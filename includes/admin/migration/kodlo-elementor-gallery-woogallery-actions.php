<?php
/**
 * Protected WooGallery actions for Kodlo Product Gallery for Elementor.
 *
 * Validates administrator consent and creates the fixed migration or cleanup
 * worker states without performing long-running metadata work in the action.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Validates a protected admin-post request and returns its activation generation.
 *
 * @param string $action         Expected action name without the generation suffix.
 * @param string $phase          Required notice phase.
 * @param string $request_method Required HTTP request method.
 * @param string $nonce_name     Request field containing the action nonce.
 * @return array{0: string, 1: array} Generation and current notice state.
 */
function kodlo_gallery_validate_woogallery_admin_action( $action, $phase, $request_method = 'POST', $nonce_name = '_wpnonce' ) {
	$request_method = strtoupper( $request_method );
	$current_request_method = isset( $_SERVER['REQUEST_METHOD'] ) && is_scalar( $_SERVER['REQUEST_METHOD'] )
		? strtoupper( sanitize_text_field( wp_unslash( (string) $_SERVER['REQUEST_METHOD'] ) ) )
		: '';
	if ( $request_method !== $current_request_method
		|| ! current_user_can( 'manage_woocommerce' ) ) {
		wp_die( esc_html__( 'You are not allowed to manage this migration.', 'kodlo-product-gallery-for-elementor' ) );
	}
	if ( ! kodlo_gallery_is_woogallery_compatibility_enabled() ) {
		wp_die( esc_html__( 'WooGallery compatibility is disabled, so this migration action cannot run.', 'kodlo-product-gallery-for-elementor' ) );
	}

	$notice_state = get_option( KODLO_GALLERY_WOOGALLERY_NOTICE_STATE_OPTION, array() );
	$expected_generation = is_array( $notice_state ) ? (string) ( $notice_state['generation'] ?? '' ) : '';
	if ( ! is_array( $notice_state )
		|| KODLO_GALLERY_WOOGALLERY_MIGRATION_VERSION !== (int) ( $notice_state['version'] ?? 0 )
		|| $phase !== ( $notice_state['phase'] ?? '' )
		|| '' === $expected_generation ) {
		wp_die( esc_html__( 'This migration request is no longer valid. Reload the page and try again.', 'kodlo-product-gallery-for-elementor' ) );
	}
	check_admin_referer( $action . '_' . $expected_generation, $nonce_name );

	$request_values = 'GET' === $request_method ? $_GET : $_POST;
	$generation     = isset( $request_values['kodlo_gallery_generation'] ) && is_scalar( $request_values['kodlo_gallery_generation'] )
		? sanitize_text_field( wp_unslash( (string) $request_values['kodlo_gallery_generation'] ) )
		: '';
	if ( '' === $generation || ! hash_equals( $expected_generation, $generation ) ) {
		wp_die( esc_html__( 'This migration request is no longer valid. Reload the page and try again.', 'kodlo-product-gallery-for-elementor' ) );
	}

	return array( $generation, $notice_state );
}

/**
 * Acquires the short transition lock used by confirmation actions.
 *
 * @param string $generation Current activation generation.
 * @return array|false Lock value on success, otherwise false.
 */
function kodlo_gallery_acquire_woogallery_action_lock( $generation ) {
	return kodlo_gallery_acquire_woogallery_worker_lock(
		KODLO_GALLERY_WOOGALLERY_ACTION_LOCK_OPTION,
		$generation
	);
}

/** Dismisses the migration prompt for the current activation generation. */
function kodlo_gallery_handle_dismiss_woogallery_migration_notice() {
	list( $generation, $notice_state ) = kodlo_gallery_validate_woogallery_admin_action(
		'kodlo_gallery_dismiss_woogallery_migration_notice',
		'migration_prompt',
		'GET'
	);
	$lock_value = kodlo_gallery_acquire_woogallery_action_lock( $generation );
	if ( false === $lock_value ) {
		kodlo_gallery_redirect_after_woogallery_action();
	}

	try {
		$dismissed_notice_state          = $notice_state;
		$dismissed_notice_state['phase'] = 'dismissed';
		kodlo_gallery_compare_and_swap_option(
			KODLO_GALLERY_WOOGALLERY_NOTICE_STATE_OPTION,
			$notice_state,
			$dismissed_notice_state
		);
	} finally {
		kodlo_gallery_delete_owned_option_lock( KODLO_GALLERY_WOOGALLERY_ACTION_LOCK_OPTION, $lock_value );
	}

	kodlo_gallery_redirect_after_woogallery_action();
}

/** Starts or resumes the bounded migration after explicit confirmation. */
function kodlo_gallery_handle_start_woogallery_migration() {
	list( $generation ) = kodlo_gallery_validate_woogallery_admin_action(
		'kodlo_gallery_start_woogallery_migration',
		'migration_prompt'
	);
	$lock_value = kodlo_gallery_acquire_woogallery_action_lock( $generation );
	if ( false === $lock_value ) {
		kodlo_gallery_redirect_after_woogallery_action();
	}

	try {
		$current_notice_state = get_option( KODLO_GALLERY_WOOGALLERY_NOTICE_STATE_OPTION, array() );
		if ( is_array( $current_notice_state )
			&& 'migration_prompt' === ( $current_notice_state['phase'] ?? '' )
			&& hash_equals( (string) ( $current_notice_state['generation'] ?? '' ), $generation ) ) {
			$snapshot      = kodlo_gallery_get_woogallery_legacy_snapshot();
			$definitions   = kodlo_gallery_get_woogallery_migration_definitions();
			$has_pending   = false;
			$workers_ready = false !== $snapshot;

			foreach ( $workers_ready ? $definitions : array() as $legacy_key => $definition ) {
				if ( ! kodlo_gallery_database_notice_is_current( $generation, 'migration_prompt' ) ) {
					$workers_ready = false;
					break;
				}

				$expected_worker_state = get_option( $definition['state_option'], null );
				$worker_state          = $expected_worker_state;
				$snapshot_max_meta_id  = absint( $snapshot[ $legacy_key ] ?? 0 );

				if ( kodlo_gallery_is_current_woogallery_worker_state( $worker_state, $generation )
					&& in_array( $worker_state['status'] ?? '', array( 'paused', 'pending' ), true ) ) {
					$start_cursor = absint( $worker_state['cursor'] ?? 0 );
				} else {
					$previous_max_meta_id = kodlo_gallery_is_current_woogallery_worker_state( $worker_state, $generation )
						&& 'completed' === ( $worker_state['status'] ?? '' )
						? absint( $worker_state['max_meta_id'] ?? 0 )
						: 0;
					$start_cursor = $previous_max_meta_id;
					$worker_state = array(
						'version'    => KODLO_GALLERY_WOOGALLERY_MIGRATION_VERSION,
						'generation' => $generation,
						'cursor'     => $start_cursor,
					);
				}

				$total_rows = kodlo_gallery_count_woogallery_legacy_rows(
					$legacy_key,
					$start_cursor,
					$snapshot_max_meta_id
				);
				if ( false === $total_rows ) {
					$workers_ready = false;
					break;
				}
				$worker_state['status']                = $total_rows > 0 ? 'pending' : 'completed';
				$worker_state['max_meta_id']           = $snapshot_max_meta_id;
				$worker_state['processed_rows']        = 0;
				$worker_state['total_rows']            = $total_rows;
				$has_pending = $has_pending || 'pending' === $worker_state['status'];

				if ( ! kodlo_gallery_store_woogallery_worker_state(
					$definition['state_option'],
					$expected_worker_state,
					$worker_state
				) ) {
					$workers_ready = false;
					break;
				}
			}

			if ( $workers_ready ) {
				$final_notice_state          = $current_notice_state;
				$final_notice_state['phase'] = $has_pending ? 'migrating' : 'cleanup_prompt';
				kodlo_gallery_compare_and_swap_option(
					KODLO_GALLERY_WOOGALLERY_NOTICE_STATE_OPTION,
					$current_notice_state,
					$final_notice_state
				);
			}
		}
	} finally {
		kodlo_gallery_delete_owned_option_lock( KODLO_GALLERY_WOOGALLERY_ACTION_LOCK_OPTION, $lock_value );
	}

	kodlo_gallery_redirect_after_woogallery_action();
}

/** Records the non-destructive post-migration choice. */
function kodlo_gallery_handle_keep_woogallery_legacy_data() {
	list( $generation, $notice_state ) = kodlo_gallery_validate_woogallery_admin_action(
		'kodlo_gallery_keep_woogallery_legacy_data',
		'cleanup_prompt',
		'POST',
		'kodlo_gallery_keep_nonce'
	);
	$lock_value = kodlo_gallery_acquire_woogallery_action_lock( $generation );
	if ( false === $lock_value ) {
		kodlo_gallery_redirect_after_woogallery_action();
	}

	try {
		$kept_notice_state          = $notice_state;
		$kept_notice_state['phase'] = 'kept';
		kodlo_gallery_compare_and_swap_option(
			KODLO_GALLERY_WOOGALLERY_NOTICE_STATE_OPTION,
			$notice_state,
			$kept_notice_state
		);
	} finally {
		kodlo_gallery_delete_owned_option_lock( KODLO_GALLERY_WOOGALLERY_ACTION_LOCK_OPTION, $lock_value );
	}

	kodlo_gallery_redirect_after_woogallery_action();
}

/** Starts bounded deletion of safely migrated legacy metadata. */
function kodlo_gallery_handle_delete_woogallery_legacy_data() {
	list( $generation ) = kodlo_gallery_validate_woogallery_admin_action(
		'kodlo_gallery_delete_woogallery_legacy_data',
		'cleanup_prompt',
		'POST',
		'kodlo_gallery_delete_nonce'
	);
	$lock_value = kodlo_gallery_acquire_woogallery_action_lock( $generation );
	if ( false === $lock_value ) {
		kodlo_gallery_redirect_after_woogallery_action();
	}

	try {
		$current_notice_state = get_option( KODLO_GALLERY_WOOGALLERY_NOTICE_STATE_OPTION, array() );
		if ( is_array( $current_notice_state )
			&& 'cleanup_prompt' === ( $current_notice_state['phase'] ?? '' )
			&& hash_equals( (string) ( $current_notice_state['generation'] ?? '' ), $generation ) ) {
			$snapshot      = kodlo_gallery_get_woogallery_legacy_snapshot();
			$workers_ready = false !== $snapshot;
			$has_pending   = false;
			$definitions   = kodlo_gallery_get_woogallery_migration_definitions();

			foreach ( $workers_ready ? $definitions : array() as $legacy_key => $definition ) {
				if ( ! kodlo_gallery_database_notice_is_current( $generation, 'cleanup_prompt' ) ) {
					$workers_ready = false;
					break;
				}

				$state_option          = $definition['cleanup_state_option'];
				$expected_worker_state = get_option( $state_option, null );
				$worker_state          = $expected_worker_state;
				$migration_state       = get_option( $definition['state_option'], array() );
				if ( ! kodlo_gallery_is_current_woogallery_worker_state( $migration_state, $generation )
					|| 'completed' !== ( $migration_state['status'] ?? '' ) ) {
					$workers_ready = false;
					break;
				}

				$cleanup_max_meta_id = min(
					absint( $snapshot[ $legacy_key ] ?? 0 ),
					absint( $migration_state['max_meta_id'] ?? 0 )
				);
				if ( kodlo_gallery_is_current_woogallery_worker_state( $worker_state, $generation )
					&& in_array( $worker_state['status'] ?? '', array( 'paused', 'pending' ), true ) ) {
					$start_cursor = absint( $worker_state['cursor'] ?? 0 );
				} else {
					$start_cursor = 0;
					$worker_state = array(
						'version'        => KODLO_GALLERY_WOOGALLERY_MIGRATION_VERSION,
						'generation'     => $generation,
						'cursor'         => $start_cursor,
						'deleted_count'  => 0,
						'retained_count' => 0,
					);
				}
				$total_rows = kodlo_gallery_count_woogallery_legacy_rows(
					$legacy_key,
					$start_cursor,
					$cleanup_max_meta_id
				);
				if ( false === $total_rows ) {
					$workers_ready = false;
					break;
				}
				$worker_state['status']                = $total_rows > 0 ? 'pending' : 'completed';
				$worker_state['max_meta_id']           = $cleanup_max_meta_id;
				$worker_state['processed_rows']        = 0;
				$worker_state['total_rows']            = $total_rows;
				$has_pending = $has_pending || 'pending' === $worker_state['status'];

				if ( ! kodlo_gallery_store_woogallery_worker_state(
					$state_option,
					$expected_worker_state,
					$worker_state
				) ) {
					$workers_ready = false;
					break;
				}
			}

			if ( $workers_ready ) {
				$final_notice_state          = $current_notice_state;
				$final_notice_state['phase'] = $has_pending ? 'cleaning' : 'cleaned';
				kodlo_gallery_compare_and_swap_option(
					KODLO_GALLERY_WOOGALLERY_NOTICE_STATE_OPTION,
					$current_notice_state,
					$final_notice_state
				);
			}
		}
	} finally {
		kodlo_gallery_delete_owned_option_lock( KODLO_GALLERY_WOOGALLERY_ACTION_LOCK_OPTION, $lock_value );
	}

	kodlo_gallery_redirect_after_woogallery_action();
}

/** Redirects a completed admin action back to a safe administration screen. */
function kodlo_gallery_redirect_after_woogallery_action() {
	$redirect_url = wp_get_referer();
	if ( ! $redirect_url ) {
		$redirect_url = admin_url( 'plugins.php' );
	}

	wp_safe_redirect( $redirect_url );
	exit;
}

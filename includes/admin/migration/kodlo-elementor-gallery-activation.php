<?php
/**
 * Activation-generation fencing for Kodlo Product Gallery for Elementor.
 *
 * Rotates migration consent generations and safely pauses in-flight workers
 * before the next legacy-data detection cycle can begin.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Fences one worker behind the next activation generation without losing progress.
 *
 * @param string $state_option          Worker state option.
 * @param string $activation_generation New activation generation.
 * @param array  $expected_notice_state Exact fencing notice authorizing this coordinator.
 * @return bool True when the worker is absent, irrelevant, or safely fenced.
 */
function kodlo_gallery_fence_worker_for_activation( $state_option, $activation_generation, $expected_notice_state ): bool {
	for ( $attempt = 0; $attempt < 10; ++$attempt ) {
		$database_error = false;
		$current_state  = kodlo_gallery_get_uncached_internal_option( $state_option, $database_error );
		if ( $database_error ) {
			return false;
		}
		if ( null === $current_state
			|| ! is_array( $current_state )
			|| KODLO_GALLERY_WOOGALLERY_MIGRATION_VERSION !== (int) ( $current_state['version'] ?? 0 ) ) {
			return true;
		}

		$next_state = $current_state;
		if ( 'pending' === ( $next_state['status'] ?? '' ) ) {
			$next_state['status'] = 'paused';
		}
		$next_state['generation'] = $activation_generation;
		if ( $current_state === $next_state ) {
			return true;
		}
		if ( kodlo_gallery_compare_and_swap_activation_worker(
			$state_option,
			$current_state,
			$next_state,
			$expected_notice_state
		) ) {
			return true;
		}
	}

	return false;
}

/**
 * Completes an interrupted activation fence before legacy-data detection starts.
 *
 * The persistent fencing phase invalidates the previous generation first. Each
 * administrator request can then safely retry any worker state that a temporary
 * database failure prevented the activation request from updating.
 */
function kodlo_gallery_resume_activation_notice_cycle(): void {
	$cached_notice_state = get_option( KODLO_GALLERY_WOOGALLERY_NOTICE_STATE_OPTION, null );
	if ( ! is_array( $cached_notice_state )
		|| KODLO_GALLERY_WOOGALLERY_MIGRATION_VERSION !== (int) ( $cached_notice_state['version'] ?? 0 )
		|| 'fencing' !== ( $cached_notice_state['phase'] ?? '' )
		|| empty( $cached_notice_state['generation'] ) ) {
		return;
	}

	$database_error = false;
	$notice_state   = kodlo_gallery_get_uncached_internal_option(
		KODLO_GALLERY_WOOGALLERY_NOTICE_STATE_OPTION,
		$database_error
	);
	if ( $database_error
		|| ! is_array( $notice_state )
		|| KODLO_GALLERY_WOOGALLERY_MIGRATION_VERSION !== (int) ( $notice_state['version'] ?? 0 )
		|| 'fencing' !== ( $notice_state['phase'] ?? '' )
		|| empty( $notice_state['generation'] ) ) {
		return;
	}

	$activation_generation = (string) $notice_state['generation'];
	$worker_state_options   = array(
		KODLO_GALLERY_VIDEO_MIGRATION_STATE_OPTION,
		KODLO_GALLERY_VARIATION_MIGRATION_STATE_OPTION,
		KODLO_GALLERY_VIDEO_CLEANUP_STATE_OPTION,
		KODLO_GALLERY_VARIATION_CLEANUP_STATE_OPTION,
	);

	foreach ( $worker_state_options as $state_option ) {
		if ( ! kodlo_gallery_fence_worker_for_activation(
			$state_option,
			$activation_generation,
			$notice_state
		) ) {
			return;
		}
	}

	$detect_state          = $notice_state;
	$detect_state['phase'] = 'detect';
	$transitioned          = kodlo_gallery_compare_and_swap_option(
		KODLO_GALLERY_WOOGALLERY_NOTICE_STATE_OPTION,
		$notice_state,
		$detect_state
	);
	if ( ! $transitioned ) {
		$database_error = false;
		$current_state  = kodlo_gallery_get_uncached_internal_option(
			KODLO_GALLERY_WOOGALLERY_NOTICE_STATE_OPTION,
			$database_error
		);
		if ( $database_error
			|| ! is_array( $current_state )
			|| 'detect' !== ( $current_state['phase'] ?? '' )
			|| ! hash_equals( $activation_generation, (string) ( $current_state['generation'] ?? '' ) ) ) {
			return;
		}
	}

	delete_option( KODLO_GALLERY_WOOGALLERY_ACTION_LOCK_OPTION );
}

/** Starts a new per-site consent cycle and invalidates in-flight legacy workers. */
function kodlo_gallery_start_activation_notice_cycle(): void {
	$fencing_state = array(
		'version'    => KODLO_GALLERY_WOOGALLERY_MIGRATION_VERSION,
		'generation' => wp_generate_uuid4(),
		'phase'      => 'fencing',
	);
	$state_stored = update_option(
		KODLO_GALLERY_WOOGALLERY_NOTICE_STATE_OPTION,
		$fencing_state,
		false
	);
	if ( ! $state_stored ) {
		$database_error = false;
		$current_state  = kodlo_gallery_get_uncached_internal_option(
			KODLO_GALLERY_WOOGALLERY_NOTICE_STATE_OPTION,
			$database_error
		);
		if ( $database_error || $fencing_state !== $current_state ) {
			return;
		}
	}

	kodlo_gallery_resume_activation_notice_cycle();
}

/**
 * Starts the consent cycle on every site covered by this activation.
 *
 * @param bool $network_wide Whether the plugin is being network activated.
 */
function kodlo_gallery_run_activation( $network_wide = false ): void {
	if ( is_multisite() && $network_wide ) {
		$sites_per_page = 100;
		$site_offset    = 0;

		do {
			$site_ids = get_sites(
				array(
					'fields' => 'ids',
					'number' => $sites_per_page,
					'offset' => $site_offset,
					'orderby' => 'id',
					'order'  => 'ASC',
				)
			);
			foreach ( $site_ids as $site_id ) {
				switch_to_blog( $site_id );
				kodlo_gallery_start_activation_notice_cycle();
				restore_current_blog();
			}
			$site_offset += $sites_per_page;
		} while ( count( $site_ids ) === $sites_per_page );

		return;
	}

	kodlo_gallery_start_activation_notice_cycle();
}

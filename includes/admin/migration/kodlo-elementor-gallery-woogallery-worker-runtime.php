<?php
/**
 * Shared WooGallery worker runtime for Kodlo Product Gallery for Elementor.
 *
 * Provides only the narrow state, authorization, lease, batch-query, and
 * completion operations shared by migration and cleanup workers.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Reports whether a stored worker state belongs to the active notice generation.
 *
 * @param mixed  $state      Stored worker state.
 * @param string $generation Current activation generation.
 * @return bool True when the worker state is current.
 */
function kodlo_gallery_is_current_woogallery_worker_state( $state, $generation ) {
	return is_array( $state )
		&& KODLO_GALLERY_WOOGALLERY_MIGRATION_VERSION === (int) ( $state['version'] ?? 0 )
		&& '' !== $generation
		&& hash_equals( (string) ( $state['generation'] ?? '' ), $generation );
}

/**
 * Stores a worker state only when the option has not changed since it was read.
 *
 * @param string $option_name    Worker option name.
 * @param mixed  $expected_state Previously read state, or null when absent.
 * @param array  $new_state      Replacement state.
 * @return bool True when this request owned the transition.
 */
function kodlo_gallery_store_woogallery_worker_state( $option_name, $expected_state, $new_state ) {
	if ( null === $expected_state ) {
		return add_option( $option_name, $new_state, '', false );
	}
	if ( $expected_state === $new_state ) {
		return true;
	}

	return kodlo_gallery_compare_and_swap_option( $option_name, $expected_state, $new_state );
}

/**
 * Checks worker authorization directly in the database to bypass request-local caches.
 *
 * @param string $worker_option Worker state option name.
 * @param string $generation    Expected activation generation.
 * @param string $notice_phase  Required notice phase.
 * @return bool True while the current generation still authorizes the worker.
 */
function kodlo_gallery_database_worker_is_authorized( $worker_option, $generation, $notice_phase ) {
	global $wpdb;

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$rows = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name IN (%s, %s)",
			KODLO_GALLERY_WOOGALLERY_NOTICE_STATE_OPTION,
			$worker_option
		),
		ARRAY_A
	);
	if ( ! is_array( $rows ) || 2 !== count( $rows ) ) {
		return false;
	}

	$states = array();
	foreach ( $rows as $row ) {
		$states[ (string) ( $row['option_name'] ?? '' ) ] = maybe_unserialize( $row['option_value'] ?? '' );
	}
	$notice_state = $states[ KODLO_GALLERY_WOOGALLERY_NOTICE_STATE_OPTION ] ?? array();
	$worker_state = $states[ $worker_option ] ?? array();

	return is_array( $notice_state )
		&& is_array( $worker_state )
		&& KODLO_GALLERY_WOOGALLERY_MIGRATION_VERSION === (int) ( $notice_state['version'] ?? 0 )
		&& KODLO_GALLERY_WOOGALLERY_MIGRATION_VERSION === (int) ( $worker_state['version'] ?? 0 )
		&& $notice_phase === ( $notice_state['phase'] ?? '' )
		&& 'pending' === ( $worker_state['status'] ?? '' )
		&& hash_equals( (string) ( $notice_state['generation'] ?? '' ), $generation )
		&& hash_equals( (string) ( $worker_state['generation'] ?? '' ), $generation );
}

/**
 * Checks the current notice generation without relying on request-local caches.
 *
 * @param string $generation   Expected activation generation.
 * @param string $notice_phase Required notice phase.
 * @return bool True when the browser action still belongs to the active notice.
 */
function kodlo_gallery_database_notice_is_current( $generation, $notice_phase ) {
	$database_error = false;
	$notice_state   = kodlo_gallery_get_uncached_internal_option(
		KODLO_GALLERY_WOOGALLERY_NOTICE_STATE_OPTION,
		$database_error
	);

	return ! $database_error
		&& is_array( $notice_state )
		&& KODLO_GALLERY_WOOGALLERY_MIGRATION_VERSION === (int) ( $notice_state['version'] ?? 0 )
		&& $notice_phase === ( $notice_state['phase'] ?? '' )
		&& hash_equals( (string) ( $notice_state['generation'] ?? '' ), $generation );
}

/**
 * Acquires a short owner-matched worker lease for one activation generation.
 *
 * @param string $lock_option Lock option name.
 * @param string $generation Active consent generation.
 * @return array|false Exact lock value, or false while another worker owns it.
 */
function kodlo_gallery_acquire_woogallery_worker_lock( $lock_option, $generation ) {
	$lock_value = array(
		'token'      => wp_generate_uuid4(),
		'generation' => $generation,
		'expires_at' => time() + 30,
	);
	if ( add_option( $lock_option, $lock_value, '', false ) ) {
		return $lock_value;
	}

	$current_lock = get_option( $lock_option, array() );
	if ( is_array( $current_lock ) && absint( $current_lock['expires_at'] ?? 0 ) >= time() ) {
		return false;
	}
	if ( ! kodlo_gallery_delete_owned_option_lock( $lock_option, $current_lock )
		|| ! add_option( $lock_option, $lock_value, '', false ) ) {
		return false;
	}

	return $lock_value;
}

/**
 * Returns the next exact-key metadata page inside one immutable snapshot.
 *
 * @param string $legacy_key Exact legacy metadata key.
 * @param int    $cursor     Last processed metadata row ID.
 * @param int    $max_meta_id Highest authorized metadata row ID.
 * @return array<int, array<string, mixed>>|false Selected rows, or false after a database error.
 */
function kodlo_gallery_get_woogallery_legacy_batch( $legacy_key, $cursor, $max_meta_id ) {
	global $wpdb;

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$rows = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT meta_id, post_id, meta_value FROM {$wpdb->postmeta}
			 WHERE meta_key = %s AND BINARY meta_key = BINARY %s
				 AND meta_id > %d AND meta_id <= %d
			 ORDER BY meta_id ASC LIMIT 50",
			$legacy_key,
			$legacy_key,
			absint( $cursor ),
			absint( $max_meta_id )
		),
		ARRAY_A
	);
	if ( '' !== (string) $wpdb->last_error || ! is_array( $rows ) ) {
		return false;
	}

	return $rows;
}

/**
 * Reports whether every migration or cleanup worker completed this generation.
 *
 * @param string $generation       Active consent generation.
 * @param string $worker_state_key Definition field containing each worker option.
 * @return bool True after all defined workers have completed.
 */
function kodlo_gallery_all_woogallery_workers_completed( $generation, $worker_state_key ) {
	if ( ! in_array( $worker_state_key, array( 'state_option', 'cleanup_state_option' ), true ) ) {
		return false;
	}

	foreach ( kodlo_gallery_get_woogallery_migration_definitions() as $definition ) {
		$worker_option = $definition[ $worker_state_key ] ?? '';
		$worker_state  = get_option( $worker_option, array() );
		if ( ! kodlo_gallery_is_current_woogallery_worker_state( $worker_state, $generation )
			|| 'completed' !== ( $worker_state['status'] ?? '' ) ) {
			return false;
		}
	}

	return true;
}

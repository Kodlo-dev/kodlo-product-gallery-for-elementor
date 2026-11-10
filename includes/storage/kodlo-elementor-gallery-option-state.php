<?php
/**
 * Atomic option-state storage for Kodlo Product Gallery for Elementor.
 *
 * Provides uncached reads, owner-matched lock deletion, and compare-and-swap
 * transitions used by activation, migration, and cleanup workers.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Deletes an option lock only when the stored value still belongs to this request.
 *
 * @param string $lock_option Lock option name.
 * @param mixed  $lock_value  Exact lock value previously read or created.
 * @return bool True when the matching lock was deleted.
 */
function kodlo_gallery_delete_owned_option_lock( $lock_option, $lock_value ) {
	global $wpdb;

	// The value comparison prevents an expired owner from deleting a renewed lock.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$deleted = $wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name = %s AND BINARY option_value = BINARY %s",
			$lock_option,
			maybe_serialize( $lock_value )
		)
	);

	if ( 1 !== $deleted ) {
		return false;
	}

	wp_cache_delete( $lock_option, 'options' );
	return true;
}

/**
 * Replaces an internal option only when its serialized state is unchanged.
 *
 * @param string $option_name    Option name.
 * @param mixed  $expected_value Exact value read before the transition.
 * @param mixed  $new_value      Replacement value.
 * @return bool True when this request owned the transition.
 */
function kodlo_gallery_compare_and_swap_option( $option_name, $expected_value, $new_value ) {
	global $wpdb;

	// The serialized comparison prevents an older activation generation from overwriting a newer state.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$updated = $wpdb->query(
		$wpdb->prepare(
			"UPDATE {$wpdb->options} SET option_value = %s WHERE option_name = %s AND BINARY option_value = BINARY %s",
			maybe_serialize( $new_value ),
			$option_name,
			maybe_serialize( $expected_value )
		)
	);

	if ( 1 !== $updated ) {
		return false;
	}

	wp_cache_delete( $option_name, 'options' );
	return true;
}

/**
 * Fences one worker only while the originating activation notice is unchanged.
 *
 * @param string $worker_option   Worker option name.
 * @param mixed  $expected_worker Exact worker state read by the coordinator.
 * @param mixed  $new_worker      Replacement worker state.
 * @param mixed  $expected_notice Exact fencing notice that authorized the coordinator.
 * @return bool True when both compare-and-swap guards matched.
 */
function kodlo_gallery_compare_and_swap_activation_worker( $worker_option, $expected_worker, $new_worker, $expected_notice ) {
	global $wpdb;

	// The notice join prevents an older activation coordinator from restoring its generation.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$updated = $wpdb->query(
		$wpdb->prepare(
			"UPDATE {$wpdb->options} AS worker
			 INNER JOIN {$wpdb->options} AS notice
				 ON notice.option_name = %s
				 AND BINARY notice.option_value = BINARY %s
			 SET worker.option_value = %s
			 WHERE worker.option_name = %s
				 AND BINARY worker.option_value = BINARY %s",
			KODLO_GALLERY_WOOGALLERY_NOTICE_STATE_OPTION,
			maybe_serialize( $expected_notice ),
			maybe_serialize( $new_worker ),
			$worker_option,
			maybe_serialize( $expected_worker )
		)
	);
	if ( 1 !== $updated ) {
		return false;
	}

	wp_cache_delete( $worker_option, 'options' );
	return true;
}

/**
 * Reads one internal option directly from the database without the object cache.
 *
 * @param string    $option_name    Option name.
 * @param bool|null $database_error Receives whether the read failed.
 * @return mixed|null Unserialized value, or null when absent or unreadable.
 */
function kodlo_gallery_get_uncached_internal_option( $option_name, &$database_error = null ) {
	global $wpdb;

	$database_error = false;
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$raw_value = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT option_value FROM {$wpdb->options} WHERE option_name = %s LIMIT 1",
			$option_name
		)
	);
	if ( '' !== (string) $wpdb->last_error ) {
		$database_error = true;
		return null;
	}
	if ( null === $raw_value ) {
		return null;
	}

	return maybe_unserialize( $raw_value );
}

/**
 * Reports whether this request still owns an unexpired migration lock.
 *
 * @param string $lock_option Lock option name.
 * @param array  $lock_value  Exact lock value created by this request.
 * @return bool True while the stored lock is unchanged and unexpired.
 */
function kodlo_gallery_owns_active_migration_lock( $lock_option, $lock_value ) {
	if ( ! is_array( $lock_value ) || absint( $lock_value['expires_at'] ?? 0 ) < time() ) {
		return false;
	}

	return $lock_value === kodlo_gallery_get_uncached_internal_option( $lock_option );
}

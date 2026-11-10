<?php
/**
 * Serialized metadata-write storage for Kodlo Product Gallery for Elementor.
 *
 * Coordinates normal administrator saves with create-only WooGallery migration
 * writes through one per-object locking contract.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Acquires the shared per-object lock used by every Kodlo metadata writer.
 *
 * @param int    $post_id      Post ID.
 * @param string $meta_key     Destination metadata key.
 * @param int    $wait_timeout Maximum wait in milliseconds.
 * @return array|false Lock descriptor, or false when another writer remains active.
 */
function kodlo_gallery_acquire_meta_write_lock( $post_id, $meta_key, $wait_timeout = 0 ) {
	$lock_option = 'kodlo_gallery_meta_write_lock_' . absint( $post_id ) . '_' . substr( md5( $meta_key ), 0, 12 );
	$lock_value  = array(
		'token' => wp_generate_uuid4(),
	);
	$deadline = microtime( true ) + ( max( 0, absint( $wait_timeout ) ) / 1000 );

	do {
		// A writer that waited for an older lease must receive its own full lease.
		$lock_value['expires_at'] = time() + 15;
		if ( add_option( $lock_option, $lock_value, '', false ) ) {
			return array(
				'option_name' => $lock_option,
				'value'       => $lock_value,
			);
		}

		$database_error = false;
		$current_lock   = kodlo_gallery_get_uncached_internal_option( $lock_option, $database_error );
		if ( $database_error ) {
			return false;
		}
		if ( ! is_array( $current_lock ) || absint( $current_lock['expires_at'] ?? 0 ) < time() ) {
			if ( kodlo_gallery_delete_owned_option_lock( $lock_option, $current_lock ) ) {
				continue;
			}
			if ( microtime( true ) >= $deadline ) {
				return false;
			}
			usleep( 10000 );
			continue;
		}
		if ( microtime( true ) >= $deadline ) {
			return false;
		}

		usleep( 10000 );
	} while ( true );
}

/**
 * Releases a per-object metadata write lock owned by this request.
 *
 * @param array $lock_descriptor Lock returned by kodlo_gallery_acquire_meta_write_lock().
 * @return bool True when the matching lock was released.
 */
function kodlo_gallery_release_meta_write_lock( $lock_descriptor ) {
	if ( ! is_array( $lock_descriptor )
		|| ! isset( $lock_descriptor['option_name'], $lock_descriptor['value'] ) ) {
		return false;
	}

	return kodlo_gallery_delete_owned_option_lock(
		(string) $lock_descriptor['option_name'],
		$lock_descriptor['value']
	);
}

/**
 * Reports an administrator metadata write that could not be persisted.
 *
 * @param int $post_id Attachment or variation ID.
 */
function kodlo_gallery_report_meta_write_failure( $post_id ) {
	/**
	 * Fires when a normal Kodlo gallery metadata write cannot be persisted.
	 *
	 * @since 2.9.1
	 * @param int $post_id Attachment or variation ID.
	 */
	do_action( 'kodlo_gallery_meta_write_failed', absint( $post_id ) );
}

/**
 * Updates Kodlo metadata while excluding the legacy migration writer.
 *
 * @param int    $post_id    Post ID.
 * @param string $meta_key   Kodlo metadata key.
 * @param mixed  $meta_value Sanitized metadata value.
 * @return bool True when the requested value is stored.
 */
function kodlo_gallery_update_locked_post_meta( $post_id, $meta_key, $meta_value ) {
	$lock_descriptor = kodlo_gallery_acquire_meta_write_lock( $post_id, $meta_key, 1000 );
	if ( false === $lock_descriptor ) {
		kodlo_gallery_report_meta_write_failure( $post_id );
		return false;
	}

	try {
		$updated = update_post_meta( $post_id, $meta_key, $meta_value );
		$write_succeeded = false !== $updated
			|| ( metadata_exists( 'post', $post_id, $meta_key )
				&& $meta_value === get_post_meta( $post_id, $meta_key, true ) );
		if ( ! $write_succeeded ) {
			kodlo_gallery_report_meta_write_failure( $post_id );
		}
		return $write_succeeded;
	} finally {
		kodlo_gallery_release_meta_write_lock( $lock_descriptor );
	}
}

/**
 * Reports whether a destination metadata key exists directly in the database.
 *
 * @param int    $post_id        Post ID.
 * @param string $meta_key       Exact metadata key.
 * @param bool   $database_error Receives whether the query failed.
 * @return bool True when at least one exact destination row exists.
 */
function kodlo_gallery_database_post_meta_exists( $post_id, $meta_key, &$database_error ) {
	global $wpdb;

	$database_error = false;
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$meta_id = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT meta_id FROM {$wpdb->postmeta} WHERE post_id = %d AND BINARY meta_key = BINARY %s LIMIT 1",
			$post_id,
			$meta_key
		)
	);
	if ( '' !== (string) $wpdb->last_error ) {
		$database_error = true;
		return false;
	}

	return null !== $meta_id;
}

/**
 * Creates one destination row only from the exact legacy row selected by the worker.
 *
 * @param int    $meta_id           Exact legacy metadata row ID.
 * @param int    $post_id           Source and destination post ID.
 * @param string $legacy_key        Exact legacy metadata key.
 * @param string $raw_legacy_value  Exact raw legacy database value.
 * @param string $destination_key   Kodlo metadata key.
 * @param string $destination_value Sanitized Kodlo metadata value.
 * @return bool True when created or safely superseded, false when the batch must retry.
 */
function kodlo_gallery_insert_migrated_post_meta( $meta_id, $post_id, $legacy_key, $raw_legacy_value, $destination_key, $destination_value ) {
	global $wpdb;

	$lock_descriptor = kodlo_gallery_acquire_meta_write_lock( $post_id, $destination_key );
	if ( false === $lock_descriptor ) {
		return false;
	}

	try {
		// The exact source guard prevents a replacement row outside the consent snapshot from being copied.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$inserted = $wpdb->query(
			$wpdb->prepare(
				"INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value)
				 SELECT source.post_id, %s, %s
				 FROM {$wpdb->postmeta} AS source
				 WHERE source.meta_id = %d
					 AND source.post_id = %d
					 AND BINARY source.meta_key = BINARY %s
					 AND BINARY source.meta_value = BINARY %s
					 AND NOT EXISTS (
						 SELECT 1 FROM {$wpdb->postmeta} AS destination
						 WHERE destination.post_id = source.post_id
							 AND BINARY destination.meta_key = BINARY %s
					 )
				 LIMIT 1",
				$destination_key,
				$destination_value,
				$meta_id,
				$post_id,
				$legacy_key,
				$raw_legacy_value,
				$destination_key
			)
		);
		if ( false === $inserted || '' !== (string) $wpdb->last_error ) {
			return false;
		}
		if ( 1 === $inserted ) {
			wp_cache_delete( $post_id, 'post_meta' );
			return true;
		}

		$database_error     = false;
		$destination_exists = kodlo_gallery_database_post_meta_exists(
			$post_id,
			$destination_key,
			$database_error
		);
		return ! $database_error && $destination_exists;
	} finally {
		kodlo_gallery_release_meta_write_lock( $lock_descriptor );
	}
}

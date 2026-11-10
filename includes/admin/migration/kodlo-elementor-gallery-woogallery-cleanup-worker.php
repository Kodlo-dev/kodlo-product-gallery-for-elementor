<?php
/**
 * WooGallery cleanup workers for Kodlo Product Gallery for Elementor.
 *
 * Deletes only unchanged legacy rows with an exact matching Kodlo destination,
 * in resumable 50-row batches after a separate administrator confirmation.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Returns the exact Kodlo destination required before deleting one legacy row.
 *
 * @param string $legacy_key     Exact legacy metadata key.
 * @param int    $post_id        Attachment or variation ID.
 * @param mixed  $raw_meta_value Raw database metadata value.
 * @return array<string, string>|false Exact destination guard, or false when the row must be retained.
 */
function kodlo_gallery_get_legacy_cleanup_destination( $legacy_key, $post_id, $raw_meta_value ) {
	$legacy_value = kodlo_gallery_decode_woogallery_database_value( $raw_meta_value );
	if ( 'wcgs_video' === $legacy_key ) {
		if ( ! kodlo_gallery_is_supported_image_attachment( $post_id ) || ! is_string( $legacy_value ) ) {
			return false;
		}
		$legacy_video_url = esc_url_raw( trim( $legacy_value ) );
		if ( '' === $legacy_video_url
			|| ! metadata_exists( 'post', $post_id, KODLO_GALLERY_VIDEO_META_KEY )
			|| $legacy_video_url !== (string) get_post_meta( $post_id, KODLO_GALLERY_VIDEO_META_KEY, true ) ) {
			return false;
		}

		return array(
			'destination_key'   => KODLO_GALLERY_VIDEO_META_KEY,
			'destination_value' => $legacy_video_url,
		);
	}

	if ( 'woo_gallery_slider' !== $legacy_key || 'product_variation' !== get_post_type( $post_id ) ) {
		return false;
	}
	$legacy_ids        = kodlo_gallery_normalize_legacy_variation_ids( $legacy_value );
	$legacy_ids        = array_values( array_filter( $legacy_ids, 'kodlo_gallery_is_supported_image_attachment' ) );
	$destination_value = implode( ',', $legacy_ids );
	if ( '' === $destination_value
		|| ! metadata_exists( 'post', $post_id, KODLO_GALLERY_VARIATION_META_KEY )
		|| $destination_value !== (string) get_post_meta( $post_id, KODLO_GALLERY_VARIATION_META_KEY, true ) ) {
		return false;
	}

	return array(
		'destination_key'   => KODLO_GALLERY_VARIATION_META_KEY,
		'destination_value' => $destination_value,
	);
}

/**
 * Deletes one legacy row only while both source and destination remain unchanged.
 *
 * @param int    $meta_id           Metadata row ID.
 * @param int    $post_id           Post ID.
 * @param string $legacy_key        Exact legacy metadata key.
 * @param string $raw_meta_value    Exact raw value selected for validation.
 * @param string $destination_key   Exact Kodlo metadata key.
 * @param string $destination_value Exact Kodlo metadata value.
 * @return bool|null True when deleted, false when retained, or null after a database error.
 */
function kodlo_gallery_delete_unchanged_legacy_row( $meta_id, $post_id, $legacy_key, $raw_meta_value, $destination_key, $destination_value ) {
	global $wpdb;

	// The primary-key source match keeps the joined safety query bounded to one legacy row.
	// Exact meta comparisons are required to prevent deleting concurrently changed data.
	// phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_key, WordPress.DB.SlowDBQuery.slow_db_query_meta_value
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$deleted = $wpdb->query(
		$wpdb->prepare(
			"DELETE legacy
			 FROM {$wpdb->postmeta} AS legacy
			 INNER JOIN {$wpdb->postmeta} AS destination
				 ON destination.post_id = legacy.post_id
				 AND BINARY destination.meta_key = BINARY %s
				 AND BINARY destination.meta_value = BINARY %s
			 WHERE legacy.meta_id = %d
				 AND legacy.post_id = %d
				 AND BINARY legacy.meta_key = BINARY %s
				 AND BINARY legacy.meta_value = BINARY %s",
			$destination_key,
			$destination_value,
			$meta_id,
			$post_id,
			$legacy_key,
			$raw_meta_value
		)
	);
	// phpcs:enable WordPress.DB.SlowDBQuery.slow_db_query_meta_key, WordPress.DB.SlowDBQuery.slow_db_query_meta_value
	if ( false === $deleted || '' !== (string) $wpdb->last_error ) {
		return null;
	}
	if ( 1 !== $deleted ) {
		return false;
	}

	wp_cache_delete( $post_id, 'post_meta' );
	return true;
}

/**
 * Deletes one exact-key cleanup batch after explicit administrator confirmation.
 *
 * @param string $legacy_key   Exact legacy metadata key.
 * @param string $state_option Cleanup state option.
 * @param string $lock_option  Cleanup lock option.
 * @param string $generation   Current activation generation.
 */
function kodlo_gallery_cleanup_legacy_meta_batch( $legacy_key, $state_option, $lock_option, $generation ) {
	$state = get_option( $state_option, array() );
	if ( ! kodlo_gallery_is_current_woogallery_worker_state( $state, $generation )
		|| 'pending' !== ( $state['status'] ?? '' ) ) {
		return;
	}

	$lock_value = kodlo_gallery_acquire_woogallery_worker_lock( $lock_option, $generation );
	if ( false === $lock_value ) {
		return;
	}

	try {
		$state = kodlo_gallery_get_uncached_internal_option( $state_option );
		if ( ! kodlo_gallery_is_current_woogallery_worker_state( $state, $generation )
			|| 'pending' !== ( $state['status'] ?? '' ) ) {
			return;
		}
		$expected_state = $state;

		if ( ! kodlo_gallery_database_worker_is_authorized( $state_option, $generation, 'cleaning' ) ) {
			return;
		}

		$rows = kodlo_gallery_get_woogallery_legacy_batch(
			$legacy_key,
			absint( $state['cursor'] ?? 0 ),
			absint( $state['max_meta_id'] ?? 0 )
		);
		if ( false === $rows ) {
			return;
		}
		$batch_row_count = count( $rows );

		$post_ids = array_values( array_unique( array_map( 'absint', array_column( $rows, 'post_id' ) ) ) );
		if ( ! empty( $post_ids ) ) {
			_prime_post_caches( $post_ids, false, true );
		}
		if ( 'woo_gallery_slider' === $legacy_key ) {
			kodlo_gallery_prime_woogallery_variation_attachment_caches( $rows );
		}

		$processed_rows = 0;
		foreach ( $rows as $row ) {
			if ( absint( $lock_value['expires_at'] ?? 0 ) < time() ) {
				break;
			}

			$meta_id        = absint( $row['meta_id'] ?? 0 );
			$post_id        = absint( $row['post_id'] ?? 0 );
			$raw_meta_value = (string) ( $row['meta_value'] ?? '' );
			$destination    = kodlo_gallery_get_legacy_cleanup_destination(
				$legacy_key,
				$post_id,
				$raw_meta_value
			);
			$delete_result = false;
			if ( is_array( $destination ) ) {
				$delete_result = kodlo_gallery_delete_unchanged_legacy_row(
					$meta_id,
					$post_id,
					$legacy_key,
					$raw_meta_value,
					$destination['destination_key'],
					$destination['destination_value']
				);
			}
			if ( null === $delete_result ) {
				break;
			}
			if ( $delete_result ) {
				$state['deleted_count'] = absint( $state['deleted_count'] ?? 0 ) + 1;
			} else {
				$state['retained_count'] = absint( $state['retained_count'] ?? 0 ) + 1;
			}
			$state['cursor'] = $meta_id;
			++$processed_rows;
		}
		$state['processed_rows'] = absint( $state['processed_rows'] ?? 0 ) + $processed_rows;

		$total_rows = absint( $state['total_rows'] ?? 0 );
		if ( ( $total_rows > 0 && $state['processed_rows'] >= $total_rows )
			|| ( $processed_rows === $batch_row_count && $batch_row_count < 50 ) ) {
			$state['status'] = 'completed';
		}

		if ( kodlo_gallery_owns_active_migration_lock( $lock_option, $lock_value ) ) {
			kodlo_gallery_compare_and_swap_option( $state_option, $expected_state, $state );
		}
	} finally {
		kodlo_gallery_delete_owned_option_lock( $lock_option, $lock_value );
	}
}

/**
 * Reconciles a completed cleanup with its terminal notice phase.
 *
 * @param string $generation Current activation generation.
 */
function kodlo_gallery_maybe_complete_woogallery_cleanup_notice( $generation ) {
	$notice_state = get_option( KODLO_GALLERY_WOOGALLERY_NOTICE_STATE_OPTION, array() );
	if ( ! is_array( $notice_state )
		|| 'cleaning' !== ( $notice_state['phase'] ?? '' )
		|| ! hash_equals( (string) ( $notice_state['generation'] ?? '' ), $generation )
		|| ! kodlo_gallery_all_woogallery_workers_completed( $generation, 'cleanup_state_option' ) ) {
		return;
	}

	$completed_notice_state          = $notice_state;
	$completed_notice_state['phase'] = 'cleaned';
	kodlo_gallery_compare_and_swap_option(
		KODLO_GALLERY_WOOGALLERY_NOTICE_STATE_OPTION,
		$notice_state,
		$completed_notice_state
	);
}

/** Continues both separately bounded cleanup jobs after explicit confirmation. */
function kodlo_gallery_maybe_cleanup_woogallery_meta() {
	if ( ! current_user_can( 'manage_woocommerce' )
		|| ! kodlo_gallery_is_woogallery_compatibility_enabled() ) {
		return;
	}

	$notice_state = get_option( KODLO_GALLERY_WOOGALLERY_NOTICE_STATE_OPTION, array() );
	$generation   = is_array( $notice_state ) ? (string) ( $notice_state['generation'] ?? '' ) : '';
	if ( ! is_array( $notice_state )
		|| 'cleaning' !== ( $notice_state['phase'] ?? '' )
		|| '' === $generation ) {
		return;
	}

	foreach ( kodlo_gallery_get_woogallery_migration_definitions() as $legacy_key => $definition ) {
		kodlo_gallery_cleanup_legacy_meta_batch(
			$legacy_key,
			$definition['cleanup_state_option'],
			$definition['cleanup_lock_option'],
			$generation
		);
	}
	kodlo_gallery_maybe_complete_woogallery_cleanup_notice( $generation );
}

<?php
/**
 * WooGallery migration workers for Kodlo Product Gallery for Elementor.
 *
 * Copies exact legacy source rows into create-only Kodlo destinations in
 * resumable 50-row batches after explicit administrator consent.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Migrates one exact legacy attachment-video row.
 *
 * @param int    $meta_id        Exact legacy metadata row ID.
 * @param int    $attachment_id  Attachment ID.
 * @param string $legacy_key     Exact legacy metadata key.
 * @param string $raw_meta_value Exact raw database value selected by the worker.
 * @return bool True when migrated or intentionally skipped; false when retry is required.
 */
function kodlo_gallery_migrate_woogallery_video_meta( $meta_id, $attachment_id, $legacy_key, $raw_meta_value ) {
	if ( 'wcgs_video' !== $legacy_key || ! kodlo_gallery_is_supported_image_attachment( $attachment_id ) ) {
		return true;
	}

	$legacy_value = kodlo_gallery_decode_woogallery_database_value( $raw_meta_value );
	if ( ! is_string( $legacy_value ) ) {
		return true;
	}

	$video_url = esc_url_raw( trim( $legacy_value ) );
	if ( '' === $video_url ) {
		return true;
	}

	return kodlo_gallery_insert_migrated_post_meta(
		absint( $meta_id ),
		absint( $attachment_id ),
		$legacy_key,
		(string) $raw_meta_value,
		KODLO_GALLERY_VIDEO_META_KEY,
		$video_url
	);
}

/**
 * Migrates one exact legacy variation-gallery row.
 *
 * @param int    $meta_id                  Exact legacy metadata row ID.
 * @param int    $variation_id             Product variation ID.
 * @param string $legacy_key               Exact legacy metadata key.
 * @param string $raw_meta_value Exact raw database value selected by the worker.
 * @return bool True when migrated or intentionally skipped; false when retry is required.
 */
function kodlo_gallery_migrate_woogallery_variation_meta( $meta_id, $variation_id, $legacy_key, $raw_meta_value ) {
	if ( 'woo_gallery_slider' !== $legacy_key || 'product_variation' !== get_post_type( $variation_id ) ) {
		return true;
	}

	$legacy_value = kodlo_gallery_decode_woogallery_database_value( $raw_meta_value );
	$image_ids    = kodlo_gallery_normalize_legacy_variation_ids( $legacy_value );
	$image_ids    = array_values( array_filter( $image_ids, 'kodlo_gallery_is_supported_image_attachment' ) );
	if ( empty( $image_ids ) ) {
		return true;
	}

	return kodlo_gallery_insert_migrated_post_meta(
		absint( $meta_id ),
		absint( $variation_id ),
		$legacy_key,
		(string) $raw_meta_value,
		KODLO_GALLERY_VARIATION_META_KEY,
		implode( ',', $image_ids )
	);
}

/**
 * Primes all attachments referenced by exact variation rows in one batch.
 *
 * @param array<int, array<string, mixed>> $rows Exact database rows selected by the worker.
 */
function kodlo_gallery_prime_woogallery_variation_attachment_caches( $rows ) {
	$attachment_ids = array();
	foreach ( (array) $rows as $row ) {
		$legacy_value = kodlo_gallery_decode_woogallery_database_value( $row['meta_value'] ?? '' );
		foreach ( kodlo_gallery_normalize_legacy_variation_ids( $legacy_value ) as $attachment_id ) {
			$attachment_ids[ $attachment_id ] = true;
		}
	}

	if ( ! empty( $attachment_ids ) ) {
		kodlo_gallery_prime_attachment_caches( array_keys( $attachment_ids ) );
	}
}

/**
 * Migrates one exact-key batch inside its consent snapshot.
 *
 * @param string        $legacy_key        Exact legacy metadata key.
 * @param string        $state_option      Migration state option.
 * @param string        $lock_option       Migration lock option.
 * @param callable      $migration_callback Per-post migration callback.
 * @param string        $generation        Current activation generation.
 * @param callable|null $batch_preloader   Optional exact-row cache preloader.
 */
function kodlo_gallery_migrate_legacy_meta_batch(
	$legacy_key,
	$state_option,
	$lock_option,
	$migration_callback,
	$generation,
	$batch_preloader = null
) {
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

		if ( ! kodlo_gallery_database_worker_is_authorized( $state_option, $generation, 'migrating' ) ) {
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
		if ( is_callable( $batch_preloader ) ) {
			call_user_func( $batch_preloader, $rows );
		}

		$processed_posts = array();
		$processed_rows  = 0;
		foreach ( $rows as $row ) {
			if ( absint( $lock_value['expires_at'] ?? 0 ) < time() ) {
				break;
			}

			$meta_id        = absint( $row['meta_id'] ?? 0 );
			$post_id        = absint( $row['post_id'] ?? 0 );
			$raw_meta_value = (string) ( $row['meta_value'] ?? '' );
			if ( ! isset( $processed_posts[ $post_id ] ) ) {
				$callback_result = $migration_callback( $meta_id, $post_id, $legacy_key, $raw_meta_value );
				if ( true !== $callback_result ) {
					break;
				}
				$processed_posts[ $post_id ] = true;
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
 * Moves the notice to the cleanup decision after both migration jobs finish.
 *
 * @param string $generation Current activation generation.
 */
function kodlo_gallery_maybe_complete_woogallery_migration_notice( $generation ) {
	$notice_state = get_option( KODLO_GALLERY_WOOGALLERY_NOTICE_STATE_OPTION, array() );
	if ( ! is_array( $notice_state )
		|| 'migrating' !== ( $notice_state['phase'] ?? '' )
		|| ! hash_equals( (string) ( $notice_state['generation'] ?? '' ), $generation )
		|| ! kodlo_gallery_all_woogallery_workers_completed( $generation, 'state_option' ) ) {
		return;
	}

	$completed_notice_state          = $notice_state;
	$completed_notice_state['phase'] = 'cleanup_prompt';
	kodlo_gallery_compare_and_swap_option(
		KODLO_GALLERY_WOOGALLERY_NOTICE_STATE_OPTION,
		$notice_state,
		$completed_notice_state
	);
}

/** Continues both separately bounded migration jobs after explicit confirmation. */
function kodlo_gallery_maybe_migrate_woogallery_meta() {
	if ( ! current_user_can( 'manage_woocommerce' )
		|| ! kodlo_gallery_is_woogallery_compatibility_enabled() ) {
		return;
	}

	$notice_state = get_option( KODLO_GALLERY_WOOGALLERY_NOTICE_STATE_OPTION, array() );
	$generation   = is_array( $notice_state ) ? (string) ( $notice_state['generation'] ?? '' ) : '';
	if ( ! is_array( $notice_state )
		|| 'migrating' !== ( $notice_state['phase'] ?? '' )
		|| '' === $generation ) {
		return;
	}

	kodlo_gallery_migrate_legacy_meta_batch(
		'wcgs_video',
		KODLO_GALLERY_VIDEO_MIGRATION_STATE_OPTION,
		KODLO_GALLERY_VIDEO_MIGRATION_LOCK_OPTION,
		'kodlo_gallery_migrate_woogallery_video_meta',
		$generation
	);
	kodlo_gallery_migrate_legacy_meta_batch(
		'woo_gallery_slider',
		KODLO_GALLERY_VARIATION_MIGRATION_STATE_OPTION,
		KODLO_GALLERY_VARIATION_MIGRATION_LOCK_OPTION,
		'kodlo_gallery_migrate_woogallery_variation_meta',
		$generation,
		'kodlo_gallery_prime_woogallery_variation_attachment_caches'
	);
	kodlo_gallery_maybe_complete_woogallery_migration_notice( $generation );
}

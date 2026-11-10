<?php
/**
 * WooGallery migration definitions for Kodlo Product Gallery for Elementor.
 *
 * Owns the two supported legacy keys and captures immutable metadata-row
 * boundaries used by consented migration and cleanup jobs.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Returns the fixed legacy metadata definitions used by detection and migration.
 *
 * @return array<string, array<string, string>> Definitions keyed by legacy metadata key.
 */
function kodlo_gallery_get_woogallery_migration_definitions() {
	return array(
		'wcgs_video' => array(
			'label'                => __( 'attachment video URLs (wcgs_video)', 'kodlo-product-gallery-for-elementor' ),
			'state_option'         => KODLO_GALLERY_VIDEO_MIGRATION_STATE_OPTION,
			'cleanup_state_option' => KODLO_GALLERY_VIDEO_CLEANUP_STATE_OPTION,
			'cleanup_lock_option'  => KODLO_GALLERY_VIDEO_CLEANUP_LOCK_OPTION,
		),
		'woo_gallery_slider' => array(
			'label'                => __( 'variation gallery images (woo_gallery_slider)', 'kodlo-product-gallery-for-elementor' ),
			'state_option'         => KODLO_GALLERY_VARIATION_MIGRATION_STATE_OPTION,
			'cleanup_state_option' => KODLO_GALLERY_VARIATION_CLEANUP_STATE_OPTION,
			'cleanup_lock_option'  => KODLO_GALLERY_VARIATION_CLEANUP_LOCK_OPTION,
		),
	);
}

/**
 * Captures the latest legacy metadata ID for each supported key.
 *
 * Each exact-key query returns at most one row and defines a deterministic job boundary.
 *
 * @return array<string, int>|false Latest metadata IDs, or false after a database error.
 */
function kodlo_gallery_get_woogallery_legacy_snapshot() {
	global $wpdb;

	$snapshot = array();
	foreach ( array_keys( kodlo_gallery_get_woogallery_migration_definitions() ) as $legacy_key ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$latest_meta_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT meta_id FROM {$wpdb->postmeta}
				 WHERE meta_key = %s AND BINARY meta_key = BINARY %s
				 ORDER BY meta_id DESC LIMIT 1",
				$legacy_key,
				$legacy_key
			)
		);
		if ( '' !== (string) $wpdb->last_error ) {
			return false;
		}
		$snapshot[ $legacy_key ] = absint( $latest_meta_id );
	}

	return $snapshot;
}

/**
 * Counts exact legacy rows inside one worker boundary.
 *
 * @param string $legacy_key   Exact legacy metadata key.
 * @param int    $after_meta_id Excluded lower metadata ID boundary.
 * @param int    $max_meta_id   Included upper metadata ID boundary.
 * @return int|false Row count, or false after a database error.
 */
function kodlo_gallery_count_woogallery_legacy_rows( $legacy_key, $after_meta_id, $max_meta_id ) {
	global $wpdb;

	$after_meta_id = absint( $after_meta_id );
	$max_meta_id   = absint( $max_meta_id );
	if ( $max_meta_id <= $after_meta_id ) {
		return 0;
	}

	// The indexed key predicate narrows this one-time progress count before exact-case validation.
	// phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_key
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$row_count = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->postmeta}
			 WHERE meta_key = %s AND BINARY meta_key = BINARY %s
				 AND meta_id > %d AND meta_id <= %d",
			$legacy_key,
			$legacy_key,
			$after_meta_id,
			$max_meta_id
		)
	);
	// phpcs:enable WordPress.DB.SlowDBQuery.slow_db_query_meta_key
	if ( '' !== (string) $wpdb->last_error ) {
		return false;
	}

	return absint( $row_count );
}

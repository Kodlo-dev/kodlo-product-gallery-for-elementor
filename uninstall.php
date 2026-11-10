<?php
/**
 * Kodlo Product Gallery for Elementor uninstall cleanup.
 *
 * Removes operational migration state and abandoned locks from each affected
 * site while preserving gallery content stored in post metadata.
 *
 * @package Kodlo_Elementor_Gallery
 */

defined( 'ABSPATH' ) || exit;
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

/** Removes Kodlo Product Gallery operational data from the current site. */
function kodlo_gallery_delete_site_data(): void {
	$option_names = array(
		'kodlo_gallery_woogallery_video_migration_state',
		'kodlo_gallery_woogallery_video_migration_lock',
		'kodlo_gallery_woogallery_variation_migration_state',
		'kodlo_gallery_woogallery_variation_migration_lock',
		'kodlo_gallery_woogallery_notice_state',
		'kodlo_gallery_woogallery_video_cleanup_state',
		'kodlo_gallery_woogallery_video_cleanup_lock',
		'kodlo_gallery_woogallery_variation_cleanup_state',
		'kodlo_gallery_woogallery_variation_cleanup_lock',
		'kodlo_gallery_woogallery_action_lock',
		'kodlo_gallery_elementor_css_schema',
	);

	foreach ( $option_names as $option_name ) {
		delete_option( $option_name );
	}

	global $wpdb;
	$find_option_names = static function ( string $prefix ) use ( $wpdb ): array {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall discovers dynamic names before deleting them through the Options and Transients APIs.
		$names = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
				$wpdb->esc_like( $prefix ) . '%'
			)
		);
		return array_values( array_filter( (array) $names, 'is_string' ) );
	};

	foreach ( $find_option_names( 'kodlo_gallery_meta_write_lock_' ) as $lock_option_name ) {
		delete_option( $lock_option_name );
	}

	// Render-error log throttles expire within an hour; uninstall removes any that are still stored.
	foreach ( $find_option_names( '_transient_kodlo_gallery_render_error_' ) as $transient_option_name ) {
		delete_transient( substr( $transient_option_name, strlen( '_transient_' ) ) );
	}

	delete_metadata(
		'user',
		0,
		'_kodlo_gallery_meta_write_failure_' . get_current_blog_id(),
		'',
		true
	);
}

/** Removes operational data from one site or every site in the current network. */
function kodlo_gallery_uninstall(): void {
	if ( ! is_multisite() ) {
		kodlo_gallery_delete_site_data();
		return;
	}

	$sites_per_page = 100;
	$offset         = 0;

	do {
		$site_ids = get_sites(
			array(
				'fields'  => 'ids',
				'number'  => $sites_per_page,
				'offset'  => $offset,
				'orderby' => 'id',
				'order'   => 'ASC',
			)
		);

		foreach ( $site_ids as $site_id ) {
			switch_to_blog( (int) $site_id );
			try {
				kodlo_gallery_delete_site_data();
			} finally {
				restore_current_blog();
			}
		}

		$offset += $sites_per_page;
	} while ( count( $site_ids ) === $sites_per_page );
}

kodlo_gallery_uninstall();

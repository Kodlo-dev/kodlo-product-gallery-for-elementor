<?php
/**
 * Plugin Name: Kodlo Product Gallery for Elementor
 * Description: Lightweight product gallery widget for Elementor + WooCommerce. Supports variable products, custom variation galleries, and inline video.
 * Version:     2.9.6
 * Author:      Kodlo
 * Author URI:  https://kodlo.dev/
 * License:     GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: kodlo-product-gallery-for-elementor
 * Domain Path: /languages
 * Requires at least: 6.6
 * Requires PHP:      8.1
 * Tested up to:      7.1
 * Requires Plugins:  elementor, woocommerce
 * WC requires at least: 9.0
 * WC tested up to:       11.2.2
 * Elementor tested up to:     4.3.6
 * Elementor Pro tested up to: 4.3.6
 *
 * ==============================================================================
 * Main loader for Kodlo Product Gallery for Elementor.
 *
 * Defines the public plugin constants, registers lifecycle hooks, and loads
 * frontend or administrator components only in the requests that need them.
 * ==============================================================================
 *
 * @package Kodlo_Elementor_Gallery
 */

defined( 'ABSPATH' ) || exit;

// Plugin identity, requirement, and metadata constants.
define( 'KODLO_ELEMENTOR_GALLERY_VERSION', '2.9.6' );
define( 'KODLO_ELEMENTOR_GALLERY_FILE', __FILE__ );
define( 'KODLO_GALLERY_MINIMUM_PHP_VERSION', '8.1' );
define( 'KODLO_GALLERY_MINIMUM_WORDPRESS_VERSION', '6.6' );
define( 'KODLO_GALLERY_MINIMUM_WOOCOMMERCE_VERSION', '9.0' );
define( 'KODLO_GALLERY_MINIMUM_ELEMENTOR_VERSION', '3.22' );
define( 'KODLO_GALLERY_VIDEO_META_KEY',     '_kodlo_gallery_video_url' );
define( 'KODLO_GALLERY_VARIATION_META_KEY', '_kodlo_gallery_variation_images' );
define( 'KODLO_GALLERY_VIDEO_MIGRATION_STATE_OPTION', 'kodlo_gallery_woogallery_video_migration_state' );
define( 'KODLO_GALLERY_VIDEO_MIGRATION_LOCK_OPTION', 'kodlo_gallery_woogallery_video_migration_lock' );
define( 'KODLO_GALLERY_VARIATION_MIGRATION_STATE_OPTION', 'kodlo_gallery_woogallery_variation_migration_state' );
define( 'KODLO_GALLERY_VARIATION_MIGRATION_LOCK_OPTION', 'kodlo_gallery_woogallery_variation_migration_lock' );
define( 'KODLO_GALLERY_WOOGALLERY_NOTICE_STATE_OPTION', 'kodlo_gallery_woogallery_notice_state' );
define( 'KODLO_GALLERY_VIDEO_CLEANUP_STATE_OPTION', 'kodlo_gallery_woogallery_video_cleanup_state' );
define( 'KODLO_GALLERY_VIDEO_CLEANUP_LOCK_OPTION', 'kodlo_gallery_woogallery_video_cleanup_lock' );
define( 'KODLO_GALLERY_VARIATION_CLEANUP_STATE_OPTION', 'kodlo_gallery_woogallery_variation_cleanup_state' );
define( 'KODLO_GALLERY_VARIATION_CLEANUP_LOCK_OPTION', 'kodlo_gallery_woogallery_variation_cleanup_lock' );
define( 'KODLO_GALLERY_WOOGALLERY_ACTION_LOCK_OPTION', 'kodlo_gallery_woogallery_action_lock' );
define( 'KODLO_GALLERY_WOOGALLERY_MIGRATION_VERSION', 4 );

require_once __DIR__ . '/includes/bootstrap/kodlo-elementor-gallery-requirements.php';

/** Loads the option storage and activation lifecycle without loading migration workers. */
function kodlo_gallery_load_activation_component(): void {
	require_once __DIR__ . '/includes/storage/kodlo-elementor-gallery-option-state.php';
	require_once __DIR__ . '/includes/admin/migration/kodlo-elementor-gallery-activation.php';
}

/**
 * Starts the activation consent cycle, including WP-CLI and network activation.
 *
 * @param bool $network_wide Whether the plugin is being network activated.
 */
function kodlo_gallery_activate( $network_wide = false ): void {
	kodlo_gallery_load_activation_component();
	kodlo_gallery_run_activation( $network_wide );
}

/** Declares compatibility with WooCommerce High-Performance Order Storage. */
function kodlo_gallery_declare_woocommerce_compatibility(): void {
	if ( class_exists( '\\Automattic\\WooCommerce\\Utilities\\FeaturesUtil' ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
			'custom_order_tables',
			KODLO_ELEMENTOR_GALLERY_FILE,
			true
		);
	}
}

/** Loads Gallery integrations only when every runtime requirement is met. */
function kodlo_gallery_bootstrap(): void {
	if ( kodlo_gallery_get_unmet_requirements() ) {
		return;
	}

	require_once __DIR__ . '/includes/kodlo-elementor-gallery-media-validation.php';
	require_once __DIR__ . '/includes/kodlo-elementor-gallery-media-data.php';
	require_once __DIR__ . '/includes/kodlo-elementor-gallery-media-renderer.php';
	require_once __DIR__ . '/includes/kodlo-elementor-gallery-woogallery-compatibility.php';
	require_once __DIR__ . '/includes/elementor/kodlo-elementor-gallery-frontend-assets.php';
	require_once __DIR__ . '/includes/elementor/kodlo-elementor-gallery-widget-setup.php';

	if ( ! is_admin() ) {
		return;
	}

	require_once __DIR__ . '/includes/storage/kodlo-elementor-gallery-option-state.php';
	require_once __DIR__ . '/includes/storage/kodlo-elementor-gallery-meta-write-storage.php';
	require_once __DIR__ . '/includes/admin/kodlo-elementor-gallery-admin-assets.php';
	require_once __DIR__ . '/includes/admin/kodlo-elementor-gallery-meta-write-notice.php';
	require_once __DIR__ . '/includes/admin/kodlo-elementor-gallery-attachment-fields.php';
	require_once __DIR__ . '/includes/admin/kodlo-elementor-gallery-woogallery-migration.php';
	require_once __DIR__ . '/includes/admin/kodlo-elementor-gallery-variation-gallery.php';
}

register_activation_hook( KODLO_ELEMENTOR_GALLERY_FILE, 'kodlo_gallery_activate' );

add_action( 'admin_notices', 'kodlo_gallery_admin_requirements_notice', 10, 0 );
add_action( 'network_admin_notices', 'kodlo_gallery_admin_requirements_notice', 10, 0 );
add_action( 'before_woocommerce_init', 'kodlo_gallery_declare_woocommerce_compatibility', 10, 0 );
add_action( 'plugins_loaded', 'kodlo_gallery_bootstrap', 20, 0 );

if ( is_admin() ) {
	require_once __DIR__ . '/includes/admin/kodlo-elementor-gallery-plugin-row.php';
	kodlo_gallery_load_activation_component();

	add_filter( 'plugin_row_meta', 'kodlo_gallery_plugin_row_meta', 10, 2 );
	add_action( 'admin_init', 'kodlo_gallery_resume_activation_notice_cycle', 1, 0 );
}

<?php
/**
 * WooGallery migration lifecycle loader for Kodlo Product Gallery for Elementor.
 *
 * Loads the administrator-only consent, worker, and cleanup components and
 * registers their WordPress lifecycle hooks in one visible entry point.
 */

defined( 'ABSPATH' ) || exit;

$kodlo_gallery_migration_directory = __DIR__ . '/migration';

require_once $kodlo_gallery_migration_directory . '/kodlo-elementor-gallery-woogallery-definitions.php';
require_once $kodlo_gallery_migration_directory . '/kodlo-elementor-gallery-woogallery-worker-runtime.php';
require_once $kodlo_gallery_migration_directory . '/kodlo-elementor-gallery-woogallery-detection.php';
require_once $kodlo_gallery_migration_directory . '/kodlo-elementor-gallery-woogallery-progress.php';
require_once $kodlo_gallery_migration_directory . '/kodlo-elementor-gallery-woogallery-notices.php';
require_once $kodlo_gallery_migration_directory . '/kodlo-elementor-gallery-woogallery-actions.php';
require_once $kodlo_gallery_migration_directory . '/kodlo-elementor-gallery-woogallery-migration-worker.php';
require_once $kodlo_gallery_migration_directory . '/kodlo-elementor-gallery-woogallery-cleanup-worker.php';

unset( $kodlo_gallery_migration_directory );

add_action( 'admin_init', 'kodlo_gallery_prepare_woogallery_notice_state', 10, 0 );
add_action( 'admin_enqueue_scripts', 'kodlo_gallery_enqueue_woogallery_progress_assets', 10, 0 );
add_action( 'admin_notices', 'kodlo_gallery_render_woogallery_migration_notice', 10, 0 );
add_action( 'wp_ajax_kodlo_gallery_process_woogallery_batch', 'kodlo_gallery_handle_woogallery_progress_batch', 10, 0 );
add_action( 'admin_post_kodlo_gallery_dismiss_woogallery_migration_notice', 'kodlo_gallery_handle_dismiss_woogallery_migration_notice', 10, 0 );
add_action( 'admin_post_kodlo_gallery_start_woogallery_migration', 'kodlo_gallery_handle_start_woogallery_migration', 10, 0 );
add_action( 'admin_post_kodlo_gallery_keep_woogallery_legacy_data', 'kodlo_gallery_handle_keep_woogallery_legacy_data', 10, 0 );
add_action( 'admin_post_kodlo_gallery_delete_woogallery_legacy_data', 'kodlo_gallery_handle_delete_woogallery_legacy_data', 10, 0 );

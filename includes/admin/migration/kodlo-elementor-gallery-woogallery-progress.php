<?php
/**
 * WooGallery progress runner for Kodlo Product Gallery for Elementor.
 *
 * Exposes authenticated batch processing and loads the administrator progress
 * interface only while migration or cleanup is active.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Adds progress counters to resumable worker states created by an earlier build.
 *
 * @param string $phase      Active notice phase.
 * @param string $generation Current activation generation.
 * @return bool True when every worker has a usable progress state.
 */
function kodlo_gallery_prepare_woogallery_progress_state( $phase, $generation ) {
	$state_key = 'cleaning' === $phase ? 'cleanup_state_option' : 'state_option';
	foreach ( kodlo_gallery_get_woogallery_migration_definitions() as $legacy_key => $definition ) {
		$state_option = $definition[ $state_key ];
		$worker_state = get_option( $state_option, array() );
		if ( ! kodlo_gallery_is_current_woogallery_worker_state( $worker_state, $generation ) ) {
			return false;
		}
		if ( isset( $worker_state['total_rows'], $worker_state['processed_rows'] ) ) {
			continue;
		}

		$start_cursor = absint( $worker_state['cursor'] ?? 0 );
		$total_rows   = 'completed' === ( $worker_state['status'] ?? '' )
			? 0
			: kodlo_gallery_count_woogallery_legacy_rows(
				$legacy_key,
				$start_cursor,
				absint( $worker_state['max_meta_id'] ?? 0 )
			);
		if ( false === $total_rows ) {
			return false;
		}

		$progress_state                          = $worker_state;
		$progress_state['processed_rows']        = 0;
		$progress_state['total_rows']            = $total_rows;
		if ( ! kodlo_gallery_compare_and_swap_option( $state_option, $worker_state, $progress_state ) ) {
			$database_error = false;
			$current_state  = kodlo_gallery_get_uncached_internal_option( $state_option, $database_error );
			if ( $database_error
				|| ! kodlo_gallery_is_current_woogallery_worker_state( $current_state, $generation )
				|| ! isset( $current_state['total_rows'], $current_state['processed_rows'] ) ) {
				return false;
			}
		}
	}

	return true;
}

/**
 * Returns aggregate progress for the active migration or cleanup workers.
 *
 * @param string $phase      Active notice phase.
 * @param string $generation Current activation generation.
 * @return array{processed: int, total: int, percentage: int}
 */
function kodlo_gallery_get_woogallery_progress_state( $phase, $generation ) {
	$state_key = 'cleaning' === $phase ? 'cleanup_state_option' : 'state_option';
	$processed = 0;
	$total     = 0;

	foreach ( kodlo_gallery_get_woogallery_migration_definitions() as $definition ) {
		$worker_state = get_option( $definition[ $state_key ], array() );
		if ( ! kodlo_gallery_is_current_woogallery_worker_state( $worker_state, $generation ) ) {
			continue;
		}

		$worker_total = absint( $worker_state['total_rows'] ?? 0 );
		$total        += $worker_total;
		$processed    += min( absint( $worker_state['processed_rows'] ?? 0 ), $worker_total );
	}

	$percentage = $total > 0 ? min( 100, (int) floor( $processed * 100 / $total ) ) : 0;
	return array(
		'processed'  => $processed,
		'total'      => $total,
		'percentage' => $percentage,
	);
}

/** Loads the progress interface only while administrator-authorized work is active. */
function kodlo_gallery_enqueue_woogallery_progress_assets() {
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		return;
	}

	$notice_state = get_option( KODLO_GALLERY_WOOGALLERY_NOTICE_STATE_OPTION, array() );
	$phase        = is_array( $notice_state ) ? (string) ( $notice_state['phase'] ?? '' ) : '';
	$generation   = is_array( $notice_state ) ? (string) ( $notice_state['generation'] ?? '' ) : '';
	if ( ! in_array( $phase, array( 'migrating', 'cleaning' ), true ) || '' === $generation ) {
		return;
	}
	kodlo_gallery_prepare_woogallery_progress_state( $phase, $generation );

	wp_enqueue_style(
		'kodlo-gallery-woogallery-progress',
		plugins_url( 'assets/css/kodlo-elementor-gallery-migration-progress.css', KODLO_ELEMENTOR_GALLERY_FILE ),
		array(),
		KODLO_ELEMENTOR_GALLERY_VERSION
	);
	wp_enqueue_script(
		'kodlo-gallery-woogallery-progress',
		plugins_url( 'assets/js/kodlo-elementor-gallery-migration-progress.js', KODLO_ELEMENTOR_GALLERY_FILE ),
		array(),
		KODLO_ELEMENTOR_GALLERY_VERSION,
		true
	);
	wp_localize_script(
		'kodlo-gallery-woogallery-progress',
		'kodloGalleryWooGalleryProgress',
		array(
			'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
			'action'         => 'kodlo_gallery_process_woogallery_batch',
			'nonce'          => wp_create_nonce( 'kodlo_gallery_process_woogallery_batch_' . $generation ),
			'generation'     => $generation,
			'failedMessage'  => __( 'The process paused because a batch could not be completed. Check the server logs, then retry.', 'kodlo-product-gallery-for-elementor' ),
			'retryLabel'     => __( 'Retry processing', 'kodlo-product-gallery-for-elementor' ),
			'completeDelay'  => 500,
			'batchDelay'     => 75,
			'requestTimeout' => 30000,
		)
	);
}

/** Processes one protected batch cycle and returns current aggregate progress. */
function kodlo_gallery_handle_woogallery_progress_batch() {
	if ( ! current_user_can( 'manage_woocommerce' )
		|| ! kodlo_gallery_is_woogallery_compatibility_enabled() ) {
		wp_send_json_error(
			array( 'message' => __( 'You are not allowed to manage this migration.', 'kodlo-product-gallery-for-elementor' ) ),
			403
		);
	}

	$notice_state = get_option( KODLO_GALLERY_WOOGALLERY_NOTICE_STATE_OPTION, array() );
	$phase        = is_array( $notice_state ) ? (string) ( $notice_state['phase'] ?? '' ) : '';
	$generation   = is_array( $notice_state ) ? (string) ( $notice_state['generation'] ?? '' ) : '';
	if ( ! in_array( $phase, array( 'migrating', 'cleaning' ), true ) || '' === $generation ) {
		wp_send_json_error(
			array( 'message' => __( 'This migration request is no longer valid. Reload the page and try again.', 'kodlo-product-gallery-for-elementor' ) ),
			409
		);
	}

	check_ajax_referer( 'kodlo_gallery_process_woogallery_batch_' . $generation, 'nonce' );
	$request_generation = isset( $_POST['generation'] ) && is_scalar( $_POST['generation'] )
		? sanitize_text_field( wp_unslash( (string) $_POST['generation'] ) )
		: '';
	if ( ! hash_equals( $generation, $request_generation ) ) {
		wp_send_json_error(
			array( 'message' => __( 'This migration request is no longer valid. Reload the page and try again.', 'kodlo-product-gallery-for-elementor' ) ),
			409
		);
	}
	if ( ! kodlo_gallery_prepare_woogallery_progress_state( $phase, $generation ) ) {
		wp_send_json_error(
			array( 'message' => __( 'The process could not prepare its progress state. Check the server logs, then retry.', 'kodlo-product-gallery-for-elementor' ) ),
			500
		);
	}

	$before_progress = kodlo_gallery_get_woogallery_progress_state( $phase, $generation );
	if ( 'migrating' === $phase ) {
		kodlo_gallery_maybe_migrate_woogallery_meta();
	} else {
		kodlo_gallery_maybe_cleanup_woogallery_meta();
	}

	$current_notice_state = get_option( KODLO_GALLERY_WOOGALLERY_NOTICE_STATE_OPTION, array() );
	$current_phase        = is_array( $current_notice_state ) ? (string) ( $current_notice_state['phase'] ?? '' ) : '';
	$current_generation   = is_array( $current_notice_state ) ? (string) ( $current_notice_state['generation'] ?? '' ) : '';
	if ( ! hash_equals( $generation, $current_generation ) ) {
		wp_send_json_error(
			array( 'message' => __( 'This migration request is no longer valid. Reload the page and try again.', 'kodlo-product-gallery-for-elementor' ) ),
			409
		);
	}
	$progress             = kodlo_gallery_get_woogallery_progress_state( $phase, $generation );
	$completed            = $phase !== $current_phase;
	if ( $completed ) {
		$progress['percentage'] = 100;
	}

	$message = sprintf(
		/* translators: 1: Processed legacy row count. 2: Total legacy row count. */
		__( 'Processed %1$d of %2$d legacy records.', 'kodlo-product-gallery-for-elementor' ),
		$progress['processed'],
		$progress['total']
	);
	if ( $completed && 'migrating' === $phase ) {
		$message = __( 'Migration is complete. Loading the legacy-data decision now.', 'kodlo-product-gallery-for-elementor' );
	} elseif ( $completed ) {
		$message = __( 'Legacy-data cleanup is complete.', 'kodlo-product-gallery-for-elementor' );
	}

	wp_send_json_success(
		array(
			'completed'    => $completed,
			'message'      => $message,
			'percentage'   => $progress['percentage'],
			'reload'       => $completed && 'migrating' === $phase,
			'stalled'      => ! $completed && $before_progress['processed'] === $progress['processed'],
		)
	);
}

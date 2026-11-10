<?php
/**
 * WooGallery legacy-data detection for Kodlo Product Gallery for Elementor.
 *
 * Runs one bounded snapshot check after activation fencing and selects the
 * appropriate administrator notice without changing legacy metadata.
 */

defined( 'ABSPATH' ) || exit;

/** Prepares the current activation notice after one bounded legacy-data check. */
function kodlo_gallery_prepare_woogallery_notice_state() {
	if ( ! current_user_can( 'manage_woocommerce' )
		|| ! kodlo_gallery_is_woogallery_compatibility_enabled() ) {
		return;
	}

	$notice_state = get_option( KODLO_GALLERY_WOOGALLERY_NOTICE_STATE_OPTION, array() );
	if ( ! is_array( $notice_state )
		|| KODLO_GALLERY_WOOGALLERY_MIGRATION_VERSION !== (int) ( $notice_state['version'] ?? 0 )
		|| 'detect' !== ( $notice_state['phase'] ?? '' )
		|| empty( $notice_state['generation'] ) ) {
		return;
	}
	$expected_notice_state = $notice_state;

	$snapshot = kodlo_gallery_get_woogallery_legacy_snapshot();
	if ( false === $snapshot ) {
		return;
	}
	$detected_keys = array_keys( array_filter( $snapshot ) );
	$migration_due = false;
	$definitions   = kodlo_gallery_get_woogallery_migration_definitions();
	$generation    = (string) $notice_state['generation'];

	foreach ( $detected_keys as $legacy_key ) {
		$worker_state = get_option( $definitions[ $legacy_key ]['state_option'], array() );
		if ( ! kodlo_gallery_is_current_woogallery_worker_state( $worker_state, $generation )
			|| 'completed' !== ( $worker_state['status'] ?? '' )
			|| absint( $worker_state['max_meta_id'] ?? 0 ) < $snapshot[ $legacy_key ] ) {
			$migration_due = true;
			break;
		}
	}

	$notice_state['detected_keys'] = $detected_keys;
	if ( empty( $detected_keys ) ) {
		$notice_state['phase'] = 'done';
	} elseif ( $migration_due ) {
		$notice_state['phase'] = 'migration_prompt';
	} else {
		$notice_state['phase'] = 'cleanup_prompt';
	}

	kodlo_gallery_compare_and_swap_option(
		KODLO_GALLERY_WOOGALLERY_NOTICE_STATE_OPTION,
		$expected_notice_state,
		$notice_state
	);
}

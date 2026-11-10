<?php
/**
 * WooGallery consent notices for Kodlo Product Gallery for Elementor.
 *
 * Renders consent, active progress, and the separate keep-or-delete decision
 * after all consented migration workers complete.
 */

defined( 'ABSPATH' ) || exit;

/** Renders the migration consent or post-migration retention notice. */
function kodlo_gallery_render_woogallery_migration_notice() {
	if ( ! current_user_can( 'manage_woocommerce' )
		|| ! kodlo_gallery_is_woogallery_compatibility_enabled() ) {
		return;
	}

	$notice_state = get_option( KODLO_GALLERY_WOOGALLERY_NOTICE_STATE_OPTION, array() );
	if ( ! is_array( $notice_state )
		|| KODLO_GALLERY_WOOGALLERY_MIGRATION_VERSION !== (int) ( $notice_state['version'] ?? 0 ) ) {
		return;
	}

	$generation = (string) ( $notice_state['generation'] ?? '' );
	if ( '' === $generation ) {
		return;
	}

	if ( 'migration_prompt' === ( $notice_state['phase'] ?? '' ) ) {
		kodlo_gallery_render_woogallery_start_notice( $notice_state, $generation );
	} elseif ( 'cleanup_prompt' === ( $notice_state['phase'] ?? '' ) ) {
		kodlo_gallery_render_woogallery_cleanup_notice( $generation );
	} elseif ( in_array( $notice_state['phase'] ?? '', array( 'migrating', 'cleaning' ), true ) ) {
		kodlo_gallery_render_woogallery_progress_notice( (string) $notice_state['phase'] );
	}
}

/**
 * Renders the persistent progress interface for migration or cleanup.
 *
 * @param string $phase Active notice phase.
 */
function kodlo_gallery_render_woogallery_progress_notice( $phase ) {
	$is_cleanup = 'cleaning' === $phase;
	$heading    = $is_cleanup
		? __( 'Kodlo is removing safely migrated legacy data.', 'kodlo-product-gallery-for-elementor' )
		: __( 'Kodlo is migrating legacy WooGallery data.', 'kodlo-product-gallery-for-elementor' );
	$label = $is_cleanup
		? __( 'Legacy-data cleanup progress', 'kodlo-product-gallery-for-elementor' )
		: __( 'Legacy-data migration progress', 'kodlo-product-gallery-for-elementor' );

	echo '<div class="notice notice-info kodlo-gallery-woogallery-progress" data-kodlo-woogallery-progress aria-busy="true">';
	echo '<p><strong>' . esc_html( $heading ) . '</strong></p>';
	echo '<p>' . esc_html__( 'Do not reload, close, or leave this page until the process is complete.', 'kodlo-product-gallery-for-elementor' ) . '</p>';
	echo '<p><label for="kodlo-gallery-woogallery-progress">' . esc_html( $label ) . '</label>';
	echo '<progress id="kodlo-gallery-woogallery-progress" max="100" value="0">0%</progress></p>';
	echo '<p data-kodlo-woogallery-status aria-live="polite">' . esc_html__( 'Preparing the first batch...', 'kodlo-product-gallery-for-elementor' ) . '</p>';
	echo '</div>';
}

/**
 * Renders the explicit migration confirmation form.
 *
 * @param array  $notice_state Current notice state.
 * @param string $generation   Current activation generation.
 */
function kodlo_gallery_render_woogallery_start_notice( $notice_state, $generation ) {
	$definitions = kodlo_gallery_get_woogallery_migration_definitions();
	$labels      = array();
	foreach ( (array) ( $notice_state['detected_keys'] ?? array() ) as $legacy_key ) {
		if ( isset( $definitions[ $legacy_key ] ) ) {
			$labels[] = $definitions[ $legacy_key ]['label'];
		}
	}
	$dismiss_url = wp_nonce_url(
		add_query_arg(
			array(
				'action'                   => 'kodlo_gallery_dismiss_woogallery_migration_notice',
				'kodlo_gallery_generation' => $generation,
			),
			admin_url( 'admin-post.php' )
		),
		'kodlo_gallery_dismiss_woogallery_migration_notice_' . $generation
	);

	echo '<div class="notice notice-warning">';
	echo '<p><strong>' . esc_html__( 'Kodlo found legacy WooGallery data in the WordPress post metadata table.', 'kodlo-product-gallery-for-elementor' ) . '</strong></p>';
	if ( ! empty( $labels ) ) {
		echo '<ul>';
		foreach ( $labels as $label ) {
			echo '<li>' . esc_html( $label ) . '</li>';
		}
		echo '</ul>';
	}
	echo '<p>' . esc_html__( 'Would you like to copy this data into Kodlo Product Gallery? After confirmation, migration starts here immediately in safe 50-record batches and never removes the original WooGallery metadata automatically.', 'kodlo-product-gallery-for-elementor' ) . '</p>';
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	echo '<input type="hidden" name="action" value="kodlo_gallery_start_woogallery_migration">';
	echo '<input type="hidden" name="kodlo_gallery_generation" value="' . esc_attr( $generation ) . '">';
	wp_nonce_field( 'kodlo_gallery_start_woogallery_migration_' . $generation );
	echo '<p>';
	submit_button( __( 'Migrate legacy data', 'kodlo-product-gallery-for-elementor' ), 'primary', 'submit', false );
	echo ' <a href="' . esc_url( $dismiss_url ) . '">' . esc_html__( 'Dismiss this notice', 'kodlo-product-gallery-for-elementor' ) . '</a>';
	echo '</p>';
	echo '</form>';
	echo '</div>';
}

/**
 * Renders the post-migration keep-or-delete decision.
 *
 * @param string $generation Current activation generation.
 */
function kodlo_gallery_render_woogallery_cleanup_notice( $generation ) {
	echo '<div class="notice notice-success">';
	echo '<p><strong>' . esc_html__( 'WooGallery metadata migration is complete.', 'kodlo-product-gallery-for-elementor' ) . '</strong></p>';
	echo '<p>' . esc_html__( 'The original legacy metadata is still stored. Keeping it is the safest default. If you choose deletion, Kodlo removes only supported legacy records that have a corresponding Kodlo value; skipped or unmigrated records remain untouched.', 'kodlo-product-gallery-for-elementor' ) . '</p>';
	echo '<p>' . esc_html__( 'Deleting legacy metadata cannot be undone. Create a database backup before continuing.', 'kodlo-product-gallery-for-elementor' ) . '</p>';
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	echo '<input type="hidden" name="kodlo_gallery_generation" value="' . esc_attr( $generation ) . '">';
	wp_nonce_field( 'kodlo_gallery_keep_woogallery_legacy_data_' . $generation, 'kodlo_gallery_keep_nonce', false );
	wp_nonce_field( 'kodlo_gallery_delete_woogallery_legacy_data_' . $generation, 'kodlo_gallery_delete_nonce', false );
	echo '<p>';
	echo '<button type="submit" name="action" value="kodlo_gallery_keep_woogallery_legacy_data" class="button button-primary">' . esc_html__( 'Keep legacy data', 'kodlo-product-gallery-for-elementor' ) . '</button> ';
	echo '<button type="submit" name="action" value="kodlo_gallery_delete_woogallery_legacy_data" class="button">' . esc_html__( 'Delete safely migrated legacy data', 'kodlo-product-gallery-for-elementor' ) . '</button>';
	echo '</p>';
	echo '</form>';
	echo '</div>';
}

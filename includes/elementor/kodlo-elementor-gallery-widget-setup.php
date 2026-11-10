<?php
/**
 * Widget registration and setup for Kodlo Product Gallery for Elementor.
 *
 * Registers the custom widget after the main bootstrap validates dependencies.
 */

defined( 'ABSPATH' ) || exit;

// Registers the Kodlo product gallery widget with Elementor.
add_action( 'elementor/widgets/register', 'kodlo_gallery_register_widget' );
add_action( 'elementor/init', 'kodlo_gallery_refresh_elementor_css_once', 10, 0 );

/**
 * Clears Elementor's generated CSS once after an update that changed the widget's style selectors.
 *
 * Saved pages otherwise keep rules from the previous selectors until each one is edited again.
 */
function kodlo_gallery_refresh_elementor_css_once(): void {
	$css_schema = '2.9.6';
	if ( get_option( 'kodlo_gallery_elementor_css_schema' ) === $css_schema ) { return; }

	$files_manager = \Elementor\Plugin::$instance->files_manager ?? null;
	if ( ! is_object( $files_manager ) || ! method_exists( $files_manager, 'clear_cache' ) ) { return; }

	update_option( 'kodlo_gallery_elementor_css_schema', $css_schema, true );
	$files_manager->clear_cache();
}

/**
 * Registers the gallery widget.
 *
 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widget manager.
 */
function kodlo_gallery_register_widget( $widgets_manager ) {
	require_once dirname( __DIR__ ) . '/class-kodlo-elementor-gallery-widget.php';
	$widgets_manager->register( new \Kodlo_Product_Gallery_Widget() );
}

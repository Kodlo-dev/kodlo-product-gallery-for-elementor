<?php
/**
 * Widget controls for Kodlo Product Gallery for Elementor.
 *
 * Defines all layout, styling, video and states tabs settings in TAB_CONTENT and TAB_STYLE.
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

$breakpoint_options = array();
$breakpoints_manager = \Elementor\Plugin::$instance->breakpoints ?? null;
$active_breakpoints = is_object( $breakpoints_manager ) ? (array) $breakpoints_manager->get_active_breakpoints() : array();

foreach ( $active_breakpoints as $breakpoint ) {
	$breakpoint_value = (int) $breakpoint->get_value();
	$breakpoint_label = $breakpoint->get_label();
	$breakpoint_direction = $breakpoint->get_direction();
	$breakpoint_threshold = false !== strpos( (string) $breakpoint_direction, 'min' ) ? $breakpoint_value : $breakpoint_value + 1;

	if ( $breakpoint_value > 0 ) {
		$breakpoint_options[ (string) $breakpoint_threshold ] = sprintf(
			/* translators: 1: breakpoint label, 2: breakpoint width. */
			esc_html__( '%1$s (%2$d px)', 'kodlo-product-gallery-for-elementor' ),
			$breakpoint_label,
			$breakpoint_threshold
		);
	}
}

$default_mobile_breakpoint = isset( $breakpoint_options['768'] ) ? '768' : (string) key( $breakpoint_options );
$object_fit_options = array(
	'cover'   => esc_html__( 'Cover', 'kodlo-product-gallery-for-elementor' ),
	'contain' => esc_html__( 'Contain', 'kodlo-product-gallery-for-elementor' ),
	'fill'    => esc_html__( 'Fill', 'kodlo-product-gallery-for-elementor' ),
);
$aspect_ratio_options = array(
	'1 / 1'  => '1:1',
	'4 / 5'  => '4:5',
	'5 / 6'  => '5:6',
	'3 / 4'  => '3:4',
	'2 / 3'  => '2:3',
	'9 / 16' => '9:16',
	'5 / 4'  => '5:4',
	'6 / 5'  => '6:5',
	'4 / 3'  => '4:3',
	'3 / 2'  => '3:2',
	'16 / 9' => '16:9',
	'21 / 9' => '21:9',
);

// Content controls
require __DIR__ . '/elementor/controls/content/kodlo-elementor-gallery-desktop-view-settings.php';
require __DIR__ . '/elementor/controls/content/kodlo-elementor-gallery-mobile-view-options.php';
require __DIR__ . '/elementor/controls/content/kodlo-elementor-gallery-thumbnails-settings.php';
require __DIR__ . '/elementor/controls/content/kodlo-elementor-gallery-mobile-navigation-options.php';
require __DIR__ . '/elementor/controls/content/kodlo-elementor-gallery-video-options.php';
require __DIR__ . '/elementor/controls/content/kodlo-elementor-gallery-general-settings.php';

// Style controls
require __DIR__ . '/elementor/controls/style/kodlo-elementor-gallery-desktop-style-settings.php';
require __DIR__ . '/elementor/controls/style/kodlo-elementor-gallery-mobile-style-settings.php';
require __DIR__ . '/elementor/controls/style/kodlo-elementor-gallery-thumbnails-style-settings.php';
require __DIR__ . '/elementor/controls/style/kodlo-elementor-gallery-navigation-style.php';
require __DIR__ . '/elementor/controls/style/kodlo-elementor-gallery-video-style-settings.php';
require __DIR__ . '/elementor/controls/style/kodlo-elementor-gallery-general-style-settings.php';

// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

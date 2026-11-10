<?php
/**
 * Per-instance dynamic styles for Kodlo Product Gallery for Elementor.
 *
 * Builds breakpoint and edge-bleed rules from the normalized render context.
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

// The gallery ID is already escaped and safe to concatenate into constrained selectors.
// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
$gallery_id_attr = esc_attr( $widget_id );
$gallery_selector = '.kodlo-gallery-wrapper[data-gallery-id="' . $gallery_id_attr . '"]';
$screen_navigation_selector = $gallery_selector . '[data-navigation-edge-nav="screen"]';

// Desktop and mobile share the same slides, so each device's appearance values apply only inside its breakpoint.
// Controls set --kodlo-desktop-* / --kodlo-mobile-* variables, which keeps every change live in the Elementor editor.
// Values without a default are applied only once set, so site CSS keeps working for galleries that leave them empty.
$has_dimension_value = static function ( $value ): bool {
	if ( ! is_array( $value ) ) { return false; }
	foreach ( array( 'top', 'right', 'bottom', 'left' ) as $side ) {
		if ( isset( $value[ $side ] ) && '' !== (string) $value[ $side ] ) { return true; }
	}
	return false;
};
$has_color_value = static function ( string $key ) use ( $settings ): bool {
	return ! empty( $settings[ $key ] ) || ! empty( $settings['__globals__'][ $key ] );
};
$is_editor_preview = $this->is_editor_request();
$desktop_items = $gallery_selector . ' .kodlo-gallery-main-wrapper .kodlo-media-item';
$desktop_entries = $gallery_selector . ' .kodlo-gallery-main-wrapper .kodlo-gallery-entry';
$mobile_items = $gallery_selector . ' .kodlo-mobile-slider .kodlo-media-item';
$mobile_slides = $gallery_selector . ' .kodlo-mobile-slider .kodlo-gallery-main-slide';

$desktop_device_css = $desktop_items . ' { background-color: var(--kodlo-image-background, #ffffff) !important;'
	. ' --kodlo-magnifier-size: var(--kodlo-desktop-magnifier-size, 160px);'
	. ' --kodlo-magnifier-zoom: var(--kodlo-desktop-magnifier-zoom, 2);'
	. ' --kodlo-lens-border-style: var(--kodlo-desktop-magnifier-border-style, solid);'
	. ' --kodlo-lens-border-width: var(--kodlo-desktop-magnifier-border-width, 2px);'
	. ' --kodlo-lens-border-color: var(--kodlo-desktop-magnifier-border-color, #fff);'
	. ' --kodlo-lens-border-radius: var(--kodlo-desktop-magnifier-border-radius, 50%); }'
	. $desktop_items . ' img.kodlo-main-image { object-fit: var(--kodlo-object-fit, cover); }'
	. $desktop_entries . ' { border-style: var(--kodlo-desktop-border-style, none) !important;'
	. ' border-width: var(--kodlo-desktop-border-width, 1px) !important;'
	. ( $is_editor_preview || $has_color_value( 'grid_border_color' )
		? ' border-color: var(--kodlo-desktop-border-color, currentColor) !important;'
		: '' )
	. ' }'
	. $desktop_entries . ':hover { border-style: var(--kodlo-desktop-border-hover-style, var(--kodlo-desktop-border-style, none)) !important;'
	. ' border-width: var(--kodlo-desktop-border-hover-width, var(--kodlo-desktop-border-width, 1px)) !important;'
	. ( $is_editor_preview || $has_color_value( 'grid_border_hover_color' ) || $has_color_value( 'grid_border_color' )
		? ' border-color: var(--kodlo-desktop-border-hover-color, var(--kodlo-desktop-border-color, currentColor)) !important;'
		: '' )
	. ' }';
if ( $is_editor_preview || $has_dimension_value( $settings['main_border_radius'] ?? null ) ) {
	$desktop_device_css .= $desktop_items . ', ' . $desktop_entries
		. ' { border-radius: var(--kodlo-desktop-radius, 0px) !important; }';
}
if ( 'zoom' === $desktop_image_effect ) {
	$desktop_device_css .= $desktop_items . ':hover img.kodlo-main-image'
		. ' { transform: scale(var(--kodlo-desktop-zoom-scale, 1.08)); }';
}

$mobile_device_css = $mobile_items . ' { background-color: var(--kodlo-mobile-image-background, #ffffff) !important;'
	. ' --kodlo-magnifier-size: var(--kodlo-mobile-magnifier-size, 140px);'
	. ' --kodlo-magnifier-zoom: var(--kodlo-mobile-magnifier-zoom, 2);'
	. ' --kodlo-lens-border-style: var(--kodlo-mobile-magnifier-border-style, solid);'
	. ' --kodlo-lens-border-width: var(--kodlo-mobile-magnifier-border-width, 2px);'
	. ' --kodlo-lens-border-color: var(--kodlo-mobile-magnifier-border-color, #fff);'
	. ' --kodlo-lens-border-radius: var(--kodlo-mobile-magnifier-border-radius, 50%); }'
	. $mobile_items . ' img.kodlo-main-image { object-fit: var(--kodlo-mobile-object-fit, cover); }'
	. $mobile_slides . ' { border-style: var(--kodlo-mobile-border-style, none) !important;'
	. ' border-width: var(--kodlo-mobile-border-width, 0px) !important;'
	. ( $is_editor_preview || $has_color_value( 'mobile_main_border_color' )
		? ' border-color: var(--kodlo-mobile-border-color, currentColor) !important;'
		: '' )
	. ' }';
if ( $is_editor_preview || $has_color_value( 'mobile_main_border_hover_color' ) || $has_color_value( 'mobile_main_border_color' ) ) {
	$mobile_device_css .= $mobile_slides . ':hover'
		. ' { border-color: var(--kodlo-mobile-border-hover-color, var(--kodlo-mobile-border-color, currentColor)) !important; }';
}
if ( $is_editor_preview || $has_dimension_value( $settings['mobile_main_border_radius'] ?? null ) ) {
	$mobile_device_css .= $mobile_slides . ', ' . $mobile_items
		. ' { border-radius: var(--kodlo-mobile-radius, 0px) !important; }';
}
if ( 'zoom' === $mobile_image_effect ) {
	$mobile_device_css .= $mobile_items . ':hover img.kodlo-main-image'
		. ' { transform: scale(var(--kodlo-mobile-zoom-scale, 1.08)); }';
}

ob_start();
if ( $desktop_view_enabled && $mobile_view_enabled ) {
	echo '@media (min-width: ' . (int) $desktop_min_width . 'px) {';
	echo $gallery_selector . ' .kodlo-gallery-main-wrapper { display: grid !important; }';
	echo $gallery_selector . ' .kodlo-gallery-main-slide {';
	echo ' display: block !important; width: 100% !important; height: auto !important; margin: 0 !important; }';
	echo $gallery_selector . ' .kodlo-main-slider-wrap .kodlo-nav-btn,';
	echo $gallery_selector . ' .kodlo-main-slider-wrap .swiper-pagination,';
	echo $gallery_selector . ' .kodlo-thumbs-slider-wrap { display: none !important; }';
	echo $gallery_selector . ' .kodlo-hide-desktop { display: none !important; }';
	echo $desktop_device_css;
	echo '}';
	echo '@media (max-width: ' . (int) $mobile_max_width . 'px) {';
	echo $gallery_selector . ' .kodlo-gallery-main-wrapper { display: flex !important; gap: 0 !important; }';
	echo $gallery_selector . ' .kodlo-mobile-slider .kodlo-media-item {';
	echo ' aspect-ratio: var(--kodlo-mobile-aspect-ratio); }';
	echo $gallery_selector . ' .kodlo-main-slider-wrap .kodlo-nav-btn { display: flex !important; }';
	echo $gallery_selector . ' .kodlo-thumbs-slider-wrap { display: flex !important; }';
	echo $gallery_selector . ' .kodlo-hide-mobile { display: none !important; }';
	echo $mobile_device_css;
	echo '}';
}
if ( $mobile_view_enabled && ! $desktop_view_enabled ) {
	echo $gallery_selector . ' .kodlo-mobile-slider .kodlo-media-item {';
	echo ' aspect-ratio: var(--kodlo-mobile-aspect-ratio); }';
	echo $mobile_device_css;
}
if ( $desktop_view_enabled && ! $mobile_view_enabled ) {
	echo $desktop_device_css;
}
if ( $mobile_edge_bleed && $mobile_view_enabled ) {
	if ( $desktop_view_enabled ) { echo '@media (max-width: ' . (int) $mobile_max_width . 'px) {'; }
	echo $gallery_selector . ' { overflow-x: clip; left: var(--kodlo-mobile-side-gutter-negative);';
	echo ' width: calc(100% - 2 * var(--kodlo-mobile-side-gutter-negative)); }';
	echo $gallery_selector . ' .kodlo-main-slider-wrap,';
	echo $gallery_selector . ' .kodlo-thumbs-slider-wrap {';
	echo ' margin-left: calc(-1 * var(--kodlo-mobile-side-gutter-negative));';
	echo ' margin-right: calc(-1 * var(--kodlo-mobile-side-gutter-negative)); }';
	echo $screen_navigation_selector . ' .kodlo-main-slider-wrap.kodlo-nav-overlay .kodlo-nav-prev,';
	echo $screen_navigation_selector . ' .kodlo-thumbs-slider-wrap.kodlo-nav-overlay .kodlo-nav-prev {';
	echo ' left: calc(var(--kodlo-mobile-side-gutter-negative) + var(--kodlo-nav-offset, 8px)); }';
	echo $screen_navigation_selector . ' .kodlo-main-slider-wrap.kodlo-nav-overlay .kodlo-nav-next,';
	echo $screen_navigation_selector . ' .kodlo-thumbs-slider-wrap.kodlo-nav-overlay .kodlo-nav-next {';
	echo ' right: calc(var(--kodlo-mobile-side-gutter-negative) + var(--kodlo-nav-offset, 8px)); }';
	if ( $desktop_view_enabled ) { echo '}'; }
}
$widget_css = ob_get_clean();
// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped

/*
 * NOTE FOR WORDPRESS.ORG REVIEWERS:
 * We intentionally output this per-instance dynamic CSS block inline here using a <style> tag.
 *
 * 1. FOUC (Flash of Unstyled Content) Prevention:
 *    Because the desktop layout is CSS Grid and the mobile layout is a Swiper flex slider,
 *    registering this via wp_add_inline_style() or wp_footer causes a visible lag on mobile devices.
 *    The browser first renders the desktop grid, and only later applies the mobile styles,
 *    resulting in a jarred layout jump ("flicker"). Direct inline tag rendering forces the
 *    browser parser to evaluate the mobile overrides synchronously, ensuring a fluid mobile experience.
 *
 * 2. Real-Time Elementor Sandbox Compatibility:
 *    During active Elementor editor sessions, values (breakpoints, view settings) change constantly.
 *    Using statically enqueued dynamic stylesheets or transient caches creates stale settings issues.
 *
 * All outputs here are safely escaped, sanitized, or calculated integers.
 */
if ( '' !== $widget_css ) {
	printf(
		'<style id="%s">%s</style>',
		esc_attr( $style_id ),
		wp_kses( $widget_css, array() )
	);
}

// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

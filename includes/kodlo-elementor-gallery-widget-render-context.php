<?php
/**
 * Normalized render context for Kodlo Product Gallery for Elementor.
 *
 * Resolves widget settings, media requirements, and view-model values used by rendering files.
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

defined( 'ABSPATH' ) || exit;

$settings = $this->get_settings_for_display();
$image_size = ! empty( $settings['gallery_image_size'] ) ? $settings['gallery_image_size'] : 'medium_large';

$render_icon = static function ( $icon, string $fallback_value = '', string $fallback_library = 'fa-solid' ): string {
	if ( ! is_array( $icon ) ) { $icon = array(); }
	if ( empty( $icon['value'] ) && $fallback_value ) {
		$icon = array( 'value' => $fallback_value, 'library' => $fallback_library );
	}

	return (string) \Elementor\Icons_Manager::try_get_icon_html( $icon, array( 'aria-hidden' => 'true' ) );
};

$video_icon_html = $render_icon( $settings['video_icon'] ?? array(), 'fas fa-play-circle' );
if ( ! trim( $video_icon_html ) ) { $video_icon_html = kodlo_gallery_get_default_video_icon_html(); }
$mobile_magnifier_icon_html = $render_icon( $settings['mobile_magnifier_button_icon'] ?? array(), 'fas fa-search-plus' );
$mobile_magnifier_active_icon_html = $render_icon( $settings['mobile_magnifier_button_active_icon'] ?? array(), 'fas fa-search-minus' );

$desktop_view_enabled = 'yes' === ( $settings['enable_desktop_view'] ?? 'yes' );
$mobile_view_enabled = 'yes' === ( $settings['enable_mobile_view'] ?? 'yes' );

if ( ! $desktop_view_enabled && ! $mobile_view_enabled ) {
	$desktop_view_enabled = true;
}

$show_variations = 'yes' === ( $settings['show_variations'] ?? 'yes' );
$show_featured = 'yes' === ( $settings['show_featured_image'] ?? 'yes' );
$show_videos = 'yes' === ( $settings['show_videos'] ?? 'yes' );
$max_images = max( 0, intval( $settings['max_images'] ?? 0 ) );
$max_images_mobile = max( 0, intval( $settings['max_images_mobile'] ?? 0 ) );
$show_thumbnails = $mobile_view_enabled && 'yes' === ( $settings['show_thumbnails'] ?? 'yes' );
$show_caption = 'none' !== ( $settings['gallery_display_caption'] ?? 'none' );
$gallery_link = $settings['gallery_link'] ?? 'none';
$show_thumbnail_video_icon = $show_thumbnails
	&& $show_videos
	&& 'yes' === ( $settings['show_thumbnail_video_icon'] ?? 'yes' );

$enabled_view_limits = array();
if ( $desktop_view_enabled ) { $enabled_view_limits[] = $max_images; }
if ( $mobile_view_enabled ) { $enabled_view_limits[] = $max_images_mobile; }
$hydration_limit = in_array( 0, $enabled_view_limits, true ) ? 0 : max( $enabled_view_limits );

$gallery_options = array(
	'exclude_featured'   => ! $show_featured,
	'randomize'          => 'rand' === ( $settings['gallery_order'] ?? '' ),
	'limit'              => $hydration_limit,
	'media_requirements' => array(
		'thumbnail'      => $show_thumbnails,
		'caption'        => $show_caption,
		'attachment_url' => 'attachment' === $gallery_link,
		'video'          => $show_videos,
	),
);
$product_images = kodlo_gallery_get_product_images( $product, $image_size, $gallery_options );
$variation_galleries = $show_variations
	? kodlo_gallery_get_variation_galleries( $product, $image_size, $gallery_options )
	: array();

$desktop_images_count = $max_images > 0 ? min( count( $product_images ), $max_images ) : count( $product_images );

$show_pagination = ( $mobile_view_enabled && 'yes' === ( $settings['show_pagination'] ?? 'yes' ) ) ? 'yes' : 'no';
$autoplay_setting = $settings['autoplay_videos'] ?? 'no';
$autoplay = in_array( $autoplay_setting, array( 'no', 'desktop', 'mobile', 'yes' ), true ) ? $autoplay_setting : 'no';
$mute_videos = ( 'yes' === $autoplay || 'yes' === ( $settings['mute_videos'] ?? 'yes' ) ) ? 'yes' : 'no';
$loop_videos = 'yes' === ( $settings['loop_videos'] ?? 'no' ) ? 'yes' : 'no';
$loop_mobile = 'yes' === ( $settings['loop_mobile'] ?? 'no' ) ? 'yes' : 'no';
$desktop_columns = max( 1, min( 10, intval( $settings['desktop_columns'] ?? 2 ) ) );
$thumbnails_per_view = $settings['thumbs_per_view'] ?? '4';
$thumbnail_gap = intval( $settings['thumb_gap']['size'] ?? 10 );
$mobile_edge_bleed = 'yes' === ( $settings['enable_mobile_edge_bleed'] ?? 'no' );
$edge_bleed_navigation_alignment = $settings['navigation_edge_bleed_nav_edge'] ?? 'content';

$show_main_navigation = $mobile_view_enabled && 'yes' === ( $settings['show_main_nav'] ?? 'no' );
$main_navigation_position = $settings['main_nav_position'] ?? 'overlay';
$show_thumbnails_navigation = $show_thumbnails && 'yes' === ( $settings['show_thumbs_nav'] ?? 'no' );
$thumbnails_navigation_position = $settings['thumbs_nav_position'] ?? 'overlay';

$main_navigation_previous_icon_html = $render_icon(
	$settings['main_nav_icon_prev'] ?? array(),
	'fas fa-chevron-left'
);
$main_navigation_next_icon_html = $render_icon(
	$settings['main_nav_icon_next'] ?? array(),
	'fas fa-chevron-right'
);
$thumbnails_navigation_previous_icon_html = $render_icon(
	$settings['thumbs_nav_icon_prev'] ?? array(),
	'fas fa-chevron-left'
);
$thumbnails_navigation_next_icon_html = $render_icon(
	$settings['thumbs_nav_icon_next'] ?? array(),
	'fas fa-chevron-right'
);

$dot_position = $settings['dot_position'] ?? 'between';
$max_visible_dots = max( 2, min( 10, intval( $settings['max_visible_dots']['size'] ?? 5 ) ) );

$desktop_image_effect = $settings['desktop_image_effect'] ?? 'none';
$mobile_image_effect = $settings['mobile_image_effect'] ?? 'none';
if ( ! in_array( $desktop_image_effect, array( 'none', 'zoom', 'magnifier' ), true ) ) {
	$desktop_image_effect = 'none';
}
if ( ! in_array( $mobile_image_effect, array( 'none', 'zoom', 'magnifier' ), true ) ) {
	$mobile_image_effect = 'none';
}

$desktop_magnifier_scope = ( $settings['desktop_magnifier_scope'] ?? 'all' ) === 'first' ? 'first' : 'all';
$mobile_magnifier_scope = ( $settings['mobile_magnifier_scope'] ?? 'all' ) === 'first' ? 'first' : 'all';

$last_image_full_width = $settings['last_image_full_width'] ?? 'no';
$last_image_aspect_ratio = $settings['last_image_aspect_ratio'] ?? '16 / 9';
$last_image_object_fit = $settings['last_image_object_fit'] ?? 'cover';
$allowed_aspect_ratios = array(
	'1 / 1', '4 / 5', '5 / 6', '3 / 4', '2 / 3', '9 / 16',
	'5 / 4', '6 / 5', '4 / 3', '3 / 2', '16 / 9', '21 / 9',
);
if ( ! in_array( $last_image_full_width, array( 'no', 'last', 'first' ), true ) ) {
	$last_image_full_width = 'no';
}
if ( ! in_array( $last_image_aspect_ratio, $allowed_aspect_ratios, true ) ) {
	$last_image_aspect_ratio = '16 / 9';
}
if ( ! in_array( $last_image_object_fit, array( 'cover', 'contain', 'fill' ), true ) ) {
	$last_image_object_fit = 'cover';
}
$video_hover_animation = sanitize_html_class( (string) ( $settings['video_play_btn_hover_animation'] ?? '' ) );

$open_lightbox = $settings['open_lightbox'] ?? 'yes';
$lightbox_enabled = 'yes' === $open_lightbox;
if ( 'default' === $open_lightbox ) {
	$kits_manager = \Elementor\Plugin::$instance->kits_manager ?? null;
	$active_kit = is_object( $kits_manager ) ? $kits_manager->get_active_kit() : null;
	$lightbox_enabled = $active_kit && 'yes' === $active_kit->get_settings( 'global_image_lightbox' );
}

$mobile_breakpoint = intval( $settings['mobile_breakpoint'] ?? 768 );

$desktop_min_width = $mobile_breakpoint;
$mobile_max_width = $mobile_breakpoint - 1;
$widget_id = $this->get_id();
$style_id = 'kodlo-gallery-style-' . $widget_id;
$gallery_data = kodlo_gallery_get_compact_gallery_data( $product_images, $variation_galleries );
$media_options = array(
	'show_caption'     => $show_caption,
	'link_type'        => $gallery_link,
	'lightbox_enabled' => $lightbox_enabled,
	'lightbox_group'   => 'kodlo-gallery-' . $widget_id,
	'hover_animation'  => $video_hover_animation,
);

$optimization_enabled = 'yes' === ( $settings['image_loading_optimization'] ?? 'no' );
$desktop_priority_count = ( $optimization_enabled && $desktop_view_enabled )
	? max( 0, intval( $settings['desktop_priority_images'] ?? 1 ) )
	: 0;
$mobile_main_priority_count = ( $optimization_enabled && $mobile_view_enabled )
	? max( 0, intval( $settings['mobile_main_priority_images'] ?? 1 ) )
	: 0;
$mobile_thumbs_priority_count = ( $optimization_enabled && $show_thumbnails )
	? max( 0, intval( $settings['mobile_thumbs_priority_images'] ?? 0 ) )
	: 0;

// Defer Off-Screen Images: thumbnails beyond the visible row, and mobile slides from the first non-priority one (never
// the first image), are printed without src; kodlo-elementor-gallery-deferred-images.js restores them. Slides are
// shared with the desktop grid, so they are deferred only when the desktop view is off. Thumbnails with an automatic
// count per view have no known visible row and stay as they are. Never in the editor or in AJAX/REST renders, which
// the restore script does not see.
$defer_offscreen_images = $optimization_enabled
	&& $mobile_view_enabled
	&& 'yes' === ( $settings['defer_offscreen_images'] ?? 'no' )
	&& ! $this->is_editor_request()
	&& ! wp_doing_ajax()
	&& ! wp_is_serving_rest_request();
$defer_main_from = $desktop_view_enabled ? PHP_INT_MAX : max( 1, $mobile_main_priority_count );
$defer_thumbs_from = is_numeric( $thumbnails_per_view )
	? max( $mobile_thumbs_priority_count, (int) ceil( (float) $thumbnails_per_view ) )
	: PHP_INT_MAX;

$show_sale_flash = 'yes' === ( $settings['sale_flash'] ?? 'no' ) && $product->is_on_sale();
$sale_flash_url = $settings['sale_flash_url'] ?? array();
$sale_flash_has_link = ! empty( $sale_flash_url['url'] );
$sale_flash_position = $settings['sale_flash_position'] ?? 'top_left';
$allowed_sale_positions = array(
	'top_left', 'top_center', 'top_right',
	'center_left', 'center_center', 'center_right',
	'bottom_left', 'bottom_center', 'bottom_right', 'custom',
);

if ( ! in_array( $sale_flash_position, $allowed_sale_positions, true ) ) {
	$sale_flash_position = 'top_left';
}

if ( $sale_flash_has_link ) {
	$this->add_link_attributes( 'sale_flash_link', $sale_flash_url );
}

// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

<?php
/**
 * Frontend assets for Kodlo Product Gallery for Elementor.
 *
 * Registers gallery scripts and styles while preserving conditional widget dependencies.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers frontend gallery JavaScript assets.
 */
function kodlo_gallery_register_frontend_scripts() {
	wp_register_script(
		'kodlo-elementor-gallery-magnifier-js',
		plugins_url( 'assets/js/kodlo-elementor-gallery-magnifier.js', KODLO_ELEMENTOR_GALLERY_FILE ),
		array(),
		KODLO_ELEMENTOR_GALLERY_VERSION,
		true
	);

	wp_localize_script(
		'kodlo-elementor-gallery-magnifier-js',
		'kodloGalleryMagnifierL10n',
		array(
			'enable'  => __( 'Enable image magnifier', 'kodlo-product-gallery-for-elementor' ),
			'disable' => __( 'Disable image magnifier', 'kodlo-product-gallery-for-elementor' ),
		)
	);

	wp_register_script(
		'kodlo-elementor-gallery-edge-bleed-js',
		plugins_url( 'assets/js/kodlo-elementor-gallery-edge-bleed.js', KODLO_ELEMENTOR_GALLERY_FILE ),
		array(),
		KODLO_ELEMENTOR_GALLERY_VERSION,
		true
	);

	wp_register_script(
		'kodlo-elementor-gallery-deferred-images-js',
		plugins_url( 'assets/js/kodlo-elementor-gallery-deferred-images.js', KODLO_ELEMENTOR_GALLERY_FILE ),
		array(),
		KODLO_ELEMENTOR_GALLERY_VERSION,
		true
	);

	wp_register_script(
		'kodlo-elementor-gallery-frontend-data-js',
		plugins_url( 'assets/js/kodlo-elementor-gallery-frontend-data.js', KODLO_ELEMENTOR_GALLERY_FILE ),
		array(),
		KODLO_ELEMENTOR_GALLERY_VERSION,
		true
	);

	wp_register_script(
		'kodlo-elementor-gallery-frontend-renderer-js',
		plugins_url( 'assets/js/kodlo-elementor-gallery-frontend-renderer.js', KODLO_ELEMENTOR_GALLERY_FILE ),
		array( 'kodlo-elementor-gallery-frontend-data-js' ),
		KODLO_ELEMENTOR_GALLERY_VERSION,
		true
	);

	wp_register_script(
		'kodlo-elementor-gallery-frontend-video-js',
		plugins_url( 'assets/js/kodlo-elementor-gallery-frontend-video.js', KODLO_ELEMENTOR_GALLERY_FILE ),
		array( 'kodlo-elementor-gallery-frontend-data-js' ),
		KODLO_ELEMENTOR_GALLERY_VERSION,
		true
	);

	wp_register_script(
		'kodlo-elementor-gallery-frontend-variations-js',
		plugins_url( 'assets/js/kodlo-elementor-gallery-frontend-variations.js', KODLO_ELEMENTOR_GALLERY_FILE ),
		array( 'jquery', 'kodlo-elementor-gallery-frontend-data-js' ),
		KODLO_ELEMENTOR_GALLERY_VERSION,
		true
	);

	wp_register_script(
		'kodlo-elementor-gallery-frontend-js',
		plugins_url( 'assets/js/kodlo-elementor-gallery-frontend.js', KODLO_ELEMENTOR_GALLERY_FILE ),
		array(
			'jquery',
			'kodlo-elementor-gallery-frontend-data-js',
			'kodlo-elementor-gallery-frontend-renderer-js',
			'kodlo-elementor-gallery-frontend-video-js',
			'kodlo-elementor-gallery-frontend-variations-js',
		),
		KODLO_ELEMENTOR_GALLERY_VERSION,
		true
	);

	wp_localize_script(
		'kodlo-elementor-gallery-frontend-data-js',
		'kodloGalleryFrontendL10n',
		array(
			'playVideo'   => __( 'Play video', 'kodlo-product-gallery-for-elementor' ),
			'videoPlayer' => __( 'Product video player', 'kodlo-product-gallery-for-elementor' ),
			'previous'    => __( 'Previous', 'kodlo-product-gallery-for-elementor' ),
			'next'        => __( 'Next', 'kodlo-product-gallery-for-elementor' ),
			'gallery'     => __( 'Gallery', 'kodlo-product-gallery-for-elementor' ),
			'thumbnail'   => __( 'Thumbnail', 'kodlo-product-gallery-for-elementor' ),
			/* translators: %d: image index */
			'viewImage'   => __( 'View image %d', 'kodlo-product-gallery-for-elementor' ),
			/* translators: %d: video index */
			'viewVideo'   => __( 'View video %d', 'kodlo-product-gallery-for-elementor' ),
		)
	);
}

add_action( 'elementor/frontend/after_register_scripts', 'kodlo_gallery_register_frontend_scripts', 10, 0 );

/**
 * Registers frontend gallery stylesheet assets.
 */
function kodlo_gallery_register_frontend_styles() {
	// Refresh the CSS URL after an in-place fix without changing the plugin version.
	$magnifier_css_file = plugin_dir_path( KODLO_ELEMENTOR_GALLERY_FILE ) . 'assets/css/kodlo-elementor-gallery-magnifier.css';
	$magnifier_css_version = is_readable( $magnifier_css_file )
		? KODLO_ELEMENTOR_GALLERY_VERSION . '.' . filemtime( $magnifier_css_file )
		: KODLO_ELEMENTOR_GALLERY_VERSION;

	wp_register_style(
		'kodlo-elementor-gallery-magnifier-css',
		plugins_url( 'assets/css/kodlo-elementor-gallery-magnifier.css', KODLO_ELEMENTOR_GALLERY_FILE ),
		array(),
		$magnifier_css_version
	);

	wp_register_style(
		'kodlo-elementor-gallery-frontend-css',
		plugins_url( 'assets/css/kodlo-elementor-gallery-frontend.css', KODLO_ELEMENTOR_GALLERY_FILE ),
		array(),
		KODLO_ELEMENTOR_GALLERY_VERSION
	);

	wp_register_style(
		'kodlo-elementor-gallery-edge-bleed-css',
		plugins_url( 'assets/css/kodlo-elementor-gallery-edge-bleed.css', KODLO_ELEMENTOR_GALLERY_FILE ),
		array( 'kodlo-elementor-gallery-frontend-css' ),
		KODLO_ELEMENTOR_GALLERY_VERSION
	);
}

add_action( 'elementor/frontend/after_register_styles', 'kodlo_gallery_register_frontend_styles', 10, 0 );

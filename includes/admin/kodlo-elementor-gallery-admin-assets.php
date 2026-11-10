<?php
/**
 * WordPress admin assets for Kodlo Product Gallery for Elementor.
 *
 * Loads variation-gallery and attachment-field assets only in their relevant admin contexts.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueues admin styles and scripts for variable product galleries.
 *
 * @param string $hook The current admin page hook.
 */
function kodlo_gallery_enqueue_admin_assets( $hook ) {
	global $post;

	$is_product_edit_page = in_array( $hook, array( 'post.php', 'post-new.php' ), true )
		&& is_object( $post )
		&& 'product' === $post->post_type;
	if ( ! $is_product_edit_page ) { return; }

	wp_enqueue_media();
	wp_enqueue_script(
		'kodlo-elementor-gallery-admin-js',
		plugins_url( 'assets/js/kodlo-elementor-gallery-variation-gallery-admin.js', KODLO_ELEMENTOR_GALLERY_FILE ),
		array( 'media-views' ),
		KODLO_ELEMENTOR_GALLERY_VERSION,
		true
	);
	wp_localize_script(
		'kodlo-elementor-gallery-admin-js',
		'kodloGalleryAdminL10n',
		array(
			'selectImages'     => __( 'Select images for Variation Gallery', 'kodlo-product-gallery-for-elementor' ),
			'removeImage'      => __( 'Remove image', 'kodlo-product-gallery-for-elementor' ),
			'moveImageBack'    => __( 'Move image backward', 'kodlo-product-gallery-for-elementor' ),
			'moveImageForward' => __( 'Move image forward', 'kodlo-product-gallery-for-elementor' ),
			/* translators: %d: attachment ID. */
			'galleryImage'     => __( 'Gallery image %d', 'kodlo-product-gallery-for-elementor' ),
			'confirmRemoveAll' => __( 'Are you sure you want to remove all variation gallery images?', 'kodlo-product-gallery-for-elementor' ),
			'editGallery'      => __( 'Edit Gallery', 'kodlo-product-gallery-for-elementor' ),
		)
	);
	wp_enqueue_style(
		'kodlo-elementor-gallery-admin-css',
		plugins_url( 'assets/css/kodlo-elementor-gallery-variation-gallery-admin.css', KODLO_ELEMENTOR_GALLERY_FILE ),
		array(),
		KODLO_ELEMENTOR_GALLERY_VERSION
	);
}

add_action( 'admin_enqueue_scripts', 'kodlo_gallery_enqueue_admin_assets' );

/**
 * Enqueues attachment assets on Media Library, attachment edit, and active media-view screens.
 *
 * @param string $hook The current admin page hook.
 */
function kodlo_gallery_enqueue_admin_attachment_assets( $hook ) {
	global $post;

	$is_media_screen = in_array( $hook, array( 'upload.php', 'media-new.php' ), true );
	$is_attachment_edit = 'post.php' === $hook
		&& is_object( $post )
		&& 'attachment' === $post->post_type;
	if ( ! $is_media_screen && ! $is_attachment_edit && ! wp_script_is( 'media-views', 'enqueued' ) ) { return; }

	wp_enqueue_script(
		'kodlo-elementor-gallery-admin-attachment-js',
		plugins_url( 'assets/js/kodlo-elementor-gallery-admin-attachment.js', KODLO_ELEMENTOR_GALLERY_FILE ),
		array( 'jquery', 'media-views' ),
		KODLO_ELEMENTOR_GALLERY_VERSION,
		true
	);
	wp_localize_script(
		'kodlo-elementor-gallery-admin-attachment-js',
		'kodloGalleryAttachmentL10n',
		array(
			'videoUrl'  => __( 'Video URL', 'kodlo-product-gallery-for-elementor' ),
			'videoHelp' => __(
				'YouTube, Vimeo, or direct MP4 URL. This image is used as the video thumbnail and poster.',
				'kodlo-product-gallery-for-elementor'
			),
		)
	);
	wp_enqueue_style(
		'kodlo-elementor-gallery-admin-attachment-css',
		plugins_url( 'assets/css/kodlo-elementor-gallery-admin-attachment.css', KODLO_ELEMENTOR_GALLERY_FILE ),
		array(),
		KODLO_ELEMENTOR_GALLERY_VERSION
	);
}

add_action( 'admin_enqueue_scripts', 'kodlo_gallery_enqueue_admin_attachment_assets', 100 );

<?php
/**
 * Media attachment fields for Kodlo Product Gallery for Elementor.
 *
 * Adds and saves the Video URL field in WordPress Media Library.
 */

defined( 'ABSPATH' ) || exit;

// Adds the Video URL field to media library attachment details.
add_filter( 'attachment_fields_to_edit', 'kodlo_gallery_add_attachment_fields', 1, 2 );

// Saves the Video URL attachment metadata.
add_filter( 'attachment_fields_to_save', 'kodlo_gallery_save_attachment_fields', 10, 2 );

// Exposes the video URL to the WordPress media modal without parsing compat HTML.
add_filter( 'wp_prepare_attachment_for_js', 'kodlo_gallery_prepare_attachment_for_js', 10, 2 );

/**
 * Adds the custom Video URL field to the media attachment edit form.
 *
 * @param array   $form_fields Form fields.
 * @param WP_Post $post        Post object.
 * @return array Modified form fields.
 */
function kodlo_gallery_add_attachment_fields( $form_fields, $post ) {
	if ( ! $post || ! kodlo_gallery_is_supported_image_attachment( $post ) ) {
		return $form_fields;
	}

	$form_fields['kodlo_gallery_video_url'] = array(
		'label' => esc_html__( 'Video URL', 'kodlo-product-gallery-for-elementor' ),
		'input' => 'text',
		'value' => kodlo_gallery_get_compatible_video_url( $post->ID ) ?: '',
		'helps' => esc_html__( 'YouTube, Vimeo, or direct MP4 URL. This image is used as the video thumbnail and poster.', 'kodlo-product-gallery-for-elementor' ),
	);

	return $form_fields;
}

/**
 * Saves the custom Video URL field from the media attachment edit form.
 *
 * @param array $post       Attachment post data.
 * @param array $attachment Attachment field data.
 * @return array Original post array.
 */
function kodlo_gallery_save_attachment_fields( $post, $attachment ) {
	$attachment_id = is_array( $post ) && isset( $post['ID'] ) ? absint( $post['ID'] ) : 0;

	if ( ! $attachment_id || ! current_user_can( 'edit_post', $attachment_id ) ) {
		return $post;
	}

	if ( ! kodlo_gallery_is_supported_image_attachment( $attachment_id ) ) {
		return $post;
	}

	if ( ! is_array( $attachment ) || ! array_key_exists( 'kodlo_gallery_video_url', $attachment ) ) {
		return $post;
	}

	$submitted_video_url = $attachment['kodlo_gallery_video_url'];
	if ( ! is_scalar( $submitted_video_url ) ) {
		return $post;
	}

	$video_url = esc_url_raw( wp_unslash( (string) $submitted_video_url ) );
	kodlo_gallery_update_locked_post_meta( $attachment_id, KODLO_GALLERY_VIDEO_META_KEY, $video_url );

	return $post;
}

/**
 * Prepares the custom Video URL field for JSON response in media modal.
 *
 * @param array   $response   Attachment response data.
 * @param WP_Post $attachment Attachment post object.
 * @return array Modified response.
 */
function kodlo_gallery_prepare_attachment_for_js( $response, $attachment ) {
	if ( kodlo_gallery_is_supported_image_attachment( $attachment ) ) {
		$response['kodlo_gallery_video_url'] = (string) kodlo_gallery_get_compatible_video_url( $attachment->ID );
	}
	return $response;
}

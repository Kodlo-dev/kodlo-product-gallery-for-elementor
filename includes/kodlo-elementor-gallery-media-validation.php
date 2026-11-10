<?php
/**
 * Shared media validation for Kodlo Product Gallery for Elementor.
 *
 * Normalizes gallery attachment IDs and validates supported image attachments
 * for both frontend reads and administrator writes.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Checks whether an attachment uses a supported image MIME type.
 *
 * @param int|WP_Post $attachment Attachment ID or post object.
 * @return bool True when the attachment is a supported image.
 */
function kodlo_gallery_is_supported_image_attachment( $attachment ) {
	$attachment_id   = $attachment instanceof WP_Post ? absint( $attachment->ID ) : absint( $attachment );
	$attachment_post = $attachment instanceof WP_Post ? $attachment : get_post( $attachment_id );
	if ( ! $attachment_post || 'attachment' !== $attachment_post->post_type ) {
		return false;
	}

	static $image_mime_types = array(
		'image/jpeg', 'image/pjpeg', 'image/png', 'image/apng',
		'image/gif', 'image/webp', 'image/avif',
		'image/bmp', 'image/x-ms-bmp', 'image/tiff', 'image/x-icon',
		'image/vnd.microsoft.icon', 'image/heic', 'image/heif',
		'image/jp2', 'image/jpx', 'image/jxl', 'image/svg+xml',
	);

	return in_array( strtolower( (string) $attachment_post->post_mime_type ), $image_mime_types, true );
}

/**
 * Normalizes a comma-separated attachment ID list without querying attachments.
 *
 * @param mixed $raw_value Raw comma-separated string or attachment ID array.
 * @return int[] Normalized attachment IDs.
 */
function kodlo_gallery_normalize_image_ids( $raw_value ) {
	if ( is_array( $raw_value ) ) {
		$raw_ids = $raw_value;
	} elseif ( is_scalar( $raw_value ) ) {
		$raw_ids = explode( ',', (string) $raw_value );
	} else {
		$raw_ids = array();
	}

	$attachment_ids = array_map( 'absint', $raw_ids );
	return array_values( array_unique( array_filter( $attachment_ids ) ) );
}

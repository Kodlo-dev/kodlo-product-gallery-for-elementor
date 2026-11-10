<?php
/**
 * Side-effect-free WooGallery reads for Kodlo Product Gallery for Elementor.
 *
 * Resolves supported legacy metadata without writing, scheduling migration, or
 * registering administrator lifecycle hooks on public requests.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Reports whether legacy WooGallery read and migration compatibility is enabled.
 *
 * @return bool True when compatibility is enabled.
 */
function kodlo_gallery_is_woogallery_compatibility_enabled() {
	/**
	 * Filters whether legacy WooGallery metadata compatibility is enabled.
	 *
	 * @since 1.3.0
	 * @param bool $enabled Whether compatibility is enabled.
	 */
	return (bool) apply_filters( 'kodlo_gallery_enable_woogallery_compatibility', true );
}

/**
 * Returns the compatible video URL for an attachment without writing metadata.
 *
 * @param int $attachment_id Attachment ID.
 * @return string Video URL.
 */
function kodlo_gallery_get_compatible_video_url( $attachment_id ) {
	$attachment_id = absint( $attachment_id );
	if ( ! $attachment_id ) {
		return '';
	}

	$meta_exists = metadata_exists( 'post', $attachment_id, KODLO_GALLERY_VIDEO_META_KEY );
	if ( ! $meta_exists && kodlo_gallery_is_woogallery_compatibility_enabled() ) {
		$legacy_video_url = get_post_meta( $attachment_id, 'wcgs_video', true );
		return is_string( $legacy_video_url ) ? esc_url_raw( trim( $legacy_video_url ) ) : '';
	}

	return (string) get_post_meta( $attachment_id, KODLO_GALLERY_VIDEO_META_KEY, true );
}

/**
 * Returns compatible variation gallery image IDs without writing metadata.
 *
 * @param int $variation_id Variation post ID.
 * @return int[] Supported image attachment IDs.
 */
function kodlo_gallery_get_compatible_variation_image_ids( $variation_id ) {
	$candidate_ids = kodlo_gallery_get_compatible_variation_image_candidates( $variation_id );
	$candidate_ids = kodlo_gallery_prime_attachment_caches( $candidate_ids );
	return array_values( array_filter( $candidate_ids, 'kodlo_gallery_is_supported_image_attachment' ) );
}

/**
 * Returns compatible variation image candidates without attachment validation.
 *
 * The caller may batch-prime multiple variations before validating candidates.
 *
 * @param int $variation_id Variation post ID.
 * @return int[] Normalized candidate attachment IDs.
 */
function kodlo_gallery_get_compatible_variation_image_candidates( $variation_id ) {
	$variation_id = absint( $variation_id );
	if ( ! $variation_id ) {
		return array();
	}

	$meta_exists = metadata_exists( 'post', $variation_id, KODLO_GALLERY_VARIATION_META_KEY );
	if ( ! $meta_exists && kodlo_gallery_is_woogallery_compatibility_enabled() ) {
		return kodlo_gallery_normalize_legacy_variation_ids( get_post_meta( $variation_id, 'woo_gallery_slider', true ) );
	}

	$variation_images = get_post_meta( $variation_id, KODLO_GALLERY_VARIATION_META_KEY, true );
	return kodlo_gallery_normalize_image_ids( $variation_images );
}

/**
 * Normalizes legacy WooGallery variation metadata without querying attachments.
 *
 * @param mixed $legacy_value Legacy metadata value.
 * @return int[] Normalized candidate attachment IDs.
 */
function kodlo_gallery_normalize_legacy_variation_ids( $legacy_value ) {
	$raw_ids = array();
	if ( is_array( $legacy_value ) ) {
		$raw_ids = $legacy_value;
	} elseif ( is_string( $legacy_value ) ) {
		$legacy_value = trim( $legacy_value );
		if ( 0 === strpos( $legacy_value, '[' ) && ']' === substr( $legacy_value, -1 ) ) {
			$decoded_ids = json_decode( $legacy_value, true );
			$raw_ids     = is_array( $decoded_ids ) ? $decoded_ids : explode( ',', $legacy_value );
		} else {
			$raw_ids = explode( ',', $legacy_value );
		}
	}

	$candidate_ids = array_map(
		static function ( $id ) {
			return absint( is_scalar( $id ) ? trim( (string) $id ) : 0 );
		},
		$raw_ids
	);
	return array_values( array_unique( array_filter( $candidate_ids ) ) );
}

/**
 * Decodes a database metadata value without allowing object instantiation.
 *
 * @param mixed $raw_value Raw database value.
 * @return mixed Decoded scalar or array value, or the original value.
 */
function kodlo_gallery_decode_woogallery_database_value( $raw_value ) {
	if ( ! is_string( $raw_value )
		|| strlen( $raw_value ) > 1048576
		|| ! is_serialized( $raw_value ) ) {
		return $raw_value;
	}

	return unserialize( $raw_value, array( 'allowed_classes' => false ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize
}

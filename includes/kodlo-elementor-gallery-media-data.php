<?php
/**
 * Product and variation media data for Kodlo Product Gallery for Elementor.
 *
 * Retrieves, limits, formats, and caches product and variation media data.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Primes attachment post and metadata caches before per-item validation.
 *
 * @param int[] $attachment_ids Candidate attachment IDs.
 * @return int[] Normalized IDs.
 */
function kodlo_gallery_prime_attachment_caches( $attachment_ids ) {
	$attachment_ids = array_values( array_unique( array_filter( array_map( 'absint', (array) $attachment_ids ) ) ) );
	if ( empty( $attachment_ids ) ) { return array(); }

	_prime_post_caches( $attachment_ids, false, true );

	return $attachment_ids;
}

/**
 * Normalizes optional media fields requested by a gallery view.
 *
 * Omitting the requirements preserves the complete historical payload.
 *
 * @param array $media_requirements Optional media field requirements.
 * @return array Normalized requirements.
 */
function kodlo_gallery_normalize_media_requirements( $media_requirements = array() ) {
	if ( ! is_array( $media_requirements ) ) { $media_requirements = array(); }
	$normalized_requirements = array(
		'thumbnail'      => true,
		'caption'        => true,
		'attachment_url' => true,
		'video'          => true,
	);

	foreach ( array_keys( $normalized_requirements ) as $field_name ) {
		if ( array_key_exists( $field_name, $media_requirements ) ) {
			$normalized_requirements[ $field_name ] = (bool) $media_requirements[ $field_name ];
		}
	}

	return $normalized_requirements;
}

/**
 * Returns base image metadata and the optional fields requested by the current view.
 *
 * @param int          $image_id          Attachment image ID.
 * @param string|array $image_size        Registered image size or width/height array.
 * @param array        $media_requirements Optional media field requirements.
 * @return array Image details.
 */
function kodlo_gallery_get_image_data( $image_id, $image_size = 'medium_large', $media_requirements = array() ) {
	static $image_data_cache = array();

	$image_id = absint( $image_id );
	$media_requirements = kodlo_gallery_normalize_media_requirements( $media_requirements );
	$thumbnail_auto_sizes_enabled = $media_requirements['thumbnail']
		&& function_exists( 'wp_sizes_attribute_includes_valid_auto' )
		&& apply_filters( 'wp_img_tag_add_auto_sizes', true ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WordPress core filter, read with its core default.
	$requirements_cache_key = implode( '', array_map( 'intval', array_values( $media_requirements ) ) );
	$image_size_cache_key = is_array( $image_size ) ? wp_json_encode( $image_size ) : (string) $image_size;
	$cache_key = $image_id . ':' . $image_size_cache_key . ':' . $requirements_cache_key . ':' . (int) $thumbnail_auto_sizes_enabled;

	if ( isset( $image_data_cache[ $cache_key ] ) ) { return $image_data_cache[ $cache_key ]; }

	$display_src = wp_get_attachment_image_src( $image_id, $image_size );
	$full_src = wp_get_attachment_image_src( $image_id, 'full' );
	$display_url = $display_src ? $display_src[0] : wp_get_attachment_url( $image_id );

	$image_data = array(
		'id'     => $image_id,
		'medium' => $display_url,
		'width'  => $display_src ? $display_src[1] : '',
		'height' => $display_src ? $display_src[2] : '',
		'srcset' => (string) wp_get_attachment_image_srcset( $image_id, $image_size ),
		'sizes'  => (string) wp_get_attachment_image_sizes( $image_id, $image_size ),
		'full'   => $full_src ? $full_src[0] : $display_url,
	);

	if ( $media_requirements['thumbnail'] ) {
		$thumbnail_src = wp_get_attachment_image_src( $image_id, 'woocommerce_thumbnail' );
		$image_data['thumb'] = $thumbnail_src ? $thumbnail_src[0] : $display_url;
		$image_data['thumb_width'] = $thumbnail_src ? $thumbnail_src[1] : '';
		$image_data['thumb_height'] = $thumbnail_src ? $thumbnail_src[2] : '';

		// Auto-sizes lets native lazy thumbnails follow any widget's actual layout. Browsers without auto-sizes use the
		// fallback width, which matches a gallery thumbnail rather than the full woocommerce_thumbnail size.
		if ( $thumbnail_auto_sizes_enabled ) {
			$thumbnail_srcset = wp_get_attachment_image_srcset( $image_id, 'woocommerce_thumbnail' );
			if ( $thumbnail_srcset ) {
				$image_data['thumb_srcset'] = $thumbnail_srcset;
				$image_data['thumb_sizes'] = 'auto, 150px';
			}
		}
	}

	$image_data['alt'] = get_post_meta( $image_id, '_wp_attachment_image_alt', true ) ?: get_the_title( $image_id );
	if ( $media_requirements['caption'] ) { $image_data['caption'] = (string) wp_get_attachment_caption( $image_id ); }
	if ( $media_requirements['attachment_url'] ) { $image_data['attachment_url'] = (string) get_attachment_link( $image_id ); }
	if ( $media_requirements['video'] ) { $image_data['video'] = (string) kodlo_gallery_get_compatible_video_url( $image_id ); }

	$image_data_cache[ $cache_key ] = $image_data;

	return $image_data_cache[ $cache_key ];
}

/**
 * Returns all gallery images associated with the product.
 *
 * @param WC_Product   $product        WooCommerce product.
 * @param string|array $image_size     Image size name.
 * @param array        $gallery_options Optional ordering, limiting, and media field options.
 * @return array Array of image data structures.
 */
function kodlo_gallery_get_product_images( $product, $image_size = 'medium_large', $gallery_options = array() ) {
	/**
	 * Filters the attachment IDs of the default gallery, the one shown before any variation is chosen.
	 *
	 * The first ID acts as the featured image (Exclude Featured Image option). Variation galleries, structured data
	 * and other uses of the product images are not affected.
	 *
	 * @param array<int|string> $default_image_ids Product image ID followed by the product gallery IDs.
	 * @param WC_Product        $product           Product whose gallery is rendered.
	 */
	$default_image_ids = apply_filters(
		'kodlo_gallery_default_image_ids',
		array_merge( array( $product->get_image_id() ), $product->get_gallery_image_ids() ),
		$product
	);
	$default_image_ids = is_array( $default_image_ids ) ? array_values( array_filter( $default_image_ids, 'is_scalar' ) ) : array();
	$featured_image_id = absint( $default_image_ids[0] ?? 0 );
	$candidate_image_ids = kodlo_gallery_prime_attachment_caches( $default_image_ids );
	$supported_image_ids = array_values( array_filter( $candidate_image_ids, 'kodlo_gallery_is_supported_image_attachment' ) );
	$media_requirements = $gallery_options['media_requirements'] ?? array();

	if ( empty( $supported_image_ids ) ) {
		$placeholder = wc_placeholder_img_src();
		$placeholder_data = array(
			'id'     => 0,
			'medium' => $placeholder,
			'width'  => '',
			'height' => '',
			'srcset' => '',
			'sizes'  => '',
			'full'   => $placeholder,
		);
		$media_requirements = kodlo_gallery_normalize_media_requirements( $media_requirements );
		if ( $media_requirements['thumbnail'] ) {
			$placeholder_data['thumb'] = $placeholder;
			$placeholder_data['thumb_width'] = '';
			$placeholder_data['thumb_height'] = '';
		}
		$placeholder_data['alt'] = '';
		if ( $media_requirements['caption'] ) { $placeholder_data['caption'] = ''; }
		if ( $media_requirements['attachment_url'] ) { $placeholder_data['attachment_url'] = ''; }
		if ( $media_requirements['video'] ) { $placeholder_data['video'] = ''; }

		return array( $placeholder_data );
	}

	if ( ! empty( $gallery_options['exclude_featured'] ) && $featured_image_id === (int) $supported_image_ids[0] ) {
		array_shift( $supported_image_ids );
	}
	if ( ! empty( $gallery_options['randomize'] ) ) { shuffle( $supported_image_ids ); }

	$image_limit = max( 0, (int) ( $gallery_options['limit'] ?? 0 ) );
	if ( $image_limit > 0 ) { $supported_image_ids = array_slice( $supported_image_ids, 0, $image_limit ); }

	return array_map(
		static function ( $image_id ) use ( $image_size, $media_requirements ) {
			return kodlo_gallery_get_image_data( $image_id, $image_size, $media_requirements );
		},
		$supported_image_ids
	);
}

/**
 * Returns the custom galleries configured for each product variation.
 *
 * @param WC_Product   $product        WooCommerce product.
 * @param string|array $image_size     Image size name.
 * @param array        $gallery_options Optional ordering, limiting, and media field options.
 * @return array Variation gallery images structured array.
 */
function kodlo_gallery_get_variation_galleries( $product, $image_size = 'medium_large', $gallery_options = array() ) {
	if ( ! $product->is_type( 'variable' ) ) { return array(); }

	$variation_ids = array_values( array_unique( array_filter( array_map( 'absint', $product->get_children() ) ) ) );
	if ( ! empty( $variation_ids ) ) { update_meta_cache( 'post', $variation_ids ); }

	$variation_candidates = array();
	$candidate_attachment_ids = array();
	$product_gallery_ids = null;

	foreach ( $variation_ids as $variation_id ) {
		$variation_thumb_id = get_post_thumbnail_id( $variation_id );
		$custom_gallery_ids = kodlo_gallery_get_compatible_variation_image_candidates( $variation_id );

		if ( empty( $custom_gallery_ids ) && ! $variation_thumb_id ) {
			continue;
		}

		// A variation with its own image but no variation gallery would otherwise show that single image only; it keeps
		// the product gallery behind its image instead.
		if ( empty( $custom_gallery_ids )
			&& apply_filters( 'kodlo_gallery_variation_fallback_to_product_gallery', true, $variation_id, $product ) ) {
			if ( null === $product_gallery_ids ) {
				$product_gallery_ids = array_values( array_filter( array_map( 'absint', $product->get_gallery_image_ids() ) ) );
			}
			$custom_gallery_ids = array_values( array_diff( $product_gallery_ids, array( (int) $variation_thumb_id ) ) );
		}

		$candidate_image_ids = array( $variation_thumb_id );
		$candidate_attachment_ids[] = $variation_thumb_id;
		foreach ( $custom_gallery_ids as $custom_gallery_id ) {
			$candidate_image_ids[] = $custom_gallery_id;
			$candidate_attachment_ids[] = $custom_gallery_id;
		}

		$variation_candidates[] = array(
			'id'                  => $variation_id,
			'candidate_image_ids' => $candidate_image_ids,
		);
	}

	kodlo_gallery_prime_attachment_caches( $candidate_attachment_ids );

	$variation_galleries = array();
	$featured_image_id = absint( $product->get_image_id() );
	$image_limit = max( 0, (int) ( $gallery_options['limit'] ?? 0 ) );
	$media_requirements = $gallery_options['media_requirements'] ?? array();

	foreach ( $variation_candidates as $variation_candidate ) {
		$valid_image_ids = array_values(
			array_unique(
				array_filter( $variation_candidate['candidate_image_ids'], 'kodlo_gallery_is_supported_image_attachment' )
			)
		);
		if ( empty( $valid_image_ids ) ) { continue; }
		$exclude_featured_image = ! empty( $gallery_options['exclude_featured'] )
			&& $featured_image_id === (int) $valid_image_ids[0];
		if ( $exclude_featured_image ) {
			array_shift( $valid_image_ids );
		}
		if ( ! empty( $gallery_options['randomize'] ) ) { shuffle( $valid_image_ids ); }
		if ( $image_limit > 0 ) { $valid_image_ids = array_slice( $valid_image_ids, 0, $image_limit ); }

		$variation_galleries[] = array(
			'id'     => $variation_candidate['id'],
			'images' => array_map(
				static function ( $image_id ) use ( $image_size, $media_requirements ) {
					return kodlo_gallery_get_image_data( $image_id, $image_size, $media_requirements );
				},
				$valid_image_ids
			),
		);
	}

	return $variation_galleries;
}

/**
 * Deduplicates media payloads while preserving variation order.
 *
 * @param array $default_images      Default product images.
 * @param array $variation_galleries Variation gallery structures.
 * @return array Compact gallery payload.
 */
function kodlo_gallery_get_compact_gallery_data( $default_images, $variation_galleries ) {
	$media_dictionary = array();
	$store_media_item = static function ( $image_data ) use ( &$media_dictionary ) {
		$attachment_id = isset( $image_data['id'] ) ? (int) $image_data['id'] : 0;
		$media_key = (string) $attachment_id;
		if ( 0 === $attachment_id ) {
			$media_key = 'placeholder-' . md5( (string) ( $image_data['medium'] ?? '' ) );
		}
		$media_dictionary[ $media_key ] = $image_data;
		return $media_key;
	};

	$default_media_keys = array_map( $store_media_item, $default_images );
	$variation_payloads = array();
	foreach ( $variation_galleries as $variation_gallery ) {
		$variation_payloads[] = array(
			'id'       => (int) $variation_gallery['id'],
			'imageIds' => array_map( $store_media_item, $variation_gallery['images'] ),
		);
	}

	return array(
		'media'           => $media_dictionary,
		'defaultImageIds' => $default_media_keys,
		'variations'      => $variation_payloads,
	);
}

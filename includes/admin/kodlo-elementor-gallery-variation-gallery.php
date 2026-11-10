<?php
/**
 * WooCommerce variation fields for Kodlo Product Gallery for Elementor.
 *
 * Renders and saves custom variation galleries on WooCommerce product variation attributes settings.
 */

defined( 'ABSPATH' ) || exit;

// Renders the image gallery fields on the WooCommerce product variation edit panel.
add_action( 'woocommerce_product_after_variable_attributes', 'kodlo_gallery_render_variation_gallery_field', 10, 3 );

// Saves the custom gallery image IDs for a product variation.
add_action( 'woocommerce_save_product_variation', 'kodlo_gallery_save_variation_gallery', 10, 2 );

/**
 * Parses an attachment ID list and returns supported image attachments.
 *
 * @param mixed $raw_value Raw comma-separated string or attachment ID array.
 * @return int[] Valid image attachment IDs.
 */
function kodlo_gallery_parse_image_ids( $raw_value ) {
	$ids = kodlo_gallery_normalize_image_ids( $raw_value );
	$ids = kodlo_gallery_prime_attachment_caches( $ids );
	return array_values( array_filter( $ids, 'kodlo_gallery_is_supported_image_attachment' ) );
}

/**
 * Returns an accessible label for a variation gallery thumbnail.
 *
 * @param int $attachment_id Attachment ID.
 * @return string Thumbnail alternative text.
 */
function kodlo_gallery_get_admin_thumbnail_alt( $attachment_id ) {
	$alternative_text = trim( (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) );
	if ( '' !== $alternative_text ) {
		return $alternative_text;
	}

	$attachment_title = trim( (string) get_the_title( $attachment_id ) );
	if ( '' !== $attachment_title ) {
		return $attachment_title;
	}

	return sprintf(
		/* translators: %d: attachment ID. */
		__( 'Gallery image %d', 'kodlo-product-gallery-for-elementor' ),
		$attachment_id
	);
}

/**
 * Renders the variation gallery field UI in WooCommerce admin variation panels.
 *
 * @param int     $loop           Loop index.
 * @param array   $variation_data Variation data.
 * @param WP_Post $variation      Variation post object.
 */
function kodlo_gallery_render_variation_gallery_field( $loop, $variation_data, $variation ) {
	$image_ids = kodlo_gallery_get_compatible_variation_image_ids( $variation->ID );
	$gallery_value = implode( ',', $image_ids );
	?>
	<div class="kodlo-variation-gallery form-row form-row-full">
		<h4><?php esc_html_e( 'Variation Image Gallery', 'kodlo-product-gallery-for-elementor' ); ?></h4>

		<div class="kodlo-variation-gallery-items"<?php if ( empty( $image_ids ) ) : ?> style="display: none;"<?php endif; ?>>
			<?php foreach ( $image_ids as $image_id ) : ?>
				<?php
				$thumb = wp_get_attachment_image_src( $image_id, 'thumbnail' );
				if ( ! $thumb ) {
					continue;
				}
				$video_url = kodlo_gallery_get_compatible_video_url( $image_id );
				$video_class = $video_url ? ' kodlo-has-video' : '';
				$thumbnail_alt = kodlo_gallery_get_admin_thumbnail_alt( $image_id );
				?>
				<div class="kodlo-variation-gallery-image<?php echo esc_attr( $video_class ); ?>"
					data-attachmentid="<?php echo esc_attr( (string) $image_id ); ?>">
					<img src="<?php echo esc_url( $thumb[0] ); ?>" alt="<?php echo esc_attr( $thumbnail_alt ); ?>" />
					<button type="button" class="kodlo-variation-gallery-remove" aria-label="<?php esc_attr_e( 'Remove image', 'kodlo-product-gallery-for-elementor' ); ?>">
						<span class="dashicons dashicons-no" aria-hidden="true"></span>
					</button>
					<div class="kodlo-variation-gallery-order-controls">
						<button type="button" class="kodlo-variation-gallery-move-back" aria-label="<?php
							esc_attr_e( 'Move image backward', 'kodlo-product-gallery-for-elementor' );
						?>">
							<span class="dashicons dashicons-arrow-left-alt2" aria-hidden="true"></span>
						</button>
						<button type="button" class="kodlo-variation-gallery-move-forward" aria-label="<?php
							esc_attr_e( 'Move image forward', 'kodlo-product-gallery-for-elementor' );
						?>">
							<span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span>
						</button>
					</div>
					<?php if ( $video_url ) : ?>
						<div class="kodlo-variation-gallery-video" aria-hidden="true">
							<i class="dashicons dashicons-video-alt3"></i>
						</div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>

		<p>
			<button type="button" class="kodlo-variation-gallery-remove-all button <?php echo esc_attr( empty( $image_ids ) ? 'hidden' : '' ); ?>">
				<?php esc_html_e( 'Remove all', 'kodlo-product-gallery-for-elementor' ); ?>
			</button>
			<button type="button" class="kodlo-variation-gallery-upload button <?php echo esc_attr( ! empty( $image_ids ) ? 'hidden' : '' ); ?>">
				<?php esc_html_e( 'Add Gallery Images', 'kodlo-product-gallery-for-elementor' ); ?>
			</button>
			<button type="button" class="kodlo-variation-gallery-edit button <?php echo esc_attr( empty( $image_ids ) ? 'hidden' : '' ); ?>">
				<?php esc_html_e( 'Edit Gallery', 'kodlo-product-gallery-for-elementor' ); ?>
			</button>
		</p>

		<div class="hidden">
			<input type="hidden" class="kodlo-variation-gallery-input"
				name="kodlo_gallery_variation_images[<?php echo esc_attr( (string) $loop ); ?>]"
				value="<?php echo esc_attr( $gallery_value ); ?>" />
		</div>
	</div>
	<?php
}

/**
 * Saves variation gallery image IDs when product variation is saved.
 *
 * @param int $variation_id Variation post ID.
 * @param int $loop_index   Loop index of the variation form.
 */
function kodlo_gallery_save_variation_gallery( $variation_id, $loop_index ) {
	$ajax_nonce = isset( $_POST['security'] ) && is_scalar( $_POST['security'] )
		? sanitize_key( wp_unslash( (string) $_POST['security'] ) )
		: '';
	$post_nonce = isset( $_POST['woocommerce_meta_nonce'] ) && is_scalar( $_POST['woocommerce_meta_nonce'] )
		? sanitize_key( wp_unslash( (string) $_POST['woocommerce_meta_nonce'] ) )
		: '';

	if ( ! wp_verify_nonce( $ajax_nonce, 'save-variations' ) && ! wp_verify_nonce( $post_nonce, 'woocommerce_save_data' ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $variation_id ) ) {
		return;
	}

	if ( ! isset( $_POST['kodlo_gallery_variation_images'] ) || ! is_array( $_POST['kodlo_gallery_variation_images'] ) ) {
		return;
	}

	if ( isset( $_POST['kodlo_gallery_variation_images'][ $loop_index ] ) && is_scalar( $_POST['kodlo_gallery_variation_images'][ $loop_index ] ) ) {
		$gallery_field = sanitize_text_field( wp_unslash( $_POST['kodlo_gallery_variation_images'][ $loop_index ] ) );
		$gallery_value = implode( ',', kodlo_gallery_parse_image_ids( $gallery_field ) );
		kodlo_gallery_update_locked_post_meta( $variation_id, KODLO_GALLERY_VARIATION_META_KEY, $gallery_value );
	} else {
		kodlo_gallery_update_locked_post_meta( $variation_id, KODLO_GALLERY_VARIATION_META_KEY, '' );
	}
}

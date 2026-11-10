<?php
/**
 * Media item renderer for Kodlo Product Gallery for Elementor.
 *
 * Owns the shared video icon fallback and individual image/video markup.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Returns the internal video icon used when Elementor cannot render a selected icon.
 *
 * @return string SVG icon markup.
 */
function kodlo_gallery_get_default_video_icon_html() {
	return '<svg viewBox="0 0 64 64" width="60" height="60" aria-hidden="true" focusable="false">'
		. '<circle cx="32" cy="32" r="30" fill="rgba(0,0,0,0.6)" stroke="#fff" stroke-width="2"/>'
		. '<polygon points="25,18 48,32 25,46" fill="#fff"/>'
		. '</svg>';
}

/**
 * Renders the HTML markup for a single gallery media item (image or video poster).
 *
 * @param array  $image_data      Image data dictionary.
 * @param string $video_icon_html Custom raw HTML for play button icon.
 * @param string $extra_style     Inline styles for container.
 * @param array  $render_options  Caption, link, lightbox, animation, loading and defer options.
 */
function kodlo_gallery_render_media_item( $image_data, $video_icon_html = '', $extra_style = '', $render_options = array() ) {
	$has_video = ! empty( $image_data['video'] );
	$show_caption = ! empty( $render_options['show_caption'] ) && ! empty( $image_data['caption'] );
	$link_type = $render_options['link_type'] ?? 'none';
	$link_url = '';

	if ( 'file' === $link_type ) {
		$link_url = $image_data['full'] ?? $image_data['medium'];
	} elseif ( 'attachment' === $link_type ) {
		$link_url = $image_data['attachment_url'] ?? '';
	}

	if ( empty( $video_icon_html ) ) { $video_icon_html = kodlo_gallery_get_default_video_icon_html(); }
	?>
	<figure class="kodlo-gallery-entry">
		<div class="kodlo-media-item<?php echo esc_attr( $has_video ? ' has-video' : '' ); ?>"
			<?php if ( $has_video ) : ?>data-video-url="<?php echo esc_url( $image_data['video'] ); ?>"<?php endif; ?>
			data-full-image="<?php echo esc_url( $image_data['full'] ?? $image_data['medium'] ); ?>"
			<?php if ( $extra_style ) : ?>style="<?php echo esc_attr( $extra_style ); ?>"<?php endif; ?>>

			<?php if ( $link_url ) : ?>
				<a class="kodlo-media-link" href="<?php echo esc_url( $link_url ); ?>"
					<?php if ( 'file' === $link_type && ! empty( $render_options['lightbox_enabled'] ) ) : ?>
						data-elementor-open-lightbox="yes"
						data-elementor-lightbox-slideshow="<?php echo esc_attr( $render_options['lightbox_group'] ?? 'kodlo-gallery' ); ?>"
					<?php endif; ?>>
			<?php endif; ?>

			<?php
			$loading_mode = $render_options['img_loading_mode'] ?? null;
			// Deferred images carry their sources in data attributes until the restore script moves them back.
			$source_prefix = empty( $render_options['defer'] ) ? '' : 'data-kodlo-';
			?>
			<img <?php echo esc_attr( $source_prefix ); ?>src="<?php echo esc_url( $image_data['medium'] ); ?>"
				<?php if ( ! empty( $image_data['width'] ) ) : ?>width="<?php echo esc_attr( $image_data['width'] ); ?>"<?php endif; ?>
				<?php if ( ! empty( $image_data['height'] ) ) : ?>height="<?php echo esc_attr( $image_data['height'] ); ?>"<?php endif; ?>
				<?php if ( ! empty( $image_data['srcset'] ) ) : ?><?php echo esc_attr( $source_prefix ); ?>srcset="<?php echo esc_attr( $image_data['srcset'] ); ?>"<?php endif; ?>
				<?php if ( ! empty( $image_data['sizes'] ) ) : ?>sizes="<?php echo esc_attr( $image_data['sizes'] ); ?>"<?php endif; ?>
				alt="<?php echo esc_attr( $image_data['alt'] ); ?>"
				<?php if ( 'high' === $loading_mode ) : ?>fetchpriority="high" decoding="async"<?php
				elseif ( 'lazy' === $loading_mode ) : ?>loading="lazy" decoding="async"<?php
				endif; ?>
				class="kodlo-main-image" />

			<?php if ( $link_url ) : ?>
				</a>
			<?php endif; ?>

			<?php if ( $has_video ) : ?>
				<?php
				$hover_animation = $render_options['hover_animation'] ?? '';
				$hover_class = $hover_animation ? ' elementor-animation-' . $hover_animation : '';
				?>
				<div class="kodlo-video-play-btn-wrap">
					<button type="button" class="kodlo-video-play-btn<?php echo esc_attr( $hover_class ); ?>" aria-label="<?php
						esc_attr_e( 'Play video', 'kodlo-product-gallery-for-elementor' );
					?>">
						<?php echo $video_icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor icon HTML. ?>
					</button>
				</div>
				<div class="kodlo-video-container"></div>
			<?php endif; ?>
		</div>

		<?php if ( $show_caption ) : ?>
			<figcaption class="kodlo-gallery-caption"><?php echo esc_html( $image_data['caption'] ); ?></figcaption>
		<?php endif; ?>
	</figure>
	<?php
}

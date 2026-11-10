<?php
/**
 * Frontend markup template for Kodlo Product Gallery for Elementor.
 *
 * Renders the normalized context prepared by the widget render entry point.
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
?>

<div class="kodlo-gallery-wrapper kodlo-elementor-gallery-wrapper"
	 data-gallery-id="<?php echo esc_attr( $widget_id ); ?>"
	 data-product-id="<?php echo esc_attr( $product->get_id() ); ?>"
	 data-desktop-view-enabled="<?php echo esc_attr( $desktop_view_enabled ? 'yes' : 'no' ); ?>"
	 data-mobile-view-enabled="<?php echo esc_attr( $mobile_view_enabled ? 'yes' : 'no' ); ?>"
	 data-show-pagination="<?php echo esc_attr( $show_pagination ); ?>"
	 data-main-nav-position="<?php echo esc_attr( $main_navigation_position ); ?>"
	 data-thumbs-nav-position="<?php echo esc_attr( $thumbnails_navigation_position ); ?>"
	 data-max-visible-dots="<?php echo esc_attr( $max_visible_dots ); ?>"
	 data-dot-position="<?php echo esc_attr( $dot_position ); ?>"
	 data-autoplay="<?php echo esc_attr( $autoplay ); ?>"
	 data-mute-videos="<?php echo esc_attr( $mute_videos ); ?>"
	 data-loop-videos="<?php echo esc_attr( $loop_videos ); ?>"
	 data-loop-mobile="<?php echo esc_attr( $loop_mobile ); ?>"
	 data-thumb-gap="<?php echo esc_attr( $thumbnail_gap ); ?>"
	 data-mobile-breakpoint="<?php echo esc_attr( $mobile_breakpoint ); ?>"
	 data-thumbs-per-view="<?php echo esc_attr( $thumbnails_per_view ); ?>"
	 data-mobile-edge-bleed="<?php echo esc_attr( $mobile_edge_bleed ? 'yes' : 'no' ); ?>"
	 data-navigation-edge-nav="<?php echo esc_attr( $edge_bleed_navigation_alignment ); ?>"
	 data-show-thumbnail-video-icon="<?php echo esc_attr( $show_thumbnail_video_icon ? 'yes' : 'no' ); ?>"
	 data-rendered-desktop-max="<?php echo esc_attr( $max_images ); ?>"
	 data-rendered-mobile-max="<?php echo esc_attr( $max_images_mobile ); ?>"
	 data-desktop-image-effect="<?php echo esc_attr( $desktop_image_effect ); ?>"
	 data-mobile-image-effect="<?php echo esc_attr( $mobile_image_effect ); ?>"
	 data-desktop-magnifier-scope="<?php echo esc_attr( $desktop_magnifier_scope ); ?>"
	 data-mobile-magnifier-scope="<?php echo esc_attr( $mobile_magnifier_scope ); ?>"
	 data-mobile-magnifier-icon="<?php echo esc_attr( $mobile_magnifier_icon_html ); ?>"
	 data-mobile-magnifier-active-icon="<?php echo esc_attr( $mobile_magnifier_active_icon_html ); ?>"
	 data-video-icon="<?php echo esc_attr( $video_icon_html ); ?>"
	 data-show-variations="<?php echo esc_attr( $show_variations ? 'yes' : 'no' ); ?>"
	 data-last-image-full-width="<?php echo esc_attr( $last_image_full_width ); ?>"
	 data-last-image-aspect-ratio="<?php echo esc_attr( $last_image_aspect_ratio ); ?>"
	 data-last-image-object-fit="<?php echo esc_attr( $last_image_object_fit ); ?>"
	 data-desktop-columns="<?php echo esc_attr( $desktop_columns ); ?>"
	 data-show-caption="<?php echo esc_attr( $show_caption ? 'yes' : 'no' ); ?>"
	 data-gallery-link="<?php echo esc_attr( $gallery_link ); ?>"
	 data-lightbox-enabled="<?php echo esc_attr( $lightbox_enabled ? 'yes' : 'no' ); ?>"
	 data-lightbox-group="<?php echo esc_attr( $media_options['lightbox_group'] ); ?>"
	 data-play-btn-hover-animation="<?php echo esc_attr( $video_hover_animation ); ?>"
	 data-gallery-source="default"
	 data-img-optimization="<?php echo esc_attr( $optimization_enabled ? 'yes' : 'no' ); ?>"
	 data-desktop-priority-count="<?php echo esc_attr( $desktop_priority_count ); ?>"
	 data-mobile-main-priority-count="<?php echo esc_attr( $mobile_main_priority_count ); ?>"
	 data-mobile-thumbs-priority-count="<?php echo esc_attr( $mobile_thumbs_priority_count ); ?>"
	 data-deferred-images="<?php echo esc_attr( $defer_offscreen_images ? 'yes' : 'no' ); ?>">

	<?php if ( $show_sale_flash ) : ?>
		<?php if ( $sale_flash_has_link ) : ?>
			<a class="kodlo-sale-flash kodlo-sale-position-<?php echo esc_attr( $sale_flash_position ); ?>" <?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor attribute string.
				echo $this->get_render_attribute_string( 'sale_flash_link' );
			?>>
				<?php wc_get_template( 'loop/sale-flash.php' ); ?>
			</a>
		<?php else : ?>
			<div class="kodlo-sale-flash kodlo-sale-position-<?php echo esc_attr( $sale_flash_position ); ?>">
				<?php wc_get_template( 'loop/sale-flash.php' ); ?>
			</div>
		<?php endif; ?>
	<?php endif; ?>

	<div class="kodlo-main-slider-wrap<?php
		echo esc_attr( $show_main_navigation && 'overlay' === $main_navigation_position ? ' kodlo-nav-overlay' : '' );
	?>">
		<?php if ( $show_main_navigation ) : ?>
			<button type="button" class="kodlo-nav-btn kodlo-nav-prev kodlo-main-nav-prev" aria-label="<?php
				esc_attr_e( 'Previous', 'kodlo-product-gallery-for-elementor' );
			?>">
				<?php
					echo $main_navigation_previous_icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor icon HTML.
				?>
			</button>
		<?php endif; ?>
		<div class="swiper-container swiper kodlo-mobile-slider">
			<div class="swiper-wrapper kodlo-gallery-main-wrapper">
				<?php foreach ( $product_images as $index => $gallery_image ) : ?>
					<?php
					$is_hidden_desktop = ( $max_images > 0 && $index >= $max_images );
					$is_hidden_mobile = ( $max_images_mobile > 0 && $index >= $max_images_mobile );

					$slide_classes = array( 'swiper-slide', 'kodlo-gallery-main-slide', 'kodlo-grid-item' );
					if ( $is_hidden_desktop ) { $slide_classes[] = 'kodlo-hide-desktop'; }
					if ( $is_hidden_mobile ) { $slide_classes[] = 'kodlo-hide-mobile'; }

					$grid_item_style = '';
					$media_item_style = '';
					$is_last_index = ( $index === $desktop_images_count - 1 );
					$is_first_index = ( 0 === $index );
					$has_incomplete_desktop_row = ( $desktop_images_count % $desktop_columns !== 0 );
					$should_stretch = $has_incomplete_desktop_row && (
						( 'last' === $last_image_full_width && $is_last_index ) ||
						( 'first' === $last_image_full_width && $is_first_index )
					);

					if ( $should_stretch ) {
						$grid_item_style = 'grid-column: 1 / -1;';
						$media_item_style = sprintf(
							'--kodlo-aspect-ratio: %s; --kodlo-object-fit: %s;',
							$last_image_aspect_ratio,
							$last_image_object_fit
						);
					}
					?>
					<div class="<?php echo esc_attr( implode( ' ', $slide_classes ) ); ?>" data-index="<?php
						echo esc_attr( $index );
					?>"<?php if ( $grid_item_style ) : ?> style="<?php
						echo esc_attr( $grid_item_style );
					?>"<?php endif; ?>>
						<?php
						$media_item_options = $media_options;
						$media_item_options['img_loading_mode'] = $optimization_enabled
							? ( $index < $desktop_priority_count ? 'high' : ( $index < $mobile_main_priority_count ? 'high' : 'lazy' ) )
							: null;
						$media_item_options['defer'] = $defer_offscreen_images && $index >= $defer_main_from;
						kodlo_gallery_render_media_item( $gallery_image, $video_icon_html, $media_item_style, $media_item_options );
						?>
					</div>
				<?php endforeach; ?>
			</div>
			<?php if ( 'yes' === $show_pagination ) : ?>
				<div class="swiper-pagination"></div>
			<?php endif; ?>
		</div>
		<?php if ( $show_main_navigation ) : ?>
			<button type="button" class="kodlo-nav-btn kodlo-nav-next kodlo-main-nav-next" aria-label="<?php
				esc_attr_e( 'Next', 'kodlo-product-gallery-for-elementor' );
			?>">
				<?php
					echo $main_navigation_next_icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor icon HTML.
				?>
			</button>
		<?php endif; ?>
	</div>

	<?php if ( $show_thumbnails ) : ?>
	<div class="kodlo-thumbs-slider-wrap<?php
		echo esc_attr( $show_thumbnails_navigation && 'overlay' === $thumbnails_navigation_position ? ' kodlo-nav-overlay' : '' );
	?>">
		<?php if ( $show_thumbnails_navigation ) : ?>
			<button type="button" class="kodlo-nav-btn kodlo-nav-prev kodlo-thumbs-nav-prev" aria-label="<?php
				esc_attr_e( 'Previous', 'kodlo-product-gallery-for-elementor' );
			?>">
				<?php
					echo $thumbnails_navigation_previous_icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor icon HTML.
				?>
			</button>
		<?php endif; ?>
		<div class="swiper-container swiper kodlo-thumbs-slider">
			<div class="swiper-wrapper">
				<?php foreach ( $product_images as $index => $gallery_image ) : ?>
					<?php
					$is_hidden_mobile = ( $max_images_mobile > 0 && $index >= $max_images_mobile );
					if ( $is_hidden_mobile ) { continue; }
					$has_video_thumbnail = ! empty( $gallery_image['video'] );
					$is_video_thumbnail = $show_thumbnail_video_icon && $has_video_thumbnail;
					if ( $has_video_thumbnail ) {
						/* translators: %d: thumbnail position number */
						$thumbnail_label = sprintf( esc_html__( 'View video %d', 'kodlo-product-gallery-for-elementor' ), $index + 1 );
					} else {
						/* translators: %d: thumbnail position number */
						$thumbnail_label = sprintf( esc_html__( 'View image %d', 'kodlo-product-gallery-for-elementor' ), $index + 1 );
					}
					?>
					<div class="swiper-slide">
						<button type="button" class="kodlo-thumb-button" aria-label="<?php echo esc_attr( $thumbnail_label ); ?>">
							<?php
							$thumb_loading_mode = $optimization_enabled
								? ( $index < $mobile_thumbs_priority_count ? 'high' : 'lazy' )
								: null;
							$thumb_source_prefix = $defer_offscreen_images && $index >= $defer_thumbs_from ? 'data-kodlo-' : '';
							?>
							<img <?php echo esc_attr( $thumb_source_prefix ); ?>src="<?php echo esc_url( $gallery_image['thumb'] ); ?>"
								 <?php if ( 'lazy' === $thumb_loading_mode && ! empty( $gallery_image['thumb_srcset'] ) && ! empty( $gallery_image['thumb_sizes'] ) ) : ?>
								 <?php echo esc_attr( $thumb_source_prefix ); ?>srcset="<?php echo esc_attr( $gallery_image['thumb_srcset'] ); ?>" sizes="<?php echo esc_attr( $gallery_image['thumb_sizes'] ); ?>"
								 <?php endif; ?>
								 alt=""
								 <?php if ( ! empty( $gallery_image['thumb_width'] ) ) : ?>width="<?php echo esc_attr( $gallery_image['thumb_width'] ); ?>"<?php endif; ?>
								 <?php if ( ! empty( $gallery_image['thumb_height'] ) ) : ?>height="<?php echo esc_attr( $gallery_image['thumb_height'] ); ?>"<?php endif; ?>
								 <?php if ( 'high' === $thumb_loading_mode ) : ?>fetchpriority="high" decoding="async"<?php
								 elseif ( 'lazy' === $thumb_loading_mode ) : ?>loading="lazy" decoding="async"<?php
								 endif; ?> />
							<?php if ( $is_video_thumbnail ) : ?>
								<span class="kodlo-thumbnail-video-icon" aria-hidden="true">
									<?php
									echo $video_icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Reviewed Elementor Icons_Manager output.
									?>
								</span>
							<?php endif; ?>
						</button>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php if ( $show_thumbnails_navigation ) : ?>
			<button type="button" class="kodlo-nav-btn kodlo-nav-next kodlo-thumbs-nav-next" aria-label="<?php
				esc_attr_e( 'Next', 'kodlo-product-gallery-for-elementor' );
			?>">
				<?php
					echo $thumbnails_navigation_next_icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor icon HTML.
				?>
			</button>
		<?php endif; ?>
	</div>
	<?php endif; ?>

	<?php // Non-executable per-widget data uses JSON_HEX flags to prevent HTML parser break-out. ?>
	<script type="application/json" class="kodlo-gallery-data">
		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON_HEX flags prevent HTML parser break-out.
		echo wp_json_encode(
			$gallery_data,
			JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
		);
		?>
	</script>
</div>
<?php
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

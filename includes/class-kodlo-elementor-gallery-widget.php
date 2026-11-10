<?php
/**
 * Widget class for Kodlo Product Gallery for Elementor.
 *
 * Defines the product gallery widget behavior, importing layout/style controls and frontend HTML/CSS rendering templates.
 */

defined( 'ABSPATH' ) || exit;

class Kodlo_Product_Gallery_Widget extends \Elementor\Widget_Base {

	/**
	 * Returns the widget unique identifier.
	 *
	 * @return string Widget name.
	 */
	public function get_name(): string {
		return 'kodlo-product-gallery';
	}

	/**
	 * Returns the human-readable title of the widget.
	 *
	 * @return string Widget title.
	 */
	public function get_title(): string {
		return esc_html__( 'Kodlo Product Gallery', 'kodlo-product-gallery-for-elementor' );
	}

	/**
	 * Returns the widget icon shown in the Elementor panel.
	 *
	 * @return string Icon class.
	 */
	public function get_icon(): string {
		return 'eicon-product-images';
	}

	/**
	 * Returns the widget categories it belongs to.
	 *
	 * @return array Categories.
	 */
	public function get_categories(): array {
		return array( 'woocommerce-elements', 'general' );
	}

	/**
	 * Returns search keywords for the widget.
	 *
	 * @return array Keywords.
	 */
	public function get_keywords(): array {
		return array( 'woocommerce', 'shop', 'gallery', 'image', 'video', 'product', 'kodlo' );
	}

	/**
	 * Declares style dependencies for this widget.
	 *
	 * Returns only the stylesheets needed for the current widget configuration.
	 * In the editor all assets are loaded so that live setting changes take effect without a reload.
	 *
	 * @return array Stylesheet handles.
	 */
	public function get_style_depends(): array {
		$style_handles = array();
		$asset_requirements = $this->get_asset_requirements();

		// Elementor's own Swiper stylesheet (e-swiper depends on swiper), loaded before the gallery styles override it.
		if ( $asset_requirements['swiper'] ) { $style_handles[] = 'e-swiper'; }
		$style_handles[] = 'kodlo-elementor-gallery-frontend-css';
		if ( $asset_requirements['magnifier'] ) { $style_handles[] = 'kodlo-elementor-gallery-magnifier-css'; }
		if ( $asset_requirements['edge_bleed'] ) { $style_handles[] = 'kodlo-elementor-gallery-edge-bleed-css'; }

		return $style_handles;
	}

	/**
	 * Declares script dependencies for this widget.
	 *
	 * Returns only the scripts needed for the current widget configuration.
	 * In the editor all assets are loaded so that live setting changes take effect without a reload.
	 * Optional scripts (magnifier, edge-bleed) are listed before frontend.js to ensure load order.
	 *
	 * @return array Script handles.
	 */
	public function get_script_depends(): array {
		$script_handles = array();
		$asset_requirements = $this->get_asset_requirements();

		if ( $asset_requirements['magnifier'] ) { $script_handles[] = 'kodlo-elementor-gallery-magnifier-js'; }
		if ( $asset_requirements['edge_bleed'] ) { $script_handles[] = 'kodlo-elementor-gallery-edge-bleed-js'; }
		if ( $asset_requirements['swiper'] ) { $script_handles[] = 'swiper'; }
		if ( $asset_requirements['deferred_images'] ) { $script_handles[] = 'kodlo-elementor-gallery-deferred-images-js'; }

		$script_handles[] = 'kodlo-elementor-gallery-frontend-js';
		return $script_handles;
	}

	/**
	 * Resolves the current WooCommerce product instance from context.
	 *
	 * @return WC_Product|null Product instance or null.
	 */
	public function get_product() {
		// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
		global $product;

		if ( $product instanceof WC_Product ) {
			return $product;
		}

		if ( is_product() ) {
			$product = wc_get_product( get_queried_object_id() );
			if ( $product ) {
				return $product;
			}
		}

		$post_id = get_the_ID();
		if ( $post_id && 'product' === get_post_type( $post_id ) ) {
			$product = wc_get_product( $post_id );
			if ( $product ) {
				return $product;
			}
		}

		if ( $this->is_editor_request() ) {
			$products = wc_get_products( array( 'limit' => 1 ) );
			if ( ! empty( $products ) ) {
				$product = $products[0];
				return $product;
			}
		}

		return null;
		// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	}

	/**
	 * Registers the Elementor widget controls.
	 */
	protected function register_controls(): void {
		require __DIR__ . '/kodlo-elementor-gallery-widget-controls.php';
	}

	/**
	 * Renders the widget HTML output on the frontend.
	 *
	 * Any failure discards the partial gallery markup, is logged, and prints the WooCommerce product images instead,
	 * so a product page or the editor never stops on a gallery error.
	 */
	protected function render(): void {
		$product = null;
		$buffer_level = ob_get_level();

		try {
			$product = $this->get_product();
			if ( ! $product ) { return; }

			ob_start();
			require __DIR__ . '/kodlo-elementor-gallery-widget-render-context.php';
			require __DIR__ . '/kodlo-elementor-gallery-widget-dynamic-styles.php';
			require __DIR__ . '/kodlo-elementor-gallery-widget-template.php';
			ob_end_flush();
		} catch ( \Throwable $render_error ) {
			while ( ob_get_level() > $buffer_level ) { ob_end_clean(); }
			$this->log_render_error( 'rendering failed, the WooCommerce product images were shown instead', $render_error );
			$this->render_woocommerce_fallback( $product );
		}
	}

	/**
	 * Interprets widget settings shared by style and script dependency lists.
	 *
	 * @return array Required optional asset groups.
	 */
	private function get_asset_requirements(): array {
		$all_asset_requirements = array(
			'magnifier'       => true,
			'edge_bleed'      => true,
			'swiper'          => true,
			'deferred_images' => true,
		);

		try {
			if ( $this->is_editor_request() ) {
				// The editor never prints deferred images (render context), so the restore script is not needed there.
				return array_merge( $all_asset_requirements, array( 'deferred_images' => false ) );
			}

			$settings = $this->get_settings_for_display();
			$desktop_view_enabled = 'yes' === ( $settings['enable_desktop_view'] ?? 'yes' );
			$mobile_view_enabled = 'yes' === ( $settings['enable_mobile_view'] ?? 'yes' );
			if ( ! $desktop_view_enabled && ! $mobile_view_enabled ) { $desktop_view_enabled = true; }
			$desktop_image_effect = $settings['desktop_image_effect'] ?? 'none';
			$mobile_image_effect = $settings['mobile_image_effect'] ?? 'none';

			return array(
				'magnifier'  => ( $desktop_view_enabled && 'magnifier' === $desktop_image_effect )
					|| ( $mobile_view_enabled && 'magnifier' === $mobile_image_effect ),
				'edge_bleed' => $mobile_view_enabled && 'yes' === ( $settings['enable_mobile_edge_bleed'] ?? 'no' ),
				'swiper'     => $mobile_view_enabled,
				'deferred_images' => $mobile_view_enabled
					&& 'yes' === ( $settings['image_loading_optimization'] ?? 'no' )
					&& 'yes' === ( $settings['defer_offscreen_images'] ?? 'no' ),
			);
		} catch ( \Throwable $requirements_error ) {
			$this->log_render_error( 'asset detection failed, every optional asset was loaded', $requirements_error );
			// Every optional asset keeps a gallery that still renders fully functional.
			return $all_asset_requirements;
		}
	}

	/**
	 * Reports whether the current request is the Elementor editor or its preview.
	 *
	 * Elementor components that are missing in a partially booted request count as a normal frontend request.
	 *
	 * @return bool True in the Elementor editor or preview.
	 */
	private function is_editor_request(): bool {
		$elementor = \Elementor\Plugin::$instance;
		if ( ! is_object( $elementor ) ) { return false; }

		$editor = $elementor->editor ?? null;
		if ( is_object( $editor ) && method_exists( $editor, 'is_edit_mode' ) && $editor->is_edit_mode() ) { return true; }

		$preview = $elementor->preview ?? null;
		return is_object( $preview ) && method_exists( $preview, 'is_preview_mode' ) && $preview->is_preview_mode();
	}

	/**
	 * Writes a gallery failure to the WooCommerce log once per hour for each distinct error.
	 *
	 * @param string     $outcome Failed step and the substitute that was used.
	 * @param \Throwable $error   Caught error or exception.
	 */
	private function log_render_error( string $outcome, \Throwable $error ): void {
		try {
			$error_file = str_replace( wp_normalize_path( ABSPATH ), '', wp_normalize_path( $error->getFile() ) );
			$error_signature = md5( $outcome . '|' . get_class( $error ) . '|' . $error->getMessage() . '|' . $error_file . '|' . $error->getLine() );
			$throttle_key = 'kodlo_gallery_render_error_' . $error_signature;
			if ( ! function_exists( 'wc_get_logger' ) || get_transient( $throttle_key ) ) { return; }

			set_transient( $throttle_key, 1, HOUR_IN_SECONDS );
			wc_get_logger()->error(
				sprintf(
					'Kodlo Product Gallery widget %1$s: %2$s. %3$s: %4$s in %5$s:%6$d',
					$this->get_id(),
					$outcome,
					get_class( $error ),
					$error->getMessage(),
					$error_file,
					$error->getLine()
				),
				array( 'source' => 'kodlo-product-gallery-for-elementor' )
			);
		} catch ( \Throwable $logging_error ) {
			// Logging must never replace the original fallback path.
			return;
		}
	}

	/**
	 * Prints the WooCommerce product images when the Kodlo gallery cannot render.
	 *
	 * @param WC_Product|null $product Product resolved before the failure, if any.
	 */
	private function render_woocommerce_fallback( $product ): void {
		try {
			if ( ! $product instanceof WC_Product || ! function_exists( 'wc_get_gallery_image_html' ) ) { return; }

			$image_ids = array_merge( array( $product->get_image_id() ), $product->get_gallery_image_ids() );
			$image_ids = array_values( array_unique( array_filter( array_map( 'absint', $image_ids ) ) ) );

			// WooCommerce image markup keeps its responsive sources; data-* attributes are allowed globally by kses.
			$allowed_html = wp_kses_allowed_html( 'post' );
			$allowed_html['img'] = array_merge( (array) ( $allowed_html['img'] ?? array() ), array( 'srcset' => true, 'sizes' => true ) );

			$buffer_level = ob_get_level();
			ob_start();
			if ( $this->is_editor_request() ) {
				echo '<p class="kodlo-gallery-fallback-notice">' . esc_html__( 'Kodlo Product Gallery could not be displayed, so the WooCommerce product images are shown. Details are in WooCommerce > Status > Logs.', 'kodlo-product-gallery-for-elementor' ) . '</p>';
			}
			echo '<div class="kodlo-gallery-fallback">';
			if ( empty( $image_ids ) ) {
				echo wp_kses( wc_placeholder_img( 'woocommerce_single' ), $allowed_html );
			}
			// Every image uses the single-product size: theme thumbnail sizes would be stretched by the fallback layout.
			foreach ( $image_ids as $image_index => $image_id ) {
				echo wp_kses( wc_get_gallery_image_html( $image_id, true, $image_index ), $allowed_html );
			}
			echo '</div>';
			ob_end_flush();
		} catch ( \Throwable $fallback_error ) {
			while ( isset( $buffer_level ) && ob_get_level() > $buffer_level ) { ob_end_clean(); }
			$this->log_render_error( 'the WooCommerce image fallback failed, nothing was printed', $fallback_error );
		}
	}
}

<?php
/**
 * Runtime requirement checks for Kodlo Product Gallery for Elementor.
 *
 * Keeps machine-readable dependency detection separate from localized
 * administrator messages so early bootstrap checks do not invoke gettext.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Returns machine-readable runtime requirement failures without translating text.
 *
 * @return array<string, string|bool> Requirement code and detected value.
 */
function kodlo_gallery_get_unmet_requirements(): array {
	global $wp_version;

	$unmet_requirements = array();

	if ( version_compare( PHP_VERSION, KODLO_GALLERY_MINIMUM_PHP_VERSION, '<' ) ) {
		$unmet_requirements['php_version'] = PHP_VERSION;
	}

	if ( ! isset( $wp_version ) || version_compare( (string) $wp_version, KODLO_GALLERY_MINIMUM_WORDPRESS_VERSION, '<' ) ) {
		$unmet_requirements['wordpress_version'] = isset( $wp_version ) ? (string) $wp_version : '';
	}

	if ( ! class_exists( 'WooCommerce' ) || ! defined( 'WC_VERSION' ) ) {
		$unmet_requirements['woocommerce_missing'] = true;
	} elseif ( version_compare( WC_VERSION, KODLO_GALLERY_MINIMUM_WOOCOMMERCE_VERSION, '<' ) ) {
		$unmet_requirements['woocommerce_version'] = WC_VERSION;
	}

	if ( ! did_action( 'elementor/loaded' ) || ! defined( 'ELEMENTOR_VERSION' ) ) {
		$unmet_requirements['elementor_missing'] = true;
	} elseif ( version_compare( ELEMENTOR_VERSION, KODLO_GALLERY_MINIMUM_ELEMENTOR_VERSION, '<' ) ) {
		$unmet_requirements['elementor_version'] = ELEMENTOR_VERSION;
	}

	return $unmet_requirements;
}

/**
 * Returns localized runtime requirement errors.
 *
 * @return string[] Human-readable requirement errors.
 */
function kodlo_gallery_get_requirement_errors(): array {
	$unmet_requirements = kodlo_gallery_get_unmet_requirements();
	$errors             = array();

	if ( isset( $unmet_requirements['php_version'] ) ) {
		$errors[] = sprintf(
			/* translators: 1: required PHP version, 2: detected PHP version. */
			__( 'Kodlo Product Gallery for Elementor requires PHP %1$s or newer. Detected version: %2$s.', 'kodlo-product-gallery-for-elementor' ),
			KODLO_GALLERY_MINIMUM_PHP_VERSION,
			$unmet_requirements['php_version']
		);
	}

	if ( array_key_exists( 'wordpress_version', $unmet_requirements ) ) {
		$errors[] = sprintf(
			/* translators: 1: required WordPress version, 2: detected WordPress version. */
			__( 'Kodlo Product Gallery for Elementor requires WordPress %1$s or newer. Detected version: %2$s.', 'kodlo-product-gallery-for-elementor' ),
			KODLO_GALLERY_MINIMUM_WORDPRESS_VERSION,
			'' !== $unmet_requirements['wordpress_version'] ? $unmet_requirements['wordpress_version'] : __( 'unknown', 'kodlo-product-gallery-for-elementor' )
		);
	}

	if ( isset( $unmet_requirements['woocommerce_missing'] ) ) {
		$errors[] = __( 'Kodlo Product Gallery for Elementor requires WooCommerce to be active.', 'kodlo-product-gallery-for-elementor' );
	} elseif ( isset( $unmet_requirements['woocommerce_version'] ) ) {
		$errors[] = sprintf(
			/* translators: 1: required WooCommerce version, 2: detected WooCommerce version. */
			__( 'Kodlo Product Gallery for Elementor requires WooCommerce %1$s or newer. Detected version: %2$s.', 'kodlo-product-gallery-for-elementor' ),
			KODLO_GALLERY_MINIMUM_WOOCOMMERCE_VERSION,
			$unmet_requirements['woocommerce_version']
		);
	}

	if ( isset( $unmet_requirements['elementor_missing'] ) ) {
		$errors[] = __( 'Kodlo Product Gallery for Elementor requires Elementor to be active.', 'kodlo-product-gallery-for-elementor' );
	} elseif ( isset( $unmet_requirements['elementor_version'] ) ) {
		$errors[] = sprintf(
			/* translators: 1: required Elementor version, 2: detected Elementor version. */
			__( 'Kodlo Product Gallery for Elementor requires Elementor %1$s or newer. Detected version: %2$s.', 'kodlo-product-gallery-for-elementor' ),
			KODLO_GALLERY_MINIMUM_ELEMENTOR_VERSION,
			$unmet_requirements['elementor_version']
		);
	}

	return $errors;
}

/** Displays runtime requirement errors to plugin administrators. */
function kodlo_gallery_admin_requirements_notice(): void {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	foreach ( kodlo_gallery_get_requirement_errors() as $error ) {
		printf( '<div class="notice notice-error"><p>%s</p></div>', esc_html( $error ) );
	}
}

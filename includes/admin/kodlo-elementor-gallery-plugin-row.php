<?php
/**
 * Plugin-list row enhancements for Kodlo Product Gallery for Elementor.
 *
 * Adds a WordPress details-modal link when the directory has not already
 * supplied one for this plugin row.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Adds the details link beside the plugin version and author.
 *
 * @param array  $plugin_meta Current row meta links.
 * @param string $plugin_file Path to the plugin file relative to the plugins directory.
 * @return array Modified row meta links.
 */
function kodlo_gallery_plugin_row_meta( array $plugin_meta, string $plugin_file ): array {
	if ( plugin_basename( KODLO_ELEMENTOR_GALLERY_FILE ) !== $plugin_file ) {
		return $plugin_meta;
	}

	foreach ( $plugin_meta as $meta ) {
		if ( false !== strpos( $meta, 'plugin-information' ) || false !== strpos( $meta, 'open-plugin-details-modal' ) ) {
			return $plugin_meta;
		}
	}

	add_thickbox();
	$details_url   = self_admin_url( 'plugin-install.php?tab=plugin-information&plugin=kodlo-product-gallery-for-elementor&TB_iframe=true&width=600&height=550' );
	$plugin_meta[] = sprintf(
		'<a href="%s" class="thickbox open-plugin-details-modal" aria-label="%s">%s</a>',
		esc_url( $details_url ),
		esc_attr__( 'More information about Kodlo Product Gallery for Elementor', 'kodlo-product-gallery-for-elementor' ),
		esc_html__( 'View details', 'kodlo-product-gallery-for-elementor' )
	);

	return $plugin_meta;
}

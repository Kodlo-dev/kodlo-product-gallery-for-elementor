/**
 * Gallery data adapter for Kodlo Product Gallery for Elementor.
 *
 * Resolves the compact shared-media payload into the image arrays consumed by
 * the frontend controller.
 */
( function( window ) {
	'use strict';

	var frontend = window.kodloGalleryFrontend = window.kodloGalleryFrontend || {};

	frontend.getGalleryData = function( wrapper ) {
		if ( wrapper.kodloGalleryData ) {
			return wrapper.kodloGalleryData;
		}

		var dataElement = wrapper.querySelector( '.kodlo-gallery-data' );
		if ( ! dataElement ) {
			return null;
		}

		try {
			var parsed = JSON.parse( dataElement.textContent );
			if ( parsed && parsed.media && Array.isArray( parsed.defaultImageIds ) ) {
				var resolveImages = function( ids ) {
					return ( ids || [] ).map( function( id ) {
						return parsed.media[ String( id ) ];
					} ).filter( Boolean );
				};

				wrapper.kodloGalleryData = {
					defaultImages: resolveImages( parsed.defaultImageIds ),
					variations: ( parsed.variations || [] ).map( function( variation ) {
						return {
							id: variation.id,
							images: resolveImages( variation.imageIds )
						};
					} )
				};
			} else {
				wrapper.kodloGalleryData = parsed;
			}
		} catch ( error ) {
			return null;
		}

		return wrapper.kodloGalleryData;
	};
}( window ) );

/**
 * WooCommerce variation adapter for Kodlo Product Gallery for Elementor.
 *
 * Connects WooCommerce variation events to the stable gallery controller API.
 */
( function( window, document, jQuery ) {
	'use strict';

	var frontend = window.kodloGalleryFrontend = window.kodloGalleryFrontend || {};
	var listenersBound = false;

	function getGalleryWrappersForForm( form ) {
		var productId = form.getAttribute( 'data-product_id' );
		var productIdInput = form.querySelector( 'input[name="product_id"]' );
		var productRoot = form.closest( '.product' );
		var wrappers = frontend.queryAll( '.kodlo-gallery-wrapper', productRoot || document );

		if ( ! productId && productIdInput ) {
			productId = productIdInput.value;
		}
		if ( productId ) {
			var matchingWrappers = wrappers.filter( function( wrapper ) {
				return String( wrapper.dataset.productId ) === String( productId );
			} );
			if ( matchingWrappers.length ) {
				return matchingWrappers;
			}
		}
		return wrappers;
	}

	function restoreDefaultGallery( wrapper, galleryData ) {
		if ( wrapper.dataset.gallerySource === 'default' ) return;
		frontend.renderGalleryIfChanged( galleryData.defaultImages, wrapper );
		wrapper.dataset.gallerySource = 'default';
	}

	function applyVariationToWrapper( wrapper, galleryData, variationId ) {
		if ( ! variationId ) {
			restoreDefaultGallery( wrapper, galleryData );
			return;
		}

		var matchingVariation = galleryData.variations.find( function( variation ) {
			return String( variation.id ) === String( variationId );
		} );
		var variationImages = matchingVariation && Array.isArray( matchingVariation.images )
			? matchingVariation.images
			: [];

		if ( variationImages.length === 0 ) {
			restoreDefaultGallery( wrapper, galleryData );
			return;
		}

		var showVariationImages = wrapper.dataset.showVariations !== 'no';
		var sourceKey = 'variation:' + variationId + ':' + ( showVariationImages ? 'v' : '' );
		if ( wrapper.dataset.gallerySource === sourceKey ) return;

		var galleryImages = showVariationImages ? variationImages : galleryData.defaultImages;
		frontend.renderGalleryIfChanged( galleryImages, wrapper );
		wrapper.dataset.gallerySource = sourceKey;
	}

	function applyVariationGallery( form, variationId ) {
		getGalleryWrappersForForm( form ).forEach( function( wrapper ) {
			var galleryData = frontend.getGalleryData( wrapper );
			if ( galleryData ) applyVariationToWrapper( wrapper, galleryData, variationId );
		} );
	}

	function getSelectedAttributes( form ) {
		var attributes = {};
		form.querySelectorAll( 'select[name^="attribute_"]' ).forEach( function( select ) {
			if ( select.value ) {
				attributes[ select.name ] = select.value;
			}
		} );
		form.querySelectorAll( 'input[type="radio"][name^="attribute_"]:checked' ).forEach( function( radio ) {
			if ( radio.value ) {
				attributes[ radio.name ] = radio.value;
			}
		} );
		return attributes;
	}

	function findBestVariationMatch( variations, selectedAttributes ) {
		var selectedKeys = Object.keys( selectedAttributes );
		if ( selectedKeys.length === 0 ) {
			return null;
		}

		var bestVariation = null;
		var bestScore = -1;
		variations.forEach( function( variation ) {
			var attributes = variation.attributes || {};
			var score = 0;
			var disqualified = false;

			selectedKeys.forEach( function( key ) {
				var variationValue = attributes[ key ];
				if ( variationValue === '' || variationValue === undefined ) {
					return;
				}
				if ( variationValue === selectedAttributes[ key ] ) {
					score++;
				} else {
					disqualified = true;
				}
			} );

			if ( ! disqualified && score > bestScore ) {
				bestScore = score;
				bestVariation = variation;
			}
		} );
		return bestVariation;
	}

	function handlePartialVariationChange( form, requirePartialSelection ) {
		var productVariations = jQuery( form ).data( 'product_variations' );
		if ( ! Array.isArray( productVariations ) ) return false;

		var selectedAttributes = getSelectedAttributes( form );
		if ( requirePartialSelection ) {
			var attributeFields = form.querySelectorAll( 'select[name^="attribute_"], input[type="radio"][name^="attribute_"]' );
			var hasUnselectedAttribute = Array.prototype.some.call( attributeFields, function( field ) {
				return ! selectedAttributes[ field.name ];
			} );
			if ( ! productVariations.length || ! Object.keys( selectedAttributes ).length || ! hasUnselectedAttribute ) return false;
		}

		var matchingProductVariation = findBestVariationMatch( productVariations, selectedAttributes );
		var variationId = matchingProductVariation ? matchingProductVariation.variation_id : null;
		applyVariationGallery( form, variationId );
		return true;
	}

	frontend.bindVariationEvents = function() {
		if ( listenersBound ) {
			return;
		}
		listenersBound = true;

		// Woo's local-data change cycle can nest when it clears an incompatible option.
		jQuery( document ).on( 'woocommerce_variation_select_change', '.variations_form', function() {
			var form = this;
			form.kodloVariationChangeDepth = ( form.kodloVariationChangeDepth || 0 ) + 1;
			Promise.resolve().then( function() {
				form.kodloVariationChangeDepth = 0;
			} );
		} );
		jQuery( document ).on( 'woocommerce_variation_has_changed', '.variations_form', function() {
			this.kodloVariationChangeDepth = Math.max( 0, ( this.kodloVariationChangeDepth || 0 ) - 1 );
		} );

		jQuery( document ).on( 'change', '.variations_form select, .variations_form input[type=radio]', function() {
			var form = this.closest( '.variations_form' );
			if ( form ) {
				handlePartialVariationChange( form );
			}
		} );
		jQuery( document ).on( 'found_variation show_variation', '.variations_form', function( event, variation ) {
			if ( variation && variation.variation_id ) {
				applyVariationGallery( this, variation.variation_id );
			}
		} );
		jQuery( document ).on( 'reset_data', '.variations_form', function() {
			// Native incomplete-selection resets must not rebuild default photos before the colour preview.
			if ( this.kodloVariationChangeDepth > 0 && handlePartialVariationChange( this, true ) ) return;
			applyVariationGallery( this, null );
		} );
	};
}( window, document, jQuery ) );

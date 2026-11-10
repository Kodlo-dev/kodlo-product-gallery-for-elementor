/**
 * Deferred images for Kodlo Product Gallery for Elementor.
 *
 * Restores the sources of images printed by the Defer Off-Screen Images option. The server prints mobile slides after
 * the priority images and thumbnails outside the visible row with data-kodlo-src / data-kodlo-srcset instead of
 * src / srcset, so they do not compete with the first image.
 * Desktop grid: restored at once (native lazy loading still applies below the fold).
 * Mobile slider: restored after the page load, or on the first touch, pointer, focus or key inside the gallery.
 * Galleries re-rendered by the frontend controller (variations, breakpoint change) print normal sources.
 */
( function() {
	'use strict';

	var interactionEvents = [ 'touchstart', 'pointerdown', 'focusin', 'keydown' ];

	function restoreImages( wrapper ) {
		Array.prototype.forEach.call( wrapper.querySelectorAll( 'img[data-kodlo-src]' ), function( image ) {
			if ( image.dataset.kodloSrcset ) image.srcset = image.dataset.kodloSrcset;
			image.src = image.dataset.kodloSrc;
			image.removeAttribute( 'data-kodlo-src' );
			image.removeAttribute( 'data-kodlo-srcset' );
		} );
	}

	// Same rule as the frontend controller.
	function isDesktop( wrapper ) {
		if ( wrapper.dataset.desktopViewEnabled === 'no' ) return false;
		if ( wrapper.dataset.mobileViewEnabled === 'no' ) return true;

		var mobileBreakpoint = parseInt( wrapper.dataset.mobileBreakpoint, 10 ) || 768;
		return window.innerWidth >= mobileBreakpoint;
	}

	function whenIdleAfterLoad( callback ) {
		var schedule = function() {
			if ( window.requestIdleCallback ) {
				window.requestIdleCallback( callback, { timeout: 2000 } );
			} else {
				setTimeout( callback, 200 );
			}
		};

		if ( document.readyState === 'complete' ) {
			schedule();
		} else {
			window.addEventListener( 'load', schedule, { once: true } );
		}
	}

	function prepareGallery( wrapper ) {
		if ( wrapper.kodloDeferredImagesBound || ! wrapper.querySelector( 'img[data-kodlo-src]' ) ) return;
		wrapper.kodloDeferredImagesBound = true;

		if ( isDesktop( wrapper ) ) {
			restoreImages( wrapper );
			return;
		}

		var restore = function() {
			interactionEvents.forEach( function( type ) {
				wrapper.removeEventListener( type, restore, true );
			} );
			restoreImages( wrapper );
		};

		interactionEvents.forEach( function( type ) {
			wrapper.addEventListener( type, restore, { capture: true, passive: true } );
		} );
		whenIdleAfterLoad( restore );
	}

	function boot() {
		Array.prototype.forEach.call( document.querySelectorAll( '.kodlo-gallery-wrapper[data-deferred-images="yes"]' ), prepareGallery );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', boot );
	} else {
		boot();
	}
} )();

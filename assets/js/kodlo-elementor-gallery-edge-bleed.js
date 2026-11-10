/* Manages mobile swipe edge bleed for Kodlo Product Gallery for Elementor. */
( function() {
	'use strict';

	var boundSwipers = typeof WeakSet === 'function' ? new WeakSet() : null;

	function isEnabled( wrapper ) {
		return wrapper && wrapper.dataset.mobileEdgeBleed === 'yes';
	}

	function bindSwiper( swiper, wrapper ) {
		if ( ! swiper || ! isEnabled( wrapper ) || ( boundSwipers && boundSwipers.has( swiper ) ) ) return;

		var slider = swiper.el;
		var releaseTimer = null;

		if ( ! slider ) return;
		if ( boundSwipers ) boundSwipers.add( swiper );

		function activate() {
			if ( releaseTimer ) clearTimeout( releaseTimer );
			slider.classList.add( 'kodlo-edge-bleed-active' );
		}

		function deactivate() {
			if ( releaseTimer ) clearTimeout( releaseTimer );
			releaseTimer = null;
			slider.classList.remove( 'kodlo-edge-bleed-active' );
		}

		function deactivateAfterTouch() {
			if ( releaseTimer ) clearTimeout( releaseTimer );
			releaseTimer = setTimeout( function() {
				if ( ! swiper.animating ) deactivate();
			}, 0 );
		}

		swiper.on( 'sliderFirstMove', activate );
		swiper.on( 'touchEnd', deactivateAfterTouch );
		swiper.on( 'transitionEnd', deactivate );
		swiper.on( 'destroy', deactivate );
	}

	window.kodloGalleryEdgeBleed = {
		bindSwiper: bindSwiper,
	};
}() );

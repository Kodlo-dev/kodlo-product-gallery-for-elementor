/**
 * Magnifier interaction module for Kodlo Product Gallery for Elementor.
 * Desktop uses pointer hover; mobile uses an explicit toggle and drag mode.
 */
( function() {
	'use strict';

	var activeState = null;
	var l10n = window.kodloGalleryMagnifierL10n || {};

	function isMobileItem( mediaItem ) {
		var wrapper = mediaItem.closest( '.kodlo-gallery-wrapper' );
		if ( ! wrapper ) return false;
		return wrapper.dataset.appliedDevice === 'mobile';
	}

	function getEffect( mediaItem ) {
		var wrapper = mediaItem.closest( '.kodlo-gallery-wrapper' );
		if ( ! wrapper ) return 'none';
		return isMobileItem( mediaItem )
			? wrapper.dataset.mobileImageEffect || 'none'
			: wrapper.dataset.desktopImageEffect || 'none';
	}

	function isInMagnifierScope( mediaItem ) {
		var wrapper = mediaItem.closest( '.kodlo-gallery-wrapper' );
		if ( ! wrapper ) return false;
		var scope = isMobileItem( mediaItem )
			? wrapper.dataset.mobileMagnifierScope
			: wrapper.dataset.desktopMagnifierScope;
		if ( scope !== 'first' ) return true;

		// Swiper can reorder or clone slides; use their logical gallery index.
		var firstImage = null;
		var firstIndex = Infinity;
		wrapper.querySelectorAll( '.kodlo-gallery-main-wrapper .kodlo-media-item:not(.has-video)[data-full-image]' ).forEach( function( item, position ) {
			if ( ! item.dataset.fullImage ) return;
			var slide = item.closest( '.kodlo-gallery-main-slide' );
			var index = slide ? parseInt( slide.dataset.index, 10 ) : position;
			if ( ! Number.isFinite( index ) ) index = position;
			if ( index < firstIndex ) {
				firstImage = item;
				firstIndex = index;
			}
		} );
		var currentSlide = mediaItem.closest( '.kodlo-gallery-main-slide' );
		var currentIndex = currentSlide ? parseInt( currentSlide.dataset.index, 10 ) : NaN;
		return Number.isFinite( currentIndex ) ? currentIndex === firstIndex : mediaItem === firstImage;
	}

	function isEligible( mediaItem ) {
		return !! mediaItem
			&& ! mediaItem.classList.contains( 'has-video' )
			&& getEffect( mediaItem ) === 'magnifier'
			&& !! mediaItem.dataset.fullImage
			&& isInMagnifierScope( mediaItem );
	}

	function getToggle( mediaItem ) {
		return mediaItem.querySelector( '.kodlo-magnifier-toggle' );
	}

	function setToggleState( mediaItem, enabled ) {
		var toggle = getToggle( mediaItem );
		if ( ! toggle ) return;
		toggle.setAttribute( 'aria-pressed', enabled ? 'true' : 'false' );
		toggle.setAttribute( 'aria-label', enabled
			? l10n.disable || 'Disable image magnifier'
			: l10n.enable || 'Enable image magnifier'
		);
	}

	function close( scope ) {
		if ( ! activeState ) return;
		if ( scope && ! scope.contains( activeState.mediaItem ) ) return;

		var mediaItem = activeState.mediaItem;
		if ( activeState.positionFrame ) window.cancelAnimationFrame( activeState.positionFrame );
		if ( activeState.preloader ) {
			activeState.preloader.onload = null;
			activeState.preloader.onerror = null;
		}
		if ( activeState.lens ) activeState.lens.remove();

		mediaItem.classList.remove( 'kodlo-magnifier-active', 'kodlo-magnifier-loading' );
		setToggleState( mediaItem, false );
		activeState = null;
	}

	function getPositionCache() {
		if ( ! activeState || ! activeState.lens || ! activeState.lensImage ) return null;
		if ( activeState.positionCache ) return activeState.positionCache;

		var mediaItem = activeState.mediaItem;
		var mediaItemRect = mediaItem.getBoundingClientRect();
		if ( mediaItemRect.width <= 0 || mediaItemRect.height <= 0 ) return null;

		var mediaItemStyle = window.getComputedStyle( mediaItem );
		var configuredSize = parseFloat( mediaItemStyle.getPropertyValue( '--kodlo-magnifier-size' ) ) || 160;
		var lensSize = Math.max( 60, Math.min( configuredSize, mediaItemRect.width, mediaItemRect.height ) );
		var zoomLevel = parseFloat( mediaItemStyle.getPropertyValue( '--kodlo-magnifier-zoom' ) ) || 2;
		var sourceImage = mediaItem.querySelector( '.kodlo-main-image' );
		var sourceImageStyle = sourceImage ? window.getComputedStyle( sourceImage ) : null;

		activeState.positionCache = {
			rect: mediaItemRect,
			lensSize: lensSize,
			zoomLevel: zoomLevel
		};
		activeState.lens.style.width = lensSize + 'px';
		activeState.lens.style.height = lensSize + 'px';
		activeState.lensImage.style.width = mediaItemRect.width * zoomLevel + 'px';
		activeState.lensImage.style.height = mediaItemRect.height * zoomLevel + 'px';
		if ( sourceImageStyle ) {
			activeState.lensImage.style.objectFit = sourceImageStyle.objectFit;
			activeState.lensImage.style.objectPosition = sourceImageStyle.objectPosition;
		}

		return activeState.positionCache;
	}

	function positionLens( clientX, clientY ) {
		if ( ! activeState ) return;
		activeState.clientX = clientX;
		activeState.clientY = clientY;

		var positionCache = getPositionCache();
		if ( ! positionCache ) return;

		var mediaItemRect = positionCache.rect;
		var lensSize = positionCache.lensSize;
		var pointerX = Math.max( 0, Math.min( clientX - mediaItemRect.left, mediaItemRect.width ) );
		var pointerY = Math.max( 0, Math.min( clientY - mediaItemRect.top, mediaItemRect.height ) );
		var lensLeft = Math.max( 0, Math.min( pointerX - lensSize / 2, mediaItemRect.width - lensSize ) );
		var lensTop = Math.max( 0, Math.min( pointerY - lensSize / 2, mediaItemRect.height - lensSize ) );
		var localX = pointerX - lensLeft;
		var localY = pointerY - lensTop;
		activeState.lens.style.left = lensLeft + 'px';
		activeState.lens.style.top = lensTop + 'px';
		activeState.lensImage.style.left = localX - pointerX * positionCache.zoomLevel + 'px';
		activeState.lensImage.style.top = localY - pointerY * positionCache.zoomLevel + 'px';
	}

	function scheduleLensPosition( clientX, clientY ) {
		if ( ! activeState ) return;
		activeState.clientX = clientX;
		activeState.clientY = clientY;
		if ( activeState.positionFrame ) return;

		var scheduledState = activeState;
		activeState.positionFrame = window.requestAnimationFrame( function() {
			if ( activeState !== scheduledState ) return;
			activeState.positionFrame = 0;
			positionLens( activeState.clientX, activeState.clientY );
		} );
	}

	function invalidatePositionCache() {
		if ( ! activeState ) return;
		activeState.positionCache = null;
		scheduleLensPosition( activeState.clientX, activeState.clientY );
	}

	function open( mediaItem, clientX, clientY ) {
		if ( ! isEligible( mediaItem ) ) return;
		if ( activeState && activeState.mediaItem === mediaItem ) {
			if ( activeState.lens ) positionLens( clientX, clientY );
			return;
		}

		close();

		var preloader = new Image();
		activeState = {
			mediaItem: mediaItem,
			preloader: preloader,
			lens: null,
			lensImage: null,
			clientX: clientX,
			clientY: clientY,
			dragging: false,
			positionCache: null,
			positionFrame: 0
		};

		mediaItem.classList.add( 'kodlo-magnifier-loading' );
		setToggleState( mediaItem, true );

		preloader.alt = '';
		preloader.setAttribute( 'aria-hidden', 'true' );
		preloader.decoding = 'async';
		preloader.onload = function() {
			if ( ! activeState || activeState.preloader !== preloader ) return;
			if ( ! mediaItem.isConnected ) {
				close();
				return;
			}

			var lens = document.createElement( 'span' );
			lens.className = 'kodlo-magnifier-lens';
			lens.setAttribute( 'aria-hidden', 'true' );
			preloader.className = 'kodlo-magnifier-lens-image';
			lens.appendChild( preloader );
			mediaItem.appendChild( lens );

			activeState.lens = lens;
			activeState.lensImage = preloader;
			mediaItem.classList.remove( 'kodlo-magnifier-loading' );
			mediaItem.classList.add( 'kodlo-magnifier-active' );
			positionLens( activeState.clientX, activeState.clientY );
		};
		preloader.onerror = function() {
			if ( activeState && activeState.preloader === preloader ) close();
		};
		preloader.src = mediaItem.dataset.fullImage;
	}

	function createToggle( mediaItem ) {
		var button = document.createElement( 'button' );
		var wrapper = mediaItem.closest( '.kodlo-gallery-wrapper' );
		var iconHtml = wrapper && wrapper.dataset.mobileMagnifierIcon;
		var activeIconHtml = wrapper && wrapper.dataset.mobileMagnifierActiveIcon;
		var defaultIcon = '<svg class="kodlo-magnifier-default-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">'
			+ '<circle cx="10.5" cy="10.5" r="6.5"></circle>'
			+ '<path d="M15.5 15.5 21 21"></path>'
			+ '<path d="M10.5 7.5v6M7.5 10.5h6"></path>'
			+ '</svg>';
		var defaultActiveIcon = '<svg class="kodlo-magnifier-default-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">'
			+ '<circle cx="10.5" cy="10.5" r="6.5"></circle>'
			+ '<path d="M15.5 15.5 21 21"></path>'
			+ '<path d="M7.5 10.5h6"></path>'
			+ '</svg>';
		button.type = 'button';
		button.className = 'kodlo-magnifier-toggle';
		button.setAttribute( 'aria-label', l10n.enable || 'Enable image magnifier' );
		button.setAttribute( 'aria-pressed', 'false' );
		button.innerHTML = '<span class="kodlo-magnifier-toggle-icon kodlo-magnifier-toggle-icon-inactive" aria-hidden="true">'
			+ ( iconHtml || defaultIcon )
			+ '</span><span class="kodlo-magnifier-toggle-icon kodlo-magnifier-toggle-icon-active" aria-hidden="true">'
			+ ( activeIconHtml || defaultActiveIcon )
			+ '</span>';
		return button;
	}

	function refresh( wrapper ) {
		if ( ! wrapper ) return;
		close( wrapper );

		Array.prototype.slice.call( wrapper.querySelectorAll( '.kodlo-magnifier-toggle' ) ).forEach( function( button ) {
			button.remove();
		} );

		if ( wrapper.dataset.appliedDevice !== 'mobile'
			|| ( wrapper.dataset.mobileImageEffect || 'none' ) !== 'magnifier' ) return;

		Array.prototype.slice.call(
			wrapper.querySelectorAll( '.kodlo-mobile-slider .kodlo-media-item:not(.has-video)' )
		).forEach( function( mediaItem ) {
			if ( isEligible( mediaItem ) ) mediaItem.appendChild( createToggle( mediaItem ) );
		} );
	}

	document.addEventListener( 'pointerover', function( event ) {
		var mediaItem = event.target.closest && event.target.closest( '.kodlo-gallery-main-wrapper .kodlo-media-item' );
		if ( ! mediaItem || ( event.pointerType !== 'mouse' && event.pointerType !== 'pen' ) ) return;
		if ( mediaItem.contains( event.relatedTarget ) ) return;
		if ( isEligible( mediaItem ) ) open( mediaItem, event.clientX, event.clientY );
	} );

	document.addEventListener( 'pointermove', function( event ) {
		if ( ! activeState ) return;
		if ( activeState.dragging || ! isMobileItem( activeState.mediaItem ) ) {
			scheduleLensPosition( event.clientX, event.clientY );
		}
	}, true );

	window.addEventListener( 'resize', invalidatePositionCache );
	window.addEventListener( 'scroll', invalidatePositionCache, true );

	document.addEventListener( 'pointerout', function( event ) {
		if ( ! activeState || isMobileItem( activeState.mediaItem ) ) return;
		if ( activeState.mediaItem.contains( event.relatedTarget ) ) return;
		close();
	} );

	document.addEventListener( 'pointerdown', function( event ) {
		if ( ! activeState || activeState.mediaItem !== event.target.closest( '.kodlo-media-item' ) ) return;
		if ( event.target.closest( '.kodlo-magnifier-toggle' ) ) return;
		if ( ! isMobileItem( activeState.mediaItem ) ) return;

		event.preventDefault();
		event.stopPropagation();
		activeState.dragging = true;
		positionLens( event.clientX, event.clientY );
		if ( activeState.mediaItem.setPointerCapture ) {
			activeState.mediaItem.setPointerCapture( event.pointerId );
		}
	}, true );

	document.addEventListener( 'pointerup', function( event ) {
		if ( ! activeState || ! activeState.dragging ) return;
		event.preventDefault();
		event.stopPropagation();
		activeState.dragging = false;
	}, true );

	document.addEventListener( 'pointercancel', function() {
		if ( activeState ) activeState.dragging = false;
	}, true );

	document.addEventListener( 'click', function( event ) {
		var toggle = event.target.closest && event.target.closest( '.kodlo-magnifier-toggle' );
		if ( toggle ) {
			event.preventDefault();
			event.stopPropagation();
			var mediaItem = toggle.closest( '.kodlo-media-item' );
			if ( activeState && activeState.mediaItem === mediaItem ) {
				close();
			} else {
				var rect = mediaItem.getBoundingClientRect();
				open( mediaItem, rect.left + rect.width / 2, rect.top + rect.height / 2 );
			}
			return;
		}

		if ( activeState && isMobileItem( activeState.mediaItem ) ) {
			var link = event.target.closest && event.target.closest( '.kodlo-media-link' );
			if ( link && activeState.mediaItem.contains( link ) ) {
				event.preventDefault();
				event.stopPropagation();
			} else if ( ! activeState.mediaItem.contains( event.target ) ) {
				close();
			}
		}
	}, true );

	document.addEventListener( 'contextmenu', function( event ) {
		if ( activeState && activeState.mediaItem.contains( event.target ) ) event.preventDefault();
	} );

	document.addEventListener( 'keydown', function( event ) {
		if ( event.key === 'Escape' ) close();
	} );

	window.kodloGalleryMagnifier = {
		refresh: refresh,
		close: close,
		destroy: function( wrapper ) {
			close( wrapper );
			if ( wrapper ) {
				Array.prototype.slice.call( wrapper.querySelectorAll( '.kodlo-magnifier-toggle' ) ).forEach( function( button ) {
					button.remove();
				} );
			}
		}
	};

} )();

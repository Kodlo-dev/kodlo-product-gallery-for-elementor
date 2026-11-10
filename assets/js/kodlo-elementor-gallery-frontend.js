/**
 * Frontend controller for Kodlo Product Gallery for Elementor.
 * Coordinates the gallery renderer, Swiper lifecycle, responsive state, and integrations.
 * Functions follow runtime ownership: shared helpers, Swiper, cleanup, rendering, and lifecycle.
 */
( function() {
	'use strict';

	var swiperInstances = {};
	var initializedWrappers = [];
	var instanceCounter = 0;
	var cleanupScheduled = false;
	var galleryRemovalObserver = null;
	var registeredElementorHooks = null;
	var l10n = window.kodloGalleryFrontendL10n || {};
	var frontend = window.kodloGalleryFrontend || {};

	function refreshMagnifier( wrapper ) {
		if ( window.kodloGalleryMagnifier ) window.kodloGalleryMagnifier.refresh( wrapper );
	}

	function closeMagnifier( wrapper ) {
		if ( window.kodloGalleryMagnifier ) window.kodloGalleryMagnifier.close( wrapper );
	}

	function destroyMagnifier( wrapper ) {
		if ( wrapper && window.kodloGalleryMagnifier ) window.kodloGalleryMagnifier.destroy( wrapper );
	}

	function getInstanceKey( wrapper ) {
		if ( ! wrapper.kodloInstanceKey ) {
			var galleryId = wrapper.dataset.galleryId || 'gallery';
			wrapper.kodloInstanceKey = galleryId + '-' + ( ++instanceCounter );
		}

		return wrapper.kodloInstanceKey;
	}

	// Returns all matching elements as an array.
	function queryAll( selector, root ) {
		return Array.prototype.slice.call( ( root || document ).querySelectorAll( selector ) );
	}

	function getWidgetProperty( wrapper, propertyName, fallback ) {
		var value = window.getComputedStyle( wrapper ).getPropertyValue( propertyName );
		return ( value ? value.trim() : '' ) || fallback;
	}

	function normalizeAspectRatio( value ) {
		var allowedValues = [ '1 / 1', '4 / 5', '5 / 6', '3 / 4', '2 / 3', '9 / 16', '5 / 4', '6 / 5', '4 / 3', '3 / 2', '16 / 9', '21 / 9' ];
		return allowedValues.indexOf( value ) > -1 ? value : '16 / 9';
	}

	function normalizeObjectFit( value ) {
		return [ 'cover', 'contain', 'fill' ].indexOf( value ) > -1 ? value : 'cover';
	}

	function normalizeStretchSetting( value ) {
		return [ 'no', 'last', 'first' ].indexOf( value ) > -1 ? value : 'no';
	}

	function updateThumbnailState( wrapper ) {
		queryAll( '.kodlo-thumbs-slider .kodlo-thumb-button', wrapper ).forEach( function( button ) {
			var slide = button.closest( '.swiper-slide' );
			if ( slide && slide.classList.contains( 'swiper-slide-thumb-active' ) ) {
				button.setAttribute( 'aria-current', 'true' );
			} else {
				button.removeAttribute( 'aria-current' );
			}
		} );
	}

	function getMaxVisibleDots( wrapper ) {
		return Math.max( 2, Math.min( 10, parseInt( wrapper.dataset.maxVisibleDots || 4, 10 ) || 4 ) );
	}

	function bindEdgeBleed( swiper, wrapper ) {
		var edgeBleed = window.kodloGalleryEdgeBleed;
		if ( edgeBleed && typeof edgeBleed.bindSwiper === 'function' ) {
			edgeBleed.bindSwiper( swiper, wrapper );
		}
	}

	function applyNavigationPositions( wrapper ) {
		var mainWrap = wrapper.querySelector( '.kodlo-main-slider-wrap' );
		var thumbsWrap = wrapper.querySelector( '.kodlo-thumbs-slider-wrap' );
		var mainPosition = getWidgetProperty(
			wrapper,
			'--kodlo-main-nav-position',
			wrapper.dataset.mainNavPosition || 'overlay'
		);
		var thumbsPosition = getWidgetProperty(
			wrapper,
			'--kodlo-thumbs-nav-position',
			wrapper.dataset.thumbsNavPosition || 'overlay'
		);

		if ( mainWrap ) mainWrap.classList.toggle( 'kodlo-nav-overlay', 'overlay' === mainPosition );
		if ( thumbsWrap ) thumbsWrap.classList.toggle( 'kodlo-nav-overlay', 'overlay' === thumbsPosition );
	}

	// Keeps a fixed-size, clickable pagination window around the active bullet.
	function updateLimitedPagination( swiper, maxVisibleDots ) {
		if ( ! swiper || ! swiper.el ) return;

		var bullets    = queryAll( '.swiper-pagination-bullet', swiper.el );
		var total      = bullets.length;
		var pagination = swiper.el.querySelector( '.swiper-pagination' );

		if ( total === 0 ) return;

		var activeIndex = bullets.findIndex( function( bullet ) {
			return bullet.classList.contains( 'swiper-pagination-bullet-active' );
		} );
		var start = parseInt( swiper.kodloPaginationStart || 0, 10 );

		if ( activeIndex < 0 ) activeIndex = swiper.realIndex || 0;

		if ( total <= maxVisibleDots ) {
			start = 0;
		} else {
			if ( activeIndex >= start + maxVisibleDots - 1 ) {
				start = activeIndex - maxVisibleDots + 2;
			} else if ( activeIndex <= start ) {
				start = activeIndex - 1;
			}

			start = Math.max( 0, Math.min( start, total - maxVisibleDots ) );
		}

		swiper.kodloPaginationStart = start;

		bullets.forEach( function( bullet, index ) {
			var isOutsideWindow = index < start || index >= start + maxVisibleDots;
			bullet.classList.toggle( 'kodlo-pagination-bullet-hidden', isOutsideWindow );

			if ( isOutsideWindow ) {
				bullet.setAttribute( 'aria-hidden', 'true' );
				bullet.setAttribute( 'tabindex', '-1' );
			} else {
				bullet.removeAttribute( 'aria-hidden' );
				bullet.removeAttribute( 'tabindex' );
			}
		} );

		var visibleCount = Math.min( total, maxVisibleDots );
		var bulletStyle = window.getComputedStyle( bullets[0] );
		var bulletWidth = bullets[0].getBoundingClientRect().width;
		var bulletStep = bulletWidth
			+ ( parseFloat( bulletStyle.marginLeft ) || 0 )
			+ ( parseFloat( bulletStyle.marginRight ) || 0 );

		if ( pagination && bulletStep > 0 ) {
			pagination.classList.toggle( 'kodlo-pagination-limited', total > maxVisibleDots );
			pagination.classList.toggle( 'kodlo-pagination-at-start', start === 0 );
			pagination.classList.toggle( 'kodlo-pagination-at-end', start === total - visibleCount );
			pagination.style.setProperty( '--kodlo-pagination-window-width', ( visibleCount * bulletStep ) + 'px' );
			pagination.style.setProperty( '--kodlo-pagination-offset', ( -start * bulletStep ) + 'px' );
		}
	}

	function resetOpenMobileVideos( wrapper ) {
		queryAll(
			'.kodlo-mobile-slider .kodlo-video-container.is-loading, .kodlo-mobile-slider .kodlo-video-container.is-ready',
			wrapper
		).forEach( function( videoContainer ) {
			frontend.resetVideoItem( videoContainer.closest( '.kodlo-media-item' ) );
		} );
	}

	// Overrides Elementor container styles to display the desktop gallery grid.
	function applyDesktopGrid( root ) {
		queryAll( '.kodlo-gallery-main-wrapper', root ).forEach( function( grid ) {
			grid.style.display = '';
		} );
	}

	// Initializes the mobile Swiper slider and thumbnail carousel.
	function initMobileSwiper( wrapper, retryCount ) {
		retryCount = retryCount || 0;

		if ( ! wrapper || ! wrapper.isConnected ) {
			return;
		}

		var SwiperClass = window.Swiper;
		if ( typeof SwiperClass !== 'function' ) {
			if ( retryCount < 10 ) {
				setTimeout( function() {
					initMobileSwiper( wrapper, retryCount + 1 );
				}, 200 );
			}
			return;
		}

		var instanceKey = getInstanceKey( wrapper );
		var mainSlider  = wrapper.querySelector( '.kodlo-mobile-slider' );
		var thumbSlider = wrapper.querySelector( '.kodlo-thumbs-slider' );

		if ( ! mainSlider ) return;

		// Clean up hidden mobile slides from the DOM so Swiper doesn't count them in layout
		queryAll( '.kodlo-hide-mobile', wrapper ).forEach( function( hiddenSlide ) {
			hiddenSlide.remove();
		} );

		destroyMobileSwiper( instanceKey );
		refreshMagnifier( wrapper );
		applyNavigationPositions( wrapper );

		var widgetThumbGap = parseFloat( window.getComputedStyle( wrapper ).getPropertyValue( '--kodlo-thumb-gap' ) );
		var thumbGap       = Number.isNaN( widgetThumbGap )
			? parseInt( wrapper.dataset.thumbGap || 0, 10 )
			: widgetThumbGap;
		var showPagination    = wrapper.dataset.showPagination === 'yes';
		var isAutoplay        = frontend.isAutoplayEnabled( wrapper, 'mobile' );
		var thumbsPerView     = getWidgetProperty( wrapper, '--kodlo-thumbs-per-view', wrapper.dataset.thumbsPerView || '4' );
		var slidesPerViewVal  = thumbsPerView === 'auto' ? 'auto' : parseFloat( thumbsPerView );
		var loopMobile        = wrapper.dataset.loopMobile === 'yes';
		var reduceMotion      = !! ( window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches );
		var isEditMode        = !! ( window.elementorFrontend && window.elementorFrontend.isEditMode() );
		var mainPreviousButton = wrapper.querySelector( '.kodlo-main-nav-prev' );
		var mainNextButton = wrapper.querySelector( '.kodlo-main-nav-next' );
		var thumbnailPreviousButton = wrapper.querySelector( '.kodlo-thumbs-nav-prev' );
		var thumbnailNextButton = wrapper.querySelector( '.kodlo-thumbs-nav-next' );

		var thumbSwiper = null;
		if ( thumbSlider ) {
			var thumbOptions = {
				slidesPerView       : slidesPerViewVal,
				spaceBetween        : thumbGap,
				watchSlidesProgress : true,
				slideToClickedSlide : true,
				watchOverflow       : true,
				speed               : reduceMotion ? 0 : 300,
				a11y                : {
					enabled          : true,
					prevSlideMessage : l10n.previous || 'Previous',
					nextSlideMessage : l10n.next || 'Next',
					containerMessage : l10n.thumbnail || 'Thumbnail',
				},
			};

			if ( thumbnailPreviousButton && thumbnailNextButton ) {
				thumbOptions.navigation = { prevEl: thumbnailPreviousButton, nextEl: thumbnailNextButton };
			}
			if ( ! isEditMode ) {
				thumbOptions.preloadImages = false;
				thumbOptions.updateOnImagesReady = false;
			}

			thumbSwiper = new SwiperClass( thumbSlider, thumbOptions );
			// Swiper 8 adds "swiper-initialized" but not "swiper-container-initialized"
			// (Swiper 6 name). Elementor Pro's widget-nested-carousel.min.css targets
			// .swiper-container:not(.swiper-container-initialized) and sets overflow:hidden,
			// clipping off-screen slides. Add the legacy class to neutralize that rule.
			thumbSlider.classList.add( 'swiper-container-initialized' );
			bindEdgeBleed( thumbSwiper, wrapper );
		}

		var mainOptions = {
			loop         : loopMobile,
			spaceBetween : 10,
			watchOverflow: true,
			speed        : reduceMotion ? 0 : 300,
			a11y         : {
				enabled                 : true,
				prevSlideMessage        : l10n.previous || 'Previous',
				nextSlideMessage        : l10n.next || 'Next',
				paginationBulletMessage : String( l10n.viewImage || 'View image %d' ).replace( '%d', '{{index}}' ),
				containerMessage        : l10n.gallery || 'Gallery',
			},
			on           : {
				slideChange: function( swiper ) {
					requestAnimationFrame( function() {
						updateLimitedPagination( swiper, getMaxVisibleDots( wrapper ) );
						updateThumbnailState( wrapper );
					} );
				},
				resize: function( swiper ) {
					updateLimitedPagination( swiper, getMaxVisibleDots( wrapper ) );
				},
				slideChangeTransitionStart: function() {
					closeMagnifier( wrapper );
					resetOpenMobileVideos( wrapper );
				},
				slideChangeTransitionEnd: function() {
					if ( isAutoplay ) {
						var activeVideoButton = wrapper.querySelector( '.swiper-slide-active .kodlo-video-play-btn' );
						if ( activeVideoButton ) activeVideoButton.click();
					}
				}
			}
		};

		if ( thumbSwiper ) {
			mainOptions.thumbs = { swiper: thumbSwiper };
		}

		if ( showPagination ) {
			mainOptions.pagination = {
				el        : mainSlider.querySelector( '.swiper-pagination' ),
				type      : 'bullets',
				clickable : true,
				renderBullet: function( index, className ) {
					var viewImageLabel = String( l10n.viewImage || 'View image %d' ).replace( '%d', index + 1 );
					return '<button type="button" class="' + className + '" aria-label="' + frontend.escapeAttribute( viewImageLabel ) + '"></button>';
				}
			};
		}

		if ( mainPreviousButton && mainNextButton ) {
			mainOptions.navigation = { prevEl: mainPreviousButton, nextEl: mainNextButton };
		}

		// Swiper 8 preloads every slide image by default, which defeats lazy loading; slide sizes come from aspect-ratio
		// boxes, so the frontend does not need it. The editor keeps it so live previews settle after setting changes.
		mainOptions.preloadImages = isEditMode;
		mainOptions.updateOnImagesReady = isEditMode;

		var mainSwiper = new SwiperClass( mainSlider, mainOptions );
		mainSlider.classList.add( 'swiper-container-initialized' );
		bindEdgeBleed( mainSwiper, wrapper );
		requestAnimationFrame( function() {
			if ( ! wrapper.isConnected || ! swiperInstances[ instanceKey ] ) return;
			updateLimitedPagination( mainSwiper, getMaxVisibleDots( wrapper ) );
			updateThumbnailState( wrapper );
		} );
		if ( window.elementorFrontend && window.elementorFrontend.isEditMode() ) {
			refreshMagnifier( wrapper );
		}

		swiperInstances[ instanceKey ] = { main: mainSwiper, thumbs: thumbSwiper, wrapper: wrapper };
		if ( isAutoplay ) {
			setTimeout( function() {
				if ( ! wrapper.isConnected || ! swiperInstances[ instanceKey ] ) return;
				var activeVideoButton = wrapper.querySelector( '.swiper-slide-active .kodlo-video-play-btn' );
				if ( activeVideoButton ) activeVideoButton.click();
			}, 150 );
		}
	}

	// Destroys the active Swiper instances for a product.
	function destroyMobileSwiper( instanceKey ) {
		var instance = swiperInstances[ instanceKey ];
		if ( ! instance ) return;
		if ( instance.main )   instance.main.destroy( true, true );
		if ( instance.thumbs ) instance.thumbs.destroy( true, true );
		delete swiperInstances[ instanceKey ];
	}

	function cleanupDisconnectedGalleries() {
		initializedWrappers = initializedWrappers.filter( function( wrapper ) {
			if ( wrapper && wrapper.isConnected ) {
				return true;
			}

			if ( wrapper && wrapper.kodloInstanceKey ) {
				destroyMobileSwiper( wrapper.kodloInstanceKey );
			}
			destroyMagnifier( wrapper );
			return false;
		} );

		Object.keys( swiperInstances ).forEach( function( instanceKey ) {
			var instance = swiperInstances[ instanceKey ];
			if ( ! instance.wrapper || ! instance.wrapper.isConnected ) {
				destroyMagnifier( instance.wrapper );
				destroyMobileSwiper( instanceKey );
			}
		} );
	}

	function scheduleDisconnectedCleanup() {
		if ( cleanupScheduled ) return;
		cleanupScheduled = true;
		requestAnimationFrame( function() {
			cleanupScheduled = false;
			cleanupDisconnectedGalleries();
		} );
	}

	function removedNodeContainsGallery( removedNode ) {
		if ( ! removedNode ) return false;
		if ( typeof removedNode.matches === 'function' && removedNode.matches( '.kodlo-gallery-wrapper' ) ) return true;
		return typeof removedNode.querySelector === 'function' && !! removedNode.querySelector( '.kodlo-gallery-wrapper' );
	}

	function observeGalleryRemovals() {
		if ( galleryRemovalObserver || ! window.MutationObserver || ! document.body ) return;

		galleryRemovalObserver = new window.MutationObserver( function( mutations ) {
			var galleryWasRemoved = mutations.some( function( mutation ) {
				return Array.prototype.some.call( mutation.removedNodes || [], removedNodeContainsGallery );
			} );
			if ( galleryWasRemoved ) scheduleDisconnectedCleanup();
		} );
		galleryRemovalObserver.observe( document.body, { childList: true, subtree: true } );
	}

	// Checks if the current viewport is in desktop range.
	function isDesktop( wrapper ) {
		if ( wrapper.dataset.desktopViewEnabled === 'no' ) return false;
		if ( wrapper.dataset.mobileViewEnabled === 'no' ) return true;

		var mobileBreakpoint = parseInt( wrapper.dataset.mobileBreakpoint, 10 ) || 768;
		return window.innerWidth >= mobileBreakpoint;
	}

	function getMaxImages( wrapper ) {
		var value = isDesktop( wrapper )
			? wrapper.dataset.renderedDesktopMax
			: wrapper.dataset.renderedMobileMax;

		return Math.max( 0, parseInt( value || 0, 10 ) || 0 );
	}

	function getDesktopColumnCount( wrapper ) {
		var computed = window.getComputedStyle( wrapper ).getPropertyValue( '--kodlo-columns' );
		var columns  = parseInt( computed, 10 );
		return Number.isFinite( columns ) && columns > 0 ? columns : parseInt( wrapper.dataset.desktopColumns || 2, 10 );
	}

	function updateDesktopGridLayout( wrapper ) {
		var items = queryAll( '.kodlo-gallery-main-wrapper .kodlo-gallery-main-slide', wrapper );
		if ( items.length === 0 ) return;

		items.forEach( function( item ) {
			item.style.removeProperty( 'grid-column' );
			if ( ! item.style.cssText ) item.removeAttribute( 'style' );

			var mediaItem = item.querySelector( '.kodlo-media-item' );
			if ( mediaItem ) {
				mediaItem.style.removeProperty( '--kodlo-aspect-ratio' );
				mediaItem.style.removeProperty( '--kodlo-object-fit' );
				if ( ! mediaItem.style.cssText ) mediaItem.removeAttribute( 'style' );
			}
		} );

		var columns = getDesktopColumnCount( wrapper );
		var stretchSetting = normalizeStretchSetting( getWidgetProperty(
			wrapper,
			'--kodlo-stretched-image',
			wrapper.dataset.lastImageFullWidth || 'no'
		) );
		if ( stretchSetting !== 'no' && items.length % columns !== 0 ) {
			var targetItem = stretchSetting === 'first' ? items[ 0 ] : items[ items.length - 1 ];
			if ( targetItem ) {
				targetItem.style.gridColumn = '1 / -1';
				var targetMedia = targetItem.querySelector( '.kodlo-media-item' );
				if ( targetMedia ) {
					targetMedia.style.setProperty(
						'--kodlo-aspect-ratio',
						normalizeAspectRatio( getWidgetProperty(
							wrapper,
							'--kodlo-last-image-aspect-ratio',
							wrapper.dataset.lastImageAspectRatio || '16 / 9'
						) )
					);
					targetMedia.style.setProperty(
						'--kodlo-object-fit',
						normalizeObjectFit( getWidgetProperty(
							wrapper,
							'--kodlo-last-image-object-fit',
							wrapper.dataset.lastImageObjectFit || 'cover'
						) )
					);
				}
			}
		}
	}

	// Triggers autoplay for the first video in the desktop grid view.
	function triggerAutoplayDesktop( wrapper ) {
		if ( frontend.isAutoplayEnabled( wrapper, 'desktop' ) ) {
			var videoButton = wrapper.querySelector( '.kodlo-gallery-main-wrapper .kodlo-video-play-btn' );
			if ( videoButton ) videoButton.click();
		}
	}

	function getVisibleImagesFingerprint( images, maxImages ) {
		var visibleImages = maxImages > 0 ? images.slice( 0, maxImages ) : images;

		return JSON.stringify( visibleImages.map( function( image ) {
			return [
				String( image.id || 0 ),
				image.medium || '',
				image.full || '',
				image.thumb || '',
				image.video || '',
			];
		} ) );
	}

	// Re-renders the gallery with a new set of images.
	function renderGallery( galleryImages, wrapper, galleryFingerprint ) {
		var instanceKey = getInstanceKey( wrapper );
		var mainWrapper = wrapper.querySelector( '.kodlo-gallery-main-wrapper' );
		var thumbWrapper = wrapper.querySelector( '.kodlo-thumbs-slider .swiper-wrapper' );
		var videoIconHtml = wrapper.dataset.videoIcon || '';

		var maxImages = getMaxImages( wrapper );
		var imageOptimizationEnabled = wrapper.dataset.imgOptimization === 'yes';
		var desktopPriorityCount = imageOptimizationEnabled ? ( parseInt( wrapper.dataset.desktopPriorityCount, 10 ) || 0 ) : 0;
		var mobileMainPriorityCount = imageOptimizationEnabled ? ( parseInt( wrapper.dataset.mobileMainPriorityCount, 10 ) || 0 ) : 0;
		var mobileThumbsPriorityCount = imageOptimizationEnabled ? ( parseInt( wrapper.dataset.mobileThumbsPriorityCount, 10 ) || 0 ) : 0;

		wrapper.kodloGalleryImages = galleryImages;
		wrapper.kodloGalleryImagesFingerprint = galleryFingerprint || getVisibleImagesFingerprint( galleryImages, maxImages );
		wrapper.dataset.appliedMaxImages = String( maxImages );

		closeMagnifier( wrapper );
		destroyMobileSwiper( instanceKey );
		if ( window.elementorFrontend && window.elementorFrontend.isEditMode() ) {
			var paginationElement = wrapper.querySelector( '.kodlo-mobile-slider .swiper-pagination' );
			if ( paginationElement ) paginationElement.innerHTML = '';
		}
		if ( mainWrapper ) mainWrapper.innerHTML = '';
		if ( thumbWrapper ) thumbWrapper.innerHTML = '';

		var isNowDesktop = isDesktop( wrapper );
		var visibleImages = maxImages > 0 ? galleryImages.slice( 0, maxImages ) : galleryImages;
		var galleryImageCount = visibleImages.length;
		var desktopColumns = getDesktopColumnCount( wrapper );
		var stretchSetting = normalizeStretchSetting( getWidgetProperty(
			wrapper,
			'--kodlo-stretched-image',
			wrapper.dataset.lastImageFullWidth || 'no'
		) );
		var stretchedAspectRatio = normalizeAspectRatio( getWidgetProperty(
			wrapper,
			'--kodlo-last-image-aspect-ratio',
			wrapper.dataset.lastImageAspectRatio || '16 / 9'
		) );
		var stretchedObjectFit = normalizeObjectFit( getWidgetProperty(
			wrapper,
			'--kodlo-last-image-object-fit',
			wrapper.dataset.lastImageObjectFit || 'cover'
		) );

		if ( mainWrapper ) {
			var mainFragment = document.createDocumentFragment();
			visibleImages.forEach( function( galleryImage, index ) {
				var slideElement = document.createElement( 'div' );
				slideElement.className = 'swiper-slide kodlo-gallery-main-slide kodlo-grid-item';
				slideElement.dataset.index = index;

				var loadingMode = imageOptimizationEnabled
					? ( isNowDesktop
						? ( index < desktopPriorityCount ? 'high' : 'lazy' )
						: ( index < mobileMainPriorityCount ? 'high' : 'lazy' ) )
					: null;

				// Applies the configured desktop grid stretch to one boundary image.
				var gridItemStyle = '';
				var mediaItemStyle = '';

				if ( isNowDesktop && stretchSetting !== 'no' && galleryImageCount % desktopColumns !== 0 ) {
					var isLastIndex = ( index === galleryImageCount - 1 );
					var isFirstIndex = ( 0 === index );
					var shouldStretch = ( 'last' === stretchSetting && isLastIndex ) || ( 'first' === stretchSetting && isFirstIndex );
					if ( shouldStretch ) {
						gridItemStyle = 'grid-column: 1 / -1;';
						mediaItemStyle = '--kodlo-aspect-ratio: ' + stretchedAspectRatio
							+ '; --kodlo-object-fit: ' + stretchedObjectFit + ';';
					}
				}

				if ( gridItemStyle ) {
					slideElement.style.cssText = gridItemStyle;
				}

				slideElement.innerHTML = frontend.buildMediaItemHtml( galleryImage, videoIconHtml, mediaItemStyle, wrapper, loadingMode );
				mainFragment.appendChild( slideElement );
			} );
			mainWrapper.appendChild( mainFragment );
		}

		if ( thumbWrapper ) {
			var thumbFragment = document.createDocumentFragment();
			visibleImages.forEach( function( galleryImage, index ) {
				var thumbnailSlide = document.createElement( 'div' );
				thumbnailSlide.className = 'swiper-slide';
				var thumbnailLabel = galleryImage.video
					? String( l10n.viewVideo || 'View video %d' ).replace( '%d', index + 1 )
					: String( l10n.viewImage || 'View image %d' ).replace( '%d', index + 1 );

				var thumbnailLoadingMode = imageOptimizationEnabled
					? ( index < mobileThumbsPriorityCount ? 'high' : 'lazy' )
					: null;
				var thumbnailWidthAttribute = galleryImage.thumb_width
					? ' width="' + frontend.escapeAttribute( galleryImage.thumb_width ) + '"'
					: '';
				var thumbnailHeightAttribute = galleryImage.thumb_height
					? ' height="' + frontend.escapeAttribute( galleryImage.thumb_height ) + '"'
					: '';
				var thumbnailLoadingAttributes = thumbnailLoadingMode === 'high'
					? ' fetchpriority="high" decoding="async"'
					: ( thumbnailLoadingMode === 'lazy' ? ' loading="lazy" decoding="async"' : '' );

				var thumbnailResponsiveAttributes = thumbnailLoadingMode === 'lazy' && galleryImage.thumb_srcset && galleryImage.thumb_sizes
					? ' srcset="' + frontend.escapeAttribute( galleryImage.thumb_srcset ) + '" sizes="' + frontend.escapeAttribute( galleryImage.thumb_sizes ) + '"'
					: '';

				var thumbnailMediaHtml = '<img src="' + frontend.escapeAttribute( galleryImage.thumb ) + '"'
					+ thumbnailWidthAttribute + thumbnailHeightAttribute + thumbnailResponsiveAttributes
					+ ' alt=""' + thumbnailLoadingAttributes + ' />';
				thumbnailMediaHtml += frontend.buildThumbnailVideoIconHtml( galleryImage, videoIconHtml, wrapper );

				thumbnailSlide.innerHTML = '<button type="button" class="kodlo-thumb-button" aria-label="'
					+ frontend.escapeAttribute( thumbnailLabel ) + '">'
					+ thumbnailMediaHtml + '</button>';
				thumbFragment.appendChild( thumbnailSlide );
			} );
			thumbWrapper.appendChild( thumbFragment );
		}

		applyDesktopGrid( wrapper );
		updateDesktopGridLayout( wrapper );
		applyNavigationPositions( wrapper );

		wrapper.dataset.appliedDevice = isNowDesktop ? 'desktop' : 'mobile';

		if ( isNowDesktop ) {
			triggerAutoplayDesktop( wrapper );
		} else {
			initMobileSwiper( wrapper );
		}
	}

	function renderGalleryIfChanged( galleryImages, wrapper ) {
		var maxImages = getMaxImages( wrapper );
		var galleryFingerprint = getVisibleImagesFingerprint( galleryImages, maxImages );

		// A variation change on page load can arrive before boot(); the server-rendered gallery already shows the
		// default images, so compare against them instead of rebuilding every slide.
		if ( ! Array.isArray( wrapper.kodloGalleryImages ) ) {
			var initialGalleryData = frontend.getGalleryData( wrapper );
			if ( initialGalleryData && initialGalleryData.defaultImages ) {
				wrapper.kodloGalleryImages = initialGalleryData.defaultImages;
				wrapper.kodloGalleryImagesFingerprint = getVisibleImagesFingerprint( initialGalleryData.defaultImages, maxImages );
			}
		}
		var imagesMatch = Array.isArray( wrapper.kodloGalleryImages )
			&& wrapper.kodloGalleryImagesFingerprint === galleryFingerprint;

		wrapper.kodloGalleryImages = galleryImages;
		wrapper.kodloGalleryImagesFingerprint = galleryFingerprint;

		if ( imagesMatch ) {
			wrapper.dataset.appliedMaxImages = String( maxImages );
			return false;
		}

		renderGallery( galleryImages, wrapper, galleryFingerprint );
		return true;
	}

	function initializeGallery( wrapper ) {
		if ( ! wrapper || ! wrapper.isConnected ) return;

		// boot() and Elementor's element_ready both initialize the same wrapper, and a variation render may already
		// have built the swipers; every extra pass destroyed and rebuilt them. A later pass still runs when the mobile
		// view has no slider yet (Swiper loaded after the first pass gave up). The editor re-renders widgets as new
		// nodes, so it keeps the full path.
		var editMode = !! ( window.elementorFrontend && window.elementorFrontend.isEditMode() );
		if ( ! editMode ) {
			var renderedInstance = swiperInstances[ getInstanceKey( wrapper ) ];
			var hasOwnSlider = !! ( renderedInstance && renderedInstance.wrapper === wrapper );
			if ( wrapper.kodloInitialized && ( hasOwnSlider || isDesktop( wrapper ) ) ) return;
			wrapper.kodloInitialized = true;
			if ( hasOwnSlider && ! isDesktop( wrapper ) ) {
				cleanupDisconnectedGalleries();
				if ( initializedWrappers.indexOf( wrapper ) === -1 ) {
					initializedWrappers.push( wrapper );
				}
				return;
			}
		}

		cleanupDisconnectedGalleries();
		if ( initializedWrappers.indexOf( wrapper ) === -1 ) {
			initializedWrappers.push( wrapper );
		}
		destroyMagnifier( wrapper );

		var galleryData = frontend.getGalleryData( wrapper );
		var currentMaxImages = getMaxImages( wrapper );
		if ( galleryData && galleryData.defaultImages && ! Array.isArray( wrapper.kodloGalleryImages ) ) {
			wrapper.kodloGalleryImages = galleryData.defaultImages;
			wrapper.kodloGalleryImagesFingerprint = getVisibleImagesFingerprint( galleryData.defaultImages, currentMaxImages );
		}

		wrapper.dataset.appliedMaxImages = String( currentMaxImages );
		applyDesktopGrid( wrapper );
		updateDesktopGridLayout( wrapper );
		var instanceKey = getInstanceKey( wrapper );
		destroyMobileSwiper( instanceKey );

		var isNowDesktop = isDesktop( wrapper );
		wrapper.dataset.appliedDevice = isNowDesktop ? 'desktop' : 'mobile';

		if ( isNowDesktop ) {
			triggerAutoplayDesktop( wrapper );
		} else {
			initMobileSwiper( wrapper );
		}
	}

	// Boot: apply grid and init swipers.
	function boot() {
		queryAll( '.kodlo-gallery-wrapper' ).forEach( initializeGallery );
		observeGalleryRemovals();
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', boot );
	} else {
		boot();
	}

	// Initializes editor rerenders, popups, templates, and AJAX-inserted frontend widgets.
	function registerElementorLifecycle() {
		var hooks = window.elementorFrontend && window.elementorFrontend.hooks;
		if ( ! hooks || registeredElementorHooks === hooks ) return;

		hooks.addAction(
			'frontend/element_ready/kodlo-product-gallery.default',
			function ( $element ) {
				var wrapper = $element[ 0 ] && $element[ 0 ].querySelector( '.kodlo-gallery-wrapper' );
				if ( wrapper ) {
					initializeGallery( wrapper );
				}
			}
		);
		registeredElementorHooks = hooks;
	}

	jQuery( window ).on( 'elementor/frontend/init', registerElementorLifecycle );
	registerElementorLifecycle();

	function updateResponsiveGallery( wrapper ) {
		closeMagnifier( wrapper );
		var instanceKey = getInstanceKey( wrapper );
		var maxImages = getMaxImages( wrapper );
		var galleryImages = wrapper.kodloGalleryImages;

		if ( String( maxImages ) !== wrapper.dataset.appliedMaxImages && Array.isArray( galleryImages ) ) {
			renderGallery( galleryImages, wrapper );
			return;
		}

		updateDesktopGridLayout( wrapper );
		applyNavigationPositions( wrapper );
		var wasDesktop = wrapper.dataset.appliedDevice === 'desktop';
		var isNowDesktop = isDesktop( wrapper );
		wrapper.dataset.appliedDevice = isNowDesktop ? 'desktop' : 'mobile';

		if ( wasDesktop !== isNowDesktop && Array.isArray( galleryImages ) ) {
			renderGallery( galleryImages, wrapper );
			return;
		}

		if ( isNowDesktop ) {
			applyDesktopGrid( wrapper );
			destroyMobileSwiper( instanceKey );
			return;
		}

		var instance = swiperInstances[ instanceKey ];
		if ( ! instance ) {
			initMobileSwiper( wrapper );
		} else if ( instance.main ) {
			updateLimitedPagination( instance.main, getMaxVisibleDots( wrapper ) );
		}
	}

	// Debounces viewport work shared by responsive rendering and Swiper state.
	var resizeTimer;
	function scheduleResponsiveGalleryUpdate() {
		clearTimeout( resizeTimer );
		resizeTimer = setTimeout( function() {
			cleanupDisconnectedGalleries();
			queryAll( '.kodlo-gallery-wrapper' ).forEach( updateResponsiveGallery );
		}, 150 );
	}

	window.addEventListener( 'resize', scheduleResponsiveGalleryUpdate );

	frontend.queryAll = queryAll;
	frontend.isDesktop = isDesktop;
	frontend.renderGalleryIfChanged = renderGalleryIfChanged;
	frontend.bindVideoPlayback();
	frontend.bindVariationEvents();

} )();

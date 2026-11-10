/**
 * Video runtime for Kodlo Product Gallery for Elementor.
 *
 * Resolves supported providers, applies playback preferences, and owns the
 * delegated play-button lifecycle.
 */
( function( window, document ) {
	'use strict';

	var frontend = window.kodloGalleryFrontend = window.kodloGalleryFrontend || {};
	var l10n = window.kodloGalleryFrontendL10n || {};
	var playbackBound = false;
	var playerCounter = 0;

	function extractYoutubeId( url ) {
		var match = url.match( /(?:youtu\.be\/|v\/|embed\/|shorts\/|watch\?v=|&v=)([^#&?]{11})/ );
		return match ? match[ 1 ] : '';
	}

	function extractVimeoId( url ) {
		var match = url.match( /vimeo\.com\/(\d+)/ );
		return match ? match[ 1 ] : '';
	}

	frontend.isAutoplayEnabled = function( wrapper, device ) {
		if ( window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
			return false;
		}

		var autoplay = wrapper.dataset.autoplay || 'no';
		return autoplay === 'yes' || autoplay === device;
	};

	// While the video plays the poster image inside the gallery link is hidden, so the link would be a focusable link
	// without an accessible name (WCAG 4.1.2 / 2.4.4). It keeps the poster's alt text (or the player label) as its name.
	function nameMediaLink( mediaItem, image ) {
		var link = mediaItem.querySelector( '.kodlo-media-link' );
		if ( ! link || link.hasAttribute( 'aria-label' ) ) {
			return;
		}
		var label = ( image && image.getAttribute( 'alt' ) ) || l10n.videoPlayer || '';
		if ( label ) {
			link.setAttribute( 'aria-label', label );
			link.setAttribute( 'data-kodlo-video-label', '' );
		}
	}

	function unnameMediaLink( mediaItem ) {
		var link = mediaItem.querySelector( '.kodlo-media-link[data-kodlo-video-label]' );
		if ( link ) {
			link.removeAttribute( 'aria-label' );
			link.removeAttribute( 'data-kodlo-video-label' );
		}
	}

	frontend.resetVideoItem = function( mediaItem ) {
		if ( ! mediaItem ) {
			return;
		}

		var container = mediaItem.querySelector( '.kodlo-video-container' );
		var image = mediaItem.querySelector( '.kodlo-main-image' );
		var button = mediaItem.querySelector( '.kodlo-video-play-btn' );
		unnameMediaLink( mediaItem );

		if ( container ) {
			var activePlayer = container.firstElementChild;
			if ( activePlayer && typeof activePlayer.kodloCleanup === 'function' ) activePlayer.kodloCleanup();
			container.classList.remove( 'is-loading', 'is-ready' );
			container.removeAttribute( 'aria-busy' );
			container.innerHTML = '';
		}
		if ( image ) {
			image.style.display = '';
		}
		if ( button ) {
			button.style.display = '';
			button.style.visibility = '';
			button.style.pointerEvents = '';
		}
	};

	function parsePlayerMessage( messageData ) {
		if ( typeof messageData !== 'string' ) return messageData || {};

		try {
			return JSON.parse( messageData );
		} catch ( error ) {
			return {};
		}
	}

	function bindEmbeddedVideoEnd( embed, provider, onEnded ) {
		var playerOrigin = 'youtube' === provider ? 'https://www.youtube.com' : 'https://player.vimeo.com';
		var handleMessage = function( event ) {
			if ( event.origin !== playerOrigin || event.source !== embed.contentWindow ) return;

			var playerMessage = parsePlayerMessage( event.data );
			var youtubeEnded = 'youtube' === provider
				&& 'onStateChange' === playerMessage.event
				&& 0 === Number( playerMessage.info );
			var vimeoEnded = 'vimeo' === provider
				&& ( 'ended' === playerMessage.event || 'finish' === playerMessage.event );

			if ( youtubeEnded || vimeoEnded ) onEnded();
		};

		window.addEventListener( 'message', handleMessage );
		embed.kodloCleanup = function() {
			window.removeEventListener( 'message', handleMessage );
			delete embed.kodloCleanup;
		};

		if ( ! embed.contentWindow ) return;
		if ( 'youtube' === provider ) {
			embed.contentWindow.postMessage( JSON.stringify( { event: 'listening', id: embed.id } ), playerOrigin );
			embed.contentWindow.postMessage(
				JSON.stringify( { event: 'command', func: 'addEventListener', args: [ 'onStateChange' ], id: embed.id } ),
				playerOrigin
			);
			return;
		}

		embed.contentWindow.postMessage(
			JSON.stringify( { method: 'addEventListener', value: 'ended' } ),
			playerOrigin
		);
	}

	function createVideoEmbed( videoUrl, wrapper, posterUrl, onReady, onError, onEnded ) {
		var device = frontend.isDesktop( wrapper ) ? 'desktop' : 'mobile';
		var autoplay = frontend.isAutoplayEnabled( wrapper, device );
		var muted = autoplay || wrapper.dataset.muteVideos === 'yes';
		var loop = wrapper.dataset.loopVideos === 'yes';
		var muteValue = muted ? '1' : '0';
		var loopValue = loop ? '1' : '0';
		var embed;

		if ( videoUrl.indexOf( 'youtube.com' ) > -1 || videoUrl.indexOf( 'youtu.be' ) > -1 ) {
			var youtubeId = extractYoutubeId( videoUrl );
			if ( ! youtubeId ) {
				return null;
			}
			embed = document.createElement( 'iframe' );
			embed.id = 'kodlo-video-player-' + ( ++playerCounter );
			embed.src = 'https://www.youtube.com/embed/' + youtubeId
				+ '?autoplay=1&mute=' + muteValue + '&loop=' + loopValue + '&playlist=' + youtubeId
				+ '&controls=0&disablekb=1&fs=0&playsinline=1&enablejsapi=1'
				+ ( window.location && window.location.origin ? '&origin=' + encodeURIComponent( window.location.origin ) : '' );
			embed.allow = 'autoplay; encrypted-media';
			embed.referrerPolicy = 'strict-origin-when-cross-origin';
			embed.title = l10n.videoPlayer || 'Product video player';
			embed.kodloProvider = 'youtube';
		} else if ( videoUrl.indexOf( 'vimeo.com' ) > -1 ) {
			var vimeoId = extractVimeoId( videoUrl );
			if ( ! vimeoId ) {
				return null;
			}
			embed = document.createElement( 'iframe' );
			embed.id = 'kodlo-video-player-' + ( ++playerCounter );
			embed.src = 'https://player.vimeo.com/video/' + vimeoId
				+ '?autoplay=1&loop=' + loopValue + '&muted=' + muteValue
				+ '&controls=0&keyboard=0&fullscreen=0&api=1&player_id=' + encodeURIComponent( embed.id );
			embed.allow = 'autoplay';
			embed.referrerPolicy = 'strict-origin-when-cross-origin';
			embed.title = l10n.videoPlayer || 'Product video player';
			embed.kodloProvider = 'vimeo';
		} else {
			embed = document.createElement( 'video' );
			embed.src = videoUrl;
			embed.autoplay = true;
			embed.controls = false;
			embed.playsInline = true;
			embed.preload = 'auto';
			embed.muted = muted;
			embed.loop = loop;
			embed.disablePictureInPicture = true;
			if ( posterUrl ) {
				embed.poster = posterUrl;
			}
		}

		var readyEvent = embed.tagName === 'VIDEO' ? 'loadeddata' : 'load';
		embed.addEventListener( readyEvent, function() {
			onReady();
			if ( ! loop && embed.kodloProvider ) bindEmbeddedVideoEnd( embed, embed.kodloProvider, onEnded );
		}, { once: true } );
		if ( ! loop && 'VIDEO' === embed.tagName ) embed.addEventListener( 'ended', onEnded );
		if ( typeof onError === 'function' ) {
			embed.addEventListener( 'error', onError, { once: true } );
		}
		return embed;
	}

	frontend.bindVideoPlayback = function() {
		if ( playbackBound ) {
			return;
		}
		playbackBound = true;

		document.addEventListener( 'click', function( event ) {
			var button = event.target.closest( '.kodlo-video-play-btn' );
			if ( ! button ) {
				return;
			}

			event.stopPropagation();
			var mediaItem = button.closest( '.kodlo-media-item' );
			var videoUrl = mediaItem && mediaItem.dataset.videoUrl;
			var container = mediaItem && mediaItem.querySelector( '.kodlo-video-container' );
			if ( ! videoUrl || ! container || container.classList.contains( 'is-loading' ) || container.classList.contains( 'is-ready' ) ) {
				return;
			}

			var wrapper = mediaItem.closest( '.kodlo-gallery-wrapper' );
			var image = mediaItem.querySelector( '.kodlo-main-image' );
			container.innerHTML = '';
			container.classList.add( 'is-loading' );
			container.setAttribute( 'aria-busy', 'true' );
			button.style.visibility = 'hidden';
			button.style.pointerEvents = 'none';

			var revealVideo = function() {
				if ( ! embed || ! embed.isConnected || ! container.isConnected || ! container.contains( embed ) ) {
					return;
				}
				container.removeAttribute( 'aria-busy' );
				container.classList.remove( 'is-loading' );
				container.classList.add( 'is-ready' );
				nameMediaLink( mediaItem, image );
				if ( image ) {
					image.style.display = 'none';
				}
				button.style.display = 'none';
			};
			var resetFailedVideo = function() {
				if ( ! embed || ! container.contains( embed ) ) {
					return;
				}

				frontend.resetVideoItem( mediaItem );
			};
			var resetCompletedVideo = function() {
				if ( embed && container.contains( embed ) ) frontend.resetVideoItem( mediaItem );
			};

			var embed = createVideoEmbed(
				videoUrl,
				wrapper,
				image ? image.currentSrc || image.src : '',
				revealVideo,
				resetFailedVideo,
				resetCompletedVideo
			);
			if ( ! embed ) {
				frontend.resetVideoItem( mediaItem );
				return;
			}

			container.appendChild( embed );
		} );
	};
}( window, document ) );

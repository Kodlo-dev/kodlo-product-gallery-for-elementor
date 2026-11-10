/**
 * Markup renderer for Kodlo Product Gallery for Elementor.
 *
 * Builds escaped media and thumbnail markup used when variation galleries are
 * rendered without a page reload.
 */
( function( window ) {
	'use strict';

	var frontend = window.kodloGalleryFrontend = window.kodloGalleryFrontend || {};
	var l10n = window.kodloGalleryFrontendL10n || {};

	frontend.escapeAttribute = function( value ) {
		return String( value || '' )
			.replace( /&/g, '&amp;' )
			.replace( /"/g, '&quot;' )
			.replace( /</g, '&lt;' )
			.replace( />/g, '&gt;' );
	};

	frontend.buildThumbnailVideoIconHtml = function( image, videoIconHtml, wrapper ) {
		if ( ! image.video || ! wrapper || wrapper.dataset.showThumbnailVideoIcon !== 'yes' ) {
			return '';
		}

		return '<span class="kodlo-thumbnail-video-icon" aria-hidden="true">' + videoIconHtml + '</span>';
	};

	frontend.buildMediaItemHtml = function( image, videoIconHtml, extraStyle, wrapper, loadingMode ) {
		var escapeAttribute = frontend.escapeAttribute;
		var hasVideo = image.video && image.video.length > 0;
		var styleAttribute = extraStyle ? ' style="' + escapeAttribute( extraStyle ) + '"' : '';
		var showCaption = wrapper && wrapper.dataset.showCaption === 'yes' && image.caption;
		var linkType = wrapper ? wrapper.dataset.galleryLink : 'none';
		var linkUrl = linkType === 'file' ? image.full : ( linkType === 'attachment' ? image.attachment_url : '' );
		var sourceSetAttribute = image.srcset ? ' srcset="' + escapeAttribute( image.srcset ) + '"' : '';
		var sizesAttribute = image.sizes ? ' sizes="' + escapeAttribute( image.sizes ) + '"' : '';
		var linkOpen = '';
		var linkClose = '';

		if ( linkUrl ) {
			var lightboxAttributes = '';
			if ( linkType === 'file' && wrapper && wrapper.dataset.lightboxEnabled === 'yes' ) {
				lightboxAttributes = ' data-elementor-open-lightbox="yes" data-elementor-lightbox-slideshow="'
					+ escapeAttribute( wrapper.dataset.lightboxGroup || 'kodlo-gallery' ) + '"';
			}
			linkOpen = '<a class="kodlo-media-link" href="' + escapeAttribute( linkUrl ) + '"' + lightboxAttributes + '>';
			linkClose = '</a>';
		}

		var widthAttribute = image.width ? ' width="' + escapeAttribute( image.width ) + '"' : '';
		var heightAttribute = image.height ? ' height="' + escapeAttribute( image.height ) + '"' : '';
		var loadingAttributes = loadingMode === 'high'
			? ' fetchpriority="high" decoding="async"'
			: ( loadingMode === 'lazy' ? ' loading="lazy" decoding="async"' : '' );
		var mediaTag = '<img src="' + escapeAttribute( image.medium ) + '"' + widthAttribute + heightAttribute
			+ sourceSetAttribute + sizesAttribute + ' alt="' + escapeAttribute( image.alt ) + '"'
			+ loadingAttributes + ' class="kodlo-main-image" />';
		var videoUrlAttribute = hasVideo ? ' data-video-url="' + escapeAttribute( image.video ) + '"' : '';
		var html = '<figure class="kodlo-gallery-entry"><div class="kodlo-media-item' + ( hasVideo ? ' has-video' : '' ) + '"'
			+ videoUrlAttribute + ' data-full-image="' + escapeAttribute( image.full || image.medium ) + '"'
			+ styleAttribute + '>' + linkOpen + mediaTag + linkClose;

		if ( hasVideo ) {
			var hoverAnimation = String( wrapper ? wrapper.dataset.playBtnHoverAnimation || '' : '' );
			var hoverClass = /^[a-z0-9_-]+$/i.test( hoverAnimation )
				? ' elementor-animation-' + escapeAttribute( hoverAnimation )
				: '';
			html += '<div class="kodlo-video-play-btn-wrap">'
				+ '<button type="button" class="kodlo-video-play-btn' + hoverClass + '" aria-label="'
				+ escapeAttribute( l10n.playVideo || 'Play video' ) + '">' + videoIconHtml + '</button>'
				+ '</div><div class="kodlo-video-container"></div>';
		}

		html += '</div>';
		if ( showCaption ) {
			html += '<figcaption class="kodlo-gallery-caption">' + escapeAttribute( image.caption ) + '</figcaption>';
		}
		return html + '</figure>';
	};
}( window ) );

/**
 * Variation gallery admin JavaScript for Kodlo Product Gallery for Elementor.
 * Manages image selection, removal, keyboard focus, and native drag-and-drop sorting.
 * No jQuery dependency - vanilla JS only.
 */
( function() {
	'use strict';

	var l10n = window.kodloGalleryAdminL10n || {};

	function escapeAttribute( value ) {
		return String( value || '' )
			.replace( /&/g, '&amp;' )
			.replace( /"/g, '&quot;' )
			.replace( /</g, '&lt;' )
			.replace( />/g, '&gt;' );
	}

	// Updates the hidden WooCommerce field with current gallery image IDs.
	function updateVariationGallery( galleryContainer ) {
		var attachmentIds = [];
		galleryContainer.querySelectorAll( '.kodlo-variation-gallery-image' ).forEach( function( galleryImage ) {
			var attachmentId = galleryImage.dataset.attachmentid;
			if ( attachmentId ) attachmentIds.push( attachmentId );
		} );

		var galleryInput = galleryContainer.parentElement.querySelector( '.kodlo-variation-gallery-input' );
		if ( galleryInput ) {
			galleryInput.value = attachmentIds.join( ',' );
			galleryInput.dispatchEvent( new Event( 'change', { bubbles: true } ) );
		}

		var buttonsContainer = galleryContainer.nextElementSibling;
		var uploadButton = buttonsContainer && buttonsContainer.querySelector( '.kodlo-variation-gallery-upload' );
		var editButton = buttonsContainer && buttonsContainer.querySelector( '.kodlo-variation-gallery-edit' );
		var removeAllButton = buttonsContainer && buttonsContainer.querySelector( '.kodlo-variation-gallery-remove-all' );

		if ( attachmentIds.length > 0 ) {
			galleryContainer.style.display = '';
			if ( uploadButton ) {
				uploadButton.classList.add( 'hidden' );
				uploadButton.style.display = 'none';
			}
			if ( editButton ) {
				editButton.classList.remove( 'hidden' );
				editButton.style.display = '';
			}
			if ( removeAllButton ) {
				removeAllButton.classList.remove( 'hidden' );
				removeAllButton.style.display = '';
			}
		} else {
			galleryContainer.style.display = 'none';
			if ( uploadButton ) {
				uploadButton.classList.remove( 'hidden' );
				uploadButton.style.display = '';
			}
			if ( editButton ) {
				editButton.classList.add( 'hidden' );
				editButton.style.display = 'none';
			}
			if ( removeAllButton ) {
				removeAllButton.classList.add( 'hidden' );
				removeAllButton.style.display = 'none';
			}
		}
	}

	// Restores focus after a gallery control removes its current DOM node.
	function restoreVariationGalleryFocus( variationGallery, preferredControl ) {
		var fallbackSelectors = [
			'.kodlo-variation-gallery-upload:not(.hidden)',
			'.kodlo-variation-gallery-edit:not(.hidden)',
			'.kodlo-variation-gallery-remove-all:not(.hidden)',
		];
		var controls = [ preferredControl ];

		fallbackSelectors.forEach( function( selector ) {
			controls.push( variationGallery.querySelector( selector ) );
		} );

		controls.some( function( control ) {
			if ( ! control || ! control.isConnected || control.disabled || control.hidden || control.offsetParent === null ) {
				return false;
			}

			control.focus();
			return true;
		} );
	}

	// Native HTML5 drag-and-drop sortable for gallery items.
	function initSortable( container ) {
		if ( container.dataset.sortableInit ) return;
		container.dataset.sortableInit = '1';

		var dragging = null;

		container.addEventListener( 'dragstart', function( e ) {
			var item = e.target.closest( '.kodlo-variation-gallery-image' );
			if ( ! item ) return;
			dragging = item;
			item.style.opacity = '0.4';
			e.dataTransfer.effectAllowed = 'move';
		} );

		container.addEventListener( 'dragend', function() {
			if ( dragging ) {
				dragging.style.opacity = '';
				dragging = null;
				updateVariationGallery( container );
			}
		} );

		container.addEventListener( 'dragover', function( e ) {
			e.preventDefault();
			e.dataTransfer.dropEffect = 'move';
			var target = e.target.closest( '.kodlo-variation-gallery-image' );
			if ( target && target !== dragging ) {
				var rect     = target.getBoundingClientRect();
				var midpoint = rect.left + rect.width / 2;
				if ( e.clientX < midpoint ) {
					container.insertBefore( dragging, target );
				} else {
					container.insertBefore( dragging, target.nextSibling );
				}
			}
		} );

		container.addEventListener( 'drop', function( e ) {
			e.preventDefault();
		} );

		// Make existing items draggable.
		container.querySelectorAll( '.kodlo-variation-gallery-image' ).forEach( function( item ) {
			item.setAttribute( 'draggable', 'true' );
		} );
	}

	// Builds the HTML string for a variation gallery item.
	function getAttachmentText( value ) {
		if ( typeof value === 'string' ) {
			return value;
		}
		if ( value && typeof value.rendered === 'string' ) {
			return value.rendered;
		}
		if ( value && typeof value.raw === 'string' ) {
			return value.raw;
		}
		return '';
	}

	function buildGalleryItemHtml( attachmentId, thumbnailUrl, thumbnailAlt, hasVideo ) {
		var videoClass = hasVideo ? ' kodlo-has-video' : '';
		var videoIcon = hasVideo
			? '<div class="kodlo-variation-gallery-video" aria-hidden="true"><i class="dashicons dashicons-video-alt3"></i></div>'
			: '';
		var fallbackAlt = ( l10n.galleryImage || 'Gallery image %d' ).replace( '%d', attachmentId );
		var imageAlt = thumbnailAlt || fallbackAlt;
		var removeLabel = escapeAttribute( l10n.removeImage || 'Remove image' );
		var moveBackLabel = escapeAttribute( l10n.moveImageBack || 'Move image backward' );
		var moveForwardLabel = escapeAttribute( l10n.moveImageForward || 'Move image forward' );

		return '<div class="kodlo-variation-gallery-image' + videoClass + '" data-attachmentid="'
			+ escapeAttribute( attachmentId ) + '" draggable="true">'
			+ '<img src="' + escapeAttribute( thumbnailUrl ) + '" alt="' + escapeAttribute( imageAlt ) + '" />'
			+ '<button type="button" class="kodlo-variation-gallery-remove" aria-label="' + removeLabel + '">'
			+ '<span class="dashicons dashicons-no" aria-hidden="true"></span></button>'
			+ '<div class="kodlo-variation-gallery-order-controls">'
			+ '<button type="button" class="kodlo-variation-gallery-move-back" aria-label="' + moveBackLabel + '">'
			+ '<span class="dashicons dashicons-arrow-left-alt2" aria-hidden="true"></span></button>'
			+ '<button type="button" class="kodlo-variation-gallery-move-forward" aria-label="' + moveForwardLabel + '">'
			+ '<span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span></button>'
			+ '</div>'
			+ videoIcon
			+ '</div>';
	}

	// Initializes sortable on all existing gallery containers.
	function initAllSortables() {
		document.querySelectorAll( '.kodlo-variation-gallery-items' ).forEach( initSortable );
	}

	// Moves the variation gallery container to be displayed right after the form-flex-box.
	function moveVariationGalleries() {
		document.querySelectorAll( '.woocommerce_variation' ).forEach( function( variation ) {
			var gallery = variation.querySelector( '.kodlo-variation-gallery' );
			var flexBox = variation.querySelector( '.form-flex-box' );
			if ( gallery && flexBox && gallery.previousElementSibling !== flexBox ) {
				flexBox.after( gallery );
			}
		} );
	}

	document.addEventListener( 'DOMContentLoaded', function() {
		initAllSortables();
		moveVariationGalleries();

		var observer = new MutationObserver( function( mutations ) {
			var hasGalleryChanges = mutations.some( function( mutation ) {
				return Array.prototype.some.call( mutation.addedNodes, function( node ) {
					return node.nodeType === 1 && (
						node.matches( '.kodlo-variation-gallery-items' )
						|| node.querySelector( '.kodlo-variation-gallery-items' )
					);
				} );
			} );

			if ( hasGalleryChanges ) {
				initAllSortables();
				moveVariationGalleries();
			}
		} );

		observer.observe( document.body, { childList: true, subtree: true } );
	} );

	// Handles all variation gallery actions using a single delegated click listener.
	document.addEventListener( 'click', function( e ) {
		// Open gallery uploader or editor.
		var uploadEditBtn = e.target.closest( '.kodlo-variation-gallery-upload, .kodlo-variation-gallery-edit' );
		if ( uploadEditBtn ) {
			if ( typeof wp === 'undefined' || ! wp.media ) return;
			e.preventDefault();

			var variationGallery = uploadEditBtn.closest( '.kodlo-variation-gallery' );
			var galleryContainer = variationGallery.querySelector( '.kodlo-variation-gallery-items' );
			var galleryInput     = variationGallery.querySelector( '.kodlo-variation-gallery-input' );
			var currentVal       = galleryInput ? galleryInput.value : '';
			var isExisting       = !! currentVal;
			var title = isExisting ? ( l10n.editGallery || 'Edit Gallery' ) : ( l10n.selectImages || 'Select images for Variation Gallery' );
			var uploader;

			if ( isExisting ) {
				uploader = wp.media.gallery.edit( '[gallery ids="' + currentVal + '"]' );
			} else {
				uploader = wp.media( {
					library  : { type: 'image' },
					frame    : 'post',
					state    : 'gallery',
					title    : title,
					multiple : true,
				} ).open();
			}

			if ( isExisting ) {
				if ( uploader.state( 'gallery-edit' ) ) {
					uploader.state( 'gallery-edit' ).set( 'title', title );
				}
				if ( uploader.state( 'gallery-library' ) ) {
					uploader.state( 'gallery-library' ).set( 'title', title );
				}
			}

			uploader.on( 'update', function( selection ) {
				galleryContainer.innerHTML = '';

				selection.models.forEach( function( attachment ) {
					var item     = attachment.toJSON();
					var sizes    = item.sizes || {};
					var thumbUrl = sizes.thumbnail ? sizes.thumbnail.url : ( sizes.full ? sizes.full.url : ( item.icon || '' ) );
					if ( item.type !== 'image' ) return;
					var hasVideo = !! item.kodlo_gallery_video_url;
					var imageAlt = getAttachmentText( item.alt ) || getAttachmentText( item.title );
					galleryContainer.insertAdjacentHTML( 'beforeend', buildGalleryItemHtml( item.id, thumbUrl, imageAlt, hasVideo ) );
				} );

				initSortable( galleryContainer );
				updateVariationGallery( galleryContainer );
			} );
			return;
		}

		// Remove all gallery images.
		var removeAllBtn = e.target.closest( '.kodlo-variation-gallery-remove-all' );
		if ( removeAllBtn ) {
			e.preventDefault();
			if ( ! window.confirm( l10n.confirmRemoveAll || 'Are you sure you want to remove all variation gallery images?' ) ) return;

			var variationGallery = removeAllBtn.closest( '.kodlo-variation-gallery' );
			var galleryContainer = variationGallery.querySelector( '.kodlo-variation-gallery-items' );
			var uploadBtn = variationGallery.querySelector( '.kodlo-variation-gallery-upload' );
			galleryContainer.innerHTML = '';
			updateVariationGallery( galleryContainer );
			restoreVariationGalleryFocus( variationGallery, uploadBtn );
			return;
		}

		// Remove a single gallery image.
		var removeBtn = e.target.closest( '.kodlo-variation-gallery-remove' );
		if ( removeBtn ) {
			e.preventDefault();
			var imageEl          = removeBtn.closest( '.kodlo-variation-gallery-image' );
			var galleryContainer = imageEl.closest( '.kodlo-variation-gallery-items' );
			var variationGallery = imageEl.closest( '.kodlo-variation-gallery' );
			var adjacentImage    = imageEl.nextElementSibling || imageEl.previousElementSibling;
			var adjacentRemove   = adjacentImage && adjacentImage.querySelector( '.kodlo-variation-gallery-remove' );
			imageEl.parentNode.removeChild( imageEl );
			updateVariationGallery( galleryContainer );
			restoreVariationGalleryFocus( variationGallery, adjacentRemove );
			return;
		}

		// Move an image forward or backward.
		var moveBack = e.target.closest( '.kodlo-variation-gallery-move-back' );
		var moveForward = e.target.closest( '.kodlo-variation-gallery-move-forward' );
		var control = moveBack || moveForward;
		if ( control ) {
			e.preventDefault();
			var imageEl = control.closest( '.kodlo-variation-gallery-image' );
			var galleryContainer = imageEl.closest( '.kodlo-variation-gallery-items' );

			if ( moveBack && imageEl.previousElementSibling ) {
				galleryContainer.insertBefore( imageEl, imageEl.previousElementSibling );
			} else if ( moveForward && imageEl.nextElementSibling ) {
				galleryContainer.insertBefore( imageEl.nextElementSibling, imageEl );
			}

			updateVariationGallery( galleryContainer );
			control.focus();
			return;
		}
	} );

} )();

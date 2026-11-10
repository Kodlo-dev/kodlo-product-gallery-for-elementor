/**
 * Admin attachment behavior for Kodlo Product Gallery for Elementor.
 * Repositions and synchronizes the Video URL field in WordPress media views.
 */
( function( $ ) {
	'use strict';

	if ( typeof wp === 'undefined' || ! wp.media || ! wp.media.view ) {
		return;
	}

	var l10n = window.kodloGalleryAttachmentL10n || {};
	var DetailsView = wp.media.view.Attachment && wp.media.view.Attachment.Details;
	var CompatView = wp.media.view.AttachmentCompat;

	function getViewScope( $element ) {
		var $scope = $element.closest( '.media-sidebar, .edit-attachment-frame' );
		return $scope.length ? $scope : $element.parent();
	}

	function findCompatRow( $element, attachmentId ) {
		return getViewScope( $element )
			.find( '.compat-field-kodlo_gallery_video_url' )
			.filter( function() {
				var name = $( this ).find( ':input' ).first().attr( 'name' ) || '';
				return name.indexOf( 'attachments[' + attachmentId + ']' ) !== -1;
			} )
			.first();
	}

	function hideOriginalField( $details, attachmentId ) {
		var $row = findCompatRow( $details, attachmentId );
		if ( $details.find( '.attachment-video-url' ).length && $row.length ) {
			$row.addClass( 'kodlo-video-url-original-hidden' );
		}
	}

	function insertVideoField( view ) {
		var $details = view.$el;
		var $description = $details.find( '.setting[data-setting="description"]' ).first();
		var attachmentId = view.model && view.model.id;
		var supportsVideoUrl = view.model
			&& typeof view.model.has === 'function'
			&& view.model.has( 'kodlo_gallery_video_url' );

		if ( ! supportsVideoUrl || ! $description.length || ! attachmentId || $details.find( '.attachment-video-url' ).length ) {
			return;
		}

		var inputId = 'kodlo-video-url-' + attachmentId;
		var helpId = inputId + '-description';
		var $field = $( '<span>', {
			'class': 'setting attachment-video-url has-description'
		} );
		var $label = $( '<label>', {
			'class': 'name',
			'for': inputId,
			text: l10n.videoUrl || 'Video URL'
		} );
		var $input = $( '<input>', {
			type: 'url',
			id: inputId,
			'aria-describedby': helpId,
			'data-attachment-id': attachmentId
		} ).val( view.model.get( 'kodlo_gallery_video_url' ) || '' );
		var $help = $( '<p>', {
			id: helpId,
			'class': 'description kodlo-gallery-video-url-description',
			text: l10n.videoHelp || 'YouTube, Vimeo, or direct MP4 URL. This image is used as the video thumbnail and poster.'
		} );

		$field.append( $label, $input );
		$description.after( $field );
		$field.after( $help );

		_.defer( function() {
			hideOriginalField( $details, attachmentId );
		} );
	}

	if ( DetailsView && DetailsView.prototype.render ) {
		var originalDetailsRender = DetailsView.prototype.render;
		DetailsView.prototype.render = function() {
			var result = originalDetailsRender.apply( this, arguments );
			insertVideoField( this );
			return result;
		};
	}

	if ( CompatView && CompatView.prototype.render ) {
		var originalCompatRender = CompatView.prototype.render;
		CompatView.prototype.render = function() {
			var result = originalCompatRender.apply( this, arguments );
			var attachmentId = this.model && this.model.id;
			var $details = this.$el.siblings( '.attachment-details' ).first();

			if ( attachmentId && $details.length ) {
				hideOriginalField( $details, attachmentId );
			}
			return result;
		};
	}

	// Syncs the inline field with WordPress's compatibility form and native auto-save.
	$( document ).on( 'input change', '.setting.attachment-video-url input', function() {
		var $input = $( this );
		var attachmentId = $input.attr( 'data-attachment-id' );
		var $row = findCompatRow( $input, attachmentId );
		var $compatInput = $row.find( ':input' ).first();
		var value = $input.val();

		if ( $compatInput.length && $compatInput.val() !== value ) {
			$compatInput.val( value ).trigger( 'change' );
		}
	} );

	// Adds the field to the standard attachment edit screen.
	$( function() {
		var $descriptionEditor = $( '#wp-attachment_content-wrap' );
		var $videoRow = $( '.compat-field-kodlo_gallery_video_url' );

		if ( $descriptionEditor.length && $videoRow.length && ! $( '.attachment-video-url-edit-page' ).length ) {
			var labelText = $videoRow.find( 'label .alignleft' ).text() || $videoRow.find( 'label' ).text() || 'Video URL';
			var value = $videoRow.find( 'input' ).val() || '';
			var helpText = $videoRow.find( '.help' ).text() || '';
			var inputId = 'kodlo-video-url-custom-field';
			var helpId = inputId + '-description';
			var $customField = $( '<p>', { 'class': 'attachment-video-url-edit-page' } );
			var $label = $( '<label>', { 'for': inputId } )
				.append( $( '<strong>' ).text( labelText ) );
			var $input = $( '<input>', {
				type: 'url',
				'class': 'widefat',
				id: inputId,
				'aria-describedby': helpId
			} ).val( value );
			var $help = $( '<p>', {
				id: helpId,
				'class': 'attachment-video-url-description'
			} ).text( helpText );

			$customField.append( $label, $( '<br>' ), $input );
			$descriptionEditor.after( $customField, $help );
			$videoRow.addClass( 'kodlo-video-url-original-hidden' );

			// Syncs the visible field value back to the hidden compatibility input.
			$input.on( 'input change', function() {
				var newValue = $( this ).val();
				var $originalInput = $videoRow.find( 'input' );
				if ( $originalInput.length && $originalInput.val() !== newValue ) {
					$originalInput.val( newValue ).trigger( 'change' );
				}
			} );
		}
	} );
}( jQuery ) );

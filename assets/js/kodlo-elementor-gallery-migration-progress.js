/**
 * WooGallery batch progress interface for Kodlo Product Gallery for Elementor.
 *
 * Keeps the administrator on the current page while protected AJAX requests
 * complete every migration or cleanup batch.
 */

( function() {
	'use strict';

	var config = window.kodloGalleryWooGalleryProgress;
	var notice = document.querySelector( '[data-kodlo-woogallery-progress]' );
	if ( ! config || ! notice ) {
		return;
	}

	var progressBar = notice.querySelector( 'progress' );
	var statusText = notice.querySelector( '[data-kodlo-woogallery-status]' );
	var stalledRequests = 0;

	function warnBeforeLeaving( event ) {
		event.preventDefault();
		event.returnValue = '';
	}

	function stopProcessing( message, reloadPage ) {
		notice.setAttribute( 'aria-busy', 'false' );
		statusText.textContent = message;
		window.removeEventListener( 'beforeunload', warnBeforeLeaving );
		if ( reloadPage ) {
			window.setTimeout( function() { window.location.reload(); }, config.completeDelay );
		}
	}

	function showRetry( message ) {
		notice.setAttribute( 'aria-busy', 'false' );
		statusText.textContent = message || config.failedMessage;
		if ( notice.querySelector( '[data-kodlo-woogallery-retry]' ) ) {
			return;
		}

		var retryButton = document.createElement( 'button' );
		retryButton.type = 'button';
		retryButton.className = 'button';
		retryButton.textContent = config.retryLabel;
		retryButton.setAttribute( 'data-kodlo-woogallery-retry', '' );
		retryButton.addEventListener( 'click', function() {
			stalledRequests = 0;
			retryButton.remove();
			notice.setAttribute( 'aria-busy', 'true' );
			processNextBatch();
		} );
		notice.appendChild( retryButton );
	}

	function processNextBatch() {
		var requestController = new window.AbortController();
		var requestTimer = window.setTimeout( function() { requestController.abort(); }, config.requestTimeout );
		var requestBody = new URLSearchParams( {
			action: config.action,
			nonce: config.nonce,
			generation: config.generation
		} );

		window.fetch( config.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
			body: requestBody.toString(),
			signal: requestController.signal
		} ).then( function( response ) {
			return response.json().catch( function() { return null; } ).then( function( responseBody ) {
				if ( ! response.ok || ! responseBody || ! responseBody.success ) {
					var requestError = new Error( 'Batch request failed.' );
					requestError.status = response.status;
					requestError.serverMessage = responseBody && responseBody.data ? responseBody.data.message : '';
					throw requestError;
				}
				return responseBody;
			} );
		} ).then( function( response ) {
			if ( ! response.data ) {
				throw new Error( 'Invalid batch response.' );
			}

			progressBar.value = response.data.percentage;
			progressBar.textContent = response.data.percentage + '%';
			statusText.textContent = response.data.message;
			stalledRequests = response.data.stalled ? stalledRequests + 1 : 0;
			if ( response.data.completed ) {
				stopProcessing( response.data.message, response.data.reload );
				return;
			}
			if ( stalledRequests >= 3 ) {
				showRetry();
				return;
			}

			window.setTimeout( processNextBatch, config.batchDelay );
		} ).catch( function( requestError ) {
			if ( 409 === requestError.status ) {
				stopProcessing( requestError.serverMessage || config.failedMessage, true );
				return;
			}
			if ( 403 === requestError.status ) {
				stopProcessing( requestError.serverMessage || config.failedMessage, false );
				return;
			}
			showRetry( requestError.serverMessage );
		} ).finally( function() {
			window.clearTimeout( requestTimer );
		} );
	}

	window.addEventListener( 'beforeunload', warnBeforeLeaving );
	processNextBatch();
}() );

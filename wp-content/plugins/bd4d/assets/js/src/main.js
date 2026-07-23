window.bd4d = {
	getRecaptchaToken: function() {

		// reCAPTCHA v3 tokens expire ~2 minutes after they are generated, so we
		// must mint a fresh one at submit time rather than once at page load.
		// Resolves to an empty string if reCAPTCHA is unavailable (adblock,
		// script load failure) so the request still goes through and the server
		// decides how to handle a missing token.
		return new Promise( function( resolve ) {
			if ( ! window.grecaptcha || ! localize.sitekey ) {
				resolve( '' );
				return;
			}
			grecaptcha.ready( function() {
				grecaptcha
					.execute( localize.sitekey, { action: 'validate_captcha' } )
					.then( resolve )
					.catch( function() {
						resolve( '' );
					} );
			} );
		} );
	},

	processSubscription: function( event ) {
		const submitButton = document.querySelector( 'input[type="submit"]' );
		const originalButtonText = submitButton.value;
		submitButton.value = 'Sending...';
		submitButton.classList.add( 'is-loading' );
		submitButton.setAttribute( 'disabled', 'disabled' );

		event.preventDefault();

		let data = {
			_ajax_nonce: localize._ajax_nonce,
			action: 'send_message'
		};

		const firstName = event.target.querySelector( 'input[name="first_name"]' ).value.trim();
		const lastName = event.target.querySelector( 'input[name="last_name"]' ).value.trim();
		const emailAddress = event.target.querySelector( 'input[name="email"]' ).value.trim();
		const affiliation = event.target.querySelector( 'input[name="affiliation"]' ).value.trim();
		const message = event.target.querySelector( 'textarea[name="message"]' ).value.trim();
		const newsletter = event.target.querySelector( 'input[name="newsletter"]' );
		const endorser = event.target.querySelector( 'input[name="endorser"]' );
		const adoption = event.target.querySelector( 'input[name="adoption"]' );

		if ( emailAddress ) {
			data.email = emailAddress;
		}

		if ( firstName ) {
			data.first_name = firstName;
		}

		if ( lastName ) {
			data.last_name = lastName;
		}

		if ( affiliation ) {
			data.affiliation = affiliation;
		}

		if ( message ) {
			data.message = message;
		}

		if ( newsletter.checked ) {
			data.newsletter = newsletter.value;
		}

		if ( endorser.checked ) {
			data.endorser = endorser.value;
		}

		if ( adoption.checked ) {
			data.adoption = adoption.value;
		}

		if ( ( newsletter.checked || endorser.checked || adoption.checked ) && ! emailAddress ) {

			// An opt-in checkbox requires an email. Surface it via the browser's
			// native validation popup on the email field — the same UX as the
			// required first/last name fields — rather than the message below.
			const emailInput = event.target.querySelector( 'input[name="email"]' );
			emailInput.setCustomValidity( localize.error_codes[10] );
			emailInput.reportValidity();
			window.bd4d.resetButton( submitButton, originalButtonText );
			return;
		}

		// Fetch a fresh reCAPTCHA token, then send. Doing this at submit time
		// (rather than at page load) avoids expired-token failures for people
		// who spend more than ~2 minutes filling out the form.
		window.bd4d.getRecaptchaToken().then( ( token ) => {
			data.token = token;

			jQuery.ajax( {
				type: 'POST',
				url: localize._ajax_url,
				data: data,
				timeout: 30000,
				success: ( res ) => {
					if ( true === res.success ) {
						event.target.querySelector( '.error-message' ).textContent = '';

						jQuery( event.target.querySelector( '.form-fields' ) ).slideUp();
						document
							.querySelectorAll( '#joinbd4dnet .et_pb_text_inner,#joinbd4dnet .form-fields' )
							.forEach( ( item ) => item.classList.add( 'hidden' ) );
						event.target.querySelector( '.message' ).classList.remove( 'hidden' );

						// Pick the success message: any opt-in checked -> "watch your
						// inbox"; otherwise it depends on whether a comment was given.
						let messageVariant;
						if ( newsletter.checked || endorser.checked || adoption.checked ) {
							messageVariant = '.msg-optin';
						} else if ( message ) {
							messageVariant = '.msg-comment';
						} else {
							messageVariant = '.msg-nocomment';
						}
						event.target
							.querySelectorAll( '.message ' + messageVariant )
							.forEach( ( item ) => item.classList.remove( 'hidden' ) );
					} else {
						let errorMessage = localize.error_codes[res?.data?.error_code];
						if ( 4 === res?.data?.error_code ) {

							// 4 is a JSON parsing error.
							errorMessage += ` (${res?.data?.error_message})`;
						}
						window.bd4d.showError( submitButton, originalButtonText, event.target, errorMessage );
					}
				},
				error: ( jqXHR, textStatus ) => {

					// Any transport-level failure (HTTP 4xx/5xx, timeout, network drop)
					// lands here. Without this handler the button hangs on "Sending...".
					let errorMessage = localize.error_codes[3]; // SEND_ERROR: 'Unable to send message'.
					if ( 'timeout' === textStatus ) {
						errorMessage = 'The request timed out. Please try again.';
					} else if ( 403 === jqXHR?.status ) {

						// Usually a stale/expired security token — a fresh page load fixes it.
						errorMessage = 'Your session expired. Please refresh the page and try again.';
					}
					window.bd4d.showError( submitButton, originalButtonText, event.target, errorMessage );
				}
			} );
		} );
	},

	emailFieldHandler: function( event ) {

		// Clear the email field's validation state as the user types, so a prior
		// "email required" popup or error styling doesn't persist.
		event.target.setCustomValidity( '' );
		event.target.classList.remove( 'has-error' );
	},

	messageFieldHandler: function( event ) {
		if ( event.target.value.trim() ) {
			window.bd4d.messageField.classList.remove( 'has-error' );
		}
	},

	resetButton: function( button, originalText ) {
		button.value = originalText;
		button.classList.remove( 'is-loading' );
		button.removeAttribute( 'disabled' );
	},

	showError: function( button, originalText, form, errorMessage ) {
		const errorDiv = form.querySelector( '.error-message' );

		// Show "Failed!" on button briefly.
		button.value = 'Failed!';
		button.classList.remove( 'is-loading' );
		button.classList.add( 'is-error' );

		// Show and highlight error message.
		errorDiv.textContent = errorMessage;
		errorDiv.classList.add( 'is-visible' );

		// After 2 seconds, restore button to normal state.
		setTimeout( function() {
			button.value = originalText;
			button.classList.remove( 'is-error' );
			button.removeAttribute( 'disabled' );
		}, 2000 );
	},

	setup: function() {
		const subscribeForm = document.getElementById( 'inline-subscribe' );

		if ( subscribeForm ) {
			window.bd4d.emailField = document.getElementById( 'inline-subscribe-email' );
			window.bd4d.messageField = document.getElementById( 'inline-subscribe-message' );
			window.bd4d.emailField.addEventListener( 'input', window.bd4d.emailFieldHandler );
			window.bd4d.messageField.addEventListener( 'input', window.bd4d.messageFieldHandler );

			subscribeForm.addEventListener( 'submit', window.bd4d.processSubscription );
		}
	}
};

document.addEventListener( 'DOMContentLoaded', window.bd4d.setup );

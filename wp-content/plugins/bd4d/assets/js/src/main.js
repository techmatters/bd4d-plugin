window.bd4d = {
	gcaptchaHandler: function() {
		grecaptcha.execute( localize.sitekey, { action: 'validate_captcha' } ).then( function( token ) {
			document.getElementById( 'g-recaptcha-response' ).value = token;
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
			action: 'send_message',
			token: document.getElementById( 'g-recaptcha-response' ).value
		};

		const firstName = event.target.querySelector( 'input[name="first_name"]' ).value.trim();
		const lastName = event.target.querySelector( 'input[name="last_name"]' ).value.trim();
		const emailAddress = event.target.querySelector( 'input[name="email"]' ).value.trim();
		const affiliation = event.target.querySelector( 'input[name="affiliation"]' ).value.trim();
		const message = event.target.querySelector( 'textarea[name="message"]' ).value.trim();
		const newsletter = event.target.querySelector( 'input[name="newsletter"]' );
		const supporter = event.target.querySelector( 'input[name="supporter"]' );
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

		if ( supporter.checked ) {
			data.supporter = supporter.value;
		}

		if ( adoption.checked ) {
			data.adoption = adoption.value;
		}

		if ( ( supporter.checked || newsletter.checked ) & ! emailAddress ) {
			event.target.querySelectorAll( '#inline-subscribe-email' ).forEach( ( item ) => item.classList.add( 'has-error' ) );
			event.target.querySelector( '.error-message' ).textContent = localize.error_codes[10];
			window.bd4d.resetButton( submitButton, originalButtonText );
			return;
		}

		if ( ! emailAddress && ! message ) {
			event.target
				.querySelectorAll( '#inline-subscribe-email,#inline-subscribe-message' )
				.forEach( ( item ) => item.classList.add( 'has-error' ) );
			event.target.querySelector( '.error-message' ).textContent = localize.error_codes[6];
			window.bd4d.resetButton( submitButton, originalButtonText );
			return;
		}

		jQuery.ajax( {
			type: 'POST',
			url: localize._ajax_url,
			data: data,
			success: ( res ) => {
				if ( true === res.success ) {
					event.target.querySelector( '.error-message' ).textContent = '';

					jQuery( event.target.querySelector( '.form-fields' ) ).slideUp();
					document
						.querySelectorAll( '#joinbd4dnet .et_pb_text_inner,#joinbd4dnet .form-fields' )
						.forEach( ( item ) => item.classList.add( 'hidden' ) );
					event.target.querySelector( '.message' ).classList.remove( 'hidden' );
					if ( emailAddress ) {
						event.target
							.querySelectorAll( '.message .yes-email' )
							.forEach( ( item ) => item.classList.remove( 'hidden' ) );
					} else {
						event.target
							.querySelectorAll( '.message .no-email' )
							.forEach( ( item ) => item.classList.remove( 'hidden' ) );
					}
				} else {
					let errorMessage = localize.error_codes[res?.data?.error_code];
					if ( 4 === res?.data?.error_code ) {

						// 4 is a JSON parsing error.
						errorMessage += ` (${res?.data?.error_message})`;
					}
					window.bd4d.showError( submitButton, originalButtonText, event.target, errorMessage );
				}
			}
		} );
	},

	emailFieldHandler: function( event ) {
		if ( event.target.value.trim() ) {
			window.bd4d.emailCheckbox.removeAttribute( 'disabled' );
			window.bd4d.supporterCheckbox.removeAttribute( 'disabled' );
			window.bd4d.emailField.classList.remove( 'has-error' );
		} else {
			window.bd4d.emailCheckbox.removeAttribute( 'checked' );
			window.bd4d.emailCheckbox.setAttribute( 'disabled', 'disabled' );
			window.bd4d.supporterCheckbox.removeAttribute( 'checked' );
			window.bd4d.supporterCheckbox.setAttribute( 'disabled', 'disabled' );
		}
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

		// Reset the CAPTCHA after a failure.
		window.bd4d.gcaptchaHandler();

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
			window.bd4d.emailCheckbox = document.getElementById( 'inline-subscribe-newsletter' );
			window.bd4d.supporterCheckbox = document.getElementById( 'inline-subscribe-supporter' );
			window.bd4d.emailField.addEventListener( 'input', window.bd4d.emailFieldHandler );
			window.bd4d.messageField.addEventListener( 'input', window.bd4d.messageFieldHandler );

			subscribeForm.addEventListener( 'submit', window.bd4d.processSubscription );
			if ( window.grecaptcha ) {
				grecaptcha.ready( window.bd4d.gcaptchaHandler );
			}
		}
	}
};

document.addEventListener( 'DOMContentLoaded', window.bd4d.setup );

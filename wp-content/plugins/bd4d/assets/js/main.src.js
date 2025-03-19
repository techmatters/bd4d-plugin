window.bd4d = {
	gcaptchaHandler: function() {
		grecaptcha.execute( localize.sitekey, { action: 'validate_captcha' } ).then( function( token ) {
			document.getElementById( 'g-recaptcha-response' ).value = token;
		} );
	},

	processSubscription: function( event ) {
		event.preventDefault();

		const emailAddress = event.target.querySelector( 'input[name="email"]' ).value.trim();

		let data = {
			_ajax_nonce: localize._ajax_nonce,
			action: 'send_message',
			token: document.getElementById( 'g-recaptcha-response' ).value,
			email: emailAddress
		};

		const firstName = event.target.querySelector( 'input[name="first_name"]' );
		const lastName = event.target.querySelector( 'input[name="last_name"]' );
		const affiliation = event.target.querySelector( 'input[name="affiliation"]' );
		const source = event.target.querySelector( 'select[name="source"]' );
		const message = event.target.querySelector( 'textarea[name="message"]' );
		const newsletter = event.target.querySelector( 'input[name="newsletter"]' );
		const supporter = event.target.querySelector( 'input[name="supporter"]' );

		if ( firstName ) {
			data.first_name = firstName.value;
		}

		if ( lastName ) {
			data.last_name = lastName.value;
		}

		if ( affiliation ) {
			data.affiliation = affiliation.value;
		}

		if ( source ) {
			data.source = [ ...source.selectedOptions ].map( ( o ) => o.value );
		}

		if ( message ) {
			data.message = message.value;
		}

		if ( newsletter.checked ) {
			data.newsletter = newsletter.value;
		}

		if ( supporter.checked ) {
			data.supporter = supporter.value;
		}

		jQuery.ajax( {
			type: 'POST',
			url: localize._ajax_url,
			data: data,
			success: ( res ) => {
				if ( true === res.success ) {
					jQuery( event.target.querySelector( '.form-fields' ) ).slideUp();
					document
						.querySelectorAll( '#joinbd4dnet .et_pb_text_inner,#joinbd4dnet .form-fields' )
						.forEach( ( item ) => item.classList.remove( 'hidden' ) );
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
					event.target.querySelector( '.error-message' ).textContent = errorMessage;

					// Reset the CAPTCHA after a failure.
					window.bd4d.gcaptchaHandler();
				}
			}
		} );
	},

	emailFieldHandler: function( event ) {
		if ( event.target.value ) {
			window.bd4d.emailCheckbox.removeAttribute( 'disabled' );
			window.bd4d.supporterCheckbox.removeAttribute( 'disabled' );
		} else {
			window.bd4d.emailCheckbox.removeAttribute( 'checked' );
			window.bd4d.emailCheckbox.setAttribute( 'disabled', 'disabled' );
			window.bd4d.supporterCheckbox.removeAttribute( 'checked' );
			window.bd4d.supporterCheckbox.setAttribute( 'disabled', 'disabled' );
		}
	},

	setup: function() {
		const subscribeForm = document.getElementById( 'inline-subscribe' );

		if ( subscribeForm ) {
			window.bd4d.emailField = document.getElementById( 'inline-subscribe-email' );
			window.bd4d.emailCheckbox = document.getElementById( 'inline-subscribe-newsletter' );
			window.bd4d.supporterCheckbox = document.getElementById( 'inline-subscribe-supporter' );
			window.bd4d.emailField.addEventListener( 'input', window.bd4d.emailFieldHandler );

			subscribeForm.addEventListener( 'submit', window.bd4d.processSubscription );
			if ( window.grecaptcha ) {
				grecaptcha.ready( window.bd4d.gcaptchaHandler );
			}
		}
	}
};

document.addEventListener( 'DOMContentLoaded', window.bd4d.setup );

//# sourceMappingURL=main.src.js.map
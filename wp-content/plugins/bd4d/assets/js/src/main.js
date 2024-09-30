window.bd4d = {
	gcaptchaHandler: function() {
		grecaptcha
			.execute( localize.sitekey, { action: 'validate_captcha' } )
			.then( function( token ) {
				document.getElementById( 'g-recaptcha-response' ).value = token;
			} );
	},

	processSubscription: function( event ) {
		event.preventDefault();

		let data = {
			_ajax_nonce: localize._ajax_nonce,
			action: 'send_message',
			token: document.getElementById( 'g-recaptcha-response' ).value,
			email: event.target.querySelector( 'input[name="email"]' ).value
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
			data.source = [ ...source.selectedOptions ].map( o => o.value );
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
			success: res => {
				if ( true === res.success ) {
					jQuery( event.target.querySelector( '.form-fields' ) ).slideUp();
					document
						.querySelector( '#joinbd4dnet .et_pb_text_inner' )
						.classList.add( 'hidden' );
					event.target.querySelector( '.message' ).classList.remove( 'hidden' );
				} else {
					let errorMessage = localize.error_codes[res?.data?.error_code];
					if ( 4 === res?.data?.error_code ) {

						// 4 is a JSON parsing error.
						errorMessage += ` (${res?.data?.error_message})`;
					}
					event.target.querySelector( '.message' ).textContent = errorMessage;

					// Reset the CAPTCHA after a failure.
					window.bd4d.gcaptchaHandler();
				}
			}
		} );
	},

	setup: function() {
		const subscribeForm = document.getElementById( 'inline-subscribe' );

		if ( subscribeForm ) {
			subscribeForm.addEventListener( 'submit', window.bd4d.processSubscription );
			if ( window.grecaptcha ) {
				grecaptcha.ready( window.bd4d.gcaptchaHandler );
			}
		}
	}
};

document.addEventListener( 'DOMContentLoaded', window.bd4d.setup );

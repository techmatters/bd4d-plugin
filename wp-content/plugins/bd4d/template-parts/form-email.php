<?php
/**
 * The template for displaying inline contact forms.
 *
 * @package BD4D
 * @since   1.0.0
 */

?>
<form id="inline-subscribe" class="newsletter-subscribe" method="post">
	<div class="form-fields">
		<div class="signup-input">
			<label class="screen-reader" for="inline-subscribe-first-name">First Name</label><input id="inline-subscribe-first-name" type="text" name="first_name" placeholder="First name *" required="required" />
		</div>
		<div class="signup-input">
			<label class="screen-reader" for="inline-subscribe-last-name">Last Name</label><input id="inline-subscribe-last-name" type="text" name="last_name" placeholder="Last name *"  required="required" />
		</div>
		<div class="signup-input">
			<label class="screen-reader" for="inline-subscribe-email">Email address</label><input id="inline-subscribe-email" type="email" name="email" placeholder="Email address" />
		</div>
		<div class="signup-input">
			<label class="screen-reader" for="inline-subscribe-affiliation">Affiliation</label><input id="inline-subscribe-affiliation" type="text" name="affiliation" autocomplete="organization" placeholder="Affiliation" />
		</div>
		<div class="signup-input wide">
			<label class="screen-reader" for="inline-subscribe-message">Message</label>
			<textarea id="inline-subscribe-message" type="message" rows="5" name="message" placeholder="Comments? Feedback? Questions? How do you use data in your community?"></textarea>
		</div>
		<div class="signup-input wide">
			<label for="inline-subscribe-newsletter">
				<input id="inline-subscribe-newsletter" disabled type="checkbox" name="newsletter" value="true" />
				Yes! Sign me up to receive email updates.
			</label>
		</div>
		<div class="signup-input wide">
			<label for="inline-subscribe-supporter">
				<input id="inline-subscribe-supporter" disabled type="checkbox" name="supporter" value="true" />
				Yes! I agree to be publicly acknowledged as a supporter of the Better Deal for Data.
			</label>
		</div>


		<div class="signup-input wide buttons">
			<input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response">
			<input type="submit" class="signup g-rec≈ptcha" value="Send Message" />
		</div>
	</div>
	<div class="error-message"></div>
	<div class="message hidden">
		<p>Thank you for taking the time to visit A Better Deal for Data.</p>

		<p>We’re excited for your interest in the movement to unlock the full potential of data to serve society!</p>

		<p class="no-email hidden">We’ve received your comments, and welcome any additional ideas or questions. If you choose to sign up for email updates in the future, please send us a message at <a href="mailto:info@bd4d.org">info@bd4d.org</a>.</p>

		<p class="no-email hidden">The latest news will always be on our website, and we look forward to your next visit!</p>

		<p class="yes-email hidden">We’ve received your information, so please watch your inbox for a quick confirmation. We look forward to keeping you updated as our community grows! If you have additional ideas, questions, or a data story to share, please send us a message at <a href="mailto:info@bd4d.org">info@bd4d.org</a>.</p>

	</div>
</form>

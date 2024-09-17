<?php
/**
 * The template for displaying inline newsletter forms.
 *
 * @package LandPKS
 * @since   1.0.0
 */

?>
<form id="inline-subscribe" class="newsletter-subscribe" method="post">
	<div class="form-fields">
		<div class="signup-input">
			<label class="screen-reader" for="inline-subscribe-first-name">First Name</label><input id="inline-subscribe-first-name" type="text" name="first_name" placeholder="First name">
		</div>
		<div class="signup-input">
			<label class="screen-reader" for="inline-subscribe-last-name">Last Name</label><input id="inline-subscribe-last-name" type="text" name="last_name" placeholder="Last name">
		</div>
		<div class="signup-input">
			<label class="screen-reader" for="inline-subscribe-email">Email address</label><input id="inline-subscribe-email" type="email" name="email" placeholder="Email address *" required="required">
		</div>
		<div class="signup-input">
			<label class="screen-reader" for="inline-subscribe-affiliation">Affiliation</label><input id="inline-subscribe-affiliation" type="text" name="affiliation" autocomplete="organization" placeholder="Affiliation">
		</div>
		<div class="signup-input wide">
			<label class="screen-reader" for="inline-subscribe-affiliation">Message</label><textarea id="inline-subscribe-message" type="message" rows="5" name="message" placeholder="What is your interest in BD4D? Do you want to contribute a use case or give feedback on our writing?"></textarea>
		</div>
		<div class="signup-input wide">
			<label for="inline-subscribe-newsletter"><input id="inline-subscribe-newsletter" type="checkbox" name="newsletter" value="true"> Subscribe to the BD4D Newsletter</label>
		</div>
		<div class="signup-input wide">
			<label for="inline-subscribe-supporter"><input id="inline-subscribe-supporter" type="checkbox" name="supporter" value="true"> Publicly acknowledge me as a BD4D supporter</label>
		</div>


		<div class="signup-input wide buttons">
			<input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response">
			<input type="submit" class="signup g-rec≈ptcha" value="Send Message" />
		</div>
	</div>
	<div class="et_pb_bg_layout_dark message"></div>
</form>

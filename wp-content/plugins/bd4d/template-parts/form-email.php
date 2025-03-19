<?php
/**
 * The template for displaying inline contact forms.
 *
 * @package LandPKS
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
			<label class="screen-reader" for="inline-subscribe-email">Email address</label><input id="inline-subscribe-email" type="email" name="email" placeholder="Email address *" required="required" />
		</div>
		<div class="signup-input">
			<label class="screen-reader" for="inline-subscribe-affiliation">Affiliation</label><input id="inline-subscribe-affiliation" type="text" name="affiliation" autocomplete="organization" placeholder="Affiliation" />
		</div>
		<div class="signup-input wide">
			<label for="inline-subscribe-source">How did you hear about A Better Deal For Data?</label>
			<select id="inline-subscribe-source" name="source">
				<option value="">Select one</option>
				<?php foreach ( BD4D::SOURCES as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="signup-input wide">
			<label class="screen-reader" for="inline-subscribe-message">Message</label>
			<textarea id="inline-subscribe-message" type="message" rows="5" name="message" placeholder="Comments? Feedback? Questions? How do you use data in your community?"></textarea>
		</div>
		<div class="signup-input wide">
			<label for="inline-subscribe-newsletter">
				<input id="inline-subscribe-newsletter" disabled type="checkbox" name="newsletter" value="true" />
				Yes! Sign me up to receive email updates
			</label>
		</div>
		<div class="signup-input wide">
			<label for="inline-subscribe-supporter">
				<input id="inline-subscribe-supporter" disabled type="checkbox" name="supporter" value="true" />
				Tell me more about the Coalition of the Willing!
			</label>
		</div>


		<div class="signup-input wide buttons">
			<input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response">
			<input type="submit" class="signup g-rec≈ptcha" value="Send Message" />
		</div>
	</div>
	<div class="message hidden">
	<p>Thank you for joining us—we’re excited to include you in A Better Deal for Data!</p>

	<p>If you’ve opted in to receive email updates from Better Deal for Data, we look forward to sharing occasional program announcements, events, and new posts and publications.</p>

	<p>And, if you requested more information on our Coalition of the Willing, and included your email address, we will reach out to you directly within three business days.</p>

	<p>More questions? Please email us at <a href="mailto:info@bd4d.org">info@bd4d.org</a>.</p>
	</div>
</form>

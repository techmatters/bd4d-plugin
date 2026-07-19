<?php
/**
 * The template for email auto replies.
 *
 * @package BD4D
 * @since   1.0.0
 */

?>
<?php if ( $adoption ) : ?>
Hello, and welcome to the Better Deal for Data community–we're excited to support your organization in adopting the BD4D Standard!

We'd love to learn more about how you work with data for good, and answer any questions you have about the BD4D Commitments, BD4D Playbook, or implementation. We will contact you personally within the next two business days.

In the meantime, please don't hesitate to contact us at info@bd4d.org to share any feedback or ideas.

Many thanks,
<?php else : ?>
Hello, and thank you for joining the Better Deal for Data community!

<?php if ( $endorser ) : // phpcs:ignore Generic.WhiteSpace.ScopeIndent -- Email template, indentation appears in output. ?>
We're grateful for your endorsement of the BD4D Standard, and excited to welcome you to this movement to unlock the full potential of data to serve society.
<?php else : // phpcs:ignore Generic.WhiteSpace.ScopeIndent ?>
We're excited to welcome you to the movement to unlock the full potential of data to serve society.
<?php endif; // phpcs:ignore Generic.WhiteSpace.ScopeIndent ?>

<?php if ( $newsletter && ! $endorser ) : // phpcs:ignore Generic.WhiteSpace.ScopeIndent -- Email template, indentation appears in output. ?>
This message is to confirm that you have subscribed to our email updates via our website. If you wish to unsubscribe from our email updates, please reply to this email with the word "Unsubscribe."

We'd love to hear your feedback, questions, or stories about data! You can reach us at info@bd4d.org.
<?php elseif ( ! $newsletter && $endorser ) : // phpcs:ignore Generic.WhiteSpace.ScopeIndent ?>
This message is to confirm that we have your permission to display your name and affiliation as a BD4D Endorser on our website. We will contact you if we have additional questions regarding your endorsement.

If you would like to sign up for our email newsletter in the future, or if you have feedback, questions, or a data story to share, please do send us a message at info@bd4d.org. We'd love to hear from you!
<?php elseif ( $newsletter && $endorser ) : // phpcs:ignore Generic.WhiteSpace.ScopeIndent ?>
This message is to confirm:
• We have permission to display your name and affiliation as a BD4D Endorser on our website; and
• You have subscribed to our email updates via our website. If you wish to unsubscribe from our email updates, please reply to this email with the word "Unsubscribe."

We'd love to hear your feedback, questions, or stories about data! You can reach us at info@bd4d.org.
<?php endif; // phpcs:ignore Generic.WhiteSpace.ScopeIndent ?>

All the best,
<?php endif; ?>
Celine

–
M Celine Takatsuno
Better Deal for Data, a Tech Matters initiative.
Tech Matters is a nonprofit tech organization.

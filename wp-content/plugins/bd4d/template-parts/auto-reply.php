<?php
/**
 * The template for email auto replies.
 *
 * @package BD4D
 * @since   1.0.0
 */

?>Hello, and thank you for joining the Better Deal for Data community!

<?php if ( $supporter ) : ?>
We’re excited to welcome you to the movement to unlock the full potential of data to serve society, and add your name to our growing list of public supporters as part of our Coalition of the Willing.
<?php else : ?>
We’re excited to welcome you to the movement to unlock the full potential of data to serve society. 
<?php endif; ?>

<?php if ( $newsletter && ! $supporter ) : ?>
This message is to confirm that you have subscribed to our email updates via our website. If you wish to unsubscribe from our email updates, please reply to this email with the word “Unsubscribe.”

We’d love to hear your feedback, questions, or stories about data! You can reach us at info@bd4d.org.
<?php elseif ( ! $newsletter && $supporter && ! $comment ) : ?>
This message is to confirm that we have your permission to display your name and affiliation on our website. If you choose to sign up for email updates in the future, or If you have feedback, questions, or a data story to share, please send us a message at info@bd4d.org. We’d love to hear from you!
<?php else : // newsletter && supporter && comment. ?>
This message is to confirm that we have your permission to contact you via email. If you no longer want to receive email from A Better Deal for Data, please reply to this email with the word “Unsubscribe.”

We’d love to hear your feedback, questions, or stories about data! You can reach us at info@bd4d.org.
<?php endif; ?>

All the best,
Celine

–
M Celine Takatsuno
A Better Deal for Data, a Tech Matters initiative.
Tech Matters is a nonprofit tech organization.

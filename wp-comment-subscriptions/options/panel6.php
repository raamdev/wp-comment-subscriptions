<?php
// Avoid direct access to this piece of code
if (!function_exists('is_admin') || !is_admin()){
	header('Location: /');
	exit;
}
?>
<h3><?php _e('Support the author','wp-comment-subscriptions') ?></h3>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
	<input type="hidden" name="cmd" value="_s-xclick">
	<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
">
	<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
	<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>

<p><?php _e('How valuable is the feature offered by this plugin to your visitors? WP Comment Subscriptions is and will always be free, but consider supporting the author if this plugin made your web site better. Any donation received will be reinvested in the development of WP Comment Subscriptions.','wp-comment-subscriptions') ?></p>

<h3><?php _e("Don't feel like donating? You can still help!",'wp-comment-subscriptions') ?></h3>
<p><?php _e("If you don't want to donate money, you can also contribute by donating your time: <file bug reports, submit localization files, or share ideas for improving WP Comment Subscriptions!",'wp-comment-subscriptions') ?></p>

<h3><?php _e("Vote and show your appreciation",'wp-comment-subscriptions') ?></h3>
<p><?php _e('If this plugin works for you, please let others know and  <a href="http://wordpress.org/extend/plugins/wp-comment-subscriptions/">Rate it</a> on its Plugin Directory page.','wp-comment-subscriptions') ?></p>
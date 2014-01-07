<?php
// Avoid direct access to this piece of code
if (!function_exists('is_admin') || !is_admin()){
	header('Location: /');
	exit;
}

// Update options
if (isset($_POST['options'])){
	$faulty_fields = '';
	if (isset($_POST['options']['purge_days']) && !wp_comment_subscriptions_update_option('purge_days', $_POST['options']['purge_days'], 'integer')) $faulty_fields = __('Autopurge requests','wp-comment-subscriptions').', ';
	if (isset($_POST['options']['enable_double_check']) && !wp_comment_subscriptions_update_option('enable_double_check', $_POST['options']['enable_double_check'], 'yesno')) $faulty_fields = __('Enable double check','wp-comment-subscriptions').', ';
	if (isset($_POST['options']['notify_authors']) && !wp_comment_subscriptions_update_option('notify_authors', $_POST['options']['notify_authors'], 'yesno')) $faulty_fields = __('Subscribe authors','wp-comment-subscriptions').', ';
	if (isset($_POST['options']['enable_html_emails']) && !wp_comment_subscriptions_update_option('enable_html_emails', $_POST['options']['enable_html_emails'], 'yesno')) $faulty_fields = __('Enable HTML emails','wp-comment-subscriptions').', ';
	if (isset($_POST['options']['process_trackbacks']) && !wp_comment_subscriptions_update_option('process_trackbacks', $_POST['options']['process_trackbacks'], 'yesno')) $faulty_fields = __('Send trackbacks','wp-comment-subscriptions').', ';
	if (isset($_POST['options']['enable_admin_messages']) && !wp_comment_subscriptions_update_option('enable_admin_messages', $_POST['options']['enable_admin_messages'], 'yesno')) $faulty_fields = __('Notify admin','wp-comment-subscriptions').', ';
	if (isset($_POST['options']['admin_subscribe']) && !wp_comment_subscriptions_update_option('admin_subscribe', $_POST['options']['admin_subscribe'], 'yesno')) $faulty_fields = __('Let admin subscribe','wp-comment-subscriptions').', ';
	if (isset($_POST['options']['admin_bcc']) && !wp_comment_subscriptions_update_option('admin_bcc', $_POST['options']['admin_bcc'], 'yesno')) $faulty_fields = __('BCC admin on Notifications','wp-comment-subscriptions').', ';

	// Display an alert in the admin interface if something went wrong
	echo '<div class="updated fade"><p>';
	if (empty($faulty_fields)){
			_e('Your settings have been successfully updated.','wp-comment-subscriptions');
	}
	else{
		_e('There was an error updating the following fields:','wp-comment-subscriptions');
		echo ' <strong>'.substr($faulty_fields,0,-2).'</strong>';
	}
	echo "</p></div>\n";
}
wp_print_scripts( 'quicktags' );
?>
<form action="admin.php?page=wp-comment-subscriptions/options/index.php&subscribepanel=<?php echo $current_panel ?>" method="post">
<table class="form-table <?php echo $wp_locale->text_direction ?>">
	<tr>
		<th scope="row"><label for="purge_days"><?php _e('Autopurge requests','wp-comment-subscriptions') ?></label></th>
		<td><input type="text" name="options[purge_days]" id="purge_days" value="<?php echo wp_comment_subscriptions_get_option('purge_days'); ?>" size="10"> <?php _e('days','wp-comment-subscriptions') ?>
			<div class="description"><?php _e("Delete pending subscriptions (not confirmed) after X days. Zero disables this feature.",'wp-comment-subscriptions'); ?></div></td>
	</tr>
	<tr>
		<th scope="row"><label for="enable_double_check"><?php _e('Enable double check','wp-comment-subscriptions') ?></label></th>
		<td>
			<input type="radio" name="options[enable_double_check]" id="enable_double_check" value="yes"<?php echo (wp_comment_subscriptions_get_option('enable_double_check') == 'yes')?' checked="checked"':''; ?>> <?php _e('Yes','wp-comment-subscriptions') ?> &nbsp; &nbsp; &nbsp;
			<input type="radio" name="options[enable_double_check]" value="no" <?php echo (wp_comment_subscriptions_get_option('enable_double_check') == 'no')?'  checked="checked"':''; ?>> <?php _e('No','wp-comment-subscriptions') ?>
			<div class="description"><?php _e('Send a notification email to confirm the subscription (to avoid addresses misuse).','wp-comment-subscriptions'); ?></div></td>
	</tr>
	<tr>
		<th scope="row"><label for="notify_authors"><?php _e('Subscribe authors','wp-comment-subscriptions') ?></label></th>
		<td>
			<input type="radio" name="options[notify_authors]" id="notify_authors" value="yes"<?php echo (wp_comment_subscriptions_get_option('notify_authors') == 'yes')?' checked="checked"':''; ?>> <?php _e('Yes','wp-comment-subscriptions') ?> &nbsp; &nbsp; &nbsp;
			<input type="radio" name="options[notify_authors]" value="no" <?php echo (wp_comment_subscriptions_get_option('notify_authors') == 'no')?'  checked="checked"':''; ?>> <?php _e('No','wp-comment-subscriptions') ?>
			<div class="description"><?php _e('Automatically subscribe authors to their own articles (not retroactive).','wp-comment-subscriptions'); ?></div></td>
	</tr>
	<tr>
		<th scope="row"><label for="enable_html_emails"><?php _e('Enable HTML emails','wp-comment-subscriptions') ?></label></th>
		<td>
			<input type="radio" name="options[enable_html_emails]" id="enable_html_emails" value="yes"<?php echo (wp_comment_subscriptions_get_option('enable_html_emails') == 'yes')?' checked="checked"':''; ?>> <?php _e('Yes','wp-comment-subscriptions') ?> &nbsp; &nbsp; &nbsp;
			<input type="radio" name="options[enable_html_emails]" value="no" <?php echo (wp_comment_subscriptions_get_option('enable_html_emails') == 'no')?'  checked="checked"':''; ?>> <?php _e('No','wp-comment-subscriptions') ?>
			<div class="description"><?php _e('If enabled, will send email messages with content-type = text/html instead of text/plain','wp-comment-subscriptions'); ?></div></td>
	</tr>
	<tr>
		<th scope="row"><label for="process_trackbacks"><?php _e('Process trackbacks','wp-comment-subscriptions') ?></label></th>
		<td>
			<input type="radio" name="options[process_trackbacks]" id="process_trackbacks" value="yes"<?php echo (wp_comment_subscriptions_get_option('process_trackbacks') == 'yes')?' checked="checked"':''; ?>> <?php _e('Yes','wp-comment-subscriptions') ?> &nbsp; &nbsp; &nbsp;
			<input type="radio" name="options[process_trackbacks]" value="no" <?php echo (wp_comment_subscriptions_get_option('process_trackbacks') == 'no')?'  checked="checked"':''; ?>> <?php _e('No','wp-comment-subscriptions') ?>
			<div class="description"><?php _e('Notify users when a new trackback or pingback is added to the discussion.','wp-comment-subscriptions'); ?></div></td>
	</tr>
	<tr>
		<th scope="row"><label for="enable_admin_messages"><?php _e('Track all subscriptions','wp-comment-subscriptions') ?></label></th>
		<td>
			<input type="radio" name="options[enable_admin_messages]" id="enable_admin_messages" value="yes"<?php echo (wp_comment_subscriptions_get_option('enable_admin_messages') == 'yes')?' checked="checked"':''; ?>> <?php _e('Yes','wp-comment-subscriptions') ?> &nbsp; &nbsp; &nbsp;
			<input type="radio" name="options[enable_admin_messages]" value="no" <?php echo (wp_comment_subscriptions_get_option('enable_admin_messages') == 'no')?'  checked="checked"':''; ?>> <?php _e('No','wp-comment-subscriptions') ?>
			<div class="description"><?php _e('Notify the administrator when users subscribe without commenting.','wp-comment-subscriptions'); ?></div></td>
	</tr>
	<tr>
		<th scope="row"><label for="admin_subscribe"><?php _e('Let admin subscribe','wp-comment-subscriptions') ?></label></th>
		<td>
			<input type="radio" name="options[admin_subscribe]" id="admin_subscribe" value="yes"<?php echo (wp_comment_subscriptions_get_option('admin_subscribe') == 'yes')?' checked="checked"':''; ?>> <?php _e('Yes','wp-comment-subscriptions') ?> &nbsp; &nbsp; &nbsp;
			<input type="radio" name="options[admin_subscribe]" value="no" <?php echo (wp_comment_subscriptions_get_option('admin_subscribe') == 'no')?'  checked="checked"':''; ?>> <?php _e('No','wp-comment-subscriptions') ?>
			<div class="description"><?php _e('Let the administrator subscribe to comments when logged in.','wp-comment-subscriptions'); ?></div></td>
	</tr>
	<tr>
		<th scope="row"><label for="admin_bcc"><?php _e('BCC admin on Notifications','wp-comment-subscriptions') ?></label></th>
		<td>
			<input type="radio" name="options[admin_bcc]" id="admin_bcc" value="yes"<?php echo (wp_comment_subscriptions_get_option('admin_bcc') == 'yes')?' checked="checked"':''; ?>> <?php _e('Yes','wp-comment-subscriptions') ?> &nbsp; &nbsp; &nbsp;
			<input type="radio" name="options[admin_bcc]" value="no" <?php echo (wp_comment_subscriptions_get_option('admin_bcc') == 'no')?'  checked="checked"':''; ?>> <?php _e('No','wp-comment-subscriptions') ?>
			<div class="description"><?php _e('Send a copy of all Notifications to the administrator.','wp-comment-subscriptions'); ?></div></td>
	</tr>
</tbody>
</table>
<p class="submit"><input type="submit" value="<?php _e('Save Changes') ?>" class="button-primary" name="Submit"></p>
</form>

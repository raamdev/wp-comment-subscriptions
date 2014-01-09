<?php
// Avoid direct access to this piece of code
if (!function_exists('is_admin') || !is_admin()){
	header('Location: /');
	exit;
}

// Update options
if (isset($_POST['options'])){
	$faulty_fields = '';
	if (isset($_POST['options']['show_subscription_box']) && !wp_comment_subscriptions_update_option('show_subscription_box', $_POST['options']['show_subscription_box'], 'yesno')) $faulty_fields = __('Enable default checkbox','wp-comment-subscriptions').', ';
	if (isset($_POST['options']['checked_by_default']) && !wp_comment_subscriptions_update_option('checked_by_default', $_POST['options']['checked_by_default'], 'yesno')) $faulty_fields = __('Checked by default','wp-comment-subscriptions').', ';
	if (isset($_POST['options']['enable_advanced_subscriptions']) && !wp_comment_subscriptions_update_option('enable_advanced_subscriptions', $_POST['options']['enable_advanced_subscriptions'], 'yesno')) $faulty_fields = __('Advanced subscription','wp-comment-subscriptions').', ';
	if (isset($_POST['options']['advanced_default']) && !wp_comment_subscriptions_update_option('advanced_default', $_POST['options']['advanced_default'], 'integer')) $faulty_fields = __('Advanced default','wp-comment-subscriptions').', ';
	if (isset($_POST['options']['checkbox_inline_style']) && !wp_comment_subscriptions_update_option('checkbox_inline_style', $_POST['options']['checkbox_inline_style'], 'text-no-encode')) $faulty_fields = __('Custom inline style','wp-comment-subscriptions').', ';
	if (isset($_POST['options']['checkbox_html']) && !wp_comment_subscriptions_update_option('checkbox_html', $_POST['options']['checkbox_html'], 'text-no-encode')) $faulty_fields = __('Custom HTML','wp-comment-subscriptions').', ';

	if (isset($_POST['options']['checkbox_label']) && !wp_comment_subscriptions_update_option('checkbox_label', $_POST['options']['checkbox_label'], 'text')) $faulty_fields = __('Checkbox label','wp-comment-subscriptions').', ';
	if (isset($_POST['options']['subscribed_label']) && !wp_comment_subscriptions_update_option('subscribed_label', $_POST['options']['subscribed_label'], 'text')) $faulty_fields = __('Subscribed label','wp-comment-subscriptions').', ';
	if (isset($_POST['options']['subscribed_waiting_label']) && !wp_comment_subscriptions_update_option('subscribed_waiting_label', $_POST['options']['subscribed_waiting_label'], 'text')) $faulty_fields = __('Awaiting label','wp-comment-subscriptions').', ';
	if (isset($_POST['options']['author_label']) && !wp_comment_subscriptions_update_option('author_label', $_POST['options']['author_label'], 'text')) $faulty_fields = __('Author label','wp-comment-subscriptions').', ';

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
?>
<form action="admin.php?page=wp-comment-subscriptions/options/index.php&subscribepanel=<?php echo $current_panel ?>" method="post">
<h3><?php _e('Options','wp-comment-subscriptions') ?></h3>
<table class="form-table <?php echo $wp_locale->text_direction ?>">
<tbody>
	<tr>
		<th scope="row"><label for="show_subscription_box"><?php _e('Enable default checkbox','wp-comment-subscriptions') ?></label></th>
		<td>
			<input type="radio" name="options[show_subscription_box]" id="show_subscription_box" value="yes"<?php echo (wp_comment_subscriptions_get_option('show_subscription_box') == 'yes')?' checked="checked"':''; ?>> <?php _e('Yes','wp-comment-subscriptions') ?> &nbsp; &nbsp; &nbsp;
			<input type="radio" name="options[show_subscription_box]" value="no" <?php echo (wp_comment_subscriptions_get_option('show_subscription_box') == 'no')?'  checked="checked"':''; ?>> <?php _e('No','wp-comment-subscriptions') ?>
			<div class="description"><?php _e('Disable this option if you want to move the subscription checkbox to a different place on your page.','wp-comment-subscriptions'); ?></div></td>
	</tr>
	<tr>
		<th scope="row"><label for="checked_by_default"><?php _e('Checked by default','wp-comment-subscriptions') ?></label></th>
		<td>
			<input type="radio" name="options[checked_by_default]" id="checked_by_default" value="yes"<?php echo (wp_comment_subscriptions_get_option('checked_by_default') == 'yes')?' checked="checked"':''; ?>> <?php _e('Yes','wp-comment-subscriptions') ?> &nbsp; &nbsp; &nbsp;
			<input type="radio" name="options[checked_by_default]" value="no" <?php echo (wp_comment_subscriptions_get_option('checked_by_default') == 'no')?'  checked="checked"':''; ?>> <?php _e('No','wp-comment-subscriptions') ?>
			<div class="description"><?php _e('Decide if the checkbox should be checked by default or not.','wp-comment-subscriptions'); ?></div></td>
	</tr>
	<tr>
		<th scope="row"><label for="enable_advanced_subscriptions"><?php _e('Advanced subscription','wp-comment-subscriptions') ?></label></th>
		<td>
			<input type="radio" name="options[enable_advanced_subscriptions]" id="enable_advanced_subscriptions" value="yes"<?php echo (wp_comment_subscriptions_get_option('enable_advanced_subscriptions') == 'yes')?' checked="checked"':''; ?>> <?php _e('Yes','wp-comment-subscriptions') ?> &nbsp; &nbsp; &nbsp;
			<input type="radio" name="options[enable_advanced_subscriptions]" value="no" <?php echo (wp_comment_subscriptions_get_option('enable_advanced_subscriptions') == 'no')?'  checked="checked"':''; ?>> <?php _e('No','wp-comment-subscriptions') ?>
			<div class="description"><?php _e('Allow users to choose from different subscription types (all, replies only).','wp-comment-subscriptions'); ?></div></td>
	</tr>
	<tr>
		<th scope="row"><label for="advanced_default"><?php _e('Advanced default','wp-comment-subscriptions') ?></label></th>
		<td>
			<select name="options[advanced_default]" id='advanced_default'>
				<option value='0' <?php echo ( wp_comment_subscriptions_get_option('advanced_default') === '0') ? "selected='selected'" : ''; ?>><? _e("None",'wp-comment-subscriptions') ?></option>
				<option value='1' <?php echo ( wp_comment_subscriptions_get_option('advanced_default') === '1') ? "selected='selected'" : ''; ?>><? _e("All new comments",'wp-comment-subscriptions') ?></option>
				<option value='2' <?php echo ( wp_comment_subscriptions_get_option('advanced_default') === '2') ? "selected='selected'" : ''; ?>> <?php _e('Replies to this comment','wp-comment-subscriptions') ?></option>
			</select>
			<div class="description"><?php _e('The default subscription type that should be selected when Advanced subscriptions are enabled.','wp-comment-subscriptions'); ?></div></td>
	</tr>
	<tr>
		<th scope="row"><label for="checkbox_inline_style"><?php _e('Custom inline style','wp-comment-subscriptions') ?></label></th>
		<td><input type="text" name="options[checkbox_inline_style]" id="checkbox_inline_style" value="<?php echo wp_comment_subscriptions_get_option('checkbox_inline_style'); ?>" size="20">
			<div class="description"><?php _e('Custom inline CSS to add to the checkbox.','wp-comment-subscriptions'); ?></div></td>
	</tr>
	<tr>
		<th scope="row"><label for="checkbox_html"><?php _e('Custom HTML','wp-comment-subscriptions') ?></label></th>
		<td><input type="text" name="options[checkbox_html]" id="checkbox_html" value="<?php echo wp_comment_subscriptions_get_option('checkbox_html'); ?>" size="50">
			<div class="description"><?php _e('Custom HTML code to be used when displaying the checkbox. Allowed tags: [checkbox_field], [checkbox_label]','wp-comment-subscriptions'); ?></div></td>
	</tr>
</tbody>
</table>

<h3><?php _e('Messages for your visitors','wp-comment-subscriptions') ?></h3>
<table class="form-table <?php echo $wp_locale->text_direction ?>">
<tbody>
	<tr>
		<th scope="row"><label for="checkbox_label"><?php _e('Default label','wp-comment-subscriptions') ?></label></th>
		<td><input type="text" name="options[checkbox_label]" id="checkbox_label" value="<?php echo wp_comment_subscriptions_get_option('checkbox_label'); ?>" size="70">
			<div class="description"><?php _e('Label associated to the checkbox. Allowed tag: [subscribe_link]','wp-comment-subscriptions'); ?></div></td>
	</tr>
	<tr>
		<th scope="row"><label for="subscribed_label"><?php _e('Subscribed label','wp-comment-subscriptions') ?></label></th>
		<td><input type="text" name="options[subscribed_label]" id="subscribed_label" value="<?php echo wp_comment_subscriptions_get_option('subscribed_label'); ?>" size="70">
			<div class="description"><?php _e('Label shown to those who are already subscribed to a post. Allowed tag: [manager_link]','wp-comment-subscriptions'); ?></div></td>
	</tr>
	<tr>
		<th scope="row"><label for="subscribed_waiting_label"><?php _e('Pending label','wp-comment-subscriptions') ?></label></th>
		<td><input type="text" name="options[subscribed_waiting_label]" id="subscribed_waiting_label" value="<?php echo wp_comment_subscriptions_get_option('subscribed_waiting_label'); ?>" size="70">
			<div class="description"><?php _e("Label shown to those who are already subscribed, but haven't clicked on the confirmation link yet. Allowed tag: [manager_link]",'wp-comment-subscriptions'); ?></div></td>
	</tr>
	<tr>
		<th scope="row"><label for="author_label"><?php _e('Author label','wp-comment-subscriptions') ?></label></th>
		<td><input type="text" name="options[author_label]" id="author_label" value="<?php echo wp_comment_subscriptions_get_option('author_label'); ?>" size="70">
			<div class="description"><?php _e('Label shown to authors (and administrators). Allowed tag: [manager_link]','wp-comment-subscriptions'); ?></div></td>
	</tr>
</tbody>
</table>
<p class="submit"><input type="submit" value="<?php _e('Save Changes') ?>" class="button-primary" name="Submit"></p>
</form>
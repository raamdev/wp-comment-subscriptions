<?php
if (!function_exists('is_admin') || !is_admin()){
	header('Location: /');
	exit;
}
?>
<div class="postbox">
<h3><?php _e('Update Subscription','wp-comment-subscriptions') ?></h3>
<form action="options-general.php?page=wp-comment-subscriptions/options/index.php&subscribepanel=1" method="post" id="update_address_form"
	onsubmit="if (this.sre.value != '<?php _e('optional','wp-comment-subscriptions') ?>') return confirm('<?php _e('Please remember: this operation cannot be undone. Are you sure you want to proceed?', 'wp-comment-subscriptions') ?>')">
<fieldset style="border:0">
<p><?php _e('Post:','wp-comment-subscriptions'); echo ' <strong>'.get_the_title(intval($_GET['srp']))." ({$_GET['srp']})"; ?></strong></p>
<p class="liquid"><label for='oldsre'><?php _e('From','wp-comment-subscriptions') ?></label> <input readonly='readonly' type='text' size='30' name='oldsre' id='oldsre' value='<?php echo $_GET['sre'] ?>' /></p>
<p class="liquid"><label for='sre'><?php _e('To','wp-comment-subscriptions') ?></label> <input type='text' size='30' name='sre' id='sre' value='<?php _e('optional','wp-comment-subscriptions') ?>' style="color:#ccc"
		onfocus='if (this.value == "<?php _e('optional','wp-comment-subscriptions') ?>") this.value="";this.style.color="#000"'
		onblur='if (this.value == ""){this.value="<?php _e('optional','wp-comment-subscriptions') ?>";this.style.color="#ccc"}'/></p>
<p class="liquid"><label for='srs'><?php _e('Status','wp-comment-subscriptions') ?></label>
	<select name="srs" id="srs">
		<option value=''><?php _e('Keep unchanged','wp-comment-subscriptions') ?></option>
		<option value='Y'><?php _e('Active','wp-comment-subscriptions') ?></option>
		<option value='R'><?php _e('Replies only','wp-comment-subscriptions') ?></option>
		<option value='C'><?php _e('Suspended','wp-comment-subscriptions') ?></option>
	</select> <input type='submit' class='subscribe-form-button' value='<?php _e('Update','wp-comment-subscriptions') ?>' /></p>
<input type='hidden' name='sra' value='edit'/>
<input type='hidden' name='srp' value='<?php echo intval($_GET['srp']) ?>'/>
</fieldset>
</form>
</div>
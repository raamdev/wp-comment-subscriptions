<?php
// Avoid direct access to this piece of code
if (!function_exists('add_action')){
	header('Location: /');
	exit;
}

global $wp_comment_subscriptions;

ob_start();
if (!empty($_POST['post_list'])){
	$post_list = array();
	foreach($_POST['post_list'] as $a_post_id){
		if (!in_array($a_post_id, $post_list))
			$post_list[] = intval($a_post_id);
	}

	$action = !empty($_POST['sra'])?$_POST['sra']:(!empty($_GET['sra'])?$_GET['sra']:'');
	switch($action){
		case 'delete':
			$rows_affected = $wp_comment_subscriptions->delete_subscriptions($post_list, $email);
			echo '<p class="updated">'.__('Subscriptions deleted:', 'wp-comment-subscriptions')." $rows_affected</p>";
			break;
		case 'suspend':
			$rows_affected = $wp_comment_subscriptions->update_subscription_status($post_list, $email, 'C');
			echo '<p class="updated">'.__('Subscriptions suspended:', 'wp-comment-subscriptions')." $rows_affected</p>";
			break;
		case 'activate':
			$rows_affected = $wp_comment_subscriptions->update_subscription_status($post_list, $email, '-C');
			echo '<p class="updated">'.__('Subscriptions activated:', 'wp-comment-subscriptions')." $rows_affected</p>";
			break;
		case 'force_r':
			$rows_affected = $wp_comment_subscriptions->update_subscription_status($post_list, $email, 'R');
			echo '<p class="updated">'.__('Subscriptions updated:', 'wp-comment-subscriptions')." $rows_affected</p>";
			break;
		default:
			break;
	}
}
$message = html_entity_decode(stripslashes(get_option('wp_comment_subscriptions_user_text')), ENT_COMPAT, 'UTF-8');
if(function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage'))
	$message = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($message);
echo "<p>$message</p>";
?>

<form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']) ?>" method="post" id="post_list_form" name="post_list_form" onsubmit="if(this.sra[0].checked) return confirm('<?php _e('Please remember: this operation cannot be undone. Are you sure you want to proceed?', 'wp-comment-subscriptions') ?>')">
<fieldset style="border:0">
<?php
	$subscriptions = $wp_comment_subscriptions->get_subscriptions('email', 'equals', $email, 'dt', 'DESC');
	if (is_array($subscriptions) && !empty($subscriptions)){
		echo '<p id="wp-comment-subscriptions-email-p">'.__('Email','wp-comment-subscriptions').': <strong>'.$email.'</strong></p>';
		echo '<p id="wp-comment-subscriptions-legend-p">'.__('Legend: Y = all comments, R = replies only, C = inactive', 'wp-comment-subscriptions').'</p>';
		echo '<ul id="wp-comment-subscriptions-list">';
		foreach($subscriptions as $a_subscription){
			$permalink = get_permalink($a_subscription->post_id);
			$title = get_the_title($a_subscription->post_id);
			echo "<li><label for='post_{$a_subscription->post_id}'><input type='checkbox' name='post_list[]' value='{$a_subscription->post_id}' id='post_{$a_subscription->post_id}'/> <span class='subscribe-column-1'>$a_subscription->dt</span> <span class='subscribe-separator subscribe-separator-1'>&mdash;</span> <span class='subscribe-column-2'>{$a_subscription->status}</span> <span class='subscribe-separator subscribe-separator-2'>&mdash;</span> <a class='subscribe-column-3' href='$permalink'>$title</a></label></li>\n";
		}
		echo '</ul>';
		echo '<p id="wp-comment-subscriptions-select-all-p"><a class="wp-comment-subscriptions-small-button" href="#" onclick="t=document.forms[\'post_list_form\'].elements[\'post_list[]\'];c=t.length;if(!c){t.checked=true}else{for(var i=0;i<c;i++){t[i].checked=true}};return false;">'.__('Select all','wp-comment-subscriptions').'</a> ';
		echo '<a class="wp-comment-subscriptions-small-button" href="#" onclick="t=document.forms[\'post_list_form\'].elements[\'post_list[]\'];c=t.length;if(!c){t.checked=!t.checked}else{for(var i=0;i<c;i++){t[i].checked=false}};return false;">'.__('Invert selection','wp-comment-subscriptions').'</a></p>';
		echo '<p id="wp-comment-subscriptions-action-p">'.__('Action:','wp-comment-subscriptions').'
			<input type="radio" name="sra" value="delete" id="action_type_delete" /> <label for="action_type_delete">'.__('Delete','wp-comment-subscriptions').'</label> &nbsp;&nbsp;&nbsp;&nbsp;
			<input type="radio" name="sra" value="suspend" id="action_type_suspend" checked="checked" /> <label for="action_type_suspend">'.__('Suspend','wp-comment-subscriptions').'</label> &nbsp;&nbsp;&nbsp;&nbsp;
			<input type="radio" name="sra" value="force_r" id="action_type_force_y" /> <label for="action_type_force_y">'.__('Replies to my comments','wp-comment-subscriptions').'</label> &nbsp;&nbsp;&nbsp;&nbsp;
			<input type="radio" name="sra" value="activate" id="action_type_activate" /> <label for="action_type_activate">'.__('Activate','wp-comment-subscriptions').'</label></p>';
		echo '<p id="wp-comment-subscriptions-update-p"><input type="submit" class="subscribe-form-button" value="'.__('Update subscriptions','wp-comment-subscriptions').'" /><input type="hidden" name="sre" value="'.urlencode($email).'"/></p>';
		
	}
	else{
		echo '<p>'.__('No subscriptions match your search criteria.', 'wp-comment-subscriptions').'</p>';
	}
?>
</fieldset>
</form>
<?php
$output = ob_get_contents();
ob_end_clean();
return $output;
?>
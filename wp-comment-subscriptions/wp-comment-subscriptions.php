<?php
/*
Plugin Name: WP Comment Subscriptions

Version: 140109
Stable tag: 140109
Requires at least: 2.9.2
Tested up to: 3.8

Plugin URI: http://wordpress.org/extend/plugins/wp-comment-subscriptions/
Description: Give commenters the ability to receive e-mail notifications for all new comment replies or only replies to their comments.

Text Domain: wp-comment-subscriptions
Domain Path: /langs

Author: Raam Dev
Author URI: http://raamdev.com/
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=82JLPADVDAB2Q
Contributors: raamdev

License: GNU General Public License v2 or later.

*/

// Avoid direct access to this piece of code
if (!function_exists('add_action')){
	header('Location: /');
	exit;
}

/**
 * Displays the checkbox to allow visitors to subscribe
 */
function wp_comment_subscriptions_show(){
	global $post, $wp_comment_subscriptions;

	$is_disabled = get_post_meta($post->ID, 'stcr_disable_subscriptions', true);
	if (!empty($is_disabled))
		return $_comment_ID;

	$show_subscription_box = true;
	$html_to_show = '';
	$user_link = get_bloginfo('url').get_option('wp_comment_subscriptions_manager_page', '');

	if (function_exists('qtrans_convertURL'))
		$user_link = qtrans_convertURL($user_link);

	$manager_link = (strpos($user_link, '?') !== false)?"$user_link&amp;srp=$post->ID":"$user_link?srp=$post->ID";

	// Load localization files
	load_plugin_textdomain('wp-comment-subscriptions', WP_PLUGIN_DIR .'/wp-comment-subscriptions/langs', '/wp-comment-subscriptions/langs');

	if($wp_comment_subscriptions->is_user_subscribed($post->ID, '', 'C')){
		$html_to_show = str_replace('[manager_link]', $user_link,
			html_entity_decode(stripslashes(get_option('wp_comment_subscriptions_subscribed_waiting_label', __("Your subscription to this post needs to be confirmed. <a href='[manager_link]'>Manage your subscriptions</a>.",'wp-comment-subscriptions'))), ENT_COMPAT, 'UTF-8'));
		$show_subscription_box = false;
	}
	elseif($wp_comment_subscriptions->is_user_subscribed($post->ID, '')){
		$html_to_show = str_replace('[manager_link]', $user_link,
			html_entity_decode(stripslashes(get_option('wp_comment_subscriptions_subscribed_label', __("You are subscribed to this post. <a href='[manager_link]'>Manage</a> your subscriptions.",'wp-comment-subscriptions'))), ENT_COMPAT, 'UTF-8'));
		$show_subscription_box = false;
	}

	if ($wp_comment_subscriptions->is_author($post->post_author)){	// when the second parameter is empty, cookie value will be used
		if (get_option('wp_comment_subscriptions_admin_subscribe', 'no') == 'no') $show_subscription_box = false;
		$html_to_show .= str_replace('[manager_link]', $manager_link,
			html_entity_decode(stripslashes(get_option('wp_comment_subscriptions_author_label', __("You can <a href='[manager_link]'>manage the subscriptions</a> of this post.",'wp-comment-subscriptions'))), ENT_COMPAT, 'UTF-8'));
	}

	if ($show_subscription_box){
		$checkbox_label = str_replace('[subscribe_link]', "$manager_link&amp;sra=s",
			html_entity_decode(stripslashes(get_option('wp_comment_subscriptions_checkbox_label', __("Notify me of followup comments via e-mail. You can also <a href='[subscribe_link]'>subscribe</a> without commenting.",'wp-comment-subscriptions'))), ENT_COMPAT, 'UTF-8'));
		$checkbox_inline_style = get_option('wp_comment_subscriptions_checkbox_inline_style', 'width:30px');
		if (!empty($checkbox_inline_style)) $checkbox_inline_style = " style='$checkbox_inline_style'";
		$checkbox_html_wrap = html_entity_decode(stripslashes(get_option('wp_comment_subscriptions_checkbox_html', '')), ENT_COMPAT, 'UTF-8');
		if (get_option('wp_comment_subscriptions_enable_advanced_subscriptions', 'no') == 'no'){
			$checkbox_field = "<input$checkbox_inline_style type='checkbox' name='wp-comment-subscriptions' id='wp-comment-subscriptions' value='yes'".((get_option('wp_comment_subscriptions_checked_by_default', 'no') == 'yes')?" checked='checked'":'')." />";
		}
		else{
			$checkbox_field = "<select name='wp-comment-subscriptions' id='wp-comment-subscriptions'>
				<option value='none' ". ( ( get_option('wp_comment_subscriptions_advanced_default') === '0') ? "selected='selected'" : '' ) .">".__("None",'wp-comment-subscriptions')."</option>
				<option value='yes' ". ( ( get_option('wp_comment_subscriptions_advanced_default') === '1') ? "selected='selected'" : '' ) .">".__('All new comments','wp-comment-subscriptions')."</option>
				<option value='replies' ". ( ( get_option('wp_comment_subscriptions_advanced_default') === '2') ? "selected='selected'" : '' ) .">".__('Replies to this comment','wp-comment-subscriptions')."</option>
				<!-- option value='digest'>".__('Daily digest','wp-comment-subscriptions')."</option -->
			</select>";
		}
		if (empty($checkbox_html_wrap)){
			$html_to_show = "$checkbox_field <label for='wp-comment-subscriptions'>$checkbox_label</label>" . $html_to_show;
		}
		else{
			$checkbox_html_wrap = str_replace('[checkbox_field]', $checkbox_field, $checkbox_html_wrap);
			$html_to_show = str_replace('[checkbox_label]', $checkbox_label, $checkbox_html_wrap) . $html_to_show;
		}
	}
	if(function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage')) $html_to_show = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($html_to_show);
	echo "<!-- BEGIN: WP Comment Subscriptions --><p class=\"comment-form-subscriptions\">$html_to_show</p><!-- END: WP Comment Subscriptions -->";
}

// Show the checkbox - You can manually override this by adding the corresponding function in your template
if (get_option('wp_comment_subscriptions_show_subscription_box', 'yes') == 'yes')
	add_action('comment_form', 'wp_comment_subscriptions_show');

class wp_comment_subscriptions {

	public $current_version = '140109';

	/**
	 * Constructor -- Sets things up.
	 */
	public function __construct(){
		$this->salt = defined('NONCE_KEY')?NONCE_KEY:'please create a unique key in your wp-config.php';

		// What to do when a new comment is posted
		add_action('comment_post', array(&$this, 'new_comment_posted'), 12, 2);

		// Provide content for the management page using WP filters
		if (!is_admin()){
			$manager_page_permalink = get_option('wp_comment_subscriptions_manager_page', '/comment-subscriptions');
			if (function_exists('qtrans_convertURL')) $manager_page_permalink = qtrans_convertURL($manager_page_permalink);
			if (empty($manager_page_permalink)) $manager_page_permalink = get_option('wp_comment_subscriptions_manager_page', '/comment-subscriptions');
			if ((strpos($_SERVER["REQUEST_URI"], $manager_page_permalink) !== false) && get_option('wp_comment_subscriptions_manager_page_enabled', 'yes') == 'yes'){
				add_filter('the_posts', array(&$this, 'wp_comment_subscriptions_manage'), 10, 2);
			}

			// Create a hook to use with the daily cron job
			add_action('wp_comment_subscriptions_purge', array(&$this,'wp_comment_subscriptions_purge'));
		}
		else{
			// Initialization routines that should be executed on activation/deactivation
			register_activation_hook(__FILE__, array(&$this, 'activate'));
			register_deactivation_hook(__FILE__, array(&$this, 'deactivate'));

			// Hook for WPMU - New blog created
			add_action('wpmu_new_blog', array(&$this, 'new_blog'), 10, 1);

			// Remove subscriptions attached to a post that is being deleted
			add_action('delete_post', array(&$this, 'delete_subscriptions'), 10, 2);

			// Monitor actions on existing comments
			add_action('deleted_comment', array(&$this, 'comment_deleted'));
			add_action('wp_set_comment_status', array(&$this, 'comment_status_changed'));

			// Subscribe post authors, if the case
			if (get_option('wp_comment_subscriptions_notify_authors', 'no') == 'yes'){
				add_action('publish_post', array(&$this, 'subscribe_post_author'));
			}

			// Add a new column to the Edit Comments panel
			add_filter('manage_edit-comments_columns', array(&$this,'add_column_header'));
			add_filter('manage_posts_columns', array(&$this,'add_column_header'));
			add_action('manage_comments_custom_column', array(&$this,'add_comment_column'));
			add_action('manage_posts_custom_column', array(&$this,'add_post_column'));

			// Add appropriate entries in the admin menu
			add_action('admin_menu', array(&$this, 'add_config_menu'));
			add_action('admin_print_styles-wp-comment-subscriptions/options/index.php', array(&$this, 'add_options_stylesheet'));
			add_action('admin_print_styles-edit-comments.php', array(&$this, 'add_post_comments_stylesheet'));
			add_action('admin_print_styles-edit.php', array(&$this, 'add_post_comments_stylesheet'));

			// Admin notices
			add_action('admin_init', array(&$this, 'admin_init'));
			add_action('admin_notices', array(&$this, 'admin_notices'));

			// Contextual help
			add_action('contextual_help', array(&$this,'contextual_help'), 10, 3);

			// Shortcodes to use the management URL sitewide
			add_shortcode('wpcs-subscribe-url', array(&$this,'subscribe_url_shortcode'));

			// Settings link for plugin on plugins page
			add_filter('plugin_action_links', array(&$this,'plugin_settings_link'), 10, 2);
		}
	}
	// end __construct

	public function admin_init() {

		$version= get_option('wp_comment_subscriptions_version');
		if ($version != $this->current_version) {
			// Do whatever upgrades needed here.
			update_option('wp_comment_subscriptions_version', $this->current_version);
		}
	}

	/**
	 * Support for WP MU network activations (experimental)
	 */
	public function activate(){
		global $wpdb;

		if (function_exists('is_multisite') && is_multisite() && isset($_GET['networkwide']) && ($_GET['networkwide'] == 1)){
			$blogids = $wpdb->get_col($wpdb->prepare("
				SELECT blog_id
				FROM $wpdb->blogs
				WHERE site_id = %d
				AND deleted = 0
				AND spam = 0", $wpdb->siteid));

			foreach ($blogids as $blog_id) {
				switch_to_blog($blog_id);
				$this->_activate();
			}
			restore_current_blog();
		}
		else{
			$this->_activate();
		}
	}
	// end activate

	/**
	 * Support for WP MU network activations (experimental)
	 */
	public function new_blog($_blog_id){
		switch_to_blog($_blog_id);
		$this->_activate();
		restore_current_blog();
	}
	// end new_blog

	public function admin_notices() {
		if ($notices= get_option('wp_comment_subscriptions_deferred_admin_notices')) {
			foreach ($notices as $notice) {
				echo $notice;
			}
			delete_option('wp_comment_subscriptions_deferred_admin_notices');
		}
	}

	/**
	 * Adds the options to the database and imports the data from other plugins
	 */
	private function _activate(){

		// Load localization files
		load_plugin_textdomain('wp-comment-subscriptions', WP_PLUGIN_DIR .'/wp-comment-subscriptions/langs', '/wp-comment-subscriptions/langs');

		/**
		 * It's important to prioritize importing data from the
		 * Subscribe to Comments Reloaded plugin over the
		 * Subscribe to Comments plugin because one we import data
		 * for one of them, the import for the second one will fail
		 * (because we never want to overwrite data).
		 */

		// Import data from Subscribe to Comments Reloaded plugin, if needed
		$this->_import_stcr_data();
		// OR, failing that, import data from Subscribe to Comments plugin, if needed
		$this->_import_stc_data();

		// Messages related to the management page
		global $wp_rewrite;

		if (empty($wp_rewrite->permalink_structure)){
			add_option('wp_comment_subscriptions_manager_page', '/?page_id=99999', '', 'no');
		}
		else{
			add_option('wp_comment_subscriptions_manager_page', '/comment-subscriptions', '', 'no');
		}

		add_option('wp_comment_subscriptions_show_subscription_box', 'yes', '', 'no');
		add_option('wp_comment_subscriptions_checked_by_default', 'no', '', 'no');
		add_option('wp_comment_subscriptions_enable_advanced_subscriptions', 'no', '', 'no');
		add_option('wp_comment_subscriptions_advanced_default', '2', '', 'no');
		add_option('wp_comment_subscriptions_checkbox_inline_style', 'width:30px', '', 'no');
		add_option('wp_comment_subscriptions_checkbox_html', "<label for='wp-comment-subscriptions'>[checkbox_field] [checkbox_label]</label>", '', 'no');
		add_option('wp_comment_subscriptions_checkbox_label', __("Notify me of followup comments via e-mail. You can also <a href='[subscribe_link]'>subscribe</a> without commenting.",'wp-comment-subscriptions'), '', 'no');
		add_option('wp_comment_subscriptions_subscribed_label', __("You are subscribed to this post. <a href='[manager_link]'>Manage</a> your subscriptions.",'wp-comment-subscriptions'), '', 'no');
		add_option('wp_comment_subscriptions_subscribed_waiting_label', __("Your subscription to this post needs to be confirmed. <a href='[manager_link]'>Manage your subscriptions</a>.",'wp-comment-subscriptions'), '', 'no');
		add_option('wp_comment_subscriptions_author_label', __("You can <a href='[manager_link]'>manage the subscriptions</a> of this post.",'wp-comment-subscriptions'), '', 'no');

		add_option('wp_comment_subscriptions_manager_page_enabled', 'yes', '', 'no');
		add_option('wp_comment_subscriptions_manager_page_title', __('Manage subscriptions','wp-comment-subscriptions'), '', 'no');
		add_option('wp_comment_subscriptions_custom_header_meta', "<meta name='robots' content='noindex,nofollow'>", '', 'no');
		add_option('wp_comment_subscriptions_request_mgmt_link', __('To manage your subscriptions, please enter your email address here below. We will send you a message containing the link to access your personal management page.', 'wp-comment-subscriptions'), '', 'no');
		add_option('wp_comment_subscriptions_request_mgmt_link_thankyou', __('Thank you for using our subscription service. Your request has been completed, and you should receive an email with the management link in a few minutes.', 'wp-comment-subscriptions'), '', 'no');
		add_option('wp_comment_subscriptions_subscribe_without_commenting', __("You can follow the discussion on <strong>[post_title]</strong> without having to leave a comment. Cool, huh? Just enter your email address in the form here below and you're all set.", 'wp-comment-subscriptions'), '', 'no');
		add_option('wp_comment_subscriptions_subscription_confirmed', __("Thank you for using our subscription service. Your request has been completed. You will receive a notification email every time a new comment to this article is approved and posted by the administrator.", 'wp-comment-subscriptions'), '', 'no');
		add_option('wp_comment_subscriptions_subscription_confirmed_dci', __("Thank you for using our subscription service. In order to confirm your request, please check your email for the verification message and follow the instructions.", 'wp-comment-subscriptions'), '', 'no');
		add_option('wp_comment_subscriptions_author_text', __("In order to cancel or suspend one or more notifications, select the corresponding checkbox(es) and click on the button at the end of the list.", 'wp-comment-subscriptions'), '', 'no');
		add_option('wp_comment_subscriptions_user_text', __("In order to cancel or suspend one or more notifications, select the corresponding checkbox(es) and click on the button at the end of the list. You are currently subscribed to:", 'wp-comment-subscriptions'), '', 'no');

		add_option('wp_comment_subscriptions_from_name', get_bloginfo('name'), '', 'no');
		add_option('wp_comment_subscriptions_from_email', get_bloginfo('admin_email'), '', 'no');
		add_option('wp_comment_subscriptions_notification_subject', __('New comment on [post_title]','wp-comment-subscriptions'), '', 'no');
		add_option('wp_comment_subscriptions_notification_content', __("There is a new comment on [post_title].\nComment Link: [comment_permalink]\nAuthor: [comment_author]\nComment:\n[comment_content]\nPermalink: [post_permalink]\nManage your subscriptions: [manager_link]",'wp-comment-subscriptions'), '', 'no');
		add_option('wp_comment_subscriptions_double_check_subject', __('Please confirm your subscription to [post_title]','wp-comment-subscriptions'), '', 'no');
		add_option('wp_comment_subscriptions_double_check_content', __("You have requested to be notified every time a new comment is added to:\n[post_permalink]\n\nPlease confirm your request by clicking on this link:\n[confirm_link]",'wp-comment-subscriptions'), '', 'no');
		add_option('wp_comment_subscriptions_management_subject', __('Manage your subscriptions on [blog_name]','wp-comment-subscriptions'));
		add_option('wp_comment_subscriptions_management_content', __("You have requested to manage your subscriptions to the articles on [blog_name]. Follow this link to access your personal page:\n[manager_link]",'wp-comment-subscriptions'));

		add_option('wp_comment_subscriptions_purge_days', '30', '', 'no');
		add_option('wp_comment_subscriptions_enable_double_check', 'no', '', 'no');
		add_option('wp_comment_subscriptions_notify_authors', 'no', '', 'no');
		add_option('wp_comment_subscriptions_enable_html_emails', 'no', '', 'no');
		add_option('wp_comment_subscriptions_htmlify_message_links', 'no', '', 'no');
		add_option('wp_comment_subscriptions_process_trackbacks', 'no', '', 'no');
		add_option('wp_comment_subscriptions_enable_admin_messages', 'no', '', 'no');
		add_option('wp_comment_subscriptions_admin_subscribe', 'no', '', 'no');
		add_option('wp_comment_subscriptions_admin_bcc', 'no', '', 'no');

		// Schedule the autopurge hook
		if (!wp_next_scheduled('wp_comment_subscriptions_purge'))
			wp_schedule_event(time(), 'daily', 'wp_comment_subscriptions_purge');
	}
	// end _activate

	/**
	 * Performs some clean-up maintenance (disable cron job).
	 */
	public function deactivate() {
		global $wpdb;
		if (function_exists('is_multisite') && is_multisite() && isset($_GET['networkwide']) && ($_GET['networkwide'] == 1)){
			$blogids = $wpdb->get_col($wpdb->prepare("
				SELECT blog_id
				FROM $wpdb->blogs
				WHERE site_id = %d
				AND deleted = 0
				AND spam = 0", $wpdb->siteid));

			foreach ($blogids as $blog_id) {
				switch_to_blog($blog_id);
				wp_clear_scheduled_hook('wp_comment_subscriptions_purge');
			}
			restore_current_blog();
		}
		else{
			wp_clear_scheduled_hook('wp_comment_subscriptions_purge');
		}

		delete_option('wp_comment_subscriptions_version');
		delete_option('wp_comment_subscriptions_deferred_admin_notices');
	}
	// end deactivate

	/*
	 * Add Settings link to plugin on plugins page
	 */
	public function plugin_settings_link($links, $file) {
		if ( $file == 'wp-comment-subscriptions/wp-comment-subscriptions.php' ) {
			$links['settings'] = sprintf( '<a href="%s"> %s </a>', admin_url( 'options-general.php?page=wp-comment-subscriptions/options/index.php' ), __( 'Settings', 'wp-comment-subscriptions' ) );
		}
		return $links;
	}

	/**
	 * Takes the appropriate action, when a new comment is posted
	 */
	public function new_comment_posted($_comment_ID = 0, $_comment_status = 0){
		// Retrieve the information about the new comment
		$info = $this->_get_comment_object($_comment_ID);

		if (empty($info) || $info->comment_approved == 'spam')
			return $_comment_ID;

		// Are subscriptions allowed for this post?
		$is_disabled = get_post_meta($info->comment_post_ID, 'stcr_disable_subscriptions', true);
		if (!empty($is_disabled))
			return $_comment_ID;

		// Process trackbacks and pingbacks?
		if ((get_option('wp_comment_subscriptions_process_trackbacks', 'no') == 'no') && ($info->comment_type == 'trackback' || $info->comment_type == 'pingback'))
			return $_comment_ID;

		// Did this visitor request to be subscribed to the discussion? (and s/he is not subscribed)
		if (!empty($_POST['wp-comment-subscriptions']) && !empty($info->comment_author_email)){
			if (!in_array($_POST['wp-comment-subscriptions'], array('replies', 'digest', 'yes')))
				return $_comment_ID;

			switch ($_POST['wp-comment-subscriptions']){
				case 'replies':
					$status = 'R';
					break;
				case 'digest':
					$status = 'D';
					break;
				default:
					$status = 'Y';
					break;
			}

			if (!$this->is_user_subscribed($info->comment_post_ID, $info->comment_author_email)){
				if($this->isDoubleCheckinEnabled($info)) {
					$this->sendConfirmationEMail($info);
					$status = "{$status}C";
				}
				$this->add_subscription($info->comment_post_ID, $info->comment_author_email, $status);

				// If comment is in the moderation queue
				if ($info->comment_approved == 0){
					//don't send notification-emails to all subscribed users
					return $_comment_ID;
				}
			}
		}

		// Send a notification to all the users subscribed to this post
		if ($info->comment_approved == 1){
			$subscriptions = $this->get_subscriptions(array('post_id', 'status'), array('equals', 'equals'), array($info->comment_post_ID, 'Y'));
			if (!empty($info->comment_parent))
				$subscriptions = array_merge($subscriptions, $this->get_subscriptions('parent', 'equals', array($info->comment_parent, $info->comment_post_ID)));

			foreach($subscriptions as $a_subscription){
				// Skip the user who posted this new comment
				if ($a_subscription->email != $info->comment_author_email)
					$this->notify_user($info->comment_post_ID, $a_subscription->email, $_comment_ID);
			}
		}

		// If the case, notify the author
		if (get_option('wp_comment_subscriptions_notify_authors', 'no') == 'yes')
			$this->notify_user($info->comment_post_ID, get_bloginfo('admin_email'), $_comment_ID);

		return $_comment_ID;
	}
	// end new_comment_posted

	public function isDoubleCheckinEnabled($info) {
		$approved_subscriptions = $this->get_subscriptions(array('status', 'email'), array('equals', 'equals'), array('Y', $info->comment_author_email));
		if ((get_option('wp_comment_subscriptions_enable_double_check', 'no') == 'yes') && !is_user_logged_in() && empty($approved_subscriptions)){
			return true;
		}
		else {
			return false;
		}
	}

	public function sendConfirmationEMail($info) {
		// Retrieve the information about the new comment
		$this->confirmation_email($info->comment_post_ID, $info->comment_author_email);
	}

	/**
	 * Performs the appropriate action when the status of a given comment changes
	 */
	public function comment_status_changed($_comment_ID = 0, $_comment_status = 0){
		// Retrieve the information about the comment
		$info = $this->_get_comment_object($_comment_ID);
		if (empty($info))
			return $_comment_ID;

		switch($info->comment_approved){
			case '0': // Unapproved: change the status of the corresponding subscription (if exists) to 'pending'
				$this->update_subscription_status($info->comment_post_ID, $info->comment_author_email, 'C');
				break;

			case '1': // Approved
				$this->update_subscription_status($info->comment_post_ID, $info->comment_author_email, '-C');
				$subscriptions = $this->get_subscriptions(array('post_id', 'status'), array('equals', 'equals'), array($info->comment_post_ID, 'Y'));
				if (!empty($info->comment_parent))
					$subscriptions = array_merge($subscriptions, $this->get_subscriptions('parent', 'equals', array($info->comment_parent, $info->comment_post_ID)));

				foreach($subscriptions as $a_subscription)
					if ($a_subscription->email != $info->comment_author_email) // Skip the user who posted this new comment
						$this->notify_user($info->comment_post_ID, $a_subscription->email, $_comment_ID);
				break;

			case 'trash':
			case 'spam':
				$this->comment_deleted($_comment_ID);
				break;

			default:
				break;
		}
		return $_comment_ID;
	}
	// end comment_status

	/**
	 * Performs the appropriate action when a comment is deleted
	 */
	public function comment_deleted($_comment_ID){
		global $wpdb;

		$info = $this->_get_comment_object($_comment_ID);
		if (empty($info))
			return $_comment_ID;

		// Are there any other approved comments sent by this user?
		$count_approved_comments = $wpdb->get_var("
			SELECT COUNT(*)
			FROM $wpdb->comments
			WHERE comment_post_ID = '$info->comment_post_ID' AND comment_author_email = '$info->comment_author_email' AND comment_approved = 1");
		if (intval($count_approved_comments) == 0)
			$this->delete_subscriptions($info->comment_post_ID, $info->comment_author_email);

		return $_comment_ID;
	}
	// end comment_deleted

	/**
	 * Subscribes the post author, if the corresponding option is set
	 */
	public function subscribe_post_author($_post_ID){
		$new_post = get_post($_post_ID);
		$author_email = get_the_author_meta('user_email', $new_post->post_author);
		if (!empty($author_email)){
			$this->add_subscription($_post_ID, $author_email, 'Y');
		}
	}
	// end subscribe_post_author

	/**
	 * Displays the appropriate management page
	 */
	public function wp_comment_subscriptions_manage($_posts = '', $_query = ''){
		global $current_user;

		if (!empty($_posts))
			return $_posts;

		$post_ID = !empty($_POST['srp'])?intval($_POST['srp']):(!empty($_GET['srp'])?intval($_GET['srp']):0);

		// Is the post_id passed in the query string valid?
		$target_post = get_post($post_ID);
		if (($post_ID > 0) && !is_object($target_post))
			return $_posts;

		// Load localization files
		load_plugin_textdomain('wp-comment-subscriptions', WP_PLUGIN_DIR .'/wp-comment-subscriptions/langs', '/wp-comment-subscriptions/langs');

		$action = !empty($_POST['sra'])?$_POST['sra']:(!empty($_GET['sra'])?$_GET['sra']:0);
		$key = !empty($_POST['srk'])?$_POST['srk']:(!empty($_GET['srk'])?$_GET['srk']:0);

		$email = $this->clean_email(!empty($_POST['sre'])?urldecode($_POST['sre']):(!empty($_GET['sre'])?$_GET['sre']:''));
		if (empty($email) && !empty($current_user->user_email))
			$email = $this->clean_email($current_user->user_email);

		// Subscribe without commenting
		if (!empty($action) && ($action == 's') && ($post_ID > 0)){
			$include_post_content = include(WP_PLUGIN_DIR.'/wp-comment-subscriptions/templates/subscribe.php');
		}

		// Management page for post authors
		elseif (($post_ID > 0) && $this->is_author($target_post->post_author)){
			$include_post_content = include(WP_PLUGIN_DIR.'/wp-comment-subscriptions/templates/author.php');
		}

		// Confirm your subscription (double check-in)
		elseif ( ($post_ID > 0) && !empty($email) && !empty($key) && !empty($action) &&
				$this->is_user_subscribed($post_ID, $email, 'C') &&
				$this->_is_valid_key($key, $email) &&
				($action == 'c') ){
			$include_post_content = include(WP_PLUGIN_DIR.'/wp-comment-subscriptions/templates/confirm.php');
		}

		// Manage your subscriptions (user)
		elseif ( !empty($email) && ((!empty($key) && $this->_is_valid_key($key, $email)) || current_user_can('read')) ){
			$include_post_content = include(WP_PLUGIN_DIR.'/wp-comment-subscriptions/templates/user.php');
		}

		if (empty($include_post_content))
			$include_post_content = include(WP_PLUGIN_DIR.'/wp-comment-subscriptions/templates/request-management-link.php');

		global $wp_query;

		$manager_page_title = html_entity_decode(get_option('wp_comment_subscriptions_manager_page_title', 'Manage subscriptions'), ENT_COMPAT, 'UTF-8');
		if(function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage'))
			$manager_page_title = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($manager_page_title);

		$posts[] =
			(object)array(
				'ID' => '9999999',
				'post_author' => '1',
				'post_date' => '2001-01-01 11:38:56',
				'post_date_gmt' => '2001-01-01 00:38:56',
				'post_content' => $include_post_content,
				'post_title' => $manager_page_title,
				'post_excerpt' => '',
				'post_status' => 'publish',
				'comment_status' => 'closed',
				'ping_status' => 'closed',
				'post_password' => '',
				'to_ping' => '',
				'pinged' => '',
				'post_modified' => '2001-01-01 11:00:01',
				'post_modified_gmt' => '2001-01-01 00:00:01',
				'post_content_filtered' => '',
				'post_parent' => '0',
				'menu_order' => '0',
				'post_type' => 'page',
				'post_mime_type' => '',
				'post_category' => '0',
				'comment_count' => '0',
				'filter' => 'raw',
				'guid' => get_bloginfo('url').'/?page_id=9999999',
				'post_name' => get_bloginfo('url').'/?page_id=9999999',
				'ancestors' => array()
			);

		// Make WP believe this is a real page, with no comments attached
		$wp_query->is_page = true;
		$wp_query->is_single = false;
		$wp_query->is_home = false;
		$wp_query->comments = false;

		// Discard 404 errors thrown by other checks
		unset($wp_query->query["error"]);
		$wp_query->query_vars["error"]="";
		$wp_query->is_404=false;

		// Seems like WP adds its own HTML formatting code to the content, we don't need that here
		remove_filter('the_content','wpautop');
		add_action('wp_head', array(&$this, 'add_custom_header_meta'));

		return $posts;
	}
	// end wp_comment_subscriptions_manage

	/**
	 * Removes old entries from the database
	 */
	public function wp_comment_subscriptions_purge() {
		global $wpdb;

		if (($autopurge_interval = intval(get_option('wp_comment_subscriptions_purge_days', 0))) <= 0)
			return true;

		// Delete old entries
		$wpdb->query("
			DELETE FROM $wpdb->postmeta
			WHERE meta_key LIKE '\_stcr@\_%'
				AND STR_TO_DATE(meta_value, '%Y-%m-%d %H:%i:%s') <= DATE_SUB(NOW(), INTERVAL $autopurge_interval DAY) AND meta_value LIKE '%C'");
	}
	// end wp_comment_subscriptions_purge

	/**
	 * Checks if current logged in user is the author of this post
	 */
	public function is_author($_post_author){
		global $current_user;

		return (!empty($current_user) && (($_post_author == $current_user->ID) || current_user_can('manage_options')));
	}
	// end is_author

	/**
	 * Checks if a given email address is subscribed to a post
	 */
	public function is_user_subscribed($_post_ID = 0, $_email = '', $_status = ''){
		global $current_user;

		if ((empty($current_user->user_email) && empty($_COOKIE['comment_author_email_'. COOKIEHASH]) && empty($_email)) || empty($_post_ID))
			return false;

		$operator = ($_status != '')?'equals':'contains';
		$subscriptions = $this->get_subscriptions(array('post_id', 'status'), array('equals', $operator), array($_post_ID, $_status));

		if(empty($_email))
			$user_email = !empty($current_user->user_email)?$current_user->user_email:(!empty($_COOKIE['comment_author_email_'. COOKIEHASH])?stripslashes(esc_attr($_COOKIE['comment_author_email_'. COOKIEHASH])):'#undefined#');
		else
			$user_email = $_email;

		foreach($subscriptions as $a_subscription)
			if ($user_email == $a_subscription->email)
				return true;

		return false;
	}
	// end is_user_subscribed

	/**
	 * Adds a new subscription
	 */
	public function add_subscription($_post_id = 0, $_email = '', $_status = 'Y'){
		global $wpdb;

		// Does the post exist?
		$target_post = get_post($post_id);
		if (($post_id > 0) && !is_object($target_post))
			return;

		// Filter unwanted statuses
		if (!in_array($_status, array('Y', 'YC', 'R', 'RC', 'C', '-C')) || empty($_status))
			return;

		// Using Wordpress local time
		$dt = date_i18n('Y-m-d H:i:s');

		$clean_email = $this->clean_email($_email);
		$wpdb->query($wpdb->prepare("
			INSERT IGNORE INTO $wpdb->postmeta (post_id, meta_key, meta_value)
				SELECT %d, %s, %s
				FROM DUAL
				WHERE NOT EXISTS (
					SELECT post_id
					FROM $wpdb->postmeta
					WHERE post_id = %d
						AND meta_key = %s
					LIMIT 0,1
				)", $_post_id, "_stcr@_$clean_email", "$dt|$_status", $_post_id, "_stcr@_$clean_email"));
	}
	// end add_subscription

	/**
	 * Deletes one or more subscriptions from the database
	 */
	public function delete_subscriptions($_post_id = 0, $_email = ''){
			global $wpdb;

		if (empty($_post_id))
			return 0;

		$posts_where = '';
		if (!is_array($_post_id)){
			$posts_where = "post_id = ".intval($_post_id);
		}
		else{
			foreach ($_post_id as $a_post_id)
				$posts_where .= "post_id = '".intval($a_post_id)."' OR ";

			$posts_where = substr($posts_where, 0, -4);
		}

		if (!empty($_email)){
			$emails_where = '';
			if (!is_array($_email)){
				$emails_where = "meta_key = '_stcr@_".$this->clean_email($_email)."'";
			}
			else{
				foreach ($_email as $a_email)
					$emails_where .= "meta_key = '_stcr@_".$this->clean_email($a_email)."' OR ";

				$emails_where = substr($emails_where, 0, -4);
			}
			return $wpdb->query("DELETE FROM $wpdb->postmeta WHERE ($posts_where) AND ($emails_where)");
		}
		else
			return $wpdb->query("DELETE FROM $wpdb->postmeta WHERE meta_key LIKE '\_stcr@\_%' AND ($posts_where)");
	}
	// end delete_subscriptions

	/**
	 * Updates the status of an existing subscription
	 */
	public function update_subscription_status($_post_id = 0, $_email = '', $_new_status = 'C'){
		global $wpdb;

		// Filter unwanted statuses
		if (empty($_new_status) || !in_array($_new_status, array('Y', 'R', 'C', '-C')) || empty($_email))
			return 0;

		if (!empty($_post_id)){
			$posts_where = '';
			if (!is_array($_post_id)){
				$posts_where = "post_id = ".intval($_post_id);
			}
			else{
				foreach ($_post_id as $a_post_id)
					$posts_where .= "post_id = '".intval($a_post_id)."' OR ";

				$posts_where = substr($posts_where, 0, -4);
			}
		}
		else{ // Mass update subscriptions
			$posts_where = '1=1';
		}

		$emails_where = '';
		if (!is_array($_email)){
			$emails_where = "meta_key = '_stcr@_".$this->clean_email($_email)."'";
		}
		else{
			foreach ($_email as $a_email)
				$emails_where .= "meta_key = '_stcr@_".$this->clean_email($a_email)."' OR ";

			$emails_where = substr($emails_where, 0, -4);
		}

		$meta_length = (strpos($_new_status,'C')!==false)?21:20;
		$new_status = ($_new_status == '-C')?'':$_new_status;

		return $wpdb->query("
			UPDATE $wpdb->postmeta
			SET meta_value = CONCAT(SUBSTRING(meta_value, 1, $meta_length), '$new_status')
			WHERE ($posts_where) AND ($emails_where)");
	}
	// end update_subscription_status

	/**
	 * Updates the email address of an existing subscription
	 */
	public function update_subscription_email($_post_id = 0, $_email = '', $_new_email = ''){
		global $wpdb;

		// Nothing to do if the new email hasn't been specified
		if (empty($_email) || empty($_new_email) || strpos($_new_email, '@') == 0)
			return;

		$clean_values[] = "_stcr@_".$this->clean_email($_new_email);
		$clean_values[] = "_stcr@_".$this->clean_email($_email);
		$post_where = '';
		if (!empty($_post_id)){
			$post_where = ' AND post_id = %d';
			$clean_values[] = $_post_id;
		}

		return $wpdb->query($wpdb->prepare("
			UPDATE $wpdb->postmeta
			SET meta_key = %s
			WHERE meta_key = %s $post_where", $clean_values));
	}
	// end update_subscription_email

	/**
	 * Retrieves a list of emails subscribed to this post
	 */
	public function get_subscriptions($_search_field = array('email'), $_operator = array('equals'), $_search_value = array(''), $_order_by = 'dt', $_order = 'ASC',  $_offset = 0, $_limit_results = 0){
		global $wpdb;

		// Type adjustments
		$search_fields = (!is_array($_search_field))?array($_search_field):$_search_field;
		$operators = (!is_array($_operator))?array($_operator):$_operator;
		$search_values = (!is_array($_search_value))?array($_search_value):$_search_value;

		// Find if exists a 'replies only' subscription for the parent comment
		if ($search_fields[0] == 'parent'){

			$parent_comment_id = $search_values[0];
			$comment_post_id = $search_values[1];

			// Get the parent comment author email so we can search for any Replies Only subscriptions
			$parent_comment_author_email_query = mysql_query("SELECT `comment_author_email` FROM $wpdb->comments WHERE `comment_ID` = '$parent_comment_id'");
			$parent_comment_author_email = mysql_fetch_row($parent_comment_author_email_query);
			$parent_comment_author_email = "\_stcr@\_" . $parent_comment_author_email[0];

			// Check if $parent_comment_author_email has any Replies Only (R) subscriptions for $comment_post_id

			/*
				Heads up: this will return Replies Only subscriptions for a given post, *not* for a given comment.
				This plugin does not track subscriptions for specific comments but rather for entire posts, so there
				is no way to figure out if a specific parent comment has a subscription (of any type). To make the
				Replies Only feature half-work, we check if a parent comment author has *any* Replies Only subscriptions
				for a given post. If they do, we assume that they must want to get notified of replies to *any* of their
				comments on *that* post.
			*/

			return $wpdb->get_results($wpdb->prepare("
				SELECT pm.meta_id, REPLACE(pm.meta_key, '_stcr@_', '') AS email, pm.post_id, SUBSTRING(pm.meta_value, 1, 19) AS dt, SUBSTRING(pm.meta_value, 21) AS status
				FROM $wpdb->postmeta pm
				WHERE pm.meta_key LIKE %s
					AND pm.meta_value LIKE '%%R'
					AND pm.post_id = %d", $parent_comment_author_email, $comment_post_id ), OBJECT);
		}
		else{
			$where_clause = '';
			foreach($search_fields as $a_idx => $a_field){
				$where_clause .= ' AND';
				$offset_status = ($a_field == 'status' && $search_values[$a_idx] == 'C')?22:21;
				switch ($a_field){
					case 'status':
						$where_clause .= " SUBSTRING(meta_value, $offset_status)";
						break;
					case 'post_id':
						$where_clause .= ' post_id';
						break;
					default:
						$where_clause .= ' SUBSTRING(meta_key, 8)';
				}
				switch ($operators[$a_idx]){
					case 'equals':
						$where_clause .= " = %s";
						$where_values[] = $search_values[$a_idx];
						break;
					case 'does not contain':
						$where_clause .= " NOT LIKE %s";
						$where_values[] = "%{$search_values[$a_idx]}%";
						break;
					case 'starts with':
						$where_clause .= " LIKE %s";
						$where_values[] = "${$search_values[$a_idx]}%";
						break;
					case 'ends with':
						$where_clause .= " LIKE %s";
						$where_values[] = "%{$search_values[$a_idx]}";
						break;
					default: // contains
						$where_clause .= " LIKE %s";
						$where_values[] = "%{$search_values[$a_idx]}%";
				}
			}
			switch ($_order_by){
				case 'status':
					$order_by = "status";
					break;
				case 'email':
					$order_by = 'meta_key';
					break;
				case 'dt':
					$order_by = 'dt';
					break;
				default:
					$order_by = 'post_id';
			}
			$order = ($_order != 'ASC' && $_order != 'DESC')?'DESC':$_order;

			// This is the 'official' way to have an offset without a limit
			$row_count = ($_limit_results <= 0)?'18446744073709551610':$_limit_results;

			return $wpdb->get_results($wpdb->prepare("
				SELECT meta_id, REPLACE(meta_key, '_stcr@_', '') AS email, post_id, SUBSTRING(meta_value, 1, 19) AS dt, SUBSTRING(meta_value, 21) AS status
				FROM $wpdb->postmeta
				WHERE meta_key LIKE '\_stcr@\_%%' $where_clause
				ORDER BY $order_by $order
				LIMIT $_offset,$row_count", $where_values), OBJECT);
		}
	}
	// end get_subscriptions

	/**
	 * Sends a message to confirm a subscription
	 */
	public function confirmation_email($_post_ID = 0, $_email = ''){
		// Retrieve the options from the database
		$from_name = stripslashes(get_option('wp_comment_subscriptions_from_name', 'admin'));
		$from_email = get_option('wp_comment_subscriptions_from_email', get_bloginfo('admin_email'));
		$subject = html_entity_decode(stripslashes(get_option('wp_comment_subscriptions_double_check_subject', 'Please confirm your subscribtion to [post_title]')), ENT_COMPAT, 'UTF-8');
		$message = html_entity_decode(stripslashes(get_option('wp_comment_subscriptions_double_check_content', '')), ENT_COMPAT, 'UTF-8');
		$manager_link = get_bloginfo('url').get_option('wp_comment_subscriptions_manager_page', '/comment-subscriptions');
		if (function_exists('qtrans_convertURL')) $manager_link = qtrans_convertURL($manager_link);

		$clean_email = $this->clean_email($_email);
		$subscriber_salt = $this->generate_key($clean_email);

		$manager_link .= ((strpos($manager_link, '?') !== false)?'&':'?')."sre=".urlencode($clean_email)."&srk=$subscriber_salt";
		$confirm_link = "$manager_link&srp=$_post_ID&sra=c";

		$headers = "From: $from_name <$from_email>\n";
		$content_type = (get_option('wp_comment_subscriptions_enable_html_emails', 'no') == 'yes')?'text/html':'text/plain';
		$headers .= "Content-Type: $content_type; charset=".get_bloginfo('charset')."\n";

		$post = get_post($_post_ID);
		$post_permalink = get_permalink($_post_ID);

		// Replace tags with their actual values
		$subject = str_replace('[post_title]', $post->post_title, $subject);

		$message = str_replace('[post_permalink]', $post_permalink, $message);
		$message = str_replace('[confirm_link]', $confirm_link, $message);
		$message = str_replace('[manager_link]', $manager_link, $message);

		// QTranslate support
		if(function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage')){
			$subject = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($subject);
			$message = str_replace('[post_title]', qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($post->post_title), $message);
			$message = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($message);
		}
		else{
			$message = str_replace('[post_title]', $post->post_title, $message);
		}
		if($content_type == 'text/html') $message = $this->wrap_html_message($message, $subject);

		wp_mail($clean_email, $subject, $message, $headers);
	}
	// end confirmation_email

	/**
	 * Sends the notification message to a given user
	 */
	public function notify_user($_post_ID = 0, $_email = '', $_comment_ID = 0){
		// Retrieve the options from the database
		$from_name = html_entity_decode(stripslashes(get_option('wp_comment_subscriptions_from_name', 'admin')), ENT_COMPAT, 'UTF-8');
		$from_email = get_option('wp_comment_subscriptions_from_email', get_bloginfo('admin_email'));
		$subject = html_entity_decode(stripslashes(get_option('wp_comment_subscriptions_notification_subject', 'There is a new comment on the post [post_title]')), ENT_COMPAT, 'UTF-8');
		$message = html_entity_decode(stripslashes(get_option('wp_comment_subscriptions_notification_content', '')), ENT_COMPAT, 'UTF-8');
		$manager_link = get_bloginfo('url').get_option('wp_comment_subscriptions_manager_page', '/comment-subscriptions');
		if (function_exists('qtrans_convertURL'))
			$manager_link = qtrans_convertURL($manager_link);

		$clean_email = $this->clean_email($_email);
		$subscriber_salt = $this->generate_key($clean_email);

		$manager_link .= ((strpos($manager_link, '?') !== false)?'&':'?')."sre=".urlencode($clean_email)."&srk=$subscriber_salt";

		$headers = "From: $from_name <$from_email>\n";

		if (get_option('wp_comment_subscriptions_admin_bcc', 'no') == 'yes') {
			$headers .= "Bcc: $from_name <$from_email>\n";
		}

		$content_type = (get_option('wp_comment_subscriptions_enable_html_emails', 'no') == 'yes')?'text/html':'text/plain';
		$headers .= "Content-Type: $content_type; charset=".get_bloginfo('charset')."\n";

		$post = get_post($_post_ID);
		$comment = get_comment($_comment_ID);
		$post_permalink = get_permalink( $_post_ID );
		$comment_permalink = get_comment_link($_comment_ID);
		$comment_reply_permalink = get_permalink( $_post_ID ) . '?replytocom=' . $_comment_ID . '#respond';

		$comment_content = $comment->comment_content;

		// Add HTML paragraph tags to comment
		// See wp-includes/formatting.php for details on the wpautop() function
		if ($content_type == 'text/html')
			$comment_content = wpautop($comment->comment_content);

		// Replace tags with their actual values
		$subject = str_replace('[post_title]', $post->post_title, $subject);

		$message = str_replace('[post_permalink]', $post_permalink, $message);
		$message = str_replace('[comment_permalink]', $comment_permalink, $message);
		$message = str_replace('[comment_reply_permalink]', $comment_reply_permalink, $message);
		$message = str_replace('[comment_author]', $comment->comment_author, $message);
		$message = str_replace('[comment_content]', $comment_content, $message);
		$message = str_replace('[manager_link]', $manager_link, $message);

		// QTranslate support
		if(function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage')){
			$subject = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($subject);
			$message = str_replace('[post_title]', qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($post->post_title), $message);
			$message = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($message);
		}
		else{
			$message = str_replace('[post_title]', $post->post_title, $message);
		}
		if($content_type == 'text/html') {
			if(get_option('wp_comment_subscriptions_htmlify_message_links') == 'yes')
				$message = $this->htmlify_message_links($message);
			$message = $this->wrap_html_message($message, $subject);
		}

		wp_mail($clean_email, $subject, $message, $headers);
	}
	// end notify_user

	/**
	 * Finds all links in text and wraps them with an HTML anchor tag
	 *
	 * @param $text
	 *
	 * @return string Text with all links wrapped in HTML anchor tags
	 *
	 */
	public function htmlify_message_links($text){
		return preg_replace('!(((f|ht)tp(s)?://)[-a-zA-Zа-яА-Я()0-9@:%_+.~#?&;//=]+)!i', '<a href="$1">$1</a>', $text);
	}

	/**
	 * Generate a unique key to allow users to manage their subscriptions
	 */
	public function generate_key($_email){
		$day = date_i18n('Ymd');

		return md5($day.$this->salt.$_email);
	}
	// end generate_key

	/**
	 * Creates the HTML structure to properly handle HTML messages
	 */
	public function wrap_html_message($_message = '', $_subject = ''){
		return "<html><head><title>$_subject</title></head><body>$_message</body></html>";
	}
	// end _wrap_html_message

	/**
	 * Returns an email address where some possible 'offending' strings have been removed
	 */
	public function clean_email($_email){
		$offending_strings = array(
			"/to\:/i",
			"/from\:/i",
			"/bcc\:/i",
			"/cc\:/i",
			"/content\-transfer\-encoding\:/i",
			"/content\-type\:/i",
			"/mime\-version\:/i"
		);
		return esc_attr(stripslashes(strip_tags(preg_replace($offending_strings, '', $_email))));
	}
	// end clean_email

	/**
	 * Adds a new entry in the admin menu, to manage this plugin's options
	 */
	public function add_config_menu( $_s ) {
		global $current_user;

		if (current_user_can('manage_options')){
			add_options_page('WP Comment Subscriptions', 'Comment Subscriptions', 'manage_options', WP_PLUGIN_DIR.'/wp-comment-subscriptions/options/index.php');
		}
		return $_s;
	}
	// end add_config_menu

	/**
	 * Adds a custom stylesheet file to the admin interface
	 */
	public function add_options_stylesheet(){
		// It looks like WP_PLUGIN_URL doesn't honor the HTTPS setting in wp-config.php
		$stylesheet_url = (is_ssl()?str_replace('http://', 'https://', WP_PLUGIN_URL):WP_PLUGIN_URL).'/wp-comment-subscriptions/style.css';
		wp_register_style('subscribe-to-comments', $stylesheet_url);
		wp_enqueue_style('subscribe-to-comments');
	}
	public function add_post_comments_stylesheet(){
		// It looks like WP_PLUGIN_URL doesn't honor the HTTPS setting in wp-config.php
		$stylesheet_url = (is_ssl()?str_replace('http://', 'https://', WP_PLUGIN_URL):WP_PLUGIN_URL).'/wp-comment-subscriptions/post-and-comments.css';
		wp_register_style('subscribe-to-comments', $stylesheet_url);
		wp_enqueue_style('subscribe-to-comments');
	}
	// end add_stylesheet

	/**
	 * Adds custom HTML code to the HEAD section of the management page
	 */
	public function add_custom_header_meta(){
		echo html_entity_decode(stripslashes(get_option('wp_comment_subscriptions_custom_header_meta', '')), ENT_COMPAT, 'UTF-8');
	}
	// end add_custom_header_meta

	/**
	 * Adds a new column header to the Edit Comments panel
	 */
	public function add_column_header($_columns){
		$image_url = (is_ssl()?str_replace('http://', 'https://', WP_PLUGIN_URL):WP_PLUGIN_URL).'/wp-comment-subscriptions/images';
		$_columns['wp-comment-subscriptions'] = "<img src='$image_url/subscribe-to-comments-small.png' width='17' height='12' alt='Subscriptions' />";
		return $_columns;
	}
	// end add_comment_column_header

	/**
	 * Adds a new column to the Edit Comments panel
	 */
	public function add_comment_column($_column_name){
		if ('wp-comment-subscriptions' != $_column_name) return;

		global $comment;
		$subscription = $this->get_subscriptions(array('post_id','email'), array('equals','equals'), array($comment->comment_post_ID, $comment->comment_author_email), 'dt', 'DESC', 0, 1);
		if (count($subscription) == 0)
			_e('No','wp-comment-subscriptions');
		else
			echo '<a href="options-general.php?page=wp-comment-subscriptions/options/index.php&subscribepanel=1&amp;srf=email&amp;srt=equals&amp;srv='.urlencode($comment->comment_author_email).'">'.$subscription[0]->status.'</a>';
	}
	// end add_column

	/**
	 * Adds a new column to the Posts management panel
	 */
	public function add_post_column($_column_name){
		if ('wp-comment-subscriptions' != $_column_name) return;

		global $post;
		load_plugin_textdomain('wp-comment-subscriptions', WP_PLUGIN_DIR .'/wp-comment-subscriptions/langs', '/wp-comment-subscriptions/langs');
		echo '<a href="options-general.php?page=wp-comment-subscriptions/options/index.php&subscribepanel=1&amp;srf=post_id&amp;srt=equals&amp;srv='.$post->ID.'">'.count($this->get_subscriptions('post_id', 'equals', $post->ID)).'</a>';
	}
	// end add_column

	/**
	 * Contextual help (link to the support forum)
	 */
	public function contextual_help($contextual_help, $screen_id, $screen){
		if ($screen_id == 'wp-comment-subscriptions/options/index'){
			load_plugin_textdomain('wp-comment-subscriptions', WP_PLUGIN_DIR .'/wp-comment-subscriptions/langs', '/wp-comment-subscriptions/langs');
			$contextual_help = __('Need help using WP Comment Subscriptions? Please post a new issue ','wp-comment-subscriptions').' <a href="http://github.com/raamdev/wp-comment-subscriptions/issues/" target="_blank">'.__('on GitHub','wp-comment-subscriptions').'</a>. ';
		}
		return $contextual_help;
	}
	// end contextual_help

	/**
	 * Returns the URL of the management page as a shortcode
	 */
	public function subscribe_url_shortcode(){
		global $post;
		$user_link = get_bloginfo('url').get_option('wp_comment_subscriptions_manager_page', '');
		if (function_exists('qtrans_convertURL'))
			$user_link = qtrans_convertURL($user_link);
		if (strpos($user_link, '?') !== false)
			return "$user_link&amp;srp=$post->ID&amp;sra=s";
		else
			return "$user_link?srp=$post->ID&amp;sra=s";
	}
	// end subscribe_url_shortcode

	/**
	 * Imports subscription data created with the Subscribe to Comments plugin
	 */
	private function _import_stc_data(){
		global $wpdb;

		// Import the information collected by Subscribe to Comments & Co., if needed
		$result = $wpdb->get_row("DESC $wpdb->comments comment_subscribe", ARRAY_A);

		// Perform the import only if the target table does not contain any subscriptions
		$count_postmeta_rows = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key LIKE '\_wpcs@\_%'");

		if (!empty($result) && is_array($result) && $count_postmeta_rows == 0){
			$wpdb->query("
				INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value)
					SELECT comment_post_ID, CONCAT('_wpcs@_', comment_author_email), CONCAT(comment_date, '|Y')
					FROM $wpdb->comments
					WHERE comment_author_email LIKE '%@%.%' AND comment_subscribe = 'Y'
					GROUP BY comment_post_ID, comment_author_email");

			$notices = get_option('wp_comment_subscriptions_deferred_admin_notices', array());
			$notices[] = '<div class="updated"><h3>' . __('Important Notice', 'wp-comment-subscriptions') . '</h3>'.
			'<p>' . __('Comment subscription data from the <strong>Subscribe to Comments</strong> plugin was detected and automatically imported into <strong>WP Comment Subscriptions</strong>.', 'wp-comment-subscriptions') . '</p>' .
					'<p>' . __('Please visit <a href="options-general.php?page=wp-comment-subscriptions/options/index.php">Settings -> Comment Subscriptions</a> to review your WP Comment Subscriptions configuration.', 'wp-comment-subscriptions') . '</p></div>';
			update_option('wp_comment_subscriptions_deferred_admin_notices', $notices);

		}
	}
	// end _import_stc_data

	/**
	 * Imports options and subscription data created with the Subscribe to Comments Reloaded plugin
	 */
	private function _import_stcr_data(){
		global $wpdb;

		if( $option = get_option('subscribe_reloaded_manager_page') ) { add_option('wp_comment_subscriptions_manager_page', $option); }
		if( $option = get_option('subscribe_reloaded_show_subscription_box') ) { add_option('wp_comment_subscriptions_show_subscription_box', $option); }
		if( $option = get_option('subscribe_reloaded_checked_by_default') ) { add_option('wp_comment_subscriptions_checked_by_default', $option); }
		if( $option = get_option('subscribe_reloaded_enable_advanced_subscriptions') ) { add_option('wp_comment_subscriptions_enable_advanced_subscriptions', $option); }
		if( $option = get_option('subscribe_reloaded_checkbox_inline_style') ) { add_option('wp_comment_subscriptions_checkbox_inline_style', $option); }
		if( $option = get_option('subscribe_reloaded_checkbox_html') ) { add_option('wp_comment_subscriptions_checkbox_html', $option); }
		if( $option = get_option('subscribe_reloaded_checkbox_label') ) { add_option('wp_comment_subscriptions_checkbox_label', $option); }
		if( $option = get_option('subscribe_reloaded_subscribed_label') ) { add_option('wp_comment_subscriptions_subscribed_label', $option); }
		if( $option = get_option('subscribe_reloaded_subscribed_waiting_label') ) { add_option('wp_comment_subscriptions_subscribed_waiting_label', $option); }
		if( $option = get_option('subscribe_reloaded_author_label') ) { add_option('wp_comment_subscriptions_author_label', $option); }

		if( $option = get_option('subscribe_reloaded_manager_page_enabled') ) { add_option('wp_comment_subscriptions_manager_page_enabled', $option); }
		if( $option = get_option('subscribe_reloaded_manager_page_title') ) { add_option('wp_comment_subscriptions_manager_page_title', $option); }
		if( $option = get_option('subscribe_reloaded_custom_header_meta') ) { add_option('wp_comment_subscriptions_custom_header_meta', $option); }
		if( $option = get_option('subscribe_reloaded_request_mgmt_link') ) { add_option('wp_comment_subscriptions_request_mgmt_link', $option); }
		if( $option = get_option('subscribe_reloaded_request_mgmt_link_thankyou') ) { add_option('wp_comment_subscriptions_request_mgmt_link_thankyou', $option); }
		if( $option = get_option('subscribe_reloaded_subscribe_without_commenting') ) { add_option('wp_comment_subscriptions_subscribe_without_commenting', $option); }
		if( $option = get_option('subscribe_reloaded_subscription_confirmed') ) { add_option('wp_comment_subscriptions_subscription_confirmed', $option); }
		if( $option = get_option('subscribe_reloaded_subscription_confirmed_dci') ) { add_option('wp_comment_subscriptions_subscription_confirmed_dci', $option); }
		if( $option = get_option('subscribe_reloaded_author_text') ) { add_option('wp_comment_subscriptions_author_text', $option); }
		if( $option = get_option('subscribe_reloaded_user_text') ) { add_option('wp_comment_subscriptions_user_text', $option); }

		if( $option = get_option('subscribe_reloaded_from_name') ) { add_option('wp_comment_subscriptions_from_name', $option); }
		if( $option = get_option('subscribe_reloaded_from_email') ) { add_option('wp_comment_subscriptions_from_email', $option); }
		if( $option = get_option('subscribe_reloaded_notification_subject') ) { add_option('wp_comment_subscriptions_notification_subject', $option); }
		if( $option = get_option('subscribe_reloaded_notification_content') ) { add_option('wp_comment_subscriptions_notification_content', $option); }
		if( $option = get_option('subscribe_reloaded_double_check_subject') ) { add_option('wp_comment_subscriptions_double_check_subject', $option); }
		if( $option = get_option('subscribe_reloaded_double_check_content') ) { add_option('wp_comment_subscriptions_double_check_content', $option); }
		if( $option = get_option('subscribe_reloaded_management_subject') ) { add_option('wp_comment_subscriptions_management_subject', $option); }
		if( $option = get_option('subscribe_reloaded_management_content') ) { add_option('wp_comment_subscriptions_management_content', $option); }

		if( $option = get_option('subscribe_reloaded_purge_days') ) { add_option('wp_comment_subscriptions_purge_days', $option); }
		if( $option = get_option('subscribe_reloaded_enable_double_check') ) { add_option('wp_comment_subscriptions_enable_double_check', $option); }
		if( $option = get_option('subscribe_reloaded_notify_authors') ) { add_option('wp_comment_subscriptions_notify_authors', $option); }
		if( $option = get_option('subscribe_reloaded_enable_html_emails') ) { add_option('wp_comment_subscriptions_enable_html_emails', $option); }
		if( $option = get_option('subscribe_reloaded_process_trackbacks') ) { add_option('wp_comment_subscriptions_process_trackbacks', $option); }
		if( $option = get_option('subscribe_reloaded_enable_admin_messages') ) { add_option('wp_comment_subscriptions_enable_admin_messages', $option); }
		if( $option = get_option('subscribe_reloaded_admin_subscribe') ) { add_option('wp_comment_subscriptions_admin_subscribe', $option); }

		// Import the information collected by Subscribe to Comments Reloaded, if needed
		$stcr_data_count = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key LIKE '\_stcr@\_%'");

		// Perform the import only if the target table does not contain any subscriptions
		$count_postmeta_rows = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key LIKE '\_wpcs@\_%'");

		if (!empty($stcr_data_count) && $count_postmeta_rows == 0){
			$wpdb->query("
				INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value)
					SELECT post_id, REPLACE(meta_key, '_stcr@_', '_wpcs@_') meta_key, meta_value
					FROM $wpdb->postmeta
					WHERE meta_key LIKE '%_stcr@_%'");

			$notices = get_option('wp_comment_subscriptions_deferred_admin_notices', array());
			$notices[] = '<div class="updated"><h3>' . __('Important Notice', 'wp-comment-subscriptions') . '</h3>'.
			'<p>' . __('Plugin options and comment subscription data from the <strong>Subscribe to Comments Reloaded</strong> plugin were detected and automatically imported into the <strong>WP Comment Subscriptions</strong> plugin.', 'wp-comment-subscriptions') . ( is_plugin_active( 'subscribe-to-comments-reloaded/subscribe-to-comments-reloaded.php' ) ? __(' It is recommended that you now <strong>deactivate</strong> Subscribe to Comments Reloaded to prevent confusion between the two plugins.','wp-comment-subscriptions') : '' ) . '</p>' .
			'<p>' . __('<strong>Note:</strong> If you were previously using the <code>subscribe_reloaded_show()</code> function or the <code>[subscribe-url]</code> shortcode, you\'ll need to replace those with <code>wp_comment_subscriptions_show()</code> and <code>[wpcs-subscribe-url]</code> respectively.', 'wp-comment-subscriptions') . '</p>' .
			'<p>' . __('Please visit <a href="options-general.php?page=wp-comment-subscriptions/options/index.php">Settings -> Comment Subscriptions</a> to review your configuration.', 'wp-comment-subscriptions') . '</p></div>';
			update_option('wp_comment_subscriptions_deferred_admin_notices', $notices);
		}
	}
	// end _import_stcr_data

	/**
	 * Retrieves the comment information from the databse
	 */
	private function _get_comment_object($_comment_ID){
		global $wpdb;

		return $wpdb->get_row($wpdb->prepare("
			SELECT comment_post_ID, comment_author_email, comment_approved, comment_type, comment_parent
			FROM $wpdb->comments
			WHERE comment_ID = %d LIMIT 1", $_comment_ID), OBJECT);
	}
	// end _get_comment_object

	/**
	 * Checks if a key is valid for a given email address
	 */
	private function _is_valid_key($_key, $_email){
		return ($this->generate_key($_email) == $_key);
	}
	// end _is_valid_key
}
// end of class declaration


// Bootstrap the whole thing
$wp_comment_subscriptions = new wp_comment_subscriptions();

// Set a cookie if the user just subscribed without commenting
$subscribe_to_comments_action = !empty($_POST['sra'])?$_POST['sra']:(!empty($_GET['sra'])?$_GET['sra']:0);
$subscribe_to_comments_post_ID = !empty($_POST['srp'])?intval($_POST['srp']):(!empty($_GET['srp'])?intval($_GET['srp']):0);
if (!empty($subscribe_to_comments_action) && !empty($_POST['wp_comment_subscriptions_email']) && ($subscribe_to_comments_action == 's') && ($subscribe_to_comments_post_ID > 0)){
	$subscribe_to_comments_clean_email = $wp_comment_subscriptions->clean_email($_POST['wp_comment_subscriptions_email']);
	setcookie('comment_author_email'.COOKIEHASH, $subscribe_to_comments_clean_email, time()+1209600, '/');
}
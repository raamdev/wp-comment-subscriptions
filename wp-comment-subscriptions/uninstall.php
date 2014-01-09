<?php

// Avoid misusage
if (!defined('WP_UNINSTALL_PLUGIN')) exit;

global $wpdb;

// Goodbye data...
$wpdb->query("DELETE FROM $wpdb->postmeta WHERE meta_key LIKE '\_wpcs@\_%'");

// Goodbye options...
delete_option('wp_comment_subscriptions_manager_page_enabled');
delete_option('wp_comment_subscriptions_manager_page');
delete_option('wp_comment_subscriptions_manager_page_title');
delete_option('wp_comment_subscriptions_request_mgmt_link');
delete_option('wp_comment_subscriptions_request_mgmt_link_thankyou');
delete_option('wp_comment_subscriptions_subscribe_without_commenting');
delete_option('wp_comment_subscriptions_subscription_confirmed');
delete_option('wp_comment_subscriptions_subscription_confirmed_dci');
delete_option('wp_comment_subscriptions_author_text');
delete_option('wp_comment_subscriptions_user_text');

delete_option('wp_comment_subscriptions_show_subscription_box');
delete_option('wp_comment_subscriptions_enable_advanced_subscriptions');
delete_option('wp_comment_subscriptions_purge_days');
delete_option('wp_comment_subscriptions_from_name');
delete_option('wp_comment_subscriptions_from_email');
delete_option('wp_comment_subscriptions_checked_by_default');
delete_option('wp_comment_subscriptions_enable_double_check');
delete_option('wp_comment_subscriptions_notify_authors');
delete_option('wp_comment_subscriptions_enable_html_emails');
delete_option('wp_comment_subscriptions_htmlify_message_links');
delete_option('wp_comment_subscriptions_process_trackbacks');
delete_option('wp_comment_subscriptions_enable_admin_messages');
delete_option('wp_comment_subscriptions_admin_subscribe');
delete_option('wp_comment_subscriptions_admin_bcc');
delete_option('wp_comment_subscriptions_custom_header_meta');

delete_option('wp_comment_subscriptions_notification_subject');
delete_option('wp_comment_subscriptions_notification_content');
delete_option('wp_comment_subscriptions_checkbox_label');
delete_option('wp_comment_subscriptions_checkbox_inline_style');
delete_option('wp_comment_subscriptions_checkbox_html');
delete_option('wp_comment_subscriptions_subscribed_label');
delete_option('wp_comment_subscriptions_subscribed_waiting_label');
delete_option('wp_comment_subscriptions_author_label');
delete_option('wp_comment_subscriptions_double_check_subject');
delete_option('wp_comment_subscriptions_double_check_content');
delete_option('wp_comment_subscriptions_management_subject');
delete_option('wp_comment_subscriptions_management_content');

delete_option('wp_comment_subscriptions_version');
delete_option('wp_comment_subscriptions_deferred_admin_notices');

// Remove scheduled autopurge events
wp_clear_scheduled_hook('wp_comment_subscriptions_purge');

?>
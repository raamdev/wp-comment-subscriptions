=== WP Comment Subscriptions ===

Plugin Name: WP Comment Subscriptions

Version: 140128
Stable tag: 140128
Requires at least: 2.9.2
Tested up to: 3.8

Plugin URI: http://wordpress.org/extend/plugins/wp-comment-subscriptions/
Description: **DEVELOPMENT HALTED**. All features and bug fixes from WP Comment Subscriptions have been rolled into the latest version of the Subscribe to Comments Reloaded plugin, including a new import routine that will automatically import WP Comment Subscriptions data and options. I have refocused my effort on improving the Subscribe to Comments Reloaded plugin and will no longer be maintaining WP Comment Subscriptions.

Text Domain: wp-comment-subscriptions
Domain Path: /langs

Author: Raam Dev
Author URI: http://raamdev.com/
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=82JLPADVDAB2Q
Contributors: raamdev

License: GNU General Public License v2 or later.

Tags: subscribe, comments, notification, subscription, manage, double check-in, follow, commenting, subscribe to comments, comment subscriptions, comment notifications

**DEVELOPMENT HALTED**. All features and bug fixes from WP Comment Subscriptions have been rolled into the Subscribe to Comments Reloaded plugin.

== Description ==

**DEVELOPMENT HALTED**. All features and bug fixes from WP Comment Subscriptions have been rolled into the Subscribe to Comments Reloaded plugin. Please see the changelog for further information.

== Installation ==

== Frequently Asked Questions ==

== Screenshots ==

== Upgrade Notice ==

= 140128 =

* **DEVELOPMENT HALTED**. All features and bug fixes from WP Comment Subscriptions have been rolled into the Subscribe to Comments Reloaded plugin.

== Changelog ==

= v140128 =

* **DEVELOPMENT OF WP COMMENT SUBSCRIPTIONS HAS BEEN HALTED AND all features and bug fixes have been rolled into the Subscribe to Comments Reloaded plugin.**
* WP Comment Subscriptions was a fork of the Subscribe to Comments Reloaded plugin. This fork was released after I was unable to get in touch with the developers of Subscribe to Comments Reloaded to patch existing bugs. However, shortly after releasing WP Comment Subscriptions, the developers [connected with me](http://wordpress.org/support/topic/subscribe-without-commenting-if-already-subscribed?replies=14#post-5091921) and I agreed to refocus my effort on improving the already established Subscribe to Comments Reloaded plugin.
* **I am refocusing my effort and now actively maintaining Subscribe to Comments Reloaded**. All of the bug fixes and features added by the WP Comment Subscriptions fork have been rolled into the latest version of [Subscribe to Comments Reloaded (v140128)](http://wordpress.org/plugins/subscribe-to-comments-reloaded/changelog/). To continue receiving my bug fixes and feature additions, please switch to Subscribe to Comments Reloaded.
* **Subscribe to Comments Reloaded v140128 includes a new import routine for WP Comment Subscriptions**. This import routine will automatically import subscription data and options from WP Comment Subscriptions to make migration easy. (**For the import routine to work, you must** first make sure that you've **completely deleted** any existing Subscribe to Comments Reloaded plugin, which will clear the database of any old StCR data; then you can install the latest version of StCR and it will detect and import your WP Comment Subscriptions data and options).
* Subscribe to Comments Reloaded also has [a new GitHub repo](https://github.com/stcr/subscribe-to-comments-reloaded). If you have any feature requests or bugs to report, please do so on GitHub.
* To prevent any confusion, I will soon be requesting that the WP Comment Subscriptions plugin be removed from the WordPress.org Plugins Repository. If you have any questions about migrating from WP Comment Subscriptions to Subscribe to Comments Reloaded, please [open a GitHub issue](https://github.com/stcr/subscribe-to-comments-reloaded/issues/new) and I will take care of you.

= v140109 =

* **New 'default subscription type' Option**. If you're using Advanced subscriptions, you can now specify the Advanced default subscription type ("None", "All new comments", or "Replies to this comment") in *Settings -> Comment Subscriptions -> Comment Form -> Advanced default*. This will be the default option shown on the comment form. See: <https://github.com/raamdev/wp-comment-subscriptions/issues/6>
* **New 'HTMLify links in emails' Option**. When using HTML emails for messages you can now choose to have WP Comment Subscriptions automatically HTMLify the links for you (*Settings -> Comment Subscriptions -> Options -> HTMLify links in emails*). You can, of course, leave this option disabled and add your own HTML to the messages if you prefer. See: <https://github.com/raamdev/wp-comment-subscriptions/issues/7>.
* Improved Subscribe to Comments Reloaded import routine.
* Added plugin title to settings pages.
* Improved import messaging to only recommend deactivating the Subscribe to Comments Reloaded plugin when it's actually active. See: <https://github.com/raamdev/wp-comment-subscriptions/issues/2>
* Added settings link to plugins page. See: <https://github.com/raamdev/wp-comment-subscriptions/issues/8>

= v140108 =

* Initial release to the WordPress.org Plugin Repository
* Import existing options and configuration from the Subscribe to Comments Reloaded plugin.
* Import existing comment subscription data from the Subscribe to Comments Reloaded plugin. The imported comment subscription data is now copied to a new `meta_key` with the prefix `_wpcs@_` inside the `wp_postmeta` table. This allows you to delete the Subscribe to Comments Reloaded plugin without losing the comment subscription data. WP Comment Subscriptions is now fully independent and does not rely on data from Subscribe to Comments Reloaded.
* Added admin notices to indicate when data is imported from an existing plugin.

= v140107 =

* Add paragraph tags to comment content when HTML emails enabled
* Add option for BCCing admin on Notifications
* Wrap WP Comment Subscriptions html in a pragraph tag with `comment-form-subscriptions` class
* Update 'Replies only' option value to 'Replies to this comment'
* Fix broken Replies Only feature
* Fix duplicate `MIME-Version` header bug resulting in unsent emails. Fixes a bug where using StCR with other plugins, like WP-Mail-SMTP, results in a quiet duplicate header error. `wp_mail()` already takes care of setting the `MIME-Version` header so this doesn't need to be done again.

= v140106 =

* Renamed functions and WordPress options to reflect the new plugin name (WP Comment Subscriptions). If you're using `subscribe_reloaded_show()` in your WordPress theme, you'll need to change that to `wp_comment_subscriptions_show()`. The subscription data in the database however has not changed and still uses the `stcr@` key, so it's fully compatible with Subscribe to Comments Reloaded v2.0.6.
* Forked the [Subscribe to Comments Reloaded Plugin](http://wordpress.org/plugins/subscribe-to-comments-reloaded/) from v2.0.6

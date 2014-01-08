=== WP Comment Subscriptions ===

Plugin Name: WP Comment Subscriptions

Version: 140107
Stable tag: 140107
Requires at least: 2.9.2
Tested up to: 3.8

Plugin URI: http://wordpress.org/extend/plugins/wp-comment-subscriptions/
Description: WP Comment Subscriptions is a robust plugin that enables commenters to sign up for e-mail notifications. It includes a full-featured subscription manager that your commenters can use to unsubscribe to certain posts or suspend all notifications.

Text Domain: wp-comment-subscriptions
Domain Path: /langs

Author: Raam Dev
Author URI: http://raamdev.com/
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=82JLPADVDAB2Q
Contributors: raamdev

License: GNU General Public License v2 or later.

Tags: subscribe, comments, notification, subscription, manage, double check-in, follow, commenting, subscribe to comments, comment subscriptions, comment notifications

Give commenters the ability to receive e-mail notifications for all new comment replies or only replies to their comment.

== Description ==
WP Comment Subscriptions is a robust plugin that gives commenters the ability to sign up for e-mail notifications of subsequent comment replies or to receive only replies made to their own comment. The plugin includes a full-featured subscription manager that your commenters can use to unsubscribe from certain posts or suspend all notifications. It allows administrators to enable a double opt-in mechanism, requiring users to confirm their subscription by clicking a link they will receive via email.

## Requirements
* Wordpress 2.9.2 or higher
* PHP 5.1 or higher
* MySQL 5.x or higher

## Main Features
* Does not modify Wordpress core tables
* Easily manage and search among your subscriptions
* Supports existing subscriptions created via the Subscribe to Comments Reloaded plugin
* Imports Mark Jaquith's Subscribe To Comments (and its clones) data
* Lets commenters choose to receive only replies to their own comments
* Messages are fully customizable, no poEdit required (and you can use HTML!)
* Disable subscriptions for specific posts
* Compatible with [Fluency Admin](http://deanjrobinson.com/projects/fluency-admin/) and [QTranslate](http://wordpress.org/extend/plugins/qtranslate/)

== Installation ==

1. If you are using Subscribe To Comments by Mark Jaquith or Subscribe To Comments Reloaded, disable them (but don't delete until after activating this plugin, so this plugin can import any existing subscription data)
2. Upload the entire folder and all the subfolders to your WordPress plugins' folder
3. Activate it
5. Customize the Permalink value under Settings > Comment Subscriptions > Management Page > Management URL. It **must** reflect your permalinks' structure
5. If you don't see the checkbox to subscribe, you will have to manually edit your template, and add `<?php if (function_exists('wp_comment_subscriptions_show')) wp_comment_subscriptions_show(); ?>` somewhere in your `comments.php`
6. If you're upgrading from a previous version, please **make sure to deactivate/activate** WP Comment Subscriptions

== Frequently Asked Questions ==

= Can I customize the layout of the management page? =
Yes, each HTML tag has a CSS class or ID that you can use to change its position or look-and-feel

= How do I disable subscriptions for a given post? =
Add a custom field called `stcr_disable_subscriptions` to it, with value 'yes'

= How do I add the management page URL to my posts? =
Use the shortcode `[subscribe-url]`, or use the following code in your theme: 
`if(function_exists('wp_comment_subscriptions_show')) echo '<a href="'.do_shortcode('[subscribe-url]').'">Subscribe</a>";`

= Can I move the subscription checkbox to another position? =
Yes! Just disable the corresponding option under Settings > Comment Form and then add the following code where you want to display the checkbox:
`<?php if (function_exists('wp_comment_subscriptions_show')) wp_comment_subscriptions_show(); ?>`

= This plugin looks eerily similar to the Subscribe To Comments Reloaded plugin; what gives? =
This plugin was forked from the Subscribe To Comments Reloaded plugin. There were several broken features in that plugin (including most importantly the ability to receive only replies to your own comment) and a few missing features (such as the ability to BCC the administrator a copy of all notifications; very useful for debugging!) that WP Comment Subscriptions has fixed. The original Subscribe To Comments Reloaded plugin author stopped working on that plugin and development slowed, so I took it upon myself to fork the plugin and continue development.

== Screenshots ==

1. Manage your subscriptions
2. Use your own messages to interact with your users
3. Configure the Virtual Management page
4. Customize the notification messages
5. Customize the plugin's behavior

== Upgrade Notice ==

== Changelog ==

= v140107 =

* Add paragraph tags to comment content when HTML emails enabled
* Add option for BCCing admin on Notifications
* Wrap WP Comment Subscriptions html in a pragraph tag with `comment-form-subscriptions` class
* Update 'Replies only' option value to 'Replies to this comment'
* Fix broken Replies Only feature
* Fix duplicate `MIME-Version` header bug resulting in unsent emails. Fixes a bug where using StCR with other plugins, like WP-Mail-SMTP, results in a quiet duplicate header error. `wp_mail()` already takes care of setting the `MIME-Version` header so this doesn't need to be done again.

= v140106 =

* Forked the [Subscribe to Comments Reloaded Plugin](http://wordpress.org/plugins/subscribe-to-comments-reloaded/) from v2.0.6

== Language Localization ==

WP Comment Subscriptions can speak your language! If you want to provide a localized file in your
language, use the template files (.pot) you'll find inside the `langs` folder,
and open a new issue on [GitHub](http://github.com/raamdev/wp-comment-subscriptions/issues/) once your
localization is ready. Currently, we support the following languages:

* Danish - [Torben Bendixen](http://www.freelancekonsulenten.dk/)
* Dutch - [Muratje](http://www.muromedia.nl/)
* French - [Anthony](http://imnotgeek.com/), Goormand, Maxime
* German - [derhenry](http://www.derhenry.net/2010/wp-comment-subscriptions/), [Stefan](http://www.beedy.de/)
* Indonesian - [The Masked Cat](http://themaskedcat.tk)
* Italian - myself
* Norwegian - [Odd Henriksen](http://www.oddhenriksen.net/)
* Polish - [Robert Koeseling](http://www.katalogpodkastow.pl), [Filip Cierpich](http://keepmind.eu/)
* Portuguese, Brazil - [Ronaldo Richieri](http://richieri.com), [ClassiNoiva](http://www.classinoiva.com.br), [Luciano](http://litemind.com/)
* Portuguese, Portugal
* Russian - [Marika Bukvonka](http://violetnotes.com)
* Serbian - [Anna Swedziol](http://www.sneg.iz.rs/)
* Spanish - [TodoWordPress team](http://www.todowp.org/), [Juan Luis Perez](http://www.juanluperez.com/)
* Turkish - [MaD, Kali](http://www.dusunsel.com/)

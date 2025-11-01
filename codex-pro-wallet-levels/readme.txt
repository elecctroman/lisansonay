=== Codex Pro – Wallet, Levels, Bonus & Netgsm ===
Contributors: codexpro
Tags: wallet, woocommerce, sms, gamification
Requires at least: 6.1
Tested up to: 6.5
Requires PHP: 8.1
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Codex Pro adds wallet balance, gamification levels, bonus loads and Netgsm SMS notifications to WooCommerce.

== Description ==

* Wallet balance with transaction log
* Gamification levels and points system
* Bonus rules when customers load balance
* Analytics dashboard and 30 day charts
* Netgsm SMS notifications on order events

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/codex-pro-wallet-levels` directory or install via Plugins.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Visit **Codex Pro → Settings** to configure currency, Netgsm credentials and SMS templates.
4. Define levels and bonus rules using the provided database tables or via filters/REST API.
5. Customers can access the wallet area from My Account → Codex Wallet.

== Frequently Asked Questions ==

= Does the plugin support translations? =
Yes. Use the `languages/codex-pro.pot` file to create translation files.

= How do I test Netgsm? =
Fill Netgsm credentials in settings and use the REST endpoint `/wp-json/codex-pro/v1/sms/test` with a phone parameter.

== Changelog ==

= 1.0.0 =
* Initial release.

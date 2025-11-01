<?php
/**
 * Plugin Name:       Codex Pro — WooWallet Admin Suite: Wallet, Points, Bonuses
 * Plugin URI:        https://example.com/
 * Description:       Advanced WooWallet integrations with analytics, bonuses, and modern account dashboards.
 * Version:           1.0.0
 * Author:            Codex Pro
 * Text Domain:       codex-pro
 * Domain Path:       /languages
 */

define( 'CODEX_PRO_ADMIN_SUITE_FILE', __FILE__ );
define( 'CODEX_PRO_ADMIN_SUITE_PATH', plugin_dir_path( __FILE__ ) );
define( 'CODEX_PRO_ADMIN_SUITE_URL', plugin_dir_url( __FILE__ ) );

autoload_codex_pro_admin_suite();

/**
 * Registers the autoloader.
 *
 * @return void
 */
function autoload_codex_pro_admin_suite() {
require_once CODEX_PRO_ADMIN_SUITE_PATH . 'src/Autoloader.php';
\CodexPro\Autoloader::register();
}

/**
 * Plugin activation hook.
 *
 * @return void
 */
function codex_pro_admin_suite_activate() {
\CodexPro\Plugin::instance()->activate();
}
register_activation_hook( __FILE__, 'codex_pro_admin_suite_activate' );

/**
 * Plugin deactivation hook.
 *
 * @return void
 */
function codex_pro_admin_suite_deactivate() {
\CodexPro\Plugin::instance()->deactivate();
}
register_deactivation_hook( __FILE__, 'codex_pro_admin_suite_deactivate' );

/**
 * Bootstrap the plugin when plugins are loaded.
 *
 * @return void
 */
function codex_pro_admin_suite_bootstrap() {
\CodexPro\Plugin::instance()->init();
}
add_action( 'plugins_loaded', 'codex_pro_admin_suite_bootstrap' );

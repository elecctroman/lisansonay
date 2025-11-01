<?php
/**
 * Plugin Name: Codex Pro – Wallet, Levels, Bonus & Netgsm
 * Plugin URI: https://example.com/
 * Description: Wallet, level and Netgsm integration for WooCommerce.
 * Version: 1.0.0
 * Author: Codex Pro
 * Author URI: https://example.com/
 * Text Domain: codex-pro
 * Domain Path: /languages
 * Requires at least: 6.1
 * Requires PHP: 8.1
 * WC requires at least: 8.0
 * License: GPLv2 or later
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'CODEX_PRO_PLUGIN_FILE' ) ) {
    define( 'CODEX_PRO_PLUGIN_FILE', __FILE__ );
}

require_once plugin_dir_path( __FILE__ ) . 'src/Autoloader.php';
CodexPro\Autoloader::register();

register_activation_hook( __FILE__, [ 'CodexPro\\Plugin', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'CodexPro\\Plugin', 'deactivate' ] );
register_uninstall_hook( __FILE__, [ 'CodexPro\\Plugin', 'uninstall' ] );

add_action( 'plugins_loaded', function () {
    CodexPro\Plugin::instance()->boot();
} );

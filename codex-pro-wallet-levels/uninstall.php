<?php
/**
 * Uninstall handler.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

require_once __DIR__ . '/src/Autoloader.php';
CodexPro\Autoloader::register();

CodexPro\Plugin::uninstall();

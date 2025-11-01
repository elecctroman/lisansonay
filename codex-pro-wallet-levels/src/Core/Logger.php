<?php
namespace CodexPro\Core;

use WC_Logger;
use WC_Log_Levels;

/**
 * Simple logger wrapper.
 */
class Logger {
    /**
     * Log data to WooCommerce logger.
     */
    public static function log( string $context, string $message, string $level = 'info' ): void {
        if ( ! class_exists( WC_Logger::class ) ) {
            return;
        }

        $logger = wc_get_logger();
        $logger->log( $level, $message, [ 'source' => 'codex-pro-' . $context ] );
    }
}

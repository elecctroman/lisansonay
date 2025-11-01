<?php
namespace CodexPro\Core;

use WC_Logger;
use WC_Log_Levels;

/**
 * Lightweight logger wrapper.
 */
class Logger {
/**
 * Logger instance cache.
 *
 * @var WC_Logger
 */
protected static $logger;

/**
 * Log an error.
 *
 * @param string $context Context.
 * @param string $message Message.
 * @param array  $data    Additional data.
 * @return void
 */
public static function error( $context, $message, array $data = array() ) {
self::log( $context, $message, $data, WC_Log_Levels::ERROR );
}

/**
 * Generic logger method.
 *
 * @param string $context Context.
 * @param string $message Message.
 * @param array  $data    Data.
 * @param string $level   Level.
 * @return void
 */
public static function log( $context, $message, array $data = array(), $level = WC_Log_Levels::INFO ) {
if ( ! self::$logger ) {
self::$logger = wc_get_logger();
}

$entry = $message;

if ( ! empty( $data ) ) {
$entry .= ' ' . wp_json_encode( $data );
}

self::$logger->log( $level, $entry, array( 'source' => 'codex-pro-' . $context ) );
}
}

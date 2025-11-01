<?php
namespace CodexPro\Core;

use DateInterval;
use DatePeriod;
use DateTimeImmutable;
use Exception;
use wpdb;

/**
 * Adapter layer for WooWallet / TeraWallet.
 */
class Wallet_Adapter {
/**
 * Singleton instance.
 *
 * @var Wallet_Adapter|null
 */
private static $instance = null;

/**
 * Cached transaction table name.
 *
 * @var string|null
 */
private $transaction_table = null;

/**
 * Get singleton instance.
 *
 * @return Wallet_Adapter
 */
public static function instance() {
if ( null === self::$instance ) {
self::$instance = new self();
}

return self::$instance;
}

/**
 * Check whether WooWallet is available.
 *
 * @return bool
 */
public function is_available() {
return function_exists( 'woo_wallet' ) || class_exists( '\\TeraWallet\\WooWallet' );
}

/**
 * Get balance for a user.
 *
 * @param int $user_id User ID.
 * @return float
 */
public function get_balance( $user_id ) {
if ( ! $this->is_available() || ! $user_id ) {
return 0.0;
}

try {
if ( function_exists( 'woo_wallet' ) ) {
return (float) woo_wallet()->get_wallet_balance( $user_id, 'edit' );
}

if ( class_exists( '\\TeraWallet\\WooWallet' ) ) {
return (float) \TeraWallet\WooWallet::instance()->get_wallet_balance( $user_id, 'edit' );
}
} catch ( Exception $e ) {
Logger::error( 'wallet_get_balance', $e->getMessage(), array( 'user_id' => $user_id ) );
}

return 0.0;
}

/**
 * Credit wallet via WooWallet API.
 *
 * @param int    $user_id User ID.
 * @param float  $amount  Amount.
 * @param string $note    Note.
 * @return bool
 */
public function credit( $user_id, $amount, $note = '' ) {
if ( ! $this->is_available() ) {
return false;
}

try {
if ( function_exists( 'woo_wallet' ) ) {
woo_wallet()->credit( $user_id, $amount, $note );
return true;
}

if ( class_exists( '\\TeraWallet\\WooWallet' ) ) {
\TeraWallet\WooWallet::instance()->credit( $user_id, $amount, $note );
return true;
}
} catch ( Exception $e ) {
Logger::error( 'wallet_credit', $e->getMessage(), array( 'user_id' => $user_id ) );
}

return false;
}

/**
 * Debit wallet via WooWallet API.
 *
 * @param int    $user_id User ID.
 * @param float  $amount  Amount.
 * @param string $note    Note.
 * @return bool
 */
public function debit( $user_id, $amount, $note = '' ) {
if ( ! $this->is_available() ) {
return false;
}

try {
if ( function_exists( 'woo_wallet' ) ) {
woo_wallet()->debit( $user_id, $amount, $note );
return true;
}

if ( class_exists( '\\TeraWallet\\WooWallet' ) ) {
\TeraWallet\WooWallet::instance()->debit( $user_id, $amount, $note );
return true;
}
} catch ( Exception $e ) {
Logger::error( 'wallet_debit', $e->getMessage(), array( 'user_id' => $user_id ) );
}

return false;
}

/**
 * Get recent transactions for a user.
 *
 * @param int $user_id User ID.
 * @param int $limit   Limit.
 * @return array
 */
public function get_recent_tx( $user_id, $limit = 20 ) {
global $wpdb;

if ( ! $this->is_available() ) {
return array();
}

$table = $this->get_transaction_table();

if ( ! $table ) {
return array();
}

$query = $wpdb->prepare( "SELECT * FROM {$table} WHERE user_id = %d ORDER BY transaction_id DESC LIMIT %d", $user_id, $limit );

return $wpdb->get_results( $query, ARRAY_A );
}

/**
 * Build analytics for the last 30 days.
 *
 * @param int $user_id Optional user.
 * @return array
 */
public function get_daily_stats_last_30( $user_id = 0 ) {
$days = $this->build_days();
$data = array();

foreach ( $days as $day ) {
$data[ $day ] = array(
'credit' => 0,
'debit'  => 0,
'orders' => 0,
);
}

return $data;
}

/**
 * Determine WooWallet transaction table name.
 *
 * @return string|null
 */
private function get_transaction_table() {
if ( null !== $this->transaction_table ) {
return $this->transaction_table;
}

global $wpdb;

$candidates = array(
$wpdb->prefix . 'woo_wallet_transactions',
$wpdb->prefix . 'woo_wallet_transaction_meta',
);

foreach ( $candidates as $table ) {
$found = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );

if ( $found ) {
$this->transaction_table = $table;
return $table;
}
}

return null;
}

/**
 * Build array of date strings for last 30 days.
 *
 * @return array
 */
private function build_days() {
$timezone = wp_timezone();
$now      = new DateTimeImmutable( 'now', $timezone );
$start    = $now->sub( new DateInterval( 'P29D' ) );
$period   = new DatePeriod( $start, new DateInterval( 'P1D' ), $now->add( new DateInterval( 'P1D' ) ) );
$days     = array();

foreach ( $period as $date ) {
$days[] = $date->format( 'Y-m-d' );
}

return $days;
}
}

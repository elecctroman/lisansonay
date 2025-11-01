<?php
namespace CodexPro\Core;

use DateInterval;
use DateTimeImmutable;

/**
 * Handles daily analytics aggregation.
 */
class Analytics {
/**
 * Singleton instance.
 *
 * @var Analytics|null
 */
private static $instance = null;

/**
 * Get singleton instance.
 *
 * @return Analytics
 */
public static function instance() {
if ( null === self::$instance ) {
self::$instance = new self();
}

return self::$instance;
}

/**
 * Schedule cron events.
 *
 * @return void
 */
public function schedule_events() {
if ( ! wp_next_scheduled( 'codex_pro_daily_analytics' ) ) {
$time = $this->get_midnight_timestamp();
wp_schedule_event( $time, 'daily', 'codex_pro_daily_analytics' );
}

add_action( 'codex_pro_daily_analytics', array( $this, 'generate_daily_summary' ) );
}

/**
 * Remove cron events.
 *
 * @return void
 */
public function clear_scheduled_events() {
$timestamp = wp_next_scheduled( 'codex_pro_daily_analytics' );

if ( $timestamp ) {
wp_unschedule_event( $timestamp, 'codex_pro_daily_analytics' );
}
}

/**
 * Generate daily summary placeholder.
 *
 * @return void
 */
public function generate_daily_summary() {
// Placeholder hook so integrators can extend functionality.
do_action( 'codex_pro_generate_daily_summary' );
}

/**
 * Get timestamp for next 03:00.
 *
 * @return int
 */
private function get_midnight_timestamp() {
$timezone = wp_timezone();
$now      = new DateTimeImmutable( 'now', $timezone );
$target   = $now->setTime( 3, 0, 0 );

if ( $target < $now ) {
$target = $target->add( new DateInterval( 'P1D' ) );
}

return $target->getTimestamp();
}
}

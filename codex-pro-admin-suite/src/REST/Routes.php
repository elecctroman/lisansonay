<?php
namespace CodexPro\REST;

use CodexPro\Core\Wallet_Adapter;

/**
 * Registers REST API routes.
 */
class Routes {
/**
 * Singleton instance.
 *
 * @var Routes|null
 */
private static $instance = null;

/**
 * Get singleton instance.
 *
 * @return Routes
 */
public static function instance() {
if ( null === self::$instance ) {
self::$instance = new self();
self::$instance->hooks();
}

return self::$instance;
}

/**
 * Register hooks.
 *
 * @return void
 */
private function hooks() {
add_action( 'rest_api_init', array( $this, 'register_routes' ) );
}

/**
 * Register REST routes.
 *
 * @return void
 */
public function register_routes() {
register_rest_route(
'codex-pro/v1',
'/account/summary',
array(
'permission_callback' => array( $this, 'check_user_logged_in' ),
'methods'             => 'GET',
'callback'            => array( $this, 'get_account_summary' ),
)
);
}

/**
 * REST permission callback.
 *
 * @return bool
 */
public function check_user_logged_in() {
return is_user_logged_in();
}

/**
 * Get account summary response.
 *
 * @return \WP_REST_Response
 */
public function get_account_summary() {
$adapter = Wallet_Adapter::instance();
$user_id = get_current_user_id();

return rest_ensure_response(
array(
'balance' => $adapter->get_balance( $user_id ),
'activity' => $adapter->get_daily_stats_last_30( $user_id ),
)
);
}
}

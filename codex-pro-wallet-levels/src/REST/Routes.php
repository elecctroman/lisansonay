<?php
namespace CodexPro\REST;

use CodexPro\Core\Wallet;
use CodexPro\Core\Points;
use CodexPro\Core\Levels;
use CodexPro\Core\Bonuses;
use CodexPro\Core\SMS_Netgsm;
use CodexPro\Plugin;
use WP_REST_Server;
use WP_REST_Request;
use WP_Error;

/**
 * Registers REST API routes.
 */
class Routes {
    /**
     * Init hooks.
     */
    public static function init(): void {
        add_action( 'rest_api_init', [ __CLASS__, 'register_routes' ] );
    }

    /**
     * Register routes.
     */
    public static function register_routes(): void {
        register_rest_route( 'codex-pro/v1', '/wallet', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [ __CLASS__, 'get_wallet' ],
            'permission_callback' => [ __CLASS__, 'check_user' ],
        ] );

        register_rest_route( 'codex-pro/v1', '/wallet/tx', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [ __CLASS__, 'get_wallet_tx' ],
            'permission_callback' => [ __CLASS__, 'check_user' ],
            'args'                => [
                'page' => [ 'default' => 1 ],
                'type' => [],
            ],
        ] );

        register_rest_route( 'codex-pro/v1', '/wallet/load', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [ __CLASS__, 'load_wallet' ],
            'permission_callback' => [ __CLASS__, 'check_user' ],
        ] );

        register_rest_route( 'codex-pro/v1', '/points', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [ __CLASS__, 'get_points' ],
            'permission_callback' => [ __CLASS__, 'check_user' ],
        ] );

        register_rest_route( 'codex-pro/v1', '/sms/test', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [ __CLASS__, 'test_sms' ],
            'permission_callback' => function () {
                return current_user_can( 'manage_codex_pro' );
            },
        ] );
    }

    /**
     * Checks if user is authenticated.
     */
    public static function check_user(): bool {
        return is_user_logged_in();
    }

    /**
     * Returns wallet summary.
     */
    public static function get_wallet(): array {
        $user_id = get_current_user_id();
        return [
            'balance' => Wallet::get_balance( $user_id ),
            'level'   => Levels::get_user_level( $user_id ),
        ];
    }

    /**
     * Returns wallet transactions.
     */
    public static function get_wallet_tx( WP_REST_Request $request ): array {
        $page    = max( 1, (int) $request->get_param( 'page' ) );
        $type    = $request->get_param( 'type' );
        $limit   = 20;
        $offset  = ( $page - 1 ) * $limit;
        $user_id = get_current_user_id();
        $rows    = Wallet::get_transactions( $user_id, $limit, $offset, $type ? sanitize_text_field( $type ) : null );
        return [ 'items' => $rows, 'page' => $page ];
    }

    /**
     * Handle wallet load request.
     */
    public static function load_wallet( WP_REST_Request $request ) {
        $amount = (float) $request->get_param( 'amount' );
        if ( $amount <= 0 ) {
            return new WP_Error( 'invalid_amount', __( 'Amount must be positive.', 'codex-pro' ) );
        }
        $user_id = get_current_user_id();
        $bonus   = Bonuses::calculate_bonus( $amount );
        Wallet::add_balance( $user_id, $amount, __( 'REST load', 'codex-pro' ) );
        if ( $bonus > 0 ) {
            Wallet::add_balance( $user_id, $bonus, __( 'Bonus', 'codex-pro' ), null, 'bonus' );
        }
        return [ 'balance' => Wallet::get_balance( $user_id ), 'bonus' => $bonus ];
    }

    /**
     * Returns points summary.
     */
    public static function get_points(): array {
        $user_id = get_current_user_id();
        return [
            'points' => Points::get_total_points( $user_id ),
            'level'  => Levels::get_user_level( $user_id ),
        ];
    }

    /**
     * Sends test sms.
     */
    public static function test_sms( WP_REST_Request $request ) {
        $phone = sanitize_text_field( $request->get_param( 'phone' ) );
        if ( empty( $phone ) ) {
            return new WP_Error( 'no_phone', __( 'Phone number required.', 'codex-pro' ) );
        }
        $message = __( 'Codex Pro test message.', 'codex-pro' );
        return SMS_Netgsm::send_sms( $phone, $message );
    }
}

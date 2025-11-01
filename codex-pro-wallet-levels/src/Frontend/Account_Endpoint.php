<?php
namespace CodexPro\Frontend;

use CodexPro\Core\Wallet;
use CodexPro\Core\Points;
use CodexPro\Core\Levels;
use CodexPro\Core\Analytics;
use CodexPro\Core\Bonuses;
use CodexPro\Plugin;

/**
 * Adds My Account endpoint for wallet.
 */
class Account_Endpoint {
    /**
     * Endpoint slug.
     */
    public const ENDPOINT = 'codex-wallet';

    /**
     * Init hooks.
     */
    public static function init(): void {
        add_action( 'init', [ __CLASS__, 'add_endpoint' ] );
        add_filter( 'woocommerce_account_menu_items', [ __CLASS__, 'add_menu_item' ] );
        add_action( 'woocommerce_account_' . self::ENDPOINT . '_endpoint', [ __CLASS__, 'render_wallet_page' ] );
        add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );
    }

    /**
     * Register endpoint.
     */
    public static function add_endpoint(): void {
        add_rewrite_endpoint( self::ENDPOINT, EP_ROOT | EP_PAGES );
    }

    /**
     * Add menu item in My Account.
     */
    public static function add_menu_item( array $items ): array {
        $items[ self::ENDPOINT ] = __( 'Codex Wallet', 'codex-pro' );
        return $items;
    }

    /**
     * Enqueue frontend assets.
     */
    public static function enqueue_assets(): void {
        if ( ! is_account_page() ) {
            return;
        }
        wp_enqueue_style( 'codex-pro-wallet', plugins_url( 'src/Frontend/Assets/css/wallet.css', CODEX_PRO_PLUGIN_FILE ), [], '1.0.0' );
        wp_enqueue_script( 'chartjs', plugins_url( 'vendor/chart.min.js', CODEX_PRO_PLUGIN_FILE ), [], '4.4.0', true );
        wp_enqueue_script( 'codex-pro-wallet', plugins_url( 'src/Frontend/Assets/js/wallet.js', CODEX_PRO_PLUGIN_FILE ), [ 'chartjs' ], '1.0.0', true );

        $analytics = Analytics::get_range( 30 );
        wp_localize_script( 'codex-pro-wallet', 'codexProAnalytics', [ 'data' => $analytics ] );
    }

    /**
     * Render wallet page.
     */
    public static function render_wallet_page(): void {
        if ( ! is_user_logged_in() ) {
            echo esc_html__( 'You must login to access wallet.', 'codex-pro' );
            return;
        }

        $user_id   = get_current_user_id();
        $balance   = Wallet::get_balance( $user_id );
        $points    = Points::get_total_points( $user_id );
        $level     = Levels::get_user_level( $user_id );
        $tx        = Wallet::get_transactions( $user_id, 20 );
        $point_log = Points::get_recent( $user_id, 20 );
        $bonus     = Bonuses::calculate_bonus( 100 );

        include plugin_dir_path( CODEX_PRO_PLUGIN_FILE ) . 'src/Frontend/Templates/wallet.php';
    }
}

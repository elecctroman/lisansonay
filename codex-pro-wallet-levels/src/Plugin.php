<?php
namespace CodexPro;

use CodexPro\Core\DB;
use CodexPro\Core\Wallet;
use CodexPro\Core\Points;
use CodexPro\Core\Levels;
use CodexPro\Core\Bonuses;
use CodexPro\Core\Analytics;
use CodexPro\Core\SMS_Netgsm;
use CodexPro\Core\Logger;
use CodexPro\Admin\Menu;
use CodexPro\Frontend\Shortcodes;
use CodexPro\Frontend\Account_Endpoint;
use CodexPro\WC\Gateway_Wallet;
use CodexPro\WC\Order_Hooks;
use CodexPro\WC\Product_Meta;
use CodexPro\REST\Routes;

/**
 * Main plugin bootstrap singleton.
 */
class Plugin {
    /**
     * Singleton instance.
     *
     * @var Plugin|null
     */
    protected static ?Plugin $instance = null;

    /**
     * Option key for plugin settings.
     */
    public const OPTION_KEY = 'codex_pro_settings';

    /**
     * Database version key.
     */
    public const DB_VERSION_KEY = 'codex_pro_db_version';

    /**
     * Current schema version.
     */
    public const DB_VERSION = '1.0.0';

    /**
     * Plugin bootstrap.
     */
    public function boot(): void {
        $this->load_textdomain();
        $this->register_services();
        $this->register_hooks();
    }

    /**
     * Returns the singleton instance.
     */
    public static function instance(): Plugin {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Plugin activation handler.
     */
    public static function activate(): void {
        DB::migrate();
        self::add_capabilities();
        self::maybe_schedule_events();
    }

    /**
     * Plugin deactivation handler.
     */
    public static function deactivate(): void {
        wp_clear_scheduled_hook( 'codex_pro_daily_analytics' );
    }

    /**
     * Plugin uninstall handler.
     */
    public static function uninstall(): void {
        if ( ! current_user_can( 'activate_plugins' ) ) {
            return;
        }

        delete_option( self::OPTION_KEY );
        delete_option( self::DB_VERSION_KEY );
        wp_clear_scheduled_hook( 'codex_pro_daily_analytics' );
        DB::uninstall();
    }

    /**
     * Load translations.
     */
    protected function load_textdomain(): void {
        load_plugin_textdomain( 'codex-pro', false, dirname( plugin_basename( CODEX_PRO_PLUGIN_FILE ) ) . '/languages' );
    }

    /**
     * Registers plugin services.
     */
    protected function register_services(): void {
        DB::init();
        Wallet::init();
        Points::init();
        Levels::init();
        Bonuses::init();
        Analytics::init();
        SMS_Netgsm::init();
        Menu::init();
        Shortcodes::init();
        Account_Endpoint::init();
        Order_Hooks::init();
        Product_Meta::init();
        Routes::init();
    }

    /**
     * Registers general hooks.
     */
    protected function register_hooks(): void {
        add_filter( 'woocommerce_payment_gateways', [ $this, 'register_gateway' ] );
        add_action( 'init', [ $this, 'register_block' ] );
        add_action( 'admin_init', [ self::class, 'maybe_schedule_events' ] );
    }

    /**
     * Adds the wallet gateway to WooCommerce gateways.
     *
     * @param array $gateways Gateways.
     *
     * @return array
     */
    public function register_gateway( array $gateways ): array {
        $gateways[] = Gateway_Wallet::class;
        return $gateways;
    }

    /**
     * Registers dynamic block for wallet summary.
     */
    public function register_block(): void {
        if ( ! function_exists( 'register_block_type' ) ) {
            return;
        }

        register_block_type( 'codex-pro/wallet-summary', [
            'render_callback' => [ Shortcodes::class, 'render_wallet_summary_block' ],
            'attributes'      => [],
            'api_version'     => 2,
        ] );
    }

    /**
     * Adds plugin capabilities to administrator role.
     */
    public static function add_capabilities(): void {
        $role = get_role( 'administrator' );
        if ( $role ) {
            $role->add_cap( 'manage_codex_pro' );
            $role->add_cap( 'edit_codex_wallet' );
        }
    }

    /**
     * Schedule cron events if not scheduled.
     */
    public static function maybe_schedule_events(): void {
        if ( ! wp_next_scheduled( 'codex_pro_daily_analytics' ) ) {
            $timestamp = strtotime( 'tomorrow 3am' );
            wp_schedule_event( $timestamp, 'daily', 'codex_pro_daily_analytics' );
        }
    }
}

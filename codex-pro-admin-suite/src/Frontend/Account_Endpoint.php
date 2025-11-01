<?php
namespace CodexPro\Frontend;

use CodexPro\Core\Wallet_Adapter;
use CodexPro\Plugin;

/**
 * Registers My Account endpoint and assets.
 */
class Account_Endpoint {
	/**
	 * Singleton instance.
	 *
	 * @var Account_Endpoint|null
	 */
	private static $instance = null;

	/**
	 * Endpoint slug.
	 *
	 * @var string
	 */
	private $endpoint_slug = 'codex-wallet';

	/**
	 * Get singleton instance.
	 *
	 * @return Account_Endpoint
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
		add_action( 'init', array( $this, 'register_endpoint' ) );
		add_filter( 'woocommerce_account_menu_items', array( $this, 'add_menu_item' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Get current endpoint slug.
	 *
	 * @return string
	 */
	private function get_endpoint_slug() {
		$settings = get_option( 'codex_pro_settings', array() );

		if ( ! empty( $settings['account_endpoint'] ) ) {
			$this->endpoint_slug = sanitize_title( $settings['account_endpoint'] );
		}

		return $this->endpoint_slug;
	}

	/**
	 * Register endpoint for My Account.
	 *
	 * @return void
	 */
	public function register_endpoint() {
		$slug = $this->get_endpoint_slug();
		add_rewrite_endpoint( $slug, EP_ROOT | EP_PAGES );

		if ( ! has_action( 'woocommerce_account_' . $slug . '_endpoint', array( $this, 'render_endpoint' ) ) ) {
			add_action( 'woocommerce_account_' . $slug . '_endpoint', array( $this, 'render_endpoint' ) );
		}
	}

	/**
	 * Add menu item.
	 *
	 * @param array $items Menu items.
	 * @return array
	 */
	public function add_menu_item( $items ) {
		$slug   = $this->get_endpoint_slug();
		$settings = get_option( 'codex_pro_settings', array() );
		$label  = ! empty( $settings['account_label'] ) ? sanitize_text_field( $settings['account_label'] ) : __( 'Codex Wallet', 'codex-pro' );

		$items[ $slug ] = $label;

		return $items;
	}

	/**
	 * Render endpoint content.
	 *
	 * @return void
	 */
	public function render_endpoint() {
		$adapter = Wallet_Adapter::instance();

		if ( ! $adapter->is_available() ) {
			printf( '<div class="woocommerce-info">%s</div>', esc_html__( 'Wallet features require WooWallet to be installed.', 'codex-pro' ) );
			return;
		}

		$user_id      = get_current_user_id();
		$stats        = $adapter->get_daily_stats_last_30( $user_id );
		$balance      = $adapter->get_balance( $user_id );
		$transactions = $adapter->get_recent_tx( $user_id, 10 );

		include CODEX_PRO_ADMIN_SUITE_PATH . 'src/Frontend/Templates/account.php';
	}

	/**
	 * Enqueue frontend assets.
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		if ( ! is_account_page() ) {
			return;
		}

		wp_enqueue_style( 'codex-pro-account', CODEX_PRO_ADMIN_SUITE_URL . 'assets/frontend/css/account.css', array(), Plugin::VERSION );
		wp_enqueue_script( 'codex-pro-chart', CODEX_PRO_ADMIN_SUITE_URL . 'vendor/chart.js/chart.umd.js', array(), '4.4.0', true );
		wp_enqueue_script( 'codex-pro-account', CODEX_PRO_ADMIN_SUITE_URL . 'assets/frontend/js/account.js', array( 'codex-pro-chart' ), Plugin::VERSION, true );
	}
}

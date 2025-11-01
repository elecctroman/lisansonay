<?php
namespace CodexPro\Admin;

use CodexPro\Core\Wallet_Adapter;
use CodexPro\Plugin;

/**
 * Registers Codex Pro admin menu pages.
 */
class Menu {
	/**
	 * Singleton instance.
	 *
	 * @var Menu|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return Menu
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
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Register Codex Pro menu and pages.
	 *
	 * @return void
	 */
	public function register_menu() {
		add_menu_page(
			esc_html__( 'Codex Pro', 'codex-pro' ),
			esc_html__( 'Codex Pro', 'codex-pro' ),
			'manage_codex_pro',
			'codex-pro-dashboard',
			array( $this, 'render_dashboard' ),
			'dashicons-chart-area',
			58
		);

		add_submenu_page(
			'codex-pro-dashboard',
			esc_html__( 'Dashboard', 'codex-pro' ),
			esc_html__( 'Dashboard', 'codex-pro' ),
			'manage_codex_pro',
			'codex-pro-dashboard',
			array( $this, 'render_dashboard' )
		);

		add_submenu_page(
			'codex-pro-dashboard',
			esc_html__( 'Wallets', 'codex-pro' ),
			esc_html__( 'Wallets', 'codex-pro' ),
			'manage_codex_pro',
			'codex-pro-wallets',
			array( $this, 'render_wallets' )
		);

		add_submenu_page(
			'codex-pro-dashboard',
			esc_html__( 'Points', 'codex-pro' ),
			esc_html__( 'Points', 'codex-pro' ),
			'manage_codex_pro',
			'codex-pro-points',
			array( $this, 'render_points' )
		);

		add_submenu_page(
			'codex-pro-dashboard',
			esc_html__( 'Bonuses', 'codex-pro' ),
			esc_html__( 'Bonuses', 'codex-pro' ),
			'manage_codex_pro',
			'codex-pro-bonuses',
			array( $this, 'render_bonuses' )
		);

		add_submenu_page(
			'codex-pro-dashboard',
			esc_html__( 'Settings', 'codex-pro' ),
			esc_html__( 'Settings', 'codex-pro' ),
			'manage_codex_pro',
			'codex-pro-settings',
			array( $this, 'render_settings' )
		);

		add_submenu_page(
			'codex-pro-dashboard',
			esc_html__( 'Logs', 'codex-pro' ),
			esc_html__( 'Logs', 'codex-pro' ),
			'manage_codex_pro',
			'codex-pro-logs',
			array( $this, 'render_logs' )
		);
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @param string $hook Hook suffix.
	 * @return void
	 */
	public function enqueue_assets( $hook ) {
		if ( false === strpos( $hook, 'codex-pro' ) ) {
			return;
		}

		wp_enqueue_style( 'codex-pro-admin', CODEX_PRO_ADMIN_SUITE_URL . 'assets/admin/css/admin.css', array(), Plugin::VERSION );
		wp_enqueue_script( 'codex-pro-chart', CODEX_PRO_ADMIN_SUITE_URL . 'vendor/chart.js/chart.umd.js', array(), '4.4.0', true );
		wp_enqueue_script( 'codex-pro-admin', CODEX_PRO_ADMIN_SUITE_URL . 'assets/admin/js/admin.js', array( 'jquery', 'codex-pro-chart' ), Plugin::VERSION, true );
		wp_localize_script(
			'codex-pro-admin',
			'codexProDashboardStrings',
			array(
				'credit' => esc_html__( 'Credit', 'codex-pro' ),
				'debit'  => esc_html__( 'Debit', 'codex-pro' ),
			)
		);
	}

	/**
	 * Render dashboard page.
	 *
	 * @return void
	 */
	public function render_dashboard() {
		$adapter = Wallet_Adapter::instance();
		$stats   = $adapter->get_daily_stats_last_30();

		include CODEX_PRO_ADMIN_SUITE_PATH . 'src/Admin/views/dashboard.php';
	}

	/**
	 * Render wallets page.
	 *
	 * @return void
	 */
	public function render_wallets() {
		$adapter = Wallet_Adapter::instance();

		include CODEX_PRO_ADMIN_SUITE_PATH . 'src/Admin/views/wallets.php';
	}

	/**
	 * Render points page.
	 *
	 * @return void
	 */
	public function render_points() {
		include CODEX_PRO_ADMIN_SUITE_PATH . 'src/Admin/views/points.php';
	}

	/**
	 * Render bonuses page.
	 *
	 * @return void
	 */
	public function render_bonuses() {
		include CODEX_PRO_ADMIN_SUITE_PATH . 'src/Admin/views/bonuses.php';
	}

	/**
	 * Render settings page.
	 *
	 * @return void
	 */
	public function render_settings() {
		include CODEX_PRO_ADMIN_SUITE_PATH . 'src/Admin/views/settings.php';
	}

	/**
	 * Render logs page.
	 *
	 * @return void
	 */
	public function render_logs() {
		include CODEX_PRO_ADMIN_SUITE_PATH . 'src/Admin/views/logs.php';
	}
}

<?php
namespace CodexPro;

use CodexPro\Admin\Menu;
use CodexPro\Core\Analytics;
use CodexPro\Core\DB;
use CodexPro\Core\Wallet_Adapter;
use CodexPro\Frontend\Account_Endpoint;
use CodexPro\REST\Routes as Rest_Routes;

/**
 * Main plugin bootstrap singleton.
 */
class Plugin {
	/**
	 * Singleton instance.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	const VERSION = '1.0.0';

	/**
	 * Gets the singleton instance.
	 *
	 * @return Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Private constructor to enforce singleton.
	 */
	private function __construct() {}

	/**
	 * Activate hook handler.
	 *
	 * @return void
	 */
	public function activate() {
		DB::instance()->maybe_install();
		Analytics::instance()->schedule_events();
		$this->add_capabilities();
		Account_Endpoint::instance()->register_endpoint();
		flush_rewrite_rules();
	}

	/**
	 * Add required capabilities.
	 *
	 * @return void
	 */
	private function add_capabilities() {
		$role = get_role( 'administrator' );

		if ( $role ) {
			$role->add_cap( 'manage_codex_pro' );
		}
	}

	/**
	 * Deactivation handler.
	 *
	 * @return void
	 */
	public function deactivate() {
		Analytics::instance()->clear_scheduled_events();
		flush_rewrite_rules();
	}

	/**
	 * Initialise the plugin.
	 *
	 * @return void
	 */
	public function init() {
		load_plugin_textdomain( 'codex-pro', false, dirname( plugin_basename( CODEX_PRO_ADMIN_SUITE_FILE ) ) . '/languages' );
		$this->register_services();
	}

	/**
	 * Register main services.
	 *
	 * @return void
	 */
	private function register_services() {
		Wallet_Adapter::instance();
		Analytics::instance()->schedule_events();
		Menu::instance();
		Account_Endpoint::instance();
		Rest_Routes::instance();
	}
}

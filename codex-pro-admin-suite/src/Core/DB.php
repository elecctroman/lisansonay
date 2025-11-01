<?php
namespace CodexPro\Core;

use wpdb;

/**
 * Handles plugin database schema.
 */
class DB {
	/**
	 * DB version option key.
	 */
	const OPTION_KEY = 'codex_pro_db_version';

	/**
	 * Current schema version.
	 */
	const VERSION = '1.0.0';

	/**
	 * Singleton instance.
	 *
	 * @var DB|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return DB
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Maybe install or upgrade schema.
	 *
	 * @return void
	 */
	public function maybe_install() {
		$installed = get_option( self::OPTION_KEY );

		if ( self::VERSION !== $installed ) {
			$this->install();
			update_option( self::OPTION_KEY, self::VERSION );
		}
	}

	/**
	 * Install database tables via dbDelta.
	 *
	 * @return void
	 */
	private function install() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset = $wpdb->get_charset_collate();
		$prefix  = $wpdb->prefix;

		$tables = array();

		$tables[] = "CREATE TABLE {$prefix}codexp_points (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT UNSIGNED NOT NULL,
			product_id BIGINT UNSIGNED NULL,
			order_id BIGINT UNSIGNED NULL,
			points INT NOT NULL,
			reason VARCHAR(120) NOT NULL,
			created_at DATETIME NOT NULL,
			PRIMARY KEY (id),
			KEY user_id (user_id),
			KEY order_id (order_id),
			KEY created_at (created_at)
		) $charset";

		$tables[] = "CREATE TABLE {$prefix}codexp_levels (
			level_id INT NOT NULL,
			name VARCHAR(60) NOT NULL,
			min_points INT NOT NULL,
			perks_json TEXT NULL,
			PRIMARY KEY (level_id),
			KEY min_points (min_points)
		) $charset";

		$tables[] = "CREATE TABLE {$prefix}codexp_bonus_rules (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			name VARCHAR(80) NOT NULL,
			min_amount DECIMAL(18,6) NOT NULL,
			bonus_type ENUM('percent','fixed') NOT NULL DEFAULT 'percent',
			bonus_value DECIMAL(18,6) NOT NULL,
			active TINYINT(1) NOT NULL DEFAULT 1,
			PRIMARY KEY (id),
			KEY min_amount (min_amount),
			KEY active (active)
		) $charset";

		$tables[] = "CREATE TABLE {$prefix}codexp_analytics_daily (
			date DATE NOT NULL,
			orders_count INT NOT NULL DEFAULT 0,
			balance_loaded DECIMAL(18,6) NOT NULL DEFAULT 0,
			balance_spent DECIMAL(18,6) NOT NULL DEFAULT 0,
			PRIMARY KEY (date)
		) $charset";

		foreach ( $tables as $sql ) {
			dbDelta( $sql );
		}
	}
}

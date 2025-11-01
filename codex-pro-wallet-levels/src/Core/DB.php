<?php
namespace CodexPro\Core;

use CodexPro\Plugin;
use wpdb;

/**
 * Handles database setup and queries helpers.
 */
class DB {
    /**
     * Cached table names.
     *
     * @var array<string,string>
     */
    protected static array $tables = [];

    /**
     * Init table names.
     */
    public static function init(): void {
        global $wpdb;

        self::$tables = [
            'wallet_tx'    => $wpdb->prefix . 'codexp_wallet_tx',
            'points'       => $wpdb->prefix . 'codexp_points',
            'levels'       => $wpdb->prefix . 'codexp_levels',
            'bonus_rules'  => $wpdb->prefix . 'codexp_bonus_rules',
            'analytics'    => $wpdb->prefix . 'codexp_analytics_daily',
        ];
    }

    /**
     * Returns table name.
     */
    public static function table( string $key ): string {
        return self::$tables[ $key ] ?? '';
    }

    /**
     * Run database migrations.
     */
    public static function migrate(): void {
        self::init();
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset_collate = $wpdb->get_charset_collate();

        $sql = [];
        $wallet_tx_table = self::table( 'wallet_tx' );
        $sql[]           = "CREATE TABLE {$wallet_tx_table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL,
            order_id BIGINT UNSIGNED NULL,
            type ENUM('load','spend','refund','bonus') NOT NULL,
            amount DECIMAL(18,6) NOT NULL,
            balance_after DECIMAL(18,6) NOT NULL,
            note TEXT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY order_id (order_id),
            KEY type (type),
            KEY created_at (created_at)
        ) {$charset_collate};";

        $points_table = self::table( 'points' );
        $sql[]        = "CREATE TABLE {$points_table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL,
            product_id BIGINT UNSIGNED NULL,
            order_id BIGINT UNSIGNED NULL,
            points INT NOT NULL,
            reason VARCHAR(120) NOT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY product_id (product_id),
            KEY order_id (order_id),
            KEY created_at (created_at)
        ) {$charset_collate};";

        $levels_table = self::table( 'levels' );
        $sql[]        = "CREATE TABLE {$levels_table} (
            level_id INT UNSIGNED NOT NULL,
            name VARCHAR(60) NOT NULL,
            min_points INT NOT NULL,
            perks_json TEXT NULL,
            PRIMARY KEY (level_id),
            KEY min_points (min_points)
        ) {$charset_collate};";

        $bonus_table = self::table( 'bonus_rules' );
        $sql[]       = "CREATE TABLE {$bonus_table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(80) NOT NULL,
            min_amount DECIMAL(18,6) NOT NULL,
            bonus_type ENUM('percent','fixed') NOT NULL,
            bonus_value DECIMAL(18,6) NOT NULL,
            active TINYINT(1) NOT NULL DEFAULT 1,
            PRIMARY KEY (id),
            KEY min_amount (min_amount),
            KEY active (active)
        ) {$charset_collate};";

        $analytics_table = self::table( 'analytics' );
        $sql[]           = "CREATE TABLE {$analytics_table} (
            date DATE NOT NULL,
            orders_count INT NOT NULL DEFAULT 0,
            balance_loaded DECIMAL(18,6) NOT NULL DEFAULT 0,
            balance_spent DECIMAL(18,6) NOT NULL DEFAULT 0,
            PRIMARY KEY (date)
        ) {$charset_collate};";

        foreach ( $sql as $query ) {
            dbDelta( $query );
        }

        update_option( Plugin::DB_VERSION_KEY, Plugin::DB_VERSION );
    }

    /**
     * Cleanup tables on uninstall.
     */
    public static function uninstall(): void {
        global $wpdb;
        foreach ( self::$tables as $table ) {
            $wpdb->query( "DROP TABLE IF EXISTS {$table}" );
        }
    }
}

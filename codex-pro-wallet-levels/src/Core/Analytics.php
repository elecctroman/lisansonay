<?php
namespace CodexPro\Core;

use wpdb;

/**
 * Analytics calculations.
 */
class Analytics {
    /**
     * Init hooks.
     */
    public static function init(): void {
        add_action( 'codex_pro_daily_analytics', [ __CLASS__, 'capture_daily_summary' ] );
    }

    /**
     * Capture yesterday summary.
     */
    public static function capture_daily_summary(): void {
        global $wpdb;
        $table = DB::table( 'analytics' );

        $date = gmdate( 'Y-m-d', strtotime( '-1 day' ) );

        $orders_count = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_type = 'shop_order' AND post_status IN ('wc-completed') AND DATE(post_date_gmt) = %s", $date ) );

        $wallet_table   = DB::table( 'wallet_tx' );
        $balance_loaded = (float) $wpdb->get_var( $wpdb->prepare( "SELECT SUM(amount) FROM {$wallet_table} WHERE type IN ('load','bonus') AND DATE(created_at) = %s", $date ) );
        $balance_spent  = (float) $wpdb->get_var( $wpdb->prepare( "SELECT SUM(ABS(amount)) FROM {$wallet_table} WHERE type = 'spend' AND DATE(created_at) = %s", $date ) );

        $wpdb->replace(
            $table,
            [
                'date'            => $date,
                'orders_count'    => $orders_count,
                'balance_loaded'  => $balance_loaded,
                'balance_spent'   => $balance_spent,
            ],
            [ '%s', '%d', '%f', '%f' ]
        );
    }

    /**
     * Returns analytics range data.
     */
    public static function get_range( int $days = 30 ): array {
        global $wpdb;
        $table = DB::table( 'analytics' );
        $from  = gmdate( 'Y-m-d', strtotime( '-' . ( $days - 1 ) . ' days' ) );

        $rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE date >= %s ORDER BY date ASC", $from ), ARRAY_A );
        $map  = [];
        foreach ( $rows as $row ) {
            $map[ $row['date'] ] = $row;
        }

        $data = [];
        for ( $i = $days - 1; $i >= 0; $i-- ) {
            $date     = gmdate( 'Y-m-d', strtotime( '-' . $i . ' days' ) );
            $existing = $map[ $date ] ?? null;
            $data[]   = [
                'date'           => $date,
                'orders_count'   => $existing ? (int) $existing['orders_count'] : 0,
                'balance_loaded' => $existing ? (float) $existing['balance_loaded'] : 0.0,
                'balance_spent'  => $existing ? (float) $existing['balance_spent'] : 0.0,
            ];
        }

        return $data;
    }
}

<?php
namespace CodexPro\Core;

use wpdb;

/**
 * Points management.
 */
class Points {
    /**
     * Boot hooks.
     */
    public static function init(): void {
    }

    /**
     * Adds points entry for user.
     */
    public static function add_points( int $user_id, int $points, string $reason, ?int $product_id = null, ?int $order_id = null ): int {
        global $wpdb;
        $table = DB::table( 'points' );

        $result = $wpdb->insert(
            $table,
            [
                'user_id'    => $user_id,
                'product_id' => $product_id,
                'order_id'   => $order_id,
                'points'     => $points,
                'reason'     => $reason,
                'created_at' => current_time( 'mysql', true ),
            ],
            [ '%d', '%d', '%d', '%d', '%s', '%s' ]
        );

        if ( $result ) {
            self::update_user_points_meta( $user_id );
        }

        return (int) $wpdb->insert_id;
    }

    /**
     * Get total points for user.
     */
    public static function get_total_points( int $user_id ): int {
        $cached = get_user_meta( $user_id, '_codex_points_total', true );
        if ( '' !== $cached ) {
            return (int) $cached;
        }
        return self::update_user_points_meta( $user_id );
    }

    /**
     * Recalculate and cache points.
     */
    public static function update_user_points_meta( int $user_id ): int {
        global $wpdb;
        $table = DB::table( 'points' );
        $total = (int) $wpdb->get_var( $wpdb->prepare( "SELECT SUM(points) FROM {$table} WHERE user_id = %d", $user_id ) );
        update_user_meta( $user_id, '_codex_points_total', $total );
        return $total;
    }

    /**
     * Returns recent points log.
     */
    public static function get_recent( int $user_id, int $limit = 20 ): array {
        global $wpdb;
        $table = DB::table( 'points' );
        $sql   = $wpdb->prepare( "SELECT * FROM {$table} WHERE user_id = %d ORDER BY created_at DESC LIMIT %d", $user_id, $limit );
        return $wpdb->get_results( $sql, ARRAY_A );
    }
}

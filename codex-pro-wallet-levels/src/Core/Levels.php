<?php
namespace CodexPro\Core;

use wpdb;

/**
 * Level handling.
 */
class Levels {
    /**
     * Init hooks.
     */
    public static function init(): void {
    }

    /**
     * Get all levels ordered by min points.
     */
    public static function get_levels(): array {
        $levels = get_transient( 'codex_pro_levels' );
        if ( false === $levels ) {
            global $wpdb;
            $table  = DB::table( 'levels' );
            $levels = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY min_points ASC", ARRAY_A );
            set_transient( 'codex_pro_levels', $levels, HOUR_IN_SECONDS );
        }
        return $levels ?: [];
    }

    /**
     * Returns current level info for user.
     */
    public static function get_user_level( int $user_id ): array {
        $total_points = Points::get_total_points( $user_id );
        $levels       = self::get_levels();
        $current      = null;
        $next         = null;
        foreach ( $levels as $level ) {
            if ( $total_points >= (int) $level['min_points'] ) {
                $current = $level;
            } elseif ( null === $next ) {
                $next = $level;
            }
        }

        return [
            'current'   => $current,
            'next'      => $next,
            'remaining' => $next ? max( 0, (int) $next['min_points'] - $total_points ) : 0,
            'points'    => $total_points,
        ];
    }
}

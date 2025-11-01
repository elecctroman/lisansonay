<?php
namespace CodexPro\Core;

use wpdb;

/**
 * Bonus rules handler.
 */
class Bonuses {
    /**
     * Init hooks.
     */
    public static function init(): void {
    }

    /**
     * Get active bonus rules.
     */
    public static function get_active_rules(): array {
        global $wpdb;
        $table = DB::table( 'bonus_rules' );
        $sql   = "SELECT * FROM {$table} WHERE active = 1 ORDER BY min_amount ASC";
        return $wpdb->get_results( $sql, ARRAY_A ) ?: [];
    }

    /**
     * Calculates bonus for amount.
     */
    public static function calculate_bonus( float $amount ): float {
        $rules = self::get_active_rules();
        $bonus = 0.0;
        foreach ( $rules as $rule ) {
            if ( $amount >= (float) $rule['min_amount'] ) {
                $value = 'percent' === $rule['bonus_type'] ? $amount * ( (float) $rule['bonus_value'] / 100 ) : (float) $rule['bonus_value'];
                if ( $value > $bonus ) {
                    $bonus = $value;
                }
            }
        }
        return $bonus;
    }
}

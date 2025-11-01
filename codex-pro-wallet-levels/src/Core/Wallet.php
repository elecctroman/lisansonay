<?php
namespace CodexPro\Core;

use CodexPro\Plugin;
use wpdb;
use WP_User;

/**
 * Wallet operations handler.
 */
class Wallet {
    /**
     * Boot hooks.
     */
    public static function init(): void {
        add_action( 'codex_pro_wallet_record', [ __CLASS__, 'record_transaction' ], 10, 6 );
    }

    /**
     * Gets user balance.
     */
    public static function get_balance( int $user_id ): float {
        $balance = get_user_meta( $user_id, '_codex_wallet_balance', true );
        if ( '' === $balance ) {
            $balance = self::recalculate_balance( $user_id );
        }
        return (float) $balance;
    }

    /**
     * Recalculate balance from transactions.
     */
    public static function recalculate_balance( int $user_id ): float {
        global $wpdb;
        $table   = DB::table( 'wallet_tx' );
        $total   = (float) $wpdb->get_var( $wpdb->prepare( "SELECT SUM(amount) FROM {$table} WHERE user_id = %d", $user_id ) );
        update_user_meta( $user_id, '_codex_wallet_balance', $total );
        return $total;
    }

    /**
     * Records a transaction.
     */
    public static function record_transaction( int $user_id, string $type, float $amount, ?int $order_id = null, ?string $note = null, ?float $balance_after = null ): int {
        global $wpdb;
        $table = DB::table( 'wallet_tx' );

        if ( null === $balance_after ) {
            $balance_after = self::get_balance( $user_id ) + $amount;
        }

        $result = $wpdb->insert(
            $table,
            [
                'user_id'       => $user_id,
                'order_id'      => $order_id,
                'type'          => $type,
                'amount'        => $amount,
                'balance_after' => $balance_after,
                'note'          => $note,
                'created_at'    => current_time( 'mysql', true ),
            ],
            [ '%d', '%d', '%s', '%f', '%f', '%s', '%s' ]
        );

        if ( $result ) {
            update_user_meta( $user_id, '_codex_wallet_balance', $balance_after );
        }

        return (int) $wpdb->insert_id;
    }

    /**
     * Adds amount to user wallet (load).
     */
    public static function add_balance( int $user_id, float $amount, string $note = '', ?int $order_id = null, string $type = 'load' ): int {
        return self::record_transaction( $user_id, $type, abs( $amount ), $order_id, $note );
    }

    /**
     * Deducts balance from user wallet.
     */
    public static function deduct_balance( int $user_id, float $amount, string $note = '', ?int $order_id = null ): int {
        return self::record_transaction( $user_id, 'spend', - abs( $amount ), $order_id, $note );
    }

    /**
     * Refund amount to user wallet.
     */
    public static function refund_balance( int $user_id, float $amount, string $note = '', ?int $order_id = null ): int {
        return self::record_transaction( $user_id, 'refund', abs( $amount ), $order_id, $note );
    }

    /**
     * Returns recent transactions.
     */
    public static function get_transactions( int $user_id, int $limit = 20, int $offset = 0, ?string $type = null ): array {
        global $wpdb;
        $table = DB::table( 'wallet_tx' );
        $where = 'WHERE user_id = %d';
        $args  = [ $user_id ];

        if ( $type ) {
            $where .= ' AND type = %s';
            $args[] = $type;
        }

        $sql = $wpdb->prepare(
            "SELECT * FROM {$table} {$where} ORDER BY created_at DESC LIMIT %d OFFSET %d",
            ...array_merge( $args, [ $limit, $offset ] )
        );

        return $wpdb->get_results( $sql, ARRAY_A );
    }
}

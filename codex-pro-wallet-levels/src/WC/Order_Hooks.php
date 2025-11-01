<?php
namespace CodexPro\WC;

use CodexPro\Core\Wallet;
use CodexPro\Core\Points;
use CodexPro\Core\SMS_Netgsm;
use CodexPro\Plugin;

/**
 * Handles WooCommerce order events.
 */
class Order_Hooks {
    /**
     * Init hooks.
     */
    public static function init(): void {
        add_action( 'woocommerce_order_status_completed', [ __CLASS__, 'handle_completed' ], 10, 1 );
        add_action( 'woocommerce_order_status_cancelled', [ __CLASS__, 'handle_cancelled' ], 10, 1 );
        add_action( 'woocommerce_order_refunded', [ __CLASS__, 'handle_refunded' ], 10, 2 );
    }

    /**
     * Order completed.
     */
    public static function handle_completed( $order_id ): void {
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return;
        }

        $user_id = $order->get_user_id();
        if ( $user_id ) {
            $points = 0;
            foreach ( $order->get_items() as $item ) {
                $product_id = $item->get_product_id();
                $multiplier = (float) get_post_meta( $product_id, '_codex_points_multiplier', true );
                if ( $multiplier <= 0 ) {
                    $multiplier = (float) ( get_option( Plugin::OPTION_KEY, [] )['points_per_currency'] ?? 1 );
                }
                $points += (int) round( $item->get_total() * $multiplier );
            }
            if ( $points > 0 ) {
                Points::add_points( $user_id, $points, 'purchase', null, $order_id );
            }
        }

        self::send_sms( 'order_completed', $order );
    }

    /**
     * Order cancelled.
     */
    public static function handle_cancelled( $order_id ): void {
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return;
        }

        $user_id = $order->get_user_id();
        if ( $user_id ) {
            $transactions = Wallet::get_transactions( $user_id, 50, 0, 'spend' );
            foreach ( $transactions as $tx ) {
                if ( (int) $tx['order_id'] === $order_id ) {
                    Wallet::refund_balance( $user_id, abs( (float) $tx['amount'] ), __( 'Order cancelled refund', 'codex-pro' ), $order_id );
                    break;
                }
            }
        }

        self::send_sms( 'order_cancelled', $order );
    }

    /**
     * Order refunded.
     */
    public static function handle_refunded( $order_id, $refund_id ): void {
        $order   = wc_get_order( $order_id );
        $refund  = wc_get_order( $refund_id );
        $user_id = $order ? $order->get_user_id() : 0;
        if ( $order && $refund && $user_id ) {
            Wallet::refund_balance( $user_id, (float) $refund->get_total(), __( 'Order refund', 'codex-pro' ), $order_id );
        }
    }

    /**
     * Sends templated sms.
     */
    protected static function send_sms( string $template_key, \WC_Order $order ): void {
        $settings = get_option( Plugin::OPTION_KEY, [] );
        if ( empty( $settings['netgsm_enabled'] ) ) {
            return;
        }
        $templates = $settings['sms_templates'] ?? [];
        $template  = $templates[ $template_key ] ?? '';
        if ( ! $template ) {
            $template = 'order_completed' === $template_key ? __( 'Order #{order_id} completed. Total: {total}', 'codex-pro' ) : __( 'Order #{order_id} cancelled.', 'codex-pro' );
        }

        $phone = $order->get_billing_phone();
        if ( ! $phone ) {
            return;
        }

        $message = SMS_Netgsm::format_template( $template, [
            'order_id' => $order->get_id(),
            'total'    => $order->get_formatted_order_total(),
            'currency' => $order->get_currency(),
            'first_name' => $order->get_billing_first_name(),
            'last_name'  => $order->get_billing_last_name(),
        ] );
        SMS_Netgsm::send_sms( $phone, $message );
    }
}

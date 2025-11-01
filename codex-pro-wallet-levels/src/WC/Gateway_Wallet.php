<?php
namespace CodexPro\WC;

use WC_Payment_Gateway;
use CodexPro\Core\Wallet;

/**
 * Wallet payment gateway allowing full or partial payment.
 */
class Gateway_Wallet extends WC_Payment_Gateway {
    /**
     * Constructor.
     */
    public function __construct() {
        $this->id                 = 'codex_wallet';
        $this->method_title       = __( 'Codex Wallet', 'codex-pro' );
        $this->method_description = __( 'Pay using Codex Pro wallet balance.', 'codex-pro' );
        $this->has_fields         = false;
        $this->supports           = [ 'products', 'partial_payments', 'refunds' ];

        $this->init_form_fields();
        $this->init_settings();

        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, [ $this, 'process_admin_options' ] );
    }

    /**
     * Init settings fields.
     */
    public function init_form_fields() {
        $this->form_fields = [
            'enabled' => [
                'title'   => __( 'Enable/Disable', 'codex-pro' ),
                'type'    => 'checkbox',
                'label'   => __( 'Enable Codex Wallet payments', 'codex-pro' ),
                'default' => 'yes',
            ],
        ];
    }

    /**
     * Processes payment.
     */
    public function process_payment( $order_id ) {
        $order    = wc_get_order( $order_id );
        $user_id  = $order->get_user_id();
        $balance  = Wallet::get_balance( $user_id );
        $total    = (float) $order->get_total();

        if ( $balance <= 0 ) {
            wc_add_notice( __( 'Wallet balance is not enough.', 'codex-pro' ), 'error' );
            return false;
        }

        $amount_to_charge = min( $balance, $total );

        Wallet::deduct_balance( $user_id, $amount_to_charge, __( 'Order payment', 'codex-pro' ), $order_id );

        if ( $amount_to_charge >= $total ) {
            $order->payment_complete();
            $order->add_order_note( __( 'Paid fully by wallet.', 'codex-pro' ) );
            return [
                'result'   => 'success',
                'redirect' => $this->get_return_url( $order ),
            ];
        }

        $remaining = $total - $amount_to_charge;
        $order->add_order_note( sprintf( __( 'Partially paid %.2f by wallet, %.2f remaining.', 'codex-pro' ), $amount_to_charge, $remaining ) );
        wc_add_notice( sprintf( __( 'Wallet covered part of the order. Please pay remaining %s using another method.', 'codex-pro' ), wc_price( $remaining ) ), 'notice' );

        return [
            'result'   => 'success',
            'redirect' => wc_get_checkout_url(),
        ];
    }

    /**
     * Process refund.
     */
    public function process_refund( $order_id, $amount = null, $reason = '' ) {
        $order   = wc_get_order( $order_id );
        $user_id = $order->get_user_id();
        if ( ! $user_id ) {
            return false;
        }
        $amount = $amount ?? $order->get_total();
        Wallet::refund_balance( $user_id, (float) $amount, $reason ?: __( 'Order refund', 'codex-pro' ), $order_id );
        $order->add_order_note( __( 'Refunded to wallet.', 'codex-pro' ) );
        return true;
    }
}

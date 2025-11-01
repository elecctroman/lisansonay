<?php
namespace CodexPro\WC;

/**
 * Adds product meta boxes for points multiplier.
 */
class Product_Meta {
    /**
     * Init hooks.
     */
    public static function init(): void {
        add_action( 'woocommerce_product_options_general_product_data', [ __CLASS__, 'add_meta_field' ] );
        add_action( 'woocommerce_admin_process_product_object', [ __CLASS__, 'save_meta_field' ] );
    }

    /**
     * Adds field to product data panel.
     */
    public static function add_meta_field(): void {
        woocommerce_wp_text_input( [
            'id'          => '_codex_points_multiplier',
            'label'       => __( 'Codex Points Multiplier', 'codex-pro' ),
            'desc_tip'    => true,
            'description' => __( 'Override global points per currency multiplier.', 'codex-pro' ),
            'type'        => 'number',
            'custom_attributes' => [
                'step' => '0.1',
                'min'  => '0',
            ],
        ] );
    }

    /**
     * Saves multiplier.
     */
    public static function save_meta_field( $product ): void {
        if ( isset( $_POST['_codex_points_multiplier'] ) ) {
            $value = wc_format_decimal( wp_unslash( $_POST['_codex_points_multiplier'] ) );
            $product->update_meta_data( '_codex_points_multiplier', $value );
        }
    }
}

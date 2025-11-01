<?php
namespace CodexPro\Admin;

use CodexPro\Plugin;
use CodexPro\Core\Analytics;
use CodexPro\Core\Wallet;
use CodexPro\Core\Points;
use CodexPro\Core\SMS_Netgsm;

/**
 * Admin menu handler.
 */
class Menu {
    /**
     * Init hooks.
     */
    public static function init(): void {
        add_action( 'admin_menu', [ __CLASS__, 'register_menu' ] );
    }

    /**
     * Register admin menu pages.
     */
    public static function register_menu(): void {
        if ( ! current_user_can( 'manage_codex_pro' ) ) {
            return;
        }

        add_menu_page(
            __( 'Codex Pro', 'codex-pro' ),
            __( 'Codex Pro', 'codex-pro' ),
            'manage_codex_pro',
            'codex-pro',
            [ __CLASS__, 'render_dashboard' ],
            'dashicons-chart-area'
        );

        add_submenu_page( 'codex-pro', __( 'Wallets', 'codex-pro' ), __( 'Wallets', 'codex-pro' ), 'edit_codex_wallet', 'codex-pro-wallets', [ __CLASS__, 'render_wallets' ] );
        add_submenu_page( 'codex-pro', __( 'Points', 'codex-pro' ), __( 'Points', 'codex-pro' ), 'edit_codex_wallet', 'codex-pro-points', [ __CLASS__, 'render_points' ] );
        add_submenu_page( 'codex-pro', __( 'Bonuses', 'codex-pro' ), __( 'Bonuses', 'codex-pro' ), 'manage_codex_pro', 'codex-pro-bonuses', [ __CLASS__, 'render_bonuses' ] );
        add_submenu_page( 'codex-pro', __( 'Settings', 'codex-pro' ), __( 'Settings', 'codex-pro' ), 'manage_codex_pro', 'codex-pro-settings', [ __CLASS__, 'render_settings' ] );
        add_submenu_page( 'codex-pro', __( 'Logs', 'codex-pro' ), __( 'Logs', 'codex-pro' ), 'manage_codex_pro', 'codex-pro-logs', [ __CLASS__, 'render_logs' ] );
    }

    /**
     * Dashboard page.
     */
    public static function render_dashboard(): void {
        if ( ! current_user_can( 'manage_codex_pro' ) ) {
            wp_die( esc_html__( 'You do not have permission.', 'codex-pro' ) );
        }

        $analytics = Analytics::get_range( 7 );
        $settings  = get_option( Plugin::OPTION_KEY, [] );
        ?>
        <div class="wrap codex-pro-admin">
            <h1><?php esc_html_e( 'Codex Pro Dashboard', 'codex-pro' ); ?></h1>
            <p><?php esc_html_e( 'Quick snapshot of wallet and points usage.', 'codex-pro' ); ?></p>
            <table class="widefat">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Date', 'codex-pro' ); ?></th>
                        <th><?php esc_html_e( 'Orders', 'codex-pro' ); ?></th>
                        <th><?php esc_html_e( 'Loaded', 'codex-pro' ); ?></th>
                        <th><?php esc_html_e( 'Spent', 'codex-pro' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ( $analytics as $day ) : ?>
                    <tr>
                        <td><?php echo esc_html( $day['date'] ); ?></td>
                        <td><?php echo esc_html( $day['orders_count'] ); ?></td>
                        <td><?php echo esc_html( wc_price( $day['balance_loaded'] ?? 0 ) ); ?></td>
                        <td><?php echo esc_html( wc_price( $day['balance_spent'] ?? 0 ) ); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Render wallets page stub.
     */
    public static function render_wallets(): void {
        if ( ! current_user_can( 'edit_codex_wallet' ) ) {
            wp_die( esc_html__( 'You do not have permission.', 'codex-pro' ) );
        }

        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Wallet Management', 'codex-pro' ); ?></h1>
            <p><?php esc_html_e( 'Use REST API or CLI for advanced operations. UI coming soon.', 'codex-pro' ); ?></p>
        </div>
        <?php
    }

    /**
     * Render points page stub.
     */
    public static function render_points(): void {
        if ( ! current_user_can( 'edit_codex_wallet' ) ) {
            wp_die( esc_html__( 'You do not have permission.', 'codex-pro' ) );
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Points Management', 'codex-pro' ); ?></h1>
            <p><?php esc_html_e( 'Points management UI placeholder.', 'codex-pro' ); ?></p>
        </div>
        <?php
    }

    /**
     * Render bonuses page stub.
     */
    public static function render_bonuses(): void {
        if ( ! current_user_can( 'manage_codex_pro' ) ) {
            wp_die( esc_html__( 'You do not have permission.', 'codex-pro' ) );
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Bonus Rules', 'codex-pro' ); ?></h1>
            <p><?php esc_html_e( 'Manage bonus rules via database or REST API.', 'codex-pro' ); ?></p>
        </div>
        <?php
    }

    /**
     * Render settings page stub.
     */
    public static function render_settings(): void {
        if ( ! current_user_can( 'manage_codex_pro' ) ) {
            wp_die( esc_html__( 'You do not have permission.', 'codex-pro' ) );
        }

        $settings = get_option( Plugin::OPTION_KEY, [] );
        if ( isset( $_POST['codex_pro_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['codex_pro_nonce'] ) ), 'codex_pro_settings' ) ) {
            $settings['netgsm_username'] = sanitize_text_field( wp_unslash( $_POST['netgsm_username'] ?? '' ) );
            $settings['netgsm_password'] = sanitize_text_field( wp_unslash( $_POST['netgsm_password'] ?? '' ) );
            $settings['netgsm_header']   = sanitize_text_field( wp_unslash( $_POST['netgsm_header'] ?? '' ) );
            $settings['netgsm_enabled']  = isset( $_POST['netgsm_enabled'] ) ? 1 : 0;
            update_option( Plugin::OPTION_KEY, $settings );
            echo '<div class="updated"><p>' . esc_html__( 'Settings saved.', 'codex-pro' ) . '</p></div>';
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Codex Pro Settings', 'codex-pro' ); ?></h1>
            <form method="post">
                <?php wp_nonce_field( 'codex_pro_settings', 'codex_pro_nonce' ); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Enable Netgsm', 'codex-pro' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="netgsm_enabled" value="1" <?php checked( ! empty( $settings['netgsm_enabled'] ) ); ?> />
                                <?php esc_html_e( 'Enable SMS notifications via Netgsm.', 'codex-pro' ); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Netgsm Username', 'codex-pro' ); ?></th>
                        <td><input type="text" name="netgsm_username" value="<?php echo esc_attr( $settings['netgsm_username'] ?? '' ); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Netgsm Password', 'codex-pro' ); ?></th>
                        <td><input type="password" name="netgsm_password" value="<?php echo esc_attr( $settings['netgsm_password'] ?? '' ); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Netgsm Header', 'codex-pro' ); ?></th>
                        <td><input type="text" name="netgsm_header" value="<?php echo esc_attr( $settings['netgsm_header'] ?? '' ); ?>" class="regular-text" /></td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Render logs stub.
     */
    public static function render_logs(): void {
        if ( ! current_user_can( 'manage_codex_pro' ) ) {
            wp_die( esc_html__( 'You do not have permission.', 'codex-pro' ) );
        }

        $log = get_transient( 'codex_pro_sms_last' );
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Codex Pro Logs', 'codex-pro' ); ?></h1>
            <?php if ( $log ) : ?>
                <p><?php printf( esc_html__( 'Last SMS response: %1$s (%2$s)', 'codex-pro' ), esc_html( $log['code'] ), esc_html( $log['body'] ) ); ?></p>
            <?php else : ?>
                <p><?php esc_html_e( 'No log entries yet.', 'codex-pro' ); ?></p>
            <?php endif; ?>
        </div>
        <?php
    }
}

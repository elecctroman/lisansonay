<?php
namespace CodexPro\Frontend;

use CodexPro\Core\Wallet;
use CodexPro\Core\Points;
use CodexPro\Core\Levels;
use CodexPro\Core\Analytics;

/**
 * Shortcodes for wallet display.
 */
class Shortcodes {
    /**
     * Init hooks.
     */
    public static function init(): void {
        add_shortcode( 'codex_wallet', [ __CLASS__, 'render_wallet' ] );
        add_shortcode( 'codex_wallet_balance', [ __CLASS__, 'render_balance' ] );
        add_shortcode( 'codex_wallet_transactions', [ __CLASS__, 'render_transactions' ] );
        add_shortcode( 'codex_levels', [ __CLASS__, 'render_levels' ] );
    }

    /**
     * Render wallet summary block (for block as well).
     */
    public static function render_wallet_summary_block(): string {
        return self::render_wallet( [], '' );
    }

    /**
     * Renders wallet shortcode.
     */
    public static function render_wallet( $atts, $content ): string {
        if ( ! is_user_logged_in() ) {
            return esc_html__( 'You need to login to view wallet.', 'codex-pro' );
        }
        ob_start();
        Account_Endpoint::render_wallet_page();
        return ob_get_clean();
    }

    /**
     * Renders balance only.
     */
    public static function render_balance(): string {
        if ( ! is_user_logged_in() ) {
            return '';
        }
        $balance = Wallet::get_balance( get_current_user_id() );
        return '<span class="codex-wallet-balance">' . esc_html( wc_price( $balance ) ) . '</span>';
    }

    /**
     * Renders transactions table.
     */
    public static function render_transactions(): string {
        if ( ! is_user_logged_in() ) {
            return '';
        }
        $transactions = Wallet::get_transactions( get_current_user_id(), 20 );
        ob_start();
        ?>
        <table class="codex-wallet-table">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Type', 'codex-pro' ); ?></th>
                    <th><?php esc_html_e( 'Amount', 'codex-pro' ); ?></th>
                    <th><?php esc_html_e( 'Date', 'codex-pro' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $transactions as $row ) : ?>
                    <tr>
                        <td><?php echo esc_html( $row['type'] ); ?></td>
                        <td><?php echo esc_html( wc_price( $row['amount'] ) ); ?></td>
                        <td><?php echo esc_html( get_date_from_gmt( $row['created_at'], get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) ); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
        return ob_get_clean();
    }

    /**
     * Render levels overview.
     */
    public static function render_levels(): string {
        if ( ! is_user_logged_in() ) {
            return '';
        }
        $info   = Levels::get_user_level( get_current_user_id() );
        $levels = Levels::get_levels();
        ob_start();
        ?>
        <div class="codex-levels">
            <p><?php esc_html_e( 'Your Points:', 'codex-pro' ); ?> <?php echo esc_html( $info['points'] ); ?></p>
            <?php if ( $info['current'] ) : ?>
                <p><?php printf( esc_html__( 'Current Level: %s', 'codex-pro' ), esc_html( $info['current']['name'] ) ); ?></p>
            <?php endif; ?>
            <?php if ( $info['next'] ) : ?>
                <p><?php printf( esc_html__( 'Points to next level: %d', 'codex-pro' ), esc_html( $info['remaining'] ) ); ?></p>
            <?php else : ?>
                <p><?php esc_html_e( 'Highest level achieved!', 'codex-pro' ); ?></p>
            <?php endif; ?>
            <ul>
                <?php foreach ( $levels as $level ) : ?>
                    <li><?php echo esc_html( $level['name'] . ' - ' . $level['min_points'] ); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
        return ob_get_clean();
    }
}

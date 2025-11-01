<?php
/**
 * Wallet page template.
 *
 * @var float $balance
 * @var int   $points
 * @var array $level
 * @var array $tx
 * @var array $point_log
 */
?>
<div class="codex-wallet">
    <div class="codex-wallet__summary" role="region" aria-label="<?php esc_attr_e( 'Wallet balance', 'codex-pro' ); ?>">
        <h2><?php esc_html_e( 'Wallet Balance', 'codex-pro' ); ?></h2>
        <div class="codex-wallet__balance"><?php echo esc_html( wc_price( $balance ) ); ?></div>
    </div>
    <div class="codex-wallet__levels" role="region" aria-label="<?php esc_attr_e( 'Gamification', 'codex-pro' ); ?>">
        <h2><?php esc_html_e( 'Your Level', 'codex-pro' ); ?></h2>
        <?php if ( $level['current'] ) : ?>
            <p><?php printf( esc_html__( 'Current Level: %s', 'codex-pro' ), esc_html( $level['current']['name'] ) ); ?></p>
        <?php endif; ?>
        <?php if ( $level['next'] ) : ?>
            <p><?php printf( esc_html__( 'Points to next level: %d', 'codex-pro' ), (int) $level['remaining'] ); ?></p>
        <?php else : ?>
            <p><?php esc_html_e( 'You have achieved the highest level.', 'codex-pro' ); ?></p>
        <?php endif; ?>
        <p><?php printf( esc_html__( 'Total points: %d', 'codex-pro' ), (int) $points ); ?></p>
    </div>
    <div class="codex-wallet__analytics" role="region" aria-label="<?php esc_attr_e( 'Analytics charts', 'codex-pro' ); ?>">
        <h2><?php esc_html_e( '30 Day Analytics', 'codex-pro' ); ?></h2>
        <canvas id="codex-wallet-orders" aria-label="<?php esc_attr_e( 'Orders chart', 'codex-pro' ); ?>" role="img"></canvas>
        <canvas id="codex-wallet-loaded" aria-label="<?php esc_attr_e( 'Loaded chart', 'codex-pro' ); ?>" role="img"></canvas>
        <canvas id="codex-wallet-spent" aria-label="<?php esc_attr_e( 'Spent chart', 'codex-pro' ); ?>" role="img"></canvas>
    </div>
    <div class="codex-wallet__transactions" role="region" aria-label="<?php esc_attr_e( 'Recent transactions', 'codex-pro' ); ?>">
        <h2><?php esc_html_e( 'Recent Transactions', 'codex-pro' ); ?></h2>
        <table>
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Type', 'codex-pro' ); ?></th>
                    <th><?php esc_html_e( 'Amount', 'codex-pro' ); ?></th>
                    <th><?php esc_html_e( 'Balance After', 'codex-pro' ); ?></th>
                    <th><?php esc_html_e( 'Date', 'codex-pro' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $tx as $row ) : ?>
                    <tr>
                        <td><?php echo esc_html( ucfirst( $row['type'] ) ); ?></td>
                        <td><?php echo esc_html( wc_price( $row['amount'] ) ); ?></td>
                        <td><?php echo esc_html( wc_price( $row['balance_after'] ) ); ?></td>
                        <td><?php echo esc_html( get_date_from_gmt( $row['created_at'], get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) ); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="codex-wallet__points" role="region" aria-label="<?php esc_attr_e( 'Points history', 'codex-pro' ); ?>">
        <h2><?php esc_html_e( 'Recent Points', 'codex-pro' ); ?></h2>
        <table>
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Points', 'codex-pro' ); ?></th>
                    <th><?php esc_html_e( 'Reason', 'codex-pro' ); ?></th>
                    <th><?php esc_html_e( 'Date', 'codex-pro' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $point_log as $row ) : ?>
                    <tr>
                        <td><?php echo esc_html( $row['points'] ); ?></td>
                        <td><?php echo esc_html( $row['reason'] ); ?></td>
                        <td><?php echo esc_html( get_date_from_gmt( $row['created_at'], get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) ); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

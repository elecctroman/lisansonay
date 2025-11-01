<?php
use CodexPro\Core\Wallet_Adapter;

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

$adapter = Wallet_Adapter::instance();
$wallet_available = $adapter->is_available();
$user_id = isset( $_GET['user_id'] ) ? absint( $_GET['user_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$transactions = array();
$balance = 0.0;

if ( $wallet_available && $user_id ) {
$transactions = $adapter->get_recent_tx( $user_id, 20 );
$balance      = $adapter->get_balance( $user_id );
}
?>
<div class="codex-pro-wrap">
<h1 class="codex-pro-title"><?php esc_html_e( 'Wallet Management', 'codex-pro' ); ?></h1>
<?php if ( ! $wallet_available ) : ?>
<div class="notice notice-error"><p><?php esc_html_e( 'WooWallet is required to manage balances. Please install and activate WooWallet.', 'codex-pro' ); ?></p></div>
<?php endif; ?>
<form method="get">
<input type="hidden" name="page" value="codex-pro-wallets" />
<label for="codex-pro-user-id" class="screen-reader-text"><?php esc_html_e( 'User ID', 'codex-pro' ); ?></label>
<input type="number" id="codex-pro-user-id" name="user_id" value="<?php echo esc_attr( $user_id ); ?>" placeholder="<?php esc_attr_e( 'User ID', 'codex-pro' ); ?>" />
<button class="button button-primary" type="submit"><?php esc_html_e( 'Load Wallet', 'codex-pro' ); ?></button>
</form>
<?php if ( $user_id ) : ?>
<div class="codex-pro-card codex-pro-wallet-summary">
<h2><?php esc_html_e( 'Wallet Summary', 'codex-pro' ); ?></h2>
<p><?php printf( esc_html__( 'Current balance: %s', 'codex-pro' ), wp_kses_post( function_exists( 'wc_price' ) ? wc_price( $balance ) : number_format_i18n( $balance, 2 ) ) ); ?></p>
</div>
<table class="widefat striped">
<thead>
<tr>
<th><?php esc_html_e( 'Type', 'codex-pro' ); ?></th>
<th><?php esc_html_e( 'Amount', 'codex-pro' ); ?></th>
<th><?php esc_html_e( 'Details', 'codex-pro' ); ?></th>
<th><?php esc_html_e( 'Date', 'codex-pro' ); ?></th>
</tr>
</thead>
<tbody>
<?php if ( empty( $transactions ) ) : ?>
<tr><td colspan="4"><?php esc_html_e( 'No transactions found for the selected user.', 'codex-pro' ); ?></td></tr>
<?php else : ?>
<?php foreach ( $transactions as $transaction ) : ?>
<tr>
<td><?php echo esc_html( $transaction['type'] ?? '' ); ?></td>
<td><?php echo wp_kses_post( function_exists( 'wc_price' ) ? wc_price( (float) ( $transaction['amount'] ?? 0 ) ) : number_format_i18n( (float) ( $transaction['amount'] ?? 0 ), 2 ) ); ?></td>
<td><?php echo esc_html( $transaction['details'] ?? '' ); ?></td>
<td><?php echo esc_html( $transaction['created'] ?? '' ); ?></td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</tbody>
</table>
<?php endif; ?>
</div>

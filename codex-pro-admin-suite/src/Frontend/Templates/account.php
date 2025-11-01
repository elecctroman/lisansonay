<?php
if ( ! defined( 'ABSPATH' ) ) {
exit;
}

$labels = array_keys( $stats );
$credits = wp_list_pluck( $stats, 'credit' );
$debits  = wp_list_pluck( $stats, 'debit' );
?>
<div class="codex-account">
<section class="codex-account__hero">
<div class="codex-card">
<h2><?php esc_html_e( 'Wallet Balance', 'codex-pro' ); ?></h2>
<p class="codex-card__value"><?php echo wp_kses_post( function_exists( 'wc_price' ) ? wc_price( $balance ) : number_format_i18n( $balance, 2 ) ); ?></p>
</div>
<div class="codex-card">
<h2><?php esc_html_e( '30 Day Activity', 'codex-pro' ); ?></h2>
<canvas id="codex-account-chart" aria-label="<?php esc_attr_e( 'Wallet analytics', 'codex-pro' ); ?>" role="img"></canvas>
</div>
</section>
<section class="codex-account__tables">
<div class="codex-card">
<h3><?php esc_html_e( 'Recent Transactions', 'codex-pro' ); ?></h3>
<table>
<thead>
<tr>
<th><?php esc_html_e( 'Type', 'codex-pro' ); ?></th>
<th><?php esc_html_e( 'Amount', 'codex-pro' ); ?></th>
<th><?php esc_html_e( 'Note', 'codex-pro' ); ?></th>
<th><?php esc_html_e( 'Date', 'codex-pro' ); ?></th>
</tr>
</thead>
<tbody>
<?php if ( empty( $transactions ) ) : ?>
<tr><td colspan="4"><?php esc_html_e( 'No wallet transactions found.', 'codex-pro' ); ?></td></tr>
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
</div>
</section>
</div>
<script>
window.codexProAccount = {
labels: <?php echo wp_json_encode( array_values( $labels ) ); ?>,
credit: <?php echo wp_json_encode( array_values( $credits ) ); ?>,
debit: <?php echo wp_json_encode( array_values( $debits ) ); ?>
};
</script>

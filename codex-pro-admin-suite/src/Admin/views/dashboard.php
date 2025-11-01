<?php
use CodexPro\Core\Wallet_Adapter;

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

$adapter = Wallet_Adapter::instance();
$wallet_available = $adapter->is_available();
$chart_labels = array_keys( $stats );
$chart_credit = wp_list_pluck( $stats, 'credit' );
$chart_debit  = wp_list_pluck( $stats, 'debit' );
?>
<div class="codex-pro-wrap">
<h1 class="codex-pro-title"><?php esc_html_e( 'Codex Pro Dashboard', 'codex-pro' ); ?></h1>
<?php if ( ! $wallet_available ) : ?>
<div class="notice notice-warning"><p><?php esc_html_e( 'WooWallet was not detected. Wallet specific features are disabled.', 'codex-pro' ); ?></p></div>
<?php endif; ?>
<div class="codex-pro-grid">
<div class="codex-pro-card">
<h2><?php esc_html_e( 'Wallet Overview', 'codex-pro' ); ?></h2>
<p><?php esc_html_e( 'Review last 30 days of wallet activity.', 'codex-pro' ); ?></p>
<canvas id="codex-pro-dashboard-chart" aria-label="<?php esc_attr_e( 'Wallet statistics chart', 'codex-pro' ); ?>" role="img"></canvas>
</div>
</div>
</div>
<script>
window.codexProDashboard = {
labels: <?php echo wp_json_encode( array_values( $chart_labels ) ); ?>,
credit: <?php echo wp_json_encode( array_values( $chart_credit ) ); ?>,
debit: <?php echo wp_json_encode( array_values( $chart_debit ) ); ?>
};
</script>

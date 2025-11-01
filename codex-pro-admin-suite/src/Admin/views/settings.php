<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$options = get_option( 'codex_pro_settings', array() );
$wallet_status = \CodexPro\Core\Wallet_Adapter::instance()->is_available();

if ( isset( $_POST['codex_pro_settings_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['codex_pro_settings_nonce'] ) ), 'codex_pro_save_settings' ) ) {
	if ( current_user_can( 'manage_codex_pro' ) ) {
		$currency         = sanitize_text_field( wp_unslash( $_POST['currency'] ?? '' ) );
		$account_endpoint = sanitize_title( wp_unslash( $_POST['account_endpoint'] ?? '' ) );
		$account_label   = sanitize_text_field( wp_unslash( $_POST['account_label'] ?? '' ) );
		$options          = array(
			'currency'         => $currency,
			'account_endpoint' => $account_endpoint,
			'account_label'   => $account_label,
		);
		update_option( 'codex_pro_settings', $options );
		?><div class="notice notice-success"><p><?php esc_html_e( 'Settings saved.', 'codex-pro' ); ?></p></div><?php
	}
}
?>
<div class="codex-pro-wrap">
	<h1 class="codex-pro-title"><?php esc_html_e( 'Codex Pro Settings', 'codex-pro' ); ?></h1>
	<p><?php esc_html_e( 'Configure account endpoint labels and currency formatting.', 'codex-pro' ); ?></p>
	<p>
		<strong><?php esc_html_e( 'WooWallet status:', 'codex-pro' ); ?></strong>
		<?php echo $wallet_status ? '<span class="status-ok">' . esc_html__( 'Detected', 'codex-pro' ) . '</span>' : '<span class="status-error">' . esc_html__( 'Not found', 'codex-pro' ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</p>
	<form method="post">
		<?php wp_nonce_field( 'codex_pro_save_settings', 'codex_pro_settings_nonce' ); ?>
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Currency', 'codex-pro' ); ?></th>
				<td>
					<input type="text" name="currency" value="<?php echo esc_attr( $options['currency'] ?? get_woocommerce_currency() ); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'My Account endpoint', 'codex-pro' ); ?></th>
				<td>
					<input type="text" name="account_endpoint" value="<?php echo esc_attr( $options['account_endpoint'] ?? 'codex-wallet' ); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Menu label', 'codex-pro' ); ?></th>
				<td>
					<input type="text" name="account_label" value="<?php echo esc_attr( $options['account_label'] ?? __( 'Codex Wallet', 'codex-pro' ) ); ?>" />
				</td>
			</tr>
		</table>
		<p><button type="submit" class="button button-primary"><?php esc_html_e( 'Save Settings', 'codex-pro' ); ?></button></p>
	</form>
</div>

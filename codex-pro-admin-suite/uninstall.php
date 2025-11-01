<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
exit;
}

delete_option( 'codex_pro_settings' );
delete_option( 'codex_pro_db_version' );

<?php
namespace CodexPro\Core;

use CodexPro\Plugin;
use WP_Error;

/**
 * Netgsm SMS integration.
 */
class SMS_Netgsm {
    /**
     * Init hooks.
     */
    public static function init(): void {
    }

    /**
     * Sends SMS via Netgsm API.
     */
    public static function send_sms( string $phone, string $message ) {
        $settings = get_option( Plugin::OPTION_KEY, [] );
        if ( empty( $settings['netgsm_enabled'] ) ) {
            return new WP_Error( 'netgsm_disabled', __( 'Netgsm integration disabled.', 'codex-pro' ) );
        }

        $username = $settings['netgsm_username'] ?? '';
        $password = $settings['netgsm_password'] ?? '';
        $header   = $settings['netgsm_header'] ?? '';

        if ( ! $username || ! $password || ! $header ) {
            return new WP_Error( 'netgsm_credentials', __( 'Netgsm credentials missing.', 'codex-pro' ) );
        }

        $endpoint = 'https://api.netgsm.com.tr/sms/send/';
        $body     = [
            'usercode' => $username,
            'password' => $password,
            'gsmno'    => $phone,
            'message'  => $message,
            'msgheader'=> $header,
        ];

        $response = wp_remote_post( $endpoint, [
            'timeout' => 20,
            'body'    => $body,
        ] );

        if ( is_wp_error( $response ) ) {
            Logger::log( 'sms', 'Netgsm error: ' . $response->get_error_message() );
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = wp_remote_retrieve_body( $response );

        Logger::log( 'sms', 'Netgsm response (' . $code . '): ' . $body );

        if ( 200 !== $code ) {
            return new WP_Error( 'netgsm_http', __( 'Unexpected Netgsm response.', 'codex-pro' ), $body );
        }

        set_transient( 'codex_pro_sms_last', [ 'code' => $code, 'body' => $body, 'time' => time() ], HOUR_IN_SECONDS );

        return true;
    }

    /**
     * Formats template with placeholders.
     */
    public static function format_template( string $template, array $data ): string {
        foreach ( $data as $key => $value ) {
            $template = str_replace( '{' . $key . '}', (string) $value, $template );
        }
        return $template;
    }
}

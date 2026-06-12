<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class LeadPing_WhatsApp {

    private string $provider;
    private string $api_key;
    private string $from_number;

    public function __construct() {
        $this->provider    = get_option( 'leadping_provider', '360dialog' );
        $this->api_key     = get_option( 'leadping_api_key', '' );
        $this->from_number = get_option( 'leadping_from_number', '' );
    }

    /**
     * Send a WhatsApp message.
     *
     * @param string $to      Recipient phone in E.164 format e.g. +2348012345678
     * @param string $message Plain text message body
     * @return bool
     */
    public function send( string $to, string $message ): bool {
        if ( empty( $this->api_key ) || empty( $this->from_number ) ) {
            error_log( 'LeadPing: API key or from number not configured.' );
            return false;
        }

        $to = $this->sanitize_phone( $to );
        if ( ! $to ) {
            error_log( 'LeadPing: Invalid recipient phone number.' );
            return false;
        }

        return match( $this->provider ) {
            '360dialog' => $this->send_360dialog( $to, $message ),
            'twilio'    => $this->send_twilio( $to, $message ),
            default     => false,
        };
    }

    // ------------------------------------------------------------------ //
    //  360dialog
    // ------------------------------------------------------------------ //

    private function send_360dialog( string $to, string $message ): bool {
        $endpoint = 'https://waba.360dialog.io/v1/messages';

        $body = wp_json_encode([
            'to'   => $to,
            'type' => 'text',
            'text' => [ 'body' => $message ],
        ]);

        $response = wp_remote_post( $endpoint, [
            'headers' => [
                'D360-API-KEY' => $this->api_key,
                'Content-Type' => 'application/json',
            ],
            'body'    => $body,
            'timeout' => 15,
        ]);

        return $this->check_response( $response, '360dialog' );
    }

    // ------------------------------------------------------------------ //
    //  Twilio
    // ------------------------------------------------------------------ //

    private function send_twilio( string $to, string $message ): bool {
        $account_sid = get_option( 'leadping_twilio_sid', '' );
        $auth_token  = get_option( 'leadping_api_key', '' );   // reuse api_key field
        $endpoint    = "https://api.twilio.com/2010-04-01/Accounts/{$account_sid}/Messages.json";

        $response = wp_remote_post( $endpoint, [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode( "{$account_sid}:{$auth_token}" ),
            ],
            'body' => [
                'From' => 'whatsapp:' . $this->from_number,
                'To'   => 'whatsapp:' . $to,
                'Body' => $message,
            ],
            'timeout' => 15,
        ]);

        return $this->check_response( $response, 'Twilio' );
    }

    // ------------------------------------------------------------------ //
    //  Helpers
    // ------------------------------------------------------------------ //

    private function check_response( $response, string $provider ): bool {
        if ( is_wp_error( $response ) ) {
            error_log( "LeadPing [{$provider}] WP_Error: " . $response->get_error_message() );
            return false;
        }

        $code = wp_remote_retrieve_response_code( $response );
        if ( $code < 200 || $code >= 300 ) {
            error_log( "LeadPing [{$provider}] HTTP {$code}: " . wp_remote_retrieve_body( $response ) );
            return false;
        }

        return true;
    }

    private function sanitize_phone( string $phone ): string {
        // Strip everything except + and digits
        $cleaned = preg_replace( '/[^\d+]/', '', $phone );
        // Must start with + and have 7–15 digits
        if ( preg_match( '/^\+\d{7,15}$/', $cleaned ) ) {
            return $cleaned;
        }
        return '';
    }
}

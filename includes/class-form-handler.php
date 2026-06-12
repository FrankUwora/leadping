<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class LeadPing_Form_Handler {

    private LeadPing_WhatsApp $wa;

    public function __construct() {
        $this->wa = new LeadPing_WhatsApp();
        $this->register_hooks();
    }

    private function register_hooks(): void {

        // Contact Form 7
        add_action( 'wpcf7_mail_sent', [ $this, 'handle_cf7' ] );

        // WPForms
        add_action( 'wpforms_process_complete', [ $this, 'handle_wpforms' ], 10, 4 );

        // Gravity Forms
        add_action( 'gform_after_submission', [ $this, 'handle_gravity' ], 10, 2 );

        // Elementor Forms
        add_action( 'elementor_pro/forms/new_record', [ $this, 'handle_elementor' ], 10, 2 );

        // Generic fallback - any form POSTing to wp-admin/admin-post.php
        add_action( 'init', [ $this, 'handle_generic_post' ] );
    }

    // ------------------------------------------------------------------ //
    //  Contact Form 7
    // ------------------------------------------------------------------ //

    public function handle_cf7( $contact_form ): void {
        $submission = WPCF7_Submission::get_instance();
        if ( ! $submission ) return;

        $posted = $submission->get_posted_data();

        $lead = [
            'name'        => $this->extract( $posted, [ 'your-name', 'name', 'full-name' ] ),
            'email'       => $this->extract( $posted, [ 'your-email', 'email' ] ),
            'phone'       => $this->extract( $posted, [ 'your-phone', 'phone', 'tel' ] ),
            'message'     => $this->extract( $posted, [ 'your-message', 'message' ] ),
            'source_form' => 'Contact Form 7 - ' . $contact_form->title(),
            'source_page' => $submission->get_meta( 'url' ),
        ];

        $this->process_lead( $lead );
    }

    // ------------------------------------------------------------------ //
    //  WPForms
    // ------------------------------------------------------------------ //

    public function handle_wpforms( array $fields, array $entry, array $form_data, int $entry_id ): void {
        $lead = [
            'name'        => $this->wpforms_field( $fields, [ 'name', 'full_name' ] ),
            'email'       => $this->wpforms_field( $fields, [ 'email' ] ),
            'phone'       => $this->wpforms_field( $fields, [ 'phone' ] ),
            'message'     => $this->wpforms_field( $fields, [ 'message', 'textarea' ] ),
            'source_form' => 'WPForms - ' . ( $form_data['settings']['form_title'] ?? 'Unknown' ),
            'source_page' => home_url( $_SERVER['REQUEST_URI'] ?? '' ),
        ];

        $this->process_lead( $lead );
    }

    // ------------------------------------------------------------------ //
    //  Gravity Forms
    // ------------------------------------------------------------------ //

    public function handle_gravity( array $entry, array $form ): void {
        $lead = [
            'name'        => $this->gravity_field( $entry, $form, [ 'name', 'full name' ] ),
            'email'       => $this->gravity_field( $entry, $form, [ 'email' ] ),
            'phone'       => $this->gravity_field( $entry, $form, [ 'phone' ] ),
            'message'     => $this->gravity_field( $entry, $form, [ 'message', 'textarea' ] ),
            'source_form' => 'Gravity Forms - ' . ( $form['title'] ?? 'Unknown' ),
            'source_page' => $entry['source_url'] ?? '',
        ];

        $this->process_lead( $lead );
    }

    // ------------------------------------------------------------------ //
    //  Elementor Forms
    // ------------------------------------------------------------------ //

    public function handle_elementor( $record, $ajax_handler ): void {
        $raw = $record->get( 'fields' );

        $lead = [
            'name'        => $raw['name']['value']    ?? $raw['full_name']['value'] ?? '',
            'email'       => $raw['email']['value']   ?? '',
            'phone'       => $raw['phone']['value']   ?? '',
            'message'     => $raw['message']['value'] ?? '',
            'source_form' => 'Elementor Form',
            'source_page' => home_url( $_SERVER['REQUEST_URI'] ?? '' ),
        ];

        $this->process_lead( $lead );
    }

    // ------------------------------------------------------------------ //
    //  Generic POST fallback
    // ------------------------------------------------------------------ //

    public function handle_generic_post(): void {
        if ( ! isset( $_POST['leadping_capture'] ) ) return;
        if ( ! wp_verify_nonce( $_POST['leadping_nonce'] ?? '', 'leadping_submit' ) ) return;

        $lead = [
            'name'        => sanitize_text_field( $_POST['name']    ?? '' ),
            'email'       => sanitize_email(      $_POST['email']   ?? '' ),
            'phone'       => sanitize_text_field( $_POST['phone']   ?? '' ),
            'message'     => sanitize_textarea_field( $_POST['message'] ?? '' ),
            'source_form' => 'LeadPing Generic Form',
            'source_page' => wp_get_referer(),
        ];

        $this->process_lead( $lead );
    }

    // ------------------------------------------------------------------ //
    //  Core processor
    // ------------------------------------------------------------------ //

    private function process_lead( array $lead ): void {

        // Save to DB first
        $lead_id = LeadPing_DB::insert( $lead );

        // Notify business owner
        $owner_phone   = get_option( 'leadping_owner_phone', '' );
        $owner_message = $this->build_owner_message( $lead );

        if ( $owner_phone && $this->wa->send( $owner_phone, $owner_message ) ) {
            LeadPing_DB::update_notification_status( $lead_id, 'owner_notified' );
        }

        // Auto-reply to lead
        if ( ! empty( $lead['phone'] ) ) {
            $lead_message = $this->build_lead_message( $lead );
            if ( $this->wa->send( $lead['phone'], $lead_message ) ) {
                LeadPing_DB::update_notification_status( $lead_id, 'lead_notified' );
            }
        }

        // Fire action so other devs can hook in
        do_action( 'leadping_lead_processed', $lead_id, $lead );
    }

    // ------------------------------------------------------------------ //
    //  Message templates
    // ------------------------------------------------------------------ //

    private function build_owner_message( array $lead ): string {
        $business = get_bloginfo( 'name' );
        $name     = $lead['name']    ?: 'Unknown';
        $email    = $lead['email']   ?: 'Not provided';
        $phone    = $lead['phone']   ?: 'Not provided';
        $message  = $lead['message'] ?: 'No message';
        $page     = $lead['source_page'] ?: '';
        $time     = current_time( 'H:i' );

        $template = get_option( 'leadping_owner_template', '' );

        if ( $template ) {
            return str_replace(
                [ '{business}', '{name}', '{email}', '{phone}', '{message}', '{page}', '{time}' ],
                [ $business, $name, $email, $phone, $message, $page, $time ],
                $template
            );
        }

        return "🔔 New Lead - {$business}\n\n"
             . "👤 Name: {$name}\n"
             . "📧 Email: {$email}\n"
             . "📞 Phone: {$phone}\n"
             . "💬 Message: {$message}\n"
             . "🌐 Page: {$page}\n"
             . "⏰ Time: {$time}\n\n"
             . "Reply fast - speed wins the job!";
    }

    private function build_lead_message( array $lead ): string {
        $business = get_bloginfo( 'name' );
        $name     = $lead['name'] ? explode( ' ', $lead['name'] )[0] : 'there';

        $template = get_option( 'leadping_lead_template', '' );

        if ( $template ) {
            return str_replace(
                [ '{business}', '{first_name}' ],
                [ $business, $name ],
                $template
            );
        }

        return "Hi {$name}! 👋\n\n"
             . "Thanks for reaching out to {$business}.\n\n"
             . "We've received your message and someone will be in touch with you very shortly.\n\n"
             . "Talk soon!";
    }

    // ------------------------------------------------------------------ //
    //  Field extraction helpers
    // ------------------------------------------------------------------ //

    private function extract( array $data, array $keys ): string {
        foreach ( $keys as $key ) {
            if ( ! empty( $data[ $key ] ) ) {
                return is_array( $data[ $key ] ) ? implode( ' ', $data[ $key ] ) : (string) $data[ $key ];
            }
        }
        return '';
    }

    private function wpforms_field( array $fields, array $types ): string {
        foreach ( $fields as $field ) {
            if ( in_array( strtolower( $field['type'] ?? '' ), $types, true ) ) {
                return $field['value'] ?? '';
            }
        }
        return '';
    }

    private function gravity_field( array $entry, array $form, array $labels ): string {
        foreach ( $form['fields'] as $field ) {
            foreach ( $labels as $label ) {
                if ( stripos( $field->label, $label ) !== false ) {
                    return rgar( $entry, (string) $field->id ) ?? '';
                }
            }
        }
        return '';
    }
}

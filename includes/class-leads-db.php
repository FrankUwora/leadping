<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class LeadPing_DB {

    const TABLE = 'leadping_leads';

    public static function create_table() {
        global $wpdb;
        $table      = $wpdb->prefix . self::TABLE;
        $charset    = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$table} (
            id            BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name          VARCHAR(100)        NOT NULL DEFAULT '',
            email         VARCHAR(150)        NOT NULL DEFAULT '',
            phone         VARCHAR(50)         NOT NULL DEFAULT '',
            message       TEXT,
            source_form   VARCHAR(100)        NOT NULL DEFAULT '',
            source_page   VARCHAR(255)        NOT NULL DEFAULT '',
            owner_notified TINYINT(1)         NOT NULL DEFAULT 0,
            lead_notified  TINYINT(1)         NOT NULL DEFAULT 0,
            created_at    DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    public static function insert( array $data ) {
        global $wpdb;
        $wpdb->insert(
            $wpdb->prefix . self::TABLE,
            [
                'name'        => sanitize_text_field( $data['name']    ?? '' ),
                'email'       => sanitize_email(      $data['email']   ?? '' ),
                'phone'       => sanitize_text_field( $data['phone']   ?? '' ),
                'message'     => sanitize_textarea_field( $data['message'] ?? '' ),
                'source_form' => sanitize_text_field( $data['source_form'] ?? '' ),
                'source_page' => esc_url_raw(         $data['source_page'] ?? '' ),
            ],
            [ '%s', '%s', '%s', '%s', '%s', '%s' ]
        );
        return $wpdb->insert_id;
    }

    public static function update_notification_status( int $id, string $field ) {
        global $wpdb;
        $allowed = [ 'owner_notified', 'lead_notified' ];
        if ( ! in_array( $field, $allowed, true ) ) return;
        $wpdb->update(
            $wpdb->prefix . self::TABLE,
            [ $field => 1 ],
            [ 'id'   => $id ],
            [ '%d'  ],
            [ '%d'  ]
        );
    }

    public static function get_leads( int $limit = 50, int $offset = 0 ) {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE;
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table} ORDER BY created_at DESC LIMIT %d OFFSET %d",
                $limit,
                $offset
            )
        );
    }

    public static function count() {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE;
        return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
    }

    public static function count_today() {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE;
        return (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$table} WHERE DATE(created_at) = CURDATE()"
        );
    }

    public static function count_this_month() {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE;
        return (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$table}
             WHERE MONTH(created_at) = MONTH(CURDATE())
             AND   YEAR(created_at)  = YEAR(CURDATE())"
        );
    }
}

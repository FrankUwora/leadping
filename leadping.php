<?php
/**
 * Plugin Name: LeadPing
 * Plugin URI:  https://yoursite.com/leadping
 * Description: Captures form leads, sends instant WhatsApp notifications to the business owner and an auto-reply to the lead. All leads logged in a dashboard.
 * Version:     1.0.0
 * Author:      Frank Uwora
 * License:     GPL2
 * Text Domain: leadping
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'LEADPING_VERSION', '1.0.0' );
define( 'LEADPING_PATH', plugin_dir_path( __FILE__ ) );
define( 'LEADPING_URL',  plugin_dir_url( __FILE__ ) );

require_once LEADPING_PATH . 'includes/class-leads-db.php';
require_once LEADPING_PATH . 'includes/class-whatsapp.php';
require_once LEADPING_PATH . 'includes/class-form-handler.php';
require_once LEADPING_PATH . 'includes/class-dashboard.php';

register_activation_hook( __FILE__, [ 'LeadPing_DB', 'create_table' ] );

add_action( 'plugins_loaded', function() {
    new LeadPing_Form_Handler();
    new LeadPing_Dashboard();
});

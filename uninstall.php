<?php
// Exit if accessed directly
if (!defined('WP_UNINSTALL_PLUGIN')) exit;

// Load WordPress functions (in case this is run directly)
if (!function_exists('delete_option')) {
    require_once ABSPATH . 'wp-load.php';
}

// Only delete data if user explicitly requested it
$delete_data = get_option('sfd_delete_data_on_uninstall');

if ($delete_data === '1') {
    global $wpdb;

    // Delete plugin options
    delete_option('sfd_api_key');
    delete_option('sfd_site_id');
    delete_option('sfd_timezone');
    delete_option('sfd_email_enabled');
    delete_option('sfd_email_recipient');
    delete_option('sfd_chart_enabled');
    delete_option('sfd_chart_respect_consent');
    delete_option('sfd_secret_key'); 

    // Drop custom table
    $table = $wpdb->prefix . 'sfd_reports';
    $wpdb->query("DROP TABLE IF EXISTS $table");
}

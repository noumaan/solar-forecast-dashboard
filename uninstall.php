<?php
// Exit if accessed directly
if (!defined('WP_UNINSTALL_PLUGIN')) exit;

// Load WordPress functions (in case this is run directly)
if (!function_exists('delete_option')) {
    require_once ABSPATH . 'wp-load.php';
}

// Only delete data if user explicitly requested it
$delete_data = get_option('solfordash_delete_data_on_uninstall');

if ($delete_data === '1') {
    global $wpdb;

    // Delete plugin options
    delete_option('solfordash_api_key');
    delete_option('solfordash_site_id');
    delete_option('solfordash_timezone');
    delete_option('solfordash_email_enabled');
    delete_option('solfordash_email_recipient');
    delete_option('solfordash_chart_enabled');
    delete_option('solfordash_secret_key'); 

    // Drop custom table
    $table = $wpdb->prefix . 'solfordash_reports';
    $wpdb->query("DROP TABLE IF EXISTS $table");
}

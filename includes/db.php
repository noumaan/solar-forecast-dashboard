<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Creates custom database table for storing forecast reports.
 */
function sfd_create_reports_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'sfd_reports';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        forecast_date DATE NOT NULL,
        forecast_data LONGTEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY forecast_date (forecast_date)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

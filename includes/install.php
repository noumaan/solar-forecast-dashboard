<?php
if (!defined('ABSPATH')) exit;

function sfd_create_reports_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'sfd_reports';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        forecast_date DATE NOT NULL,
        forecast_data LONGTEXT NOT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY forecast_date (forecast_date)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

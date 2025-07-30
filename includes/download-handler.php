<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function solfordash_handle_csv_download() {
    if ( ! current_user_can('manage_options') ) {
        wp_die('Unauthorized', 'Error', ['response' => 403]);
    }

    if ( ! isset($_GET['date']) || ! isset($_GET['_wpnonce']) ) {
        wp_die('Missing parameters', 'Error', ['response' => 400]);
    }

    $date  = sanitize_text_field($_GET['date']);
    $nonce = sanitize_text_field($_GET['_wpnonce']);

    if ( ! wp_verify_nonce($nonce, 'solfordash_download_' . $date) ) {
        wp_die('Invalid nonce', 'Error', ['response' => 403]);
    }

    global $wpdb;
    $table = $wpdb->prefix . 'solfordash_reports';
    $row = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $table WHERE forecast_date = %s", $date),
        ARRAY_A
    );

    if ( ! $row ) {
        wp_die('No data found', 'Error', ['response' => 404]);
    }

    $data = json_decode($row['forecast_data'], true);

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="solar-forecast-' . esc_attr($date) . '.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Time', 'Low Estimate (10%)', 'Expected (50%)', 'High Estimate (90%)']);
    foreach ( $data as $point ) {
        fputcsv($output, [
            gmdate('g:i A', strtotime($point['local_time'])),
            $point['pv_estimate10'] ?? 0,
            $point['pv_estimate'] ?? 0,
            $point['pv_estimate90'] ?? 0,
        ]);
    }
    fclose($output);
    exit;
}

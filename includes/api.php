<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

function sfd_get_forecast_data($api_key, $timezone = 'Asia/Karachi') {
    $site_id = get_option('sfd_site_id');
    if (!$site_id) return false;

    $url = "https://api.solcast.com.au/rooftop_sites/$site_id/forecasts?format=json";

    $response = wp_remote_get($url, [
        'headers' => [
            'Authorization' => 'Bearer ' . $api_key,
        ],
        'timeout' => 20,
    ]);

    if (is_wp_error($response)) return false;

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (!isset($data['forecasts'])) return false;

    // Convert times to local
    $timezone = $timezone ?: 'Asia/Karachi';
    $converted = array_map(function ($item) use ($timezone) {
        $dt = new DateTime($item['period_end'], new DateTimeZone('UTC'));
        $dt->setTimezone(new DateTimeZone($timezone));
        $item['local_time'] = $dt->format('Y-m-d H:i');
        return $item;
    }, $data['forecasts']);

    return $converted;
}

function sfd_fetch_and_store_forecast() {
    global $wpdb;

    $api_key_encrypted = get_option('sfd_api_key');
    $site_id = get_option('sfd_site_id');
    $timezone = get_option('sfd_timezone', 'Asia/Karachi');

    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log("Manual fetch: Encrypted API key = $api_key_encrypted, Site ID = $site_id");
    }

    if (!$api_key_encrypted || !$site_id) return;

    $api_key = sfd_decrypt($api_key_encrypted);

    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log("Manual fetch: Decrypted API key = $api_key");
    }

    $forecast_data = sfd_get_forecast_data($api_key, $timezone);

    if (!$forecast_data) return;

    // Keep only entries for the next day (24 hours)
    $tomorrow = new DateTime('tomorrow', new DateTimeZone($timezone));
    $target_date = $tomorrow->format('Y-m-d');

    $filtered = array_filter($forecast_data, function ($item) use ($target_date) {
        return strpos($item['local_time'], $target_date) === 0;
    });

    if (empty($filtered)) return;

    // Avoid duplicates
    $table = $wpdb->prefix . 'sfd_reports';
    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE forecast_date = %s",
        $target_date
    ));

    if ($exists > 0) return;

    // Save to DB
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log("Manual fetch: About to insert into DB. Table = $table");
    }

    $wpdb->insert(
        $table,
        [
            'forecast_date' => $target_date,
            'forecast_data' => wp_json_encode($filtered)
        ]
    );

    if (defined('WP_DEBUG') && WP_DEBUG) {
        if ($wpdb->last_error) {
            error_log("Manual fetch: DB Error - " . $wpdb->last_error);
        } else {
            error_log("Manual fetch: Insert successful");
        }
    }
    
    // Send email report if enabled
    if (get_option('sfd_email_enabled')) {
        $recipient = get_option('sfd_email_recipient', get_option('admin_email'));
        sfd_send_email_report($filtered, $target_date, $recipient);
    }
}

    // get latest report 
    
function sfd_get_latest_report_row() {
	global $wpdb;

	$table = $wpdb->prefix . 'sfd_reports';

	$sql = "SELECT * FROM $table ORDER BY forecast_date DESC LIMIT 1";
	$row = $wpdb->get_row($sql, ARRAY_A);

	if (!$row || empty($row['forecast_data'])) {
		return false;
	}

	$row['decoded'] = json_decode($row['forecast_data'], true);
	return $row;
}

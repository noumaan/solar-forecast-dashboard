<?php 
function solfordash_send_email_report($data, $forecast_date, $recipient = null) {
	if (!$recipient) {
		$recipient = get_option('solfordash_email_recipient', get_option('admin_email'));
	}

	if (empty($data)) {
		error_log("Solar Forecast Email Error: Data array is empty for $forecast_date");
		return;
	}

	$subject = sprintf(
		__('Solar Forecast Report for %s', 'solar-forecast-dashboard'),
		$forecast_date
	);
	$headers = ['Content-Type: text/html; charset=UTF-8'];

	$rows = '';
	foreach ($data as $point) {
		if (!isset($point['local_time'])) continue;

		$rows .= '<tr>';
		$rows .= '<td>' . esc_html( date('g:i A', strtotime($point['local_time'])) ) . '</td>';
		$rows .= '<td>' . esc_html( number_format($point['pv_estimate10'] ?? 0, 2) ) . '</td>';
		$rows .= '<td>' . esc_html( number_format($point['pv_estimate'] ?? 0, 2) ) . '</td>';
		$rows .= '<td>' . esc_html( number_format($point['pv_estimate90'] ?? 0, 2) ) . '</td>';
		$rows .= '</tr>';
	}

	$message = '<h2>' . esc_html( sprintf( __( 'Solar Forecast for %s', 'solar-forecast-dashboard' ), $forecast_date ) ) . '</h2>
		<table style="width: 100%; border-collapse: collapse; border: 1px solid #ccc;">
			<thead>
				<tr style="background: #f5f5f5;">
					<th style="padding: 8px; border: 1px solid #ccc;">' . esc_html__( 'Time', 'solar-forecast-dashboard' ) . '</th>
					<th style="padding: 8px; border: 1px solid #ccc;">' . esc_html__( 'Low Estimate (10%)', 'solar-forecast-dashboard' ) . '</th>
					<th style="padding: 8px; border: 1px solid #ccc;">' . esc_html__( 'Expected (50%)', 'solar-forecast-dashboard' ) . '</th>
					<th style="padding: 8px; border: 1px solid #ccc;">' . esc_html__( 'High Estimate (90%)', 'solar-forecast-dashboard' ) . '</th>
				</tr>
			</thead>
			<tbody>' . $rows . '</tbody>
		</table>
		<p style="margin-top: 20px;">' . esc_html__( 'This email was sent by your WordPress site using the Solar Forecast plugin.', 'solar-forecast-dashboard' ) . '</p>';

	wp_mail($recipient, $subject, $message, $headers);
}

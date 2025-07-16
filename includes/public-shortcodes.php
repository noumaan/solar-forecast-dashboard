<?php

function sfd_get_table_name( $suffix ) {
	global $wpdb;
	return $wpdb->prefix . $suffix;
}

add_shortcode( 'sfd_forecast_tomorrow', 'sfd_forecast_tomorrow_shortcode' );
function sfd_forecast_tomorrow_shortcode() {
	
	global $wpdb;

	$table    = sfd_get_table_name( 'sfd_reports' );
	$timezone = get_option( 'sfd_timezone', 'Asia/Karachi' );
	$date     = ( new DateTime( 'tomorrow', new DateTimeZone( $timezone ) ) )->format( 'Y-m-d' );

	$sql      = $wpdb->prepare( "SELECT forecast_data FROM {$table} WHERE forecast_date = %s", $date );
	$forecast = $wpdb->get_var( $sql );

	if ( ! $forecast ) {
		return '';
	}

	$data  = json_decode( $forecast, true );
	$total = array_sum( array_column( $data, 'pv_estimate' ) );

	return esc_html( round( $total, 2 ) . ' kWh' );
}

add_shortcode( 'sfd_today_generation', 'sfd_today_generation_shortcode' );
function sfd_today_generation_shortcode() {
	
	global $wpdb;

	$table    = sfd_get_table_name( 'sfd_reports' );
	$timezone = get_option( 'sfd_timezone', 'Asia/Karachi' );
	$date     = ( new DateTime( 'today', new DateTimeZone( $timezone ) ) )->format( 'Y-m-d' );

	$sql      = $wpdb->prepare( "SELECT forecast_data FROM {$table} WHERE forecast_date = %s", $date );
	$forecast = $wpdb->get_var( $sql );

	if ( ! $forecast ) {
		return '';
	}

	$data  = json_decode( $forecast, true );
	$total = array_sum( array_column( $data, 'pv_estimate' ) );

	return esc_html( round( $total, 2 ) . ' kWh' );
}

add_shortcode( 'sfd_month_summary', 'sfd_month_summary_shortcode' );
function sfd_month_summary_shortcode() {
	
	global $wpdb;

	$table    = sfd_get_table_name( 'sfd_reports' );
	$timezone = get_option( 'sfd_timezone', 'Asia/Karachi' );
	$start    = ( new DateTime( 'first day of this month', new DateTimeZone( $timezone ) ) )->format( 'Y-m-d' );
	$end      = ( new DateTime( 'tomorrow', new DateTimeZone( $timezone ) ) )->format( 'Y-m-d' );

	$sql     = $wpdb->prepare( "SELECT forecast_data FROM {$table} WHERE forecast_date BETWEEN %s AND %s", $start, $end );
	$results = $wpdb->get_results( $sql );

	$total = 0;
	foreach ( $results as $row ) {
		$data   = json_decode( $row->forecast_data, true );
		$total += array_sum( array_column( $data, 'pv_estimate' ) );
	}

	return esc_html( round( $total, 2 ) . ' kWh' );
}

add_shortcode( 'sfd_monthly_impact', 'sfd_monthly_impact_shortcode' );
function sfd_monthly_impact_shortcode() {
	global $wpdb;

	$table    = sfd_get_table_name( 'sfd_reports' );
	$timezone = get_option( 'sfd_timezone', 'Asia/Karachi' );
	$start    = ( new DateTime( 'first day of this month', new DateTimeZone( $timezone ) ) )->format( 'Y-m-d' );
	$end      = ( new DateTime( 'tomorrow', new DateTimeZone( $timezone ) ) )->format( 'Y-m-d' );

	$sql     = $wpdb->prepare( "SELECT forecast_data FROM {$table} WHERE forecast_date BETWEEN %s AND %s", $start, $end );
	$results = $wpdb->get_results( $sql );

	$total_kwh = 0;
	foreach ( $results as $row ) {
		$data       = json_decode( $row->forecast_data, true );
		$total_kwh += array_sum( array_column( $data, 'pv_estimate' ) );
	}

	$co2_saved = round( $total_kwh * 0.92, 2 );
	$trees     = round( $co2_saved / 21.77 );

	return sprintf(
		// translators: 1: CO₂ saved in kg, 2: Number of trees planted
		esc_html__( 'This month, I saved approximately %1$s kg of CO₂, which is like planting 🌳%2$s trees.', 'solar-forecast-dashboard' ),
		'<strong>' . esc_html( $co2_saved ) . '</strong>',
		'<strong>' . esc_html( $trees ) . '</strong>'
	);
}

add_shortcode( 'sfd_year_summary', 'sfd_year_summary_shortcode' );
function sfd_year_summary_shortcode() {
	global $wpdb;

	$table    = sfd_get_table_name( 'sfd_reports' );
	$timezone = get_option( 'sfd_timezone', 'Asia/Karachi' );
	$start    = ( new DateTime( 'first day of January', new DateTimeZone( $timezone ) ) )->format( 'Y-m-d' );
	$end      = ( new DateTime( 'tomorrow', new DateTimeZone( $timezone ) ) )->format( 'Y-m-d' );

	$sql     = $wpdb->prepare( "SELECT forecast_data FROM {$table} WHERE forecast_date BETWEEN %s AND %s", $start, $end );
	$results = $wpdb->get_results( $sql );

	$total = 0;
	foreach ( $results as $row ) {
		$data   = json_decode( $row->forecast_data, true );
		$total += array_sum( array_column( $data, 'pv_estimate' ) );
	}

	return esc_html( round( $total, 2 ) . ' kWh' );
}

add_shortcode( 'sfd_yearly_impact', 'sfd_yearly_impact_shortcode' );
function sfd_yearly_impact_shortcode() {
	global $wpdb;

	$table    = sfd_get_table_name( 'sfd_reports' );
	$timezone = get_option( 'sfd_timezone', 'Asia/Karachi' );
	$start    = ( new DateTime( 'first day of January', new DateTimeZone( $timezone ) ) )->format( 'Y-m-d' );
	$end      = ( new DateTime( 'tomorrow', new DateTimeZone( $timezone ) ) )->format( 'Y-m-d' );

	$sql     = $wpdb->prepare( "SELECT forecast_data FROM {$table} WHERE forecast_date BETWEEN %s AND %s", $start, $end );
	$results = $wpdb->get_results( $sql );

	$total_kwh = 0;
	foreach ( $results as $row ) {
		$data       = json_decode( $row->forecast_data, true );
		$total_kwh += array_sum( array_column( $data, 'pv_estimate' ) );
	}

	$co2_saved = round( $total_kwh * 0.92, 2 );
	$trees     = round( $co2_saved / 21.77 );

	return sprintf(
		// translators: 1: CO₂ saved in kg, 2: Number of trees planted
		esc_html__( 'This year, my solar panels have saved %1$s kg of CO₂, equal to planting 🌳%2$s trees.', 'solar-forecast-dashboard' ),
		'<strong>' . esc_html( $co2_saved ) . '</strong>',
		'<strong>' . esc_html( $trees ) . '</strong>'
	);
}

add_shortcode( 'sfd_public_reports', 'sfd_render_public_reports' );
function sfd_render_public_reports() {
	global $wpdb;

	$table = sfd_get_table_name( 'sfd_reports' );
	$date  = isset( $_GET['sfd_view'] ) ? sanitize_text_field( wp_unslash( $_GET['sfd_view'] ) ) : '';

	if ( ! empty( $date ) && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
		$sql = $wpdb->prepare( "SELECT * FROM {$table} WHERE forecast_date = %s", $date );
		$row = $wpdb->get_row( $sql, ARRAY_A );

		if ( ! $row ) {
			return '<p>' . esc_html__( 'Report not found.', 'solar-forecast-dashboard' ) . '</p>';
		}

		$raw_data = json_decode( $row['forecast_data'], true );
		$data     = array_filter( $raw_data, function ( $point ) {
			return (
				! empty( $point['local_time'] ) &&
				isset( $point['pv_estimate10'], $point['pv_estimate'], $point['pv_estimate90'] ) &&
				is_numeric( $point['pv_estimate10'] ) &&
				is_numeric( $point['pv_estimate'] ) &&
				is_numeric( $point['pv_estimate90'] )
			);
		} );

		ob_start();
		?>
		<h2>
			<?php
			echo esc_html__( 'Solar Forecast for', 'solar-forecast-dashboard' ) . ' ' . esc_html( $date );
			?>
		</h2>

		<?php
		if ( get_option( 'sfd_chart_enabled' ) ) {
			sfd_maybe_enqueue_chartjs();
			echo '<canvas id="sfd_chart" height="400" style="width:100%; object-fit: contain; margin-bottom: 40px;"></canvas>';
		}
		?>

		<?php if ( get_option( 'sfd_chart_enabled' ) ) : ?>
			<script>
				document.addEventListener('DOMContentLoaded', function () {
					const ctx = document.getElementById('sfd_chart')?.getContext('2d');
					if (!ctx) return;

					const chartData = <?php echo wp_json_encode( array_values( array_map( function ( $point ) {
						if ( empty( $point['local_time'] ) ) return null;
						return [
							'time'     => gmdate( 'g:i A', strtotime( $point['local_time'] ) ),
							'low'      => isset( $point['pv_estimate10'] ) ? (float) $point['pv_estimate10'] : 0,
							'expected' => isset( $point['pv_estimate'] ) ? (float) $point['pv_estimate'] : 0,
							'high'     => isset( $point['pv_estimate90'] ) ? (float) $point['pv_estimate90'] : 0,
						];
					}, $data ) ) ); ?>;

					const labels = chartData.map(item => item.time);
					const lowData = chartData.map(item => item.low);
					const expectedData = chartData.map(item => item.expected);
					const highData = chartData.map(item => item.high);

					new Chart(ctx, {
						type: 'line',
						data: {
							labels,
							datasets: [
								{
									label: 'Low (10%)',
									data: lowData,
									borderColor: 'hotpink',
									backgroundColor: 'rgba(255,105,180,0.2)',
									fill: false,
									tension: 0.4,
									pointRadius: 3,
								},
								{
									label: 'Expected (50%)',
									data: expectedData,
									borderColor: 'deepskyblue',
									backgroundColor: 'rgba(0,191,255,0.2)',
									fill: false,
									tension: 0.4,
									pointRadius: 3,
								},
								{
									label: 'High (90%)',
									data: highData,
									borderColor: 'mediumturquoise',
									backgroundColor: 'rgba(72,209,204,0.2)',
									fill: false,
									tension: 0.4,
									pointRadius: 3,
								},
							]
						},
						options: {
							responsive: true,
							plugins: {
								legend: { position: 'top' },
								title: { display: true, text: 'Solar Forecast (kWh)' }
							},
							scales: {
								y: {
									title: { display: true, text: 'kWh' },
									beginAtZero: true
								},
								x: {
									title: { display: true, text: 'Time' }
								}
							}
						}
					});
				});
			</script>
		<?php endif; ?>

		<table style="width:100%; border-collapse: collapse; border:1px solid #ccc; margin-bottom: 20px;">
			<thead style="background: #f8f8f8;">
				<tr>
					<th style="padding: 8px;"><?php echo esc_html__( 'Time', 'solar-forecast-dashboard' ); ?></th>
					<th style="padding: 8px;"><?php echo esc_html__( 'Low (10%)', 'solar-forecast-dashboard' ); ?></th>
					<th style="padding: 8px;"><?php echo esc_html__( 'Expected (50%)', 'solar-forecast-dashboard' ); ?></th>
					<th style="padding: 8px;"><?php echo esc_html__( 'High (90%)', 'solar-forecast-dashboard' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $data as $point ) : ?>
					<tr>
						<td style="padding: 8px;"><?php echo esc_html( gmdate( 'g:i A', strtotime( $point['local_time'] ) ) ); ?></td>
						<td style="padding: 8px;"><?php echo esc_html( $point['pv_estimate10'] ) . ' kWh'; ?></td>
						<td style="padding: 8px;"><?php echo esc_html( $point['pv_estimate'] ) . ' kWh'; ?></td>
						<td style="padding: 8px;"><?php echo esc_html( $point['pv_estimate90'] ) . ' kWh'; ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<a href="<?php echo esc_url( remove_query_arg( 'sfd_view' ) ); ?>" style="display:inline-block; margin-top: 20px;">
			<?php echo esc_html__( '← Back to all reports', 'solar-forecast-dashboard' ); ?>
		</a>
		<?php
		return ob_get_clean();
	}

	// Load full report list
	$sql  = "SELECT forecast_date FROM {$table} ORDER BY forecast_date DESC";
	$rows = $wpdb->get_results( $sql, ARRAY_A );

	if ( empty( $rows ) ) {
		return '<p>' . esc_html__( 'No reports found.', 'solar-forecast-dashboard' ) . '</p>';
	}

	ob_start();
	?>
	<h2><?php echo esc_html__( 'Solar Forecast Reports', 'solar-forecast-dashboard' ); ?></h2>
	<ul style="list-style: none; padding-left: 0;">
		<?php foreach ( $rows as $row ) : ?>
			<li style="margin-bottom: 10px;">
				<a href="<?php echo esc_url( add_query_arg( 'sfd_view', urlencode( $row['forecast_date'] ) ) ); ?>" style="text-decoration: none; color: #0073aa;">
					<?php echo esc_html( $row['forecast_date'] ); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php
	return ob_get_clean();
}

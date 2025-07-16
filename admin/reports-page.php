<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'sfd_reports';

// Handle deletion
if ( isset( $_GET['delete'], $_GET['_wpnonce'] ) ) {
	$date  = sanitize_text_field( wp_unslash( $_GET['delete'] ) );
	$nonce = sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) );

	if ( wp_verify_nonce( $nonce, 'sfd_delete_' . $date ) ) {
		$wpdb->delete( $table_name, [ 'forecast_date' => $date ] );
		wp_redirect( admin_url( 'admin.php?page=sfd-reports' ) );
		exit;
	}
}

// Handle single report view
if ( isset( $_GET['view'], $_GET['_wpnonce'] ) ) {
	$date  = sanitize_text_field( wp_unslash( $_GET['view'] ) );
	$nonce = sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) );

	if ( ! wp_verify_nonce( $nonce, 'sfd_view_' . $date ) ) {
		echo '<div class="notice notice-error"><p>' . esc_html__( 'Invalid nonce.', 'solar-forecast-dashboard' ) . '</p></div>';
		return;
	}

	$row = $wpdb->get_row(
		$wpdb->prepare( "SELECT * FROM {$table_name} WHERE forecast_date = %s", $date ),
		ARRAY_A
	);

	if ( ! $row ) {
		echo '<div class="notice notice-error"><p>' . esc_html__( 'Report not found.', 'solar-forecast-dashboard' ) . '</p></div>';
		return;
	}

	$data = json_decode( $row['forecast_data'], true );
	if ( ! is_array( $data ) ) {
		echo '<div class="notice notice-error"><p>' . esc_html__( 'Invalid forecast data format.', 'solar-forecast-dashboard' ) . '</p></div>';
		return;
	}

	?>

	<div class="wrap">
		<h1><?php esc_html_e( 'Solar Forecast Report', 'solar-forecast-dashboard' ); ?></h1>
		<h2><?php echo esc_html( $date ); ?></h2>

		<?php
		if ( get_option( 'sfd_chart_enabled' ) ) {
			sfd_maybe_enqueue_chartjs();
			?>
			<div id="sfd-chart-container" style="max-width: 800px;">
    <canvas id="sfd_chart"></canvas>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const rawData = 
    
   <?php echo wp_json_encode(
    is_array($data) ? array_map(function ($point) {
        return [
            'time'   => gmdate('g:i A', strtotime($point['local_time'])),
            'low'    => (float) $point['pv_estimate10'],
            'median' => (float) $point['pv_estimate'],
            'high'   => (float) $point['pv_estimate90'],
        ];
    }, array_values($data)) : []
); ?>;


    if (!Array.isArray(rawData)) {
        console.error('rawData is not an array:', rawData);
        return;
    }

    const ctx = document.getElementById('sfd_chart')?.getContext('2d');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: rawData.map(d => d.time),
            datasets: [
                {
                    label: 'Low (10%)',
                    data: rawData.map(d => d.low),
                    borderColor: 'hotpink',
                    backgroundColor: 'rgba(255,105,180,0.2)',
                    fill: false,
                    tension: 0.4,
                    pointRadius: 3,
                },
                {
                    label: 'Expected (50%)',
                    data: rawData.map(d => d.median),
                    borderColor: 'deepskyblue',
                    backgroundColor: 'rgba(0,191,255,0.2)',
                    fill: false,
                    tension: 0.4,
                    pointRadius: 3,
                },
                {
                    label: 'High (90%)',
                    data: rawData.map(d => d.high),
                    borderColor: 'mediumturquoise',
                    backgroundColor: 'rgba(72,209,204,0.2)',
                    fill: false,
                    tension: 0.4,
                    pointRadius: 3,
                }
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



			<?php
		}
		?>

		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Time', 'solar-forecast-dashboard' ); ?></th>
					<th><?php esc_html_e( 'Low (10%)', 'solar-forecast-dashboard' ); ?></th>
					<th><?php esc_html_e( 'Expected (50%)', 'solar-forecast-dashboard' ); ?></th>
					<th><?php esc_html_e( 'High (90%)', 'solar-forecast-dashboard' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $data as $point ) : ?>
					<tr>
						<td><?php echo esc_html( gmdate( 'g:i A', strtotime( $point['local_time'] ) ) ); ?></td>
						<td><?php echo esc_html( $point['pv_estimate10'] ); ?> kWh</td>
						<td><?php echo esc_html( $point['pv_estimate'] ); ?> kWh</td>
						<td><?php echo esc_html( $point['pv_estimate90'] ); ?> kWh</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<p>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=sfd-reports' ) ); ?>" class="button">
				<?php esc_html_e( '← Back to Reports', 'solar-forecast-dashboard' ); ?>
			</a>
		</p>
	</div>
	<?php
	return;
}

// Default view: list of reports
$results = $wpdb->get_results(
	"SELECT forecast_date FROM {$table_name} ORDER BY forecast_date DESC",
	ARRAY_A
);
?>

<div class="wrap">
	<h1><?php esc_html_e( 'Solar Forecast Reports', 'solar-forecast-dashboard' ); ?></h1>

	<table class="widefat striped" role="presentation">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Date', 'solar-forecast-dashboard' ); ?></th>
				<th><?php esc_html_e( 'Actions', 'solar-forecast-dashboard' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $results as $report ) :
				$date = esc_attr( $report['forecast_date'] );
			?>
				<tr>
					<td><?php echo esc_html( $date ); ?></td>
					<td>
						<a href="<?php echo esc_url( wp_nonce_url( "admin.php?page=sfd-reports&view=$date", 'sfd_view_' . $date ) ); ?>" class="button button-primary">
							<?php esc_html_e( 'View', 'solar-forecast-dashboard' ); ?>
						</a>
						<a href="<?php echo esc_url( wp_nonce_url( "admin.php?page=sfd-reports&delete=$date", 'sfd_delete_' . $date ) ); ?>" class="button button-secondary" onclick="return confirm('<?php echo esc_js( __( 'Are you sure you want to delete this report?', 'solar-forecast-dashboard' ) ); ?>');">
							<?php esc_html_e( 'Delete', 'solar-forecast-dashboard' ); ?>
						</a>
						<a href="<?php echo esc_url(
							admin_url('admin-post.php?action=sfd_download_csv&date=' . $date . '&_wpnonce=' . wp_create_nonce('sfd_download_' . $date))
						); ?>" class="button button-secondary">
							<?php esc_html_e('Download CSV', 'solar-forecast-dashboard'); ?>
						</a>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>

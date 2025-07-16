<?php
/**
 * Plugin Name: Solar Forecast Dashboard
 * Description: Fetches daily solar forecast from Solcast, stores it, and optionally emails it.
 * Version: 1.0.0
 * Author: Noumaan Yaqoob
 * Text Domain: solar-forecast-dashboard
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */


if (!defined('ABSPATH')) exit;

define('SFD_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SFD_PLUGIN_DIR_URL', plugin_dir_url(__FILE__));
define( 'SFD_PLUGIN_FILE', __FILE__ );


// Load includes
require_once SFD_PLUGIN_DIR . 'includes/api.php';
require_once SFD_PLUGIN_DIR . 'includes/cron.php';
require_once SFD_PLUGIN_DIR . 'includes/security.php';
require_once SFD_PLUGIN_DIR . 'includes/email.php';
require_once SFD_PLUGIN_DIR . 'includes/install.php';
require_once SFD_PLUGIN_DIR . 'includes/public-shortcodes.php';
require_once SFD_PLUGIN_DIR . 'admin/settings-page.php';
require_once SFD_PLUGIN_DIR . 'admin/admin-documentation.php';
require_once SFD_PLUGIN_DIR . 'includes/download-handler.php';

add_action('admin_post_sfd_download_csv', 'sfd_handle_csv_download');


function sfd_add_query_var($vars) {
    $vars[] = 'sfd_view';
    return $vars;
}
add_filter('query_vars', 'sfd_add_query_var');




// Register admin menus
add_action('admin_menu', function () {

    // Top-level menu: Solar Forecast (loads settings page)
    add_menu_page(
        __( 'Solar Forecast Settings', 'solar-forecast-dashboard' ),
        __( 'Solar Forecast', 'solar-forecast-dashboard' ),
        'manage_options',
        'sfd-settings',
        'sfd_render_settings_page',
        'dashicons-chart-line',
        100
    );

    // Remove the default first submenu (duplicate of top-level)
    remove_submenu_page('sfd-settings', 'sfd-settings');

    // Submenu: Settings
    add_submenu_page(
        'sfd-settings',
        __( 'Settings', 'solar-forecast-dashboard' ),
        __( 'Settings', 'solar-forecast-dashboard' ),
        'manage_options',
        'sfd-settings',
        'sfd_render_settings_page'
    );

    // Submenu: Reports
    add_submenu_page(
        'sfd-settings',
        __( 'Reports', 'solar-forecast-dashboard' ),
        __( 'Reports', 'solar-forecast-dashboard' ),
        'manage_options',
        'sfd-reports',
        function () {
            require_once SFD_PLUGIN_DIR . 'admin/reports-page.php';
        }
    );

    // Submenu: Documentation
    add_submenu_page(
        'sfd-settings',
        __( 'Documentation', 'solar-forecast-dashboard' ),
        __( 'Documentation', 'solar-forecast-dashboard' ),
        'manage_options',
        'sfd-documentation',
        'sfd_render_documentation_page'
    );
});


// Activation hook: create DB table and show admin notice
register_activation_hook(__FILE__, function () {
    sfd_create_reports_table();
    set_transient('sfd_plugin_just_activated', true, 60);
});

// Admin notice on plugin activation
add_action('admin_notices', function () {
    if (get_transient('sfd_plugin_just_activated')) {
        delete_transient('sfd_plugin_just_activated');
        if (!current_user_can('manage_options')) return;

        $settings_url = admin_url('admin.php?page=sfd-settings');
        echo '<div class="notice notice-info is-dismissible">';
        echo '<p><strong>' . esc_html__( 'Solar Forecast Display:', 'solar-forecast-dashboard' ) . '</strong> ';
        echo esc_html__( 'Plugin activated.', 'solar-forecast-dashboard' ) . ' ';
        echo '<a href="' . esc_url( $settings_url ) . '">' . esc_html__( 'Click here to configure settings.', 'solar-forecast-dashboard' ) . '</a></p>';
        echo '</div>';
    }
});

// Conditionally load chart script based on user consent
function sfd_maybe_enqueue_chartjs() {
	if ( get_option( 'sfd_chart_enabled' ) ) {
		wp_enqueue_script(
			'sfd-chartjs',
			plugins_url( 'assets/js/chart.umd.min.js', SFD_PLUGIN_FILE ),
			array(),
			'4.4.1',
			true
		);
	}
}


// Cron job event
add_action('sfd_daily_forecast_event', 'sfd_fetch_and_store_forecast');

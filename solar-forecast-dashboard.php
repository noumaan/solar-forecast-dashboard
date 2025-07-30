<?php
/**
 * Plugin Name: Solar Forecast Dashboard
 * Description: Fetches daily solar forecast from Solcast, stores it, and optionally emails it.
 * Version: 1.0.0
 * Author: Noumaan Yaqoob
 * Text Domain: solar-forecast-dashboard
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) exit;

define('SOLFORDASH_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SOLFORDASH_PLUGIN_DIR_URL', plugin_dir_url(__FILE__));
define('SOLFORDASH_PLUGIN_FILE', __FILE__);

// Load includes
require_once SOLFORDASH_PLUGIN_DIR . 'includes/api.php';
require_once SOLFORDASH_PLUGIN_DIR . 'includes/cron.php';
require_once SOLFORDASH_PLUGIN_DIR . 'includes/security.php';
require_once SOLFORDASH_PLUGIN_DIR . 'includes/email.php';
require_once SOLFORDASH_PLUGIN_DIR . 'includes/install.php';
require_once SOLFORDASH_PLUGIN_DIR . 'includes/public-shortcodes.php';
require_once SOLFORDASH_PLUGIN_DIR . 'admin/settings-page.php';
require_once SOLFORDASH_PLUGIN_DIR . 'admin/admin-documentation.php';
require_once SOLFORDASH_PLUGIN_DIR . 'includes/download-handler.php';

add_action('admin_post_solfordash_download_csv', 'solfordash_handle_csv_download');

function solfordash_add_query_var($vars) {
    $vars[] = 'solfordash_view';
    return $vars;
}
add_filter('query_vars', 'solfordash_add_query_var');

// Register admin menus
add_action('admin_menu', function () {

    // Top-level menu: Solar Forecast (loads settings page)
    add_menu_page(
        __( 'Solar Forecast Settings', 'solar-forecast-dashboard' ),
        __( 'Solar Forecast', 'solar-forecast-dashboard' ),
        'manage_options',
        'solfordash-settings',
        'solfordash_render_settings_page',
        'dashicons-chart-line',
        100
    );

    // Remove the default first submenu (duplicate of top-level)
    remove_submenu_page('solfordash-settings', 'solfordash-settings');

    // Submenu: Settings
    add_submenu_page(
        'solfordash-settings',
        __( 'Settings', 'solar-forecast-dashboard' ),
        __( 'Settings', 'solar-forecast-dashboard' ),
        'manage_options',
        'solfordash-settings',
        'solfordash_render_settings_page'
    );

    // Submenu: Reports
    add_submenu_page(
        'solfordash-settings',
        __( 'Reports', 'solar-forecast-dashboard' ),
        __( 'Reports', 'solar-forecast-dashboard' ),
        'manage_options',
        'solfordash-reports',
        function () {
            require_once SOLFORDASH_PLUGIN_DIR . 'admin/reports-page.php';
        }
    );

    // Submenu: Documentation
    add_submenu_page(
        'solfordash-settings',
        __( 'Documentation', 'solar-forecast-dashboard' ),
        __( 'Documentation', 'solar-forecast-dashboard' ),
        'manage_options',
        'solfordash-documentation',
        'solfordash_render_documentation_page'
    );
});

// Activation hook: create DB table and show admin notice
register_activation_hook(__FILE__, function () {
    solfordash_create_reports_table();
    set_transient('solfordash_plugin_just_activated', true, 60);
});

// Admin notice on plugin activation
add_action('admin_notices', function () {
    if (get_transient('solfordash_plugin_just_activated')) {
        delete_transient('solfordash_plugin_just_activated');
        if (!current_user_can('manage_options')) return;

        $settings_url = admin_url('admin.php?page=solfordash-settings');
        echo '<div class="notice notice-info is-dismissible">';
        echo '<p><strong>' . esc_html__( 'Solar Forecast Display:', 'solar-forecast-dashboard' ) . '</strong> ';
        echo esc_html__( 'Plugin activated.', 'solar-forecast-dashboard' ) . ' ';
        echo '<a href="' . esc_url( $settings_url ) . '">' . esc_html__( 'Click here to configure settings.', 'solar-forecast-dashboard' ) . '</a></p>';
        echo '</div>';
    }
});

// Conditionally load chart script based on user consent
function solfordash_maybe_enqueue_chartjs() {
	if ( get_option( 'solfordash_chart_enabled' ) ) {
		wp_enqueue_script(
			'solfordash-chartjs',
			plugins_url( 'assets/js/chart.umd.min.js', SOLFORDASH_PLUGIN_FILE ),
			array(),
			'4.5.0',
			true
		);
	}
}

// Cron job event
add_action('solfordash_daily_forecast_event', 'solfordash_fetch_and_store_forecast');

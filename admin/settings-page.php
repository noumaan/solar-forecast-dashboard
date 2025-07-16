<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

require_once SFD_PLUGIN_DIR . 'includes/security.php';
require_once SFD_PLUGIN_DIR . 'includes/api.php';

if (!function_exists('sfd_render_settings_page')) {
    function sfd_render_settings_page() {
        $api_key_encrypted = get_option('sfd_api_key');
        $api_key = $api_key_encrypted ? sfd_decrypt($api_key_encrypted) : '';
        $site_id = get_option('sfd_site_id', '');
        $timezone = get_option('sfd_timezone', 'Asia/Karachi');
        $email_enabled = get_option('sfd_email_enabled', false);
        $email_recipient = get_option('sfd_email_recipient', get_option('admin_email'));
        $chart_enabled = get_option('sfd_chart_enabled', false);
        $respect_consent = get_option('sfd_chart_respect_consent', 0);
        $delete_data = get_option('sfd_delete_data_on_uninstall', '0');

        // Show warning if cron is not scheduled
        if (!wp_next_scheduled('sfd_daily_forecast_event')) {
            echo '<div class="notice notice-warning" role="alert"><p>' .
                esc_html__('The daily forecast cron job is not scheduled yet. Please', 'solar-forecast-dashboard') . 
                ' <strong>' . esc_html__('save your settings', 'solar-forecast-dashboard') . '</strong> ' .
                esc_html__('to enable automatic forecast fetching.', 'solar-forecast-dashboard') .
                '</p></div>';
        }

        // Handle manual fetch
        if (isset($_GET['run_now']) && $_GET['run_now'] == 1 && current_user_can('manage_options')) {
            if (wp_verify_nonce($_GET['_wpnonce'], 'sfd_run_now')) {
                require_once SFD_PLUGIN_DIR . 'includes/cron.php';
                sfd_fetch_and_store_forecast();
                echo '<div class="updated"><p>' . esc_html__('Forecast fetched successfully.', 'solar-forecast-dashboard') . '</p></div>';
            } else {
                echo '<div class="error"><p>' . esc_html__('Invalid security token (nonce).', 'solar-forecast-dashboard') . '</p></div>';
            }
        }

        // Handle POST save
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && current_user_can('manage_options')) {
            if (!isset($_POST['sfd_settings_nonce']) || !wp_verify_nonce($_POST['sfd_settings_nonce'], 'sfd_save_settings')) {
               wp_die(esc_html__('Security check failed. Please try again.', 'solar-forecast-dashboard'));
            }

            if (!empty($_POST['sfd_api_key'])) {
                update_option('sfd_api_key', sfd_encrypt(sanitize_text_field($_POST['sfd_api_key'])));
            }

            if (!empty($_POST['sfd_site_id'])) {
                update_option('sfd_site_id', sanitize_text_field($_POST['sfd_site_id']));
            }

            if (!empty($_POST['sfd_timezone'])) {
                update_option('sfd_timezone', sanitize_text_field($_POST['sfd_timezone']));
            }

            if (!empty($_POST['sfd_email_recipient'])) {
                update_option('sfd_email_recipient', sanitize_email($_POST['sfd_email_recipient']));
            }
            
      // Handle test email sending
if (isset($_GET['send_test']) && wp_verify_nonce($_GET['_wpnonce'], 'sfd_send_test')) {
	$row = sfd_get_latest_report_row();

	if ($row && !empty($row['decoded'])) {
		$recipient = get_option('sfd_email_recipient', get_option('admin_email'));
		sfd_send_email_report($row['decoded'], $row['forecast_date'], $recipient);

		echo '<div class="updated"><p>' . esc_html__('Test email sent successfully.', 'solar-forecast-dashboard') . '</p></div>';
	} else {
		echo '<div class="error"><p>' . esc_html__('No forecast data available. Please run a manual fetch first.', 'solar-forecast-dashboard') . '</p></div>';
	}
}


            update_option('sfd_email_enabled', isset($_POST['sfd_email_enabled']) ? 1 : 0);
            update_option('sfd_chart_enabled', isset($_POST['sfd_chart_enabled']) ? 1 : 0);
            update_option('sfd_chart_respect_consent', isset($_POST['sfd_chart_respect_consent']) ? 1 : 0);
            update_option('sfd_delete_data_on_uninstall', isset($_POST['sfd_delete_data_on_uninstall']) ? '1' : '0');

            // Optional: Auto-generate secret key if not already defined
            if (!defined('SFD_SECRET_KEY') && empty(get_option('sfd_secret_key'))) {
                $generated_key = bin2hex(random_bytes(32));
                update_option('sfd_secret_key', $generated_key);
                echo '<div class="notice notice-info"><p>' . esc_html__('A new secret key was generated automatically for secure encryption.', 'solar-forecast-dashboard') . '</p></div>';
            }

            // Schedule cron job
            if (!wp_next_scheduled('sfd_daily_forecast_event')) {
                $tz = new DateTimeZone(get_option('sfd_timezone', 'Asia/Karachi'));
                $time = new DateTime('23:00:00', $tz);
                $gmt_timestamp = $time->getTimestamp() - $tz->getOffset($time);
                wp_schedule_event($gmt_timestamp, 'daily', 'sfd_daily_forecast_event');
            }

            echo '<div class="updated" role="status"><p>' . esc_html__('Settings saved and cron job scheduled.', 'solar-forecast-dashboard') . '</p></div>';
        }
        ?>

        <div class="wrap" aria-labelledby="sfd_settings_heading">
            <h1 id="sfd_settings_heading"><?php echo esc_html__('Solar Forecast Settings', 'solar-forecast-dashboard'); ?></h1>
            <form method="POST" aria-label="<?php esc_attr_e('Solar Forecast Plugin Settings Form', 'solar-forecast-dashboard'); ?>">
                <?php wp_nonce_field('sfd_save_settings', 'sfd_settings_nonce'); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="sfd_api_key"><?php esc_html_e('Solcast API Key', 'solar-forecast-dashboard'); ?></label></th>
                        <td><input type="text" id="sfd_api_key" name="sfd_api_key" value="<?php echo esc_attr($api_key); ?>" size="50" aria-describedby="sfd_api_key_desc" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="sfd_site_id"><?php esc_html_e('Solcast Site ID', 'solar-forecast-dashboard'); ?></label></th>
                        <td>
                            <input type="text" id="sfd_site_id" name="sfd_site_id" value="<?php echo esc_attr($site_id); ?>" size="50" />
                            <p id="sfd_api_key_desc" class="description"><?php esc_html_e('Enter your Solcast rooftop site ID (e.g., abc12345-6789-def0-gh12-ijkl34567890).', 'solar-forecast-dashboard'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="sfd_timezone"><?php esc_html_e('Timezone', 'solar-forecast-dashboard'); ?></label></th>
                        <td>
                            <select name="sfd_timezone" id="sfd_timezone">
                                <?php
                                foreach (timezone_identifiers_list() as $tz) {
                                    printf(
                                        '<option value="%s"%s>%s</option>',
                                        esc_attr($tz),
                                        selected($tz, $timezone, false),
                                        esc_html($tz)
                                    );
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Enable Daily Email', 'solar-forecast-dashboard'); ?></th>
                        <td>
                            <input type="checkbox" id="sfd_email_enabled" name="sfd_email_enabled" <?php checked($email_enabled, 1); ?> />
                            <label for="sfd_email_enabled"><?php esc_html_e('Send daily forecast email', 'solar-forecast-dashboard'); ?></label>
                        </td>
                    </tr>
                    <tr id="sfd_email_recipient_row" style="<?php echo $email_enabled ? '' : 'display:none;'; ?>">
                        <th scope="row"><label for="sfd_email_recipient"><?php esc_html_e('Recipient Email', 'solar-forecast-dashboard'); ?></label></th>
                        <td>
                            <input type="email" id="sfd_email_recipient" name="sfd_email_recipient" value="<?php echo esc_attr($email_recipient); ?>" size="50" />
                            <p class="description"><?php esc_html_e('Daily report will be sent to this email.', 'solar-forecast-dashboard'); ?></p>
                        </td>
                    </tr>
                    <?php if ($email_enabled): ?>
                        <tr>
                            <th scope="row"><label for="sfd_test_email"><?php esc_html_e('Send Test Email', 'solar-forecast-dashboard'); ?></label></th>
                            <td>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=sfd-settings&send_test=1&_wpnonce=' . wp_create_nonce('sfd_send_test'))); ?>" class="button" aria-label="<?php esc_attr_e('Send test email to configured recipient', 'solar-forecast-dashboard'); ?>">
                                    <?php esc_html_e('Send Test Email', 'solar-forecast-dashboard'); ?>
                                </a>
                                <p class="description"><?php esc_html_e("We'll send a sample forecast report to your admin email or configured recipient.", 'solar-forecast-dashboard'); ?></p>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <th scope="row"><?php esc_html_e('Enable Chart.js', 'solar-forecast-dashboard'); ?></th>
                        <td>
                            <input type="checkbox" id="sfd_chart_enabled" name="sfd_chart_enabled" <?php checked($chart_enabled, 1); ?> />
                            <label for="sfd_chart_enabled"><?php esc_html_e('Show line charts on report pages using Chart.js', 'solar-forecast-dashboard'); ?></label>
                            <p class="description">
                                <?php esc_html_e('We will load Chart.js from a third-party CDN. This may involve cookies or IP logging.', 'solar-forecast-dashboard'); ?>
                                <a href="https://www.jsdelivr.com/terms/privacy-policy" target="_blank" rel="noopener noreferrer"><?php esc_html_e('View their privacy policy', 'solar-forecast-dashboard'); ?></a>.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('User Privacy & Cookie Consent', 'solar-forecast-dashboard'); ?></th>
                        <td>
                            <fieldset>
                                <label for="sfd_chart_respect_consent">
                                    <input type="checkbox" name="sfd_chart_respect_consent" id="sfd_chart_respect_consent" value="1" <?php checked(1, $respect_consent); ?> />
                                    <?php esc_html_e('Respect cookie consent before displaying chart', 'solar-forecast-dashboard'); ?>
                                </label>
                                <p class="description">
                                    <?php esc_html_e('The forecast chart is loaded from third-party servers. Some privacy regulations may require explicit user consent to display third-party content.', 'solar-forecast-dashboard'); ?>
                                    <br />
                                    <?php esc_html_e('Checking this option will prevent chart display until users give consent. We recommend using a cookie banner plugin like', 'solar-forecast-dashboard'); ?>
                                    <a href="https://wpconsent.com/?utm_source=nouman-solar-forecast-dashboard-plugin" target="_blank" rel="noopener"><?php esc_html_e('WPConsent', 'solar-forecast-dashboard'); ?></a>.
                                </p>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Delete Data on Uninstall', 'solar-forecast-dashboard'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="sfd_delete_data_on_uninstall" value="1" <?php checked($delete_data, '1'); ?> />
                                <?php esc_html_e('Delete all plugin data when the plugin is uninstalled.', 'solar-forecast-dashboard'); ?>
                            </label>
                            <p class="description"><?php esc_html_e('⚠️ This will permanently delete all forecast data and plugin settings when the plugin is deleted.', 'solar-forecast-dashboard'); ?></p>
                        </td>
                    </tr>
                </table>

                <?php submit_button(__('Save Settings', 'solar-forecast-dashboard')); ?>

                <hr>
                <h2><?php esc_html_e('Manual Fetch', 'solar-forecast-dashboard'); ?></h2>
                <p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=sfd-settings&run_now=1&_wpnonce=' . wp_create_nonce('sfd_run_now'))); ?>" class="button button-primary">
                        <?php esc_html_e('Run Forecast Fetch Now', 'solar-forecast-dashboard'); ?>
                    </a>
                </p>

                <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const checkbox = document.getElementById('sfd_email_enabled');
                    const emailRow = document.getElementById('sfd_email_recipient_row');

                    checkbox.addEventListener('change', function () {
                        emailRow.style.display = this.checked ? '' : 'none';
                    });
                });
                </script>
            </form>
        </div>
        <?php
    }
}

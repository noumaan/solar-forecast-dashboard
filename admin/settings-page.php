<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

require_once SOLFORDASH_PLUGIN_DIR . 'includes/security.php';
require_once SOLFORDASH_PLUGIN_DIR . 'includes/api.php';

if (!function_exists('solfordash_render_settings_page')) {
    function solfordash_render_settings_page() {
        $api_key_encrypted = get_option('solfordash_api_key');
        $api_key = $api_key_encrypted ? solfordash_decrypt($api_key_encrypted) : '';
        $site_id = get_option('solfordash_site_id', '');
        $timezone = get_option('solfordash_timezone', 'Asia/Karachi');
        $email_enabled = get_option('solfordash_email_enabled', false);
        $email_recipient = get_option('solfordash_email_recipient', get_option('admin_email'));
        $chart_enabled = get_option('solfordash_chart_enabled', false);
        $delete_data = get_option('solfordash_delete_data_on_uninstall', '0');

        // Show warning if cron is not scheduled
        if (!wp_next_scheduled('solfordash_daily_forecast_event')) {
            echo '<div class="notice notice-warning" role="alert"><p>' .
                esc_html__('The daily forecast cron job is not scheduled yet. Please', 'solar-forecast-dashboard') . 
                ' <strong>' . esc_html__('save your settings', 'solar-forecast-dashboard') . '</strong> ' .
                esc_html__('to enable automatic forecast fetching.', 'solar-forecast-dashboard') .
                '</p></div>';
        }

        // Handle manual fetch
        if (isset($_GET['run_now']) && $_GET['run_now'] == 1 && current_user_can('manage_options')) {
            if (wp_verify_nonce($_GET['_wpnonce'], 'solfordash_run_now')) {
                require_once SOLFORDASH_PLUGIN_DIR . 'includes/cron.php';
                solfordash_fetch_and_store_forecast();
                echo '<div class="updated"><p>' . esc_html__('Forecast fetched successfully.', 'solar-forecast-dashboard') . '</p></div>';
            } else {
                echo '<div class="error"><p>' . esc_html__('Invalid security token (nonce).', 'solar-forecast-dashboard') . '</p></div>';
            }
        }

        // Handle POST save
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && current_user_can('manage_options')) {
            if (!isset($_POST['solfordash_settings_nonce']) || !wp_verify_nonce($_POST['solfordash_settings_nonce'], 'solfordash_save_settings')) {
               wp_die(esc_html__('Security check failed. Please try again.', 'solar-forecast-dashboard'));
            }

            if (!empty($_POST['solfordash_api_key'])) {
                update_option('solfordash_api_key', solfordash_encrypt(sanitize_text_field($_POST['solfordash_api_key'])));
            }

            if (!empty($_POST['solfordash_site_id'])) {
                update_option('solfordash_site_id', sanitize_text_field($_POST['solfordash_site_id']));
            }

            if (!empty($_POST['solfordash_timezone'])) {
                update_option('solfordash_timezone', sanitize_text_field($_POST['solfordash_timezone']));
            }

            if (!empty($_POST['solfordash_email_recipient'])) {
                update_option('solfordash_email_recipient', sanitize_email($_POST['solfordash_email_recipient']));
            }

            update_option('solfordash_email_enabled', isset($_POST['solfordash_email_enabled']) ? 1 : 0);
            update_option('solfordash_chart_enabled', isset($_POST['solfordash_chart_enabled']) ? 1 : 0);
            update_option('solfordash_delete_data_on_uninstall', isset($_POST['solfordash_delete_data_on_uninstall']) ? '1' : '0');

            // Auto-generate secret key if missing
            if (!defined('SOLFORDASH_SECRET_KEY') && empty(get_option('solfordash_secret_key'))) {
                $generated_key = bin2hex(random_bytes(32));
                update_option('solfordash_secret_key', $generated_key);
                echo '<div class="notice notice-info"><p>' . esc_html__('A new secret key was generated automatically for secure encryption.', 'solar-forecast-dashboard') . '</p></div>';
            }

            // Schedule cron job
            if (!wp_next_scheduled('solfordash_daily_forecast_event')) {
                $tz = new DateTimeZone(get_option('solfordash_timezone', 'Asia/Karachi'));
                $time = new DateTime('23:00:00', $tz);
                $gmt_timestamp = $time->getTimestamp() - $tz->getOffset($time);
                wp_schedule_event($gmt_timestamp, 'daily', 'solfordash_daily_forecast_event');
            }

            echo '<div class="updated" role="status"><p>' . esc_html__('Settings saved and cron job scheduled.', 'solar-forecast-dashboard') . '</p></div>';
        }
        ?>

        <div class="wrap" aria-labelledby="solfordash_settings_heading">
            <h1 id="solfordash_settings_heading"><?php echo esc_html__('Solar Forecast Settings', 'solar-forecast-dashboard'); ?></h1>
            <form method="POST" aria-label="<?php esc_attr_e('Solar Forecast Plugin Settings Form', 'solar-forecast-dashboard'); ?>">
                <?php wp_nonce_field('solfordash_save_settings', 'solfordash_settings_nonce'); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="solfordash_api_key"><?php esc_html_e('Solcast API Key', 'solar-forecast-dashboard'); ?></label></th>
                        <td><input type="text" id="solfordash_api_key" name="solfordash_api_key" value="<?php echo esc_attr($api_key); ?>" size="50" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="solfordash_site_id"><?php esc_html_e('Solcast Site ID', 'solar-forecast-dashboard'); ?></label></th>
                        <td>
                            <input type="text" id="solfordash_site_id" name="solfordash_site_id" value="<?php echo esc_attr($site_id); ?>" size="50" />
                            <p class="description"><?php esc_html_e('Enter your Solcast rooftop site ID (e.g., abc12345-6789-def0-gh12-ijkl34567890).', 'solar-forecast-dashboard'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="solfordash_timezone"><?php esc_html_e('Timezone', 'solar-forecast-dashboard'); ?></label></th>
                        <td>
                            <select name="solfordash_timezone" id="solfordash_timezone">
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
                            <input type="checkbox" id="solfordash_email_enabled" name="solfordash_email_enabled" <?php checked($email_enabled, 1); ?> />
                            <label for="solfordash_email_enabled"><?php esc_html_e('Send daily forecast email', 'solar-forecast-dashboard'); ?></label>
                        </td>
                    </tr>
                    <tr id="solfordash_email_recipient_row" style="<?php echo $email_enabled ? '' : 'display:none;'; ?>">
                        <th scope="row"><label for="solfordash_email_recipient"><?php esc_html_e('Recipient Email', 'solar-forecast-dashboard'); ?></label></th>
                        <td>
                            <input type="email" id="solfordash_email_recipient" name="solfordash_email_recipient" value="<?php echo esc_attr($email_recipient); ?>" size="50" />
                            <p class="description"><?php esc_html_e('Daily report will be sent to this email.', 'solar-forecast-dashboard'); ?></p>
                        </td>
                    </tr>
                    <?php if ($email_enabled): ?>
                        <tr>
                            <th scope="row"><?php esc_html_e('Send Test Email', 'solar-forecast-dashboard'); ?></th>
                            <td>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=solfordash-settings&send_test=1&_wpnonce=' . wp_create_nonce('solfordash_send_test'))); ?>" class="button">
                                    <?php esc_html_e('Send Test Email', 'solar-forecast-dashboard'); ?>
                                </a>
                                <p class="description"><?php esc_html_e("We'll send a sample forecast report to your admin email or configured recipient.", 'solar-forecast-dashboard'); ?></p>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <th scope="row"><?php esc_html_e('Enable Chart.js', 'solar-forecast-dashboard'); ?></th>
                        <td>
                            <input type="checkbox" id="solfordash_chart_enabled" name="solfordash_chart_enabled" <?php checked($chart_enabled, 1); ?> />
                            <label for="solfordash_chart_enabled"><?php esc_html_e('Show line charts on report pages using Chart.js', 'solar-forecast-dashboard'); ?></label>
                            <p class="description">
                                <?php esc_html_e('Chart.js is loaded locally from this plugin. No data is sent to third-party servers.', 'solar-forecast-dashboard'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Delete Data on Uninstall', 'solar-forecast-dashboard'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="solfordash_delete_data_on_uninstall" value="1" <?php checked($delete_data, '1'); ?> />
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
                    <a href="<?php echo esc_url(admin_url('admin.php?page=solfordash-settings&run_now=1&_wpnonce=' . wp_create_nonce('solfordash_run_now'))); ?>" class="button button-primary">
                        <?php esc_html_e('Run Forecast Fetch Now', 'solar-forecast-dashboard'); ?>
                    </a>
                </p>

                <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const checkbox = document.getElementById('solfordash_email_enabled');
                    const emailRow = document.getElementById('solfordash_email_recipient_row');

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

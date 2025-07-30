<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

if (!function_exists('solfordash_render_documentation_page')) {
    function solfordash_render_documentation_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Solar Forecast Plugin Documentation', 'solar-forecast-dashboard'); ?></h1>

            <p><?php echo esc_html__('Below are the available shortcodes you can use to show solar forecast and generation data on your public pages.', 'solar-forecast-dashboard'); ?></p>

            <table class="widefat striped" aria-label="<?php esc_attr_e('List of shortcodes and their descriptions', 'solar-forecast-dashboard'); ?>">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Shortcode', 'solar-forecast-dashboard'); ?></th>
                        <th><?php esc_html_e('Description', 'solar-forecast-dashboard'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>[solfordash_forecast_tomorrow]</code></td>
                        <td><?php esc_html_e('Shows tomorrow’s expected solar forecast in kWh.', 'solar-forecast-dashboard'); ?></td>
                    </tr>
                    <tr>
                        <td><code>[solfordash_today_generation]</code></td>
                        <td><?php esc_html_e('Displays today’s total estimated solar energy generation.', 'solar-forecast-dashboard'); ?></td>
                    </tr>
                    <tr>
                        <td><code>[solfordash_month_summary]</code></td>
                        <td><?php esc_html_e('Shows month-to-date solar generation.', 'solar-forecast-dashboard'); ?></td>
                    </tr>
                    <tr>
                        <td><code>[solfordash_year_summary]</code></td>
                        <td><?php esc_html_e('Displays year-to-date total solar generation.', 'solar-forecast-dashboard'); ?></td>
                    </tr>
                    <tr>
                        <td><code>[solfordash_public_reports]</code></td>
                        <td><?php esc_html_e('Shows a public-facing reports table with no sensitive information.', 'solar-forecast-dashboard'); ?></td>
                    </tr>
                    <tr>
                        <td><code>[solfordash_monthly_impact]</code></td>
                        <td><?php esc_html_e('Shows month-to-date environmental impact and trees saved.', 'solar-forecast-dashboard'); ?></td>
                    </tr>
                    <tr>
                        <td><code>[solfordash_yearly_impact]</code></td>
                        <td><?php esc_html_e('Shows year-to-date environmental impact and trees saved.', 'solar-forecast-dashboard'); ?></td>
                    </tr>
                </tbody>
            </table>

            <h2 style="margin-top: 30px;"><?php esc_html_e('Email Troubleshooting', 'solar-forecast-dashboard'); ?></h2>
            <p><?php echo wp_kses_post(__('If you’re not receiving daily forecast emails, we recommend installing the <strong>WP Mail SMTP</strong> plugin to ensure your site can send emails reliably.', 'solar-forecast-dashboard')); ?></p>
            <p><?php esc_html_e('For feedback and support, please leave a message on the plugin author’s website.', 'solar-forecast-dashboard'); ?></p>

            <h2 style="margin-top: 30px;"><?php esc_html_e('Solcast API & Free Account Setup', 'solar-forecast-dashboard'); ?></h2>
            <p><?php echo wp_kses_post(__('This plugin uses the <a href="https://solcast.com/" target="_blank" rel="noopener noreferrer">Solcast API</a> to fetch solar forecast data for your location. To use it, you’ll need to create a free account on Solcast and configure your rooftop site.', 'solar-forecast-dashboard')); ?></p>
            <ul>
                <li><?php esc_html_e('Visit https://solcast.com/ and sign up.', 'solar-forecast-dashboard'); ?></li>
                <li><?php esc_html_e('After signing in, create a Rooftop Site to get your Site ID and API Key.', 'solar-forecast-dashboard'); ?></li>
                <li><?php esc_html_e('Enter these credentials on the plugin’s settings page inside your WordPress dashboard.', 'solar-forecast-dashboard'); ?></li>
            </ul>
            <p><?php esc_html_e('The free tier of Solcast provides enough API requests for most small setups.', 'solar-forecast-dashboard'); ?></p>

            <h2 style="margin-top: 30px;"><?php esc_html_e('Data Storage & Deletion', 'solar-forecast-dashboard'); ?></h2>
            <p><?php echo wp_kses_post(__('This plugin stores daily forecast data in a custom database table called <code>wp_solfordash_reports</code>. These records allow you to view and compare past forecasts.', 'solar-forecast-dashboard')); ?></p>
            <p><?php esc_html_e('If you want to remove specific reports, you can go to the Reports section in the plugin menu and use the "Delete" link next to each entry.', 'solar-forecast-dashboard'); ?></p>
            <p><?php esc_html_e('Deleted reports are permanently removed from the database and cannot be recovered.', 'solar-forecast-dashboard'); ?></p>

            <h2 style="margin-top: 30px;"><?php esc_html_e('Data Privacy & GDPR Compliance', 'solar-forecast-dashboard'); ?></h2>
            <p><?php esc_html_e('This plugin does not collect or transmit any personal user data. Solar forecast data is fetched directly from the Solcast API using credentials you provide and stored locally in your WordPress database. The plugin also loads Chart.js locally without contacting third-party servers.', 'solar-forecast-dashboard'); ?></p>
            <p><?php esc_html_e('No personal information is shared externally. If you wish, you can mention in your site’s privacy policy that your website uses Solcast to fetch non-personal forecast data for display purposes.', 'solar-forecast-dashboard'); ?></p>

        </div>
        <?php
    }
}

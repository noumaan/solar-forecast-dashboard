<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

// Only load helper files
require_once SFD_PLUGIN_DIR . 'includes/api.php';
require_once SFD_PLUGIN_DIR . 'includes/security.php';
require_once SFD_PLUGIN_DIR . 'includes/email.php';

// Hook the function (defined in api.php) to our custom daily cron event
add_action('sfd_daily_forecast_event', 'sfd_fetch_and_store_forecast');


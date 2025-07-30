<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

// Only load helper files
require_once SOLFORDASH_PLUGIN_DIR . 'includes/api.php';
require_once SOLFORDASH_PLUGIN_DIR . 'includes/security.php';
require_once SOLFORDASH_PLUGIN_DIR . 'includes/email.php';

// Hook the function (defined in api.php) to our custom daily cron event
add_action('solfordash_daily_forecast_event', 'solfordash_fetch_and_store_forecast');

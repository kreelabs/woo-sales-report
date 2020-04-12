<?php

/***
 * Plugin Name: Woo Sales Report
 * Plugin URI: https://kreelabs.com
 * Description: All in one reporting plugin for WooCommerce.
 * Version: 0.0.0
 * Requires at least: 4.3
 * Tested up to: 5.4
 * WC requires at least: 3.6
 * WC tested up to: 4.0
 * Requires PHP: 5.6
 * Stable tag: 0.0.0
 * Author: KreeLabs
 * Author URI: https://kreelabs.com
 * Text Domain: woo-sales-report
 * Domain Path: /languages
 *
 * Copyright (c) 2020 KreeLabs <hello@kreelabs.com>.
 */

// Avoid direct calls to this file.
if ( ! defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    die('Access Forbidden');
}

define('WSR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WSR_TEXT_DOMAIN', 'woo-sales-report');
define('WSR_EXPORT_LIMIT', 100);

require __DIR__ . '/vendor/autoload.php';

$wcReport = new \KreeLabs\WSR\Reports();

// Initialize admin section.
add_action('init', [$wcReport, 'initAdmin']);

// Check if required plugins are installed and activated.
add_action('plugins_loaded', function () use ($wcReport) {
    if ( ! class_exists(WooCommerce::class)) {
        add_action('admin_notices', [$wcReport, 'requirementsCheck']);
    }
});

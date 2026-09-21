<?php
/**
 * Syncly Site Reports & Event History
 *
 * @package Syncly_Site_Reports
 *
 * Plugin Name: Syncly Site Reports & Event History
 * Plugin URI: https://github.com/Cottonnion/site-wise
 * Description: A clean activity log and shareable site reports for freelancers and agencies.
 * Version: 1.1.0
 * Author: Yahya Eddaqqaq
 * Author URI: https://profiles.wordpress.org/yahyadeved/
 * Requires at least: 6.0
 * Requires PHP: 8.1
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: syncly-site-reports
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

if (version_compare(PHP_VERSION, '8.1', '<')) {
    add_action('admin_notices', function () {
        echo '<div class="notice notice-error"><p>' .
            esc_html__('Syncly Site Reports & Event History requires PHP 8.1 or higher. Please upgrade PHP before activating this plugin.', 'syncly-site-reports') .
            '</p></div>';
    });
    return;
}

define('WSAL_VERSION',  '1.1.0');
define('WSAL_PATH',     plugin_dir_path(__FILE__));
define('WSAL_URL',      plugin_dir_url(__FILE__));
define('WSAL_BASENAME', plugin_basename(__FILE__));

require_once WSAL_PATH . 'vendor/autoload.php';

register_activation_hook(__FILE__, ['WPSiteActivityLog\Core\Plugin', 'activate']);
register_deactivation_hook(__FILE__, ['WPSiteActivityLog\Core\Plugin', 'deactivate']);
register_uninstall_hook(__FILE__, ['WPSiteActivityLog\Core\Plugin', 'uninstall']);

add_action('plugins_loaded', function () {
    load_plugin_textdomain('syncly-site-reports', false, dirname(plugin_basename(__FILE__)) . '/languages');
    \WPSiteActivityLog\Core\Plugin::get_instance()->init();
});

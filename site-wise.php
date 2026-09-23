<?php
/**
 * Loghaven Site Logs & Reports
 *
 * @package Loghaven_Site_Logs
 *
 * Plugin Name: Loghaven Site Logs & Reports
 * Plugin URI: https://github.com/Cottonnion/site-wise
 * Description: A lightweight WordPress activity log, audit trail, and white-label client reports for agencies, freelancers, and store owners.
 * Version: 1.3.0
 * Author: Yahya Eddaqqaq
 * Author URI: https://profiles.wordpress.org/yahyadeved/
 * Requires at least: 6.0
 * Requires PHP: 8.1
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: loghaven-site-logs
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

define('WSAL_PLUGIN_NAME', 'Loghaven Site Logs & Reports');

if (version_compare(PHP_VERSION, '8.1', '<')) {
    add_action('admin_notices', function () {
        printf(
            '<div class="notice notice-error"><p>%s</p></div>',
            sprintf(
                esc_html__('%1$s requires PHP 8.1 or higher. Please upgrade PHP before activating this plugin.', 'loghaven-site-logs'),
                esc_html(WSAL_PLUGIN_NAME)
            )
        );
    });
    return;
}

define('WSAL_VERSION',  '1.3.0');
define('WSAL_PATH',     plugin_dir_path(__FILE__));
define('WSAL_URL',      plugin_dir_url(__FILE__));
define('WSAL_BASENAME', plugin_basename(__FILE__));

require_once WSAL_PATH . 'vendor/autoload.php';

register_activation_hook(__FILE__, ['WPSiteActivityLog\Core\Plugin', 'activate']);
register_deactivation_hook(__FILE__, ['WPSiteActivityLog\Core\Plugin', 'deactivate']);
register_uninstall_hook(__FILE__, ['WPSiteActivityLog\Core\Plugin', 'uninstall']);

add_action('plugins_loaded', function () {
    load_plugin_textdomain('loghaven-site-logs', false, dirname(plugin_basename(__FILE__)) . '/languages');
    \WPSiteActivityLog\Core\Plugin::get_instance()->init();
});

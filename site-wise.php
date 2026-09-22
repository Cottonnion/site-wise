<?php
/**
 * Loghaven Site Logs & Reports
 *
 * @package Loghaven_Site_Logs
 *
 * Plugin Name: Loghaven Site Logs & Reports
 * Plugin URI: https://github.com/Cottonnion/site-wise
 * Description: A clean activity log and shareable site reports for freelancers and agencies.
 * Version: 1.1.1
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

if (version_compare(PHP_VERSION, '8.1', '<')) {
    add_action('admin_notices', function () {
        echo '<div class="notice notice-error"><p>' .
            esc_html__('Loghaven Site Logs & Reports requires PHP 8.1 or higher. Please upgrade PHP before activating this plugin.', 'loghaven-site-logs') .
            '</p></div>';
    });
    return;
}

define('WSAL_VERSION',  '1.1.1');
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

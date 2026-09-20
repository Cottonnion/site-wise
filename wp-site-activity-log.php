<?php
/**
 * WP Site Activity Log
 *
 * @package WP_Site_Activity_Log
 *
 * Plugin Name: WP Site Activity Log
 * Description: A clean activity log and shareable site reports for freelancers and agencies.
 * Version: 1.0.0
 * Author: Yahya Eddaqqaq
 * Author URI: https://profiles.wordpress.org/yahyaeeddaqqaq/
 * Requires at least: 6.0
 * Requires PHP: 8.1
 * Tested up to: 7.1.1
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: site-wise
 * Domain Path: /languages
 */

declare(strict_types=1);

if (!defined('ABSPATH')) exit;

define('WSAL_VERSION',  '1.0.0');
define('WSAL_PATH',     plugin_dir_path(__FILE__));
define('WSAL_URL',      plugin_dir_url(__FILE__));
define('WSAL_BASENAME', plugin_basename(__FILE__));

require_once WSAL_PATH . 'vendor/autoload.php';

add_action('plugins_loaded', function () {
    load_plugin_textdomain('site-wise', false, dirname(plugin_basename(__FILE__)) . '/languages');
    \WPSiteActivityLog\Core\Plugin::get_instance()->init();
});

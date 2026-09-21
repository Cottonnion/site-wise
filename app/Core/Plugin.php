<?php
declare(strict_types=1);

namespace WPSiteActivityLog\Core;

if (!defined('ABSPATH')) exit;

use WPSiteActivityLog\Log\EventRegistry;
use WPSiteActivityLog\Log\ActivityLogger;
use WPSiteActivityLog\Log\LogQuery;
use WPSiteActivityLog\Trackers\PostTracker;
use WPSiteActivityLog\Trackers\UserTracker;
use WPSiteActivityLog\Trackers\PluginTracker;
use WPSiteActivityLog\Trackers\SettingsTracker;
use WPSiteActivityLog\Trackers\CoreTracker;
use WPSiteActivityLog\Trackers\ThemeTracker;
use WPSiteActivityLog\Trackers\MediaTracker;
use WPSiteActivityLog\Trackers\CommentTracker;
use WPSiteActivityLog\Trackers\TaxonomyTracker;
use WPSiteActivityLog\Admin\AdminController;
use WPSiteActivityLog\Admin\ReportAnalyzer;
use WPSiteActivityLog\Admin\ReportGenerator;
use WPSiteActivityLog\Admin\NoticeManager;

class Plugin
{
    private static ?self $instance = null;

    private function __construct() {}

    private function __clone() {}

    public function __wakeup(): void
    {
        throw new \Exception('Cannot unserialize Plugin');
    }

    public static function get_instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init(): void
    {
        $loader = Loader::get_instance();
        $db_manager = DatabaseManager::get_instance();
        $settings_manager = SettingsManager::get_instance();
        $menu_manager = MenuManager::get_instance();
        $assets_manager = AssetsManager::get_instance();
        $event_registry = EventRegistry::get_instance();
        $activity_logger = ActivityLogger::get_instance();
        $log_query = LogQuery::get_instance();
        $post_tracker = PostTracker::get_instance();
        $user_tracker = UserTracker::get_instance();
        $plugin_tracker = PluginTracker::get_instance();
        $settings_tracker = SettingsTracker::get_instance();
        $core_tracker = CoreTracker::get_instance();
        $theme_tracker = ThemeTracker::get_instance();
        $media_tracker = MediaTracker::get_instance();
        $comment_tracker = CommentTracker::get_instance();
        $taxonomy_tracker = TaxonomyTracker::get_instance();
        $admin_controller = AdminController::get_instance();
        $report_analyzer = ReportAnalyzer::get_instance();
        $report_generator = ReportGenerator::get_instance();
        $notice_manager = NoticeManager::get_instance();

        $event_registry->init();
        $activity_logger->init();
        $menu_manager->register();
        $assets_manager->init();
        $admin_controller->init();
        $post_tracker->init();
        $user_tracker->init();
        $plugin_tracker->init();
        $settings_tracker->init();
        $core_tracker->init();
        $theme_tracker->init();
        $media_tracker->init();
        $comment_tracker->init();
        $taxonomy_tracker->init();
        $report_analyzer->init();
        $report_generator->init();
        $notice_manager->init();

        $db_manager->ensure_schema();
        $this->schedule_maintenance();

        add_action('wsal_daily_maintenance', [$this, 'run_daily_maintenance']);

        $loader->run();
    }

    public static function activate(): void
    {
        if (version_compare(get_bloginfo('version'), '6.0', '<')) {
            deactivate_plugins(WSAL_BASENAME);
            wp_die(
                esc_html__('Syncly Site Reports & Event History requires WordPress 6.0 or higher. Please upgrade WordPress before activating this plugin.', 'syncly-site-reports'),
                '',
                ['back_link' => true]
            );
        }

        DatabaseManager::get_instance()->install();
    }

    public static function deactivate(): void
    {
        wp_clear_scheduled_hook('wsal_daily_maintenance');
    }

    public function schedule_maintenance(): void
    {
        if (!wp_next_scheduled('wsal_daily_maintenance')) {
            wp_schedule_event(time() + HOUR_IN_SECONDS, 'daily', 'wsal_daily_maintenance');
        }
    }

    public function run_daily_maintenance(): void
    {
        if (!DatabaseManager::get_instance()->table_exists()) {
            return;
        }

        $retention_days = (int)SettingsManager::get_instance()->get('retention_days', 90);
        LogQuery::get_instance()->delete_old_logs($retention_days);
    }

    public static function uninstall(): void
    {
        wp_clear_scheduled_hook('wsal_daily_maintenance');
        global $wpdb;
        $table = DatabaseManager::get_instance()->get_table_name();
        $wpdb->query($wpdb->prepare('DROP TABLE IF EXISTS %i', $table));
        delete_option('wsal_settings');
    }
}

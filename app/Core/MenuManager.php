<?php
declare(strict_types=1);

namespace WPSiteActivityLog\Core;

if (!defined('ABSPATH')) exit;

use WPSiteActivityLog\Admin\AdminController;

class MenuManager
{
    private static ?self $instance = null;

    private function __construct() {}

    private function __clone() {}

    public function __wakeup(): void
    {
        throw new \Exception('Cannot unserialize MenuManager');
    }

    public static function get_instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function register(): void
    {
        add_action('admin_menu', [$this, 'add_menu_pages']);
    }

    public function add_menu_pages(): void
    {
        $capability = 'manage_options';
        $slug = 'wsal-dashboard';

        $assets = AssetsManager::get_instance();

        $hook = add_menu_page(
            __('Activity Log', 'syncly-site-reports'),
            __('Activity Log', 'syncly-site-reports'),
            $capability,
            $slug,
            [$this, 'render_dashboard_page'],
            'dashicons-list-view',
            26
        );
        $assets->register_page('wsal-dashboard', (string)$hook);
    }

    public function render_dashboard_page(): void
    {
        AdminController::get_instance()->render_page('wsal-dashboard');
    }
}

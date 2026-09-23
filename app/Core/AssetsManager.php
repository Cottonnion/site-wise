<?php
declare(strict_types=1);

namespace WPSiteActivityLog\Core;

if (!defined('ABSPATH')) exit;

class AssetsManager
{
    private static ?self $instance = null;

    /** @var array<string, AssetDefinition[]> */
    private array $admin_assets = [];

    /** @var AssetDefinition[] */
    private array $admin_global_assets = [];

    /** @var AssetDefinition[] */
    private array $public_assets = [];

    /** @var array<string, string> page slug => screen id (hook suffix) */
    private array $page_hooks = [];

    private function __construct() {}

    private function __clone() {}

    public function __wakeup(): void
    {
        throw new \Exception('Cannot unserialize AssetsManager');
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
        add_action('init', [$this, 'define_admin_assets'], 5);
        add_action('init', [$this, 'define_frontend_assets'], 5);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    public function register_page(string $slug, string $hook): void
    {
        $this->page_hooks[$slug] = $hook;
    }

    public function define_admin_assets(): void
    {
        $this->admin_global_assets = [
            new AssetDefinition(
                'wsal-globals-css',
                'assets/admin/css/globals.css',
                [],
                [],
                WSAL_VERSION,
                false
            ),
        ];

        $this->admin_assets['wsal-dashboard'] = [
            new AssetDefinition(
                'wsal-dashboard-css',
                'assets/admin/css/dashboard.css',
                ['wsal-globals-css'],
                [],
                WSAL_VERSION,
                false
            ),
            new AssetDefinition(
                'wsal-log-css',
                'assets/admin/css/activity-log.css',
                ['wsal-globals-css'],
                [],
                WSAL_VERSION,
                false
            ),
            new AssetDefinition(
                'wsal-settings-css',
                'assets/admin/css/settings.css',
                ['wsal-globals-css'],
                [],
                WSAL_VERSION,
                false
            ),
            new AssetDefinition(
                'wsal-app-js',
                'assets/admin/js/app.js',
                ['jquery'],
                ['wsal_app_data' => [
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('wsal_spa'),
                    'default_view' => 'dashboard',
                    'copied_msg' => __('Link copied!', 'loghaven-site-logs'),
                    'clear_scopes' => [
                        ['value' => 'all', 'label' => __('All logs', 'loghaven-site-logs')],
                        ['value' => 'older_7', 'label' => __('Older than 7 days', 'loghaven-site-logs')],
                        ['value' => 'older_30', 'label' => __('Older than 30 days', 'loghaven-site-logs')],
                        ['value' => 'older_90', 'label' => __('Older than 90 days', 'loghaven-site-logs')],
                        ['value' => 'older_180', 'label' => __('Older than 180 days', 'loghaven-site-logs')],
                    ],
                    'clear_logs_title' => __('Clear activity logs', 'loghaven-site-logs'),
                    'clear_logs_confirm' => __('Clear logs', 'loghaven-site-logs'),
                    'clear_logs_cancel' => __('Cancel', 'loghaven-site-logs'),
                    'clear_logs_scope' => __('Scope', 'loghaven-site-logs'),
                    'clear_logs_count' => __('This will remove approximately %1$d log entries. This action cannot be undone.', 'loghaven-site-logs'),
                    'clear_logs_count_zero' => __('No log entries match this criteria.', 'loghaven-site-logs'),
                    'clear_logs_loading' => __('Counting…', 'loghaven-site-logs'),
                    'cleared_msg' => __('%1$d log entries cleared.', 'loghaven-site-logs'),
                ]],
                WSAL_VERSION,
                true
            ),
        ];
    }

    public function define_frontend_assets(): void
    {
        $this->public_assets[] = new AssetDefinition(
            'wsal-report-css',
            'assets/frontend/css/report.css',
            [],
            [],
            WSAL_VERSION,
            false
        );
        $this->public_assets[] = new AssetDefinition(
            'wsal-report-js',
            'assets/frontend/js/report.js',
            ['jquery'],
            ['wsal_report_data' => ['ajax_url' => admin_url('admin-ajax.php')]],
            WSAL_VERSION,
            true
        );
    }

    public function enqueue_admin_assets(): void
    {
        $screen = get_current_screen();
        if (!$screen) {
            return;
        }

        $page = $this->resolve_page_slug($screen->id);
        if ($page === null || !isset($this->admin_assets[$page])) {
            return;
        }

        $this->register_global_assets();
        wp_enqueue_media();

        foreach ($this->admin_assets[$page] as $asset) {
            $this->enqueue_asset($asset->handle, $asset, 'admin');
        }
    }

    private function register_global_assets(): void
    {
        foreach ($this->admin_global_assets as $asset) {
            $file_url = $asset->base_url ?? (WSAL_URL . $asset->file);

            $min_file = preg_replace('/\.(css|js)$/', '.min.$1', $asset->file) ?? $asset->file;
            $min_file_path = WSAL_PATH . $min_file;
            $min_file_url = $asset->base_url ?? (WSAL_URL . $min_file);

            $use_min = (!defined('SCRIPT_DEBUG') || !SCRIPT_DEBUG) && file_exists($min_file_path);
            $file_to_use = $use_min ? $min_file_url : $file_url;

            wp_register_style($asset->handle, $file_to_use, $asset->deps, $asset->version);
        }
    }

    public function enqueue_report_assets(): void
    {
        $this->enqueue_public_asset_list();
    }

    private function enqueue_public_asset_list(): void
    {
        foreach ($this->public_assets as $asset) {
            $this->enqueue_asset($asset->handle, $asset, 'public');
        }
    }

    private function resolve_page_slug(string $screen_id): ?string
    {
        foreach ($this->page_hooks as $slug => $hook) {
            if ($screen_id === $hook || $screen_id === $slug) {
                return $slug;
            }
        }

        if (str_starts_with($screen_id, 'toplevel_page_') && str_contains($screen_id, 'wsal-dashboard')) {
            return 'wsal-dashboard';
        }

        return null;
    }

    private function enqueue_asset(string $handle, AssetDefinition $asset, string $context): void
    {
        $file_url = $asset->base_url ?? (WSAL_URL . $asset->file);

        $min_file = preg_replace('/\.(css|js)$/', '.min.$1', $asset->file) ?? $asset->file;
        $min_file_path = WSAL_PATH . $min_file;
        $min_file_url = $asset->base_url ?? (WSAL_URL . $min_file);

        $use_min = (!defined('SCRIPT_DEBUG') || !SCRIPT_DEBUG) && file_exists($min_file_path);
        $file_to_use = $use_min ? $min_file_url : $file_url;

        if (str_ends_with($asset->file, '.css')) {
            wp_enqueue_style($handle, $file_to_use, $asset->deps, $asset->version);
        } else {
            wp_enqueue_script($handle, $file_to_use, $asset->deps, $asset->version, $asset->in_footer);
            if (!empty($asset->localize)) {
                foreach ($asset->localize as $object_name => $localize_data) {
                    $sanitized_name = str_replace('-', '_', $object_name);
                    wp_localize_script($handle, $sanitized_name, $localize_data);
                }
            }
        }
    }

    public function add_admin_asset(AssetDefinition $asset, array $pages): void
    {
        foreach ($pages as $page) {
            if (!isset($this->admin_assets[$page])) {
                $this->admin_assets[$page] = [];
            }
            $this->admin_assets[$page][] = $asset;
        }
    }

    public function add_public_asset(AssetDefinition $asset): void
    {
        $this->public_assets[] = $asset;
    }
}
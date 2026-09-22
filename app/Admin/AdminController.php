<?php
declare(strict_types=1);

namespace WPSiteActivityLog\Admin;

if (!defined('ABSPATH')) exit;

use WPSiteActivityLog\Core\SettingsManager;
use WPSiteActivityLog\Log\LogQuery;
use WPSiteActivityLog\Log\EventRegistry;

class AdminController
{
    private const VIEWS = [
        'dashboard' => 'dashboard.php',
        'log' => 'activity-log.php',
        'settings' => 'settings.php',
    ];

    private static ?self $instance = null;

    private function __construct() {}

    private function __clone() {}

    public function __wakeup(): void
    {
        throw new \Exception('Cannot unserialize AdminController');
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
        add_action('wp_ajax_wsal_load_view', [$this, 'handle_load_view']);
        add_action('wp_ajax_wsal_save_settings', [$this, 'handle_save_settings']);
        add_action('wp_ajax_wsal_export_csv', [$this, 'handle_export_csv']);
    }

    public function render_page(string $slug): void
    {
        if ($slug !== 'wsal-dashboard') {
            wp_die(esc_html__('Invalid page', 'loghaven-site-logs'));
        }

        $args = [
            'page_title' => __('Activity Log', 'loghaven-site-logs'),
        ];

        include WSAL_PATH . 'templates/admin/partials/header.php';
        include WSAL_PATH . 'templates/admin/partials/footer.php';
    }

    public function handle_load_view(): void
    {
        check_ajax_referer('wsal_spa', '_wpnonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized', 'loghaven-site-logs')], 403);
        }

        $view = sanitize_key(wp_unslash($_POST['view'] ?? ''));
        if (!isset(self::VIEWS[$view])) {
            $view = 'dashboard';
        }

        $template = WSAL_PATH . 'templates/admin/pages/' . self::VIEWS[$view];

        ob_start();
        $args = $this->collect_view_args($view);
        include $template;
        $html = ob_get_clean();

        wp_send_json_success(['html' => $html]);
    }

    public function handle_save_settings(): void
    {
        check_ajax_referer('wsal_spa', '_wpnonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized', 'loghaven-site-logs')], 403);
        }

        $settings = SettingsManager::get_instance();
        $retention = max(1, min(365, (int)($_POST['retention_days'] ?? 90)));
        $enable_report = !empty($_POST['enable_report']) ? 1 : 0;
        $report_period = in_array($_POST['report_period'] ?? 'week', ['day', 'week', 'month'], true) ? sanitize_key($_POST['report_period']) : 'week';

        $settings->set('retention_days', $retention);
        $settings->set('enable_report', $enable_report);
        $settings->set('report_period', $report_period);

        wp_send_json_success(['message' => __('Settings saved successfully', 'loghaven-site-logs')]);
    }

    public function handle_export_csv(): void
    {
        check_ajax_referer('wsal_spa', '_wpnonce');

        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Unauthorized', 'loghaven-site-logs'), 403);
        }

        $query_args = [];
        $search = sanitize_text_field(wp_unslash($_REQUEST['search'] ?? ''));
        if ($search !== '') {
            $query_args['search'] = $search;
        }

        $event_code = $this->sanitize_event_code(wp_unslash($_REQUEST['event_code'] ?? ''));
        if ($event_code !== '') {
            $query_args['event_code'] = $event_code;
        }

        $csv = LogQuery::get_instance()->export_csv($query_args);

        nocache_headers();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="wsal-export-' . gmdate('Y-m-d') . '.csv"');

        echo $csv;
        wp_die();
    }

    private function sanitize_event_code(string $code): string
    {
        $raw = trim($code);
        if ($raw === '') {
            return '';
        }
        $codes = EventRegistry::get_instance()->get_all();
        return isset($codes[$raw]) ? $raw : '';
    }

    private function collect_view_args(string $view): array
    {
        $args = [];

        if ($view === 'log') {
            $args['search'] = sanitize_text_field(wp_unslash($_POST['search'] ?? ''));
            $args['event_code'] = $this->sanitize_event_code(wp_unslash($_POST['event_code'] ?? ''));
            $args['paged'] = max(1, (int)($_POST['paged'] ?? 1));
        }

        if ($view === 'settings') {
            $settings = SettingsManager::get_instance();
            $args['retention_days'] = (int)$settings->get('retention_days', 90);
            $args['enable_report'] = (bool)$settings->get('enable_report', true);
            $args['report_period'] = $settings->get('report_period', 'week');
        }

        return $args;
    }
}
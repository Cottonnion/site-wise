<?php
declare(strict_types=1);

namespace WPSiteActivityLog\Admin;

if (!defined('ABSPATH')) exit;

use WPSiteActivityLog\Core\SettingsManager;
use WPSiteActivityLog\Core\EmailDigestManager;
use WPSiteActivityLog\Core\WebhookNotifier;
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
        add_action('wp_ajax_wsal_test_email', [$this, 'handle_test_email']);
        add_action('wp_ajax_wsal_test_webhook', [$this, 'handle_test_webhook']);
        add_action('wp_ajax_wsal_clear_logs', [$this, 'handle_clear_logs']);
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

        // Core settings
        $retention = max(1, min(365, (int)($_POST['retention_days'] ?? 90)));
        $enable_report = !empty($_POST['enable_report']) ? 1 : 0;
        $report_period = in_array($_POST['report_period'] ?? 'week', ['day', 'week', 'month'], true) ? sanitize_key($_POST['report_period']) : 'week';

        // White label settings
        $agency_name = sanitize_text_field(wp_unslash($_POST['agency_name'] ?? ''));
        $agency_logo_url = esc_url_raw(wp_unslash($_POST['agency_logo_url'] ?? ''));
        $brand_color = sanitize_hex_color(wp_unslash($_POST['brand_color'] ?? '#4f46e5')) ?: '#4f46e5';
        $custom_footer_text = sanitize_text_field(wp_unslash($_POST['custom_footer_text'] ?? ''));

        // Webhook settings
        $webhook_url = esc_url_raw(wp_unslash($_POST['webhook_url'] ?? ''));

        // Email digest settings
        $enable_email_digest = !empty($_POST['enable_email_digest']) ? 1 : 0;
        $digest_email = sanitize_text_field(wp_unslash($_POST['digest_email'] ?? ''));
        $digest_frequency = in_array($_POST['digest_frequency'] ?? 'weekly', ['weekly', 'monthly'], true) ? sanitize_key($_POST['digest_frequency']) : 'weekly';

        $settings->set('retention_days', $retention);
        $settings->set('enable_report', $enable_report);
        $settings->set('report_period', $report_period);
        $settings->set('agency_name', $agency_name);
        $settings->set('agency_logo_url', $agency_logo_url);
        $settings->set('brand_color', $brand_color);
        $settings->set('custom_footer_text', $custom_footer_text);
        $settings->set('webhook_url', $webhook_url);
        $settings->set('enable_email_digest', $enable_email_digest);
        $settings->set('digest_email', $digest_email);
        $settings->set('digest_frequency', $digest_frequency);

        // Reschedule email digest if frequency or status changed
        EmailDigestManager::get_instance()->schedule();

        wp_send_json_success(['message' => __('Settings saved successfully', 'loghaven-site-logs')]);
    }

    public function handle_test_email(): void
    {
        check_ajax_referer('wsal_spa', '_wpnonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized', 'loghaven-site-logs')], 403);
        }

        $recipient = sanitize_email(wp_unslash($_POST['email'] ?? ''));
        if (!is_email($recipient)) {
            wp_send_json_error(['message' => __('Enter a valid recipient email address first.', 'loghaven-site-logs')]);
        }

        $sent = EmailDigestManager::get_instance()->send_test_email($recipient);
        if ($sent) {
            wp_send_json_success(['message' => sprintf(
                /* translators: %s: email address */
                __('Test email sent to %s.', 'loghaven-site-logs'),
                $recipient
            )]);
        }

        wp_send_json_error(['message' => __('Test email failed to send. Check your site mail configuration.', 'loghaven-site-logs')]);
    }

    public function handle_test_webhook(): void
    {
        check_ajax_referer('wsal_spa', '_wpnonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized', 'loghaven-site-logs')], 403);
        }

        $url = esc_url_raw(wp_unslash($_POST['url'] ?? ''));
        $result = WebhookNotifier::get_instance()->send_test($url);

        if (!empty($result['success'])) {
            wp_send_json_success(['message' => $result['message']]);
        }

        wp_send_json_error(['message' => $result['message'] ?? __('Test webhook failed.', 'loghaven-site-logs')]);
    }

    public function handle_clear_logs(): void
    {
        check_ajax_referer('wsal_spa', '_wpnonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized', 'loghaven-site-logs')], 403);
        }

        $scope = sanitize_key(wp_unslash($_POST['scope'] ?? 'all'));
        $days = max(1, min(3650, (int)($_POST['days'] ?? 30)));
        $dry_run = !empty($_POST['dry_run']);

        $older_than = $scope === 'older' ? $days : null;

        $query = LogQuery::get_instance();

        if ($dry_run) {
            wp_send_json_success(['count' => $query->count_entries($older_than)]);
        }

        $deleted = $query->clear($older_than);

        if ($deleted > 0) {
            wp_send_json_success([
                'count' => $deleted,
                'message' => sprintf(
                    /* translators: %d: number of log entries */
                    _n('%1$d log entry cleared.', '%1$d log entries cleared.', $deleted, 'loghaven-site-logs'),
                    $deleted
                ),
            ]);
        }

        wp_send_json_success([
            'count' => 0,
            'message' => __('No log entries matched the selected criteria.', 'loghaven-site-logs'),
        ]);
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
        header('Content-Disposition: attachment; filename="loghaven-export-' . gmdate('Y-m-d') . '.csv"');

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
            $args['report_period'] = (string)$settings->get('report_period', 'week');
            $args['agency_name'] = (string)$settings->get('agency_name', '');
            $args['agency_logo_url'] = (string)$settings->get('agency_logo_url', '');
            $args['brand_color'] = (string)$settings->get('brand_color', '#4f46e5');
            $args['custom_footer_text'] = (string)$settings->get('custom_footer_text', '');
            $args['webhook_url'] = (string)$settings->get('webhook_url', '');
            $args['enable_email_digest'] = (bool)$settings->get('enable_email_digest', false);
            $args['digest_email'] = (string)$settings->get('digest_email', '');
            $args['digest_frequency'] = (string)$settings->get('digest_frequency', 'weekly');
        }

        return $args;
    }
}

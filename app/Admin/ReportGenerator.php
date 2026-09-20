<?php
declare(strict_types=1);

namespace WPSiteActivityLog\Admin;

if (!defined('ABSPATH')) exit;

use WPSiteActivityLog\Log\LogQuery;
use WPSiteActivityLog\Core\SettingsManager;

class ReportGenerator
{
    private static ?self $instance = null;
    private LogQuery $query;

    private function __construct()
    {
        $this->query = LogQuery::get_instance();
    }

    private function __clone() {}

    public function __wakeup(): void
    {
        throw new \Exception('Cannot unserialize ReportGenerator');
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
        add_action('template_redirect', [$this, 'maybe_render_report']);
    }

    public function generate(array $options = []): array
    {
        $period = $options['period'] ?? 'week';
        $date_from = $this->get_period_start($period);

        $logs = $this->query->get_logs([
            'date_from' => $date_from,
            'per_page' => 100,
        ]);

        $by_type = [];
        $recent_events = [];
        $top_users = [];
        $user_counts = [];

        foreach ($logs as $log) {
            if (!isset($by_type[$log->object_type])) {
                $by_type[$log->object_type] = 0;
            }
            $by_type[$log->object_type]++;

            if (count($recent_events) < 10) {
                $recent_events[] = [
                    'message' => $log->message,
                    'user' => $log->user_name,
                    'time' => $log->created_at,
                ];
            }

            if (!empty($log->user_name)) {
                $user_counts[$log->user_name] = ($user_counts[$log->user_name] ?? 0) + 1;
            }
        }

        arsort($user_counts);
        $top_users = array_slice($user_counts, 0, 5, true);

        return [
            'period' => $period,
            'generated_at' => current_time('Y-m-d H:i:s'),
            'site_name' => get_bloginfo('name'),
            'site_url' => get_site_url(),
            'total_events' => count($logs),
            'by_type' => $by_type,
            'recent_events' => $recent_events,
            'top_users' => $top_users,
        ];
    }

    public function get_report_url(): string
    {
        $token = wp_generate_password(32, false);
        set_transient('wsal_report_token_' . $token, time(), 86400);

        $period = SettingsManager::get_instance()->get('report_period', 'week');
        return add_query_arg([
            'wsal_report' => '1',
            'period' => $period,
            'token' => $token,
        ], home_url());
    }

    public function maybe_render_report(): void
    {
        if (!isset($_GET['wsal_report']) || !isset($_GET['token'])) {
            return;
        }

        if (!(bool)SettingsManager::get_instance()->get('enable_report', true)) {
            wp_die(esc_html__('Reports are disabled on this site', 'site-wise'), 403);
        }

        $token = sanitize_text_field($_GET['token']);
        if (!get_transient('wsal_report_token_' . $token)) {
            wp_die(esc_html__('Invalid or expired token', 'site-wise'), 403);
        }

        $period = sanitize_key($_GET['period'] ?? 'week');

        $report = $this->generate(['period' => $period]);
        $analysis = ReportAnalyzer::get_instance()->analyze(['period' => $period]);
        $args = ['report' => $report, 'analysis' => $analysis];

        $template = WSAL_PATH . 'templates/frontend/partials/report-public.php';
        if (file_exists($template)) {
            extract($args);
            include $template;
        } else {
            wp_die(esc_html__('Report template not found', 'site-wise'));
        }

        exit;
    }

    public function period_start(string $period): string
    {
        return $this->get_period_start($period);
    }

    public function period_label(string $period): string
    {
        return match ($period) {
            'day' => __('Today', 'site-wise'),
            'week' => __('Last 7 days', 'site-wise'),
            'month' => __('Last 30 days', 'site-wise'),
            default => __('Reporting period', 'site-wise'),
        };
    }

    private function get_period_start(string $period): string
    {
        $now = current_time('Y-m-d H:i:s');
        return match ($period) {
            'day' => date('Y-m-d 00:00:00', current_time('U')),
            'week' => date('Y-m-d H:i:s', current_time('U') - 604800),
            'month' => date('Y-m-d 00:00:00', strtotime('first day of this month', current_time('U'))),
            default => date('Y-m-d H:i:s', current_time('U') - 604800),
        };
    }
}

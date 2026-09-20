<?php
declare(strict_types=1);

namespace WPSiteActivityLog\Admin;

if (!defined('ABSPATH')) exit;

use WPSiteActivityLog\Log\LogQuery;

class ReportAnalyzer
{
    private const CRITICAL_OPTIONS = [
        'home',
        'siteurl',
        'admin_email',
        'users_can_register',
        'default_role',
        'permalink_structure',
    ];

    private const LOGIN_BURST_THRESHOLD = 5;

    private static ?self $instance = null;
    private LogQuery $query;

    private function __construct()
    {
        $this->query = LogQuery::get_instance();
    }

    private function __clone() {}

    public function __wakeup(): void
    {
        throw new \Exception('Cannot unserialize ReportAnalyzer');
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
    }

    public function analyze(array $options = []): array
    {
        $period = $options['period'] ?? 'week';
        $generator = ReportGenerator::get_instance();
        $date_from = $generator->period_start($period);

        $logs = $this->query->get_logs([
            'date_from' => $date_from,
            'per_page' => 500,
        ]);

        $counts = $this->empty_counts();
        $failed_by_ip = [];
        $admin_grants = [];
        $drift = [];
        $deleted_plugins = [];
        $deleted_users = [];

        foreach ($logs as $log) {
            $code = $log->event_code;

            if (isset($counts[$code])) {
                $counts[$code]++;
            }

            if ($code === 'user.login_failed') {
                $key = $log->ip_address ?: 'unknown';
                $failed_by_ip[$key] = ($failed_by_ip[$key] ?? 0) + 1;
            }

            if ($code === 'user.role_changed') {
                $meta = json_decode((string)$log->meta, true) ?: [];
                if (($meta['to_role'] ?? '') === 'administrator') {
                    $admin_grants[] = $log->object_name;
                }
            }

            if ($code === 'settings.updated') {
                $meta = json_decode((string)$log->meta, true) ?: [];
                $option = $meta['option_name'] ?? '';
                if (in_array($option, self::CRITICAL_OPTIONS, true)) {
                    $drift[] = [
                        'option' => $option,
                        'old' => (string)($meta['old_value'] ?? ''),
                        'new' => (string)($meta['new_value'] ?? ''),
                        'user' => $log->user_name,
                        'time' => $log->created_at,
                    ];
                }
            }

            if ($code === 'plugin.deleted') {
                $deleted_plugins[] = $log->object_name;
            }

            if ($code === 'user.deleted') {
                $deleted_users[] = $log->object_name;
            }
        }

        return [
            'period' => $period,
            'period_label' => $generator->period_label($period),
            'date_from' => $date_from,
            'total_events' => count($logs),
            'counts' => $counts,
            'narrative' => $this->build_narrative($counts),
            'attention' => $this->build_attention($failed_by_ip, $admin_grants, $deleted_plugins, $deleted_users, $counts),
            'anomalies' => ['failed_by_ip' => $failed_by_ip],
            'drift' => $drift,
        ];
    }

    private function empty_counts(): array
    {
        $codes = [
            'post.created', 'post.updated', 'post.deleted', 'post.status_changed',
            'user.login', 'user.login_failed', 'user.registered', 'user.deleted', 'user.role_changed',
            'plugin.installed', 'plugin.updated', 'plugin.activated', 'plugin.deactivated', 'plugin.deleted',
            'theme.switched', 'theme.installed', 'theme.updated', 'theme.deleted', 'core.updated',
            'media.uploaded', 'media.deleted',
            'comment.created', 'comment.spammed', 'comment.deleted',
            'term.created', 'term.deleted',
            'settings.updated',
        ];
        return array_fill_keys($codes, 0);
    }

    private function build_narrative(array $counts): array
    {
        $narrative = [];
        $c = $counts;

        if (($c['post.created'] + $c['post.updated'] + $c['post.status_changed']) > 0) {
            $n = $c['post.created'] + $c['post.updated'] + $c['post.status_changed'];
            $this->say($narrative, $n, '%s post or page was created or edited.', '%s posts or pages were created or edited.');
        }
        $this->say($narrative, $c['post.deleted'], '%s post or page was deleted.', '%s posts or pages were deleted.');
        $this->say($narrative, $c['media.uploaded'], '%s media file was uploaded.', '%s media files were uploaded.');
        $this->say($narrative, $c['media.deleted'], '%s media file was deleted.', '%s media files were deleted.');
        $this->say($narrative, $c['user.login'], '%s person signed in to the site.', '%s people signed in to the site.');
        $this->say($narrative, $c['user.registered'], '%s new user account was created.', '%s new user accounts were created.');
        $this->say($narrative, $c['user.role_changed'], '%s user had their role changed.', '%s users had their roles changed.');
        $this->say($narrative, $c['user.deleted'], '%s user account was deleted.', '%s user accounts were deleted.');
        $this->say($narrative, $c['plugin.installed'], '%s plugin was installed.', '%s plugins were installed.');
        $this->say($narrative, $c['plugin.updated'], '%s plugin was updated.', '%s plugins were updated.');
        $this->say($narrative, $c['plugin.deleted'], '%s plugin was deleted.', '%s plugins were deleted.');
        $this->say($narrative, $c['plugin.activated'] + $c['plugin.deactivated'], '%s plugin was switched on or off.', '%s plugins were switched on or off.');
        $this->say($narrative, $c['theme.switched'], '%s theme change was made.', '%s theme changes were made.');
        $this->say($narrative, $c['theme.installed'] + $c['theme.updated'] + $c['theme.deleted'], '%s other theme change was made.', '%s other theme changes were made.');
        $this->say($narrative, $c['comment.created'], '%s comment was added.', '%s comments were added.');
        $this->say($narrative, $c['comment.spammed'], '%s comment was marked as spam.', '%s comments were marked as spam.');
        $this->say($narrative, $c['term.created'] + $c['term.deleted'], '%s category or tag was changed.', '%s categories or tags were changed.');

        if ($c['core.updated'] > 0) {
            $narrative[] = __('WordPress was updated to a new version.', 'site-wise');
        }

        if ($c['user.login_failed'] > 0) {
            $narrative[] = sprintf(
                _n('%s sign-in attempt failed.', '%s sign-in attempts failed.', $c['user.login_failed'], 'site-wise'),
                number_format_i18n($c['user.login_failed'])
            );
        }

        return $narrative;
    }

    private function say(array &$narrative, int $count, string $singular, string $plural): void
    {
        if ($count <= 0) {
            return;
        }
        $narrative[] = sprintf(
            _n($singular, $plural, $count, 'site-wise'),
            number_format_i18n($count)
        );
    }

    private function build_attention(array $failed_by_ip, array $admin_grants, array $deleted_plugins, array $deleted_users, array $counts): array
    {
        $attention = [];

        foreach ($failed_by_ip as $count) {
            if ($count >= self::LOGIN_BURST_THRESHOLD) {
                $attention[] = [
                    'severity' => 'critical',
                    'text' => sprintf(
                        _n('%d failed sign-in attempt came from a single IP address.', '%d failed sign-in attempts came from a single IP address.', $count, 'site-wise'),
                        $count
                    ),
                ];
            }
        }

        foreach ($admin_grants as $name) {
            $attention[] = ['severity' => 'warning', 'text' => sprintf(__('%s was granted the Administrator role.', 'site-wise'), $name)];
        }

        foreach ($deleted_plugins as $name) {
            $attention[] = ['severity' => 'warning', 'text' => sprintf(__('Plugin deleted: %s', 'site-wise'), $name)];
        }

        foreach ($deleted_users as $name) {
            $attention[] = ['severity' => 'warning', 'text' => sprintf(__('User account deleted: %s', 'site-wise'), $name)];
        }

        if ($attention === [] && $counts['user.login_failed'] > 0) {
            $attention[] = [
                'severity' => 'info',
                'text' => sprintf(__('%d failed sign-in attempt was recorded.', 'site-wise'), $counts['user.login_failed']),
            ];
        }

        return array_slice($attention, 0, 6);
    }
}
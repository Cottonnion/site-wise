<?php
declare(strict_types=1);

namespace WPSiteActivityLog\Core;

if (!defined('ABSPATH')) exit;

use WPSiteActivityLog\Log\ActivityLogger;
use WPSiteActivityLog\Log\EventRegistry;

class WebhookNotifier
{
    private static ?self $instance = null;
    private SettingsManager $settings;

    private function __construct()
    {
        $this->settings = SettingsManager::get_instance();
    }

    private function __clone() {}

    public function __wakeup(): void
    {
        throw new \Exception('Cannot unserialize WebhookNotifier');
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
        add_action('wsal_event_logged', [$this, 'handle_event'], 10, 2);
    }

    public function handle_event(array $data, array $raw_meta): void
    {
        $webhook_url = trim((string)$this->settings->get('webhook_url', ''));
        if ($webhook_url === '' || !filter_var($webhook_url, FILTER_VALIDATE_URL)) {
            return;
        }

        $enabled_events = (array)$this->settings->get('webhook_events', [
            'user.role_changed',
            'user.deleted',
            'plugin.deleted',
            'plugin.deactivated',
            'theme.deleted',
            'core.updated',
            'settings.updated',
        ]);

        $event_code = $data['event_code'] ?? '';
        if (!in_array($event_code, $enabled_events, true)) {
            return;
        }

        $this->dispatch_webhook($webhook_url, $data, $raw_meta);
    }

    public function send_test(string $webhook_url): array
    {
        $webhook_url = trim($webhook_url);
        if ($webhook_url === '' || !filter_var($webhook_url, FILTER_VALIDATE_URL)) {
            return ['success' => false, 'message' => __('Please enter a valid webhook URL first.', 'loghaven-site-logs')];
        }

        $logger = ActivityLogger::get_instance();
        $current_user = wp_get_current_user();

        $data = [
            'event_code' => 'settings.updated',
            'message' => sprintf(__('This is a test alert from %1$s. Your webhook is working correctly.', 'loghaven-site-logs'), WSAL_PLUGIN_NAME),
            'user_name' => $current_user->display_name ?: __('System', 'loghaven-site-logs'),
            'ip_address' => $logger->get_current_user_ip(),
            'object_name' => __('Webhook Test', 'loghaven-site-logs'),
        ];

        $meta = ['event_id' => 0, 'is_test' => true];

        $response = $this->dispatch_webhook($webhook_url, $data, $meta, true);

        if (is_wp_error($response)) {
            return ['success' => false, 'message' => $response->get_error_message()];
        }

        $status = wp_remote_retrieve_response_code($response);
        if ($status >= 200 && $status < 300) {
            return ['success' => true, 'message' => sprintf(
                /* translators: %d: HTTP status code */
                __('Test webhook delivered (HTTP %d).', 'loghaven-site-logs'),
                $status
            )];
        }

        return ['success' => false, 'message' => sprintf(
            /* translators: %d: HTTP status code */
            __('Webhook endpoint responded with HTTP %d. Check the URL and try again.', 'loghaven-site-logs'),
            $status
        )];
    }

    private function dispatch_webhook(string $url, array $data, array $meta, bool $blocking = false): \WP_Error|array
    {
        $event = EventRegistry::get_instance()->get($data['event_code'] ?? '');
        $event_label = $event['label'] ?? ($data['event_code'] ?? 'Activity Alert');
        $site_name = get_bloginfo('name');
        $site_url = home_url();

        $is_discord = str_contains($url, 'discord.com/api/webhooks');
        $is_slack = str_contains($url, 'hooks.slack.com');

        $color = match ($event['severity'] ?? 'info') {
            'critical', 'danger' => 14427686, // #dc2626
            'warning' => 14251782,            // #d97706
            default => 5195493,              // #4f46e5
        };

        if ($is_discord) {
            $payload = [
                'username' => 'Loghaven Alerts',
                'embeds' => [
                    [
                        'title' => sprintf('[%s] %s', $site_name, $event_label),
                        'description' => $data['message'] ?? '',
                        'url' => $site_url,
                        'color' => $color,
                        'fields' => [
                            ['name' => 'User', 'value' => $data['user_name'] ?: 'System / Guest', 'inline' => true],
                            ['name' => 'IP Address', 'value' => $data['ip_address'] ?: '—', 'inline' => true],
                            ['name' => 'Object', 'value' => $data['object_name'] ?: '—', 'inline' => true],
                        ],
                        'footer' => ['text' => WSAL_PLUGIN_NAME . ' • ' . current_time('Y-m-d H:i:s')],
                    ],
                ],
            ];
        } elseif ($is_slack) {
            $payload = [
                'text' => sprintf('*[%s]* %s: %s', $site_name, $event_label, $data['message'] ?? ''),
                'attachments' => [
                    [
                        'color' => match ($event['severity'] ?? 'info') {
                            'critical', 'danger' => '#dc2626',
                            'warning' => '#d97706',
                            default => '#4f46e5',
                        },
                        'fields' => [
                            ['title' => 'User', 'value' => $data['user_name'] ?: 'System / Guest', 'short' => true],
                            ['title' => 'IP Address', 'value' => $data['ip_address'] ?: '—', 'short' => true],
                            ['title' => 'Object', 'value' => $data['object_name'] ?: '—', 'short' => true],
                        ],
                        'footer' => WSAL_PLUGIN_NAME,
                        'ts' => time(),
                    ],
                ],
            ];
        } else {
            // Generic JSON Webhook
            $payload = [
                'site_name' => $site_name,
                'site_url' => $site_url,
                'event_code' => $data['event_code'] ?? '',
                'event_label' => $event_label,
                'severity' => $event['severity'] ?? 'info',
                'message' => $data['message'] ?? '',
                'object_name' => $data['object_name'] ?? '',
                'user_name' => $data['user_name'] ?? '',
                'ip_address' => $data['ip_address'] ?? '',
                'meta' => $meta,
                'timestamp' => current_time('mysql'),
            ];
        }

        return wp_remote_post($url, [
            'method' => 'POST',
            'timeout' => 6,
            'blocking' => $blocking,
            'headers' => ['Content-Type' => 'application/json'],
            'body' => wp_json_encode($payload),
        ]);
    }
}

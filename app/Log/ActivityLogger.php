<?php
declare(strict_types=1);

namespace WPSiteActivityLog\Log;

if (!defined('ABSPATH')) exit;

use WPSiteActivityLog\Core\DatabaseManager;

class ActivityLogger
{
    private static ?self $instance = null;
    private DatabaseManager $db;
    private EventRegistry $registry;

    private function __construct()
    {
        $this->db = DatabaseManager::get_instance();
        $this->registry = EventRegistry::get_instance();
    }

    private function __clone() {}

    public function __wakeup(): void
    {
        throw new \Exception('Cannot unserialize ActivityLogger');
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

    public function log(string $event_code, string $object_name, array $meta = [], ?int $object_id = null): void
    {
        $event = $this->registry->get($event_code);
        if ($event === null) {
            return;
        }

        $user = wp_get_current_user();
        $ip_address = $this->get_current_user_ip();
        $user_agent = sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? '');

        $data = [
            'event_code' => $event_code,
            'object_type' => $event['object_type'],
            'object_name' => $object_name,
            'object_id' => $object_id,
            'user_id' => $user->ID ?: 0,
            'user_name' => $user->display_name ?? '',
            'user_role' => !empty($user->roles) ? implode(',', $user->roles) : '',
            'ip_address' => $ip_address,
            'user_agent' => $user_agent,
            'message' => $this->build_message($event_code, $object_name),
            'meta' => $this->sanitize_meta($meta),
        ];

        $this->db->insert($data);
    }

    private function build_message(string $event_code, string $object_name): string
    {
        $messages = [
            'post.created' => sprintf(__('New post created: %s', 'loghaven-site-logs'), $object_name),
            'post.updated' => sprintf(__('Post updated: %s', 'loghaven-site-logs'), $object_name),
            'post.deleted' => sprintf(__('Post deleted: %s', 'loghaven-site-logs'), $object_name),
            'post.status_changed' => sprintf(__('Post status changed: %s', 'loghaven-site-logs'), $object_name),
            'user.login' => sprintf(__('User logged in: %s', 'loghaven-site-logs'), $object_name),
            'user.logout' => sprintf(__('User logged out: %s', 'loghaven-site-logs'), $object_name),
            'user.login_failed' => sprintf(__('Login failed for: %s', 'loghaven-site-logs'), $object_name),
            'user.registered' => sprintf(__('New user registered: %s', 'loghaven-site-logs'), $object_name),
            'user.deleted' => sprintf(__('User deleted: %s', 'loghaven-site-logs'), $object_name),
            'user.role_changed' => sprintf(__('Role changed for user: %s', 'loghaven-site-logs'), $object_name),
            'user.profile_updated' => sprintf(__('Profile updated: %s', 'loghaven-site-logs'), $object_name),
            'plugin.activated' => sprintf(__('Plugin activated: %s', 'loghaven-site-logs'), $object_name),
            'plugin.deactivated' => sprintf(__('Plugin deactivated: %s', 'loghaven-site-logs'), $object_name),
            'plugin.updated' => sprintf(__('Plugin updated: %s', 'loghaven-site-logs'), $object_name),
            'plugin.installed' => sprintf(__('Plugin installed: %s', 'loghaven-site-logs'), $object_name),
            'plugin.deleted' => sprintf(__('Plugin deleted: %s', 'loghaven-site-logs'), $object_name),
            'theme.switched' => sprintf(__('Theme switched to: %s', 'loghaven-site-logs'), $object_name),
            'theme.installed' => sprintf(__('Theme installed: %s', 'loghaven-site-logs'), $object_name),
            'theme.updated' => sprintf(__('Theme updated: %s', 'loghaven-site-logs'), $object_name),
            'theme.deleted' => sprintf(__('Theme deleted: %s', 'loghaven-site-logs'), $object_name),
            'core.updated' => sprintf(__('WordPress updated to version %s', 'loghaven-site-logs'), $object_name),
            'media.uploaded' => sprintf(__('Uploaded: %s', 'loghaven-site-logs'), $object_name),
            'media.deleted' => sprintf(__('Deleted media: %s', 'loghaven-site-logs'), $object_name),
            'comment.created' => sprintf(__('Comment from %s', 'loghaven-site-logs'), $object_name),
            'comment.spammed' => sprintf(__('Comment marked as spam: %s', 'loghaven-site-logs'), $object_name),
            'comment.deleted' => sprintf(__('Comment deleted: %s', 'loghaven-site-logs'), $object_name),
            'term.created' => sprintf(__('Taxonomy term created: %s', 'loghaven-site-logs'), $object_name),
            'term.deleted' => sprintf(__('Taxonomy term deleted: %s', 'loghaven-site-logs'), $object_name),
            'settings.updated' => sprintf(__('Settings updated', 'loghaven-site-logs')),
        ];

        return $messages[$event_code] ?? sprintf(__('%s action performed', 'loghaven-site-logs'), $object_name);
    }

    public function get_current_user_ip(): string
    {
        if (!empty($_SERVER['CF-CONNECTING-IP'])) {
            return sanitize_text_field($_SERVER['CF-CONNECTING-IP']);
        }

        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = array_map('trim', explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
            return sanitize_text_field(end($ips));
        }

        return sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    }

    public function sanitize_meta(array $meta): string
    {
        $sanitized = [];
        foreach ($meta as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitize_meta($value);
            } else {
                $sanitized[$key] = is_string($value) ? sanitize_text_field($value) : $value;
            }
        }
        return wp_json_encode($sanitized, JSON_UNESCAPED_UNICODE);
    }
}

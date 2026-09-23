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
            'message' => $this->build_message($event_code, $object_name, $meta),
            'meta' => $this->sanitize_meta($meta),
        ];

        $this->db->insert($data);

        /**
         * Fires immediately after an activity event has been logged to the database.
         *
         * @param array $data Stored event data.
         * @param array $meta Raw sanitized metadata array.
         */
        do_action('wsal_event_logged', $data, $meta);
    }

    private function build_message(string $event_code, string $object_name, array $meta = []): string
    {
        $messages = [
            'post.created' => sprintf(__('New content created: %s', 'loghaven-site-logs'), $object_name),
            'post.updated' => sprintf(__('Content updated: %s', 'loghaven-site-logs'), $object_name),
            'post.deleted' => sprintf(__('Content deleted permanently: %s', 'loghaven-site-logs'), $object_name),
            'post.trashed' => sprintf(__('Moved to trash: %s', 'loghaven-site-logs'), $object_name),
            'post.restored' => sprintf(__('Restored from trash: %s', 'loghaven-site-logs'), $object_name),
            'post.status_changed' => sprintf(__('Status changed: %s', 'loghaven-site-logs'), $object_name),

            'user.login' => sprintf(__('User logged in: %s', 'loghaven-site-logs'), $object_name),
            'user.logout' => sprintf(__('User logged out: %s', 'loghaven-site-logs'), $object_name),
            'user.login_failed' => sprintf(__('Login failed for: %s', 'loghaven-site-logs'), $object_name),
            'user.registered' => sprintf(__('New user registered: %s', 'loghaven-site-logs'), $object_name),
            'user.deleted' => sprintf(__('User deleted: %s', 'loghaven-site-logs'), $object_name),
            'user.role_changed' => sprintf(__('Role changed for: %s', 'loghaven-site-logs'), $object_name),
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
            'settings.updated' => sprintf(__('Settings updated: %s', 'loghaven-site-logs'), $object_name),

            // WooCommerce
            'wc.order_status' => sprintf(
                __('%1$s status changed: %2$s &rarr; %3$s', 'loghaven-site-logs'),
                $object_name,
                $meta['old_status'] ?? 'unknown',
                $meta['new_status'] ?? 'unknown'
            ),
            'wc.product_created' => sprintf(__('WooCommerce product created: %s', 'loghaven-site-logs'), $object_name),
            'wc.product_updated' => sprintf(__('WooCommerce product updated: %s', 'loghaven-site-logs'), $object_name),
            'wc.product_deleted' => sprintf(__('WooCommerce product deleted: %s', 'loghaven-site-logs'), $object_name),
            'wc.stock_changed' => sprintf(__('Stock quantity changed for: %s', 'loghaven-site-logs'), $object_name),
            'wc.coupon_created' => sprintf(__('Coupon created: %s', 'loghaven-site-logs'), $object_name),
            'wc.coupon_deleted' => sprintf(__('Coupon deleted: %s', 'loghaven-site-logs'), $object_name),

            // Elementor
            'elementor.post_edited' => sprintf(__('Edited with Elementor: %s', 'loghaven-site-logs'), $object_name),
            'elementor.template_created' => sprintf(__('Elementor template created: %s', 'loghaven-site-logs'), $object_name),
            'elementor.template_updated' => sprintf(__('Elementor template updated: %s', 'loghaven-site-logs'), $object_name),
            'elementor.template_deleted' => sprintf(__('Elementor template deleted: %s', 'loghaven-site-logs'), $object_name),
            'elementor.settings_updated' => sprintf(__('Elementor settings updated: %s', 'loghaven-site-logs'), $object_name),
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
                $sanitized[$key] = $this->sanitize_meta_array($value);
            } else {
                $sanitized[$key] = is_string($value) ? sanitize_text_field($value) : $value;
            }
        }
        return wp_json_encode($sanitized, JSON_UNESCAPED_UNICODE);
    }

    private function sanitize_meta_array(array $array): array
    {
        $clean = [];
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                $clean[$k] = $this->sanitize_meta_array($v);
            } else {
                $clean[$k] = is_string($v) ? sanitize_text_field($v) : $v;
            }
        }
        return $clean;
    }
}

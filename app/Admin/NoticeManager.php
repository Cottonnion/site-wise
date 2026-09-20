<?php
declare(strict_types=1);

namespace WPSiteActivityLog\Admin;

if (!defined('ABSPATH')) exit;

class NoticeManager
{
    private static ?self $instance = null;

    private function __construct() {}

    private function __clone() {}

    public function __wakeup(): void
    {
        throw new \Exception('Cannot unserialize NoticeManager');
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
        add_action('admin_notices', [$this, 'render_notices']);
    }

    public function add(string $message, string $type = 'success', bool $dismissible = true): void
    {
        $user_id = get_current_user_id();
        $notices = get_user_meta($user_id, 'wsal_notices', true);

        if (!is_array($notices)) {
            $notices = [];
        }

        $notices[] = [
            'message' => $message,
            'type' => $type,
            'dismissible' => $dismissible,
            'id' => uniqid('wsal_'),
        ];

        update_user_meta($user_id, 'wsal_notices', $notices);
    }

    public function render_notices(): void
    {
        $user_id = get_current_user_id();
        $notices = get_user_meta($user_id, 'wsal_notices', true);

        if (!is_array($notices) || empty($notices)) {
            return;
        }

        foreach ($notices as $notice) {
            $class = 'notice notice-' . esc_attr($notice['type']);
            if ($notice['dismissible']) {
                $class .= ' is-dismissible';
            }

            echo '<div class="' . esc_attr($class) . '">';
            echo '<p>' . wp_kses_post($notice['message']) . '</p>';
            echo '</div>';
        }

        delete_user_meta($user_id, 'wsal_notices');
    }
}

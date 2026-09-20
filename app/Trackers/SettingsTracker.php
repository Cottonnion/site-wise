<?php
declare(strict_types=1);

namespace WPSiteActivityLog\Trackers;

if (!defined('ABSPATH')) exit;

use WPSiteActivityLog\Log\ActivityLogger;

class SettingsTracker
{
    private static ?self $instance = null;
    private ActivityLogger $logger;

    private array $tracked_options = [
        'blogname',
        'blogdescription',
        'admin_email',
        'users_can_register',
        'default_role',
        'permalink_structure',
        'home',
        'siteurl',
        'timezone_string',
        'date_format',
        'time_format',
    ];

    private function __construct()
    {
        $this->logger = ActivityLogger::get_instance();
    }

    private function __clone() {}

    public function __wakeup(): void
    {
        throw new \Exception('Cannot unserialize SettingsTracker');
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
        add_action('updated_option', [$this, 'track_option_update'], 10, 3);
    }

    public function track_option_update(string $option, mixed $old_value, mixed $new_value): void
    {
        if (!in_array($option, $this->tracked_options, true)) {
            return;
        }

        if (str_starts_with($option, '_transient_') || str_starts_with($option, '_site_transient_')) {
            return;
        }

        if (str_starts_with($option, 'cron') || str_starts_with($option, '_')) {
            return;
        }

        $old_value_str = is_scalar($old_value) ? (string)$old_value : wp_json_encode($old_value);
        $new_value_str = is_scalar($new_value) ? (string)$new_value : wp_json_encode($new_value);

        $old_value_truncated = substr($old_value_str, 0, 255);
        $new_value_truncated = substr($new_value_str, 0, 255);

        $this->logger->log('settings.updated', $option, [
            'option_name' => $option,
            'old_value' => $old_value_truncated,
            'new_value' => $new_value_truncated,
        ]);
    }
}

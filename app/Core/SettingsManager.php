<?php
declare(strict_types=1);

namespace WPSiteActivityLog\Core;

if (!defined('ABSPATH')) exit;

class SettingsManager
{
    private static ?self $instance = null;
    private const OPTION_NAME = 'wsal_settings';

    private function __construct() {}

    private function __clone() {}

    public function __wakeup(): void
    {
        throw new \Exception('Cannot unserialize SettingsManager');
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

    public function get(string $key, mixed $default = null): mixed
    {
        $settings = $this->get_all();
        return $settings[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $settings = $this->get_all();
        $settings[$key] = $value;
        $this->save($settings);
    }

    public function get_all(): array
    {
        $settings = get_option(self::OPTION_NAME, []);
        if (!is_array($settings)) {
            $settings = [];
        }
        return $settings;
    }

    public function save(array $data): void
    {
        update_option(self::OPTION_NAME, $data, true);
    }
}

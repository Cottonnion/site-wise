<?php
declare(strict_types=1);

namespace WPSiteActivityLog\Trackers;

if (!defined('ABSPATH')) exit;

use WPSiteActivityLog\Log\ActivityLogger;

class ThemeTracker
{
    private static ?self $instance = null;
    private ActivityLogger $logger;

    private function __construct()
    {
        $this->logger = ActivityLogger::get_instance();
    }

    private function __clone() {}

    public function __wakeup(): void
    {
        throw new \Exception('Cannot unserialize ThemeTracker');
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
        add_action('switch_theme', [$this, 'track_switched'], 10, 2);
        add_action('upgrader_process_complete', [$this, 'track_upgrade'], 10, 2);
        add_action('delete_theme', [$this, 'track_deleted']);
    }

    public function track_switched(string $new_name, \WP_Theme $new_theme): void
    {
        $this->logger->log('theme.switched', $new_name, [
            'theme_stylesheet' => $new_theme->get_stylesheet(),
            'theme_version' => (string)$new_theme->get('Version'),
        ]);
    }

    public function track_upgrade(\WP_Upgrader $upgrader, array $options): void
    {
        if (($options['type'] ?? '') !== 'theme') {
            return;
        }

        $action = $options['action'] ?? '';
        $themes = $options['themes'] ?? [];

        if (empty($themes)) {
            return;
        }

        foreach ($themes as $stylesheet) {
            $theme = \wp_get_theme($stylesheet);
            $name = (string)$theme->get('Name');
            if ($name === '') {
                continue;
            }

            if ($action === 'install') {
                $this->logger->log('theme.installed', $name, [
                    'theme_stylesheet' => $stylesheet,
                    'theme_version' => (string)$theme->get('Version'),
                ]);
            } elseif ($action === 'update') {
                $this->logger->log('theme.updated', $name, [
                    'theme_stylesheet' => $stylesheet,
                    'theme_version' => (string)$theme->get('Version'),
                ]);
            }
        }
    }

    public function track_deleted(string $stylesheet): void
    {
        $theme = \wp_get_theme($stylesheet);
        $name = (string)$theme->get('Name') !== '' ? (string)$theme->get('Name') : $stylesheet;

        $this->logger->log('theme.deleted', $name, [
            'theme_stylesheet' => $stylesheet,
        ]);
    }
}
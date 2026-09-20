<?php
declare(strict_types=1);

namespace WPSiteActivityLog\Trackers;

if (!defined('ABSPATH')) exit;

use WPSiteActivityLog\Log\ActivityLogger;

class PluginTracker
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
        throw new \Exception('Cannot unserialize PluginTracker');
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
        add_action('activated_plugin', [$this, 'track_activated']);
        add_action('deactivated_plugin', [$this, 'track_deactivated']);
        add_action('upgrader_process_complete', [$this, 'track_upgrade'], 10, 2);
        add_action('delete_plugin', [$this, 'track_deleted']);
    }

    public function track_activated(string $plugin_file): void
    {
        $plugin_data = $this->get_plugin_data($plugin_file);
        $this->logger->log('plugin.activated', $plugin_data['Name'] ?? $plugin_file, [
            'plugin_file' => $plugin_file,
            'plugin_name' => $plugin_data['Name'] ?? '',
            'version' => $plugin_data['Version'] ?? '',
        ]);
    }

    public function track_deactivated(string $plugin_file): void
    {
        $plugin_data = $this->get_plugin_data($plugin_file);
        $this->logger->log('plugin.deactivated', $plugin_data['Name'] ?? $plugin_file, [
            'plugin_file' => $plugin_file,
            'plugin_name' => $plugin_data['Name'] ?? '',
            'version' => $plugin_data['Version'] ?? '',
        ]);
    }

    public function track_upgrade(\WP_Upgrader $upgrader, array $options): void
    {
        if ($options['type'] !== 'plugin') {
            return;
        }

        $action = $options['action'] ?? '';
        $plugins = $options['plugins'] ?? [];

        if (empty($plugins)) {
            return;
        }

        foreach ($plugins as $plugin_file) {
            $plugin_data = $this->get_plugin_data($plugin_file);

            if ($action === 'install') {
                $this->logger->log('plugin.installed', $plugin_data['Name'] ?? $plugin_file, [
                    'plugin_file' => $plugin_file,
                    'plugin_name' => $plugin_data['Name'] ?? '',
                    'version' => $plugin_data['Version'] ?? '',
                ]);
            } elseif ($action === 'update') {
                $this->logger->log('plugin.updated', $plugin_data['Name'] ?? $plugin_file, [
                    'plugin_file' => $plugin_file,
                    'plugin_name' => $plugin_data['Name'] ?? '',
                    'version' => $plugin_data['Version'] ?? '',
                ]);
            }
        }
    }

    public function track_deleted(string $plugin_file): void
    {
        $plugin_data = $this->get_plugin_data($plugin_file);
        $this->logger->log('plugin.deleted', $plugin_data['Name'] ?? $plugin_file, [
            'plugin_file' => $plugin_file,
            'plugin_name' => $plugin_data['Name'] ?? '',
            'version' => $plugin_data['Version'] ?? '',
        ]);
    }

    private function get_plugin_data(string $plugin_file): array
    {
        $plugin_path = trailingslashit(WP_PLUGIN_DIR) . $plugin_file;
        if (!file_exists($plugin_path)) {
            return [];
        }
        return get_plugin_data($plugin_path, false, false);
    }
}

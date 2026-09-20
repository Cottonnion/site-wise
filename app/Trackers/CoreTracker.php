<?php
declare(strict_types=1);

namespace WPSiteActivityLog\Trackers;

if (!defined('ABSPATH')) exit;

use WPSiteActivityLog\Log\ActivityLogger;

class CoreTracker
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
        throw new \Exception('Cannot unserialize CoreTracker');
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
        add_action('upgrader_process_complete', [$this, 'track_upgrade'], 10, 2);
    }

    public function track_upgrade(\WP_Upgrader $upgrader, array $options): void
    {
        if (($options['type'] ?? '') !== 'core' || ($options['action'] ?? '') !== 'update') {
            return;
        }

        $version = get_bloginfo('version');
        if ($version === '') {
            return;
        }

        $this->logger->log('core.updated', $version, [
            'previous_version' => $options['previous'] ?? '',
        ]);
    }
}
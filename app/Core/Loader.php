<?php
declare(strict_types=1);

namespace WPSiteActivityLog\Core;

if (!defined('ABSPATH')) exit;

class Loader
{
    private static ?self $instance = null;
    private array $actions = [];
    private array $filters = [];

    private function __construct() {}

    private function __clone() {}

    public function __wakeup(): void
    {
        throw new \Exception('Cannot unserialize Loader');
    }

    public static function get_instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function add_action(string $hook, callable $callback, int $priority = 10, int $args = 1): void
    {
        $this->actions[] = [
            'hook' => $hook,
            'callback' => $callback,
            'priority' => $priority,
            'args' => $args,
        ];
    }

    public function add_filter(string $hook, callable $callback, int $priority = 10, int $args = 1): void
    {
        $this->filters[] = [
            'hook' => $hook,
            'callback' => $callback,
            'priority' => $priority,
            'args' => $args,
        ];
    }

    public function run(): void
    {
        foreach ($this->actions as $action) {
            add_action(
                $action['hook'],
                $action['callback'],
                $action['priority'],
                $action['args']
            );
        }

        foreach ($this->filters as $filter) {
            add_filter(
                $filter['hook'],
                $filter['callback'],
                $filter['priority'],
                $filter['args']
            );
        }
    }
}

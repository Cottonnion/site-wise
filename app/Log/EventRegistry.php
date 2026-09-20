<?php
declare(strict_types=1);

namespace WPSiteActivityLog\Log;

if (!defined('ABSPATH')) exit;

class EventRegistry
{
    private static ?self $instance = null;
    private array $events = [];

    private function __construct() {}

    private function __clone() {}

    public function __wakeup(): void
    {
        throw new \Exception('Cannot unserialize EventRegistry');
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
        $this->register('post.created', __('Post Created', 'site-wise'), 'post', 'info');
        $this->register('post.updated', __('Post Updated', 'site-wise'), 'post', 'info');
        $this->register('post.deleted', __('Post Deleted', 'site-wise'), 'post', 'warning');
        $this->register('post.status_changed', __('Post Status Changed', 'site-wise'), 'post', 'info');

        $this->register('user.login', __('User Login', 'site-wise'), 'user', 'info');
        $this->register('user.logout', __('User Logout', 'site-wise'), 'user', 'info');
        $this->register('user.login_failed', __('Login Failed', 'site-wise'), 'user', 'warning');
        $this->register('user.registered', __('User Registered', 'site-wise'), 'user', 'info');
        $this->register('user.deleted', __('User Deleted', 'site-wise'), 'user', 'warning');
        $this->register('user.role_changed', __('User Role Changed', 'site-wise'), 'user', 'warning');
        $this->register('user.profile_updated', __('User Profile Updated', 'site-wise'), 'user', 'info');

        $this->register('plugin.activated', __('Plugin Activated', 'site-wise'), 'plugin', 'info');
        $this->register('plugin.deactivated', __('Plugin Deactivated', 'site-wise'), 'plugin', 'info');
        $this->register('plugin.updated', __('Plugin Updated', 'site-wise'), 'plugin', 'info');
        $this->register('plugin.installed', __('Plugin Installed', 'site-wise'), 'plugin', 'info');
        $this->register('plugin.deleted', __('Plugin Deleted', 'site-wise'), 'plugin', 'warning');

        $this->register('theme.switched', __('Theme Switched', 'site-wise'), 'theme', 'info');
        $this->register('theme.installed', __('Theme Installed', 'site-wise'), 'theme', 'info');
        $this->register('theme.updated', __('Theme Updated', 'site-wise'), 'theme', 'info');
        $this->register('theme.deleted', __('Theme Deleted', 'site-wise'), 'theme', 'warning');
        $this->register('core.updated', __('WordPress Core Updated', 'site-wise'), 'core', 'info');

        $this->register('media.uploaded', __('Media Uploaded', 'site-wise'), 'media', 'info');
        $this->register('media.deleted', __('Media Deleted', 'site-wise'), 'media', 'warning');

        $this->register('comment.created', __('Comment Created', 'site-wise'), 'comment', 'info');
        $this->register('comment.spammed', __('Comment Marked Spam', 'site-wise'), 'comment', 'warning');
        $this->register('comment.deleted', __('Comment Deleted', 'site-wise'), 'comment', 'warning');

        $this->register('term.created', __('Taxonomy Term Created', 'site-wise'), 'term', 'info');
        $this->register('term.deleted', __('Taxonomy Term Deleted', 'site-wise'), 'term', 'warning');

        $this->register('settings.updated', __('Settings Updated', 'site-wise'), 'settings', 'info');
    }

    public function register(string $code, string $label, string $object_type, string $severity = 'info'): void
    {
        $this->events[$code] = [
            'code' => $code,
            'label' => $label,
            'object_type' => $object_type,
            'severity' => $severity,
        ];
    }

    public function get(string $code): ?array
    {
        return $this->events[$code] ?? null;
    }

    public function get_all(): array
    {
        return $this->events;
    }
}

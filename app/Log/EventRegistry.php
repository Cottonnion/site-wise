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
        $this->register('post.created', __('Post Created', 'loghaven-site-logs'), 'post', 'info');
        $this->register('post.updated', __('Post Updated', 'loghaven-site-logs'), 'post', 'info');
        $this->register('post.deleted', __('Post Deleted', 'loghaven-site-logs'), 'post', 'warning');
        $this->register('post.status_changed', __('Post Status Changed', 'loghaven-site-logs'), 'post', 'info');

        $this->register('user.login', __('User Login', 'loghaven-site-logs'), 'user', 'info');
        $this->register('user.logout', __('User Logout', 'loghaven-site-logs'), 'user', 'info');
        $this->register('user.login_failed', __('Login Failed', 'loghaven-site-logs'), 'user', 'warning');
        $this->register('user.registered', __('User Registered', 'loghaven-site-logs'), 'user', 'info');
        $this->register('user.deleted', __('User Deleted', 'loghaven-site-logs'), 'user', 'warning');
        $this->register('user.role_changed', __('User Role Changed', 'loghaven-site-logs'), 'user', 'warning');
        $this->register('user.profile_updated', __('User Profile Updated', 'loghaven-site-logs'), 'user', 'info');

        $this->register('plugin.activated', __('Plugin Activated', 'loghaven-site-logs'), 'plugin', 'info');
        $this->register('plugin.deactivated', __('Plugin Deactivated', 'loghaven-site-logs'), 'plugin', 'info');
        $this->register('plugin.updated', __('Plugin Updated', 'loghaven-site-logs'), 'plugin', 'info');
        $this->register('plugin.installed', __('Plugin Installed', 'loghaven-site-logs'), 'plugin', 'info');
        $this->register('plugin.deleted', __('Plugin Deleted', 'loghaven-site-logs'), 'plugin', 'warning');

        $this->register('theme.switched', __('Theme Switched', 'loghaven-site-logs'), 'theme', 'info');
        $this->register('theme.installed', __('Theme Installed', 'loghaven-site-logs'), 'theme', 'info');
        $this->register('theme.updated', __('Theme Updated', 'loghaven-site-logs'), 'theme', 'info');
        $this->register('theme.deleted', __('Theme Deleted', 'loghaven-site-logs'), 'theme', 'warning');
        $this->register('core.updated', __('WordPress Core Updated', 'loghaven-site-logs'), 'core', 'info');

        $this->register('media.uploaded', __('Media Uploaded', 'loghaven-site-logs'), 'media', 'info');
        $this->register('media.deleted', __('Media Deleted', 'loghaven-site-logs'), 'media', 'warning');

        $this->register('comment.created', __('Comment Created', 'loghaven-site-logs'), 'comment', 'info');
        $this->register('comment.spammed', __('Comment Marked Spam', 'loghaven-site-logs'), 'comment', 'warning');
        $this->register('comment.deleted', __('Comment Deleted', 'loghaven-site-logs'), 'comment', 'warning');

        $this->register('term.created', __('Taxonomy Term Created', 'loghaven-site-logs'), 'term', 'info');
        $this->register('term.deleted', __('Taxonomy Term Deleted', 'loghaven-site-logs'), 'term', 'warning');

        $this->register('settings.updated', __('Settings Updated', 'loghaven-site-logs'), 'settings', 'info');
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

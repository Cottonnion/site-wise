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
        $this->register('post.created', __('Post Created', 'syncly-site-reports'), 'post', 'info');
        $this->register('post.updated', __('Post Updated', 'syncly-site-reports'), 'post', 'info');
        $this->register('post.deleted', __('Post Deleted', 'syncly-site-reports'), 'post', 'warning');
        $this->register('post.status_changed', __('Post Status Changed', 'syncly-site-reports'), 'post', 'info');

        $this->register('user.login', __('User Login', 'syncly-site-reports'), 'user', 'info');
        $this->register('user.logout', __('User Logout', 'syncly-site-reports'), 'user', 'info');
        $this->register('user.login_failed', __('Login Failed', 'syncly-site-reports'), 'user', 'warning');
        $this->register('user.registered', __('User Registered', 'syncly-site-reports'), 'user', 'info');
        $this->register('user.deleted', __('User Deleted', 'syncly-site-reports'), 'user', 'warning');
        $this->register('user.role_changed', __('User Role Changed', 'syncly-site-reports'), 'user', 'warning');
        $this->register('user.profile_updated', __('User Profile Updated', 'syncly-site-reports'), 'user', 'info');

        $this->register('plugin.activated', __('Plugin Activated', 'syncly-site-reports'), 'plugin', 'info');
        $this->register('plugin.deactivated', __('Plugin Deactivated', 'syncly-site-reports'), 'plugin', 'info');
        $this->register('plugin.updated', __('Plugin Updated', 'syncly-site-reports'), 'plugin', 'info');
        $this->register('plugin.installed', __('Plugin Installed', 'syncly-site-reports'), 'plugin', 'info');
        $this->register('plugin.deleted', __('Plugin Deleted', 'syncly-site-reports'), 'plugin', 'warning');

        $this->register('theme.switched', __('Theme Switched', 'syncly-site-reports'), 'theme', 'info');
        $this->register('theme.installed', __('Theme Installed', 'syncly-site-reports'), 'theme', 'info');
        $this->register('theme.updated', __('Theme Updated', 'syncly-site-reports'), 'theme', 'info');
        $this->register('theme.deleted', __('Theme Deleted', 'syncly-site-reports'), 'theme', 'warning');
        $this->register('core.updated', __('WordPress Core Updated', 'syncly-site-reports'), 'core', 'info');

        $this->register('media.uploaded', __('Media Uploaded', 'syncly-site-reports'), 'media', 'info');
        $this->register('media.deleted', __('Media Deleted', 'syncly-site-reports'), 'media', 'warning');

        $this->register('comment.created', __('Comment Created', 'syncly-site-reports'), 'comment', 'info');
        $this->register('comment.spammed', __('Comment Marked Spam', 'syncly-site-reports'), 'comment', 'warning');
        $this->register('comment.deleted', __('Comment Deleted', 'syncly-site-reports'), 'comment', 'warning');

        $this->register('term.created', __('Taxonomy Term Created', 'syncly-site-reports'), 'term', 'info');
        $this->register('term.deleted', __('Taxonomy Term Deleted', 'syncly-site-reports'), 'term', 'warning');

        $this->register('settings.updated', __('Settings Updated', 'syncly-site-reports'), 'settings', 'info');
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

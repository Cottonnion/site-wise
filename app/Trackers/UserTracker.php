<?php
declare(strict_types=1);

namespace WPSiteActivityLog\Trackers;

if (!defined('ABSPATH')) exit;

use WPSiteActivityLog\Log\ActivityLogger;

class UserTracker
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
        throw new \Exception('Cannot unserialize UserTracker');
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
        add_action('wp_login', [$this, 'track_login'], 10, 2);
        add_action('wp_logout', [$this, 'track_logout']);
        add_action('wp_login_failed', [$this, 'track_login_failed']);
        add_action('user_register', [$this, 'track_user_register']);
        add_action('deleted_user', [$this, 'track_user_deleted']);
        add_action('set_user_role', [$this, 'track_role_change'], 10, 3);
        add_action('profile_update', [$this, 'track_profile_update'], 10, 2);
    }

    public function track_login(string $user_login, \WP_User $user): void
    {
        $user_role = !empty($user->roles) ? reset($user->roles) : 'unknown';
        $this->logger->log('user.login', $user->display_name ?: $user_login, [
            'user_email' => $user->user_email,
            'user_role' => $user_role,
        ], $user->ID);
    }

    public function track_logout(): void
    {
        $user = wp_get_current_user();
        if ($user && $user->ID) {
            $user_role = !empty($user->roles) ? reset($user->roles) : 'unknown';
            $this->logger->log('user.logout', $user->display_name ?: $user->user_login, [
                'user_email' => $user->user_email,
                'user_role' => $user_role,
            ], $user->ID);
        }
    }

    public function track_login_failed(string $username): void
    {
        $user = get_user_by('login', $username);
        $email = $user ? $user->user_email : '';

        $this->logger->log('user.login_failed', $username, [
            'user_email' => $email,
        ]);
    }

    public function track_user_register(int $user_id): void
    {
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return;
        }

        $user_role = !empty($user->roles) ? reset($user->roles) : 'unknown';
        $this->logger->log('user.registered', $user->display_name ?: $user->user_login, [
            'user_email' => $user->user_email,
            'user_role' => $user_role,
        ], $user_id);
    }

    public function track_user_deleted(int $user_id, ?int $reassign = null, ?\WP_User $user = null): void
    {
        if ($user === null) {
            $user = get_user_by('id', $user_id);
        }

        if (!$user) {
            return;
        }

        $user_role = !empty($user->roles) ? reset($user->roles) : 'unknown';
        $this->logger->log('user.deleted', $user->display_name ?: $user->user_login, [
            'user_email' => $user->user_email,
            'user_role' => $user_role,
            'user_id' => $user_id,
        ], $user_id);
    }

    public function track_role_change(int $user_id, string $role, array $old_roles): void
    {
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return;
        }

        $old_role = $old_roles !== [] ? reset($old_roles) : '';

        $this->logger->log('user.role_changed', $user->display_name ?: $user->user_login, [
            'user_id' => $user_id,
            'from_role' => $old_role ?: __('(none)', 'loghaven-site-logs'),
            'to_role' => $role,
        ], $user_id);
    }

    public function track_profile_update(int $user_id, \WP_User $old_user_data): void
    {
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return;
        }

        $this->logger->log('user.profile_updated', $user->display_name ?: $user->user_login, [
            'user_id' => $user_id,
            'user_login' => $user->user_login,
        ], $user_id);
    }
}

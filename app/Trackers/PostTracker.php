<?php
declare(strict_types=1);

namespace WPSiteActivityLog\Trackers;

if (!defined('ABSPATH')) exit;

use WPSiteActivityLog\Log\ActivityLogger;

class PostTracker
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
        throw new \Exception('Cannot unserialize PostTracker');
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
        add_action('save_post', [$this, 'track_save_post'], 10, 2);
        add_action('before_delete_post', [$this, 'track_delete_post'], 10, 2);
        add_action('transition_post_status', [$this, 'track_status_change'], 10, 3);
    }

    public function track_save_post(int $post_id, \WP_Post $post): void
    {
        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
            return;
        }

        $excluded_types = ['nav_menu_item', 'attachment'];
        if (in_array($post->post_type, $excluded_types, true)) {
            return;
        }

        $previous_post = get_post_meta($post_id, '_previous_post', true);

        if (empty($previous_post)) {
            $this->logger->log('post.created', $post->post_title, [
                'post_type' => $post->post_type,
                'post_id' => $post_id,
            ], $post_id);
        } else {
            $this->logger->log('post.updated', $post->post_title, [
                'post_type' => $post->post_type,
                'post_id' => $post_id,
            ], $post_id);
        }

        update_post_meta($post_id, '_previous_post', 1);
    }

    public function track_delete_post(int $post_id, \WP_Post $post): void
    {
        $excluded_types = ['nav_menu_item', 'attachment'];
        if (in_array($post->post_type, $excluded_types, true)) {
            return;
        }

        $this->logger->log('post.deleted', $post->post_title, [
            'post_type' => $post->post_type,
            'post_id' => $post_id,
        ], $post_id);
    }

    public function track_status_change(string $new_status, string $old_status, \WP_Post $post): void
    {
        if ($new_status === $old_status || $new_status === 'auto-draft') {
            return;
        }

        $excluded_types = ['nav_menu_item', 'attachment'];
        if (in_array($post->post_type, $excluded_types, true)) {
            return;
        }

        $this->logger->log('post.status_changed', $post->post_title, [
            'post_type' => $post->post_type,
            'old_status' => $old_status,
            'new_status' => $new_status,
            'post_id' => $post->ID,
        ], $post->ID);
    }
}

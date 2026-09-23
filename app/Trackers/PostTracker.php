<?php
declare(strict_types=1);

namespace WPSiteActivityLog\Trackers;

if (!defined('ABSPATH')) exit;

use WPSiteActivityLog\Log\ActivityLogger;

class PostTracker
{
    private static ?self $instance = null;
    private ActivityLogger $logger;

    private const EXCLUDED_TYPES = [
        'nav_menu_item',
        'attachment',
        'revision',
        'customize_changeset',
        'custom_css',
        'oembed_cache',
        'action_monitor',
        'wp_global_styles',
        'elementor_library',
        'shop_order',
        'shop_coupon',
    ];

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
        add_action('wp_trash_post', [$this, 'track_trash_post'], 10, 1);
        add_action('untrash_post', [$this, 'track_restore_post'], 10, 1);
        add_action('transition_post_status', [$this, 'track_status_change'], 10, 3);
    }

    private function is_excluded(string $post_type): bool
    {
        return in_array($post_type, self::EXCLUDED_TYPES, true);
    }

    private function get_post_type_label(string $post_type): string
    {
        $obj = get_post_type_object($post_type);
        return $obj ? ($obj->labels->singular_name ?? $post_type) : $post_type;
    }

    public function track_save_post(int $post_id, \WP_Post $post): void
    {
        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
            return;
        }

        if ($this->is_excluded($post->post_type)) {
            return;
        }

        $previous_post = get_post_meta($post_id, '_previous_post', true);
        $title = $post->post_title !== '' ? $post->post_title : sprintf(__('(No title #%d)', 'loghaven-site-logs'), $post_id);
        $type_label = $this->get_post_type_label($post->post_type);

        if (empty($previous_post)) {
            $this->logger->log('post.created', $title, [
                'post_type' => $post->post_type,
                'post_type_label' => $type_label,
                'post_id' => $post_id,
            ], $post_id);
        } else {
            $this->logger->log('post.updated', $title, [
                'post_type' => $post->post_type,
                'post_type_label' => $type_label,
                'post_id' => $post_id,
            ], $post_id);
        }

        update_post_meta($post_id, '_previous_post', 1);
    }

    public function track_trash_post(int $post_id): void
    {
        $post = get_post($post_id);
        if (!$post || $this->is_excluded($post->post_type)) {
            return;
        }

        $title = $post->post_title !== '' ? $post->post_title : sprintf(__('(No title #%d)', 'loghaven-site-logs'), $post_id);
        $type_label = $this->get_post_type_label($post->post_type);

        $this->logger->log('post.trashed', $title, [
            'post_type' => $post->post_type,
            'post_type_label' => $type_label,
            'post_id' => $post_id,
        ], $post_id);
    }

    public function track_restore_post(int $post_id): void
    {
        $post = get_post($post_id);
        if (!$post || $this->is_excluded($post->post_type)) {
            return;
        }

        $title = $post->post_title !== '' ? $post->post_title : sprintf(__('(No title #%d)', 'loghaven-site-logs'), $post_id);
        $type_label = $this->get_post_type_label($post->post_type);

        $this->logger->log('post.restored', $title, [
            'post_type' => $post->post_type,
            'post_type_label' => $type_label,
            'post_id' => $post_id,
        ], $post_id);
    }

    public function track_delete_post(int $post_id, \WP_Post $post): void
    {
        if ($this->is_excluded($post->post_type)) {
            return;
        }

        $title = $post->post_title !== '' ? $post->post_title : sprintf(__('(No title #%d)', 'loghaven-site-logs'), $post_id);
        $type_label = $this->get_post_type_label($post->post_type);

        $this->logger->log('post.deleted', $title, [
            'post_type' => $post->post_type,
            'post_type_label' => $type_label,
            'post_id' => $post_id,
        ], $post_id);
    }

    public function track_status_change(string $new_status, string $old_status, \WP_Post $post): void
    {
        if ($new_status === $old_status || $new_status === 'auto-draft' || $new_status === 'trash' || $old_status === 'trash') {
            return;
        }

        if ($this->is_excluded($post->post_type)) {
            return;
        }

        $title = $post->post_title !== '' ? $post->post_title : sprintf(__('(No title #%d)', 'loghaven-site-logs'), $post->ID);
        $type_label = $this->get_post_type_label($post->post_type);

        $this->logger->log('post.status_changed', $title, [
            'post_type' => $post->post_type,
            'post_type_label' => $type_label,
            'old_status' => $old_status,
            'new_status' => $new_status,
            'post_id' => $post->ID,
        ], $post->ID);
    }
}

<?php
declare(strict_types=1);

namespace WPSiteActivityLog\Trackers;

if (!defined('ABSPATH')) exit;

use WPSiteActivityLog\Log\ActivityLogger;

class MediaTracker
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
        throw new \Exception('Cannot unserialize MediaTracker');
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
        add_action('add_attachment', [$this, 'track_uploaded']);
        add_action('delete_attachment', [$this, 'track_deleted']);
    }

    public function track_uploaded(int $post_id): void
    {
        $post = get_post($post_id);
        if (!$post) {
            return;
        }

        $name = $this->get_attachment_name($post);
        $mime = $post->post_mime_type ?: '';

        $this->logger->log('media.uploaded', $name, [
            'attachment_id' => $post_id,
            'mime_type' => $mime,
        ], $post_id);
    }

    public function track_deleted(int $post_id): void
    {
        $post = get_post($post_id);
        if (!$post) {
            return;
        }

        $name = $this->get_attachment_name($post);

        $this->logger->log('media.deleted', $name, [
            'attachment_id' => $post_id,
            'mime_type' => $post->post_mime_type ?: '',
        ], $post_id);
    }

    private function get_attachment_name(\WP_Post $post): string
    {
        $title = get_the_title($post->ID);
        if ($title !== '') {
            return $title;
        }
        return basename((string)get_attached_file($post->ID));
    }
}
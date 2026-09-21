<?php
declare(strict_types=1);

namespace WPSiteActivityLog\Trackers;

if (!defined('ABSPATH')) exit;

use WPSiteActivityLog\Log\ActivityLogger;

class CommentTracker
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
        throw new \Exception('Cannot unserialize CommentTracker');
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
        add_action('wp_insert_comment', [$this, 'track_created'], 10, 2);
        add_action('wp_set_comment_status', [$this, 'track_status'], 10, 2);
        add_action('delete_comment', [$this, 'track_deleted']);
    }

    public function track_created(int $comment_id, \WP_Comment $comment): void
    {
        $author = $comment->comment_author ?: __('Guest', 'syncly-site-reports');

        $this->logger->log('comment.created', $author, [
            'comment_id' => $comment_id,
            'post_id' => $comment->comment_post_ID,
            'approved' => '1',
        ], $comment_id);
    }

    public function track_status(int $comment_id, string $new_status): void
    {
        $comment = get_comment($comment_id);
        if (!$comment) {
            return;
        }

        $author = $comment->comment_author ?: __('Guest', 'syncly-site-reports');

        if ($new_status === 'spam') {
            $this->logger->log('comment.spammed', $author, [
                'comment_id' => $comment_id,
                'post_id' => $comment->comment_post_ID,
            ], $comment_id);
        }
    }

    public function track_deleted(int $comment_id): void
    {
        $comment = get_comment($comment_id);
        if (!$comment) {
            return;
        }

        $author = $comment->comment_author ?: __('Guest', 'syncly-site-reports');

        $this->logger->log('comment.deleted', $author, [
            'comment_id' => $comment_id,
            'post_id' => $comment->comment_post_ID,
        ], $comment_id);
    }
}
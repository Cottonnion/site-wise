<?php
declare(strict_types=1);

namespace WPSiteActivityLog\Trackers;

if (!defined('ABSPATH')) exit;

use WPSiteActivityLog\Log\ActivityLogger;

class TaxonomyTracker
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
        throw new \Exception('Cannot unserialize TaxonomyTracker');
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
        add_action('created_term', [$this, 'track_created'], 10, 3);
        add_action('delete_term', [$this, 'track_deleted'], 10, 4);
    }

    public function track_created(int $term_id, int $tt_id, string $taxonomy): void
    {
        $term = get_term($term_id);
        if (!$term || is_wp_error($term)) {
            return;
        }

        $this->logger->log('term.created', $term->name, [
            'term_id' => $term_id,
            'taxonomy' => $taxonomy,
            'term_slug' => $term->slug,
        ], $term_id);
    }

    public function track_deleted(int $term_id, int $tt_id, string $taxonomy, $deleted_term = null): void
    {
        $name = $term_id;
        if ($deleted_term instanceof \WP_Term && $deleted_term->name !== '') {
            $name = $deleted_term->name;
        } else {
            $term = get_term($term_id);
            if ($term && !is_wp_error($term)) {
                $name = $term->name;
            }
        }

        $this->logger->log('term.deleted', (string)$name, [
            'term_id' => $term_id,
            'taxonomy' => $taxonomy,
        ], $term_id);
    }
}
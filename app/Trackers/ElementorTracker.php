<?php
declare(strict_types=1);

namespace WPSiteActivityLog\Trackers;

if (!defined('ABSPATH')) exit;

use WPSiteActivityLog\Log\ActivityLogger;

class ElementorTracker
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
        throw new \Exception('Cannot unserialize ElementorTracker');
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
        add_action('elementor/editor/after_save', [$this, 'track_editor_save'], 10, 2);
        add_action('before_delete_post', [$this, 'track_delete_template'], 10, 2);
        add_action('updated_option', [$this, 'track_elementor_options'], 10, 3);
    }

    public function track_editor_save(int $post_id, array $editor_data = []): void
    {
        $post = get_post($post_id);
        if (!$post) {
            return;
        }

        $title = !empty($post->post_title) ? $post->post_title : sprintf(__('Item #%d', 'loghaven-site-logs'), $post_id);

        if ($post->post_type === 'elementor_library') {
            $template_type = get_post_meta($post_id, '_elementor_template_type', true) ?: 'section';
            $is_new = empty(get_post_meta($post_id, '_wsal_elementor_tracked', true));

            if ($is_new) {
                update_post_meta($post_id, '_wsal_elementor_tracked', 1);
                $this->logger->log('elementor.template_created', $title, [
                    'post_id' => $post_id,
                    'template_type' => $template_type,
                ], $post_id);
            } else {
                $this->logger->log('elementor.template_updated', $title, [
                    'post_id' => $post_id,
                    'template_type' => $template_type,
                ], $post_id);
            }
        } else {
            $this->logger->log('elementor.post_edited', $title, [
                'post_id' => $post_id,
                'post_type' => $post->post_type,
            ], $post_id);
        }
    }

    public function track_delete_template(int $post_id, \WP_Post $post): void
    {
        if ($post->post_type !== 'elementor_library') {
            return;
        }

        $title = !empty($post->post_title) ? $post->post_title : sprintf(__('Template #%d', 'loghaven-site-logs'), $post_id);
        $template_type = get_post_meta($post_id, '_elementor_template_type', true) ?: 'section';

        $this->logger->log('elementor.template_deleted', $title, [
            'post_id' => $post_id,
            'template_type' => $template_type,
        ], $post_id);
    }

    public function track_elementor_options(string $option, mixed $old_value, mixed $new_value): void
    {
        if (!in_array($option, ['elementor_active_kit', 'elementor_custom_icon_sets', 'elementor_experiment-container'], true)) {
            return;
        }

        $this->logger->log('elementor.settings_updated', sprintf(__('Elementor Setting: %s', 'loghaven-site-logs'), $option), [
            'option_name' => $option,
        ]);
    }
}

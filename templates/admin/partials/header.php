<?php
if (!defined('ABSPATH')) exit;
$page_title = $args['page_title'] ?? __('Activity Log', 'syncly-site-reports');
?>
<div class="wsal-wrap">
    <h1><?php echo esc_html($page_title); ?></h1>

    <nav class="wsal-tabs" role="tablist">
        <a href="#" class="wsal-tab is-active" data-view="dashboard" role="tab" aria-selected="true">
            <?php esc_html_e('Dashboard', 'syncly-site-reports'); ?>
        </a>
        <a href="#" class="wsal-tab" data-view="log" role="tab" aria-selected="false">
            <?php esc_html_e('Activity Log', 'syncly-site-reports'); ?>
        </a>
        <a href="#" class="wsal-tab" data-view="settings" role="tab" aria-selected="false">
            <?php esc_html_e('Settings', 'syncly-site-reports'); ?>
        </a>
    </nav>

    <div id="wsal-view" class="wsal-view" aria-live="polite"></div>
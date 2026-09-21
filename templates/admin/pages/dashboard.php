<?php
if (!defined('ABSPATH')) exit;

use WPSiteActivityLog\Log\LogQuery;
use WPSiteActivityLog\Admin\ReportAnalyzer;
use WPSiteActivityLog\Admin\ReportGenerator;

$query = LogQuery::get_instance();
$today = date('Y-m-d 00:00:00', current_time('U'));
$week_ago = date('Y-m-d H:i:s', current_time('U') - 604800);

$today_count = $query->get_total(['date_from' => $today]);
$week_count = $query->get_total(['date_from' => $week_ago]);
$failed_count = $query->get_total(['date_from' => $week_ago, 'event_code' => 'user.login_failed']);

$logs = $query->get_logs(['per_page' => 10]);
$user_ids = array_unique(array_column($logs, 'user_id'));
$active_users = count(array_filter($user_ids));

$report = ReportGenerator::get_instance()->generate(['period' => 'week']);
$analysis = ReportAnalyzer::get_instance()->analyze(['period' => 'week']);
$args = ['report' => $report, 'analysis' => $analysis];
include WSAL_PATH . 'templates/admin/partials/report-preview.php';
?>

<div class="wsal-stats-grid wsal-mt-lg">
    <div class="wsal-stat-card">
        <div class="wsal-stat-number"><?php echo esc_html($today_count); ?></div>
        <div class="wsal-stat-label"><?php esc_html_e('Events Today', 'syncly-site-reports'); ?></div>
    </div>
    <div class="wsal-stat-card">
        <div class="wsal-stat-number"><?php echo esc_html($week_count); ?></div>
        <div class="wsal-stat-label"><?php esc_html_e('Events This Week', 'syncly-site-reports'); ?></div>
    </div>
    <div class="wsal-stat-card">
        <div class="wsal-stat-number"><?php echo esc_html($active_users); ?></div>
        <div class="wsal-stat-label"><?php esc_html_e('Active Users', 'syncly-site-reports'); ?></div>
    </div>
    <div class="wsal-stat-card">
        <div class="wsal-stat-number"><?php echo esc_html($failed_count); ?></div>
        <div class="wsal-stat-label"><?php esc_html_e('Failed Logins (7d)', 'syncly-site-reports'); ?></div>
    </div>
</div>

<div class="wsal-card wsal-mt-lg">
    <div class="wsal-card-header">
        <h2 class="wsal-card-title"><?php esc_html_e('Recent Activity', 'syncly-site-reports'); ?></h2>
    </div>
    <div class="wsal-card-body">
        <?php if (empty($logs)) : ?>
            <div class="wsal-empty-state">
                <p><?php esc_html_e('No activity yet', 'syncly-site-reports'); ?></p>
            </div>
        <?php else : ?>
            <table class="wsal-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Event', 'syncly-site-reports'); ?></th>
                        <th><?php esc_html_e('Object', 'syncly-site-reports'); ?></th>
                        <th><?php esc_html_e('User', 'syncly-site-reports'); ?></th>
                        <th><?php esc_html_e('IP', 'syncly-site-reports'); ?></th>
                        <th><?php esc_html_e('Time', 'syncly-site-reports'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log) : ?>
                        <?php $args = ['log' => $log]; include WSAL_PATH . 'templates/admin/partials/log-row.php'; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
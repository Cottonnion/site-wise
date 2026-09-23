<?php
if (!defined('ABSPATH')) exit;

use WPSiteActivityLog\Log\LogQuery;
use WPSiteActivityLog\Log\EventRegistry;

$query = LogQuery::get_instance();
$search = $args['search'] ?? '';
$event_code = $args['event_code'] ?? '';
$paged = (int)($args['paged'] ?? 1);
$per_page = 20;

$query_args = [
    'page' => $paged,
    'per_page' => $per_page,
];

if ($search !== '') {
    $query_args['search'] = $search;
}

if ($event_code !== '') {
    $query_args['event_code'] = $event_code;
}

$logs = $query->get_logs($query_args);
$total = $query->get_total($query_args);
$total_pages = (int)ceil($total / $per_page);
?>

<div class="wsal-card">
    <div class="wsal-card-header">
        <h2 class="wsal-card-title"><?php echo esc_html__('Activity Log', 'loghaven-site-logs'); ?></h2>
    </div>
    <div class="wsal-card-body">
        <form id="wsal-log-filter" class="wsal-filter-bar" method="post">
            <div class="wsal-filter-group">
                <label for="wsal-log-search"><?php esc_html_e('Search', 'loghaven-site-logs'); ?></label>
                <input type="text" id="wsal-log-search" name="search" class="wsal-input"
                       placeholder="<?php esc_attr_e('Search...', 'loghaven-site-logs'); ?>"
                       value="<?php echo esc_attr($search); ?>">
            </div>

            <div class="wsal-filter-group">
                <label for="wsal-log-event"><?php esc_html_e('Event', 'loghaven-site-logs'); ?></label>
                <select id="wsal-log-event" name="event_code" class="wsal-select">
                    <option value=""><?php esc_html_e('All events', 'loghaven-site-logs'); ?></option>
                    <?php foreach (EventRegistry::get_instance()->get_all() as $code => $event) : ?>
                        <option value="<?php echo esc_attr($code); ?>" <?php selected($event_code, $code); ?>>
                            <?php echo esc_html($event['label']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="wsal-btn wsal-btn-primary">
                <?php esc_html_e('Filter', 'loghaven-site-logs'); ?>
            </button>

            <span class="wsal-log-actions">
                <button type="button" class="wsal-btn wsal-btn-ghost" data-action="export-csv">
                    <?php esc_html_e('Export CSV', 'loghaven-site-logs'); ?>
                </button>
                <button type="button" class="wsal-btn wsal-btn-ghost wsal-btn-danger" data-action="clear-logs">
                    <?php esc_html_e('Clear Logs', 'loghaven-site-logs'); ?>
                </button>
            </span>
        </form>

        <?php if (empty($logs)) : ?>
            <div class="wsal-empty-state">
                <p><?php echo esc_html__('No activity found', 'loghaven-site-logs'); ?></p>
            </div>
        <?php else : ?>
            <table class="wsal-table wsal-log-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Event', 'loghaven-site-logs'); ?></th>
                        <th><?php esc_html_e('Object', 'loghaven-site-logs'); ?></th>
                        <th><?php esc_html_e('User', 'loghaven-site-logs'); ?></th>
                        <th><?php esc_html_e('IP', 'loghaven-site-logs'); ?></th>
                        <th><?php esc_html_e('Time', 'loghaven-site-logs'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log) : ?>
                        <?php $args = ['log' => $log]; include WSAL_PATH . 'templates/admin/partials/log-row.php'; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ($total_pages > 1) : ?>
                <div class="wsal-pagination">
                    <?php if ($paged > 1) : ?>
                        <button type="button" class="wsal-btn wsal-btn-ghost wsal-paginate" data-page="<?php echo (int)($paged - 1); ?>">
                            <?php esc_html_e('Previous', 'loghaven-site-logs'); ?>
                        </button>
                    <?php endif; ?>

                    <span><?php echo esc_html(sprintf(__('Page %1$d of %2$d', 'loghaven-site-logs'), $paged, $total_pages)); ?></span>

                    <?php if ($paged < $total_pages) : ?>
                        <button type="button" class="wsal-btn wsal-btn-ghost wsal-paginate" data-page="<?php echo (int)($paged + 1); ?>">
                            <?php esc_html_e('Next', 'loghaven-site-logs'); ?>
                        </button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
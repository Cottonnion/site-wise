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
        <h2 class="wsal-card-title"><?php echo esc_html__('Activity Log', 'syncly-site-reports'); ?></h2>
    </div>
    <div class="wsal-card-body">
        <form id="wsal-log-filter" class="wsal-filter-bar" method="post">
            <div class="wsal-filter-group">
                <label for="wsal-log-search"><?php esc_html_e('Search', 'syncly-site-reports'); ?></label>
                <input type="text" id="wsal-log-search" name="search" class="wsal-input"
                       placeholder="<?php esc_attr_e('Search...', 'syncly-site-reports'); ?>"
                       value="<?php echo esc_attr($search); ?>">
            </div>

            <div class="wsal-filter-group">
                <label for="wsal-log-event"><?php esc_html_e('Event', 'syncly-site-reports'); ?></label>
                <select id="wsal-log-event" name="event_code" class="wsal-select">
                    <option value=""><?php esc_html_e('All events', 'syncly-site-reports'); ?></option>
                    <?php foreach (EventRegistry::get_instance()->get_all() as $code => $event) : ?>
                        <option value="<?php echo esc_attr($code); ?>" <?php selected($event_code, $code); ?>>
                            <?php echo esc_html($event['label']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="wsal-btn wsal-btn-primary">
                <?php esc_html_e('Filter', 'syncly-site-reports'); ?>
            </button>

            <span class="wsal-log-actions">
                <button type="button" class="wsal-btn wsal-btn-ghost" data-action="export-csv">
                    <?php esc_html_e('Export CSV', 'syncly-site-reports'); ?>
                </button>
            </span>
        </form>

        <?php if (empty($logs)) : ?>
            <div class="wsal-empty-state">
                <p><?php echo esc_html__('No activity found', 'syncly-site-reports'); ?></p>
            </div>
        <?php else : ?>
            <table class="wsal-table wsal-log-table">
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

            <?php if ($total_pages > 1) : ?>
                <div class="wsal-pagination">
                    <?php if ($paged > 1) : ?>
                        <button type="button" class="wsal-btn wsal-btn-ghost wsal-paginate" data-page="<?php echo (int)($paged - 1); ?>">
                            <?php esc_html_e('Previous', 'syncly-site-reports'); ?>
                        </button>
                    <?php endif; ?>

                    <span><?php echo sprintf(esc_html__('Page %d of %d', 'syncly-site-reports'), $paged, $total_pages); ?></span>

                    <?php if ($paged < $total_pages) : ?>
                        <button type="button" class="wsal-btn wsal-btn-ghost wsal-paginate" data-page="<?php echo (int)($paged + 1); ?>">
                            <?php esc_html_e('Next', 'syncly-site-reports'); ?>
                        </button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
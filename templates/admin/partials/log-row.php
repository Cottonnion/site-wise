<?php
if (!defined('ABSPATH')) exit;

use WPSiteActivityLog\Log\EventRegistry;

$log = $args['log'] ?? null;
if (!$log) return;

$event = EventRegistry::get_instance()->get($log->event_code);
$label = $event['label'] ?? $log->event_code;
$severity = $event['severity'] ?? 'info';
$time = human_time_diff(strtotime((string)$log->created_at), current_time('U')) . ' ' . __('ago', 'loghaven-site-logs');

$meta = json_decode((string)($log->meta ?? '{}'), true) ?: [];

// Extract visual diff if applicable
$diff_text = '';
if ($log->event_code === 'settings.updated' && isset($meta['old_value'], $meta['new_value'])) {
    $diff_text = sprintf('%s &rarr; %s', esc_html($meta['old_value']), esc_html($meta['new_value']));
} elseif ($log->event_code === 'user.role_changed' && isset($meta['from_role'], $meta['to_role'])) {
    $diff_text = sprintf('%s &rarr; %s', esc_html($meta['from_role']), esc_html($meta['to_role']));
} elseif ($log->event_code === 'wc.order_status' && isset($meta['old_status'], $meta['new_status'])) {
    $diff_text = sprintf('%s &rarr; %s', esc_html($meta['old_status']), esc_html($meta['new_status']));
} elseif ($log->event_code === 'post.status_changed' && isset($meta['old_status'], $meta['new_status'])) {
    $diff_text = sprintf('%s &rarr; %s', esc_html($meta['old_status']), esc_html($meta['new_status']));
} elseif ($log->event_code === 'wc.stock_changed' && isset($meta['new_stock'])) {
    $diff_text = sprintf(__('New Stock: %s', 'loghaven-site-logs'), esc_html((string)$meta['new_stock']));
}
?>
<tr class="wsal-log-row" data-id="<?php echo esc_attr($log->id); ?>">
    <td>
        <span class="wsal-event-label">
            <span class="wsal-severity-dot wsal-severity-<?php echo esc_attr($severity); ?>"></span>
            <?php echo esc_html($label); ?>
        </span>
    </td>
    <td>
        <span class="wsal-td-object">
            <span class="wsal-badge"><?php echo esc_html($log->object_type); ?></span>
            <span class="wsal-object-name"><?php echo esc_html($log->object_name); ?></span>
            <?php if ($diff_text !== '') : ?>
                <span class="wsal-diff-badge"><?php echo $diff_text; ?></span>
            <?php endif; ?>
        </span>
    </td>
    <td class="wsal-td-user">
        <?php if (!empty($log->user_id) && get_userdata((int)$log->user_id)) : ?>
            <a class="wsal-user-link" href="<?php echo esc_url(get_edit_user_link((int)$log->user_id)); ?>" title="<?php esc_attr_e('View user profile', 'loghaven-site-logs'); ?>">
                <?php echo esc_html($log->user_name ?: '—'); ?>
            </a>
        <?php else : ?>
            <?php echo esc_html($log->user_name ?: '—'); ?>
        <?php endif; ?>
        <?php if (!empty($log->user_role)) : ?>
            <small class="wsal-user-role">(<?php echo esc_html($log->user_role); ?>)</small>
        <?php endif; ?>
    </td>
    <td class="wsal-td-ip"><?php echo esc_html($log->ip_address ?: '—'); ?></td>
    <td class="wsal-td-time" title="<?php echo esc_attr($log->created_at); ?>"><?php echo esc_html($time); ?></td>
</tr>

<?php
if (!defined('ABSPATH')) exit;

use WPSiteActivityLog\Log\EventRegistry;

$log = $args['log'] ?? null;
if (!$log) return;

$event = EventRegistry::get_instance()->get($log->event_code);
$label = $event['label'] ?? $log->event_code;
$severity = $event['severity'] ?? 'info';
$time = human_time_diff(strtotime((string)$log->created_at), current_time('U')) . ' ' . __('ago', 'site-wise');
?>
<tr>
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
        </span>
    </td>
    <td class="wsal-td-user"><?php echo esc_html($log->user_name ?: '—'); ?></td>
    <td class="wsal-td-ip"><?php echo esc_html($log->ip_address ?: '—'); ?></td>
    <td class="wsal-td-time"><?php echo esc_html($time); ?></td>
</tr>
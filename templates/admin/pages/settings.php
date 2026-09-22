<?php
if (!defined('ABSPATH')) exit;

$retention_days = (int)($args['retention_days'] ?? 90);
$enable_report = (bool)($args['enable_report'] ?? true);
$report_period = (string)($args['report_period'] ?? 'week');
?>

<div class="wsal-card">
    <div class="wsal-card-header">
        <h2 class="wsal-card-title"><?php esc_html_e('Settings', 'loghaven-site-logs'); ?></h2>
    </div>
    <div class="wsal-card-body">
        <div id="wsal-settings-notice" aria-live="polite"></div>

        <form id="wsal-settings-form" class="wsal-settings-form" method="post">
            <div class="wsal-form-group">
                <label for="wsal-retention-days" class="wsal-label">
                    <?php esc_html_e('Log Retention (Days)', 'loghaven-site-logs'); ?>
                </label>
                <input type="number" id="wsal-retention-days" name="retention_days"
                       class="wsal-input" value="<?php echo esc_attr($retention_days); ?>" min="1" max="365">
                <small><?php esc_html_e('Logs older than this will be automatically deleted', 'loghaven-site-logs'); ?></small>
            </div>

            <div class="wsal-form-group">
                <label class="wsal-label">
                    <span class="wsal-checkbox-row">
                        <input type="checkbox" id="wsal-enable-report" name="enable_report"
                               value="1" <?php checked($enable_report); ?>>
                        <?php esc_html_e('Enable Shareable Reports', 'loghaven-site-logs'); ?>
                    </span>
                </label>
            </div>

            <div class="wsal-form-group">
                <label for="wsal-report-period" class="wsal-label">
                    <?php esc_html_e('Report Period', 'loghaven-site-logs'); ?>
                </label>
                <select id="wsal-report-period" name="report_period" class="wsal-select">
                    <option value="day" <?php selected($report_period, 'day'); ?>><?php esc_html_e('Today', 'loghaven-site-logs'); ?></option>
                    <option value="week" <?php selected($report_period, 'week'); ?>><?php esc_html_e('Last 7 days', 'loghaven-site-logs'); ?></option>
                    <option value="month" <?php selected($report_period, 'month'); ?>><?php esc_html_e('Last 30 days', 'loghaven-site-logs'); ?></option>
                </select>
                <small><?php esc_html_e('Time range covered by the shareable report', 'loghaven-site-logs'); ?></small>
            </div>

            <div class="wsal-settings-actions">
                <button type="submit" class="wsal-btn wsal-btn-primary">
                    <?php esc_html_e('Save Settings', 'loghaven-site-logs'); ?>
                </button>
            </div>
        </form>
    </div>
</div>
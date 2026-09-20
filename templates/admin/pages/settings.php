<?php
if (!defined('ABSPATH')) exit;

$retention_days = (int)($args['retention_days'] ?? 90);
$enable_report = (bool)($args['enable_report'] ?? true);
$report_period = (string)($args['report_period'] ?? 'week');
?>

<div class="wsal-card">
    <div class="wsal-card-header">
        <h2 class="wsal-card-title"><?php esc_html_e('Settings', 'site-wise'); ?></h2>
    </div>
    <div class="wsal-card-body">
        <div id="wsal-settings-notice" aria-live="polite"></div>

        <form id="wsal-settings-form" class="wsal-settings-form" method="post">
            <div class="wsal-form-group">
                <label for="wsal-retention-days" class="wsal-label">
                    <?php esc_html_e('Log Retention (Days)', 'site-wise'); ?>
                </label>
                <input type="number" id="wsal-retention-days" name="retention_days"
                       class="wsal-input" value="<?php echo esc_attr($retention_days); ?>" min="1" max="365">
                <small><?php esc_html_e('Logs older than this will be automatically deleted', 'site-wise'); ?></small>
            </div>

            <div class="wsal-form-group">
                <label class="wsal-label">
                    <span class="wsal-checkbox-row">
                        <input type="checkbox" id="wsal-enable-report" name="enable_report"
                               value="1" <?php checked($enable_report); ?>>
                        <?php esc_html_e('Enable Shareable Reports', 'site-wise'); ?>
                    </span>
                </label>
            </div>

            <div class="wsal-form-group">
                <label for="wsal-report-period" class="wsal-label">
                    <?php esc_html_e('Report Period', 'site-wise'); ?>
                </label>
                <select id="wsal-report-period" name="report_period" class="wsal-select">
                    <option value="day" <?php selected($report_period, 'day'); ?>><?php esc_html_e('Today', 'site-wise'); ?></option>
                    <option value="week" <?php selected($report_period, 'week'); ?>><?php esc_html_e('Last 7 days', 'site-wise'); ?></option>
                    <option value="month" <?php selected($report_period, 'month'); ?>><?php esc_html_e('Last 30 days', 'site-wise'); ?></option>
                </select>
                <small><?php esc_html_e('Time range covered by the shareable report', 'site-wise'); ?></small>
            </div>

            <div class="wsal-settings-actions">
                <button type="submit" class="wsal-btn wsal-btn-primary">
                    <?php esc_html_e('Save Settings', 'site-wise'); ?>
                </button>
            </div>
        </form>
    </div>
</div>
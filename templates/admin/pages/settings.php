<?php
if (!defined('ABSPATH')) exit;

$retention_days = (int)($args['retention_days'] ?? 90);
$enable_report = (bool)($args['enable_report'] ?? true);
$report_period = (string)($args['report_period'] ?? 'week');
$agency_name = (string)($args['agency_name'] ?? '');
$agency_logo_url = (string)($args['agency_logo_url'] ?? '');
$brand_color = (string)($args['brand_color'] ?? '#4f46e5');
$custom_footer_text = (string)($args['custom_footer_text'] ?? '');
$webhook_url = (string)($args['webhook_url'] ?? '');
$enable_email_digest = (bool)($args['enable_email_digest'] ?? false);
$digest_email = (string)($args['digest_email'] ?? '');
$digest_frequency = (string)($args['digest_frequency'] ?? 'weekly');
?>

<div id="wsal-settings-notice" aria-live="polite"></div>

<form id="wsal-settings-form" class="wsal-settings-form" method="post">

    <div class="wsal-accordion">

        <section class="wsal-acc is-open" data-section="storage">
            <button type="button" class="wsal-acc-toggle" aria-expanded="true">
                <span class="wsal-acc-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="6" rx="8" ry="3"/><path d="M4 6v6c0 1.7 3.6 3 8 3s8-1.3 8-3V6"/><path d="M4 12v6c0 1.7 3.6 3 8 3s8-1.3 8-3v-6"/></svg>
                </span>
                <span class="wsal-acc-copy">
                    <span class="wsal-acc-title"><?php esc_html_e('Storage', 'loghaven-site-logs'); ?></span>
                    <span class="wsal-acc-desc"><?php esc_html_e('How long activity records are kept before automatic cleanup.', 'loghaven-site-logs'); ?></span>
                </span>
                <span class="wsal-acc-chevron" aria-hidden="true"></span>
            </button>
            <div class="wsal-acc-body">
                <div class="wsal-form-group">
                    <label for="wsal-retention-days" class="wsal-label">
                        <?php esc_html_e('Retention period', 'loghaven-site-logs'); ?>
                    </label>
                    <div class="wsal-input-inline">
                        <input type="number" id="wsal-retention-days" name="retention_days"
                               class="wsal-input wsal-input-sm" value="<?php echo esc_attr($retention_days); ?>" min="1" max="365">
                        <span class="wsal-input-suffix"><?php esc_html_e('days', 'loghaven-site-logs'); ?></span>
                    </div>
                    <small><?php esc_html_e('Logs older than this are deleted daily. Range: 1–365 days.', 'loghaven-site-logs'); ?></small>
                </div>
            </div>
        </section>

        <section class="wsal-acc" data-section="reports">
            <button type="button" class="wsal-acc-toggle" aria-expanded="false">
                <span class="wsal-acc-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                </span>
                <span class="wsal-acc-copy">
                    <span class="wsal-acc-title"><?php esc_html_e('Client reports', 'loghaven-site-logs'); ?></span>
                    <span class="wsal-acc-desc"><?php esc_html_e('Shareable, tokenized summaries you can send to clients.', 'loghaven-site-logs'); ?></span>
                </span>
                <span class="wsal-acc-chevron" aria-hidden="true"></span>
            </button>
            <div class="wsal-acc-body">
                <div class="wsal-form-group">
                    <label class="wsal-switch" for="wsal-enable-report">
                        <input type="checkbox" id="wsal-enable-report" name="enable_report"
                               class="wsal-toggle-hidden" value="1" <?php checked($enable_report); ?>>
                        <span class="wsal-switch-slider" aria-hidden="true"></span>
                        <span class="wsal-switch-text"><?php esc_html_e('Enable shareable reports', 'loghaven-site-logs'); ?></span>
                    </label>
                </div>
                <div class="wsal-form-group">
                    <label for="wsal-report-period" class="wsal-label">
                        <?php esc_html_e('Default period', 'loghaven-site-logs'); ?>
                    </label>
                    <select id="wsal-report-period" name="report_period" class="wsal-select wsal-select-sm">
                        <option value="day" <?php selected($report_period, 'day'); ?>><?php esc_html_e('Today', 'loghaven-site-logs'); ?></option>
                        <option value="week" <?php selected($report_period, 'week'); ?>><?php esc_html_e('Last 7 days', 'loghaven-site-logs'); ?></option>
                        <option value="month" <?php selected($report_period, 'month'); ?>><?php esc_html_e('Last 30 days', 'loghaven-site-logs'); ?></option>
                    </select>
                </div>
            </div>
        </section>

        <section class="wsal-acc" data-section="branding">
            <button type="button" class="wsal-acc-toggle" aria-expanded="false">
                <span class="wsal-acc-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="13.5" cy="6.5" r="2.5"/><circle cx="6" cy="12" r="2"/><circle cx="17" cy="15.5" r="2.5"/><path d="M8 12h4.5M13.5 9v4M17 13v-1"/></svg>
                </span>
                <span class="wsal-acc-copy">
                    <span class="wsal-acc-title"><?php esc_html_e('Agency branding', 'loghaven-site-logs'); ?></span>
                    <span class="wsal-acc-desc"><?php esc_html_e('Logo, name, colors, and footer shown on public reports.', 'loghaven-site-logs'); ?></span>
                </span>
                <span class="wsal-acc-chevron" aria-hidden="true"></span>
            </button>
            <div class="wsal-acc-body">
                <div class="wsal-form-row">
                    <div class="wsal-form-group">
                        <label for="wsal-agency-name" class="wsal-label">
                            <?php esc_html_e('Agency name', 'loghaven-site-logs'); ?>
                        </label>
                        <input type="text" id="wsal-agency-name" name="agency_name"
                               class="wsal-input" value="<?php echo esc_attr($agency_name); ?>" placeholder="<?php esc_attr_e('Acme Web Care', 'loghaven-site-logs'); ?>">
                    </div>
                    <div class="wsal-form-group">
                        <label for="wsal-brand-color" class="wsal-label">
                            <?php esc_html_e('Accent color', 'loghaven-site-logs'); ?>
                        </label>
                        <div class="wsal-color-row">
                            <input type="color" id="wsal-brand-color" name="brand_color"
                                   class="wsal-color-input" value="<?php echo esc_attr($brand_color); ?>">
                            <span class="wsal-color-hex"><?php echo esc_html($brand_color); ?></span>
                        </div>
                    </div>
                </div>

                <div class="wsal-form-group">
                    <label class="wsal-label"><?php esc_html_e('Logo', 'loghaven-site-logs'); ?></label>
                    <div class="wsal-logo-uploader-wrap">
                        <div id="wsal-logo-preview-wrap" class="wsal-logo-preview-wrap <?php echo $agency_logo_url ? '' : 'is-empty'; ?>">
                            <img id="wsal-logo-preview" src="<?php echo esc_url($agency_logo_url); ?>" alt="" style="<?php echo $agency_logo_url ? '' : 'display:none;'; ?>">
                        </div>
                        <input type="hidden" id="wsal-agency-logo" name="agency_logo_url" value="<?php echo esc_attr($agency_logo_url); ?>">
                        <div class="wsal-logo-btn-group">
                            <button type="button" class="wsal-btn wsal-btn-ghost wsal-btn-sm" id="wsal-choose-logo">
                                <?php esc_html_e('Choose from Media Library', 'loghaven-site-logs'); ?>
                            </button>
                            <button type="button" class="wsal-btn wsal-btn-ghost wsal-btn-danger wsal-btn-sm" id="wsal-remove-logo" style="<?php echo $agency_logo_url ? '' : 'display:none;'; ?>">
                                <?php esc_html_e('Remove', 'loghaven-site-logs'); ?>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="wsal-form-group">
                    <label for="wsal-custom-footer" class="wsal-label">
                        <?php esc_html_e('Report footer', 'loghaven-site-logs'); ?>
                    </label>
                    <input type="text" id="wsal-custom-footer" name="custom_footer_text"
                           class="wsal-input" value="<?php echo esc_attr($custom_footer_text); ?>" placeholder="<?php esc_attr_e('Managed by Acme Agency.', 'loghaven-site-logs'); ?>">
                </div>
            </div>
        </section>

        <section class="wsal-acc" data-section="email">
            <button type="button" class="wsal-acc-toggle" aria-expanded="false">
                <span class="wsal-acc-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                </span>
                <span class="wsal-acc-copy">
                    <span class="wsal-acc-title"><?php esc_html_e('Email digest', 'loghaven-site-logs'); ?></span>
                    <span class="wsal-acc-desc"><?php esc_html_e('Scheduled weekly or monthly summaries sent to clients.', 'loghaven-site-logs'); ?></span>
                </span>
                <span class="wsal-acc-chevron" aria-hidden="true"></span>
            </button>
            <div class="wsal-acc-body">
                <div class="wsal-form-group">
                    <label class="wsal-switch" for="wsal-enable-email-digest">
                        <input type="checkbox" id="wsal-enable-email-digest" name="enable_email_digest"
                               class="wsal-toggle-hidden" value="1" <?php checked($enable_email_digest); ?>>
                        <span class="wsal-switch-slider" aria-hidden="true"></span>
                        <span class="wsal-switch-text"><?php esc_html_e('Send automated email digests', 'loghaven-site-logs'); ?></span>
                    </label>
                </div>
                <div class="wsal-form-row">
                    <div class="wsal-form-group">
                        <label for="wsal-digest-email" class="wsal-label">
                            <?php esc_html_e('Recipients', 'loghaven-site-logs'); ?>
                        </label>
                        <input type="text" id="wsal-digest-email" name="digest_email"
                               class="wsal-input" value="<?php echo esc_attr($digest_email); ?>" placeholder="client@example.com">
                        <small><?php esc_html_e('Comma-separated. Blank uses the site admin email.', 'loghaven-site-logs'); ?></small>
                    </div>
                    <div class="wsal-form-group">
                        <label for="wsal-digest-frequency" class="wsal-label">
                            <?php esc_html_e('Frequency', 'loghaven-site-logs'); ?>
                        </label>
                        <select id="wsal-digest-frequency" name="digest_frequency" class="wsal-select">
                            <option value="weekly" <?php selected($digest_frequency, 'weekly'); ?>><?php esc_html_e('Weekly', 'loghaven-site-logs'); ?></option>
                            <option value="monthly" <?php selected($digest_frequency, 'monthly'); ?>><?php esc_html_e('Monthly', 'loghaven-site-logs'); ?></option>
                        </select>
                    </div>
                </div>
                <div class="wsal-form-group wsal-test-row">
                    <button type="button" class="wsal-btn wsal-btn-ghost wsal-btn-sm" id="wsal-test-email">
                        <span class="wsal-btn-label"><?php esc_html_e('Send Test Email', 'loghaven-site-logs'); ?></span>
                        <span class="wsal-btn-spinner" aria-hidden="true"></span>
                    </button>
                    <small><?php esc_html_e('Sends a test digest to the first recipient configured above.', 'loghaven-site-logs'); ?></small>
                </div>
            </div>
        </section>

        <section class="wsal-acc" data-section="alerts">
            <button type="button" class="wsal-acc-toggle" aria-expanded="false">
                <span class="wsal-acc-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                </span>
                <span class="wsal-acc-copy">
                    <span class="wsal-acc-title"><?php esc_html_e('Security alerts', 'loghaven-site-logs'); ?></span>
                    <span class="wsal-acc-desc"><?php esc_html_e('Instant Slack, Discord, or webhook pings for critical events.', 'loghaven-site-logs'); ?></span>
                </span>
                <span class="wsal-acc-chevron" aria-hidden="true"></span>
            </button>
            <div class="wsal-acc-body">
                <div class="wsal-form-group">
                    <label for="wsal-webhook-url" class="wsal-label">
                        <?php esc_html_e('Webhook URL', 'loghaven-site-logs'); ?>
                    </label>
                    <input type="url" id="wsal-webhook-url" name="webhook_url"
                           class="wsal-input" value="<?php echo esc_attr($webhook_url); ?>" placeholder="https://hooks.slack.com/services/…">
                    <small><?php esc_html_e('Fires on admin role grants, plugin deletions, and critical setting changes.', 'loghaven-site-logs'); ?></small>
                </div>
                <div class="wsal-form-group wsal-test-row">
                    <button type="button" class="wsal-btn wsal-btn-ghost wsal-btn-sm" id="wsal-test-webhook">
                        <span class="wsal-btn-label"><?php esc_html_e('Send Test Webhook', 'loghaven-site-logs'); ?></span>
                        <span class="wsal-btn-spinner" aria-hidden="true"></span>
                    </button>
                    <small><?php esc_html_e('Sends a test alert to the webhook URL above.', 'loghaven-site-logs'); ?></small>
                </div>
            </div>
        </section>

    </div>

    <div class="wsal-settings-actions">
        <button type="submit" class="wsal-btn wsal-btn-primary">
            <?php esc_html_e('Save settings', 'loghaven-site-logs'); ?>
        </button>
    </div>
</form>

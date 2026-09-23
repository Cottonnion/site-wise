<?php
if (!defined('ABSPATH')) exit;

use WPSiteActivityLog\Core\SettingsManager;

$settings = SettingsManager::get_instance();
$agency_name = (string)$settings->get('agency_name', '');
$agency_logo_url = (string)$settings->get('agency_logo_url', '');
$brand_color = (string)$settings->get('brand_color', '#4f46e5');
$custom_footer_text = (string)$settings->get('custom_footer_text', '');

$report = $report ?? [];
$analysis = $analysis ?? [];

$site_name = $report['site_name'] ?? '';
$site_url = $report['site_url'] ?? '';
$period_label = $analysis['period_label'] ?? ($report['period'] ?? '');
$narrative = $analysis['narrative'] ?? [];
$attention = $analysis['attention'] ?? [];
$drift = $analysis['drift'] ?? [];
$total_events = (int)($analysis['total_events'] ?? ($report['total_events'] ?? 0));
$by_type = $report['by_type'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?php echo esc_html($site_name !== '' ? $site_name . ' — ' . __('Activity Report', 'loghaven-site-logs') : __('Site Report', 'loghaven-site-logs')); ?></title>
    <?php wp_print_styles('wsal-report-css'); ?>
    <style>
        :root {
            --wsal-color-primary: <?php echo esc_attr($brand_color); ?>;
            --wsal-color-primary-hover: <?php echo esc_attr($brand_color); ?>ee;
        }
    </style>
</head>
<body>
    <div class="wsal-report-container">
        <div class="wsal-report-header">
            <?php if ($agency_logo_url !== '') : ?>
                <div class="wsal-agency-logo-wrap">
                    <img src="<?php echo esc_url($agency_logo_url); ?>" alt="<?php echo esc_attr($agency_name ?: 'Agency Logo'); ?>" class="wsal-agency-logo">
                </div>
            <?php endif; ?>

            <h1><?php echo esc_html($site_name !== '' ? $site_name : __('Site Report', 'loghaven-site-logs')); ?></h1>
            <p class="wsal-report-url"><?php echo esc_html($site_url); ?></p>
            <span class="wsal-report-period"><?php echo esc_html($period_label); ?></span>
        </div>

        <div class="wsal-report-meta">
            <div class="wsal-report-meta-item">
                <span class="wsal-report-meta-label"><?php esc_html_e('Report Period:', 'loghaven-site-logs'); ?></span>
                <span class="wsal-report-meta-value"><?php echo esc_html($period_label); ?></span>
            </div>
            <div class="wsal-report-meta-item">
                <span class="wsal-report-meta-label"><?php esc_html_e('Generated On:', 'loghaven-site-logs'); ?></span>
                <span class="wsal-report-meta-value"><?php echo esc_html($report['generated_at'] ?? ''); ?></span>
            </div>
            <div class="wsal-report-meta-item">
                <span class="wsal-report-meta-label"><?php esc_html_e('Total Events Tracked:', 'loghaven-site-logs'); ?></span>
                <span class="wsal-report-meta-value"><?php echo esc_html($total_events); ?></span>
            </div>
        </div>

        <div class="wsal-report-section">
            <h2><?php esc_html_e('Activity Summary', 'loghaven-site-logs'); ?></h2>
            <?php if (empty($narrative)) : ?>
                <p class="wsal-report-quiet"><?php esc_html_e('No notable activity during this period.', 'loghaven-site-logs'); ?></p>
            <?php else : ?>
                <ul class="wsal-report-narrative">
                    <?php foreach ($narrative as $sentence) : ?>
                        <li><?php echo esc_html($sentence); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <?php if (!empty($attention)) : ?>
            <div class="wsal-report-section">
                <h2><?php esc_html_e('Security & System Alerts', 'loghaven-site-logs'); ?></h2>
                <ul class="wsal-report-narrative">
                    <?php foreach ($attention as $item) : ?>
                        <li class="wsal-attention-<?php echo esc_attr($item['severity'] ?? 'info'); ?>">
                            <?php echo esc_html($item['text'] ?? ''); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!empty($drift)) : ?>
            <div class="wsal-report-section">
                <h2><?php esc_html_e('Important Settings Changed', 'loghaven-site-logs'); ?></h2>
                <ul class="wsal-report-narrative">
                    <?php foreach ($drift as $row) : ?>
                        <li>
                            <?php echo esc_html(sprintf(
                                /* translators: 1: option name, 2: old value, 3: new value */
                                __('%1$s changed from "%2$s" to "%3$s"', 'loghaven-site-logs'),
                                $row['option'],
                                $row['old'],
                                $row['new']
                            )); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!empty($by_type)) : ?>
            <div class="wsal-report-section">
                <h2><?php esc_html_e('Activity by Area', 'loghaven-site-logs'); ?></h2>
                <div class="wsal-report-breakdown">
                    <?php
                    $max = max(array_values($by_type));
                    $labels = [
                        'post' => __('Posts & Pages', 'loghaven-site-logs'),
                        'woocommerce' => __('WooCommerce Commerce', 'loghaven-site-logs'),
                        'elementor' => __('Elementor Builder', 'loghaven-site-logs'),
                        'user' => __('Users & Auth', 'loghaven-site-logs'),
                        'plugin' => __('Plugins', 'loghaven-site-logs'),
                        'theme' => __('Themes', 'loghaven-site-logs'),
                        'core' => __('WordPress Core', 'loghaven-site-logs'),
                        'media' => __('Media Library', 'loghaven-site-logs'),
                        'comment' => __('Comments', 'loghaven-site-logs'),
                        'term' => __('Categories & Tags', 'loghaven-site-logs'),
                        'settings' => __('Site Settings', 'loghaven-site-logs'),
                    ];
                    foreach ($by_type as $type => $count) : ?>
                        <div class="wsal-breakdown-item">
                            <div class="wsal-breakdown-label"><?php echo esc_html($labels[$type] ?? ucfirst($type)); ?></div>
                            <div class="wsal-breakdown-bar">
                                <div class="wsal-breakdown-fill" style="width: <?php echo esc_attr(($count / max(1, $max)) * 100); ?>%"></div>
                            </div>
                            <div class="wsal-breakdown-count"><?php echo esc_html($count); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="wsal-report-footer">
            <?php if ($custom_footer_text !== '') : ?>
                <p><?php echo esc_html($custom_footer_text); ?></p>
            <?php elseif ($agency_name !== '') : ?>
                <p><?php echo esc_html(sprintf(__('Report prepared and verified by %s. IP addresses and sensitive data are excluded from client reports.', 'loghaven-site-logs'), $agency_name)); ?></p>
            <?php else : ?>
                <p><?php printf(esc_html__('This is an automated site maintenance report generated by %1$s. IP addresses and sensitive user credentials are not shown.', 'loghaven-site-logs'), esc_html(WSAL_PLUGIN_NAME)); ?></p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

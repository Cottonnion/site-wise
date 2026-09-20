<?php
if (!defined('ABSPATH')) exit;

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
    <meta name="robots" content="noindex">
    <title><?php echo esc_html($site_name !== '' ? $site_name : __('Site Report', 'site-wise')); ?></title>
    <link rel="stylesheet" href="<?php echo esc_url(WSAL_URL . 'assets/frontend/css/report.css'); ?>">
</head>
<body>
    <div class="wsal-report-container">
        <div class="wsal-report-header">
            <h1><?php echo esc_html($site_name !== '' ? $site_name : 'Site Report'); ?></h1>
            <p class="wsal-report-url"><?php echo esc_html($site_url); ?></p>
            <span class="wsal-report-period"><?php echo esc_html($period_label); ?></span>
        </div>

        <div class="wsal-report-meta">
            <div class="wsal-report-meta-item">
                <span class="wsal-report-meta-label"><?php esc_html_e('Report Period:', 'site-wise'); ?></span>
                <span class="wsal-report-meta-value"><?php echo esc_html($period_label); ?></span>
            </div>
            <div class="wsal-report-meta-item">
                <span class="wsal-report-meta-label"><?php esc_html_e('Generated:', 'site-wise'); ?></span>
                <span class="wsal-report-meta-value"><?php echo esc_html($report['generated_at'] ?? ''); ?></span>
            </div>
            <div class="wsal-report-meta-item">
                <span class="wsal-report-meta-label"><?php esc_html_e('Total Events:', 'site-wise'); ?></span>
                <span class="wsal-report-meta-value"><?php echo esc_html($total_events); ?></span>
            </div>
        </div>

        <div class="wsal-report-section">
            <h2><?php esc_html_e('Summary', 'site-wise'); ?></h2>
            <?php if (empty($narrative)) : ?>
                <p class="wsal-report-quiet"><?php esc_html_e('No notable activity during this period.', 'site-wise'); ?></p>
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
                <h2><?php esc_html_e('Things to be aware of', 'site-wise'); ?></h2>
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
                <h2><?php esc_html_e('Important settings changed', 'site-wise'); ?></h2>
                <ul class="wsal-report-narrative">
                    <?php foreach ($drift as $row) : ?>
                        <li>
                            <?php echo esc_html(sprintf(
                                /* translators: 1: option name, 2: old value, 3: new value */
                                __('%1$s changed from "%2$s" to "%3$s"', 'site-wise'),
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
                <h2><?php esc_html_e('Activity by Area', 'site-wise'); ?></h2>
                <div class="wsal-report-breakdown">
                    <?php
                    $max = max(array_values($by_type));
                    $labels = [
                        'post' => __('Posts &amp; Pages', 'site-wise'),
                        'user' => __('Users', 'site-wise'),
                        'plugin' => __('Plugins', 'site-wise'),
                        'theme' => __('Themes', 'site-wise'),
                        'core' => __('WordPress Core', 'site-wise'),
                        'media' => __('Media', 'site-wise'),
                        'comment' => __('Comments', 'site-wise'),
                        'term' => __('Categories &amp; Tags', 'site-wise'),
                        'settings' => __('Settings', 'site-wise'),
                    ];
                    foreach ($by_type as $type => $count) : ?>
                        <div class="wsal-breakdown-item">
                            <div class="wsal-breakdown-label"><?php echo esc_html($labels[$type] ?? $type); ?></div>
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
            <p><?php echo esc_html__('This is an automated report generated by Site Activity Log. IP addresses and user details are not shown in this client report.', 'site-wise'); ?></p>
        </div>
    </div>
</body>
</html>
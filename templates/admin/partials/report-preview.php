<?php
if (!defined('ABSPATH')) exit;

use WPSiteActivityLog\Admin\ReportGenerator;

$report = $args['report'] ?? null;
if (!$report) return;

$analysis = $args['analysis'] ?? [];
$share_url = ReportGenerator::get_instance()->get_report_url();
$period_label = $analysis['period_label'] ?? ($report['period'] ?? '');
$narrative = $analysis['narrative'] ?? [];
$attention = $analysis['attention'] ?? [];
$drift = $analysis['drift'] ?? [];
?>
<div class="wsal-card">
    <div class="wsal-card-header">
        <div class="wsal-flex wsal-flex-between wsal-gap-md">
            <h2 class="wsal-card-title">
                <?php echo esc_html($period_label !== '' ? $period_label : __('Site Report', 'site-wise')); ?>
            </h2>
            <button class="wsal-btn wsal-btn-ghost wsal-btn-sm" data-action="copy-report-link" data-url="<?php echo esc_url($share_url); ?>">
                <?php esc_html_e('Copy Report Link', 'site-wise'); ?>
            </button>
        </div>
    </div>
    <div class="wsal-card-body">
        <?php if (empty($narrative)) : ?>
            <p class="wsal-report-quiet"><?php esc_html_e('No notable activity during this period.', 'site-wise'); ?></p>
        <?php else : ?>
            <ul class="wsal-report-bullets">
                <?php foreach ($narrative as $sentence) : ?>
                    <li><?php echo esc_html($sentence); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php if (!empty($attention)) : ?>
            <div class="wsal-attention wsal-mt-md">
                <strong class="wsal-attention-title"><?php esc_html_e('Needs your attention', 'site-wise'); ?></strong>
                <ul class="wsal-attention-list">
                    <?php foreach ($attention as $item) : ?>
                        <li class="wsal-attention-<?php echo esc_attr($item['severity'] ?? 'info'); ?>">
                            <?php echo esc_html($item['text'] ?? ''); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!empty($drift)) : ?>
            <div class="wsal-drift wsal-mt-md">
                <strong class="wsal-attention-title"><?php esc_html_e('Important settings changed', 'site-wise'); ?></strong>
                <ul class="wsal-report-bullets">
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
    </div>
</div>
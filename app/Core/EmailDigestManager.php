<?php
declare(strict_types=1);

namespace WPSiteActivityLog\Core;

if (!defined('ABSPATH')) exit;

use WPSiteActivityLog\Admin\ReportAnalyzer;
use WPSiteActivityLog\Admin\ReportGenerator;

class EmailDigestManager
{
    private static ?self $instance = null;
    private SettingsManager $settings;

    private function __construct()
    {
        $this->settings = SettingsManager::get_instance();
    }

    private function __clone() {}

    public function __wakeup(): void
    {
        throw new \Exception('Cannot unserialize EmailDigestManager');
    }

    public static function get_instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init(): void
    {
        add_action('wsal_scheduled_email_digest', [$this, 'send_digest']);
    }

    public function schedule(): void
    {
        $enabled = (bool)$this->settings->get('enable_email_digest', false);
        $frequency = $this->settings->get('digest_frequency', 'weekly');
        $recurrence = $frequency === 'monthly' ? 'monthly' : 'weekly';

        if ($enabled) {
            if (!wp_next_scheduled('wsal_scheduled_email_digest')) {
                wp_schedule_event(time() + HOUR_IN_SECONDS, $recurrence, 'wsal_scheduled_email_digest');
            }
        } else {
            wp_clear_scheduled_hook('wsal_scheduled_email_digest');
        }
    }

    public function send_digest(): void
    {
        $enabled = (bool)$this->settings->get('enable_email_digest', false);
        if (!$enabled) {
            return;
        }

        $recipients_str = (string)$this->settings->get('digest_email', '');
        if ($recipients_str === '') {
            $recipients_str = get_option('admin_email');
        }

        $recipients = array_filter(array_map('sanitize_email', explode(',', $recipients_str)));
        if (empty($recipients)) {
            return;
        }

        $frequency = $this->settings->get('digest_frequency', 'weekly');
        $period = $frequency === 'monthly' ? 'month' : 'week';

        $analyzer = ReportAnalyzer::get_instance();
        $generator = ReportGenerator::get_instance();

        $analysis = $analyzer->analyze(['period' => $period]);
        $report = $generator->generate(['period' => $period]);

        $site_name = get_bloginfo('name');
        $agency_name = (string)$this->settings->get('agency_name', '');
        $brand_color = (string)$this->settings->get('brand_color', '#4f46e5');

        $subject = sprintf(
            /* translators: 1: Site Name, 2: Period Label */
            __('[%1$s] Activity & Maintenance Report - %2$s', 'loghaven-site-logs'),
            $site_name,
            $analysis['period_label']
        );

        $body = $this->render_email_html($site_name, $report, $analysis, $agency_name, $brand_color);

        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $site_name . ' <' . get_option('admin_email') . '>',
        ];

        foreach ($recipients as $to) {
            wp_mail($to, $subject, $body, $headers);
        }
    }

    public function send_test_email(string $to): bool
    {
        if (!is_email($to)) {
            return false;
        }

        $site_name = get_bloginfo('name');
        $agency_name = (string)$this->settings->get('agency_name', '');
        $brand_color = (string)$this->settings->get('brand_color', '#4f46e5');

        $subject = sprintf(
            /* translators: %s: Site Name */
            __('[%s] Test Activity Digest', 'loghaven-site-logs'),
            $site_name
        );

        $sender = $agency_name !== '' ? esc_html($agency_name) : WSAL_PLUGIN_NAME;

        $body = '<!DOCTYPE html>
        <html>
        <head><meta charset="utf-8"></head>
        <body style="font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif; background-color: #f6f7f7; margin: 0; padding: 30px 15px; color: #1d2327;">
            <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; border: 1px solid #dcdcde; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                <div style="background-color: ' . esc_attr($brand_color) . '; padding: 30px 25px; text-align: center; color: #ffffff;">
                    <h1 style="margin: 0 0 8px; font-size: 22px; font-weight: 700; color: #ffffff;">' . esc_html($site_name) . '</h1>
                    <p style="margin: 0; font-size: 14px; opacity: 0.9;">' . esc_html__('Test Email', 'loghaven-site-logs') . '</p>
                </div>
                <div style="padding: 30px 25px;">
                    <p style="margin: 0 0 12px; font-size: 15px; line-height: 1.6;">' . sprintf(esc_html__('This is a test digest from %1$s.', 'loghaven-site-logs'), esc_html(WSAL_PLUGIN_NAME)) . '</p>
                    <p style="margin: 0; font-size: 14px; line-height: 1.6; color: #3c434a;">' . esc_html__('If you are receiving this email, the digest delivery channel is configured correctly by your web developer or agency.', 'loghaven-site-logs') . '</p>
                </div>
                <div style="background-color: #f6f7f7; padding: 18px 25px; text-align: center; font-size: 12px; color: #646970; border-top: 1px solid #f0f0f1;">
                    <p style="margin: 0;">' . esc_html(sprintf(__('Prepared by %1$s for %2$s.', 'loghaven-site-logs'), $sender, $site_name)) . '</p>
                </div>
            </div>
        </body>
        </html>';

        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $site_name . ' <' . get_option('admin_email') . '>',
        ];

        return (bool) wp_mail($to, $subject, $body, $headers);
    }

    private function render_email_html(string $site_name, array $report, array $analysis, string $agency_name, string $brand_color): string
    {
        $narrative = $analysis['narrative'] ?? [];
        $attention = $analysis['attention'] ?? [];
        $total_events = (int)($analysis['total_events'] ?? 0);
        $period_label = $analysis['period_label'] ?? 'Summary';
        $site_url = home_url();

        $narrative_html = '';
        if (empty($narrative)) {
            $narrative_html = '<p style="color: #646970; font-style: italic;">' . esc_html__('No notable changes during this period.', 'loghaven-site-logs') . '</p>';
        } else {
            $narrative_html .= '<ul style="margin: 0; padding-left: 20px; color: #1d2327; line-height: 1.6;">';
            foreach ($narrative as $item) {
                $narrative_html .= '<li style="margin-bottom: 8px;">' . esc_html($item) . '</li>';
            }
            $narrative_html .= '</ul>';
        }

        $attention_html = '';
        if (!empty($attention)) {
            $attention_html .= '<div style="margin-top: 25px; padding: 15px 20px; background-color: #fef2f2; border-left: 4px solid #dc2626; border-radius: 4px;">';
            $attention_html .= '<h3 style="margin: 0 0 10px; color: #991b1b; font-size: 15px;">' . esc_html__('Security & System Alerts', 'loghaven-site-logs') . '</h3>';
            $attention_html .= '<ul style="margin: 0; padding-left: 20px; color: #7f1d1d; line-height: 1.5;">';
            foreach ($attention as $item) {
                $attention_html .= '<li style="margin-bottom: 6px;">' . esc_html($item['text'] ?? '') . '</li>';
            }
            $attention_html .= '</ul></div>';
        }

        $sender_footer = $agency_name !== '' ? esc_html($agency_name) : WSAL_PLUGIN_NAME;

        return '<!DOCTYPE html>
        <html>
        <head><meta charset="utf-8"></head>
        <body style="font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif; background-color: #f6f7f7; margin: 0; padding: 30px 15px; color: #1d2327;">
            <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; border: 1px solid #dcdcde; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                <div style="background-color: ' . esc_attr($brand_color) . '; padding: 30px 25px; text-align: center; color: #ffffff;">
                    <h1 style="margin: 0 0 8px; font-size: 22px; font-weight: 700; color: #ffffff;">' . esc_html($site_name) . '</h1>
                    <p style="margin: 0; font-size: 14px; opacity: 0.9;">' . esc_html($period_label) . ' • ' . esc_html($total_events) . ' ' . esc_html__('Events Logged', 'loghaven-site-logs') . '</p>
                </div>
                <div style="padding: 30px 25px;">
                    <h2 style="font-size: 16px; margin: 0 0 15px; color: #1d2327; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #f0f0f1; padding-bottom: 8px;">' . esc_html__('Summary of Activity', 'loghaven-site-logs') . '</h2>
                    ' . $narrative_html . '
                    ' . $attention_html . '
                    <div style="margin-top: 30px; text-align: center;">
                        <a href="' . esc_url($site_url) . '" style="display: inline-block; background-color: ' . esc_attr($brand_color) . '; color: #ffffff; text-decoration: none; padding: 10px 22px; border-radius: 6px; font-weight: 600; font-size: 13px;">' . esc_html__('Visit Website', 'loghaven-site-logs') . '</a>
                    </div>
                </div>
                <div style="background-color: #f6f7f7; padding: 18px 25px; text-align: center; font-size: 12px; color: #646970; border-top: 1px solid #f0f0f1;">
                    <p style="margin: 0;">' . esc_html(sprintf(__('Report prepared by %1$s for %2$s.', 'loghaven-site-logs'), $sender_footer, $site_name)) . '</p>
                </div>
            </div>
        </body>
        </html>';
    }
}

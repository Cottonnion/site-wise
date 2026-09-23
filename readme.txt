=== Loghaven Site Logs & Reports ===
Contributors: yahyadeved
Tags: activity log, audit log, user activity, client report, woocommerce activity log
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 8.1
Stable tag: 1.3.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: loghaven-site-logs
Domain Path: /languages

A lightweight WordPress activity log, audit trail, and white-label client reports for agencies, freelancers, and store owners.

== Description ==

**Loghaven Site Logs & Reports** is a lightweight, high-performance activity log and client reporting plugin for WordPress. It records every important change on your website with zero database bloat and transforms raw technical logs into beautiful, client-friendly reports and instant webhook notifications.

Perfect for **freelancers, agencies offering website maintenance care plans, WooCommerce store owners, and site administrators** who need to know exactly who did what, when, and where.

https://wordpress.org/plugins/loghaven-site-logs/

---

### 🚀 Why Loghaven?

* **No Database Bloat:** Single indexed table with strictly enforced automatic retention cleanup (prunes old records automatically on cron).
* **Client-Ready Reports:** Convert dry technical logs into human-readable narrative summaries that prove the value of your monthly maintenance care plans.
* **White-Label Agency Branding:** Customize public client reports with your agency name, logo, brand accent colors, and custom footer notes.
* **WooCommerce & Elementor Ready:** Track WooCommerce order status changes, product updates, and stock levels, alongside Elementor page and template edits.
* **Instant Webhook Alerts:** Receive real-time security alerts in your Slack or Discord channel when critical events occur (such as Administrator role grants, plugin deletions, or login brute-force attempts).
* **Automated Email Digests:** Send scheduled weekly or monthly summary reports straight to your clients' inboxes.
* **100% Standalone & Private:** All data resides on your server in your database. No SaaS subscriptions, no external tracking, no third-party data leaks.

---

### 🔍 What Loghaven Tracks

#### 🛒 WooCommerce Activity Log
* **Orders:** Status changes (Pending, Processing, Completed, Refunded, Cancelled, Failed) with order numbers, customer details, and order amounts.
* **Products:** Creation, modification, deletion, and price updates.
* **Inventory:** Real-time stock level adjustments.
* **Coupons:** Coupon code creation and deletion.

#### 🎨 Elementor & Page Builders
* **Elementor Edits:** Edits made to pages and posts using the Elementor live builder.
* **Elementor Templates:** Header, footer, section, and pop-up template creation and updates.
* **Global Styles:** Elementor Site Settings and Active Kit modifications.

#### 📝 Posts, Pages & Custom Post Types (CPT)
* Creation, updates, deletions, status transitions (draft to publish, private, scheduled).
* Trash and restore events across Posts, Pages, and all registered Custom Post Types (Portfolios, Testimonials, Products, etc.).

#### 👥 Users & Authentication
* Successful logins and logouts.
* Failed login attempts and brute-force burst detection.
* New user registrations and user deletions.
* User role changes (e.g. Subscriber promoted to Administrator).
* Profile updates and email changes.

#### 🔌 Plugins & Themes
* Plugin installations, activations, deactivations, updates, and deletions.
* Theme installations, switches, updates, and deletions.

#### ⚙️ Core & Settings
* WordPress core updates.
* Settings changes on critical options (`siteurl`, `home`, `admin_email`, `users_can_register`, `permalink_structure`) with visual before-and-after comparison.
* Media library uploads and deletions.
* Comments submitted, approved, marked as spam, or deleted.
* Taxonomy categories and tags created or deleted.

---

### 📊 Beautiful Client Reports & White-Labeling

Stop sending clients confusing raw log tables. Loghaven compiles site changes into an elegant, tokenized HTML report:
1. **Human-Readable Narratives:** Automatically translates technical logs into sentences (e.g. *"5 plugins updated, 2 security alerts resolved, 12 new posts published"*).
2. **Visual Charts & Categorized Breakdowns:** Shows activity across Content, Users, Commerce, and Core.
3. **Shareable Secure Links:** Generate one-click passwordless token links to share with clients without granting them wp-admin access.
4. **Agency White-Labeling:** Add your agency logo, brand colors, and custom sign-off message.
5. **Print & PDF Friendly:** Optimized CSS print stylesheet for 1-click browser PDF generation.

---

### 🔔 Instant Slack, Discord & Webhook Notifications

Get notified immediately when high-risk events occur:
* When a user is elevated to Administrator.
* When a plugin is deleted.
* When multiple failed login attempts are triggered from a single IP.
* When core site URLs or administrator emails change.

---

== Installation ==

1. Upload the `loghaven-site-logs` folder to your `/wp-content/plugins/` directory, or install directly via WordPress "Plugins > Add New".
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Access your new **Activity Log** menu in the WordPress dashboard.
4. (Optional) Go to **Activity Log > Settings** to configure your retention period, agency branding, webhook URL, and automated email digest.

== Frequently Asked Questions ==

= Does Loghaven slow down my website? =
No. Loghaven operates exclusively on standard WordPress hooks without blocking main page renders. Log queries are indexed on a dedicated database table, ensuring rapid dashboard loads and minimal server overhead.

= How does Loghaven prevent database bloat? =
Loghaven uses an automatic daily cleanup cron that purges records older than your configured retention threshold (default: 90 days). You can customize this from 1 to 365 days.

= Can I use this for client maintenance reporting? =
Yes! Loghaven was built specifically for web agencies and maintenance freelancers. You can send clients a secure tokenized report link or enable automated weekly/monthly email digests branded with your agency logo and colors.

= Does it support WooCommerce and Elementor? =
Yes. Loghaven includes dedicated trackers for WooCommerce orders, products, inventory, and coupons, as well as Elementor page edits, templates, and global kit modifications.

= Can I export activity logs to CSV? =
Yes. Use the "Export CSV" button on the Activity Log screen to download filtered logs for compliance, security audits, or offline records.

= Where is my data stored? =
100% of your activity data is stored locally in your WordPress database. Nothing is sent to external servers or third-party cloud services.

== Screenshots ==

1. Activity Log dashboard with summary cards, activity timeline, and quick stats.
2. Filterable audit log with event filters, search, and before/after change diffs.
3. White-labeled client maintenance report view with custom branding and narrative summaries.
4. Settings page with retention options, agency white-labeling, email digests, and Slack/Discord webhook alerts.

== Changelog ==

= 1.3.1 =
* Trimmed plugin tags to the WordPress.org limit (max 5) to restore proper categorization.

= 1.3.0 =
* Reworked the Settings page into clean, collapsible sections so configuration is less overwhelming.
* Added "Send Test Email" and "Send Test Webhook" buttons to verify your digest and alert channels instantly.
* Added "Clear Logs" with conditional scope: delete all history or only entries older than 7/30/90/180 days, with an entry-count confirmation dialog.
* Activity Log user column now links to each user's WordPress profile for fast account review.
* Report link copy now shows inline feedback on the button instead of a browser alert.
* Replaced native checkboxes with custom toggle switches and added a media-library logo picker for agency branding.
* Introduced a plugin-name constant so branding is consistent across the codebase.
* Kept the Loghaven admin screens clean by hiding unrelated WordPress admin notices.
* Fixed webhook test delivery, translation load timing for WordPress 6.7+, and centered the admin UI with a 1000px max width.

= 1.2.0 =
* Added WooCommerce tracking: order status changes, product creation/updates/deletion, stock changes, and coupon activity.
* Added Elementor tracking: page builder edits, template updates, and kit settings.
* Added Custom Post Types (CPT) dynamic tracking and trash/restore logging.
* Added White-Label Agency Branding: custom agency name, logo, accent colors, and custom report footers.
* Added Instant Webhook Alerts: receive critical security notifications in Slack, Discord, or custom endpoints.
* Added Automated Scheduled Email Digest for client maintenance reports via cron.
* Added visual before-and-after change diff badges in the Activity Log viewer.
* Expanded keyword metadata, SEO descriptions, and translations.

= 1.1.1 =
* Rebranded plugin to Loghaven Site Logs & Reports (`loghaven-site-logs`).
* Integrated automated WordPress.org SVN release and deployment workflow.

= 1.1.0 =
* Registered activation and deactivation hooks at plugin load so the log table is created on the activation request.
* Enqueued report styles via the WordPress asset API instead of a hard-coded stylesheet link, and scoped all admin styles so they load only on the plugin's own screens.
* Moved the "Tested up to" declaration to the readme and updated it to the current WordPress major version.

= 1.0.0 =
* Initial release.

=== WP Site Activity Log ===
Contributors: yahyaeeddaqqaq, yahyadeved
Tags: activity log, audit log, user activity, site report, logging
Requires at least: 6.0
Tested up to: 7.1.1
Requires PHP: 8.1
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: site-wise
Domain Path: /languages

A clean activity log and shareable site reports for freelancers and agencies.

== Description ==

WP Site Activity Log records notable events on your WordPress site in a fast, searchable history and turns them into shareable reports — built for freelancers and agencies who need a quick, honest view of what's happening on the sites they manage.

= What it does =

* Records posts, users, plugins, themes and settings changes with the actor, IP address and time.
* Single-screen dashboard with live stats: events today, this week and active users.
* Filterable activity log with search, event type, CSV export and pagination.
* Weekly site report summary with a shareable link you can send to clients.
* Configurable retention so old log entries are cleaned up automatically.
* Lightweight: writes only on standard WordPress hooks, no external calls.

= What it tracks =

* Posts and pages — created, updated, deleted, status changed.
* Users — login, logout, failed logins, registered, deleted.
* Plugins — activated, deactivated, installed, updated, deleted.
* Settings and themes — option updates and theme switches.

= Privacy =

All data stays in your own database. Records contain the user who performed the action (or "-" for guests), the IP address, the affected object, event type and timestamp. Log entries are removed automatically after the retention period you choose (90 days by default, configurable), and uninstalling the plugin removes its table and settings completely.

== Installation ==

1. Upload the `site-wise` folder to `/wp-content/plugins/`, or install the plugin through the WordPress "Plugins > Add New" screen.
2. Activate the plugin through the "Plugins" screen.
3. Go to the new "Activity Log" menu item to view the dashboard, log and settings.

The plugin creates its table on activation. No configuration is required to start logging.

== Frequently Asked Questions ==

= Does this slow down my site? =

No. The plugin writes only on standard WordPress hooks when relevant events happen, and log lookups are indexed queries on a dedicated table.

= What data is recorded? =

The event, the object that changed, the username of the person responsible (or "-" for logged-out events such as failed logins), the IP address, and a timestamp. No page content or sensitive passwords are ever stored.

= How long are logs kept? =

By default 90 days. You can change this in "Activity Log > Settings" from 1 to 365 days. Expired entries are automatically deleted.

= What happens to my data when I uninstall? =

The plugin's table and settings option are removed. Uninstalling deletes all stored log data.

= Can I export the log? =

Yes. On the Activity Log screen, use the "Export CSV" button to download the filtered results.

= Where can I get support? =

Report issues on the plugin's WordPress.org support forum or the development repository.

== Screenshots ==

1. Dashboard view with the weekly report, stats and recent activity.

== Changelog ==

= 1.0.0 =
* Initial release.
* Posts, pages, users, plugins, settings and theme tracking.
* Dashboard with daily/weekly stats and recent activity.
* Filterable activity log with search, event filter, pagination and CSV export.
* Shareable weekly report via a public link.
* Configurable log retention with automatic cleanup.
* Clean uninstall that removes the log table and options.
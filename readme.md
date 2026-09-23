# Loghaven Site Logs & Reports

A lightweight WordPress activity log, audit trail, and white-label client reporting suite for freelancers, agencies, and store owners.

## What this plugin tracks

Every tracked event is stored in a dedicated, indexed database table with the user who performed it (or `-` for logged-out events), client IP address, affected object, metadata diff, and timestamp.

### 📝 Posts, Pages & Custom Post Types
| Event code | Description | Severity |
| --- | --- | --- |
| `post.created` | Post, page, or custom post type created | info |
| `post.updated` | Post, page, or custom post type updated | info |
| `post.deleted` | Post, page, or custom post type deleted permanently | warning |
| `post.trashed` | Post moved to trash | warning |
| `post.restored` | Post restored from trash | info |
| `post.status_changed` | Post status changed (draft → publish, etc.) | info |

### 🛒 WooCommerce
| Event code | Description | Severity |
| --- | --- | --- |
| `wc.order_status` | Order status changed (e.g. Pending → Completed, Refunded) | info |
| `wc.product_created` | WooCommerce product created | info |
| `wc.product_updated` | Product updated / price modified | info |
| `wc.product_deleted` | Product deleted | warning |
| `wc.stock_changed` | Product inventory stock level changed | info |
| `wc.coupon_created` | Discount coupon created | info |
| `wc.coupon_deleted` | Discount coupon deleted | warning |

### 🎨 Elementor & Builders
| Event code | Description | Severity |
| --- | --- | --- |
| `elementor.post_edited` | Page or post updated in Elementor editor | info |
| `elementor.template_created` | Elementor template/header/footer created | info |
| `elementor.template_updated` | Elementor template/header/footer updated | info |
| `elementor.template_deleted` | Elementor template deleted | warning |
| `elementor.settings_updated` | Elementor global kit / site settings updated | info |

### 👥 Users & Authentication
| Event code | Description | Severity |
| --- | --- | --- |
| `user.login` | User signed in | info |
| `user.logout` | User signed out | info |
| `user.login_failed` | Failed login attempt | warning |
| `user.registered` | New user registered | info |
| `user.deleted` | User deleted | warning |
| `user.role_changed` | User role changed (e.g. subscriber → administrator) | warning |
| `user.profile_updated` | User profile/email updated | info |

### 🔌 Plugins & Themes
| Event code | Description | Severity |
| --- | --- | --- |
| `plugin.activated` | Plugin activated | info |
| `plugin.deactivated` | Plugin deactivated | info |
| `plugin.updated` | Plugin updated | info |
| `plugin.installed` | Plugin installed | info |
| `plugin.deleted` | Plugin deleted | warning |
| `theme.switched` | Active theme changed | info |
| `theme.installed` | Theme installed | info |
| `theme.updated` | Theme updated | info |
| `theme.deleted` | Theme deleted | warning |
| `core.updated` | WordPress core updated | info |

### 🖼️ Media & Comments
| Event code | Description | Severity |
| --- | --- | --- |
| `media.uploaded` | Media file uploaded | info |
| `media.deleted` | Media file deleted | warning |
| `comment.created` | Comment submitted | info |
| `comment.spammed` | Comment marked as spam | warning |
| `comment.deleted` | Comment deleted | warning |
| `term.created` | Category or tag created | info |
| `term.deleted` | Category or tag deleted | warning |
| `settings.updated` | A WordPress option was updated | info |

---

## Agency Features & Client Reporting

- **White-Label Branding:** Customize the public report with your agency name, logo, custom accent colors, and custom footer note.
- **Narrative Client Summaries:** Converts dry technical logs into human-readable sentences that explain what work was done on the site.
- **Instant Webhooks:** Stream critical security events to Slack, Discord, or generic webhook endpoints.
- **Automated Email Digests:** Send scheduled weekly or monthly maintenance summary emails via WordPress Cron.
- **Visual Diff Badges:** Inspect old vs new values directly in the activity log table for settings, user roles, order statuses, and posts.

## Development & Architecture

- Requires PHP 8.1+, WordPress 6.0+.
- Fully PSR-4 compliant autoloading under `WPSiteActivityLog\`.
- Single-table indexed design with zero external SaaS dependencies.
- Nonce and capability verified AJAX endpoints.

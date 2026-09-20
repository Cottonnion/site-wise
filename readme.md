# WP Site Activity Log

A clean activity log and shareable site reports for freelancers and agencies.

## What this plugin tracks

Every tracked event is stored in a dedicated database table with the user who performed it (or `-` for logged-out events), the IP address, the affected object, and a timestamp.

### Posts & Pages
| Event code | Description | Severity |
| --- | --- | --- |
| `post.created` | Post or page created | info |
| `post.updated` | Post or page updated | info |
| `post.deleted` | Post or page deleted | warning |
| `post.status_changed` | Post status changed (e.g. draft → published) | info |

### Users
| Event code | Description | Severity |
| --- | --- | --- |
| `user.login` | User signed in | info |
| `user.logout` | User signed out | info |
| `user.login_failed` | Failed login attempt | warning |
| `user.registered` | New user registered | info |
| `user.deleted` | User deleted | warning |

### Plugins
| Event code | Description | Severity |
| --- | --- | --- |
| `plugin.activated` | Plugin activated | info |
| `plugin.deactivated` | Plugin deactivated | info |
| `plugin.updated` | Plugin updated | info |
| `plugin.installed` | Plugin installed | info |
| `plugin.deleted` | Plugin deleted | warning |

### Settings & Themes
| Event code | Description | Severity |
| --- | --- | --- |
| `settings.updated` | A WordPress option was updated | info |
| `theme.switched` | Active theme changed | info |

## What is stored per event

- **Event code** — machine-readable identifier (e.g. `post.deleted`).
- **Object type & name** — the type (`post`, `user`, `plugin`, `settings`, `theme`) and the affected object name/ID.
- **User** — username of the actor, or `-` for logged-out events such as failed logins.
- **IP address** — client IP at the time of the event.
- **Timestamp** — when the event happened.
- **Message** — human-readable summary used in reports.

## Data retention & privacy

- Default retention is **90 days**; configurable from 1 to 365 days in the plugin's **Settings** tab. Expired entries are purged automatically on a schedule.
- **No passwords, page content, or sensitive payloads are ever stored** — only event metadata.
- Shareable reports are generated server-side and exposed via a public, unguessable link only while the "Enable Shareable Reports" option is on.
- The log is only visible to users with `manage_options` capability.
- **Uninstall deletes everything**: the plugin drops its table and removes its settings option, leaving no data behind.

## Development

- Requires PHP 8.1+, WordPress 6.0+.
- Composer PSR-4 autoloading, no build step required.
- Admin UI is a single-page interface served over admin AJAX (`wsal_load_view`, `wsal_save_settings`, `wsal_export_csv`), protected by nonces and capability checks.
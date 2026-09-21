# Syncly Site Reports & Event History

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
| `user.role_changed` | User role changed (e.g. promoted to admin) | warning |
| `user.profile_updated` | User profile/account updated | info |

### Plugins
| Event code | Description | Severity |
| --- | --- | --- |
| `plugin.activated` | Plugin activated | info |
| `plugin.deactivated` | Plugin deactivated | info |
| `plugin.updated` | Plugin updated | info |
| `plugin.installed` | Plugin installed | info |
| `plugin.deleted` | Plugin deleted | warning |

### Themes & Core
| Event code | Description | Severity |
| --- | --- | --- |
| `theme.switched` | Active theme changed | info |
| `theme.installed` | Theme installed | info |
| `theme.updated` | Theme updated | info |
| `theme.deleted` | Theme deleted | warning |
| `core.updated` | WordPress core updated | info |

### Media
| Event code | Description | Severity |
| --- | --- | --- |
| `media.uploaded` | Media file uploaded | info |
| `media.deleted` | Media file deleted | warning |

### Comments
| Event code | Description | Severity |
| --- | --- | --- |
| `comment.created` | Comment added | info |
| `comment.spammed` | Comment marked as spam | warning |
| `comment.deleted` | Comment deleted | warning |

### Taxonomies
| Event code | Description | Severity |
| --- | --- | --- |
| `term.created` | Category or tag created | info |
| `term.deleted` | Category or tag deleted | warning |

### Settings
| Event code | Description | Severity |
| --- | --- | --- |
| `settings.updated` | A WordPress option was updated | info |

## What is stored per event

- **Event code** — machine-readable identifier (e.g. `post.deleted`).
- **Object type & name** — the type (`post`, `user`, `plugin`, `theme`, `core`, `media`, `comment`, `term`, `settings`) and the affected object name/ID.
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
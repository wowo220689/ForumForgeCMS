# Changelog

## ForumForgeCMS 1.4

### Added

- Persistent, atomic SQLite login protection with account and IP counters and temporary 15-minute lockouts.
- Shared account protection for username/email sign-in, unaffected by new browser sessions.
- Public, members-only, staff-only, and administrator-only hidden forum visibility.
- Separate read-only forum mode, with server-side enforcement for creating topics, replying, and editing.
- Permission-aware search, recent activity, profile activity, content counters, moderation, and direct content access.
- Paginated on-site notifications with unread counts, individual opening, and mark-all-read actions.
- Reply notifications for topic authors and previous participants, private-message notifications, and staff report notifications.
- Permission checks when notifications are viewed; closed report notifications disappear for all staff.
- Public profile fields: about me, location, website, and a plain-text signature shown below posts.
- Administrator editing of public profile fields in user management.
- PL/EN/DE translations for the new features and the topic description.
- Regression coverage for the role/visibility matrix, forged writes, profile validation, notification ownership, and persistent login locks.
- HTTP workflow tests and a 16-process test of atomic login attempt reservations.

### Security And Behavior

- Profile and signature output is HTML-escaped; external links allow only HTTP/HTTPS without embedded credentials.
- Notification links resolve to the matching post and its pagination page.
- Opening a private message also marks its notification read.
- Successful login and completed password reset clear the account's login failure state.
- Existing SMTP/mail behavior is unchanged. No database migration workflow is included.

## ForumForgeCMS 1.3

### Added

- Dedicated administrator Spam protection section, localized in English, Polish, and German.
- Optional arithmetic registration question, enabled by default, with session-bound one-use tokens and a ten-minute expiry.
- Validated email domain block lists, including subdomains.
- Exact, case-insensitive blocked usernames for new registrations.
- Canonical IPv4/IPv6 block lists with HTTP 403 enforcement on forum, avatar, and logo endpoints.
- Protection against blocking the primary administrator name or the administrator's current IP address.
- Isolated SQLite regression tests and HTTP smoke tests for registration, permissions, and CSRF.

### Improved

- Replaced whole-page HTML string replacement with explicit UTF-8 PL/EN/DE translation dictionaries.
- Localized labels, validation errors, confirmations, editor and image-processing prompts, and system email templates at their point of use.
- Preserved user-authored text, usernames, and custom settings during interface translation.
- Retained language switching for unmodified starter content and browser-language selection on first installation.
- Corrected Polish diacritics in older error messages.
- Detected browsers that return a non-WebP format during client-side image conversion.

### Distribution

- Clean-install release archive excludes screenshots, development tests, runtime data, and credentials.
- No migration or SMTP changes introduced.

## ForumForgeCMS 1.2

ForumForgeCMS 1.2 focuses on making the forum easier to use as content grows.

### Added

- Built-in forum search available from the top navigation.
- Topic title search using SQLite `LIKE`.
- Post body search using SQLite `LIKE`.
- Search results with forum name, topic title, author, date, and a content excerpt.
- Direct links from post search results to the matching post, including the correct paginated topic page.
- Search result limits and pagination.
- English, Polish, and German translations for the search interface.

### Improved

- Expanded English and German translations across the administrator panel.
- Improved translations for post reports, user management, footer text, form confirmations, and system messages.
- Role labels now follow the selected forum language instead of relying on generic word replacement.
- HTML attribute translation now covers common interface attributes such as `aria-label`, `alt`, `placeholder`, `title`, and confirmation prompts.

### Fixed

- Fixed mixed-language strings such as partially translated Polish grammar forms in the English interface.
- Fixed missing translations visible in the administrator panel after switching the forum language.

## ForumForgeCMS 1.1

### Added

- Visible ForumForgeCMS version information in the administrator panel.
- Administrator backup and restore section.
- Downloadable ZIP backups containing the SQLite database, avatars, and custom forum logo.
- Backup restore support from the administrator panel for moving forums between hosting environments.
- Localized theme names for English, Polish, and German.

## ForumForgeCMS 1.0

### Added

- Initial standalone PHP forum CMS release.
- SQLite-based automatic first-run installation.
- Default administrator account setup.
- Forum categories, forums, topics, replies, likes, private messages, profiles, avatars, and moderation tools.
- Client-side WebP conversion for avatars and forum logos.
- Multiple visual styles and multilingual interface support.

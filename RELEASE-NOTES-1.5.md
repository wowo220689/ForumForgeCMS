# ForumForgeCMS 1.5

ForumForgeCMS 1.5 brings five practical improvements for running a standalone forum on classic PHP hosting: hosting diagnostics, maintenance mode, SEO and sharing metadata, self-service account deletion, and activity statistics. The lightweight PHP + SQLite architecture remains unchanged, with no external production services or build tools required.

## Hosting Diagnostics

- New administrator page checks PHP 8.1+, PDO SQLite, sessions, SQLite quick integrity and foreign keys, writable storage directories, mail() availability, and Apache/IIS protection-file presence.
- A same-origin browser test checks a random disposable file inside `forum-data/`; it never requests the live database or generated administrator password.
- Blocked, exposed and inconclusive outcomes are clearly distinguished. Temporary probe files are cleaned after the check, with stale-file cleanup on subsequent checks.
- These checks assist deployment; they do not certify the server as secure. Verify database, password, WAL and SHM protection separately. Nginx needs a server-level deny rule. The PHP built-in server does not enforce `.htaccess`.

## Maintenance Mode

- Administrators retain forum and panel access while visitors and moderators receive HTTP 503, `Retry-After: 3600`, no-store caching and noindex directives.
- The sign-in page stays available. Only sign-in/sign-out POST actions are permitted for non-administrators; posting to the login URL cannot bypass maintenance.
- Avatar and logo endpoints are gated as well. Static assets and the static terms page are outside this application-level gate.
- Choose the translated default visitor message or an escaped custom message of up to 1,000 characters.

## SEO And Sharing

- Configure the public forum URL, including its subdirectory, in the new **SEO and sharing** administrator section.
- Canonical and Open Graph URLs use this configured origin, not the request Host header. Public topic/forum pagination is retained; quote, edit, token and tracking parameters are omitted.
- Public topics receive first-post excerpts; Open Graph metadata includes title, description, locale, type, forum name and a custom logo when available.
- Restricted content has no public sharing excerpt. Private/account/search/administration pages are marked noindex. Absolute canonicals are omitted until a public base URL is configured.

## Self-Service Account Deletion

- Members and moderators can delete their own account using their current password and an explicit retention-policy confirmation.
- The account identity, profile fields, avatar, private messages, likes, submitted reports, reset tokens and related notifications are removed. Administrator accounts cannot self-delete.
- Existing discussions remain under a shared non-login anonymous account so other authors' replies are not lost. The anonymous account is excluded from member counts, rankings and new-account statistics.
- Retained post text, quotations, other users' content and existing backups are not automatically redacted. This limitation is shown before consent; site operators remain responsible for content-removal requests and backup retention.

## Activity Statistics

- Administrator-only totals and daily tables for new accounts, topics and posts.
- Inclusive date filters, a last-30-days default, and a maximum 366-day range.
- Europe/Berlin calendar boundaries handle daylight-saving changes; indexed SQLite date queries stream the matching timestamps.
- Statistics reflect currently retained records, not visits, external analytics or an immutable audit history.

## Localization And Verification

All new screens, consent text, diagnostics and validation messages are translated into English, Polish and German with UTF-8 support. Custom messages and user-authored content remain unchanged when the interface language switches.

Verification included 4,794 isolated PHP/SQLite assertions, 79 combined HTTP workflow checks (including backup download), syntax checks of 28 PHP files, JavaScript syntax validation, and a 16-process login-reservation test with exactly eight allowed and eight blocked attempts. Browser checks covered the new administrator screens, the HTTP protection warning, and light/dark form readability. These are local test results, not a production-hosting certification.

The release ZIP excludes live data, generated passwords, private credentials, screenshots and development tests. Tests remain available in the source repository.

## Installation

Download **ForumForgeCMS-1.5.zip**, extract it, upload the contents to your PHP hosting directory, and open `index.php`. Use PHP 8.1 or newer, PDO SQLite, PHP sessions and a writable `forum-data/` directory. The initial administrator username is `admin`; read the generated password from `forum-data/admin-initial-password.txt` using trusted hosting access, then change it and complete the administrator profile.

This is a **clean-install release**. No migration from earlier database schemas is included. Use backups made by the same release; after restoring on another host, review the canonical URL, maintenance setting and IP blocks. PHP `mail()` delivery and the existing proprietary free-use license remain unchanged.

The accompanying `.sha256` file allows verification of the downloadable ZIP.

Live demo: [wowo89.de/forge](https://wowo89.de/forge)  
Project: [wowo220689/ForumForgeCMS](https://github.com/wowo220689/ForumForgeCMS)  
Author: [Piotr Zawalka](https://zawalka.com)

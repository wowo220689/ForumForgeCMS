# ForumForgeCMS 1.3

ForumForgeCMS 1.3 focuses on dependable multilingual interfaces and practical registration protection for classic PHP hosting. It keeps the same standalone PHP + SQLite deployment model, with no external services or new production dependencies.

## Highlights

### Explicit English, Polish, and German Localization

The interface now uses separate UTF-8 dictionaries instead of replacing words across the final HTML document. Labels, validation errors, confirmation dialogs, editor prompts, image-processing errors, and system emails are translated where they are generated. User-authored text and custom descriptions remain intact. Unmodified starter categories, topics, and descriptions still follow the administrator's selected language.

### Administrator Spam Protection

A new **Spam protection** page provides:

- A registration arithmetic question, enabled by default, validated by the server using a session-bound, one-use token that expires after 10 minutes.
- Blocked email domains, including subdomains.
- Exact, case-insensitive blocked usernames for new registrations.
- Individual IPv4 and IPv6 blocks returning HTTP 403 for the forum and its avatar/logo endpoints.
- Validation of all lists before saving, deduplication, and safeguards against blocking the primary administrator name or the administrator's current IP.

Existing registration rate limits and CSRF protection remain active. These are basic anti-spam controls, not a guarantee against automated abuse. IP checks rely on `REMOTE_ADDR`; reverse-proxy users should configure the web server's trusted real-IP handling. Username/domain blocks do not retroactively disable existing accounts.

## Installation

Download **ForumForgeCMS-1.3.zip**, extract it, upload its contents to a PHP hosting directory, and open `index.php`. PHP 8.1+, PDO SQLite, sessions, and a writable `forum-data/` directory are required. The initial administrator login is `admin`; the generated password is saved in `forum-data/admin-initial-password.txt`.

The archive is intended for clean installation. It contains no live database, administrator password, private credentials, screenshots, or development tests. No migration procedure is provided. A SHA-256 checksum accompanies the ZIP.

## Validation

Regression coverage includes dictionary completeness and placeholder consistency, UTF-8 encoding, PL/EN/DE administrator views, user-content preservation, registration challenges, block-list validation, HTTP registration flows, permissions, and CSRF. PHP and JavaScript syntax checks are included in release verification.

The visitor's browser controls native file chooser labels. User-created posts and the site owner's static terms document are not automatically translated.

## Links

- [Live demo](https://wowo89.de/forge) (may run a different version)
- [Documentation](https://github.com/wowo220689/ForumForgeCMS#readme)
- [Complete changelog](https://github.com/wowo220689/ForumForgeCMS/blob/main/CHANGELOG.md)

The existing free-use, no-modification license and author attribution requirements remain unchanged. See `LICENSE` for full terms.

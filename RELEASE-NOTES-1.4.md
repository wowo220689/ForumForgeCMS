# ForumForgeCMS 1.4

ForumForgeCMS 1.4 adds persistent login protection, forum-level access rules, on-site notifications, and richer public profiles. It keeps the lightweight standalone PHP + SQLite architecture and introduces no external production services or build tools.

## Persistent Login Protection

Login attempts are tracked in SQLite, independently of browser sessions. Atomic reservations prevent parallel requests from bypassing the limit. Eight failed account attempts or thirty failed IP attempts within the protection window trigger a temporary 15-minute lockout. Username and email sign-in share an account counter. Successful authentication and completed password resets clear account failures.

The server stores hashed keys and counters rather than plain account/IP identifiers. IP handling uses `REMOTE_ADDR`; installations behind a reverse proxy must configure trusted real-IP handling on the web server. Other existing action rate limits remain session-based.

## Forum Access Rules

Administrators can set each forum to **Public**, **Members only**, **Moderators and administrators**, or **Hidden (administrators only)**. A separate **Read-only for members** option prevents ordinary members from creating topics, replying, or editing posts while retaining staff writing access where visibility allows it.

These rules are enforced by the server across direct links, search, recent activity, public profile activity, content counters, reports, and notifications. They are not visual-only restrictions. Likes and reports remain available to signed-in readers.

## Notifications

The account area includes an on-site notification inbox with an unread counter, twenty entries per page, direct opening, and a mark-all-read action.

- Replies notify the topic author and earlier participants, excluding the author of the new reply.
- Private messages notify their recipient.
- Reports notify staff who have access to the affected forum.
- Closing a report removes its report notifications for all moderators and administrators.
- Visibility and ownership are rechecked when a notification is listed or opened.
- Reply links point to the exact post and the correct paginated topic page.

This is an on-site system; it does not add email or push subscriptions.

## Extended Public Profiles

Members can edit **About me**, **Location**, **Website**, and **Signature** in My account. Administrators can moderate the same fields from user management. Plain-text signatures appear below the author's posts. Text is HTML-escaped, and website links accept only HTTP/HTTPS addresses without embedded credentials.

All new controls, notification labels, and validation messages are translated into English, Polish, and German, including Polish diacritics and German umlauts.

## Verification

The release includes isolated PHP/SQLite regression tests, HTTP workflow checks, and a parallel-process test of login reservations. Coverage includes guest/member/moderator/administrator visibility, read-only writes, restricted-content discovery, profile escaping, notification ownership and pagination, role changes, closed reports, and login protection across new sessions. These are functional/concurrency checks, not a full-scale hosting load benchmark.

## Installation And Compatibility

Download **ForumForgeCMS-1.4.zip**, extract it, upload its contents to a PHP hosting directory, and open `index.php`. PHP 8.1+, PDO SQLite, sessions, and a writable `forum-data/` directory are required. The initial administrator login is `admin`; the generated password is stored in `forum-data/admin-initial-password.txt`.

Version 1.4 follows the project's clean-install workflow. No migration from an earlier database schema is supplied, and older-schema backups are not supported by this workflow. Backups created by 1.4 include its new tables and profile/permission settings. Existing PHP `mail()` delivery and licensing remain unchanged.

The installation ZIP excludes live databases, credentials, initial passwords, development tests, and screenshots. A SHA-256 checksum is provided alongside the archive.

- [Documentation](https://github.com/wowo220689/ForumForgeCMS#readme)
- [Complete changelog](https://github.com/wowo220689/ForumForgeCMS/blob/main/CHANGELOG.md)
- [Live demo](https://wowo89.de/forge) (may run a different version)

The existing free-use, no-modification license and author attribution requirements remain in effect. See `LICENSE` for full terms.

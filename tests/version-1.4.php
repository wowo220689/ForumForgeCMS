<?php
declare(strict_types=1);

$pdo = forum_db();
foreach (['member14', 'mod14', 'other14'] as $name) {
    forum_register_user($name, $name . '@safe.org', 'Testing-password-123', true);
}
$pdo->exec('UPDATE users SET role="moderator" WHERE username="mod14"');
$member = $pdo->query('SELECT * FROM users WHERE username="member14"')->fetch();
$moderator = $pdo->query('SELECT * FROM users WHERE username="mod14"')->fetch();
$other = $pdo->query('SELECT * FROM users WHERE username="other14"')->fetch();
$tester = $pdo->query('SELECT * FROM users WHERE username="tester"')->fetch();
$admin = $pdo->query('SELECT * FROM users WHERE id=1')->fetch();
$fixtures = [];
foreach (['public', 'members', 'staff', 'hidden', 'readonly'] as $mode) {
    forum_create_category('AccessMarker-' . $mode, 'Access testing', 1, $mode === 'readonly' ? 'public' : $mode, $mode === 'readonly');
    $categoryId = (int) $pdo->lastInsertId();
    $id = forum_create_topic($pdo, $categoryId, 1, 'AccessMarker-' . $mode, 'AccessMarker-content-' . $mode);
    $post = $pdo->query('SELECT id FROM posts WHERE topic_id=' . $id)->fetchColumn();
    $fixtures[$mode] = ['category' => $categoryId, 'topic' => $id, 'post' => (int) $post];
}
rejects(fn() => forum_permission_values('unexpected', false), 'invalid visibility rejected');
foreach (['guest' => null, 'member' => $member, 'moderator' => $moderator, 'admin' => $admin] as $role => $actor) {
    $_SESSION = $actor ? ['forum_user_id' => (int) $actor['id']] : [];
    $visible = 0;
    foreach ($fixtures as $mode => $item) {
        $read = $role === 'admin' || in_array($mode, ['public', 'readonly'], true)
            || ($mode === 'members' && $role !== 'guest') || ($mode === 'staff' && $role === 'moderator');
        $write = $read && $role !== 'guest' && ($mode !== 'readonly' || in_array($role, ['moderator', 'admin'], true));
        $visible += $read ? 1 : 0;
        check((forum_fetch_category($item['category']) !== null) === $read, 'category visibility ' . $role . ':' . $mode);
        check((forum_fetch_topic($item['topic']) !== null) === $read, 'topic visibility ' . $role . ':' . $mode);
        check((forum_ext_fetch_topic($item['topic']) !== null) === $read, 'extended topic visibility ' . $role . ':' . $mode);
        check((forum_ext_fetch_post($item['post']) !== null) === $read, 'post visibility ' . $role . ':' . $mode);
        check((forum_fetch_topics_for_category($item['category']) !== []) === $read, 'topic list visibility');
        check((forum_ext_fetch_posts_for_topic($item['topic']) !== []) === $read, 'post list visibility');
        $ids = array_column(forum_fetch_categories(), 'id');
        check(in_array($item['category'], array_map('intval', $ids), true) === $read, 'home category visibility');
        $recentIds = array_map('intval', array_column(forum_recent_topics(100), 'id'));
        check(in_array($item['topic'], $recentIds, true) === $read, 'recent topic visibility');
        $profileIds = array_map('intval', array_column(forum_ext_recent_topics_for_user(1, 100), 'id'));
        check(in_array($item['topic'], $profileIds, true) === $read, 'profile topic visibility');
        $profilePostIds = array_map('intval', array_column(forum_ext_recent_posts_for_user(1, 100), 'id'));
        check(in_array($item['post'], $profilePostIds, true) === $read, 'profile post visibility');
        if ($write) {
            forum_create_post($item['topic'], (int) $actor['id'], 'Allowed permission test reply');
            check(true, 'authorized reply');
        } else {
            rejects(fn() => forum_create_post($item['topic'], (int) ($actor['id'] ?? 0), 'Denied permission test reply'), 'unauthorized reply denied');
            rejects(fn() => forum_create_topic($pdo, $item['category'], (int) ($actor['id'] ?? 0), 'Denied topic', 'Denied topic content'), 'unauthorized topic denied');
        }
        if (!$read) {
            rejects(fn() => forum_ext_quote_text_for_post($item['post']), 'quote does not expose content');
        }
    }
    check(forum_ext_count_search_results('AccessMarker') === $visible * 2, 'search count: ' . $role);
    check(count(forum_ext_search_results('AccessMarker', 1, 100)) === $visible * 2, 'search results: ' . $role);
    $expectedTopics = 6 + $visible;
    check(forum_ext_admin_summary()['topics'] === $expectedTopics, 'visible topic total: ' . $role);
    check((int) forum_ext_fetch_user_profile(1)['topic_count'] === $expectedTopics, 'visible profile topic total');
}
$_SESSION = ['forum_user_id' => (int) $moderator['id']];
rejects(fn() => forum_delete_topic($fixtures['hidden']['topic']), 'moderator cannot delete admin-only topic');
rejects(fn() => forum_toggle_topic_flag($fixtures['hidden']['topic'], 'is_pinned'), 'moderator cannot pin admin-only topic');

$_SESSION = ['forum_user_id' => (int) $member['id']];
$ownedTopic = forum_create_topic($pdo, $fixtures['public']['category'], (int) $member['id'], 'Owned post', 'Owned post body for editing');
$ownedPost = (int) $pdo->query('SELECT id FROM posts WHERE topic_id=' . $ownedTopic)->fetchColumn();
$pdo->exec('UPDATE categories SET read_only=1 WHERE id=' . $fixtures['public']['category']);
rejects(fn() => forum_ext_update_post_with_staff($ownedPost, $member, 'changed body', 'Changed title'), 'read-only edit denied to owner');
$pdo->exec('UPDATE categories SET read_only=0 WHERE id=' . $fixtures['public']['category']);

$profileInput = ['about_me' => 'Łódź, Größe, <script>alert(1)</script>', 'location' => 'Gdańsk / Köln', 'website' => 'https://example.com/?a=1&b=2', 'signature' => '<img src=x onerror=alert(1)>'];
forum_profile_save((int) $member['id'], $profileInput);
$savedProfile = forum_ext_fetch_user_profile((int) $member['id']);
check($savedProfile['about_me'] === $profileInput['about_me'], 'profile UTF-8 round trip');
$details = capture(fn() => forum_profile_render_details($savedProfile));
check(str_contains($details, '&lt;script&gt;') && !str_contains($details, '<script>'), 'profile escapes HTML');
check(str_contains($details, 'nofollow ugc'), 'external profile links marked');
foreach (['javascript:alert(1)', '//example.com', 'data:text/html,hello', 'https://user:pass@example.com'] as $url) {
    rejects(fn() => forum_profile_save((int) $member['id'], ['website' => $url]), 'unsafe website rejected');
}
rejects(fn() => forum_profile_save((int) $member['id'], ['signature' => str_repeat('x', 301)]), 'signature length validated');
check(forum_ext_fetch_user_profile((int) $member['id'])['about_me'] === $profileInput['about_me'], 'profile validation atomic');

$pdo->exec('DELETE FROM notifications');
$notificationTopic = forum_create_topic($pdo, $fixtures['public']['category'], 1, 'Notification test', 'Initial notification test content');
forum_create_post($notificationTopic, (int) $member['id'], 'First notification test reply');
$replyId = (int) $pdo->lastInsertId();
check(forum_notifications_count($admin, true) === 1, 'reply notifies author');
check(forum_notifications_count($member, true) === 0, 'reply does not notify self');
$_SESSION = ['forum_user_id' => (int) $tester['id']];
forum_create_post($notificationTopic, (int) $tester['id'], 'Another participant reply');
check(forum_notifications_count($member, true) === 1, 'reply notifies earlier participant');
$lastPost = (int) $pdo->query('SELECT MAX(id) FROM posts WHERE topic_id=' . $notificationTopic)->fetchColumn();
forum_notify_reply($pdo, $notificationTopic, $lastPost, (int) $tester['id']);
check(forum_notifications_count($member, true) === 1, 'duplicate reply notification avoided');
$notice = forum_notifications_fetch($member)[0];
rejects(fn() => forum_notification_open((int) $notice['id'], $other), 'another user cannot open notification');
$pdo->exec('UPDATE categories SET visibility="staff" WHERE id=' . $fixtures['public']['category']);
check(forum_notifications_count($member) === 0, 'permission change hides old notifications');
rejects(fn() => forum_notification_open((int) $notice['id'], $member), 'permission checked again on open');
$pdo->exec('UPDATE categories SET visibility="public" WHERE id=' . $fixtures['public']['category']);
$target = forum_notification_open((int) $notice['id'], $member);
check(str_contains($target, '#post-' . $lastPost), 'notification links to exact reply');
check(forum_notifications_count($member, true) === 0, 'notification marked read');
for ($i = 0; $i < 22; $i++) { forum_create_post($notificationTopic, (int) $tester['id'], 'Pagination notification reply'); }
$lastPost = (int) $pdo->query('SELECT MAX(id) FROM posts WHERE topic_id=' . $notificationTopic)->fetchColumn();
check(str_contains(forum_post_url($notificationTopic, $lastPost), 'page=2'), 'notification links to correct page');
check(count(forum_notifications_fetch($member)) === 20, 'notification pagination limit');
forum_notifications_mark_all($member);
check(forum_notifications_count($member, true) === 0, 'mark all own notifications read');
check(forum_notifications_count($admin, true) > 0, 'mark all leaves other users alone');

$messageId = forum_ext_send_message((int) $tester['id'], $member['username'], 'Private notification', 'Private message content');
$messageNotice = array_values(array_filter(forum_notifications_fetch($member), static fn($n) => $n['kind'] === 'message'))[0];
check(str_contains(forum_notification_open((int) $messageNotice['id'], $member), 'view=message'), 'private message target');
$pdo->exec('UPDATE private_messages SET deleted_by_recipient=1 WHERE id=' . $messageId);
rejects(fn() => forum_notification_open((int) $messageNotice['id'], $member), 'deleted message notification unavailable');
forum_ext_report_post($lastPost, (int) $member['id']);
$reportId = (int) $pdo->query('SELECT MAX(id) FROM post_reports')->fetchColumn();
check((int) $pdo->query('SELECT COUNT(*) FROM notifications WHERE report_id=' . $reportId)->fetchColumn() === 2, 'report notifies all eligible staff');
$pdo->exec('UPDATE users SET role="member" WHERE id=' . (int) $moderator['id']);
$demoted = forum_permission_user((int) $moderator['id']);
check(!array_filter(forum_notifications_fetch($demoted), static fn($n) => $n['kind'] === 'report'), 'demoted moderator loses report notifications');
$pdo->exec('UPDATE users SET role="moderator" WHERE id=' . (int) $moderator['id']);
forum_ext_close_post_report($reportId, 1);
check((int) $pdo->query('SELECT COUNT(*) FROM notifications WHERE report_id=' . $reportId)->fetchColumn() === 0, 'closed report disappears for all staff');

foreach (['pl', 'en', 'de'] as $language) {
    forum_ext_store_setting($pdo, 'forum_language', $language);
    forum_ext_current_language();
    $_SESSION = ['forum_user_id' => (int) $member['id']];
    $html = capture(fn() => forum_render_notifications($member, null));
    check(str_contains($html, forum_escape(forum_t('notifications.title'))), 'notifications localized: ' . $language);
    check(!preg_match('/(?:notifications|profile|permissions)\.[a-z_]+/', strip_tags($html)), 'no untranslated new keys');
    $html = capture(fn() => forum_profile_render_form($savedProfile));
    check(str_contains($html, forum_escape(forum_t('profile.about_me'))), 'profile localized');
    check(str_contains($html, '&lt;img'), 'signature escaped in form');
}

$pdo->exec('DELETE FROM login_guards');
for ($i = 0; $i < FORUM_LOGIN_ACCOUNT_LIMIT; $i++) {
    $_SESSION = [];
    rejects(fn() => forum_login_user('tester', 'incorrect-password'), 'failed login counted across sessions');
}
$_SERVER['REMOTE_ADDR'] = '192.0.2.14';
try { forum_login_user('tester@safe.org', 'Testing-password-123'); check(false, 'locked login allowed'); }
catch (RuntimeException $e) { check($e->getMessage() === forum_t('security.login_locked'), 'account lock covers aliases and another IP'); }
$resetToken = bin2hex(random_bytes(24));
$selector = bin2hex(random_bytes(12));
$pdo->prepare('INSERT INTO password_reset_tokens (user_id, selector, token_hash, created_at, expires_at) VALUES (:user, :selector, :hash, :created, :expires)')->execute([
    ':user' => (int) $tester['id'], ':selector' => $selector, ':hash' => password_hash($resetToken, PASSWORD_DEFAULT),
    ':created' => forum_now(), ':expires' => (new DateTimeImmutable('+1 hour'))->format('Y-m-d H:i:s'),
]);
forum_ext_reset_password_with_token($selector, $resetToken, 'Testing-password-123');
check(!$pdo->query('SELECT 1 FROM login_guards WHERE key_hash="' . forum_security_account_key('', $tester) . '"')->fetchColumn(), 'password reset clears account lock');
forum_login_user('tester', 'Testing-password-123');
check((int) ($_SESSION['forum_user_id'] ?? 0) === (int) $tester['id'], 'reset account can log in');
for ($i=0; $i < FORUM_LOGIN_ACCOUNT_LIMIT; $i++) { forum_security_login_reserve('tester', $tester); }
$pdo->exec('UPDATE login_guards SET blocked_until=' . (time() - 1) . ', window_started=' . (time() - 1000));
forum_login_user('tester', 'Testing-password-123');
check((int) ($_SESSION['forum_user_id'] ?? 0) === (int) $tester['id'], 'login works after lock expires');
check(!$pdo->query('SELECT 1 FROM login_guards WHERE key_hash="' . forum_security_account_key('', $tester) . '"')->fetchColumn(), 'successful login clears account failures');
$pdo->exec('DELETE FROM login_guards');
for ($i = 0; $i < FORUM_LOGIN_IP_LIMIT; $i++) { forum_security_login_reserve('unknown' . $i, null); }
rejects(fn() => forum_security_login_reserve('another-account', null), 'IP spray block spans different account names');
$pdo->exec('DELETE FROM login_guards');
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SESSION = ['forum_user_id' => 1];

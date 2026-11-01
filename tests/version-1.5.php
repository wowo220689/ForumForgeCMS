<?php
declare(strict_types=1);

$_SESSION = ['forum_user_id' => 1];
$_GET = [];
http_response_code(200);
check(FORUM_VERSION === '1.5', 'version 1.5');
check(forum_ext_setting('maintenance_enabled') === '0', 'maintenance off on clean install');
check(forum_ext_setting('site_url') === '', 'no untrusted initial canonical origin');
foreach (['https://example.com/forum/', 'http://localhost:8155'] as $url) {
    check(forum_seo_validate_url($url) === rtrim($url, '/'), 'valid canonical base');
}
foreach (['javascript:alert(1)', 'https://user:pass@example.com', 'https://example.com/?q=x', 'https://example.com/#fragment', 'https://example.com/index.php', 'https://example.com/other.php', 'https://example.com\\@evil.test', "https://example.com/\r\nX-Test:1"] as $url) {
    rejects(fn() => forum_seo_validate_url($url), 'unsafe canonical URL rejected');
}
forum_operations_save('seo', ['site_url' => 'https://example.com/forum/']);
$_SERVER['HTTP_HOST'] = 'attacker.invalid';
foreach (['public', 'members', 'staff', 'hidden'] as $mode) {
    $_GET = ['view' => 'topic', 'id' => $fixtures[$mode]['topic'], 'quote_post' => 999, 'token' => 'SecretToken'];
    $meta = forum_seo_metadata('Ignored private heading', 'Ignored private description');
    check($meta['index'] === ($mode === 'public'), 'SEO visibility: ' . $mode);
    check(!str_contains(json_encode($meta), 'SecretToken') && !str_contains(json_encode($meta), 'attacker.invalid'), 'SEO query and Host isolation');
    if ($mode === 'public') {
        check($meta['url'] === 'https://example.com/forum/index.php?view=topic&id=' . $fixtures[$mode]['topic'], 'public canonical allowlist');
        check(str_contains($meta['description'], 'AccessMarker-content-public'), 'topic first-post excerpt');
    } else {
        check($meta['url'] === '' && !str_contains($meta['description'], 'AccessMarker'), 'restricted metadata has no body or canonical');
    }
}
$_GET = ['view' => 'topic', 'id' => $notificationTopic, 'page' => 2];
check(str_ends_with(forum_seo_metadata('', '')['url'], '&page=2'), 'paginated canonical');
$_GET = ['view' => 'topic', 'id' => 1, 'page' => 999999];
check(!str_contains(forum_seo_metadata('', '')['url'], 'page='), 'canonical normalizes out-of-range page to rendered page');
foreach (['account', 'search', 'login', 'admin', 'user', 'notifications', 'message'] as $view) {
    $_GET = ['view' => $view];
    check(!forum_seo_metadata('private title', 'private body')['index'], 'private view noindex: ' . $view);
}
$_GET = ['view' => 'topic', 'id' => $fixtures['public']['topic']];
http_response_code(404);
check(!forum_seo_metadata('', '')['index'], '404 not indexed');
http_response_code(200);
check(forum_seo_excerpt('[b]Hello[/b] [quote=user]private quote[/quote] <script>bad</script> world') === 'Hello bad world', 'plain SEO excerpt excludes quotes and markup');
check(forum_seo_excerpt(str_repeat('ą', 161)) === str_repeat('ą', 160) . '…', 'UTF-8 SEO truncation does not require mbstring');

forum_operations_save('maintenance', ['maintenance_enabled' => '1', 'maintenance_message' => '<script>Not executed</script> Łódź / Größe']);
foreach (['home', 'topic', 'search', 'account', 'avatar', 'logo', 'admin'] as $view) {
    foreach ([null, $member, $moderator] as $actor) { check(forum_maintenance_blocks($actor, $view, 'GET'), 'maintenance non-admin blocked'); }
    check(!forum_maintenance_blocks($admin, $view, 'GET'), 'maintenance admin retained access');
}
check(!forum_maintenance_blocks(null, 'login', 'GET'), 'login page remains available');
foreach (['login', 'logout'] as $action) { check(!forum_maintenance_blocks(null, 'home', 'POST', $action), 'authentication submission remains available'); }
foreach (['register', 'create_post', 'send_message', 'update_settings', 'delete_own_account'] as $action) { check(forum_maintenance_blocks($member, 'login', 'POST', $action), 'no maintenance POST bypass: ' . $action); }
$html = capture(fn() => forum_maintenance_render());
check(http_response_code() === 503 && str_contains($html, '&lt;script&gt;') && !str_contains($html, '<script>Not executed'), '503 escaped maintenance page');
http_response_code(200);
rejects(fn() => forum_operations_save('maintenance', ['maintenance_message' => str_repeat('ą', 1001)]), 'maintenance length limit');
rejects(fn() => forum_operations_save('maintenance', ['maintenance_message' => "\xff"]), 'maintenance UTF-8 validation');
forum_operations_save('maintenance', []);
check(!forum_maintenance_blocks($member, 'home', 'GET'), 'maintenance disabled');
$probe = forum_hosting_probe();
check(preg_match('~^forum-data/hosting-check-[a-f0-9]{32}\.txt$~D', $probe['url']) === 1, 'random non-sensitive probe path');
check(is_file($fixture . '/' . $probe['url']), 'probe created');
forum_hosting_probe_cleanup();
check(!is_file($fixture . '/' . $probe['url']), 'probe removed');
foreach (forum_hosting_checks() as [$key, $ok]) {
    if ($key !== 'hosting.rules') { check($ok, 'runtime hosting check: ' . $key); }
}

foreach ([['2026-02-30', '2026-03-02'], ['2026-05-02', '2026-05-01'], ['2024-01-01', '2026-01-01'], ['not-date', '2026-01-01']] as [$from, $to]) {
    rejects(fn() => forum_statistics_range($from, $to), 'invalid statistics range');
}
rejects(fn() => forum_statistics_range("2026-01-01\0", '2026-01-02'), 'invalid binary date rejected without parser error');
$range = forum_statistics_range('2026-03-29', '2026-03-29');
check($range['end']->getTimestamp() - $range['start']->getTimestamp() === 23 * 3600, 'DST-aware inclusive range');
$pdo->prepare('INSERT INTO users(username,email,password_hash,role,created_at) VALUES ("stat15","stat15@safe.org","!","member",:time)')->execute([':time' => '2026-03-28T23:30:00+00:00']);
$stats = forum_statistics($range);
check($stats['totals']['users'] === 1 && $stats['days']['2026-03-29']['users'] === 1, 'UTC event counted in correct local date');
$pdo->exec('DELETE FROM users WHERE username="stat15"');
$_SESSION = ['forum_user_id' => (int) $member['id']];
rejects(fn() => forum_statistics($range), 'member cannot read admin statistics');
rejects(fn() => forum_hosting_probe(), 'member cannot generate probe');
rejects(fn() => forum_operations_save('maintenance', []), 'member cannot change operations settings');

forum_register_user('delete15', 'delete15@safe.org', 'Testing-password-123', true);
$deleteId = (int) $pdo->lastInsertId();
$_SESSION = ['forum_user_id' => $deleteId];
$deleteTopic = forum_create_topic($pdo, $fixtures['public']['category'], $deleteId, 'Retained discussion 1.5', 'Retained post without personal data');
$firstPost = (int) $pdo->query('SELECT id FROM posts WHERE topic_id=' . $deleteTopic . ' ORDER BY id LIMIT 1')->fetchColumn();
forum_create_post($deleteTopic, (int) $member['id'], 'Another author keeps this reply');
forum_ext_send_message($deleteId, 'member14', 'Private subject', 'Private content removed');
forum_ext_toggle_post_like($fixtures['public']['post'], $deleteId);
forum_ext_report_post($fixtures['public']['post'], $deleteId, 'Test report to remove');
$avatarPath = FORUM_AVATAR_DIR . '/delete15.webp';
file_put_contents($avatarPath, 'test avatar');
$pdo->exec('UPDATE users SET avatar_filename="delete15.webp", about_me="Private about", signature="Private signature" WHERE id=' . $deleteId);
rejects(fn() => forum_delete_own_account((int) $member['id'], 'Testing-password-123', true), 'self-delete ownership');
rejects(fn() => forum_delete_own_account($deleteId, 'incorrect', true), 'self-delete requires password');
rejects(fn() => forum_delete_own_account($deleteId, 'Testing-password-123', false), 'self-delete requires explicit consent');
forum_delete_own_account($deleteId, 'Testing-password-123', true);
check(!isset($_SESSION['forum_user_id']), 'deletion ends session');
check(!$pdo->query('SELECT id FROM users WHERE id=' . $deleteId)->fetchColumn(), 'real identity removed');
check(!$pdo->query('SELECT id FROM private_messages WHERE sender_id=' . $deleteId . ' OR recipient_id=' . $deleteId)->fetchColumn(), 'private messages removed');
check(!$pdo->query('SELECT id FROM post_likes WHERE user_id=' . $deleteId)->fetchColumn(), 'likes removed');
check(!$pdo->query('SELECT id FROM post_reports WHERE reporter_user_id=' . $deleteId)->fetchColumn(), 'submitted reports removed');
check(!is_file($avatarPath), 'avatar file removed');
$anonymous = (int) $pdo->query('SELECT id FROM users WHERE role="deleted"')->fetchColumn();
check($anonymous > 0 && !forum_ext_fetch_user_profile($anonymous), 'anonymous account has no public profile');
check((int) $pdo->query('SELECT user_id FROM topics WHERE id=' . $deleteTopic)->fetchColumn() === $anonymous, 'topic preserved anonymously');
check((int) $pdo->query('SELECT user_id FROM posts WHERE id=' . $firstPost)->fetchColumn() === $anonymous, 'first post preserved anonymously');
check((int) $pdo->query('SELECT COUNT(*) FROM posts WHERE topic_id=' . $deleteTopic)->fetchColumn() === 2, 'other authors replies preserved');
check($pdo->query('PRAGMA foreign_key_check')->fetchAll() === [], 'deletion preserves referential integrity');
rejects(fn() => forum_ext_send_message((int) $member['id'], '[deleted]', 'Forbidden recipient', 'Cannot send to deleted account'), 'anonymous account cannot receive messages');
$_SESSION = ['forum_user_id' => 1];
rejects(fn() => forum_delete_own_account(1, 'Test-only-password-123', true), 'admin self-deletion blocked');
rejects(fn() => forum_update_user_by_admin($anonymous, 'renamed', 'rename@safe.org', 'member', 'Strong-password-123'), 'anonymous account cannot be reactivated');
check(!in_array($anonymous, array_map('intval', array_column(forum_ext_top_users(100), 'id')), true), 'anonymous excluded from ranking');
check(forum_ext_count_users() === (int) $pdo->query('SELECT COUNT(*) FROM users WHERE role<>"deleted"')->fetchColumn(), 'anonymous excluded from live user counts');
foreach (['pl', 'en', 'de'] as $language) {
    forum_ext_store_setting($pdo, 'forum_language', $language);
    forum_ext_current_language();
    $_GET = ['view' => 'admin'];
    foreach (['hosting', 'maintenance', 'seo', 'statistics'] as $section) {
        $html = capture(fn() => forum_ext_render_admin_panel(forum_ext_admin_summary(), forum_fetch_categories(), forum_ext_top_users(), [$admin], $pagination, null, $section));
        check(str_contains($html, forum_escape(forum_t($section . '.title'))) && !preg_match('/>(?:hosting|maintenance|statistics|seo)\.[a-z_]+</', $html), 'localized operations screen: ' . $language . ':' . $section);
    }
    $html = capture(fn() => forum_privacy_render($member));
    check(str_contains($html, forum_escape(forum_t('privacy.confirm'))), 'localized retention consent: ' . $language);
}
$_GET = [];
$_SESSION = ['forum_user_id' => 1];
forum_operations_save('seo', ['site_url' => '']);
forum_register_user('http15', 'http15@safe.org', 'Testing-password-123', true);

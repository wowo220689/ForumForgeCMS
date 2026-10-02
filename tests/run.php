<?php
declare(strict_types=1);

// Tests run against a disposable copy, never against the installed forum database.
$source = dirname(__DIR__);
$childArgument = array_search('--fixture-child', $argv, true);
$fixture = $childArgument === false ? sys_get_temp_dir() . '/forumforgecms-test-' . bin2hex(random_bytes(8)) : $argv[$childArgument + 1];
function copy_tree(string $source, string $target): void
{
    if (is_dir($source)) {
        if (!is_dir($target)) { mkdir($target, 0700, true); }
        foreach (new DirectoryIterator($source) as $item) {
            if (!$item->isDot()) { copy_tree($item->getPathname(), $target . '/' . $item->getFilename()); }
        }
    } else { copy($source, $target); }
}
if ($childArgument === false) {
    mkdir($fixture, 0700, true);
    foreach (glob($source . '/*.php') as $file) { copy($file, $fixture . '/' . basename($file)); }
    foreach (['locales', 'assets', 'media'] as $directory) { copy_tree($source . '/' . $directory, $fixture . '/' . $directory); }
    // The child must exit before Windows releases SQLite's file handles.
    $command = [PHP_BINARY, '-n', '-d', 'extension_dir=' . ini_get('extension_dir'), '-d', 'extension=pdo_sqlite', '-d', 'extension=mbstring', __FILE__, '--fixture-child', $fixture];
    if (in_array('--keep', $argv, true)) { $command[] = '--keep'; }
    passthru(implode(' ', array_map('escapeshellarg', $command)), $status);
    if (!in_array('--keep', $argv, true)) { remove_tree($fixture); }
    exit($status);
}
putenv('FORUM_ADMIN_INITIAL_PASSWORD=Test-only-password-123');
$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US,en;q=0.9';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['REQUEST_METHOD'] = 'GET';
foreach (['bootstrap', 'ext_schema', 'ext_social', 'ext_comms', 'ext_backup', 'ext_search', 'ext_render', 'ext_views'] as $file) { require $fixture . '/' . $file . '.php'; }
$checks = 0;
function check(bool $result, string $message): void
{
    global $checks;
    if (!$result) { throw new RuntimeException('FAIL: ' . $message); }
    $checks++;
}
function rejects(callable $callback, string $message): void
{
    $rejected = false;
    try { $callback(); } catch (RuntimeException $e) { $rejected = true; }
    check($rejected, $message);
}
function capture(callable $callback): string
{
    ob_start();
    try { $callback(); return (string) ob_get_contents(); } finally { ob_end_clean(); }
}
function remove_tree(string $directory): void
{
    foreach (new DirectoryIterator($directory) as $item) {
        if ($item->isDot()) { continue; }
        if ($item->isDir()) { remove_tree($item->getPathname()); } else { unlink($item->getPathname()); }
    }
    rmdir($directory);
}
set_error_handler(static function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});
forum_ext_boot();
check(forum_ext_current_language() === 'en', 'fresh install language');
check(forum_ext_setting('antispam_question_enabled') === '1', 'challenge enabled by default');
forum_ext_apply_language_defaults('en');
$catalogs = [];
foreach (['pl', 'en', 'de'] as $language) {
    $catalogs[$language] = forum_dictionary($language);
    check(array_keys($catalogs[$language]) === array_keys($catalogs['pl']), 'dictionary keys: ' . $language);
    foreach ($catalogs[$language] as $key => $value) {
        check(mb_check_encoding($value, 'UTF-8'), 'UTF-8: ' . $language . ':' . $key);
        preg_match_all('/\{[a-z]+\}|%[sd]/', $value, $actual);
        preg_match_all('/\{[a-z]+\}|%[sd]/', $catalogs['pl'][$key], $expected);
        sort($actual[0]); sort($expected[0]);
        check($actual[0] === $expected[0], 'placeholders: ' . $language . ':' . $key);
    }
}
foreach (glob($source . '/*.php') as $file) {
    $tokens = token_get_all((string) file_get_contents($file));
    for ($i = 0; $i < count($tokens); $i++) {
        if (!is_array($tokens[$i]) || $tokens[$i][0] !== T_STRING || $tokens[$i][1] !== 'forum_t') { continue; }
        $j = $i + 1;
        while (isset($tokens[$j]) && is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) { $j++; }
        if (($tokens[$j++] ?? null) !== '(') { continue; }
        while (isset($tokens[$j]) && is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) { $j++; }
        if (is_array($tokens[$j] ?? null) && $tokens[$j][0] === T_CONSTANT_ENCAPSED_STRING) {
            $key = eval('return ' . $tokens[$j][1] . ';');
            check(isset($catalogs['en'][$key]), 'translated call: ' . basename($file) . ':' . $key);
        }
    }
}
check(!function_exists('forum_ext_translate_html'), 'no output-wide replacements');
foreach (['tekst', 'Adres linku:', 'opis linku', 'Adres obrazka:', 'Wyczyścić treść pola?', 'Nie udało się odczytać obrazu.', 'Przeglądarka nie potrafi zapisać obrazu jako WebP.', 'Nie udało się przygotować obrazu do wysłania.'] as $key) {
    check(isset($catalogs['en'][$key]), 'JavaScript message: ' . $key);
}
check(forum_antispam_normalize_list("EXAMPLE.COM\nexample.com\n sub.example.org ", 'domains') === "example.com\nsub.example.org", 'domain normalization');
check(forum_antispam_normalize_list("Spammer\nspammer", 'usernames') === 'spammer', 'name normalization');
check(forum_antispam_normalize_list('2001:0db8:0:0:0:0:0:1', 'ips') === '2001:db8::1', 'IPv6 normalization');
check(forum_antispam_ip_blocked('2001:0db8::1', ['2001:db8::1']), 'IPv6 matching');
rejects(fn() => forum_antispam_normalize_list('127.0.0.1', 'ips', '127.0.0.1'), 'self-IP protection');
rejects(fn() => forum_antispam_normalize_list('ADMIN', 'usernames'), 'admin name protection');
foreach (['https://example.com', '*.example.com', 'example..com'] as $domain) {
    rejects(fn() => forum_antispam_normalize_list($domain, 'domains'), 'invalid domain');
}
rejects(fn() => forum_antispam_normalize_list('not-an-IP', 'ips'), 'invalid IP');
rejects(fn() => forum_antispam_normalize_list('x', 'usernames'), 'invalid name');
rejects(fn() => forum_antispam_normalize_list(str_repeat("example.com\n", 1001), 'domains'), 'entry limit');
forum_antispam_save(['antispam_question_enabled' => '1', 'antispam_domains' => 'example.com', 'antispam_usernames' => 'spammer']);
rejects(fn() => forum_antispam_check_identity('SpAmMeR', 'person@safe.org'), 'case-insensitive block');
rejects(fn() => forum_antispam_check_identity('person', 'person@sub.example.com'), 'subdomain block');
forum_antispam_check_identity('person', 'person@notexample.com');
check(true, 'domain boundary');
rejects(fn() => forum_antispam_save(['antispam_domains' => 'new.example', 'antispam_ips' => 'bad']), 'invalid settings rejected');
check(forum_ext_setting('antispam_domains') === 'example.com', 'atomic validation');
$challenge = forum_antispam_challenge();
$answer = $_SESSION['forum_antispam_challenges'][$challenge['token']]['answer'];
forum_antispam_validate_answer($challenge['token'], $answer);
rejects(fn() => forum_antispam_validate_answer($challenge['token'], $answer), 'replay rejected');
$challenge = forum_antispam_challenge();
rejects(fn() => forum_antispam_validate_answer($challenge['token'], '99'), 'wrong answer rejected');
$challenge = forum_antispam_challenge();
$_SESSION['forum_antispam_challenges'][$challenge['token']]['expires'] = time() - 1;
rejects(fn() => forum_antispam_validate_answer($challenge['token'], '1'), 'expired challenge rejected');
rejects(fn() => forum_antispam_validate_answer('', ''), 'missing challenge rejected');
$one = forum_antispam_challenge();
$two = forum_antispam_challenge();
foreach ([$one, $two] as $tab) {
    forum_antispam_validate_answer($tab['token'], $_SESSION['forum_antispam_challenges'][$tab['token']]['answer']);
    check(true, 'independent browser tabs');
}
for ($i = 0; $i < 20; $i++) { forum_antispam_challenge(); }
check(count($_SESSION['forum_antispam_challenges']) <= 8, 'bounded session storage');
forum_antispam_save([]);
forum_antispam_validate_answer('', '');
check(true, 'disabled challenge');
forum_antispam_save(['antispam_question_enabled' => '1']);
forum_register_user('tester', 'tester@safe.org', 'Testing-password-123', true);
rejects(fn() => forum_register_user('TESTER', 'different@safe.org', 'Testing-password-123', true), 'localized duplicate registration');
$admin = forum_ext_fetch_user_profile(1);
$_SESSION['forum_user_id'] = 1;
$body = 'Użytkownicy: Zarządzaj kategoriami. Łódź, Größe. <script>alert(1)</script>';
$topicId = forum_create_topic(forum_db(), 1, 1, 'User content untouched', $body);
$topic = forum_ext_fetch_topic($topicId);
$posts = forum_ext_fetch_posts_for_topic($topicId, 1);
$pagination = forum_pagination(1, 1, FORUM_POSTS_PER_PAGE);
foreach (['pl', 'en', 'de'] as $language) {
    forum_ext_store_setting(forum_db(), 'forum_language', $language);
    forum_ext_current_language();
    forum_ext_apply_language_defaults($language);
    $register = capture(fn() => forum_ext_render_auth('register', null));
    check(str_contains($register, forum_escape(forum_t('Kontrola antyspamowa'))), 'localized registration: ' . $language);
    $html = capture(fn() => forum_ext_render_topic($topic, $posts, $pagination, $admin, null));
    check(str_contains($html, 'Zarządzaj kategoriami'), 'user text preserved: ' . $language);
    check(!str_contains($html, '<script>alert(1)</script>'), 'post escaped: ' . $language);
    foreach (['settings', 'basic-settings', 'antispam', 'backup', 'reports', 'users', 'top-users', 'forum-sections', 'create-forum-section', 'categories', 'create-category', 'user'] as $section) {
        $html = capture(fn() => forum_ext_render_admin_panel(forum_ext_admin_summary(), forum_fetch_categories(), forum_ext_top_users(), [$admin], $pagination, null, $section, 1));
        check(str_contains($html, 'ForumForgeCMS 1.3'), 'admin view: ' . $language . ':' . $section);
    }
    check(!str_contains(forum_t('mail.reset.body', ['username' => 'tester', 'url' => 'https://example.com', 'hours' => 2]), '{'), 'mail parameters');
    $footer = capture(fn() => forum_ext_render_footer());
    check(str_contains($footer, 'id="forum-i18n"'), 'browser dictionary');
}
forum_ext_store_setting(forum_db(), 'forum_language', 'en');
forum_ext_current_language();
forum_ext_apply_language_defaults('en');
echo 'PASS: ' . $checks . " assertions\n";
if (in_array('--keep', $argv, true)) { echo 'FIXTURE=' . $fixture . "\n"; }

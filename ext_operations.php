<?php
declare(strict_types=1);

function forum_operations_require_admin(): void
{
    if (!forum_is_admin(forum_current_user())) {
        throw new RuntimeException(forum_t('Ta sekcja jest dostepna tylko dla administratora.'));
    }
}

function forum_maintenance_blocks(?array $user, string $view, string $method, string $action = ''): bool
{
    if (forum_ext_setting('maintenance_enabled') !== '1' || forum_is_admin($user)) { return false; }
    if ($method === 'POST') { return !in_array($action, ['login', 'logout'], true); }
    return $view !== 'login';
}

function forum_maintenance_render(): void
{
    http_response_code(503);
    header('Retry-After: 3600');
    header('Cache-Control: no-store');
    header('X-Robots-Tag: noindex, nofollow');
    $message = forum_ext_setting('maintenance_message');
    if ($message === '') { $message = forum_t('maintenance.default'); }
    ?>
    <!doctype html><html lang="<?php echo forum_escape(forum_ext_current_language()); ?>"><head>
      <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
      <meta name="robots" content="noindex,nofollow"><title><?php echo forum_escape(forum_t('maintenance.title')); ?></title>
      <link rel="stylesheet" href="assets/forumforgecms2026.css"><link rel="stylesheet" href="assets/forum.css">
    </head><body class="theme-<?php echo forum_escape(forum_ext_setting('graphic_style')); ?>"><main class="page-wrap"><section class="panel forum-panel forum-narrow-panel">
      <h1><?php echo forum_escape(forum_t('maintenance.title')); ?></h1>
      <p class="forum-plain-text"><?php echo nl2br(forum_escape($message)); ?></p>
      <a class="button-secondary" href="<?php echo forum_url(['view' => 'login']); ?>"><?php echo forum_escape(forum_t('maintenance.admin_login')); ?></a>
    </section></main></body></html>
    <?php
}

function forum_operations_save(string $section, array $input): void
{
    forum_operations_require_admin();
    if ($section === 'seo') {
        forum_ext_store_setting(forum_db(), 'site_url', forum_seo_validate_url((string) ($input['site_url'] ?? '')));
        return;
    }
    if ($section !== 'maintenance') { throw new RuntimeException(forum_t('operations.invalid')); }
    $message = trim((string) ($input['maintenance_message'] ?? ''));
    if (!preg_match('//u', $message) || preg_match_all('/./us', $message) > 1000) { throw new RuntimeException(forum_t('maintenance.too_long')); }
    $pdo = forum_db();
    $pdo->beginTransaction();
    try {
        forum_ext_store_setting($pdo, 'maintenance_message', $message);
        forum_ext_store_setting($pdo, 'maintenance_enabled', isset($input['maintenance_enabled']) ? '1' : '0');
        $pdo->commit();
    } catch (Throwable $e) { $pdo->rollBack(); unset($GLOBALS['forum_ext_settings_cache']); throw $e; }
}

function forum_hosting_checks(): array
{
    forum_operations_require_admin();
    $pdo = forum_db();
    $checks = [
        ['hosting.php', PHP_VERSION_ID >= 80100, PHP_VERSION],
        ['hosting.sqlite', extension_loaded('pdo_sqlite'), (string) $pdo->query('SELECT sqlite_version()')->fetchColumn()],
        ['hosting.sessions', session_status() === PHP_SESSION_ACTIVE, ''],
        ['hosting.integrity', $pdo->query('PRAGMA quick_check')->fetchColumn() === 'ok', ''],
        ['hosting.foreign_keys', (int) $pdo->query('PRAGMA foreign_keys')->fetchColumn() === 1, ''],
        ['hosting.mail', function_exists('mail'), ''],
        ['hosting.rules', is_file(FORUM_DATA_DIR . '/.htaccess') && is_file(FORUM_DATA_DIR . '/web.config'), ''],
    ];
    foreach ([FORUM_DATA_DIR, FORUM_AVATAR_DIR, FORUM_BRAND_DIR] as $directory) {
        $checks[] = ['hosting.writable', is_dir($directory) && is_writable($directory), 'forum-data/' . ($directory === FORUM_DATA_DIR ? '' : basename($directory))];
    }
    return $checks;
}

function forum_hosting_probe_cleanup(): void
{
    forum_operations_require_admin();
    foreach ($_SESSION['forum_hosting_probes'] ?? [] as $name) {
        if (preg_match('/^hosting-check-[a-f0-9]{32}\.txt$/D', (string) $name)) {
            $path = FORUM_DATA_DIR . '/' . $name;
            if (is_file($path)) { unlink($path); }
        }
    }
    unset($_SESSION['forum_hosting_probes']);
}

function forum_hosting_probe(): array
{
    forum_operations_require_admin();
    forum_hosting_probe_cleanup();
    // Probe only disposable, non-sensitive text. Never request the live database or password file.
    foreach (glob(FORUM_DATA_DIR . '/hosting-check-*.txt') ?: [] as $path) {
        if (preg_match('/^hosting-check-[a-f0-9]{32}\.txt$/D', basename($path)) && filemtime($path) < time() - 600) { unlink($path); }
    }
    $name = 'hosting-check-' . bin2hex(random_bytes(16)) . '.txt';
    if (file_put_contents(FORUM_DATA_DIR . '/' . $name, 'ForumForgeCMS temporary access check', LOCK_EX) === false) {
        throw new RuntimeException(forum_t('hosting.probe_failed'));
    }
    $_SESSION['forum_hosting_probes'] = [$name];
    return ['url' => 'forum-data/' . $name];
}

function forum_statistics_range(string $from = '', string $to = ''): array
{
    $zone = new DateTimeZone('Europe/Berlin');
    $today = new DateTimeImmutable('today', $zone);
    $from = $from === '' ? $today->modify('-29 days')->format('Y-m-d') : $from;
    $to = $to === '' ? $today->format('Y-m-d') : $to;
    if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/D', $from) || !preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/D', $to)) {
        throw new RuntimeException(forum_t('statistics.invalid_range'));
    }
    $start = DateTimeImmutable::createFromFormat('!Y-m-d', $from, $zone);
    $end = DateTimeImmutable::createFromFormat('!Y-m-d', $to, $zone);
    if (!$start || !$end || $start->format('Y-m-d') !== $from || $end->format('Y-m-d') !== $to || $start > $end || $start->diff($end)->days > 365) {
        throw new RuntimeException(forum_t('statistics.invalid_range'));
    }
    return ['from' => $from, 'to' => $to, 'start' => $start, 'end' => $end->modify('+1 day')];
}

function forum_statistics(array $range): array
{
    forum_operations_require_admin();
    $days = [];
    for ($date = $range['start']; $date < $range['end']; $date = $date->modify('+1 day')) {
        $days[$date->format('Y-m-d')] = ['users' => 0, 'topics' => 0, 'posts' => 0];
    }
    $totals = ['users' => 0, 'topics' => 0, 'posts' => 0];
    // SQLite normalizes ISO timestamps to UTC; assign each event to a Berlin calendar day in PHP (including DST).
    foreach (array_keys($totals) as $table) {
        $query = 'SELECT created_at FROM ' . $table . ' WHERE datetime(created_at)>=datetime(:start) AND datetime(created_at)<datetime(:end)';
        if ($table === 'users') { $query .= ' AND role<>"deleted"'; }
        $stmt = forum_db()->prepare($query);
        $stmt->execute([':start' => $range['start']->format(DATE_ATOM), ':end' => $range['end']->format(DATE_ATOM)]);
        while ($timestamp = $stmt->fetchColumn()) {
            $day = (new DateTimeImmutable((string) $timestamp))->setTimezone(new DateTimeZone('Europe/Berlin'))->format('Y-m-d');
            if (isset($days[$day])) { $days[$day][$table]++; $totals[$table]++; }
        }
    }
    return ['totals' => $totals, 'days' => $days];
}

function forum_operations_render(string $section): void
{
    forum_operations_require_admin();
    ?>
    <section class="panel forum-panel forum-operations">
      <h2><?php echo forum_escape(forum_t($section . '.title')); ?></h2>
      <?php if ($section === 'statistics'): ?>
        <?php try { $range = forum_statistics_range((string) ($_GET['from'] ?? ''), (string) ($_GET['to'] ?? '')); }
        catch (RuntimeException $e) { echo '<p class="warning">' . forum_escape($e->getMessage()) . '</p>'; $range = forum_statistics_range(); }
        $stats = forum_statistics($range); ?>
        <form class="forum-form forum-date-filter" method="get" action="index.php">
          <input type="hidden" name="view" value="admin"><input type="hidden" name="section" value="statistics">
          <label><?php echo forum_escape(forum_t('statistics.from')); ?><input type="date" name="from" value="<?php echo forum_escape($range['from']); ?>" required></label>
          <label><?php echo forum_escape(forum_t('statistics.to')); ?><input type="date" name="to" value="<?php echo forum_escape($range['to']); ?>" required></label>
          <button class="button" type="submit"><?php echo forum_escape(forum_t('statistics.apply')); ?></button>
        </form>
        <p><?php echo forum_escape(forum_t('statistics.retention')); ?></p>
        <div class="forum-stats-grid"><?php foreach ($stats['totals'] as $key => $count): ?><article class="forum-stat-box"><strong><?php echo $count; ?></strong><span><?php echo forum_escape(forum_t('statistics.' . $key)); ?></span></article><?php endforeach; ?></div>
        <div class="forum-table-scroll"><table class="forum-data-table"><thead><tr><th><?php echo forum_escape(forum_t('statistics.day')); ?></th><?php foreach (array_keys($stats['totals']) as $key): ?><th><?php echo forum_escape(forum_t('statistics.' . $key)); ?></th><?php endforeach; ?></tr></thead><tbody>
          <?php foreach (array_reverse($stats['days'], true) as $day => $counts): ?><tr><th scope="row"><?php echo forum_escape($day); ?></th><?php foreach ($counts as $count): ?><td><?php echo $count; ?></td><?php endforeach; ?></tr><?php endforeach; ?>
        </tbody></table></div>
      <?php elseif ($section === 'hosting'): ?>
        <dl class="forum-diagnostics"><?php foreach (forum_hosting_checks() as [$label, $ok, $detail]): ?><div><dt><?php echo forum_escape(forum_t($label)); ?> <?php echo forum_escape($detail); ?></dt><dd><?php echo forum_escape(forum_t($ok ? 'hosting.ok' : 'hosting.failed')); ?></dd></div><?php endforeach; ?></dl>
        <p><?php echo forum_escape(forum_t('hosting.rules_note')); ?></p>
        <form id="forum-hosting-probe" class="forum-form" method="post" action="<?php echo forum_url(['view' => 'admin', 'section' => 'hosting']); ?>"
          data-safe="<?php echo forum_escape(forum_t('hosting.probe_safe')); ?>" data-exposed="<?php echo forum_escape(forum_t('hosting.probe_exposed')); ?>" data-unknown="<?php echo forum_escape(forum_t('hosting.probe_unknown')); ?>">
          <input type="hidden" name="action" value="hosting_probe"><input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
          <button class="button-secondary" type="submit"><?php echo forum_escape(forum_t('hosting.probe')); ?></button><output aria-live="polite"></output>
        </form><script src="assets/forum-hosting.js" defer></script>
      <?php else: ?>
        <form class="forum-form" method="post" action="<?php echo forum_url(['view' => 'admin', 'section' => $section]); ?>">
          <input type="hidden" name="action" value="update_operations"><input type="hidden" name="operations_section" value="<?php echo forum_escape($section); ?>"><input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
          <?php if ($section === 'maintenance'): ?>
            <label class="checkbox-row"><input type="checkbox" name="maintenance_enabled" value="1" <?php echo forum_ext_setting('maintenance_enabled') === '1' ? 'checked' : ''; ?>><span><?php echo forum_escape(forum_t('maintenance.enabled')); ?></span></label>
            <label for="maintenance-message"><?php echo forum_escape(forum_t('maintenance.message')); ?></label><textarea id="maintenance-message" name="maintenance_message" maxlength="1000" placeholder="<?php echo forum_escape(forum_t('maintenance.default')); ?>"><?php echo forum_escape(forum_ext_setting('maintenance_message')); ?></textarea>
            <p><?php echo forum_escape(forum_t('maintenance.note')); ?></p>
          <?php elseif ($section === 'seo'): ?>
            <label for="site-url"><?php echo forum_escape(forum_t('seo.site_url')); ?></label><input id="site-url" name="site_url" type="url" maxlength="255" placeholder="https://example.com/forum" value="<?php echo forum_escape(forum_ext_setting('site_url')); ?>">
            <p><?php echo forum_escape(forum_t('seo.note')); ?></p>
          <?php endif; ?>
          <button class="button" type="submit"><?php echo forum_escape(forum_t('Zapisz ustawienia')); ?></button>
        </form>
      <?php endif; ?>
    </section>
    <?php
}

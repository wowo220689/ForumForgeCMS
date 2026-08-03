<?php
declare(strict_types=1);

if (!defined('FORUM_DATA_DIR')) {
    define('FORUM_DATA_DIR', __DIR__ . '/forum-data');
}
if (!defined('FORUM_AVATAR_DIR')) {
    define('FORUM_AVATAR_DIR', __DIR__ . '/forum-data/avatars');
}
if (!defined('FORUM_BRAND_DIR')) {
    define('FORUM_BRAND_DIR', __DIR__ . '/forum-data/brand');
}
if (!defined('FORUM_MAIL_FROM')) {
    $forumMailHost = preg_replace('/:\d+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? 'example.com'));
    define('FORUM_MAIL_FROM', 'webmaster@' . ($forumMailHost !== '' ? $forumMailHost : 'example.com'));
}
if (!defined('FORUM_UPLOAD_MAX_BYTES')) {
    define('FORUM_UPLOAD_MAX_BYTES', 2097152);
}
if (!defined('FORUM_PASSWORD_RESET_TTL_HOURS')) {
    define('FORUM_PASSWORD_RESET_TTL_HOURS', 2);
}
if (!defined('FORUM_ACTIVITY_PURGE_TTL_HOURS')) {
    define('FORUM_ACTIVITY_PURGE_TTL_HOURS', 12);
}
if (!defined('FORUM_ACTIVITY_PURGE_CONFIRM_EMAIL')) {
    define('FORUM_ACTIVITY_PURGE_CONFIRM_EMAIL', FORUM_MAIL_FROM);
}

function forum_ext_boot(): void
{
    static $booted = false;

    if ($booted) {
        return;
    }

    forum_ext_ensure_storage_directories();
    forum_ext_migrate_schema(forum_db());
    $booted = true;
}

function forum_ext_ensure_storage_directories(): void
{
    foreach ([FORUM_DATA_DIR, FORUM_AVATAR_DIR, FORUM_BRAND_DIR] as $directory) {
        if (is_dir($directory)) {
            continue;
        }

        if (!mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException('Nie udało się utworzyć katalogu danych forum.');
        }
    }
}

function forum_ext_migrate_schema(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS settings (
            key TEXT PRIMARY KEY,
            value TEXT NOT NULL
        )'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS post_likes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            post_id INTEGER NOT NULL,
            user_id INTEGER NOT NULL,
            created_at TEXT NOT NULL,
            UNIQUE(post_id, user_id),
            FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS private_messages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            sender_id INTEGER NOT NULL,
            recipient_id INTEGER NOT NULL,
            reply_to_id INTEGER,
            subject TEXT NOT NULL,
            body TEXT NOT NULL,
            created_at TEXT NOT NULL,
            read_at TEXT,
            deleted_by_sender INTEGER NOT NULL DEFAULT 0,
            deleted_by_recipient INTEGER NOT NULL DEFAULT 0,
            FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (reply_to_id) REFERENCES private_messages(id) ON DELETE SET NULL
        )'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS password_reset_tokens (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            selector TEXT NOT NULL UNIQUE,
            token_hash TEXT NOT NULL,
            created_at TEXT NOT NULL,
            expires_at TEXT NOT NULL,
            used_at TEXT,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS admin_activity_purge_requests (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            target_user_id INTEGER NOT NULL,
            requested_by_user_id INTEGER NOT NULL,
            selector TEXT NOT NULL UNIQUE,
            token_hash TEXT NOT NULL,
            created_at TEXT NOT NULL,
            expires_at TEXT NOT NULL,
            used_at TEXT,
            executed_by_user_id INTEGER,
            FOREIGN KEY (target_user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (requested_by_user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (executed_by_user_id) REFERENCES users(id) ON DELETE SET NULL
        )'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS post_reports (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            post_id INTEGER NOT NULL,
            reporter_user_id INTEGER NOT NULL,
            reason TEXT NOT NULL DEFAULT "",
            status TEXT NOT NULL DEFAULT "open",
            created_at TEXT NOT NULL,
            closed_at TEXT,
            closed_by_user_id INTEGER,
            FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
            FOREIGN KEY (reporter_user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (closed_by_user_id) REFERENCES users(id) ON DELETE SET NULL
        )'
    );

    forum_ext_ensure_column($pdo, 'users', 'avatar_filename', 'TEXT');
    forum_ext_ensure_column($pdo, 'users', 'avatar_updated_at', 'TEXT');
    forum_ext_ensure_column($pdo, 'posts', 'edited_by_user_id', 'INTEGER');

    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_likes_post ON post_likes(post_id)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_likes_user ON post_likes(user_id)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_messages_recipient ON private_messages(recipient_id, created_at DESC)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_messages_sender ON private_messages(sender_id, created_at DESC)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_messages_recipient_unread ON private_messages(recipient_id, read_at, deleted_by_recipient)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_reset_selector ON password_reset_tokens(selector)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_activity_purge_selector ON admin_activity_purge_requests(selector)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_post_reports_status_created ON post_reports(status, created_at DESC)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_post_reports_post_status ON post_reports(post_id, status)');

    $stmt = $pdo->prepare('INSERT OR IGNORE INTO settings (key, value) VALUES (:key, :value)');
    foreach (forum_ext_default_settings() as $key => $value) {
        $stmt->execute([
            ':key' => $key,
            ':value' => $value,
        ]);
    }

    forum_ext_apply_forum_structure_upgrade($pdo);
}

function forum_ext_ensure_column(PDO $pdo, string $table, string $column, string $definition): void
{
    $stmt = $pdo->query(sprintf('PRAGMA table_info(%s)', $table));
    foreach ($stmt->fetchAll() as $info) {
        if (($info['name'] ?? '') === $column) {
            return;
        }
    }

    $pdo->exec(sprintf('ALTER TABLE %s ADD COLUMN %s %s', $table, $column, $definition));
}

function forum_ext_initial_language_from_browser(): string
{
    $acceptedLanguages = strtolower((string) ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? ''));
    foreach (explode(',', $acceptedLanguages) as $languagePart) {
        $languageCode = trim(explode(';', $languagePart, 2)[0] ?? '');
        if ($languageCode === '') {
            continue;
        }

        $primaryLanguage = explode('-', $languageCode, 2)[0];
        if (in_array($primaryLanguage, ['pl', 'de', 'en'], true)) {
            return $primaryLanguage;
        }
    }

    return 'en';
}

function forum_ext_default_settings(): array
{
    return [
        'brand_name' => 'ForumForgeCMS',
        'brand_tagline' => 'samodzielne forum dla Twojej społeczności',
        'brand_logo_filename' => '',
        'graphic_style' => 'classic',
        'forum_language' => forum_ext_initial_language_from_browser(),
        'home_intro_title' => 'ForumForgeCMS',
        'home_intro_text' => 'Lekka przestrzeń do rozmowy, wymiany wiedzy i budowania społeczności wokół dowolnego tematu. ForumForgeCMS daje prosty start, czytelne działy i spokojne miejsce na dyskusje, które z czasem może urosnąć razem z użytkownikami.',
        'allow_registrations' => '1',
    ];
}

function forum_ext_apply_forum_structure_upgrade(PDO $pdo): void
{
    $currentVersion = (int) forum_ext_read_setting_raw($pdo, 'forum_structure_version', '0');
    if ($currentVersion >= 3) {
        return;
    }

    forum_sync_default_categories($pdo);
    forum_ensure_default_forum_sections($pdo);
    forum_ext_store_setting($pdo, 'forum_structure_version', '3');
}

function forum_ext_read_setting_raw(PDO $pdo, string $key, string $fallback = ''): string
{
    $stmt = $pdo->prepare('SELECT value FROM settings WHERE key = :key LIMIT 1');
    $stmt->execute([':key' => $key]);
    $value = $stmt->fetchColumn();

    return $value === false ? $fallback : (string) $value;
}

function forum_ext_store_setting(PDO $pdo, string $key, string $value): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO settings (key, value) VALUES (:key, :value)
         ON CONFLICT(key) DO UPDATE SET value = excluded.value'
    );
    $stmt->execute([
        ':key' => $key,
        ':value' => $value,
    ]);

    unset($GLOBALS['forum_ext_settings_cache']);
}

function forum_ext_settings(): array
{
    if (isset($GLOBALS['forum_ext_settings_cache']) && is_array($GLOBALS['forum_ext_settings_cache'])) {
        return $GLOBALS['forum_ext_settings_cache'];
    }

    $settings = forum_ext_default_settings();
    $rows = forum_db()->query('SELECT key, value FROM settings')->fetchAll();
    foreach ($rows as $row) {
        $settings[(string) $row['key']] = (string) $row['value'];
    }

    $GLOBALS['forum_ext_settings_cache'] = $settings;
    return $settings;
}

function forum_ext_setting(string $key): string
{
    $settings = forum_ext_settings();
    return (string) ($settings[$key] ?? '');
}

function forum_ext_save_settings(array $input): void
{
    $data = [
        'brand_name' => forum_trimmed_text($input['brand_name'] ?? '', 80),
        'brand_tagline' => forum_trimmed_text($input['brand_tagline'] ?? '', 160),
        'graphic_style' => forum_trimmed_text($input['graphic_style'] ?? 'classic', 40),
        'forum_language' => forum_trimmed_text($input['forum_language'] ?? 'en', 8),
        'home_intro_title' => forum_trimmed_text($input['home_intro_title'] ?? '', 120),
        'home_intro_text' => forum_trimmed_text($input['home_intro_text'] ?? '', 500),
        'allow_registrations' => !empty($input['allow_registrations']) ? '1' : '0',
    ];

    if ($data['brand_name'] === '' || $data['brand_tagline'] === '' || $data['home_intro_title'] === '' || $data['home_intro_text'] === '') {
        throw new RuntimeException('Nazwa, krótki opis, tytuł i opis forum nie mogą być puste.');
    }

    if (!array_key_exists($data['graphic_style'], forum_ext_graphic_styles())) {
        $data['graphic_style'] = 'classic';
    }

    if (!array_key_exists($data['forum_language'], forum_ext_languages())) {
        $data['forum_language'] = 'en';
    }

    $stmt = forum_db()->prepare(
        'INSERT INTO settings (key, value) VALUES (:key, :value)
         ON CONFLICT(key) DO UPDATE SET value = excluded.value'
    );

    foreach ($data as $key => $value) {
        $stmt->execute([
            ':key' => $key,
            ':value' => $value,
        ]);
    }

    unset($GLOBALS['forum_ext_settings_cache']);
}

function forum_ext_registrations_enabled(): bool
{
    return forum_ext_setting('allow_registrations') === '1';
}

function forum_ext_graphic_styles(): array
{
    return array_fill_keys(array_keys(forum_ext_graphic_style_labels('pl')), '');
}

function forum_ext_graphic_style_labels(?string $language = null): array
{
    $language = $language ?? forum_ext_current_language();
    $labels = [
        'pl' => [
            'classic' => 'Klasyczny jasny',
            'ocean' => 'Oceaniczny',
            'forest' => 'Leśny',
            'sunrise' => 'Poranny',
            'graphite' => 'Grafitowy',
            'berry' => 'Jagodowy',
            'compact' => 'Kompaktowy',
            'neon' => 'Neonowy',
            'paper' => 'Papierowy',
            'metro' => 'Miejski',
            'studio' => 'Studyjny',
            'terminal' => 'Terminalowy',
        ],
        'en' => [
            'classic' => 'Classic light',
            'ocean' => 'Ocean',
            'forest' => 'Forest',
            'sunrise' => 'Sunrise',
            'graphite' => 'Graphite',
            'berry' => 'Berry',
            'compact' => 'Compact',
            'neon' => 'Neon',
            'paper' => 'Paper',
            'metro' => 'Metro',
            'studio' => 'Studio',
            'terminal' => 'Terminal',
        ],
        'de' => [
            'classic' => 'Klassisch hell',
            'ocean' => 'Ozean',
            'forest' => 'Wald',
            'sunrise' => 'Morgenrot',
            'graphite' => 'Graphit',
            'berry' => 'Beere',
            'compact' => 'Kompakt',
            'neon' => 'Neon',
            'paper' => 'Papier',
            'metro' => 'Metro',
            'studio' => 'Studio',
            'terminal' => 'Terminal',
        ],
    ];

    return $labels[$language] ?? $labels['en'];
}

function forum_ext_graphic_style_label(string $style): string
{
    $labels = forum_ext_graphic_style_labels();
    return $labels[$style] ?? $style;
}

function forum_ext_languages(): array
{
    return [
        'pl' => 'Polski',
        'en' => 'Angielski',
        'de' => 'Niemiecki',
    ];
}

function forum_ext_current_user(): ?array
{
    $baseUser = forum_current_user();
    if (!$baseUser) {
        return null;
    }

    $stmt = forum_db()->prepare(
        'SELECT
            u.id,
            u.username,
            u.email,
            u.role,
            u.created_at,
            u.last_login_at,
            u.avatar_filename,
            u.avatar_updated_at,
            (
                SELECT COUNT(*)
                FROM private_messages pm
                WHERE pm.recipient_id = u.id
                  AND pm.deleted_by_recipient = 0
                  AND pm.read_at IS NULL
            ) AS unread_message_count
         FROM users u
         WHERE u.id = :id
         LIMIT 1'
    );
    $stmt->execute([':id' => (int) $baseUser['id']]);
    $user = $stmt->fetch();

    return $user ?: null;
}

function forum_ext_admin_summary(): array
{
    $pdo = forum_db();

    return [
        'users' => (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
        'categories' => (int) $pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn(),
        'topics' => (int) $pdo->query('SELECT COUNT(*) FROM topics')->fetchColumn(),
        'posts' => (int) $pdo->query('SELECT COUNT(*) FROM posts')->fetchColumn(),
        'likes' => (int) $pdo->query('SELECT COUNT(*) FROM post_likes')->fetchColumn(),
        'messages' => (int) $pdo->query('SELECT COUNT(*) FROM private_messages')->fetchColumn(),
    ];
}

function forum_ext_top_users(int $limit = 8): array
{
    $stmt = forum_db()->prepare(
        'SELECT
            u.id,
            u.username,
            u.role,
            u.avatar_filename,
            u.avatar_updated_at,
            (
                SELECT COUNT(*)
                FROM topics t
                WHERE t.user_id = u.id
            ) AS topic_count,
            (
                SELECT COUNT(*)
                FROM posts p
                WHERE p.user_id = u.id
            ) AS post_count,
            (
                SELECT COUNT(*)
                FROM post_likes pl
                INNER JOIN posts p2 ON p2.id = pl.post_id
                WHERE p2.user_id = u.id
            ) AS likes_received
         FROM users u
         ORDER BY post_count DESC, topic_count DESC, likes_received DESC, u.username ASC
         LIMIT :limit'
    );
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

function forum_ext_count_users(): int
{
    return (int) forum_db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
}

function forum_ext_admin_users_sql(): string
{
    return 'SELECT
        u.id,
        u.username,
        u.email,
        u.role,
        u.created_at,
        u.last_login_at,
        u.avatar_filename,
        u.avatar_updated_at,
        (
            SELECT COUNT(*)
            FROM topics t
            WHERE t.user_id = u.id
        ) AS topic_count,
        (
            SELECT COUNT(*)
            FROM posts p
            WHERE p.user_id = u.id
        ) AS post_count,
        (
            SELECT COUNT(*)
            FROM post_likes pl
            INNER JOIN posts p2 ON p2.id = pl.post_id
            WHERE p2.user_id = u.id
        ) AS likes_received,
        (
            SELECT COUNT(*)
            FROM post_likes pl
            WHERE pl.user_id = u.id
        ) AS likes_given,
        (
            SELECT COUNT(*)
            FROM private_messages pm
            WHERE pm.sender_id = u.id
               OR pm.recipient_id = u.id
        ) AS message_count
     FROM users u';
}

function forum_ext_admin_users(int $limit = 200, int $offset = 0): array
{
    $sql = forum_ext_admin_users_sql() . '
     ORDER BY
        CASE u.role
            WHEN "admin" THEN 0
            WHEN "moderator" THEN 1
            ELSE 2
        END,
        u.username ASC';

    if ($limit > 0) {
        $stmt = forum_db()->prepare($sql . ' LIMIT :limit OFFSET :offset');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);
        $stmt->execute();
    } else {
        $stmt = forum_db()->query($sql);
    }

    return $stmt->fetchAll();
}

function forum_ext_admin_user(int $userId): ?array
{
    $stmt = forum_db()->prepare(forum_ext_admin_users_sql() . ' WHERE u.id = :id LIMIT 1');
    $stmt->execute([':id' => $userId]);
    $user = $stmt->fetch();

    return $user ?: null;
}


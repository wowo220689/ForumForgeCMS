<?php
declare(strict_types=1);

require_once __DIR__ . '/ext_i18n.php';
require_once __DIR__ . '/ext_antispam.php';

const FORUM_TITLE = 'ForumForgeCMS';
const FORUM_VERSION = '1.3';
const FORUM_DB_PATH = __DIR__ . '/forum-data/forum.sqlite';
const FORUM_ADMIN_USERNAME = 'admin';
const FORUM_ADMIN_EMAIL = '';
const FORUM_ADMIN_INITIAL_PASSWORD_FILE = __DIR__ . '/forum-data/admin-initial-password.txt';
const FORUM_TOPICS_PER_PAGE = 25;
const FORUM_POSTS_PER_PAGE = 20;
const FORUM_MESSAGES_PER_PAGE = 25;
const FORUM_ADMIN_USERS_PER_PAGE = 50;
const FORUM_SEARCH_RESULTS_PER_PAGE = 10;

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function forum_db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    forum_ensure_storage_directory();

    $pdo = new PDO('sqlite:' . FORUM_DB_PATH, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    forum_configure_sqlite($pdo);
    forum_initialize_database($pdo);

    return $pdo;
}

function forum_configure_sqlite(PDO $pdo): void
{
    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec('PRAGMA busy_timeout = 5000');

    foreach ([
        'PRAGMA journal_mode = WAL',
        'PRAGMA synchronous = NORMAL',
        'PRAGMA temp_store = MEMORY',
    ] as $pragma) {
        try {
            $pdo->exec($pragma);
        } catch (Throwable $e) {
            // Some shared hosts keep SQLite on filesystems that do not allow WAL.
        }
    }
}

function forum_ensure_storage_directory(): void
{
    $directory = dirname(FORUM_DB_PATH);
    if (is_dir($directory)) {
        return;
    }

    if (!mkdir($directory, 0775, true) && !is_dir($directory)) {
        throw new RuntimeException(forum_t('Nie udalo sie utworzyc katalogu danych forum.'));
    }
}

function forum_initial_admin_password(): string
{
    $envPassword = getenv('FORUM_ADMIN_INITIAL_PASSWORD');
    if (is_string($envPassword) && strlen($envPassword) >= 10) {
        return $envPassword;
    }

    forum_ensure_storage_directory();
    if (is_file(FORUM_ADMIN_INITIAL_PASSWORD_FILE)) {
        $storedPassword = trim((string) file_get_contents(FORUM_ADMIN_INITIAL_PASSWORD_FILE));
        if (strlen($storedPassword) >= 10) {
            return $storedPassword;
        }
    }

    $password = rtrim(strtr(base64_encode(random_bytes(24)), '+/', '-_'), '=');
    if (file_put_contents(FORUM_ADMIN_INITIAL_PASSWORD_FILE, $password . PHP_EOL, LOCK_EX) === false) {
        throw new RuntimeException(forum_t('Nie udalo sie zapisac poczatkowego hasla administratora.'));
    }

    return $password;
}

function forum_initialize_database(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE COLLATE NOCASE,
            email TEXT NOT NULL UNIQUE COLLATE NOCASE,
            password_hash TEXT NOT NULL,
            role TEXT NOT NULL DEFAULT "member",
            created_at TEXT NOT NULL,
            last_login_at TEXT
        )'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            description TEXT NOT NULL DEFAULT "",
            position INTEGER NOT NULL DEFAULT 0,
            created_at TEXT NOT NULL
        )'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS forum_sections (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            description TEXT NOT NULL DEFAULT "",
            position INTEGER NOT NULL DEFAULT 0,
            created_at TEXT NOT NULL
        )'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS topics (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            category_id INTEGER NOT NULL,
            user_id INTEGER NOT NULL,
            title TEXT NOT NULL,
            is_locked INTEGER NOT NULL DEFAULT 0,
            is_pinned INTEGER NOT NULL DEFAULT 0,
            created_at TEXT NOT NULL,
            updated_at TEXT NOT NULL,
            last_post_at TEXT NOT NULL,
            last_post_user_id INTEGER NOT NULL,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (last_post_user_id) REFERENCES users(id) ON DELETE CASCADE
        )'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS posts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            topic_id INTEGER NOT NULL,
            user_id INTEGER NOT NULL,
            body TEXT NOT NULL,
            created_at TEXT NOT NULL,
            updated_at TEXT,
            FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )'
    );

    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_categories_position ON categories(position)');
    forum_ensure_column($pdo, 'categories', 'section_id', 'INTEGER');
    forum_ensure_column($pdo, 'categories', 'section_position', 'INTEGER NOT NULL DEFAULT 0');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_forum_sections_position ON forum_sections(position)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_categories_section_position ON categories(section_id, section_position)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_topics_category_last_post ON topics(category_id, last_post_at DESC)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_topics_user_created ON topics(user_id, created_at DESC)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_posts_topic_created ON posts(topic_id, created_at ASC)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_posts_user_created ON posts(user_id, created_at DESC)');

    forum_seed_defaults($pdo);
    forum_ensure_default_forum_sections($pdo);
}

function forum_ensure_column(PDO $pdo, string $table, string $column, string $definition): void
{
    $stmt = $pdo->query(sprintf('PRAGMA table_info(%s)', $table));
    foreach ($stmt->fetchAll() as $info) {
        if (($info['name'] ?? '') === $column) {
            return;
        }
    }

    $pdo->exec(sprintf('ALTER TABLE %s ADD COLUMN %s %s', $table, $column, $definition));
}

function forum_seed_defaults(PDO $pdo): void
{
    $existingAdminId = $pdo->prepare('SELECT id FROM users WHERE username = :username LIMIT 1');
    $existingAdminId->execute([':username' => FORUM_ADMIN_USERNAME]);
    $adminId = $existingAdminId->fetchColumn();

    if ($adminId === false) {
        $stmt = $pdo->prepare(
            'INSERT INTO users (username, email, password_hash, role, created_at)
             VALUES (:username, :email, :password_hash, "admin", :created_at)'
        );
        $stmt->execute([
            ':username' => FORUM_ADMIN_USERNAME,
            ':email' => FORUM_ADMIN_EMAIL,
            ':password_hash' => password_hash(forum_initial_admin_password(), PASSWORD_DEFAULT),
            ':created_at' => forum_now(),
        ]);
        $adminId = (int) $pdo->lastInsertId();
    } else {
        $adminId = (int) $adminId;
    }

    $categoryCount = (int) $pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn();
    if ($categoryCount === 0) {
        forum_insert_default_categories($pdo);
    }

    $topicCount = (int) $pdo->query('SELECT COUNT(*) FROM topics')->fetchColumn();
    if ($topicCount === 0) {
        forum_seed_default_topics($pdo, $adminId);
    }
}

function forum_ensure_default_forum_sections(PDO $pdo): void
{
    $sectionCount = (int) $pdo->query('SELECT COUNT(*) FROM forum_sections')->fetchColumn();
    if ($sectionCount === 0) {
        forum_insert_default_forum_sections($pdo);
    }

    $defaultSectionId = (int) $pdo->query('SELECT id FROM forum_sections ORDER BY position ASC, id ASC LIMIT 1')->fetchColumn();
    if ($defaultSectionId <= 0) {
        return;
    }

    $pdo->prepare(
        'UPDATE categories
         SET section_id = :section_id
         WHERE section_id IS NULL OR section_id <= 0'
    )->execute([
        ':section_id' => $defaultSectionId,
    ]);

    $categories = $pdo->query('SELECT id, position, section_position FROM categories ORDER BY position ASC, id ASC')->fetchAll();
    $position = 1;
    $update = $pdo->prepare(
        'UPDATE categories
         SET section_position = :section_position
         WHERE id = :id'
    );
    foreach ($categories as $category) {
        if ((int) ($category['section_position'] ?? 0) > 0) {
            continue;
        }
        $update->execute([
            ':section_position' => $position++,
            ':id' => (int) $category['id'],
        ]);
    }
}

function forum_default_forum_sections(): array
{
    return [
        [
            'slug' => 'start',
            'name' => 'Start i organizacja',
            'description' => 'Ogłoszenia, zasady, pierwsze pytania i sprawy techniczne związane z działaniem forum.',
        ],
        [
            'slug' => 'community',
            'name' => 'Społeczność i rozmowy',
            'description' => 'Dyskusje, pomysły, prezentacje projektów i luźniejsze tematy budujące życie forum.',
        ],
    ];
}

function forum_insert_default_forum_sections(PDO $pdo): void
{
    $insert = $pdo->prepare(
        'INSERT INTO forum_sections (name, description, position, created_at)
         VALUES (:name, :description, :position, :created_at)'
    );

    foreach (forum_default_forum_sections() as $index => $section) {
        $insert->execute([
            ':name' => $section['name'],
            ':description' => $section['description'],
            ':position' => $index + 1,
            ':created_at' => forum_now(),
        ]);
    }
}

function forum_default_section_ids(PDO $pdo): array
{
    $rows = $pdo->query('SELECT id, name FROM forum_sections ORDER BY position ASC, id ASC')->fetchAll();
    $ids = [];
    foreach (forum_default_forum_sections() as $section) {
        foreach ($rows as $row) {
            if ((string) $row['name'] === $section['name']) {
                $ids[$section['slug']] = (int) $row['id'];
                break;
            }
        }
    }

    $fallback = (int) ($rows[0]['id'] ?? 0);
    foreach (forum_default_forum_sections() as $section) {
        $ids[$section['slug']] = $ids[$section['slug']] ?? $fallback;
    }

    return $ids;
}

function forum_default_categories(): array
{
    return [
        [
            'slug' => 'announcements',
            'section_slug' => 'start',
            'name' => 'Ogłoszenia i aktualności',
            'description' => 'Ważne informacje od administracji, zmiany w forum, komunikaty techniczne i zapowiedzi kolejnych usprawnień.',
        ],
        [
            'slug' => 'first-steps',
            'section_slug' => 'start',
            'name' => 'Pierwsze kroki',
            'description' => 'Dział dla nowych użytkowników: przedstaw się, zapytaj jak zacząć i spokojnie poznaj zasady działania forum.',
        ],
        [
            'slug' => 'support',
            'section_slug' => 'start',
            'name' => 'Pomoc i pytania techniczne',
            'description' => 'Problemy z kontem, ustawieniami forum, działaniem strony i inne pytania organizacyjno-techniczne.',
        ],
        [
            'slug' => 'rules',
            'section_slug' => 'start',
            'name' => 'Regulamin i zasady',
            'description' => 'Najważniejsze zasady korzystania z forum, dobre praktyki dyskusji i informacje porządkowe dla społeczności.',
        ],
        [
            'slug' => 'ideas',
            'section_slug' => 'start',
            'name' => 'Pomysły i sugestie',
            'description' => 'Miejsce na propozycje zmian, nowe funkcje, usprawnienia działów i uwagi dotyczące rozwoju forum.',
        ],
        [
            'slug' => 'news',
            'section_slug' => 'start',
            'name' => 'Nowości na forum',
            'description' => 'Krótki przegląd zmian, nowych możliwości i rzeczy, które warto zauważyć po kolejnych aktualizacjach.',
        ],
        [
            'slug' => 'general',
            'section_slug' => 'community',
            'name' => 'Rozmowy ogólne',
            'description' => 'Swobodne dyskusje społeczności, pomysły, pytania bez sztywnej kategorii i codzienne rozmowy użytkowników.',
        ],
        [
            'slug' => 'projects',
            'section_slug' => 'community',
            'name' => 'Projekty i inspiracje',
            'description' => 'Pokaż co budujesz, opisz swój projekt, poproś o opinię albo zainspiruj innych ciekawym rozwiązaniem.',
        ],
        [
            'slug' => 'offtopic',
            'section_slug' => 'community',
            'name' => 'Off-topic',
            'description' => 'Luźniejsze tematy, rozmowy poboczne i wszystko to, co buduje klimat forum, ale nie pasuje do pozostałych działów.',
        ],
    ];
}

function forum_default_category_aliases(): array
{
    return [
        'announcements' => ['Ogłoszenia i aktualności'],
        'first-steps' => ['Pierwsze kroki'],
        'support' => ['Pomoc i pytania techniczne'],
        'rules' => ['Regulamin i zasady'],
        'ideas' => ['Pomysły i sugestie'],
        'news' => ['Nowości na forum'],
        'general' => ['Rozmowy ogólne'],
        'projects' => ['Projekty i inspiracje'],
        'offtopic' => ['Off-topic'],
    ];
}

function forum_insert_default_categories(PDO $pdo): void
{
    forum_ensure_default_forum_sections($pdo);
    $sectionIds = forum_default_section_ids($pdo);
    $insertCategory = $pdo->prepare(
        'INSERT INTO categories (name, description, position, section_id, section_position, created_at)
         VALUES (:name, :description, :position, :section_id, :section_position, :created_at)'
    );

    $sectionPositions = [];
    foreach (forum_default_categories() as $index => $category) {
        $sectionSlug = (string) ($category['section_slug'] ?? 'start');
        $sectionPositions[$sectionSlug] = ($sectionPositions[$sectionSlug] ?? 0) + 1;
        $insertCategory->execute([
            ':name' => $category['name'],
            ':description' => $category['description'],
            ':position' => $index + 1,
            ':section_id' => $sectionIds[$sectionSlug] ?? forum_normalize_forum_section_id(0),
            ':section_position' => $sectionPositions[$sectionSlug],
            ':created_at' => forum_now(),
        ]);
    }
}

function forum_sync_default_categories(PDO $pdo): void
{
    $existing = $pdo->query('SELECT id, name, description, position FROM categories ORDER BY position ASC, id ASC')->fetchAll();
    if ($existing === []) {
        forum_insert_default_categories($pdo);
        return;
    }

    $updateWithPosition = $pdo->prepare(
        'UPDATE categories
         SET name = :name, description = :description, position = :position
         WHERE id = :id'
    );
    $updateWithoutPosition = $pdo->prepare(
        'UPDATE categories
         SET name = :name, description = :description
         WHERE id = :id'
    );
    $insertCategory = $pdo->prepare(
        'INSERT INTO categories (name, description, position, section_id, section_position, created_at)
         VALUES (:name, :description, :position, :section_id, :section_position, :created_at)'
    );
    $defaultSectionId = forum_normalize_forum_section_id(0);

    $legacyLayout = forum_has_legacy_default_layout($existing);
    $usedIds = [];
    $nextPosition = (int) $pdo->query('SELECT COALESCE(MAX(position), 0) FROM categories')->fetchColumn();

    foreach (forum_default_categories() as $index => $category) {
        $match = forum_match_default_category($existing, $category, $usedIds);

        if ($match) {
            $params = [
                ':name' => $category['name'],
                ':description' => $category['description'],
                ':id' => (int) $match['id'],
            ];

            if ($legacyLayout) {
                $params[':position'] = $index + 1;
                $updateWithPosition->execute($params);
            } elseif (
                (string) $match['name'] !== $category['name']
                || (string) $match['description'] !== $category['description']
            ) {
                $updateWithoutPosition->execute($params);
            }

            $usedIds[] = (int) $match['id'];
            continue;
        }

        $position = $legacyLayout ? $index + 1 : ++$nextPosition;
        $insertCategory->execute([
            ':name' => $category['name'],
            ':description' => $category['description'],
            ':position' => $position,
            ':section_id' => $defaultSectionId,
            ':section_position' => $position,
            ':created_at' => forum_now(),
        ]);
    }

    forum_ensure_default_forum_sections($pdo);
}

function forum_match_default_category(array $existing, array $category, array $usedIds): ?array
{
    $aliases = forum_default_category_aliases()[$category['slug']] ?? [$category['name']];

    foreach ($existing as $row) {
        $id = (int) ($row['id'] ?? 0);
        if (in_array($id, $usedIds, true)) {
            continue;
        }

        if (in_array((string) ($row['name'] ?? ''), $aliases, true)) {
            return $row;
        }
    }

    return null;
}

function forum_has_legacy_default_layout(array $categories): bool
{
    if ($categories === [] || count($categories) > 4) {
        return false;
    }

    $legacySlugs = ['announcements', 'first-steps', 'sharing', 'drives'];
    $matchedSlugs = [];

    foreach ($categories as $category) {
        $slug = forum_default_slug_for_name((string) ($category['name'] ?? ''));
        if ($slug === null || !in_array($slug, $legacySlugs, true) || in_array($slug, $matchedSlugs, true)) {
            return false;
        }
        $matchedSlugs[] = $slug;
    }

    return true;
}

function forum_default_slug_for_name(string $name): ?string
{
    foreach (forum_default_category_aliases() as $slug => $aliases) {
        if (in_array($name, $aliases, true)) {
            return $slug;
        }
    }

    return null;
}

function forum_default_category_id_by_slug(PDO $pdo): array
{
    $rows = $pdo->query('SELECT id, name FROM categories ORDER BY position ASC, id ASC')->fetchAll();
    $ids = [];

    foreach (forum_default_categories() as $category) {
        $aliases = forum_default_category_aliases()[$category['slug']] ?? [$category['name']];
        foreach ($rows as $row) {
            if (in_array((string) $row['name'], $aliases, true)) {
                $ids[$category['slug']] = (int) $row['id'];
                break;
            }
        }
    }

    return $ids;
}

function forum_seed_default_topics(PDO $pdo, int $adminId): void
{
    $categoryIds = forum_default_category_id_by_slug($pdo);
    $topics = [
        'announcements' => [
            [
                'title' => 'Witamy w ForumForgeCMS',
                'body' => "To jest pierwszy komunikat administracyjny na forum.\n\nForumForgeCMS zostało uruchomione i jest gotowe do konfiguracji. Administrator może teraz dopasować kategorie, działy, opis strony, regulamin oraz ustawienia rejestracji do swojej społeczności.",
            ],
            [
                'title' => 'Jak korzystać z ogłoszeń',
                'body' => "Ten dział służy do publikowania ważnych informacji od administracji.\n\nWarto umieszczać tutaj komunikaty o zmianach w forum, planowanych pracach technicznych, nowych funkcjach oraz zasadach, które powinni znać wszyscy użytkownicy.",
            ],
            [
                'title' => 'Pierwsze rzeczy po instalacji',
                'body' => "Po pierwszym uruchomieniu forum zalecamy wykonać kilka kroków:\n\n1. Zmień hasło administratora.\n2. Uzupełnij adres e-mail konta administratora.\n3. Przejrzyj domyślne kategorie i działy.\n4. Dopasuj opis forum do swojej społeczności.\n5. Sprawdź, czy katalog forum-data jest niedostępny z przeglądarki.",
            ],
            [
                'title' => 'Zasady publikowania komunikatów',
                'body' => "Komunikaty administracyjne powinny być krótkie, jasne i konkretne.\n\nJeśli informacja dotyczy wszystkich użytkowników, warto ją przypiąć. Jeśli jest tymczasowa, można ją później odpiąć albo przenieść do odpowiedniego działu.",
            ],
        ],
        'first-steps' => [
            [
                'title' => 'Przewodnik dla nowych użytkowników',
                'body' => "Witaj na forum.\n\nJeśli jesteś tu pierwszy raz, zacznij od założenia konta, przeczytania regulaminu i krótkiego rozejrzenia się po działach. Gdy zadajesz pytanie, opisz dokładnie sytuację, dodaj ważne szczegóły i wybierz dział, który najlepiej pasuje do tematu.",
            ],
        ],
    ];

    foreach ($topics as $slug => $items) {
        $categoryId = (int) ($categoryIds[$slug] ?? 0);
        if ($categoryId <= 0) {
            continue;
        }

        foreach ($items as $topic) {
            forum_create_topic($pdo, $categoryId, $adminId, $topic['title'], $topic['body'], true);
        }
    }
}

function forum_ensure_default_topics(PDO $pdo, int $adminId): void
{
    if ((int) $pdo->query('SELECT COUNT(*) FROM topics')->fetchColumn() === 0) {
        forum_seed_default_topics($pdo, $adminId);
    }
}

function forum_now(): string
{
    return (new DateTimeImmutable('now', new DateTimeZone('Europe/Berlin')))->format(DateTimeInterface::ATOM);
}

function forum_flash(string $type, string $message): void
{
    $_SESSION['forum_flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function forum_pull_flash(): ?array
{
    if (!isset($_SESSION['forum_flash']) || !is_array($_SESSION['forum_flash'])) {
        return null;
    }

    $flash = $_SESSION['forum_flash'];
    unset($_SESSION['forum_flash']);
    return $flash;
}

function forum_csrf_token(): string
{
    if (!isset($_SESSION['forum_csrf_token']) || !is_string($_SESSION['forum_csrf_token'])) {
        $_SESSION['forum_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['forum_csrf_token'];
}

function forum_require_valid_csrf(): void
{
    $token = (string) ($_POST['csrf_token'] ?? '');
    $sessionToken = (string) ($_SESSION['forum_csrf_token'] ?? '');

    if ($token === '' || $sessionToken === '' || !hash_equals($sessionToken, $token)) {
        throw new RuntimeException(forum_t('Sesja formularza wygasła. Odśwież stronę i spróbuj ponownie.'));
    }
}

function forum_current_page(string $param = 'page'): int
{
    return max(1, (int) ($_GET[$param] ?? 1));
}

function forum_pagination(int $totalItems, int $page, int $perPage): array
{
    $perPage = max(1, $perPage);
    $totalPages = max(1, (int) ceil($totalItems / $perPage));
    $page = min(max(1, $page), $totalPages);

    return [
        'page' => $page,
        'per_page' => $perPage,
        'total_items' => max(0, $totalItems),
        'total_pages' => $totalPages,
        'offset' => ($page - 1) * $perPage,
    ];
}

function forum_rate_limit(string $key, int $maxAttempts, int $windowSeconds): void
{
    $now = time();
    $bucketKey = 'forum_rate_' . $key;
    $bucket = $_SESSION[$bucketKey] ?? [];
    if (!is_array($bucket)) {
        $bucket = [];
    }

    $bucket = array_values(array_filter(
        array_map('intval', $bucket),
        static fn(int $timestamp): bool => $timestamp > $now - $windowSeconds
    ));

    if (count($bucket) >= $maxAttempts) {
        throw new RuntimeException(forum_t('Wykonujesz te akcje zbyt szybko. Odczekaj chwilę i spróbuj ponownie.'));
    }

    $bucket[] = $now;
    $_SESSION[$bucketKey] = $bucket;
}

function forum_current_user(): ?array
{
    $userId = (int) ($_SESSION['forum_user_id'] ?? 0);
    if ($userId <= 0) {
        return null;
    }

    $stmt = forum_db()->prepare('SELECT id, username, email, role, created_at, last_login_at FROM users WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $userId]);
    $user = $stmt->fetch();

    if (!$user) {
        unset($_SESSION['forum_user_id']);
        return null;
    }

    return $user;
}

function forum_is_admin(?array $user): bool
{
    return is_array($user) && ($user['role'] ?? '') === 'admin';
}

function forum_is_moderator(?array $user): bool
{
    return is_array($user) && ($user['role'] ?? '') === 'moderator';
}

function forum_is_staff(?array $user): bool
{
    return forum_is_admin($user) || forum_is_moderator($user);
}

function forum_role_label(?string $role): string
{
    $language = function_exists('forum_ext_current_language') ? forum_ext_current_language() : 'pl';
    $labels = [
        'pl' => [
            'admin' => 'administrator',
            'moderator' => 'moderator',
            'member' => 'użytkownik',
        ],
        'en' => [
            'admin' => 'administrator',
            'moderator' => 'moderator',
            'member' => 'member',
        ],
        'de' => [
            'admin' => 'Administrator',
            'moderator' => 'Moderator',
            'member' => 'Benutzer',
        ],
    ];

    $roleKey = match ((string) $role) {
        'admin' => 'admin',
        'moderator' => 'moderator',
        default => 'member',
    };

    return $labels[$language][$roleKey] ?? $labels['en'][$roleKey] ?? $labels['pl'][$roleKey];
}

function forum_require_login(): array
{
    $user = forum_current_user();
    if (!$user) {
        forum_flash('error', forum_t('Zaloguj się, aby wykonać tę akcję.'));
        forum_redirect(forum_url(['view' => 'login']));
    }

    return $user;
}

function forum_require_admin_user(): array
{
    $user = forum_require_login();
    if (!forum_is_admin($user)) {
        forum_flash('error', forum_t('Ta sekcja jest dostepna tylko dla administratora.'));
        forum_redirect(forum_url());
    }

    return $user;
}

function forum_require_staff_user(): array
{
    $user = forum_require_login();
    if (!forum_is_staff($user)) {
        forum_flash('error', forum_t('Ta sekcja jest dostepna tylko dla moderatora lub administratora.'));
        forum_redirect(forum_url());
    }

    return $user;
}

function forum_redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function forum_url(array $params = []): string
{
    $base = 'index.php';
    if ($params === []) {
        return $base;
    }

    return $base . '?' . http_build_query($params);
}

function forum_base_url(): string
{
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $scheme = $https ? 'https' : 'http';
    $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
    $scriptDir = str_replace('\\', '/', dirname((string) ($_SERVER['SCRIPT_NAME'] ?? '/forum/index.php')));
    $scriptDir = rtrim($scriptDir, '/');

    return $scheme . '://' . $host . ($scriptDir === '' ? '' : $scriptDir);
}

function forum_url_absolute(array $params = []): string
{
    return forum_base_url() . '/' . ltrim(forum_url($params), '/');
}

function forum_escape(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function forum_render_text(?string $value): string
{
    $text = forum_escape($value);

    $text = preg_replace_callback('/\[code\](.*?)\[\/code\]/is', static function (array $matches): string {
        return '<pre class="forum-code-block"><code>' . trim($matches[1]) . '</code></pre>';
    }, $text) ?? $text;

    $text = preg_replace_callback('/^&gt;\s?(.*)$/m', static function (array $matches): string {
        return '[quote]' . $matches[1] . '[/quote]';
    }, $text) ?? $text;

    $text = preg_replace_callback('/\[quote(?:=([^\]]{1,80}))?\](.*?)\[\/quote\]/is', static function (array $matches): string {
        $author = trim((string) ($matches[1] ?? ''));
        $caption = $author !== '' ? '<strong>' . $author . ' napisał(a):</strong>' : '<strong>Cytat:</strong>';
        return '<blockquote class="forum-quote">' . $caption . '<div>' . nl2br(forum_render_inline_markup($matches[2])) . '</div></blockquote>';
    }, $text) ?? $text;

    $text = preg_replace_callback('/\[(list|olist)\](.*?)\[\/\1\]/is', static function (array $matches): string {
        $tag = strtolower((string) $matches[1]) === 'olist' ? 'ol' : 'ul';
        $items = preg_split('/\[\*\]/', (string) $matches[2]) ?: [];
        $html = '';
        foreach ($items as $item) {
            $item = trim($item);
            if ($item === '') {
                continue;
            }
            $html .= '<li>' . forum_render_inline_markup($item) . '</li>';
        }

        return $html === '' ? '' : '<' . $tag . ' class="forum-rich-list">' . $html . '</' . $tag . '>';
    }, $text) ?? $text;

    $text = forum_render_inline_markup($text);
    return nl2br($text);
}

function forum_render_inline_markup(?string $value): string
{
    $text = (string) $value;

    $patterns = [
        '/\[b\](.*?)\[\/b\]/is' => '<strong>$1</strong>',
        '/\[i\](.*?)\[\/i\]/is' => '<em>$1</em>',
        '/\[u\](.*?)\[\/u\]/is' => '<span class="forum-underline">$1</span>',
        '/\[s\](.*?)\[\/s\]/is' => '<s>$1</s>',
        '/\[sup\](.*?)\[\/sup\]/is' => '<sup>$1</sup>',
        '/\[sub\](.*?)\[\/sub\]/is' => '<sub>$1</sub>',
        '/\[left\](.*?)\[\/left\]/is' => '<span class="forum-align-left">$1</span>',
        '/\[center\](.*?)\[\/center\]/is' => '<span class="forum-align-center">$1</span>',
        '/\[right\](.*?)\[\/right\]/is' => '<span class="forum-align-right">$1</span>',
        '/\[color=([#a-zA-Z0-9(),.\s%-]{1,40})\](.*?)\[\/color\]/is' => '<span style="color:$1">$2</span>',
        '/\[size=(small|normal|large)\](.*?)\[\/size\]/is' => '<span class="forum-size-$1">$2</span>',
    ];

    foreach ($patterns as $pattern => $replacement) {
        $text = preg_replace($pattern, $replacement, $text) ?? $text;
    }

    $text = preg_replace('/\[url\](https?:\/\/[^\s\[]+)\[\/url\]/i', '<a href="$1" rel="nofollow ugc noopener noreferrer" target="_blank">$1</a>', $text) ?? $text;
    $text = preg_replace('/\[url=(https?:\/\/[^\s\]]+)\](.*?)\[\/url\]/is', '<a href="$1" rel="nofollow ugc noopener noreferrer" target="_blank">$2</a>', $text) ?? $text;
    $text = preg_replace('/\[img\](https?:\/\/[^\s\[]+)\[\/img\]/i', '<img class="forum-inline-image" src="$1" alt="Obraz w poście" loading="lazy">', $text) ?? $text;

    $replacements = [
        '&lt;3' => '<span class="forum-emoji" aria-label="serce">??</span>',
        ':-)' => '<span class="forum-emoji" aria-label="usmiech">??</span>',
        ':)' => '<span class="forum-emoji" aria-label="usmiech">??</span>',
        ':-(' => '<span class="forum-emoji" aria-label="smutek">??</span>',
        ':(' => '<span class="forum-emoji" aria-label="smutek">??</span>',
        ';-)' => '<span class="forum-emoji" aria-label="mrugniecie">??</span>',
        ';)' => '<span class="forum-emoji" aria-label="mrugniecie">??</span>',
        ':-D' => '<span class="forum-emoji" aria-label="radosc">??</span>',
        ':D' => '<span class="forum-emoji" aria-label="radosc">??</span>',
        ':-P' => '<span class="forum-emoji" aria-label="jezyk">??</span>',
        ':P' => '<span class="forum-emoji" aria-label="jezyk">??</span>',
        ':p' => '<span class="forum-emoji" aria-label="jezyk">??</span>',
        ':-O' => '<span class="forum-emoji" aria-label="zaskoczenie">??</span>',
        ':O' => '<span class="forum-emoji" aria-label="zaskoczenie">??</span>',
        ':o' => '<span class="forum-emoji" aria-label="zaskoczenie">??</span>',
        ':-/' => '<span class="forum-emoji" aria-label="zaklopotanie">??</span>',
        ':/' => '<span class="forum-emoji" aria-label="zaklopotanie">??</span>',
        ':-|' => '<span class="forum-emoji" aria-label="neutralnie">??</span>',
        ':|' => '<span class="forum-emoji" aria-label="neutralnie">??</span>',
    ];

    return str_replace(array_keys($replacements), array_values($replacements), $text);
}

function forum_trimmed_text($value, int $maxLength = 5000): string
{
    $text = trim((string) $value);
    $text = preg_replace("/\r\n?/", "\n", $text) ?? $text;
    $text = preg_replace("/[^\P{C}\n\t]/u", '', $text) ?? $text;
    if (function_exists('mb_strlen') && mb_strlen($text, 'UTF-8') > $maxLength) {
        $text = (string) mb_substr($text, 0, $maxLength, 'UTF-8');
    } elseif (strlen($text) > $maxLength) {
        $text = substr($text, 0, $maxLength);
    }

    return trim($text);
}

function forum_register_user(string $username, string $email, string $password, bool $acceptedTerms): void
{
    $username = forum_trimmed_text($username, 40);
    $email = forum_trimmed_text($email, 190);

    if (!$acceptedTerms) {
        throw new RuntimeException(forum_t('Aby założyć konto, musisz zaakceptować regulamin strony i forum.'));
    }

    if ($username === '' || !preg_match('/^[A-Za-z0-9._-]{3,40}$/', $username)) {
        throw new RuntimeException(forum_t('Nazwa użytkownika musi mieć 3-40 znaków i może zawierać litery, cyfry, kropki, myślniki oraz podkreślenia.'));
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException(forum_t('Podaj poprawny adres e-mail.'));
    }

    forum_antispam_check_identity($username, $email);

    if (strlen($password) < 10) {
        throw new RuntimeException(forum_t('Hasło musi mieć co najmniej 10 znaków.'));
    }

    $stmt = forum_db()->prepare(
        'INSERT INTO users (username, email, password_hash, role, created_at)
         VALUES (:username, :email, :password_hash, "member", :created_at)'
    );
    try {
        $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ':created_at' => forum_now(),
        ]);
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            throw new RuntimeException(forum_t('Taki login albo adres e-mail jest juz zajety.'), 0, $e);
        }
        throw $e;
    }
}

function forum_login_user(string $login, string $password): void
{
    $login = trim($login);
    $stmt = forum_db()->prepare(
        'SELECT * FROM users WHERE username = :login OR email = :login LIMIT 1'
    );
    $stmt->execute([':login' => $login]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, (string) $user['password_hash'])) {
        throw new RuntimeException(forum_t('Nie udało się zalogować. Sprawdź login i hasło.'));
    }

    session_regenerate_id(true);
    $_SESSION['forum_user_id'] = (int) $user['id'];

    $update = forum_db()->prepare('UPDATE users SET last_login_at = :last_login_at WHERE id = :id');
    $update->execute([
        ':last_login_at' => forum_now(),
        ':id' => (int) $user['id'],
    ]);
}

function forum_logout_user(): void
{
    unset($_SESSION['forum_user_id']);
    session_regenerate_id(true);
}

function forum_change_password(int $userId, string $currentPassword, string $newPassword): void
{
    $stmt = forum_db()->prepare('SELECT password_hash FROM users WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $userId]);
    $hash = $stmt->fetchColumn();

    if (!$hash || !password_verify($currentPassword, (string) $hash)) {
        throw new RuntimeException(forum_t('Aktualne hasło jest niepoprawne.'));
    }

    if (strlen($newPassword) < 10) {
        throw new RuntimeException(forum_t('Nowe hasło musi mieć co najmniej 10 znaków.'));
    }

    $update = forum_db()->prepare('UPDATE users SET password_hash = :password_hash WHERE id = :id');
    $update->execute([
        ':password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
        ':id' => $userId,
    ]);
}

function forum_update_user_by_admin(int $userId, string $username, string $email, string $role, string $newPassword = ''): void
{
    $pdo = forum_db();
    $stmt = $pdo->prepare('SELECT id, username, email, role FROM users WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $userId]);
    $existing = $stmt->fetch();

    if (!$existing) {
        throw new RuntimeException(forum_t('Nie znaleziono użytkownika do edycji.'));
    }

    $isPrimaryAdmin = strcasecmp((string) $existing['username'], FORUM_ADMIN_USERNAME) === 0;

    if ($isPrimaryAdmin) {
        $username = FORUM_ADMIN_USERNAME;
        $role = 'admin';
    } else {
        $username = forum_trimmed_text($username, 40);
        if ($username === '' || !preg_match('/^[A-Za-z0-9._-]{3,40}$/', $username)) {
            throw new RuntimeException(forum_t('Nazwa użytkownika musi mieć 3-40 znaków i może zawierać litery, cyfry, kropki, myślniki oraz podkreślenia.'));
        }
    }

    $email = forum_trimmed_text($email, 190);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException(forum_t('Podaj poprawny adres e-mail użytkownika.'));
    }

    $allowedRoles = $isPrimaryAdmin ? ['admin'] : ['member', 'moderator'];
    if (!in_array($role, $allowedRoles, true)) {
        throw new RuntimeException(forum_t('Wybrana rola użytkownika jest nieprawidłowa.'));
    }

    $duplicate = $pdo->prepare(
        'SELECT id
         FROM users
         WHERE (username = :username OR email = :email)
           AND id <> :id
         LIMIT 1'
    );
    $duplicate->execute([
        ':username' => $username,
        ':email' => $email,
        ':id' => $userId,
    ]);
    if ($duplicate->fetchColumn() !== false) {
        throw new RuntimeException(forum_t('Taki login albo adres e-mail jest juz zajety.'));
    }

    $passwordHash = null;
    $newPassword = trim($newPassword);
    if ($newPassword !== '') {
        if (strlen($newPassword) < 10) {
            throw new RuntimeException(forum_t('Nowe hasło dla użytkownika musi mieć co najmniej 10 znaków.'));
        }
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
    }

    if ($passwordHash !== null) {
        $update = $pdo->prepare(
            'UPDATE users
             SET username = :username, email = :email, role = :role, password_hash = :password_hash
             WHERE id = :id'
        );
        $update->execute([
            ':username' => $username,
            ':email' => $email,
            ':role' => $role,
            ':password_hash' => $passwordHash,
            ':id' => $userId,
        ]);
        return;
    }

    $update = $pdo->prepare(
        'UPDATE users
         SET username = :username, email = :email, role = :role
         WHERE id = :id'
    );
    $update->execute([
        ':username' => $username,
        ':email' => $email,
        ':role' => $role,
        ':id' => $userId,
    ]);
}

function forum_fetch_categories(): array
{
    $sql = 'SELECT
                c.*,
                fs.name AS section_name,
                fs.description AS section_description,
                fs.position AS section_order,
                COUNT(DISTINCT t.id) AS topic_count,
                COUNT(p.id) AS post_count,
                MAX(t.last_post_at) AS last_post_at
            FROM categories c
            LEFT JOIN forum_sections fs ON fs.id = c.section_id
            LEFT JOIN topics t ON t.category_id = c.id
            LEFT JOIN posts p ON p.topic_id = t.id
            GROUP BY c.id
            ORDER BY COALESCE(fs.position, 999999) ASC, COALESCE(c.section_position, c.position) ASC, c.id ASC';
    return forum_db()->query($sql)->fetchAll();
}

function forum_fetch_forum_sections(): array
{
    return forum_db()
        ->query('SELECT * FROM forum_sections ORDER BY position ASC, id ASC')
        ->fetchAll();
}

function forum_fetch_sections_with_categories(): array
{
    $sections = [];
    foreach (forum_fetch_forum_sections() as $section) {
        $section['categories'] = [];
        $sections[(int) $section['id']] = $section;
    }

    foreach (forum_fetch_categories() as $category) {
        $sectionId = (int) ($category['section_id'] ?? 0);
        if (!isset($sections[$sectionId])) {
            $sectionId = array_key_first($sections);
        }
        if ($sectionId !== null && isset($sections[$sectionId])) {
            $sections[$sectionId]['categories'][] = $category;
        }
    }

    return array_values($sections);
}

function forum_fetch_category(int $categoryId): ?array
{
    $stmt = forum_db()->prepare('SELECT * FROM categories WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $categoryId]);
    $category = $stmt->fetch();
    return $category ?: null;
}

function forum_count_topics_for_category(int $categoryId): int
{
    $stmt = forum_db()->prepare('SELECT COUNT(*) FROM topics WHERE category_id = :category_id');
    $stmt->execute([':category_id' => $categoryId]);
    return (int) $stmt->fetchColumn();
}

function forum_fetch_topics_for_category(int $categoryId, int $page = 1, int $perPage = FORUM_TOPICS_PER_PAGE): array
{
    $total = forum_count_topics_for_category($categoryId);
    $pagination = forum_pagination($total, $page, $perPage);
    $stmt = forum_db()->prepare(
        'SELECT
            t.*,
            u.username AS author_username,
            lp.username AS last_post_username,
            COUNT(p.id) AS post_count
         FROM topics t
         INNER JOIN users u ON u.id = t.user_id
         INNER JOIN users lp ON lp.id = t.last_post_user_id
         LEFT JOIN posts p ON p.topic_id = t.id
         WHERE t.category_id = :category_id
         GROUP BY t.id
         ORDER BY t.is_pinned DESC, t.last_post_at DESC, t.id DESC'
        . ' LIMIT :limit OFFSET :offset'
    );
    $stmt->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
    $stmt->bindValue(':limit', (int) $pagination['per_page'], PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int) $pagination['offset'], PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function forum_fetch_topic(int $topicId): ?array
{
    $stmt = forum_db()->prepare(
        'SELECT
            t.*,
            c.name AS category_name,
            c.id AS category_id,
            u.username AS author_username,
            lp.username AS last_post_username
         FROM topics t
         INNER JOIN categories c ON c.id = t.category_id
         INNER JOIN users u ON u.id = t.user_id
         INNER JOIN users lp ON lp.id = t.last_post_user_id
         WHERE t.id = :id
         LIMIT 1'
    );
    $stmt->execute([':id' => $topicId]);
    $topic = $stmt->fetch();
    return $topic ?: null;
}

function forum_count_posts_for_topic(int $topicId): int
{
    $stmt = forum_db()->prepare('SELECT COUNT(*) FROM posts WHERE topic_id = :topic_id');
    $stmt->execute([':topic_id' => $topicId]);
    return (int) $stmt->fetchColumn();
}

function forum_fetch_posts_for_topic(int $topicId, int $page = 1, int $perPage = FORUM_POSTS_PER_PAGE): array
{
    $total = forum_count_posts_for_topic($topicId);
    $pagination = forum_pagination($total, $page, $perPage);
    $stmt = forum_db()->prepare(
        'SELECT
            p.*,
            u.username,
            u.role
         FROM posts p
         INNER JOIN users u ON u.id = p.user_id
         WHERE p.topic_id = :topic_id
         ORDER BY p.created_at ASC, p.id ASC
         LIMIT :limit OFFSET :offset'
    );
    $stmt->bindValue(':topic_id', $topicId, PDO::PARAM_INT);
    $stmt->bindValue(':limit', (int) $pagination['per_page'], PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int) $pagination['offset'], PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function forum_create_topic(PDO $pdo, int $categoryId, int $userId, string $title, string $body, bool $pin = false): int
{
    $title = forum_trimmed_text($title, 140);
    $body = forum_trimmed_text($body, 12000);

    if ($title === '' || strlen($title) < 4) {
        throw new RuntimeException(forum_t('Tytul tematu musi miec co najmniej 4 znaki.'));
    }

    if ($body === '' || strlen($body) < 10) {
        throw new RuntimeException(forum_t('Pierwsza wiadomość musi mieć co najmniej 10 znaków.'));
    }

    $timestamp = forum_now();
    $pdo->beginTransaction();
    try {
        $insertTopic = $pdo->prepare(
            'INSERT INTO topics (category_id, user_id, title, is_locked, is_pinned, created_at, updated_at, last_post_at, last_post_user_id)
             VALUES (:category_id, :user_id, :title, 0, :is_pinned, :created_at, :updated_at, :last_post_at, :last_post_user_id)'
        );
        $insertTopic->execute([
            ':category_id' => $categoryId,
            ':user_id' => $userId,
            ':title' => $title,
            ':is_pinned' => $pin ? 1 : 0,
            ':created_at' => $timestamp,
            ':updated_at' => $timestamp,
            ':last_post_at' => $timestamp,
            ':last_post_user_id' => $userId,
        ]);

        $topicId = (int) $pdo->lastInsertId();

        $insertPost = $pdo->prepare(
            'INSERT INTO posts (topic_id, user_id, body, created_at)
             VALUES (:topic_id, :user_id, :body, :created_at)'
        );
        $insertPost->execute([
            ':topic_id' => $topicId,
            ':user_id' => $userId,
            ':body' => $body,
            ':created_at' => $timestamp,
        ]);

        $pdo->commit();
        return $topicId;
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function forum_create_topic_from_request(int $categoryId, int $userId, string $title, string $body): int
{
    return forum_create_topic(forum_db(), $categoryId, $userId, $title, $body, false);
}

function forum_create_post(int $topicId, int $userId, string $body): void
{
    $body = forum_trimmed_text($body, 12000);
    if ($body === '' || strlen($body) < 3) {
        throw new RuntimeException(forum_t('Odpowiedz jest za kr?tka.'));
    }

    $pdo = forum_db();
    $timestamp = forum_now();
    $pdo->beginTransaction();
    try {
        $insert = $pdo->prepare(
            'INSERT INTO posts (topic_id, user_id, body, created_at)
             VALUES (:topic_id, :user_id, :body, :created_at)'
        );
        $insert->execute([
            ':topic_id' => $topicId,
            ':user_id' => $userId,
            ':body' => $body,
            ':created_at' => $timestamp,
        ]);

        $updateTopic = $pdo->prepare(
            'UPDATE topics
             SET updated_at = :updated_at, last_post_at = :last_post_at, last_post_user_id = :last_post_user_id
             WHERE id = :id'
        );
        $updateTopic->execute([
            ':updated_at' => $timestamp,
            ':last_post_at' => $timestamp,
            ':last_post_user_id' => $userId,
            ':id' => $topicId,
        ]);

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function forum_create_forum_section(string $name, string $description): void
{
    $name = forum_trimmed_text($name, 80);
    $description = forum_trimmed_text($description, 260);

    if ($name === '') {
        throw new RuntimeException(forum_t('Nazwa kategorii forum nie może być pusta.'));
    }

    $position = (int) forum_db()->query('SELECT COALESCE(MAX(position), 0) + 1 FROM forum_sections')->fetchColumn();
    forum_db()->prepare(
        'INSERT INTO forum_sections (name, description, position, created_at)
         VALUES (:name, :description, :position, :created_at)'
    )->execute([
        ':name' => $name,
        ':description' => $description,
        ':position' => $position,
        ':created_at' => forum_now(),
    ]);
}

function forum_update_forum_section(int $sectionId, string $name, string $description): void
{
    $name = forum_trimmed_text($name, 80);
    $description = forum_trimmed_text($description, 260);

    if ($name === '') {
        throw new RuntimeException(forum_t('Nazwa kategorii forum nie może być pusta.'));
    }

    $stmt = forum_db()->prepare(
        'UPDATE forum_sections
         SET name = :name, description = :description
         WHERE id = :id'
    );
    $stmt->execute([
        ':name' => $name,
        ':description' => $description,
        ':id' => $sectionId,
    ]);
}

function forum_create_category(string $name, string $description, int $sectionId = 0): void
{
    $name = forum_trimmed_text($name, 80);
    $description = forum_trimmed_text($description, 260);

    if ($name === '') {
        throw new RuntimeException(forum_t('Nazwa działu nie może być pusta.'));
    }

    $sectionId = forum_normalize_forum_section_id($sectionId);
    $position = (int) forum_db()->query('SELECT COALESCE(MAX(position), 0) + 1 FROM categories')->fetchColumn();
    $sectionPositionStmt = forum_db()->prepare('SELECT COALESCE(MAX(section_position), 0) + 1 FROM categories WHERE section_id = :section_id');
    $sectionPositionStmt->execute([':section_id' => $sectionId]);
    $sectionPosition = (int) $sectionPositionStmt->fetchColumn();
    $stmt = forum_db()->prepare(
        'INSERT INTO categories (name, description, position, section_id, section_position, created_at)
         VALUES (:name, :description, :position, :section_id, :section_position, :created_at)'
    );
    $stmt->execute([
        ':name' => $name,
        ':description' => $description,
        ':position' => $position,
        ':section_id' => $sectionId,
        ':section_position' => $sectionPosition,
        ':created_at' => forum_now(),
    ]);
}

function forum_update_category(int $categoryId, string $name, string $description, int $sectionId = 0): void
{
    $name = forum_trimmed_text($name, 80);
    $description = forum_trimmed_text($description, 260);

    if ($name === '') {
        throw new RuntimeException(forum_t('Nazwa działu nie może być pusta.'));
    }

    $pdo = forum_db();
    $sectionId = forum_normalize_forum_section_id($sectionId);
    $currentStmt = $pdo->prepare('SELECT section_id FROM categories WHERE id = :id LIMIT 1');
    $currentStmt->execute([':id' => $categoryId]);
    $currentSectionId = (int) ($currentStmt->fetchColumn() ?: 0);
    $sectionPosition = null;

    if ($sectionId !== $currentSectionId) {
        $sectionPositionStmt = $pdo->prepare('SELECT COALESCE(MAX(section_position), 0) + 1 FROM categories WHERE section_id = :section_id');
        $sectionPositionStmt->execute([':section_id' => $sectionId]);
        $sectionPosition = (int) $sectionPositionStmt->fetchColumn();
    }

    if ($sectionPosition !== null) {
        $stmt = $pdo->prepare('UPDATE categories SET name = :name, description = :description, section_id = :section_id, section_position = :section_position WHERE id = :id');
        $stmt->execute([
            ':name' => $name,
            ':description' => $description,
            ':section_id' => $sectionId,
            ':section_position' => $sectionPosition,
            ':id' => $categoryId,
        ]);
        forum_reindex_section_categories($currentSectionId);
        return;
    }

    $stmt = $pdo->prepare('UPDATE categories SET name = :name, description = :description, section_id = :section_id WHERE id = :id');
    $stmt->execute([
        ':name' => $name,
        ':description' => $description,
        ':section_id' => $sectionId,
        ':id' => $categoryId,
    ]);
}

function forum_normalize_forum_section_id(int $sectionId): int
{
    $pdo = forum_db();
    if ($sectionId > 0) {
        $stmt = $pdo->prepare('SELECT id FROM forum_sections WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $sectionId]);
        $found = $stmt->fetchColumn();
        if ($found !== false) {
            return (int) $found;
        }
    }

    $fallback = (int) $pdo->query('SELECT id FROM forum_sections ORDER BY position ASC, id ASC LIMIT 1')->fetchColumn();
    if ($fallback <= 0) {
        forum_ensure_default_forum_sections($pdo);
        $fallback = (int) $pdo->query('SELECT id FROM forum_sections ORDER BY position ASC, id ASC LIMIT 1')->fetchColumn();
    }

    return $fallback;
}

function forum_reindex_section_categories(int $sectionId): void
{
    if ($sectionId <= 0) {
        return;
    }

    $pdo = forum_db();
    $stmt = $pdo->prepare('SELECT id FROM categories WHERE section_id = :section_id ORDER BY section_position ASC, position ASC, id ASC');
    $stmt->execute([':section_id' => $sectionId]);
    $update = $pdo->prepare('UPDATE categories SET section_position = :position WHERE id = :id');
    $position = 1;
    foreach ($stmt->fetchAll() as $category) {
        $update->execute([
            ':position' => $position++,
            ':id' => (int) $category['id'],
        ]);
    }
}

function forum_delete_category(int $categoryId): void
{
    $topicCountStmt = forum_db()->prepare('SELECT COUNT(*) FROM topics WHERE category_id = :category_id');
    $topicCountStmt->execute([':category_id' => $categoryId]);
    if ((int) $topicCountStmt->fetchColumn() > 0) {
        throw new RuntimeException(forum_t('Najpierw przenieś lub usuń tematy z tego działu.'));
    }

    $stmt = forum_db()->prepare('DELETE FROM categories WHERE id = :id');
    $stmt->execute([':id' => $categoryId]);
}

function forum_move_category(int $categoryId, string $direction): void
{
    $currentStmt = forum_db()->prepare('SELECT section_id FROM categories WHERE id = :id LIMIT 1');
    $currentStmt->execute([':id' => $categoryId]);
    $sectionId = (int) ($currentStmt->fetchColumn() ?: 0);
    $stmt = forum_db()->prepare('SELECT * FROM categories WHERE section_id = :section_id ORDER BY section_position ASC, position ASC, id ASC');
    $stmt->execute([':section_id' => $sectionId]);
    $categories = $stmt->fetchAll();
    $ids = array_column($categories, 'id');
    $currentIndex = array_search($categoryId, $ids, true);
    if ($currentIndex === false) {
        return;
    }

    $targetIndex = $direction === 'up' ? $currentIndex - 1 : $currentIndex + 1;
    if (!isset($categories[$targetIndex])) {
        return;
    }

    $pdo = forum_db();
    $pdo->beginTransaction();
    try {
        $current = $categories[$currentIndex];
        $target = $categories[$targetIndex];

        $stmt = $pdo->prepare('UPDATE categories SET section_position = :position WHERE id = :id');
        $stmt->execute([
            ':position' => (int) $target['section_position'],
            ':id' => (int) $current['id'],
        ]);
        $stmt->execute([
            ':position' => (int) $current['section_position'],
            ':id' => (int) $target['id'],
        ]);

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function forum_toggle_topic_flag(int $topicId, string $flag): void
{
    if (!in_array($flag, ['is_locked', 'is_pinned'], true)) {
        throw new RuntimeException(forum_t('Nieznana flaga tematu.'));
    }

    $topic = forum_fetch_topic($topicId);
    if (!$topic) {
        throw new RuntimeException(forum_t('Nie znaleziono tematu.'));
    }

    $newValue = ((int) $topic[$flag] === 1) ? 0 : 1;
    $stmt = forum_db()->prepare('UPDATE topics SET ' . $flag . ' = :value WHERE id = :id');
    $stmt->execute([
        ':value' => $newValue,
        ':id' => $topicId,
    ]);
}

function forum_delete_topic(int $topicId): void
{
    $stmt = forum_db()->prepare('DELETE FROM topics WHERE id = :id');
    $stmt->execute([':id' => $topicId]);
}

function forum_admin_summary(): array
{
    $pdo = forum_db();
    return [
        'users' => (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
        'categories' => (int) $pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn(),
        'topics' => (int) $pdo->query('SELECT COUNT(*) FROM topics')->fetchColumn(),
        'posts' => (int) $pdo->query('SELECT COUNT(*) FROM posts')->fetchColumn(),
    ];
}

function forum_recent_topics(int $limit = 8): array
{
    $stmt = forum_db()->prepare(
        'SELECT
            t.*,
            c.name AS category_name,
            u.username AS author_username,
            lp.username AS last_post_username,
            COUNT(p.id) AS post_count
         FROM topics t
         INNER JOIN categories c ON c.id = t.category_id
         INNER JOIN users u ON u.id = t.user_id
         INNER JOIN users lp ON lp.id = t.last_post_user_id
         LEFT JOIN posts p ON p.topic_id = t.id
         GROUP BY t.id
         ORDER BY t.is_pinned DESC, t.last_post_at DESC
         LIMIT :limit'
    );
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function forum_format_date(?string $value): string
{
    if (!$value) {
        return forum_t('brak');
    }

    try {
        return (new DateTimeImmutable($value))->setTimezone(new DateTimeZone('Europe/Berlin'))->format('d.m.Y H:i');
    } catch (Throwable $e) {
        return forum_escape($value);
    }
}

function forum_render_header(string $title, string $description = 'Forum dyskusyjne ForumForgeCMS', string $currentView = 'forum'): void
{
    $user = forum_current_user();
    ?>
<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="<?php echo forum_escape($description); ?>">
  <meta name="robots" content="index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1">
  <title><?php echo forum_escape($title); ?> - ForumForgeCMS</title>
  <link rel="icon" type="image/png" sizes="32x32" href="media/images/favicons/favicon-32x32.png">
  <link rel="stylesheet" href="assets/forumforgecms2026.css">
  <link rel="stylesheet" href="assets/forum.css">
</head>
<body>
  <div class="site-shell">
    <header class="site-header">
      <div class="site-header-inner">
        <a class="brand" href="index.php"><span class="brand-mark">FFC</span><span class="brand-copy">ForumForgeCMS<small><?php echo forum_escape(forum_t('samodzielne forum dla Twojej społeczności')); ?></small></span></a>
        <nav class="nav-links" aria-label="<?php echo forum_escape(forum_t('Gl?wna nawigacja')); ?>">
          <a href="index.php" aria-current="page"><?php echo forum_escape(forum_t('Forum')); ?></a>
        </nav>
        <details class="mobile-nav">
          <summary><?php echo forum_escape(forum_t('Menu')); ?></summary>
          <div class="mobile-links">
            <a href="index.php" aria-current="page"><?php echo forum_escape(forum_t('Forum')); ?></a>
          </div>
        </details>
      </div>
    </header>
    <main class="page-wrap">
      <section class="hero forum-hero">
        <div class="forum-hero-grid">
          <div>
            <span class="eyebrow"><?php echo forum_escape(forum_t('Społeczność')); ?></span>
            <h1><?php echo forum_escape($title); ?></h1>
            <p class="lead"><?php echo forum_escape($description); ?></p>
          </div>
          <div class="forum-user-card">
            <?php if ($user): ?>
              <strong><?php echo forum_escape($user['username']); ?></strong>
              <p><?php echo forum_escape(forum_t('Zalogowany jako')); ?> <?php echo forum_escape(forum_role_label((string) ($user['role'] ?? 'member'))); ?>.</p>
              <div class="forum-button-row">
                <a class="button-secondary" href="<?php echo forum_url(['view' => 'account']); ?>"><?php echo forum_escape(forum_t('Moje konto')); ?></a>
                <?php if (forum_is_admin($user)): ?>
                  <a class="button-secondary" href="<?php echo forum_url(['view' => 'admin']); ?>"><?php echo forum_escape(forum_t('Panel admina')); ?></a>
                <?php endif; ?>
                <form method="post" action="<?php echo forum_url(); ?>" class="forum-inline-form">
                  <input type="hidden" name="action" value="logout">
                  <input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
                  <button class="button" type="submit"><?php echo forum_escape(forum_t('Wyloguj')); ?></button>
                </form>
              </div>
            <?php else: ?>
              <strong><?php echo forum_escape(forum_t('Dolacz do dyskusji')); ?></strong>
              <p><?php echo forum_escape(forum_t('Załóż konto, aby pisać posty, wysyłać prywatne wiadomości i budować swój profil na forum.')); ?></p>
              <div class="forum-button-row">
                <a class="button" href="<?php echo forum_url(['view' => 'register']); ?>"><?php echo forum_escape(forum_t('Zal?z konto')); ?></a>
                <a class="button-secondary" href="<?php echo forum_url(['view' => 'login']); ?>"><?php echo forum_escape(forum_t('Zaloguj sie')); ?></a>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </section>
<?php
}

function forum_render_footer(): void
{
    ?>
    </main>
    <footer class="site-footer">
      <div class="site-footer-inner">
        <?php echo forum_escape(forum_t('© 2026 Stronę zbudował')); ?> <a href="https://zawalka.com">Piotr Zawalka</a> (<a href="https://wowo89.de/">https://wowo89.de/</a>) - <a href="mailto:piotr@zawalka.com">piotr@zawalka.com</a>
      </div>
    </footer>
  </div>
</body>
</html>
<?php
}





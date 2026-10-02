<?php
declare(strict_types=1);

function forum_ext_fetch_topic(int $topicId): ?array
{
    $stmt = forum_db()->prepare(
        'SELECT
            t.*,
            c.name AS category_name,
            c.id AS category_id,
            u.username AS author_username,
            lp.username AS last_post_username,
            (
                SELECT COUNT(*)
                FROM posts p
                WHERE p.topic_id = t.id
            ) AS post_count
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

function forum_ext_fetch_posts_for_topic(int $topicId, int $viewerUserId = 0, int $page = 1, int $perPage = FORUM_POSTS_PER_PAGE): array
{
    $pagination = forum_pagination(forum_count_posts_for_topic($topicId), $page, $perPage);
    $stmt = forum_db()->prepare(
        'SELECT
            p.*,
            u.id AS profile_user_id,
            u.username,
            u.role,
            u.avatar_filename,
            u.avatar_updated_at,
            eu.username AS edited_by_username,
            (
                SELECT COUNT(*)
                FROM posts up
                WHERE up.user_id = u.id
            ) AS user_post_count,
            (
                SELECT COUNT(*)
                FROM topics ut
                WHERE ut.user_id = u.id
            ) AS user_topic_count,
            (
                SELECT COUNT(*)
                FROM post_likes upl
                INNER JOIN posts lp ON lp.id = upl.post_id
                WHERE lp.user_id = u.id
            ) AS user_likes_received,
            (
                SELECT COUNT(*)
                FROM post_likes pl
                WHERE pl.post_id = p.id
            ) AS like_count,
            (
                SELECT COUNT(*)
                FROM post_likes vpl
                WHERE vpl.post_id = p.id
                  AND vpl.user_id = :viewer_id
            ) AS viewer_liked,
            CASE
                WHEN p.id = (
                    SELECT fp.id
                    FROM posts fp
                    WHERE fp.topic_id = p.topic_id
                    ORDER BY fp.created_at ASC, fp.id ASC
                    LIMIT 1
                ) THEN 1 ELSE 0
            END AS is_topic_starter
         FROM posts p
         INNER JOIN users u ON u.id = p.user_id
         LEFT JOIN users eu ON eu.id = p.edited_by_user_id
         WHERE p.topic_id = :topic_id
         ORDER BY p.created_at ASC, p.id ASC
         LIMIT :limit OFFSET :offset'
    );
    $stmt->bindValue(':topic_id', $topicId, PDO::PARAM_INT);
    $stmt->bindValue(':viewer_id', $viewerUserId, PDO::PARAM_INT);
    $stmt->bindValue(':limit', (int) $pagination['per_page'], PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int) $pagination['offset'], PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

function forum_ext_fetch_post(int $postId): ?array
{
    $stmt = forum_db()->prepare(
        'SELECT
            p.*,
            p.user_id,
            u.username,
            t.title AS topic_title,
            t.id AS topic_id,
            c.name AS category_name,
            t.is_locked,
            CASE
                WHEN p.id = (
                    SELECT fp.id
                    FROM posts fp
                    WHERE fp.topic_id = p.topic_id
                    ORDER BY fp.created_at ASC, fp.id ASC
                    LIMIT 1
                ) THEN 1 ELSE 0
            END AS is_topic_starter
         FROM posts p
         INNER JOIN topics t ON t.id = p.topic_id
         INNER JOIN categories c ON c.id = t.category_id
         INNER JOIN users u ON u.id = p.user_id
         WHERE p.id = :id
         LIMIT 1'
    );
    $stmt->execute([':id' => $postId]);
    $post = $stmt->fetch();

    return $post ?: null;
}

function forum_ext_user_can_edit_post(array $actor, array $post): bool
{
    return forum_is_staff($actor) || (int) $actor['id'] === (int) $post['user_id'];
}

function forum_ext_update_post(int $postId, array $actor, string $body, ?string $topicTitle = null): int
{
    $post = forum_ext_fetch_post($postId);
    if (!$post) {
        throw new RuntimeException(forum_t('Nie znaleziono postu do edycji.'));
    }

    if (!forum_ext_user_can_edit_post($actor, $post)) {
        throw new RuntimeException(forum_t('Nie masz uprawnień do edycji tego postu.'));
    }

    if ((int) $post['is_locked'] === 1 && !forum_is_admin($actor)) {
        throw new RuntimeException(forum_t('Ten temat jest zamknięty. Tylko administrator może jeszcze edytować post.'));
    }

    $body = forum_trimmed_text($body, 12000);
    if ($body === '' || strlen($body) < 3) {
        throw new RuntimeException(forum_t('Post po edycji jest za krótki.'));
    }

    $timestamp = forum_now();
    $pdo = forum_db();
    $pdo->beginTransaction();

    try {
        $pdo->prepare(
            'UPDATE posts
             SET body = :body, updated_at = :updated_at, edited_by_user_id = :edited_by_user_id
             WHERE id = :id'
        )->execute([
            ':body' => $body,
            ':updated_at' => $timestamp,
            ':edited_by_user_id' => (int) $actor['id'],
            ':id' => $postId,
        ]);

        if ((int) $post['is_topic_starter'] === 1) {
            $newTitle = forum_trimmed_text((string) $topicTitle, 140);
            if ($newTitle === '' || strlen($newTitle) < 4) {
                throw new RuntimeException(forum_t('Tytuł tematu musi mieć co najmniej 4 znaki.'));
            }

            $pdo->prepare('UPDATE topics SET title = :title, updated_at = :updated_at WHERE id = :id')->execute([
                ':title' => $newTitle,
                ':updated_at' => $timestamp,
                ':id' => (int) $post['topic_id'],
            ]);
        }

        $pdo->commit();
        return (int) $post['topic_id'];
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function forum_ext_update_post_with_staff(int $postId, array $actor, string $body, ?string $topicTitle = null): int
{
    $post = forum_ext_fetch_post($postId);
    if (!$post) {
        throw new RuntimeException(forum_t('Nie znaleziono postu do edycji.'));
    }

    if (!forum_ext_user_can_edit_post($actor, $post)) {
        throw new RuntimeException(forum_t('Nie masz uprawnień do edycji tego postu.'));
    }

    if ((int) $post['is_locked'] === 1 && !forum_is_staff($actor)) {
        throw new RuntimeException(forum_t('Ten temat jest zamknięty. Tylko moderator albo administrator może jeszcze edytować post.'));
    }

    $body = forum_trimmed_text($body, 12000);
    if ($body === '' || strlen($body) < 3) {
        throw new RuntimeException(forum_t('Post po edycji jest za krótki.'));
    }

    $timestamp = forum_now();
    $pdo = forum_db();
    $pdo->beginTransaction();

    try {
        $pdo->prepare(
            'UPDATE posts
             SET body = :body, updated_at = :updated_at, edited_by_user_id = :edited_by_user_id
             WHERE id = :id'
        )->execute([
            ':body' => $body,
            ':updated_at' => $timestamp,
            ':edited_by_user_id' => (int) $actor['id'],
            ':id' => $postId,
        ]);

        if ((int) $post['is_topic_starter'] === 1) {
            $newTitle = forum_trimmed_text((string) $topicTitle, 140);
            if ($newTitle === '' || strlen($newTitle) < 4) {
                throw new RuntimeException(forum_t('Tytuł tematu musi mieć co najmniej 4 znaki.'));
            }

            $pdo->prepare('UPDATE topics SET title = :title, updated_at = :updated_at WHERE id = :id')->execute([
                ':title' => $newTitle,
                ':updated_at' => $timestamp,
                ':id' => (int) $post['topic_id'],
            ]);
        }

        $pdo->commit();
        return (int) $post['topic_id'];
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function forum_ext_toggle_post_like(int $postId, int $userId): void
{
    $post = forum_ext_fetch_post($postId);
    if (!$post) {
        throw new RuntimeException(forum_t('Nie znaleziono wskazanego postu.'));
    }

    $pdo = forum_db();
    $check = $pdo->prepare('SELECT id FROM post_likes WHERE post_id = :post_id AND user_id = :user_id LIMIT 1');
    $check->execute([
        ':post_id' => $postId,
        ':user_id' => $userId,
    ]);
    $existing = $check->fetchColumn();

    if ($existing !== false) {
        $pdo->prepare('DELETE FROM post_likes WHERE id = :id')->execute([':id' => (int) $existing]);
        return;
    }

    $pdo->prepare(
        'INSERT INTO post_likes (post_id, user_id, created_at)
         VALUES (:post_id, :user_id, :created_at)'
    )->execute([
        ':post_id' => $postId,
        ':user_id' => $userId,
        ':created_at' => forum_now(),
    ]);
}

function forum_ext_quote_text_for_post(int $postId): string
{
    $post = forum_ext_fetch_post($postId);
    if (!$post) {
        throw new RuntimeException(forum_t('Nie znaleziono posta do zacytowania.'));
    }

    $author = (string) ($post['username'] ?? 'uzytkownik');
    $body = trim((string) ($post['body'] ?? ''));
    $lines = preg_split("/\r\n|\r|\n/", $body) ?: [];
    $quotedLines = array_map(static fn(string $line): string => '> ' . $line, array_slice($lines, 0, 12));

    return '> ' . $author . " napisal(a):\n" . implode("\n", $quotedLines) . "\n\n";
}

function forum_ext_report_post(int $postId, int $reporterUserId, string $reason = ''): void
{
    $post = forum_ext_fetch_post($postId);
    if (!$post) {
        throw new RuntimeException(forum_t('Nie znaleziono posta do zgloszenia.'));
    }

    if ((int) $post['user_id'] === $reporterUserId) {
        throw new RuntimeException(forum_t('Nie mozesz zglosic wlasnego posta.'));
    }

    $reason = forum_trimmed_text($reason, 500);
    $pdo = forum_db();
    $existing = $pdo->prepare(
        'SELECT id FROM post_reports
         WHERE post_id = :post_id
           AND reporter_user_id = :reporter_user_id
           AND status = "open"
         LIMIT 1'
    );
    $existing->execute([
        ':post_id' => $postId,
        ':reporter_user_id' => $reporterUserId,
    ]);

    if ($existing->fetchColumn() !== false) {
        throw new RuntimeException(forum_t('Ten post jest juz przez Ciebie zgloszony.'));
    }

    $pdo->prepare(
        'INSERT INTO post_reports (post_id, reporter_user_id, reason, status, created_at)
         VALUES (:post_id, :reporter_user_id, :reason, "open", :created_at)'
    )->execute([
        ':post_id' => $postId,
        ':reporter_user_id' => $reporterUserId,
        ':reason' => $reason,
        ':created_at' => forum_now(),
    ]);
}

function forum_ext_open_post_reports(): array
{
    $stmt = forum_db()->query(
        'SELECT
            pr.*,
            reporter.username AS reporter_username,
            author.username AS post_author_username,
            p.body AS post_body,
            p.created_at AS post_created_at,
            t.id AS topic_id,
            t.title AS topic_title,
            c.name AS category_name
         FROM post_reports pr
         INNER JOIN posts p ON p.id = pr.post_id
         INNER JOIN topics t ON t.id = p.topic_id
         INNER JOIN categories c ON c.id = t.category_id
         INNER JOIN users reporter ON reporter.id = pr.reporter_user_id
         INNER JOIN users author ON author.id = p.user_id
         WHERE pr.status = "open"
         ORDER BY pr.created_at ASC, pr.id ASC'
    );

    return $stmt->fetchAll();
}

function forum_ext_close_post_report(int $reportId, int $actorUserId): void
{
    $stmt = forum_db()->prepare(
        'UPDATE post_reports
         SET status = "closed", closed_at = :closed_at, closed_by_user_id = :closed_by_user_id
         WHERE id = :id
           AND status = "open"'
    );
    $stmt->execute([
        ':closed_at' => forum_now(),
        ':closed_by_user_id' => $actorUserId,
        ':id' => $reportId,
    ]);

    if ($stmt->rowCount() === 0) {
        throw new RuntimeException(forum_t('Nie znaleziono aktywnego zgloszenia do zamkniecia.'));
    }
}

function forum_ext_fetch_user_profile(int $userId): ?array
{
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
            ) AS likes_given
         FROM users u
         WHERE u.id = :id
         LIMIT 1'
    );
    $stmt->execute([':id' => $userId]);
    $user = $stmt->fetch();

    return $user ?: null;
}

function forum_ext_recent_posts_for_user(int $userId, int $limit = 5): array
{
    $stmt = forum_db()->prepare(
        'SELECT
            p.id,
            p.topic_id,
            p.body,
            p.created_at,
            p.updated_at,
            t.title AS topic_title
         FROM posts p
         INNER JOIN topics t ON t.id = p.topic_id
         WHERE p.user_id = :user_id
         ORDER BY p.created_at DESC, p.id DESC
         LIMIT :limit'
    );
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

function forum_ext_recent_topics_for_user(int $userId, int $limit = 5): array
{
    $stmt = forum_db()->prepare(
        'SELECT
            t.id,
            t.title,
            t.created_at,
            t.last_post_at,
            c.name AS category_name
         FROM topics t
         INNER JOIN categories c ON c.id = t.category_id
         WHERE t.user_id = :user_id
         ORDER BY t.created_at DESC, t.id DESC
         LIMIT :limit'
    );
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

function forum_ext_avatar_url(array $user): string
{
    $version = rawurlencode((string) ($user['avatar_updated_at'] ?? $user['created_at'] ?? $user['id'] ?? '0'));
    return 'avatar.php?id=' . (int) ($user['id'] ?? 0) . '&v=' . $version;
}

function forum_ext_brand_logo_url(): string
{
    $filename = forum_ext_setting('brand_logo_filename');
    if ($filename === '') {
        return '';
    }

    return 'logo.php?v=' . rawurlencode($filename);
}

function forum_ext_delete_avatar_file(string $filename): void
{
    $path = FORUM_AVATAR_DIR . '/' . basename($filename);
    if (is_file($path)) {
        @unlink($path);
    }
}

function forum_ext_delete_brand_logo_file(string $filename): void
{
    $path = FORUM_BRAND_DIR . '/' . basename($filename);
    if (is_file($path)) {
        @unlink($path);
    }
}

function forum_ext_save_browser_webp_upload(string $tmpPath, string $destination, int $size, string $label): void
{
    $imageInfo = @getimagesize($tmpPath);
    if (!is_array($imageInfo) || ($imageInfo['mime'] ?? '') !== 'image/webp') {
        throw new RuntimeException(forum_t('{label} musi zostać wysłany jako obraz WebP.', ['label' => $label]));
    }

    if ((int) ($imageInfo[0] ?? 0) !== $size || (int) ($imageInfo[1] ?? 0) !== $size) {
        throw new RuntimeException(forum_t('{label} musi mieć rozmiar {size}x{size} px.', ['label' => $label, 'size' => $size]));
    }

    if (!move_uploaded_file($tmpPath, $destination)) {
        throw new RuntimeException(forum_t('Nie udalo sie zapisac obrazu na serwerze.'));
    }
}

function forum_ext_handle_avatar_upload(int $userId, array $file): void
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        throw new RuntimeException(forum_t('Wybierz plik z avatarem.'));
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException(forum_t('Nie udało się przesłać pliku z avatarem.'));
    }

    $tmpPath = (string) ($file['tmp_name'] ?? '');
    if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
        throw new RuntimeException(forum_t('Przesłany plik z avatarem jest nieprawidłowy.'));
    }

    if ((int) ($file['size'] ?? 0) > FORUM_UPLOAD_MAX_BYTES) {
        throw new RuntimeException(forum_t('Avatar może mieć maksymalnie 2 MB.'));
    }

    $imageInfo = @getimagesize($tmpPath);
    if (!is_array($imageInfo) || empty($imageInfo['mime'])) {
        throw new RuntimeException(forum_t('Avatar musi być poprawnym obrazem.'));
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    $mime = (string) $imageInfo['mime'];
    if (!isset($allowed[$mime])) {
        throw new RuntimeException(forum_t('Dozwolone formaty avatara to JPG, PNG, WEBP i GIF.'));
    }

    $oldStmt = forum_db()->prepare('SELECT avatar_filename FROM users WHERE id = :id LIMIT 1');
    $oldStmt->execute([':id' => $userId]);
    $oldFilename = (string) $oldStmt->fetchColumn();

    $filename = 'u' . $userId . '_' . bin2hex(random_bytes(12)) . '.webp';
    $destination = FORUM_AVATAR_DIR . '/' . $filename;

    forum_ext_save_browser_webp_upload($tmpPath, $destination, 160, forum_t('Avatar'));

    forum_db()->prepare(
        'UPDATE users SET avatar_filename = :avatar_filename, avatar_updated_at = :avatar_updated_at WHERE id = :id'
    )->execute([
        ':avatar_filename' => $filename,
        ':avatar_updated_at' => forum_now(),
        ':id' => $userId,
    ]);

    if ($oldFilename !== '') {
        forum_ext_delete_avatar_file($oldFilename);
    }
}

function forum_ext_handle_brand_logo_upload(array $file): void
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return;
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException(forum_t('Nie udalo sie przeslac pliku z logo.'));
    }

    $tmpPath = (string) ($file['tmp_name'] ?? '');
    if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
        throw new RuntimeException(forum_t('Przeslany plik z logo jest nieprawidlowy.'));
    }

    if ((int) ($file['size'] ?? 0) > FORUM_UPLOAD_MAX_BYTES) {
        throw new RuntimeException(forum_t('Logo moze miec maksymalnie 2 MB.'));
    }

    $oldFilename = forum_ext_setting('brand_logo_filename');
    $filename = 'brand_' . bin2hex(random_bytes(12)) . '.webp';
    $destination = FORUM_BRAND_DIR . '/' . $filename;

    forum_ext_save_browser_webp_upload($tmpPath, $destination, 192, 'Logo');
    forum_ext_store_setting(forum_db(), 'brand_logo_filename', $filename);

    if ($oldFilename !== '') {
        forum_ext_delete_brand_logo_file($oldFilename);
    }
}

function forum_ext_output_brand_logo_image(): void
{
    forum_ext_boot();

    $filename = forum_ext_setting('brand_logo_filename');
    if ($filename === '') {
        throw new RuntimeException(forum_t('Logo nie zostalo ustawione.'));
    }

    $path = FORUM_BRAND_DIR . '/' . basename($filename);
    if (!is_file($path) || !is_readable($path)) {
        throw new RuntimeException(forum_t('Nie znaleziono pliku logo.'));
    }

    header('Content-Type: image/webp');
    header('Content-Length: ' . (string) filesize($path));
    header('Cache-Control: public, max-age=86400');
    readfile($path);
    exit;
}

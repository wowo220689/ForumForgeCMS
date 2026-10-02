<?php
declare(strict_types=1);

function forum_ext_remove_avatar(int $userId): void
{
    $stmt = forum_db()->prepare('SELECT avatar_filename FROM users WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $userId]);
    $filename = (string) $stmt->fetchColumn();

    forum_db()->prepare('UPDATE users SET avatar_filename = NULL, avatar_updated_at = :updated_at WHERE id = :id')->execute([
        ':updated_at' => forum_now(),
        ':id' => $userId,
    ]);

    if ($filename !== '') {
        forum_ext_delete_avatar_file($filename);
    }
}

function forum_ext_output_avatar_image(int $userId): void
{
    forum_ext_boot();

    $stmt = forum_db()->prepare('SELECT id, username, avatar_filename FROM users WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $userId]);
    $user = $stmt->fetch();

    if ($user && !empty($user['avatar_filename'])) {
        $path = FORUM_AVATAR_DIR . '/' . basename((string) $user['avatar_filename']);
        if (is_file($path) && is_readable($path)) {
            $mime = 'application/octet-stream';
            if (function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                if ($finfo !== false) {
                    $detected = finfo_file($finfo, $path);
                    if (is_string($detected) && $detected !== '') {
                        $mime = $detected;
                    }
                    finfo_close($finfo);
                }
            }

            header('Content-Type: ' . $mime);
            header('Content-Length: ' . (string) filesize($path));
            header('Cache-Control: public, max-age=86400');
            readfile($path);
            exit;
        }
    }

    $username = is_array($user) ? (string) ($user['username'] ?? 'U') : 'U';
    $initial = strtoupper(function_exists('mb_substr') ? (string) mb_substr($username, 0, 1, 'UTF-8') : substr($username, 0, 1));
    $initial = htmlspecialchars($initial !== '' ? $initial : 'U', ENT_QUOTES, 'UTF-8');

    header('Content-Type: image/svg+xml; charset=UTF-8');
    header('Cache-Control: public, max-age=3600');
    echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 160 160" role="img" aria-label="Avatar">'
        . '<defs><linearGradient id="g" x1="0" x2="1" y1="0" y2="1">'
        . '<stop offset="0%" stop-color="#0f766e"/>'
        . '<stop offset="100%" stop-color="#1d4ed8"/>'
        . '</linearGradient></defs>'
        . '<rect width="160" height="160" rx="32" fill="url(#g)"/>'
        . '<text x="50%" y="54%" text-anchor="middle" dominant-baseline="middle" font-family="Segoe UI, Arial, sans-serif" font-size="64" font-weight="700" fill="#ffffff">'
        . $initial
        . '</text></svg>';
    exit;
}

function forum_ext_count_inbox(int $userId): int
{
    $stmt = forum_db()->prepare(
        'SELECT COUNT(*)
         FROM private_messages
         WHERE recipient_id = :user_id
           AND deleted_by_recipient = 0'
    );
    $stmt->execute([':user_id' => $userId]);
    return (int) $stmt->fetchColumn();
}

function forum_ext_count_outbox(int $userId): int
{
    $stmt = forum_db()->prepare(
        'SELECT COUNT(*)
         FROM private_messages
         WHERE sender_id = :user_id
           AND deleted_by_sender = 0'
    );
    $stmt->execute([':user_id' => $userId]);
    return (int) $stmt->fetchColumn();
}

function forum_ext_fetch_inbox(int $userId, int $page = 1, int $perPage = FORUM_MESSAGES_PER_PAGE): array
{
    $pagination = forum_pagination(forum_ext_count_inbox($userId), $page, $perPage);
    $stmt = forum_db()->prepare(
        'SELECT
            pm.*,
            s.username AS sender_username,
            s.avatar_filename AS sender_avatar_filename,
            s.avatar_updated_at AS sender_avatar_updated_at
         FROM private_messages pm
         INNER JOIN users s ON s.id = pm.sender_id
         WHERE pm.recipient_id = :user_id
           AND pm.deleted_by_recipient = 0
         ORDER BY pm.created_at DESC, pm.id DESC
         LIMIT :limit OFFSET :offset'
    );
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':limit', (int) $pagination['per_page'], PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int) $pagination['offset'], PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

function forum_ext_fetch_outbox(int $userId, int $page = 1, int $perPage = FORUM_MESSAGES_PER_PAGE): array
{
    $pagination = forum_pagination(forum_ext_count_outbox($userId), $page, $perPage);
    $stmt = forum_db()->prepare(
        'SELECT
            pm.*,
            r.username AS recipient_username,
            r.avatar_filename AS recipient_avatar_filename,
            r.avatar_updated_at AS recipient_avatar_updated_at
         FROM private_messages pm
         INNER JOIN users r ON r.id = pm.recipient_id
         WHERE pm.sender_id = :user_id
           AND pm.deleted_by_sender = 0
         ORDER BY pm.created_at DESC, pm.id DESC
         LIMIT :limit OFFSET :offset'
    );
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':limit', (int) $pagination['per_page'], PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int) $pagination['offset'], PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

function forum_ext_fetch_message(int $messageId, int $userId): ?array
{
    $stmt = forum_db()->prepare(
        'SELECT
            pm.*,
            s.username AS sender_username,
            s.role AS sender_role,
            s.avatar_filename AS sender_avatar_filename,
            s.avatar_updated_at AS sender_avatar_updated_at,
            r.username AS recipient_username,
            r.role AS recipient_role,
            r.avatar_filename AS recipient_avatar_filename,
            r.avatar_updated_at AS recipient_avatar_updated_at
         FROM private_messages pm
         INNER JOIN users s ON s.id = pm.sender_id
         INNER JOIN users r ON r.id = pm.recipient_id
         WHERE pm.id = :id
           AND (
                (pm.sender_id = :user_id AND pm.deleted_by_sender = 0)
                OR
                (pm.recipient_id = :user_id AND pm.deleted_by_recipient = 0)
           )
         LIMIT 1'
    );
    $stmt->execute([
        ':id' => $messageId,
        ':user_id' => $userId,
    ]);

    $message = $stmt->fetch();
    return $message ?: null;
}

function forum_ext_mark_message_read(int $messageId, int $userId): void
{
    forum_db()->prepare(
        'UPDATE private_messages
         SET read_at = COALESCE(read_at, :read_at)
         WHERE id = :id
           AND recipient_id = :user_id'
    )->execute([
        ':read_at' => forum_now(),
        ':id' => $messageId,
        ':user_id' => $userId,
    ]);
}

function forum_ext_send_message(int $senderId, string $recipientLogin, string $subject, string $body): int
{
    $recipientLogin = forum_trimmed_text($recipientLogin, 190);
    $subject = forum_trimmed_text($subject, 180);
    $body = forum_trimmed_text($body, 12000);

    if ($recipientLogin === '') {
        throw new RuntimeException(forum_t('Wskaż odbiorcę wiadomości.'));
    }
    if ($subject === '' || strlen($subject) < 3) {
        throw new RuntimeException(forum_t('Temat wiadomości musi mieć co najmniej 3 znaki.'));
    }
    if ($body === '' || strlen($body) < 5) {
        throw new RuntimeException(forum_t('Treść wiadomości jest za krótka.'));
    }

    $stmt = forum_db()->prepare('SELECT id FROM users WHERE username = :login OR email = :login LIMIT 1');
    $stmt->execute([':login' => $recipientLogin]);
    $recipient = $stmt->fetch();
    if (!$recipient) {
        throw new RuntimeException(forum_t('Nie znaleziono użytkownika, do którego chcesz napisać.'));
    }

    if ((int) $recipient['id'] === $senderId) {
        throw new RuntimeException(forum_t('Nie możesz wysłać wiadomości do samego siebie.'));
    }

    forum_db()->prepare(
        'INSERT INTO private_messages (sender_id, recipient_id, subject, body, created_at)
         VALUES (:sender_id, :recipient_id, :subject, :body, :created_at)'
    )->execute([
        ':sender_id' => $senderId,
        ':recipient_id' => (int) $recipient['id'],
        ':subject' => $subject,
        ':body' => $body,
        ':created_at' => forum_now(),
    ]);

    return (int) forum_db()->lastInsertId();
}

function forum_ext_send_mail(string $to, string $subject, string $body): bool
{
    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'From: ForumForgeCMS <' . FORUM_MAIL_FROM . '>',
        'Reply-To: ' . FORUM_MAIL_FROM,
    ];

    $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    return mail($to, $encodedSubject, $body, implode("\r\n", $headers));
}

function forum_ext_request_password_reset(string $login): void
{
    $login = forum_trimmed_text($login, 190);
    if ($login === '') {
        throw new RuntimeException(forum_t('Podaj login lub adres e-mail.'));
    }

    $stmt = forum_db()->prepare('SELECT id, username, email FROM users WHERE username = :login OR email = :login LIMIT 1');
    $stmt->execute([':login' => $login]);
    $user = $stmt->fetch();
    if (!$user) {
        return;
    }

    forum_db()->prepare('DELETE FROM password_reset_tokens WHERE user_id = :user_id')->execute([
        ':user_id' => (int) $user['id'],
    ]);

    $selector = bin2hex(random_bytes(8));
    $token = bin2hex(random_bytes(24));
    $expiresAt = (new DateTimeImmutable('now', new DateTimeZone('Europe/Berlin')))
        ->modify('+' . FORUM_PASSWORD_RESET_TTL_HOURS . ' hours')
        ->format(DateTimeInterface::ATOM);

    forum_db()->prepare(
        'INSERT INTO password_reset_tokens (user_id, selector, token_hash, created_at, expires_at)
         VALUES (:user_id, :selector, :token_hash, :created_at, :expires_at)'
    )->execute([
        ':user_id' => (int) $user['id'],
        ':selector' => $selector,
        ':token_hash' => password_hash($token, PASSWORD_DEFAULT),
        ':created_at' => forum_now(),
        ':expires_at' => $expiresAt,
    ]);

    $resetUrl = forum_url_absolute([
        'view' => 'reset-password',
        'selector' => $selector,
        'token' => $token,
    ]);

    $body = forum_t('mail.reset.body', ['username' => $user['username'], 'url' => $resetUrl, 'hours' => FORUM_PASSWORD_RESET_TTL_HOURS]);

    if (!forum_ext_send_mail((string) $user['email'], forum_t('mail.reset.subject'), $body)) {
        throw new RuntimeException(forum_t('Nie udało się wysłać wiadomości resetującej hasło. Spróbuj ponownie później.'));
    }
}

function forum_ext_validate_reset_token(string $selector, string $token): ?array
{
    $stmt = forum_db()->prepare(
        'SELECT *
         FROM password_reset_tokens
         WHERE selector = :selector
           AND used_at IS NULL
         LIMIT 1'
    );
    $stmt->execute([':selector' => $selector]);
    $record = $stmt->fetch();
    if (!$record) {
        return null;
    }

    if (!password_verify($token, (string) $record['token_hash'])) {
        return null;
    }

    try {
        $expires = new DateTimeImmutable((string) $record['expires_at']);
    } catch (Throwable $e) {
        return null;
    }

    if ($expires < new DateTimeImmutable('now', new DateTimeZone('Europe/Berlin'))) {
        return null;
    }

    return $record;
}

function forum_ext_reset_password_with_token(string $selector, string $token, string $newPassword): void
{
    if (strlen($newPassword) < 10) {
        throw new RuntimeException(forum_t('Nowe hasło musi mieć co najmniej 10 znaków.'));
    }

    $record = forum_ext_validate_reset_token($selector, $token);
    if (!$record) {
        throw new RuntimeException(forum_t('Link do resetu hasła jest nieprawidłowy albo wygasł.'));
    }

    $pdo = forum_db();
    $pdo->beginTransaction();

    try {
        $pdo->prepare('UPDATE users SET password_hash = :password_hash WHERE id = :id')->execute([
            ':password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
            ':id' => (int) $record['user_id'],
        ]);

        $pdo->prepare('UPDATE password_reset_tokens SET used_at = :used_at WHERE id = :id')->execute([
            ':used_at' => forum_now(),
            ':id' => (int) $record['id'],
        ]);

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function forum_ext_request_user_activity_purge(int $targetUserId, int $requestedByUserId): void
{
    $pdo = forum_db();
    $stmt = $pdo->prepare(
        'SELECT id, username, email, role
         FROM users
         WHERE id = :id
         LIMIT 1'
    );
    $stmt->execute([':id' => $targetUserId]);
    $targetUser = $stmt->fetch();

    if (!$targetUser) {
        throw new RuntimeException(forum_t('Nie znaleziono użytkownika, którego aktywność chcesz usunąć.'));
    }

    $requesterStmt = $pdo->prepare(
        'SELECT id, username
         FROM users
         WHERE id = :id
         LIMIT 1'
    );
    $requesterStmt->execute([':id' => $requestedByUserId]);
    $requestedBy = $requesterStmt->fetch();

    if (!$requestedBy || ($requestedBy['username'] ?? '') === '') {
        throw new RuntimeException(forum_t('Nie udało się potwierdzić konta administratora.'));
    }

    $summary = forum_ext_fetch_user_activity_totals((int) $targetUser['id']);
    $selector = bin2hex(random_bytes(8));
    $token = bin2hex(random_bytes(24));
    $expiresAt = (new DateTimeImmutable('now', new DateTimeZone('Europe/Berlin')))
        ->modify('+' . FORUM_ACTIVITY_PURGE_TTL_HOURS . ' hours')
        ->format(DateTimeInterface::ATOM);

    $pdo->beginTransaction();

    try {
        $pdo->prepare(
            'DELETE FROM admin_activity_purge_requests
             WHERE target_user_id = :target_user_id
               AND used_at IS NULL'
        )->execute([
            ':target_user_id' => (int) $targetUser['id'],
        ]);

        $pdo->prepare(
            'INSERT INTO admin_activity_purge_requests (
                target_user_id,
                requested_by_user_id,
                selector,
                token_hash,
                created_at,
                expires_at
             ) VALUES (
                :target_user_id,
                :requested_by_user_id,
                :selector,
                :token_hash,
                :created_at,
                :expires_at
             )'
        )->execute([
            ':target_user_id' => (int) $targetUser['id'],
            ':requested_by_user_id' => (int) $requestedBy['id'],
            ':selector' => $selector,
            ':token_hash' => password_hash($token, PASSWORD_DEFAULT),
            ':created_at' => forum_now(),
            ':expires_at' => $expiresAt,
        ]);

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }

    $confirmUrl = forum_url_absolute([
        'view' => 'confirm-user-activity-purge',
        'selector' => $selector,
        'token' => $token,
    ]);

    $body = forum_t('mail.purge.body', [
        'username' => $targetUser['username'], 'email' => $targetUser['email'],
        'role' => forum_role_label((string) ($targetUser['role'] ?? 'member')),
        'admin' => $requestedBy['username'], 'topics' => $summary['topic_count'],
        'posts' => $summary['post_count'], 'likes' => $summary['likes_given'],
        'messages' => $summary['message_count'], 'url' => $confirmUrl,
        'hours' => FORUM_ACTIVITY_PURGE_TTL_HOURS,
    ]);

    if (!forum_ext_send_mail(FORUM_ACTIVITY_PURGE_CONFIRM_EMAIL, forum_t('mail.purge.subject'), $body)) {
        $pdo->prepare(
            'DELETE FROM admin_activity_purge_requests
             WHERE selector = :selector
               AND used_at IS NULL'
        )->execute([
            ':selector' => $selector,
        ]);

        throw new RuntimeException(forum_t('Nie udało się wysłać wiadomości potwierdzającej na adres administratora. Spróbuj ponownie później.'));
    }
}

function forum_ext_validate_user_activity_purge_request(string $selector, string $token): ?array
{
    $stmt = forum_db()->prepare(
        'SELECT
            req.*,
            target.username AS target_username,
            target.email AS target_email,
            requester.username AS requested_by_username
         FROM admin_activity_purge_requests req
         INNER JOIN users target ON target.id = req.target_user_id
         INNER JOIN users requester ON requester.id = req.requested_by_user_id
         WHERE req.selector = :selector
           AND req.used_at IS NULL
         LIMIT 1'
    );
    $stmt->execute([':selector' => $selector]);
    $record = $stmt->fetch();

    if (!$record) {
        return null;
    }

    if (!password_verify($token, (string) $record['token_hash'])) {
        return null;
    }

    try {
        $expires = new DateTimeImmutable((string) $record['expires_at']);
    } catch (Throwable $e) {
        return null;
    }

    if ($expires < new DateTimeImmutable('now', new DateTimeZone('Europe/Berlin'))) {
        return null;
    }

    return $record;
}

function forum_ext_execute_user_activity_purge(string $selector, string $token, int $adminUserId): array
{
    $record = forum_ext_validate_user_activity_purge_request($selector, $token);
    if (!$record) {
        throw new RuntimeException(forum_t('Link potwierdzający jest nieprawidłowy albo wygasł.'));
    }

    $pdo = forum_db();
    $targetUserId = (int) $record['target_user_id'];
    $summary = forum_ext_fetch_user_activity_totals($targetUserId);
    $touchedTopicIds = $pdo->prepare(
        'SELECT DISTINCT topic_id
         FROM posts
         WHERE user_id = :user_id'
    );
    $touchedTopicIds->execute([':user_id' => $targetUserId]);
    $topicIds = array_map(static fn(array $row): int => (int) $row['topic_id'], $touchedTopicIds->fetchAll());

    $ownedTopicIdsStmt = $pdo->prepare(
        'SELECT id
         FROM topics
         WHERE user_id = :user_id'
    );
    $ownedTopicIdsStmt->execute([':user_id' => $targetUserId]);
    $ownedTopicIds = array_map(static fn(array $row): int => (int) $row['id'], $ownedTopicIdsStmt->fetchAll());

    $pdo->beginTransaction();

    try {
        $pdo->prepare(
            'DELETE FROM private_messages
             WHERE sender_id = :user_id
                OR recipient_id = :user_id'
        )->execute([
            ':user_id' => $targetUserId,
        ]);

        $pdo->prepare(
            'DELETE FROM post_likes
             WHERE user_id = :user_id'
        )->execute([
            ':user_id' => $targetUserId,
        ]);

        $pdo->prepare(
            'DELETE FROM topics
             WHERE user_id = :user_id'
        )->execute([
            ':user_id' => $targetUserId,
        ]);

        $pdo->prepare(
            'DELETE FROM posts
             WHERE user_id = :user_id'
        )->execute([
            ':user_id' => $targetUserId,
        ]);

        forum_ext_rebuild_topics_after_activity_purge($pdo, array_values(array_diff($topicIds, $ownedTopicIds)));

        $pdo->prepare(
            'UPDATE admin_activity_purge_requests
             SET used_at = :used_at,
                 executed_by_user_id = :executed_by_user_id
             WHERE id = :id'
        )->execute([
            ':used_at' => forum_now(),
            ':executed_by_user_id' => $adminUserId,
            ':id' => (int) $record['id'],
        ]);

        $pdo->prepare(
            'DELETE FROM admin_activity_purge_requests
             WHERE target_user_id = :target_user_id
               AND id <> :id
               AND used_at IS NULL'
        )->execute([
            ':target_user_id' => $targetUserId,
            ':id' => (int) $record['id'],
        ]);

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }

    return [
        'username' => (string) $record['target_username'],
        'topic_count' => (int) $summary['topic_count'],
        'post_count' => (int) $summary['post_count'],
        'likes_given' => (int) $summary['likes_given'],
        'message_count' => (int) $summary['message_count'],
    ];
}

function forum_ext_delete_user_completely(int $targetUserId, int $adminUserId): array
{
    if ($targetUserId <= 0) {
        throw new RuntimeException(forum_t('Nie wybrano użytkownika do usunięcia.'));
    }

    if ($targetUserId === $adminUserId) {
        throw new RuntimeException(forum_t('Nie możesz usunąć własnego konta administratora.'));
    }

    $pdo = forum_db();
    $stmt = $pdo->prepare(
        'SELECT id, username, role, avatar_filename
         FROM users
         WHERE id = :id
         LIMIT 1'
    );
    $stmt->execute([':id' => $targetUserId]);
    $targetUser = $stmt->fetch();

    if (!$targetUser) {
        throw new RuntimeException(forum_t('Nie znaleziono użytkownika do usunięcia.'));
    }

    if (strcasecmp((string) $targetUser['username'], FORUM_ADMIN_USERNAME) === 0 || (string) $targetUser['role'] === 'admin') {
        throw new RuntimeException(forum_t('Głównego konta administratora nie można usunąć z panelu.'));
    }

    $summary = forum_ext_fetch_user_activity_totals($targetUserId);
    $avatarFilename = (string) ($targetUser['avatar_filename'] ?? '');
    $touchedTopicIds = $pdo->prepare(
        'SELECT DISTINCT topic_id
         FROM posts
         WHERE user_id = :user_id'
    );
    $touchedTopicIds->execute([':user_id' => $targetUserId]);
    $topicIds = array_map(static fn(array $row): int => (int) $row['topic_id'], $touchedTopicIds->fetchAll());

    $ownedTopicIdsStmt = $pdo->prepare(
        'SELECT id
         FROM topics
         WHERE user_id = :user_id'
    );
    $ownedTopicIdsStmt->execute([':user_id' => $targetUserId]);
    $ownedTopicIds = array_map(static fn(array $row): int => (int) $row['id'], $ownedTopicIdsStmt->fetchAll());

    $pdo->beginTransaction();

    try {
        $pdo->prepare(
            'DELETE FROM private_messages
             WHERE sender_id = :user_id
                OR recipient_id = :user_id'
        )->execute([
            ':user_id' => $targetUserId,
        ]);

        $pdo->prepare(
            'DELETE FROM post_likes
             WHERE user_id = :user_id'
        )->execute([
            ':user_id' => $targetUserId,
        ]);

        $pdo->prepare(
            'DELETE FROM admin_activity_purge_requests
             WHERE target_user_id = :user_id
                OR requested_by_user_id = :user_id
                OR executed_by_user_id = :user_id'
        )->execute([
            ':user_id' => $targetUserId,
        ]);

        $pdo->prepare(
            'DELETE FROM password_reset_tokens
             WHERE user_id = :user_id'
        )->execute([
            ':user_id' => $targetUserId,
        ]);

        $pdo->prepare(
            'UPDATE posts
             SET edited_by_user_id = NULL
             WHERE edited_by_user_id = :user_id'
        )->execute([
            ':user_id' => $targetUserId,
        ]);

        $pdo->prepare(
            'DELETE FROM topics
             WHERE user_id = :user_id'
        )->execute([
            ':user_id' => $targetUserId,
        ]);

        $pdo->prepare(
            'DELETE FROM posts
             WHERE user_id = :user_id'
        )->execute([
            ':user_id' => $targetUserId,
        ]);

        forum_ext_rebuild_topics_after_activity_purge($pdo, array_values(array_diff($topicIds, $ownedTopicIds)));

        $pdo->prepare(
            'DELETE FROM users
             WHERE id = :id'
        )->execute([
            ':id' => $targetUserId,
        ]);

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }

    if ($avatarFilename !== '') {
        forum_ext_delete_avatar_file($avatarFilename);
    }

    return [
        'username' => (string) $targetUser['username'],
        'topic_count' => (int) $summary['topic_count'],
        'post_count' => (int) $summary['post_count'],
        'likes_given' => (int) $summary['likes_given'],
        'message_count' => (int) $summary['message_count'],
    ];
}

function forum_ext_fetch_user_activity_totals(int $userId): array
{
    $stmt = forum_db()->prepare(
        'SELECT
            (
                SELECT COUNT(*)
                FROM topics t
                WHERE t.user_id = :user_id
            ) AS topic_count,
            (
                SELECT COUNT(*)
                FROM posts p
                WHERE p.user_id = :user_id
            ) AS post_count,
            (
                SELECT COUNT(*)
                FROM post_likes pl
                WHERE pl.user_id = :user_id
            ) AS likes_given,
            (
                SELECT COUNT(*)
                FROM private_messages pm
                WHERE pm.sender_id = :user_id
                   OR pm.recipient_id = :user_id
            ) AS message_count'
    );
    $stmt->execute([':user_id' => $userId]);
    $row = $stmt->fetch() ?: [];

    return [
        'topic_count' => (int) ($row['topic_count'] ?? 0),
        'post_count' => (int) ($row['post_count'] ?? 0),
        'likes_given' => (int) ($row['likes_given'] ?? 0),
        'message_count' => (int) ($row['message_count'] ?? 0),
    ];
}

function forum_ext_rebuild_topics_after_activity_purge(PDO $pdo, array $topicIds): void
{
    $topicIds = array_values(array_unique(array_map('intval', $topicIds)));
    if ($topicIds === []) {
        return;
    }

    $latestPostStmt = $pdo->prepare(
        'SELECT
            id,
            user_id,
            created_at
         FROM posts
         WHERE topic_id = :topic_id
         ORDER BY created_at DESC, id DESC
         LIMIT 1'
    );
    $updateTopicStmt = $pdo->prepare(
        'UPDATE topics
         SET updated_at = :updated_at,
             last_post_at = :last_post_at,
             last_post_user_id = :last_post_user_id
         WHERE id = :topic_id'
    );
    $deleteTopicStmt = $pdo->prepare(
        'DELETE FROM topics
         WHERE id = :topic_id'
    );

    foreach ($topicIds as $topicId) {
        if ($topicId <= 0) {
            continue;
        }

        $latestPostStmt->execute([':topic_id' => $topicId]);
        $latestPost = $latestPostStmt->fetch();

        if (!$latestPost) {
            $deleteTopicStmt->execute([':topic_id' => $topicId]);
            continue;
        }

        $updateTopicStmt->execute([
            ':updated_at' => (string) $latestPost['created_at'],
            ':last_post_at' => (string) $latestPost['created_at'],
            ':last_post_user_id' => (int) $latestPost['user_id'],
            ':topic_id' => $topicId,
        ]);
    }
}

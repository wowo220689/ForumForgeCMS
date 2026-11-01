<?php
declare(strict_types=1);

function forum_notifications_schema(PDO $pdo): void
{
    $pdo->exec('CREATE TABLE IF NOT EXISTS notifications (
        id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL,
        kind TEXT NOT NULL, object_id INTEGER NOT NULL, actor_user_id INTEGER,
        topic_id INTEGER, post_id INTEGER, message_id INTEGER, report_id INTEGER,
        created_at TEXT NOT NULL, read_at TEXT,
        UNIQUE(user_id, kind, object_id),
        FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY(actor_user_id) REFERENCES users(id) ON DELETE SET NULL,
        FOREIGN KEY(topic_id) REFERENCES topics(id) ON DELETE CASCADE,
        FOREIGN KEY(post_id) REFERENCES posts(id) ON DELETE CASCADE,
        FOREIGN KEY(message_id) REFERENCES private_messages(id) ON DELETE CASCADE,
        FOREIGN KEY(report_id) REFERENCES post_reports(id) ON DELETE CASCADE
    )');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_notifications_recipient ON notifications(user_id, read_at, id DESC)');
}

function forum_notification_insert(PDO $pdo, int $recipient, string $kind, int $objectId, int $actor, array $references): void
{
    if ($recipient === $actor) { return; }
    $pdo->prepare('INSERT OR IGNORE INTO notifications (user_id, kind, object_id, actor_user_id, topic_id, post_id, message_id, report_id, created_at)
        VALUES (:user, :kind, :object, :actor, :topic, :post, :message, :report, :created)')->execute([
        ':user' => $recipient, ':kind' => $kind, ':object' => $objectId, ':actor' => $actor,
        ':topic' => $references['topic_id'] ?? null, ':post' => $references['post_id'] ?? null,
        ':message' => $references['message_id'] ?? null, ':report' => $references['report_id'] ?? null, ':created' => forum_now(),
    ]);
}

function forum_notify_reply(PDO $pdo, int $topicId, int $postId, int $actor): void
{
    $stmt = $pdo->prepare('SELECT c.* FROM topics t JOIN categories c ON c.id=t.category_id WHERE t.id=:id');
    $stmt->execute([':id' => $topicId]);
    $category = $stmt->fetch();
    $users = $pdo->prepare('SELECT DISTINCT u.id, u.role FROM users u WHERE u.role<>"deleted" AND u.id IN (
        SELECT user_id FROM topics WHERE id=:topic UNION SELECT user_id FROM posts WHERE topic_id=:topic)');
    $users->execute([':topic' => $topicId]);
    foreach ($users->fetchAll() as $user) {
        if ($category && forum_category_visible($category, $user)) {
            forum_notification_insert($pdo, (int) $user['id'], 'reply', $postId, $actor, ['topic_id' => $topicId, 'post_id' => $postId]);
        }
    }
}

function forum_notify_report(PDO $pdo, int $reportId, int $postId, int $actor): void
{
    $stmt = $pdo->prepare('SELECT c.*, t.id AS topic_id FROM posts p JOIN topics t ON t.id=p.topic_id JOIN categories c ON c.id=t.category_id WHERE p.id=:id');
    $stmt->execute([':id' => $postId]);
    $category = $stmt->fetch();
    foreach ($pdo->query('SELECT id, role FROM users WHERE role IN ("admin", "moderator")')->fetchAll() as $user) {
        if ($category && forum_category_visible($category, $user)) {
            forum_notification_insert($pdo, (int) $user['id'], 'report', $reportId, $actor, ['topic_id' => (int) $category['topic_id'], 'post_id' => $postId, 'report_id' => $reportId]);
        }
    }
}

function forum_notification_query(array $user): string
{
    return ' FROM notifications n
        LEFT JOIN users a ON a.id=n.actor_user_id
        LEFT JOIN topics t ON t.id=n.topic_id
        LEFT JOIN categories c ON c.id=t.category_id
        LEFT JOIN private_messages pm ON pm.id=n.message_id
        LEFT JOIN post_reports pr ON pr.id=n.report_id
        WHERE n.user_id=:user AND (
            (n.kind="reply" AND t.id IS NOT NULL AND ' . forum_visibility_sql('c', $user) . ')
            OR (n.kind="message" AND pm.recipient_id=:user AND pm.deleted_by_recipient=0)
            OR (n.kind="report" AND ' . (forum_is_staff($user) ? '1' : '0') . ' AND pr.status="open" AND ' . forum_visibility_sql('c', $user) . '))';
}

function forum_notifications_count(array $user, bool $unread = false): int
{
    $stmt = forum_db()->prepare('SELECT COUNT(*)' . forum_notification_query($user) . ($unread ? ' AND n.read_at IS NULL' : ''));
    $stmt->execute([':user' => (int) $user['id']]);
    return (int) $stmt->fetchColumn();
}

function forum_notifications_fetch(array $user, int $page = 1): array
{
    $pagination = forum_pagination(forum_notifications_count($user), $page, 20);
    $stmt = forum_db()->prepare('SELECT n.*, a.username AS actor_username, t.title AS topic_title, pm.subject AS message_subject'
        . forum_notification_query($user) . ' ORDER BY n.id DESC LIMIT 20 OFFSET :offset');
    $stmt->bindValue(':user', (int) $user['id'], PDO::PARAM_INT);
    $stmt->bindValue(':offset', $pagination['offset'], PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function forum_post_url(int $topicId, int $postId): string
{
    $stmt = forum_db()->prepare('SELECT COUNT(*) FROM posts p WHERE p.topic_id=:topic AND (p.created_at, p.id) <= (SELECT created_at, id FROM posts WHERE id=:post AND topic_id=:topic)');
    $stmt->execute([':topic' => $topicId, ':post' => $postId]);
    $page = max(1, (int) ceil((int) $stmt->fetchColumn() / FORUM_POSTS_PER_PAGE));
    return forum_url(['view' => 'topic', 'id' => $topicId, 'page' => $page]) . '#post-' . $postId;
}

function forum_notification_open(int $id, array $user): string
{
    $stmt = forum_db()->prepare('SELECT n.*' . forum_notification_query($user) . ' AND n.id=:id');
    $stmt->execute([':user' => (int) $user['id'], ':id' => $id]);
    $notification = $stmt->fetch();
    if (!$notification) { throw new RuntimeException(forum_t('notifications.unavailable')); }
    forum_db()->prepare('UPDATE notifications SET read_at=COALESCE(read_at, :now) WHERE id=:id AND user_id=:user')
        ->execute([':now' => forum_now(), ':id' => $id, ':user' => (int) $user['id']]);
    return $notification['kind'] === 'message'
        ? forum_url(['view' => 'message', 'id' => (int) $notification['message_id']])
        : forum_post_url((int) $notification['topic_id'], (int) $notification['post_id']);
}

function forum_notifications_mark_all(array $user): void
{
    forum_db()->prepare('UPDATE notifications SET read_at=:now WHERE user_id=:user AND read_at IS NULL')
        ->execute([':now' => forum_now(), ':user' => (int) $user['id']]);
}

function forum_render_notifications(array $user, ?array $flash): void
{
    $pagination = forum_pagination(forum_notifications_count($user), forum_current_page(), 20);
    $rows = forum_notifications_fetch($user, (int) $pagination['page']);
    forum_ext_render_header(forum_t('notifications.title'), '');
    forum_ext_render_flash($flash);
    ?>
    <section class="panel forum-panel forum-notifications">
      <div class="forum-section-head">
        <h2><?php echo forum_escape(forum_t('notifications.title')); ?></h2>
        <form method="post" class="forum-inline-form" action="<?php echo forum_url(['view' => 'notifications']); ?>">
          <input type="hidden" name="action" value="read_notifications">
          <input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
          <button class="button-secondary" type="submit"><?php echo forum_escape(forum_t('notifications.read_all')); ?></button>
        </form>
      </div>
      <?php if (!$rows): ?><p><?php echo forum_escape(forum_t('notifications.empty')); ?></p><?php endif; ?>
      <div class="forum-list forum-topic-list">
        <?php foreach ($rows as $row): ?>
          <article class="forum-row<?php echo $row['read_at'] === null ? ' forum-notification-unread' : ''; ?>">
            <div class="forum-row-main">
              <strong><?php echo forum_escape(forum_t('notifications.' . $row['kind'], ['actor' => $row['actor_username'] ?? forum_t('użytkownik')])); ?></strong>
              <p><?php echo forum_escape($row['kind'] === 'message' ? $row['message_subject'] : $row['topic_title']); ?></p>
              <small><?php echo forum_escape(forum_format_date($row['created_at'])); ?></small>
            </div>
            <form class="forum-inline-form" method="post" action="<?php echo forum_url(['view' => 'notifications']); ?>">
              <input type="hidden" name="action" value="open_notification"><input type="hidden" name="notification_id" value="<?php echo (int) $row['id']; ?>">
              <input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
              <button class="button-secondary" type="submit"><?php echo forum_escape(forum_t('notifications.open')); ?></button>
            </form>
          </article>
        <?php endforeach; ?>
      </div>
      <?php forum_ext_render_pagination($pagination, ['view' => 'notifications']); ?>
    </section>
    <?php
    forum_ext_render_footer();
}

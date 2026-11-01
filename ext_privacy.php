<?php
declare(strict_types=1);

function forum_delete_own_account(int $userId, string $password, bool $confirmed): void
{
    $actor = forum_require_login();
    if ((int) $actor['id'] !== $userId) { throw new RuntimeException(forum_t('privacy.denied')); }
    $pdo = forum_db();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id=:id');
    $stmt->execute([':id' => $userId]);
    $user = $stmt->fetch();
    if (!$user || $user['role'] === 'admin' || $user['role'] === 'deleted') { throw new RuntimeException(forum_t('privacy.admin')); }
    if (!$confirmed) { throw new RuntimeException(forum_t('privacy.confirm_required')); }
    if (!password_verify($password, $user['password_hash'])) { throw new RuntimeException(forum_t('Aktualne hasło jest niepoprawne.')); }
    $pdo->beginTransaction();
    try {
        $anonymous = $pdo->query('SELECT id FROM users WHERE role="deleted" ORDER BY id LIMIT 1')->fetchColumn();
        if ($anonymous === false) {
            $insert = $pdo->prepare('INSERT INTO users(username,email,password_hash,role,created_at) VALUES (:name,:email,:hash,"deleted",:created)');
            $random = bin2hex(random_bytes(12));
            $insert->execute([':name' => '[deleted]', ':email' => $random . '@deleted.invalid', ':hash' => '!', ':created' => forum_now()]);
            $anonymous = (int) $pdo->lastInsertId();
        }
        // Preserve whole discussions, including replies by other authors, before the FK cascade removes the real identity.
        foreach (['topics' => ['user_id', 'last_post_user_id'], 'posts' => ['user_id']] as $table => $columns) {
            foreach ($columns as $column) {
                $pdo->prepare('UPDATE ' . $table . ' SET ' . $column . '=:anonymous WHERE ' . $column . '=:id')->execute([':anonymous' => $anonymous, ':id' => $userId]);
            }
        }
        $pdo->prepare('UPDATE posts SET edited_by_user_id=NULL WHERE edited_by_user_id=:id')->execute([':id' => $userId]);
        $pdo->prepare('DELETE FROM users WHERE id=:id')->execute([':id' => $userId]);
        forum_security_clear_account($userId);
        $pdo->commit();
    } catch (Throwable $e) { $pdo->rollBack(); throw $e; }
    if (!empty($user['avatar_filename']) && basename($user['avatar_filename']) === $user['avatar_filename']) {
        $path = FORUM_AVATAR_DIR . '/' . $user['avatar_filename'];
        if (is_file($path)) { unlink($path); }
    }
    $_SESSION = [];
    session_regenerate_id(true);
}

function forum_privacy_render(array $profile): void
{
    ?>
    <section class="panel forum-panel forum-narrow-panel">
      <h2><?php echo forum_escape(forum_t('privacy.title')); ?></h2>
      <?php if ($profile['role'] === 'admin'): ?><p><?php echo forum_escape(forum_t('privacy.admin')); ?></p>
      <?php else: ?>
      <p><?php echo forum_escape(forum_t('privacy.policy')); ?></p>
      <form class="forum-form" method="post" action="<?php echo forum_url(['view' => 'account']); ?>">
        <input type="hidden" name="action" value="delete_own_account"><input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
        <label for="delete-password"><?php echo forum_escape(forum_t('Aktualne hasło')); ?></label><input id="delete-password" name="current_password" type="password" autocomplete="current-password" required>
        <label class="checkbox-row"><input type="checkbox" name="confirm_deletion" value="1" required><span><?php echo forum_escape(forum_t('privacy.confirm')); ?></span></label>
        <button class="button-secondary forum-danger" type="submit"><?php echo forum_escape(forum_t('privacy.submit')); ?></button>
      </form>
      <?php endif; ?>
    </section>
    <?php
}

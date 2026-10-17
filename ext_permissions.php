<?php
declare(strict_types=1);

function forum_permission_user(int $userId): ?array
{
    $stmt = forum_db()->prepare('SELECT id, username, role FROM users WHERE id = :id');
    $stmt->execute([':id' => $userId]);
    return $stmt->fetch() ?: null;
}

function forum_category_visible(array $category, ?array $user): bool
{
    if (forum_is_admin($user)) { return true; }
    return match ($category['visibility'] ?? 'public') {
        'public' => true,
        'members' => $user !== null,
        'staff' => forum_is_staff($user),
        default => false,
    };
}

function forum_category_writable(array $category, ?array $user): bool
{
    return $user !== null && forum_category_visible($category, $user)
        && (empty($category['read_only']) || forum_is_staff($user));
}

function forum_visibility_sql(string $alias = 'c', ?array $user = null): string
{
    // Aliases are internal constants; role values never become SQL input.
    if (!preg_match('/^[a-z][a-z0-9_]*$/D', $alias)) { throw new LogicException('Invalid SQL alias'); }
    $user = $user ?? forum_current_user();
    if (forum_is_admin($user)) { return '1=1'; }
    $allowed = forum_is_staff($user) ? '"public", "members", "staff"' : ($user ? '"public", "members"' : '"public"');
    return $alias . '.visibility IN (' . $allowed . ')';
}

function forum_visible_topic_sql(string $alias = 't'): string
{
    if (!preg_match('/^[a-z][a-z0-9_]*$/D', $alias)) { throw new LogicException('Invalid SQL alias'); }
    return $alias . '.category_id IN (SELECT vc.id FROM categories vc WHERE ' . forum_visibility_sql('vc') . ')';
}

function forum_assert_category_write(PDO $pdo, int $categoryId, int $userId): array
{
    $stmt = $pdo->prepare('SELECT * FROM categories WHERE id = :id');
    $stmt->execute([':id' => $categoryId]);
    $category = $stmt->fetch();
    if (!$category || !forum_category_writable($category, forum_permission_user($userId))) {
        throw new RuntimeException(forum_t('permissions.denied'));
    }
    return $category;
}

function forum_permission_values(string $visibility, bool $readOnly): array
{
    if (!in_array($visibility, ['public', 'members', 'staff', 'hidden'], true)) {
        throw new RuntimeException(forum_t('permissions.invalid'));
    }
    return ['visibility' => $visibility, 'read_only' => $readOnly ? 1 : 0];
}

function forum_render_permission_fields(array $category = []): void
{
    $id = 'permission-' . (int) ($category['id'] ?? 0);
    ?>
    <label for="<?php echo $id; ?>"><?php echo forum_escape(forum_t('permissions.visibility')); ?></label>
    <select id="<?php echo $id; ?>" name="visibility">
      <?php foreach (['public', 'members', 'staff', 'hidden'] as $value): ?>
        <option value="<?php echo $value; ?>" <?php echo ($category['visibility'] ?? 'public') === $value ? 'selected' : ''; ?>><?php echo forum_escape(forum_t('permissions.' . $value)); ?></option>
      <?php endforeach; ?>
    </select>
    <label class="checkbox-row"><input type="checkbox" name="read_only" value="1" <?php echo !empty($category['read_only']) ? 'checked' : ''; ?>><span><?php echo forum_escape(forum_t('permissions.read_only')); ?></span></label>
    <?php
}

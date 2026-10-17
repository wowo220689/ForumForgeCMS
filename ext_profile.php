<?php
declare(strict_types=1);

function forum_profile_values(array $input): array
{
    $values = [];
    foreach (['about_me' => 2000, 'location' => 100, 'website' => 255, 'signature' => 300] as $field => $limit) {
        $value = trim((string) ($input[$field] ?? ''));
        if (!preg_match('//u', $value) || preg_match_all('/./us', $value) > $limit) {
            throw new RuntimeException(forum_t('profile.too_long', ['limit' => $limit]));
        }
        $values[$field] = $value;
    }
    if ($values['website'] !== '') {
        $url = parse_url($values['website']);
        if (!filter_var($values['website'], FILTER_VALIDATE_URL) || !is_array($url)
            || !in_array(strtolower($url['scheme'] ?? ''), ['http', 'https'], true) || isset($url['user']) || isset($url['pass'])) {
            throw new RuntimeException(forum_t('profile.invalid_website'));
        }
    }
    return $values;
}

function forum_profile_save(int $userId, array $input): void
{
    $values = forum_profile_values($input);
    forum_db()->prepare('UPDATE users SET about_me=:about_me, location=:location, website=:website, signature=:signature WHERE id=:id')
        ->execute([':id' => $userId, ':about_me' => $values['about_me'], ':location' => $values['location'], ':website' => $values['website'], ':signature' => $values['signature']]);
}

function forum_profile_render_form(array $profile, bool $admin = false): void
{
    ?>
    <article class="forum-surface-card">
      <h3><?php echo forum_escape(forum_t('profile.public_profile')); ?></h3>
      <form class="forum-form" method="post" action="<?php echo forum_url($admin ? ['view' => 'admin', 'section' => 'user', 'user_id' => (int) $profile['id']] : ['view' => 'account']); ?>">
        <input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
        <input type="hidden" name="action" value="<?php echo $admin ? 'admin_update_profile' : 'update_profile'; ?>">
        <?php if ($admin): ?><input type="hidden" name="user_id" value="<?php echo (int) $profile['id']; ?>"><?php endif; ?>
        <?php foreach (['about_me' => 2000, 'location' => 100, 'website' => 255, 'signature' => 300] as $field => $limit): ?>
          <label for="profile-<?php echo $field; ?>"><?php echo forum_escape(forum_t('profile.' . $field)); ?></label>
          <?php if (in_array($field, ['about_me', 'signature'], true)): ?>
            <textarea id="profile-<?php echo $field; ?>" name="<?php echo $field; ?>" maxlength="<?php echo $limit; ?>" rows="<?php echo $field === 'about_me' ? 4 : 2; ?>"><?php echo forum_escape($profile[$field] ?? ''); ?></textarea>
          <?php else: ?>
            <input id="profile-<?php echo $field; ?>" type="<?php echo $field === 'website' ? 'url' : 'text'; ?>" name="<?php echo $field; ?>" maxlength="<?php echo $limit; ?>" value="<?php echo forum_escape($profile[$field] ?? ''); ?>">
          <?php endif; ?>
        <?php endforeach; ?>
        <button class="button" type="submit"><?php echo forum_escape(forum_t('profile.save')); ?></button>
      </form>
    </article>
    <?php
}

function forum_profile_render_details(array $profile): void
{
    foreach (['about_me', 'location', 'website', 'signature'] as $field) {
        $value = (string) ($profile[$field] ?? '');
        if ($value === '') { continue; }
        echo '<div class="forum-profile-detail"><strong>' . forum_escape(forum_t('profile.' . $field)) . '</strong><p>';
        if ($field === 'website') {
            try { forum_profile_values(['website' => $value]); }
            catch (RuntimeException $e) { echo forum_escape($value) . '</p></div>'; continue; }
            echo '<a href="' . forum_escape($value) . '" target="_blank" rel="noopener noreferrer nofollow ugc">' . forum_escape($value) . '</a>';
        } else { echo nl2br(forum_escape($value)); }
        echo '</p></div>';
    }
}

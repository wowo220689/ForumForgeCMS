<?php
declare(strict_types=1);

function forum_ext_render_editor_toolbar(string $targetId): void
{
    $emoticons = [
        ['&#128578;', 'Uśmiech'],
        ['&#128512;', 'Szeroki uśmiech'],
        ['&#128513;', 'Zadowolenie'],
        ['&#128514;', 'Śmiech'],
        ['&#128521;', 'Mrugnięcie'],
        ['&#128526;', 'Luzak'],
        ['&#128525;', 'Serduszka'],
        ['&#128536;', 'Buziak'],
        ['&#128539;', 'Język'],
        ['&#128540;', 'Psikus'],
        ['&#128558;', 'Zdziwienie'],
        ['&#128559;', 'Ojej'],
        ['&#128528;', 'Neutralnie'],
        ['&#128577;', 'Smutek'],
        ['&#128546;', 'Łza'],
        ['&#128557;', 'Płacz'],
        ['&#128545;', 'Złość'],
        ['&#128520;', 'Diabełek'],
        ['&#128519;', 'Aniołek'],
        ['&#129300;', 'Myślę'],
        ['&#129320;', 'Podejrzliwie'],
        ['&#129327;', 'Szok'],
        ['&#128564;', 'Sen'],
        ['&#129296;', 'Milczę'],
        ['&#128556;', 'Nerwowo'],
        ['&#128580;', 'Przewracam oczami'],
        ['&#128077;', 'OK'],
        ['&#128078;', 'Nie'],
        ['&#128079;', 'Brawo'],
        ['&#128591;', 'Proszę'],
        ['&#128170;', 'Siła'],
        ['&#128076;', 'Dobrze'],
        ['&#10084;&#65039;', 'Serce'],
        ['&#128148;', 'Złamane serce'],
        ['&#11088;', 'Gwiazdka'],
        ['&#127881;', 'Impreza'],
        ['&#128293;', 'Ogień'],
        ['&#128161;', 'Pomysł'],
        ['&#10071;', 'Uwaga'],
        ['&#10067;', 'Pytanie'],
    ];
    ?>
    <div class="forum-editor-toolbar" data-editor-target="<?php echo forum_escape($targetId); ?>" role="toolbar" aria-label="Formatowanie posta">
      <button type="button" data-wrap="[b]" data-close="[/b]" title="Pogrubienie"><strong>B</strong></button>
      <button type="button" data-wrap="[i]" data-close="[/i]" title="Kursywa"><em>I</em></button>
      <button type="button" data-wrap="[u]" data-close="[/u]" title="Podkreślenie"><span class="forum-underline">U</span></button>
      <button type="button" data-prompt="url" title="Link">🔗</button>
      <button type="button" data-wrap="[quote]" data-close="[/quote]" title="Cytat">❞</button>
      <button type="button" data-wrap="[code]" data-close="[/code]" title="Kod">&lt;&gt;</button>
      <button type="button" data-action="emoticons" class="forum-emoticon-toggle" title="Emotki">☺</button>
      <button type="button" data-line="[list]\n[*] " data-close="\n[/list]" title="Lista punktowana">•</button>
      <button type="button" data-line="[olist]\n[*] " data-close="\n[/olist]" title="Lista numerowana">1.</button>
      <button type="button" data-wrap="[left]" data-close="[/left]" title="Do lewej">☰</button>
      <button type="button" data-wrap="[center]" data-close="[/center]" title="Wyśrodkuj">≡</button>
      <button type="button" data-wrap="[right]" data-close="[/right]" title="Do prawej">☷</button>
      <button type="button" data-wrap="[s]" data-close="[/s]" title="Przekreślenie">S</button>
      <button type="button" data-wrap="[sup]" data-close="[/sup]" title="Indeks górny">x²</button>
      <button type="button" data-wrap="[sub]" data-close="[/sub]" title="Indeks dolny">x₂</button>
      <select data-style="color" title="Kolor tekstu">
        <option value="">Kolor</option>
        <option value="#0f4c5c">Morski</option>
        <option value="#cc5a4d">Czerwony</option>
        <option value="#1f8a59">Zielony</option>
        <option value="#7c3aed">Fioletowy</option>
      </select>
      <select data-style="size" title="Rozmiar tekstu">
        <option value="">Rozmiar</option>
        <option value="small">Mały</option>
        <option value="normal">Normalny</option>
        <option value="large">Duży</option>
      </select>
      <button type="button" data-prompt="image" title="Obraz z URL">🖼</button>
      <button type="button" data-action="preview" title="Podgląd">👁</button>
      <button type="button" data-action="clear" title="Wyczyść">□</button>
      <div class="forum-emoticon-picker" data-emoticon-picker hidden aria-label="Lista emotek">
        <?php foreach ($emoticons as [$entity, $label]): ?>
          <?php $emoticon = html_entity_decode($entity, ENT_QUOTES | ENT_HTML5, 'UTF-8'); ?>
          <button type="button" data-emoticon="<?php echo forum_escape($emoticon); ?>" title="<?php echo forum_escape($label); ?>"><?php echo $emoticon; ?></button>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="forum-editor-preview" data-editor-preview="<?php echo forum_escape($targetId); ?>" hidden></div>
    <?php
}

function forum_ext_render_home(?array $flash): void
{
    $forumSections = forum_fetch_sections_with_categories();
    $recentTopics = forum_recent_topics(5);
    $topUsers = forum_ext_top_users();
    $summary = forum_ext_admin_summary();

    forum_ext_render_header(forum_ext_setting('home_intro_title'), forum_ext_setting('home_intro_text'));
    forum_ext_render_flash($flash);
    ?>
    <section class="panel forum-panel">
      <div class="forum-stats-grid forum-stats-grid-wide">
        <article class="forum-stat-box"><strong><?php echo $summary['users']; ?></strong><span>użytkowników</span></article>
        <article class="forum-stat-box"><strong><?php echo $summary['topics']; ?></strong><span>tematów</span></article>
        <article class="forum-stat-box"><strong><?php echo $summary['posts']; ?></strong><span>postów</span></article>
        <article class="forum-stat-box"><strong><?php echo $summary['likes']; ?></strong><span>lajków</span></article>
        <article class="forum-stat-box"><strong><?php echo $summary['messages']; ?></strong><span>wiadomości prywatnych</span></article>
      </div>
    </section>

    <section class="forum-section-stack">
      <?php foreach ($forumSections as $section): ?>
        <?php if (($section['categories'] ?? []) === []): ?>
          <?php continue; ?>
        <?php endif; ?>
        <div class="panel forum-panel forum-section-block">
          <div class="forum-section-title">
            <div>
              <h2><?php echo forum_escape($section['name']); ?></h2>
              <?php if ((string) ($section['description'] ?? '') !== ''): ?>
                <p><?php echo forum_escape($section['description']); ?></p>
              <?php endif; ?>
            </div>
          </div>
          <div class="forum-list forum-section-category-list">
            <?php foreach ($section['categories'] as $category): ?>
              <article class="forum-row">
                <div class="forum-row-main">
                  <h3><a href="<?php echo forum_url(['view' => 'category', 'id' => (int) $category['id']]); ?>"><?php echo forum_escape($category['name']); ?></a></h3>
                  <p><?php echo forum_escape($category['description']); ?></p>
                </div>
                <div class="forum-row-meta">
                  <strong><?php echo (int) $category['topic_count']; ?></strong>
                  <span>tematów</span>
                  <small><?php echo (int) $category['post_count']; ?> postów</small>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </section>

    <section class="panel forum-panel">
      <div class="forum-grid-two">
        <div>
          <span class="eyebrow">Najnowsze</span>
          <h2>Ostatnio aktywne tematy</h2>
          <div class="forum-list forum-home-recent-list">
            <?php foreach ($recentTopics as $topic): ?>
              <article class="forum-row forum-row-tight">
                <div class="forum-row-main">
                  <h3><a href="<?php echo forum_url(['view' => 'topic', 'id' => (int) $topic['id']]); ?>"><?php echo forum_escape($topic['title']); ?></a></h3>
                  <p><?php echo forum_escape($topic['category_name']); ?> · autor: <strong><?php echo forum_escape($topic['author_username']); ?></strong></p>
                </div>
                <div class="forum-row-meta">
                  <strong><?php echo (int) $topic['post_count']; ?></strong>
                  <span>postów</span>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        </div>
        <div>
          <span class="eyebrow">Aktywni użytkownicy</span>
          <h2>Kto najczęściej pomaga</h2>
          <div class="forum-list forum-list-compact">
            <?php foreach ($topUsers as $user): ?>
              <article class="forum-user-row">
                <?php forum_ext_render_avatar($user); ?>
                <div>
                  <h3><a href="<?php echo forum_url(['view' => 'user', 'id' => (int) $user['id']]); ?>"><?php echo forum_escape($user['username']); ?></a></h3>
                  <p><?php echo (int) $user['post_count']; ?> postów · <?php echo (int) $user['likes_received']; ?> lajków</p>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </section>
    <?php
    forum_ext_render_footer();
}

function forum_ext_render_category(array $category, array $topics, array $pagination, ?array $currentUser, ?array $flash): void
{
    forum_ext_render_header($category['name'], $category['description']);
    forum_ext_render_flash($flash);
    ?>
    <section class="panel forum-panel">
      <div class="forum-section-head">
        <div><span class="eyebrow">Dział forum</span><h2><?php echo forum_escape($category['name']); ?></h2><p class="lead forum-lead"><?php echo forum_escape($category['description']); ?></p></div>
        <div class="forum-button-row"><a class="button-secondary" href="<?php echo forum_url(); ?>">Wróć do listy działów</a></div>
      </div>
      <?php if ($topics === []): ?>
        <div class="callout"><strong>Ten dział jest jeszcze pusty.</strong><p>Jeśli chcesz, możesz założyć pierwszy temat i rozpocząć dyskusję.</p></div>
      <?php else: ?>
        <div class="forum-list forum-topic-list">
          <?php foreach ($topics as $topic): ?>
            <article class="forum-row">
              <div class="forum-row-main">
                <h3><a href="<?php echo forum_url(['view' => 'topic', 'id' => (int) $topic['id']]); ?>"><?php echo forum_escape($topic['title']); ?></a></h3>
                <p><?php if ((int) $topic['is_pinned'] === 1): ?><span class="forum-badge">Przypięty</span><?php endif; ?><?php if ((int) $topic['is_locked'] === 1): ?><span class="forum-badge forum-badge-muted">Zamknięty</span><?php endif; ?> Autor: <strong><?php echo forum_escape($topic['author_username']); ?></strong></p>
              </div>
              <div class="forum-row-meta"><strong><?php echo (int) $topic['post_count']; ?></strong><span>postów</span><small><?php echo forum_escape(forum_format_date($topic['last_post_at'])); ?></small></div>
            </article>
          <?php endforeach; ?>
        </div>
        <?php forum_ext_render_pagination($pagination, ['view' => 'category', 'id' => (int) $category['id']]); ?>
      <?php endif; ?>
    </section>

    <?php if ($currentUser): ?>
      <section class="panel forum-panel">
        <span class="eyebrow">Nowy temat</span>
        <h2>Rozpocznij nową dyskusję</h2>
        <form class="forum-form" method="post" action="<?php echo forum_url(['view' => 'category', 'id' => (int) $category['id']]); ?>">
          <input type="hidden" name="action" value="create_topic">
          <input type="hidden" name="category_id" value="<?php echo (int) $category['id']; ?>">
          <input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
          <label for="topic-title">Tytuł tematu</label>
          <input id="topic-title" type="text" name="title" maxlength="140" required>
          <label for="topic-body">Pierwsza wiadomość</label>
          <?php forum_ext_render_editor_toolbar('topic-body'); ?>
          <textarea id="topic-body" name="body" minlength="10" maxlength="12000" required></textarea>
          <button class="button" type="submit">Dodaj temat</button>
        </form>
      </section>
    <?php endif; ?>
    <?php
    forum_ext_render_footer();
}

function forum_ext_render_topic(array $topic, array $posts, array $pagination, ?array $currentUser, ?array $flash, ?array $editPost = null): void
{
    forum_ext_render_header($topic['title'], 'Dyskusja na forum ForumForgeCMS');
    forum_ext_render_flash($flash);
    ?>
    <section class="panel forum-panel">
      <div class="forum-section-head">
        <div>
          <span class="eyebrow">Temat</span>
          <h2><?php echo forum_escape($topic['title']); ?></h2>
          <p class="forum-meta-line">Dział: <a href="<?php echo forum_url(['view' => 'category', 'id' => (int) $topic['category_id']]); ?>"><?php echo forum_escape($topic['category_name']); ?></a> · Autor: <strong><?php echo forum_escape($topic['author_username']); ?></strong> · Założono: <?php echo forum_escape(forum_format_date($topic['created_at'])); ?></p>
          <div class="forum-badge-row"><?php if ((int) $topic['is_pinned'] === 1): ?><span class="forum-badge">Przypięty</span><?php endif; ?><?php if ((int) $topic['is_locked'] === 1): ?><span class="forum-badge forum-badge-muted">Zamknięty</span><?php endif; ?></div>
        </div>
        <div class="forum-admin-tools">
          <a class="button-secondary" href="<?php echo forum_url(['view' => 'category', 'id' => (int) $topic['category_id']]); ?>">Wróć do działu</a>
          <?php if ($currentUser && forum_is_staff($currentUser)): ?>
            <form method="post" action="<?php echo forum_url(['view' => 'topic', 'id' => (int) $topic['id']]); ?>" class="forum-inline-form"><input type="hidden" name="action" value="toggle_topic_pin"><input type="hidden" name="topic_id" value="<?php echo (int) $topic['id']; ?>"><input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>"><button class="button-secondary" type="submit"><?php echo (int) $topic['is_pinned'] === 1 ? 'Odepnij' : 'Przypnij'; ?></button></form>
            <form method="post" action="<?php echo forum_url(['view' => 'topic', 'id' => (int) $topic['id']]); ?>" class="forum-inline-form"><input type="hidden" name="action" value="toggle_topic_lock"><input type="hidden" name="topic_id" value="<?php echo (int) $topic['id']; ?>"><input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>"><button class="button-secondary" type="submit"><?php echo (int) $topic['is_locked'] === 1 ? 'Otwórz temat' : 'Zamknij temat'; ?></button></form>
            <form method="post" action="<?php echo forum_url(['view' => 'topic', 'id' => (int) $topic['id']]); ?>" class="forum-inline-form" onsubmit="return confirm('Usunąć cały temat?');"><input type="hidden" name="action" value="delete_topic"><input type="hidden" name="topic_id" value="<?php echo (int) $topic['id']; ?>"><input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>"><button class="button-secondary forum-danger-button" type="submit">Usuń temat</button></form>
          <?php endif; ?>
        </div>
      </div>
      <div class="forum-post-list">
        <?php foreach ($posts as $index => $post): ?>
          <article id="post-<?php echo (int) $post['id']; ?>" class="forum-post-card">
            <div class="forum-post-aside">
              <?php forum_ext_render_avatar(['id' => (int) $post['profile_user_id'], 'username' => $post['username'], 'avatar_updated_at' => $post['avatar_updated_at']], 'forum-avatar forum-post-avatar'); ?>
              <strong><a href="<?php echo forum_url(['view' => 'user', 'id' => (int) $post['user_id']]); ?>"><?php echo forum_escape($post['username']); ?></a></strong>
              <span><?php echo forum_escape(forum_role_label((string) ($post['role'] ?? 'member'))); ?></span>
              <small>#<?php echo (int) ($pagination['offset'] ?? 0) + $index + 1; ?></small>
              <small><?php echo (int) $post['user_post_count']; ?> postów</small>
              <small><?php echo (int) $post['user_likes_received']; ?> lajków</small>
            </div>
            <div class="forum-post-body">
              <div class="forum-post-meta">
                <span><?php echo forum_escape(forum_format_date($post['created_at'])); ?></span>
                <?php if (!empty($post['updated_at'])): ?><small>edytowano: <?php echo forum_escape(forum_format_date($post['updated_at'])); ?></small><?php endif; ?>
              </div>
              <?php if ($editPost && (int) $editPost['id'] === (int) $post['id'] && $currentUser): ?>
                <form id="edit-post-form" class="forum-form" method="post" action="<?php echo forum_url(['view' => 'topic', 'id' => (int) $topic['id'], 'page' => (int) ($pagination['page'] ?? 1)]); ?>">
                  <input type="hidden" name="action" value="update_post">
                  <input type="hidden" name="post_id" value="<?php echo (int) $post['id']; ?>">
                  <input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
                  <?php if ((int) $post['is_topic_starter'] === 1): ?><label for="edit-topic-title">Tytuł tematu</label><input id="edit-topic-title" type="text" name="topic_title" value="<?php echo forum_escape($topic['title']); ?>" maxlength="140" required><?php endif; ?>
                  <label for="edit-post-body">Treść postu</label>
                  <?php forum_ext_render_editor_toolbar('edit-post-body'); ?>
                  <textarea id="edit-post-body" name="body" minlength="3" maxlength="12000" required><?php echo forum_escape($post['body']); ?></textarea>
                  <div class="forum-button-row"><button class="button" type="submit">Zapisz zmiany</button><a class="button-secondary" href="<?php echo forum_url(['view' => 'topic', 'id' => (int) $topic['id']]); ?>">Anuluj</a></div>
                </form>
              <?php else: ?>
                <div class="forum-post-content"><?php echo forum_render_text($post['body']); ?></div>
                <?php if (!empty($post['updated_at'])): ?>
                  <p class="forum-edit-note">Ostatnio edytowano przez: <strong><?php echo forum_escape((string) ($post['edited_by_username'] ?? $post['username'] ?? 'użytkownik')); ?></strong>, <?php echo forum_escape(forum_format_date($post['updated_at'])); ?></p>
                <?php endif; ?>
              <?php endif; ?>
              <div class="forum-post-actions">
                <span class="forum-like-count"><?php echo (int) $post['like_count']; ?> lajków</span>
                <?php if ($currentUser): ?><form class="forum-inline-form" method="post" action="<?php echo forum_url(['view' => 'topic', 'id' => (int) $topic['id'], 'page' => (int) ($pagination['page'] ?? 1)]); ?>"><input type="hidden" name="action" value="like_post"><input type="hidden" name="post_id" value="<?php echo (int) $post['id']; ?>"><input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>"><button class="button-secondary forum-like-button<?php echo (int) $post['viewer_liked'] === 1 ? ' is-active' : ''; ?>" type="submit" title="<?php echo (int) $post['viewer_liked'] === 1 ? 'Cofnij lajka' : 'Polub ten post'; ?>"><?php forum_ext_render_like_icon((int) $post['viewer_liked'] === 1); ?><span class="visually-hidden"><?php echo (int) $post['viewer_liked'] === 1 ? 'Cofnij lajka' : 'Lubię to'; ?></span></button></form><?php endif; ?>
                <?php if ($currentUser): ?><a class="button-secondary" href="<?php echo forum_url(['view' => 'topic', 'id' => (int) $topic['id'], 'page' => (int) ($pagination['page'] ?? 1), 'quote_post' => (int) $post['id']]); ?>#reply-body">Cytuj</a><?php endif; ?>
                <?php if ($currentUser && (int) $currentUser['id'] !== (int) $post['user_id']): ?><form class="forum-inline-form" method="post" action="<?php echo forum_url(['view' => 'topic', 'id' => (int) $topic['id'], 'page' => (int) ($pagination['page'] ?? 1)]); ?>"><input type="hidden" name="action" value="report_post"><input type="hidden" name="post_id" value="<?php echo (int) $post['id']; ?>"><input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>"><button class="button-secondary" type="submit">Zgłoś</button></form><?php endif; ?>
                <?php if ($currentUser && forum_ext_user_can_edit_post($currentUser, $post)): ?><a class="button-secondary" href="<?php echo forum_url(['view' => 'topic', 'id' => (int) $topic['id'], 'page' => (int) ($pagination['page'] ?? 1), 'edit_post' => (int) $post['id']]); ?>#edit-post-form">Edytuj</a><?php endif; ?>
                <?php if ($currentUser && (int) $currentUser['id'] !== (int) $post['user_id']): ?><a class="button-secondary" href="<?php echo forum_url(['view' => 'compose', 'to' => $post['username']]); ?>">Napisz wiadomość</a><?php endif; ?>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
      <?php forum_ext_render_pagination($pagination, ['view' => 'topic', 'id' => (int) $topic['id']]); ?>
    </section>
    <?php
}

function forum_ext_render_account(array $profile, array $recentTopics, array $recentPosts, ?array $flash): void
{
    forum_ext_render_header('Moje konto', 'Ustawienia profilu, avatar, hasło i statystyki aktywności.');
    forum_ext_render_flash($flash);
    ?>
    <section class="panel forum-panel">
      <div class="forum-profile-grid">
        <article class="forum-profile-card">
          <?php forum_ext_render_avatar($profile, 'forum-avatar forum-avatar-large'); ?>
          <h2><?php echo forum_escape($profile['username']); ?></h2>
          <p><?php echo forum_escape($profile['email']); ?></p>
          <div class="forum-stats-grid forum-profile-stats">
            <article class="forum-stat-box"><strong><?php echo (int) $profile['topic_count']; ?></strong><span>tematów</span></article>
            <article class="forum-stat-box"><strong><?php echo (int) $profile['post_count']; ?></strong><span>postów</span></article>
            <article class="forum-stat-box"><strong><?php echo (int) $profile['likes_received']; ?></strong><span>otrzymanych lajków</span></article>
            <article class="forum-stat-box"><strong><?php echo (int) $profile['likes_given']; ?></strong><span>danych lajków</span></article>
          </div>
        </article>
        <div class="forum-stack">
          <article class="forum-surface-card">
            <div class="forum-post-body">
              <span class="eyebrow">Avatar</span>
              <form class="forum-form" method="post" action="<?php echo forum_url(['view' => 'account']); ?>" enctype="multipart/form-data">
                <input type="hidden" name="action" value="upload_avatar">
                <input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
                <label for="avatar-file">Nowy avatar</label>
                <input id="avatar-file" type="file" name="avatar" accept=".jpg,.jpeg,.png,.webp,.gif,image/jpeg,image/png,image/webp,image/gif" data-forum-image="avatar" data-forum-size="160" data-forum-fit="cover" required>
                <button class="button" type="submit">Wyślij avatar</button>
              </form>
              <?php if (!empty($profile['avatar_filename'])): ?>
                <form class="forum-inline-form" method="post" action="<?php echo forum_url(['view' => 'account']); ?>">
                  <input type="hidden" name="action" value="remove_avatar">
                  <input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
                  <button class="button-secondary" type="submit">Usuń avatar</button>
                </form>
              <?php endif; ?>
            </div>
          </article>
          <article class="forum-surface-card"><div class="forum-post-body"><span class="eyebrow">Hasło</span><form class="forum-form" method="post" action="<?php echo forum_url(['view' => 'account']); ?>"><input type="hidden" name="action" value="change_password"><input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>"><label for="current-password">Aktualne hasło</label><input id="current-password" type="password" name="current_password" required><label for="new-password">Nowe hasło</label><input id="new-password" type="password" name="new_password" minlength="10" required><button class="button" type="submit">Zmień hasło</button></form></div></article>
        </div>
      </div>
    </section>

    <section class="panel forum-panel">
      <div class="forum-grid-two">
        <div class="forum-surface-card"><span class="eyebrow">Moje tematy</span><h2>Ostatnio założone</h2><div class="forum-list forum-list-compact"><?php foreach ($recentTopics as $topic): ?><article class="forum-row forum-row-tight"><div class="forum-row-main"><h3><a href="<?php echo forum_url(['view' => 'topic', 'id' => (int) $topic['id']]); ?>"><?php echo forum_escape($topic['title']); ?></a></h3><p><?php echo forum_escape($topic['category_name']); ?></p></div></article><?php endforeach; ?></div></div>
        <div class="forum-surface-card"><span class="eyebrow">Moje posty</span><h2>Ostatnie odpowiedzi</h2><div class="forum-list forum-list-compact"><?php foreach ($recentPosts as $post): ?><article class="forum-row forum-row-tight"><div class="forum-row-main"><h3><a href="<?php echo forum_url(['view' => 'topic', 'id' => (int) $post['topic_id']]); ?>#post-<?php echo (int) $post['id']; ?>"><?php echo forum_escape($post['topic_title']); ?></a></h3><p><?php echo forum_escape(forum_ext_excerpt($post['body'], 110)); ?></p></div></article><?php endforeach; ?></div></div>
      </div>
    </section>
    <?php
    forum_ext_render_footer();
}

function forum_ext_render_auth(string $view, ?array $flash, ?array $resetRecord = null): void
{
    $titles = [
        'login' => ['Logowanie', 'Zaloguj sie do ForumForgeCMS.'],
        'register' => ['Rejestracja', 'Załóż konto na ForumForgeCMS.'],
        'forgot-password' => ['Reset hasła', 'Przywróć dostęp do forum przez link wysłany e-mailem.'],
        'reset-password' => ['Nowe hasło', 'Ustaw nowe hasło do ForumForgeCMS.'],
    ];

    [$title, $description] = $titles[$view] ?? ['Forum', 'ForumForgeCMS'];
    forum_ext_render_header($title, $description);
    forum_ext_render_flash($flash);
    ?>
    <section class="panel forum-panel forum-narrow-panel">
      <span class="eyebrow"><?php echo forum_escape($title); ?></span>
      <h2><?php echo forum_escape($title); ?></h2>
      <?php if ($view === 'login'): ?>
        <form class="forum-form" method="post" action="<?php echo forum_url(['view' => 'login']); ?>">
          <input type="hidden" name="action" value="login">
          <input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
          <label for="login">Login lub e-mail</label><input id="login" type="text" name="login" required>
          <label for="login-password">Hasło</label><input id="login-password" type="password" name="password" required>
          <button class="button" type="submit">Zaloguj się</button>
        </form>
        <p class="support-help">Nie pamiętasz hasła? <a href="<?php echo forum_url(['view' => 'forgot-password']); ?>">Wyślij link do resetu</a>.</p>
      <?php elseif ($view === 'register'): ?>
        <?php if (!forum_ext_registrations_enabled()): ?><div class="warning">Rejestracja nowych kont jest chwilowo wyłączona.</div><?php else: ?>
        <form class="forum-form" method="post" action="<?php echo forum_url(['view' => 'register']); ?>">
          <input type="hidden" name="action" value="register"><input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
          <label for="register-username">Nazwa użytkownika</label><input id="register-username" type="text" name="username" maxlength="40" required>
          <label for="register-email">E-mail</label><input id="register-email" type="email" name="email" maxlength="190" required>
          <label for="register-password">Hasło</label><input id="register-password" type="password" name="password" minlength="10" required>
          <label class="checkbox-row" for="register-accept-terms"><input id="register-accept-terms" type="checkbox" name="accept_terms" value="1" required><span>Akceptuję <a href="regulamin.html" target="_blank" rel="noopener noreferrer">Regulamin strony i forum</a>.</span></label>
          <button class="button" type="submit">Załóż konto</button>
        </form>
        <?php endif; ?>
      <?php elseif ($view === 'forgot-password'): ?>
        <form class="forum-form" method="post" action="<?php echo forum_url(['view' => 'forgot-password']); ?>">
          <input type="hidden" name="action" value="request_password_reset"><input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
          <label for="reset-login">Login lub e-mail</label><input id="reset-login" type="text" name="login" required>
          <button class="button" type="submit">Wyślij link resetujący</button>
        </form>
      <?php else: ?>
        <?php if (!$resetRecord): ?><div class="warning">Link do resetu hasła jest nieprawidłowy albo wygasł.</div><?php else: ?>
        <form class="forum-form" method="post" action="<?php echo forum_url(['view' => 'reset-password', 'selector' => $_GET['selector'] ?? '', 'token' => $_GET['token'] ?? '']); ?>">
          <input type="hidden" name="action" value="reset_password"><input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
          <input type="hidden" name="selector" value="<?php echo forum_escape((string) ($_GET['selector'] ?? '')); ?>">
          <input type="hidden" name="token" value="<?php echo forum_escape((string) ($_GET['token'] ?? '')); ?>">
          <label for="new-reset-password">Nowe hasło</label><input id="new-reset-password" type="password" name="new_password" minlength="10" required>
          <button class="button" type="submit">Ustaw nowe hasło</button>
        </form>
        <?php endif; ?>
      <?php endif; ?>
    </section>
    <?php
    forum_ext_render_footer();
}

function forum_ext_render_messages(array $inbox, array $outbox, string $box, array $pagination, ?array $flash): void
{
    forum_ext_render_header('Wiadomości prywatne', 'Rozmowy z innymi użytkownikami ForumForgeCMS.');
    forum_ext_render_flash($flash);
    $items = $box === 'outbox' ? $outbox : $inbox;
    ?>
    <section class="panel forum-panel">
      <div class="forum-section-head">
        <div><span class="eyebrow">Wiadomości</span><h2>Skrzynka prywatnych wiadomości</h2></div>
        <div class="forum-button-row"><a class="button-secondary" href="<?php echo forum_url(['view' => 'messages', 'box' => 'inbox']); ?>">Odebrane</a><a class="button-secondary" href="<?php echo forum_url(['view' => 'messages', 'box' => 'outbox']); ?>">Wysłane</a><a class="button" href="<?php echo forum_url(['view' => 'compose']); ?>">Nowa wiadomość</a></div>
      </div>
      <?php if ($items === []): ?>
        <div class="callout"><strong>Ta skrzynka jest jeszcze pusta.</strong><p>Kiedy zaczniesz prywatne rozmowy z innymi użytkownikami, wiadomości pojawią się właśnie tutaj.</p></div>
      <?php else: ?>
        <div class="forum-list">
          <?php foreach ($items as $message): ?>
            <article class="forum-user-row">
              <?php forum_ext_render_avatar(['id' => $box === 'outbox' ? $message['recipient_id'] : $message['sender_id'], 'username' => $box === 'outbox' ? $message['recipient_username'] : $message['sender_username'], 'avatar_updated_at' => $box === 'outbox' ? $message['recipient_avatar_updated_at'] : $message['sender_avatar_updated_at']]); ?>
              <div class="forum-user-row-main">
                <h3><a href="<?php echo forum_url(['view' => 'message', 'id' => (int) $message['id']]); ?>"><?php echo forum_escape($message['subject']); ?></a></h3>
                <p><?php echo forum_escape($box === 'outbox' ? 'Do: ' . $message['recipient_username'] : 'Od: ' . $message['sender_username']); ?> · <?php echo forum_escape(forum_format_date($message['created_at'])); ?></p>
                <small><?php echo forum_escape(forum_ext_excerpt($message['body'], 140)); ?></small>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
        <?php forum_ext_render_pagination($pagination, ['view' => 'messages', 'box' => $box]); ?>
      <?php endif; ?>
    </section>
    <?php
    forum_ext_render_footer();
}

function forum_ext_render_message(array $message, ?array $flash): void
{
    forum_ext_render_header($message['subject'], 'Treść prywatnej wiadomości.');
    forum_ext_render_flash($flash);
    ?>
    <section class="panel forum-panel forum-narrow-panel">
      <span class="eyebrow">Prywatna wiadomość</span>
      <h2><?php echo forum_escape($message['subject']); ?></h2>
      <p class="forum-meta-line">Od: <strong><?php echo forum_escape($message['sender_username']); ?></strong> · Do: <strong><?php echo forum_escape($message['recipient_username']); ?></strong> · <?php echo forum_escape(forum_format_date($message['created_at'])); ?></p>
      <div class="forum-post-content"><?php echo forum_render_text($message['body']); ?></div>
      <div class="forum-button-row"><a class="button" href="<?php echo forum_url(['view' => 'compose', 'to' => $message['sender_username'], 'subject' => 'Re: ' . $message['subject']]); ?>">Odpowiedz</a><a class="button-secondary" href="<?php echo forum_url(['view' => 'messages']); ?>">Wróć do skrzynki</a></div>
    </section>
    <?php
    forum_ext_render_footer();
}

function forum_ext_render_compose(?array $flash, string $to = '', string $subject = '', string $body = ''): void
{
    forum_ext_render_header('Nowa wiadomość', 'Napisz prywatną wiadomość do innego użytkownika forum.');
    forum_ext_render_flash($flash);
    ?>
    <section class="panel forum-panel forum-narrow-panel">
      <span class="eyebrow">Nowa wiadomość</span>
      <h2>Napisz prywatnie do użytkownika</h2>
      <form class="forum-form" method="post" action="<?php echo forum_url(['view' => 'compose']); ?>">
        <input type="hidden" name="action" value="send_message"><input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
        <label for="message-to">Odbiorca</label><input id="message-to" type="text" name="recipient" value="<?php echo forum_escape($to); ?>" required>
        <label for="message-subject">Temat</label><input id="message-subject" type="text" name="subject" maxlength="180" value="<?php echo forum_escape($subject); ?>" required>
        <label for="message-body">Treść</label><textarea id="message-body" name="body" minlength="5" maxlength="12000" required><?php echo forum_escape($body); ?></textarea>
        <button class="button" type="submit">Wyślij wiadomość</button>
      </form>
    </section>
    <?php
    forum_ext_render_footer();
}

function forum_ext_render_user(array $profile, array $recentTopics, array $recentPosts, ?array $currentUser, ?array $flash): void
{
    forum_ext_render_header($profile['username'], 'Profil użytkownika ForumForgeCMS.');
    forum_ext_render_flash($flash);
    ?>
    <section class="panel forum-panel">
      <div class="forum-profile-grid">
        <article class="forum-profile-card">
          <div class="forum-profile-card-head">
            <?php forum_ext_render_avatar($profile, 'forum-avatar forum-avatar-large'); ?>
            <div>
              <h2><?php echo forum_escape($profile['username']); ?></h2>
              <p><?php echo forum_escape(forum_role_label((string) ($profile['role'] ?? 'member'))); ?></p>
            </div>
          </div>
          <div class="forum-stats-grid forum-profile-stats">
            <article class="forum-stat-box"><strong><?php echo (int) $profile['topic_count']; ?></strong><span>tematów</span></article>
            <article class="forum-stat-box"><strong><?php echo (int) $profile['post_count']; ?></strong><span>postów</span></article>
            <article class="forum-stat-box"><strong><?php echo (int) $profile['likes_received']; ?></strong><span>lajków</span></article>
            <article class="forum-stat-box"><strong><?php echo forum_escape(forum_format_date($profile['created_at'])); ?></strong><span>na forum od</span></article>
          </div>
          <?php if ($currentUser && (int) $currentUser['id'] !== (int) $profile['id']): ?><a class="button" href="<?php echo forum_url(['view' => 'compose', 'to' => $profile['username']]); ?>">Napisz wiadomość</a><?php endif; ?>
        </article>
        <div class="forum-stack">
          <article class="forum-surface-card"><div class="forum-post-body"><span class="eyebrow">Ostatnie tematy</span><div class="forum-list forum-list-compact"><?php foreach ($recentTopics as $topic): ?><article class="forum-row forum-row-tight"><div class="forum-row-main"><h3><a href="<?php echo forum_url(['view' => 'topic', 'id' => (int) $topic['id']]); ?>"><?php echo forum_escape($topic['title']); ?></a></h3><p><?php echo forum_escape($topic['category_name']); ?></p></div></article><?php endforeach; ?></div></div></article>
          <article class="forum-surface-card"><div class="forum-post-body"><span class="eyebrow">Ostatnie posty</span><div class="forum-list forum-list-compact"><?php foreach ($recentPosts as $post): ?><article class="forum-row forum-row-tight"><div class="forum-row-main"><h3><a href="<?php echo forum_url(['view' => 'topic', 'id' => (int) $post['topic_id']]); ?>#post-<?php echo (int) $post['id']; ?>"><?php echo forum_escape($post['topic_title']); ?></a></h3><p><?php echo forum_escape(forum_ext_excerpt($post['body'], 120)); ?></p></div></article><?php endforeach; ?></div></div></article>
        </div>
      </div>
    </section>
    <?php
    forum_ext_render_footer();
}

function forum_ext_render_post_reports_list(array $reports, string $returnView): void
{
    ?>
    <section id="moderation-reports" class="panel forum-panel">
      <span class="eyebrow">Moderacja</span>
      <h2>Zgłoszenia postów</h2>
      <?php if ($reports === []): ?>
        <p class="forum-admin-note">Nie ma aktywnych zgłoszeń do sprawdzenia.</p>
      <?php else: ?>
        <div class="forum-admin-list">
          <?php foreach ($reports as $report): ?>
            <article class="forum-admin-card forum-report-card">
              <div class="forum-section-head">
                <div>
                  <span class="eyebrow"><?php echo forum_escape($report['category_name']); ?></span>
                  <h3><?php echo forum_escape($report['topic_title']); ?></h3>
                  <p class="forum-meta-line">Zgłosił: <strong><?php echo forum_escape($report['reporter_username']); ?></strong> · Autor posta: <strong><?php echo forum_escape($report['post_author_username']); ?></strong> · <?php echo forum_escape(forum_format_date($report['created_at'])); ?></p>
                </div>
                <div class="forum-button-row">
                  <a class="button-secondary" href="<?php echo forum_url(['view' => 'topic', 'id' => (int) $report['topic_id']]); ?>#post-<?php echo (int) $report['post_id']; ?>">Przejdź do posta</a>
                  <form class="forum-inline-form" method="post" action="<?php echo forum_url(['view' => $returnView, 'section' => 'reports']); ?>">
                    <input type="hidden" name="action" value="close_post_report">
                    <input type="hidden" name="report_id" value="<?php echo (int) $report['id']; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
                    <button class="button" type="submit">Zamknij spór</button>
                  </form>
                </div>
              </div>
              <div class="forum-post-content forum-report-excerpt"><?php echo forum_render_text(forum_ext_excerpt((string) $report['post_body'], 420)); ?></div>
              <?php if ((string) ($report['reason'] ?? '') !== ''): ?>
                <p class="forum-admin-note"><strong>Powód:</strong> <?php echo forum_escape($report['reason']); ?></p>
              <?php endif; ?>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
    <?php
}

function forum_ext_render_moderation_panel(array $reports, ?array $flash): void
{
    forum_ext_render_header('Panel moderatora', 'Zgłoszenia postów i narzędzia porządkowe forum.');
    forum_ext_render_flash($flash);
    forum_ext_render_post_reports_list($reports, 'moderation');
    forum_ext_render_footer();
}

function forum_ext_render_admin(array $summary, array $categories, array $topUsers, ?array $flash): void
{
    $settings = forum_ext_settings();
    $summaryLabels = [
        'users' => 'użytkowników',
        'categories' => 'działów',
        'topics' => 'tematów',
        'posts' => 'postów',
        'likes' => 'lajków',
        'messages' => 'wiadomości',
    ];
    forum_ext_render_header('Panel administratora', 'Zarządzanie ustawieniami, działami i aktywnością forum.');
    forum_ext_render_flash($flash);
    ?>
    <section class="panel forum-panel">
      <span class="eyebrow">Administrator</span>
      <h2>Podsumowanie forum</h2>
      <div class="forum-stats-grid forum-stats-grid-wide">
        <?php foreach ($summary as $label => $value): ?><article class="forum-stat-box"><strong><?php echo (int) $value; ?></strong><span><?php echo forum_escape($summaryLabels[$label] ?? $label); ?></span></article><?php endforeach; ?>
      </div>
    </section>
    <section class="panel forum-panel">
      <span class="eyebrow">Ustawienia</span>
      <h2>Widok forum i rejestracja</h2>
      <form class="forum-form" method="post" action="<?php echo forum_url(['view' => 'admin']); ?>" enctype="multipart/form-data">
        <input type="hidden" name="action" value="update_settings"><input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
        <label for="brand-name">Nazwa forum</label><input id="brand-name" type="text" name="brand_name" value="<?php echo forum_escape($settings['brand_name']); ?>" maxlength="80" required>
        <label for="brand-tagline">Krótki opis forum</label><input id="brand-tagline" type="text" name="brand_tagline" value="<?php echo forum_escape($settings['brand_tagline']); ?>" maxlength="160" required>
        <label for="brand-logo">Własne logo forum</label><input id="brand-logo" type="file" name="brand_logo" accept=".jpg,.jpeg,.png,.webp,.gif,image/jpeg,image/png,image/webp,image/gif" data-forum-image="logo" data-forum-size="192" data-forum-fit="contain">
        <label for="graphic-style">Styl graficzny forum</label><select id="graphic-style" name="graphic_style"><?php foreach (forum_ext_graphic_styles() as $styleKey => $_styleLabel): ?><option value="<?php echo forum_escape($styleKey); ?>" <?php echo ($settings['graphic_style'] ?? 'classic') === $styleKey ? 'selected' : ''; ?>><?php echo forum_escape(forum_ext_graphic_style_label($styleKey)); ?></option><?php endforeach; ?></select>
        <label for="home-intro-title">Tytuł forum</label><input id="home-intro-title" type="text" name="home_intro_title" value="<?php echo forum_escape($settings['home_intro_title']); ?>" maxlength="120" required>
        <label for="home-intro-text">Opis forum</label><textarea id="home-intro-text" name="home_intro_text" maxlength="500" required><?php echo forum_escape($settings['home_intro_text']); ?></textarea>
        <label class="checkbox-row"><input type="checkbox" name="allow_registrations" value="1" <?php echo $settings['allow_registrations'] === '1' ? 'checked' : ''; ?>><span>Pozwól użytkownikom zakładać nowe konta</span></label>
        <button class="button" type="submit">Zapisz ustawienia</button>
      </form>
    </section>
    <section class="panel forum-panel"><span class="eyebrow">Najaktywniejsi</span><h2>Użytkownicy z największą aktywnością</h2><div class="forum-list forum-list-compact"><?php foreach ($topUsers as $user): ?><article class="forum-user-row"><?php forum_ext_render_avatar($user); ?><div class="forum-user-row-main"><h3><a href="<?php echo forum_url(['view' => 'user', 'id' => (int) $user['id']]); ?>"><?php echo forum_escape($user['username']); ?></a></h3><p><?php echo (int) $user['post_count']; ?> postów · <?php echo (int) $user['likes_received']; ?> lajków</p></div></article><?php endforeach; ?></div></section>
    <section class="panel forum-panel"><span class="eyebrow">Działy</span><h2>Zarządzaj działami</h2><div class="forum-admin-list"><?php foreach ($categories as $category): ?><article class="forum-admin-card"><form class="forum-form" method="post" action="<?php echo forum_url(['view' => 'admin']); ?>"><input type="hidden" name="action" value="update_category"><input type="hidden" name="category_id" value="<?php echo (int) $category['id']; ?>"><input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>"><label>Nazwa działu</label><input type="text" name="name" value="<?php echo forum_escape($category['name']); ?>" maxlength="80" required><label>Opis działu</label><textarea name="description" maxlength="260" required><?php echo forum_escape($category['description']); ?></textarea><button class="button" type="submit">Zapisz zmiany</button></form><div class="forum-button-row"><form class="forum-inline-form" method="post" action="<?php echo forum_url(['view' => 'admin']); ?>"><input type="hidden" name="action" value="move_category"><input type="hidden" name="direction" value="up"><input type="hidden" name="category_id" value="<?php echo (int) $category['id']; ?>"><input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>"><button class="button-secondary" type="submit">Wyżej</button></form><form class="forum-inline-form" method="post" action="<?php echo forum_url(['view' => 'admin']); ?>"><input type="hidden" name="action" value="move_category"><input type="hidden" name="direction" value="down"><input type="hidden" name="category_id" value="<?php echo (int) $category['id']; ?>"><input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>"><button class="button-secondary" type="submit">Niżej</button></form></div></article><?php endforeach; ?></div></section>
    <section class="panel forum-panel forum-narrow-panel"><span class="eyebrow">Nowy dział</span><h2>Dodaj dział forum</h2><form class="forum-form" method="post" action="<?php echo forum_url(['view' => 'admin']); ?>"><input type="hidden" name="action" value="create_category"><input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>"><label for="category-name">Nazwa działu</label><input id="category-name" type="text" name="name" maxlength="80" required><label for="category-description">Opis działu</label><textarea id="category-description" name="description" maxlength="260" required></textarea><button class="button" type="submit">Dodaj dział</button></form></section>
    <?php
    forum_ext_render_footer();
}

function forum_ext_render_admin_panel(array $summary, array $categories, array $topUsers, array $users, array $userPagination, ?array $flash, string $activeSection = 'settings', int $selectedUserId = 0): void
{
    $settings = forum_ext_settings();
    $forumSections = forum_fetch_sections_with_categories();
    $flatForumSections = forum_fetch_forum_sections();
    $adminSections = [
        'settings' => 'Widok forum i rejestracja',
        'basic-settings' => 'Ustawienia podstawowe',
        'backup' => 'Kopia zapasowa i przywracanie forum',
        'reports' => 'Zgłoszenia postów',
        'users' => 'Zarządzaj użytkownikami i moderatorami',
        'top-users' => 'Użytkownicy z największą aktywnością',
        'forum-sections' => 'Zarządzaj kategoriami',
        'create-forum-section' => 'Dodaj kategorię',
        'categories' => 'Zarządzaj działami',
        'create-category' => 'Dodaj dział forum',
    ];
    $adminChildSections = ['create-forum-section', 'create-category'];
    if ($activeSection !== 'user' && !isset($adminSections[$activeSection])) {
        $activeSection = 'settings';
    }
    $selectedUser = null;
    foreach ($users as $user) {
        if ((int) $user['id'] === $selectedUserId) {
            $selectedUser = $user;
            break;
        }
    }
    if ($activeSection === 'user' && !$selectedUser) {
        $selectedUser = forum_ext_admin_user($selectedUserId);
    }
    if ($activeSection === 'user' && !$selectedUser) {
        $activeSection = 'users';
    }
    $staffUsers = array_values(array_filter($users, static fn(array $user): bool => in_array((string) ($user['role'] ?? 'member'), ['admin', 'moderator'], true)));
    $memberUsers = array_values(array_filter($users, static fn(array $user): bool => (string) ($user['role'] ?? 'member') === 'member'));

    forum_ext_render_header('Panel administratora', 'Zarządzanie ustawieniami, działami, użytkownikami i aktywnością forum.');
    forum_ext_render_flash($flash);
    ?>
    <div class="forum-admin-layout">
      <aside class="forum-admin-sidebar" aria-label="Menu panelu administratora">
        <span class="eyebrow">Panel admina</span>
        <p class="forum-admin-note">ForumForgeCMS <?php echo forum_escape(FORUM_VERSION); ?></p>
        <nav class="forum-admin-menu">
          <?php foreach ($adminSections as $section => $label): ?>
            <?php $isActiveMenuItem = $activeSection === $section || ($activeSection === 'user' && $section === 'users'); ?>
            <?php $menuClass = trim(($isActiveMenuItem ? 'is-active ' : '') . (in_array($section, $adminChildSections, true) ? 'is-child' : '')); ?>
            <a class="<?php echo forum_escape($menuClass); ?>" href="<?php echo forum_url(['view' => 'admin', 'section' => $section]); ?>" <?php echo $isActiveMenuItem ? 'aria-current="page"' : ''; ?>><?php echo forum_escape($label); ?></a>
          <?php endforeach; ?>
        </nav>
      </aside>

      <div class="forum-admin-content">
        <?php if ($activeSection === 'settings'): ?>
        <section id="admin-forum-settings" class="panel forum-panel">
          <span class="eyebrow">Ustawienia</span>
          <h2>Widok forum i rejestracja</h2>
          <form class="forum-form" method="post" action="<?php echo forum_url(['view' => 'admin', 'section' => 'settings']); ?>" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update_settings">
            <input type="hidden" name="return_section" value="settings">
            <input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
            <input type="hidden" name="brand_name" value="<?php echo forum_escape($settings['brand_name']); ?>">
            <input type="hidden" name="brand_tagline" value="<?php echo forum_escape($settings['brand_tagline']); ?>">
            <input type="hidden" name="forum_language" value="<?php echo forum_escape($settings['forum_language'] ?? 'pl'); ?>">
            <input type="hidden" name="home_intro_title" value="<?php echo forum_escape($settings['home_intro_title']); ?>">
            <input type="hidden" name="home_intro_text" value="<?php echo forum_escape($settings['home_intro_text']); ?>">
            <label for="brand-logo">Własne logo forum</label>
            <input id="brand-logo" type="file" name="brand_logo" accept=".jpg,.jpeg,.png,.webp,.gif,image/jpeg,image/png,image/webp,image/gif" data-forum-image="logo" data-forum-size="192" data-forum-fit="contain">
            <label for="graphic-style">Styl graficzny forum</label>
            <select id="graphic-style" name="graphic_style">
              <?php foreach (forum_ext_graphic_styles() as $styleKey => $_styleLabel): ?>
                <option value="<?php echo forum_escape($styleKey); ?>" <?php echo ($settings['graphic_style'] ?? 'classic') === $styleKey ? 'selected' : ''; ?>><?php echo forum_escape(forum_ext_graphic_style_label($styleKey)); ?></option>
              <?php endforeach; ?>
            </select>
            <label class="checkbox-row">
              <input type="checkbox" name="allow_registrations" value="1" <?php echo $settings['allow_registrations'] === '1' ? 'checked' : ''; ?>>
              <span>Pozwól użytkownikom zakładać nowe konta</span>
            </label>
            <button class="button" type="submit">Zapisz ustawienia</button>
          </form>
        </section>
        <?php endif; ?>

        <?php if ($activeSection === 'basic-settings'): ?>
        <section id="admin-basic-settings" class="panel forum-panel">
          <span class="eyebrow">Podstawowe</span>
          <h2>Ustawienia podstawowe</h2>
          <form class="forum-form" method="post" action="<?php echo forum_url(['view' => 'admin', 'section' => 'basic-settings']); ?>">
            <input type="hidden" name="action" value="update_settings">
            <input type="hidden" name="return_section" value="basic-settings">
            <input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
            <input type="hidden" name="graphic_style" value="<?php echo forum_escape($settings['graphic_style']); ?>">
            <?php if ($settings['allow_registrations'] === '1'): ?>
              <input type="hidden" name="allow_registrations" value="1">
            <?php endif; ?>
            <label for="basic-forum-language">Język forum</label>
            <select id="basic-forum-language" name="forum_language">
              <?php foreach (forum_ext_languages() as $languageKey => $languageLabel): ?>
                <option value="<?php echo forum_escape($languageKey); ?>" <?php echo ($settings['forum_language'] ?? 'pl') === $languageKey ? 'selected' : ''; ?>><?php echo forum_escape($languageLabel); ?></option>
              <?php endforeach; ?>
            </select>
            <label for="basic-brand-name">Nazwa forum</label>
            <input id="basic-brand-name" type="text" name="brand_name" value="<?php echo forum_escape($settings['brand_name']); ?>" maxlength="80" required>
            <label for="basic-brand-tagline">Krótki opis forum</label>
            <input id="basic-brand-tagline" type="text" name="brand_tagline" value="<?php echo forum_escape($settings['brand_tagline']); ?>" maxlength="160" required>
            <label for="basic-home-intro-title">Tytuł forum</label>
            <input id="basic-home-intro-title" type="text" name="home_intro_title" value="<?php echo forum_escape($settings['home_intro_title']); ?>" maxlength="120" required>
            <label for="basic-home-intro-text">Opis forum</label>
            <textarea id="basic-home-intro-text" name="home_intro_text" maxlength="500" required><?php echo forum_escape($settings['home_intro_text']); ?></textarea>
            <button class="button" type="submit">Zapisz ustawienia podstawowe</button>
          </form>
        </section>
        <?php endif; ?>

        <?php if ($activeSection === 'backup'): ?>
        <section id="admin-backup" class="panel forum-panel">
          <span class="eyebrow">Kopia danych</span>
          <h2>Kopia zapasowa i przywracanie forum</h2>
          <p class="forum-admin-note">Pobierz komplet danych forum jako ZIP: bazę SQLite, avatary użytkowników oraz własne logo forum. Taki plik możesz później wgrać na innym hostingu i przywrócić forum z panelu administratora.</p>

          <div class="forum-admin-list">
            <article class="forum-admin-card">
              <span class="eyebrow">Pobieranie</span>
              <h3>Pobierz kopię zapasową</h3>
              <p class="forum-admin-note">Archiwum zawiera <code>forum-data/forum.sqlite</code>, katalog <code>forum-data/avatars</code> oraz katalog <code>forum-data/brand</code>.</p>
              <form class="forum-form" method="post" action="<?php echo forum_url(['view' => 'admin', 'section' => 'backup']); ?>">
                <input type="hidden" name="action" value="download_backup">
                <input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
                <button class="button" type="submit">Pobierz backup ZIP</button>
              </form>
            </article>

            <article class="forum-admin-card forum-admin-danger-zone">
              <span class="eyebrow">Przywracanie</span>
              <h3>Przywróć forum z kopii</h3>
              <p class="forum-admin-note">Przywracanie zastąpi aktualną bazę danych, avatary i logo zawartością przesłanej kopii. Po tej operacji odśwież stronę i zaloguj się danymi z przywróconej bazy.</p>
              <form class="forum-form" method="post" action="<?php echo forum_url(['view' => 'admin', 'section' => 'backup']); ?>" enctype="multipart/form-data" onsubmit="return confirm('Przywracanie zastąpi aktualne dane forum. Kontynuować?');">
                <input type="hidden" name="action" value="restore_backup">
                <input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
                <label for="forum-backup">Plik kopii zapasowej ZIP</label>
                <input id="forum-backup" type="file" name="forum_backup" accept=".zip,application/zip,application/x-zip-compressed" required>
                <button class="button-secondary forum-danger-button" type="submit">Przywróć backup</button>
              </form>
            </article>
          </div>
        </section>
        <?php endif; ?>

        <?php if ($activeSection === 'reports'): ?>
          <?php forum_ext_render_post_reports_list(forum_ext_open_post_reports(), 'admin'); ?>
        <?php endif; ?>

        <?php if ($activeSection === 'users'): ?>
        <section id="admin-users" class="panel forum-panel">
          <span class="eyebrow">Użytkownicy</span>
          <h2>Zarządzaj użytkownikami i moderatorami</h2>
          <p class="forum-admin-note">Kliknij nazwę użytkownika, żeby otworzyć jego kartę edycji, zmienić rolę albo wykonać operacje administracyjne.</p>

          <div class="forum-admin-user-list-group">
            <div>
              <h3>Administratorzy i moderatorzy</h3>
              <div class="forum-list forum-list-compact">
                <?php foreach ($staffUsers as $user): ?>
                  <article class="forum-admin-user-list-row">
                    <?php forum_ext_render_avatar($user); ?>
                    <div class="forum-user-row-main">
                      <h3><a href="<?php echo forum_url(['view' => 'admin', 'section' => 'user', 'user_id' => (int) $user['id']]); ?>"><?php echo forum_escape($user['username']); ?></a></h3>
                      <p><?php echo forum_escape(forum_role_label((string) ($user['role'] ?? 'member'))); ?> · <?php echo (int) $user['topic_count']; ?> tematów · <?php echo (int) $user['post_count']; ?> postów</p>
                    </div>
                  </article>
                <?php endforeach; ?>
              </div>
            </div>

            <div>
              <h3>Zwykli użytkownicy</h3>
              <div class="forum-list forum-list-compact">
                <?php if ($memberUsers === []): ?>
                  <p class="forum-admin-note">Nie ma jeszcze zwykłych użytkowników.</p>
                <?php endif; ?>
                <?php foreach ($memberUsers as $user): ?>
                  <article class="forum-admin-user-list-row">
                    <?php forum_ext_render_avatar($user); ?>
                    <div class="forum-user-row-main">
                      <h3><a href="<?php echo forum_url(['view' => 'admin', 'section' => 'user', 'user_id' => (int) $user['id']]); ?>"><?php echo forum_escape($user['username']); ?></a></h3>
                      <p><?php echo (int) $user['topic_count']; ?> tematów · <?php echo (int) $user['post_count']; ?> postów · <?php echo (int) $user['likes_received']; ?> lajków</p>
                    </div>
                  </article>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
          <?php forum_ext_render_pagination($userPagination, ['view' => 'admin', 'section' => 'users']); ?>
        </section>
        <?php endif; ?>

        <?php if ($activeSection === 'user' && $selectedUser): ?>
        <?php $isPrimaryAdmin = strcasecmp((string) $selectedUser['username'], FORUM_ADMIN_USERNAME) === 0; ?>
        <section id="admin-user-detail" class="panel forum-panel">
          <div class="forum-section-head">
            <div>
              <span class="eyebrow">Użytkownik</span>
              <h2><?php echo forum_escape($selectedUser['username']); ?></h2>
            </div>
            <div class="forum-button-row">
              <a class="button-secondary" href="<?php echo forum_url(['view' => 'admin', 'section' => 'users']); ?>">Wróć do listy</a>
              <a class="button-secondary" href="<?php echo forum_url(['view' => 'user', 'id' => (int) $selectedUser['id']]); ?>">Profil publiczny</a>
            </div>
          </div>

          <article class="forum-admin-card forum-admin-user-card">
            <div class="forum-admin-user-head">
              <div class="forum-user-row">
                <?php forum_ext_render_avatar($selectedUser); ?>
                <div class="forum-user-row-main">
                  <h3><?php echo forum_escape($selectedUser['username']); ?></h3>
                  <p><?php echo forum_escape(forum_role_label((string) ($selectedUser['role'] ?? 'member'))); ?> · <?php echo forum_escape($selectedUser['email']); ?></p>
                </div>
              </div>
              <div class="forum-admin-user-meta">
                <span><?php echo (int) $selectedUser['topic_count']; ?> tematów</span>
                <span><?php echo (int) $selectedUser['post_count']; ?> postów</span>
                <span><?php echo (int) $selectedUser['likes_received']; ?> lajków</span>
                <span><?php echo (int) ($selectedUser['likes_given'] ?? 0); ?> danych lajk&oacute;w</span>
                <span><?php echo (int) ($selectedUser['message_count'] ?? 0); ?> wiadomo&#347;ci prywatnych</span>
                <span>Ostatnie logowanie: <?php echo forum_escape(!empty($selectedUser['last_login_at']) ? forum_format_date($selectedUser['last_login_at']) : 'jeszcze brak'); ?></span>
              </div>
            </div>

            <form class="forum-form" method="post" action="<?php echo forum_url(['view' => 'admin', 'section' => 'user', 'user_id' => (int) $selectedUser['id']]); ?>">
              <input type="hidden" name="action" value="update_user">
              <input type="hidden" name="user_id" value="<?php echo (int) $selectedUser['id']; ?>">
              <input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
              <div class="forum-admin-user-form-grid">
                <div>
                  <label for="user-name-<?php echo (int) $selectedUser['id']; ?>">Nazwa użytkownika</label>
                  <input id="user-name-<?php echo (int) $selectedUser['id']; ?>" type="text" name="username" maxlength="40" value="<?php echo forum_escape($selectedUser['username']); ?>" <?php echo $isPrimaryAdmin ? 'readonly' : ''; ?> required>
                </div>
                <div>
                  <label for="user-email-<?php echo (int) $selectedUser['id']; ?>">E-mail</label>
                  <input id="user-email-<?php echo (int) $selectedUser['id']; ?>" type="email" name="email" maxlength="190" value="<?php echo forum_escape($selectedUser['email']); ?>" required>
                </div>
                <div>
                  <label for="user-role-<?php echo (int) $selectedUser['id']; ?>">Rola</label>
                  <?php if ($isPrimaryAdmin): ?>
                    <input id="user-role-<?php echo (int) $selectedUser['id']; ?>" type="text" value="administrator" readonly>
                    <input type="hidden" name="role" value="admin">
                  <?php else: ?>
                    <select id="user-role-<?php echo (int) $selectedUser['id']; ?>" name="role">
                      <option value="member" <?php echo ($selectedUser['role'] ?? '') === 'member' ? 'selected' : ''; ?>>użytkownik</option>
                      <option value="moderator" <?php echo ($selectedUser['role'] ?? '') === 'moderator' ? 'selected' : ''; ?>>moderator</option>
                    </select>
                  <?php endif; ?>
                </div>
                <div>
                  <label for="user-password-<?php echo (int) $selectedUser['id']; ?>">Nowe hasło</label>
                  <input id="user-password-<?php echo (int) $selectedUser['id']; ?>" type="password" name="new_password" minlength="10" placeholder="zostaw puste, jeśli bez zmian">
                </div>
                <p class="forum-admin-note forum-form-field-full">
                  <?php if ($isPrimaryAdmin): ?>
                    To główne konto administratora forum. Możesz zmienić jego e-mail i hasło, ale nie nazwę ani rolę.
                  <?php else: ?>
                    Moderator może przypinać, zamykać i usuwać tematy oraz edytować posty podczas moderacji.
                  <?php endif; ?>
                </p>
              </div>
              <button class="button" type="submit">Zapisz użytkownika</button>
            </form>

            <?php if (!$isPrimaryAdmin): ?>
              <div class="forum-admin-danger-zone">
                <form class="forum-inline-form forum-admin-danger-form" method="post" action="<?php echo forum_url(['view' => 'admin', 'section' => 'user', 'user_id' => (int) $selectedUser['id']]); ?>" onsubmit="return confirm('Wysłać wiadomość potwierdzającą usunięcie całej aktywności tego użytkownika?');">
                  <input type="hidden" name="action" value="request_delete_user_activity">
                  <input type="hidden" name="user_id" value="<?php echo (int) $selectedUser['id']; ?>">
                  <input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
                  <button class="button-secondary forum-danger-button" type="submit">Usuń całą aktywność</button>
                </form>
                <p class="forum-admin-note">Ta operacja usuwa tematy, posty, lajki oraz prywatne wiadomości użytkownika. Samo konto zostaje na forum. Potwierdzenie przyjdzie na adres administratora.</p>

                <form class="forum-inline-form forum-admin-danger-form" method="post" action="<?php echo forum_url(['view' => 'admin', 'section' => 'user', 'user_id' => (int) $selectedUser['id']]); ?>" onsubmit="return confirm('Na pewno całkowicie usunąć tego użytkownika, jego aktywność, wiadomości, lajki, tokeny i avatar? Tej operacji nie da się cofnąć.');">
                  <input type="hidden" name="action" value="delete_user">
                  <input type="hidden" name="user_id" value="<?php echo (int) $selectedUser['id']; ?>">
                  <input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
                  <button class="button-secondary forum-danger-button" type="submit">Usuń użytkownika</button>
                </form>
                <p class="forum-admin-note">Ta operacja usuwa konto użytkownika oraz jego dane z bazy forum. Nie zostawia profilu ani historii prywatnych wiadomości tego konta.</p>
              </div>
            <?php endif; ?>
          </article>
        </section>
        <?php endif; ?>

        <?php if ($activeSection === 'top-users'): ?>
        <section id="admin-top-users" class="panel forum-panel">
          <span class="eyebrow">Najaktywniejsi</span>
          <h2>Użytkownicy z największą aktywnością</h2>
          <div class="forum-list forum-home-recent-list">
            <?php foreach ($topUsers as $user): ?>
              <article class="forum-user-row">
                <?php forum_ext_render_avatar($user); ?>
                <div class="forum-user-row-main">
                  <h3><a href="<?php echo forum_url(['view' => 'user', 'id' => (int) $user['id']]); ?>"><?php echo forum_escape($user['username']); ?></a></h3>
                  <p><?php echo forum_escape(forum_role_label((string) ($user['role'] ?? 'member'))); ?> · <?php echo (int) $user['post_count']; ?> postów · <?php echo (int) $user['likes_received']; ?> lajków</p>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        </section>
        <?php endif; ?>

        <?php if ($activeSection === 'forum-sections'): ?>
        <section id="admin-forum-sections" class="panel forum-panel">
          <span class="eyebrow">Kategorie</span>
          <h2>Zarządzaj kategoriami</h2>
          <div class="forum-admin-list">
            <?php foreach ($flatForumSections as $section): ?>
              <article class="forum-admin-card forum-admin-section-card">
                <form class="forum-form" method="post" action="<?php echo forum_url(['view' => 'admin', 'section' => 'forum-sections']); ?>">
                  <input type="hidden" name="action" value="update_forum_section">
                  <input type="hidden" name="section_id" value="<?php echo (int) $section['id']; ?>">
                  <input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
                  <span class="eyebrow">Kategoria</span>
                  <div class="forum-admin-user-form-grid">
                    <div>
                      <label for="forum-section-name-<?php echo (int) $section['id']; ?>">Nazwa kategorii</label>
                      <input id="forum-section-name-<?php echo (int) $section['id']; ?>" type="text" name="name" value="<?php echo forum_escape($section['name']); ?>" maxlength="80" required>
                    </div>
                    <div class="forum-form-field-full">
                      <label for="forum-section-description-<?php echo (int) $section['id']; ?>">Opis kategorii</label>
                      <textarea id="forum-section-description-<?php echo (int) $section['id']; ?>" name="description" maxlength="260"><?php echo forum_escape($section['description']); ?></textarea>
                    </div>
                  </div>
                  <button class="button" type="submit">Zapisz kategorię</button>
                </form>
              </article>
            <?php endforeach; ?>
          </div>
        </section>
        <?php endif; ?>

        <?php if ($activeSection === 'create-forum-section'): ?>
        <section id="admin-create-forum-section" class="panel forum-panel forum-narrow-panel">
          <span class="eyebrow">Nowa kategoria</span>
          <h2>Dodaj kategorię</h2>
          <form class="forum-form" method="post" action="<?php echo forum_url(['view' => 'admin', 'section' => 'create-forum-section']); ?>">
            <input type="hidden" name="action" value="create_forum_section">
            <input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
            <label for="forum-section-name">Nazwa kategorii</label>
            <input id="forum-section-name" type="text" name="name" maxlength="80" required>
            <label for="forum-section-description">Opis kategorii</label>
            <textarea id="forum-section-description" name="description" maxlength="260"></textarea>
            <button class="button" type="submit">Dodaj kategorię</button>
          </form>
        </section>
        <?php endif; ?>

        <?php if ($activeSection === 'categories'): ?>
        <section id="admin-categories" class="panel forum-panel">
          <span class="eyebrow">Działy</span>
          <h2>Zarządzaj działami</h2>
          <div class="forum-admin-list">
            <?php foreach ($forumSections as $section): ?>
              <article class="forum-admin-card forum-admin-section-card">
                <div class="forum-section-title">
                  <div>
                  <span class="eyebrow">Kategoria</span>
                  <h3><?php echo forum_escape($section['name']); ?></h3>
                  <?php if ((string) ($section['description'] ?? '') !== ''): ?>
                    <p><?php echo forum_escape($section['description']); ?></p>
                  <?php endif; ?>
                  </div>
                </div>

                <?php if (($section['categories'] ?? []) === []): ?>
                  <p class="forum-admin-note">W tej kategorii nie ma jeszcze działów.</p>
                <?php endif; ?>

                <?php foreach (($section['categories'] ?? []) as $category): ?>
                  <article class="forum-admin-card forum-admin-category-card">
                    <form class="forum-form" method="post" action="<?php echo forum_url(['view' => 'admin', 'section' => 'categories']); ?>">
                      <input type="hidden" name="action" value="update_category">
                      <input type="hidden" name="category_id" value="<?php echo (int) $category['id']; ?>">
                      <input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
                      <div class="forum-admin-user-form-grid">
                        <div>
                          <label>Nazwa działu</label>
                          <input type="text" name="name" value="<?php echo forum_escape($category['name']); ?>" maxlength="80" required>
                        </div>
                        <div>
                          <label>Kategoria</label>
                          <select name="section_id">
                            <?php foreach ($flatForumSections as $forumSection): ?>
                              <option value="<?php echo (int) $forumSection['id']; ?>" <?php echo (int) $forumSection['id'] === (int) $category['section_id'] ? 'selected' : ''; ?>><?php echo forum_escape($forumSection['name']); ?></option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                        <div class="forum-form-field-full">
                          <label>Opis działu</label>
                          <textarea name="description" maxlength="260" required><?php echo forum_escape($category['description']); ?></textarea>
                        </div>
                      </div>
                      <button class="button" type="submit">Zapisz dział</button>
                    </form>
                    <div class="forum-button-row">
                      <form class="forum-inline-form" method="post" action="<?php echo forum_url(['view' => 'admin', 'section' => 'categories']); ?>">
                        <input type="hidden" name="action" value="move_category">
                        <input type="hidden" name="direction" value="up">
                        <input type="hidden" name="category_id" value="<?php echo (int) $category['id']; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
                        <button class="button-secondary" type="submit">Wyżej w kategorii</button>
                      </form>
                      <form class="forum-inline-form" method="post" action="<?php echo forum_url(['view' => 'admin', 'section' => 'categories']); ?>">
                        <input type="hidden" name="action" value="move_category">
                        <input type="hidden" name="direction" value="down">
                        <input type="hidden" name="category_id" value="<?php echo (int) $category['id']; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
                        <button class="button-secondary" type="submit">Niżej w kategorii</button>
                      </form>
                    </div>
                  </article>
                <?php endforeach; ?>
              </article>
            <?php endforeach; ?>
          </div>
        </section>
        <?php endif; ?>

        <?php if ($activeSection === 'create-category'): ?>
        <section id="admin-create-category" class="panel forum-panel forum-narrow-panel">
          <span class="eyebrow">Nowy dział</span>
          <h2>Dodaj dział forum</h2>
          <form class="forum-form" method="post" action="<?php echo forum_url(['view' => 'admin', 'section' => 'create-category']); ?>">
            <input type="hidden" name="action" value="create_category">
            <input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
            <label for="category-name">Nazwa działu</label>
            <input id="category-name" type="text" name="name" maxlength="80" required>
            <label for="category-section">Kategoria</label>
            <select id="category-section" name="section_id">
              <?php foreach ($flatForumSections as $forumSection): ?>
                <option value="<?php echo (int) $forumSection['id']; ?>"><?php echo forum_escape($forumSection['name']); ?></option>
              <?php endforeach; ?>
            </select>
            <label for="category-description">Opis działu</label>
            <textarea id="category-description" name="description" maxlength="260" required></textarea>
            <button class="button" type="submit">Dodaj dział</button>
          </form>
        </section>
        <?php endif; ?>
      </div>
    </div>
    <?php
    forum_ext_render_footer();
}


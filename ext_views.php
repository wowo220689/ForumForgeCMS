<?php
declare(strict_types=1);

function forum_ext_render_editor_toolbar(string $targetId): void
{
    $emoticons = [
        ['&#128578;', forum_t('Uśmiech')],
        ['&#128512;', forum_t('Szeroki uśmiech')],
        ['&#128513;', forum_t('Zadowolenie')],
        ['&#128514;', forum_t('Śmiech')],
        ['&#128521;', forum_t('Mrugnięcie')],
        ['&#128526;', forum_t('Luzak')],
        ['&#128525;', forum_t('Serduszka')],
        ['&#128536;', forum_t('Buziak')],
        ['&#128539;', forum_t('Język')],
        ['&#128540;', forum_t('Psikus')],
        ['&#128558;', forum_t('Zdziwienie')],
        ['&#128559;', forum_t('Ojej')],
        ['&#128528;', forum_t('Neutralnie')],
        ['&#128577;', forum_t('Smutek')],
        ['&#128546;', forum_t('Łza')],
        ['&#128557;', forum_t('Płacz')],
        ['&#128545;', forum_t('Złość')],
        ['&#128520;', forum_t('Diabełek')],
        ['&#128519;', forum_t('Aniołek')],
        ['&#129300;', forum_t('Myślę')],
        ['&#129320;', forum_t('Podejrzliwie')],
        ['&#129327;', forum_t('Szok')],
        ['&#128564;', forum_t('Sen')],
        ['&#129296;', forum_t('Milczę')],
        ['&#128556;', forum_t('Nerwowo')],
        ['&#128580;', forum_t('Przewracam oczami')],
        ['&#128077;', forum_t('OK')],
        ['&#128078;', forum_t('Nie')],
        ['&#128079;', forum_t('Brawo')],
        ['&#128591;', forum_t('Proszę')],
        ['&#128170;', forum_t('Siła')],
        ['&#128076;', forum_t('Dobrze')],
        ['&#10084;&#65039;', forum_t('Serce')],
        ['&#128148;', forum_t('Złamane serce')],
        ['&#11088;', forum_t('Gwiazdka')],
        ['&#127881;', forum_t('Impreza')],
        ['&#128293;', forum_t('Ogień')],
        ['&#128161;', forum_t('Pomysł')],
        ['&#10071;', forum_t('Uwaga')],
        ['&#10067;', forum_t('Pytanie')],
    ];
    ?>
    <div class="forum-editor-toolbar" data-editor-target="<?php echo forum_escape($targetId); ?>" role="toolbar" aria-label="<?php echo forum_escape(forum_t('Formatowanie posta')); ?>">
      <button type="button" data-wrap="[b]" data-close="[/b]" title="<?php echo forum_escape(forum_t('Pogrubienie')); ?>"><strong>B</strong></button>
      <button type="button" data-wrap="[i]" data-close="[/i]" title="<?php echo forum_escape(forum_t('Kursywa')); ?>"><em>I</em></button>
      <button type="button" data-wrap="[u]" data-close="[/u]" title="<?php echo forum_escape(forum_t('Podkreślenie')); ?>"><span class="forum-underline">U</span></button>
      <button type="button" data-prompt="url" title="<?php echo forum_escape(forum_t('Link')); ?>">🔗</button>
      <button type="button" data-wrap="[quote]" data-close="[/quote]" title="<?php echo forum_escape(forum_t('Cytat')); ?>">❞</button>
      <button type="button" data-wrap="[code]" data-close="[/code]" title="<?php echo forum_escape(forum_t('Kod')); ?>">&lt;&gt;</button>
      <button type="button" data-action="emoticons" class="forum-emoticon-toggle" title="<?php echo forum_escape(forum_t('Emotki')); ?>">☺</button>
      <button type="button" data-line="[list]\n[*] " data-close="\n[/list]" title="<?php echo forum_escape(forum_t('Lista punktowana')); ?>">•</button>
      <button type="button" data-line="[olist]\n[*] " data-close="\n[/olist]" title="<?php echo forum_escape(forum_t('Lista numerowana')); ?>">1.</button>
      <button type="button" data-wrap="[left]" data-close="[/left]" title="<?php echo forum_escape(forum_t('Do lewej')); ?>">☰</button>
      <button type="button" data-wrap="[center]" data-close="[/center]" title="<?php echo forum_escape(forum_t('Wyśrodkuj')); ?>">≡</button>
      <button type="button" data-wrap="[right]" data-close="[/right]" title="<?php echo forum_escape(forum_t('Do prawej')); ?>">☷</button>
      <button type="button" data-wrap="[s]" data-close="[/s]" title="<?php echo forum_escape(forum_t('Przekreślenie')); ?>">S</button>
      <button type="button" data-wrap="[sup]" data-close="[/sup]" title="<?php echo forum_escape(forum_t('Indeks górny')); ?>">x²</button>
      <button type="button" data-wrap="[sub]" data-close="[/sub]" title="<?php echo forum_escape(forum_t('Indeks dolny')); ?>">x₂</button>
      <select data-style="color" title="<?php echo forum_escape(forum_t('Kolor tekstu')); ?>">
        <option value=""><?php echo forum_escape(forum_t('Kolor')); ?></option>
        <option value="#0f4c5c"><?php echo forum_escape(forum_t('Morski')); ?></option>
        <option value="#cc5a4d"><?php echo forum_escape(forum_t('Czerwony')); ?></option>
        <option value="#1f8a59"><?php echo forum_escape(forum_t('Zielony')); ?></option>
        <option value="#7c3aed"><?php echo forum_escape(forum_t('Fioletowy')); ?></option>
      </select>
      <select data-style="size" title="<?php echo forum_escape(forum_t('Rozmiar tekstu')); ?>">
        <option value=""><?php echo forum_escape(forum_t('Rozmiar')); ?></option>
        <option value="small"><?php echo forum_escape(forum_t('Mały')); ?></option>
        <option value="normal"><?php echo forum_escape(forum_t('Normalny')); ?></option>
        <option value="large"><?php echo forum_escape(forum_t('Duży')); ?></option>
      </select>
      <button type="button" data-prompt="image" title="<?php echo forum_escape(forum_t('Obraz z URL')); ?>">🖼</button>
      <button type="button" data-action="preview" title="<?php echo forum_escape(forum_t('Podgląd')); ?>">👁</button>
      <button type="button" data-action="clear" title="<?php echo forum_escape(forum_t('Wyczyść')); ?>">□</button>
      <div class="forum-emoticon-picker" data-emoticon-picker hidden aria-label="<?php echo forum_escape(forum_t('Lista emotek')); ?>">
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
        <article class="forum-stat-box"><strong><?php echo $summary['users']; ?></strong><span><?php echo forum_escape(forum_t('użytkowników')); ?></span></article>
        <article class="forum-stat-box"><strong><?php echo $summary['topics']; ?></strong><span><?php echo forum_escape(forum_t('tematów')); ?></span></article>
        <article class="forum-stat-box"><strong><?php echo $summary['posts']; ?></strong><span><?php echo forum_escape(forum_t('postów')); ?></span></article>
        <article class="forum-stat-box"><strong><?php echo $summary['likes']; ?></strong><span><?php echo forum_escape(forum_t('lajków')); ?></span></article>
        <article class="forum-stat-box"><strong><?php echo $summary['messages']; ?></strong><span><?php echo forum_escape(forum_t('wiadomości prywatnych')); ?></span></article>
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
                  <span><?php echo forum_escape(forum_t('tematów')); ?></span>
                  <small><?php echo (int) $category['post_count']; ?> <?php echo forum_escape(forum_t('postów')); ?></small>
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
          <span class="eyebrow"><?php echo forum_escape(forum_t('Najnowsze')); ?></span>
          <h2><?php echo forum_escape(forum_t('Ostatnio aktywne tematy')); ?></h2>
          <div class="forum-list forum-home-recent-list">
            <?php foreach ($recentTopics as $topic): ?>
              <article class="forum-row forum-row-tight">
                <div class="forum-row-main">
                  <h3><a href="<?php echo forum_url(['view' => 'topic', 'id' => (int) $topic['id']]); ?>"><?php echo forum_escape($topic['title']); ?></a></h3>
                  <p><?php echo forum_escape($topic['category_name']); ?> <?php echo forum_escape(forum_t('· autor:')); ?> <strong><?php echo forum_escape($topic['author_username']); ?></strong></p>
                </div>
                <div class="forum-row-meta">
                  <strong><?php echo (int) $topic['post_count']; ?></strong>
                  <span><?php echo forum_escape(forum_t('postów')); ?></span>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        </div>
        <div>
          <span class="eyebrow"><?php echo forum_escape(forum_t('Aktywni użytkownicy')); ?></span>
          <h2><?php echo forum_escape(forum_t('Kto najczęściej pomaga')); ?></h2>
          <div class="forum-list forum-list-compact">
            <?php foreach ($topUsers as $user): ?>
              <article class="forum-user-row">
                <?php forum_ext_render_avatar($user); ?>
                <div>
                  <h3><a href="<?php echo forum_url(['view' => 'user', 'id' => (int) $user['id']]); ?>"><?php echo forum_escape($user['username']); ?></a></h3>
                  <p><?php echo (int) $user['post_count']; ?> <?php echo forum_escape(forum_t('postów ·')); ?> <?php echo (int) $user['likes_received']; ?> <?php echo forum_escape(forum_t('lajków')); ?></p>
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
        <div><span class="eyebrow"><?php echo forum_escape(forum_t('Dział forum')); ?></span><h2><?php echo forum_escape($category['name']); ?></h2><p class="lead forum-lead"><?php echo forum_escape($category['description']); ?></p></div>
        <div class="forum-button-row"><a class="button-secondary" href="<?php echo forum_url(); ?>"><?php echo forum_escape(forum_t('Wróć do listy działów')); ?></a></div>
      </div>
      <?php if ($topics === []): ?>
        <div class="callout"><strong><?php echo forum_escape(forum_t('Ten dział jest jeszcze pusty.')); ?></strong><p><?php echo forum_escape(forum_t('Jeśli chcesz, możesz założyć pierwszy temat i rozpocząć dyskusję.')); ?></p></div>
      <?php else: ?>
        <div class="forum-list forum-topic-list">
          <?php foreach ($topics as $topic): ?>
            <article class="forum-row">
              <div class="forum-row-main">
                <h3><a href="<?php echo forum_url(['view' => 'topic', 'id' => (int) $topic['id']]); ?>"><?php echo forum_escape($topic['title']); ?></a></h3>
                <p><?php if ((int) $topic['is_pinned'] === 1): ?><span class="forum-badge"><?php echo forum_escape(forum_t('Przypięty')); ?></span><?php endif; ?><?php if ((int) $topic['is_locked'] === 1): ?><span class="forum-badge forum-badge-muted"><?php echo forum_escape(forum_t('Zamknięty')); ?></span><?php endif; ?> <?php echo forum_escape(forum_t('Autor:')); ?> <strong><?php echo forum_escape($topic['author_username']); ?></strong></p>
              </div>
              <div class="forum-row-meta"><strong><?php echo (int) $topic['post_count']; ?></strong><span><?php echo forum_escape(forum_t('postów')); ?></span><small><?php echo forum_escape(forum_format_date($topic['last_post_at'])); ?></small></div>
            </article>
          <?php endforeach; ?>
        </div>
        <?php forum_ext_render_pagination($pagination, ['view' => 'category', 'id' => (int) $category['id']]); ?>
      <?php endif; ?>
    </section>

    <?php if ($currentUser): ?>
      <section class="panel forum-panel">
        <span class="eyebrow"><?php echo forum_escape(forum_t('Nowy temat')); ?></span>
        <h2><?php echo forum_escape(forum_t('Rozpocznij nową dyskusję')); ?></h2>
        <form class="forum-form" method="post" action="<?php echo forum_url(['view' => 'category', 'id' => (int) $category['id']]); ?>">
          <input type="hidden" name="action" value="create_topic">
          <input type="hidden" name="category_id" value="<?php echo (int) $category['id']; ?>">
          <input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
          <label for="topic-title"><?php echo forum_escape(forum_t('Tytuł tematu')); ?></label>
          <input id="topic-title" type="text" name="title" maxlength="140" required>
          <label for="topic-body"><?php echo forum_escape(forum_t('Pierwsza wiadomość')); ?></label>
          <?php forum_ext_render_editor_toolbar('topic-body'); ?>
          <textarea id="topic-body" name="body" minlength="10" maxlength="12000" required></textarea>
          <button class="button" type="submit"><?php echo forum_escape(forum_t('Dodaj temat')); ?></button>
        </form>
      </section>
    <?php endif; ?>
    <?php
    forum_ext_render_footer();
}

function forum_ext_render_search(string $query, array $results, array $pagination, ?array $flash): void
{
    $title = forum_t('Wyszukiwarka forum');
    $description = forum_t('Szukaj tematów i postów opublikowanych na forum.');
    forum_ext_render_header($title, $description);
    forum_ext_render_flash($flash);
    ?>
    <section class="panel forum-panel">
      <div class="forum-section-head">
        <div>
          <span class="eyebrow"><?php echo forum_escape(forum_t('Szukaj')); ?></span>
          <h2><?php echo forum_escape(forum_t('Wyszukiwarka forum')); ?></h2>
          <p class="lead forum-lead"><?php echo forum_escape(forum_t('Wpisz szukaną frazę, aby znaleźć tematy i posty na forum.')); ?></p>
        </div>
      </div>

      <form class="forum-form forum-search-form" method="get" action="<?php echo forum_url(); ?>">
        <input type="hidden" name="view" value="search">
        <label for="forum-search-query"><?php echo forum_escape(forum_t('Szukana fraza')); ?></label>
        <div class="forum-search-inline">
          <input id="forum-search-query" type="text" name="q" value="<?php echo forum_escape($query); ?>" minlength="2" maxlength="80">
          <button class="button" type="submit"><?php echo forum_escape(forum_t('Szukaj')); ?></button>
        </div>
      </form>
    </section>

    <?php if ($query === ''): ?>
      <section class="panel forum-panel">
        <div class="callout">
          <strong><?php echo forum_escape(forum_t('Wpisz frazę, aby rozpocząć wyszukiwanie.')); ?></strong>
          <p><?php echo forum_escape(forum_t('Wyniki obejmują tytuły tematów oraz treść postów.')); ?></p>
        </div>
      </section>
    <?php elseif (!forum_ext_search_query_is_valid($query)): ?>
      <section class="panel forum-panel">
        <div class="warning">
          <strong><?php echo forum_escape(forum_t('Fraza jest za krótka.')); ?></strong>
          <p><?php echo forum_escape(forum_t('Wpisz co najmniej 2 znaki, aby przeszukać forum.')); ?></p>
        </div>
      </section>
    <?php else: ?>
      <section class="panel forum-panel">
        <div class="forum-section-title">
          <div>
            <span class="eyebrow"><?php echo forum_escape(forum_t('Wyniki')); ?></span>
            <h2><?php echo forum_escape(forum_t('Wyniki wyszukiwania')); ?></h2>
            <p><?php echo forum_escape(forum_t('Znaleziono')); ?> <?php echo (int) ($pagination['total_items'] ?? 0); ?> <?php echo forum_escape(forum_t('wyników dla:')); ?> <strong><?php echo forum_escape($query); ?></strong></p>
          </div>
        </div>

        <?php if ($results === []): ?>
          <div class="callout">
            <strong><?php echo forum_escape(forum_t('Brak wyników.')); ?></strong>
            <p><?php echo forum_escape(forum_t('Spróbuj użyć krótszej albo innej frazy.')); ?></p>
          </div>
        <?php else: ?>
          <div class="forum-list forum-topic-list forum-search-results">
            <?php foreach ($results as $result): ?>
              <?php
                $isPost = (string) ($result['result_type'] ?? '') === forum_t('post');
                $url = $isPost
                    ? forum_url(['view' => 'topic', 'id' => (int) $result['topic_id'], 'page' => max(1, (int) ($result['post_page'] ?? 1))]) . '#post-' . (int) $result['post_id']
                    : forum_url(['view' => 'topic', 'id' => (int) $result['topic_id']]);
                $excerpt = $isPost
                    ? forum_ext_search_excerpt((string) ($result['matched_text'] ?? ''), $query, 180)
                    : forum_t('Tytuł tematu pasuje do szukanej frazy.');
              ?>
              <article class="forum-row">
                <div class="forum-row-main">
                  <h3><a href="<?php echo forum_escape($url); ?>"><?php echo forum_escape($result['topic_title']); ?></a></h3>
                  <p>
                    <span class="forum-badge"><?php echo $isPost ? forum_t('Post') : forum_t('Temat'); ?></span>
                    <?php echo forum_escape($result['category_name']); ?> <?php echo forum_escape(forum_t('· Autor:')); ?> <strong><?php echo forum_escape($result['author_username']); ?></strong>
                  </p>
                  <?php if ($excerpt !== ''): ?>
                    <p class="forum-search-excerpt"><?php echo forum_escape($excerpt); ?></p>
                  <?php endif; ?>
                </div>
                <div class="forum-row-meta">
                  <?php if ($isPost): ?>
                    <span><?php echo forum_escape(forum_t('post')); ?></span>
                  <?php else: ?>
                    <strong><?php echo (int) ($result['post_count'] ?? 0); ?></strong>
                    <span><?php echo forum_escape(forum_t('postów')); ?></span>
                  <?php endif; ?>
                  <small><?php echo forum_escape(forum_format_date((string) $result['result_at'])); ?></small>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
          <?php forum_ext_render_pagination($pagination, ['view' => 'search', 'q' => $query]); ?>
        <?php endif; ?>
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
          <span class="eyebrow"><?php echo forum_escape(forum_t('Temat')); ?></span>
          <h2><?php echo forum_escape($topic['title']); ?></h2>
          <p class="forum-meta-line"><?php echo forum_escape(forum_t('Dział:')); ?> <a href="<?php echo forum_url(['view' => 'category', 'id' => (int) $topic['category_id']]); ?>"><?php echo forum_escape($topic['category_name']); ?></a> <?php echo forum_escape(forum_t('· Autor:')); ?> <strong><?php echo forum_escape($topic['author_username']); ?></strong> <?php echo forum_escape(forum_t('· Założono:')); ?> <?php echo forum_escape(forum_format_date($topic['created_at'])); ?></p>
          <div class="forum-badge-row"><?php if ((int) $topic['is_pinned'] === 1): ?><span class="forum-badge"><?php echo forum_escape(forum_t('Przypięty')); ?></span><?php endif; ?><?php if ((int) $topic['is_locked'] === 1): ?><span class="forum-badge forum-badge-muted"><?php echo forum_escape(forum_t('Zamknięty')); ?></span><?php endif; ?></div>
        </div>
        <div class="forum-admin-tools">
          <a class="button-secondary" href="<?php echo forum_url(['view' => 'category', 'id' => (int) $topic['category_id']]); ?>"><?php echo forum_escape(forum_t('Wróć do działu')); ?></a>
          <?php if ($currentUser && forum_is_staff($currentUser)): ?>
            <form method="post" action="<?php echo forum_url(['view' => 'topic', 'id' => (int) $topic['id']]); ?>" class="forum-inline-form"><input type="hidden" name="action" value="toggle_topic_pin"><input type="hidden" name="topic_id" value="<?php echo (int) $topic['id']; ?>"><input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>"><button class="button-secondary" type="submit"><?php echo (int) $topic['is_pinned'] === 1 ? forum_t('Odepnij') : forum_t('Przypnij'); ?></button></form>
            <form method="post" action="<?php echo forum_url(['view' => 'topic', 'id' => (int) $topic['id']]); ?>" class="forum-inline-form"><input type="hidden" name="action" value="toggle_topic_lock"><input type="hidden" name="topic_id" value="<?php echo (int) $topic['id']; ?>"><input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>"><button class="button-secondary" type="submit"><?php echo (int) $topic['is_locked'] === 1 ? forum_t('Otwórz temat') : forum_t('Zamknij temat'); ?></button></form>
            <form method="post" action="<?php echo forum_url(['view' => 'topic', 'id' => (int) $topic['id']]); ?>" class="forum-inline-form" onsubmit="return confirm(<?php echo forum_escape(json_encode(forum_t('Usunąć cały temat?'), JSON_HEX_APOS | JSON_HEX_QUOT)); ?>);"><input type="hidden" name="action" value="delete_topic"><input type="hidden" name="topic_id" value="<?php echo (int) $topic['id']; ?>"><input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>"><button class="button-secondary forum-danger-button" type="submit"><?php echo forum_escape(forum_t('Usuń temat')); ?></button></form>
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
              <small><?php echo (int) $post['user_post_count']; ?> <?php echo forum_escape(forum_t('postów')); ?></small>
              <small><?php echo (int) $post['user_likes_received']; ?> <?php echo forum_escape(forum_t('lajków')); ?></small>
            </div>
            <div class="forum-post-body">
              <div class="forum-post-meta">
                <span><?php echo forum_escape(forum_format_date($post['created_at'])); ?></span>
                <?php if (!empty($post['updated_at'])): ?><small><?php echo forum_escape(forum_t('edytowano:')); ?> <?php echo forum_escape(forum_format_date($post['updated_at'])); ?></small><?php endif; ?>
              </div>
              <?php if ($editPost && (int) $editPost['id'] === (int) $post['id'] && $currentUser): ?>
                <form id="edit-post-form" class="forum-form" method="post" action="<?php echo forum_url(['view' => 'topic', 'id' => (int) $topic['id'], 'page' => (int) ($pagination['page'] ?? 1)]); ?>">
                  <input type="hidden" name="action" value="update_post">
                  <input type="hidden" name="post_id" value="<?php echo (int) $post['id']; ?>">
                  <input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
                  <?php if ((int) $post['is_topic_starter'] === 1): ?><label for="edit-topic-title"><?php echo forum_escape(forum_t('Tytuł tematu')); ?></label><input id="edit-topic-title" type="text" name="topic_title" value="<?php echo forum_escape($topic['title']); ?>" maxlength="140" required><?php endif; ?>
                  <label for="edit-post-body"><?php echo forum_escape(forum_t('Treść postu')); ?></label>
                  <?php forum_ext_render_editor_toolbar('edit-post-body'); ?>
                  <textarea id="edit-post-body" name="body" minlength="3" maxlength="12000" required><?php echo forum_escape($post['body']); ?></textarea>
                  <div class="forum-button-row"><button class="button" type="submit"><?php echo forum_escape(forum_t('Zapisz zmiany')); ?></button><a class="button-secondary" href="<?php echo forum_url(['view' => 'topic', 'id' => (int) $topic['id']]); ?>"><?php echo forum_escape(forum_t('Anuluj')); ?></a></div>
                </form>
              <?php else: ?>
                <div class="forum-post-content"><?php echo forum_render_text($post['body']); ?></div>
                <?php if (!empty($post['updated_at'])): ?>
                  <p class="forum-edit-note"><?php echo forum_escape(forum_t('Ostatnio edytowano przez:')); ?> <strong><?php echo forum_escape((string) ($post['edited_by_username'] ?? $post['username'] ?? forum_t('użytkownik'))); ?></strong>, <?php echo forum_escape(forum_format_date($post['updated_at'])); ?></p>
                <?php endif; ?>
              <?php endif; ?>
              <div class="forum-post-actions">
                <span class="forum-like-count"><?php echo (int) $post['like_count']; ?> <?php echo forum_escape(forum_t('lajków')); ?></span>
                <?php if ($currentUser): ?><form class="forum-inline-form" method="post" action="<?php echo forum_url(['view' => 'topic', 'id' => (int) $topic['id'], 'page' => (int) ($pagination['page'] ?? 1)]); ?>"><input type="hidden" name="action" value="like_post"><input type="hidden" name="post_id" value="<?php echo (int) $post['id']; ?>"><input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>"><button class="button-secondary forum-like-button<?php echo (int) $post['viewer_liked'] === 1 ? ' is-active' : ''; ?>" type="submit" title="<?php echo (int) $post['viewer_liked'] === 1 ? 'Cofnij lajka' : 'Polub ten post'; ?>"><?php forum_ext_render_like_icon((int) $post['viewer_liked'] === 1); ?><span class="visually-hidden"><?php echo (int) $post['viewer_liked'] === 1 ? 'Cofnij lajka' : forum_t('Lubię to'); ?></span></button></form><?php endif; ?>
                <?php if ($currentUser): ?><a class="button-secondary" href="<?php echo forum_url(['view' => 'topic', 'id' => (int) $topic['id'], 'page' => (int) ($pagination['page'] ?? 1), 'quote_post' => (int) $post['id']]); ?>#reply-body"><?php echo forum_escape(forum_t('Cytuj')); ?></a><?php endif; ?>
                <?php if ($currentUser && (int) $currentUser['id'] !== (int) $post['user_id']): ?><form class="forum-inline-form" method="post" action="<?php echo forum_url(['view' => 'topic', 'id' => (int) $topic['id'], 'page' => (int) ($pagination['page'] ?? 1)]); ?>"><input type="hidden" name="action" value="report_post"><input type="hidden" name="post_id" value="<?php echo (int) $post['id']; ?>"><input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>"><button class="button-secondary" type="submit"><?php echo forum_escape(forum_t('Zgłoś')); ?></button></form><?php endif; ?>
                <?php if ($currentUser && forum_ext_user_can_edit_post($currentUser, $post)): ?><a class="button-secondary" href="<?php echo forum_url(['view' => 'topic', 'id' => (int) $topic['id'], 'page' => (int) ($pagination['page'] ?? 1), 'edit_post' => (int) $post['id']]); ?>#edit-post-form"><?php echo forum_escape(forum_t('Edytuj')); ?></a><?php endif; ?>
                <?php if ($currentUser && (int) $currentUser['id'] !== (int) $post['user_id']): ?><a class="button-secondary" href="<?php echo forum_url(['view' => 'compose', 'to' => $post['username']]); ?>"><?php echo forum_escape(forum_t('Napisz wiadomość')); ?></a><?php endif; ?>
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
    forum_ext_render_header(forum_t('Moje konto'), forum_t('Ustawienia profilu, avatar, hasło i statystyki aktywności.'));
    forum_ext_render_flash($flash);
    ?>
    <section class="panel forum-panel">
      <div class="forum-profile-grid">
        <article class="forum-profile-card">
          <?php forum_ext_render_avatar($profile, 'forum-avatar forum-avatar-large'); ?>
          <h2><?php echo forum_escape($profile['username']); ?></h2>
          <p><?php echo forum_escape($profile['email']); ?></p>
          <div class="forum-stats-grid forum-profile-stats">
            <article class="forum-stat-box"><strong><?php echo (int) $profile['topic_count']; ?></strong><span><?php echo forum_escape(forum_t('tematów')); ?></span></article>
            <article class="forum-stat-box"><strong><?php echo (int) $profile['post_count']; ?></strong><span><?php echo forum_escape(forum_t('postów')); ?></span></article>
            <article class="forum-stat-box"><strong><?php echo (int) $profile['likes_received']; ?></strong><span><?php echo forum_escape(forum_t('otrzymanych lajków')); ?></span></article>
            <article class="forum-stat-box"><strong><?php echo (int) $profile['likes_given']; ?></strong><span><?php echo forum_escape(forum_t('danych lajków')); ?></span></article>
          </div>
        </article>
        <div class="forum-stack">
          <article class="forum-surface-card">
            <div class="forum-post-body">
              <span class="eyebrow"><?php echo forum_escape(forum_t('Avatar')); ?></span>
              <form class="forum-form" method="post" action="<?php echo forum_url(['view' => 'account']); ?>" enctype="multipart/form-data">
                <input type="hidden" name="action" value="upload_avatar">
                <input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
                <label for="avatar-file"><?php echo forum_escape(forum_t('Nowy avatar')); ?></label>
                <input id="avatar-file" type="file" name="avatar" accept=".jpg,.jpeg,.png,.webp,.gif,image/jpeg,image/png,image/webp,image/gif" data-forum-image="avatar" data-forum-size="160" data-forum-fit="cover" required>
                <button class="button" type="submit"><?php echo forum_escape(forum_t('Wyślij avatar')); ?></button>
              </form>
              <?php if (!empty($profile['avatar_filename'])): ?>
                <form class="forum-inline-form" method="post" action="<?php echo forum_url(['view' => 'account']); ?>">
                  <input type="hidden" name="action" value="remove_avatar">
                  <input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
                  <button class="button-secondary" type="submit"><?php echo forum_escape(forum_t('Usuń avatar')); ?></button>
                </form>
              <?php endif; ?>
            </div>
          </article>
          <article class="forum-surface-card"><div class="forum-post-body"><span class="eyebrow"><?php echo forum_escape(forum_t('Hasło')); ?></span><form class="forum-form" method="post" action="<?php echo forum_url(['view' => 'account']); ?>"><input type="hidden" name="action" value="change_password"><input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>"><label for="current-password"><?php echo forum_escape(forum_t('Aktualne hasło')); ?></label><input id="current-password" type="password" name="current_password" required><label for="new-password"><?php echo forum_escape(forum_t('Nowe hasło')); ?></label><input id="new-password" type="password" name="new_password" minlength="10" required><button class="button" type="submit"><?php echo forum_escape(forum_t('Zmień hasło')); ?></button></form></div></article>
        </div>
      </div>
    </section>

    <section class="panel forum-panel">
      <div class="forum-grid-two">
        <div class="forum-surface-card"><span class="eyebrow"><?php echo forum_escape(forum_t('Moje tematy')); ?></span><h2><?php echo forum_escape(forum_t('Ostatnio założone')); ?></h2><div class="forum-list forum-list-compact"><?php foreach ($recentTopics as $topic): ?><article class="forum-row forum-row-tight"><div class="forum-row-main"><h3><a href="<?php echo forum_url(['view' => 'topic', 'id' => (int) $topic['id']]); ?>"><?php echo forum_escape($topic['title']); ?></a></h3><p><?php echo forum_escape($topic['category_name']); ?></p></div></article><?php endforeach; ?></div></div>
        <div class="forum-surface-card"><span class="eyebrow"><?php echo forum_escape(forum_t('Moje posty')); ?></span><h2><?php echo forum_escape(forum_t('Ostatnie odpowiedzi')); ?></h2><div class="forum-list forum-list-compact"><?php foreach ($recentPosts as $post): ?><article class="forum-row forum-row-tight"><div class="forum-row-main"><h3><a href="<?php echo forum_url(['view' => 'topic', 'id' => (int) $post['topic_id']]); ?>#post-<?php echo (int) $post['id']; ?>"><?php echo forum_escape($post['topic_title']); ?></a></h3><p><?php echo forum_escape(forum_ext_excerpt($post['body'], 110)); ?></p></div></article><?php endforeach; ?></div></div>
      </div>
    </section>
    <?php
    forum_ext_render_footer();
}

function forum_ext_render_auth(string $view, ?array $flash, ?array $resetRecord = null): void
{
    $titles = [
        'login' => [forum_t('Logowanie'), forum_t('Zaloguj sie do ForumForgeCMS.')],
        'register' => [forum_t('Rejestracja'), forum_t('Załóż konto na ForumForgeCMS.')],
        'forgot-password' => [forum_t('Reset hasła'), forum_t('Przywróć dostęp do forum przez link wysłany e-mailem.')],
        'reset-password' => [forum_t('Nowe hasło'), forum_t('Ustaw nowe hasło do ForumForgeCMS.')],
    ];

    [$title, $description] = $titles[$view] ?? [forum_t('Forum'), 'ForumForgeCMS'];
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
          <label for="login"><?php echo forum_escape(forum_t('Login lub e-mail')); ?></label><input id="login" type="text" name="login" required>
          <label for="login-password"><?php echo forum_escape(forum_t('Hasło')); ?></label><input id="login-password" type="password" name="password" required>
          <button class="button" type="submit"><?php echo forum_escape(forum_t('Zaloguj się')); ?></button>
        </form>
        <p class="support-help"><?php echo forum_escape(forum_t('Nie pamiętasz hasła?')); ?> <a href="<?php echo forum_url(['view' => 'forgot-password']); ?>"><?php echo forum_escape(forum_t('Wyślij link do resetu')); ?></a>.</p>
      <?php elseif ($view === 'register'): ?>
        <?php if (!forum_ext_registrations_enabled()): ?><div class="warning"><?php echo forum_escape(forum_t('Rejestracja nowych kont jest chwilowo wyłączona.')); ?></div><?php else: ?>
        <form class="forum-form" method="post" action="<?php echo forum_url(['view' => 'register']); ?>">
          <input type="hidden" name="action" value="register"><input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
          <label for="register-username"><?php echo forum_escape(forum_t('Nazwa użytkownika')); ?></label><input id="register-username" type="text" name="username" maxlength="40" required>
          <label for="register-email">E-mail</label><input id="register-email" type="email" name="email" maxlength="190" required>
          <label for="register-password"><?php echo forum_escape(forum_t('Hasło')); ?></label><input id="register-password" type="password" name="password" minlength="10" required>
          <?php if (forum_ext_setting('antispam_question_enabled') === '1'): ?>
            <?php $challenge = forum_antispam_challenge(); ?>
            <input type="hidden" name="antispam_token" value="<?php echo forum_escape($challenge['token']); ?>">
            <label for="antispam-answer"><?php echo forum_escape(forum_t('Kontrola antyspamowa')); ?>: <?php echo forum_escape($challenge['question']); ?></label>
            <input id="antispam-answer" type="text" name="antispam_answer" inputmode="numeric" autocomplete="off" pattern="[0-9]{1,2}" maxlength="2" required>
          <?php endif; ?>
          <label class="checkbox-row" for="register-accept-terms"><input id="register-accept-terms" type="checkbox" name="accept_terms" value="1" required><span><?php echo forum_escape(forum_t('Akceptuję')); ?> <a href="regulamin.html" target="_blank" rel="noopener noreferrer"><?php echo forum_escape(forum_t('Regulamin strony i forum')); ?></a>.</span></label>
          <button class="button" type="submit"><?php echo forum_escape(forum_t('Załóż konto')); ?></button>
        </form>
        <?php endif; ?>
      <?php elseif ($view === 'forgot-password'): ?>
        <form class="forum-form" method="post" action="<?php echo forum_url(['view' => 'forgot-password']); ?>">
          <input type="hidden" name="action" value="request_password_reset"><input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
          <label for="reset-login"><?php echo forum_escape(forum_t('Login lub e-mail')); ?></label><input id="reset-login" type="text" name="login" required>
          <button class="button" type="submit"><?php echo forum_escape(forum_t('Wyślij link resetujący')); ?></button>
        </form>
      <?php else: ?>
        <?php if (!$resetRecord): ?><div class="warning"><?php echo forum_escape(forum_t('Link do resetu hasła jest nieprawidłowy albo wygasł.')); ?></div><?php else: ?>
        <form class="forum-form" method="post" action="<?php echo forum_url(['view' => 'reset-password', 'selector' => $_GET['selector'] ?? '', 'token' => $_GET['token'] ?? '']); ?>">
          <input type="hidden" name="action" value="reset_password"><input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
          <input type="hidden" name="selector" value="<?php echo forum_escape((string) ($_GET['selector'] ?? '')); ?>">
          <input type="hidden" name="token" value="<?php echo forum_escape((string) ($_GET['token'] ?? '')); ?>">
          <label for="new-reset-password"><?php echo forum_escape(forum_t('Nowe hasło')); ?></label><input id="new-reset-password" type="password" name="new_password" minlength="10" required>
          <button class="button" type="submit"><?php echo forum_escape(forum_t('Ustaw nowe hasło')); ?></button>
        </form>
        <?php endif; ?>
      <?php endif; ?>
    </section>
    <?php
    forum_ext_render_footer();
}

function forum_ext_render_messages(array $inbox, array $outbox, string $box, array $pagination, ?array $flash): void
{
    forum_ext_render_header(forum_t('Wiadomości prywatne'), forum_t('Rozmowy z innymi użytkownikami ForumForgeCMS.'));
    forum_ext_render_flash($flash);
    $items = $box === 'outbox' ? $outbox : $inbox;
    ?>
    <section class="panel forum-panel">
      <div class="forum-section-head">
        <div><span class="eyebrow"><?php echo forum_escape(forum_t('Wiadomości')); ?></span><h2><?php echo forum_escape(forum_t('Skrzynka prywatnych wiadomości')); ?></h2></div>
        <div class="forum-button-row"><a class="button-secondary" href="<?php echo forum_url(['view' => 'messages', 'box' => 'inbox']); ?>"><?php echo forum_escape(forum_t('Odebrane')); ?></a><a class="button-secondary" href="<?php echo forum_url(['view' => 'messages', 'box' => 'outbox']); ?>"><?php echo forum_escape(forum_t('Wysłane')); ?></a><a class="button" href="<?php echo forum_url(['view' => 'compose']); ?>"><?php echo forum_escape(forum_t('Nowa wiadomość')); ?></a></div>
      </div>
      <?php if ($items === []): ?>
        <div class="callout"><strong><?php echo forum_escape(forum_t('Ta skrzynka jest jeszcze pusta.')); ?></strong><p><?php echo forum_escape(forum_t('Kiedy zaczniesz prywatne rozmowy z innymi użytkownikami, wiadomości pojawią się właśnie tutaj.')); ?></p></div>
      <?php else: ?>
        <div class="forum-list">
          <?php foreach ($items as $message): ?>
            <article class="forum-user-row">
              <?php forum_ext_render_avatar(['id' => $box === 'outbox' ? $message['recipient_id'] : $message['sender_id'], 'username' => $box === 'outbox' ? $message['recipient_username'] : $message['sender_username'], 'avatar_updated_at' => $box === 'outbox' ? $message['recipient_avatar_updated_at'] : $message['sender_avatar_updated_at']]); ?>
              <div class="forum-user-row-main">
                <h3><a href="<?php echo forum_url(['view' => 'message', 'id' => (int) $message['id']]); ?>"><?php echo forum_escape($message['subject']); ?></a></h3>
                <p><?php echo forum_escape($box === 'outbox' ? forum_t('Do:') . ' ' . $message['recipient_username'] : forum_t('Od:') . ' ' . $message['sender_username']); ?> · <?php echo forum_escape(forum_format_date($message['created_at'])); ?></p>
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
    forum_ext_render_header($message['subject'], forum_t('Treść prywatnej wiadomości.'));
    forum_ext_render_flash($flash);
    ?>
    <section class="panel forum-panel forum-narrow-panel">
      <span class="eyebrow"><?php echo forum_escape(forum_t('Prywatna wiadomość')); ?></span>
      <h2><?php echo forum_escape($message['subject']); ?></h2>
      <p class="forum-meta-line"><?php echo forum_escape(forum_t('Od:')); ?> <strong><?php echo forum_escape($message['sender_username']); ?></strong> <?php echo forum_escape(forum_t('· Do:')); ?> <strong><?php echo forum_escape($message['recipient_username']); ?></strong> · <?php echo forum_escape(forum_format_date($message['created_at'])); ?></p>
      <div class="forum-post-content"><?php echo forum_render_text($message['body']); ?></div>
      <div class="forum-button-row"><a class="button" href="<?php echo forum_url(['view' => 'compose', 'to' => $message['sender_username'], 'subject' => 'Re: ' . $message['subject']]); ?>"><?php echo forum_escape(forum_t('Odpowiedz')); ?></a><a class="button-secondary" href="<?php echo forum_url(['view' => 'messages']); ?>"><?php echo forum_escape(forum_t('Wróć do skrzynki')); ?></a></div>
    </section>
    <?php
    forum_ext_render_footer();
}

function forum_ext_render_compose(?array $flash, string $to = '', string $subject = '', string $body = ''): void
{
    forum_ext_render_header(forum_t('Nowa wiadomość'), forum_t('Napisz prywatną wiadomość do innego użytkownika forum.'));
    forum_ext_render_flash($flash);
    ?>
    <section class="panel forum-panel forum-narrow-panel">
      <span class="eyebrow"><?php echo forum_escape(forum_t('Nowa wiadomość')); ?></span>
      <h2><?php echo forum_escape(forum_t('Napisz prywatnie do użytkownika')); ?></h2>
      <form class="forum-form" method="post" action="<?php echo forum_url(['view' => 'compose']); ?>">
        <input type="hidden" name="action" value="send_message"><input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
        <label for="message-to"><?php echo forum_escape(forum_t('Odbiorca')); ?></label><input id="message-to" type="text" name="recipient" value="<?php echo forum_escape($to); ?>" required>
        <label for="message-subject"><?php echo forum_escape(forum_t('Temat')); ?></label><input id="message-subject" type="text" name="subject" maxlength="180" value="<?php echo forum_escape($subject); ?>" required>
        <label for="message-body"><?php echo forum_escape(forum_t('Treść')); ?></label><textarea id="message-body" name="body" minlength="5" maxlength="12000" required><?php echo forum_escape($body); ?></textarea>
        <button class="button" type="submit"><?php echo forum_escape(forum_t('Wyślij wiadomość')); ?></button>
      </form>
    </section>
    <?php
    forum_ext_render_footer();
}

function forum_ext_render_user(array $profile, array $recentTopics, array $recentPosts, ?array $currentUser, ?array $flash): void
{
    forum_ext_render_header($profile['username'], forum_t('Profil użytkownika ForumForgeCMS.'));
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
            <article class="forum-stat-box"><strong><?php echo (int) $profile['topic_count']; ?></strong><span><?php echo forum_escape(forum_t('tematów')); ?></span></article>
            <article class="forum-stat-box"><strong><?php echo (int) $profile['post_count']; ?></strong><span><?php echo forum_escape(forum_t('postów')); ?></span></article>
            <article class="forum-stat-box"><strong><?php echo (int) $profile['likes_received']; ?></strong><span><?php echo forum_escape(forum_t('lajków')); ?></span></article>
            <article class="forum-stat-box"><strong><?php echo forum_escape(forum_format_date($profile['created_at'])); ?></strong><span><?php echo forum_escape(forum_t('na forum od')); ?></span></article>
          </div>
          <?php if ($currentUser && (int) $currentUser['id'] !== (int) $profile['id']): ?><a class="button" href="<?php echo forum_url(['view' => 'compose', 'to' => $profile['username']]); ?>"><?php echo forum_escape(forum_t('Napisz wiadomość')); ?></a><?php endif; ?>
        </article>
        <div class="forum-stack">
          <article class="forum-surface-card"><div class="forum-post-body"><span class="eyebrow"><?php echo forum_escape(forum_t('Ostatnie tematy')); ?></span><div class="forum-list forum-list-compact"><?php foreach ($recentTopics as $topic): ?><article class="forum-row forum-row-tight"><div class="forum-row-main"><h3><a href="<?php echo forum_url(['view' => 'topic', 'id' => (int) $topic['id']]); ?>"><?php echo forum_escape($topic['title']); ?></a></h3><p><?php echo forum_escape($topic['category_name']); ?></p></div></article><?php endforeach; ?></div></div></article>
          <article class="forum-surface-card"><div class="forum-post-body"><span class="eyebrow"><?php echo forum_escape(forum_t('Ostatnie posty')); ?></span><div class="forum-list forum-list-compact"><?php foreach ($recentPosts as $post): ?><article class="forum-row forum-row-tight"><div class="forum-row-main"><h3><a href="<?php echo forum_url(['view' => 'topic', 'id' => (int) $post['topic_id']]); ?>#post-<?php echo (int) $post['id']; ?>"><?php echo forum_escape($post['topic_title']); ?></a></h3><p><?php echo forum_escape(forum_ext_excerpt($post['body'], 120)); ?></p></div></article><?php endforeach; ?></div></div></article>
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
      <span class="eyebrow"><?php echo forum_escape(forum_t('Moderacja')); ?></span>
      <h2><?php echo forum_escape(forum_t('Zgłoszenia postów')); ?></h2>
      <?php if ($reports === []): ?>
        <p class="forum-admin-note"><?php echo forum_escape(forum_t('Nie ma aktywnych zgłoszeń do sprawdzenia.')); ?></p>
      <?php else: ?>
        <div class="forum-admin-list">
          <?php foreach ($reports as $report): ?>
            <article class="forum-admin-card forum-report-card">
              <div class="forum-section-head">
                <div>
                  <span class="eyebrow"><?php echo forum_escape($report['category_name']); ?></span>
                  <h3><?php echo forum_escape($report['topic_title']); ?></h3>
                  <p class="forum-meta-line"><?php echo forum_escape(forum_t('Zgłosił:')); ?> <strong><?php echo forum_escape($report['reporter_username']); ?></strong> <?php echo forum_escape(forum_t('· Autor posta:')); ?> <strong><?php echo forum_escape($report['post_author_username']); ?></strong> · <?php echo forum_escape(forum_format_date($report['created_at'])); ?></p>
                </div>
                <div class="forum-button-row">
                  <a class="button-secondary" href="<?php echo forum_url(['view' => 'topic', 'id' => (int) $report['topic_id']]); ?>#post-<?php echo (int) $report['post_id']; ?>"><?php echo forum_escape(forum_t('Przejdź do posta')); ?></a>
                  <form class="forum-inline-form" method="post" action="<?php echo forum_url(['view' => $returnView, 'section' => 'reports']); ?>">
                    <input type="hidden" name="action" value="close_post_report">
                    <input type="hidden" name="report_id" value="<?php echo (int) $report['id']; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
                    <button class="button" type="submit"><?php echo forum_escape(forum_t('Zamknij spór')); ?></button>
                  </form>
                </div>
              </div>
              <div class="forum-post-content forum-report-excerpt"><?php echo forum_render_text(forum_ext_excerpt((string) $report['post_body'], 420)); ?></div>
              <?php if ((string) ($report['reason'] ?? '') !== ''): ?>
                <p class="forum-admin-note"><strong><?php echo forum_escape(forum_t('Powód:')); ?></strong> <?php echo forum_escape($report['reason']); ?></p>
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
    forum_ext_render_header(forum_t('Panel moderatora'), forum_t('Zgłoszenia postów i narzędzia porządkowe forum.'));
    forum_ext_render_flash($flash);
    forum_ext_render_post_reports_list($reports, 'moderation');
    forum_ext_render_footer();
}

function forum_ext_render_admin(array $summary, array $categories, array $topUsers, ?array $flash): void
{
    $settings = forum_ext_settings();
    $summaryLabels = [
        'users' => forum_t('użytkowników'),
        'categories' => forum_t('działów'),
        'topics' => forum_t('tematów'),
        'posts' => forum_t('postów'),
        'likes' => forum_t('lajków'),
        'messages' => forum_t('wiadomości'),
    ];
    forum_ext_render_header(forum_t('Panel administratora'), forum_t('Zarządzanie ustawieniami, działami i aktywnością forum.'));
    forum_ext_render_flash($flash);
    ?>
    <section class="panel forum-panel">
      <span class="eyebrow"><?php echo forum_escape(forum_t('Administrator')); ?></span>
      <h2><?php echo forum_escape(forum_t('Podsumowanie forum')); ?></h2>
      <div class="forum-stats-grid forum-stats-grid-wide">
        <?php foreach ($summary as $label => $value): ?><article class="forum-stat-box"><strong><?php echo (int) $value; ?></strong><span><?php echo forum_escape($summaryLabels[$label] ?? $label); ?></span></article><?php endforeach; ?>
      </div>
    </section>
    <section class="panel forum-panel">
      <span class="eyebrow"><?php echo forum_escape(forum_t('Ustawienia')); ?></span>
      <h2><?php echo forum_escape(forum_t('Widok forum i rejestracja')); ?></h2>
      <form class="forum-form" method="post" action="<?php echo forum_url(['view' => 'admin']); ?>" enctype="multipart/form-data">
        <input type="hidden" name="action" value="update_settings"><input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
        <label for="brand-name"><?php echo forum_escape(forum_t('Nazwa forum')); ?></label><input id="brand-name" type="text" name="brand_name" value="<?php echo forum_escape($settings['brand_name']); ?>" maxlength="80" required>
        <label for="brand-tagline"><?php echo forum_escape(forum_t('Krótki opis forum')); ?></label><input id="brand-tagline" type="text" name="brand_tagline" value="<?php echo forum_escape($settings['brand_tagline']); ?>" maxlength="160" required>
        <label for="brand-logo"><?php echo forum_escape(forum_t('Własne logo forum')); ?></label><input id="brand-logo" type="file" name="brand_logo" accept=".jpg,.jpeg,.png,.webp,.gif,image/jpeg,image/png,image/webp,image/gif" data-forum-image="logo" data-forum-size="192" data-forum-fit="contain">
        <label for="graphic-style"><?php echo forum_escape(forum_t('Styl graficzny forum')); ?></label><select id="graphic-style" name="graphic_style"><?php foreach (forum_ext_graphic_styles() as $styleKey => $_styleLabel): ?><option value="<?php echo forum_escape($styleKey); ?>" <?php echo ($settings['graphic_style'] ?? 'classic') === $styleKey ? 'selected' : ''; ?>><?php echo forum_escape(forum_ext_graphic_style_label($styleKey)); ?></option><?php endforeach; ?></select>
        <label for="home-intro-title"><?php echo forum_escape(forum_t('Tytuł forum')); ?></label><input id="home-intro-title" type="text" name="home_intro_title" value="<?php echo forum_escape($settings['home_intro_title']); ?>" maxlength="120" required>
        <label for="home-intro-text"><?php echo forum_escape(forum_t('Opis forum')); ?></label><textarea id="home-intro-text" name="home_intro_text" maxlength="500" required><?php echo forum_escape($settings['home_intro_text']); ?></textarea>
        <label class="checkbox-row"><input type="checkbox" name="allow_registrations" value="1" <?php echo $settings['allow_registrations'] === '1' ? 'checked' : ''; ?>><span><?php echo forum_escape(forum_t('Pozwól użytkownikom zakładać nowe konta')); ?></span></label>
        <button class="button" type="submit"><?php echo forum_escape(forum_t('Zapisz ustawienia')); ?></button>
      </form>
    </section>
    <section class="panel forum-panel"><span class="eyebrow"><?php echo forum_escape(forum_t('Najaktywniejsi')); ?></span><h2><?php echo forum_escape(forum_t('Użytkownicy z największą aktywnością')); ?></h2><div class="forum-list forum-list-compact"><?php foreach ($topUsers as $user): ?><article class="forum-user-row"><?php forum_ext_render_avatar($user); ?><div class="forum-user-row-main"><h3><a href="<?php echo forum_url(['view' => 'user', 'id' => (int) $user['id']]); ?>"><?php echo forum_escape($user['username']); ?></a></h3><p><?php echo (int) $user['post_count']; ?> <?php echo forum_escape(forum_t('postów ·')); ?> <?php echo (int) $user['likes_received']; ?> <?php echo forum_escape(forum_t('lajków')); ?></p></div></article><?php endforeach; ?></div></section>
    <section class="panel forum-panel"><span class="eyebrow"><?php echo forum_escape(forum_t('Działy')); ?></span><h2><?php echo forum_escape(forum_t('Zarządzaj działami')); ?></h2><div class="forum-admin-list"><?php foreach ($categories as $category): ?><article class="forum-admin-card"><form class="forum-form" method="post" action="<?php echo forum_url(['view' => 'admin']); ?>"><input type="hidden" name="action" value="update_category"><input type="hidden" name="category_id" value="<?php echo (int) $category['id']; ?>"><input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>"><label><?php echo forum_escape(forum_t('Nazwa działu')); ?></label><input type="text" name="name" value="<?php echo forum_escape($category['name']); ?>" maxlength="80" required><label><?php echo forum_escape(forum_t('Opis działu')); ?></label><textarea name="description" maxlength="260" required><?php echo forum_escape($category['description']); ?></textarea><button class="button" type="submit"><?php echo forum_escape(forum_t('Zapisz zmiany')); ?></button></form><div class="forum-button-row"><form class="forum-inline-form" method="post" action="<?php echo forum_url(['view' => 'admin']); ?>"><input type="hidden" name="action" value="move_category"><input type="hidden" name="direction" value="up"><input type="hidden" name="category_id" value="<?php echo (int) $category['id']; ?>"><input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>"><button class="button-secondary" type="submit"><?php echo forum_escape(forum_t('Wyżej')); ?></button></form><form class="forum-inline-form" method="post" action="<?php echo forum_url(['view' => 'admin']); ?>"><input type="hidden" name="action" value="move_category"><input type="hidden" name="direction" value="down"><input type="hidden" name="category_id" value="<?php echo (int) $category['id']; ?>"><input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>"><button class="button-secondary" type="submit"><?php echo forum_escape(forum_t('Niżej')); ?></button></form></div></article><?php endforeach; ?></div></section>
    <section class="panel forum-panel forum-narrow-panel"><span class="eyebrow"><?php echo forum_escape(forum_t('Nowy dział')); ?></span><h2><?php echo forum_escape(forum_t('Dodaj dział forum')); ?></h2><form class="forum-form" method="post" action="<?php echo forum_url(['view' => 'admin']); ?>"><input type="hidden" name="action" value="create_category"><input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>"><label for="category-name"><?php echo forum_escape(forum_t('Nazwa działu')); ?></label><input id="category-name" type="text" name="name" maxlength="80" required><label for="category-description"><?php echo forum_escape(forum_t('Opis działu')); ?></label><textarea id="category-description" name="description" maxlength="260" required></textarea><button class="button" type="submit"><?php echo forum_escape(forum_t('Dodaj dział')); ?></button></form></section>
    <?php
    forum_ext_render_footer();
}

function forum_ext_render_admin_panel(array $summary, array $categories, array $topUsers, array $users, array $userPagination, ?array $flash, string $activeSection = 'settings', int $selectedUserId = 0): void
{
    $settings = forum_ext_settings();
    $forumSections = forum_fetch_sections_with_categories();
    $flatForumSections = forum_fetch_forum_sections();
    $adminSections = [
        'settings' => forum_t('Widok forum i rejestracja'),
        'basic-settings' => forum_t('Ustawienia podstawowe'),
        'antispam' => forum_t('Ochrona przed spamem'),
        'backup' => forum_t('Kopia zapasowa i przywracanie forum'),
        'reports' => forum_t('Zgłoszenia postów'),
        'users' => forum_t('Zarządzaj użytkownikami i moderatorami'),
        'top-users' => forum_t('Użytkownicy z największą aktywnością'),
        'forum-sections' => forum_t('Zarządzaj kategoriami'),
        'create-forum-section' => forum_t('Dodaj kategorię'),
        'categories' => forum_t('Zarządzaj działami'),
        'create-category' => forum_t('Dodaj dział forum'),
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

    forum_ext_render_header(forum_t('Panel administratora'), forum_t('Zarządzanie ustawieniami, działami, użytkownikami i aktywnością forum.'));
    forum_ext_render_flash($flash);
    ?>
    <div class="forum-admin-layout">
      <aside class="forum-admin-sidebar" aria-label="<?php echo forum_escape(forum_t('Menu panelu administratora')); ?>">
        <span class="eyebrow"><?php echo forum_escape(forum_t('Panel admina')); ?></span>
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
        <?php if ($activeSection === 'antispam'): ?>
          <?php forum_antispam_render_settings(); ?>
        <?php endif; ?>
        <?php if ($activeSection === 'settings'): ?>
        <section id="admin-forum-settings" class="panel forum-panel">
          <span class="eyebrow"><?php echo forum_escape(forum_t('Ustawienia')); ?></span>
          <h2><?php echo forum_escape(forum_t('Widok forum i rejestracja')); ?></h2>
          <form class="forum-form" method="post" action="<?php echo forum_url(['view' => 'admin', 'section' => 'settings']); ?>" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update_settings">
            <input type="hidden" name="return_section" value="settings">
            <input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
            <input type="hidden" name="brand_name" value="<?php echo forum_escape($settings['brand_name']); ?>">
            <input type="hidden" name="brand_tagline" value="<?php echo forum_escape($settings['brand_tagline']); ?>">
            <input type="hidden" name="forum_language" value="<?php echo forum_escape($settings['forum_language'] ?? 'pl'); ?>">
            <input type="hidden" name="home_intro_title" value="<?php echo forum_escape($settings['home_intro_title']); ?>">
            <input type="hidden" name="home_intro_text" value="<?php echo forum_escape($settings['home_intro_text']); ?>">
            <label for="brand-logo"><?php echo forum_escape(forum_t('Własne logo forum')); ?></label>
            <input id="brand-logo" type="file" name="brand_logo" accept=".jpg,.jpeg,.png,.webp,.gif,image/jpeg,image/png,image/webp,image/gif" data-forum-image="logo" data-forum-size="192" data-forum-fit="contain">
            <label for="graphic-style"><?php echo forum_escape(forum_t('Styl graficzny forum')); ?></label>
            <select id="graphic-style" name="graphic_style">
              <?php foreach (forum_ext_graphic_styles() as $styleKey => $_styleLabel): ?>
                <option value="<?php echo forum_escape($styleKey); ?>" <?php echo ($settings['graphic_style'] ?? 'classic') === $styleKey ? 'selected' : ''; ?>><?php echo forum_escape(forum_ext_graphic_style_label($styleKey)); ?></option>
              <?php endforeach; ?>
            </select>
            <label class="checkbox-row">
              <input type="checkbox" name="allow_registrations" value="1" <?php echo $settings['allow_registrations'] === '1' ? 'checked' : ''; ?>>
              <span><?php echo forum_escape(forum_t('Pozwól użytkownikom zakładać nowe konta')); ?></span>
            </label>
            <button class="button" type="submit"><?php echo forum_escape(forum_t('Zapisz ustawienia')); ?></button>
          </form>
        </section>
        <?php endif; ?>

        <?php if ($activeSection === 'basic-settings'): ?>
        <section id="admin-basic-settings" class="panel forum-panel">
          <span class="eyebrow"><?php echo forum_escape(forum_t('Podstawowe')); ?></span>
          <h2><?php echo forum_escape(forum_t('Ustawienia podstawowe')); ?></h2>
          <form class="forum-form" method="post" action="<?php echo forum_url(['view' => 'admin', 'section' => 'basic-settings']); ?>">
            <input type="hidden" name="action" value="update_settings">
            <input type="hidden" name="return_section" value="basic-settings">
            <input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
            <input type="hidden" name="graphic_style" value="<?php echo forum_escape($settings['graphic_style']); ?>">
            <?php if ($settings['allow_registrations'] === '1'): ?>
              <input type="hidden" name="allow_registrations" value="1">
            <?php endif; ?>
            <label for="basic-forum-language"><?php echo forum_escape(forum_t('Język forum')); ?></label>
            <select id="basic-forum-language" name="forum_language">
              <?php foreach (forum_ext_languages() as $languageKey => $languageLabel): ?>
                <option value="<?php echo forum_escape($languageKey); ?>" <?php echo ($settings['forum_language'] ?? 'pl') === $languageKey ? 'selected' : ''; ?>><?php echo forum_escape($languageLabel); ?></option>
              <?php endforeach; ?>
            </select>
            <label for="basic-brand-name"><?php echo forum_escape(forum_t('Nazwa forum')); ?></label>
            <input id="basic-brand-name" type="text" name="brand_name" value="<?php echo forum_escape($settings['brand_name']); ?>" maxlength="80" required>
            <label for="basic-brand-tagline"><?php echo forum_escape(forum_t('Krótki opis forum')); ?></label>
            <input id="basic-brand-tagline" type="text" name="brand_tagline" value="<?php echo forum_escape($settings['brand_tagline']); ?>" maxlength="160" required>
            <label for="basic-home-intro-title"><?php echo forum_escape(forum_t('Tytuł forum')); ?></label>
            <input id="basic-home-intro-title" type="text" name="home_intro_title" value="<?php echo forum_escape($settings['home_intro_title']); ?>" maxlength="120" required>
            <label for="basic-home-intro-text"><?php echo forum_escape(forum_t('Opis forum')); ?></label>
            <textarea id="basic-home-intro-text" name="home_intro_text" maxlength="500" required><?php echo forum_escape($settings['home_intro_text']); ?></textarea>
            <button class="button" type="submit"><?php echo forum_escape(forum_t('Zapisz ustawienia podstawowe')); ?></button>
          </form>
        </section>
        <?php endif; ?>

        <?php if ($activeSection === 'backup'): ?>
        <section id="admin-backup" class="panel forum-panel">
          <span class="eyebrow"><?php echo forum_escape(forum_t('Kopia danych')); ?></span>
          <h2><?php echo forum_escape(forum_t('Kopia zapasowa i przywracanie forum')); ?></h2>
          <p class="forum-admin-note"><?php echo forum_escape(forum_t('Pobierz komplet danych forum jako ZIP: bazę SQLite, avatary użytkowników oraz własne logo forum. Taki plik możesz później wgrać na innym hostingu i przywrócić forum z panelu administratora.')); ?></p>

          <div class="forum-admin-list">
            <article class="forum-admin-card">
              <span class="eyebrow"><?php echo forum_escape(forum_t('Pobieranie')); ?></span>
              <h3><?php echo forum_escape(forum_t('Pobierz kopię zapasową')); ?></h3>
              <p class="forum-admin-note"><?php echo forum_escape(forum_t('Archiwum zawiera')); ?> <code>forum-data/forum.sqlite</code><?php echo forum_escape(forum_t(', katalog')); ?> <code>forum-data/avatars</code> <?php echo forum_escape(forum_t('oraz katalog')); ?> <code>forum-data/brand</code>.</p>
              <form class="forum-form" method="post" action="<?php echo forum_url(['view' => 'admin', 'section' => 'backup']); ?>">
                <input type="hidden" name="action" value="download_backup">
                <input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
                <button class="button" type="submit"><?php echo forum_escape(forum_t('Pobierz backup ZIP')); ?></button>
              </form>
            </article>

            <article class="forum-admin-card forum-admin-danger-zone">
              <span class="eyebrow"><?php echo forum_escape(forum_t('Przywracanie')); ?></span>
              <h3><?php echo forum_escape(forum_t('Przywróć forum z kopii')); ?></h3>
              <p class="forum-admin-note"><?php echo forum_escape(forum_t('Przywracanie zastąpi aktualną bazę danych, avatary i logo zawartością przesłanej kopii. Po tej operacji odśwież stronę i zaloguj się danymi z przywróconej bazy.')); ?></p>
              <form class="forum-form" method="post" action="<?php echo forum_url(['view' => 'admin', 'section' => 'backup']); ?>" enctype="multipart/form-data" onsubmit="return confirm(<?php echo forum_escape(json_encode(forum_t('Przywracanie zastąpi aktualne dane forum. Kontynuować?'), JSON_HEX_APOS | JSON_HEX_QUOT)); ?>);">
                <input type="hidden" name="action" value="restore_backup">
                <input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
                <label for="forum-backup"><?php echo forum_escape(forum_t('Plik kopii zapasowej ZIP')); ?></label>
                <input id="forum-backup" type="file" name="forum_backup" accept=".zip,application/zip,application/x-zip-compressed" required>
                <button class="button-secondary forum-danger-button" type="submit"><?php echo forum_escape(forum_t('Przywróć backup')); ?></button>
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
          <span class="eyebrow"><?php echo forum_escape(forum_t('Użytkownicy')); ?></span>
          <h2><?php echo forum_escape(forum_t('Zarządzaj użytkownikami i moderatorami')); ?></h2>
          <p class="forum-admin-note"><?php echo forum_escape(forum_t('Kliknij nazwę użytkownika, żeby otworzyć jego kartę edycji, zmienić rolę albo wykonać operacje administracyjne.')); ?></p>

          <div class="forum-admin-user-list-group">
            <div>
              <h3><?php echo forum_escape(forum_t('Administratorzy i moderatorzy')); ?></h3>
              <div class="forum-list forum-list-compact">
                <?php foreach ($staffUsers as $user): ?>
                  <article class="forum-admin-user-list-row">
                    <?php forum_ext_render_avatar($user); ?>
                    <div class="forum-user-row-main">
                      <h3><a href="<?php echo forum_url(['view' => 'admin', 'section' => 'user', 'user_id' => (int) $user['id']]); ?>"><?php echo forum_escape($user['username']); ?></a></h3>
                      <p><?php echo forum_escape(forum_role_label((string) ($user['role'] ?? 'member'))); ?> · <?php echo (int) $user['topic_count']; ?> <?php echo forum_escape(forum_t('tematów ·')); ?> <?php echo (int) $user['post_count']; ?> <?php echo forum_escape(forum_t('postów')); ?></p>
                    </div>
                  </article>
                <?php endforeach; ?>
              </div>
            </div>

            <div>
              <h3><?php echo forum_escape(forum_t('Zwykli użytkownicy')); ?></h3>
              <div class="forum-list forum-list-compact">
                <?php if ($memberUsers === []): ?>
                  <p class="forum-admin-note"><?php echo forum_escape(forum_t('Nie ma jeszcze zwykłych użytkowników.')); ?></p>
                <?php endif; ?>
                <?php foreach ($memberUsers as $user): ?>
                  <article class="forum-admin-user-list-row">
                    <?php forum_ext_render_avatar($user); ?>
                    <div class="forum-user-row-main">
                      <h3><a href="<?php echo forum_url(['view' => 'admin', 'section' => 'user', 'user_id' => (int) $user['id']]); ?>"><?php echo forum_escape($user['username']); ?></a></h3>
                      <p><?php echo (int) $user['topic_count']; ?> <?php echo forum_escape(forum_t('tematów ·')); ?> <?php echo (int) $user['post_count']; ?> <?php echo forum_escape(forum_t('postów ·')); ?> <?php echo (int) $user['likes_received']; ?> <?php echo forum_escape(forum_t('lajków')); ?></p>
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
              <span class="eyebrow"><?php echo forum_escape(forum_t('Użytkownik')); ?></span>
              <h2><?php echo forum_escape($selectedUser['username']); ?></h2>
            </div>
            <div class="forum-button-row">
              <a class="button-secondary" href="<?php echo forum_url(['view' => 'admin', 'section' => 'users']); ?>"><?php echo forum_escape(forum_t('Wróć do listy')); ?></a>
              <a class="button-secondary" href="<?php echo forum_url(['view' => 'user', 'id' => (int) $selectedUser['id']]); ?>"><?php echo forum_escape(forum_t('Profil publiczny')); ?></a>
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
                <span><?php echo (int) $selectedUser['topic_count']; ?> <?php echo forum_escape(forum_t('tematów')); ?></span>
                <span><?php echo (int) $selectedUser['post_count']; ?> <?php echo forum_escape(forum_t('postów')); ?></span>
                <span><?php echo (int) $selectedUser['likes_received']; ?> <?php echo forum_escape(forum_t('lajków')); ?></span>
                <span><?php echo (int) ($selectedUser['likes_given'] ?? 0); ?> <?php echo forum_escape(forum_t('danych lajków')); ?></span>
                <span><?php echo (int) ($selectedUser['message_count'] ?? 0); ?> <?php echo forum_escape(forum_t('wiadomości prywatnych')); ?></span>
                <span><?php echo forum_escape(forum_t('Ostatnie logowanie:')); ?> <?php echo forum_escape(!empty($selectedUser['last_login_at']) ? forum_format_date($selectedUser['last_login_at']) : forum_t('jeszcze brak')); ?></span>
              </div>
            </div>

            <form class="forum-form" method="post" action="<?php echo forum_url(['view' => 'admin', 'section' => 'user', 'user_id' => (int) $selectedUser['id']]); ?>">
              <input type="hidden" name="action" value="update_user">
              <input type="hidden" name="user_id" value="<?php echo (int) $selectedUser['id']; ?>">
              <input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
              <div class="forum-admin-user-form-grid">
                <div>
                  <label for="user-name-<?php echo (int) $selectedUser['id']; ?>"><?php echo forum_escape(forum_t('Nazwa użytkownika')); ?></label>
                  <input id="user-name-<?php echo (int) $selectedUser['id']; ?>" type="text" name="username" maxlength="40" value="<?php echo forum_escape($selectedUser['username']); ?>" <?php echo $isPrimaryAdmin ? 'readonly' : ''; ?> required>
                </div>
                <div>
                  <label for="user-email-<?php echo (int) $selectedUser['id']; ?>">E-mail</label>
                  <input id="user-email-<?php echo (int) $selectedUser['id']; ?>" type="email" name="email" maxlength="190" value="<?php echo forum_escape($selectedUser['email']); ?>" required>
                </div>
                <div>
                  <label for="user-role-<?php echo (int) $selectedUser['id']; ?>"><?php echo forum_escape(forum_t('Rola')); ?></label>
                  <?php if ($isPrimaryAdmin): ?>
                    <input id="user-role-<?php echo (int) $selectedUser['id']; ?>" type="text" value="<?php echo forum_escape(forum_role_label('admin')); ?>" readonly>
                    <input type="hidden" name="role" value="admin">
                  <?php else: ?>
                    <select id="user-role-<?php echo (int) $selectedUser['id']; ?>" name="role">
                      <option value="member" <?php echo ($selectedUser['role'] ?? '') === 'member' ? 'selected' : ''; ?>><?php echo forum_escape(forum_role_label('member')); ?></option>
                      <option value="moderator" <?php echo ($selectedUser['role'] ?? '') === 'moderator' ? 'selected' : ''; ?>><?php echo forum_escape(forum_role_label('moderator')); ?></option>
                    </select>
                  <?php endif; ?>
                </div>
                <div>
                  <label for="user-password-<?php echo (int) $selectedUser['id']; ?>"><?php echo forum_escape(forum_t('Nowe hasło')); ?></label>
                  <input id="user-password-<?php echo (int) $selectedUser['id']; ?>" type="password" name="new_password" minlength="10" placeholder="<?php echo forum_escape(forum_t('zostaw puste, jeśli bez zmian')); ?>">
                </div>
                <p class="forum-admin-note forum-form-field-full">
                  <?php if ($isPrimaryAdmin): ?>
                    <?php echo forum_escape(forum_t('To główne konto administratora forum. Możesz zmienić jego e-mail i hasło, ale nie nazwę ani rolę.')); ?>
                  <?php else: ?>
                    <?php echo forum_escape(forum_t('Moderator może przypinać, zamykać i usuwać tematy oraz edytować posty podczas moderacji.')); ?>
                  <?php endif; ?>
                </p>
              </div>
              <button class="button" type="submit"><?php echo forum_escape(forum_t('Zapisz użytkownika')); ?></button>
            </form>

            <?php if (!$isPrimaryAdmin): ?>
              <div class="forum-admin-danger-zone">
                <form class="forum-inline-form forum-admin-danger-form" method="post" action="<?php echo forum_url(['view' => 'admin', 'section' => 'user', 'user_id' => (int) $selectedUser['id']]); ?>" onsubmit="return confirm(<?php echo forum_escape(json_encode(forum_t('Wysłać wiadomość potwierdzającą usunięcie całej aktywności tego użytkownika?'), JSON_HEX_APOS | JSON_HEX_QUOT)); ?>);">
                  <input type="hidden" name="action" value="request_delete_user_activity">
                  <input type="hidden" name="user_id" value="<?php echo (int) $selectedUser['id']; ?>">
                  <input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
                  <button class="button-secondary forum-danger-button" type="submit"><?php echo forum_escape(forum_t('Usuń całą aktywność')); ?></button>
                </form>
                <p class="forum-admin-note"><?php echo forum_escape(forum_t('Ta operacja usuwa tematy, posty, lajki oraz prywatne wiadomości użytkownika. Samo konto zostaje na forum. Potwierdzenie przyjdzie na adres administratora.')); ?></p>

                <form class="forum-inline-form forum-admin-danger-form" method="post" action="<?php echo forum_url(['view' => 'admin', 'section' => 'user', 'user_id' => (int) $selectedUser['id']]); ?>" onsubmit="return confirm(<?php echo forum_escape(json_encode(forum_t('Na pewno całkowicie usunąć tego użytkownika, jego aktywność, wiadomości, lajki, tokeny i avatar? Tej operacji nie da się cofnąć.'), JSON_HEX_APOS | JSON_HEX_QUOT)); ?>);">
                  <input type="hidden" name="action" value="delete_user">
                  <input type="hidden" name="user_id" value="<?php echo (int) $selectedUser['id']; ?>">
                  <input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
                  <button class="button-secondary forum-danger-button" type="submit"><?php echo forum_escape(forum_t('Usuń użytkownika')); ?></button>
                </form>
                <p class="forum-admin-note"><?php echo forum_escape(forum_t('Ta operacja usuwa konto użytkownika oraz jego dane z bazy forum. Nie zostawia profilu ani historii prywatnych wiadomości tego konta.')); ?></p>
              </div>
            <?php endif; ?>
          </article>
        </section>
        <?php endif; ?>

        <?php if ($activeSection === 'top-users'): ?>
        <section id="admin-top-users" class="panel forum-panel">
          <span class="eyebrow"><?php echo forum_escape(forum_t('Najaktywniejsi')); ?></span>
          <h2><?php echo forum_escape(forum_t('Użytkownicy z największą aktywnością')); ?></h2>
          <div class="forum-list forum-home-recent-list">
            <?php foreach ($topUsers as $user): ?>
              <article class="forum-user-row">
                <?php forum_ext_render_avatar($user); ?>
                <div class="forum-user-row-main">
                  <h3><a href="<?php echo forum_url(['view' => 'user', 'id' => (int) $user['id']]); ?>"><?php echo forum_escape($user['username']); ?></a></h3>
                  <p><?php echo forum_escape(forum_role_label((string) ($user['role'] ?? 'member'))); ?> · <?php echo (int) $user['post_count']; ?> <?php echo forum_escape(forum_t('postów ·')); ?> <?php echo (int) $user['likes_received']; ?> <?php echo forum_escape(forum_t('lajków')); ?></p>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        </section>
        <?php endif; ?>

        <?php if ($activeSection === 'forum-sections'): ?>
        <section id="admin-forum-sections" class="panel forum-panel">
          <span class="eyebrow"><?php echo forum_escape(forum_t('Kategorie')); ?></span>
          <h2><?php echo forum_escape(forum_t('Zarządzaj kategoriami')); ?></h2>
          <div class="forum-admin-list">
            <?php foreach ($flatForumSections as $section): ?>
              <article class="forum-admin-card forum-admin-section-card">
                <form class="forum-form" method="post" action="<?php echo forum_url(['view' => 'admin', 'section' => 'forum-sections']); ?>">
                  <input type="hidden" name="action" value="update_forum_section">
                  <input type="hidden" name="section_id" value="<?php echo (int) $section['id']; ?>">
                  <input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
                  <span class="eyebrow"><?php echo forum_escape(forum_t('Kategoria')); ?></span>
                  <div class="forum-admin-user-form-grid">
                    <div>
                      <label for="forum-section-name-<?php echo (int) $section['id']; ?>"><?php echo forum_escape(forum_t('Nazwa kategorii')); ?></label>
                      <input id="forum-section-name-<?php echo (int) $section['id']; ?>" type="text" name="name" value="<?php echo forum_escape($section['name']); ?>" maxlength="80" required>
                    </div>
                    <div class="forum-form-field-full">
                      <label for="forum-section-description-<?php echo (int) $section['id']; ?>"><?php echo forum_escape(forum_t('Opis kategorii')); ?></label>
                      <textarea id="forum-section-description-<?php echo (int) $section['id']; ?>" name="description" maxlength="260"><?php echo forum_escape($section['description']); ?></textarea>
                    </div>
                  </div>
                  <button class="button" type="submit"><?php echo forum_escape(forum_t('Zapisz kategorię')); ?></button>
                </form>
              </article>
            <?php endforeach; ?>
          </div>
        </section>
        <?php endif; ?>

        <?php if ($activeSection === 'create-forum-section'): ?>
        <section id="admin-create-forum-section" class="panel forum-panel forum-narrow-panel">
          <span class="eyebrow"><?php echo forum_escape(forum_t('Nowa kategoria')); ?></span>
          <h2><?php echo forum_escape(forum_t('Dodaj kategorię')); ?></h2>
          <form class="forum-form" method="post" action="<?php echo forum_url(['view' => 'admin', 'section' => 'create-forum-section']); ?>">
            <input type="hidden" name="action" value="create_forum_section">
            <input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
            <label for="forum-section-name"><?php echo forum_escape(forum_t('Nazwa kategorii')); ?></label>
            <input id="forum-section-name" type="text" name="name" maxlength="80" required>
            <label for="forum-section-description"><?php echo forum_escape(forum_t('Opis kategorii')); ?></label>
            <textarea id="forum-section-description" name="description" maxlength="260"></textarea>
            <button class="button" type="submit"><?php echo forum_escape(forum_t('Dodaj kategorię')); ?></button>
          </form>
        </section>
        <?php endif; ?>

        <?php if ($activeSection === 'categories'): ?>
        <section id="admin-categories" class="panel forum-panel">
          <span class="eyebrow"><?php echo forum_escape(forum_t('Działy')); ?></span>
          <h2><?php echo forum_escape(forum_t('Zarządzaj działami')); ?></h2>
          <div class="forum-admin-list">
            <?php foreach ($forumSections as $section): ?>
              <article class="forum-admin-card forum-admin-section-card">
                <div class="forum-section-title">
                  <div>
                  <span class="eyebrow"><?php echo forum_escape(forum_t('Kategoria')); ?></span>
                  <h3><?php echo forum_escape($section['name']); ?></h3>
                  <?php if ((string) ($section['description'] ?? '') !== ''): ?>
                    <p><?php echo forum_escape($section['description']); ?></p>
                  <?php endif; ?>
                  </div>
                </div>

                <?php if (($section['categories'] ?? []) === []): ?>
                  <p class="forum-admin-note"><?php echo forum_escape(forum_t('W tej kategorii nie ma jeszcze działów.')); ?></p>
                <?php endif; ?>

                <?php foreach (($section['categories'] ?? []) as $category): ?>
                  <article class="forum-admin-card forum-admin-category-card">
                    <form class="forum-form" method="post" action="<?php echo forum_url(['view' => 'admin', 'section' => 'categories']); ?>">
                      <input type="hidden" name="action" value="update_category">
                      <input type="hidden" name="category_id" value="<?php echo (int) $category['id']; ?>">
                      <input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
                      <div class="forum-admin-user-form-grid">
                        <div>
                          <label><?php echo forum_escape(forum_t('Nazwa działu')); ?></label>
                          <input type="text" name="name" value="<?php echo forum_escape($category['name']); ?>" maxlength="80" required>
                        </div>
                        <div>
                          <label><?php echo forum_escape(forum_t('Kategoria')); ?></label>
                          <select name="section_id">
                            <?php foreach ($flatForumSections as $forumSection): ?>
                              <option value="<?php echo (int) $forumSection['id']; ?>" <?php echo (int) $forumSection['id'] === (int) $category['section_id'] ? 'selected' : ''; ?>><?php echo forum_escape($forumSection['name']); ?></option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                        <div class="forum-form-field-full">
                          <label><?php echo forum_escape(forum_t('Opis działu')); ?></label>
                          <textarea name="description" maxlength="260" required><?php echo forum_escape($category['description']); ?></textarea>
                        </div>
                      </div>
                      <button class="button" type="submit"><?php echo forum_escape(forum_t('Zapisz dział')); ?></button>
                    </form>
                    <div class="forum-button-row">
                      <form class="forum-inline-form" method="post" action="<?php echo forum_url(['view' => 'admin', 'section' => 'categories']); ?>">
                        <input type="hidden" name="action" value="move_category">
                        <input type="hidden" name="direction" value="up">
                        <input type="hidden" name="category_id" value="<?php echo (int) $category['id']; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
                        <button class="button-secondary" type="submit"><?php echo forum_escape(forum_t('Wyżej w kategorii')); ?></button>
                      </form>
                      <form class="forum-inline-form" method="post" action="<?php echo forum_url(['view' => 'admin', 'section' => 'categories']); ?>">
                        <input type="hidden" name="action" value="move_category">
                        <input type="hidden" name="direction" value="down">
                        <input type="hidden" name="category_id" value="<?php echo (int) $category['id']; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
                        <button class="button-secondary" type="submit"><?php echo forum_escape(forum_t('Niżej w kategorii')); ?></button>
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
          <span class="eyebrow"><?php echo forum_escape(forum_t('Nowy dział')); ?></span>
          <h2><?php echo forum_escape(forum_t('Dodaj dział forum')); ?></h2>
          <form class="forum-form" method="post" action="<?php echo forum_url(['view' => 'admin', 'section' => 'create-category']); ?>">
            <input type="hidden" name="action" value="create_category">
            <input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
            <label for="category-name"><?php echo forum_escape(forum_t('Nazwa działu')); ?></label>
            <input id="category-name" type="text" name="name" maxlength="80" required>
            <label for="category-section"><?php echo forum_escape(forum_t('Kategoria')); ?></label>
            <select id="category-section" name="section_id">
              <?php foreach ($flatForumSections as $forumSection): ?>
                <option value="<?php echo (int) $forumSection['id']; ?>"><?php echo forum_escape($forumSection['name']); ?></option>
              <?php endforeach; ?>
            </select>
            <label for="category-description"><?php echo forum_escape(forum_t('Opis działu')); ?></label>
            <textarea id="category-description" name="description" maxlength="260" required></textarea>
            <button class="button" type="submit"><?php echo forum_escape(forum_t('Dodaj dział')); ?></button>
          </form>
        </section>
        <?php endif; ?>
      </div>
    </div>
    <?php
    forum_ext_render_footer();
}


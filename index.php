<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require __DIR__ . '/ext_schema.php';
require __DIR__ . '/ext_social.php';
require __DIR__ . '/ext_comms.php';
require __DIR__ . '/ext_i18n.php';
require __DIR__ . '/ext_backup.php';
require __DIR__ . '/ext_search.php';
require __DIR__ . '/ext_render.php';
require __DIR__ . '/ext_views.php';

try {
    forum_db();
    forum_ext_boot();
    forum_ext_apply_language_defaults(forum_ext_current_language());
} catch (Throwable $e) {
    forum_ext_render_header('Forum chwilowo niedostępne', 'Nie udało się uruchomić bazy danych forum.');
    ?>
    <section class="panel forum-panel">
      <div class="warning">
        <strong>Forum nie może się teraz uruchomić.</strong>
        <p>Sprawdź, czy hosting pozwala na zapis do katalogu <code>forum-data</code> i czy PHP ma włączoną obsługę SQLite.</p>
      </div>
    </section>
    <?php
    forum_ext_render_footer();
    exit;
}

$currentUser = forum_ext_current_user();
$view = (string) ($_GET['view'] ?? 'home');
$adminSection = (string) ($_GET['section'] ?? 'settings');
$adminUserId = (int) ($_GET['user_id'] ?? 0);

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    try {
        forum_require_valid_csrf();
        $action = (string) ($_POST['action'] ?? '');

        switch ($action) {
            case 'register':
                forum_rate_limit('register', 3, 900);
                if (!forum_ext_registrations_enabled()) {
                    throw new RuntimeException('Rejestracja nowych kont jest chwilowo wyłączona.');
                }
                forum_register_user((string) ($_POST['username'] ?? ''), (string) ($_POST['email'] ?? ''), (string) ($_POST['password'] ?? ''), isset($_POST['accept_terms']) && (string) $_POST['accept_terms'] === '1');
                forum_flash('success', 'Konto zostało utworzone. Możesz się teraz zalogować.');
                forum_redirect(forum_url(['view' => 'login']));

            case 'login':
                forum_rate_limit('login', 8, 900);
                forum_login_user((string) ($_POST['login'] ?? ''), (string) ($_POST['password'] ?? ''));
                forum_flash('success', 'Zalogowano pomyślnie.');
                forum_redirect(forum_url());

            case 'logout':
                forum_logout_user();
                forum_flash('success', 'Zostałeś wylogowany.');
                forum_redirect(forum_url());

            case 'change_password':
                $user = forum_require_login();
                forum_change_password((int) $user['id'], (string) ($_POST['current_password'] ?? ''), (string) ($_POST['new_password'] ?? ''));
                forum_flash('success', 'Hasło zostało zmienione.');
                forum_redirect(forum_url(['view' => 'account']));

            case 'upload_avatar':
                $user = forum_require_login();
                forum_ext_handle_avatar_upload((int) $user['id'], $_FILES['avatar'] ?? []);
                forum_flash('success', 'Avatar został zaktualizowany.');
                forum_redirect(forum_url(['view' => 'account']));

            case 'remove_avatar':
                $user = forum_require_login();
                forum_ext_remove_avatar((int) $user['id']);
                forum_flash('success', 'Avatar został usunięty.');
                forum_redirect(forum_url(['view' => 'account']));

            case 'create_topic':
                forum_rate_limit('create_topic_' . (int) ($_SESSION['forum_user_id'] ?? 0), 8, 600);
                $user = forum_require_login();
                $topicId = forum_create_topic_from_request((int) ($_POST['category_id'] ?? 0), (int) $user['id'], (string) ($_POST['title'] ?? ''), (string) ($_POST['body'] ?? ''));
                forum_flash('success', 'Temat został utworzony.');
                forum_redirect(forum_url(['view' => 'topic', 'id' => $topicId]));

            case 'create_post':
                forum_rate_limit('create_post_' . (int) ($_SESSION['forum_user_id'] ?? 0), 20, 600);
                $user = forum_require_login();
                $topicId = (int) ($_POST['topic_id'] ?? 0);
                $topic = forum_ext_fetch_topic($topicId);
                if (!$topic) {
                    throw new RuntimeException('Nie znaleziono tematu.');
                }
                if ((int) $topic['is_locked'] === 1 && !forum_is_staff($user)) {
                    throw new RuntimeException('Ten temat jest zamknięty.');
                }
                forum_create_post($topicId, (int) $user['id'], (string) ($_POST['body'] ?? ''));
                forum_flash('success', 'Odpowiedź została dodana.');
                $lastPage = (int) forum_pagination(forum_count_posts_for_topic($topicId), 1, FORUM_POSTS_PER_PAGE)['total_pages'];
                forum_redirect(forum_url(['view' => 'topic', 'id' => $topicId, 'page' => $lastPage]));

            case 'update_post':
                $user = forum_require_login();
                $topicId = forum_ext_update_post_with_staff((int) ($_POST['post_id'] ?? 0), $user, (string) ($_POST['body'] ?? ''), (string) ($_POST['topic_title'] ?? ''));
                forum_flash('success', 'Post został zaktualizowany.');
                forum_redirect(forum_url(['view' => 'topic', 'id' => $topicId, 'page' => forum_current_page()]));

            case 'like_post':
                $user = forum_require_login();
                $post = forum_ext_fetch_post((int) ($_POST['post_id'] ?? 0));
                if (!$post) {
                    throw new RuntimeException('Nie znaleziono wskazanego postu.');
                }
                forum_ext_toggle_post_like((int) $post['id'], (int) $user['id']);
                forum_redirect(forum_url(['view' => 'topic', 'id' => (int) $post['topic_id'], 'page' => forum_current_page()]) . '#post-' . (int) $post['id']);

            case 'report_post':
                $user = forum_require_login();
                $post = forum_ext_fetch_post((int) ($_POST['post_id'] ?? 0));
                if (!$post) {
                    throw new RuntimeException('Nie znaleziono wskazanego postu.');
                }
                forum_ext_report_post((int) $post['id'], (int) $user['id'], (string) ($_POST['reason'] ?? ''));
                forum_flash('success', 'Post został zgłoszony moderatorom.');
                forum_redirect(forum_url(['view' => 'topic', 'id' => (int) $post['topic_id'], 'page' => forum_current_page()]) . '#post-' . (int) $post['id']);

            case 'send_message':
                forum_rate_limit('send_message_' . (int) ($_SESSION['forum_user_id'] ?? 0), 12, 600);
                $user = forum_require_login();
                $messageId = forum_ext_send_message((int) $user['id'], (string) ($_POST['recipient'] ?? ''), (string) ($_POST['subject'] ?? ''), (string) ($_POST['body'] ?? ''));
                forum_flash('success', 'Wiadomość została wysłana.');
                forum_redirect(forum_url(['view' => 'message', 'id' => $messageId]));

            case 'request_password_reset':
                forum_rate_limit('password_reset', 5, 900);
                forum_ext_request_password_reset((string) ($_POST['login'] ?? ''));
                forum_flash('success', 'Jeśli konto istnieje, wysłaliśmy link do zmiany hasła na powiązany adres e-mail.');
                forum_redirect(forum_url(['view' => 'forgot-password']));

            case 'reset_password':
                forum_rate_limit('reset_password', 5, 900);
                forum_ext_reset_password_with_token((string) ($_POST['selector'] ?? ''), (string) ($_POST['token'] ?? ''), (string) ($_POST['new_password'] ?? ''));
                forum_flash('success', 'Hasło zostało ustawione. Możesz się teraz zalogować.');
                forum_redirect(forum_url(['view' => 'login']));

            case 'create_forum_section':
                forum_require_admin_user();
                forum_create_forum_section((string) ($_POST['name'] ?? ''), (string) ($_POST['description'] ?? ''));
                forum_flash('success', 'Nowa kategoria forum została dodana.');
                forum_redirect(forum_url(['view' => 'admin', 'section' => 'create-forum-section']));

            case 'update_forum_section':
                forum_require_admin_user();
                forum_update_forum_section((int) ($_POST['section_id'] ?? 0), (string) ($_POST['name'] ?? ''), (string) ($_POST['description'] ?? ''));
                forum_flash('success', 'Kategoria forum została zaktualizowana.');
                forum_redirect(forum_url(['view' => 'admin', 'section' => 'forum-sections']));

            case 'create_category':
                forum_require_admin_user();
                forum_create_category((string) ($_POST['name'] ?? ''), (string) ($_POST['description'] ?? ''), (int) ($_POST['section_id'] ?? 0));
                forum_flash('success', 'Nowy dział został dodany.');
                forum_redirect(forum_url(['view' => 'admin', 'section' => 'create-category']));

            case 'update_category':
                forum_require_admin_user();
                forum_update_category((int) ($_POST['category_id'] ?? 0), (string) ($_POST['name'] ?? ''), (string) ($_POST['description'] ?? ''), (int) ($_POST['section_id'] ?? 0));
                forum_flash('success', 'Dział został zaktualizowany.');
                forum_redirect(forum_url(['view' => 'admin', 'section' => 'categories']));

            case 'move_category':
                forum_require_admin_user();
                forum_move_category((int) ($_POST['category_id'] ?? 0), (string) ($_POST['direction'] ?? 'down'));
                forum_flash('success', 'Kolejność działów została zmieniona.');
                forum_redirect(forum_url(['view' => 'admin', 'section' => 'categories']));

            case 'update_user':
                forum_require_admin_user();
                forum_update_user_by_admin(
                    (int) ($_POST['user_id'] ?? 0),
                    (string) ($_POST['username'] ?? ''),
                    (string) ($_POST['email'] ?? ''),
                    (string) ($_POST['role'] ?? 'member'),
                    (string) ($_POST['new_password'] ?? '')
                );
                forum_flash('success', 'Dane użytkownika zostały zaktualizowane.');
                if ($adminSection === 'user' && $adminUserId > 0) {
                    forum_redirect(forum_url(['view' => 'admin', 'section' => 'user', 'user_id' => $adminUserId]));
                }
                forum_redirect(forum_url(['view' => 'admin', 'section' => 'users']));

            case 'request_delete_user_activity':
                $adminUser = forum_require_admin_user();
                forum_ext_request_user_activity_purge(
                    (int) ($_POST['user_id'] ?? 0),
                    (int) $adminUser['id']
                );
                forum_flash('success', 'Wysłaliśmy wiadomość potwierdzającą na adres administratora. Dopiero po kliknięciu w link aktywność użytkownika zostanie usunięta.');
                if ($adminSection === 'user' && $adminUserId > 0) {
                    forum_redirect(forum_url(['view' => 'admin', 'section' => 'user', 'user_id' => $adminUserId]));
                }
                forum_redirect(forum_url(['view' => 'admin', 'section' => 'users']));

            case 'delete_user':
                $adminUser = forum_require_admin_user();
                $result = forum_ext_delete_user_completely(
                    (int) ($_POST['user_id'] ?? 0),
                    (int) $adminUser['id']
                );
                forum_flash(
                    'success',
                    sprintf(
                        'Usunięto użytkownika %s oraz jego dane: %d tematów, %d postów, %d lajków i %d prywatnych wiadomości.',
                        $result['username'],
                        $result['topic_count'],
                        $result['post_count'],
                        $result['likes_given'],
                        $result['message_count']
                    )
                );
                forum_redirect(forum_url(['view' => 'admin', 'section' => 'users']));

            case 'toggle_topic_lock':
            case 'toggle_topic_pin':
                forum_require_staff_user();
                $topicId = (int) ($_POST['topic_id'] ?? 0);
                forum_toggle_topic_flag($topicId, $action === 'toggle_topic_lock' ? 'is_locked' : 'is_pinned');
                forum_flash('success', 'Status tematu został zaktualizowany.');
                forum_redirect(forum_url(['view' => 'topic', 'id' => $topicId]));

            case 'delete_topic':
                forum_require_staff_user();
                $topicId = (int) ($_POST['topic_id'] ?? 0);
                forum_delete_topic($topicId);
                forum_flash('success', 'Temat został usunięty.');
                forum_redirect(forum_url());

            case 'close_post_report':
                $staffUser = forum_require_staff_user();
                forum_ext_close_post_report((int) ($_POST['report_id'] ?? 0), (int) $staffUser['id']);
                forum_flash('success', 'Zgłoszenie zostało zamknięte.');
                forum_redirect(forum_url(['view' => forum_is_admin($staffUser) ? 'admin' : 'moderation', 'section' => 'reports']));

            case 'download_backup':
                forum_require_admin_user();
                forum_ext_download_backup();

            case 'restore_backup':
                forum_require_admin_user();
                forum_ext_restore_backup($_FILES['forum_backup'] ?? []);
                forum_flash('success', 'Kopia zapasowa została przywrócona. Forum korzysta teraz z danych z przesłanego archiwum.');
                forum_redirect(forum_url(['view' => 'admin', 'section' => 'backup']));

            case 'update_settings':
                forum_require_admin_user();
                forum_ext_save_settings($_POST);
                forum_ext_apply_language_defaults((string) ($_POST['forum_language'] ?? 'en'));
                forum_ext_handle_brand_logo_upload($_FILES['brand_logo'] ?? []);
                forum_flash('success', 'Ustawienia forum zostały zapisane.');
                $returnSection = (string) ($_POST['return_section'] ?? 'settings');
                if (!in_array($returnSection, ['settings', 'basic-settings'], true)) {
                    $returnSection = 'settings';
                }
                forum_redirect(forum_url(['view' => 'admin', 'section' => $returnSection]));
        }
    } catch (Throwable $e) {
        forum_flash('error', $e->getMessage());
        $fallbackParams = ['view' => $view, 'id' => (int) ($_GET['id'] ?? 0)];
        if ($view === 'admin') {
            $fallbackParams = ['view' => 'admin', 'section' => $adminSection];
            if ($adminUserId > 0) {
                $fallbackParams['user_id'] = $adminUserId;
            }
        }
        $fallback = $view === '' ? forum_url() : forum_url($fallbackParams);
        forum_redirect($fallback);
    }
}

$flash = forum_pull_flash();
$currentUser = forum_ext_current_user();

switch ($view) {
    case 'category':
        $category = forum_fetch_category((int) ($_GET['id'] ?? 0));
        if (!$category) {
            forum_ext_render_header('Nie znaleziono działu', 'Wybrany dział forum nie istnieje.');
            forum_ext_render_flash($flash);
            echo '<section class="panel forum-panel"><p>Nie udało się znaleźć wskazanego działu.</p></section>';
            forum_ext_render_footer();
            break;
        }
        $topicPagination = forum_pagination(forum_count_topics_for_category((int) $category['id']), forum_current_page(), FORUM_TOPICS_PER_PAGE);
        forum_ext_render_category(
            $category,
            forum_fetch_topics_for_category((int) $category['id'], (int) $topicPagination['page'], (int) $topicPagination['per_page']),
            $topicPagination,
            $currentUser,
            $flash
        );
        break;

    case 'topic':
        $topic = forum_ext_fetch_topic((int) ($_GET['id'] ?? 0));
        if (!$topic) {
            forum_ext_render_header('Nie znaleziono tematu', 'Wybrany temat forum nie istnieje.');
            forum_ext_render_flash($flash);
            echo '<section class="panel forum-panel"><p>Nie udało się znaleźć wskazanego tematu.</p></section>';
            forum_ext_render_footer();
            break;
        }
        $editPost = null;
        if ($currentUser && !empty($_GET['edit_post'])) {
            $candidate = forum_ext_fetch_post((int) $_GET['edit_post']);
            if ($candidate && forum_ext_user_can_edit_post($currentUser, $candidate)) {
                $editPost = $candidate;
            }
        }
        $quoteText = '';
        if ($currentUser && !empty($_GET['quote_post'])) {
            try {
                $quoteText = forum_ext_quote_text_for_post((int) $_GET['quote_post']);
            } catch (Throwable $e) {
                forum_flash('error', $e->getMessage());
            }
        }
        $postPagination = forum_pagination(forum_count_posts_for_topic((int) $topic['id']), forum_current_page(), FORUM_POSTS_PER_PAGE);
        forum_ext_render_topic(
            $topic,
            forum_ext_fetch_posts_for_topic((int) $topic['id'], (int) ($currentUser['id'] ?? 0), (int) $postPagination['page'], (int) $postPagination['per_page']),
            $postPagination,
            $currentUser,
            $flash,
            $editPost
        );
        if ($currentUser && ((int) $topic['is_locked'] === 0 || forum_is_staff($currentUser))) {
            ?>
            <section class="panel forum-panel">
              <span class="eyebrow">Odpowiedź</span>
              <h2>Dodaj odpowiedź</h2>
              <form class="forum-form" method="post" action="<?php echo forum_url(['view' => 'topic', 'id' => (int) $topic['id'], 'page' => (int) $postPagination['page']]); ?>">
                <input type="hidden" name="action" value="create_post"><input type="hidden" name="topic_id" value="<?php echo (int) $topic['id']; ?>"><input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
                <label for="reply-body">Twoja wiadomość</label><?php forum_ext_render_editor_toolbar('reply-body'); ?><textarea id="reply-body" name="body" minlength="3" maxlength="12000" required><?php echo forum_escape($quoteText); ?></textarea>
                <button class="button" type="submit">Opublikuj odpowiedź</button>
              </form>
            </section>
            <?php
        } elseif (!$currentUser) {
            ?>
            <section class="panel forum-panel">
              <div class="callout">
                <strong>Chcesz odpowiedzieć?</strong>
                <p>
                  <?php if (forum_ext_registrations_enabled()): ?>
                    Najpierw <a href="<?php echo forum_url(['view' => 'register']); ?>">załóż konto</a> albo <a href="<?php echo forum_url(['view' => 'login']); ?>">zaloguj się</a>.
                  <?php else: ?>
                    Najpierw <a href="<?php echo forum_url(['view' => 'login']); ?>">zaloguj się</a>.
                  <?php endif; ?>
                </p>
              </div>
            </section>
            <?php
        } else {
            ?>
            <section class="panel forum-panel">
              <div class="warning">
                <strong>Ten temat jest zamknięty.</strong>
                <p>Nowe odpowiedzi są obecnie wyłączone.</p>
              </div>
            </section>
            <?php
        }
        forum_ext_render_footer();
        break;

    case 'login':
    case 'register':
    case 'forgot-password':
        forum_ext_render_auth($view, $flash);
        break;

    case 'reset-password':
        forum_ext_render_auth('reset-password', $flash, forum_ext_validate_reset_token((string) ($_GET['selector'] ?? ''), (string) ($_GET['token'] ?? '')));
        break;

    case 'confirm-user-activity-purge':
        $adminUser = forum_require_admin_user();

        try {
            $result = forum_ext_execute_user_activity_purge(
                (string) ($_GET['selector'] ?? ''),
                (string) ($_GET['token'] ?? ''),
                (int) $adminUser['id']
            );
            forum_flash(
                'success',
                sprintf(
                    'Usunięto aktywność użytkownika %s: %d tematów, %d postów, %d lajków i %d prywatnych wiadomości.',
                    $result['username'],
                    $result['topic_count'],
                    $result['post_count'],
                    $result['likes_given'],
                    $result['message_count']
                )
            );
        } catch (Throwable $e) {
            forum_flash('error', $e->getMessage());
        }

        forum_redirect(forum_url(['view' => 'admin']));
        break;

    case 'account':
        $user = forum_require_login();
        forum_ext_render_account(forum_ext_fetch_user_profile((int) $user['id']) ?? $user, forum_ext_recent_topics_for_user((int) $user['id']), forum_ext_recent_posts_for_user((int) $user['id']), $flash);
        break;

    case 'messages':
        $user = forum_require_login();
        $messageBox = (string) ($_GET['box'] ?? 'inbox');
        $messageBox = $messageBox === 'outbox' ? 'outbox' : 'inbox';
        $messageTotal = $messageBox === 'outbox' ? forum_ext_count_outbox((int) $user['id']) : forum_ext_count_inbox((int) $user['id']);
        $messagePagination = forum_pagination($messageTotal, forum_current_page(), FORUM_MESSAGES_PER_PAGE);
        forum_ext_render_messages(
            $messageBox === 'outbox' ? [] : forum_ext_fetch_inbox((int) $user['id'], (int) $messagePagination['page'], (int) $messagePagination['per_page']),
            $messageBox === 'outbox' ? forum_ext_fetch_outbox((int) $user['id'], (int) $messagePagination['page'], (int) $messagePagination['per_page']) : [],
            $messageBox,
            $messagePagination,
            $flash
        );
        break;

    case 'message':
        $user = forum_require_login();
        $messageId = (int) ($_GET['id'] ?? 0);
        forum_ext_mark_message_read($messageId, (int) $user['id']);
        $message = forum_ext_fetch_message($messageId, (int) $user['id']);
        if (!$message) {
            forum_flash('error', 'Nie znaleziono wiadomości.');
            forum_redirect(forum_url(['view' => 'messages']));
        }
        forum_ext_render_message($message, $flash);
        break;

    case 'compose':
        forum_require_login();
        forum_ext_render_compose($flash, (string) ($_GET['to'] ?? ''), (string) ($_GET['subject'] ?? ''), (string) ($_GET['body'] ?? ''));
        break;

    case 'search':
        $searchQuery = forum_ext_search_query((string) ($_GET['q'] ?? ''));
        $searchTotal = forum_ext_count_search_results($searchQuery);
        $searchPagination = forum_pagination($searchTotal, forum_current_page(), FORUM_SEARCH_RESULTS_PER_PAGE);
        forum_ext_render_search(
            $searchQuery,
            forum_ext_search_results($searchQuery, (int) $searchPagination['page'], (int) $searchPagination['per_page']),
            $searchPagination,
            $flash
        );
        break;

    case 'user':
        $profile = forum_ext_fetch_user_profile((int) ($_GET['id'] ?? 0));
        if (!$profile) {
            forum_ext_render_header('Nie znaleziono użytkownika', 'Wybrany profil nie istnieje.');
            forum_ext_render_flash($flash);
            echo '<section class="panel forum-panel"><p>Nie udało się znaleźć wskazanego użytkownika.</p></section>';
            forum_ext_render_footer();
            break;
        }
        forum_ext_render_user($profile, forum_ext_recent_topics_for_user((int) $profile['id']), forum_ext_recent_posts_for_user((int) $profile['id']), $currentUser, $flash);
        break;

    case 'moderation':
        forum_require_staff_user();
        forum_ext_render_moderation_panel(forum_ext_open_post_reports(), $flash);
        break;

    case 'admin':
        forum_require_admin_user();
        $adminUserPagination = forum_pagination(forum_ext_count_users(), forum_current_page(), FORUM_ADMIN_USERS_PER_PAGE);
        forum_ext_render_admin_panel(
            forum_ext_admin_summary(),
            forum_fetch_categories(),
            forum_ext_top_users(12),
            forum_ext_admin_users((int) $adminUserPagination['per_page'], (int) $adminUserPagination['offset']),
            $adminUserPagination,
            $flash,
            $adminSection,
            $adminUserId
        );
        break;

    case 'home':
    default:
        forum_ext_render_home($flash);
        break;
}

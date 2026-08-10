<?php
declare(strict_types=1);

function forum_ext_excerpt(?string $text, int $length = 160): string
{
    $text = trim((string) $text);
    if ($text === '') {
        return '';
    }

    if (function_exists('mb_strlen') && mb_strlen($text, 'UTF-8') > $length) {
        return (string) mb_substr($text, 0, $length, 'UTF-8') . '…';
    }

    if (strlen($text) > $length) {
        return substr($text, 0, $length) . '...';
    }

    return $text;
}

function forum_ext_render_avatar(array $user, string $class = 'forum-avatar'): void
{
    ?>
    <img
      class="<?php echo forum_escape($class); ?>"
      src="<?php echo forum_escape(forum_ext_avatar_url($user)); ?>"
      alt="<?php echo forum_escape('Avatar użytkownika ' . ($user['username'] ?? 'forum')); ?>"
      loading="lazy"
      width="80"
      height="80">
    <?php
}

function forum_ext_render_like_icon(bool $active = false): void
{
    ?>
    <svg class="forum-like-icon<?php echo $active ? ' is-active' : ''; ?>" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
      <path d="M10.1 3.2c.7.5 1 1.4.8 2.3l-.6 2.7h6c1.7 0 2.9 1.6 2.4 3.2l-2 6.4c-.4 1.2-1.5 2-2.8 2H8.1c-.7 0-1.3-.2-1.8-.7L4 16.9V8.8l2.2-2.1c.4-.4.7-.9.9-1.5l.4-1.4c.4-1.2 1.6-1.5 2.6-.6Zm-7.6 5h2.3v10.9H2.5c-.6 0-1-.4-1-1V9.2c0-.6.4-1 1-1Z" fill="currentColor"/>
    </svg>
    <?php
}

function forum_ext_render_flash(?array $flash): void
{
    if (!$flash) {
        return;
    }

    $class = ($flash['type'] ?? '') === 'success' ? 'success-box' : 'warning';
    ?>
    <section class="panel forum-panel">
      <div class="<?php echo forum_escape($class); ?>">
        <?php echo forum_escape((string) ($flash['message'] ?? '')); ?>
      </div>
    </section>
    <?php
}

function forum_ext_render_pagination(array $pagination, array $baseParams = []): void
{
    $totalPages = (int) ($pagination['total_pages'] ?? 1);
    $page = (int) ($pagination['page'] ?? 1);
    if ($totalPages <= 1) {
        return;
    }

    $start = max(1, $page - 2);
    $end = min($totalPages, $page + 2);
    ?>
    <nav class="forum-pagination" aria-label="Strony">
      <?php if ($page > 1): ?>
        <a class="button-secondary" href="<?php echo forum_url($baseParams + ['page' => $page - 1]); ?>">Poprzednia</a>
      <?php endif; ?>
      <?php for ($i = $start; $i <= $end; $i++): ?>
        <a class="button-secondary<?php echo $i === $page ? ' is-active' : ''; ?>" href="<?php echo forum_url($baseParams + ['page' => $i]); ?>" <?php echo $i === $page ? 'aria-current="page"' : ''; ?>><?php echo $i; ?></a>
      <?php endfor; ?>
      <?php if ($page < $totalPages): ?>
        <a class="button-secondary" href="<?php echo forum_url($baseParams + ['page' => $page + 1]); ?>">Następna</a>
      <?php endif; ?>
    </nav>
    <?php
}

function forum_ext_render_header(string $title, string $description): void
{
    ob_start();
    $user = forum_ext_current_user();
    $brandName = forum_ext_setting('brand_name');
    $brandTagline = forum_ext_setting('brand_tagline');
    $brandLogoUrl = forum_ext_brand_logo_url();
    $graphicStyle = forum_ext_setting('graphic_style');
    if (!array_key_exists($graphicStyle, forum_ext_graphic_styles())) {
        $graphicStyle = 'classic';
    }
    if ($brandName === '') {
        $brandName = 'ForumForgeCMS';
    }
    if ($brandTagline === '') {
        $brandTagline = 'samodzielne forum dla Twojej społeczności';
    }
    $language = forum_ext_current_language();
    $currentView = (string) ($_GET['view'] ?? 'home');
    ?>
<!doctype html>
<html lang="<?php echo forum_escape($language); ?>">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <meta http-equiv="Content-Language" content="<?php echo forum_escape($language); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="<?php echo forum_escape($description); ?>">
  <meta name="robots" content="index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1">
  <title><?php echo forum_escape($title); ?> - <?php echo forum_escape($brandName); ?></title>
  <link rel="icon" type="image/png" sizes="32x32" href="media/images/favicons/favicon-32x32.png">
  <link rel="stylesheet" href="assets/forumforgecms2026.css">
  <link rel="stylesheet" href="assets/forum.css">
</head>
<body class="theme-<?php echo forum_escape($graphicStyle); ?>">
  <div class="site-shell">
    <header class="site-header">
      <div class="site-header-inner">
        <a class="brand" href="index.php"><?php if ($brandLogoUrl !== ''): ?><img class="brand-mark brand-logo" src="<?php echo forum_escape($brandLogoUrl); ?>" alt="<?php echo forum_escape($brandName); ?>" width="69" height="69"><?php else: ?><span class="brand-mark">FFC</span><?php endif; ?><span class="brand-copy"><?php echo forum_escape($brandName); ?><small><?php echo forum_escape($brandTagline); ?></small></span></a>
        <nav class="nav-links" aria-label="Główna nawigacja">
          <a href="index.php" <?php echo $currentView === 'home' ? 'aria-current="page"' : ''; ?>>Forum</a>
          <a href="<?php echo forum_url(['view' => 'search']); ?>" <?php echo $currentView === 'search' ? 'aria-current="page"' : ''; ?>>Szukaj</a>
        </nav>
        <details class="mobile-nav">
          <summary>Menu</summary>
          <div class="mobile-links">
            <a href="index.php" <?php echo $currentView === 'home' ? 'aria-current="page"' : ''; ?>>Forum</a>
            <a href="<?php echo forum_url(['view' => 'search']); ?>" <?php echo $currentView === 'search' ? 'aria-current="page"' : ''; ?>>Szukaj</a>
          </div>
        </details>
      </div>
    </header>
    <main class="page-wrap">
      <section class="hero forum-hero">
        <div class="forum-hero-grid">
          <div>
            <span class="eyebrow">Społeczność</span>
            <h1><?php echo forum_escape($title); ?></h1>
            <p class="lead"><?php echo forum_escape($description); ?></p>
          </div>
          <div class="forum-user-card">
            <?php if ($user): ?>
              <div class="forum-user-card-head">
                <?php forum_ext_render_avatar($user, 'forum-avatar forum-avatar-medium'); ?>
                <div>
                  <strong><?php echo forum_escape($user['username']); ?></strong>
                  <p>Zalogowany jako <?php echo forum_escape(forum_role_label((string) ($user['role'] ?? 'member'))); ?>.</p>
                </div>
              </div>
              <div class="forum-button-row">
                <a class="button-secondary" href="<?php echo forum_url(['view' => 'account']); ?>">Moje konto</a>
                <a class="button-secondary" href="<?php echo forum_url(['view' => 'messages']); ?>">
                  Wiadomości<?php if ((int) ($user['unread_message_count'] ?? 0) > 0): ?> (<?php echo (int) $user['unread_message_count']; ?>)<?php endif; ?>
                </a>
                <?php if (forum_is_admin($user)): ?>
                  <a class="button-secondary" href="<?php echo forum_url(['view' => 'admin']); ?>">Panel admina</a>
                <?php elseif (forum_is_moderator($user)): ?>
                  <a class="button-secondary" href="<?php echo forum_url(['view' => 'moderation']); ?>">Panel moderatora</a>
                <?php endif; ?>
                <form method="post" action="<?php echo forum_url(); ?>" class="forum-inline-form">
                  <input type="hidden" name="action" value="logout">
                  <input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
                  <button class="button" type="submit">Wyloguj</button>
                </form>
              </div>
            <?php else: ?>
              <strong>Dołącz do dyskusji</strong>
              <p>Załóż konto, aby pisać posty, wysyłać prywatne wiadomości i budować swój profil na forum.</p>
              <div class="forum-button-row">
                <?php if (forum_ext_registrations_enabled()): ?>
                  <a class="button" href="<?php echo forum_url(['view' => 'register']); ?>">Załóż konto</a>
                <?php endif; ?>
                <a class="button-secondary" href="<?php echo forum_url(['view' => 'login']); ?>">Zaloguj się</a>
                <a class="button-secondary" href="<?php echo forum_url(['view' => 'forgot-password']); ?>">Reset hasła</a>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </section>
<?php
}

function forum_ext_render_footer(): void
{
    ?>
    </main>
    <footer class="site-footer">
      <div class="site-footer-inner">
        &copy; 2026 Stronę zbudował <a href="https://zawalka.com">Piotr Zawalka</a> (<a href="https://wowo89.de/">https://wowo89.de/</a>) - <a href="mailto:piotr@zawalka.com">piotr@zawalka.com</a>
      </div>
    </footer>
  </div>
  <script src="assets/forum-media.js"></script>
  <script src="assets/forum-editor.js"></script>
</body>
</html>
<?php
    $html = ob_get_clean();
    echo forum_ext_translate_html((string) $html);
}


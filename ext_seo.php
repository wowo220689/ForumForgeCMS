<?php
declare(strict_types=1);

function forum_seo_validate_url(string $url): string
{
    $url = trim($url);
    if ($url === '') { return ''; }
    $parts = parse_url($url);
    if (strlen($url) > 255 || !filter_var($url, FILTER_VALIDATE_URL) || !$parts
        || !in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)
        || isset($parts['user']) || isset($parts['pass']) || isset($parts['query']) || isset($parts['fragment'])
        || preg_match('/[\x00-\x20\\\\]/', $url) || preg_match('~\.php/?$~i', $parts['path'] ?? '')) {
        throw new RuntimeException(forum_t('seo.invalid_url'));
    }
    return rtrim($url, '/');
}

function forum_seo_excerpt(string $text): string
{
    $text = preg_replace('~\[(?:quote|code)(?:=[^\]]*)?\].*?\[/(?:quote|code)\]~is', ' ', $text) ?? '';
    $text = preg_replace('~\[/?[a-z][^\]]*\]~i', '', $text) ?? '';
    $text = preg_replace('/\s+/u', ' ', strip_tags($text)) ?? '';
    $text = trim($text);
    return preg_match('/^(.{160})./us', $text, $matches) ? $matches[1] . '…' : $text;
}

function forum_seo_metadata(string $title, string $description): array
{
    $brand = forum_ext_setting('brand_name') ?: 'ForumForgeCMS';
    $meta = ['title' => $brand, 'description' => forum_ext_setting('brand_tagline'), 'index' => false, 'url' => '', 'image' => '', 'type' => 'website'];
    $view = (string) ($_GET['view'] ?? 'home');
    $params = [];
    $status = http_response_code();
    if (($status !== false && $status >= 400) || ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') { return $meta; }
    if ($view === 'home') {
        $meta['index'] = true;
        $meta['description'] = forum_seo_excerpt(forum_ext_setting('home_intro_text'));
    } elseif (in_array($view, ['topic', 'category'], true)) {
        $id = (int) ($_GET['id'] ?? 0);
        $sql = $view === 'topic'
            ? 'SELECT t.title, c.description, (SELECT body FROM posts WHERE topic_id=t.id ORDER BY created_at,id LIMIT 1) AS body FROM topics t JOIN categories c ON c.id=t.category_id WHERE t.id=:id AND c.visibility="public"'
            : 'SELECT name AS title, description FROM categories WHERE id=:id AND visibility="public"';
        $stmt = forum_db()->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        if (!$row) { return $meta; }
        $meta['index'] = true;
        $meta['title'] = $row['title'] . ' - ' . $brand;
        $meta['description'] = forum_seo_excerpt($row['body'] ?? $row['description']);
        $meta['type'] = $view === 'topic' ? 'article' : 'website';
        $params = ['view' => $view, 'id' => $id];
        $page = forum_current_page();
        if ($page > 1) {
            $count = $view === 'topic' ? forum_count_posts_for_topic($id) : forum_count_topics_for_category($id);
            $page = (int) forum_pagination($count, $page, $view === 'topic' ? FORUM_POSTS_PER_PAGE : FORUM_TOPICS_PER_PAGE)['page'];
        }
        if ($page > 1) { $params['page'] = $page; }
    }
    $base = forum_ext_setting('site_url');
    if ($meta['index'] && $base !== '') {
        // Use the administrator's configured origin, never the request Host header.
        $meta['url'] = $base . '/index.php' . ($params ? '?' . http_build_query($params) : '');
        $logo = forum_ext_brand_logo_url();
        if ($logo !== '') { $meta['image'] = $base . '/' . ltrim($logo, '/'); }
    }
    return $meta;
}

function forum_seo_render(array $meta): void
{
    ?>
    <meta name="robots" content="<?php echo $meta['index'] ? 'index,follow,max-image-preview:large' : 'noindex,nofollow'; ?>">
    <meta property="og:site_name" content="<?php echo forum_escape(forum_ext_setting('brand_name')); ?>">
    <meta property="og:title" content="<?php echo forum_escape($meta['title']); ?>">
    <meta property="og:description" content="<?php echo forum_escape($meta['description']); ?>">
    <meta property="og:type" content="<?php echo forum_escape($meta['type']); ?>">
    <meta property="og:locale" content="<?php echo ['pl' => 'pl_PL', 'en' => 'en_US', 'de' => 'de_DE'][forum_ext_current_language()]; ?>">
    <?php if ($meta['url'] !== ''): ?>
      <link rel="canonical" href="<?php echo forum_escape($meta['url']); ?>">
      <meta property="og:url" content="<?php echo forum_escape($meta['url']); ?>">
    <?php endif; ?>
    <?php if ($meta['image'] !== ''): ?><meta property="og:image" content="<?php echo forum_escape($meta['image']); ?>"><meta property="og:image:alt" content="<?php echo forum_escape(forum_ext_setting('brand_name')); ?>"><?php endif; ?>
    <?php
}

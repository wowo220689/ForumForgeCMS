<?php
declare(strict_types=1);

function forum_ext_search_query(?string $query): string
{
    $query = trim((string) $query);
    $query = preg_replace('/\s+/u', ' ', $query) ?? $query;

    if (function_exists('mb_substr')) {
        return mb_substr($query, 0, 80, 'UTF-8');
    }

    return substr($query, 0, 80);
}

function forum_ext_search_query_is_valid(string $query): bool
{
    if ($query === '') {
        return false;
    }

    if (function_exists('mb_strlen')) {
        return mb_strlen($query, 'UTF-8') >= 2;
    }

    return strlen($query) >= 2;
}

function forum_ext_search_like_pattern(string $query): string
{
    $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $query);
    return '%' . $escaped . '%';
}

function forum_ext_count_search_results(string $query): int
{
    if (!forum_ext_search_query_is_valid($query)) {
        return 0;
    }

    $stmt = forum_db()->prepare(
        <<<'SQL'
        SELECT SUM(match_count)
         FROM (
            SELECT COUNT(*) AS match_count
            FROM topics t
            WHERE t.title LIKE :pattern ESCAPE '\'

            UNION ALL

            SELECT COUNT(*) AS match_count
            FROM posts p
            WHERE p.body LIKE :pattern ESCAPE '\'
         )
        SQL
    );
    $stmt->execute([':pattern' => forum_ext_search_like_pattern($query)]);

    return (int) $stmt->fetchColumn();
}

function forum_ext_search_results(string $query, int $page = 1, int $perPage = FORUM_SEARCH_RESULTS_PER_PAGE): array
{
    if (!forum_ext_search_query_is_valid($query)) {
        return [];
    }

    $offset = (max(1, $page) - 1) * max(1, $perPage);
    $stmt = forum_db()->prepare(
        <<<'SQL'
        SELECT *
         FROM (
            SELECT
                "topic" AS result_type,
                t.id AS topic_id,
                NULL AS post_id,
                t.title AS topic_title,
                "" AS matched_text,
                c.id AS category_id,
                c.name AS category_name,
                u.username AS author_username,
                t.created_at AS result_at,
                1 AS post_position,
                (
                    SELECT COUNT(*)
                    FROM posts p_count
                    WHERE p_count.topic_id = t.id
                ) AS post_count
            FROM topics t
            INNER JOIN categories c ON c.id = t.category_id
            INNER JOIN users u ON u.id = t.user_id
            WHERE t.title LIKE :pattern ESCAPE '\'

            UNION ALL

            SELECT
                "post" AS result_type,
                t.id AS topic_id,
                p.id AS post_id,
                t.title AS topic_title,
                p.body AS matched_text,
                c.id AS category_id,
                c.name AS category_name,
                u.username AS author_username,
                p.created_at AS result_at,
                (
                    SELECT COUNT(*)
                    FROM posts p_before
                    WHERE p_before.topic_id = p.topic_id
                      AND (
                        p_before.created_at < p.created_at
                        OR (p_before.created_at = p.created_at AND p_before.id <= p.id)
                      )
                ) AS post_position,
                NULL AS post_count
            FROM posts p
            INNER JOIN topics t ON t.id = p.topic_id
            INNER JOIN categories c ON c.id = t.category_id
            INNER JOIN users u ON u.id = p.user_id
            WHERE p.body LIKE :pattern ESCAPE '\'
         )
         ORDER BY result_at DESC, topic_id DESC, post_id DESC
         LIMIT :limit OFFSET :offset
        SQL
    );
    $stmt->bindValue(':pattern', forum_ext_search_like_pattern($query), PDO::PARAM_STR);
    $stmt->bindValue(':limit', max(1, $perPage), PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $rows = $stmt->fetchAll();
    foreach ($rows as &$row) {
        if (($row['result_type'] ?? '') === 'post') {
            $row['post_page'] = max(1, (int) ceil(((int) ($row['post_position'] ?? 1)) / FORUM_POSTS_PER_PAGE));
        } else {
            $row['post_page'] = 1;
        }
    }
    unset($row);

    return $rows;
}

function forum_ext_search_excerpt(string $text, string $query, int $length = 180): string
{
    $text = trim(strip_tags((string) preg_replace('/\[(\/?)[a-z0-9=:#,\s-]+\]/iu', ' ', $text)));
    $text = preg_replace('/\s+/u', ' ', $text) ?? $text;
    if ($text === '') {
        return '';
    }

    $lowerText = function_exists('mb_strtolower') ? mb_strtolower($text, 'UTF-8') : strtolower($text);
    $lowerQuery = function_exists('mb_strtolower') ? mb_strtolower($query, 'UTF-8') : strtolower($query);
    $position = function_exists('mb_stripos') ? mb_stripos($lowerText, $lowerQuery, 0, 'UTF-8') : stripos($lowerText, $lowerQuery);

    if ($position === false || $position <= 40) {
        return forum_ext_excerpt($text, $length);
    }

    $start = max(0, (int) $position - 50);
    if (function_exists('mb_substr')) {
        return '…' . forum_ext_excerpt(mb_substr($text, $start, null, 'UTF-8'), $length);
    }

    return '...' . forum_ext_excerpt(substr($text, $start), $length);
}

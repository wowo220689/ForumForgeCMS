<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require __DIR__ . '/ext_schema.php';
require __DIR__ . '/ext_social.php';
require __DIR__ . '/ext_comms.php';

$userId = (int) ($_GET['id'] ?? 0);

try {
    forum_ext_output_avatar_image($userId);
} catch (Throwable $e) {
    header('Content-Type: image/svg+xml; charset=UTF-8');
    echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 160 160"><rect width="160" height="160" rx="32" fill="#0f766e"/><text x="50%" y="54%" text-anchor="middle" dominant-baseline="middle" font-family="Segoe UI, Arial, sans-serif" font-size="64" font-weight="700" fill="#ffffff">?</text></svg>';
}

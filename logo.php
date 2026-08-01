<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require __DIR__ . '/ext_schema.php';
require __DIR__ . '/ext_social.php';

try {
    forum_ext_output_brand_logo_image();
} catch (Throwable $e) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Logo nie jest dostepne.';
}

<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require __DIR__ . '/ext_schema.php';
require __DIR__ . '/ext_social.php';
require_once __DIR__ . '/ext_antispam.php';

try {
    forum_ext_boot();
    forum_ext_current_language();
    forum_antispam_enforce_ip();
    forum_ext_output_brand_logo_image();
} catch (Throwable $e) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    echo forum_t('Logo nie jest dostępne.');
}

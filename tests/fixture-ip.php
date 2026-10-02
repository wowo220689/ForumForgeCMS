<?php
declare(strict_types=1);

$fixture = realpath($argv[1] ?? '');
if (!$fixture || !str_starts_with(basename($fixture), 'forumforgecms-test-') || dirname($fixture) !== realpath(sys_get_temp_dir())) {
    throw new RuntimeException('A disposable test fixture is required.');
}
require $fixture . '/bootstrap.php';
require $fixture . '/ext_schema.php';
forum_ext_boot();
forum_ext_store_setting(forum_db(), 'antispam_ips', ($argv[2] ?? '') === 'block' ? '127.0.0.1' : '');
echo "Fixture IP policy changed.\n";

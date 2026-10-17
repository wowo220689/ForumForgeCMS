<?php
declare(strict_types=1);

$fixture = realpath($argv[1] ?? '');
if (!$fixture || !str_starts_with(basename($fixture), 'forumforgecms-test-') || dirname($fixture) !== realpath(sys_get_temp_dir())) {
    throw new RuntimeException('A disposable test fixture is required.');
}
require $fixture . '/bootstrap.php';
require $fixture . '/ext_schema.php';
forum_ext_boot();
if (($argv[2] ?? '') === 'reset') {
    forum_db()->exec('DELETE FROM login_guards');
    echo "RESET\n";
    exit;
}
$_SERVER['REMOTE_ADDR'] = '203.0.113.142';
try {
    forum_security_login_reserve('concurrent-attempt', null);
    echo "ALLOWED\n";
} catch (RuntimeException $e) {
    if ($e->getMessage() !== forum_t('security.login_locked')) { throw $e; }
    echo "BLOCKED\n";
}

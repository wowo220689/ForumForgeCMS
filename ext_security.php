<?php
declare(strict_types=1);

const FORUM_LOGIN_WINDOW = 900;
const FORUM_LOGIN_ACCOUNT_LIMIT = 8;
const FORUM_LOGIN_IP_LIMIT = 30;

function forum_security_schema(PDO $pdo): void
{
    $pdo->exec('CREATE TABLE IF NOT EXISTS login_guards (
        key_hash TEXT PRIMARY KEY, attempts INTEGER NOT NULL,
        window_started INTEGER NOT NULL, blocked_until INTEGER NOT NULL DEFAULT 0
    )');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_login_guards_window ON login_guards(window_started)');
}

function forum_security_account_key(string $login, ?array $user): string
{
    return hash('sha256', 'account:' . ($user ? 'id:' . $user['id'] : 'login:' . strtolower(trim($login))));
}

function forum_security_login_reserve(string $login, ?array $user): array
{
    $keys = [
        forum_security_account_key($login, $user) => FORUM_LOGIN_ACCOUNT_LIMIT,
        hash('sha256', 'ip:' . (forum_antispam_ip() ?: 'unknown')) => FORUM_LOGIN_IP_LIMIT,
    ];
    $pdo = forum_db();
    $now = time();
    // Reserve attempts before password verification, serializing simultaneous requests.
    $pdo->exec('BEGIN IMMEDIATE');
    try {
        $pdo->prepare('DELETE FROM login_guards WHERE window_started < :before AND blocked_until <= :now')
            ->execute([':before' => $now - 86400, ':now' => $now]);
        $select = $pdo->prepare('SELECT * FROM login_guards WHERE key_hash = :key');
        $upsert = $pdo->prepare('INSERT INTO login_guards (key_hash, attempts, window_started, blocked_until)
            VALUES (:key, :attempts, :started, :blocked)
            ON CONFLICT(key_hash) DO UPDATE SET attempts=excluded.attempts, window_started=excluded.window_started, blocked_until=excluded.blocked_until');
        foreach ($keys as $key => $limit) {
            $select->execute([':key' => $key]);
            $row = $select->fetch();
            if ($row && (int) $row['blocked_until'] > $now) { throw new RuntimeException(forum_t('security.login_locked')); }
            $fresh = !$row || (int) $row['window_started'] <= $now - FORUM_LOGIN_WINDOW;
            $attempts = $fresh ? 1 : (int) $row['attempts'] + 1;
            $upsert->execute([':key' => $key, ':attempts' => $attempts, ':started' => $fresh ? $now : (int) $row['window_started'], ':blocked' => $attempts >= $limit ? $now + FORUM_LOGIN_WINDOW : 0]);
        }
        $pdo->exec('COMMIT');
    } catch (Throwable $e) {
        $pdo->exec('ROLLBACK');
        throw $e;
    }
    return array_keys($keys);
}

function forum_security_login_succeeded(array $keys): void
{
    $pdo = forum_db();
    $pdo->beginTransaction();
    try {
        $pdo->prepare('DELETE FROM login_guards WHERE key_hash = :key')->execute([':key' => $keys[0]]);
        $pdo->prepare('UPDATE login_guards SET attempts = MAX(0, attempts - 1),
            blocked_until = CASE WHEN attempts - 1 < :limit THEN 0 ELSE blocked_until END WHERE key_hash = :key')
            ->execute([':key' => $keys[1], ':limit' => FORUM_LOGIN_IP_LIMIT]);
        $pdo->commit();
    } catch (Throwable $e) { $pdo->rollBack(); throw $e; }
}

function forum_security_clear_account(int $userId): void
{
    forum_db()->prepare('DELETE FROM login_guards WHERE key_hash = :key')
        ->execute([':key' => forum_security_account_key('', ['id' => $userId])]);
}

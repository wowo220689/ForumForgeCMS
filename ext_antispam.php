<?php
declare(strict_types=1);

function forum_antispam_ip(?string $value = null): string
{
    $value = $value ?? (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    if (!filter_var($value, FILTER_VALIDATE_IP)) {
        return '';
    }
    return (string) inet_ntop(inet_pton($value));
}

function forum_antispam_lines(string $value): array
{
    return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $value) ?: []), static fn($line) => $line !== ''));
}

function forum_antispam_ip_blocked(string $ip, array $blocked): bool
{
    $ip = forum_antispam_ip($ip);
    return $ip !== '' && in_array($ip, $blocked, true);
}

function forum_antispam_enforce_ip(): void
{
    // Trust only the server address, not client-provided forwarding headers.
    if (!forum_antispam_ip_blocked(forum_antispam_ip(), forum_antispam_lines(forum_ext_setting('antispam_ips')))) {
        return;
    }
    http_response_code(403);
    header('Content-Type: text/plain; charset=UTF-8');
    echo forum_t('Dostęp z tego adresu IP jest zablokowany.');
    exit;
}

function forum_antispam_check_identity(string $username, string $email): void
{
    $names = array_map('strtolower', forum_antispam_lines(forum_ext_setting('antispam_usernames')));
    if (in_array(strtolower($username), $names, true)) {
        throw new RuntimeException(forum_t('Ta nazwa użytkownika jest zablokowana.'));
    }
    $domain = strtolower(substr(strrchr($email, '@') ?: '', 1));
    foreach (forum_antispam_lines(forum_ext_setting('antispam_domains')) as $blocked) {
        if ($domain === $blocked || str_ends_with($domain, '.' . $blocked)) {
            throw new RuntimeException(forum_t('Rejestracja z tej domeny e-mail jest zablokowana.'));
        }
    }
}

function forum_antispam_challenge(): array
{
    $now = time();
    $challenges = array_filter($_SESSION['forum_antispam_challenges'] ?? [], static fn($item) => $item['expires'] >= $now);
    $challenges = array_slice($challenges, -7, null, true);
    $left = random_int(1, 9);
    $right = random_int(1, 9);
    $token = bin2hex(random_bytes(16));
    $challenges[$token] = ['answer' => (string) ($left + $right), 'expires' => $now + 600];
    $_SESSION['forum_antispam_challenges'] = $challenges;
    return ['token' => $token, 'question' => sprintf(forum_t('Ile wynosi %d + %d?'), $left, $right)];
}

function forum_antispam_validate_answer(string $token, string $answer): void
{
    if (forum_ext_setting('antispam_question_enabled') !== '1') {
        return;
    }
    $challenge = $_SESSION['forum_antispam_challenges'][$token] ?? null;
    unset($_SESSION['forum_antispam_challenges'][$token]);
    if (!is_array($challenge) || $challenge['expires'] < time() || !hash_equals($challenge['answer'], trim($answer))) {
        throw new RuntimeException(forum_t('Odpowiedź na pytanie antyspamowe jest niepoprawna lub wygasła.'));
    }
}

function forum_antispam_normalize_list(string $value, string $type, string $currentIp = ''): string
{
    if (strlen($value) > 65536) {
        throw new RuntimeException(forum_t('Lista blokad jest za długa.'));
    }
    $lines = forum_antispam_lines($value);
    if (count($lines) > 1000) {
        throw new RuntimeException(forum_t('Lista blokad jest za długa.'));
    }
    $normalized = [];
    foreach ($lines as $line) {
        $entry = strtolower($line);
        if ($type === 'ips') {
            $entry = forum_antispam_ip($line);
            $valid = $entry !== '';
            if ($valid && $entry === forum_antispam_ip($currentIp)) {
                throw new RuntimeException(forum_t('Nie można zablokować bieżącego adresu IP administratora.'));
            }
        } elseif ($type === 'usernames') {
            $valid = (bool) preg_match('/^[a-z0-9._-]{3,40}$/D', $entry);
            if ($entry === strtolower(FORUM_ADMIN_USERNAME)) {
                throw new RuntimeException(forum_t('Nie można zablokować nazwy głównego administratora.'));
            }
        } else {
            $valid = strlen($entry) <= 253 && (bool) preg_match('/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z](?:[a-z0-9-]{0,61}[a-z0-9])?$/D', $entry);
        }
        if (!$valid) {
            throw new RuntimeException(sprintf(forum_t('Nieprawidłowy wpis na liście blokad: %s'), $line));
        }
        $normalized[] = $entry;
    }
    return implode("\n", array_unique($normalized));
}

function forum_antispam_save(array $input): void
{
    $values = ['antispam_question_enabled' => !empty($input['antispam_question_enabled']) ? '1' : '0'];
    foreach (['domains', 'usernames', 'ips'] as $type) {
        $key = 'antispam_' . $type;
        $values[$key] = forum_antispam_normalize_list((string) ($input[$key] ?? ''), $type, forum_antispam_ip());
    }
    $pdo = forum_db();
    $pdo->beginTransaction();
    try {
        foreach ($values as $key => $value) {
            forum_ext_store_setting($pdo, $key, $value);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function forum_antispam_render_settings(): void
{
    ?>
    <section class="panel forum-panel">
      <span class="eyebrow"><?php echo forum_escape(forum_t('Ochrona przed spamem')); ?></span>
      <h2><?php echo forum_escape(forum_t('Ochrona przed spamem')); ?></h2>
      <form class="forum-form" method="post" action="<?php echo forum_url(['view' => 'admin', 'section' => 'antispam']); ?>">
        <input type="hidden" name="action" value="update_antispam">
        <input type="hidden" name="csrf_token" value="<?php echo forum_escape(forum_csrf_token()); ?>">
        <label class="checkbox-row"><input type="checkbox" name="antispam_question_enabled" value="1" <?php echo forum_ext_setting('antispam_question_enabled') === '1' ? 'checked' : ''; ?>><span><?php echo forum_escape(forum_t('Pytanie antyspamowe przy rejestracji')); ?></span></label>
        <?php foreach ([
            'domains' => ['Zablokowane domeny e-mail', 'Jedna domena w wierszu. Blokada obejmuje również subdomeny.'],
            'usernames' => ['Zablokowane nazwy użytkowników', 'Jedna dokładna nazwa w wierszu. Wielkość liter nie ma znaczenia.'],
            'ips' => ['Zablokowane adresy IP', 'Jeden adres IPv4 lub IPv6 w wierszu. Blokada dotyczy dostępu do forum.'],
        ] as $type => [$label, $help]): ?>
          <label for="antispam-<?php echo $type; ?>"><?php echo forum_escape(forum_t($label)); ?></label>
          <textarea id="antispam-<?php echo $type; ?>" name="antispam_<?php echo $type; ?>" maxlength="65536" rows="5" aria-describedby="antispam-help-<?php echo $type; ?>"><?php echo forum_escape(forum_ext_setting('antispam_' . $type)); ?></textarea>
          <p id="antispam-help-<?php echo $type; ?>" class="forum-admin-note"><?php echo forum_escape(forum_t($help)); ?></p>
        <?php endforeach; ?>
        <button class="button" type="submit"><?php echo forum_escape(forum_t('Zapisz ochronę przed spamem')); ?></button>
      </form>
    </section>
    <?php
}

<?php
declare(strict_types=1);

function forum_ext_backup_filename(): string
{
    return 'ForumForgeCMS-backup-' . date('Ymd-His') . '.zip';
}

function forum_ext_backup_allowed_path(string $path): bool
{
    $normalized = str_replace('\\', '/', $path);
    if ($normalized === 'forum-data/forum.sqlite') {
        return true;
    }

    if (str_starts_with($normalized, 'forum-data/avatars/') || str_starts_with($normalized, 'forum-data/brand/')) {
        return !str_contains($normalized, '../') && !str_starts_with($normalized, '/');
    }

    return false;
}

function forum_ext_backup_collect_files(string $directory, string $archivePrefix): array
{
    if (!is_dir($directory)) {
        return [];
    }

    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        if (!$item instanceof SplFileInfo || !$item->isFile()) {
            continue;
        }

        $path = $item->getPathname();
        $relative = str_replace('\\', '/', substr($path, strlen($directory) + 1));
        $files[] = [
            'path' => $path,
            'archive' => rtrim($archivePrefix, '/') . '/' . $relative,
        ];
    }

    return $files;
}

function forum_ext_zip_datetime(int $timestamp): array
{
    $date = getdate($timestamp);
    $dosTime = (($date['hours'] & 0x1f) << 11) | (($date['minutes'] & 0x3f) << 5) | ((int) floor($date['seconds'] / 2) & 0x1f);
    $dosDate = ((max(1980, $date['year']) - 1980) << 9) | (($date['mon'] & 0x0f) << 5) | ($date['mday'] & 0x1f);

    return [$dosTime, $dosDate];
}

function forum_ext_create_zip_archive(array $files, string $targetFile): void
{
    $handle = fopen($targetFile, 'wb');
    if (!is_resource($handle)) {
        throw new RuntimeException(forum_t('Nie udalo sie przygotowac pliku ZIP kopii zapasowej.'));
    }

    $centralDirectory = '';
    $offset = 0;

    foreach ($files as $file) {
        $sourcePath = (string) ($file['path'] ?? '');
        $archiveName = str_replace('\\', '/', (string) ($file['archive'] ?? ''));
        if ($sourcePath === '' || $archiveName === '' || !is_file($sourcePath)) {
            continue;
        }

        $content = (string) file_get_contents($sourcePath);
        $size = strlen($content);
        $crc = crc32($content);
        [$dosTime, $dosDate] = forum_ext_zip_datetime((int) filemtime($sourcePath));

        $localHeader = pack('VvvvvvVVVvv', 0x04034b50, 20, 0, 0, $dosTime, $dosDate, $crc, $size, $size, strlen($archiveName), 0);
        fwrite($handle, $localHeader . $archiveName . $content);

        $centralDirectory .= pack(
            'VvvvvvvVVVvvvvvVV',
            0x02014b50,
            20,
            20,
            0,
            0,
            $dosTime,
            $dosDate,
            $crc,
            $size,
            $size,
            strlen($archiveName),
            0,
            0,
            0,
            0,
            0,
            $offset
        ) . $archiveName;

        $offset += strlen($localHeader) + strlen($archiveName) + $size;
    }

    $centralOffset = $offset;
    fwrite($handle, $centralDirectory);
    $centralSize = strlen($centralDirectory);
    $fileCount = count($files);
    fwrite($handle, pack('VvvvvVVv', 0x06054b50, 0, 0, $fileCount, $fileCount, $centralSize, $centralOffset, 0));
    fclose($handle);
}

function forum_ext_download_backup(): void
{
    $pdo = forum_db();
    try {
        $pdo->exec('PRAGMA wal_checkpoint(TRUNCATE)');
    } catch (Throwable $e) {
        // Some shared hosts keep SQLite on filesystems that do not allow this checkpoint mode.
    }

    $tempFile = tempnam(sys_get_temp_dir(), 'ffc-backup-');
    if ($tempFile === false) {
        throw new RuntimeException(forum_t('Nie udalo sie utworzyc pliku tymczasowego kopii zapasowej.'));
    }

    $files = [];
    if (is_file(FORUM_DB_PATH)) {
        $files[] = ['path' => FORUM_DB_PATH, 'archive' => 'forum-data/forum.sqlite'];
    }
    $files = array_merge(
        $files,
        forum_ext_backup_collect_files(FORUM_AVATAR_DIR, 'forum-data/avatars'),
        forum_ext_backup_collect_files(FORUM_BRAND_DIR, 'forum-data/brand')
    );

    $infoFile = tempnam(sys_get_temp_dir(), 'ffc-info-');
    if ($infoFile !== false) {
        file_put_contents($infoFile, "ForumForgeCMS backup\nVersion: " . FORUM_VERSION . "\nCreated: " . forum_now() . "\n");
        $files[] = ['path' => $infoFile, 'archive' => 'backup-info.txt'];
    }

    forum_ext_create_zip_archive($files, $tempFile);
    if ($infoFile !== false) {
        @unlink($infoFile);
    }

    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . forum_ext_backup_filename() . '"');
    header('Content-Length: ' . (string) filesize($tempFile));
    header('X-Content-Type-Options: nosniff');
    readfile($tempFile);
    @unlink($tempFile);
    exit;
}

function forum_ext_clean_directory_contents(string $directory): void
{
    if (!is_dir($directory)) {
        if (!mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException(forum_t('Nie udalo sie przygotowac katalogu danych do przywracania.'));
        }
        return;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($iterator as $item) {
        if (!$item instanceof SplFileInfo) {
            continue;
        }

        if ($item->isDir()) {
            @rmdir($item->getPathname());
        } elseif (!in_array($item->getFilename(), ['.gitkeep'], true)) {
            @unlink($item->getPathname());
        }
    }
}

function forum_ext_restore_file_contents(string $archiveName, string $content): void
{
    $relative = substr(str_replace('\\', '/', $archiveName), strlen('forum-data/'));
    $targetPath = FORUM_DATA_DIR . '/' . $relative;
    $directory = dirname($targetPath);
    if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
        throw new RuntimeException(forum_t('Nie udalo sie utworzyc katalogu docelowego podczas przywracania.'));
    }

    if (file_put_contents($targetPath, $content, LOCK_EX) === false) {
        throw new RuntimeException(forum_t('Nie udalo sie zapisac pliku podczas przywracania.'));
    }
}

function forum_ext_restore_backup_with_zip_archive(string $tmpName): void
{
    $zip = new ZipArchive();
    if ($zip->open($tmpName) !== true) {
        throw new RuntimeException(forum_t('Plik kopii zapasowej nie jest poprawnym archiwum ZIP.'));
    }

    $hasDatabase = false;
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $name = (string) $zip->getNameIndex($i);
        if ($name === '' || str_ends_with($name, '/')) {
            continue;
        }
        if (!forum_ext_backup_allowed_path($name) && $name !== 'backup-info.txt') {
            $zip->close();
            throw new RuntimeException(forum_t('Kopia zawiera niedozwolone pliki. Przywracanie zostalo przerwane.'));
        }
        if ($name === 'forum-data/forum.sqlite') {
            $hasDatabase = true;
        }
    }

    if (!$hasDatabase) {
        $zip->close();
        throw new RuntimeException(forum_t('Kopia zapasowa nie zawiera pliku forum-data/forum.sqlite.'));
    }

    forum_ext_clean_directory_contents(FORUM_AVATAR_DIR);
    forum_ext_clean_directory_contents(FORUM_BRAND_DIR);

    for ($i = 0; $i < $zip->numFiles; $i++) {
        $name = (string) $zip->getNameIndex($i);
        if ($name === '' || str_ends_with($name, '/') || !forum_ext_backup_allowed_path($name)) {
            continue;
        }
        forum_ext_restore_file_contents($name, (string) $zip->getFromName($name));
    }

    $zip->close();
}

function forum_ext_restore_backup_with_phar(string $tmpName): void
{
    if (!class_exists('PharData')) {
        throw new RuntimeException(forum_t('Na serwerze brakuje obslugi archiwow ZIP potrzebnej do przywracania kopii.'));
    }

    $phar = new PharData($tmpName);
    $files = [];
    $hasDatabase = false;
    $iterator = new RecursiveIteratorIterator($phar, RecursiveIteratorIterator::SELF_FIRST);
    $pharPrefix = 'phar://' . str_replace('\\', '/', $tmpName) . '/';

    foreach ($iterator as $item) {
        if (!$item instanceof SplFileInfo || !$item->isFile()) {
            continue;
        }

        $name = str_replace('\\', '/', $item->getPathname());
        if (str_starts_with($name, $pharPrefix)) {
            $name = substr($name, strlen($pharPrefix));
        }
        if (!forum_ext_backup_allowed_path($name) && $name !== 'backup-info.txt') {
            throw new RuntimeException(forum_t('Kopia zawiera niedozwolone pliki. Przywracanie zostalo przerwane.'));
        }
        if ($name === 'forum-data/forum.sqlite') {
            $hasDatabase = true;
        }
        $files[] = [$name, (string) file_get_contents($item->getPathname())];
    }

    if (!$hasDatabase) {
        throw new RuntimeException(forum_t('Kopia zapasowa nie zawiera pliku forum-data/forum.sqlite.'));
    }

    forum_ext_clean_directory_contents(FORUM_AVATAR_DIR);
    forum_ext_clean_directory_contents(FORUM_BRAND_DIR);
    foreach ($files as [$name, $content]) {
        if (forum_ext_backup_allowed_path($name)) {
            forum_ext_restore_file_contents($name, $content);
        }
    }
}

function forum_ext_restore_backup(array $file): void
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException(forum_t('Nie wybrano poprawnego pliku kopii zapasowej.'));
    }

    $tmpName = (string) ($file['tmp_name'] ?? '');
    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        throw new RuntimeException(forum_t('Nie udalo sie odebrac przeslanego pliku kopii zapasowej.'));
    }

    if (class_exists('ZipArchive')) {
        forum_ext_restore_backup_with_zip_archive($tmpName);
    } else {
        forum_ext_restore_backup_with_phar($tmpName);
    }

    unset($GLOBALS['forum_ext_settings_cache']);
}

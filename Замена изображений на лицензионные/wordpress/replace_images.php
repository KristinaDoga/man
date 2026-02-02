<?php

/**
 * Конфигурация
 */
$domains_dir = __DIR__; // где лежат папки ekb.czm.su / mcmk.su / fond.su
$root_dir = __DIR__ . "/public_html"; // корень сайта
$backup_dir = __DIR__ . "/backup_originals"; // куда сохранять оригиналы
$log_file = __DIR__ . "/replace-log.txt";

function log_msg($msg, $log_file) {
    file_put_contents($log_file, $msg . "\n", FILE_APPEND);
    echo $msg . "\n";
}

/**
 * Удаляем ресайзы, webp-версии и .bk файлы
 */
function remove_related_images($dst, $log_file) {
    $dir = dirname($dst);
    $orig_name = basename($dst);
    $orig_base = pathinfo($orig_name, PATHINFO_FILENAME); // main_background
    $orig_ext  = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));

    if (!is_dir($dir)) return;

    $files = scandir($dir);
    if (!$files) return;

    foreach ($files as $f) {
        if ($f === "." || $f === "..") continue;
        $full = $dir . "/" . $f;

        // не файл → пропускаем
        if (!is_file($full)) continue;

        $lower = strtolower($f);

        // НЕ удаляем сам оригинал
        if ($lower === strtolower($orig_name)) continue;

        // МАСКИ для удаления
        $patterns = [
            // ресайзы: main_background-420x468.jpg
            "/^" . preg_quote($orig_base, "/") . "-\d+x\d+\.$orig_ext$/",

            // webp версии ресайзов: main_background-420x468.jpg.webp
            "/^" . preg_quote($orig_base, "/") . "-\d+x\d+.*\.webp$/",

            // webp оригинала: main_background.jpg.webp
            "/^" . preg_quote($orig_base, "/") . "\.$orig_ext\.webp$/",

            // .bk: main_background.bk.jpg
            "/^" . preg_quote($orig_base, "/") . "\.bk\.$orig_ext$/",

            // .bk.webp
            "/^" . preg_quote($orig_base, "/") . ".*\.bk\.webp$/",
        ];

        foreach ($patterns as $p) {
            if (preg_match($p, $lower)) {
                @unlink($full);
                log_msg("REMOVED: $full", $log_file);
                break;
            }
        }
    }
}

/**
 * Копирование оригинала
 */
function copy_file($src, $dst, $backup_dir, $log_file) {

    // Только изображения
    $allowed_ext = ['jpg','jpeg','png','gif','webp','bmp','svg'];
    $ext = strtolower(pathinfo($src, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_ext)) {
        log_msg("SKIP non-image: $src", $log_file);
        return;
    }

    // Бэкапим ТОЛЬКО оригинал
    if (file_exists($dst)) {
        $backup_path = $backup_dir . '/' . ltrim($dst, '/');
        @mkdir(dirname($backup_path), 0775, true);

        if (!copy($dst, $backup_path)) {
            log_msg("ERROR backup failed: $dst", $log_file);
            return;
        }
        log_msg("BACKUP: $backup_path", $log_file);
    }

    // Перед заменой — удаляем ресайзы и кэшты
    remove_related_images($dst, $log_file);

    // Создаём директорию
    @mkdir(dirname($dst), 0775, true);

    // Заменяем оригинал
    if (copy($src, $dst)) {
        log_msg("REPLACED: $dst", $log_file);
    } else {
        log_msg("ERROR replacing file: $dst", $log_file);
    }
}


//------------------------------------------------------------
// Проход по доменам
//------------------------------------------------------------

$domains = array_filter(scandir($domains_dir), function($d){
    return !in_array($d, ['.','..','public_html','backup_originals']);
});

foreach ($domains as $domain) {
    $domain_path = $domains_dir . "/" . $domain;

    if (!is_dir($domain_path)) continue;

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($domain_path, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $file) {
        if ($file->isFile()) {
            // относительный путь внутри доменной папки
            $rel = substr($file->getPathname(), strlen($domain_path));
            $src = $file->getPathname();
            $dst = $root_dir . $rel;

            copy_file($src, $dst, $backup_dir, $log_file);
        }
    }
}

echo "Done.\n";

<?php
// Получаем путь к public_html
$targetDir = $argv[1] ?? null;

if (!$targetDir || !is_dir($targetDir)) {
    file_put_contents(__DIR__ . '/clear_error.log', "[" . date('Y-m-d H:i:s') . "] Не передана директория или не найдена\n", FILE_APPEND);
    exit(1);
}

// Меняем текущую директорию
chdir($targetDir);

// $baseDir = __DIR__; // Или укажите вручную путь к каталогу
$baseDir = getcwd();

// изменение прав на 755/644
$excludeDirs = ['vendor', 'node_modules', '.git'];
$excludeExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'mp4', 'mp3', 'avi', 'pdf', 'zip', 'rar', 'gz'];
$specialDirs = ['storage', 'cache', 'logs'];

function fixPermissions($path, $excludeDirs, $excludeExtensions, $specialDirs) {
    // Проверка, нужно ли исключить текущую папку
    foreach ($excludeDirs as $excl) {
        if (strpos($path, DIRECTORY_SEPARATOR . $excl) !== false) {
            return;
        }
    }

    // Если это каталог
    if (is_dir($path)) {
        $isSpecial = false;
        foreach ($specialDirs as $special) {
            if (preg_match('#/' . preg_quote($special) . '(/|$)#', $path)) {
                chmod($path, 0775);
                $isSpecial = true;
                break;
            }
        }

        if (!$isSpecial) {
            chmod($path, 0755);
        }

        $items = scandir($path);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            fixPermissions($path . DIRECTORY_SEPARATOR . $item, $excludeDirs, $excludeExtensions, $specialDirs);
        }

        } else {
        if (!is_file($path)) return;

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (!in_array($ext, $excludeExtensions)) {
            chmod($path, 0644);
        }
    }

}



// удаление function _0x
function removeObfuscatedFunctions($dir) {
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

    foreach ($rii as $file) {
        if ($file->isDir()) continue;

        $filePath = $file->getPathname();
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);

        if (!in_array($ext, ['php', 'js'])) continue;

        $content = file_get_contents($filePath);
        $newContent = preg_replace('/^\s*function\s+_0x.*$/m', '', $content);

        if ($content !== $newContent) {
            file_put_contents($filePath, $newContent);
            echo "Очищено (function _0x): $filePath\n";
        }
    }
}
// удаление script function _0x

function removeObfuscatedScripts($dir) {
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

    foreach ($rii as $file) {
        if ($file->isDir()) continue;

        $filePath = $file->getPathname();
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);

        if (!in_array($ext, ['php', 'html', 'htm', 'js'])) continue;

        $content = file_get_contents($filePath);
        $originalContent = $content;

        // Удалить <script>...</script>, если внутри есть function _0x...
        $content = preg_replace_callback(
            '#<script\b[^>]*>.*?</script>#is',
            function ($match) {
                return (strpos($match[0], 'function _0x') !== false) ? '' : $match[0];
            },
            $content
        );

        if ($content !== $originalContent) {
            file_put_contents($filePath, $content);
            echo "Очищено (script с function _0x): $filePath\n";
        }
    }
}

// удаление файлов с stt1
function findAndDeleteMaliciousPHP($dir) {
    $deletedFiles = [];

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if (strtolower($file->getExtension()) !== 'php') {
            continue;
        }

        $content = file_get_contents($file->getPathname());

        if (strpos($content, "<?php\n\$stt1 = \"") !== false) {
            if (unlink($file->getPathname())) {
                $deletedFiles[] = $file->getPathname();
            }
        }
    }

    return $deletedFiles;
}

// удаление "ob_start(); ? >" <мусорная строка>"
function cleanObStartGarbage($dir) {
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    echo "Ищем ob_start + мусор...\n";

    foreach ($rii as $file) {
        if ($file->isDir()) continue;

        $filePath = $file->getPathname(); // Полный путь к файлу
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION)); // Расширение
        if ($ext !== 'php') continue; // Только PHP-файлы

        $content = file_get_contents($filePath); // Содержимое файла

        // Паттерн: ob_start(); ? > + строка с "мусором"
        $pattern = '/ob_start\s*\(\s*\)\s*;\s*\?>\s*[^\w\s<][^\x00-\x7F\r\n]{3,};?\s*/u';

        $newContent = preg_replace($pattern, '', $content); // Удаление

        if ($newContent !== $content) {
            file_put_contents($filePath, $newContent); // Перезапись
            echo "Очищен (ob_start + мусор): $filePath\n";
        }
    }
}


fixPermissions($baseDir, $excludeDirs, $excludeExtensions, $specialDirs);
echo "Права изменены\n";

removeObfuscatedFunctions($baseDir);

removeObfuscatedScripts($baseDir);

$result = findAndDeleteMaliciousPHP($baseDir);

if (empty($result)) {
    echo "Не найдено файлов stt1.\n";
    } else {
        echo "Удалены следующие файлы (stt1):\n";
        foreach ($result as $file) {
            echo $file . "\n";
        }
    }
cleanObStartGarbage($baseDir);

// Удалить index.htm и index.html

$files = ['index.html', 'index.htm'];
foreach ($files as $file) {
    if (file_exists($file)) {
        if (unlink($file)) {
            echo "Удалён файл: $file\n";
        } else {
            echo "Не удалось удалить: $file\n";
        }
    } else {
        echo "Файл не найден: $file\n";
    }
}

// Заменить htaccess и index.php версией без вируса
$files = ['index.php' => 'index.php__clean','.htaccess' => '.htaccess__clean'];

foreach ($files as $target => $clean) {
    // Удаление оригинальных файлов
    if (file_exists($target)) {
        unlink($target);
        // echo "Удалён: $target\n";
    }

    // Копирование clean-файлов
    if (file_exists($clean)) {
        $copyName = $target . '__1';
        if (copy($clean, $copyName)) {
            // echo "Создана копия: $copyName\n";
        } else {
            echo "Ошибка копирования: $clean → $copyName\n";
            continue;
        }

        // Переименование копии в оригинал
        if (rename($copyName, $target)) {
            echo "Файл восстановлен: $target\n";
        } else {
            echo "Ошибка переименования: $copyName → $target\n";
        }
    } else {
        echo "Файл не найден: $clean\n";
    }
}
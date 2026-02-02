<?php
// Корневая папка для проверки
$baseDir = '/home/admeen/web';

// Время 3 дня назад
$threeDaysAgo = time() - (3 * 24 * 60 * 60);

// Что будем добавлять в конец footer.tpl.php
$appendContent = <<<CONTENT

<style>
.modal_form:before{
    content: "x";
    position: absolute;
    top: 20px;
    right: 20px;
    font-size: 20px;
    font-weight: 600;
}
</style>
<script>
document.addEventListener('click', function (e) {
  const widget = e.target.closest('.modal_form');
  if (!widget) return;

  const rect = widget.getBoundingClientRect();
  // Размер области, где визуально находится "крестик" (::before)
  const areaWidth = 40;  // ширина области справа
  const areaHeight = 40; // высота области сверху

  const clickX = e.clientX;
  const clickY = e.clientY;

  // Проверяем, что клик пришёл в зону ::before (правый верхний угол)
  const inBeforeArea =
    clickX > rect.right - areaWidth &&
    clickY < rect.top + areaHeight;

  if (inBeforeArea) {
    document.querySelectorAll('.modal_overlay, .modal_form')
      .forEach(el => {
        el.style.display = 'none';
      });
  }
});

</script>


CONTENT;

// Список изменённых файлов
$modifiedFiles = [];

// Рекурсивный поиск footer.tpl.php
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($baseDir, FilesystemIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if ($file->getFilename() === 'footer.tpl.php') {
        $filePath = $file->getPathname();
        $mtime = $file->getMTime();

        // Проверяем, был ли файл изменён за последние 3 дня
        if ($mtime >= $threeDaysAgo) {
            // Добавляем в конец файла CSS+JS
            file_put_contents($filePath, $appendContent, FILE_APPEND);
            $modifiedFiles[] = $filePath;
        }
    }
}

// Логирование
$logFile = '/home/admeen/web/footer_update_log_' . date('Y-m-d_H-i-s') . '.txt';
$logContent = "Найдено и обновлено " . count($modifiedFiles) . " файлов:\n\n" . implode("\n", $modifiedFiles);
file_put_contents($logFile, $logContent);

echo nl2br($logContent);
?>

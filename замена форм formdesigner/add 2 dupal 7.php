<?php
/**
 * deploy_forms.php
 * Запускать из CLI: php deploy_forms.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

/* ---------- Источники для копирования ---------- */
$sourceFiles = [
    '/home/admeen/web/rehab-restart.ru/public_html/sites/all/themes/flumb/templates/block--webform--client-block-13.tpl.php',
    '/home/admeen/web/rehab-restart.ru/public_html/sites/all/themes/flumb/templates/block--webform--client-block-43.tpl.php',
    '/home/admeen/web/rehab-restart.ru/public_html/sites/all/themes/flumb/templates/block--webform--client-block-359.tpl.php',
    '/home/admeen/web/rehab-restart.ru/public_html/sites/all/themes/flumb/templates/block--webform--client-block-1396.tpl.php',
];

/* ---------- Списки сайтов (templates каталоги) ---------- */
$siteTemplateDirs = [
    '/home/admeen/web/rehab-restart.ru/public_html/sites/balashiha/themes/flumb/templates',
    '/home/admeen/web/rehab-restart.ru/public_html/sites/elektrostal/themes/flumb/templates',
    '/home/admeen/web/rehab-restart.ru/public_html/sites/himki/themes/flumb/templates',
    '/home/admeen/web/rehab-restart.ru/public_html/sites/korolev/themes/flumb/templates',
    '/home/admeen/web/rehab-restart.ru/public_html/sites/lech-anapa/themes/flumb/templates',
    '/home/admeen/web/rehab-restart.ru/public_html/sites/lechenie-narko/themes/flumb/templates',
    '/home/admeen/web/rehab-restart.ru/public_html/sites/lech-gelend/themes/flumb/templates',
    '/home/admeen/web/rehab-restart.ru/public_html/sites/lech-krymsk/themes/flumb/templates',
    '/home/admeen/web/rehab-restart.ru/public_html/sites/lech-nvr/themes/flumb/templates',
    '/home/admeen/web/rehab-restart.ru/public_html/sites/lyubercy/themes/flumb/templates',
    '/home/admeen/web/rehab-restart.ru/public_html/sites/mytischi/themes/flumb/templates',
    '/home/admeen/web/rehab-restart.ru/public_html/sites/neva/themes/flumb/templates',
    '/home/admeen/web/rehab-restart.ru/public_html/sites/perezagruzka/themes/flumb/templates',
    '/home/admeen/web/rehab-restart.ru/public_html/sites/podolsk/themes/flumb/templates',
    '/home/admeen/web/rehab-restart.ru/public_html/sites/reutov/themes/flumb/templates',
];

/* ---------- Старый блок для точной замены (строгий поиск) ---------- */
$oldBlockExact = <<<HTML
<div class="modals">
\t<span class="modal_overlay"></span>
\t<div class="modal_form agrmnt" id="callorder"><?php block_print('webform','client-block-44'); ?></div>
\t<div class="modal_form agrmnt" id="questionform"><?php block_print('webform','client-block-45'); ?></div>
\t<div class="modal_form agrmnt" id="consultfrom"><?php block_print('webform','client-block-46'); ?></div>
\t<div class="modal_form agrmnt" id="excursion"><?php block_print('webform','client-block-47'); ?></div>
\t<div class="contentform hidden"><?php block_print('webform','client-block-61'); ?></div>
</div> 
HTML;

/* ---------- Новый блок (с виджетами + скрипт + стиль) ---------- */
$newBlockWithScripts = <<<HTML
<div class="modals">
\t<span class="modal_overlay"></span>
\t<div class="modal_form agrmnt" id="callorder">
         <div class="formdesigner-widget" data-id="240959"></div>
    </div>
\t<div class="modal_form agrmnt" id="questionform">
         <div class="formdesigner-widget" data-id="240959"></div>
    </div>
\t<div class="modal_form agrmnt" id="consultfrom">
         <div class="formdesigner-widget" data-id="240959"></div>
    </div>
\t<div class="modal_form agrmnt" id="excursion">
         <div class="formdesigner-widget" data-id="240959"></div>
    </div>
\t<div class="contentform hidden">
         <div class="formdesigner-widget" data-id="240959"></div>
    </div>
</div>

<script type="text/javascript">
    (function (d, o) {
        var s = d.createElement("script"), g = "getElementsByTagName";
        s.type = "text/javascript"; s.async = true;
        s.src = "//formdesigner.ru/js/universal/init.js?v=1.0.0";
        s.onload = function () {
            if (document.readyState !== 'loading') {
                FD.init(o);
            } else document.addEventListener("DOMContentLoaded", function () {
                FD.init(o);
            });
        };
        var h=d[g]("head")[0] || d[g]("body")[0];
        h.appendChild(s);
    })(document, {
    "host": "formdesigner.ru",
    "forms": {
        "240959": {
            "width": "600px",
            "height": "auto",
            "scroll": true
        }
    }
});
</script>
<style>
    .modal_form:before{
    content: "x";
    position: absolute;
    top: 20px;
    right: 20px;
    font-size: 20px;
    font-weight: 600;
}
.region-bottom-content{
    margin-top: 20px
}
.region-bottom-content .formdesigner-widget{
    max-width: 90vw!important;
}
#forma + .contentform{
    padding: 0;
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
    document.querySelectorAll('.modal_overlay.visible, .modal_form.visible')
      .forEach(el => el.classList.remove('visible'));
  }
});
</script>
HTML;

/* ---------- Лог ---------- */
$ts = date('Ymd-His');
$logFile = __DIR__ . "/deploy_forms_{$ts}.log";
$log = [];
$addLog = function($m) use (&$log) { $log[] = '['.date('Y-m-d H:i:s')."] ".$m; };

/* ---------- Утилиты ---------- */
function ensureDir($dir, $addLog) {
    if (!is_dir($dir)) {
        if (!mkdir($dir, 0775, true)) {
            $addLog("ERROR: Не удалось создать каталог: $dir");
            return false;
        }
        $addLog("Создан каталог: $dir");
    }
    return true;
}

function copyFile($src, $dst, $addLog) {
    if (!file_exists($src)) {
        $addLog("WARN: Источник не найден: $src");
        return false;
    }
    if (!@copy($src, $dst)) {
        $addLog("ERROR: Не удалось скопировать $src → $dst");
        return false;
    }
    $addLog("Скопировано: $src → $dst");
    return true;
}

function backupFile($path, $addLog) {
    if (!file_exists($path)) return false;
    $bak = $path . '.bak-' . date('Ymd-His');
    if (@copy($path, $bak)) {
        $addLog("Бэкап: $path → $bak");
        return $bak;
    } else {
        $addLog("ERROR: Не удалось сделать бэкап $path");
        return false;
    }
}

/**
 * Заменяет блок <div class="modals">…</div> на новый (со скриптами/стилями).
 * Сначала пробует точную подстановку, затем — гибкий regex.
 */
function replaceModalsBlock($html, $oldExact, $newBlock, $addLog) {
    // 1) Точная замена
    if (strpos($html, $oldExact) !== false) {
        $addLog('Найден точный старый блок (str_replace). Выполняем замену.');
        return str_replace($oldExact, $newBlock, $html);
    }

    // 2) Regex замена — ищем блок с нужными id (callorder, questionform, consultfrom, excursion, contentform)
    $pattern = '#<div\s+class="modals">\s*'
             . '<span\s+class="modal_overlay"></span>.*?'
             . '<div\s+class="modal_form[^"]*"\s+id="callorder">.*?</div>.*?'
             . '<div\s+class="modal_form[^"]*"\s+id="questionform">.*?</div>.*?'
             . '<div\s+class="modal_form[^"]*"\s+id="consultfrom">.*?</div>.*?'
             . '<div\s+class="modal_form[^"]*"\s+id="excursion">.*?</div>.*?'
             . '<div\s+class="contentform\s+hidden">.*?</div>.*?'
             . '</div>#si';

    if (preg_match($pattern, $html)) {
        $addLog('Найден старый модальный блок по regex. Выполняем замену.');
        return preg_replace($pattern, $newBlock, $html, 1);
    }

    $addLog('WARN: Старый модальный блок не найден — изменения не внесены.');
    return $html;
}

/* ---------- Процесс ---------- */
foreach ($siteTemplateDirs as $tplDir) {
    $addLog("==== Обработка каталога: $tplDir ====");

    if (!ensureDir($tplDir, $addLog)) continue;

    // 1) Копирование файлов block--webform--*.tpl.php
    foreach ($sourceFiles as $src) {
        $dst = $tplDir . '/' . basename($src);
        copyFile($src, $dst, $addLog);
    }

    // 2) Правка footer.tpl.php
    $footer = $tplDir . '/footer.tpl.php';
    if (!file_exists($footer)) {
        $addLog("WARN: footer.tpl.php не найден: $footer");
        continue;
    }
    if (!is_writable($footer)) {
        $addLog("ERROR: Нет прав на запись: $footer");
        continue;
    }

    $backup = backupFile($footer, $addLog);
    $html = file_get_contents($footer);
    if ($html === false) {
        $addLog("ERROR: Не удалось прочитать $footer");
        continue;
    }

    $newHtml = replaceModalsBlock($html, $oldBlockExact, $newBlockWithScripts, $addLog);

    if ($newHtml !== $html) {
        if (file_put_contents($footer, $newHtml) !== false) {
            $addLog("OK: Обновлён файл: $footer");
        } else {
            $addLog("ERROR: Не удалось записать изменения в $footer");
            // попытка откатить бэкап
            if ($backup && file_exists($backup)) {
                @copy($backup, $footer);
                $addLog("Откат к бэкапу выполнен: $backup → $footer");
            }
        }
    } else {
        $addLog("INFO: Изменений в $footer не внесено (блок не найден).");
    }
}

/* ---------- Сохраняем лог ---------- */
file_put_contents($logFile, implode(PHP_EOL, $log) . PHP_EOL);
echo implode(PHP_EOL, $log) . PHP_EOL;
echo "Лог: $logFile" . PHP_EOL;

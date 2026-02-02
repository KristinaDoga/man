<?php
/**
 * batch_replace_forms.php
 * PHP-CLI скрипт.
 * Делает замены в footer.tpl.php и media.css, логирует действия, создаёт бэкапы.
 */

date_default_timezone_set('Europe/Moscow');

$logFile = __DIR__ . '/batch_replace_forms.log';

function logmsg($msg) {
    global $logFile;
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
    file_put_contents($logFile, $line, FILE_APPEND);
    echo $line;
}

function backupFile($path) {
    if (!is_file($path)) return false;
    $bak = $path . '.bak.' . date('Ymd-His');
    if (@copy($path, $bak)) {
        logmsg("Backup created: $bak");
        return true;
    }
    logmsg("WARNING: Backup failed: $path");
    return false;
}

/**
 * Безопасная запись файла (с временным файлом и rename).
 */
function safeWrite($path, $content) {
    $tmp = $path . '.tmp.' . uniqid('', true);
    if (file_put_contents($tmp, $content) === false) {
        throw new RuntimeException("Cannot write temp file: $tmp");
    }
    if (!@rename($tmp, $path)) {
        @unlink($tmp);
        throw new RuntimeException("Cannot move temp to target: $path");
    }
}

/**
 * Простой поиск "уже добавлен ли скрипт".
 */
function hasFormdesignerScript($content, $formId) {
    if (strpos($content, 'formdesigner.ru/js/universal/init.js') !== false &&
        strpos($content, '"' . $formId . '"') !== false
    ) {
        return true;
    }
    return false;
}

/**
 * Удалить height:100% внутри блока .modal_form { ... }.
 */
function removeHeightInModalForm($css) {
    $pattern = '/(\.modal_form\s*\{)(.*?)(\})/si';
    $out = preg_replace_callback($pattern, function($m) {
        // Удаляем height: 100%; внутри тела правила
        $body = $m[2];
        // точечно height: 100%; (с пробелами)
        $body = preg_replace('/height\s*:\s*100%\s*;?/i', '', $body);
        // нормализуем лишние пробелы перед }
        $body = preg_replace('/;(\s*;)+/',';',$body);
        return $m[1] . $body . $m[3];
    }, $css);
    return $out;
}

/**
 * Заменить блок webform -> formdesigner в footer.tpl.php
 * и дописать инициализационный скрипт при необходимости.
 */
function processFooter($path, $newBlock, $appendScript, $formId) {
    if (!is_file($path)) { logmsg("Skip (not found): $path"); return; }

    $orig = file_get_contents($path);
    if ($orig === false) { logmsg("ERROR: Cannot read $path"); return; }

    // Точный исходный блок (как в задаче), для прямой замены:
    $oldBlockExact = <<<'PHP'
<span class="go_top"></span>

<div class="content_form clr smrt hidden">
	<?php block_print('webform', 'client-block-65'); ?>
</div>

<div class="modals smrt">
	<span class="modal_overlay"></span>
	<div class="modal_form warm_bg" id="call_order">
		<?php block_print('webform', 'client-block-127'); ?>
	</div>
	<div class="modal_form warm_bg" id="consultation">
		<?php block_print('webform', 'client-block-128'); ?>
	</div>
	<div class="modal_form warm_bg" id="question">
		<?php block_print('webform', 'client-block-129'); ?>
	</div>
</div>
PHP;

    $modified = $orig;

    if (strpos($modified, $oldBlockExact) !== false) {
        $modified = str_replace($oldBlockExact, $newBlock, $modified);
        logmsg("Replaced block by exact match in: $path");
    } else {
        // Более гибкая замена: от <span class="go_top"></span> до </div> закрывающего модалки
        // Осторожный регекс: ищем последовательность блоков как в исходнике
        $pattern = '#<span\s+class="go_top"></span>\s*<div\s+class="content_form.*?</div>\s*<div\s+class="modals\s+smrt">.*?</div>\s*#si';
        $try = preg_replace($pattern, $newBlock, $modified, 1, $count);
        if ($count > 0 && $try !== null) {
            $modified = $try;
            logmsg("Replaced block by regex in: $path");
        } else {
            logmsg("WARNING: Could not find old block in: $path (skipped replace)");
        }
    }

    // Добавить инициализационный скрипт, если его нет
    if (!hasFormdesignerScript($modified, $formId)) {
        $modified .= PHP_EOL . $appendScript . PHP_EOL;
        logmsg("Appended FormDesigner script in: $path");
    } else {
        logmsg("FormDesigner script already present in: $path");
    }

    if ($modified !== $orig) {
        backupFile($path);
        try {
            safeWrite($path, $modified);
            logmsg("Saved: $path");
        } catch (Throwable $e) {
            logmsg("ERROR writing $path: " . $e->getMessage());
        }
    } else {
        logmsg("No changes needed: $path");
    }
}

/**
 * Обработать media.css — удалить height:100% в .modal_form
 */
function processMediaCss($path) {
    if (!is_file($path)) { logmsg("Skip (not found): $path"); return; }
    $orig = file_get_contents($path);
    if ($orig === false) { logmsg("ERROR: Cannot read $path"); return; }

    $modified = removeHeightInModalForm($orig);

    if ($modified !== $orig) {
        backupFile($path);
        try {
            safeWrite($path, $modified);
            logmsg("Updated CSS (removed height:100% in .modal_form): $path");
        } catch (Throwable $e) {
            logmsg("ERROR writing $path: " . $e->getMessage());
        }
    } else {
        logmsg("No CSS changes needed (height:100% not found or already removed): $path");
    }
}

/* ------------------ Конфигурация ------------------ */

$sites = [
'all',
'kasimov',
'korablino',
'mihajlov',
'novomichurinsk',
'ryazhsk',
'rybnoe',
'sasovo',
'shilovo',
'skopin',
];

$base = '/home/admeen/web/klinika-borisova.ru/public_html/sites';
$formId = '240865';

$newBlock = <<<'PHP'
<span class="go_top"></span>

<div class="content_form clr smrt hidden">
	<?php 
	// block_print('webform', 'client-block-65'); 
	?>
	<div class="formdesigner-widget" data-id="240865"></div>
</div>

<div class="modals smrt">
	<span class="modal_overlay"></span>
	<div class="modal_form warm_bg" id="call_order">
		<?php 
		// block_print('webform', 'client-block-127'); 
		?>
		<div class="formdesigner-widget" data-id="240865"></div>
	</div>
	<div class="modal_form warm_bg" id="consultation">
		<?php 
		// block_print('webform', 'client-block-128'); 
		?>
		<div class="formdesigner-widget" data-id="240865"></div>
	</div>
	<div class="modal_form warm_bg" id="question">
		<?php
		//  block_print('webform', 'client-block-129'); 
		 ?>
		 <div class="formdesigner-widget" data-id="240865"></div>
	</div>
</div>
PHP;

$appendScript = <<<'HTML'
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
        "240865": {
            "width": "600px",
            "height": "auto",
            "scroll": true
        }
    }
});
</script>
HTML;

/* ------------------ Запуск ------------------ */

logmsg("=== START batch_replace_forms ===");

foreach ($sites as $site) {
    $footer = "$base/$site/themes/flumb/templates/footer.tpl.php";
    $media  = "$base/$site/themes/flumb/css/media.css";

    logmsg("--- Process site: $site ---");

    processFooter($footer, $newBlock, $appendScript, $formId);
    processMediaCss($media);
}

logmsg("=== DONE batch_replace_forms ===");

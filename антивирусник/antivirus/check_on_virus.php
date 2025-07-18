<?php
// Добавлена отправка в тг
$sites = [
"viroha.ru",
"kwtelectro.ru",
];

$infectedSites = []; // Список доменов для которых нужно запустить cleaner.php

$to = 'czm-marketing@yandex.ru';
$subject = 'Мониторинг сайтов — Обнаружены проблемы';
$headers = 'From: monitor@check.com';

$logFile = __DIR__ . '/check_log.txt';
$cacheFile = __DIR__ . '/site_status.json';

$telegramToken = '7910894632:AAEGD0htVFxjEgwZWCRfjpxIz-M12ueLPSo'; // Твой токен от @BotFather
$telegramChatId = '-1002824725502'; // Твой chat_id (пользователь или группа)

// в https://api.telegram.org/bot7910894632:AAEGD0htVFxjEgwZWCRfjpxIz-M12ueLPSo/getUpdates смотрим
// "chat": {
//   "id": -1002824725502,
//   "title": "Проверка сайтов на вирусы",
//   "type": "supergroup"
// }
// Функция для отправки Telegram-сообщения
function sendTelegramMessage($text, $token, $chat_id) {
    $url = "https://api.telegram.org/bot$token/sendMessage";
    $data = [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => 'HTML'
    ];

    $options = [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => http_build_query($data),
        CURLOPT_TIMEOUT => 10
    ];

    $ch = curl_init();
    curl_setopt_array($ch, $options);
    curl_exec($ch);
    curl_close($ch);
}


$previousStatus = file_exists($cacheFile) ? json_decode(file_get_contents($cacheFile), true) : [];
$currentStatus = [];
$confirmedProblems = [];
$now = date('Y-m-d H:i:s');

foreach ($sites as $domain) {
    $url = "https://$domain";
    $http_ok = false;
    $has_footer = false;
    $has_coming_soon = false;
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false, // ок, но потенциально небезопасно
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_HEADER => true,
        CURLOPT_NOBODY => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; SiteMonitorBot/1.0)',
    ]);

    $response = curl_exec($curl);
    if ($response === false) {
        file_put_contents(__DIR__ . '/tg_error.log', "[$now] $url: " . curl_error($curl) . "\n", FILE_APPEND);
    }
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $body = substr($response, curl_getinfo($curl, CURLINFO_HEADER_SIZE));
    curl_close($curl);

    if ($httpCode === 200) {
        $http_ok = true;
    }

    if ($body) {
        if (stripos($body, 'footer') !== false) {
            $has_footer = true;
        }
        if (stripos($body, '<h1>COMING SOON</h1>') !== false) {
            $has_coming_soon = true;
        }
    }
    else{
        // если нет body, с сайтом что-то не так
        $http_ok = false;
    }

    $has_error = !$http_ok || !$has_footer || $has_coming_soon;

    $status_key = "$domain";
    $currentStatus[$status_key] = [
        'error' => $has_error,
        'http' => $httpCode,
        'footer' => $has_footer,
        'coming_soon' => $has_coming_soon,
        'time' => $now
    ];

    $logLine = "[$now] $url — HTTP: $httpCode, FOOTER: " . ($has_footer ? "YES" : "NO") .
               ", COMING_SOON: " . ($has_coming_soon ? "YES" : "NO") . "\n";
    file_put_contents($logFile, $logLine, FILE_APPEND);

    // Сравниваем с предыдущим статусом
    if ($has_error) {
        $issue = "$url — ";
        if (!$http_ok) {
            $issue .= "HTTP != 200 ($httpCode)";
        }
        if (!$has_footer) {
            $issue .= (!$http_ok ? "; " : "") . "Нет footer";
        }
        if ($has_coming_soon) {
            $issue .= (!$http_ok || !$has_footer ? "; " : "") . "COMING SOON";
        }
        $confirmedProblems[] = $issue;
        $infectedSites[] = $domain; // <-- Добавляем заражённый сайт
    }
}

// Обновляем кэш
file_put_contents($cacheFile, json_encode($currentStatus, JSON_PRETTY_PRINT));


// Отправляем письмо при подтверждённых проблемах
if (!empty($confirmedProblems)) {
    $message = "Обнаружены проблемы на сайтах:\n\n" . implode("\n", $confirmedProblems);

    // Email
    mail($to, $subject, $message, $headers);

    // Telegram
    sendTelegramMessage($message, $telegramToken, $telegramChatId);
}


$clearScript = '/home/admeen/_cron/customScript/cleaner.php';

foreach ($infectedSites as $infectedDomain) {
    $publicPath = "/home/admeen/web/$infectedDomain/public_html";

    if (is_dir($publicPath)) {
        // Меняем рабочую директорию перед запуском cleaner.php
        $cmd = "php -d open_basedir= $clearScript $publicPath";
        exec($cmd);
    } else {
        file_put_contents(__DIR__ . '/clear_error.log', "[$now] Папка public_html не найдена у $infectedDomain\n", FILE_APPEND);
    }
}

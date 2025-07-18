<?php
// полностью работает оповещение, но ещё нет связки с clear.php
// Добавлена отправка в тг
$sites = [
"esnp24.ru",
"omsk.esnp24.ru",
"voronezh.esnp24.ru",
"volgograd.esnp24.ru",
"vladimir.esnp24.ru",
"ufa.esnp24.ru",
"tyumen.esnp24.ru",
"tula.esnp24.ru",
"tomsk.esnp24.ru",
"stavropol.esnp24.ru",
"spb.esnp24.ru",
"sochi.esnp24.ru",
"saratov.esnp24.ru",
"samara.esnp24.ru",
"rostov.esnp24.ru",
"perm.esnp24.ru",
"novosibirsk.esnp24.ru",
"nn.esnp24.ru",
"nizhny-tagil.esnp24.ru",
"moscow.esnp24.ru",
"mahachkala.esnp24.ru",
"lipetsk.esnp24.ru",
"kursk.esnp24.ru",
"kazan.esnp24.ru",
"kaliningrad.esnp24.ru",
"irkutsk.esnp24.ru",
"ekb.esnp24.ru",
"chelyabinsk.esnp24.ru",
"cheboksary.esnp24.ru",
"bryansk.esnp24.ru",
"astrahan.esnp24.ru",
"yaroslavl.esnp24.ru",
"madmen.bz",
"direct.madmen.bz",
"artem-iroshnikov.ru",
"narko-hospital.ru",
"chelyabinsk.narko-hospital.ru",
"ekb.narko-hospital.ru",
"krasnodar.narko-hospital.ru",
"nn.narko-hospital.ru",
"rostov.narko-hospital.ru",
"saratov.narko-hospital.ru",
"spb.narko-hospital.ru",
"tver.narko-hospital.ru",
"grail.su",
"labinsk.grail.su",
"timashevsk.grail.su",
"tihoreck.grail.su",
"temruk.grail.su",
"sochi.grail.su",
"slavyansk-na-kubani.grail.su",
"novorossijsk.grail.su",
"majkop.grail.su",
"kurganinsk.grail.su",
"anapa.grail.su",
"krymsk.grail.su",
"kropotkin.grail.su",
"korenovsk.grail.su",
"goryachii-kluch.grail.su",
"gelendzhik.grail.su",
"ejsk.grail.su",
"belorechensk.grail.su",
"armavir.grail.su",
"tuapse.grail.su",
"rehab-centr.su",
"nonna.su",
"pansionat-nn.ru",
"vsepansionati.ru",
"rodusadba.ru",
"medtransit.ru",
"rodusadba78.ru",
"rodusadba.su",
"ru161.ru",
"rupans.ru",
"rusadba.su",
"stpansionat.ru",
"esteshop.ru",
"este-shop.ru",
"pansionat-voronezh.ru",
"pansionat-stavropol.ru",
"pansionat-sochi.su",
"brastore.ru",
"franshiza-pansionat.ru",
"estett.ru",
"ibankeer.ru",
"wheelmax.ru",
"vsekliniki.su",
"rdplast.kz",
"ekzosom.ru",
"vektortrezvosti.ru",
"priznanie.su",
"spasenieest.ru",
"novystart.ru",
"zhizn-bezteni.ru",
"zhivoe-dyhanie.ru",
"novyritm.su",
"pulssvobody.ru",
"povorot-rubezh.ru",
"antinarkolab.ru",
"antikod.su",
"stupeni-pererozhdeniya.ru",
"altervita.su",
"impulssveta.ru",
"navershine.su",
"nachalo-vozvrata.ru",
"evolyuciya.su",
"siyanievoli.ru",
"svoboda360.ru",
"klinika-reboot.ru",
"alhisvozh.ru",
"bezgranic.su",
"centr-vit.ru",
"alkonarkologiya.ru",
"generator-kwatt.ru",
"zavod-amper.ru",
"dolgoletie-pansionat.ru",
"krasnodar.dolgoletie-pansionat.ru",
"viroha.ru",
"kwtelectro.ru",
];


$to = 'czm-marketing@yandex.ru';
$subject = '⛔ Мониторинг сайтов — Обнаружены проблемы';
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
// ???????????
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

    // $response = curl_exec($ch);
    // if ($response === false) {
    //     file_put_contents(__DIR__ . '/tg_error.log', curl_error($ch), FILE_APPEND);
    // }
    // curl_close($ch);
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

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function amoLog($msg) {
    $logFile = __DIR__ . '/amo_test.log';
    file_put_contents($logFile, date('Y-m-d H:i:s') . " | " . $msg . PHP_EOL, FILE_APPEND);
}

require_once '/home/admeen/web/vsepansionati.ru/public_html/web/modules/custom/pension_amocrm/vendor/autoload.php';

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Models\LeadModel;
use AmoCRM\Collections\Leads\LeadsCollection;  // Обрати внимание на этот use!
use League\OAuth2\Client\Token\AccessToken;

amoLog("=== Запуск тестового создания лида ===");

$tokenFile = '/home/admeen/web/vsepansionati.ru/public_html/web/modules/custom/pension_amocrm/amo/token_info.json'; // поправь путь, если нужно
if (!file_exists($tokenFile)) {
    amoLog("❌ Не найден token_info.json");
    exit('Нет токена для авторизации');
}

$tokenData = json_decode(file_get_contents($tokenFile), true);
if (!$tokenData) {
    amoLog("❌ Не удалось прочитать token_info.json");
    exit('Проблема с токеном');
}

try {
    $accessToken = new AccessToken([
        'access_token' => $tokenData['accessToken'],
        'refresh_token' => $tokenData['refreshToken'],
        'expires' => $tokenData['expires'],
    ]);

    $apiClient = new AmoCRMApiClient(
        '55794fee-6bac-4fc0-9745-319fb7e26c7c',
        'on9NJwK6facn7kfshl4jos1mNdPeLqESwAZ5PwgfzFP09EvRGMznBK0hn8HjvSEt',
        'https://vsepansionati.ru/modules/custom/pension_amocrm/amo/get_token.php'
    );
 

    $apiClient->setAccessToken($accessToken)
              ->setAccountBaseDomain($tokenData['baseDomain']);

    amoLog("Токен загружен, создаю лид");

    $lead = new LeadModel();
    $lead->setName('Тестовый лид из test_create_lead.php');

    $leadsCollection = new LeadsCollection();
    $leadsCollection->add($lead);

    $result = $apiClient->leads()->add($leadsCollection);

    $leadId = $result->first()->getId();
    amoLog("✅ Лид создан с ID: $leadId");
    echo "Лид успешно создан! ID: $leadId";

} catch (\Exception $e) {
    amoLog("❌ Ошибка при создании лида: " . $e->getMessage());
    echo "Ошибка: " . $e->getMessage();
}
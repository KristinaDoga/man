
$host = 'localhost';
$db   = 'admeen_narcoprof';
$user = 'admeen_narcoprof';
$pass = 'LBmicbs7RHourV48';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

//Добавляем в metatag отсутствующие ноды и термы
try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Подготовка SQL запроса для проверки наличия записи
    $checkQuery = "SELECT entity_id FROM dp_metatag WHERE entity_id = :entity_id";
    $checkStmt = $pdo->prepare($checkQuery);

    // Подготовка SQL запроса для вставки данных
    $insertQuery = "INSERT INTO dp_metatag (entity_type, entity_id, revision_id, language, data) VALUES (:entity_type, :entity_id, :revision_id, :language, :data)";
    $insertStmt = $pdo->prepare($insertQuery);

    // Получение данных из временной таблицы
    $metaTmpQuery = "SELECT url_source_num, url_source_type, new_title, new_desc FROM dp_meta_tmp";
    foreach ($pdo->query($metaTmpQuery) as $row) {
        // Проверяем наличие и непустоту необходимых полей
        if (!empty($row['url_source_num']) && !empty($row['url_source_type'])) {
            // Проверяем наличие записи
            $checkStmt->execute(['entity_id' => $row['url_source_num']]);
            if ($checkStmt->fetch() === false) {
                // Сериализация данных
                $serializedData = serialize([
                    'title' => ['value' => $row['new_title']],
                    'description' => ['value' => $row['new_desc']]
                ]);

                // Вставка данных
                $insertStmt->execute([
                    'entity_type' => $row['url_source_type'],
                    'entity_id' => $row['url_source_num'],
                    'revision_id' => $row['url_source_num'],
                    'language' => 'ru',
                    'data' => $serializedData
                ]);
            }
        }
    }
    echo "Data processing completed successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Замена node и term
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $pdo->beginTransaction();

    // Получаем все нужные записи из dp_meta_tmp
    $metaTmpQuery = "SELECT new_title, new_desc, url_source_type, url_source_num FROM dp_meta_tmp";
    foreach ($pdo->query($metaTmpQuery) as $tmpRow) {
        // Находим соответствующие записи в dp_metatag
        $metatagQuery = "SELECT entity_id, data FROM dp_metatag WHERE entity_type = ? AND entity_id = ?";
        $stmt = $pdo->prepare($metatagQuery);
        $stmt->execute([$tmpRow['url_source_type'], $tmpRow['url_source_num']]);

        while ($metaRow = $stmt->fetch()) {
            // Десериализация данных
            $data = unserialize($metaRow['data']);

            // Обновление данных
             $data['title']['value'] = $tmpRow['new_title'];
             $data['description']['value'] = $tmpRow['new_desc'];


            // Сериализация и обновление записи
            $newDataSerialized = serialize($data);
            $updateQuery = "UPDATE dp_metatag SET data = ? WHERE entity_id = ?";
            $updateStmt = $pdo->prepare($updateQuery);
            $updateStmt->execute([$newDataSerialized, $metaRow['entity_id']]);
        }
    }

    $pdo->commit();
    echo "Meta tags successfully updated.\n";
} catch (\PDOException $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage();
}



// Замена представлений
try {
    // Подключение к базе данных
    $pdo = new PDO($dsn, $user, $pass, $options);
    $pdo->beginTransaction();

    // Получаем данные для обновления из таблицы dp_meta_tmp
    $metaTmpQuery = "SELECT url_vid, new_title, new_desc FROM dp_meta_tmp";
    foreach ($pdo->query($metaTmpQuery) as $row) {
        // Находим соответствующие записи в dp_views_display
        $displayQuery = "SELECT vid, display_options FROM dp_views_display WHERE vid = ? AND id = 'page'";
        $stmt = $pdo->prepare($displayQuery);
        $stmt->execute([$row['url_vid']]);

        while ($displayRow = $stmt->fetch()) {
            // Десериализация данных
            $options = unserialize($displayRow['display_options']);
            $updated = false;

            // Обновление title, description в metatags
            if (isset($options['metatags']['und'])) {
                $options['metatags']['und']['title']['value'] = $row['new_title'];
                    $updated = true;
                    $options['metatags']['und']['description']['value'] = $row['new_desc'];
                    $updated = true;

            }

            // Сериализация обновленных данных и обновление записи
            if ($updated) {
                $updatedOptionsSerialized = serialize($options);
                $updateQuery = "UPDATE dp_views_display SET display_options = ? WHERE vid = ? AND id = 'page'";
                $updateStmt = $pdo->prepare($updateQuery);
                $updateStmt->execute([$updatedOptionsSerialized, $displayRow['vid']]);
            }
        }
    }

    // Завершение транзакции
    $pdo->commit();
    echo "Updates completed successfully.";
} catch (PDOException $e) {
    // Откат транзакции в случае ошибки
    $pdo->rollBack();
    echo "Error: " . $e->getMessage();
}
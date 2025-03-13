--------------- Подготовка таблицы meta_tmp ---------------

-- Добавление столбцов в таблицу dp_meta_tmp
ALTER TABLE dp_meta_tmp 
ADD COLUMN url_source VARCHAR(255),
ADD COLUMN url_source_type VARCHAR(255),
ADD COLUMN url_source_num INT,
ADD COLUMN url_vid INT;

-- Обновление новых столбцов с помощью данных из dp_url_alias с учётом приоритета node
UPDATE dp_meta_tmp
SET url_source = (
    SELECT ua.source
    FROM dp_url_alias AS ua
    WHERE ua.alias = dp_meta_tmp.url 
    ORDER BY 
        CASE
            WHEN ua.source LIKE 'node/%' THEN 1
            WHEN ua.source LIKE 'taxonomy_term/%' THEN 2
            ELSE 3
        END, 
        ua.pid DESC
    LIMIT 1
);

UPDATE dp_meta_tmp
SET url_source_type = CASE
    WHEN url_source = 'node' THEN 'node' -- условие для главной страницы
    WHEN url_source LIKE 'taxonomy/term/%' THEN 'taxonomy_term'
    WHEN url_source LIKE 'node/%' THEN 'node'
    ELSE NULL
END,
url_source_num = CASE
    WHEN url_source = 'node' THEN '1' -- Задаем номер 1 для главной страницы
    WHEN url_source LIKE 'taxonomy/term/%' THEN SUBSTRING_INDEX(url_source, '/', -1)
    WHEN url_source LIKE 'node/%' THEN SUBSTRING_INDEX(url_source, '/', -1)
    ELSE NULL
END;

UPDATE dp_meta_tmp
JOIN dp_views_view ON dp_meta_tmp.url = dp_views_view.name
SET dp_meta_tmp.url_vid = dp_views_view.vid;

--------------- Обновление h1 ---------------
-- Обновление dp_node.title
UPDATE dp_node n
JOIN dp_meta_tmp mt ON mt.url_source_num = n.nid
SET n.title = mt.new_h1
WHERE mt.url_source_type = 'node';

-- Обновление dp_node_revision.title
UPDATE dp_node_revision nr
JOIN dp_meta_tmp mt ON mt.url_source_num = nr.nid
SET nr.title = mt.new_h1
WHERE mt.url_source_type = 'node';

-- Обновление dp_taxonomy_term_data
UPDATE dp_taxonomy_term_data t
JOIN dp_meta_tmp mt ON mt.url_source_num = t.tid
SET t.name = mt.new_h1
WHERE mt.url_source_type = 'taxonomy_term';


-- Обновление значения в dp_field_data_field_title для узлов и терминов таксономии
UPDATE dp_field_data_field_title f
JOIN dp_meta_tmp m ON f.entity_id = m.url_source_num AND f.entity_type = m.url_source_type
SET f.field_title_value = m.new_h1
WHERE m.url_source_type IN ('node', 'taxonomy_term');

-- Обновление значения в dp_field_data_field_title для узлов и терминов таксономии
UPDATE dp_field_data_field_title fr
JOIN dp_meta_tmp m ON fr.entity_id = m.url_source_num AND fr.entity_type = m.url_source_type
SET fr.field_title_value = m.new_h1
WHERE m.url_source_type IN ('node', 'taxonomy_term');





------------------- Обновление h1 внутри контента-----------------------

-- Добавляем колонки с заголовком и флагом
ALTER TABLE dp_field_data_field_right_column
ADD COLUMN new_h1 VARCHAR(255);

ALTER TABLE dp_field_data_field_right_column
ADD COLUMN flag INT;

-- Подтягиваем значения заголовков из dp_meta_tmp
UPDATE dp_field_data_field_right_column AS f
JOIN dp_meta_tmp AS m ON f.entity_id = m.url_source_num
SET f.new_h1 = m.new_h1;

-- Оборачиваем заголовки в вёрску
UPDATE dp_field_data_field_right_column
SET new_h1 = CONCAT('<h1 class="about__title title">', new_h1, '</h1>')
WHERE new_h1 IS NOT NULL;

-- Удаляем старый заголовок и поднимаем флаг
UPDATE dp_field_data_field_right_column
SET field_right_column_value = REGEXP_REPLACE(
    field_right_column_value,
    '<h1 class="about__title title">.*?</h1>',
    ''),
    flag = 1
WHERE field_right_column_value REGEXP '<h1 class="about__title title">.*?</h1>';

UPDATE dp_field_data_field_right_column
SET field_right_column_value = REGEXP_REPLACE(
    field_right_column_value,
    '<h1>.*?</h1>',
    ''),
    flag = 1
WHERE field_right_column_value REGEXP '<h1>.*?</h1>';

UPDATE dp_field_data_field_right_column
SET field_right_column_value = REGEXP_REPLACE(
    field_right_column_value,
    '<h1 class="spollers-alcoholism__title title">.*?</h1>',
    ''),
    flag = 1
WHERE field_right_column_value REGEXP '<h1 class="spollers-alcoholism__title title">.*?</h1>';

-- У поднятых флагов после контейнера вставляем новый заголовок
UPDATE dp_field_data_field_right_column
SET field_right_column_value = CONCAT(
    SUBSTRING(field_right_column_value, 1, LOCATE('<div class="about__container">', field_right_column_value) + LENGTH('<div class="about__container">') - 1),
    new_h1,
    SUBSTRING(field_right_column_value, LOCATE('<div class="about__container">', field_right_column_value) + LENGTH('<div class="about__container">'))
)
WHERE field_right_column_value LIKE '%<div class="about__container">%'
  AND new_h1 IS NOT NULL
  AND flag = 1 ;


UPDATE dp_field_data_field_right_column
SET field_right_column_value = CONCAT(
    SUBSTRING(field_right_column_value, 1, LOCATE('<div class="spollers-alcoholism__container">', field_right_column_value) + LENGTH('<div class="spollers-alcoholism__container">') - 1),
    new_h1,
    SUBSTRING(field_right_column_value, LOCATE('<div class="spollers-alcoholism__container">', field_right_column_value) + LENGTH('<div class="spollers-alcoholism__container">'))
)
WHERE field_right_column_value LIKE '%<div class="spollers-alcoholism__container">%'
  AND new_h1 IS NOT NULL
  AND flag = 1 ;

-- то же самое с таблицей dp_field_revision_field_right_column
-- Добавляем колонки с заголовком и флагом
ALTER TABLE dp_field_revision_field_right_column
ADD COLUMN new_h1 VARCHAR(255);

ALTER TABLE dp_field_revision_field_right_column
ADD COLUMN flag INT;

-- Подтягиваем значения заголовков из dp_meta_tmp
UPDATE dp_field_revision_field_right_column AS f
JOIN dp_meta_tmp AS m ON f.entity_id = m.url_source_num
SET f.new_h1 = m.new_h1;

-- Оборачиваем заголовки в вёрску
UPDATE dp_field_revision_field_right_column
SET new_h1 = CONCAT('<h1 class="about__title title">', new_h1, '</h1>')
WHERE new_h1 IS NOT NULL;

-- Удаляем старый заголовок и поднимаем флаг
UPDATE dp_field_revision_field_right_column
SET field_right_column_value = REGEXP_REPLACE(
    field_right_column_value,
    '<h1 class="about__title title">.*?</h1>',
    ''),
    flag = 1
WHERE field_right_column_value REGEXP '<h1 class="about__title title">.*?</h1>';

UPDATE dp_field_revision_field_right_column
SET field_right_column_value = REGEXP_REPLACE(
    field_right_column_value,
    '<h1>.*?</h1>',
    ''),
    flag = 1
WHERE field_right_column_value REGEXP '<h1>.*?</h1>';

UPDATE dp_field_revision_field_right_column
SET field_right_column_value = REGEXP_REPLACE(
    field_right_column_value,
    '<h1 class="spollers-alcoholism__title title">.*?</h1>',
    ''),
    flag = 1
WHERE field_right_column_value REGEXP '<h1 class="spollers-alcoholism__title title">.*?</h1>';

-- У поднятых флагов после контейнера вставляем новый заголовок
UPDATE dp_field_revision_field_right_column
SET field_right_column_value = CONCAT(
    SUBSTRING(field_right_column_value, 1, LOCATE('<div class="about__container">', field_right_column_value) + LENGTH('<div class="about__container">') - 1),
    new_h1,
    SUBSTRING(field_right_column_value, LOCATE('<div class="about__container">', field_right_column_value) + LENGTH('<div class="about__container">'))
)
WHERE field_right_column_value LIKE '%<div class="about__container">%'
  AND new_h1 IS NOT NULL
  AND flag = 1 ;


UPDATE dp_field_revision_field_right_column
SET field_right_column_value = CONCAT(
    SUBSTRING(field_right_column_value, 1, LOCATE('<div class="spollers-alcoholism__container">', field_right_column_value) + LENGTH('<div class="spollers-alcoholism__container">') - 1),
    new_h1,
    SUBSTRING(field_right_column_value, LOCATE('<div class="spollers-alcoholism__container">', field_right_column_value) + LENGTH('<div class="spollers-alcoholism__container">'))
)
WHERE field_right_column_value LIKE '%<div class="spollers-alcoholism__container">%'
  AND new_h1 IS NOT NULL
  AND flag = 1 ;

------- 
------- Только после генерации проверочной таблицы
-------
------------------- Проверка -----------------------

-- Проверка h1
SELECT new_h1
FROM meta m1
WHERE NOT EXISTS (
    SELECT 1
    FROM meta m2
    WHERE m1.new_h1 = m2.real_h1
);

-- Проверка title
SELECT new_title
FROM meta m1
WHERE NOT EXISTS (
    SELECT 1
    FROM meta m2
    WHERE m1.new_title = m2.real_title
);

-- Проверка desc
SELECT new_desc
FROM meta m1
WHERE NOT EXISTS (
    SELECT 1
    FROM meta m2
    WHERE m1.new_desc = m2.real_desc
);


----------- Чистка базы ---------------

-- Удаляем временные колонки и таблицы
ALTER TABLE dp_field_data_field_right_column
DROP COLUMN flag;

ALTER TABLE dp_field_data_field_right_column
DROP COLUMN new_h1;

  ALTER TABLE dp_field_revision_field_right_column
DROP COLUMN flag;

ALTER TABLE dp_field_revision_field_right_column
DROP COLUMN new_h1;

DROP TABLE meta;
DROP TABLE dp_meta_tmp;










<?php

$databases = array (
  'default' =>
  array (
    'default' =>
    array (
      'database' => 'admeen_czm_irkutsk_new2',
      'username' => 'admeen_czm_irkutsk_new2',
      'password' => 'hfDUPS5{DNRvEn',
      'host' => 'localhost',
      'port' => '',
      'driver' => 'mysql',
      'prefix' => 'dp_',
      'charset' => 'utf8mb4',
      'collation' => 'utf8mb4_general_ci',
    ),
  ),
);


$databases = array (
    'default' =>
    array (
      'default' =>
      array (
        'database' => 'admeen_czm_novosibirsk_new',
        'username' => 'admeen_czm_novosibirsk_new',
        'password' => 'xP?EW7UkH9aXpx',
        'host' => 'localhost',
        'port' => '',
        'driver' => 'mysql',
        'prefix' => 'dp_',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_general_ci',
      ),
    ),
  );
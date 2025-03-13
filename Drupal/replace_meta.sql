-- Добавление столбцов в таблицу dp_meta_tmp
ALTER TABLE dp_meta_tmp 
ADD COLUMN url_source VARCHAR(255),
ADD COLUMN url_source_type VARCHAR(255),
ADD COLUMN url_source_num INT;

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


ALTER TABLE dp_meta_tmp ADD COLUMN url_vid INT;
UPDATE dp_meta_tmp
JOIN dp_views_view ON dp_meta_tmp.url = dp_views_view.name
SET dp_meta_tmp.url_vid = dp_views_view.vid;

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
sql запрос. Магазин на opencart3. Вычислить все дочерние категории от категорий  
2
23
47
149
351


SELECT * FROM `oc_category` WHERE `oc_category`.`parent_id` = 2
OR `oc_category`.`parent_id` = 23
OR `oc_category`.`parent_id` = 47
OR `oc_category`.`parent_id` = 149
OR `oc_category`.`parent_id` = 351

Удалить товары с oc_product_to_category.product_id, у которых oc_product_to_category.category_id не равно ни одному из перечисленных
1
3
8
10
12
16
19
20
22
25
27
28
29
31
36
37
39
41
43
45
56
60
62
64
67
72
2
23
47
149
351

sql запрос. Магазин на opencart3. Удалить товары с oc_product_to_category.product_id, у которых oc_product_to_category.category_id не равно ни одному из перечисленных 

-- Найдём товары, которые нужно удалить
DELETE FROM oc_product 
WHERE product_id NOT IN (
    SELECT DISTINCT product_id 
    FROM oc_product_to_category 
    WHERE category_id IN (1,3,8,10,12,16,19,20,22,25,27,28,29,31,36,37,39,41,43,45,56,60,62,64,67,72,2,23,47,149,351)
);

-- Удаляем связи товаров с категориями
DELETE FROM oc_product_to_category 
WHERE product_id NOT IN (
    SELECT DISTINCT product_id 
    FROM oc_product_to_category 
    WHERE category_id IN (1,3,8,10,12,16,19,20,22,25,27,28,29,31,36,37,39,41,43,45,56,60,62,64,67,72,2,23,47,149,351)
);

-- Удаляем описания товаров
DELETE FROM oc_product_description 
WHERE product_id NOT IN (
    SELECT DISTINCT product_id 
    FROM oc_product_to_category 
    WHERE category_id IN (1,3,8,10,12,16,19,20,22,25,27,28,29,31,36,37,39,41,43,45,56,60,62,64,67,72,2,23,47,149,351)
);

-- Удаляем SEO URL товаров
DELETE FROM oc_seo_url 
WHERE query LIKE 'product_id=%' 
AND CAST(SUBSTRING_INDEX(query, '=', -1) AS UNSIGNED) NOT IN (
    SELECT DISTINCT product_id 
    FROM oc_product_to_category 
    WHERE category_id IN (1,3,8,10,12,16,19,20,22,25,27,28,29,31,36,37,39,41,43,45,56,60,62,64,67,72,2,23,47,149,351)
);

-- Удаляем изображения товаров
DELETE FROM oc_product_image 
WHERE product_id NOT IN (
    SELECT DISTINCT product_id 
    FROM oc_product_to_category 
    WHERE category_id IN (1,3,8,10,12,16,19,20,22,25,27,28,29,31,36,37,39,41,43,45,56,60,62,64,67,72,2,23,47,149,351)
);

-- Удаляем скидки товаров
DELETE FROM oc_product_discount 
WHERE product_id NOT IN (
    SELECT DISTINCT product_id 
    FROM oc_product_to_category 
    WHERE category_id IN (1,3,8,10,12,16,19,20,22,25,27,28,29,31,36,37,39,41,43,45,56,60,62,64,67,72,2,23,47,149,351)
);

-- Удаляем специальные цены товаров
DELETE FROM oc_product_special 
WHERE product_id NOT IN (
    SELECT DISTINCT product_id 
    FROM oc_product_to_category 
    WHERE category_id IN (1,3,8,10,12,16,19,20,22,25,27,28,29,31,36,37,39,41,43,45,56,60,62,64,67,72,2,23,47,149,351)
);

-- Удаляем атрибуты товаров
DELETE FROM oc_product_attribute 
WHERE product_id NOT IN (
    SELECT DISTINCT product_id 
    FROM oc_product_to_category 
    WHERE category_id IN (1,3,8,10,12,16,19,20,22,25,27,28,29,31,36,37,39,41,43,45,56,60,62,64,67,72,2,23,47,149,351)
);

-- Удаляем опции товаров
DELETE FROM oc_product_option 
WHERE product_id NOT IN (
    SELECT DISTINCT product_id 
    FROM oc_product_to_category 
    WHERE category_id IN (1,3,8,10,12,16,19,20,22,25,27,28,29,31,36,37,39,41,43,45,56,60,62,64,67,72,2,23,47,149,351)
);

DELETE FROM oc_product_option_value 
WHERE product_id NOT IN (
    SELECT DISTINCT product_id 
    FROM oc_product_to_category 
    WHERE category_id IN (1,3,8,10,12,16,19,20,22,25,27,28,29,31,36,37,39,41,43,45,56,60,62,64,67,72,2,23,47,149,351)
);



-- Удаляем отзывы о товарах
DELETE FROM oc_review 
WHERE product_id NOT IN (
    SELECT DISTINCT product_id 
    FROM oc_product_to_category 
    WHERE category_id IN (1,3,8,10,12,16,19,20,22,25,27,28,29,31,36,37,39,41,43,45,56,60,62,64,67,72,2,23,47,149,351)
);

-- Удаляем категории, которых нет в списке
DELETE FROM oc_category 
WHERE category_id NOT IN (1,3,8,10,12,16,19,20,22,25,27,28,29,31,36,37,39,41,43,45,56,60,62,64,67,72,2,23,47,149,351);

-- Удаляем описания категорий
DELETE FROM oc_category_description 
WHERE category_id NOT IN (1,3,8,10,12,16,19,20,22,25,27,28,29,31,36,37,39,41,43,45,56,60,62,64,67,72,2,23,47,149,351);

-- Удаляем связи категорий с родительскими категориями
DELETE FROM oc_category_path 
WHERE category_id NOT IN (1,3,8,10,12,16,19,20,22,25,27,28,29,31,36,37,39,41,43,45,56,60,62,64,67,72,2,23,47,149,351);

-- Удаляем связи категорий с магазинами (если мультистор)
DELETE FROM oc_category_to_store 
WHERE category_id NOT IN (1,3,8,10,12,16,19,20,22,25,27,28,29,31,36,37,39,41,43,45,56,60,62,64,67,72,2,23,47,149,351);

-- Удаляем SEO-ссылки категорий
DELETE FROM oc_seo_url 
WHERE query LIKE 'category_id=%' 
AND CAST(SUBSTRING_INDEX(query, '=', -1) AS UNSIGNED) NOT IN (1,3,8,10,12,16,19,20,22,25,27,28,29,31,36,37,39,41,43,45,56,60,62,64,67,72,2,23,47,149,351);

-- Удаляем связи товаров с удалёнными категориями
DELETE FROM oc_product_to_category 
WHERE category_id NOT IN (1,3,8,10,12,16,19,20,22,25,27,28,29,31,36,37,39,41,43,45,56,60,62,64,67,72,2,23,47,149,351);


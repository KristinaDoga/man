UPDATE `field_data_body`
SET `body_value` = REPLACE(`body_value`, '+7 (861) 211-46-30', '+7 (861) 202-66-69')
WHERE `body_value` LIKE '%+7 (861) 211-46-30%';

UPDATE `field_data_body`
SET `body_value` = REPLACE(`body_value`, '8 (861) 211-46-30', '8 (861) 202-66-69')
WHERE `body_value` LIKE '%8 (861) 211-46-30%';

UPDATE `field_data_body`
SET `body_value` = REPLACE(`body_value`, '88612114630', '88612026669')
WHERE `body_value` LIKE '%88612114630%';


UPDATE `field_revision_body`
SET `body_value` = REPLACE(`body_value`, '+7 (861) 211-46-30', '+7 (861) 202-66-69')
WHERE `body_value` LIKE '%+7 (861) 211-46-30%';

UPDATE `field_revision_body`
SET `body_value` = REPLACE(`body_value`, '8 (861) 211-46-30', '8 (861) 202-66-69')
WHERE `body_value` LIKE '%8 (861) 211-46-30%';

UPDATE `field_revision_body`
SET `body_value` = REPLACE(`body_value`, '88612114630', '88612026669')
WHERE `body_value` LIKE '%88612114630%';

import csv

csv_file = 'admeen_dp-rdomsu.csv'
sql_file = 'admeen_dp-rdomsu.sql'
table = 'word_replace'

def escape_sql(value):
    if value is None:
        return 'NULL'
    # Экранируем одинарные кавычки
    return "'" + value.replace("'", "''") + "'"

with open(csv_file, 'r', encoding='utf-8', newline='') as f_csv, open(sql_file, 'w', encoding='utf-8') as f_sql:
    reader = csv.DictReader(f_csv, delimiter=',', quotechar='"', doublequote=True)
    
    f_sql.write(f"TRUNCATE TABLE {table};\n")
    
    for row in reader:
        data = row['data'].strip()
        entity_id = row['entity_id'].strip()
        value = row['value'].strip().replace('\n', '')  # Заменяем переводы строк на <br>
        
        data_sql = escape_sql(data)
        entity_id_sql = escape_sql(entity_id)
        value_sql = escape_sql(value)
        
        sql = f"INSERT INTO {table} (data, entity_id, value) VALUES ({data_sql}, {entity_id_sql}, {value_sql});\n"
        f_sql.write(sql)

print(f"Готово! SQL записан в файл: {sql_file}")

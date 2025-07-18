#!/bin/bash

#Скрипт предназначен для обхода всего сервера и резервного копирования htaccess и index.php
# под именами htaccess__clean index.php__clean. Потом это используется в cleaner.php для восстановления в каждом отдельном сайте

# Базовая директория, где лежат сайты
BASE_DIR="/home/admeen/web"

# Проходим по всем public_html
for site_dir in "$BASE_DIR"/*/public_html; do
  echo "Проверка директории: $site_dir"

  if [ -d "$site_dir" ]; then
    cd "$site_dir" || continue

    # Копирование .htaccess
    if [ -f ".htaccess" ]; then
      cp -p ".htaccess" ".htaccess__clean"
      echo "Скопирован .htaccess -> .htaccess__clean"
    fi

    # Копирование index.php
    if [ -f "index.php" ]; then
      cp -p "index.php" "index.php__clean"
      echo "Скопирован index.php -> index.php__clean"
    fi
  fi
done

echo "Готово!"

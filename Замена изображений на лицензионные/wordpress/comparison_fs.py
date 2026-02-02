import os

# ---------------------------
# НАСТРОЙКИ
# ---------------------------

SOURCE_DIR = r"C:\Users\kristina\Desktop\replace-img\narkologia.pro"
WORKING_DIR = r"C:\Users\kristina\Desktop\replace-img\narkologia.pro-working"

# допустимые форматы изображений
IMAGE_EXTENSIONS = {
    ".jpg",
    ".jpeg",
    ".png",
    ".bmp",
    ".tif",
    ".tiff",
    ".webp",
    ".avif"
}

LOG_FILE = "deleted_images_log.txt"


# ---------------------------
# Логирование
# ---------------------------

def log(text):
    print(text)
    with open(LOG_FILE, "a", encoding="utf-8") as f:
        f.write(text + "\n")


# ---------------------------
# Основная логика
# ---------------------------

def collect_working_images():
    """
    собираем все относительные пути изображений из WORKING_DIR
    """
    images = set()

    for root, _, files in os.walk(WORKING_DIR):
        for name in files:
            ext = os.path.splitext(name)[1].lower()
            if ext in IMAGE_EXTENSIONS:
                full_path = os.path.join(root, name)
                rel_path = os.path.relpath(full_path, WORKING_DIR)
                images.add(rel_path.lower())

    return images


def delete_duplicates(existing_images):
    """
    удаляем изображения из SOURCE_DIR,
    если такой же относительный путь существует в WORKING_DIR
    """
    removed_count = 0

    for root, _, files in os.walk(SOURCE_DIR):
        for name in files:
            ext = os.path.splitext(name)[1].lower()
            if ext not in IMAGE_EXTENSIONS:
                continue

            source_path = os.path.join(root, name)
            rel_path = os.path.relpath(source_path, SOURCE_DIR).lower()

            if rel_path in existing_images:
                os.remove(source_path)
                log(f"Удалён: {source_path}")
                removed_count += 1

    log(f"\n✅ Удалено файлов: {removed_count}")


# ---------------------------
# Запуск
# ---------------------------

def main():
    log("Сканируем WORKING каталог...")
    working_images = collect_working_images()

    log(f"Найдено файлов в WORKING: {len(working_images)}")

    log("\nУдаляем дубликаты в SOURCE...")
    delete_duplicates(working_images)

    log("\n✅ Готово")


if __name__ == "__main__":
    main()

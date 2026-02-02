import paramiko
import os
import re

# --------------------------
# Настройки
# --------------------------

SSH_HOST = "nicoubofu.beget.app"
SSH_PORT = 22
SSH_USER = "admeen"
SSH_PASSWORD = "7PlC1%RW"

REMOTE_DIR = "/home/admeen/web/mcmk.ru/public_html"
LOCAL_BASE_DIR = REMOTE_DIR[REMOTE_DIR.find('/home/admeen/web/') + len('/home/admeen/web/'):REMOTE_DIR.find('/public_html')]

# Расширения, которые считаем "оригинальными", именно по ПОСЛЕДНЕМУ расширению
ORIGINAL_EXT = {".jpg", ".jpeg", ".png", ".bmp", ".tif", ".tiff", ".jpe"}

# Разрешённые MIME-типы для оригиналов
ALLOWED_MIME = {
    "image/jpeg",
    "image/png",
    "image/bmp",
    "image/tiff",
}

# Папки, которые нужно игнорировать (любого уровня вложенности)
IGNORE_DIRS = {"plugin", "plugins", "js", "modules", "node_modules", "cache", "_kama_thumb_cache", "wp-includes", "wp-admin", "_kama_thumb", "wp-rocket-config", "wp-cloudflare-super-page-cache", "plugins", "mu-plugins", "languages", "backups-dup-lite", "backup-migration", "backup-db"}

LOG_FILE = "ssh_download_log.txt"


# --------------------------
# Логирование
# --------------------------

def log(msg):
    with open(LOG_FILE, "a", encoding="utf-8") as f:
        f.write(msg + "\n")
    print(msg)


# --------------------------
# Определение MIME по сигнатуре файла
# --------------------------

def detect_mime_from_header(header: bytes) -> str | None:
    """
    Очень простой детектор MIME по магическим байтам.
    При необходимости можно расширить.
    """
    # PNG
    if header.startswith(b"\x89PNG\r\n\x1a\n"):
        return "image/png"

    # JPEG
    if header.startswith(b"\xff\xd8\xff"):
        return "image/jpeg"

    # GIF (на всякий случай, если вдруг попадётся)
    if header[:6] in (b"GIF87a", b"GIF89a"):
        return "image/gif"

    # WebP: RIFF....WEBP
    if header.startswith(b"RIFF") and header[8:12] == b"WEBP":
        return "image/webp"

    # AVIF: ...ftypavif в заголовке
    if b"ftypavif" in header[4:16]:
        return "image/avif"

    # BMP
    if header.startswith(b"BM"):
        return "image/bmp"

    # TIFF
    if header.startswith(b"II*\x00") or header.startswith(b"MM\x00*"):
        return "image/tiff"

    return None


def get_remote_mime(sftp, remote_path: str) -> str | None:
    """
    Читает первые байты удалённого файла и пытается определить MIME.
    """
    try:
        with sftp.file(remote_path, "rb") as f:
            header = f.read(64)
    except Exception as e:
        log(f"ERROR reading for MIME detection {remote_path}: {e}")
        return None

    return detect_mime_from_header(header)


# --------------------------
# Проверка: является ли файл ОРИГИНАЛОМ
# --------------------------

# Регулярка, чтобы отсеять явные переконвертированные копии
# типа file.jpg.webp, file.jpeg.avif, file.png.webp и т.п.
DOUBLE_CONVERT_RE = re.compile(
    r'\.(jpe?g|png|bmp|tiff?|jpe)\.(webp|avif)\b'
)


def is_original_image(sftp, remote_path: str, filename: str) -> bool:
    """
    Оригинал = файл, у которого:
    - ПОСЛЕДНЕЕ расширение в наборе ORIGINAL_EXT
    - в имени нет паттерна -WxH (ресайзы)
    - это не бэкап
    - нет паттерна *.jpg.webp / *.jpeg.avif и т.п.
    - MIME по сигнатуре в ALLOWED_MIME
    """
    name = filename.lower()

    # 1) Отсекаем явные двойные расширения вида .jpg.webp / .jpeg.avif и т.п.
    if DOUBLE_CONVERT_RE.search(name):
        return False

    # 2) По последнему расширению: НЕ берём файлы, у которых оно не "оригинальное"
    ext = os.path.splitext(name)[1]
    if ext not in ORIGINAL_EXT:
        return False

    # 3) Отсекаем ресайзы типа image-300x200.jpg
    if re.search(r"-\d+x\d+(?=\.)", name):
        return False

    # 4) Отсекаем очевидные бэкапы
    if (
        ".bk." in name
        or name.endswith(".bk")
        or ".backup." in name
        or name.endswith("~")
    ):
        return False

    # 5) MIME по содержимому
    mime = get_remote_mime(sftp, remote_path)
    if mime is None:
        return False

    if mime not in ALLOWED_MIME:
        return False

    return True


# --------------------------
# Рекурсивное скачивание
# --------------------------

def download_recursive(sftp, remote_path, local_path):
    """
    Возвращает True, если в каталоге или подкаталогах есть скачанные оригиналы.
    """

    try:
        items = sftp.listdir_attr(remote_path)
    except IOError:
        return False

    contains_originals = False

    for item in items:
        remote_item = remote_path + "/" + item.filename
        local_item = os.path.join(local_path, item.filename)

        # Каталог?
        if item.st_mode & 0o40000:

            # Проверка на игнорируемые каталоги
            if item.filename.lower() in IGNORE_DIRS:
                log(f"Skipping directory (ignored): {remote_item}")
                continue

            # Рекурсивный обход
            if download_recursive(sftp, remote_item, local_item):
                contains_originals = True

        else:
            # Проверяем: является ли оригиналом
            if is_original_image(sftp, remote_item, item.filename):
                os.makedirs(local_path, exist_ok=True)
                try:
                    sftp.get(remote_item, local_item)
                    log(f"Downloaded: {remote_item}")
                except Exception as e:
                    log(f"ERROR downloading {remote_item}: {e}")

                contains_originals = True

    return contains_originals


# --------------------------
# Основная функция
# --------------------------

def main():
    log("Connecting via SSH...")

    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())

    try:
        ssh.connect(SSH_HOST, port=SSH_PORT, username=SSH_USER, password=SSH_PASSWORD)
    except Exception as e:
        log(f"SSH connection error: {e}")
        return

    sftp = ssh.open_sftp()

    log(f"Start downloading ORIGINAL images from: {REMOTE_DIR}")
    download_recursive(sftp, REMOTE_DIR, LOCAL_BASE_DIR)

    sftp.close()
    ssh.close()

    log("Done.")


if __name__ == "__main__":
    main()

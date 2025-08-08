import requests
from bs4 import BeautifulSoup
from urllib.parse import urljoin, urlparse, urlunparse
from collections import deque

BASE_URL = "https://lipetsk.zabotadom.ru"
SEARCH_TEXT = "долгожител"
visited = set()
found_links = []
queue = deque([BASE_URL])

def normalize_url(url):
    """Приводит URL к каноническому виду: https -> http, убирает / в конце"""
    parsed = urlparse(url)
    # Приводим схему к http (чтобы http и https считались одинаковыми)
    scheme = 'http'
    # Убираем слеш в конце пути (если не просто /)
    path = parsed.path.rstrip('/') if parsed.path != '/' else '/'
    # Собираем обратно
    normalized = urlunparse((scheme, parsed.netloc.lower(), path, '', '', ''))
    return normalized

def get_links(url):
    """Получает все внутренние ссылки со страницы"""
    try:
        r = requests.get(url, timeout=10)
        r.encoding = r.apparent_encoding
        if r.status_code != 200:
            return [], ""
        soup = BeautifulSoup(r.text, "html.parser")
        links = set()
        for a in soup.find_all("a", href=True):
            href = urljoin(url, a["href"])
            if urlparse(href).netloc == urlparse(BASE_URL).netloc:
                links.add(href.split("#")[0])  # убираем якорь
        return links, r.text.lower()
    except requests.RequestException:
        return [], ""

while queue:
    current_url = queue.popleft()
    norm_url = normalize_url(current_url)
    if norm_url in visited:
        continue
    visited.add(norm_url)

    links, text = get_links(current_url)

    # Проверка наличия строки
    if SEARCH_TEXT.lower() in text:
        print(f"[+] Найдено: {current_url}")
        found_links.append(current_url)

    # Добавляем новые ссылки в очередь
    for link in links:
        if normalize_url(link) not in visited:
            queue.append(link)

# Сохраняем найденные ссылки
with open("found_links.txt", "w", encoding="utf-8") as f:
    for link in found_links:
        f.write(link + "\n")

print(f"Готово! Найдено {len(found_links)} страниц. Ссылки сохранены в found_links.txt")

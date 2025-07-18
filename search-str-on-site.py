import requests
from bs4 import BeautifulSoup
from urllib.parse import urljoin, urlparse
import re

visited = set()
matches = []

def crawl(url, domain):
    if url in visited:
        return
    visited.add(url)
    
    try:
        response = requests.get(url, timeout=5)
        if 'text/html' not in response.headers.get('Content-Type', ''):
            return
        
        html = response.text
        if re.search(r'\b(Феникс|фенякс)\b', html, re.IGNORECASE):
            print(f'🔍 Найдено совпадение на странице: {url}')
            matches.append(url)
        
        soup = BeautifulSoup(html, 'html.parser')
        for link in soup.find_all('a', href=True):
            next_url = urljoin(url, link['href'])
            if urlparse(next_url).netloc == domain:
                crawl(next_url, domain)
    
    except Exception as e:
        print(f'⚠️ Ошибка при обработке {url}: {e}')

# Указать начальную страницу
start_url = 'https://czm-fond.ru/'  # ← замените на ваш сайт
domain = urlparse(start_url).netloc

crawl(start_url, domain)

print('\n✅ Найдены совпадения на следующих страницах:')
for match in matches:
    print(match)

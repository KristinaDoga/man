Файл backup_htaccess_index_on_server.sh запускается в каталоге с папками всех доменов (web), обходит каждый и в public_html создаёт копии файлов .htaccess и index.php
Это нужно делать зараннее, чтобы сохранились здоровые копии, которые потом можно восстановить

В каталоге antivirus:
site_status.json - кэш
check_on_virus.php - файл для крон задачи. Проверяет сайты, отправляет уведомление на почту и в тг, запускает очистку в cleaner.php
cleaner.php - меняет права файлов на стандартные, при наличии здоровых копий перезаписывает .htaccess и index.php, очищает файлы от function _0x , <script>function _0x и ob_start() с мусором, удаляет файлы с stt1 


В планировщик крон задача добавляется с помощью команды
crontab -e

Например, раз в час с 6 утра до 23 вечера

0 8-21 * * * php /home/user/_cron/customScript/check_on_virus.php

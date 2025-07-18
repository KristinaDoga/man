<?php

$files = [
    '/home/admeen/web/klinika-borisova.ru/public_html/sites/all/themes/flumb/js/common.js',
    '/home/admeen/web/klinika-borisova.ru/public_html/sites/korablino/themes/flumb/js/common.js',
    '/home/admeen/web/klinika-borisova.ru/public_html/sites/mihajlov/themes/flumb/js/common.js',
    '/home/admeen/web/klinika-borisova.ru/public_html/sites/novomichurinsk/themes/flumb/js/common.js',
    '/home/admeen/web/klinika-borisova.ru/public_html/sites/ryazhsk/themes/flumb/js/common.js',
    '/home/admeen/web/klinika-borisova.ru/public_html/sites/rybnoe/themes/flumb/js/common.js',
    '/home/admeen/web/klinika-borisova.ru/public_html/sites/sasovo/themes/flumb/js/common.js',
    '/home/admeen/web/klinika-borisova.ru/public_html/sites/shilovo/themes/flumb/js/common.js',
    '/home/admeen/web/klinika-borisova.ru/public_html/sites/skopin/themes/flumb/js/common.js',
];

$jsCode = <<<EOD

// куки попап
jQuery(function(\$) {
  const popupId = 'cookie-popup';
  const storageKey = 'cookieConsentGiven';
  const oneDay = 24 * 60 * 60 * 1000;

  const lastConsent = localStorage.getItem(storageKey);
  const now = Date.now();

  if (!lastConsent || now - lastConsent > oneDay) {
    const \$popup = \$('<div>', {
      id: popupId,
      css: {
        position: 'fixed',
        bottom: 0,
        left: 0,
        right: 0,
        background: '#222',
        color: '#fff',
        padding: '15px 20px',
        fontFamily: 'Arial, sans-serif',
        fontSize: '14px',
        display: 'flex',
        justifyContent: 'space-between',
        alignItems: 'center',
        boxShadow: '0 -2px 8px rgba(0,0,0,0.3)',
        zIndex: 10000
      },
      html: '<span>Этот сайт использует cookie файлы для хранения данных. Продолжая использовать сайт, Вы даете согласие на работу с этими файлами.</span><button class="close-btn" aria-label="Закрыть" style="background: transparent; border: none; color: #fff; font-size: 20px; cursor: pointer; padding: 0; line-height: 1;">&times;</button>'
    });

    \$('body').append(\$popup);

    \$popup.find('.close-btn').on('click', function() {
      localStorage.setItem(storageKey, Date.now());
      \$popup.remove();
    });
  }
});

EOD;

foreach ($files as $file) {
    if (file_exists($file) && is_writable($file)) {
        file_put_contents($file, $jsCode, FILE_APPEND | LOCK_EX);
        echo "✅ Код добавлен в файл: $file\n";
    } else {
        echo "⚠️ Файл не найден или недоступен для записи: $file\n";
    }
}

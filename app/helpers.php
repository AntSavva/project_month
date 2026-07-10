<?php

function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function limit_text(string $value, int $maxLength): string
{
    $value = str_replace("\0", '', $value);
    $value = preg_replace('/[^\P{C}\t\r\n]+/u', '', $value) ?? $value;
    $value = trim(strip_tags($value));

    if ($maxLength < 1) {
        return '';
    }

    $length = function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);

    if ($length <= $maxLength) {
        return $value;
    }

    return function_exists('mb_substr') ? mb_substr($value, 0, $maxLength) : substr($value, 0, $maxLength);
}

function post_text(string $key, int $maxLength = 500, string $default = ''): string
{
    return limit_text((string) ($_POST[$key] ?? $default), $maxLength);
}

function post_choice(string $key, array $allowed, string $default): string
{
    $value = (string) ($_POST[$key] ?? $default);

    return in_array($value, $allowed, true) ? $value : $default;
}

function post_phone(string $key): string
{
    $phone = post_text($key, 40);
    $phone = preg_replace('/[^\d+\s().-]/u', '', $phone) ?? '';
    $digits = preg_replace('/\D+/', '', $phone) ?? '';

    return strlen($digits) >= 5 ? trim($phone) : '';
}

function post_email(string $key): string
{
    $email = post_text($key, 120);

    return $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : '';
}

function page_href(array $page): string
{
    return '/' . trim((string) ($page['slug'] ?? ''), '/');
}

function text_excerpt(string $value, int $length = 150): string
{
    $value = trim(preg_replace('/\s+/u', ' ', strip_tags($value)));
    $valueLength = function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);

    if ($valueLength <= $length) {
        return $value;
    }

    return (function_exists('mb_substr') ? mb_substr($value, 0, $length - 1) : substr($value, 0, $length - 1)) . '...';
}

function site_origin(): string
{
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if ($host !== '') {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        if (strpos($host, 'localhost') === false && strpos($host, '127.0.0.1') === false) {
            $scheme = 'https';
        }

        return $scheme . '://' . $host;
    }

    return 'https://kubera-dom.ru';
}

function absolute_url(string $path): string
{
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }

    return rtrim(site_origin(), '/') . '/' . ltrim($path, '/');
}

function canonical_path(?string $path = null): string
{
    $path = $path ?? parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $path = '/' . trim((string) $path, '/');

    return $path === '/' ? '/' : rtrim($path, '/');
}

function page_seo(array $page): array
{
    $title = trim((string) ($page['seoTitle'] ?? ''));
    $description = trim((string) ($page['seoDescription'] ?? ''));

    if ($title === '') {
        $title = trim((string) ($page['title'] ?? SITE_NAME));
    }

    if ($description === '') {
        $description = trim((string) ($page['menuDescription'] ?? ''));
    }

    return [
        'title' => $title,
        'description' => $description,
        'canonical' => page_href($page),
        'image' => (string) ($page['cover'] ?? ''),
    ];
}

function seo_schema(array $site, string $canonical): array
{
    $settings = $site['settings'] ?? [];
    $sameAs = array_values(array_filter($settings['socials'] ?? [], function ($url) {
        return is_string($url) && $url !== '' && $url !== '/';
    }));

    return [
        '@context' => 'https://schema.org',
        '@type' => 'LocalBusiness',
        '@id' => absolute_url('/#organization'),
        'name' => SITE_NAME,
        'url' => absolute_url('/'),
        'telephone' => $settings['phone'] ?? '',
        'email' => $settings['email'] ?? '',
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => $settings['address'] ?? '',
        ],
        'sameAs' => $sameAs,
        'mainEntityOfPage' => $canonical,
    ];
}

function get_nested(array $data, string $path, $fallback = '')
{
    foreach (explode('.', $path) as $key) {
        if (!is_array($data) || !array_key_exists($key, $data)) {
            return $fallback;
        }

        $data = $data[$key];
    }

    return $data;
}

function asset_url(string $path): string
{
    return '/assets/source/' . ltrim($path, '/');
}

function interior_cover_url(array $page): string
{
    $cover = (string) ($page['cover'] ?? '');

    if ($cover !== '') {
        return $cover;
    }

    $fallback = [
        'interior' => asset_url('images/InteriorCards/library.png'),
        'restorany-bary-kafe' => asset_url('images/InteriorCards/restoraunt.png'),
        'bani-i-bannye-kompleksy' => asset_url('images/InteriorCards/sauna.png'),
    ];

    return $fallback[(string) ($page['slug'] ?? '')] ?? '';
}

function image_alt(string $text, string $fallback = 'Изображение'): string
{
    $alt = trim(preg_replace('/\s+/u', ' ', strip_tags($text)) ?? '');

    if ($alt === '') {
        return $fallback;
    }

    if (function_exists('mb_substr') && function_exists('mb_strlen') && mb_strlen($alt) > 125) {
        return rtrim(mb_substr($alt, 0, 122)) . '...';
    }

    return $alt;
}

function page_image_alt(array $page, string $prefix = 'Фото'): string
{
    $title = (string) ($page['seoTitle'] ?? $page['title'] ?? '');
    $titleParts = preg_split('/\s+—\s+/u', $title);
    $title = is_array($titleParts) ? ($titleParts[0] ?? $title) : $title;

    return image_alt(trim($prefix . ' ' . $title), $prefix . ' ' . SITE_NAME);
}

function review_avatar_alt(array $review): string
{
    return image_alt('Фото клиента ' . (string) ($review['author'] ?? ''), 'Фото клиента ' . SITE_NAME);
}

function icon_html(string $name, string $class = 'icon', bool $hasFill = false): string
{
    $attrs = $hasFill ? ' data-fill=""' : '';

    return '<span class="' . h($class) . '" aria-hidden="true"' . $attrs . ' style="--iconUrl: url(' . h(asset_url('icons/' . $name . '.svg')) . ')"></span>';
}

function phone_href(string $phone): string
{
    return 'tel:' . preg_replace('/[^0-9+]/', '', $phone);
}

function email_href(string $email): string
{
    return 'mailto:' . $email;
}

function social_links(array $settings, bool $withYoutube = false): array
{
    $socials = $settings['socials'] ?? [];
    $items = [
        ['name' => 'vk', 'label' => 'VKontakte'],
        ['name' => 'telegram', 'label' => 'Telegram'],
        ['name' => 'max', 'label' => 'Max'],
    ];

    if ($withYoutube) {
        $items[] = ['name' => 'youtube', 'label' => 'YouTube'];
    }

    return array_values(array_filter(array_map(function ($item) use ($socials) {
        $href = $socials[$item['name']] ?? '';
        if (!$href) {
            return null;
        }

        $item['href'] = $href;
        return $item;
    }, $items)));
}

function content_items(array $block): array
{
    $items = $block['items'] ?? [];

    return is_array($items) ? $items : [];
}

function render_layout(array $site, string $title, string $content, array $seo = []): void
{
    $scriptNonce = base64_encode(random_bytes(16));
    $settings = $site['settings'] ?? [];
    $products = site_pages($site, 'product', true);
    $interiors = site_pages($site, 'interior', true);
    $documents = site_pages($site, 'document', true);
    $phone = $settings['phone'] ?? '';
    $email = $settings['email'] ?? '';
    $description = trim((string) ($seo['description'] ?? ''));
    if ($description === '') {
        $description = text_excerpt($content, 160);
    }
    if ($description === '') {
        $description = $title;
    }
    $canonical = absolute_url(canonical_path($seo['canonical'] ?? null));
    $image = trim((string) ($seo['image'] ?? ''));
    if ($image === '') {
        $image = asset_url('images/Social/background-message.png');
    }
    $image = absolute_url($image);
    $robots = !empty($seo['noindex']) ? 'noindex, nofollow' : 'index, follow';
    $schema = seo_schema($site, $canonical);
    header("Content-Security-Policy: default-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'self'; object-src 'none'; script-src 'self' 'nonce-" . $scriptNonce . "'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; frame-src https://yandex.ru https://*.yandex.ru; connect-src 'self'; upgrade-insecure-requests");

    echo '<!doctype html><html lang="ru"><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . h($title) . '</title>';
    echo '<meta name="description" content="' . h($description) . '">';
    echo '<meta name="robots" content="' . h($robots) . '">';
    echo '<link rel="canonical" href="' . h($canonical) . '">';
    echo '<meta property="og:type" content="website">';
    echo '<meta property="og:site_name" content="' . h(SITE_NAME) . '">';
    echo '<meta property="og:title" content="' . h($title) . '">';
    echo '<meta property="og:description" content="' . h($description) . '">';
    echo '<meta property="og:url" content="' . h($canonical) . '">';
    echo '<meta property="og:image" content="' . h($image) . '">';
    echo '<meta property="og:image:width" content="1749">';
    echo '<meta property="og:image:height" content="925">';
    echo '<meta name="twitter:card" content="summary_large_image">';
    echo '<meta name="twitter:title" content="' . h($title) . '">';
    echo '<meta name="twitter:description" content="' . h($description) . '">';
    echo '<meta name="twitter:image" content="' . h($image) . '">';
    echo '<script type="application/ld+json" nonce="' . h($scriptNonce) . '">' . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
    echo '<link rel="stylesheet" href="/assets/css/base.css?v=20260710-factory-background">';
    echo '<link rel="stylesheet" href="/assets/css/site.css?v=20260710-about-production-high">';
    $bodyClass = trim((string) ($seo['bodyClass'] ?? ''));
    echo '</head><body' . ($bodyClass !== '' ? ' class="' . h($bodyClass) . '"' : '') . '>';
    render_header($products, $interiors, $phone, $email);
    echo '<main class="content">' . $content . '</main>';
    render_footer($settings, $products, $interiors, $documents);
    render_callback_popup($products, $phone);
    render_site_scripts($scriptNonce);
    echo '</body></html>';
}

function sitemap_urls(array $site): array
{
    $urls = [
        ['loc' => '/', 'priority' => '1.0'],
        ['loc' => '/about', 'priority' => '0.7'],
        ['loc' => '/reviews', 'priority' => '0.6'],
        ['loc' => '/contacts', 'priority' => '0.7'],
    ];

    foreach (site_pages($site, null, true) as $page) {
        if (($page['type'] ?? '') === 'document') {
            $priority = '0.4';
        } elseif (($page['type'] ?? '') === 'interior') {
            $priority = '0.8';
        } else {
            $priority = '0.9';
        }

        $urls[] = [
            'loc' => page_href($page),
            'lastmod' => $page['updatedAt'] ?? '',
            'priority' => $priority,
        ];
    }

    $seen = [];
    return array_values(array_filter($urls, function ($url) use (&$seen) {
        $loc = $url['loc'];
        if (isset($seen[$loc])) {
            return false;
        }
        $seen[$loc] = true;
        return true;
    }));
}

function render_sitemap(array $site): void
{
    header('Content-Type: application/xml; charset=utf-8');
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    foreach (sitemap_urls($site) as $url) {
        echo '<url><loc>' . h(absolute_url($url['loc'])) . '</loc>';
        if (!empty($url['lastmod'])) {
            echo '<lastmod>' . h(substr((string) $url['lastmod'], 0, 10)) . '</lastmod>';
        }
        echo '<changefreq>weekly</changefreq><priority>' . h($url['priority']) . '</priority></url>';
    }
    echo '</urlset>';
}

function render_robots(array $site): void
{
    header('Content-Type: text/plain; charset=utf-8');
    echo "User-agent: *\n";
    echo "Disallow: /admin\n";
    echo "Disallow: /lead\n";
    echo "Sitemap: " . absolute_url('/sitemap.xml') . "\n";
}

function render_header(array $products, array $interiors, string $phone, string $email): void
{
    echo '<header class="header" data-js-overlay-menu=""><div class="header__inner">';
    echo '<a class="logo header__logo" href="/" title="Home" aria-label="Home"><img class="logo__image" src="/images/logo.svg" alt="Кубэра" width="216" height="40" loading="eager"></a>';
    echo '<nav class="header__nav" aria-label="Основная навигация"><ul class="header__nav-list">';
    render_header_dropdown('Продукция', $products, asset_url('images/AboutProduction/factory_background.webp'));
    render_header_dropdown('Отделка интерьера', $interiors, asset_url('images/Cases/case-preview.png'), true);
    echo '<li class="header__nav-item"><a class="header__nav-link" href="/about/"><span>О компании</span></a></li>';
    echo '<li class="header__nav-item"><a class="header__nav-link" href="/reviews/"><span>Отзывы</span></a></li>';
    echo '<li class="header__nav-item"><a class="header__nav-link" href="/contacts/"><span>Контакты</span></a></li>';
    echo '</ul></nav>';
    echo '<div class="header__actions"><address class="header__contacts">';
    echo '<a class="header__contact-link" href="' . h(phone_href($phone)) . '">' . icon_html('phone', 'icon header__contact-icon', true) . '<span>' . h($phone) . '</span></a>';
    echo '<a class="header__contact-link" href="' . h(email_href($email)) . '">' . icon_html('mail', 'icon header__contact-icon', true) . '<span>' . h($email) . '</span></a>';
    echo '</address><a class="button header__callback" href="#callback-popup" data-js-callback-open>Записаться на замер</a>';
    echo '<button class="burger-button header__burger visible-tablet" type="button" aria-label="Открыть меню" data-js-overlay-menu-burger-button=""><svg class="burger-button__svg" width="44" height="44" viewBox="0 0 100 100"><path class="burger-button__line burger-button__line--1" d="M 20,29 H 80"/><path class="burger-button__line burger-button__line--2" d="M 20,50 H 80"/><path class="burger-button__line burger-button__line--3" d="M 20,71 H 80"/></svg></button>';
    echo '</div></div>';
    render_overlay_menu($products, $interiors, $phone, $email);
    echo '</header>';
}

function render_overlay_menu(array $products, array $interiors, string $phone, string $email): void
{
    echo '<dialog class="header__overlay-menu" data-js-overlay-menu-dialog="">';
    echo '<div class="header__overlay-menu-inner"><div class="header__overlay-head">';
    echo '<a class="logo header__overlay-logo" href="/" title="Home" aria-label="Home"><img class="logo__image" src="/images/logo.svg" alt="Кубэра" width="216" height="40" loading="lazy"></a>';
    echo '<button class="header__overlay-close" type="button" aria-label="Закрыть меню" data-js-overlay-menu-close=""></button>';
    echo '</div><nav class="header__overlay-nav" aria-label="Мобильная навигация">';

    render_overlay_menu_group('Продукция', $products, true);
    render_overlay_menu_group('Отделка интерьера', $interiors, false);

    echo '<ul class="header__overlay-nav-list header__overlay-nav-list--main">';
    echo '<li class="header__overlay-nav-item"><a class="header__overlay-nav-link" href="/about/">О компании</a></li>';
    echo '<li class="header__overlay-nav-item"><a class="header__overlay-nav-link" href="/reviews/">Отзывы</a></li>';
    echo '<li class="header__overlay-nav-item"><a class="header__overlay-nav-link" href="/contacts/">Контакты</a></li>';
    echo '</ul></nav><div class="header__overlay-footer"><address class="header__overlay-contacts">';
    echo '<a class="header__overlay-contact-link" href="' . h(phone_href($phone)) . '">' . icon_html('phone', 'icon header__overlay-contact-icon', true) . '<span>' . h($phone) . '</span></a>';
    echo '<a class="header__overlay-contact-link" href="' . h(email_href($email)) . '">' . icon_html('mail', 'icon header__overlay-contact-icon', true) . '<span>' . h($email) . '</span></a>';
    echo '</address><a class="button header__overlay-callback" href="#callback-popup" data-js-callback-open>Записаться на замер</a></div></div></dialog>';
}

function render_callback_popup(array $products, string $phone): void
{
    echo '<dialog class="callback-popup" id="callback-popup" data-js-callback-dialog aria-labelledby="callback-popup-title">';
    echo '<div class="callback-popup__body"><button class="callback-popup__close" type="button" data-js-callback-close aria-label="Закрыть форму"></button>';
    echo '<div class="callback-popup__header"><h2 class="callback-popup__title" id="callback-popup-title">Запись на замер</h2><p class="callback-popup__description">В течении часа с вами свяжется наш менеджер</p></div>';
    echo '<form class="callback-popup__form" action="/lead" method="post">' . csrf_input();
    echo '<input type="hidden" name="source" value="callback-popup">';
    echo '<label class="callback-popup__select-wrapper"><span class="visually-hidden">Продукт</span><select class="callback-popup__control" name="product" required><option value="" selected disabled>Продукт</option>';
    foreach ($products as $product) {
        $title = trim((string) ($product['title'] ?? ''));
        if ($title !== '') {
            echo '<option value="' . h($title) . '">' . h($title) . '</option>';
        }
    }
    echo '<option value="Отделка интерьера">Отделка интерьера</option><option value="Другое">Другое</option></select></label>';
    echo '<input class="callback-popup__control" name="name" placeholder="Ваше имя" aria-label="Ваше имя" autocomplete="name" required>';
    echo '<label class="callback-popup__select-wrapper"><span class="visually-hidden">Удобный способ связи</span><select class="callback-popup__control" name="contact_method" required><option value="" selected disabled>Удобный способ связи</option><option value="Телефон">Телефон</option><option value="WhatsApp">WhatsApp</option><option value="Telegram">Telegram</option></select></label>';
    echo '<input class="callback-popup__control" name="phone" placeholder="' . h($phone) . '" aria-label="Телефон" autocomplete="tel" inputmode="tel" required>';
    echo '<button class="button callback-popup__button" type="submit">Записаться на замер</button>';
    echo '<p class="callback-popup__privacy">Нажимая на кнопку, вы соглашаетесь с <a href="/privacy-policy">политикой конфиденциальности</a>.</p></form></div></dialog>';
}

function render_overlay_menu_group(string $label, array $pages, bool $showDescription): void
{
    if (!$pages) {
        return;
    }

    echo '<details class="header__overlay-nav-group" open><summary class="header__overlay-nav-title">' . h($label) . '</summary>';
    echo '<ul class="header__overlay-product-list">';
    foreach ($pages as $page) {
        echo '<li class="header__overlay-product-item"><a class="header__overlay-product-link" href="' . h(page_href($page)) . '">';
        echo '<span class="header__overlay-product-label">' . h($page['title'] ?? '') . '</span>';
        if ($showDescription && !empty($page['menuDescription'])) {
            echo '<span class="header__overlay-product-description">' . h($page['menuDescription']) . '</span>';
        }
        echo '</a></li>';
    }
    echo '</ul></details>';
}

function render_header_dropdown(string $label, array $pages, string $cover, bool $usePageCovers = false): void
{
    if (!$pages) {
        return;
    }

    $previewCover = $cover;

    if ($usePageCovers) {
        foreach ($pages as $page) {
            $pageCover = interior_cover_url($page);

            if ($pageCover !== '') {
                $previewCover = $pageCover;
                break;
            }
        }
    }

    echo '<li class="header__nav-item header__nav-item--dropdown">';
    echo '<button class="header__nav-link" type="button"><span>' . h($label) . '</span><svg class="header__nav-arrow" width="8" height="6" viewBox="0 0 8 6" aria-hidden="true"><path d="M4 6L0.535898 0H7.4641L4 6Z" /></svg></button>';
    echo '<div class="header__mega-menu"><div class="header__mega-menu-inner header__mega-menu-inner--compact"><ul class="header__mega-list">';
    foreach ($pages as $page) {
        $pageCover = $usePageCovers ? interior_cover_url($page) : '';
        $coverAttribute = $pageCover !== '' ? ' data-header-preview-cover="' . h($pageCover) . '"' : '';
        echo '<li class="header__mega-item"><a class="header__mega-link" href="' . h(page_href($page)) . '"' . $coverAttribute . '>' . h($page['title'] ?? '') . '</a></li>';
    }
    echo '</ul><img class="header__mega-cover" src="' . h($previewCover) . '" alt="' . h(image_alt($label . ' Кубэра')) . '" width="730" height="360" data-header-preview-image=""></div></div></li>';
}

function render_site_scripts(string $scriptNonce): void
{
    echo '<script nonce="' . h($scriptNonce) . '">';
    echo <<<'HTML'
document.addEventListener('DOMContentLoaded', function () {
  var callbackDialog = document.querySelector('[data-js-callback-dialog]');

  if (callbackDialog) {
    var closeCallbackDialog = function () {
      if (callbackDialog.open && typeof callbackDialog.close === 'function') {
        callbackDialog.close();
      } else {
        callbackDialog.removeAttribute('open');
        document.documentElement.classList.remove('is-lock');
      }
    };

    document.addEventListener('click', function (event) {
      var opener = event.target.closest('[data-js-callback-open]');

      if (!opener) {
        return;
      }

      event.preventDefault();
      document.documentElement.classList.add('is-lock');

      if (!callbackDialog.open) {
        if (typeof callbackDialog.showModal === 'function') {
          callbackDialog.showModal();
        } else {
          callbackDialog.setAttribute('open', '');
        }
      }
    });

    callbackDialog.addEventListener('click', function (event) {
      if (event.target === callbackDialog || event.target.closest('[data-js-callback-close]')) {
        closeCallbackDialog();
      }
    });

    callbackDialog.addEventListener('close', function () {
      document.documentElement.classList.remove('is-lock');
    });
  }

  document.querySelectorAll('[data-js-overlay-menu]').forEach(function (root) {
    if (root.hasAttribute('data-js-overlay-menu-bound')) {
      return;
    }

    var dialog = root.querySelector('[data-js-overlay-menu-dialog]');
    var burger = root.querySelector('[data-js-overlay-menu-burger-button]');

    if (!dialog || !burger) {
      return;
    }

    var closeMenu = function () {
      burger.classList.remove('is-active');
      document.documentElement.classList.remove('is-lock');

      if (dialog.open && typeof dialog.close === 'function') {
        dialog.close();
      } else {
        dialog.open = false;
      }
    };

    root.setAttribute('data-js-overlay-menu-bound', '');

    burger.addEventListener('click', function (event) {
      event.preventDefault();

      if (dialog.open) {
        closeMenu();
        return;
      }

      burger.classList.add('is-active');
      document.documentElement.classList.add('is-lock');

      if (typeof dialog.showModal === 'function') {
        dialog.showModal();
      } else {
        dialog.open = true;
      }
    });

    dialog.addEventListener('click', function (event) {
      if (
        event.target === dialog ||
        event.target.closest('[data-js-overlay-menu-close]') ||
        event.target.closest('a')
      ) {
        closeMenu();
      }
    });

    dialog.addEventListener('close', function () {
      burger.classList.remove('is-active');
      document.documentElement.classList.remove('is-lock');
    });
  });

  document.querySelectorAll('.header__mega-menu').forEach(function (menu) {
    var image = menu.querySelector('[data-header-preview-image]');

    if (!image) {
      return;
    }

    menu.querySelectorAll('[data-header-preview-cover]').forEach(function (link) {
      var updatePreview = function () {
        var nextCover = link.getAttribute('data-header-preview-cover');

        if (nextCover) {
          image.setAttribute('src', nextCover);
        }
      };

      link.addEventListener('mouseenter', updatePreview);
      link.addEventListener('focus', updatePreview);
    });
  });

  document.querySelectorAll('[data-js-reviews-slider]').forEach(function (slider) {
    if (slider.hasAttribute('data-js-reviews-slider-bound')) {
      return;
    }

    var viewport = slider.querySelector('.reviews__viewport');
    var track = slider.querySelector('[data-js-reviews-slider-track]');
    var prevButton = slider.querySelector('[data-js-reviews-slider-prev]');
    var nextButton = slider.querySelector('[data-js-reviews-slider-next]');

    if (!viewport || !track || !prevButton || !nextButton) {
      return;
    }

    var getStep = function () {
      var firstCard = track.querySelector('.reviews-card');
      var gap = parseFloat(getComputedStyle(track).columnGap) || 0;

      return firstCard ? firstCard.getBoundingClientRect().width + gap : viewport.clientWidth;
    };

    var updateButtons = function () {
      var maxScroll = Math.max(viewport.scrollWidth - viewport.clientWidth - 2, 0);

      prevButton.disabled = viewport.scrollLeft <= 2;
      nextButton.disabled = viewport.scrollLeft >= maxScroll;
    };

    slider.setAttribute('data-js-reviews-slider-bound', '');
    updateButtons();

    prevButton.addEventListener('click', function () {
      viewport.scrollBy({ left: -getStep(), behavior: 'smooth' });
    });

    nextButton.addEventListener('click', function () {
      viewport.scrollBy({ left: getStep(), behavior: 'smooth' });
    });

    viewport.addEventListener('scroll', updateButtons, { passive: true });
    window.addEventListener('resize', updateButtons);
  });

  document.querySelectorAll('[data-js-projects]').forEach(function (root) {
    if (root.hasAttribute('data-js-projects-bound')) {
      return;
    }

    var dialog = root.querySelector('[data-project-gallery-dialog]');
    var image = root.querySelector('[data-project-gallery-image]');
    var dots = root.querySelector('[data-project-gallery-dots]');
    var closeButton = root.querySelector('[data-project-gallery-close]');
    var prevButton = root.querySelector('[data-project-gallery-prev]');
    var nextButton = root.querySelector('[data-project-gallery-next]');

    if (!dialog || !image || !dots || !closeButton || !prevButton || !nextButton) {
      return;
    }

    var galleryItems = [];
    var currentIndex = 0;

    var renderGallery = function () {
      if (!galleryItems.length) {
        return;
      }

      var currentItem = galleryItems[currentIndex];
      image.setAttribute('src', currentItem.src);
      image.setAttribute('alt', currentItem.alt);
      dots.innerHTML = '';

      galleryItems.forEach(function (_, index) {
        var dot = document.createElement('button');
        dot.type = 'button';
        dot.className = 'project-gallery__dot' + (index === currentIndex ? ' is-active' : '');
        dot.setAttribute('aria-label', 'Показать фото ' + (index + 1));
        dot.addEventListener('click', function () {
          currentIndex = index;
          renderGallery();
        });
        dots.appendChild(dot);
      });
    };

    var openGallery = function (dataNode) {
      galleryItems = Array.prototype.slice.call(dataNode.querySelectorAll('[data-gallery-src]')).map(function (node) {
        return {
          src: node.getAttribute('data-gallery-src') || '',
          alt: node.getAttribute('data-gallery-alt') || ''
        };
      }).filter(function (item) {
        return item.src !== '';
      });

      if (!galleryItems.length) {
        return;
      }

      currentIndex = 0;
      renderGallery();
      document.documentElement.classList.add('is-lock');

      if (typeof dialog.showModal === 'function') {
        dialog.showModal();
      } else {
        dialog.setAttribute('open', '');
      }
    };

    var closeGallery = function () {
      if (typeof dialog.close === 'function' && dialog.open) {
        dialog.close();
      } else {
        dialog.removeAttribute('open');
        document.documentElement.classList.remove('is-lock');
      }
    };

    var moveGallery = function (step) {
      if (!galleryItems.length) {
        return;
      }

      currentIndex = (currentIndex + step + galleryItems.length) % galleryItems.length;
      renderGallery();
    };

    root.querySelectorAll('[data-project-card]').forEach(function (card) {
      card.addEventListener('click', function () {
        var dataNode = card.querySelector('.project-card__gallery-data');

        if (dataNode) {
          openGallery(dataNode);
        }
      });
    });

    closeButton.addEventListener('click', closeGallery);
    prevButton.addEventListener('click', function () {
      moveGallery(-1);
    });
    nextButton.addEventListener('click', function () {
      moveGallery(1);
    });

    dialog.addEventListener('click', function (event) {
      var isGalleryControl = event.target.closest('.project-gallery__figure, .project-gallery__arrow, .project-gallery__dots, .project-gallery__close');

      if (!isGalleryControl) {
        closeGallery();
      }
    });

    dialog.addEventListener('close', function () {
      document.documentElement.classList.remove('is-lock');
    });

    dialog.addEventListener('keydown', function (event) {
      if (event.key === 'ArrowLeft') {
        moveGallery(-1);
      }

      if (event.key === 'ArrowRight') {
        moveGallery(1);
      }
    });

    root.setAttribute('data-js-projects-bound', '');
  });
});
HTML;
    echo '</script>';
}

function render_footer(array $settings, array $products, array $interiors, array $documents): void
{
    $phone = $settings['phone'] ?? '';
    $email = $settings['email'] ?? '';

    echo '<footer class="footer"><div class="footer__inner container"><div class="footer__main"><section class="footer__section">';
    echo '<h2 class="footer__title">Юридическая информация</h2><p class="footer__text">' . nl2br(h($settings['legalInfo'] ?? '')) . '</p>';
    echo '<h2 class="footer__title footer__title--spaced">Телефон</h2><a class="footer__link" href="' . h(phone_href($phone)) . '">' . h($phone) . '</a>';
    echo '<h2 class="footer__title footer__title--spaced">Почта</h2><a class="footer__link" href="' . h(email_href($email)) . '">' . h($email) . '</a>';
    echo '<h2 class="footer__title footer__title--spaced">Соц сети</h2><ul class="footer__socials" aria-label="Социальные сети">';
    foreach (social_links($settings, true) as $social) {
        echo '<li class="footer__social-item"><a class="footer__social-link" href="' . h($social['href']) . '" aria-label="' . h($social['label']) . '">' . icon_html($social['name'], 'icon footer__social-icon', true) . '</a></li>';
    }
    echo '</ul></section><nav class="footer__section footer__section--products" aria-label="Продукция"><h2 class="footer__title">Продукция</h2><ul class="footer__list footer__list--products">';
    foreach ($products as $page) {
        echo '<li class="footer__item"><a class="footer__link" href="' . h(page_href($page)) . '">' . h($page['title'] ?? '') . '</a></li>';
    }
    echo '</ul></nav><nav class="footer__section footer__section--interior" aria-label="Интерьер"><h2 class="footer__title">Интерьер</h2><ul class="footer__list">';
    foreach ($interiors as $page) {
        echo '<li class="footer__item"><a class="footer__link" href="' . h(page_href($page)) . '">' . h($page['title'] ?? '') . '</a></li>';
    }
    echo '</ul></nav></div><nav class="footer__documents" aria-label="Документы"><ul class="footer__documents-list">';
    foreach ($documents as $page) {
        echo '<li class="footer__documents-item"><a class="footer__documents-link" href="' . h(page_href($page)) . '">' . h($page['title'] ?? '') . '</a></li>';
    }
    echo '</ul></nav><div class="footer__bottom"><p class="footer__copyright">Все права зарегистрированы</p><a class="footer__developer" href="/">Разработка сайта</a></div></div></footer>';
}

function render_product_cards(array $pages): string
{
    $fallbackImages = [
        asset_url('images/Products/product-primary.png'),
        asset_url('images/Products/product-secondary.png'),
    ];
    $html = '<section class="products container" aria-labelledby="products-title"><h2 class="products__title h2" id="products-title">Что мы производим</h2><div class="products__viewport"><div class="products__grid">';

    foreach ($pages as $index => $page) {
        $image = $page['cover'] ?: $fallbackImages[$index % count($fallbackImages)];
        $description = $page['menuDescription'] ?? ($page['seoDescription'] ?? '');
        $html .= '<a class="products-card" href="' . h(page_href($page)) . '">';
        $html .= '<span class="products-card__picture"><img class="products-card__image" src="' . h($image) . '" alt="' . h(page_image_alt($page)) . '" width="450" height="450" loading="lazy"></span>';
        $html .= '<span class="products-card__content"><span class="products-card__title h3">' . h($page['title'] ?? '') . '</span>';
        if ($description) {
            $html .= '<span class="products-card__description">' . h($description) . '</span>';
        }
        $html .= '</span><span class="products-card__link" aria-hidden="true"><span>Подробнее</span>' . icon_html('arrow-top-right', 'icon products-card__link-icon') . '</span></a>';
    }

    return $html . '</div></div></section>';
}

function render_interior_cards(array $pages): string
{
    $html = '<section class="interior-solutions container" aria-labelledby="interior-solutions-title"><h2 class="interior-solutions__title h2" id="interior-solutions-title">Решения для отделки интерьеров</h2><div class="interior-solutions__grid">';

    foreach ($pages as $page) {
        $description = $page['menuDescription'] ?? ($page['seoDescription'] ?? '');
        $cover = interior_cover_url($page);
        $html .= '<a class="interior-solutions-card" href="' . h(page_href($page)) . '">';
        if ($cover !== '') {
            $html .= '<span class="interior-solutions-card__media"><img class="interior-solutions-card__image" src="' . h($cover) . '" alt="' . h(page_image_alt($page)) . '" width="540" height="304" loading="lazy"></span>';
        } else {
            $html .= '<span class="interior-solutions-card__media" role="img" aria-label="' . h($page['title'] ?? '') . '"></span>';
        }
        $html .= '<span class="interior-solutions-card__content"><span class="interior-solutions-card__title h3">' . h($page['title'] ?? '') . '</span>';
        if ($description) {
            $html .= '<span class="interior-solutions-card__description">' . h($description) . '</span>';
        }
        $html .= '</span><span class="interior-solutions-card__link" aria-hidden="true"><span>Подробнее</span>' . icon_html('arrow-top-right', 'icon interior-solutions-card__link-icon') . '</span></a>';
    }

    return $html . '</div></section>';
}

function render_home_request(array $settings, string $class = 'home-request'): string
{
    $socialClass = $class . '__social';
    $html = '<section class="' . h($class) . ' container" id="request" aria-labelledby="' . h($class) . '-title"><div class="' . h($class) . '__inner"><div class="' . h($class) . '__content"><div class="' . h($class) . '__offer">';
    $html .= '<h2 class="' . h($class) . '__title h2" id="' . h($class) . '-title">Скидка 10% при оформлении комплексного заказа</h2>';
    $html .= '<p class="' . h($class) . '__description">Действует только при полной оплате за проект</p></div>';
    $html .= '<ul class="' . h($class) . '__socials" aria-label="Способы связи">';
    foreach (social_links($settings, true) as $social) {
        $html .= '<li class="' . h($socialClass) . '-item"><a class="' . h($socialClass) . '-link" href="' . h($social['href']) . '" aria-label="' . h($social['label']) . '">' . icon_html($social['name'], 'icon ' . $socialClass . '-icon', true) . '</a></li>';
    }
    $html .= '</ul></div>';
    $html .= render_lead_form($class, $settings['phone'] ?? '', 'home');
    return $html . '</div></section>';
}

function render_lead_form(string $class, string $phone, string $source): string
{
    return '<form class="' . h($class) . '__form" action="/lead" method="post">'
        . csrf_input()
        . '<input type="hidden" name="source" value="' . h($source) . '">'
        . '<input class="' . h($class) . '__control" name="name" placeholder="Григорий" aria-label="Ваше имя" required>'
        . '<input class="' . h($class) . '__control" name="phone" placeholder="' . h($phone) . '" aria-label="Телефон" inputmode="tel" required>'
        . '<textarea class="' . h($class) . '__control ' . h($class) . '__control--textarea" name="comment" placeholder="Комментарий" aria-label="Комментарий"></textarea>'
        . '<button class="button ' . h($class) . '__button" type="submit">Записаться на замер</button>'
        . '<p class="' . h($class) . '__privacy">Нажимая на кнопку, вы соглашаетесь с <a href="/privacy-policy">политикой конфиденциальности</a>.</p>'
        . '</form>';
}

function render_home(array $site): void
{
    $products = site_pages($site, 'product', true);
    $interiors = site_pages($site, 'interior', true);
    $reviews = $site['reviews'] ?? [];
    $settings = $site['settings'] ?? [];

    $content = render_home_hero();
    $content .= render_product_cards($products);
    $content .= render_home_request($settings);
    $content .= render_advantages();
    $content .= render_interior_cards($interiors);
    $content .= render_cases();
    $content .= render_production_showcase();
    $content .= render_reviews_section($reviews);
    $content .= render_work_features();
    $content .= render_modular_house_promo();
    $content .= render_questions($settings);
    $content .= render_route($settings);

    render_layout($site, 'Столярные изделия из дерева на заказ в Санкт-Петербурге — Кубэра', $content, [
        'description' => 'Производим столярные изделия из массива дерева на заказ: лестницы, наличники, обсады, арки, мебель, декор и деревянную отделку в Санкт-Петербурге.',
        'canonical' => '/',
        'image' => asset_url('images/Social/background-message.png'),
    ]);
}

function render_home_hero(): string
{
    $advantages = [
        ['title' => '15 лет мастерства', 'description' => '1500+ реализованных проектов', 'icon' => 'star'],
        ['title' => 'Индивидуальный подход', 'description' => 'Решения под ваш бюджет', 'icon' => 'person'],
        ['title' => 'От дизайна до установки', 'description' => 'Полный цикл без посредников', 'icon' => 'detail'],
    ];
    $html = '<section class="hero" aria-labelledby="hero-title"><img class="hero__bg-image" src="' . h(asset_url('images/Hero/hero-bg.png')) . '" alt="Столярные изделия из массива дерева на заказ" width="1304" height="586" loading="eager">';
    $html .= '<div class="hero__inner container"><div class="hero__content"><p class="hero__eyebrow h3">Санкт-Петербург, Москва и регионы</p><h1 class="hero__title h1" id="hero-title"><span>Производство столярных изделий</span><span class="hero__title-accent">из натуральных пород дерева</span></h1></div>';
    $html .= '<ul class="hero__advantages" aria-label="Преимущества">';
    foreach ($advantages as $item) {
        $html .= '<li class="hero__advantage"><img class="hero__advantage-icon" src="' . h(asset_url('images/CardIcons/' . $item['icon'] . '.png')) . '" alt="" width="64" height="64" loading="eager"><div class="hero__advantage-content"><h2 class="hero__advantage-title h4">' . h($item['title']) . '</h2><p class="hero__advantage-description">' . h($item['description']) . '</p></div></li>';
    }
    return $html . '</ul></div></section>';
}

function render_advantages(): string
{
    $items = [
        ['title' => 'Консультация', 'description' => 'Ответим на все вопросы до замера', 'icon' => 'check'],
        ['title' => 'Оперативный замер', 'description' => 'Приедем в удобное для вас время', 'icon' => 'roulette'],
        ['title' => 'Срок от 3 дней', 'description' => 'Быстро и без потери качества', 'icon' => 'target'],
        ['title' => 'Элитные материалы', 'description' => 'Используем проверенных поставщиков', 'icon' => 'woods'],
        ['title' => 'Дизайн-проект', 'description' => 'Подберем стиль и планировку под бюджет', 'icon' => 'ruler_and_pen'],
        ['title' => 'Гарантия 10 лет', 'description' => 'Бесплатно устраним любые дефекты', 'icon' => 'shield'],
    ];
    $html = '<section class="advantages container" aria-labelledby="advantages-title"><h2 class="advantages__title h2" id="advantages-title">Залог качества нашей работы</h2><div class="advantages__grid">';
    foreach ($items as $item) {
        $html .= '<article class="advantages-card"><div class="advantages-card__content"><h3 class="advantages-card__title h3">' . h($item['title']) . '</h3><p class="advantages-card__description">' . h($item['description']) . '</p></div><img class="advantages-card__image" src="' . h(asset_url('images/CardIcons/' . $item['icon'] . '.png')) . '" alt="" width="320" height="320" loading="lazy"></article>';
    }
    return $html . '</div></section>';
}

function project_groups(): array
{
    return [
        [
            'title' => 'Барные стойки и столешницы из дерева',
            'description' => 'Комплекс работ по изготовлению деревянных барных стоек, столешниц и интерьерных элементов для кухни и зоны отдыха.',
            'images' => [
                ['file' => 'bar1.webp', 'alt' => 'Деревянная барная стойка в интерьере кухни'],
                ['file' => 'bar2.webp', 'alt' => 'Столешница и фасад барной стойки из дерева'],
                ['file' => 'bar3.webp', 'alt' => 'Готовая барная стойка из массива дерева'],
            ],
        ],
        [
            'title' => 'Деревянные наличники и обрамления проемов',
            'description' => 'Наличники, откосы и декоративное обрамление окон и дверей из дерева для домов и интерьеров.',
            'images' => [
                ['file' => 'nalich5.webp', 'alt' => 'Деревянные наличники на высоких окнах в доме'],
                ['file' => 'nalich1.jpg', 'alt' => 'Фрагмент деревянного наличника у окна'],
                ['file' => 'nalich2.jpg', 'alt' => 'Деревянный наличник и подоконник вокруг окна'],
                ['file' => 'nalich3.jpg', 'alt' => 'Деревянные наличники вокруг окон и дверного проема'],
                ['file' => 'nalich4.jpg', 'alt' => 'Белое деревянное обрамление высокого окна с витражом'],
            ],
        ],
        [
            'title' => 'Деревянные конструкции и интерьерный декор',
            'description' => 'Деревянные балки, перегородки, порталы и декоративные конструкции для частных домов и интерьерных проектов.',
            'images' => [
                ['file' => 'CONST1.webp', 'alt' => 'Деревянные порталы и полки в интерьере'],
                ['file' => 'CONST2.webp', 'alt' => 'Декоративные потолочные балки из дерева'],
                ['file' => 'CONST3.webp', 'alt' => 'Деревянная отделка мансардного помещения'],
                ['file' => 'CONST4.webp', 'alt' => 'Деревянные рейки и перегородки в интерьере'],
            ],
        ],
        [
            'title' => 'Библиотеки, кабинеты и деревянная отделка',
            'description' => 'Встроенные библиотеки, стеллажи и деревянная отделка кабинетов под размер с учетом архитектуры помещения.',
            'images' => [
                ['file' => 'inter1.webp', 'alt' => 'Домашняя библиотека с деревянными стеллажами'],
                ['file' => 'inter2.webp', 'alt' => 'Кабинет с деревянной библиотекой и лестницей'],
                ['file' => 'inter3.webp', 'alt' => 'Комната-библиотека с деревянными стеллажами'],
            ],
        ],
        [
            'title' => 'Деревянные лестницы на заказ',
            'description' => 'Лестницы из дерева и комбинированные конструкции с металлическим каркасом, перилами и подсветкой.',
            'images' => [
                ['file' => 'ladd1.webp', 'alt' => 'Деревянная лестница на металлическом каркасе'],
                ['file' => 'ladd2.webp', 'alt' => 'Лестница с деревянными ступенями и металлическими перилами'],
                ['file' => 'ladd3.webp', 'alt' => 'Деревянная лестница с подсветкой ступеней'],
                ['file' => 'ladd4.webp', 'alt' => 'Двухмаршевая деревянная лестница в доме'],
            ],
        ],
    ];
}

function render_project_cases(string $class = 'cases'): string
{
    static $instance = 0;
    $instance++;
    $titleId = $class . '-projects-title-' . $instance;
    $dialogId = $class . '-projects-gallery-' . $instance;
    $projects = project_groups();

    $html = '<section class="' . h($class) . ' project-cases container" aria-labelledby="' . h($titleId) . '" data-js-projects>';
    $html .= '<div class="project-cases__header"><h2 class="project-cases__title h2" id="' . h($titleId) . '">Уже реализованные проекты</h2></div><div class="project-cases__list">';

    foreach ($projects as $index => $project) {
        $images = $project['images'];
        $cover = $images[0];
        $galleryId = $dialogId . '-item-' . $index;
        $html .= '<button class="project-card" type="button" data-project-card aria-haspopup="dialog" aria-controls="' . h($dialogId) . '">';
        $html .= '<span class="project-card__content"><span class="project-card__title h3">' . h($project['title']) . '</span><span class="project-card__description">' . h($project['description']) . '</span><span class="project-card__meta">Подробнее ↗</span></span>';
        $html .= '<span class="project-card__media"><img class="project-card__image" src="' . h(asset_url('images/Projects/' . $cover['file'])) . '" alt="' . h($cover['alt']) . '" width="910" height="520" loading="lazy"></span>';
        $html .= '<span class="project-card__gallery-data" hidden data-project-title="' . h($project['title']) . '" data-project-description="' . h($project['description']) . '" data-project-gallery="' . h($galleryId) . '">';
        foreach ($images as $imageIndex => $image) {
            $html .= '<span data-gallery-src="' . h(asset_url('images/Projects/' . $image['file'])) . '" data-gallery-alt="' . h($image['alt']) . '" data-gallery-index="' . h((string) $imageIndex) . '"></span>';
        }
        $html .= '</span></button>';
    }

    $html .= '</div><dialog class="project-gallery" id="' . h($dialogId) . '" data-project-gallery-dialog>';
    $html .= '<button class="project-gallery__close" type="button" aria-label="Закрыть галерею" data-project-gallery-close>×</button>';
    $html .= '<div class="project-gallery__body">';
    $html .= '<button class="project-gallery__arrow project-gallery__arrow--prev" type="button" aria-label="Предыдущее фото" data-project-gallery-prev><span class="project-gallery__arrow-icon" aria-hidden="true"></span></button>';
    $html .= '<figure class="project-gallery__figure"><img class="project-gallery__image" src="" alt="" data-project-gallery-image></figure>';
    $html .= '<button class="project-gallery__arrow project-gallery__arrow--next" type="button" aria-label="Следующее фото" data-project-gallery-next><span class="project-gallery__arrow-icon" aria-hidden="true"></span></button>';
    $html .= '<div class="project-gallery__dots" data-project-gallery-dots></div></div></dialog></section>';

    return $html;
}

function render_cases(): string
{
    return render_project_cases('cases');
}

function render_production_showcase(): string
{
    $items = [
        ['title' => '1500+ объектов', 'description' => 'Довольные клиенты в каждом районе', 'icon' => 'woods'],
        ['title' => 'Работаем с 2009 года', 'description' => 'Знаем все нюансы отделки за 15 лет', 'icon' => 'medal'],
        ['title' => 'Индивидуальный подход', 'description' => 'Учитываем пожелания и бюджет клиента', 'icon' => 'person_with_star'],
        ['title' => 'Профессиональное оборудование', 'description' => 'Станки с ЧПУ и немецкие линии', 'icon' => 'machine'],
        ['title' => 'Строгий входной контроль качества материалов', 'description' => 'Проверяем каждую партию перед производством', 'icon' => 'loop'],
    ];
    $html = '<section class="production-showcase" aria-labelledby="production-showcase-title"><div class="production-showcase__inner container"><div class="production-showcase__header"><h2 class="production-showcase__title h2" id="production-showcase-title">Создаем красивые, стильные и долговечные изделия</h2><p class="production-showcase__description">Собственное производство изделий в Санкт-Петербурге с минимальными сроками изготовления от 3 дней</p></div><div class="production-showcase__body"><div class="production-showcase__features">';
    foreach ($items as $item) {
        $html .= '<article class="production-showcase-feature"><img class="production-showcase-feature__icon" src="' . h(asset_url('images/CardIcons/' . $item['icon'] . '.png')) . '" alt="" width="64" height="64" loading="lazy"><div class="production-showcase-feature__content"><h3 class="production-showcase-feature__title">' . h($item['title']) . '</h3><p class="production-showcase-feature__description">' . h($item['description']) . '</p></div></article>';
    }
    return $html . '</div><img class="production-showcase__image" src="' . h(asset_url('images/AboutProduction/factory_background.webp')) . '" alt="Производство столярных изделий из дерева" width="910" height="662" loading="lazy"></div></div></section>';
}

function render_about_hero(): string
{
    return '<section class="about-hero container" aria-labelledby="about-hero-title">'
        . '<p class="about-hero__subtitle">О компании</p>'
        . '<h1 class="about-hero__title h1" id="about-hero-title">Миссия КУБЭРЫ - создавать красивые и долговечные изделия из ценных пород дерева для уюта и стиля вашего дома</h1>'
        . '<p class="about-hero__description">Мы вкладываем в каждый проект душу и профессионализм, чтобы наши клиенты получали не просто изделие, а семейную ценность, которая будет радовать их десятилетиями</p>'
        . '</section>';
}

function render_about_values(): string
{
    $items = [
        ['title' => 'Долговечность', 'description' => 'Изделия служат не один десяток лет', 'icon' => 'shield'],
        ['title' => 'Эстетика', 'description' => 'Создание красивых, стильных изделий, которые радуют глаз', 'icon' => 'star'],
        ['title' => 'Честный подход', 'description' => 'Отказ от компромиссов в качестве, вкладываем душу и сердце', 'icon' => 'medal'],
        ['title' => 'Клиентоориентированность', 'description' => 'Создание ценности для клиента, которая дорого стоит', 'icon' => 'person'],
    ];

    $html = '<section class="about-values" aria-label="Ценности компании"><div class="about-values__inner container"><div class="about-values__grid">';
    foreach ($items as $item) {
        $html .= '<article class="about-values-card"><img class="about-values-card__icon" src="' . h(asset_url('images/CardIcons/' . $item['icon'] . '.png')) . '" alt="" width="64" height="64" loading="lazy"><div class="about-values-card__content">';
        $html .= '<h3 class="about-values-card__title">' . h($item['title']) . '</h3><p class="about-values-card__description">' . h($item['description']) . '</p></div></article>';
    }

    return $html . '</div></div></section>';
}

function render_about_production(): string
{
    $steps = [
        [
            'title' => 'Консультация и замер',
            'subtitle' => 'Это первый контакт с клиентом, на котором закладывается основа будущего изделия',
            'details' => [
                'Консультация: специалисты помогают клиенту определиться с выбором породы дерева (дуб, бук, ясень), типом покрытия (например, масло Biofa) и дизайном. Предлагается несколько вариантов исполнения.',
                'Профессиональный замер: на объект выезжает замерщик, который снимает точные размеры с учетом всех особенностей проема или пространства. Это критически важный этап, так как от точности замеров зависит идеальная геометрия и легкость монтажа готового изделия.',
            ],
        ],
        [
            'title' => 'Проектирование и подготовка материала',
            'subtitle' => 'После согласования всех деталей начинается работа в цеху',
            'details' => [
                'Разработка проекта: на основе замеров создается точная модель будущего изделия.',
                'Строгий отбор материала: компания подчеркивает, что использует только качественные заготовки. Древесина предварительно проходит сушку в камере для достижения оптимальной влажности, что предотвращает ее растрескивание и деформацию в будущем.',
                'Склейка щита (если необходимо): для изготовления широких деталей, таких как подоконники или ступени, деревянные ламели склеиваются в щит. Это обеспечивает большую стабильность и прочность изделия по сравнению с цельной доской.',
            ],
        ],
        [
            'title' => 'Изготовление изделия',
            'subtitle' => 'Это основной производственный этап, где заготовка превращается в готовое изделие',
            'details' => [
                'Строгание и калибровка: материал доводится до точных размеров.',
                'Фрезеровка: на станках с ЧПУ или вручную изделию придается нужная форма, профиль и рельеф (например, создается четверть для чистовой обсады или сложный узор для наличников).',
                'Шлифовка: многоэтапная шлифовка поверхности для достижения идеальной гладкости перед нанесением покрытия.',
            ],
        ],
        [
            'title' => 'Финишная отделка',
            'subtitle' => 'От качества этого этапа зависит внешний вид и долговечность изделия',
            'details' => [
                'Покрытие лакокрасочными материалами (ЛКМ): изделия покрываются профессиональным маслом Biofa. Масло не создает пленки, а пропитывает древесину, защищая ее изнутри, сохраняя тактильную теплоту и подчеркивая натуральную текстуру дерева.',
                'Сушка покрытия: изделия выдерживаются до полного высыхания и полимеризации покрытия.',
            ],
        ],
        [
            'title' => 'Монтаж «под ключ»',
            'subtitle' => 'Завершающий этап, который так же важен, как и само изготовление',
            'details' => [
                'Доставка: готовое изделие упаковывается и бережно доставляется на объект.',
                'Профессиональная установка: монтажники компании устанавливают изделие. Благодаря тому, что они же выполняли и замер, монтаж проходит быстро и точно. Клиенты в отзывах часто отмечают аккуратность и высокую квалификацию монтажной бригады.',
            ],
        ],
    ];

    $html = '<section class="about-production" aria-labelledby="about-production-title"><div class="about-production__inner container"><h2 class="about-production__title h2" id="about-production-title">Производство</h2><div class="about-production__list">';
    foreach ($steps as $index => $step) {
        $html .= '<article class="about-production-step"><div class="about-production-step__number" aria-hidden="true">' . ($index + 1) . '</div><div class="about-production-step__header">';
        $html .= '<h3 class="about-production-step__title">' . h($step['title']) . '</h3><p class="about-production-step__subtitle">' . h($step['subtitle']) . '</p></div><div class="about-production-step__details">';
        foreach ($step['details'] as $detail) {
            $html .= '<p>' . h($detail) . '</p>';
        }
        $html .= '</div></article>';
    }

    return $html . '</div></div></section>';
}

function render_about(array $site): void
{
    $settings = $site['settings'] ?? [];
    $body = render_about_hero();
    $body .= render_about_values();
    $body .= render_about_production();
    $body .= render_route($settings);

    render_layout($site, 'О компании Кубэра — столярное производство в Санкт-Петербурге', $body, [
        'description' => 'Кубэра — столярное производство в Санкт-Петербурге: проектируем, изготавливаем и монтируем изделия из массива дерева для домов и интерьеров.',
        'canonical' => '/about',
        'image' => asset_url('images/AboutProduction/factory_background.webp'),
    ]);
}

function render_reviews_section(array $reviews): string
{
    $reviews = array_values(array_filter($reviews, function ($review) {
        return ($review['status'] ?? 'published') !== 'draft';
    }));
    if (!$reviews) {
        return '';
    }

    $visibleReviews = array_slice($reviews, 0, 10);
    $html = '<section class="reviews container" aria-labelledby="reviews-title">';
    $html .= '<div class="reviews__header"><h2 class="reviews__title h2" id="reviews-title">Отзывы клиентов</h2></div>';
    $html .= '<div class="reviews__slider" data-js-reviews-slider=""><div class="reviews__viewport"><div class="reviews__track" data-js-reviews-slider-track="">';

    foreach ($visibleReviews as $index => $review) {
        $popupId = 'review-popup-' . ($index + 1);
        $html .= '<article class="reviews-card"><p class="reviews-card__text">' . h($review['text'] ?? '') . '</p>';
        $html .= '<a class="reviews-card__more" href="#' . h($popupId) . '"><span>Читать полностью</span>' . icon_html('arrow-top-right', 'icon reviews-card__more-icon') . '</a>';
        $html .= '<footer class="reviews-card__author">';
        if (!empty($review['avatar'])) {
            $html .= '<img class="reviews-card__avatar" src="' . h($review['avatar']) . '" alt="' . h(review_avatar_alt($review)) . '" loading="lazy">';
        } else {
            $html .= '<span class="reviews-card__avatar" aria-hidden="true"></span>';
        }
        $html .= '<span class="reviews-card__meta"><span class="reviews-card__name">' . h($review['author'] ?? '') . '</span><span class="reviews-card__date">' . h($review['date'] ?? '') . '</span></span></footer></article>';
    }

    $html .= '</div></div>';
    $html .= '<div class="reviews__controls" aria-label="Навигация по отзывам">';
    $html .= '<button class="reviews__arrow reviews__arrow--prev" type="button" aria-label="Предыдущие отзывы" data-js-reviews-slider-prev="">' . icon_html('arrow-left', 'icon reviews__arrow-icon') . '</button>';
    $html .= '<button class="reviews__arrow reviews__arrow--next" type="button" aria-label="Следующие отзывы" data-js-reviews-slider-next="">' . icon_html('arrow-right', 'icon reviews__arrow-icon') . '</button>';
    $html .= '</div></div>';
    $html .= '<a class="button reviews__button" href="/reviews">Посмотреть все отзывы</a>';

    foreach ($visibleReviews as $index => $review) {
        $popupId = 'review-popup-' . ($index + 1);
        $titleId = 'review-popup-title-' . ($index + 1);
        $html .= '<div class="reviews-popup" id="' . h($popupId) . '" role="dialog" aria-modal="true" aria-labelledby="' . h($titleId) . '">';
        $html .= '<a class="reviews-popup__backdrop" href="#reviews-title" aria-label="Закрыть отзыв"></a><article class="reviews-popup__body">';
        $html .= '<a class="reviews-popup__close" href="#reviews-title" aria-label="Закрыть">x</a><div class="reviews-popup__author">';
        if (!empty($review['avatar'])) {
            $html .= '<img class="reviews-popup__avatar" src="' . h($review['avatar']) . '" alt="' . h(review_avatar_alt($review)) . '" loading="lazy">';
        } else {
            $html .= '<span class="reviews-popup__avatar" aria-hidden="true"></span>';
        }
        $html .= '<div class="reviews-popup__meta"><h3 class="reviews-popup__title h3" id="' . h($titleId) . '">' . h($review['author'] ?? '') . '</h3><p class="reviews-popup__date">' . h($review['date'] ?? '') . '</p></div></div>';
        $html .= '<p class="reviews-popup__text">' . h($review['text'] ?? '') . '</p></article></div>';
    }

    return $html . '</section>';
}

function visible_reviews(array $reviews): array
{
    return array_values(array_filter($reviews, function ($review) {
        return ($review['status'] ?? 'published') !== 'draft';
    }));
}

function render_reviews_page(array $site): void
{
    $settings = $site['settings'] ?? [];
    $reviews = visible_reviews($site['reviews'] ?? []);
    $filters = [
        ['label' => 'Все', 'value' => 'all'],
        ['label' => 'Наличники', 'value' => 'trim'],
        ['label' => 'Обсады', 'value' => 'frames'],
        ['label' => 'Подоконники', 'value' => 'sills'],
        ['label' => 'Лестницы', 'value' => 'stairs'],
        ['label' => 'Малые архитектурные формы', 'value' => 'small-forms'],
    ];

    $body = '<section class="reviews-hero" aria-labelledby="reviews-hero-title" data-js-reviews-page="">';
    $body .= '<div class="reviews-hero__inner container"><div class="reviews-hero__content">';
    $body .= '<p class="reviews-hero__subtitle">Отзывы</p><h1 class="reviews-hero__title h1" id="reviews-hero-title">Мнение наших клиентов</h1></div>';
    $body .= '<div class="reviews-hero__toolbar"><label class="reviews-hero__type"><span>Тип: <span data-js-reviews-filter-current="">Все</span></span>';
    $body .= '<select class="reviews-hero__type-select" name="reviews-type" aria-label="Тип отзыва" data-js-reviews-filter-select="">';
    foreach ($filters as $filter) {
        $body .= '<option value="' . h($filter['value']) . '">' . h($filter['label']) . '</option>';
    }
    $body .= '</select><span class="reviews-hero__select-icon" aria-hidden="true"></span></label>';
    $body .= '<div class="reviews-hero__filters" aria-label="Категории отзывов" role="tablist">';
    foreach ($filters as $index => $filter) {
        $activeClass = $index === 0 ? ' is-active' : '';
        $selected = $index === 0 ? 'true' : 'false';
        $body .= '<button class="reviews-hero__filter' . $activeClass . '" type="button" role="tab" aria-selected="' . $selected . '" data-js-reviews-filter="' . h($filter['value']) . '">' . h($filter['label']) . '</button>';
    }
    $body .= '</div><label class="reviews-hero__sort"><span>Порядок:</span><select class="reviews-hero__sort-select" name="reviews-order" data-js-reviews-sort="">';
    $body .= '<option value="default">По умолчанию</option><option value="new">Сначала новые</option><option value="old">Сначала старые</option>';
    $body .= '</select><span class="reviews-hero__select-icon" aria-hidden="true"></span></label></div></div>';
    $body .= '<div class="reviews-hero__grid container" data-js-reviews-grid="">';

    if (!$reviews) {
        $body .= '<p class="reviews-hero__empty">Отзывы пока не добавлены.</p>';
    }

    foreach ($reviews as $index => $review) {
        $popupId = 'review-page-popup-' . ($index + 1);
        $category = $review['category'] ?? 'all';
        $order = $review['order'] ?? $index;
        $body .= '<article class="reviews-card" data-category="' . h($category) . '" data-order="' . h($order) . '"><p class="reviews-card__text">' . h($review['text'] ?? '') . '</p>';
        $body .= '<a class="reviews-card__more" href="#' . h($popupId) . '"><span>Читать полностью</span>' . icon_html('arrow-top-right', 'icon reviews-card__more-icon') . '</a>';
        $body .= '<footer class="reviews-card__author">';
        if (!empty($review['avatar'])) {
            $body .= '<img class="reviews-card__avatar" src="' . h($review['avatar']) . '" alt="' . h(review_avatar_alt($review)) . '" loading="lazy">';
        } else {
            $body .= '<span class="reviews-card__avatar" aria-hidden="true"></span>';
        }
        $body .= '<span class="reviews-card__meta"><span class="reviews-card__name">' . h($review['author'] ?? '') . '</span><span class="reviews-card__date">' . h($review['date'] ?? '') . '</span></span></footer></article>';
    }

    $body .= '</div>';
    foreach ($reviews as $index => $review) {
        $popupId = 'review-page-popup-' . ($index + 1);
        $titleId = 'review-page-popup-title-' . ($index + 1);
        $body .= '<div class="reviews-popup" id="' . h($popupId) . '" role="dialog" aria-modal="true" aria-labelledby="' . h($titleId) . '">';
        $body .= '<a class="reviews-popup__backdrop" href="#reviews-hero-title" aria-label="Закрыть отзыв"></a><article class="reviews-popup__body">';
        $body .= '<a class="reviews-popup__close" href="#reviews-hero-title" aria-label="Закрыть">x</a><div class="reviews-popup__author">';
        if (!empty($review['avatar'])) {
            $body .= '<img class="reviews-popup__avatar" src="' . h($review['avatar']) . '" alt="' . h(review_avatar_alt($review)) . '" loading="lazy">';
        } else {
            $body .= '<span class="reviews-popup__avatar" aria-hidden="true"></span>';
        }
        $body .= '<div class="reviews-popup__meta"><h3 class="reviews-popup__title h3" id="' . h($titleId) . '">' . h($review['author'] ?? '') . '</h3><p class="reviews-popup__date">' . h($review['date'] ?? '') . '</p></div></div>';
        $body .= '<p class="reviews-popup__text">' . h($review['text'] ?? '') . '</p></article></div>';
    }
    $body .= '</section>';
    $body .= render_questions($settings);
    $body .= render_route($settings);

    render_layout($site, 'Отзывы клиентов о столярных изделиях Кубэра', $body, [
        'description' => 'Отзывы клиентов о деревянных наличниках, обсадах, лестницах, МАФах и других столярных изделиях Кубэра с производством и монтажом.',
        'canonical' => '/reviews',
    ]);
}

function render_contacts_hero(array $settings): string
{
    $phone = $settings['phone'] ?? '';
    $email = $settings['email'] ?? '';
    $socials = $settings['socials'] ?? [];
    $cards = [
        ['icon' => 'phone', 'title' => 'Телефон отдела продаж', 'value' => $phone, 'href' => phone_href($phone)],
        ['icon' => 'mail', 'title' => 'E-mail', 'value' => $email, 'href' => email_href($email)],
        ['icon' => 'telegram', 'title' => 'Telegram для связи', 'value' => 'Ссылка', 'href' => $socials['telegram'] ?? '/'],
        ['icon' => 'max', 'title' => 'Мессенджер MAX', 'value' => 'Ссылка', 'href' => $socials['max'] ?? '/'],
    ];

    $html = '<section class="contacts-hero" aria-labelledby="contacts-hero-title"><div class="contacts-hero__inner container">';
    $html .= '<div class="contacts-hero__content"><p class="contacts-hero__subtitle">Контакты</p><h1 class="contacts-hero__title h1" id="contacts-hero-title">Всегда остаемся на связи</h1></div>';
    $html .= '<ul class="contacts-hero__list">';
    foreach ($cards as $card) {
        $html .= '<li class="contacts-hero__item"><a class="contacts-hero-card" href="' . h($card['href']) . '">';
        $html .= '<span class="contacts-hero-card__icon" aria-hidden="true">' . icon_html($card['icon'], 'icon', true) . '</span>';
        $html .= '<span class="contacts-hero-card__content"><span class="contacts-hero-card__title">' . h($card['title']) . '</span><span class="contacts-hero-card__value">' . h($card['value']) . '</span></span>';
        $html .= '</a></li>';
    }

    return $html . '</ul></div></section>';
}

function render_contacts_schedule(): string
{
    return '<section class="contacts-schedule" aria-labelledby="contacts-schedule-title"><div class="contacts-schedule__inner container">'
        . '<h2 class="contacts-schedule__title h2" id="contacts-schedule-title">Режим работы</h2>'
        . '<div class="contacts-schedule__grid"><article class="contacts-schedule__card contacts-schedule__card--worktime">'
        . '<span class="contacts-schedule__icon" aria-hidden="true"><img class="contacts-schedule__icon-image" src="' . h(asset_url('images/ContactsSchedule/clock.svg')) . '" alt="" width="28" height="28" loading="lazy"></span>'
        . '<p class="contacts-schedule__worktime">Понедельник-пятница: с 9:00 до 19:00<br>Суббота-воскресенье: выходные дни</p>'
        . '</article><article class="contacts-schedule__card contacts-schedule__card--notice">'
        . '<p class="contacts-schedule__notice"><strong>Внимание!</strong> В нерабочее время мы принимаем заказы только через наш сайт, по электронной почте или в Telegram. Если вы напишете нам в выходные - мы ответим в течение ближайшего рабочего часа.</p>'
        . '</article></div></div></section>';
}

function render_contacts_page(array $site): void
{
    $settings = $site['settings'] ?? [];
    $body = render_contacts_hero($settings);
    $body .= render_contacts_schedule();
    $body .= render_questions($settings);
    $body .= render_route($settings);

    render_layout($site, 'Контакты Кубэра — заказать замер столярных изделий', $body, [
        'description' => 'Контакты Кубэра для заказа замера и консультации по столярным изделиям из дерева, деревянной отделке и индивидуальным проектам в Санкт-Петербурге.',
        'canonical' => '/contacts',
    ]);
}

function render_work_features(): string
{
    $items = [
        ['title' => 'Древесина экстра-класса и 1 сорта', 'description' => 'На наших изделиях вы не найдете сколы, трещины и другие дефекты', 'icon' => 'star'],
        ['title' => 'Фиксированная стоимость', 'description' => 'Цена не меняется, если не меняется профиль работ', 'icon' => 'rubles', 'class' => ' work-features-card--last-mobile'],
        ['title' => 'Условия в договоре', 'description' => 'Срок изготовления, гарантия на монтаж и конструкцию, критерии проверки качества', 'icon' => 'detail'],
        ['title' => 'Большой запас прочности', 'description' => 'Учитываем множество факторов, обязательно соблюдаем ГОСТы и СНиПы', 'icon' => 'shield'],
    ];
    $html = '<section class="work-features container" aria-labelledby="work-features-title"><h2 class="work-features__title h2" id="work-features-title">Особенности нашей работы</h2><div class="work-features__grid">';
    foreach ($items as $item) {
        $html .= '<article class="work-features-card' . h($item['class'] ?? '') . '"><div class="work-features-card__content"><h3 class="work-features-card__title h3">' . h($item['title']) . '</h3><p class="work-features-card__description">' . h($item['description']) . '</p></div><img class="work-features-card__decor" src="' . h(asset_url('images/CardIcons/' . $item['icon'] . '.png')) . '" alt="" width="189" height="189" loading="lazy"></article>';
    }
    return $html . '</div></section>';
}

function render_modular_house_promo(): string
{
    return '<section class="modular-house-promo container" aria-labelledby="modular-house-promo-title">'
        . '<div class="modular-house-promo__inner">'
        . '<img class="modular-house-promo__image" src="' . h(asset_url('images/Promo/modular-house.png')) . '" alt="Модульный деревянный дом от Кубэра" width="1840" height="752" loading="lazy">'
        . '<div class="modular-house-promo__content">'
        . '<h2 class="modular-house-promo__title h2" id="modular-house-promo-title">Спроектируем и соберём ваш дом<br>за 2 месяца</h2>'
        . '<p class="modular-house-promo__description">Мы знаем, как обращаться с деревом. Теперь строим из него целые дома<br>по модульной технологии. Быстро, тепло и без усадки</p>'
        . '<a class="button modular-house-promo__button" href="#request">Подробнее</a>'
        . '</div></div></section>';
}

function render_standards(bool $isOpen = true): string
{
    $items = [
        ['title' => 'Продукция мебельного производства. Термины и определения', 'code' => 'ГОСТ 20400-80'],
        ['title' => 'Мебель. Общие технические условия', 'code' => 'ГОСТ 16371-2014'],
        ['title' => 'Мебель для сидения и лежания. Общие технические условия', 'code' => 'ГОСТ 19917-2014'],
        ['title' => 'Межкомнатные двери. Общие технические условия', 'code' => 'ГОСТ 30211-94'],
    ];
    $openAttribute = $isOpen ? ' open' : '';
    $html = '<section class="standards container" aria-labelledby="standards-title"><details class="standards__details"' . $openAttribute . '><summary class="standards__header"><h2 class="standards__title h2" id="standards-title">Основные нормативные документы</h2><span class="standards__summary"><span>Подробнее</span><span class="standards__summary-icon" aria-hidden="true"></span></span></summary><div class="standards__list">';
    foreach ($items as $item) {
        $html .= '<article class="standards__item"><h3 class="standards__item-title">' . h($item['title']) . '</h3><p class="standards__item-code">' . h($item['code']) . '</p><a class="standards__link" href="/"><span>Посмотреть</span><span class="standards__link-icon" aria-hidden="true">' . icon_html('folder') . '</span></a></article>';
    }
    return $html . '</div></details></section>';
}

function render_questions(array $settings): string
{
    $html = '<section class="questions container" aria-labelledby="questions-title"><div class="questions__inner"><div class="questions__content"><div class="questions__header"><h2 class="questions__title h2" id="questions-title">Остались вопросы?</h2><p class="questions__description">Оставьте заявку или свяжитесь с нами удобным способом</p></div><ul class="questions__socials" aria-label="Способы связи">';
    foreach (social_links($settings) as $social) {
        $html .= '<li class="questions__social-item"><a class="questions__social-link" href="' . h($social['href']) . '" aria-label="' . h($social['label']) . '">' . icon_html($social['name'], 'icon questions__social-icon', true) . '</a></li>';
    }
    $html .= '</ul><div class="questions__manager"><img class="questions__manager-photo" src="' . h(asset_url('images/Questions/manager-photo.png')) . '" alt="Григорий Карпинский, менеджер продаж Кубэра" width="96" height="96" loading="lazy"><div class="questions__manager-info"><p class="questions__manager-name">Григорий Карпинский</p><p class="questions__manager-position">Менеджер продаж</p></div></div></div>';
    $html .= render_lead_form('questions', $settings['phone'] ?? '', 'questions');
    return $html . '</div></section>';
}

function render_route(array $settings): string
{
    return '<section class="route container" aria-labelledby="route-title"><div class="route__header"><h2 class="route__title h2" id="route-title">Как к нам добраться?</h2><div class="route__info"><address class="route__address">' . h($settings['address'] ?? '') . '<br>' . h($settings['workingHours'] ?? '') . '</address><a class="route__link" href="https://yandex.ru/maps/-/CPcFYA8G" target="_blank" rel="noreferrer"><span>Построить маршрут</span><span class="route__link-icon" aria-hidden="true">' . icon_html('location-minus') . '</span></a></div></div><div class="route__map"><iframe class="route__map-iframe" src="https://yandex.ru/map-widget/v1/?um=constructor%3A0ac116e0529e203ff85ff75480538831d74af8ef092c310fd14db9f30abf4248&amp;source=constructor" width="649" height="495" frameborder="0" title="Карта проезда"></iframe></div></section>';
}

function card_icon_url(?string $icon, string $fallback): string
{
    $map = [
        'rulerAndPen' => 'ruler_and_pen',
        'personWithStar' => 'person_with_star',
        'shieldPlain' => 'shield_1',
    ];
    $name = $icon ? ($map[$icon] ?? $icon) : $fallback;

    return asset_url('images/CardIcons/' . $name . '.png');
}

function material_image_url(?string $image, string $fallback): string
{
    return $image ?: asset_url('images/ServiceMaterials/' . $fallback . '.png');
}

function render_service_hero(array $page, array $contentData, array $settings): string
{
    $hero = $contentData['hero'] ?? [];
    $cover = $page['cover'] ?: asset_url('images/ServiceHero/hero-image.png');
    $html = '<section class="service-hero" aria-labelledby="service-hero-title"><div class="service-hero__inner container"><div class="service-hero__content">';
    $html .= '<p class="service-hero__subtitle">' . h($hero['subtitle'] ?? 'Столярные изделия') . '</p>';
    $html .= '<h1 class="service-hero__title h1" id="service-hero-title">' . h($hero['title'] ?? $page['title'] ?? '') . '<span> ' . h($hero['accent'] ?? '') . '</span></h1>';
    $html .= '<form class="service-hero__form" action="/lead" method="post">' . csrf_input() . '<input type="hidden" name="source" value="' . h($page['slug'] ?? 'service') . '"><div class="service-hero__fields">';
    $html .= '<input class="service-hero__control" name="name" placeholder="Григорий" aria-label="Ваше имя" required>';
    $html .= '<input class="service-hero__control" name="phone" placeholder="' . h($settings['phone'] ?? '') . '" aria-label="Телефон" inputmode="tel" required>';
    $html .= '<textarea class="service-hero__control service-hero__control--textarea" name="comment" placeholder="Комментарий" aria-label="Комментарий"></textarea>';
    $html .= '<button class="button service-hero__button" type="submit">Записаться на замер</button></div><p class="service-hero__privacy">Нажимая на кнопку, вы соглашаетесь с <a href="/privacy-policy">политикой конфиденциальности</a>.</p></form></div>';

    return $html . '<img class="service-hero__image" src="' . h($cover) . '" alt="' . h(page_image_alt($page, 'Фото изделия')) . '" width="730" height="730" loading="eager"></div></section>';
}

function render_service_includes(?array $data): string
{
    if (!$data) {
        return '';
    }

    $fallbackIcons = ['roulette', 'machine', 'car', 'level'];
    $items = content_items($data);
    $html = '<section class="service-includes container" aria-labelledby="service-includes-title"><header class="service-includes__header">';
    $html .= '<h2 class="service-includes__title h2" id="service-includes-title">' . h($data['title'] ?? 'Что входит в услугу') . '</h2>';
    if (!empty($data['description'])) {
        $html .= '<p class="service-includes__description">' . h($data['description']) . '</p>';
    }
    $html .= '</header><ul class="service-includes__list">';
    foreach ($items as $index => $item) {
        $icon = card_icon_url($item['icon'] ?? null, $fallbackIcons[$index % count($fallbackIcons)]);
        $html .= '<li class="service-includes__item"><article class="service-includes-card"><div class="service-includes-card__content">';
        $html .= '<h3 class="service-includes-card__title h3">' . h($item['title'] ?? '') . '</h3><p class="service-includes-card__description">' . h($item['description'] ?? '') . '</p></div>';
        $html .= '<picture class="service-includes-card__picture"><img class="service-includes-card__image" src="' . h($icon) . '" alt="" width="454" height="454" loading="lazy"></picture></article></li>';
    }

    return $html . '</ul></section>';
}

function render_service_materials(?array $data): string
{
    if (!$data) {
        return '';
    }

    $fallback = ['oak', 'ash', 'larch', 'pine', 'walnut', 'beech'];
    $items = content_items($data);
    $html = '<section class="service-materials container" aria-labelledby="service-materials-title"><h2 class="service-materials__title h2" id="service-materials-title">' . h($data['title'] ?? 'Материалы') . '</h2><ul class="service-materials__list">';
    foreach ($items as $index => $item) {
        $title = is_string($item) ? $item : ($item['title'] ?? '');
        $image = is_array($item) ? ($item['image'] ?? '') : '';
        $html .= '<li class="service-materials__item"><article class="service-materials-card" style="--material-image: url(' . h(material_image_url($image, $fallback[$index % count($fallback)])) . ')"><h3 class="service-materials-card__title h3">' . h($title) . '</h3></article></li>';
    }

    return $html . '</ul></section>';
}

function render_service_icon_cards(?array $data, string $section, array $fallbackIcons, string $fallbackTitle = ''): string
{
    if (!$data) {
        return '';
    }

    $items = content_items($data);
    $html = '<section class="' . h($section) . ($section === 'service-benefits' ? '' : ' container') . '" aria-labelledby="' . h($section) . '-title">';
    if ($section === 'service-benefits') {
        $html .= '<div class="service-benefits__inner container">';
    }
    $html .= '<div class="' . h($section) . '__header"><h2 class="' . h($section) . '__title h2" id="' . h($section) . '-title">' . h($data['title'] ?? $fallbackTitle) . '</h2>';
    if (!empty($data['description'])) {
        $html .= '<p class="' . h($section) . '__description">' . h($data['description']) . '</p>';
    }
    $html .= '</div><' . ($section === 'service-benefits' ? 'ul' : 'div') . ' class="' . h($section) . ($section === 'service-benefits' ? '__list' : '__grid') . '">';
    foreach ($items as $index => $item) {
        $icon = card_icon_url($item['icon'] ?? null, $fallbackIcons[$index % count($fallbackIcons)]);
        if ($section === 'service-benefits') {
            $cardItems = !empty($item['items']) && is_array($item['items']) ? $item['items'] : array_values(array_filter([$item['description'] ?? '']));
            $html .= '<li class="service-benefits-card"><img class="service-benefits-card__decor" src="' . h($icon) . '" alt="" width="150" height="150" loading="lazy"><div class="service-benefits-card__content"><h3 class="service-benefits-card__title h3">' . h($item['title'] ?? '') . '</h3><ul class="service-benefits-card__items">';
            foreach ($cardItems as $line) {
                $html .= '<li class="service-benefits-card__item">' . h($line) . '</li>';
            }
            $html .= '</ul></div><a class="service-benefits-card__link" href="/"><span>Подробнее</span>' . icon_html('arrow-top-right', 'icon service-benefits-card__icon') . '</a></li>';
        } else {
            $html .= '<article class="service-colors-card"><div class="service-colors-card__content"><h3 class="service-colors-card__title h3">' . h($item['title'] ?? '') . '</h3><p class="service-colors-card__description">' . h($item['description'] ?? '') . '</p></div><img class="service-colors-card__decor" src="' . h($icon) . '" alt="" width="189" height="189" loading="lazy"></article>';
        }
    }
    $html .= '</' . ($section === 'service-benefits' ? 'ul' : 'div') . '>';
    if ($section === 'service-benefits') {
        $html .= '</div>';
    }

    return $html . '</section>';
}

function render_service_plans(?array $data): string
{
    if (!$data) {
        return '';
    }

    $fallbackIcons = ['box', 'star', 'medal'];
    $items = content_items($data);
    $html = '<section class="service-plans" aria-labelledby="service-plans-title"><div class="service-plans__inner container"><h2 class="service-plans__title h2" id="service-plans-title">' . h($data['title'] ?? 'Варианты сотрудничества') . '</h2><ul class="service-plans__list">';
    foreach ($items as $index => $item) {
        $icon = card_icon_url($item['icon'] ?? null, $fallbackIcons[$index % count($fallbackIcons)]);
        $html .= '<li class="service-plans-card"><div class="service-plans-card__body"><div class="service-plans-card__content"><img class="service-plans-card__icon" src="' . h($icon) . '" alt="" width="100" height="100" loading="lazy"><h3 class="service-plans-card__title h3">' . h($item['title'] ?? '') . '</h3><ul class="service-plans-card__items">';
        foreach (($item['items'] ?? []) as $line) {
            $html .= '<li class="service-plans-card__item">' . h($line) . '</li>';
        }
        $html .= '</ul></div><a class="button service-plans-card__button" href="#callback-popup" data-js-callback-open>Записаться на замер</a></div></li>';
    }

    return $html . '</ul></div></section>';
}

function render_items(array $items): string
{
    if (!$items) {
        return '';
    }

    $html = '<div class="feature-grid">';
    foreach ($items as $item) {
        if (is_string($item)) {
            $html .= '<article class="feature"><h3>' . h($item) . '</h3></article>';
            continue;
        }

        $html .= '<article class="feature"><h3>' . h($item['title'] ?? '') . '</h3>';
        if (!empty($item['description'])) {
            $html .= '<p>' . h($item['description']) . '</p>';
        }
        if (!empty($item['items']) && is_array($item['items'])) {
            $html .= '<ul>';
            foreach ($item['items'] as $line) {
                $html .= '<li>' . h($line) . '</li>';
            }
            $html .= '</ul>';
        }
        $html .= '</article>';
    }

    return $html . '</div>';
}

function render_faq(array $items): string
{
    if (!$items) {
        return '';
    }

    $html = '<div class="faq">';
    foreach ($items as $item) {
        $html .= '<details><summary>' . h($item['question'] ?? '') . '</summary>';
        $html .= '<p>' . h($item['answer'] ?? '') . '</p></details>';
    }

    return $html . '</div>';
}

function render_request_form(string $source = 'site'): string
{
    return '<section class="service-request container"><div class="service-request__inner"><div class="service-request__content"><div class="service-request__offer"><h2 class="service-request__title h2">Оставить заявку</h2><p class="service-request__description">Расскажите о задаче, и мы свяжемся с вами.</p></div></div>' . render_lead_form('service-request', '', $source) . '</div></section>';
}

function render_service_request(array $settings, string $source, string $serviceTitle): string
{
    $html = '<section class="service-request container" id="request" aria-labelledby="service-request-title"><div class="service-request__inner"><div class="service-request__content"><div class="service-request__offer">';
    $html .= '<h2 class="service-request__title h2" id="service-request-title">Обсудим ваш проект: ' . h($serviceTitle) . '</h2><ul class="service-request__benefits"><li>Оперативный выезд на замер</li><li>Скидка 10%</li></ul></div>';
    $html .= '<div class="service-request__contacts"><p class="service-request__description">Оставьте заявку или свяжитесь с нами удобным способом</p><ul class="service-request__socials" aria-label="Способы связи">';
    foreach (social_links($settings) as $social) {
        $html .= '<li class="service-request__social-item"><a class="service-request__social-link" href="' . h($social['href']) . '" aria-label="' . h($social['label']) . '">' . icon_html($social['name'], 'icon service-request__social-icon', true) . '</a></li>';
    }
    $html .= '</ul></div></div>' . render_lead_form('service-request', $settings['phone'] ?? '', $source) . '</div></section>';

    return $html;
}

function render_service_options(): string
{
    $items = [
        ['title' => 'Интеграция с интерьером и экстерьером', 'description' => 'Подберем профиль под ваши двери и окна'],
        ['title' => 'Уникальные технологии и материалы', 'description' => 'Лазерная резка и обработка от влаги'],
    ];
    $html = '<section class="service-options" aria-labelledby="service-options-title"><div class="service-options__inner container"><h2 class="service-options__title h2" id="service-options-title">Дополнительные опции и возможности</h2><ul class="service-options__list">';
    foreach ($items as $item) {
        $html .= '<li class="service-options-card" style="--card-bg: url(' . h(asset_url('images/ServiceOptions/card-bg.png')) . ')"><div class="service-options-card__content"><h3 class="service-options-card__title h3">' . h($item['title']) . '</h3><p class="service-options-card__description">' . h($item['description']) . '</p></div><a class="service-options-card__link" href="/"><span>Подробнее</span>' . icon_html('arrow-top-right', 'icon service-options-card__icon') . '</a></li>';
    }

    return $html . '</ul></div></section>';
}

function render_service_cases(): string
{
    return render_project_cases('service-cases');
}

function render_service_process(): string
{
    $steps = [
        ['title' => 'Профессиональная консультация', 'description' => 'Поможем выбрать материал и конструкцию, предложим проверенные решения и назовем примерную стоимость'],
        ['title' => 'Оперативный замер на объекте', 'description' => 'Точные замеры рабочих габаритов предотвращают проблемы с монтажом'],
        ['title' => 'Разработка 3D-модели', 'description' => 'Разработаем модель изделия с размерами, схемой сборки и точным расчетом стоимости'],
        ['title' => 'Изготовление на производстве', 'description' => 'Детали создаются на высокоточных станках с последующей шлифовкой и полировкой'],
        ['title' => 'Предварительная сборка', 'description' => 'На производстве собираем и подгоняем детали, чтобы исключить задержки установки'],
        ['title' => 'Сборка на объекте', 'description' => 'Доставка и установка на объекте без лишнего мусора и грязи'],
    ];
    $html = '<section class="service-process" aria-labelledby="service-process-title"><div class="service-process__inner container"><h2 class="service-process__title h2" id="service-process-title">Процесс работы</h2><ol class="service-process__list">';
    foreach ($steps as $index => $step) {
        $html .= '<li class="service-process__item"><span class="service-process__number" aria-hidden="true">' . ($index + 1) . '</span><div class="service-process__content"><h3 class="service-process__item-title h3">' . h($step['title']) . '</h3><p class="service-process__description">' . h($step['description']) . '</p></div></li>';
    }

    return $html . '</ol></div></section>';
}

function render_service_faq(array $items): string
{
    if (!$items) {
        $items = [
            ['question' => 'Сколько времени занимает изготовление?', 'answer' => 'Срок зависит от материала, объема и сложности профиля. После замера мы фиксируем этапы работ и называем понятный срок производства.'],
            ['question' => 'Можно ли подобрать цвет под интерьер?', 'answer' => 'Да, подберем оттенок по образцу, каталогу или фотографии.'],
            ['question' => 'Вы выезжаете на замер?', 'answer' => 'Да, специалист выезжает на объект и учитывает особенности монтажа.'],
        ];
    }

    $html = '<section class="service-faq container" aria-labelledby="service-faq-title"><div class="service-faq__header"><h2 class="service-faq__title h2" id="service-faq-title">Частые вопросы</h2></div><div class="service-faq__list">';
    foreach ($items as $item) {
        if (empty($item['question']) && empty($item['answer'])) {
            continue;
        }
        $html .= '<details class="service-faq__item"><summary class="service-faq__summary"><span class="service-faq__question">' . h($item['question'] ?? '') . '</span><span class="service-faq__icon" aria-hidden="true"></span></summary><div class="service-faq__content"><p class="service-faq__answer">' . h($item['answer'] ?? '') . '</p></div></details>';
    }

    return $html . '</div></section>';
}

function render_interior_hero(array $page, array $contentData, array $settings): string
{
    $hero = $contentData['hero'] ?? [];
    $image = $hero['image'] ?? (interior_cover_url($page) ?: asset_url('images/ServiceHero/hero-image.png'));
    $html = '<section class="interior-hero" aria-labelledby="interior-hero-title"><div class="interior-hero__inner container"><div class="interior-hero__content">';
    $html .= '<p class="interior-hero__eyebrow">' . h($hero['subtitle'] ?? 'Внутренняя отделка') . '</p>';
    $html .= '<h1 class="interior-hero__title h1" id="interior-hero-title"><span>' . h($hero['title'] ?? $page['title'] ?? '') . '</span><span class="interior-hero__title-accent">' . h($hero['accent'] ?? '') . '</span></h1>';
    $html .= '<form class="interior-hero__form" action="/lead" method="post">' . csrf_input() . '<input type="hidden" name="source" value="' . h($page['slug'] ?? 'interior') . '"><div class="interior-hero__fields">';
    $html .= '<input class="interior-hero__control" name="name" placeholder="Григорий" aria-label="Ваше имя" required>';
    $html .= '<input class="interior-hero__control" name="phone" placeholder="' . h($settings['phone'] ?? '') . '" aria-label="Телефон" inputmode="tel" required>';
    $html .= '<button class="button interior-hero__button" type="submit">Записаться на замер</button></div><p class="interior-hero__privacy">Нажимая на кнопку, вы соглашаетесь с <a href="/privacy-policy">политикой конфиденциальности</a>.</p></form></div>';

    return $html . '<img class="interior-hero__image" src="' . h($image) . '" alt="' . h(page_image_alt($page, 'Фото отделки')) . '" width="730" height="730" loading="eager"></div></section>';
}

function render_interior_includes(?array $data): string
{
    if (!$data) {
        return '';
    }

    $items = content_items($data);
    $html = '<section class="interior-includes container" aria-labelledby="interior-includes-title"><h2 class="interior-includes__title h2" id="interior-includes-title">' . h($data['title'] ?? 'Что входит в услугу') . '</h2><div class="interior-includes__grid">';
    foreach ($items as $item) {
        $html .= '<article class="interior-includes-card"><div class="interior-includes-card__content"><h3 class="interior-includes-card__title h3">' . h($item['title'] ?? '') . '</h3><p class="interior-includes-card__description">' . h($item['description'] ?? '') . '</p></div>';
        $html .= '<img class="interior-includes-card__decor" src="' . h(asset_url('images/Advantages/advantage-decor.png')) . '" alt="" width="454" height="454" loading="lazy"></article>';
    }

    return $html . '</div></section>';
}

function render_interior_room_solutions(?array $data): string
{
    if (!$data) {
        return '';
    }

    $items = content_items($data);
    $html = '<section class="interior-room-solutions container" aria-labelledby="interior-room-solutions-title"><h2 class="interior-room-solutions__title h2" id="interior-room-solutions-title">' . h($data['title'] ?? 'Специальные решения для вашего типа помещения') . '</h2><div class="interior-room-solutions__grid">';
    foreach ($items as $item) {
        $html .= '<article class="interior-room-solutions-card" style="--card-bg: url(' . h(asset_url('images/ServiceOptions/card-bg.png')) . ')"><h3 class="interior-room-solutions-card__title h3">' . h($item['title'] ?? '') . '</h3><ul class="interior-room-solutions-card__list">';
        foreach (($item['items'] ?? []) as $line) {
            $html .= '<li class="interior-room-solutions-card__item">' . h($line) . '</li>';
        }
        $html .= '</ul></article>';
    }

    return $html . '</div></section>';
}

function render_interior_proposal_request(array $settings, string $source): string
{
    $html = '<section class="interior-proposal-request container" id="request" aria-labelledby="interior-proposal-request-title"><div class="interior-proposal-request__inner"><div class="interior-proposal-request__content"><div class="interior-proposal-request__offer">';
    $html .= '<h2 class="interior-proposal-request__title h2" id="interior-proposal-request-title">Подготовим детальное коммерческое предложение</h2><p class="interior-proposal-request__description">Оперативный выезд на замер и скидка 10%</p></div>';
    $html .= '<div class="interior-proposal-request__contacts"><p class="interior-proposal-request__contacts-text">Оставьте заявку или свяжитесь с нами удобным способом</p><ul class="interior-proposal-request__socials" aria-label="Способы связи">';
    foreach (social_links($settings, true) as $social) {
        $html .= '<li class="interior-proposal-request__social-item"><a class="interior-proposal-request__social-link" href="' . h($social['href']) . '" aria-label="' . h($social['label']) . '">' . icon_html($social['name'], 'icon interior-proposal-request__social-icon', true) . '</a></li>';
    }
    $html .= '</ul></div></div>';
    $html .= '<form class="interior-proposal-request__form" action="/lead" method="post">' . csrf_input() . '<input type="hidden" name="source" value="' . h($source) . '">';
    $html .= '<input class="interior-proposal-request__control" name="name" placeholder="Григорий" aria-label="Ваше имя" required>';
    $html .= '<input class="interior-proposal-request__control" name="phone" placeholder="' . h($settings['phone'] ?? '') . '" aria-label="Телефон" inputmode="tel" required>';
    $html .= '<textarea class="interior-proposal-request__control interior-proposal-request__control--textarea" name="comment" placeholder="Комментарий" aria-label="Комментарий"></textarea>';
    $html .= '<button class="button interior-proposal-request__button" type="submit">Записаться на замер</button><p class="interior-proposal-request__privacy">Нажимая на кнопку, вы соглашаетесь с <a href="/privacy-policy">политикой конфиденциальности</a>.</p></form></div></section>';

    return $html;
}

function render_interior_advantages(?array $data): string
{
    if (!$data) {
        return '';
    }

    $items = content_items($data);
    $html = '<section class="interior-advantages container" aria-labelledby="interior-advantages-title"><header class="interior-advantages__header"><h2 class="interior-advantages__title h2" id="interior-advantages-title">' . h($data['title'] ?? 'Преимущества') . '</h2>';
    if (!empty($data['description'])) {
        $html .= '<p class="interior-advantages__description">' . h($data['description']) . '</p>';
    }
    $html .= '</header><div class="interior-advantages__grid">';
    foreach ($items as $item) {
        $html .= '<article class="interior-advantages-card" style="--card-bg: url(' . h(asset_url('images/ServiceOptions/card-bg.png')) . ')"><div class="interior-advantages-card__content"><h3 class="interior-advantages-card__title h3">' . h($item['title'] ?? '') . '</h3><p class="interior-advantages-card__description">' . h($item['description'] ?? '') . '</p></div>';
        $html .= '<a class="interior-advantages-card__link" href="/"><span>Подробнее</span>' . icon_html('arrow-top-right', 'icon interior-advantages-card__icon') . '</a></article>';
    }

    return $html . '</div></section>';
}

function render_interior_plans(?array $data): string
{
    if (!$data) {
        return '';
    }

    $fallbackIcons = ['box', 'star', 'medal'];
    $items = content_items($data);
    $html = '<section class="interior-plans container" aria-labelledby="interior-plans-title"><h2 class="interior-plans__title h2" id="interior-plans-title">' . h($data['title'] ?? 'Варианты сотрудничества') . '</h2><div class="interior-plans__grid">';
    foreach ($items as $index => $item) {
        $featured = !empty($item['isFeatured']) ? ' interior-plans-card--featured' : '';
        $icon = card_icon_url($item['icon'] ?? null, $fallbackIcons[$index % count($fallbackIcons)]);
        $html .= '<article class="interior-plans-card' . h($featured) . '"><div class="interior-plans-card__body"><div class="interior-plans-card__content"><img class="interior-plans-card__icon" src="' . h($icon) . '" alt="" width="100" height="100" loading="lazy">';
        $html .= '<h3 class="interior-plans-card__title h3">' . h($item['title'] ?? '') . '</h3><ul class="interior-plans-card__items">';
        foreach (($item['items'] ?? []) as $line) {
            $html .= '<li class="interior-plans-card__item">' . h($line) . '</li>';
        }
        $html .= '</ul></div><a class="button interior-plans-card__button" href="#callback-popup" data-js-callback-open>Записаться</a></div></article>';
    }

    return $html . '</div></section>';
}

function render_interior_questions(array $settings): string
{
    $html = '<section class="interior-questions container" aria-labelledby="interior-questions-title"><div class="interior-questions__inner"><div class="interior-questions__content"><div class="interior-questions__header"><h2 class="interior-questions__title h2" id="interior-questions-title">Остались вопросы?</h2><p class="interior-questions__description">Оставьте заявку или свяжитесь с нами удобным способом</p></div><ul class="interior-questions__socials" aria-label="Способы связи">';
    foreach (social_links($settings, true) as $social) {
        $html .= '<li class="interior-questions__social-item"><a class="interior-questions__social-link" href="' . h($social['href']) . '" aria-label="' . h($social['label']) . '">' . icon_html($social['name'], 'icon interior-questions__social-icon', true) . '</a></li>';
    }
    $html .= '</ul><div class="interior-questions__manager"><img class="interior-questions__manager-photo" src="' . h(asset_url('images/Questions/manager-photo.png')) . '" alt="Григорий Карпинский, менеджер продаж Кубэра" width="96" height="96" loading="lazy"><div class="interior-questions__manager-info"><p class="interior-questions__manager-name">Григорий Карпинский</p><p class="interior-questions__manager-position">Менеджер продаж</p></div></div></div>';
    $html .= render_lead_form('interior-questions', $settings['phone'] ?? '', 'interior-questions');

    return $html . '</div></section>';
}

function render_document_page(array $page, array $contentData): string
{
    $title = trim((string)($contentData['h1'] ?? ''));
    if ($title === '') {
        $title = (string)($page['title'] ?? 'Документ');
    }

    $text = (string)($contentData['text'] ?? '');
    $lines = preg_split("/\r\n|\r|\n/", $text);
    if ($lines === false || count($lines) === 0) {
        $lines = [''];
    }

    $html = '<section class="document-hero container" aria-labelledby="document-hero-title">';
    $html .= '<p class="document-hero__subtitle">Документ</p>';
    $html .= '<h1 class="document-hero__title h1" id="document-hero-title">' . h($title) . '</h1>';
    $html .= '</section>';
    $html .= '<section class="document-content" aria-label="Содержание документа"><div class="document-content__inner container"><article class="document-content__section"><div class="document-content__text">';

    foreach ($lines as $line) {
        $html .= '<p>' . ($line === '' ? '&nbsp;' : h($line)) . '</p>';
    }

    return $html . '</div></article></div></section>';
}

function render_page(array $site, array $page): void
{
    $type = $page['type'] ?? 'product';
    $contentData = $page['content'] ?? [];
    $seo = page_seo($page);

    if ($type === 'document') {
        $body = render_document_page($page, $contentData);
        render_layout($site, $seo['title'], $body, $seo);
        return;
    }

    if ($type === 'product') {
        $settings = $site['settings'] ?? [];
        $body = render_service_hero($page, $contentData, $settings);
        $body .= render_service_includes($contentData['includes'] ?? null);
        $body .= render_service_materials($contentData['materials'] ?? null);
        $body .= render_service_icon_cards($contentData['colors'] ?? null, 'service-colors', ['tree', 'target', 'lines', 'color', 'shield', 'woods'], 'Цветовые решения');
        $slug = $page['slug'] ?? 'service';
        $body .= render_service_request($settings, $slug, $page['title'] ?? 'изделие из дерева');
        $body .= render_service_icon_cards($contentData['benefits'] ?? null, 'service-benefits', ['shield_1', 'star', 'medal', 'person'], 'Преимущества');
        if ($slug === 'nalichniki') {
            $body .= render_service_plans($contentData['plans'] ?? null);
        }
        $body .= render_home_request($settings);
        $body .= render_service_options();
        $body .= render_service_cases();
        $body .= render_service_process();
        $body .= render_reviews_section($site['reviews'] ?? []);
        $body .= render_service_faq($contentData['faq']['items'] ?? []);
        $body .= render_questions($settings);
        $body .= render_route($settings);

        render_layout($site, $seo['title'], $body, $seo);
        return;
    }

    if ($type === 'interior') {
        $settings = $site['settings'] ?? [];
        $body = render_interior_hero($page, $contentData, $settings);
        $body .= render_interior_includes($contentData['includes'] ?? null);
        $body .= render_interior_room_solutions($contentData['roomSolutions'] ?? null);
        $body .= render_service_materials($contentData['materials'] ?? null);
        $body .= render_interior_proposal_request($settings, $page['slug'] ?? 'interior');
        $body .= render_interior_advantages($contentData['advantages'] ?? null);
        $body .= render_interior_plans($contentData['plans'] ?? null);
        $body .= render_home_request($settings);
        $body .= render_cases();
        $body .= render_reviews_section($site['reviews'] ?? []);
        $body .= render_service_faq($contentData['faq']['items'] ?? []);
        $body .= render_interior_questions($settings);
        $body .= render_route($settings);

        render_layout($site, $seo['title'], $body, $seo);
        return;
    }

    $hero = $contentData['hero'] ?? [];
    $isInterior = $type === 'interior';
    $heroClass = $isInterior ? 'interior-hero' : 'service-hero';
    $body = '<section class="' . h($heroClass) . ' container"><div class="' . h($heroClass) . '__content"><p class="' . h($heroClass) . '__subtitle">' . h($hero['subtitle'] ?? '') . '</p>';
    $body .= '<h1 class="' . h($heroClass) . '__title h1">' . h($hero['title'] ?? $page['title'] ?? '') . ' <span>' . h($hero['accent'] ?? '') . '</span></h1></div>';
    if (!empty($page['cover'])) {
        $body .= '<img class="' . h($heroClass) . '__image" src="' . h($page['cover']) . '" alt="' . h(page_image_alt($page)) . '" loading="lazy">';
    }
    $body .= '</section>';

    $blocks = [
        'includes' => $isInterior ? 'Что входит в услугу' : 'Что входит в услугу',
        'roomSolutions' => 'Специальные решения',
        'materials' => 'Материалы',
        'colors' => 'Дополнительные опции',
        'benefits' => 'Преимущества',
        'advantages' => 'Преимущества',
        'plans' => 'Варианты сотрудничества',
    ];

    foreach ($blocks as $key => $fallbackTitle) {
        if (empty($contentData[$key]) || !is_array($contentData[$key])) {
            continue;
        }
        $block = $contentData[$key];
        $body .= '<section class="section"><h2>' . h($block['title'] ?? $fallbackTitle) . '</h2>';
        if (!empty($block['description'])) {
            $body .= '<p class="section-lead">' . h($block['description']) . '</p>';
        }
        $body .= render_items(content_items($block));
        $body .= '</section>';
    }

    $body .= '<section class="section"><h2>Частые вопросы</h2>' . render_faq($contentData['faq']['items'] ?? []) . '</section>';
    $body .= render_request_form($page['slug'] ?? 'page');

    render_layout($site, $seo['title'], $body, $seo);
}

function render_not_found(array $site): void
{
    $content = '<section class="not-found-hero" aria-labelledby="not-found-title"><div class="not-found-hero__content">';
    $content .= '<p class="not-found-hero__code" aria-hidden="true">404</p>';
    $content .= '<h1 class="not-found-hero__title" id="not-found-title">Страница не найдена</h1>';
    $content .= '<a class="button not-found-hero__button" href="/">На главную</a></div></section>';

    render_layout($site, 'Страница не найдена', $content, [
        'canonical' => canonical_path(),
        'noindex' => true,
        'bodyClass' => 'not-found-page',
    ]);
}

function handle_lead_submit(): void
{
    if (!csrf_verify((string) ($_POST['csrf_token'] ?? ''))) {
        http_response_code(403);
        render_not_found(site_read());
        return;
    }

    $name = post_text('name', 80);
    $phone = post_phone('phone');
    $email = post_email('email');
    $comment = post_text('comment', 1000);
    $product = post_text('product', 120);
    $contactMethod = post_text('contact_method', 40);
    $source = post_text('source', 80, 'site') ?: 'site';

    $details = [];
    if ($product !== '') {
        $details[] = 'Продукт: ' . $product;
    }
    if ($contactMethod !== '') {
        $details[] = 'Способ связи: ' . $contactMethod;
    }
    if ($comment !== '') {
        $details[] = $comment;
    }
    $comment = implode("\n", $details);

    if ($name === '' || $phone === '') {
        header('Location: /?sent=0');
        return;
    }

    $leads = leads_read();
    $leads[] = [
        'id' => create_id('lead'),
        'createdAt' => date(DATE_ATOM),
        'status' => 'new',
        'name' => $name,
        'phone' => $phone,
        'email' => $email,
        'comment' => $comment,
        'source' => $source,
    ];
    leads_write($leads);
    header('Location: /?sent=1');
}

<?php

function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
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

function render_layout(array $site, string $title, string $content): void
{
    $settings = $site['settings'] ?? [];
    $products = site_pages($site, 'product', true);
    $interiors = site_pages($site, 'interior', true);
    $documents = site_pages($site, 'document', true);
    $phone = $settings['phone'] ?? '';
    $email = $settings['email'] ?? '';

    echo '<!doctype html><html lang="ru"><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . h($title) . '</title>';
    echo '<link rel="stylesheet" href="/_next/static/css/d64a07c86d44443e.css">';
    echo '<link rel="stylesheet" href="/assets/css/site.css">';
    echo '</head><body>';
    render_header($products, $interiors, $phone, $email);
    echo '<main class="content">' . $content . '</main>';
    render_footer($settings, $products, $interiors, $documents);
    render_site_scripts();
    echo '</body></html>';
}

function render_header(array $products, array $interiors, string $phone, string $email): void
{
    echo '<header class="header" data-js-overlay-menu=""><div class="header__inner">';
    echo '<a class="logo header__logo" href="/" title="Home" aria-label="Home"><img class="logo__image" src="/images/logo.svg" alt="" width="216" height="40" loading="eager"></a>';
    echo '<nav class="header__nav" aria-label="Основная навигация"><ul class="header__nav-list">';
    render_header_dropdown('Продукция', $products, asset_url('images/AboutProduction/production-photo.png'));
    render_header_dropdown('Отделка интерьера', $interiors, asset_url('images/Cases/case-preview.png'), true);
    echo '<li class="header__nav-item"><a class="header__nav-link" href="/about/"><span>О компании</span></a></li>';
    echo '<li class="header__nav-item"><a class="header__nav-link" href="/reviews/"><span>Отзывы</span></a></li>';
    echo '<li class="header__nav-item"><a class="header__nav-link" href="/contacts/"><span>Контакты</span></a></li>';
    echo '</ul></nav>';
    echo '<div class="header__actions"><address class="header__contacts">';
    echo '<a class="header__contact-link" href="' . h(phone_href($phone)) . '">' . icon_html('phone', 'icon header__contact-icon', true) . '<span>' . h($phone) . '</span></a>';
    echo '<a class="header__contact-link" href="' . h(email_href($email)) . '">' . icon_html('mail', 'icon header__contact-icon', true) . '<span>' . h($email) . '</span></a>';
    echo '</address><a class="button header__callback" href="#request">Записаться на замер</a>';
    echo '<button class="burger-button header__burger visible-tablet" type="button" aria-label="Открыть меню" data-js-overlay-menu-burger-button=""><svg class="burger-button__svg" width="44" height="44" viewBox="0 0 100 100"><path class="burger-button__line burger-button__line--1" d="M 20,29 H 80"/><path class="burger-button__line burger-button__line--2" d="M 20,50 H 80"/><path class="burger-button__line burger-button__line--3" d="M 20,71 H 80"/></svg></button>';
    echo '</div></div>';
    render_overlay_menu($products, $interiors, $phone, $email);
    echo '</header>';
}

function render_overlay_menu(array $products, array $interiors, string $phone, string $email): void
{
    echo '<dialog class="header__overlay-menu" data-js-overlay-menu-dialog="">';
    echo '<div class="header__overlay-menu-inner"><div class="header__overlay-head">';
    echo '<a class="logo header__overlay-logo" href="/" title="Home" aria-label="Home"><img class="logo__image" src="/images/logo.svg" alt="" width="216" height="40" loading="lazy"></a>';
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
    echo '</address><a class="button header__overlay-callback" href="#request">Записаться на замер</a></div></div></dialog>';
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
    echo '</ul><img class="header__mega-cover" src="' . h($previewCover) . '" alt="" width="730" height="360" data-header-preview-image=""></div></div></li>';
}

function render_site_scripts(): void
{
    echo <<<'HTML'
<script>
document.addEventListener('DOMContentLoaded', function () {
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
});
</script>
HTML;
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
        $html .= '<span class="products-card__picture"><img class="products-card__image" src="' . h($image) . '" alt="" width="450" height="450" loading="lazy"></span>';
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
            $html .= '<span class="interior-solutions-card__media"><img class="interior-solutions-card__image" src="' . h($cover) . '" alt="" width="540" height="304" loading="lazy"></span>';
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
        . '<input type="hidden" name="source" value="' . h($source) . '">'
        . '<input class="' . h($class) . '__control" name="name" placeholder="Григорий" aria-label="Ваше имя" required>'
        . '<input class="' . h($class) . '__control" name="phone" placeholder="' . h($phone) . '" aria-label="Телефон" inputmode="tel" required>'
        . '<textarea class="' . h($class) . '__control ' . h($class) . '__control--textarea" name="comment" placeholder="Комментарий" aria-label="Комментарий"></textarea>'
        . '<button class="button ' . h($class) . '__button" type="submit">Записаться на замер</button>'
        . '<p class="' . h($class) . '__privacy">Нажимая на кнопку, вы соглашаетесь с политикой конфиденциальности.</p>'
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

    render_layout($site, SITE_NAME, $content);
}

function render_home_hero(): string
{
    $advantages = [
        ['title' => '15 лет мастерства', 'description' => 'Подпись', 'icon' => 'star'],
        ['title' => 'Индивидуальный подход', 'description' => 'Подпись', 'icon' => 'person'],
        ['title' => 'От дизайна до установки', 'description' => 'Подпись', 'icon' => 'detail'],
    ];
    $html = '<section class="hero" aria-labelledby="hero-title"><img class="hero__bg-image" src="' . h(asset_url('images/Hero/hero-bg.png')) . '" alt="" width="1304" height="586" loading="eager">';
    $html .= '<div class="hero__inner container"><div class="hero__content"><p class="hero__eyebrow h3">Столярные изделия</p><h1 class="hero__title h1" id="hero-title"><span>Производство эксклюзивных</span><span class="hero__title-accent">столярных изделий из массива</span></h1></div>';
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

function render_cases(): string
{
    $html = '<section class="cases container" aria-labelledby="cases-title"><div class="cases__header"><h2 class="cases__title h2" id="cases-title">Уже реализованные проекты</h2></div><div class="cases__list">';
    for ($i = 0; $i < 4; $i++) {
        $html .= '<article class="cases-card"><div class="cases-card__content"><div class="cases-card__text"><h3 class="cases-card__title h3">Название</h3><p class="cases-card__description">Описание</p></div></div><img class="cases-card__image" src="' . h(asset_url('images/Cases/case-preview.png')) . '" alt="" width="910" height="380" loading="lazy"></article>';
    }
    return $html . '</div></section>';
}

function render_production_showcase(): string
{
    $items = [
        ['title' => '1500+ объектов', 'description' => 'Описание', 'icon' => 'woods'],
        ['title' => 'Работаем с 2009 года', 'description' => 'Описание', 'icon' => 'medal'],
        ['title' => 'Индивидуальный подход', 'description' => 'Описание', 'icon' => 'person_with_star'],
        ['title' => 'Профессиональное оборудование', 'description' => 'Описание', 'icon' => 'machine'],
        ['title' => 'Строгий входной контроль качества материалов', 'description' => 'Описание', 'icon' => 'loop'],
    ];
    $html = '<section class="production-showcase" aria-labelledby="production-showcase-title"><div class="production-showcase__inner container"><div class="production-showcase__header"><h2 class="production-showcase__title h2" id="production-showcase-title">Создаем красивые, стильные и долговечные изделия</h2><p class="production-showcase__description">Собственное производство изделий в Санкт-Петербурге с минимальными сроками изготовления от 3 дней</p></div><div class="production-showcase__body"><div class="production-showcase__features">';
    foreach ($items as $item) {
        $html .= '<article class="production-showcase-feature"><img class="production-showcase-feature__icon" src="' . h(asset_url('images/CardIcons/' . $item['icon'] . '.png')) . '" alt="" width="64" height="64" loading="lazy"><div class="production-showcase-feature__content"><h3 class="production-showcase-feature__title">' . h($item['title']) . '</h3><p class="production-showcase-feature__description">' . h($item['description']) . '</p></div></article>';
    }
    return $html . '</div><img class="production-showcase__image" src="' . h(asset_url('images/AboutProduction/production-photo.png')) . '" alt="" width="910" height="662" loading="lazy"></div></div></section>';
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

    render_layout($site, 'О компании', $body);
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
            $html .= '<img class="reviews-card__avatar" src="' . h($review['avatar']) . '" alt="" loading="lazy">';
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
            $html .= '<img class="reviews-popup__avatar" src="' . h($review['avatar']) . '" alt="" loading="lazy">';
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
            $body .= '<img class="reviews-card__avatar" src="' . h($review['avatar']) . '" alt="" loading="lazy">';
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
            $body .= '<img class="reviews-popup__avatar" src="' . h($review['avatar']) . '" alt="" loading="lazy">';
        } else {
            $body .= '<span class="reviews-popup__avatar" aria-hidden="true"></span>';
        }
        $body .= '<div class="reviews-popup__meta"><h3 class="reviews-popup__title h3" id="' . h($titleId) . '">' . h($review['author'] ?? '') . '</h3><p class="reviews-popup__date">' . h($review['date'] ?? '') . '</p></div></div>';
        $body .= '<p class="reviews-popup__text">' . h($review['text'] ?? '') . '</p></article></div>';
    }
    $body .= '</section>';
    $body .= render_questions($settings);
    $body .= render_route($settings);

    render_layout($site, 'Отзывы', $body);
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

    render_layout($site, 'Контакты', $body);
}

function render_work_features(): string
{
    $items = [
        ['title' => 'Древесина экстра-класса и 1 сорта', 'description' => 'На наших изделиях вы не найдете сколы, трещины, сучки и другие дефекты', 'icon' => 'star'],
        ['title' => 'Фиксированная стоимость', 'description' => 'Описание', 'icon' => 'rubles', 'class' => ' work-features-card--last-mobile'],
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
        . '<img class="modular-house-promo__image" src="' . h(asset_url('images/Promo/modular-house.png')) . '" alt="" width="1840" height="752" loading="lazy">'
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
    $html .= '</ul><div class="questions__manager"><img class="questions__manager-photo" src="' . h(asset_url('images/Questions/manager-photo.png')) . '" alt="" width="80" height="80" loading="lazy"><div class="questions__manager-info"><p class="questions__manager-name">Силинский Максим</p><p class="questions__manager-position">Менеджер продаж</p></div></div></div>';
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
    $html .= '<form class="service-hero__form" action="/lead" method="post"><input type="hidden" name="source" value="' . h($page['slug'] ?? 'service') . '"><div class="service-hero__fields">';
    $html .= '<input class="service-hero__control" name="name" placeholder="Григорий" aria-label="Ваше имя" required>';
    $html .= '<input class="service-hero__control" name="phone" placeholder="' . h($settings['phone'] ?? '') . '" aria-label="Телефон" inputmode="tel" required>';
    $html .= '<textarea class="service-hero__control service-hero__control--textarea" name="comment" placeholder="Комментарий" aria-label="Комментарий"></textarea>';
    $html .= '<button class="button service-hero__button" type="submit">Записаться на замер</button></div><p class="service-hero__privacy">Нажимая на кнопку, вы соглашаетесь с политикой конфиденциальности.</p></form></div>';

    return $html . '<img class="service-hero__image" src="' . h($cover) . '" alt="" width="730" height="730" loading="eager"></div></section>';
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
        $html .= '</ul></div><a class="button service-plans-card__button" href="#request">Записаться на замер</a></div></li>';
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

function render_service_request(array $settings, string $source): string
{
    $html = '<section class="service-request container" id="request" aria-labelledby="service-request-title"><div class="service-request__inner"><div class="service-request__content"><div class="service-request__offer">';
    $html .= '<h2 class="service-request__title h2" id="service-request-title">Наличники на весь дом внутренние и наружные</h2><ul class="service-request__benefits"><li>Оперативный выезд на замер</li><li>Скидка 10%</li></ul></div>';
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
    $html = '<section class="service-cases" aria-labelledby="service-cases-title"><div class="service-cases__inner container"><div class="service-cases__header"><h2 class="service-cases__title h2" id="service-cases-title">Уже реализованные проекты</h2></div><div class="service-cases__list">';
    for ($i = 0; $i < 4; $i++) {
        $html .= '<article class="service-cases-card"><div class="service-cases-card__content"><div class="service-cases-card__text"><h3 class="service-cases-card__title h3">Название</h3><p class="service-cases-card__description">Описание</p></div></div><img class="service-cases-card__image" src="' . h(asset_url('images/Cases/case-preview.png')) . '" alt="" width="910" height="380" loading="lazy"></article>';
    }

    return $html . '</div></div></section>';
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
    $html .= '<form class="interior-hero__form" action="/lead" method="post"><input type="hidden" name="source" value="' . h($page['slug'] ?? 'interior') . '"><div class="interior-hero__fields">';
    $html .= '<input class="interior-hero__control" name="name" placeholder="Григорий" aria-label="Ваше имя" required>';
    $html .= '<input class="interior-hero__control" name="phone" placeholder="' . h($settings['phone'] ?? '') . '" aria-label="Телефон" inputmode="tel" required>';
    $html .= '<button class="button interior-hero__button" type="submit">Записаться на замер</button></div><p class="interior-hero__privacy">Нажимая на кнопку, вы соглашаетесь с политикой конфиденциальности.</p></form></div>';

    return $html . '<img class="interior-hero__image" src="' . h($image) . '" alt="" width="730" height="730" loading="eager"></div></section>';
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
    $html .= '<form class="interior-proposal-request__form" action="/lead" method="post"><input type="hidden" name="source" value="' . h($source) . '">';
    $html .= '<input class="interior-proposal-request__control" name="name" placeholder="Григорий" aria-label="Ваше имя" required>';
    $html .= '<input class="interior-proposal-request__control" name="phone" placeholder="' . h($settings['phone'] ?? '') . '" aria-label="Телефон" inputmode="tel" required>';
    $html .= '<textarea class="interior-proposal-request__control interior-proposal-request__control--textarea" name="comment" placeholder="Комментарий" aria-label="Комментарий"></textarea>';
    $html .= '<button class="button interior-proposal-request__button" type="submit">Записаться на замер</button><p class="interior-proposal-request__privacy">Нажимая на кнопку, вы соглашаетесь с политикой конфиденциальности.</p></form></div></section>';

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
        $html .= '</ul></div><a class="button interior-plans-card__button" href="#request">Записаться</a></div></article>';
    }

    return $html . '</div></section>';
}

function render_interior_questions(array $settings): string
{
    $html = '<section class="interior-questions container" aria-labelledby="interior-questions-title"><div class="interior-questions__inner"><div class="interior-questions__content"><div class="interior-questions__header"><h2 class="interior-questions__title h2" id="interior-questions-title">Остались вопросы?</h2><p class="interior-questions__description">Оставьте заявку или свяжитесь с нами удобным способом</p></div><ul class="interior-questions__socials" aria-label="Способы связи">';
    foreach (social_links($settings, true) as $social) {
        $html .= '<li class="interior-questions__social-item"><a class="interior-questions__social-link" href="' . h($social['href']) . '" aria-label="' . h($social['label']) . '">' . icon_html($social['name'], 'icon interior-questions__social-icon', true) . '</a></li>';
    }
    $html .= '</ul><div class="interior-questions__manager"><img class="interior-questions__manager-photo" src="' . h(asset_url('images/Questions/manager-photo.png')) . '" alt="" width="80" height="80" loading="lazy"><div class="interior-questions__manager-info"><p class="interior-questions__manager-name">Григорий Карпинский</p><p class="interior-questions__manager-position">Менеджер продаж</p></div></div></div>';
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

    if ($type === 'document') {
        $body = render_document_page($page, $contentData);
        render_layout($site, $page['seoTitle'] ?? $page['title'] ?? SITE_NAME, $body);
        return;
    }

    if ($type === 'product') {
        $settings = $site['settings'] ?? [];
        $body = render_service_hero($page, $contentData, $settings);
        $body .= render_service_includes($contentData['includes'] ?? null);
        $body .= render_service_materials($contentData['materials'] ?? null);
        $body .= render_service_icon_cards($contentData['colors'] ?? null, 'service-colors', ['tree', 'target', 'lines', 'color', 'shield', 'woods'], 'Цветовые решения');
        $body .= render_service_request($settings, $page['slug'] ?? 'service');
        $body .= render_service_icon_cards($contentData['benefits'] ?? null, 'service-benefits', ['shield_1', 'star', 'medal', 'person'], 'Преимущества');
        $body .= render_service_plans($contentData['plans'] ?? null);
        $body .= render_home_request($settings);
        $body .= render_service_options();
        $body .= render_service_cases();
        $body .= render_service_process();
        $body .= render_reviews_section($site['reviews'] ?? []);
        $body .= render_service_faq($contentData['faq']['items'] ?? []);
        $body .= render_questions($settings);
        $body .= render_route($settings);

        render_layout($site, $page['seoTitle'] ?? $page['title'] ?? SITE_NAME, $body);
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

        render_layout($site, $page['seoTitle'] ?? $page['title'] ?? SITE_NAME, $body);
        return;
    }

    $hero = $contentData['hero'] ?? [];
    $isInterior = $type === 'interior';
    $heroClass = $isInterior ? 'interior-hero' : 'service-hero';
    $body = '<section class="' . h($heroClass) . ' container"><div class="' . h($heroClass) . '__content"><p class="' . h($heroClass) . '__subtitle">' . h($hero['subtitle'] ?? '') . '</p>';
    $body .= '<h1 class="' . h($heroClass) . '__title h1">' . h($hero['title'] ?? $page['title'] ?? '') . ' <span>' . h($hero['accent'] ?? '') . '</span></h1></div>';
    if (!empty($page['cover'])) {
        $body .= '<img class="' . h($heroClass) . '__image" src="' . h($page['cover']) . '" alt="" loading="lazy">';
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

    render_layout($site, $page['seoTitle'] ?? $page['title'] ?? SITE_NAME, $body);
}

function render_not_found(array $site): void
{
    render_layout($site, 'Страница не найдена', '<section class="not-found-hero container"><h1 class="not-found-hero__title h1">Страница не найдена</h1><a class="button" href="/">На главную</a></section>');
}

function handle_lead_submit(): void
{
    $leads = leads_read();
    $leads[] = [
        'id' => create_id('lead'),
        'createdAt' => date(DATE_ATOM),
        'status' => 'new',
        'name' => trim($_POST['name'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'comment' => trim($_POST['comment'] ?? ''),
        'source' => trim($_POST['source'] ?? 'site'),
    ];
    leads_write($leads);
    header('Location: /?sent=1');
}

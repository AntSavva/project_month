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
    echo '<link rel="stylesheet" href="/_next/static/css/502c9b70610a5c6c.css">';
    echo '<link rel="stylesheet" href="/assets/css/site.css">';
    echo '</head><body>';
    render_header($products, $interiors, $phone, $email);
    echo '<main class="content">' . $content . '</main>';
    render_footer($settings, $products, $interiors, $documents);
    echo '</body></html>';
}

function render_header(array $products, array $interiors, string $phone, string $email): void
{
    echo '<header class="header"><div class="header__inner">';
    echo '<a class="logo header__logo" href="/" title="Home" aria-label="Home"><img class="logo__image" src="/images/logo.svg" alt="" width="216" height="40" loading="eager"></a>';
    echo '<nav class="header__nav" aria-label="Основная навигация"><ul class="header__nav-list">';
    render_header_dropdown('Продукция', $products, asset_url('images/AboutProduction/production-photo.png'));
    render_header_dropdown('Отделка интерьера', $interiors, asset_url('images/Cases/case-preview.png'));
    echo '<li class="header__nav-item"><a class="header__nav-link" href="/about/"><span>О компании</span></a></li>';
    echo '<li class="header__nav-item"><a class="header__nav-link" href="/reviews/"><span>Отзывы</span></a></li>';
    echo '<li class="header__nav-item"><a class="header__nav-link" href="/contacts/"><span>Контакты</span></a></li>';
    echo '</ul></nav>';
    echo '<div class="header__actions"><address class="header__contacts">';
    echo '<a class="header__contact-link" href="' . h(phone_href($phone)) . '">' . icon_html('phone', 'icon header__contact-icon', true) . '<span>' . h($phone) . '</span></a>';
    echo '<a class="header__contact-link" href="' . h(email_href($email)) . '">' . icon_html('mail', 'icon header__contact-icon', true) . '<span>' . h($email) . '</span></a>';
    echo '</address><a class="button header__callback" href="#request">Записаться на замер</a>';
    echo '<button class="burger-button header__burger visible-tablet" type="button" aria-label="Открыть меню"><svg class="burger-button__svg" width="44" height="44" viewBox="0 0 100 100"><path class="burger-button__line burger-button__line--1" d="M 20,29 H 80"/><path class="burger-button__line burger-button__line--2" d="M 20,50 H 80"/><path class="burger-button__line burger-button__line--3" d="M 20,71 H 80"/></svg></button>';
    echo '</div></div></header>';
}

function render_header_dropdown(string $label, array $pages, string $cover): void
{
    if (!$pages) {
        return;
    }

    echo '<li class="header__nav-item header__nav-item--dropdown">';
    echo '<button class="header__nav-link" type="button"><span>' . h($label) . '</span><svg class="header__nav-arrow" width="8" height="6" viewBox="0 0 8 6" aria-hidden="true"><path d="M4 6L0.535898 0H7.4641L4 6Z" /></svg></button>';
    echo '<div class="header__mega-menu"><div class="header__mega-menu-inner header__mega-menu-inner--compact"><ul class="header__mega-list">';
    foreach ($pages as $page) {
        echo '<li class="header__mega-item"><a class="header__mega-link" href="' . h(page_href($page)) . '">' . h($page['title'] ?? '') . '</a></li>';
    }
    echo '</ul><img class="header__mega-cover" src="' . h($cover) . '" alt="" width="730" height="360"></div></div></li>';
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
        $html .= '<a class="interior-solutions-card" href="' . h(page_href($page)) . '">';
        if (!empty($page['cover'])) {
            $html .= '<span class="interior-solutions-card__media"><img class="interior-solutions-card__image" src="' . h($page['cover']) . '" alt="" width="540" height="304" loading="lazy"></span>';
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
    $content .= render_standards();
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

function render_standards(): string
{
    $items = [
        ['title' => 'Продукция мебельного производства. Термины и определения', 'code' => 'ГОСТ 20400-80'],
        ['title' => 'Мебель. Общие технические условия', 'code' => 'ГОСТ 16371-2014'],
        ['title' => 'Мебель для сидения и лежания. Общие технические условия', 'code' => 'ГОСТ 19917-2014'],
        ['title' => 'Межкомнатные двери. Общие технические условия', 'code' => 'ГОСТ 30211-94'],
    ];
    $html = '<section class="standards container" aria-labelledby="standards-title"><details class="standards__details" open><summary class="standards__header"><h2 class="standards__title h2" id="standards-title">Основные нормативные документы</h2><span class="standards__summary"><span>Подробнее</span><span class="standards__summary-icon" aria-hidden="true"></span></span></summary><div class="standards__list">';
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

function render_page(array $site, array $page): void
{
    $type = $page['type'] ?? 'product';
    $contentData = $page['content'] ?? [];

    if ($type === 'document') {
        $body = '<section class="document-content container"><h1 class="document-content__title h1">' . h($contentData['h1'] ?? $page['title'] ?? '') . '</h1>';
        $body .= '<div class="document-content__body">' . nl2br(h($contentData['text'] ?? '')) . '</div></section>';
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

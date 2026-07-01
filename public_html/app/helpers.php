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

    return (function_exists('mb_substr') ? mb_substr($value, 0, $length - 1) : substr($value, 0, $length - 1)) . '…';
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

function render_layout(array $site, string $title, string $content): void
{
    $settings = $site['settings'] ?? [];
    $products = site_pages($site, 'product', true);
    $interiors = site_pages($site, 'interior', true);
    $phone = $settings['phone'] ?? '';
    $email = $settings['email'] ?? '';

    echo '<!doctype html><html lang="ru"><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . h($title) . '</title>';
    echo '<link rel="stylesheet" href="/assets/css/site.css">';
    echo '</head><body>';
    echo '<header class="site-header"><a class="logo" href="/">Кубэра</a><nav>';
    echo '<a href="/">Главная</a><a href="/reviews">Отзывы</a><a href="/contacts">Контакты</a><a href="/admin">Админка</a>';
    echo '</nav><a class="header-phone" href="tel:' . h($phone) . '">' . h($phone) . '</a></header>';
    echo $content;
    echo '<footer class="site-footer"><div><strong>Кубэра</strong><p>' . h($settings['address'] ?? '') . '</p></div>';
    echo '<div><a href="mailto:' . h($email) . '">' . h($email) . '</a><p>' . h($settings['workingHours'] ?? '') . '</p></div>';
    echo '<div class="footer-links">';
    foreach (site_pages($site, 'document', true) as $page) {
        echo '<a href="' . h(page_href($page)) . '">' . h($page['title'] ?? '') . '</a>';
    }
    echo '</div></footer>';
    echo '</body></html>';
}

function render_cards(array $pages, string $class): string
{
    $html = '<div class="cards ' . h($class) . '">';

    foreach ($pages as $page) {
        $cover = $page['cover'] ?? '';
        $description = $page['menuDescription'] ?? ($page['seoDescription'] ?? '');
        $html .= '<a class="card" href="' . h(page_href($page)) . '">';
        $html .= '<span class="card-media">';
        if ($cover) {
            $html .= '<img src="' . h($cover) . '" alt="">';
        }
        $html .= '</span>';
        $html .= '<span class="card-title">' . h($page['title'] ?? '') . '</span>';
        $html .= '<span class="card-text">' . h($description) . '</span>';
        $html .= '<span class="card-link">Подробнее ↗</span>';
        $html .= '</a>';
    }

    return $html . '</div>';
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
    return '<section class="section request"><h2>Оставить заявку</h2><form method="post" action="/lead" class="lead-form">'
        . '<input type="hidden" name="source" value="' . h($source) . '">'
        . '<label>Имя<input name="name" required></label>'
        . '<label>Телефон<input name="phone" required></label>'
        . '<label>Комментарий<textarea name="comment" rows="4"></textarea></label>'
        . '<button type="submit">Отправить</button></form></section>';
}

function render_home(array $site): void
{
    $products = site_pages($site, 'product', true);
    $interiors = site_pages($site, 'interior', true);
    $reviews = $site['reviews'] ?? [];
    $settings = $site['settings'] ?? [];

    $content = '<main><section class="hero"><div><p class="eyebrow">Столярное производство</p>';
    $content .= '<h1>Изделия и отделка из дерева для вашего дома и бизнеса</h1>';
    $content .= '<p>Проектируем, производим и монтируем деревянные решения под размер.</p>';
    $content .= '<a class="button" href="#request">Оставить заявку</a></div></section>';
    $content .= '<section class="section"><h2>Что мы производим</h2>' . render_cards($products, 'products') . '</section>';
    $content .= '<section class="section section-dark"><h2>Решения для отделки интерьеров</h2>' . render_cards($interiors, 'interiors') . '</section>';

    if ($reviews) {
        $content .= '<section class="section"><h2>Отзывы клиентов</h2><div class="reviews">';
        foreach (array_slice($reviews, 0, 6) as $review) {
            if (($review['status'] ?? 'published') !== 'published') {
                continue;
            }
            $content .= '<article class="review"><strong>' . h($review['author'] ?? '') . '</strong>';
            $content .= '<p>' . h(text_excerpt($review['text'] ?? '', 220)) . '</p></article>';
        }
        $content .= '</div></section>';
    }

    $content .= '<section class="section contacts"><h2>Контакты</h2><p>' . h($settings['address'] ?? '') . '</p>';
    $content .= '<p><a href="tel:' . h($settings['phone'] ?? '') . '">' . h($settings['phone'] ?? '') . '</a></p></section>';
    $content .= '<div id="request">' . render_request_form('home') . '</div></main>';

    render_layout($site, 'Кубэра', $content);
}

function render_page(array $site, array $page): void
{
    $type = $page['type'] ?? 'product';
    $contentData = $page['content'] ?? [];

    if ($type === 'document') {
        $body = '<main class="document"><section class="section"><h1>' . h($contentData['h1'] ?? $page['title'] ?? '') . '</h1>';
        $body .= '<div class="document-text">' . nl2br(h($contentData['text'] ?? '')) . '</div></section></main>';
        render_layout($site, $page['seoTitle'] ?? $page['title'] ?? SITE_NAME, $body);
        return;
    }

    $hero = $contentData['hero'] ?? [];
    $body = '<main><section class="hero hero-inner"><p class="eyebrow">' . h($hero['subtitle'] ?? '') . '</p>';
    $body .= '<h1>' . h($hero['title'] ?? $page['title'] ?? '') . ' <span>' . h($hero['accent'] ?? '') . '</span></h1>';
    if (!empty($page['cover'])) {
        $body .= '<img class="hero-cover" src="' . h($page['cover']) . '" alt="">';
    }
    $body .= '</section>';

    $blocks = [
        'includes' => 'Что входит в услугу',
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
        $items = $block['items'] ?? [];
        $body .= '<section class="section"><h2>' . h($block['title'] ?? $fallbackTitle) . '</h2>';
        if (!empty($block['description'])) {
            $body .= '<p class="section-lead">' . h($block['description']) . '</p>';
        }
        $body .= render_items(is_array($items) ? $items : []);
        $body .= '</section>';
    }

    $body .= '<section class="section"><h2>Частые вопросы</h2>' . render_faq($contentData['faq']['items'] ?? []) . '</section>';
    $body .= render_request_form($page['slug'] ?? 'page') . '</main>';

    render_layout($site, $page['seoTitle'] ?? $page['title'] ?? SITE_NAME, $body);
}

function render_not_found(array $site): void
{
    render_layout($site, 'Страница не найдена', '<main><section class="section"><h1>Страница не найдена</h1><a class="button" href="/">На главную</a></section></main>');
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

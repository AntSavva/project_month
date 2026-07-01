<?php

$message = '';
$error = '';

function render_admin_layout(string $title, string $content): void
{
    echo '<!doctype html><html lang="ru"><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . h($title) . '</title>';
    echo '<link rel="stylesheet" href="/_next/static/css/502c9b70610a5c6c.css">';
    echo '<link rel="stylesheet" href="/assets/css/site.css">';
    echo '</head><body>' . $content . '</body></html>';
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'login') {
            if (admin_login($_POST['password'] ?? '')) {
                header('Location: /admin');
                exit;
            }

            $error = 'Неверный пароль.';
        } elseif ($action === 'logout') {
            admin_logout();
            header('Location: /admin');
            exit;
        } elseif (!admin_is_authenticated()) {
            $error = 'Авторизуйтесь заново.';
        } elseif ($action === 'save_settings') {
            $site['settings'] = [
                'phone' => trim($_POST['phone'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'address' => trim($_POST['address'] ?? ''),
                'workingHours' => trim($_POST['workingHours'] ?? ''),
                'legalInfo' => trim($_POST['legalInfo'] ?? ''),
                'socials' => [
                    'vk' => trim($_POST['vk'] ?? ''),
                    'telegram' => trim($_POST['telegram'] ?? ''),
                    'youtube' => trim($_POST['youtube'] ?? ''),
                    'max' => trim($_POST['max'] ?? ''),
                ],
            ];
            site_write($site);
            $message = 'Настройки сохранены.';
        } elseif ($action === 'create_page') {
            $type = in_array($_POST['type'] ?? '', ['product', 'interior', 'document'], true)
                ? $_POST['type']
                : 'product';
            $cover = !empty($_FILES['cover']) ? upload_image($_FILES['cover']) : '';
            $site['pages'][] = [
                'id' => create_id($type),
                'type' => $type,
                'title' => trim($_POST['title'] ?? 'Новая страница'),
                'slug' => normalize_slug($_POST['slug'] ?? ''),
                'menuDescription' => trim($_POST['menuDescription'] ?? ''),
                'seoTitle' => trim($_POST['seoTitle'] ?? ''),
                'seoDescription' => trim($_POST['seoDescription'] ?? ''),
                'status' => $_POST['status'] === 'published' ? 'published' : 'draft',
                'cover' => $cover,
                'content' => default_content($type),
                'updatedAt' => date(DATE_ATOM),
            ];
            site_write($site);
            $message = 'Страница создана.';
        } elseif ($action === 'save_page') {
            $index = site_find_page_index($site, $_POST['id'] ?? '');

            if ($index === null) {
                throw new RuntimeException('Страница не найдена.');
            }

            $content = json_decode($_POST['content'] ?? '{}', true);

            if (!is_array($content)) {
                throw new RuntimeException('Контент должен быть корректным JSON.');
            }

            $page = $site['pages'][$index];
            $cover = $page['cover'] ?? '';

            if (!empty($_FILES['cover']) && ($_FILES['cover']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $cover = upload_image($_FILES['cover']);
            }

            $site['pages'][$index] = array_merge($page, [
                'title' => trim($_POST['title'] ?? ''),
                'slug' => normalize_slug($_POST['slug'] ?? ''),
                'menuDescription' => trim($_POST['menuDescription'] ?? ''),
                'seoTitle' => trim($_POST['seoTitle'] ?? ''),
                'seoDescription' => trim($_POST['seoDescription'] ?? ''),
                'status' => $_POST['status'] === 'published' ? 'published' : 'draft',
                'cover' => $cover,
                'content' => $content,
                'updatedAt' => date(DATE_ATOM),
            ]);
            site_write($site);
            $message = 'Страница сохранена.';
        } elseif ($action === 'delete_page') {
            $id = $_POST['id'] ?? '';
            $site['pages'] = array_values(array_filter($site['pages'] ?? [], function ($page) use ($id) {
                return ($page['id'] ?? '') !== $id;
            }));
            site_write($site);
            $message = 'Страница удалена.';
        } elseif ($action === 'save_reviews') {
            $reviews = json_decode($_POST['reviews'] ?? '[]', true);

            if (!is_array($reviews)) {
                throw new RuntimeException('Отзывы должны быть корректным JSON.');
            }

            $site['reviews'] = $reviews;
            site_write($site);
            $message = 'Отзывы сохранены.';
        } elseif ($action === 'save_lead_status') {
            $leads = leads_read();
            foreach ($leads as &$lead) {
                if (($lead['id'] ?? '') === ($_POST['id'] ?? '')) {
                    $lead['status'] = $_POST['status'] ?? 'new';
                    $lead['updatedAt'] = date(DATE_ATOM);
                }
            }
            unset($lead);
            leads_write($leads);
            $message = 'Статус заявки обновлен.';
        }
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

if (!admin_is_authenticated()) {
    $content = '<main class="admin-page admin-page--login"><form method="post" class="admin-login">'
        . '<input type="hidden" name="action" value="login">'
        . '<h1 class="admin-login__title">Админ-панель</h1>'
        . '<p class="admin-login__text">Введите пароль для управления сайтом.</p>'
        . ($error ? '<p class="admin-message">' . h($error) . '</p>' : '')
        . '<input class="admin-input" type="password" name="password" placeholder="Пароль" required autofocus>'
        . '<button class="admin-button" type="submit">Войти</button></form></main>';
    render_admin_layout('Админ-панель', $content);
    return;
}

$settings = $site['settings'] ?? [];
$leads = array_reverse(leads_read());
$adminSections = [
    'leads' => 'Заявки',
    'reviews' => 'Отзывы',
    'settings' => 'Контакты и реквизиты',
    'create-page' => 'Создать страницу',
    'pages' => 'Страницы',
];
$currentSection = $_GET['section'] ?? 'leads';

if (!isset($adminSections[$currentSection])) {
    $currentSection = 'leads';
}

$content = '<main class="admin-page"><header class="admin-header"><div><p class="admin-header__eyebrow">Кубэра</p><h1 class="admin-header__title">Админ-панель сайта</h1></div>'
    . '<form method="post"><input type="hidden" name="action" value="logout"><button class="admin-button admin-button--ghost" type="submit">Выйти</button></form></header>'
    . '<nav class="admin-tabs" aria-label="Разделы админки">';

foreach ($adminSections as $sectionKey => $sectionTitle) {
    $activeClass = $sectionKey === $currentSection ? ' is-active' : '';
    $content .= '<a class="admin-tabs__button' . $activeClass . '" href="/admin?section=' . h($sectionKey) . '">' . h($sectionTitle) . '</a>';
}

$content .= '</nav>';

if ($message) {
    $content .= '<p class="admin-message admin-message--floating">' . h($message) . '</p>';
}
if ($error) {
    $content .= '<p class="admin-message admin-message--floating">' . h($error) . '</p>';
}

if ($currentSection === 'settings') {
    $content .= '<section id="settings" class="panel admin-section"><h2 class="admin-section__title">Настройки сайта</h2><form method="post" class="admin-grid">'
    . '<input type="hidden" name="action" value="save_settings">'
    . '<label>Телефон<input name="phone" value="' . h($settings['phone'] ?? '') . '"></label>'
    . '<label>Email<input name="email" value="' . h($settings['email'] ?? '') . '"></label>'
    . '<label>Адрес<input name="address" value="' . h($settings['address'] ?? '') . '"></label>'
    . '<label>Время работы<input name="workingHours" value="' . h($settings['workingHours'] ?? '') . '"></label>'
    . '<label>VK<input name="vk" value="' . h($settings['socials']['vk'] ?? '') . '"></label>'
    . '<label>Telegram<input name="telegram" value="' . h($settings['socials']['telegram'] ?? '') . '"></label>'
    . '<label>YouTube<input name="youtube" value="' . h($settings['socials']['youtube'] ?? '') . '"></label>'
    . '<label>Max<input name="max" value="' . h($settings['socials']['max'] ?? '') . '"></label>'
    . '<label class="wide">Юридическая информация<textarea name="legalInfo" rows="4">' . h($settings['legalInfo'] ?? '') . '</textarea></label>'
    . '<button type="submit">Сохранить настройки</button></form></section>';
}

if ($currentSection === 'create-page') {
    $content .= '<section id="create-page" class="panel admin-section"><h2 class="admin-section__title">Создать страницу</h2><form method="post" enctype="multipart/form-data" class="admin-grid">'
    . '<input type="hidden" name="action" value="create_page">'
    . '<label>Тип<select name="type"><option value="product">Продукция</option><option value="interior">Интерьер</option><option value="document">Документ</option></select></label>'
    . '<label>Название<input name="title" required></label>'
    . '<label>Slug<input name="slug" required></label>'
    . '<label>Статус<select name="status"><option value="draft">Черновик</option><option value="published">Опубликовано</option></select></label>'
    . '<label>Описание в меню<input name="menuDescription"></label>'
    . '<label>SEO title<input name="seoTitle"></label>'
    . '<label class="wide">SEO description<textarea name="seoDescription" rows="3"></textarea></label>'
    . '<label>Обложка<input type="file" name="cover" accept="image/*"></label>'
    . '<button type="submit">Создать</button></form></section>';
}

if ($currentSection === 'pages') {
    $content .= '<section id="pages" class="panel admin-section"><h2 class="admin-section__title">Страницы</h2><div class="admin-pages">';
    foreach ($site['pages'] ?? [] as $page) {
        $contentJson = json_encode($page['content'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $content .= '<details class="admin-edit-page"><summary><strong>' . h($page['title'] ?? '') . '</strong> <span>' . h($page['type'] ?? '') . ' / ' . h($page['slug'] ?? '') . ' / ' . h($page['status'] ?? '') . '</span></summary>';
        $content .= '<form method="post" enctype="multipart/form-data" class="admin-grid">'
            . '<input type="hidden" name="action" value="save_page">'
            . '<input type="hidden" name="id" value="' . h($page['id'] ?? '') . '">'
            . '<label>Название<input name="title" value="' . h($page['title'] ?? '') . '"></label>'
            . '<label>Slug<input name="slug" value="' . h($page['slug'] ?? '') . '"></label>'
            . '<label>Статус<select name="status"><option value="draft"' . (($page['status'] ?? '') === 'draft' ? ' selected' : '') . '>Черновик</option><option value="published"' . (($page['status'] ?? '') === 'published' ? ' selected' : '') . '>Опубликовано</option></select></label>'
            . '<label>Описание<input name="menuDescription" value="' . h($page['menuDescription'] ?? '') . '"></label>'
            . '<label>SEO title<input name="seoTitle" value="' . h($page['seoTitle'] ?? '') . '"></label>'
            . '<label>Новая обложка<input type="file" name="cover" accept="image/*"></label>'
            . '<label class="wide">SEO description<textarea name="seoDescription" rows="3">' . h($page['seoDescription'] ?? '') . '</textarea></label>'
            . '<label class="wide">Контент JSON<textarea name="content" rows="16" class="code">' . h($contentJson) . '</textarea></label>'
            . '<button type="submit">Сохранить страницу</button></form>';
        $content .= '<form method="post" onsubmit="return confirm(\'Удалить страницу?\')" class="delete-form">'
            . '<input type="hidden" name="action" value="delete_page"><input type="hidden" name="id" value="' . h($page['id'] ?? '') . '">'
            . '<button type="submit" class="danger">Удалить</button></form></details>';
    }
    $content .= '</div></section>';
}

if ($currentSection === 'reviews') {
    $content .= '<section id="reviews" class="panel admin-section"><h2 class="admin-section__title">Отзывы</h2><form method="post">'
    . '<input type="hidden" name="action" value="save_reviews">'
    . '<textarea name="reviews" rows="18" class="code">' . h(json_encode($site['reviews'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) . '</textarea>'
    . '<button type="submit">Сохранить отзывы</button></form></section>';
}

if ($currentSection === 'leads') {
    $content .= '<section id="leads" class="panel admin-section"><h2 class="admin-section__title">Заявки</h2><div class="lead-list">';
    if (!$leads) {
        $content .= '<p>Заявок пока нет.</p>';
    }
    foreach ($leads as $lead) {
        $content .= '<article class="lead"><div><strong>' . h($lead['name'] ?? 'Без имени') . '</strong><p>' . h($lead['phone'] ?? '') . '</p><p>' . h($lead['comment'] ?? '') . '</p><small>' . h($lead['createdAt'] ?? '') . '</small></div>'
            . '<form method="post"><input type="hidden" name="action" value="save_lead_status"><input type="hidden" name="id" value="' . h($lead['id'] ?? '') . '">'
            . '<select name="status"><option value="new"' . (($lead['status'] ?? '') === 'new' ? ' selected' : '') . '>Новая</option><option value="done"' . (($lead['status'] ?? '') === 'done' ? ' selected' : '') . '>Обработана</option></select>'
            . '<button type="submit">OK</button></form></article>';
    }
    $content .= '</div></section>';
}

$content .= '</main>';

render_admin_layout('Админ-панель', $content);

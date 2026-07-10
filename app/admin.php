<?php

$message = '';
$error = '';

function render_admin_layout(string $title, string $content): void
{
    echo '<!doctype html><html lang="ru"><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<meta name="robots" content="noindex, nofollow">';
    echo '<title>' . h($title) . '</title>';
    echo '<link rel="stylesheet" href="/assets/css/admin.css">';
    echo '<link rel="stylesheet" href="/assets/css/site.css">';
    echo '</head><body>' . $content . '</body></html>';
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $action = post_text('action', 40);

    if (!csrf_verify((string) ($_POST['csrf_token'] ?? ''))) {
        $error = 'Сессия устарела. Обновите страницу и повторите действие.';
    } else {
        try {
            if ($action === 'login') {
                if (admin_login(post_text('username', 80), (string) ($_POST['password'] ?? ''))) {
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
                'phone' => post_text('phone', 80),
                'email' => post_email('email'),
                'address' => post_text('address', 250),
                'workingHours' => post_text('workingHours', 120),
                'legalInfo' => post_text('legalInfo', 1000),
                'socials' => [
                    'vk' => post_text('vk', 250),
                    'telegram' => post_text('telegram', 250),
                    'youtube' => post_text('youtube', 250),
                    'max' => post_text('max', 250),
                ],
            ];
            site_write($site);
            $message = 'Настройки сохранены.';
        } elseif ($action === 'create_page') {
            $type = post_choice('type', ['product', 'interior', 'document'], 'product');
            $cover = !empty($_FILES['cover']) ? upload_image($_FILES['cover']) : '';
            $site['pages'][] = [
                'id' => create_id($type),
                'type' => $type,
                'title' => post_text('title', 150, 'Новая страница'),
                'slug' => normalize_slug(post_text('slug', 150)),
                'menuDescription' => post_text('menuDescription', 250),
                'seoTitle' => post_text('seoTitle', 180),
                'seoDescription' => post_text('seoDescription', 320),
                'status' => post_choice('status', ['published', 'draft'], 'draft'),
                'cover' => $cover,
                'content' => default_content($type),
                'updatedAt' => date(DATE_ATOM),
            ];
            site_write($site);
            $message = 'Страница создана.';
        } elseif ($action === 'save_page') {
            $index = site_find_page_index($site, post_text('id', 80));

            if ($index === null) {
                throw new RuntimeException('Страница не найдена.');
            }

            $contentJson = (string) ($_POST['content'] ?? '{}');

            if (strlen($contentJson) > 200000) {
                throw new RuntimeException('Контент слишком большой.');
            }

            $content = json_decode($contentJson, true);

            if (!is_array($content)) {
                throw new RuntimeException('Контент должен быть корректным JSON.');
            }

            $page = $site['pages'][$index];
            $cover = $page['cover'] ?? '';

            if (!empty($_FILES['cover']) && ($_FILES['cover']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $cover = upload_image($_FILES['cover']);
            }

            $site['pages'][$index] = array_merge($page, [
                'title' => post_text('title', 150),
                'slug' => normalize_slug(post_text('slug', 150)),
                'menuDescription' => post_text('menuDescription', 250),
                'seoTitle' => post_text('seoTitle', 180),
                'seoDescription' => post_text('seoDescription', 320),
                'status' => post_choice('status', ['published', 'draft'], 'draft'),
                'cover' => $cover,
                'content' => $content,
                'updatedAt' => date(DATE_ATOM),
            ]);
            site_write($site);
            $message = 'Страница сохранена.';
        } elseif ($action === 'delete_page') {
            $id = post_text('id', 80);
            $site['pages'] = array_values(array_filter($site['pages'] ?? [], function ($page) use ($id) {
                return ($page['id'] ?? '') !== $id;
            }));
            site_write($site);
            $message = 'Страница удалена.';
        } elseif ($action === 'save_review') {
            $reviews = array_values($site['reviews'] ?? []);
            $id = post_text('id', 80);
            $reviewAction = post_choice('review_action', ['save', 'draft', 'publish', 'delete'], 'save');
            $reviewIndex = null;

            foreach ($reviews as $index => $review) {
                if (($review['id'] ?? '') === $id) {
                    $reviewIndex = $index;
                    break;
                }
            }

            if ($reviewAction === 'delete') {
                if ($reviewIndex !== null) {
                    array_splice($reviews, $reviewIndex, 1);
                }

                $site['reviews'] = $reviews;
                site_write($site);
                $message = 'Отзыв удален.';
            } else {
                $author = post_text('author', 120);
                $text = post_text('text', 3000);

                if ($author === '' || $text === '') {
                    throw new RuntimeException('Заполните имя и текст отзыва.');
                }

                $avatar = post_text('avatar', 250);

                if (!empty($_FILES['avatar']) && ($_FILES['avatar']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                    $avatar = upload_image($_FILES['avatar']);
                }

                $status = post_choice('status', ['published', 'draft'], 'published');

                if ($reviewAction === 'draft') {
                    $status = 'draft';
                } elseif ($reviewAction === 'publish') {
                    $status = 'published';
                }

                $reviewData = [
                    'id' => $id ?: create_id('review'),
                    'author' => $author,
                    'date' => post_text('date', 80),
                    'text' => $text,
                    'avatar' => $avatar,
                    'category' => post_choice('category', ['general', 'trim', 'frames', 'sills', 'stairs'], 'general'),
                    'status' => $status,
                    'order' => max(0, min(10000, (int) ($_POST['order'] ?? count($reviews) + 1))),
                ];

                if ($reviewIndex === null) {
                    $reviews[] = $reviewData;
                } else {
                    $reviews[$reviewIndex] = $reviewData;
                }

                usort($reviews, function ($firstReview, $secondReview) {
                return ($firstReview['order'] ?? 0) <=> ($secondReview['order'] ?? 0);
                });

                $site['reviews'] = $reviews;
                site_write($site);
                $message = $reviewAction === 'draft' ? 'Отзыв снят с публикации.' : 'Отзыв сохранен.';
            }
        } elseif ($action === 'save_lead_status') {
            $leads = leads_read();
            $leadStatus = post_choice('status', ['new', 'done'], 'new');
            $leadId = post_text('id', 80);
            foreach ($leads as &$lead) {
                if (($lead['id'] ?? '') === $leadId) {
                    $lead['status'] = $leadStatus;
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
}

if (!admin_is_authenticated()) {
    $content = '<main class="admin-page admin-page--login"><form method="post" class="admin-login">'
        . csrf_input()
        . '<input type="hidden" name="action" value="login">'
        . '<h1 class="admin-login__title">Админ-панель</h1>'
        . '<p class="admin-login__text">Введите логин и пароль для управления сайтом.</p>'
        . ($error ? '<p class="admin-message">' . h($error) . '</p>' : '')
        . '<input class="admin-input" type="text" name="username" placeholder="Логин" autocomplete="username" required autofocus>'
        . '<input class="admin-input" type="password" name="password" placeholder="Пароль" autocomplete="current-password" required>'
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
    . '<form method="post">' . csrf_input() . '<input type="hidden" name="action" value="logout"><button class="admin-button admin-button--ghost" type="submit">Выйти</button></form></header>'
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
    . csrf_input()
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
    . csrf_input()
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
            . csrf_input()
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
            . csrf_input()
            . '<input type="hidden" name="action" value="delete_page"><input type="hidden" name="id" value="' . h($page['id'] ?? '') . '">'
            . '<button type="submit" class="danger">Удалить</button></form></details>';
    }
    $content .= '</div></section>';
}

if ($currentSection === 'reviews') {
    $reviewItems = array_values($site['reviews'] ?? []);
    $reviewItems[] = [
        'id' => '',
        'author' => '',
        'date' => '',
        'text' => '',
        'avatar' => '',
        'category' => 'general',
        'status' => 'published',
        'order' => count($reviewItems) + 1,
    ];
    $content .= '<section id="reviews" class="panel admin-section"><div class="admin-section__head"><h2 class="admin-section__title">Отзывы</h2><p>Откройте нужную карточку, внесите изменения и сохраните ее отдельно. Внизу есть карточка для нового отзыва.</p></div><div class="admin-review-list">';
    foreach ($reviewItems as $index => $review) {
        $isNewReview = empty($review['id']) && empty($review['author']) && empty($review['text']);
        $cardTitle = $isNewReview ? 'Новый отзыв' : ($review['author'] ?? 'Отзыв');
        $status = ($review['status'] ?? 'published') === 'draft' ? 'draft' : 'published';
        $statusText = $status === 'draft' ? 'Черновик' : 'Опубликован';
        $excerpt = trim($review['text'] ?? '');
        $excerpt = $excerpt !== '' && function_exists('mb_substr') ? mb_substr($excerpt, 0, 120) : substr($excerpt, 0, 120);
        $content .= '<details class="admin-review-card">'
            . '<summary class="admin-review-card__summary">'
            . '<span class="admin-review-card__title">' . h($cardTitle) . '</span>'
            . '<span class="admin-review-card__status admin-review-card__status--' . h($status) . '">' . h($statusText) . '</span>'
            . '<span class="admin-review-card__excerpt">' . h($isNewReview ? 'Добавить новый отзыв' : $excerpt) . '</span>'
            . '</summary>'
            . '<form method="post" enctype="multipart/form-data" class="admin-review-card__form">'
            . csrf_input()
            . '<input type="hidden" name="action" value="save_review">'
            . '<input type="hidden" name="id" value="' . h($review['id'] ?? '') . '">'
            . '<input type="hidden" name="avatar" value="' . h($review['avatar'] ?? '') . '">'
            . '<div class="admin-review-card__grid">'
            . '<label>Имя<input name="author" value="' . h($review['author'] ?? '') . '"></label>'
            . '<label>Дата<input name="date" value="' . h($review['date'] ?? '') . '" placeholder="10 апреля 2023"></label>'
            . '<label>Категория<select name="category">'
            . '<option value="general"' . (($review['category'] ?? 'general') === 'general' ? ' selected' : '') . '>Общий</option>'
            . '<option value="trim"' . (($review['category'] ?? '') === 'trim' ? ' selected' : '') . '>Наличники</option>'
            . '<option value="frames"' . (($review['category'] ?? '') === 'frames' ? ' selected' : '') . '>Обсады</option>'
            . '<option value="sills"' . (($review['category'] ?? '') === 'sills' ? ' selected' : '') . '>Подоконники</option>'
            . '<option value="stairs"' . (($review['category'] ?? '') === 'stairs' ? ' selected' : '') . '>Лестницы</option>'
            . '</select></label>'
            . '<label>Статус<select name="status"><option value="published"' . ($status === 'published' ? ' selected' : '') . '>Опубликован</option><option value="draft"' . ($status === 'draft' ? ' selected' : '') . '>Черновик</option></select></label>'
            . '<label>Порядок<input type="number" name="order" value="' . h((string) ($review['order'] ?? $index + 1)) . '"></label>'
            . '<label>Аватарка<input type="file" name="avatar" accept="image/*"></label>'
            . '<label class="wide">Текст отзыва<textarea name="text" rows="6">' . h($review['text'] ?? '') . '</textarea></label>'
            . '</div>';
        if (!empty($review['avatar'])) {
            $content .= '<div class="admin-review-card__avatar"><img src="' . h($review['avatar']) . '" alt="' . h(image_alt('Аватар отзыва ' . (string) ($review['author'] ?? ''), 'Аватар отзыва')) . '"><span>' . h($review['avatar']) . '</span></div>';
        }
        $content .= '<div class="admin-review-card__actions">'
            . '<button type="submit" name="review_action" value="save">Сохранить карточку</button>'
            . (!$isNewReview ? ($status === 'published'
                ? '<button type="submit" name="review_action" value="draft" class="admin-button--secondary">Снять с публикации</button>'
                : '<button type="submit" name="review_action" value="publish" class="admin-button--secondary">Опубликовать</button>') : '')
            . (!$isNewReview ? '<button type="submit" name="review_action" value="delete" class="danger" onclick="return confirm(\'Удалить отзыв?\')">Удалить</button>' : '')
            . '</div></form></details>';
    }
    $content .= '</div></section>';
}

if ($currentSection === 'leads') {
    $content .= '<section id="leads" class="panel admin-section"><h2 class="admin-section__title">Заявки</h2><div class="lead-list">';
    if (!$leads) {
        $content .= '<p>Заявок пока нет.</p>';
    }
    foreach ($leads as $lead) {
        $content .= '<article class="lead"><div><strong>' . h($lead['name'] ?? 'Без имени') . '</strong><p>' . h($lead['phone'] ?? '') . '</p><p>' . h($lead['comment'] ?? '') . '</p><small>' . h($lead['createdAt'] ?? '') . '</small></div>'
            . '<form method="post">' . csrf_input() . '<input type="hidden" name="action" value="save_lead_status"><input type="hidden" name="id" value="' . h($lead['id'] ?? '') . '">'
            . '<select name="status"><option value="new"' . (($lead['status'] ?? '') === 'new' ? ' selected' : '') . '>Новая</option><option value="done"' . (($lead['status'] ?? '') === 'done' ? ' selected' : '') . '>Обработана</option></select>'
            . '<button type="submit">OK</button></form></article>';
    }
    $content .= '</div></section>';
}

$content .= '</main>';

render_admin_layout('Админ-панель', $content);

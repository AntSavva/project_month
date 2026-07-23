<?php

$message = '';
$error = '';

function admin_icon_options(): array
{
    return [
        '' => 'Авто (по содержанию)',
        'box' => 'Бокс',
        'car' => 'Грузовик',
        'check' => 'Галочка',
        'color' => 'Палитра',
        'detail' => 'Шестерёнка',
        'level' => 'Уровень',
        'lines' => 'Стрелки по кругу',
        'loop' => 'Лупа',
        'machine' => 'Станок',
        'medal' => 'Медаль',
        'person' => 'Пользователь',
        'person_with_star' => 'Пользователь со звездой',
        'roulette' => 'Рулетка',
        'rubles' => 'Ценник',
        'ruler_and_pen' => 'Карандаш и линейка',
        'shield' => 'Щит с медалью',
        'shield_1' => 'Щит',
        'star' => 'Звезда',
        'target' => 'Мишень',
        'tree' => 'Дерево',
        'woods' => 'Стопка досок',
    ];
}

function admin_icon_select(string $name, string $selected = ''): string
{
    $options = '';
    foreach (admin_icon_options() as $value => $label) {
        $options .= '<option value="' . h($value) . '"' . ($selected === $value ? ' selected' : '') . '>' . h($label) . '</option>';
    }

    return '<label class="admin-field"><span>Иконка</span><select class="admin-input" name="' . h($name) . '">' . $options . '</select></label>';
}

function admin_card_fields(string $section, $index, array $item = [], bool $withPopup = false): string
{
    $base = 'content[' . $section . '][items][' . $index . ']';
    return '<article class="admin-content-card" data-editor-item>'
        . '<label class="admin-field"><span>Заголовок</span><input class="admin-input" name="' . h($base . '[title]') . '" value="' . h($item['title'] ?? '') . '"></label>'
        . '<label class="admin-field"><span>Подзаголовок</span><textarea class="admin-input admin-input--textarea" name="' . h($base . '[description]') . '">' . h($item['description'] ?? '') . '</textarea></label>'
        . admin_icon_select($base . '[icon]', (string) ($item['icon'] ?? ''))
        . ($withPopup ? '<label class="admin-field"><span>Информационный поп-ап (первая строка — заголовок)</span><textarea class="admin-input admin-input--textarea admin-input--large" name="' . h($base . '[popup]') . '">' . h($item['popup'] ?? '') . '</textarea></label>' : '')
        . '<button class="admin-button admin-button--danger admin-button--wide" type="button" data-remove-item>Удалить карточку</button></article>';
}

function admin_cards_editor(string $section, array $items = [], bool $withPopup = false): string
{
    $html = '<div class="admin-cards-editor" data-editor data-next-index="' . count($items) . '">';
    foreach ($items as $index => $item) {
        $html .= admin_card_fields($section, $index, is_array($item) ? $item : [], $withPopup);
    }
    $html .= '<template data-editor-template>' . admin_card_fields($section, '__INDEX__', [], $withPopup) . '</template>'
        . '<button class="admin-button admin-button--wide" type="button" data-add-item>Добавить карточку</button></div>';
    return $html;
}

function admin_plan_fields($index, array $item = []): string
{
    $base = 'content[plans][items][' . $index . ']';
    $lines = is_array($item['items'] ?? null) ? implode("\n", $item['items']) : (string) ($item['items'] ?? '');
    return '<article class="admin-content-card" data-editor-item>'
        . '<label class="admin-field"><span>Название тарифа</span><input class="admin-input" name="' . h($base . '[title]') . '" value="' . h($item['title'] ?? '') . '"></label>'
        . admin_icon_select($base . '[icon]', (string) ($item['icon'] ?? ''))
        . '<label class="admin-field"><span>Преимущества тарифа — по одному в строке</span><textarea class="admin-input admin-input--textarea admin-input--large" name="' . h($base . '[items]') . '">' . h($lines) . '</textarea></label>'
        . '<button class="admin-button admin-button--danger admin-button--wide" type="button" data-remove-item>Удалить тариф</button></article>';
}

function admin_plans_editor(array $items = []): string
{
    $html = '<div class="admin-cards-editor" data-editor data-next-index="' . count($items) . '">';
    foreach ($items as $index => $item) {
        $html .= admin_plan_fields($index, is_array($item) ? $item : []);
    }
    return $html . '<template data-editor-template>' . admin_plan_fields('__INDEX__') . '</template>'
        . '<button class="admin-button admin-button--wide" type="button" data-add-item>Добавить тариф</button></div>';
}

function admin_room_solution_fields($index, array $item = []): string
{
    $base = 'content[roomSolutions][items][' . $index . ']';
    $lines = is_array($item['items'] ?? null) ? implode("\n", $item['items']) : (string) ($item['items'] ?? '');
    return '<article class="admin-content-card" data-editor-item>'
        . '<label class="admin-field"><span>Название решения</span><input class="admin-input" name="' . h($base . '[title]') . '" value="' . h($item['title'] ?? '') . '"></label>'
        . '<label class="admin-field"><span>Особенности — по одной в строке</span><textarea class="admin-input admin-input--textarea admin-input--large" name="' . h($base . '[items]') . '">' . h($lines) . '</textarea></label>'
        . '<button class="admin-button admin-button--danger admin-button--wide" type="button" data-remove-item>Удалить решение</button></article>';
}

function admin_room_solutions_editor(array $items = []): string
{
    $html = '<div class="admin-cards-editor" data-editor data-next-index="' . count($items) . '">';
    foreach ($items as $index => $item) {
        $html .= admin_room_solution_fields($index, is_array($item) ? $item : []);
    }
    return $html . '<template data-editor-template>' . admin_room_solution_fields('__INDEX__') . '</template>'
        . '<button class="admin-button admin-button--wide" type="button" data-add-item>Добавить решение</button></div>';
}

function admin_material_fields($index, array $item = []): string
{
    $base = 'content[materials][items][' . $index . ']';
    $image = (string) ($item['image'] ?? '');
    return '<article class="admin-material-card" data-editor-item>'
        . '<label class="admin-field"><span>Название материала</span><input class="admin-input" name="' . h($base . '[title]') . '" value="' . h($item['title'] ?? '') . '"></label>'
        . '<input type="hidden" name="' . h($base . '[image]') . '" value="' . h($image) . '">'
        . '<label class="admin-cover-upload"><span>Изображение материала</span>'
        . ($image !== '' ? '<img class="admin-cover-upload__preview" src="' . h($image) . '" alt="">' : '')
        . '<input class="admin-cover-upload__input" type="file" name="material_images[' . h((string) $index) . ']" accept=".png,.svg,.webp,.jpg,.jpeg,image/png,image/svg+xml,image/webp,image/jpeg"></label>'
        . '<button class="admin-button admin-button--danger" type="button" data-remove-item>Удалить материал</button></article>';
}

function admin_materials_editor(array $items = []): string
{
    $html = '<div class="admin-materials-editor" data-editor data-next-index="' . count($items) . '">';
    foreach ($items as $index => $item) {
        $html .= admin_material_fields($index, is_array($item) ? $item : []);
    }
    return $html . '<template data-editor-template>' . admin_material_fields('__INDEX__') . '</template>'
        . '<button class="admin-button admin-button--wide" type="button" data-add-item>Добавить материал</button></div>';
}

function admin_faq_fields($index, array $item = []): string
{
    $base = 'content[faq][items][' . $index . ']';
    return '<article class="admin-faq-card" data-editor-item>'
        . '<label class="admin-field"><span>Вопрос</span><input class="admin-input" name="' . h($base . '[question]') . '" value="' . h($item['question'] ?? '') . '"></label>'
        . '<label class="admin-field"><span>Ответ</span><textarea class="admin-input admin-input--textarea" name="' . h($base . '[answer]') . '">' . h($item['answer'] ?? '') . '</textarea></label>'
        . '<button class="admin-button admin-button--danger admin-button--wide" type="button" data-remove-item>Удалить вопрос</button></article>';
}

function admin_faq_editor(array $items = []): string
{
    $html = '<div class="admin-faq-editor" data-editor data-next-index="' . count($items) . '">';
    foreach ($items as $index => $item) {
        $html .= admin_faq_fields($index, is_array($item) ? $item : []);
    }
    return $html . '<template data-editor-template>' . admin_faq_fields('__INDEX__') . '</template>'
        . '<button class="admin-button admin-button--wide" type="button" data-add-item>Добавить вопрос</button></div>';
}

function admin_content_block(string $title, string $body): string
{
    return '<div class="admin-product-block"><h3 class="admin-product-block__title">' . h($title) . '</h3>' . $body . '</div>';
}

function admin_content_field(string $label, string $name, $value, bool $textarea = false): string
{
    $control = $textarea
        ? '<textarea class="admin-input admin-input--textarea" name="' . h($name) . '">' . h($value) . '</textarea>'
        : '<input class="admin-input" name="' . h($name) . '" value="' . h($value) . '">';
    return '<label class="admin-field"><span>' . h($label) . '</span>' . $control . '</label>';
}

function admin_product_content_editor(array $data): string
{
    $html = '<input type="hidden" name="structured_content" value="1">';
    $html .= admin_content_block('Первый экран',
        admin_content_field('Надзаголовок', 'content[hero][subtitle]', $data['hero']['subtitle'] ?? '')
        . admin_content_field('Заголовок', 'content[hero][title]', $data['hero']['title'] ?? '')
        . admin_content_field('Акцентная строка', 'content[hero][accent]', $data['hero']['accent'] ?? ''));
    $html .= admin_content_block('Что входит в услугу',
        admin_content_field('Заголовок', 'content[includes][title]', $data['includes']['title'] ?? '')
        . admin_content_field('Подзаголовок', 'content[includes][description]', $data['includes']['description'] ?? '', true)
        . '<div class="admin-field admin-field--full"><span>Карточки</span>' . admin_cards_editor('includes', $data['includes']['items'] ?? []) . '</div>');
    $html .= admin_content_block('Материалы',
        admin_content_field('Заголовок', 'content[materials][title]', $data['materials']['title'] ?? '')
        . admin_materials_editor($data['materials']['items'] ?? []));
    $html .= admin_content_block('Дополнительные опции и возможности',
        admin_content_field('Заголовок', 'content[colors][title]', $data['colors']['title'] ?? '')
        . admin_content_field('Описание рядом с заголовком', 'content[colors][description]', $data['colors']['description'] ?? '', true)
        . '<div class="admin-field admin-field--full"><span>Карточки</span>' . admin_cards_editor('colors', $data['colors']['items'] ?? [], true) . '</div>');
    $html .= admin_content_block('Преимущества',
        admin_content_field('Заголовок', 'content[benefits][title]', $data['benefits']['title'] ?? '')
        . admin_content_field('Описание рядом с заголовком', 'content[benefits][description]', $data['benefits']['description'] ?? '', true)
        . '<div class="admin-field admin-field--full"><span>Карточки</span>' . admin_cards_editor('benefits', $data['benefits']['items'] ?? [], true) . '</div>');
    $html .= admin_content_block('Тарифы',
        admin_content_field('Заголовок секции', 'content[plans][title]', $data['plans']['title'] ?? '')
        . '<div class="admin-field admin-field--full"><span>Тарифы и списки преимуществ</span>' . admin_plans_editor($data['plans']['items'] ?? []) . '</div>');
    $html .= admin_content_block('FAQ / Частые вопросы', admin_faq_editor($data['faq']['items'] ?? []));
    return $html;
}

function admin_interior_content_editor(array $data): string
{
    $html = '<input type="hidden" name="structured_content" value="1">';
    $html .= admin_content_block('Первый экран',
        admin_content_field('Надзаголовок', 'content[hero][subtitle]', $data['hero']['subtitle'] ?? '')
        . admin_content_field('Заголовок', 'content[hero][title]', $data['hero']['title'] ?? '')
        . admin_content_field('Акцентная строка', 'content[hero][accent]', $data['hero']['accent'] ?? ''));
    $html .= admin_content_block('Что входит в услугу',
        admin_content_field('Заголовок', 'content[includes][title]', $data['includes']['title'] ?? '')
        . '<div class="admin-field admin-field--full"><span>Карточки</span>' . admin_cards_editor('includes', $data['includes']['items'] ?? []) . '</div>');
    $html .= admin_content_block('Специальные решения',
        admin_content_field('Заголовок', 'content[roomSolutions][title]', $data['roomSolutions']['title'] ?? '')
        . '<div class="admin-field admin-field--full"><span>Карточки со списками</span>' . admin_room_solutions_editor($data['roomSolutions']['items'] ?? []) . '</div>');
    $html .= admin_content_block('Материалы',
        admin_content_field('Заголовок', 'content[materials][title]', $data['materials']['title'] ?? '')
        . admin_materials_editor($data['materials']['items'] ?? []));
    $html .= admin_content_block('Преимущества',
        admin_content_field('Заголовок', 'content[advantages][title]', $data['advantages']['title'] ?? '')
        . admin_content_field('Описание рядом с заголовком', 'content[advantages][description]', $data['advantages']['description'] ?? '', true)
        . '<div class="admin-field admin-field--full"><span>Карточки</span>' . admin_cards_editor('advantages', $data['advantages']['items'] ?? []) . '</div>');
    $html .= admin_content_block('Тарифы',
        admin_content_field('Заголовок секции', 'content[plans][title]', $data['plans']['title'] ?? '')
        . '<div class="admin-field admin-field--full"><span>Тарифы и списки преимуществ</span>' . admin_plans_editor($data['plans']['items'] ?? []) . '</div>');
    $html .= admin_content_block('FAQ / Частые вопросы', admin_faq_editor($data['faq']['items'] ?? []));
    return $html;
}

function admin_document_content_editor(array $data): string
{
    return '<input type="hidden" name="structured_content" value="1">'
        . admin_content_block('Содержание документа',
            admin_content_field('Заголовок H1', 'content[h1]', $data['h1'] ?? '')
            . '<label class="admin-field admin-field--full"><span>Текст документа</span><textarea class="admin-input admin-input--textarea admin-input--large" name="content[text]">' . h($data['text'] ?? '') . '</textarea></label>');
}

function admin_content_with_material_uploads(array $contentInput): array
{
    $materialFiles = is_array($_FILES['material_images'] ?? null) ? $_FILES['material_images'] : [];
    foreach (is_array($materialFiles['name'] ?? null) ? $materialFiles['name'] : [] as $materialIndex => $name) {
        if (!is_array($contentInput['materials']['items'][$materialIndex] ?? null)
            || trim((string) ($contentInput['materials']['items'][$materialIndex]['title'] ?? '')) === '') {
            continue;
        }
        $file = [
            'name' => $name,
            'type' => $materialFiles['type'][$materialIndex] ?? '',
            'tmp_name' => $materialFiles['tmp_name'][$materialIndex] ?? '',
            'error' => $materialFiles['error'][$materialIndex] ?? UPLOAD_ERR_NO_FILE,
            'size' => $materialFiles['size'][$materialIndex] ?? 0,
        ];
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $contentInput['materials']['items'][$materialIndex]['image'] = upload_material_image($file);
        }
    }
    return $contentInput;
}

function render_admin_layout(string $title, string $content): void
{
    echo '<!doctype html><html lang="ru"><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<meta name="robots" content="noindex, nofollow">';
    echo '<title>' . h($title) . '</title>';
    echo '<link rel="stylesheet" href="/assets/css/admin.css">';
    echo '<link rel="stylesheet" href="/assets/css/site.css?v=20260713-admin-editor-dark">';
    echo '<script src="/assets/js/admin.js" defer></script>';
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
                    'avito' => post_text('avito', 500),
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

            $page = $site['pages'][$index];
            if (in_array($page['type'] ?? '', ['product', 'interior', 'document'], true) && !empty($_POST['structured_content'])) {
                $contentInput = is_array($_POST['content'] ?? null) ? $_POST['content'] : [];
                if (($page['type'] ?? '') === 'document') {
                    $content = document_content_from_input($contentInput);
                } else {
                    $contentInput = admin_content_with_material_uploads($contentInput);
                    $content = ($page['type'] ?? '') === 'interior'
                        ? interior_content_from_input($contentInput)
                        : product_content_from_input($contentInput);
                }
            } else {
                $contentJson = (string) ($_POST['content'] ?? '{}');

                if (strlen($contentJson) > 200000) {
                    throw new RuntimeException('Контент слишком большой.');
                }

                $content = json_decode($contentJson, true);

                if (!is_array($content)) {
                    throw new RuntimeException('Контент должен быть корректным JSON.');
                }
            }

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
    . '<label>Avito<input name="avito" value="' . h($settings['socials']['avito'] ?? '') . '"></label>'
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
        $pageContent = is_array($page['content'] ?? null) ? $page['content'] : [];
        if (($page['type'] ?? '') === 'product') {
            $contentEditor = admin_product_content_editor($pageContent);
        } elseif (($page['type'] ?? '') === 'interior') {
            $contentEditor = admin_interior_content_editor($pageContent);
        } elseif (($page['type'] ?? '') === 'document') {
            $contentEditor = admin_document_content_editor($pageContent);
        } else {
            $contentEditor = '<label class="wide">Контент JSON<textarea name="content" rows="16" class="code">' . h(json_encode($pageContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) . '</textarea></label>';
        }
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
            . $contentEditor
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

<?php

function app_path(string $path = ''): string
{
    return __DIR__ . ($path ? '/' . ltrim($path, '/') : '');
}

function root_path(string $path = ''): string
{
    return dirname(__DIR__) . ($path ? '/' . ltrim($path, '/') : '');
}

function storage_read_json(string $path, array $fallback): array
{
    if (!is_file($path)) {
        storage_write_json($path, $fallback);
        return $fallback;
    }

    $content = file_get_contents($path);
    $data = json_decode(preg_replace('/^\xEF\xBB\xBF/', '', $content ?: ''), true);

    return is_array($data) ? $data : $fallback;
}

function storage_write_json(string $path, array $data): void
{
    $dir = dirname($path);

    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    file_put_contents($path, $json . PHP_EOL, LOCK_EX);
}

function site_default_data(): array
{
    return [
        'settings' => [
            'phone' => '',
            'email' => '',
            'address' => '',
            'workingHours' => '',
            'legalInfo' => '',
            'socials' => [],
        ],
        'reviews' => [],
        'pages' => [],
    ];
}

function site_read(): array
{
    $data = storage_read_json(root_path('data/site.json'), site_default_data());

    return array_replace_recursive(site_default_data(), $data);
}

function site_write(array $site): void
{
    storage_write_json(root_path('data/site.json'), $site);
}

function leads_read(): array
{
    return storage_read_json(root_path('data/leads.json'), []);
}

function leads_write(array $leads): void
{
    storage_write_json(root_path('data/leads.json'), $leads);
}

function site_pages(array $site, ?string $type = null, bool $publishedOnly = false): array
{
    $pages = $site['pages'] ?? [];

    return array_values(array_filter($pages, function ($page) use ($type, $publishedOnly) {
        if ($type && ($page['type'] ?? '') !== $type) {
            return false;
        }

        if ($publishedOnly && ($page['status'] ?? '') !== 'published') {
            return false;
        }

        return true;
    }));
}

function site_find_page(array $site, string $slug): ?array
{
    foreach ($site['pages'] ?? [] as $page) {
        if (($page['slug'] ?? '') === $slug && ($page['status'] ?? '') === 'published') {
            return $page;
        }
    }

    return null;
}

function site_find_page_index(array $site, string $id): ?int
{
    foreach ($site['pages'] ?? [] as $index => $page) {
        if (($page['id'] ?? '') === $id) {
            return $index;
        }
    }

    return null;
}

function create_id(string $prefix): string
{
    return $prefix . '-' . time() . '-' . bin2hex(random_bytes(3));
}

function normalize_slug(string $slug): string
{
    $slug = trim($slug);
    $slug = trim($slug, '/');
    $slug = preg_replace('/\s+/u', '-', $slug);

    return function_exists('mb_strtolower') ? mb_strtolower($slug) : strtolower($slug);
}

function default_content(string $type): array
{
    if ($type === 'document') {
        return ['h1' => 'Документ', 'text' => 'Текст документа'];
    }

    if ($type === 'interior') {
        return [
            'hero' => ['subtitle' => 'Внутренняя отделка', 'title' => 'Новая страница', 'accent' => ''],
            'includes' => ['title' => 'Что входит в услугу', 'items' => []],
            'roomSolutions' => ['title' => 'Специальные решения', 'items' => []],
            'materials' => ['title' => 'Материалы', 'items' => []],
            'advantages' => ['title' => 'Преимущества', 'description' => '', 'items' => []],
            'plans' => ['title' => 'Варианты сотрудничества', 'items' => []],
            'faq' => ['items' => []],
        ];
    }

    return [
        'hero' => ['subtitle' => 'Столярные изделия', 'title' => 'Новая страница', 'accent' => ''],
        'includes' => ['title' => 'Что входит в услугу', 'description' => '', 'items' => []],
        'materials' => ['title' => 'Материалы', 'items' => []],
        'colors' => ['title' => 'Дополнительные опции', 'description' => '', 'items' => []],
        'benefits' => ['title' => 'Преимущества', 'description' => '', 'items' => []],
        'plans' => ['title' => 'Варианты сотрудничества', 'items' => []],
        'faq' => ['items' => []],
    ];
}

function product_content_from_input(array $input): array
{
    $allowedIcons = ['', 'box', 'car', 'check', 'color', 'detail', 'level', 'lines', 'loop', 'machine', 'medal', 'person', 'person_with_star', 'roulette', 'rubles', 'ruler_and_pen', 'shield', 'shield_1', 'star', 'target', 'tree', 'woods'];
    $text = function ($value, int $length = 500): string {
        return is_scalar($value) ? limit_text((string) $value, $length) : '';
    };
    $cards = function ($items) use ($text, $allowedIcons): array {
        $result = [];
        foreach (array_slice(is_array($items) ? $items : [], 0, 50) as $item) {
            if (!is_array($item)) {
                continue;
            }
            $card = [
                'title' => $text($item['title'] ?? '', 200),
                'description' => $text($item['description'] ?? '', 1000),
            ];
            $icon = $text($item['icon'] ?? '', 40);
            if ($icon !== '' && in_array($icon, $allowedIcons, true)) {
                $card['icon'] = $icon;
            }
            if ($card['title'] !== '' || $card['description'] !== '') {
                $result[] = $card;
            }
        }
        return $result;
    };
    $lines = function ($value) use ($text): array {
        $value = is_scalar($value) ? (string) $value : '';
        return array_values(array_filter(array_map(function ($line) use ($text) {
            return $text($line, 500);
        }, preg_split('/\R/u', $value) ?: []), 'strlen'));
    };

    $materials = [];
    foreach (array_slice(is_array($input['materials']['items'] ?? null) ? $input['materials']['items'] : [], 0, 30) as $item) {
        if (!is_array($item)) {
            continue;
        }
        $material = [
            'title' => $text($item['title'] ?? '', 200),
            'image' => $text($item['image'] ?? '', 500),
        ];
        if ($material['title'] !== '') {
            $materials[] = $material;
        }
    }

    $plans = [];
    foreach (array_slice(is_array($input['plans']['items'] ?? null) ? $input['plans']['items'] : [], 0, 20) as $item) {
        if (!is_array($item)) {
            continue;
        }
        $plan = [
            'title' => $text($item['title'] ?? '', 200),
            'items' => $lines($item['items'] ?? ''),
        ];
        $icon = $text($item['icon'] ?? '', 40);
        if ($icon !== '' && in_array($icon, $allowedIcons, true)) {
            $plan['icon'] = $icon;
        }
        if ($plan['title'] !== '' || $plan['items']) {
            $plans[] = $plan;
        }
    }

    $faq = [];
    foreach (array_slice(is_array($input['faq']['items'] ?? null) ? $input['faq']['items'] : [], 0, 50) as $item) {
        if (!is_array($item)) {
            continue;
        }
        $question = $text($item['question'] ?? '', 500);
        $answer = $text($item['answer'] ?? '', 3000);
        if ($question !== '' || $answer !== '') {
            $faq[] = ['question' => $question, 'answer' => $answer];
        }
    }

    return [
        'hero' => [
            'subtitle' => $text($input['hero']['subtitle'] ?? '', 200),
            'title' => $text($input['hero']['title'] ?? '', 300),
            'accent' => $text($input['hero']['accent'] ?? '', 300),
        ],
        'includes' => [
            'title' => $text($input['includes']['title'] ?? '', 300),
            'description' => $text($input['includes']['description'] ?? '', 1000),
            'items' => $cards($input['includes']['items'] ?? []),
        ],
        'materials' => [
            'title' => $text($input['materials']['title'] ?? '', 300),
            'items' => $materials,
        ],
        'colors' => [
            'title' => $text($input['colors']['title'] ?? '', 300),
            'description' => $text($input['colors']['description'] ?? '', 1000),
            'items' => $cards($input['colors']['items'] ?? []),
        ],
        'benefits' => [
            'title' => $text($input['benefits']['title'] ?? '', 300),
            'description' => $text($input['benefits']['description'] ?? '', 1000),
            'items' => $cards($input['benefits']['items'] ?? []),
        ],
        'plans' => [
            'title' => $text($input['plans']['title'] ?? '', 300),
            'items' => $plans,
        ],
        'faq' => ['items' => $faq],
    ];
}

function upload_image(array $file): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return '';
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Не удалось загрузить файл.');
    }

    if (($file['size'] ?? 0) > MAX_UPLOAD_SIZE) {
        throw new RuntimeException('Файл должен быть меньше 5 МБ.');
    }

    $extension = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
    $allowedMimeByExtension = [
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'webp' => ['image/webp'],
        'gif' => ['image/gif'],
    ];

    if (!isset($allowedMimeByExtension[$extension])) {
        throw new RuntimeException('Поддерживаются JPG, PNG, WebP и GIF.');
    }

    $tmpName = (string) ($file['tmp_name'] ?? '');

    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        throw new RuntimeException('Не удалось проверить загруженный файл.');
    }

    $imageInfo = @getimagesize($tmpName);

    if ($imageInfo === false) {
        throw new RuntimeException('Файл должен быть изображением.');
    }

    $mime = strtolower((string) ($imageInfo['mime'] ?? ''));

    if (!in_array($mime, $allowedMimeByExtension[$extension], true)) {
        throw new RuntimeException('Тип изображения не соответствует расширению файла.');
    }

    $width = (int) ($imageInfo[0] ?? 0);
    $height = (int) ($imageInfo[1] ?? 0);

    if ($width < 1 || $height < 1 || $width > 8000 || $height > 8000) {
        throw new RuntimeException('Некорректный размер изображения.');
    }

    $uploadsDir = root_path('uploads');

    if (!is_dir($uploadsDir)) {
        mkdir($uploadsDir, 0775, true);
    }

    $name = time() . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
    $target = $uploadsDir . '/' . $name;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        throw new RuntimeException('Не удалось сохранить файл.');
    }

    return '/uploads/' . $name;
}

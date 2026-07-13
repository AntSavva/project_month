<?php

require __DIR__ . '/../app/storage.php';
require __DIR__ . '/../app/helpers.php';

$content = product_content_from_input([
    'hero' => ['subtitle' => 'Услуга', 'title' => 'Лестницы', 'accent' => 'из дуба'],
    'includes' => ['title' => 'Что входит', 'items' => [
        ['title' => 'Замер', 'description' => 'На объекте', 'icon' => 'roulette'],
        ['title' => '', 'description' => ''],
    ]],
    'plans' => ['title' => 'Тарифы', 'items' => [[
        'title' => 'Стандарт',
        'icon' => '../config',
        'items' => "Замер\n\nМонтаж",
    ]]],
]);

assert($content['hero']['title'] === 'Лестницы');
assert(count($content['includes']['items']) === 1);
assert($content['includes']['items'][0]['icon'] === 'roulette');
assert($content['plans']['items'][0]['items'] === ['Замер', 'Монтаж']);
assert(!isset($content['plans']['items'][0]['icon']));

if (class_exists('DOMDocument')) {
    assert(str_contains(sanitize_uploaded_svg('<svg xmlns="http://www.w3.org/2000/svg"><path d="M0 0h10v10z"/></svg>'), '<svg'));
    $unsafeSvgRejected = false;
    try {
        sanitize_uploaded_svg('<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>');
    } catch (RuntimeException $exception) {
        $unsafeSvgRejected = true;
    }
    assert($unsafeSvgRejected);
}

echo "admin product content: ok\n";

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

$interior = interior_content_from_input([
    'hero' => ['title' => 'Кабинеты'],
    'includes' => ['title' => 'Что входит', 'items' => [['title' => 'Замер', 'icon' => 'roulette']]],
    'roomSolutions' => ['title' => 'Решения', 'items' => [['title' => 'Для офисов', 'items' => "Акустика\nСкрытая проводка"]]],
    'advantages' => ['title' => 'Преимущества', 'items' => [['title' => 'Тишина', 'description' => 'Шумоизоляция', 'icon' => 'shield']]],
]);
assert($interior['hero']['title'] === 'Кабинеты');
assert($interior['roomSolutions']['items'][0]['items'] === ['Акустика', 'Скрытая проводка']);
assert($interior['advantages']['items'][0]['icon'] === 'shield');

$document = document_content_from_input(['h1' => 'Политика', 'text' => "Первый абзац\n\nВторой абзац"]);
assert($document['h1'] === 'Политика');
assert($document['text'] === "Первый абзац\n\nВторой абзац");

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

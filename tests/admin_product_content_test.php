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

echo "admin product content: ok\n";

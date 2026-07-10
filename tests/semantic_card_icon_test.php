<?php

require dirname(__DIR__) . '/app/helpers.php';

assert(str_ends_with(semantic_card_icon_url(['title' => 'Доставка на объект'], 'star'), '/car.png'));
assert(str_ends_with(semantic_card_icon_url(['title' => 'Точный замер'], 'star'), '/roulette.png'));
assert(str_ends_with(semantic_card_icon_url(['title' => 'Рабочие чертежи'], 'star'), '/ruler_and_pen.png'));
assert(str_ends_with(semantic_card_icon_url(['title' => 'Доставка', 'icon' => 'box'], 'star'), '/box.png'));

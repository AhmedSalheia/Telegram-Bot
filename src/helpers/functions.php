<?php

function route($route) {
    $route = explode('.', $route);
    return \TelegramBot\Components\Router::initialize(['type'=>$route[0],'route'=>$route[1], 'args'=>array_slice($route,2)]);
}

<?php

require 'vendor/autoload.php';
use \TelegramBot\Bot;
use \TelegramBot\Components\Router;
use \TelegramBot\Components\Response;

$bot = new Bot('5362393291:AAFUDrCFU_yvq6QJN20eujyOZgAeHIqdbiE');
if (@$_SERVER['CONTENT_TYPE']===null) {
    $bot::setupWebsocket();
    exit();
}

Router::input('/start::id::',function ($id) use ($bot){
    return Response::sendMessage()->text($id??'id is null');
    // inside the input use {{$bot->update()->message()}} to get your message data...
    $text = "Hi ".$bot->update()->message()->from()->username." Your ID is: ".$bot->update()->message()->from()->id;
    return Response::sendMessage()->text($text);
});

Router::callback('##hi', function () use ($bot){
    // inside the input use {{$bot->update()->callback()}} to get your callback data, and {{$bot->update()->callback()->message()}} to get message data...
    $text = "Hi ".$bot->update()->callback()->message()->from()->username." Your ID is: ".$bot->update()->callback()->message()->from()->id;
    return Response::sendMessage()->text('$text');
});

Router::initialize();

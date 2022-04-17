<?php

require 'vendor/autoload.php';
use \TelegramBot\Bot;
use \TelegramBot\Components\Router;
use \TelegramBot\Components\Response;

$bot = new Bot('Your Telegram Token');
if (@$_SERVER['CONTENT_TYPE']===null) {
    $bot::setupWebsocket();
    exit();
}

Router::input('/start',function () use ($bot){
    // inside the input use {{$bot->update()->message()}} to get your message data...
    $text = "Hi ".$bot->update()->message()->from()->username." Your ID is: ".$bot->update()->message()->from()->id;
    return Response::sendMessage()->text($text);
});

Router::callback('##hi', function () use ($bot){
    // inside the input use {{$bot->update()->callback()}} to get your callback data, and {{$bot->update()->callback()->message()}} to get message data...
    $text = "Hi ".$bot->update()->callback()->message()->from()->username." Your ID is: ".$bot->update()->callback()->message()->from()->id;
    return Response::sendMessage()->text($text);
});

Router::initialize();

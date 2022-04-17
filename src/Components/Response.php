<?php

namespace TelegramBot\Components;

class Response
{
    public static function sendMessage($args=[])
    {
        return new Send('SendMessage',$args);
    }
    public static function editMessageText($args=[])
    {
        return new Send('EditMessageText',$args);
    }
    public static function answerCallbackQuery($args=[])
    {
        return new Send('AnswerCallbackQuery',$args);
    }
    public static function deleteMessage($args=[])
    {
        return new Send('DeleteMessage',$args);
    }
}

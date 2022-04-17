<?php

namespace TelegramBot\Components\Update;

class Message
{
    private $message;
    public function __construct($message)
    {
        $this->message = $message;
    }

    public function id()
    {
        return $this->message->message_id;
    }
    public function chat()
    {
        return $this->message->chat;
    }
    public function from()
    {
        return $this->message->from;
    }
    public function text()
    {
        return $this->message->text;
    }
}

<?php

namespace TelegramBot\Components\Update;

class CallbackQuery
{
    private $callback_query;
    private $message=null;

    public function __construct($callback_query)
    {
        $this->callback_query = $callback_query;
        $this->message = new Message($this->callback_query->message);
    }
    public function from()
    {
        return $this->callback_query->from;
    }
    public function message()
    {
        return $this->message;
    }
    public function data()
    {
        return $this->callback_query->data;
    }
}

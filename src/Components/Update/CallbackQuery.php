<?php

namespace TelegramBot\Components\Update;

class CallbackQuery
{
    private $callback_query;
    private $message=null;

    public function __get(string $name)
    {
        return (!in_array($name,get_class_vars(self::class)))?$this->callback_query->$name:$this->$name;
    }

    public function __construct($callback_query)
    {
        $this->callback_query = $callback_query;
        $this->message = new Message($this->callback_query->message);
    }
    public function from()
    {
        return $this->from;
    }
    public function message()
    {
        return $this->message;
    }
    public function data()
    {
        return $this->data;
    }
}

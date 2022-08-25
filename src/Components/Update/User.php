<?php

namespace TelegramBot\Components\Update;

class User
{
    public $id;
    public $first_name;
    public $last_name;
    public $username;
    public $language_code;

    public function __construct($data)
    {
        foreach ($data as $key=>$value) $this->$key = $value;
    }
}

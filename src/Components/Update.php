<?php

namespace TelegramBot\Components;

use TelegramBot\Components\Update\CallbackQuery;
use TelegramBot\Components\Update\Message;

class Update
{
    protected $update = null;
    protected $message = null;
    protected $callback = null;

    public function __construct()
    {
        $this->update = json_decode(file_get_contents('php://input'), false);
        if (isset($this->update->message))
            $this->message = new Message($this->update->message);

        if (isset($this->update->callback_query))
            $this->callback = new CallbackQuery($this->update->callback_query);
    }

    public function getRoute()
    {
        return ($this->message!==null)?
            ['type'=>'input','route'=>$this->message()->text()]:
            ['type'=>'callback','route'=>$this->callback()->data()];
    }
    public function getChatId()
    {
        if ($this->message!==null)
            return $this->message()->chat()->id;
        else
            return $this->callback()->message()->chat()->id;
    }
    public function getMessageId()
    {
        if ($this->message!==null)
            return $this->message()->id();
        else
            return $this->callback()->message()->id();
    }
    public function message()
    {
        return $this->message;
    }
    public function callback()
    {
        return $this->callback;
    }
}

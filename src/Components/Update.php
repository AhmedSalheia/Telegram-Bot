<?php

namespace TelegramBot\Components;

use TelegramBot\Components\Update\CallbackQuery;
use TelegramBot\Components\Update\Message;
use TelegramBot\Components\Update\User;

class Update
{
    protected $update = null;
    protected $message = null;
    protected $callback = null;
    protected $user = null;

    public function __construct()
    {
        $this->update = json_decode(file_get_contents('php://input'), false);
        if (isset($this->update->message))
            $this->message = new Message($this->update->message);

        if (isset($this->update->callback_query))
            $this->callback = new CallbackQuery($this->update->callback_query);

        $this->user = new User((array)($this->message??$this->callback)->from());
    }

    public function getRoute()
    {
        $data = explode(' ',$this->message()?->text()??$this->callback()?->data(), 2);
        return ($this->message!==null)?
            ['type'=>'input','route'=>array_shift($data), 'args'=>$data]:
            ['type'=>'callback','route'=>array_shift($data), 'args'=>$data];
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
    public function user()
    {
        return $this->user;
    }
    public function callback()
    {
        return $this->callback;
    }
}

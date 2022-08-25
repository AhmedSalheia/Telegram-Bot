<?php

namespace TelegramBot;

use TelegramBot\Components\Send;
use TelegramBot\Components\Update;
use TelegramBot\Components\Router;

class Bot
{
    public static $TOKEN;
    protected static $bot = null;
    protected static $update = null;
    protected static $router = null;
    protected static $admins = [];
    public static $channels = [];

    public function __construct($token="")
    {
        if ($token !== "")
            self::$TOKEN = $token;
    }

    public function __call(string $name, array $arguments)
    {
        return $this->$name($arguments);
    }
    public static function __callStatic(string $name, array $arguments)
    {
        return (new self)->$name($arguments);
    }

    public static function setupWebsocket()
    {
        $ws = self::query('setWebhook',['url'=>"https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']]);
        if ($ws->ok===false)
            throw new \Exception('Error Setting Websocket');
    }

    // me data:
    public function me()
    {
        if (self::$bot === null)
            self::$bot = $this->send('getme')->execute();
        return self::$bot;
    }
    public function name()
    {
        return $this->me()->username;
    }
    public function id()
    {
        return $this->me()->id;
    }

    //updates:
    protected function update()
    {
        if (self::$update===null)
            self::$update = new Update();
        return self::$update;
    }
    protected function router(){
        if (self::$router===null)
            self::$router = new Router();
        return self::$router;
    }

    protected function admins($admins)
    {
        self::$admins = $admins[0];
        return $this;
    }

    protected function is_admin()
    {
        return in_array(($this->update()->message() ?? $this->update()->callback())->from()->id,self::$admins);
    }

    protected function channel($username, $id)
    {
        self::$channels[$username] = $id;
        return $this;
    }

    protected function channels(array $channels)
    {
        foreach ($channels[0] as $username=>$id) $this->channel($username, $id);
        return $this;
    }

    protected function checkChannels()
    {
        foreach (self::$channels as $username=>$id)
        {
            $q = self::query('getchatmember',[
                'user_id'   =>  $this->update()->getChatId(),
                'chat_id'   =>  $id
            ]);
            if ($q->ok === false)
                return Router::respondWithRoute('input.fallback_required_channels');
        }
        return true;
    }

    protected static function query($method, $queryData=[])
    {
        return (new self)->send($method.'?'.http_build_query($queryData),['chat_id'=>'chat_id','message_id'=>'message_id'])->execute('all',false);
    }

    public function send($method, $args=[])
    {
        return new Send($method,$args);
    }
}

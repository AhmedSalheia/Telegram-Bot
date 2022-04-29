<?php

namespace TelegramBot;

use TelegramBot\Components\Send;
use TelegramBot\Components\Update;
use TelegramBot\Components\Router;

date_default_timezone_set("Asia/Jerusalem");
class Bot
{
    public static $TOKEN;
    protected static $bot = null;
    protected static $update = null;
    protected static $router = null;
    protected static $admins = [];

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
        $ws = json_decode(file_get_contents("https://api.telegram.org/bot".self::$TOKEN."/setWebhook?url=https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']));
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

    public function admins(...$admins)
    {
        self::$admins = $admins;
    }

    public function is_admin()
    {
        return in_array(($this->update()->message() ?? $this->update()->callback())->from()->id,self::$admins);
    }

    public function send($method)
    {
        return new Send($method);
    }
}

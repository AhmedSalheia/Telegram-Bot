<?php

namespace TelegramBot\Components;

use TelegramBot\Bot;

class Router
{
    protected static $inputs = [];
    protected static $callbacks = [];
    public static $namespace = '';
    public static $prefixes = [
        'inputs' => '/',
        'callbacks' => '##'
    ];

    public static function initialize()
    {
        self::set404Routes();

        $route = Bot::update()->getRoute();
        $type = $route['type'].'s';
        $key = strtolower(str_replace(self::$prefixes[$type],'',$route['route']));
        if (!array_key_exists($key,self::$$type)) $key = 'default.404.response';

        return self::return($key, $type);
    }
    private function getReference(\Closure|array|string $value)
    {
        if ($value instanceof \Closure) return $value();
        elseif(is_array($value)) return (new $value[0])->{$value[1]}();
        else {
            $value = explode('@',$value);
            return (new (self::$namespace . $value[0]))->{$value[1]}();
        }
    }
    private static function return($key, $type)
    {
        $return = (new self())->getReference((self::$$type)[$key]);
        if ($return instanceof Send)
            return $return->execute(false);
        else
            return $return;
    }

    public static function input($key, \Closure|array|string $value)
    {
        $key = strtolower(str_replace(self::$prefixes['inputs'],'',$key));
        self::$inputs[$key] = $value;
    }
    public static function callback($key, \Closure|array|string $value)
    {
        $key = strtolower(str_replace(self::$prefixes['callbacks'],'',$key));
        self::$callbacks[$key] = $value;
    }

    private static function set404routes()
    {
        if (!array_key_exists('default.404.response', self::$inputs))
            self::input('default.404.response', function () {
                return Response::sendMessage()->text('Your Command Is Not Found');
            });
        if (!array_key_exists('default.404.response', self::$callbacks))
            self::callback('default.404.response', function () {
            return Response::sendMessage()->text('Your Command Is Not Found');
        });
    }
}

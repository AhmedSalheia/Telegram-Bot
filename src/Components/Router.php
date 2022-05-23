<?php

namespace TelegramBot\Components;

use TelegramBot\Bot;

class Router
{
    protected static $inputs = [];
    protected static $callbacks = [];
    protected static $steps = [];
    protected static $step = '';
    protected static $adminRoutes = [
        'inputs' => [],
        'callbacks'=> []
    ];
    public static $namespace = '';
    public static $isAdmin = false;

    public static $prefixes = [
        'inputs' => '/',
        'callbacks' => '##'
    ];

    public static function initialize()
    {
        self::set404Routes();

        $is_admin = false;
        $route = Bot::update()->getRoute();
        $type = $route['type'].'s';
        $key = strtolower(str_replace(self::$prefixes[$type],'',$route['route']));
        if (!array_key_exists($key,self::$$type))
            if (($key = self::$step) !== '' && !empty($key) && array_key_exists($key, self::$steps)) $type = 'steps';
            else $key = 'default.404.response';

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
    public static function any($key, \Closure|array|string $value)
    {
        $key = strtolower(str_replace(self::$prefixes['callbacks'],'',$key));
        self::$inputs[$key] = $value;
        self::$callbacks[$key] = $value;
    }

    public static function getStepFrom($stepFrom)
    {
        self::$step = $stepFrom;
    }

    public static function step($key, \Closure|array|string $value)
    {
        $key = strtolower(str_replace(self::$prefixes['callbacks'],'',$key));
        self::$steps[$key] = $value;
    }

    public static function admin(\Closure $routes)
    {
        self::$isAdmin = true;
        $routes();
        self::$isAdmin = false;
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

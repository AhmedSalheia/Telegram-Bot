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
        self::fallbackRoutes();

        $route = Bot::update()->getRoute();
        $type = $route['type'].'s';
        $key = strtolower(str_replace(self::$prefixes[$type],'',$route['route']));
        if (!array_key_exists($key,self::$$type))
            if (($key = self::$step) !== '' && !empty($key) && array_key_exists($key, self::$steps)) $type = 'steps';
            else $key = 'fallback.404';

        return self::return($key, $type);
    }
    private static function return($key, $type)
    {
        $ref = (self::$$type)[$key];
        if (!$ref['admin'] || (Bot::is_admin()))
        {
            $return = (new self())->getReference($ref['value']);
            if ($return instanceof Send)
                return $return->execute(false);
            else
                return $return;
        }
        return self::return('fallback.403', ($type === 'steps') ? 'inputs' : $type);
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
    public static function getStepFrom($stepFrom)
    {
        self::$step = $stepFrom;
    }
    private static function getKey($key, $prefixes, &$admin)
    {
        $key = strtolower(str_replace($prefixes,'',$key));
        $admin=str_starts_with($key, 'admin.');
        return  str_replace('admin.', '', $key);
    }
    public static function input($key, \Closure|array|string $value)
    {
        $key = self::getKey($key, self::$prefixes['inputs'], $admin);
        self::$inputs[$key]['value'] = $value;
        self::$inputs[$key]['admin'] = $admin;
    }
    public static function callback($key, \Closure|array|string $value)
    {
        $key = self::getKey($key, self::$prefixes['callbacks'], $admin);
        self::$callbacks[$key]['value'] = $value;
        self::$callbacks[$key]['admin'] = $admin;
    }
    public static function any($key, \Closure|array|string $value)
    {
        self::input($key, $value);
        self::callback($key, $value);
    }
    public static function step($key, \Closure|array|string $value)
    {
        $key = self::getKey($key, '', $admin);
        self::$steps[$key]['value'] = $value;
        self::$steps[$key]['admin'] = $admin;
    }

    private static function fallbackRoutes(){
        self::set404Routes();
    }
    private static function set404routes()
    {
        if (!array_key_exists('fallback.404', self::$inputs))
            self::input('fallback.404', function () {
                return Response::sendMessage()->text('Your Command Is Not Found');
            });
        if (!array_key_exists('fallback.404', self::$callbacks))
            self::callback('fallback.404', function () {
            return Response::sendMessage()->text('Your Command Is Not Found');
        });
    }
    private static function set403routes()
    {
        if (!array_key_exists('fallback.403', self::$inputs))
            self::input('fallback.403', function () {
                return Response::sendMessage()->text('You Don\'t Have Permission To Do So');
            });
        if (!array_key_exists('fallback.403', self::$callbacks))
            self::callback('fallback.403', function () {
                return Response::sendMessage()->text('You Don\'t Have Permission To Do So');
            });
    }
}

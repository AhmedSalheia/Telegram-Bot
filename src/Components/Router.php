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

    public static $updateLastMessage = null;

    public static function initialize($route=[])
    {
        self::fallbackRoutes();
        if (self::$updateLastMessage===null) self::$updateLastMessage = fn ($lastMessageId) => null;

        if ($route === []) $route = Bot::update()->getRoute();
        $type = $route['type'].'s';
        $params = explode(' ',strtolower(str_replace(self::$prefixes[$type],'',$route['route'])));
        $key = array_shift($params);
        if (!array_key_exists($key,self::$$type))
            if (($key = self::$step) !== '' && !empty($key) && array_key_exists($key, self::$steps)) $type = 'steps';
            else $key = 'fallback_404';

        return self::return($key, $type, array_merge($route['args'],$params));
    }
    private static function return($key, $type, $params=[])
    {
        $ref = (self::$$type)[$key];
        $paramValues = [];
        if ($ref['params'])
            foreach ($ref['params'] as $param=>$optional)
            {
                if ($v = array_shift($params))
                    $paramValues[$param] = $v;
                elseif (!$optional) return self::return('fallback_404', ($type === 'steps') ? 'inputs' : $type);
                else $paramValues[$param] = null;
            }

        if (!$ref['admin'] || (Bot::is_admin()))
        {
            $return = (new self())->getReference($ref['value'], $paramValues);
            if ($return instanceof Send)
                return $return->execute(false);
            else
                return $return;
        }
        return self::return('fallback_403', ($type === 'steps') ? 'inputs' : $type);
    }
    private function getReference(\Closure|array|string $value, $params)
    {
        if ($value instanceof \Closure) return $value(...$params);
        elseif(is_array($value)) return (new $value[0])->{$value[1]}(...$params);
        else {
            $value = explode('@',$value);
            return (new (self::$namespace . $value[0]))->{$value[1]}(...$params);
        }
    }
    public static function getStepFrom($stepFrom)
    {
        self::$step = $stepFrom;
    }
    private static function getKey($key, $prefixes, &$admin, &$params)
    {
        $key = strtolower(str_replace($prefixes,'',$key));
        preg_match_all('/::[a-zA-Z\d_\-]+\??::/',$key, $matches);

        foreach ($matches[0] as $el)
        {
            $e = trim($el, ':');
            $params[trim($e,'?')] = str_ends_with($e, '?');
        }
        $key = preg_replace('/::[a-zA-Z\d_\-]+\??::/','',$key);

        $admin=str_starts_with($key, 'admin.');
        return  str_replace('admin.', '', $key);
    }
    public static function input($key, \Closure|array|string $value)
    {
        $key = self::getKey($key, self::$prefixes['inputs'], $admin, $params);
        self::$inputs[$key]['value'] = $value;
        self::$inputs[$key]['admin'] = $admin;
        self::$inputs[$key]['params'] = $params;
    }
    public static function callback($key, \Closure|array|string $value)
    {
        $key = self::getKey($key, self::$prefixes['callbacks'], $admin, $params);
        self::$callbacks[$key]['value'] = $value;
        self::$callbacks[$key]['admin'] = $admin;
        self::$callbacks[$key]['params'] = $params;
    }
    public static function any($key, \Closure|array|string $value)
    {
        self::input($key, $value);
        self::callback($key, $value);
        self::step($key, $value);
    }
    public static function step($key, \Closure|array|string $value)
    {
        $key = self::getKey($key, '', $admin, $params);
        self::$steps[$key]['value'] = $value;
        self::$steps[$key]['admin'] = $admin;
        self::$steps[$key]['params'] = $params;
    }

    private static function fallbackRoutes(){
        self::set404Routes();
        self::set403Routes();
        self::channelFallbacks();
    }
    private static function set404routes()
    {
        if (!array_key_exists('fallback_404', self::$inputs))
            self::input('fallback_404', function () {
                return Response::sendMessage()->text('Your Command Is Not Found');
            });
        if (!array_key_exists('fallback_404', self::$callbacks))
            self::callback('fallback_404', function () {
            return Response::sendMessage()->text('Your Command Is Not Found');
        });
    }
    private static function set403routes()
    {
        if (!array_key_exists('fallback_403', self::$inputs))
            self::input('fallback_403', function () {
                return Response::sendMessage()->text('You Don\'t Have Permission To Do So');
            });
        if (!array_key_exists('fallback_403', self::$callbacks))
            self::callback('fallback_403', function () {
                return Response::sendMessage()->text('You Don\'t Have Permission To Do So');
            });
    }
    private static function channelFallbacks()
    {
        $message = "You Need To Follow This Channels First:\n";
        foreach (Bot::$channels as $username=>$id) $message .= "- @$username\n";

        if (!array_key_exists('fallback_required_channels', self::$inputs))
            self::input('fallback_required_channels', function () use ($message) {
                return Response::sendMessage()->text($message);
            });
        if (!array_key_exists('fallback_required_channels', self::$callbacks))
            self::callback('fallback_required_channels', function () use ($message) {
                return Response::sendMessage()->text($message);
            });
    }

    public static function respondWithRoute($route) {
        $route = explode('.', $route);
        return \TelegramBot\Components\Router::initialize(['type'=>$route[0],'route'=>$route[1], 'args'=>array_slice($route,2)]);
    }
}

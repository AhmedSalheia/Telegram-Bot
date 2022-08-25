<?php

namespace TelegramBot\Components;

use TelegramBot\Bot;

class Send
{
    private $method;
    private $chat_id='';
    private $message_id='';
    private $text='';
    private $parse_mode='html';
    private $disable_web_page_preview = true;
    private $reply_markup = [
        "inline_keyboard" => [],
    ];

    public function __construct($method,$args=[])
    {
        $this->method = $method;
        if (!empty($args))
        {
            foreach ($args as $arg=>$val)
                $this->$arg = $val;
        }
        if ($this->chat_id==='' && Bot::update()!==null) {
            $this->chat_id = Bot::update()->getChatId();
        }
        if ($this->message_id==='' && Bot::update()!==null) {
            $this->message_id = Bot::update()->getMessageId();
        }
    }

    public function chat($id)
    {
        $this->chat_id = $id;
        return $this;
    }
    public function text($text)
    {
        $this->text = $text;
        return $this;
    }
    public function parse_mode($mode)
    {
        $this->parse_mode = $mode;
        return $this;
    }
    public function reply_markup($key, $data)
    {
        $this->reply_markup[$key] = $data;
        return $this;
    }
    public function keyboard_btn_grid($grid)
    {
        $this->reply_markup['inline_keyboard'] = $grid;
        return $this;
    }
    private function keyboard_btn_grid_row_add($row, $index=null){
        if (!isset($row['if']) || $row['if']) {
            if (isset($row['if'])) unset($row['if']);
            if ($index!==null) $this->reply_markup['inline_keyboard'][$index][] = $row;
            else $this->reply_markup['inline_keyboard'][][] = $row;
        }
    }
    public function keyboard_btn_grid_row(...$rows)
    {
        foreach ($rows as $row)
            if (!isset($row[0]) || !is_array($row[0]))
                $this->keyboard_btn_grid_row_add($row);
            else {
                $index = count($this->reply_markup['inline_keyboard']);
                foreach ($row as $r)
                    $this->keyboard_btn_grid_row_add($r,$index);
            }
        return $this;
    }

    private function generateData()
    {
        if ($this->chat_id ==='') throw new \Exception('You Must Provide Chat Id First');
        if ($this->text ==='') throw new \Exception('You Must Provide Message Text First');

        return [
            "chat_id" => $this->chat_id,
            "message_id" => $this->message_id,
            "text" => $this->text,
            "parse_mode" => $this->parse_mode,
            'disable_web_page_preview' => $this->disable_web_page_preview,
            "reply_markup" => json_encode($this->reply_markup)
        ];
    }

    public function execute($return=true, $data=true)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot" . Bot::$TOKEN . "/" . $this->method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, (!$data)?:$this->generateData());
        $res = curl_exec($ch);
        if (curl_error($ch)) {
            var_dump(curl_error($ch));
        }
        $res = json_decode($res, false);

        if ($return==='all')
            return $res;
        elseif ($return)
            return $res->result;

        if (isset($res->result) && isset($res->result->message_id))
            (Router::$updateLastMessage)($res->result->message_id);

        return;
    }
}

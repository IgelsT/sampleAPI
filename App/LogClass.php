<?php

namespace App;

class LogClass
{

    public static function LogT($text, string $file_sufix = "")
    {
        $file = __DIR__ . "/../logs/varlog_" . date("Y.m.d_H.i.s.u") . $file_sufix . ".log";
        file_put_contents($file, $text);
    }

    public static function LogV($var, string $file_sufix = "")
    {
        $file = __DIR__ . "/../logs/varlog_" . date("Y.m.d_H.i.s.u") . $file_sufix . ".log";
        file_put_contents($file, print_r($var, true));
    }

    public static function pprint($value)
    {
        echo '<pre>' . print_r($value, true) . '</pre>';
    }

    public static function logToSTD($var)
    {
        fwrite(STDERR, print_r($var, TRUE));
    }
}

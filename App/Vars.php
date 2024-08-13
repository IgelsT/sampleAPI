<?php

namespace App;

use DTO\UserDTO;
use DTO\DeviceParams\DeviceDTO;

class Vars
{
    private $settings = null;
    private $request = null;
    private $user = null;
    private $device = null;

    protected static Vars $_instance;  //экземпляр объекта	

    private function __construct()
    {
    }

    public static function getInstance(): Vars
    { // получить экземпляр данного класса 
        if (!isset(self::$_instance)) { // если экземпляр данного класса  не создан
            self::$_instance = new self;  // создаем экземпляр данного класса 
        }
        return self::$_instance; // возвращаем экземпляр данного класса
    }

    public static function setSettings($settings)
    {
        $self = self::getInstance();
        $self->settings = $settings;
    }

    public static function setUser($user)
    {
        $self = self::getInstance();
        $self->user = $user;
    }

    public static function setDevice($device)
    {
        $self = self::getInstance();
        $self->device = $device;
    }

    public static function setRequest($request)
    {
        $self = self::getInstance();
        $self->request = $request;
    }

    public static function s()
    {
        return self::getInstance()->settings;
    }

    public static function u(): UserDTO
    {
        return self::getInstance()->user;
    }

    public static function d(): DeviceDTO
    {
        return self::getInstance()->device;
    }

    public static function getHash(string $string): string
    {
        $secret = self::getInstance()->settings;
        return hash_hmac('sha256', $string, $secret);
    }

    public static function req(): ApiRequest
    {
        return self::getInstance()->request;
    }

    private function __clone()
    {
    } //запрещаем клонирование объекта модификатором private
    public function __wakeup()
    {
    } //запрещаем клонирование объекта модификатором private

}

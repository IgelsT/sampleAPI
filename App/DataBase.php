<?php

namespace App;

use PDO;
use PDOException;
use App\ApiError;
use App\ERROR_CODES;
use PDOStatement;

class DataBase
{
    /** @var PDO */
    private $connlink = null;
    private $lastError = [];
    private $lastQuery = "";
    private $debugLevel = 0;
    private $lastResult = null;
    private $lastCountRows = 0;

    protected static DataBase $_instance;  //экземпляр объекта	

    private function __construct()
    {
    }

    public static function getInstance()
    { // получить экземпляр данного класса 
        if (!isset(self::$_instance)) { // если экземпляр данного класса  не создан
            self::$_instance = new self;  // создаем экземпляр данного класса 
        }
        return self::$_instance; // возвращаем экземпляр данного класса
    }

    public static function setConnection($host, $database, $user, $pass, $debugLevel = 0): bool
    { 
        return self::getInstance()->_setConnection($host, $database, $user, $pass, $debugLevel);
    }

    private function _setConnection($host, $database, $user, $pass, $debugLevel): bool
    {
        $this->debugLevel = $debugLevel;
        try {
            $this->connlink = new PDO(
                'mysql:host=' . $host . ';dbname=' . $database . ';charser=utf8mb4', $user, $pass,
                [
                    PDO::ATTR_EMULATE_PREPARES => true,
                    PDO::ATTR_STRINGIFY_FETCHES => false,
                    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ]
            );
            return true;
        } catch (PDOException $Exception) {
            return false;
        }
    }

    function __destruct() { }

    public static function querySelect($query, $params = [], $oneRow = false): array | bool
    {
        $self = self::getInstance();
        $result = $self->execQuerySafe($query, $params);
        $ret = false;
        if ($result) $ret = ($oneRow) ? $result->fetch(\PDO::FETCH_ASSOC) : $result->fetchAll(\PDO::FETCH_ASSOC);
        $result->closeCursor();
        return $ret;
    }

    public static function query($query, $params = []): PDOStatement | bool
    {
        $result = self::getInstance()->execQuerySafe($query, $params);
        $result->closeCursor();
        return $result;
    }

    public static function queryRaw($query): PDOStatement | bool
    {
        $result = self::getInstance()->execRaw($query);
        return $result;
    }

    public static function callPrc($prc, $params)
    {
        $query = 'call $prc($params)';
        $result = self::getInstance()->execRaw($query);
        $result->closeCursor();
        return $result;
    }

    public static function getLastID()
    {
        $self = self::getInstance();
        return intval($self->connlink->lastInsertId());
    }

    public static function getLastError()
    {
        return self::getInstance()->lastError;
    }

    public static function getLastResult()
    {
        return self::getInstance()->lastResult;
    }

    #region inner functions
    private function execQuery($query): PDOStatement | false
    {
        $this->lastResult = null;
        $this->lastQuery = $query;
        try {
            $this->lastResult = $this->connlink->query($query);
            $this->lastCountRows = $this->lastResult->rowCount();
            $this->checkError();
            return $this->lastResult;
        } catch (\Exception $e) {
            $this->throwError($e->getMessage());
        }
    }

    private function execQuerySafe($query, $params): PDOStatement | false
    {
        $this->lastQuery = $query;
        // echo $query; print_r($params);
        try {
            $stmt = $this->connlink->prepare($query);
            $result = $stmt->execute($params);
            $this->lastResult = ($result) ? $stmt : false;
            $this->lastCountRows = $stmt->rowCount();
            $this->checkError();
            return $this->lastResult;
        } catch (\Exception $e) {
            $this->throwError($e->getMessage());
        }
    }

    private function execRaw($query): PDOStatement | false
    {
        $this->lastQuery = $query;
        try {
            $result = $this->connlink->exec($query);
            $this->lastResult = ($result) ? true : false;
            $this->lastCountRows = $result;
            $this->checkError();
            return $this->lastResult;
        } catch (\Exception $e) {
            $this->throwError($e->getMessage());
        }
    }

    private function checkError()
    {
        $this->lastError = $this->connlink->errorInfo();
        if ($this->lastError && $this->lastError[0] != '00000') {
            $this->throwError($this->lastError[2]);
        }
    }

    private function throwError($message) {
        if($this->debugLevel == 0) $message = ERROR_CODES::$DB_REQUEST_ERROR;
        if($this->debugLevel == 2) $message.= "\r\n" . $this->cleanQuery($this->lastQuery);
        throw new ApiError(ERROR_CODES::$DB_REQUEST_ERROR, $message);
    }

    private function cleanQuery(string $query) {
        $query = str_replace("\r", "", $query);
        $query = str_replace("\n", "", $query);
        $query = preg_replace('/\s+/', ' ', $query);
        return $query;
    }

    #endregion
    private function __clone()
    {
    } //запрещаем клонирование объекта модификатором private
    public function __wakeup()
    {
    } //запрещаем клонирование объекта модификатором private
}

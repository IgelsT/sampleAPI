<?php

declare(strict_types=1);

namespace Models;

use App\ApiError;
use App\DataBase;
use App\ERROR_CODES;
use Exception;

class DBQuery
{
}

class BasicModel
{
    protected $_table = '';
    protected $_id = '';
    protected $_fields = [];

    private $_query_str = '';
    private $_FIELDS = '*';
    private $_TABLE = '';
    private $_WHERE = '';
    private $_ORDERBY = '';
    private $_LIMIT = [];
    private $_FIELDSSET = [];
    private $_query_params = [];
    private $_simple_query = false;

    function __construct()
    {
        if($this->_table == '' || $this->_id == '' || count($this->_fields) == 0) 
            throw new ApiError(ERROR_CODES::$CREATE_MODEL_ERRROR, $this::class);
        if (is_array($this->_fields) && count($this->_fields) > 0) {
            $fields = [];
            foreach ($this->_fields as $field) $fields[$field] = null;
            $this->_fields = $fields;
        }
    }

    private function buildSelect()
    {
        $this->_query_str = "SELECT " . $this->_FIELDS . " FROM ";
        $this->_query_str .= ($this->_TABLE == '') ? $this->_table : $this->_TABLE;
        if ($this->_WHERE != '') $this->_query_str .= " WHERE " . $this->_WHERE;
        if ($this->_ORDERBY != '') $this->_query_str .= " ORDER BY " . $this->_ORDERBY;
        if (isset($this->_LIMIT['limit_from']) && isset($this->_LIMIT['limit_to'])) {
            try {
                $from = intval($this->_LIMIT['limit_from']);
                $to = intval($this->_LIMIT['limit_to']);
                $this->_query_str .= " LIMIT $from,$to";
            } catch (Exception $e) {
            }
        }
        if($this->_table == $this->_TABLE || $this->_TABLE == '') $this->_simple_query = true;
    }

    private function buildDelete()
    {
        $table = ($this->_TABLE == '') ? $this->_table : $this->_TABLE;
        $where = $this->defaultWhere($this->_WHERE);
        $this->_query_str = "DELETE FROM $table WHERE $where";
    }

    private function buildUpdate()
    {
        $table = ($this->_TABLE == '') ? $this->_table : $this->_TABLE;
        $where = $this->defaultWhere($this->_WHERE);
        $setStr = '';
        $updateSet = (is_array($this->_FIELDSSET) && count($this->_FIELDSSET) > 0) ? $this->_FIELDSSET : $this->_fields;
       
        $setStr = implode(', ', array_map(function ($var, $key) {
            return "$key = :$key";
        }, $updateSet, array_keys($updateSet)));
        $this->_query_params = array_merge($this->_query_params, $updateSet);
        $this->_query_str = "UPDATE $table SET $setStr WHERE $where";
    }

    private function defaultWhere($where): string
    {
        if ($where == '' && isset($this->_fields[$this->_id])) {
            $where = $this->_id . ' = :' . $this->_id;
            $this->_query_params[$this->_id] = $this->_fields[$this->_id];
        }
        return $where;
    }

    private function buildInsert()
    {
        $table = ($this->_TABLE == '') ? $this->_table : $this->_TABLE;
        $insetrSet = (is_array($this->_FIELDSSET) && count($this->_FIELDSSET) > 0) ? $this->_FIELDSSET : $this->_fields;
        $fields = '';
        $values = '';
        foreach ($insetrSet as $key => $value) {
            if ($key != $this->_id) {
                $fields .= "$key, ";
                $values .= ":$key, ";
                $this->_query_params[$key] = $value;
            }
        }
        $fields = rtrim($fields, ', ');
        $values = rtrim($values, ', ');
        $this->_query_str = "INSERT INTO $table ($fields) VALUES($values)";
    }

    private function clearQuery()
    {
        $this->_query_str = '';
        $this->_FIELDS = '*';
        $this->_TABLE = '';
        $this->_ORDERBY = '';
        $this->_WHERE = '';
        $this->_LIMIT = [];
        $this->_FIELDSSET = [];
        $this->_query_params = [];
        $this->_simple_query = false;
    }

    protected function loadData($vars) {
        $varType = gettype($vars);
        if($varType == 'array') {
            foreach($vars as $key=>$value) {
                if(array_key_exists($key,$this->_fields)) $this->_fields[$key] = $value;
            }
        }
        elseif($varType == 'object') {
            $seflFields = get_object_vars($vars);
            foreach($seflFields as $key=>$value) {
                if(array_key_exists($key,$this->_fields)) $this->_fields[$key] = $value;
            }
        }
    }
    
    protected function fields($str): BasicModel
    {
        $this->_FIELDS = $str;
        return $this;
    }

    protected function fieldsSet(array $params): BasicModel
    {
        $this->_FIELDSSET = $params;
        return $this;
    }

    protected function from(string $str): BasicModel
    {
        $this->_TABLE = $str;
        return $this;
    }

    protected function where(string $str, array $params): BasicModel
    {
        $this->_WHERE = $str;
        $this->_query_params = array_merge($this->_query_params, $params);
        return $this;
    }

    protected function orderBy(string $str): BasicModel
    {
        $this->_ORDERBY = $str;
        return $this;
    }

    protected function limit(int $from, int $to): BasicModel
    {
        $this->_LIMIT = ['limit_from' => $from, 'limit_to' => $to];
        return $this;
    }

    protected function setQuery(string $str, array $params): BasicModel
    {
        $this->_query_str = $str;
        $this->_query_params = array_merge($this->_query_params, $params);
        return $this;
    }

    protected function getOne(): array | bool
    {
        if ($this->_query_str == '') $this->buildSelect();
        $result = DataBase::querySelect($this->_query_str, $this->_query_params, true);
        if($this->_simple_query) $this->loadData($result);
        $this->clearQuery();
        return $result;
    }

    protected function getAll(): array
    {
        if ($this->_query_str == '') $this->buildSelect();
        $result = DataBase::querySelect($this->_query_str, $this->_query_params);
        $this->clearQuery();
        return $result;
    }

    protected function delete()
    {
        $this->buildDelete();
        $result = DataBase::query($this->_query_str, $this->_query_params);
        $this->clearQuery();
        return $result;
    }

    protected function update()
    {
        $this->buildUpdate();
        $result = DataBase::query($this->_query_str, $this->_query_params);
        $this->clearQuery();
        return $result;
    }

    protected function insert()
    {
        $this->buildInsert();
        $result = DataBase::query($this->_query_str, $this->_query_params);
        $this->clearQuery();
        return $result;
    }

    protected function callPrc(string $prcName, array $params)
    {
        $newParams = [];
        foreach( $params as $key => $value ) {
            $newParams['p_' . $key] = $value;
        }
        $query = "CALL $prcName(" .  implode(',', array_map(function ($var, $key) {
            return ":$key";
        }, $newParams, array_keys($newParams))) . ")";

        return DataBase::query($query, $newParams);
    }

    protected function upsert($data = null) {
        if($data != null) $this->loadData($data);
        if(isset($this->_fields[$this->_id]) && $this->_fields[$this->_id] > 0) {
            $this->update();
        }
        else {
            $this->insert();
        }
    }

    protected function getLastID() : int {
        return DataBase::getLastID();
    }

    protected function query(string $str, array $params)
    {
        return DataBase::query($str, $params);
    }

    protected function queryRaw(string $str)
    {
        return DataBase::queryRaw($str);
    }
}

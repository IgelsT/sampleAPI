<?php

namespace App;

class ApiResponse
{
    private $result = 'error';
    private $action = '';
    private $code = 200;
    private ?array $data;
    private ?object $error;

    public function __construct() {}

    public function setOK(array $data)
    {
        $this->result = 'ok';
        $this->code = 200;
        $this->addDataArray($data);
    }

    public function setError(ApiError $error, array $data)
    {
        $this->result = 'error';
        $this->code = $error->httpCode;
        $this->error = $error;
    }

    function setResult(bool $result)
    {
        $this->result = ($result) ? 'ok' : 'error';
    }

    function setCode(int $code)
    {
        $this->code = $code;
    }

    function getCode()
    {
        return $this->code;
    }

    function setAction(string $action = '')
    {
        $this->action = $action;
    }

    function getAction()
    {
        return $this->action;
    }

    function addData(string $key, $value)
    {
        $this->data[$key] = $value;
    }

    function addDataArray(array $value)
    {
        if (count($value) > 0) {
            foreach ($value as $k => $v) {
                $this->data[$k] = $v;
            }
        }
    }

    function removeData(string $key)
    {
        unset($this->data[$key]);
    }

    public function toJSON()
    {
        return json_encode(get_object_vars($this));
    }
}

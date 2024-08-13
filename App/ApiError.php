<?php

namespace App;

// HTTP_ERROR_CODES
// [200, 'OK']
// [204, 'No Content']
// [400, 'Bad Request']
// [401, 'Unauthorized']
// [403, 'Forbidden']
// [404, 'Not Found']
// [500, 'Internal Server Error']

class ERROR_CODES
{
    static $JSON_DECODE_ERROR = ['JSON_DECODE_ERROR', 'json decode error', 400];
    static $WRONG_REQUEST = ['WRONG_REQUEST', 'wrong request', 400];
    static $INTERNAL_ERROR = ['INTERNAL_ERROR', 'internal error', 500];
    static $NO_RETURN_DATA = ['NO_RETURN_DATA', 'no return data', 500];
    static $WRONG_RETURN_DATA = ['WRONG_RETURN_DATA', 'wrong return data, must be array', 500];
    static $CREATE_MODEL_ERRROR = ['CREATE_MODEL_ERRROR', 'empty required fields', 500];
    static $DB_CONNECTION_ERROR = ['DB_CONNECTION_ERROR', 'DB connection error', 500];
    static $DB_REQUEST_ERROR = ['DB_REQUEST_ERROR', 'DB request error', 500];
    static $BAD_TOKEN = ['BAD_TOKEN', 'bad token', 401];
    static $WRONG_PASSWORD = ['WRONG_PASSWORD', 'Неправильный email/пароль!', 200];
}

class ApiError extends \Exception
{
    var $message;
    var $code;
    var $httpCode;
    var $reason;

    public function __construct(array $error, $reason = '')
    {
        $this->code = $error[0];
        $this->message = $error[1];
        $this->httpCode = isset($error[2]) ? $error[2] : 200;
        $this->reason = $reason;
        parent::__construct($error[1], 0, null);
    }

    public static function fromCODE(array $params, $reason = '')
    {
        return new self($params, $reason);
    }
}

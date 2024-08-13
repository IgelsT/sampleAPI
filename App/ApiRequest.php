<?php

namespace App;

use App\ApiError;
use App\LogClass;

class ApiRequest
{
    var $headers = [];
    var $uri = "";
    var $requestMethod = "";
    var $get = [];
    var $post = [];
    var $content = "";
    var $controller = "/default";
    var $action = "";
    var $data = [];

    public function __construct(array $headers, string $uri, string $method, array $get, array $post, string $content, string $apiPath)
    {
        $this->headers = $headers;
        $this->uri = $uri;
        $this->requestMethod = $method;
        $this->get = $get;
        $this->post = $post;
        $this->content = $content;

        if($this->requestMethod == 'OPTIONS') return;

        $uri = preg_split("/\/\?|\?/", $uri)[0];
        $uri = str_replace($apiPath, '', $uri ?? '/default');

        $this->controller = $uri;

        $data = null;
        if (str_contains($this->headers['Content-Type'], 'application/json')) {
            $data = json_decode($content, true);
        }

        if (str_contains($this->headers['Content-Type'], 'multipart/form-data')) {
            // LogClass::LogV($this->post);
            if (isset($this->post['data'])) $data = json_decode($this->post['data'], true);
        }
        
        if (!is_array($data)) {
            // LogClass::LogV($this);
            throw new ApiError(ERROR_CODES::$JSON_DECODE_ERROR);
        }

        if (isset($data['action']) && $data['action'] != "") {
            $this->action = $data['action'];
        }
        if (isset($data['data'])) {
            $this->data = $data['data'];
        }
    }

    public function getParam(string $name, string $def_val = '') {
        return (isset($this->data[$name])) ? $this->data[$name] : $def_val;
    }

    public function checkParams(array $fields_array) {
        $ret = [];
        foreach ($fields_array as $field) {
            $value = $this->checkParam($field);
            $ret[$field] = $value;
        }
        return $ret;
    }

    public function checkParam(string $field, string $erroMessage = ''): mixed {
        $message = ($erroMessage == '') ? "no " . $field : $erroMessage;
        if (!isset($this->data[$field]))
            throw new ApiError(ERROR_CODES::$ERROR_REQUEST_PARAMS, $message);
        return $this->data[$field];
    }
}

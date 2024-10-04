<?php

namespace App;

require_once __DIR__ . '/settings.php';

use Controllers\AuthController;

class ApiRoute
{
    var $route;
    var $method;
    var $auth;
    var $action;

    public function __construct($route, array $method, $auth = true)
    {
        $this->route = $route;
        $this->method = isset($method[0]) ? $method[0] : "";
        $this->auth = $auth;
        $this->action = isset($method[1]) ? $method[1] : "index";
    }
}

class MainApp
{
    /** @var ApiRoute[] */
    private $routes;

    /** @var ApiRoute */
    private $currentRoute;

    private $settings;

    /** @var ApiRequest */
    private $request;

    /** @var ApiResponse */
    private $response;

    private $lastError = '';

    public function GlobalErrorHandler($errno, $errstr, $errfile, $errline)
    {
        throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    public function GlobalExceptionHandler(\Throwable $exception)
    {
        $errorStr = "Stack trace:\n";
        $errorStr .= $this->formatTrace($exception->getTrace());

        if ($exception instanceof ApiError) $errorStr .= "Message:\n" . $exception->reason;
        else $errorStr .= "Message:\n" . $exception->getMessage();

        $this->lastError = $errorStr;

        if ($exception instanceof ApiError) $this->sendError($exception);
        $this->sendError(new ApiError(ERROR_CODES::$INTERNAL_ERROR, $exception->getMessage()));
    }

    private function formatTrace(array $trace)
    {
        $str = "";
        foreach ($trace as $key => $row) {
            $str .= sprintf(
                "#%s %s(%s): %s%s%s()\n",
                $key,
                $row['file'],
                $row['line'],
                $row['class'],
                $row['type'],
                $row['function']
            );
        }
        return $str;
    }

    public function __construct()
    {
        set_error_handler(array($this, 'GlobalErrorHandler'));
        set_exception_handler(array($this, 'GlobalExceptionHandler'));
        global $settings;
        $this->settings = $settings;
        $this->response = new ApiResponse();
        Vars::setSettings($settings);
        $this->request = new ApiRequest(
            getallheaders(),
            $_SERVER['REQUEST_URI'],
            $_SERVER['REQUEST_METHOD'],
            $_GET,
            $_POST,
            file_get_contents("php://input"),
            $this->settings['apiPath']
        );
        Vars::setRequest($this->request);
    }

    public function addRoute($route, array $method, $auth = true)
    {
        $this->routes[] = new ApiRoute($route, $method, $auth);
    }

    private function beforeRun()
    {
        // fix CORS
        if ($this->request->requestMethod == 'OPTIONS') {
            $this->response->setCode(200);
            $this->sendResponse();
        }

        if (!DataBase::getInstance()->setConnection(
            $this->settings['DB']['dbhost'],
            $this->settings['DB']['dbbase'],
            $this->settings['DB']['dbuser'],
            $this->settings['DB']['dbpass'],
            $this->settings['DB']['dblevel']
        )) {
            throw (new ApiError(ERROR_CODES::$DB_CONNECTION_ERROR));
            exit();
        }
    }

    private function beforeRoute()
    {
        // Check route for auth.
        if ($this->currentRoute->auth) {
            $authController = new AuthController();
            $authorization = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : false;
            $authController->checkAuthorization($authorization);
        }
    }

    private function beforeSend()
    {
        if ($this->response->getCode() == 500) error_log($this->lastError);
    }

    public function run()
    {
        $this->beforeRun();

        $this->currentRoute = $this->findRoute($this->request->controller);

        $this->beforeRoute();

        if ($this->request->action == "") $this->request->action = $this->currentRoute->action;

        $this->response->setAction($this->request->action);

        $method = $this->chechMethodExist($this->currentRoute->method, $this->request->action);
        $result = $method();
        if ($result === NULL) throw (new ApiError(ERROR_CODES::$NO_RETURN_DATA));
        if (\gettype($result) == "array") $this->sendOK($result);
        else if (\gettype($result) == "string") $this->sendRaw($result);
        else throw (new ApiError(ERROR_CODES::$WRONG_RETURN_DATA, "must be array or string"));
    }

    private function findRoute(string $path): ApiRoute
    {
        foreach ($this->routes as $route) {
            if ($route->route == $path) {
                return $route;
            }
        }
        throw (new ApiError(ERROR_CODES::$WRONG_REQUEST));
    }

    private function chechMethodExist(string $controller, string $method)
    {
        if (!class_exists($controller) || !method_exists($controller, $method)) {
            throw (new ApiError(ERROR_CODES::$WRONG_REQUEST));
        }
        $object = new $controller();
        return array($object, $method);
    }

    #region Response functions

    private function setHeaders()
    {
        // header("Vary: Origin");
        header("Access-Control-Allow-Origin: *");
        header('Access-Control-Allow-Headers: X-Requested-With, Content-Type, Accept, Origin, Authorization');
        header('Access-Control-Expose-Headers: Content-Disposition');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
        header("Expires: 0");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: o-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0");
        header("Pragma: no-cache");
    }

    private function sendResponse()
    {
        $this->beforeSend();

        $this->setHeaders();
        header("HTTP/1.0 " . $this->response->getCode());
        http_response_code($this->response->getCode());
        header("Content-Type: application/json");
        echo $this->response->toJSON();
        exit();
    }

    private function sendRaw(string $content)
    {
        $this->beforeSend();

        $this->setHeaders();
        echo $content;
    }

    public function sendFile(string $file_path, string $file_name, $file_type = 'application/octet-stream')
    {
        $this->setHeaders();
        header('X-Accel-Buffering: no');
        // header('Content-Encoding: none;');
        header('Content-Description: File Transfer');
        header('Content-Type: ' . $file_type);
        header('Content-Disposition: attachment; filename=' . $file_name);
        header('Content-Length: ' . filesize($file_path . $file_name));
        header("Content-Transfer-Encoding: binary");

        ini_set('output_buffering', 0);
        ini_set('zlib.output_compression', 0);
        @ob_end_flush();
        @flush();
        readfile($file_path . $file_name);
        exit();
    }

    public function sendOK(array $data = [])
    {
        $this->response->setOK($data);
        $this->sendResponse();
    }

    public function sendError(apiError $error, array $data = [])
    {
        $this->response->setError($error, $data);
        $this->sendResponse();
    }
    #endregion
}

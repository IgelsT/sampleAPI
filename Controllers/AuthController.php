<?php

declare(strict_types=1);

namespace Controllers;

use App\ApiError;
use App\ERROR_CODES;
use App\Mailer;
use App\Vars;
use DTO\UserDTO;
use Models\DeviceModel;
use Models\UserModel;

class AuthController
{
    function getHash($string)
    {
        $secret = Vars::s()['auth']['secretPhase'];
        $sig = hash_hmac('sha256', $string, $secret);
        return $sig;
    }

    public function login()
    {
        $data = Vars::req()->data;
        if (!isset($data['username']) || !isset($data['pass']))
            throw new ApiError(ERROR_CODES::$WRONG_PASSWORD);

        $userid = $data['username'];
        $pass = $data['pass'];
        $userModel = new UserModel();
        $user = $userModel->userByNamePasswd($userid, $pass);
        if (!$user)
            throw new ApiError(ERROR_CODES::$WRONG_PASSWORD);

        $user_name = $user->user_name;
        $user_pass = $user->user_password;
        if ($user_pass == $pass) {
            $first_path = base64_encode(json_encode(['user' => $user_name]));
            $second_path = $this->getHash(json_encode(['user' => $user_name, 'pass' => $user_pass]));
            return ['hash' => $first_path . '.' . $second_path, 'user' => $user];
        }
        throw new ApiError(ERROR_CODES::$WRONG_PASSWORD);
    }

    public function checkAuthorization($authorization)
    {
        if (!is_string($authorization)) goto badtoken;
        $auth_paths = explode('.', $authorization);
        if (!isset($auth_paths[0]) || !isset($auth_paths[1])) goto badtoken;
        try {
            $first_path = json_decode(base64_decode($auth_paths[0]));
            if ($first_path == '') goto badtoken;
        } catch (\Exception $e) {
            goto badtoken;
        }

        if (!isset($first_path->user)) goto badtoken;

        $userModel = new UserModel();
        $user = $userModel->userByName($first_path->user);
        if (!$user) goto badtoken;

        Vars::setUser($user);
        $calc_hash = $this->getHash(json_encode(['user' => $user->user_name, 'pass' => $user->user_password]));
        $second_path = $auth_paths[1];
        if ($calc_hash == $second_path) return true;
        
        goto badtoken;
        badtoken:
            throw(new ApiError(ERROR_CODES::$BAD_TOKEN));
    }
}

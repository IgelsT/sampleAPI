<?php

declare(strict_types=1);

namespace Models;

use DTO\UserDTO;
use Models\BasicModel;

class UserModel extends BasicModel
{
	protected $_table = 'users';
	protected $_id = 'user_id';
	protected $_fields = ['user_id', 'user_name', 'user_description', 'user_password', 'user_email'
		, 'user_hash', 'user_confirm'];

	public function userByName($name): UserDTO | bool
	{
		$user = $this->where('user_name=:name', ['name' => $name])->getOne();
		if (!$user) return false;
		return new UserDTO($user);
	}

	public function userByNamePasswd($name, $passwd): UserDTO | bool
	{
		$user = $this->where('user_name=:name AND user_password=:passwd', ['name' => $name, 'passwd' => $passwd])->getOne();
		if (!$user) return false;
		return new UserDTO($user);
	}

	public function saveUser(UserDTO $user) {
		$this->upsert($user);
	}
}

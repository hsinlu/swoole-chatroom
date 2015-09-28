<?php

namespace App\Repositories;

use \swoole_table;

class UserRepository
{
	protected $table;

	public function __construct()
	{
		$this->table = new swoole_table(1024);
		$this->table->column('fd', swoole_table::TYPE_INT, 4);
		$this->table->column('username', swoole_table::TYPE_STRING, 64);
		$this->table->column('channel', swoole_table::TYPE_STRING, 30);
		$this->table->column('is_online', swoole_table::TYPE_INT, 4);
		$this->table->create();
	}

	/**
	 * get all users, include offline
	 * 
	 * @return [type] [description]
	 */
	public function all()
	{
		$users = [];
		foreach ($this->table as $key => $user) {
			$users[] = $user;
		}
		return $users;
	}

	public function online()
	{
		$users = [];
		foreach ($this->table as $key => $user) {
			if ($user['is_online'] == 1) {
				$users[] = $user;
			}
		}
		return $users;
	}

	public function offline()
	{
		$users = [];
		foreach ($this->table as $key => $user) {
			if ($user['is_online'] == 0) {
				$users[] = $user;
			}
		}
		return $users;
	}

	public function getByUsername($username)
	{
		foreach ($this->table as $key => $user) {
			if ($user['username'] == $username) {
				return $user;
			}
		}

		return null;
	}

	public function getByFd($fd)
	{
		return $this->table->get($fd);
	}

	/**
	 * login to server
	 * 
	 * @param  [type] $fd       [description]
	 * @param  [type] $username [description]
	 * @return [type]           [description]
	 */
	public function login($fd, $username)
	{
		$user = $this->getByUsername($username);
		if (! is_null($user)) {
			$this->table->del($user['fd']);
		}

		$this->table->set($fd, [
			'fd' => $fd,
			'username' => $username,
			'channel' => 'public',
			'is_online' => 1
		]);

		return $this;
	}

	/**
	 * move to specify channel
	 * 
	 * @param  [type] $fd      [description]
	 * @param  [type] $channel [description]
	 * @return [type]          [description]
	 */
	public function moveToChannel($fd, $channel)
	{
		$user = $this->table->get($fd);
		if ($user) {
			$user['channel'] = $channel;

			$this->table->set($fd, $user);
		}

		return $this;
	}

	/**
	 * move to public channel
	 * 
	 * @param  [type] $fd [description]
	 * @return [type]     [description]
	 */
	public function movdToPublicChannel($fd)
	{
		return $this->moveToChannel($fd, 'public');
	}

	public function exist($fd)
	{
		return $this->table->exist($fd);
	}

	public function logout($fd)
	{
		$user = $this->table->get($fd);
		if ($user) {
			$user['is_online'] = 0;

			$this->table->set($fd, $user);
		}

		return $user;
	}
}
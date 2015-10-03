<?php

namespace App\Repositories;

use \swoole_table;

class MessageRepository
{
	protected $table;

	public function __construct()
	{
		$this->table = new swoole_table(1024);
		$this->table->column('fd', swoole_table::TYPE_INT, 4);
		$this->table->column('from_fd', swoole_table::TYPE_INT, 4);
		$this->table->column('content', swoole_table::TYPE_STRING, 4000);
		$this->table->column('channel', swoole_table::TYPE_STRING, 30);
		$this->table->column('time', swoole_table::TYPE_STRING, 30);
		$this->table->column('is_readed', swoole_table::TYPE_INT, 4);
		$this->table->create();
	}

	public function create($attributes)
	{
		if (is_array($attributes)) {
			$this->table->set(md5(time()), $attributes);
		}
	}

	public function where($where)
	{
		$messages = [];

		foreach ($this->table as $key => $message) {
			// match message
			foreach ($where as $column => $value) {
				if ($message[$column] != $value) {
					continue;
				}
			}

			$messages[] = $message;
		}

		return $messages;
	}

	public function orWhere($where)
	{
		$messages = [];

		foreach ($this->table as $key => $message) {
			// match message
			foreach ($where as $column => $value) {
				if ($message[$column] == $value) {
					$messages[] = $message;
				}
			}
		}

		return $messages;
	}
}
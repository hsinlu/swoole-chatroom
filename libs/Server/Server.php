<?php

namespace Libs\Server;

use Libs\App;

abstract class Server
{
	protected $app;
	protected $config;

	/**
	 * create swoole server
	 * @param Libs\App $app [description]
	 */
	public function __construct(App $app)
	{
		$this->app = $app;
	}

	/**
	 * set swoole server config
	 * @param  array $config server config
	 */
	public function configure($config)
	{
		$this->config = $config;

		return $this;
	}

	protected function parseData($data)
	{
		$message = json_decode($data);

		if (!is_array($message) || count($message) == 0 || !is_string($message[0])) {
			return [ 'unkown', [] ];
		}

		if (count($message) == 1) {
			return [ $message[0], [] ];
		}

		return [ $message[0], $message[1] ];
	}

	abstract function listen($port);
}
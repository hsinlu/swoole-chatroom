<?php

namespace App;

use \swoole_server;

class SwooleServer
{
	private $app;
	private $server;
	private $config;

	/**
	 * create swoole server
	 * @param App $app [description]
	 */
	public function __construct($app)
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

	/**
	 * start server 
	 * @param  int $port server port
	 */
	public function listen($port)
	{
		$this->server = new swoole_server("0.0.0.0", $port, SWOOLE_BASE, SWOOLE_SOCK_TCP);
		$this->server->set($this->config);

		$this->server->on('Connect', function ($server, $fd, $from_id) {
			$this->app->emit('connect', compact('server', 'fd', 'from_id'));
		});

		$this->server->on('Receive', function ($server, $fd, $from_id, $data) {
			echo "Receive: {$data}\n";

			$data = json_decode($data);
		
			if (!is_null($data)) {
				$this->app->emit($data->type, compact('server', 'fd', 'from_id', 'data'));	
			}
		});

		$this->server->on('Close', function ($server, $fd, $from_id) {
			$this->app->emit('close', compact('server', 'fd', 'from_id'));
		});

		$this->server->start();
	}
}
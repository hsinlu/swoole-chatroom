<?php

namespace Libs\Server;

use \swoole_server;

class TcpServer extends Server
{
	/**
	 * start server 
	 * @param  int $port server port
	 */
	public function listen($port)
	{
		$server = new swoole_server("0.0.0.0", $port, SWOOLE_BASE, SWOOLE_SOCK_TCP);
		$server->set($this->config);

		$server->on('Connect', function ($server, $fd, $from_id) {
			$this->app->emit('connect', compact('server', 'fd', 'from_id'));
		});

		$server->on('Receive', function ($server, $fd, $from_id, $data) {
			echo "Receive: {$data}\n";

			list($type, $message) = $this->parseData($data);
		
			if (!is_null($message)) {
				$this->app->emit($type, compact('server', 'fd', 'from_id', 'message'));	
			}
		});

		$server->on('Close', function ($server, $fd, $from_id) {
			$this->app->emit('close', compact('server', 'fd', 'from_id'));
		});

		$server->start();
	}
}
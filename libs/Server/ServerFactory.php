<?php

namespace Libs\Server;

class ServerFactory
{
	public static function createTcpServer($app)
	{
		return new TcpServer($app);
	}

	public static function createWebSocket($app)
	{
		return new WebSocket($app);
	}
}
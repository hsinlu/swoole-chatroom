<?php

namespace App\Middlewares;

class Auth
{
	public $app;

	public function __construct($app)
	{
		$this->app = $app;
	}

	public function handle($context, $next)
	{
		extract($context);

		if (!$this->app->users->exist($fd)) {
			return $server->send($fd, "请输入用户名和密码。");	
		}

		return $next($context);
	}
}
<?php

function auth()
{
	global $app;

	return function ($context, $next) use ($app) {
		extract($context);

		if (!$app->users->exist($fd)) {
			return $server->send($fd, "请输入用户名和密码。");	
		}

		return $next($context);
	};
}
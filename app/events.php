<?php

$app->on('connect', function ($context) use ($app) {
	// extract($context);
});

$app->on('login', function ($context) use ($app) {
	extract($context);

	if ($message->username == $message->password) {
		$app->users->login($fd, $message->username);

		// 返回登录成功消息
		reply($server, $fd, 'login', [ 'success' => true ]);

		// 广播加入聊天室通知
		broadcast($server, 'join', [
			'fd' => $fd,
			'username' => $message->username
		], $fd);
	} else {
		reply($server, $fd, 'login', [ 'errors' => [ '用户名和密码不正确。' ] ]);
	}
});

$app->on('close', function ($context) use ($app) {
	extract($context);

	$user = $app->users->logout($fd);
	if ($user) {
		// 广播离开聊天室通知
		broadcast($server, 'leave', [
			'fd' => $fd,
			'username' => $user['username'],
		], $fd);
	}
});

$app->on('list', [
	App\Middlewares\Auth::class, 
	function ($context) use ($app) {
		extract($context);

		reply($server, $fd, 'list', $app->users->all());
	}
]);

$app->on('chat', [
	App\Middlewares\Auth::class, 
	function ($context) use ($app) {
		extract($context);
	
		if (property_exists($message, 'to_fd')) {
			// 私人聊天
			whisper($server, $message->content, $fd, $message->to_fd);
		} else if (property_exists($message, 'to_channel')) {
			// 频道聊天
			mass($server, $message->content, $fd, $message->to_channel);
		} else {
			// 公共聊天
			mass($server, $message->content, $fd);
		}

		reply($server, $fd, 'chat', ['success' => true, 'id' => $message->id ]);
	}
]);

$app->on('messages', [
	App\Middlewares\Auth::class, 
	function ($context) use ($app) {
		extract($context);
	
		reply($server, $fd, 'messages', 
			$app->messages->orWhere([
				'fd' => $fd,
				'from_fd' => $fd
			])
		);
	}
]);
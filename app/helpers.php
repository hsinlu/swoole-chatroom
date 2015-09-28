<?php

function sendMessage($server, $type, $message, $from_fd, $to_fd)
{
	$user = $app->users->getByFd($from_fd);

	// 记录消息
	$app->messages->create([
		'fd' => $to_fd,
		'from_fd' => $from_fd,
		'message' => $message,
		'channel' => 'whisper',
		'time' => time(),
		'is_readed' => 0
	]);

	$server->send($to_fd, json_encode(
		array_merge([
			'type' => $type,
			'from_fd' => $from_fd,
			'from_username' => $user['username'],
		], $message)
	));
}

/**
 * 私聊
 * 
 * @param  swoole_server  $server  
 * @param  array|mixed    $message 消息
 * @param  int            $from_fd 发送人
 * @param  int            $to_fd   接收人
 */
function whisper($server, $message, $from_fd, $to_fd)
{
	global $app;

	sendMessage($server, 'chat', $message, $from_fd, $to_fd);
}

/**
 * 群聊
 * 
 * @param  swoole_server $server  
 * @param  array|mixed   $message 消息
 * @param  int           $from_fd 发送人
 * @param  string        $channel 频道，如果没有指定，默认为公共频道
 */
function mass($server, $message, $from_fd, $channel = 'public')
{
	global $app;

	foreach ($app->users->online() as $user) {
		$to_fd = $user['fd'];

		if ($from_fd === $to_fd) {
			continue;
		}

		if (is_null($channel) || $user['channel'] !== $channel) {
			continue;
		}

		sendMessage($server, 'chat', $message, $from_fd, $to_fd);
	}
}

function reply($server, $to_fd, $type, $segments)
{
	global $app;

	$server->send($to_fd, json_encode(
		array_merge([
			'type' => $type,
		], $segments)
	));
}

function broadcast($server, $type, $segments, $except_fd, $channel = 'public')
{
	global $app;

	foreach ($app->users->online() as $user) {
		$to_fd = $user['fd'];

		if (is_array($except_fd) && in_array($to_fd, $except_fd) 
			|| $except_fd === $to_fd) {
			continue;
		}

		if (is_null($channel) || $user['channel'] !== $channel) {
			continue;
		}

		$server->send($to_fd, json_encode(
			array_merge([
				'type' => $type
			], $segments)
		));
	}
}
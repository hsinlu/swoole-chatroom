<?php

function sendMessage($server, $type, $content, $from_fd, $to_fd)
{
	global $app;

	$user = $app->users->getByFd($from_fd);

	// 记录消息
	$app->messages->create([
		'fd' => $to_fd,
		'from_fd' => $from_fd,
		'content' => $content,
		'channel' => 'whisper',
		'time' => time(),
		'is_readed' => 0
	]);

	$server->send($to_fd, json_encode([
		$type,
		[
			'from_fd' => $from_fd,
			'from_username' => $user['username'],
			'content' => $content
		]
	]));
}

/**
 * 私聊
 * 
 * @param  swoole_server  $server  
 * @param  array|mixed    $content 消息内容
 * @param  int            $from_fd 发送人
 * @param  int            $to_fd   接收人
 */
function whisper($server, $content, $from_fd, $to_fd)
{
	sendMessage($server, 'chat', $content, $from_fd, $to_fd);
}

/**
 * 群聊
 * 
 * @param  swoole_server $server  
 * @param  array|mixed   $content 消息内容
 * @param  int           $from_fd 发送人
 * @param  string        $channel 频道，如果没有指定，默认为公共频道
 */
function mass($server, $content, $from_fd, $channel = 'public')
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

		sendMessage($server, 'chat', $content, $from_fd, $to_fd);
	}
}

function reply($server, $to_fd, $type, $message)
{
	$server->send($to_fd, json_encode([ $type, $message ]));
}

function broadcast($server, $type, $message, $except_fd, $channel = 'public')
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

		reply($server, $to_fd, $type, $message);
	}
}
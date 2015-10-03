<?php

// php client.php -u hsinlu -p hsinlu

use \swoole_client;

$readonly = false;

for ($i=1; $i < $argc; $i++) { 
	if ($argv[$i] === '-u') {
		$i++;
		$username = $argv[$i];
	}

	if ($argv[$i] === '-p') {
		$i++;
		$password = $argv[$i];
	}

	if ($argv[$i] === '-r') {
		$readonly = true;
	}
}

$client = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);

$client->on("connect", function(swoole_client $cli) use($username, $password) {
	$cli->send(json_encode([
		'login',
		[
			'username' => $username,
			'password' => $password
		]
	]));
});

$client->on("receive", function(swoole_client $cli, $data) use ($readonly) {
	echo "Receive: {$data}\n";

	list($type, $message) = json_decode($data);

	if ($type == 'login') {
		if (property_exists($message, 'success')) {
			echo "您已登录成功，目前支持“list、chat:fd={fd}/channel={channel}/空:{message}、messages”指令\n";
		} else if (property_exists($message, 'errors')) {
			$errors = implode('、', $message->errors);
			echo "登录失败：{$errors}\n";
		}
	} else if ($type === 'leave') {
		echo "{$message->username}退出了聊天室。\n";
	} else if ($type === 'list') {
		$onlinec = $offlinec = 0;
		foreach ($message as $user) {
			if ($user->is_online == 1) {
				$onlinec++;
			} else {
				$offlinec++;
			}
		}

		echo "当前在线人数：{$onlinec}，离线人数：{$offlinec}\n";

		echo json_encode($message);
		echo "\n";
	} else if ($type === 'chat') {
		if (!property_exists($message, 'success')) {
			echo "收到了来自{$message->from_username}的消息：{$message->content}\n";
		} else {
			echo "消息发送成功。\n";
		}
	} else if ($type === 'messages') {
		echo json_encode($message);
		echo "\n";
	} else if ($type === 'join') {
		echo "{$message->username}加入了聊天室，打个招呼吧。\n";
	}
	echo "---------------------------------------------------------\n\n";

	if (!$readonly) {
		// read command
		$stdin = fopen('php://stdin', 'r');
		$line = trim(fgets($stdin));

		if ($line === 'list') {
			$cli->send(json_encode([ 'list' ]));
		} else if ($line === 'messages') {
			$cli->send(json_encode([ 'messages' ]));
		} else if (strpos($line, 'chat:') !== false) {
			$paras = explode(':', $line);

			$message = $paras[2];

			if (!empty($paras[1])) {
				list($type, $val) = explode('=', $paras[1]);

				if ($type === 'fd') {
					$cli->send(json_encode([
						'chat',
						[
							'to_fd' => $val,
							'content' => $message,
							'id' => md5(time()),
						]
					]));
				} else if ($type === 'channel') {
					$cli->send(json_encode([
						'chat',
						[
							'to_channel' => $val,
							'content' => $message,
							'id' => md5(time()),
						]
					]));
				}
			} else {
				$cli->send(json_encode([
					'chat',
					[
						'content' => $message,
						'id' => md5(time()),
					]
				]));
			}
		} else if ($line === 'exit') {
			$cli->close();
			exit;
		}
	}
});

$client->on("error", function(swoole_client $cli){
    echo "error\n";
});

$client->on("close", function(swoole_client $cli){
    echo "Connection close\n";
});

$client->connect('127.0.0.1', 9501);
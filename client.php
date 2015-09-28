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
		'type' => 'login',
		'username' => $username,
		'password' => $password
	]));
});

$client->on("receive", function(swoole_client $cli, $data) use ($readonly) {
	echo "Receive: {$data}\n";

	$data = json_decode($data);

	if ($data->type == 'login') {
		if (property_exists($data, 'success')) {
			echo "您已登录成功，目前支持“list、chat:fd={fd}/channel={channel}/空:{message}、messages”指令\n";
		} else if (property_exists($data, 'errors')) {
			$errors = implode('、', $data->errors);
			echo "登录失败：{$errors}\n";
		}
	} else if ($data->type === 'leave') {
		echo "{$data->username}退出了聊天室。\n";
	} else if ($data->type === 'list') {
		$onlinec = $offlinec = 0;
		foreach ($data->users as $user) {
			if ($user->is_online == 1) {
				$onlinec++;
			} else {
				$offlinec++;
			}
		}

		echo "当前在线人数：{$onlinec}，离线人数：{$offlinec}\n";

		echo json_encode($data->users);
		echo "\n";
	} else if ($data->type === 'chat') {
		if (!property_exists($data, 'success')) {
			echo "收到了来自{$data->from_username}的消息：{$data->message}\n";
		} else {
			echo "消息发送成功。\n";
		}
	} else if ($data->type === 'messages') {
		echo json_encode($data->messages);
		echo "\n";
	} else if ($data->type === 'join') {
		echo "{$data->username}加入了聊天室，打个招呼吧。\n";
	}
	echo "---------------------------------------------------------\n\n";

	if (!$readonly) {
		// read command
		$stdin = fopen('php://stdin', 'r');
		$line = trim(fgets($stdin));

		if ($line === 'list') {
			$cli->send(json_encode([
				'type' => 'list'
			]));
		} else if ($line === 'messages') {
			$cli->send(json_encode([
				'type' => 'messages'
			]));
		} else if (strpos($line, 'chat:') !== false) {
			$paras = explode(':', $line);

			$message = $paras[2];

			if (!empty($paras[1])) {
				list($type, $val) = explode('=', $paras[1]);

				if ($type === 'fd') {
					$cli->send(json_encode([
						'type' => 'chat',
						'to_fd' => $val,
						'message' => $message,
						'id' => md5(time()),
					]));
				} else if ($type === 'channel') {
					$cli->send(json_encode([
						'type' => 'chat',
						'to_channel' => $val,
						'message' => $message,
						'id' => md5(time()),
					]));
				}
			} else {
				$cli->send(json_encode([
					'type' => 'chat',
					'message' => $message,
					'id' => md5(time()),
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
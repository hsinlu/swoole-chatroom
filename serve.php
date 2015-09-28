<?php

require __DIR__ . '/vendor/autoload.php';

$app = new App\App;

$app->bind('users', new App\Repositories\UserRepository);
$app->bind('messages', new App\Repositories\MessageRepository);

require __DIR__ . '/app/events.php';

$server = new App\SwooleServer($app);

$server
	->configure([
		'worker_num' => 4,
		'daemonize' => in_array('-d', $argv)
	])
	->listen(9501);

echo "server running...\n";


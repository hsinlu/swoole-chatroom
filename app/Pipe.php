<?php

namespace App;

use \Closure;

class Pipe
{
	private $app;

	private $context;

	private $pipes = [];

	public function __construct($app)
	{
		$this->app = $app;
	}

	public function context($context)
	{
		$this->context = $context;
		
		return $this;
	}

	public function through($pipes)
	{
		$this->pipes = is_array($pipes) ? $pipes : func_get_args();

		return $this;
	}

	public function then(Closure $destination)
	{
		$exp = array_reduce($this->pipes, function ($stack, $middleware) {
			return function ($context) use ($stack, $middleware) {
				return call_user_func($middleware, $context, $stack);
			};
		}, $destination);

		call_user_func($exp, $this->context);
	}
}
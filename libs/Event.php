<?php

namespace Libs;

use Closure;

trait Event
{
	/**
	 * all events
	 * @var array
	 */
	private $events = [];

	private $middlewares = [];

	/**
	 * register event handler
	 * 
	 * @param  string $event   event name
	 * @param  mixed  $handler event handler
	 */
	public function on($event, $handler)
	{
		if (is_array($handler)) {
			$handler = array_reverse($handler);

			$strategy = array_shift($handler);

			$this->middlewares[$event] = $handler;
			$this->events[$event] = $strategy;
		} else {
			$this->events[$event] = $handler;
		}
	}

	/**
	 * emit event
	 * 
	 * @param  string $event   event name
	 * @param  array  $context event context
	 */
	public function emit($event, $context)
	{
		if (!isset($this->events[$event])) {
			return;
		}

		(new Pipe($this))
			->context($context)
			->through(isset($this->middlewares[$event]) ? $this->middlewares[$event] : [])
			->then($this->events[$event]);
	}
}
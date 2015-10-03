<?php

namespace Libs;

trait Container
{
	/**
	 * container
	 * @var array
	 */
	private $container = [];

	/**
	 * resolve object for container
	 * @var array
	 */
	private $resolvers = [];

	/**
	 * bind a resolver for container
	 * 
	 * @param  string $name     name
	 * @param  mixed  $resolver resolver
	 */
	public function bind($name, $resolver)
	{
		if (is_object($resolver)) {
			$this->container[$name] = $resolver;
		} else {
			$this->resolvers[$name] = $resolver;
		}
	}

	public function resolve($name)
	{
		if (isset($this->container[$name])) {
			return $this->container[$name];
		}

		if (isset($this->resolvers[$name])) {
			$resolver = $this->resolvers[$name];

			if ($resolver instanceof Closure) {
				$obj = $resolver();
			} else {
				$obj = new $resolver;
			}

			$this->container[$name] = $obj;

			return $obj;
		}

		return null;
	}

	/**
	 * get object from container
	 * 
	 * @param  string $name object name in container
	 * @return object       
	 */
	public function __get($name)
	{
		return $this->resolve($name);
	}	
}
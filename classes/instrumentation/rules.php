<?php

namespace mageekguy\atoum\instrumentation;

class rules implements \iteratorAggregate, \arrayAccess
{
	protected $rules = array();

	public function add($context, $rule)
	{
		$this->rules[$context][] = $rule;

		return $this;
	}

	public function merge(rules $rules)
	{
		$this->rules = array_merge($this->rules, $rules->rules);

		return $this;
	}

	public function getIterator()
	{
		return new \arrayIterator($this->rules);
	}

	public function offsetExists($offset)
	{
		return isset($this->rules[$offset]);
	}

	public function offsetGet($offset)
	{
		return $this->rules[$offset];
	}

	public function offsetSet($offset, $value)
	{
		$this->rules[$offset] = $value;
	}

	public function offsetUnset($offset)
	{
		unset($this->rules[$offset]);
	}
}

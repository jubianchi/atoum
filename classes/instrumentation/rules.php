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
		foreach ($rules->rules as $context => $list)
		{
			foreach ($list as $rule)
			{
				$this->add($context, $rule);
			}
		}

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

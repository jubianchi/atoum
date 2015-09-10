<?php

namespace mageekguy\atoum\test\dataProviders;

use
	mageekguy\atoum\exceptions\logic,
	mageekguy\atoum\mock\generator,
	mageekguy\atoum\test\dataProvider,
	mageekguy\atoum\exceptions\runtime
;

class object implements dataProvider
{
	protected $class;

	public function __invoke()
	{
		return $this->generate();
	}

	public function __toString()
	{
		return __CLASS__ . '<' . $this->class . '>';
	}

	function __sleep()
	{
		return array('class');
	}

	public function getClass()
	{
		return $this->class;
	}

	public function setClass($class)
	{
		if (static::classExists($class) === false)
		{
			throw new logic\invalidArgument('Argument must be a class name');
		}

		$this->class = $class;

		return $this;
	}

	public function generate()
	{
		if (static::canInstanciateClass($this->classIsSet()->class)) {
			$className = $this->class;

			return new $className();
		}

		throw new runtime('Could not instanciate an object from ' . $this->class);
	}

	protected function classIsSet()
	{
		if ($this->class === null)
		{
			throw new logic('Class is undefined');
		}

		return $this;
	}

	protected static function canInstanciateClass($class)
	{
		$reflection = new \reflectionClass($class);

		if ($reflection->hasMethod('__construct') === false)
		{
			return true;
		}

		$constructor = $reflection->getMethod('__construct');

		if ($constructor->isPublic() === false)
		{
			throw new dataProvider\object\exceptions\privateConstructor('Could not instanciate an object from ' . $class . ' because ' . $class . '::__construct() is private');
		}

		foreach ($constructor->getParameters() as $parameter)
		{
			if ($parameter->isOptional() === false)
			{
				throw new dataProvider\object\exceptions\mandatoryArgument('Could not instanciate an object from ' . $class . ' because ' . $class . '::__construct() has at least one mandatory argument');
			}
		}

		return true;
	}

	protected static function classExists($class)
	{
		return class_exists($class);
	}
}

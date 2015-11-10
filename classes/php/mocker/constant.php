<?php

namespace mageekguy\atoum\php\mocker;

use mageekguy\atoum;
use mageekguy\atoum\php\mocker;

class constant extends mocker
{
	public function __get($constantName)
	{
		return constant($this->getDefaultNamespace() . $constantName);
	}

	public function __set($constantName, $value)
	{
		define($this->getDefaultNamespace() . $constantName, $value);

		return $this;
	}

	public function __isset($constantName)
	{
		return defined($this->getDefaultNamespace() . $constantName);
	}

	public function __unset($constantName)
	{
		return;
	}

	function addToTest(atoum\test $test)
	{
		$test->setPhpConstantMocker($this);

		return $this;
	}
}

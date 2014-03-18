<?php

namespace mageekguy\atoum\instrumentation\score;

use
	mageekguy\atoum,
	mageekguy\atoum\score,
	mageekguy\atoum\exceptions
;

class coverage extends score\coverage
{
	public function addDataForClass($class, array $data)
	{
		try
		{
			$reflectedClass = call_user_func($this->reflectionClassFactory, $class);

			if ($this->isExcluded($reflectedClass) === false)
			{
				$reflectedClassName = $reflectedClass->getName();

				if (isset($this->classes[$reflectedClassName]) === false)
				{
					$this->classes[$reflectedClassName] = $reflectedClass->getFileName();
					$this->methods[$reflectedClassName] = array();

					foreach ($reflectedClass->getMethods() as $method)
					{
						if ($method->isAbstract() === false)
						{
							$declaringClass = $this->getDeclaringClass($method);

							if ($this->isExcluded($declaringClass) === false)
							{
								$declaringClassName = $declaringClass->getName();
								$declaringClassFile = $declaringClass->getFilename();

								if (isset($this->classes[$declaringClassName]) === false)
								{
									$this->classes[$declaringClassName] = $declaringClassFile;
									$this->methods[$declaringClassName] = array();
								}

								$id = $declaringClassName . '::' . $method->getName();

								if (isset($data[$id]) === true)
								{
									foreach ($data[$id] as $index => $bucket)
									{
										$this->methods[$declaringClassName][$method->getName()][$index] = $bucket[atoum\instrumentation\coverage::BUCKET_VALUE];
									}
								}
							}
						}
					}
				}
			}
		}
		catch (\exception $exception) {}

		return $this;
	}

	public function merge(score\coverage $coverage)
	{
		$classes = $coverage->getClasses();
		$methods = $coverage->getMethods();

		foreach ($methods as $class => $declaredMethods)
		{
			$reflectedClass = call_user_func($this->reflectionClassFactory, $class);

			if (isset($this->classes[$class]) === false)
			{
				if ($this->isExcluded($reflectedClass) === false)
				{
					$this->classes[$class] = $classes[$class];
				}
			}

			foreach ($declaredMethods as $method => $data)
			{
				if ($this->isExcluded($this->getDeclaringClass($reflectedClass->getMethod($method))) === false)
				{
					if (isset($this->methods[$class][$method]) === false)
					{
						$this->methods[$class][$method] = array();
					}

					foreach ($data as $index => $covered)
					{
						$this->methods[$class][$method][$index] = (isset($this->methods[$class][$method][$index]) ? $this->methods[$class][$method][$index] : false) || $covered;
					}
				}
			}
		}

		return $this;
	}

	public function getValue()
	{
		$value = null;

		if (($total = sizeof($this->methods)) > 0)
		{
			$coverage = 0;

			foreach (array_keys($this->methods) as $class)
			{
				$coverage += $this->getValueForClass($class);
			}

			$value = (float) $coverage / $total;
		}

		return $value;
	}

	public function getValueForClass($class)
	{
		$value = null;

		if (isset($this->methods[$class]) === true && ($total = sizeof($this->methods[$class])) > 0)
		{
			$coverage = 0;

			foreach (array_keys($this->methods[$class]) as $method)
			{
				$coverage += $this->getValueForMethod($class, $method);
			}

			$value = (float) $coverage / $total;
		}

		return $value;
	}

	public function getNumberOfCoverableLinesInClass($class)
	{
		return 0;
	}

	public function getNumberOfCoveredLinesInClass($class)
	{
		return 0;
	}

	public function getValueForMethod($class, $method)
	{
		$value = null;

		if (isset($this->methods[$class][$method]) === true && ($total = sizeof($this->methods[$class][$method])) > 0)
		{
			$coverage = 0;

			foreach($this->methods[$class][$method] as $covered)
			{
				$coverage += (int) $covered;
			}

			$value = (float) $coverage / $total;
		}

		return $value;
	}
}

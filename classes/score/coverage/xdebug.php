<?php

namespace mageekguy\atoum\score\coverage;

use
	mageekguy\atoum,
	mageekguy\atoum\score,
	mageekguy\atoum\exceptions
;

class xdebug extends score\coverage
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

								if (isset($data[$declaringClassFile]) === true)
								{
									for ($line = $method->getStartLine(), $endLine = $method->getEndLine(); $line <= $endLine; $line++)
									{
										if (isset($data[$declaringClassFile][$line]) === true && (isset($this->methods[$declaringClassName][$method->getName()][$line]) === false || $this->methods[$declaringClassName][$method->getName()][$line] < $data[$declaringClassFile][$line]))
										{
											$this->methods[$declaringClassName][$method->getName()][$line] = $data[$declaringClassFile][$line];
										}
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

			foreach ($declaredMethods as $method => $lines)
			{
				if (isset($this->methods[$class][$method]) === true || $this->isExcluded($this->getDeclaringClass($reflectedClass->getMethod($method))) === false)
				{
					foreach ($lines as $line => $call)
					{
						if (isset($this->methods[$class][$method][$line]) === false || $this->methods[$class][$method][$line] < $call)
						{
							$this->methods[$class][$method][$line] = $call;
						}
					}
				}
			}
		}

		return $this;
	}

	public function getValue()
	{
		$value = null;

		if (sizeof($this) > 0)
		{
			$totalLines = 0;
			$coveredLines = 0;

			foreach ($this->methods as $methods)
			{
				foreach ($methods as $lines)
				{
					foreach ($lines as $call)
					{
						if ($call >= -1)
						{
							$totalLines++;
						}

						if ($call === 1)
						{
							$coveredLines++;
						}
					}
				}
			}

			if ($totalLines > 0)
			{
				$value = (float) $coveredLines / $totalLines;
			}
		}

		return $value;
	}

	public function getValueForClass($class)
	{
		$value = null;

		if (isset($this->methods[$class]) === true)
		{
			$totalLines = 0;
			$coveredLines = 0;

			foreach ($this->methods[$class] as $lines)
			{
				foreach ($lines as $call)
				{
					if ($call >= -1)
					{
						$totalLines++;
					}

					if ($call === 1)
					{
						$coveredLines++;
					}
				}
			}

			if ($totalLines > 0)
			{
				$value = (float) $coveredLines / $totalLines;
			}
		}

		return $value;
	}

	public function getNumberOfCoverableLinesInClass($class)
	{
		$coverableLines = 0;

		$class = (string) $class;

		if (isset($this->methods[$class]) === true && $this->isInExcludedClasses($class) === false)
		{
			foreach ($this->methods[$class] as $lines)
			{
				foreach ($lines as $call)
				{
					if ($call >= -1)
					{
						$coverableLines++;
					}
				}
			}
		}

		return $coverableLines;
	}

	public function getNumberOfCoveredLinesInClass($class)
	{
		$coveredLines = 0;

		$class = (string) $class;

		if (isset($this->methods[$class]) === true && $this->isInExcludedClasses($class) === false)
		{
			foreach ($this->methods[$class] as $lines)
			{
				foreach ($lines as $call)
				{
					if ($call === 1)
					{
						$coveredLines++;
					}
				}
			}
		}

		return $coveredLines;
	}

	public function getValueForMethod($class, $method)
	{
		$value = null;

		if (isset($this->methods[$class][$method]) === true)
		{
			$totalLines = 0;
			$coveredLines = 0;

			foreach ($this->methods[$class][$method] as $call)
			{
				if ($call >= -1)
				{
					$totalLines++;
				}

				if ($call === 1)
				{
					$coveredLines++;
				}
			}

			if ($totalLines > 0)
			{
				$value = (float) $coveredLines / $totalLines;
			}
		}

		return $value;
	}
}

<?php

namespace mageekguy\atoum\score;

use
	mageekguy\atoum,
	mageekguy\atoum\score,
	mageekguy\atoum\exceptions
;

abstract class coverage implements \countable, \serializable
{
	protected $adapter = null;
	protected $reflectionClassFactory = null;
	protected $classes = array();
	protected $methods = array();
	protected $excludedClasses = array();
	protected $excludedNamespaces = array();
	protected $excludedDirectories = array();

	public function __construct(atoum\adapter $adapter = null, \closure $reflectionClassFactory = null)
	{
		$this
			->setAdapter($adapter)
			->setReflectionClassFactory($reflectionClassFactory)
		;
	}

	public function setAdapter(atoum\adapter $adapter = null)
	{
		$this->adapter = $adapter ?: new atoum\adapter();

		return $this;
	}

	public function getAdapter()
	{
		return $this->adapter;
	}

	public function setReflectionClassFactory(\closure $factory = null)
	{
		$this->reflectionClassFactory = $factory ?: function($class) { return new \reflectionClass($class); };

		return $this;
	}

	public function getReflectionClassFactory()
	{
		return $this->reflectionClassFactory;
	}

	public function serialize()
	{
		return serialize(array(
				$this->classes,
				$this->methods,
				$this->excludedClasses,
				$this->excludedNamespaces,
				$this->excludedDirectories
			)
		);
	}

	public function unserialize($string, \closure $reflectionClassFactory = null)
	{
		$this->setReflectionClassFactory($reflectionClassFactory);

		list(
			$this->classes,
			$this->methods,
			$this->excludedClasses,
			$this->excludedNamespaces,
			$this->excludedDirectories
		) = unserialize($string);

		return $this;
	}

	public function getClasses()
	{
		return $this->classes;
	}

	public function getMethods()
	{
		return $this->methods;
	}

	public function reset()
	{
		$this->classes = array();
		$this->methods = array();

		return $this;
	}

	public function resetExcludedClasses()
	{
		$this->excludedClasses = array();

		return $this;
	}

	public function resetExcludedNamespaces()
	{
		$this->excludedNamespaces = array();

		return $this;
	}

	public function resetExcludedDirectories()
	{
		$this->excludedDirectories = array();

		return $this;
	}

	public function addDataForTest(atoum\test $test, array $data)
	{
		return $this->addDataForClass($test->getTestedClassName(), $data);
	}

	abstract public function merge(score\coverage $coverage);

	abstract public function addDataForClass($class, array $data);

	abstract public function getValue();

	abstract public function getValueForClass($class);

	abstract public function getValueForMethod($class, $method);

	abstract public function getNumberOfCoverableLinesInClass($class);

	abstract public function getNumberOfCoveredLinesInClass($class);

	public function getCoverageForClass($class)
	{
		$coverage = array();

		$class = (string) $class;

		if (isset($this->methods[$class]) === true && $this->isInExcludedClasses($class) === false)
		{
			$coverage = $this->methods[$class];
		}

		return $coverage;
	}

	public function getCoverageForMethod($class, $method)
	{
		$class = $this->getCoverageForClass($class);

		return (isset($class[$method]) === false ? array() : $class[$method]);
	}

	public function excludeClass($class)
	{
		$class = (string) $class;

		if (in_array($class, $this->excludedClasses) === false)
		{
			$this->excludedClasses[] = $class;
		}

		return $this;
	}

	public function getExcludedClasses()
	{
		return $this->excludedClasses;
	}

	public function excludeNamespace($namespace)
	{
		$namespace = trim((string) $namespace, '\\');

		if (in_array($namespace, $this->excludedNamespaces) === false)
		{
			$this->excludedNamespaces[] = $namespace;
		}

		return $this;
	}

	public function getExcludedNamespaces()
	{
		return $this->excludedNamespaces;
	}

	public function excludeDirectory($directory)
	{
		$directory = rtrim((string) $directory, DIRECTORY_SEPARATOR);

		if (in_array($directory, $this->excludedDirectories) === false)
		{
			$this->excludedDirectories[] = $directory;
		}

		return $this;
	}

	public function getExcludedDirectories()
	{
		return $this->excludedDirectories;
	}

	public function count()
	{
		return sizeof($this->methods);
	}

	public function isInExcludedClasses($class)
	{
		return (in_array($class, $this->excludedClasses) === true);
	}

	public function isInExcludedNamespaces($class)
	{
		return self::itemIsExcluded($this->excludedNamespaces, $class, '\\');
	}

	public function isInExcludedDirectories($file)
	{
		return self::itemIsExcluded($this->excludedDirectories, $file, DIRECTORY_SEPARATOR);
	}

	protected function isExcluded(\reflectionClass $class)
	{
		$className = $class->getName();

		if ($this->isInExcludedClasses($className) === true || $this->isInExcludedNamespaces($className) === true)
		{
			return true;
		}
		else
		{
			$fileName = $class->getFileName();

			return ($fileName === false || $this->isInExcludedDirectories($fileName) === true);
		}
	}

	protected function getDeclaringClass(\reflectionMethod $method)
	{
		$declaringClass = $method->getDeclaringClass();

		$traits = ($this->adapter->method_exists($declaringClass, 'getTraits') === false ? array() : $declaringClass->getTraits());

		if (sizeof($traits) > 0)
		{
			$methodFileName = $method->getFileName();

			if ($methodFileName !== $declaringClass->getFileName() || $method->getStartLine() < $declaringClass->getStartLine() || $method->getEndLine() > $declaringClass->getEndLine())
			{
				if (sizeof($traits) > 0)
				{
					$methodName = $method->getName();

					foreach ($traits as $trait)
					{
						if ($methodFileName === $trait->getFileName() && $trait->hasMethod($methodName) === true)
						{
							return $trait;
						}
					}
				}
			}
		}

		return $declaringClass;
	}

	protected static function itemIsExcluded(array $excludedItems, $item, $delimiter)
	{
		foreach ($excludedItems as $excludedItem)
		{
			$excludedItem .= $delimiter;

			if (substr($item, 0, strlen($excludedItem)) === $excludedItem)
			{
				return true;
			}
		}

		return false;
	}
}

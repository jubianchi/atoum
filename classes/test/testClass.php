<?php

namespace mageekguy\atoum\test;

use
	mageekguy\atoum,
	mageekguy\atoum\test,
	mageekguy\atoum\annotations
;

class testClass implements \iterator, \countable
{
	const enginesNamespace = '\mageekguy\atoum\test\engines';
	const defaultEngine = 'concurrent';

	protected $class;
	protected $phpVersions = array();
	protected $engineClass;
	protected $engine;
	protected $tags = array();
	protected $ignored = false;
	protected $void = false;
	protected $mandatoryExtensions = array();
	protected $testMethods = array();
	protected $methodsFilter;
	protected $methods = array();
	protected $position = 0;

	public function __construct(\reflectionClass $class)
	{
		$this->class = $class;

		$this->setEngineClass();
	}

	public function __toString()
	{
		return $this->getName();
	}

	public function setMethodFilter(\closure $filter)
	{
		$this->methodsFilter = $filter;

		return $this;
	}

	public function getName()
	{
		return $this->class->getName();
	}

	public function ignore($ignore = true)
	{
		$this->ignored = $ignore;

		return $this;
	}

	public function isIgnored()
	{
		return $this->ignored;
	}

	public function void($void = true)
	{
		$this->void = $void;

		return $this;
	}

	public function isVoid()
	{
		return $this->void;
	}

	public function addPhpVersion($version, $operator = null)
	{
		$this->phpVersions[$version] = $operator ?: '>=';

		return $this;
	}

	public function getPhpVersions()
	{
		return $this->phpVersions;
	}

	public function addMandatoryExtension($extension)
	{
		$this->mandatoryExtensions[] = $extension;

		return $this;
	}

	public function getMandatoryExtensions()
	{
		return $this->mandatoryExtensions;
	}

	public function setEngineClass($engine = null)
	{
		$engine = $engine ?: self::defaultEngine;

		if (substr($engine, 0, 1) !== '\\')
		{
			$engine = self::enginesNamespace . '\\' . $engine;
		}

		$this->engine = null;
		$this->engineClass = $engine;

		return $this;
	}

	public function getEngineClass()
	{
		return $this->engineClass;
	}

	public function setTags(array $tags)
	{
		$this->tags = $tags;

		return $this;
	}

	public function getTags()
	{
		return $this->tags;
	}

	public function getAllTags()
	{
		$tags = $this->getTags();

		foreach ($this as $method)
		{
			$tags = array_merge($tags, $method->getTags());
		}

		return array_unique($tags);
	}

	public function hasTags(array $tags)
	{
		return sizeof(array_intersect($tags, $this->tags)) > 0;
	}

	public function current()
	{
		return current($this->methods);
	}

	public function next()
	{
		next($this->methods);
		$this->position++;

		return $this;
	}

	public function key()
	{
		return current($this->methods)->getName();
	}

	public function valid()
	{
		return $this->position < count($this->methods);
	}

	public function rewind()
	{
		$this->position = 0;
		$this->methods = $this->class->getMethods(\reflectionMethod::IS_PUBLIC);

		if ($this->methodsFilter !== null)
		{
			$filter = $this->methodsFilter;

			$this->methods = array_filter($this->methods, function(\reflectionMethod $method) use ($filter) {
				return $filter($method->getName());
			});
		}

		$class = $this;

		$this->methods = array_map(
			function(\reflectionMethod $method) use ($class) {
				return $class->fetchMethod($method->getName());
			},
			$this->methods
		);

		$this->methods = array_filter($this->methods, function(method $method) {
			return $method->isIgnored() === false;
		});

		return $this;
	}

	public function count()
	{
		return count($this->rewind()->methods);
	}

	public function setDataProvider(test\method $method, $dataProvider = null)
	{
		if ($dataProvider === null)
		{
			$dataProvider = $method->getName() . 'DataProvider';
		}

		try
		{
			$method->setDataProvider(new \reflectionMethod($this->class->getName(), $dataProvider));
		}
		catch(\reflectionException $exception)
		{
			throw new atoum\exceptions\logic\invalidArgument('Data provider ' . $this->class->getName() . '::' . lcfirst($dataProvider) . '() is unknown');
		}


		return $this;
	}

	public function getMethod($methodName)
	{
		return $this->checkMethod($methodName)->testMethods[$methodName];
	}

	public function getMethodTags($methodName)
	{
		$methodTags = $this->getMethod($methodName)->getTags();
		$tags = sizeof($methodTags) === 0 ? $this->getTags() : $methodTags;

		return $tags;
	}

	private function checkMethod($methodName)
	{
		try
		{
			$this->testMethods[$methodName] = $this->fetchMethod($methodName);
		}
		catch (\reflectionException $exception)
		{
			throw new atoum\exceptions\logic\invalidArgument('Test method ' . $this->class->getName() . '::' . $methodName . '() does not exist');
		}

		return $this;
	}

	private function fetchMethod($methodName)
	{
		if (isset($this->testMethods[$methodName]) === false)
		{
			$this->testMethods[$methodName] = new method($this->class->getMethod($methodName));
			$this->testMethods[$methodName]->extractAnnotation();

			if ($this->testMethods[$methodName]->needsDataProvider())
			{
				$this->setDataProvider($this->testMethods[$methodName]);
			}
		}

		return $this->testMethods[$methodName];
	}
} 

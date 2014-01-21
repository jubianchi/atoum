<?php

namespace mageekguy\atoum\tests\units\score;

use
	mageekguy\atoum,
	mageekguy\atoum\mock,
	mageekguy\atoum\score,
	mock\mageekguy\atoum\score\coverage as testedClass
;

require_once __DIR__ . '/../../runner.php';

class coverage extends atoum\test
{
	public function testClass()
	{
		$this->testedClass
			->hasInterface('countable')
			->hasInterface('serializable')
		;
	}

	public function test__construct()
	{
		$this
			->if($coverage = new testedClass())
			->then
				->variable($coverage->getValue())->isNull()
				->array($coverage->getMethods())->isEmpty()
				->object($coverage->getAdapter())->isEqualTo(new atoum\adapter())
				->object($defaultReflectionClassFactory = $coverage->getReflectionClassFactory())->isInstanceOf('closure')
				->object($defaultReflectionClassFactory($this))->isEqualTo(new \reflectionClass($this))
			->if($coverage = new testedClass($adapter = new atoum\adapter(), $reflectionClassFactory = function() {}))
			->then
				->variable($coverage->getValue())->isNull()
				->array($coverage->getMethods())->isEmpty()
				->object($coverage->getAdapter())->isIdenticalTo($adapter)
				->object($coverage->getReflectionClassFactory())->isIdenticalTo($reflectionClassFactory)
		;
	}

	public function testSetAdapter()
	{
		$this
			->if($coverage = new testedClass())
			->then
				->object($coverage->setAdapter($adapter = new atoum\adapter()))->isIdenticalTo($coverage)
				->object($coverage->getAdapter())->isIdenticalTo($adapter)
				->object($coverage->setAdapter())->isIdenticalTo($coverage)
				->object($coverage->getAdapter())
					->isInstanceOf('mageekguy\atoum\adapter')
					->isNotIdenticalTo($adapter)
		;
	}

	public function testSetReflectionClassFactory()
	{
		$this
			->if($coverage = new testedClass())
			->then
				->object($coverage->setReflectionClassFactory($reflectionClassFactory = function() {}))->isIdenticalTo($coverage)
				->object($coverage->getReflectionClassFactory())->isIdenticalTo($reflectionClassFactory)
				->object($coverage->setReflectionClassFactory())->isIdenticalTo($coverage)
				->object($defaultReflectionClassFactory = $coverage->getReflectionClassFactory())
					->isInstanceOf('closure')
					->isNotIdenticalTo($reflectionClassFactory)
				->object($defaultReflectionClassFactory($this))->isEqualTo(new \reflectionClass($this))
		;
	}

	public function testReset()
	{
		$this
			->if($coverage = new testedClass())
			->then
				->array($coverage->getClasses())->isEmpty()
				->array($coverage->getMethods())->isEmpty()
				->array($coverage->getExcludedClasses())->isEmpty()
				->array($coverage->getExcludedNamespaces())->isEmpty()
				->array($coverage->getExcludedDirectories())->isEmpty()
				->object($coverage->reset())->isIdenticalTo($coverage)
				->array($coverage->getClasses())->isEmpty()
				->array($coverage->getMethods())->isEmpty()
				->array($coverage->getExcludedClasses())->isEmpty()
				->array($coverage->getExcludedNamespaces())->isEmpty()
				->array($coverage->getExcludedDirectories())->isEmpty()
		;
	}

	public function testResetExcludedClasses()
	{
		$this
			->if($coverage = new testedClass())
			->then
				->object($coverage->resetExcludedClasses())->isIdenticalTo($coverage)
				->array($coverage->getExcludedClasses())->isEmpty()
			->if($coverage->excludeClass(uniqid()))
			->then
				->object($coverage->resetExcludedClasses())->isIdenticalTo($coverage)
				->array($coverage->getExcludedClasses())->isEmpty()
		;
	}

	public function testResetExcludedNamespaces()
	{
		$this
			->if($coverage = new testedClass())
			->then
				->object($coverage->resetExcludedNamespaces())->isIdenticalTo($coverage)
				->array($coverage->getExcludedNamespaces())->isEmpty()
			->if($coverage->excludeNamespace(uniqid()))
			->then
				->object($coverage->resetExcludedNamespaces())->isIdenticalTo($coverage)
				->array($coverage->getExcludedNamespaces())->isEmpty()
		;
	}

	public function testResetExcludedDirectories()
	{
		$this
			->if($coverage = new testedClass())
			->then
				->object($coverage->resetExcludedDirectories())->isIdenticalTo($coverage)
				->array($coverage->getExcludedDirectories())->isEmpty()
			->if($coverage->excludeDirectory(uniqid()))
			->then
				->object($coverage->resetExcludedDirectories())->isIdenticalTo($coverage)
				->array($coverage->getExcludedDirectories())->isEmpty()
		;
	}

	public function testCount()
	{
		$this
			->if($coverage = new testedClass())
			->then
				->sizeOf($coverage)->isZero()
		;
	}

	public function testGetClasses()
	{
		$this
			->if($coverage = new testedClass())
			->then
				->array($coverage->getClasses())->isEmpty()
		;
	}

	public function testGetCoverageForClass()
	{
		$this
			->if($coverage = new testedClass())
			->then
				->array($coverage->getCoverageForClass(uniqid()))->isEmpty()
		;
	}

	public function testGetCoverageForMethod()
	{
		$this
			->if($coverage = new testedClass())
			->then
				->array($coverage->getCoverageForClass(uniqid()))->isEmpty()
		;
	}

	public function testExcludeClass()
	{
		$this
			->if($coverage = new testedClass())
			->then
				->object($coverage->excludeClass($class = uniqid()))->isIdenticalTo($coverage)
				->array($coverage->getExcludedClasses())->isEqualTo(array($class))
				->object($coverage->excludeClass($otherClass = rand(1, PHP_INT_MAX)))->isIdenticalTo($coverage)
				->array($coverage->getExcludedClasses())->isEqualTo(array($class, (string) $otherClass))
				->object($coverage->excludeClass($class))->isIdenticalTo($coverage)
				->array($coverage->getExcludedClasses())->isEqualTo(array($class, (string) $otherClass))
		;
	}

	public function testExcludeNamespace()
	{
		$this
			->if($coverage = new testedClass())
			->then
				->object($coverage->excludeNamespace($namespace = uniqid()))->isIdenticalTo($coverage)
				->array($coverage->getExcludedNamespaces())->isEqualTo(array($namespace))
				->object($coverage->excludeNamespace($otherNamespace = rand(1, PHP_INT_MAX)))->isIdenticalTo($coverage)
				->array($coverage->getExcludedNamespaces())->isEqualTo(array($namespace, (string) $otherNamespace))
				->object($coverage->excludeNamespace($namespace))->isIdenticalTo($coverage)
				->array($coverage->getExcludedNamespaces())->isEqualTo(array($namespace, (string) $otherNamespace))
				->object($coverage->excludeNamespace('\\' . ($anotherNamespace = uniqid()) . '\\'))->isIdenticalTo($coverage)
				->array($coverage->getExcludedNamespaces())->isEqualTo(array($namespace, (string) $otherNamespace, $anotherNamespace))
		;
	}

	public function testExcludeDirectory()
	{
		$this
			->if($coverage = new testedClass())
			->then
				->object($coverage->excludeDirectory($directory = uniqid()))->isIdenticalTo($coverage)
				->array($coverage->getExcludedDirectories())->isEqualTo(array($directory))
				->object($coverage->excludeDirectory($otherDirectory = rand(1, PHP_INT_MAX)))->isIdenticalTo($coverage)
				->array($coverage->getExcludedDirectories())->isEqualTo(array($directory, (string) $otherDirectory))
				->object($coverage->excludeDirectory($directory))->isIdenticalTo($coverage)
				->array($coverage->getExcludedDirectories())->isEqualTo(array($directory, (string) $otherDirectory))
				->object($coverage->excludeDirectory(($anotherDirectory = (DIRECTORY_SEPARATOR . uniqid())) . DIRECTORY_SEPARATOR))->isIdenticalTo($coverage)
				->array($coverage->getExcludedDirectories())->isEqualTo(array($directory, (string) $otherDirectory, $anotherDirectory))
		;
	}

	public function testIsInExcludedClasses()
	{
		$this
			->if($coverage = new testedClass())
			->then
				->boolean($coverage->isInExcludedClasses(uniqid()))->isFalse()
			->if($coverage->excludeClass($class = uniqid()))
			->then
				->boolean($coverage->isInExcludedClasses(uniqid()))->isFalse()
				->boolean($coverage->isInExcludedClasses($class))->isTrue()
		;
	}

	public function testIsInExcludedNamespaces()
	{
		$this
			->if($coverage = new testedClass())
			->then
				->boolean($coverage->isInExcludedNamespaces(uniqid()))->isFalse()
			->if($coverage->excludeNamespace($namespace = uniqid()))
			->then
				->boolean($coverage->isInExcludedNamespaces(uniqid()))->isFalse()
				->boolean($coverage->isInExcludedNamespaces($namespace . '\\' . uniqid()))->isTrue()
		;
	}

	public function testIsInExcludedDirectories()
	{
		$this
			->if($coverage = new testedClass())
			->then
				->boolean($coverage->isInExcludedDirectories(uniqid()))->isFalse()
			->if($coverage->excludeDirectory($directory = uniqid()))
			->then
				->boolean($coverage->isInExcludedDirectories(uniqid()))->isFalse()
				->boolean($coverage->isInExcludedDirectories($directory . DIRECTORY_SEPARATOR . uniqid()))->isTrue()
				->boolean($coverage->isInExcludedDirectories($directory . uniqid() . DIRECTORY_SEPARATOR . uniqid()))->isFalse()
		;
	}
}

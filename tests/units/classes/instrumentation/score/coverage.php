<?php

namespace mageekguy\atoum\tests\units\instrumentation\score;

use
	mageekguy\atoum,
	mageekguy\atoum\mock,
	mageekguy\atoum\score,
	mageekguy\atoum\instrumentation\score\coverage as testedClass
;

require_once __DIR__ . '/../../../runner.php';

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

	public function testAddDataForTest()
	{
		$this
			->if($coverage = new testedClass())
			->then
				->object($coverage->addDataForTest($this, array()))->isIdenticalTo($coverage)
				->array($coverage->getClasses())
					->string[$this->getTestedClassName()]->isEqualTo($this->getTestedClassPath())
				->array($methods = $coverage->getMethods())->hasKey($this->getTestedClassName())
				->array($methods[$this->getTestedClassName()])->isEmpty()
			->if($classController = new mock\controller())
			->and($classController->disableMethodChecking())
			->and($classController->__construct = function() {})
			->and($classController->getName = function() use (& $className) { return $className; })
			->and($classController->getFileName = function() use (& $classFile) { return $classFile; })
			->and($classController->getTraits = array())
			->and($class = new \mock\reflectionClass(uniqid(), $classController))
			->and($methodController = new mock\controller())
			->and($methodController->__construct = function() {})
			->and($methodController->isAbstract = false)
			->and($methodController->getName = function() use (& $methodName) { return $methodName; })
			->and($methodController->getDeclaringClass = function() use ($class) { return $class; })
			->and($methodController->getName = function() use (& $methodName) { return $methodName; })
			->and($methodController->getFileName = $classFile)
			->and($classController->getMethods = array($method = new \mock\reflectionMethod(uniqid(), uniqid(), $methodController)))
			->and($classDirectory = uniqid())
			->and($classFile = $classDirectory . DIRECTORY_SEPARATOR . uniqid())
			->and($className = uniqid())
			->and($methodName = uniqid())
			->and($coverageData = array(
					  $className . '::' . $methodName =>
						 array(
							1 => array(false, 1),
							2 => array(true, 2),
							3 => array(false, 3),
							4 => array(false, 4),
							5 => array(false, 5)
						),
					  uniqid() . '::' . uniqid() =>
						 array(
							1 => array(true, 1),
							2 => array(true, 2),
							3 => array(true, 3),
							4 => array(true, 4),
							5 => array(true, 5)
						)
					)
				)
			->and($reflectionClassFactory = function() use ($class) { return $class; })
			->and($coverage->setReflectionClassFactory($reflectionClassFactory))
			->then
				->object($coverage->addDataForTest($this, $coverageData))->isIdenticalTo($coverage)
				->array($methods = $coverage->getMethods())
					->hasKey($this->getTestedClassName())
					->hasKey($className)
				->array($methods[$this->getTestedClassName()])->isEmpty()
				->array($methods[$className])->isEqualTo(array(
						$methodName => array(
							1 => false,
							2 => true,
							3 => false,
							4 => false,
							5 => false
						)
					)
				)
				->array($coverage->getMethods())->isIdenticalTo($methods)
				->object($coverage->addDataForTest($this, $coverageData))->isIdenticalTo($coverage)
				->array($coverage->getMethods())->isIdenticalTo($methods)
			->if($class->getMockController()->getName = get_class($class))
			->and($coverage = new testedClass())
			->and($coverage->setReflectionClassFactory($reflectionClassFactory))
			->and($coverage->excludeClass(get_class($class)))
			->then
				->object($coverage->addDataForTest($this, array()))->isIdenticalTo($coverage)
				->array($coverage->getClasses())->isEmpty()
				->array($coverage->getMethods())->isEmpty()
				->object($coverage->addDataForTest($this, $coverageData))->isIdenticalTo($coverage)
				->array($coverage->getClasses())->isEmpty()
				->array($coverage->getMethods())->isEmpty()
			->and($coverage = new testedClass())
			->and($coverage->setReflectionClassFactory($reflectionClassFactory))
			->and($coverage->excludeDirectory($classDirectory))
			->then
				->object($coverage->addDataForTest($this, array()))->isIdenticalTo($coverage)
				->array($coverage->getClasses())->isEmpty()
				->array($coverage->getMethods())->isEmpty()
				->object($coverage->addDataForTest($this, $coverageData))->isIdenticalTo($coverage)
				->array($coverage->getClasses())->isEmpty()
				->array($coverage->getMethods())->isEmpty()
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
			->if($classController = new mock\controller())
			->and($classController->disableMethodChecking())
			->and($classController->__construct = function() {})
			->and($classController->getName = function() use (& $className) { return $className; })
			->and($classController->getFileName = function() use (& $classFile) { return $classFile; })
			->and($classController->getTraits = array())
			->and($class = new \mock\reflectionClass(uniqid(), $classController))
			->and($methodController = new mock\controller())
			->and($methodController->__construct = function() {})
			->and($methodController->getName = function() use (& $methodName) { return $methodName; })
			->and($methodController->isAbstract = false)
			->and($methodController->getFileName = function() use (& $classFile) { return $classFile; })
			->and($methodController->getDeclaringClass = function() use ($class) { return $class; })
			->and($classController->getMethods = array(new \mock\reflectionMethod(uniqid(), uniqid(), $methodController)))
			->and($classFile = uniqid())
			->and($className = uniqid())
			->and($methodName = uniqid())
			->and($coverageData = array(
					$className . '::' . $methodName =>
					 array(
						5 => array(true, 5),
						6 => array(true, 6),
						7 => array(true, 7),
						8 => array(true, 8),
						9 => array(true, 9)
					),
				  uniqid() . '::' . uniqid() =>
					 array(
						5 => array(true, 5),
						6 => array(true, 6),
						7 => array(true, 7),
						8 => array(true, 8),
						9 => array(true, 9)
					)
				)
			)
			->and($coverage = new testedClass())
			->and($coverage->setReflectionClassFactory(function() use ($class) { return $class; }))
			->and($coverage->addDataForTest($this, $coverageData))
			->and($coverage->excludeClass($excludedClass =uniqid()))
			->and($coverage->excludeNamespace($excludedNamespace= uniqid()))
			->and($coverage->excludeDirectory($excludedDirectory = uniqid()))
			->then
				->array($coverage->getClasses())->isNotEmpty()
				->array($coverage->getMethods())->isNotEmpty()
				->array($coverage->getExcludedClasses())->isNotEmpty()
				->array($coverage->getExcludedNamespaces())->isNotEmpty()
				->array($coverage->getExcludedDirectories())->isNotEmpty()
				->object($coverage->reset())->isIdenticalTo($coverage)
				->array($coverage->getClasses())->isEmpty()
				->array($coverage->getMethods())->isEmpty()
				->array($coverage->getExcludedClasses())->isNotEmpty()
				->array($coverage->getExcludedNamespaces())->isNotEmpty()
				->array($coverage->getExcludedDirectories())->isNotEmpty()
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

	public function testMerge()
	{
		$this
			->if($classController = new mock\controller())
			->and($classController->disableMethodChecking())
			->and($classController->__construct = function() {})
			->and($classController->getName = function() use (& $className) { return $className; })
			->and($classController->getFileName = function() use (& $classFile) { return $classFile; })
			->and($classController->getTraits = array())
			->and($class = new \mock\reflectionClass(uniqid(), $classController))
			->and($methodController = new mock\controller())
			->and($methodController->__construct = function() {})
			->and($methodController->getName = function() use (& $methodName) { return $methodName; })
			->and($methodController->isAbstract = false)
			->and($methodController->getFileName = function() use (& $classFile) { return $classFile; })
			->and($methodController->getDeclaringClass = function() use ($class) { return $class; })
			->and($method = new \mock\reflectionMethod(uniqid(), uniqid(), $methodController))
			->and($classController->getMethod = function() use ($method) { return $method; })
			->and($classController->getMethods = array($method))
			->and($classFile = uniqid())
			->and($className = uniqid())
			->and($methodName = uniqid())
			->and($coverageData = array(
				  $className . '::' . $methodName =>
					 array(
						5 => array(false, 5),
						6 => array(false, 6),
						7 => array(true, 7),
						8 => array(false, 8),
						9 => array(false, 9)
					),
				  uniqid() . '::' . uniqid() =>
					 array(
						5 => array(true, 5),
						6 => array(true, 6),
						7 => array(true, 7),
						8 => array(true, 8),
						9 => array(true, 9)
					)
				)
			)
			->and($coverage = new testedClass())
			->and($coverage->setReflectionClassFactory(function() use ($class) { return $class; }))
			->then
				->object($coverage->merge($coverage))->isIdenticalTo($coverage)
				->array($coverage->getClasses())->isEmpty()
				->array($coverage->getMethods())->isEmpty()
			->if($otherCoverage = new testedClass())
			->then
				->object($coverage->merge($otherCoverage))->isIdenticalTo($coverage)
				->array($coverage->getClasses())->isEmpty()
				->array($coverage->getMethods())->isEmpty()
			->if($coverage->addDataForTest($this, $coverageData))
			->then
				->object($coverage->merge($otherCoverage))->isIdenticalTo($coverage)
				->array($coverage->getClasses())->isEqualTo(array($className => $classFile))
				->array($coverage->getMethods())->isEqualTo(array(
						$className => array(
							$methodName => array(
								5 => false,
								6 => false,
								7 => true,
								8 => false,
								9 => false
							)
						)
					)
				)
				->object($coverage->merge($coverage))->isIdenticalTo($coverage)
				->array($coverage->getClasses())->isEqualTo(array($className => $classFile))
				->array($coverage->getMethods())->isEqualTo(array(
						$className => array(
							$methodName => array(
								5 => false,
								6 => false,
								7 => true,
								8 => false,
								9 => false
							)
						)
					)
				)
			->if($otherClassController = new mock\controller())
			->and($otherClassController->disableMethodChecking())
			->and($otherClassController->__construct = function() {})
			->and($otherClassController->getName = function() use (& $otherClassName) { return $otherClassName; })
			->and($otherClassController->getFileName = function() use (& $otherClassFile) { return $otherClassFile; })
			->and($otherClassController->getTraits = array())
			->and($otherClass = new \mock\reflectionClass($class, $otherClassController))
			->and($otherMethodController = new mock\controller())
			->and($otherMethodController->__construct = function() {})
			->and($otherMethodController->getName = function() use (& $otherMethodName) { return $otherMethodName; })
			->and($otherMethodController->isAbstract = false)
			->and($otherMethodController->getFileName = function() use (& $otherClassFile) { return $otherClassFile; })
			->and($otherMethodController->getDeclaringClass = function() use ($otherClass) { return $otherClass; })
			->and($otherClassController->getMethods = array(new \mock\reflectionMethod(uniqid(), uniqid(), $otherMethodController)))
			->and($otherClassFile = uniqid())
			->and($otherClassName = uniqid())
			->and($otherMethodName = uniqid())
			->and($otherCoverageData = array(
				  $otherClassName . '::' . $otherMethodName =>
					 array(
						1 => array(false, 1),
						2 => array(false, 2),
						3 => array(true, 3),
						4 => array(true, 4),
						5 => array(false, 5),
						6 => array(true, 6),
						7 => array(true, 7),
						8 => array(false, 8),
						9 => array(false, 9),
						10 => array(true, 10)
					),
				  uniqid() =>
					 array(
						500 => array(true, 500),
						600 => array(true, 600),
						700 => array(true, 700),
						800 => array(true, 800),
						900 => array(true, 900)
					)
				)
			)
			->and($otherCoverage->setReflectionClassFactory(function() use ($otherClass) { return $otherClass; }))
			->then
				->object($coverage->merge($otherCoverage->addDataForTest($this, $otherCoverageData)))->isIdenticalTo($coverage)
				->array($coverage->getClasses())->isEqualTo(array(
						$className => $classFile,
						$otherClassName => $otherClassFile
					)
				)
				->array($coverage->getMethods())->isEqualTo(array(
						$className => array(
							$methodName => array(
								5 => false,
								6 => false,
								7 => true,
								8 => false,
								9 => false
							)
						),
						$otherClassName => array(
							$otherMethodName => array(
								1 => false,
								2 => false,
								3 => true,
								4 => true,
								5 => false,
								6 => true,
								7 => true,
								8 => false,
								9 => false,
								10 => true
							)
						)
					)
				)
			->if($classController = new mock\controller())
			->and($classController->disableMethodChecking())
			->and($classController->__construct = function() {})
			->and($classController->getName = function() use (& $className) { return $className; })
			->and($classController->getFileName = function() use (& $classFile) { return $classFile; })
			->and($classController->getTraits = array())
			->and($class = new \mock\reflectionClass(uniqid(), $classController))
			->and($methodController = new mock\controller())
			->and($methodController->__construct = function() {})
			->and($methodController->getName = function() use (& $methodName) { return $methodName; })
			->and($methodController->isAbstract = false)
			->and($methodController->getFileName = function() use (& $classFile) { return $classFile; })
			->and($methodController->getDeclaringClass = function() use ($class) { return $class; })
			->and($method = new \mock\reflectionMethod(uniqid(), uniqid(), $methodController))
			->and($classController->getMethod = function() use ($method) { return $method; })
			->and($classController->getMethods = array($method))
			->and($classFile = uniqid())
			->and($className = uniqid())
			->and($methodName = uniqid())
			->and($coverageData = array(
				  $className . '::' . $methodName =>
					 array(
						5 => array(false, 5),
						6 => array(false, 6),
						7 => array(true, 7),
						8 => array(false, 8),
						9 => array(false, 9)
					),
				  uniqid() . '::' . uniqid() =>
					 array(
						5 => array(true, 5),
						6 => array(true, 6),
						7 => array(true, 7),
						8 => array(true, 8),
						9 => array(true, 9)
					)
				)
			)
			->and($coverage = new testedClass())
			->and($coverage->setReflectionClassFactory(function() use ($class) { return $class; }))
			->and($coverage->excludeClass($className))
			->and($otherCoverage = new testedClass())
			->and($otherCoverage->setReflectionClassFactory(function() use ($class) { return $class; }))
			->and($otherCoverage->addDataForTest($this, $coverageData))
			->then
				->array($otherCoverage->getClasses())->isNotEmpty()
				->array($otherCoverage->getMethods())->isNotEmpty()
				->object($coverage->merge($otherCoverage))->isIdenticalTo($coverage)
				->array($coverage->getClasses())->isEmpty()
				->array($coverage->getMethods())->isEmpty()
		;
	}

	public function testCount()
	{
		$this
			->if($coverage = new testedClass())
			->then
				->sizeOf($coverage)->isZero()
			->if($classController = new mock\controller())
			->and($classController->disableMethodChecking())
			->and($classController->__construct = function() {})
			->and($classController->getName = function() use (& $className) { return $className; })
			->and($classController->getFileName = function() use (& $classFile) { return $classFile; })
			->and($classController->getTraits = array())
			->and($class = new \mock\reflectionClass(uniqid(), $classController))
			->and($methodController = new mock\controller())
			->and($methodController->__construct = function() {})
			->and($methodController->getName = function() use (& $methodName) { return $methodName; })
			->and($methodController->isAbstract = false)
			->and($methodController->getFileName = function() use (& $classFile) { return $classFile; })
			->and($methodController->getDeclaringClass = function() use ($class) { return $class; })
			->and($classController->getMethods = array(new \mock\reflectionMethod(uniqid(), uniqid(), $methodController)))
			->and($classFile = uniqid())
			->and($className = uniqid())
			->and($methodName = uniqid())
			->and($coverageData = array(
				$className . '::' . $methodName =>
					 array(
						5 => array(true, 5),
						6 => array(true, 6),
						7 => array(true, 7),
						8 => array(true, 8),
						9 => array(true, 9)
					),
				  uniqid() . '::' . uniqid() =>
					 array(
						5 => array(true, 5),
						6 => array(true, 6),
						7 => array(true, 7),
						8 => array(true, 8),
						9 => array(true, 9)
					)
				)
			)
			->and($coverage->setReflectionClassFactory(function() use ($class) { return $class; }))
			->then
				->sizeOf($coverage->addDataForTest($this, $coverageData))->isEqualTo(1)
		;
	}

	public function testGetClasses()
	{
		$this
			->if($classController = new mock\controller())
			->and($classController->disableMethodChecking())
			->and($classController->__construct = function() {})
			->and($classController->getName = function() use (& $className) { return $className; })
			->and($classController->getFileName = function() use (& $classFile) { return $classFile; })
			->and($classController->getTraits = array())
			->and($class = new \mock\reflectionClass(uniqid(), $classController))
			->and($methodController = new mock\controller())
			->and($methodController->__construct = function() {})
			->and($methodController->getName = function() { return uniqid(); })
			->and($methodController->isAbstract = false)
			->and($methodController->getFileName = function() use (& $classFile) { return $classFile; })
			->and($methodController->getDeclaringClass = function() use ($class) { return $class; })
			->and($classController->getMethods = array(new \mock\reflectionMethod(uniqid(), uniqid(), $methodController)))
			->and($classFile = uniqid())
			->and($className = uniqid())
			->and($coverageData = array(
				  $className . '::' . uniqid() =>
					 array(
						3 => array(false, 3),
						4 => array(false, 4),
						5 => array(false, 5),
						6 => array(false, 6),
						7 => array(false, 7),
						8 => array(false, 8),
						9 => array(false, 9)
					)
				)
			)
			->and($coverage = new testedClass())
			->and($coverage->setReflectionClassFactory(function() use ($class) { return $class; }))
			->and($coverage->addDataForTest($this, $coverageData))
			->then
				->array($coverage->getClasses())->isEqualTo(array($className => $classFile))
		;
	}

	public function testGetValue()
	{
		$this
			->if($classController = new mock\controller())
			->and($classController->disableMethodChecking())
			->and($classController->__construct = function() {})
			->and($classController->getName = function() use (& $className) { return $className; })
			->and($classController->getFileName = function() use (& $classFile) { return $classFile; })
			->and($classController->getTraits = array())
			->and($class = new \mock\reflectionClass(uniqid(), $classController))
			->and($methodController = new mock\controller())
			->and($methodController->__construct = function() {})
			->and($methodController->getName = function() use (& $methodName) { return $methodName; })
			->and($methodController->isAbstract = false)
			->and($methodController->getFileName = function() use (& $classFile) { return $classFile; })
			->and($methodController->getDeclaringClass = function() use ($class) { return $class; })
			->and($classController->getMethods = array(new \mock\reflectionMethod(uniqid(), uniqid(), $methodController)))
			->and($classFile = uniqid())
			->and($className = uniqid())
			->and($methodName = uniqid())
			->and($coverageData = array(
				  $className . '::' . $methodName =>
					 array(
						3 => array(false, 3),
						4 => array(false, 4),
						5 => array(false, 5),
						6 => array(false, 6),
						7 => array(false, 7),
						8 => array(false, 8),
						9 => array(false, 9)
					),
				  uniqid() . '::' . uniqid() =>
					 array(
						5 => array(true, 5),
						6 => array(true, 6),
						7 => array(true, 7),
						8 => array(true, 8),
						9 => array(true, 9)
					)
				)
			)
			->and($coverage = new testedClass())
			->and($coverage->setReflectionClassFactory(function() use ($class) { return $class; }))
			->and($coverage->addDataForTest($this, $coverageData))
			->then
				->float($coverage->getValue())->isEqualTo(0.0)
			->if($coverageData = array(
				  $className . '::' . $methodName =>
					 array(
						3 => array(false, 3),
						4 => array(true, 4),
						5 => array(false, 5),
						6 => array(false, 6)
					),
				  uniqid() . '::' . uniqid() =>
					 array(
						5 => array(true, 5),
						6 => array(true, 6),
						7 => array(true, 7),
						8 => array(true, 8),
						9 => array(true, 9)
					)
				)
			)
			->and($coverage->reset()->addDataForTest($this, $coverageData))
			->then
				->float($coverage->getValue())->isEqualTo(1 / 4)
			->if($coverageData = array(
				  $className . '::' . $methodName =>
					 array(
						3 => array(false, 3),
						4 => array(true, 4),
						5 => array(false, 5),
						6 => array(true, 6)
					),
				  uniqid() . '::' . uniqid() =>
					 array(
						5 => array(true, 5),
						6 => array(true, 6),
						7 => array(true, 7),
						8 => array(true, 8),
						9 => array(true, 9)
					)
			)
		)
		->and($coverage->reset()->addDataForTest($this, $coverageData))
		->then
			->float($coverage->getValue())->isEqualTo(2 / 4)
		->if($coverageData = array(
			  $className . '::' . $methodName =>
				 array(
					3 => array(true, 3),
					4 => array(true, 4),
					5 => array(true, 5),
					6 => array(true, 6)
				),
			  uniqid() . '::' . uniqid() =>
				 array(
					5 => array(false, 5),
					6 => array(false, 6),
					7 => array(false, 7),
					8 => array(false, 8),
					9 => array(false, 9)
				)
			)
		)
		->and($coverage->reset()->addDataForTest($this, $coverageData))
		->then
			->float($coverage->getValue())->isEqualTo(1.0)
		;
	}

	public function testGetValueForClass()
	{
		$this
			->if($coverage = new testedClass())
			->then
				->variable($coverage->getValueForClass(uniqid()))->isNull()
			->if($classController = new mock\controller())
			->and($classController->disableMethodChecking())
			->and($classController->__construct = function() {})
			->and($classController->getName = function() use (& $className) { return $className; })
			->and($classController->getFileName = function() use (& $classFile) { return $classFile; })
			->and($classController->getTraits = array())
			->and($class =  new \mock\reflectionClass(uniqid(), $classController))
			->and($methodController = new mock\controller())
			->and($methodController->__construct = function() {})
			->and($methodController->getName = function() use (& $methodName) { return $methodName; })
			->and($methodController->isAbstract = false)
			->and($methodController->getFileName = function() use (& $classFile) { return $classFile; })
			->and($methodController->getDeclaringClass = function() use ($class) { return $class; })
			->and($classController->getMethods = array(new \mock\reflectionMethod(uniqid(), uniqid(), $methodController)))
			->and($classFile = uniqid())
			->and($className = uniqid())
			->and($methodName = uniqid())
			->and($coverageData = array(
				  $className . '::' . $methodName =>
					 array(
						3 => array(false, 3),
						4 => array(false, 4),
						5 => array(false, 5),
						6 => array(false, 6),
						7 => array(false, 7),
						8 => array(false, 8),
						9 => array(false, 9)
					),
				  uniqid() . '::' . uniqid() =>
					 array(
						5 => array(true, 5),
						6 => array(true, 6),
						7 => array(true, 7),
						8 => array(true, 8),
						9 => array(true, 9)
					)
				)
			)
			->and($coverage = new testedClass())
			->and($coverage->setReflectionClassFactory(function() use ($class) { return $class; }))
			->and($coverage->addDataForTest($this, $coverageData))
			->then
				->variable($coverage->getValueForClass(uniqid()))->isNull()
				->float($coverage->getValueForClass($className))->isEqualTo(0.0)
			->if($coverageData = array(
				  $className . '::' . $methodName =>
					 array(
						3 => array(false, 3),
						4 => array(false, 4),
						5 => array(true, 5),
						6 => array(false, 6)
					),
				  uniqid()  . '::' . uniqid() =>
					 array(
						5 => array(true, 5),
						6 => array(true, 6),
						7 => array(true, 7),
						8 => array(true, 8),
						9 => array(true, 9)
					)
				)
			)
			->and($coverage->reset()->addDataForTest($this, $coverageData))
			->then
				->variable($coverage->getValueForClass(uniqid()))->isNull()
				->float($coverage->getValueForClass($className))->isEqualTo(1 / 4)
			->if($coverageData = array(
				  $className . '::' . $methodName =>
					 array(
						3 => array(false, 3),
						4 => array(true, 4),
						5 => array(true, 5),
						6 => array(false, 6)
					),
				  uniqid() . '::' . uniqid() =>
					 array(
						5 => array(true, 5),
						6 => array(true, 6),
						7 => array(true, 7),
						8 => array(true, 8),
						9 => array(true, 9)
					)
				)
			)
			->and($coverage->reset()->addDataForTest($this, $coverageData))
			->then
				->variable($coverage->getValueForClass(uniqid()))->isNull()
				->float($coverage->getValueForClass($className))->isEqualTo(2 / 4)
			->if($coverageData = array(
				  $className . '::' . $methodName =>
					 array(
						3 => array(true, 3),
						4 => array(true, 4),
						5 => array(true, 5),
						6 => array(true, 6),
						7 => array(true, 7),
						8 => array(true, 8),
						9 => array(true, 9)
					),
				  uniqid() . '::' . uniqid() =>
					 array(
						5 => array(false, 5),
						6 => array(false, 6),
						7 => array(false, 7),
						8 => array(false, 8),
						9 => array(false, 9)
					)
				)
			)
			->and($coverage->reset()->addDataForTest($this, $coverageData))
			->then
				->variable($coverage->getValueForClass(uniqid()))->isNull()
				->float($coverage->getValueForClass($className))->isEqualTo(1.0)
		;
	}

	public function testGetCoverageForClass()
	{
		$this
			->if($coverage = new testedClass())
			->then
				->array($coverage->getCoverageForClass(uniqid()))->isEmpty()
			->if($classController = new mock\controller())
			->and($classController->disableMethodChecking())
			->and($classController->__construct = function() {})
			->and($classController->getName = function() use (& $className) { return $className; })
			->and($classController->getFileName = function() use (& $classFile) { return $classFile; })
			->and($classController->getTraits = array())
			->and($class =  new \mock\reflectionClass(uniqid(), $classController))
			->and($methodController = new mock\controller())
			->and($methodController->__construct = function() {})
			->and($methodController->getName = function() use (& $methodName) { return $methodName; })
			->and($methodController->isAbstract = false)
			->and($methodController->getFileName = function() use (& $classFile) { return $classFile; })
			->and($methodController->getDeclaringClass = function() use ($class) { return $class; })
			->and($classController->getMethods = array(new \mock\reflectionMethod(uniqid(), uniqid(), $methodController)))
			->and($classFile = uniqid())
			->and($className = uniqid())
			->and($methodName = uniqid())
			->and($coverageData = array(
				$className . '::' . $methodName =>
					array(
						3 => array(true, 3),
						4 => array(true, 4),
						5 => array(true, 5),
						6 => array(true, 6),
						7 => array(true, 7),
						8 => array(true, 8),
						9 => array(true, 9)
					),
				uniqid() . '::' . uniqid() =>
					array(
						5 => array(true, 5),
						6 => array(true, 6),
						7 => array(true, 7),
						8 => array(true, 8),
						9 => array(true, ç)
					)
				)
			)
			->and($expected = array(
				$methodName =>
					array(
						3 => array(true, 3),
						4 => array(true, 4),
						5 => array(true, 5),
						6 => array(true, 6),
						7 => array(true, 7),
						8 => array(true, 8),
						9 => array(true, 9)
					)
				)
			)
			->and($coverage->setReflectionClassFactory(function() use ($class) { return $class; }))
			->and($coverage->addDataForTest($this, $coverageData))
			->then
				->array($coverage->getCoverageForClass($className))->isEqualTo($expected)
		;
	}

	public function testGetValueForMethod()
	{
		$this
			->if($coverage = new testedClass())
			->then
				->variable($coverage->getValueForMethod(uniqid(), uniqid()))->isNull()
			->if($classController = new mock\controller())
			->and($classController->disableMethodChecking())
			->and($classController->__construct = function() {})
			->and($classController->getName = function() use (& $className) { return $className; })
			->and($classController->getFileName = function() use (& $classFile) { return $classFile; })
			->and($classController->getTraits = array())
			->and($class = new \mock\reflectionClass(uniqid(), $classController))
			->and($methodController = new mock\controller())
			->and($methodController->__construct = function() {})
			->and($methodController->getName = function() use (& $methodName) { return $methodName; })
			->and($methodController->isAbstract = false)
			->and($methodController->getFileName = function() use (& $classFile) { return $classFile; })
			->and($methodController->getDeclaringClass = function() use ($class) { return $class; })
			->and($classController->getMethods = array(new \mock\reflectionMethod(uniqid(), uniqid(), $methodController)))
			->and($classFile = uniqid())
			->and($className = uniqid())
			->and($methodName = uniqid())
			->and($coverageData = array(
				  $className . '::' . $methodName =>
					 array(
						3 => array(false, 3),
						4 => array(false, 4),
						5 => array(false, 5),
						6 => array(false, 6),
						7 => array(false, 7),
						8 => array(false, 8),
						9 => array(false, 9)
					),
				  uniqid() . '::' . uniqid() =>
					 array(
						5 => array(true, 5),
						6 => array(true, 6),
						7 => array(true, 7),
						8 => array(true, 8),
						9 => array(true, 9)
					)
				)
			)
			->and($coverage->setReflectionClassFactory(function() use ($class) { return $class; }))
			->and($coverage->addDataForTest($this, $coverageData))
			->then
				->variable($coverage->getValueForMethod(uniqid(), uniqid()))->isNull()
				->variable($coverage->getValueForMethod($className, uniqid()))->isNull()
				->float($coverage->getValueForMethod($className, $methodName))->isEqualTo(0.0)
			->if($coverageData = array(
				  $className . '::' . $methodName =>
					 array(
						3 => array(true, 3),
						4 => array(false, 4),
						5 => array(false, 5),
						6 => array(false, 6)
					),
				  uniqid() . '::' . uniqid() =>
					 array(
						5 => array(true, 5),
						6 => array(true, 6),
						7 => array(true, 7),
						8 => array(true, 8),
						9 => array(true, 9)
					)
				)
			)
			->and($coverage->reset()->addDataForTest($this, $coverageData))
			->then
				->variable($coverage->getValueForMethod(uniqid(), uniqid()))->isNull()
				->variable($coverage->getValueForMethod($className, uniqid()))->isNull()
				->float($coverage->getValueForMethod($className, $methodName))->isEqualTo(1 / 4)
			->if($coverageData = array(
				  $className . '::' . $methodName =>
					 array(
						3 => array(true, 3),
						4 => array(false, 4),
						5 => array(false, 5),
						6 => array(true, 6)
					),
				  uniqid() . '::' . uniqid() =>
					 array(
						5 => array(true, 5),
						6 => array(true, 6),
						7 => array(true, 7),
						8 => array(true, 8),
						9 => array(true, 9)
					)
				)
			)
			->and($coverage->reset()->addDataForTest($this, $coverageData))
			->then
				->variable($coverage->getValueForMethod(uniqid(), uniqid()))->isNull()
				->variable($coverage->getValueForMethod($className, uniqid()))->isNull()
				->float($coverage->getValueForMethod($className, $methodName))->isEqualTo(2 / 4)
			->if($coverageData = array(
				  $className . '::' . $methodName =>
					 array(
						3 => array(true, 3),
						4 => array(true, 4),
						5 => array(true, 5),
						6 => array(true, 6)
					),
				  uniqid() . '::' . uniqid() =>
					 array(
						5 => array(false, 5),
						6 => array(false, 6),
						7 => array(false, 7),
						8 => array(false, 8),
						9 => array(false, 9)
					)
				)
			)
			->and($coverage->reset()->addDataForTest($this, $coverageData))
			->then
				->variable($coverage->getValueForMethod(uniqid(), uniqid()))->isNull()
				->variable($coverage->getValueForMethod($className, uniqid()))->isNull()
				->float($coverage->getValueForMethod($className, $methodName))->isEqualTo(1.0)
		;
	}

	public function testGetCoverageForMethod()
	{
		$this
			->if($coverage = new testedClass())
			->then
				->array($coverage->getCoverageForClass(uniqid()))->isEmpty()
			->if($classController = new mock\controller())
			->and($classController->disableMethodChecking())
			->and($classController->__construct = function() {})
			->and($classController->getName = function() use (& $className) { return $className; })
			->and($classController->getFileName = function() use (& $classFile) { return $classFile; })
			->and($classController->getTraits = array())
			->and($class =  new \mock\reflectionClass(uniqid(), $classController))
			->and($methodController = new mock\controller())
			->and($methodController->__construct = function() {})
			->and($methodController->getName = function() use (& $methodName) { return $methodName; })
			->and($methodController->isAbstract = false)
			->and($methodController->getFileName = function() use (& $classFile) { return $classFile; })
			->and($methodController->getDeclaringClass = function() use ($class) { return $class; })
			->and($classController->getMethods = array(new \mock\reflectionMethod(uniqid(), uniqid(), $methodController)))
			->and($classFile = uniqid())
			->and($className = uniqid())
			->and($methodName = uniqid())
			->and($coverageData = array(
				$className . '::' . $methodName =>
					array(
						3 => array(false, 3),
						4 => array(true, 4),
						5 => array(false, 5),
						6 => array(false, 6),
						7 => array(false, 7),
						8 => array(false, 8),
						9 => array(false, 9)
					),
				uniqid() . '::' . uniqid() =>
					array(
						5 => array(true, 5),
						6 => array(true, 6),
						7 => array(true, 7),
						8 => array(true, 8),
						9 => array(true, 9)
					)
				)
			)
			->and($expected = array(
					3 => false,
					4 => true,
					5 => false,
					6 => false,
					7 => false,
					8 => false,
					9 => false
				)
			)
			->and($coverage = new testedClass())
			->and($coverage->setReflectionClassFactory(function() use ($class) { return $class; }))
			->and($coverage->addDataForTest($this, $coverageData))
			->then
				->array($coverage->getCoverageForMethod($className, $methodName))->isEqualTo($expected)
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

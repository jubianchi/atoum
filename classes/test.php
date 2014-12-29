<?php

namespace mageekguy\atoum;

use
	mageekguy\atoum,
	mageekguy\atoum\test,
	mageekguy\atoum\mock,
	mageekguy\atoum\asserter,
	mageekguy\atoum\asserters,
	mageekguy\atoum\exceptions,
	mageekguy\atoum\annotations
;

abstract class test implements observable, \countable
{
	const testMethodPrefix = 'test';
	const defaultNamespace = '#(?:^|\\\)tests?\\\units?\\\#i';
	const defaultMethodPrefix = '#^(?:test|_*[^_]+_should_)#i';
	const runStart = 'testRunStart';
	const beforeSetUp = 'beforeTestSetUp';
	const afterSetUp = 'afterTestSetUp';
	const beforeTestMethod = 'beforeTestMethod';
	const fail = 'testAssertionFail';
	const error = 'testError';
	const void = 'testVoid';
	const uncompleted = 'testUncompleted';
	const skipped = 'testSkipped';
	const exception = 'testException';
	const runtimeException = 'testRuntimeException';
	const success = 'testAssertionSuccess';
	const afterTestMethod = 'afterTestMethod';
	const beforeTearDown = 'beforeTestTearDown';
	const afterTearDown = 'afterTestTearDown';
	const runStop = 'testRunStop';
	const defaultEngine = 'concurrent';
	const enginesNamespace = '\mageekguy\atoum\test\engines';

	private $score = null;
	private $locale = null;
	private $adapter = null;
	private $mockGenerator = null;
	private $mockAutoloader = null;
	private $factoryBuilder = null;
	private $reflectionMethodFactory = null;
	private $asserterGenerator = null;
	private $assertionManager = null;
	private $phpMocker = null;
	private $testAdapterStorage = null;
	private $asserterCallManager = null;
	private $mockControllerLinker = null;
	private $phpPath = null;
	private $testedClassName = null;
	private $testedClassPath = null;
	private $currentMethod = null;
	private $testNamespace = null;
	private $testMethodPrefix = null;
	private $classEngine = null;
	private $bootstrapFile = null;
	private $maxAsynchronousEngines = null;
	private $asynchronousEngines = 0;
	private $path = '';
	private $class = '';
	private $classNamespace = '';
	private $observers = null;
	private $phpVersions = array();
	private $mandatoryExtensions = array();
	private $testClass;
	private $runTestMethods = array();
	private $methods = array();
	private $executeOnFailure = array();
	private $debugMode = false;
	private $xdebugConfig = null;
	private $codeCoverage = false;
	private $classHasNotVoidMethods = false;
	private $extensions = null;

	private static $namespace = null;
	private static $methodPrefix = null;
	private static $defaultEngine = self::defaultEngine;

	public function __construct(adapter $adapter = null, annotations\extractor $annotationExtractor = null, asserter\generator $asserterGenerator = null, test\assertion\manager $assertionManager = null, \closure $reflectionClassFactory = null)
	{
		$this
			->setAdapter($adapter)
			->setPhpMocker()
			->setMockGenerator()
			->setMockAutoloader()
			->setAsserterGenerator($asserterGenerator)
			->setAssertionManager($assertionManager)
			->setTestAdapterStorage()
			->setMockControllerLinker()
			->setScore()
			->setLocale()
			->setFactoryBuilder()
			->setReflectionMethodFactory()
			->setAsserterCallManager()
			->enableCodeCoverage()
		;

		$this->observers = new \splObjectStorage();
		$this->extensions = new \splObjectStorage();

		$class = ($reflectionClassFactory ? $reflectionClassFactory($this) : new \reflectionClass($this));

		$this->path = $class->getFilename();
		$this->class = $class->getName();
		$this->classNamespace = $class->getNamespaceName();

		$this->testClass = new test\testClass($class);
		$this->setTestMethodPrefix(self::defaultMethodPrefix);

		$annotationExtractor = $annotationExtractor ?: new annotations\extractor();

		$this->setClassAnnotations($annotationExtractor);

		$annotationExtractor->extract($class->getDocComment());

		if ($this->testNamespace === null || $this->testMethodPrefix === null)
		{
			$annotationExtractor
				->unsetHandler('ignore')
				->unsetHandler('tags')
				->unsetHandler('maxChildrenNumber')
			;

			$parentClass = $class;

			while (($this->testNamespace === null || $this->testMethodPrefix === null) && ($parentClass = $parentClass->getParentClass()) !== false)
			{
				$annotationExtractor->extract($parentClass->getDocComment());

				if ($this->testNamespace !== null)
				{
					$annotationExtractor->unsetHandler('namespace');
				}

				if ($this->testMethodPrefix !== null)
				{
					$annotationExtractor->unsetHandler('methodPrefix');
				}
			}
		}

		$this->runTestMethods($this->getTestMethods());
	}

	public function __toString()
	{
		return $this->getClass();
	}

	public function __get($property)
	{
		return $this->assertionManager->__get($property);
	}

	public function __set($property, $handler)
	{
		$this->assertionManager->{$property} = $handler;

		return $this;
	}

	public function __call($method, array $arguments)
	{
		return $this->assertionManager->__call($method, $arguments);
	}

	public function setTestAdapterStorage(test\adapter\storage $storage = null)
	{
		$this->testAdapterStorage = $storage ?: new test\adapter\storage();

		return $this;
	}

	public function getTestAdapterStorage()
	{
		return $this->testAdapterStorage;
	}

	public function setMockControllerLinker(mock\controller\linker $linker = null)
	{
		$this->mockControllerLinker = $linker ?: new mock\controller\linker();

		return $this;
	}

	public function getMockControllerLinker()
	{
		return $this->mockControllerLinker;
	}

	public function setScore(test\score $score = null)
	{
		$this->score = $score ?: new test\score();

		return $this;
	}

	public function getScore()
	{
		return $this->score;
	}

	public function setLocale(locale $locale = null)
	{
		$this->locale = $locale ?: new locale();

		return $this;
	}

	public function getLocale()
	{
		return $this->locale;
	}

	public function setAdapter(adapter $adapter = null)
	{
		$this->adapter = $adapter ?: new adapter();

		return $this;
	}

	public function getAdapter()
	{
		return $this->adapter;
	}

	public function setPhpMocker(php\mocker $phpMocker = null)
	{
		$this->phpMocker = $phpMocker ?: new php\mocker();

		return $this;
	}

	public function getPhpMocker()
	{
		return $this->phpMocker;
	}

	public function setMockGenerator(test\mock\generator $generator = null)
	{
		if ($generator !== null)
		{
			$generator->setTest($this);
		}
		else
		{
			$generator = new test\mock\generator($this);
		}

		$this->mockGenerator = $generator;

		return $this;
	}

	public function getMockGenerator()
	{
		return $this->mockGenerator;
	}

	public function setMockAutoloader(atoum\autoloader\mock $autoloader = null)
	{
		$this->mockAutoloader = $autoloader ?: new atoum\autoloader\mock();

		return $this;
	}

	public function getMockAutoloader()
	{
		return $this->mockAutoloader;
	}

	public function setFactoryBuilder(factory\builder $factoryBuilder = null)
	{
		$this->factoryBuilder = $factoryBuilder ?: new factory\builder\closure();

		return $this;
	}

	public function getFactoryBuilder()
	{
		return $this->factoryBuilder;
	}

	public function setReflectionMethodFactory(\closure $factory = null)
	{
		$this->reflectionMethodFactory = $factory ?: function($class, $method) { return new \reflectionMethod($class, $method); };

		return $this;
	}

	public function setAsserterGenerator(test\asserter\generator $generator = null)
	{
		if ($generator !== null)
		{
			$generator->setTest($this);
		}
		else
		{
			$generator = new test\asserter\generator($this);
		}

		$this->asserterGenerator = $generator->setTest($this);

		return $this;
	}

	public function getAsserterGenerator()
	{
		$this->testAdapterStorage->resetCalls();

		return $this->asserterGenerator;
	}

	public function setAssertionManager(test\assertion\manager $assertionManager = null)
	{
		$this->assertionManager = $assertionManager ?: new test\assertion\manager();

		$test = $this;

		$this->assertionManager
			->setHandler('when', function($mixed) use ($test) { if ($mixed instanceof \closure) { $mixed($test); } return $test; })
			->setHandler('assert', function($case = null) use ($test) { $test->stopCase(); if ($case !== null) { $test->startCase($case); } return $test; })
			->setHandler('mockGenerator', function() use ($test) { return $test->getMockGenerator(); })
			->setHandler('mockClass', function($class, $mockNamespace = null, $mockClass = null) use ($test) { $test->getMockGenerator()->generate($class, $mockNamespace, $mockClass); return $test; })
			->setHandler('mockTestedClass', function($mockNamespace = null, $mockClass = null) use ($test) { $test->getMockGenerator()->generate($test->getTestedClassName(), $mockNamespace, $mockClass); return $test; })
			->setHandler('dump', function() use ($test) { if ($test->debugModeIsEnabled() === true) { call_user_func_array('var_dump', func_get_args()); } return $test; })
			->setHandler('stop', function() use ($test) { if ($test->debugModeIsEnabled() === true) { throw new test\exceptions\stop(); } return $test; })
			->setHandler('executeOnFailure', function($callback) use ($test) { if ($test->debugModeIsEnabled() === true) { $test->executeOnFailure($callback); } return $test; })
			->setHandler('dumpOnFailure', function($variable) use ($test) { if ($test->debugModeIsEnabled() === true) { $test->executeOnFailure(function() use ($variable) { var_dump($variable); }); } return $test; })
			->setPropertyHandler('function', function() use ($test) { return $test->getPhpMocker(); })
			->setPropertyHandler('exception', function() { return asserters\exception::getLastValue(); })
		;

		$mockGenerator = $this->mockGenerator;

		$this->assertionManager
			->setPropertyHandler('nextMockedMethod', function() use ($mockGenerator) { return $mockGenerator->getMethod(); })
		;

		$returnTest = function() use ($test) { return $test; };

		$this->assertionManager
			->setHandler('if', $returnTest)
			->setHandler('and', $returnTest)
			->setHandler('then', $returnTest)
			->setHandler('given', $returnTest)
			->setMethodHandler('define', $returnTest)
		;

		$returnMockController = function(mock\aggregator $mock) { return $mock->getMockController(); };

		$this->assertionManager
			->setHandler('calling', $returnMockController)
			->setHandler('ƒ', $returnMockController)

		;

		$this->assertionManager
			->setHandler('resetMock', function(mock\aggregator $mock) { return $mock->getMockController()->resetCalls(); })
			->setHandler('resetAdapter', function(test\adapter $adapter) { return $adapter->resetCalls(); })
		;

		$phpMocker = $this->phpMocker;

		$this->assertionManager->setHandler('resetFunction', function(test\adapter\invoker $invoker) use ($phpMocker) { $phpMocker->resetCalls($invoker->getFunction()); return $invoker; });

		$assertionAliaser = $this->assertionManager->getAliaser();

		$this->assertionManager
			->setPropertyHandler('define', function() use ($assertionAliaser, $test) { return $assertionAliaser; })
			->setHandler('from', function($class) use ($assertionAliaser, $test) { $assertionAliaser->from($class); return $test; })
			->setHandler('use', function($target) use ($assertionAliaser, $test) { $assertionAliaser->alias($target); return $test; })
			->setHandler('as', function($alias) use ($assertionAliaser, $test) { $assertionAliaser->to($alias); return $test; })
		;

		$asserterGenerator = $this->asserterGenerator;

		$this->assertionManager->setDefaultHandler(function($keyword, $arguments) use ($asserterGenerator, $assertionAliaser, & $lastAsserter) {
				static $lastAsserter = null;

				if ($lastAsserter !== null)
				{
					$realKeyword = $assertionAliaser->resolveAlias($keyword, get_class($lastAsserter));

					if ($realKeyword !== $keyword)
					{
						return call_user_func_array(array($lastAsserter, $realKeyword), $arguments);
					}
				}

				return ($lastAsserter = $asserterGenerator->getAsserterInstance($keyword, $arguments));
			}
		);

		$this->assertionManager
			->use('phpArray')->as('array')
			->use('phpArray')->as('in')
			->use('phpClass')->as('class')
			->use('phpFunction')->as('function')
			->use('calling')->as('method')
		;

		return $this;
	}

	public function getAsserterCallManager()
	{
		return $this->asserterCallManager;
	}

	public function setAsserterCallManager(asserters\adapter\call\manager $asserterCallManager = null)
	{
		$this->asserterCallManager = $asserterCallManager ?: new asserters\adapter\call\manager();

		return $this;
	}

	public function getMethodPhpVersions($methodName = null)
	{
		$versions = array();

		$classVersions = $this->testClass->getPhpVersions();

		if ($methodName === null)
		{
			foreach ($this->testClass as $testMethodName => $method)
			{
				$versions[$testMethodName] = array_merge($classVersions, $method->getPhpVersions());
			}
		}
		else
		{
			$testMethod = $this->testClass->getMethod($methodName);

			if (sizeof($testMethod->getPhpVersions()) === 0)
			{
				$versions = $classVersions;
			}
			else
			{
				$versions = array_merge($classVersions, $testMethod->getPhpVersions());
			}
		}

		return $versions;
	}

	public function getMandatoryMethodExtensions($methodName = null)
	{
		$extensions = array();

		$mandatoryClassExtensions = $this->testClass->getMandatoryExtensions();

		if ($methodName === null)
		{
			foreach ($this->testClass as $testMethodName => $method)
			{
				$extensions[$testMethodName] = array_merge($mandatoryClassExtensions, $method->getMandatoryExtensions());
			}
		}
		else
		{
			$extensions = array_merge($mandatoryClassExtensions, $this->testClass->getMethod($methodName)->getMandatoryExtensions());
		}

		return $extensions;
	}

	public function skip($message)
	{
		throw new test\exceptions\skip($message);
	}

	public function getAssertionManager()
	{
		return $this->assertionManager;
	}

	public function setClassEngine($engine)
	{
		$this->classEngine = (string) $engine;

		return $this;
	}

	public function getClassEngine()
	{
		return $this->classEngine;
	}

	public function classHasVoidMethods()
	{
		$this->classHasNotVoidMethods = false;
	}

	public function classHasNotVoidMethods()
	{
		$this->classHasNotVoidMethods = true;
	}

	public function methodIsNotVoid(test\method $method)
	{
		return $method->isVoid() === false && $this->classHasNotVoidMethods;
	}

	public function enableDebugMode()
	{
		$this->debugMode = true;

		return $this;
	}

	public function disableDebugMode()
	{
		$this->debugMode = false;

		return $this;
	}

	public function debugModeIsEnabled()
	{
		return $this->debugMode;
	}

	public function setXdebugConfig($value)
	{
		$this->xdebugConfig = $value;

		return $this;
	}

	public function getXdebugConfig()
	{
		return $this->xdebugConfig;
	}

	public function executeOnFailure(\closure $closure)
	{
		$this->executeOnFailure[] = $closure;

		return $this;
	}

	public function codeCoverageIsEnabled()
	{
		return $this->codeCoverage;
	}

	public function enableCodeCoverage()
	{
		$this->codeCoverage = $this->adapter->extension_loaded('xdebug');

		return $this;
	}

	public function disableCodeCoverage()
	{
		$this->codeCoverage = false;

		return $this;
	}

	public function setMaxChildrenNumber($number)
	{
		$number = (int) $number;

		if ($number < 1)
		{
			throw new exceptions\logic\invalidArgument('Maximum number of children must be greater or equal to 1');
		}

		$this->maxAsynchronousEngines = $number;

		return $this;
	}

	public function setBootstrapFile($path)
	{
		$this->bootstrapFile = $path;

		return $this;
	}

	public function getBootstrapFile()
	{
		return $this->bootstrapFile;
	}

	public function setTestNamespace($testNamespace)
	{
		$this->testNamespace = self::cleanNamespace($testNamespace);

		if ($this->testNamespace === '')
		{
			throw new exceptions\logic\invalidArgument('Test namespace must not be empty');
		}

		return $this;
	}

	public function getTestNamespace()
	{
		return $this->testNamespace ?: self::getNamespace();
	}

	public function setTestMethodPrefix($methodPrefix)
	{
		$methodPrefix = (string) $methodPrefix;

		if ($methodPrefix == '')
		{
			throw new exceptions\logic\invalidArgument('Test method prefix must not be empty');
		}

		$this->testMethodPrefix = $methodPrefix;

		if (static::isRegex($methodPrefix) === false)
		{
			$this->testClass->setMethodFilter(function($methodName) use ($methodPrefix) { return (stripos($methodName, $methodPrefix) === 0); });
		}
		else
		{
			$this->testClass->setMethodFilter(function($methodName) use ($methodPrefix) { return (preg_match($methodPrefix, $methodName) == true); });
		}

		return $this;
	}

	public function getTestMethodPrefix()
	{
		return $this->testMethodPrefix ?: self::getMethodPrefix();
	}

	public function setPhpPath($path)
	{
		$this->phpPath = (string) $path;

		return $this;
	}

	public function getPhpPath()
	{
		return $this->phpPath;
	}

	public function getTestedClassName()
	{
		if ($this->testedClassName === null)
		{
			$this->testedClassName = self::getTestedClassNameFromTestClass($this->getClass(), $this->getTestNamespace());
		}

		return $this->testedClassName;
	}

	public function getTestedClassNamespace()
	{
		$testedClassName = $this->getTestedClassName();

		return substr($testedClassName, 0, strrpos($testedClassName, '\\'));
	}

	public function getTestedClassPath()
	{
		if ($this->testedClassPath === null)
		{
			$testedClass = new \reflectionClass($this->getTestedClassName());

			$this->testedClassPath = $testedClass->getFilename();
		}

		return $this->testedClassPath;
	}

	public function setTestedClassName($className)
	{
		if ($this->testedClassName !== null)
		{
			throw new exceptions\runtime('Tested class name is already defined');
		}

		$this->testedClassName = $className;

		return $this;
	}

	public function getClass()
	{
		return $this->class;
	}

	public function getClassNamespace()
	{
		return $this->classNamespace;
	}

	public function getPath()
	{
		return $this->path;
	}

	public function getTaggedTestMethods(array $methods, array $tags = array())
	{
		$taggedMethods = array();

		foreach ($this->getTestMethods($tags) as $method)
		{
			if (sizeof(array_uintersect($methods, array($method->getName()), 'strcasecmp')) > 0)
			{
				$taggedMethods[$method->getName()] = $method;
			}
		}

		return $taggedMethods;
	}

	public function getTestMethod($methodName)
	{
		return $this->testClass->getMethod($methodName);
	}

	public function getTestMethods(array $tags = array())
	{
		$testMethods = array();

		foreach ($this->testClass as $methodName => $method)
		{
			if ($this->methodIsIgnored($methodName, $tags) === false)
			{
				$testMethods[$method->getName()] = $method;
			}
		}

		return $testMethods;
	}

	public function getCurrentMethod()
	{
		return $this->currentMethod;
	}

	public function getMaxChildrenNumber()
	{
		return $this->maxAsynchronousEngines;
	}

	public function getCoverage()
	{
		return $this->score->getCoverage();
	}

	public function count()
	{
		return sizeof($this->runTestMethods);
	}

	public function addObserver(observer $observer)
	{
		$this->observers->attach($observer);

		return $this;
	}

	public function removeObserver(atoum\observer $observer)
	{
		$this->observers->detach($observer);

		return $this;
	}

	public function getObservers()
	{
		return iterator_to_array($this->observers);
	}

	public function callObservers($event)
	{
		foreach ($this->observers as $observer)
		{
			$observer->handleEvent($event, $this);
		}

		return $this;
	}

	public function isIgnored(array $namespaces = array(), array $tags = array())
	{
		$isIgnored = (sizeof($this) <= 0 || $this->testClass->isIgnored());

		if ($isIgnored === false && sizeof($namespaces) > 0)
		{
			$classNamespace = strtolower($this->getClassNamespace());

			$isIgnored = sizeof(array_filter($namespaces, function($value) use ($classNamespace) { return strpos($classNamespace, strtolower($value)) === 0; })) <= 0;
		}

		if ($isIgnored === false && sizeof($tags) > 0)
		{
			$isIgnored = sizeof($testTags = $this->testClass->getAllTags()) <= 0 || sizeof(array_intersect($tags, $testTags)) == 0;
		}

		return $isIgnored;
	}

	public function methodIsIgnored($methodName, array $tags = array())
	{
		$isIgnored = $this->testClass->isIgnored();

		if ($isIgnored === false && sizeof($tags) > 0)
		{
			$isIgnored = sizeof($methodTags = $this->testClass->getMethodTags($methodName)) <= 0 || sizeof(array_intersect($tags, $methodTags)) <= 0;
		}

		return $isIgnored;
	}

	public function runTestMethods(array $methods)
	{
		$this->runTestMethods = $runTestMethods = array();

		$testClass = $this->getClass();

		if (isset($methods[$testClass]) === true)
		{
			$runTestMethods = $methods[$testClass];
		}

		if (sizeof($runTestMethods) <= 0)
		{
			$this->runTestMethods = $this->getTestMethods();
		}
		else
		{
			$this->runTestMethods = $this->getTaggedTestMethods($runTestMethods);
		}

		return $this;
	}

	public function runTestMethod($testMethodName, array $tags = array())
	{
		$testMethod = $this->testClass->getMethod($testMethodName);

		if ($this->methodIsIgnored($testMethod->getName(), $tags) === false)
		{
			$this->mockAutoloader->setMockGenerator($this->mockGenerator)->register();

			set_error_handler(array($this, 'errorHandler'));

			ini_set('display_errors', 'stderr');
			ini_set('log_errors', 'Off');
			ini_set('log_errors_max_len', '0');

			$this->currentMethod = $testMethod;
			$this->executeOnFailure = array();

			$this->phpMocker->setDefaultNamespace($this->getTestedClassNamespace());

			try
			{
				foreach ($this->getMethodPhpVersions($testMethod->getName()) as $phpVersion => $operator)
				{
					if (version_compare(phpversion(), $phpVersion, $operator) === false)
					{
						throw new test\exceptions\skip('PHP version ' . PHP_VERSION . ' is not ' . $operator . ' to ' . $phpVersion);
					}
				}

				foreach ($this->getMandatoryMethodExtensions($testMethod->getName()) as $mandatoryExtension)
				{
					$this->extension($mandatoryExtension)->isLoaded();
				}

				try
				{
					ob_start();

					try
					{
						$testedClass = new \reflectionClass($testedClassName = $this->getTestedClassName());
					}
					catch (\exception $exception)
					{
						throw new exceptions\runtime('Tested class \'' . $testedClassName . '\' does not exist for test class \'' . $this->getClass() . '\'');
					}

					if ($testedClass->isAbstract() === true)
					{
						$testedClass = new \reflectionClass($testedClassName = $this->mockGenerator->getDefaultNamespace() . '\\' . $testedClassName);
					}

					$this->factoryBuilder->build($testedClass, $instance)
						->addToAssertionManager($this->assertionManager, 'newTestedInstance', function() use ($testedClass) {
								throw new exceptions\runtime('Tested class ' . $testedClass->getName() . ' has no constructor or its constructor has at least one mandatory argument');
							}
						)
					;

					$this->factoryBuilder->build($testedClass)
						->addToAssertionManager($this->assertionManager, 'newInstance', function() use ($testedClass) {
								throw new exceptions\runtime('Tested class ' . $testedClass->getName() . ' has no constructor or its constructor has at least one mandatory argument');
							}
						)
					;

					$this->assertionManager->setPropertyHandler('testedInstance', function() use (& $instance) {
							if ($instance === null)
							{
								throw new exceptions\runtime('Use $this->newTestedInstance before using $this->testedInstance');
							}

							return $instance;
						}
					);

					test\adapter::setStorage($this->testAdapterStorage);
					mock\controller::setLinker($this->mockControllerLinker);

					$this->testAdapterStorage->add(php\mocker::getAdapter());

					$this->beforeTestMethod($this->currentMethod);

					if ($this->codeCoverageIsEnabled() === true)
					{
						xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE);
					}

					$assertionNumber = $this->score->getAssertionNumber();
					$time = microtime(true);
					$memory = memory_get_usage(true);

					if ($testMethod->getDataProvider() === null)
					{
						$this->{$testMethod->getName()}();

						$this->asserterCallManager->check();
					}
					else
					{
						$data = $this->{$testMethod->getDataProvider()->getName()}();

						if (is_array($data) === false && $data instanceof \traversable === false)
						{
							throw new test\exceptions\runtime('Data provider ' . $this->getClass() . '::' . $testMethod->getDataProvider()->getName() . '() must return an array or an iterator');
						}

						$reflectedTestMethod = call_user_func($this->reflectionMethodFactory, $this, $testMethod);
						$numberOfArguments = $reflectedTestMethod->getNumberOfRequiredParameters();

						foreach ($data as $key => $arguments)
						{
							if (is_array($arguments) === false)
							{
								$arguments = array($arguments);
							}

							if (sizeof($arguments) != $numberOfArguments)
							{
								throw new test\exceptions\runtime('Data provider ' . $this->getClass() . '::' . $testMethod->getDataProvider()->getName() . '() not provide enough arguments at key ' . $key . ' for test method ' . $this->getClass() . '::' . $testMethod . '()');
							}

							$this->score->setDataSet($key, $testMethod->getDataProvider()->getName());

							$reflectedTestMethod->invokeArgs($this, $arguments);

							$this->asserterCallManager->check();

							$this->score->unsetDataSet();
						}
					}

					$this->mockControllerLinker->reset();
					$this->testAdapterStorage->reset();

					$memoryUsage = memory_get_usage(true) - $memory;
					$duration = microtime(true) - $time;

					$this->score
						->addMemoryUsage($this->path, $this->class, $this->currentMethod->getName(), $memoryUsage)
						->addDuration($this->path, $this->class, $this->currentMethod->getName(), $duration)
						->addOutput($this->path, $this->class, $this->currentMethod->getName(), ob_get_clean())
					;

					if ($this->codeCoverageIsEnabled() === true)
					{
						$this->score->getCoverage()->addXdebugDataForTest($this, xdebug_get_code_coverage());
						xdebug_stop_code_coverage();
					}

					if ($assertionNumber == $this->score->getAssertionNumber() && $this->methodIsNotVoid($this->currentMethod) === false)
					{
						$this->score->addVoidMethod($this->path, $this->class, $this->currentMethod->getName());
					}
				}
				catch (\exception $exception)
				{
					$this->score->addOutput($this->path, $this->class, $this->currentMethod->getName(), ob_get_clean());

					throw $exception;
				}
			}
			catch (asserter\exception $exception)
			{
				foreach ($this->executeOnFailure as $closure)
				{
					ob_start();
					$closure();
					$this->score->addOutput($this->path, $this->class, $this->currentMethod->getName(), ob_get_clean());
				}

				if ($this->score->failExists($exception) === false)
				{
					$this->addExceptionToScore($exception);
				}
			}
			catch (test\exceptions\runtime $exception)
			{
				$this->score->addRuntimeException($this->path, $this->class, $this->currentMethod->getName(), $exception);
			}
			catch (test\exceptions\skip $exception)
			{
				list($file, $line) = $this->getBacktrace($exception->getTrace());

				$this->score->addSkippedMethod($file, $this->class, $this->currentMethod->getName(), $line, $exception->getMessage());
			}
			catch (test\exceptions\stop $exception)
			{
			}
			catch (exception $exception)
			{
				list($file, $line) = $this->getBacktrace($exception->getTrace());

				$this->errorHandler(E_USER_ERROR, $exception->getMessage(), $file, $line);
			}
			catch (\exception $exception)
			{
				$this->addExceptionToScore($exception);
			}

			$this->afterTestMethod($this->currentMethod->getName());

			$this->currentMethod = null;

			restore_error_handler();

			ini_restore('display_errors');
			ini_restore('log_errors');
			ini_restore('log_errors_max_len');

			$this->mockAutoloader->unregister();
		}

		return $this;
	}

	public function run(array $runTestMethods = array(), array $tags = array())
	{
		if ($runTestMethods)
		{
			$this->runTestMethods(array_intersect($runTestMethods, $this->getTestMethods($tags)));
		}

		if ($this->isIgnored() === false)
		{
			$this->callObservers(self::runStart);

			try
			{
				$this->runEngines();
			}
			catch (\exception $exception)
			{
				$this->stopEngines();

				throw $exception;
			}

			$this->callObservers(self::runStop);
		}

		return $this;
	}

	public function startCase($case)
	{
		$this->testAdapterStorage->resetCalls();
		$this->score->setCase($case);

		return $this;
	}

	public function stopCase()
	{
		$this->testAdapterStorage->resetCalls();
		$this->score->unsetCase();

		return $this;
	}

	public function setDataProvider($testMethodName, $dataProvider = null)
	{
		if ($dataProvider === null)
		{
			$dataProvider = $testMethodName . 'DataProvider';
		}

		try
		{
			$this->testClass->getMethod($testMethodName)->setDataProvider(new \reflectionMethod($this, $dataProvider));
		}
		catch(\reflectionException $e)
		{
			throw new exceptions\logic\invalidArgument('Data provider ' . $this->class . '::' . lcfirst($dataProvider) . '() is unknown');
		}


		return $this;
	}

	public function errorHandler($errno, $errstr, $errfile, $errline)
	{
		$doNotCallDefaultErrorHandler = true;
		$errorReporting = $this->adapter->error_reporting();

		if ($errorReporting !== 0 && $errorReporting & $errno)
		{
			list($file, $line) = $this->getBacktrace();

			$method = $this->currentMethod !== null ? $this->currentMethod->getName() : null;
			$this->score->addError($file ?: ($errfile ?: $this->path), $this->class, $method, $line ?: $errline, $errno, trim($errstr), $errfile, $errline);

			$doNotCallDefaultErrorHandler = !($errno & E_RECOVERABLE_ERROR);
		}

		return $doNotCallDefaultErrorHandler;
	}

	public function setUp() {}

	public function beforeTestMethod($testMethod) {}

	public function afterTestMethod($testMethod) {}

	public function tearDown() {}

	public static function setNamespace($namespace)
	{
		$namespace = self::cleanNamespace($namespace);

		if ($namespace === '')
		{
			throw new exceptions\logic\invalidArgument('Namespace must not be empty');
		}

		self::$namespace = $namespace;
	}

	public static function getNamespace()
	{
		return self::$namespace ?: static::defaultNamespace;
	}

	public static function setMethodPrefix($methodPrefix)
	{
		if ($methodPrefix == '')
		{
			throw new exceptions\logic\invalidArgument('Method prefix must not be empty');
		}

		self::$methodPrefix = $methodPrefix;
	}

	public static function getMethodPrefix()
	{
		return self::$methodPrefix ?: static::defaultMethodPrefix;
	}

	public static function setDefaultEngine($defaultEngine)
	{
		self::$defaultEngine = (string) $defaultEngine;
	}

	public static function getDefaultEngine()
	{
		return self::$defaultEngine ?: self::defaultEngine;
	}

	public static function getTestedClassNameFromTestClass($fullyQualifiedClassName, $testNamespace = null)
	{
		if ($testNamespace === null)
		{
			$testNamespace = self::getNamespace();
		}

		if (self::isRegex($testNamespace) === true)
		{
			if (preg_match($testNamespace, $fullyQualifiedClassName) === 0)
			{
				throw new exceptions\runtime('Test class \'' . $fullyQualifiedClassName . '\' is not in a namespace which match pattern \'' . $testNamespace . '\'');
			}

			$testedClassName = preg_replace($testNamespace, '\\', $fullyQualifiedClassName);
		}
		else
		{
			$position = strpos($fullyQualifiedClassName, $testNamespace);

			if ($position === false)
			{
				throw new exceptions\runtime('Test class \'' . $fullyQualifiedClassName . '\' is not in a namespace which contains \'' . $testNamespace . '\'');
			}

			$testedClassName = substr($fullyQualifiedClassName, 0, $position) . substr($fullyQualifiedClassName, $position + 1 + strlen($testNamespace));
		}

		return trim($testedClassName, '\\');
	}

	protected function setClassAnnotations(annotations\extractor $extractor)
	{
		$test = $this;
		$class = $this->testClass;

		$extractor
			->resetHandlers()
			->setHandler('ignore', function($value) use ($class) { $class->ignore(annotations\extractor::toBoolean($value)); })
			->setHandler('tags', function($value) use ($class) { $class->setTags(annotations\extractor::toArray($value)); })
			->setHandler('namespace', function($value) use ($test) { $test->setTestNamespace($value === true ? static::defaultNamespace : $value); })
			->setHandler('methodPrefix', function($value) use ($test) { $test->setTestMethodPrefix($value === true ? static::defaultMethodPrefix : $value); })
			->setHandler('maxChildrenNumber', function($value) use ($test) { $test->setMaxChildrenNumber($value); })
			->setHandler('engine', function($value) use ($test) { $test->setClassEngine($value); })
			->setHandler('hasVoidMethods', function($value) use ($test) { $test->classHasVoidMethods(); })
			->setHandler('hasNotVoidMethods', function($value) use ($test) { $test->classHasNotVoidMethods(); })
			->setHandler('php', function($value) use ($class) {
					$value = annotations\extractor::toArray($value);

					if (isset($value[0]) === true)
					{
						$operator = null;

						if (isset($value[1]) === false)
						{
							$version = $value[0];
						}
						else
						{
							$version = $value[1];

							switch ($value[0])
							{
								case '<':
								case '<=':
								case '=':
								case '==':
								case '>=':
								case '>':
									$operator = $value[0];
							}
						}

						$class->addPhpVersion($version, $operator);
					}
				}
			)
			->setHandler('extensions', function($value) use ($class) {
					foreach (annotations\extractor::toArray($value) as $mandatoryExtension)
					{
						$class->addMandatoryExtension($mandatoryExtension);
					}
				}
			)
		;

		return $this;
	}

	protected function getBacktrace(array $trace = null)
	{
		$debugBacktrace = $trace === null ? debug_backtrace(false) : $trace;

		foreach ($debugBacktrace as $key => $value)
		{
			if (isset($value['class']) === true && $value['class'] === $this->class && isset($value['function']) === true && $value['function'] === $this->currentMethod->getName())
			{
				if (isset($debugBacktrace[$key - 1]) === true)
				{
					$key -= 1;
				}

				return array(
					$debugBacktrace[$key]['file'],
					$debugBacktrace[$key]['line']
				);
			}
		}

		return null;
	}

	private function addExceptionToScore(\exception $exception)
	{
		list($file, $line) = $this->getBacktrace($exception->getTrace());

		$this->score->addException($file, $this->class, $this->currentMethod->getName(), $line, $exception);

		return $this;
	}

	private function runEngines()
	{
		$this->callObservers(self::beforeSetUp);
		$this->setUp();
		$this->callObservers(self::afterSetUp);

		while ($this->runEngine()->methods)
		{
			$engines = $this->methods;

			foreach ($engines as $methodName => $engine)
			{
				$this->currentMethod = $this->testClass->getMethod($methodName);
				$score = $engine->getScore();

				if ($score !== null)
				{
					unset($this->methods[$methodName]);

					$this
						->callObservers(self::afterTestMethod)
						->score
							->merge($score)
					;

					$runtimeExceptions = $score->getRuntimeExceptions();

					if (sizeof($runtimeExceptions) > 0)
					{
						$this->callObservers(self::runtimeException);

						throw reset($runtimeExceptions);
					}
					else
					{
						switch (true)
						{
							case $score->getVoidMethodNumber():
								$signal = self::void;
								break;

							case $score->getUncompletedMethodNumber():
								$signal = self::uncompleted;
								break;

							case $score->getSkippedMethodNumber():
								$signal = self::skipped;
								break;

							case $score->getFailNumber():
								$signal = self::fail;
								break;

							case $score->getErrorNumber():
								$signal = self::error;
								break;

							case $score->getExceptionNumber():
								$signal = self::exception;
								break;

							default:
								$signal = self::success;
						}

						$this->callObservers($signal);
					}

					if ($engine->isAsynchronous() === true)
					{
						$this->asynchronousEngines--;
					}
				}
			}

			$this->currentMethod = null;
		}

		return $this->doTearDown();
	}

	private function stopEngines()
	{
		while ($this->methods)
		{
			$engines = $this->methods;

			foreach ($engines as $currentMethod => $engine)
			{
				if ($engine->getScore() !== null)
				{
					unset($this->methods[$currentMethod]);
				}
			}
		}

		return $this->doTearDown();
	}

	private function runEngine()
	{
		$method = reset($this->runTestMethods);

		if ($method !== false)
		{
			$this->currentMethod = current($this->runTestMethods);

			if ($this->canRunMethod($this->currentMethod) === true)
			{
				unset($this->runTestMethods[$this->currentMethod->getName()]);

				$this->methods[$this->currentMethod->getName()] = $method->run($this->callObservers(self::beforeTestMethod));

				if ($method->isAsynchronous() === true)
				{
					$this->asynchronousEngines++;
				}
			}

			$this->currentMethod = null;
		}

		return $this;
	}

	private function canRunMethod(test\method $method)
	{
		return ($method->isAsynchronous() === false || $this->maxAsynchronousEngines === null || $this->asynchronousEngines < $this->maxAsynchronousEngines);
	}

	private function doTearDown()
	{
		$this->callObservers(self::beforeTearDown);
		$this->tearDown();
		$this->callObservers(self::afterTearDown);

		return $this;
	}

	public function getExtensions()
	{
		return iterator_to_array($this->extensions);
	}

	public function removeExtension(atoum\extension $extension)
	{
		$this->extensions->detach($extension);

		return $this->removeObserver($extension);
	}

	public function removeExtensions()
	{
		foreach ($this->extensions as $extension)
		{
			$this->removeObserver($extension);
		}

		$this->extensions = new \splObjectStorage();

		return $this;
	}


	public function addExtension(atoum\extension $extension)
	{
		if ($this->extensions->contains($extension) === false)
		{
			$extension->setTest($this);

			$this->extensions->attach($extension);

			$this->addObserver($extension);
		}

		return $this;
	}

	public function addExtensions(\traversable $extensions)
	{
		foreach ($extensions as $extension)
		{
			$this->addExtension($extension);
		}

		return $this;
	}

	private static function cleanNamespace($namespace)
	{
		return trim((string) $namespace, '\\');
	}

	private static function isRegex($namespace)
	{
		return preg_match('/^([^\\\[:alnum:][:space:]]).*\1.*$/', $namespace) === 1;
	}
}

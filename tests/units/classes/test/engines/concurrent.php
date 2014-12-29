<?php

namespace mageekguy\atoum\tests\units\test\engines;

require_once __DIR__ . '/../../../runner.php';

use
	mageekguy\atoum,
	mageekguy\atoum\test\engines\concurrent as testedClass
;

class concurrent extends atoum\test
{
	public function testClass()
	{
		$this->testedClass->extends('mageekguy\atoum\test\engine');
	}

	public function test__construct()
	{
		$this
			->if($engine = new testedClass())
			->then
				->object($defaultScoreFactory = $engine->getScoreFactory())->isInstanceOf('closure')
				->object($defaultScoreFactory())->isInstanceOf('mageekguy\atoum\score')
				->object($engine->getPhp())->isEqualTo(new atoum\php())
		;
	}

	public function testSetPhp()
	{
		$this
			->if($engine = new testedClass())
			->then
				->object($engine->setPhp($php = new atoum\php()))->isIdenticalTo($engine)
				->object($engine->getPhp())->isIdenticalTo($php)
				->object($engine->setPhp())->isIdenticalTo($engine)
				->object($engine->getPhp())
					->isEqualTo(new atoum\php())
					->isNotIdenticalTo($php)
		;
	}

	public function testIsAsynchronous()
	{
		$this
			->if($engine = new testedClass())
			->then
				->boolean($engine->isAsynchronous())->isTrue()
		;
	}

	public function testRun()
	{
		$this
			->if($engine = new testedClass())
			->and($engine->setPhp($php = new \mock\mageekguy\atoum\php()))
			->then
				->object($engine->run($test = new \mock\mageekguy\atoum\test()))->isIdenticalTo($engine)
			->if(
				$this->mockGenerator->shuntParentClassCalls(),
				$reflection = new \mock\reflectionMethod(uniqid(), $methodName = uniqid()),
				$this->calling($reflection)->getName = $methodName,
				$this->calling($test)->getCurrentMethod = $method = new atoum\test\method($reflection)
			)
			->and($test->getMockController()->getPath = $testPath = uniqid())
			->and($test->getMockController()->getPhpPath = $phpPath = uniqid())
			->and($test->getMockController()->codeCoverageIsEnabled = false)
			->and($test->getMockController()->getBootstrapFile = null)
			->and($test->setXdebugConfig($xdebugConfig = uniqid()))
			->and($this->calling($php)->run->throw = $exception = new atoum\php\exception())
			->and($this->function->getenv = false)
			->and($this->function->ini_get = 0)
			->then
				->exception(function() use ($engine, $test) { $engine->run($test); })
					->isIdenticalTo($exception)
			->if($this->calling($php)->run = $php)
			->then
				->object($engine->run($test))->isIdenticalTo($engine)
				->mock($php)
					->call('run')->withArguments(
						'<?php ' .
						'ob_start();' .
						'require \'' . atoum\directory . '/classes/autoloader.php\';' .
						'require \'' . $testPath . '\';' .
						'$test = new ' . get_class($test) . '();' .
						'$test->setLocale(new ' . get_class($test->getLocale()) . '(' . $test->getLocale()->get() . '));' .
						'$test->setPhpPath(\'' . $phpPath . '\');' .
						'$test->disableCodeCoverage();' .
						'ob_end_clean();' .
						'mageekguy\atoum\scripts\runner::disableAutorun();' .
						'echo serialize($test->runTestMethod(\'' . $methodName . '\')->getScore());'
					)->twice()
					->call('__set')->withArguments('XDEBUG_CONFIG', $xdebugConfig)->twice()
		;
	}

	public function testGetScore()
	{
		$this
			->if($engine = new testedClass())
			->and($engine->setPhp($php = new \mock\mageekguy\atoum\php()))
			->and($this->calling($php)->run = $php)
			->and($this->calling($php)->isRunning = false)
			->then
				->variable($engine->getScore())->isNull()
			->if($engine->run($test = new \mock\mageekguy\atoum\test()))
			->and($this->calling($php)->isRunning = true)
			->then
				->variable($engine->getScore())->isNull()
			->if(
				$this->mockGenerator->shuntParentClassCalls(),
				$reflection = new \mock\reflectionMethod(uniqid(), $methodName = uniqid()),
				$this->calling($reflection)->getName = $methodName,
				$this->calling($test)->getCurrentMethod = $method = new atoum\test\method($reflection)
			)
			->and($this->calling($test)->getPath = $testPath = uniqid())
			->and($this->calling($test)->getPhpPath = $phpPath = uniqid())
			->and($this->calling($test)->codeCoverageIsEnabled = false)
			->and($this->calling($test)->getBootstrapFile = null)
			->and($this->calling($php)->isRunning = false)
			->and($this->calling($php)->getStdOut = $output = uniqid())
			->and($this->calling($php)->getExitCode = $exitCode = uniqid())
			->and($engine->run($test))
			->then
				->object($score = $engine->getScore())->isInstanceOf('mageekguy\atoum\score')
				->array($score->getUncompletedMethods())->isEqualTo(array(array('file' => $testPath, 'class' => get_class($test), 'method' => $methodName, 'exitCode' => $exitCode, 'output' => $output)))
			->if($this->calling($php)->getStdOut = serialize($score))
			->and($engine->run($test))
			->then
				->object($score = $engine->getScore())->isEqualTo($score)
		;
	}
}

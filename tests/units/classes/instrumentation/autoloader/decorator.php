<?php

namespace mageekguy\atoum\tests\units\instrumentation\autoloader;

use
	mageekguy\atoum,
	mageekguy\atoum\test,
	mageekguy\atoum\adapter,
	mageekguy\atoum\instrumentation\stream,
	mageekguy\atoum\instrumentation\autoloader\decorator as testedClass
;

require_once __DIR__ . '/../../../runner.php';

class decorator extends test
{
	public function testClass()
	{
		$this->testedClass->isSubclassOf('mageekguy\atoum\autoloader\decorator');
	}

	public function testIgnorePath()
	{
		$this
			->if($autoloader = new testedClass())
			->then
				->object($autoloader->ignorePath($path = uniqid()))->isIdenticalTo($autoloader)
				->array($autoloader->getIgnoredPaths())->isEqualTo(array(dirname(dirname($this->getTestedClassPath())), $path))
				->object($autoloader->ignorePath($path))->isIdenticalTo($autoloader)
				->array($autoloader->getIgnoredPaths())->isEqualTo(array(dirname(dirname($this->getTestedClassPath())), $path))
				->object($autoloader->ignorePath($otherPath = uniqid()))->isIdenticalTo($autoloader)
				->array($autoloader->getIgnoredPaths())->isEqualTo(array(dirname(dirname($this->getTestedClassPath())), $path, $otherPath))
		;
	}

	public function testIsIgnored()
	{
		$this
			->if($autoloader = new testedClass())
			->then
				->boolean($autoloader->isIgnored($this->getTestedClassPath()))->isTrue()
				->boolean($autoloader->isIgnored($this->getTestedClassPath() . DIRECTORY_SEPARATOR . uniqid()))->isTrue()
				->boolean($autoloader->isIgnored($file = uniqid()))->isFalse()
				->boolean($autoloader->isIgnored($directory = uniqid() . DIRECTORY_SEPARATOR . $file))->isFalse()
			->if($autoloader->ignorePath($directory . DIRECTORY_SEPARATOR . $file))
			->then
				->boolean($autoloader->isIgnored($file))->isFalse()
				->boolean($autoloader->isIgnored($directory . DIRECTORY_SEPARATOR . $file))->isTrue()
				->boolean($autoloader->isIgnored(uniqid() . DIRECTORY_SEPARATOR . $directory . DIRECTORY_SEPARATOR . $file))->isFalse()
			->if($autoloader->ignorePath($directory = uniqid()))
			->then
				->boolean($autoloader->isIgnored($directory . DIRECTORY_SEPARATOR . $file))->isTrue()
		;
	}

	public function testEnableDisableInstrumentation()
	{
		$this
			->if($autoloader = new testedClass())
			->then
				->boolean($autoloader->instrumentationEnabled())->isTrue()
				->object($autoloader->disableInstrumentation())->isIdenticalTo($autoloader)
				->boolean($autoloader->instrumentationEnabled())->isFalse()
				->object($autoloader->enableInstrumentation())->isIdenticalTo($autoloader)
				->boolean($autoloader->instrumentationEnabled())->isTrue()
		;
	}

	public function testEnableDisableMoleInstrumentation()
	{
		$this
			->if($autoloader = new testedClass())
			->then
				->boolean($autoloader->moleInstrumentationEnabled())->isTrue()
				->object($autoloader->disableMoleInstrumentation())->isIdenticalTo($autoloader)
				->boolean($autoloader->moleInstrumentationEnabled())->isFalse()
				->object($autoloader->enableMoleInstrumentation())->isIdenticalTo($autoloader)
				->boolean($autoloader->moleInstrumentationEnabled())->isTrue()
		;
	}

	public function testEnableDisableCoverageInstrumentation()
	{
		$this
			->if($autoloader = new testedClass())
			->then
				->boolean($autoloader->coverageInstrumentationEnabled())->isTrue()
				->object($autoloader->disableCoverageInstrumentation())->isIdenticalTo($autoloader)
				->boolean($autoloader->coverageInstrumentationEnabled())->isFalse()
				->object($autoloader->enableCoverageInstrumentation())->isIdenticalTo($autoloader)
				->boolean($autoloader->coverageInstrumentationEnabled())->isTrue()
		;
	}

	public function testGetInstrumentedPath()
	{
		$this
			->if($autoloader = new testedClass())
			->then
				->string($autoloader->getInstrumentedPath($path = uniqid()))->isEqualTo(stream::defaultProtocol . stream::protocolSeparator . $path)
			->if($autoloader->disableMoleInstrumentation())
			->then
				->string($autoloader->getInstrumentedPath($path = uniqid()))->isEqualTo(stream::defaultProtocol . stream::protocolSeparator . 'options=-moles/' . $path)
			->if($autoloader->disableCoverageInstrumentation())
			->then
				->string($autoloader->getInstrumentedPath($path = uniqid()))->isEqualTo(stream::defaultProtocol . stream::protocolSeparator . 'options=-moles,-coverage-transition/' . $path)
			->if($autoloader->enableMoleInstrumentation())
			->then
				->string($autoloader->getInstrumentedPath($path = uniqid()))->isEqualTo(stream::defaultProtocol . stream::protocolSeparator . 'options=-coverage-transition/' . $path)
			->if($autoloader->enableCoverageInstrumentation())
			->then
				->string($autoloader->getInstrumentedPath($path = uniqid()))->isEqualTo(stream::defaultProtocol . stream::protocolSeparator . $path)
		;
	}
}

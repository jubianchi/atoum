<?php

namespace mageekguy\atoum\tests\units\instrumentation;

use
	mageekguy\atoum\test,
	mageekguy\atoum\adapter,
	mageekguy\atoum\instrumentation\stream as testedClass
;

require_once __DIR__ . '/../../runner.php';

class stream extends test
{
	public function testClassConstants()
	{
		$this
			->string(testedClass::defaultProtocol)->isEqualTo('instrumentation')
			->string(testedClass::protocolSeparator)->isEqualTo('://')
		;
	}

	public function testGetAdapter()
	{
		$this
			->object(testedClass::getAdapter())->isEqualTo(new adapter())
			->if(testedClass::setAdapter($adapter = new adapter()))
			->then
				->object(testedClass::getAdapter())->isIdenticalTo($adapter)
		;
	}

	public function testSet()
	{
		$this
			->if(testedClass::setAdapter($adapter = new test\adapter()))
			->and($adapter->stream_get_wrappers = array())
			->and($adapter->stream_wrapper_register = true)
			->then
				->object($streamController = testedClass::set())->isInstanceOf('mageekguy\atoum\instrumentation\stream\controller')
				->variable($streamController->getStream())->isNull()
				->variable($streamController->getStreamName())->isNull()
				->adapter($adapter)
					->call('stream_wrapper_register')->withArguments(testedClass::defaultProtocol, 'mageekguy\atoum\instrumentation\stream')->once()
			->if($adapter->stream_get_wrappers = array())
			->and($adapter->stream_wrapper_register = false)
			->then
				->exception(function() { testedClass::set(); })
					->isInstanceOf('mageekguy\atoum\exceptions\runtime')
					->hasMessage('Unable to register ' . testedClass::defaultProtocol . ' stream')
		;
	}

	public function testSetDirectorySeparator()
	{
		$this
			->string(testedClass::setDirectorySeparator('foo/bar', '/'))->isEqualTo('foo/bar')
			->string(testedClass::setDirectorySeparator('foo\bar', '/'))->isEqualTo('foo/bar')
			->string(testedClass::setDirectorySeparator('foo/bar', '\\'))->isEqualTo('foo\bar')
			->string(testedClass::setDirectorySeparator('foo\bar', '\\'))->isEqualTo('foo\bar')
			->string(testedClass::setDirectorySeparator('foo' . DIRECTORY_SEPARATOR . 'bar'))->isEqualTo('foo' . DIRECTORY_SEPARATOR . 'bar')
			->string(testedClass::setDirectorySeparator('foo' . (DIRECTORY_SEPARATOR == '/' ? '\\' : '/') . 'bar'))->isEqualTo('foo' . DIRECTORY_SEPARATOR . 'bar')
		;
	}
}

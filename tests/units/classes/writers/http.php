<?php

namespace mageekguy\atoum\tests\units\writers;

use
	mageekguy\atoum,
	mageekguy\atoum\writers\http as testedClass
;

require_once __DIR__ . '/../../runner.php';

class http extends atoum\test
{
	public function testClass()
	{
		$this
			->testedClass
				->isSubclassOf('\\mageekguy\\atoum\\writer')
				->implements('mageekguy\atoum\report\writers\asynchronous')
		;
	}

	public function testWrite()
	{
		$this
			->if($adapter = new atoum\test\adapter())
			->and($adapter->file_get_contents = '')
			->and($adapter->stream_context_create = $context = uniqid())
			->and($file = new testedClass($url = uniqid(), null, null, array(), $adapter))
			->then
				->object($file->write($string = uniqid()))->isIdenticalTo($file)
				->adapter($adapter)
					->call('stream_context_create')->withArguments(array('http' => array('method'  => 'GET', 'header'  => '', 'content' => $string)))->once()
					->call('file_get_contents')->withArguments($url, false, $context)->once()
			->if($file = new testedClass($url, $method = uniqid(), null, array(), $adapter))
			->and($adapter->resetCalls())
			->then
				->object($file->write($string = uniqid()))->isIdenticalTo($file)
				->adapter($adapter)
					->call('stream_context_create')->withArguments(array('http' => array('method'  => $method, 'header'  => '', 'content' => $string)))->once()
					->call('file_get_contents')->withArguments($url, false, $context)->once()
			->if($file = new testedClass($url, $method, $param = uniqid(), array(), $adapter))
			->and($adapter->resetCalls())
			->then
				->object($file->write($string = uniqid()))->isIdenticalTo($file)
				->adapter($adapter)
					->call('stream_context_create')->withArguments(array('http' => array('method'  => $method, 'header'  => '', 'content' => http_build_query(array($param => $string)))))->once()
					->call('file_get_contents')->withArguments($url, false, $context)->once()
			->if($file = new testedClass($url, $method, null, array($header = uniqid() => $value = uniqid()), $adapter))
			->and($adapter->resetCalls())
			->then
				->object($file->write($string = uniqid()))->isIdenticalTo($file)
				->adapter($adapter)
					->call('stream_context_create')->withArguments(array('http' => array('method'  => $method, 'header'  => $header . ': ' . $value, 'content' => $string)))->once()
					->call('file_get_contents')->withArguments($url, false, $context)->once()
			->if($file = new testedClass($url, $method, null, array($header => $value, $otherHeader = uniqid() => $otherValue = uniqid()), $adapter))
			->and($adapter->resetCalls())
			->then
				->object($file->write($string = uniqid()))->isIdenticalTo($file)
				->adapter($adapter)
					->call('stream_context_create')->withArguments(array('http' => array('method'  => $method, 'header'  => $header . ': ' . $value . "\r\n" . $otherHeader . ': ' . $otherValue, 'content' => $string)))->once()
					->call('file_get_contents')->withArguments($url, false, $context)->once()
		;
	}

	public function testClear()
	{
		$this
			->if($file = new testedClass(uniqid()))
			->then
				->object($file->clear())->isIdenticalTo($file)
		;
	}

	public function testWriteAsynchronousReport()
	{
		$this
			->if($adapter = new atoum\test\adapter())
			->and($adapter->file_get_contents = '')
			->and($report = new \mock\mageekguy\atoum\reports\asynchronous())
			->and($file = new \mock\mageekguy\atoum\writers\http(uniqid(), null, null, array(), $adapter))
			->then
				->object($file->writeAsynchronousReport($report))->isIdenticalTo($file)
				->mock($file)->call('write')->withArguments($report->__toString())->once()
		;
	}
}

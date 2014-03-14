<?php

namespace mageekguy\atoum\tests\units\instrumentation\stream;

use
	mageekguy\atoum,
	mageekguy\atoum\test,
	mageekguy\atoum\mock\stream,
	mageekguy\atoum\mock\streams\fs\file,
	mageekguy\atoum\instrumentation\stream\controller as testedClass
;

require_once __DIR__ . '/../../../runner.php';

class controller extends atoum\test
{
	public function test__construct()
	{
		$this
			->if($controller = new testedClass())
			->then
				->object($controller->getAdapter())->isEqualTo(new atoum\adapter())
				->variable($controller->getStream())->isNull()
				->variable($controller->getStreamName())->isNull()
			->if($controller = new testedClass($adapter = new atoum\test\adapter()))
			->then
				->object($controller->getAdapter())->isIdenticalTo($adapter)
		;
	}

	public function testGetSetAdapter()
	{
		$this
			->if($controller = new testedClass())
			->then
				->object($adapter = $controller->getAdapter())->isEqualTo(new atoum\adapter())
				->object($controller->setAdapter())->isIdenticalTo($controller)
				->object($controller->getAdapter())
					->isNotIdenticalTo($adapter)
					->isEqualTo(new atoum\adapter())
			->if($controller->setAdapter($adapter = new atoum\test\adapter()))
			->then
				->object($controller->getAdapter())->isIdenticalTo($adapter)
		;
	}

	public function testGetStream()
	{
		$this
			->if($controller = new testedClass())
			->then
				->variable($controller->getStream())->isNull()
			->if($controller->setAdapter($adapter = new atoum\test\adapter()))
			->and($adapter->stream_filter_append = true)
			->and($file = file::get())
			->and($cache = new \mock\mageekguy\atoum\instrumentation\stream\cache(uniqid(), (string) $file))
			->and($this->calling($cache)->exists = false)
			->and($this->calling($cache)->lock = $cache)
			->and($this->calling($cache)->write = $cache)
			->and($cacheFactory = function() use ($cache) { return $cache; })
			->and($controller->setAdapter($adapter = new atoum\test\adapter()))
			->and($adapter->stream_filter_append = true)
			->and($controller->stream_open((string) $file, 'r', 0, $openedStream, $cacheFactory))
			->then
				->variable($controller->getStream())->isIdenticalTo($openedStream)
			->if($controller->stream_close())
			->then
				->variable($controller->getStream())->isNull()
		;
	}

	public function testGetStreamName()
	{
		$this
			->if($controller = new testedClass())
			->then
				->variable($controller->getStreamName())->isNull()
			->if($file = file::get())
			->and($cache = new \mock\mageekguy\atoum\instrumentation\stream\cache(uniqid(), (string) $file))
			->and($this->calling($cache)->exists = false)
			->and($this->calling($cache)->lock = $cache)
			->and($this->calling($cache)->write = $cache)
			->and($cacheFactory = function() use ($cache) { return $cache; })
			->and($controller->setAdapter($adapter = new atoum\test\adapter()))
			->and($adapter->stream_filter_append = true)
			->and($controller->stream_open((string) $file, 'r', 0, $openedStream, $cacheFactory))
			->then
				->string($controller->getStreamName())->isEqualTo((string) $file)
			->if($controller->stream_close())
			->then
				->variable($controller->getStreamName())->isNull()
		;
	}

	public function testStream_open()
	{
		$this
			->if($controller = new testedClass($adapter = new atoum\test\adapter()))
			->and($adapter->stream_filter_append = true)
			->and($adapter->flock = true)
			->and($adapter->fseek = 0)
			->and($adapter->stream_get_contents = null)
			->and($file = file::get())
			->and($cache = new \mock\mageekguy\atoum\instrumentation\stream\cache(uniqid(), (string) $file))
			->and($this->calling($cache)->exists = false)
			->and($this->calling($cache)->lock = $cache)
			->and($this->calling($cache)->write = $cache)
			->and($cacheFactory = function() use ($cache) { return $cache; })
			->then
				->variable($controller->stream_open($file, 'r', 0, $openedStream, $cacheFactory))->isIdenticalTo($openedStream)
				->variable($controller->getStream())->isIdenticalTo($openedStream)
				->string($controller->getStreamName())->isEqualTo((string) $file)
				->adapter($adapter)
					->call('fopen')->withArguments((string) $file, 'r', false)->once()
					->call('stream_filter_append')->withArguments($openedStream, atoum\instrumentation\stream::defaultProtocol, STREAM_FILTER_READ, array('moles' => true, 'coverage-transition' => true))->once()
				->variable($controller->stream_open($file, 'r', STREAM_USE_PATH, $openedStream, $cacheFactory))->isIdenticalTo($openedStream)
				->variable($controller->getStream())->isIdenticalTo($openedStream)
				->string($controller->getStreamName())->isEqualTo((string) $file)
				->adapter($adapter)
					->call('fopen')->withArguments((string) $file, 'r', true)->once()
					->call('stream_filter_append')->withArguments($openedStream, atoum\instrumentation\stream::defaultProtocol, STREAM_FILTER_READ, array('moles' => true, 'coverage-transition' => true))->once()
				->if($adapter->resetCalls())
				->and($adapter->fopen = false)
				->then
					->boolean($controller->stream_open($file, 'r', 0, $openedStream, $cacheFactory))->isFalse()
					->variable($controller->getStream())->isNull()
					->variable($controller->getStreamName())->isNull()
					->adapter($adapter)
						->call('fopen')->withArguments((string) $file, 'r', false)->once()
						->call('stream_filter_append')->never()
					->boolean($controller->stream_open($file, 'r', STREAM_USE_PATH, $openedStream, $cacheFactory))->isFalse()
					->variable($controller->getStream())->isNull()
					->variable($controller->getStreamName())->isNull()
					->adapter($adapter)
						->call('fopen')->withArguments((string) $file, 'r', true)->once()
						->call('stream_filter_append')->never()
				->if($adapter->resetCalls())
				->and($adapter->fopen = uniqid())
				->and($adapter->is_resource = true)
				->and($adapter->stream_filter_append = true)
				->and($adapter->flock = true)
				->and($adapter->fseek = 0)
				->and($adapter->stream_get_contents = null)
				->then
					->variable($controller->stream_open($path = 'options=-moles/' . ($file = uniqid()), 'r', 0, $openedStream, $cacheFactory))->isIdenticalTo($openedStream)
					->adapter($adapter)
						->call('fopen')->withArguments($file, 'r', false)->once()
						->call('stream_filter_append')->withArguments($openedStream, atoum\instrumentation\stream::defaultProtocol, STREAM_FILTER_READ, array('moles' => false, 'coverage-transition' => true))->once()
					->variable($controller->stream_open($path = 'options=-moles,-coverage-transition/' . ($file = uniqid()), 'r', 0, $openedStream, $cacheFactory))->isIdenticalTo($openedStream)
					->adapter($adapter)
						->call('fopen')->withArguments($file, 'r', false)->once()
						->call('stream_filter_append')->withArguments($openedStream, atoum\instrumentation\stream::defaultProtocol, STREAM_FILTER_READ, array('moles' => false, 'coverage-transition' => false))->once()
					->variable($controller->stream_open($path = 'options=-coverage-transition/' . ($file = uniqid()), 'r', 0, $openedStream, $cacheFactory))->isIdenticalTo($openedStream)
					->adapter($adapter)
						->call('fopen')->withArguments($file, 'r', false)->once()
						->call('stream_filter_append')->withArguments($openedStream, atoum\instrumentation\stream::defaultProtocol, STREAM_FILTER_READ, array('moles' => true, 'coverage-transition' => false))->once()
		;
	}

	public function testStream_read()
	{
		$this
			->if($controller = new testedClass($adapter = new atoum\test\adapter()))
			->then
				->exception(function() use ($controller) {
						$controller->stream_read(rand(0, PHP_INT_MAX));
					}
				)
					->isInstanceOf('mageekguy\atoum\exceptions\runtime')
					->hasMessage('Stream is not set')
			->if($adapter->stream_filter_append = true)
			->and($adapter->flock = true)
			->and($adapter->fseek = 0)
			->and($adapter->stream_get_contents[0] = null)
			->and($file = file::get())
			->and($file->setContents($content = uniqid()))
			->and($cache = new \mock\mageekguy\atoum\instrumentation\stream\cache(uniqid(), (string) $file))
			->and($this->calling($cache)->exists = false)
			->and($this->calling($cache)->lock = $cache)
			->and($this->calling($cache)->write = $cache)
			->and($cacheFactory = function() use ($cache) { return $cache; })
			->and($controller->stream_open((string) $file, 'r', 0, $openedStream, $cacheFactory))
			->then
				->string($controller->stream_read($length = rand(1, strlen($content) - 1)))->isEqualTo(substr($content, 0, $length))
				->adapter($adapter)
					->call('fread')->withArguments($openedStream, $length)->once()
		;
	}

	public function testStream_seek()
	{
		$this
			->if($controller = new testedClass($adapter = new atoum\test\adapter()))
			->then
				->exception(function() use ($controller) {
						$controller->stream_seek(rand(0, PHP_INT_MAX));
					}
				)
					->isInstanceOf('mageekguy\atoum\exceptions\runtime')
					->hasMessage('Stream is not set')
			->if($adapter->stream_filter_append = true)
			->and($file = file::get())
			->and($file->setContents($content = uniqid()))
			->and($adapter->flock = true)
			->and($adapter->fseek = 0)
			->and($adapter->stream_get_contents = null)
			->and($cache = new \mock\mageekguy\atoum\instrumentation\stream\cache(uniqid(), (string) $file))
			->and($this->calling($cache)->exists = false)
			->and($this->calling($cache)->lock = $cache)
			->and($this->calling($cache)->write = $cache)
			->and($cacheFactory = function() use ($cache) { return $cache; })
			->and($controller->stream_open((string) $file, 'r', 0, $openedStream, $cacheFactory))
			->then
				->boolean($controller->stream_seek($offset = rand(0, strlen($content) - 1)))->isTrue()
				->adapter($adapter)
					->call('fseek')->withArguments($openedStream, $offset, SEEK_SET)->once()
		;
	}

	public function testStream_stat()
	{
		$this
			->if($controller = new testedClass($adapter = new atoum\test\adapter()))
			->then
				->exception(function() use ($controller) {
						$controller->stream_stat();
					}
				)
					->isInstanceOf('mageekguy\atoum\exceptions\runtime')
					->hasMessage('Stream is not set')
			->if($adapter->stream_filter_append = true)
			->and($file = file::get())
			->and($adapter->flock = true)
			->and($adapter->fseek = 0)
			->and($adapter->stream_get_contents = null)
			->and($cache = new \mock\mageekguy\atoum\instrumentation\stream\cache(uniqid(), (string) $file))
			->and($this->calling($cache)->exists = false)
			->and($this->calling($cache)->lock = $cache)
			->and($this->calling($cache)->write = $cache)
			->and($cacheFactory = function() use ($cache) { return $cache; })
			->and($controller->stream_open((string) $file, 'r', 0, $openedStream, $cacheFactory))
			->then
				->array($controller->stream_stat())
				->adapter($adapter)
					->call('fstat')->withArguments($openedStream)->once()
		;
	}

	public function testStream_tell()
	{
		$this
			->if($controller = new testedClass($adapter = new atoum\test\adapter()))
			->then
				->exception(function() use ($controller) {
						$controller->stream_tell();
					}
				)
					->isInstanceOf('mageekguy\atoum\exceptions\runtime')
					->hasMessage('Stream is not set')
			->if($adapter->stream_filter_append = true)
			->and($adapter->flock = true)
			->and($adapter->fseek = 0)
			->and($adapter->stream_get_contents = null)
			->and($adapter->is_resource = true)
			->and($file = file::get())
			->and($file->setContents($content = uniqid()))
			->and($cache = new \mock\mageekguy\atoum\instrumentation\stream\cache(uniqid(), (string) $file))
			->and($this->calling($cache)->exists = false)
			->and($this->calling($cache)->lock = $cache)
			->and($this->calling($cache)->write = $cache)
			->and($cacheFactory = function() use ($cache) { return $cache; })
			->and($controller->stream_open((string) $file, 'r', 0, $openedStream, $cacheFactory))
			->and($adapter->reset())
			->then
				->integer($controller->stream_tell())->isZero()
				->adapter($adapter)
					->call('ftell')->withArguments($openedStream)->once()
			->if($controller->stream_seek($offset = rand(0, strlen($content) - 1)))
			->then
				->integer($controller->stream_tell())->isEqualTo($offset)
		;
	}

	public function testUrl_stat()
	{
		$this
			->if($controller = new testedClass($adapter = new atoum\test\adapter()))
			->and($file = file::get())
			->then
				->array($controller->url_stat($path = (string) $file, 0))->isNotEmpty()
				->adapter($adapter)
					->call('stat')->withArguments($path)->once()
				->array($controller->url_stat($path, STREAM_URL_STAT_LINK))->isNotEmpty()
				->adapter($adapter)
					->call('lstat')->withArguments($path)->once()
		;
	}
}

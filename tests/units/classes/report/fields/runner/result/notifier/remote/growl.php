<?php

namespace mageekguy\atoum\tests\units\report\fields\runner\result\notifier\remote;

use
	mageekguy\atoum,
	mageekguy\atoum\locale,
	mageekguy\atoum\mock\stream,
	mageekguy\atoum\test\adapter,
	mageekguy\atoum\report\fields\runner\result\notifier\remote\growl as testedClass
;

require_once __DIR__ . '/../../../../../../runner.php';

class growl extends atoum\test
{
	public function testClass()
	{
		$this
			->testedClass
				->extends('mageekguy\atoum\report\fields\runner\result\notifier')
				->string(testedClass::applicationName)->isEqualTo('atoum')
				->string(testedClass::successNotification)->isEqualTo('Success')
				->string(testedClass::failureNotification)->isEqualTo('Failure')
				->integer(testedClass::defaultPort)->isEqualTo(9887)
		;
	}

	public function test__construct()
	{
		$this
			->if($adapter = new atoum\test\adapter())
			->and($adapter->fsockopen = function() use (& $resource) { return $resource = stream::get(); })
			->and($adapter->fwrite = function() {})
			->and($adapter->fclose = function() {})
			->and($adapter->pack = function() {})
			->and($field = new TestedClass($host = uniqid(), null, $adapter))
			->then
				->object($field->getLocale())->isEqualTo(new locale())
				->object($field->getAdapter())->isEqualTo($adapter)
				->variable($field->getTestNumber())->isNull()
				->variable($field->getTestMethodNumber())->isNull()
				->variable($field->getFailNumber())->isNull()
				->variable($field->getErrorNumber())->isNull()
				->variable($field->getExceptionNumber())->isNull()
				->string($field->getHost())->isEqualTo($host)
				->integer($field->getPort())->isEqualTo(testedClass::defaultPort)
				->array($field->getEvents())->isEqualTo(array(atoum\runner::runStop))
			->if($this->resetAdapter($adapter))
			->and($field = new TestedClass(uniqid(), $port = rand(1, PHP_INT_MAX), $adapter))
			->then
				->integer($field->getPort())->isEqualTo($port)
				->adapter($adapter)
					->call('pack')->withArguments('n', strlen(testedClass::successNotification) . testedClass::successNotification)->once()
					->call('pack')->withArguments('c', 0)->once()
					->call('pack')->withArguments('n', strlen(testedClass::failureNotification) . testedClass::failureNotification)->once()
					->call('pack')->withArguments('c', 1)->once()
					->call('pack')->withArguments('c2nc2', 1, 0, strlen(testedClass::applicationName), 2, 2)->once()
		;
	}

	public function testGetSetHost()
	{
		$this
			->if($adapter = new atoum\test\adapter())
			->and($adapter->fsockopen = function() use (& $resource) { return $resource = stream::get(); })
			->and($adapter->fwrite = function() {})
			->and($adapter->fclose = function() {})
			->and($adapter->pack = function() {})
			->and($field = new TestedClass($host = uniqid(), rand(1, PHP_INT_MAX), $adapter))
			->then
				->string($field->getHost())->isEqualTo($host)
			->if($host = uniqid())
			->then
				->object($field->setHost($host))->isIdenticalTo($field)
				->string($field->getHost())->isEqualTo($host)
		;
	}

	public function testGetSetPort()
	{
		$this
			->if($adapter = new atoum\test\adapter())
			->and($adapter->fsockopen = function() use (& $resource) { return $resource = stream::get(); })
			->and($adapter->fwrite = function() {})
			->and($adapter->fclose = function() {})
			->and($adapter->pack = function() {})
			->and($field = new TestedClass(uniqid(), $port = rand(1, PHP_INT_MAX), $adapter))
			->then
				->integer($field->getPort())->isEqualTo($port)
			->if($port = rand(1, PHP_INT_MAX))
			->then
				->object($field->setPort($port))->isIdenticalTo($field)
				->integer($field->getPort())->isEqualTo($port)
			->if($port = uniqid())
			->then
				->object($field->setPort($port))->isIdenticalTo($field)
				->integer($field->getPort())->isEqualTo((int) $port)
		;
	}

	public function testWrite()
	{
		$this
			->if($adapter = new atoum\test\adapter())
			->and($adapter->fsockopen = function() use (& $resource) { return $resource = stream::get(); })
			->and($adapter->fwrite = function() {})
			->and($adapter->fclose = function() {})
			->and($adapter->pack = function() use (& $packed) { return $packed = uniqid(); })
			->and($field = new TestedClass($host = uniqid(), $port = rand(1, PHP_INT_MAX), $adapter))
			->and($this->resetAdapter($adapter))
			->then
				->object($field->write($data = uniqid()))->isIdenticalTo($field)
				->adapter($adapter)
					->call('fsockopen')->withArguments(sprintf('udp://%s', $host), $port)->once()
					->call('pack')->withArguments('H32', md5($data))->once()
					->call('fwrite')->withArguments($resource, $data . $packed)->once()
					->call('fclose')->withArguments($resource)->once()
		;
	}

	public function testRegister()
	{
		$this
			->if($adapter = new atoum\test\adapter())
			->and($adapter->fsockopen = function() {})
			->and($adapter->fwrite = function() {})
			->and($adapter->fclose = function() {})
			->and($adapter->pack = function($format) {})
			->and($field = new TestedClass($host = uniqid(), $port = rand(1, PHP_INT_MAX), $adapter))
			->and($this->resetAdapter($adapter))
			->then
				->object($field->register(array()))->isIdenticalTo($field)
				->adapter($adapter)
					->call('pack')->never()
			->if($notifications = array($notification = uniqid(), $otherNotification = uniqid()))
			->and($adapter->fsockopen = function() { return stream::get(); })
			->and($adapter->pack = function($format) use (& $data, & $defaults, & $packet) {
						$pack = uniqid();

						switch ($format)
						{
							case 'n':
								$data .= $pack;
								break;

							case 'c':
								$defaults .= $pack;
								break;

							case 'c2nc2':
								$packet = $pack;
								break;
						}

						return $pack;
					}
				)
			->and($field = new TestedClass($host = uniqid(), $port = rand(1, PHP_INT_MAX), $adapter))
			->and($this->resetAdapter($adapter))
			->and($data = '')
			->and($defaults = '')
			->then
				->object($field->register($notifications))->isIdenticalTo($field)
				->adapter($adapter)
					->call('pack')->withArguments('n', strlen($notification) . $notification)->once()
					->call('pack')->withArguments('c', 0)->once()
					->call('pack')->withArguments('n', strlen($otherNotification) . $otherNotification)->once()
					->call('pack')->withArguments('c', 1)->once()
					->call('pack')->withArguments('c2nc2', 1, 0, strlen(testedClass::applicationName), count($notifications), count($notifications))->once()
					->call('pack')->withArguments('H32', md5($packet . testedClass::applicationName . $data . $defaults))->once()
		;
	}

	public function testExecute()
	{
		$this
			->if($adapter = new atoum\test\adapter())
			->and($adapter->fsockopen = function() {})
			->and($adapter->fwrite = function() {})
			->and($adapter->fclose = function() {})
			->and($adapter->pack = function($format) use (& $packet) {
						return $format === 'c2n5' ? ($packet = uniqid()) : uniqid();
					}
				)
			->and($field = new TestedClass($host = uniqid(), $port = rand(1, PHP_INT_MAX), $adapter))
			->then
				->object($field->execute($command = uniqid(), array($title = uniqid(), $message = uniqid())))->isIdenticalTo($field)
				->adapter($adapter)
					->call('pack')->withArguments('c2n5', 1, 1, 0, strlen($command), strlen($title), strlen($message), strlen(testedClass::applicationName))->once()
					->call('pack')->withArguments('H32', md5($packet . $command . $title . $message . testedClass::applicationName))->once()
		;
	}
}

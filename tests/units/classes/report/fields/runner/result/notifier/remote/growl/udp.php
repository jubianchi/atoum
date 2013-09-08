<?php

namespace mageekguy\atoum\tests\units\report\fields\runner\result\notifier\remote\growl;

use
	mageekguy\atoum,
	mageekguy\atoum\locale,
	mageekguy\atoum\test\adapter,
	mageekguy\atoum\report\fields\runner\result\notifier\remote\growl\udp as testedClass
;

require_once __DIR__ . '/../../../../../../../runner.php';

class udp extends atoum\test
{
	public function testClass()
	{
		$this
			->testedClass
				->extends('mageekguy\atoum\report\fields\runner\result\notifier\remote')
				->string(testedClass::defaultApplicationName)->isEqualTo('atoum')
				->string(testedClass::defaultNotificationName)->isEqualTo('Tests')
		;
	}

	public function test__construct()
	{
		$this
			->if($host = uniqid())
			->and($port = rand(1, PHP_INT_MAX))
			->and($field = new testedClass($host, $port))
			->then
				->object($field->getLocale())->isEqualTo(new locale())
				->object($field->getAdapter())->isEqualTo(new atoum\adapter())
				->variable($field->getTestMethodNumber())->isNull()
				->variable($field->getFailNumber())->isNull()
				->variable($field->getErrorNumber())->isNull()
				->variable($field->getExceptionNumber())->isNull()
				->array($field->getEvents())->isEqualTo(array(atoum\runner::runStop))
				->string($field->getApplicationName())->isEqualTo(testedClass::defaultApplicationName)
				->string($field->getNotificationName())->isEqualTo(testedClass::defaultNotificationName)
				->boolean($field->isSticky())->isFalse()
				->variable($field->getPassword())->isNull()
				->integer($field->getPriority())->isZero()
		;
	}

	public function testGetSetApplicationName()
	{
		$this
			->if($field = new testedClass(uniqid(), rand(1, PHP_INT_MAX)))
			->then
				->string($field->getApplicationName())->isEqualTo(testedClass::defaultApplicationName)
				->object($field->setApplicationName($id = uniqid()))->isIdenticalTo($field)
				->string($field->getApplicationName())->isEqualTo($id)
				->object($field->setApplicationName())->isEqualTo($field)
				->string($field->getApplicationName())->isEqualTo(testedClass::defaultApplicationName)
		;
	}

	public function testGetSetNotificationName()
	{
		$this
			->if($field = new testedClass(uniqid(), rand(1, PHP_INT_MAX)))
			->then
				->string($field->getNotificationName())->isEqualTo(testedClass::defaultNotificationName)
				->object($field->setNotificationName($name = uniqid()))->isIdenticalTo($field)
				->string($field->getNotificationName())->isEqualTo($name)
				->object($field->setNotificationName())->isEqualTo($field)
				->string($field->getNotificationName())->isEqualTo(testedClass::defaultNotificationName)
		;
	}

	public function testGetSetPassword()
	{
		$this
			->if($field = new testedClass(uniqid(), rand(1, PHP_INT_MAX)))
			->then
				->variable($field->getPassword())->isNull()
				->object($field->setPassword($password = uniqid()))->isIdenticalTo($field)
				->string($field->getPassword())->isEqualTo($password)
		;
	}

	public function testGetSetPriority()
	{
		$this
			->if($field = new testedClass(uniqid(), rand(1, PHP_INT_MAX)))
			->then
				->integer($field->getPriority())->isZero()
				->object($field->setPriority($priority = rand(1, PHP_INT_MAX)))->isIdenticalTo($field)
				->integer($field->getPriority())->isEqualTo($priority)
		;
	}

	public function testSetIsSticky()
	{
		$this
			->if($field = new testedClass(uniqid(), rand(1, PHP_INT_MAX)))
			->then
				->boolean($field->isSticky())->isFalse()
				->object($field->setSticky(true))->isIdenticalTo($field)
				->boolean($field->isSticky())->isTrue()
				->object($field->setSticky(false))->isIdenticalTo($field)
				->boolean($field->isSticky())->isFalse()
		;
	}

	public function testHandleEvent()
	{
		$this
			->if($score = new \mock\mageekguy\atoum\runner\score())
			->and($this->calling($score)->getAssertionNumber = $assertionNumber = rand(1, PHP_INT_MAX))
			->and($this->calling($score)->getFailNumber = $failNumber = rand(1, PHP_INT_MAX))
			->and($this->calling($score)->getErrorNumber = $errorNumber = rand(1, PHP_INT_MAX))
			->and($this->calling($score)->getExceptionNumber = $exceptionNumber = rand(1, PHP_INT_MAX))
			->and($runner = new \mock\mageekguy\atoum\runner())
			->and($runner->setScore($score))
			->and($this->calling($runner)->getTestNumber = $testNumber = rand(1, PHP_INT_MAX))
			->and($this->calling($runner)->getTestMethodNumber = $testMethodNumber = rand(1, PHP_INT_MAX))
			->and($field = new testedClass(uniqid(), rand(1, PHP_INT_MAX)))
			->then
				->boolean($field->handleEvent(atoum\runner::runStart, $runner))->isFalse()
				->variable($field->getTestNumber())->isNull()
				->variable($field->getTestMethodNumber())->isNull()
				->variable($field->getAssertionNumber())->isNull()
				->variable($field->getFailNumber())->isNull()
				->variable($field->getErrorNumber())->isNull()
				->variable($field->getExceptionNumber())->isNull()
				->boolean($field->handleEvent(atoum\runner::runStop, $runner))->isTrue()
				->integer($field->getTestNumber())->isEqualTo($testNumber)
				->integer($field->getTestMethodNumber())->isEqualTo($testMethodNumber)
				->integer($field->getAssertionNumber())->isEqualTo($assertionNumber)
				->integer($field->getFailNumber())->isEqualTo($failNumber)
				->integer($field->getErrorNumber())->isEqualTo($errorNumber)
				->integer($field->getExceptionNumber())->isEqualTo($exceptionNumber)
		;
	}

	public function testRegister()
	{
		$this
			->if($adapter = new adapter())
			->and($adapter->function_exists = function($function) {
				return $function === 'socket_create';
			})
			->and($adapter->socket_create = function() use (& $socket) {
				return $socket = atoum\mock\streams\fs\file::get();
			})
			->and($adapter->socket_sendto = function($socket, $buffer) {
				return strlen($buffer);
			})
			->and($adapter->socket_close = true)
			->and($adapter->inet_pton = "\000\000\001")
			->and($field = new testedClass(uniqid(), rand(1, PHP_INT_MAX), $adapter))
			->and($data = pack('c2nc2', testedClass::growlProtocolVersion, testedClass::growlMethodRegistration, strlen($field->getApplicationName()), 1, 1)
				. $field->getApplicationName()
				. pack('n', strlen($field->getNotificationName())) . $field->getNotificationName()
				. pack('c', 0)
			)
			->then
				->object($field->register())->isIdenticalTo($field)
				->adapter($adapter)
					->call('socket_create')->withArguments(AF_INET, SOCK_DGRAM, SOL_UDP)->once()
					->call('socket_sendto')->withArguments($socket, $data . pack('H32', md5($data . $field->getPassword())))->once()
					->call('socket_close')->withArguments($socket)->once()
			->if($adapter->inet_pton = "\000\000\000\000\000\000\000\000\000\000\000\000\000\000\000\001")
			->then
				->object($field->register())->isIdenticalTo($field)
				->adapter($adapter)
					->call('socket_create')->withArguments(AF_INET6, SOCK_DGRAM, SOL_UDP)->once()
		;
	}

	public function testPublish()
	{
		$this
			->if($adapter = new adapter())
			->and($adapter->function_exists = function($function) {
				return $function === 'socket_create';
			})
			->and($adapter->socket_create = function() use (& $socket) {
				return $socket = atoum\mock\streams\fs\file::get();
			})
			->and($adapter->socket_sendto = function($socket, $buffer) {
				return strlen($buffer);
			})
			->and($adapter->socket_close = true)
			->and($adapter->inet_pton = "\000\000\001")
			->and($title = uniqid())
			->and($message = uniqid())
			->and($field = new testedClass(uniqid(), rand(1, PHP_INT_MAX), $adapter))
			->and($data = pack('c2n5', testedClass::growlProtocolVersion, testedClass::growlMethodNotification, 0, strlen($field->getNotificationName()), strlen($title), strlen($message), strlen($field->getApplicationName()))
				. $field->getNotificationName() . $title . $message . $field->getApplicationName()
			)
			->then
				->object($field->publish($title, $message))->isIdenticalTo($field)
				->adapter($adapter)
					->call('socket_create')->withArguments(AF_INET, SOCK_DGRAM, SOL_UDP)->once()
					->call('socket_sendto')->withArguments($socket, $data . pack('H32', md5($data . $field->getPassword())))->once()
					->call('socket_close')->withArguments($socket)->once()
			->if($adapter->inet_pton = "\000\000\000\000\000\000\000\000\000\000\000\000\000\000\000\001")
			->then
				->object($field->publish($title, $message))->isIdenticalTo($field)
				->adapter($adapter)
					->call('socket_create')->withArguments(AF_INET6, SOCK_DGRAM, SOL_UDP)->once()
		;
	}
}

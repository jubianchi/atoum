<?php

namespace mageekguy\atoum\tests\units\report\fields\runner\result\notifier;

use
	mageekguy\atoum,
	mageekguy\atoum\adapter,
	mageekguy\atoum\test,
	mock\mageekguy\atoum\report\fields\runner\result\notifier\remote as testedClass
;

require_once __DIR__ . '/../../../../../runner.php';

class remote extends atoum\test
{
	public function testClass()
	{
		$this->testedClass->extends('mageekguy\atoum\report\fields\runner\result\notifier');
	}

	public function test__construct()
	{
		$this
			->if($host = uniqid())
			->and($port = rand(1, PHP_INT_MAX))
			->and($field = new testedClass($host, $port))
			->then
				->object($field->getAdapter())->isEqualTo(new adapter())
				->string($field->getHost())->isEqualTo($host)
				->integer($field->getPort())->isEqualTo($port)
			->if($adapter = new adapter())
			->and($field = new testedClass($host, $port, $adapter))
			->then
				->object($field->getAdapter())->isIdenticalTo($adapter)
		;
	}

	public function testGetSetHost()
	{
		$this
			->if($host = uniqid())
			->and($field = new testedClass($host, rand(1, PHP_INT_MAX)))
			->then
				->string($field->getHost())->isEqualTo($host)
				->object($field->setHost(uniqid()))->isIdenticalTo($field)
		;
	}

	public function testGetSetPort()
	{
		$this
			->if($port = rand(1, PHP_INT_MAX))
			->and($field = new testedClass(uniqid(), $port))
			->then
				->integer($field->getPort())->isEqualTo($port)
				->object($field->setPort(uniqid()))->isIdenticalTo($field)
		;
	}

	public function testGetSetAdapter()
	{
		$this
			->if($field = new testedClass(uniqid(), rand(1, PHP_INT_MAX)))
			->then
				->object($field->setAdapter($adapter = new atoum\adapter()))->isIdenticalTo($field)
				->object($field->getAdapter())->isIdenticalTo($adapter)
				->object($field->setAdapter())->isIdenticalTo($field)
				->object($field->getAdapter())->isNotIdenticalTo($adapter)->isEqualTo(new atoum\adapter())
		;
	}
}

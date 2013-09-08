<?php

namespace mageekguy\atoum\tests\units\report\fields\runner\result\notifier;

use
	mageekguy\atoum,
	mageekguy\atoum\adapter,
	mageekguy\atoum\test,
	mock\mageekguy\atoum\report\fields\runner\result\notifier\cli as testedClass
;

require_once __DIR__ . '/../../../../../runner.php';

class cli extends atoum\test
{
	public function testClass()
	{
		$this->testedClass->extends('mageekguy\atoum\report\fields\runner\result\notifier');
	}

	public function test__construct()
	{
		$this
			->if($field = new testedClass())
			->then
				->object($field->getAdapter())->isEqualTo(new adapter())
			->if($adapter = new adapter())
			->and($field = new testedClass($adapter))
			->then
				->object($field->getAdapter())->isIdenticalTo($adapter)
		;
	}

	public function test__toString()
	{
		$this
			->if($adapter = new test\adapter())
			->and($adapter->system = function() use (& $output) { return $output = uniqid(); })
			->and($field = new testedClass($adapter))
			->then
				->castToString($field)->isEqualTo($output . PHP_EOL)
				->mock($field)->call('getCommand')->once()
				->adapter($adapter)->call('system')->once()
		;
	}

	public function testGetSetAdapter()
	{
		$this
			->if($field = new testedClass())
			->then
				->object($field->setAdapter($adapter = new atoum\adapter()))->isIdenticalTo($field)
				->object($field->getAdapter())->isIdenticalTo($adapter)
				->object($field->setAdapter())->isIdenticalTo($field)
				->object($field->getAdapter())->isNotIdenticalTo($adapter)->isEqualTo(new atoum\adapter())
		;
	}
}

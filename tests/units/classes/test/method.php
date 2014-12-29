<?php

namespace mageekguy\atoum\tests\units\test;

use
    mageekguy\atoum
;

require_once __DIR__ . '/../../runner.php';

class method extends atoum\test
{
    public function testClass()
    {
        $this
            ->testedClass
                ->hasConstant('defaultEngine')->isEqualTo('concurrent')
        ;
    }

    public function test__construct()
    {
        $this
            ->mockGenerator->shuntParentClassCalls()
            ->given(
                $method = new \mock\reflectionMethod(uniqid(), $name = uniqid())
            )
            ->then
                ->object($this->newTestedInstance($method))
                ->string($this->testedInstance->getEngineClass())->isEqualTo('\mageekguy\atoum\test\engines\concurrent')
                ->boolean($this->testedInstance->isIgnored())->isFalse
        ;
    }

    public function testAddPhpVersion()
    {
        $this
            ->mockGenerator->shuntParentClassCalls()
            ->given(
                $method = new \mock\reflectionMethod(uniqid(), uniqid()),
                $this->newTestedInstance($method),
                $version = uniqid()
            )
            ->then
                ->array($this->testedInstance->getPhpVersions())->isEmpty
                ->object($this->testedInstance->addPhpVersion($version))->isTestedInstance
                ->array($this->testedInstance->getPhpVersions())
                    ->string[$version]->isEqualTo('>=')
            ->given($operator = uniqid())
            ->then
                ->object($this->testedInstance->addPhpVersion($version, $operator))->isTestedInstance
                ->array($this->testedInstance->getPhpVersions())
                    ->string[$version]->isEqualTo($operator)
        ;
    }

    public function testAddMandatoryExtension()
    {
        $this
            ->mockGenerator->shuntParentClassCalls()
            ->given(
                $method = new \mock\reflectionMethod(uniqid(), uniqid()),
                $this->newTestedInstance($method),
                $extension = uniqid()
            )
            ->then
                ->array($this->testedInstance->getMandatoryExtensions())->isEmpty
                ->object($this->testedInstance->addMandatoryExtension($extension))->isTestedInstance
                ->array($this->testedInstance->getMandatoryExtensions())->contains($extension)
            ->given($otherExtension = uniqid())
            ->then
                ->object($this->testedInstance->addMandatoryExtension($otherExtension))->isTestedInstance
                ->array($this->testedInstance->getMandatoryExtensions())
                    ->contains($extension)
                    ->contains($otherExtension)
        ;
    }

    public function testGetSetDataProider()
    {
        $this
            ->mockGenerator->shuntParentClassCalls()
            ->given(
                $method = new \mock\reflectionMethod(uniqid(), uniqid()),
                $this->newTestedInstance($method),
                $provider = new \mock\reflectionMethod(uniqid(), uniqid())
            )
            ->then
                ->variable($this->testedInstance->getDataProvider())->isNull
                ->object($this->testedInstance->setDataProvider($provider))->isTestedInstance
                ->object($this->testedInstance->getDataProvider())->isIdenticalTo($provider)
        ;
    }

    public function testGetSetEngineClass()
    {
        $this
            ->mockGenerator->shuntParentClassCalls()
            ->given(
                $method = new \mock\reflectionMethod(uniqid(), uniqid()),
                $this->newTestedInstance($method)
            )
            ->then
                ->string($this->testedInstance->getEngineClass())->isEqualTo('\mageekguy\atoum\test\engines\concurrent')
                ->object($this->testedInstance->setEngineClass($engine = uniqid()))->isTestedInstance
                ->string($this->testedInstance->getEngineClass())->isEqualTo('\mageekguy\atoum\test\engines\\' . $engine)
        ;
    }

    public function testGetSetTags()
    {
        $this
            ->mockGenerator->shuntParentClassCalls()
            ->given(
                $method = new \mock\reflectionMethod(uniqid(), uniqid()),
                $this->newTestedInstance($method)
            )
            ->then
                ->array($this->testedInstance->getTags())->isEmpty
                ->object($this->testedInstance->setTags($tags = array(uniqid())))->isTestedInstance
                ->array($this->testedInstance->getTags())->isIdenticalTo($tags)
        ;
    }

    public function testHasTags()
    {
        $this
            ->mockGenerator->shuntParentClassCalls()
            ->given(
                $method = new \mock\reflectionMethod(uniqid(), uniqid()),
                $this->newTestedInstance($method)
            )
            ->then
                ->boolean($this->testedInstance->hasTags(array()))->isFalse
                ->boolean($this->testedInstance->hasTags(array(uniqid())))->isFalse
            ->if($this->testedInstance->setTags($tags = array(uniqid())))
            ->then
                ->boolean($this->testedInstance->hasTags(array()))->isFalse
                ->boolean($this->testedInstance->hasTags(array(uniqid())))->isFalse
                ->boolean($this->testedInstance->hasTags($tags))->isTrue
            ->if(
                $tags[] = uniqid(),
                $this->testedInstance->setTags($tags)
            )
            ->then
                ->boolean($this->testedInstance->hasTags(array()))->isFalse
                ->boolean($this->testedInstance->hasTags(array(uniqid())))->isFalse
                ->boolean($this->testedInstance->hasTags($tags))->isTrue
                ->boolean($this->testedInstance->hasTags(array(current($tags))))->isTrue
        ;
    }

    public function testIsAsynchronous()
    {
        $this
            ->mockGenerator->shuntParentClassCalls()
            ->given(
                $method = new \mock\reflectionMethod(uniqid(), uniqid()),
                $this->newTestedInstance($method)
            )
            ->then
                ->boolean($this->testedInstance->isAsynchronous())->isTrue
            ->if($this->testedInstance->setEngineClass('inline'))
            ->then
                ->boolean($this->testedInstance->isAsynchronous())->isFalse
        ;
    }

    public function testIsVoid()
    {
        $this
            ->mockGenerator->shuntParentClassCalls()
            ->given(
                $method = new \mock\reflectionMethod(uniqid(), uniqid()),
                $this->newTestedInstance($method)
            )
            ->then
                ->boolean($this->testedInstance->isVoid())->isFalse
                ->object($this->testedInstance->void())->isTestedInstance
                ->boolean($this->testedInstance->isVoid())->isTrue
                ->object($this->testedInstance->void(false))->isTestedInstance
                ->boolean($this->testedInstance->isVoid())->isFalse
        ;
    }
} 

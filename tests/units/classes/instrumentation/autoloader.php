<?php

namespace mageekguy\atoum\tests\units\instrumentation;

use
    mageekguy\atoum,
	mageekguy\atoum\test,
	mageekguy\atoum\adapter,
    mageekguy\atoum\instrumentation\stream,
	mageekguy\atoum\instrumentation\autoloader as testedClass
;

require_once __DIR__ . '/../../runner.php';

class autoloader extends test
{
    public function test__construct()
    {
        $this
            ->if($autoloader = new testedClass())
            ->then
                ->array($autoloader->getClasses())->isEmpty()
				->variable($autoloader->getCacheFileForInstance())->isEqualTo(testedClass::getCacheFile())
				->array($autoloader->getDirectories())->isEqualTo(array(
						'mageekguy\atoum\\' => array(
							array(
								atoum\directory . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR,
								testedClass::defaultFileSuffix
							)
						)
					)
				)
				->array($autoloader->getNamespaceAliases())->isEqualTo(array('atoum\\' => 'mageekguy\\atoum\\'))
				->array($autoloader->getClassAliases())->isEqualTo(array('atoum' => 'mageekguy\\atoum\\test', 'mageekguy\\atoum' => 'mageekguy\\atoum\\test'))
                ->boolean($autoloader->instrumentationEnabled())->isTrue()
                ->boolean($autoloader->moleInstrumentationEnabled())->isTrue()
                ->boolean($autoloader->coverageInstrumentationEnabled())->isTrue()
                ->array($autoloader->getIgnoredNamespaces())->isEqualTo(array($this->getTestedClassNamespace()))
                ->array($autoloader->getIgnoredClasses())->isEmpty()
        ;
    }

    public function testIgnoreNamespace()
    {
        $this
            ->if($autoloader = new testedClass())
            ->then
                ->object($autoloader->ignoreNamespace($namespace = uniqid()))->isIdenticalTo($autoloader)
                ->array($autoloader->getIgnoredNamespaces())->isEqualTo(array($this->getTestedClassNamespace(), $namespace))
                ->object($autoloader->ignoreNamespace($namespace))->isIdenticalTo($autoloader)
                ->array($autoloader->getIgnoredNamespaces())->isEqualTo(array($this->getTestedClassNamespace(), $namespace))
                ->object($autoloader->ignoreNamespace($otherNamespace = uniqid()))->isIdenticalTo($autoloader)
                ->array($autoloader->getIgnoredNamespaces())->isEqualTo(array($this->getTestedClassNamespace(), $namespace, $otherNamespace))
        ;
    }

    public function testIgnoreClass()
    {
        $this
            ->if($autoloader = new testedClass())
            ->then
                ->object($autoloader->ignoreClass($class = uniqid()))->isIdenticalTo($autoloader)
                ->array($autoloader->getIgnoredClasses())->isEqualTo(array($class))
                ->object($autoloader->ignoreClass($class))->isIdenticalTo($autoloader)
                ->array($autoloader->getIgnoredClasses())->isEqualTo(array($class))
                ->object($autoloader->ignoreClass($otherClass = uniqid()))->isIdenticalTo($autoloader)
                ->array($autoloader->getIgnoredClasses())->isEqualTo(array($class, $otherClass))
        ;
    }

    public function testIsIgnored()
    {
        $this
            ->if($autoloader = new testedClass())
            ->then
                ->boolean($autoloader->isIgnored($this->getTestedClassNamespace()))->isTrue()
                ->boolean($autoloader->isIgnored($this->getTestedClassNamespace() . '\\' . uniqid()))->isTrue()
                ->boolean($autoloader->isIgnored($class = uniqid()))->isFalse()
                ->boolean($autoloader->isIgnored($namespace = uniqid() . '\\' . $class))->isFalse()
            ->if($autoloader->ignoreClass($namespace . '\\' . $class))
            ->then
                ->boolean($autoloader->isIgnored($class))->isFalse()
                ->boolean($autoloader->isIgnored($namespace . '\\' . $class))->isTrue()
                ->boolean($autoloader->isIgnored(uniqid() . '\\' . $namespace . '\\' . $class))->isFalse()
            ->if($autoloader->ignoreNamespace($namespace = uniqid()))
            ->then
                ->boolean($autoloader->isIgnored($namespace . '\\' . $class))->isTrue()
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

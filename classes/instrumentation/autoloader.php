<?php

namespace mageekguy\atoum\instrumentation;

use
    mageekguy\atoum,
    mageekguy\atoum\instrumentation\stream
;

class autoloader extends atoum\autoloader
{
    protected static $autoloader = null;

    protected $instrumentationEnabled = true;
    protected $moleInstrumentationEnabled = true;
    protected $coverageInstrumentationEnabled = true;
    protected $ignoredNamespaces = array();
    protected $ignoredClasses = array();

    public function __construct(array $namespaces = array(), array $namespaceAliases = array(), $classAliases = array())
    {
        parent::__construct($namespaces, $namespaceAliases, $classAliases);

        $this->ignoreNamespace(__NAMESPACE__);
    }

    public function getPath($class)
    {
        $path = parent::getPath($class);

        if ($path !== null && $this->instrumentationEnabled && $this->isIgnored($class) === false)
        {
            $path = $this->getInstrumentedPath($path);
        }

        return $path;
    }

    public function getInstrumentedPath($path)
    {
        $options = null;
        if ($this->moleInstrumentationEnabled === false)
        {
            $options[] = '-moles';
        }

        if ($this->coverageInstrumentationEnabled === false)
        {
            $options[] = '-coverage-transition';
        }

        if (sizeof($options))
        {
            $options = 'options=' . implode(',', $options) . DIRECTORY_SEPARATOR;
        }

        return stream::defaultProtocol . stream::protocolSeparator . $options . $path;
    }

    public function isIgnored($class)
    {
        $ignored = in_array($class, $this->ignoredClasses);

        if ($ignored === false)
        {
            foreach ($this->ignoredNamespaces as $namespace)
            {
                if (strpos($class, $namespace) === 0)
                {
                    return true;
                }
            }
        }

        return $ignored;
    }

    public function ignoreNamespace($namespace)
    {
        if (in_array($namespace, $this->ignoredNamespaces) === false)
        {
            $this->ignoredNamespaces[] = $namespace;
        }

        return $this;
    }

    public function getIgnoredNamespaces()
    {
        return $this->ignoredNamespaces;
    }

    public function ignoreClass($class)
    {
        if (in_array($class, $this->ignoredClasses) === false)
        {
            $this->ignoredClasses[] = $class;
        }

        return $this;
    }

    public function getIgnoredClasses()
    {
        return $this->ignoredClasses;
    }

    public function enableInstrumentation()
    {
        $this->instrumentationEnabled = true;

        return $this;
    }

    public function disableInstrumentation()
    {
        $this->instrumentationEnabled = false;

        return $this;
    }

    public function instrumentationEnabled()
    {
        return $this->instrumentationEnabled;
    }

    public function enableMoleInstrumentation()
    {
        $this->moleInstrumentationEnabled = true;

        return $this;
    }

    public function disableMoleInstrumentation()
    {
        $this->moleInstrumentationEnabled = false;

        return $this;
    }

    public function moleInstrumentationEnabled()
    {
        return $this->moleInstrumentationEnabled;
    }

    public function enableCoverageInstrumentation()
    {
        $this->coverageInstrumentationEnabled = true;

        return $this;
    }

    public function disableCoverageInstrumentation()
    {
        $this->coverageInstrumentationEnabled = false;

        return $this;
    }

    public function coverageInstrumentationEnabled()
    {
        return $this->coverageInstrumentationEnabled;
    }

    public static function set()
    {
        if (static::$autoloader === null)
        {
            static::$autoloader = new static();
            static::$autoloader->register();
        }

        return static::$autoloader;
    }

    public static function get()
    {
        return static::set();
    }
} 
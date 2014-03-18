<?php

namespace mageekguy\atoum\instrumentation\autoloader;

use
	mageekguy\atoum,
	mageekguy\atoum\instrumentation\stream
;

class decorator implements atoum\autoloader\decorator
{
	protected $instrumentationEnabled = true;
	protected $moleInstrumentationEnabled = true;
	protected $coverageInstrumentationEnabled = true;
	protected $ignoredPaths = array();

	public function __construct()
	{
		$this
			->ignorePath(dirname(__DIR__))
			->ignorePath(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'scripts')
		;
	}

	public function decorate($path)
	{
		if ($path !== null && $this->isIgnored($path) === false)
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

	public function isIgnored($path)
	{
		foreach ($this->ignoredPaths as $ignored)
		{
			if (strpos($path, $ignored) === 0)
			{
				return true;
			}
		}

		return false;
	}

	public function ignorePath($path)
	{
		if (in_array((string) $path, $this->ignoredPaths) === false)
		{
			$this->ignoredPaths[] = (string) $path;
		}

		return $this;
	}

	public function getIgnoredPaths()
	{
		return $this->ignoredPaths;
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
}

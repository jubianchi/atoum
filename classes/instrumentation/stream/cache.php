<?php

namespace mageekguy\atoum\instrumentation\stream;

use
	mageekguy\atoum\test,
	mageekguy\atoum\writers,
	mageekguy\atoum\exceptions,
	mageekguy\atoum\instrumentation\rules
;

class cache
{
	protected $filePath;
	protected $cacheFile = false;
	protected $cacheRootDirectory;

	protected static $cacheDirectory = null;

	public function __construct($rootDirectory, $path)
	{
		$this->cacheRootDirectory = $rootDirectory;
		$this->filePath = $path;
	}

	public function exists()
	{
		return is_file($this->getCachePath()) && filesize($this->getCachePath()) > 0;
	}

	public function lock()
	{
		$cachePath = $this->getCachePath();
		$cacheDir = dirname($cachePath);

		if (is_dir($cacheDir) === false)
		{
			@mkdir($cacheDir, 0777, true);
		}

		if (($this->cacheFile = fopen($cachePath, 'w')) !== false)
		{
			flock($this->cacheFile, LOCK_EX);
		}

		return $this;
	}

	public function write($data)
	{
		if ($this->cacheFileIsSet() === true)
		{
			fwrite($this->cacheFile, $data);
			flock($this->cacheFile, LOCK_UN);
			fclose($this->cacheFile);
		}

		return $this;
	}

	public function getCachePath()
	{
		return rtrim($this->cacheRootDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $this->filePath;
	}

	public function isValid()
	{
		return (filemtime($this->getCachePath()) > filemtime($this->filePath));
	}

	public static function get($path)
	{
		return new static(static::getCacheDirectory(), $path);
	}

	protected function cacheFileIsSet()
	{
		is_resource($this->cacheFile);
	}

	public static function setCacheDirectory($cacheDirectory)
	{
		static::$cacheDirectory = $cacheDirectory;
	}

	public static function getCacheDirectory()
	{
		return (static::$cacheDirectory ?: rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR));
	}
}

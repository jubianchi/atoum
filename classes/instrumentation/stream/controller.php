<?php

namespace mageekguy\atoum\instrumentation\stream;

use
	mageekguy\atoum,
	mageekguy\atoum\exceptions
;

class controller
{
	const pathRegex = '#^(?:options=(?<options>[^/]*)/)?(?:resource=)?(?<resource>[^$]+)$#';
	const optionsRegex = '#(?<flag>[+\-])?(?<name>[\w\-]+)#';

	protected $adapter;
	protected $stream;
	protected $streamName;

	public function __construct(atoum\adapter $adapter = null)
	{
		$this->setAdapter($adapter);
	}

	public function setAdapter(atoum\adapter $adapter = null)
	{
		$this->adapter = $adapter ?: new atoum\adapter();

		return $this;
	}

	public function getAdapter()
	{
		return $this->adapter;
	}

	public function getStream()
	{
		return $this->stream;
	}

	public function getStreamName()
	{
		return $this->streamName;
	}

	public function stream_close()
	{
		if (fclose($this->stream) === true)
		{
			$this->stream	 = null;
			$this->streamName = null;
		}
	}

	public function stream_eof()
	{
		return feof($this->stream);
	}

	public function stream_flush()
	{
		return fflush($this->stream);
	}

	public function stream_lock($operation)
	{
		return flock($this->stream, $operation);
	}

	public function stream_open($path, $mode, $options, & $openedPath, $cacheFactory = null)
	{
		$cacheFactory = $cacheFactory ?: function($path) { return cache::get($path); };

		$this->reset();

		cache::setCacheDirectory('/tmp');

		if (preg_match(static::pathRegex, $path, $matches) > 0)
		{
			$path = $matches['resource'];
			$cache = $cacheFactory($path);
			$cacheUsed = false;

			if ($cache->exists() && $cache->isValid())
			{
				$path = $cache->getCachePath();
				$cacheUsed = true;
			}
			else
			{
				$cache->lock();
			}

			$stream = $this->adapter->fopen($path, $mode, $options & STREAM_USE_PATH);

			if ($this->adapter->is_resource($stream))
			{
				$openedPath = $this->stream = $stream;
				$this->streamName = $path;

				if ($cacheUsed === false)
				{
					$this->adapter->stream_filter_append(
						$stream,
						atoum\instrumentation\stream::defaultProtocol,
						STREAM_FILTER_READ,
						static::parseOptions($matches['options'])
					);

					$data = $this->adapter->stream_get_contents($stream);
					$this->adapter->fseek($stream, 0);

					$cache->write($data);
				}

				while ($this->adapter->flock($stream, LOCK_SH) === false);
			}
		}

		return $this->adapter->is_resource($this->stream) ? $this->stream : false;
	}

	public function stream_read($count)
	{
		return $this->adapter->fread($this->streamIsSet()->stream, $count);
	}

	public function stream_seek($offset, $whence = SEEK_SET)
	{
		return 0 === $this->adapter->fseek($this->streamIsSet()->stream, $offset, $whence);
	}

	public function stream_stat()
	{
		return $this->adapter->fstat($this->streamIsSet()->stream);
	}

	public function stream_tell()
	{
		return $this->adapter->ftell($this->streamIsSet()->stream);
	}

	public function url_stat($path, $flags) {

		if ($flags & STREAM_URL_STAT_LINK)
		{
			return @$this->adapter->lstat($path);
		}

		return @$this->adapter->stat($path);
	}

	protected function streamIsSet()
	{
		if (is_resource($this->stream) === false)
		{
			throw new exceptions\runtime('Stream is not set');
		}

		return $this;
	}

	protected function reset()
	{
		if (is_resource($this->stream))
		{
			$this->stream_close();
		}

		$this->stream = null;
		$this->streamName = null;

		return $this;
	}

	private static function parseOptions($options)
	{
		$parameters = array(
			'coverage-transition' => true,
			'moles' => true
		);

		if (preg_match_all(static::optionsRegex, $options, $matches, PREG_SET_ORDER) > 0)
		{
			foreach ($matches as $option)
			{
				if (isset($parameters[$option['name']]))
				{
					$parameters[$option['name']] = '-' !== $option['flag'];
				}
			}
		}

		return $parameters;
	}
}

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

	protected $stream;
	protected $streamName;

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

			$stream = fopen($path, $mode, $options & STREAM_USE_PATH);

			if (is_resource($stream))
			{
				$openedPath = $this->stream = $stream;
				$this->streamName = $path;

				if ($cacheUsed === false)
				{
					stream_filter_append(
						$stream,
						atoum\instrumentation\stream::defaultProtocol,
						STREAM_FILTER_READ,
						static::parseOptions($matches['options'])
					);

					$data = stream_get_contents($stream);
					fseek($stream, 0);

					$cache->write($data);
				}

				while (flock($stream, LOCK_SH) === false);
			}
		}

		return is_resource($this->stream) ? $this->stream : false;
	}

	public function stream_read($count)
	{
		try
		{
			return fread($this->streamIsSet()->stream, $count);
		}
		catch (exceptions\runtime $exception)
		{
			return false;
		}
	}

	public function stream_seek($offset, $whence = SEEK_SET)
	{
		try
		{
			return 0 === fseek($this->streamIsSet()->stream, $offset, $whence);
		}
		catch (exceptions\runtime $exception)
		{
			return false;
		}
	}

	public function stream_stat()
	{
		try
		{
			return fstat($this->streamIsSet()->stream);
		}
		catch (exceptions\runtime $exception)
		{
			return false;
		}
	}

	public function stream_tell()
	{
		try
		{
			return ftell($this->streamIsSet()->stream);
		}
		catch (exceptions\runtime $exception)
		{
			return false;
		}
	}

	public function url_stat($path, $flags) {

		if ($flags & STREAM_URL_STAT_LINK)
		{
			return @lstat($path);
		}

		return @stat($path);
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

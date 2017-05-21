<?php

namespace mageekguy\atoum\instrumentation;

use
	mageekguy\atoum\exceptions\logic,
	mageekguy\atoum\exceptions\runtime
;

class stream
{
	const defaultProtocol = 'instrumentation';
	const protocolSeparator = '://';

	public $params;
	public $filtername;

	protected $streamFilter = null;

	protected static $controller = null;

	public function __call($method, array $arguments)
	{
		switch ($method)
		{
			case 'filter':
				$arguments[2] = & $arguments[2];
				break;

			case 'url_stat':
			case 'stream_open':
				$arguments[0] = static::setDirectorySeparator(substr($arguments[0], strlen(static::defaultProtocol . static::protocolSeparator)));
		}

		switch (strtolower($method))
		{
			case 'filter':
			case 'oncreate':
			case 'onclose':
				$filter = $this->getFilter()->setStream(static::getController()->getStream())->setParameters($this->params)->setName($this->filtername);

				return call_user_func_array(array($filter, $method), $arguments);

			case 'stream_cast':
			case 'stream_set_option':
				return false;

			case 'stream_open':
			case 'url_stat':
			case 'stream_close':
			case 'stream_eof':
			case 'stream_lock':
			case 'stream_read':
			case 'stream_seek':
			case 'stream_stat':
			case 'stream_tell':
			case 'stream_flush':
				return call_user_func_array(array(static::getController(), $method), $arguments);

			case 'dir_closedir':
			case 'dir_opendir':
			case 'dir_readdir':
			case 'dir_rewinddir':
				throw new logic\badMethodCall(static::defaultProtocol . ' stream does not support directories');

			case 'stream_write':
			case 'stream_truncate':
			case 'mkdir':
			case 'rename':
			case 'rmdir':
			case 'unlink':
				throw new logic\badMethodCall(static::defaultProtocol . ' stream is readonly');

			default:
				throw new logic\badMethodCall('Method ' . $method . ' is not supported by ' . static::defaultProtocol . ' stream');
		}
	}

	public static function getController()
	{
		return (static::$controller = static::$controller ?: new stream\controller());
	}

	public function setFilter(stream\filter $filter)
	{
		$this->streamFilter = $filter;

		return $this;
	}

	public function getFilter()
	{
		return ($this->streamFilter = $this->streamFilter ?: new stream\filter());
	}

	public static function setController(stream\controller $controller)
	{
		static::$controller = $controller;
	}

	public static function set()
	{
		if (in_array(static::defaultProtocol, stream_get_wrappers()) === false && stream_wrapper_register(static::defaultProtocol, get_called_class(), 0) === false)
		{
			throw new runtime('Unable to register ' . static::defaultProtocol . ' stream');
		}

		if (in_array(static::defaultProtocol, stream_get_filters()) === false && stream_filter_register(static::defaultProtocol, get_called_class()) === false)
		{
			throw new runtime('Unable to register ' . static::defaultProtocol . ' filter');
		}

		return static::getController();
	}

	public static function setDirectorySeparator($stream, $directorySeparator = DIRECTORY_SEPARATOR)
	{
		$path = str_replace(($directorySeparator == '/' ? '\\' : '/'), $directorySeparator, preg_replace('#^[^:]+://#', '', $stream));

		return substr($stream, 0, strlen($stream) - strlen($path)) . $path;
	}
}

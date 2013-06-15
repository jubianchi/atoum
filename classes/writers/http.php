<?php

namespace mageekguy\atoum\writers;

use
	mageekguy\atoum,
	mageekguy\atoum\reports,
	mageekguy\atoum\exceptions,
	mageekguy\atoum\report\writers
;


class http extends atoum\writer implements writers\asynchronous
{
	protected $url = null;
	protected $method = null;
	protected $parameter = null;
	protected $headers = array();

	public function __construct($url, $method = null, $parameter = null, array $headers = array(), atoum\adapter $adapter = null)
	{
		parent::__construct($adapter);

		$this->url = $url;
		$this->method = $method ?: 'GET';
		$this->parameter = $parameter;
		$this->headers = $headers;
	}

	public function writeAsynchronousReport(reports\asynchronous $report)
	{
		return $this->write((string) $report);
	}

	public function write($string)
	{
		$headers = array();
		foreach ($this->headers as $name => $value)
		{
			$headers[] = sprintf('%s: %s', $name, $value);
		}

		$context = $this->adapter->stream_context_create(array(
			'http' => array(
				'method'  => $this->method,
				'header'  => implode("\r\n", $headers),
				'content' => $this->parameter ? http_build_query(array($this->parameter => $string)) : $string
			)
		));

		$this->adapter->file_get_contents($this->url, false, $context);

		return $this;
	}

	public function clear()
	{
		return $this;
	}
}

<?php

namespace mageekguy\atoum\report\fields\runner\result\notifier;

use
	mageekguy\atoum,
	mageekguy\atoum\exceptions,
	mageekguy\atoum\report\fields\runner\result\notifier
	;

abstract class remote extends notifier
{
	protected $host;
	protected $port;

	public function __construct($host, $port, atoum\adapter $adapter = null)
	{
		parent::__construct();

		$this
			->setHost($host)
			->setPort($port)
			->setAdapter($adapter)
		;
	}

	public function setHost($host)
	{
		$this->host = $host;

		return $this;
	}

	public function getHost()
	{
		return $this->host;
	}

	public function setPort($port)
	{
		$this->port = $port;

		return $this;
	}

	public function getPort()
	{
		return $this->port;
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
}

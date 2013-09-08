<?php

namespace mageekguy\atoum\report\fields\runner\result\notifier;

use
	mageekguy\atoum,
	mageekguy\atoum\exceptions,
	mageekguy\atoum\report\fields\runner\result\notifier
;

abstract class cli extends notifier
{
	public function __construct(atoum\adapter $adapter = null)
	{
		parent::__construct();

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

	protected function send($title, $message, $success)
	{
		return $this->adapter->system(sprintf(
			$this->getCommand(),
			escapeshellarg($title),
			escapeshellarg($message),
			escapeshellarg($success)
		));
	}

	protected abstract function getCommand();
}

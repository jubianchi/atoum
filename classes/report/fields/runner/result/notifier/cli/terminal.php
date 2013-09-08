<?php

namespace mageekguy\atoum\report\fields\runner\result\notifier\cli;

use
	mageekguy\atoum,
	mageekguy\atoum\report\fields\runner\result\notifier\cli
;

class terminal extends cli
{
	protected $callbackCommand = null;

	public function getCommand()
	{
		return 'terminal-notifier -title %s -message %s -execute %s';
	}

	public function setCallbackCommand($command)
	{
		$this->callbackCommand = $command;

		return $this;
	}

	protected function send($title, $message, $success)
	{
		return $this->adapter->system(sprintf($this->getCommand(), escapeshellarg($title), escapeshellarg($message), escapeshellarg($this->callbackCommand)));
	}
}

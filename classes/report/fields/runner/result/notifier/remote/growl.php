<?php

namespace mageekguy\atoum\report\fields\runner\result\notifier\remote;

use
	mageekguy\atoum,
	mageekguy\atoum\adapter,
	mageekguy\atoum\exceptions\logic,
	mageekguy\atoum\report\fields\runner\result\notifier
;

class growl extends notifier
{
	const defaultPort = 9887;
	const applicationName = 'atoum';
	const successNotification = 'Success';
	const failureNotification = 'Failure';

	protected $host;
	protected $port;

	public function __construct($host, $port = null, adapter $adapter = null)
	{
		parent::__construct($adapter);

		$this
			->setHost($host)
			->setPort($port ?: static::defaultPort)
			->register(array(static::successNotification, static::failureNotification))
		;
	}

	protected function send($title, $message, $success)
	{
		$notification = $success ? static::successNotification : static::failureNotification;
		$this->execute($notification, array($notification . '!', $message));
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
		$this->port = (int) $port;

		return $this;
	}

	public function getPort()
	{
		return $this->port;
	}

	public function write($data)
	{
		$data .= $this->getAdapter()->pack('H32', md5($data));

		$fp = $this->getAdapter()->fsockopen(sprintf('udp://%s', $this->host), $this->port);
		$this->getAdapter()->fwrite($fp, $data);
		$this->getAdapter()->fclose($fp);

		return $this;
	}

	public function register(array $notifications)
	{
		if (sizeof($notifications))
		{
			$data = '';
			$defaults = '';

			foreach ($notifications as $index => $notification)
			{
				$data .= $this->getAdapter()->pack('n', strlen($notification) . $notification);
				$defaults .= $this->getAdapter()->pack('c', $index);
			}

			$packet = $this->getAdapter()->pack('c2nc2', 1, 0, strlen(static::applicationName), count($notifications), count($notifications)) . static::applicationName . $data . $defaults;

			$this->write($packet);
		}

		return $this;
	}

	public function execute($command, array $args)
	{
		$packet = $this->getAdapter()->pack('c2n5', 1, 1, 0, strlen($command), strlen($args[0]), strlen($args[1]), strlen(static::applicationName)) . $command . $args[0] . $args[1] . static::applicationName;

		$this->write($packet);

		return $this;
	}
}

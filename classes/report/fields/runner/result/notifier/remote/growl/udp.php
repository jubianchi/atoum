<?php

namespace mageekguy\atoum\report\fields\runner\result\notifier\remote\growl;

use
	mageekguy\atoum,
	mageekguy\atoum\exceptions\logic,
	mageekguy\atoum\report\fields\runner\result\notifier\remote
;

class udp extends remote
{
	const growlProtocolVersion = 1;
	const growlMethodRegistration = 0;
	const growlMethodNotification = 1;
	const defaultApplicationName = 'atoum';
	const defaultNotificationName = 'Tests';

	private $id;
	private $notificationName;
	private $password;
	private $sticky = false;
	private $priority = 0;

	public function __construct($host, $port, atoum\adapter $adapter = null)
	{
		parent::__construct($host, $port, $adapter);

		$this
			->setApplicationName()
			->setNotificationName()
		;
	}

	public function setApplicationName($applicationName = null)
	{
		$this->id = $applicationName ?: self::defaultApplicationName;

		return $this;
	}

	public function getApplicationName()
	{
		return $this->id;
	}

	public function setNotificationName($notificationName = null)
	{
		$this->notificationName = $notificationName ?: self::defaultNotificationName;

		return $this;
	}

	public function getNotificationName()
	{
		return utf8_encode($this->notificationName);
	}

	public function setPassword($password = null)
	{
		$this->password = $password;

		return $this;
	}

	public function getPassword()
	{
		return $this->password;
	}

	public function setPriority($priority)
	{
		$this->priority = $priority;

		return $this;
	}

	public function getPriority()
	{
		return $this->priority;
	}

	public function setSticky($sticky)
	{
		$this->sticky = $sticky;

		return $this;
	}

	public function isSticky()
	{
		return $this->sticky;
	}

	protected function write($data)
	{
		switch(true)
		{
			case $this->getAdapter()->function_exists('socket_create'):
				$socket = (strlen($this->getAdapter()->inet_pton($this->getHost())) > 4 && defined('AF_INET6'))
					? $this->getAdapter()->socket_create(AF_INET6, SOCK_DGRAM, SOL_UDP)
					: $this->getAdapter()->socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

				$this->getAdapter()->socket_sendto($socket, $data, strlen($data), 0x100, $this->getHost(), $this->getPort());
				$this->getAdapter()->socket_close($socket);
				break;

			case $this->getAdapter()->function_exists('fsockopen'):
				$socket = fsockopen('udp://' . $this->getHost(), $this->getPort());
				fwrite($socket, $data);
				fclose($socket);
				break;
		}

		return $this;
	}

	public function register()
	{
		$data = pack('c2nc2', self::growlProtocolVersion, self::growlMethodRegistration, strlen($this->getApplicationName()), 1, 1);
		$data .= $this->getApplicationName();
		$data .= pack('n', strlen($this->getNotificationName())) . $this->getNotificationName();
		$data .= pack('c', 0);
		$data .= pack('H32', md5($data . $this->getPassword()));

		return $this->write($data);
	}

	public function publish($title, $message)
	{
		$name     = $this->getNotificationName();
		$title    = utf8_encode($title);
		$message  = utf8_encode($message);
		$priority = intval($this->getPriority());

		$flags = ($priority & 7) * 2;
		if ($priority < 0)
		{
			$flags |= 8;
		}

		if ($this->isSticky())
		{
			$flags |= 256;
		}

		$data = pack('c2n5', self::growlProtocolVersion, self::growlMethodNotification, $flags, strlen($name), strlen($title), strlen($message), strlen($this->getApplicationName()));
		$data .= $name . $title . $message . $this->getApplicationName();
		$data .= pack('H32', md5($data . $this->getPassword()));

		return $this->write($data);
	}

	protected function send($title, $message, $success)
	{
		$this
			->register()
			->publish($title, $message)
		;

		return 'Notification sent to ' . $this->getHost() . ':' . $this->getPort();
	}
}

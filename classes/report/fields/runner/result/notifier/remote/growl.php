<?php

    namespace mageekguy\atoum\report\fields\runner\result\notifier\remote;

    use
        mageekguy\atoum,
        mageekguy\atoum\exceptions\logic,
        mageekguy\atoum\report\fields\runner\result\notifier\remote
        ;

    class growl extends remote
    {
        private $_id = 'atoum';
        private $_notificationName = 'Test';
        private $_ip = '127.0.0.1';
        private $_password = '';
        private $_port = 9887;
        private $_sticky = false;
        private $_priority = 0;

        /**
         * @param string $id
         */
        public function setId($id)
        {
            $this->_id = $id;
        }

        /**
         * @return string
         */
        public function getId()
        {
            return $this->_id;
        }

        /**
         * @param string $ip
         */
        public function setIp($ip)
        {
            $this->_ip = $ip;
        }

        /**
         * @return string
         */
        public function getIp()
        {
            return $this->_ip;
        }

        /**
         * @param string $notificationName
         */
        public function setNotificationName($notificationName)
        {
            $this->_notificationName = $notificationName;
        }

        /**
         * @return string
         */
        public function getNotificationName()
        {
            return utf8_encode($this->_notificationName);
        }

        /**
         * @param string $password
         */
        public function setPassword($password)
        {
            $this->_password = $password;
        }

        /**
         * @return string
         */
        public function getPassword()
        {
            return $this->_password;
        }

        /**
         * @param int $port
         */
        public function setPort($port)
        {
            $this->_port = $port;
        }

        /**
         * @return int
         */
        public function getPort()
        {
            return $this->_port;
        }

        /**
         * @param int $priority
         */
        public function setPriority($priority)
        {
            $this->_priority = $priority;
        }

        /**
         * @return int
         */
        public function getPriority()
        {
            return $this->_priority;
        }

        /**
         * @param boolean $sticky
         */
        public function setSticky($sticky)
        {
            $this->_sticky = $sticky;
        }

        /**
         * @return boolean
         */
        public function getSticky()
        {
            return $this->_sticky;
        }

        protected function _send($ip, $port, $data)
        {
            if ((!defined('GROWL_SOCK') && function_exists('socket_create') && function_exists('socket_sendto')) || (GROWL_SOCK === 'socket')) {
                $sck = (strlen(inet_pton($this->getIp())) > 4 && defined('AF_INET6'))
                    ? socket_create(AF_INET6, SOCK_DGRAM, SOL_UDP)
                    : socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
                socket_sendto($sck, $data, strlen($data), 0x100, $ip, $port);
                return true;
            } elseif ((!defined('GROWL_SOCK') && function_exists('fsockopen')) || (GROWL_SOCK === 'fsock')) {
                $fp = fsockopen('udp://' . $ip, $port);
                fwrite($fp, $data);
                fclose($fp);
                return true;
            }

            return false;
        }

        protected function _register()
        {
            $data         = '';
            $defaults     = '';
            $num_defaults = 0;

            $data .= pack('n', strlen($this->getNotificationName())) . $this->getNotificationName();
            $defaults .= pack('c', 0);
            $num_defaults++;

            // pack(Protocol version, type, app name, number of notifications to register)
            $data = pack('c2nc2', 1, 0, strlen($this->getId()), 1, $num_defaults) . $this->getId() . $data . $defaults;
            $data .= pack('H32', md5($data . $this->getPassword()));

            return $this->_send($this->getIp(), $this->getPort(), $data);
        }

        protected function _notify($title, $message)
        {

            $name     = $this->getNotificationName();
            $title    = utf8_encode($title);
            $message  = utf8_encode($message);
            $priority = intval($this->getPriority());

            $flags = ($priority & 7) * 2;
            if ($priority < 0) $flags |= 8;
            if ($this->getSticky()) $flags |= 256;

            // pack(protocol version, type, priority/sticky flags, notification name length, title length, message length. app name length)
            $data = pack('c2n5', 1, 1, $flags, strlen($name), strlen($title), strlen($message), strlen($this->getId()));
            $data .= $name . $title . $message . $this->getId();
            $data .= pack('H32', md5($data . $this->getPassword()));

            return $this->_send($this->getIp(), $this->getPort(), $data);
        }

        public function send($title, $message, $success)
        {
            $register = $this->_register();
            $notify   = $this->_notify($title, $message);
        }
    }

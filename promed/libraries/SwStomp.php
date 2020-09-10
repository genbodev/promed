<?php

/**
 * Class SwStomp
 */
class SwStomp {
    protected $host;
    protected $port;
	protected $login;
	protected $password;
	protected $destPrefix;
    protected $timout;

    /**
     * SwStomp constructor.
     */
    public function __construct($config) {
        $this->host = $config['host'];
        $this->port = $config['port'];
		$this->login = $config['login'];
		$this->password = $config['password'];
		$this->destPrefix = $config['destPrefix'];
        $this->timeout = $config['timeout'];
    }

	/**
	 * @param string $name
	 * @param string $from
	 * @return string
	 */
    protected function createDestination($object, $from = '') {
    	$arr = array();
		$delim = '.';

		if (!empty($this->destPrefix)) {
			$arr[] = trim($this->destPrefix, $delim);
		}
		if (!empty($from)) {
			$arr[] = ucfirst(trim($from, $delim));
		}
		$arr[] = trim($object['name'], $delim);

		return implode($delim, $arr);
	}

    /**
     * @param string $objectName
     * @return array
     */
    protected function createBody($object) {
        return json_encode($object['data']);
    }

    /**
     * @param string $objectName
     * @return array
     */
    protected function createProperties($object) {
        $properties = array(
        	'amq-msg-type' => 'text',
			'content-type' => 'text/plain',
        	'type' => $object['operation']
		);
        return $properties;
    }

    /**
     * @param array $objects
     * @throws Exception
     */
    public function publicate($objects, $from = '') {
        $url = $this->host.':'.$this->port;

        try {
            $stomp = new Stomp($url, $this->login, $this->password);
            $stomp->setReadTimeout($this->timeout);

            foreach ($objects as $object) {
                $resp = $stomp->send(
					$this->createDestination($object, $from),
                    $this->createBody($object),
                    $this->createProperties($object)
                );
            }

            unset($stomp);
        } catch(Exception $e) {
            if (isset($stomp)) unset($stomp);
            throw $e;
        }
    }
}
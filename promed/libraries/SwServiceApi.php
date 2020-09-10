<?php

class SwServiceApi {
    private $url = null;
    private $timeout = 30;
    private $name = null;
    private $nick = null;

    public $auth_error;

    /**
     * SwServiceApi constructor.
     * @param array $config
     */
    public function __construct($config = NULL) {
    	if (empty($config)) {
    		throw new Exception('Не указан конфиг API');
		}

        // Переменные класса
        $this->url = $config['apiurl'];
        $this->timeout = $config['timeout'];
        $this->name = $config['name'];
        $this->nick = $config['nick'];
    }

    /**
     * @param string $type
     * @param string $method
     * @param array|null $data
     * @return mixed
     */
    public function data($type, $method, $data = null, $resultType = null, $as_json = false) {

        $service = curl_init();
        $result = array();
		$session_id = session_id();

		if (strlen($method) > 0 && $method[0] != '/' && $this->url[strlen($this->url) - 1] != '/') {
			$url = $this->url.'/'.$method;
		} else {
			$url = $this->url.$method;
		}

		$dataUrl = '';
		if (is_array($data)) {
			unset($data['session']);
			$dataUrlArr = array();

			// для авторизации по токену
			if (!empty($_SESSION['swtoken'])) {
				$data['swtoken'] = $_SESSION['swtoken'];
			}

			array_walk_recursive($data, function(&$value, $key) {
				if ($value instanceof DateTime) {
					$value = $value->format('Y-m-d H:i:s').'.'.substr($value->format('u'), 0, 3);
				}
			});

			if ($as_json) {

				$dataUrl = json_encode($data);
				curl_setopt($service, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));

			} else {

				foreach($data as $key => $value) {
					if (is_array($value)) {
						$value = json_encode($value);
					}
					$dataUrlArr[] = $key.'='.$value;
				}

				$dataUrl = str_replace('+','%2B',implode('&', $dataUrlArr));
				$dataUrl = str_replace(' ', '%20', $dataUrl);
			}
		}

        if (strtolower($type)=='get' && !empty($dataUrl)) {
            $url .= (strpos($url, '?') === false)?'?':'&';
            $url .= $dataUrl;
			//$url .= '&sql_debug=1';
        }

        curl_setopt($service, CURLOPT_URL, $url);
        curl_setopt($service, CURLOPT_CONNECTTIMEOUT, $this->timeout);
        curl_setopt($service, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($service, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($service, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($service, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($service, CURLOPT_POST, false);

		$session_id = session_id();
		if (!empty($session_id)) {
			curl_setopt($service, CURLOPT_COOKIE, 'PHPSESSID='.$session_id);
		}

        if (strtolower($type)=='post') {
            curl_setopt($service, CURLOPT_POST, true);
            curl_setopt($service, CURLOPT_POSTFIELDS, $dataUrl);
        }

        if (strtolower($type)=='put') {
			curl_setopt($service, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($service, CURLOPT_POSTFIELDS, $dataUrl);
        }
        if (strtolower($type)=='patch') {
			curl_setopt($service, CURLOPT_CUSTOMREQUEST, 'PATCH');
            curl_setopt($service, CURLOPT_POSTFIELDS, $dataUrl);
        }
        if (strtolower($type)=='delete') {
            curl_setopt($service, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($service, CURLOPT_POSTFIELDS, $dataUrl);
        }

        $response = curl_exec($service);
        $response_code = curl_getinfo($service, CURLINFO_HTTP_CODE);

		if (!empty($_REQUEST['getDebug'])) {
			echo "request url: ".$url."<br>";
			echo "request type: ".$type."<br>";
			$fields = '';
			switch($_REQUEST['getDebug']){
				case 2:
					foreach ($data as $key => $field) {
						if (is_array($field)) {
							$fields .= $key . ":" . json_encode($field, true) . "\n";
						} else {
							$fields .= $key . ":" . $field . "\n";
						}
					}
					break;
				default:
					$fields .= print_r($data, true);
			}
			echo "<textarea cols=150 rows=20>" . $fields . "</textarea><br><br>";
			echo "response_code: " . $response_code."<br>";
			echo "<textarea cols=150 rows=20>" . $response . "</textarea><br><br>";
		}

		$result = json_decode($response, true);
		if (!empty($response) && empty($result)) {
			curl_close($service);
			die($response);exit;
		}

		if ($response_code != 200) {
			$err = curl_error($service);
			if (is_array($result) && !empty($result['error_msg'])) {
				$err = $result['error_msg'];
			} else if (empty($err)) {
				$err = "Ресурс {$method} вернул код ошибки {$response_code}";
			}
			$result = array();
			$result['success'] = false;
			$result['error_msg'] = "Ошибка взаимодействия с сервисом {$this->name}: {$err}";
			$result['error_code'] = $response_code;
		}

		curl_close($service);

        if ($resultType) {
        	return processRestResponse($result, $resultType);
		}

        return $result;
    }

	/**
	 * @param string $method
	 * @param array|null $data
	 * @return mixed
	 */
    public function GET($method, $data = null, $resultType = null) {
    	return $this->data("GET", $method, $data, $resultType);
	}

	/**
	 * @param string $method
	 * @param array|null $data
	 * @return mixed
	 */
    public function POST($method, $data = null, $resultType = null, $as_json = false) {
    	return $this->data("POST", $method, $data, $resultType, $as_json);
	}

	/**
	 * @param string $method
	 * @param array|null $data
	 * @return mixed
	 */
    public function PUT($method, $data = null, $resultType = null) {
    	return $this->data("PUT", $method, $data, $resultType);
	}

	/**
	 * @param string $method
	 * @param array|null $data
	 * @return mixed
	 */
    public function PATCH($method, $data = null, $resultType = null) {
    	return $this->data("PATCH", $method, $data, $resultType);
	}

	/**
	 * @param string $method
	 * @param array|null $data
	 * @return mixed
	 */
    public function DELETE($method, $data = null, $resultType = null) {
    	return $this->data("DELETE", $method, $data, $resultType);
	}
}
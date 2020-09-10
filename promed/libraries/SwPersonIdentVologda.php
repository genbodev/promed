<?php

class SwPersonIdentVologda {
	protected $url;
	protected $token;
	protected $timeout;
	protected $soapClient;

	protected $tryCount = 0;
	protected $allowTryCount = 3;

	/**
	 * Constructor
	 */
	public function __construct($config) {
		$this->url = $config['url'];
		$this->token = $config['token'];
		$this->timeout = $config['timeout'];
	}

	/**
	 * @throws Exception
	 */
	public function connect() {
		$options = array(
			'soap_version' => SOAP_1_1,
			'exceptions' => 1,
			'trace' => 1,
			'connection_timeout' => $this->timeout
		);

		try {
			$this->tryCount++;
			$error_msg = "Не удалось установить соединение с сервисом";
			set_error_handler(function() use ($error_msg) { throw new Exception($error_msg); }, E_ALL & ~E_NOTICE);
            $this->soapClient = new SoapClient($this->url, $options);
            restore_error_handler();
            $this->tryCount = 0;
        } catch (Exception $e) {
			restore_error_handler();
			if ($this->tryCount < $this->allowTryCount) {
				sleep(2);
				$this->connect();
			} else {
				throw $e;
			}
		}
	}

	/**
	 * @param srting $method
	 * @param array $params
	 * @return stdClass
	 * @throws Exception
	 */
	public function exec($method, $params) {
		$result = null;
		try {
			$this->tryCount++;
			$error_msg = "Ошибка при запросе к серверу";
			set_error_handler(function() use ($error_msg) { throw new Exception($error_msg); }, E_ALL & ~E_NOTICE);
			$result = $this->soapClient->$method($params);
			restore_error_handler();
			$this->tryCount = 0;
		} catch (Exception $e) {
			restore_error_handler();
			if ($this->tryCount < $this->allowTryCount) {
				sleep(2);
				$result = $this->exec($method, $params);
			} else {
				throw $e;
			}
		}
		return $result;
	}

	/**
	 * @param array $person
	 * @param DateTime|null $identDate
	 * @return mixed|null
	 */
	public function getMedInsState($person, $identDate = null) {
		$params = array(
			'token' => $this->token,
			'searchActive' => false,
			'familyName' => $person['Person_SurName'],
			'firstName' => $person['Person_FirName'],
			'middleName' => '',
			'birthDate' => $person['Person_BirthDay'],
			'insId' => '',
			'enp' => '',
			'docType' => '',
			'docIdent' => '',
			'snils' => '',
		);

		if (!empty($person['Person_SecName'])) {
			$params['middleName'] = $person['Person_SecName'];
		}
		if (!empty($person['PolisType_id']) && !empty($person['Polis_Num'])) {
			if ($person['PolisType_id'] == 1 && !empty($person['Polis_Ser'])) {
				$params['insId'] = $person['Polis_Ser'].'№'.$person['Polis_Num'];
			} else {
				$params['insId'] = $person['Polis_Num'];
			}
		}
		if (!empty($person['DocumentType_Code']) && !empty($person['Document_Num'])) {
			$params['docType'] = $person['DocumentType_Code'];

			if (!empty($person['Document_Ser'])) {
				if (($person['DocumentType_Code'] == 1 || $person['DocumentType_Code'] == 3) && mb_strlen($person['Document_Ser']) == 3) {
					$person['Document_Ser'] = substr($person['Document_Ser'], 0, 1).'-'.substr($person['Document_Ser'], 1);
				}
				if ($person['DocumentType_Code'] == 14 && mb_strlen($person['Document_Ser']) == 4) {
					$person['Document_Ser'] = substr($person['Document_Ser'], 0, 2).' '.substr($person['Document_Ser'], 2);
				}

				$params['docIdent'] = $person['Document_Ser'].'№'.$person['Document_Num'];
			} else {
				$params['docIdent'] = $person['Document_Num'];
			}
		}
		if (!empty($person['Person_Snils'])) {
			$params['snils'] = $person['Person_Snils'];
		}
		if (!empty($person['Person_ENP'])) {
			$params['enp'] = $person['Person_ENP'];
		}

		$result = $this->exec('GetMedInsState', $params);
		$result = objectToArray($result);
		$list = isset($result['return'])?$result['return']:array();

		if (!isset($list[0])) {
			$list = array($list);
		}

		$getDate = function($item, $dateField) {
			return ($item && !empty($item[$dateField]))?date_create($item[$dateField]):null;
		};
		$isIncludeIdentDate = function($beg, $end) use($identDate) {
			if (!$identDate) return true;
			return ($beg <= $identDate && (!$end || $end > $identDate));
		};

		$response = null;
		foreach($list as $item) {
			$respBeg = $getDate($response, 'InsBegin');
			$respEnd = $getDate($response, 'InsEnd');
			$itemBeg = $getDate($item, 'InsBegin');
			$itemEnd = $getDate($item, 'InsEnd');

			if ($respBeg < $itemBeg) {
				$response = $item;
			}
		}

		if ($response) {
			$response['InsSer'] = '';
			$response['InsNum'] = $response['InsId'];
			$regexp = '/^(\d+)\s№\s(\d+)$/';

			if (!empty($response['InsId']) && preg_match($regexp, $response['InsId'], $matches)) {
				$response['InsSer'] = $matches[1];
				$response['InsNum'] = $matches[2];
			}
			if (isset($response['IC'])) {
				$response['InnerICCode'] = $response['IC']['InnerICCode'];
				unset($response['IC']);
			}
			if (empty($response['InsEnd']) && !empty($response['DateOfDeath'])) {
				$response['InsEnd'] = $response['DateOfDeath'];
			}
		}

		return $response;
	}

	/**
	 * @param array $enp
	 * @return array
	 */
	public function getAttach($enp) {
		$params = array(
			'ENP' =>$enp,
			'token' => $this->token,
		);

		$result = $this->exec('GetAttach', $params);
		$result = objectToArray($result);

		return ($result['return'] === 0)?$result['attach']:false;
	}
}
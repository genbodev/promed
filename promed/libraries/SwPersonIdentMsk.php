<?php

class SwPersonIdentMsk {
	protected $url;
	protected $username;
	protected $password;
	protected $timeout;

	/**
	 * Constructor
	 */
	public function __construct($config) {
		$this->url = $config['url'];
		$this->username = $config['username'];
		$this->password = $config['password'];
		$this->timeout = $config['timeout'];
		
		$this->CI = &get_instance();
		$this->CI->load->helper('xml');
		$this->CI->load->library('parser');
	}
	
	/**
	 * @param string $method
	 * @param mixed $data
	 * @return string
	 * @throws Exception
	 */
	protected function exec($method, $data) {
		$curl = curl_init();
		
		curl_setopt($curl, CURLOPT_URL, $this->url."{$method}.htm");
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->timeout);
		curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_USERPWD, "{$this->username}:{$this->password}");
		curl_setopt($curl, CURLOPT_HTTPHEADER, [
			'Content-Type: text/xml; charset=Utf-8'
		]);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
		curl_setopt($curl, CURLOPT_POST, false);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		
		$result = curl_exec($curl);
		$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		
		curl_close($curl);
		
		if (empty($http_code)) {
			$http_code = 400;
		}
		if ($http_code != 200) {
			throw new Exception($http_code, "Ошибка соединения с сервисом");
		}
		
		return $result;
	}
	
	/**
	 * @param array $person
	 * @return string
	 */
	protected function createRequest($person) {
		$data = [
			'ID' => $person['PersonRequestData_ReqGUID'],
			'FAM' => $person['Person_SurName'],
			'IM' => $person['Person_FirName'],
			'OT' => $person['Person_SecName'],
			'MR' => null,
			'DR' => $person['Person_BirthDay'],
			'DOCTYPE' => $person['DocumentType_Code'],
			'DOCSER' => $person['Document_Ser'],
			'DOCNUM' => $person['Document_Num'],
			'SS' => $person['Person_Snils'],
		];
		
		$template = 'export_xml/person_ident_package_msk';
		
		$xml = $this->CI->parser->parse_ext($template, $data, true);
		$xml = "<?xml version=\"1.0\" encoding=\"Utf-8\" standalone=\"yes\"?>\n".$xml;
		
		return $xml;
	}
	
	/**
	 * @param array $person
	 * @return array|false
	 */
	public function getMedInsState($person) {
		$request = $this->createRequest($person);
		$response = $this->exec('WebGetIns', $request);
		$result = simplexml_load_string($response);
		
		if (empty($result) || 
			in_array($result->Header->ACK, ['E','W']) || 
			empty($result->Content->Insurances) || 
			$result->Content->Insurances->IsEmpty
		) {
			return null;
		}
		
		$activeInsurance = null;
		foreach($result->Content->Insurances->Insurance as $insurance) {
			if ($insurance->IsActive) {
				$activeInsurance = $this->prepareResult($insurance);
				break;
			}
		}
		
		return $activeInsurance;
	}
	
	/**
	 * @param SimpleXMLElement $xmlElement
	 * @return array
	 */
	protected function prepareResult($xmlElement) {
		$result = simpleXMLToArray($xmlElement);
		foreach($result as $key => $value) {
			if (is_array($value) && empty($value)) {
				$result[$key] = null;
			}
		}
		return $result;
	}
}
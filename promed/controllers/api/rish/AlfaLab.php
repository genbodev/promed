<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package            API
 * @access            public
 */

require(APPPATH . 'libraries/SwREST_Controller.php');

/**
 * @property AlfaLab_model $dbmodel
 */
class AlfaLab extends SwREST_Controller
{
	protected $inputRules = [
		'GetNewRequest' => [
		]
	];

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();

		$this->load->database();
		$this->load->model('AlfaLab_model', 'dbmodel');
	}

	/**
	 * Получаем тело SOAP запроса
	 */
	function processRequest()
	{
		$xml = file_get_contents("php://input");
		//убираем неймспейсы
		$clearXML = str_ireplace(['soapenv:', 'ser:', 'dat:'], '', $xml);
		$clearXML = simplexml_load_string($clearXML);
		return json_decode(json_encode($clearXML), true);
	}

	/**
	 * Запрос заявки на ЛИС
	 */
	function newRequest_post()
	{
		$data = $this->ProcessInputData('GetNewRequest', null, true);
		$clearXML = $this->processRequest();

		$data['MedService_id'] = $clearXML['Body']['GetNewRequest']['OrganizationCode'];
		$this->response($this->dbmodel->newRequest($data));
	}

	/**
	 * Установка статуса принятия заявки в ЛИС
	 */
	function RequestProcessingStatus_post()
	{
		$data = $this->ProcessInputData('GetNewRequest', null, true);
		$clearXML = $this->processRequest();

		$data['EvnLabRequest_id'] = $clearXML['Body']['RequestProcessingStatus']['RequestCode'];
		$data['State'] = $clearXML['Body']['RequestProcessingStatus']['State'];
		$data['ErrorText'] = $clearXML['Body']['RequestProcessingStatus']['ErrorText'];

		$this->response($this->dbmodel->RequestProcessingStatus($data));
	}

	/**
	 *
	 */
	function SendResultObtained_post()
	{
		$data = $this->ProcessInputData('GetNewRequest', null, true);
		$clearXML = $this->processRequest();
		
		$data['SendResultObtained'] = $clearXML['Body']['SendResultObtained'];
		$this->response($this->dbmodel->SendResultObtained($data));
	}

	/**
	 *
	 */
	function renderedServiceProtocols_post()
	{
		$data = $this->ProcessInputData('GetNewRequest', null, true);
		$clearXML = $this->processRequest();

		$data['SendResultObtained'] = $clearXML['Body']['SendResultObtained'];
		$this->response($this->dbmodel->renderedServiceProtocols($data));
	}
	
	/**
	*/
	function checkForResults_get()
	{
		$data = $this->ProcessInputData('GetNewRequest', null, true);
		$uslugas = $this->dbmodel->getApprovedUslugas();
		foreach ($uslugas as $usluga) {
			// добавить к uri env - данные о среде исполнения
			$url = $this->config['AlfaLab_URL'];
			$url .= "/medservices-ws/service-rs/renderedServiceProtocols";
			$url .= "/{$usluga['EvnUslugaPar_id']}";
			$headers = array(
				"Accept-Encoding: gzip,deflate",
				"Content-Type: application/json",
				"Pragma: no-cache",
				"Content-length: 45"
			);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$response = curl_exec($ch);
			$code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
			curl_close($ch);
			if (!empty($response) && $code == 200) {
				$resp = json_decode($ch, true);
				if (empty($resp['path'])) {
					continue;
				}
				$resp = simplexml_load_file($this->config['AlfaLab_URL'] .'/'. $resp['path']);
				if (!$resp) {
					continue;
				}
				$clearXML = json_decode(json_encode($resp), true);
				if (!isset($clearXML['data']['content']['data']['events']['data']['items']))
					continue;
				$data['result'] = $clearXML['data']['content']['data']['events']['data']['items'];
				$data['EvnUslugaPar_id'] = $usluga['EvnUslugaPar_id'];
				$data['xml'] = $resp->asXML();
				
				$this->dbmodel->processTestData($data);
			}
		}
	}
}

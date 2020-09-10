<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* PolisUpdateClient - контроллер для выполнения запроса к серверу БДЗ на получение данных о полисе и застрахованном человеке
*                     и сохранение данных в базе Промед
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Services
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      28.10.2010
*/


class PolisUpdateClient extends swController {
	protected $soapClient = null;

	/**
	 * PolisUpdateClient constructor.
	 */
	public function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->model("PolisUpdateClient_model", "dbmodel");
		$this->load->helper('Date');
		/*
		try {
			$this->soapClient = new SoapClient('http://172.22.99.4/Service.aspx?wsdl', array("connection_timeout" => 15, "classmap" => array()));
		}
		catch (SoapFault $e) {
			echo $e->getMessage();
		}
		*/
	}

	/**
	 * @return bool
	 */
	public function Index() {
		// 1. Получаем список полисов на обновление (PolisQueue)
		// 2. Получаем список логинов/паролей СМО
		// 3. Делаем запрос к сервису на получение данных
		// 4. Обрабатываем полученные данные

		$data       = array();
		$orgSmoList = array();

		// 1. Получаем список полисов на обновление (PolisQueue)
		$polisQueueResponse = $this->dbmodel->getPolisQueueList();

		if ( is_array($polisQueueResponse) ) {
			// Сортируем полученные данные по OrgSmo_id
			foreach ( $polisQueueResponse as $record ) {
				if ( array_key_exists('OrgSmo_id', $record) && is_numeric($record['OrgSmo_id']) && !array_key_exists($record['OrgSmo_id'], $orgSmoList) ) {
					$orgSmoList[$record['OrgSmo_id']] = array();
					$data[$record['OrgSmo_id']] = array();
				}

				$data[$record['OrgSmo_id']][] = $record;
			}
		}
		else {
			echo 'Ошибка при выполнении запроса к базе данных (получение списка идентификаторов полисов)';
			return false;
		}

		if ( count($orgSmoList) == 0 ) {
			echo 'Нет записей в PolisQueue';
			return false;
		}

		// 2. Получаем список логинов/паролей СМО
		$orgSmoDataResponse = $this->dbmodel->getOrgSmoData(array_keys($orgSmoList));

		// 3. Делаем запрос к сервису на получение данных
		foreach ( $data as $org_smo_id => $orgSmoData ) {
			var_dump($orgSmoData);

			// $orgSmoData['login'] = $orgSmoList[$org_smo_id]['login'];
			// $orgSmoData['password'] = $orgSmoList[$org_smo_id]['password'];

			// $personPolisData = $this->soapClient->getPersonPolisData(new SoapVar(array("polisIdList" => $orgSmoData), SOAP_ENC_OBJECT));

			// 4. Обрабатываем полученные данные
		}

		return true;
	}
}

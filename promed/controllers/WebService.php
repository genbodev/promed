<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* WebService - контроллер для обработки данных, полученных через сервисы
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      03.08.2010
*/


class WebService extends swController {
	
	var $NeedCheckLogin = false;
	
	public $inputRules = array(
		'putPerson' => array(
			array(
				'field' => 'bdzID',
				'label' => 'Идентификатор застрахованного в БДЗ',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'surName',
				'label' => 'Фамилия',
				'rules' => 'trim|required',
				'type' => 'string'
			),
			array(
				'field' => 'firName',
				'label' => 'Имя',
				'rules' => 'trim|required',
				'type' => 'string'
			),
			array(
				'field' => 'secName',
				'label' => 'Отчество',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'birthDay',
				'label' => 'Дата рождения',
				'rules' => 'trim|required',
				'type' => 'date'
			),
			array(
				'field' => 'edNum',
				'label' => 'Единый номер',
				'rules' => 'trim|required',
				'type' => 'string'
			),
			array(
				'field' => 'uaddressKladr',
				'label' => 'Код КЛАДР адреса регистрации',
				'rules' => 'trim|required',
				'type' => 'string'
			),
			array(
				'field' => 'uaddressHome',
				'label' => 'Номер дома из адреса регистрации',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'uaddressFlat',
				'label' => 'Номер квартиры из адреса регистрации',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'regNomC',
				'label' => 'Код СМО (regNomC)',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'regNomN',
				'label' => 'Код СМО (regNomN)',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'polisSer',
				'label' => 'Серия полиса',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'polisNum',
				'label' => 'Номер полиса',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'polisBegDate',
				'label' => 'Дата выдачи полиса',
				'rules' => 'trim|required',
				'type' => 'date'
			),
			array(
				'field' => 'sex',
				'label' => 'Пол',
				'rules' => 'required',
				'type' => 'int'
			)
		),
		'putPersonCardState' => array(
			array(
				'field' => 'status',
				'label' => 'Статус записи',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'transactCode',
				'label' => 'Идентификатор записи',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'setPersonCardQueueStatus' => array(
			array(
				'field' => 'status',
				'label' => 'Статус записи',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'transactCode',
				'label' => 'Идентификатор записи',
				'rules' => 'required',
				'type' => 'id'
			)
		)
	);


	/**
	 * Description
	 */
	public function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->model("WebService_model", "dbmodel");
		// $this->load->helper('Date');
	}


	/**
	 * Description
	 */
	public function Index() {
		return true;
	}


	/**
	 * Description
	 */
	public function getPersonCardQueueList() {
		$result  = array();

		$response = $this->dbmodel->getPersonCardQueueList();
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
	}

	/*
	public function putPerson() {
		$data = array();
		$result  = array();

		$data = $this->ProcessInputData('putPerson', false);
		if ($data === false) { return false; }
		
		$response = array(
			'status' => 0,
			'errorCode' => 0,
			'errorMessage' => ''
		);

		// Обработка входящих параметров
		if ( !is_array($data) ) {
			$response['status'] = -1;
			$response['errorCode'] = 100500;
			$response['errorMessage'] = toUTF('Неверные параметры');
			return $response;
		}

		if ( !isset($data['bdzID']) || !is_numeric($data['bdzID']) || $data['bdzID'] <= 0 ) {
			$response['status'] = -1;
			$response['errorCode'] = 100501;
			$response['errorMessage'] = toUTF('Неверный идентификатор застрахованного');
			return $response;
		}

		if ( !isset($data['surName']) || strlen(trim($data['surName'])) == 0 ) {
			$response['status'] = -1;
			$response['errorCode'] = 100502;
			$response['errorMessage'] = toUTF('Не задана фамилия застрахованного');
			return $response;
		}

		if ( !isset($data['firName']) || strlen(trim($data['firName'])) == 0 ) {
			$response['status'] = -1;
			$response['errorCode'] = 100503;
			$response['errorMessage'] = toUTF('Не задано имя застрахованного');
			return $response;
		}

		if ( !isset($data['secName']) || strlen(trim($data['secName'])) == 0 || trim($data['secName']) == '- - -' ) {
			$data['secName'] = '';
		}

		if ( !isset($data['birthDay']) ) {
			$response['status'] = -1;
			$response['errorCode'] = 100504;
			$response['errorMessage'] = toUTF('Не задана дата рождения застрахованного');
			return $response;
		}
		else if ( CheckDateFormat($data['birthDay']) > 0 ) {
			$response['status'] = -1;
			$response['errorCode'] = 100505;
			$response['errorMessage'] = toUTF('Неверный формат даты рождения застрахованного');
			return $response;
		}
		else {
			$data['birthDay'] = ConvertDateFormat($data['birthDay']);
		}

		if ( !isset($data['edNum']) ) {
			$response['status'] = -1;
			$response['errorCode'] = 100506;
			$response['errorMessage'] = toUTF('Не задан единый номер застрахованного');
			return $response;
		}
		else if ( !preg_match("/^\d{16}$/", $data['edNum']) ) {
			$response['status'] = -1;
			$response['errorCode'] = 100507;
			$response['errorMessage'] = toUTF('Неверный формат единого номера застрахованного');
			return $response;
		}

		if ( !isset($data['uaddressKladr']) ) {
			$response['status'] = -1;
			$response['errorCode'] = 100508;
			$response['errorMessage'] = toUTF('Не задан код КЛАДР адреса регистрации застрахованного');
			return $response;
		}
		else if ( !preg_match("/^\d+$/", $data['uaddressKladr']) && !in_array(strlen($data['uaddressKladr']), array(13, 17)) ) {
			$response['status'] = -1;
			$response['errorCode'] = 100509;
			$response['errorMessage'] = toUTF('Неверный формат кода КЛАДР адреса регистрации застрахованного');
			return $response;
		}

		if ( !isset($data['uaddressHome']) || strlen(trim($data['uaddressHome'])) == 0 ) {
			$data['uaddressHome'] = '';
		}

		if ( !isset($data['uaddressFlat']) || strlen(trim($data['uaddressFlat'])) == 0 ) {
			$data['uaddressFlat'] = '';
		}

		if ( !isset($data['regNomC']) || !is_numeric($data['regNomC']) || $data['regNomC'] <= 0 ) {
			$response['status'] = -1;
			$response['errorCode'] = 100510;
			$response['errorMessage'] = toUTF('Неверный формат кода СМО regNomC');
			return $response;
		}

		if ( !isset($data['regNomN']) || !is_numeric($data['regNomN']) || $data['regNomN'] <= 0 ) {
			$response['status'] = -1;
			$response['errorCode'] = 100511;
			$response['errorMessage'] = toUTF('Неверный формат кода СМО regNomN');
			return $response;
		}

		if ( !isset($data['polisSer']) || strlen(trim($data['polisSer'])) == 0 ) {
			$response['status'] = -1;
			$response['errorCode'] = 100512;
			$response['errorMessage'] = toUTF('Не задана серия полиса');
			return $response;
		}

		if ( !isset($data['polisNum']) || !is_numeric($data['polisNum']) || $data['polisNum'] <= 0 ) {
			$response['status'] = -1;
			$response['errorCode'] = 100513;
			$response['errorMessage'] = toUTF('Не задан номер полиса');
			return $response;
		}

		if ( !isset($data['polisBegDate']) ) {
			$response['status'] = -1;
			$response['errorCode'] = 100514;
			$response['errorMessage'] = toUTF('Не задана дата выдачи полиса');
			return $response;
		}
		else if ( CheckDateFormat($data['polisBegDate']) > 0 ) {
			$response['status'] = -1;
			$response['errorCode'] = 100515;
			$response['errorMessage'] = toUTF('Неверный формат даты выдачи полиса');
			return $response;
		}
		else {
			$data['polisBegDate'] = ConvertDateFormat($data['polisBegDate']);
		}

		if ( !isset($data['sex']) || !is_numeric($data['sex']) || $data['sex'] <= 0 ) {
			$response['status'] = -1;
			$response['errorCode'] = 100516;
			$response['errorMessage'] = toUTF('Не задан пол');
			return $response;
		}

		if ( !isset($data['socStatus']) || !is_numeric($data['socStatus']) || $data['socStatus'] <= 0 ) {
			$data['socStatus'] = 0;
		}

		if ( !isset($data['snils']) || strlen(trim($data['snils'])) != 11 ) {
			$data['snils'] = '';
		}

		if ( !isset($data['paddressKladr']) ) {
			$data['paddressKladr'] = '';
		}
		else if ( !preg_match("/^\d+$/", $data['paddressKladr']) && !in_array(strlen($data['paddressKladr']), array(13, 17)) ) {
			$data['paddressKladr'] = '';
		}

		if ( !isset($data['paddressHome']) || strlen(trim($data['paddressHome'])) == 0 ) {
			$data['paddressHome'] = '';
		}

		if ( !isset($data['paddressFlat']) || strlen(trim($data['paddressFlat'])) == 0 ) {
			$data['paddressFlat'] = '';
		}

		if ( !isset($data['sprTerr']) || !is_numeric($data['sprTerr']) || $data['sprTerr'] <= 0 ) {
			$data['sprTerr'] = 0;
		}

		$data['Server_id'] = 0;

		array_walk($data, 'convertFromUTF8ToWin1251');

		// Надо получить Person_id(+), Sex_id, OrgSmo_id, KLRgn_uid, KLSubRgn_uid, KLCity_uid, KLTown_uid, KLStreet_uid...

		$queryResponse = $this->dbmodel->checkPersonExists($data['bdzID']);

		if ( strlen($queryResponse['Error_Msg']) > 0 ) {
			$response['status'] = -1;
			$response['errorCode'] = 100550;
			$response['errorMessage'] = toUTF($queryResponse['Error_Msg']);
			return $response;
		}

		$data['Person_id'] = $queryResponse['Person_id'];

		$this->dbmodel->beginTransaction();

		if ( $data['Person_id'] == 0 ) {
			// Добавляем человека в базу
			$queryResponse = $this->dbmodel->addPerson($data);

			if ( is_array($queryResponse) && count($queryResponse) > 0 ) {
				if ( strlen($queryResponse[0]['Error_Msg']) == 0 ) {
					if ( $queryResponse[0]['Person_id'] > 0 ) {
						$data['Person_id'] = $queryResponse[0]['Person_id'];
					}
					else {
						$response['status'] = -1;
						$response['errorCode'] = 100551;
						$response['errorMessage'] = toUTF($queryResponse[0]['Error_Msg']);
						$this->dbmodel->rollbackTransaction();
						return $response;
					}
				}
				else {
					$response['status'] = -1;
					$response['errorCode'] = 100552;
					$response['errorMessage'] = toUTF('Ошибка при добавлении человека в базу данных');
					$this->dbmodel->rollbackTransaction();
					return $response;
				}
			}
			else {
				$response['status'] = -1;
				$response['errorCode'] = 100553;
				$response['errorMessage'] = toUTF('Ошибка при выполнении запроса к базе данных (добавление человека)');
				$this->dbmodel->rollbackTransaction();
				return $response;
			}
		}

		// Добавляем/обновляем фамилию
		$queryResponse = $this->dbmodel->addPersonSurname($data);

		if ( is_array($queryResponse) && count($queryResponse) > 0 ) {
			if ( strlen($queryResponse[0]['Error_Msg']) > 0 ) {
				$response['status'] = -1;
				$response['errorCode'] = 100561;
				$response['errorMessage'] = toUTF($queryResponse[0]['Error_Msg']);
				$this->dbmodel->rollbackTransaction();
				return $response;
			}
		}
		else {
			$response['status'] = -1;
			$response['errorCode'] = 100562;
			$response['errorMessage'] = toUTF('Ошибка при выполнении запроса к базе данных (добавление/обновление фамилии)');
			$this->dbmodel->rollbackTransaction();
			return $response;
		}

		// Добавляем/обновляем имя
		$queryResponse = $this->dbmodel->addPersonFirname($data);

		if ( is_array($queryResponse) && count($queryResponse) > 0 ) {
			if ( strlen($queryResponse[0]['Error_Msg']) > 0 ) {
				$response['status'] = -1;
				$response['errorCode'] = 100571;
				$response['errorMessage'] = toUTF($queryResponse[0]['Error_Msg']);
				$this->dbmodel->rollbackTransaction();
				return $response;
			}
		}
		else {
			$response['status'] = -1;
			$response['errorCode'] = 100572;
			$response['errorMessage'] = toUTF('Ошибка при выполнении запроса к базе данных (добавление/обновление имени)');
			$this->dbmodel->rollbackTransaction();
			return $response;
		}

		// Добавляем/обновляем отчество
		$queryResponse = $this->dbmodel->addPersonSecname($data);

		if ( is_array($queryResponse) && count($queryResponse) > 0 ) {
			if ( strlen($queryResponse[0]['Error_Msg']) > 0 ) {
				$response['status'] = -1;
				$response['errorCode'] = 100571;
				$response['errorMessage'] = toUTF($queryResponse[0]['Error_Msg']);
				$this->dbmodel->rollbackTransaction();
				return $response;
			}
		}
		else {
			$response['status'] = -1;
			$response['errorCode'] = 100572;
			$response['errorMessage'] = toUTF('Ошибка при выполнении запроса к базе данных (добавление/обновление отчества)');
			$this->dbmodel->rollbackTransaction();
			return $response;
		}

		// Добавляем/обновляем дату рождения
		$queryResponse = $this->dbmodel->addPersonBirthday($data);

		if ( is_array($queryResponse) && count($queryResponse) > 0 ) {
			if ( strlen($queryResponse[0]['Error_Msg']) > 0 ) {
				$response['status'] = -1;
				$response['errorCode'] = 100581;
				$response['errorMessage'] = toUTF($queryResponse[0]['Error_Msg']);
				$this->dbmodel->rollbackTransaction();
				return $response;
			}
		}
		else {
			$response['status'] = -1;
			$response['errorCode'] = 100582;
			$response['errorMessage'] = toUTF('Ошибка при выполнении запроса к базе данных (добавление/обновление даты рождения)');
			$this->dbmodel->rollbackTransaction();
			return $response;
		}

		// Добавляем/обновляем единый номер
		$queryResponse = $this->dbmodel->addPersonPolisEdNum($data);

		if ( is_array($queryResponse) && count($queryResponse) > 0 ) {
			if ( strlen($queryResponse[0]['Error_Msg']) > 0 ) {
				$response['status'] = -1;
				$response['errorCode'] = 100591;
				$response['errorMessage'] = toUTF($queryResponse[0]['Error_Msg']);
				$this->dbmodel->rollbackTransaction();
				return $response;
			}
		}
		else {
			$response['status'] = -1;
			$response['errorCode'] = 100592;
			$response['errorMessage'] = toUTF('Ошибка при выполнении запроса к базе данных (добавление/обновление единого номера)');
			$this->dbmodel->rollbackTransaction();
			return $response;
		}

		// Добавляем/обновляем пол
		$queryResponse = $this->dbmodel->addPersonSex($data);

		if ( is_array($queryResponse) && count($queryResponse) > 0 ) {
			if ( strlen($queryResponse[0]['Error_Msg']) > 0 ) {
				$response['status'] = -1;
				$response['errorCode'] = 100601;
				$response['errorMessage'] = toUTF($queryResponse[0]['Error_Msg']);
				$this->dbmodel->rollbackTransaction();
				return $response;
			}
		}
		else {
			$response['status'] = -1;
			$response['errorCode'] = 100602;
			$response['errorMessage'] = toUTF('Ошибка при выполнении запроса к базе данных (добавление/обновление пола)');
			$this->dbmodel->rollbackTransaction();
			return $response;
		}

		// Добавляем/обновляем адрес регистрации
		$queryResponse = $this->dbmodel->addPersonUAddress($data);

		if ( is_array($queryResponse) && count($queryResponse) > 0 ) {
			if ( strlen($queryResponse[0]['Error_Msg']) > 0 ) {
				$response['status'] = -1;
				$response['errorCode'] = 100611;
				$response['errorMessage'] = toUTF($queryResponse[0]['Error_Msg']);
				$this->dbmodel->rollbackTransaction();
				return $response;
			}
		}
		else {
			$response['status'] = -1;
			$response['errorCode'] = 100612;
			$response['errorMessage'] = toUTF('Ошибка при выполнении запроса к базе данных (добавление/обновление адреса регистрации)');
			$this->dbmodel->rollbackTransaction();
			return $response;
		}

		// Добавляем/обновляем полис
		$queryResponse = $this->dbmodel->addPersonPolis($data);

		if ( is_array($queryResponse) && count($queryResponse) > 0 ) {
			if ( strlen($queryResponse[0]['Error_Msg']) > 0 ) {
				$response['status'] = -1;
				$response['errorCode'] = 100621;
				$response['errorMessage'] = toUTF($queryResponse[0]['Error_Msg']);
				$this->dbmodel->rollbackTransaction();
				return $response;
			}
		}
		else {
			$response['status'] = -1;
			$response['errorCode'] = 100622;
			$response['errorMessage'] = toUTF('Ошибка при выполнении запроса к базе данных (добавление/обновление полиса)');
			$this->dbmodel->rollbackTransaction();
			return $response;
		}
		// Прикрепляем человека к ЛПУ
		$queryResponse = $this->dbmodel->attachPersonToLpu($data);

		if ( is_array($queryResponse) && count($queryResponse) > 0 ) {
			if ( strlen($queryResponse[0]['Error_Msg']) > 0 ) {
				$response['status'] = -1;
				$response['errorCode'] = 100631;
				$response['errorMessage'] = toUTF($queryResponse[0]['Error_Msg']);
				$this->dbmodel->rollbackTransaction();
				return $response;
			}
		}
		else {
			$response['status'] = -1;
			$response['errorCode'] = 100632;
			$response['errorMessage'] = toUTF('Ошибка при выполнении запроса к базе данных (прикрепление человека к ЛПУ)');
			$this->dbmodel->rollbackTransaction();
			return $response;
		}
		$this->dbmodel->commitTransaction();

		return $response;
	}
	*/

	/**
	*	Обновление статуса записи по картотеке
	*/
	public function putPersonCardState() {
		$data = $this->ProcessInputData('putPersonCardState', false);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->putPersonCardState($data);
		$this->ProcessModelSave($response, true, 'Ошибка при обновлении статуса записи')->ReturnData();

		return true;
	}

	/**
	 * То же, что и предыдущий метод?
	 */
	public function setPersonCardQueueStatus() {
		$data = $this->ProcessInputData('setPersonCardQueueStatus', false);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->setPersonCardQueueStatus($data);
		$this->ProcessModelSave($response, true, 'Ошибка при обновлении статуса записи')->ReturnData();

		return true;
	}
}

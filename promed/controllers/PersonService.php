<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* PersonService - контроллер для обработки данных человека из БДЗ
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      29.07.2010
*/


class PersonService extends swController {
	
	var $NeedCheckLogin = false;
	
	/**
	 * Description
	 */
	public function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->model("PersonService_model", "dbmodel");
		$this->load->helper('Date');

		$server = new SoapServer('http://172.22.99.4/promed.wsdl');
		// $server = new SoapServer('http://192.168.36.61/promed.wsdl');
		// $server = new SoapServer('http://172.19.61.18:90/promed.wsdl');
		$server->setObject($this);
		$server->handle();
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
	public function putPerson($personData) {
		$isNewPerson = false;
		$personDataArray = array();
		$response = array(
			'bdzID' => 0,
			'status' => -1,
			'errorCode' => 0,
			'errorMessage' => ''
		);

		// $f = fopen('__test.txt', 'w');
		// fwrite($f, gettype($personData));
		// fwrite($f, serialize($personData));
		// fclose($f);

		// Обработка входящих параметров
		if ( !is_object($personData) ) {
			$response['errorCode'] = 1;
			$response['errorMessage'] = 'Input data is not an object';

			return $response;
		}

		if ( !isset($personData->bdzID) || !is_numeric($personData->bdzID) || $personData->bdzID <= 0 ) {
			$response['errorCode'] = 11;
			$response['errorMessage'] = toUTF('Неверный идентификатор застрахованного');

			return $response;
		}

		$response['bdzID'] = $personData->bdzID;

		if ( !isset($personData->surName) || strlen(trim($personData->surName)) == 0 ) {
			$response['errorCode'] = 12;
			$response['errorMessage'] = toUTF('Не задана фамилия застрахованного');

			return $response;
		}

		if ( !isset($personData->firName) || strlen(trim($personData->firName)) == 0 ) {
			$response['errorCode'] = 13;
			$response['errorMessage'] = toUTF('Не задано имя застрахованного');

			return $response;
		}

		if ( !isset($personData->secName) || strlen(trim($personData->secName)) == 0 || trim($personData->secName) == '- - -' ) {
			$personDataArray['secName'] = '';
		}
		else {
			$personDataArray['secName'] = $personData->secName;
		}

		if ( !isset($personData->birthDay) ) {
			$response['errorCode'] = 15;
			$response['errorMessage'] = toUTF('Не задана дата рождения застрахованного');

			return $response;
		}
		else if ( CheckDateFormat($personData->birthDay) > 0 ) {
			$response['errorCode'] = 16;
			$response['errorMessage'] = toUTF('Неверный формат даты рождения застрахованного');

			return $response;
		}

		if ( !isset($personData->edNum) ) {
			$response['errorCode'] = 17;
			$response['errorMessage'] = toUTF('Не задан единый номер застрахованного');

			return $response;
		}
		else if ( !preg_match("/^\d{16}$/", $personData->edNum) ) {
			$response['errorCode'] = 18;
			$response['errorMessage'] = toUTF('Неверный формат единого номера застрахованного');

			return $response;
		}

		if ( !isset($personData->uaddressKladr) ) {
			$response['errorCode'] = 18;
			$response['errorMessage'] = toUTF('Не задан код КЛАДР адреса регистрации застрахованного');

			return $response;
		}
		else if ( !preg_match("/^\d+$/", $personData->uaddressKladr) && !in_array(strlen($personData->uaddressKladr), array(13, 17)) ) {
			$response['errorCode'] = 19;
			$response['errorMessage'] = toUTF('Неверный формат кода КЛАДР адреса регистрации застрахованного');

			return $response;
		}

		if ( !isset($personData->uaddressHome) || strlen(trim($personData->uaddressHome)) == 0 ) {
			$personDataArray['uaddressHome'] = '';
		}
		else {
			$personDataArray['uaddressHome'] = trim($personData->uaddressHome);
		}

		if ( !isset($personData->uaddressFlat) || strlen(trim($personData->uaddressFlat)) == 0 ) {
			$personDataArray['uaddressFlat'] = '';
		}
		else {
			$personDataArray['uaddressFlat'] = trim($personData->uaddressFlat);
		}

		if ( !isset($personData->regNomC) || !is_numeric($personData->regNomC) || $personData->regNomC <= 0 ) {
			$response['errorCode'] = 20;
			$response['errorMessage'] = toUTF('Неверный формат кода СМО regNomC');

			return $response;
		}

		if ( !isset($personData->regNomN) || !is_numeric($personData->regNomN) || $personData->regNomN <= 0 ) {
			$response['errorCode'] = 21;
			$response['errorMessage'] = toUTF('Неверный формат кода СМО regNomN');

			return $response;
		}

		if ( !isset($personData->polisSer) || strlen(trim($personData->polisSer)) == 0 ) {
			$response['errorCode'] = 22;
			$response['errorMessage'] = toUTF('Не задана серия полиса');

			return $response;
		}

		if ( !isset($personData->polisNum) || !is_numeric($personData->polisNum) || $personData->polisNum <= 0 ) {
			$response['errorCode'] = 23;
			$response['errorMessage'] = toUTF('Не задан номер полиса');

			return $response;
		}

		if ( !isset($personData->polisBegDate) ) {
			$response['errorCode'] = 24;
			$response['errorMessage'] = toUTF('Не задана дата выдачи полиса');

			return $response;
		}
		else if ( CheckDateFormat($personData->polisBegDate) > 0 ) {
			$response['errorCode'] = 24;
			$response['errorMessage'] = toUTF('Неверный формат даты выдачи полиса');

			return $response;
		}

		if ( !isset($personData->sex) || !is_numeric($personData->sex) || $personData->sex <= 0 ) {
			$response['errorCode'] = 25;
			$response['errorMessage'] = toUTF('Не задан пол застрахованного');

			return $response;
		}

		if ( !isset($personData->socStatus) || !is_numeric($personData->socStatus) || $personData->socStatus <= 0 ) {
			$personDataArray['socStatus'] = 0;
		}
		else {
			$personDataArray['socStatus'] = $personData->socStatus;
		}

		if ( !isset($personData->snils) || strlen(trim($personData->snils)) != 11 ) {
			$personDataArray['snils'] = '';
		}
		else {
			$personDataArray['snils'] = $personData->snils;
		}

		if ( !isset($personData->paddressKladr) ) {
			$personDataArray['paddressKladr'] = '';
		}
		else if ( !preg_match("/^\d+$/", $personData->paddressKladr) && !in_array(strlen($personData->paddressKladr), array(13, 17)) ) {
			$personDataArray['paddressKladr'] = '';
		}
		else {
			$personDataArray['paddressKladr'] = $personData->paddressKladr;
		}

		if ( !isset($personData->paddressHome) || strlen(trim($personData->paddressHome)) == 0 ) {
			$personDataArray['paddressHome'] = '';
		}
		else {
			$personDataArray['paddressHome'] = $personData->paddressHome;
		}

		if ( !isset($personData->paddressFlat) || strlen(trim($personData->paddressFlat)) == 0 ) {
			$personDataArray['paddressFlat'] = '';
		}
		else {
			$personDataArray['paddressFlat'] = trim($personData->paddressFlat);
		}

		if ( !isset($personData->sprTerr) || !is_numeric($personData->sprTerr) || $personData->sprTerr <= 0 ) {
			$personDataArray['sprTerr'] = 0;
		}
		else {
			$personDataArray['sprTerr'] = $personData->sprTerr;
		}

		$personDataArray['AttachLpu_id'] = 0;
		$personDataArray['Server_id'] = 0;
		$personDataArray['bdzID'] = $personData->bdzID;
		$personDataArray['surName'] = $personData->surName;
		$personDataArray['firName'] = $personData->firName;
		$personDataArray['birthDay'] = ConvertDateFormat($personData->birthDay);
		$personDataArray['edNum'] = $personData->edNum;
		$personDataArray['uaddressKladr'] = $personData->uaddressKladr;
		$personDataArray['regNomC'] = $personData->regNomC;
		$personDataArray['regNomN'] = $personData->regNomN;
		$personDataArray['polisSer'] = $personData->polisSer;
		$personDataArray['polisNum'] = $personData->polisNum;
		$personDataArray['polisBegDate'] = ConvertDateFormat($personData->polisBegDate);
		$personDataArray['sex'] = $personData->sex;

		array_walk($personDataArray, 'convertFromUTF8ToWin1251');

		$queryResponse = $this->dbmodel->checkPersonExists($personData->bdzID);

		if ( strlen($queryResponse['Error_Msg']) > 0 ) {
			$response['errorCode'] = 51;
			$response['errorMessage'] = toUTF($queryResponse['Error_Msg']);

			return $response;
		}

		$personDataArray['Person_id'] = $queryResponse['Person_id'];

		$this->dbmodel->beginTransaction();

		if ( $personDataArray['Person_id'] == 0 ) {
			$isNewPerson = true;

			// Добавляем человека в базу
			$queryResponse = $this->dbmodel->addPerson($personDataArray);

			if ( is_array($queryResponse) && count($queryResponse) > 0 ) {
				if ( strlen($queryResponse[0]['Error_Msg']) == 0 ) {
					if ( $queryResponse[0]['Person_id'] > 0 ) {
						$personDataArray['Person_id'] = $queryResponse[0]['Person_id'];
					}
					else {
						$response['errorCode'] = 52;
						$response['errorMessage'] = toUTF($queryResponse['Error_Msg']);

						$this->dbmodel->rollbackTransaction();

						return $response;
					}
				}
				else {
					$response['errorCode'] = 53;
					$response['errorMessage'] = toUTF('Ошибка при добавлении человека в базу данных');

					$this->dbmodel->rollbackTransaction();

					return $response;
				}
			}
			else {
				$response['errorCode'] = 54;
				$response['errorMessage'] = toUTF('Ошибка при выполнении запроса к базе данных (добавление человека)');

				$this->dbmodel->rollbackTransaction();

				return $response;
			}
		}

		// Добавляем/обновляем фамилию
		$queryResponse = $this->dbmodel->addPersonSurname($personDataArray);

		if ( is_array($queryResponse) && count($queryResponse) > 0 ) {
			if ( strlen($queryResponse[0]['Error_Msg']) > 0 ) {
				$response['errorCode'] = 55;
				$response['errorMessage'] = toUTF($queryResponse['Error_Msg']);

				$this->dbmodel->rollbackTransaction();

				return $response;
			}
		}
		else {
			$response['errorCode'] = 56;
			$response['errorMessage'] = toUTF('Ошибка при выполнении запроса к базе данных (добавление/обновление фамилии)');

			$this->dbmodel->rollbackTransaction();

			return $response;
		}

		// Добавляем/обновляем имя
		$queryResponse = $this->dbmodel->addPersonFirname($personDataArray);

		if ( is_array($queryResponse) && count($queryResponse) > 0 ) {
			if ( strlen($queryResponse[0]['Error_Msg']) > 0 ) {
				$response['errorCode'] = 57;
				$response['errorMessage'] = toUTF($queryResponse['Error_Msg']);

				$this->dbmodel->rollbackTransaction();

				return $response;
			}
		}
		else {
			$response['errorCode'] = 58;
			$response['errorMessage'] = toUTF('Ошибка при выполнении запроса к базе данных (добавление/обновление имени)');

			$this->dbmodel->rollbackTransaction();

			return $response;
		}

		// Добавляем/обновляем отчество
		$queryResponse = $this->dbmodel->addPersonSecname($personDataArray);

		if ( is_array($queryResponse) && count($queryResponse) > 0 ) {
			if ( strlen($queryResponse[0]['Error_Msg']) > 0 ) {
				$response['errorCode'] = 59;
				$response['errorMessage'] = toUTF($queryResponse['Error_Msg']);

				$this->dbmodel->rollbackTransaction();

				return $response;
			}
		}
		else {
			$response['errorCode'] = 60;
			$response['errorMessage'] = toUTF('Ошибка при выполнении запроса к базе данных (добавление/обновление отчества)');

			$this->dbmodel->rollbackTransaction();

			return $response;
		}

		// Добавляем/обновляем дату рождения
		$queryResponse = $this->dbmodel->addPersonBirthday($personDataArray);

		if ( is_array($queryResponse) && count($queryResponse) > 0 ) {
			if ( strlen($queryResponse[0]['Error_Msg']) > 0 ) {
				$response['errorCode'] = 61;
				$response['errorMessage'] = toUTF($queryResponse['Error_Msg']);

				$this->dbmodel->rollbackTransaction();

				return $response;
			}
		}
		else {
			$response['errorCode'] = 62;
			$response['errorMessage'] = toUTF('Ошибка при выполнении запроса к базе данных (добавление/обновление даты рождения)');

			$this->dbmodel->rollbackTransaction();

			return $response;
		}

		// Добавляем/обновляем единый номер
		$queryResponse = $this->dbmodel->addPersonPolisEdNum($personDataArray);

		if ( is_array($queryResponse) && count($queryResponse) > 0 ) {
			if ( strlen($queryResponse[0]['Error_Msg']) > 0 ) {
				$response['errorCode'] = 63;
				$response['errorMessage'] = toUTF($queryResponse['Error_Msg']);

				$this->dbmodel->rollbackTransaction();

				return $response;
			}
		}
		else {
			$response['errorCode'] = 64;
			$response['errorMessage'] = toUTF('Ошибка при выполнении запроса к базе данных (добавление/обновление единого номера)');

			$this->dbmodel->rollbackTransaction();

			return $response;
		}

		// Добавляем/обновляем пол
		$queryResponse = $this->dbmodel->addPersonSex($personDataArray);

		if ( is_array($queryResponse) && count($queryResponse) > 0 ) {
			if ( strlen($queryResponse[0]['Error_Msg']) > 0 ) {
				$response['errorCode'] = 65;
				$response['errorMessage'] = toUTF($queryResponse['Error_Msg']);

				$this->dbmodel->rollbackTransaction();

				return $response;
			}
		}
		else {
			$response['errorCode'] = 66;
			$response['errorMessage'] = toUTF('Ошибка при выполнении запроса к базе данных (добавление/обновление пола)');

			$this->dbmodel->rollbackTransaction();

			return $response;
		}

		// Добавляем/обновляем адрес регистрации
		$queryResponse = $this->dbmodel->addPersonUAddress($personDataArray);

		if ( is_array($queryResponse) && count($queryResponse) > 0 ) {
			if ( strlen($queryResponse[0]['Error_Msg']) > 0 ) {
				$response['errorCode'] = 67;
				$response['errorMessage'] = toUTF($queryResponse['Error_Msg']);

				$this->dbmodel->rollbackTransaction();

				return $response;
			}
		}
		else {
			$response['errorCode'] = 68;
			$response['errorMessage'] = toUTF('Ошибка при выполнении запроса к базе данных (добавление/обновление адреса регистрации)');

			$this->dbmodel->rollbackTransaction();

			return $response;
		}

		// Добавляем/обновляем полис
		$queryResponse = $this->dbmodel->addPersonPolis($personDataArray);

		if ( is_array($queryResponse) && count($queryResponse) > 0 ) {
			if ( strlen($queryResponse[0]['Error_Msg']) > 0 ) {
				$response['errorCode'] = 69;
				$response['errorMessage'] = toUTF($queryResponse['Error_Msg']);

				$this->dbmodel->rollbackTransaction();

				return $response;
			}
		}
		else {
			$response['errorCode'] = 70;
			$response['errorMessage'] = toUTF('Ошибка при выполнении запроса к базе данных (добавление/обновление полиса)');

			$this->dbmodel->rollbackTransaction();

			return $response;
		}

		// Если человек добавлен в базу впервые, то прикрепляем его к ЛПУ
		// При ошибках коммит транзакции, ибо прикрепление вторично. Главное - добавить/обновить данные по человеку в БД

		if ( $isNewPerson === true ) {
			// Определяем ЛПУ, к которой надо прикрепить человека
			$queryResponse = $this->dbmodel->getAttachLpu($personDataArray['Person_id']);

			if ( is_array($queryResponse) && count($queryResponse) > 0 ) {
				if ( strlen($queryResponse[0]['Error_Msg']) > 0 ) {
					$response['errorCode'] = 71;
					$response['errorMessage'] = toUTF($queryResponse['Error_Msg']);

					$this->dbmodel->rollbackTransaction();

					return $response;
				}
				else {
					$personDataArray['AttachLpu_id'] = $queryResponse[0]['Lpu_id'];
				}
			}
			else {
				$response['errorCode'] = 72;
				$response['errorMessage'] = toUTF('Ошибка при выполнении запроса к базе данных (определение ЛПУ прикрепления)');

				$this->dbmodel->rollbackTransaction();

				return $response;
			}

			if ( is_numeric($personDataArray['AttachLpu_id']) && $personDataArray['AttachLpu_id'] > 0 ) {
				// Прикрепляем человека к ЛПУ
				$queryResponse = $this->dbmodel->attachPersonToLpu($personDataArray);

				if ( is_array($queryResponse) && count($queryResponse) > 0 ) {
					if ( strlen($queryResponse[0]['Error_Msg']) > 0 ) {
						$response['errorCode'] = 73;
						$response['errorMessage'] = toUTF($queryResponse['Error_Msg']);

						$this->dbmodel->rollbackTransaction();

						return $response;
					}
				}
				else {
					$response['errorCode'] = 74;
					$response['errorMessage'] = toUTF('Ошибка при выполнении запроса к базе данных (прикрепление человека к ЛПУ)');

					$this->dbmodel->rollbackTransaction();

					return $response;
				}
			}
		}

		$response['status'] = 0;

		$this->dbmodel->commitTransaction();

		// return new SoapVar($response, SOAP_ENC_ARRAY, 'putPersonResponse', 'http://swan.perm.ru/webservices/types/');
		return $response;
	}


	/**
	 * Description
	 */
	public function putPersonCardState($personCardStateData) {
		$personCardStateDataArray = array();
		$response = array(
			'status' => -1,
			'errorCode' => 0,
			'errorMessage' => ''
		);

		// Обработка входящих параметров
		if ( !is_object($personCardStateData) ) {
			$response['errorCode'] = 1;
			$response['errorMessage'] = 'Input data is not an object';
		}
		else if ( !isset($personCardStateData->transactCode) || !is_numeric($personCardStateData->transactCode) || $personCardStateData->transactCode <= 0 ) {
			$response['errorCode'] = 11;
			$response['errorMessage'] = toUTF('Неверный код транзакции');
		}
		else if ( !isset($personCardStateData->status) ) {
			$response['errorCode'] = 12;
			$response['errorMessage'] = toUTF('Не задан параметр status');
		}
		else if ( !is_numeric($personCardStateData->status) || !in_array($personCardStateData->status, array(-1, 0, 1)) ) {
			$response['errorCode'] = 13;
			$response['errorMessage'] = toUTF('Неверное значение параметра status');
		}
		else {
			$personCardStateDataArray['transactCode'] = $personCardStateData->transactCode;
			$personCardStateDataArray['status'] = $personCardStateData->status;

			array_walk($personCardStateDataArray, 'convertFromUTF8ToWin1251');

			$queryResponse = $this->dbmodel->putPersonCardState($personCardStateDataArray);

			if ( is_array($queryResponse) && count($queryResponse) > 0 ) {
				if ( strlen($queryResponse[0]['Error_Msg']) > 0 ) {
					$response['errorCode'] = 14;
					$response['errorMessage'] = toUTF($queryResponse[0]['Error_Msg']);
				}
				else {
					$response['status'] = 0;
				}
			}
			else {
				$response['errorCode'] = 15;
				$response['errorMessage'] = toUTF('Ошибка при выполнении запроса к базе данных (установка статуса записи по картотеке)');
			}
		}

		return $response;
	}
}

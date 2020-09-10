<?php	defined('BASEPATH') or die ('No direct script access allowed');
class BDZServiceClient extends swController {
	
	var $NeedCheckLogin = false;
	protected $soapClient = null;
	protected $log_file_name = 'bdz.soap.client.log';
	protected $pkmiac_login = 'PKF_PKMIAC';
	protected $pkmiac_password = '8f20962cf18c731b8b4c422798945229f81beb15';

	/**
	 * Method description
	 */
	public function __construct() {
		parent::__construct();

		@ini_set('max_execution_time', 1200);

		$this->load->database();
		$this->load->model('BDZData_model', 'dbmodel');
		$this->load->helper('Date');
		$this->load->helper('Sql');

		try {
			$this->soapClient = new SoapClient('https://bdz.tfoms.perm.ru/fd/Service.asmx', array("connection_timeout" => 15, "classmap" => array()));
		}
		catch ( SoapFault $e ) {
			echo $e->getMessage();
		}
	}


	/**
	 * Method description
	 */
	public function Index() {
		return true;
	}


	/**
	 * Method description
	 */
	public function writeRecordToLog($str) {
		$result = false;

		if ( strlen($this->log_file_name) > 0 ) {
			$f_log = fopen(PROMED_LOGS . $this->log_file_name, 'a');
			fputs($f_log, $str);
			fputs($f_log, "\r\n");
			fclose($f_log);
			$result = true;
		}

		return $result;
	}


	/**
	 * Method description
	 */
	public function getPolisIdList() {
		$this->writeRecordToLog('[' . date('Y-m-d H:i:s') . '] Получение списка идентификаторов обновленных полисов...');

		// Получаем дату и время последнего обновления
		$getStartDT = $this->dbmodel->getStartDT();

		if ( is_array($getStartDT) && count($getStartDT) > 0 && array_key_exists('endDT', $getStartDT[0]) && !empty($getStartDT[0]['endDT']) ) {
			$startDT = $getStartDT[0]['endDT'];
		}
		else {
			$startDT = date('d.m.Y') . ' 00:00:00';
		}

		// Верхння граница периода
		$finalDT = date('d.m.Y H:i:s');

		try {
			$soapResponse = $this->soapClient->Polis_News(array('StartTime' => $startDT, 'FinalTime' => $finalDT, 'Login' => $this->pkmiac_login, 'Password' => $this->pkmiac_password));
		}
		catch ( SoapFault $e ) {
			// echo $e->getMessage();
			$this->writeRecordToLog('[' . date('Y-m-d H:i:s') . '] ' . toAnsi($e->getMessage()));
			return false;
		}

		$this->writeRecordToLog('[' . date('Y-m-d H:i:s') . '] Получение списка идентификаторов обновленных полисов завершено');

		// Проверить ошибки
		if ( $soapResponse->Polis_NewsResult->errorCode != 0 ) {
			$this->writeRecordToLog('[' . date('Y-m-d H:i:s') . '] Error (' . $soapResponse->Polis_NewsResult->errorCode . '): ' . $soapResponse->Polis_NewsResult->errorMessage);
			return false;
		}

		$data = array();
		$orgSmoIdList = array();
		$soapResponseData = (array)$soapResponse->Polis_NewsResult->Polises->PolisInfo;

		$this->writeRecordToLog('[' . date('Y-m-d H:i:s') . '] Загрузка списка идентификаторов обновленных полисов в БД...');

		// Обработка записей
		foreach ( $soapResponseData as $soapResponseRecord ) {
			$record = (array)$soapResponseRecord;
			$record['Error_Code'] = 0;
			$record['Error_Msg'] = '';

			if ( !isset($record['Polis_ID']) || !is_numeric($record['Polis_ID']) || $record['Polis_ID'] <= 0 ) {
				$this->writeRecordToLog('[' . date('Y-m-d H:i:s') . '] Error (500): Неверный идентификатор полиса (Polis_ID)');
				continue;
			}

			if ( !isset($record['regNomC']) || !is_numeric($record['regNomC']) || $record['regNomC'] <= 0 ) {
				$record['Error_Code'] = 500;
				$record['Error_Msg'] = 'Неверный код СМО (regNomC)';
			}
			else if ( !isset($record['regNomN']) || !is_numeric($record['regNomN']) || $record['regNomN'] <= 0 ) {
				$record['Error_Code'] = 500;
				$record['Error_Msg'] = 'Неверный код СМО (regNomN)';
			}
			else if ( !isset($record['polisSetDT']) || strlen($record['polisSetDT']) == 0 ) {
				$record['Error_Code'] = 500;
				$record['Error_Msg'] = 'Не задана дата/временя добавления/изменения полиса в СБДЗ (polisSetDT)';
			}
			else if ( isset($record['polisSetDT']) && !preg_match('/^\d{2}\.\d{2}\.\d{4} \d{1,2}:\d{2}:\d{2}$/', $record['polisSetDT']) ) {
				$record['Error_Code'] = 500;
				$record['Error_Msg'] = 'Неверный формат даты/времени добавления/изменения полиса в СБДЗ (polisSetDT)';
				$record['polisSetDT'] = NULL;
			}

			// Получаем идентификатор СМО из списка найденных идентификаторов
			foreach ( $orgSmoIdList as $orgSmoId => $orgSmoIdListRecord ) {
				if ( $orgSmoIdListRecord['regNomC'] == $record['regNomC'] && $orgSmoIdListRecord['regNomN'] == $record['regNomN'] ) {
					$record['OrgSmo_id'] = $orgSmoId;
					break;
				}
			}

			// Получаем идентификатор СМО из БД
			if ( !isset($record['OrgSmo_id']) ) {
				$getOrgSmoId = $this->dbmodel->getOrgSmoId($record['regNomC'], $record['regNomN']);

				if ( strlen($getOrgSmoId['Error_Msg']) > 0 ) {
					$record['Error_Code'] = 500;
					$record['Error_Msg'] = $getOrgSmoId['Error_Msg'];
				}
				else {
					$orgSmoIdList[$getOrgSmoId['OrgSmo_id']] = array('regNomC' => $record['regNomC'], 'regNomN' => $record['regNomN']);
					$record['OrgSmo_id'] = $getOrgSmoId['OrgSmo_id'];
				}
			}

			$data[] = $record;
		}

		$saveResponse = $this->dbmodel->savePolisQueue($data, $startDT, $finalDT);

		if ( $saveResponse['success'] === false ) {
			$this->writeRecordToLog('[' . date('Y-m-d H:i:s') . '] Error: ' . $saveResponse['Error_Msg']);
			return false;
		}

		$this->writeRecordToLog('[' . date('Y-m-d H:i:s') . '] Загрузка списка идентификаторов обновленных полисов в БД завершена');

		// $this->updateInsuredsData();

		return true;
	}


	/**
	 * Method description
	 */
	public function updateInsuredsData() {
		// @header('Content-type: text/html; charset=utf-8');

		$this->writeRecordToLog('[' . date('Y-m-d H:i:s') . '] Получение данных из БДЗ...');

		// Получаем список логинов и паролей СМО для обращения к сервису СБДЗ
		$orgSmoAccountDataResponse = $this->dbmodel->getOrgSmoAccountData();

		if ( is_array($orgSmoAccountDataResponse) ) {
			foreach ( $orgSmoAccountDataResponse as $orgSmoRecord ) {
				// Получаем список идентификаторов полисов по каждой СМО в отдельности
				$idList = array();
				$polisQueueResponse = $this->dbmodel->getPolisQueueList($orgSmoRecord['OrgSmo_id']);

				if ( is_array($polisQueueResponse) ) {
					foreach ( $polisQueueResponse as $record ) {
						$errorCode = 0;
						$errorMsg = '';

						// Получение данных о застрахованном с сервера СБДЗ
						try {
							$soapResponse = $this->soapClient->INS_GETDATA(array('polisID' => $record['Polis_id'], 'Login' => $orgSmoRecord['Login'], 'Password' => $orgSmoRecord['Password']));
						}
						catch ( SoapFault $e ) {
							$this->writeRecordToLog('[' . date('Y-m-d H:i:s') . '] ' . toAnsi($e->getMessage()) . " (Polis_ID = " . $record['Polis_id'] . ")");
							$this->dbmodel->setPolisQueueStatus(array(
								'PolisQueue_id' => $record['PolisQueue_id'],
								'PolisQueue_ErrorMessage' => $e->getMessage()
							));
							continue;
						}

						if ( is_object($soapResponse) && is_object($soapResponse->INS_GETDATAResult) ) {
							if ( $soapResponse->INS_GETDATAResult->errorCode > 0 ) {
								$this->writeRecordToLog('[' . date('Y-m-d H:i:s') . "] Error (" . $soapResponse->INS_GETDATAResult->errorCode . "): " . toAnsi($soapResponse->INS_GETDATAResult->errorMessage) . " (Polis_ID = " . $record['Polis_id'] . ")");
								$this->dbmodel->setPolisQueueStatus(array(
									'PolisQueue_id' => $record['PolisQueue_id'],
									'PolisQueue_ErrorCode' => $soapResponse->INS_GETDATAResult->errorCode,
									'PolisQueue_ErrorMessage' => toAnsi($soapResponse->INS_GETDATAResult->errorMessage)
								));
								continue;
							}
						}
						else {
							$this->writeRecordToLog('[' . date('Y-m-d H:i:s') . "] Error (1): Неверный ответ сервера (Polis_ID = " . $record['Polis_id'] . ")");
							$this->dbmodel->setPolisQueueStatus(array(
								'PolisQueue_id' => $record['PolisQueue_id'],
								'PolisQueue_ErrorCode' => 1,
								'PolisQueue_ErrorMessage' => 'Неверный ответ сервера'
							));
							continue;
						}

						// Обработка $soapResponse и формирование $data для saveBDZDataRecord
						$data = array(
							'BDZ_id' => NULL,
							'Person_SurName' => NULL,
							'Person_FirName' => NULL,
							'Person_SecName' => NULL,
							'Person_BirthDay' => NULL,
							'Sex_Code' => NULL,
							'SocStatus_Code' => NULL,
							'Person_Snils' => NULL,
							'Person_EdNum' => NULL,
							'DocumentType_Code' => NULL,
							'Document_Ser' => NULL,
							'Document_Num' => NULL,
							'Document_begDate' => NULL,
							'UKLAdr_Code' => NULL,
							'UAddress_House' => NULL,
							'UAddress_Flat' => NULL,
							'PKLAdr_Code' => NULL,
							'PAddress_House' => NULL,
							'PAddress_Flat' => NULL,
							'OrgSMO_RegNomC' => NULL,
							'OrgSMO_RegNomN' => NULL,
							'OmsSprTerr_Code' => NULL,
							'Polis_Ser' => NULL,
							'Polis_Num' => NULL,
							'Polis_begDate' => NULL,
							'Polis_endDate' => NULL,
							'Polis_planDate' => NULL,
							'Polis_changeDate' => NULL,
							'PolisCloseCause_Code' => NULL
						);
						$soapResponseData = (array)$soapResponse->INS_GETDATAResult->IResult;

						if ( isset($soapResponseData['bdzID']) && is_numeric($soapResponseData['bdzID']) && $soapResponseData['bdzID'] > 0 ) {
							$data['BDZ_id'] = $soapResponseData['bdzID'];

							if ( !in_array($soapResponseData['bdzID'], $idList) ) {
								$idList[] = $soapResponseData['bdzID'];
							}
						}
						else {
							$this->writeRecordToLog('[' . date('Y-m-d H:i:s') . "] Error (2): Неверный идентификатор застрахованного (bdzID)");
							$this->dbmodel->setPolisQueueStatus(array(
								'PolisQueue_id' => $record['PolisQueue_id'],
								'PolisQueue_ErrorCode' => 2,
								'PolisQueue_ErrorMessage' => 'Неверный идентификатор застрахованного (bdzID)'
							));
							continue;
						}

						if ( isset($soapResponseData['SurName']) && strlen($soapResponseData['SurName']) > 0 ) {
							$data['Person_SurName'] = toAnsi($soapResponseData['SurName']);
						}

						if ( isset($soapResponseData['FirName']) && strlen($soapResponseData['FirName']) > 0 ) {
							$data['Person_FirName'] = toAnsi($soapResponseData['FirName']);
						}

						if ( isset($soapResponseData['SecName']) && strlen($soapResponseData['SecName']) > 0 ) {
							$data['Person_SecName'] = toAnsi($soapResponseData['SecName']);
						}

						if ( isset($soapResponseData['BirthDay']) && strlen($soapResponseData['BirthDay']) >= 10 ) {
							if ( CheckDateFormat(substr($soapResponseData['BirthDay'], 0, 10)) ) {
								$this->writeRecordToLog('[' . date('Y-m-d H:i:s') . "] Error (2): Неверный формат даты рождения (BirthDay)");
								$this->dbmodel->setPolisQueueStatus(array(
									'PolisQueue_id' => $record['PolisQueue_id'],
									'PolisQueue_ErrorCode' => 2,
									'PolisQueue_ErrorMessage' => 'Неверный формат даты рождения (BirthDay)'
								));
								continue;
							}
							else {
								$data['Person_BirthDay'] = ConvertDateFormat(substr($soapResponseData['BirthDay'], 0, 10));
							}
						}

						if ( isset($soapResponseData['sex']) && is_numeric($soapResponseData['sex']) && in_array($soapResponseData['sex'], array(1, 2)) ) {
							$data['Sex_Code'] = $soapResponseData['sex'];
						}
						else {
							$this->writeRecordToLog('[' . date('Y-m-d H:i:s') . "] Error (2): Неверное значение кода пола (sex)");
							$this->dbmodel->setPolisQueueStatus(array(
								'PolisQueue_id' => $record['PolisQueue_id'],
								'PolisQueue_ErrorCode' => 2,
								'PolisQueue_ErrorMessage' => 'Неверное значение кода пола (sex)'
							));
							continue;
						}

						if ( isset($soapResponseData['socStatus']) && is_numeric($soapResponseData['socStatus']) && $soapResponseData['socStatus'] > 0 ) {
							$data['SocStatus_Code'] = $soapResponseData['socStatus'];
						}
						else {
							$this->writeRecordToLog('[' . date('Y-m-d H:i:s') . "] Error (2): Неверное значение кода социального статуса (socStatus)");
							$this->dbmodel->setPolisQueueStatus(array(
								'PolisQueue_id' => $record['PolisQueue_id'],
								'PolisQueue_ErrorCode' => 2,
								'PolisQueue_ErrorMessage' => 'Неверное значение кода социального статуса (socStatus)'
							));
							continue;
						}

						if ( isset($soapResponseData['snils']) && preg_match('/^[\d]{11}$/', $soapResponseData['snils']) ) {
							$data['Person_Snils'] = $soapResponseData['snils'];
						}

						if ( isset($soapResponseData['edNum']) && strlen($soapResponseData['edNum']) > 0 ) {
							$data['Person_EdNum'] = $soapResponseData['edNum'];
						}

						if ( isset($soapResponseData['DocumCode']) && is_numeric($soapResponseData['DocumCode']) && $soapResponseData['DocumCode'] > 0 ) {
							$data['DocumentType_Code'] = $soapResponseData['DocumCode'];
						}

						if ( isset($soapResponseData['DocumSer']) && strlen($soapResponseData['DocumSer']) > 0 ) {
							$data['Document_Ser'] = toAnsi($soapResponseData['DocumSer']);
						}

						if ( isset($soapResponseData['DocumNum']) && strlen($soapResponseData['DocumNum']) > 0 ) {
							$data['Document_Num'] = toAnsi($soapResponseData['DocumNum']);
						}
						if ( isset($soapResponseData['DocumBegDT']) && strlen($soapResponseData['DocumBegDT']) >= 10 ) {
							if ( CheckDateFormat(substr($soapResponseData['DocumBegDT'], 0, 10)) ) {
								$this->writeRecordToLog('[' . date('Y-m-d H:i:s') . "] Error (2): Неверный формат даты выдачи документа (DocumBegDT)");
								$this->dbmodel->setPolisQueueStatus(array(
									'PolisQueue_id' => $record['PolisQueue_id'],
									'PolisQueue_ErrorCode' => 2,
									'PolisQueue_ErrorMessage' => 'Неверный формат даты выдачи документа (DocumBegDT)'
								));
								continue;
							}
							else {
								$data['Document_begDate'] = ConvertDateFormat(substr($soapResponseData['DocumBegDT'], 0, 10)) . substr($soapResponseData['DocumBegDT'], 10, strlen($soapResponseData['DocumBegDT']) - 9) ;
							}
						}

						if ( isset($soapResponseData['uaddressKladr']) && is_numeric($soapResponseData['uaddressKladr']) && in_array(strlen($soapResponseData['uaddressKladr']), array(13, 17)) ) {
							$data['UKLAdr_Code'] = $soapResponseData['uaddressKladr'];
						}

						if ( isset($soapResponseData['uaddressHome']) && strlen($soapResponseData['uaddressHome']) > 0 ) {
							$data['UAddress_House'] = toAnsi($soapResponseData['uaddressHome']);
						}

						if ( isset($soapResponseData['uaddressFlat']) && strlen($soapResponseData['uaddressFlat']) > 0 ) {
							$data['UAddress_Flat'] = toAnsi($soapResponseData['uaddressFlat']);
						}

						if ( isset($soapResponseData['paddressKladr']) && is_numeric($soapResponseData['paddressKladr']) && in_array(strlen($soapResponseData['paddressKladr']), array(13, 17)) ) {
							$data['PKLAdr_Code'] = $soapResponseData['paddressKladr'];
						}

						if ( isset($soapResponseData['paddressHome']) && strlen($soapResponseData['paddressHome']) > 0 ) {
							$data['PAddress_House'] = toAnsi($soapResponseData['paddressHome']);
						}

						if ( isset($soapResponseData['paddressFlat']) && strlen($soapResponseData['paddressFlat']) > 0 ) {
							$data['PAddress_Flat'] = toAnsi($soapResponseData['paddressFlat']);
						}

						if ( isset($soapResponseData['regNomC']) && is_numeric($soapResponseData['regNomC']) && $soapResponseData['regNomC'] > 0 ) {
							$data['OrgSMO_RegNomC'] = $soapResponseData['regNomC'];
						}
						else {
							$this->writeRecordToLog('[' . date('Y-m-d H:i:s') . "] Error (2): Неверное значение кода СМО (regNomC)");
							$this->dbmodel->setPolisQueueStatus(array(
								'PolisQueue_id' => $record['PolisQueue_id'],
								'PolisQueue_ErrorCode' => 2,
								'PolisQueue_ErrorMessage' => 'Неверное значение кода СМО (regNomC)'
							));
							continue;
						}

						if ( isset($soapResponseData['regNomN']) && is_numeric($soapResponseData['regNomN']) && $soapResponseData['regNomN'] > 0 ) {
							$data['OrgSMO_RegNomN'] = $soapResponseData['regNomN'];
						}
						else {
							$this->writeRecordToLog('[' . date('Y-m-d H:i:s') . "] Error (2): Неверное значение кода СМО (regNomN)");
							$this->dbmodel->setPolisQueueStatus(array(
								'PolisQueue_id' => $record['PolisQueue_id'],
								'PolisQueue_ErrorCode' => 2,
								'PolisQueue_ErrorMessage' => 'Неверное значение кода СМО (regNomN)'
							));
							continue;
						}

						if ( isset($soapResponseData['sprTerr']) && is_numeric($soapResponseData['sprTerr']) && $soapResponseData['sprTerr'] > 0 ) {
							$data['OmsSprTerr_Code'] = $soapResponseData['sprTerr'];
						}
						else {
							$this->writeRecordToLog('[' . date('Y-m-d H:i:s') . "] Error (2): Неверное значение кода территории страхования (sprTerr)");
							$this->dbmodel->setPolisQueueStatus(array(
								'PolisQueue_id' => $record['PolisQueue_id'],
								'PolisQueue_ErrorCode' => 2,
								'PolisQueue_ErrorMessage' => 'Неверное значение кода территории страхования (sprTerr)'
							));
							continue;
						}

						if ( isset($soapResponseData['polisSer']) && strlen($soapResponseData['polisSer']) > 0 ) {
							$data['Polis_Ser'] = trim(toAnsi($soapResponseData['polisSer']));
						}

						if ( isset($soapResponseData['polisNum']) && is_numeric($soapResponseData['polisNum']) && $soapResponseData['polisNum'] > 0 ) {
							$data['Polis_Num'] = $soapResponseData['polisNum'];
						}
						else {
							$this->writeRecordToLog('[' . date('Y-m-d H:i:s') . "] Error (2): Неверное значение номера полиса (polisNum)");
							$this->dbmodel->setPolisQueueStatus(array(
								'PolisQueue_id' => $record['PolisQueue_id'],
								'PolisQueue_ErrorCode' => 2,
								'PolisQueue_ErrorMessage' => 'Неверное значение номера полиса (polisNum)'
							));
							continue;
						}

						if ( isset($soapResponseData['polisBegDT']) && strlen($soapResponseData['polisBegDT']) >= 10 ) {
							if ( CheckDateFormat(substr($soapResponseData['polisBegDT'], 0, 10)) ) {
								$this->writeRecordToLog('[' . date('Y-m-d H:i:s') . "] Error (2): Неверный формат даты начала действия полиса (polisBegDT)");
								$this->dbmodel->setPolisQueueStatus(array(
									'PolisQueue_id' => $record['PolisQueue_id'],
									'PolisQueue_ErrorCode' => 2,
									'PolisQueue_ErrorMessage' => 'Неверный формат даты начала действия полиса (polisBegDT)'
								));
								continue;
							}
							else {
								$data['Polis_begDate'] = ConvertDateFormat(substr($soapResponseData['polisBegDT'], 0, 10)) . substr($soapResponseData['polisBegDT'], 10, strlen($soapResponseData['polisBegDT']) - 9) ;
							}
						}

						if ( isset($soapResponseData['polisEndDT']) && strlen($soapResponseData['polisEndDT']) >= 10 ) {
							if ( CheckDateFormat(substr($soapResponseData['polisEndDT'], 0, 10)) ) {
								$this->writeRecordToLog('[' . date('Y-m-d H:i:s') . "] Error (2): Неверный формат даты закрытия полиса (polisEndDT)");
								$this->dbmodel->setPolisQueueStatus(array(
									'PolisQueue_id' => $record['PolisQueue_id'],
									'PolisQueue_ErrorCode' => 2,
									'PolisQueue_ErrorMessage' => 'Неверный формат даты закрытия полиса (polisEndDT)'
								));
								continue;
							}
							else {
								$data['Polis_endDate'] = ConvertDateFormat(substr($soapResponseData['polisEndDT'], 0, 10)) . substr($soapResponseData['polisEndDT'], 10, strlen($soapResponseData['polisEndDT']) - 9) ;
							}
						}
						/*
						if ( isset($soapResponseData['polisPlanDT']) && strlen($soapResponseData['polisPlanDT']) >= 10 ) {
							if ( CheckDateFormat(substr($soapResponseData['polisPlanDT'], 0, 10)) ) {
								$this->writeRecordToLog('[' . date('Y-m-d H:i:s') . "] Error (2): Неверный формат даты окончания действия полиса (polisPlanDT)");
								$this->dbmodel->setPolisQueueStatus(array(
									'PolisQueue_id' => $record['PolisQueue_id'],
									'PolisQueue_ErrorCode' => 2,
									'PolisQueue_ErrorMessage' => 'Неверный формат даты окончания действия полиса (polisPlanDT)'
								));
								continue;
							}
							else {
								$data['Polis_planDate'] = ConvertDateFormat(substr($soapResponseData['polisPlanDT'], 0, 10)) . substr($soapResponseData['polisPlanDT'], 10, strlen($soapResponseData['polisPlanDT']) - 9) ;
							}
						}
						*/
						if ( isset($soapResponseData['polisEditDT']) && strlen($soapResponseData['polisEditDT']) >= 10 ) {
							if ( CheckDateFormat(substr($soapResponseData['polisEditDT'], 0, 10)) ) {
								$this->writeRecordToLog('[' . date('Y-m-d H:i:s') . "] Error (2): Неверный формат даты последнего редактирования данных полиса (polisEditDT)");
								$this->dbmodel->setPolisQueueStatus(array(
									'PolisQueue_id' => $record['PolisQueue_id'],
									'PolisQueue_ErrorCode' => 2,
									'PolisQueue_ErrorMessage' => 'Неверный формат даты последнего редактирования данных полиса (polisEditDT)'
								));
								continue;
							}
							else {
								$data['Polis_changeDate'] = ConvertDateFormat(substr($soapResponseData['polisEditDT'], 0, 10)) . substr($soapResponseData['polisEditDT'], 10, strlen($soapResponseData['polisEditDT']) - 9) ;
							}
						}

						if ( isset($soapResponseData['polisCloseCause']) && is_numeric($soapResponseData['polisCloseCause']) && $soapResponseData['polisCloseCause'] > 0 ) {
							$data['PolisCloseCause_Code'] = $soapResponseData['polisCloseCause'];
						}

						// Вызов добавления записи в таблицу BDZData
						$response = $this->dbmodel->saveBDZDataRecord($data);

						// Обработка ответа
						if ( is_array($response) && count($response) > 0 ) {
							if ( array_key_exists('Error_Msg', $response[0]) ) {
								if ( empty($response[0]['Error_Msg']) ) {
									// Установка записи статуса "загружена"
									$this->dbmodel->setPolisQueueStatus(array(
										'PolisQueue_id' => $record['PolisQueue_id'],
										'PolisQueue_IsLoad' => 2
									));
								}
								else {
									$this->writeRecordToLog('[' . date('Y-m-d H:i:s') . "] Error (" . $response[0]['Error_Code'] . "): " . $response[0]['Error_Msg']);
									$this->dbmodel->setPolisQueueStatus(array(
										'PolisQueue_id' => $record['PolisQueue_id'],
										'PolisQueue_ErrorCode' => $response[0]['Error_Code'],
										'PolisQueue_ErrorMessage' => $response[0]['Error_Msg']
									));
									continue;
								}
							}
							else {
								$this->writeRecordToLog('[' . date('Y-m-d H:i:s') . "] Error (3): Ошибка при добавлении записи в BDZData");
								$this->dbmodel->setPolisQueueStatus(array(
									'PolisQueue_id' => $record['PolisQueue_id'],
									'PolisQueue_ErrorCode' => 3,
									'PolisQueue_ErrorMessage' => "Ошибка при добавлении записи в BDZData"
								));
								continue;
							}
						}
						else {
							$this->writeRecordToLog('[' . date('Y-m-d H:i:s') . "] Error (3): Ошибка при добавлении записи в BDZData");
							$this->dbmodel->setPolisQueueStatus(array(
								'PolisQueue_id' => $record['PolisQueue_id'],
								'PolisQueue_ErrorCode' => 3,
								'PolisQueue_ErrorMessage' => "Ошибка при добавлении записи в BDZData"
							));
							continue;
						}
					}
				}
			}

			$this->writeRecordToLog('[' . date('Y-m-d H:i:s') . "] Загрузка записей завершена");

			// Запуск разбора новых данных в таблице BDZData
			$this->writeRecordToLog('[' . date('Y-m-d H:i:s') . "] Запуск разбора новых данных в таблице BDZData...");
			$response = $this->dbmodel->parseBDZData();
			$this->writeRecordToLog('[' . date('Y-m-d H:i:s') . "] Разбора новых данных в таблице BDZData завершен");

			// Запуск обновления данных
			$this->writeRecordToLog('[' . date('Y-m-d H:i:s') . "] Запуск обновления данных...");

			foreach ( $idList as $BDZ_id ) {
				$response = $this->dbmodel->loadBDZData($BDZ_id);
			}

			$this->writeRecordToLog('[' . date('Y-m-d H:i:s') . "] Обновление данных завершено");
		}

		$this->writeRecordToLog('[' . date('Y-m-d H:i:s') . '] Получение данных с сервера СБДЗ ПКФОМС завершено');

		return true;
	}


	/**
	 * Method description
	 */
	public function updateLpuAttachment() {
		@header('Content-type: text/html; charset=utf-8');

		$this->writeRecordToLog('[' . date('Y-m-d H:i:s') . '] Прикрепление');
		/*
		$person_card_array = array(1, 6, 7, 8, 9, 64, 98, 226, 294, 525, 756, 797, 847, 1045, 1080, 1125, 1173, 1234, 1247, 1248, 1267, 1407, 1803,
			1844, 2053, 2115, 2141, 2170, 2195, 2338, 2379, 2450, 2486, 2525);

		foreach ( $person_card_array as $record ) {
			$person_card_queue_status = 0;

			try {
				$soapResponse = $this->soapClient->FD_UPDATE(
					array(
						'bdzID' => $record,
						'regNomC' => 5,
						'regNomN' => 12177,
						'lpuAttachDT' => '01.11.2010',
						'lpuDetachDT' => '10.11.2010',
						'personCloseCause' => 1,
						'Login' => 'PKF_PKMIAC',
						'Password' => '8f20962cf18c731b8b4c422798945229f81beb15'
					)
				);
			}
			catch ( SoapFault $e ) {
				$this->writeRecordToLog('[' . date('Y-m-d H:i:s') . "] Error: " . toAnsi($e->getMessage()));
				continue;
			}

			// Обработка $soapResponse
			$soapResult = (array)$soapResponse->FD_UPDATEResult;

			var_dump($soapResult);

			$this->writeRecordToLog('[' . date('Y-m-d H:i:s') . "] Success: " . $soapResult['status']);
		}
		*/
		$this->writeRecordToLog('[' . date('Y-m-d H:i:s') . '] Получение списка прикрепленных');

		// Получаем данные о прикреплении
		$response = $this->dbmodel->getLpuAttachmentData();

		if ( is_array($response) ) {
			$this->writeRecordToLog('[' . date('Y-m-d H:i:s') . '] Количество записей: ' . count($response));

			foreach ( $response as $record ) {
				$person_card_queue_status = 0;

				try {
					$soapResponse = $this->soapClient->FD_UPDATE(
						array(
							'bdzID' => $record['BDZ_id'],
							'regNomC' => $record['Lpu_RegNomC'],
							'regNomN' => $record['Lpu_RegNomN'],
							'lpuAttachDT' => $record['PersonCard_begDate'],
							'lpuDetachDT' => $record['PersonCard_endDate'],
							'personCloseCause' => $record['CardCloseCause_Code'],
							'Login' => $this->pkmiac_login,
							'Password' => $this->pkmiac_password
						)
					);
				}
				catch ( SoapFault $e ) {
					$personCardQueueResponse = $this->dbmodel->setPersonCardQueueRecord(array(
						'PersonCardQueue_id' => $record['PersonCardQueue_id'],
						'PersonCardQueue_Status' => 2
					));
					$this->writeRecordToLog('[' . date('Y-m-d H:i:s') . "] Error: " . toAnsi($e->getMessage()));
					continue;
				}

				// Обработка $soapResponse
				$soapResult = (array)$soapResponse->FD_UPDATEResult;

				if ( $soapResult['errorCode'] > 0 ) {
					$this->writeRecordToLog('[' . date('Y-m-d H:i:s') . "] Error (" . $soapResult['errorCode'] . "): " . toAnsi($soapResult['errorMessage']));
				}

				// Обновление записи в PersonCardQueue
				$personCardQueueResponse = $this->dbmodel->setPersonCardQueueRecord(array(
					'PersonCardQueue_id' => $record['PersonCardQueue_id'],
					'PersonCardQueue_Status' => $soapResult['status']
				));
			}
		}
		else {
			$this->writeRecordToLog('[' . date('Y-m-d H:i:s') . '] Ошибка при получении списка прикрепленных');
		}

		$this->writeRecordToLog('[' . date('Y-m-d H:i:s') . '] Прикрепление завершено');

		return true;
	}
}

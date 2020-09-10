<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* PolisQueue - контроллер для добавления "приглашений" на закачку обновленных полисов
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Services
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      27.10.2010
*/


class PolisQueue extends swController {
	public $inputRules = array(
		'PolisQueue' => array(
			array(
				'field' => 'inputData',
				'label' => 'JSON-строка',
				'rules' => 'trim|required',
				'type' => 'string'
			)
		)
	);

	/**
	 * Description
	 */
	public function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model("PolisQueue_model", "dbmodel");
		$this->load->helper('Date');
	}


	/**
	 * Description
	 */
	public function Index() {
		$data     = array();
		$post     = array();
		$response = array(
			'status' => 0,
			'errorCode' => 0,
			'errorMessage' => ''
		);
		/*
		$_POST['inputData'] = json_encode(
			array(
				array(
					'polisID' => 1,
					'regNomC' => 4,
					'regNomN' => 1247,
					'polisSetDT' => '28.10.2010 10:05:12'
				)
			)
		);
		*/
		$err = getInputParams($post, $this->inputRules['PolisQueue']);

		if ( strlen($err) > 0 ) {
			$response['status'] = -1;
			$response['errorCode'] = 1;
			$response['errorMessage'] = 'Отсутствуют входящие параметры';
		}
		else {
			$data = json_decode($post['inputData'], true);

			if ( is_array($data) && count($data) > 0 ) {
				foreach ( $data as $key => $record ) {
					$recordCheck = $this->checkRecordFormat($record);

					if ( !$recordCheck['success'] ) {
						$response['status'] = -1;
						$response['errorCode'] = 11;
						$response['errorMessage'] = $recordCheck['Error_Msg'];
						break;
					}

					$getOrgSmoId = $this->dbmodel->getOrgSmoId($record['regNomC'], $record['regNomN']);

					if ( strlen($getOrgSmoId['Error_Msg']) > 0 ) {
						$response['status'] = -1;
						$response['errorCode'] = 12;
						$response['errorMessage'] = $getOrgSmoId['Error_Msg'];
						break;
					}

					$data[$key]['OrgSmo_id'] = $getOrgSmoId['OrgSmo_id'];
				}

				if ( $response['status'] == 0 ) {
					$saveResponse = $this->dbmodel->savePolisQueue($data);

					if ( $saveResponse['success'] === false ) {
						$response['status'] = -1;
						$response['errorCode'] = 13;
						$response['errorMessage'] = $saveResponse['Error_Msg'];
					}
				}
			}
			else {
				$response['status'] = -1;
				$response['errorCode'] = 2;
				$response['errorMessage'] = 'Отсутствуют входящие параметры';
			}
		}

		$response['errorMessage'] = toUTF($response['errorMessage']);

		echo json_encode($response);

		return true;
	}


	/**
	 * Description
	 */
	private function checkRecordFormat($record) {
		$result = array(
			'success' => true,
			'Error_Msg' => ''
		);

		if ( !isset($record['polisID']) || !is_numeric($record['polisID']) || $record['polisID'] <= 0 ) {
			$result['success'] = false;
			$result['Error_Msg'] = 'Неверный идентификатор полиса (polisID)';
		}
		else if ( !isset($record['regNomC']) || !is_numeric($record['regNomC']) || $record['regNomC'] <= 0 ) {
			$result['success'] = false;
			$result['Error_Msg'] = 'Неверный код СМО (regNomC)';
		}
		else if ( !isset($record['regNomN']) || !is_numeric($record['regNomN']) || $record['regNomN'] <= 0 ) {
			$result['success'] = false;
			$result['Error_Msg'] = 'Неверный код СМО (regNomN)';
		}
		else if ( !isset($record['polisSetDT']) || strlen($record['polisSetDT']) == 0 ) {
			$result['success'] = false;
			$result['Error_Msg'] = 'Неверное значение даты/времени добавления/изменения полиса в СБДЗ (polisSetDT)';
		}
		else {
			if ( !preg_match('/^\d{2}\.\d{2}\.\d{4} \d{2}:\d{2}:\d{2}$/', $record['polisSetDT']) ) {
				$result['success'] = false;
				$result['Error_Msg'] = 'Неверное формат даты/времени добавления/изменения полиса в СБДЗ (polisSetDT)';
			}
			else {
				$DT = explode(' ', $record['polisSetDT']);
			}
		}

		return $result;
	}
}

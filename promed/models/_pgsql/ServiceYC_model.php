<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ServiceNSI_model - модель для работы с сервисом НСИ ЕГИСЗ
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      ServiceNSI
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 * @author       Maksim Yavorskiy
 * @version      17.10.2019
 */

class ServiceYC_model extends swPgModel {
	protected $ServiceList_id;

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		$this->load->library('textlog', array('file' => 'ServiceYC_'.date('Y-m-d').'.log'));

		$this->load->model('ServiceList_model');
		$this->load->helper('ServiceListLog');
		$this->ServiceList_id = $this->ServiceList_model->getServiceListId('ServiceUpdСС');
	}

	/**
	 * Создание исключений по ошибкам
	 */
	function exceptionErrorHandler($errno, $errstr, $errfile, $errline) {
		switch ($errno) {
			case E_NOTICE:
			case E_USER_NOTICE:
				$errors = "Notice";
				break;
			case E_WARNING:
			case E_USER_WARNING:
				$errors = "Warning";
				break;
			case E_ERROR:
			case E_USER_ERROR:
				$errors = "Fatal Error";
				break;
			default:
				$errors = "Unknown Error";
				break;
		}

		$msg = sprintf("%s:  %s in %s on line %d", $errors, $errstr, $errfile, $errline);
		throw new ErrorException($msg, 0, $errno, $errfile, $errline);
	}

	/**
	 * Обработка Fatal Error
	 */
	function shutdownErrorHandler($func) {
		$error = error_get_last();

		if (!empty($error)) {
			switch ($error['type']) {
				case E_NOTICE:
				case E_USER_NOTICE:
					$type = "Notice";
					break;
				case E_WARNING:
				case E_USER_WARNING:
					$type = "Warning";
					break;
				case E_ERROR:
				case E_USER_ERROR:
					$type = "Fatal Error";
					break;
				default:
					$type = "Unknown Error";
					break;
			}

			$msg = sprintf("%s:  %s in %s on line %d", $type, $error['message'], $error['file'], $error['line']);

			//$func($msg);
			call_user_func($func, $msg);

			exit($error['type']);
		}
	}

	/**
	 * Выполнение запросов к сервису УЦ и обработка ошибок, которые возвращает сервис
	 */
	function exec() {
		$this->load->library('swServiceYC', $this->config->item('ServiceYC'), 'service');
		$this->textlog->add("exec method: download registry");
		$result = $this->service->data();
		$this->textlog->add("result: ".print_r($result,true));
		if (is_object($result) && !empty($result->Message)) {
			$result = array(
				'success' => false,
				'errorMsg' => 'Ошибка в работе сервиса УЦ: '.$result->Message
			);
		}
		if (is_object($result) && !empty($result->ExceptionMessage)) {
			$result = array(
				'success' => false,
				'errorMsg' => 'Ошибка в работе сервиса УЦ: '.$result->ExceptionMessage
			);
		}
		return $result;
	}

	/**
	 * Получение списка справочников
	 */
	function syncSprList($data) {
		// 1. Загружаем реестр УЦ
		$resp = $this->exec();

		if (is_object($resp)) {
			throw new Exception('Ошибка получения реестра УЦ: ' . print_r($resp, true));
		} else {
			$res = new SimpleXMLElement($resp);

			$register = array();
			$i = 1;
			foreach ($res as $key => $item) {
				if (in_array($key, array('Дата', 'Версия', 'Signature'))) continue;
				$register[$i]['CertificateCenter_Name'] = objectToArray($item->Название)[0];
				$register[$i]['CertificateCenter_Nick'] = objectToArray($item->КраткоеНазвание)[0];
				$register[$i]['CertificateCenter_INN'] = objectToArray($item->ИНН)[0];
				$register[$i]['CertificateCenter_Ogrn'] = objectToArray($item->ОГРН)[0];
				$register[$i]['CertificateCenterStatus_Name'] = objectToArray($item->СтатусАккредитации->Статус)[0];
				$register[$i]['CertificateCenterStatusHist_begDate'] = objectToArray($item->СтатусАккредитации->ДействуетС)[0];
				$i++;
			}

			foreach ($register as $item) {
				$this->saveCertificateCenter(array(
					'CertificateCenter_Name' => $item['CertificateCenter_Name'],
					'CertificateCenter_Nick' => $item['CertificateCenter_Nick'],
					'CertificateCenter_INN' => $item['CertificateCenter_INN'],
					'CertificateCenter_Ogrn' => $item['CertificateCenter_Ogrn'],
					'CertificateCenterStatus_Name' => $item['CertificateCenterStatus_Name'],
					'CertificateCenterStatusHist_begDate' => $item['CertificateCenterStatusHist_begDate'],
					'pmUser_id' => $data['pmUser_id']
				));
			}
		}
	}

	/**
	 * Сохранение справочников в БД
	 */
	function saveCertificateCenter($data) {
		// проверяем, есть ли такой центр сертификации у нас уже
		$resp = $this->queryResult("
			select
				CC.CertificateCenter_id as \"CertificateCenter_id\",
				CC.CertificateCenter_Name as \"CertificateCenter_Name\",
				CC.CertificateCenter_Nick as \"CertificateCenter_Nick\",
				CC.CertificateCenter_INN as \"CertificateCenter_INN\",
				CC.CertificateCenter_Ogrn as \"CertificateCenter_Ogrn\",
				CCSH.CertificateCenterStatusHist_id as \"CertificateCenterStatusHist_id\",
				CCS.CertificateCenterStatus_id as \"CertificateCenterStatus_id\",
				CCS.CertificateCenterStatus_Name as \"CertificateCenterStatus_Name\"
			from
				v_CertificateCenter CC
				left join CertificateCenterStatusHist CCSH on CCSH.CertificateCenter_id = CC.CertificateCenter_id
				left join CertificateCenterStatus CCS on CCS.CertificateCenterStatus_id = CCSH.CertificateCenterStatus_id
			where
				CC.CertificateCenter_INN = :CertificateCenter_INN
			limit 1
		", array(
			'CertificateCenter_INN' => $data['CertificateCenter_INN']
		));

		$statusData = array();

		if (!empty($resp[0]['CertificateCenter_id'])) {
			$procCC = 'p_CertificateCenter_upd';
			$procCCS = 'p_CertificateCenterStatusHist_upd';
			$data['CertificateCenter_id'] = $resp[0]['CertificateCenter_id'];
			$data['CertificateCenter_Name'] = $resp[0]['CertificateCenter_Name'];
			$data['CertificateCenter_Nick'] = $resp[0]['CertificateCenter_Nick'];
			$data['CertificateCenter_INN'] = $resp[0]['CertificateCenter_INN'];
			$data['CertificateCenter_Ogrn'] = $resp[0]['CertificateCenter_Ogrn'];
			$statusData['CertificateCenterStatusHist_id'] = $resp[0]['CertificateCenterStatusHist_id'];
			$statusData['CertificateCenterStatusHist_begDate'] = $data['CertificateCenterStatusHist_begDate'];
			$statusData['pmUser_id'] = $data['pmUser_id'];
		} else {
			$procCC = 'p_CertificateCenter_ins';
			$procCCS = 'p_CertificateCenterStatusHist_ins';
			$data['CertificateCenter_id'] = null;
			$statusData['CertificateCenterStatusHist_id'] = null;
			$statusData['CertificateCenterStatusHist_begDate'] = $data['CertificateCenterStatusHist_begDate'];
			$statusData['pmUser_id'] = $data['pmUser_id'];
		}

		$query = "	
			select
				CertificateCenter_id as \"CertificateCenter_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procCC}
				CertificateCenter_id := :CertificateCenter_id,
				CertificateCenter_Name := :CertificateCenter_Name,
				CertificateCenter_Nick := :CertificateCenter_Nick,
				CertificateCenter_INN := :CertificateCenter_INN,
				CertificateCenter_Ogrn := :CertificateCenter_Ogrn,
				pmUser_id := :pmUser_id
			)
		";

		$certificateCenter_id = $this->queryResult($query, $data);

		if (isset($certificateCenter_id[0]['CertificateCenter_id'])) {
			$statusData['CertificateCenter_id'] = $certificateCenter_id[0]['CertificateCenter_id'];

			$statusData['CertificateCenterStatus_id'] = $this->getStatusIdByName($data['CertificateCenterStatus_Name']);

			$query = "	
				select
					CertificateCenterStatusHist_id as \"CertificateCenterStatusHist_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from {$procCCS}(
					CertificateCenterStatusHist_id := :CertificateCenterStatusHist_id,
					CertificateCenter_id := :CertificateCenter_id,
					CertificateCenterStatus_id := :CertificateCenterStatus_id,
					CertificateCenterStatusHist_begDate := :CertificateCenterStatusHist_begDate,
					pmUser_id := :pmUser_id
				)
			";

			$this->queryResult($query, $statusData);
		}
	}

	public function getStatusIdByName($name)
	{
		$query = "
			SELECT CertificateCenterStatus_id as \"CertificateCenterStatus_id\"
			FROM CertificateCenterStatus
			WHERE CertificateCenterStatus_Name = :CertificateCenterStatus_Name
		";

		$res = $this->queryResult($query, array('CertificateCenterStatus_Name' => $name));

		if(isset($res[0]['CertificateCenterStatus_id'])) {
			return $res[0]['CertificateCenterStatus_id'];
		}
		return false;
	}

	/**
	 * Запуск импорта данных
	 */
	function syncAll($data) {
		set_time_limit(0);
		ini_set("max_execution_time", "0");

		$pmUser_id = !empty($data['pmUser_id'])?$data['pmUser_id']:1;

		$this->log = new ServiceListLog($this->ServiceList_id, $pmUser_id);

		$resp = $this->log->start();
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}

		$log = $this->log;
		$this->load->helper('ShutdownErrorHandler');
		registerShutdownErrorHandler(array($this, 'shutdownErrorHandler'), function($error) use($log) {
			$log->add(false, array("Импорт данных из сервиса УЦ завершён с ошибкой:", $error));
			$log->finish(false);
		});

		try {
			set_error_handler(array($this, 'exceptionErrorHandler'));

			$this->log->add(true, "Запуск импорта данных из сервиса УЦ");
			$this->syncSprList($data);
			$this->log->add(true, "Импорт данных из сервиса УЦ завершён успешно");
			$this->log->finish(true);
		} catch(Exception $e) {
			restore_exception_handler();

			$code = $e->getCode();
			$error = $e->getMessage();

			$this->log->add(false, array("Импорт данных из сервиса УЦ завершён с ошибкой:", $error));
			$this->log->finish(false);

			$response = $this->createError($code, $error);
			$response[0]['ServiceListLog_id'] = $this->log->getId();

			return $response;
		}

		return array(array('success' => true, 'ServiceListLog_id' => $this->log->getId()));
	}
}

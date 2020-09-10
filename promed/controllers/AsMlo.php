<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * AsMlo - контроллер обмена данными с АС МЛО-сервисом
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package	  AsMlo
 * @access	   public
 * @copyright	Copyright (c) 2014 Swan Ltd.
 * @author	   Dmitriy Vlasenko
 * @version	  06 2014
 * @property AsMlo_model dbmodel
 */
class AsMlo extends swController {
	public $inputRules = array(
		'checkAsMloLabSamples' => array(
		),
		'login' => array(
			array(
				'field' => 'login',
				'label' => 'Логин',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'password',
				'label' => 'Пароль',
				'rules' => '',
				'type' => 'string'
			)
		),
		'logout' => array(
		),
		'check' => array(
		),
		'setDirectory' => array(
			array(
				'field' => 'directory',
				'label' => 'Справочник',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'records',
				'label' => 'Массив строк справочника',
				'rules' => 'required',
				'type' => 'string'
			)
		),
		'getDirectory' => array(
			array(
				'field' => 'directory',
				'label' => 'Справочник',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'filters',
				'label' => 'Набор параметров для фильтрации',
				'rules' => '',
				'type' => 'string'
			)
		),
		'setSample' => array(
			array(
				'field' => 'id',
				'label' => 'Идентификатор пробы',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'number',
				'label' => 'Штрих-код',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'internalNum',
				'label' => 'Внутренний номер',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'biomaterialId',
				'label' => 'Идентификатор биоматериала',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'cito',
				'label' => 'Признак срочности',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'orderId',
				'label' => 'Идентификатор Заявки',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'clinicId',
				'label' => 'Идентификатор клиники',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'clinicName',
				'label' => 'Наименование клиники',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'directionNum',
				'label' => 'Номер направления',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'doctorId',
				'label' => 'Идентификатор врача',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'doctor',
				'label' => 'Врач',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'patOtdelen',
				'label' => 'Отделение',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'weight',
				'label' => 'Вес пациента',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'personId',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'lastName',
				'label' => 'Фамилия пациента',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'firstName',
				'label' => 'Имя пациента',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'middleName',
				'label' => 'Отчество пациента',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'sex',
				'label' => 'Пол пациента',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'snils',
				'label' => 'СНИЛС',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'polisSer',
				'label' => 'Серия полиса',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'polisNum',
				'label' => 'Номер полиса',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'dateOfBirth',
				'label' => 'Дата рождения пациента',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'update',
				'label' => 'Признак повторной отправки',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'tests',
				'label' => 'Тесты',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'targets',
				'label' => 'Исследования',
				'rules' => 'required',
				'type' => 'string'
			)
		),
		'getSampleInfo' => array(
			array(
				'field' => 'id',
				'label' => 'Идентификатор пробы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'number',
				'label' => 'Штрих-код',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'archive',
				'label' => 'Признак архивных записей',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'ready',
				'label' => 'Признак готовности проб',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'raw',
				'label' => 'Признак возврата сырых данных',
				'rules' => '',
				'type' => 'checkbox'
			)
		),
		'setWorklist' => array(
			array(
				'field' => 'id',
				'label' => 'Идентификатор рабочего списка',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'lengthX',
				'label' => 'Размерность рабочего списка по горизонтали',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'lengthY',
				'label' => 'Размерность рабочего списка по вертикали',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'worklist',
				'label' => 'Набор проб в штативе',
				'rules' => 'required',
				'type' => 'string'
			)
		),
		'getWorklistInfo' => array(
			array(
				'field' => 'id',
				'label' => 'Идентификатор рабочего списка',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'archive',
				'label' => 'Признак архивных записей',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'ready',
				'label' => 'Признак готовности проб',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'raw',
				'label' => 'Признак возврата сырых данных',
				'rules' => '',
				'type' => 'checkbox'
			)
		),
		'setSuccessConfirmation' => array(
			array(
				'field' => 'samples',
				'label' => 'Набор идентификаторов проб',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'worklists',
				'label' => 'Набор идентификаторов рабочих списков',
				'rules' => '',
				'type' => 'string'
			)
		),
		'moveArchive' => array(
			array(
				'field' => 'samples',
				'label' => 'Набор идентификаторов проб',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'worklists',
				'label' => 'Набор идентификаторов рабочих списков',
				'rules' => '',
				'type' => 'string'
			)
		),
		'createRequestSelections' => array(
 			array(
				'field' => 'MedServiceType_SysNick',
				'label' => 'Тип службы',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnLabSample_id',
				'label' => 'EvnLabSample_id',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnLabSamples',
				'label' => 'Набор проб',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'onlyNew',
				'label' => 'Признак отправки только новых тестов',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'changeNumber',
				'label' => 'Признак смены номера пробы на номер текущего дня',
				'rules' => '',
				'type' => 'string'
			)
		),
		'createRequestSelectionsLabRequest' => array(
			array(
				'field' => 'EvnLabRequest_id',
				'label' => 'EvnLabRequest_id',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnLabRequests',
				'label' => 'Набор заявок',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'onlyNew',
				'label' => 'Признак отправки только новых тестов',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'changeNumber',
				'label' => 'Признак смены номера пробы на номер текущего дня',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'CurMedService_id',
				'label' => 'id текущей службы',
				'rules' => '',
				'type' => 'string'
			)
		),
		'getResultSamples' => array(
			array(
				'field' => 'EvnLabSample_id',
				'label' => 'EvnLabSample_id',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnLabSamples',
				'label' => 'EvnLabSamples',
				'rules' => '',
				'type' => 'json_array'
			)
		),
		'isSend2AnalyzerEnabled' => array(
			array(
				'field' => 'EvnLabSamples',
				'label' => 'Набор проб',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'MedService_id',
				'label' => 'Служба',
				'rules' => '',
				'type' => 'int'
			)
		)
	);

	private $moduleMethods = [
		'createRequestSelections',
		'getResultSamples'
	];

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		$this->init();
	}

	private function init() {
		$method = $this->router->fetch_method();

		if ($this->usePostgreLis && in_array($method, $this->moduleMethods)) {
			$this->load->swapi('lis');
		} else {
			$this->load->database();
			$this->load->model('AsMlo_model', 'dbmodel');
		}
	}
	
	/**
	 * Отправляет набор выделенных проб в АсМло
	 */
	function createRequestSelections(){
		$data = $this->ProcessInputData('createRequestSelections');
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->POST("AsMlo/RequestSelections", $data);
			$this->ProcessRestResponse($response, 'single')->ReturnData();
		} else {
			$arrayId = array();
			if (!empty($data['EvnLabSamples'])) {
				$arrayId = json_decode($data['EvnLabSamples']);
			}

			$response = $this->dbmodel->createRequestSelections($data, $arrayId);
			$this->ProcessModelSave($response, true, 'Ошибка отправки в АС МЛО')->ReturnData();
		}
	}
	
	/**
	 * Отправляет набор выделенных заявок в АсМло
	 */
	function createRequestSelectionsLabRequest(){
		$data = $this->ProcessInputData('createRequestSelectionsLabRequest');
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$this->load->swapi('lis');
			$response = $this->lis->POST("AsMlo/RequestSelectionsLabRequest", $data);
			$this->ProcessRestResponse($response, 'single')->ReturnData();
		} else {
			if($data) {
				$arrayId = $this->dbmodel->getLabSamplesForEvnLabRequests($data);
			} else {
				$this->ReturnError('Для создания заявки необходимо выбрать хотя бы одну заявку с пробами');
				return false;
			}

			if (count($arrayId) < 1) {
				$this->ReturnError('В выбранных заявках отсутсвуют взятые пробы');
				return false;
			}

			$response = $this->dbmodel->createRequestSelections($data, $arrayId);
			$this->ProcessModelSave($response, true, 'Ошибка отправки в АС МЛО')->ReturnData();
		}
	}

	/**
	 * выполняет setSuccessConfirmation для заданных проб
	 */
	function doSetSuccessConfirmation() {
		$response = $this->dbmodel->doSetSuccessConfirmation();
		$this->ProcessModelSave($response, true, 'Ошибка подтверждения успешной передачи информации данных')->ReturnData();
	}
	
	/**
	 * Получает данные из АсМло по нескольким выбранным пробам
	 */
	function getResultSamples(){
		$data = $this->ProcessInputData('getResultSamples');
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->GET("AsMlo/ResultSamples", $data);
			$this->ProcessRestResponse($response, 'single')->ReturnData();
		} else {
			if(!$data || empty($data['EvnLabSamples'])) {
				DieWithError('Для получения результатов нужно выбрать хотя бы одну пробу');
			}
			$answers = array();
			$resultSuccess = false;
			$isErrorReplyFormat = false;
			foreach($data['EvnLabSamples'] as $idObj) {
				$data['EvnLabSample_id'] = $idObj->id;
				$data['EvnLabSample_BarCode'] = $idObj->barcode;
		
				if ( !$this->dbmodel->isLogon() ) {
					$result = $this->dbmodel->login($data);
					if ( !( is_array($result) && !empty($result['success']) && $result['success']==1 ) ) {
						if ( is_array($result) && !empty($result['Error_Msg']) ) {
							$this->ReturnError($result['Error_Msg']);
							return;
						}
						$this->ReturnError('Ошибка идентификации в сервисе АСМЛО');
						return;
					}
				}
				if (!$this->dbmodel->isLogon()) {
					$this->ReturnError('Ошибка авторизации в сервисе АСМЛО');
					return;
				}

				$result = $this->dbmodel->getSampleInfo(array(
					'id' => $data['EvnLabSample_id']
				,'number' => $data['EvnLabSample_BarCode']
				));
				//log_message('error', 'result-getSampleInfo:'); log_message('error', print_r($result, true));
				if ( !is_array($result) ) {
					$isErrorReplyFormat = true;//Признак наличия ошибки формата ответного сообщения
					$errMess = 'Неверный формат ответа от АСМЛО';
					$answers[$errMess] = $errMess;
					//$this->ReturnError($errMess);
					//return false;
					continue;
				} else if ( !empty($result['Error_Msg']) ) {
					$errMess = $this->dbmodel->getErrorMessage($result['Error_Code'], ($idObj->analyzer2way == '2'));
					$answers[$errMess] = $errMess;
					continue;
				}

				$result = $this->dbmodel->getResultSamples($data, $result['response']);
				$resultSuccess = true; //Признак успеха хотя бы по одной пробе
			}
			if ((!$resultSuccess || $isErrorReplyFormat) && count($answers)>0) { // Если ("Не успех" или "ошибка формата") и есть ошибки то выведем их
				$this->ReturnError(join(';<br/>', $answers));
				return;
			}
			$this->ReturnData(array('success'=>true));
		}
	}
	
	/**
	 * Получение результатов из АсМло
	 */
	function checkAsMloLabSamples() {
		$data = $this->ProcessInputData('checkAsMloLabSamples');
		if ($data === false) return;

		$response = $this->dbmodel->checkAsMloLabSamples($data);
		$this->ProcessModelSave($response, true, 'Ошибка получения результатов')->ReturnData();
	}

	/**
	 * Идентификация в сервисе
	 */
	function login() {
		$data = $this->ProcessInputData('login');
		if ($data === false) return;

		$response = $this->dbmodel->login($data);
		$this->ProcessModelSave($response, true, 'Ошибка идентификации в сервисе')->ReturnData();
	}
	
	/**
	 * Завершение сессии в сервисе
	 */
	function logout() {
		$data = $this->ProcessInputData('logout');
		if ($data === false) return;

		$response = $this->dbmodel->logout($data);
		$this->ProcessModelSave($response, true, 'Ошибка завершения сессии в сервисе')->ReturnData();
	}
	
	/**
	 * Проверка готовности работы сервиса
	 */
	function check() {
		$data = $this->ProcessInputData('check');
		if ($data === false) return;

		$response = $this->dbmodel->check($data);
		$this->ProcessModelSave($response, true, 'Ошибка проверки готовности работы сервиса')->ReturnData();
	}

	/**
	 * Передача ГОСТ-2011 сервису
	 */
	function setDirectoryGost2011() {
		set_time_limit(0);

		$response = $this->dbmodel->setDirectoryGost2011();
		$this->ProcessModelSave($response, true, 'Ошибка передачи справочника сервису')->ReturnData();
	}

	/**
	 * Передача ЛПУ сервису
	 */
	function setDirectoryLpu() {
		set_time_limit(0);

		$response = $this->dbmodel->setDirectoryLpu();
		$this->ProcessModelSave($response, true, 'Ошибка передачи справочника сервису')->ReturnData();
	}

	/**
	 * Передача биоматериалов сервису
	 */
	function setDirectoryRefMaterial() {
		set_time_limit(0);

		$response = $this->dbmodel->setDirectoryRefMaterial();
		$this->ProcessModelSave($response, true, 'Ошибка передачи справочника сервису')->ReturnData();
	}

	/**
	 * Передача справочника сервису
	 */
	function setDirectory() {
		$data = $this->ProcessInputData('setDirectory');
		if ($data === false) return;

		if (!empty($data['records'])) {
			$data['records'] = json_decode($data['records'], true);
		} else {
			$data['records'] = array();
		}

		$response = $this->dbmodel->setDirectory($data);
		$this->ProcessModelSave($response, true, 'Ошибка передачи справочника сервису')->ReturnData();
	}
	
	/**
	 * Получение справочника из сервиса
	 */
	function getDirectory() {
		$data = $this->ProcessInputData('getDirectory');
		if ($data === false) return;

		if (!empty($data['filters'])) {
			$data['filters'] = json_decode($data['filters'], true);
		} else {
			$data['filters'] = array();
		}

		$response = $this->dbmodel->getDirectory($data);
		$this->ProcessModelSave($response, true, 'Ошибка получения справочника из сервиса')->ReturnData();
	}
	
	/**
	 * Передача проб в сервис
	 */
	function setSample() {
		$data = $this->ProcessInputData('setSample');
		if ($data === false) return;

		if (!empty($data['tests'])) {
			$data['tests'] = json_decode($data['tests'], true);
		} else {
			$data['tests'] = array();
		}

		$response = $this->dbmodel->setSample($data);
		$this->ProcessModelSave($response, true, 'Ошибка передачи пробы в сервис')->ReturnData();
	}
	
	/**
	 * Получение данных по пробе
	 */
	function getSampleInfo() {
		$data = $this->ProcessInputData('getSampleInfo');
		if ($data === false) return;

		$response = $this->dbmodel->getSampleInfo($data);
		$this->ProcessModelSave($response, true, 'Ошибка получения данных по пробе')->ReturnData();
	}
	
	/**
	 * Передача рабочих списков в сервис
	 */
	function setWorklist() {
		$data = $this->ProcessInputData('setWorklist');
		if ($data === false) return;

		if (!empty($data['worklist'])) {
			$data['worklist'] = json_decode($data['worklist'], true);
		} else {
			$data['worklist'] = array();
		}

		$response = $this->dbmodel->setWorklist($data);
		$this->ProcessModelSave($response, true, 'Ошибка передачи рабочего списка в сервис')->ReturnData();
	}
	
	/**
	 * Получение данных по рабочему списку
	 */
	function getWorklistInfo() {
		$data = $this->ProcessInputData('getWorklistInfo');
		if ($data === false) return;

		$response = $this->dbmodel->getWorklistInfo($data);
		$this->ProcessModelSave($response, true, 'Ошибка получения данных по рабочему списку')->ReturnData();
	}
	
	/**
	 * Подтверждение сервису успешной передачи информации данных рабочего списка или пробы
	 */
	function setSuccessConfirmation() {
		$data = $this->ProcessInputData('setSuccessConfirmation');
		if ($data === false) return;

		if (!empty($data['samples'])) {
			$data['samples'] = json_decode($data['samples'], true);
		} else {
			$data['samples'] = array();
		}

		if (!empty($data['worklists'])) {
			$data['worklists'] = json_decode($data['worklists'], true);
		} else {
			$data['worklists'] = array();
		}

		$response = $this->dbmodel->setSuccessConfirmation($data);
		$this->ProcessModelSave($response, true, 'Ошибка подтверждения успешной передачи информации данных')->ReturnData();
	}
	
	/**
	 * Перенос в архив пробы или рабочего списка
	 */
	function moveArchive() {
		$data = $this->ProcessInputData('moveArchive');
		if ($data === false) return;

		if (!empty($data['samples'])) {
			$data['samples'] = json_decode($data['samples'], true);
		} else {
			$data['samples'] = array();
		}

		if (!empty($data['worklists'])) {
			$data['worklists'] = json_decode($data['worklists'], true);
		} else {
			$data['worklists'] = array();
		}

		$response = $this->dbmodel->moveArchive($data);
		$this->ProcessModelSave($response, true, 'Ошибка переноса в архив')->ReturnData();
	}
	
	/**
	 * Формирование признака доступности кнопки "Отправить на анализатор"
	 */
	function isSend2AnalyzerEnabled() {
		$data = $this->ProcessInputData('isSend2AnalyzerEnabled');
		if ($data === false) return;

		$response = $this->dbmodel->isSend2AnalyzerEnabled($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
}
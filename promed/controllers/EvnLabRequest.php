<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnLabRequest - контроллер для работы с заявками на лабораторное обследование
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      common
 * @access       public
 * @copyright    Copyright (c) 2009-2010 Swan Ltd.
 * @author       gabdushev
 * @version      март 2012

 * @property EvnLabRequest_model $dbmodel
 */
class EvnLabRequest extends swController {
	// Костыль для устранения ошибки https://redmine.swan.perm.ru/issues/47755
	// Используются в Lis_model.php, определяются только в контроллере Lis.php
	public $server = array(); // редактируются через форму "Общие настройки", умолчания прописаны в Options_model.php
	public $debug = false;

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		if ($this->usePostgreLis) {
			$this->load->swapi('lis');
		} else {
			$this->load->database();
			$this->load->model('EvnLabRequest_model', 'dbmodel');
		}

		$this->inputRules = array(
			'getEvnUslugaParForPrint' => array(
				array('field' => 'EvnDirections', 'label' => 'Идентификаторы направлений', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'isProtocolPrinted', 'label' => 'Протокол распечатан', 'rules' => '', 'type' => 'int')
			),
			'loadCompositionMenu' => array(
				array('field' => 'EvnDirection_id', 'label' => 'Направление', 'rules' => '', 'type' => 'id')
			),
			'saveEvnLabRequestContent' => array(
				array('field' => 'EvnDirection_id', 'label' => 'Заявка на проведение лабораторного обследования', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'UslugaComplexContent_ids', 'label' => 'Состав исследования', 'rules' => '', 'type' => 'string')
			),
			'getNewEvnLabRequests' => array(
				array('field' => 'MedService_id','label' => 'Служба','rules' => 'required','type' => 'id'),
				array('field' => 'Person_id','label' => 'Идентификатор человека','rules' => 'required','type' => 'id')
			),
			'loadEvnLabRequestList' => array(
				array('field' => 'MedService_id','label' => 'Служба','rules' => 'required','type' => 'id'),
				array('field' => 'MedServiceLab_id','label' => 'Служба','rules' => '','type' => 'id'),
				array('field' => 'MedServiceType_SysNick','label' => 'Тип службы','rules' => '','type' => 'string'),
				array('field' => 'EvnDirection_IsCito','label' => 'Cito!','rules' => '','type' => 'id', 'default' => null),
				array('field' => 'EvnLabSample_IsOutNorm','label' => 'Отклонение','rules' => '','type' => 'id', 'default' => null),
				array('field' => 'begDate','label' => 'Начало периода','rules' => '','type' => 'date', 'default' => null),
				array('field' => 'endDate','label' => 'Конец периода','rules' => '','type' => 'date', 'default' => null),
				array('field' => 'EvnLabRequest_id','label' => 'Заявка','rules' => '','type' => 'id'),
				array('field' => 'filterWorkELRByDate','label' => 'Фильтровать заявки в работе по дате','rules' => '','type' => 'int'),
				array('field' => 'filterDoneELRByDate','label' => 'Фильтровать заявки с результатами по дате','rules' => '','type' => 'int'),
				array('field' => 'filterSign','label' => 'Фильтр по подписи','rules' => '','type' => 'id'),
				array('field' => 'EvnStatus_id', 'label' => 'Статус заявки', 'rules' => '', 'type' => 'id'),
				array('field' => 'pmUser_id', 'label' => 'Пользователь', 'rules' => '', 'type' => 'id'),
				array('field' => 'UslugaComplex_id', 'label' => 'Фильтр по услуге', 'rules' => '', 'type' => 'id'),
                array('field' => 'Person_Phone', 'label' => 'Телефон', 'rules' => '', 'type' => 'string'),
                array('field' => 'EvnDirection_Num', 'label' => 'Номер направления', 'rules' => '', 'type' => 'string'),
				array('field' => 'PrehospDirect_Name', 'label' => 'Кем направлен', 'rules' => '', 'type' => 'string'),
				array('field' => '(EvnLabRequest_IsProtocolPrinted', 'label' => 'Протокол распечатан', 'rules' => '', 'type' => 'int'),
				array('field' => 'EvnLabRequest_FullBarCode', 'label' => 'Штрих-код', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'Person_SurName'       ,'label' => 'Фамилия','rules' => '','type' => 'string'),
				array('field' => 'Person_FirName'       ,'label' => 'Имя','rules' => '','type' => 'string'),
				array('field' => 'Person_SecName'       ,'label' => 'Отчество','rules' => '','type' => 'string'),
				array('field' => 'Person_id'			,'label' => 'ИД пациента','rules' => '','type' => 'id'),
				array('field' => 'Person_BirthDay'      ,'label' => 'Дата рождения','rules' => '','type' => 'date', 'default' => null),
				array('field' => 'ElectronicService_id'      ,'label' => 'Пункт обслуживания','rules' => '','type' => 'id'),
				array('field' => 'ElectronicQueueInfo_id'      ,'label' => 'Электронная очередь','rules' => '','type' => 'id'),
				array('field' => 'AnalyzerTest_id' ,'label' => 'Исследование','rules' => '','type' => 'int'),
				array('field' => 'MethodsIFA_id' ,'label' => 'Методики ИФА','rules' => '','type' => 'int'),
				array('field' => 'formMode','label' => 'Режим формы','rules' => '','type' => 'string'),
				array('field' => 'byElectronicService','label' => 'показать только заявки связанные с ЭО','rules' => '','type' => 'string'),
				array('field' => 'EvnLabRequest_RegNum', 'label' => 'Регистрационный номер', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'Lpu_sid', 'label' => 'Медицинская организация', 'rules' => '', 'type' => 'int' ),
				array('field' => 'LpuSection_id', 'label' => 'Отделение', 'rules' => '', 'type' => 'int' ),
				array('field' => 'MedStaffFact_id', 'label' => 'Врач', 'rules' => '', 'type' => 'int' ),
				array('field' => 'Lpu_id'                ,'label' => 'ЛПУ','rules' => '','type' => 'id'),
			),
			'load' => array(
				array('field' => 'EvnLabRequest_BarCode', 'label' => 'Штрих-код', 'rules' => '', 'type' => 'int'),
				array('field' => 'EvnLabRequest_id', 'label' => 'Идентификатор Заявки', 'rules' => '', 'type' => 'int'),
				array('field' => 'EvnDirection_id', 'label' => 'Направление', 'rules' => '', 'type' => 'int'),
				array('field' => 'delDocsView', 'label' => 'Просмотр удаленных документов', 'rules' => '', 'type' => 'int')
			),
			'deleteEmptySamples' => array(
				array('field' => 'EvnLabRequest_id', 'label' => 'Идентификатор Заявки', 'rules' => '', 'type' => 'int')
			),
			'save' => array(
				array('field' => 'action'       ,'label' => 'Тип операции','rules' => '','type' => 'string'),
				array('field' => 'EvnUsluga_id'       ,'label' => 'Заказ на проведение лабораторного обследования','rules' => '','type' => 'id'),
				array('field' => 'EvnDirection_id'       ,'label' => 'Направление','rules' => '','type' => 'id'),
				array('field' => 'EvnDirection_Num'       ,'label' => 'Номер направления','rules' => '','type' => 'string'),
				array('field' => 'Diag_id', 'label' => 'Диагноз', 'rules' => '', 'type' => 'id'),
				array('field' => 'TumorStage_id', 'label' => 'Стадия выявленного ЗНО', 'rules' => '', 'type' => 'id'),
				array('field' => 'Mes_id', 'label' => 'МЭС', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnDirection_setDT'   ,'label' => 'Дата направления','rules' => '','type' => 'date'/*, 'convertIntoObject' => true,*/),
				array('field' => 'EvnLabRequest_RegNum'  ,'label' => 'Регистрационный номер','rules' => '','type' => 'string'),
				array('field' => 'EvnLabRequest_Comment' ,'label' => 'Комментарий','rules' => '','type' => 'string'),
				array('field' => 'EvnLabRequest_Count'   ,'label' => 'Количество','rules' => '','type' => 'int'),
				array('field' => 'EvnLabRequest_didDT'   ,'label' => 'Дата','rules' => '','type' => 'datetime'/*, 'convertIntoObject' => true,*/),
				array('field' => 'EvnLabRequest_disDT'   ,'label' => 'Дата','rules' => '','type' => 'datetime'/*, 'convertIntoObject' => true,*/),
				array('field' => 'EvnLabRequest_id'      ,'label' => 'Заявка на проведение лабораторного обследования','rules' => '','type' => 'id'),
				array('field' => 'EvnLabRequest_Index'   ,'label' => '','rules' => '','type' => 'int'),
				array('field' => 'EvnLabRequest_insDT'   ,'label' => '','rules' => '','type' => 'datetime'/*, 'convertIntoObject' => true,*/),
				array('field' => 'EvnLabRequest_IsSigned','label' => '','rules' => '','type' => 'id'),
				array('field' => 'EvnLabRequest_pid'     ,'label' => '','rules' => '','type' => 'id'),
				array('field' => 'EvnLabRequest_rid'     ,'label' => '','rules' => '','type' => 'id'),
				array('field' => 'EvnLabRequest_setDT'   ,'label' => '','rules' => '','type' => 'datetime'/*, 'convertIntoObject' => true,*/),
				array('field' => 'EvnLabRequest_signDT'  ,'label' => '','rules' => '','type' => 'datetime'/*, 'convertIntoObject' => true,*/),
				array('field' => 'EvnLabRequest_updDT'   ,'label' => '','rules' => '','type' => 'datetime'/*, 'convertIntoObject' => true,*/),
				array('field' => 'Lpu_id'                ,'label' => 'ЛПУ','rules' => '','type' => 'id'),
				array('field' => 'MedPersonal_id'                ,'label' => 'Врач','rules' => '','type' => 'id'),
				array('field' => 'MedStaffFact_id'                ,'label' => 'Врач','rules' => '','type' => 'id'),
				array('field' => 'MedPersonal_Code'               ,'label' => 'Код врача', 'rules' => '', 'type' => 'id'),
				array('field' => 'LpuSection_id'         ,'label' => 'Направившее отделение ЛПУ','rules' => '','type' => 'id'),
				array('field' => 'Lpu_sid'                ,'label' => 'Направившее ЛПУ','rules' => '','type' => 'id'),
				array('field' => 'Org_sid'                ,'label' => 'Направившая организация','rules' => '','type' => 'id'),
				array('field' => 'PrehospDirect_id'       ,'label' => 'Кем направлен','rules' => '','type' => 'id'),
				array('field' => 'Morbus_id'             ,'label' => '','rules' => '','type' => 'id'),
				array('field' => 'Person_id'          	  ,'label' => 'Идентификатор человека','rules' => 'trim','type' => 'id'),
				array('field' => 'PersonEvn_id'          ,'label' => 'Идентификатор состояния человека','rules' => 'required','type' => 'id'),
				array('field' => 'pmUser_signID'         ,'label' => '','rules' => '','type' => 'id'),
				array('field' => 'Server_id'             ,'label' => 'Сервер','rules' => 'required','type' => 'int'),
				array('field' => 'UslugaExecutionType_id','label' => 'Тип выполнения услуги','rules' => '','type' => 'id'),
				array('field' => 'UslugaComplex_id','label' => 'Услуга','rules' => '','type' => 'id'),
				array('field' => 'EvnLabRequest_Ward','label' => 'Палата','rules' => '','type' => 'string'),
				array('field' => 'PayType_id','label' => 'Вид оплаты','rules' => 'required','type' => 'id'),
				array('field' => 'pmUser_id'             ,'label' => 'Пользователь','rules' => '','type' => 'id'),
				array('field' => 'MedService_id'             ,'label' => 'Лаборатория','rules' => '','type' => 'id', 'default'=> null),
				array('field' => 'MedService_sid'             ,'label' => 'Пункт забора','rules' => '','type' => 'id', 'default'=> null),
				array('field' => 'EvnDirection_IsCito'             ,'label' => 'Cito','rules' => '','type' => 'string'),
				array('field' => 'EvnDirection_Descr'             ,'label' => 'Комментарий','rules' => '','type' => 'string'),
				array('field' => 'LabSample'             ,'label' => 'Пробы','rules' => '','type' => 'string'),
				array('field' => 'EvnLabRequest_BarCode'             ,'label' => 'Штрих-код','rules' => '','type' => 'string'),
				array('field' => 'ignoreCheckPayType'             ,'label' => 'Игнорировать проверку вида оплаты в исследованиях','rules' => '','type' => 'int'),
				array('field' => 'PersonDetailEvnDirection_id','label' => 'PersonDetailEvnDirection_id','rules' => '','type' => 'int'),
				array('field' => 'HIVContingentTypeFRMIS_id', 'label' => 'Код контингента ВИЧ', 'rules' => '', 'type' => 'int'),
				array('field' => 'CovidContingentType_id', 'label' => 'Код контингента COVID', 'rules' => '', 'type' => 'int'),
				array('field' => 'HormonalPhaseType_id', 'label' => 'Фаза цикла', 'rules' => '', 'type' => 'int')
			),
			'cancelDirection' => array(
				array('field' => 'EvnDirection_ids', 'label' => 'Идентификаторы направлений на лабораторное обследование', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'EvnStatusCause_id', 'label' => 'Причина', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnStatusHistory_Cause', 'label' => 'Комментарий', 'rules' => 'trim', 'type' => 'string')
			),
			'approveEvnLabRequestResults' => array(
				array('field' => 'EvnLabRequests', 'label' => 'EvnLabRequests', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'onlyNormal', 'label' => 'Флаг неодобрения проб с патологией', 'rules' => 'required', 'type' => 'int')
			),
			'takeLabSample' => array(
				array('field' => 'MedServiceType_SysNick', 'label' => 'MedServiceType_SysNick', 'rules' => '', 'type' => 'string'),
				array('field' => 'MedService_did', 'label' => 'Служба, где взята проба', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnLabRequests', 'label' => 'EvnLabRequests', 'rules' => '', 'type' => 'string'),
				array('field' => 'sendToLis', 'label' => 'sendToLis', 'rules' => '', 'type' => 'int')
			),
			'cancelLabSample' => array(
				array('field' => 'MedServiceType_SysNick', 'label' => 'MedServiceType_SysNick', 'rules' => '', 'type' => 'string'),
				array('field' => 'MedService_did', 'label' => 'Служба, где взята проба', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnLabRequests', 'label' => 'EvnLabRequests', 'rules' => '', 'type' => 'string'),
				array('field' => 'sendToLis', 'label' => 'sendToLis', 'rules' => '', 'type' => 'int')
			),
			'getLabTestsPrintData' => array(
				array('field' => 'Evn_pid', 'label' => 'Evn_pid', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnClass_SysNick', 'label' => 'Класс события', 'rules' => '', 'type' => 'string')
			),
			'filterEvnLabRequests' => array(
				array('field' => 'EvnLabRequest_ids', 'label' => 'Список заявок', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'UslugaComplexAttributeType_SysNick', 'label' => 'Системное наименование атрибута', 'rules' => '', 'type' => 'string'),
				array('field' => 'UslugaTestStatuses', 'label' => 'Статусы теста', 'rules' => '', 'type' => 'string')
			),
			'getUslugaComplexList' => array(
				array('field' => 'EvnLabRequest_id', 'label' => 'EvnLabRequest_id', 'rules' => 'required', 'type' => 'id'),
			),
			'getCanceledEvnLabRequests' => array(
				array('field' => 'begDate','label' => 'Начало периода','rules' => '','type' => 'date', 'default' => null),
				array('field' => 'endDate','label' => 'Конец периода','rules' => '','type' => 'date', 'default' => null),
				array('field' => 'MedService_id','label' => 'Лаборатория','rules' => 'required','type' => 'id', 'default'=> null),
			)
		);
	}

	/**
	 * Загружает состав исследования в заявке
	 * @return bool
	 */
	function loadCompositionMenu() {
		$data = $this->ProcessInputData('loadCompositionMenu');
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->GET("EvnLabRequest/compositionMenu", $data);
			$this->ProcessRestResponse($response)->ReturnData();
		} else {
			$response = $this->dbmodel->loadCompositionMenu($data, false);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}

	/**
	 * Массовое взятие проб
	 */
	function takeLabSample() {
		$data = $this->ProcessInputData('takeLabSample', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->POST("EvnLabRequest/LabSample/take", $data);
			$this->ProcessRestResponse($response, 'single')->ReturnData();
		} else {
			$response = $this->dbmodel->takeLabSample($data);
			$this->ProcessModelSave($response, true)->ReturnData();
		}
	}

	/**
	 * Получение услуг по заявке для печати
	 */
	function getEvnUslugaParForPrint() {
		$data = $this->ProcessInputData('getEvnUslugaParForPrint', true);
		if ($data === false) return false;

		if ($this->usePostgreLis) {
			$response = $this->lis->GET("EvnLabRequest/EvnUslugaPar", array(
				'EvnDirections' => $data['EvnDirections'],
				'isProtocolPrinted' => $data['isProtocolPrinted']
			));
			$this->ProcessRestResponse($response)->ReturnData();
		} else {
			$response = $this->dbmodel->getEvnUslugaParForPrint($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}

	/**
	 * Массовая отмена проб
	 */
	function cancelLabSample() {
		$data = $this->ProcessInputData('cancelLabSample', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->POST("EvnLabRequest/LabSample/cancel", $data);
			$this->ProcessRestResponse($response, 'single')->ReturnData();
		} else {
			$response = $this->dbmodel->cancelLabSample($data);
			$this->ProcessModelSave($response, true)->ReturnData();
		}
	}

	/**
	 * Массовое одобрение результатов заявок
	 */
	function approveEvnLabRequestResults() {
		$data = $this->ProcessInputData('approveEvnLabRequestResults', true);
		if ($data === false) return;
		$MedService_id = $data['session']['CurARM']['MedService_id'];
		$data['MedService_id'] = $MedService_id;

		$this->load->model('MedService_model', 'MedService_model');
		$MS = $this->MedService_model->loadEditForm(['MedService_id' => $MedService_id]);
		$data['MedService_IsQualityTestApprove'] = $MS && $MS[0] ? $MS[0]['MedService_IsQualityTestApprove'] : 0;

		if ($this->usePostgreLis) {
			$response = $this->lis->POST("EvnLabRequest/approveResult", $data);
			$this->ProcessRestResponse($response, 'single')->ReturnData();
		} else {
			$response = $this->dbmodel->approveEvnLabRequestResults($data);
			$this->ProcessModelSave($response, true)->ReturnData();
		}
	}

	/**
	 * Вывод списка
	 */
	function loadEvnLabRequestList() {
		$data = $this->ProcessInputData('loadEvnLabRequestList', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$keys = [
				'EvnLabRequest_FullBarCode',
				'PrehospDirect_Name',
				'Person_SurName',
				'Person_FirName',
				'Person_SecName'
			];
			foreach ($keys as $key) {
				if ($data[$key]) {
					$data[$key] = trim($data[$key]);
					$data[$key] = json_encode($data[$key]);
				}
			}
			$response = $this->lis->GET("EvnLabRequest", $data);
			$this->ProcessRestResponse($response)->ReturnData();
		} else {
			$response = $this->dbmodel->loadEvnLabRequestList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}

	/**
	 * Вывод списка
	 */
	function getNewEvnLabRequests() {
		$data = $this->ProcessInputData('getNewEvnLabRequests', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->GET("EvnLabRequest/newEvnLabRequests", array(
				'MedService_id' => $data['MedService_id'],
				'Person_id' => $data['Person_id']
			));
			$this->ProcessRestResponse($response)->ReturnData();
		} else {
			$response = $this->dbmodel->getNewEvnLabRequests($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}

	/**
	 * Загрузка заявки
	 */
	function load() {
		$data = $this->ProcessInputData('load', true);
		if ($data === false) return;

		if($data['delDocsView'] && $data['delDocsView'] == 1 ) {
			$this->load->model('EvnLabRequest_model', 'dbmodel');
			$response = $this->dbmodel->loadForDelDocs($data['EvnDirection_id']);
			if(isset($response[0])){
				$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			}else if($this->usePostgreLis){
				$response = $this->lis->GET("EvnLabRequest", $data);
				$this->ProcessRestResponse($response)->formatDatetimeFields()->ReturnData();
			}else{
				return ['Error_Msg' => 'Не удалось получить заявку'];
			}
		}else{
			if ($this->usePostgreLis) {
				$response = $this->lis->GET("EvnLabRequest", $data);
				$this->ProcessRestResponse($response)->formatDatetimeFields()->ReturnData();
			} else {
				if ($data && isset($data['EvnLabRequest_id']) && $data['EvnLabRequest_id']) {
					$this->dbmodel->EvnLabRequest_id = $data['EvnLabRequest_id'];
					$response = $this->dbmodel->load();
				} else {
					if (($data && isset($data['EvnDirection_id']) && $data['EvnDirection_id'])) {
						$response = $this->dbmodel->load($data['EvnDirection_id']);
					} else {
						if (($data && isset($data['EvnLabRequest_BarCode']) && $data['EvnLabRequest_BarCode'])) {
							$response = $this->dbmodel->load(null, $data['EvnLabRequest_BarCode']);
						} else {
							return false;
						}
					}
				}
				$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			}
		}
	}

	/**
	 * Получение номера направления
	 * @return bool
	 */
	function getEvnDirectionNumber() {
		$data = getSessionParams();
		if ($data['Lpu_id'] == 0) {
			$this->ReturnData(array('success' => false));
			return;
		}

		if ($this->usePostgreLis) {
			$response = $this->lis->GET("EvnLabRequest/EvnDirectionNumber", array(
				'Lpu_id' => $data['Lpu_id']
			));
			$this->ProcessRestResponse($response, 'single')->ReturnData();
		} else {
			$response = $this->dbmodel->getEvnDirectionNumber($data);
			$this->ProcessModelSave($response, true)->ReturnData();
		}
	}

	/**
	 * Сгенерировать штрих-код
	 */
	function genEvnLabRequest_BarCode() {
		if ($this->usePostgreLis) {
			$response = $this->lis->GET("EvnLabRequest/BarCode/generate");
			$this->ProcessRestResponse($response, 'single')->ReturnData();
		} else {
			$response = $this->dbmodel->genEvnLabRequest_BarCode();
			$this->ProcessModelSave($response, true)->ReturnData();
		}
	}

	/**
	 * Сохранение
	 */
	function save() {
		$data = $this->ProcessInputData('save', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			if (empty($data['EvnLabRequest_id'])) {
				$response = $this->lis->POST("EvnLabRequest", $data);
			} else {
				$response = $this->lis->PUT("EvnLabRequest", $data);
			}
			$this->ProcessRestResponse($response, 'single')->ReturnData();
		} else {
			if (isset($data['EvnLabRequest_id']) && $data['EvnLabRequest_id']){
				$this->dbmodel->EvnLabRequest_id = $data['EvnLabRequest_id'];
				$this->dbmodel->load();
			} else {
				if (isset($data['EvnDirection_id']) && $data['EvnDirection_id']) {
					$this->dbmodel->load($data['EvnDirection_id']);
				}
			}
			$this->dbmodel->assign($data);
			$response = $this->dbmodel->save($data);
			$this->ProcessModelSave($response, true)->ReturnData();
		}
	}

	/**
	 * Удаление пустых проб заявки
	 */
	function deleteEmptySamples() {
		$data = $this->ProcessInputData('deleteEmptySamples', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->DELETE("EvnLabRequest/emptySamples", $data);
			$this->ProcessRestResponse($response, 'single')->ReturnData();
		} else {
			$response = $this->dbmodel->deleteEmptySamples($data);
			$this->ProcessModelSave($response, true)->ReturnData();
		}
	}

	/**
	 * Сохранение состава заявки
	 */
	function saveEvnLabRequestContent() {
		$data = $this->ProcessInputData('saveEvnLabRequestContent', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->POST("EvnLabRequest/Content", $data);
			$this->ProcessRestResponse($response, 'single')->ReturnData();
		} else {
			$response = $this->dbmodel->saveEvnLabRequestContent($data);
			$this->ProcessModelSave($response, true)->ReturnData();
		}
	}

	/**
	 * Отмена направления
	 */
	function cancelDirection() {
		$data = $this->ProcessInputData('cancelDirection', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->POST("EvnLabRequest/cancelDirection", $data);
			$this->ProcessRestResponse($response, 'single')->ReturnData();
		} else {
			$this->load->helper('Reg');

			if (!empty($data['EvnDirection_ids'])) {
				$data['EvnDirection_ids'] = json_decode($data['EvnDirection_ids'], true);
				foreach($data['EvnDirection_ids'] as $item) {
					$data['EvnDirection_id'] = $item;
					$response = $this->dbmodel->cancelDirection($data);
				}
			}
			if (!empty($response)) {
				$this->ProcessModelSave($response, true, 'Ошибка отмены направления')->ReturnData();
			} else {
				$this->ReturnError('Ошибка при отмене направления');
			}
		}
	}

	/**
	 * Получение данных маркера ЭМК для TinyMCE
	 */
	function getLabTestsPrintData() {
		$data = $this->ProcessInputData('getLabTestsPrintData', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->GET("EvnLabRequest/LabTestsPrintData", $data);
			$this->ProcessRestResponse($response, 'single')->ReturnData();
		} else {
			$response = $this->dbmodel->getLabTestsPrintData($data);
			$this->ProcessModelSave($response, true)->ReturnData();
		}
	}

	/**
	 * Фильтрация заявок
	 */
	function filterEvnLabRequests() {
		$data = $this->ProcessInputData('filterEvnLabRequests', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->GET("EvnLabRequest/filterEvnLabRequests", $data);
			$this->ProcessRestResponse($response)->ReturnData();
		} else {
			$response = $this->dbmodel->filterEvnLabRequests($data);
			$this->ReturnData($response);
		}
	}

	/**
	 * Получение списка UslugaColplex по заявке
	 */
	function getUslugaComplexList() {
		$data = $this->ProcessInputData('getUslugaComplexList', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->GET("EvnLabRequest/getUslugaComplexList", $data);
			$this->ProcessRestResponse($response)->ReturnData();
		} else {
			$response = $this->dbmodel->getUslugaComplexList($data);
			$this->ReturnData($response);
		}
	}

	/**
	 * Получение отменёных заявок
	 */
	function getCanceledEvnLabRequests() {
		$data = $this->ProcessInputData('getCanceledEvnLabRequests', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->GET("EvnLabRequest/getCanceledEvnLabRequests", $data);
			$this->ProcessRestResponse($response)->ReturnData();
		} else {
			$response = $this->dbmodel->getCanceledEvnLabRequests($data);
			$this->ReturnData($response);
		}
	}
}

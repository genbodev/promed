<?php	defined('BASEPATH') or die ('No direct script access allowed');

/**
 * PlanVolume - контроллер для выполнения операций с плановыми объёмами
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 * @author       Dmitry Vlasenko
 * @version      21.11.2018
 *
 * @property PlanVolume_model dbmodel
 */
class PlanVolume extends swController 
{
	/**
	 * Описание правил для входящих параметров
	 * @var array
	 */
	var $inputRules = array(
		'deletePlanVolumeRequest' => array(
			array(
				'field' => 'id',
				'label' => 'Идентификатор заявки на плановый объём',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getPlanVolumeRequestNumber' => array(
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор МО',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'savePlanVolumeRequest' => array(
			array(
				'field' => 'PlanVolumeRequest_id',
				'label' => 'Идентификатор заявки на плановый объём',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор МО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PlanVolumeRequest_Num',
				'label' => 'Номер',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MedicalCareBudgType_id',
				'label' => 'Тип мед. помощи',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PayType_id',
				'label' => 'Вид оплаты',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'QuoteUnitType_id',
				'label' => 'Единица измерения',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PlanVolumeRequest_Value',
				'label' => 'Значение',
				'rules' => 'required',
				'type' => 'float'
			),
			array(
				'field' => 'PlanVolumeRequest_begDT',
				'label' => 'Дата начала',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'PlanVolumeRequest_endDT',
				'label' => 'Дата окончания',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'PlanVolumeRequest_Comment',
				'label' => 'Примечание',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'PlanVolumeRequestStatus_id',
				'label' => 'Статус',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PlanVolumeRequestSourceType_id',
				'label' => 'Тип источника',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PlanVolume_id',
				'label' => 'Исходный объём',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadPlanVolumeRequestEditWindow' => array(
			array(
				'field' => 'PlanVolumeRequest_id',
				'label' => 'Идентификатор заявки на плановый объём',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadPlanVolumeRequestGrid' => array(
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор МО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_ids',
				'label' => 'Идентификаторы МО',
				'rules' => '',
				'type'	=> 'string'
			),
			array(
				'field' => 'isClose',
				'label' => 'Флаг закрытия',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'MedicalCareBudgType_id',
				'label' => 'Тип мед. помощи',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PayType_id',
				'label' => 'Вид оплаты',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'QuoteUnitType_id',
				'label' => 'Единица измерения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Year',
				'label' => 'Год',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PlanVolumeRequestStatus_id',
				'label' => 'Статус',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PlanVolumeRequestSourceType_id',
				'label' => 'Тип источника',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Начальный номер записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество возвращаемых записей',
				'rules' => '',
				'type' => 'int'
			)
		),
		'loadPlanVolumeGrid' => array(
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор МО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_ids',
				'label' => 'Идентификаторы МО',
				'rules' => '',
				'type'	=> 'string'
			),
			array(
				'field' => 'isClose',
				'label' => 'Флаг закрытия',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'MedicalCareBudgType_id',
				'label' => 'Тип мед. помощи',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PayType_id',
				'label' => 'Вид оплаты',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'QuoteUnitType_id',
				'label' => 'Единица измерения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Year',
				'label' => 'Год',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PlanVolume_Num',
				'label' => 'Номер',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Начальный номер записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество возвращаемых записей',
				'rules' => '',
				'type' => 'int'
			)
		),
		'setPlanVolumeRequestStatus' => array(
			array(
				'field' => 'PlanVolumeRequest_id',
				'label' => 'Идентификатор заявки',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PlanVolumeRequestStatus_id',
				'label' => 'Статус',
				'rules' => '',
				'type' => 'id'
			)
		)
	);

	/**
	 *	Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->load->database();
		$this->load->model('PlanVolume_model', 'dbmodel');
	}

	/**
	 * Удаление заявки на плановый объём
	 */
	function deletePlanVolumeRequest() {
		$data = $this->ProcessInputData('deletePlanVolumeRequest', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->deletePlanVolumeRequest($data);
		$this->ProcessModelSave($response, true, 'Ошибка удаления заявки на плановый объём по бюджету')->ReturnData();
	}

	/**
	 * Получение номера заявки
	 */
	function getPlanVolumeRequestNumber() {
		$data = $this->ProcessInputData('getPlanVolumeRequestNumber', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->getPlanVolumeRequestNumber($data);
		$this->ProcessModelSave($response, true, 'Ошибка получения номера заявки')->ReturnData();
	}

	/**
	 * Сохранение заявки на плановый объём
	 */
	function savePlanVolumeRequest() {
		$data = $this->ProcessInputData('savePlanVolumeRequest', false);
		if ($data === false) {
			return false;
		}

		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];

		$response = $this->dbmodel->savePlanVolumeRequest($data);
		$this->ProcessModelSave($response, true, 'Ошибка сохранения заявки на плановый объём по бюджету')->ReturnData();
	}

	/**
	 * Загрузка заявки на плановый объём на редактирование
	 */
	function loadPlanVolumeRequestEditWindow() {
		$data = $this->ProcessInputData('loadPlanVolumeRequestEditWindow', false);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->loadPlanVolumeRequestEditWindow($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}


	/**
	 * Загрузка списка заявок на плановый объём
	 */
	function loadPlanVolumeRequestGrid() {
		$data = $this->ProcessInputData('loadPlanVolumeRequestGrid', false);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->loadPlanVolumeRequestGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}


	/**
	 * Загрузка списка плановых объёмов
	 */
	function loadPlanVolumeGrid() {
		$data = $this->ProcessInputData('loadPlanVolumeGrid', false);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->loadPlanVolumeGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	 * Установка статуса заявки
	 */
	function setPlanVolumeRequestStatus() {
		$data = $this->ProcessInputData('setPlanVolumeRequestStatus', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->setPlanVolumeRequestStatus($data);
		$this->ProcessModelSave($response, true, 'Ошибка установки статуса заявки')->ReturnData();
	}
}

?>
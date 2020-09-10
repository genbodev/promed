<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ElectronicService - контроллер для работы с Пунктами обслуживания
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 *
 * @property ElectronicService_model dbmodel
 */

class ElectronicService extends swController {
	protected  $inputRules = array(
		'delete' => array(
			array(
				'field' => 'ElectronicService_id',
				'label' => 'Идентификатор пункта',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'loadList' => array(
			array(
				'field' => 'ElectronicQueueInfo_id',
				'label' => 'Идентификатор очереди',
				'rules' => 'required',
				'type' => 'id'
			),
			array('default' => 0, 'field' => 'start','label' => 'Начальная запись','rules' => '','type' => 'int'),
			array('default' => 100, 'field' => 'limit','label' => 'Лимит записей','rules' => '','type' => 'int'),
		),
		'loadElectronicServicesList' => array(
			array(
				'field' => 'MedService_id',
				'label' => 'Служба',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuBuilding_id',
				'label' => 'Служба',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Служба',
				'rules' => '',
				'type' => 'id'
			)
		),
		'load' => array(
			array(
				'field' => 'ElectronicService_id',
				'label' => 'Идентификатор пункта',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'loadElectronicServiceOrder' => array(
			array(
				'field' => 'ElectronicQueueInfo_id',
				'label' => 'Идентификатор очереди',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'AgeGroupDisp_id',
				'label' => 'Возрастная группа',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'SurveyType_id',
				'label' => 'осмотр / исследование',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ElectronicServiceOrder_Num',
				'label' => 'Порядок',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Начальная запись',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество записей',
				'rules' => '',
				'type' => 'int'
			)
		),
		'loadAgeGroupDispList' => array(
			array(
				'field' => 'DispClass_id',
				'label' => 'Идентификатор Типа диспансеризации/осмотра',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadSurveyTypeList' => array(
			array(
				'field' => 'AgeGroupDisp_id',
				'label' => 'Идентификатор возрастной группы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DispClass_id',
				'label' => 'Тип диспансеризации/осмотра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ElectronicQueueInfo_id',
				'label' => 'Идентификатор электронной очереди',
				'rules' => '',
				'type' => 'id'
			)
		),
		'checkExistAgeGroupOrderList' => array(
			array(
				'field' => 'ElectronicQueueInfo_id',
				'label' => 'Идентификатор очереди',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'AgeGroupDisp_id',
				'label' => 'Идентификатор возрастной группы',
				'rules' => 'required',
				'type' => 'id'
			)

		),
		'saveOrderServicePoints' => array(
			array(
				'field' => 'ElectronicQueueInfo_id',
				'label' => 'Идентификатор очереди',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'action',
				'label' => 'Режим редактирования',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'ServicePointsCount',
				'label' => 'Количество осмотров / исследований',
				'rules' => '',
				'type' => 'int',
			),
			array(
				'field' => 'DispClass_id',
				'label' => 'Тип диспансеризации/осмотра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'AgeGroupDisp_id',
				'label' => 'Идентификатор возрастной группы',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'ServicePoints',
				'label' => 'Осмотры / исследования',
				'rules' => 'required',
				'type' => 'string'
			)

		),
		'deleteOrderServicePoints' => array(
			array(
				'field' => 'ElectronicQueueInfo_id',
				'label' => 'Идентификатор очереди',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'AgeGroupDisp_id',
				'label' => 'Идентификатор возрастной группы',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'save' => array(
			array(
				'field' => 'ElectronicService_id',
				'label' => 'Идентификатор пункта',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ElectronicQueueInfo_id',
				'label' => 'Идентификатор очереди',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'ElectronicService_Code',
				'label' => 'Код',
				'rules' => 'trim|required',
				'type' => 'string'
			),
			array(
				'field' => 'ElectronicService_Num',
				'label' => 'Порядковый номер',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'ElectronicService_Name',
				'label' => 'Наименование',
				'rules' => 'trim|required',
				'type' => 'string'
			),
			array(
				'field' => 'ElectronicService_Nick',
				'label' => 'Краткое наименование',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'ElectronicService_isShownET',
				'label' => 'Отображать повод обращения',
				'rules' => '',
				'type' => 'checkbox'
			),
		),
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('ElectronicService_model', 'dbmodel');
	}

	/**
	 * Удаление пункта
	 */
	function delete()
	{
		$data = $this->ProcessInputData('delete');
		if ($data === false) { return false; }

		$response = $this->dbmodel->delete($data);
		$this->ProcessModelSave($response, true, 'Ошибка при удалении пункта')->ReturnData();
		return true;
	}

	/**
	 * Возвращает список пунктов
	 */
	function loadList()
	{
		$data = $this->ProcessInputData('loadList');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadList($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Возвращает список пунктов (фильтрация по службам, подразделениям или отделениям)
	 */
	function loadElectronicServicesList()
	{
		$data = $this->ProcessInputData('loadElectronicServicesList', false);
		if ($data === false) { return false; }

		if (empty($data['MedService_id']) && empty($data['LpuBuilding_id']) && empty($data['LpuSection_id'])) {
			$this->ReturnData(array(
				'Error_Msg' => 'Не указана служба, подразделение или отделение',
				'Error_Code' => 149,
				'success' => false
			));
			return false;
		}

		$response = $this->dbmodel->loadElectronicServicesList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Возвращает список возрастных групп
	 */
	function loadAgeGroupDispList() 
	{
		$data = $this->ProcessInputData('loadAgeGroupDispList', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadAgeGroupDispList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 *	Возвращает порядок прохождения для проф. осмотра
	 */
	function loadElectronicServiceOrder()
	{
		$data = $this->ProcessInputData('loadElectronicServiceOrder', false);
		if($data === false) { return false; }

		if (empty($data['ElectronicQueueInfo_id'])) {
			$this->ReturnData(array(
				'Error_Msg' => 'Не указан идентификатор очереди',
				'success' => false
			));
			return false;
		}

		$response = $this->dbmodel->loadElectronicServiceOrder($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 *	Возвращает список осмотров / исследований с фильтрацией по возрастной группе
	 */
	function loadSurveyTypeList()
	{
		$data = $this->ProcessInputData('loadSurveyTypeList', false);
		if($data === false) { return false; }


		$response = $this->dbmodel->loadSurveyTypeList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Сохранение порядка прохождения осмотров / исследований
	 */
	function saveOrderServicePoints()
	{
		$data = $this->ProcessInputData('saveOrderServicePoints');
		if($data === false) { return false; }
		if(empty($data['ServicePoints'])) {
			$data['ServicePoints'] = 1;
		}
		if($data['action'] == 'add') {
			$exist = $this->dbmodel->checkExistAgeGroupOrderList($data);
			if($exist) {
				$this->ReturnError('Для указанной возрастной группы уже существует порядок осмотров / исследований');
				return false;
			}
		} else {
			$this->dbmodel->deleteOrderServicePoints($data);
		}
		$response = $this->dbmodel->saveOrderServicePoints($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Возвращает пункт
	 */
	function load()
	{
		$data = $this->ProcessInputData('load');
		if ($data === false) { return false; }

		$response = $this->dbmodel->load($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Сохранение пункта
	 */
	function save()
	{
		$data = $this->ProcessInputData('save');
		if ($data === false) { return false; }

		$response = $this->dbmodel->save($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}
	
	/**
	 *	Проверяет существование списка порядка осмотров / исследований для возрастной группы
	 */
	function checkExistAgeGroupOrderList()
	{
		$data = $this->ProcessInputData('deleteOrderServicePoints');
		if($data === false) { return false; }
		
		$response = $this->dbmodel->checkExistAgeGroupOrderList($data);
		
		$this->ReturnData(array('AgeGroupDispList_exist' => $response));
		
		return true;
	}

	/**
	 * Удаляет пункты обслуживания из очереди, соответствующие возрастной группе
	 */
	function deleteOrderServicePoints()
	{
		$data = $this->ProcessInputData('deleteOrderServicePoints');
		if($data === false) { return false; }
		
		$response = $this->dbmodel->deleteOrderServicePoints($data);
		
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}
}
<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* OperBlock - контроллер для работы с оперблоком
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package			OperBlock
* @access			public
* @copyright		Copyright (c) 2015 Swan Ltd.
* @author			Dmitry Vlasenko
* @version			05.2015
* @property			OperBlock_model $dbmodel
*/

class OperBlock extends swController
{
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->load->database();
		$this->load->model('OperBlock_model', 'dbmodel');
		
		$this->inputRules = array(
			'createUrgentRequest' => array(
				array(
					'field' => 'LpuSection_id',
					'label' => 'Отделение',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Врач',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'MedService_id',
					'label' => 'Служба',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Пациент',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'PersonEvn_id',
					'label' => 'Пациент',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Server_id',
					'label' => 'Пациент',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'destroyCalendarEvents' => array(
				array(
					'field' => 'EvnDirection_id',
					'label' => 'Направление',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'updateCalendarEvents' => array(
				array(
					'field' => 'EvnDirection_id',
					'label' => 'Направление',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Resource_id',
					'label' => 'Ресурс',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'start',
					'label' => 'Дата начала',
					'rules' => 'required',
					'type' => 'datetime'
				),
				array(
					'field' => 'end',
					'label' => 'Дата окончания',
					'rules' => 'required',
					'type' => 'datetime'
				)
			),
			'loadMainGrid' => array(
				array(
					'field' => 'MedService_id',
					'label' => 'Служба',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'onDate',
					'label' => 'Дата',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'type',
					'label' => 'Тип',
					'rules' => '',
					'type' => 'string'
				)
			),
			'loadEvnPrescrOperBlockPlanWindow' => array(
				array(
					'field' => 'EvnDirection_id',
					'label' => 'Направление',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getCalendars' => array(
				array(
					'field' => 'MedService_id',
					'label' => 'Служба',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getCalendarEvents' => array(
				array(
					'field' => 'MedService_id',
					'label' => 'Служба',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'onDate',
					'label' => 'Дата',
					'rules' => '',
					'type' => 'date'
				)
			),
			'saveEvnPrescrOperBlockPlanWindow' => array(
				array(
					'field' => 'EvnDirection_id',
					'label' => 'Направление',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Вид операции',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Resource_id',
					'label' => 'Стол',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'TimetableResource_begDate',
					'label' => 'Дата начала',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'TimetableResource_begTime',
					'label' => 'Время начала',
					'rules' => 'required',
					'type' => 'time'
				),
				array(
					'field' => 'TimetableResource_Time',
					'label' => 'Длительность',
					'rules' => '',
					'type' => 'time'
				),
				array(
					'field' => 'BrigDataJson',
					'label' => 'Операционная бригада',
					'rules' => '',
					'type' => 'json_array',
					'assoc' => true
				),
				array(
					'field' => 'AnestDataJson',
					'label' => 'Виды анестезий',
					'rules' => '',
					'type' => 'json_array',
					'assoc' => true
				),
				array(
					'field' => 'EvnRequestOper_isAnest',
					'label' => 'Необходимость анестезии',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getIntersectedResources' => array(
				array(
					'field' => 'EvnDirection_id',
					'label' => 'Направление',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'TimetableResource_id',
					'label' => 'План ID',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Resource_id',
					'label' => 'Стол',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'start',
					'label' => 'Дата и время начала',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'end',
					'label' => 'Дата и время окончания',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'TimetableResource_begDate',
					'label' => 'Дата начала',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'TimetableResource_begTime',
					'label' => 'Время начала',
					'rules' => '',
					'type' => 'time'
				),
				array(
					'field' => 'TimetableResource_Time',
					'label' => 'Длительность',
					'rules' => '',
					'type' => 'time'
				),
				array(
					'field' => 'BrigDataJson',
					'label' => 'Операционная бригада',
					'rules' => '',
					'type' => 'json_array',
					'assoc' => true
				),
				array(
					'field' => 'AnestDataJson',
					'label' => 'Виды анестезий',
					'rules' => '',
					'type' => 'json_array',
					'assoc' => true
				),
				array(
					'field' => 'EvnRequestOper_isAnest',
					'label' => 'Необходимость анестезии',
					'rules' => '',
					'type' => 'id'
				)
			)
		);
	}

	/**
	 * Сохранение планирования
	 */
	function saveEvnPrescrOperBlockPlanWindow() {
		$data = $this->ProcessInputData('saveEvnPrescrOperBlockPlanWindow', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveEvnPrescrOperBlockPlanWindow($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении планирования')->ReturnData();
	}

	/**
	 * Проверка пересечения ресурсов
	 */
	function getIntersectedResources() {
		$data = $this->ProcessInputData('getIntersectedResources', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getIntersectedResources($data);
		$this->ProcessModelSave($response, true, 'Ошибка при проверке пересечения ресурсов')->ReturnData();
	}

	/**
	 * Сохранение экстренной заявки
	 */
	function createUrgentRequest() {
		$data = $this->ProcessInputData('createUrgentRequest', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->createUrgentRequest($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении заявки')->ReturnData();
	}

	/**
	 * Удаление записи
	 */
	function destroyCalendarEvents() {
		$data = $this->ProcessInputData('destroyCalendarEvents', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->destroyCalendarEvents($data);
		$this->ProcessModelSave($response, true, 'Ошибка при удалении записи')->ReturnData();
	}
	/**
	 * Сохранение записи
	 */
	function updateCalendarEvents() {
		$data = $this->ProcessInputData('updateCalendarEvents', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->updateCalendarEvents($data);
		$this->ProcessModelSave($response, true, 'Ошибка при записи')->ReturnData();
	}

	/**
	*  Получение главного списка АРМ
	*/
	function loadMainGrid() {
		$data = $this->ProcessInputData('loadMainGrid', true);
		if ($data === false) { return false; }
	
		$response = $this->dbmodel->loadMainGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	*  Получение данных формы планирования
	*/
	function loadEvnPrescrOperBlockPlanWindow() {
		$data = $this->ProcessInputData('loadEvnPrescrOperBlockPlanWindow', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadEvnPrescrOperBlockPlanWindow($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Получение календарей
	 */
	function getCalendars() {
		$data = $this->ProcessInputData('getCalendars', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getCalendars($data);
		$this->ReturnData($response);
	}

	/**
	 * Получение событий календарей
	 */
	function getCalendarEvents() {
		$data = $this->ProcessInputData('getCalendarEvents', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getCalendarEvents($data);
		$this->ReturnData($response);
	}
}
?>
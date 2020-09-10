<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Numerator - контроллер для работы с нумераторами
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2015 Swan Ltd.
*/

class Numerator extends swController
{
    /**
     * Конструктор
     */
    function __construct()
	{
		parent::__construct();

		$this->load->database();
		$this->load->model("Numerator_model", "dbmodel");

		$this->inputRules = array(
			'checkNumeratorOnDateWithStructure' => array(
				array(
					'field' => 'onDate',
					'label' => 'Дата',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'NumeratorObject_SysName',
					'label' => 'Тип нумератора',
					'rules' => 'required',
					'type' => 'string'
				)
			),
			'loadNumeratorEditForm' => array(
				array(
					'field' => 'Numerator_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getNumeratorNum' => array(
				array(
					'field' => 'Numerator_id',
					'label' => 'Идентификатор',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'NumeratorObject_SysName',
					'label' => 'Тип нумератора',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'showOnly',
					'label' => 'Не обновлять',
					'rules' => '',
					'type' => 'int'
				)
			),
			'getActiveNumeratorList' => array(
				array(
					'field' => 'NumeratorObject_SysName',
					'label' => 'Тип нумератора',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'NumeratorObject_Query',
					'label' => 'Запрос нумератора',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'NumeratorObject_Querys',
					'label' => 'Запросы нумератора',
					'rules' => '',
					'type' => 'json_array'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор ЛПУ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'onDate',
					'label' => 'Дата',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'allowFuture',
					'label' => 'Разрешить нумераторы с началой действия в будущем',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadNumeratorRezervEditForm' => array(
				array(
					'field' => 'NumeratorRezerv_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadNumeratorLinkEditForm' => array(
				array(
					'field' => 'NumeratorLink_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'deleteNumerator' => array(
				array(
					'field' => 'Numerator_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'ignoreCheckRezerv',
					'label' => 'Игнорировать резервные номера',
					'rules' => '',
					'type' => 'int'
				)
			),
			'deleteNumeratorRezerv' => array(
				array(
					'field' => 'NumeratorRezerv_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'deleteNumeratorLink' => array(
				array(
					'field' => 'NumeratorLink_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'saveNumerator' => array(
				array(
					'field' => 'Numerator_id',
					'label' => 'Идентификатор',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Numerator_begDT',
					'label' => 'Дата начала',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'Numerator_endDT',
					'label' => 'Дата окончания',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'NumeratorGenUpd_id',
					'label' => 'Частота обнуления',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Numerator_Ser',
					'label' => 'Серия',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Numerator_NumLen',
					'label' => 'Длина номера',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Numerator_PreNum',
					'label' => 'Префикс',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Numerator_PostNum',
					'label' => 'Постфикс',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Numerator_Num',
					'label' => 'Текущее значение',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Numerator_Name',
					'label' => 'Наименование',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'NumeratorLinkGridData',
					'label' => 'Список связанных документов',
					'rules' => '',
					'type' => 'json_array',
					'assoc' => true
				),
				array(
					'field' => 'NumeratorRezervGridData',
					'label' => 'Список диапазонов резервирования',
					'rules' => '',
					'type' => 'json_array',
					'assoc' => true
				),
				array(
					'field' => 'NumeratorRezervGenGridData',
					'label' => 'Список диапазонов генерации',
					'rules' => '',
					'type' => 'json_array',
					'assoc' => true
				),
				array(
					'field' => 'LpuGridData',
					'label' => 'Список связанных МО',
					'rules' => '',
					'type' => 'json_array',
					'assoc' => true
				),
				array(
					'field' => 'LpuStructureGridData',
					'label' => 'Список связанных структуры МО',
					'rules' => '',
					'type' => 'json_array',
					'assoc' => true
				)
			),
			'saveNumeratorRezerv' => array(
				array(
					'field' => 'NumeratorRezerv_id',
					'label' => 'Идентификатор',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'NumeratorRezervType_id',
					'label' => 'Тип резерва номеров',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Numerator_id',
					'label' => 'Идентификатор нумератора',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'NumeratorRezerv_From',
					'label' => 'Начало диапазона',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'NumeratorRezerv_To',
					'label' => 'Конец диапазона',
					'rules' => '',
					'type' => 'int'
				)
			),
			'saveNumeratorLink' => array(
				array(
					'field' => 'NumeratorLink_id',
					'label' => 'Идентификатор',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Numerator_id',
					'label' => 'Идентификатор нумератора',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'NumeratorObject_id',
					'label' => 'Документ',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadNumeratorList' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'МО',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Отделение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuUnit_id',
					'label' => 'Группа отделений',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuBuilding_id',
					'label' => 'Подразделение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'NumeratorObject_id',
					'label' => 'Документ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Numerator_Name',
					'label' => 'Наименование',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'start',
					'label' => 'Начальная запись',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'limit',
					'label' => 'Ограничение',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadNumeratorRezervList' => array(
				array(
					'field' => 'Numerator_id',
					'label' => 'Идентификатор нумератора',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'NumeratorRezervType_id',
					'label' => 'Тип резерва нумератора',
					'rules' => '',
					'type' => 'id'
				)
			),
			'loadNumeratorLinkList' => array(
				array(
					'field' => 'Numerator_id',
					'label' => 'Идентификатор нумератора',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadNumeratorLpuList' => array(
				array(
					'field' => 'Numerator_id',
					'label' => 'Идентификатор нумератора',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadNumeratorLpuStructureList' => array(
				array(
					'field' => 'Numerator_id',
					'label' => 'Идентификатор нумератора',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadLpuStructureCombo' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getNumeratorNumList' => array(
				array(
					'field' => 'Numerator_id',
					'label' => 'Идентификатор нумератора',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'NumeratorObject_SysName',
					'label' => 'Объект нумератора',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'Numerator_Num',
					'label' => 'Первый номер',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'asString',
					'label' => 'asString',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'num_count',
					'label' => 'Количество номеров',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'setDefaultNumerator' => array(
				array(
					'field' => 'Numerator_id',
					'label' => 'Идентификатор нумератора',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'NumeratorObject_SysName',
					'label' => 'Тип нумератора',
					'rules' => 'required',
					'type' => 'string'
				)
			)
		);
	}

	/**
	 * Загрузка формы редактирования
	 */
	function getNumeratorNum() {
		$data = $this->ProcessInputData('getNumeratorNum', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getNumeratorNum($data);
		if (!empty($response['Numerator_Num'])) {
			$response['Error_Msg'] = '';
		}
		$this->ProcessModelSave($response, true, 'Ошибка получения номера')->ReturnData();
	}

	/**
	 * Проверка наличия нумератора на дату со структурой
	 */
	function checkNumeratorOnDateWithStructure() {
		$data = $this->ProcessInputData('checkNumeratorOnDateWithStructure', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkNumeratorOnDateWithStructure($data);
		$this->ProcessModelSave($response, true, 'Ошибка проверки наличия нумератора со структурой')->ReturnData();
	}

	/**
	 * Загрузка формы редактирования
	 */
	function loadNumeratorEditForm() {
		$data = $this->ProcessInputData('loadNumeratorEditForm', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadNumeratorEditForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Загрузка формы редактирования
	 */
	function loadNumeratorRezervEditForm() {
		$data = $this->ProcessInputData('loadNumeratorRezervEditForm', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadNumeratorRezervEditForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Загрузка формы редактирования
	 */
	function loadNumeratorLinkEditForm() {
		$data = $this->ProcessInputData('loadNumeratorLinkEditForm', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadNumeratorLinkEditForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Удаление нумератора
	 */
	function deleteNumerator() {
		$data = $this->ProcessInputData('deleteNumerator', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteNumerator($data);
		$this->ProcessModelSave($response, true, 'Ошибка удаления нумератора')->ReturnData();
	}

	/**
	 * Удаление резерва нумератора
	 */
	function deleteNumeratorRezerv() {
		$data = $this->ProcessInputData('deleteNumeratorRezerv', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteNumeratorRezerv($data);
		$this->ProcessModelSave($response, true, 'Ошибка удаления резерва нумератора')->ReturnData();
	}

	/**
	 * Удаление связанного документа
	 */
	function deleteNumeratorLink() {
		$data = $this->ProcessInputData('deleteNumeratorLink', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteNumeratorLink($data);
		$this->ProcessModelSave($response, true, 'Ошибка удаления резерва нумератора')->ReturnData();
	}

	/**
	 * Сохранение нумератора
	 */
	function saveNumerator() {
		$sesson = getSessionParams();

		$data = $this->ProcessInputData('saveNumerator', false);
		if ($data === false) { return false; }

		$data['session'] = $sesson['session'];
		$data['pmUser_id'] = $sesson['pmUser_id'];

		$response = $this->dbmodel->saveNumerator($data);
		$this->ProcessModelSave($response, true, 'Ошибка сохранения нумератора')->ReturnData();
	}

	/**
	 * Получение списка активных нумераторов
	 */
	function getActiveNumeratorList() {
		$data = $this->ProcessInputData('getActiveNumeratorList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getActiveNumeratorList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Сохранение резерва нумератора
	 */
	function saveNumeratorRezerv() {
		$data = $this->ProcessInputData('saveNumeratorRezerv', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveNumeratorRezerv($data);
		$this->ProcessModelSave($response, true, 'Ошибка сохранения резерва нумератора')->ReturnData();
	}

	/**
	 * Сохранение связанного документа
	 */
	function saveNumeratorLink() {
		$data = $this->ProcessInputData('saveNumeratorLink', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveNumeratorLink($data);
		$this->ProcessModelSave($response, true, 'Ошибка сохранения связанного документа')->ReturnData();
	}
	
	/**
	 *  Получение списка нумераторов
	 */
	function loadNumeratorList() {
		$sesson = getSessionParams();

		$data = $this->ProcessInputData('loadNumeratorList', false);
		if ($data === false) { return false; }

		$data['session'] = $sesson['session'];
	
		$response = $this->dbmodel->loadNumeratorList($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	 *  Получение списка резерва нумераторов
	 */
	function loadNumeratorRezervList() {
		$data = $this->ProcessInputData('loadNumeratorRezervList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadNumeratorRezervList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *  Получение списка связанных документов
	 */
	function loadNumeratorLinkList() {
		$data = $this->ProcessInputData('loadNumeratorLinkList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadNumeratorLinkList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *  Получение списка МО
	 */
	function loadNumeratorLpuList() {
		$data = $this->ProcessInputData('loadNumeratorLpuList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadNumeratorLpuList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *  Получение списка структуры МО
	 */
	function loadNumeratorLpuStructureList() {
		$data = $this->ProcessInputData('loadNumeratorLpuStructureList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadNumeratorLpuStructureList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *  Получение комбо структуры МО
	 */
	function loadLpuStructureCombo() {
		$data = $this->ProcessInputData('loadLpuStructureCombo', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadLpuStructureCombo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *  Получение списка номераов
	 */
	function getNumeratorNumList() {
		$data = $this->ProcessInputData('getNumeratorNumList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getNumeratorNumList($data);
		$this->ProcessModelSave($response, true, true)->ReturnData();
	}
	
	/**
	 *  Сохраняет выбранный нумератор пользователем в сессии пользователя
	 */
	function setDefaultNumerator(){
		$data = $this->ProcessInputData('setDefaultNumerator', true);
		if ( $data === false ) {
			return false;
		}
		
		$response = $this->dbmodel->setDefaultNumerator($data);
		$this->ProcessModelSave($response)->ReturnData();
	}
}
?>
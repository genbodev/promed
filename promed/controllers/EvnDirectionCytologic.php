<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnDirectionCytologic - контроллер для работы с направлениями на цитологическое исследование
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2009-2011 Swan Ltd.
 * @author       SWAN developers
 * @version      11.02.2011
 */
 
class EvnDirectionCytologic extends swController {
	public $inputRules = array(		
		'saveEvnDirectionCytologic' => array(
			array(
				'field' => 'EvnDirectionCytologic_id',
				'label' => 'Идентификатор направления на цитологическое исследование',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Numerator_id',
				'label' => 'Идентификатор нумератора',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirectionCytologic_Ser',
				'label' => 'Серия направления на цитологическое исследование',
				'rules' => 'trim|required',
				'type' => 'string'
			),
			array(
				'field' => 'EvnDirectionCytologic_setDate',
				'label' => 'Дата направления',
				'rules' => 'trim|required',
				'type' => 'date'
			),
			array(
				'field' => 'EvnDirectionCytologic_setTime',
				'label' => 'Время направления',
				'rules' => 'trim',
				'type' => 'time'
			),
			array(
				'field' => 'EvnDirectionCytologic_Num',
				'label' => 'Номер направления на цитологическое исследование',
				'rules' => 'trim|required',
				'type' => 'string'
			),
			array(
				'field' => 'Lpu_sid',
				'label' => 'Направившая МО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_did',
				'label' => 'лпу куда направили',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_did',
				'label' => 'отделение куда направили',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'направившее отделение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => 'направивший врач',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_did',
				'label' => 'врач кому направили',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirectionCytologic_IsFirstTime',
				'label' => 'Тип направления',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirectionCytologic_IsCito',
				'label' => 'срочность',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'PayType_id',
				'label' => 'Вид оплаты',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPS_id',
				'label' => 'Идентификатор КВС',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirectionCytologic_NumCard',    // ??????????????????????
				'label' => 'Номер амб. карты ',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EvnDirectionCytologic_NumKVS',    // ??????????????????????
				'label' => 'Номер КВС',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'BiopsyReceive_id',
				'label' => 'Способ получения материала',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirectionCytologic_MaterialDT',
				'label' => 'Дата забора материала',
				'rules' => 'trim',
				'type' => 'date'
			),
			// Категория услуги ???????????
			array(
				'field' => 'UslugaComplex_id',
				'label' => 'Исследование',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_id',
				'label' => 'Диагноз',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirectionCytologic_ClinicalDiag',
				'label' => 'клинический диагноз',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnDirectionCytologic_Anamnes',
				'label' => 'краткий анамнез',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnDirectionCytologic_GynecologicAnamnes',
				'label' => 'гинекологический анамнез',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnDirectionCytologic_OperTherapy',
				'label' => 'Оперативное лечение',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnDirectionCytologic_RadiationTherapy',
				'label' => 'лучевое лечение',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnDirectionCytologic_ChemoTherapy',
				'label' => 'химиотерапия',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'BiopsyReceive_id',
				'label' => 'идентификатор способа получения материала',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonEvn_id',
				'label' => 'Идентификатор состояния человека',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'assoc' => true,
				'field' => 'VolumeAndMacroscopicDescriptionData',
				'label' => 'Описание биологического материала',
				'rules' => '',
				'type' => 'json_array'
			),
			array(
				'assoc' => true,
				'field' => 'LocalizationNatureProcessAndMethodData',
				'label' => 'Локализация, характер процесса',
				'rules' => '',
				'type' => 'json_array'
			),
			array(
				'field' => 'EvnDirectionCytologic_Data',
				'label' => 'Данные о проведенных обследованиях',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnDirectionCytologic_pid',
				'label' => 'Идентификатор',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirectionCytologic_LpuSectionName',
				'label' => 'Отделение поле ввода',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnDirectionCytologic_MedPersonalFIO',
				'label' => 'Врач поле ввода',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'MedServiceType_id',
				'label' => 'Тип Службы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedService_id',
				'label' => 'Служба',
				'rules' => '',
				'type' => 'id'
			),
		),
		'loadEvnDirectionCytologicGrid' => array(
			array(
				'field' => 'EvnDirectionCytologic_IsCito',
				'label' => 'Срочность',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirectionCytologic_Num',
				'label' => 'Номер направления на патологогистологическое исследование',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EvnDirectionCytologic_Ser',
				'label' => 'Серия направления на патологогистологическое исследование',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'begDate',
				'label' => 'Дата начала',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'endDate',
				'label' => 'Дата окончания',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnType_id',
				'label' => 'Состояние направления',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_Firname',
				'label' => 'Имя',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_Secname',
				'label' => 'Отчество',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_Surname',
				'label' => 'Фамилия',
				'rules' => 'trim',
				'type' => 'string'
			),
			// Параметры страничного вывода
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Номер стартовой записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество записей',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadEvnDirectionCytologicEditForm' => array(
			array(
				'field' => 'EvnDirectionCytologic_id',
				'label' => 'Идентификатор направления на цитологическое исследование',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadVolumeAndMacroscopicDescriptionGrid' => array(
			array(
				'field' => 'EvnDirectionCytologic_id',
				'label' => 'Идентификатор направления на цитологическое исследование',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'deleteEvnDirectionCytologic' => array(
			array(
				'field' => 'EvnDirectionCytologic_id',
				'label' => 'Идентификатор направления на цитологическое исследование',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		/*
		'loadEvnDirectionCytologicGrid' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		 * 
		 */
		'loadEvnDirectionCytologicList' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadProcessingResultsGrid' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirectionCytologic_pid',
				'label' => 'Идентификатор',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'cancelEvnDirectionCytologic' => array(
			array(
				'field' => 'EvnDirectionCytologic_id',
				'label' => 'Идентификатор направления',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStatusCause_id',
				'label' => 'Причина',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStatusHistory_Cause',
				'label' => 'Комментарий',
				'rules' => '',
				'type' => 'string'
			)
		),
		'loadUslugaList' => array(
			array(
				'field' => 'EvnDirectionCytologic_pid',
				'label' => 'Идентификатор',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Врач',
				'rules' => '',
				'type' => 'id'
			)
		)
	);


	/**
	 * comment
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('EvnDirectionCytologic_model', 'dbmodel');
	}


	/**
	*  Удаление направления на цитологическое исследование
	*  Входящие данные: $_POST['EvnDirectionHistologic_id']
	*  На выходе: JSON-строка
	*  Используется: журнал направлений на цитологическое исследование
	*/
	function deleteEvnDirectionCytologic() {
		$data = $this->ProcessInputData('deleteEvnDirectionCytologic', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->deleteEvnDirectionCytologic($data);
		$this->ProcessModelSave($response, true, 'При удалении направления на цитологическое исследование возникли ошибки')->ReturnData();

		return true;
	}


	/**
	*  Получение серии и номера направления на цитологическое исследование
	*  Входящие данные: нет
	*  На выходе: JSON-строка
	*  Используется: форма редактирования направления на цитологическое исследование
	*/
	function getEvnDirectionCytologicNumber() {
		$data = getSessionParams();

		$result = $this->dbmodel->getEvnDirectionCytologicNumber($data);		
		$this->ReturnData($result);

		return true;
	}


	/**
	*  Получение данных для формы редактирования направления на цитологическое исследование
	*  Входящие данные: $_POST['EvnDirectionCytologic_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования направления на цитологическое исследование
	*/
	function loadEvnDirectionCytologicEditForm() {
		$data = $this->ProcessInputData('loadEvnDirectionCytologicEditForm', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadEvnDirectionCytologicEditForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}


	/**
	*  Получение списка направлений на цитологическое исследование
	*  Входящие данные: <фильтры>
	*  На выходе: JSON-строка
	*  Используется: журнал направлений на цитологическое исследование
	*/
	function loadEvnDirectionCytologicGrid() {
		$data = $this->ProcessInputData('loadEvnDirectionCytologicGrid', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadEvnDirectionCytologicGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		
		return true;
	}


	/**
	*  Получение списка направлений на цитологическое исследование
	*/
	function loadEvnDirectionCytologicList() {
		$data = $this->ProcessInputData('loadEvnDirectionCytologicList', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadEvnDirectionCytologicList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return false;
	}

	/**
	*  Сохранение направления на цитологическое исследование
	*  Входящие данные: <поля формы>
	*  На выходе: JSON-строка
	*  Используется: форма редактирования направления на цитологическое исследование
	*/
	function saveEvnDirectionCytologic() {
		// my
		$data = $this->ProcessInputData('saveEvnDirectionCytologic', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->saveEvnDirectionCytologic($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении направления на патологогистологическое исследование')->ReturnData();
		
		return true;
	}
	
	/**
	 * Получение списка записей для раздела "Объем и макроскопическое описание материала"
	 */
	public function loadVolumeAndMacroscopicDescriptionGrid() {
		$data = $this->ProcessInputData('loadVolumeAndMacroscopicDescriptionGrid', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadVolumeAndMacroscopicDescriptionGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
	}
	
	/**
	 * Получение списка записей для раздела "Локализация, характер процесса и способ получения материала"
	 */
	public function loadLocalizationNatureProcessAndMethodGrid() {
		$data = $this->ProcessInputData('loadVolumeAndMacroscopicDescriptionGrid', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadLocalizationNatureProcessAndMethodGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
	}
	
	/**
	 * сохранение данных описания биологического материала
	 */
	function saveVolumeAndMacroscopicDescription(){
		return false;
	}
	
	/**
	 * сохранение данных Локализация, характер процесса и способ получения материала
	 */
	function saveLocalizationNatureProcessAndMethod(){
		return false;
	}
	
	/**
	 * Загрузка раздела "Обследование" формы Направление на цитологическое диагностическое исследование
	 */
	function loadProcessingResultsGrid(){
		$data = $this->ProcessInputData('loadProcessingResultsGrid', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadProcessingResultsGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
	}
	
	/**
	 * Отмена направления
	 */
	function cancelEvnDirectionCytologic(){
		$data = $this->ProcessInputData('cancelEvnDirectionCytologic', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->cancelEvnDirectionCytologic($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
	}
	
	/**
	 * Загрузка услуг
	 */
	function loadUslugaList(){
		$data = $this->ProcessInputData('loadUslugaList', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadUslugaList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
	}
}
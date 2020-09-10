<?php defined('BASEPATH') or die ('No direct script access allowed');

/**
 * EvnCytologicProto - контроллер для работы с протоколами цитологического исследования
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 */

class EvnCytologicProto extends swController {
	public $inputRules = array(
		'saveEvnCytologicProto' => array(
			array(
				'field' => 'EvnCytologicProto_id',
				'label' => 'Идентификатор протокола цитологического диагностического исследования',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PrescrReactionType_ids',
				'label' => 'Назначенные окраски',
				'rules' => '',
				'type' => 'multipleid'
			),
			array(
				'field' => 'MedPersonal_ids',
				'label' => 'Исследование выполнили, ФИО',
				'rules' => '',
				'type' => 'multipleid'
			),
			array(
				'field' => 'MedStaffFact_ids',
				'label' => 'Исследование выполнили, ФИО',
				'rules' => '',
				'type' => 'multipleid'
			),
			array(
				'field' => 'EvnDirectionCytologic_id',
				'label' => 'Идентификатор направления на цитологическое диагностическое исследование',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PersonEvn_id',
				'label' => 'Идентификатор состояния человека',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Server_id',
				'label' => 'Идентификатор сервера',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnCytologicProto_MaterialDT',
				'label' => 'Дата поступления материала',
				'rules' => 'trim|required',
				'type' => 'date'
			),
			array(
				'field' => 'EvnCytologicProto_setDate',
				'label' => 'Дата поступления материала',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnCytologicProto_setTime',
				'label' => 'Время поступления материала',
				'rules' => 'trim',
				'type' => 'time'
			),
			array(
				'field' => 'EvnCytologicProto_Num',
				'label' => 'Номер протокола',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'EvnCytologicProto_Ser',
				'label' => 'Серия протокола',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'PayType_id',
				'label' => 'Вид оплаты',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplex_id',
				'label' => 'Исследование',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnCytologicProto_CountUsluga',
				'label' => 'Количество',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnCytologicProto_CountGlass',
				'label' => 'Количество стекол',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnCytologicProto_CountFlacon',
				'label' => 'Количество флаконов',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnCytologicProto_IssueDT',
				'label' => 'Дата выдачи врачу',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'OnkoDiag_id',
				'label' => 'Цитологический диагноз',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnCytologicProto_MicroDescr',
				'label' => 'Микроскопическое описание',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Mkb10Code_id',
				'label' => 'Диагноз по МКБ-10',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnCytologicProto_Difficulty',
				'label' => 'Категория сложности',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnCytologicProto_Conclusion',
				'label' => 'Заключение',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'EvnCytologicProto_SurveyDT',
				'label' => 'Дата проведения исследования',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'LabMedPersonal_id',
				'label' => 'Лаборант',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Numerator_id',
				'label' => 'Нумератор',
				'rules' => '',
				'type' => 'id'
			),
			array('field' => 'DrugQualityCytologic_id', 'label' => 'Качество препарата', 'rules' => '', 'type' => 'id'),
			array('field' => 'ScreeningSmearType_id', 'label' => 'Тип мазка', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnCytologicProto_Cytogram', 'label' => 'Цитограмма без особенностей (для репродуктивного возраста)', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'EvnCytologicProto_Description', 'label' => 'Описание', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'CytologicMaterialPathology_id', 'label' => 'Цитограмма соответствует', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnCytologicProto_Degree', 'label' => 'Степень выраженности', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'EvnCytologicProto_Etiologic', 'label' => 'Этиологический фактор', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'EvnCytologicProto_OtherConcl', 'label' => 'Другие типы цитологических заключений', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'EvnCytologicProto_MoreClar', 'label' => 'Дополнительные уточнения', 'rules' => 'trim', 'type' => 'string'),
		),
		'loadEvnCytologicProtoGrid' => array(
			array(
				'field' => 'EvnType_id',
				'label' => 'Состояние протокола',
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
			/*
			array(
				'field' => 'didRangeStart',
				'label' => 'Дата иследования С',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'didRangeEnd',
				'label' => 'Дата иследования По',
				'rules' => 'trim',
				'type' => 'date'
			),
			*/
			array(
				'field' => 'setRangeStart',
				'label' => 'Дата поступления материала С',
				'rules' => 'trim|required',
				'type' => 'date'
			),
			array(
				'field' => 'setRangeEnd',
				'label' => 'Дата поступления материала По',
				'rules' => 'trim|required',
				'type' => 'date'
			),
			array(
				'field' => 'minAge',
				'label' => 'Возраст С',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'maxAge',
				'label' => 'Возраст По',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PT_Diag_Code_From',
				'label' => 'Диагноз С',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'PT_Diag_Code_To',
				'label' => 'Диагноз По',
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
		'loadEvnCytologicProtoEditForm' => array(
			array(
				'field' => 'EvnCytologicProto_id',
				'label' => 'Идентификатор протокола',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'deleteEvnCytologicProto' => array(
			array(
				'field' => 'EvnCytologicProto_id',
				'label' => 'Идентификатор протокола',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadCytologicMaterialPathologyCombo' => array(
			array('field' => 'CytologicMaterialPathology_id', 'label' => 'Цитологические признаки патологии материала', 'rules' => '', 'type' => 'id'),
			array('field' => 'CytologicMaterialPathology_pid', 'label' => 'Цитологические признаки патологии материала', 'rules' => '', 'type' => 'id')
		),
		'loadScreeningSmearTypeCombo' => array(
			array('field' => 'ScreeningSmearType_id', 'label' => 'Тип мазка', 'rules' => '', 'type' => 'id'),
			array('field' => 'ScreeningSmearType_pid', 'label' => 'Тип мазка', 'rules' => '', 'type' => 'id')
		),
	);


	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('EvnCytologicProto_model', 'dbmodel');
	}
	
	/**
	 * Получение серии номера протокола из нумератора
	 */
	function getEvnCytologicProtoNumber(){
		$data = getSessionParams();

		$result = $this->dbmodel->getEvnCytologicProtoNumber($data);		
		$this->ReturnData($result);

		return true;
	}
	
	/**
	 * Сохранение протокола цитологического дигностического исследования
	 */
	function saveEvnCytologicProto(){
		$data = $this->ProcessInputData('saveEvnCytologicProto', true);		
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->saveEvnCytologicProto($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении протокола цитологического дигностического исследования')->ReturnData();

		return true;
	}
	
	/**
	 * Получение списка протоколов цитологических дигностических исследований
	 */
	function loadEvnCytologicProtoGrid(){
		$data = $this->ProcessInputData('loadEvnCytologicProtoGrid', true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->loadEvnCytologicProtoGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}
	
	/**
	 * Получение данных для формы редактирования протокола цитологического дигностического исследования
	 */
	function loadEvnCytologicProtoEditForm(){
		$data = $this->ProcessInputData('loadEvnCytologicProtoEditForm', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadEvnCytologicProtoEditForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
	
	/**
	*  Удаление протокола цитологического дигностического исследования
	*  Входящие данные: $_POST['EvnCytologicProto_id']
	*/
	function deleteEvnCytologicProto() {
		$data = $this->ProcessInputData('deleteEvnCytologicProto', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->deleteEvnCytologicProto($data);
		$this->ProcessModelSave($response, true, 'При удалении протокола цитологического дигностического исследования возникли ошибки')->ReturnData();

		return true;
	}
	
	/**
	 * combo Цитограмма соответствует.
	 * поле с выпадающим списком из справочника «Цитологические признаки патологии материала, полученного при профилактическом гинекологическом осмотре, скрининге»
	 */
	function loadCytologicMaterialPathologyCombo()
	{
		$data = $this->ProcessInputData('loadCytologicMaterialPathologyCombo', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadCytologicMaterialPathologyCombo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	
	/**
	 * combo Тип мазка.
	 */
	function loadScreeningSmearTypeCombo()
	{
		$data = $this->ProcessInputData('loadScreeningSmearTypeCombo', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadScreeningSmearTypeCombo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
}
<?php
defined('BASEPATH') or die('No direct script access allowed');

class Cmp110_model extends SwPgModel {

	/**
	 * @param string $code Алиас атрибута
	 * @return array список значений атрибута
	 */
	public function getAttributeListDataByCode($code){
		$sql = "
			SELECT

                ca_child.Cmp110Attribute_id as \"Cmp110Attribute_id\",
                ca_child.Cmp110Attribute_pid as \"Cmp110Attribute_pid\",
                ca_child.Cmp110Attribute_code as \"Cmp110Attribute_code\",
                ca_child.Cmp110Attribute_insdt as \"Cmp110Attribute_insdt\",
                ca_child.Cmp110Attribute_upddt as \"Cmp110Attribute_upddt\",
                ca_child.Cmp110Attribute_name as \"Cmp110Attribute_name\",
                ca_child.pmUser_insid as \"pmUser_insid\",
                ca_child.pmUser_updid as \"pmUser_updid\",
				ca_parent.Cmp110Attribute_Code as \"ParentCode\"
			FROM
				Cmp110Attribute as ca_parent
				INNER JOIN Cmp110Attribute as ca_child  ON (ca_child.Cmp110Attribute_pid=ca_parent.Cmp110Attribute_id)
			WHERE
				ca_parent.Cmp110Attribute_Code=:Cmp110Attribute_Code
		";

		return $this->db->query($sql, array('Cmp110Attribute_Code' => $code))->result_array();

	}

	/**
	 * @param string $code Алиас атрибута
	 * @return array список значений атрибута
	 */
	public function getAttributeListDataById($id){
		$sql = "
			SELECT
				ca_child.Cmp110Attribute_id as \"Cmp110Attribute_id\",
                ca_child.Cmp110Attribute_pid as \"Cmp110Attribute_pid\",
                ca_child.Cmp110Attribute_code as \"Cmp110Attribute_code\",
                ca_child.Cmp110Attribute_insdt as \"Cmp110Attribute_insdt\",
                ca_child.Cmp110Attribute_upddt as \"Cmp110Attribute_upddt\",
                ca_child.Cmp110Attribute_name as \"Cmp110Attribute_name\",
                ca_child.pmUser_insid as \"pmUser_insid\",
                ca_child.pmUser_updid as \"pmUser_updid\",
				ca_parent.Cmp110Attribute_Code as \"ParentCode\"
			FROM
				Cmp110Attribute as ca_parent 
				INNER JOIN Cmp110Attribute as ca_child  ON (ca_child.Cmp110Attribute_pid=ca_parent.Cmp110Attribute_id)
			WHERE
				ca_parent.Cmp110Attribute_id=:Cmp110Attribute_id
		";

		return $this->db->query($sql, array('Cmp110Attribute_id' => $id))->result_array();
	}

	/**
	 * Формирование формы
	 * 
	 * @return array
	 */
	public function buildForm(){
		return array(
			'success' => true,
			'metaData' => array(
				'formConfig' => $this->getFormConfig(),
				'fields' => $this->getFormFields(),
			),
			'data' => array(
			// values
			)
		);
	}

	/**
	 * Возвращает конфигурацию Ext.form.FormPanel
	 */
	protected function getFormConfig(){
		return array(
			'labelWidth' => 220,
			'labelAlign' => 'left',
		);
	}

	/**
	 * @return array список полей формы
	 */
	protected function getFormFields(){
		return array(
			// accessType ?
			// AgeType_id2 ?
			// SocStatusNick ?
			// ARMType ?
			// Person_id
			// Cmp110Field::initFromName('CmpCallCard_id')->t(),
			// self::getTabPanel(),
			// @todo delete line down
			Cmp110Field::init('Cmp110_ResultDepartureId')->initItems()->t(),
		);
	}

	/**
	 * @return array набор вкладок с полями
	 */
	protected static function getTabPanel(){
		return array(
			'xtype' => 'tabpanel',
			'activeTab' => 0,
			'deferredRender' => false, // Необходимо загружать все вкладки разом
			// @todo Скролл внутри табов
			'items' => array(
				self::getTabBase(),
				self::getTabReason(),
				self::getTabComplaints(),
				self::getTabDiagnosis(),
				self::getTabManipulation(),
				self::getTabResult(),
			)
		);
	}

	/**
	 * @return array вкладка base
	 */
	protected static function getTabBase(){
		return Cmp110Tab::initFromName('base')->appendItems(array(
				self::getBlockCallData(),
				self::getBlockTime(),
				self::getBlockAddress(),
				self::getBlockPatient(),
			))->t();
	}

	/**
	 * @return array блок call_data
	 */
	protected static function getBlockCallData(){
		return Cmp110Block::initFromName('call_data')->appendItems(array(
				Cmp110Field::init('Cmp110_NumDay')->t(),
				Cmp110Field::init('Cmp110_NumYear')->t(),
				// FeldsherIdField(panelNumber),
				// FeldsherAcceptCallField(panelNumber),
				Cmp110Field::init('Cmp110_StationNum')->t(),
				// LpuBuilding(panelNumber),
				Cmp110Field::init('Cmp110_EmergencyTeamNum')->t(),
				// EmergencyTeamIdField(panelNumber),
				// EmergencyTeamSpec
				// LpuSection_id
				// MedStaffFact_id
				// MedPersonal_id
				// swpaytypecombo
				// BrigSelectBtn
			))->t();
	}

	/**
	 * @return array блок time
	 */
	protected static function getBlockTime(){
		return Cmp110Block::initFromName('time')->appendItems(array(
				Cmp110Field::init('Cmp110_TimeAccept')->t(),
				Cmp110Field::init('Cmp110_TimeTransfer')->t(),
				Cmp110Field::init('Cmp110_TimeMove')->t(),
				Cmp110Field::init('Cmp110_TimeArrive')->t(),
				Cmp110Field::init('Cmp110_TimeTransportation')->t(),
				Cmp110Field::init('Cmp110_TimeDelivery')->t(),
				Cmp110Field::init('Cmp110_TimeEnd')->t(),
				Cmp110Field::init('Cmp110_TimeBack')->t(),
				// Затраченное время
			))->t();
	}

	/**
	 * @return array блок address
	 */
	protected static function getBlockAddress(){
		return Cmp110Block::initFromName('address')->appendItems(array(
				// KLAreaStat_idEdit
				// KLRgn_id
				// Area_id
				// City_id
				// Town_id
				// Street_id
				Cmp110Field::init('Cmp110_AddressHouse')->t(),
				Cmp110Field::init('Cmp110_AddressBuilding')->t(),
				Cmp110Field::init('Cmp110_AddressFlat')->t(),
				Cmp110Field::init('Cmp110_AddressRoom')->t(),
				Cmp110Field::init('Cmp110_AddressEntrance')->t(),
				Cmp110Field::init('Cmp110_AddressFloor')->t(),
				Cmp110Field::init('Cmp110_AddressEntranceCode')->t(),
			))->t();
	}

	/**
	 * @return array блок patient
	 */
	protected static function getBlockPatient(){
		return Cmp110Block::initFromName('patient')->appendItems(array(
				// CCCSEF_PersonSearchBtn
				// CCCSEF_PersonResetBtn
				// CCCSEF_PersonUnknownBtn
				Cmp110Field::init('Cmp110_PatientLastname')->t(),
				Cmp110Field::init('Cmp110_PatientFirstname')->t(),
				Cmp110Field::init('Cmp110_PatientSurname')->t(),
				Cmp110Field::init('Cmp110_PatientPolicySeries')->t(),
				Cmp110Field::init('Cmp110_PatientPolicyNum')->t(),
				Cmp110Field::init('Cmp110_PatientPolicyUnified')->t(),
				Cmp110Field::init('Cmp110_PatientAge')->t(),
				// AgeType_id
				// Sex_id
				Cmp110Field::init('Cmp110_PatientDocumentType')->t(),
				Cmp110Field::init('Cmp110_PatientDocumentSeries')->t(),
				Cmp110Field::init('Cmp110_PatientDocumentNum')->t(),
				Cmp110Field::init('Cmp110_PatientWork')->t(),
				// _CMPCLOSE_ComboValue_141 - Место регистрации больного
				// _CMPCLOSE_ComboValue_153 - Социальное положение больного
				// CmpCallerType_id
				Cmp110Field::init('Cmp110_CallerPhone')->t(),
				// FeldsherAccept
				// FeldsherTrans
			))->t();
	}

	/**
	 * @return array вкладка reason
	 */
	protected static function getTabReason(){
		return Cmp110Tab::initFromName('reason')->appendItems(array(
				self::getBlockReason(),
			))->t();
	}

	/**
	 * @return array блок reason
	 */
	protected static function getBlockReason(){
		return Cmp110Block::initFromName('reason')->appendItems(array(
				Cmp110Field::init('tmp_Reason_id')->t(),
				Cmp110Field::init('tmp_CallType_id')->t(),
				Cmp110Field::init('tmp_CallTeamPlace_id')->t(),
				Cmp110Field::init('tmp_Delay_id')->t(),
				Cmp110Field::init('tmp_TeamComplect_id')->t(),
				Cmp110Field::init('tmp_CallPlace_id')->t(),
				Cmp110Field::init('tmp_AccidentReason_id')->t(),
				Cmp110Field::init('tmp_Trauma_id')->t(),
				Cmp110Field::init('tmp_isAlco')->t(),
			))->t();
	}

	/**
	 * @return array вкладка complaints
	 */
	protected static function getTabComplaints(){
		return Cmp110Tab::initFromName('complaints')->appendItems(array(
				self::getBlockComplaints(),
			))->t();
	}

	/**
	 * @return array блок complaints
	 */
	protected static function getBlockComplaints(){
		return Cmp110Block::initFromName('complaints')->appendItems(array(
				Cmp110Field::init('Cmp110_Complaints')->t(),
				Cmp110Field::init('Cmp110_DiseaseOnsetDate')->t(),
				Cmp110Field::init('Cmp110_Anamnesis')->t(),
			))->t();
	}

	/**
	 * @return array вкладка diagnosis
	 */
	protected static function getTabDiagnosis(){
		return Cmp110Tab::initFromName('diagnosis')->appendItems(array(
				self::getBlockDiagnosis(),
			))->t();
	}

	/**
	 * @return array блок diagnosis
	 */
	protected static function getBlockDiagnosis(){
		return Cmp110Block::initFromName('diagnosis')->appendItems(array(
				Cmp110Field::init('tmp_Diag_id')->t(),
				Cmp110Field::init('Cmp110_DiagnosisSpecify')->t(),
				Cmp110Field::init('tmp_Complicat_id')->t(),
				Cmp110Field::init('tmp_ComplicatEf_id')->t(),
			))->t();
	}

	/**
	 * @return array вкладка manipulation
	 */
	protected static function getTabManipulation(){
		return Cmp110Tab::initFromName('manipulation')->appendItems(array(
				self::getBlockManipulation(),
			))->t();
	}

	/**
	 * @return array блок manipulation
	 */
	protected static function getBlockManipulation(){
		return Cmp110Block::initFromName('manipulation')->appendItems(array(
				Cmp110Field::init('Cmp110_HelpPlace')->t(),
				Cmp110Field::init('Cmp110_HelpAuto')->t(),
			))->t();
	}

	/**
	 * @return array вкладка result
	 */
	protected static function getTabResult(){
		return Cmp110Tab::initFromName('result')->appendItems(array(
				self::getBlockResult(),
			))->t();
	}

	/**
	 * @return array блок result
	 */
	protected static function getBlockResult(){
		return Cmp110Block::initFromName('result')->appendItems(array(
				Cmp110Field::init('tmp_isOtkazHosp')->t(),
				Cmp110Field::init('tmp_isOtkazSign')->t(),
				Cmp110Field::init('tmp_OtkazSignWhy')->t(),
				Cmp110Field::init('tmp_ResultId')->t(),
				Cmp110Field::init('tmp_Patient_id')->t(),
				Cmp110Field::init('tmp_CMPCLOSE_ComboValue_111')->t(),
				Cmp110Field::init('tmp_TransToAuto_id')->t(),
				Cmp110Field::init('Cmp110_ResultDepartureId')->initItems()->t(),
				Cmp110Field::init('Cmp110_ResultOdometer')->t(),
				Cmp110Field::init('Cmp110_ResultNotes')->t(),
			))->t();
	}
}

/**
 * Cmp110Component базовый класс для всех компонентов формы
 * 
 * @param $name
 * @param $template
 */
class Cmp110Component {

	/**
	 * @var string Имя компонента
	 */
	public $name;

	/**
	 * @var array Шаблон компонента для ExtJS 4
	 */
	public $template = array();

	/**
	 * @var string Имя класса содержащего список конфигурации объектов.
	 * Этот параметр должен быть переопределен если используется стандартная
	 * реализация метода [[Cmp110Component::initFromName]]
	 */
	public $list_class;

	/**
	 * @var array Параметр Ext.panel.Panel применяемый ко всем дочерним элементам
	 */
	public $defaults = array();

	/**
	 * Устанавливает имя компонента
	 * 
	 * @param string $name Имя компонента
	 */
	public function setName($name){
		$this->name = $name;

		return $this;
	}

	/**
	 * @return string имя компонента
	 */
	public function getName(){
		return $this->template;
	}

	/**
	 * Устанавливает шаблон поля
	 * 
	 * @param array $template Шаблон поля для ExtJS 4
	 */
	public function setTemplate($template, $apply_defaults = true){
		$this->template = $template;

		if ($apply_defaults && !empty($this->defaults)) {
			$this->template['defaults'] = $this->defaults;
		}

		return $this;
	}

	/**
	 * @return array шаблон компонента
	 */
	public function getTemplate(){
		return $this->template;
	}

	/**
	 * Алиас для [[Cmp110Component::getTemplate()]]
	 */
	public function t(){
		return $this->getTemplate();
	}

	/**
	 * Инициализация компонента по имени
	 * 
	 * @param string $name Имя вкладки
	 * @return \Cmp110Tab
	 */
	public static function initFromName($name){
		$item = new static;

		$list_class = $item->list_class;
		
		if (isset($list_class::${$name})) {
			$tempate = $list_class::${$name};
		} elseif (method_exists($list_class, $name)) {
			$tempate = $list_class::$name();
		} else {
			throw new Exception('Не задан шаблон для указанного элемента "'.$name.'".');
		}
		
		$item->setTemplate($tempate)
			->setName($name);

		return $item;
	}

	/**
	 * Добавляет дочерние элементы к компоненту
	 * При необходимости можно переопределить этот метод
	 * 
	 * @param array $items Список элементов
	 * @return \Cmp110Block
	 */
	public function appendItems($items){
		if (!isset($this->template['items'])) {
			$this->template['items'] = $items;
		} else {
			$this->template['items'] = array_merge($this->template['items'], $items);
		}

		return $this;
	}

	/**
	 * Установка параметра 'defaults' в шаблоне
	 * 
	 * @param array $defaults Ext.container.AbstractContainer.defaults
	 * @return \Cmp110Block
	 */
	public function applyTemplateDefaults($defaults = array()){
		if (empty($this->template['defaults'])) {
			$this->template['defaults'] = array();
		}

		$this->template['defaults'] += $defaults;

		return $this;
	}
}

/**
 * Cmp110Tab базовый класс представляющий основной функционал необходимый для объектов вкладок
 */
class Cmp110Tab extends Cmp110Component {

	/**
	 * @inheritdoc
	 */
	public $list_class = 'Cmp110TabList';

}

/**
 * Cmp110TabList базовый класс со списком шаблонов вкладок
 */
class Cmp110TabList {

	/**
	 * @var array $base
	 */
	public static $base = array(
		'title' => 'Основные данные',
	);

	/**
	 * @var array $reason
	 */
	public static $reason = array(
		'title' => 'Повод к вызову',
	);

	/**
	 * @var array $complaints
	 */
	public static $complaints = array(
		'title' => 'Жалобы и объективные данные',
	);

	/**
	 * @var array $diagnosis
	 */
	public static $diagnosis = array(
		'title' => 'Диагноз',
	);

	/**
	 * @var array $manipulation
	 */
	public static $manipulation = array(
		'title' => 'Манипуляции',
	);

	/**
	 * @var array $result
	 */
	public static $result = array(
		'title' => 'Результат',
	);

}

/**
 * Cmp110Block базовый класс представляющий основной функционал необходимый для объектов блоков
 */
class Cmp110Block extends Cmp110Component {

	/**
	 * @inheritdoc
	 */
	public $list_class = 'Cmp110BlockList';

	/**
	 * @inheritdoc
	 */
	public $defaults = array(
		'autoHeight' => true,
		'border' => false,
		'layout' => 'form',
		'labelWidth' => 220,
		'labelAlign' => 'left',
	);

}

/**
 * Cmp110BlockList базовый класс со списком шаблонов блоков
 * 
 * По сути является xtype: panel, т.к. это дефолтный тип у большинства элементов.
 */
class Cmp110BlockList {

	/**
	 * @var array $call_data
	 */
	public static $call_data = array(
		'title' => 'Данные вызова',
	);

	/**
	 * @var array $time
	 */
	public static $time = array(
		'title' => 'Время вызова',
	);

	/**
	 * @var array $address
	 */
	public static $address = array(
		'title' => 'Адрес вызова',
	);

	/**
	 * @var array $patient
	 */
	public static $patient = array(
		'title' => 'Сведения о больном',
	);

	/**
	 * @var array $reason
	 */
	public static $reason = array(
		'title' => 'Повод к вызову',
	);

	/**
	 * @var array $complaints
	 */
	public static $complaints = array(
		'title' => 'Жалобы',
	);

	/**
	 * @var array $ext_evidence
	 */
	public static $ext_evidence = array(
		'title' => 'Объективные данные',
	);

	/**
	 * @var array $diagnosis
	 */
	public static $diagnosis = array(
		'title' => 'Диагноз',
	);

	/**
	 * @var array $manipulation
	 */
	public static $manipulation = array(
		'title' => 'Манипуляции',
	);

	/**
	 * @var array $result
	 */
	public static $result = array(
		'title' => 'Результат',
	);

}

/**
 * Cmp110Field базовый класс представляющий основной функционал необходимый для объектов полей
 */
class Cmp110Field extends Cmp110Component {

	/**
	 * @inheritdoc
	 */
	public $list_class = 'Cmp110FieldList';

	/**
	 * @var Cmp110_model 
	 */
	public static $model;

	/**
	 * Алиас для [[Cmp110Component::initFromName()]]
	 */
	public static function init($name){
		return static::initFromName($name);
	}

	/**
	 * Инициализация дочерних элементов
	 * 
	 * @param Cmp110Field $item Объект Cmp110Field
	 */
	public function initItems(){
		if (empty($this->template['items'])) {
			// Получаем ссылку на модель контроллера Cmp110
			$CI = & get_instance();
			$model = $CI->model;
			// Получаем список значений
			$list = $model->getAttributeListDataByCode($this->template['name']);
			if (empty($list)) {
				throw new Exception('Не указан список значений элемента Cmp110Field "' . $this->template['name'] . '".');
			}
			$this->template['items'] = array();
			foreach ($list as $v) {
				$this->template['items'][] = array(
					'boxLabel' => $v['Cmp110Attribute_Name'],
					'name' => $this->template['name'],
					'inputValue' => $v['Cmp110Attribute_id']
				);
			}
		}
		return $this;
	}
}

/**
 * Cmp110FieldList базовый класс со списком шаблонов полей
 */
class Cmp110FieldList {

	/**
	 * @var array CmpCallCard_id
	 */
	public static $CmpCallCard_id = array(
		'xtype' => 'hidden',
		'name' => 'CmpCallCard_id'
	);

	/**
	 * @var array Cmp110_NumDay
	 */
	public static $Cmp110_NumDay = array(
		'xtype' => 'numberfield',
		'name' => 'Cmp110_NumDay',
		'fieldLabel' => 'Номер вызова за день',
		'vtype' => 'num',
	);

	/**
	 * @var array Cmp110_NumYear
	 */
	public static $Cmp110_NumYear = array(
		'xtype' => 'numberfield',
		'name' => 'Cmp110_NumYear',
		'fieldLabel' => 'Номер вызова за год',
		'vtype' => 'num',
	);

	/**
	 * @var array Cmp110_StationNum
	 */
	public static $Cmp110_StationNum = array(
		'xtype' => 'numberfield',
		'name' => 'Cmp110_StationNum',
		'fieldLabel' => 'Номер станции (подстанции), отделения',
		'vtype' => 'num',
	);

	/**
	 * @var array LpuBuilding_id
	 */
	public static $LpuBuilding_id = array(
		// @todo Добавить новый комбобокс xtype:LpuBuilding_id (Ext2 sw.Promed.SmpUnits)
		'xtype' => 'swLpuBuilding',
		'fieldLabel' => 'Станция (подстанция), отделения'
	);

	/**
	 * @var array Cmp110_EmergencyTeamNum
	 */
	public static $Cmp110_EmergencyTeamNum = array(
		'xtype' => 'numberfield',
		'name' => 'Cmp110_EmergencyTeamNum',
		'fieldLabel' => 'Номер бригады скорой помощи',
		'vtype' => 'num',
	);

	/**
	 * @var array Cmp110_TimeAccept
	 */
	public static $Cmp110_TimeAccept = array(
		'xtype' => 'datetimefield',
		'name' => 'Cmp110_TimeAccept',
		'fieldLabel' => 'Прием вызова'
	);

	/**
	 * @var array Cmp110_TimeTransfer
	 */
	public static $Cmp110_TimeTransfer = array(
		'xtype' => 'datetimefield',
		'name' => 'Cmp110_TimeTransfer',
		'fieldLabel' => 'Передача вызова бригаде СМП'
	);

	/**
	 * @var array Cmp110_TimeMove
	 */
	public static $Cmp110_TimeMove = array(
		'xtype' => 'datetimefield',
		'name' => 'Cmp110_TimeMove',
		'fieldLabel' => 'Выезд на вызов'
	);

	/**
	 * @var array Cmp110_TimeArrive
	 */
	public static $Cmp110_TimeArrive = array(
		'xtype' => 'datetimefield',
		'name' => 'Cmp110_TimeArrive',
		'fieldLabel' => 'Прибытие на место вызова'
	);

	/**
	 * @var array Cmp110_TimeTransportation
	 */
	public static $Cmp110_TimeTransportation = array(
		'xtype' => 'datetimefield',
		'name' => 'Cmp110_TimeTransportation',
		'fieldLabel' => 'Начало транспортировки больного'
	);

	/**
	 * @var array Cmp110_TimeDelivery
	 */
	public static $Cmp110_TimeDelivery = array(
		'xtype' => 'datetimefield',
		'name' => 'Cmp110_TimeDelivery',
		'fieldLabel' => 'Прибытие в медицинскую организацию'
	);

	/**
	 * @var array Cmp110_TimeEnd
	 */
	public static $Cmp110_TimeEnd = array(
		'xtype' => 'datetimefield',
		'name' => 'Cmp110_TimeEnd',
		'fieldLabel' => 'Окончание вызова'
	);

	/**
	 * @var array Cmp110_TimeBack
	 */
	public static $Cmp110_TimeBack = array(
		'xtype' => 'datetimefield',
		'name' => 'Cmp110_TimeBack',
		'fieldLabel' => 'Возвращение на станцию (подстанцию), отделение'
	);

	/**
	 * @var array Cmp110_AddressHouse
	 */
	public static $Cmp110_AddressHouse = array(
		'xtype' => 'textfield',
		'name' => 'Cmp110_AddressHouse',
		'fieldLabel' => 'Дом'
	);

	/**
	 * @var array Cmp110_AddressBuilding
	 */
	public static $Cmp110_AddressBuilding = array(
		'xtype' => 'textfield',
		'name' => 'Cmp110_AddressBuilding',
		'fieldLabel' => 'Корпус'
	);

	/**
	 * @var array Cmp110_AddressFlat
	 */
	public static $Cmp110_AddressFlat = array(
		'xtype' => 'textfield',
		'name' => 'Cmp110_AddressFlat',
		'fieldLabel' => 'Квартира'
	);

	/**
	 * @var array Cmp110_AddressRoom
	 */
	public static $Cmp110_AddressRoom = array(
		'xtype' => 'textfield',
		'name' => 'Cmp110_AddressRoom',
		'fieldLabel' => 'Комната'
	);

	/**
	 * @var array Cmp110_AddressEntrance
	 */
	public static $Cmp110_AddressEntrance = array(
		'xtype' => 'textfield',
		'name' => 'Cmp110_AddressEntrance',
		'fieldLabel' => 'Подъезд'
	);

	/**
	 * @var array Cmp110_AddressFloor
	 */
	public static $Cmp110_AddressFloor = array(
		'xtype' => 'textfield',
		'name' => 'Cmp110_AddressFloor',
		'fieldLabel' => 'Этаж'
	);

	/**
	 * @var array Cmp110_AddressEntranceCode
	 */
	public static $Cmp110_AddressEntranceCode = array(
		'xtype' => 'textfield',
		'name' => 'Cmp110_AddressEntranceCode',
		'fieldLabel' => 'Код домофона (замка) в подъезде'
	);

	/**
	 * @var array Cmp110_PatientLastname
	 */
	public static $Cmp110_PatientLastname = array(
		'xtype' => 'textfield',
		'name' => 'Cmp110_PatientLastname',
		'fieldLabel' => 'Фамилия'
	);

	/**
	 * @var array Cmp110_PatientFirstname
	 */
	public static $Cmp110_PatientFirstname = array(
		'xtype' => 'textfield',
		'name' => 'Cmp110_PatientFirstname',
		'fieldLabel' => 'Имя'
	);

	/**
	 * @var array Cmp110_PatientSurname
	 */
	public static $Cmp110_PatientSurname = array(
		'xtype' => 'textfield',
		'name' => 'Cmp110_PatientSurname',
		'fieldLabel' => 'Отчество'
	);

	/**
	 * @var array Cmp110_PatientPolicySeries
	 */
	public static $Cmp110_PatientPolicySeries = array(
		'xtype' => 'textfield',
		'name' => 'Cmp110_PatientPolicySeries',
		'fieldLabel' => 'Серия полиса'
	);

	/**
	 * @var array Cmp110_PatientPolicyNum
	 */
	public static $Cmp110_PatientPolicyNum = array(
		'xtype' => 'textfield',
		'name' => 'Cmp110_PatientPolicyNum',
		'fieldLabel' => 'Номер полиса'
	);

	/**
	 * @var array Cmp110_PolicyUnified
	 */
	public static $Cmp110_PatientPolicyUnified = array(
		'xtype' => 'textfield',
		'name' => 'Cmp110_PatientPolicyUnified',
		'fieldLabel' => 'Единый номер полиса'
	);

	/**
	 * @var array Cmp110_PatientAge
	 */
	public static $Cmp110_PatientAge = array(
		'xtype' => 'numberfield',
		'name' => 'Cmp110_PatientAge',
		'fieldLabel' => 'Возвраст',
		'vtype' => 'num',
	);

	/**
	 * @var array Cmp110_PatientWork
	 */
	public static $Cmp110_PatientWork = array(
		'xtype' => 'textfield',
		'name' => 'Cmp110_PatientWork',
		'fieldLabel' => 'Место работы',
	);

	/**
	 * @var array Cmp110_PatientDocumentType
	 */
	public static $Cmp110_PatientDocumentType = array(
		'xtype' => 'textfield',
		'name' => 'Cmp110_PatientDocumentType',
		'fieldLabel' => 'Тип документа, удостоверящего личность',
	);

	/**
	 * @var array Cmp110_PatientDocumentSeries
	 */
	public static $Cmp110_PatientDocumentSeries = array(
		'xtype' => 'textfield',
		'name' => 'Cmp110_PatientDocumentSeries',
		'fieldLabel' => 'Серия',
	);

	/**
	 * @var array Cmp110_PatientDocumentNum
	 */
	public static $Cmp110_PatientDocumentNum = array(
		'xtype' => 'textfield',
		'name' => 'Cmp110_PatientDocumentNum',
		'fieldLabel' => 'Номер',
	);

	/**
	 * @var array Cmp110_CallerPhone
	 */
	public static $Cmp110_CallerPhone = array(
		'xtype' => 'textfield',
		'name' => 'Cmp110_CallerPhone',
		'fieldLabel' => 'Номер телефона звонящего',
		// @todo Маска ввода номера телефона
		// @todo vtype для номера телефона
	);

	/**
	 * @var array tmp_Reason_id
	 */
	public static $tmp_Reason_id = array(
		'xtype' => 'textfield',
		'fieldLabel' => 'Повод',
	);

	/**
	 * @var array tmp_CallType_id
	 */
	public static $tmp_CallType_id = array(
		'xtype' => 'textfield',
		'fieldLabel' => 'Вызов',
	);

	/**
	 * @var array tmp_CallTeamPlace_id
	 */
	public static $tmp_CallTeamPlace_id = array(
		'xtype' => 'textfield',
		'fieldLabel' => 'Место получения вызова бригадой скорой медицинской помощи',
	);

	/**
	 * @var array tmp_Delay_id
	 */
	public static $tmp_Delay_id = array(
		'xtype' => 'textfield',
		'fieldLabel' => 'Причины выезда с опозданием',
	);

	/**
	 * @var array tmp_TeamComplect_id
	 */
	public static $tmp_TeamComplect_id = array(
		'xtype' => 'textfield',
		'fieldLabel' => 'Состав бригады скорой медицинской помощи',
	);

	/**
	 * @var array tmp_CallPlace_id
	 */
	public static $tmp_CallPlace_id = array(
		'xtype' => 'textfield',
		'fieldLabel' => 'Тип места вызова',
	);

	/**
	 * @var array tmp_AccidentReason_id
	 */
	public static $tmp_AccidentReason_id = array(
		'xtype' => 'textfield',
		'fieldLabel' => 'Причина несчастного случая',
	);

	/**
	 * @var array tmp_Trauma_id
	 */
	public static $tmp_Trauma_id = array(
		'xtype' => 'textfield',
		'fieldLabel' => 'Травма',
	);

	/**
	 * @var array tmp_isAlco
	 */
	public static $tmp_isAlco = array(
		'xtype' => 'textfield',
		'fieldLabel' => 'Наличие клиники опьянения',
	);

	/**
	 * @var array Cmp110_Complaints
	 */
	public static $Cmp110_Complaints = array(
		'xtype' => 'textarea',
		'name' => 'Cmp110_Complaints',
		'fieldLabel' => 'Жалобы'
	);

	/**
	 * @var array Cmp110_DiseaseOnsetDate
	 */
	public static $Cmp110_DiseaseOnsetDate = array(
		'xtype' => 'datefield',
		'name' => 'Cmp110_DiseaseOnsetDate',
		'fieldLabel' => 'Дата начала заболевания',
	);

	/**
	 * @var array Cmp110_Anamnesis
	 */
	public static $Cmp110_Anamnesis = array(
		'xtype' => 'textarea',
		'name' => 'Cmp110_Anamnesis',
		'fieldLabel' => 'Анамнез'
	);

	/**
	 * @var array tmp_Diag_id
	 */
	public static $tmp_Diag_id = array(
		'xtype' => 'textfield',
		'fieldLabel' => 'Диагноз'
	);

	/**
	 * @var array Cmp110_DiagnosisSpecify
	 */
	public static $Cmp110_DiagnosisSpecify = array(
		'xtype' => 'textfield',
		'name' => 'Cmp110_DiagnosisSpecify',
		'fieldLabel' => 'Уточнение диагноза'
	);

	/**
	 * @var array tmp_Complicat_id
	 */
	public static $tmp_Complicat_id = array(
		'xtype' => 'textfield',
		'fieldLabel' => 'Осложнения'
	);

	/**
	 * @var array tmp_ComplicatEf_id
	 */
	public static $tmp_ComplicatEf_id = array(
		'xtype' => 'textfield',
		'fieldLabel' => 'Эффективность мероприятий при осложнении'
	);

	/**
	 * @var array Cmp110_HelpPlace
	 */
	public static $Cmp110_HelpPlace = array(
		'xtype' => 'textarea',
		'name' => 'Cmp110_HelpPlace',
		'fieldLabel' => 'Оказанная помощь на месте вызова'
	);

	/**
	 * @var array Cmp110_HelpAuto
	 */
	public static $Cmp110_HelpAuto = array(
		'xtype' => 'textarea',
		'name' => 'Cmp110_HelpAuto',
		'fieldLabel' => 'Оказанная помощь в автомобиле скорой медицинской помощи'
	);

	/**
	 * @var array tmp_isOtkazHosp
	 */
	public static $tmp_isOtkazHosp = array(
		'xtype' => 'textfield',
		'fieldLabel' => 'Отказ от транспортировки для госпитализации в стационар.'
	);

	/**
	 * @var array tmp_isOtkazSign
	 */
	public static $tmp_isOtkazSign = array(
		'xtype' => 'textfield',
		'fieldLabel' => 'Отказ от подписи'
	);

	/**
	 * @var array tmp_OtkazSignWhy
	 */
	public static $tmp_OtkazSignWhy = array(
		'xtype' => 'textfield',
		'fieldLabel' => 'Причина отказа от подписи'
	);

	/**
	 * @var array tmp_ResultId
	 */
	public static $tmp_ResultId = array(
		'xtype' => 'textfield',
		'fieldLabel' => 'Результат оказания скорой медицинской помощи'
	);

	/**
	 * @var array tmp_Patient_id
	 */
	public static $tmp_Patient_id = array(
		'xtype' => 'textfield',
		'fieldLabel' => 'Больной'
	);

	/**
	 * @var array tmp_CMPCLOSE_ComboValue_111
	 */
	public static $tmp_CMPCLOSE_ComboValue_111 = array(
		'xtype' => 'textfield',
		'fieldLabel' => 'Выберите ЛПУ'
	);

	/**
	 * @var array tmp_TransToAuto_id
	 */
	public static $tmp_TransToAuto_id = array(
		'xtype' => 'textfield',
		'fieldLabel' => 'Способ доставки больного в автомобиль скорой медицинской помощи'
	);
	
	/**
	 * @return array Cmp110_ResultDepartureId
	 */
	public static function Cmp110_ResultDepartureId(){
		$config = array(
			'xtype' => 'radiogroup',
			'name' => 'Cmp110_ResultDepartureId',
			'fieldLabel' => 'Результат выезда',
			'columns' => 1,
			'vertical' => true,
			'items' => array()
		);
		
		// Получаем ссылку на модель контроллера Cmp110
		$CI = & get_instance();
		$model = $CI->model;
		// Получаем список значений
		$list = $model->getAttributeListDataByCode($config['name']);
		foreach ($list as $k => $v) {
			$item = array(
				'boxLabel' => $v['Cmp110Attribute_Name'],
				'name' => $config['name'],
				'inputValue' => $v['Cmp110Attribute_id']
			);
			$child = $model->getAttributeListDataById($v['Cmp110Attribute_id']);
			if ($child) {
				$item = array(
					'xtype' => 'container',
					'layout' => 'vbox',
					'items' => array(
						array_merge($item, array('xtype' => 'radio'))
					)
				);
				// $item['items'][] = array('xtype' => 'splitter', 'performCollapse' => false);
				foreach ($child as $c) {
					$item['items'][] = array(
						'xtype' => 'textfield',
						'fieldLabel' => $c['Cmp110Attribute_Name'],
					);
				}
			}
			
			$config['items'][] = $item;
		}
		
		return $config;
	}

	/**
	 * @var array Cmp110_ResultOdometer
	 */
	public static $Cmp110_ResultOdometer = array(
		'xtype' => 'numberfield',
		'name' => 'Cmp110_ResultOdometer',
		'fieldLabel' => 'Километраж',
		'vtype' => 'num'
	);

	/**
	 * @var array Cmp110_ResultNotes
	 */
	public static $Cmp110_ResultNotes = array(
		'xtype' => 'textarea',
		'name' => 'Cmp110_ResultNotes',
		'fieldLabel' => 'Примечания',
	);

}

/**
 * Cmp110Panel базовый класс представляющий основной функционал необходимый для объектов Ext.panel.Panel
 */
class Cmp110Panel extends Cmp110Component {

	public $template = array(
		'xtype' => 'panel'
	);

	/**
	 * Инициализация Ext.panel.Panel
	 * 
	 * @param string $title Заголовок
	 * @return \Cmp110Panel
	 */
	public static function init($title){
		$item = new self;
		$item->template['title'] = $title;
		return $item;
	}
}

/**
 * Cmp110Fieldset базовый класс представляющий основной функционал необходимый для объектов Ext.form.FieldSet
 */
class Cmp110Fieldset extends Cmp110Component {

	public $template = array(
		'xtype' => 'fieldset'
	);

	/**
	 * Инициализация Ext.form.FieldSet
	 * 
	 * @param string $title Заголовок
	 * @return \Cmp110Fieldset
	 */
	public static function init($title){
		$item = new self;
		$item->template['title'] = $title;
		return $item;
	}
}

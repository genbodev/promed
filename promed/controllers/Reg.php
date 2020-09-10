<?php

defined('BASEPATH') or die('No direct script access allowed');

/**
 * Reg - контроллер для общих операций электронной регистратуры
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Reg
 * @access       public
 * @copyright    Copyright (c) 2012 Swan Ltd.
 * @author       Petukhov Ivan aka Lich (ethereallich@gmail.com)
 * @version      19.03.2012
 *
 * @property Reg_model rmodel
 */
class Reg extends swController {

	public $inputRules = array();
	var $recTypes = Array(
		1 => array(
			'tip' => "",
			'fontcolor' => ""
		),
		2 => array(
			'tip' => " ext:qtip=\"Прием к врачу только через регистратуру ЛПУ\" ",
			'fontcolor' => "green"
		),
		3 => array(
			'tip' => "  ext:qtip=\"Прием к врачу только по &quot;живой очереди&quot;\" ",
			'fontcolor' => "gray"
		),
		4 => array(
			'tip' => " ext:qtip=\"Прием пациентов из других районов'\" ",
			'fontcolor' => "red"
		),
		5 => array(
			'tip' => " ext:qtip=\"Врачи ведущие только платный прием'\" ",
			'fontcolor' => "#aa66aa"
		),
		8 => array(
			'tip' => " ext:qtip=\"Через ЦЗ, регистратуру ЛПУ, без интернета'\" ",
			'fontcolor' => "green"
		),
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();

		$this->inputRules = array(
			'GetFilterTree' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор ЛПУ',
					'rules' => '',
					'type' => 'id',
					'session_value' => 'lpu_id'
				),
				array(
					'field' => 'LpuUnit_id',
					'label' => 'Идентификатор ЛПУ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'object',
					'label' => 'Объект',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'node',
					'label' => 'Идентификатор узла',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'object_id',
					'label' => 'Идентификатор объекта',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'filterByArm',
					'label' => 'Фильтр по АРМ, через которое была открыта форма',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'UserLpuSection_id',
					'label' => 'Отделение пользователя',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'UserMedStaffFact_id',
					'label' => 'Место работы пользователя',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedServiceType_SysNick',
					'label' => 'Тип службы',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedServiceOnly',
					'label' => 'Флаг загрузки служб',
					'rules' => '',
					'type' => 'boolean'
				),
				array(
					'field' => 'LpuUnitType_id',
					'label' => 'Тип подразделения',
					'rules' => 'trim',
					'type' => 'string'
				),
			),
			'getMedStaffFactListForSchedule' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор ЛПУ',
					'rules' => '',
					'type' => 'id',
					'session_value' => 'lpu_id'
				),
				array(
					'field' => 'LpuUnit_id',
					'label' => 'Идентификатор подразделения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSectionPid_id',
					'label' => 'Идентификатор подотделения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedService_id',
					'label' => 'Идентификатор службы',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getUslugaComplexListForSchedule' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор ЛПУ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedService_id',
					'label' => 'Идентификатор службы',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'withMedservice',
					'label' => 'Список услуг с самой службой',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getResourceListForSchedule' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор ЛПУ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuUnit_id',
					'label' => 'Идентификатор подразделения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSectionPid_id',
					'label' => 'Идентификатор подотделения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaComplex_ids',
					'label' => 'Идентификаторы услуг',
					'rules' => '',
					'type' => 'json_array',
					'assoc' => true
				),
				array(
					'field' => 'MedService_id',
					'label' => 'Идентификатор службы',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaComplexMedService_id',
					'label' => 'Идентификатор связи услуги и службы',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'withMedservice',
					'label' => 'Список услуг с самой службой',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Resource_begDate',
					'label' => 'Дата начала дейтсвия ресурса',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'TimetableResource_begDate',
					'label' => 'Дата начала записи на ресурс',
					'rules' => '',
					'type' => 'date'
				),
			),
			'getRecordLpuUnitList' => array(
				array('default' => 100, 'field' => 'limit', 'label' => 'Лимит записей', 'rules' => '', 'type' => 'int'),
				array('default' => 0, 'field' => 'start', 'label' => 'Начальная запись', 'rules' => '', 'type' => 'int'),
				array(
					'field' => 'ListForDirection',
					'label' => 'Показывать список для направлений',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DirType_Code',
					'label' => 'Тип направления',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Filter_Lpu_id',
					'label' => 'Идентификатор ЛПУ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Filter_LpuUnit_id',
					'label' => 'Идентификатор Подразделения',
					'rules' => '',
					'type' => 'id'
				),
				array(
				    'field' => 'Filter_LpuAgeType_id',
                    'label' => 'Идентификатор типа МО по возрасту',
                    'rules' => '',
                    'type' => 'id'
                ),
				array(
					'field' => 'Filter_LpuSection_id',
					'label' => 'Идентификатор текущего отделения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Filter_LpuRegionType_id',
					'label' => 'Тип прикрепления',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Filter_Lpu_Nick',
					'label' => 'Название ЛПУ',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Filter_LpuSectionProfile_id',
					'label' => 'Идентификатор профиля отделения',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'Filter_includeDopProfiles',
					'label' => 'Признак необходимости учитывать доп. профили отделений',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'Filter_MedPersonal_FIO',
					'label' => 'ФИО врача',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Filter_KLTown_Name',
					'label' => 'Населенный пункт',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Filter_KLStreet_Name',
					'label' => 'Улица',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Filter_KLHouse',
					'label' => 'Номер дома',
					'rules' => 'trim|mb_strtoupper',
					'type' => 'string'
				),
				array(
					'field' => 'Filter_LpuUnitType_id',
					'label' => 'Тип подразделения',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Filter_LpuType_id',
					'label' => 'Тип ЛПУ',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Filter_LpuUnit_Address',
					'label' => 'Адрес подразделения',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'WithoutChildLpuSectionAge',
					'label' => 'Скрыть детские отделения',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'ARMType',
					'label' => 'Текущий тип АРМа',
					'rules' => '',
					'type' => 'string'
				)
			),
			'getRecordMedPersonalList' => array(
				array(
					'field' => 'LpuUnit_id',
					'label' => 'Идентификатор подразделения ЛПУ',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Date',
					'label' => 'Дата начала отображения расписания',
					'rules' => '',
					'type' => 'string',
					'default' => date("Y-m-d")
				),
				array(
					'field' => 'Filter_Lpu_id',
					'label' => 'Идентификатор ЛПУ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Filter_LpuRegionType_id',
					'label' => 'Тип прикрепления',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Filter_LpuSectionProfile_id',
					'label' => 'Идентификатор профиля отделения',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'Filter_includeDopProfiles',
					'label' => 'Признак необходимости учитывать доп. профили отделений',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'Filter_MedPersonal_FIO',
					'label' => 'ФИО врача',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Filter_KLTown_Name',
					'label' => 'Населенный пункт',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Filter_KLStreet_Name',
					'label' => 'Улица',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Filter_KLHouse',
					'label' => 'Номер дома',
					'rules' => 'trim|mb_strtoupper',
					'type' => 'string'
				),
				array(
					'field' => 'ListForDirection',
					'label' => 'Показывать список для направлений',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'withDirection',
					'label' => 'С электронным направлением',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'WithoutChildLpuSectionAge',
					'label' => 'Скрыть детские отделения',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'FormName',
					'label' => 'Форма, с которой вызывается поиск',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'ARMType',
					'label' => 'АРМ',
					'rules' => '',
					'type' => 'string'
				)
			),
			'getRecordLpuSectionList' => array(
				array(
					'field' => 'LpuUnit_id',
					'label' => 'Идентификатор подразделения ЛПУ',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Date',
					'label' => 'Дата начала отображения расписания',
					'rules' => '',
					'type' => 'string',
					'default' => date("Y-m-d")
				),
				array(
					'field' => 'Filter_Lpu_id',
					'label' => 'Идентификатор ЛПУ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Filter_LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Filter_LpuSectionProfile_id',
					'label' => 'Идентификатор профиля отделения',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'Filter_includeDopProfiles',
					'label' => 'Признак необходимости учитывать доп. профили отделений',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'Filter_MedPersonal_FIO',
					'label' => 'ФИО врача',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Filter_KLTown_Name',
					'label' => 'Населенный пункт',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Filter_KLStreet_Name',
					'label' => 'Улица',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Filter_KLHouse',
					'label' => 'Номер дома',
					'rules' => 'trim|mb_strtoupper',
					'type' => 'string'
				),
				array(
					'field' => 'ListForDirection',
					'label' => 'Показывать список для направлений',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'WithoutChildLpuSectionAge',
					'label' => 'Скрыть детские отделения',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'FormName',
					'label' => 'Форма, с которой вызывается поиск',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'ARMType',
					'label' => 'АРМ',
					'rules' => '',
					'type' => 'string'
				)
			),
			'getRecordMedServiceList' => array(
				array(
					'field' => 'FormName',
					'label' => 'Форма',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор ЛПУ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuUnit_id',
					'label' => 'Идентификатор подразделения ЛПУ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Date',
					'label' => 'Дата начала отображения расписания',
					'rules' => '',
					'type' => 'string',
					'default' => date("Y-m-d")
				),
				array(
					'field' => 'Filter_Lpu_id',
					'label' => 'Идентификатор ЛПУ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Filter_MedPersonal_FIO',
					'label' => 'ФИО врача',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'LpuUnitLevel',
					'label' => 'Показывать службы уровня подразделений и выше',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'ListForDirection',
					'label' => 'Показывать список для направлений',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Filter_MedPersonal_FIO',
					'label' => 'ФИО врача',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Filter_Lpu_Nick',
					'label' => 'Название ЛПУ',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Filter_KLTown_Name',
					'label' => 'Населенный пункт',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Filter_LpuUnit_Address',
					'label' => 'Адрес подразделения',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Filter_MedService_id',
					'label' => 'Служба',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedServiceType_SysNick',
					'label' => 'Тип службы',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'ListForDirection',
					'label' => 'Показывать список для направлений',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DirType_Code',
					'label' => 'Тип направления',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedService_Caption',
					'label' => 'Служба',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'UslugaComplexMedService_IsPay',
					'label' => 'Платная услуга',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'ARMType',
					'label' => 'АРМ',
					'rules' => '',
					'type' => 'string'
				),
				array('default' => 0, 'field' => 'isOnlyPolka', 'label' => 'Флаг отображения служб только поликлинических отделений', 'rules' => '', 'type' => 'int'),
				array('field' => 'groupByMedService', 'label' => 'Групиировать по месту оказания', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedService_id', 'label' => 'Служба', 'rules' => '', 'type' => 'id')
			),
			'getAppropriateLpuUnit' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedService_id',
					'label' => 'Идентификатор службы',
					'rules' => '',
					'type' => 'id'
				),
			),
			'printLpuUnitSchedule' => array(
				array(
					'field' => 'LpuUnit_id',
					'label' => 'Идентификатор подразделения',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Date',
					'label' => 'Дата распечатки',
					'rules' => 'required',
					'type' => 'string'
				),
			),
			'getDirTypeList' => array(
				array(
					'field' => 'isDead',
					'label' => 'isDead',
					'rules' => '',
					'type' => 'checkbox'
				)
			),
			'getRegionsList' => array(
				array(
					'field' => 'KLStreet_id',
					'label' => 'Улица',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Address_House',
					'label' => 'Дом',
					'rules' => 'required|trim',
					'type' => 'string'
				),
			),
			'getCurrentLpuData' => array(
			),
			'getListByPerson' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getListMedServiceByUsluga' => array(
				array(
					'field' => 'uslugaList',
					'label' => 'Услуги',
					'rules' => 'required',
					'type' => 'string'
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
			'getInetUserInfo' => array(
				array(
					'field' => 'pmUser_id',
					'label' => 'Идентификатор пользователя',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getTimetableTypeList' => array(
				array(
					'field' => 'Place_id',
					'label' => 'Идентификатор места',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getTimetableTypeMenu' => array(
				array(
					'field' => 'Place_id',
					'label' => 'Идентификатор места',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getLpuUnitQueue' => array(
				array(
					'field' => 'Filter_Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSectionProfile_id',
					'label' => 'Идентификатор профиля отделения',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getMedServiceQueue' => array(
				array(
					'field' => 'Filter_Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedService_id',
					'label' => 'Идентификатор службы',
					'rules' => '',
					'type' => 'id'
				)
			),
		);

		// В конструкторе контроллера сразу открываем хелпер Reg
		$this->load->helper('Reg');
	}

	/**
	 * Функция получения дерева фильтров.
	 * Входящие данные: $_POST с фильтрами
	 * На выходе: JSON-строка
	 */
	function GetFilterTree() {
		$this->load->helper('Main');

		$data = $this->ProcessInputData('GetFilterTree', true);
		if ( $data === false )
			return false;

		if ( $data['node'] == 'root' ) {
			// структура МО
			// Подразделения
			$this->load->model("Reg_model", "rmodel");
			$data['object_id'] = $data['Lpu_id'];
			if ( !empty($data['LpuSection_id'])) {//загружаем только указанное отделение
				$response = $this->rmodel->getLpuSection($data);
				if (!empty($response)) {
					$val[] = array(
						'text' => trim($response['LpuSection_Name']),
						'id' => 'LpuSection_' . $response['LpuSection_id'],
						'leaf' => false,
						'object' => 'LpuSection',
						'object_id' => $response['LpuSection_id'],
						'LpuUnitType_id' => $response['LpuUnitType_id'],
						'iconCls' => $response['isClosed'] == 1 ? 'lpu-section-closed16' : 'lpu-section16'
					);
				}
			} else {
				$info = $this->rmodel->GetLpuUnitNodeList($data);
				$val = array();
				// здания
				if ( $info != false && count($info) > 0 ) {
					foreach ( $info as $rows ) {
						$val[] = array('text' =>
							trim($rows['LpuUnit_Name']),
							'id' => 'LpuUnit_' . $rows['LpuUnit_id'],
							'leaf' => false,
							'object' => 'LpuUnit',
							'object_id' => $rows['LpuUnit_id'],
							'LpuUnitType_id' => $rows['LpuUnitType_id'],
							'iconCls' => $rows['isClosed'] == 1 ? 'lpu-building-closed16' : 'lpu16');
					}
				}
				if (!(in_array(getRegionNick(), array('vologda', 'msk','ufa')) && !empty($data['filterByArm'])) && empty($data['LpuUnit_id']) ) {
					$val[] = array('text' =>
						"Службы уровня МО",
						'id' => 'razdel_medservices',
						'leaf' => false,
						'object' => 'MedServices',
						'iconCls' => 'medservice16'
					);
				}
			}
			$this->ReturnData($val);
		} else {
			switch ( $data['object'] ) {
				case 'MedServices':
					$this->load->model("Reg_model", "rmodel");
					$info = $this->rmodel->GetMedServiceNodeList($data);
					$val = array();
					if ( $info != false && count($info) > 0 ) {
						foreach ( $info as $rows ) {
							$val[] = array('text' =>
								trim($rows['MedService_Name']),
								'id' => 'MedService_' . $rows['MedService_id'],
								'leaf' => ($rows['leafcount'] > 0 ? false : true),
								'object' => 'MedService',
								'object_id' => $rows['MedService_id'],
								'iconCls' => $rows['isClosed'] == 1 ? 'medservice-closed16' : 'medservice16',
								'MedServiceType_SysNick' => $rows['MedServiceType_SysNick']
							);
						}
					}
					$this->ReturnData($val);
					break;
				case 'LpuUnit':
					$this->load->model("Reg_model", "rmodel");
					$info = $this->rmodel->GetLpuSectionNodeList($data);
					$val = array();
					if ( $info != false && count($info) > 0 ) {
						foreach ( $info as $rows ) {
							$val[] = array('text' =>
								trim($rows['LpuSection_Name']),
								'id' => 'LpuSection_' . $rows['LpuSection_id'],
								'leaf' => ($rows['leafcount'] > 0 ? false : true),
								'object' => 'LpuSection',
								'object_id' => $rows['LpuSection_id'],
								'LpuUnitType_id' => $rows['LpuUnitType_id'],
								'iconCls' => $rows['isClosed'] == 1 ? 'lpu-section-closed16' : 'lpu-section16');
						}
					}

					//Загрузка служб на уровне, грузить всегда для параклиники
					if ($data['LpuUnitType_id']==3 || !(in_array(getRegionNick(), array('vologda', 'msk', 'ufa')) && !empty($data['filterByArm']))) {
						$info = $this->rmodel->GetMedServiceNodeList($data);
						if ($info != false && count($info) > 0) {
							foreach ($info as $rows) {
								$val[] = array('text' =>
									trim($rows['MedService_Name']),
									'id' => 'MedService_' . $rows['MedService_id'],
									'leaf' => ($rows['leafcount'] > 0 ? false : true),
									'object' => 'MedService',
									'object_id' => $rows['MedService_id'],
									'iconCls' => $rows['isClosed'] == 1 ? 'medservice-closed16' : 'medservice16',
									'MedServiceType_SysNick' => $rows['MedServiceType_SysNick']
								);
							}
						}
					}

					$this->ReturnData($val);
					break;
				case 'LpuSection':
					$this->load->model("Reg_model", "rmodel");
					$val = array();
					if (empty($data['MedServiceOnly'])) {
						$info = $this->rmodel->GetLpuSectionPidNodeList($data);
						if ( $info != false && count($info) > 0 ) {
							foreach ( $info as $rows ) {
								$val[] = array('text' =>
									trim($rows['LpuSection_Name']),
									'id' => 'LpuSection_' . $rows['LpuSection_id'],
									'leaf' => true,
									'object' => 'LpuSectionPid',
									'object_id' => $rows['LpuSection_id'],
									'LpuUnitType_id' => $rows['LpuUnitType_id'],
									'iconCls' => $rows['isClosed'] == 1 ? 'lpu-section-closed16' : 'lpu-section16');
							}
						}
					}
					
					//если с клиента приходит null он обрабатывается как строка
					if (isset($data['filterByArm']) && $data['filterByArm'] == 'null'){
						$data['filterByArm'] = null;
					}
					//Загрузка служб на уровне, грузить всегда для параклиники
					if ($data['LpuUnitType_id'] == 3 || !(in_array(getRegionNick(), ['vologda', 'msk']) && !empty($data['filterByArm']))) {
						$info = $this->rmodel->GetMedServiceNodeList($data);
						if ($info != false && count($info) > 0) {
							foreach ($info as $rows) {
								$val[] = array('text' =>
									trim($rows['MedService_Name']),
									'id' => 'MedService_' . $rows['MedService_id'],
									'leaf' => ($rows['leafcount'] > 0 ? false : true),
									'object' => 'MedService',
									'object_id' => $rows['MedService_id'],
									'iconCls' => $rows['isClosed'] == 1 ? 'medservice-closed16' : 'medservice16',
									'MedServiceType_SysNick' => $rows['MedServiceType_SysNick']
								);
							}
						}
					}

					$this->ReturnData($val);
					break;
			}
		}
	}

	/**
	 * Получение списка врачей для ведения расписания
	 */
	function getMedStaffFactListForSchedule() {

		/**
		 * Обработка результатов
		 */
		function ProcessData( $row ) {
			$row['MedPersonal_FIO'] .= "<br/><font color=gray>" . $row['LpuSectionProfile_Name'] . " / " . $row['LpuSection_Name'] . "</font>";
			if ($row['isClosed'] == 1) {
				$row['MedPersonal_FIO'] .= "<br/><font color=gray>Период работы: " . $row['MedStaffFact_setDate'] . " - " . $row['MedStaffFact_disDate'] . "</font>";
			}
			return $row;
		}

		$data = $this->ProcessInputData('getMedStaffFactListForSchedule', true);
		if ( $data === false )
			return false;
		$this->load->model("MedPersonal_model", "mpmodel");
		$response = $this->mpmodel->getMedStaffFactListForReg($data);
		$this->ProcessModelList($response, true, true, 'При запросе возникла ошибка.', 'ProcessData')->ReturnData();
	}

	/**
	 * Получение списка услуг для ведения расписания
	 */
	function getUslugaComplexListForSchedule() {

		$data = $this->ProcessInputData('getUslugaComplexListForSchedule', true);
		if ( $data === false )
			return false;
		$this->load->model("Reg_model", "rmodel");
		$this->load->model("MedService_model", "msmodel");
		$response = $this->rmodel->getUslugaComplexListForSchedule($data);
		// Отображаем само название службы в списке, то есть рисуем иерархию
		if ( isset($data['withMedservice']) && $data['withMedservice'] == 1 ) {
			foreach ( $response as &$row ) {
				$row['UslugaComplex_Name'] = '<div class="x-row-list-tree">' . $row['UslugaComplex_Name'];
				if(!empty($row['UslugaComplexMedService_endDT']) && ( strtotime(ConvertDateFormat($row['UslugaComplexMedService_endDT'], 'd.m.Y')) < time() ) ){
					$row['UslugaComplex_Name'] .= '<span style="color:red;"> (закрыта с '. ConvertDateFormat($row['UslugaComplexMedService_endDT'], 'd.m.Y').')</span>';
				}
				$row['UslugaComplex_Name'] .= '</div>';
			}
			// Добавляем само название службы жирным первым элементом
			$MedService_Info = $this->msmodel->getMedServiceInfoForReg($data);
			$response = array_merge(array(array('UslugaComplexMedService_id' => null, 'UslugaComplex_Name' => '<b>' . $MedService_Info['MedService_Name'] . '</b>')), $response);
		}
		$this->ProcessModelList($response, true, true, 'При запросе возникла ошибка.')->ReturnData();
	}


	/**
	 *
	 * @return type
	 */
	function getResourceListForSchedule() {

		$data = $this->ProcessInputData('getResourceListForSchedule', true);
		if ( $data === false )
			return false;
		$this->load->model("Reg_model", "rmodel");
		$this->load->model("MedService_model", "msmodel");
		$response = $this->rmodel->getResourceListForSchedule($data);
		// Отображаем само название службы в списке, то есть рисуем иерархию

		$this->ProcessModelList($response, true, true, 'При запросе возникла ошибка.')->ReturnData();
	}

	/**
	 * Получение списка подразделений ЛПУ для первого шага мастера записи
	 */
	function getRecordLpuUnitList() {

		/**
		 * Раскраска названий ЛПУ по типам
		 * Добавление комментариев
		 */
		function ProcessData( $row, $ctrl ) {

			// Если заданы какие-то данные по адресам участков, будем жирным шрифтом выделять подразделения, где найдены эти участки
			if ( isset($ctrl->data['Filter_KLStreet_Name']) || isset($ctrl->data['Filter_KLTown_Name']) || isset($ctrl->data['Filter_KLHouse']) ) {
				if ( isset($ctrl->response['RegionLpuUnit'][$row['LpuUnit_id']]) ) {
					$row['Lpu_Nick'] = "<b>" . $row['Lpu_Nick'] . "</b>";
					$row['LpuUnit_Name'] = "<b>" . $row['LpuUnit_Name'] . "</b>";
					$row['LpuUnit_Address'] = "<b>" . $row['LpuUnit_Address'] . "</b>";
					$row['LpuUnit_Phone'] = "<b>" . $row['LpuUnit_Phone'] . "</b>";
				}
			}

			$lpuname = $row['LpuUnit_Name'];
			If ( $row['LpuUnit_Descr'] != "" ) {
				$row['LpuUnit_Name'] = "<img ext:qtip=\"" . htmlspecialchars(str_replace(chr(10), '<br>', $row['LpuUnit_Descr']));
				//Если есть пользователь поставивший примечание и дата его установки, то показываем и их
				if ( isset($row['pmuser_name']) && isset($row['LpuUnit_updDT']) ) {
					$dateTime = (gettype($row['LpuUnit_updDT']) == 'object') ? $row['LpuUnit_updDT']->format("H:i d.m.y") : DateTime::createFromFormat('d.m.Y H:i:s',$row['LpuUnit_updDT'])->format("H:i d.m.y");
					$row['LpuUnit_Name'] .= "<hr><font class=\'smallfont\'>" . htmlspecialchars($row['pmuser_name']) . ", " . $dateTime . "</font>";
				}
				$row['LpuUnit_Name'] .= "\" src=\"/img/icons/info16.png\" style=\"cursor: pointer;\">";
			} else {
				$row['LpuUnit_Name'] = "";
			}
			If ( $row['LpuUnit_Enabled'] != 1 ) {
				$row['LpuUnit_Name'].="<img ext:qtip=\"Операторам центра записи не доступна запись в это подразделение.\" src=\"/img/icons/exclamation16.png\" style=\"cursor: pointer;\">";
			}
			$row['LpuUnit_Name'] = $row['LpuUnit_Name'] . " " . Trim($lpuname) . "";

			If ( isset($row['ExtMed']) && $row['ExtMed'] > 0 ) {
				$row['Lpu_Nick'] = "<font color='red'>" . trim($row['Lpu_Nick']) . "</font>";
			}
			If ( mb_strpos($row['Lpu_Name'], 'ДЕТСК') > 0 ) {
				$row['Lpu_Nick'] = "<font color='green'>" . trim($row['Lpu_Nick']) . "</font>";
			}
			If ( isset($row['Town']) ) {
				$row['LpuUnit_Address'] = $row['Town'] . ", " . $row['LpuUnit_Address'];
			}

			if ( isset($ctrl->data['Filter_LpuSectionProfile_id']) ) {
				If ( isset($ctrl->MinDates[$row['LpuUnit_id']]) && $ctrl->MinDates[$row['LpuUnit_id']] != '' ) {
					$row['FreeTime'] = "<span style=\"background-color: rgb(221, 255, 221);\">" . ConvertDateFormat($ctrl->MinDates[$row['LpuUnit_id']], 'd.m') . "</span>";
				} else {
					$row['FreeTime'] = "<span style=\"background-color: rgb(255, 221, 221);\">нет</span>";
				}
			}

			return $row;
		}

		$this->data = $this->ProcessInputData('getRecordLpuUnitList', true);

		if ( $this->data === false )
			return false;
		$this->load->model("Reg_model", "rmodel");
		$this->response = $this->rmodel->getRecordLpuUnitList($this->data);

		// Если в фильтре задаем профиль, то считаем первые дни приема
		if ( isset($this->data['Filter_LpuSectionProfile_id']) ) {
			$this->MinDates = $this->rmodel->CacheFirstDates($this->data, $this->data['Filter_LpuSectionProfile_id']);
		}
		$this->ProcessModelMultiList($this->response, true, true, 'При запросе возникла ошибка.', 'ProcessData')->ReturnData();
	}

	/**
	 * Получение списка подразделений ЛПУ для мастера выписки направлений
	 */
	function getDirectionLpuUnitList() {
		return $this->getRecordLpuUnitList();
	}

	/**
	 * Получение списка врачей для переданного подразделения для мастера записи
	 */
	function getRecordMedPersonalList() {
		$data = $this->ProcessInputData('getRecordMedPersonalList', true);
		if ( $data === false ) {
			return false;
		}

		$this->load->model("Reg_model", "rmodel");
		$response = $this->rmodel->getRecordMedPersonalList($data);
		$this->ProcessModelList($response, true, true, 'При запросе возникла ошибка.')->ReturnData();
	}

	/**
	 * Получение списка врачей для переданного подразделения для мастера выписки направлений
	 */
	function getDirectionMedPersonalList() {
		return $this->getRecordMedPersonalList();
	}

	/**
	 * Получение списка отделений для переданного подразделения для мастера записи
	 */
	function getRecordLpuSectionList() {
		$data = $this->ProcessInputData('getRecordLpuSectionList', true);
		if ($data === false) {
			return false;
		}

		$this->load->model("Reg_model", "rmodel");
		$response = $this->rmodel->getRecordLpuSectionList($data);
		$this->ProcessModelList($response, true, true, 'При запросе возникла ошибка.')->ReturnData();
	}

	/**
	 * Получение списка отделений для переданного подразделения для мастера выписки направлений
	 */
	function getDirectionLpuSectionList() {
		return $this->getRecordLpuSectionList();
	}

	/**
	 * Получение списка медицинских служб подразделения или ЛПУ
	 */
	function getRecordMedServiceList() {
		$data = $this->ProcessInputData('getRecordMedServiceList', true);
		if ($data === false) {
			return false;
		}

		$this->load->model("Reg_model", "rmodel");
		$response = $this->rmodel->getRecordMedServiceList($data);
		$this->ProcessModelList($response, true, true, 'При запросе возникла ошибка.')->ReturnData();
	}

	/**
	 * Получение списка медицинских служб подразделения или ЛПУ для выписки направления
	 */
	function getDirectionMedServiceList() {
		return $this->getRecordMedServiceList();
	}

	/**
	 * Выбирает наиболее подходящее подразделение для записи
	 * Если передан идентификатор человека, то выбирается подразделение, где находится его участковый врач
	 * Если человек не передан, то по переданному идентификатору службы выбирается подразделение, где находится регистратура
	 * Если служба не привязана к подразделению, то выбирается первое поликлиническое подразделение в ЛПУ
	 */
	function getAppropriateLpuUnit() {
		$this->data = $this->ProcessInputData('getAppropriateLpuUnit', true);
		if ( $this->data === false )
			return false;
		$this->load->model("Reg_model", "rmodel");
		$response = $this->rmodel->getAppropriateLpuUnit($this->data);
		$this->ProcessModelSave($response, true, 'При запросе возникла ошибка.')->ReturnData();
	}

	/**
	 * Печать расписания у всех врачей подразделения на заданный день
	 */
	function printLpuUnitSchedule() {
		$this->load->view(
				'reg/timetable_general_css'
		);

		$this->load->model("Reg_model", "rmodel");
		$this->data = $this->ProcessInputData('printLpuUnitSchedule', true);

		$scheduledata = $this->rmodel->getLpuUnitSchedule($this->data);

		$LpuUnit_Name = $this->rmodel->getLpuUnitName($this->data);

		$this->load->library("TTimetableGraf");
		$this->load->view(
				'reg/printlpuunitschedule', array(
			'data' => $scheduledata,
			'LpuUnit_Name' => $LpuUnit_Name,
			'Date' => $this->data['Date']
				), false, (defined('USE_UTF') && USE_UTF)
		);
	}

	/**
	 * Получение списка типов направлений
	 */
	function getDirTypeList() {
		$this->data = $this->ProcessInputData('getDirTypeList', true);
		if ( $this->data === false )
			return false;
		$this->load->model("Reg_model", "rmodel");
		$response = $this->rmodel->getDirTypeList($this->data);
		$this->ProcessModelList($response, true, true, 'При запросе возникла ошибка.')->ReturnData();
	}

	/**
	 * Получение списка участков
	 */
	function getRegionsList() {

		/**
		 * Обработка результатов
		 */
		function ProcessData( $row, $ctrl ) {
			$row['LpuRegion_Name'] = "<a href=# onclick=\"getWnd('swRegionStreetListWindow').show({LpuRegion_id:" . $row['LpuRegion_id'] . "})\">" . $row['LpuRegion_Name'] . "</a>";

			return $row;
		}

		$this->data = $this->ProcessInputData('getRegionsList', true);
		if ( $this->data === false )
			return false;
		$this->load->model("Reg_model", "rmodel");
		$response = $this->rmodel->getRegionsList($this->data);
		$this->ProcessModelList($response, true, true, 'При запросе возникла ошибка.', 'ProcessData')->ReturnData();
	}

	/**
	 * Получение данных по текущей ЛПУ, в частности нас интересует территория обслуживания
	 */
	function getCurrentLpuData() {
		$this->data = $this->ProcessInputData('getCurrentLpuData', true);
		if ( $this->data === false )
			return false;
		$this->load->model("User_model", "umodel");
		$response = $this->umodel->getCurrentLpuData($this->data);
		$this->ProcessModelList($response, true, true, 'При запросе возникла ошибка.')->ReturnData();
	}

	/**
	 * Загрузка грида "Записи пациента"
	 *
	 * Используется форма АРМ регистратора
	 *
	 * @param integer $_POST['Person_id'] Идентификатор человека
	 * @return string JSON-строка
	 * @author       Alexander Permyakov
	 */
	function getListByPerson() {
		$data = $this->ProcessInputData('getListByPerson', true, true);
		if ( $data === false ) {
			return false;
		}
		$this->load->model("Reg_model", "rmodel");
		$response = $this->rmodel->getListByPerson($data);
		$this->ProcessModelList($response, true, true, 'При поиске записей человека возникла ошибка.')->ReturnData();
		return true;
	}

	/**
	 * Получение списка служб для услуги
	 */
	function getListMedServiceByUsluga() {
		$data = $this->ProcessInputData('getListMedServiceByUsluga', true, true);
		if ( $data === false ) {
			return false;
		}
		$this->load->model("Reg_model", "rmodel");
		$response = $this->rmodel->getListMedServiceByUsluga($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение данных о пользователе интернет портала
	 */
	function getInetUserInfo() {
		$data = $this->ProcessInputData('getInetUserInfo', true, true);
		if ( $data === false ) {
			return false;
		}
		$this->load->model("Reg_model", "rmodel");
		$response = $this->rmodel->getInetUserInfo($data);

		$view = 'reg/inet_user_info';
		if ( $data['session']['region']['nick'] == 'pskov' ) { // для Пскова меньше информации
			$view = 'reg/inet_user_info_short';
		}
		$this->load->view(
			$view,
			array(
				'data' => $response
			)
		);
	}

	/**
	 * Получение списка типов бирок
	 */
	function getTimetableTypeList() {
		$this->data = $this->ProcessInputData('getTimetableTypeList', true);
		if ( $this->data === false )
			return false;
		$this->load->model("Reg_model", "rmodel");
		$types = $this->rmodel->getTimetableTypeList($this->data);
		$response = array();
		foreach($types as $type) {
			$response[] = array('TimetableType_id' => $type->id, 'TimetableType_Name' => $type->name);
		}
		$this->ProcessModelList($response, true, true, 'При запросе возникла ошибка.')->ReturnData();
	}

	/**
	 * Получение меню из списка типов бирок
	 */
	function getTimetableTypeMenu() {
		$this->data = $this->ProcessInputData('getTimetableTypeMenu', true);
		if ( $this->data === false )
			return false;
		$this->load->model("Reg_model", "rmodel");
		$types = $this->rmodel->getTimetableTypeList($this->data);
		$this->load->view(
			'reg/timetabletype_menu',
			array(
				'types' => $types
			)
		);
	}

	/**
	 * Получение данных об очереди
	 */
	function getLpuUnitQueue() {
		$data = $this->ProcessInputData('getLpuUnitQueue', true);
		if ( $data === false ) {
			return false;
		}

		/**
		 * Обработка строк
		 */
		function ProcessData($row, $ctrl) {
			$row['date'] = ConvertDateFormat($row['date'], 'd.m.Y');
			return $row;
		}

		$this->load->model("Reg_model", "rmodel");
		$response = $this->rmodel->GetLpuUnitQueue($data);

		$this->ProcessModelList($response, true, true, 'При запросе возникла ошибка.', 'ProcessData')->ReturnData();
	}

	/**
	 * Получение данных об очереди
	 */
	function getMedServiceQueue() {
		$data = $this->ProcessInputData('getMedServiceQueue', true);
		if ( $data === false ) {
			return false;
		}

		/**
		 * Обработка строк
		 */
		function ProcessData($row, $ctrl) {
			$row['date'] = ConvertDateFormat($row['date'], 'd.m.Y');
			return $row;
		}

		$this->load->model("Reg_model", "rmodel");
		$response = $this->rmodel->GetMedServiceQueue($data);

		$this->ProcessModelList($response, true, true, 'При запросе возникла ошибка.', 'ProcessData')->ReturnData();
	}
}

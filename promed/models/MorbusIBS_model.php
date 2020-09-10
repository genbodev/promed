<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * MorbusIBS_model - модель для MorbusIBS
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      IBS
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Александр Пермяков
 * @version      12.2014
 *
 * Магические свойства
 * @property-read int $morbusTypeId
 * @property-read string $morbusTypeSysNick
 * @property-read string $groupRegistry
 * @property-read Morbus_model $Morbus
 */
class MorbusIBS_model extends swModel
{
	/**
	 * @var bool Требуется ли параметр pmUser_id для хранимки удаления
	 */
	protected $_isNeedPromedUserIdForDel = true;

	protected $_MorbusType_id = 50;
	/**
	 * Список полей для метода updateMorbusSpecific
	*/
	private $entityFields = array(
		'MorbusIBS' => array(
			'Morbus_id',
			'Diag_nid',// Диагноз ИБС из справочника МКБ-10 
			'IBSType_id',// Тип ИБС
			// Стабильная стенокардия
			'MorbusIBS_IsStenocardia',// Стабильная стенокардия (Да/Нет)
			'MorbusIBS_FuncClass',// Функциональный класс (1/2/3/4)
			'MorbusIBS_StressTest',// Стресс-тест (Положительный/Отрицательный)
			'MorbusIBS_IsEchocardiography',// Эхокардиография (Да/Нет)
			'MorbusIBS_IsHalterMonitor',// Холтеровское мониторирование (Да/Нет)
			
			'MorbusIBS_IsMyocardInfarct',// Перенесенный инфаркт миокарда (Да/Нет)
			'MorbusIBS_IsMyocardIschemia',// Выраженная ишемия миокарда при нагрузке (Да/Нет)
			'MorbusIBS_IsFirstStenocardia',// Впервые возникшая стенокардия (Да/Нет)
			'MorbusIBS_IsNoStableStenocardia',// Нестабильная (прогрессирующая) стенокардия (Да/Нет)
			'MorbusIBS_IsRiseTS',// Подъем TS (Да/Нет)
			'MorbusIBS_IsSaveIschemia',// Сохраняются и/или рецидивируют признаки ишемии (Да/Нет)
			'MorbusIBS_IsBackStenocardia',// Возврат стенокардии (Да/Нет)
			'MorbusIBS_IsShunting',// Перенесенная операция коронарного шунтирования (Да/Нет)
			'MorbusIBS_IsStenting',// Перенесенная операция коронарного стентирования (Да/Нет)
 
			//Коронарография
			'MorbusIBS_IsKGIndication',// Показано проведение КГ (Да/Нет)
			'UslugaComplex_id',// Код из справочника услуг ГОСТ-2011 с фильтром по A06.10.006
			'MorbusIBS_IsKGConsent',// Получено соглание на проведение КГ (Да/Нет)
			'MorbusIBS_IsKGFinished',// Проведена КГ (Да/Нет)
		),
		'Morbus' => array(
			'MorbusBase_id',
			'Evn_pid', //Учетный документ, в рамках которого добавлено заболевание
			'Diag_id','MorbusKind_id','Morbus_Name','Morbus_Nick',
			'Morbus_setDT','Morbus_disDT','MorbusResult_id'
		),
		'MorbusBase' => array(
			'Person_id','Evn_pid','MorbusType_id','MorbusBase_setDT',
			'MorbusBase_disDT','MorbusResult_id'
		)
	);

	/**
	 * Определение правил для входящих параметров
	 * @param string $name
	 * @return array
	 */
	function getInputRules($name)
	{
		$rules = array();
		switch ($name) {
			case self::SCENARIO_DO_SAVE:
				$rules[self::ID_KEY] = array(
					'field' => 'MorbusIBS_id',
					'rules' => 'trim|required',
					'label' => 'Идентификатор специфики',
					'type' => 'id'
				);
				$rules['morbus_id'] = array(
					'field' => 'Morbus_id',
					'rules' => 'trim|required',
					'label' => 'Идентификатор простого заболевания',
					'type' => 'id'
				);
				$rules['person_id'] = array(
					'field' => 'Person_id',
					'label' => 'Пациент',
					'rules' => 'trim|required',
					'type' => 'id'
				);
				$rules['diag_id'] = array(
					'field' => 'Diag_id',
					'label' => 'Диагноз',
					'rules' => 'trim|required',
					'type' => 'id'
				);
				$rules['evn_pid'] = array(
					'field' => 'Evn_pid',
					'label' => 'Учетный документ',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['ibstype_id'] = array(
					'field' => 'IBSType_id',
					'label' => 'Тип ИБС',
					'rules' => 'trim|required',
					'type' => 'id'
				);
				$rules['isstenocardia'] = array(
					'field' => 'MorbusIBS_IsStenocardia',
					'label' => 'Стабильная стенокардия',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['funcclass'] = array(
					'field' => 'MorbusIBS_FuncClass',
					'label' => 'Функциональный класс',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['stresstest'] = array(
					'field' => 'MorbusIBS_StressTest',
					'label' => 'Стресс-тест',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['isechocardiography'] = array(
					'field' => 'MorbusIBS_IsEchocardiography',
					'label' => 'Эхокардиография',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['ishaltermonitor'] = array(
					'field' => 'MorbusIBS_IsHalterMonitor',
					'label' => 'Холтеровское мониторирование',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['ismyocardinfarct'] = array(
					'field' => 'MorbusIBS_IsMyocardInfarct',
					'label' => 'Перенесенный инфаркт миокарда',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['ismyocardischemia'] = array(
					'field' => 'MorbusIBS_IsMyocardIschemia',
					'label' => 'Выраженная ишемия миокарда при нагрузке',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['isfirststenocardia'] = array(
					'field' => 'MorbusIBS_IsFirstStenocardia',
					'label' => 'Впервые возникшая стенокардия',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['isnostablestenocardia'] = array(
					'field' => 'MorbusIBS_IsNoStableStenocardia',
					'label' => 'Нестабильная (прогрессирующая) стенокардия',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['isrisets'] = array(
					'field' => 'MorbusIBS_IsRiseTS',
					'label' => 'Подъем TS',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['issaveischemia'] = array(
					'field' => 'MorbusIBS_IsSaveIschemia',
					'label' => 'Сохраняются и/или рецидивируют признаки ишемии',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['isbackstenocardia'] = array(
					'field' => 'MorbusIBS_IsBackStenocardia',
					'label' => 'Возврат стенокардии',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['isshunting'] = array(
					'field' => 'MorbusIBS_IsShunting',
					'label' => 'Перенесенная операция коронарного шунтирования',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['isstenting'] = array(
					'field' => 'MorbusIBS_IsStenting',
					'label' => 'Перенесенная операция коронарного стентирования',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['iskgindication'] = array(
					'field' => 'MorbusIBS_IsKGIndication',
					'label' => 'Показано проведение КГ',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['uslugacomplex_id'] = array(
					'field' => 'UslugaComplex_id',
					'label' => 'Код услуги КГ',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['iskgconsent'] = array(
					'field' => 'MorbusIBS_IsKGConsent',
					'label' => 'Получено соглание на проведение КГ',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['iskgfinished'] = array(
					'field' => 'MorbusIBS_IsKGFinished',
					'label' => 'Проведена КГ',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['Mode'] = array(
					'field' => 'Mode',
					'label' => 'Режим сохранения',
					'rules' => 'trim|required',
					'type' => 'string'
				);
				break;
			case 'doSavePersonDispForm':
				$rules[self::ID_KEY] = array(
					'field' => 'MorbusIBS_id',
					'rules' => 'trim',
					'label' => 'Идентификатор специфики',
					'type' => 'id'
				);
				$rules['morbus_id'] = array(
					'field' => 'Morbus_id',
					'rules' => 'trim',
					'label' => 'Идентификатор простого заболевания',
					'type' => 'id'
				);
				$rules['person_id'] = array(
					'field' => 'Person_id',
					'label' => 'Пациент',
					'rules' => 'trim|required',
					'type' => 'id'
				);
				$rules['diag_id'] = array(
					'field' => 'Diag_id',
					'label' => 'Диагноз',
					'rules' => 'trim|required',
					'type' => 'id'
				);
				break;
			case 'checkByPersonDispForm':
				$rules['person_id'] = array(
					'field' => 'Person_id',
					'label' => 'Пациент',
					'rules' => 'trim|required',
					'type' => 'id'
				);
				$rules['diag_id'] = array(
					'field' => 'Diag_id',
					'label' => 'Диагноз',
					'rules' => 'trim|required',
					'type' => 'id'
				);
				$rules['ibstype_id'] = array(
					'field' => 'IBSType_id',
					'label' => 'Тип ИБС',
					'rules' => 'trim|required',
					'type' => 'id'
				);
				$rules['isstenocardia'] = array(
					'field' => 'MorbusIBS_IsStenocardia',
					'label' => 'Стабильная стенокардия',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['funcclass'] = array(
					'field' => 'MorbusIBS_FuncClass',
					'label' => 'Функциональный класс',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['stresstest'] = array(
					'field' => 'MorbusIBS_StressTest',
					'label' => 'Стресс-тест',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['isechocardiography'] = array(
					'field' => 'MorbusIBS_IsEchocardiography',
					'label' => 'Эхокардиография',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['ishaltermonitor'] = array(
					'field' => 'MorbusIBS_IsHalterMonitor',
					'label' => 'Холтеровское мониторирование',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['ismyocardinfarct'] = array(
					'field' => 'MorbusIBS_IsMyocardInfarct',
					'label' => 'Перенесенный инфаркт миокарда',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['ismyocardischemia'] = array(
					'field' => 'MorbusIBS_IsMyocardIschemia',
					'label' => 'Выраженная ишемия миокарда при нагрузке',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['isfirststenocardia'] = array(
					'field' => 'MorbusIBS_IsFirstStenocardia',
					'label' => 'Впервые возникшая стенокардия',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['isnostablestenocardia'] = array(
					'field' => 'MorbusIBS_IsNoStableStenocardia',
					'label' => 'Нестабильная (прогрессирующая) стенокардия',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['isrisets'] = array(
					'field' => 'MorbusIBS_IsRiseTS',
					'label' => 'Подъем TS',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['issaveischemia'] = array(
					'field' => 'MorbusIBS_IsSaveIschemia',
					'label' => 'Сохраняются и/или рецидивируют признаки ишемии',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['isbackstenocardia'] = array(
					'field' => 'MorbusIBS_IsBackStenocardia',
					'label' => 'Возврат стенокардии',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['isshunting'] = array(
					'field' => 'MorbusIBS_IsShunting',
					'label' => 'Перенесенная операция коронарного шунтирования',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['isstenting'] = array(
					'field' => 'MorbusIBS_IsStenting',
					'label' => 'Перенесенная операция коронарного стентирования',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['iskgindication'] = array(
					'field' => 'MorbusIBS_IsKGIndication',
					'label' => 'Показано проведение КГ',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['uslugacomplex_id'] = array(
					'field' => 'UslugaComplex_id',
					'label' => 'Код услуги КГ',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['iskgconsent'] = array(
					'field' => 'MorbusIBS_IsKGConsent',
					'label' => 'Получено соглание на проведение КГ',
					'rules' => 'trim',
					'type' => 'id'
				);
				$rules['iskgfinished'] = array(
					'field' => 'MorbusIBS_IsKGFinished',
					'label' => 'Проведена КГ',
					'rules' => 'trim',
					'type' => 'id'
				);
				break;
			case self::SCENARIO_LOAD_EDIT_FORM:
				$rules[self::ID_KEY] = array(
					'field' => 'Morbus_id',
					'rules' => 'trim|required',
					'label' => 'Идентификатор',
					'type' => 'id'
				);
				break;
		}
		return $rules;
	}

	/**
	 * construct
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * @return string
	 */
	function getMorbusTypeSysNick()
	{
		return 'ibs';
	}

	/**
	 * @return int
	 * @throws Exception
	 */
	function getMorbusTypeId()
	{
		if (empty($this->_MorbusType_id)) {
			$this->load->library('swMorbus');
			$this->_MorbusType_id = swMorbus::getMorbusTypeIdBySysNick($this->getMorbusTypeSysNick());
			if (empty($this->_MorbusType_id)) {
				throw new Exception('Не удалось определить тип заболевания', 500);
			}
		}
		return $this->_MorbusType_id;
	}

	/**
	 * @return string
	 */
	function getGroupRegistry()
	{
		return 'IBSRegistry';
	}

	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName()
	{
		return 'MorbusIBS';
	}

	/**
	 * Удаление данных специфик заболевания заведенных из регистра, когда заболевание нельзя удалить
	 *
	 * Вызывается из swMorbus::onBeforeDeletePersonRegister
	 * @param PersonRegister_model $model
	 * @param array $data
	 * @throws Exception Если выбросить исключение, то будет отменено удаление записи регистра
	 */
	public function onBeforeDeletePersonRegister(PersonRegister_model $model, $data)
	{
		// тут должно быть реализовано удаление данных введенных в разделах специфики заболевания,
		// в которых нет ссылки на Evn
		// если таковых разделов нет, то этот метод можно убрать
	}

	/**
	 * Удаление данных специфик заболевания заведенных в учетном документе, когда заболевание нельзя удалить
	 *
	 * Вызывается из swMorbus::onBeforeDeleteEvn
	 * @param EvnAbstract_model $evn
	 * @param array $data
	 * @throws Exception Если выбросить исключение, то будет отменено удаление учетного документа
	 */
	public function onBeforeDeleteEvn(EvnAbstract_model $evn, $data)
	{
		// тут должно быть реализовано удаление данных введенных в разделах специфики заболевания,
		// в которых есть ссылка на Evn
		// если таковых нет, то этот метод можно убрать
	}

	/**
	 * Получение списка пользователей регистра
	 */
	function loadUsersRegistry($data, $group)
	{
		if (empty($group)) {
			$group = $this->groupRegistry;
		}
		$query = "
			select PMUser_id
			from v_pmUserCache (nolock)
			where pmUser_groups like '%{$group}%'
				and Lpu_id = ?
		";
		$result = $this->db->query($query, array($data['Lpu_id']));
		$response = $result->result('array');
		if ( is_array($response) && count($response) > 0 ) {
			return $response;
		} else	{
			return false;
		}
	}

 	/**
	 * Проверка обязательных параметров специфики
	 *
	 * @params Mode 
	 *	- check_by_personregister - это создание нового заболевания при ручном вводе новой записи регистра из формы "Регистр по ..." (если есть открытое заболевание, то ничего не сохраняем. В регистре сохранится связь с открытым или созданным заболевание)
	 *	- personregister_viewform - это ввод данных специфики из панели просмотра в форме записи регистра
	 *	- evnsection_viewform - это ввод данных специфики из панели просмотра движения в ЭМК
	 *	- evnvizitpl_viewform - это ввод данных специфики из панели просмотра посещения в ЭМК
	 *	- check_by_evn - это создание нового заболевания при редактировании данных движения/посещения (если есть открытое заболевание и диагноз уточнился и посещение/движение актуально, то сохраняем диагноз и привязываем заболевание к этому посещению/движению)
	 *	- persondisp_form - это ввод данных специфики в форме "Диспансерные карты пациентов: Добавление / Редактирование"
	 */
	protected function checkParams($data)
	{
		if ( empty($data['Mode']) ) {
			throw new Exception('Не указан режим сохранения');
		}
		$fields = array(
			'Diag_id' => 'Идентификатор диагноза'
			,'Person_id' => 'Идентификатор человека'
			,'Evn_pid' => 'Идентификатор движения/посещения'
			,'pmUser_id' => 'Идентификатор пользователя'
			,'Morbus_id' => 'Идентификатор заболевания'
			,'MorbusIBS_id' => 'Идентификатор специфики заболевания'
		);
		switch ($data['Mode']) {
			case 'check_by_evn':
				$check_fields_list = array('Evn_pid','Diag_id','Person_id','pmUser_id');
				break;
			case 'check_by_personregister':
				$check_fields_list = array('Diag_id','Person_id','pmUser_id');
				$data['Evn_pid'] = null;
				break;
			case 'persondisp_form':
			case 'personregister_viewform':
				$check_fields_list = array('MorbusIBS_id','Morbus_id','Person_id','pmUser_id');
				$data['Evn_pid'] = null;
				break;
			case 'evnsection_viewform':
			case 'evnvizitpl_viewform':
				$check_fields_list = array('MorbusIBS_id','Morbus_id','Evn_pid','pmUser_id');
				break;
			default:
				throw new Exception('Указан неправильный режим сохранения');
				break;
		}
		$errors = array();
		foreach($check_fields_list as $field) {
			if( empty($data[$field]) )
			{
				$errors[] = 'Не указан '. $fields[$field];
			}
		}
		if( count($errors) > 0 ) {
			throw new Exception(implode('<br />',$errors));
		}
		return $data;
	}

	/**
	 * Сохранение полей записи регистра из формы "Диспансерные карты пациентов: Добавление / Редактирование"
	 *
	 * @param array $data
	 * @return array Идентификаторы заболевания, специфики заболевания или ошибка
	 * @throws Exception
	 */
	function doSavePersonDispForm($data)
	{
		return array(array('Error_Msg' => 'не реализовано'));
	}

	/**
	 * Сохранение
	 *
	 * @param array $data Обязательные параметры:
	 * 1) Evn_pid или пара Person_id и Diag_id
	 * 2) pmUser_id
	 * 3) Mode
	 * @param bool $isAllowTransaction
	 * @return array Идентификаторы заболевания, специфики заболевания или ошибка
	 * @throws Exception
	 * @comment Будут сохранены те данные, которые переданы, т.е. можно отдельные параметры сохранять
	 */
	public function doSave($data = array(), $isAllowTransaction = true) {
		try {
			$this->isAllowTransaction = false;
			$data = $this->checkParams($data);

			$data['Evn_aid'] = null;
			if (in_array($data['Mode'],array(
				'evnsection_viewform','evnvizitpl_viewform'
			))) {
				// Проверка существования у человека актуального учетного документа с данной группой диагнозов для привязки к нему заболевания и определения последнего диагноза заболевания
				if (empty($data['Evn_pid'])) {
					$data['Evn_pid'] = null;
				}
				if (empty($data['Person_id'])) {
					$data['Person_id'] = null;
				}
				if (empty($data['Diag_id'])) {
					$data['Diag_id'] = null;
				}
				$this->load->library('swMorbus');
				$result = swMorbus::getStaticMorbusCommon()->loadLastEvnData($this->getMorbusTypeSysNick(), $data['Evn_pid'], $data['Person_id'], $data['Diag_id']);
				if ( !empty($result) ) {
					//учетный документ найден
					$data['Evn_aid'] = $result[0]['Evn_id'];
					$data['Diag_id'] = $result[0]['Diag_id'];
					$data['Person_id'] = $result[0]['Person_id'];
				} else {
					throw new Exception('Ошибка определения актуального учетного документа с данным заболеванием');
				}
			}
			$data['Diag_nid'] = $data['Diag_id'];
            if (!empty($data['MorbusIBS_FuncClass']) && !in_array($data['MorbusIBS_FuncClass'], array(1,2,3,4))) {
                throw new Exception('Указан неправильный Функциональный класс');
            }
            if (!empty($data['MorbusIBS_StressTest']) && !in_array($data['MorbusIBS_StressTest'], array(1,2))) {
                throw new Exception('Указан неправильный Стресс-тест');
            }
			
			if (in_array($data['Mode'],array(
				'personregister_viewform','persondisp_form'
			)) || $data['Evn_pid'] == $data['Evn_aid']) {
				// Если редактирование происходит из актуального учетного документа
				// или из панели просмотра в форме записи регистра, то сохраняем данные
				// Стартуем транзакцию
				$this->isAllowTransaction = $isAllowTransaction;
				if ( !$this->beginTransaction() ) {
					$this->isAllowTransaction = false;
					throw new Exception('Ошибка при попытке запустить транзакцию');
				}

				//update таблиц Morbus, MorbusIBS
				$tmp = $this->updateMorbusSpecific($data);
				if ( isset($tmp[0]['Error_Msg']) ) {
					throw new Exception($tmp[0]['Error_Msg']);
				}
				$response = $tmp;
				if (!empty($data['Evn_pid']) && !empty($data['Morbus_id'])) {
					$this->load->library('swMorbus');
					$tmp = swMorbus::updateMorbusIntoEvn(array(
						'Evn_id' => $data['Evn_pid'],
						'Morbus_id' => $data['Morbus_id'],
						'session' => $data['session'],
						'mode' => 'onAfterSaveMorbusSpecific',
					));
					if (isset($tmp['Error_Msg'])) {
						//нужно откатить транзакцию
						throw new Exception($tmp['Error_Msg']);
					}
				}
				
				$this->commitTransaction();
				return $response;
			} else {
				//Ничего не сохраняем
				throw new Exception('Данные не были сохранены, т.к. данный учетный документ не является актуальным для данного заболевания.');
			}
		} catch (Exception $e) {
			$this->rollbackTransaction();
			return array(array('Error_Msg' => 'Сохранение специфики заболевания. <br />'. $e->getMessage()));	
		}
	}

	/**
	 * Применение данных извещения
	 * @param EvnNotifyIBS_model $evn
	 * @return array
	 * @throws Exception
	 */
	function setEvnNotifyIBS(EvnNotifyIBS_model $evn)
	{
		$morbus = $this->getFirstRowFromQuery('
			select top 1 MorbusBase_id, MorbusIBS_id
			from v_MorbusIBS (nolock)
			where Morbus_id = :Morbus_id
		', array('Morbus_id' => $evn->Morbus_id));
		if (empty($morbus)) {
			throw new Exception('Не удалось получить данные заболевания!', 500);
		}
		$data = array(
			'MorbusBase_id' => $morbus['MorbusBase_id'],

			'Morbus_id' => $evn->Morbus_id,
			'Diag_id' => $evn->Diag_id,

			'MorbusIBS_id' => $morbus['MorbusIBS_id'],
			'Diag_nid' => $evn->Diag_id,
			'IBSType_id' => $evn->IBSType_id,

			'pmUser_id' => $evn->promedUserId,
		);
		if (!empty($evn->IsStenocardia)) {
			$data['MorbusIBS_IsStenocardia'] = $evn->IsStenocardia;
		}
		return $this->updateMorbusSpecific($data);
	}

	/**
	 * Сохранение специфики
	 * @param $data
	 * @return array
	 * @comment Будут сохранены те данные, которые переданы, т.е. можно отдельные параметры сохранять
	 */
	private function updateMorbusSpecific($data) {
		$err_arr = array();
		$entity_saved_arr = array();
		$not_edit_fields = array('Evn_pid', 'Person_id','MorbusIBS_id', 'Morbus_id', 'MorbusBase_id','MorbusType_id','Morbus_disDT','MorbusBase_disDT');
		if (isset($data['field_notedit_list']) && is_array($data['field_notedit_list'])) {
			$not_edit_fields = array_merge($not_edit_fields,$data['field_notedit_list']);
		}

		foreach($this->entityFields as $entity => $l_arr) {

			$allow_save = false;
			foreach($data as $key => $value) {
				if (in_array($key, $l_arr) && !in_array($key, $not_edit_fields)) {
					$allow_save = true;
					break;
				}
			}
			if ( $allow_save && !empty($data[$entity.'_id']) ) {
				$q = 'select top 1 '. implode(', ',$l_arr) .' from dbo.v_'. $entity .' WITH (NOLOCK) where '. $entity .'_id = :'. $entity .'_id';
				$p = array($entity.'_id' => $data[$entity.'_id']);
				$r = $this->db->query($q, $data);
				//echo getDebugSQL($q, $data);
				if (is_object($r)) {
					$result = $r->result('array');
					if( empty($result) || !is_array($result[0]) || count($result[0]) == 0 ) {
						$err_arr[] = 'Получение данных '. $entity .' По идентификатору '. $data[$entity.'_id'] .' данные не получены';
						continue;
					}
					foreach($result[0] as $key => $value) {
						if (is_object($value) && $value instanceof DateTime)
						{
							$value = $value->format('Y-m-d H:i:s');
						}
						//в $data[$key] может быть null
						$p[$key] = array_key_exists($key, $data)?$data[$key]:$value;
						// ситуация, когда пользователь удалил какое-то значение
						$p[$key] = (empty($p[$key]) || $p[$key]=='0')?null:$p[$key];
					}
				} else {
					$err_arr[] = 'Получение данных '. $entity .' Ошибка при выполнении запроса к базе данных';
					continue;
				}
				$field_str = '';
				foreach($l_arr as $key) {
					$field_str .= '
						@'. $key .' = :'. $key .',';
				}
				$q = '
					declare
						@'. $entity .'_id bigint,
						@ErrCode int,
						@ErrMessage varchar(4000);
					set @'. $entity .'_id = :'. $entity .'_id;
					exec dbo.p_'. $entity .'_upd
						@'. $entity .'_id = @'. $entity .'_id output, '. $field_str .'
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @'. $entity .'_id as '. $entity .'_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				';
				$p['pmUser_id'] = $data['pmUser_id'];
				$r = $this->db->query($q, $p);
				if (is_object($r)) {
					$result = $r->result('array');
					if( !empty($result[0]['Error_Msg']) )
					{
						$err_arr[] = 'Сохранение данных '. $entity .' '. $result[0]['Error_Msg'];
						continue;
					}
					$entity_saved_arr[$entity .'_id'] = $data[$entity.'_id'];
				} else {
					$err_arr[] = 'Сохранение данных '. $entity .' Ошибка при выполнении запроса к базе данных';
					continue;
				}
			} else {
				continue;
			}
		}
		$entity_saved_arr['Morbus_id'] = $data['Morbus_id'];
		$entity_saved_arr['Error_Msg'] = (count($err_arr) > 0) ? implode('<br />',$err_arr) : null;
		return array($entity_saved_arr);
	}

	/**
	 * Создание заболевания с проверкой на существование заболевания у человека
	 */
	function checkByPersonDispForm($data)
	{
		/*
		//проверка диагноза
		$this->load->library('swMorbus');
		$this->_saveResponse['MorbusType_id'] = $this->getMorbusTypeId();
		$arr = swMorbus::getMorbusTypeListByDiag($data['Diag_id']);
		if ( empty($arr[$this->getMorbusTypeId()]) ) {
			return $this->_saveResponse;
		}
		try {
			$this->load->library('swMorbus');
			$data['Morbus_setDT'] = $this->currentDT->format('Y-m-d');
			$tmp = swMorbus::checkByPersonRegister($this->getMorbusTypeSysNick(), $data, 'onBeforeSavePersonRegister');
		} catch (Exception $e) {
			$this->_saveResponse['Error_Msg'] = $e->getMessage();
			return $this->_saveResponse;
		}
		$this->_saveResponse = array_merge($tmp, $this->_saveResponse);
		//проверка наличия извещения, записи регистра
		$query = "
			select top 1 EN.EvnNotifyNephro_id, PR.PersonRegister_id
			from v_Morbus M with (nolock)
			left join v_EvnNotifyNephro EN with (nolock) on M.Morbus_id = EN.Morbus_id
			left join v_PersonRegister PR with (nolock) on M.Morbus_id = PR.Morbus_id
				and PR.PersonRegisterOutCause_id is null
			where M.Morbus_id = ?
		";
		$res = $this->db->query($query, array($this->_saveResponse['Morbus_id']));
		if ( is_object($res) ) {
			$tmp = $res->result('array');
			return array_merge($tmp[0], $this->_saveResponse);
		} else {
			$this->_saveResponse['Error_Msg'] = 'Не удалось выполнить проверку наличия извещения, записи регистра';
			return $this->_saveResponse;
		}*/
		return $this->_saveResponse;
	}

	/**
	 * Создание специфики заболевания
	 * Должно выполняться внутри транзакции
	 * @param array $data
	 * @param bool $isAllowTransaction
	 * @return array
	 * @throws Exception
	 */
	public function autoCreate($data, $isAllowTransaction = true)
	{
		if (empty($data['MorbusBase_id']) ||empty($data['Person_id'])
			|| empty($data['Morbus_id']) || empty($data['Diag_id']) || empty($data['Morbus_setDT'])
			|| empty($data['mode'])
			|| false == in_array($data['mode'], array('onBeforeViewData', 'onBeforeSavePersonRegister', 'onBeforeSaveEvnNotify'))
		) {
			throw new Exception('Переданы неправильные параметры', 500);
		}
		$this->setParams($data);
		$tableName = $this->tableName();
		$queryParams = array();
		$queryParams['pmUser_id'] = $this->promedUserId;
		$queryParams['Morbus_id'] = $data['Morbus_id'];
		$queryParams['Person_id'] = $data['Person_id'];
		$queryParams['Diag_nid'] = $data['Diag_id'];

		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@Morbus_id bigint = :Morbus_id,
				@MorbusIBS_id bigint = null,
				@pmUser_id bigint = :pmUser_id;

			-- должно быть одно на Morbus
			select top 1 @MorbusIBS_id = MorbusIBS_id from v_MorbusIBS with (nolock) where Morbus_id = @Morbus_id

			if isnull(@MorbusIBS_id, 0) = 0
			begin
				exec p_MorbusIBS_inscalc
					@MorbusIBS_id = @MorbusIBS_id output,
					@Morbus_id = @Morbus_id,
					@Person_id = :Person_id,
					@Diag_nid = :Diag_nid,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;

				select @MorbusIBS_id as MorbusIBS_id, 2 as IsCreate, @Error_Code as Error_Code, @Error_Message as Error_Msg;
			end
			else
			begin
				select @MorbusIBS_id as MorbusIBS_id, 1 as IsCreate, @Error_Code as Error_Code, @Error_Message as Error_Msg;
			end
		";
		//echo getDebugSQL($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);
		if ( !is_object($result) ) {
			throw new Exception('Ошибка БД', 500);
		}
		$resp = $result->result('array');
		if (!empty($resp[0]['Error_Msg'])) {
			throw new Exception($resp[0]['Error_Msg'], 500);
		}
		if (empty($resp[0][$tableName . '_id'])) {
			throw new Exception('Что-то пошло не так', 500);
		}
		$this->_saveResponse[$tableName . '_id'] = $resp[0][$tableName . '_id'];

		if ( 2 == $resp[0]['IsCreate'] ) {
		}
		return $this->_saveResponse;
	}

	/**
	 * Метод получения данных для панели просмотра
	 * При вызове из формы просмотра записи регистра параметр MorbusIBS_pid будет содержать Person_id, также будет передан PersonRegister_id
	 * При вызове из формы просмотра движения/посещения параметр MorbusIBS_pid будет содержать Evn_id просматриваемого движения/посещения
	 */
	function getViewData($data)
	{
		if (empty($data['session'])) { $data['session'] = null; }
		if (empty($data['MorbusIBS_pid'])) { $data['MorbusIBS_pid'] = null; }
		if (empty($data['PersonRegister_id'])) { $data['PersonRegister_id'] = null; }
		$this->load->library('swMorbus');
		$params = swMorbus::onBeforeViewData($this->getMorbusTypeSysNick(), $data['session'], $data['MorbusIBS_pid'], $data['PersonRegister_id']);
		if ($params['Error_Msg']) {
			throw new Exception($params['Error_Msg']);
		}
		$params['MorbusIBS_pid'] = $data['MorbusIBS_pid'];
		$query = "
			select
				" . swMorbus::getAccessTypeQueryPart('MV', 'MB', 'MorbusIBS_pid', '1', '0') . ",
				ndst.IBSType_id,
				ndst.IBSType_Name,
				v_Diag.Diag_id,
				v_Diag.Diag_FullName as Diag_Name,
				MV.MorbusIBS_IsStenocardia,
				IsStenocardia.YesNo_Name as IsStenocardia_Name,
				MV.MorbusIBS_FuncClass,
				MV.MorbusIBS_StressTest as IBSStressTest_id,
				st.TubHistolResultType_Name as IBSStressTest_Name,
				MV.MorbusIBS_IsEchocardiography,
				IsEchocardiography.YesNo_Name as IsEchocardiography_Name,
				MV.MorbusIBS_IsHalterMonitor,
				IsHalterMonitor.YesNo_Name as IsHalterMonitor_Name,
				MV.MorbusIBS_IsMyocardInfarct,
				IsMyocardInfarct.YesNo_Name as IsMyocardInfarct_Name,
				MV.MorbusIBS_IsMyocardIschemia,
				IsMyocardIschemia.YesNo_Name as IsMyocardIschemia_Name,
				MV.MorbusIBS_IsFirstStenocardia,
				IsFirstStenocardia.YesNo_Name as IsFirstStenocardia_Name,
				MV.MorbusIBS_IsNoStableStenocardia,
				IsNoStableStenocardia.YesNo_Name as IsNoStableStenocardia_Name,
				MV.MorbusIBS_IsRiseTS,
				IsRiseTS.YesNo_Name as IsRiseTS_Name,
				MV.MorbusIBS_IsSaveIschemia,
				IsSaveIschemia.YesNo_Name as IsSaveIschemia_Name,
				MV.MorbusIBS_IsBackStenocardia,
				IsBackStenocardia.YesNo_Name as IsBackStenocardia_Name,
				MV.MorbusIBS_IsShunting,
				IsShunting.YesNo_Name as IsShunting_Name,
				MV.MorbusIBS_IsStenting,
				IsStenting.YesNo_Name as IsStenting_Name,
				MV.MorbusIBS_IsKGIndication,
				IsKGIndication.YesNo_Name as IsKGIndication_Name,
				MV.UslugaComplex_id,
				uc.UslugaComplex_Code as UslugaComplex_Name,
				MV.MorbusIBS_IsKGConsent,
				IsKGConsent.YesNo_Name as IsKGConsent_Name,
				MV.MorbusIBS_IsKGFinished,
				IsKGFinished.YesNo_Name as IsKGFinished_Name,

				MV.MorbusIBS_id,
				MV.Morbus_id,
				MB.MorbusBase_id,
				:MorbusIBS_pid as MorbusIBS_pid,
				MB.Person_id
			from
				v_MorbusIBS MV with (nolock)
				inner join v_MorbusBase MB with (nolock) on MB.MorbusBase_id = MV.MorbusBase_id
				inner join v_Diag with (nolock) on v_Diag.Diag_id = MV.Diag_nid
				left join v_IBSType ndst with (nolock) on ndst.IBSType_id = MV.IBSType_id
				left join v_UslugaComplex uc with (nolock) on uc.UslugaComplex_id = MV.UslugaComplex_id
				left join v_TubHistolResultType st with (nolock) on st.TubHistolResultType_id = MV.MorbusIBS_StressTest
				left join v_YesNo IsStenocardia with (nolock) on IsStenocardia.YesNo_id = MV.MorbusIBS_IsStenocardia
				left join v_YesNo IsBackStenocardia with (nolock) on IsBackStenocardia.YesNo_id = MV.MorbusIBS_IsBackStenocardia
				left join v_YesNo IsEchocardiography with (nolock) on IsEchocardiography.YesNo_id = MV.MorbusIBS_IsEchocardiography
				left join v_YesNo IsFirstStenocardia with (nolock) on IsFirstStenocardia.YesNo_id = MV.MorbusIBS_IsFirstStenocardia
				left join v_YesNo IsHalterMonitor with (nolock) on IsHalterMonitor.YesNo_id = MV.MorbusIBS_IsHalterMonitor
				left join v_YesNo IsKGConsent with (nolock) on IsKGConsent.YesNo_id = MV.MorbusIBS_IsKGConsent
				left join v_YesNo IsKGFinished with (nolock) on IsKGFinished.YesNo_id = MV.MorbusIBS_IsKGFinished
				left join v_YesNo IsKGIndication with (nolock) on IsKGIndication.YesNo_id = MV.MorbusIBS_IsKGIndication
				left join v_YesNo IsMyocardInfarct with (nolock) on IsMyocardInfarct.YesNo_id = MV.MorbusIBS_IsMyocardInfarct
				left join v_YesNo IsMyocardIschemia with (nolock) on IsMyocardIschemia.YesNo_id = MV.MorbusIBS_IsMyocardIschemia
				left join v_YesNo IsNoStableStenocardia with (nolock) on IsNoStableStenocardia.YesNo_id = MV.MorbusIBS_IsNoStableStenocardia
				left join v_YesNo IsRiseTS with (nolock) on IsRiseTS.YesNo_id = MV.MorbusIBS_IsRiseTS
				left join v_YesNo IsSaveIschemia with (nolock) on IsSaveIschemia.YesNo_id = MV.MorbusIBS_IsSaveIschemia
				left join v_YesNo IsShunting with (nolock) on IsShunting.YesNo_id = MV.MorbusIBS_IsShunting
				left join v_YesNo IsStenting with (nolock) on IsStenting.YesNo_id = MV.MorbusIBS_IsStenting
			where
				MV.Morbus_id = :Morbus_id
		";
		//echo getDebugSql($query, $params);die();
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Читает одну строку для формы редактирования
	 * @param array $data
	 * @return array
	 */
	function doLoadEditForm($data)
	{
		$data['scenario'] = self::SCENARIO_LOAD_EDIT_FORM;
		$params = array(
			'Morbus_id' => $data['Morbus_id']
		);
		$query = "
			select top 1
				case when MV.Morbus_disDT is null then 1 else 0 end as accessType,
				MV.IBSType_id,				
				MV.Diag_id,
				MV.MorbusIBS_IsStenocardia,
				MV.MorbusIBS_FuncClass,
				MV.MorbusIBS_StressTest as IBSStressTest_id,
				MV.MorbusIBS_IsEchocardiography,
				MV.MorbusIBS_IsHalterMonitor,
				MV.MorbusIBS_IsMyocardInfarct,
				MV.MorbusIBS_IsMyocardIschemia,
				MV.MorbusIBS_IsFirstStenocardia,
				MV.MorbusIBS_IsNoStableStenocardia,
				MV.MorbusIBS_IsRiseTS,
				MV.MorbusIBS_IsSaveIschemia,
				MV.MorbusIBS_IsBackStenocardia,
				MV.MorbusIBS_IsShunting,
				MV.MorbusIBS_IsStenting,
				MV.MorbusIBS_IsKGIndication,
				MV.UslugaComplex_id,
				MV.MorbusIBS_IsKGConsent,
				MV.MorbusIBS_IsKGFinished,
				
				MV.MorbusIBS_id,
				MV.Morbus_id,
				MB.MorbusBase_id,
				MB.Person_id
			from
				v_MorbusIBS MV with (nolock)
				inner join v_MorbusBase MB with (nolock) on MB.MorbusBase_id = MV.MorbusBase_id
			where
				MV.Morbus_id = :Morbus_id
		";
		//echo getDebugSql($query, $params);die();
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return array();
		}
	}

	/**
	 * Вывод печатной формы «Карта динамического наблюдения»
	 */
	function doPrint($data)
	{
		$parse_data = $this->loadPrintData($data['Morbus_id']);
		if (empty($parse_data)) {
			return 'Не удалось получить данные карты';
		}
		$this->load->library('parser');
		return $this->parser->parse('print_IBSregistry', $parse_data, true);
	}

	/**
	 * Получаем данные для печатной формы
	 */
	protected function loadPrintData($id)
	{
		$query = "
			select top 1
				MN.MorbusIBS_id,
				Diag.Diag_Code,
				Diag.Diag_Name,
				IBSType.IBSType_Name,
				PR.PersonRegister_id as PersonRegister_Num,
				Sex.Sex_Code,
				Sex.Sex_Name,
				a.Address_Address as Person_Address,
				convert(varchar(10), PS.Person_BirthDay, 104) as Person_BirthDay,
				dbo.Age2(PS.Person_BirthDay, dbo.tzGetDate()) as Person_Age,
				PS.Person_Phone,
				PS.Person_SurName,
				PS.Person_FirName,
				PS.Person_SecName
			from
				v_MorbusIBS MN with (nolock)
				inner join v_MorbusBase MB with (nolock) on MB.MorbusBase_id = MN.MorbusBase_id
				inner join v_PersonState PS with (nolock) on PS.Person_id = MB.Person_id
				inner join Diag with (nolock) on Diag.Diag_id = MN.Diag_nid
				left join Sex with (nolock) on Sex.Sex_id = PS.Sex_id
				left join v_Address a (nolock) on a.Address_id = ISNULL(PS.PAddress_id,PS.UAddress_id)
				left join IBSType with (nolock) on IBSType.IBSType_id = MN.IBSType_id
				left join v_PersonRegister PR with (nolock) on PR.Morbus_id = MN.Morbus_id
			where
				EN.Morbus_id = ?
		";
		$res = $this->db->query($query, array($id));
		if ( is_object($res) ) {
			$tmp = $res->result('array');
			if (is_array($tmp) && !empty($tmp)) {
				return $tmp[0];
			}
		}
		return array();
	}
}

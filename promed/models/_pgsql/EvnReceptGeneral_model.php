<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package      PromedWeb
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @link         http://swan.perm.ru/PromedWeb
 */

require_once('EvnAbstract_model.php');

/**
 * EvnReceptGeneral_model Выписка простого (не льготного) рецепта
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2015 Swan Ltd.
 * @author       Александр Пермяков
 * @version      04.2015
 *
 * @property-read int $rid КВС или ТАП
 * @property int $pid Движение в отделении или посещение
 * @property int $EvnCourseTreatDrug_id Медикамент курса лекарственного лечения
 * @property string $num номер рецепта
 * @property string $ser серия рецепта
 * @property int $Diag_id диагноз
 * @property int $ReceptValid_id актуальность рецепта
 * @property float $kolvo Количество ЛС
 * @property int $MedPersonal_id врач, выписавший рецепт
 * @property int $LpuSection_id отделение МО врача, выписавшего рецепт
 * @property int $ReceptType_id тип выписки рецепта
 * @property int $isMnn признак выписки по МНН
 * @property string $signa схема приема медикамента
 * @property int $Person_Age возраст
 * @property int $DrugComplexMnn_id Комплексное МНН РЛС
 * @property int $Drug_rlsid Медикамент РЛС
 * @property int $DrugFinance_id Источник финансирования
 * @property int $WhsDocumentCostItemType_id Статья расхода
 * @property int $ReceptForm_id Форма рецепта
 * @property int $ReceptDiscount_id процент скидки в рецепте
 * @property int $ReceptFinance_id тип финансирования
 * @property int $PrivilegeType_id тип льготы
 * @property int $isKEK признак КЭК (медикамент выписывается через врачебную комиссию)
 * @property string $udostSer серия удостоверения
 * @property string $udostNum номер удостоверения
 * @property int $Drug_id Медикамент DBO.Drug
 * @property int $isInReg
 * @property int $OrgFarmacy_id Аптека обращения
 * @property int $isNotOstat признак отсутствия остатков
 * @property DateTime $obrDT дата обращения
 * @property DateTime $otpDT дата отпуска рецепта в аптеке
 * @property int $ReceptDelayType_id статус рецепта
 * @property int $OrgFarmacy_oid Аптека, отпустившая рецепт
 * @property int $Drug_oid Отпущенный медикамент DBO.Drug
 * @property float $oKolvo количество, отпускаемое в аптеке
 * @property float $oPrice цена, отпускаемая в аптеке
 * @property int $DrugRequestRow_id строка заявки медикаментов
 * @property string $extempContents состав медикамента
 * @property int $isExtemp экстемпоральный
 * @property int $is7Noz признак выписки по 7ми нозологиям
 * @property int $ReceptRemoveCauseType_id Причина удаления рецепта
 * @property int $PrepSeries_id Серия выпуска ЛС
 * @property int $WhsDocumentUc_id
 * @property int $isOtherDiag Другие показания к применению
 *
 * @property-read array $receptFormStore
 * @property-read string $receptFormCode Код формы рецепта
 * @property-read array $receptFormCodePrivelegeList
 * @property-read array $receptFormCodeNotPrivelegeList
 * @property-read array $whsDocumentCostItemTypeStore
 * @property-read int $whsDocumentCostItemTypeCode Код статьи расхода
 * @property-read array $receptValidStore
 * @property-read int $receptValidCode Код продолжительности актуальности рецепта
 */
class EvnReceptGeneral_model extends EvnAbstract_model
{
	private static $_receptValidStore = array();
	private static $_receptFormStore = array();
	private static $_whsDocumentCostItemTypeStore = array();

	/**
	 * Определение идентификатора класса события
	 * @return int
	 */
	static function evnClassId()
	{
		return 180;
	}

	/**
	 * Определение кода класса события
	 * @return string
	 */
	static function evnClassSysNick()
	{
		return 'EvnReceptGeneral';
	}

	/**
	 * @param string $enum_name
	 * @param int|string $code
	 * @return int
	 * @throws Exception
	 */
	function loadEnumValueByCode($enum_name, $code)
	{
		$row = $this->loadEnumRowByCode($enum_name, $code);
		return $row[$enum_name . '_id'];
	}

	/**
	 * @param string $enum_name
	 * @param int|string $code
	 * @return array
	 * @throws Exception
	 */
	function loadEnumRowByCode($enum_name, $code)
	{
		switch ($enum_name) {
			case 'ReceptValid':
				$store = $this->receptValidStore;
				break;
			case 'ReceptForm':
				$store = $this->receptFormStore;
				break;
			case 'WhsDocumentCostItemType':
				$store = $this->whsDocumentCostItemTypeStore;
				break;
			default:
				$store = null;
				break;
		}
		if (empty($store)) {
			throw new Exception('Не реализована загрузка значений перечисления ' . $enum_name, 500);
		}
		foreach ($store as $row) {
			if ($code == $row[$enum_name . '_Code']) {
				return $row;
			}
		}
		throw new Exception("Не найдено значение перечисления {$enum_name} по коду {$code}", 500);
	}

	/**
	 * @return array
	 * @throws Exception
	 */
	function getReceptValidStore()
	{
		if (empty(self::$_receptValidStore)) {
			$result = $this->db->query('
			select 
				ReceptValid_id as \"ReceptValid_id\", 
				ReceptValid_Code as \"ReceptValid_Code\", 
				ReceptValid_Name as \"ReceptValid_Name\" 
			from v_ReceptValid');
			if (false == is_object($result)) {
				throw new Exception('Не удалось загрузить перечисление продолжительности актуальности рецепта', 500);
			}
			self::$_receptValidStore = $result->result('array');
			if (false == is_array(self::$_receptValidStore) || empty(self::$_receptValidStore)) {
				throw new Exception('Ошибка при загрузке перечисления продолжительности актуальности рецепта', 500);
			}
		}
		return self::$_receptValidStore;
	}

	/**
	 * @param int $id По умолчанию будет использован ReceptValid_id этого экземпляра
	 * @return bool|int
	 */
	function getReceptValidCode($id = null)
	{
		if (empty($id)) {
			$id = $this->ReceptValid_id;
		}
		foreach ($this->receptValidStore as $row) {
			if ($id == $row['ReceptValid_id']) {
				return $row['ReceptValid_Code'] + 0;
			}
		}
		return false;
	}

	/**
	 * @return array
	 * @throws Exception
	 */
	function getWhsDocumentCostItemTypeStore()
	{
		if (empty(self::$_whsDocumentCostItemTypeStore)) {
			$result = $this->db->query('select * from v_WhsDocumentCostItemType');
			if (false == is_object($result)) {
				throw new Exception('Не удалось загрузить перечисление статей расхода', 500);
			}
			self::$_whsDocumentCostItemTypeStore = $result->result('array');
			if (false == is_array(self::$_whsDocumentCostItemTypeStore) || empty(self::$_whsDocumentCostItemTypeStore)) {
				throw new Exception('Ошибка при загрузке перечисления статей расхода', 500);
			}
		}
		return self::$_whsDocumentCostItemTypeStore;
	}

	/**
	 * @param int $id По умолчанию будет использован WhsDocumentCostItemType_id этого экземпляра
	 * @return bool|int
	 */
	function getWhsDocumentCostItemTypeCode($id = null)
	{
		if (empty($id)) {
			$id = $this->WhsDocumentCostItemType_id;
		}
		foreach ($this->whsDocumentCostItemTypeStore as $row) {
			if ($id == $row['WhsDocumentCostItemType_id']) {
				return $row['WhsDocumentCostItemType_Code'] + 0;
			}
		}
		return false;
	}

	/**
	 * @return array
	 * @throws Exception
	 */
	function getReceptFormStore()
	{
		if (empty(self::$_receptFormStore)) {
			$result = $this->db->query('
			select
				ReceptForm_id as \"ReceptForm_id\",
				ReceptForm_Code as \"ReceptForm_Code\",
				ReceptForm_Name as \"ReceptForm_Name\",
				ReceptForm_IsPrivilege as \"ReceptForm_IsPrivilege\",
				ReceptForm_begDate as \"ReceptForm_begDate\",
				ReceptForm_endDate as \"ReceptForm_endDate\"
				from v_ReceptForm'
			);
			if (false == is_object($result)) {
				throw new Exception('Не удалось загрузить перечисление форм рецептов', 500);
			}
			self::$_receptFormStore = $result->result('array');
			if (false == is_array(self::$_receptFormStore) || empty(self::$_receptFormStore)) {
				throw new Exception('Ошибка при загрузке перечисления форм рецептов', 500);
			}
		}
		return self::$_receptFormStore;
	}

	/**
	 * @return array
	 */
	function getReceptFormCodePrivelegeList()
	{
		$response = array();
		foreach ($this->receptFormStore as $row) {
			if (2 == $row['ReceptForm_IsPrivilege']) {
				$response[] = $row['ReceptForm_Code'];
			}
		}
		return $response;
	}

	/**
	 * @return array
	 */
	function getReceptFormCodeNotPrivelegeList()
	{
		$response = array();
		foreach ($this->receptFormStore as $row) {
			if (2 != $row['ReceptForm_IsPrivilege']) {
				$response[] = $row['ReceptForm_Code'];
			}
		}
		return $response;
	}

	/**
	 * @param int $id По умолчанию будет использован ReceptForm_id этого экземпляра
	 * @return bool|string
	 */
	function getReceptFormCode($id = null)
	{
		if (empty($id)) {
			$id = $this->ReceptForm_id;
		}
		foreach ($this->receptFormStore as $row) {
			if ($id == $row['ReceptForm_id']) {
				return $row['ReceptForm_Code'];
			}
		}
		return false;
	}

	/**
	 * @param $code
	 * @return bool|int
	 */
	function loadReceptFormIdByCode($code)
	{
		foreach ($this->receptFormStore as $row) {
			if ($code == $row['ReceptForm_Code']) {
				return $row['ReceptForm_id'] + 0;
			}
		}
		return false;
	}

	/**
	 * Конструктор объекта
	 */
	function __construct()
	{
		parent::__construct();
		$this->_setScenarioList(array(
			self::SCENARIO_AUTO_CREATE,
			/*
			self::SCENARIO_DELETE,
			self::SCENARIO_LOAD_EDIT_FORM,
			self::SCENARIO_SET_ATTRIBUTE,
			self::SCENARIO_DO_SAVE,
			*/
		));
	}

	/**
	 * Возвращает список всех используемых ключей атрибутов объекта
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();
		$arr[self::ID_KEY]['alias'] = 'EvnReceptGeneral_id';
		$arr[self::ID_KEY]['label'] = 'Идентификатор события выписки рецепта';
		$arr['pid']['alias'] = 'EvnReceptGeneral_pid';
		$arr['setdate']['label'] = 'Дата выписки рецепта';
		$arr['setdate']['alias'] = 'EvnReceptGeneral_setDate';
		$arr['settime']['label'] = 'Время выписки рецепта';
		$arr['settime']['alias'] = 'EvnReceptGeneral_setTime';
		$arr['diddt']['alias'] = 'EvnReceptGeneral_didDT';
		$arr['disdt']['alias'] = 'EvnReceptGeneral_disDT';
		$arr['evncoursetreatdrug_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			), // Медикамент курса лекарственного лечения
			'alias' => 'EvnCourseTreatDrug_id',
		);
		$arr['num'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			), // номер рецепта
			'alias' => 'EvnReceptGeneral_Num',
		);
		$arr['ser'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			), // серия рецепта
			'alias' => 'EvnReceptGeneral_Ser',
		);
		$arr['diag_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			), // диагноз
			'alias' => 'Diag_id',
		);
		$arr['receptvalid_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			), // актуальность рецепта
			'alias' => 'ReceptValid_id',
		);
		$arr['kolvo'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			), // количество
			'alias' => 'EvnReceptGeneral_Kolvo',
		);
		$arr['medpersonal_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_NOT_NULL,
			), // врач, выписавший рецепт
			'alias' => 'MedPersonal_id',
		);
		$arr['lpusection_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			), // отделение МО врача, выписавшего рецепт
			'alias' => 'LpuSection_id',
		);
		$arr['recepttype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_NOT_NULL,
			), // тип выписки рецепта
			'alias' => 'ReceptType_id',
		);
		$arr['ismnn'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			), // признак выписки по МНН
			'alias' => '',
		);
		$arr['signa'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			), // схема приема медикамента
			'alias' => 'EvnReceptGeneral_Signa',
		);
		$arr['person_age'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			), // возраст
			'alias' => 'Person_Age',
		);
		$arr['drugcomplexmnn_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),// Комплексное МНН РЛС
			'alias' => 'DrugComplexMnn_id',
		);
		$arr['drug_rlsid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			), // Медикамент РЛС
			'alias' => 'Drug_rlsid',
		);
		$arr['drugfinance_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			), // Источник финансирования
			'alias' => 'DrugFinance_id',
		);
		$arr['whsdocumentcostitemtype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			), // Статья расхода
			'alias' => 'WhsDocumentCostItemType_id',
		);
		$arr['receptform_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			), // Форма рецепта
			'alias' => 'ReceptForm_id',
		);
		// ниже все null при создании простых рецептов
		$arr['receptdiscount_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'ReceptDiscount_id',
		);
		$arr['receptfinance_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'ReceptFinance_id',
		);
		$arr['privilegetype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'PrivilegeType_id',
		);
		$arr['iskek'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnReceptGeneral_IsKEK',
		);
		$arr['udostser'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnReceptGeneral_UdostSer',
		);
		$arr['udostnum'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnReceptGeneral_UdostNum',
		);
		$arr['drug_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Drug_id',
		);
		$arr['isinreg'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
			),
			'alias' => 'EvnReceptGeneral_IsInReg',
		);
		$arr['orgfarmacy_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'OrgFarmacy_id',
		);
		$arr['isnotostat'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnReceptGeneral_IsNotOstat',
		);
		$arr['obrdt'] = array(
			'properties' => array(
				self::PROPERTY_DATE_TIME,
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'applyMethod' => '_applyObrDT',
			'alias' => 'EvnReceptGeneral_obrDT',
		);
		$arr['otpdt'] = array(
			'properties' => array(
				self::PROPERTY_DATE_TIME,
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'applyMethod' => '_applyOtpDT',
			'alias' => 'EvnReceptGeneral_otpDT',
		);
		$arr['receptdelaytype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'ReceptDelayType_id',
		);
		$arr['orgfarmacy_oid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'OrgFarmacy_oid',
		);
		$arr['drug_oid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Drug_oid',
		);
		$arr['okolvo'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnReceptGeneral_oKolvo',
		);
		$arr['oprice'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnReceptGeneral_oPrice',
		);
		$arr['drugrequestrow_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'DrugRequestRow_id',
		);
		$arr['extempcontents'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnReceptGeneral_ExtempContents',
		);
		$arr['isextemp'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnReceptGeneral_IsExtemp',
		);
		$arr['is7noz'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnReceptGeneral_Is7Noz',
		);
		$arr['receptremovecausetype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'ReceptRemoveCauseType_id',
		);
		$arr['prepseries_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'PrepSeries_id',
		);
		$arr['whsdocumentuc_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'WhsDocumentUc_id',
		);
		$arr['isotherdiag'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnReceptGeneral_IsOtherDiag',
		);
		return $arr;
	}

	/**
	 * Извлечение даты из входящих параметров
	 * @param array $data
	 * @return bool
	 */
	protected function _applyObrDT($data)
	{
		return $this->_applyDate($data, 'obrdt');
	}

	/**
	 * Извлечение даты из входящих параметров
	 * @param array $data
	 * @return bool
	 */
	protected function _applyOtpDT($data)
	{
		return $this->_applyDate($data, 'otpdt');
	}

	/**
	 * Извлечение значений параметров модели из входящих параметров,
	 * переданных из контроллера
	 * @param array $data
	 * @throws Exception
	 */
	function setParams($data)
	{
		parent::setParams($data);
		//$this->_params[''] = isset($data['']) ? $data[''] : null;
	}

	/**
	 * Проверка корректности данных модели для указанного сценария
	 * @throws Exception
	 */
	protected function _validate()
	{
		parent::_validate();
		/*if ('perm' != $this->regionNick || 10010833 != $this->Lpu_id) {
			throw new Exception('Пока простой рецепт сделан только для Перми и только для ГП2');
		}*/
		if (in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_AUTO_CREATE))) {
			if (empty($this->setDT) || false == ($this->setDT instanceof DateTime)) {
				throw new Exception('Не указана дата выписки рецепта');
			}
			$this->_checkChangeParent();
			if (empty($this->MedPersonal_id) || $this->MedPersonal_id < 0) {
				throw new Exception('Не указан врач, выписавший рецепт');
			}
			if (empty($this->LpuSection_id) || $this->LpuSection_id < 0) {
				throw new Exception('Не указано отделение врача, выписавший рецепт');
			}
			$this->_checkChangeDrug();
			if (empty($this->kolvo) || $this->kolvo < 0) {
				throw new Exception('Не указано количество ЛС');
			}
			$this->_checkChangeReceptForm();
			/*if ( empty($this->ReceptValid_id) || $this->ReceptValid_id < 0 ) {
				throw new Exception('Не указана актуальность рецепта');
			}*/
			if (empty($this->ReceptType_id) || $this->ReceptType_id < 0) {
				throw new Exception('Не указан тип выписки рецепта');
			}
			$this->_checkChangeWhsDocumentCostItemType();
		}
	}

	/**
	 * @throws Exception
	 */
	protected function _checkChangeWhsDocumentCostItemType()
	{
		if (empty($this->WhsDocumentCostItemType_id) || $this->WhsDocumentCostItemType_id < 0) {
			throw new Exception('Не указана статья расхода');
		}
		if (33 != $this->whsDocumentCostItemTypeCode) {
			throw new Exception('Для простых рецептов должна быть указана статья расхода 33. Основная деятельность');
		}
		$whsDocumentCostItemType = $this->loadEnumRowByCode('WhsDocumentCostItemType', $this->whsDocumentCostItemTypeCode);
		$this->setAttribute('DrugFinance_id', $whsDocumentCostItemType['DrugFinance_id']);
	}

	/**
	 * @throws Exception
	 */
	protected function _checkChangeDrug()
	{
		if (empty($this->DrugComplexMnn_id) || $this->DrugComplexMnn_id < 0) {
			// т.к. пока только по назначению
			throw new Exception('Не указано комплексное МНН');
		}
		if (!empty($this->DrugComplexMnn_id)) {
			// т.к. пока только по назначению
			$this->setAttribute('isMnn', empty($this->Drug_rlsid) ? 2 : 1);
		}
		if (empty($this->isMnn) || $this->isMnn < 0) {
			// т.к. пока только по назначению
			throw new Exception('Не указан признак выписки по МНН');
		}
	}

	/**
	 * @throws Exception
	 */
	protected function _checkChangeParent()
	{
		if (empty($this->pid) || $this->pid < 0) {
			// т.к. пока только по назначению
			throw new Exception('Не указано посещение или движение');
		}
		if (empty($this->EvnCourseTreatDrug_id) || $this->EvnCourseTreatDrug_id < 0) {
			// т.к. пока только по назначению
			throw new Exception('Не указан медикамент курса лекарственного лечения');
		}
	}

	/**
	 * @throws Exception
	 */
	protected function _checkChangeReceptForm()
	{
		if (empty($this->ReceptForm_id) || $this->ReceptForm_id < 0) {
			throw new Exception('Не указана форма рецепта');
		}
		if (false == in_array($this->receptFormCode, $this->receptFormCodeNotPrivelegeList)) {
			throw new Exception('Для льготных рецептов должна использоваться другая модель');
		}
		if (in_array($this->receptFormCode, array('107', '148-88'))) {
			$this->setAttribute('WhsDocumentCostItemType_id', $this->loadEnumValueByCode('WhsDocumentCostItemType', 33));
		}
		//if ( '107' == $this->receptFormCode) {
		//$this->setAttribute('ReceptValid_id', $this->loadEnumValueByCode('ReceptValid', 5)); // 2 месяца
		//}

		if ('148-88' == $this->receptFormCode) {
			$recept_valid = '7';
			if (isset($this->setdate) && ($this->setdate >= '2016-01-01')) //https://redmine.swan.perm.ru/issues/80689
				$recept_valid = '11';
			$this->setAttribute('ReceptValid_id', $this->loadEnumValueByCode('ReceptValid', $recept_valid)); // 10 дней
		}

		if (empty($this->num) && empty($this->ser)) {
			$num_ser = $this->genSerNum();
			$this->setAttribute('num', $num_ser['num']);
			$this->setAttribute('ser', $num_ser['ser']);
		}
		if (empty($this->num)) {
			throw new Exception('Не указан номер рецепта');
		}
		if (strlen($this->num) != 4) {
			throw new Exception('Неправильный номер рецепта');
		}
		if (empty($this->ser)) {
			throw new Exception('Не указана серия рецепта');
		}
		if (strlen($this->ser) != 5) {
			throw new Exception('Неправильная серия рецепта');
		}
	}

	/**
	 * Проверки и другая логика перед сохранением объекта
	 * @param array $data Массив входящих параметров
	 * @throws Exception
	 */
	protected function _beforeSave($data = array())
	{
		parent::_beforeSave($data);
		$this->setAttribute('Person_Age', $this->getPerson_Age());
	}

	/**
	 * автогенерация серии и номера нельготного рецепта
	 *
	 * Сделано только для показа, лишь бы было))
	 * @return array|bool
	 */
	function genSerNum()
	{
		$row = $this->getFirstRowFromQuery("select max(EvnReceptGeneral_Num) as \"num\", max(EvnReceptGeneral_Ser) as \"ser\" from EvnReceptGeneral");
		if (empty($row) || empty($row['num']) || empty($row['ser'])) {
			$row = array('num' => '5901', 'ser' => '50000');
		}
		$row['ser']++;
		switch (strlen($row['ser'])) {
			case 1:
				$row['ser'] = '0000' . $row['ser'];
				break;
			case 2:
				$row['ser'] = '000' . $row['ser'];
				break;
			case 3:
				$row['ser'] = '00' . $row['ser'];
				break;
			case 4:
				$row['ser'] = '0' . $row['ser'];
				break;
			case 5:
				$row['ser'] = '' . $row['ser'];
				break;
			default:
				$row['ser'] = '00001';
				$row['num']++;
				break;
		}
		switch (strlen($row['num'])) {
			case 1:
				$row['ser'] = '000' . $row['ser'];
				break;
			case 2:
				$row['ser'] = '00' . $row['ser'];
				break;
			case 3:
				$row['ser'] = '0' . $row['ser'];
				break;
			case 4:
				$row['ser'] = '' . $row['ser'];
				break;
			default:
				$row['ser'] = '00001';
				$row['num'] = '5901';
				break;
		}
		return $row;
	}
}
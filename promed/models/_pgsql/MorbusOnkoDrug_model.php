<?php
defined("BASEPATH") or die ("No direct script access allowed");
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package		PromedWeb
 * @access		public
 * @copyright	Copyright (c) 2013 Swan Ltd.
 * @link		http://swan.perm.ru/PromedWeb
 *
 * Препарат гормоноиммунотерапевтического или химиотерапевтического лечения
 *
 * @package      MorbusOnko
 * @author       Пермяков Александр
 * @version      06.2013
 *
 * @property CI_DB_driver $db
 */
class MorbusOnkoDrug_model extends SwPgModel
{
	private $dateTimeForm104 = "DD.MM.YYYY";
	private $dateTimeForm120 = "YYYY-MM-DD";

	/**
	 * Пользователь
	 */
	public $pmUser_id;

	/**
	 * Простое заболевание в рамках, которого просматривается запись регистра в форме просмотра ЭМК
	 * или в форме просмотра регистра по онкологии
	 */
	public $Morbus_id;

	/**
	 * Учетный документ в рамках, которого просматривается запись регистра в форме просмотра ЭМК
	 * или идентификатор человека, если запись регистра просматривается
	 * в форме просмотра регистра по онкологии (не в ЭМК, вне контекста учетного документа)
	 * @var integer
	 */
	public $Evn_id;
	public $_MorbusOnkoVizitPLDop_id;
	public $_MorbusOnkoDiagPLStom_id;
	public $_MorbusOnkoLeave_id;

	/**
	 * Список служебных параметров, которые должны быть получены из входящих параметров
	 * @var array
	 */
	protected $_params = [
		"pmUser_id",
		"Morbus_id",
		"Evn_id",
	];

	/**
	 * Primary key
	 * @var integer
	 */
	protected $_MorbusOnkoDrug_id;
	/**
	 * Принадлежность онкозаболеванию
	 * @var integer
	 */
	protected $_MorbusOnko_id;
	/**
	 * Дата начала
	 * @var datetime
	 */
	protected $_MorbusOnkoDrug_begDT;
	/**
	 * Дата окончания
	 * @var datetime
	 */
	protected $_MorbusOnkoDrug_endDT;
	/**
	 * Справочник (перечисление DrugDictType)
	 * @var integer
	 */
	protected $_DrugDictType_id;
	/**
	 * Препарат    (перечисление OnkoDrug)
	 * @var integer
	 */
	protected $_OnkoDrug_id;
	/**
	 * Препарат    (перечисление rls.CLSATC)
	 * @var integer
	 */
	protected $_CLSATC_id;
	/**
	 * Единица формы выпуска препарата (перечисление OnkoDrugUnitType)
	 * @var integer
	 */
	protected $_OnkoDrugUnitType_id;
	/**
	 * Разовая доза
	 * @var string
	 */
	protected $_MorbusOnkoDrug_Dose;
	/**
	 * Кратность
	 * @var string
	 */
	protected $_MorbusOnkoDrug_Multi;
	/**
	 * Периодичность
	 * @var string
	 */
	protected $_MorbusOnkoDrug_Period;
	/**
	 * Суммарная доза
	 * @var string
	 */
	protected $_MorbusOnkoDrug_SumDose;
	/**
	 * Метод введения
	 * @var string
	 */
	protected $_MorbusOnkoDrug_Method;
	/**
	 * Проведена профилактика тошноты и рвотного рефлекса
	 * @var string
	 */
	protected $_MorbusOnkoDrug_IsPreventionVomiting;
	/**
	 * Метод введения
	 * @var string
	 */
	protected $_PrescriptionIntroType_id;
	/**
	 * случай гормоноиммунотерапевтического или химиотерапевтического лечения
	 * @var integer
	 */
	protected $_Evn_id;
	/**
	 * Медикамент
	 * @var integer
	 */
	protected $_Drug_id;
	/**
	 * Медикамент
	 * @var integer
	 */
	protected $_DrugMNN_id;

	/**
	 * Список атрибутов, которые могут быть записаны в модель и должны быть получены из входящих параметров
	 * @var array
	 */
	protected $_safeAttributes = [
		"MorbusOnkoDrug_id",
		"MorbusOnko_id",
		"MorbusOnkoDrug_begDT",
		"MorbusOnkoDrug_endDT",
		"DrugDictType_id",
		"CLSATC_id",
		"OnkoDrug_id",
		"OnkoDrugUnitType_id",
		"MorbusOnkoDrug_Dose",
		"MorbusOnkoDrug_Multi",
		"MorbusOnkoDrug_Period",
		"MorbusOnkoDrug_SumDose",
		"MorbusOnkoDrug_Method",
		"MorbusOnkoDrug_IsPreventionVomiting",
		"PrescriptionIntroType_id",
		"Evn_id",
		"MorbusOnkoVizitPLDop_id",
		"MorbusOnkoDiagPLStom_id",
		"MorbusOnkoLeave_id",
		"Drug_id",
		"DrugMNN_id",
	];

	/**
	 * Текст ошибки
	 * @var string
	 */
	protected $_errorMsg;
	/**
	 * Код ошибки
	 * @var integer
	 */
	protected $_errorCode;
	/**
	 * Имя сценария, определяющего правила валидации модели
	 *
	 * Возможные сценарии:
	 * create - Создание записи
	 * update - Обновление записи
	 * read - Загрузка данных одной записи по ключу
	 * destroy - Удаление записи из БД
	 * read_list - Загрузка списка препаратов в рамках случая лечения
	 *
	 * @var string
	 */
	protected $_scenario;

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Запись значения первичного ключа в модель
	 * @param int $MorbusOnkoDrug_id
	 */
	public function setId($MorbusOnkoDrug_id)
	{
		$this->_MorbusOnkoDrug_id = $MorbusOnkoDrug_id;
	}

	/**
	 * Получение значения первичного ключа из модели
	 * @return int
	 */
	public function getId()
	{
		return $this->_MorbusOnkoDrug_id;
	}

	/**
	 * Извлечение значений атрибутов модели из входящих параметров
	 * @param array $data
	 * @return void
	 */
	public function setSafeAttributes($data)
	{
		foreach ($this->_safeAttributes as $key) {
			$property = '_' . $key;
			if (property_exists($this, $property) && array_key_exists($key, $data)) {
				$this->{$property} = $data[$key];
			}
		}
	}

	/**
	 * Извлечение значений служебных параметров модели из входящих параметров
	 * @param array $data
	 * @return void
	 */
	public function setParams($data)
	{
		foreach ($this->_params as $key) {
			if (property_exists($this, $key) && array_key_exists($key, $data)) {
				$this->{$key} = $data[$key];
			}
		}
	}

	/**
	 * Проверка корректности данных модели для указанного сценария
	 * @return boolean
	 */
	protected function _validate()
	{
		if (empty($this->_scenario)) {
			$this->_errorCode = 500;
			$this->_errorMsg = "Не указан сценарий";
			return false;
		}
		if (in_array($this->_scenario, ["update", "destroy", "read"]) && empty($this->_MorbusOnkoDrug_id)) {
			$this->_errorCode = 500;
			$this->_errorMsg = "Не указан ключ записи";
			return false;
		}
		if (in_array($this->_scenario, ["update", "create"]) && empty($this->_MorbusOnkoDrug_begDT)) {
			$this->_errorCode = 500;
			$this->_errorMsg = "Не указана дата начала";
			return false;
		}
		if (in_array($this->_scenario, ["update", "create"]) && empty($this->_DrugDictType_id)) {
			$this->_errorCode = 500;
			$this->_errorMsg = "Не указан справочник";
			return false;
		}
		if ($this->regionNick != "kz" && in_array($this->_scenario, ["update", "create"]) && empty($this->_OnkoDrug_id) && empty($this->_DrugMNN_id)) {
			$this->_errorCode = 500;
			$this->_errorMsg = "Не указан медикамент";
			return false;
		}
		if (in_array($this->_scenario, ["update", "create"]) && empty($this->pmUser_id)) {
			$this->_errorCode = 500;
			$this->_errorMsg = "Не указан пользователь";
			return false;
		}
		if (in_array($this->_scenario, ["read_list"]) && empty($this->_Evn_id)) {
			$this->_errorCode = 500;
			$this->_errorMsg = "Не указан случай лечения";
			return false;
		}
		return true;
	}

	/**
	 * Получение данных для редактирования
	 * Параметры должны быть установлены в контроллере
	 * @return array|false
	 * @throws Exception
	 */
	public function read()
	{
		$this->_scenario = "read";
		if (!$this->_validate()) {
			throw new Exception($this->_errorMsg, $this->_errorCode);
		}
		$sql = "
			select
				MorbusOnkoDrug_id as \"MorbusOnkoDrug_id\",
				MorbusOnko_id as \"MorbusOnko_id\",
				DrugDictType_id as \"DrugDictType_id\",
				CLSATC_id as \"CLSATC_id\",
				OnkoDrug_id as \"OnkoDrug_id\",
				to_char(MorbusOnkoDrug_begDT, '{$this->dateTimeForm104}') as \"MorbusOnkoDrug_begDT\",
				to_char(MorbusOnkoDrug_endDT, '{$this->dateTimeForm104}') as \"MorbusOnkoDrug_endDT\",
				OnkoDrugUnitType_id as \"OnkoDrugUnitType_id\",
				MorbusOnkoDrug_Dose as \"MorbusOnkoDrug_Dose\",
				MorbusOnkoDrug_Multi as \"MorbusOnkoDrug_Multi\",
				MorbusOnkoDrug_Period as \"MorbusOnkoDrug_Period\",
				MorbusOnkoDrug_SumDose as \"MorbusOnkoDrug_SumDose\",
				MorbusOnkoDrug_Method as \"MorbusOnkoDrug_Method\",
				MorbusOnkoDrug_IsPreventionVomiting as \"MorbusOnkoDrug_IsPreventionVomiting\",
				PrescriptionIntroType_id as \"PrescriptionIntroType_id\",
				Evn_id as \"Evn_id\",
				MorbusOnkoVizitPLDop_id as \"MorbusOnkoVizitPLDop_id\",
				MorbusOnkoDiagPLStom_id as \"MorbusOnkoDiagPLStom_id\",
				MorbusOnkoLeave_id as \"MorbusOnkoLeave_id\",
				Drug_id as \"Drug_id\",
				DrugMNN_id as \"DrugMNN_id\"
			from v_MorbusOnkoDrug
			where MorbusOnkoDrug_id = :MorbusOnkoDrug_id
			limit 1
		";
		$sqlParams = ["MorbusOnkoDrug_id" => $this->_MorbusOnkoDrug_id];
		return $this->queryResult($sql, $sqlParams);
	}

	/**
	 * Логика перед сохранением, включающая в себя проверку данных
	 * @param array $data
	 * @return bool|void
	 */
	protected function _beforeSave($data = [])
	{
		if (empty($this->_scenario)) {
			$this->_scenario = "update";
			if (empty($this->_MorbusOnkoDrug_id)) {
				$this->_scenario = "create";
			}
		}
		if (!$this->_validate()) {
			return false;
		}
		// проверки перед сохранением
		if (!empty($this->_Evn_id)) {
			$sql = "
				select
					e.EvnClass_SysNick as \"EvnClass_SysNick\",
					ep.EvnClass_SysNick as \"ParentEvnClass_SysNick\"
				from
					v_Evn e
					left join v_Evn ep on ep.Evn_id = e.Evn_pid
				where e.Evn_id = :Evn_id
				limit 1
			";
			$sqlParams = ["Evn_id" => $this->_Evn_id];
			$EvnData = $this->getFirstRowFromQuery($sql, $sqlParams);
			if ($EvnData === false || !is_array($EvnData) || count($EvnData) == 0) {
				$this->_errorCode = 500;
				$this->_errorMsg = "Ошибка при получении класса события";
				return false;
			}
			$EvnClass_SysNick = $EvnData["EvnClass_SysNick"];
			if (in_array($EvnClass_SysNick, array('EvnUslugaOnkoChem','EvnUslugaOnkoChemBeam'))) {
				$MorbusOnkoObject = "MorbusOnko";
				$ParentEvnClass_SysNick = $EvnData["ParentEvnClass_SysNick"];
				switch ($ParentEvnClass_SysNick) {
					case "EvnDiagPLStom":
						$MorbusOnkoObject = "MorbusOnkoDiagPLStom";
						break;

					case "EvnSection":
						$MorbusOnkoObject = "MorbusOnkoLeave";
						break;

					case "EvnVizitPL":
						$MorbusOnkoObject = "MorbusOnkoVizitPLDop";
						break;
				}
				switch ($ParentEvnClass_SysNick) {
					case "EvnDiagPLStom":
					case "EvnSection":
						$EvnIdField = $EvnClass_SysNick . "_pid";
						$MorbusOnkoIdField = $ParentEvnClass_SysNick . "_id";
						break;
					case "EvnVizitPL":
						$EvnIdField = $EvnClass_SysNick . "_pid";
						$MorbusOnkoIdField = "EvnVizit_id";
						break;
					default:
						$EvnIdField = "Morbus_id";
						$MorbusOnkoIdField = "Morbus_id";
				}
				$query = "
					select
						to_char(MO.{$MorbusOnkoObject}_setDiagDT, '$this->dateTimeForm120') as \"MorbusOnko_setDiagDT\",
						to_char(Evn.{$EvnClass_SysNick}_setDT, '$this->dateTimeForm120') as \"EvnUsluga_setDT\",
						to_char(Evn.{$EvnClass_SysNick}_disDT, '$this->dateTimeForm120') as \"EvnUsluga_disDT\"
					from v_{$EvnClass_SysNick} Evn
						inner join v_{$MorbusOnkoObject} MO on MO.{$MorbusOnkoIdField} = Evn.{$EvnIdField}
					where Evn.{$EvnClass_SysNick}_id = :Evn_id
					limit 1
				";
				$queryParams = ["Evn_id" => $this->_Evn_id];
				/**@var CI_DB_result $result */
				$result = $this->db->query($query, $queryParams);
				if (!is_object($result)) {
					$this->_errorCode = 500;
					$this->_errorMsg = "Ошибка запроса получения данных случая лечения";
					return false;
				}
				$tmp = $result->result("array");
				if (empty($tmp)) {
					$this->_errorCode = 400;
					$this->_errorMsg = "Не удалось получить данные случая лечения";
					return false;
				}
				if (!empty($tmp[0]["MorbusOnko_setDiagDT"]) && $this->_MorbusOnkoDrug_begDT < $tmp[0]["MorbusOnko_setDiagDT"]) {
					$this->_errorCode = 400;
					$this->_errorMsg = "Дата начала не может быть меньше «Даты установления диагноза»";
					return false;
				}
				$cur = ("EvnUslugaOnkoChem" == $EvnClass_SysNick) ? "химиотерапевтического лечения" : "гормоноиммунотерапевтического лечения";
				
				if (
					!empty($tmp[0]["EvnUsluga_setDT"]) &&
					(
						$this->_MorbusOnkoDrug_begDT < $tmp[0]["EvnUsluga_setDT"] ||
						(!empty($tmp[0]["EvnUsluga_disDT"]) && $this->_MorbusOnkoDrug_begDT > $tmp[0]["EvnUsluga_disDT"])
					)
				) {
					$this->_errorCode = 400;
					$this->_errorMsg = "Дата начала не входит в период " . $cur;
					return false;
				}
				if (
					!empty($this->_MorbusOnkoDrug_endDT) &&
					!empty($tmp[0]["EvnUsluga_setDT"]) &&
					(
						$this->_MorbusOnkoDrug_endDT < $tmp[0]["EvnUsluga_setDT"] ||
						(!empty($tmp[0]["EvnUsluga_disDT"]) && $this->_MorbusOnkoDrug_endDT > $tmp[0]["EvnUsluga_disDT"])
					)
				) {
					$this->_errorCode = 400;
					$this->_errorMsg = "Дата окончания не входит в период " . $cur;
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * Сохранение данных
	 * @param array $data
	 * @return array
	 * @throws Exception
	 */
	public function save($data = [])
	{
		if (count($data) > 0) {
			$this->setParams($data);
			$this->setSafeAttributes($data);
		}
		if (!$this->_beforeSave()) {
			throw new Exception($this->_errorMsg, $this->_errorCode);
		}
		$procedure = (empty($this->_MorbusOnkoDrug_id)) ? "p_MorbusOnkoDrug_ins" : "p_MorbusOnkoDrug_upd";
		$selectString = "
			morbusonkodrug_id as \"MorbusOnkoDrug_id\",
			error_code as \"Error_Code\",
			error_message as \"Error_Msg\"
		";
		$sql = "
			select {$selectString}
			from {$procedure}(
				morbusonkodrug_id := :MorbusOnkoDrug_id,
				morbusonko_id := :MorbusOnko_id,
				onkodrug_id := :OnkoDrug_id,
				morbusonkodrug_begdt := :MorbusOnkoDrug_begDT,
				morbusonkodrug_enddt := :MorbusOnkoDrug_endDT,
				morbusonkodrug_dose := :MorbusOnkoDrug_Dose,
				onkodrugunittype_id := :OnkoDrugUnitType_id,
				morbusonkodrug_multi := :MorbusOnkoDrug_Multi,
				morbusonkodrug_period := :MorbusOnkoDrug_Period,
				morbusonkodrug_sumdose := :MorbusOnkoDrug_SumDose,
				morbusonkodrug_method := :MorbusOnkoDrug_Method,
				evn_id := :Evn_id,
				drugdicttype_id := :DrugDictType_id,
				clsatc_id := :CLSATC_id,
				prescriptionintrotype_id := :PrescriptionIntroType_id,
				drug_id := :Drug_id,
				morbusonkoleave_id := :MorbusOnkoLeave_id,
				morbusonkovizitpldop_id := :MorbusOnkoVizitPLDop_id,
				morbusonkodiagplstom_id := :MorbusOnkoDiagPLStom_id,
				morbusonkodrug_ispreventionvomiting := :MorbusOnkoDrug_IsPreventionVomiting,
				drugmnn_id := :DrugMNN_id,
				pmuser_id := :pmUser_id
			);
		";
		$params = [
			"MorbusOnkoDrug_id" => $this->_MorbusOnkoDrug_id,
			"MorbusOnko_id" => $this->_MorbusOnko_id,
			"MorbusOnkoDrug_begDT" => $this->_MorbusOnkoDrug_begDT,
			"MorbusOnkoDrug_endDT" => $this->_MorbusOnkoDrug_endDT,
			"MorbusOnkoDrug_Dose" => $this->_MorbusOnkoDrug_Dose,
			"MorbusOnkoDrug_Multi" => $this->_MorbusOnkoDrug_Multi,
			"DrugDictType_id" => $this->_DrugDictType_id,
			"CLSATC_id" => $this->_CLSATC_id,
			"OnkoDrug_id" => $this->_OnkoDrug_id,
			"OnkoDrugUnitType_id" => $this->_OnkoDrugUnitType_id,
			"MorbusOnkoDrug_Method" => $this->_MorbusOnkoDrug_Method,
			"MorbusOnkoDrug_SumDose" => $this->_MorbusOnkoDrug_SumDose,
			"MorbusOnkoDrug_Period" => $this->_MorbusOnkoDrug_Period,
			"PrescriptionIntroType_id" => $this->_PrescriptionIntroType_id,
			"Evn_id" => $this->_Evn_id,
			"MorbusOnkoVizitPLDop_id" => $this->_MorbusOnkoVizitPLDop_id,
			"MorbusOnkoDiagPLStom_id" => $this->_MorbusOnkoDiagPLStom_id,
			"MorbusOnkoLeave_id" => $this->_MorbusOnkoLeave_id,
			"MorbusOnkoDrug_IsPreventionVomiting" => $this->_MorbusOnkoDrug_IsPreventionVomiting,
			"Drug_id" => $this->_Drug_id,
			"DrugMNN_id" => $this->_DrugMNN_id,
			"pmUser_id" => $this->pmUser_id,
		];
		$result = $this->db->query($sql, $params);
		if (!is_object($result)) {
			throw new Exception("Ошибка запроса сохранения записи!", 500);
		}
		return $result->result("array");
	}

	/**
	 * Логика перед удалением, может включать в себя проверки данных
	 * @return bool
	 */
	protected function _beforeDestroy()
	{
		$this->_scenario = "destroy";
		return $this->_validate();
	}

	/**
	 * Удаление шаблона
	 * @param array $data
	 * @return array|false
	 * @throws Exception
	 */
	public function destroy($data = [])
	{
		if (count($data) > 0) {
			$this->setSafeAttributes($data);
		}
		if (!$this->_beforeDestroy()) {
			throw new Exception($this->_errorMsg, $this->_errorCode);
		}
		$sql = "
			select
				error_code as \"Error_Code\",
				error_message as \"Error_Message\"
			from p_morbusonkodrug_del(
				morbusonkodrug_id := :MorbusOnkoDrug_id,
				pmUser_id := :pmUser_id
			);
		";
		$params = [
			"MorbusOnkoDrug_id" => $this->_MorbusOnkoDrug_id,
			'pmUser_id' => $this->pmUser_id
		];
		$resp = $this->queryResult($sql, $params);
		if (!is_array($resp)) {
			throw new Exception("Ошибка запроса удаления записи!", 500);
		}
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}
		return [["success" => true]];
	}

	/**
	 * Метод получения списка препаратов в рамках случая лечения
	 * @param array $data
	 * @return array|bool
	 */
	function readList($data = [])
	{
		if (count($data) > 0) {
			$this->setSafeAttributes($data);
		}
		$this->_scenario = "read_list";
		if (!$this->_validate()) {
			return false;
		}
		$query = "
			select
				Drug.MorbusOnkoDrug_id as \"MorbusOnkoDrug_id\",
			    Drug.MorbusOnko_id as \"MorbusOnko_id\",
			    Drug.Evn_id as \"Evn_id\",
			    to_char(Drug.MorbusOnkoDrug_begDT, '{$this->dateTimeForm104}') as \"MorbusOnkoDrug_begDT\",
			    to_char(Drug.MorbusOnkoDrug_endDT, '{$this->dateTimeForm104}') as \"MorbusOnkoDrug_endDT\",
			    DDT.DrugDictType_Name as \"DrugDictType_Name\",
			    case when FDM.DrugMNN_Name is not null then FDM.DrugMNN_Name
			    	when OD.OnkoDrug_Name is not null then OD.OnkoDrug_Name
			    	else RLS.NAME
			    end as \"OnkoDrug_Name\",
			    Drug.MorbusOnkoDrug_SumDose as \"MorbusOnkoDrug_SumDose\",
			    odut.OnkoDrugUnitType_Name as \"OnkoDrugUnitType_Name\"
			from v_MorbusOnkoDrug Drug
				inner join v_DrugDictType DDT on DDT.DrugDictType_id = coalesce(Drug.DrugDictType_id, 2)
				left join v_OnkoDrug OD on Drug.OnkoDrug_id = OD.OnkoDrug_id
				left join rls.v_CLSATC RLS on RLS.CLSATC_id = Drug.CLSATC_id
				left join fed.DrugMNN FDM on FDM.DrugMNN_id = Drug.DrugMNN_id
				left join v_OnkoDrugUnitType odut on odut.OnkoDrugUnitType_id = Drug.OnkoDrugUnitType_id
			where Drug.Evn_id = :Evn_id
		";
		$params = ["Evn_id" => $this->_Evn_id,];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Метод получения списка препаратов в рамках случая лечения для печати списка для Уфы
	 * @param array $data
	 * @return array|bool
	 */
	function readListForPrint($data = [])
	{
		if (count($data) > 0) {
			$this->setSafeAttributes($data);
		}
		$this->_scenario = "read_list";
		if (!$this->_validate()) {
			return false;
		}
		$query = "
			select
				Drug.MorbusOnkoDrug_id as \"MorbusOnkoDrug_id\",
			    Drug.MorbusOnko_id as \"MorbusOnko_id\",
			    Drug.Evn_id as \"Evn_id\",
			    to_char(Drug.MorbusOnkoDrug_begDT, '{$this->dateTimeForm104}') as \"MorbusOnkoDrug_begDT\",
			    to_char(Drug.MorbusOnkoDrug_endDT, '{$this->dateTimeForm104}') as \"MorbusOnkoDrug_endDT\",
			    DDT.DrugDictType_Name,
			    case when FDM.DrugMNN_Name is not null then FDM.DrugMNN_Name
				    when OD.OnkoDrug_Name is not null then OD.OnkoDrug_Name
				    else RLS.NAME
				end as \"OnkoDrug_Name\",
			    Drug.MorbusOnkoDrug_SumDose as \"MorbusOnkoDrug_SumDose\",
			    Drug.MorbusOnkoDrug_Dose as \"MorbusOnkoDrug_Dose\",
			    Drug.MorbusOnkoDrug_Period as \"MorbusOnkoDrug_Period\",
			    Drug.MorbusOnkoDrug_Multi as \"MorbusOnkoDrug_Multi\",
			    Drug.MorbusOnkoDrug_Method as \"MorbusOnkoDrug_Method\",
			    Drug.MorbusOnkoDrug_IsPreventionVomiting as \"MorbusOnkoDrug_IsPreventionVomiting\",
			    ODUT.OnkoDrugUnitType_Name as \"OnkoDrugUnitType_Name\"
			from
				v_MorbusOnkoDrug Drug
				inner join v_DrugDictType DDT on DDT.DrugDictType_id = coalesce(Drug.DrugDictType_id, 2)
				left join v_OnkoDrugUnitType ODUT on ODUT.OnkoDrugUnitType_id = Drug.OnkoDrugUnitType_id
				left join v_OnkoDrug OD on Drug.OnkoDrug_id = OD.OnkoDrug_id
				left join rls.v_CLSATC RLS on RLS.CLSATC_id = Drug.CLSATC_id
				left join fed.DrugMNN FDM on FDM.DrugMNN_id = Drug.DrugMNN_id
			where Drug.Evn_id = :Evn_id
		";
		$params = ["Evn_id" => $this->_Evn_id,];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Метод получения списка записей комбобокса для выбора медикамента
	 * @param $data
	 * @return array|bool
	 */
	function loadDrugCombo($data)
	{
		$params = [];
		$join = [];
		$where = [];
		if (!empty($data["Drug_id"])) {
			$where[] = "d.Drug_id = :Drug_id";
			$params["Drug_id"] = $data["Drug_id"];
		} else {
			if (!empty($data["CLSATC_id"])) {
				$evn_drug_exists = false;
				$evn_prescr_exists = false;
				if (!empty($data["Evn_id"])) {
					//если есть идентификатор лечения, ищем, нет ли в сопуствующей КВС медикамента с заданной АТХ
					//поиск в разделе использования медикаментов
					$query = "
						select count(d.Drug_id) as cnt
						from
							v_Evn mod_e
							left join v_EvnDrug ed on ed.EvnDrug_rid = mod_e.Evn_rid
							left join rls.v_Drug d on d.Drug_id = ed.Drug_id
							left join rls.v_PREP_ATC pa on pa.PREPID = d.DrugPrep_id
							left join rls.v_CLSATC ca on ca.CLSATC_ID = pa.UNIQID
						where mod_e.Evn_id = :Evn_id
						  and ca.CLSATC_ID = :CLSATC_id
					";
					$queryParams = [
						"Evn_id" => $data["Evn_id"],
						"CLSATC_id" => $data["CLSATC_id"]
					];
					$check_data = $this->getFirstRowFromQuery($query, $queryParams);
					if (!empty($check_data["cnt"])) {
						$evn_drug_exists = true;
					}
					//поиск в разделе лекарственного лечения
					$query = "
						select count(d.Drug_id) as cnt
						from
							v_Evn mod_e
							left join v_EvnPrescrTreat ept on ept.EvnPrescrTreat_rid = mod_e.Evn_rid
							left join v_EvnPrescrTreatDrug eptd on eptd.EvnPrescrTreat_id = ept.EvnPrescrTreat_id
							left join rls.v_DrugComplexMnn c_dcm on c_dcm.DrugComplexMnn_pid = eptd.DrugComplexMnn_id
							left join rls.v_Drug d on
								d.Drug_id = eptd.Drug_id or
								d.DrugComplexMnn_id = eptd.DrugComplexMnn_id or
								d.DrugComplexMnn_id = c_dcm.DrugComplexMnn_id
							left join rls.v_PREP_ATC pa on pa.PREPID = d.DrugPrep_id
							left join rls.v_CLSATC ca on ca.CLSATC_ID = pa.UNIQID
						where mod_e.Evn_id = :Evn_id
						  and ca.CLSATC_ID = :CLSATC_id
					";
					$queryParams = [
						"Evn_id" => $data["Evn_id"],
						"CLSATC_id" => $data["CLSATC_id"]
					];
					$check_data = $this->getFirstRowFromQuery($query, $queryParams);
					if (!empty($check_data["cnt"])) {
						$evn_prescr_exists = true;
					}
				}
				if ($evn_drug_exists || $evn_prescr_exists) {
					$tmp_where = [];
					if ($evn_drug_exists) {
						//медикаменты в наличии в разделе использования медикаментов
						$tmp_where[] = "
							d.Drug_id in (
								select i_d.Drug_id
								from
									v_Evn i_mod_e
									left join v_EvnDrug i_ed on i_ed.EvnDrug_rid = i_mod_e.Evn_rid
									left join rls.v_Drug i_d on i_d.Drug_id = i_ed.Drug_id
								where i_mod_e.Evn_id = :Evn_id
							)
						";
					}
					if ($evn_prescr_exists) {
						//медикаменты в наличии в разделе лекарственного лечения
						$tmp_where[] = "
							d.Drug_id in (
								select i_d.Drug_id
								from
									v_Evn i_mod_e
									left join v_EvnPrescrTreat i_ept on i_ept.EvnPrescrTreat_rid = i_mod_e.Evn_rid
									left join v_EvnPrescrTreatDrug i_eptd on i_eptd.EvnPrescrTreat_id = i_ept.EvnPrescrTreat_id
									left join rls.v_DrugComplexMnn i_c_dcm on i_c_dcm.DrugComplexMnn_pid = i_eptd.DrugComplexMnn_id
									left join rls.v_Drug i_d on
										i_d.Drug_id = i_eptd.Drug_id or
										i_d.DrugComplexMnn_id = i_eptd.DrugComplexMnn_id or
										i_d.DrugComplexMnn_id = i_c_dcm.DrugComplexMnn_id
								where
									i_mod_e.Evn_id = :Evn_id and 
									i_d.Drug_id is not null
							)
						";
					}
					//если медикаментыы в наличи в нескольких разделах, собираем условия через ИЛИ
					$where[] = "(" . implode(" or ", $tmp_where) . ")";
					$params["Evn_id"] = $data["Evn_id"];
					$params["CLSATC_id"] = $data["CLSATC_id"];
				} else {
					$where[] = "ca.CLSATC_ID = :CLSATC_id";
					$params["CLSATC_id"] = $data["CLSATC_id"];
				}
			}

			$params["Date_Str"] = "";
			if (!empty($data["Date"])) {
				$params["Date_Str"] = $data["Date"];
			} else {
				$params["Date_Str"] = date("Y-m-d");
			}
			$where[] = "(d.Drug_begDate is null or d.Drug_begDate <= :Date_Str)";
			$where[] = "(d.Drug_endDate is null or d.Drug_endDate >= :Date_Str)";
			if (!empty($data["query"])) {
				$where[] = "Drug_RegNum||' '||coalesce(d.Drug_ShortName, d.Drug_Name) like :query";
				$params["query"] = "%" . $data["query"] . "%";
			}
		}

		$join_clause = implode(" ", $join);
		$where_clause = implode(" and ", $where);
		if (strlen($where_clause)) {
			$where_clause = "
				where {$where_clause}
			";
		}
		$selectString = "
			d.Drug_id as \"Drug_id\",
			coalesce(d.Drug_RegNum||' '||coalesce(d.Drug_ShortName, d.Drug_Name), '') as \"Drug_FullName\",
			coalesce(d.Drug_RegNum, '') as \"Drug_RegNum\",
			coalesce(d.Drug_ShortName, d.Drug_Name) as \"Drug_ShortName\",
			ca.CLSATC_ID as \"CLSATC_ID\"
		";
		$fromString = "
			rls.v_Drug d
			left join rls.v_PREP_ATC pa on pa.PREPID = d.DrugPrep_id
			left join rls.v_CLSATC ca on ca.CLSATC_ID = pa.UNIQID
			{$join_clause}
		";
		$query = "
			select {$selectString}
			from {$fromString}
			{$where_clause}
			limit 200
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Метод получения списка записей комбобокса для выбора медикамента (fed.DrugMNN)
	 * @param $data
	 * @return array|false
	 */
	public function loadFedDrugMNNCombo($data)
	{
		$filterList = [];
		$params = [];
		if (!empty($data["DrugMNN_id"])) {
			$filterList["DrugMNN_id"] = "d.DrugMNN_id = :DrugMNN_id";
			$params["DrugMNN_id"] = $data["DrugMNN_id"];
		} else {
			if (!empty($data["Date"])) {
				$params["Date"] = $data["Date"];
			} else {
				$params["Date"] = date("Y-m-d");
			}
			$filterList["DrugMNN_begDate"] = "(d.DrugMNN_begDate is null or d.DrugMNN_begDate <= :Date)";
			$filterList["DrugMNN_endDate"] = "(d.DrugMNN_endDate is null or d.DrugMNN_endDate >= :Date)";
			if (!empty($data["Evn_id"])) {
				$drugList = [];
				$sql = "
					select
						e.EvnClass_SysNick as \"EvnClass_SysNick\",
					    parent.EvnClass_SysNick as \"ParentEvnClass_SysNick\",
					    parent.Evn_id as \"Evn_id\",
					    e.Evn_rid as \"Evn_rid\"
					from v_Evn e
						inner join v_Evn parent on parent.Evn_id = e.Evn_pid
					where e.Evn_id = :Evn_id
					limit 1
				";
				$parentEvnData = $this->getFirstRowFromQuery($sql, $data);
				if (is_array($parentEvnData) && count($parentEvnData) > 0) {
					$isEvnSection = false;
					$params["Evn_rid"] = $parentEvnData["Evn_rid"];
					if ($parentEvnData["EvnClass_SysNick"] == "EvnSection") {
						$isEvnSection = true;
						$params["Evn_pid"] = $data["Evn_id"];
					} elseif ($parentEvnData["EvnClass_SysNick"] == "EvnPS") {
						$params["Evn_pid"] = $data["Evn_id"];
					} else {
						if ($parentEvnData["EvnClass_SysNick"] == "EvnVizitPL") {
							$params["Evn_pid"] = $data["Evn_id"];
						} else {
							$params["Evn_pid"] = $parentEvnData["Evn_id"];
						}
						if ($parentEvnData["ParentEvnClass_SysNick"] == "EvnSection") {
							$isEvnSection = true;
						}
					}
					if ($isEvnSection === true) {
						$sql = "
							with DTS as (
								select DrugTherapyScheme_id
								from v_EvnSectionDrugTherapyScheme
								where EvnSection_id = :Evn_pid
							)
							select DrugMNN_id as \"DrugMNN_id\"
							from dbo.DrugTherapySchemeMNNLink
							where DrugTherapyScheme_id in (select DrugTherapyScheme_id from DTS)
							  and (DrugTherapySchemeMNNLink_begDate is null or DrugTherapySchemeMNNLink_begDate <= :Date)
							  and (DrugTherapySchemeMNNLink_endDate is null or DrugTherapySchemeMNNLink_endDate >= :Date)
						";
						$resp = $this->queryResult($sql, $params);
						if (is_array($resp) && count($resp) > 0) {
							foreach ($resp as $row) {
								$drugList[] = $row["DrugMNN_id"];
							}
						}
					}
					if (count($drugList) == 0) {
						// Шуршим в EvnPrescrTreat и EvnDrug
						$sql = "
							select eptd.Drug_id as \"Drug_id\"
							from v_EvnPrescrTreatDrug eptd
							     inner join v_EvnPrescrTreat ept on ept.EvnPrescrTreat_id = eptd.EvnPrescrTreat_id
							where ept.EvnPrescrTreat_pid = :Evn_pid
							  and eptd.Drug_id is not null
							union all
							select eptd.Drug_id as \"Drug_id\"
							from v_EvnPrescrTreatDrug eptd
								 inner join v_EvnPrescrTreat ept on ept.EvnPrescrTreat_id = eptd.EvnPrescrTreat_id
							where ept.EvnPrescrTreat_pid = ept.EvnPrescrTreat_rid
							  and ept.EvnPrescrTreat_rid = :Evn_rid
							  and eptd.Drug_id is not null
							union all
							select d.Drug_id as \"Drug_id\"
							from v_EvnPrescrTreatDrug eptd
								 inner join v_EvnPrescrTreat ept on ept.EvnPrescrTreat_id = eptd.EvnPrescrTreat_id
								 inner join rls.v_Drug d on d.DrugComplexMnn_id = eptd.DrugComplexMnn_id
							where ept.EvnPrescrTreat_pid = :Evn_pid
							union all
							select d.Drug_id as \"Drug_id\"
							from v_EvnPrescrTreatDrug eptd
								 inner join v_EvnPrescrTreat ept on ept.EvnPrescrTreat_id = eptd.EvnPrescrTreat_id
								 inner join rls.v_Drug d on d.DrugComplexMnn_id = eptd.DrugComplexMnn_id
							where ept.EvnPrescrTreat_pid = ept.EvnPrescrTreat_rid
							  and ept.EvnPrescrTreat_rid = :Evn_rid
							union all
							select Drug_id as \"Drug_id\"
							from v_EvnDrug
							where EvnDrug_pid = :Evn_pid
							union all
							select Drug_id as \"Drug_id\"
							from v_EvnDrug
							where EvnDrug_pid = EvnDrug_rid
							  and EvnDrug_rid = :Evn_rid
						";
						$resp = $this->queryResult($sql, $params);
						if (is_array($resp) && count($resp) > 0) {
							foreach ($resp as $row) {
								if (in_array($row["Drug_id"], $drugList)) {
									continue;
								}
								$drugList[] = $row["Drug_id"];
							}
							if (count($drugList) > 0) {
								$drugListString = implode(",", $drugList);
								$filterList["ACTMATTERS_ID"] = "
									d.ACTMATTERS_ID in (
										select DrugMnn_id
										from rls.v_Drug
										where Drug_id in ({$drugListString})
									)
								";
							}
						}
					} else {
						$drugListString = implode(",", $drugList);
						$filterList['DrugMNNList'] = "d.DrugMNN_id in ({$drugListString})";
					}
					$filterListString = implode(" and ", $filterList);
					$sql = "
						select
							d.DrugMNN_id as \"DrugMNN_id\",
							d.DrugMNN_Code as \"DrugMNN_Code\",
							d.DrugMNN_Name as \"DrugMNN_Name\"
						from fed.DrugMNN d
						where {$filterListString}
						limit 1
					";
					$resp = $this->queryResult($sql, $params);
					if ($resp === false || !is_array($resp) || count($resp) == 0) {
						if (array_key_exists("DrugMNNList", $filterList)) {
							unset($filterList["DrugMNNList"]);
						}

						if (array_key_exists("ACTMATTERS_ID", $filterList)) {
							unset($filterList["ACTMATTERS_ID"]);
						}
					}
				}
			}
			if (!empty($data["query"])) {
				$filterList["query"] = "cast(d.DrugMNN_Code as varchar(10))||' '||d.DrugMNN_Name ilike :query";
				$params["query"] = "%" . $data["query"] . "%";
			}
		}
		$filterListString = implode(" and ", $filterList);
		$sql = "
			select
				d.DrugMNN_id as \"DrugMNN_id\",
				d.DrugMNN_Code as \"DrugMNN_Code\",
				d.DrugMNN_Name as \"DrugMNN_Name\"
			from
				fed.DrugMNN d
			where {$filterListString}
			limit 200
		";
		return $this->queryResult($sql, $params);
	}

	/**
	 * @param $data
	 * @return array|false
	 */
	function loadSelectionList($data)
	{
		$params = [
			"MorbusOnko_id" => $data["MorbusOnko_id"]
		];
		$query = "
			select
				MOD.MorbusOnkoDrug_id as \"MorbusOnkoDrug_id\",
				to_char(MOD.MorbusOnkoDrug_begDT, '{$this->dateTimeForm104}') as \"MorbusOnkoDrug_begDate\",
				to_char(MOD.MorbusOnkoDrug_endDT, '{$this->dateTimeForm104}') as \"MorbusOnkoDrug_endDate\",
				case when MOD.DrugDictType_id = 1
					then CA.NAME else OD.OnkoDrug_Name
				end as \"Prep_Name\",
				D.DrugMNN_Name as \"OnkoDrug_Name\"
			from
				v_MorbusOnkoDrug MOD
				inner join v_MorbusOnko MO on MO.MorbusOnko_id = MOD.MorbusOnko_id
				left join v_OnkoDrug OD on OD.OnkoDrug_id = MOD.OnkoDrug_id
				left join rls.v_CLSATC CA on CA.CLSATC_ID = MOD.CLSATC_id
				left join fed.DrugMNN D on D.DrugMNN_id = MOD.DrugMNN_id
				left join v_Evn Evn on Evn.Evn_id = MOD.Evn_id
			where MO.MorbusOnko_id = :MorbusOnko_id
			  and coalesce(Evn.EvnClass_SysNick, '') not in ('EvnUslugaOnkoChem','EvnUslugaOnkoChemBeam')
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function setEvn($data)
	{
		$ids = $data["MorbusOnkoDrug_ids"];
		if (!is_array($ids) && count($ids)) {
			throw new Exception("Не передано идентификаторов препаратов");
		}
		foreach ($ids as $id) {
			$resp = $this->swUpdate("MorbusOnkoDrug", [
				"MorbusOnkoDrug_id" => $id,
				"Evn_id" => $data["Evn_id"],
				"pmUser_id" => $data["pmUser_id"],
			]);
			if (!$this->isSuccessful($resp)) {
				return $resp;
			}
		}
		return [["success" => true]];
	}

	/**
	 * @param array $data
	 * @return array|false
	 */
	function getViewData($data)
	{
	    $filterList = ["(1 = 1)"];
        $params = [
            "MorbusOnko_pid" => $data["MorbusOnko_pid"],
        ];

	    if(!empty($data["Morbus_id"])) {
            $filterList[] = "MO.Morbus_id = :Morbus_id";
            $params["Morbus_id"] = $data["Morbus_id"];
        }

		if (!empty($data["MorbusOnko_pid"])) {
			$EvnClass_SysNick = $this->getFirstResultFromQuery("
				select EvnClass_SysNick as \"EvnClass_SysNick\"
				from v_Evn
				where Evn_id = :Evn_id
				limit 1
			", ["Evn_id" => $data["MorbusOnko_pid"]]);
			if ($EvnClass_SysNick !== false && !empty($EvnClass_SysNick)) {
				switch ($EvnClass_SysNick) {
					case "EvnDiagPLStom":
						$filterList[] = "MOD.MorbusOnkoDiagPLStom_id = :MorbusOnkoDiagPLStom_id";
						$params["MorbusOnkoDiagPLStom_id"] = (!empty($data["MorbusOnkoDiagPLStom_id"]) ? $data["MorbusOnkoDiagPLStom_id"] : null);
						break;

					case "EvnSection":
						$filterList[] = "MOD.MorbusOnkoLeave_id = :MorbusOnkoLeave_id";
						$params["MorbusOnkoLeave_id"] = (!empty($data["MorbusOnkoLeave_id"]) ? $data["MorbusOnkoLeave_id"] : null);
						break;

					case "EvnVizitPL":
						$filterList[] = "MOD.MorbusOnkoVizitPLDop_id = :MorbusOnkoVizitPLDop_id";
						$params["MorbusOnkoVizitPLDop_id"] = (!empty($data["MorbusOnkoVizitPLDop_id"]) ? $data["MorbusOnkoVizitPLDop_id"] : null);
						break;
				}
			}
		} else if (!empty($data["MorbusOnko_id"])) {
			$filterList[] = "MOD.MorbusOnko_id = :MorbusOnko_id";
			$params["MorbusOnko_id"] = $data["MorbusOnko_id"];
		}

		$filterListString = implode(" and ", $filterList);
		$query = "
			select
				case
					when Evn.EvnClass_SysNick is null OR
					Evn.EvnClass_SysNick not in (
						'EvnUslugaOnkoChem', 
						'EvnUslugaOnkoBeam', 
						'EvnUslugaOnkoChemBeam', 
						'EvnUslugaOnkoGormun', 
						'EvnUslugaOnkoSurg'
					) then 'edit'
					else 'view'
				end as \"accessType\",
				MOD.MorbusOnkoDrug_id as \"MorbusOnkoDrug_id\",
				MOD.MorbusOnko_id as \"MorbusOnko_id\",
				MOD.MorbusOnkoLeave_id as \"MorbusOnkoLeave_id\",
				MOD.MorbusOnkoVizitPLDop_id as \"MorbusOnkoVizitPLDop_id\",
				MOD.MorbusOnkoDiagPLStom_id as \"MorbusOnkoDiagPLStom_id\",
				:MorbusOnko_pid as \"MorbusOnko_pid\",
				MO.Morbus_id as \"Morbus_id\",
				to_char(MOD.MorbusOnkoDrug_begDT, '{$this->dateTimeForm104}') as \"MorbusOnkoDrug_begDate\",
				to_char(MOD.MorbusOnkoDrug_endDT, '{$this->dateTimeForm104}') as \"MorbusOnkoDrug_endDate\",
				case when MOD.DrugDictType_id = 1
					then CA.NAME else OD.OnkoDrug_Name
				end as \"Prep_Name\",
				CA.CLSATC_id as \"CLSATC_id\",
				CA.NAME as \"CLSATC_Name\",
				FDM.DrugMNN_id as \"DrugMNN_id\",
				FDM.DrugMNN_Code as \"DrugMNN_Code\",
				FDM.DrugMNN_Name as \"DrugMNN_Name\",
				coalesce(FDM.DrugMNN_Name, OD.OnkoDrug_Name) as \"OnkoDrug_Name\"
			from
				v_MorbusOnkoDrug MOD
				inner join v_MorbusOnko MO on MO.MorbusOnko_id = MOD.MorbusOnko_id
				left join v_OnkoDrug OD on OD.OnkoDrug_id = MOD.OnkoDrug_id
				left join rls.v_CLSATC CA on CA.CLSATC_ID = MOD.CLSATC_id
				left join rls.v_Drug D on D.Drug_id = MOD.Drug_id
				left join fed.DrugMNN FDM on FDM.DrugMNN_id = MOD.DrugMNN_id
				left join v_Evn Evn on Evn.Evn_id = MOD.Evn_id
			where  {$filterListString}";
		return $this->queryResult($query, $params);
	}
}
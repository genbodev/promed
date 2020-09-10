<?php
defined("BASEPATH") or die ("No direct script access allowed");
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Модель для объектов Анализатор
 *
 * @package		lis
 * @access	   public
 * @copyright	Copyright (c) 2010-2011 Swan Ltd.
 * @author	   gabdushev
 * @version
 *
 * @property CI_DB_driver $db
 * @property Lis_UslugaComplexMedService_model $Lis_UslugaComplexMedService_model
 * @property AnalyzerTestRefValues_model $AnalyzerTestRefValues_model
 */
class Analyzer_model extends SwPgModel
{
	private $Analyzer_id;//Идентификатор
	private $Analyzer_Name;//Наименование анализатора
	private $Analyzer_Code;//Код
	private $AnalyzerModel_id;//Модель анализатора
	private $MedService_id;//Служба
	private $Analyzer_begDT;//Дата открытия
	private $Analyzer_endDT;//Дата закрытия
	private $Analyzer_LisClientId;//Id клиента
	private $Analyzer_LisCompany;//Наименование ЛПУ
	private $Analyzer_LisLab;//Наименование лаборатории
	private $Analyzer_LisMachine;//Название машины в ЛИС
	private $Analyzer_LisLogin;//Логин в ЛИС
	private $Analyzer_LisPassword;//Пароль
	private $Analyzer_LisNote;//Примечание
	private $pmUser_id;//Идентификатор пользователя системы Промед

	/**
	 * @return mixed
	 */
	public function getAnalyzer_id()
	{
		return $this->Analyzer_id;
	}

	/**
	 * @param $value
	 */
	public function setAnalyzer_id($value)
	{
		$this->Analyzer_id = $value;
	}

	/**
	 * @return mixed
	 */
	public function getAnalyzer_Name()
	{
		return $this->Analyzer_Name;
	}

	/**
	 * @param $value
	 */
	public function setAnalyzer_Name($value)
	{
		$this->Analyzer_Name = $value;
	}

	/**
	 * @return mixed
	 */
	public function getAnalyzer_Code()
	{
		return $this->Analyzer_Code;
	}

	/**
	 * @param $value
	 */
	public function setAnalyzer_Code($value)
	{
		$this->Analyzer_Code = $value;
	}

	/**
	 * @return mixed
	 */
	public function getAnalyzerModel_id()
	{
		return $this->AnalyzerModel_id;
	}

	/**
	 * @param $value
	 */
	public function setAnalyzerModel_id($value)
	{
		$this->AnalyzerModel_id = $value;
	}

	/**
	 * @return mixed
	 */
	public function getMedService_id()
	{
		return $this->MedService_id;
	}

	/**
	 * @param $value
	 */
	public function setMedService_id($value)
	{
		$this->MedService_id = $value;
	}

	/**
	 * @return mixed
	 */
	public function getAnalyzer_begDT()
	{
		return $this->Analyzer_begDT;
	}

	/**
	 * @param $value
	 */
	public function setAnalyzer_begDT($value)
	{
		$this->Analyzer_begDT = $value;
	}

	/**
	 * @return mixed
	 */
	public function getAnalyzer_endDT()
	{
		return $this->Analyzer_endDT;
	}

	/**
	 * @param $value
	 */
	public function setAnalyzer_endDT($value)
	{
		$this->Analyzer_endDT = $value;
	}

	/**
	 * @return mixed
	 */
	public function getAnalyzer_LisClientId()
	{
		return $this->Analyzer_LisClientId;
	}

	/**
	 * @param $value
	 */
	public function setAnalyzer_LisClientId($value)
	{
		$this->Analyzer_LisClientId = $value;
	}

	/**
	 * @return mixed
	 */
	public function getAnalyzer_LisCompany()
	{
		return $this->Analyzer_LisCompany;
	}

	/**
	 * @param $value
	 */
	public function setAnalyzer_LisCompany($value)
	{
		$this->Analyzer_LisCompany = $value;
	}

	/**
	 * @return mixed
	 */
	public function getAnalyzer_LisLab()
	{
		return $this->Analyzer_LisLab;
	}

	/**
	 * @param $value
	 */
	public function setAnalyzer_LisLab($value)
	{
		$this->Analyzer_LisLab = $value;
	}

	/**
	 * @return mixed
	 */
	public function getAnalyzer_LisMachine()
	{
		return $this->Analyzer_LisMachine;
	}

	/**
	 * @param $value
	 */
	public function setAnalyzer_LisMachine($value)
	{
		$this->Analyzer_LisMachine = $value;
	}

	/**
	 * @return mixed
	 */
	public function getAnalyzer_LisLogin()
	{
		return $this->Analyzer_LisLogin;
	}

	/**
	 * @param $value
	 */
	public function setAnalyzer_LisLogin($value)
	{
		$this->Analyzer_LisLogin = $value;
	}

	/**
	 * @return mixed
	 */
	public function getAnalyzer_LisPassword()
	{
		return $this->Analyzer_LisPassword;
	}

	/**
	 * @param $value
	 */
	public function setAnalyzer_LisPassword($value)
	{
		$this->Analyzer_LisPassword = $value;
	}

	/**
	 * @return mixed
	 */
	public function getAnalyzer_LisNote()
	{
		return $this->Analyzer_LisNote;
	}

	/**
	 * @param $value
	 */
	public function setAnalyzer_LisNote($value)
	{
		$this->Analyzer_LisNote = $value;
	}

	/**
	 * @return mixed
	 */
	public function getequipment_id()
	{
		return $this->equipment_id;
	}

	/**
	 * @param $value
	 */
	public function setequipment_id($value)
	{
		$this->equipment_id = $value;
	}

	/**
	 * @return mixed
	 */
	public function getpmUser_id()
	{
		return $this->pmUser_id;
	}

	/**
	 * @param $value
	 */
	public function setpmUser_id($value)
	{
		$this->pmUser_id = $value;
	}

	/**
	 * Analyzer_model constructor.
	 * @throws Exception
	 */
	function __construct()
	{
		parent::__construct();
		if (!isset($_SESSION["pmuser_id"])) {
			throw new Exception("Значение pmuser_id не установлено в текущей сессии (не выполнен вход в Промед?)");
		}
		$this->setpmUser_id($_SESSION["pmuser_id"]);
	}

	/**
	 *	Сохранение признака(активности, связи, учёта) анализатора
	 */
	function saveAnalyzerField($data)
	{
		if(!isset($data['Analyzer_IsNotActive'])
			&& !isset($data['Analyzer_2wayComm'])
			&& !isset($data['Analyzer_IsUseAutoReg']))
			return false;

		$set = 'Analyzer_IsNotActive = :Analyzer_IsNotActive';
		if ($data['Analyzer_2wayComm'])
			$set = "Analyzer_2wayComm = :Analyzer_2wayComm";
		if ($data['Analyzer_IsUseAutoReg'])
			$set = "Analyzer_IsUseAutoReg = :Analyzer_IsUseAutoReg";
		$query = "
			update
				lis.Analyzer
			set
				{$set}
			where
				Analyzer_id = :Analyzer_id
		";

		$this->db->query($query, $data);

		return true;
	}

	/**
	 * Загрузка
	 * @param null $data
	 * @return array|bool|CI_DB_result|mixed
	 */
	function load($data = null)
	{
		$query = "
			select
				a.Analyzer_id as \"Analyzer_id\",
				a.Analyzer_Name as \"Analyzer_Name\",
				a.Analyzer_Code as \"Analyzer_Code\",
				a.AnalyzerModel_id as \"AnalyzerModel_id\",
				a.MedService_id as \"MedService_id\",
				to_char(a.Analyzer_begDT, 'dd.mm.yyyy') as \"Analyzer_begDT\",
				to_char(a.Analyzer_endDT, 'dd.mm.yyyy') as \"Analyzer_endDT\",
				a.Analyzer_LisClientId as \"Analyzer_LisClientId\",
				a.Analyzer_LisCompany as \"Analyzer_LisCompany\",
				a.Analyzer_LisLab as \"Analyzer_LisLab\",
				a.Analyzer_LisMachine as \"Analyzer_LisMachine\",
				a.Analyzer_LisLogin as \"Analyzer_LisLogin\",
				a.Analyzer_LisPassword as \"Analyzer_LisPassword\",
				a.Analyzer_LisNote as \"Analyzer_LisNote\",
				case when a.Analyzer_2wayComm = 2 then 1 else 0 end as \"Analyzer_2wayComm\",
				case when a.Analyzer_IsUseAutoReg = 2 then 1 else 0 end as \"Analyzer_IsUseAutoReg\",
				case when a.Analyzer_IsNotActive = 2 then 1 else 0 end as \"Analyzer_IsNotActive\",
				case when a.Analyzer_IsAutoOk = 2 then 1 else 0 end as \"Analyzer_IsAutoOk\",
				case when a.Analyzer_IsAutoGood = 2 then 1 else 0 end as \"Analyzer_IsAutoGood\",
				case 
					when a.Analyzer_IsAutoOk = 2 and a.Analyzer_IsAutoGood = 2 then 2
					when a.Analyzer_IsAutoOk = 2 and a.Analyzer_IsAutoGood = 1 then 1
					else 0
				end as \"AutoOkType\",
			    case when a.Analyzer_IsManualTechnic = 2 then 1 else 0 end as \"Analyzer_IsManualTechnic\",
				link.equipment_id as \"equipment_id\"
			from
				lis.v_Analyzer a
				left join lateral (
					select lis_id as equipment_id
					from lis.v_Link l
					where l.object_id = a.Analyzer_id and l.link_object = 'Analyzer'
					limit 1	
				) as link on true
			where Analyzer_id = :Analyzer_id
		";
		/**@var CI_DB_result $result */
		if (empty($data)) {
			$data["Analyzer_id"] = $this->Analyzer_id;
		}
		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result_array();
		if (!isset($result[0])) {
			return false;
		}
		$this->Analyzer_id = $result[0]["Analyzer_id"];
		$this->Analyzer_Name = $result[0]["Analyzer_Name"];
		$this->Analyzer_Code = $result[0]["Analyzer_Code"];
		$this->AnalyzerModel_id = $result[0]["AnalyzerModel_id"];
		$this->MedService_id = $result[0]["MedService_id"];
		$this->Analyzer_begDT = $result[0]["Analyzer_begDT"];
		$this->Analyzer_endDT = $result[0]["Analyzer_endDT"];
		$this->Analyzer_LisClientId = $result[0]["Analyzer_LisClientId"];
		$this->Analyzer_LisCompany = $result[0]["Analyzer_LisCompany"];
		$this->Analyzer_LisLab = $result[0]["Analyzer_LisLab"];
		$this->Analyzer_LisMachine = $result[0]["Analyzer_LisMachine"];
		$this->Analyzer_LisLogin = $result[0]["Analyzer_LisLogin"];
		$this->Analyzer_LisPassword = $result[0]["Analyzer_LisPassword"];
		$this->Analyzer_LisNote = $result[0]["Analyzer_LisNote"];
		$this->equipment_id = $result[0]["equipment_id"];
		return $result;
	}

	/**
	 * Загрузка списка
	 * @param $filter
	 * @return array|bool|CI_DB_result|mixed
	 */
	function loadList($filter)
	{
		if (!empty($filter["EvnLabSamples"])) {
			// будем для каждой пробы вызывать запрос получения анализаторов, затем всё это мержить, т.к. как всё выгрести одним запросом неясно.
			$EvnLabSamples = json_decode($filter["EvnLabSamples"], true);
			$resp = [];
			$filter["EvnLabSamples"] = null;
			$count = 0;
			foreach ($EvnLabSamples as $EvnLabSample) {
				$filter["EvnLabSample_id"] = $EvnLabSample;
				$analyzers = $this->loadList($filter);
				$newanalyzers = [];
				foreach ($analyzers as $analyzer) {
					$newanalyzers[$analyzer["Analyzer_id"]] = $analyzer;
					$newanalyzers[$analyzer["Analyzer_id"]]["disabled"] = false;
					if ($count > 0) {
						// помечаем все которых нет неактивными
						if (empty($resp[$analyzer["Analyzer_id"]])) {
							$newanalyzers[$analyzer["Analyzer_id"]]["disabled"] = true;
						}
					}
				}
				// помечаем все которых нет неактивными
				foreach ($resp as $analyzer) {
					if (empty($newanalyzers[$analyzer["Analyzer_id"]])) {
						$resp[$analyzer["Analyzer_id"]]["disabled"] = true;
					}
				}
				// сливаем всё в $resp
				foreach ($newanalyzers as $analyzer) {
					if (empty($resp[$analyzer["Analyzer_id"]])) {
						$resp[$analyzer["Analyzer_id"]] = $analyzer;
					}
				}
				$count++;
			}
			return $resp;
		}
		$where = [];
		$join = "";
		if (isset($filter["hideRuchMetodiki"]) && $filter["hideRuchMetodiki"]) {
			// скрыть ручные методики
			$where[] = "(A.pmUser_insID <> 1 OR A.Analyzer_Code <> '000')";
		}
		$fields = [
			"Analyzer_id",
			"Analyzer_Name",
			"Analyzer_Code",
			"AnalyzerModel_id",
			"MedService_id",
			"Analyzer_begDT",
			"Analyzer_endDT",
			"Analyzer_LisClientId",
			"Analyzer_LisCompany",
			"Analyzer_LisLab",
			"Analyzer_LisMachine",
			"Analyzer_LisLogin",
			"Analyzer_LisPassword",
			"Analyzer_LisNote"
		];
		foreach ($fields as $field) {
			if (isset($filter[$field]) && $filter[$field]) {
				$where[] = "A.{$field} = :{$field}";
			}
		}
		if (isset($filter["Analyzer_IsNotActive"]) && $filter["Analyzer_IsNotActive"]) {
			$where[] = "COALESCE(A.Analyzer_IsNotActive, 1) = :Analyzer_IsNotActive";
			// также не показываем закрытые анализаторы
			if ($filter["Analyzer_IsNotActive"] == 1) {
				$where[] = "(A.Analyzer_endDT >= dbo.tzgetdate() OR A.Analyzer_endDT IS NULL)";
			}
		}
		$filterChild = "";
		/**@var CI_DB_result $result */
		if (isset($filter["EvnLabSample_id"])) {
			$uccodes = [];
			if (!empty($filter["uccodes"])) {
				$uccodes = $filter["uccodes"];
			} else {
				// получаем список исследований пробы
				$query = "
					select
						uc.UslugaComplex_id as \"UslugaComplexTest_id\",
						euinp.UslugaComplex_id as \"UslugaComplexTarget_id\"
					from
						v_UslugaComplex uc
						inner join v_EvnUslugaPar euin on euin.UslugaComplex_id = uc.UslugaComplex_id
						inner join v_EvnLabSample els on els.EvnLabSample_id = euin.EvnLabSample_id
						inner join v_EvnUslugaPar euinp on euinp.EvnUslugaPar_id = euin.EvnUslugaPar_pid -- корневая услуга
					where els.EvnLabSample_id = :EvnLabSample_id
				";
				$result = $this->db->query($query, $filter);
				if (is_object($result)) {
					$uccodes = $result->result_array();
				}
			}
			$uccodes_count = 0;
			if (count($uccodes) > 0) {
				// т.к. исселдование в пробе может быть и не одно, собираем в массив тесты к их исследованиям
				$researches = [];
				foreach ($uccodes as $respone) {
					if (empty($researches[$respone["UslugaComplexTarget_id"]])) {
						$researches[$respone["UslugaComplexTarget_id"]] = [];
					}
					if (!in_array($respone["UslugaComplexTest_id"], $researches[$respone["UslugaComplexTarget_id"]])) {
						$researches[$respone["UslugaComplexTarget_id"]][] = $respone["UslugaComplexTest_id"];
						$uccodes_count++;
					}
				}
				$filterChildAr = [];
				foreach (array_keys($researches) as $key) {
					$filterChildAr[] = "(ucms_child.UslugaComplex_id IN ('" . implode("','", $researches[$key]) . "') and at_ucms.UslugaComplex_id = '{$key}')";
				}
				$filterChild = "";
				if (count($filterChildAr) > 0) {
					$filterChild = " and (" . implode(" or ", $filterChildAr) . ")";
				}
			}
		}
		// если есть фильтрация по составу, то отображаем анализаторы при наличии хоть одной услуги на нём
		if (!empty($uccodes_count) && $uccodes_count > 0) {
			$join .= "
				inner join lateral( 
					select at_child.AnalyzerTest_id 
					FROM 
						lis.v_AnalyzerTest at
						inner join v_UslugaComplexMedService at_ucms on at_ucms.UslugaComplexMedService_id = at.UslugaComplexMedService_id
						inner join lis.v_AnalyzerTest at_child on (case when at.AnalyzerTest_IsTest = 2 then at_child.AnalyzerTest_id else at_child.AnalyzerTest_pid end) = at.AnalyzerTest_id
						inner join v_UslugaComplexMedService ucms_child on ucms_child.UslugaComplexMedService_id = at_child.UslugaComplexMedService_id
					where at.Analyzer_id = a.Analyzer_id
					  and at.AnalyzerTest_pid is null
					  {$filterChild}
					  and COALESCE(AT.AnalyzerTest_IsNotActive, 1) = 1
					  and COALESCE(at_child.AnalyzerTest_IsNotActive, 1) = 1
					limit 1	
				) at on true
			";
		}
		$where_clause = implode(" AND ", $where);
		$where_clause = (strlen($where_clause)) ? "WHERE " . $where_clause : "WHERE (1=1)";
		$selectString = "
			A.Analyzer_id as \"Analyzer_id\",
			A.Analyzer_Name as \"Analyzer_Name\",
			A.Analyzer_Code as \"Analyzer_Code\",
			A.pmUser_insID as \"pmUser_insID\",
			A.AnalyzerModel_id as \"AnalyzerModel_id\",
			A.MedService_id as \"MedService_id\",
			to_char(A.Analyzer_begDT, 'dd.mm.yyyy') as \"Analyzer_begDT\",
			to_char(A.Analyzer_endDT,'dd.mm.yyyy') as \"Analyzer_endDT\",
			A.Analyzer_LisClientId as \"Analyzer_LisClientId\",
			A.Analyzer_LisCompany as \"Analyzer_LisCompany\",
			A.Analyzer_LisLab as \"Analyzer_LisLab\",
			A.Analyzer_LisMachine as \"Analyzer_LisMachine\",
			A.Analyzer_LisLogin as \"Analyzer_LisLogin\",
			A.Analyzer_LisPassword as \"Analyzer_LisPassword\",
			A.Analyzer_LisNote as \"Analyzer_LisNote\",
			case when A.Analyzer_2wayComm = 2 then 1 else 0 end as \"Analyzer_2wayComm\",
			case when A.Analyzer_IsUseAutoReg = 2 then 1 else 0 end as \"Analyzer_IsUseAutoReg\",
			case when A.Analyzer_IsNotActive = 2 then 1 else 0 end as \"Analyzer_IsNotActive\",
			case when A.Analyzer_IsAutoOk = 2 then 1 else 0 end as \"Analyzer_IsAutoOk\",
			case when a.Analyzer_IsAutoGood = 2 then 1 else 0 end as \"Analyzer_IsAutoGood\",
			AnalyzerModel_id_ref.AnalyzerModel_Name as \"AnalyzerModel_id_Name\",
			MedService_id_ref.MedService_Name as \"MedService_id_Name\"
		";
		$fromString = "
			lis.v_Analyzer A
			left join lis.v_AnalyzerModel AnalyzerModel_id_ref ON AnalyzerModel_id_ref.AnalyzerModel_id = A.AnalyzerModel_id
			left join dbo.v_MedService MedService_id_ref ON MedService_id_ref.MedService_id = A.MedService_id
			{$join}
		";
		$query = "
			select {$selectString}
			from {$fromString}
			{$where_clause}
		";
		$result = $this->db->query($query, $filter);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result_array();
		if (count($result) > 0) {
			return $result;
		}
		$query = "
			select
				A.Analyzer_id as \"Analyzer_id\",
				A.Analyzer_Name as \"Analyzer_Name\",
				A.Analyzer_Code as \"Analyzer_Code\",
				A.pmUser_insID as \"pmUser_insID\",
				A.AnalyzerModel_id as \"AnalyzerModel_id\",
				A.MedService_id as \"MedService_id\",
				to_char (A.Analyzer_begDT, 'dd.mm.yyyy') as \"Analyzer_begDT\",
				to_char(A.Analyzer_endDT,'dd.mm.yyyy') as \"Analyzer_endDT\",
				A.Analyzer_LisClientId as \"Analyzer_LisClientId\",
				A.Analyzer_LisCompany as \"Analyzer_LisCompany\",
				A.Analyzer_LisLab as \"Analyzer_LisLab\",
				A.Analyzer_LisMachine as \"Analyzer_LisMachine\",
				A.Analyzer_LisLogin as \"Analyzer_LisLogin\",
				A.Analyzer_LisPassword as \"Analyzer_LisPassword\",
				A.Analyzer_LisNote as \"Analyzer_LisNote\",
				case when A.Analyzer_2wayComm = 2 then 1 else 0 end as \"Analyzer_2wayComm\",
				case when A.Analyzer_IsUseAutoReg = 2 then 1 else 0 end as \"Analyzer_IsUseAutoReg\",
				case when A.Analyzer_IsNotActive = 2 then 1 else 0 end as \"Analyzer_IsNotActive\",
				case when A.Analyzer_IsAutoOk = 2 then 1 else 0 end as \"Analyzer_IsAutoOk\",
				case when a.Analyzer_IsAutoGood = 2 then 1 else 0 end as \"Analyzer_IsAutoGood\",
				AnalyzerModel_id_ref.AnalyzerModel_Name as \"AnalyzerModel_id_Name\",
				MedService_id_ref.MedService_Name as \"MedService_id_Name\"
			from
				lis.v_Analyzer A
				left join lis.v_AnalyzerModel AnalyzerModel_id_ref on AnalyzerModel_id_ref.AnalyzerModel_id = A.AnalyzerModel_id
				left join dbo.v_MedService MedService_id_ref on MedService_id_ref.MedService_id = A.MedService_id
			{$where_clause}
		";
		$result = $this->db->query($query, $filter);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * Сохранение ссылки
	 * @param $data
	 * @return bool
	 */
	function saveEquimpentLink($data)
	{
		/**@var CI_DB_result $result */
		$query = "
			select Link_id as \"Link_id\"
			from lis.v_Link
			where object_id = :Analyzer_id
			  and link_object = 'Analyzer'
			limit 1	
		";
		$result = $this->db->query($query, $data);
		$data["Link_id"] = null;
		if (is_object($result)) {
			$resp = $result->result("array");
			if (count($resp) > 0 && !empty($resp[0]["Link_id"])) {
				$data["Link_id"] = $resp[0]["Link_id"];
			}
		}
		$procedure = (!empty($data["Link_id"])) ? "p_Link_upd" : "p_Link_ins";
		$selectString = "
			link_id as \"Link_id\",
			error_code as \"Error_Code\", 
			error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from lis.{$procedure} (
				Link_id := :Link_id,
				link_object := 'Analyzer',
				lis_id := :equipment_id,
				object_id := :Analyzer_id,
				pmUser_id := :pmUser_id
			)   
		";
		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		$resp = $result->result_array();
		return (count($resp) > 0 && !empty($resp[0]["Error_Msg"])) ? false : true;
	}

	/**
	 * Проверка на связь анализатора
	 * @param $data
	 * @return bool
	 */
	function checkAnalyzerHasLinkAllready($data)
	{
		/**@var CI_DB_result $result */
		if (empty($data["Analyzer_id"])) {
			$data["Analyzer_id"] = null;
		}
		// проверяем наличие связи
		$query = "
			select Link_id as \"Link_id\"
			from lis.v_Link
			where lis_id = :equipment_id
			  and link_object = 'Analyzer'
			  and (object_id <> :Analyzer_id or :Analyzer_id is null)
			limit 1	
		";
		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result_array();
		return (count($result) > 0 && !empty($result[0]["Link_id"])) ? false : true;
	}

	/**
	 * Добавление услуги теста в состав услуги исследования
	 * @param $data
	 * @return mixed
	 */
	function addUslugaComplexTargetTestComposition($data)
	{
		/**@var CI_DB_result $result */
		// 1. ищем, возможно уже есть связь указанных услуг
		$query = "
			select ucc.UslugaComplexComposition_id as \"UslugaComplexComposition_id\"
			from v_UslugaComplexComposition ucc
			where ucc.UslugaComplex_pid = :UslugaComplexTarget_id
			  and ucc.UslugaComplex_id = :UslugaComplexTest_id
			limit 1		
		";
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$result = $result->result_array();
			if (count($result) > 0 && !empty($result[0]["UslugaComplexComposition_id"])) {
				return $result[0]["UslugaComplexComposition_id"];
			}
		}
		// 2. если не нашли связь то добавляем услугу в состав
		$query = "
			select 
				UslugaComplexComposition_id as \"UslugaComplexComposition_id\",
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from p_UslugaComplexComposition_ins(
				UslugaComplex_id := :UslugaComplexTest_id,
				UslugaComplex_pid := :UslugaComplexTarget_id,
				pmUser_id := :pmUser_id
			)
		";
		$result = $this->queryResult($query, $data);
		return $result[0]["UslugaComplexComposition_id"];
	}

	/**
	 * Добавление услуги ЛИС
	 * @param $data
	 * @param $UslugaComplex_Code
	 * @param $UslugaComplex_Name
	 * @return array|bool|CI_DB_result|mixed|null
	 */
	function addUslugaComplexFromLis($data, $UslugaComplex_Code, $UslugaComplex_Name)
	{
		$data["UslugaComplex_Code"] = $UslugaComplex_Code;
		$data["UslugaComplex_Name"] = $UslugaComplex_Name;
		// 0. ищем услугу ГОСТ-2011
		$UslugaComplex_2011id = null;
		$query = "
			select uc.UslugaComplex_id as \"UslugaComplex_id\"
			from
				v_UslugaComplex uc
				inner join v_UslugaCategory ucat on ucat.UslugaCategory_id = uc.UslugaCategory_id
			where uc.UslugaComplex_Code = :UslugaComplex_Code
			  and ucat.UslugaCategory_SysNick = 'gost2011'
			limit 1	
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$resp = $result->result_array();
			if (count($resp) > 0 && !empty($resp[0]["UslugaComplex_id"])) {
				$UslugaComplex_2011id = $resp[0]["UslugaComplex_id"];
			}
		}
		if (empty($UslugaComplex_2011id)) {
			return false;
		}
		$data["UslugaComplex_2011id"] = $UslugaComplex_2011id;

		// Теперь услуга строго по госту(refs #PROMEDWEB-9543)
		$UslugaComplex_id = $data['UslugaComplex_2011id'];
		
		// добавляем услуге атрибут "Лабораторно-диагностическая"
		$UslugaComplexAttr = $this->addUslugaComplexAttr([
			"UslugaComplex_id" => $UslugaComplex_id,
			"UslugaComplexAttributeType_SysNick" => "lab",
			"pmUser_id" => $data['pmUser_id']
		]);
		if ($UslugaComplexAttr !== true)
			return $UslugaComplexAttr;
		
		return $UslugaComplex_id;
	}

	/**
	 * Добавление услуги на службу из исследования ЛИС
	 * @param $data
	 * @return mixed|null
	 * @throws Exception
	 */
	function addUslugaComplexTargetMedService($data)
	{
		/**@var CI_DB_result $result */
		$data["UslugaComplexMedService_id"] = null;
		// сначала услугу превращаем в связанную гостовскую услугу!
		$data["UslugaComplexToAdd_id"] = $data["UslugaComplexTarget_id"];
		$query = "
			select UslugaComplex_2011id as \"UslugaComplex_2011id\"
			from v_UslugaComplex
			where UslugaComplex_id = :UslugaComplexToAdd_id
		";
		$UslugaComplex_2011id = $this->getFirstResultFromQuery($query, $data);
		if (!empty($UslugaComplex_2011id)) {
			$data["UslugaComplexToAdd_id"] = $UslugaComplex_2011id;
		}
		// 1. сначала ищем, может уже добавлена на службу такая услуга
		$query = "
			select ucm.UslugaComplexMedService_id as \"UslugaComplexMedService_id\"
			from v_UslugaComplexMedService ucm
			where ucm.UslugaComplex_id = :UslugaComplexToAdd_id
			  and ucm.MedService_id = :MedService_id
			  and ucm.UslugaComplexMedService_pid is null
		";
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$result = $result->result_array();
			if (count($result) > 0 && !empty($result[0]["UslugaComplexMedService_id"])) {
				$data["UslugaComplexMedService_id"] = $result[0]["UslugaComplexMedService_id"];
			}
		}
		if (empty($data["UslugaComplexMedService_id"])) {
			$this->load->model("Lis_UslugaComplexMedService_model");
			$funcParams = [
				"UslugaComplexMedService_id" => null,
				"scenario" => self::SCENARIO_DO_SAVE,
				"MedService_id" => $data["MedService_id"],
				"UslugaComplex_id" => $data["UslugaComplexToAdd_id"],
				"UslugaComplexMedService_begDT" => "@curDT",
				"UslugaComplexMedService_endDT" => null,
				"RefSample_id" => $data["RefSample_id"],
				"session" => $data["session"],
				"pmUser_id" => $data["pmUser_id"]
			];
			$resp = $this->Lis_UslugaComplexMedService_model->doSaveUslugaComplexMedService($funcParams);
			if (!empty($resp["UslugaComplexMedService_id"])) {
				$data["UslugaComplexMedService_id"] = $resp["UslugaComplexMedService_id"];
			}
		}
		// добавить исследование на анализатор
		if (!empty($data["UslugaComplexMedService_id"])) {
			$this->addAnalyzerTestForUslugaComplexMedService($data, null);
		}
		return $data["UslugaComplexMedService_id"];
	}

	/**
	 * Добавление услуги на службе на анализатор
	 * @param $data
	 * @param $test_id
	 * @throws Exception
	 */
	function addAnalyzerTestForUslugaComplexMedService($data, $test_id)
	{
		/**@var CI_DB_result $result */
		$data["AnalyzerTest_id"] = null;
		// 1. сначала ищем, может уже добавлена на анализатор такая услуга
		$query = "
			select at.AnalyzerTest_id as \"AnalyzerTest_id\"
			from lis.v_AnalyzerTest at
			where at.UslugaComplexMedService_id = :UslugaComplexMedService_id
			  and at.Analyzer_id = :Analyzer_id
		";
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$result = $result->result_array();
			if (count($result) > 0 && !empty($result[0]["AnalyzerTest_id"])) {
				$data["AnalyzerTest_id"] = $result[0]["AnalyzerTest_id"];
			}
		}
		if (empty($data["AnalyzerTest_id"])) {
			if (empty($data["AnalyzerTest_Code"])) {
				$data["AnalyzerTest_Code"] = null;
			}
			if (empty($data["AnalyzerTest_SysNick"])) {
				$data["AnalyzerTest_SysNick"] = null;
			}
			$data["AnalyzerTest_pid"] = null;
			// 2. добавляем услугу на анализатор
			$query = "
				select
                	AnalyzerTest_id as \"AnalyzerTest_id\",
					error_code as \"Error_Code\", 
					error_message as \"Error_Msg\"
                from lis.p_AnalyzerTest_ins(
					AnalyzerTest_id := :AnalyzerTest_id,
					AnalyzerTest_pid := (
						select parentAt.AnalyzerTest_id
						from
							lis.v_AnalyzerTest parentAt
							inner join v_UslugaComplexMedService parentUc on parentAt.UslugaComplexMedService_id = parentUc.UslugaComplexMedService_id
							inner join v_UslugaComplexMedService childUc on parentUc.UslugaComplexMedService_id = childUc.UslugaComplexMedService_pid
						where childUc.UslugaComplexMedService_id = :UslugaComplexMedService_id and parentAt.Analyzer_id = :Analyzer_id
	                    limit 1    
					),
					AnalyzerModel_id := null,
					Analyzer_id := :Analyzer_id,
					UslugaComplex_id := (
						select UslugaComplex_id
						from v_UslugaComplexMedService
						where UslugaComplexMedService_id = :UslugaComplexMedService_id
	                    limit 1    
					),
					AnalyzerTest_IsTest := :AnalyzerTest_IsTest,
					AnalyzerTestType_id := 1,
					Unit_id := null,
					AnalyzerTest_Code := :AnalyzerTest_Code,
					AnalyzerTest_Name := (
						select UslugaComplex_Name
						from v_UslugaComplex
						where UslugaComplex_id = UslugaComplex_id
	                    limit 1    
					),
					AnalyzerTest_SysNick := :AnalyzerTest_SysNick,
					AnalyzerTest_begDT := dbo.tzGetDate(),
					AnalyzerTest_endDT := null,
					UslugaComplexMedService_id := :UslugaComplexMedService_id,
					pmUser_id := :pmUser_id
				)
			";
			$result = $this->db->query($query, $data);
			if (is_object($result)) {
				$result = $result->result_array();
				if (count($result) > 0 && !empty($result[0]["AnalyzerTest_id"])) {
					$data["AnalyzerTest_id"] = $result[0]["AnalyzerTest_id"];
				}
			}
			if (!empty($test_id) && $data["AnalyzerTest_IsTest"] == 2 && !empty($data["AnalyzerTest_id"])) {
				// 3. Копируем референсные значения
				$this->copyRefValuesFromLis($data, $test_id);
			}
		}
	}

	/**
	 * Копирование референсных значений из теста лис
	 * @param $data
	 * @param $test_id
	 * @throws Exception
	 */
	function copyRefValuesFromLis($data, $test_id)
	{
		// 1. достаем все референсные значения из лис
		/**@var CI_DB_result $result */
		$query = "
			select
				nr.point2 as \"RefValues_LowerLimit\",
				nr.point3 as \"RefValues_UpperLimit\",
				pg.sex as \"Sex_id\",
				case
					when pg.ageunit = 1 then round(cast(pg.agestart as double precision) * 12, 0)
					when pg.ageunit = 2 then round(cast(pg.agestart as double precision) * 365, 0)
					else round(pg.agestart, 0)
				end as \"RefValues_LowerAge\",
				case
					when pg.ageunit = 1 then round(cast(pg.ageend as double precision) * 12, 0)
					when pg.ageunit = 2 then round(cast(pg.ageend as double precision) * 365, 0)
					else round(pg.ageend, 0)
				end as \"RefValues_UpperAge\",
				case
					when pg.ageunit = 1 then 2
					when pg.ageunit = 2 then 3
					else 1
				end as \"AgeUnit_id\",
				pg.pregnancyStart as \"RefValues_PregnancyFrom\",
				pg.pregnancyEnd as \"RefValues_PregnancyTo\"
			from
				lis.v__test_numericRanges tnr
				inner join lis.v__numericRanges nr on nr.id = tnr.numericRanges_id
				left join lis.v__patientGroup pg on pg.id = nr.patientGroup_id
			where tnr.test_id = :test_id
		";
		$result = $this->db->query($query, ["test_id" => $test_id]);
		$this->load->model("AnalyzerTestRefValues_model");
		if (is_object($result)) {
			$resp = $result->result_array();
			foreach ($resp as $respone) {
				// 2. добавляем референсное значение тесту
				$funcPararms = [
					"AnalyzerTest_id" => $data["AnalyzerTest_id"],
					"pmUser_id" => $data["pmUser_id"],
					"AnalyzerTestRefValues_id" => null,
					"RefValues_Name" => "{$respone["RefValues_LowerLimit"]}-{$respone["RefValues_UpperLimit"]}",
					"Unit_id" => null,
					"RefValues_LowerLimit" => $respone["RefValues_LowerLimit"],
					"RefValues_UpperLimit" => $respone["RefValues_UpperLimit"],
					"RefValues_BotCritValue" => null,
					"RefValues_TopCritValue" => null,
					"RefValues_Description" => null,
					"Sex_id" => $respone["Sex_id"],
					"RefValues_LowerAge" => $respone["RefValues_LowerAge"],
					"RefValues_UpperAge" => $respone["RefValues_UpperAge"],
					"AgeUnit_id" => $respone["AgeUnit_id"],
					"HormonalPhaseType_id" => null,
					"RefValues_PregnancyFrom" => $respone["RefValues_PregnancyFrom"],
					"RefValues_PregnancyTo" => $respone["RefValues_PregnancyTo"],
					"PregnancyUnitType_id" => 1,
					"RefValues_TimeOfDayFrom" => null,
					"RefValues_TimeOfDayTo" => null
				];
				$this->AnalyzerTestRefValues_model->save($funcPararms);
			}
		}
	}

	/**
	 * Добавление услуги на службу из теста ЛИС
	 * @param $data
	 * @param $test_id
	 * @return bool
	 * @throws Exception
	 */
	function addUslugaComplexTestMedService($data, $test_id)
	{
		/**@var CI_DB_result $result */
		$data["UslugaComplexMedService_id"] = null;
		// сначала услугу превращаем в связанную гостовскую услугу!
		$data["UslugaComplexToAdd_id"] = $data["UslugaComplexTest_id"];
		$query = "
			select UslugaComplex_2011id as \"UslugaComplex_2011id\"
			from v_UslugaComplex
			where UslugaComplex_id = :UslugaComplexToAdd_id
		";
		$UslugaComplex_2011id = $this->getFirstResultFromQuery($query, $data);
		if (!empty($UslugaComplex_2011id)) {
			$data["UslugaComplexToAdd_id"] = $UslugaComplex_2011id;
		}
		// 1. сначала ищем, может уже добавлена на службу такая услуга
		$query = "
			select ucm.UslugaComplexMedService_id as \"UslugaComplexMedService_id\"
			from v_UslugaComplexMedService ucm
			where ucm.UslugaComplex_id = :UslugaComplexToAdd_id
			  and ucm.MedService_id = :MedService_id
			  and ucm.UslugaComplexMedService_pid = :UslugaComplexMedService_pid
		";
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$result = $result->result_array();
			if (count($result) > 0 && !empty($result[0]["UslugaComplexMedService_id"])) {
				$data["UslugaComplexMedService_id"] = $result[0]["UslugaComplexMedService_id"];
			}
		}
		if (empty($data["UslugaComplexMedService_id"])) {
			$this->load->model("Lis_UslugaComplexMedService_model");
			$funcParams = [
				"UslugaComplexMedService_id" => null,
				"MedService_id" => $data["MedService_id"],
				"UslugaComplex_id" => $data["UslugaComplexToAdd_id"],
				"UslugaComplexMedService_pid" => $data["UslugaComplexMedService_pid"],
				"UslugaComplexMedService_begDT" => "@curDT",
				"UslugaComplexMedService_endDT" => null,
				"RefSample_id" => $data["RefSample_id"],
				"session" => $data["session"],
				"pmUser_id" => $data["pmUser_id"]
			];
			$resp = $this->Lis_UslugaComplexMedService_model->doSaveUslugaComplexMedService($funcParams);
			if (!empty($resp["UslugaComplexMedService_id"])) {
				$data["UslugaComplexMedService_id"] = $resp["UslugaComplexMedService_id"];
			}
		}
		// добавить тест на анализатор
		if (!empty($data["UslugaComplexMedService_id"])) {
			$data["AnalyzerTest_IsTest"] = 2;
			$this->addAnalyzerTestForUslugaComplexMedService($data, $test_id);
		}
		return false;
	}

	/**
	 * Добавление пробы для исследования/теста
	 * @param $data
	 * @return mixed|null
	 */
	function addRefSample($data)
	{
		/**@var CI_DB_result $result */
		$RefSample_id = null;
		$RefMaterial_id = null;
		// если не задан биоматериал, то пробу не создаём
		if (!empty($data["RefMaterial_Code"]) && !empty($data["RefMaterial_SysNick"])) {
			// 1. ищем биоматериал
			$query = "
				select RefMaterial_id as \"RefMaterial_id\"
				from v_RefMaterial
				where RefMaterial_SysNick = :RefMaterial_SysNick
				limit 1	
			";
			$result = $this->db->query($query, $data);
			if (is_object($result)) {
				$result = $result->result_array();
				if (count($result) > 0 && !empty($result[0]["RefMaterial_id"])) {
					$RefMaterial_id = $result[0]["RefMaterial_id"];
				}
			}
			// 2. если не нашли биоматериал то создаём новый
			if (empty($RefMaterial_id)) {
				$query = "
					select
						RefMaterial_id as \"RefMaterial_id\",
						error_code as \"Error_Code\", 
						error_message as \"Error_Msg\"
					from p_RefMaterial_ins(
						RefMaterial_Code := :RefMaterial_Code,
						RefMaterial_Name := :RefMaterial_Name,
						RefMaterial_SysNick := :RefMaterial_SysNick,
						pmUser_id := :pmUser_id
					)
				";
				$result = $this->db->query($query, $data);
				if (is_object($result)) {
					$result = $result->result_array();
					if (count($result) > 0 && !empty($result[0]["RefMaterial_id"])) {
						$RefMaterial_id = $result[0]["RefMaterial_id"];
					}
				}
			}
			// 3. создаём пробу
			if (!empty($RefMaterial_id)) {
				$data["RefMaterial_id"] = $RefMaterial_id;
				$query = "
					select
						RefSample_id as \"RefSample_id\",
						error_code as \"Error_Code\", 
						error_message as \"Error_Msg\"
					from p_RefSample_ins(
						RefMaterial_id := :RefMaterial_id,
						refsample_name := :RefMaterial_Name,
						pmUser_id := :pmUser_id
					)
				";
				$result = $this->db->query($query, $data);
				if (is_object($result)) {
					$result = $result->result_array();
					if (count($result) > 0 && !empty($result[0]["RefSample_id"])) {
						$RefSample_id = $result[0]["RefSample_id"];
					}
				}
			}
		}
		return $RefSample_id;
	}

	/**
	 * Добавление услуги на из теста ЛИС
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function addUslugaComplexMedServiceFromTest($data)
	{
		$funcResult = ["Error_Msg" => ""];
		// 1. получаем код услуги в ЛИС
		$query = "
			select
				t.id as test_id,
				t.code as test_code,
				t.name as test_name,
				target_bio.code as \"RefMaterialTarget_Code\",
				target_bio.name as \"RefMaterialTarget_Name\",
				target_bio.mnemonics as \"RefMaterialTarget_SysNick\",
				myvars.MedService_id as \"MedService_id\",
				myvars.UslugaComplex_id as \"UslugaComplexTarget_id\"
			from
				lis.v__test t
				left join lateral(
					select
						b.code,
						b.name,
						b.mnemonics
					from
						lis.v__biomaterial b
						inner join lis.v__target_biomaterials tb on tb.biomaterial_id = b.id
						inner join lis.v__test_targets tt on tt.target_id = tb.target_id
					where tt.test_id = t.id	
				) as target_bio on true
				left join lateral(
					select
						MedService_id,
						UslugaComplex_id
					from v_UslugaComplexMedService
					where UslugaComplexMedService_id = :UslugaComplexMedService_pid	
					limit 1
				) as myvars on true
			where t.id = :test_id
			limit 1	
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			return $funcResult;
		}
		$resp = $result->result_array();
		if (count($resp) == 0 || empty($resp[0]["test_code"])) {
			return $funcResult;
		}
		// добавляем услугу теста
		$data["MedService_id"] = $resp[0]["MedService_id"];
		$data["UslugaComplexTarget_id"] = $resp[0]["UslugaComplexTarget_id"];
		$data["UslugaComplexTest_id"] = $this->addUslugaComplexFromLis($data, $resp[0]["test_code"], $resp[0]["test_name"]);
		if (empty($data["UslugaComplexTarget_id"]) || empty($data["UslugaComplexTest_id"])) {
			return $funcResult;
		}
		// добавляем услугу теста в состав услуги исследования
		$this->addUslugaComplexTargetTestComposition($data);
		if (empty($data["UslugaComplexMedService_pid"])) {
			return $funcResult;
		}
		// добавляем услугу теста на службу в состав услуги исследования
		$data["RefSample_id"] = null;
		$data["RefMaterial_Code"] = $resp[0]["RefMaterialTarget_Code"];
		$data["RefMaterial_Name"] = $resp[0]["RefMaterialTarget_Name"];
		$data["RefMaterial_SysNick"] = $resp[0]["RefMaterialTarget_SysNick"];
		$data["RefSample_id"] = $this->addRefSample($data);
		$response = $this->addUslugaComplexTestMedService($data, $resp[0]["test_id"]);
		return $response;
	}

	/**
	 * Копирование атрибутов тестов
	 * @param $data
	 */
	function copyAnalyzerTestAttr($data)
	{
		/**@var CI_DB_result $result */
		// 1. QuantitativeTestUnit
		$query = "
			select
				Unit_id as \"Unit_id\",
				QuantitativeTestUnit_IsBase as \"QuantitativeTestUnit_IsBase\",
				QuantitativeTestUnit_CoeffEnum as \"QuantitativeTestUnit_CoeffEnum\"
			from lis.v_QuantitativeTestUnit
			where AnalyzerTest_id = :AnalyzerTest_id
		";
		$result = $this->db->query($query, ["AnalyzerTest_id" => $data["AnalyzerTest_idFrom"]]);
		if (is_object($result)) {
			$resp = $result->result_array();
			foreach ($resp as $respone) {
				$query = "
					select
						quantitativetestunit_id as \"QuantitativeTestUnit_id\",
						error_code as \"Error_Code\", 
						error_message as \"Error_Msg\"
					from lis.p_QuantitativeTestUnit_ins(
						AnalyzerTest_id := :AnalyzerTest_id,
						Unit_id := :Unit_id,
						QuantitativeTestUnit_IsBase := :QuantitativeTestUnit_IsBase,
						QuantitativeTestUnit_CoeffEnum := :QuantitativeTestUnit_CoeffEnum,
						pmUser_id := :pmUser_id
					)
				";
				$respone["pmUser_id"] = $data["pmUser_id"];
				$respone["AnalyzerTest_id"] = $data["AnalyzerTest_idTo"];
				$this->db->query($query, $respone);
			}
		}
		// 2. QualitativeTestAnswerAnalyzerTest
		$query = "
			select
				QualitativeTestAnswerAnalyzerTest_id as \"QualitativeTestAnswerAnalyzerTest_id\",
				QualitativeTestAnswer_id as \"QualitativeTestAnswer_id\",
				QualitativeTestAnswerAnalyzerTest_Answer as \"QualitativeTestAnswerAnalyzerTest_Answer\"
			from lis.v_QualitativeTestAnswerAnalyzerTest
			where AnalyzerTest_id = :AnalyzerTest_id
		";
		$result = $this->db->query($query, ["AnalyzerTest_id" => $data["AnalyzerTest_idFrom"]]);
		$QualitativeTestAnswerAnalyzerTests = [];
		if (is_object($result)) {
			$resp = $result->result_array();
			foreach ($resp as $respone) {
				$query = "
					select
						QualitativeTestAnswerAnalyzerTest_id as \"QualitativeTestAnswerAnalyzerTest_id\",
						error_code as \"Error_Code\", 
						error_message as \"Error_Msg\"
					from lis.p_QualitativeTestAnswerAnalyzerTest_ins(
						QualitativeTestAnswerAnalyzerTest_id := :QualitativeTestAnswerAnalyzerTest_id,
						AnalyzerTest_id := :AnalyzerTest_id,
						QualitativeTestAnswer_id := :QualitativeTestAnswer_id,
						QualitativeTestAnswerAnalyzerTest_Answer := :QualitativeTestAnswerAnalyzerTest_Answer,
						pmUser_id := :pmUser_id
                    )
				";
				$respone["pmUser_id"] = $data["pmUser_id"];
				$respone["AnalyzerTest_id"] = $data["AnalyzerTest_idTo"];
				/**@var CI_DB_result $result_qtaat */
				$result_qtaat = $this->db->query($query, $respone);
				if (is_object($result_qtaat)) {
					$resp_qtaat = $result_qtaat->result_array();
					if (!empty($resp_qtaat[0]["QualitativeTestAnswerAnalyzerTest_id"])) {
						$QualitativeTestAnswerAnalyzerTests[$respone["QualitativeTestAnswerAnalyzerTest_id"]] = $resp_qtaat[0]["QualitativeTestAnswerAnalyzerTest_id"];
					}
				}
			}
		}
		// 3. AnalyzerTestRefValues
		$query = "
			select
				atrv.AnalyzerTestRefValues_id as \"AnalyzerTestRefValues_id\",
				atrv.RefValues_id as \"RefValues_id\",
				rv.RefValues_Name as \"RefValues_Name\",
				rv.Unit_id as \"Unit_id\",
				rv.RefValues_LowerLimit as \"RefValues_LowerLimit\",
				rv.RefValues_UpperLimit as \"RefValues_UpperLimit\",
				rv.RefValues_BotCritValue as \"RefValues_BotCritValue\",
				rv.RefValues_TopCritValue as \"RefValues_TopCritValue\",
				rv.RefValues_Description as \"RefValues_Description\",
				rv.Sex_id as \"Sex_id\",
				rv.RefValues_LowerAge as \"RefValues_LowerAge\",
				rv.RefValues_UpperAge as \"RefValues_UpperAge\",
				rv.AgeUnit_id as \"AgeUnit_id\",
				rv.HormonalPhaseType_id as \"HormonalPhaseType_id\",
				rv.RefValues_PregnancyFrom as \"RefValues_PregnancyFrom\",
				rv.RefValues_PregnancyTo as \"RefValues_PregnancyTo\",
				rv.PregnancyUnitType_id as \"PregnancyUnitType_id\",
				rv.RefValues_TimeOfDayFrom as \"RefValues_TimeOfDayFrom\",
				rv.RefValues_TimeOfDayTo as \"RefValues_TimeOfDayTo\"
			from
				lis.v_AnalyzerTestRefValues atrv
				inner join v_RefValues rv on rv.RefValues_id = atrv.RefValues_id
			where AnalyzerTest_id = :AnalyzerTest_id
		";
		$result = $this->db->query($query, ["AnalyzerTest_id" => $data["AnalyzerTest_idFrom"]]);
		if (is_object($result)) {
			$resp = $result->result_array();
			foreach ($resp as $respone) {
				$query = "
					select
						RefValues_id as \"RefValues_id\",
						error_code as \"Error_Code\", 
						error_message as \"Error_Msg\"                   	
                    from p_RefValues_ins(
						RefValues_Name := :RefValues_Name,
						RefValues_Nick := :RefValues_Name,
						RefValuesType_id := NULL,
						Unit_id := :Unit_id,
						RefValues_LowerLimit := :RefValues_LowerLimit,
						RefValues_UpperLimit := :RefValues_UpperLimit,
						RefValues_BotCritValue := :RefValues_BotCritValue,
						RefValues_TopCritValue := :RefValues_TopCritValue,
						RefValues_Description := :RefValues_Description,
						Sex_id := :Sex_id,
						RefValues_LowerAge := :RefValues_LowerAge,
						RefValues_UpperAge := :RefValues_UpperAge,
						AgeUnit_id := :AgeUnit_id,
						HormonalPhaseType_id := :HormonalPhaseType_id,
						RefValues_PregnancyFrom := :RefValues_PregnancyFrom,
						RefValues_PregnancyTo := :RefValues_PregnancyTo,
						PregnancyUnitType_id := :PregnancyUnitType_id,
						RefValues_TimeOfDayFrom := :RefValues_TimeOfDayFrom,
						RefValues_TimeOfDayTo := :RefValues_TimeOfDayTo,
						pmUser_id := :pmUser_id
					)
				";
				$respone["pmUser_id"] = $data["pmUser_id"];
				$result = $this->db->query($query, $respone);
				if (is_object($result)) {
					$resp = $result->result_array();
					if (!empty($resp[0]["RefValues_id"])) {
						$query = "
                            select
								AnalyzerTestRefValues_id as \"AnalyzerTestRefValues_id\",
								error_code as \"Error_Code\", 
								error_message as \"Error_Msg\"
							from lis.p_AnalyzerTestRefValues_ins(
								AnalyzerTest_id := :AnalyzerTest_id,
								RefValues_id := :RefValues_id,
								pmUser_id := :pmUser_id
							)
						";
						$queryParams = [
							"AnalyzerTest_id" => $data["AnalyzerTest_idTo"],
							"RefValues_id" => $resp[0]["RefValues_id"],
							"pmUser_id" => $data["pmUser_id"]
						];
						$result_atrv = $this->db->query($query, $queryParams);
						$resp_atrv = $result_atrv->result("array");
						if (!empty($resp_atrv[0]["AnalyzerTestRefValues_id"]) && !empty($respone["AnalyzerTestRefValues_id"])) {
							// скопировать значения ответов качественных тестов
							$query = "
								select QualitativeTestAnswerAnalyzerTest_id as \"QualitativeTestAnswerAnalyzerTest_id\"
								from lis.v_QualitativeTestAnswerReferValue qtarv
								where AnalyzerTestRefValues_id = :AnalyzerTestRefValues_id
							";
							/**@var CI_DB_result $result_qtarv */
							$result_qtarv = $this->db->query($query, ["AnalyzerTestRefValues_id" => $respone["AnalyzerTestRefValues_id"]]);
							if (is_object($result_qtarv)) {
								$resp_qtarv = $result_qtarv->result_array();
								foreach ($resp_qtarv as $resp_qtarvone) {
									if (!empty($QualitativeTestAnswerAnalyzerTests[$resp_qtarvone["QualitativeTestAnswerAnalyzerTest_id"]])) {
										$query = "
											select
												error_code as \"Error_Code\", 
												error_message as \"Error_Msg\",
												QualitativeTestAnswerReferValue_id as \"QualitativeTestAnswerReferValue_id\"
											from lis.p_QualitativeTestAnswerReferValue_ins(
												AnalyzerTestRefValues_id := :AnalyzerTestRefValues_id,
												QualitativeTestAnswerAnalyzerTest_id := :QualitativeTestAnswerAnalyzerTest_id,
												pmUser_id := :pmUser_id
											)
										";
										$queryParams = [
											"AnalyzerTestRefValues_id" => $resp_atrv[0]["AnalyzerTestRefValues_id"],
											"QualitativeTestAnswerAnalyzerTest_id" => $QualitativeTestAnswerAnalyzerTests[$resp_qtarvone["QualitativeTestAnswerAnalyzerTest_id"]],
											"pmUser_id" => $data["pmUser_id"]
										];
										$this->db->query($query, $queryParams);
									}
								}
							}
							// скопировать Limit
							$query = "
								select
									Limit_id as \"Limit_id\",
									LimitType_id as \"LimitType_id\",
									Limit_Values as \"Limit_Values\",
									RefValues_id as \"RefValues_id\",
									Limit_ValuesFrom as \"Limit_ValuesFrom\",
									Limit_ValuesTo as \"Limit_ValuesTo\",
									Limit_IsActiv as \"Limit_IsActiv\",
									pmUser_insID as \"pmUser_insID\",
									pmUser_updID as \"pmUser_updID\",
									Limit_insDT as \"Limit_insDT\",
									Limit_updDT as \"Limit_updDT\",
									RefValuesSetRefValues_id as \"RefValuesSetRefValues_id\"
								from v_LimitValues
								where RefValues_id = :RefValues_id
							";
							/**@var CI_DB_result $result_limit */
							$result_limit = $this->db->query($query, ["RefValues_id" => $respone["RefValues_id"]]);
							if (is_object($result_limit)) {
								$resp_limit = $result_limit->result_array();
								foreach ($resp_limit as $resp_limitone) {
									$resp_limitone["RefValues_id"] = $resp[0]["RefValues_id"];
									$resp_limitone["pmUser_id"] = $data["pmUser_id"];
									$query = "
										select
											limit_id as \"Limit_id\",
											error_code as \"Error_Code\", 
											error_message as \"Error_Msg\"
										from p_LimitValues_ins(
											LimitType_id := :LimitType_id,
											Limit_Values := :Limit_Values,
											RefValues_id := :RefValues_id,
											Limit_ValuesFrom := :Limit_ValuesFrom,
											Limit_ValuesTo := :Limit_ValuesTo,
											Limit_IsActiv := :Limit_IsActiv,
											pmUser_id := :pmUser_id
										)  
									";
									$this->db->query($query, $resp_limitone);
								}
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Получение и сохранение указанной услуги для анализатора из Промед
	 * @param $data
	 * @throws Exception
	 */
	function saveUslugaFromModelToAnalyzer($data)
	{
		/**@var CI_DB_result $result */
		if (!empty($data["AnalyzerModel_id"]) && !empty($data["UslugaComplex_id"])) {
			// 1. получаем услугу в моделях анализаторов
			$query = "
				select
					AnalyzerTest_id as \"AnalyzerTest_id\",
					UslugaComplex_id as \"UslugaComplex_2011id\",
					AnalyzerTest_Code as \"AnalyzerTest_Code\",
					AnalyzerTest_Name as \"AnalyzerTest_Name\",
					AnalyzerTest_SysNick as \"AnalyzerTest_SysNick\",
					AnalyzerTestType_id as \"AnalyzerTestType_id\",
					Unit_id as \"Unit_id\",
					AnalyzerTest_IsTest as \"AnalyzerTest_IsTest\"
				from lis.v_AnalyzerTest
				where AnalyzerModel_id = :AnalyzerModel_id
				  and UslugaComplex_id = :UslugaComplex_id
				  and AnalyzerTest_IsTest = 2
				limit 1	
			";
			$result = $this->db->query($query, $data);
			if (is_object($result)) {
				$resp = $result->result_array();
				foreach ($resp as $respone) {
					//на сам деле имеет место только один элемент массива
					// копируем услугу на анализатор
					$respone["AnalyzerTest_pid"] = $data["AnalyzerTest_pid"];
					$respone["MedService_id"] = $data["MedService_id"];
					$respone["Analyzer_id"] = $data["Analyzer_id"];
					$respone["pmUser_id"] = $data["pmUser_id"];
					$respone["Server_id"] = $data["Server_id"];
					$respone["session"] = $data["session"];
					$savetest = $this->saveAnalyzerTestFromAnalyzerModel($respone);
					if (!empty($savetest[0]["AnalyzerTest_id"])) {
						// копируем аттрибуты
						$data["AnalyzerTest_idFrom"] = $respone["AnalyzerTest_id"];
						$data["AnalyzerTest_idTo"] = $savetest[0]["AnalyzerTest_id"];
						$this->copyAnalyzerTestAttr($data);
					}
				}
			}
		}
	}

	/**
	 * Получение и сохранение услуг для анализатора из Промед(без тестов, приписанных к автоучету реактивов)
	 * @param $data
	 * @throws Exception
	 */
	function getAndSaveUslugaCodesForAnalyzerModel($data)
	{
		/**@var CI_DB_result $result */
		// 1. получаем услуги в моделях анализаторов
		$query = "
			select
				at.AnalyzerTest_id as \"AnalyzerTest_id\",
				at.UslugaComplex_id as \"UslugaComplex_2011id\",
				at.AnalyzerTest_Code as \"AnalyzerTest_Code\",
				at.AnalyzerTest_Name as \"AnalyzerTest_Name\",
				at.AnalyzerTest_SysNick as \"AnalyzerTest_SysNick\",
				at.AnalyzerTestType_id as \"AnalyzerTestType_id\",
				at.Unit_id as \"Unit_id\",
				at.AnalyzerTest_IsTest as \"AnalyzerTest_IsTest\"
			from
				lis.v_AnalyzerTest at
				left join lis.ReagentNormRate rnr on rnr.AnalyzerTest_id = at.AnalyzerTest_id
			where at.AnalyzerModel_id = :AnalyzerModel_id
			  and at.AnalyzerTest_pid is null
			  and rnr.ReagentNormRate_id is null
		";
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$resp = $result->result_array();
			// идём по всем услугам модели анализатора
			foreach ($resp as $respone) {
				// копируем услугу на анализатор
				$respone["AnalyzerTest_pid"] = null;
				$respone["MedService_id"] = $data["MedService_id"];
				$respone["Analyzer_id"] = $data["Analyzer_id"];
				$respone["pmUser_id"] = $data["pmUser_id"];
				$respone["Server_id"] = $data["Server_id"];
				$respone["session"] = $data["session"];
				$savetest = $this->saveAnalyzerTestFromAnalyzerModel($respone);
				if (!empty($savetest[0]["AnalyzerTest_id"])) {
					// копируем аттрибуты
					$data["AnalyzerTest_idFrom"] = $respone["AnalyzerTest_id"];
					$data["AnalyzerTest_idTo"] = $savetest[0]["AnalyzerTest_id"];
					$this->copyAnalyzerTestAttr($data);
					// 2. получаем дочерние услуги
					$query_child = "
						select
							AnalyzerTest_id as \"AnalyzerTest_id\",
							UslugaComplex_id as \"UslugaComplex_2011id\",
							AnalyzerTest_Code as \"AnalyzerTest_Code\",
							AnalyzerTest_Name as \"AnalyzerTest_Name\",
							AnalyzerTest_SysNick as \"AnalyzerTest_SysNick\",
							AnalyzerTestType_id as \"AnalyzerTestType_id\",
							Unit_id as \"Unit_id\",
							AnalyzerTest_IsTest as \"AnalyzerTest_IsTest\"
						from lis.v_AnalyzerTest
						where AnalyzerModel_id = :AnalyzerModel_id
						  and AnalyzerTest_pid = :AnalyzerTest_pid
					";
					$data["AnalyzerTest_pid"] = $respone["AnalyzerTest_id"];
					$result_child = $this->db->query($query_child, $data);
					if (is_object($result_child)) {
						$resp_child = $result_child->result("array");
						// идём по всем услугам модели анализатора
						foreach ($resp_child as $respone_child) {
							// копируем услугу на анализатор
							$respone_child["AnalyzerTest_pid"] = $savetest[0]["AnalyzerTest_id"];
							$respone_child["MedService_id"] = $data["MedService_id"];
							$respone_child["Analyzer_id"] = $data["Analyzer_id"];
							$respone_child["pmUser_id"] = $data["pmUser_id"];
							$respone_child["Server_id"] = $data["Server_id"];
							$respone_child["session"] = $data["session"];
							$savetest_child = $this->saveAnalyzerTestFromAnalyzerModel($respone_child);
							if (!empty($savetest_child[0]["AnalyzerTest_id"])) {
								// копируем аттрибуты
								$data["AnalyzerTest_idFrom"] = $respone_child["AnalyzerTest_id"];
								$data["AnalyzerTest_idTo"] = $savetest_child[0]["AnalyzerTest_id"];
								$this->copyAnalyzerTestAttr($data);
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Сохранение теста на анализатор из теста модели анализатора
	 * @param $data
	 * @return bool|CI_DB_result|mixed
	 * @throws Exception
	 */
	function saveAnalyzerTestFromAnalyzerModel($data)
	{
		/**@var CI_DB_result $result */
		
		// Теперь услуга строго по госту(refs #PROMEDWEB-9543)
		$UslugaComplex_id = $data['UslugaComplex_2011id'];

		if (empty($UslugaComplex_id)) {
			return false;
		}
		$data["UslugaComplex_id"] = $UslugaComplex_id;
		// добавляем услуге атрибут "Лабораторно-диагностическая"
		$UslugaComplexAttr = $this->addUslugaComplexAttr([
			"UslugaComplex_id" => $UslugaComplex_id,
			"UslugaComplexAttributeType_SysNick" => "lab",
			"pmUser_id" => $data['pmUser_id']
		]);
		if ($UslugaComplexAttr !== true)
			return $UslugaComplexAttr;
		
		$data["AnalyzerTest_id"] = null;
		$data["UslugaComplexMedService_id"] = null;
		$query = "
			select MedService_id as \"MedService_id\"
			from lis.v_Analyzer
			where Analyzer_id = :Analyzer_id
			limit 1
		";
		$data["MedService_id"] = $this->getFirstResultFromQuery($query, $data);
		$query = "
			select UslugaComplexMedService_id as \"UslugaComplexMedService_id\"
			from lis.v_AnalyzerTest
			where AnalyzerTest_id = :AnalyzerTest_pid
			limit 1	
		";
		$data["UslugaComplexMedService_pid"] = $this->getFirstResultFromQuery($query, $data);
		if (empty($data["MedService_id"])) {
			$data["MedService_id"] = null;
		}
		if (empty($data["UslugaComplexMedService_pid"])) {
			$data["UslugaComplexMedService_pid"] = null;
		}
		// сначала услугу превращаем в связанную гостовскую услугу!
		$data["UslugaComplexToAdd_id"] = $data["UslugaComplex_id"];
		$query = "
			select UslugaComplex_2011id as\"UslugaComplex_2011id\"
			from v_UslugaComplex
			where UslugaComplex_id = :UslugaComplexToAdd_id
		";
		$UslugaComplex_2011id = $this->getFirstResultFromQuery($query, $data);
		if (!empty($UslugaComplex_2011id)) {
			$data["UslugaComplexToAdd_id"] = $UslugaComplex_2011id;
		}
		// 1. сначала ищем, может уже добавлена на службу такая услуга
		$query = "
			select ucm.UslugaComplexMedService_id as \"UslugaComplexMedService_id\"
			from v_UslugaComplexMedService ucm
			where ucm.UslugaComplex_id = :UslugaComplexToAdd_id
			  and ucm.MedService_id = :MedService_id
			  and COALESCE(ucm.UslugaComplexMedService_pid, 0) = COALESCE(CAST(NULLIF(:UslugaComplexMedService_pid, '') as bigint), 0)
		";
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$resp = $result->result_array();
			if (count($resp) > 0 && !empty($resp[0]["UslugaComplexMedService_id"])) {
				$data["UslugaComplexMedService_id"] = $resp[0]["UslugaComplexMedService_id"];
			}
		}
		if (empty($data["UslugaComplexMedService_id"])) {
			$this->load->model("Lis_UslugaComplexMedService_model");
			$funcParams = [
				"UslugaComplexMedService_id" => null,
				"scenario" => self::SCENARIO_DO_SAVE,
				"MedService_id" => $data["MedService_id"],
				"UslugaComplex_id" => $data["UslugaComplexToAdd_id"],
				"UslugaComplexMedService_pid" => $data["UslugaComplexMedService_pid"],
				"UslugaComplexMedService_begDT" => "@curDT",
				"UslugaComplexMedService_endDT" => null,
				"RefSample_id" => null,
				"LpuEquipment_id" => null,
				"session" => $data["session"],
				"pmUser_id" => $data["pmUser_id"]
			];
			$resp = $this->Lis_UslugaComplexMedService_model->doSaveUslugaComplexMedService($funcParams);
			if (!empty($resp["UslugaComplexMedService_id"])) {
				$data["UslugaComplexMedService_id"] = $resp["UslugaComplexMedService_id"];
			}
		}
		if (empty($data["UslugaComplexMedService_id"])) {
			throw new Exception("Ошибка сохранения услуги на службе");
		}
		$query = "
			select
               	AnalyzerTest_id as \"AnalyzerTest_id\",
				error_code as \"Error_Code\", 
				error_message as \"Error_Msg\"
               from lis.p_AnalyzerTest_ins(
				AnalyzerTest_id := :AnalyzerTest_id,
				AnalyzerTest_pid := :AnalyzerTest_pid,
				Analyzer_id := :Analyzer_id,
				UslugaComplex_id := :UslugaComplex_id,
				AnalyzerTest_IsTest := :AnalyzerTest_IsTest,
				AnalyzerTestType_id := :AnalyzerTestType_id,
				Unit_id := :Unit_id,
				AnalyzerTest_Name := :AnalyzerTest_Name,
				AnalyzerTest_SysNick := :AnalyzerTest_SysNick,
				AnalyzerTest_begDT := dbo.tzGetDate(),
				UslugaComplexMedService_id := :UslugaComplexMedService_id,
				pmUser_id := :pmUser_id
			)
		";
		$result = $this->db->query($query, $data);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * Получение и сохранение услуг для анализатора из ЛИС
	 * @param $data
	 * @throws Exception
	 */
	function getAndSaveUslugaCodesForEquipment($data)
	{
		// 1. получаем коды услуг в ЛИС
		$Test_JSONString = implode("','", $data["Test_JSON"]);
		$query = "
			select distinct
				ta.code as target_code,
				ta.name as target_name,
				t.id as test_id,
				t.code as test_code,
				t.name as test_name,
				tm.code as test_sysnick,
				target_bio.code as \"RefMaterialTarget_Code\",
				target_bio.name as \"RefMaterialTarget_Name\",
				target_bio.mnemonics as \"RefMaterialTarget_SysNick\"
			from
				lis.v__test t
				inner join lis.v__test_targets tt on tt.test_id = t.id
				inner join lis.v__target ta on ta.id = tt.target_id
				inner join lis.v__test_equipments e on t.id = e.test_id
				left join lateral(
					select
						b.code,
						b.name,
						b.mnemonics
					from
						lis.v__biomaterial b
						inner join lis.v__target_biomaterials tb on tb.biomaterial_id = b.id
					where tb.target_id = ta.id
					limit 1
				) as target_bio on true
				left join lis.v__testMappings tm on tm.test_id = t.id and tm.equipment_id = e.equipment_id
			where e.equipment_id = :equipment_id
			  and ta.id||'_'||t.id IN ('{$Test_JSONString}')
			order by
				ta.code,
				t.code
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$resp = $result->result_array();
			$lastTargetCode = null;
			$lastRefMaterialTestCode = null;
			$lastRefSample_id = null;
			// идём по всем услугам анализатора
			foreach ($resp as $respone) {
				// добавляем услугу исследования
				$data["UslugaComplexTarget_id"] = $this->addUslugaComplexFromLis($data, $respone["target_code"], $respone["target_name"]);
				if (!empty($data["UslugaComplexTarget_id"])) {
					// добавляем услугу исследования на службу
					// если услуга исследования = услуге теста, то создаём пробу и добавляем её услуге
					$data["RefSample_id"] = null;
					if ($lastTargetCode != $respone["target_code"] || $lastRefMaterialTestCode != $respone["RefMaterialTarget_Code"]) {
						$lastTargetCode = $respone["target_code"];
						$lastRefMaterialTestCode = $respone["RefMaterialTarget_Code"];
						$data["RefMaterial_Code"] = $respone["RefMaterialTarget_Code"];
						$data["RefMaterial_Name"] = $respone["RefMaterialTarget_Name"];
						$data["RefMaterial_SysNick"] = $respone["RefMaterialTarget_SysNick"];
						$lastRefSample_id = $this->addRefSample($data);
					}
					$data["AnalyzerTest_IsTest"] = 1;
					// добавляем услугу исследования
					$data["AnalyzerTest_Code"] = null;
					$data["AnalyzerTest_SysNick"] = null;
					$data["UslugaComplexMedService_pid"] = $this->addUslugaComplexTargetMedService($data);
					$data["RefSample_id"] = $lastRefSample_id;
					// добавляем услугу теста
					$data["UslugaComplexTest_id"] = $this->addUslugaComplexFromLis($data, $respone["test_code"], $respone["test_name"]);
					if (!empty($data["UslugaComplexTarget_id"]) && !empty($data["UslugaComplexTest_id"])) {
						// добавляем услугу теста в состав услуги исследования
						$this->addUslugaComplexTargetTestComposition($data);
						// добавляем услугу теста на службу в состав услуги исследования
						if (!empty($data["UslugaComplexMedService_pid"])) {
							$data["AnalyzerTest_Code"] = $respone["test_sysnick"];
							$data["AnalyzerTest_SysNick"] = null; // убрал по задаче #42668
							$this->addUslugaComplexTestMedService($data, $respone["test_id"]);
						}
					}
				}
			}
		}
	}

	/**
	 * Сохранение
	 * @param $data
	 * @return array|CI_DB_result|mixed
	 * @throws Exception
	 */
	function save($data)
	{
		/**@var CI_DB_result $result */
		if (isset($data["flag"])) {
			$query = "
				select Analyzer_id as \"Analyzer_id\"
				from lis.v_Analyzer
				where MedService_id = :MedService_id
				  and pmUser_insID = 1
				  and Analyzer_Code = '000'
			";
			$data["Analyzer_id"] = $this->getFirstResultFromQuery($query, $data);
			if (empty($data["Analyzer_id"])) {
				$data["Analyzer_id"] = null;
			}
		}
		$procedure = (isset($data["Analyzer_id"]) && $data["Analyzer_id"] > 0) ? "p_Analyzer_upd" : "p_Analyzer_ins";
		if (!isset($data["Analyzer_id"]) || $data["Analyzer_id"] == 0) {
			$data["Analyzer_id"] = null;
		}
		$data["Analyzer_2wayComm"] = (isset($data["Analyzer_2wayComm"]) && $data["Analyzer_2wayComm"]) ? 2 : 1;
		$data["Analyzer_IsUseAutoReg"] = (isset($data["Analyzer_IsUseAutoReg"]) && $data["Analyzer_IsUseAutoReg"]) ? 2 : 1;
		$data["Analyzer_IsNotActive"] = ($data["Analyzer_IsNotActive"]) ? 2 : 1;
		$data['Analyzer_IsManualTechnic'] = (isset($data['Analyzer_IsManualTechnic']) && $data['Analyzer_IsManualTechnic']) ? 2 : 1;
		if( !empty($data['AutoOkType']) ) {
			switch ($data['AutoOkType']) {
				case 1:
					$data['Analyzer_IsAutoOk'] = 2;
					$data['Analyzer_IsAutoGood'] = 1;
					break;
				case 2:
					$data['Analyzer_IsAutoOk'] = 2;
					$data['Analyzer_IsAutoGood'] = 2;
					break;
			}
		} else {
			$data['Analyzer_IsAutoOk'] = $data['Analyzer_IsAutoOk'] ? 2 : 1;
			$data['Analyzer_IsAutoGood'] = $data['Analyzer_IsAutoGood'] ? 2 : 1;
		}
		if (!empty($data["Analyzer_id"]) && !empty($data["Analyzer_endDT"])) {
			// при закрытии закрываем и все дочерние услугиы
			$query = "
				update lis.AnalyzerTest
				set AnalyzerTest_endDT = :Analyzer_endDT
				where Analyzer_id = :Analyzer_id
				  and AnalyzerTest_endDT is null
			";
			$this->db->query($query, $data);
		}

		$selectString = "
           	Analyzer_id as \"Analyzer_id\",
			error_code as \"Error_Code\", 
			error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
            from lis.{$procedure}(
				Analyzer_id := :Analyzer_id,
				Analyzer_Name := :Analyzer_Name,
				Analyzer_Code := :Analyzer_Code,
				AnalyzerModel_id := :AnalyzerModel_id,
				MedService_id := :MedService_id,
				Analyzer_begDT := :Analyzer_begDT,
				Analyzer_endDT := :Analyzer_endDT,
				Analyzer_LisClientId := :Analyzer_LisClientId,
				Analyzer_LisCompany := :Analyzer_LisCompany,
				Analyzer_LisLab := :Analyzer_LisLab,
				Analyzer_LisMachine := :Analyzer_LisMachine,
				Analyzer_LisLogin := :Analyzer_LisLogin,
				Analyzer_LisPassword := :Analyzer_LisPassword,
				Analyzer_LisNote := :Analyzer_LisNote,
				Analyzer_IsNotActive := :Analyzer_IsNotActive,
				Analyzer_IsAutoOk := :Analyzer_IsAutoOk,
				Analyzer_IsAutoGood := :Analyzer_IsAutoGood,
				Analyzer_2wayComm := :Analyzer_2wayComm,
				Analyzer_IsUseAutoReg := :Analyzer_IsUseAutoReg,
				Analyzer_IsManualTechnic := :Analyzer_IsManualTechnic,
				pmUser_id := :pmUser_id
			)
		";
		
		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			log_message("error", var_export(["q" => $query, "p" => $data, "e" => sqlsrv_errors()], true));
			throw new Exception("Ошибка при выполнении запроса к базе данных");
		}
		$result = $result->result_array();
		// Если при добавлении анализатора такой код уже сгенерирован
		if (empty($data["Analyzer_id"])) {
			// только при добавлении
			$ccodes = 2;
			while ($ccodes > 1) {
				// проверяем пока код не станет нормальным
				$ccodes = $this->checkAnalyzerCode($data);
				if ($ccodes > 1) {
					// если уже есть анализатор с таким кодом, то надо проапдейтить наш код анализатора на вновь сгенерированный
					$result_update = $this->incAnalyzerCode(["MedService_id" => $data["MedService_id"], "Analyzer_id" => $result[0]["Analyzer_id"]]);
					if (!$result_update) {
						// Запишем ошибку в лог
						log_message("error", "Error update Analyzer Code: Analyzer_id = " . $result[0]["Analyzer_id"] . " params: " . var_export($data, true));
						$ccodes = 1; // если количество анализаторов с таким кодом два, но апдейт вернул ошибку, то нужно остановить это насилие
					}
				}
			}
		}
		$data["Analyzer_id"] = $result[0]["Analyzer_id"];
		if (!empty($data["equipment_id"])) {
			$funcParams = [
				"Analyzer_id" => $data["Analyzer_id"],
				"equipment_id" => $data["equipment_id"],
				"pmUser_id" => $data["pmUser_id"]
			];
			$saveequip = $this->saveEquimpentLink($funcParams);
			if ($saveequip === false) {
				throw new Exception("Ошибка при сохранении связи с анализатором ЛИС");
			}
		}
		return $result;
	}

	/**
	 *  Возвращает количество анализаторов с указанным кодом
	 * @param $data
	 * @return mixed|null
	 */
	function checkAnalyzerCode($data)
	{
		/**@var CI_DB_result $result */
		$params = ["Analyzer_Code" => $data["Analyzer_Code"]];
		$sql = "
			select count(*) as record_count
			from lis.v_Analyzer a
			where a.Analyzer_Code = :Analyzer_Code
			  and a.Analyzer_Code <> '000'
		";
		$result = $this->db->query($sql, $params);
		if (is_object($result)) {
			$result = $result->result_array();
			if (count($result) > 0 && is_array($result[0])) {
				return $result[0]["record_count"];
			}
		}
		return null;
	}

	/**
	 *  Меняет код для указанного анализатора на максимальный
	 * @param $data
	 * @return bool
	 */
	function incAnalyzerCode($data)
	{
		/**@var CI_DB_result $result */
		$params = ["MedService_id" => $data["MedService_id"]];
		$resp = $this->getAnalyzerCode($params);
		$params = [];
		if (is_array($resp)) {
			$params["Analyzer_Code"] = $resp[0]["Analyzer_Code"];
		}
		$params["Analyzer_id"] = $data["Analyzer_id"];
		$sql = "
			update lis.Analyzer
			set Analyzer_Code = :Analyzer_Code
			where Analyzer_id = :Analyzer_id
		";
		$result = $this->db->query($sql, $params);
		return (is_object($result)) ? true : false;
	}

	/**
	 * Удаление
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function delete($data)
	{
		/**@var CI_DB_result $result */
		// надо проверить что у анализатора нет услуг
		$query = "
			select AnalyzerTest_id as \"AnalyzerTest_id\"
			from lis.v_AnalyzerTest
			where Analyzer_id = :Analyzer_id
			limit 1	
		";
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$result = $result->result_array();
			if (!empty($result[0]["AnalyzerTest_id"])) {
				throw new Exception("Нельзя удалить анализатор пока на него заведены услуги");
			}
		}
		// надо проверить что анализатор не "ручные методики"
		$query = "
			select Analyzer_Code as \"Analyzer_Code\"
			from lis.v_Analyzer
			where Analyzer_id = :Analyzer_id
			  and pmUser_insID = 1
			  and Analyzer_Code = '000'
			limit 1	
		";
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$result = $result->result_array();
			if (!empty($result[0]["Analyzer_Code"])) {
				throw new Exception("Нельзя удалить \"Ручные методики\"");
			}
		}
		$this->beginTransaction();
		try {
			$query = "
				delete from lis.Link
				where object_id = :Analyzer_id
				  and link_object = 'Analyzer';
			";
			$this->db->query($query, $data);
		} catch (Exception $exception) {
			$this->rollbackTransaction();
			throw new Exception("Ошибка при удалении связи с анализаторами лис");
		}
		$query = "
			select
            	error_code as \"Error_Code\", 
				error_message as \"Error_Msg\"
			from lis.p_Analyzer_del(
				Analyzer_id := :Analyzer_id,
				pmUser_delid := :pmUser_id
			)
		";
		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			$this->rollbackTransaction();
			return false;
		}
		return $result->result_array();
	}

	/**
	 * Генерирует код анализатора
	 * @param array $data
	 * @return array|bool
	 */
	function getAnalyzerCode($data = [])
	{
		$sql = "
			with mv as (
				select
					cast(max(
						case when to_number(Analyzer_Code) is not null
							then right(Analyzer_Code, 2)
							else '0'
						end
					) as bigint) as newNum
				from lis.Analyzer
				where MedService_id = :MedService_id
			)

			SELECT
				(select newNum from mv) as \"NewAnalyzerNum\", 
				RIGHT('0000' || coalesce(MedService_Code, 0), 4) || RIGHT('00' || coalesce((select newNum from mv), 0) + 1, 2) AS \"Analyzer_Code\",
				MedService_id as \"MedService_id\",
				MedService_Code as \"MedService_Code\"
			FROM dbo.MedService
			WHERE MedService_id = :MedService_id
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($sql, $data);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * Добавление аттрибута комплексной услуге
	 * @param $data
	 * @return bool|string
	 */
	function addUslugaComplexAttr($data)
	{
		if ($this->usePostgreLis) {
			$this->load->swapi("common");
			$result = $this->common->POST("UslugaComplex/Attribute", $data);
			if (!$this->isSuccessful($result)) {
				return $result;
			}
		} else {
			$query = "
				with mv as (
					select
					    UslugaComplexAttributeType_id as UslugaComplexAttributeType_id
					from
					    v_UslugaComplexAttributeType 
					where
					    UslugaComplexAttributeType_SysNick = :UslugaComplexAttributeType_SysNick 
                    limit 1
				)
                
                select
                    UslugaComplexAttribute_id as \"UslugaComplexAttribute_id\",
                    Error_Code as \"Error_Code\",
                    Error_Message as \"Error_Msg\"
				from p_UslugaComplexAttribute_ins (
					UslugaComplex_id := :UslugaComplex_id,
					UslugaComplexAttributeType_id := (select UslugaComplexAttributeType_id from mv),
					UslugaComplexAttribute_Float := null,
					UslugaComplexAttribute_Int := null,
					UslugaComplexAttribute_Text := null,
					UslugaComplexAttribute_Value := null,
					pmUser_id := :pmUser_id
					)
			";
			$result = $this->db->query($query, $data);
			if (!is_object($result))
				return $result;
		}
		
		return true;
	}

	/**
	 * Проверка, является ли служба, на которой заведен анализатор, внешней
	 */
	function checkIfFromExternalMedService($data) {
		$res = $this->getFirstResultFromQuery("
			select
				case when coalesce(ms.MedService_IsExternal, 1) = 2
					then 'true'
					else 'false'
				end as \"isExternal\"
			from lis.v_Analyzer a
				inner join dbo.v_MedService ms on ms.MedService_id = a.MedService_id
			where Analyzer_id = :Analyzer_id
		", $data);
		
		return ['isExternal' => $res == 'true' ? true : false];
	}
}
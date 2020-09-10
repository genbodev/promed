<?php
defined("BASEPATH") or die ("No direct script access allowed");
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Модель для объектов привязки норм расхода реагента к определенной модели анализатора и услуге
 * 
 * @package      common
 * @access       public
 * @author       Arslanov Azat
 *
 * @property CI_DB_driver $db
 */
class ReagentNormRate_model extends swPgModel
{
	private $ReagentNormRate_id;
	private $UslugaComplex_Code; //Код услуги
	private $AnalyzerModel_id; //Модель анализатора
	private $DrugNomen_id; //Реагент
	private $ReagentNormRate_RateValue;//величина расхода реактива
	private $unit_id;        //ед изм. расходуемого реактива
	private $RefMaterial_id; //биоматериал
	private $pmUser_id;      //Идентификатор пользователя системы Промед

	/**
	 * @return mixed
	 */
	public function getReagentNormRate_id()
	{
		return $this->ReagentNormRate_id;
	}

	/**
	 * @param $value
	 */
	public function setReagentNormRate_id($value)
	{
		$this->ReagentNormRate_id = $value;
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
	public function getDrugNomen_id()
	{
		return $this->DrugNomen_id;
	}

	/**
	 * @param $value
	 */
	public function setDrugNomen_id($value)
	{
		$this->DrugNomen_id = $value;
	}

	/**
	 * @return mixed
	 */
	public function getUslugaComplex_Code()
	{
		return $this->UslugaComplex_Code;
	}

	/**
	 * @param $value
	 */
	public function setUslugaComplex_Code($value)
	{
		$this->UslugaComplex_Code = $value;
	}

	/**
	 * @return mixed
	 */
	public function getReagentNormRate_RateValue()
	{
		return $this->ReagentNormRate_RateValue;
	}

	/**
	 * @param $value
	 */
	public function setReagentNormRate_RateValue($value)
	{
		$this->ReagentNormRate_RateValue = $value;
	}

	/**
	 * @return mixed
	 */
	public function getunit_id()
	{
		return $this->unit_id;
	}

	/**
	 * @param $value
	 */
	public function setunit_id($value)
	{
		$this->unit_id = $value;
	}

	/**
	 * @return mixed
	 */
	public function getRefMaterial_id()
	{
		return $this->RefMaterial_id;
	}

	/**
	 * @param $value
	 */
	public function setRefMaterial_id($value)
	{
		$this->RefMaterial_id = $value;
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
	 * ReagentNormRate_model constructor.
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
	 * Сохранение норматива расхода
	 * @return array|CI_DB_result
	 * @throws Exception
	 */
	function saveReagentNormRate()
	{
		$procedure = "p_ReagentNormRate_ins";
		if (!empty($this->ReagentNormRate_id)) {
			//если задан id записи
			$query = "
				select ts.TestStat_id as \"TestStat_id\"
				from lis.TestStat ts
				where ts.ReagentNormRate_id = :ReagentNormRate_id
				limit 1
			";
			$queryParams = ["ReagentNormRate_id" => $this->ReagentNormRate_id];
			$testStat = $this->getFirstResultFromQuery($query, $queryParams);
			if (!$testStat) {
				//ссылка на ReagentNormRate_id в TestStat не используется
				$procedure = "p_ReagentNormRate_upd";
			}
		}
		$selectString = "
		    reagentnormrate_id as \"ReagentNormRate_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from lis.{$procedure}(
			    reagentnormrate_id := :ReagentNormRate_id,
			    refmaterial_id := :RefMaterial_id,
			    unit_id := :unit_id,
			    reagentnormrate_ratevalue := :ReagentNormRate_RateValue,
			    analyzermodel_id := :AnalyzerModel_id,
			    drugnomen_id := :DrugNomen_id,
			    uslugacomplex_code := :UslugaComplex_Code,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"ReagentNormRate_id" => $this->ReagentNormRate_id,
			"AnalyzerModel_id" => $this->AnalyzerModel_id,
			"DrugNomen_id" => $this->DrugNomen_id,
			"UslugaComplex_Code" => $this->UslugaComplex_Code,
			"ReagentNormRate_RateValue" => $this->ReagentNormRate_RateValue,
			"unit_id" => $this->unit_id,
			"RefMaterial_id" => $this->RefMaterial_id,
			"pmUser_id" => $this->pmUser_id,
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных");
		}
		$result = $result->result("array");
		$this->ReagentNormRate_id = $result[0]["ReagentNormRate_id"];
		return $result;
	}

	/**
	 * Удаление норматива расхода
	 * @return array|bool
	 */
	function delete()
	{
		$query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Msg\"
			from lis.p_reagentnormrate_del(
			    reagentnormrate_id := :ReagentNormRate_id,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"ReagentNormRate_id" => $this->ReagentNormRate_id,
			"pmUser_id" => $this->pmUser_id
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * получение списка всех реактивов из номенклатурного справочника
	 * @param $data
	 * @return array|bool
	 */
	function loadReagentList($data)
	{
		$queryParams = [];
		$where = "";
		if (strlen($data["query"]) > 0) {
			$queryParams["query"] = "%" . $data["query"] . "%";
			$where .= " and (DN.DrugNomen_Code||' '||DN.DrugNomen_Nick) ilike replace(ltrim(rtrim(:query)),' ', '%')||'%'";
		}
		$query = "
			select
				DN.DrugNomen_id as \"Drug_id\",
				DN.DrugNomen_Code as \"Drug_Code\",
				DN.DrugNomen_Code||' '||DN.DrugNomen_Nick as \"Drug_Name\"
			from
				rls.v_DrugNomen DN
				inner join rls.v_Drug D on D.Drug_id = DN.Drug_id
			where DN.PrepClass_id = 10 {$where}
			order by
				DN.DrugNomen_Nick,
			    DN.DrugNomen_Code
			limit 100
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * Получение списка реактивов, допустимых для данного анализатора (по модели) и теста
	 * @param $data
	 * @return array|bool
	 */
	function loadReagentListForTest($data)
	{
		$params = [
			"Analyzer_id" => $data["Analyzer_id"],
			"UslugaComplex_Code" => $data["UslugaComplex_Code"]
		];
		$query = "
			select
				rnr.ReagentNormRate_id as \"ReagentNormRate_id\",
			    dn.DrugNomen_Name as \"DrugNomen_Name\"
			from
				lis.ReagentNormRate rnr
				join lis.Analyzer a on a.AnalyzerModel_id = rnr.AnalyzerModel_id and a.Analyzer_id = :Analyzer_id 
				join rls.v_DrugNomen dn on dn.DrugNomen_id = rnr.DrugNomen_id
			where rnr.UslugaComplex_Code = :UslugaComplex_Code
			  and coalesce(rnr.ReagentNormRate_Deleted, 1) = 1
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $params);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * получение данных реактива модели анализатора
	 * @param $filter
	 * @return array|bool
	 */
	function loadReagentNormRate($filter)
	{
		$params["ReagentNormRate_id"] = $filter["ReagentNormRate_id"];
		$query = "
			select 
				rnr.unit_id as \"unit_id\",
			    DN.DrugNomen_Name as \"DrugNomen_Name\",
			    rnr.reagentnormrate_id as \"ReagentNormRate_id\",
			    rnr.pmuser_insid as \"pmUser_insID\",
			    rnr.pmuser_updid as \"pmUser_updID\",
			    rnr.analyzertest_id as \"AnalyzerTest_id\",
			    rnr.reagentmodel_id as \"ReagentModel_id\",
			    rnr.refmaterial_id as \"RefMaterial_id\",
			    rnr.unit_id as \"Unit_id\",
			    rnr.reagentnormrate_ratevalue as \"ReagentNormRate_RateValue\",
			    rnr.analyzermodel_id as \"AnalyzerModel_id\",
			    rnr.drugnomen_id as \"DrugNomen_id\",
			    rnr.uslugacomplex_code as \"UslugaComplex_Code\",
			    rnr.reagentnormrate_deleted as \"ReagentNormRate_Deleted\",
			    rnr.pmuser_delid as \"pmUser_delID\"
			from 
				lis.ReagentNormRate rnr
				left join rls.v_DrugNomen DN on DN.DrugNomen_id = rnr.DrugNomen_id
			where rnr.ReagentNormRate_id = :ReagentNormRate_id
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $params);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * Получение списка реактивов модели-теста анализатора
	 * @param $filter
	 * @return array|bool
	 */
	function loadReagentNormRateGrid($filter)
	{
		$query = "
			select 
				-- select
				DN.DrugNomen_Name as \"DrugNomen_Name\",
			    rnr.reagentnormrate_id as \"ReagentNormRate_id\",
			    rnr.pmuser_insid as \"pmUser_insID\",
			    rnr.pmuser_updid as \"pmUser_updID\",
			    rnr.analyzertest_id as \"AnalyzerTest_id\",
			    rnr.reagentmodel_id as \"ReagentModel_id\",
			    rnr.refmaterial_id as \"RefMaterial_id\",
			    rnr.unit_id as \"Unit_id\",
			    rnr.reagentnormrate_ratevalue as \"ReagentNormRate_RateValue\",
			    rnr.analyzermodel_id as \"AnalyzerModel_id\",
			    rnr.drugnomen_id as \"DrugNomen_id\",
			    rnr.uslugacomplex_code as \"UslugaComplex_Code\",
			    rnr.reagentnormrate_deleted as \"ReagentNormRate_Deleted\",
			    rnr.pmuser_delid as \"pmUser_delID\"
				-- end select
			from
				-- from
				lis.ReagentNormRate rnr
				LEFT JOIN rls.v_DrugNomen DN on DN.DrugNomen_id = rnr.DrugNomen_id
				-- end from
			where 
				-- where
				rnr.AnalyzerModel_id = :AnalyzerModel_id
			and rnr.UslugaComplex_Code = :UslugaComplex_Code
			and (rnr.ReagentNormRate_Deleted is null OR rnr.ReagentNormRate_Deleted != 2) -- неудаленные
				-- end where
			order by
				-- order by
				DN.DrugNomen_Name
				-- end order by
		";
		$result = $this->db->query(getLimitSQLPH($query, $filter["start"], $filter["limit"]), $filter);
		$result_count = $this->db->query(getCountSQLPH($query), $filter);
		if (is_object($result_count)) {
			$cnt_arr = $result_count->result("array");
			$count = $cnt_arr[0]["cnt"];
			unset($cnt_arr);
		} else {
			$count = 0;
		}
		if (!is_object($result)) {
			return false;
		}
		$response = [];
		$response["data"] = $result->result("array");
		$response["totalCount"] = $count;
		return $response;
	}
}
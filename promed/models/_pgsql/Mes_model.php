<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Mes_model - модель для работы с МЕСами.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @access       public
 * @copyright    Copyright (c) 2009 - 2010 Swan Ltd.
 * @author       Pshenitcyn Ivan aka IvP (ipshon@rambler.ru)
 * @version      16.02.2010
 *
 * @property CI_DB_driver $db
 */
class Mes_model extends swPgModel
{
	public $dateTimeForm104 = "DD.MM.YYYY";
	
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function saveMes($data)
	{
		$procedure = (isset($data["Mes_id"]) && $data["Mes_id"] > 0) ? "p_Mes_upd" : "p_Mes_ins";
		if (!isset($data["Mes_id"]) || $data["Mes_id"] == 0) {
			$data["Mes_id"] = null;
			// генерируем код
			$query = "
				select
					mp.MesProf_Code as \"MesProf_Code\",
					mag.MesAgeGroup_Code as \"MesAgeGroup_Code\",
					olut.OmsLpuUnitType_Code as \"OmsLpuUnitType_Code\",
					ml.MesLevel_Code as \"MesLevel_Code\",
					dg.Diag_Code as \"Diag_Code\",
					mes1.cnt as cnt,
					mes1.mx as mx
				from
					MesProf mp
					left join MesAgeGroup mag on mag.MesAgeGroup_id = :MesAgeGroup_id
					left join OmsLpuUnitType olut on olut.OmsLpuUnitType_id = :OmsLpuUnitType_id
					left join MesLevel ml on ml.MesLevel_id = :MesLevel_id and ml.MesLevel_id = :MesLevel_id
					left join Diag dg on dg.Diag_id = :Diag_id
					left join lateral (
						select
							count(Mes_id) as cnt,
					        max(right(Mes_Code, 2)::int8) + 1 as mx
						from Mes
						where MesProf_id = :MesProf_id
						  and MesAgeGroup_id = :MesAgeGroup_id
						  and OmsLpuUnitType_id = :OmsLpuUnitType_id
						  and MesLevel_id = :MesLevel_id
						  and Diag_id = :Diag_id
					) as mes1 on true
				where mp.MesProf_id = :MesProf_id
			";
			$queryParams = [
				"MesProf_id" => $data["MesProf_id"],
				"MesAgeGroup_id" => $data["MesAgeGroup_id"],
				"OmsLpuUnitType_id" => $data["OmsLpuUnitType_id"],
				"MesLevel_id" => $data["MesLevel_id"],
				"Diag_id" => $data["Diag_id"]
			];
			/**@var CI_DB_result $result */
			$result = $this->db->query($query, $queryParams);
			if (!is_object($result)) {
				throw new Exception("Ошибка при выполнении запроса к базе данных");
			}
			$res = $result->result('array');
			if (count($res) == 0) {
				throw new Exception("Не удалось сгенерировать код " . getMESAlias());
			}
			$data["Mes_Code"] = "";
			$data["Mes_Code"] .= $res[0]["MesProf_Code"];
			$data["Mes_Code"] .= ".";
			$data["Mes_Code"] .= $res[0]["MesAgeGroup_Code"];
			$data["Mes_Code"] .= ".";
			$data["Mes_Code"] .= $res[0]["OmsLpuUnitType_Code"];
			$data["Mes_Code"] .= ".";
			$data["Mes_Code"] .= $res[0]["MesLevel_Code"];
			$data["Mes_Code"] .= ".";
			$data["Mes_Code"] .= str_replace(".", "", $res[0]["Diag_Code"]);
			if (strlen($data["Mes_Code"]) <= 3) {
				$data["Mes_Code"] .= "X";
			}
			if ($res[0]["cnt"] == 0) {
				$data["Mes_Code"] .= "00";
			} else {
				if ($res[0]["mx"] <= 9)
					$data["Mes_Code"] .= "0";
				$data["Mes_Code"] .= $res[0]["mx"];
			}
		}
		$selectString = "
		    mes_id as \"Mes_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    mes_id := :Mes_id,
			    mes_code := :Mes_Code,
			    mes_koikodni := :Mes_KoikoDni,
			    mes_begdt := :Mes_begDT,
			    mes_enddt := :Mes_endDT,
			    mesagegroup_id := :MesAgeGroup_id,
			    meslevel_id := :MesLevel_id,
			    mesprof_id := :MesProf_id,
			    diag_id := :Diag_id,
			    omslpuunittype_id := :OmsLpuUnitType_id,
			    mes_diagclinical := :Mes_DiagClinical,
			    mes_diagvolume := :Mes_DiagVolume,
			    mes_consulting := :Mes_Consulting,
			    mes_curevolume := :Mes_CureVolume,
			    mes_qualitymeasure := :Mes_QualityMeasure,
			    mes_resultclass := :Mes_ResultClass,
			    mes_complrisk := :Mes_ComplRisk,
			    mes_order := 0,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"Mes_id" => $data["Mes_id"],
			"Mes_Code" => $data["Mes_Code"],
			"Mes_KoikoDni" => $data["Mes_KoikoDni"],
			"Mes_begDT" => $data["Mes_begDT"],
			"Mes_endDT" => $data["Mes_endDT"],
			"MesAgeGroup_id" => $data["MesAgeGroup_id"],
			"MesLevel_id" => $data["MesLevel_id"],
			"MesProf_id" => $data["MesProf_id"],
			"Diag_id" => $data["Diag_id"],
			"OmsLpuUnitType_id" => $data["OmsLpuUnitType_id"],
			"Mes_DiagClinical" => $data["Mes_DiagClinical"],
			"Mes_DiagVolume" => $data["Mes_DiagVolume"],
			"Mes_Consulting" => $data["Mes_Consulting"],
			"Mes_CureVolume" => $data["Mes_CureVolume"],
			"Mes_QualityMeasure" => $data["Mes_QualityMeasure"],
			"Mes_ResultClass" => $data["Mes_ResultClass"],
			"Mes_ComplRisk" => $data["Mes_ComplRisk"],
			"pmUser_id" => $data["pmUser_id"]
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных");
		}
		return $result->result("array");
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function loadMes($data)
	{
		$query = "
			select
				Mes_id as \"Mes_id\",
				Mes_Code as \"Mes_Code\",
				MesProf_id as \"MesProf_id\",
				MesAgeGroup_id as \"MesAgeGroup_id\",
				MesLevel_id as \"MesLevel_id\",
				Diag_id as \"Diag_id\",
				OmsLpuUnitType_id as \"OmsLpuUnitType_id\",
				Mes_KoikoDni as \"Mes_KoikoDni\",
				to_char(Mes_begDT, '{$this->dateTimeForm104}') as \"Mes_begDT\",
				to_char(Mes_endDT, '{$this->dateTimeForm104}') as \"Mes_endDT\",
				Mes_DiagClinical as \"Mes_DiagClinical\",
				Mes_DiagVolume as \"Mes_DiagVolume\",
				Mes_Consulting as \"Mes_Consulting\",
				Mes_CureVolume as \"Mes_CureVolume\",
				Mes_QualityMeasure as \"Mes_QualityMeasure\",
				Mes_ResultClass as \"Mes_ResultClass\",
				Mes_ComplRisk as \"Mes_ComplRisk\"
			from Mes
			where Mes_id = :Mes_id
		";
		$queryParams = ["Mes_id" => $data["Mes_id"]];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Метод для получения списка записей для поиска по МЭСам
	 * @param $data
	 * @return array|bool
	 */
	function loadMesListForDbf($data)
	{
		$filterArray = [];
		$queryParams = [];
		if (isset($data["MesStatus_id"])) {
			switch ($data["MesStatus_id"]) {
				case 1:
					$filterArray[] = "ms.Mes_endDT is null";
					break;
				case 2:
					$filterArray[] = "(ms.Mes_endDT is null or ms.Mes_begDT <= tzgetdate())";
					break;
				case 3:
					$filterArray[] = "(ms.Mes_endDT is not null or ms.Mes_endDT <= tzgetdate())";
					break;
				case 4:
					$filterArray[] = "(ms.Mes_endDT is null or ms.Mes_begDT > tzgetdate())";
					break;
			}
		}
		if (isset($data["MesProf_id"])) {
			$filterArray[] = "ms.MesProf_id = :MesProf_id";
			$queryParams["MesProf_id"] = $data["MesProf_id"];
		}
		if (isset($data["MesAgeGroup_id"])) {
			$filterArray[] = "ms.MesAgeGroup_id = :MesAgeGroup_id";
			$queryParams["MesAgeGroup_id"] = $data["MesAgeGroup_id"];
		}
		if (isset($data["MesLevel_id"])) {
			$filterArray[] = "ms.MesLevel_id = :MesLevel_id";
			$queryParams["MesLevel_id"] = $data["MesLevel_id"];
		}
		if (isset($data["OmsLpuUnitType_id"])) {
			$filterArray[] = "ms.OmsLpuUnitType_id = :OmsLpuUnitType_id";
			$queryParams["OmsLpuUnitType_id"] = $data["OmsLpuUnitType_id"];
		}
		if (isset($data["Diag_id"])) {
			$filterArray[] = "ms.Diag_id = :Diag_id";
			$queryParams["Diag_id"] = $data["Diag_id"];
		}
		if (isset($data["Mes_KoikoDni_From"]) || isset($data["Mes_KoikoDni_To"])) {
			if (isset($data["Mes_KoikoDni_From"])) {
				$filterArray[] = "ms.Mes_KoikoDni >= :Mes_KoikoDni_From";
				$queryParams["Mes_KoikoDni_From"] = $data["Mes_KoikoDni_From"];
			}
			if (isset($data["Mes_KoikoDni_To"])) {
				$filterArray[] = "ms.Mes_KoikoDni <= :Mes_KoikoDni_To";
				$queryParams["Mes_KoikoDni_To"] = $data["Mes_KoikoDni_To"];
			}
		}
		if (isset($data["Mes_begDT_Range"][0])) {
			$filterArray[] = "ms.Mes_begDT >= :Mes_begDT_Range_0";
			$queryParams["Mes_begDT_Range_0"] = $data["Mes_begDT_Range"][0];
		}
		if (isset($data["Mes_begDT_Range"][1])) {
			$filterArray[] = "ms.Mes_begDT <= :Mes_begDT_Range_1";
			$queryParams["Mes_begDT_Range_1"] = $data["Mes_begDT_Range"][1];
		}
		if (isset($data["Mes_endDT_Range"][0])) {
			$filterArray[] = "ms.Mes_endDT >= :Mes_endDT_Range_0";
			$queryParams["Mes_endDT_Range_0"] = $data["Mes_endDT_Range"][0];
		}
		if (isset($data["Mes_endDT_Range"][1])) {
			$filterArray[] = "ms.Mes_endDT <= :Mes_endDT_Range_1";
			$queryParams["Mes_endDT_Range_1"] = $data["Mes_endDT_Range"][1];
		}
		$whereString = (count($filterArray) != 0) ? "where " . implode(" and ", $filterArray) : "";
		$sql = "
			select
				case when mp.MesProf_id is null then '' else mp.MesProf_Code::varchar end as codespec,
				case when mag.MesAgeGroup_id is null then '' else mag.MesAgeGroup_Code::varchar end as vzdet,
				case when mp.MesProf_id is null then '' else mp.MesProf_Name::varchar end as namespec,
				case when dg.Diag_id is null then '' else dg.Diag_Code::varchar end as codemkb,
				case when ml.MesLevel_id is null then '' else ml.MesLevel_Code::varchar end as level,
				case when olut.OmsLpuUnitType_id is null then '' else olut.OmsLpuUnitType_Code end as stactype,
				ms.Mes_KoikoDni as stac,
				ms.Mes_id as mes_id,
				ms.Mes_Code as codemes,
				case when dg.Diag_id is null then '' else dg.Diag_Name::varchar(200) end as namemkb,
				rtrim(coalesce(to_char(ms.Mes_begDT, '{$this->dateTimeForm104}'), '')) as datebeg,
				rtrim(coalesce(to_char(ms.Mes_endDT, '{$this->dateTimeForm104}'), '')) as dateend,
				Mes_DiagClinical as diagclin,
				Mes_DiagVolume as diag_vol,
				Mes_Consulting as consult,
				Mes_CureVolume as cure_vol,
				Mes_QualityMeasure as qual_cri,
				Mes_ResultClass as s_result,
				Mes_ComplRisk as agg_risk
			from
				Mes ms
				left join MesProf mp on mp.MesProf_id = ms.MesProf_id
				left join MesAgeGroup mag on mag.MesAgeGroup_id = ms.MesAgeGroup_id
				left join OmsLpuUnitType olut on olut.OmsLpuUnitType_id = ms.OmsLpuUnitType_id
				left join MesLevel ml on ml.MesLevel_id = ms.MesLevel_id
				left join Diag dg on dg.Diag_id = ms.Diag_id
			{$whereString}
			order by ms.Mes_updDT desc
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($sql, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result('array');
	}

	/**
	 * Метод для получения списка записей для поиска по МЭСам
	 * @param $data
	 * @return array|bool
	 */
	function loadMesOldComboSearchList($data)
	{
		if (!isset($data['Diag_Name'])) {
			return false;
		}
		$query = "
			select
				ms.Mes_id as \"Mes_id\",
				ms.Mes_Code as \"Mes_Code\",
				dg.Diag_Name as \"Diag_Name\"
			from
				MesOld ms
				left join Diag dg on dg.Diag_id = ms.Diag_id
			where Diag_Name Like '%{$data['Diag_Name']}%'
			order by ms.MesOld_updDT desc
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result("array");
		$response = [];
		if (is_array($result)) {
			$response["data"] = $result;
		}
		return $response;
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function loadMesOldVizit($data)
	{
		$filterArray = [];
		$queryParams = [];
		if (isset($data["UslugaComplex_id"])) {
			$queryParams["UslugaComplex_id"] = $data["UslugaComplex_id"];
			$filterArray[] = "mu.Uslugacomplex_id = :UslugaComplex_id";
		}
		if (!empty($data["Mes_Codes"])) {
			$mes_codes = json_decode($data["Mes_Codes"], true);
			$mes_codes_str = "'" . implode("','", $mes_codes) . "'";
			$filterArray[] = "mo.Mes_Code in ({$mes_codes_str})";
		}
		if ($data["MesType_id"] === "0" || $data["MesType_id"] === 0) {
			$filterArray[] = "mo.MesType_id is null";
		} elseif (!empty($data["MesType_id"])) {
			$queryParams["MesType_id"] = $data["MesType_id"];
			$filterArray[] = "mo.MesType_id = :MesType_id";
		}
		if (isset($data["EvnDate"])) {
			$filterArray[] = "(mo.Mes_begDT <= :EvnDate and (mo.Mes_endDT is null or mo.Mes_endDT >= :EvnDate))";
			$queryParams["EvnDate"] = $data["EvnDate"];
		}
		if (!empty($data["UslugaComplexPartition_CodeList"])) {
			$UslugaComplexPartition_CodeList = json_decode($data["UslugaComplexPartition_CodeList"], true);
			$UslugaComplexPartition_CodeList_str = "'" . implode("','", $UslugaComplexPartition_CodeList) . "'";
			$filterArray[] = "
				exists(
					select UC.UslugaComplex_id
					from
						v_UslugaComplex UC
						inner join r66.v_UslugaComplexPartitionLink ucpl on ucpl.UslugaComplex_id = uc.UslugaComplex_id
						inner join r66.v_UslugaComplexPartition ucp on ucp.UslugaComplexPartition_id = ucpl.UslugaComplexPartition_id
							and ucp.UslugaComplexPartition_Code in ({$UslugaComplexPartition_CodeList_str})
					where UC.UslugaComplex_id = mu.UslugaComplex_id
					limit 1
				)
			";
		}
		$whereString = (count($filterArray) != 0) ? "where " . implode(" and ", $filterArray) : "";
		$sql = "
			select distinct 
				mo.Mes_id as \"MesOldVizit_id\",
				mo.Mes_Code as \"MesOldVizit_Code\",
				coalesce(mo.Mes_Name,'') as \"MesOldVizit_Name\"
			from
				v_MesOld mo
				inner join v_MesUsluga mu on mu.Mes_id = mo.Mes_id and mu.MesUslugaLinkType_id = 5
			{$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($sql, $queryParams);
		$response = [];
		if (!is_object($result)) {
			return false;
		}
		$res = $result->result("array");
		if (is_array($res)) {
			$response["data"] = $res;
		}
		return $response;
	}

	/**
	 * Метод для получения списка кодов старых МЭС
	 * @param $data
	 * @return array|bool
	 */
	function searchFullMesOldCodeList($data)
	{
		$queryParams = [];
		if (!isset($data["query"]) && !isset($data["Mes_id"])) {
			return false;
		}
		$filter = (isset($data["Mes_id"]) && $data["Mes_id"] > 0) ? "Mes_id = {$data['Mes_id']}" : "Mes_Code Like '{$data['query']}%'";
		$query = "
			select
				ms.Mes_id as \"Mes_id\",
				ms.Mes_Code as \"Mes_Code\"
			from MesOld ms
			where $filter
			order by Mes_Code desc
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		$response = [];
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result("array");
		if (is_array($result)) {
			$response["data"] = $result;
		}
		return $response;
	}

	/**
	 * Метод для получения списка записей для поиска по МЭСам
	 */
	function loadMesSearchList($data)
	{
		$filterArray = [];
		$queryParams = [];
		if (isset($data["MesStatus_id"])) {
			switch ($data["MesStatus_id"]) {
				case 1:
					$filterArray[] = "ms.Mes_endDT is null";
					break;
				case 2:
					$filterArray[] = "(ms.Mes_begDT <= tzgetdate() or ms.Mes_endDT is null)";
					break;
				case 3:
					$filterArray[] = "(ms.Mes_endDT <= tzgetdate() or ms.Mes_endDT is not null)";
					break;
				case 4:
					$filterArray[] = "(ms.Mes_begDT > tzgetdate() or ms.Mes_endDT is null)";
					break;
			}
		}
		if (isset($data["MesProf_id"])) {
			$filterArray[] = "ms.MesProf_id = :MesProf_id";
			$queryParams["MesProf_id"] = $data["MesProf_id"];
		}
		if (isset($data["MesAgeGroup_id"])) {
			$filterArray[] = "ms.MesAgeGroup_id = :MesAgeGroup_id";
			$queryParams["MesAgeGroup_id"] = $data["MesAgeGroup_id"];
		}
		if (isset($data["MesLevel_id"])) {
			$filterArray[] = "ms.MesLevel_id = :MesLevel_id";
			$queryParams["MesLevel_id"] = $data["MesLevel_id"];
		}
		if (isset($data["OmsLpuUnitType_id"])) {
			$filterArray[] = "ms.OmsLpuUnitType_id = :OmsLpuUnitType_id";
			$queryParams["OmsLpuUnitType_id"] = $data["OmsLpuUnitType_id"];
		}
		if (isset($data["Diag_id"])) {
			$filterArray[] = "ms.Diag_id = :Diag_id";
			$queryParams["Diag_id"] = $data["Diag_id"];
		}
		if (isset($data["Mes_KoikoDni_From"]) || isset($data["Mes_KoikoDni_To"])) {
			if (isset($data["Mes_KoikoDni_From"])) {
				$filterArray[] = "ms.Mes_KoikoDni >= :Mes_KoikoDni_From";
				$queryParams["Mes_KoikoDni_From"] = $data["Mes_KoikoDni_From"];
			}
			if (isset($data["Mes_KoikoDni_To"])) {
				$filterArray[] = "ms.Mes_KoikoDni <= :Mes_KoikoDni_To";
				$queryParams["Mes_KoikoDni_To"] = $data["Mes_KoikoDni_To"];
			}
		}
		if (isset($data["Mes_begDT_Range"][0])) {
			$filterArray[] = "ms.Mes_begDT >= :Mes_begDT_Range_0";
			$queryParams["Mes_begDT_Range_0"] = $data["Mes_begDT_Range"][0];
		}
		if (isset($data["Mes_begDT_Range"][1])) {
			$filterArray[] = "ms.Mes_begDT <= :Mes_begDT_Range_1";
			$queryParams["Mes_begDT_Range_1"] = $data["Mes_begDT_Range"][1];
		}
		if (isset($data["Mes_endDT_Range"][0])) {
			$filterArray[] = "ms.Mes_endDT >= :Mes_endDT_Range_0";
			$queryParams["Mes_endDT_Range_0"] = $data["Mes_endDT_Range"][0];
		}
		if (isset($data["Mes_endDT_Range"][1])) {
			$filterArray[] = "ms.Mes_endDT <= :Mes_endDT_Range_1";
			$queryParams["Mes_endDT_Range_1"] = $data["Mes_endDT_Range"][1];
		}
		$whereString = (count($filterArray) != 0) ? implode(" and ", $filterArray) : "";
		if($whereString != "") {
			$whereString = "
				where
				-- where
				{$whereString}
				-- end where
			";
		}
		$sql = "
			select
			    -- select
				ms.Mes_id as \"Mes_id\",
				ms.Mes_Code as \"Mes_Code\",
				case when mp.MesProf_id is null then '' else mp.MesProf_Code::varchar||'. '||mp.MesProf_Name end as \"MesProf_CodeName\",
				case when mag.MesAgeGroup_id is null then '' else mag.MesAgeGroup_Code::varchar||'. '||mag.MesAgeGroup_Name end as \"MesAgeGroup_CodeName\",
				ms.Mes_KoikoDni as \"Mes_KoikoDni\",
				case when olut.OmsLpuUnitType_id is null then '' else olut.OmsLpuUnitType_Code::varchar||'. '||olut.OmsLpuUnitType_Name end as \"OmsLpuUnitType_CodeName\",
				case when ml.MesLevel_id is null then '' else ml.MesLevel_Code::varchar end as \"MesLevel_CodeName\",
				case when dg.Diag_id is null then '' else dg.Diag_Code::varchar||'. '||dg.Diag_Name end as \"Diag_CodeName\",
				to_char(ms.Mes_begDT, '{$this->dateTimeForm104}') as \"Mes_begDT\",
				to_char(ms.Mes_endDT, '{$this->dateTimeForm104}') as \"Mes_endDT\",
				Mes_DiagClinical as \"Mes_DiagClinical\",
				Mes_DiagVolume as \"Mes_DiagVolume\",
				Mes_Consulting as \"Mes_Consulting\",
				Mes_CureVolume as \"Mes_CureVolume\",
				Mes_QualityMeasure as \"Mes_QualityMeasure\",
				Mes_ResultClass as \"Mes_ResultClass\",
				Mes_ComplRisk as \"Mes_ComplRisk\",
				case when ms.Mes_endDT is not null and ms.Mes_endDT <= tzgetdate()
				    then 3
				    else
				        case when ms.Mes_begDT > tzgetdate() and ms.Mes_endDT is null
				            then 4
				            else 1
				        end
				end as \"MesStatus\"
				--end select
			from
				-- from
				Mes ms
				left join MesProf mp on mp.MesProf_id = ms.MesProf_id
				left join MesAgeGroup mag on mag.MesAgeGroup_id = ms.MesAgeGroup_id
				left join OmsLpuUnitType olut on olut.OmsLpuUnitType_id = ms.OmsLpuUnitType_id
				left join MesLevel ml on ml.MesLevel_id = ms.MesLevel_id
				left join Diag dg on dg.Diag_id = ms.Diag_id
				-- end from
			{$whereString}
			order by
				-- order by
				ms.Mes_updDT desc
				-- end order by
		";
		return $this->getPagingResponse($sql, $queryParams, $data["start"], $data["limit"], true);
	}
}
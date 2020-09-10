<?php defined('BASEPATH') or die ('No direct script access allowed');
/**одель для работы с спецификой "Беременность и роды" в КВС
 *
 * @author: gabdushev
 * @copyright
 *
 * @property bool $isAllowControlTransaction
 * @property CI_DB_driver $db
 *
 * @property Person_model $Person_model
 * @property MedSvid_model $MedSvid_model
 * @property EvnPS_model $EvnPS_model
 * @property PersonNewBorn_model $PersonNewBorn_model
 */
class BirthSpecStac_model extends swPgModel
{
	public $dateTimeForm104 = "DD.MM.YYYY";
	public $dateTimeFormUnixFull = "YYYY-MM-DD HH24:MI:SS";

	function __construct()
	{
		parent::__construct();
		$this->isAllowControlTransaction = true;
	}

	/**
	 * Сохранение данных специфики "Беременность и роды"
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public function save($data)
	{
		if ((!isset($data["BirthSpecStac_id"])) || ($data["BirthSpecStac_id"] <= 0)) {
			$procedure = "p_BirthSpecStac_ins";
			if (isset($data["EvnSection_id"])) {
				$query = "
					select BirthSpecStac_id as \"BirthSpecStac_id\"
					from
						v_BirthSpecStac BSS
					    inner join v_EvnSection EvnSection on EvnSection.EvnSection_id=BSS.EvnSection_id
					where EvnSection.EvnSection_id =:EvnSection_id
					limit 1
				";
				$queryParams = ["EvnSection_id" => $data["EvnSection_id"]];
				/**@var CI_DB_result $result */
				$result = $this->db->query($query, $queryParams);
				$response = $result->result("array");
				if (count($response) > 0) {
					$data["BirthSpecStac_id"] = $response[0]["BirthSpecStac_id"];
					$procedure = "p_BirthSpecStac_upd";
				}
			}
		} else {
			$procedure = "p_BirthSpecStac_upd";
		}
		$selectString = "
			birthspecstac_id as \"BirthSpecStac_id\",
			error_code as \"Error_Code\",
			error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    birthspecstac_id := :BirthSpecStac_id,
			    evnsection_id := :EvnSection_id,
			    birthspecstac_countpregnancy := :BirthSpecStac_CountPregnancy,
			    birthspecstac_countbirth := :BirthSpecStac_CountBirth,
			    birthspecstac_countchild := :BirthSpecStac_CountChild,
			    birthspecstac_countchildalive := :BirthSpecStac_CountChildAlive,
			    birthresult_id := :BirthResult_id,
			    birthplace_id := :BirthPlace_id,
			    birthspecstac_outcomperiod := :BirthSpecStac_OutcomPeriod,
			    birthspecstac_outcomdt := :BirthSpecStac_OutcomDT,
			    birthspec_id := :BirthSpec_id,
			    birthspecstac_ishivtest := :BirthSpecStac_IsHIVtest,
			    birthspecstac_ishiv := :BirthSpecStac_IsHIV,
			    aborttype_id := :AbortType_id,
			    birthspecstac_ismedicalabort := :BirthSpecStac_IsMedicalAbort,
			    birthspecstac_bloodloss := :BirthSpecStac_BloodLoss,
			    pregnancyspec_id := :PregnancySpec_id,
			    pmuser_id := :pmUser_id
			);
		";
		$paramset = [
			"BirthSpecStac_id",
			"EvnSection_id",
			"BirthSpecStac_CountPregnancy",
			"BirthSpecStac_CountBirth",
			"BirthSpecStac_CountChild",
			"BirthSpecStac_CountChildAlive",
			"BirthResult_id",
			"BirthPlace_id",
			"BirthSpecStac_OutcomPeriod",
			"BirthSpecStac_OutcomDT",
			"BirthSpec_id",
			"BirthSpecStac_IsHIVtest",
			"BirthSpecStac_IsHIV",
			"AbortType_id",
			"BirthSpecStac_IsMedicalAbort",
			"BirthSpecStac_BloodLoss",
			"PregnancySpec_id",
			"pmUser_id"
		];
		//формируем массив параметров
		$queryParams = [];
		foreach ($paramset as $p) {
			if (isset($data[$p])) {
				$queryParams[$p] = $data[$p];
			} else {
				$queryParams[$p] = null;
			}
		}
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к БД");
		}
		$response = $result->result("array");
		if (!is_array($response) || count($response) == 0) {
			throw new Exception("Ошибка при выполнении запроса к БД");
		}
		return $response;
	}

	/**
	 * Загрузка данных формы специфики "Беременность и роды"
	 * @param $evnSectionId
	 * @return array|bool
	 */
	public function load($evnSectionId)
	{
		$query = "
			select
		        BirthSpecStac_id as \"BirthSpecStac_id\",
		        EvnSection_id as \"EvnSection_id\",
		        BirthSpecStac_CountPregnancy as \"BirthSpecStac_CountPregnancy\",
		        BirthSpecStac_CountBirth as \"BirthSpecStac_CountBirth\",
		        BirthSpecStac_CountChild as \"BirthSpecStac_CountChild\",
		        BirthSpecStac_CountChildAlive as \"BirthSpecStac_CountChildAlive\",
		        BirthResult_id as \"BirthResult_id\",
		        BirthPlace_id as \"BirthPlace_id\",
		        BirthSpecStac_OutcomPeriod as \"BirthSpecStac_OutcomPeriod\",
		        to_char(BirthSpecStac_OutcomDT, '{$this->dateTimeFormUnixFull}') as \"BirthSpecStac_OutcomDT\",
		        BirthSpec_id as \"BirthSpec_id\",
		        BirthSpecStac_IsHIVtest as \"BirthSpecStac_IsHIVtest\",
		        BirthSpecStac_IsHIV as \"BirthSpecStac_IsHIV\",
		        AbortType_id as \"AbortType_id\",
		        BirthSpecStac_IsMedicalAbort as \"BirthSpecStac_IsMedicalAbort\",
		        BirthSpecStac_BloodLoss as \"BirthSpecStac_BloodLoss\",
		        PregnancySpec_id as \"PregnancySpec_id\",
		        pmUser_insID as \"pmUser_insID\",
		        pmUser_updID as \"pmUser_updID\",
		        BirthSpecStac_insDT as \"BirthSpecStac_insDT\",
		        BirthSpecStac_updDT as \"BirthSpecStac_updDT\"
			from v_BirthSpecStac
			where EvnSection_id = :EvnSection_id
			order by BirthSpecStac_id desc
		";
		$queryParams = ["EvnSection_id" => $evnSectionId];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Сохранение данных по мертворожденному ребенку
	 * @param $childDeathData
	 * @return array
	 * @throws Exception
	 */
	public function saveChildDeath($childDeathData)
	{
		$procedure = "p_ChildDeath_ins";
		if ($childDeathData["ChildDeath_id"] > 0) {
			$procedure = "p_ChildDeath_upd";
		} else {
			$childDeathData["ChildDeath_id"] = null;
		}
		if ($childDeathData["BirthSvid_id"] <= 0) {
			$childDeathData["BirthSvid_id"] = null;
		}
		if (isset($childDeathData["PntDeathSvid_id"])) {
			if ($childDeathData["PntDeathSvid_id"] <= 0) {
				$childDeathData["PntDeathSvid_id"] = null;
			}
		} else {
			$childDeathData["PntDeathSvid_id"] = null;
		}
		$selectString = "
			childdeath_id as \"ChildDeath_id\",
			error_code as \"Error_Code\",
			error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    childdeath_id := :ChildDeath_id,
			    birthspecstac_id := :BirthSpecStac_id,
			    medstafffact_id := :MedStaffFact_id,
			    diag_id := :Diag_id,
			    sex_id := :Sex_id,
			    childdeath_weight := :ChildDeath_Weight,
			    childdeath_height := :ChildDeath_Height,
			    pntdeathtime_id := :PntDeathTime_id,
			    childtermtype_id := :ChildTermType_id,
			    childdeath_count := :ChildDeath_Count,
			    birthsvid_id := :BirthSvid_id,
			    okei_wid := :Okei_wid,
			    pntdeathsvid_id := :PntDeathSvid_id,
			    pmuser_id := :pmUser_id
			);
		";
		if (!isset($childDeathData["BirthSvid_id"]) || $childDeathData["BirthSvid_id"] === "") {
			$childDeathData["BirthSvid_id"] = null;
		}
		if (!isset($childDeathData["PntDeathSvid_id"]) || $childDeathData["PntDeathSvid_id"] === "") {
			$childDeathData["PntDeathSvid_id"] = null;
		}
		$queryParams = [
			"ChildDeath_id" => $childDeathData["ChildDeath_id"],
			"BirthSpecStac_id" => $childDeathData["BirthSpecStac_id"],
			"MedStaffFact_id" => $childDeathData["MedStaffFact_id"],
			"Diag_id" => $childDeathData["Diag_id"],
			"Sex_id" => $childDeathData["Sex_id"],
			"ChildDeath_Weight" => $childDeathData["ChildDeath_Weight"],
			"ChildDeath_Height" => $childDeathData["ChildDeath_Height"],
			"PntDeathTime_id" => $childDeathData["PntDeathTime_id"],
			"ChildTermType_id" => $childDeathData["ChildTermType_id"],
			"ChildDeath_Count" => $childDeathData["ChildDeath_Count"],
			"BirthSvid_id" => $childDeathData["BirthSvid_id"],
			"PntDeathSvid_id" => $childDeathData["PntDeathSvid_id"],
			"Okei_wid" => $childDeathData["Okei_wid"],
			"pmUser_id" => $childDeathData["pmUser_id"]
		];
		/** @var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных (сохранение результатов измерения роста пациента)");
		}
		return $result->result("array");
	}

	/**
	 * Загргрузка данных по метворожденным
	 * @param $data
	 * @return array|false
	 */
	function loadChildDeathGridData($data)
	{
		if (empty($data["BirthSpecStac_id"]) && empty($data["EvnSection_id"])) {
			return [];
		}
		$params = [];
		$where = "";
		if (!empty($data["BirthSpecStac_id"])) {
			$where = "CD.BirthSpecStac_id = :BirthSpecStac_id";
			$params["BirthSpecStac_id"] = $data["BirthSpecStac_id"];
		} else if (!empty($data["EvnSection_id"])) {
			$where = "
				CD.BirthSpecStac_id in (
					select BirthSpecStac_id
					from BirthSpecStac
					where EvnSection_id = :EvnSection_id
					order by BirthSpecStac_id desc
					limit 1
				)
			";
			$params["EvnSection_id"] = $data["EvnSection_id"];
		}
		$query = "
			select
				CD.ChildDeath_id as \"ChildDeath_id\",
				CD.MedStaffFact_id as \"MedStaffFact_id\",
				(select Person_Fio from v_MedStaffFact where MedStaffFact_id = cd.MedStaffFact_id) as \"MedStaffFact_Name\",
				CD.Diag_id as \"Diag_id\",
				(select diag_name from Diag as d where cd.diag_id = d.diag_id) as \"Diag_Name\",
				CD.Sex_id as \"Sex_id\",
				(select Sex_Name from sex as s where s.sex_id = cd.sex_id) as \"Sex_Name\",
				CD.ChildDeath_Weight as \"ChildDeath_Weight\",
				CD.ChildDeath_Height as \"ChildDeath_Height\",
				CD.PntDeathTime_id as \"PntDeathTime_id\",
				(select PntDeathTime_Name from PntDeathTime dt where dt.PntDeathTime_id  = cd.PntDeathTime_id) as \"PntDeathTime_Name\",
				CD.ChildTermType_id as \"ChildTermType_id\",
				(select ChildTermType_Name from ChildTermType tt where tt.ChildTermType_id = cd.ChildTermType_id) as \"ChildTermType_Name\",
				CD.ChildDeath_Count as \"ChildDeath_Count\",
				CD.BirthSvid_id as \"BirthSvid_id\",
				(select BirthSvid_Num from BirthSvid as bs where bs.BirthSvid_id = cd.BirthSvid_id) AS \"BirthSvid_Num\",
				PntDeathSvid.PntDeathSvid_id as \"PntDeathSvid_id\",
				PntDeathSvid.PntDeathSvid_Num as \"PntDeathSvid_Num\",
				CD.pmUser_insID as \"pmUser_insID\",
				CD.pmUser_updID as \"pmUser_updID\",
				CD.ChildDeath_insDT as \"ChildDeath_insDT\",
				CD.ChildDeath_updDT as \"ChildDeath_updDT\",
				CD.Okei_wid as \"Okei_wid\",
				CD.ChildDeath_Weight::varchar||' '||coalesce((select Okei_NationSymbol from v_Okei o where Okei_id = cd.Okei_wid), '') as \"ChildDeath_Weight_text\",
				1 as \"RecordStatus_Code\"
			from
				v_ChildDeath as CD
				left join lateral (
					select
						pds.PntDeathSvid_id,
						pds.PntDeathSvid_Num
					from PntDeathSvid as pds
					where pds.PntDeathSvid_id = cd.PntDeathSvid_id
					  and coalesce(pds.PntDeathSvid_isBad, 1) = 1
					  and coalesce(pds.PntDeathSvid_IsLose, 1) = 1
					  and coalesce(pds.PntDeathSvid_IsActual, 1) = 2
					limit 1
				) as PntDeathSvid on true
			where {$where}
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Загрузка данных по рожденным детям
	 * @param $data
	 * @return array|bool
	 */
	public function loadChildGridData($data)
	{
		$where = "pch.BirthSpecStac_id = :BirthSpecStac_id";
		$join = "";
		if (empty($data["BirthSpecStac_id"])) {
			$where = "e.EvnSection_pid = :EvnSection_pid";
			$join = "inner join v_EvnSection e on e.EvnSection_id = BSS.EvnSection_id";
		}
		$query = "
			select
			    pch.PersonNewBorn_id as \"PersonNewBorn_id\",
			    CPS.EvnPS_id as \"ChildEvnPS_id\",
			    child.Person_SurName AS \"Person_F\",
			    child.Person_FirName AS \"Person_I\",
			    child.Person_SecName AS \"Person_O\",
			    child.PersonEvn_id as \"PersonEvn_id\",
			    to_char(child.Person_BirthDay, '{$this->dateTimeForm104}') AS \"Person_Bday\",
			    Sex.Sex_name as \"Sex_name\",
			    Sex.Sex_id as \"Sex_id\",
			    pch.EvnSection_mid as \"EvnSection_mid\",
			    W.PersonWeight_Weight::integer as \"Person_Weight\",
			    W.PersonWeight_Weight::integer AS \"PersonWeight_text\",
			    H.PersonHeight_Height::integer as \"Person_Height\",
			    BirthSvid.BirthSvid_id as \"BirthSvid_id\",
			    BirthSvid.BirthSvid_Num as \"BirthSvid_Num\",
			    LT.LeaveType_Name as \"BirthResult\",
			    PersonNewBorn_CountChild as \"CountChild\",
			    PntDeathSvid.PntDeathSvid_id as \"PntDeathSvid_id\",
			    PntDeathSvid.PntDeathSvid_Num as \"PntDeathSvid_Num\",
			    0 AS \"RecordStatus_Code\",
			    EL.EvnLink_id AS EvnLink_id,
			    mother.Person_id as \"Person_id\",
			    child.Server_id AS \"Server_id\",
			    child.Person_id AS \"Person_cid\",
			    BSS.BirthSpecStac_id as \"BirthSpecStac_id\"
			from
			    v_PersonNewBorn pch
			    left join v_BirthSpecStac BSS on pch.birthspecstac_id = BSS.BirthSpecStac_id
			    left join v_PersonRegister PR on PR.PersonRegister_id = BSS.PersonRegister_id
			    inner join v_PersonState child on child.Person_id = pch.Person_id
			    left join v_Sex sex on sex.Sex_id = child.Sex_id
			    left join v_PersonState mother on mother.Person_id = coalesce(:Person_id, PR.Person_id)
			    {$join}
			    left join v_EvnPS CPS on CPS.EvnPS_id = pch.EvnPS_id
			    left join v_LeaveType LT on LT.LeaveType_id = CPS.LeaveType_id
			    left join v_EvnLink EL on EL.Evn_lid = CPS.EvnPS_id and EL.Evn_id = :EvnSection_pid
			    left join lateral (
			        select
			            ph.PersonHeight_Height,
			            ph.PersonHeight_id
			        from v_personHeight ph
			        where ph.person_id = child.Person_id
			          and ph.HeightMeasureType_id = 1
			        limit 1
			    ) as H on true
			    left join lateral (
			        select
			            pw.PersonWeight_id,
			            case when pw.Okei_id = 37
			                then pw.PersonWeight_Weight*1000
			                else pw.PersonWeight_Weight
			            end as PersonWeight_Weight
			        from v_personWeight pw
			        where pw.person_id = child.Person_id
			          and pw.WeightMeasureType_id = 1
			        limit 1
			    ) as W on true
			    left join lateral (
			        select
			            pds.PntDeathSvid_id,
			            pds.PntDeathSvid_Num
			        from v_PntDeathSvid pds
			        where pds.Person_cid = child.Person_id
			          and pds.Person_id = mother.Person_id
			          and coalesce(pds.PntDeathSvid_isBad, 1) = 1
			          and coalesce(pds.PntDeathSvid_IsLose, 1) = 1
			          and coalesce(pds.PntDeathSvid_IsActual, 1) = 2
			        limit 1
			    ) as PntDeathSvid on true
			    left join lateral (
			        select
			            BirthSvid_id,
			            BirthSvid_Num
			        from v_BirthSvid bs
			        where bs.Person_cid = pch.Person_id
			          and bs.Person_id = mother.Person_id
			          and coalesce(bs.BirthSvid_IsBad, 1) = 1
			        limit 1
			    ) AS BirthSvid on true
			where {$where}
		";
		$queryParams = [
			"EvnSection_pid" => $data["EvnSection_pid"],
			"Person_id" => $data["Person_id"],
			"BirthSpecStac_id" => $data["BirthSpecStac_id"]
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Сохранение данных о рожденном ребенке
	 * @param $childData
	 * @return array
	 * @throws Exception
	 */
	public function saveChild($childData)
	{
		$procedure = "p_EvnLink_ins";
		$childData["EvnLink_id"] = null;
		$selectString = "
			evnlink_id as \"EvnLink_id\",
			error_code as \"Error_Code\",
			error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString} 
			from {$procedure}(
			    evnlink_id := :EvnLink_id,
			    evn_id := :Evn_id,
			    evn_lid := :Evn_lid,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"EvnLink_id" => $childData["EvnLink_id"],
			"Evn_id" => $childData["Evn_id"],
			"Evn_lid" => $childData["Evn_lid"],
			"pmUser_id" => $childData["pmUser_id"],
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных (сохранение результатов измерения роста пациента)");
		}
		return $result->result("array");
	}

	/**
	 * @param $data
	 * @param bool $isAllowTransaction
	 * @return array|false|mixed
	 * @throws Exception
	 */
	public function deleteChild($data, $isAllowTransaction = true)
	{
		$this->isAllowTransaction = $isAllowTransaction;
		$this->beginTransaction();
		$this->load->model("PersonNewBorn_model");
		$this->load->model("Person_model");
		$this->load->model("MedSvid_model", "MedSvid_model");
		if (empty($data["PersonNewBorn_id"])) {
			$query = "
				select PersonNewBorn_id
				from v_PersonNewBorn
				where Person_id = :Person_id
				limit 1
			";
			$data["PersonNewBorn_id"] = $this->getFirstResultFromQuery($query, $data, true);
			if ($data["PersonNewBorn_id"] === false) {
				$this->rollbackTransaction();
				throw new Exception("Ошибка при получении идентификатора специфики новорожденного");
			}
		}
		$query = "
			select count(evn_id) as cnt 
			from v_evn 
			where person_id = :Person_id 
			  and Evn_id != coalesce(:Evn_id, 0)
		";
		$params = [
			"Person_id" => $data["Person_id"],
			"Evn_id" => isset($data["ChildEvnPS_id"]) ? $data["ChildEvnPS_id"] : null,
			"pmUser_id" => $data["pmUser_id"]
		];
		$EvnCount = $this->getFirstResultFromQuery($query, $params);
		if ($EvnCount === false) {
			$this->rollbackTransaction();
			throw new Exception("Ошибка при запросе количества событий у ребенка.");
		}
		if ($EvnCount > 0 && (!isset($data['type']) || !in_array($data['type'], array('kvs', 'cancel')))) {
			$this->rollbackTransaction();
			throw new Exception("У ребенка имеются события, удаление невозможно.");
		}
		$query = "
			select (
				select count(*) as cnt
				from v_BirthSvid
				where Person_cid = :Person_id 
				  and coalesce(BirthSvid_isBad, 1) = 1
				limit 1
			) + (
				select count(*) as cnt
				from v_PntDeathSvid
				where Person_cid = :Person_id 
				  and coalesce(PntDeathSvid_isBad, 1) = 1 
				  and coalesce(PntDeathSvid_IsLose, 1) = 1
				  and coalesce(PntDeathSvid_IsActual, 1) = 2
				limit 1
			) as cnt
		";
		$cnt = $this->getFirstResultFromQuery($query, $params);
		if ($cnt === false) {
			$this->rollbackTransaction();
			throw new Exception("Ошибка при проверке существования свидетельств.");
		}
		if ($cnt > 0) {
			$this->rollbackTransaction();
			throw new Exception("У ребенка имеется мед.свидетельство, удаление не возможно.");
		}
		if (!empty($data["PersonNewBorn_id"])) {
			$this->PersonNewBorn_model->setPersonNewBornEvnPS([
				"PersonNewBorn_id" => $data["PersonNewBorn_id"],
				"EvnPS_id" => null
			]);
			if (!empty($data["PntDeathSvid_id"]) && $data["PntDeathSvid_id"] > 0) {
				$this->MedSvid_model->deleteMedSvid($data, "pntdeath");
			}
			if (!empty($data["EvnLink_id"]) && $data["EvnLink_id"] > 0) {
				$resp = $this->deleteEvnLink($data["EvnLink_id"]);
				if (!$this->isSuccessful($resp)) {
					$this->rollbackTransaction();
					return $resp;
				}
			}
			if (!empty($data["ChildEvnPS_id"]) && $data["ChildEvnPS_id"] > 0) {
				$data["EvnPS_id"] = $data["ChildEvnPS_id"];
				$this->load->model("EvnPS_model", "EvnPS_model");
				$data["isExecCommonChecksOnDelete"] = true;
				$resp = $this->EvnPS_model->deleteEvnPS($data, false);
				if (!$this->isSuccessful($resp)) {
					$this->rollbackTransaction();
					return $resp;
				}
			}
			if (isset($data["type"]) && $data["type"] == "kvs") {
				$this->commitTransaction();
				return [["success" => true, "Error_Code" => null, "Error_Msg" => null]];
			}
			$query = "
				select PersonBirthTrauma_id as \"PersonBirthTrauma_id\"
				from v_PersonBirthTrauma
				where PersonNewborn_id = :PersonNewBorn_id
			";
			$resp = $this->queryResult($query, $data);
			if (is_array($resp)) {
				foreach ($resp as $item) {
					$query = "
						select
							error_code as \"Error_Code\",
							error_message as \"Error_Msg\"
						from p_personbirthtrauma_del(
							personbirthtrauma_id := :personbirthtrauma_id
						);
					";
					$queryParams = ["personbirthtrauma_id" => $item["PersonBirthTrauma_id"]];
					$result = $this->queryResult($query, $queryParams);
					if (!is_array($result)) {
						$this->rollbackTransaction();
						throw new Exception("Ошибка при удалении данных новорожденного");
					}
				}
			}
			$query = "
				select NewbornApgarRate_id as \"NewbornApgarRate_id\"
				from v_NewbornApgarRate
				where PersonNewborn_id = :PersonNewBorn_id
			";
			$resp = $this->queryResult($query, $data);
			if (is_array($resp)) {
				foreach ($resp as $item) {
					$query = "
						select
							error_code as \"Error_Code\",
							error_message as \"Error_Msg\"
						from p_newbornapgarrate_del(
							newbornapgarrate_id := :newbornapgarrate_id
						);
					";
					$queryParams = ["newbornapgarrate_id" => $item["NewbornApgarRate_id"]];
					$result = $this->queryResult($query, $queryParams);
					if (!is_array($result)) {
						$this->rollbackTransaction();
						throw new Exception("Ошибка при удалении данных новорожденного");
					}
				}
			}
			$query = "
				update EvnObservNewborn 
				set PersonNewBorn_id = null 
				where PersonNewBorn_id = :PersonNewBorn_id
			";
			$this->queryResult($query, $data);
			$query = "
				select
					error_code as \"Error_Code\",
					error_message as \"Error_Msg\"
				from p_personnewborn_del(
					personnewborn_id := :PersonNewBorn_id
				);
			";
			$resp = $this->queryResult($query, $data);
			if (!is_array($resp)) {
				$this->rollbackTransaction();
				throw new Exception("Ошибка при удалении данных новорожденного");
			}
			if (!$this->isSuccessful($resp)) {
				$this->rollbackTransaction();
				return $resp;
			}
		}
		$resp = $this->Person_model->deletePerson($params);
		if (!$this->isSuccessful($resp)) {
			$this->rollbackTransaction();
			return $resp;
		}
		$this->isAllowTransaction = $isAllowTransaction;
		$this->commitTransaction();
		return [["success" => true, "Error_Code" => null, "Error_Msg" => null]];
	}

	/**
	 * Удаление детей из списка
	 * 
	 * @param $data
	 * @return array|false|mixed
	 * @throws Exception
	 */
	public function deleteChildren($data)
	{
		if (count($data["PersonNewBorn_ids"]) == 0) {
			return [["success" => true]];
		}
		$ids_string = implode(",", $data['PersonNewBorn_ids']);
		$query = "
			select
				PNB.PersonNewBorn_id as \"PersonNewBorn_id\",
				PNB.Person_id as \"Person_id\",
				EL.EvnLink_id as \"EvnLink_id\",
				EL.Evn_lid as \"ChildEvnPS_id\"
			from
				v_PersonNewBorn PNB
				left join v_BirthSpecStac BSS on BSS.BirthSpecStac_id = PNB.BirthSpecStac_id
				left join v_EvnSection ES on ES.EvnSection_id = BSS.EvnSection_id
				left join v_EvnPS EPS on EPS.EvnPS_id = ES.EvnSection_pid
				left join v_EvnLink EL on EL.Evn_id = EPS.EvnPS_id
			where PNB.PersonNewBorn_id in ($ids_string)
		";
		$PersonNewBornList = $this->queryResult($query);
		if (!is_array($PersonNewBornList)) {
			throw new Exception("Ошибка при получении данных новорожденных");
		}
		foreach ($PersonNewBornList as $PersonNewBorn) {
			//Удаление всех данных новорожденного, в том числе Person
			$funcParams = [
				"PersonNewBorn_id" => $PersonNewBorn["PersonNewBorn_id"],
				"Person_id" => $PersonNewBorn["Person_id"],
				"ChildEvnPS_id" => $PersonNewBorn["ChildEvnPS_id"],
				"EvnLink_id" => $PersonNewBorn["EvnLink_id"],
				"pmUser_id" => $data["pmUser_id"]
			];
			$resp = $this->deleteChild($funcParams);
			if (!$this->isSuccessful($resp)) {
				return $resp;
			}
		}
		return [["success" => true]];
	}

	/**
	 * Удаление ребенка из КВС матери
	 * @param $ChildEvnPS_id
	 * @param $EvnLink_id
	 * @param $pmUser_id
	 * @throws Exception
	 */
	function delChild($ChildEvnPS_id, $EvnLink_id, $pmUser_id)
	{
		//при удалении дитя надо удалить связь мать-дитя и КВС дитя
		//удаление КВС дитя
		require_once("EvnPS_model.php");
		$EvnPS = new EvnPS_model();
		$EvnPS->deleteEvnPS(["EvnPS_id" => $ChildEvnPS_id, "pmUser_id" => $pmUser_id]);
		$this->deleteEvnLink($EvnLink_id);
	}

	/**
	 * Удаление связи КВС матери и КВС ребенка
	 * @param $EvnLink_id
	 * @return array
	 * @throws Exception
	 */
	function deleteEvnLink($EvnLink_id)
	{
		$query = "
			select
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from p_evnlink_del(evnlink_id := :EvnLink_id);
		";
		$queryParams = ["EvnLink_id" => $EvnLink_id];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных (удаление связи КВС матери и КВС ребенка)");
		}
		return $result->result("array");
	}

	/**
	 * Удаление данных по метровожденному
	 * @param $ChildDeath_id
	 * @return array|false
	 * @throws Exception
	 */
	function deleteChildDeath($ChildDeath_id)
	{
		$query = "
			select PntDeathSvid.PntDeathSvid_id as \"PntDeathSvid_id\"
			from 
				v_ChildDeath CD
				left join lateral (
					select pds.PntDeathSvid_id
					from PntDeathSvid AS pds
					where pds.PntDeathSvid_id = cd.PntDeathSvid_id
					  and coalesce(pds.PntDeathSvid_isBad, 1) = 1
					  and coalesce(pds.PntDeathSvid_IsLose, 1) = 1
					  and coalesce(pds.PntDeathSvid_IsActual, 1) = 2
					limit 1
				) as PntDeathSvid on true
			where CD.ChildDeath_id = :ChildDeath_id
			limit 1
		";
		$queryParams = ["ChildDeath_id" => $ChildDeath_id];
		$PntDeathSvid_id = $this->getFirstResultFromQuery($query, $queryParams, true);
		if ($PntDeathSvid_id === false) {
			throw new Exception("Ошибка при получении мед.свидетельсвта мертворожденного");
		}
		if (!empty($PntDeathScvid_id)) {
			throw new Exception("Нельзя удалить мертворожденного, т.к. выписано свидетельство о смерти");
		}
		$query = "
			select
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from p_childdeath_del(
				childdeath_id := :ChildDeath_id
			);
		";
		$response = $this->queryResult($query, $queryParams);
		if (!is_array($response)) {
			throw new Exception("Ошибка при удалении мертворожденного");
		}
		return $response;
	}

	/**
	 * Проверка
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function checkChild($data)
	{
		$childEvnPS_id = !empty($data["childEvnPS_id"]) ? $data["childEvnPS_id"] : null;
		$motherEvnPS_id = !empty($data["motherEvnPS_id"]) ? $data["motherEvnPS_id"] : null;
		if (!empty($data["BirthSpecStac_OutcomeDate"])) {
			$BirthSpecStac_OutcomeDate = ":BirthSpecStac_OutcomeDate";
			$queryParams["BirthSpecStac_OutcomeDate"] = $data["BirthSpecStac_OutcomeDate"];
		} else {
			if (!$data["motherEvnSection_id"]) {
				throw new Exception("Не указан идентификатор движения матери");
			}
			$BirthSpecStac_OutcomeDate = "(select b.BirthSpecStac_OutcomDT from v_BirthSpecStac b where b.EvnSection_id = :motherEvnSection_id order by BirthSpecStac_id desc limit 1)";
			$queryParams["motherEvnSection_id"] = $data["motherEvnSection_id"];
		}
		if (isset($data["mother_Person_id"]) && $data["mother_Person_id"]) {
			$mother_Person_id = ":mother_Person_id";
			$queryParams["mother_Person_id"] = $data["mother_Person_id"];
			$queryParams["motherEvnPS_id"] = null;
		} else {
			if (!$data["motherEvnPS_id"]) {
				throw new Exception("Не указан идентификатор КВС матери");
			}
			$mother_Person_id = "
				(
					select person_id
			        from v_evnPS
			        where evnPS_id = :motherEvnPS_id
				)
			";
			$queryParams["motherEvnPS_id"] = $motherEvnPS_id;
		}
		if (isset($data["child_Person_id"]) && $data["child_Person_id"]) {
			$child_Person_id = ":child_Person_id";
			$queryParams["child_Person_id"] = $data["child_Person_id"];
			$queryParams["childEvnPS_id"] = null;
		} else {
			$queryParams["child_Person_id"] = null;
			if (!$data["childEvnPS_id"]) {
				throw new Exception("Не указан идентификатор КВС ребенка");
			}
			$child_Person_id = "(select person_id from v_EvnPS where EvnPS_id = :childEvnPS_id)";
			$queryParams["childEvnPS_id"] = $childEvnPS_id;
		}
		$result = [["Success" => true]];
		$query = "
			select  
				case when {$child_Person_id} = {$mother_Person_id} then 1 else 0 end as \"isSamePerson\",
				case when {$child_Person_id} in (
					SELECT (
						SELECT Person_id
						FROM v_EvnPS ps 
						WHERE ps.EvnPS_id = el.Evn_lid
						  and ps.EvnPS_id != :childEvnPS_id
					)
					from v_EvnLink el
					where Evn_id in (
						select EvnPS_id
						from v_EvnPS
						where Person_id = {$mother_Person_id}
					)
				) then 1 else 0 end as \"alredyBindedThis\",
				coalesce((
					select coalesce(Person_SurName, '')||' '||coalesce(Person_FirName, '')||' '||coalesce(Person_SecName, '')
					from v_PersonState
					where   
						Person_id = (
							select Person_id
							from v_EvnPS
							where EvnPS_id = ( 
								select evn_id
								from v_EvnLink
								where Evn_lid in (select EvnPS_id from v_EvnPS where person_id = {$child_Person_id} )
								  and Evn_id not in (select EvnPS_id from v_EvnPS where person_id = {$mother_Person_id})
								limit 1
							)
						)
				)::int, 0) AS \"alredyBindedAnother\",
			    extract(day from {$BirthSpecStac_OutcomeDate}::timestamp - (select Person_BirthDay from v_PersonState where Person_id = {$child_Person_id})::timestamp) as \"datediff\"
		";
		/**@var CI_DB_result $query_result */
		$query_result = $this->db->query($query, $queryParams);
		if (!is_object($query_result)) {
			throw new Exception("Ошибка запроса к БД (проверка связи мать-ребенок)");
		}
		$query_result_array = $query_result->result('array');
		if (count($query_result_array) != 1) {
			throw new Exception("Ошибочный результат запроса (проверка связи мать-ребенок)");
		}
		//принимаем и расшифровываем пользователю сообщения об ошибках, переданные базой
		//childAndMotherIsSamePerson должно быть 0, иначе "Мать добавляется в список рожденных ею детей"
		$err = "";
		if (!(0 == $query_result_array[0]["isSamePerson"])) {
			$err = "Мать добавляется в список рожденных ею детей";
		} else {
			//childAlredyBindedToThisMother должно быть 0, иначе "Ребенок к матери добавляется повторно"
			//childAlredyBindedToAnotherMother должно быть 0, инчае "Ребенок уже привязан к другой матери (ФИО матери - в поле)"
			if (!($query_result_array[0]["alredyBindedAnother"] == "0")) {
				$err = "Ребенок уже привязан к другой матери ({$query_result_array[0]["alredyBindedAnother"]})";
			} else {
				//datediffOutcomBateOfBirth должно быть
				//  более 0, иначе "Добавляется ребенок, дата рождения которого наступила на N дней раньше даты этих родов"
				//  менее или равна 2, иначе "Добавляется ребенок, дата рождения которого наступила на N дней позже даты этих родов"
				$days = (int)$query_result_array[0]["datediff"];
				if ($days < 0) {
					$days = -1 * $days;
					$word_case = $this->ru_word_case("день", "дня", "дней", $days);
					$err = "Добавляется ребенок, дата рождения которого наступила на $days $word_case раньше даты этих родов";
				} else {
					if ($days > 2) {
						//  менее или равна 2, иначе "Добавляется ребенок, дата рождения которого наступила на N дней позже даты этих родов"
						$word_case = $this->ru_word_case("день", "дня", "дней", $days);
						$err = "Добавляется ребенок, дата рождения которого наступила на $days $word_case позже даты этих родов";
					}
				}
			}
		}
		if ($err) {
			throw new Exception($err);
		}

		$query = "
			select
			    ps.Person_id as \"Person_id\",
			    el.EvnLink_id as \"EvnLink_id\",
			    pc.PersonNewBorn_id as \"PersonNewBorn_id\",
			    pc.EvnPS_id as \"EvnPS_id\"
			from
			    v_personstate ps
			    left join lateral (
			        select el.EvnLink_id
			        from dbo.v_EvnLink el
			        where el.Evn_id = :motherEvnPS_id
			          and el.Evn_lid = :childEvnPS_id
			        limit 1
			    ) as el on true
			    left join v_PersonNewBorn pc on pc.person_id = ps.person_id
			where ps.person_id = coalesce((select person_id from v_EvnPS where EvnPS_id = :childEvnPS_id), :child_Person_id)
		";
		$res = $this->db->query($query, $queryParams);
		$res = $res->result("array");
		if (count($res) > 0) {
			if ($query_result_array[0]["alredyBindedThis"] > 0) {
				throw new Exception("Ребенок к матери добавляется повторно");
			}
			if ($res[0]["EvnLink_id"] == null) {
				$arr = [
					"EvnLink_id" => null,
					"Evn_id" => $motherEvnPS_id,
					"Evn_lid" => $childEvnPS_id,
					"pmUser_id" => $data["pmUser_id"]
				];
				$this->saveChild($arr);
			}
			$result[0]["person_id"] = $res[0]["Person_id"];
			if ($res[0]["PersonNewBorn_id"] == null) {
				$result[0]["add"] = 1;
			}
			if (!empty($res[0]["PersonNewBorn_id"]) && empty($res[0]["EvnPS_id"])) {
				$this->load->model("PersonNewBorn_model");
				$this->PersonNewBorn_model->setPersonNewBornEvnPS([
					"PersonNewBorn_id" => $res[0]["PersonNewBorn_id"],
					"EvnPS_id" => $childEvnPS_id
				]);
			}
		}
		return $result;
	}

	/**
	 * Склоняем слово по числам
	 * @param $case1 - ед. число,
	 * @param $case2 - мн. число для 2, 3, 4 или оканчивающихся на 2, 3, 4
	 * @param $case3 - мн. число для 5-20 (включительно), и всех что кончаются на любые кроме 2, 3, 4
	 * @param $anInteger
	 * @return mixed
	 *
	 * пример:
	 *   '1 '.ru_word_case('день', 'дня', 'дней', 1) // output: 1 день
	 *   '2 '.ru_word_case('день', 'дня', 'дней', 2) // output: 2 дня
	 *   '11 '.ru_word_case('день', 'дня', 'дней', 11) // output: 11 дней
	 *   '21 '.ru_word_case('день', 'дня', 'дней', 21) // output: 21 день
	 */
	function ru_word_case($case1, $case2, $case3, $anInteger)
	{
		$result = $case3;
		if (($anInteger < 5) || (20 < $anInteger)) {
			$days = (string)$anInteger;
			$lastSymbol = $days[strlen($anInteger) - 1];
			switch ($lastSymbol) {
				case "1":
					$result = $case1;
					break;
				case "2":
				case "3":
				case "4":
					$result = $case2;
					break;
				default:
					break;
			}
		}
		return $result;
	}

	/**
	 * Удаление специфики по беременности
	 * @param $BirthSpecStac_id
	 * @param $pmUser_id
	 * @param bool $isAllowTransaction
	 * @return array|false|mixed
	 * @throws Exception
	 */
	public function del($BirthSpecStac_id, $pmUser_id, $isAllowTransaction = true)
	{
		$this->isAllowTransaction = $isAllowTransaction;
		$this->beginTransaction();
		//получение списка детей и удаление
		$childLinks = $this->getChildLinks($BirthSpecStac_id);
		foreach ($childLinks as $childLink) {
			//Удаление всех данных новорожденного, в том числе Person
			$resp = $this->deleteChild([
				"PersonNewBorn_id" => $childLink["PersonNewBorn_id"],
				"Person_id" => $childLink["Person_id"],
				"ChildEvnPS_id" => $childLink["ChildEvnPS_id"],
				"EvnLink_id" => $childLink["EvnLink_id"],
				"pmUser_id" => $pmUser_id
			], false);
			if (!$this->isSuccessful($resp)) {
				$this->rollbackTransaction();
				return $resp;
			}
		}
		$ChildDeaths = $this->getChildDeaths($BirthSpecStac_id);
		foreach ($ChildDeaths as $ChildDeath) {
			$resp = $this->deleteChildDeath($ChildDeath["ChildDeath_id"]);
			if (!$this->isSuccessful($resp)) {
				$this->rollbackTransaction();
				return $resp;
			}
		}
		$query = "
			select PersonChild_id as \"PersonChild_id\"
			from v_PersonChild
			where BirthSpecStac_id = :BirthSpecStac_id
		";
		$queryParams = ["BirthSpecStac_id" => $BirthSpecStac_id];
		$PersonChildList = $this->queryResult($query, $queryParams);
		if (!is_array($PersonChildList)) {
			$this->rollbackTransaction();
			throw new Exception("Ошибка при удалении PersonChild");
		}
		foreach ($PersonChildList as $PersonChild) {
			$query = "
				select
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from p_personchild_del(
					personchild_id := :PersonChild_id
				);
			";
			$resp = $this->queryResult($query, $PersonChild);
			if (!is_array($resp)) {
				$this->rollbackTransaction();
				throw new Exception("Ошибка при удалении PersonChild");
			}
			if (!$this->isSuccessful($resp)) {
				$this->rollbackTransaction();
				return $resp;
			}
		}
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_birthspecstac_del(
				birthspecstac_id := :BirthSpecStac_id
			);
		";
		$params = ["BirthSpecStac_id" => $BirthSpecStac_id];
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			$this->rollbackTransaction();
			throw new Exception("Ошибка при удалении исхода беременности");
		}
		if (!$this->isSuccessful($response)) {
			$this->rollbackTransaction();
			return $response;
		}
		$this->isAllowTransaction = $isAllowTransaction;
		$this->commitTransaction();
		return $response;
	}

	/**
	 * Получение данных о специфике родов. Метод для API.
	 * @param $data
	 * @return array|false
	 */
	public function getBirthSpecStacForAPI($data)
	{
		$queryParams = array();
		$filter = "";

		if (!empty($data['EvnSection_id'])) {
			$filter .= " and bss.EvnSection_id = :EvnSection_id";
			$queryParams['EvnSection_id'] = $data['EvnSection_id'];
		}
		if (!empty($data['PregnancyResult_id'])) {
			$filter .= " and bss.PregnancyResult_id = :PregnancyResult_id";
			$queryParams['PregnancyResult_id'] = $data['PregnancyResult_id'];
		}
		if (empty($filter)) {
			return [];
		}

		return $this->queryResult("
			select
				bss.BirthSpecStac_id as \"BirthSpecStac_id\",
				bss.EvnSection_id as \"EvnSection_id\",
				bss.BirthSpecStac_CountPregnancy as \"BirthSpecStac_CountPregnancy\",
				bss.BirthSpecStac_CountBirth as \"BirthSpecStac_CountBirth\",
				bss.BirthSpecStac_CountChild as \"BirthSpecStac_CountChild\",
				bss.BirthSpecStac_CountChildAlive as \"BirthSpecStac_CountChildAlive\",
				bss.BirthResult_id as \"BirthResult_id\",
				bss.BirthPlace_id as \"BirthPlace_id\",
				bss.BirthSpecStac_OutcomPeriod as \"BirthSpecStac_OutcomPeriod\",
				bss.BirthSpecStac_OutcomDT as \"BirthSpecStac_OutcomDT\",
				bss.BirthSpec_id as \"BirthSpec_id\",
				case when bss.BirthSpecStac_IsHIVtest = 2 then 1 else 0 end as \"BirthSpecStac_IsHIVtest\",
				case when bss.BirthSpecStac_IsHIV = 2 then 1 else 0 end as \"BirthSpecStac_IsHIV\",
				bss.AbortType_id as \"AbortType_id\",
				case when bss.BirthSpecStac_IsMedicalAbort = 2 then 1 else 0 end as \"BirthSpecStac_IsMedicalAbort\",
				bss.BirthSpecStac_BloodLoss as \"BirthSpecStac_BloodLoss\",
				bss.PregnancySpec_id as \"PregnancySpec_id\",
				bss.Evn_id as \"Evn_id\",
				bss.Lpu_id as \"Lpu_id\",
				bss.PregnancyResult_id as \"PregnancyResult_id\",
				bss.BirthCharactType_id as \"BirthCharactType_id\",
				bss.AbortLpuPlaceType_id as \"AbortLpuPlaceType_id\",
				case when bss.BirthSpecStac_IsRWtest = 2 then 1 else 0 end as \"BirthSpecStac_IsRWtest\",
				case when bss.BirthSpecStac_IsRW = 2 then 1 else 0 end as \"BirthSpecStac_IsRW\",
				case when bss.BirthSpecStac_IsHBtest = 2 then 1 else 0 end as \"BirthSpecStac_IsHBtest\",
				case when bss.BirthSpecStac_IsHB = 2 then 1 else 0 end as \"BirthSpecStac_IsHB\",
				case when bss.BirthSpecStac_IsHCtest = 2 then 1 else 0 end as \"BirthSpecStac_IsHCtest\",
				case when bss.BirthSpecStac_IsHC = 2 then 1 else 0 end as \"BirthSpecStac_IsHC\",
				bss.AbortLawType_id as \"AbortLawType_id\",
				bss.AbortMethod_id as \"AbortMethod_id\",
				bss.BirthSpecStac_InjectVMS as \"BirthSpecStac_InjectVMS\",
				bss.BirthSpecStac_Info as \"BirthSpecStac_Info\",
				bss.BirthSpecStac_SurgeryVolume as \"BirthSpecStac_SurgeryVolume\",
				bss.AbortIndicat_id as \"AbortIndicat_id\",
				bss.BirthSpecStac_IsContrac as \"BirthSpecStac_IsContrac\",
				bss.BirthSpecStac_ContracDesc as \"BirthSpecStac_ContracDesc\",
				bss.PersonRegister_id as \"PersonRegister_id\"
			from
				v_BirthSpecStac bss
			where
				1=1
				{$filter}
		", $queryParams);
	}

	/**
	 * Получение списка КВС детей связанных с КВС матери
	 * @param $BirthSpecStac_id
	 * @return array|bool
	 */
	function getChildLinks($BirthSpecStac_id)
	{
		$query = "
			select
				PNB.PersonNewBorn_id as \"PersonNewBorn_id\",
				PNB.Person_id as \"Person_id\",
				el.EvnLink_id as \"EvnLink_id\",
				el.Evn_lid as \"ChildEvnPS_id\"
			from
				v_BirthSpecStac bs
				inner join v_PersonNewBorn PNB on PNB.BirthSpecStac_id = bs.BirthSpecStac_id
				left join v_EvnSection es on bs.EvnSection_id = es.EvnSection_id
				left join v_EvnPS ps on ps.EvnPS_id = es.EvnSection_pid
				left join v_EvnLink el on el.Evn_id = ps.EvnPS_id
			where bs.BirthSpecStac_id = :BirthSpecStac_id
		";
		$params = ["BirthSpecStac_id" => $BirthSpecStac_id];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Список мертворожденных
	 * @param $BirthSpecStac_id
	 * @return array|bool
	 */
	function getChildDeaths($BirthSpecStac_id)
	{
		$query = "
			select ChildDeath_id as \"ChildDeath_id\"
			from ChildDeath
			where BirthSpecStac_id = :BirthSpecStac_id
		";
		$params = ["BirthSpecStac_id" => $BirthSpecStac_id];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}
	
	/**
     * метод проверки связи движения и исхода беременности
     */
	function mCheckingMovementAndPregnancyOutcomeForAPI($data){
		if(empty($data['EvnSection_id'])) return false;
		$query = "
			SELECT
				to_char(BirthSpecStac_OutcomDT,'yyyy-mm-dd hh:mi:ss') as \"BirthSpecStac_OutcomDT\",
				BSS.BirthSpecStac_CountPregnancy as \"BirthSpecStac_CountPregnancy\",
				PR.PregnancyResult_Name as \"PregnancyResult_Name\",
				BSS.BirthSpecStac_OutcomPeriod as \"BirthSpecStac_OutcomPeriod\",
				BSS.BirthSpecStac_CountChild as \"BirthSpecStac_CountChild\",
				BSS.BirthSpecStac_BloodLoss as \"BirthSpecStac_BloodLoss\",
				ALPT.AbortLpuPlaceType_Name as \"AbortLpuPlaceType_Name\",
				ALT.AbortLawType_Name as \"AbortLawType_Name\",
				AM.AbortMethod_Name as \"AbortMethod_Name\",
				AI.AbortIndicat_Name as \"AbortIndicat_Name\",
				BSS.BirthSpecStac_InjectVMS as \"BirthSpecStac_InjectVMS\",
				BP.BirthPlace_Name as \"BirthPlace_Name\",
				BSS.BirthSpecStac_CountBirth as \"BirthSpecStac_CountBirth\",
				BS.BirthSpec_Name as \"BirthSpec_Name\",
				--BR.BirthResult_Name as \"BirthResult_Name\",
				BCT.BirthCharactType_Name as \"BirthCharactType_Name\",
				BSS.BirthSpecStac_CountChildAlive as \"BirthSpecStac_CountChildAlive\"
			FROM v_BirthSpecStac BSS 
				LEFT JOIN dbo.PregnancyResult PR  ON PR.PregnancyResult_id = BSS.PregnancyResult_id
				LEFT JOIN v_AbortLpuPlaceType ALPT  ON ALPT.AbortLpuPlaceType_id = BSS.AbortLpuPlaceType_id
				LEFT JOIN v_AbortLawType ALT  ON ALT.AbortLawType_id = BSS.AbortLawType_id
				LEFT JOIN v_AbortMethod AM  ON AM.AbortMethod_id = BSS.AbortMethod_id
				LEFT JOIN dbo.v_AbortIndicat AI  ON AI.AbortIndicat_id = BSS.AbortIndicat_id
				LEFT JOIN v_BirthPlace BP  ON BP.BirthPlace_id = BSS.BirthPlace_id
				LEFT JOIN v_BirthSpec BS  ON BS.BirthSpec_id = BSS.BirthSpec_id
				LEFT JOIN v_BirthResult BR  ON BR.BirthResult_id = BSS.BirthResult_id
				LEFT JOIN v_BirthCharactType BCT  ON BCT.BirthCharactType_id = BSS.BirthCharactType_id
			WHERE BSS.EvnSection_id = :EvnSection_id
		";
		$r = $this->db->query($query, $data);
		if (is_object($r)) {
			$result = $r->result('array');
		} else {
			$result =  false;
		}
		return $result;
	}	
	
}

<?php
defined("BASEPATH") or die ("No direct script access allowed");

/**
 * Class DrugNormativeList_model
 *
 * @property CI_DB_driver $db
 */
class DrugNormativeList_model extends swPgModel
{
	public $dateTimeForm104 = "DD.MM.YYYY";
	
	private $DrugNormativeList_id;//DrugNormativeList_id
	private $DrugNormativeList_Name;//Наименование
	private $WhsDocumentCostItemType_id;//Программа ЛЛО
	private $PersonRegisterType_id;//Тип перечня 
	private $DrugNormativeList_BegDT;//Дата начала действия записи
	private $DrugNormativeList_EndDT;//Дата окончания действия записи
	private $pmUser_id;//Идентификатор пользователя системы Промед

	/**
	 * @return mixed
	 */
	public function getDrugNormativeList_id()
	{
		return $this->DrugNormativeList_id;
	}

	/**
	 * @param $value
	 */
	public function setDrugNormativeList_id($value)
	{
		$this->DrugNormativeList_id = $value;
	}

	/**
	 * @return mixed
	 */
	public function getDrugNormativeList_Name()
	{
		return $this->DrugNormativeList_Name;
	}

	/**
	 * @param $value
	 */
	public function setDrugNormativeList_Name($value)
	{
		$this->DrugNormativeList_Name = $value;
	}

	/**
	 * @return mixed
	 */
	public function getWhsDocumentCostItemType_id() {
		return $this->WhsDocumentCostItemType_id;
	}

	/**
	 * @param $value
	 */
	public function setWhsDocumentCostItemType_id($value) {
		$this->WhsDocumentCostItemType_id = $value;
	}

	/**
	 * @return mixed
	 */
	public function getPersonRegisterType_id()
	{
		return $this->PersonRegisterType_id;
	}

	/**
	 * @param $value
	 */
	public function setPersonRegisterType_id($value)
	{
		$this->PersonRegisterType_id = $value;
	}

	/**
	 * @return mixed
	 */
	public function getDrugNormativeList_BegDT()
	{
		return $this->DrugNormativeList_BegDT;
	}

	/**
	 * @param $value
	 */
	public function setDrugNormativeList_BegDT($value)
	{
		$this->DrugNormativeList_BegDT = $value;
	}

	/**
	 * @return mixed
	 */
	public function getDrugNormativeList_EndDT()
	{
		return $this->DrugNormativeList_EndDT;
	}

	/**
	 * @param $value
	 */
	public function setDrugNormativeList_EndDT($value)
	{
		$this->DrugNormativeList_EndDT = $value;
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
	 * Получение списка
	 * @return array|bool|CI_DB_result
	 */
	function load()
	{
		$query = "
			select
				DrugNormativeList_id as \"DrugNormativeList_id\",
			    DrugNormativeList_Name as \"DrugNormativeList_Name\",
			    WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\",
			    PersonRegisterType_id as \"PersonRegisterType_id\",
			    DrugNormativeList_BegDT as \"DrugNormativeList_BegDT\",
			    DrugNormativeList_EndDT as \"DrugNormativeList_EndDT\"
			from v_DrugNormativeList
			where DrugNormativeList_id = :DrugNormativeList_id
		";
		$queryParams = ["DrugNormativeList_id" => $this->DrugNormativeList_id];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result("array");
		if (!isset($result[0])) {
			return false;
		}
		$this->DrugNormativeList_id = $result[0]["DrugNormativeList_id"];
		$this->DrugNormativeList_Name = $result[0]["DrugNormativeList_Name"];
		$this->WhsDocumentCostItemType_id = $result[0]['WhsDocumentCostItemType_id'];
		$this->PersonRegisterType_id = $result[0]["PersonRegisterType_id"];
		$this->DrugNormativeList_BegDT = $result[0]["DrugNormativeList_BegDT"];
		$this->DrugNormativeList_EndDT = $result[0]["DrugNormativeList_EndDT"];
		return $result;
	}

	/**
	 * Получение списка
	 * @param $filter
	 * @return array|bool
	 */
	function loadList($filter)
	{
		$where = [];
		$queryParams = [];
		if (isset($filter["DrugNormativeList_id"]) && $filter["DrugNormativeList_id"]) {
			$where[] = "v_DrugNormativeList.DrugNormativeList_id = :DrugNormativeList_id";
			$queryParams["DrugNormativeList_id"] = $filter["DrugNormativeList_id"];
		}
		if (isset($filter["DrugNormativeList_Name"]) && $filter["DrugNormativeList_Name"]) {
			$where[] = "v_DrugNormativeList.DrugNormativeList_Name = :DrugNormativeList_Name";
			$queryParams["DrugNormativeList_Name"] = $filter["DrugNormativeList_Name"];
		}
		if (isset($filter["PersonRegisterType_id"]) && $filter["PersonRegisterType_id"]) {
			$where[] = "v_DrugNormativeList.PersonRegisterType_id = :PersonRegisterType_id";
			$queryParams["PersonRegisterType_id"] = $filter["PersonRegisterType_id"];
		}
		if (isset($filter["DrugNormativeList_BegDT"]) && $filter["DrugNormativeList_BegDT"]) {
			$where[] = "v_DrugNormativeList.DrugNormativeList_BegDT = :DrugNormativeList_BegDT";
			$queryParams["DrugNormativeList_BegDT"] = $filter["DrugNormativeList_BegDT"];
		}
		if (isset($filter["DrugNormativeList_EndDT"]) && $filter["DrugNormativeList_EndDT"]) {
			$where[] = "v_DrugNormativeList.DrugNormativeList_EndDT = :DrugNormativeList_EndDT";
			$queryParams["DrugNormativeList_EndDT"] = $filter["DrugNormativeList_EndDT"];
		}
		$whereString = (count($where) != 0) ? "where " . implode(" and ", $where) : "";
		$query = "
			select
				v_DrugNormativeList.DrugNormativeList_id as \"DrugNormativeList_id\",
				v_DrugNormativeList.DrugNormativeList_Name as \"DrugNormativeList_Name\",
				WhsDocumentCostItemType_ref.WhsDocumentCostItemType_Name as \"WhsDocumentCostItemType_Name\",
				v_DrugNormativeList.PersonRegisterType_id as \"PersonRegisterType_id\",
				to_char(v_DrugNormativeList.DrugNormativeList_BegDT, '{$this->dateTimeForm104}') as \"DrugNormativeList_BegDT\",
				to_char(v_DrugNormativeList.DrugNormativeList_EndDT, '{$this->dateTimeForm104}') as \"DrugNormativeList_EndDT\",
				PersonRegisterType_id_ref.PersonRegisterType_Name as \"PersonRegisterType_Name\",
				(select count(DrugNormativeListSpec_id) from v_DrugNormativeListSpec where DrugNormativeList_id = v_DrugNormativeList.DrugNormativeList_id) as \"DrugNormativeListSpec_count\"
			from
				v_DrugNormativeList
				left join dbo.v_PersonRegisterType PersonRegisterType_id_ref on PersonRegisterType_id_ref.PersonRegisterType_id = v_DrugNormativeList.PersonRegisterType_id
				left join dbo.WhsDocumentCostItemType WhsDocumentCostItemType_ref ON WhsDocumentCostItemType_ref.WhsDocumentCostItemType_id = v_DrugNormativeList.WhsDocumentCostItemType_id
			{$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * Сохранение
	 * @return array|CI_DB_result
	 * @throws Exception
	 */
	function save()
	{
		$procedure = ($this->DrugNormativeList_id > 0)?"p_DrugNormativeList_upd":"p_DrugNormativeList_ins";
		$selectString = "
		    drugnormativelist_id as \"DrugNormativeList_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    drugnormativelist_id := :DrugNormativeList_id,
			    drugnormativelist_name := :DrugNormativeList_Name,
			    drugnormativelist_begdt := :DrugNormativeList_BegDT,
			    drugnormativelist_enddt := :DrugNormativeList_EndDT,
			    whsdocumentcostitemtype_id := :WhsDocumentCostItemType_id,
			    personregistertype_id := :PersonRegisterType_id,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"DrugNormativeList_id" => $this->DrugNormativeList_id,
			"DrugNormativeList_Name" => $this->DrugNormativeList_Name,
			"WhsDocumentCostItemType_id" => $this->WhsDocumentCostItemType_id,
			"PersonRegisterType_id" => $this->PersonRegisterType_id,
			"DrugNormativeList_BegDT" => $this->DrugNormativeList_BegDT,
			"DrugNormativeList_EndDT" => $this->DrugNormativeList_EndDT,
			"pmUser_id" => $this->pmUser_id
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных");
		}
		$result = $result->result("array");
		$this->DrugNormativeList_id = $result[0]["DrugNormativeList_id"];
		return $result;
	}

	/**
	 * Удаление
	 * @return array|bool
	 */
	function delete()
	{
		// UPD 05.02.2020
		// Перенос всех запросов в p_drugnormativelist_del()

//		$query = "
//			delete from DrugNormativeListSpecTorgLink
//			where DrugNormativeListSpec_id in (
//				select DrugNormativeListSpec_id
//				from DrugNormativeListSpec
//				where DrugNormativeList_id = :DrugNormativeList_id
//			)
//		";
//
//		$queryParams = ["DrugNormativeList_id" => $this->DrugNormativeList_id];
//		$this->db->query($query, $queryParams);
//		$query = "
//			delete from DrugNormativeListSpecFormsLink
//			where DrugNormativeListSpec_id in (
//				select DrugNormativeListSpec_id
//				from DrugNormativeListSpec
//				where DrugNormativeList_id = :DrugNormativeList_id
//			)
//		";
//		$queryParams = ["DrugNormativeList_id" => $this->DrugNormativeList_id];
//		$this->db->query($query, $queryParams);
//
//		$query = "
//			delete from DrugNormativeListSpec
//			where DrugNormativeList_id = :DrugNormativeList_id
//		";
//		$queryParams = ["DrugNormativeList_id" => $this->DrugNormativeList_id];
//		$this->db->query($query, $queryParams);

		$query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Msg\"
			from p_drugnormativelist_del(drugnormativelist_id := :DrugNormativeList_id);
		";
		$queryParams = ["DrugNormativeList_id" => $this->DrugNormativeList_id];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * Сохранение
	 * @param $data
	 * @return array
	 */
	function saveDrugNormativeListSpecFromJSON($data)
	{
		if (!empty($data["DrugNormativeList_JsonData"]) && $data["DrugNormativeList_id"] > 0) {
			ConvertFromWin1251ToUTF8($data["DrugNormativeList_JsonData"]);
			$dt = (array)json_decode($data["DrugNormativeList_JsonData"]);
			foreach ($dt as $record) {
				if ($record->state == "add" || $record->state == "edit") {
					$funcParams = [
						"DrugNormativeList_id" => $data["DrugNormativeList_id"],
						"pmUser_id" => $data["pmUser_id"]
					];
					$this->editDrugNormativeListSpec(array_merge((array)$record, $funcParams));
				} else if ($record->state == "delete") {
					if (isset($record->DrugNormativeListSpec_id) && $record->DrugNormativeListSpec_id > 0) {
						$this->deleteDrugNormativeListSpec($record->DrugNormativeListSpec_id);
					}
				}
			}
		}
		return [["Error_Code" => "", "Error_Msg" => ""]];
	}

	/**
	 * Редактирование
	 * @param $data
	 */
	function editDrugNormativeListSpec($data)
	{
		$spec_id = 0;
		if ($data["state"] == "edit") {
			$this->deleteDrugNormativeListSpec($data["DrugNormativeListSpec_id"]);
		}
		$torg_name_array = explode(",", $data["TorgNameArray"]);
		$drug_form_array = explode(",", $data["DrugFormArray"]);
		$query = "
			select
			    drugnormativelistspec_id as \"MedProductCardResource_id\",
			    error_code as \"Error_Code\",
			    error_message as \"Error_Msg\"
			from p_drugnormativelistspec_ins(
			    drugnormativelist_id := :DrugNormativeList_id,
			    drugnormativelistspecmnn_id := :DrugNormativeListSpecMNN_id,
			    drugnormativelistspec_begdt := :DrugNormativeListSpec_BegDT,
			    drugnormativelistspec_enddt := :DrugNormativeListSpec_EndDT,
			    drugnormativelistspec_isvk := (select YesNo_id from YesNo where YesNo_code = :DrugNormativeListSpec_IsVK),
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"DrugNormativeList_id" => $data["DrugNormativeList_id"],
			"DrugNormativeListSpecMNN_id" => $data["RlsActmatters_id"] > 0 ? $data["RlsActmatters_id"] : null,
			"DrugNormativeListSpec_BegDT" => $data["DrugNormativeListSpec_BegDT"] != "" ? join("-", array_reverse(explode(".", $data["DrugNormativeListSpec_BegDT"]))) : null,
			"DrugNormativeListSpec_EndDT" => $data["DrugNormativeListSpec_EndDT"] != "" ? join("-", array_reverse(explode(".", $data["DrugNormativeListSpec_EndDT"]))) : null,
			"DrugNormativeListSpec_IsVK" => $data["DrugNormativeListSpec_IsVK"] ? 1 : 0,
			"pmUser_id" => $data["pmUser_id"]
		];
		$result = $this->getFirstResultFromQuery($query, $queryParams);
		if ($result && $result > 0) {
			$spec_id = $result;
		}
		if ($spec_id > 0) {
			foreach ($torg_name_array as $torg_id) {
				if ($torg_id > 0) {
					$query = "
						select
						    error_code as \"Error_Code\",
						    error_message as \"Error_Msg\"
						from p_drugnormativelistspectorglink_ins(
						    drugnormativelistspec_id := :DrugNormativeListSpec_id,
						    drugnormativelistspectorg_id := :DrugNormativeListSpecTorg_id,
						    pmuser_id := :pmUser_id
						);
					";
					$queryParams = [
						"DrugNormativeListSpec_id" => $spec_id,
						"DrugNormativeListSpecTorg_id" => $torg_id,
						"pmUser_id" => $data["pmUser_id"]
					];
					$this->db->query($query, $queryParams);
				}
			}
			foreach ($drug_form_array as $form_id) {
				if ($form_id > 0) {
					$query = "
						select
						    error_code as \"Error_Code\",
						    error_message as \"Error_Msg\"
						from p_drugnormativelistspecformslink_ins(
						    drugnormativelistspec_id := :DrugNormativeListSpec_id,
						    drugnormativelistspecforms_id := :DrugNormativeListSpecForms_id,
						    pmuser_id := :pmUser_id
						);
					";
					$queryParams = [
						"DrugNormativeListSpec_id" => $spec_id,
						"DrugNormativeListSpecForms_id" => $form_id,
						"pmUser_id" => $data["pmUser_id"]
					];
					$this->db->query($query, $queryParams);
				}
			}
		}
	}

	/**
	 * Удаление
	 * @param $id
	 */
	function deleteDrugNormativeListSpec($id)
	{
		if ($id > 0) {
			$query = "
				delete from DrugNormativeListSpecTorgLink
				where DrugNormativeListSpec_id = :DrugNormativeListSpec_id;
				delete from DrugNormativeListSpecFormsLink
				where DrugNormativeListSpec_id = :DrugNormativeListSpec_id;
				delete from DrugNormativeListSpec
				where DrugNormativeListSpec_id = :DrugNormativeListSpec_id;
			";
			$queryParams = ["DrugNormativeListSpec_id" => $id];
			$this->db->query($query, $queryParams);
		}
	}

	/**
	 * Получение списка
	 * @param $filter
	 * @return array|bool
	 */
	function loadDrugNormativeListSpecList($filter)
	{
		$query = "
			select
				dnls.DrugNormativeListSpec_id as \"DrugNormativeListSpec_id\",
				dnls.DrugNormativeListSpecMNN_id as \"RlsActmatters_id\",
				am.RUSNAME as \"RlsActmatters_RusName\",
				am.STRONGGROUPID as \"STRONGGROUPID\",
				am.NARCOGROUPID as \"NARCOGROUPID\",
				to_char(dnls.DrugNormativeListSpec_BegDT, '{$this->dateTimeForm104}') as \"DrugNormativeListSpec_BegDT\",
				to_char(dnls.DrugNormativeListSpec_EndDT, '{$this->dateTimeForm104}') as \"DrugNormativeListSpec_EndDT\",
				to_char(dnls.DrugNormativeListSpec_BegDT, '{$this->dateTimeForm104}')||' - '||to_char(dnls.DrugNormativeListSpec_EndDT, '{$this->dateTimeForm104}') as \"DrugNormativeListSpec_DateRange\",
				atx.NAME as \"ATX_Name\",
				atx.CLSATC_ID as \"ATX_id\",
				parent_atx.NAME as \"ParentATX_Name\",
				parent_atx.CLSATC_ID as \"ParentATX_id\",
				replace((
					select string_agg(dnlsfl.DrugNormativeListSpecForms_id::varchar, ',')
					from v_DrugNormativeListSpecFormsLink dnlsfl
					where dnlsfl.DrugNormativeListSpec_id = dnls.DrugNormativeListSpec_id
				), ' ', ',') as \"DrugFormArray\",
				replace(replace((
					select string_agg(FULLNAME, ',')
					from
						v_DrugNormativeListSpecFormsLink dnlsfl
						left join rls.CLSDRUGFORMS cdf on cdf.CLSDRUGFORMS_ID = dnlsfl.DrugNormativeListSpecForms_id
					where dnlsfl.DrugNormativeListSpec_id = dnls.DrugNormativeListSpec_id
				)||',,', ',,,', ''), ',,', '') as DrugForm_NameList,
				replace((
					select string_agg(dnlstl.DrugNormativeListSpecTorg_id::varchar, ',')
					from v_DrugNormativeListSpecTorgLink dnlstl
					where dnlstl.DrugNormativeListSpec_id = dnls.DrugNormativeListSpec_id
				), ' ', ',') as TorgNameArray,
				replace(replace((
					select string_agg(coalesce(trd_code.code||', ', '')||atx.NAME||';', ';')
					from
						v_DrugNormativeListSpecTorgLink dnlstl
						left join rls.TRADENAMES trd on trd.TRADENAMES_ID = dnlstl.DrugNormativeListSpecTorg_id
						left join lateral (
							select DrugTorgCode_Code as code
							from rls.v_DrugTorgCode
							where TRADENAMES_id = trd.TRADENAMES_ID
							order by DrugTorgCode_id
				    		limit 1
						) as trd_code on true
					where dnlstl.DrugNormativeListSpec_id = dnls.DrugNormativeListSpec_id
				)||';;', ';;;', ''), ';;', '') as \"TorgName_NameList\",
				dnls_yn.YesNo_Code as \"DrugNormativeListSpec_IsVK\",
				dmc.DrugMnnCode_Code as \"DrugMnnCode_Code\"
			from
				v_DrugNormativeListSpec dnls
				left join rls.ACTMATTERS am on am.ACTMATTERS_ID = dnls.DrugNormativeListSpecMNN_id
				left join lateral (
					select
						ca.NAME,
					    ca.CLSATC_ID
					from
						rls.PREP_ACTMATTERS pam
						left join rls.PREP_ATC pca on pca.PREPID = pam.PREPID
						left join rls.CLSATC ca on ca.CLSATC_ID = pca.UNIQID
					where pam.MATTERID = dnls.DrugNormativeListSpecMNN_id
				    limit 1
				) as atx on true
				left join lateral (
					select
						ca.NAME,
					    ca.CLSATC_ID
					from rls.CLSATC ca
					where atx.CLSATC_ID > 0
					  and CLSATC_ID = dbo.GetClsAtcParentID(atx.CLSATC_ID, 5)
				    limit 1
				) as parent_atx on true
				left join lateral (
					select DrugMnnCode_Code
					from rls.v_DrugMnnCode
					where ACTMATTERS_id = am.ACTMATTERS_id
					order by DrugMnnCode_id
				    limit 1
				) as dmc on true
				left join YesNo dnls_yn on dnls_yn.YesNo_id = dnls.DrugNormativeListSpec_IsVK
			where DrugNormativeList_id = :DrugNormativeList_id
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $filter);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * Получение контекста
	 * @param $data
	 * @return array
	 */
	function getDrugNormativeListSpecContext($data)
	{
		$result = [
			"RlsActmatters_RusName" => null,
			"ATX_id" => null,
			"ATX_Name" => null,
			"ParentATX_Name" => null,
			"ParentATX_id" => null,
			"DrugForm_NameList" => null,
			"TorgName_NameList" => null,
			"DrugMnnCode_Code" => null
		];
		$filter1 = (!empty($data["DrugFormArray"])) ? $data["DrugFormArray"] : "null";
		$filter2 = (!empty($data["TorgNameArray"])) ? $data["TorgNameArray"] : "null";
		$query = "
			select
				replace(replace((
					select string_agg(FULLNAME||',', ',')
					from rls.CLSDRUGFORMS cdf
					where cdf.CLSDRUGFORMS_ID in ({$filter1})
				)||',,', ',,,', ''), ',,', '') as \"DrugForm_NameList\",
				replace(replace((
					select string_agg(coalesce(trd_code.code||', ', '')||NAME||';', ';')
					from
						rls.TRADENAMES trd
						left join lateral (
							select DrugTorgCode_Code as code
							from rls.v_DrugTorgCode
							where TRADENAMES_id = trd.TRADENAMES_ID
							order by DrugTorgCode_id
						    limit 1
						) as trd_code on true
					where trd.TRADENAMES_ID in ({$filter2})
				)||';;', ';;;', ''), ';;', '') as \"TorgName_NameList\"
		";
		$r = $this->getFirstRowFromQuery($query);
		if (is_array($r)) {
			$result = array_merge($result, $r);
		}
		$filter1 = (!empty($data["DrugFormArray"])) ? $data["DrugFormArray"] : "null";
		$filter2 = (!empty($data["TorgNameArray"])) ? $data["TorgNameArray"] : "null";
		$query = "
			select
				RUSNAME as \"RlsActmatters_RusName\",
				atx.CLSATC_ID as \"ATX_id\",
				atx.Name as \"ATX_Name\",
				parent_atx.NAME as \"ParentATX_Name\",
				parent_atx.CLSATC_ID as \"ParentATX_id\",
				replace(replace((
					select string_agg(FULLNAME||',', ',')
					from rls.CLSDRUGFORMS cdf
					where cdf.CLSDRUGFORMS_ID in ({$filter1})
				)||',,', ',,,', ''), ',,', '') as \"DrugForm_NameList\",
				replace(replace((
					select string_agg(coalesce(trd_code.code||', ', '')||NAME||';', ';')
					from
						rls.TRADENAMES trd
						left join lateral (
							select DrugTorgCode_Code as code
							from rls.v_DrugTorgCode
							where TRADENAMES_id = trd.TRADENAMES_ID
							order by DrugTorgCode_id
				    		limit 1
						) as trd_code on true
					where trd.TRADENAMES_ID in ({$filter2})
				)||';;', ';;;', ''), ';;', '') as \"TorgName_NameList\",
				dmc.DrugMnnCode_Code as \"DrugMnnCode_Code\"
			from
				rls.ACTMATTERS am
				left join lateral (
					select
						ca.NAME,
						ca.CLSATC_ID
					from
						rls.PREP_ACTMATTERS pam
						left join rls.PREP_ATC pca on pca.PREPID = pam.PREPID
						left join rls.CLSATC ca on ca.CLSATC_ID = pca.UNIQID
					where pam.MATTERID = am.ACTMATTERS_ID
					limit 1
				) as atx on true
				left join lateral (
					select
						ca.NAME,
						ca.CLSATC_ID
					from rls.CLSATC ca
					where atx.CLSATC_ID > 0
					  and CLSATC_ID = dbo.GetClsAtcParentID(atx.CLSATC_ID, 5)
					limit 1
				) as parent_atx on true
				left join lateral (
					select DrugMnnCode_Code
					from rls.v_DrugMnnCode
					where ACTMATTERS_id = am.ACTMATTERS_id
					order by DrugMnnCode_id
					limit 1
				) as dmc on true
			where ACTMATTERS_ID = :RlsActmatters_id
		";
		$queryParams = ["RlsActmatters_id" => $data["RlsActmatters_id"]];
		$r = $this->db->query($query, $queryParams);
		if (is_object($r)) {
			$r = $r->result("array");
			if (isset($r[0])) {
				$result = array_merge($result, $r[0]);
			}
		}
		return array($result);
	}

	/**
	 * Получение списка
	 * @param $data
	 * @return array|mixed
	 * @throws Exception
	 */
	function copyDrugNormativeList($data)
	{
		$query = "
			select
			    drugnormativelist_id as \"DrugNormativeList_id\",
			    error_code as \"Error_Code\",
			    error_message as \"Error_Msg\"
			from p_drugnormativelist_ins(
			    drugnormativelist_name := (select DrugNormativeList_Name from v_DrugNormativeList where DrugNormativeList_id = :DrugNormativeList_id),
			    drugnormativelist_begdt := (select DrugNormativeList_BegDT from v_DrugNormativeList where DrugNormativeList_id = :DrugNormativeList_id),
			    drugnormativelist_enddt := (select DrugNormativeList_EndDT from v_DrugNormativeList where DrugNormativeList_id = :DrugNormativeList_id),
			    personregistertype_id := (select PersonRegisterType_id from v_DrugNormativeList where DrugNormativeList_id = :DrugNormativeList_id),
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"DrugNormativeList_id" => $data["DrugNormativeList_id"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных");
		}
		$spec_count = 0;
		$result = $result->result("array");
		$new_list_id = $result[0]["DrugNormativeList_id"];
		$query = "
			select
				DrugNormativeListSpec_id as \"DrugNormativeListSpec_id\",
				DrugNormativeListSpecMNN_id as \"DrugNormativeListSpecMNN_id\",
				DrugNormativeListSpec_BegDT as \"DrugNormativeListSpec_BegDT\",
				DrugNormativeListSpec_EndDT as \"DrugNormativeListSpec_EndDT\",
				DrugNormativeListSpec_IsVK as \"DrugNormativeListSpec_IsVK\"
			from v_DrugNormativeListSpec
			where DrugNormativeList_id = :DrugNormativeList_id;
		";
		$queryParams = ["DrugNormativeList_id" => $data["DrugNormativeList_id"]];
		$result = $this->db->query($query, $queryParams);
		if (is_object($result)) {
			$result = $result->result("array");
			foreach ($result as $spec_data) {
				$query = "
					select
					    drugnormativelistspec_id as \"DrugNormativeListSpec_id\",
					    error_code as \"Error_Code\",
					    error_message as \"Error_Msg\"
					from p_drugnormativelistspec_ins(
					    drugnormativelist_id := :DrugNormativeList_id,
					    drugnormativelistspecmnn_id := :DrugNormativeListSpecMNN_id,
					    drugnormativelistspec_begdt := :DrugNormativeListSpec_BegDT,
					    drugnormativelistspec_enddt := :DrugNormativeListSpec_EndDT,
					    drugnormativelistspec_isvk := :DrugNormativeListSpec_IsVK,
					    pmuser_id := :pmUser_id
					);
				";
				$queryParams = [
					"DrugNormativeList_id" => $new_list_id,
					"DrugNormativeListSpecMNN_id" => $spec_data["DrugNormativeListSpecMNN_id"],
					"DrugNormativeListSpec_BegDT" => $spec_data["DrugNormativeListSpec_BegDT"],
					"DrugNormativeListSpec_EndDT" => $spec_data["DrugNormativeListSpec_EndDT"],
					"DrugNormativeListSpec_IsVK" => $spec_data["DrugNormativeListSpec_IsVK"],
					"pmUser_id" => $data["pmUser_id"]
				];
				$r = $this->getFirstResultFromQuery($query, $queryParams);
				if ($r > 0) {
					$spec_count++;
					$query = "
						insert into DrugNormativeListSpecFormsLink (
							DrugNormativeListSpec_id,
							DrugNormativeListSpecForms_id,
							pmUser_insID,
							pmUser_updID,
							DrugNormativeListSpecFormsLink_insDT,
							DrugNormativeListSpecFormsLink_updDT
						)
						select
							:NewDrugNormativeListSpec_id,
							DrugNormativeListSpecForms_id,
							:pmUser_id,
							:pmUser_id,
							dbo.tzGetDate(),
						    dbo.tzGetDate()
						from v_DrugNormativeListSpecFormsLink
						where DrugNormativeListSpec_id = :OldDrugNormativeListSpec_id
					";
					$queryParams = [
						"NewDrugNormativeListSpec_id" => $r,
						"OldDrugNormativeListSpec_id" => $spec_data["DrugNormativeListSpec_id"],
						"pmUser_id" => $data["pmUser_id"]
					];
					$this->db->query($query, $queryParams);
					$query = "
						insert into DrugNormativeListSpecTorgLink (
							DrugNormativeListSpec_id,
							DrugNormativeListSpecTorg_id,
							pmUser_insID,
							pmUser_updID,
							DrugNormativeListSpecTorgLink_insDT,
							DrugNormativeListSpecTorgLink_updDT
						)
						select
							:NewDrugNormativeListSpec_id,
							DrugNormativeListSpecTorg_id,
							:pmUser_id,
							:pmUser_id,
							dbo.tzGetDate(),
						    dbo.tzGetDate()
						from v_DrugNormativeListSpecTorgLink
						where DrugNormativeListSpec_id = :OldDrugNormativeListSpec_id;
					";
					$queryParams = [
						"NewDrugNormativeListSpec_id" => $r,
						"OldDrugNormativeListSpec_id" => $spec_data["DrugNormativeListSpec_id"],
						"pmUser_id" => $data["pmUser_id"]
					];
					$this->db->query($query, $queryParams);
				}
			}
		}
		$result = [[
			"DrugNormativeListSpec_count" => $spec_count,
			"DrugNormativeList_id" => $new_list_id,
			"Error_Code" => null,
			"Error_Msg" => null
		]];
		return $result;
	}

	/**
	 * Получение списка
	 * @param $data
	 * @return array|bool
	 */
	function loadDrugFormsCombo($data)
	{
		$where = [];
		$where[] = "cdf.FULLNAME != ''";
		if ($data["RlsClsdrugforms_id"] > 0) {
			$where[] = "cdf.CLSDRUGFORMS_ID = :RlsClsdrugforms_id";
		} else {
			if (strlen($data["query"]) > 0) {
				$data["query"] = "%{$data["query"]}%";
				$where[] = "cdf.FULLNAME ilike :query";
			}
			if ($data["RlsActmatters_id"]) {
				$where[] = "
					cdf.CLSDRUGFORMS_ID in (
						select p.DRUGFORMID
						from
							rls.PREP_ACTMATTERS pa
							left join rls.PREP p on p.Prep_id = pa.PREPID
						where pa.MATTERID = :RlsActmatters_id
					)
				";
			}
		}
		$where = implode(" and ", $where);
		$query = "
			select distinct
				cdf.CLSDRUGFORMS_ID as \"RlsClsdrugforms_id\",
				cdf.FULLNAME as \"RlsClsdrugforms_Name\"
			from rls.Clsdrugforms as cdf
			where {$where}
			order by cdf.FULLNAME
			limit 500
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $data);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * Получение списка
	 * @param $data
	 * @return array|bool
	 */
	function loadTradenamesCombo($data)
	{
		$where = [];
		$where[] = "t.NAME != ''";
		if ($data["RlsTradenames_id"] > 0) {
			$where[] = "t.TRADENAMES_ID = :RlsTradenames_id";
		} else {
			if (strlen($data["query"]) > 0) {
				$data["query"] = "%{$data["query"]}%";
				$where[] = "t.NAME like :query";
			}
			if ($data["RlsActmatters_id"] > 0) {
				$filter = (isset($data["DrugFormList"]) && !empty($data["DrugFormList"]))? "and p.DRUGFORMID in ({$data["DrugFormList"]})" : "";
				$where[] = "
					t.TRADENAMES_ID in (
						select p.TRADENAMEID
						from
							rls.PREP_ACTMATTERS pa
							left join rls.PREP p on p.Prep_id = pa.PREPID
						where pa.MATTERID = :RlsActmatters_id
						  {$filter}
				)";
			} else {
				$filter = (isset($data["DrugFormList"]) && !empty($data["DrugFormList"])) ? "and p.DRUGFORMID in ({$data["DrugFormList"]})" : "";
				$where[] = "t.TRADENAMES_ID in (
					select p.TRADENAMEID
					from rls.PREP p
					where p.Prep_id not in (
							select PREPID from rls.PREP_ACTMATTERS
						)
					  {$filter}
				)";
			}
		}
		$where = implode(" and ", $where);
		$query = "
			select distinct
				t.TRADENAMES_ID as \"RlsTradenames_id\",
				t.NAME as \"RlsTradenames_Name\"
			from rls.TRADENAMES t
			where {$where}
			order by t.NAME
			limit 500
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $data);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * Получение списка по Drug_id
	 * @param $data
	 * @return array|bool
	 */
	function loadListByRlsDrug($data)
	{
		$params = ["Drug_id" => $data["Drug_id"]];
		$query = "
			select
				DNL.DrugNormativeList_id as \"DrugNormativeList_id\",
				DNL.DrugNormativeList_Name as \"DrugNormativeList_Name\",
				MT.PersonRegisterType_id as \"PersonRegisterType_id\",
				MT.PersonRegisterType_Code as \"PersonRegisterType_Code\",
				MT.PersonRegisterType_Name as \"PersonRegisterType_Name\",
				to_char(DNL.DrugNormativeList_BegDT, '{$this->dateTimeForm104}') as \"DrugNormativeList_begDate\"
			from
				v_DrugNormativeList DNL
				left join v_PersonRegisterType MT on MT.PersonRegisterType_id = DNL.PersonRegisterType_id
				left join lateral (
					select t.DrugNormativeList_id
					from
						rls.v_Drug D
						inner join v_DrugNormativeListSpec t on t.DrugNormativeList_id = DNL.DrugNormativeList_id
						inner join rls.v_DrugComplexMnn DCM on DCM.DrugComplexMnn_id = D.DrugComplexMnn_id
						inner join rls.v_ACTMATTERS AM on AM.ACTMATTERS_ID = DCM.ActMatters_id
						left join v_DrugNormativeListSpecTorgLink dnlstl on dnlstl.DrugNormativeListSpec_id = t.DrugNormativeListSpec_id
						left join v_DrugNormativeListSpecFormsLink dnlsfl on dnlsfl.DrugNormativeListSpec_id = t.DrugNormativeListSpec_id
						left join rls.v_CLSDRUGFORMS CDF on CDF.CLSDRUGFORMS_ID = DCM.CLSDRUGFORMS_ID
						left join rls.v_Prep P on P.Prep_id = D.DrugPrep_id
						left join rls.v_TRADENAMES TN on TN.TRADENAMES_ID = P.TRADENAMEID
					where D.Drug_id = :Drug_id
					  and t.DrugNormativeListSpecMNN_id = AM.ACTMATTERS_ID
					  and (dnlsfl.DrugNormativeListSpecForms_id is null or dnlsfl.DrugNormativeListSpecForms_id = CDF.CLSDRUGFORMS_ID)
					  and (dnlstl.DrugNormativeListSpecTorg_id is null or dnlstl.DrugNormativeListSpecTorg_id = TN.TRADENAMES_ID)
					  and (t.DrugNormativeListSpec_EndDT is null or t.DrugNormativeListSpec_EndDT > dbo.tzGetDate())
					limit 1
				) as DNLS on true
			where
				DNLS.DrugNormativeList_id is not null
				and (DNL.DrugNormativeList_EndDT is null or DNL.DrugNormativeList_EndDT > dbo.tzGetDate())
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $params);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * Получение Типа регистра по Статье расходов
	 * @param $data
	 * @return array|bool
	 */
	function getPersonRegisterTypeByWhsDocumentCostItemType($data) {
		$params = array('WhsDocumentCostItemType_id' => $data['WhsDocumentCostItemType_id']);

		$query = "select PersonRegisterType_id as \"PersonRegisterType_id\" from dbo.WhsDocumentCostItemType where WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id";

		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $params);
		return (is_object($result)) ? $result->result("array") : false;
	}
}
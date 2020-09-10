<?php
defined("BASEPATH") or die ("No direct script access allowed");
/**
 * EvnPLDispOrp_model - модель для работы с талонами по доп. диспансеризации
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @version      май 2010
 *
 * @property CI_DB_driver $db
 */

class EvnPLDispOrp_model extends CI_Model
{
	public $dateTimeForm104 = "DD.MM.YYYY";
	public $dateTimeForm108 = "HH24:MI:SS";
	public $dateTimeForm112 = "YYYYMMDD";
	public $dateTimeForm120 = "YYYY-MM-DD HH24:MI:SS";

	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Удаление
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function deleteEvnPLDispOrp($data)
	{
		$query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from p_evnpldisporp_del(
			    evnpldisporp_id := 0,
			    pmuser_id := 0
			);
		";
		$queryParams = [
			"EvnPLDispOrp_id" => $data["EvnPLDispOrp_id"],
			"pmUser_id" => $data["pmUser_id"]
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных (удаление талона ДД)");
		}
		return $result->result("array");
	}

	/**
	 * Загрузка формы
	 * @param $data
	 * @return array|bool
	 */
	function loadEvnPLDispOrpEditForm($data)
	{
		$query = "
			select
				EPLDD.EvnPLDispOrp_id as \"EvnPLDispOrp_id\",
				EPLDD.EvnPLDispOrp_IsFinish as \"EvnPLDispOrp_IsFinish\",
				to_char(EPLDD.EvnPLDispOrp_setDate, '{$this->dateTimeForm104}') as \"EvnPLDispOrp_setDate\",
				EPLDD.AttachType_id as \"AttachType_id\",
				EPLDD.Lpu_aid as \"Lpu_aid\",
				EPLDD.PersonEvn_id as \"PersonEvn_id\"
			from v_EvnPLDispOrp EPLDD
			where EPLDD.EvnPLDispOrp_id = :EvnPLDispOrp_id
			  and EPLDD.Lpu_id = :Lpu_id
			limit 1
		";
		$queryParams = [
			"EvnPLDispOrp_id" => $data["EvnPLDispOrp_id"],
			"Lpu_id" => $data["Lpu_id"]
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Получение полей
	 * @param $data
	 * @return array|bool
	 */
	function getEvnPLDispOrpFields($data)
	{
		$query = "
			select
				rtrim(lp.Lpu_Name) as \"Lpu_Name\",
				rtrim(coalesce(lp1.Lpu_Name, '')) as \"Lpu_AName\",
				rtrim(coalesce(addr1.Address_Address, '')) as \"Lpu_AAddress\",
				rtrim(lp.Lpu_OGRN) as \"Lpu_OGRN\",
				coalesce(pc.PersonCard_Code, '') as \"PersonCard_Code\",
				ps.Person_SurName||' '||ps.Person_FirName||' '||coalesce(ps.Person_SecName, '') as \"Person_FIO\",
				sx.Sex_Name as \"Sex_Name\",
				coalesce(osmo.OrgSMO_Nick, '') as \"OrgSMO_Nick\",
				coalesce(case when pls.PolisType_id = 4 then '' else pls.Polis_Ser end, '') as \"Polis_Ser\",
				coalesce(case when pls.PolisType_id = 4 then ps.Person_EdNum else pls.Polis_Num end, '') as \"Polis_Num\",
				coalesce(osmo.OrgSMO_Name, '') as \"OrgSMO_Name\",
				to_char(ps.Person_BirthDay, '{$this->dateTimeForm104}') as \"Person_BirthDay\",
				coalesce(addr.Address_Address, '') as \"Person_Address\",
				jborg.Org_Nick as \"Org_Nick\",
				atype.AttachType_Name as \"AttachType_Name\",
				to_char(EPLDD.EvnPLDispOrp_disDate, '{$this->dateTimeForm104}') as \"EvnPLDispOrp_disDate\"
			from
				v_EvnPLDispOrp EPLDD
				inner join v_Lpu lp on lp.Lpu_id = EPLDD.Lpu_id
				left join v_Lpu lp1 on lp1.Lpu_id = EPLDD.Lpu_aid
				left join Address addr1 on addr1.Address_id = lp1.UAddress_id
				left join v_PersonCard pc on pc.Person_id = EPLDD.Person_id and pc.LpuAttachType_id = 1
				inner join v_PersonState ps on ps.Person_id = EPLDD.Person_id
				inner join Sex sx on sx.Sex_id = ps.Sex_id
				left join Polis pls on pls.Polis_id = ps.Polis_id
				left join v_OrgSmo osmo on osmo.OrgSmo_id = pls.OrgSmo_id
				left join Address addr on addr.Address_id = ps.PAddress_id
				left join Job jb on jb.Job_id = ps.Job_id
				left join Org jborg on jborg.Org_id = jb.Org_id
				left join AttachType atype on atype.AttachType_id = EPLDD.AttachType_id
			where EPLDD.EvnPLDispOrp_id = :EvnPLDispOrp_id
				and EPLDD.Lpu_id = :Lpu_id
			limit 1
		";
		$queryParams = [
			"EvnPLDispOrp_id" => $data["EvnPLDispOrp_id"],
			"Lpu_id" => $data["Lpu_id"]
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Получение списка осмотров врача-специалиста в талоне по ДД
	 * @param $data
	 * @return array|bool
	 */
	function loadEvnVizitDispOrpGrid($data)
	{
		$query = "
			select
				EVZDD.EvnVizitDispOrp_id as \"EvnVizitDispOrp_id\",
				to_char(EVZDD.EvnVizitDispOrp_setDate, '{$this->dateTimeForm104}') as \"EvnVizitDispOrp_setDate\",
				RTRIM(LS.LpuSection_Name) as \"LpuSection_Name\",
				RTRIM(MP.Person_Fio) as \"MedPersonal_Fio\",
				RTRIM(DDS.OrpDispSpec_Name) as \"OrpDispSpec_Name\",
				RTRIM(D.Diag_Code) as \"Diag_Code\",
				EVZDD.MedPersonal_id as \"MedPersonal_id\",
				EVZDD.OrpDispSpec_id as \"OrpDispSpec_id\",
				EVZDD.LpuSection_id as \"LpuSection_id\",
				EVZDD.Diag_id as \"Diag_id\",
				EVZDD.DopDispDiagType_id as \"DopDispDiagType_id\",
				EVZDD.DeseaseStage_id as \"DeseaseStage_id\",
				EVZDD.HealthKind_id as \"HealthKind_id\",
				EVZDD.EvnVizitDispOrp_IsSanKur as \"EvnVizitDispOrp_IsSanKur\",
				EVZDD.EvnVizitDispOrp_IsOut as \"EvnVizitDispOrp_IsOut\",
				EVZDD.DopDispAlien_id,
				1 as \"Record_Status\"
			from
				v_EvnVizitDispOrp EVZDD
				left join LpuSection LS on LS.LpuSection_id = EVZDD.LpuSection_id
				left join v_MedPersonal MP on MP.MedPersonal_id = EVZDD.MedPersonal_id
				left join OrpDispSpec DDS on DDS.OrpDispSpec_id = EVZDD.OrpDispSpec_id
				left join Diag D on D.Diag_id = EVZDD.Diag_id
			where EVZDD.EvnVizitDispOrp_pid = :EvnPLDispOrp_id
		";
		$queryParams = [
			"EvnPLDispOrp_id" => $data['EvnPLDispOrp_id']
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Получение списка осмотров врача-специалиста в талоне по ДД
	 * @param $data
	 * @return array|bool
	 */
	function loadEvnVizitDispOrpData($data)
	{
		$query = "
			select
				EVZDD.EvnVizitDispOrp_id as \"EvnVizitDispOrp_id\",
				to_char(EVZDD.EvnVizitDispOrp_setDate, '{$this->dateTimeForm104}') as \"EvnVizitDispOrp_setDate\",
				RTRIM(MP.Person_Fio) as \"MedPersonal_Fio\",
				RTRIM(coalesce(MP.MedPersonal_TabCode, '')) as \"MedPersonal_TabCode\",
				RTRIM(DDS.OrpDispSpec_Name) as \"OrpDispSpec_Name\",
				RTRIM(D.Diag_Code) as \"Diag_Code\",
				EVZDD.MedPersonal_id as \"MedPersonal_id\",
				EVZDD.OrpDispSpec_id as \"OrpDispSpec_id\",
				EVZDD.LpuSection_id as \"LpuSection_id\",
				EVZDD.Diag_id as \"Diag_id\",
				EVZDD.DopDispDiagType_id as \"DopDispDiagType_id\",
				EVZDD.DeseaseStage_id as \"DeseaseStage_id\",
				EVZDD.HealthKind_id as \"HealthKind_id\",
				EVZDD.EvnVizitDispOrp_IsSanKur as \"EvnVizitDispOrp_IsSanKur\",
				EVZDD.EvnVizitDispOrp_IsOut as \"EvnVizitDispOrp_IsOut\",
				EVZDD.DopDispAlien_id as \"DopDispAlien_id\",
				1 as \"Record_Status\"
			from v_EvnVizitDispOrp EVZDD
				left join LpuSection LS on LS.LpuSection_id = EVZDD.LpuSection_id
				left join v_MedPersonal MP on MP.MedPersonal_id = EVZDD.MedPersonal_id
				left join OrpDispSpec DDS on DDS.OrpDispSpec_id = EVZDD.OrpDispSpec_id
				left join Diag D on D.Diag_id = EVZDD.Diag_id
			where EVZDD.EvnVizitDispOrp_pid = :EvnPLDispOrp_id
		";
		$queryParams = ["EvnPLDispOrp_id" => $data["EvnPLDispOrp_id"]];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Получение списка исследований в талоне по ДД
	 * @param $data
	 * @return array|bool
	 */
	function loadEvnUslugaDispOrpGrid($data)
	{
		$query = "
			select
				EUDD.EvnUslugaDispOrp_id as \"EvnUslugaDispOrp_id\",
				to_char(EUDD.EvnUslugaDispOrp_setDate, '{$this->dateTimeForm104}') as \"EvnUslugaDispOrp_setDate\",
				to_char(EUDD.EvnUslugaDispOrp_didDate, '{$this->dateTimeForm104}') as \"EvnUslugaDispOrp_didDate\",
				EUDD.OrpDispUslugaType_id as \"OrpDispUslugaType_id\",
				RTRIM(DDUT.OrpDispUslugaType_Name) as \"OrpDispUslugaType_Name\",
				EUDD.LpuSection_uid as \"LpuSection_id\",
				RTRIM(LS.LpuSection_Name) as \"LpuSection_Name\",
				EUDD.MedPersonal_id as \"MedPersonal_id\",
				RTRIM(MP.Person_Fio) as \"MedPersonal_Fio\",
				EUDD.Usluga_id as \"Usluga_id\",
				RTRIM(U.Usluga_Name) as \"Usluga_Name\",
				RTRIM(U.Usluga_Code) as \"Usluga_Code\",
				EUDD.ExaminationPlace_id as \"ExaminationPlace_id\",
				1 as \"Record_Status\"
			from v_EvnUslugaDispOrp EUDD
				left join OrpDispUslugaType DDUT on DDUT.OrpDispUslugaType_id = EUDD.OrpDispUslugaType_id
				left join v_LpuSection LS on LS.LpuSection_id = EUDD.LpuSection_uid
				left join v_MedPersonal MP on MP.MedPersonal_id = EUDD.MedPersonal_id
				left join v_Usluga U on U.Usluga_id = EUDD.Usluga_id
			where EUDD.EvnUslugaDispOrp_pid = :EvnPLDispOrp_id
		";
		$queryParams = ["EvnPLDispOrp_id" => $data["EvnPLDispOrp_id"]];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Получение списка исследований в талоне по ДД
	 * @param $data
	 * @return array|bool
	 */
	function loadEvnUslugaDispOrpData($data)
	{
		$query = "
			select
				EUDD.EvnUslugaDispOrp_id as \"EvnUslugaDispOrp_id\",
				to_char(EUDD.EvnUslugaDispOrp_setDate, '{$this->dateTimeForm104}') as \"EvnUslugaDispOrp_setDate\",
				to_char(EUDD.EvnUslugaDispOrp_didDate, '{$this->dateTimeForm104}') as \"EvnUslugaDispOrp_didDate\",
				EUDD.OrpDispUslugaType_id as \"OrpDispUslugaType_id\"
			from v_EvnUslugaDispOrp EUDD
			where EUDD.EvnUslugaDispOrp_pid = :EvnPLDispOrp_id
		";
		$queryParams = ["EvnPLDispOrp_id" => $data["EvnPLDispOrp_id"]];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Загрузка списка
	 * @param $data
	 * @return array|bool
	 */
	function loadEvnPLDispOrpStreamList($data)
	{
		$queryParams = ["pmUser_id" => $data["pmUser_id"]];
		$filter = "EPL.pmUser_insID = :pmUser_id";
		if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $data["begTime"])) {
			$filter .= " and EPL.EvnPL_insDT >= :date_time";
			$queryParams["date_time"] = $data["begDate"] . " " . $data["begTime"];
		}
		if (isset($data["Lpu_id"])) {
			$filter .= " and EPL.Lpu_id = :Lpu_id";
			$queryParams["Lpu_id"] = $data["Lpu_id"];
		}
		$query = "
        	select distinct
				EPL.EvnPL_id as \"EvnPL_id\",
				EPL.Person_id as \"Person_id\",
				EPL.Server_id as \"Server_id\",
				EPL.PersonEvn_id as \"PersonEvn_id\",
				RTRIM(EPL.EvnPL_NumCard) as \"EvnPL_NumCard\",
				RTRIM(PS.Person_Surname) as \"Person_Surname\",
				RTRIM(PS.Person_Firname) as \"Person_Firname\",
				RTRIM(PS.Person_Secname) as \"Person_Secname\",
				to_char(PS.Person_Birthday, '{$this->dateTimeForm104}') as \"Person_Birthday\",
				to_char(EPL.EvnPL_setDate, '{$this->dateTimeForm104}') as \"EvnPL_setDate\",
				to_char(EPL.EvnPL_disDate, '{$this->dateTimeForm104}') as \"EvnPL_disDate\",
				EPL.EvnPL_VizitCount as \"EvnPL_VizitCount\",
				IsFinish.YesNo_Name as \"EvnPL_IsFinish\"
			from
				v_EvnPL EPL
				inner join v_PersonState PS on PS.Person_id = EPL.Person_id
				left join YesNo IsFinish on IsFinish.YesNo_id = EPL.EvnPL_IsFinish
			where {$filter}
			order by EPL.EvnPL_id desc
			limit 100
    	";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Загрузка списка
	 * @param $data
	 * @return array|bool
	 */
	function loadEvnVizitPLDispOrpGrid($data)
	{
		$query = "
			select
				EVPL.EvnVizitPL_id as \"EvnVizitPL_id\",
				EVPL.LpuSection_id as \"LpuSection_id\",
				EVPL.MedPersonal_id as \"MedPersonal_id\",
				EVPL.MedPersonal_sid as \"MedPersonal_sid\",
				EVPL.PayType_id as \"PayType_id\",
				EVPL.ProfGoal_id as \"ProfGoal_id\",
				EVPL.ServiceType_id as \"ServiceType_id\",
				EVPL.VizitType_id as \"VizitType_id\",
				EVPL.EvnVizitPL_Time as \"EvnVizitPL_Time\",
				to_char(EVPL.EvnVizitPL_setDate, '{$this->dateTimeForm104}') as \"EvnVizitPL_setDate\",
				EVPL.EvnVizitPL_setTime as \"EvnVizitPL_setTime\",
				RTrim(LS.LpuSection_Name) as \"LpuSection_Name\",
				RTrim(MP.Person_Fio) as \"MedPersonal_Fio\",
				RTrim(PT.PayType_Name) as \"PayType_Name\",
				RTrim(ST.ServiceType_Name) as \"ServiceType_Name\",
				RTrim(VT.VizitType_Name) as \"VizitType_Name\",
				1 as \"Record_Status\"
			from v_EvnVizitPL EVPL
				left join LpuSection LS on LS.LpuSection_id = EVPL.LpuSection_id
				left join v_MedPersonal MP on MP.MedPersonal_id = EVPL.MedPersonal_id
				left join PayType PT on PT.PayType_id = EVPL.PayType_id
				left join ServiceType ST on ST.ServiceType_id = EVPL.ServiceType_id
				left join VizitType VT on VT.VizitType_id = EVPL.VizitType_id
			where EVPL.EvnVizitPL_pid = :EvnPL_id
		";
		$queryParams = ["EvnPL_id" => $data["EvnPL_id"]];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Проверка того, что человек есть в регистре по ДД и у него заведены все необходимые данные
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function checkPersonData($data)
	{
		$query = "
			select
				Sex_id as \"Sex_id\",
				SocStatus_id as \"SocStatus_id\",
				ps.UAddress_id as \"Person_UAddress_id\",
				ps.Polis_Ser as \"Polis_Ser\",
				ps.Polis_Num as \"Polis_Num\",
				o.Org_Name as \"Org_Name\",
				o.Org_INN as \"Org_INN\",
				o.Org_OGRN as \"Org_OGRN\",
				o.UAddress_id as \"Org_UAddress_id\",
				os.OrgSmo_Name as \"OrgSmo_Name\",
				(
				    age(PS.Person_Birthday, tzgetdate()) +
					case when date_part('month', ps.Person_Birthday) > date_part('month', tzgetdate()) or (date_part('month', ps.Person_Birthday) = date_part('month', tzgetdate()) and date_part('day', ps.Person_Birthday) > date_part('day', tzgetdate()))
						then -1
					    else 0
					end
				) as \"Person_Age\",
				to_char(PS.Person_Birthday, '{$this->dateTimeForm104}') as \"Person_Birthday\"
			from
				v_persondisporp pdd
				left join v_PersonState ps on ps.Person_id=pdd.Person_id
				left join v_Job j on j.Job_id=ps.Job_id
				left join v_Org o on o.Org_id=j.Org_id
				left join v_Polis pol on pol.Polis_id=ps.Polis_id
				left join v_OrgSmo os on os.OrgSmo_id=pol.OrgSmo_id
			where pdd.Person_id = :Person_id
		";
		$queryParams = ["Person_id" => $data["Person_id"]];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		$response = $result->result("array");

		if (!is_array($response) || count($response) == 0) {
			throw new Exception("Этого человека нет в регистре по диспансеризации детей-сирот!");
		}
		$error = [];
		if (ArrayVal($response[0], "Sex_id") == "") {
			$errors[] = "Не заполнен Пол";
		}
		if (ArrayVal($response[0], "SocStatus_id") == "") {
			$errors[] = "Не заполнен Соц. статус";
		}
		if (ArrayVal($response[0], "Person_UAddress_id") == "") {
			$errors[] = "Не заполнен Адрес по месту регистрации";
		}
		if (ArrayVal($response[0], "Polis_Num") == "") {
			$errors[] = "Не заполнен Номер полиса";
		}
		if (ArrayVal($response[0], "Polis_Ser") == "") {
			$errors[] = "Не заполнена Серия полиса";
		}
		if (ArrayVal($response[0], "OrgSmo_id") == "") {
			$errors[] = "Не заполнена Организация, выдавшая полис";
		}
		if (ArrayVal($response[0], "Org_UAddress_id") == "") {
			$errors[] = "Не заполнен Адрес места работы";
		}
		if (ArrayVal($response[0], "Org_INN") == "") {
			$errors[] = "Не заполнен ИНН места работы";
		}
		if (ArrayVal($response[0], "Org_OGRN") == "") {
			$errors[] = "Не заполнена ОГРН организации, в которой содержится ребенок";
		}
		if (count($error) > 0) { // есть ошибки в заведении
			$errstr = implode("<br/>", $errors);
			throw new Exception("Проверьте полноту заведения данных у человека!<br/>" . $errstr);
		}
		return [
			"Ok",
			ArrayVal($response[0], "Sex_id"),
			ArrayVal($response[0], "Person_Age"),
			ArrayVal($response[0], "Person_Birthday")
		];
	}

	/**
	 * Сохранение
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function saveEvnPLDispOrp($data)
	{
		// Проверяем что человек находится в регистре по ДД и у него заведены все необходимые данные
		$checkResult = $this->checkPersonData($data);
		if ($checkResult[0] != "Ok") {
			return $checkResult;
		}
		// поверяем, есть ли все обязательные осмотры и исследования, если проставляется законченность случая								
		$err_str = "";
		if (isset($data["EvnPLDispOrp_IsFinish"]) && $data["EvnPLDispOrp_IsFinish"] == 2) {
			if ($data["EvnVizitDispOrp"]) {
				$test_vizits = $data["EvnVizitDispOrp"];
			} else {
				$test_vizits = [];
			}
			if ($data["EvnUslugaDispOrp"]) {
				$test_usluga = $data["EvnUslugaDispOrp"];
			} else {
				$test_usluga = [];
			}

			if (isset($data["EvnPLDispOrp_id"])) {
				$sel = $this->loadEvnVizitDispOrpGrid($data);
				if (count($sel) > 0) {
					foreach ($sel as $record) {
						$test_vizits[] = $record;
					}
				}

				$sel = $this->loadEvnUslugaDispOrpGrid($data);
				if (count($sel) > 0) {
					foreach ($sel as $record) {
						$test_usluga[] = $record;
					}
				}
			}
			// осмотры
			// массив обязательных осмотров
			$vizits_array = [
				"1" => "Педиатрия",
				"2" => "Неврология",
				"3" => "Офтальмология",
				"4" => "Детская хирургия",
				"5" => "Отоларингология",
				"7" => "Стоматология детская",
				"8" => "Ортопедия-травматология"
			];

			$deleted_vizits = [];
			$ped_time = time();
			$pers_time = strtotime($checkResult[3]);
			foreach ($test_vizits as $key => $record) {
				if ($record["OrpDispSpec_id"] == 1) {
					$ped_time = strtotime($record["EvnVizitDispOrp_setDate"]);
				}
				if ($record["Record_Status"] == 3) {
					$deleted_vizits[] = $record["EvnVizitDispOrp_id"];
				}
			}
			if ($checkResult[1] == 2) {
				$vizits_array["6"] = "Гинекология";
			} elseif (strtotime("+5 year", $pers_time) < $ped_time) {
				$vizits_array["10"] = "Детская урология-андрология";
			}
			if (strtotime("+3 year", $pers_time) < $ped_time) {
				$vizits_array["9"] = "Психиатрия";
			}
			if (strtotime("+5 year", $pers_time) < $ped_time) {
				$vizits_array["9"] = "Детская эндокринология";
			}
			if ($test_vizits) {
				$deleted_vizits = [];
				foreach ($test_vizits as $key => $record) {
					if ($record["Record_Status"] != 3 && isset($vizits_array[(string)$record["OrpDispSpec_id"]]) && !in_array($record["EvnVizitDispOrp_id"], $deleted_vizits)) {
						unset($vizits_array[(string)$record["OrpDispSpec_id"]]);
					}
					if ($record["Record_Status"] == 3) {
						$deleted_vizits[] = $record["EvnVizitDispOrp_id"];
					}
				}
			}
			if (count($vizits_array) > 0) {
				$err_str = "<p>В талоне отсутствуют осмотры следующих специалистов:</p>";
				foreach ($vizits_array as $value) {
					$err_str .= "<p>" . $value . "</p>";
				}
			}
			// исследования
			// массив обязательных исследований
			$usluga_array = [
				"1" => "02000101	Общий анализ крови",
				"2" => "02000130	Общий анализ мочи",
				"3" => "02001101	Экг старше 15 лет",
				"4" => "02001301	Узи печени",
				"5" => "02001304	Узи почки с надпочечниками",
				"6" => "02001311	УЗИ печени и желчного пузыря"
			];
			if ($checkResult[2] < 1) {
				$usluga_array["7"] = "02001311	Узи сустава";
			}
			if ($test_usluga) {
				$deleted_usluga = [];
				foreach ($test_usluga as $key => $record) {
					if ($record["Record_Status"] != 3 && isset($usluga_array[(string)$record["OrpDispUslugaType_id"]]) && !in_array($record["EvnUslugaDispOrp_id"], $deleted_usluga)) {
						unset($usluga_array[(string)$record["OrpDispUslugaType_id"]]);
					}
					if ($record["Record_Status"] == 3) {
						$deleted_usluga[] = $record["EvnUslugaDispOrp_id"];
					}
				}
			}
			if (count($usluga_array) > 0) {
				$err_str .= "<p>&nbsp;</p><p>В талоне отсутствуют следующие исследования:</p>";
				foreach ($usluga_array as $value) {
					$err_str .= "<p>" . $value . "</p>";
				}
			}
			if ($err_str != "") {
				throw new Exception("<p>Случай не может быть закончен!</p><p>&nbsp;</p>{$err_str}");
			}
		}
		if (!isset($data["EvnPLDispOrp_id"])) {
			$data["EvnPLDispOrp_setDT"] = date("Y-m-d");
			$data["EvnPLDispOrp_disDT"] = null;
			$data["EvnPLDispOrp_didDT"] = null;
			$data["EvnPLDispOrp_VizitCount"] = 0;
			$procedure = "p_EvnPLDispOrp_ins";
		} else {
			// достаем дату начала, дату окончания, количество посещений
			$query = "
				select
					to_char(EvnPLDispOrp_setDT, '{$this->dateTimeForm112}') as \"EvnPLDispOrp_setDT\",
					to_char(EvnPLDispOrp_disDT, '{$this->dateTimeForm112}') as \"EvnPLDispOrp_disDT\",
					to_char(EvnPLDispOrp_didDT, '{$this->dateTimeForm112}') as \"EvnPLDispOrp_didDT\",					
					EvnPLDispOrp_VizitCount as \"EvnPLDispOrp_VizitCount\"
				from v_EvnPLDispOrp
				where EvnPLDispOrp_id = :EvnPLDispOrp_id
			";
			$queryParams = ["EvnPLDispOrp_id" => $data["EvnPLDispOrp_id"]];
			/**@var CI_DB_result $result */
			$result = $this->db->query($query, $queryParams);
			$response = $result->result("array");
			$data["EvnPLDispOrp_setDT"] = $response[0]["EvnPLDispOrp_setDT"];
			$data["EvnPLDispOrp_disDT"] = $response[0]["EvnPLDispOrp_disDT"];
			$data["EvnPLDispOrp_didDT"] = $response[0]["EvnPLDispOrp_didDT"];
			$data["EvnPLDispOrp_VizitCount"] = $response[0]["EvnPLDispOrp_VizitCount"];
			$procedure = "p_EvnPLDispOrp_upd";
		}
		$selectString = "
		    evnpldisporp_id as \"EvnPLDispOrp_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Message\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    evnpldisporp_id := :EvnPLDispOrp_id,
			    lpu_id := :Lpu_id,
			    server_id := :Server_id,
			    personevn_id := :PersonEvn_id,
			    evnpldisporp_setdt := :EvnPLDispOrp_setDT,
			    evnpldisporp_disdt := :EvnPLDispOrp_disDT,
			    evnpldisporp_diddt := :EvnPLDispOrp_didDT,
			    evnpldisporp_vizitcount := :EvnPLDispOrp_VizitCount,
			    evnpldisporp_isfinish := :EvnPLDispOrp_IsFinish,
			    attachtype_id := :AttachType_id,
			    lpu_aid := :Lpu_aid,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"EvnPLDispOrp_id" => $data["EvnPLDispOrp_id"],
			"Lpu_id" => $data["Lpu_id"],
			"Server_id" => $data["Server_id"],
			"PersonEvn_id" => $data["PersonEvn_id"],
			"EvnPLDispOrp_setDT" => $data["EvnPLDispOrp_setDT"],
			"EvnPLDispOrp_disDT" => $data["EvnPLDispOrp_disDT"],
			"EvnPLDispOrp_didDT" => $data["EvnPLDispOrp_didDT"],
			"EvnPLDispOrp_VizitCount" => $data["EvnPLDispOrp_VizitCount"],
			"EvnPLDispOrp_IsFinish" => $data["EvnPLDispOrp_IsFinish"],
			"AttachType_id" => $data["AttachType_id"],
			"Lpu_aid" => $data["Lpu_aid"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных (сохранение талона по ДД)");
		}
		$response = $result->result("array");
		if (!is_array($response) || count($response) == 0) {
			return false;
		} else if ($response[0]["Error_Msg"]) {
			return $response;
		}
		if (!isset($data["EvnPLDispOrp_id"])) {
			$data["EvnPLDispOrp_id"] = $response[0]["EvnPLDispOrp_id"];
		}
		// Осмотры врача-специалиста
		foreach ($data["EvnVizitDispOrp"] as $key => $record) {
			if ($record["Record_Status"] == 3) {// удаление посещений
				$query = "
					select
					    error_code as \"Error_Code\",
					    error_message as \"Error_Message\"
					from p_evnvizitdisporp_del(
					    evnvizitdisporp_id := :EvnVizitDispOrp_id,
					    pmuser_id := :pmUser_id
					);
				";
				$queryParams = [
					"EvnVizitDispOrp_id" => $record["EvnVizitDispOrp_id"],
					"pmUser_id" => $data["pmUser_id"]
				];
				$result = $this->db->query($query, $queryParams);
				if (!is_object($result)) {
					throw new Exception("Ошибка при выполнении запроса к базе данных (удаление осмотра врача-специалиста)");
				}
				$response = $result->result("array");
				if (!is_array($response) || count($response) == 0) {
					throw new Exception("Ошибка при удалении осмотра врача-специалиста");
				} else if (strlen($response[0]["Error_Msg"]) > 0) {
					return $response;
				}
			} else {
				$procedure = ($record["Record_Status"] == 0) ? "p_EvnVizitDispOrp_ins" : "p_EvnVizitDispOrp_upd";
				// проверяем, есть ли уже такое посещение
				$query = "
					select  count(*) as cnt
					from v_EvnVizitDispOrp
					where EvnVizitDispOrp_pid = :EvnPLDispOrp_id
					  and OrpDispSpec_id = :OrpDispSpec_id
					  and (EvnVizitDispOrp_id <> coalesce(:EvnVizitDispOrp_id, 0))
				";
				$queryParams = [
					"EvnPLDispOrp_id" => $data["EvnPLDispOrp_id"],
					"OrpDispSpec_id" => $record["OrpDispSpec_id"],
					"EvnVizitDispOrp_id" => $record["Record_Status"] == 0 ? null : $record["EvnVizitDispOrp_id"]
				];
				$result = $this->db->query($query, $queryParams);
				if (!is_object($result)) {
					throw new Exception("Ошибка при выполнении запроса к базе данных (сохранение посещения)");
				}
				$response = $result->result("array");
				if (!is_array($response) || count($response) == 0) {
					throw new Exception("Ошибка при выполнении запроса к базе данных (сохранение посещения)");
				} else if ($response[0]["cnt"] >= 1) {
					throw new Exception("Обнаружено дублирование осмотров, это недопустимо.");
				}
				// окончание проверки
				$selectString = "
				    evnvizitdisporp_id as \"EvnVizitDispOrp_id\",
				    error_code as \"Error_Code\",
				    error_message as \"Error_Message\"
				";
				$query = "
					select {$selectString}
					from {$procedure}(
					    evnvizitdisporp_id := :EvnVizitDispOrp_id,
					    evnvizitdisporp_pid := :EvnPLDispOrp_id,
					    lpu_id := :Lpu_id,
					    server_id := :Server_id,
					    personevn_id := :PersonEvn_id,
					    evnvizitdisporp_setdt := :EvnVizitDispOrp_setDate,
					    lpusection_id := :LpuSection_id,
					    medpersonal_id := :MedPersonal_id,
					    diag_id := :Diag_id,
					    healthkind_id := :HealthKind_id,
					    deseasestage_id := :DeseaseStage_id,
					    dopdispdiagtype_id := :DopDispDiagType_id,
					    evnvizitdisporp_issankur := :EvnVizitDispOrp_IsSanKur,
					    evnvizitdisporp_isout := :EvnVizitDispOrp_IsOut,
					    dopdispalien_id := :DopDispAlien_id,
					    orpdispspec_id := :OrpDispSpec_id,
					    pmuser_id := :pmUser_id
					);
				";
				$queryParams = [
					"EvnVizitDispOrp_id" => $record["Record_Status"] == 0 ? null : $record["EvnVizitDispOrp_id"],
					"EvnPLDispOrp_id" => $data["EvnPLDispOrp_id"],
					"Lpu_id" => $data["Lpu_id"],
					"Server_id" => $data["Server_id"],
					"PersonEvn_id" => $data["PersonEvn_id"],
					"EvnVizitDispOrp_setDate" => $record["EvnVizitDispOrp_setDate"],
					"LpuSection_id" => $record["LpuSection_id"],
					"MedPersonal_id" => $record["MedPersonal_id"],
					"OrpDispSpec_id" => $record["OrpDispSpec_id"],
					"Diag_id" => $record["Diag_id"],
					"HealthKind_id" => $record["HealthKind_id"],
					"DeseaseStage_id" => (isset($record["DeseaseStage_id"]) && $record["DeseaseStage_id"] > 0) ? $record["DeseaseStage_id"] : null,
					"DopDispDiagType_id" => (isset($record["DopDispDiagType_id"]) && $record["DopDispDiagType_id"] > 0) ? $record["DopDispDiagType_id"] : null,
					"EvnVizitDispOrp_IsSanKur" => $record["EvnVizitDispOrp_IsSanKur"],
					"EvnVizitDispOrp_IsOut" => $record["EvnVizitDispOrp_IsOut"],
					"DopDispAlien_id" => $record["DopDispAlien_id"],
					"pmUser_id" => $data["pmUser_id"]
				];
				$result = $this->db->query($query, $queryParams);
				if (!is_object($result)) {
					throw new Exception("Ошибка при выполнении запроса к базе данных (сохранение посещения)");
				}
				$response = $result->result("array");
				if (!is_array($response) || count($response) == 0) {
					return false;
				} else if ($response[0]["Error_Msg"]) {
					return $response;
				}
				$record["EvnVizitDispOrp_id"] = $response[0]["EvnVizitDispOrp_id"];
			}
		}

		// Лабораторные исследования
		foreach ($data["EvnUslugaDispOrp"] as $key => $record) {
			if ($record["Record_Status"] == 3) {// удаление исследований
				$query = "
					select
					    error_code as \"Error_Code\",
					    error_message as \"Error_Message\"
					from p_evnuslugadisporp_del(
					    evnuslugadisporp_id := :EvnUslugaDispOrp_id,
					    pmuser_id := :pmUser_id
					);
				";
				$queryParams = [
					"EvnUslugaDispOrp_id" => $record["EvnUslugaDispOrp_id"],
					"pmUser_id" => $data["pmUser_id"]
				];
				$result = $this->db->query($query, $queryParams);
				if (!is_object($result)) {
					throw new Exception("Ошибка при выполнении запроса к базе данных (удаление лабораторного исследования)");
				}
				$response = $result->result("array");
				if (!is_array($response) || count($response) == 0) {
					throw new Exception("Ошибка при удалении лабораторного исследования");
				} else if (strlen($response[0]["Error_Msg"]) > 0) {
					return $response;
				}
			} else {
				$procedure = ($record["Record_Status"] == 0) ? "p_EvnUslugaDispOrp_ins" : "p_EvnUslugaDispOrp_upd";
				// проверяем, есть ли уже такое исследование
				$query = "
					select  count(*) as cnt
					from v_EvnUslugaDispOrp
					where EvnUslugaDispOrp_pid = :EvnPLDispOrp_id
					  and OrpDispUslugaType_id = :OrpDispUslugaType_id
					  and (EvnUslugaDispOrp_id <> coalesce(:EvnUslugaDispOrp_id, 0))
				";
				$queryParams = [
					"EvnPLDispOrp_id" => $data["EvnPLDispOrp_id"],
					"OrpDispUslugaType_id" => $record["OrpDispUslugaType_id"],
					"EvnUslugaDispOrp_id" => $record["Record_Status"] == 0 ? null : $record["EvnUslugaDispOrp_id"]
				];
				$result = $this->db->query($query, $queryParams);
				if (!is_object($result)) {
					throw new Exception("Ошибка при выполнении запроса к базе данных (сохранение исследования)");
				}
				$response = $result->result("array");
				if (!is_array($response) || count($response) == 0) {
					throw new Exception("Ошибка при выполнении запроса к базе данных (сохранение исследования)");
				} else if ($response[0]["cnt"] >= 1) {
					throw new Exception("Обнаружено дублирование исследований, это недопустимо.");
				}
				// окончание проверки
				if ($record["LpuSection_id"] == "") {
					$record["LpuSection_id"] = null;
				}
				$selectString = "
					evnuslugadisporp_id as \"EvnUslugaDispOrp_id\",
					error_code as \"Error_Code\",
					error_message as \"Error_Message\"
				";
				$query = "
					select {$selectString}
					from {$procedure}(
					    evnuslugadisporp_id := :EvnUslugaDispOrp_id,
					    evnuslugadisporp_pid := :EvnPLDispOrp_id,
					    lpu_id := :Lpu_id,
					    server_id := :Server_id,
					    personevn_id := :PersonEvn_id,
					    evnuslugadisporp_setdt := :EvnUslugaDispOrp_setDate,
					    evnuslugadisporp_diddt := :EvnUslugaDispOrp_didDate,
					    paytype_id := 7,
					    usluga_id := :Usluga_id,
					    medpersonal_id := :MedPersonal_id,
					    uslugaplace_id := 1,
					    lpu_uid := :Lpu_id,
					    lpusection_uid := :LpuSection_id,
					    evnuslugadisporp_kolvo := 1,
					    orpdispuslugatype_id := :OrpDispUslugaType_id,
					    examinationplace_id := :ExaminationPlace_id,
					    pmuser_id := :pmUser_id
					);
				";
				$queryParams = [
					"EvnUslugaDispOrp_id" => $record["Record_Status"] == 0 ? null : $record["EvnUslugaDispOrp_id"],
					"EvnPLDispOrp_id" => $data["EvnPLDispOrp_id"],
					"Lpu_id" => $data["Lpu_id"],
					"Server_id" => $data["Server_id"],
					"PersonEvn_id" => $data["PersonEvn_id"],
					"EvnUslugaDispOrp_setDate" => $record["EvnUslugaDispOrp_setDate"],
					"EvnUslugaDispOrp_didDate" => $record["EvnUslugaDispOrp_didDate"],
					"LpuSection_id" => $record["LpuSection_id"],
					"MedPersonal_id" => $record["MedPersonal_id"],
					"OrpDispUslugaType_id" => $record["OrpDispUslugaType_id"],
					"Usluga_id" => $record["Usluga_id"],
					"ExaminationPlace_id" => $record["ExaminationPlace_id"],
					"pmUser_id" => $data["pmUser_id"]
				];
				$result = $this->db->query($query, $queryParams);
				if (!is_object($result)) {
					throw new Exception("Ошибка при выполнении запроса к базе данных (сохранение посещения)");
				}
				$response = $result->result("array");
				if (!is_array($response) || count($response) == 0) {
					return false;
				} else if ($response[0]["Error_Msg"]) {
					return $response;
				}
				$record["EvnUslugaDispOrp_id"] = $response[0]["EvnUslugaDispOrp_id"];
			}
		}
		return [["EvnPLDispOrp_id" => $data["EvnPLDispOrp_id"], "Error_Msg" => ""]];
	}

	/**
	 * Поиск талонов по ДД
	 * @param $data
	 * @return array|bool
	 */
	function searchEvnPLDispOrp($data)
	{
		$filterArray = [];
		$joinArray = [];
		if ($data["PersonAge_Min"] > $data["PersonAge_Max"]) {
			return false;
		}
		$queryParams = [];
		if (($data["DocumentType_id"] > 0) || ($data["OrgDep_id"] > 0)) {
			$joinArray[] = "inner join Document on Document.Document_id = PS.Document_id";
			if ($data["DocumentType_id"] > 0) {
				$filterArray[] = "Document.DocumentType_id = :DocumentType_id";
				$queryParams["DocumentType_id"] = $data["DocumentType_id"];
			}
			if ($data["OrgDep_id"] > 0) {
				$filterArray[] = "Document.OrgDep_id = :OrgDep_id";
				$queryParams["OrgDep_id"] = $data["OrgDep_id"];
			}
		}
		if (($data["OMSSprTerr_id"] > 0) || ($data["OrgSmo_id"] > 0) || ($data["PolisType_id"] > 0)) {
			$joinArray[] = "inner join Polis on Polis.Polis_id = PS.Polis_id";
			if ($data["OMSSprTerr_id"] > 0) {
				$filterArray[] = "Polis.OmsSprTerr_id = :OMSSprTerr_id";
				$queryParams["OMSSprTerr_id"] = $data["OMSSprTerr_id"];
			}
			if ($data["OrgSmo_id"] > 0) {
				$filterArray[] = "Polis.OrgSmo_id = :OrgSmo_id";
				$queryParams["OrgSmo_id"] = $data["OrgSmo_id"];
			}
			if ($data["PolisType_id"] > 0) {
				$filterArray[] = "Polis.PolisType_id = :PolisType_id";
				$queryParams["PolisType_id"] = $data["PolisType_id"];
			}
		}
		if (($data["Org_id"] > 0) || ($data["Post_id"] > 0)) {
			$joinArray[] = "inner join Job on Job.Job_id = PS.Job_id";
			if ($data["Org_id"] > 0) {
				$filterArray[] = "Job.Org_id = :Org_id";
				$queryParams["Org_id"] = $data["Org_id"];
			}
			if ($data["Post_id"] > 0) {
				$filterArray[] = "Job.Post_id = :Post_id";
				$queryParams["Post_id"] = $data["Post_id"];
			}
		}
		if (($data["KLRgn_id"] > 0) || ($data["KLSubRgn_id"] > 0) || ($data["KLCity_id"] > 0) || ($data["KLTown_id"] > 0) || ($data["KLStreet_id"] > 0) || (strlen($data["Address_House"]) > 0)) {
			$joinArray[] = " inner join Address on Address.Address_id = PS.UAddress_id";
			if ($data["KLRgn_id"] > 0) {
				$filterArray[] = "Address.KLRgn_id = :KLRgn_id";
				$queryParams["KLRgn_id"] = $data["KLRgn_id"];
			}
			if ($data["KLSubRgn_id"] > 0) {
				$filterArray[] = "Address.KLSubRgn_id = :KLSubRgn_id";
				$queryParams["KLSubRgn_id"] = $data["KLSubRgn_id"];
			}
			if ($data["KLCity_id"] > 0) {
				$filterArray[] = "Address.KLCity_id = :KLCity_id";
				$queryParams["KLCity_id"] = $data["KLCity_id"];
			}
			if ($data["KLTown_id"] > 0) {
				$filterArray[] = "Address.KLTown_id = :KLTown_id";
				$queryParams["KLTown_id"] = $data["KLTown_id"];
			}
			if ($data["KLStreet_id"] > 0) {
				$filterArray[] = "Address.KLStreet_id = :KLStreet_id";
				$queryParams["KLStreet_id"] = $data["KLStreet_id"];
			}
			if (strlen($data["Address_House"]) > 0) {
				$filterArray[] = "Address.Address_House = :Address_House";
				$queryParams["Address_House"] = $data["Address_House"];
			}
		}
		if (isset($data["EvnPLDispOrp_disDate"][1])) {
			$filterArray[] = "EvnPLDispOrp.EvnPLDispOrp_disDate <= :EvnPLDispOrp_disDate1";
			$queryParams["EvnPLDispOrp_disDate1"] = $data["EvnPLDispOrp_disDate"][1];
		}
		if (isset($data["EvnPLDispOrp_disDate"][0])) {
			$filterArray[] = "EvnPLDispOrp.EvnPLDispOrp_disDate >= :EvnPLDispOrp_disDate1";
			$queryParams["EvnPLDispOrp_disDate0"] = $data["EvnPLDispOrp_disDate"][0];
		}
		if ($data["EvnPLDispOrp_IsFinish"] > 0) {
			$filterArray[] = "EvnPLDispOrp.EvnPLDispOrp_IsFinish = :EvnPLDispOrp_IsFinish";
			$queryParams["EvnPLDispOrp_IsFinish"] = $data["EvnPLDispOrp_IsFinish"];
		}
		if (isset($data["EvnPLDispOrp_setDate"][1])) {
			$filterArray[] = "EvnPLDispOrp.EvnPLDispOrp_setDate <= :EvnPLDispOrp_setDate1";
			$queryParams["EvnPLDispOrp_setDate1"] = $data["EvnPLDispOrp_setDate"][1];
		}
		if (isset($data["EvnPLDispOrp_setDate"][0])) {
			$filterArray[] = "EvnPLDispOrp.EvnPLDispOrp_setDate >= :EvnPLDispOrp_setDate0";
			$queryParams["EvnPLDispOrp_setDate0"] = $data["EvnPLDispOrp_setDate"][0];
		}
		if ($data["PersonAge_Max"] > 0) {
			$filterArray[] = "EvnPLDispOrp.Person_Age <= :PersonAge_Max";
			$queryParams["PersonAge_Max"] = $data["PersonAge_Max"];
		}
		if ($data["PersonAge_Min"] > 0) {
			$filterArray[] = "EvnPLDispOrp.Person_Age >= :PersonAge_Min";
			$queryParams["PersonAge_Min"] = $data["PersonAge_Min"];
		}
		if (($data["PersonCard_Code"] != "") || ($data["LpuRegion_id"] > 0)) {
			$joinArray[] = "inner join v_PersonCard PC on PC.Person_id = PS.Person_id";
			if (strlen($data["PersonCard_Code"]) > 0) {
				$filterArray[] = "PC.PersonCard_Code = :PersonCard_Code";
				$queryParams["PersonCard_Code"] = $data["PersonCard_Code"];
			}
			if (strlen($data["LpuRegion_id"]) > 0) {
				$filterArray[] = "PC.LpuRegion_id = :LpuRegion_id";
				$queryParams["LpuRegion_id"] = $data["LpuRegion_id"];
			}
		}
		if (isset($data["Person_Birthday"][1])) {
			$filterArray[] = "PS.Person_Birthday <= :Person_Birthday1";
			$queryParams["Person_Birthday1"] = $data["Person_Birthday"][1];
		}
		if (isset($data["Person_Birthday"][0])) {
			$filterArray[] = "PS.Person_Birthday >= :Person_Birthday0";
			$queryParams["Person_Birthday0"] = $data["Person_Birthday"][0];
		}
		if (strlen($data["Person_Firname"]) > 0) {
			$filterArray[] = "PS.Person_Firname like :Person_Firname";
			$queryParams["Person_Firname"] = $data["Person_Firname"] . "%";
		}
		if (strlen($data["Person_Secname"]) > 0) {
			$filterArray[] = "PS.Person_Secname like :Person_Secname";
			$queryParams["Person_Secname"] = $data["Person_Secname"] . "%";
		}
		if ($data["Person_Snils"] > 0) {
			$filterArray[] = "PS.Person_Snils = :Person_Snils";
			$queryParams["Person_Snils"] = $data["Person_Snils"];
		}
		if (strlen($data["Person_Surname"]) > 0) {
			$filterArray[] = "PS.Person_Surname like :Person_Surname";
			$queryParams["Person_Surname"] = $data["Person_Surname"] . "%";
		}
		if ($data["PrivilegeType_id"] > 0) {
			$joinArray[] = "
				inner join v_PersonPrivilege PP on PP.Person_id = EvnPLDispOrp.Person_id
					and PP.PrivilegeType_id = :PrivilegeType_id
					and PP.PersonPrivilege_begDate is not null
					and PP.PersonPrivilege_begDate <= tzgetdate()
					and (PP.PersonPrivilege_endDate is null or PP.PersonPrivilege_endDate >= tzgetdate())
					and PP.Lpu_id = :Lpu_id
			";
			$queryParams["PrivilegeType_id"] = $data["PrivilegeType_id"];
			$queryParams["Lpu_id"] = $data["Lpu_id"];
		}
		if ($data["Sex_id"] >= 0) {
			$filterArray[] = "PS.Sex_id = :Sex_id";
			$queryParams["Sex_id"] = $data["Sex_id"];
		}
		if ($data["SocStatus_id"] > 0) {
			$filterArray[] = "PS.SocStatus_id = :SocStatus_id";
			$queryParams["SocStatus_id"] = $data["SocStatus_id"];
		}
		$joinString = implode("\n ", $joinArray);
		$filterString = (count($filterArray) != 0)?"where ".implode(" and ", $filterArray):"";
		$fromString = "
			v_EvnPLDispOrp EvnPLDispOrp
			inner join v_PersonState PS on PS.Person_id = EvnPLDispOrp.Person_id
			left join YesNo IsFinish on IsFinish.YesNo_id = EvnPLDispOrp.EvnPLDispOrp_IsFinish
			{$joinString}
		";
		$selectString = "
			EvnPLDispOrp.EvnPLDispOrp_id as \"EvnPLDispOrp_id\",
			EvnPLDispOrp.Person_id as \"Person_id\",
			EvnPLDispOrp.Server_id as \"Server_id\",
			EvnPLDispOrp.PersonEvn_id as \"PersonEvn_id\",
			rtrim(PS.Person_Surname) as \"Person_Surname\",
			rtrim(PS.Person_Firname) as \"Person_Firname\",
			rtrim(PS.Person_Secname) as \"Person_Secname\",
			to_char(PS.Person_Birthday, '{$this->dateTimeForm104}') as \"Person_Birthday\",
			EvnPLDispOrp.EvnPLDispOrp_VizitCount as \"EvnPLDispOrp_VizitCount\",
			IsFinish.YesNo_Name as \"EvnPLDispOrp_IsFinish\",
			to_char(EvnPLDispOrp.EvnPLDispOrp_setDate, '{$this->dateTimeForm104}') as \"EvnPLDispOrp_setDate\",
			to_char(EvnPLDispOrp.EvnPLDispOrp_disDate, '{$this->dateTimeForm104}') as \"EvnPLDispOrp_disDate\"
		";
		$query = "
			select distinct {$selectString}
			from {$fromString}
			{$filterString}
			limit 100
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Получение списка записей для потокового ввода
	 * @param $data
	 * @return array|bool
	 */
	function getEvnPLDispOrpStreamList($data)
	{
		$query = "
			select distinct
				EvnPLDispOrp.EvnPLDispOrp_id as EvnPLDispOrp_id,
				EvnPLDispOrp.Person_id as Person_id,
				EvnPLDispOrp.Server_id as Server_id,
				EvnPLDispOrp.PersonEvn_id as PersonEvn_id,
				RTRIM(PS.Person_Surname)||' '||RTRIM(PS.Person_Firname)||' '||RTRIM(PS.Person_Secname) as Person_Fio,
				to_char(PS.Person_Birthday, '{$this->dateTimeForm104}') as Person_Birthday,
				EvnPLDispOrp.EvnPLDispOrp_VizitCount as EvnPLDispOrp_VizitCount,
				IsFinish.YesNo_Name as EvnPLDispOrp_IsFinish,
				to_char(EvnPLDispOrp.EvnPLDispOrp_setDate, '{$this->dateTimeForm104}') as EvnPLDispOrp_setDate,
				to_char(EvnPLDispOrp.EvnPLDispOrp_disDate, '{$this->dateTimeForm104}') as EvnPLDispOrp_disDate
			from
				v_EvnPLDispOrp EvnPLDispOrp
				inner join v_PersonState PS on PS.Person_id = EvnPLDispOrp.Person_id
				left join YesNo IsFinish on IsFinish.YesNo_id = EvnPLDispOrp.EvnPLDispOrp_IsFinish
			where EvnPLDispOrp_updDT >= :dt
			  and EvnPLDispOrp.pmUser_updID= :pmUser_id
			limit 100
		";
		$queryParams = [
			"dt" => $data["begDate"] . " " . $data["begTime"],
			"pmUser_id" => $data["pmUser_id"]
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Получение списка лет, в которые выписывались талоны по ДД с количеством талонов, для комбобокса
	 * @param $data
	 * @return array|bool
	 */
	function getEvnPLDispOrpYears($data)
	{
		$query = "
			select
				count(EvnPLDispOrp_id) as count,
				date_part('year', EvnPLDispOrp_setDate) as \"EvnPLDispOrp_Year\"
			from v_EvnPLDispOrp
			where Lpu_id = :Lpu_id
			  and date_part('year', EvnPLDispOrp_setDate) <= 2012
			group by date_part('year', EvnPLDispOrp_setDate)
			order by date_part('year', EvnPLDispOrp_setDate)
		";
		$queryParams = ["Lpu_id" => $data["Lpu_id"]];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Проверка, есть ли талон на этого человека в этом году
	 * @param $data
	 * @return array|bool
	 */
	function checkIfEvnPLDispOrpExists($data)
	{
		$query = "
			select count(EvnPLDispOrp_id) as count
			from v_EvnPLDispOrp
			where Person_id = ?
			  and Lpu_id = ?
			  and date_part('year', EvnPLDispOrp_setDate) = date_part('year', tzgetdate())
		";
		$queryParams = [
			"Person_id" => $data["Person_id"],
			"Lpu_id" => $data["Lpu_id"]
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$selection = $result->result("array");
		if ($selection[0]["count"] == 0) {
			return [["Error_Msg" => "", "isEvnPLDispOrpExists" => false]];
		} else {
			return [["Error_Msg" => "", "isEvnPLDispOrpExists" => true]];
		}
	}
}
<?php
defined("BASEPATH") or die ("No direct script access allowed");
/**
 * MeasuresRehab_model - модель для работы c мероприятиями реабилитации и абилитации
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			12.12.2016
 *
 * @property CI_DB_driver $db
 */

class MeasuresRehab_model extends swPgModel
{
	public $dateTimeForm104 = "DD.MM.YYYY";
	public $dateTimeForm120 = "YYYY-MM-DD";

	public $IPRAScheme = "dbo";

	function __construct()
	{
		parent::__construct();

		if (getRegionNick() == "ufa") {
			$this->IPRAScheme = "r2";
		}
	}

	/**
	 * Получение списка услуг, доступных для мероприятия реабилитации
	 * @param $data
	 * @return array|false
	 */
	function loadEvnUslugaList($data)
	{
		$params = ["IPRARegistry_id" => $data["IPRARegistry_id"]];
		$query = "
			select
			    EU.EvnUsluga_id as \"EvnUsluga_id\",
			    to_char(EU.EvnUsluga_setDT, '{$this->dateTimeForm104}') as \"EvnUsluga_setDate\",
			    UC.UslugaComplex_id as \"UslugaComplex_id\",
			    UC.UslugaComplex_Code as \"UslugaComplex_Code\",
			    UC.UslugaComplex_Name as \"UslugaComplex_Name\",
			    L.Lpu_id as \"Lpu_id\",
			    L.Org_id as \"Org_id\",
			    L.Org_Nick as \"Org_Nick\"
			from
			    v_EvnUsluga EU
			    inner join v_UslugaComplex UC on UC.UslugaComplex_id = EU.UslugaComplex_id
			    inner join v_Lpu L on L.Lpu_id = EU.Lpu_id
			where EU.Person_id = (select IR.Person_id from v_IPRARegistry IR where IR.IPRARegistry_id = :IPRARegistry_id)
			  and EU.EvnUsluga_setDate
			    between (select IR.IPRARegistry_issueDate from v_IPRARegistry IR where IR.IPRARegistry_id = :IPRARegistry_id)
			    and (
			        case when
			            (
			                (select dateadd('day', -30, IR.IPRARegistry_EndDate) from v_IPRARegistry IR where IR.IPRARegistry_id = :IPRARegistry_id) is null or
			                (select dateadd('day', -30, IR.IPRARegistry_EndDate) from v_IPRARegistry IR where IR.IPRARegistry_id = :IPRARegistry_id) > tzGetDate()
			            )
			            then tzGetDate()
			            else (select dateadd('day', -30, IR.IPRARegistry_EndDate) from v_IPRARegistry IR where IR.IPRARegistry_id = :IPRARegistry_id)
			        end
			    )
			order by EU.EvnUsluga_setDT
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка случаев лечения, доступных для мероприятия реабилитации
	 * @param $data
	 * @return array|false
	 */
	function loadEvnList($data)
	{
		$params = ["IPRARegistry_id" => $data["IPRARegistry_id"]];
		$query = "
			select
				E.Evn_id as \"Evn_id\",
				E.EvnClass_SysNick as \"EvnClass_SysNick\",
				case 
					when E.EvnClass_SysNick = 'EvnPL' then 'АПП'
					when E.EvnClass_SysNick = 'EvnPS' and LU.LpuUnitType_SysNick = 'stac' then 'КС'
					when E.EvnClass_SysNick = 'EvnPS' then 'СЗП'
				end as \"EvnClass_Nick\",
				coalesce(EPL.EvnPL_NumCard, EPS.EvnPS_NumCard) as \"Evn_NumCard\",
				to_char(E.Evn_setDT, '{$this->dateTimeForm104}') as \"Evn_setDate\",
				to_char(E.Evn_disDT, '{$this->dateTimeForm104}') as \"Evn_disDate\",
				L.Lpu_id as \"Lpu_id\",
				L.Org_id as \"Org_id\",
				L.Lpu_Nick as \"Lpu_Nick\"
			from
				v_Evn E
				left join v_EvnPL EPL on EPL.EvnPL_id = E.Evn_id
				left join v_EvnPS EPS on EPS.EvnPS_id = E.Evn_id
				left join v_LpuSection LS on LS.LpuSection_id = EPS.LpuSection_id
				left join v_LpuUnit LU on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_Lpu L on L.Lpu_id = E.Lpu_id
			where E.Person_id = (select IR.Person_id from v_IPRARegistry IR where IR.IPRARegistry_id = :IPRARegistry_id)
			  and E.Evn_disDate between
			        (select IR.IPRARegistry_issueDate from v_IPRARegistry IR where IR.IPRARegistry_id = :IPRARegistry_id) and
					(
						case where (
								(select dateadd('day', -30, IR.IPRARegistry_EndDate) from v_IPRARegistry IR where IR.IPRARegistry_id = :IPRARegistry_id) is null or
								(select dateadd('day', -30, IR.IPRARegistry_EndDate) from v_IPRARegistry IR where IR.IPRARegistry_id = :IPRARegistry_id) > tzGetDate()
							)
						then tzGetDate()
						else (select dateadd('day', -30, IR.IPRARegistry_EndDate) from v_IPRARegistry IR where IR.IPRARegistry_id = :IPRARegistry_id)
						end
					)
			  and E.EvnClass_SysNick in ('EvnPL','EvnPS')
			  and (E.EvnClass_SysNick not like 'EvnPL' or coalesce(EPL.EvnPL_IsFinish, 1) = 2)
			order by E.Evn_setDate
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка медикаментов, доступных для мероприятия реабилитации
	 * @param $data
	 * @return array|false
	 */
	function loadReceptOtovList($data)
	{
		$params = ["IPRARegistry_id" => $data["IPRARegistry_id"]];
		$query = "
			select
				RO.ReceptOtov_id as \"ReceptOtov_id\",
				ER.EvnRecept_Ser as \"EvnRecept_Ser\",
				ER.EvnRecept_Num as \"EvnRecept_Num\",
				D.Drug_id as \"Drug_id\",
				D.Drug_Name as \"Drug_Name\",
				to_char(RO.EvnRecept_otpDate, '{$this->dateTimeForm104}') as \"EvnRecept_otpDate\",
				L.Lpu_id as \"Lpu_id\",
				L.Org_id as \"Org_id\",
				L.Lpu_Nick as \"Lpu_Nick\"
			from
				v_EvnRecept ER
				left join lateral (
					select RO.*
					from ReceptOtov RO
					where RO.EvnRecept_id = ER.EvnRecept_id
					  and RO.EvnRecept_otpDate is not null
					limit 1
				) as RO on true
				left join v_Drug D on D.Drug_id = ER.Drug_id
				left join v_Lpu L on L.Lpu_id = ER.Lpu_id
			where ER.Person_id = (select IR.Person_id from v_IPRARegistry IR where IR.IPRARegistry_id = :IPRARegistry_id)
			  and RO.EvnRecept_otpDate::date between
			    (select IR.IPRARegistry_issueDate from v_IPRARegistry IR where IR.IPRARegistry_id = :IPRARegistry_id) and
				(
					case where (
							(select dateadd('day', -30, IR.IPRARegistry_EndDate) from v_IPRARegistry IR where IR.IPRARegistry_id = :IPRARegistry_id) is null or
							(select dateadd('day', -30, IR.IPRARegistry_EndDate) from v_IPRARegistry IR where IR.IPRARegistry_id = :IPRARegistry_id) > tzGetDate()
						)
					then tzGetDate()
					else (select dateadd('day', -30, IR.IPRARegistry_EndDate) from v_IPRARegistry IR where IR.IPRARegistry_id = :IPRARegistry_id)
					end
				)
			order by
				RO.EvnRecept_otpDate::date,
				ER.EvnRecept_Ser,
				ER.EvnRecept_Num
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка мероприятий реабилитации или абилитации по пациенту
	 * @param $data
	 * @return array
	 */
	function loadMeasuresRehabGridPerson($data)
	{
		$params = ["EvnPrescrMse_id" => $data["EvnPrescrMse_id"]];
		$query = "
			select
				MeasuresRehabMSE_id as \"MeasuresRehabMSE_id\",
				EvnPrescrMse_id as \"EvnPrescrMse_id\",
				MeasuresRehabMSE_Name as \"MeasuresRehabMSE_Name\",
				MeasuresRehabMSE_Type as \"MeasuresRehabMSE_Type\",
				MeasuresRehabMSE_SubType as \"MeasuresRehabMSE_SubType\",
				MeasuresRehabMSE_Result as \"MeasuresRehabMSE_Result\",
				MeasuresRehabMSE_IsExport as \"MeasuresRehabMSE_IsExport\",
				to_char(MeasuresRehabMSE_BegDate, '{$this->dateTimeForm104}') as \"MeasuresRehabMSE_BegDate\",
				to_char(MeasuresRehabMSE_EndDate, '{$this->dateTimeForm104}') as \"MeasuresRehabMSE_EndDate\"
			from MeasuresRehabMSE
			where EvnPrescrMse_id = :EvnPrescrMse_id
			order by MeasuresRehabMSE_BegDate
		";
		$resp = $this->queryResult($query, $params);
		return ["data" => $resp];
	}

	/**
	 * Получение списка мероприятий реабилитации или абилитации
	 * @param $data
	 * @return array
	 */
	function loadMeasuresRehabGrid($data)
	{
		$params = ["IPRARegistry_id" => $data["IPRARegistry_id"]];
		$query = "
			select
				MR.MeasuresRehab_id as \"MeasuresRehab_id\",
				MR.IPRARegistry_id as \"IPRARegistry_id\",
				MR.MeasuresRehab_Name as \"MeasuresRehab_Name\",
				to_char(MR.MeasuresRehab_setDate, '{$this->dateTimeForm104}') as \"MeasuresRehab_setDate\",
				MRT.MeasuresRehabType_id as \"MeasuresRehabType_id\",
				MRT.MeasuresRehabType_Name as \"MeasuresRehabType_Name\",
				MRT.MeasuresRehabType_Code as \"MeasuresRehabType_Code\",
				case
					when RO.ReceptOtov_id is not null then 'Рецепт №'||RO.EvnRecept_Num||' '||RO.EvnRecept_Ser||'. '||D.Drug_Name
					when EU.EvnUsluga_id is not null then UC.UslugaComplex_Name
					else MR.MeasuresRehab_Name
				end as \"MeasuresRehab_Name\",
				MRST.MeasuresRehabSubType_id as \"MeasuresRehabSubType_id\",
				MRST.MeasuresRehabSubType_Code as \"MeasuresRehabSubType_Code\",
				MRST.MeasuresRehabSubType_Name as \"MeasuresRehabSubType_Name\",
				MRR.MeasuresRehabResult_id as \"MeasuresRehabResult_id\",
				MRR.MeasuresRehabResult_Code as \"MeasuresRehabResult_Code\",
				MRR.MeasuresRehabResult_Name as \"MeasuresRehabResult_Name\",
				UC.UslugaComplex_Code as \"MeasuresRehab_Code\"
			from
				v_MeasuresRehab MR
				left join v_MeasuresRehabType MRT on MRT.MeasuresRehabType_id = MR.MeasuresRehabType_id
				left join v_MeasuresRehabSubType MRST on MRST.MeasuresRehabSubType_id = MR.MeasuresRehabSubType_id
				left join v_MeasuresRehabResult MRR on MRR.MeasuresRehabResult_id = MR.MeasuresRehabResult_id
				left join v_EvnUsluga EU on EU.EvnUsluga_id = MR.EvnUsluga_id
				left join v_UslugaComplex UC on UC.UslugaComplex_id = EU.UslugaComplex_id
				left join ReceptOtov RO on RO.ReceptOtov_id = MR.ReceptOtov_id
				left join v_EvnRecept ER on ER.EvnRecept_id = RO.EvnRecept_id
				left join v_Drug D on D.Drug_id = ER.Drug_id
			where MR.IPRARegistry_id = :IPRARegistry_id
			order by MR.MeasuresRehab_setDate
		";
		$resp = $this->queryResult($query, $params);
		return ["data" => $resp];
	}

	/**
	 * Получение списка мероприятий реабилитации или абилитации для экспорта
	 * @param $data
	 * @return array
	 */
	function loadMeasuresRehabExportGrid($data)
	{
		$params = [];
		$filters = [];
		if (!empty($data["MeasuresRehab_begRange"])) {
			$filters[] = "MR.MeasuresRehab_setDate >= :MeasuresRehab_begRange";
			$params["MeasuresRehab_begRange"] = $data["MeasuresRehab_begRange"];
		}
		if (!empty($data["MeasuresRehab_endRange"])) {
			$filters[] = "MR.MeasuresRehab_setDate <= :MeasuresRehab_endRange";
			$params["MeasuresRehab_endRange"] = $data["MeasuresRehab_endRange"];
		}
		if (!empty($data["MeasuresRehab_IsExport"])) {
			$filters[] = "coalesce(MR.MeasuresRehab_IsExport, 1) = :MeasuresRehab_IsExport";
			$params["MeasuresRehab_IsExport"] = $data["MeasuresRehab_IsExport"];
		}
		if (!empty($data["LpuAttach_id"])) {
			$filters[] = "LpuAttach.Lpu_id = :LpuAttach_id";
			$params["LpuAttach_id"] = $data["LpuAttach_id"];
		}
		$whereString = (count($filters) != 0)?"where ".implode(" and ", $filters):"";
		$query = "
			select
				MR.MeasuresRehab_id as \"MeasuresRehab_id\",
				IR.IPRARegistry_id as \"IPRARegistry_id\",
				IR.IPRARegistry_Number as \"IPRARegistry_Number\",
				coalesce(PS.Person_SurName, '')||coalesce(' '||PS.Person_FirName, '')||coalesce(' '||PS.Person_SecName, '') as \"Person_Fio\",
				to_char(PS.Person_BirthDay, '{$this->dateTimeForm104}') as \"Person_BirthDay\",
				LpuAttach.Lpu_id as \"LpuAttach_id\",
				LpuAttach.Lpu_Nick as \"LpuAttach_Nick\",
				MR.MeasuresRehab_Name as \"MeasuresRehab_Name\",
				to_char(MR.MeasuresRehab_setDate, '{$this->dateTimeForm104}') as \"MeasuresRehab_setDate\",
				MRT.MeasuresRehabType_id as \"MeasuresRehabType_id\",
				MRT.MeasuresRehabType_Code as \"MeasuresRehabType_Code\",
				MRT.MeasuresRehabType_Name as \"MeasuresRehabType_Name\",
				MRST.MeasuresRehabSubType_id as \"MeasuresRehabSubType_id\",
				MRST.MeasuresRehabSubType_Code as \"MeasuresRehabSubType_Code\",
				MRST.MeasuresRehabSubType_Name as \"MeasuresRehabSubType_Name\",
				MRR.MeasuresRehabResult_id as \"MeasuresRehabResult_id\",
				MRR.MeasuresRehabResult_Code as \"MeasuresRehabResult_Code\",
				MRR.MeasuresRehabResult_Name as \"MeasuresRehabResult_Name\"
			from
				v_MeasuresRehab MR
				inner join v_IPRARegistry IR on IR.IPRARegistry_id = MR.IPRARegistry_id
				inner join v_PersonState PS on PS.Person_id = IR.Person_id
				left join v_Lpu LpuAttach on LpuAttach.Lpu_id = PS.Lpu_id
				left join v_MeasuresRehabType MRT on MRT.MeasuresRehabType_id = MR.MeasuresRehabType_id
				left join v_MeasuresRehabSubType MRST on MRST.MeasuresRehabSubType_id = MR.MeasuresRehabSubType_id
				left join v_MeasuresRehabResult MRR on MRR.MeasuresRehabResult_id = MR.MeasuresRehabResult_id
			{$whereString}
		";
		$resp = $this->queryResult($query, $params);
		return ["data" => $resp];
	}

	/**
	 * Получение данных мероприятия реабилитиции для редактирования
	 * @param $data
	 * @return array|false
	 */
	function loadMeasuresRehabForm($data)
	{
		$params = ["MeasuresRehab_id" => $data["MeasuresRehab_id"]];
		$query = "
			select
				MR.MeasuresRehab_id as \"MeasuresRehab_id\",
				MR.IPRARegistry_id as \"IPRARegistry_id\",
				case 
					when MR.EvnUsluga_id is not null then 'usluga'
					when MR.Evn_id is not null then 'evn'
					when MR.ReceptOtov_id is not null then 'drug'
					else 'other'
				end as \"type\",
				MR.MeasuresRehabType_id as \"MeasuresRehabType_id\",
				MR.MeasuresRehabSubType_id as \"MeasuresRehabSubType_id\",
				to_char(MR.MeasuresRehab_setDate, '{$this->dateTimeForm104}') \"as MeasuresRehab_setDate\",
				MR.MeasuresRehab_Name as \"MeasuresRehab_Name\",
				case 
					when MR.Org_id is not null then MR.Org_id::varchar
					else MR.MeasuresRehab_OrgName
				end as \"Org_id\",
				MR.MeasuresRehabResult_id as \"MeasuresRehabResult_id\",
				MR.EvnUsluga_id as \"EvnUsluga_id\",
				MR.Evn_id as \"Evn_id\",
				MR.ReceptOtov_id as \"ReceptOtov_id\"
			from v_MeasuresRehab MR
			where MR.MeasuresRehab_id = :MeasuresRehab_id
			limit 1
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Сохранение мероприятия реабилитации
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	function saveMeasuresRehab($data)
	{
		$params = [
			"MeasuresRehab_id" => !empty($data["MeasuresRehab_id"]) ? $data["MeasuresRehab_id"] : null,
			"IPRARegistry_id" => $data["IPRARegistry_id"],
			"MeasuresRehabType_id" => $data["MeasuresRehabType_id"],
			"MeasuresRehabSubType_id" => !empty($data["MeasuresRehabSubType_id"]) ? $data["MeasuresRehabSubType_id"] : null,
			"MeasuresRehab_setDate" => !empty($data["MeasuresRehab_setDate"]) ? $data["MeasuresRehab_setDate"] : null,
			"MeasuresRehab_OrgName" => !empty($data["MeasuresRehab_OrgName"]) ? $data["MeasuresRehab_OrgName"] : null,
			"Org_id" => !empty($data["Org_id"]) ? $data["Org_id"] : null,
			"MeasuresRehab_Name" => !empty($data["MeasuresRehab_Name"]) ? $data["MeasuresRehab_Name"] : null,
			"MeasuresRehabResult_id" => !empty($data["MeasuresRehabResult_id"]) ? $data["MeasuresRehabResult_id"] : null,
			"EvnUsluga_id" => !empty($data["EvnUsluga_id"]) ? $data["EvnUsluga_id"] : null,
			"Evn_id" => !empty($data["Evn_id"]) ? $data["Evn_id"] : null,
			"ReceptOtov_id" => !empty($data["ReceptOtov_id"]) ? $data["ReceptOtov_id"] : null,
			"pmUser_id" => $data["pmUser_id"],
		];
		$procedure = (empty($params["MeasuresRehab_id"]))?"p_MeasuresRehab_ins":"p_MeasuresRehab_upd";
		$selectString = "
		    measuresrehab_id as \"MeasuresRehab_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    measuresrehab_id := :MeasuresRehab_id,
			    measuresrehab_name := :MeasuresRehab_Name,
			    measuresrehabtype_id := :MeasuresRehabType_id,
			    measuresrehabsubtype_id := :MeasuresRehabSubType_id,
			    measuresrehab_setdate := :MeasuresRehab_setDate,
			    measuresrehabresult_id := :MeasuresRehabResult_id,
			    org_id := :Org_id,
			    measuresrehab_orgname := :MeasuresRehab_OrgName,
			    ipraregistry_id := :IPRARegistry_id,
			    evnusluga_id := :EvnUsluga_id,
			    evn_id := :Evn_id,
			    receptotov_id := :ReceptOtov_id,
			    pmuser_id := :pmUser_id
			);
		";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			throw new Exception("Ошибка при сохранении мероприятия реабилитации");
		}
		return $response;
	}

	/**
	 * Удаление мероприятия реабилитации
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	function deleteMeasuresRehab($data)
	{
		$params = ["MeasuresRehab_id" => $data["MeasuresRehab_id"]];
		$query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Msg\"
			from p_measuresrehab_del(measuresrehab_id := :MeasuresRehab_id);
		";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			throw new Exception("Ошибка при удалении мероприятия реабилитации");
		}
		return $response;
	}

	/**
	 * Добавление идентификатора мероприятия для экспорта
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	function addMeasuresRehabExportID($data)
	{
		$params = [
			"MeasuresRehab_id" => $data["MeasuresRehab_id"],
			"pmUser_id" => $data["pmUser_id"],
		];
		$query = "
			select
				MeasuresRehabExportID_id as \"MeasuresRehabExportID_id\"
			from v_MeasuresRehabExportID 
			where MeasuresRehab_id = :MeasuresRehab_id
			order by MeasuresRehabExportID_insDT desc
			limit 1
		";
		$result = $this->queryResult($query, $params);
		$resp = [];
		if(@$result[0]["MeasuresRehabExportID_id"] == null) {
			$query = "
				select
				    measuresrehabexportid_id as \"MeasuresRehabExportID_id\",
				    error_code as \"Error_Code\",
				    error_message as \"Error_Msg\"
				from p_measuresrehabexportid_ins(
				    measuresrehab_id := :MeasuresRehab_id,
				    pmuser_id := :pmUser_id
				);
			";
			$resp = $this->queryResult($query, $params);
			if (!is_array($resp)) {
				throw new Exception("Ошибка при подготовке мероприятия для экспорта");
			}
		}
		return $resp;
	}

	/**
	 * Формирование скрипта для экспорта данных мероприятий реабилитации
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	function genMeasuresRehabExportScript($data)
	{
		if (!is_array($data["MeasuresRehab_ids"])) {
			throw new Exception("Не передан список мероприятий для экспорта");
		}
		$ids = $data["MeasuresRehab_ids"];
		if (count($ids) == 0 || (count($ids) == 1 && empty($ids[0]))) {
			throw new Exception("Не передан список мероприятий для экспорта");
		}
		//Сохранение идентификаторов мероприятий для экспорта
		foreach ($ids as $id) {
			$funcParams = [
				"MeasuresRehab_id" => $id,
				"pmUser_id" => $data["pmUser_id"]
			];
			$resp = $this->addMeasuresRehabExportID($funcParams);
			if (!$this->isSuccessful($resp)) {
				return $resp;
			}
		}
		//Формирование скрипта для экспорта данных мероприятий
		$query = "
			select
			    SqlExportInsert as \"SqlExportInsert\",
			    error_code as \"Error_Code\",
			    error_message as \"Error_Msg\"
			from xp_ipraexportinsert(
			    isnottruncate := :IsNotTruncate
			);
		";
		$params = ["IsNotTruncate" => 1];
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			throw new Exception("Ошибка при формировании скрипта для экспорта данных");
		}
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}
		return $resp;
	}

	/**
	 * получение данных по "Мероприятия по медицинской реабилитации"
	 * @param $data
	 * @return array|bool
	 */
	function getMeasuresForMedicalRehabilitation($data)
	{
		$query = "
			select
				ES.EvnStick_id as \"EvnStick_id\",
				ES.Person_id as \"Person_id\",
				case
					when EPL.Diag_id is not null then EPL.Diag_id
					when EPS.Diag_id is not null then EPS.Diag_id
				end as \"Diag_id\",
				to_char(ESWR.EvnStickWorkRelease_begDT, '{$this->dateTimeForm104}') as \"EvnStick_setDate\",
				to_char(ESWR.EvnStickWorkRelease_endDT, '{$this->dateTimeForm104}') as \"EvnStick_disDate\",
				datediff('d', ESWR.EvnStickWorkRelease_begDT, (case when ESWR.EvnStickWorkRelease_endDT is not null then ESWR.EvnStickWorkRelease_endDT else tzgetdate() end)) as \"DayCount\",
				case
					when EPL.Diag_id is not null then (select diag_FullName from v_Diag where Diag_id = EPL.Diag_id)
					when EPS.Diag_id is not null then (select diag_FullName from v_Diag where Diag_id = EPS.Diag_id)
				end as \"Diag_Name\"
			from v_EvnStick ES
				left join v_EvnStickWorkRelease ESWR on ESWR.EvnStickBase_id = ES.EvnStick_id
				left join v_EvnPL EPL on EPL.EvnPL_id = ES.EvnStick_mid and EPL.Person_id = ES.Person_id and EPL.PersonEvn_id = ES.PersonEvn_id
				left join v_EvnPS EPS on EPS.EvnPS_id = ES.EvnStick_mid and EPS.Person_id = ES.Person_id and EPS.PersonEvn_id = ES.PersonEvn_id
			where ES.Person_id = :Person_id
			  and ES.EvnStick_setDT is not null
			  and datediff('m', case when ESWR.EvnStickWorkRelease_endDT is not null then ESWR.EvnStickWorkRelease_endDT else dbo.tzGetDate() end, tzGetDate()) <= 12
			union All
			select
				EMS.EvnMseStick_id as EvnStick_id,
				EMS.Person_id,
				EMS.Diag_id,
				to_char(EMS.EvnMseStick_begDT, '{$this->dateTimeForm104}') as EvnStick_setDate,
				to_char(EMS.EvnMseStick_endDT, '{$this->dateTimeForm104}') as EvnStick_disDate,
				datediff('d', EMS.EvnMseStick_begDT, case when EMS.EvnMseStick_endDT is not null then EMS.EvnMseStick_endDT else tzgetdate() end) as DayCount,
				(select diag_FullName from v_Diag where Diag_id = EMS.Diag_id) as Diag_Name
			from v_EvnMseStick EMS
			where EMS.Person_id = :Person_id
			  and EMS.EvnMseStick_begDT is not null
			  and datediff('m', (case when EMS.EvnMseStick_endDT is not null then EMS.EvnMseStick_endDT else tzGetDate() end), tzGetDate()) <= 12
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $data);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * Сохранение мероприятия реабилитации
	 * @param $data
	 * @return bool
	 * @throws Exception
	 */
	function saveMeasuresForMedicalRehabilitation($data)
	{
		if (!$data["pmUser_id"] || !$data["EvnPrescrMse_id"]) {
			return false;
		}
		$this->clearMeasuresFMR($data);
		if ((int)$data["EvnPrescrMse_IsFirstTime"] != 2) {
			return false;
		}
		if ($data["MeasuresForMedicalRehabilitation"]) {
			$MeasuresForMedicalRehabilitation = json_decode($data["MeasuresForMedicalRehabilitation"]);
			if (count($MeasuresForMedicalRehabilitation) > 0) {
				$data["MeasuresForMedicalRehabilitation"] = $MeasuresForMedicalRehabilitation;
				$this->addMeasuresFMR($data);
			}
		}
		return true;
	}

	/**
	 * добавление записи из формы «Мероприятия по медицинской реабилитации»
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function addMeasuresFMR($data)
	{
		if (!$data["pmUser_id"] || !$data["EvnPrescrMse_id"]) {
			return false;
		}
		$MeasuresForMedicalRehabilitation = $data["MeasuresForMedicalRehabilitation"];
		$countAdd = [];
		if (count($MeasuresForMedicalRehabilitation) > 0) {
			foreach ($MeasuresForMedicalRehabilitation as $MeasuresFMR) {
				$params = [
					"MeasuresRehabMSE_BegDate" => $MeasuresFMR->MeasuresRehabMSE_BegDate,
					"MeasuresRehabMSE_EndDate" => $MeasuresFMR->MeasuresRehabMSE_EndDate,
					"MeasuresRehabMSE_Type" => $MeasuresFMR->MeasuresRehabMSE_Type,
					"MeasuresRehabMSE_SubType" => $MeasuresFMR->MeasuresRehabMSE_SubType,
					"MeasuresRehabMSE_Name" => $MeasuresFMR->MeasuresRehabMSE_Name,
					"MeasuresRehabMSE_Result" => $MeasuresFMR->MeasuresRehabMSE_Result,
					"action" => "add",
					"MeasuresRehabMSE_IsExport" => $MeasuresFMR->MeasuresRehabMSE_IsExport,
					"EvnPrescrMse_id" => $data["EvnPrescrMse_id"],
					"pmUser_id" => $data["pmUser_id"]
				];
				$res = $this->saveMeasuresFMR($params);
				if ($res) $countAdd[] = $res;
			}
		}
		return $countAdd;
	}

	/**
	 * Сохранение мероприятия реабилитации
	 * @param $data
	 * @return array|bool|false
	 * @throws Exception
	 */
	function saveMeasuresFMR($data)
	{
		if (!$data["pmUser_id"] || !$data["EvnPrescrMse_id"] || !$data["MeasuresRehabMSE_Result"] || !$data["MeasuresRehabMSE_Name"]) {
			return false;
		}
		$params = [
			"MeasuresRehabMSE_id" => !empty($data["MeasuresRehabMSE_id"]) ? $data["MeasuresRehabMSE_id"] : null,
			"EvnPrescrMse_id" => !empty($data["EvnPrescrMse_id"]) ? $data["EvnPrescrMse_id"] : null,
			"MeasuresRehabMSE_Name" => !empty($data["MeasuresRehabMSE_Name"]) ? $data["MeasuresRehabMSE_Name"] : null,
			"MeasuresRehabMSE_Type" => !empty($data["MeasuresRehabMSE_Type"]) ? $data["MeasuresRehabMSE_Type"] : null,
			"MeasuresRehabMSE_SubType" => !empty($data["MeasuresRehabMSE_SubType"]) ? $data["MeasuresRehabMSE_SubType"] : null,
			"MeasuresRehabMSE_BegDate" => !empty($data["MeasuresRehabMSE_BegDate"]) ? $data["MeasuresRehabMSE_BegDate"] : null,
			"MeasuresRehabMSE_EndDate" => !empty($data["MeasuresRehabMSE_EndDate"]) ? $data["MeasuresRehabMSE_EndDate"] : null,
			"MeasuresRehabMSE_Result" => !empty($data["MeasuresRehabMSE_Result"]) ? $data["MeasuresRehabMSE_Result"] : null,
			"MeasuresRehabMSE_IsExport" => !empty($data["MeasuresRehabMSE_IsExport"]) ? $data["MeasuresRehabMSE_IsExport"] : null,
			"pmUser_id" => $data["pmUser_id"],
		];
		$procedure = (empty($params["MeasuresRehabMSE_id"])) ? "p_MeasuresRehabMSE_ins" : "p_MeasuresRehabMSE_upd";
		$selectString = "
		    measuresrehabmse_id as \"MeasuresRehabMSE_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Msg\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    measuresrehabmse_id := :MeasuresRehabMSE_id,
			    evnprescrmse_id := :EvnPrescrMse_id,
			    measuresrehabmse_name := :MeasuresRehabMSE_Name,
			    measuresrehabmse_type := :MeasuresRehabMSE_Type,
			    measuresrehabmse_subtype := :MeasuresRehabMSE_SubType,
			    measuresrehabmse_begdate := :MeasuresRehabMSE_BegDate,
			    measuresrehabmse_enddate := :MeasuresRehabMSE_EndDate,
			    measuresrehabmse_result := :MeasuresRehabMSE_Result,
			    measuresrehabmse_isexport := :MeasuresRehabMSE_IsExport,
			    pmuser_id := :pmUser_id
			);
		";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			throw new Exception("Ошибка при сохранении мероприятия реабилитации");
		}
		return $response;
	}

	/**
	 * удаление записи из формы «Мероприятия по медицинской реабилитации»
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	function deleteMeasuresForMedicalRehabilitation($data)
	{
		$params = ["MeasuresRehabMSE_id" => $data["MeasuresRehabMSE_id"]];
		$query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Msg\"
			from p_measuresrehabmse_del(measuresrehabmse_id := :MeasuresRehabMSE_id);
		";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			throw new Exception("Ошибка при удалении мероприятия по медицинской реабилитации");
		}
		return $response;
	}

	/**
	 * Очистить мероприятия  из формы «Мероприятия по медицинской реабилитации» загруженные из регистра ИПРА
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function clearMeasuresFMR($data)
	{
		if (!$data["EvnPrescrMse_id"]) {
			return false;
		}
		$countMeasuresRehabMSE = [];
		// получим список 
		$listMeasuresFMR_ID = $this->getIDListMeasuresFMR($data);
		// удалим записи с полученными значениями
		if (count($listMeasuresFMR_ID) > 0) {
			foreach ($listMeasuresFMR_ID as $MeasuresFMR) {
				if (isset($MeasuresFMR["MeasuresRehabMSE_id"])) {
					$countMeasuresRehabMSE[$MeasuresFMR["MeasuresRehabMSE_id"]] = $this->deleteMeasuresForMedicalRehabilitation($MeasuresFMR);
				}
			}
		}
		return $countMeasuresRehabMSE;
	}

	/**
	 * получим список «Мероприятия по медицинской реабилитации»
	 * @param $data
	 * @return array|bool|false
	 */
	function downloadIPRAinMeasuresFMR($data)
	{
		$listIPRARegistry = $this->getListIPRARegistry($data);
		return $listIPRARegistry;
	}

	/**
	 * получить список id мероприятий из формы «Мероприятия по медицинской реабилитации»
	 * @param $data
	 * @return array|bool|false
	 */
	function getIDListMeasuresFMR($data)
	{
		if (!$data["EvnPrescrMse_id"]) {return false;}
		$params = ["EvnPrescrMse_id" => $data["EvnPrescrMse_id"]];
		$query = "
			select MRM.MeasuresRehabMSE_id as \"MeasuresRehabMSE_id\"
			from MeasuresRehabMSE MRM
			where MRM.EvnPrescrMse_id = :EvnPrescrMse_id
		";
		$resp = $this->queryResult($query, $params);
		return $resp;
	}

	/**
	 * получить список незакрытых записей пациента в регистре ИПРА
	 * @param $data
	 * @return array|bool|false
	 */
	function getListIPRARegistry($data)
	{
		if (!$data["Person_id"]) {
			return false;
		}
		$params = ["Person_id" => $data["Person_id"]];
		$query = "
			select
				to_char(MR.MeasuresRehab_setDate, '{$this->dateTimeForm120}') as \"MeasuresRehab_setDate\",
				to_char(EV.Evn_disDT, '{$this->dateTimeForm120}') as \"Evn_disDT\",
				MRT.MeasuresRehabType_Name as \"MeasuresRehabType_Name\",
				case
					when RO.ReceptOtov_id is not null then 'Рецепт №'||RO.EvnRecept_Num||' '||RO.EvnRecept_Ser||'. '||D.Drug_Name
					when EU.EvnUsluga_id is not null then UC.UslugaComplex_Name
					else MR.MeasuresRehab_Name
				end as \"MeasuresRehab_Name\",
				MRST.MeasuresRehabSubType_Name as \"MeasuresRehabSubType_Name\",
				MRR.MeasuresRehabResult_Name as \"MeasuresRehabResult_Name\",
				R.Person_id as \"Person_id\",
				case 
					when MR.EvnUsluga_id is not null then 'usluga'
					when MR.Evn_id is not null then 'evn'
					when MR.ReceptOtov_id is not null then 'drug'
					else 'other'
				end as \"type\"
			from
				v_IPRARegistry R
				left join v_MeasuresRehab MR on MR.IPRARegistry_id = R.IPRARegistry_id
				left join v_MeasuresRehabType MRT on MRT.MeasuresRehabType_id = MR.MeasuresRehabType_id
				left join v_MeasuresRehabSubType MRST on MRST.MeasuresRehabSubType_id = MR.MeasuresRehabSubType_id
				left join v_MeasuresRehabResult MRR on MRR.MeasuresRehabResult_id = MR.MeasuresRehabResult_id
				left join v_EvnUsluga EU on EU.EvnUsluga_id = MR.EvnUsluga_id
				left join v_UslugaComplex UC on UC.UslugaComplex_id = EU.UslugaComplex_id
				left join ReceptOtov RO on RO.ReceptOtov_id = MR.ReceptOtov_id
				left join v_EvnRecept ER on ER.EvnRecept_id = RO.EvnRecept_id
				left join v_Drug D on D.Drug_id = ER.Drug_id
				left join v_Evn EV on EV.Evn_id = MR.Evn_id
			where R.Person_id = :Person_id
		";
		$resp = $this->queryResult($query, $params);
		return $resp;
	}
}
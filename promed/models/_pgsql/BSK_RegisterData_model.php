<?php
defined('BASEPATH') or die('No direct script access allowed');
/**
 * BSK_RegisterData_model - модель для работы с анкетами БСК
 * 
 * @package         BSK
 * @author
 * @version         01.12.2019
 */

class BSK_RegisterData_model extends swPgModel
{
	
	var $scheme = "dbo";
	
	/**
	 * comments
	 */
	function __construct()
	{
		parent::__construct();
	}


	/**
	 *  Объём талии в перцентелях
	 */
	function getWaistPercentel($data)
	{
		$params = array(
			'age' => $data['age'],
			'waist' => strtr($data['waist'], array(
				',' => '.'
			)),
			'Sex_id' => $data['Sex_id']
		);
		
		$query = "
			select 
				PercentileWaist_Percentile as \"prc\"
			from dbo.PercentileWaist
			where PercentileWaist_Age = :age
				and Sex_id = :Sex_id 
				and	PercentileWaist_Circle >= :waist
			limit 1
		";
		
		$result = $this->db->query($query, $params);
		
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Проставление признака просмотра анкеты
	 */
	function setIsBrowsed($data) {

		$params = array(
			'BSKRegistry_id' => $data['BSKRegistry_id']
		);

		$query = "UPDATE {$this->scheme}.BSKRegistry
				SET BSKRegistry_isBrowsed = 2
				WHERE BSKRegistry_id = :BSKRegistry_id";

		$result = $this->db->query($query, $params);

		if (!$result) {
			return array('success' => false, 'Error_Msg' => 'Ошибка при установлении признака просмотра анкеты');
		} else 
			return array('success' => true, 'Error_Msg' => '');

	}

	function loadBSKObjectTree($data) {
		$params = array(
			'Person_id' => $data['Person_id']
		);

		$query = "
			select distinct
				O.BSKObject_id as \"BSKObject_id\", 
				R.Person_id as \"Person_id\", 
				MT.MorbusType_Name as \"MorbusType_Name\",
				MT.MorbusType_id as \"MorbusType_id\",
				to_char(ps.Person_deadDT , 'dd.mm.yyyy') as \"Person_deadDT\",
				case 
					when O.BSKObject_SysNick = 'screening' then cast(riskGroup_screening.BSKRegistry_riskGroup as varchar(10))
					when O.BSKObject_SysNick = 'lung_hypert' then Rtrim(riskGroup_lung_hypert.BSKRegistryData_data)
					when O.BSKObject_SysNick = 'Arter_hypert' then Rtrim(riskGroup_Arter_hypert.BSKRegistryData_data) end as \"riskGroup\",
				to_char(LastAnket.BSKRegistry_setDate, 'yyyy-mm-dd') as \"BSKRegistry_setDate\"
			from v_PersonState PS
			inner join dbo.v_PersonRegister R on R.Person_id = PS.Person_id
			inner join dbo.MorbusType MT on MT.MorbusType_id = R.MorbusType_id
			inner join dbo.v_BSKObject O on O.MorbusType_id = R.MorbusType_id
			left join lateral (
				select BSKR.BSKRegistry_id, BSKR.BSKRegistry_setDate
				from dbo.v_BSKRegistry BSKR 
				where BSKR.MorbusType_id = O.MorbusType_id 
					and BSKR.Person_id = PS.Person_id 
					--and coalesce(BSKR.BSKRegistry_deleted,1) <> 2
				order by BSKR.BSKRegistry_setDate DESC, BSKR.BSKRegistry_id DESC
				limit 1
			) LastAnket on true
			--группа риска скрининг
			left join lateral (
				select BSKR.BSKRegistry_riskGroup
				from dbo.v_BSKRegistry BSKR 
				where BSKR.MorbusType_id = O.MorbusType_id 
					and BSKR.Person_id = PS.Person_id 
					and O.BSKObject_SysNick = 'screening'
					--and coalesce(BSKR.BSKRegistry_deleted,1) <> 2
				order by BSKR.BSKRegistry_setDate DESC, BSKR.BSKRegistry_id DESC
				limit 1
			) riskGroup_screening on true
			--группа риска Легочная гипертензия
			left join lateral (
				select BSKR.BSKRegistry_id
				from dbo.v_BSKRegistry BSKR 
				where BSKR.MorbusType_id = O.MorbusType_id 
					and BSKR.Person_id = PS.Person_id 
					and O.BSKObject_SysNick = 'lung_hypert'
					--and coalesce(BSKR.BSKRegistry_deleted,1) <> 2
				order by BSKR.BSKRegistry_setDate DESC, BSKR.BSKRegistry_id DESC
				limit 1
			) lung_hypert on true
			left join lateral (
				select coalesce(v.BSKObservElementValues_data,rd.BSKRegistryData_data) BSKRegistryData_data
				from dbo.v_BSKRegistryData rd
				left join dbo.v_BSKObservElementValues v on v.BSKObservElementValues_id = rd.BSKObservElementValues_id
				where rd.BSKRegistry_id = lung_hypert.BSKRegistry_id
				and rd.BSKObservElement_id = 151
				limit 1
			) riskGroup_lung_hypert on true
			--группа риска Артериальная гипертензия
			left join lateral (
				select BSKR.BSKRegistry_id
				from dbo.v_BSKRegistry BSKR 
				where BSKR.MorbusType_id = O.MorbusType_id 
					and BSKR.Person_id = PS.Person_id 
					and O.BSKObject_SysNick = 'Arter_hypert'
					--and coalesce(BSKR.BSKRegistry_deleted,1) <> 2
				order by BSKR.BSKRegistry_setDate DESC, BSKR.BSKRegistry_id DESC
				limit 1
			) Arter_hypert on true
			left join lateral (
				select coalesce(v.BSKObservElementValues_data,rd.BSKRegistryData_data) BSKRegistryData_data
				from dbo.v_BSKRegistryData rd
				left join dbo.v_BSKObservElementValues v on v.BSKObservElementValues_id = rd.BSKObservElementValues_id
				where rd.BSKRegistry_id = Arter_hypert.BSKRegistry_id
				and rd.BSKObservElement_id = 269
				limit 1
			) riskGroup_Arter_hypert on true
			where R.Person_id = :Person_id
			and O.BSKObject_SysNick in ('screening','lung_hypert','Arter_hypert','acs','ibs','hsn','aheart_defects','cheart_defects','heart_rhythm')
			order by O.BSKObject_id
		";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	function loadBSKObjectListTree($data)
	{
		$params = array(
			'Person_id' => $data['Person_id'],
			'MorbusType_id' => $data['MorbusType_id']
		);

		$query = "
			select 
				r.BSKRegistry_id as \"BSKRegistry_id\",
				r.Person_id as \"Person_id\",
				r.MorbusType_id as \"MorbusType_id\",
				to_char(r.BSKRegistry_setDate,'dd.mm.yyyy') as \"BSKRegistry_setDate\",
				r.BSKRegistry_riskGroup as \"BSKRegistry_riskGroup\",
				rd.BSKObservElementGroup_id as \"BSKObservElementGroup_id\",
				case when exists (
					select rLast.BSKRegistry_id
					from dbo.v_BSKRegistry rLast 
					where rLast.BSKRegistry_setDate > r.BSKRegistry_setDate
						and rLast.Person_id = r.Person_id
						and rLast.MorbusType_id = r.MorbusType_id
					limit 1
				) then 1 else 2 end as \"isLast\",
				to_char(r.BSKRegistry_setDate, 'yyyy-mm-dd') as \"BSKRegistry_setDateFormat\",
				o.BSKObject_id as \"BSKObject_id\"
			from dbo.v_BSKRegistry r
			inner join dbo.v_BSKObject o on o.MorbusType_id = r.MorbusType_id
			left join lateral (
				select el.BSKObservElementGroup_id
				from dbo.v_BSKRegistryData rd
				inner join dbo.v_BSKObservElement el on el.BSKObservElement_id = rd.BSKObservElement_id
				where rd.BSKRegistry_id = r.BSKRegistry_id
				and el.BSKObservElementGroup_id in (32,44)
				limit 1
			) rd on true
			where r.Person_id = :Person_id
				and r.MorbusType_id = :MorbusType_id
				--and coalesce(r.BSKRegistry_deleted,1) <> 2
			order by r.BSKRegistry_setDate DESC, r.BSKRegistry_id DESC
		";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	function getBSKRegistryFormTemplate($data) {
		$params = array(
			'Person_id' => $data['Person_id'],
			'MorbusType_id' => $data['MorbusType_id'],
			'BSKRegistry_id' => $data['BSKRegistry_id']
		);
		if(!isset($params['BSKRegistry_id'])) 
			$where = 'r.Person_id = :Person_id';
		else 
			$where = 'r.BSKRegistry_id = :BSKRegistry_id';

		$query = "
			select 
				ft.BSKRegistryFormTemplate_id as \"BSKRegistryFormTemplate_id\", 
				PersonRegister.PersonRegister_id as \"PersonRegister_id\", 
				PersonRegister.PersonRegister_setDate as \"PersonRegister_setDate\", 
				PersonRegister.PersonRegister_disDate as \"PersonRegister_disDate\",
				ob.BSKObject_SysNick as \"BSKObject_SysNick\",
				ob.BSKObject_id as \"BSKObject_id\", 
				ob.MorbusType_id as \"MorbusType_id\", 
				mt.MorbusType_Name as \"MorbusType_Name\",
				gr.BSKObservElementGroup_name as \"BSKObservElementGroup_name\", 
				gr.BSKObservElementGroup_id as \"BSKObservElementGroup_id\", 
				BSKRegistryFormTemplateData_GroupNum as \"BSKRegistryFormTemplateData_GroupNum\",
				el.BSKObservElement_name as \"BSKObservElement_name\", 
				el.BSKObservElement_id as \"BSKObservElement_id\", 
				el.BSKObservElement_pid as \"BSKObservElement_pid\", 
				el.BSKObservElement_rid as \"BSKObservElement_rid\",
				el.Unit_id as \"Unit_id\", 
				u.Unit_Name as \"Unit_Name\",
				el.BSKObservElementFormat_id as \"BSKObservElementFormat_id\",
				BSKRegistry.BSKRegistry_id as \"BSKRegistry_id\", 
				BSKRegistry.Person_id as \"Person_id\", 
				BSKRegistry.BSKRegistry_isBrowsed as \"BSKRegistry_isBrowsed\", 
				BSKRegistry.BSKRegistry_riskGroup as \"BSKRegistry_riskGroup\", 
				coalesce(case 
					when el.BSKObservElement_id = 273 and Isnumeric(BSKRegistry.BSKRegistryData_AnswerText) = 1 then 
					(select ReferenceECGResult_Name from dbo.ReferenceECGResult where cast(ReferenceECGResult_id as varchar) = BSKRegistry.BSKRegistryData_AnswerText limit 1)
					when el.BSKObservElement_id = 303 and BSKRegistry.BSKRegistryData_AnswerInt is not null then 
					(select cast(ccc.CmpCallCard_Numv as varchar) from dbo.v_CmpCallCard ccc where ccc.CmpCallCard_id = BSKRegistry.BSKRegistryData_AnswerInt limit 1)
					else BSKRegistry.BSKRegistryData_AnswerText end,case 
					when el.BSKObservElement_id = 273 and Isnumeric(BSKRegistry.BSKRegistryData_data) = 1 then 
					(select ReferenceECGResult_Name from dbo.ReferenceECGResult where cast(ReferenceECGResult_id as varchar) = BSKRegistry.BSKRegistryData_data limit 1)
					when el.BSKObservElement_id = 303 and Isnumeric(BSKRegistry.BSKRegistryData_data) = 1 then 
					(select cast(ccc.CmpCallCard_Numv as varchar) from dbo.v_CmpCallCard ccc where cast(ccc.CmpCallCard_id as varchar) = BSKRegistry.BSKRegistryData_data limit 1)
					when el.BSKObservElement_id in (276,271,272,274,302,413,308,270,311,312) then /*to_char(*/BSKRegistry.BSKRegistryData_data/*,'dd.mm.yyyy')*/
					else coalesce(BSKRegistry.BSKRegistryData_data,LAD.BSKRegistryData_data) end) as \"BSKRegistryData_data\",
				coalesce(to_char(BSKRegistry.BSKRegistryData_AnswerDT,'dd.mm.yyyy'),case 
					when el.BSKObservElement_id in (276,271,272,274,302,413,308,270,311,312) then /*to_char(*/BSKRegistry.BSKRegistryData_data/*,'dd.mm.yyyy')*/ end) as \"BSKRegistryData_AnswerDT\",
				coalesce(BSKRegistry.BSKRegistryData_AnswerInt, case 
					when el.BSKObservElement_id in (50,51,54,55,107,108,109,142,143,144,145,146,147,148,149,208,209,211,212,213,214,215,216,318,319,321,322,323,324,325,326) then CAST(coalesce(BSKRegistry.BSKRegistryData_data,LAD.BSKRegistryData_data) as bigint) end) as \"BSKRegistryData_AnswerInt\",
				case 
					when el.BSKObservElement_id in (276,271,272,274,302,413,308,270,311,312) 
						then coalesce(to_char(BSKRegistry.BSKRegistryData_AnswerDT,'HH24:MI'),Substring(BSKRegistry.BSKRegistryData_data,12,5)) end as \"BSKRegistryData_dataTime\",
				to_char(BSKRegistry.BSKRegistry_setDate,'dd.mm.yyyy') as \"BSKRegistry_setDateFormat\", 
				to_char(BSKRegistry.BSKRegistry_setDate,'yyyy-mm-dd') as \"BSKRegistry_setDate\",
				coalesce(el.BSKObservElement_minAge,0) as \"minAge\",
				coalesce(el.BSKObservElement_maxAge,999) as \"maxAge\",
				coalesce(el.Sex_id,el.BSKObservElement_Sex_id) as \"Sex_id\",
				el.BSKObservElement_stage as \"BSKObservElement_stage\",
				BSKRegistry.BSKObservElementValues_id as \"BSKObservElementValues_id\", 
				BSKRegistry.BSKObservElementValues_data as \"BSKObservElementValues_data\",
				BSKRegistry.BSKRegistryData_AnswerFloat as \"BSKRegistryData_AnswerFloat\", 
				BSKRegistry.BSKRegistryData_AnswerText as \"BSKRegistryData_AnswerText\",
				BSKRegistry.BSKRegistryData_id as \"BSKRegistryData_id\",
				el.BSKObservElement_IsEdit as \"isEdit\",
				valDiag.BSKObservElementValues_id as \"noDiag\",
				coalesce(to_char(BSKRegistry.BSKRegistry_nextDate,'dd.mm.yyyy'),case 
					when mt.MorbusType_id = 84 then
						case 
							when BSKRegistry.BSKRegistry_riskGroup = 1 then to_char(dateadd('MONTH',18,BSKRegistry.BSKRegistry_setDate),'dd.mm.yyyy')
							when BSKRegistry.BSKRegistry_riskGroup = 2 then to_char(dateadd('MONTH',12,BSKRegistry.BSKRegistry_setDate),'dd.mm.yyyy')
							when BSKRegistry.BSKRegistry_riskGroup = 3 then to_char(dateadd('MONTH',6,BSKRegistry.BSKRegistry_setDate),'dd.mm.yyyy') 
						end 
					else to_char(dateadd('MONTH',6,BSKRegistry.BSKRegistry_setDate),'dd.mm.yyyy') 
				end) as \"BSKRegistry_nextDate\"
			from dbo.BSKRegistryFormTemplate ft
			inner join dbo.BSKRegistryFormTemplateData td on td.BSKRegistryFormTemplate_id = ft.BSKRegistryFormTemplate_id
			inner join dbo.v_BSKObject ob on ob.BSKObject_id = ft.BSKObject_id
			inner join dbo.MorbusType mt on mt.MorbusType_id = ob.MorbusType_id
			inner join dbo.v_BSKObservElementGroup gr on gr.BSKObservElementGroup_id = td.BSKObservElementGroup_id
			inner join dbo.v_BSKObservElement el on el.BSKObservElement_id = td.BSKObservElement_id and gr.BSKObservElementGroup_id = el.BSKObservElementGroup_id
			left join lateral (
				select valDiagNo.BSKObservElementValues_id
				from dbo.v_BSKObservElementValues valDiag
				inner join dbo.v_BSKObservElementValues valDiagNo on valDiagNo.BSKObservElement_id = valDiag.BSKObservElement_id 
					and valDiagNo.BSKObservElementValues_data = 'Нет'
				where valDiag.BSKObservElement_id = el.BSKObservElement_id
					and valDiag.Diag_id is not null 
				limit 1
			) valDiag on true
			left join dbo.Unit u on u.Unit_id = el.Unit_id
						left join lateral (
							select 
								r.BSKRegistry_id, r.BSKRegistry_setDate, r.BSKRegistry_riskGroup, r.Person_id, rd.BSKRegistryData_data, rd.BSKRegistryData_AnswerDT,
								r.pmUser_insID, r.pmUser_updID, r.Lpu_id, r.BSKRegistry_isBrowsed, r.BSKRegistryFormTemplate_id, rd.BSKRegistryData_id,
								rd.BSKObservElementValues_id, val.BSKObservElementValues_data, rd.BSKRegistryData_AnswerInt, rd.BSKRegistryData_AnswerFloat, rd.BSKRegistryData_AnswerText, r.BSKRegistry_nextDate
							from (
								select 
									r.BSKRegistry_id, r.BSKRegistry_setDate, r.BSKRegistry_riskGroup, r.Person_id,
									coalesce(r.pmUser_insID,1) pmUser_insID, r.pmUser_updID, usr.Lpu_id, r.BSKRegistry_isBrowsed, r.BSKRegistryFormTemplate_id, r.BSKRegistry_nextDate
								from dbo.v_BSKRegistry r
								left join dbo.v_pmUserCache usr on usr.PMUser_id = r.pmUser_insID
								where {$where}
									and r.MorbusType_id = ob.MorbusType_id
									--and coalesce(r.BSKRegistry_deleted,1) <> 2
								order by r.BSKRegistry_setDate desc, r.BSKRegistry_id DESC
								limit 1
							) r
							--left join dbo.v_BSKRegistryData rd on r.BSKRegistry_id = rd.BSKRegistry_id and rd.BSKObservElement_id = td.BSKObservElement_id
							/*из-за неправильного сохранения приходится брать 1 запись*/
							left join lateral (
								select 
									rd.BSKRegistryData_data, 
									rd.BSKRegistryData_AnswerDT,
									rd.BSKRegistryData_id,
									rd.BSKObservElementValues_id,
									rd.BSKRegistryData_AnswerInt, 
									rd.BSKRegistryData_AnswerFloat, 
									rd.BSKRegistryData_AnswerText
								from dbo.v_BSKRegistryData rd 
								where r.BSKRegistry_id = rd.BSKRegistry_id and rd.BSKObservElement_id = td.BSKObservElement_id
								order by rd.BSKRegistryData_insDT desc
								limit 1
							) rd on true
							left join dbo.v_BSKObservElementValues val on val.BSKObservElementValues_id = rd.BSKObservElementValues_id
						) BSKRegistry on true
			--рост, вес, индекс массы тел, объём талии - если они были указаны ранее в том или ином предмете наблюдения
			left join lateral (
				select LAD.BSKRegistryData_data
				from dbo.getLastAnketData(:Person_id) LAD
				where ( (el.BSKObservElement_id in (107,142,208,318) and LAD.BSKObservElement_id in (107,142,208,318))
					or (el.BSKObservElement_id in (108,143,209,319) and LAD.BSKObservElement_id in (108,143,209,319))
					or (el.BSKObservElement_id in (109,0,211,321) and LAD.BSKObservElement_id in (109,0,211,321))
					or (el.BSKObservElement_id in (110,172,210,320) and LAD.BSKObservElement_id in (110,172,210,320)) )
				limit 1
			) LAD on true
			inner join lateral (
				select PR.PersonRegister_id, PR.PersonRegister_setDate, PR.PersonRegister_disDate
				from v_PersonRegister PR
				where PR.Person_id = :Person_id
					and PR.MorbusType_id = :MorbusType_id
				order by PR.PersonRegister_setDate desc, PR.PersonRegister_disDate
				limit 1
			) PersonRegister on true
			where 
				ob.MorbusType_id = :MorbusType_id
				and (ft.BSKRegistryFormTemplate_id = BSKRegistry.BSKRegistryFormTemplate_id or (BSKRegistry.BSKRegistryFormTemplate_id is null and ft.BSKRegistryFormTemplate_EndDT is null)) --Шаблон или из существующей анкеты или незакрытый
			order by ft.BSKRegistryFormTemplate_ObservNum, td.BSKRegistryFormTemplateData_GroupNum, td.BSKRegistryFormTemplateData_ElementNum 
		";
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	function getBSKRegistryElementValues($data) {
		$params = array(
			'MorbusType_id' => $data['MorbusType_id']
		);

		$query = "
			select 
				ElVal.BSKObservElementValues_id as \"BSKObservElementValues_id\",
				ElVal.BSKObservElementValues_data as \"BSKObservElementValues_data\",
				El.BSKObservElement_id as \"BSKObservElement_id\",
				ElGr.BSKObservElementGroup_id as \"BSKObservElementGroup_id\",
				Ob.BSKObject_id as \"BSKObject_id\",
				Ob.MorbusType_id as \"MorbusType_id\",
				ElSign.BSKObservElementSign_id as \"BSKObservElementSign_id\",
				ElSign.BSKObservElementSign_name as \"BSKObservElementSign_name\",
				ElVal.Diag_id as \"Diag_id\",
				Dict.BSKObservDict_id as \"BSKObservDict_id\",
				Dict.BSKObservDict_name as \"BSKObservDict_name\",
				ECGResult.ReferenceECGResult_id as \"ReferenceECGResult_id\", 
				ECGResult.ReferenceECGResult_Name as \"ReferenceECGResult_Name\",
				Lpu.Lpu_id as \"Lpu_id\",
				Lpu.Lpu_Nick as \"Lpu_Nick\"
			from dbo.v_BSKObservElement El
			inner join dbo.v_BSKObservElementGroup ElGr on ElGr.BSKObservElementGroup_id = El.BSKObservElementGroup_id 
				and (El.BSKObservElementFormat_id = 8 /*or (ElGr.BSKObject_id = 11 and El.BSKObservElement_id = 400)*/)
				and El.BSKObservElementGroup_id not in (30,31)
			inner join dbo.v_BSKObject Ob on Ob.BSKObject_id = ElGr.BSKObject_id
			left join dbo.v_BSKObservElementValues ElVal on El.BSKObservElement_id = ElVal.BSKObservElement_id 
				and ElVal.BSKObservElementValues_data <> 'empty'
			left join dbo.BSKObservElementSign ElSign on ElSign.BSKObservElementSign_id = ElVal.BSKObservElementSign_id
			left join dbo.BSKObservDict Dict on Dict.BSKObservDict_id = El.BSKObservDict_id 
				and ElVal.BSKObservElementValues_id is null
			left join dbo.ReferenceECGResult ECGResult on Dict.BSKObservDict_name = 'ReferenceECGResult'
			left join v_Lpu Lpu on Dict.BSKObservDict_name = 'Lpu' 
				and coalesce(Lpu.Lpu_endDate, GETDATE()) >= GETDATE()
			where Ob.MorbusType_id = :MorbusType_id
				--and coalesce(El.BSKObservElement_Deleted,1) = 1
				--and coalesce(ElVal.BSKObservElementValues_Deleted,1) = 1
		";
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	function getPersonElementValuesDiag($data) {
		$params = array(
			'Person_id' => $data['Person_id'],
			'MorbusType_id' => $data['MorbusType_id']
		);

		$query = "
			SELECT 
				D.Evn_setDate as \"Evn_setDate\",
				D.Diag_id as \"Diag_id\",
				D.Diag_Code as \"Diag_Code\",
				coalesce(D.Diag_FullName,val.BSKObservElementValues_data,'Нет') as \"Diag_FullName\", 
				el.BSKObservElement_id as \"BSKObservElement_id\", 
				coalesce(D.BSKObservElementValues_id, val.BSKObservElementValues_id,0) as \"BSKObservElementValues_id\"
			FROM dbo.v_BSKObservElement el 
			inner join dbo.v_BSKObservElementGroup gr on gr.BSKObservElementGroup_id = el.BSKObservElementGroup_id
			inner join dbo.v_BSKObject ob on ob.BSKObject_id = gr.BSKObject_id
			left join lateral (
				select 
					Evn.Evn_setDate,
					Evn.Diag_id,
					Evn.Diag_Code,
					Evn.Diag_FullName,
					v.BSKObservElementValues_id
				from (
					Select  
						E.Person_id, 
						D.Diag_Code,D.Diag_id,
						D.Diag_Name,D.Diag_FullName, 
						E.EvnSection_insDT as Evn_insDT,
						E.EvnSection_setDate as Evn_setDate
					from v_EvnSection E
					inner join dbo.v_Diag D on D.Diag_id = E.Diag_id
					where E.Person_id = :Person_id 
					union all
					Select
						EPL.Person_id, 
						D.Diag_Code,D.Diag_id,
						D.Diag_Name,D.Diag_FullName, 
						EPL.EvnPL_insDT,
						EPL.EvnPL_setDT as Evn_setDate
					from v_EvnPL EPL	
					inner join dbo.v_Diag D on D.Diag_id = EPL.Diag_id
					where EPL.Person_id = :Person_id
				) as Evn
				inner join dbo.v_BSKObservElementValues v on Evn.Diag_id = v.Diag_id and v.BSKObservElement_id = el.BSKObservElement_id
				order by Evn.Evn_setDate
				limit 1
			) as D on true
			left join lateral (
				select val.BSKObservElementValues_id, val.BSKObservElementValues_data, val.BSKObservElement_id
				from dbo.v_BSKObservElementValues val
				where val.BSKObservElement_id = el.BSKObservElement_id and Rtrim(Ltrim(val.BSKObservElementValues_data)) = 'Нет'
				limit 1
			) val on true
			inner join lateral (
				select val.BSKObservElementValues_id, val.BSKObservElementValues_data
				from dbo.v_BSKObservElementValues valD
				where valD.BSKObservElement_id = val.BSKObservElement_id and valD.Diag_id is not null
				limit 1
			) valD on true
			where ob.MorbusType_id = :MorbusType_id
		";
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	function getBSKObjectWithoutAnket($data) {
		$params = array(
			'MorbusType_id' => $data['MorbusType_id'],
			'Person_id' => $data['Person_id'],
		);

		$query = "
			select 
				to_char(pr.PersonRegister_setDate, 'yyyy-mm-dd') as \"PersonRegister_setDate\",
				pr.MorbusType_id as \"MorbusType_id\",
				mt.MorbusType_Name as \"MorbusType_Name\",
				pr.Person_id as \"Person_id\"
			from v_PersonRegister pr
			inner join dbo.MorbusType mt on mt.MorbusType_id = pr.MorbusType_id
			where Person_id = :Person_id
				and pr.MorbusType_id = :MorbusType_id
			order by pr.PersonRegister_setDate desc
			limit 1
		";
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	function loadBSKEvnGrid($data) {
		$params = array(
			'MorbusType_id' => $data['MorbusType_id'],
			'Person_id' => $data['Person_id'],
		);
		switch ($data['MorbusType_id']) {
			//ХСН
			case 110: 
				$filterDiag_Code = "'I50.0','I50.1','I50.9'";
				$query = "
					select distinct
						--to_char(pr.PersonRegister_setDate, 'yyyy.mm.dd') PersonRegister_setDate,
						pr.MorbusType_id as \"MorbusType_id\",
						Evn.Person_id as \"Person_id\",
						Evn.Evn_id as \"Evn_id\",
						to_char(Evn.EvnDiagPS_setDate,'dd.mm.yyyy') as \"EvnDiagPS_setDate\",
						Evn.Diag_id as \"Diag_id\",
						Evn.Diag_FullName as \"Diag_FullName\",
						Evn.Lpu_id as \"Lpu_id\",
						Evn.Lpu_Nick as \"Lpu_Nick\",
						Evn.LpuSection_id as \"LpuSection_id\",
						Evn.LpuSection_Name as \"LpuSection_Name\",
						Evn.MedPersonal_id as \"MedPersonal_id\",
						Evn.Person_Fio as \"Person_Fio\",
						Evn.HSNStage_id as \"HSNStage_id\",
						Evn.HSNStage_Name as \"HSNStage_Name\",
						Evn.HSNFuncClass_id as \"HSNFuncClass_id\",
						Evn.HSNFuncClass_Name as \"HSNFuncClass_Name\"
					from v_PersonRegister pr 
					left join (
						select 
							ps.Person_id,
							ps.EvnPS_id Evn_id,
							case when d.Diag_Code in ({$filterDiag_Code}) then es.EvnSection_setDate else DiagPS.EvnDiagPS_setDate end EvnDiagPS_setDate,
							case when d.Diag_Code in ({$filterDiag_Code}) then es.Diag_id else DiagPS.Diag_id end Diag_id,
							case when d.Diag_Code in ({$filterDiag_Code}) then d.Diag_FullName else DiagPS.Diag_FullName end Diag_FullName,
							Lpu.Lpu_id,
							Lpu.Lpu_Nick,
							LS.LpuSection_id,
							LS.LpuSection_Name,
							mp.MedPersonal_id,
							mp.Person_Fio,
							HSNStage.HSNStage_id,
							HSNStage.HSNStage_Name,
							HSNFuncClass.HSNFuncClass_id,
							HSNFuncClass.HSNFuncClass_Name
						from v_EvnPS ps
						inner join v_EvnSection es on es.EvnSection_pid = ps.EvnPS_id
						inner join v_DiagHSNDetails HSNDet on HSNDet.Evn_id = es.EvnSection_id
						left join HSNStage on HSNStage.HSNStage_id = HSNDet.HSNStage_id
						left join HSNFuncClass on HSNFuncClass.HSNFuncClass_id = HSNDet.HSNFuncClass_id
						left join v_Diag d on d.Diag_id = ps.Diag_id 
						left join lateral (
							select DiagPS.EvnDiagPS_setDate, dPS.Diag_id, dPS.Diag_FullName
							from v_EvnDiagPS DiagPS
							inner join v_Diag dPS on dPS.Diag_id = DiagPS.Diag_id
							where DiagPS.EvnDiagPS_pid = es.EvnSection_id
								and DiagPS.DiagSetClass_id = 2
								and dPS.Diag_Code in ({$filterDiag_Code})
							order by DiagPS.EvnDiagPS_setDate
							limit 1
						) DiagPS on true
						inner join v_Lpu Lpu on Lpu.Lpu_id = es.Lpu_id
						inner join v_LpuSection LS on LS.LpuSection_id = es.LpuSection_id
						left join v_MedPersonal mp on mp.MedPersonal_id = es.MedPersonal_id and mp.Lpu_id = es.Lpu_id
						where ps.Person_id = :Person_id
						UNION ALL
						select 
							pl.Person_id,
							pl.EvnPL_id Evn_id,
							es.EvnVizitPL_setDate EvnDiagPS_setDate,
							case when d.Diag_Code in ({$filterDiag_Code}) then es.Diag_id else dPS.Diag_id end Diag_id,
							case when d.Diag_Code in ({$filterDiag_Code}) then d.Diag_FullName else dPS.Diag_FullName end Diag_FullName,
							Lpu.Lpu_id,
							Lpu.Lpu_Nick,
							LS.LpuSection_id,
							LS.LpuSection_Name,
							mp.MedPersonal_id,
							mp.Person_Fio,
							HSNStage.HSNStage_id,
							HSNStage.HSNStage_Name,
							HSNFuncClass.HSNFuncClass_id,
							HSNFuncClass.HSNFuncClass_Name
						from v_EvnPL pl
						inner join v_EvnVizitPL es on es.EvnVizitPL_pid = pl.EvnPL_id and es.EvnVizitPL_Index = 0
						inner join v_DiagHSNDetails HSNDet on HSNDet.Evn_id = es.EvnVizitPL_id
						left join HSNStage on HSNStage.HSNStage_id = HSNDet.HSNStage_id
						left join HSNFuncClass on HSNFuncClass.HSNFuncClass_id = HSNDet.HSNFuncClass_id
						left join v_Diag d on d.Diag_id = pl.Diag_id 
						left join v_Diag dPS on dPS.Diag_id = es.Diag_agid and dPS.Diag_Code in ({$filterDiag_Code})
						inner join v_Lpu Lpu on Lpu.Lpu_id = es.Lpu_id
						left join v_LpuSection LS on LS.LpuSection_id = es.LpuSection_id
						left join v_MedPersonal mp on mp.MedPersonal_id = es.MedPersonal_id and mp.Lpu_id = es.Lpu_id
						where pl.Person_id = :Person_id
					) Evn on evn.Person_id = pr.Person_id
					where pr.Person_id = :Person_id
						and pr.MorbusType_id = :MorbusType_id
				";
				$result = $this->db->query($query, $params);

				if (is_object($result)) {
					return $result->result('array');
				} else {
					return false;
				}		
			break;
			//Приобретённые пороки сердца
			case 111: 
				$filterDiag_Code = "'I05.0','I05.1','I05.2','I06.0','I06.1','I06.2','I07.0','I07.1','I07.2','I08.0','I08.1',
				'I08.2','I08.3','I08.8','I34.0','I34.1','I34.2','I34.8','I35.0','I35.1','I35.2','I35.8','I36.0','I36.1','I36.2','I36.8','I37.0','I37.1','I37.2','I37.8','I33.0'";
			break;
			//Врождённые пороки сердца
			case 112: 
				$filterDiag_Code = "'Q20.0','Q20.1','Q20.2','Q20.3','Q20.4','Q20.5','Q20.6','Q20.8','Q21.0',
				'Q21.1','Q21.2','Q21.3','Q21.4','Q21.8','Q22.0','Q22.1','Q22.2','Q22.3','Q22.4','Q22.5','Q22.6','Q22.8','Q23.0','Q23.1','Q23.2','Q23.3',
				'Q23.4','Q23.8','Q24.0','Q24.1','Q24.2','Q24.3','Q24.4','Q24.5','Q24.6','Q24.8','Q25.0','Q25.1','Q25.2','Q25.3','Q25.4','Q25.5','Q25.6',
				'Q25.7','Q25.8','Q26.0','Q26.1','Q26.2','Q26.3','Q26.4','Q26.5','Q26.6','Q26.8'";
			break;
			default:
				return false;
			break;
		}

		$query = "
			select 
				to_char(pr.PersonRegister_setDate, 'yyyy.mm.dd') as \"PersonRegister_setDate\",
				pr.MorbusType_id as \"MorbusType_id\",
				Evn.Person_id as \"Person_id\",
				Evn.Evn_id as \"Evn_id\",
				to_char(Evn.EvnDiagPS_setDate,'dd.mm.yyyy') as \"EvnDiagPS_setDate\",
				Evn.Diag_id as \"Diag_id\",
				Evn.Diag_FullName as \"Diag_FullName\",
				Evn.Lpu_id as \"Lpu_id\",
				Evn.Lpu_Nick as \"Lpu_Nick\",
				Evn.LpuSection_id as \"LpuSection_id\",
				Evn.LpuSection_Name as \"LpuSection_Name\",
				Evn.MedPersonal_id as \"MedPersonal_id\",
				Evn.Person_Fio as \"Person_Fio\"
			from v_PersonRegister pr 
			left join (
				select 
					ps.Person_id,
					ps.EvnPS_id Evn_id,
					case when d.Diag_Code in ({$filterDiag_Code}) then es.EvnSection_setDate else DiagPS.EvnDiagPS_setDate end EvnDiagPS_setDate,
					case when d.Diag_Code in ({$filterDiag_Code}) then es.Diag_id else DiagPS.Diag_id end Diag_id,
					case when d.Diag_Code in ({$filterDiag_Code}) then d.Diag_FullName else DiagPS.Diag_FullName end Diag_FullName,
					Lpu.Lpu_id,
					Lpu.Lpu_Nick,
					LS.LpuSection_id,
					LS.LpuSection_Name,
					mp.MedPersonal_id,
					mp.Person_Fio
				from v_EvnPS ps
				inner join v_EvnSection es on es.EvnSection_pid = ps.EvnPS_id and es.EvnSection_Index = 0
				left join v_Diag d on d.Diag_id = ps.Diag_id 
				left join lateral (
					select DiagPS.EvnDiagPS_setDate, dPS.Diag_id, dPS.Diag_FullName
					from v_EvnDiagPS DiagPS
					inner join v_Diag dPS on dPS.Diag_id = DiagPS.Diag_id
					where DiagPS.EvnDiagPS_rid = ps.EvnPS_rid
						and dPS.Diag_Code in ({$filterDiag_Code})
						and DiagPS.DiagSetClass_id = 3
					order by DiagPS.EvnDiagPS_setDate
					limit 1
				) DiagPS on true
				inner join v_Lpu Lpu on Lpu.Lpu_id = es.Lpu_id
				inner join v_LpuSection LS on LS.LpuSection_id = es.LpuSection_id
				left join v_MedPersonal mp on mp.MedPersonal_id = es.MedPersonal_id and mp.Lpu_id = es.Lpu_id
				where ps.Person_id = :Person_id
					and (d.Diag_Code in ({$filterDiag_Code})
						or DiagPS.Diag_id is not null)
				UNION ALL
				select 
					pl.Person_id,
					pl.EvnPL_id Evn_id,
					case when d.Diag_Code in ({$filterDiag_Code}) then es.EvnVizitPL_setDate else DiagPS.EvnDiagPLSop_setDate end EvnDiagPS_setDate,
					case when d.Diag_Code in ({$filterDiag_Code}) then es.Diag_id else DiagPS.Diag_id end Diag_id,
					case when d.Diag_Code in ({$filterDiag_Code}) then d.Diag_FullName else DiagPS.Diag_FullName end Diag_FullName,
					Lpu.Lpu_id,
					Lpu.Lpu_Nick,
					LS.LpuSection_id,
					LS.LpuSection_Name,
					mp.MedPersonal_id,
					mp.Person_Fio
				from v_EvnPL pl
				inner join v_EvnVizitPL es on es.EvnVizitPL_pid = pl.EvnPL_id and es.EvnVizitPL_Index = 0
				left join v_Diag d on d.Diag_id = pl.Diag_id 
				left join lateral (
					select DiagPS.EvnDiagPLSop_setDate, dPS.Diag_id, dPS.Diag_FullName
					from v_EvnDiagPLSop DiagPS
					inner join v_Diag dPS on dPS.Diag_id = DiagPS.Diag_id
					where DiagPS.EvnDiagPLSop_rid = pl.EvnPL_rid
						and dPS.Diag_Code in ({$filterDiag_Code})
					order by DiagPS.EvnDiagPLSop_setDate
					limit 1
				) DiagPS on true
				inner join v_Lpu Lpu on Lpu.Lpu_id = es.Lpu_id
				left join v_LpuSection LS on LS.LpuSection_id = es.LpuSection_id
				left join v_MedPersonal mp on mp.MedPersonal_id = es.MedPersonal_id and mp.Lpu_id = es.Lpu_id
				where pl.Person_id = :Person_id
					and (d.Diag_Code in ({$filterDiag_Code})
						or DiagPS.Diag_id is not null)
			) Evn on evn.Person_id = pr.Person_id
			where pr.Person_id = :Person_id
				and pr.MorbusType_id = :MorbusType_id
		";
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	public function saveBSKRegistry($data, $BSKRegistry, $BSKRegistryData) {
		//Дата следующего осмотра автоматически должна рассчитываться от значения в поле «Дата анкетирования» по алгоритму #196897
		if ((int)$BSKRegistry['MorbusType_id'] == 84) {
			$BSKRegistry['BSKRegistry_nextDate'] = $this->getNextDateAnket($BSKRegistry);
		}

		$response = [
			'success' => true,
			'Error_Msg' => '',
			'BSKRegistry_id' => null
		];

		try {
			$this->beginTransaction();

			$result = $this->getFirstRowFromQuery("
				select 
					BSKRegistry_id as \"BSKRegistry_id\", 
					Error_Code as \"Error_Code\", 
					Error_Message as \"Error_Msg\"
				from dbo.p_BSKRegistry_ins
				(
					BSKRegistry_riskGroup := :BSKRegistry_riskGroup,
					BSKRegistry_setDate := :BSKRegistry_setDate,
					MorbusType_id := :MorbusType_id,
					Person_id := :Person_id,
					BSKRegistry_isBrowsed := :isBrowsed,
					CmpCallCard_id := :CmpCallCard_id,
					pmUser_id := :pmUser_id,
					PersonRegister_id := :PersonRegister_id,
					BSKRegistryFormTemplate_id := :BSKRegistryFormTemplate_id,
					BSKRegistry_nextDate := :BSKRegistry_nextDate
				)
	
			", [
				'pmUser_id' => $data['pmUser_id'],
				'BSKRegistry_riskGroup' => $BSKRegistry['BSKRegistry_riskGroup'],
				'MorbusType_id' => $BSKRegistry['MorbusType_id'],
				'Person_id' => $BSKRegistry['Person_id'],
				'BSKRegistry_setDate' => $BSKRegistry['BSKRegistry_setDate'],
				'BSKRegistry_nextDate' => $BSKRegistry['BSKRegistry_nextDate'],
				'PersonRegister_id' => $BSKRegistry['PersonRegister_id'],
				'BSKRegistryFormTemplate_id' => $BSKRegistry['BSKRegistryFormTemplate_id'],
				'isBrowsed' => $BSKRegistry['MorbusType_id']==19 ? 1 : 2,
				'CmpCallCard_id' => isset($data['CmpCallCard_id'])?$data['CmpCallCard_id']:NULL
			]);

			if ( $result === false || !is_array($result) || count($result) == 0 ) {
				throw new Exception('Ошибка при добавлении пациента в регистр БСК');
			}
			else if ( !empty($result['Error_Msg']) ) {
				throw new Exception($result['Error_Msg']);
			}

			$response['BSKRegistry_id'] = $result['BSKRegistry_id'];

			if ( is_array($BSKRegistryData) && count($BSKRegistryData) > 0 && !empty($response['BSKRegistry_id']) ) {
				foreach ($BSKRegistryData as $k => $v) {

					$result = $this->getFirstRowFromQuery("
						select 
							BSKRegistryData_id as \"BSKRegistryData_id\", 
							Error_Code as \"Error_Code\", 
							Error_Message as \"Error_Message\"
						from dbo.p_BSKRegistryData_ins
						(
							BSKRegistry_id  := :BSKRegistry_id,
							BSKObservElement_id := :BSKObservElement_id,
							BSKObservElementValues_id := :BSKObservElementValues_id,
							BSKRegistryData_AnswerText := :BSKRegistryData_AnswerText,
							BSKRegistryData_AnswerInt := :BSKRegistryData_AnswerInt,
							BSKRegistryData_AnswerFloat := :BSKRegistryData_AnswerFloat,
							BSKRegistryData_AnswerDT := :BSKRegistryData_AnswerDT,
							pmUser_id := :pmUser_id
						)
						", [
							'BSKRegistry_id' => $response['BSKRegistry_id'],
							'pmUser_id' => $data['pmUser_id'],
							'BSKObservElement_id' => $v['BSKObservElement_id'],
							'BSKObservElementValues_id' => $v['BSKObservElementValues_id'],
							'BSKRegistryData_AnswerText' => $v['BSKRegistryData_AnswerText'],
							'BSKRegistryData_AnswerInt' => $v['BSKRegistryData_AnswerInt'],
							'BSKRegistryData_AnswerFloat' => $v['BSKRegistryData_AnswerFloat'],
							'BSKRegistryData_AnswerDT' => $v['BSKRegistryData_AnswerDT']
						]);

					// тут можно прикрутить обработку ответа, но в изначальном коде ее не было
					if ( $result === false || !is_array($result) || count($result) == 0 ) {
						throw new Exception('Ошибка при добавлении анкеты пациента в регистр БСК');
					}
					else if ( !empty($result['Error_Msg']) ) {
						throw new Exception($result['Error_Msg']);
					}
				}
			}

			// Рассылка уведомлений
			$resUsers =  $this->queryResult("
				SELECT puc.pmUser_id as \"pmUser_id\"
				FROM v_PersonCard pc 
					INNER JOIN v_LpuRegion lp ON lp.LpuRegion_id = pc.LpuRegion_id
					INNER JOIN v_MedStaffRegion msr ON lp.LpuRegion_id = msr.LpuRegion_id
						and msr.MedStaffRegion_isMain = 2
						and (msr.MedStaffRegion_begDate is null or msr.MedStaffRegion_begDate <= dbo.tzGetdate())
						and (msr.MedStaffRegion_endDate is null or msr.MedStaffRegion_endDate >= dbo.tzGetdate())
					LEFT JOIN v_MedStaffFact msf on msf.MedStaffFact_id = msr.MedStaffFact_id
					INNER JOIN dbo.pmUserCache puc on puc.MedPersonal_id = coalesce(msf.MedPersonal_id, msr.MedPersonal_id)
				WHERE pc.LpuAttachType_id = 1
					and pc.Person_id = :Person_id
			", [
				'Person_id' => $BSKRegistry['Person_id']
			]);

			if ( is_array($resUsers) && count($resUsers) > 0 ) {
				$userList = [];

				foreach ( $resUsers as $row ) {
					if ( !in_array($row['pmUser_id'], $userList) ) {
						$userList[] = $row['pmUser_id'];
					}
				}

				$persData = $this->getFirstRowFromQuery("
					SELECT 
						ps.Person_SurName as \"Person_SurName\",
						ps.Person_FirName as \"Person_FirName\",
						ps.Person_SecName as \"Person_SecName\",
						to_char(ps.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\"
					FROM 
						v_PersonState ps
					WHERE 
						ps.Person_id = :Person_id
					limit 1
				", [
					'Person_id' => $BSKRegistry['Person_id']
				]);

				if ( $persData === false || !is_array($persData) || count($persData) == 0 ) {
					throw new Exception('Ошибка при получении данных пациента');
				}

				$datasend = [
					'Message_id' => null,
					'Message_pid' => null,
					'Message_Subject' => 'Пациент включен в регистр БСК',
					'Message_Text' => "Пациент вашего участка " . $persData['Person_SurName'] . " ". $persData['Person_FirName'] . " " . $persData['Person_SecName'] . ", дата рождения " . $persData['Person_BirthDay'] . " включен в регистр БСК",
					'pmUser_id' => $data['pmUser_id'],
					'UserSend_ID' => 0,
					'Lpus' => '',
					'pmUser_Group' => '',
					'Message_isSent' => 1,
					'NoticeType_id' => 5,
					'Message_isFlag' => 1,
					'Message_isDelete' => 1,
					'RecipientType_id' => 1,
					'action' => 'ins',
					'MessageRecipient_id' => null,
					'Message_isRead' => null,
				];

				$this->load->model("Messages_model", "msmodel");

				$result = $this->msmodel->insMessage($datasend);

				if ( !is_array($result) || count($result) == 0 || empty($result[0]['Message_id']) ) {
					throw new Exception('Ошибка при добавлении сообщения');
				}
				else if ( !empty($result[0]['Error_Msg']) ) {
					throw new Exception($result[0]['Error_Msg']);
				}

				$Message_id = $result[0]['Message_id'];

				foreach ( $userList as $pmUser_id ) {
					$result = $this->msmodel->insMessageLink($Message_id, $pmUser_id, $datasend);

					if ( !is_array($result) || count($result) == 0 || empty($result[0]['MessageLink_id']) ) {
						throw new Exception('Ошибка при добавлении сообщения');
					}
					else if ( !empty($result[0]['Error_Msg']) ) {
						throw new Exception($result[0]['Error_Msg']);
					}

					$result = $this->msmodel->sendMessage($datasend, $pmUser_id, $Message_id);

					if ( !is_array($result) || count($result) == 0 || empty($result[0]['MessageRecipient_id']) ) {
						throw new Exception('Ошибка при добавлении сообщения');
					}
					else if ( !empty($result[0]['Error_Msg']) ) {
						throw new Exception($result[0]['Error_Msg']);
					}
				}
			}

			$this->commitTransaction();
		}
		catch ( Exception $e ) {
			$this->rollbackTransaction();
			$response['success'] = false;
			$response['Error_Msg'] = $e->getMessage();
		}

		return [ $response ];
	}

	function updateBSKRegistry($data, $BSKRegistry, $BSKRegistryData) {
		//Дата следующего осмотра автоматически должна рассчитываться от значения в поле «Дата анкетирования» по алгоритму #196897
		if ((int)$BSKRegistry['MorbusType_id'] == 84) {
			$BSKRegistry['BSKRegistry_nextDate'] = $this->getNextDateAnket($BSKRegistry);
		}

		$response = [
			'success' => true,
			'Error_Msg' => '',
			'BSKRegistry_id' => null
		];

		try {
			$this->beginTransaction();

			$result = $this->getFirstRowFromQuery("
				select 
					BSKRegistry_id as \"BSKRegistry_id\", 
					Error_Code as \"Error_Code\", 
					Error_Message as \"Error_Msg\"
				from dbo.p_BSKRegistry_upd
				(
					BSKRegistry_id := :BSKRegistry_id,
					BSKRegistry_riskGroup := :BSKRegistry_riskGroup,
					BSKRegistry_setDate := :BSKRegistry_setDate,
					BSKRegistry_nextDate := :BSKRegistry_nextDate,
					MorbusType_id := :MorbusType_id,
					Person_id := :Person_id,
					BSKRegistry_isBrowsed := :isBrowsed,
					CmpCallCard_id := :CmpCallCard_id,
					pmUser_id := :pmUser_id,
					PersonRegister_id := :PersonRegister_id,
					BSKRegistryFormTemplate_id := :BSKRegistryFormTemplate_id
				)
	
			", [
				'BSKRegistry_id' => $BSKRegistry['BSKRegistry_id'],
				'pmUser_id' => $data['pmUser_id'],
				'BSKRegistry_riskGroup' => $BSKRegistry['BSKRegistry_riskGroup'],
				'MorbusType_id' => $BSKRegistry['MorbusType_id'],
				'Person_id' => $BSKRegistry['Person_id'],
				'BSKRegistry_setDate' => $BSKRegistry['BSKRegistry_setDate'],
				'BSKRegistry_nextDate' => $BSKRegistry['BSKRegistry_nextDate'],
				'PersonRegister_id' => $BSKRegistry['PersonRegister_id'],
				'BSKRegistryFormTemplate_id' => $BSKRegistry['BSKRegistryFormTemplate_id'],
				'isBrowsed' => $BSKRegistry['MorbusType_id']==19 ? 1 : 2,
				'CmpCallCard_id' => isset($data['CmpCallCard_id'])?$data['CmpCallCard_id']:NULL
			]);

			if ( $result === false || !is_array($result) || count($result) == 0 ) {
				throw new Exception('Ошибка при добавлении пациента в регистр БСК');
			}
			else if ( !empty($result['Error_Msg']) ) {
				throw new Exception($result['Error_Msg']);
			}

			$response['BSKRegistry_id'] = $result['BSKRegistry_id'];

			if ( is_array($BSKRegistryData) && count($BSKRegistryData) > 0 && !empty($response['BSKRegistry_id']) ) {
				foreach ($BSKRegistryData as $k => $v) {
					if ($v['BSKRegistryData_id'] == null && ($v['BSKObservElement_id'] !== '' || $v['BSKRegistryData_AnswerText'] !== '' || $v['BSKRegistryData_AnswerInt'] !== '' || $v['BSKRegistryData_AnswerFloat'] !== '' || $v['BSKRegistryData_AnswerDT'] !== '')) {

						$result = $this->getFirstRowFromQuery("
							select 
								BSKRegistryData_id as \"BSKRegistryData_id\", 
								Error_Code as \"Error_Code\", 
								Error_Message as \"Error_Message\"
							from dbo.p_BSKRegistryData_ins
							(
								BSKRegistry_id  := :BSKRegistry_id,
								BSKObservElement_id := :BSKObservElement_id,
								BSKObservElementValues_id := :BSKObservElementValues_id,
								BSKRegistryData_AnswerText := :BSKRegistryData_AnswerText,
								BSKRegistryData_AnswerInt := :BSKRegistryData_AnswerInt,
								BSKRegistryData_AnswerFloat := :BSKRegistryData_AnswerFloat,
								BSKRegistryData_AnswerDT := :BSKRegistryData_AnswerDT,
								pmUser_id := :pmUser_id
							)
							", [
								'BSKRegistry_id' => $response['BSKRegistry_id'],
								'pmUser_id' => $data['pmUser_id'],
								'BSKObservElement_id' => $v['BSKObservElement_id'],
								'BSKObservElementValues_id' => $v['BSKObservElementValues_id'],
								'BSKRegistryData_AnswerText' => $v['BSKRegistryData_AnswerText'],
								'BSKRegistryData_AnswerInt' => $v['BSKRegistryData_AnswerInt'],
								'BSKRegistryData_AnswerFloat' => $v['BSKRegistryData_AnswerFloat'],
								'BSKRegistryData_AnswerDT' => $v['BSKRegistryData_AnswerDT']
							]);

						// тут можно прикрутить обработку ответа, но в изначальном коде ее не было
						if ( $result === false || !is_array($result) || count($result) == 0 ) {
							throw new Exception('Ошибка при добавлении анкеты пациента в регистр БСК');
						}
						else if ( !empty($result['Error_Msg']) ) {
							throw new Exception($result['Error_Msg']);
						}
					}
					else if ($v['BSKRegistryData_id'] !== null) {

						$result = $this->getFirstRowFromQuery("
							select 
								BSKRegistryData_id as \"BSKRegistryData_id\", 
								Error_Code as \"Error_Code\", 
								Error_Message as \"Error_Message\"
							from dbo.p_bskregistrydata_upd
							(
								BSKRegistryData_id  := :BSKRegistryData_id,
								BSKRegistry_id  := :BSKRegistry_id,
								BSKObservElement_id := :BSKObservElement_id,
								BSKObservElementValues_id := :BSKObservElementValues_id,
								BSKRegistryData_AnswerText := :BSKRegistryData_AnswerText,
								BSKRegistryData_AnswerInt := :BSKRegistryData_AnswerInt,
								BSKRegistryData_AnswerFloat := :BSKRegistryData_AnswerFloat,
								BSKRegistryData_AnswerDT := :BSKRegistryData_AnswerDT,
								pmUser_id := :pmUser_id
							)
							", [
								'BSKRegistryData_id' => $v['BSKRegistryData_id'],
								'BSKRegistry_id' => $response['BSKRegistry_id'],
								'pmUser_id' => $data['pmUser_id'],
								'BSKObservElement_id' => $v['BSKObservElement_id'],
								'BSKObservElementValues_id' => $v['BSKObservElementValues_id'],
								'BSKRegistryData_AnswerText' => $v['BSKRegistryData_AnswerText'],
								'BSKRegistryData_AnswerInt' => $v['BSKRegistryData_AnswerInt'],
								'BSKRegistryData_AnswerFloat' => $v['BSKRegistryData_AnswerFloat'],
								'BSKRegistryData_AnswerDT' => $v['BSKRegistryData_AnswerDT']
							]);

						// тут можно прикрутить обработку ответа, но в изначальном коде ее не было
						if ( $result === false || !is_array($result) || count($result) == 0 ) {
							throw new Exception('Ошибка при добавлении анкеты пациента в регистр БСК');
						}
						else if ( !empty($result['Error_Msg']) ) {
							throw new Exception($result['Error_Msg']);
						}
					}
				}
			}

			$this->commitTransaction();
		}
		catch ( Exception $e ) {
			$this->rollbackTransaction();
			$response['success'] = false;
			$response['Error_Msg'] = $e->getMessage();
		}

		return [ $response ];

	}
	/**
	 * Таблица «Услуги». Отображаются операционные и общие услуги, проведённые пациенту 
	 */
	function getListUslugforEvents($data)
	{
		$params = array(
			'Person_id' => $data['Person_id']
		);

		$query = "
			with UslugaPid (
				EvnUsluga_id
			) as (
				select EvnUsluga_id from v_EvnUsluga EU
				where  (1=1)
				and EU.Person_id = :Person_id
				and (EU.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaOper'))
				and coalesce(EU.EvnUsluga_IsVizitCode, 1) = 1
			)
			select
				EU.EvnUsluga_id as \"EvnUsluga_id\",
				EPS.EvnPS_id as \"EvnPS_id\",
				EU.EvnUsluga_pid as \"EvnUsluga_pid\",
				to_char(EU.EvnUsluga_setDate, 'dd.mm.yyyy') as \"EvnUsluga_setDate\",
				EU.EvnUsluga_setTime as \"EvnUsluga_setTime\",
				UC.UslugaComplex_Code as \"Usluga_Code\",
				coalesce(Usluga.Usluga_Name, UC.UslugaComplex_Name) as \"Usluga_Name\",
				l.Lpu_Nick as \"Lpu_Nick\"
			from
				v_EvnUsluga EU
				left join v_Evn EvnParent on EvnParent.Evn_id = EU.EvnUsluga_pid
				left join v_Usluga Usluga on Usluga.Usluga_id = EU.Usluga_id
				left join v_UslugaComplex UC on UC.UslugaComplex_id = EU.UslugaComplex_id
				left join v_EvnSection ES on ES.EvnSection_id = EU.EvnUsluga_pid
				left join v_EvnPS EPS on EPS.EvnPS_id = ES.EvnSection_pid
				left join v_Lpu l on EU.Lpu_id = l.Lpu_id
			where
				exists(
					Select
						1
					from
						UslugaPid
					where UslugaPid.EvnUsluga_id = EU.EvnUsluga_id
					limit 1
				)
		
				and coalesce(EU.EvnUsluga_IsVizitCode, 1) = 1
				and EPS.EvnPS_id is not null
				and UC.UslugaComplex_id in (select UslugaComplex_id from dbo.BSKRegistryUslugaComplex)
			order by EU.EvnUsluga_setDate desc 
		";
		//TODO: вынести сортировку в PHP
		//echo getDebugSql($query, $params);
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Случаи оказания амбулаторно-поликлинической медицинской помощи
	 */
	function getListPersonCureHistoryPL($data)
	{
		$params = array(
			'Person_id' => $data['Person_id']
		);

		$query = "
			select
				ec.EvnClass_SysNick || '_' || cast(epl.EvnPL_id as varchar(20)) as \"Evn_id\",
				epl.EvnPL_id as \"EvnPL_id\",
				epl.Person_id as \"Person_id\",
				epl.PersonEvn_id as \"PersonEvn_id\",
				to_char(epl.EvnPL_setDate, 'dd.mm.yyyy') as \"EvnPL_setDate\",
				d.Diag_Code || ' ' || d.Diag_Name as \"Diag_Name\",
				l.Lpu_Nick as \"Lpu_Nick\",
				ls.LpuSection_Name as \"LpuSection_Name\",
				MP.Person_Fio as \"Person_Fio\",
				epl.EvnPL_NumCard as \"EvnPL_NumCard\",
				evpl.LpuSection_id as \"LpuSection_id\",
				evpl.MedPersonal_id as \"MedPersonal_id\",
				evpl.MedStaffFact_id as \"MedStaffFact_id\",
				epl.EvnPL_IsFinish as \"EvnPL_IsFinish\"
			from v_EvnPL epl
				inner join EvnClass ec on ec.EvnClass_id = epl.EvnClass_id
				left join v_EvnVizitPL evpl on evpl.EvnVizitPL_pid = epl.EvnPL_id and evpl.EvnVizitPL_Index = evpl.EvnVizitPL_Count - 1
				left join v_LpuSection ls on ls.LpuSection_id = evpl.LpuSection_id
				left join v_Diag d on d.Diag_id = epl.Diag_id
				left join v_Lpu l on l.Lpu_id = epl.Lpu_id
				left join lateral (
					select
						Person_Fio
					from
						v_MedPersonal
					where
						MedPersonal_id = evpl.MedPersonal_id
					limit 1
				) MP on true
			where (1 = 1)
				and epl.Person_id = :Person_id
				and epl.EvnClass_id in (3)
				and ls.LpuSectionProfile_Code in (
					'625',
					'825',
					'525',
					'602',
					'802',
					'502',
					'623',
					'823',
					'523',
					'632',
					'832',
					'532',
					'655',
					'855',
					'555')
			order by epl.EvnPL_setDate desc
		";
		//TODO: вынести сортировку в PHP
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 * Таблица «Случаи оказания стационарной медицинской помощи»
	 */
	function getListPersonCureHistoryPS($data)
	{
		$params = array(
			'Person_id' => $data['Person_id']
		);

		$query = "
			select
				ec.EvnClass_SysNick || '_' || cast(eps.EvnPS_id as varchar(20)) as \"Evn_id\",
				eps.EvnPS_id as \"EvnPS_id\",
				eps.Person_id as \"Person_id\",
				eps.PersonEvn_id as \"PersonEvn_id\",
				eps.Server_id as \"Server_id\",
				to_char(eps.EvnPS_setDate, 'dd.mm.yyyy') as \"EvnPS_setDate\",
				d.Diag_Code || ' ' || d.Diag_Name as \"Diag_Name\",
				l.Lpu_Nick as \"Lpu_Nick\",
				MP.Person_Fio as \"Person_Fio\",
				eps.EvnPS_NumCard as \"EvnPS_NumCard\",
				ls.LpuSection_Name as \"LpuSection_Name\",
				null as \"MedPersonal_Fio\",
				eps.EvnPS_setDT as \"Evn_setDT\",
				eps.EvnPS_disDT as \"Evn_disDT\"
			from v_EvnPS eps
				inner join EvnClass ec on ec.EvnClass_id = eps.EvnClass_id
				left join v_EvnSection es on es.EvnSection_pid = eps.EvnPS_id
					and es.EvnSection_Index = es.EvnSection_Count - 1
				left join v_Diag d on d.Diag_id = eps.Diag_id
				left join v_LpuSection ls on ls.LpuSection_id = eps.LpuSection_id
				left join v_Lpu l on l.Lpu_id = eps.Lpu_id
				left join lateral (
					select Person_Fio
					from v_MedPersonal
					where MedPersonal_id = es.MedPersonal_id
					limit 1
				) MP on true
			where (1 = 1)
				and eps.Person_id = :Person_id
				and ls.LpuSectionProfile_Code in (
					'2032',	
					'1032',	
					'2008',
					'2058',
					'3058',
					'1058',
					'1059',
					'3008',
					'1008',
					'2100',
					'1009',
					'6012',
					'6013',
					'2024',
					'3024',
					'1024',
					'654',
					'854',
					'554',
					'2075',
					'3075',
					'1075'
				)
			order by eps.EvnPS_setDate desc
		";
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 * Таблица «Сопутствующие диагнозы»
	 */
	function getListPersonCureHistoryDiagSop($data)
	{
		$params = array(
			'Person_id' => $data['Person_id']
		);
		$query = "
			with Evn(
				EvnClass_SysNick,
				Evn_id,
				Person_id,
				PersonEvn_id,
				Server_id,
				Diag_id,
				SetDate,
				Lpu_id,
				LpuSection_id, 
				MedPersonal_id,
				NumCard
			) as (
			
			select
				'EvnDiagPLSop',
				epl.EvnPL_id,
				epl.Person_id,
				epl.PersonEvn_id,
				epl.Server_id,
				DiagPS.Diag_id,
				DiagPS.EvnDiagPLSop_setDate,
				es.Lpu_id,
				es.LpuSection_id,
				es.MedPersonal_id,
				epl.EvnPL_NumCard
			from v_EvnPL epl
				inner join v_EvnVizitPL es on es.EvnVizitPL_pid = epl.EvnPL_id and es.EvnVizitPL_Index = 0
				inner join lateral (
					select DiagPS.EvnDiagPLSop_setDate, dPS.Diag_id, dPS.Diag_Name 
					from v_EvnDiagPLSop DiagPS
						inner join v_Diag dPS on dPS.Diag_id = DiagPS.Diag_id
						where DiagPS.EvnDiagPLSop_rid = epl.EvnPL_rid
						and (dPS.Diag_Code ilike 'I%' or dPS.Diag_Code ilike 'G45%' or dPS.Diag_Code ilike 'G46%' or dPS.Diag_Code ilike 'J44%' or dPS.Diag_Code ilike 'N18%'
						or dPS.Diag_Code ilike 'N19%' or dPS.Diag_Code ilike 'С[0-9][0-7]%' or dPS.Diag_Code ilike 'D[0-4][0-8]%' or dPS.Diag_Code ilike 'E10%'
						or dPS.Diag_Code ilike 'E11%' or dPS.Diag_Code ilike 'E12%' or dPS.Diag_Code ilike 'E13%' or dPS.Diag_Code ilike 'E14%')
					) DiagPS on true
			where (1=1)
			and epl.Person_id = :Person_id
			union all
			select
				'EvnDiagPS',
				EPS.EvnPS_id,
				EPS.Person_id,
				EPS.PersonEvn_id,
				EPS.Server_id,
				DiagPS.Diag_id,
				DiagPS.EvnDiagPS_setDate,
				es.Lpu_id,
				es.LpuSection_id,
				es.MedPersonal_id,
				EPS.EvnPS_NumCard
			from v_EvnPS EPS
				inner join v_EvnSection es on es.EvnSection_pid = EPS.EvnPS_id and es.EvnSection_Index = 0
				inner join lateral (
					select 
						DiagPS.EvnDiagPS_setDate, dPS.Diag_id, dPS.Diag_Name
					from 
						v_EvnDiagPS DiagPS
						inner join v_Diag dPS on dPS.Diag_id = DiagPS.Diag_id
					where 
						DiagPS.EvnDiagPS_rid = EPS.EvnPS_rid
						and (dPS.Diag_Code ilike 'I%' or dPS.Diag_Code ilike 'G45%' or dPS.Diag_Code ilike 'G46%' or dPS.Diag_Code ilike 'J44%' or dPS.Diag_Code ilike 'N18%'
						or dPS.Diag_Code ilike 'N19%' or dPS.Diag_Code ilike 'С[0-9][0-7]%' or dPS.Diag_Code ilike 'D[0-4][0-8]%' or dPS.Diag_Code ilike 'E10%'
						or dPS.Diag_Code ilike 'E11%' or dPS.Diag_Code ilike 'E12%' or dPS.Diag_Code ilike 'E13%' or dPS.Diag_Code ilike 'E14%')
						and DiagPS.DiagSetClass_id in (3)
				) DiagPS on true
			where (1=1)
			and EPS.Person_id = :Person_id
			)
			select 
				num,
				EvnClass_SysNick as \"EvnClass_SysNick\",
				Evn_id as \"Evn_id\",
				Person_id as \"Person_id\",
				PersonEvn_id as \"PersonEvn_id\",
				Diag_id as \"Diag_id\",
				Diag_FullName as \"Diag_FullName\",
				Server_id as \"Server_id\",
				SetDate as \"SetDate\",
				Lpu_Nick as \"Lpu_Nick\",
				LpuSection_Name as \"LpuSection_Name\",
				Person_Fio as \"Person_Fio\",
				NumCard as \"NumCard\",
				to_char(SetDate, 'dd.mm.yyyy') as \"Diag_setDate\"
			from (
				select 
					row_number() over (partition by Evn.Diag_id order by Evn.Diag_id, Evn.SetDate asc ) num,
					Evn.EvnClass_SysNick,
					Evn.Evn_id,
					Evn.Person_id,
					Evn.PersonEvn_id,
					Evn.Diag_id,
					d.Diag_FullName,
					Evn.Server_id,
					Evn.SetDate,
					Lpu.Lpu_Nick,
					LS.LpuSection_Name,
					MP.Person_Fio,
					Evn.NumCard
				from Evn
					left join v_Lpu Lpu on Lpu.Lpu_id = Evn.Lpu_id
					left join v_Diag d on d.Diag_id = Evn.Diag_id
					left join v_MedPersonal MP on MP.MedPersonal_id = Evn.MedPersonal_id and MP.Lpu_id = Evn.Lpu_id
					left join v_LpuSection LS on LS.LpuSection_id = Evn.LpuSection_id
				where (1=1)
			) T
			where T.num = 1
			order by SetDate desc
		";
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 * Таблица «Постинфарктный кардиосклероз»
	 */
	function getListPersonCureHistoryDiagKardio($data)
	{
		$params = array(
			'Person_id' => $data['Person_id']
		);
		$query = "
			with Evn (
				EvnClass_SysNick,
				Person_id,
				Evn_id,
				EvnDiagPS_setDate,
				Diag_id,
				Diag_FullName,
				Lpu_id,
				Lpu_Nick,
				LpuSection_id,
				LpuSection_Name,
				MedPersonal_id,
				Person_Fio,
				NumCard
			) as (
				select 
					'EvnDiagPS',
					ps.Person_id,
					ps.EvnPS_id Evn_id,
					case when d.Diag_Code in ('I25.2') then es.EvnSection_setDate else DiagPS.EvnDiagPS_setDate end EvnDiagPS_setDate,
					case when d.Diag_Code in ('I25.2') then es.Diag_id else DiagPS.Diag_id end Diag_id,
					case when d.Diag_Code in ('I25.2') then d.Diag_FullName else DiagPS.Diag_FullName end Diag_FullName,
					Lpu.Lpu_id,
					Lpu.Lpu_Nick,
					LS.LpuSection_id,
					LS.LpuSection_Name,
					mp.MedPersonal_id,
					mp.Person_Fio,
					ps.EvnPS_NumCard
				from v_EvnPS ps
					inner join v_EvnSection es on es.EvnSection_pid = ps.EvnPS_id and es.EvnSection_Index = 0
					left join v_Diag d on d.Diag_id = ps.Diag_id 
					left join lateral (
						select DiagPS.EvnDiagPS_setDate, dPS.Diag_id, dPS.Diag_FullName
						from v_EvnDiagPS DiagPS
						inner join v_Diag dPS on dPS.Diag_id = DiagPS.Diag_id
						where DiagPS.EvnDiagPS_rid = ps.EvnPS_rid
							and dPS.Diag_Code in ('I25.2')
							and DiagPS.DiagSetClass_id in (3,2)
					) DiagPS on true
					inner join v_Lpu Lpu on Lpu.Lpu_id = es.Lpu_id
					inner join v_LpuSection LS on LS.LpuSection_id = es.LpuSection_id
					left join v_MedPersonal mp on mp.MedPersonal_id = es.MedPersonal_id and mp.Lpu_id = es.Lpu_id
				where ps.Person_id = :Person_id
					and (d.Diag_Code in ('I25.2')
					or DiagPS.Diag_id is not null)
					and LS.LpuSectionProfile_Code in ('2032','1032','2008','2058','3058','1058','1059','3008','1008','2100','1009','6012','6013','2024','3024','1024','654','854','554','2075','3075','1075')
				
				UNION ALL

				select 
					'EvnDiagPLSop',
					pl.Person_id,
					pl.EvnPL_id Evn_id,
					case when d.Diag_Code in ('I25.2') then es.EvnVizitPL_setDate when dOsl.Diag_Code in ('I25.2') then es.EvnVizitPL_setDate else DiagPS.EvnDiagPLSop_setDate end EvnDiagPS_setDate,
					case when d.Diag_Code in ('I25.2') then es.Diag_id when dOsl.Diag_Code in ('I25.2') then dOsl.Diag_id else DiagPS.Diag_id end Diag_id,
					case when d.Diag_Code in ('I25.2') then d.Diag_FullName when dOsl.Diag_Code in ('I25.2') then dOsl.Diag_FullName else DiagPS.Diag_FullName end Diag_FullName,
					Lpu.Lpu_id,
					Lpu.Lpu_Nick,
					LS.LpuSection_id,
					LS.LpuSection_Name,
					mp.MedPersonal_id,
					mp.Person_Fio,
					pl.EvnPL_NumCard
				from v_EvnPL pl
					inner join v_EvnVizitPL es on es.EvnVizitPL_pid = pl.EvnPL_id --and es.EvnVizitPL_Index = 0
					left join v_Diag d on d.Diag_id = pl.Diag_id 
					left join v_Diag dOsl on dOsl.Diag_id = es.Diag_agid
					left join lateral (
						select DiagPS.EvnDiagPLSop_setDate, dPS.Diag_id, dPS.Diag_FullName
						from v_EvnDiagPLSop DiagPS
						inner join v_Diag dPS on dPS.Diag_id = DiagPS.Diag_id
						where DiagPS.EvnDiagPLSop_rid = pl.EvnPL_rid
							and dPS.Diag_Code in ('I25.2')
					) DiagPS on true
					inner join v_Lpu Lpu on Lpu.Lpu_id = es.Lpu_id
					left join v_LpuSection LS on LS.LpuSection_id = es.LpuSection_id
					left join v_MedPersonal mp on mp.MedPersonal_id = es.MedPersonal_id and mp.Lpu_id = es.Lpu_id
				where pl.Person_id = :Person_id
					and (d.Diag_Code in ('I25.2') or dOsl.Diag_Code in ('I25.2')
					or DiagPS.Diag_id is not null)
					and LS.LpuSectionProfile_Code in ('625','825','525','602','802','502','623','823','523','632','832','532','655','855','555')
				)
			select 
				Evn.EvnClass_SysNick as \"EvnClass_SysNick\",
				Evn.Person_id as \"Person_id\",
				Evn.Evn_id as \"Evn_id\",
				to_char(Evn.EvnDiagPS_setDate, 'dd.mm.yyyy') as \"EvnDiagPS_setDate\",
				to_char(Evn.EvnDiagPS_setDate, 'dd.mm.yyyy') as \"Diag_setDate\",
				Evn.Diag_id as \"Diag_id\",
				Evn.Diag_FullName as \"Diag_FullName\",
				Evn.Lpu_id as \"Lpu_id\",
				Evn.Lpu_Nick as \"Lpu_Nick\",
				Evn.LpuSection_id as \"LpuSection_id\",
				Evn.LpuSection_Name as \"LpuSection_Name\",
				Evn.MedPersonal_id as \"MedPersonal_id\",
				Evn.Person_Fio as \"Person_Fio\",
				Evn.NumCard as \"NumCard\"
			from Evn
			order by Evn.EvnDiagPS_setDate
			limit 1
		";
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 * Вкладка «Исследования»
	 */
	function getLabResearch($data)
	{
		$params = array(
			'Person_id' => $data['Person_id']
		);

		$query = "
			select
				ut.UslugaTest_id as \"EvnUslugaPar_id\",
				to_char(EUP.EvnUslugaPar_setDate, 'dd.mm.yyyy') as \"EvnUslugaPar_setDate\",
				UslugaComplex.UslugaComplex_Code as \"UslugaComplex_Code\",
				UslugaComplex.UslugaComplex_Name as \"UslugaComplex_Name\",
				Lpu.Lpu_Nick as \"Lpu_Nick\",
				doc.EvnXml_id as \"EvnXml_id\",
				doc.XmlTemplate_HtmlTemplate as \"XmlTemplate_HtmlTemplate\",
				doc.UslugaTest_ResultValue as \"EvnUslugaPar_ResultValue\",
				doc.UslugaTest_ResultUnit as \"EvnUslugaPar_ResultUnit\",
				EvnXml_id as \"EvnXml_id\",
				Replace(Substring(
					Substring(doc.XmlTemplate_HtmlTemplate
						,position(UslugaComplex.UslugaComplex_Code in doc.XmlTemplate_HtmlTemplate)-1
						,length(doc.XmlTemplate_HtmlTemplate)),
					0,position('</tr>' in Substring(doc.XmlTemplate_HtmlTemplate
						,position(UslugaComplex.UslugaComplex_Code in doc.XmlTemplate_HtmlTemplate)-1
						,length(doc.XmlTemplate_HtmlTemplate)))-1),'td','') as \"ResultValueColor\"
			from
				v_PersonState PS
				inner join v_EvnLabSample ELS on ELS.Person_id = PS.Person_id
				inner join v_UslugaTest ut on ut.EvnLabSample_id = ELS.EvnLabSample_id
				inner join v_EvnUslugaPar EUP on EUP.EvnUslugaPar_id = ut.UslugaTest_pid
				inner join v_Lpu Lpu on Lpu.Lpu_id = EUP.Lpu_id
				inner join lateral (
						select EvnXml.EvnXml_id, xth.XmlTemplateHtml_HtmlTemplate as XmlTemplate_HtmlTemplate, ut.UslugaTest_ResultValue, ut.UslugaTest_ResultUnit
						from v_EvnXml  EvnXml
						left join XmlTemplateHtml xth on xth.XmlTemplateHtml_id = EvnXml.XmlTemplateHtml_id
						--left join v_UslugaTest ut on ut.UslugaTest_pid = EvnXml.Evn_id
						--left join UslugaComplex on UslugaComplex.UslugaComplex_id = ut.UslugaComplex_id
						where EvnXml.Evn_id = ut.UslugaTest_pid
						order by EvnXml_insDT desc
						limit 1
				) doc on true
				inner join UslugaComplex on UslugaComplex.UslugaComplex_id = ut.UslugaComplex_id
			where
				(1=1)
				and UslugaComplex.UslugaComplex_Code in (
					'A09.05.026',
					'A09.05.028',
					'A09.05.004',
					'A09.05.025',
					'A09.05.027',
					'A09.05.023',
					'A09.05.023',
					'A12.22.005',
					'A09.05.042',
					'A09.05.177',
					'A09.05.042',
					'A09.05.009',
					'A09.05.003',
					'A12.05.118',
					'A12.30.014',
					'A09.28.006.002',
					'A09.28.003.005',
					'A09.28.010')
				--and doc.EvnXml_id is not null
				and EUP.EvnUslugaPar_setDate is not null
				and ut.UslugaTest_ResultValue is not null
				and PS.Person_id = :Person_id
			order by
				EUP.EvnUslugaPar_setDate desc
		";
		//TODO: вынести сортировку в PHP
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 * Вкладка «Обследования»
	 */
	function getLabSurveys($data)
	{
		$params = array(
			'Person_id' => $data['Person_id']
		);

		$query = "
			select
				EUP.EvnUsluga_id as \"EvnUsluga_id\",
				to_char(EUP.EvnUsluga_setDate, 'dd.mm.yyyy') as \"EvnUsluga_setDate\",
				UslugaComplex.UslugaComplex_Code as \"UslugaComplex_Code\",
				UslugaComplex.UslugaComplex_Name as \"UslugaComplex_Name\",
				Lpu.Lpu_Nick as \"Lpu_Nick\",
				doc.EvnXml_id as \"EvnXml_id\",
				EvnXml_id as \"prosmotr\"
			from
				v_PersonState PS
				inner join v_EvnUsluga EUP on EUP.Person_id = PS.Person_id
				left join v_Lpu Lpu on Lpu.Lpu_id = EUP.Lpu_id
				left join lateral (
						select EvnXml_id
						from v_EvnXml
						where Evn_id = EUP.EvnUsluga_id
						order by EvnXml_insDT desc 
						limit 1 
				) doc on true
				left join UslugaComplex on UslugaComplex.UslugaComplex_id = EUP.UslugaComplex_id
			where
				(1=1)
				and UslugaComplex.UslugaComplex_id in (select UslugaComplex_id from dbo.BSKRegistryUslugaComplexSurvey)
				and doc.EvnXml_id is not null
				and EUP.EvnUsluga_setDate is not null
				and PS.Person_id = :Person_id
			order by
				EUP.EvnUsluga_setDate desc
		";
		//TODO: вынести сортировку в PHP
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 * Получение рекомендаций по регистрам
	 */
	function getRecomendationByDate($data) {
		$params = array(
			'MorbusType_id' => $data['MorbusType_id'],
			'Person_id' => $data['Person_id'],
			'Sex_id' => $data['Sex_id'],
			'BSKRegistry_id' => $data['BSKRegistry_id'],
			'BSKObservRecomendationType_id' => $data['BSKObservRecomendationType_id']
		);
		
		$query = "
			select 
				bskobservrecomendation_id as \"BSKObservRecomendation_id\", 
				bskobservrecomendation_text as \"BSKObservRecomendation_text\",
				bskobservrecomendationtype_id as \"BSKObservRecomendationType_id\",
				answer
			from dbo.p_getrecomendationbydatenew (
				p_morbustype_id := :MorbusType_id,
				p_person_id := :Person_id,
				p_sex_id := :Sex_id,
				p_bskregistry_id := :BSKRegistry_id,
				p_bskobservrecomendationtype_id := :BSKObservRecomendationType_id
			)
		";
		
		$result = $this->db->query($query, $params);
		
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 * Получение сведений о лекарственном лечении
	 */
	function getDrugs($data) {
		$params = array(
			'Person_id' => $data['Person_id']
		);
		
		$query = "
			select 
				DENSE_RANK() OVER(ORDER BY ob.BSKObject_id) as \"num\",
				r.BSKRegistry_id as \"BSKRegistry_id\", 
				to_char(r.BSKRegistry_setDate,'dd.mm.yyyy') as \"BSKRegistry_setDate\", 
				to_char(r.BSKRegistry_setDate,'dd.mm.yyyy') as \"BSKRegistry_prDate\", 
				mt.MorbusType_id as \"MorbusType_id\", 
				mt.MorbusType_Name as \"MorbusType_Name\", 
				ob.BSKObject_id as \"BSKObject_id\", 
				ob.BSKObject_SysNick as \"BSKObject_SysNick\",
				rd.BSKRegistryData_id as \"BSKRegistryData_id\",
				el.BSKObservElement_name as \"BSKObservElement_name\",
				coalesce(elVal.BSKObservElementValues_data, rd.BSKRegistryData_data) as \"BSKRegistryData_data\",
				coalesce(coalesce(elValDose.BSKObservElementValues_data,rdDose.BSKRegistryData_AnswerText)||coalesce(' '||Unit.Unit_Name,''),rdDose.BSKRegistryData_data||coalesce(' '||rdDose.BSKUnits_name,'')) as \"Unit_name\",
				coalesce(elValCancel.BSKObservElementValues_data, rdCancel.BSKRegistryData_data) as \"ReasonCancel\"
			from dbo.v_BSKObservElementGroup gr
			inner join dbo.v_BSKObservElement el on el.BSKObservElementGroup_id = gr.BSKObservElementGroup_id
				and gr.BSKObservElementGroup_id in (10,23,28,42)
				and el.BSKObservElement_rid is null
				--and coalesce(el.BSKObservElement_deleted,1) <> 2
			--ответы
			--1. MNN
			inner join dbo.v_BSKRegistryData rd on rd.BSKObservElement_id = el.BSKObservElement_id
			inner join dbo.v_BSKRegistry r on r.BSKRegistry_id = rd.BSKRegistry_id and r.Person_id = :Person_id
			left join dbo.BSKRegistryFormTemplate ft on ft.BSKRegistryFormTemplate_id = r.BSKRegistryFormTemplate_id
			left join dbo.BSKRegistryFormTemplateData td on td.BSKRegistryFormTemplate_id = ft.BSKRegistryFormTemplate_id and td.BSKObservElement_id = el.BSKObservElement_id
			left join dbo.v_BSKObservElementValues elVal on elVal.BSKObservElementValues_id = rd.BSKObservElementValues_id
			--дозировки и прочее
			inner join dbo.v_BSKObservElement elDose on elDose.BSKObservElement_rid = el.BSKObservElement_id and elDose.BSKObservElement_pid is not null --дозировка
			--2. dose
			left join dbo.v_BSKRegistryData rdDose on rdDose.BSKObservElement_id = elDose.BSKObservElement_id and rdDose.BSKRegistry_id = r.BSKRegistry_id
			left join dbo.v_BSKObservElement elAnswerDose on elAnswerDose.BSKObservElement_id = rdDose.BSKObservElement_id
			left join dbo.v_BSKObservElementValues elValDose on elValDose.BSKObservElementValues_id = rdDose.BSKObservElementValues_id
			left join dbo.Unit Unit on Unit.Unit_id = elAnswerDose.Unit_id
			left join dbo.v_BSKObservElement elCancel on elCancel.BSKObservElement_rid = el.BSKObservElement_id and elCancel.BSKObservElement_pid is null --Причина отмены
			left join dbo.v_BSKRegistryData rdCancel on rdCancel.BSKObservElement_id = elCancel.BSKObservElement_id and rdCancel.BSKRegistry_id = r.BSKRegistry_id
			left join dbo.v_BSKObservElementValues elValCancel on elValCancel.BSKObservElementValues_id = rdCancel.BSKObservElementValues_id
			full join dbo.MorbusType mt on mt.MorbusType_id = r.MorbusType_id
			inner join dbo.v_BSKObject ob on ob.MorbusType_id = mt.MorbusType_id
			where 
				mt.MorbusType_SysNick in ('ibs','screening','lung_hypert','Arter_hypert')
				--and coalesce(r.BSKRegistry_deleted,1) <> 2
			order by 
				ob.BSKObject_id, 
				r.BSKRegistry_setDate desc, 
				r.BSKRegistry_id desc, 
				td.BSKRegistryFormTemplateData_GroupNum, 
				td.BSKRegistryFormTemplateData_ElementNum
		";
		
		$result = $this->db->query($query, $params);
		
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 * Получение данных регистра для сравнения
	 */ 
	function getCompare($data) {
		$params = array(
			'Person_id' => $data['Person_id'],
			'MorbusType_id' => $data['MorbusType_id']
		);
		
		$query = "
			select 
				BSKRegistry_id as \"BSKRegistry_id\",
				left(to_char(BSKRegistry_setDate,'dd.mm.yyyy'),10) as \"BSKRegistry_setDate\",
				BSKRegistry_riskGroup as \"BSKRegistry_riskGroup\"
			from dbo.v_BSKRegistry r
			where Person_id = :Person_id
				and MorbusType_id = :MorbusType_id
				--and coalesce(r.BSKRegistry_deleted,1) <> 2
			-- Все 3 группы попадают в сравнение
			--and BSKRegistry_riskGroup in(2,3)
			order by r.BSKRegistry_setDate DESC, r.BSKRegistry_id DESC
		";
		
		
		$result = $this->db->query($query, $params);
		
		//echo getDebugSql($query, $params);
		//exit;
		
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 *  Проверка наличия пациента в регистре по предмету наблюдения
	 *  false  - отсутствует
	 */
	function checkPersonInRegister($params) {
		$params = array(
			'Person_id' => $params['Person_id'],
			'MorbusType_id' => $params['MorbusType_id']
		);
		
		$query = "
			select 
				PersonRegister_id as \"PersonRegister_id\",
				Person_id as \"Person_id\", 
				MorbusType_id as \"MorbusType_id\",
				coalesce(to_char(PersonRegister_disDate,'yyyy-mm-dd'),to_char(getdate(),'yyyy-mm-dd')) as \"PersonRegister_disDate\",
				to_char(PersonRegister_setDate,'yyyy-mm-dd') as \"PersonRegister_setDate\"
			from dbo.v_PersonRegister 
			where Person_id = :Person_id
				and MorbusType_id = :MorbusType_id
			order by coalesce(PersonRegister_disDate,getdate()) desc
			limit 1
		";

		$result = $this->db->query($query, $params);
		
		if (is_object($result)) {
			$dataInDB = $result->result('array');
			
			if (!empty($dataInDB)) {
				return $dataInDB;
			}
			
			return false;
		} else {
			return false;
		}
	}
	/**
	 *  Метод преобразования BSKObservRecomendation_id в BSKObservElement_id
	 */
	function getBSKObservElement_id($BSKObservRecomendation_id) {
		$ids = array(
			287 => 277,
			288 => 278,
			289 => 279,
			290 => 280,
			291 => 281,
			292 => 282,
			293 => 283,
			294 => 284,
			295 => 285,
			296 => 286,
			
			297 => 287,
			298 => 288,
			299 => 289,
			300 => 290,
			301 => 291,
			302 => 292,
			303 => 293,
			304 => 294,
			305 => 295,
			306 => 296,
			307 => 297,
			308 => 298,
			309 => 299
		);
		
		return $ids[$BSKObservRecomendation_id];
	} 
	/**
	 * Добавление пациента в PersonRegister
	 */
	function saveInPersonRegister($data) {
		$response = [
			'success' => true,
			'Error_Msg' => '',
			'PersonRegister_id' => null
		];
		try {
			$this->beginTransaction();
			$resultPR = $this->getFirstRowFromQuery("
				select 
					PersonRegister_id as \"PersonRegister_id\", 
					Error_Code as \"Error_Code\", 
					Error_Message as \"Error_Message\"

				from dbo.p_PersonRegister_ins (
					Person_id := :Person_id,
					MorbusType_id := :MorbusType_id,
					Diag_id := :Diag_id,
					PersonRegister_Code := :PersonRegister_Code,
					PersonRegister_setDate := :PersonRegister_setDate,
					PersonRegister_disDate := :PersonRegister_disDate,
					MedPersonal_iid := :MedPersonal_iid,
					Lpu_iid := :Lpu_iid,
					EvnNotifyBase_id := :EvnNotifyBase_id,
					pmUser_id := :pmUser_id,
					MedPersonal_did := :MedPersonal_did,
					Lpu_did := :Lpu_did,
					Morbus_id := :Morbus_id,
					PersonRegisterOutCause_id := :PersonRegisterOutCause_id
				)
				", [
					'Person_id' => $data['Person_id'],
					'MorbusType_id' => $data['MorbusType_id'],
					'Diag_id' => $data['Diag_id'],
					'PersonRegister_Code' => $data['PersonRegister_Code'],
					'PersonRegister_setDate' => $data['PersonRegister_setDate'],
					'PersonRegister_disDate' => $data['PersonRegister_disDate'],
					'Morbus_id' => $data['Morbus_id'],
					'PersonRegisterOutCause_id' => $data['PersonRegisterOutCause_id'],
					'MedPersonal_iid' => $data['MedPersonal_iid'],
					'Lpu_iid' => $data['Lpu_iid'],
					'MedPersonal_did' => $data['MedPersonal_did'],
					'Lpu_did' => $data['Lpu_did'],
					'EvnNotifyBase_id' => $data['EvnNotifyBase_id'],
					'pmUser_id' => $data['pmUser_id']
				]);
			if ( !empty($resultPR['Error_Message']) ) {
				throw new Exception($resultPR['Error_Message']);
			}

			$response['PersonRegister_id'] = $resultPR['PersonRegister_id'];
			// Рассылка уведомлений
			$resUsers =  $this->queryResult('
				SELECT puc.pmUser_id as "pmUser_id"
				FROM v_PersonCard pc 
					INNER JOIN v_LpuRegion lp ON lp.LpuRegion_id = pc.LpuRegion_id
					INNER JOIN v_MedStaffRegion msr ON lp.LpuRegion_id = msr.LpuRegion_id
						and msr.MedStaffRegion_isMain = 2
						and (msr.MedStaffRegion_begDate is null or msr.MedStaffRegion_begDate <= dbo.tzGetdate())
						and (msr.MedStaffRegion_endDate is null or msr.MedStaffRegion_endDate >= dbo.tzGetdate())
					LEFT JOIN v_MedStaffFact msf on msf.MedStaffFact_id = msr.MedStaffFact_id
					INNER JOIN dbo.pmUserCache puc on puc.MedPersonal_id = coalesce(msf.MedPersonal_id, msr.MedPersonal_id)
				WHERE pc.LpuAttachType_id = 1
					and pc.Person_id = :Person_id
			', [
			'Person_id' => $data['Person_id']
			]);

			if ( is_array($resUsers) && count($resUsers) > 0 ) {
				$userList = [];

				foreach ( $resUsers as $row ) {
					if ( !in_array($row['pmUser_id'], $userList) ) {
						$userList[] = $row['pmUser_id'];
					}
				}

				$persData = $this->getFirstRowFromQuery("
						SELECT 
							ps.Person_SurName as \"Person_SurName\",
							ps.Person_FirName as \"Person_FirName\",
							ps.Person_SecName as \"Person_SecName\",
							to_char(ps.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\"
						FROM v_PersonState ps
						WHERE ps.Person_id = :Person_id
						limit 1
					", [
					'Person_id' => $data['Person_id']
				]);

				if ( $persData === false || !is_array($persData) || count($persData) == 0 ) {
					throw new Exception('Ошибка при получении данных пациента');
				}

				$datasend = [
					'Message_id' => null,
					'Message_pid' => null,
					'Message_Subject' => 'Пациент включен в регистр БСК',
					'Message_Text' => "Пациент вашего участка " . $persData['Person_SurName'] . " ". $persData['Person_FirName'] . " " . $persData['Person_SecName'] . ", дата рождения " . $persData['Person_BirthDay'] . " включен в регистр БСК",
					'pmUser_id' => $data['pmUser_id'],
					'UserSend_ID' => 0,
					'Lpus' => '',
					'pmUser_Group' => '',
					'Message_isSent' => 1,
					'NoticeType_id' => 5,
					'Message_isFlag' => 1,
					'Message_isDelete' => 1,
					'RecipientType_id' => 1,
					'action' => 'ins',
					'MessageRecipient_id' => null,
					'Message_isRead' => null,
				];

				$this->load->model("Messages_model", "msmodel");

				$result = $this->msmodel->insMessage($datasend);

				if ( !is_array($result) || count($result) == 0 || empty($result[0]['Message_id']) ) {
					throw new Exception('Ошибка при добавлении сообщения');
				}
				else if ( !empty($result[0]['Error_Msg']) ) {
					throw new Exception($result[0]['Error_Msg']);
				}

				$Message_id = $result[0]['Message_id'];

				foreach ( $userList as $pmUser_id ) {
					$result = $this->msmodel->insMessageLink($Message_id, $pmUser_id, $datasend);

					if ( !is_array($result) || count($result) == 0 || empty($result[0]['MessageLink_id']) ) {
						throw new Exception('Ошибка при добавлении сообщения');
					}
					else if ( !empty($result[0]['Error_Msg']) ) {
						throw new Exception($result[0]['Error_Msg']);
					}

					$result = $this->msmodel->sendMessage($datasend, $pmUser_id, $Message_id);

					if ( !is_array($result) || count($result) == 0 || empty($result[0]['MessageRecipient_id']) ) {
						throw new Exception('Ошибка при добавлении сообщения');
					}
					else if ( !empty($result[0]['Error_Msg']) ) {
						throw new Exception($result[0]['Error_Msg']);
					}
				}
			}

			$this->commitTransaction();
		}
		catch ( Exception $e ) {
			$this->rollbackTransaction();
			$response['success'] = false;
			$response['Error_Msg'] = $e->getMessage();
		}

		return [ $response ];

	}
	/**
	 *  Последние сохраненные ответы из анкеты, из-за неправильного сохранения приходится так
	*/
	function getLastBSKRegistryData($BSKRegistry_id) {
		$params = array(
			'BSKRegistry_id' => $BSKRegistry_id
		);
		
		$query = "
			select 
				rd.BSKRegistry_id as \"BSKRegistry_id\",
				rd.BSKObservElement_id as \"BSKObservElement_id\",
				rd.BSKRegistryData_id as \"BSKRegistryData_id\"
			from dbo.v_BSKRegistryData rd
			inner join lateral (
				select MAX(rdM.BSKRegistryData_id) BSKRegistryData_idMax
				from dbo.v_BSKRegistryData rdM
				inner join dbo.v_BSKObservElement el on el.BSKObservElement_id = rdM.BSKObservElement_id 
					--and coalesce(el.BSKObservElement_deleted,1) = 1
				where rdM.BSKRegistry_id = rd.BSKRegistry_id
					and rdM.BSKObservElement_id = rd.BSKObservElement_id
			) rdM on true
			where rd.BSKRegistry_id = :BSKRegistry_id
				and rd.BSKRegistryData_id = rdM.BSKRegistryData_idMax
		";

		$result = $this->db->query($query, $params);
		
		if (is_object($result)) {
			$dataInDB = $result->result('array');
			
			if (!empty($dataInDB)) {
				return $dataInDB;
			}
			
			return false;
		} else {
			return false;
		}
	}
	/**
	 *  Запись в регистр БСК в ПН ОКС с АРМ Админситратора СМП / ... / Подстанции СМП
	 */		
	function saveInOKS($data) {
		$IsMainServer = $this->config->item('IsMainServer');
		$params = array(
			'Person_id' => $data['Person_id'],
			'MorbusType_id' => $data['MorbusType_id']
		);
		if(is_null($data['Person_id']) || $data['Person_id'] == ''){
			return array('Error_Msg' => 'Ошибка сохранения ОКС! Не определен пациент!');
		}
		$PersonRegister =  $this->checkPersonInRegister($params);

		//Проверить наличия пациента в dbo.PersonRegister
		if($data['Person_id'] !=0 && $this->checkPersonInRegister($params) == false){
			//Записать пациента в dbo.PersonRegister с MorbusType = 19
			$disDate = preg_match('/^\d{4}\-\d{2}\-\d{2}/', $data['AcceptTime'])?date('Y-m-d', strtotime($data['AcceptTime'] . "+1 year")) : date('Y-m-d', strtotime('+1 year'));
			$params = array(  
				'Person_id' => $data['Person_id'],
				'MorbusType_id' => $data['MorbusType_id'],
				'Diag_id' => $data['Diag_id'],
				'PersonRegister_Code' =>null,
				'PersonRegister_setDate' => preg_match('/^\d{4}\-\d{2}\-\d{2}/', $data['AcceptTime'])?date('Y-m-d', strtotime($data['AcceptTime'])) : date('Y-m-d'),
				'PersonRegister_disDate' => $data['MorbusType_id'] == 19 ? $disDate : null,
				'Morbus_id' => null,
				'PersonRegisterOutCause_id' => null,
				'MedPersonal_iid' => null,
				'Lpu_iid' => null,
				'MedPersonal_did' => null,
				'Lpu_did' =>null,
				'EvnNotifyBase_id' => null,
				'pmUser_id' => $data['pmUser_id'],
				'PersonRegister_id'=>null
			);


			if($IsMainServer === true) {
				//проверяем подключение к СМП
				unset($this->db);

				try{
					$this->load->database('smp');
				} catch (Exception $e) {
					$this->load->database();
					$errMsg = "Нет связи с сервером: сохранение ОКС недоступно";
					//$this->ReturnError($errMsg);
					return false;
				}

				$pr_res = $this->saveInPersonRegister($params);
				//возвращаемся на рабочую
				unset($this->db);
				$this->load->database();

			}else{
				$pr_res = $this->saveInPersonRegister($params);
			}

		} else {
			$pr_res = $PersonRegister;
			$pr_disDate = date('Y-m-d', strtotime($pr_res[0]['PersonRegister_disDate']));
			$disDate = preg_match('/^\d{4}\-\d{2}\-\d{2}/', $data['AcceptTime'])?date('Y-m-d', strtotime($data['AcceptTime'] . "+1 year")) : date('Y-m-d', strtotime('+1 year'));
			if (!empty($pr_res[0]['PersonRegister_id']) && $pr_disDate < $disDate) {
				$params = array(  
					'PersonRegister_id'=>$pr_res[0]['PersonRegister_id'],
					'PersonRegister_disDate' => $data['MorbusType_id'] == 19 ? $disDate : null
				);	
	
				if($IsMainServer === true) {
					//проверяем подключение к СМП
					unset($this->db);
	
					try{
						$this->load->database('smp');
					} catch (Exception $e) {
						$this->load->database();
						$errMsg = "Нет связи с сервером: сохранение ОКС недоступно";
						//$this->ReturnError($errMsg);
						return false;
					}
	
					$prReg_res = $this->saveObject('PersonRegister',$params);
					//возвращаемся на рабочую
					unset($this->db);
					$this->load->database();
	
				}else{
					$prReg_res = $this->saveObject('PersonRegister',$params);
				}
			}
		}
		//return $PersonRegister;
		$BSKRegistry = array(
			'BSKRegistry_riskGroup'      => null,
			'MorbusType_id'              => $data['MorbusType_id'],
			'Person_id'                  => $data['Person_id'],
			'BSKRegistry_setDate'        => preg_match('/^\d{4}\-\d{2}\-\d{2}/', $data['AcceptTime'])?date('Y-m-d H:i:s', strtotime($data['AcceptTime'])) : date('Y-m-d H:i:s'),
			'PersonRegister_id'          => $pr_res[0]['PersonRegister_id'],
			'BSKRegistryFormTemplate_id' => 4, //актуальный шаблон по ОКС можно запросом вытащить но все равно при смене шаблона тут менять BSKRegistryData
			'BSKRegistry_nextDate'       => null
		);

		$BSKRegistryData = array(
				//Время прибытия к пациенту
				270=>array(
						'BSKObservElement_id'         => 270,
						'BSKObservElementValues_id'   => null,
						'BSKRegistryData_AnswerText'  => null,
						'BSKRegistryData_AnswerInt'   => null,
						'BSKRegistryData_AnswerFloat' => null,
						'BSKRegistryData_AnswerDT'    => preg_match('/^\d{4}\-\d{2}\-\d{2}/', $data['ArrivalDT'])?date('Y-m-d H:i:s', strtotime($data['ArrivalDT'])):null,
						'BSKRegistryData_id'          => null
				),
				//Время начала болевых симптомов
				271=>array(
						'BSKObservElement_id'         => 271,
						'BSKObservElementValues_id'   => null,
						'BSKRegistryData_AnswerText'  => null,
						'BSKRegistryData_AnswerInt'   => null,
						'BSKRegistryData_AnswerFloat' => null,
						'BSKRegistryData_AnswerDT'    => preg_match('/^\d{4}\-\d{2}\-\d{2}/', $data['PainDT'])?date('Y-m-d H:i:s', strtotime($data['PainDT'])):null,
						'BSKRegistryData_id'          => null
				),   
				//Время проведения ЭКГ
				272=>array(
						'BSKObservElement_id'         => 272,
						'BSKObservElementValues_id'   => null,
						'BSKRegistryData_AnswerText'  => null,
						'BSKRegistryData_AnswerInt'   => null,
						'BSKRegistryData_AnswerFloat' => null,
						'BSKRegistryData_AnswerDT'    => preg_match('/^\d{4}\-\d{2}\-\d{2}/', $data['ECGDT'])?date('Y-m-d H:i:s', strtotime($data['ECGDT'])):null,
						'BSKRegistryData_id'          => null
				),  
				//Результат ЭКГ
				273=>array(
						'BSKObservElement_id'         => 273,
						'BSKObservElementValues_id'   => null,
						'BSKRegistryData_AnswerText'  => $data['ResultECG'],
						'BSKRegistryData_AnswerInt'   => null,
						'BSKRegistryData_AnswerFloat' => null,
						'BSKRegistryData_AnswerDT'    => null,
						'BSKRegistryData_id'          => null
				),  
				//Результат проведения ТЛТ
				274=>array(
						'BSKObservElement_id'         => 274,
						'BSKObservElementValues_id'   => null,
						'BSKRegistryData_AnswerText'  => null,
						'BSKRegistryData_AnswerInt'   => null,
						'BSKRegistryData_AnswerFloat' => null,
						'BSKRegistryData_AnswerDT'    => preg_match('/^\d{4}\-\d{2}\-\d{2}/', $data['TLTDT'])?date('Y-m-d H:i:s', strtotime($data['TLTDT'])):null,
						'BSKRegistryData_id'          => null
				),
				//Причина отказа от ТЛТ
				275=>array(
						'BSKObservElement_id'         => 275,
						'BSKObservElementValues_id'   => null,
						'BSKRegistryData_AnswerText'  => $data['FailTLT'],
						'BSKRegistryData_AnswerInt'   => null,
						'BSKRegistryData_AnswerFloat' => null,
						'BSKRegistryData_AnswerDT'    => null,
						'BSKRegistryData_id'          => null
				), 
				//Время прибытия в МО
				276=>array(
						'BSKObservElement_id'         => 276,
						'BSKObservElementValues_id'   => null,
						'BSKRegistryData_AnswerText'  => null,
						'BSKRegistryData_AnswerInt'   => null,
						'BSKRegistryData_AnswerFloat' => null,
						'BSKRegistryData_AnswerDT'    => preg_match('/^\d{4}\-\d{2}\-\d{2}/', $data['LpuDT'])?date('Y-m-d H:i:s', strtotime($data['LpuDT'])):null,
						'BSKRegistryData_id'          => null
				),	
				//Зона ответственности МО
				301=>array(
						'BSKObservElement_id'         => 301,
						'BSKObservElementValues_id'   => null,
						'BSKRegistryData_AnswerText'  => $data['ZonaMO'],
						'BSKRegistryData_AnswerInt'   => null,
						'BSKRegistryData_AnswerFloat' => null,
						'BSKRegistryData_AnswerDT'    => null,
						'BSKRegistryData_id'          => null
				), 
				//Зона ответственности ЧКВ
				414=>array(
						'BSKObservElement_id'         => 414,
						'BSKObservElementValues_id'   => null,
						'BSKRegistryData_AnswerText'  => $data['ZonaCHKV'],
						'BSKRegistryData_AnswerInt'   => null,
						'BSKRegistryData_AnswerFloat' => null,
						'BSKRegistryData_AnswerDT'    => null,
						'BSKRegistryData_id'          => null
				),
				//Диагноз
				300=>array(
						'BSKObservElement_id'         => 300,
						'BSKObservElementValues_id'   => null,
						'BSKRegistryData_AnswerText'  => $data['DiagOKS'],
						'BSKRegistryData_AnswerInt'   => null,
						'BSKRegistryData_AnswerFloat' => null,
						'BSKRegistryData_AnswerDT'    => null,
						'BSKRegistryData_id'          => null
				),
				//CmpCallCard_id
				303=>array(
						'BSKObservElement_id'         => 303,
						'BSKObservElementValues_id'   => null,
						'BSKRegistryData_AnswerText'  => null,
						'BSKRegistryData_AnswerInt'   => $data['CmpCallCard_id'],
						'BSKRegistryData_AnswerFloat' => null,
						'BSKRegistryData_AnswerDT'    => null,
						'BSKRegistryData_id'          => null
				),   
				//MOHospital
				304=>array(
						'BSKObservElement_id'         => 304,
						'BSKObservElementValues_id'   => null,
						'BSKRegistryData_AnswerText'  => $data['MOHospital'],
						'BSKRegistryData_AnswerInt'   => null,
						'BSKRegistryData_AnswerFloat' => null,
						'BSKRegistryData_AnswerDT'    => null,
						'BSKRegistryData_id'          => null
				), 
				
				/**
				 *  дополнительно
				 */ 
				//Номер фельдшера по приёму вызова
				305=>array(
						'BSKObservElement_id'         => 305,
						'BSKObservElementValues_id'   => null,
						'BSKRegistryData_AnswerText'  => $data['MedStaffFact_num'],
						'BSKRegistryData_AnswerInt'   => null,
						'BSKRegistryData_AnswerFloat' => null,
						'BSKRegistryData_AnswerDT'    => null,
						'BSKRegistryData_id'          => null
				),
				//Станция (подстанция), отделения
				306=>array(
						'BSKObservElement_id'         => 306,
						'BSKObservElementValues_id'   => null,
						'BSKRegistryData_AnswerText'  => $data['LpuBuilding_name'],
						'BSKRegistryData_AnswerInt'   => null,
						'BSKRegistryData_AnswerFloat' => null,
						'BSKRegistryData_AnswerDT'    => null,
						'BSKRegistryData_id'          => null
				), 
				307=>array(
						'BSKObservElement_id'         => 307,
						'BSKObservElementValues_id'   => null,
						'BSKRegistryData_AnswerText'  => $data['EmergencyTeam_number'],
						'BSKRegistryData_AnswerInt'   => null,
						'BSKRegistryData_AnswerFloat' => null,
						'BSKRegistryData_AnswerDT'    => null,
						'BSKRegistryData_id'          => null
				),  
				308=>array(
						'BSKObservElement_id'         => 308,
						'BSKObservElementValues_id'   => null,
						'BSKRegistryData_AnswerText'  => null,
						'BSKRegistryData_AnswerInt'   => null,
						'BSKRegistryData_AnswerFloat' => null,
						'BSKRegistryData_AnswerDT'    => preg_match('/^\d{4}\-\d{2}\-\d{2}/', $data['AcceptTime'])?date('Y-m-d H:i:s', strtotime($data['AcceptTime'])):null,
						'BSKRegistryData_id'          => null
				),  
				// 309=>array(
				// 		'BSKObservElement_id'         => 309,
				// 		'BSKObservElementValues_id'   => null,
				// 		'BSKRegistryData_AnswerText'  => null,
				// 		'BSKRegistryData_AnswerInt'   => null,
				// 		'BSKRegistryData_AnswerFloat' => null,
				// 		'BSKRegistryData_AnswerDT'    => $data['TransTime'],
				// 		'BSKRegistryData_id'          => null
				// ),  
				// 310=>array(
				// 		'BSKObservElement_id'         => 310,
				// 		'BSKObservElementValues_id'   => null,
				// 		'BSKRegistryData_AnswerText'  => null,
				// 		'BSKRegistryData_AnswerInt'   => null,
				// 		'BSKRegistryData_AnswerFloat' => null,
				// 		'BSKRegistryData_AnswerDT'    => $data['GoTime'],
				// 		'BSKRegistryData_id'          => null
				// ),  
				311=>array(
						'BSKObservElement_id'         => 311,
						'BSKObservElementValues_id'   => null,
						'BSKRegistryData_AnswerText'  => null,
						'BSKRegistryData_AnswerInt'   => null,
						'BSKRegistryData_AnswerFloat' => null,
						'BSKRegistryData_AnswerDT'    => preg_match('/^\d{4}\-\d{2}\-\d{2}/', $data['TransportTime'])?date('Y-m-d H:i:s', strtotime($data['TransportTime'])):null,
						'BSKRegistryData_id'          => null
				),  
				312=>array(
						'BSKObservElement_id'         => 312,
						'BSKObservElementValues_id'   => null,
						'BSKRegistryData_AnswerText'  => null,
						'BSKRegistryData_AnswerInt'   => null,
						'BSKRegistryData_AnswerFloat' => null,
						'BSKRegistryData_AnswerDT'    => preg_match('/^\d{4}\-\d{2}\-\d{2}/', $data['EndTime'])?date('Y-m-d H:i:s', strtotime($data['EndTime'])):null,
						'BSKRegistryData_id'          => null
				),  
				// 313=>array(
				// 		'BSKObservElement_id'         => 313,
				// 		'BSKObservElementValues_id'   => null,
				// 		'BSKRegistryData_AnswerText'  => null,
				// 		'BSKRegistryData_AnswerInt'   => null,
				// 		'BSKRegistryData_AnswerFloat' => null,
				// 		'BSKRegistryData_AnswerDT'    => $data['BackTime'],
				// 		'BSKRegistryData_id'          => null
				// ),	 
				// 314=>array(
				// 		'BSKObservElement_id'         => 314,
				// 		'BSKObservElementValues_id'   => null,
				// 		'BSKRegistryData_AnswerText'  => $data['SummTime'],
				// 		'BSKRegistryData_AnswerInt'   => null,
				// 		'BSKRegistryData_AnswerFloat' => null,
				// 		'BSKRegistryData_AnswerDT'    => null,
				// 		'BSKRegistryData_id'          => null
				// ),
				415=>array(
						'BSKObservElement_id'         => 415,
						'BSKObservElementValues_id'   => null,
						'BSKRegistryData_AnswerText'  => $data['TLTres'],
						'BSKRegistryData_AnswerInt'   => null,
						'BSKRegistryData_AnswerFloat' => null,
						'BSKRegistryData_AnswerDT'    => null,
						'BSKRegistryData_id'          => null
				),
				416=>array(
						'BSKObservElement_id'         => 416,
						'BSKObservElementValues_id'   => null,
						'BSKRegistryData_AnswerText'  => $data['UslugaTLT'],
						'BSKRegistryData_AnswerInt'   => null,
						'BSKRegistryData_AnswerFloat' => null,
						'BSKRegistryData_AnswerDT'    => null,
						'BSKRegistryData_id'          => null
				)
		);

		/**
		 *  Противопоказания к проведения ТЛТ
		*/
		//абсолютные противопоказания
		$AbsoluteList = json_decode($data['AbsoluteList']);
		//относительные противопоказания
		$RelativeList = json_decode($data['RelativeList']);	
		foreach($AbsoluteList as $k=>$v){
			$link = $this->getBSKObservElement_id($k);
			$BSKRegistryData[$link] = array(
						'BSKObservElement_id'         => $link,
						'BSKObservElementValues_id'   => null,
						'BSKRegistryData_AnswerText'  => $v,
						'BSKRegistryData_AnswerInt'   => null,
						'BSKRegistryData_AnswerFloat' => null,
						'BSKRegistryData_AnswerDT'    => null,
						'BSKRegistryData_id'          => null
				);
		}

		foreach($RelativeList as $k=>$v){
			$link = $this->getBSKObservElement_id($k);
			$BSKRegistryData[$link] = array(
						'BSKObservElement_id'         => $link,
						'BSKObservElementValues_id'   => null,
						'BSKRegistryData_AnswerText'  => $v,
						'BSKRegistryData_AnswerInt'   => null,
						'BSKRegistryData_AnswerFloat' => null,
						'BSKRegistryData_AnswerDT'    => null,
						'BSKRegistryData_id'          => null
				);
		}

		if(!empty($data['CmpCallCard_id'])) {
			$bskparams = [
				'CmpCallCard_id' => $data['CmpCallCard_id']
			];
			$BSKRegistry_id = $this->isExistObjectRecord('BSKRegistry',$bskparams);
			$data['Registry_method'] = !empty($BSKRegistry_id) ? $BSKRegistry_id : 'ins';
		}

		if($data['Registry_method'] == 'ins') {
			// добавляем
			$result = $this->saveBSKRegistry($data, $BSKRegistry, $BSKRegistryData);
		} else {
			// обновляем
			$BSKRegistry['BSKRegistry_id'] = $BSKRegistry_id;
			$lastData = $this->getLastBSKRegistryData($BSKRegistry_id);
			if ($lastData !== false) {
				foreach($BSKRegistryData as $k=>$v){
					foreach($lastData as $kl=>$vl) {
						if ($v['BSKObservElement_id'] == $vl['BSKObservElement_id'])
							$BSKRegistryData[$k]['BSKRegistryData_id'] = $vl['BSKRegistryData_id'];
					}
				}
			}
			$result =  $this->updateBSKRegistry($data, $BSKRegistry, $BSKRegistryData);
		}
		return $result;
	}
	/**
	 *  Сохранение в регистр БСК (предмет наблюдения ОКС)
	 */		
	function saveKvsInOKS($data) {
		$params = array(
			'Person_id' => $data['Person_id'],
			'MorbusType_id' => $data['MorbusType_id']
		);
		if(is_null($data['Person_id']) || $data['Person_id'] == ''){
			return array('Error_Msg' => 'Ошибка сохранения ОКС! Не определен пациент!');
		}
		$PersonRegister =  $this->checkPersonInRegister($params);

		//Проверить наличия пациента в dbo.PersonRegister
		if($data['Person_id'] !=0 && $this->checkPersonInRegister($params) == false){
			//Записать пациента в dbo.PersonRegister с MorbusType = 19
			$disDate = preg_match('/^\d{2}\-\d{2}\-\d{4}/', $data['LpuDT'])?date('Y-m-d', strtotime($data['LpuDT'] . "+1 year")) : date('Y-m-d', strtotime('+1 year'));
			$params = array(  
				'Person_id' => $data['Person_id'],
				'MorbusType_id' => $data['MorbusType_id'],
				'Diag_id' => $data['Diag_id'],
				'PersonRegister_Code' =>null,
				'PersonRegister_setDate' => preg_match('/^\d{2}\-\d{2}\-\d{4}/', $data['LpuDT'])?date('Y-m-d', strtotime($data['LpuDT'])) : date('Y-m-d'),
				'PersonRegister_disDate' => $data['MorbusType_id'] == 19 ? $disDate : null,
				'Morbus_id' => null,
				'PersonRegisterOutCause_id' => null,
				'MedPersonal_iid' => null,
				'Lpu_iid' => null,
				'MedPersonal_did' => null,
				'Lpu_did' =>null,
				'EvnNotifyBase_id' => null,
				'pmUser_id' => $data['pmUser_id'],
				'PersonRegister_id'=>null
			);
			$pr_res = $this->saveInPersonRegister($params);
		} else {
			$pr_res = $PersonRegister;
			$pr_disDate = date('Y-m-d', strtotime($pr_res[0]['PersonRegister_disDate']));
			$disDate = preg_match('/^\d{2}\-\d{2}\-\d{4}/', $data['LpuDT'])?date('Y-m-d', strtotime($data['LpuDT'] . "+1 year")) : date('Y-m-d', strtotime('+1 year'));
			if (!empty($pr_res[0]['PersonRegister_id']) && $pr_disDate < $disDate) {
				$params = array(  
					'PersonRegister_id'=>$pr_res[0]['PersonRegister_id'],
					'PersonRegister_disDate' => $data['MorbusType_id'] == 19 ? $disDate : null
				);
				$prReg_res = $this->saveObject('PersonRegister',$params);
			}
		}
		//return $PersonRegister;
		$BSKRegistry = array(
			'BSKRegistry_riskGroup'      => null,
			'MorbusType_id'              => $data['MorbusType_id'],
			'Person_id'                  => $data['Person_id'],
			'BSKRegistry_setDate'        => preg_match('/^\d{2}\-\d{2}\-\d{4}/', $data['LpuDT'])?date('Y-m-d H:i:s', strtotime($data['LpuDT'])) : date('Y-m-d H:i:s'),
			'PersonRegister_id'          => $pr_res[0]['PersonRegister_id'],
			'BSKRegistryFormTemplate_id' => 4, //актуальный шаблон по ОКС можно запросом вытащить но все равно при смене шаблона тут менять BSKRegistryData
			'BSKRegistry_nextDate'       => null
		);

		$BSKRegistryData = array(
				//Время начала болевых симптомов
				271=>array(
						'BSKObservElement_id'         => 271,
						'BSKObservElementValues_id'   => null,
						'BSKRegistryData_AnswerText'  => null,
						'BSKRegistryData_AnswerInt'   => null,
						'BSKRegistryData_AnswerFloat' => null,
						'BSKRegistryData_AnswerDT'    => preg_match('/^\d{2}\-\d{2}\-\d{4}/', $data['PainDT'])?date('Y-m-d H:i:s', strtotime($data['PainDT'])):null,
						'BSKRegistryData_id'          => null
				),
				//Время проведения ЭКГ
				272=>array(
						'BSKObservElement_id'         => 272,
						'BSKObservElementValues_id'   => null,
						'BSKRegistryData_AnswerText'  => null,
						'BSKRegistryData_AnswerInt'   => null,
						'BSKRegistryData_AnswerFloat' => null,
						'BSKRegistryData_AnswerDT'    => preg_match('/^\d{2}\-\d{2}\-\d{4}/', $data['ECGDT'])?date('Y-m-d H:i:s', strtotime($data['ECGDT'])):null,
						'BSKRegistryData_id'          => null
				),  
				//Результат ЭКГ
				273=>array(
						'BSKObservElement_id'         => 273,
						'BSKObservElementValues_id'   => null,
						'BSKRegistryData_AnswerText'  => $data['ResultECG'],
						'BSKRegistryData_AnswerInt'   => null,
						'BSKRegistryData_AnswerFloat' => null,
						'BSKRegistryData_AnswerDT'    => null,
						'BSKRegistryData_id'          => null
				),  
				//Результат проведения ТЛТ
				274=>array(
						'BSKObservElement_id'         => 274,
						'BSKObservElementValues_id'   => null,
						'BSKRegistryData_AnswerText'  => null,
						'BSKRegistryData_AnswerInt'   => null,
						'BSKRegistryData_AnswerFloat' => null,
						'BSKRegistryData_AnswerDT'    => preg_match('/^\d{2}\-\d{2}\-\d{4}/', $data['TLTDT'])?date('Y-m-d H:i:s', strtotime($data['TLTDT'])):null,
						'BSKRegistryData_id'          => null
				),
				//Время прибытия в МО
				276=>array(
						'BSKObservElement_id'         => 276,
						'BSKObservElementValues_id'   => null,
						'BSKRegistryData_AnswerText'  => null,
						'BSKRegistryData_AnswerInt'   => null,
						'BSKRegistryData_AnswerFloat' => null,
						'BSKRegistryData_AnswerDT'    => preg_match('/^\d{2}\-\d{2}\-\d{4}/', $data['LpuDT'])?date('Y-m-d H:i:s', strtotime($data['LpuDT'])):null,
						'BSKRegistryData_id'          => null
				),	
				//Диагноз
				300=>array(
						'BSKObservElement_id'         => 300,
						'BSKObservElementValues_id'   => null,
						'BSKRegistryData_AnswerText'  => $data['DiagOKS'],
						'BSKRegistryData_AnswerInt'   => null,
						'BSKRegistryData_AnswerFloat' => null,
						'BSKRegistryData_AnswerDT'    => null,
						'BSKRegistryData_id'          => null
				),
				//CmpCallCard_id
				302=>array(
						'BSKObservElement_id'         => 302,
						'BSKObservElementValues_id'   => null,
						'BSKRegistryData_AnswerText'  => null,
						'BSKRegistryData_AnswerInt'   => null,
						'BSKRegistryData_AnswerFloat' => null,
						'BSKRegistryData_AnswerDT'    => preg_match('/^\d{2}\-\d{2}\-\d{4}/', $data['ZonaCHKV'])?date('Y-m-d H:i:s', strtotime($data['ZonaCHKV'])):null,
						'BSKRegistryData_id'          => null
				),
				//MOHospital
				304=>array(
						'BSKObservElement_id'         => 304,
						'BSKObservElementValues_id'   => null,
						'BSKRegistryData_AnswerText'  => $data['MOHospital'],
						'BSKRegistryData_AnswerInt'   => null,
						'BSKRegistryData_AnswerFloat' => null,
						'BSKRegistryData_AnswerDT'    => null,
						'BSKRegistryData_id'          => null
				), 
				398=>array(
						'BSKObservElement_id'         => 398,
						'BSKObservElementValues_id'   => null,
						'BSKRegistryData_AnswerText'  => $data['EvnPS_NumCard'],
						'BSKRegistryData_AnswerInt'   => null,
						'BSKRegistryData_AnswerFloat' => null,
						'BSKRegistryData_AnswerDT'    => null,
						'BSKRegistryData_id'          => null
				), 
				399=>array(
						'BSKObservElement_id'         => 399,
						'BSKObservElementValues_id'   => null,
						'BSKRegistryData_AnswerText'  => $data['TimeFromEnterToChkv'],
						'BSKRegistryData_AnswerInt'   => null,
						'BSKRegistryData_AnswerFloat' => null,
						'BSKRegistryData_AnswerDT'    => null,
						'BSKRegistryData_id'          => null
				),  
				400=>array(
						'BSKObservElement_id'         => 400,
						'BSKObservElementValues_id'   => null,
						'BSKRegistryData_AnswerText'  => $data['LeaveType_Name'],
						'BSKRegistryData_AnswerInt'   => null,
						'BSKRegistryData_AnswerFloat' => null,
						'BSKRegistryData_AnswerDT'    => null,
						'BSKRegistryData_id'          => null
				),  
				410=>array(
						'BSKObservElement_id'         => 410,
						'BSKObservElementValues_id'   => null,
						'BSKRegistryData_AnswerText'  => $data['diagDir'],
						'BSKRegistryData_AnswerInt'   => null,
						'BSKRegistryData_AnswerFloat' => null,
						'BSKRegistryData_AnswerDT'    => null,
						'BSKRegistryData_id'          => null
				),  
				411=>array(
						'BSKObservElement_id'         => 411,
						'BSKObservElementValues_id'   => null,
						'BSKRegistryData_AnswerText'  => $data['diagPriem'],
						'BSKRegistryData_AnswerInt'   => null,
						'BSKRegistryData_AnswerFloat' => null,
						'BSKRegistryData_AnswerDT'    => null,
						'BSKRegistryData_id'          => null
				),  
				412=>array(
						'BSKObservElement_id'         => 412,
						'BSKObservElementValues_id'   => null,
						'BSKRegistryData_AnswerText'  => $data['LpuSection'],
						'BSKRegistryData_AnswerInt'   => null,
						'BSKRegistryData_AnswerFloat' => null,
						'BSKRegistryData_AnswerDT'    => null,
						'BSKRegistryData_id'          => null
				),
				413=>array(
						'BSKObservElement_id'         => 413,
						'BSKObservElementValues_id'   => null,
						'BSKRegistryData_AnswerText'  => null,
						'BSKRegistryData_AnswerInt'   => null,
						'BSKRegistryData_AnswerFloat' => null,
						'BSKRegistryData_AnswerDT'    => preg_match('/^\d{2}\-\d{2}\-\d{4}/', $data['KAGDT'])?date('Y-m-d H:i:s', strtotime($data['KAGDT'])):null,
						'BSKRegistryData_id'          => null
				)
		);

		if($data['Registry_method'] == 'ins') {
			// добавляем
			$result = $this->saveBSKRegistry($data, $BSKRegistry, $BSKRegistryData);
		} else {
			// обновляем
			$BSKRegistry['BSKRegistry_id'] = $data['Registry_method'];
			$lastData = $this->getLastBSKRegistryData($data['Registry_method']);
			if ($lastData !== false) {
				foreach($BSKRegistryData as $k=>$v){
					foreach($lastData as $kl=>$vl) {
						if ($v['BSKObservElement_id'] == $vl['BSKObservElement_id'])
							$BSKRegistryData[$k]['BSKRegistryData_id'] = $vl['BSKRegistryData_id'];
					}
				}
			}
			$result =  $this->updateBSKRegistry($data, $BSKRegistry, $BSKRegistryData);
		}
		return $result;
	}
	/**
	  * Получение идентификатора анкеты по номеру КВС и Person_id
	*/
	function getOksId($data) {

		$params = array(
				'Person_id' => $data['Person_id'],
				'EvnPS_NumCard' => $data['EvnPS_NumCard']
		);

		$query = "
			SELECT 
				P.Person_id as \"Person_id\",
				P.Person_deadDT as \"Person_deadDT\",
				BRD.BSKRegistry_id as \"BSKRegistry_id\"
			FROM v_Person P
			left join lateral (
				select r.BSKRegistry_id 
				from dbo.v_BSKRegistry r
				inner join dbo.v_BSKRegistryData rd on rd.BSKRegistry_id = r.BSKRegistry_id
				where BSKObservElement_id = 398 
					and coalesce(rd.BSKRegistryData_AnswerText,rd.BSKRegistryData_data) = :EvnPS_NumCard
					and r.Person_id = P.Person_id
				order by rd.BSKRegistryData_id desc
				limit 1
			) BRD on true
			WHERE P.Person_id = :Person_id
			limit 1
		";
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 *	Сохранение диагнозов
	 */
	function savePrognosDiseases($data) {

		$PrognosOslDiagList = array(); 
		$PrognosOslDiagArr = array(); 
		foreach($data['PrognosOslDiagList'] as $diag_id) {
			if (in_array($diag_id[0], $PrognosOslDiagArr)) {
				throw new Exception('Ввод одинаковых осложнений заболевания не допускается', 500);
			}
			$PrognosOslDiagArr[] = $diag_id[0];
			$OslDiagList[] = $diag_id;
		}
		
		$this->deletePrognosDiseases($data);
	
		
		foreach($data['PrognosOslDiagList'] as $diag_id) {
			$this->queryResult("
				select 
					error_code as \"Error_Code\", 
					error_message as \"Error_Msg\"

				from p_bskdiagprognos_ins (
					bskdiagprognos_id := :BSKDiagPrognos_id,
					person_id := :Person_id,
					diag_id := :Diag_id,
					bskdiagprognos_descriptdiag := :DescriptDiag,
					pmUser_id := :pmUser_id
				)
			", array(
				'BSKDiagPrognos_id' => null,
				'Person_id' => $data['Person_id'],
				'Diag_id' =>$diag_id[0],
				'DescriptDiag' => $diag_id[1],
				'pmUser_id' => $data['pmUser_id']
			));
		}

		return array('success' => true);
	}
	/**
	 *	Удаление диагнозов
	 */
	function deletePrognosDiseases($data) {
		$resp = $this->queryResult("
			select BSKDiagPrognos_id as \"BSKDiagPrognos_id\" from BSKDiagPrognos where Person_id = :Person_id
		", $data);
		
		foreach($resp as $item) {
			$this->queryResult("
				select 
					Error_Code as \"Error_Code\", 
					Error_Msg as \"Error_Msg\"

				from p_BSKDiagPrognos_del (
					BSKDiagPrognos_id := :BSKDiagPrognos_id
				)
			", array(
				'BSKDiagPrognos_id' => $item['BSKDiagPrognos_id']
			));
		}
	}
	/**
	*	Получение прогнозируемых осложнений основного заболевания
	*/
	function loadPrognosDiseases($data)	{
		$query = "
			select
				BSKDiagPrognos_id as \"BSKDiagPrognos_id\", 
				Person_id as \"Person_id\", 
				Diag_id as \"Diag_id\", 
				BSKDiagPrognos_DescriptDiag as \"DescriptDiag\"
			from
				v_BSKDiagPrognos
			where
				Person_id = :Person_id
		";
		
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 *  Сроки заполнения анкет в предметах наблюдения в зависимости от группы риска
	 *  
	 */
	function getNextDateAnket($BSKRegistry) {
		//Дата следующего осмотра автоматически должна рассчитываться от значения в поле «Дата анкетирования» по алгоритму #196897
		switch ((int)$BSKRegistry['BSKRegistry_riskGroup']) {
			case 2:
				$setDate = date_create($BSKRegistry['BSKRegistry_setDate']);
				date_modify($setDate, '+12 month');
				$nextDate = date_create($BSKRegistry['BSKRegistry_nextDate']);
				if ($nextDate > $setDate)
					$BSKRegistry['BSKRegistry_nextDate'] = $setDate->format('Y-m-d');
				break;
			case 3:
				$setDate = date_create($BSKRegistry['BSKRegistry_setDate']);
				date_modify($setDate, '+6 month');
				$nextDate = date_create($BSKRegistry['BSKRegistry_nextDate']);
				if ($nextDate > $setDate)
					$BSKRegistry['BSKRegistry_nextDate'] = $setDate->format('Y-m-d');
				break;
				
			default:
				# code...
				break;
		}
		return $BSKRegistry['BSKRegistry_nextDate'];

	}

}
?>
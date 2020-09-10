<?php
/**
* Astra_Polka_PersonCard_model - модель, для работы с таблицей PersonCard (Астрахань)
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stanislav Bykov (savage@swan.perm.ru)
* @version      27.05.2015
*/

require_once(APPPATH.'models/_pgsql/Polka_PersonCard_model.php');

class Astra_Polka_PersonCard_model extends Polka_PersonCard_model {
	/**
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 *	Список прикрепленного населения к указанной МО на указанную дату
	 */
	function loadAttachedList($data)
	{
		$filterList = array();
		$queryParams = array(
			'Lpu_id' => $data['AttachLpu_id'],
			'OrgSMO_id' => $data['OrgSMO_id'],
			'Date_upload' => $data['Date_upload']
		);

		if ( !empty($data['OrgSMO_id']) ) {
			$filterList[] = "PLS.OrgSmo_id = :OrgSMO_id";
		}

		$query = "
			select distinct
				PC.PersonCard_id as \"Field_id\",
				'1' as \"Field_Type\",
				PC.Person_id as \"IDCASE\", -- Уникальный идентификатор пациента 
				rtrim(upper(PS.Person_SurName)) as \"FAM\", -- Фамилия
				rtrim(upper(PS.Person_FirName)) as \"IM\", -- Имя
				coalesce(rtrim(Upper(case when Replace(PS.Person_Secname,' ','')='---'  or PS.Person_Secname = '' then 'НЕТ' else PS.Person_Secname end)), 'НЕТ') as \"OT\", -- Отчество
				to_char(PS.Person_BirthDay, 'yyyy-mm-dd') as \"DR\", -- Дата рождения застрахованного
				PS.Person_Snils as \"SNILS\",
				DT.DocumentType_Code as \"DOCTYPE\",
				D.Document_Ser as \"DOCSER\",
				D.Document_Num as \"DOCNUM\",
				to_char(D.Document_begDate, 'yyyy-mm-dd') as \"DOCDT\",
				null as \"TEL\",
				PI.Person_BDZCode as \"RZ\",
				case when pc.PersonCardAttach_id is not null then 2 else 1 end as \"SP_PRIK\",
				case when pc.PersonCard_endDate is null then 1 else 2 end as \"T_PRIK\",
				to_char(coalesce(PC.PersonCard_endDate, PC.PersonCard_begDate), 'yyyy-mm-dd') as \"DATE_1\",
				case when PC.PersonCard_endDate is null and ADDRESSCHANGE.PersonUAddress_id is not null then 1 else 0 end as \"N_ADR\",
				right('00000000' || coalesce(L.Lpu_f003mcod,'') || coalesce(LB.LpuBuilding_Code, ''), 8) as \"KODPODR\",
				case
					when LR.LpuRegionType_SysNick in ('ter', 'ped', 'vop') then
						case
							when LR.LpuRegionType_SysNick in ('ter', 'vop') then '1'
							when LR.LpuRegionType_SysNick in ('ped') then '2'
							else ''
						end || coalesce(LR.LpuRegion_Name,'')
					else ''
				end as \"LPUUCH\",
				case
					when MSF.MedStaffFact_id is not null then coalesce(MP.Person_Snils, '')
					else coalesce(LRMSF.Person_Snils, '')
				end as \"SSD\",
				case
					when MSF.MedStaffFact_id is not null and MSF.PostKind_id = 1 then 1
					when MSF.MedStaffFact_id is not null then 2
					when LRMSF.PostKind_id = 1 then 1
					else 2
				end as \"MEDRAB\"
			from
				PersonCard PC
				left join v_PersonState PS on PS.Person_id = PC.Person_id
				left join v_Polis PLS on PLS.Polis_id = PS.Polis_id
				left join v_Lpu L on L.Lpu_id = PC.Lpu_id
				left join v_Document D on D.Document_id = PS.Document_id
				left join v_DocumentType DT on DT.DocumentType_id = D.DocumentType_id
				left join v_LpuRegion LR on LR.LpuRegion_id = PC.LpuRegion_id
				left join v_LpuSection LS on LS.LpuSection_id = LR.LpuSection_id
				left join v_LpuBuilding LB on LB.LpuBuilding_id = LS.LpuBuilding_id
				left join v_MedStaffFact MSF on MSF.MedStaffFact_id = PC.MedStaffFact_id
				left join v_MedPersonal MP on MP.MedPersonal_id = MSF.MedPersonal_id
				left join lateral (
					select
						t2.Person_Snils, t3.PostKind_id
					from v_MedStaffRegion t1
						inner join v_MedStaffFact t3 on t3.MedStaffFact_id = t1.MedStaffFact_id
						inner join v_MedPersonal t2 on t2.MedPersonal_id = t3.MedPersonal_id
					where t1.LpuRegion_id = PC.LpuRegion_id
						and t2.Person_Snils is not null
						and t3.Lpu_id = :Lpu_id
						and (t3.WorkData_begDate is null or t3.WorkData_begDate <= PC.PersonCard_begDate)
						and (t3.WorkData_endDate is null or t3.WorkData_endDate >= PC.PersonCard_begDate)
						and (t1.MedStaffRegion_begDate is null or t1.MedStaffRegion_begDate <= PC.PersonCard_begDate)
						and (t1.MedStaffRegion_endDate is null or t1.MedStaffRegion_endDate >= PC.PersonCard_begDate)
					order by t3.PostKind_id
					limit 1
				) LRMSF on true
				left join lateral (
					select
						Person_BDZCode
					from v_PersonInfo
					where Person_id = PS.Person_id
					limit 1
				) PI on true
				left join lateral (
					select
						CardCloseCause_id, PersonCard_begDate
					from v_PersonCard_all t
					where t.Person_id = PC.Person_id
						and t.PersonCard_id != PC.PersonCard_id
						and cast(t.PersonCard_endDate as date) = cast(PC.PersonCard_begDate as date)
					order by t.PersonCard_begDate desc
					limit 1
				) PCL
				left join lateral (
					select
						pua.PersonUAddress_id
					from
						v_PersonUAddress pua
					where
						pua.Person_id = pc.Person_id
						and pua.PersonUAddress_insDate >= PCL.PersonCard_begDate
						and pua.PersonUAddress_insDate <= coalesce(cast(:Date_upload as date), cast(dbo.tzGetDate() as date))
					limit 1
				) ADDRESSCHANGE on true
			where PC.Lpu_id = :Lpu_id
			and PC.LpuAttachType_id = 1
			and PC.PersonCard_IsAttachAuto is null
			and coalesce(PLS.Polis_begDate, cast(dbo.tzGetDate() as date) - interval '1 day') <= cast(dbo.tzGetDate() as date)
			and coalesce(PLS.Polis_endDate, cast(dbo.tzGetDate() as date) + interval '1 day') > cast(dbo.tzGetDate() as date)
			" . (!empty($data['Date_upload']) ? "and cast(coalesce(PC.PersonCard_updDT, PC.PersonCard_insDT) as date) >= cast(:Date_upload as date)" : "") . "
			" . (count($filterList) > 0 ? "and " . implode(' and ', $filterList) : "") . "
			
			union all

			select distinct
				PCA.PersonCardAttach_id as \"Field_id\",
				'2' as \"Field_Type\",
				PCA.Person_id as \"IDCASE\", -- Уникальный идентификатор пациента 
				rtrim(upper(PS.Person_SurName)) as \"FAM\", -- Фамилия
				rtrim(upper(PS.Person_FirName)) as \"IM\", -- Имя
				coalesce(rtrim(Upper(case when Replace(PS.Person_Secname,' ','')='---'  or PS.Person_Secname = '' then 'НЕТ' else PS.Person_Secname end)), 'НЕТ') as \"OT\", -- Отчество
				to_char(PS.Person_BirthDay, 'yyyy-mm-dd') as \"DR\", -- Дата рождения застрахованного
				PS.Person_Snils as \"SNILS\",
				DT.DocumentType_Code as \"DOCTYPE\",
				D.Document_Ser as \"DOCSER\",
				D.Document_Num as \"DOCNUM\",
				to_char(D.Document_begDate, 'yyyy-mm-dd') as \"DOCDT\",
				null as \"TEL\",
				PI.Person_BDZCode as \"RZ\",
				2 as \"SP_PRIK\",
				1 as \"T_PRIK\",
				to_char(PCA.PersonCardAttach_setDate, 'yyyy-mm-dd') as \"DATE_1\",
				case when ADDRESSCHANGE.PersonUAddress_id is not null then 1 else 0 end as \"N_ADR\",
				right('00000000' || coalesce(L.Lpu_f003mcod,'') || coalesce(LB.LpuBuilding_Code, ''), 8) as \"KODPODR\",
				case
					when LR.LpuRegionType_SysNick in ('ter', 'ped', 'vop') then
						case
							when LR.LpuRegionType_SysNick in ('ter', 'vop') then '1'
							when LR.LpuRegionType_SysNick in ('ped') then '2'
							else ''
						end ||
						coalesce(LR.LpuRegion_Name,'')
					else ''
				end as \"LPUUCH\",
				coalesce(MP.Person_Snils, '') as \"SSD\",
				case when MSF.PostKind_id = 1 then 1 else 2 end as \"MEDRAB\"
			from
				PersonCardAttach PCA
				left join v_PersonState PS on PS.Person_id = PCA.Person_id
				left join v_Polis PLS on PLS.Polis_id = PS.Polis_id
				left join v_Lpu L on L.Lpu_id = PCA.Lpu_id
				left join v_Document D on D.Document_id = PS.Document_id
				left join v_DocumentType DT on DT.DocumentType_id = D.DocumentType_id
				left join v_LpuRegion LR on LR.LpuRegion_id = PCA.LpuRegion_id
				left join v_LpuSection LS on LS.LpuSection_id = LR.LpuSection_id
				left join v_LpuBuilding LB on LB.LpuBuilding_id = LS.LpuBuilding_id
				left join v_MedStaffFact MSF on MSF.MedStaffFact_id = PCA.MedStaffFact_id
				left join v_MedPersonal MP on MP.MedPersonal_id = MSF.MedPersonal_id
				left join lateral (
					select
						Person_BDZCode
					from v_PersonInfo
					where Person_id = PS.Person_id
					limit 1
				) PI on true
				left join lateral (
					select
						pua.PersonUAddress_id
					from
						v_PersonUAddress pua
					where
						pua.Person_id = pca.Person_id
						and pua.PersonUAddress_insDate >= PCA.PersonCardAttach_setDate
						and pua.PersonUAddress_insDate <= coalesce(cast(:Date_upload as date), cast(dbo.tzGetDate() as date))
					limit 1
				) ADDRESSCHANGE on true
			where PCA.Lpu_id = :Lpu_id
			and coalesce(PLS.Polis_begDate, cast(dbo.tzGetDate() as date) - interval '1 day') <= cast(dbo.tzGetDate() as date)
			and coalesce(PLS.Polis_endDate, cast(dbo.tzGetDate() as date) + interval '1 day') > cast(dbo.tzGetDate() as date)
			" . (!empty($data['Date_upload']) ? "and cast(coalesce(PCA.PersonCardAttach_updDT, PCA.PersonCardAttach_insDT) as date) >= cast(:Date_upload as date)" : "") . "
			" . (count($filterList) > 0 ? "and " . implode(' and ', $filterList) : "") . "
			and not exists (
				select
					PersonCard_id
				from v_PersonCard_all PC
				where PC.PersonCardAttach_id = PCA.PersonCardAttach_id
				limit 1
			)	
			order by \"IDCASE\", \"DATE_1\", \"T_PRIK\"
		";
		//echo getDebugSQL($query, $queryParams); die();
		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return false;
		}

		$ZAP = $result->result('array');

		if ( !is_array($ZAP) || count($ZAP) == 0) {
			return array(
				'Error_Code' => 1, 'Error_Msg' => 'Список выгрузки пуст!'
			);
		}

		$ZGLV = array(
			array(
				'N_REESTR' => '',
				'SMO_CODE' => ''
			)
		);

		// Получаем код МО и код СМО/ТФОМС
		if ( !empty($data['OrgSMO_id']) ) {
			$smoCodeField = "smo.Orgsmo_f002smocod";
			$outer_apply = "
				left join lateral (
					select
						Orgsmo_f002smocod
					from v_OrgSMO
					where OrgSMO_id = :OrgSMO_id
					limit 1
				) smo on true
			";
		}
		else {
			$smoCodeField = "30";
			$outer_apply = "";
		}

		$query = "
			select
				l.Lpu_f003mcod as \"N_REESTR\",
				{$smoCodeField} as \"SMO_CODE\"
			from v_Lpu l
				{$outer_apply}
			where l.Lpu_id = :Lpu_id
			limit 1
		";
		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return false;
		}

		$ZGLV = $result->result('array');

		if ( !is_array($ZGLV) || count($ZGLV) == 0) {
			return array(
				'Error_Code' => 1, 'Error_Msg' => 'Ошибка при получении кода МО!'
			);
		}

		$data = array();
		$data['Error_Code'] = 0;

		$data['ZAP'] = $ZAP;
		$data['ZGLV'] = $ZGLV;

		return $data;
	}

	/**
	 *	Получение данных для формы списка заявлений о выборе МО
	 */
	function loadPersonCardAttachGrid($data)
	{
		//var_dump($data);die;
		$filter = '';
		$params = array();
		if(!empty($data['Lpu_aid']))
		{
			$filter .= ' and PCA.Lpu_aid = :Lpu_aid';
			$params['Lpu_aid'] = $data['Lpu_aid'];
		}
		if( !empty($data['Person_SurName']) ) {
			$filter .= " and PS.Person_SurName ilike :Person_SurName || '%'";
			$params['Person_SurName'] = rtrim($data['Person_SurName']);
		}
		
		if( !empty($data['Person_FirName']) ) {
			$filter .= " and PS.Person_FirName ilike :Person_FirName || '%'";
			$params['Person_FirName'] = rtrim($data['Person_FirName']);
		}
		
		if( !empty($data['Person_SecName']) ) {
			$filter .= " and PS.Person_SecName ilike :Person_SecName || '%'";
			$params['Person_SecName'] = rtrim($data['Person_SecName']);
		}
		if(!empty($data['PersonCardAttachStatusType_id'])) {
			$filter .= " and PCAST.PersonCardAttachStatusType_id = :PersonCardAttachStatusType_id";
			$params['PersonCardAttachStatusType_id'] = $data['PersonCardAttachStatusType_id'];
		}
		if(isset($data['Person_BirthDay_Range'][0])){
			$filter .= " and PS.Person_BirthDay >= cast(:begBirthday as date)";
			$params['begBirthday'] = $data['Person_BirthDay_Range'][0];
		}
		if(isset($data['Person_BirthDay_Range'][1])){
			$filter .= " and PS.Person_BirthDay <= cast(:endBirthday as date)";
			$params['endBirthday'] = $data['Person_BirthDay_Range'][1];
		}
		if(isset($data['PersonCardAttach_setDate_Range'][0])){
			$filter .= " and PCA.PersonCardAttach_setDate >= cast(:betAttachDate as date)";
			$params['betAttachDate'] = $data['PersonCardAttach_setDate_Range'][0];
		}
		if(isset($data['PersonCardAttach_setDate_Range'][1])){
			$filter .= " and PCA.PersonCardAttach_setDate <= cast(:endAttachDate as date)";
			$params['endAttachDate'] = $data['PersonCardAttach_setDate_Range'][1];
		}
		if( !empty($data['RecMethodType_id']) ) {
			$filter .= " and RMT.RecMethodType_id = :RecMethodType_id ";
			$params['RecMethodType_id'] = rtrim($data['RecMethodType_id']);
		}
		$query = "
			select
				--select
				PersonCardAttach_id as \"PersonCardAttach_id\",
				PersonCardAttach_setDate2 as \"PersonCardAttach_setDate2\",
				PersonCardAttach_setDate as \"PersonCardAttach_setDate\",
				Person_FIO as \"Person_FIO\",
				Lpu_Nick as \"Lpu_Nick\",
				Lpu_id as \"Lpu_id\",
				Person_id as \"Person_id\",
				PersonCardAttachStatusType_id as \"PersonCardAttachStatusType_id\",
				PersonCardAttachStatusType_Code as \"PersonCardAttachStatusType_Code\",
				PersonCardAttachStatusType_Name as \"PersonCardAttachStatusType_Name\",
				LpuRegionType_Name as \"LpuRegionType_Name\",
				LpuRegion_Name as \"LpuRegion_Name\",
				MSF_FIO as \"MSF_FIO\",
				HasPersonCard as \"HasPersonCard\",
				RecMethodType_Name as \"RecMethodType_Name\"
				--end select
			from
				--from
				(
					select
						PCA.PersonCardAttach_id,
						PCA.PersonCardAttach_setDate as PersonCardAttach_setDate2,
						to_char(cast(PCA.PersonCardAttach_setDate as timestamp), 'dd.mm.yyyy') as PersonCardAttach_setDate,
						coalesce(PS.Person_SurName,'') || ' ' || coalesce(PS.Person_FirName,'') || ' ' || coalesce(PS.Person_Secname,'') as Person_FIO,
						L.Lpu_Nick,
						L.Lpu_id,
						PS.Person_id,
						PCAST.PersonCardAttachStatusType_id,
						PCAST.PersonCardAttachStatusType_Code,
						PCAST.PersonCardAttachStatusType_Name,
						LRT.LpuRegionType_Name,
						LR.LpuRegion_Name,
						coalesce(MSF.Person_SurName,'') || ' ' || coalesce(MSF.Person_FirName,'') || ' ' || coalesce(MSF.Person_Secname,'') as MSF_FIO,
						--'false' as HasPersonCard
						case when PC.PersonCard_id is null then 'false' else 'true' end as HasPersonCard,
						RMT.RecMethodType_Name
					from v_PersonCardAttach PCA
					inner join v_PersonState PS on PS.Person_id = PCA.Person_id
					left join v_Lpu L on L.Lpu_id = PCA.Lpu_aid
					left join v_RecMethodType RMT on RMT.RecMethodType_id = PCA.RecMethodType_id
					left join lateral
					(
						select
							PCAS.PersonCardAttachStatus_id,
							PersonCardAttachStatusType_id
						from v_PersonCardAttachStatus PCAS
						where PCAS.PersonCardAttach_id = PCA.PersonCardAttach_id
						order by PersonCardAttachStatusType_id desc
						limit 1
					) PCAS on true
					inner join PersonCardAttachStatusType PCAST on PCAST.PersonCardAttachStatusType_id = PCAS.PersonCardAttachStatusType_id
					inner join v_LpuRegion LR on LR.LpuRegion_id = PCA.LpuRegion_id
					inner join v_LpuRegionType LRT on LRT.LpuRegionType_id = LR.LpuRegionType_id
					left join PersonCard PC on PC.PersonCardAttach_id = PCA.PersonCardAttach_id
					left join v_MedStaffFact MSF on MSF.MedStaffFact_id = PCA.MedStaffFact_id
					where PCA.LpuRegion_id is not null
					{$filter}

					union --Костылина, т.к. старые заявления не имеют ни участка, ни персона, ни врача (проверяется по LpuRegion_id - если его нет, значит это старое заявление)
					select 
						PCA.PersonCardAttach_id,
						PCA.PersonCardAttach_setDate as PersonCardAttach_setDate2,
						to_char(cast(PCA.PersonCardAttach_setDate as timestamp), 'dd.mm.yyyy') as PersonCardAttach_setDate,
						coalesce(PS.Person_SurName,'') || ' ' || coalesce(PS.Person_FirName,'') || ' ' || coalesce(PS.Person_Secname,'') as Person_FIO,
						L.Lpu_Nick,
						L.Lpu_id,
						PS.Person_id,
						PCAST.PersonCardAttachStatusType_id,
						PCAST.PersonCardAttachStatusType_Code,
						PCAST.PersonCardAttachStatusType_Name,
						LRT.LpuRegionType_Name,
						LR.LpuRegion_Name,
						coalesce(MSF.Person_SurName,'') || ' ' || coalesce(MSF.Person_FirName,'') || ' ' || coalesce(MSF.Person_Secname,'') as MSF_FIO,
						'true' as HasPersonCard,
						RMT.RecMethodType_Name
					from v_PersonCardAttach PCA
					inner join lateral
					(
						select
							PCard.PersonCard_id,
							PCard.LpuRegion_id,
							PCard.Lpu_id,
							PCard.MedStaffFact_id,
							PCard.Person_id
						from v_PersonCard_all PCard
						where PCard.PersonCardAttach_id = PCA.PersonCardAttach_id
						limit 1
					) PC on true
					inner join v_Lpu L on L.Lpu_id = PC.Lpu_id
					inner join v_LpuRegion LR on LR.LpuRegion_id = PC.LpuRegion_id
					inner join v_LpuRegionType LRT on LRT.LpuRegionType_id = LR.LpuRegionType_id
					left join v_MedStaffFact MSF on MSF.MedStaffFact_id = PC.MedStaffFact_id
					inner join v_PersonState PS on PS.Person_id = PC.Person_id
					left join v_RecMethodType RMT on RMT.RecMethodType_id = PCA.RecMethodType_id
					left join lateral
					(
						select
							PCAS.PersonCardAttachStatus_id,
							PersonCardAttachStatusType_id
						from v_PersonCardAttachStatus PCAS
						where PCAS.PersonCardAttach_id = PCA.PersonCardAttach_id
						order by PersonCardAttachStatusType_id desc
						limit 1
					) PCAS on true
					inner join PersonCardAttachStatusType PCAST on PCAST.PersonCardAttachStatusType_id = PCAS.PersonCardAttachStatusType_id

					where PCA.LpuRegion_id is null
					{$filter}
				) S
				--end from
			where
				--where
				(1=1)
				--end where
			order by
				-- order by
				S.PersonCardAttach_setDate2 desc
				-- end order by

		";
		//echo getDebugSQL($query, $params);die;
		return $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
	}

	/**
	 *	Проверка наличия активного прикрепления
	 */
	function checkPersonCardActive($data)
	{
		$params = array(
			'Person_id' => $data['Person_id'],
			'Lpu_id'	=> $data['Lpu_id']
		);
		$query = "
			select
				PC.PersonCard_id as \"PersonCard_id\",
				coalesce(PS.Person_SurName,'')
					|| ' ' || coalesce(PS.Person_FirName,'')
					|| ' ' || coalesce(PS.Person_Secname,'')
				as \"Person_FIO\",
				LR.LpuRegion_Name as \"LpuRegion_Name\",
				LRT.LpuRegionType_Name as \"LpuRegionType_Name\"
			from v_PersonCard PC
				left join v_LpuRegion LR on LR.LpuRegion_id = PC.LpuRegion_id
				left join v_LpuRegionType LRT on LRT.LpuRegionType_id = LR.LpuRegionType_id
				left join v_PersonState PS on PS.Person_id = PC.Person_id
			where
				PC.Lpu_id = :Lpu_id
				and PC.Person_id = :Person_id
				and PC.LpuAttachType_id=1
		";
		//echo getDebugSQL($query,$params);die;
		$result = $this->db->query($query,$params);
		if(is_object($result))
			return $result->result('array');
		else
			return false;
	}

	/**
	 *	Получение данных по заявлению о выборе МО
	 */
	function loadPersonCardAttachForm($data)
	{
		$params = array('PersonCardAttach_id' => $data['PersonCardAttach_id']);

		$query = "
			select
				PCA.PersonCardAttach_id as \"PersonCardAttach_id\",
				PCA.Lpu_aid as \"Lpu_aid\",
				to_char(PCA.PersonCardAttach_setDate, 'dd.mm.yyyy') as \"PersonCardAttach_setDate\",
				coalesce(PCA.Person_id, PS.Person_id) as \"Person_id\",
				PCAS.PersonCardAttachStatus_id as \"PersonCardAttachStatus_id\",
				coalesce(LR.LpuRegion_id, LR2.LpuRegion_id) as \"LpuRegion_id\",
				coalesce(LR.LpuRegionType_id, LR2.LpuRegionType_id) as \"LpuRegionType_id\",
				coalesce(PCA.MedStaffFact_id, PC.MedStaffFact_id) as \"MedStaffFact_id\",
				PAC.PersonAmbulatCard_id as \"PersonAmbulatCard_id\",
				rtrim(rtrim(coalesce(PAC.PersonAmbulatCard_Num,PC.PersonCard_Code))) as \"PersonCard_Code\"
			from
				v_PersonCardAttach PCA
				left join lateral (
					select
						PCAS.PersonCardAttachStatus_id,
						PersonCardAttachStatusType_id
					from v_PersonCardAttachStatus PCAS
					where PCAS.PersonCardAttach_id = PCA.PersonCardAttach_id
					order by PersonCardAttachStatusType_id desc
					limit 1
				) PCAS on true
				left join v_LpuRegion LR on LR.LpuRegion_id = PCA.LpuRegion_id
				left join v_PersonCard_all PC on PC.PersonCardAttach_id = PCA.PersonCardAttach_id
				left join v_PersonState PS on PS.Person_id = PC.Person_id
				left join v_LpuRegion LR2 on LR2.LpuRegion_id = PC.LpuRegion_id
				left join v_PersonAmbulatCardLink PACL on PACL.PersonCard_id = PC.PersonCard_id
				left join v_PersonAmbulatCard PAC on PAC.PersonAmbulatCard_id = coalesce(PCA.PersonAmbulatCard_id,PACL.PersonAmbulatCard_id)
			where
				PCA.PersonCardAttach_id = :PersonCardAttach_id
			limit 1
		";


		
		$result = $this->queryResult($query, $params);

		if (isset($result[0]) && !empty($result[0]['PersonCardAttach_id'])) {
			$files = $this->getFilesOnPersonCardAttach(array(
				'PersonCardAttach_id' => $result[0]['PersonCardAttach_id'],
				'PersonCard_id' => null,
			));
			if (!$files) {
				$this->createError('Ошибка при получении списка прикрепленных файлов');
			}
			$result[0]['files'] = $files;
		}


		return $result;
	}

	/**
	 *	Сохранение заявления о выборе МО
	 */
	function savePersonCardAttachForm($data) {
		$params = array(
			'PersonCardAttach_id'			=> !empty($data['PersonCardAttach_id'])?$data['PersonCardAttach_id']:null,
			'Lpu_id' 						=> $data['Lpu_aid'],
			'Lpu_aid' 						=> $data['Lpu_aid'],
			'LpuRegionType_id' 				=> $data['LpuRegionType_id'],
			'LpuRegion_id' 					=> $data['LpuRegion_id'],
			'PersonCardAttach_setDate'		=> $data['PersonCardAttach_setDate'],
			'MedStaffFact_id' 				=> $data['MedStaffFact_id'],
			'Person_id' 					=> $data['Person_id'],
			'PersonAmbulatCard_id' 			=> $data['PersonAmbulatCard_id'],
			'PersonCardAttach_IsSMS' 		=> 1,
			'PersonCardAttach_SMS' 			=> null,
			'PersonCardAttach_IsEmail' 		=> 1,
			'PersonCardAttach_Email' 		=> null,
			'PersonCardAttach_IsHimself' 	=> null,
			'RecMethodType_id' 				=> !empty($data['PersonCardAttach_id']) ? 16 : 
			//При добавлении заявления устанавливать источник записи «Промед: регистратор»
												(!empty($data['RecMethodType_id']) ? $data['RecMethodType_id'] : null),
			'pmUser_id' 					=> $data['pmUser_id']
		);
		if (empty($data['PersonCardAttach_id'])) {
			$procedure = 'p_PersonCardAttach_ins';
		} else {
			$procedure = 'p_PersonCardAttach_upd';
		}

		$query = "
			select
				PersonCardAttach_id as \"PersonCardAttach_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				PersonCardAttach_id := :PersonCardAttach_id,
				PersonCardAttach_setDate := :PersonCardAttach_setDate,
				Lpu_id := :Lpu_id,
				Lpu_aid := :Lpu_aid,
				Person_id := :Person_id,
				PersonAmbulatCard_id := :PersonAmbulatCard_id,
				LpuRegion_id := :LpuRegion_id,
				MedStaffFact_id := :MedStaffFact_id,
				Address_id := null,
				Polis_id := null,
				PersonCardAttach_IsSMS := :PersonCardAttach_IsSMS,
				PersonCardAttach_SMS := :PersonCardAttach_SMS,
				PersonCardAttach_IsEmail := :PersonCardAttach_IsEmail,
				PersonCardAttach_Email := :PersonCardAttach_Email,
				PersonCardAttach_IsHimself := :PersonCardAttach_IsHimself,
				RecMethodType_id := :RecMethodType_id,
				pmUser_id := :pmUser_id
			)
		";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			$this->rollbackTransaction();
			return $this->createError('','Ошибка при сохранении заявления');
		}
		if (!$this->isSuccessful($response)) {
			$this->rollbackTransaction();
			return $response;
		}

		//При добавлении заявления сохраняется статус "Принято"
		if (empty($data['PersonCardAttach_id'])) {
			$resp = $this->savePersonCardAttachStatus(array(
				'PersonCardAttachStatus_id' => null,
				'PersonCardAttach_id' => $response[0]['PersonCardAttach_id'],
				'PersonCardAttachStatusType_id' => 7,
				'PersonCardAttachStatus_setDate' => $data['PersonCardAttach_setDate'],
				'pmUser_id' => $data['pmUser_id']
			));
			if (!$this->isSuccessful($resp)) {
				$this->rollbackTransaction();
				return $resp;
			}
		}

		$this->commitTransaction();

		return $response;
	}

	/**
	 *	Установка статуса заявления
	 */
	function changePersonCardAttachStatus($data){
		$params = array(
			'PersonCardAttach_id' => $data['PersonCardAttach_id'],
			'PersonCardAttachStatusType_id' => $data['PersonCardAttachStatusType_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		$res_Str = array('success'=>true,'string'=>'');
		$queryCheck = "
			select
				PC.PersonCard_id as \"PersonCard_id\",
				to_char(PCA.PersonCardAttach_setDate, 'dd.mm.yyyy') as \"PersonCardAttach_setDate\",
				coalesce(LR.LpuRegion_Name,'') as \"LpuRegion_Name\",
				coalesce(LRT.LpuRegionType_Name,'') as \"LpuRegionType_Name\",
				coalesce(L.Lpu_Nick,'') as \"Lpu_Nick\",
				coalesce(PS.Person_SurName,'') || ' ' || coalesce(PS.Person_FirName,'') || ' ' || coalesce(PS.Person_Secname,'') as \"Person_FIO\"
			from v_PersonCard_all PC
				left join v_PersonState PS on PS.Person_id = PC.Person_id
				left join v_PersonCardAttach PCA on PCA.PersonCardAttach_id = PC.PersonCardAttach_id
				left join v_LpuRegion LR on LR.LpuRegion_id = PCA.LpuRegion_id
				left join v_LpuRegionType LRT on LRT.LpuRegionType_id = LR.LpuRegionType_id
				left join v_Lpu L on L.Lpu_id = PCA.Lpu_aid
			where PC.PersonCardAttach_id = :PersonCardAttach_id
			limit 1
		";
		$resultCheck = $this->db->query($queryCheck, $params);
		if(!is_object($resultCheck))
		{
			$query = "
			update dbo.PersonCardAttachStatus
			set
				PersonCardAttachStatusType_id = :PersonCardAttachStatusType_id,
				pmUser_updID = :pmUser_id,
				PersonCardAttachStatus_updDT = tzGetDate()
			where PersonCardAttach_id = :PersonCardAttach_id
			";
			$result = $this->db->query($query, $params);
		}
		else
		{
			$resultCheck = $resultCheck->result('array');
			if(count($resultCheck) == 0)
			{
				$query = "
					update dbo.PersonCardAttachStatus
					set
						PersonCardAttachStatusType_id = :PersonCardAttachStatusType_id,
						pmUser_updID = :pmUser_id,
						PersonCardAttachStatus_updDT = tzGetDate()
					where PersonCardAttach_id = :PersonCardAttach_id
				";
				$result = $this->db->query($query, $params);
			}
			else
			{
				$res_Str['string'] = 'Заявление от '.$resultCheck[0]['PersonCardAttach_setDate'].' ('. $resultCheck[0]['Person_FIO'].') '.'связано с прикреплением. Смена статуса невозможна.';
			}
		}
		return $res_Str;
		//return true;
	}

	/**
	 *	Установка статуса заявления по имеющемуся PersonCard_id
	 */
	function changePersonCardAttachStatusByPersonCard($data)
	{
		$params_get_PersonCardAttach = array(
			'PersonCard_id' => $data['PersonCard_id']
		);
		$query_get_PersonCardAttach = "
			select
				PC.PersonCardAttach_id as \"PersonCardAttach_id\"
			from v_PersonCard_all PC
			where PC.PersonCard_id = :PersonCard_id
			limit 1
		";
		$result_get_PersonCardAttach = $this->db->query($query_get_PersonCardAttach,$params_get_PersonCardAttach);
		if(is_object($result_get_PersonCardAttach))
		{
			$result_get_PersonCardAttach = $result_get_PersonCardAttach->result('array');
			if(is_array($result_get_PersonCardAttach) && count($result_get_PersonCardAttach) > 0)
			{
				$params = array(
					'PersonCardAttach_id' => $result_get_PersonCardAttach[0]['PersonCardAttach_id'],
					'PersonCardAttachStatusType_id' => $data['PersonCardAttachStatusType_id'],
					'pmUser_id' => $data['pmUser_id']
				);
				$query = "
					update dbo.PersonCardAttachStatus
						set
						PersonCardAttachStatusType_id = :PersonCardAttachStatusType_id,
						pmUser_updID = :pmUser_id,
						PersonCardAttachStatus_updDT = tzGetDate()
					where PersonCardAttach_id = :PersonCardAttach_id
				";
				$result = $this->db->query($query, $params);
			}
		}
		return true;
	}

	/**
	 *	Проверка связи заявления с прикреплением
	 */
	function checkPersonCardByAttach($data) {
		$params = array(
			'PersonCardAttach_id' => $data['PersonCardAttach_id']
		);
		$query = "
			select
				PC.PersonCard_id as \"PersonCard_id\",
				to_char(PCA.PersonCardAttach_setDate, 'dd.mm.yyyy') as \"PersonCardAttach_setDate\",
				coalesce(LR.LpuRegion_Name,'') as \"LpuRegion_Name\",
				coalesce(LRT.LpuRegionType_Name,'') as \"LpuRegionType_Name\",
				coalesce(L.Lpu_Nick,'') as \"Lpu_Nick\",
				coalesce(PS.Person_SurName,'')
					|| ' ' || coalesce(PS.Person_FirName,'')
					|| ' ' || coalesce(PS.Person_Secname,'')
				as \"Person_FIO\"
			from v_PersonCard_all PC
				left join v_PersonState PS on PS.Person_id = PC.Person_id
				left join v_PersonCardAttach PCA on PCA.PersonCardAttach_id = PC.PersonCardAttach_id
				left join v_LpuRegion LR on LR.LpuRegion_id = PCA.LpuRegion_id
				left join v_LpuRegionType LRT on LRT.LpuRegionType_id = LR.LpuRegionType_id
				left join v_Lpu L on L.Lpu_id = PCA.Lpu_aid
			where PC.PersonCardAttach_id = :PersonCardAttach_id
			limit 1
		";
		$result = $this->db->query($query,$params);
		if(is_object($result)){
			$result = $result->result('array');
			return $result;
		}
		return false;
	}

	/**
	 *	Проверка статуса заявления
	 */
	function checkAttachStatus($data){
		$params = array(
			'PersonCardAttach_id' => $data['PersonCardAttach_id']
		);
		$query = "
			select 
				PCAS.PersonCardAttachStatusType_Code as \"PersonCardAttachStatusType_Code\",
				PCAS.PersonCardAttachStatusType_Name as \"PersonCardAttachStatusType_Name\",
				to_char(PCA.PersonCardAttach_setDate, 'dd.mm.yyyy') as \"PersonCardAttach_setDate\",
				coalesce(PS.Person_SurName,'') || ' ' || coalesce(PS.Person_FirName,'') || ' ' || coalesce(PS.Person_Secname,'') as \"Person_FIO\"
			from
				v_PersonCardAttach PCA
				left join v_PersonState PS on PS.Person_id = PCA.Person_id
				left join lateral (
					select
						PCAST.PersonCardAttachStatusType_Code,
						PCAST.PersonCardAttachStatusType_Name
					from v_PersonCardAttachStatus PCAS
					left join PersonCardAttachStatusType PCAST on PCAST.PersonCardAttachStatusType_id = PCAS.PersonCardAttachStatusType_id
					where PCAS.PersonCardAttach_id = PCA.PersonCardAttach_id
					order by PCAS.PersonCardAttachStatusType_id desc
					limit 1
				) PCAS on true
			where PCA.PersonCardAttach_id = :PersonCardAttach_id
		";
		$result = $this->db->query($query,$params);
		if(is_object($result)){
			$result = $result->result('array');
			return $result;
		}
		return false;
	}

	/**
	 *	Добавление прикрепления на основе заявления
	 */
	function addPersonCardByAttach($data){
		$queryAttach = "
			select
				PCA.Lpu_aid as \"Lpu_id\",
				PCA.Person_id as \"Person_id\",
				PCA.LpuRegion_id as \"LpuRegion_id\",
				PCA.MedStaffFact_id as \"MedStaffFact_id\",
				coalesce(PCA.PersonAmbulatCard_id,0) as \"PersonAmbulatCard_id\",
				coalesce(PAC.PersonAmbulatCard_Num,'') as \"PersonAmbulatCard_Code\"
			from v_PersonCardAttach PCA
				left join v_PersonAmbulatCard PAC on PAC.PersonAmbulatCard_id = PCA.PersonAmbulatCard_id
			where PCA.PersonCardAttach_id = :PersonCardAttach_id
		";
		$resultAttach = $this->db->query($queryAttach,array('PersonCardAttach_id' => $data['PersonCardAttach_id']));
		if(is_object($resultAttach)){
			$resultAttach = $resultAttach->result('array');
			$params = array(
				'PersonCard_id' => null,
				'CardCloseCause_id' => null,
				'Lpu_id' => $resultAttach[0]['Lpu_id'],
				'Person_id' => $resultAttach[0]['Person_id'],
				'LpuRegion_id' => $resultAttach[0]['LpuRegion_id'],
				'MedStaffFact_id' => $resultAttach[0]['MedStaffFact_id'],
				'PersonAmbulatCard_id' => $resultAttach[0]['PersonAmbulatCard_id'],
				'PersonAmbulatCard_Code' => $resultAttach[0]['PersonAmbulatCard_Code'],
				'pmUser_id' => $data['pmUser_id']
			);
			if($resultAttach[0]['PersonAmbulatCard_id'] == 0){ //Если не указана амбулаторная карта, то берем последнюю у пациента, либо создаем новую
				$query_SearchAmbulatCard = "
					select
						PersonAmbulatCard_Num as \"PersonAmbulatCard_Num\"
					from v_PersonAmbulatCard
					where Person_id = :Person_id
					order by PersonAmbulatCard_id desc
					limit 1
				";
				$resultAmbulatCard = $this->db->query($query_SearchAmbulatCard,$resultAttach[0]);
				$resultAmbulatCard = $resultAmbulatCard->result('array');
				if(isset($resultAmbulatCard[0]['PersonAmbulatCard_Num']))
					$params['PersonAmbulatCard_Code'] = $resultAmbulatCard[0]['PersonAmbulatCard_Num'];
				else { //У пациента нет АК, поэтому нужно создать
					$params_PersonAmbulatCard = array();
					$data['Lpu_id'] = $resultAttach[0]['Lpu_id'];
                    $params_PersonAmbulatCard['PersonAmbulatCard_id'] = null;
                    $params_PersonAmbulatCard['Server_id'] = $data['Server_id'];
                    $params_PersonAmbulatCard['Person_id'] = $resultAttach[0]['Person_id'];
                    $PersonCardCode_res = $this->getPersonCardCode($data);
                    $params_PersonAmbulatCard['PersonAmbulatCard_Num'] = $PersonCardCode_res[0]['PersonCard_Code'];
                    $personCard_Code = $params_PersonAmbulatCard['PersonAmbulatCard_Num'];
                    $params_PersonAmbulatCard['Lpu_id'] = $data['Lpu_id'];
                    $params_PersonAmbulatCard['PersonAmbulatCard_CloseCause'] = null;
                    $params_PersonAmbulatCard['PersonAmbulatCard_endDate'] = null;
                    $params_PersonAmbulatCard['pmUser_id'] = $data['pmUser_id'];
                    $query_PersonAmbulatCard = "
                    	select
                    		PersonAmbulatCard_id as \"PersonAmbulatCard_id\",
                    		Error_Code as \"Error_Code\",
                    		Error_Message as \"Error_Msg\"
                        from p_PersonAmbulatCard_ins(
                        	Server_id := :Server_id,
                        	PersonAmbulatCard_id := :PersonAmbulatCard_id,
                        	Person_id := :Person_id,
                        	PersonAmbulatCard_Num := :PersonAmbulatCard_Num,
                        	Lpu_id := :Lpu_id,
                        	PersonAmbulatCard_CloseCause := :PersonAmbulatCard_CloseCause,
                        	PersonAmbulatCard_endDate := :PersonAmbulatCard_endDate,
                        	PersonAmbulatCard_begDate := dbo.tzGetDate(),
                        	pmUser_id := :pmUser_id
                        )
                    ";
                    $result_PersonAmbulatCard = $this->db->query($query_PersonAmbulatCard,$params_PersonAmbulatCard);
                    $params['PersonAmbulatCard_Code'] = $personCard_Code;
                    if(is_object($result_PersonAmbulatCard)){
                        $result_PersonAmbulatCard = $result_PersonAmbulatCard->result('array');
                        $change_lpu = 1;
                        //Теперь добавляем PersonAmbulatCardLocat - движение амбулаторной карты
                        $PersonAmbulatCard_id = $result_PersonAmbulatCard[0]['PersonAmbulatCard_id'];
                        $params_PersonAmbulatCardLocat = array();
                        $params_PersonAmbulatCardLocat['PersonAmbulatCardLocat_id'] = null;
                        $params_PersonAmbulatCardLocat['Server_id'] = $data['Server_id'];
                        $params_PersonAmbulatCardLocat['PersonAmbulatCard_id'] = $PersonAmbulatCard_id;
                        $params_PersonAmbulatCardLocat['AmbulatCardLocatType_id'] = 1;
                        $params_PersonAmbulatCardLocat['MedStaffFact_id'] = null;
                        $params_PersonAmbulatCardLocat['PersonAmbulatCardLocat_begDate'] = date('Y-m-d H:i');
                        $params_PersonAmbulatCardLocat['PersonAmbulatCardLocat_Desc'] = null;
                        $params_PersonAmbulatCardLocat['PersonAmbulatCardLocat_OtherLocat'] = null;
                        $params_PersonAmbulatCardLocat['pmUser_id'] = $data['pmUser_id'];
                        $query_PersonAmbulatCardLocat = "
							select
								PersonAmbulatCardLocat_id as \"PersonAmbulatCardLocat_id\",
								Error_Code as \"Error_Code\",
								Error_Message as \"Error_Msg\"
                            from p_PersonAmbulatCardLocat_ins(
                            	Server_id := :Server_id,
                            	PersonAmbulatCardLocat_id := :PersonAmbulatCardLocat_id,
                            	PersonAmbulatCard_id := :PersonAmbulatCard_id,
                            	AmbulatCardLocatType_id := :AmbulatCardLocatType_id,
                            	MedStaffFact_id := :MedStaffFact_id,
                            	PersonAmbulatCardLocat_begDate := :PersonAmbulatCardLocat_begDate,
                            	PersonAmbulatCardLocat_Desc := :PersonAmbulatCardLocat_Desc,
                            	PersonAmbulatCardLocat_OtherLocat := :PersonAmbulatCardLocat_OtherLocat,
                            	pmUser_id := :pmUser_id
                            )
                        ";
                        $result_PersonAmbulatCardLocat = $this->db->query($query_PersonAmbulatCardLocat,$params_PersonAmbulatCardLocat);
                    }
				}
			}
			
			$procedure = 'p_PersonCard_ins';
			//Проверим, а есть ли у этого пациента активное прикрепление
			$queryPersonCard = "
				select
					PersonCard_id as \"PersonCard_id\",
					Lpu_id as \"Lpu_id\"
				from v_PersonCard
				where Person_id = :Person_id
					and LpuAttachType_id = 1
				limit 1
			";
			$resultPersonCard = $this->db->query($queryPersonCard,$params);
			$resultPersonCard = $resultPersonCard->result('array');
			if(count($resultPersonCard) > 0){
				$params['PersonCard_id'] = $resultPersonCard[0]['PersonCard_id'];
				$params['CardCloseCause_id'] = 1;
				$procedure = 'p_PersonCard_upd';
				if($resultPersonCard[0]['Lpu_id'] == $resultAttach[0]['Lpu_id'])
					$params['CardCloseCause_id'] = 4;


				$upd_params = array();
				$beg_date = date('Y-m-d H:i:00.000');
				$upd_params['BegDate'] = $beg_date;

				//https://redmine.swan.perm.ru/issues/108218 - получим дату заявления
				$query_get_AttachDate = "
					select
						to_char(PersonCardAttach_setDate, 'yyyy-mm-dd') as \"setDate\"
					from v_PersonCardAttach
					where PersonCardAttach_id = :PersonCardAttach_id
				";
				$result_get_AttachDate = $this->db->query($query_get_AttachDate, array('PersonCardAttach_id' => $data['PersonCardAttach_id']));
				if(is_object($result_get_AttachDate)){
					$result_get_AttachDate = $result_get_AttachDate->result('array');
					if(is_array($result_get_AttachDate) && count($result_get_AttachDate) > 0)
						$upd_params['BegDate'] = $result_get_AttachDate[0]['setDate'];
				}
                //$beg_date = date('Y-m-d H:i:00.000');
                $upd_params['PersonCard_id'] = $params['PersonCard_id'];
                $upd_params['Lpu_id'] = $params["Lpu_id"];
                $upd_params['Server_id'] = $data["Server_id"];
                $upd_params['Person_id'] = $params["Person_id"];
                $upd_params['PersonCard_IsAttachCondit'] = null;
                //$upd_params['BegDate'] = $beg_date;
                $upd_params['EndDate'] = null;
                $upd_params['CardCloseCause_id'] = $params['CardCloseCause_id'];
                $upd_params['pmUser_id'] = $params['pmUser_id'];
                $upd_params['PersonCard_Code'] = $params['PersonAmbulatCard_Code'];
                $upd_params['LpuRegion_id'] = $params["LpuRegion_id"];
                $upd_params['LpuRegion_Fapid'] = null;
                $upd_params['LpuAttachType_id'] = 1;
                $upd_params['MedStaffFact_id'] = $params['MedStaffFact_id'];
                $upd_params['PersonCardAttach_id'] = $data["PersonCardAttach_id"];
                $sql = "
						select
							PersonCard_id as \"PersonCard_id\",
							Error_Code as \"Error_Code\",
							Error_Message as \"Error_Msg\"
						from p_PersonCard_upd(
							PersonCard_id := :PersonCard_id,
							Lpu_id := :Lpu_id,
							Server_id := :Server_id,
							Person_id := :Person_id,
							PersonCard_begDate := :BegDate,
							PersonCard_endDate := :EndDate,
							PersonCard_Code := :PersonCard_Code,
							PersonCard_IsAttachCondit := :PersonCard_IsAttachCondit,
							LpuRegion_id := :LpuRegion_id,
							LpuRegion_fapid := :LpuRegion_Fapid,
							LpuAttachType_id := :LpuAttachType_id,
							CardCloseCause_id := :CardCloseCause_id,
							PersonCardAttach_id := :PersonCardAttach_id,
							MedStaffFact_id := :MedStaffFact_id,
							pmUser_id := :pmUser_id
						)
					";
                $result = $this->db->query($sql, $upd_params);
			}
			else
			{
				$beg_date = date('Y-m-d H:i:00.000');
				$ins_params = array();
				$ins_params['PersonCard_begDate'] = $beg_date;

				//https://redmine.swan.perm.ru/issues/108218 - получим дату заявления
				$query_get_AttachDate = "
					select
						to_char(PersonCardAttach_setDate, 'yyyy-mm-dd') as \"setDate\"
					from v_PersonCardAttach
					where PersonCardAttach_id = :PersonCardAttach_id
				";
				$result_get_AttachDate = $this->db->query($query_get_AttachDate, array('PersonCardAttach_id' => $data['PersonCardAttach_id']));
				if(is_object($result_get_AttachDate)){
					$result_get_AttachDate = $result_get_AttachDate->result('array');
					if(is_array($result_get_AttachDate) && count($result_get_AttachDate) > 0)
						$ins_params['PersonCard_begDate'] = $result_get_AttachDate[0]['setDate'];
				}

                $ins_params['Lpu_id'] = $resultAttach[0]['Lpu_id'];
                $ins_params['Server_id'] = $data["Server_id"];
                $ins_params['Person_id'] = $params["Person_id"];
                $ins_params['PersonCard_IsAttachCondit'] = null;
                //$ins_params['PersonCard_begDate'] = $beg_date;
                $ins_params['PersonCard_Code'] = $params['PersonAmbulatCard_Code'];
                $ins_params['EndDate'] = null;
                $ins_params['pmUser_id'] = $data['pmUser_id'];
                $ins_params['LpuRegion_id'] = $params["LpuRegion_id"];
                $ins_params['LpuRegion_Fapid'] = null;
                $ins_params['MedStaffFact_id'] = $params['MedStaffFact_id'];
                $ins_params['PersonCardAttach_id'] = $data["PersonCardAttach_id"];
                $sql = "
                    select
                    	PersonCard_id as \"PersonCard_id\",
                    	Error_Code as \"Error_Code\",
                    	Error_Message as \"Error_Msg\"
                    from p_PersonCard_ins(
                        Lpu_id := :Lpu_id,
                        Server_id := :Server_id,
                        Person_id := :Person_id,
                        PersonCard_begDate := :PersonCard_begDate,
                        PersonCard_Code := :PersonCard_Code,
                        PersonCard_IsAttachCondit := :PersonCard_IsAttachCondit,
                        PersonCard_IsAttachAuto := 2,
                        LpuRegion_id := :LpuRegion_id,
                        LpuRegion_fapid := :LpuRegion_Fapid,
                        LpuAttachType_id := 1,
                        CardCloseCause_id := null,
                        PersonCardAttach_id := :PersonCardAttach_id,
                        MedStaffFact_id := :MedStaffFact_id,
                        pmUser_id := :pmUser_id
                    )
                ";
                //echo getDebugSQL($sql, $ins_params);die;
                $result = $this->db->query($sql, $ins_params);
			}
			return $result->result('array');
		}
		else
			return false;
	}

	/**
	 *	Получение номера прикрепления
	 */
	function getPersonCardCode($data)
	{
		$sql = "
			select
				ObjectID as \"PersonCard_Code\"
			from (
				ObjectName := 'PersonCard',
				Lpu_id := ?
			)
		";
		$result = $this->db->query($sql, array($data['Lpu_id']));
		if (is_object($result))
		{
			$personcard_result = $result->result('array');
			$personcard_result[0]['success'] = true;
			return $personcard_result;
			//return $result->result('array');
		}
		else
		{
			return false;
		}
	}

	/**
	* Поиск человека по ФИО, ДР и СНИЛС
	*/
	function searchPerson($data){
		$query = "
			select
				Person_id as \"Person_id\"
			from v_PersonState
			where REPLACE(REPLACE(Person_Snils,'-',''),' ','') = REPLACE(REPLACE(:SNILS,'-',''),' ','')
				and Person_SurName = :FAM
				and Person_FirName = :IM
				and Person_SecName = :OT
				and Person_BirthDay = :DR
			limit 1
		";
		$result = $this->db->query($query,$data);
		if(is_object($result)){
			$result = $result->result('array');
			if(count($result)>0)
				return $result[0]['Person_id'];
			else
				return 0;
		}
		else
			return 0;
	}

	/**
	* Поиск врача по СНИЛС
	*/
	function searchMedPersonal($SSD,$LPUC){
		$query = "
			select
				MP.Person_Fio as \"Person_Fio\"
			from v_MedPersonal MP
				inner join v_Lpu L on L.Lpu_id = MP.Lpu_id
			where REPLACE(REPLACE(MP.Person_Snils,'-',''),' ','') = REPLACE(REPLACE('{$SSD}','-',''),' ','')
				and right('000000' || coalesce(L.Lpu_f003mcod, ''), 6) = '{$LPUC}'
			limit 1
		";
		//echo getDebugSQL($query,array());die;
		$result = $this->db->query($query,array());
		if(is_object($result)){
			$result = $result->result('array');
			if(count($result)>0)
				return $result[0]['Person_Fio'];
			else
				return 'не указан';
		}
		else
			return 'не указан';
	}

	/**
	*	Поиск открепления/прикрепления/заявления
	*/
	function searchPersonCard($data)
	{
		$result_ret = array(
			'PersonCard_id' => '0',
			'PersonCardAttach_id' => '0',
			'ItemExists' => '0'
		);
		$params = array(
			'Person_id' => $data['PER_ID'],
			'Lpu_Code' 	=> $data['LPU_CODE'],
			'LpuRegion_Name' => $data['LR_N'],
			'MedPersonal_Snils' => $data['SSD'],
			'PersonCard_Date' => $data['DATE_1']
		);

		$and_date = '';
		if($data['T_PRIK'] == '2') //Открепление
		{
			$and_date = " and to_char(PC.PersonCard_endDate, 'yyyy-mm-dd') = :PersonCard_Date";
		}
		else //Прикрепление
		{
			$and_date = " and to_char(PC.PersonCard_begDate, 'yyyy-mm-dd') = :PersonCard_Date";
		}
		$query = "
			select
				PC.PersonCard_id as \"PersonCard_id\"
			from v_PersonCard_all PC
				inner join v_PersonState PS on PS.Person_id = PC.Person_id
				inner join v_Lpu L on L.Lpu_id = PC.Lpu_id
				inner join v_LpuRegion LR on LR.LpuRegion_id = PC.LpuRegion_id
				left join v_MedStaffFact MSF on MSF.MedStaffFact_id = PC.MedStaffFact_id
				left join v_MedPersonal MP on MP.MedPersonal_id = MSF.MedPersonal_id
			where (1=1)
				and PC.Person_id = :Person_id
				and right('000000' || coalesce(L.Lpu_f003mcod, ''), 6) = :Lpu_Code
				and LR.LpuRegion_Name = :LpuRegion_Name
				and	replace(ltrim(replace(LR.LpuRegion_Name, '0', ' ')), ' ', 0) = replace(ltrim(replace(:LpuRegion_Name, '0', ' ')), ' ', 0)
				and (PC.MedStaffFact_id is null or REPLACE(REPLACE(MP.Person_Snils,'-',''),' ','') = REPLACE(REPLACE(:MedPersonal_Snils,'-',''),' ',''))
				{$and_date}
			limit 1
		";
		/*else if($data['T_PRIK'] == '1' && $data['SP_PRIK'] == '2') //Заявительное прикрепление
		{

		}*/
		//echo getDebugSQL($query,$params);die;
		/*if($data['PER_ID'] == '60690')
		{
			echo getDebugSQL($query,$params);die;
		}*/
		$result = $this->db->query($query,$params);
		if(is_object($result)){
			$result = $result->result('array');
			if(count($result) > 0) //Нашли прикрепление/открепление. Возвращаем его.
			{
				$result_ret['PersonCard_id'] = $result[0]['PersonCard_id'];
				$result_ret['ItemExists'] = '1';
				return $result_ret;
			}
			else
			{
				if($data['T_PRIK'] == '2') //Открепление. Не нашли.
					return $result_ret;
				if($data['T_PRIK'] == '1' && $data['SP_PRIK'] == '1') //Территориальное прикрепление. Не нашли.
					return $result_ret;
				if($data['T_PRIK'] == '1' && $data['SP_PRIK'] == '2') //Заявительное прикрепление. Не нашли. Тогда поищем заявление.
				{
					$query_a = "
						select
							PCA.PersonCardAttach_id as \"PersonCardAttach_id\"
						from v_PersonCardAttach PCA
							left join v_PersonState PS on PS.Person_id = PCA.Person_id
							left join v_Lpu L on L.Lpu_id = PCA.Lpu_aid
							left join v_LpuRegion LR on LR.LpuRegion_id = PCA.LpuRegion_id
							left join v_MedStaffFact MSF on MSF.MedStaffFact_id = PCA.MedStaffFact_id
							left join v_MedPersonal MP on MP.MedPersonal_id = MSF.MedPersonal_id
						where (1=1)
							and PCA.Person_id = :Person_id
							and right('000000' || coalesce(L.Lpu_f003mcod, ''), 6) = :Lpu_Code
							and LR.LpuRegion_Name = :LpuRegion_Name
							and	replace(ltrim(replace(LR.LpuRegion_Name, '0', ' ')), ' ', 0) = replace(ltrim(replace(:LpuRegion_Name, '0', ' ')), ' ', 0)
							and (PCA.MedStaffFact_id is null or REPLACE(REPLACE(MP.Person_Snils,'-',''),' ','') = REPLACE(REPLACE(:MedPersonal_Snils,'-',''),' ',''))
							and to_char(PCA.PersonCardAttach_setDate, 'yyyy-mm-dd') = :PersonCard_Date
						limit 1
					";
					$result_a = $this->db->query($query_a,$params);
					//var_dump($result_a);die;
					if(is_object($result_a))
					{
						$result_a = $result_a->result('array');
						//var_dump($result_a);die;
						if(count($result_a) > 0)
						{
							$result_ret['PersonCardAttach_id'] = $result_a[0]['PersonCardAttach_id'];
							$result_ret['ItemExists'] = '1';
							return $result_ret;
						}
						else
						{
							return $result_ret;
						}
					}
					else
						return $result_ret;
				}
			}
		}
		else
			return $result_ret;
	}
}

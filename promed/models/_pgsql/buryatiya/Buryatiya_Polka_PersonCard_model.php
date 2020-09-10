<?php
/**
* Buryatiya_Polka_PersonCard_model - модель, для работы с таблицей PersonCard (Астрахань)
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* copy Astra_Polka_PersonCard_model.php
*/

require_once(APPPATH.'models/_pgsql/Polka_PersonCard_model.php');

class Buryatiya_Polka_PersonCard_model extends Polka_PersonCard_model {
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
				left join v_PersonState PS with on PS.Person_id = PC.Person_id
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
					select t2.Person_Snils, t3.PostKind_id
					from v_MedStaffRegion t1
						inner join v_MedStaffFact t3 on t3.MedStaffFact_id = t1.MedStaffFact_id
						inner join v_MedPersonal t2 on t2.MedPersonal_id = t3.MedPersonal_id
					where t1.LpuRegion_id = PC.LpuRegion_id
						and t2.Person_Snils is not null
						and t3.Lpu_id = :Lpu_id
						and (t3.WorkData_begDate is null or t3.WorkData_begDate <= /*coalesce(:Date_upload, cast(dbo.tzGetDate() as date))*/PC.PersonCard_begDate)
						and (t3.WorkData_endDate is null or t3.WorkData_endDate >= /*coalesce(:Date_upload, cast(dbo.tzGetDate() as date))*/PC.PersonCard_begDate)
						and (t1.MedStaffRegion_begDate is null or t1.MedStaffRegion_begDate <= /*coalesce(:Date_upload, cast(dbo.tzGetDate() as date))*/PC.PersonCard_begDate)
						and (t1.MedStaffRegion_endDate is null or t1.MedStaffRegion_endDate >= /*coalesce(:Date_upload, cast(dbo.tzGetDate() as date))*/PC.PersonCard_begDate)
					order by t3.PostKind_id
					limit 1
				) LRMSF on true
				left join lateral (
					select Person_BDZCode
					from v_PersonInfo
					where Person_id = PS.Person_id
					limit 1
				) PI on true
				left join lateral (
					select CardCloseCause_id, PersonCard_begDate
					from v_PersonCard_all t
					where t.Person_id = PC.Person_id
						and t.PersonCard_id != PC.PersonCard_id
						and cast(t.PersonCard_endDate as date) = cast(PC.PersonCard_begDate as date)
					order by t.PersonCard_begDate desc
					limit 1
				) PCL on true
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
			" . (!empty($data['Date_upload']) ? "and cast(coalesce(PC.PersonCard_updDT, PC.PersonCard_insDT) as date) >= :Date_upload" : "") . "
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
				[PI].Person_BDZCode as \"RZ\",
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
					select Person_BDZCode
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
			" . (!empty($data['Date_upload']) ? "and cast(coalesce(PCA.PersonCardAttach_updDT, PCA.PersonCardAttach_insDT) as date) >= :Date_upload" : "") . "
			" . (count($filterList) > 0 ? "and " . implode(' and ', $filterList) : "") . "
			and not exists (
				select PersonCard_id from v_PersonCard_all PC where PC.PersonCardAttach_id = PCA.PersonCardAttach_id limit 1
			)	
			order by IDCASE, DATE_1, T_PRIK
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
					select Orgsmo_f002smocod
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
			$filter .= " and lower(PS.Person_SurName) like lower(:Person_SurName) || '%'";
			$params['Person_SurName'] = rtrim($data['Person_SurName']);
		}
		
		if( !empty($data['Person_FirName']) ) {
			$filter .= " and lower(PS.Person_FirName) like lower(:Person_FirName) || '%'";
			$params['Person_FirName'] = rtrim($data['Person_FirName']);
		}
		
		if( !empty($data['Person_SecName']) ) {
			$filter .= " and lower(PS.Person_SecName) like lower(:Person_SecName) || '%'";
			$params['Person_SecName'] = rtrim($data['Person_SecName']);
		}
		if(!empty($data['PersonCardAttachStatusType_id'])) {
			$filter .= " and PCAST.PersonCardAttachStatusType_id = :PersonCardAttachStatusType_id";
			$params['PersonCardAttachStatusType_id'] = $data['PersonCardAttachStatusType_id'];
		}
		if(isset($data['Person_BirthDay_Range'][0])){
			$filter .= " and PS.Person_BirthDay >= :begBirthday";
			$params['begBirthday'] = $data['Person_BirthDay_Range'][0];
		}
		if(isset($data['Person_BirthDay_Range'][1])){
			$filter .= " and PS.Person_BirthDay <= :endBirthday";
			$params['endBirthday'] = $data['Person_BirthDay_Range'][1];
		}
		if(isset($data['PersonCardAttach_setDate_Range'][0])){
			$filter .= " and PCA.PersonCardAttach_setDate >= :betAttachDate";
			$params['betAttachDate'] = $data['PersonCardAttach_setDate_Range'][0];
		}
		if(isset($data['PersonCardAttach_setDate_Range'][1])){
			$filter .= " and PCA.PersonCardAttach_setDate <= :endAttachDate";
			$params['endAttachDate'] = $data['PersonCardAttach_setDate_Range'][1];
		}
		if( !empty($data['RecMethodType_id']) ) {
			$filter .= " and RMT.RecMethodType_id = :RecMethodType_id ";
			$params['RecMethodType_id'] = rtrim($data['RecMethodType_id']);
		}
		
		$query = "
			select
				--select
				S.PersonCardAttach_id as \"PersonCardAttach_id\",
				S.PersonCardAttach_setDate2 as \"PersonCardAttach_setDate2\",
				S.PersonCardAttach_setDate as \"PersonCardAttach_setDate\",
				S.Person_FIO as \"Person_FIO\",
				S.Lpu_Nick as \"Lpu_Nick\",
				S.Lpu_id as \"Lpu_id\",
				S.Person_id as \"Person_id\",
				S.PersonCardAttachStatusType_id as \"PersonCardAttachStatusType_id\",
				S.PersonCardAttachStatusType_Code as \"PersonCardAttachStatusType_Code\",
				S.PersonCardAttachStatusType_Name as \"PersonCardAttachStatusType_Name\",
				S.LpuRegionType_Name as \"LpuRegionType_Name\",
				S.LpuRegion_Name as \"LpuRegion_Name\",
				S.MSF_FIO as \"MSF_FIO\",
				S.LpuRegion_fapid as \"LpuRegion_fapid\",
				S.LpuRegion_fapName as \"LpuRegion_fapName\",
				S.HasPersonCard as \"HasPersonCard\",
				S.RecMethodType_Name as \"RecMethodType_Name\"
				--end select
			from
				--from
				(
					select
						PCA.PersonCardAttach_id,
						PCA.PersonCardAttach_setDate as PersonCardAttach_setDate2,
						to_char(cast(PCA.PersonCardAttach_setDate as datetime),'dd.mm.yyyy') as PersonCardAttach_setDate,
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
						fapLR.LpuRegion_id as LpuRegion_fapid,
						fapLR.LpuRegion_Name as LpuRegion_fapName,
						case when PC.PersonCard_id is null then 'false' else 'true' end as HasPersonCard,
						RMT.RecMethodType_Name
					from v_PersonCardAttach PCA
					inner join v_PersonState PS on PS.Person_id = PCA.Person_id
					left join v_Lpu L on L.Lpu_id = PCA.Lpu_aid
					left join v_RecMethodType RMT on RMT.RecMethodType_id = PCA.RecMethodType_id
					left join lateral
					(
						select PCAS.PersonCardAttachStatus_id,
						PersonCardAttachStatusType_id
						from v_PersonCardAttachStatus PCAS
						where PCAS.PersonCardAttach_id = PCA.PersonCardAttach_id
						order by PersonCardAttachStatus_setDate desc
						limit 1
					) PCAS on true
					inner join PersonCardAttachStatusType PCAST on PCAST.PersonCardAttachStatusType_id = PCAS.PersonCardAttachStatusType_id
					inner join v_LpuRegion LR on LR.LpuRegion_id = PCA.LpuRegion_id
					inner join v_LpuRegionType LRT on LRT.LpuRegionType_id = LR.LpuRegionType_id
					left join PersonCard PC on PC.PersonCardAttach_id = PCA.PersonCardAttach_id
					left join v_MedStaffFact MSF on MSF.MedStaffFact_id = PCA.MedStaffFact_id
					left join v_LpuRegion fapLR on fapLR.LpuRegion_id = PCA.LpuRegion_fapid
					where PCA.LpuRegion_id is not null
					{$filter}

					union --Костылина, т.к. старые заявления не имеют ни участка, ни персона, ни врача (проверяется по LpuRegion_id - если его нет, значит это старое заявление)
					select 
						PCA.PersonCardAttach_id,
						PCA.PersonCardAttach_setDate as PersonCardAttach_setDate2,
						to_char(cast(PCA.PersonCardAttach_setDate as datetime),'dd.mm.yyyy') as PersonCardAttach_setDate,
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
						fapLR.LpuRegion_id as LpuRegion_fapid,
						fapLR.LpuRegion_id as LpuRegion_fapName,
						'true' as HasPersonCard,
						RMT.RecMethodType_Name
					from v_PersonCardAttach PCA
					left join lateral
					(
						select PCard.PersonCard_id,
						PCard.LpuRegion_id,
						PCard.LpuRegion_fapid,
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
					left join v_LpuRegion fapLR on fapLR.LpuRegion_id = PC.LpuRegion_fapid
					inner join v_PersonState PS on PS.Person_id = PC.Person_id
					left join v_RecMethodType RMT on RMT.RecMethodType_id = PCA.RecMethodType_id
					left join lateral
					(
						select PCAS.PersonCardAttachStatus_id,
						PersonCardAttachStatusType_id
						from v_PersonCardAttachStatus PCAS
						where PCAS.PersonCardAttach_id = PCA.PersonCardAttach_id
						order by PersonCardAttachStatus_setDate desc
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
				\"PersonCardAttach_setDate2\" desc
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
				PC.PersonCard_id as \"PersonCard_id \",
				coalesce(PS.Person_SurName,'') || ' ' || coalesce(PS.Person_FirName,'') || ' ' || coalesce(PS.Person_Secname,'') as \"Person_FIO\",
				LR.LpuRegion_Name as \"LpuRegion_Name\",
				LRT.LpuRegionType_Name as \"LpuRegionType_Name\"
			from v_PersonCard PC
			left join v_LpuRegion LR on LR.LpuRegion_id = PC.LpuRegion_id
			left join v_LpuRegionType LRT on LRT.LpuRegionType_id = LR.LpuRegionType_id
			left join v_PersonState PS on PS.Person_id = PC.Person_id
			where PC.Lpu_id = :Lpu_id and PC.Person_id = :Person_id and PC.LpuAttachType_id=1
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
				coalesce(fLR.LpuRegion_id, fLR2.LpuRegion_id) as \"LpuRegion_fapid\",
				COALESCE(LR.LpuRegionType_id, LR2.LpuRegionType_id, PCA.LpuRegionType_id) as \"LpuRegionType_id\",
				coalesce(PCA.MedStaffFact_id, PC.MedStaffFact_id) as \"MedStaffFact_id\",
				PAC.PersonAmbulatCard_id as \"PersonAmbulatCard_id\",
				rtrim(rtrim(coalesce(PAC.PersonAmbulatCard_Num,PC.PersonCard_Code))) as \"PersonCard_Code\",
				PCA.PersonCardAttach_ExpNameFile as \"PersonCardAttach_ExpNameFile\",
				PCA.PersonCardAttach_ExpNumRow as \"PersonCardAttach_ExpNumRow \"
			from
				v_PersonCardAttach PCA
				left join lateral (
					select PCAS.PersonCardAttachStatus_id,
					PersonCardAttachStatusType_id
					from v_PersonCardAttachStatus PCAS
					where PCAS.PersonCardAttach_id = PCA.PersonCardAttach_id
					order by PersonCardAttachStatus_setDate desc
					limit 1
				) PCAS on true
				left join v_LpuRegion LR on LR.LpuRegion_id = PCA.LpuRegion_id
				left join v_LpuRegion fLR on fLR.LpuRegion_id = PCA.LpuRegion_fapid
				--left join v_LpuRegionType LRT on LRT.LpuRegionType_id = LR.LpuRegionType_id
				left join v_PersonCard_all PC on PC.PersonCardAttach_id = PCA.PersonCardAttach_id
				left join v_PersonState PS on PS.Person_id = PC.Person_id
				left join v_LpuRegion LR2 on LR2.LpuRegion_id = PC.LpuRegion_id
				left join v_LpuRegion fLR2 on fLR2.LpuRegion_id = PC.LpuRegion_fapid
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
			'LpuRegion_fapid' 				=> $data['LpuRegion_fapid'],
			'PersonCardAttach_setDate'		=> $data['PersonCardAttach_setDate'],
			'MedStaffFact_id' 				=> $data['MedStaffFact_id'],
			'Person_id' 					=> $data['Person_id'],
			'PersonAmbulatCard_id' 			=> $data['PersonAmbulatCard_id'],
			'PersonCardAttach_IsSMS' 		=> 1,
			'PersonCardAttach_SMS' 			=> null,
			'PersonCardAttach_IsEmail' 		=> 1,
			'PersonCardAttach_Email' 		=> null,
			'PersonCardAttach_IsHimself' 	=> null,
			'PersonCardAttach_ExpNameFile'	=> !empty($data['PersonCardAttach_ExpNameFile'])?$data['PersonCardAttach_ExpNameFile']:null,
			'PersonCardAttach_ExpNumRow'	=> !empty($data['PersonCardAttach_ExpNumRow'])?$data['PersonCardAttach_ExpNumRow']:null,
			'RecMethodType_id' 				=> empty($data['PersonCardAttach_id']) ? 16 : 
			//При добавлении заявления устанавливать источник записи «Промед: регистратор»
												(!empty($data['RecMethodType_id']) ? $data['RecMethodType_id'] : null),
			'pmUser_id' 					=> $data['pmUser_id']
		);
		if (empty($data['PersonCardAttach_id'])) {
			$procedure = 'p_PersonCardAttach_ins';
		} else {
			$procedure = 'p_PersonCardAttach_upd';
		}

		$this->beginTransaction();

		$query = "
			select
				PersonCardAttach_id as \"PersonCardAttach_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure} (
				PersonCardAttach_id := :PersonCardAttach_id,
				PersonCardAttach_setDate := :PersonCardAttach_setDate,
				Lpu_id := :Lpu_id,
				Lpu_aid := :Lpu_aid,
				Person_id := :Person_id,
				PersonAmbulatCard_id := :PersonAmbulatCard_id,
				LpuRegion_id := :LpuRegion_id,
				LpuRegion_fapid := :LpuRegion_fapid,
				LpuRegionType_id := :LpuRegionType_id,
				MedStaffFact_id := :MedStaffFact_id,
				Address_id := null,
				Polis_id := null,
				PersonCardAttach_IsSMS := :PersonCardAttach_IsSMS,
				PersonCardAttach_SMS := :PersonCardAttach_SMS,
				PersonCardAttach_IsEmail := :PersonCardAttach_IsEmail,
				PersonCardAttach_Email := :PersonCardAttach_Email,
				PersonCardAttach_IsHimself := :PersonCardAttach_IsHimself,
				PersonCardAttach_ExpNameFile := :PersonCardAttach_ExpNameFile,
				PersonCardAttach_ExpNumRow := :PersonCardAttach_ExpNumRow,
				RecMethodType_id := :RecMethodType_id,
				pmUser_id := :pmUser_id
			)
		";
		//echo getDebugSQL($query, $params);exit;
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
				'PersonCardAttach_id' => $response[0]['PersonCardAttach_id'],
				'PersonCardAttachStatusType_Code' => 1,
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
			update dbo.PersonCardAttachStatus set
				PersonCardAttachStatusType_id = :PersonCardAttachStatusType_id,
				pmUser_updID = :pmUser_id,
				PersonCardAttachStatus_updDT = GetDate()
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
					update dbo.PersonCardAttachStatus set
					PersonCardAttachStatusType_id = :PersonCardAttachStatusType_id,
					pmUser_updID = :pmUser_id,
					PersonCardAttachStatus_updDT = GetDate()
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
			select \"PC.PersonCardAttach_id\"
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
					update dbo.PersonCardAttachStatus set
					PersonCardAttachStatusType_id = :PersonCardAttachStatusType_id,
					pmUser_updID = :pmUser_id,
					PersonCardAttachStatus_updDT = GetDate()
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
				select PCAST.PersonCardAttachStatusType_Code, PCAST.PersonCardAttachStatusType_Name
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
				PCA.LpuRegion_fapid as \"LpuRegion_fapid\",
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
				'LpuRegion_fapid' => $resultAttach[0]['LpuRegion_fapid'],
				'PersonAmbulatCard_id' => $resultAttach[0]['PersonAmbulatCard_id'],
				'PersonAmbulatCard_Code' => $resultAttach[0]['PersonAmbulatCard_Code'],
				'pmUser_id' => $data['pmUser_id']
			);
			if($resultAttach[0]['PersonAmbulatCard_id'] == 0){ //Если не указана амбулаторная карта, то берем последнюю у пациента, либо создаем новую
				$query_SearchAmbulatCard = "
					select \"PersonAmbulatCard_Num\"
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
                        from p_PersonAmbulatCard_ins (
							Server_id := :Server_id,
							Person_id := :Person_id,
							PersonAmbulatCard_Num := :PersonAmbulatCard_Num,
							Lpu_id := :Lpu_id,
							PersonAmbulatCard_CloseCause :=:PersonAmbulatCard_CloseCause,
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
                            from p_PersonAmbulatCardLocat_ins (
								Server_id := :Server_id,
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
			$resultPersonCard = array();
			//Проверим, а есть ли у этого пациента активное прикрепление
			$queryPersonCard = "
				select
					PersonCard_id as \"PersonCard_id\",
					Lpu_id as \"Lpu_id\"
				from v_PersonCard
				where Person_id = :Person_id
				and LpuAttachType_id = 1
				order by PersonCard_begDate desc
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

				if (!empty($data['PersonCard_begDate'])) {
					$upd_params['BegDate'] = $data['PersonCard_begDate'];
				} else {
					//https://redmine.swan.perm.ru/issues/108218 - получим дату заявления
					$query_get_AttachDate = "
						select to_char(PersonCardAttach_setDate, 'yyyy-mm-dd') as \"setDate\" 
						from v_PersonCardAttach
						where PersonCardAttach_id = :PersonCardAttach_id
						limit 1
					";
					$result_get_AttachDate = $this->getFirstResultFromQuery($query_get_AttachDate, array('PersonCardAttach_id' => $data['PersonCardAttach_id']));
					if (!empty($result_get_AttachDate)) {
						$upd_params['BegDate'] = $result_get_AttachDate;
					}
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
                $upd_params['LpuRegion_Fapid'] = $params['LpuRegion_fapid'];
                $upd_params['LpuAttachType_id'] = 1;
                $upd_params['MedStaffFact_id'] = $params['MedStaffFact_id'];
                $upd_params['PersonCardAttach_id'] = $data["PersonCardAttach_id"];
                $sql = "
						select
							PersonCard_id as \"PersonCard_id\",
							Error_Code as \"Error_Code\",
							Error_Message as \"Error_Msg\"
						from p_PersonCard_upd (
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

				if (!empty($data['PersonCard_begDate'])) {
					$ins_params['PersonCard_begDate'] = $data['PersonCard_begDate'];
				} else {
					//https://redmine.swan.perm.ru/issues/108218 - получим дату заявления
					$query_get_AttachDate = "
						select to_char(PersonCardAttach_setDate, 'yyyy-mm-dd') as \"setDate\"
						from v_PersonCardAttach
						where PersonCardAttach_id = :PersonCardAttach_id
					";
					$result_get_AttachDate = $this->getFirstResultFromQuery($query_get_AttachDate, array('PersonCardAttach_id' => $data['PersonCardAttach_id']));
					if (!empty($result_get_AttachDate)) {
						$ins_params['PersonCard_begDate'] = $result_get_AttachDate;
					}
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
                $ins_params['LpuRegion_Fapid'] = $params['LpuRegion_fapid'];
                $ins_params['MedStaffFact_id'] = $params['MedStaffFact_id'];
                $ins_params['PersonCardAttach_id'] = $data["PersonCardAttach_id"];
                $sql = "
                    select
                    	PersonCard_id as \"PersonCard_id\",
                        Error_Code as \"Error_Code\",
                        Error_Message as \"Error_Msg\"
                    from p_PersonCard_ins (
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
	function getPersonCardCode($data) {
		$params = array(
			'Lpu_id' => $data['Lpu_id'],
		);
		$query = "
			select
				PersonCard_Code as \"PersonCard_Code\"
			from xp_GenpmID (
				ObjectName := 'PersonCard',
				Lpu_id := :Lpu_id
			)
		";
		$result = $this->queryResult($query, $params);
		if (!is_array($result)) {
			return $this->createError('','Ошибка при генерации номера амбулаторной карты');
		}
		$result[0]['success'] = true;
		return $result;
	}

	/**
	* Поиск человека по ФИО, ДР и СНИЛС
	*/
	function searchPerson($data){
		$query = "
			select \"Person_id\"
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
			select MP.Person_Fio as \"Person_Fio\"
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
			select PC.PersonCard_id as \"PersonCard_id\"
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
			and replace(ltrim(replace(LR.LpuRegion_Name, '0', ' ')), ' ', 0) = replace(ltrim(replace(:LpuRegion_Name, '0', ' ')), ' ', 0)
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
						select PCA.PersonCardAttach_id as \"PersonCardAttach_id\"
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
						and replace(ltrim(replace(LR.LpuRegion_Name, '0', ' ')), ' ', 0) = replace(ltrim(replace(:LpuRegion_Name, '0', ' ')), ' ', 0)
						and (PCA.MedStaffFact_id is null or REPLACE(REPLACE(MP.Person_Snils,'-',''),' ','') = REPLACE(REPLACE(:MedPersonal_Snils,'-',''),' ',''))
						and to_char(PCA.PersonCardAttach_setDate, 'yyyy-mm-dd') = :PersonCard_Date
						limit 1
					";
					//echo getDebugSQL($query_a,$params);die;
					/*if($data['PER_ID'] == '1673')
					{echo getDebugSQL($query_a,$params);die;}*/
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

	/**
	 * Формирование пути до каталога для экспорта
	 * @param string|null $mode
	 * @return string
	 */
	function getExportPersonCardAttachPath($mode = null) {
		$out_dir = $this->regionNick . '_person_card_attach';
		if ($mode) $out_dir .= '_' . $mode;
		return EXPORTPATH_PC . $out_dir . '_' . time();
	}

	/**
	 * Отвязка заявлений о прикреплении от файла экспорта
	 * @param array $data
	 * @return array
	 */
	function clearPersonCardAttachFile($data) {
		$params = array(
			'PersonCardAttach_ExpNameFile' => $data['PersonCardAttach_ExpNameFile'],
			'pmUser_id' => $data['pmUser_id'],
		);
		$query = "
			update 
				PersonCardAttach
			set
				PersonCardAttach_ExpNumRow = null,
				PersonCardAttach_ExpNameFile = null,
				PersonCardAttach_updDT = dbo.tzGetDate(),
				pmUser_updID = :pmUser_id
			where
				PersonCardAttach_ExpNameFile = :PersonCardAttach_ExpNameFile
			returning '' as \"Error_Code\", '' as \"Error_Msg\"
		";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('','Ошибка при отвязке заявлений о прикреплении от файла экспорта');
		}
		return $response;
	}

	/**
	 * Экспорт заявлений о прикреплении
	 * @param array $data
	 * @return array
	 */
	function exportPersonCardAttach($data) {
		$params = array(
			'Lpu_aid' => $data['Lpu_aid'],
			'OrgSMO_id' => $data['OrgSMO_id'],
			'begDate' => $data['dateRange'][0],
			'endDate' => $data['dateRange'][1],
		);

		$response = array(
			'success' => true,
			'xmllink' => null,
			'loglink' => null,
		);

		$query = "
			with MedSpecTree as (
				select
					t.MedSpec_id,
					t.MedSpec_pid,
					t.MedSpec_Code as MedSpec_rCode
				from fed.MedSpec t
				where t.MedSpec_pid is null
				union all
				select
					t.MedSpec_id,
					t.MedSpec_pid,
					t1.MedSpec_rCode
				from fed.MedSpec t
				inner join MedSpecTree t1 on t1.MedSpec_id = t.MedSpec_pid
				where t.MedSpec_pid is not null
			)
			select
				PCA.PersonCardAttach_id as \"ID_ATTACH\",
				ROW_NUMBER() over (order by PCA.PersonCardAttach_id) as \"N_ZAP\",
				case when PCAST.PersonCardAttachStatusType_Code = 4
					then 1 else 0
				end as \"PR_NOV\",
				PS.Person_id as \"ID_PAC\",
				rtrim(PS.Person_SurName) as \"FAM\",
				rtrim(PS.Person_FirName) as \"IM\",
				rtrim(PS.Person_SecName) as \"OT\",
				Sex.Sex_fedid as \"W\",
				to_char(PS.Person_BirthDay, 'yyyy-mm-dd') as \"DR\",
				D.Document_Ser as \"DOCSER\",
				D.Document_Num as \"DOCNUM\",
				PT.PolisType_CodeF008 as \"VPOLIS\",
				case when PT.PolisType_CodeF008 = 3 
					then PS.Person_EdNum else P.Polis_Num 
				end as \"NPOLIS\",
				coalesce(P.Polis_Ser, '') as \"SPOLIS\",
				SMO.Orgsmo_f002smocod as \"SMO\",
				to_char(PCA.PersonCardAttach_setDate, 'yyyy-mm-dd') as \"DATEZ\",
				2 as \"PRZ\",
				null as \"REZ\",
				null as \"DATEREZ\",
				(
					left(MSF.Person_Snils, 3) || '-' || substring(MSF.Person_Snils, 4, 3) || '-' ||
					substring(MSF.Person_Snils, 7, 3) || ' ' || right(MSF.Person_Snils, 2)
				) as \"DOC_CODE\",
				case when MS.MedSpec_rCode = '204'
					then 2 else 1
				end as \"DOC_POST\",
				case when MSR.MedStaffRegion_endDate < PCA.PersonCardAttach_setDate
					then 0 else 1
				end as \"DOC_ACTUAL\",
				null as \"COMENTZ\"
			from
				v_PersonCardAttach PCA
				inner join v_Lpu L on L.Lpu_id = PCA.Lpu_id
				inner join lateral (
					select PS.*
					from v_Person_all PS
					where PS.Person_id = PCA.Person_id
					and PS.PersonEvn_insDT <= PCA.PersonCardAttach_setDate
					order by PS.PersonEvn_insDT desc
					limit 1
				) PS on true
				left join v_Sex Sex on Sex.Sex_id = PS.Sex_id
				left join v_Document D on D.Document_id = PS.Document_id
				left join v_Polis P on P.Polis_id = PS.Polis_id
				left join v_PolisType PT on PT.PolisType_id = P.PolisType_id
				left join v_OrgSMO SMO on SMO.OrgSMO_id = P.OrgSMO_id
				left join v_MedStaffFact MSF on MSF.MedStaffFact_id = PCA.MedStaffFact_id
				left join v_MedSpecOms MSO on MSO.MedSpecOms_id = MSF.MedSpecOms_id
				left join MedSpecTree MS on MS.MedSpec_id = MSO.MedSpec_id
				left join lateral (
					select
						MSR.*
					from
						v_MedStaffRegion MSR
					where
						MSR.MedStaffFact_id = MSF.MedStaffFact_id
						and MSR.LpuRegion_id = PCA.LpuRegion_id
						and MSR.MedStaffRegion_begDate <= PCA.PersonCardAttach_setDate
					order by
						MSR.MedStaffRegion_isMain desc,
						MSR.MedStaffRegion_begDate desc
					limit 1
				) MSR on true
				left join lateral (
					select PCAS.PersonCardAttachStatusType_id
					from v_PersonCardAttachStatus PCAS
					where PCAS.PersonCardAttach_id = PCA.PersonCardAttach_id
					order by PCAS.PersonCardAttachStatus_setDate desc
					limit 1
				) PCAS on true
				left join v_PersonCardAttachStatusType PCAST on PCAST.PersonCardAttachStatusType_id = PCAS.PersonCardAttachStatusType_id
			where
				L.Lpu_id = :Lpu_aid
				and SMO.OrgSMO_id = :OrgSMO_id
				and PCA.PersonCardAttach_setDate between :begDate and :endDate
				and PCAST.PersonCardAttachStatusType_Code in (1,4,5)
		";
		$pers_list = $this->queryResult($query, $params);
		if (!is_array($pers_list)) {
			return $this->createError('','Ошибка при получении даных заявлений');
		}
		if (count($pers_list) == 0) {
			return $this->createError('','Отсутвуют данные для экспорта');
		}

		$query = "
			select
				L.Lpu_f003mcod as \"CODE_MO\",
				SMO.Orgsmo_f002smocod as \"SMO\",
				to_char(dbo.tzGetDate(), 'yyyy-mm-dd') as \"DATE\",
				year(dbo.tzGetDate()) as \"YEAR\",
				month(dbo.tzGetDate()) as \"MONTH\"
			from
				(select 1 as a) t
				inner join v_Lpu L on L.Lpu_id = :Lpu_aid
				inner join v_OrgSMO SMO on SMO.OrgSMO_id = :OrgSMO_id
		";

		$resp = $this->getFirstRowFromQuery($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при получении данных для заголовка файла');
		}

		$MO = $resp['CODE_MO'];
		$SMO = $resp['SMO'];
		$YY = substr($resp['YEAR'], 2, 2);
		$MM = sprintf("%02d", $resp['MONTH']);
		$NN = (strlen($data['packageNumber']) < 2) ? sprintf('%02d', $data['packageNumber']) : $data['packageNumber'];

		$filename = "SZPM{$MO}S{$SMO}_{$YY}{$MM}{$NN}";

		$zl = array_merge($resp, array(
			'VERSION' => '1.0',
			'FILENAME' => $filename,
			'ZAP' => count($pers_list),
			'LETTER' => null,
			'COMMENT' => null,
			'PERS' => $pers_list,
		));
		unset($pers_list);

		$this->beginTransaction();

		try {
			$resp = $this->clearPersonCardAttachFile(array(
				'PersonCardAttach_ExpNameFile' => $filename,
				'pmUser_id' => $data['pmUser_id']
			));
			if (!$this->isSuccessful($resp)) {
				throw new Exception($resp[0]['Error_Msg']);
			}

			$currDate = $this->currentDT->format('Y-m-d');
			$log = array();
			foreach ($zl['PERS'] as $idx => $pers) {
				if (empty($pers['DOC_CODE'])) {
					$log[] = "N_ZAP={$pers['N_ZAP']}: не указан врач";
					unset($zl['PERS'][$idx]);
				} else if (!$pers['DOC_ACTUAL']) {
					$log[] = "N_ZAP={$pers['N_ZAP']}: закончился период врача на участке";
					unset($zl['PERS'][$idx]);
				} else {
					$resp = $this->swUpdate('PersonCardAttach', array(
						'PersonCardAttach_id' => $pers['ID_ATTACH'],
						'PersonCardAttach_ExpNumRow' => $pers['N_ZAP'],
						'PersonCardAttach_ExpNameFile' => $filename,
						'pmUser_id' => $data['pmUser_id']
					));
					if (!$this->isSuccessful($resp)) {
						throw new Exception($resp[0]['Error_Msg']);
					}

					$resp = $this->savePersonCardAttachStatus(array(
						'PersonCardAttach_id' => $pers['ID_ATTACH'],
						'PersonCardAttachStatusType_Code' => 2,
						'PersonCardAttachStatus_setDate' => $currDate,
						'pmUser_id' => $data['pmUser_id']
					));
					if (!$this->isSuccessful($resp)) {
						throw new Exception($resp[0]['Error_Msg']);
					}
				}
			}

			$xmlfilename = $filename . '.xml';
			$logfilename = $filename . '_лог.txt';

			$out_path = $this->getExportPersonCardAttachPath();
			if (!is_dir($out_path)) mkdir($out_path);

			$xmlfilepath = $out_path . "/" . $xmlfilename;
			$logfilepath = $out_path . "/" . $logfilename;

			if ($zl['ZAP'] > 0) {
				$this->load->library('parser');
				$tpl = 'export_xml/vologda_person_card_attach';
				$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\"?>\n" . $this->parser->parse_ext($tpl, $zl, true);
				file_put_contents($xmlfilepath, toAnsi($xml, true));
				$response['xmllink'] = $xmlfilepath;
			}

			if (count($log) > 0) {
				file_put_contents(toAnsi($logfilepath, true), implode("\n\n", $log));
				$response['loglink'] = $logfilepath;
			}
		} catch(Exception $e) {
			$this->rollbackTransaction();
			return $this->createError($e->getCode(), $e->getMessage());
		}

		$this->commitTransaction();

		return array($response);
	}

	/**
	 * Импорт ошибок ФЛК от СМО
	 * @param array $data
	 * @return array
	 */
	function importPersonCardAttachFLK($data) {
		$response = array(
			'success' => true,
			'loglink' => null,
			'infilecount' => 0,
			'recievedcount' => 0,
		);

		$invalidFileMsg = 'Выбранный файл не является файлом с протоколом ФЛК';

		if ($data['File']['type'] != 'text/xml') {
			return $this->createError('',$invalidFileMsg);
		}

		$xml = objectToArray(simplexml_load_file($data['File']['tmp_name']));

		if (!isset($xml['PR'])) {
			$xml['PR'] = array();
		} else if (!is_array($xml['PR'])) {
			$xml['PR'] = array($xml['PR']);
		}

		$response['infilecount'] = count($xml['PR']);

		$filename = $xml['FNAME'];
		$origfilename = $xml['FNAME_I'];
		$pattern  = '/^SZPM(\d+)S(\d+)_(\d{2})(\d{2})(\d+)$/';

		if (!preg_match($pattern, $origfilename, $matches)) {
			return $this->createError('',$invalidFileMsg);
		}
		list(,$MO,$SMO,$YY,$MM,$NN) = $matches;

		$Lpu_id = $this->getFirstResultFromQuery("
			select Lpu_id as \"Lpu_id\" from v_Lpu where Lpu_f003mcod = :Lpu_f003mcod limit 1
		", array(
			'Lpu_f003mcod' => $MO
		), true);
		if ($Lpu_id === false) {
			return $this->createError('','Ошибка при получении идентификатора МО');
		}
		if ($Lpu_id != $data['Lpu_id']) {
			return $this->createError('',$invalidFileMsg);
		}

		$params = array(
			'filename' => $origfilename,
		);
		$query = "
			select
				PersonCardAttach_id as \"PersonCardAttach_id\",
				PersonCardAttach_ExpNumRow as \"PersonCardAttach_ExpNumRow\"
			from
				v_PersonCardAttach PCA
			where 
				PCA.PersonCardAttach_ExpNameFile = :filename
			order by
				PersonCardAttach_ExpNumRow
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при получении списка заявок на прикрепление');
		}

		$id_by_num = array();
		foreach($resp as $item) {
			$id_by_num[$item['PersonCardAttach_ExpNumRow']] = $item['PersonCardAttach_id'];
		}
		unset($resp);

		$log = array();
		$date = $this->currentDT->format('Y-m-d');

		$this->beginTransaction();

		try {
			foreach ($xml['PR'] as $item) {
				$PersonCardAttach_id = isset($id_by_num[$item['N_ZAP']]) ? $id_by_num[$item['N_ZAP']] : null;

				if (empty($PersonCardAttach_id)) {
					$log[] = "Для ошибки с кодом {$item['OSHIB']} по записи N_ZAP = {$item['N_ZAP']} не удалось найти заявление";
					continue;
				}

				$resp = $this->savePersonCardAttachStatus(array(
					'PersonCardAttach_id' => $PersonCardAttach_id,
					'PersonCardAttachStatusType_Code' => 5,
					'PersonCardAttachStatus_setDate' => $date,
					'pmUser_id' => $data['pmUser_id']
				));
				if (!$this->isSuccessful($resp)) {
					throw new Exception($resp[0]['Error_Msg']);
				}

				$response['recievedcount']++;
			}

			if (count($log) > 0) {
				$logfilename = $filename . '_лог.txt';

				$out_path = $this->getExportPersonCardAttachPath('flk');
				if (!is_dir($out_path)) mkdir($out_path);

				$logfilepath = $out_path . "/" . $logfilename;

				file_put_contents(toAnsi($logfilepath, true), implode("\n\n", $log));
				$response['loglink'] = $logfilepath;
			}
		} catch(Exception $e) {
			$this->rollbackTransaction();
			return $this->createError($e->getCode(), $e->getMessage());
		}

		$this->commitTransaction();

		return array($response);
	}

	/**
	 * Импорт предварительного ответа по прикрепленному населению
	 * @param array $data
	 * @return array
	 */
	function importPersonCardAttachResponse($data) {
		$response = array(
			'success' => true,
			'loglink' => null,
			'infilecount' => 0,
			'recievedcount' => 0,
		);

		$invalidFileMsg = 'Выбранный файл не является предварительным ответом от СМО по прикрепленному населению';

		if ($data['File']['type'] != 'text/xml') {
			return $this->createError('',$invalidFileMsg);
		}

		$xml = objectToArray(simplexml_load_file($data['File']['tmp_name']));

		if (!isset($xml['PERS'])) {
			$xml['PERS'] = array();
		} else if (!is_array($xml['PERS'])) {
			$xml['PERS'] = array($xml['PERS']);
		}

		$response['infilecount'] = count($xml['PERS']);

		$filename = $xml['ZGLV']['FILENAME'];
		$pattern  = '/^NZPS(\d+)M(\d+)_(\d{2})(\d{2})(\d+)$/';

		if (!preg_match($pattern, $filename, $matches)) {
			return $this->createError('',$invalidFileMsg);
		}
		list(,$SMO,$MO,$YY,$MM,$NN) = $matches;

		$Lpu_id = $this->getFirstResultFromQuery("
			select Lpu_id as \"Lpu_id\" from v_Lpu where Lpu_f003mcod = :Lpu_f003mcod limit 1
		", array(
			'Lpu_f003mcod' => $MO
		), true);
		if ($Lpu_id === false) {
			return $this->createError('','Ошибка при получении идентификатора МО');
		}
		if ($Lpu_id != $data['Lpu_id']) {
			return $this->createError('',$invalidFileMsg);
		}

		$log = array();
		$date = $this->currentDT->format('Y-m-d');

		$this->beginTransaction();

		try {
			foreach ($xml['PERS'] as $item) {
				$PersonCardAttach_id = $this->getFirstResultFromQuery("
					select
						PCA.PersonCardAttach_id as \"PersonCardAttach_id\"
					from
						v_PersonCardAttach PCA
						inner join lateral (
							select PS.*
							from v_Person_all PS
							where PS.Person_id = PCA.Person_id
							and PS.PersonEvn_insDT <= PCA.PersonCardAttach_setDate
							order by PS.PersonEvn_insDT desc
							limit 1
						) PS on true
						left join v_Polis P on P.Polis_id = PS.Polis_id
					where
						PCA.PersonCardAttach_setDate = :DATEZ
						and PS.Person_SurName = :FAM
						and PS.Person_FirName = :IM
						and coalesce(PS.Person_SecName, '') = coalesce(:OT::varchar, '')
						and PS.Person_BirthDay = :DR
						and (P.Polis_Num = :NPOLIS or PS.Person_EdNum = :NPOLIS)
					order by
						PCA.PersonCardAttach_insDT desc
					limit 1
				", $item, true);
				if ($PersonCardAttach_id === false) {
					throw new Exception('Ошибка при поиске заявления по ЗЛ');
				}

				if (empty($PersonCardAttach_id)) {
					$log[] = "Для записи по ЗЛ {$item['FAM']} {$item['IM']} {$item['OT']} {$item['DR']} {$item['NPOLIS']} не удалось найти заявление";
					continue;
				}

				$resp = $this->savePersonCardAttachStatus(array(
					'PersonCardAttach_id' => $PersonCardAttach_id,
					'PersonCardAttachStatusType_Code' => 4,
					'PersonCardAttachStatus_setDate' => $date,
					'pmUser_id' => $data['pmUser_id']
				));
				if (!$this->isSuccessful($resp)) {
					throw new Exception($resp[0]['Error_Msg']);
				}

				$response['recievedcount']++;
			}

			if (count($log) > 0) {
				$logfilename = $filename . '_лог.txt';

				$out_path = $this->getExportPersonCardAttachPath('response');
				if (!is_dir($out_path)) mkdir($out_path);

				$logfilepath = $out_path . "/" . $logfilename;

				file_put_contents(toAnsi($logfilepath, true), implode("\n\n", $log));
				$response['loglink'] = $logfilepath;
			}
		} catch(Exception $e) {
			$this->rollbackTransaction();
			return $this->createError($e->getCode(), $e->getMessage());
		}

		$this->commitTransaction();

		return array($response);
	}

	/**
	 * Импорт сведений о ЗЛ, открепленных от МО
	 * @param array $data
	 * @return array
	 */
	function importPersonCardDetach($data) {
		$response = array(
			'success' => true,
			'loglink' => null,
			'infilecount' => 0,
			'recievedcount' => 0,
		);

		$invalidFileMsg = 'Выбранный файл не является файлом со сведениями о ЗЛ, открепленных от МО';

		if ($data['File']['type'] != 'text/xml') {
			return $this->createError('',$invalidFileMsg);
		}

		$xml = objectToArray(simplexml_load_file($data['File']['tmp_name']));

		if (!isset($xml['PERS'])) {
			$xml['PERS'] = array();
		} else if (!is_array($xml['PERS'])) {
			$xml['PERS'] = array($xml['PERS']);
		}

		$response['infilecount'] = count($xml['PERS']);

		$filename = $xml['ZGLV']['FILENAME'];
		$pattern  = '/^OZPS(\d+)M(\d+)_(\d{2})(\d{2})$/';

		if (!preg_match($pattern, $filename, $matches)) {
			return $this->createError('',$invalidFileMsg);
		}
		list(,$SMO,$MO,$YY,$MM) = $matches;

		$Lpu_id = $this->getFirstResultFromQuery("
			select Lpu_id as \"Lpu_id\" from v_Lpu where Lpu_f003mcod = :Lpu_f003mcod limit 1
		", array(
			'Lpu_f003mcod' => $MO
		), true);
		if ($Lpu_id === false) {
			return $this->createError('','Ошибка при получении идентификатора МО');
		}
		if ($Lpu_id != $data['Lpu_id']) {
			return $this->createError('',$invalidFileMsg);
		}

		$log = array();
		$date = sprintf('%d-%02d-01', $xml['ZGLV']['YEAR'], $xml['ZGLV']['MONTH']);

		$this->beginTransaction();

		try {
			foreach ($xml['PERS'] as $item) {
				$Person_ids = $this->queryList("
					select
						PS.Person_id as \"Person_id\"
					from
						v_PersonState PS
					where
						PS.Person_SurName = :FAM
						and PS.Person_FirName = :IM
						and coalesce(PS.Person_SecName, '') = coalesce(:OT::varchar, '')
						and PS.Person_BirthDay = :DR
						and (PS.Polis_Num = :NPOLIS or PS.Person_EdNum = :NPOLIS)
				", $item, true);
				if (!is_array($Person_ids)) {
					throw new Exception('Ошибка при поиске человека');
				}

				if (count($Person_ids) != 1) {
					$log[] = "Не удалось идентифицировать человека {$item['FAM']} {$item['IM']} {$item['OT']} {$item['DR']} {$item['NPOLIS']}";
					continue;
				}

				$resp = $this->closePersonCardByImport(array(
					'Lpu_id' => $Lpu_id,
					'Person_id' => $Person_ids[0],
					'date' => $date,
					'prz' => $item['PRZ'],
					'Server_id' => $data['Server_id'],
					'pmUser_id' => $data['pmUser_id'],
				));
				if (!$this->isSuccessful($resp)) {
					throw new Exception($resp[0]['Error_Msg']);
				}
				if (!empty($resp[0]['PersonCard_id'])) {
					$log[] = "N_ZAP={$item['N_ZAP']}, {$item['FAM']} {$item['IM']} {$item['OT']}, {$item['DR']}, {$item['NPOLIS']}" .
						" был откреплен {$resp[0]['PersonCard_endDate']} по причине \"{$resp[0]['PRZ_Name']}\"";
				}

				$response['recievedcount']++;
			}

			if (count($log) > 0) {
				$logfilename = $filename . '_лог.txt';

				$out_path = $this->getExportPersonCardAttachPath('response');
				if (!is_dir($out_path)) mkdir($out_path);

				$logfilepath = $out_path . "/" . $logfilename;

				file_put_contents(toAnsi($logfilepath, true), implode("\n\n", $log));
				$response['loglink'] = $logfilepath;
			}
		} catch(Exception $e) {
			$this->rollbackTransaction();
			return $this->createError($e->getCode(), $e->getMessage());
		}

		$this->commitTransaction();

		return array($response);
	}

	/**
	 * Открепление при импорте сведений о ЗЛ
	 * @param array $data
	 * @return array
	 */
	function closePersonCardByImport($data) {
		$response = array(
			'success' => true,
			'PersonCard_id' => null,
			'PersonCard_endDate' => null,
			'PRZ_Name' => null,
			'Error_Code' => null,
			'Error_Msg' => null,
		);

		$params = array(
			'Lpu_id' => $data['Lpu_id'],
			'Person_id' => $data['Person_id'],
			'date' => $data['date'],
			'prz' => $data['prz'],
		);
		$query = "
			with PRZ as (
				select 1 as PRZ_Code, 'Не является застрахованным' as PRZ_Name, 8 as CardCloseCause_Code
				union select 2 as PRZ_Code, 'Умерший' as PRZ_Name, 2 as CardCloseCause_Code
				union select 3 as PRZ_Code, 'Прикреплен к другой МО' as PRZ_Name, 1 as CardCloseCause_Code
				union select 4 as PRZ_Code, 'Смена МО по возрастному принципу' as PRZ_Name, 3 as CardCloseCause_Code
				union select 5 as PRZ_Code, 'Изменение территориального деления' as PRZ_Name, 8 as CardCloseCause_Code
			)
			select
				PC.PersonCard_id as \"PersonCard_id\",
				PC.Person_id as \"Person_id\",
				PC.Lpu_id as \"Lpu_id\",
				PC.LpuRegion_id as \"LpuRegion_id\",
				PC.LpuAttachType_id as \"LpuAttachType_id\",
				PC.PersonCard_Code as \"PersonCard_Code\",
				to_char(PC.PersonCard_begDate, 'yyyy-mm-dd') as \"PersonCard_begDate\",
				to_char(:date - interval '1 day', 'yyyy-mm-dd') as \"PersonCard_endDate\",
				CCC.CardCloseCause_id as \"CardCloseCause_id\",
				PRZ.PRZ_Name as \"PRZ_Name\",
				PC.PersonCard_IsAttachCondit as \"PersonCard_IsAttachCondit\",
				PC.OrgSMO_id as \"OrgSMO_id\",
				PC.PersonCardAttach_id as \"PersonCardAttach_id\",
				PC.LpuRegion_fapid as \"LpuRegion_fapid\",
				PC.LpuRegionType_id as \"LpuRegionType_id\",
				PC.MedStaffFact_id as \"MedStaffFact_id\"
			from
				v_PersonCard PC
				left join v_LpuAttachType LAT on LAT.LpuAttachType_id = PC.LpuAttachType_id
				left join PRZ on PRZ.PRZ_Code = :prz
				left join v_CardCloseCause CCC on CCC.CardCloseCause_Code = PRZ.CardCloseCause_Code
			where
				PC.Lpu_id = :Lpu_id
				and PC.Person_id = :Person_id
				and PC.PersonCard_begDate < :date
				and PC.PersonCard_endDate is null
				and LAT.LpuAttachType_SysNick = 'main'
			order by
				PC.PersonCard_begDate desc
			limit 1
		";
		$PersonCard = $this->getFirstRowFromQuery($query, $params, true);
		if ($PersonCard === false) {
			return $this->createError('','Ошибка при поиске прикрепления человека');
		}

		if (empty($PersonCard)) {
			return array($response);
		}

		$params = array_merge($PersonCard, array(
			'Server_id' => $data['Server_id'],
			'pmUser_id' => $data['pmUser_id'],
		));
		$query = "
			select
				PersonCard_id as \"PersonCard_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_PersonCard_upd (
				PersonCard_id := :PersonCard_id,
				Person_id := :Person_id,
				Lpu_id := :Lpu_id,
				Server_id := :Server_id,
				PersonCard_begDate := :PersonCard_begDate,
				PersonCard_endDate := :PersonCard_endDate,
				PersonCard_Code := :PersonCard_Code,
				PersonCard_IsAttachCondit := :PersonCard_IsAttachCondit,
				OrgSMO_id := :OrgSMO_id,
				LpuRegion_id := :LpuRegion_id,
				LpuRegion_fapid := :LpuRegion_fapid,
				LpuAttachType_id := :LpuAttachType_id,
				CardCloseCause_id := :CardCloseCause_id,
				PersonCardAttach_id := :PersonCardAttach_id,
				LpuRegionType_id := :LpuRegionType_id,
				MedStaffFact_id := :MedStaffFact_id,
				pmUser_id := :pmUser_id
			)
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при закрытии прикрепления');
		}
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}

		$response['PersonCard_id'] = $resp[0]['PersonCard_id'];
		$response['PersonCard_endDate'] = $PersonCard['PersonCard_endDate'];
		$response['PRZ_Name'] = $PersonCard['PRZ_Name'];

		return array($response);
	}

	/**
	 * Импорт регистра прикрепленного населения
	 * @param array $data
	 * @return array
	 */
	function importPersonCardRegister($data) {
		$this->getExportPersonCardAttachPath('register');
		exit;

		$response = array(
			'success' => true,
			'loglink' => null,
			'infilecount' => 0,
			'createdcount' => 0,
		);

		$invalidFileMsg = 'Структура выбранного файла не соответствует структуре файла с регистром прикрепленного населения';

		$struct = array(
			'N_PP' => 'int',
			'FAM' => 'string',
			'IM' => 'string',
			'OT' => 'string',
			'DR' => 'date',
			'W' => 'int',
			'T_POL' => 'int',
			'N_POL' => 'string',
			'MCOD' => 'string',
			'DATA_ZVL' => 'date',
			'DOC_CODE' => 'string'
		);

		$to_charRecord = function($record) use($struct) {
			$_record = array();
			foreach($record as $key => $value) {
				$type = isset($struct[$key])?$struct[$key]:'string';
				switch(true) {
					case ($type == 'int'):
						$_record[$key] = (int)$value;
						break;
					case ($type == 'date'):
						$_record[$key] = date_create(trim($value))->format('Y-m-d');
						if ($_record[$key] == '2049-12-31') $_record[$key] = null;
						break;
					case ($key == 'DOC_CODE'):
						$_record[$key] = str_replace(array('-',' '), '', trim($value));
						break;
					default:
						$_record[$key] = trim(toUTF($value, true));
						break;
				}
			}
			return $_record;
		};

		$filenameparts = explode('.', $data['File']['name']);
		$filename = $filenameparts[0];
		$fileext = $filenameparts[1];

		if ($fileext != 'dbf') {
			return $this->createError('',$invalidFileMsg);
		}

		$dbf = dbase_open($data['File']['tmp_name'], 0);
		if (!$dbf) {
			return $this->createError('','Не удалось открыть файл');
		}

		$dbf_header = dbase_get_header_info($dbf);
		if (!$dbf_header) {
			return $this->createError('','Не удалось прочитать файл');
		}

		$structCountdown = count($struct);
		foreach($dbf_header as $field) {
			if (isset($struct[$field['name']])) {
				$structCountdown--;
			}
		}
		if ($structCountdown > 0) {
			return $this->createError('',$invalidFileMsg);
		}

		$count = dbase_numrecords($dbf);
		if ($count == 0) {
			return array($response);
		}
		$response['infilecount'] = $count;

		$Lpu_OGRN = $this->getFirstResultFromQuery("
			select rtrim(Lpu_OGRN) as \"Lpu_OGRN\"
			from v_Lpu where Lpu_id = :Lpu_id limit 1
		", array(
			'Lpu_id' => $data['Lpu_id']
		));
		if (empty($Lpu_OGRN)) {
			return $this->createError('','Ошибка при получении ОГРН текущей МО');
		}
		for ($i = 1; $i <= $count; $i++) {
			$item = $to_charRecord(dbase_get_record_with_names($dbf, $i));
			if ($item['MCOD'] != $Lpu_OGRN) {
				return $this->createError('','Выбранный файл не является файлом с регистром прикрепленного населения текущей МО');
			}
		}

		$log = array();
		$date = $this->currentDT->format('Y-m-01');
		$currDate = $this->currentDT->format('Y-m-d');

		$this->beginTransaction();

		try {
			for ($i = 1; $i <= $count; $i++) {
				$item = $to_charRecord(dbase_get_record_with_names($dbf, $i));

				$PersonCard_begDate = !empty($item['DATA_ZVL'])?$item['DATA_ZVL']:$date;

				//Поиск человека
				$PersonList = $this->queryResult("
					select
						PS.Person_id as \"Person_id\",
						dbo.Age2(PS.Person_BirthDay, cast(:PersonCard_begDate as date)) as \"Person_Age\"
					from
						v_PersonState
					where
						PS.Person_SurName = :FAM
						and PS.Person_FirName = :IM
						and coalesce(PS.Person_SecName, '') = coalesce(:OT::varchar, '')
						and PS.Person_BirthDay = :DR
						and (PS.Polis_Num = :N_POL or PS.Person_EdNum = :N_POL)
				", array_merge($item, array(
					'PersonCard_begDate' => $PersonCard_begDate
				)));
				if (!is_array($PersonList)) {
					throw new Exception('Ошибка при поиске человека');
				}
				if (count($PersonList) != 1) {
					$log[] = "Не удалось идентифицировать человека" .
						" N_PP={$item['N_PP']}, {$item['FAM']} {$item['IM']} {$item['OT']}, {$item['DR']}, {$item['N_POL']}";
					continue;
				}

				//Поиск действующего прикрепления
				$PersonCard = $this->getFirstRowFromQuery("
					select
						PC.PersonCard_id as \"PersonCard_id\",
						MSF.Person_Snils as \"MedPersonal_Snils\"
					from
						v_PersonCard PC
						inner join v_LpuAttachType LAT on LAT.LpuAttachType_id = PC.LpuAttachType_id
						inner join v_LpuRegion LR on LR.LpuRegion_id = PC.LpuRegion_id
						inner join v_MedStaffFact MSF on MSF.MedStaffFact_id = PC.MedStaffFact_id
					where
						PC.Lpu_id = :Lpu_id
						and PC.Person_id = :Person_id
						and PC.PersonCard_endDate is null
						and LAT.LpuAttachType_SysNick = 'main'
					limit 1
				", array(
					'Lpu_id' => $data['Lpu_id'],
					'Person_id' => $PersonList[0]['Person_id'],
				), true);
				if ($PersonCard === false) {
					throw new Exception('Ошибка при поиске прикрепления');
				}
				if ($PersonCard && $PersonCard['MedPersonal_Snils'] == $item['DOC_CODE']) {
					continue;
				}

				//Если нет действующего прикрепления или врач прикрепления отличается от врача из регистра (определяется по СНИЛС),
				//то выполняется поиск врача на участке и поиск заявления для создания нового прикрепления
				$params = array(
					'Person_id' => $PersonList[0]['Person_id'],
					'Person_Age' => $PersonList[0]['Person_Age'],
					'Lpu_id' => $data['Lpu_id'],
					'MedPersonal_Snils' => $item['DOC_CODE'],
					'PersonCard_begDate' => $PersonCard_begDate,
				);
				$query = "
					select
						'add' as \"action\",
						null as \"PersonCard_id\",
						PrevPC.PersonCard_id as \"PrevPersonCard_id\",
						PCA.PersonCardAttach_id as \"PersonCardAttach_id\",
						:Lpu_id as \"Lpu_id\",
						:Person_id as \"Person_id\",
						LAT.LpuAttachType_id as \"LpuAttachType_id\",
						to_char(:PersonCard_begDate, 'yyyy-mm-dd') as \"PersonCard_begDate\",
						LR.LpuRegion_id as \"LpuRegion_id\",
						LR.LpuRegion_Name as \"LpuRegion_Name\",
						LR.LpuRegionType_id as \"LpuRegionType_id\",
						MSF.MedStaffFact_id as \"MedStaffFact_id\",
						MSF.Person_Fio as \"MedPersonal_Fio\"
					from
						v_MedStaffRegion MSR
						inner join v_MedStaffFact MSF on MSF.MedStaffFact_id = MSR.MedStaffFact_id
						inner join v_LpuRegion LR on LR.LpuRegion_id = MSR.LpuRegion_id
						left join v_LpuAttachType LAT on LAT.LpuAttachType_SysNick = 'main'
						left join lateral (
							select
								PCA.PersonCardAttach_id
							from
								v_PersonCardAttach PCA
								left join v_PersonCard_all PC PC.PersonCardAttach_id = PCA.PersonCardAttach_id
							where
								PCA.Person_id = :Person_id
								and PCA.MedStaffFact_id = MSF.MedStaffFact_id
								and PCA.LpuRegion_id = LR.LpuRegion_id
								and PC.PersonCard_id is null
							order by
								PCA.PersonCardAttach_setDate desc
							limit 1
						) PCA on true
						left join lateral (
							select
								PC.PersonCard_id
							from 
								v_PersonCard PC
							where 
								PC.Person_id = :Person_id
								and PC.LpuAttachType_id = LAT.LpuAttachType_id
								and PC.PersonCard_endDate is null
							order by 
								PC.PersonCard_begDate desc
							limit 1
						) PrevPC on true
					where
						MSF.Lpu_id = :Lpu_id
						and MSF.Person_Snils = :MedPersonal_Snils
						and MSR.MedStaffRegion_begDate <= :PersonCard_begDate
						and (MSR.MedStaffRegion_endDate is null or MSR.MedStaffRegion_endDate > :PersonCard_begDate)
						and LR.LpuRegionType_SysNick in ('ter','ped','vop')
					order by
						case 
							when PCA.PersonCardAttach_id is not null then 1 
							else 0 
						end desc,
						case
							when LR.LpuRegionType_SysNick = 'vop' then 1
							when LR.LpuRegionType_SysNick = 'ter' and :Person_Age >= 18 then 1
							when LR.LpuRegionType_SysNick = 'ped' and :Person_Age < 18 then 1
							else 0
						end desc
					limit 1
				";
				$PersonCard = $this->getFirstRowFromQuery($query, $params, true);
				if ($PersonCard === false) {
					throw new Exception('Ошибка при определении врача и заявления на прикрепление');
				}
				if (empty($PersonCard)) {
					$log[] = "Прикрепление {$item['FAM']} {$item['IM']} {$item['OT']}, {$item['DR']}, {$item['N_POL']}" .
						" не было создано, так как не удалось найти врача со СНИЛС {$item['DOC_CODE']}";
					continue;
				}

				if (!empty($PersonCard['PersonCardAttach_id'])) {
					$resp = $this->savePersonCardAttachStatus(array(
						'PersonCardAttach_id' => $PersonCard['PersonCardAttach_id'],
						'PersonCardAttachStatusType_Code' => 3,
						'PersonCardAttachStatus_setDate' => $currDate,
						'pmUser_id' => $data['pmUser_id']
					));
					if (!$this->isSuccessful($resp)) {
						throw new Exception($resp[0]['Error_Msg']);
					}

					$PersonCardResp = $this->addPersonCardByAttach(array(
						'PersonCardAttach_id' => $PersonCard['PersonCardAttach_id'],
						'PersonCard_begDate' => $PersonCard['PersonCard_begDate'],
						'Server_id' => $data['Server_id'],
						'pmUser_id' => $data['pmUser_id']
					));
					if (!is_array($PersonCardResp)) {
						throw new Exception('Ошибка при создании прикрепления на основе заявления');
					}
					if (!$this->isSuccessful($PersonCardResp)) {
						throw new Exception($PersonCardResp[0]['Error_Msg']);
					}
				} else {
					$resp = $this->findOrCreatePersonCardCode(array_merge($PersonCard, array(
						'Server_id' => $data['Server_id'],
						'pmUser_id' => $data['pmUser_id']
					)));
					if (!$this->isSuccessful($resp)) {
						throw new Exception($resp[0]['Error_Msg']);
					}

					$PersonCardResp = $this->savePersonCard(array_merge($PersonCard, array(
						'PersonCard_Code' => $resp[0]['PersonCard_Code'],
						'Server_id' => $data['Server_id'],
						'pmUser_id' => $data['pmUser_id']
					)));
					if (!is_array($PersonCardResp)) {
						throw new Exception('Ошибка при создании прикрепления');
					}
					if (!$this->isSuccessful($PersonCardResp)) {
						throw new Exception($PersonCardResp[0]['Error_Msg']);
					}
				}

				if (!empty($PersonCardResp[0]['PersonCard_id'])) {
					$log[] = "{$item['FAM']} {$item['IM']} {$item['OT']}, {$item['DR']}, {$item['N_POL']} был прикреплен" .
						" {$PersonCard['PersonCard_begDate']} к участку №{$PersonCard['LpuRegion_Name']}, врач {$PersonCard['MedPersonal_Fio']}";

					$response['createdcount']++;
				}
			}

			if (count($log) > 0) {
				$logfilename = $filename . '_лог.txt';

				$out_path = $this->getExportPersonCardAttachPath('register');
				if (!is_dir($out_path)) mkdir($out_path);

				$logfilepath = $out_path . "/" . $logfilename;

				file_put_contents(toAnsi($logfilepath, true), implode("\n\n", $log));
				$response['loglink'] = $logfilepath;
			}
		} catch(Exception $e) {
			$this->rollbackTransaction();
			dbase_close($dbf);
			return $this->createError($e->getCode(), $e->getMessage());
		}

		dbase_close($dbf);
		$this->commitTransaction();

		return array($response);
	}

	/**
	 * Поиск или создание амбулаторной карты
	 * @param array $data
	 * @return array
	 */
	function findOrCreatePersonCardCode($data) {
		$response = array(
			'success' => true,
			'PersonCard_Code' => null,
			'Error_Msg' => null
		);

		$query = "
			select PersonAmbulatCard_Num as \"PersonAmbulatCard_Num\"
			from v_PersonAmbulatCard
			where Person_id = :Person_id
			order by PersonAmbulatCard_id desc
			limit 1
		";
		$response['PersonCard_Code'] = $this->getFirstResultFromQuery($query, $data, true);
		if ($response['PersonCard_Code'] === false) {
			return $this->createError('','Ошибка при поиске амбулаторной карты');
		}
		if (!empty($response['PersonCard_Code'])) {
			return array($response);
		}

		$resp = $this->getPersonCardCode($data);
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}
		$response['PersonCard_Code'] = $resp[0]['PersonCard_Code'];

		$params = array(
			'Person_id' => $data['Person_id'],
			'Lpu_id' => $data['Lpu_id'],
			'PersonAmbulatCard_Num' => $response['PersonCard_Code'],
			'PersonAmbulatCard_CloseCause' => null,
			'PersonAmbulatCard_begDate' => $data['PersonCard_begDate'],
			'PersonAmbulatCard_endDate' => null,
			'Server_id' => $data['Server_id'],
			'pmUser_id' => $data['pmUser_id'],
		);
		$query = "
			select
				PersonAmbulatCard_id as \"PersonAmbulatCard_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_PersonAmbulatCard_ins (
				Server_id := :Server_id,
				Person_id := :Person_id,
				PersonAmbulatCard_Num := :PersonAmbulatCard_Num,
				Lpu_id := :Lpu_id,
				PersonAmbulatCard_CloseCause := :PersonAmbulatCard_CloseCause,
				PersonAmbulatCard_begDate := :PersonAmbulatCard_begDate,
				PersonAmbulatCard_endDate := :PersonAmbulatCard_endDate,
				pmUser_id := :pmUser_id
			)
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при создании амбулаторной карты');
		}
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}

		$params = array(
			'PersonAmbulatCard_id' => $resp[0]['PersonAmbulatCard_id'],
			'AmbulatCardLocatType_id' => 1,
			'MedStaffFact_id' => null,
			'PersonAmbulatCardLocat_begDate' => $data['PersonCard_begDate'],
			'PersonAmbulatCardLocat_Desc' => null,
			'PersonAmbulatCardLocat_OtherLocat' => null,
			'Server_id' => $data['Server_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		$query = "
			select
				PersonAmbulatCardLocat_id as \"PersonAmbulatCardLocat_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_PersonAmbulatCardLocat_ins (
				Server_id := :Server_id,
				PersonAmbulatCard_id := :PersonAmbulatCard_id,
				AmbulatCardLocatType_id := :AmbulatCardLocatType_id,
				MedStaffFact_id := :MedStaffFact_id,
				PersonAmbulatCardLocat_begDate := :PersonAmbulatCardLocat_begDate,
				PersonAmbulatCardLocat_Desc := :PersonAmbulatCardLocat_Desc,
				PersonAmbulatCardLocat_OtherLocat :=:PersonAmbulatCardLocat_OtherLocat,
				pmUser_id := :pmUser_id
			)
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при создании записи движения амбулаторной карты');
		}
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}

		return array($response);
	}
	
	/**
	 * Экспорт dbf прикреплений / заявлений
	 */
	function exportAttachmentsApplicationsDBF($data){
		if(empty($data['Lpu_id']) || empty($data['PackageNumber']) || empty($data['OrgSMO_id']) || empty($data['begDate']) || empty($data['endDate'])) return false;
		$nameFille = 'M';
		
		$month=date("m", strtotime($data['endDate']));
		$year=substr(date("Y", strtotime($data['endDate'])), -2);
		
		$sql = "
			select
				l.Lpu_f003mcod as \"Lpu_f003mcod\",
				smo.Orgsmo_f002smocod as \"Orgsmo_f002smocod\"
			from v_Lpu l
				left join lateral (
					select Orgsmo_f002smocod
					from v_OrgSMO
					where OrgSMO_id = :OrgSMO_id
					limit 1
				) smo on true
			where l.Lpu_id = :Lpu_id
			limit 1";
		$cods = $this->getFirstRowFromQuery($sql, $data);
		
		$nameFille = $nameFille.$cods['Lpu_f003mcod'].'_'.$cods['Orgsmo_f002smocod'].'_'.$year.$month.$data['PackageNumber'];
		$result = array();
		
		$query = "
			with Lpu_table as (
				select
					l.Lpu_f003mcod as Lpu_f003mcod,
					smo.Orgsmo_f002smocod as Orgsmo_f002smocod
				from v_Lpu l
					left join lateral (
						select Orgsmo_f002smocod
						from v_OrgSMO
						where OrgSMO_id = :OrgSMO_id
						limit 1
					) smo on true
				where l.Lpu_id = :Lpu_id
				limit 1
			)
			SELECT DISTINCT
				--PC.PersonCard_id as \"PersonCard_id\",
				PC.Person_id AS \"ID\",
				CASE
					WHEN PCAC.PersonCard_id is not null and PCAC.Lpu_id = PC.Lpu_id and coalesce(PCAC.CardCloseCause_Code, 0) = 4 then 'И'
					WHEN PCAC.PersonCard_id is null THEN 'Р'
					WHEN PCAC.Lpu_id is not null and PS.Lpu_id != PCAC.Lpu_id THEN 'Р'
					ELSE 'И'
				END AS \"OP\",
				coalesce(PS.Person_SurName,'') AS \"FAM\",
				coalesce(PS.Person_FirName,'') AS \"IM\",
				coalesce(PS.Person_Secname,'') AS \"OT\",
				to_char(PS.Person_Birthday, 'yyyymmdd') AS \"DR\",
				PS.Sex_id AS \"W\",
				PS.Polis_Ser AS \"SPOL\",
				CASE WHEN PS.PolisType_id = 4 THEN PS.Person_edNum ELSE PS.Polis_Num END AS \"NPOL\",
				Lpu_table.Orgsmo_f002smocod AS \"Q\",
				Lpu_table.Lpu_f003mcod AS \"LPU\",
				to_char(PC.PersonCard_begDate, 'yyyymmdd') AS \"LPUDZ\",
				to_char(PC.PersonCard_updDT, 'yyyymmdd') AS \"LPUDU\",
				CASE 
					WHEN PC.PersonCardAttach_id IS NULL THEN '1'
					WHEN PC.PersonCardAttach_id IS NOT NULL
						AND (
							coalesce(PA.KLRgn_id, 0) != coalesce(LastPA.KLRgn_id, 0)
							OR coalesce(PA.KLSubRgn_id, 0) != coalesce(LastPA.KLSubRgn_id, 0)
							OR coalesce(PA.KLCity_id, 0) != coalesce(LastPA.KLCity_id, 0)
							OR coalesce(PA.KLTown_id, 0) != coalesce(LastPA.KLTown_id, 0)
							OR coalesce(PA.KLStreet_id, 0) != coalesce(LastPA.KLStreet_id, 0)
							OR coalesce(PA.Address_House, '') != coalesce(LastPA.Address_House, '')
							OR coalesce(PA.Address_Corpus, '') != coalesce(LastPA.Address_Corpus, '')
							OR coalesce(PA.Address_Flat, '') != coalesce(LastPA.Address_Flat, '')
						)
						THEN '1'
					ELSE '2'
				END \"LPUTP\",
				'0' AS \"LPUPODR\",
				LEFT(mrmp.Person_Snils, 3) || '-' || SUBSTRING(mrmp.Person_Snils, 4, 3) || '-' || SUBSTRING(mrmp.Person_Snils, 7, 3) || ' ' || RIGHT(mrmp.Person_Snils, 2) AS \"LPUSS\",
				mrmp.Person_SurName AS \"FAM_DOC\", 
				mrmp.Person_FirName AS \"IM_DOC\", 
				mrmp.Person_SecName AS OT_\"DOC\",
				left(L.Lpu_f003mcod, 6) || CASE
					when mrmp.LpuRegionType_SysNick in ('ter', 'ped', 'vop', 'feld') then
						case
							when mrmp.LpuRegionType_SysNick in ('ter') then '1'
							when mrmp.LpuRegionType_SysNick in ('ped') then '2'
							when mrmp.LpuRegionType_SysNick in ('vop') then '3'
							when mrmp.LpuRegionType_SysNick in ('feld') then '5'
							else ''
						end ||
						RIGHT('00' || oalesce(mrmp.LpuRegion_Name,''), 2)
					else ''
				end as \"LPUKOD\",
				coalesce(mrmp.code, '') AS \"LPUKAT\",
				CASE
					WHEN street_p.KLStreet_id is not null and street_p.KLAdr_Ocatd is not null THEN street_p.KLAdr_Ocatd
					WHEN town_p.KLArea_id is not null and town_p.KLAdr_Ocatd is not null THEN town_p.KLAdr_Ocatd
					WHEN city_p.KLArea_id is not null and city_p.KLAdr_Ocatd is not null THEN city_p.KLAdr_Ocatd
					WHEN srgn_p.KLArea_id is not null and srgn_p.KLAdr_Ocatd is not null THEN srgn_p.KLAdr_Ocatd
					WHEN rgn_p.KLArea_id is not null and rgn_p.KLAdr_Ocatd is not null THEN rgn_p.KLAdr_Ocatd
					WHEN country_p.KLArea_id is not null and country_p.KLAdr_Ocatd is not null THEN country_p.KLAdr_Ocatd
					ELSE ''
				END as \"OKATO\",
				coalesce(srgn_p.KLArea_Name, 'Бурятия') AS \"RNNAME\",
				coalesce(city_p.KLArea_Name, town_p.KLArea_Name) AS \"NPNAME\",
				coalesce(street_p.KLStreet_Name, '') AS \"UL\",
				coalesce(PA.Address_House, '') AS \"DOM\",
				coalesce(PA.Address_Corpus, '') AS \"KORP\",
				coalesce(PA.Address_Flat, '') AS \"KV\",
				'' AS \"LPUDT\",
				'' AS \"LPUDX\",
				'' AS \"STATUS\",
				'' AS \"ERR\",
				'' AS \"RSTOP\",
				case
					WHEN PC.PersonCardAttach_id IS NULL THEN 'A'
					ELSE 'Z'
				end AS \"file_package\"
			from
				v_PersonCard PC
				left join v_PersonState PS on PS.Person_id = PC.Person_id
				left join v_Address PA on PS.PAddress_id = PA.Address_id
				left join v_Polis PLS on PLS.Polis_id = PS.Polis_id

				left join KLArea country_p on country_p.KLArea_id = PA.KLCountry_id
				left join KLArea rgn_p on rgn_p.KLArea_id = PA.KLRgn_id
				left join KLArea srgn_p on srgn_p.KLArea_id = PA.KLSubRgn_id
				left join KLArea city_p on city_p.KLArea_id = PA.KLCity_id
				left join KLArea town_p on town_p.KLArea_id = PA.KLTown_id
				left join KLStreet street_p on street_p.KLStreet_id = PA.KLStreet_id

				left join v_Lpu L on L.Lpu_id = PC.Lpu_id
				--left join v_Document D on D.Document_id = PS.Document_id
				--left join v_DocumentType DT on DT.DocumentType_id = D.DocumentType_id
				left join v_LpuRegion LR on LR.LpuRegion_id = PC.LpuRegion_id
				left join v_LpuSection LS on LS.LpuSection_id = LR.LpuSection_id
				left join v_LpuBuilding LB on LB.LpuBuilding_id = LS.LpuBuilding_id
				--left join v_MedStaffFact MSF on MSF.MedStaffFact_id = PC.MedStaffFact_id
				--left join v_MedPersonal MP on MP.MedPersonal_id = MSF.MedPersonal_id

				left join lateral (
					SELECT PCS.PersonCardAttachStatusType_id, PersonCardStatus_setDate
					FROM PersonCardStatus PCS
					WHERE PCS.PersonCard_id = PC.PersonCard_id
					ORDER BY PersonCardStatus_setDate DESC
					LIMIT 1
				) PCS on true
				left join PersonCardAttachStatusType PCAST on PCAST.PersonCardAttachStatusType_id = PCS.PersonCardAttachStatusType_id
				left join lateral (
					SELECT PersonCard_IsAttachAuto FROM PersonCard WHERE PersonCard_id = PC.PersonCard_id
				) PCAUTO on true
				left join lateral (
					-- предыдущее прикрепление
					select
						PCA.PersonCard_id,
						PCA.PersonCard_begDate,
						PCA.PersonCard_endDate,
						ccc.CardCloseCause_Code,
						ccc.CardCloseCause_Name,
						PCA.Lpu_id
					FROM v_PersonCard_all PCA
						LEFT JOIN v_CardCloseCause ccc on ccc.CardCloseCause_id = PCA.CardCloseCause_id	
					where pc.Person_id = PCA.Person_id
						AND PCA.PersonCard_id <> pc.PersonCard_id
						and PCA.LpuAttachType_id = 1
						and PCA.PersonCard_begDate <= PC.PersonCard_begDate
					order by PCA.PersonCard_begDate desc
					limit 1
				) PCAC on true
				left join lateral (
					-- предыдущее прикрепление к текущей МО
					select PCA.PersonCard_id
					FROM v_PersonCard_all PCA
					where pc.Person_id = PCA.Person_id
						and PCA.PersonCard_id <> pc.PersonCard_id
						and PCA.LpuAttachType_id = 1
						and PCA.Lpu_id = pc.Lpu_id
					limit 1
				) PCLastInLpu on true
				left join lateral (
					SELECT
						msr.MedStaffFact_id,
						mp.Person_Snils, mp.Person_SurName, mp.Person_FirName, mp.Person_SecName, 
						CASE WHEN pk.code=1 THEN '1' WHEN pk.code=2 THEN '2' ELSE '' END AS code,
						LR.LpuRegionType_SysNick,
						LR.LpuRegion_Name
					FROM v_MedStaffRegion msr
						left join v_MedStaffFact msf on msf.MedStaffFact_id = msr.MedStaffFact_id and msr.Lpu_id = msf.Lpu_id
						left join lateral (
							select Person_Fio, Person_Snils, Person_SurName, Person_FirName, Person_SecName
							from v_MedPersonal
							where MedPersonal_id = msr.MedPersonal_id
							limit 1
						) mp on true
						inner join v_LpuRegion lr on lr.LpuRegion_id = msr.LpuRegion_id
						LEFT JOIN persis.v_PostKind pk ON pk.id = msf.PostKind_id
					WHERE (1=1)  
						AND msr.Lpu_id=PC.Lpu_id 
						AND (msr.LpuRegion_id = PC.LpuRegion_id OR msr.LpuRegion_id = PC.LpuRegion_fapid) 
						AND msr.MedStaffRegion_isMain = 2
				) mrmp on true
				left join lateral (
					select Address_id
					from v_PersonPAddress
					where Person_id = PC.Person_id
						and PersonPAddress_insDT <= PCAC.PersonCard_begDate
					order by PersonPAddress_insDT desc
					limit 1
				) LastAddrPer on true
				left join v_Address LastPA on LastPA.Address_id = LastAddrPer.Address_id
			where 
				PC.Lpu_id = :Lpu_id
				AND PC.PersonCard_endDate IS NULL
				AND PC.LpuAttachType_id = 1
				AND PLS.OrgSmo_id = :OrgSmo_id
				AND (
					(
						PC.PersonCardAttach_id is not null 
						AND PCS.PersonCardStatus_setDate BETWEEN :begDate and :endDate
						AND PCAST.PersonCardAttachStatusType_Code in (5,2) -- перекрепления со статусом «Ошибки ФЛК», «Отправлено в СМО»
					) OR (
						--внутреннее перекрепление
						PC.PersonCardAttach_id is null
						and PCAC.PersonCard_endDate = PC.PersonCard_begDate --предыдущее прикрепление закрыто датой начала нового прикрепления
						and PCAC.CardCloseCause_Code in (3,4,9) --причина открепления – Смена участка внутри МО либо Смена основного врача на участке
						and PC.PersonCard_begDate BETWEEN :begDate and :endDate -- •	Новые внутренние перекрепления
					)
				)

			UNION ALL

			SELECT DISTINCT
				PCA.Person_id AS \"ID\",
				CASE
					WHEN cast(PCA.PersonCardAttach_insDT as date) BETWEEN :begDate AND :endDate THEN 'Р'
					WHEN cast(PS.PersonState_insDT as date) BETWEEN :begDate AND :endDate THEN 'Р'
					WHEN PCLastInLpu.PersonCard_id is null THEN 'Р'
					WHEN PCCurrent.Lpu_id is not null and PCCurrent.Lpu_id != :Lpu_id THEN 'Р'
					ELSE 'И'
				END AS \"OP\",
				coalesce(PS.Person_SurName,'') AS \"FAM\",
				coalesce(PS.Person_FirName,'') AS \"IM\",
				coalesce(PS.Person_Secname,'') AS \"OT\",
				to_char(PS.Person_Birthday, 'yyyymmdd') AS \"DR\",
				PS.Sex_id AS \"W\",
				PS.Polis_Ser AS \"SPOL\",
				CASE WHEN PS.PolisType_id = 4 THEN PS.Person_edNum ELSE PS.Polis_Num END AS \"NPOL\",
				Lpu_table.Orgsmo_f002smocod AS \"Q\",
				Lpu_table.Lpu_f003mcod AS \"LPU\",
				to_char(PCA.PersonCardAttach_setDate, 'yyyymmdd') AS \"LPUDZ\",
				to_char(PCA.PersonCardAttach_setDate, 'yyyymmdd') AS \"LPUDU\",
				CASE
					WHEN
						coalesce(PA.KLRgn_id, 0) != coalesce(LastPA.KLRgn_id, 0)
						OR coalesce(PA.KLSubRgn_id, 0) != coalesce(LastPA.KLSubRgn_id, 0)
						OR coalesce(PA.KLCity_id, 0) != coalesce(LastPA.KLCity_id, 0)
						OR coalesce(PA.KLTown_id, 0) != coalesce(LastPA.KLTown_id, 0)
						OR coalesce(PA.KLStreet_id, 0) != coalesce(LastPA.KLStreet_id, 0)
						OR coalesce(PA.Address_House, '') != coalesce(LastPA.Address_House, '')
						OR coalesce(PA.Address_Corpus, '') != coalesce(LastPA.Address_Corpus, '')
						OR coalesce(PA.Address_Flat, '') != coalesce(LastPA.Address_Flat, '')
					THEN '1'
					ELSE '2'
				END AS \"LPUTP\",
				'0' AS \"LPUPODR\",
				LEFT(mrmp.Person_Snils, 3) || '-' || SUBSTRING(mrmp.Person_Snils, 4, 3) || '-' || SUBSTRING(mrmp.Person_Snils, 7, 3) || ' ' || RIGHT(mrmp.Person_Snils, 2) AS \"LPUSS\",
				mrmp.Person_SurName AS \"FAM_DOC\", 
				mrmp.Person_FirName AS \"IM_DOC\", 
				mrmp.Person_SecName AS \"OT_DOC\",
				left(L.Lpu_f003mcod, 6) || CASE
					when mrmp.LpuRegionType_SysNick in ('ter', 'ped', 'vop', 'feld') then
						case
							when mrmp.LpuRegionType_SysNick in ('ter') then '1'
							when mrmp.LpuRegionType_SysNick in ('ped') then '2'
							when mrmp.LpuRegionType_SysNick in ('vop') then '3'
							when mrmp.LpuRegionType_SysNick in ('feld') then '5'
							else ''
						end ||
						RIGHT('00' || coalesce(mrmp.LpuRegion_Name,''), 2)
					else ''
				end as \"LPUKOD\",
				coalesce(mrmp.code, '') AS \"LPUKAT\",
				CASE
					WHEN street_p.KLStreet_id is not null and street_p.KLAdr_Ocatd is not null THEN street_p.KLAdr_Ocatd
					WHEN town_p.KLArea_id is not null and town_p.KLAdr_Ocatd is not null THEN town_p.KLAdr_Ocatd
					WHEN city_p.KLArea_id is not null and city_p.KLAdr_Ocatd is not null THEN city_p.KLAdr_Ocatd
					WHEN srgn_p.KLArea_id is not null and srgn_p.KLAdr_Ocatd is not null THEN srgn_p.KLAdr_Ocatd
					WHEN rgn_p.KLArea_id is not null and rgn_p.KLAdr_Ocatd is not null THEN rgn_p.KLAdr_Ocatd
					WHEN country_p.KLArea_id is not null and country_p.KLAdr_Ocatd is not null THEN country_p.KLAdr_Ocatd
					ELSE ''
				END as \"OKATO\",
				coalesce(srgn_p.KLArea_Name, 'Бурятия') AS \"RNNAME\",
				coalesce(city_p.KLArea_Name, town_p.KLArea_Name) AS \"NPNAME\",
				coalesce(street_p.KLStreet_Name, '') AS \"UL\",
				coalesce(PA.Address_House, '') AS \"DOM\",
				coalesce(PA.Address_Corpus, '') AS \"KORP\",
				coalesce(PA.Address_Flat, '') AS \"KV\",
				'' AS \"LPUDT\",
				'' AS \"LPUDX\",
				'' AS \"STATUS\",
				'' AS \"ERR\",
				'' AS \"RSTOP\",
				'Z' AS \"file_package\"
			from
				PersonCardAttach PCA
				left join v_PersonState PS on PS.Person_id = PCA.Person_id
				left join v_Address PA on PS.PAddress_id = PA.Address_id
				left join v_Polis PLS on PLS.Polis_id = PS.Polis_id

				left join KLArea country_p on country_p.KLArea_id = PA.KLCountry_id
				left join KLArea rgn_p on rgn_p.KLArea_id = PA.KLRgn_id
				left join KLArea srgn_p on srgn_p.KLArea_id = PA.KLSubRgn_id
				left join KLArea city_p on city_p.KLArea_id = PA.KLCity_id
				left join KLArea town_p on town_p.KLArea_id = PA.KLTown_id
				left join KLStreet street_p on street_p.KLStreet_id = PA.KLStreet_id

				left join v_Lpu L on L.Lpu_id = PCA.Lpu_id
				left join v_LpuRegion LR on LR.LpuRegion_id = PCA.LpuRegion_id
				left join v_LpuSection LS on LS.LpuSection_id = LR.LpuSection_id
				left join v_LpuBuilding LB on LB.LpuBuilding_id = LS.LpuBuilding_id
				
				--left join v_MedStaffFact MSF on msf.MedStaffFact_id = PCA.MedStaffFact_id
				--LEFT JOIN v_MedPersonal MP ON MP.MedPersonal_id = MSF.MedPersonal_id
				--LEFT JOIN persis.v_PostKind pk ON pk.id = msf.PostKind_id
				left join lateral (
					SELECT
						msr.MedStaffFact_id,
						mp.Person_Snils, mp.Person_SurName, mp.Person_FirName, mp.Person_SecName, 
						CASE WHEN pk.code=1 THEN '1' WHEN pk.code=2 THEN '2' ELSE '' END AS code,
						LR.LpuRegionType_SysNick,
						LR.LpuRegion_Name
					FROM v_MedStaffRegion msr
						left join v_MedStaffFact msf on msf.MedStaffFact_id = msr.MedStaffFact_id and msr.Lpu_id = msf.Lpu_id
						left join lateral (
							select Person_Fio, Person_Snils, Person_SurName, Person_FirName, Person_SecName
							from v_MedPersonal
							where MedPersonal_id = msr.MedPersonal_id
							limit 1
						) mp on true
						inner join v_LpuRegion lr on lr.LpuRegion_id = msr.LpuRegion_id
						LEFT JOIN persis.v_PostKind pk ON pk.id = msf.PostKind_id
					WHERE (1=1)  
						AND msr.Lpu_id=PCA.Lpu_id 
						AND (msr.LpuRegion_id = PCA.LpuRegion_id OR msr.LpuRegion_id = PCA.LpuRegion_fapid) 
						AND msr.MedStaffRegion_isMain = 2
						AND pk.code IN (1,2)
				) mrmp on true

				left join lateral (
					SELECT Person_id, PersonCard_id, Lpu_id, PersonCard_begDate
					FROM v_PersonCard_all pc
					WHERE PersonCardAttach_id = PCA.PersonCardAttach_id
						AND PersonCard_endDate IS null
					ORDER BY PersonCard_begDate DESC
					LIMIT 1
				) PC on true
				left join lateral (
					select PersonCardAttachStatus_id, PersonCardAttachStatusType_id, PersonCardAttachStatus_setDate
					from v_PersonCardAttachStatus
					where PersonCardAttach_id = PCA.PersonCardAttach_id
					order by PersonCardAttachStatus_setDate desc
					limit 1
				) PCAS on true
				inner join PersonCardAttachStatusType PCAST on PCAST.PersonCardAttachStatusType_id = PCAS.PersonCardAttachStatusType_id
				left join lateral (
					SELECT PersonCard_id, Lpu_id
					FROM v_PersonCard_all pc
					WHERE Person_id = PCA.Person_id
						AND PersonCard_endDate IS null
					ORDER BY PersonCard_begDate DESC
					LIMIT 1
				) PCCurrent on true
				left join lateral (
					-- предыдущее прикрепление к текущей МО
					select PersonCard_id, Lpu_id
					FROM v_PersonCard_all
					where Person_id = PCA.Person_id
						and PersonCard_id <> PC.PersonCard_id
						and LpuAttachType_id = 1
						and Lpu_id = PCA.Lpu_id
					limit 1
				) PCLastInLpu on true
				left join lateral (
					select Address_id
					from v_PersonPAddress
					where Person_id = PC.Person_id
						and PersonPAddress_insDT <= PC.PersonCard_begDate
					order by PersonPAddress_insDT desc
					limit 1
				) LastAddrPer on true
				left join v_Address LastPA on LastPA.Address_id = LastAddrPer.Address_id
			where 
				PCA.Lpu_id = :Lpu_id
				--and coalesce(PLS.Polis_begDate, cast(dbo.tzGetDate() as date) - interval '1 day') <= cast(dbo.tzGetDate() as date)
				--and coalesce(PLS.Polis_endDate, cast(dbo.tzGetDate() as date) + interval '1 day') > cast(dbo.tzGetDate() as date)
				AND PCA.PersonCardAttach_setDate BETWEEN :begDate and :endDate
				and PLS.OrgSmo_id = :OrgSmo_id
				AND (
					(
						PCAST.PersonCardAttachStatusType_Code = 1 AND PC.PersonCard_id IS NULL
					)
					OR
					(
						PCAST.PersonCardAttachStatusType_Code in (5,2) 
						AND PCAS.PersonCardAttachStatus_setDate between :begDate and :endDate
					)
				)
		";
		//echo getDebugSQL($query, $data); die();
		$result = $this->db->query($query, $data);

		$defs = array(
			Array('ID','C',64),
			Array('OP','C',1),		//Код действия, связанного с событием прикрепления к медицинскому работнику
			Array('FAM','C',40),	//Фамилия 
			Array('IM','C',40),		//Имя
			Array('OT','C',40),		//Отчество
			Array('DR','D'),		//YYYYMMDD
			Array('W','C',1),		//пол
			Array('SPOL','C',20),	//серия поиса
			Array('NPOL','C',20),	//номер полиса
			Array('Q','C',5),		//Реестровый номер СМО
			Array('LPU','C',6),		//Реестровый номер МО
			Array('LPUDZ','D'),		//Дата подачи заявления при регистрации события
			Array('LPUDU','D'),		//Дата исправления информации о событии
			Array('LPUTP','C',1),	//Тип прикрепления (1,2)
			Array('LPUPODR','C',64),//Код подразделения
			Array('LPUSS','C',14),	//СНИЛС медицинского работника (XXX-XXX-XXXXX)
			Array('FAM_DOC','C',40),//Фамилия медицинского работника 
			Array('IM_DOC','C',40),	//Имя медицинского работника 
			Array('OT_DOC','C',40),	//Отчество медицинского работника 
			Array('LPUKOD','C',64),	//Номер участка, к которому прикреплен застрахованный
			Array('LPUKAT','C',1),	//Категория медработника (1,2)
			Array('OKATO','C',11),	//Код места жительства по справочнику ОКАТО
			Array('RNNAME','C',80),	//Район места регистрации
			Array('NPNAME','C',80),	//Наименование населенного пункта
			Array('UL','C',80),		//Наименование улицы 
			Array('DOM','C',7),		//Номер дома места регистрации
			Array('KORP','C',6),	//Номер корпуса места регистрации
			Array('KV','C',6),		//Номер квартиры места регистрации
			Array('LPUDT','D'),		//Дата прикрепления (Не заполняется)
			Array('LPUDX','D'),		//Дата открепления (Не заполняется)
			Array('STATUS','C',1),	//Ответ от СМО (Не заполняется)
			Array('ERR','C',40),	//Причина отклонения от прикрепления (Не заполняется)
			Array('RSTOP','C',1),	//Причина открепления (снятия с учета) (Не заполняется)
		);
		
		return array(
			'result' => $result,
			'nameFille' => $nameFille,
			'attachesFields' => $defs
		);
	}

	/**
	 * Проверки при Импорте dbf ошибок ФЛК по ЗЛ
	 */
	function verificationImportErrors_FLKforZL_DBF($handler, $data, $nameLogFile){
		$result = array('success' => true, 'Err_msg' => false, 'errArray' => array());
		$err = false;
		$errorsArr = array(
			'OP' => array(
					'1.1' => 'Поле не найдено или имеет неверный тип',
					'1.2' => 'Поле содержит неверное значение (Допустимо: У, И, Р)'
				),
			'FAM' => array(
					'2.1' => 'Поле не найдено или имеет неверный тип',
					'2.2' => 'Поле содержит недопустимые знаки или сочетания знаков'
				),
			'IM' =>	array(
					'3.1' => 'Поле не найдено или имеет неверный тип',
					'3.2' => 'Поле содержит недопустимые знаки или сочетания знаков'
				),
			'OT' => array(
					'4.1' => 'Поле не найдено или имеет неверный тип',
					'4.2' => 'Поле содержит недопустимые знаки или сочетания знаков'
				),
			'DR' =>	array(
					'5.1' => 'Поле не найдено или имеет неверный тип',
					'5.2' => 'Дата рождения больше даты выгрузки файла'
				),
			'W' => array(
					'6.1' => 'Поле не найдено или имеет неверный тип',
					'6.2' => 'Содержит недопустимое значение (должно быть 1 или 2)'
				),
			'SPOL' =>array(
					'7.1' => 'Поле не найдено или имеет неверный тип',
					'7.2' => 'Поле имеет неверный формат (Может содержать русские,  латинские буквы, тире, цифры)'
				),
			'NPOL' => array(
					'8.1' => 'Поле не найдено или имеет неверный тип',
					'8.2' => 'Поле должно содержать только цифры',
					'8.3' => 'Поле имеет неверный формат: Если SPOL - не пусто, то длина может быть любой. Если SPOL - пусто, то поле должно содержать либо 9, либо 16 символов.'
				),
			'Q' => array(
					'9.1' => 'Поле не найдено или имеет неверный тип',
					'9.2' => 'Указан неверный реестровый номер СМО (несоответствие коду СМО в названии файла)'
				),
			'LPU' => array(
					'10.1' => 'Поле не найдено или имеет неверный тип',
					'10.2' => 'Не указан реестровый номер МО',
					'10.3' => 'Указан неверный реестровый номер МО (несоответствие коду МО в названии файла)'
				),
			'LPUDZ' => array(
					'11.1' => 'Поле не найдено или имеет неверный тип',
					'11.2' => 'Дата подачи заявления больше даты выгрузки файла'
				),
			'LPUDU' =>	array(
					'12.1' => 'Поле не найдено или имеет неверный тип',
					'12.2' => 'Дата не попадает в допустимый период для события "И"'
				),
			'LPUTP'	=> array(
					'13.1' => 'Поле не найдено или имеет неверный тип',
					'13.2' => 'Поле содержит неверное значение (Допустимо: 1, 2)'
				),
			'LPUPODR' => array(
					'14.1' => 'Поле не найдено или имеет неверный тип',
					'14.2' => 'Поле не может быть пустым '
				),
			'LPUSS' => array(
					'15.1' => 'Поле не найдено или имеет неверный тип',
					'15.2' => 'Не указан СНИЛС врача',
					'15.3' => 'Поле содержит недопустимые символы',
					'15.4' => 'Указана неверная длина',
					'15.5' => 'Контрольное число (две последние цифры) вычислены с ошибкой. Поле должно соответствовать формату ххх ххх ххх хх, где х - цифра, пробелы или тире между числами не контролируются.'
				),
			'FAM_DOC' => array(
					'16.1' => 'Поле не найдено или имеет неверный тип',
					'16.2' => 'Поле содержит недопустимые знаки или сочетания знаков'
				),
			'IM_DOC' => array(
					'17.1' => 'Поле не найдено или имеет неверный тип',
					'17.2' => 'Поле содержит недопустимые знаки или сочетания знаков'
				),
			'OT_DOC' =>	array(
					'18.1' => 'Поле не найдено или имеет неверный тип',
					'18.2' => 'Поле содержит недопустимые знаки или сочетания знаков'
				),
			'LPUKOD' =>	array(
					'19.1' => 'Поле не найдено или имеет неверный тип',
					'19.2' => 'Поле не может быть пустым (Может содержать буквы, цифры)'
				),
			'LPUKAT' => array(
					'20.1' => 'Поле не найдено или имеет неверный тип',
					'20.2' => 'Не указана категория врача',
					'20.3' => 'Указано недопустимое значение (Допустимо: 1, 2)'
				),
			'OKATO'	=> array(
					'21.1' => 'Поле не найдено или имеет неверный тип',
					'21.2' => 'Поле не может быть пустым',
					'21.3' => 'Поле имеет неверный формат (длинна должна быть 11 символов, поле может содержать только цифры)'
				),
			'RNNAME' =>	array(
					'22.1' => 'Поле не найдено или имеет неверный тип',
					'22.2' => 'Поле не может быть пустым',
				),
			'NPNAME' =>	array(
					'23.1' => 'Поле не найдено или имеет неверный тип',
					'23.2' => 'Поле не может быть пустым'
				),
			'UL' =>	array(
					'24.1' => 'Поле не найдено или имеет неверный тип',
					'24.2' => 'Поле не может быть пустым',
				),
			'DOM' => array(
					'25.1' => 'Поле не найдено или имеет неверный тип',
					'25.2' => 'Поле не может быть пустым'
				),
			'KORP' => array(
					'26.1'	=> 'Поле не найдено или имеет неверный тип'
				),
			'KV' =>	array(
					'27.1' => 'Поле не найдено или имеет неверный тип'
				)
		);
		
		$record_count = dbase_numrecords($handler);
		for($i=1; $i<=$record_count; $i++) {
			$record = dbase_get_record_with_names($handler, $i);
			array_walk($record, 'to_charFromWin866ToUtf8');
			$record = array_map('trim',$record);
			
			//•	хотя бы для одной записи файла заполнено поле 31. ERR Ошибка ФЛК, и заполнено поле 0. ID.
			//•	не заполнены поля (либо этих полей вообще нет в структуре файла) 28. LPUDT, 29. LPUDX, 30. STATUS, 32. RSTOP
			if(
				(empty($record['ERR']) && empty($record['ID']))
				||
				(!empty($record['RSTOP']) || !empty($record['STATUS']) || !empty($record['LPUDX']) || !empty($record['LPUDT']))
			){
				$result['success'] = false;
				$result['Err_msg'] = 'Структура загружаемого файла не соответствует файлу загрузки ошибок ФЛК';
				break;
			}
			
			if(!empty($record['ERR']) && $result['success']){
				$error = $record['ERR'];
				foreach ($errorsArr as $key => $value) {
					if (array_key_exists($record['ERR'], $value)){
						$error = $key.' - '.$value[$record['ERR']];
						break 1;
					}
				}
				$fam = (!empty($record['FAM'])) ? $record['FAM'] : '';
				$im = (!empty($record['IM'])) ? $record['IM'] : '';
				$ot = (!empty($record['OT'])) ? $record['OT'] : '';
				$dr = (!empty($record['DR'])) ? $record['DR'] : '';
				$spol = (!empty($record['SPOL'])) ? $record['SPOL'] : '';
				$npol = (!empty($record['NPOL'])) ? $record['NPOL'] : '';				
				$lpukat = (!empty($record['LPUKAT'])) ? $record['LPUKAT'] : '';
				$lpukod = (!empty($record['LPUKOD'])) ? $record['LPUKOD'] : '';
				$fam_doc = (!empty($record['FAM_DOC'])) ? $record['FAM_DOC'] : '';
				$im_doc = (!empty($record['IM_DOC'])) ? $record['IM_DOC'] : '';
				$ot_doc = (!empty($record['OT_DOC'])) ? $record['OT_DOC'] : '';
				$lpuss = (!empty($record['LPUSS'])) ? $record['LPUSS'] : '';
				$lpudt = (!empty($record['LPUDT'])) ? $record['LPUDT'] : '';
				$lpudx = (!empty($record['LPUDX'])) ? $record['LPUDX'] : '';
				
				$error = array(
					'ФИО: '.$fam.' '.$im.' '.$ot,
					'ДР: '.$dr,
					'ПОЛИС: '.$spol.' '.$npol,
					'Заявление/прикрепление: '.$lpukat.', '.$lpukod.', '.$fam_doc.' '.$im_doc.' '.$ot_doc.' '.$lpuss,
					'дата прикрепления: '.$lpudt,
					'дата открепления: '.$lpudx,
					'Результат: '.$error
				);
				$result['errArray'][] = implode("\r\n",$error);
			}
		}
		if($result['success']){
			if($nameLogFile) $fileLog = $this->recordLogFile($nameLogFile, $result['errArray'], '');
			if($fileLog) $result['fileLog'] = $fileLog;
		}
		
		return $result;
	}
	
	/**
	 * Проверки при Импорте dbf ответа от СМО по ЗЛ
	 */
	function verificationImportResponseFrom_SMOforPL_DBF($handler, $data, $nameLogFile){
		$result = array('success' => true, 'Err_msg' => false, 'errArray' => array());
		$recordArr = array();
		$errLog = array();
		$record_count = dbase_numrecords($handler);
		for($i=1; $i<=$record_count; $i++) {
			$record = dbase_get_record_with_names($handler, $i);
			array_walk($record, 'to_charFromWin866ToUtf8');
			$record = array_map('trim',$record);
			
			if(empty($record['ID']) || empty($record['STATUS'])){
				$result['success'] = false;
				$result['Err_msg'] = 'Структура загружаемого файла не соответствует структуре файла ответа от СМО';
				break;
			}
			if(!empty($record['LPUKAT'])) {
				$recordArr[$record['ID']][$record['LPUKAT']] = $record;
			}
		}
		
		//id статус одобрено PersonCardAttachStatusType_Code = 3
		//$personCardAttachStatusType_id = $this->getByCodePersonCardAttachStatusTypeID(3);
		try {
			if( $result['success'] && count($recordArr)>0 ){
				$this->beginTransaction();
				foreach ($recordArr as $key => $value) {
					if(!empty($value[2]) && empty($value[1])) {
						//Если передана только запись по ФАП участку, то прикрепление не создается с записью ошибки 
						//в лог «В файле импорта отсутствуют данные по прикреплению к основному участку».
						$errLog[] = $this->strLogDBF($value[2], 'В файле импорта отсутствуют данные по прикреплению к основному участку');
						continue;
					}
					if(empty($value[1])) continue;				
					$line = $value[1];
					if(!empty($value[2]) && !empty($value[2]['LPUKOD'])){
						//fap участок LPUKOD
						$line['LPUKOD_Fap'] = $value[2]['LPUKOD'];
					}
					if(empty($line['LPUSS'])){
						//отсутствует запись о медработнике LPUSS
						$errLog[] = $this->strLogDBF($line, 'отсутствует запись о медработнике LPUSS');
						continue;
					}
					//ищем человека
					$person_id = $this->searchPersonDBF($line);
					if(!$person_id) {
						$errLog[] = $this->strLogDBF($line, 'человек не найден');
						//break;
						continue;
					}
					
					switch ($line['STATUS']) {
						case 1:
							//id статус одобрено PersonCardAttachStatusType_Code = 3
							$personCardAttachStatusType_id = $this->getByCodePersonCardAttachStatusTypeID(3);
							// Ищем запись о действующем прикреплении (с открытой датой начала).						
							$validAttachment = $this->searchPersonCardDBF(array(
								'Person_id' => $person_id,
								'MedPersonal_Snils' => $line['LPUSS'],
								'LPUKOD' => $line['LPUKOD'],
								'LpuAttachType_id' => 1,	//тип прикрепления
								'plot_type' => 'basis'		//тип участка
							));
							if(count($validAttachment)>0 && !empty($validAttachment[0]['PersonCard_id'])){
								//если нашли прикрепление
								//	на прикреплении устанавливается статус «Одобрено»
								$res = $this->setPersonCardStatusDBF(array(
									'PersonCard_id'=>$validAttachment[0]['PersonCard_id'], 
									'PersonCardAttachStatusType_id' => $personCardAttachStatusType_id, 
									'pmUser_id' => $data['pmUser_id']
								));
								if(!$res){
									$errLog[] = $this->strLogDBF($line, 'ошибка при установке на прикреплении статуса Одобрено');
									continue 2;
								}
								$errLog[] = $this->strLogDBF($line, 'Прикреплению установлен статус Одобрено');
							}else{
								//если не нашли прикрепление, то ищем заявление
								$personCardAttach_setDate = false;
								if($line['OP'] == 'P' && $line['LPUDZ']){
									$personCardAttach_setDate = $line['LPUDZ'];
								}else if($line['OP'] == 'И' && $line['LPUDU']){
									$personCardAttach_setDate = $line['LPUDU'];
								}
								$validAttachment = $this->searchPersonAttachDBF(array(
									'Person_id' => $person_id,
									'PersonCardAttach_setDate' => $personCardAttach_setDate,
									'MedPersonal_Snils' => $line['LPUSS'],
									'LPUKOD' => $line['LPUKOD']
								));
								if(count($validAttachment)>0){
									//	Если нашли заявление, то заявлению устанавливаем статус «Одобрено», по заявлению создается прикрепление
									$res = $this->changePersonCardAttachStatus(array(
										'PersonCardAttach_id' => $validAttachment[0]['PersonCardAttach_id'],
										'PersonCardAttachStatusType_id' => $personCardAttachStatusType_id,
										'pmUser_id' => $data['pmUser_id']
									));
									if($res['success'] && !$res['string']){
										//создаем прикрепление
										//PersonCardAttach_id
										$res2 = $this->addPersonCardByAttach(array(
											'PersonCardAttach_id' => $validAttachment[0]['PersonCardAttach_id'],
											'pmUser_id' => $data['pmUser_id'],
											'Server_id' => $data['Server_id']
										));
										if(!empty($res2[0]['Error_Code'])){
											$errLog[] = $this->strLogDBF($line, 'ошибка при создании прикрепления по заявлению. '.$res2[0]['Error_Msg']);
											continue 2;
										}
									}else{
										$errLog[] = $this->strLogDBF($line, 'ошибка при установке заявлению статуса Одобрено. '.$res['string']);
										continue 2;
									}
									$errLog[] = $this->strLogDBF($line, 'Заявлению установлен статус Одобрено. Создано прикрепление без заявления');
								}else{
									//	Если заявление не удалось найти
									// Ищем открытое прикрепление на человеке, но без совпадения по участку и врачу 
									$validAttachment = $this->searchPersonCardDBF(array(
										'Person_id' => $person_id,
										'LpuAttachType_id' => 1,	//тип прикрепления
									));
									if(count($validAttachment)>0){
										$PersonCard = $validAttachment[0];
										
										$Lpu_id = $this->getLpuIdByAppointment_f003mcod($line);
										if(!$Lpu_id){
											//не найдена МО
											$errLog[] = $this->strLogDBF($line, 'не найдена МО');
											continue 2;
										}
										//сначала амбулаторная карта
										$resp = $this->findOrCreatePersonCardCode(array_merge($PersonCard, array(
											'Server_id' => $data['Server_id'],
											'pmUser_id' => $data['pmUser_id']
										)));
										if (!$this->isSuccessful($resp)) {
											//throw new Exception($resp[0]['Error_Msg']);
											$errLog[] = $this->strLogDBF($line, $resp[0]['Error_Msg']);
											continue 2;
										}
										//прикрепление без заявления
										if(!empty($resp[0]['PersonCard_Code'])){
											$LpuRegion = $this->getLpuRegionID_LPUKOD($line);
											if(empty($LpuRegion['LpuRegion_id']) || empty($LpuRegion['LpuRegionType_id'])){
												// не определен участок прикрепления
												$errLog[] = $this->strLogDBF($line, 'не определен участок прикрепления');
												continue 2;
											}
											$LpuRegionFap = (!empty($line['LPUKOD_Fap'])) ? $this->getLpuRegionID_LPUKOD(array('LPUKOD'=>$line['LPUKOD_Fap'])) : null;
											//создаем прикрепление
											$PersonCardResp = $this->savePersonCard(array(
												'action' => 'add',
												'PersonCard_id' => null,
												'Person_id' => $person_id,
												'Lpu_id' => $Lpu_id,
												'PersonCard_Code' => $resp[0]['PersonCard_Code'],
												'LpuAttachType_id' => 1,				// основное прикрепление
												'PersonCard_begDate' => $line['LPUDT'], // дата прикрепления
												'PersonCard_endDate' => null,
												'PersonCardAttach_id' => null,
												'LpuRegion_Fapid' => $LpuRegionFap['LpuRegion_id'],
												'LpuRegion_id' => $LpuRegion['LpuRegion_id'],
												'LpuRegionType_id' => $LpuRegion['LpuRegionType_id'],
												'Server_id' => $data['Server_id'],
												'pmUser_id' => $data['pmUser_id']
											));
											if(!empty($PersonCardResp[0]['Error_Msg'])){
												$errLog[] = $this->strLogDBF($line, 'ошибка при создании прикрепления. '.$PersonCardResp[0]['Error_Msg']);
												continue 2;
											}
											//	созданному прикреплению устанавливаем статус «Одобрено».
											$res = $this->setPersonCardStatusDBF(array(
												'PersonCard_id'=>$PersonCardResp[0]['PersonCard_id'], 
												'PersonCardAttachStatusType_id' => $personCardAttachStatusType_id, 
												'pmUser_id' => $data['pmUser_id']
											));
											$errLog[] = $this->strLogDBF($line, 'Создано прикрепление без заявления');
											continue 2;
										}
									}
								}
							}
							break;
						case 2:
							//id статус Ошибки ФЛК PersonCardAttachStatusType_Code = 5
							$personCardAttachStatusType_id = $this->getByCodePersonCardAttachStatusTypeID(5);
							// Ищем запись о действующем прикреплении (с открытой датой начала).						
							$validAttachment = $this->searchPersonCardDBF(array(
								'Person_id' => $person_id,
								'MedPersonal_Snils' => $line['LPUSS'],
								'LPUKOD' => $line['LPUKOD'],
								'LpuAttachType_id' => 1,	//тип прикрепления
							));
							if(count($validAttachment)>0 && !empty($validAttachment[0]['PersonCard_id'])){
								//если нашли прикрепление
								//на прикреплении устанавливается статус Ошибки ФЛК
								$res = $this->setPersonCardStatusDBF(array(
									'PersonCard_id'=>$validAttachment[0]['PersonCard_id'], 
									'PersonCardAttachStatusType_id' => $personCardAttachStatusType_id, 
									'pmUser_id' => $data['pmUser_id']
								));
								if(!$res){
									$errLog[] = $this->strLogDBF($line, 'ошибка при установке на прикреплении статуса Ошибки ФЛК');
									continue 2;
								}
								$errLog[] = $this->strLogDBF($line, $this->getErrorFLK($line));
							}else{
								//если не нашли прикрепление, то ищем заявление
								$personCardAttach_setDate = false;
								if($line['OP'] == 'P' && $line['LPUDZ']){
									$personCardAttach_setDate = $line['LPUDZ'];
								}else if($line['OP'] == 'И' && $line['LPUDU']){
									$personCardAttach_setDate = $line['LPUDU'];
								}
								$validAttachment = $this->searchPersonAttachDBF(array(
									'Person_id' => $person_id,
									'PersonCardAttach_setDate' => $personCardAttach_setDate,
									'MedPersonal_Snils' => $line['LPUSS'],
									'LPUKOD' => $line['LPUKOD']
								));
								if(count($validAttachment)>0){
									//Если нашли заявление, то заявлению устанавливаем статус Ошибки ФЛК, по заявлению создается прикрепление
									$res = $this->changePersonCardAttachStatus(array(
										'PersonCardAttach_id' => $validAttachment[0]['PersonCardAttach_id'],
										'PersonCardAttachStatusType_id' => $personCardAttachStatusType_id,
										'pmUser_id' => $data['pmUser_id']
									));
									$errLog[] = $this->strLogDBF($line, 'Причина отклонения от прикрепления : '.$this->getErrorFLK($line));
								}else{
									//если не нашли и заявление
									$errLog[] = $this->strLogDBF($line, 'Не удалось найти заявление/прикрепление');
								}
							}
							continue 2;
							break;
						case 3:
							// Ищем запись о действующем прикреплении (с открытой датой начала).						
							$validAttachment = $this->searchPersonCardDBF(array(
								'Person_id' => $person_id,
								'MedPersonal_Snils' => $line['LPUSS'],
								'LPUKOD' => $line['LPUKOD'],
								'LpuAttachType_id' => 1,	//тип прикрепления
							));
							if(count($validAttachment)>0 && !empty($validAttachment[0]['PersonCard_id'])){
								//если нашли прикрепление
								//закрываем прикрепление
								$params = array(
									'PersonCard_id'=>$validAttachment[0]['PersonCard_id'], 
									'Server_id' => $data['Server_id'],
									'pmUser_id' => $data['pmUser_id']
								);
								$res = $this->closePersonCard_importDBF($params, $line);
								if($res['success'] && !empty($res['PersonCard_id'])){
									$errLog[] = $this->strLogDBF($line, 'Закрыли прикрепление');
								}else{
									$errLog[] = $this->strLogDBF($line, $res['Error_Msg']);
								}
								continue 2;
							}else{
								//если не нашли прикрепление, то ищем заявление
								$personCardAttach_setDate = false;
								if($line['OP'] == 'P' && $line['LPUDZ']){
									$personCardAttach_setDate = $line['LPUDZ'];
								}else if($line['OP'] == 'И' && $line['LPUDU']){
									$personCardAttach_setDate = $line['LPUDU'];
								}
								$validAttachment = $this->searchPersonAttachDBF(array(
									'Person_id' => $person_id,
									'PersonCardAttach_setDate' => $personCardAttach_setDate,
									'MedPersonal_Snils' => $line['LPUSS'],
									'LPUKOD' => $line['LPUKOD']
								));
								if(count($validAttachment)>0){
									//Если нашли заявление, то устанавливаем на заявление статус отказано
									//id статус Ошибки ФЛК PersonCardAttachStatusType_Code = 4
									$personCardAttachStatusType_id = $this->getByCodePersonCardAttachStatusTypeID(4);
									$res = $this->changePersonCardAttachStatus(array(
										'PersonCardAttach_id' => $validAttachment[0]['PersonCardAttach_id'],
										'PersonCardAttachStatusType_id' => $personCardAttachStatusType_id,
										'pmUser_id' => $data['pmUser_id']
									));
									$errLog[] = $this->strLogDBF($line, 'Установили статус Отказ');
								}else{
									//если не нашли и заявление
									$errLog[] = $this->strLogDBF($line, 'Не удалось найти заявление');
								}
							}
							continue 2;
							break;
						default:
							break;
					}
				}
			}
		} catch(Exception $e) {
			$this->rollbackTransaction();			
			return $false;
		}
		if($nameLogFile && count($errLog)>0) {
			$fileLog = $this->recordLogFile($nameLogFile, $errLog, 'test');
			if(!empty($fileLog)) $result['fileLog'] = $fileLog;
		}
		
		$this->commitTransaction();
		return $result;
	}
	
	/**
	 * Проверки при Импорте территориальных прикреплений/ откреплений
	 */
	function verificationImportOfTerritorialAttachmentsDetachmentsDBF($handler, $data, $nameLogFile){
		$result = array('success' => true, 'Err_msg' => null, 'errArray' => array());
		$recordArr = array();
		$errLog = array();
		$record_count = dbase_numrecords($handler);
		
		for($i=1; $i<=$record_count; $i++) {
			$record = dbase_get_record_with_names($handler, $i);
			array_walk($record, 'to_charFromWin866ToUtf8');
			$record = array_map('trim',$record);
			
			if(!empty($record['LPUKAT']) && !empty($record['OP']) && in_array($record['OP'], array('И', 'Р'))) $recordArr[$record['ID']][$record['LPUKAT']] = $record;
		}
		
		try {
			$this->beginTransaction();
			$this->load->model("Person_model", 'Person_model');
			if( $result['success'] && count($recordArr)>0 ){
				foreach ($recordArr as $key => $value) {
					if(!empty($value[2]) && empty($value[1])) {
						$errLog[] = $this->strLogDBF($value[2], 'В файле импорта отсутствуют данные по прикреплению к основному участку');
						continue;
					}
					
					if(empty($value[1])) continue;
					$line = $value[1];
					if(!empty($value[2]) && !empty($value[2]['LPUKOD'])) $line['LPUKOD_Fap'] = $value[2]['LPUKOD'];
					if(empty($line['LPUSS'])){
						//отсутствует запись о медработнике LPUSS
						$errLog[] = $this->strLogDBF($line, 'отсутствует запись о медработнике LPUSS');
						continue;
					}
					//ищем человека
					$person_id = $this->searchPersonDBF($line);
					
					if($person_id){
						// Ищем запись о действующем прикреплении (с открытой датой начала).						
						$validAttachment = $this->searchPersonCardDBF(array(
							'Person_id' => $person_id,
							'MedPersonal_Snils' => $line['LPUSS'],
							'LPUKOD' => $line['LPUKOD'],
							'LpuAttachType_id' => 1,	//тип прикрепления
						));	
						if(count($validAttachment)>0 && !empty($validAttachment[0]['PersonCard_id'])){
							// Если нашли прикрепление,	то дополнительных действий не производится.
							$errLog[] = $this->strLogDBF($line, 'Найдено действующее прикрепление. Дополнительные действия не производится');
							continue;
						}
						
						//ищем заявление о прикреплении
						$personCardAttach_setDate = false;
						if($line['OP'] == 'P' && $line['LPUDZ']){
							$personCardAttach_setDate = $line['LPUDZ'];
						}else if($line['OP'] == 'И' && $line['LPUDU']){
							$personCardAttach_setDate = $line['LPUDU'];
						}
						$validAttachment = $this->searchPersonAttachDBF(array(
							'Person_id' => $person_id,
							'PersonCardAttach_setDate' => $personCardAttach_setDate,
							'MedPersonal_Snils' => $line['LPUSS'],
							'LPUKOD' => $line['LPUKOD']
						));
						if(count($validAttachment)>0 && !empty($validAttachment[0]['PersonCardAttach_id'])){
							//Если нашли заявление, то заявлению устанавливаем статус «Одобрено», по заявлению создается прикрепление
							//id статус одобрено PersonCardAttachStatusType_Code = 3
							$personCardAttachStatusType_id = $this->getByCodePersonCardAttachStatusTypeID(3);
							$res = $this->changePersonCardAttachStatus(array(
								'PersonCardAttach_id' => $validAttachment[0]['PersonCardAttach_id'],
								'PersonCardAttachStatusType_id' => $personCardAttachStatusType_id,
								'pmUser_id' => $data['pmUser_id']
							));
							if($res['success'] && !$res['string']){
								//по заявлению создаем прикрепление
								$res = $this->addPersonCardByAttach(array(
									'PersonCardAttach_id' => $validAttachment[0]['PersonCardAttach_id'],
									'pmUser_id' => $data['pmUser_id'],
									'Server_id' => $data['Server_id']
								));
								if(!empty($res[0]['Error_Code'])){
									$errLog[] = $this->strLogDBF($line, 'ошибка при создании прикрепления по заявлению. '.$res[0]['Error_Msg']);
									continue;
								}else{
									$errLog[] = $this->strLogDBF($line, 'Заявлению установлен статус Одобрено. Создано прикрепление без заявления');
									continue;
								}
							}else{
								$errLog[] = $this->strLogDBF($line, 'ошибка при установке заявлению статуса Одобрено. '.$res['string']);
								continue;
							}
						}else{
							//не нашли заявление
							//создаем прикрепление без заявления
							$Lpu_id = $this->getLpuIdByAppointment_f003mcod($line);
							if(!$Lpu_id){
								//не найдена МО
								$errLog[] = $this->strLogDBF($line, 'не найдена МО');
								continue;
							}
							//сначала амбулаторная карта
							$resp = $this->findOrCreatePersonCardCode(array(
								'Person_id' => $person_id,
								'PersonCard_begDate' => date("Y-m-d"),
								'Lpu_id' => $Lpu_id,
								'Server_id' => $data['Server_id'],
								'pmUser_id' => $data['pmUser_id']
							));
							if (!$this->isSuccessful($resp)) {
								$errLog[] = $this->strLogDBF($line, $resp[0]['Error_Msg']);
								continue;
							}
							if(empty($resp[0]['PersonCard_Code'])){
								$errLog[] = $this->strLogDBF($line, 'ошибка при получении амбулаторной карты');
								continue;
							}
							$LpuRegion = $this->getLpuRegionID_LPUKOD($line);
							if(empty($LpuRegion['LpuRegion_id']) || empty($LpuRegion['LpuRegionType_id'])){
								// не определен участок прикрепления
								$errLog[] = $this->strLogDBF($line, 'не определен участок прикрепления');
								continue;
							}
							$LpuRegionFap = (!empty($line['LPUKOD_Fap'])) ? $this->getLpuRegionID_LPUKOD(array('LPUKOD'=>$line['LPUKOD_Fap'])) : null;
							//создаем прикрепление
							$PersonCardResp = $this->savePersonCard(array(
								'action' => 'add',
								'PersonCard_id' => null,
								'Person_id' => $person_id,
								'Lpu_id' => $Lpu_id,
								'PersonCard_Code' => $resp[0]['PersonCard_Code'],
								'LpuAttachType_id' => 1,				// основное прикрепление
								'PersonCard_begDate' => $line['LPUDT'], // дата прикрепления
								'PersonCard_endDate' => null,
								'PersonCardAttach_id' => null,
								'LpuRegion_Fapid' => $LpuRegionFap['LpuRegion_id'],
								'LpuRegion_id' => $LpuRegion['LpuRegion_id'],
								'LpuRegionType_id' => $LpuRegion['LpuRegionType_id'],
								'Server_id' => $data['Server_id'],
								'pmUser_id' => $data['pmUser_id']
							));
							if(!empty($PersonCardResp[0]['Error_Msg'])){
								$errLog[] = $this->strLogDBF($line, 'ошибка при создании прикрепления. '.$PersonCardResp[0]['Error_Msg']);
								continue;
							}
							$errLog[] = $this->strLogDBF($line, 'Создано прикрепление без заявления');
							continue;
						}
					}else{
						//не нашли человека
						$Lpu_id = $this->getLpuIdByAppointment_f003mcod($line);
						if(!$Lpu_id){
							$errLog[] = $this->strLogDBF($line, 'не найдена МО');
							//throw new Exception('не найдена МО');
							continue;
						}
						//Создаем нового человека
						$res = $this->createNewPerson_importDBF(array_merge($line, $data));
						if(!empty($res['Person_id'])){
							$person_id = $res['Person_id'];
							//сначала амбулаторная карта
							$resp = $this->findOrCreatePersonCardCode(array(
								'Person_id' => $res['Person_id'],
								'PersonCard_begDate' => date("Y-m-d"),
								'Lpu_id' => $Lpu_id,
								'Server_id' => $data['Server_id'],
								'pmUser_id' => $data['pmUser_id']
							));
							if (!$this->isSuccessful($resp)) {
								$errLog[] = $this->strLogDBF($line, $resp[0]['Error_Msg']);
								continue;
							}
							//прикрепление без заявления
							if(empty($resp[0]['PersonCard_Code'])){
								$errLog[] = $this->strLogDBF($line, 'ошибка при создании амбулаторной карты');
								continue;
							}
							$LpuRegion = $this->getLpuRegionID_LPUKOD($line);
							if(empty($LpuRegion['LpuRegion_id']) || empty($LpuRegion['LpuRegionType_id'])){
								// не определен участок прикрепления
								$errLog[] = $this->strLogDBF($line, 'не определен участок прикрепления');
								continue;
							}
							$LpuRegionFap = (!empty($line['LPUKOD_Fap'])) ? $this->getLpuRegionID_LPUKOD(array('LPUKOD'=>$line['LPUKOD_Fap'])) : null;
							//создаем прикрепление
							$PersonCardResp = $this->savePersonCard(array(
								'action' => 'add',
								'PersonCard_id' => null,
								'Person_id' => $person_id,
								'Lpu_id' => $Lpu_id,
								'PersonCard_Code' => $resp[0]['PersonCard_Code'],
								'LpuAttachType_id' => 1,				// основное прикрепление
								'PersonCard_begDate' => $line['LPUDT'], // дата прикрепления
								'PersonCard_endDate' => null,
								'PersonCardAttach_id' => null,
								'LpuRegion_Fapid' => $LpuRegionFap['LpuRegion_id'],
								'LpuRegion_id' => $LpuRegion['LpuRegion_id'],
								'LpuRegionType_id' => $LpuRegion['LpuRegionType_id'],
								'Server_id' => $data['Server_id'],
								'pmUser_id' => $data['pmUser_id']
							));
							if(!empty($PersonCardResp[0]['Error_Msg'])){
								$errLog[] = $this->strLogDBF($line, 'ошибка при создании прикрепления. '.$PersonCardResp[0]['Error_Msg']);
								throw new Exception('ошибка при создании прикрепления. '.$PersonCardResp[0]['Error_Msg']);
							}
							$errLog[] = $this->strLogDBF($line, 'Человек добавлен в систему. Создано прикрепление без заявления');
							continue;
						}else{
							$errLog[] = $this->strLogDBF($line, 'Ошибка при добавления Человека в систему');
							continue;
						}
					}
				}
			}
			
			$this->commitTransaction();
			if($nameLogFile) $fileLog = $this->recordLogFile($nameLogFile, $errLog, 'test');
			if($fileLog) $result['fileLog'] = $fileLog;
			return $result;
		} catch (Exception $ex) {
			$this->rollbackTransaction();
			if($nameLogFile) $fileLog = $this->recordLogFile($nameLogFile, $errLog, 'test');
			if($fileLog) $result['fileLog'] = $fileLog;
			$result['success'] = false;
			return $result;
		}
		
	}
	
	/**
	 * Поиск прикрепления для импорта DBF
	 */
	function searchPersonCardDBF($data){
		$result = array();
		$queryParams = array();
		$where = '';
		if(!empty($data['Person_id'])){
			$queryParams['Person_id'] = $data['Person_id'];
			$where .= ' AND ps.Person_id = :Person_id';
		}
		if(!empty($data['LpuAttachType_id'])){
			$queryParams['LpuAttachType_id'] = $data['LpuAttachType_id'];
			$where .= ' AND pc.LpuAttachType_id = :LpuAttachType_id';
		}
		if(!empty($data['MedPersonal_SurName'])){
			$queryParams['Person_SurName'] = trim($data['MedPersonal_SurName']);
			$where .= ' AND mrmp.Person_SurName = :Person_SurName';
		}
		if(!empty($data['MedPersonal_FirName'])){
			$queryParams['Person_FirName'] = trim($data['MedPersonal_FirName']);
			$where .= ' AND mrmp.Person_FirName = :Person_FirName';
		}
		if(!empty($data['MedPersonal_SecName'])){
			$queryParams['Person_SecName'] = trim($data['MedPersonal_SecName']);
			$where .= ' AND mrmp.Person_SecName = :Person_SecName';
		}
		if(!empty($data['MedPersonal_Snils'])){
			$queryParams['Person_Snils'] = str_replace("-", "", $data['MedPersonal_Snils']);
			$where .= ' AND mrmp.MedPersonal_Snils = :Person_Snils';
		}
		if(!empty($data['PostKindCode'])){
			$queryParams['PostKindCode'] = $data['PostKindCode'];
			$where .= ' AND mrmp.code = :PostKindCode';
		}
		if(!empty($data['LPUKOD'])){
			$queryParams['LPUKOD'] = $data['LPUKOD'];
			$where .= ' AND left(L.Lpu_f003mcod, 6) + mrmp.LpuRegionKOD = :LPUKOD';			
		}
		if(!$where) return false;
		$sql = "SELECT 
			ps.Person_id as \"Person_id\",
			pc.LpuAttachType_id as \"LpuAttachType_id\",
			mrmp.Person_SurName AS \"FAM_DOC\", 
			mrmp.Person_FirName AS \"IM_DOC\", 
			mrmp.Person_SecName AS \"OT_DOC\",
			mrmp.LpuRegionKOD as \"LpuRegionKOD\",
			coalesce(mrmp.code, '') AS \"LPUKAT\",
			pc.PersonCard_id as \"PersonCard_id\",
			PC.Lpu_id as \"Lpu_id\",
			PC.LpuRegion_id as \"LpuRegion_id\",
			PC.LpuRegion_Fapid as \"LpuRegion_Fapid\",
			PC.LpuRegionType_id as \"LpuRegionType_id\",
			PC.PersonCardAttach_id as \"PersonCardAttach_id\",
			PC.PersonCard_begDate as \"PersonCard_begDate\",
			PC.PersonCard_endDate as \"PersonCard_endDate\"
		FROM v_PersonCard_all pc
			INNER JOIN v_PersonState ps on ps.Person_id = pc.Person_id
			--left join PersonCard PCard on PCard.PersonCard_id = pc.PersonCard_id
			left join v_Lpu L on L.Lpu_id = PC.Lpu_id
			--left join v_LpuRegion LR on LR.LpuRegion_id = PC.LpuRegion_id			
			left join lateral (
				SELECT
					msr.MedStaffFact_id,
					msr.MedPersonal_id,
					mp.Person_Snils AS MedPersonal_Snils, 
					mp.Person_SurName, 
					mp.Person_FirName, 
					mp.Person_SecName,
					CASE WHEN pk.code=1 THEN '1' WHEN pk.code=2 THEN '2' ELSE '' END AS code,
					CASE
						when LR.LpuRegionType_SysNick in ('ter', 'ped', 'vop', 'feld') then
							case
								when LR.LpuRegionType_SysNick in ('ter') then '1'
								when LR.LpuRegionType_SysNick in ('ped') then '2'
								when LR.LpuRegionType_SysNick in ('vop') then '3'
								when LR.LpuRegionType_SysNick in ('feld') then '5'
								else ''
							end ||
							RIGHT('00' || coalesce(LR.LpuRegion_Name,''), 2)
						else ''
					end as LpuRegionKOD
				FROM v_MedStaffRegion msr
					INNER join v_LpuRegion lr on lr.LpuRegion_id = msr.LpuRegion_id
					left join v_MedStaffFact msf on msf.MedStaffFact_id = msr.MedStaffFact_id and msr.Lpu_id = msf.Lpu_id
					left join lateral (
						select Person_Fio, Person_Snils, Person_SurName, Person_FirName, Person_SecName
						from v_MedPersonal
						where MedPersonal_id = msr.MedPersonal_id
						limit 1
					) mp on true
					LEFT JOIN persis.v_PostKind pk ON pk.id = msf.PostKind_id
				WHERE (1=1)  
					AND msr.Lpu_id=PC.Lpu_id 
					AND (msr.LpuRegion_id = PC.LpuRegion_id /*OR msr.LpuRegion_id = PC.LpuRegion_fapid*/) 
					AND msr.MedStaffRegion_isMain = 2
			) mrmp on true
		WHERE 1=1
			AND pc.PersonCard_endDate IS NULL
			{$where}
		ORDER BY pc.PersonCard_begDate DESC
		";
		//echo getDebugSQL($sql, $queryParams); die();	
		$res = $this->db->query($sql, $queryParams);

        if (is_object($res)) {
            $result = $res->result('array');
        }
		return $result;
	}
	
	/**
	 * Поиск заявления на прикрепление
	 */
	function searchPersonAttachDBF($data){
		$result = array();
		$queryParams = array();
		$where = '';

		if(!empty($data['Person_id'])){
			$queryParams['Person_id'] = $data['Person_id'];
			$where .= ' AND ps.Person_id = :Person_id';
		}
		if(!empty($data['LpuAttachType_id'])){
			$queryParams['LpuAttachType_id'] = $data['LpuAttachType_id'];
			$where .= ' AND pc.LpuAttachType_id = :LpuAttachType_id';
		}
		if(!empty($data['MedPersonal_SurName'])){
			$queryParams['Person_SurName'] = trim($data['MedPersonal_SurName']);
			$where .= ' AND mrmp.Person_SurName = :Person_SurName';
		}
		if(!empty($data['MedPersonal_FirName'])){
			$queryParams['Person_FirName'] = trim($data['MedPersonal_FirName']);
			$where .= ' AND mrmp.Person_FirName = :Person_FirName';
		}
		if(!empty($data['MedPersonal_SecName'])){
			$queryParams['Person_SecName'] = trim($data['MedPersonal_SecName']);
			$where .= ' AND mrmp.Person_SecName = :Person_SecName';
		}
		if(!empty($data['MedPersonal_Snils'])){
			$queryParams['MedPersonal_Snils'] = str_replace("-", "", $data['MedPersonal_Snils']);
			$where .= ' AND mrmp.MedPersonal_Snils = :MedPersonal_Snils';
		}
		if(!empty($data['LPUKOD'])){
			$queryParams['LPUKOD'] = $data['LPUKOD'];
			$where .= ' AND left(L.Lpu_f003mcod, 6) || mrmp.LpuRegionKOD = :LPUKOD';			
		}
		
		if(!$where) return false;
		if(!empty($data['plot_type'])){
			//поиск по типу участка
			if($data['plot_type'] == 'basis') {
				$where .= ' AND mrmp.code = 1';
			}else if($data['plot_type'] == 'fed') {
				$where .= ' AND mrmp.code = 2';
			}else{
				$where .= ' AND mrmp.code in (1,2)';
			}
		}
		
		$sql = "
			select
				PCA.Person_id as \"Person_id\",
				PCA.PersonCardAttach_id as \"PersonCardAttach_id\",
				PCA.LpuRegion_id as \"LpuRegion_id\",
				PCA.LpuRegion_fapid as \"LpuRegion_fapid\",
				PCA.MedStaffFact_id as \"MedStaffFact_id\",
				mrmp.Person_SurName as \"Person_SurName\",
				mrmp.Person_FirName as \"Person_FirName\",
				mrmp.Person_SecName as \"Person_SecName\",
				mrmp.MedPersonal_id as \"MedPersonal_id\",
				mrmp.MedPersonal_Snils as \"MedPersonal_Snils\"
			from v_PersonCardAttach PCA
				inner join v_PersonState PS on PS.Person_id = PCA.Person_id
				left join v_Lpu L on L.Lpu_id = PCA.Lpu_id
				left join lateral (
					SELECT
						msr.MedStaffFact_id,
						msr.MedPersonal_id,
						mp.Person_Snils AS MedPersonal_Snils, 
						mp.Person_SurName, 
						mp.Person_FirName, 
						mp.Person_SecName, 
						CASE WHEN pk.code=1 THEN '1' WHEN pk.code=2 THEN '2' ELSE '' END AS code,
						CASE
							when LR.LpuRegionType_SysNick in ('ter', 'ped', 'vop', 'feld') then
								case
									when LR.LpuRegionType_SysNick in ('ter') then '1'
									when LR.LpuRegionType_SysNick in ('ped') then '2'
									when LR.LpuRegionType_SysNick in ('vop') then '3'
									when LR.LpuRegionType_SysNick in ('feld') then '5'
									else ''
								end ||
								RIGHT('00' || coalesce(LR.LpuRegion_Name,''), 2)
							else ''
						end as LpuRegionKOD
					FROM v_MedStaffRegion msr
						INNER join v_LpuRegion lr on lr.LpuRegion_id = msr.LpuRegion_id
						left join v_MedStaffFact msf on msf.MedStaffFact_id = msr.MedStaffFact_id and msr.Lpu_id = msf.Lpu_id
						left join lateral (
							select Person_Fio, Person_Snils, Person_SurName, Person_FirName, Person_SecName
							from v_MedPersonal
							where MedPersonal_id = msr.MedPersonal_id
							limit 1
						) mp on true
						LEFT JOIN persis.v_PostKind pk ON pk.id = msf.PostKind_id
					WHERE (1=1)  
						AND msr.Lpu_id=PCA.Lpu_id 
						AND (msr.LpuRegion_id = PCA.LpuRegion_id /*OR msr.LpuRegion_id = PCA.LpuRegion_fapid*/) 
						AND msr.MedStaffRegion_isMain = 2
				) mrmp on true
				LEFT JOIN v_PersonCard_all PC ON PCA.PersonCardAttach_id = PC.PersonCardAttach_id
				/*
				left join lateral (
					--статус заявительного прикрепления
					select PCAST.PersonCardAttachStatusType_Code, PCAST.PersonCardAttachStatusType_Name
					from v_PersonCardAttachStatus PCAS
					left join PersonCardAttachStatusType PCAST on PCAST.PersonCardAttachStatusType_id = PCAS.PersonCardAttachStatusType_id
					where PCAS.PersonCardAttach_id = PCA.PersonCardAttach_id
					order by PCAS.PersonCardAttachStatusType_id desc
					limit 1
				) PCAS on true
				*/
			WHERE 1=1
				AND PC.PersonCard_id IS null --не связано с прикреплением
				AND pc.PersonCard_endDate IS NULL
				{$where}
			ORDER BY PCA.PersonCardAttach_insDT DESC
		";
		//echo getDebugSQL($sql, $queryParams); die();
		$res = $this->db->query($sql, $queryParams);

        if (is_object($res)) {
            $result = $res->result('array');
        }
		return $result;
	}


	/**
	 * Установка статуса прикреплению
	 */
	function setPersonCardStatusDBF($data){
		if(empty($data['PersonCard_id']) || empty($data['PersonCardAttachStatusType_id']) || empty($data['pmUser_id'])) return false;		
		
		$query = "
			select
				PersonCardStatus_id as \"PersonCardStatus_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_PersonCardStatus_ins (
				PersonCard_id := :PersonCard_id,
				PersonCardAttachStatusType_id := :PersonCardAttachStatusType_id,
				PersonCardStatus_setDate := dbo.tzGetDate(),
				pmUser_id := :pmUser_id
			)
		";

		$params = array(
			'PersonCard_id' => $data['PersonCard_id'],
			'PersonCardAttachStatusType_id' => $data['PersonCardAttachStatusType_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$result = $this->db->query($query, $params);
		$response = $result->result('array');
		if(is_object($result)){
			$response = $result->result('array');
			return $response[0];
		}
		return false;
	}
	
	/**
	 * Поиск человека
	 */
	function searchPersonDBF($data){
		if(empty($data)) return false;
		$line = $data;
		$person_id = false;
		$where = '';
		$queryParams = array();
		
		if(!empty($line['ID'])){
			$where .= ' AND PS.Person_id = :Person_id';
			$queryParams = array('Person_id'=>$line['ID']);
		}else if(!empty($line['NPOL']) && !empty($line['FAM']) && !empty($line['IM']) && !empty($line['DR'])){
			// по сочетанию ФИО (отчество – при наличии) + ДР + Серия (при наличии) и номер полиса ищем человека (не нашли человека – сообщение об ошибке в лог).
			$where .= " AND PS.Person_SurNameR = :Person_SurNameR";
			$queryParams['Person_SurNameR'] = $line['FAM'];
			$where .= " AND PS.Person_FirNameR = :Person_FirNameR";
			$queryParams['Person_FirNameR'] = $line['IM'];
			$where .= " AND PS.Person_BirthDay = :Person_BirthDay";
			$queryParams['Person_BirthDay'] = $line['DR'];
			$queryParams['Polis_Num'] = $line['NPOL'];
			$where .= ' AND PS.Polis_Num = :Polis_Num';
			if(!empty($line['OT'])){
				$where .= " AND PS.Person_SecNameR = :Person_SecNameR";
				$queryParams['Person_SecNameR'] = $line['OT'];
			}
			if(!empty($line['SPOL'])){
				$where .= " AND PS.Polis_Ser = :Polis_Ser";
				$queryParams['Polis_Ser'] = $line['SPOL'];
			}	
		}
		$sql = "
			select PS.Person_id as \"Person_id\"
			from v_PersonState PS
				left join Person per on per.Person_id = ps.Person_id
			where 1=1
				AND coalesce(per.Person_IsUnknown,1) <> 2
				{$where}
			limit 1";
		//echo getDebugSQL($sql, $queryParams); die();
		if($where){
			$resPerson = $this->getFirstRowFromQuery($sql, $queryParams);
			$person_id =(!empty($resPerson['Person_id'])) ? $resPerson['Person_id'] : false;
		}
		return $person_id;
	}
	
	/**
	 * Получить PersonCardAttachStatusType_id по коду
	 */
	function getByCodePersonCardAttachStatusTypeID($code){
		if(empty($code)) return false;
		$query = "SELECT PersonCardAttachStatusType_id as \"PersonCardAttachStatusType_id\" FROM v_PersonCardAttachStatusType WHERE PersonCardAttachStatusType_Code = :Code";
		$personCardAttachStatusType = $this->getFirstRowFromQuery($query, array('Code' => $code));
		$personCardAttachStatusType_id =(!empty($personCardAttachStatusType['PersonCardAttachStatusType_id'])) ? $personCardAttachStatusType['PersonCardAttachStatusType_id'] : false;
		return $personCardAttachStatusType_id;
	}
	
	/**
	 * Получаем Lpu_id по f003mcod в LPUKOD
	 */
	function getLpuIdByAppointment_f003mcod($data){
		if(empty($data['LPUKOD'])) return false;
		$f003mcod = substr($data['LPUKOD'], 0, 6);
		$query = "SELECT Lpu_id as \"Lpu_id\" FROM v_Lpu L WHERE left(L.Lpu_f003mcod, 6) = :f003mcod LIMIT 1";
		$Lpu = $this->getFirstRowFromQuery($query, array('f003mcod' => $f003mcod));
		$Lpu_id =(!empty($Lpu['Lpu_id'])) ? $Lpu['Lpu_id'] : false;
		return $Lpu_id;
	}
	
	/**
	 * ЗАпись лог файла
	 */
	function recordLogFile($filename, $errArr, $mode = ''){
		$logfilepath = false;
		if (count($errArr) > 0) {
			$logfilename = $filename;
			$log = $errArr;

			$out_path = $this->getExportPersonCardAttachPath($mode);
			if (!is_dir($out_path)) mkdir($out_path);

			$logfilepath = $out_path . "/" . $logfilename;

			$result = file_put_contents(toAnsi($logfilepath, true), implode("\n\n", $log));
		}
		return $logfilepath;
	}
	
	/**
	 * Формирование строки с ошибкой для лог файла
	 */
	function strLogDBF($data, $error){
		$record = $data;
		
		$fam = (!empty($record['FAM'])) ? $record['FAM'] : '';
		$im = (!empty($record['IM'])) ? $record['IM'] : '';
		$ot = (!empty($record['OT'])) ? $record['OT'] : '';
		$dr = (!empty($record['DR'])) ? $record['DR'] : '';
		$spol = (!empty($record['SPOL'])) ? $record['SPOL'] : '';
		$npol = (!empty($record['NPOL'])) ? $record['NPOL'] : '';				
		$lpukat = (!empty($record['LPUKAT'])) ? $record['LPUKAT'] : '';
		$lpukod = (!empty($record['LPUKOD'])) ? $record['LPUKOD'] : '';
		$fam_doc = (!empty($record['FAM_DOC'])) ? $record['FAM_DOC'] : '';
		$im_doc = (!empty($record['IM_DOC'])) ? $record['IM_DOC'] : '';
		$ot_doc = (!empty($record['OT_DOC'])) ? $record['OT_DOC'] : '';
		$lpuss = (!empty($record['LPUSS'])) ? $record['LPUSS'] : '';
		$lpudt = (!empty($record['LPUDT'])) ? $record['LPUDT'] : '';
		$lpudx = (!empty($record['LPUDX'])) ? $record['LPUDX'] : '';

		$err = array(
			'ФИО: '.$fam.' '.$im.' '.$ot,
			'ДР: '.$dr,
			'ПОЛИС: '.$spol.' '.$npol,
			'Заявление/прикрепление: '.$lpukat.', '.$lpukod.', '.$fam_doc.' '.$im_doc.' '.$ot_doc.' '.$lpuss,
			'дата прикрепления: '.$lpudt,
			'дата открепления: '.$lpudx,
			'Результат: '.$error
		);
		return implode("\r\n",$err);
	}
	
	/**
	 * получение участка по LPUKOD
	 */
	function getLpuRegionID_LPUKOD($data){
		if(empty($data['LPUKOD'])) return false;
		$LpuRegion_id = NULL;
		$LpuRegionType_id = NULL;
		
		$numRegion = (int)substr($data['LPUKOD'], -2);
		$typeRegion = substr($data['LPUKOD'], -3, 1);
		$regionSysNick = array(1 => 'ter', 2 => 'ped', 3 => 'vop', 5 => 'feld');
		$Lpu_id = $this->getLpuIdByAppointment_f003mcod($data);
		if(!empty($Lpu_id) && !empty($numRegion) && !empty($typeRegion) && !empty($regionSysNick[$typeRegion])){
			$query = "
				SELECT LR.LpuRegion_id as \"LpuRegion_id\", LR.LpuRegionType_id as \"LpuRegionType_id\"
				FROM v_LpuRegion LR
				WHERE 
					RIGHT('00' || coalesce(LR.LpuRegion_Name,''), 2) = :LpuRegion_Name
					AND LR.Lpu_id = :Lpu_id
					AND LR.LpuRegionType_SysNick = :LpuRegionType_SysNick";
			$LpuRegion = $this->getFirstRowFromQuery($query, array(
				'LpuRegion_Name' => $numRegion,
				'Lpu_id' => $Lpu_id,
				'LpuRegionType_SysNick' => $regionSysNick[$typeRegion]
			));
			$LpuRegion_id =(!empty($LpuRegion['LpuRegion_id'])) ? $LpuRegion['LpuRegion_id'] : NULL;
			$LpuRegionType_id =(!empty($LpuRegion['LpuRegionType_id'])) ? $LpuRegion['LpuRegionType_id'] : NULL;
		}
		
		return array(
			'LpuRegion_id' => $LpuRegion_id,
			'LpuRegionType_id' => $LpuRegionType_id,
		);
	}
	
	/**
	 * Получение кода и текста ошибки из 31. ERR 
	 */
	function getErrorFLK($data){
		if(empty($data['ERR'])) return '';
		$errorsArr = array(
			'OP' => array(
					'1.1' => 'Поле не найдено или имеет неверный тип',
					'1.2' => 'Поле содержит неверное значение (Допустимо: У, И, Р)'
				),
			'FAM' => array(
					'2.1' => 'Поле не найдено или имеет неверный тип',
					'2.2' => 'Поле содержит недопустимые знаки или сочетания знаков'
				),
			'IM' =>	array(
					'3.1' => 'Поле не найдено или имеет неверный тип',
					'3.2' => 'Поле содержит недопустимые знаки или сочетания знаков'
				),
			'OT' => array(
					'4.1' => 'Поле не найдено или имеет неверный тип',
					'4.2' => 'Поле содержит недопустимые знаки или сочетания знаков'
				),
			'DR' =>	array(
					'5.1' => 'Поле не найдено или имеет неверный тип',
					'5.2' => 'Дата рождения больше даты выгрузки файла'
				),
			'W' => array(
					'6.1' => 'Поле не найдено или имеет неверный тип',
					'6.2' => 'Содержит недопустимое значение (должно быть 1 или 2)'
				),
			'SPOL' =>array(
					'7.1' => 'Поле не найдено или имеет неверный тип',
					'7.2' => 'Поле имеет неверный формат (Может содержать русские,  латинские буквы, тире, цифры)'
				),
			'NPOL' => array(
					'8.1' => 'Поле не найдено или имеет неверный тип',
					'8.2' => 'Поле должно содержать только цифры',
					'8.3' => 'Поле имеет неверный формат: Если SPOL - не пусто, то длина может быть любой. Если SPOL - пусто, то поле должно содержать либо 9, либо 16 символов.'
				),
			'Q' => array(
					'9.1' => 'Поле не найдено или имеет неверный тип',
					'9.2' => 'Указан неверный реестровый номер СМО (несоответствие коду СМО в названии файла)'
				),
			'LPU' => array(
					'10.1' => 'Поле не найдено или имеет неверный тип',
					'10.2' => 'Не указан реестровый номер МО',
					'10.3' => 'Указан неверный реестровый номер МО (несоответствие коду МО в названии файла)'
				),
			'LPUDZ' => array(
					'11.1' => 'Поле не найдено или имеет неверный тип',
					'11.2' => 'Дата подачи заявления больше даты выгрузки файла'
				),
			'LPUDU' =>	array(
					'12.1' => 'Поле не найдено или имеет неверный тип',
					'12.2' => 'Дата не попадает в допустимый период для события "И"'
				),
			'LPUTP'	=> array(
					'13.1' => 'Поле не найдено или имеет неверный тип',
					'13.2' => 'Поле содержит неверное значение (Допустимо: 1, 2)'
				),
			'LPUPODR' => array(
					'14.1' => 'Поле не найдено или имеет неверный тип',
					'14.2' => 'Поле не может быть пустым '
				),
			'LPUSS' => array(
					'15.1' => 'Поле не найдено или имеет неверный тип',
					'15.2' => 'Не указан СНИЛС врача',
					'15.3' => 'Поле содержит недопустимые символы',
					'15.4' => 'Указана неверная длина',
					'15.5' => 'Контрольное число (две последние цифры) вычислены с ошибкой. Поле должно соответствовать формату ххх ххх ххх хх, где х - цифра, пробелы или тире между числами не контролируются.'
				),
			'FAM_DOC' => array(
					'16.1' => 'Поле не найдено или имеет неверный тип',
					'16.2' => 'Поле содержит недопустимые знаки или сочетания знаков'
				),
			'IM_DOC' => array(
					'17.1' => 'Поле не найдено или имеет неверный тип',
					'17.2' => 'Поле содержит недопустимые знаки или сочетания знаков'
				),
			'OT_DOC' =>	array(
					'18.1' => 'Поле не найдено или имеет неверный тип',
					'18.2' => 'Поле содержит недопустимые знаки или сочетания знаков'
				),
			'LPUKOD' =>	array(
					'19.1' => 'Поле не найдено или имеет неверный тип',
					'19.2' => 'Поле не может быть пустым (Может содержать буквы, цифры)'
				),
			'LPUKAT' => array(
					'20.1' => 'Поле не найдено или имеет неверный тип',
					'20.2' => 'Не указана категория врача',
					'20.3' => 'Указано недопустимое значение (Допустимо: 1, 2)'
				),
			'OKATO'	=> array(
					'21.1' => 'Поле не найдено или имеет неверный тип',
					'21.2' => 'Поле не может быть пустым',
					'21.3' => 'Поле имеет неверный формат (длинна должна быть 11 символов, поле может содержать только цифры)'
				),
			'RNNAME' =>	array(
					'22.1' => 'Поле не найдено или имеет неверный тип',
					'22.2' => 'Поле не может быть пустым',
				),
			'NPNAME' =>	array(
					'23.1' => 'Поле не найдено или имеет неверный тип',
					'23.2' => 'Поле не может быть пустым'
				),
			'UL' =>	array(
					'24.1' => 'Поле не найдено или имеет неверный тип',
					'24.2' => 'Поле не может быть пустым',
				),
			'DOM' => array(
					'25.1' => 'Поле не найдено или имеет неверный тип',
					'25.2' => 'Поле не может быть пустым'
				),
			'KORP' => array(
					'26.1'	=> 'Поле не найдено или имеет неверный тип'
				),
			'KV' =>	array(
					'27.1' => 'Поле не найдено или имеет неверный тип'
				)
		);
		
		$error = $data['ERR'];
		foreach ($errorsArr as $key => $value) {
			if (array_key_exists($data['ERR'], $value)){
				$error = $key.' - '.$value[$data['ERR']];
				break 1;
			}
		}
		
		return $error;
	}
	
	/**
	 * Закрытие прикрепления при импорте DBF
	 */
	function closePersonCard_importDBF($data, $line){
		if(empty($data['PersonCard_id'])) return false;
		$response = array(
			'success' => false,
			'PersonCard_id' => null,
			'PersonCard_endDate' => null,
			'Error_Code' => null,
			'Error_Msg' => null,
		);
		$codeCMO_promedCode = array(
			1 => array('errorSMO' => 'Смерть застрахованного', 'promedCode' => 2),
			2 => array('errorSMO' => 'Ежегодная замена страховой компании застрахованным лицом', 'promedCode' => 10),
			3 => array('errorSMO' => 'Замена страховой компании по причине изменения места жительства', 'promedCode' => 10),
			4 => array('errorSMO' => 'Выдача временного свидетельства в другой СМО', 'promedCode' => 10),
			5 => array('errorSMO' => 'Выявление дубликата', 'promedCode' => 8),
			6 => array('errorSMO' => 'Прочие причины', 'promedCode' => 8),
		);
		if(empty($line['RSTOP'])){
			$response['Error_Msg'] = 'отсутствует Причина открепления (снятия с учета) в RSTOP';
			$response['Error_Code'] = 7;
			return $response;
		}
		if(empty($line['LPUDX'])){
			$response['Error_Msg'] = 'отсутствует дата открепления в LPUDX';
			$response['Error_Code'] = 7;
			return $response;
		}
		if(!array_key_exists($line['RSTOP'], $codeCMO_promedCode)){
			$response['Error_Msg'] = 'не найдена причина открепления в таблице стыковки';
			$response['Error_Code'] = 7;
			return $response;
		}
		$query = "
			select
				PC.PersonCard_id as \"PersonCard_id\",
				PC.Person_id as \"Person_id\",
				PC.Lpu_id as \"Lpu_id\",
				PC.LpuRegion_id as \"LpuRegion_id\",
				PC.LpuAttachType_id as \"LpuAttachType_id\",
				PC.PersonCard_Code as \"PersonCard_Code\",
				to_char(PC.PersonCard_begDate, 'yyyy-mm-dd') as \"PersonCard_begDate\",
				CCC.CardCloseCause_id as \"CardCloseCause_id\",
				PC.PersonCard_IsAttachCondit as \"PersonCard_IsAttachCondit\",
				PC.OrgSMO_id as \"OrgSMO_id\",
				PC.PersonCardAttach_id as \"PersonCardAttach_id\",
				PC.LpuRegion_fapid as \"LpuRegion_fapid\",
				PC.LpuRegionType_id as \"LpuRegionType_id\",
				PC.MedStaffFact_id as \"MedStaffFact_id\"
			from
				v_PersonCard PC
				left join v_LpuAttachType LAT on LAT.LpuAttachType_id = PC.LpuAttachType_id
				left join v_CardCloseCause CCC on CCC.CardCloseCause_Code = :CardCloseCause_Code
			where
				PC.PersonCard_id = :PersonCard_id
				and PC.PersonCard_begDate <= :date
				and PC.PersonCard_endDate is null
				and LAT.LpuAttachType_SysNick = 'main'
			order by
				PC.PersonCard_begDate desc
			limit 1
		";
		$params = array(
			'PersonCard_id' => $data['PersonCard_id'],
			'date' => $line['LPUDX'],
			'CardCloseCause_Code' => $codeCMO_promedCode[$line['RSTOP']]['promedCode']
		);
		$PersonCard = $this->getFirstRowFromQuery($query, $params, true);
		if ($PersonCard === false || empty($PersonCard['PersonCard_id'])) {
			$response['Error_Msg'] = 'Ошибка при поиске прикрепления человека';
			$response['Error_Code'] = 7;
			$response['success'] = false;
			return $response;
		}

		$params = array_merge($PersonCard, array(
			'Server_id' => $data['Server_id'],
			'pmUser_id' => $data['pmUser_id'],
		));
		$query = "
			select
				PersonCard_id as \"PersonCard_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_PersonCard_upd (
				PersonCard_id := :PersonCard_id,
				Person_id := :Person_id,
				Lpu_id := :Lpu_id,
				Server_id := :Server_id,
				PersonCard_begDate := :PersonCard_begDate,
				PersonCard_endDate := :PersonCard_endDate,
				PersonCard_Code := :PersonCard_Code,
				PersonCard_IsAttachCondit := :PersonCard_IsAttachCondit,
				OrgSMO_id := :OrgSMO_id,
				LpuRegion_id := :LpuRegion_id,
				LpuRegion_fapid := :LpuRegion_fapid,
				LpuAttachType_id := :LpuAttachType_id,
				CardCloseCause_id := :CardCloseCause_id,
				PersonCardAttach_id := :PersonCardAttach_id,
				LpuRegionType_id := :LpuRegionType_id,
				MedStaffFact_id := :MedStaffFact_id,
				pmUser_id := :pmUser_id
			)
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp) || empty($resp[0]['PersonCard_id'])) {
			$response['Error_Msg'] = 'Ошибка при закрытии прикрепления';
			$response['Error_Code'] = 7;
			$response['success'] = false;
		}else{
			$response['Error_Msg'] = null;
			$response['Error_Code'] = null;
			$response['PersonCard_id'] = $resp[0]['PersonCard_id'];
			$response['PersonCard_endDate'] = $line['LPUDX'];
			$response['success'] = true;
		}

		return array($response);
	}
	
	/**
	 * Создание нового человека на основе данных импорта DBF
	 */
	function createNewPerson_importDBF($data){
		$result = array(
			'success' => false,
			'Person_id' => null,
			'Error_Msg' => null
		);
		$params = array();
		$params['mode'] = 'add';
		$params['Polis_CanAdded'] = 0; //Признак того, что разрешено добавлять полис
		$params['Person_id'] = null;
		$params['Person_SurName'] = (!empty($data['FAM'])) ? $data['FAM'] : '';
		$params['Person_FirName'] = (!empty($data['IM'])) ? $data['IM'] : '';
		$params['Person_SecName'] = (!empty($data['OT'])) ? $data['OT'] : '';
		$params['Person_BirthDay'] = (!empty($data['DR'])) ? $data['DR'] : '';
		$params['PersonSex_id'] = (!empty($data['W'])) ? $data['W'] : '';
		$params['SocStatus_id'] = $this->getSocStatusID($data);
		$params['Server_id'] = $data['Server_id'];
		$params['pmUser_id'] = $data['pmUser_id'];
		$params['session'] = $data['session'];
		
		$res = $this->Person_model->savePersonEditWindow($params);
		
		if(!empty($res[0]['Person_id'])){
			$result['Person_id'] = $res[0]['Person_id'];
			$result['success'] = true;
		} else {
			$result['Error_Msg'] = ($res[0]['Error_Msg']) ? $res[0]['Error_Msg'] : 'Ошибки сохранения человека';
		}
		return $result;
	}
	
	/**
	 * Получение ID социального статуса
	 */
	function getSocStatusID($data){
		if(empty($data['DR']) || empty($data['W'])) return null;
		$SocStatus_id = null;
		$age = getCurrentAge($data['DR']);
		$sql = "
			SELECT
				SocStatus_id as \"SocStatus_id\",
				SocStatus_SysNick as \"SocStatus_SysNick\",
				SocStatus_Name as \"SocStatus_Name\",
				SocStatus_Code as \"SocStatus_Code\"
			from v_SocStatus
			WHERE
				SocStatus_Code = (
					CASE 
						 WHEN :Age BETWEEN 0 AND 2 THEN 1
						 WHEN :Age BETWEEN 3 AND 6 THEN 2
						 WHEN :Age BETWEEN 7 AND 22 THEN 3
						 WHEN :Age BETWEEN 23 AND 54 AND :Sex_id=2 THEN 4
						 WHEN :Age BETWEEN 23 AND 59 AND :Sex_id=1 THEN 4
						 WHEN :Age BETWEEN 55 AND 200 AND :Sex_id=2 THEN 6
						 WHEN :Age BETWEEN 60 AND 200 AND :Sex_id=1 THEN 6
					END
				)
		";
		
		$SocStatus = $this->getFirstRowFromQuery($sql, array(
			'Age' => $age,
			'Sex_id' => $data['W']
		));
		$SocStatus_id =(!empty($SocStatus['SocStatus_id'])) ? $SocStatus['SocStatus_id'] : NULL;
		return $SocStatus_id;
	}
}
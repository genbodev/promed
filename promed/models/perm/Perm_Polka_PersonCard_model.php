<?php
/**
* Vologda_Polka_PersonCard_model - модель, для работы с таблицей PersonCard (Вологда)
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

require_once(APPPATH.'models/Polka_PersonCard_model.php');

class Perm_Polka_PersonCard_model extends Polka_PersonCard_model {
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
			declare
				@Date date = :Date_upload,
				@getdate datetime = cast(dbo.tzGetDate() as date),
				@Lpu_id bigint = :Lpu_id;

			select distinct
				PC.PersonCard_id as Field_id,
				'1' as Field_Type,
				PC.Person_id as IDCASE, -- Уникальный идентификатор пациента 
				rtrim(upper(PS.Person_SurName)) as FAM, -- Фамилия
				rtrim(upper(PS.Person_FirName)) as IM, -- Имя
				isnull(rtrim(Upper(case when Replace(PS.Person_Secname,' ','')='---'  or PS.Person_Secname = '' then 'НЕТ' else PS.Person_Secname end)), 'НЕТ') as OT, -- Отчество
				convert(varchar(10), PS.Person_BirthDay, 120) as DR, -- Дата рождения застрахованного
				PS.Person_Snils as SNILS,
				DT.DocumentType_Code as DOCTYPE,
				D.Document_Ser as DOCSER,
				D.Document_Num as DOCNUM,
				convert(varchar(10), D.Document_begDate, 120) as DOCDT,
				null as TEL,
				[PI].Person_BDZCode as RZ,
				case when pc.PersonCardAttach_id is not null then 2 else 1 end as SP_PRIK,
				case when pc.PersonCard_endDate is null then 1 else 2 end as T_PRIK,
				convert(varchar(10), ISNULL(PC.PersonCard_endDate, PC.PersonCard_begDate), 120) as DATE_1,
				case when PC.PersonCard_endDate is null and ADDRESSCHANGE.PersonUAddress_id is not null then 1 else 0 end as N_ADR,
				right('00000000' + isnull(L.Lpu_f003mcod,'') + isnull(LB.LpuBuilding_Code, ''), 8) as KODPODR,
				case
					when LR.LpuRegionType_SysNick in ('ter', 'ped', 'vop') then
						case
							when LR.LpuRegionType_SysNick in ('ter', 'vop') then '1'
							when LR.LpuRegionType_SysNick in ('ped') then '2'
							else ''
						end + ISNULL(LR.LpuRegion_Name,'')
					else ''
				end as LPUUCH,
				case
					when MSF.MedStaffFact_id is not null then ISNULL(MP.Person_Snils, '')
					else ISNULL(LRMSF.Person_Snils, '')
				end as SSD,
				case
					when MSF.MedStaffFact_id is not null and MSF.PostKind_id = 1 then 1
					when MSF.MedStaffFact_id is not null then 2
					when LRMSF.PostKind_id = 1 then 1
					else 2
				end as MEDRAB
			from
				PersonCard PC with (nolock)
				left join v_PersonState PS with (nolock) on PS.Person_id = PC.Person_id
				left join v_Polis PLS with (nolock) on PLS.Polis_id = PS.Polis_id
				left join v_Lpu L with (nolock) on L.Lpu_id = PC.Lpu_id
				left join v_Document D with (nolock) on D.Document_id = PS.Document_id
				left join v_DocumentType DT with (nolock) on DT.DocumentType_id = D.DocumentType_id
				left join v_LpuRegion LR with (nolock) on LR.LpuRegion_id = PC.LpuRegion_id
				left join v_LpuSection LS with(nolock) on LS.LpuSection_id = LR.LpuSection_id
				left join v_LpuBuilding LB with(nolock) on LB.LpuBuilding_id = LS.LpuBuilding_id
				left join v_MedStaffFact MSF (nolock) on MSF.MedStaffFact_id = PC.MedStaffFact_id
				left join v_MedPersonal MP (nolock) on MP.MedPersonal_id = MSF.MedPersonal_id
				outer apply (
					select top 1 t2.Person_Snils, t3.PostKind_id
					from v_MedStaffRegion t1 with(nolock)
						inner join v_MedStaffFact t3 with (nolock) on t3.MedStaffFact_id = t1.MedStaffFact_id
						inner join v_MedPersonal t2 with(nolock) on t2.MedPersonal_id = t3.MedPersonal_id
					where t1.LpuRegion_id = PC.LpuRegion_id
						and t2.Person_Snils is not null
						and t3.Lpu_id = @Lpu_id
						and (t3.WorkData_begDate is null or t3.WorkData_begDate <= /*ISNULL(@Date, @getdate)*/PC.PersonCard_begDate)
						and (t3.WorkData_endDate is null or t3.WorkData_endDate >= /*ISNULL(@Date, @getdate)*/PC.PersonCard_begDate)
						and (t1.MedStaffRegion_begDate is null or t1.MedStaffRegion_begDate <= /*ISNULL(@Date, @getdate)*/PC.PersonCard_begDate)
						and (t1.MedStaffRegion_endDate is null or t1.MedStaffRegion_endDate >= /*ISNULL(@Date, @getdate)*/PC.PersonCard_begDate)
					order by t3.PostKind_id
				) LRMSF
				outer apply (
					select top 1 Person_BDZCode
					from v_PersonInfo with (nolock)
					where Person_id = PS.Person_id
				) [PI]
				outer apply (
					select top 1 CardCloseCause_id, PersonCard_begDate
					from v_PersonCard_all t with (nolock)
					where t.Person_id = PC.Person_id
						and t.PersonCard_id != PC.PersonCard_id
						and cast(t.PersonCard_endDate as date) = cast(PC.PersonCard_begDate as date)
					order by t.PersonCard_begDate desc
				) PCL
				outer apply (
					select top 1
						pua.PersonUAddress_id
					from
						v_PersonUAddress pua (nolock)
					where
						pua.Person_id = pc.Person_id
						and pua.PersonUAddress_insDate >= PCL.PersonCard_begDate
						and pua.PersonUAddress_insDate <= ISNULL(@Date, @getdate)
				) ADDRESSCHANGE
			where PC.Lpu_id = @Lpu_id
			and PC.LpuAttachType_id = 1
			and PC.PersonCard_IsAttachAuto is null
			and ISNULL(PLS.Polis_begDate, @getdate - 1) <= @getdate
			and ISNULL(PLS.Polis_endDate, @getdate + 1) > @getdate
			" . (!empty($data['Date_upload']) ? "and cast(ISNULL(PC.PersonCard_updDT, PC.PersonCard_insDT) as date) >= @Date" : "") . "
			" . (count($filterList) > 0 ? "and " . implode(' and ', $filterList) : "") . "
			
			union all

			select distinct
				PCA.PersonCardAttach_id as Field_id,
				'2' as Field_Type,
				PCA.Person_id as IDCASE, -- Уникальный идентификатор пациента 
				rtrim(upper(PS.Person_SurName)) as FAM, -- Фамилия
				rtrim(upper(PS.Person_FirName)) as IM, -- Имя
				isnull(rtrim(Upper(case when Replace(PS.Person_Secname,' ','')='---'  or PS.Person_Secname = '' then 'НЕТ' else PS.Person_Secname end)), 'НЕТ') as OT, -- Отчество
				convert(varchar(10), PS.Person_BirthDay, 120) as DR, -- Дата рождения застрахованного
				PS.Person_Snils as SNILS,
				DT.DocumentType_Code as DOCTYPE,
				D.Document_Ser as DOCSER,
				D.Document_Num as DOCNUM,
				convert(varchar(10), D.Document_begDate, 120) as DOCDT,
				null as TEL,
				[PI].Person_BDZCode as RZ,
				2 as SP_PRIK,
				1 as T_PRIK,
				convert(varchar(10), PCA.PersonCardAttach_setDate, 120) as DATE_1,
				case when ADDRESSCHANGE.PersonUAddress_id is not null then 1 else 0 end as N_ADR,
				right('00000000' + isnull(L.Lpu_f003mcod,'') + isnull(LB.LpuBuilding_Code, ''), 8) as KODPODR,
				case
					when LR.LpuRegionType_SysNick in ('ter', 'ped', 'vop') then
						case
							when LR.LpuRegionType_SysNick in ('ter', 'vop') then '1'
							when LR.LpuRegionType_SysNick in ('ped') then '2'
							else ''
						end +
						ISNULL(LR.LpuRegion_Name,'')
					else ''
				end as LPUUCH,
				ISNULL(MP.Person_Snils, '') as SSD,
				case when MSF.PostKind_id = 1 then 1 else 2 end as MEDRAB
			from
				PersonCardAttach PCA with (nolock)
				left join v_PersonState PS with (nolock) on PS.Person_id = PCA.Person_id
				left join v_Polis PLS with (nolock) on PLS.Polis_id = PS.Polis_id
				left join v_Lpu L with (nolock) on L.Lpu_id = PCA.Lpu_id
				left join v_Document D with (nolock) on D.Document_id = PS.Document_id
				left join v_DocumentType DT with (nolock) on DT.DocumentType_id = D.DocumentType_id
				left join v_LpuRegion LR with (nolock) on LR.LpuRegion_id = PCA.LpuRegion_id
				left join v_LpuSection LS with(nolock) on LS.LpuSection_id = LR.LpuSection_id
				left join v_LpuBuilding LB with(nolock) on LB.LpuBuilding_id = LS.LpuBuilding_id
				left join v_MedStaffFact MSF (nolock) on MSF.MedStaffFact_id = PCA.MedStaffFact_id
				left join v_MedPersonal MP (nolock) on MP.MedPersonal_id = MSF.MedPersonal_id
				outer apply (
					select top 1 Person_BDZCode
					from v_PersonInfo with (nolock)
					where Person_id = PS.Person_id
				) [PI]
				outer apply (
					select top 1
						pua.PersonUAddress_id
					from
						v_PersonUAddress pua (nolock)
					where
						pua.Person_id = pca.Person_id
						and pua.PersonUAddress_insDate >= PCA.PersonCardAttach_setDate
						and pua.PersonUAddress_insDate <= ISNULL(@Date, @getdate)
				) ADDRESSCHANGE
			where PCA.Lpu_id = @Lpu_id
			and ISNULL(PLS.Polis_begDate, @getdate - 1) <= @getdate
			and ISNULL(PLS.Polis_endDate, @getdate + 1) > @getdate
			" . (!empty($data['Date_upload']) ? "and cast(ISNULL(PCA.PersonCardAttach_updDT, PCA.PersonCardAttach_insDT) as date) >= @Date" : "") . "
			" . (count($filterList) > 0 ? "and " . implode(' and ', $filterList) : "") . "
			and not exists (
				select top 1 PersonCard_id from v_PersonCard_all PC where PC.PersonCardAttach_id = PCA.PersonCardAttach_id
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
				outer apply (
					select top 1 Orgsmo_f002smocod
					from v_OrgSMO with (nolock)
					where OrgSMO_id = :OrgSMO_id
				) smo
			";
		}
		else {
			$smoCodeField = "30";
			$outer_apply = "";
		}

		$query = "
			select top 1
				l.Lpu_f003mcod as N_REESTR,
				{$smoCodeField} as SMO_CODE
			from v_Lpu l with (nolock)
				{$outer_apply}
			where l.Lpu_id = :Lpu_id
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
			$filter .= " and PS.Person_SurName like :Person_SurName + '%'";
			$params['Person_SurName'] = rtrim($data['Person_SurName']);
		}
		
		if( !empty($data['Person_FirName']) ) {
			$filter .= " and PS.Person_FirName like :Person_FirName + '%'";
			$params['Person_FirName'] = rtrim($data['Person_FirName']);
		}
		
		if( !empty($data['Person_SecName']) ) {
			$filter .= " and PS.Person_SecName like :Person_SecName + '%'";
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
			$filter .= " and cast(PCA.PersonCardAttach_setDate as date) >= :betAttachDate";
			$params['betAttachDate'] = $data['PersonCardAttach_setDate_Range'][0];
		}
		if(isset($data['PersonCardAttach_setDate_Range'][1])){
			$filter .= " and cast(PCA.PersonCardAttach_setDate as date) <= :endAttachDate";
			$params['endAttachDate'] = $data['PersonCardAttach_setDate_Range'][1];
		}
		if( !empty($data['RecMethodType_id']) ) {
			$filter .= " and RMT.RecMethodType_id = :RecMethodType_id ";
			$params['RecMethodType_id'] = rtrim($data['RecMethodType_id']);
		}
		
		$query = "
			select
				--select
				s.*
				--end select
			from
				--from
				(
					select
						PCA.PersonCardAttach_id,
						PCA.PersonCardAttach_setDate as PersonCardAttach_setDate2,
						convert(varchar, cast(PCA.PersonCardAttach_setDate as datetime),104) as PersonCardAttach_setDate,
						ISNULL(PS.Person_SurName,'') + ' ' + ISNULL(PS.Person_FirName,'') + ' ' + ISNULL(PS.Person_Secname,'') as Person_FIO,
						LPU_N.Lpu_Nick as Lpu_N_Nick,
						LPU_O.Lpu_Nick as Lpu_O_Nick,
						LPU_N.Lpu_id,
						PS.Person_id,
						PCAST.PersonCardAttachStatusType_id,
						PCAST.PersonCardAttachStatusType_Code,
						PCAST.PersonCardAttachStatusType_Name,
						LRT.LpuRegionType_Name,
						LR.LpuRegion_Name,
						ISNULL(MSF.Person_SurName,'') + ' ' + ISNULL(MSF.Person_FirName,'') + ' ' + ISNULL(MSF.Person_Secname,'') as MSF_FIO,
						fapLR.LpuRegion_id as LpuRegion_fapid,
						fapLR.LpuRegion_Name as LpuRegion_fapName,
						case when PC.PersonCard_id is null then 'false' else 'true' end as HasPersonCard,
						RMT.RecMethodType_Name,
						A.Address_Address
					from v_PersonCardAttach PCA (nolock)
					inner join v_PersonState PS (nolock) on PS.Person_id = PCA.Person_id
					left join v_RecMethodType RMT (nolock) on RMT.RecMethodType_id = PCA.RecMethodType_id
					left join v_Address A (nolock) on A.Address_id = PCA.Address_id
					left join v_Lpu LPU_N (nolock) on LPU_N.Lpu_id = PCA.Lpu_aid
					left join v_Lpu LPU_O (nolock) on LPU_O.Lpu_id = PCA.Lpu_id
				
					outer apply
					(
						select top 1 PCAS.PersonCardAttachStatus_id,
						PersonCardAttachStatusType_id
						from v_PersonCardAttachStatus PCAS (nolock)
						where PCAS.PersonCardAttach_id = PCA.PersonCardAttach_id
						order by PersonCardAttachStatus_setDate desc
					) PCAS
					inner join PersonCardAttachStatusType PCAST (nolock) on PCAST.PersonCardAttachStatusType_id = PCAS.PersonCardAttachStatusType_id
					inner join v_LpuRegion LR (nolock) on LR.LpuRegion_id = PCA.LpuRegion_id
					inner join v_LpuRegionType LRT (nolock) on LRT.LpuRegionType_id = LR.LpuRegionType_id
					left join PersonCard PC (nolock) on PC.PersonCardAttach_id = PCA.PersonCardAttach_id
					left join v_MedStaffFact MSF (nolock) on MSF.MedStaffFact_id = PCA.MedStaffFact_id
					left join v_LpuRegion fapLR with(nolock) on fapLR.LpuRegion_id = PCA.LpuRegion_fapid
					where PCA.LpuRegion_id is not null
					{$filter}

					union --Костылина, т.к. старые заявления не имеют ни участка, ни персона, ни врача (проверяется по LpuRegion_id - если его нет, значит это старое заявление)
					select 
						PCA.PersonCardAttach_id,
						PCA.PersonCardAttach_setDate as PersonCardAttach_setDate2,
						convert(varchar, cast(PCA.PersonCardAttach_setDate as datetime),104) as PersonCardAttach_setDate,
						ISNULL(PS.Person_SurName,'') + ' ' + ISNULL(PS.Person_FirName,'') + ' ' + ISNULL(PS.Person_Secname,'') as Person_FIO,
						LPU_N.Lpu_Nick as Lpu_N_Nick,
						LPU_O.Lpu_Nick as Lpu_O_Nick,
						LPU_N.Lpu_id,
						PS.Person_id,
						PCAST.PersonCardAttachStatusType_id,
						PCAST.PersonCardAttachStatusType_Code,
						PCAST.PersonCardAttachStatusType_Name,
						LRT.LpuRegionType_Name,
						LR.LpuRegion_Name,
						ISNULL(MSF.Person_SurName,'') + ' ' + ISNULL(MSF.Person_FirName,'') + ' ' + ISNULL(MSF.Person_Secname,'') as MSF_FIO,
						fapLR.LpuRegion_id as LpuRegion_fapid,
						fapLR.LpuRegion_id as LpuRegion_fapName,
						'true' as HasPersonCard,
						RMT.RecMethodType_Name,
						A.Address_Address
					from v_PersonCardAttach PCA
					cross apply
					(
						select top 1 PCard.PersonCard_id,
						PCard.LpuRegion_id,
						PCard.LpuRegion_fapid,
						PCard.Lpu_id,
						PCard.MedStaffFact_id,
						PCard.Person_id
						from v_PersonCard_all PCard (nolock)
						where PCard.PersonCardAttach_id = PCA.PersonCardAttach_id
					) PC
					left join v_Lpu LPU_N (nolock) on LPU_N.Lpu_id = PCA.Lpu_aid
					left join v_Lpu LPU_O (nolock) on LPU_O.Lpu_id = PCA.Lpu_id
					inner join v_LpuRegion LR (nolock) on LR.LpuRegion_id = PC.LpuRegion_id
					inner join v_LpuRegionType LRT (nolock) on LRT.LpuRegionType_id = LR.LpuRegionType_id
					left join v_MedStaffFact MSF (nolock) on MSF.MedStaffFact_id = PC.MedStaffFact_id
					left join v_LpuRegion fapLR with(nolock) on fapLR.LpuRegion_id = PC.LpuRegion_fapid
					left join v_RecMethodType RMT (nolock) on RMT.RecMethodType_id = PCA.RecMethodType_id
					left join v_Address A (nolock) on A.Address_id = PCA.Address_id
					inner join v_PersonState PS (nolock) on PS.Person_id = PC.Person_id
					outer apply
					(
						select top 1 PCAS.PersonCardAttachStatus_id,
						PersonCardAttachStatusType_id
						from v_PersonCardAttachStatus PCAS (nolock)
						where PCAS.PersonCardAttach_id = PCA.PersonCardAttach_id
						order by PersonCardAttachStatus_setDate desc
					) PCAS
					inner join PersonCardAttachStatusType PCAST (nolock) on PCAST.PersonCardAttachStatusType_id = PCAS.PersonCardAttachStatusType_id

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
				PersonCardAttach_setDate2 desc
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
				PC.PersonCard_id,
				ISNULL(PS.Person_SurName,'') + ' ' + ISNULL(PS.Person_FirName,'') + ' ' + ISNULL(PS.Person_Secname,'') as Person_FIO,
				LR.LpuRegion_Name,
				LRT.LpuRegionType_Name
			from v_PersonCard PC (nolock)
			left join v_LpuRegion LR (nolock) on LR.LpuRegion_id = PC.LpuRegion_id
			left join v_LpuRegionType LRT (nolock) on LRT.LpuRegionType_id = LR.LpuRegionType_id
			left join v_PersonState PS (nolock) on PS.Person_id = PC.Person_id
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
			select top 1
				PCA.PersonCardAttach_id,
				PCA.Lpu_aid,
				convert(varchar(10), PCA.PersonCardAttach_setDate, 104) as PersonCardAttach_setDate,
				ISNULL(PCA.Person_id, PS.Person_id) as Person_id,
				PCAS.PersonCardAttachStatus_id,
				ISNULL(LR.LpuRegion_id, LR2.LpuRegion_id) as LpuRegion_id,
				ISNULL(fLR.LpuRegion_id, fLR2.LpuRegion_id) as LpuRegion_fapid,
				COALESCE(LR.LpuRegionType_id, LR2.LpuRegionType_id, PCA.LpuRegionType_id) as LpuRegionType_id,
				ISNULL(PCA.MedStaffFact_id, PC.MedStaffFact_id) as MedStaffFact_id,
				PAC.PersonAmbulatCard_id,
				rtrim(rtrim(ISNULL(PAC.PersonAmbulatCard_Num,PC.PersonCard_Code))) as PersonCard_Code,
				PCA.PersonCardAttach_ExpNameFile,
				PCA.PersonCardAttach_ExpNumRow
			from
				v_PersonCardAttach PCA with(nolock)
				outer apply (
					select top 1 PCAS.PersonCardAttachStatus_id,
					PersonCardAttachStatusType_id
					from v_PersonCardAttachStatus PCAS (nolock)
					where PCAS.PersonCardAttach_id = PCA.PersonCardAttach_id
					order by PersonCardAttachStatus_setDate desc
				) PCAS
				left join v_LpuRegion LR (nolock) on LR.LpuRegion_id = PCA.LpuRegion_id
				left join v_LpuRegion fLR (nolock) on fLR.LpuRegion_id = PCA.LpuRegion_fapid
				--left join v_LpuRegionType LRT (nolock) on LRT.LpuRegionType_id = LR.LpuRegionType_id
				left join v_PersonCard_all PC (nolock) on PC.PersonCardAttach_id = PCA.PersonCardAttach_id
				left join v_PersonState PS on PS.Person_id = PC.Person_id
				left join v_LpuRegion LR2 (nolock) on LR2.LpuRegion_id = PC.LpuRegion_id
				left join v_LpuRegion fLR2 (nolock) on fLR2.LpuRegion_id = PC.LpuRegion_fapid
				left join v_PersonAmbulatCardLink PACL (nolock) on PACL.PersonCard_id = PC.PersonCard_id
				left join v_PersonAmbulatCard PAC (nolock) on PAC.PersonAmbulatCard_id = ISNULL(PCA.PersonAmbulatCard_id,PACL.PersonAmbulatCard_id)
			where
				PCA.PersonCardAttach_id = :PersonCardAttach_id
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
			'pmUser_id' 					=> $data['pmUser_id']
		);
		if (empty($data['PersonCardAttach_id'])) {
			$procedure = 'p_PersonCardAttach_ins';
		} else {
			$procedure = 'p_PersonCardAttach_upd';
		}

		$this->beginTransaction();

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :PersonCardAttach_id;
			exec {$procedure}
				@PersonCardAttach_id = @Res output,
				@PersonCardAttach_setDate = :PersonCardAttach_setDate,
				@Lpu_id = :Lpu_id,
				@Lpu_aid = :Lpu_aid,
				@Person_id = :Person_id,
				@PersonAmbulatCard_id = :PersonAmbulatCard_id,
				@LpuRegion_id = :LpuRegion_id,
				@LpuRegion_fapid = :LpuRegion_fapid,
				@LpuRegionType_id = :LpuRegionType_id,
				@MedStaffFact_id = :MedStaffFact_id,
				@Address_id = null,
				@Polis_id = null,
				@PersonCardAttach_IsSMS = :PersonCardAttach_IsSMS,
				@PersonCardAttach_SMS = :PersonCardAttach_SMS,
				@PersonCardAttach_IsEmail = :PersonCardAttach_IsEmail,
				@PersonCardAttach_Email = :PersonCardAttach_Email,
				@PersonCardAttach_IsHimself = :PersonCardAttach_IsHimself,
				@PersonCardAttach_ExpNameFile = :PersonCardAttach_ExpNameFile,
				@PersonCardAttach_ExpNumRow = :PersonCardAttach_ExpNumRow,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as PersonCardAttach_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
			select top 1 
				PC.PersonCard_id,
				convert(varchar(10), PCA.PersonCardAttach_setDate, 104) as PersonCardAttach_setDate,
				ISNULL(LR.LpuRegion_Name,'') as LpuRegion_Name,
				ISNULL(LRT.LpuRegionType_Name,'') as LpuRegionType_Name,
				ISNULL(L.Lpu_Nick,'') as Lpu_Nick,
				ISNULL(PS.Person_SurName,'') + ' ' + ISNULL(PS.Person_FirName,'') + ' ' + ISNULL(PS.Person_Secname,'') as Person_FIO
			from v_PersonCard_all PC (nolock)
			left join v_PersonState PS (nolock) on PS.Person_id = PC.Person_id
			left join v_PersonCardAttach PCA (nolock) on PCA.PersonCardAttach_id = PC.PersonCardAttach_id
			left join v_LpuRegion LR (nolock) on LR.LpuRegion_id = PCA.LpuRegion_id
			left join v_LpuRegionType LRT (nolock) on LRT.LpuRegionType_id = LR.LpuRegionType_id
			left join v_Lpu L (nolock) on L.Lpu_id = PCA.Lpu_aid
			where PC.PersonCardAttach_id = :PersonCardAttach_id
		";
		$resultCheck = $this->db->query($queryCheck, $params);
		if(!is_object($resultCheck))
		{
			$query = "
			update dbo.PersonCardAttachStatus with (ROWLOCK) set
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
					update dbo.PersonCardAttachStatus with (ROWLOCK) set
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
	 * Отказ в прикреплении
	 */
	function cancelPersonCardAttach($data) {
		$params = array(
			'PersonCardAttach_id' => $data['PersonCardAttach_id'],
			'PersonCardAttach_CancelReason' => $data['PersonCardAttach_CancelReason'],
			'pmUser_id' => $data['pmUser_id']
		);
		$res_Str = array('success'=>true,'string'=>'');
		
		$queryCheck = "
			select 
				PCAS.PersonCardAttachStatusType_id,
				ISNULL(PS.Person_SurName,'') + ' ' + ISNULL(PS.Person_FirName,'') + ' ' + ISNULL(PS.Person_Secname,'') as Person_FIO
			from v_PersonCardAttach PCA (nolock)
			outer apply
			(
				select top 1 PCAS.PersonCardAttachStatus_id,
				PersonCardAttachStatusType_id
				from v_PersonCardAttachStatus PCAS (nolock)
				where PCAS.PersonCardAttach_id = PCA.PersonCardAttach_id
				order by PersonCardAttachStatus_setDate desc
			) PCAS
			inner join PersonCardAttachStatusType PCAST (nolock) on PCAST.PersonCardAttachStatusType_id = PCAS.PersonCardAttachStatusType_id
			left join v_PersonState PS (nolock) on PS.Person_id = PCA.Person_id
			where PCA.PersonCardAttach_id = :PersonCardAttach_id
		";
		$resultCheck = $this->db->query($queryCheck, $params);
		if(is_object($resultCheck)) {
			$resultCheck = $resultCheck->result('array');
			if(count($resultCheck) > 0 && $resultCheck[0]['PersonCardAttachStatusType_id']==23)
			{
				$res_Str['string'] = 'Пациент '.$resultCheck[0]['Person_FIO'].' отказался от заявления о прикреплении';
				return $res_Str;
			}
		}
		
		$queryCheck = "
			select top 1 
				PC.PersonCard_id,
				PCAST.PersonCardAttachStatusType_Code,
				convert(varchar(10), PCA.PersonCardAttach_setDate, 104) as PersonCardAttach_setDate,
				ISNULL(LR.LpuRegion_Name,'') as LpuRegion_Name,
				ISNULL(LRT.LpuRegionType_Name,'') as LpuRegionType_Name,
				ISNULL(L.Lpu_Nick,'') as Lpu_Nick,
				ISNULL(PS.Person_SurName,'') + ' ' + ISNULL(PS.Person_FirName,'') + ' ' + ISNULL(PS.Person_Secname,'') as Person_FIO
			from v_PersonCard_all PC (nolock)
			left join v_PersonState PS (nolock) on PS.Person_id = PC.Person_id
			left join v_PersonCardAttach PCA (nolock) on PCA.PersonCardAttach_id = PC.PersonCardAttach_id
			left join v_PersonCardAttachStatus PCAS (nolock) on PCAS.PersonCardAttach_id = PCA.PersonCardAttach_id
			left join v_PersonCardAttachStatusType PCAST (nolock) on PCAST.PersonCardAttachStatusType_id = PCAS.PersonCardAttachStatusType_id
			left join v_LpuRegion LR (nolock) on LR.LpuRegion_id = PCA.LpuRegion_id
			left join v_LpuRegionType LRT (nolock) on LRT.LpuRegionType_id = LR.LpuRegionType_id
			left join v_Lpu L (nolock) on L.Lpu_id = PCA.Lpu_aid
			where PC.PersonCardAttach_id = :PersonCardAttach_id
		";
		$resultCheck = $this->db->query($queryCheck, $params);
		$resultCheck = null;
		if(!is_object($resultCheck))
		{
			$query = "
			update dbo.PersonCardAttachStatus with (ROWLOCK) set
				PersonCardAttachStatusType_id = 24,
				pmUser_updID = :pmUser_id,
				PersonCardAttachStatus_updDT = GetDate()
			where PersonCardAttach_id = :PersonCardAttach_id
				
			update dbo.PersonCardAttach with (ROWLOCK) set
				PersonCardAttach_CancelReason = :PersonCardAttach_CancelReason
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
					update dbo.PersonCardAttachStatus with (ROWLOCK) set
						PersonCardAttachStatusType_id = 24,
						pmUser_updID = :pmUser_id,
						PersonCardAttachStatus_updDT = GetDate()
					where PersonCardAttach_id = :PersonCardAttach_id
						
					update dbo.PersonCardAttach with (ROWLOCK) set
						PersonCardAttach_CancelReason = :PersonCardAttach_CancelReason
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
			select top 1 PC.PersonCardAttach_id
			from v_PersonCard_all PC (nolock)
			where PC.PersonCard_id = :PersonCard_id
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
					update dbo.PersonCardAttachStatus with (ROWLOCK) set
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
			select top 1 
				PC.PersonCard_id,
				convert(varchar(10), PCA.PersonCardAttach_setDate, 104) as PersonCardAttach_setDate,
				ISNULL(LR.LpuRegion_Name,'') as LpuRegion_Name,
				ISNULL(LRT.LpuRegionType_Name,'') as LpuRegionType_Name,
				ISNULL(L.Lpu_Nick,'') as Lpu_Nick,
				ISNULL(PS.Person_SurName,'') + ' ' + ISNULL(PS.Person_FirName,'') + ' ' + ISNULL(PS.Person_Secname,'') as Person_FIO
			from v_PersonCard_all PC (nolock)
			left join v_PersonState PS (nolock) on PS.Person_id = PC.Person_id
			left join v_PersonCardAttach PCA (nolock) on PCA.PersonCardAttach_id = PC.PersonCardAttach_id
			left join v_LpuRegion LR (nolock) on LR.LpuRegion_id = PCA.LpuRegion_id
			left join v_LpuRegionType LRT (nolock) on LRT.LpuRegionType_id = LR.LpuRegionType_id
			left join v_Lpu L (nolock) on L.Lpu_id = PCA.Lpu_aid
			where PC.PersonCardAttach_id = :PersonCardAttach_id
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
			PCAS.PersonCardAttachStatusType_id,
			PCAS.PersonCardAttachStatusType_Code,
			PCAS.PersonCardAttachStatusType_Name,
			convert(varchar(10), PCA.PersonCardAttach_setDate, 104) as PersonCardAttach_setDate,
			ISNULL(PS.Person_SurName,'') + ' ' + ISNULL(PS.Person_FirName,'') + ' ' + ISNULL(PS.Person_Secname,'') as Person_FIO
		from
			v_PersonCardAttach PCA with(nolock)
			left join v_PersonState PS (nolock) on PS.Person_id = PCA.Person_id
			outer apply (
				select top 1 PCAST.PersonCardAttachStatusType_id, PCAST.PersonCardAttachStatusType_Code, PCAST.PersonCardAttachStatusType_Name
				from v_PersonCardAttachStatus PCAS (nolock)
				left join PersonCardAttachStatusType PCAST (nolock) on PCAST.PersonCardAttachStatusType_id = PCAS.PersonCardAttachStatusType_id
				where PCAS.PersonCardAttach_id = PCA.PersonCardAttach_id
				order by PCAS.PersonCardAttachStatusType_id desc
			) PCAS
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
				PCA.Lpu_aid as Lpu_id,
				PCA.Person_id,
				PCA.LpuRegion_id,
				PCA.MedStaffFact_id,
				PCA.LpuRegion_fapid,
				ISNULL(PCA.PersonAmbulatCard_id,0) as PersonAmbulatCard_id,
				ISNULL(PAC.PersonAmbulatCard_Num,'') as PersonAmbulatCard_Code,
				PCA.Address_id as PersonCardAttach_Address_id,
				PS.PAddress_id as PersonState_Address_id
			from v_PersonCardAttach PCA (nolock)
			left join v_PersonAmbulatCard PAC (nolock) on PAC.PersonAmbulatCard_id = PCA.PersonAmbulatCard_id
			left join v_PersonState PS (nolock) on PS.Person_id = PCA.Person_id
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
				'PersonCardAttach_Address_id' => $resultAttach[0]['PersonCardAttach_Address_id'],
				'pmUser_id' => $data['pmUser_id']
			);
			$this->savePersonCardAttachStatus(array(
					'PersonCardAttach_id' => $data['PersonCardAttach_id']
					,'PersonCardAttachStatusType_id' => 25
					//~ ,'PersonCardAttachStatus_setDate' => $data['PersonCardAttach_setDate']
					,'pmUser_id' => $data['pmUser_id']
				));
			if($resultAttach[0]['PersonCardAttach_Address_id'] != $resultAttach[0]['PersonState_Address_id'] && !empty($resultAttach[0]['PersonCardAttach_Address_id'])) {
				$queryChangeAddress = "
					update PersonState set PAddress_id = :PersonCardAttach_Address_id where Person_id = :Person_id
				";
				$this->db->query($queryChangeAddress,$params);
			}
			if($resultAttach[0]['PersonAmbulatCard_id'] == 0){ //Если не указана амбулаторная карта, то берем последнюю у пациента, либо создаем новую
				$query_SearchAmbulatCard = "
					select top 1 PersonAmbulatCard_Num
					from v_PersonAmbulatCard
					where Person_id = :Person_id
					order by PersonAmbulatCard_id desc
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
                        declare
                            @Res bigint,
                            @ErrCode int,
                            @time datetime,
                            @ErrMessage varchar(4000);

                        set @Res = :PersonAmbulatCard_id;
                        set @time = (select dbo.tzGetDate());
                        exec p_PersonAmbulatCard_ins
                            @Server_id = :Server_id,
                            @PersonAmbulatCard_id = @Res output,
                            @Person_id = :Person_id,
                            @PersonAmbulatCard_Num = :PersonAmbulatCard_Num,
                            @Lpu_id = :Lpu_id,
                            @PersonAmbulatCard_CloseCause =:PersonAmbulatCard_CloseCause,
                            @PersonAmbulatCard_endDate = :PersonAmbulatCard_endDate,
                            @PersonAmbulatCard_begDate = @time,
                            @pmUser_id = :pmUser_id,
                            @Error_Code = @ErrCode output,
                            @Error_Message = @ErrMessage output;

                        select @Res as PersonAmbulatCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
                            declare
                                @Res bigint,
                                @ErrCode int,
                                @ErrMessage varchar(4000);

                            set @Res = :PersonAmbulatCardLocat_id;
                            exec p_PersonAmbulatCardLocat_ins
                                @Server_id = :Server_id,
                                @PersonAmbulatCardLocat_id = @Res output,
                                @PersonAmbulatCard_id = :PersonAmbulatCard_id,
                                @AmbulatCardLocatType_id = :AmbulatCardLocatType_id,
                                @MedStaffFact_id = :MedStaffFact_id,
                                @PersonAmbulatCardLocat_begDate = :PersonAmbulatCardLocat_begDate,
                                @PersonAmbulatCardLocat_Desc = :PersonAmbulatCardLocat_Desc,
                                @PersonAmbulatCardLocat_OtherLocat =:PersonAmbulatCardLocat_OtherLocat,
                                @pmUser_id = :pmUser_id,
                                @Error_Code = @ErrCode output,
                                @Error_Message = @ErrMessage output;

                            select @Res as PersonAmbulatCardLocat_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
                        ";
                        $result_PersonAmbulatCardLocat = $this->db->query($query_PersonAmbulatCardLocat,$params_PersonAmbulatCardLocat);
                    }
				}
			}
			
			$procedure = 'p_PersonCard_ins';
			$resultPersonCard = array();
			//Проверим, а есть ли у этого пациента активное прикрепление
			$queryPersonCard = "
				select top 1 *
				from v_PersonCard (nolock)
				where Person_id = :Person_id
				and LpuAttachType_id = 1
				order by PersonCard_begDate desc
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
						select top 1 CONVERT(varchar(10), PersonCardAttach_setDate, 120) as setDate 
						from v_PersonCardAttach
						where PersonCardAttach_id = :PersonCardAttach_id
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
						declare
							@Res bigint,
							@ErrCode int,
							@ErrMessage varchar(4000);
						set @Res = :PersonCard_id;
						exec p_PersonCard_upd
							@PersonCard_id = @Res output,
							@Lpu_id = :Lpu_id,
							@Server_id = :Server_id,
							@Person_id = :Person_id,
							@PersonCard_begDate = :BegDate,
							@PersonCard_endDate = :EndDate,
							@PersonCard_Code = :PersonCard_Code,
							@PersonCard_IsAttachCondit = :PersonCard_IsAttachCondit,
							@LpuRegion_id = :LpuRegion_id,
							@LpuRegion_fapid = :LpuRegion_Fapid,
							@LpuAttachType_id = :LpuAttachType_id,
							@CardCloseCause_id = :CardCloseCause_id,
							@PersonCardAttach_id = :PersonCardAttach_id,
							@MedStaffFact_id = :MedStaffFact_id,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;
						select @Res as PersonCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
						select CONVERT(varchar(10), PersonCardAttach_setDate, 120) as setDate 
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
                    declare
                        @Res bigint,
                        @ErrCode int,
                        @ErrMessage varchar(4000);
                    set @Res = null;
                    exec p_PersonCard_ins
                        @PersonCard_id = @Res output,
                        @Lpu_id = :Lpu_id,
                        @Server_id = :Server_id,
                        @Person_id = :Person_id,
                        @PersonCard_begDate = :PersonCard_begDate,
                        @PersonCard_Code = :PersonCard_Code,
                        @PersonCard_IsAttachCondit = :PersonCard_IsAttachCondit,
                        @PersonCard_IsAttachAuto = 2,
                        @LpuRegion_id = :LpuRegion_id,
                        @LpuRegion_fapid = :LpuRegion_Fapid,
                        @LpuAttachType_id = 1,
                        @CardCloseCause_id = null,
                        @PersonCardAttach_id = :PersonCardAttach_id,
                        @MedStaffFact_id = :MedStaffFact_id,
                        @pmUser_id = :pmUser_id,
                        @Error_Code = @ErrCode output,
                        @Error_Message = @ErrMessage output;
                    select @Res as PersonCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
			declare
				@ObjID bigint;
			exec xp_GenpmID 
				@ObjectName = 'PersonCard', 
				@Lpu_id = :Lpu_id,
				@ObjectID = @ObjID output;
			select @ObjID as PersonCard_Code;
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
			select top 1 Person_id
			from v_PersonState (nolock)
			where REPLACE(REPLACE(Person_Snils,'-',''),' ','') = REPLACE(REPLACE(:SNILS,'-',''),' ','')
			and Person_SurName = :FAM
			and Person_FirName = :IM
			and Person_SecName = :OT
			and Person_BirthDay = :DR
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
			select top 1 MP.Person_Fio
			from v_MedPersonal MP (nolock)
			inner join v_Lpu L (nolock) on L.Lpu_id = MP.Lpu_id
			where REPLACE(REPLACE(MP.Person_Snils,'-',''),' ','') = REPLACE(REPLACE('{$SSD}','-',''),' ','')
			and right('000000' + ISNULL(L.Lpu_f003mcod, ''), 6) = '{$LPUC}'
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
			$and_date = ' and convert(varchar(10), PC.PersonCard_endDate, 120) = :PersonCard_Date';
		}
		else //Прикрепление
		{
			$and_date = ' and convert(varchar(10), PC.PersonCard_begDate, 120) = :PersonCard_Date';
		}
		$query = "
			select top 1 PC.PersonCard_id
			from v_PersonCard_all PC (nolock)
			inner join v_PersonState PS (nolock) on PS.Person_id = PC.Person_id
			inner join v_Lpu (nolock) L on L.Lpu_id = PC.Lpu_id
			inner join v_LpuRegion LR (nolock) on LR.LpuRegion_id = PC.LpuRegion_id
			left join v_MedStaffFact MSF (nolock) on MSF.MedStaffFact_id = PC.MedStaffFact_id
			left join v_MedPersonal MP (nolock) on MP.MedPersonal_id = MSF.MedPersonal_id
			where (1=1)
			and PC.Person_id = :Person_id
			and right('000000' + ISNULL(L.Lpu_f003mcod, ''), 6) = :Lpu_Code
			and LR.LpuRegion_Name = :LpuRegion_Name
			and	replace(ltrim(replace(LR.LpuRegion_Name, '0', ' ')), ' ', 0) = replace(ltrim(replace(:LpuRegion_Name, '0', ' ')), ' ', 0)
			and (PC.MedStaffFact_id is null or REPLACE(REPLACE(MP.Person_Snils,'-',''),' ','') = REPLACE(REPLACE(:MedPersonal_Snils,'-',''),' ',''))
			{$and_date}
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
						select top 1 PCA.PersonCardAttach_id
						from v_PersonCardAttach PCA (nolock)
						left join v_PersonState PS (nolock) on PS.Person_id = PCA.Person_id
						left join v_Lpu (nolock) L on L.Lpu_id = PCA.Lpu_aid
						left join v_LpuRegion LR (nolock) on LR.LpuRegion_id = PCA.LpuRegion_id
						left join v_MedStaffFact MSF (nolock) on MSF.MedStaffFact_id = PCA.MedStaffFact_id
						left join v_MedPersonal MP (nolock) on MP.MedPersonal_id = MSF.MedPersonal_id
						where (1=1)
						and PCA.Person_id = :Person_id
						and right('000000' + ISNULL(L.Lpu_f003mcod, ''), 6) = :Lpu_Code
						and LR.LpuRegion_Name = :LpuRegion_Name
						and	replace(ltrim(replace(LR.LpuRegion_Name, '0', ' ')), ' ', 0) = replace(ltrim(replace(:LpuRegion_Name, '0', ' ')), ' ', 0)
						and (PCA.MedStaffFact_id is null or REPLACE(REPLACE(MP.Person_Snils,'-',''),' ','') = REPLACE(REPLACE(:MedPersonal_Snils,'-',''),' ',''))
						and convert(varchar(10), PCA.PersonCardAttach_setDate, 120) = :PersonCard_Date
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
			declare @Error_Code bigint = null
			declare @Error_Message varchar(4000) = ''
			declare @date datetime = (select top 1 dbo.tzGetDate())
			set nocount on
			begin try
				update 
					PersonCardAttach with(rowlock)
				set
					PersonCardAttach_ExpNumRow = null,
					PersonCardAttach_ExpNameFile = null,
					PersonCardAttach_updDT = @date,
					pmUser_updID = :pmUser_id
				where
					PersonCardAttach_ExpNameFile = :PersonCardAttach_ExpNameFile
			end try
			begin catch
				set @Error_Code = error_number()
				set @Error_Message = error_message()
			end catch
			set nocount off
			select @Error_Code as Error_Code, @Error_Message as Error_Msg
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
				from fed.MedSpec t with(nolock)
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
				PCA.PersonCardAttach_id as ID_ATTACH,
				ROW_NUMBER() over (order by PCA.PersonCardAttach_id) as N_ZAP,
				case when PCAST.PersonCardAttachStatusType_Code = 4
					then 1 else 0
				end as PR_NOV,
				PS.Person_id as ID_PAC,
				rtrim(PS.Person_SurName) as FAM,
				rtrim(PS.Person_FirName) as IM,
				rtrim(PS.Person_SecName) as OT,
				Sex.Sex_fedid as W,
				convert(varchar(10), PS.Person_BirthDay, 120) as DR,
				D.Document_Ser as DOCSER,
				D.Document_Num as DOCNUM,
				PT.PolisType_CodeF008 as VPOLIS,
				case when PT.PolisType_CodeF008 = 3 
					then PS.Person_EdNum else P.Polis_Num 
				end as NPOLIS,
				nullif(P.Polis_Ser, '') as SPOLIS,
				SMO.Orgsmo_f002smocod as SMO,
				convert(varchar(10), PCA.PersonCardAttach_setDate, 120) as DATEZ,
				2 as PRZ,
				null as REZ,
				null as DATEREZ,
				(
					left(MSF.Person_Snils, 3) + '-' + substring(MSF.Person_Snils, 4, 3) + '-' + 
					substring(MSF.Person_Snils, 7, 3) + ' ' + right(MSF.Person_Snils, 2)
				) as DOC_CODE,
				case when MS.MedSpec_rCode = '204'
					then 2 else 1
				end as DOC_POST,
				case when MSR.MedStaffRegion_endDate < PCA.PersonCardAttach_setDate
					then 0 else 1
				end as DOC_ACTUAL,
				null as COMENTZ
			from
				v_PersonCardAttach PCA with(nolock)
				inner join v_Lpu L with(nolock) on L.Lpu_id = PCA.Lpu_id
				cross apply (
					select top 1 PS.*
					from v_Person_all PS with(nolock)
					where PS.Person_id = PCA.Person_id
					and PS.PersonEvn_insDT <= PCA.PersonCardAttach_setDate
					order by PS.PersonEvn_insDT desc
				) PS
				left join v_Sex Sex with(nolock) on Sex.Sex_id = PS.Sex_id
				left join v_Document D with(nolock) on D.Document_id = PS.Document_id
				left join v_Polis P with(nolock) on P.Polis_id = PS.Polis_id
				left join v_PolisType PT with(nolock) on PT.PolisType_id = P.PolisType_id
				left join v_OrgSMO SMO with(nolock) on SMO.OrgSMO_id = P.OrgSMO_id
				left join v_MedStaffFact MSF with(nolock) on MSF.MedStaffFact_id = PCA.MedStaffFact_id
				left join v_MedSpecOms MSO with(nolock) on MSO.MedSpecOms_id = MSF.MedSpecOms_id
				left join MedSpecTree MS with(nolock) on MS.MedSpec_id = MSO.MedSpec_id
				outer apply (
					select top 1
						MSR.*
					from
						v_MedStaffRegion MSR with(nolock)
					where
						MSR.MedStaffFact_id = MSF.MedStaffFact_id
						and MSR.LpuRegion_id = PCA.LpuRegion_id
						and MSR.MedStaffRegion_begDate <= PCA.PersonCardAttach_setDate
					order by
						MSR.MedStaffRegion_isMain desc,
						MSR.MedStaffRegion_begDate desc
				) MSR
				outer apply (
					select top 1 PCAS.PersonCardAttachStatusType_id
					from v_PersonCardAttachStatus PCAS with(nolock)
					where PCAS.PersonCardAttach_id = PCA.PersonCardAttach_id
					order by PCAS.PersonCardAttachStatus_setDate desc
				) PCAS
				left join v_PersonCardAttachStatusType PCAST with(nolock) on PCAST.PersonCardAttachStatusType_id = PCAS.PersonCardAttachStatusType_id
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
			declare @date date = dbo.tzGetDate()
			select
				L.Lpu_f003mcod as CODE_MO,
				SMO.Orgsmo_f002smocod as SMO,
				convert(varchar(10), @date, 120) as DATE,
				year(@date) as YEAR,
				month(@date) as MONTH
			from
				(select 1 as a) t
				inner join v_Lpu L with(nolock) on L.Lpu_id = :Lpu_aid
				inner join v_OrgSMO SMO with(nolock) on SMO.OrgSMO_id = :OrgSMO_id
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
			$logfilename = $filename . '_log.txt';

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
			select top 1 Lpu_id from v_Lpu with(nolock) where Lpu_f003mcod = :Lpu_f003mcod
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
				PersonCardAttach_id,
				PersonCardAttach_ExpNumRow
			from
				v_PersonCardAttach PCA with(nolock)
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
			select top 1 Lpu_id from v_Lpu with(nolock) where Lpu_f003mcod = :Lpu_f003mcod
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
					select top 1
						PCA.PersonCardAttach_id
					from
						v_PersonCardAttach PCA with(nolock)
						cross apply (
							select top 1 PS.*
							from v_Person_all PS with(nolock)
							where PS.Person_id = PCA.Person_id
							and PS.PersonEvn_insDT <= PCA.PersonCardAttach_setDate
							order by PS.PersonEvn_insDT desc
						) PS
						left join v_Polis P with(nolock) on P.Polis_id = PS.Polis_id
					where
						PCA.PersonCardAttach_setDate = :DATEZ
						and PS.Person_SurName = :FAM
						and PS.Person_FirName = :IM
						and isnull(PS.Person_SecName, '') = isnull(:OT, '')
						and PS.Person_BirthDay = :DR
						and (P.Polis_Num = :NPOLIS or PS.Person_EdNum = :NPOLIS)
					order by
						PCA.PersonCardAttach_insDT desc
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
			select top 1 Lpu_id from v_Lpu with(nolock) where Lpu_f003mcod = :Lpu_f003mcod
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
						PS.Person_id
					from
						v_PersonState PS with(nolock)
					where
						PS.Person_SurName = :FAM
						and PS.Person_FirName = :IM
						and isnull(PS.Person_SecName, '') = isnull(:OT, '')
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
			declare
				@date date = :date,
				@prz int = :prz;
			with PRZ as (
				select 1 as PRZ_Code, 'Не является застрахованным' as PRZ_Name, 8 as CardCloseCause_Code
				union select 2 as PRZ_Code, 'Умерший' as PRZ_Name, 2 as CardCloseCause_Code
				union select 3 as PRZ_Code, 'Прикреплен к другой МО' as PRZ_Name, 1 as CardCloseCause_Code
				union select 4 as PRZ_Code, 'Смена МО по возрастному принципу' as PRZ_Name, 3 as CardCloseCause_Code
				union select 5 as PRZ_Code, 'Изменение территориального деления' as PRZ_Name, 8 as CardCloseCause_Code
			)
			select top 1
				PC.PersonCard_id,
				PC.Person_id,
				PC.Lpu_id,
				PC.LpuRegion_id,
				PC.LpuAttachType_id,
				PC.PersonCard_Code,
				convert(varchar(10), PC.PersonCard_begDate, 120) as PersonCard_begDate,
				convert(varchar(10), dateadd(day, -1, @date), 120) as PersonCard_endDate,
				CCC.CardCloseCause_id,
				PRZ.PRZ_Name,
				PC.PersonCard_IsAttachCondit,
				PC.OrgSMO_id,
				PC.PersonCardAttach_id,
				PC.LpuRegion_fapid,
				PC.LpuRegionType_id,
				PC.MedStaffFact_id
			from
				v_PersonCard PC with(nolock)
				left join v_LpuAttachType LAT with(nolock) on LAT.LpuAttachType_id = PC.LpuAttachType_id
				left join PRZ with(nolock) on PRZ.PRZ_Code = :prz
				left join v_CardCloseCause CCC with(nolock) on CCC.CardCloseCause_Code = PRZ.CardCloseCause_Code
			where
				PC.Lpu_id = :Lpu_id
				and PC.Person_id = :Person_id
				and PC.PersonCard_begDate < :date
				and PC.PersonCard_endDate is null
				and LAT.LpuAttachType_SysNick = 'main'
			order by
				PC.PersonCard_begDate desc
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
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :PersonCard_id;
			exec p_PersonCard_upd
				@PersonCard_id = @Res output,
				@Person_id = :Person_id,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonCard_begDate = :PersonCard_begDate,
				@PersonCard_endDate = :PersonCard_endDate,
				@PersonCard_Code = :PersonCard_Code,
				@PersonCard_IsAttachCondit = :PersonCard_IsAttachCondit,
				@OrgSMO_id = :OrgSMO_id,
				@LpuRegion_id = :LpuRegion_id,
				@LpuRegion_fapid = :LpuRegion_fapid,
				@LpuAttachType_id = :LpuAttachType_id,
				@CardCloseCause_id = :CardCloseCause_id,
				@PersonCardAttach_id = :PersonCardAttach_id,
				@LpuRegionType_id = :LpuRegionType_id,
				@MedStaffFact_id = :MedStaffFact_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as PersonCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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

		$convertRecord = function($record) use($struct) {
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
			select top 1 rtrim(Lpu_OGRN) as Lpu_OGRN 
			from v_Lpu with(nolock) where Lpu_id = :Lpu_id
		", array(
			'Lpu_id' => $data['Lpu_id']
		));
		if (empty($Lpu_OGRN)) {
			return $this->createError('','Ошибка при получении ОГРН текущей МО');
		}
		for ($i = 1; $i <= $count; $i++) {
			$item = $convertRecord(dbase_get_record_with_names($dbf, $i));
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
				$item = $convertRecord(dbase_get_record_with_names($dbf, $i));

				$PersonCard_begDate = !empty($item['DATA_ZVL'])?$item['DATA_ZVL']:$date;

				//Поиск человека
				$PersonList = $this->queryResult("
					declare
						@date date = :PersonCard_begDate;
					select
						PS.Person_id,
						dbo.Age2(PS.Person_BirthDay, @date) as Person_Age
					from
						v_PersonState PS with(nolock)
					where
						PS.Person_SurName = :FAM
						and PS.Person_FirName = :IM
						and isnull(PS.Person_SecName, '') = isnull(:OT, '')
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
					select top 1
						PC.PersonCard_id,
						MSF.Person_Snils as MedPersonal_Snils
					from
						v_PersonCard PC with(nolock)
						inner join v_LpuAttachType LAT with(nolock) on LAT.LpuAttachType_id = PC.LpuAttachType_id
						inner join v_LpuRegion LR with(nolock) on LR.LpuRegion_id = PC.LpuRegion_id
						inner join v_MedStaffFact MSF with(nolock) on MSF.MedStaffFact_id = PC.MedStaffFact_id
					where
						PC.Lpu_id = :Lpu_id
						and PC.Person_id = :Person_id
						and PC.PersonCard_endDate is null
						and LAT.LpuAttachType_SysNick = 'main'
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
					declare
						@Person_id bigint = :Person_id,
						@Person_Age int = :Person_Age,
						@Lpu_id bigint = :Lpu_id,
						@PersonCard_begDate date = :PersonCard_begDate,
						@MedPersonal_Snils varchar(11) = :MedPersonal_Snils;
					select top 1
						'add' as action,
						null as PersonCard_id,
						PrevPC.PersonCard_id as PrevPersonCard_id,
						PCA.PersonCardAttach_id,
						@Lpu_id as Lpu_id,
						@Person_id as Person_id,
						LAT.LpuAttachType_id,
						convert(varchar(10), @PersonCard_begDate, 120) as PersonCard_begDate,
						LR.LpuRegion_id,
						LR.LpuRegion_Name,
						LR.LpuRegionType_id,
						MSF.MedStaffFact_id,
						MSF.Person_Fio as MedPersonal_Fio
					from
						v_MedStaffRegion MSR with(nolock)
						inner join v_MedStaffFact MSF with(nolock) on MSF.MedStaffFact_id = MSR.MedStaffFact_id
						inner join v_LpuRegion LR with(nolock) on LR.LpuRegion_id = MSR.LpuRegion_id
						left join v_LpuAttachType LAT with(nolock) on LAT.LpuAttachType_SysNick = 'main'
						outer apply (
							select top 1
								PCA.PersonCardAttach_id
							from
								v_PersonCardAttach PCA with(nolock)
								left join v_PersonCard_all PC with(nolock) on PC.PersonCardAttach_id = PCA.PersonCardAttach_id
							where
								PCA.Person_id = @Person_id
								and PCA.MedStaffFact_id = MSF.MedStaffFact_id
								and PCA.LpuRegion_id = LR.LpuRegion_id
								and PC.PersonCard_id is null
							order by
								PCA.PersonCardAttach_setDate desc
						) PCA
						outer apply(
							select top 1 
								PC.PersonCard_id
							from 
								v_PersonCard PC with(nolock)
							where 
								PC.Person_id = @Person_id
								and PC.LpuAttachType_id = LAT.LpuAttachType_id
								and PC.PersonCard_endDate is null
							order by 
								PC.PersonCard_begDate desc
						) PrevPC
					where
						MSF.Lpu_id = @Lpu_id
						and MSF.Person_Snils = @MedPersonal_Snils
						and MSR.MedStaffRegion_begDate <= @PersonCard_begDate
						and (MSR.MedStaffRegion_endDate is null or MSR.MedStaffRegion_endDate > @PersonCard_begDate)
						and LR.LpuRegionType_SysNick in ('ter','ped','vop')
					order by
						case 
							when PCA.PersonCardAttach_id is not null then 1 
							else 0 
						end desc,
						case
							when LR.LpuRegionType_SysNick = 'vop' then 1
							when LR.LpuRegionType_SysNick = 'ter' and @Person_Age >= 18 then 1
							when LR.LpuRegionType_SysNick = 'ped' and @Person_Age < 18 then 1
							else 0
						end desc
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
			select top 1 PersonAmbulatCard_Num
			from v_PersonAmbulatCard
			where Person_id = :Person_id
			order by PersonAmbulatCard_id desc
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
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_PersonAmbulatCard_ins
				@Server_id = :Server_id,
				@PersonAmbulatCard_id = @Res output,
				@Person_id = :Person_id,
				@PersonAmbulatCard_Num = :PersonAmbulatCard_Num,
				@Lpu_id = :Lpu_id,
				@PersonAmbulatCard_CloseCause = :PersonAmbulatCard_CloseCause,
				@PersonAmbulatCard_begDate = :PersonAmbulatCard_begDate,
				@PersonAmbulatCard_endDate = :PersonAmbulatCard_endDate,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as PersonAmbulatCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_PersonAmbulatCardLocat_ins
				@Server_id = :Server_id,
				@PersonAmbulatCardLocat_id = @Res output,
				@PersonAmbulatCard_id = :PersonAmbulatCard_id,
				@AmbulatCardLocatType_id = :AmbulatCardLocatType_id,
				@MedStaffFact_id = :MedStaffFact_id,
				@PersonAmbulatCardLocat_begDate = :PersonAmbulatCardLocat_begDate,
				@PersonAmbulatCardLocat_Desc = :PersonAmbulatCardLocat_Desc,
				@PersonAmbulatCardLocat_OtherLocat =:PersonAmbulatCardLocat_OtherLocat,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as PersonAmbulatCardLocat_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
	*	Сохранение заявления о выборе МО
	*/
	function savePersonCardAttach($data) {
		$proc = "p_PersonCardAttach_" . (empty($data['PersonCardAttach_id']) ? "ins" : "upd");
		
		$data['PersonCardAttach_IsSMS'] = $data['PersonCardAttach_IsSMS']+1;
		$data['PersonCardAttach_IsEmail'] = $data['PersonCardAttach_IsEmail']+1;
		$data['PersonCardAttach_SMS'] = str_replace(' ','',substr($data['PersonCardAttach_SMS'],3));
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :PersonCardAttach_id;
			exec {$proc}
				@PersonCardAttach_id = @Res output,
				@PersonCardAttach_setDate = :PersonCardAttach_setDate,
				@Lpu_id = :Lpu_id,
				@Lpu_aid = :Lpu_aid,
				@Address_id = :Address_id,
				@Polis_id = :Polis_id,
				@Person_id = :Person_id,
				@PersonCardAttach_IsSMS = :PersonCardAttach_IsSMS,
				@PersonCardAttach_SMS = :PersonCardAttach_SMS,
				@PersonCardAttach_IsEmail = :PersonCardAttach_IsEmail,
				@PersonCardAttach_Email = :PersonCardAttach_Email,
				@PersonCardAttach_IsHimself = :PersonCardAttach_IsHimself,
				@RecMethodType_id = 16,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as PersonCardAttach_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		//		--@PersonAmbulatCard_id = :PersonAmbulatCard_id,
		//echo getDebugSql($query, $data); die();
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$result = $result->result('array');
			$statusTypes = array(25); //принято
			foreach($statusTypes as $statusType) {
				$this->savePersonCardAttachStatus(array(
					'PersonCardAttachStatus_id' => null
					,'PersonCardAttach_id' => $result[0]['PersonCardAttach_id']
					,'PersonCardAttachStatusType_id' => $statusType
					,'PersonCardAttachStatus_setDate' => $data['PersonCardAttach_setDate']
					,'pmUser_id' => $data['pmUser_id']
				));
			}
			return $result;
		} else {
			return false;
		}
	}
}
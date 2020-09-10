<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnDirectionCVI_model - модель
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 */

class EvnDirectionCVI_model extends swModel {

	/**
	 * Загрузка
	 */
	function load($data) {

		$kz_join = "";
		$kz_select = "";
		
		$where = "where edc.EvnDirectionCVI_id = :EvnDirectionCVI_id";
		
		if (getRegionNick() == 'kz') {
			$kz_join = "inner join r101.v_EvnDirectionCVILink edcvil (nolock) on edcvil.EvnDirectionCVI_id = edc.EvnDirectionCVI_id";
			$kz_select = "
				,edcvil.Lpu_id as EvnDirectionCVILink_ReceiverMoID
				,edcvil.CVIBiomaterial_id
				,edcvil.CVIOrderType_id
				,edcvil.CVISampleStatus_id
				,edcvil.CVIPurposeSurvey_id
				,edcvil.CVIStatus_id
				,edcvil.EvnDirectionCVILink_WorkPlace
				,edcvil.EvnDirectionCVILink_Address
				,edcvil.EvnDirectionCVILink_Phone
				,edcvil.EvnDirectionCVILink_PhonePersonal
				,edcvil.EvnDirectionCVILink_IsSymptom
				,edcvil.MedStaffFact_id
				,coalesce(edcvil.EvnDirectionCVILink_lisIsSuccess,1) as EvnDirectionCVILink_lisIsSuccess
			";
			
			//условие начитки для ЛИС
			if ($data == 'sendDirectionToLis') {
				$where = "where coalesce(edcvil.EvnDirectionCVILink_lisIsSuccess,1) != 2 and edc.DirFailType_id is null";
				$kz_join.="
					left join r101.v_GetMO gmos (nolock) on gmos.Lpu_id = edc.Lpu_id
					left join r101.v_GetMO gmor (nolock) on gmor.Lpu_id = edcvil.Lpu_id
					inner join v_PersonState ps (nolock) on ps.Person_id = edc.Person_id
					inner join v_Person p (nolock) on ps.Person_id = p.Person_id
					inner join v_MedStaffFact msf on msf.MedStaffFact_id = edcvil.MedStaffFact_id
					inner join v_Person pSpec on pSpec.Person_id = msf.Person_id
					inner join r101.CVIStatus cvis (nolock) on cvis.CVIStatus_id = edcvil.CVIStatus_id
					outer apply (
						select top 1 gp.PersonId
						from r101.v_GetPersonal (nolock) gp
							inner join r101.v_GetPersonalHistory gph (nolock) on gp.PersonalID = gph.PersonalID
							inner join r101.v_GetPersonalHistoryWP gphwp (nolock) on gph.GetPersonalHistory_id = gphwp.GetPersonalHistory_id
						where gphwp.WorkPlace_id = edcvil.MedStaffFact_id
					) sur
				";
				$kz_select.="
					,gmos.ID as SenderMoID
					,gmor.ID as ReceiverMoID
					,ps.KLCountry_id
					,p.BDZ_id as personId
					,coalesce(sur.PersonId, pSpec.BDZ_id) as specialistId
					,edc.EvnDirectionCVI_setDate as date
					,convert(varchar(19), edc.EvnDirectionCVI_takeDT, 120) as date_selection
					,cvis.CVIStatus_Code
					,RTRIM(ps.Person_SurNameR + ' ' + coalesce(ps.Person_FirNameR,'') + ' ' + coalesce(ps.Person_SecNameR,'')) as fio
					,ps.Sex_id
					,ps.Document_Num
					,convert(varchar(19), ps.Person_BirthDay, 120) as Person_BirthDay
					,dbo.Age(PS.Person_BirthDay, dbo.tzGetDate()) as age
					,edcvil.EvnDirectionCVILink_id
				";
			}
		}
		
		return $this->queryResult("
			select 
				edc.EvnDirectionCVI_id
				,edc.EvnDirectionCVI_pid
				,edc.Person_id
				,edc.PersonEvn_id
				,edc.Server_id
				,edc.Lpu_id
				,edc.EvnDirectionCVI_RegNumber
				,edc.EvnDirectionCVI_Contact
				,edc.EvnDirectionCVI_Lab
				,edc.Diag_id
				,convert(varchar(10), edc.EvnDirectionCVI_setDate, 104) as EvnDirectionCVI_setDate
				,edc.MedPersonal_id
				,convert(varchar(10), edc.EvnDirectionCVI_takeDT, 104) as EvnDirectionCVI_takeDate
				,convert(varchar(5), edc.EvnDirectionCVI_takeDT, 108) as EvnDirectionCVI_takeTime
				,convert(varchar(10), edc.EvnDirectionCVI_sendDT, 104) as EvnDirectionCVI_sendDate
				,convert(varchar(5), edc.EvnDirectionCVI_sendDT, 108) as EvnDirectionCVI_sendTime
				,edc.MedPersonal_tid
				,IIF(edc.EvnDirectionCVI_IsCito = 2, 'true', 'false') as EvnDirectionCVI_IsCito
				,IIF(edc.EvnDirectionCVI_isSmear = 2, 'true', 'false') as EvnDirectionCVI_isSmear
				,edc.EvnDirectionCVI_SmearNumber
				,edc.EvnDirectionCVI_SmearResult
				,IIF(edc.EvnDirectionCVI_isBlood = 2, 'true', 'false') as EvnDirectionCVI_isBlood
				,edc.EvnDirectionCVI_BloodNumber
				,edc.EvnDirectionCVI_BloodResult
				,IIF(edc.EvnDirectionCVI_isSputum = 2, 'true', 'false') as EvnDirectionCVI_isSputum
				,edc.EvnDirectionCVI_SputumNumber
				,edc.EvnDirectionCVI_SputumResult
				,IIF(edc.EvnDirectionCVI_isLavage = 2, 'true', 'false') as EvnDirectionCVI_isLavage
				,edc.EvnDirectionCVI_LavageNumber
				,edc.EvnDirectionCVI_LavageResult
				,IIF(edc.EvnDirectionCVI_isAspirate = 2, 'true', 'false') as EvnDirectionCVI_isAspirate
				,edc.EvnDirectionCVI_AspirateNumber
				,edc.EvnDirectionCVI_AspirateResult
				,IIF(edc.EvnDirectionCVI_isAutopsy = 2, 'true', 'false') as EvnDirectionCVI_isAutopsy
				,edc.EvnDirectionCVI_AutopsyNumber
				,edc.EvnDirectionCVI_AutopsyResult
				{$kz_select}
			from v_EvnDirectionCVI edc (nolock)
				{$kz_join}
			{$where}
		", $data);
	}

	/**
	 * Загрузка
	 */
	function loadJournal($data) {
		
		$filters = '1 = 1 ';
		$queryParams = [];
		
		$filters .= ' and EDC.Lpu_id = :Lpu_id ';

		if (!empty($data['Person_SurName'])) {
			$filters .= ' and PS.Person_SurName like :Person_SurName ';
			$data['Person_SurName'] .= '%';
		}

		if (!empty($data['Person_FirName'])) {
			$filters .= ' and PS.Person_FirName like :Person_FirName ';
			$data['Person_FirName'] .= '%';
		}

		if (!empty($data['Person_SecName'])) {
			$filters .= ' and PS.Person_SecName like :Person_SecName ';
			$data['Person_SecName'] .= '%';
		}

		if (!empty($data['Person_BirthDay'])) {
			$filters .= ' and PS.Person_BirthDay = :Person_BirthDay ';
		}
		
		if (!empty($data['Person_AgeFrom'])) {
			$filters .= " and dbo.Age2(PS.Person_BirthDay, @curDt) >= :Person_AgeFrom";
		}
		
		if (!empty($data['Person_AgeTo'])) {
			$filters .= " and dbo.Age2(PS.Person_BirthDay, @curDt) <= :Person_AgeTo";
		}

		if (!empty($data['PersonBirthYearFrom'])) {
			$filters .= " and year(PS.Person_BirthDay) >= :PersonBirthYearFrom";
		}

		if (!empty($data['PersonBirthYearTo'])) {
			$filters .= " and year(PS.Person_BirthDay) <= :PersonBirthYearTo";
		}

		if (!empty($data['EvnDirectionCVI_RegNumber'])) {
			$filters .= " and EDC.EvnDirectionCVI_RegNumber = :EvnDirectionCVI_RegNumber";
		}

		if (!empty($data['EvnDirectionCVI_Lab'])) {
			$filters .= ' and EDC.EvnDirectionCVI_Lab like :EvnDirectionCVI_Lab ';
			$data['EvnDirectionCVI_Lab'] .= '%';
		}

		if (!empty($data['Diag_id'])) {
			$filters .= " and EDC.Diag_id = :Diag_id";
		}
		
		if (
			!empty($data['EvnDirectionCVI_setDate_Range']) 
			&& count($data['EvnDirectionCVI_setDate_Range']) == 2
			&& !empty($data['EvnDirectionCVI_setDate_Range'][0])
			&& !empty($data['EvnDirectionCVI_setDate_Range'][1])
		) {
			$filters .= ' and EDC.EvnDirectionCVI_setDate between :EvnDirectionCVI_setDate_RangeStart and :EvnDirectionCVI_setDate_RangeEnd ';
			$data['EvnDirectionCVI_setDate_RangeStart'] = $data['EvnDirectionCVI_setDate_Range'][0];
			$data['EvnDirectionCVI_setDate_RangeEnd'] = $data['EvnDirectionCVI_setDate_Range'][1];
		}

		if (!empty($data['MedPersonal_id'])) {
			$filters .= " and EDC.MedPersonal_id = :MedPersonal_id";
		}
		
		if (
			!empty($data['EvnDirectionCVI_takeDate_Range']) 
			&& count($data['EvnDirectionCVI_takeDate_Range']) == 2
			&& !empty($data['EvnDirectionCVI_takeDate_Range'][0])
			&& !empty($data['EvnDirectionCVI_takeDate_Range'][1])
		) {
			$filters .= ' and EDC.EvnDirectionCVI_takeDT between :EvnDirectionCVI_takeDate_RangeStart and :EvnDirectionCVI_takeDate_RangeEnd ';
			$data['EvnDirectionCVI_takeDate_RangeStart'] = $data['EvnDirectionCVI_takeDate_Range'][0];
			$data['EvnDirectionCVI_takeDate_RangeEnd'] = $data['EvnDirectionCVI_takeDate_Range'][1];
		}

		if (!empty($data['MedPersonal_tid'])) {
			$filters .= " and EDC.MedPersonal_tid = :MedPersonal_tid";
		}
		
		if (
			!empty($data['EvnDirectionCVI_sendDate_Range']) 
			&& count($data['EvnDirectionCVI_sendDate_Range']) == 2
			&& !empty($data['EvnDirectionCVI_sendDate_Range'][0])
			&& !empty($data['EvnDirectionCVI_sendDate_Range'][1])
		) {
			$filters .= ' and EDC.EvnDirectionCVI_sendDT between :EvnDirectionCVI_sendDate_RangeStart and :EvnDirectionCVI_sendDate_RangeEnd ';
			$data['EvnDirectionCVI_sendDate_RangeStart'] = $data['EvnDirectionCVI_sendDate_Range'][0];
			$data['EvnDirectionCVI_sendDate_RangeEnd'] = $data['EvnDirectionCVI_sendDate_Range'][1];
		}

		if (!empty($data['EvnDirectionCVI_Number'])) {
			$filters .= " and EDC.EvnDirectionCVI_Number = :EvnDirectionCVI_Number";
		}
		
		$query = "
			-- variables
			declare @curDt datetime = dbo.tzGetDate();
			-- end variables
			select
				-- select
				EDC.EvnDirectionCVI_id
				,EDC.Person_id
				,isnull(PS.Person_SurName, '') + isnull(' '+PS.Person_FirName, '') + isnull(' '+PS.Person_SecName, '') as Person_Fio
				,d.Diag_FullName as Diag_Name
				,case 
					when epl.EvnPL_NumCard is not null then 'ТАП №' + epl.EvnPL_NumCard
					when eps.EvnPS_NumCard is not null then 'КВС №' + eps.EvnPS_NumCard
					else ''
				end as Evn_Name
				,convert(varchar(10), EvnDirectionCVI_setDate, 104) as EvnDirectionCVI_setDate
				,convert(varchar(10), EvnDirectionCVI_takeDT, 104) + ' ' + convert(varchar(5), EvnDirectionCVI_takeDT, 108) as EvnDirectionCVI_takeDT
				,convert(varchar(10), EvnDirectionCVI_sendDT, 104) + ' ' + convert(varchar(5), EvnDirectionCVI_sendDT, 108) as EvnDirectionCVI_sendDT
				,mp.Person_Fin as MedPersonal_Fio
				,mpt.Person_Fin as MedPersonal_tFio
				,case when EDC.EvnDirectionCVI_isSmear = 2 then 'Мазок <br>' else '' end +
					case when EDC.EvnDirectionCVI_isBlood = 2 then 'Кровь <br>' else '' end +
					case when EDC.EvnDirectionCVI_isSputum = 2 then 'Мокрота <br>' else '' end +
					case when EDC.EvnDirectionCVI_isLavage = 2 then 'БАЛ <br>' else '' end +
					case when EDC.EvnDirectionCVI_isAspirate = 2 then 'Аспират <br>' else '' end +
					case when EDC.EvnDirectionCVI_isAutopsy = 2 then 'Аутопсийный <br>' else '' end
					as material
				,case when EDC.EvnDirectionCVI_SmearNumber is not null then 'Мазок: Обр. №'+cast(EDC.EvnDirectionCVI_SmearNumber as varchar)+' <br>' else '' end +
					case when EDC.EvnDirectionCVI_BloodNumber is not null then 'Кровь: Обр. №'+cast(EDC.EvnDirectionCVI_BloodNumber as varchar)+' <br>' else '' end +
					case when EDC.EvnDirectionCVI_SputumNumber is not null then 'Мокрота: Обр. №'+cast(EDC.EvnDirectionCVI_SputumNumber as varchar)+' <br>' else '' end +
					case when EDC.EvnDirectionCVI_LavageNumber is not null then 'БАЛ: Обр. №'+cast(EDC.EvnDirectionCVI_LavageNumber as varchar)+' <br>' else '' end +
					case when EDC.EvnDirectionCVI_AspirateNumber is not null then 'Аспират: Обр. №'+cast(EDC.EvnDirectionCVI_AspirateNumber as varchar)+' <br>' else '' end +
					case when EDC.EvnDirectionCVI_AutopsyNumber is not null then 'Аутопсийный: Обр. №'+cast(EDC.EvnDirectionCVI_AutopsyNumber as varchar)+' <br>' else '' end
					as tests
				,case when isSmear.YesNo_Name is not null then 'Мазок: '+isSmear.YesNo_Name+' <br>' else '' end +
					case when isBlood.YesNo_Name is not null then 'Кровь: '+isBlood.YesNo_Name+' <br>' else '' end +
					case when isSputum.YesNo_Name is not null then 'Мокрота: '+isSputum.YesNo_Name+' <br>' else '' end +
					case when isLavage.YesNo_Name is not null then 'БАЛ: '+isLavage.YesNo_Name+' <br>' else '' end +
					case when isAspirate.YesNo_Name is not null then 'Аспират: '+isAspirate.YesNo_Name+' <br>' else '' end +
					case when isAutopsy.YesNo_Name is not null then 'Аутопсийный: '+isAutopsy.YesNo_Name+' <br>' else '' end
					as results
				,IIF(EDC.EvnDirectionCVI_IsCito = 2, 'true', 'false') as EvnDirectionCVI_IsCito
				,EDC.EvnDirectionCVI_Lab
				-- end select
			from
				-- from
				v_EvnDirectionCVI EDC (nolock)
				inner join v_PersonState PS (nolock) on PS.Person_id = EDC.Person_id
				left join v_Diag d (nolock) on d.Diag_id = EDC.Diag_id
				left join v_MedPersonal mp (nolock) on mp.MedPersonal_id = EDC.MedPersonal_id and mp.Lpu_id = EDC.Lpu_id
				left join v_MedPersonal mpt (nolock) on mpt.MedPersonal_id = EDC.MedPersonal_tid and mpt.Lpu_id = EDC.Lpu_id
				left join v_EvnPL epl (nolock) on epl.EvnPL_id = EDC.EvnDirectionCVI_rid 
				left join v_EvnPS eps (nolock) on eps.EvnPS_id = EDC.EvnDirectionCVI_rid
				left join v_YesNo isSmear (nolock) on isSmear.YesNo_id = EDC.EvnDirectionCVI_SmearResult
				left join v_YesNo isBlood (nolock) on isBlood.YesNo_id = EDC.EvnDirectionCVI_BloodResult
				left join v_YesNo isSputum (nolock) on isSputum.YesNo_id = EDC.EvnDirectionCVI_SputumResult
				left join v_YesNo isLavage (nolock) on isLavage.YesNo_id = EDC.EvnDirectionCVI_LavageResult
				left join v_YesNo isAspirate (nolock) on isAspirate.YesNo_id = EDC.EvnDirectionCVI_AspirateResult
				left join v_YesNo isAutopsy (nolock) on isAutopsy.YesNo_id = EDC.EvnDirectionCVI_AutopsyResult
				-- end from
			WHERE
				-- where
				{$filters}
				-- end where
			ORDER BY 
				-- order by
				EDC.EvnDirectionCVI_setDate DESC
				-- end order by
		";
		
		return $this->getPagingResponse($query, $data, $data['start'], $data['limit'], true);
	}

	/**
	 * Сохранение 
	 */
	function save($data) {
		
		$proc = empty($data['EvnDirectionCVI_id']) ? 'p_EvnDirectionCVI_ins' : 'p_EvnDirectionCVI_upd';
		
		$result = $this->execCommonSP($proc, [
			'EvnDirectionCVI_id' => ['value' => $data['EvnDirectionCVI_id'], 'out' => true,	'type' => 'bigint'],
			'DirType_id' => 30,
			'EvnDirectionCVI_pid' => $data['EvnDirectionCVI_pid'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'Server_id' => $data['Server_id'],
			'Lpu_id' => $data['Lpu_id'],
			'EvnDirectionCVI_Num' => '',
			'EvnDirectionCVI_RegNumber' => $data['EvnDirectionCVI_RegNumber'],
			'EvnDirectionCVI_Contact' => $data['EvnDirectionCVI_Contact'],
			'EvnDirectionCVI_Lab' => $data['EvnDirectionCVI_Lab'],
			'Diag_id' => $data['Diag_id'],
			'EvnDirectionCVI_setDT' => $data['EvnDirectionCVI_setDate'],
			'EvnDirectionCVI_takeDT' => !empty($data['EvnDirectionCVI_takeDate']) ? "{$data['EvnDirectionCVI_takeDate']} {$data['EvnDirectionCVI_takeTime']}" : null,
			'EvnDirectionCVI_sendDT' => !empty($data['EvnDirectionCVI_sendDate']) ? "{$data['EvnDirectionCVI_sendDate']} {$data['EvnDirectionCVI_sendTime']}" : null,
			'MedPersonal_id' => $data['MedPersonal_id'],
			'MedPersonal_tid' => $data['MedPersonal_tid'],
			'EvnDirectionCVI_IsCito' => $data['EvnDirectionCVI_IsCito'],
			'EvnDirectionCVI_isSmear' => $data['EvnDirectionCVI_isSmear'],
			'EvnDirectionCVI_SmearNumber' => $data['EvnDirectionCVI_SmearNumber'],
			'EvnDirectionCVI_SmearResult' => $data['EvnDirectionCVI_SmearResult'],
			'EvnDirectionCVI_isBlood' => $data['EvnDirectionCVI_isBlood'],
			'EvnDirectionCVI_BloodNumber' => $data['EvnDirectionCVI_BloodNumber'],
			'EvnDirectionCVI_BloodResult' => $data['EvnDirectionCVI_BloodResult'],
			'EvnDirectionCVI_isSputum' => $data['EvnDirectionCVI_isSputum'],
			'EvnDirectionCVI_SputumNumber' => $data['EvnDirectionCVI_SputumNumber'],
			'EvnDirectionCVI_SputumResult' => $data['EvnDirectionCVI_SputumResult'],
			'EvnDirectionCVI_isLavage' => $data['EvnDirectionCVI_isLavage'],
			'EvnDirectionCVI_LavageNumber' => $data['EvnDirectionCVI_LavageNumber'],
			'EvnDirectionCVI_LavageResult' => $data['EvnDirectionCVI_LavageResult'],
			'EvnDirectionCVI_isAspirate' => $data['EvnDirectionCVI_isAspirate'],
			'EvnDirectionCVI_AspirateNumber' => $data['EvnDirectionCVI_AspirateNumber'],
			'EvnDirectionCVI_AspirateResult' => $data['EvnDirectionCVI_AspirateResult'],
			'EvnDirectionCVI_isAutopsy' => $data['EvnDirectionCVI_isAutopsy'],
			'EvnDirectionCVI_AutopsyNumber' => $data['EvnDirectionCVI_AutopsyNumber'],
			'pmUser_id' => $data['pmUser_id']
		], 'array_assoc');
		
		if ($result['success'] && getRegionNick() == 'kz') {
			
			$check_record = $this->getFirstRowFromQuery("
				select top 1 
					EvnDirectionCVILink_id, 
					EvnDirectionCVILink_lisIsSuccess,
					EvnDirectionCVILink_lisID,
					EvnDirectionCVILink_lisNum,
					EvnDirectionCVILink_lisDT
				from r101.EvnDirectionCVILink (nolock)
				where EvnDirectionCVI_id = ?		
			", [$result['EvnDirectionCVI_id']], true);
			
			$proc = (empty($check_record))?'r101.p_EvnDirectionCVILink_ins':'r101.p_EvnDirectionCVILink_upd';

			$reslt = $this->execCommonSP($proc, [
				'EvnDirectionCVILink_id' => $check_record['EvnDirectionCVILink_id'],
				'EvnDirectionCVILink_lisIsSuccess' => $check_record['EvnDirectionCVILink_lisIsSuccess'],
				'EvnDirectionCVILink_lisID' => $check_record['EvnDirectionCVILink_lisID'],
				'EvnDirectionCVILink_lisNum' => $check_record['EvnDirectionCVILink_lisNum'],
				'EvnDirectionCVILink_lisDT' => $check_record['EvnDirectionCVILink_lisDT'],
				'EvnDirectionCVI_id' => $result['EvnDirectionCVI_id'],
				'Lpu_id' => $data['EvnDirectionCVILink_ReceiverMoID'],
				'CVIBiomaterial_id' => $data['CVIBiomaterial_id'],
				'CVIOrderType_id' => $data['CVIOrderType_id'],
				'CVISampleStatus_id' => $data['CVISampleStatus_id'],
				'CVIPurposeSurvey_id' => $data['CVIPurposeSurvey_id'],
				'CVIStatus_id' => $data['CVIStatus_id'],
				'EvnDirectionCVILink_WorkPlace' => $data['EvnDirectionCVILink_WorkPlace'],
				'EvnDirectionCVILink_Address' => $data['EvnDirectionCVILink_Address'],
				'EvnDirectionCVILink_Phone' => $data['EvnDirectionCVILink_Phone'],
				'EvnDirectionCVILink_PhonePersonal' => $data['EvnDirectionCVILink_PhonePersonal'],
				'EvnDirectionCVILink_IsSymptom' => $data['EvnDirectionCVILink_IsSymptom'],
				'MedStaffFact_id' => $data['MedStaffFact_id'],
				'pmUser_id' => $data['pmUser_id']
			], 'array_assoc');
		}
		
		return $result;
	}

	/**
	 * Удаление
	 */
	function delete($data) {
		
		return $this->execCommonSP('p_EvnDirectionCVI_del', [
			'EvnDirectionCVI_id' => $data['id'],
			'pmUser_id' => $data['pmUser_id']
		], 'array_assoc');
	}

	/**
	 * Получение адреса/телефона
	 */
	function getPersonAddressPhone($data) {
		
		$result = $this->getFirstRowFromQuery("
			select top 1 
				ppa.Address_Address,
				pp.PersonPhone_Phone,
				job.Org_Name
			from v_Person p (nolock)
				left join v_PersonPAddress ppa (nolock) on ppa.Person_id = p.Person_id
				left join v_PersonPhone pp (nolock) on pp.Person_id = p.Person_id
				outer apply (
					select top 1 
						o.Org_Name 
					from v_PersonJob pj (nolock)
						left join v_Org o (nolock) on pj.Org_id = o.Org_id
					where pj.person_id = p.Person_id
					order by pj.PersonJob_insDate desc
				) as job
			where 
				p.Person_id = ?
			order by 
				ppa.personpaddress_insDt desc, pp.PersonPhone_insDt desc
		", [$data['Person_id']]);
		
		return array_merge($result,['success'=>true]);
	}
}
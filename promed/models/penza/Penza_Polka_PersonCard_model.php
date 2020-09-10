<?php
/**
* pena_Polka_PersonCard_model - модель, для работы с таблицей PersonCard (Астрахань)
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

class Penza_Polka_PersonCard_model extends Polka_PersonCard_model {
	/**
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 *	Список прикрепленного населения к указанной МО на указанную дату
	 */
	function saveAttachedListToCSV($data, $file_zip_name, $out_dir)
	{
		$filterList = array();
		$queryParams = array(
			'Lpu_id' => $data['AttachLpu_id'],
			'Date_upload' => $data['Date_upload']
		);

		$Lpu_f003mcod = "";
		$resp = $this->queryResult("select Lpu_f003mcod from v_Lpu (nolock) where Lpu_id = :Lpu_id", array(
			'Lpu_id' => $data['AttachLpu_id']
		));
		if (!empty($resp[0]['Lpu_f003mcod'])) {
			$Lpu_f003mcod = $resp[0]['Lpu_f003mcod'];
		}

		// M+ Источник информации +Реестровый номер МО + реестровый номер ТФОМС + ГГГГММДД
		$dateUpload = date('Ymd', strtotime($data['Date_upload']));
		$attached_list_file_name = 'M2' . $Lpu_f003mcod . "58000" . $dateUpload . '.csv';
		$attached_list_file_path = EXPORTPATH_ATACHED_LIST.$out_dir."/".$attached_list_file_name;

		$query = "
			declare
				@Date date = :Date_upload,
				@Lpu_id bigint = :Lpu_id;
				
			select
				PC.LpuRegion_id,
				PC.LpuRegion_fapid,
				null as FIELD1, -- Pid
				case
					when PLS.PolisType_id = 4 and ISNULL(PFT.PolisFormType_Code, 0) in (0, 1) then 'П'
					when PLS.PolisType_id = 4 and PFT.PolisFormType_Code = 2 then 'Э'
					when PLS.PolisType_id = 3 then 'В'
					when PLS.PolisType_id = 1 then 'С'
					when PLS.PolisType_id = 4 and PFT.PolisFormType_Code = 3 then 'К'
				end as FIELD2, -- Тип_ДПФС
				case
					when PLS.PolisType_id = 3 then PLS.Polis_Num
					when PLS.PolisType_id = 1 then ISNULL(PLS.Polis_Ser, '') + ' № ' + ISNULL(PLS.Polis_Num, '')
				end as FIELD3, -- ИД_полиса
				PS.Person_edNum as FIELD4, -- ЕНП
				PS.Person_SurName as FIELD5, -- Фамилия
				PS.Person_FirName as FIELD6, -- Имя
				PS.Person_SecName as FIELD7, -- Отчество
				S.Sex_id as FIELD8, -- Пол
				CONVERT(varchar(8), PS.Person_BirthDay, 112) as FIELD9, -- Дата_рождения
				BA.Address_Address as FIELD10, -- Место_рождения
				DT.DocumentType_Code as FIELD11, -- Тип_УДЛ
				PS.Document_Ser + ' ' + PS.Document_Num as FIELD12, -- Ном_УДЛ
				convert(varchar(8), D.Document_begDate, 112) as FIELD13, -- Дата_УДЛ
				O.Org_Name as FIELD14, -- Орган_УДЛ
				case when LEN(PS.Person_Snils) = 11 then SUBSTRING(PS.Person_Snils,1,3)+'-'+SUBSTRING(PS.Person_Snils,4,3)+'-'+SUBSTRING(PS.Person_Snils,7,3)+' '+SUBSTRING(PS.Person_Snils,10,2) else '' end as FIELD15, -- СНИЛС
				null as FIELD16, -- КЛАДР
				ISNULL(KLSR.KLSubRgn_Name, KLR.KLRgn_Name) as FIELD17, -- Район
				ISNULL(KLT.KLTown_Name, KLC.KLCity_Name) as FIELD18, -- Нас_пункт
				KLS.KLStreet_Name as FIELD19, -- Улица
				UA.Address_House as FIELD20, -- Дом
				UA.Address_Flat as FIELD21, -- Квартира
				PS.Person_Phone as FIELD22, -- Телефон
				L.Lpu_f003mcod as FIELD23, -- ИД_МО
				case
					when (PC.PersonCardAttach_id is not null) then 2
					when (PC.PersonCardAttach_id is null and ISNULL(PC.PersonCard_IsAttachCondit,1) = 2) then 1
					else 0
				end as FIELD24, -- Способ_прикрепления
				null as FIELD25, -- Тип_прикрепления
				convert(varchar(8), PC.PersonCard_begDate, 112) as FIELD26, -- Дата_прикрепления
				convert(varchar(8), PC.PersonCard_endDate, 112) as FIELD27, -- Дата_открепления
				case when LEN(MPSnils.Person_Snils) = 11 then SUBSTRING(MPSnils.Person_Snils,1,3)+'-'+SUBSTRING(MPSnils.Person_Snils,4,3)+'-'+SUBSTRING(MPSnils.Person_Snils,7,3)+' '+SUBSTRING(MPSnils.Person_Snils,10,2) else '' end as FIELD28, -- СНИЛС_врача
				L.Lpu_RegNomN2 + ISNULL(BFCODE.AttributeValue_ValueString, LB.LpuBuilding_Code) as FIELD29,
				case when LEN(FAP.Person_Snils) = 11 then SUBSTRING(FAP.Person_Snils,1,3)+'-'+SUBSTRING(FAP.Person_Snils,4,3)+'-'+SUBSTRING(FAP.Person_Snils,7,3)+' '+SUBSTRING(FAP.Person_Snils,10,2) else '' end as FIELD30, -- СНИЛС_фап
				LR.LpuRegion_tfoms as FIELD31, -- Код_участка
				OS.Orgsmo_f002smocod as FIELD32 -- ИД_СМО
			from
				v_PersonState PS with (nolock)
				inner join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id
				inner join v_Polis PLS with (nolock) on PLS.Polis_id = ps.Polis_id
				left join v_Sex S with (nolock) on S.Sex_id = ps.Sex_id
				left join v_PolisFormType PFT with (nolock) on PLS.PolisFormType_id = PFT.PolisFormType_id
				left join v_Document D with (nolock) on D.Document_id = PS.Document_id
				left join v_DocumentType DT with (nolock) on DT.DocumentType_id = D.DocumentType_id
				left join v_OrgDep OD with (nolock) on D.OrgDep_id = OD.OrgDep_id
				left join v_Org O with (nolock) on O.Org_id = OD.Org_id
				left join v_Lpu L with (nolock) on L.Lpu_id = PC.Lpu_id
				left join v_LpuRegion LR with (nolock) on (LR.LpuRegion_id = PC.LpuRegion_fapid OR (PC.LpuRegion_fapid is null AND LR.LpuRegion_id = PC.LpuRegion_id))
				left join v_OrgSMO OS with (nolock) on PLS.OrgSMO_id = OS.OrgSMO_id
				left join v_PersonBirthPlace pbp with (nolock) on ps.Person_id = pbp.Person_id
				left join v_Address_all ba with (nolock) on ba.Address_id = pbp.Address_id
				left join v_Address_all ua with (nolock) on ua.Address_id = ps.UAddress_id
				left join v_KLSubRgn KLSR with (nolock) on KLSR.KLSubRgn_id = UA.KLSubRgn_id
				left join v_KLRgn KLR with (nolock) on KLR.KLRgn_id = UA.KLRgn_id
				left join v_KLTown KLT with (nolock) on KLT.KLTown_id = UA.KLTown_id
				left join v_KLCity KLC with (nolock) on KLC.KLCity_id = UA.KLCity_id
				left join v_KLStreet KLS with (nolock) on KLS.KLStreet_id = UA.KLStreet_id
				outer apply (
					select top 1
						MP.Person_Snils
					from
						v_MedPersonal MP (nolock)
						inner join v_MedStaffRegion MSR (nolock) on MSR.LpuRegion_id = PC.LpuRegion_id and MSR.MedPersonal_id = MP.MedPersonal_id
					where
						MSR.MedStaffRegion_IsMain = 2
					order by
						MSR.MedStaffRegion_endDate
				) as MPSnils
				outer apply (
					select top 1
						MP.Person_Snils
					from
						v_MedPersonal MP (nolock)
						inner join v_MedStaffRegion MSR (nolock) on MSR.LpuRegion_id = PC.LpuRegion_fapid and MSR.MedPersonal_id = MP.MedPersonal_id
					where
						MSR.MedStaffRegion_IsMain = 2
					order by
						MSR.MedStaffRegion_endDate
				) as FAP
				left join v_LpuSection ls with (nolock) on LS.LpuSection_id = lr.LpuSection_id
				left join v_LpuBuilding LB (nolock) on LB.LpuBuilding_id = ls.LpuBuilding_id
				outer apply (
					select top 1
						AV.AttributeValue_ValueString
					from
						v_AttributeValue AV with(nolock)
						inner join v_AttributeSignValue ASV with(nolock) on AV.AttributeSignValue_id = ASV.AttributeSignValue_id
						inner join v_AttributeSign [AS] with(nolock) on [AS].AttributeSign_id = ASV.AttributeSign_id
						inner join v_Attribute A with(nolock) on A.Attribute_id = AV.Attribute_id
					where
						[AS].AttributeSign_TableName like 'dbo.LpuSection'
						and ASV.AttributeSignValue_TablePKey = LR.LpuSection_id
						and [AS].AttributeSign_id = 1
						and a.Attribute_SysNick = 'Building_Code'
				) BFCODE
			where
				PC.LpuAttachType_id = 1
				and (PLS.Polis_endDate is null or PLS.Polis_endDate >= @Date)
				and PC.Lpu_id = @Lpu_id
				and pc.PersonCard_begDate <= @Date
				and (pc.PersonCard_endDate > @Date or pc.PersonCard_endDate is null)
				" . (count($filterList) > 0 ? "and " . implode(' and ', $filterList) : "") . "
		";
		//echo getDebugSQL($query, $queryParams); die();
		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return false;
		}

		$zip = new ZipArchive();
		$zip->open($file_zip_name, ZIPARCHIVE::CREATE);

		$empty = true;
		$toWrite = array();
		$filesToZip = array();
		while ($row = $result->_fetch_assoc()) {
			//if (empty($row['FIELD15']) && empty($row['FIELD28']) && empty($row['FIELD30'])) {
			if (
				(!empty($row['LpuRegion_id']) && empty($row['FIELD28']))
				|| (!empty($row['LpuRegion_fapid']) && empty($row['FIELD30']))
			) {
				continue; // В файл не попадают записи о прикреплении, в которых отсутствует СНИЛС участкового терапевта, участкового педиатра, врача общей практики.
			}
			$empty = false;

			unset($row['LpuRegion_id']);
			unset($row['LpuRegion_fapid']);

			array_walk_recursive($row, 'ReplaceLineBreaks');

			if (!empty($toWrite) && count($toWrite) >= 1000) { // пишем в файл если изменилась СМО или набрали 1000 записей
				// запись в файл
				if (!in_array($attached_list_file_name, $filesToZip)) {
					$filesToZip[] = $attached_list_file_name;
					$csv = toAnsi('Pid;Тип_ДПФС;ИД_полиса;ЕНП;Фамилия;Имя;Отчество;Пол;Дата_рождения;Место_рождения;Тип_УДЛ;Ном_УДЛ;Дата_УДЛ;Орган_УДЛ;СНИЛС;КЛАДР;Район;Нас_пункт;Улица;Дом;Квартира;Телефон;ИД_МО;Способ_прикрепления;Тип_прикрепления;Дата_прикрепления;Дата_открепления;СНИЛС_врача;ИД_МО_подр;СНИЛС_фап;Код_участка;ИД_СМО', true);
				} else {
					$csv = '';
				}
				array_walk_recursive($toWrite, 'ConvertFromUTF8ToWin1251', true);
				foreach($toWrite as $one) {
					$csv .= "\r\n" . implode(";", $one);
				}
				file_put_contents($attached_list_file_path, $csv, FILE_APPEND);

				$toWrite = array();
			}
			$toWrite[] = $row;
		}

		if (!empty($toWrite)) { // пишем в файл всё что осталось
			// запись в файл
			if (!in_array($attached_list_file_name, $filesToZip)) {
				$filesToZip[] = $attached_list_file_name;
				$csv = toAnsi('Pid;Тип_ДПФС;ИД_полиса;ЕНП;Фамилия;Имя;Отчество;Пол;Дата_рождения;Место_рождения;Тип_УДЛ;Ном_УДЛ;Дата_УДЛ;Орган_УДЛ;СНИЛС;КЛАДР;Район;Нас_пункт;Улица;Дом;Квартира;Телефон;ИД_МО;Способ_прикрепления;Тип_прикрепления;Дата_прикрепления;Дата_открепления;СНИЛС_врача;ИД_МО_подр;СНИЛС_фап;Код_участка;ИД_СМО', true);
			} else {
				$csv = '';
			}
			array_walk_recursive($toWrite, 'ConvertFromUTF8ToWin1251', true);
			foreach($toWrite as $one) {
				$csv .= "\r\n" . implode(";", $one);
			}
			file_put_contents($attached_list_file_path, $csv, FILE_APPEND);

			$toWrite = array();
		}

		foreach($filesToZip as $oneFileToZip) {
			$attached_list_file_path = EXPORTPATH_ATACHED_LIST.$out_dir."/".$oneFileToZip;
			$zip->AddFile( $attached_list_file_path, $oneFileToZip );
		}

		$zip->close();

		foreach($filesToZip as $oneFileToZip) {
			$attached_list_file_path = EXPORTPATH_ATACHED_LIST.$out_dir."/".$oneFileToZip;
			unlink($attached_list_file_path);
		}

		if ( $empty ) {
			return array(
				'Error_Code' => 1, 'Error_Msg' => 'Список выгрузки пуст!'
			);
		} else {
			return array(
				'Error_Msg' => '', 'file_zip_name' => $file_zip_name
			);
		}
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
			$filter .= " and PCA.PersonCardAttach_setDate >= :betAttachDate";
			$params['betAttachDate'] = $data['PersonCardAttach_setDate_Range'][0];
		}
		if(isset($data['PersonCardAttach_setDate_Range'][1])){
			$filter .= " and PCA.PersonCardAttach_setDate <= :endAttachDate";
			$params['endAttachDate'] = $data['PersonCardAttach_setDate_Range'][1];
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
						L.Lpu_Nick,
						L.Lpu_id,
						PS.Person_id,
						PCAST.PersonCardAttachStatusType_id,
						PCAST.PersonCardAttachStatusType_Code,
						PCAST.PersonCardAttachStatusType_Name,
						LRT.LpuRegionType_Name,
						LR.LpuRegion_Name,
						ISNULL(MSF.Person_SurName,'') + ' ' + ISNULL(MSF.Person_FirName,'') + ' ' + ISNULL(MSF.Person_Secname,'') as MSF_FIO,
						--'false' as HasPersonCard
						case when PC.PersonCard_id is null then 'false' else 'true' end as HasPersonCard
					from v_PersonCardAttach PCA (nolock)
					inner join v_PersonState PS (nolock) on PS.Person_id = PCA.Person_id
					left join v_Lpu L (nolock) on L.Lpu_id = PCA.Lpu_aid
					outer apply
					(
						select top 1 PCAS.PersonCardAttachStatus_id,
						PersonCardAttachStatusType_id
						from v_PersonCardAttachStatus PCAS (nolock)
						where PCAS.PersonCardAttach_id = PCA.PersonCardAttach_id
						order by PersonCardAttachStatusType_id desc
					) PCAS
					inner join PersonCardAttachStatusType PCAST (nolock) on PCAST.PersonCardAttachStatusType_id = PCAS.PersonCardAttachStatusType_id
					inner join v_LpuRegion LR (nolock) on LR.LpuRegion_id = PCA.LpuRegion_id
					inner join v_LpuRegionType LRT (nolock) on LRT.LpuRegionType_id = LR.LpuRegionType_id
					left join PersonCard PC (nolock) on PC.PersonCardAttach_id = PCA.PersonCardAttach_id
					left join v_MedStaffFact MSF (nolock) on MSF.MedStaffFact_id = PCA.MedStaffFact_id
					where PCA.LpuRegion_id is not null
					{$filter}

					union --Костылина, т.к. старые заявления не имеют ни участка, ни персона, ни врача (проверяется по LpuRegion_id - если его нет, значит это старое заявление)
					select 
						PCA.PersonCardAttach_id,
						PCA.PersonCardAttach_setDate as PersonCardAttach_setDate2,
						convert(varchar, cast(PCA.PersonCardAttach_setDate as datetime),104) as PersonCardAttach_setDate,
						ISNULL(PS.Person_SurName,'') + ' ' + ISNULL(PS.Person_FirName,'') + ' ' + ISNULL(PS.Person_Secname,'') as Person_FIO,
						L.Lpu_Nick,
						L.Lpu_id,
						PS.Person_id,
						PCAST.PersonCardAttachStatusType_id,
						PCAST.PersonCardAttachStatusType_Code,
						PCAST.PersonCardAttachStatusType_Name,
						LRT.LpuRegionType_Name,
						LR.LpuRegion_Name,
						ISNULL(MSF.Person_SurName,'') + ' ' + ISNULL(MSF.Person_FirName,'') + ' ' + ISNULL(MSF.Person_Secname,'') as MSF_FIO,
						'true' as HasPersonCard
					from v_PersonCardAttach PCA
					cross apply
					(
						select top 1 PCard.PersonCard_id,
						PCard.LpuRegion_id,
						PCard.Lpu_id,
						PCard.MedStaffFact_id,
						PCard.Person_id
						from v_PersonCard_all PCard (nolock)
						where PCard.PersonCardAttach_id = PCA.PersonCardAttach_id
					) PC
					inner join v_Lpu L (nolock) on L.Lpu_id = PC.Lpu_id
					inner join v_LpuRegion LR (nolock) on LR.LpuRegion_id = PC.LpuRegion_id
					inner join v_LpuRegionType LRT (nolock) on LRT.LpuRegionType_id = LR.LpuRegionType_id
					left join v_MedStaffFact MSF (nolock) on MSF.MedStaffFact_id = PC.MedStaffFact_id
					inner join v_PersonState PS (nolock) on PS.Person_id = PC.Person_id
					outer apply
					(
						select top 1 PCAS.PersonCardAttachStatus_id,
						PersonCardAttachStatusType_id
						from v_PersonCardAttachStatus PCAS (nolock)
						where PCAS.PersonCardAttach_id = PCA.PersonCardAttach_id
						order by PersonCardAttachStatusType_id desc
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
				ISNULL(LR.LpuRegionType_id, LR2.LpuRegionType_id) as LpuRegionType_id,
				ISNULL(PCA.MedStaffFact_id, PC.MedStaffFact_id) as MedStaffFact_id,
				PAC.PersonAmbulatCard_id,
				rtrim(rtrim(ISNULL(PAC.PersonAmbulatCard_Num,PC.PersonCard_Code))) as PersonCard_Code
			from
				v_PersonCardAttach PCA with(nolock)
				outer apply (
					select top 1 PCAS.PersonCardAttachStatus_id,
					PersonCardAttachStatusType_id
					from v_PersonCardAttachStatus PCAS (nolock)
					where PCAS.PersonCardAttach_id = PCA.PersonCardAttach_id
					order by PersonCardAttachStatusType_id desc
				) PCAS
				left join v_LpuRegion LR (nolock) on LR.LpuRegion_id = PCA.LpuRegion_id
				--left join v_LpuRegionType LRT (nolock) on LRT.LpuRegionType_id = LR.LpuRegionType_id
				left join v_PersonCard_all PC (nolock) on PC.PersonCardAttach_id = PCA.PersonCardAttach_id
				left join v_PersonState PS on PS.Person_id = PC.Person_id
				left join v_LpuRegion LR2 (nolock) on LR2.LpuRegion_id = PC.LpuRegion_id
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
			'PersonCardAttach_setDate'		=> $data['PersonCardAttach_setDate'],
			'MedStaffFact_id' 				=> $data['MedStaffFact_id'],
			'Person_id' 					=> $data['Person_id'],
			'PersonAmbulatCard_id' 			=> $data['PersonAmbulatCard_id'],
			'PersonCardAttach_IsSMS' 		=> 1,
			'PersonCardAttach_SMS' 			=> null,
			'PersonCardAttach_IsEmail' 		=> 1,
			'PersonCardAttach_Email' 		=> null,
			'PersonCardAttach_IsHimself' 	=> null,
			'pmUser_id' 					=> $data['pmUser_id']
		);
		if (empty($data['PersonCardAttach_id'])) {
			$procedure = 'p_PersonCardAttach_ins';
		} else {
			$procedure = 'p_PersonCardAttach_upd';
		}

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
				@MedStaffFact_id = :MedStaffFact_id,
				@Address_id = null,
				@Polis_id = null,
				@PersonCardAttach_IsSMS = :PersonCardAttach_IsSMS,
				@PersonCardAttach_SMS = :PersonCardAttach_SMS,
				@PersonCardAttach_IsEmail = :PersonCardAttach_IsEmail,
				@PersonCardAttach_Email = :PersonCardAttach_Email,
				@PersonCardAttach_IsHimself = :PersonCardAttach_IsHimself,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as PersonCardAttach_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
			PCAS.PersonCardAttachStatusType_Code,
			PCAS.PersonCardAttachStatusType_Name,
			convert(varchar(10), PCA.PersonCardAttach_setDate, 104) as PersonCardAttach_setDate,
			ISNULL(PS.Person_SurName,'') + ' ' + ISNULL(PS.Person_FirName,'') + ' ' + ISNULL(PS.Person_Secname,'') as Person_FIO
		from
			v_PersonCardAttach PCA with(nolock)
			left join v_PersonState PS (nolock) on PS.Person_id = PCA.Person_id
			outer apply (
				select top 1 PCAST.PersonCardAttachStatusType_Code, PCAST.PersonCardAttachStatusType_Name
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
				ISNULL(PCA.PersonAmbulatCard_id,0) as PersonAmbulatCard_id,
				ISNULL(PAC.PersonAmbulatCard_Num,'') as PersonAmbulatCard_Code
			from v_PersonCardAttach PCA (nolock)
			left join v_PersonAmbulatCard PAC (nolock) on PAC.PersonAmbulatCard_id = PCA.PersonAmbulatCard_id
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
			//Проверим, а есть ли у этого пациента активное прикрепление
			$queryPersonCard = "
				select top 1 *
				from v_PersonCard (nolock)
				where Person_id = :Person_id
				and LpuAttachType_id = 1
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
					select CONVERT(varchar(10), PersonCardAttach_setDate, 120) as setDate 
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

				//https://redmine.swan.perm.ru/issues/108218 - получим дату заявления
				$query_get_AttachDate = "
					select CONVERT(varchar(10), PersonCardAttach_setDate, 120) as setDate 
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
	function getPersonCardCode($data)
	{
		$sql = "
			declare @ObjID bigint;
			exec xp_GenpmID 
				@ObjectName = 'PersonCard', 
				@Lpu_id = ?,
				@ObjectID = @ObjID output;
			select @ObjID as PersonCard_Code;
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
			and right('000000' + ISNULL(L.Lpu_f003mcod, ''), 6) = {$LPUC}
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
}
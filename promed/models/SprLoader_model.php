<?php
/**
* SprLoader - проверка и загрузка справочников при входе в Промед
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package			Common
* @access			public
* @copyright		Copyright (c) 2009 Swan Ltd.
* @author	Stas Bykov aka Savage (savage1981@gmail.com)
* @version			?
*/
class SprLoader_model extends CI_Model
{
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Получение списка таблиц
	 */
	function getSprSyncTable($mode)
	{
		$table_list = array();

		if ($mode == "promed")
			$table_list = array(
				'AbortPlace',
				'AbortType',
				'AggType',
				'AggWhen',
				'AllergicReactionLevel',
				'AllergicReactionType',
				'AnatomWhere',
				'AnesthesiaClass',
				'AttachType',
				'BiopsyOrder',
				'BirthChildResult',
				'BirthEducation',
				'BirthEmployment',
				'BirthFamilyStatus',
				'BirthMedPersonalType',
				'BirthPlace',
				'BirthResult',
				'BirthSpec',
				'BirthSpecialist',
				'BloodGroupType',
				'CardCloseCause',
				'CauseInputType',
				'ChildPositionType',
				'ChildTermType',
				'CmpPlace',
				'CmpDopInfo',
				'CmpReason',
				'CmpCallType',
				'CmpDrug',
				'CmpDiag',
				'CmpLpu',
				'CmpProfile',
				'CmpResult',
				'CmpTrauma',
				'CmpTalon',
				'DeathCause',
				'DeathEducation',
				'DeathEmployment',
				'DeathFamilyStatus',
				'DeathPlace',
				'DeathSetCause',
				'DeathSetType',
				'DeathSvidType',
				'DeathTrauma',
				'DeathWomanType',
				'DemandState',
				'DeputyKind',
				'DeseaseInfectionType',
				'DeseaseType',
				'DeseaseFuncType',
				'DeseaseStage',
				'Diag',
				'DiagClinicalErrType',
				'DiagReasonDiscrepancy',
				'DiagSetClass',
				'DiagSetType',
				'DiagType',
				'DispResMedicalMeasureType',
				'DopDispResType',
				'DispRegistrationType',
				'DirectClass',
				'DirectType',
				'DirFailType',
				'DirType',
				'DispOutType',
				'DispUslugaTeen14Type',
				'Teen14DispSpecType',
				'DocumentType',
				'DopDispAlien',
				'DopDispDiagType',
				//'OrpDispDiagType',
				'DopDispSpec',
				'OrpDispSpec',
				'DopDispUslugaType',
				'OrpDispUslugaType',
				'DrugDocumentMotivation',
				'DrugFed',
				'DrugFinance',
				'DrugReg',
				'DrugDisp',
				'DtpDeathTime',
				'FeedingType',
				//'Glossary',
				'GlossaryTagType',
				'HealthAbnorm',
				'HealthAbnormVital',
				'HealthKind',
				'HeightAbnormType',
				'HeightMeasureType',
				'HistologicMaterial',
				'HistologicSpecimenPlace',
				'HistologicSpecimenSaint',
				'InvalidKind',
				'InvalidType',
				'KatNasel',
				'KLAreaStat',
				'KLAreaType',
				'PersonSprTerrDop',
				'KLCountry',
				'KLSocr',
				'LeaveCause',
				'LeaveType',
				'Lpu',
				'LpuLevel',
				'LpuSearch',
				'LpuAttachType',
				'LpuRegionType',
				'LpuSectionProfile',
				'LpuUnitType',
				'LpuSectionAge',
				'LpuSectionBedProfile',
				'LpuType',
				'LpuWardType',
				'MESLevel',
				'MedServiceType',
				'MedSpec',
				'MedSpecOms',
				'MedStatus',
				'Nationality',
				//'MorfoHistologicItemsType',
				'OrgHeadPost',
				'OMSSprTerr',
				'OMSSprTerrAddit',
				'Okved',
				'Okonh',
				'Okogu',
				'Okopf',
				'Okfs',
				'OperDiff',
				'OperType',
				'OrgSMO',
				'PayType',
				'PsychicalConditionType',
				'PersonSprTerrDop',
				'PntDeathCause',
				'PntDeathEducation',
				'PntDeathFamilyStatus',
				'PntDeathGetBirth',
				'PntDeathPeriod',
				'PntDeathPlace',
				'PntDeathSetCause',
				'PntDeathSetType',
				'PntDeathTime',
				'PolisType',
				'PostMed',
				'PostMedCat',
				'PostMedClass',
				'PostMedType',
				'PrehospArrive',
				'PrehospDefect',
				'PrehospDirect',
				'PrehospToxic',
				'PrehospTrauma',
				'PrehospType',
				'PrehospStatus',
				'PrehospWaifArrive',
				'PrehospWaifReason',
				'PrehospWaifRefuseCause',
				'PrehospWaifRetired',
				'PrescriptionDietType',
				'PrescriptionIntroType',
				'PrescriptionRegimeType',
				'PrescriptionStatusType',
				'PrescriptionTreatType',
				'PrescriptionType',
				'PrivilegeType',
				'ProfGoal',
				'QueueFailCause',
				'ReceptDelayType',
				'ReceptDiscount',
				'ReceptFinance',
				'ReceptRemoveCauseType',
				'ReceptType',
				'ReceptValid',
				'RecommendationsTreatmentType',
				'RecommendationsTreatmentDopType',
				'ResidPlace',
				'RecType',
				'RegistryStatus',
				'RegistryType',
				'RelatedLinkType',
				'ResultClass',
				'ResultDesease',
				'RhFactorType',
				'SanationStatus',
				'SFPrehospDirect',
				'ServiceType',
				'Sex',
				'SexualConditionType',
				'Sickness',
				'SicknessDiag',
				'SocStatus',
				'SpecificType',
				'StickCause',
				'StickCauseDopType',
				'StickIrregularity',
				'StickLeaveType',
				'StickOrder',
				'StickRecipient',
				'StickRegime',
				'StickType',
				'StickWorkType',
				'StudyType',
				'SurgType',
				'TariffClass',
				'TariffMesType',
				'TimetableType',
				'ToothType',
				'TreatmentMultiplicity',
				'TreatmentReview',
				'TreatmentSubjectType',
				'TreatmentType',
				'TreatmentUrgency',
				'TreatmentSenderType',
				'Usluga',
				'UslugaClass',
				'UslugaType',
				'UslugaPlace',
				'UslugaExecutionReason',
				'VizitClass',
				'VizitType',
				'WeightAbnormType',
				'WeightMeasureType',
				'YesNo',
				
				
				'LpuBuildingType',
				'LpuSectionHospType',
				'LpuSectionPlanType',
				'DrugRequestStatus',
				'DrugRequestPeriod',
				'DrugRequestType',
				'ReceptResult',
				'ReceptMismatch',
				'ContragentType',
				'DrugNds',				
				'MesProf',
				'MesAgeGroup',
				'PrivilegeTypeWow',
				'OmsLpuUnitType',
				'DispWowSpec',
				'DispWowUslugaType',
				'ExaminationPlace'/*,
				',DrugExtraLevel'*/
				,'RefCategory'
				,'RefValuesType'
				,'RefValuesUnit'
				,'RefValuesGroup'
				,'AgeUnit'
				,'HormonalPhaseType'
				,'TimeOfDay'
				,'RefMaterial'
				,'WhsDocumentCostItemType'
				,'WhsDocumentStatusType'
				,'WhsDocumentSupplyType'
				,'WhsDocumentType'
				,'XmlTemplateSeparator' // справочник разделителей шаблонов
				,'RegistryStacType'
				,'RegistryEventType'
				,'rls_COUNTRIES'
				,'rls_FIRMS'
				,'rls_ACTMATTERS'
				,'rls_DESCTEXTES'
				//,'rls_TRADENAMES'
				,'rls_CLSPHARMAGROUP'
				,'rls_ClsPhGrLimp'
				,'rls_CLSIIC'
				,'rls_CLSATC'
				,'rls_CLSDRUGFORMS'
				,'ExpertiseNameType'
				,'ExpertiseEventType'
				,'PatientStatusType'
				,'CauseTreatmentType'
				,'ExpertiseNameSubjectType'
				,'ExpertMedStaffType'
				,'msg_RecipientType'
				,'msg_NoticeType' //
			);

		if ($mode == "farmacy")
			$table_list = array(			
				'DrugDocumentMotivation',
				'DrugFinance',
				'Lpu',
				'OMSSprTerr',
				'OrgSMO',
				'PayType',
				'PolisType',
				'ReceptDelayType',
				'ReceptDiscount',
				'ReceptFinance',
				'ReceptType',
				'ReceptValid',
				'YesNo',
				'ReceptResult',
				'ReceptMismatch',
				'ContragentType',
				'DrugNds'
			);


		$response = array();

		for ($i = 0; $i < count($table_list); $i++)
		{
			if (in_array($table_list[$i], array('DrugDisp')))
			{
				$query = "
					select
						count(*) as cnt, convert(varchar(10), max(isnull([SicknessDrug_updDT], [SicknessDrug_insDT])), 104) + ' ' + convert(varchar(10), max(isnull([SicknessDrug_updDT], [SicknessDrug_insDT])), 108) as [" . $table_list[$i] . "_updDT]
					from [SicknessDrug]
				";
			}
			else
			if (preg_match('/rls/', $table_list[$i]))
			{
				$table_list[$i] = substr($table_list[$i], 4);
				$query = "
					select
						count(*) as cnt, convert(varchar(10), max(isnull([" . $table_list[$i] . "_updDT], [" . $table_list[$i] . "_insDT])), 104) + ' ' + convert(varchar(10), max(isnull([" . $table_list[$i] . "_updDT], [" . $table_list[$i] . "_insDT])), 108) as [" . $table_list[$i] . "_updDT]
					from
						rls.v_" .$table_list[$i]. " with(nolock)
				";
				$table_list[$i] = 'rls_'.$table_list[$i];
			}
			else
			if (preg_match('/msg/', $table_list[$i]))
			{
				$table_list[$i] = substr($table_list[$i], 4);
				$query = "
					select
						count(*) as cnt, convert(varchar(10), max(isnull([" . $table_list[$i] . "_updDT], [" . $table_list[$i] . "_insDT])), 104) + ' ' + convert(varchar(10), max(isnull([" . $table_list[$i] . "_updDT], [" . $table_list[$i] . "_insDT])), 108) as [" . $table_list[$i] . "_updDT]
					from
						msg.v_" .$table_list[$i]. " with(nolock)
				";
				$table_list[$i] = 'msg_'.$table_list[$i];
			}
			else
			if (in_array($table_list[$i], array('DrugFed', 'DrugReg')))
			{
				$query = "
					select
						count(*) as cnt, convert(varchar(10), max(isnull([Drug_updDT], [Drug_insDT])), 104) + ' ' + convert(varchar(10), max(isnull([Drug_updDT], [Drug_insDT])), 108) as [" . $table_list[$i] . "_updDT]
					from [v_" . $table_list[$i] . "]
				";
			}
			else
				{
					$query = "
						if OBJECT_ID('v_" . $table_list[$i] . "', 'V') is not null 
						begin
							select
								count(*) as cnt, convert(varchar(10), max(isnull([" . $table_list[$i] . "_updDT], [" . $table_list[$i] . "_insDT])), 104) + ' ' + convert(varchar(10), max(isnull([" . $table_list[$i] . "_updDT], [" . $table_list[$i] . "_insDT])), 108) as [" . $table_list[$i] . "_updDT]
							from [v_" . $table_list[$i] . "]
						end
						else 
						begin
							-- Очевидно что нет вьюхи
							-- Если View соответствующей таблицы отсутствует, то данные не загружаются , но ошибка не выходит
							-- То есть если в таблице данные есть, а при загрузке справочника таблица (локально) пустая, то нет вьюхи
							select 0 as cnt, null as ".$table_list[$i]."_updDT
						end
					";
				}

			if ( $table_list[$i] == 'OMSSprTerrAddit' )
				$query = "
					select
						count(*) as cnt, convert(varchar(10), max(isnull([OMSSprTerr_updDT], [OMSSprTerr_insDT])), 104) + ' ' + convert(varchar(10), max(isnull([OMSSprTerr_updDT], [OMSSprTerr_insDT])), 108) as [OMSSprTerrAddit_updDT]
					from [v_OMSSprTerr]
				";						

			if ( $table_list[$i] == 'LpuSearch' )
				$query = "
					select
						count(*) as cnt, convert(varchar(10), max(isnull([Lpu_updDT], [Lpu_insDT])), 104) + ' ' + convert(varchar(10), max(isnull([Lpu_updDT], [Lpu_insDT])), 108) as [LpuSearch_updDT]
					from [v_Lpu]
				";

			if ( $table_list[$i] == 'SFPrehospDirect' )
				$query = "
					select
						count(*) as cnt, convert(varchar(10), max(isnull([PrehospDirect_updDT], [PrehospDirect_insDT])), 104) + ' ' + convert(varchar(10), max(isnull([PrehospDirect_updDT], [PrehospDirect_insDT])), 108) as [SFPrehospDirect_updDT]
					from [v_PrehospDirect]
				";
			$result = $this->db->query($query);

			if (is_object($result))
			{
				$result_row = $result->row_array(0);
				$response[] = array(
									'SyncTable_Name' => $table_list[$i],
									'SyncTable_updDT' => $result_row[(preg_match('/msg/', $table_list[$i]) || preg_match('/rls/', $table_list[$i]))?substr($table_list[$i],4).'_updDT':$table_list[$i] . '_updDT'],
									'SyncTable_Count' => $result_row['cnt']
									);
			}
		}
		return $response;
	}

	/**
	 * Создание новой версии локальной базы по изменениям таблиц в базе
	 */
	function createNewVersionLocalDB($data)
	{
		$table_list = array();
		$data['mode'] = 'farmacy';
		if ($data['mode'] == "promed")
			$table_list = array(
				'AbortPlace',
				'AbortType',
				'AggType',
				'AggWhen',
				'AllergicReactionLevel',
				'AllergicReactionType',
				'AnatomWhere',
				'AnesthesiaClass',
				'AttachType',
				'BiopsyOrder',
				'BirthChildResult',
				'BirthEducation',
				'BirthEmployment',
				'BirthFamilyStatus',
				'BirthMedPersonalType',
				'BirthPlace',
				'BirthResult',
				'BirthSpec',
				'BirthSpecialist',
				'BloodGroupType',
				'CardCloseCause',
				'CauseInputType',
				'ChildPositionType',
				'ChildTermType',
				'CmpPlace',
				'CmpDopInfo',
				'CmpReason',
				'CmpCallType',
				'CmpDrug',
				'CmpDiag',
				'CmpLpu',
				'CmpProfile',
				'CmpResult',
				'CmpTrauma',
				'CmpTalon',
				'DeathCause',
				'DeathEducation',
				'DeathEmployment',
				'DeathFamilyStatus',
				'DeathPlace',
				'DeathSetCause',
				'DeathSetType',
				'DeathSvidType',
				'DeathTrauma',
				'DeathWomanType',
				'DemandState',
				'DeputyKind',
				'DeseaseInfectionType',
				'DeseaseType',
				'DeseaseStage',
				'Diag',
				'DiagClinicalErrType',
				'DiagReasonDiscrepancy',
				'DiagSetClass',
				'DiagSetType',
				'DirectClass',
				'DirectType',
				'DirFailType',
				'DirType',
				'DispOutType',
				'DocumentType',
				'DopDispAlien',
				'DopDispDiagType',
				//'OrpDispDiagType',
				'DopDispSpec',
				'OrpDispSpec',
				'DopDispUslugaType',
				'OrpDispUslugaType',				
				'DrugFed',
				'DrugFinance',
				'DrugReg',
				'DrugDisp',
				'DtpDeathTime',
				'FeedingType',
				//'Glossary',
				'GlossaryTagType',
				'HealthAbnorm',
				'HealthAbnormVital',
				'HealthKind',
				'HeightAbnormType',
				'HeightMeasureType',
				'HistologicMaterial',
				'HistologicSpecimenPlace',
				'HistologicSpecimenSaint',
				'InvalidKind',
				'KatNasel',
				'KLAreaStat',
				'KLAreaType',
				'PersonSprTerrDop',
				'KLCountry',
				'KLSocr',
				'LeaveCause',
				'LeaveType',
				'Lpu',
				'LpuLevel',
				'LpuSearch',
				'LpuAttachType',
				'LpuRegionType',
				'LpuSectionProfile',
				'LpuUnitType',
				'LpuSectionAge',
				'LpuSectionBedProfile',
				'LpuType',
				'LpuWardType',
				'MESLevel',
				'MedServiceType',
				'MedSpec',
				'MedSpecOms',
				'MedStatus',
				'Nationality',
				//'MorfoHistologicItemsType',
				'OrgHeadPost',
				'OMSSprTerr',
				'OMSSprTerrAddit',
				'Okved',
				'Okonh',
				'Okogu',
				'Okopf',
				'Okfs',
				'OperDiff',
				'OperType',
				'OrgSMO',
				'PayType',
				'PsychicalConditionType',
				'PersonSprTerrDop',
				'PntDeathCause',
				'PntDeathEducation',
				'PntDeathFamilyStatus',
				'PntDeathGetBirth',
				'PntDeathPeriod',
				'PntDeathPlace',
				'PntDeathSetCause',
				'PntDeathSetType',
				'PntDeathTime',
				'PolisType',
				'PostMed',
				'PostMedCat',
				'PostMedClass',
				'PostMedType',
				'PrehospArrive',
				'PrehospDefect',
				'PrehospDirect',
				'PrehospToxic',
				'PrehospTrauma',
				'PrehospType',
				'PrehospStatus',
				'PrehospWaifArrive',
				'PrehospWaifReason',
				'PrehospWaifRetired',
				'PrehospWaifRefuseCause',
				'PrescriptionDietType',
				'PrescriptionIntroType',
				'PrescriptionRegimeType',
				'PrescriptionStatusType',
				'PrescriptionTreatType',
				'PrescriptionType',
				'PrivilegeType',
				'ProfGoal',
				'QueueFailCause',
				'ReceptDelayType',
				'ReceptDiscount',
				'ReceptFinance',
				'ReceptType',
				'ReceptValid',
				'ResidPlace',
				'RecType',
				'RegistryStatus',
				'RegistryType',
				'ResultClass',
				'ResultDesease',
				'RhFactorType',
				'SanationStatus',
				'SFPrehospDirect',
				'ServiceType',
				'Sex',
				'Sickness',
				'SicknessDiag',
				'SocStatus',
				'SpecificType',
				'StickCause',
				'StickCauseDopType',
				'StickIrregularity',
				'StickLeaveType',
				'StickOrder',
				'StickRecipient',
				'StickRegime',
				'RelatedLinkType',
				'StickType',
				'StickWorkType',
				'SurgType',
				'TimetableType',
				'Usluga',
				'UslugaClass',
				'UslugaType',
				'UslugaPlace',
				'UslugaExecutionReason',
				'VizitClass',
				'VizitType',
				'WeightAbnormType',
				'WeightMeasureType',
				'YesNo',
				'TariffClass',
				'TariffMesType',
				'ToothType',
				'LpuBuildingType',
				'LpuSectionHospType',
				'LpuSectionPlanType',
				'DrugRequestStatus',
				'DrugRequestPeriod',
				'DrugRequestType',
				'ReceptResult',
				'ReceptMismatch',
				'ContragentType',
				'DrugNds',
				'MesProf',
				'MesAgeGroup',
				'PrivilegeTypeWow',
				'OmsLpuUnitType',
				'DispWowSpec',
				'DispWowUslugaType',
				'ExaminationPlace',
				'TreatmentMultiplicity',
				'TreatmentReview',
				'TreatmentSubjectType',
				'TreatmentType',
				'TreatmentUrgency',
				'TreatmentSenderType'/*,
				'DrugExtraLevel'*/
				,'RefCategory'
				,'RefValuesType'
				,'RefValuesUnit'
				,'RefValuesGroup'
				,'AgeUnit'
				,'HormonalPhaseType'
				,'TimeOfDay'
				,'RefMaterial'
				,'WhsDocumentCostItemType'
				,'WhsDocumentStatusType'
				,'WhsDocumentSupplyType'
				,'WhsDocumentType'
				,'XmlTemplateSeparator' // справочник разделителей шаблонов
				,'RegistryStacType'
				,'RegistryEventType'
				,'rls_Countries'
				,'rls_Firms'
				,'rls_Actmatters'
				,'rls_Desctextes'
				//,'rls_Tradenames'
				,'rls_Clspharmagroup'
				,'rls_ClsMzPhgroup'
				,'rls_ClsPhGrLimp'
				,'rls_Clsiic'
				,'rls_Clsatc'
				,'rls_Clsdrugforms'
				,'rls_Narcogroups'
				,'rls_Stronggroups'
				,'msg_RecipientType'
				,'msg_NoticeType' //
			);

		if ($data['mode'] == "farmacy")
			$table_list = array(
				'DrugDocumentMotivation',
				'DrugFinance',				
				'Lpu',
				'OMSSprTerr',
				'OrgSMO',
				'PayType',
				'PolisType',
				'ReceptDelayType',
				'ReceptDiscount',
				'ReceptFinance',
				'ReceptType',
				'ReceptValid',
				'YesNo',
				'ReceptResult',
				'ReceptMismatch',
				'ContragentType',
				'DrugNds'
			);


		$response = array();
		$query = "";
		/*
		$query = "
			DECLARE @spr as [stg].tableLocalDB ";
		*/
		for ($i = 0; $i < count($table_list); $i++)
		{
			// По умолчанию 
			$spr_schema = 'dbo'; // в какой схеме таблица 
			$spr_nick = $table_list[$i]; // из какой таблицы брать данные 
			$spr_prefix = $table_list[$i]; // префикс полей updDT и insDT
			
			if (in_array($table_list[$i], array('DrugDisp'))) {
				$spr_nick = "SicknessDrug";
				$spr_prefix = $spr_nick;
			} elseif (preg_match('/rls/', $table_list[$i])) {
				$spr_schema = 'rls';
				$spr_nick = substr($table_list[$i], 4);
				$spr_prefix = $spr_nick;
			} elseif (in_array($table_list[$i], array('DrugFed', 'DrugReg'))) {
				$spr_prefix = "Drug";
				$spr_nick = "v_".$table_list[$i];
			} elseif ( $table_list[$i] == 'OMSSprTerrAddit' ) {
				$spr_nick = "OMSSprTerr";
				$spr_prefix = "OMSSprTerr";
			} elseif ( $table_list[$i] == 'LpuSearch' ) {
				$spr_nick = "Lpu";
				$spr_prefix = "Lpu";
			} elseif ( $table_list[$i] == 'SFPrehospDirect' ) {
				$spr_nick = "PrehospDirect";
				$spr_prefix = "PrehospDirect";
			} elseif ( preg_match('/msg/', $table_list[$i]) ) {
				$spr_schema = 'msg';
				$spr_nick = substr($table_list[$i], 4);
				$spr_prefix = $spr_nick;
			}
			
			$query .= "
				insert stg.LocalDBList (LocalDBList_name, LocalDBList_prefix, LocalDBList_nick, LocalDBList_schema, LocalDBList_sql, LocalDBList_module) values ('{$table_list[$i]}', '{$spr_prefix}', '{$spr_nick}', '{$spr_schema}', '', '{$data['mode']}'); ";
		}
		
		
		$result = $this->db->query($query, array());
		exit;
		// дальше эта херотень пока не заработала 
		$query .= "
		Declare @Error_Code int, @Error_Message varchar(4000)
		exec stg.xp_LocalDBVersion_generate @spr, :mode, :pmUser_id, @Error_Code output, @Error_Message output
		Select @Error_Code as Error_Code, @Error_Message as Error_Message";
		
		$params = array('mode'=>$data['mode'], 'pmUser_id'=>$data['pmUser_id']);
		/*
		echo getDebugSql($query, $params);
		exit;
		*/
		$result = $this->db->query($query, $params);

		if (is_object($result))
		{
			$d = $result->result('array');
			var_dump($d);
			return $result->result('array');
		}
		
		return $response;
	}
	/**
	 * Получение таблиц для синхронизации одной таблицы
	 * Входящие данные $data['mode'] - модуль (promed|farmacy) и $data['object'] - таблица
	 */
	function getSyncTable($data) {
		
		// Запросом получаем файлы нескольких версий (которые нам нужны) и того модуля который нам необходим 
		$query = "
		Select top 1
			list.LocalDBList_id,
			LocalDBTables_Name as SyncTable_name,
			LocalDBList_Key as SyncTable_key,
			LocalDbList_prefix as SyncTable_prefix,
			LocalDbList_nick as SyncTable_nick,
			LocalDbList_schema as SyncTable_schema,
			reg.RegionalLocalDbList_Sql as SyncTable_sql
		from stg.LocalDBTables tb with(nolock)
			inner join stg.LocalDBList list with(nolock) on list.LocalDBList_name = LocalDBTables_Name
			outer apply (
				select top 1 rldl.RegionalLocalDbList_Sql 
				from stg.RegionalLocalDBList rldl with (nolock) 
				where rldl.LocalDBList_id = list.LocalDBList_id
				and rldl.RegionalLocalDbList_Sql is not null
				and (rldl.Region_id is null or rldl.Region_id = :region)
				order by rldl.Region_id desc
			) reg
		where list.LocalDBList_module = :mode and tb.LocalDBTables_Name = :object
		";
		
		$params = array('mode'=>$data['mode'], 'object'=>$data['object'], 'region' => $data['region']);
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			$response = $result->result('array');
			return $response;
		}
		else {
			return false;
		}
	}
	
	/**
	 * Получение таблиц для синхронизации таблиц версии
	 * Входящие данные $data['mode'] - модуль (promed|farmacy) и $data['version'] - версия локальной базы клиента
	 */
	 
	function getSyncTables($data) {
		
		// Запросом получаем файлы нескольких версий (которые нам нужны) и того модуля который нам необходим 
		$query = "
		Select 
			distinct 
				LocalDBTables_Name as SyncTable_Name/*,*/
				/*LocalDBList_Key as SyncTable_Key*/
		from stg.LocalDBTables tb with(nolock)
			inner join stg.LocalDBVersion ver with(nolock) on ver.LocalDBVersion_id = tb.LocalDBVersion_id 
			inner join stg.LocalDBList list with(nolock) on list.LocalDBList_name = LocalDBTables_Name
		where list.LocalDBList_module = :mode and ver.LocalDBVersion_Ver > :version
		";
		
		$params = array('mode'=>$data['mode'], 'version'=>$data['version']);
		/*
		echo getDebugSql($query, $params);
		exit;
		*/
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			$response = $result->result('array');
			return $response;
		}
		else {
			return false;
		}
	}
	
	/**
	 * Получение таблиц для синхронизации таблиц версии БД и MongoDB 
	 * Входящие данные $data['mode'] - модуль (promed|farmacy) и $data['version'] - версия локальной базы клиента
	 */
	 
	function getSyncTablesAll($data) {
		
		// Запросом получаем файлы нескольких версий (которые нам нужны) и того модуля который нам необходим 
		$query = "
		Select 
			distinct 
				LocalDBTables_Name as SyncTable_name,
				LocalDBList_Key as SyncTable_key,
				LocalDbList_prefix as SyncTable_prefix,
				LocalDbList_nick as SyncTable_nick,
				LocalDbList_schema as SyncTable_schema,
				reg.RegionalLocalDbList_Sql as SyncTable_sql
		from stg.LocalDBTables tb with(nolock)
			inner join stg.LocalDBVersion ver with(nolock) on ver.LocalDBVersion_id = tb.LocalDBVersion_id 
			inner join stg.LocalDBList list with(nolock) on list.LocalDBList_name = LocalDBTables_Name
			outer apply (
				select top 1 rldl.RegionalLocalDbList_Sql 
				from stg.RegionalLocalDBList rldl with (nolock) 
				where rldl.LocalDBList_id = list.LocalDBList_id
				and rldl.RegionalLocalDbList_Sql is not null
				and (rldl.Region_id is null or rldl.Region_id = :region)
				order by rldl.Region_id desc
			) reg
		where list.LocalDBList_module = :mode and ver.LocalDBVersion_Ver > :version
		";
		
		$params = array('mode'=>$data['mode'], 'version'=>$data['version'], 'region'=>$data['region']);
		/*
		echo getDebugSql($query, $params);
		exit;
		*/
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			$response = $result->result('array');
			return $response;
		}
		else {
			return false;
		}
	}
}
?>
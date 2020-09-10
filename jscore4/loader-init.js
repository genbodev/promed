function getCommonSprDescr(sprName, allowSysNick){
	var l = [
		{ name: sprName + '_id', type: 'int' },
		{ name: sprName + '_Code', type: 'int' },
		{ name: sprName + '_Name', type: 'string' }
	];
	if(allowSysNick) {
		l.push({ name: sprName + '_SysNick', type: 'string' });
	}
	return l;
};
// Описание структуры таблиц локальных справочников
var spr_structure = {
/*
	DeseaseGroup: [
		{ name: 'DeseaseGroup_id', type: 'int' },
		{ name: 'DeseaseGroup_Code', type: 'int' },
		{ name: 'DeseaseGroup_Name', type: 'string' }
	],AnesthesiaClass
*/
	CmpArea: getCommonSprDescr('CmpArea'),
	MesOperType: getCommonSprDescr('MesOperType'),
	AbortPlace: getCommonSprDescr('AbortPlace'),
	AbortType: getCommonSprDescr('AbortType'),
	AggType: getCommonSprDescr('AggType'),
	AggWhen: getCommonSprDescr('AggWhen'),
	AllergicReactionLevel: getCommonSprDescr('AllergicReactionLevel'),
	AllergicReactionType: getCommonSprDescr('AllergicReactionType'),
	AnatomWhere: getCommonSprDescr('AnatomWhere'),
	AnesthesiaClass: getCommonSprDescr('AnesthesiaClass'),
	AttachType: getCommonSprDescr('AttachType'),
	BiopsyOrder: getCommonSprDescr('BiopsyOrder'),
	BirthChildResult: getCommonSprDescr('BirthChildResult'),
	BirthEducation: getCommonSprDescr('BirthEducation'),
	BirthEmployment: getCommonSprDescr('BirthEmployment'),
	BirthFamilyStatus: getCommonSprDescr('BirthFamilyStatus'),
	BirthMedPersonalType: getCommonSprDescr('BirthMedPersonalType'),
	BirthPlace: getCommonSprDescr('BirthPlace'),
	BirthResult: getCommonSprDescr('BirthResult'),
	BirthSpec: getCommonSprDescr('BirthSpec'),
	BirthSpecialist: getCommonSprDescr('BirthSpecialist'),
	BloodGroupType: getCommonSprDescr('BloodGroupType'),
	CardCloseCause: getCommonSprDescr('CardCloseCause'),
	CategoryLifeType: getCommonSprDescr('CategoryLifeType'),
	CategoryLifeDegreeType: getCommonSprDescr('CategoryLifeDegreeType'),
	CauseInputType: getCommonSprDescr('CauseInputType'),
	ChildPositionType: getCommonSprDescr('ChildPositionType'),//Предлежание новорожденного
	ChildTermType: getCommonSprDescr('ChildTermType'),
	ClinicalForecastType: getCommonSprDescr('ClinicalForecastType'),
	ClinicalPotentialType: getCommonSprDescr('ClinicalPotentialType'),
	CrazyCauseEndSurveyType: getCommonSprDescr('CrazyCauseEndSurveyType'),
	CrazyWorkPlaceType: getCommonSprDescr('CrazyWorkPlaceType'),
	CmpPlace: [
		{ name: 'CmpPlace_id', type: 'int' },
		{ name: 'CmpPlace_Code', type: 'string' },
		{ name: 'CmpPlace_Name', type: 'string' }
	],
	CmpDopInfo: [
		{ name: 'CmpDopInfo_id', type: 'int' },
		{ name: 'CmpDopInfo_Code', type: 'string' },
		{ name: 'CmpDopInfo_Name', type: 'string' }
	],
	CmpReason: [
		{ name: 'CmpReason_id', type: 'int' },
		{ name: 'CmpReason_Code', type: 'string' },
		{ name: 'CmpReason_Name', type: 'string' }
	],
	CmpCallType: [
		{ name: 'CmpCallType_id', type: 'int' },
		{ name: 'CmpCallType_Code', type: 'string' },
		{ name: 'CmpCallType_Name', type: 'string' }
	],
	CmpDrug: [
		{ name: 'CmpDrug_id', type: 'int' },
		{ name: 'CmpDrug_Code', type: 'int' },
		{ name: 'CmpDrug_Name', type: 'string' },
		{ name: 'CmpDrug_Ei', type: 'string' },
		{ name: 'CmpDrug_Kolvo', type: 'int' }
	],
	CmpDiag: [
		{ name: 'CmpDiag_id', type: 'int' },
		{ name: 'CmpDiag_Code', type: 'string' },
		{ name: 'CmpDiag_Name', type: 'string' }
	],
	CmpKindOfCall: [
		{ name: 'CmpKindOfCall_id', type: 'int' },
		{ name: 'CmpKindOfCall_Code', type: 'string' },
		{ name: 'CmpKindOfCall_Name', type: 'string' }
	],
	CmpLpu: [
		{ name: 'CmpLpu_id', type: 'int' },
		{ name: 'CmpLpu_Code', type: 'string' },
		{ name: 'CmpLpu_Name', type: 'string' }
	],
	CmpProfile: [
		{ name: 'CmpProfile_id', type: 'int' },
		{ name: 'CmpProfile_Code', type: 'string' },
		{ name: 'CmpProfile_Name', type: 'string' }
	],
	CmpProfileType: [
		{ name: 'CmpProfileType_id', type: 'int' },
		{ name: 'CmpProfileType_Code', type: 'string' },
		{ name: 'CmpProfileType_Name', type: 'string' }
	],
	CmpSmpGroups: [
		{ name: 'CmpSmpGroups_id', type: 'int' },
		{ name: 'CmpSmpGroups_Code', type: 'string' },
		{ name: 'CmpSmpGroups_Name', type: 'string' }
	],
	CmpResult: [
		{ name: 'CmpResult_id', type: 'int' },
		{ name: 'CmpResult_Code', type: 'string' },
		{ name: 'CmpResult_Name', type: 'string' }
	],
	CmpTrauma: [
		{ name: 'CmpTrauma_id', type: 'int' },
		{ name: 'CmpTrauma_Code', type: 'string' },
		{ name: 'CmpTrauma_Name', type: 'string' }
	],
	CmpTalon: [
		{ name: 'CmpTalon_id', type: 'int' },
		{ name: 'CmpTalon_Code', type: 'string' },
		{ name: 'CmpTalon_Name', type: 'string' }
	],
	DeathCause: [
		{ name: 'DeathCause_id', type: 'int' },
		{ name: 'DeathCause_Code', type: 'string' },
		{ name: 'DeathCause_Name', type: 'string' }
	],
	DeathEducation: [
		{ name: 'DeathEducation_id', type: 'int' },
		{ name: 'DeathEducation_Code', type: 'string' },
		{ name: 'DeathEducation_Name', type: 'string' }
	],
	DeathEmployment: [
		{ name: 'DeathEmployment_id', type: 'int' },
		{ name: 'DeathEmployment_Code', type: 'string' },
		{ name: 'DeathEmployment_Name', type: 'string' }
	],
	DeathFamilyStatus: [
		{ name: 'DeathFamilyStatus_id', type: 'int' },
		{ name: 'DeathFamilyStatus_Code', type: 'string' },
		{ name: 'DeathFamilyStatus_Name', type: 'string' }
	],
	DeathPlace: [
		{ name: 'DeathPlace_id', type: 'int' },
		{ name: 'DeathPlace_Code', type: 'string' },
		{ name: 'DeathPlace_Name', type: 'string' }
	],
	DeathSetCause: [
		{ name: 'DeathSetCause_id', type: 'int' },
		{ name: 'DeathSetCause_Code', type: 'string' },
		{ name: 'DeathSetCause_Name', type: 'string' }
	],
	DeathSetType: [
		{ name: 'DeathSetType_id', type: 'int' },
		{ name: 'DeathSetType_Code', type: 'string' },
		{ name: 'DeathSetType_Name', type: 'string' }
	],
	DeathSvidType: [
		{ name: 'DeathSvidType_id', type: 'int' },
		{ name: 'DeathSvidType_Code', type: 'string' },
		{ name: 'DeathSvidType_Name', type: 'string' }
	],
	DeathTrauma: [
		{ name: 'DeathTrauma_id', type: 'int' },
		{ name: 'DeathTrauma_Code', type: 'string' },
		{ name: 'DeathTrauma_Name', type: 'string' }
	],
	DeathWomanType: [
		{ name: 'DeathWomanType_id', type: 'int' },
		{ name: 'DeathWomanType_Code', type: 'string' },
		{ name: 'DeathWomanType_Name', type: 'string' }
	],
	DemandState: [
		{ name: 'DemandState_id', type: 'int' },
		{ name: 'DemandState_Name', type: 'string' }
	],
	DeseaseType: [
		{ name: 'DeseaseType_id', type: 'int' },
		{ name: 'DeseaseType_Code', type: 'int' },
		{ name: 'DeseaseType_Name', type: 'string' }
	],
	DeputyKind: [
		{ name: 'DeputyKind_id', type: 'int' },
		{ name: 'DeputyKind_Code', type: 'int' },
		{ name: 'DeputyKind_Name', type: 'string' }
	],
	DeseaseInfectionType: [
		{ name: 'DeseaseInfectionType_id', type: 'int' },
		{ name: 'DeseaseInfectionType_Code', type: 'int' },
		{ name: 'DeseaseInfectionType_Name', type: 'string' }
	],
	DeseaseFuncType: [
		{ name: 'DeseaseFuncType_id', type: 'int' },
		{ name: 'DeseaseFuncType_Code', type: 'int' },
		{ name: 'DeseaseFuncType_Name', type: 'string' }
	],
	DeseaseStage: [
		{ name: 'DeseaseStage_id', type: 'int' },
		{ name: 'DeseaseStage_Code', type: 'int' },
		{ name: 'DeseaseStage_Name', type: 'string' }
	],
	Diag: [
		{ name: 'Diag_id', type: 'int' },
		{ name: 'Diag_pid', type: 'int' },
		{ name: 'DiagLevel_id', type: 'int' },
		{ name: 'Diag_Code', type: 'string' },
		{ name: 'Diag_Name', type: 'string' },
		{ name: 'Diag_endDate', type: 'date', dateFormat: 'd.m.Y' },
		// Добавлены поля из DiagFinance для контроля диагноза
		{ name: 'PersonAgeGroup_Code', type: 'int' },
		{ name: 'Sex_Code', type: 'int' },
		{ name: 'DiagFinance_IsOms', type: 'int' },
		{ name: 'DiagFinance_IsAlien', type: 'int' },
		{ name: 'DiagFinance_IsHealthCenter', type: 'int' },
		{ name: 'MorbusType_id', type: 'int' }
	],
	DiagClinicalErrType: [
		{ name: 'DiagClinicalErrType_id', type: 'int' },
		{ name: 'DiagClinicalErrType_Code', type: 'int' },
		{ name: 'DiagClinicalErrType_Name', type: 'string' }
	],
	DiagReasonDiscrepancy: [
		{ name: 'DiagReasonDiscrepancy_id', type: 'int' },
		{ name: 'DiagReasonDiscrepancy_Code', type: 'int' },
		{ name: 'DiagReasonDiscrepancy_Name', type: 'string' }
	],
	DiagSetClass: [
		{ name: 'DiagSetClass_id', type: 'int' },
		{ name: 'DiagSetClass_Code', type: 'int' },
		{ name: 'DiagSetClass_Name', type: 'string' }
	],
	DiagSetPhase: getCommonSprDescr('DiagSetPhase'),
	DiagSetType: [
		{ name: 'DiagSetType_id', type: 'int' },
		{ name: 'DiagSetType_Code', type: 'int' },
		{ name: 'DiagSetType_Name', type: 'string' }
	],
	DiagType: [
		{ name: 'DiagType_id', type: 'int' },
		{ name: 'DiagType_Code', type: 'int' },
		{ name: 'DiagType_Name', type: 'string' }
	],
	DispRegistrationType: [
		{ name: 'DispRegistrationType_id', type: 'int' },
		{ name: 'DispRegistrationType_Code', type: 'int' },
		{ name: 'DispRegistrationType_Name', type: 'string' }
	],
	DopDispResType: [
		{ name: 'DopDispResType_id', type: 'int' },
		{ name: 'DopDispResType_Code', type: 'int' },
		{ name: 'DopDispResType_Name', type: 'string' }
	],
	DrugDocumentMotivation: [
		{ name: 'DrugDocumentMotivation_id', type: 'int' },
		{ name: 'DrugDocumentMotivation_Code', type: 'int' },
		{ name: 'DrugDocumentMotivation_Name', type: 'string' }
	],
	DrugDocumentStatus: [
		{ name: 'DrugDocumentStatus_id', type: 'int' },
		{ name: 'DrugDocumentStatus_Code', type: 'int' },
		{ name: 'DrugDocumentStatus_Name', type: 'string' }
	],
	DrugDocumentType: [
		{ name: 'DrugDocumentType_id', type: 'int' },
		{ name: 'DrugDocumentType_Code', type: 'int' },
		{ name: 'DrugDocumentType_Name', type: 'string' }
	],
	DrugTorgUse: [
		{ name: 'DrugTorgUse_id', type: 'int' },
		{ name: 'DrugTorgUse_Code', type: 'int' },
		{ name: 'DrugTorgUse_Name', type: 'string' }
	],
	DispResMedicalMeasureType: [
		{ name: 'DispResMedicalMeasureType_id', type: 'int' },
		{ name: 'DispResMedicalMeasureType_Code', type: 'int' },
		{ name: 'DispResMedicalMeasureType_Name', type: 'string' }
	],
	Teen14DispSpecType: [
		{ name: 'Teen14DispSpecType_id', type: 'int' },
		{ name: 'Teen14DispSpecType_Code', type: 'int' },
		{ name: 'Teen14DispSpecType_Name', type: 'string' },
		{ name: 'Teen14DispSpecType_SysNick', type: 'string' }
	],
	DirectClass: [
		{ name: 'DirectClass_id', type: 'int' },
		{ name: 'DirectClass_Code', type: 'int' },
		{ name: 'DirectClass_Name', type: 'string' }
	],
	DirectType: [
		{ name: 'DirectType_id', type: 'int' },
		{ name: 'DirectType_Code', type: 'int' },
		{ name: 'DirectType_Name', type: 'string' }
	],
	DirFailType: [
		{ name: 'DirFailType_id', type: 'int' },
		{ name: 'DirFailType_Name', type: 'string' }
	],
	DirType: [
		{ name: 'DirType_id', type: 'int' },
		{ name: 'DirType_Code', type: 'int' },
		{ name: 'DirType_Name', type: 'string' }
	],
	DispOutType: [
		{ name: 'DispOutType_id', type: 'int' },
		{ name: 'DispOutType_Code', type: 'int' },
		{ name: 'DispOutType_Name', type: 'string' }
	],
	DispUslugaTeen14Type: [
		{ name: 'DispUslugaTeen14Type_id', type: 'int' },
		{ name: 'DispUslugaTeen14Type_Code', type: 'int' },
		{ name: 'DispUslugaTeen14Type_Name', type: 'string' },
		{ name: 'DispUslugaTeen14Type_SysNick', type: 'string' }
	],
	DocumentType: [
		{ name: 'DocumentType_id', type: 'int' },
		{ name: 'DocumentType_Code', type: 'int' },
		{ name: 'DocumentType_Name', type: 'string' },
		{ name: 'DocumentType_MaskSer', type: 'string' },
		{ name: 'DocumentType_MaskNum', type: 'string' }
	],
	DopDispAlien: [
		{ name: 'DopDispAlien_id', type: 'int' },
		{ name: 'DopDispAlien_Code', type: 'int' },
		{ name: 'DopDispAlien_Name', type: 'string' }
	],
	DopDispDiagType: [
		{ name: 'DopDispDiagType_id', type: 'int' },
		{ name: 'DopDispDiagType_Code', type: 'int' },
		{ name: 'DopDispDiagType_Name', type: 'string' }
	],
	/*OrpDispDiagType: [
		{ name: 'OrpDispDiagType_id', type: 'int' },
		{ name: 'OrpDispDiagType_Code', type: 'int' },
		{ name: 'OrpDispDiagType_Name', type: 'string' }
	],*/
	DopDispSpec: [
		{ name: 'DopDispSpec_id', type: 'int' },
		{ name: 'DopDispSpec_Code', type: 'int' },
		{ name: 'DopDispSpec_Name', type: 'string' }
	],
	DopDispUslugaType: [
		{ name: 'DopDispUslugaType_id', type: 'int' },
		{ name: 'DopDispUslugaType_Code', type: 'int' },
		{ name: 'DopDispUslugaType_Name', type: 'string' }
	],
	DrugFed: [
		{ name: 'DrugMnnKey_id', type: 'int' },
		{ name: 'DrugMnn_id', type: 'int' },
		{ name: 'DrugMnn_Code', type: 'int' },
		{ name: 'DrugMnn_Name', type: 'string' },
		{ name: 'Drug_IsKEK', type: 'int' }
	],
	DrugFinance: [
		{ name: 'DrugFinance_id', type: 'int' },
		{ name: 'DrugFinance_Code', type: 'int' },
		{ name: 'DrugFinance_Name', type: 'string' },
		{ name: 'DrugFinance_begDate', type: 'date', dateFormat: 'd.m.Y' },
		{ name: 'DrugFinance_endDate', type: 'date', dateFormat: 'd.m.Y' }
	],
	DrugReg: [
		{ name: 'DrugMnnKey_id', type: 'int' },
		{ name: 'DrugMnn_id', type: 'int' },
		{ name: 'DrugMnn_Code', type: 'int' },
		{ name: 'DrugMnn_Name', type: 'string' },
		{ name: 'Drug_IsKEK', type: 'int' }
	],
	DrugDisp: [
		{ name: 'DrugMnnKey_id', type: 'int' },
		{ name: 'DrugMnn_id', type: 'int' },
		{ name: 'DrugMnn_Code', type: 'int' },
		{ name: 'DrugMnn_Name', type: 'string' },
		{ name: 'Drug_IsKEK', type: 'int' },
		{ name: 'PrivilegeType_id', type: 'int' }
	],
	DtpDeathTime: [
		{ name: 'DtpDeathTime_id', type: 'int' },
		{ name: 'DtpDeathTime_Code', type: 'int' },
		{ name: 'DtpDeathTime_Name', type: 'string' }
	],
	EmergencyTeamSpec: [
		{ name: 'EmergencyTeamSpec_id', type: 'int' },
		{ name: 'EmergencyTeamSpec_Code', type: 'string' },
		{ name: 'EmergencyTeamSpec_Name', type: 'string' }
	],
	EmergencyTeamStatus: [
		{ name: 'EmergencyTeamStatus_id', type: 'int' },
		{ name: 'EmergencyTeamStatus_Code', type: 'string' },
		{ name: 'EmergencyTeamStatus_Name', type: 'int' }
	],

	FeedingType: [
		{ name: 'FeedingType_id', type: 'int' },
		{ name: 'FeedingType_Code', type: 'int' },
		{ name: 'FeedingType_Name', type: 'string' }
	],
	FamilyStatus: [
		{ name: 'FamilyStatus_id', type: 'int' },
		{ name: 'FamilyStatus_Code', type: 'int' },
		{ name: 'FamilyStatus_Name', type: 'string' }
	],
	/*
	Glossary: [
		{ name: 'Glossary_id', type: 'int' },
		{ name: 'GlossarySynonym_id', type: 'int' },
		{ name: 'GlossaryTagType_id', type: 'int' },
		{ name: 'GlossaryTagType_SysNick', type: 'string' },
		{ name: 'Glossary_Descr', type: 'string' },
		{ name: 'pmUser_did', type: 'int' },
		{ name: 'Glossary_Word', type: 'string' }
	],*/
	GlossaryTagType: [
		{ name: 'GlossaryTagType_id', type: 'int' },
		{ name: 'GlossaryTagType_Code', type: 'int' },
		{ name: 'GlossaryTagType_Name', type: 'string' },
		{ name: 'GlossaryTagType_SysNick', type: 'string' }
	],
	HealthAbnorm: [
		{ name: 'HealthAbnorm_id', type: 'int' },
		{ name: 'HealthAbnorm_Code', type: 'int' },
		{ name: 'HealthAbnorm_Name', type: 'string' }
	],
	HealthAbnormDegree: [
		{ name: 'HealthAbnormDegree_id', type: 'int' },
		{ name: 'HealthAbnormDegree_Code', type: 'int' },
		{ name: 'HealthAbnormDegree_Name', type: 'string' }
	],
	HealthAbnormVital: [
		{ name: 'HealthAbnormVital_id', type: 'int' },
		{ name: 'HealthAbnormVital_Code', type: 'int' },
		{ name: 'HealthAbnormVital_Name', type: 'string' }
	],
	HealthKind: [
		{ name: 'HealthKind_id', type: 'int' },
		{ name: 'HealthKind_Code', type: 'int' },
		{ name: 'HealthKind_Name', type: 'string' }
	],
	HeightAbnormType: [
		{ name: 'HeightAbnormType_id', type: 'int' },
		{ name: 'HeightAbnormType_Code', type: 'int' },
		{ name: 'HeightAbnormType_Name', type: 'string' }
	],
	HeightMeasureType: [
		{ name: 'HeightMeasureType_id', type: 'int' },
		{ name: 'HeightMeasureType_Code', type: 'int' },
		{ name: 'HeightMeasureType_Name', type: 'string' }
	],
	HistologicMaterial: [
		{ name: 'HistologicMaterial_id', type: 'int' },
		{ name: 'HistologicMaterial_Code', type: 'int' },
		{ name: 'HistologicMaterial_Name', type: 'string' }
	],
	HistologicSpecimenPlace: [
		{ name: 'HistologicSpecimenPlace_id', type: 'int' },
		{ name: 'HistologicSpecimenPlace_Code', type: 'int' },
		{ name: 'HistologicSpecimenPlace_Name', type: 'string' }
	],
	HistologicSpecimenSaint: [
		{ name: 'HistologicSpecimenSaint_id', type: 'int' },
		{ name: 'HistologicSpecimenSaint_Code', type: 'int' },
		{ name: 'HistologicSpecimenSaint_Name', type: 'string' }
	],
	HIVPregPathTransType: getCommonSprDescr('HIVPregPathTransType',false), //Предполагаемый путь инфицирования
	HIVPregInfectStudyType: getCommonSprDescr('HIVPregInfectStudyType',false), //Стадия ВИЧ-инфекции. 
	HIVDispOutCauseType: getCommonSprDescr('HIVDispOutCauseType',false),//Причина снятия с диспансерного наблюдения.
	HIVChildType: getCommonSprDescr('HIVChildType',false), // Ребенок
	HIVContingentType: [ //Код контингента
		{ name: 'HIVContingentType_id', type: 'int' },
		{ name: 'HIVContingentType_pid', type: 'int' },
		{ name: 'HIVContingentType_Code', type: 'int' },
		{ name: 'HIVContingentType_Name', type: 'string' }
	],
	HIVPregAbortPeriodType: getCommonSprDescr('HIVPregAbortPeriodType',false), //Срок беременности при аборте
	HIVPregChemProphType: getCommonSprDescr('HIVPregChemProphType',false), //Химиопрофилактика в период беременности
	HIVPregnancyTermType: getCommonSprDescr('HIVPregnancyTermType',false), //Период проведение перинатальной профилактики
	HIVPregPeriodType: getCommonSprDescr('HIVPregPeriodType',false), //Период установления диагноза
	HIVPregResultType: getCommonSprDescr('HIVPregResultType',false), //Результат беременности
	HIVPregWayBirthType: getCommonSprDescr('HIVPregWayBirthType',false), //Способ родоразрешения
	HIVRegPregnancyType: getCommonSprDescr('HIVRegPregnancyType',false), //Срок постановки на учет в ЖК
	HIVNotifyType: getCommonSprDescr('HIVNotifyType',true), //тип извещения
	InvalidKind: [
		{ name: 'InvalidKind_id', type: 'int' },
		{ name: 'InvalidKind_Code', type: 'int' },
		{ name: 'InvalidKind_Name', type: 'string' }
	],
	InvalidType: [
		{ name: 'InvalidType_id', type: 'int' },
		{ name: 'InvalidType_Code', type: 'int' },
		{ name: 'InvalidType_Name', type: 'string' }
	],
	InvalidGroupType: [
		{ name: 'InvalidGroupType_id', type: 'int' },
		{ name: 'InvalidGroupType_Code', type: 'int' },
		{ name: 'InvalidGroupType_Name', type: 'string' }
	],
	KatNasel: [
		{ name: 'KatNasel_id', type: 'int' },
		{ name: 'KatNasel_Code', type: 'int' },
		{ name: 'KatNasel_Name', type: 'string' }
	],
	KLAreaStat: [
		{ name: 'KLAreaStat_id', type: 'int' },
		{ name: 'KLAreaStat_Code', type: 'int' },
		{ name: 'KLArea_Name', type: 'string'},
		{ name: 'KLCountry_id', type: 'int' },
		{ name: 'KLRGN_id', type: 'int' },
		{ name: 'KLSubRGN_id', type: 'int' },
		{ name: 'KLCity_id', type: 'int'},
		{ name: 'KLTown_id', type: 'int'}
	],
	KLAreaType: [
		{ name: 'KLAreaType_id', type: 'int' },
		{ name: 'KLAreaType_Code', type: 'int' },
		{ name: 'KLAreaType_Name', type: 'string' }
	],
	PersonSprTerrDop: [
		{ name: 'PersonSprTerrDop_id', type: 'int' },
		{ name: 'PersonSprTerrDop_Code', type: 'int' },
		{ name: 'PersonSprTerrDop_Name', type: 'string' },
		{ name: 'KLAdr_Ocatd', type: 'string' }
	],
	PersonCardAttachStatusType: [
		{ name: 'PersonCardAttachStatusType_id', type: 'int' },
		{ name: 'PersonCardAttachStatusType_Code', type: 'int' },
		{ name: 'PersonCardAttachStatusType_Name', type: 'string' }
	],
	KLCountry: [
		{ name: 'KLCountry_id', type: 'int' },
		{ name: 'KLCountry_Code', type: 'int' },
		{ name: 'KLCountry_Name', type: 'string' }
	],
	KLSocr: [
		{ name: 'KLSocr_id', type: 'int' },
		{ name: 'KLSocr_Nick', type: 'string' },
        { name: 'KLAreaLevel_id', type: 'int' }
	],
	LearnGroupType: [
		{ name: 'LearnGroupType_id', type: 'int' },
		{ name: 'LearnGroupType_Code', type: 'int' },
		{ name: 'LearnGroupType_Name', type: 'string' }
	],
	LeaveCause: [
		{ name: 'LeaveCause_id', type: 'int' },
		{ name: 'LeaveCause_Code', type: 'int' },
		{ name: 'LeaveCause_Name', type: 'string' }
	],
	LeaveType: [
		{ name: 'LeaveType_id', type: 'int' },
		{ name: 'LeaveType_Code', type: 'int' },
		{ name: 'LeaveType_Name', type: 'string' }
	],
	Lpu: [
		{ name: 'Lpu_id', type: 'int' },
		{ name: 'LpuLevel_Code', type: 'int' },
		{ name: 'Lpu_IsOblast', type: 'int' },
		{ name: 'Lpu_Name', type: 'string' },
		{ name: 'Lpu_Nick', type: 'string' },
		{ name: 'Lpu_Ouz', type: 'int' },
		{ name: 'Lpu_RegNomC', type: 'int' },
		{ name: 'Lpu_RegNomC2', type: 'int' },
		{ name: 'Lpu_RegNomN2', type: 'int' },
		{ name: 'Lpu_isDMS', type: 'int' },
		{ name: 'Lpu_DloBegDate', type: 'date', dateFormat: 'd.m.Y' },
		{ name: 'Lpu_DloEndDate', type: 'date', dateFormat: 'd.m.Y' },
		{ name: 'Lpu_BegDate', type: 'date', dateFormat: 'd.m.Y' },
		{ name: 'Lpu_EndDate', type: 'date', dateFormat: 'd.m.Y' }
	],
	LpuLevel: [
		{ name: 'LpuLevel_id', type: 'int' },
		{ name: 'LpuLevel_Code', type: 'int' },
		{ name: 'LpuLevel_Name', type: 'string' },
		{ name: 'LpuLevel_SysNick', type: 'string' }
	],
	LpuSearch: [
		{ name: 'Lpu_id', type: 'int' },
		{ name: 'Lpu_IsOblast', type: 'int' },
		{ name: 'Lpu_Name', type: 'string' },
		{ name: 'Lpu_Nick', type: 'string' },
		{ name: 'Lpu_Ouz', type: 'int' },
		{ name: 'Lpu_RegNomC', type: 'int' },
		{ name: 'Lpu_RegNomC2', type: 'int' },
		{ name: 'Lpu_RegNomN2', type: 'int' },
		{ name: 'Lpu_DloBegDate', type: 'date', dateFormat: 'd.m.Y' },
		{ name: 'Lpu_DloEndDate', type: 'date', dateFormat: 'd.m.Y' },
		{ name: 'Lpu_BegDate', type: 'date', dateFormat: 'd.m.Y' },
		{ name: 'Lpu_EndDate', type: 'date', dateFormat: 'd.m.Y' }
	],
	LpuAttachType: [
		{ name: 'LpuAttachType_id', type: 'int' },
		{ name: 'LpuAttachType_Code', type: 'int' },
		{ name: 'LpuAttachType_Name', type: 'string' }
	],
	LpuRegionType: [
		{ name: 'LpuRegionType_id', type: 'int' },
		{ name: 'LpuRegionType_Code', type: 'int' },
		{ name: 'LpuRegionType_Name', type: 'string' }
	],
	LpuSectionProfile: [
		{ name: 'LpuSectionProfile_id', type: 'int' },
		{ name: 'LpuSectionProfile_Code', type: 'int' },
		{ name: 'LpuSectionProfile_Name', type: 'string' },
		{ name: 'LpuSectionProfile_begDT', type: 'date', dateFormat: 'd.m.Y' },
		{ name: 'LpuSectionProfile_endDT', type: 'date', dateFormat: 'd.m.Y' }
	],
	LpuType: [
		{ name: 'LpuType_id', type: 'int' },
		{ name: 'LpuType_Code', type: 'int' },
		{ name: 'LpuType_Name', type: 'string' }
	],
	LpuWardType: [
		{ name: 'LpuWardType_id', type: 'int' },
		{ name: 'LpuWardType_Code', type: 'int' },
		{ name: 'LpuWardType_Name', type: 'string' }
	],
	LpuUnitType: [
		{ name: 'LpuUnitType_id', type: 'int' },
		{ name: 'LpuUnitType_Code', type: 'int' },
		{ name: 'LpuUnitType_Name', type: 'string' }
	],
	LpuSectionAge: [
		{ name: 'LpuSectionAge_id', type: 'int' },
		{ name: 'LpuSectionAge_Code', type: 'int' },
		{ name: 'LpuSectionAge_Name', type: 'string' }
	],
	LpuSectionBedProfile: [
		{ name: 'LpuSectionBedProfile_id', type: 'int' },
		{ name: 'LpuSectionBedProfile_Code', type: 'int' },
		{ name: 'LpuSectionBedProfile_Name', type: 'string' },
		{ name: 'LpuSectionBedProfile_begDT', type: 'date', dateFormat: 'd.m.Y' },
		{ name: 'LpuSectionBedProfile_endDT', type: 'date', dateFormat: 'd.m.Y' }
	],
	LpuSubjectionLevel: [
		{ name: 'LpuSubjectionLevel_id', type: 'int' },
		{ name: 'LpuSubjectionLevel_Code', type: 'int' },
		{ name: 'LpuSubjectionLevel_Name', type: 'string' }
	],
	MESLevel: [
		{ name: 'MESLevel_id', type: 'int' },
		{ name: 'MESLevel_Code', type: 'int' },
		{ name: 'MESLevel_Name', type: 'string' }
	],
	MesAgeGroup: [
		{ name: 'MesAgeGroup_id', type: 'int' },
		{ name: 'MesAgeGroup_Code', type: 'int' },
		{ name: 'MesAgeGroup_Name', type: 'string' }
	],
	MesProf: [
		{ name: 'MesProf_id', type: 'int' },
		{ name: 'MesProf_Code', type: 'string' },
		{ name: 'MesProf_Name', type: 'string' }
	],
	MedServiceType: [
		{ name: 'MedServiceType_id', type: 'int' },
		{ name: 'MedServiceType_Code', type: 'int' },
		{ name: 'MedServiceType_Name', type: 'string' },
		{ name: 'MedServiceType_IsAdmin', type: 'int' },
		{ name: 'MedServiceType_SysNick', type: 'string' }
	],
	MedSpec: [
		{ name: 'MedSpec_id', type: 'int' },
		{ name: 'MedSpec_Code', type: 'int' },
		{ name: 'MedSpec_Name', type: 'string' }
	],
	MedSpecOms: [
		{ name: 'MedSpecOms_id', type: 'int' },
		{ name: 'MedSpecOms_Code', type: 'int' },
		{ name: 'MedSpecOms_Name', type: 'string' }
	],
	MseDirectionAimType: [
		{ name: 'MseDirectionAimType_id', type: 'int' },
		{ name: 'MseDirectionAimType_Code', type: 'int' },
		{ name: 'MseDirectionAimType_Name', type: 'string' }
	],
	Nationality: [
		{ name: 'Nationality_id', type: 'int' },
		{ name: 'Nationality_Code', type: 'int' },
		{ name: 'Nationality_Name', type: 'string' }
	],
	MedStatus: [
		{ name: 'MedStatus_id', type: 'int' },
		{ name: 'MedStatus_Name', type: 'string' }
	],
	MorbusDiag: [
		{ name: 'MorbusDiag_id', type: 'int' },
		{ name: 'MorbusType_id', type: 'int' },
		{ name: 'Diag_id', type: 'int' }
	],	
	MorbusType: [
		{ name: 'MorbusType_id', type: 'int' },
		{ name: 'MorbusType_Code', type: 'int' },
		{ name: 'MorbusType_Name', type: 'string' },
		{ name: 'MorbusType_SysNick', type: 'string' }
	],	
	MorfoHistologicItemsType: [
		{ name: 'MorfoHistologicItemsType_id', type: 'int' },
		{ name: 'MorfoHistologicItemsType_Code', type: 'int' },
		{ name: 'MorfoHistologicItemsType_Name', type: 'string' }
	],
	OrgHeadPost: [
		{ name: 'OrgHeadPost_id', type: 'int' },
		{ name: 'OrgHeadPost_Name', type: 'string' }
	],
	ObservParamType: [
		{ name: 'ObservParamType_id', type: 'int' },
		{ name: 'ObservParamType_Code', type: 'int' },
		{ name: 'ObservParamType_Name', type: 'string' }
	],
	ObservTimeType: [
		{ name: 'ObservTimeType_id', type: 'int' },
		{ name: 'ObservTimeType_Code', type: 'int' },
		{ name: 'ObservTimeType_Name', type: 'string' }
	],
	OrgType: getCommonSprDescr('OrgType', true),
	Okei: [
		{ name: 'Okei_id', type: 'int' },
		{ name: 'OkeiType_id', type: 'int' },
		{ name: 'Okei_Code', type: 'int' },
		{ name: 'Okei_Name', type: 'string' },
		{ name: 'Okei_NationSymbol', type: 'string' },
		{ name: 'Okei_InterNationSymbol', type: 'string' },
		{ name: 'Okei_NationCode', type: 'string' },
		{ name: 'Okei_InterNationCode', type: 'string' },
		{ name: 'Okei_cid', type: 'int' },
		{ name: 'Okei_UnitConversion', type: 'int' }
	],
	OkeiType: getCommonSprDescr('OkeiType'),
	Okved: [
		{ name: 'Okved_id', type: 'int' },
		{ name: 'Okved_Code', type: 'string' },
		{ name: 'Okved_Name', type: 'string'}
	],
	Okonh: getCommonSprDescr('Okonh', false),
	Okogu: getCommonSprDescr('Okogu', false),
	Okopf: getCommonSprDescr('Okopf', false),
	Okfs: getCommonSprDescr('Okfs', false),
	OmsLpuUnitType: getCommonSprDescr('OmsLpuUnitType', false),
	OMSSprTerr: [
		{ name: 'OMSSprTerr_id', type: 'int' },
		{ name: 'OMSSprTerr_Code', type: 'int' },
		{ name: 'OMSSprTerr_Name', type: 'string' },
		{ name: 'KLRgn_id', type: 'string' }
	],
	OMSSprTerrAddit: [
		{ name: 'OMSSprTerr_id', type: 'int' },
		{ name: 'OMSSprTerr_Code', type: 'int' },
		{ name: 'OMSSprTerr_Name', type: 'string' },
		{ name: 'KLRgn_id', type: 'string' }
	],
	OperDiff: [
		{ name: 'OperDiff_id', type: 'int' },
		{ name: 'OperDiff_Code', type: 'int' },
		{ name: 'OperDiff_Name', type: 'string' }
	],
	OperType: getCommonSprDescr('OperType', false),
	OrgSMO: [
		{ name: 'OrgSMO_id', type: 'int' },
		{ name: 'OrgSMO_RegNomC', type: 'int' },
		{ name: 'OrgSMO_RegNomN', type: 'int' },
		{ name: 'OrgSMO_Nick', type: 'string' },
		{ name: 'KLRgn_id', type: 'int' },
		{ name: 'OrgSMO_isDMS', type: 'int' },
		{ name: 'OrgSMO_endDate', type: 'date', dateFormat: 'd.m.Y' }
	],
	OrpDispSpec: [
		{ name: 'OrpDispSpec_id', type: 'int' },
		{ name: 'OrpDispSpec_Code', type: 'int' },
		{ name: 'OrpDispSpec_Name', type: 'string' }
	],
	OrpDispUslugaType: [
		{ name: 'OrpDispUslugaType_id', type: 'int' },
		{ name: 'OrpDispUslugaType_Code', type: 'int' },
		{ name: 'OrpDispUslugaType_Name', type: 'string' }
	],
	ParameterValueListType: getCommonSprDescr('ParameterValueListType', true),
	PayType: getCommonSprDescr('PayType', true),
	PsychicalConditionType: getCommonSprDescr('PsychicalConditionType', false),
	PntDeathCause: [
		{ name: 'PntDeathCause_id', type: 'int' },
		{ name: 'PntDeathCause_Code', type: 'string' },
		{ name: 'PntDeathCause_Name', type: 'string' }
	],
	PntDeathEducation: [
		{ name: 'PntDeathEducation_id', type: 'int' },
		{ name: 'PntDeathEducation_Code', type: 'string' },
		{ name: 'PntDeathEducation_Name', type: 'string' }
	],
	PntDeathFamilyStatus: [
		{ name: 'PntDeathFamilyStatus_id', type: 'int' },
		{ name: 'PntDeathFamilyStatus_Code', type: 'string' },
		{ name: 'PntDeathFamilyStatus_Name', type: 'string' }
	],
	PntDeathGetBirth: [
		{ name: 'PntDeathGetBirth_id', type: 'int' },
		{ name: 'PntDeathGetBirth_Code', type: 'string' },
		{ name: 'PntDeathGetBirth_Name', type: 'string' }
	],
	PntDeathPeriod: [
		{ name: 'PntDeathPeriod_id', type: 'int' },
		{ name: 'PntDeathPeriod_Code', type: 'string' },
		{ name: 'PntDeathPeriod_Name', type: 'string' }
	],
	PntDeathPlace: [
		{ name: 'PntDeathPlace_id', type: 'int' },
		{ name: 'PntDeathPlace_Code', type: 'string' },
		{ name: 'PntDeathPlace_Name', type: 'string' }
	],
	PntDeathSetCause: [
		{ name: 'PntDeathSetCause_id', type: 'int' },
		{ name: 'PntDeathSetCause_Code', type: 'string' },
		{ name: 'PntDeathSetCause_Name', type: 'string' }
	],
	PntDeathSetType: [
		{ name: 'PntDeathSetType_id', type: 'int' },
		{ name: 'PntDeathSetType_Code', type: 'string' },
		{ name: 'PntDeathSetType_Name', type: 'string' }
	],
	PntDeathTime: [
		{ name: 'PntDeathTime_id', type: 'int' },
		{ name: 'PntDeathTime_Code', type: 'string' },
		{ name: 'PntDeathTime_Name', type: 'string' }
	],
	PolisType: [
		{ name: 'PolisType_id', type: 'int' },
		{ name: 'PolisType_Code', type: 'int' },
		{ name: 'PolisType_Name', type: 'string' }
	],
	PostMed: [
		{ name: 'PostMed_id', type: 'int' },
		{ name: 'PostMed_Name', type: 'string' }
	],
	/*PostMedCat: [
		{ name: 'PostMedCat_id', type: 'int' },
		{ name: 'PostMedCat_Code', type: 'int' },
		{ name: 'PostMedCat_Name', type: 'string' }
	],
	PostMedClass: [
		{ name: 'PostMedClass_id', type: 'int' },
		{ name: 'PostMedClass_Name', type: 'string' }
	],*/
	PostMedType: [
		{ name: 'PostMedType_id', type: 'int' },
		{ name: 'PostMedType_Code', type: 'int' },
		{ name: 'PostMedType_Name', type: 'string' }
	],
	PrehospArrive: [
		{ name: 'PrehospArrive_id', type: 'int' },
		{ name: 'PrehospArrive_Code', type: 'int' },
		{ name: 'PrehospArrive_Name', type: 'string' },
		{ name: 'PrehospArrive_SysNick', type: 'string' }
	],
	PrehospDefect: [
		{ name: 'PrehospDefect_id', type: 'int' },
		{ name: 'PrehospDefect_Code', type: 'int' },
		{ name: 'PrehospDefect_Name', type: 'string' },
		{ name: 'PrehospDefect_SysNick', type: 'string' }
	],
	PrehospDirect: [
		{ name: 'PrehospDirect_id', type: 'int' },
		{ name: 'PrehospDirect_Code', type: 'int' },
		{ name: 'PrehospDirect_Name', type: 'string' },
		{ name: 'PrehospDirect_SysNick', type: 'string' }
	],
	PrehospToxic: [
		{ name: 'PrehospToxic_id', type: 'int' },
		{ name: 'PrehospToxic_Code', type: 'int' },
		{ name: 'PrehospToxic_Name', type: 'string' },
		{ name: 'PrehospToxic_SysNick', type: 'string' }
	],
	PrehospTrauma: [
		{ name: 'PrehospTrauma_id', type: 'int' },
		{ name: 'PrehospTrauma_Code', type: 'int' },
		{ name: 'PrehospTrauma_Name', type: 'string' },
		{ name: 'TraumaType_id', type: 'int' },
		{ name: 'TraumaType_Code', type: 'int' }
	],
	PrehospType: [
		{ name: 'PrehospType_id', type: 'int' },
		{ name: 'PrehospType_Code', type: 'int' },
		{ name: 'PrehospType_Name', type: 'string' },
		{ name: 'PrehospType_SysNick', type: 'string' }
	],
	PrehospStatus: [
		{ name: 'PrehospStatus_id', type: 'int' },
		{ name: 'PrehospStatus_Code', type: 'int' },
		{ name: 'PrehospStatus_Name', type: 'string' }
	],
	PrehospWaifArrive: [
		{ name: 'PrehospWaifArrive_id', type: 'int' },
		{ name: 'PrehospWaifArrive_Code', type: 'int' },
		{ name: 'PrehospWaifArrive_Name', type: 'string' }
	],
	PrehospWaifReason: [
		{ name: 'PrehospWaifReason_id', type: 'int' },
		{ name: 'PrehospWaifReason_Code', type: 'int' },
		{ name: 'PrehospWaifReason_Name', type: 'string' }
	],
	PrehospWaifRefuseCause: [
		{ name: 'PrehospWaifRefuseCause_id', type: 'int' },
		{ name: 'PrehospWaifRefuseCause_Code', type: 'int' },
		{ name: 'PrehospWaifRefuseCause_Name', type: 'string' }
	],
	PrehospWaifRetired: [
		{ name: 'PrehospWaifRetired_id', type: 'int' },
		{ name: 'PrehospWaifRetired_Code', type: 'int' },
		{ name: 'PrehospWaifRetired_Name', type: 'string' }
	],
	PrescriptionDietType: [
		{ name: 'PrescriptionDietType_id', type: 'int' },
		{ name: 'PrescriptionDietType_Code', type: 'string' },
		{ name: 'PrescriptionDietType_Name', type: 'string' }
	],
	PrescriptionIntroType: [
		{ name: 'PrescriptionIntroType_id', type: 'int' },
		{ name: 'PrescriptionIntroType_Code', type: 'int' },
		{ name: 'PrescriptionIntroType_Name', type: 'string' }
	],
	PrescriptionRegimeType: [
		{ name: 'PrescriptionRegimeType_id', type: 'int' },
		{ name: 'PrescriptionRegimeType_Code', type: 'int' },
		{ name: 'PrescriptionRegimeType_Name', type: 'string' }
	],
	PrescriptionStatusType: [
		{ name: 'PrescriptionStatusType_id', type: 'int' },
		{ name: 'PrescriptionStatusType_Code', type: 'int' },
		{ name: 'PrescriptionStatusType_Name', type: 'string' }
	],
	PrescriptionTreatType: [
		{ name: 'PrescriptionTreatType_id', type: 'int' },
		{ name: 'PrescriptionTreatType_Code', type: 'int' },
		{ name: 'PrescriptionTreatType_Name', type: 'string' }
	],
	PrescriptionType: [
		{ name: 'PrescriptionType_id', type: 'int' },
		{ name: 'PrescriptionType_Code', type: 'int' },
		{ name: 'PrescriptionType_Name', type: 'string' }
	],
	PrivilegeType: [
		{ name: 'PrivilegeType_id', type: 'int' },
		{ name: 'PrivilegeType_Code', type: 'int' },
		{ name: 'PrivilegeType_Name', type: 'string' },
		{ name: 'ReceptDiscount_id', type: 'int' },
		{ name: 'ReceptFinance_id', type: 'int' },
		{ name: 'PrivilegeType_begDate', type: 'date', dateFormat: 'd.m.Y' },
		{ name: 'PrivilegeType_endDate', type: 'date', dateFormat: 'd.m.Y' }
		
	],
	ProfGoal: [
		{ name: 'ProfGoal_id', type: 'int' },
		{ name: 'ProfGoal_Code', type: 'int' },
		{ name: 'ProfGoal_Name', type: 'string' }
	],
	QueueFailCause: [
		{ name: 'QueueFailCause_id', type: 'int' },
		{ name: 'QueueFailCause_Name', type: 'string' }
	],
	RecType: [
		{ name: 'RecType_id', type: 'int' },
		{ name: 'RecType_Name', type: 'string' }
	],
	ReceptDelayType: [
		{ name: 'ReceptDelayType_id', type: 'int' },
		{ name: 'ReceptDelayType_Code', type: 'int' },
		{ name: 'ReceptDelayType_Name', type: 'string' }
	],
	ReceptDiscount: [
		{ name: 'ReceptDiscount_id', type: 'int' },
		{ name: 'ReceptDiscount_Code', type: 'int' },
		{ name: 'ReceptDiscount_Name', type: 'string' }
	],
	ReceptFinance: [
		{ name: 'ReceptFinance_id', type: 'int' },
		{ name: 'ReceptFinance_Code', type: 'int' },
		{ name: 'ReceptFinance_Name', type: 'string' }
	],
	ReceptRemoveCauseType: [
		{ name: 'ReceptRemoveCauseType_id', type: 'int' },
		{ name: 'ReceptRemoveCauseType_Code', type: 'int' },
		{ name: 'ReceptRemoveCauseType_Name', type: 'string' },
		{ name: 'ReceptRemoveCauseType_SysNick', type: 'string' }
	],
	ReceptType: [
		{ name: 'ReceptType_id', type: 'int' },
		{ name: 'ReceptType_Code', type: 'int' },
		{ name: 'ReceptType_Name', type: 'string' }
	],
	ReceptValid: [
		{ name: 'ReceptValid_id', type: 'int' },
		{ name: 'ReceptValid_Code', type: 'int' },
		{ name: 'ReceptValid_Name', type: 'string' }
	],
	RecommendationsTreatmentType: [
		{ name: 'RecommendationsTreatmentType_id', type: 'int' },
		{ name: 'RecommendationsTreatmentType_Code', type: 'int' },
		{ name: 'RecommendationsTreatmentType_Name', type: 'string' }
	],
	RecommendationsTreatmentDopType: [
		{ name: 'RecommendationsTreatmentDopType_id', type: 'int' },
		{ name: 'RecommendationsTreatmentDopType_Code', type: 'int' },
		{ name: 'RecommendationsTreatmentDopType_Name', type: 'string' }
	],
	ResidPlace: [
		{ name: 'ResidPlace_id', type: 'int' },
		{ name: 'ResidPlace_Code', type: 'string' },
		{ name: 'ResidPlace_Name', type: 'string' }
	],
	RegistryStatus: [
		{ name: 'RegistryStatus_id', type: 'int' },
		{ name: 'RegistryStatus_Code', type: 'int' },
		{ name: 'RegistryStatus_Name', type: 'string' }
	],
	RegistryType: [
		{ name: 'RegistryType_id', type: 'int' },
		{ name: 'RegistryType_Code', type: 'int' },
		{ name: 'RegistryType_Name', type: 'string' }
	],
	ResultClass: [
		{ name: 'ResultClass_id', type: 'int' },
		{ name: 'ResultClass_Code', type: 'int' },
		{ name: 'ResultClass_Name', type: 'string' },
		{ name: 'ResultClass_begDT', type: 'date', dateFormat: 'd.m.Y' },
		{ name: 'ResultClass_endDT', type: 'date', dateFormat: 'd.m.Y' }
	],
	ResultDesease: [
		{ name: 'ResultDesease_id', type: 'int' },
		{ name: 'ResultDesease_Code', type: 'int' },
		{ name: 'ResultDesease_Name', type: 'string' }
	],
	RhFactorType: [
		{ name: 'RhFactorType_id', type: 'int' },
		{ name: 'RhFactorType_Code', type: 'int' },
		{ name: 'RhFactorType_Name', type: 'string' }
	],
	SanationStatus: [
		{ name: 'SanationStatus_id', type: 'int' },
		{ name: 'SanationStatus_Code', type: 'int' },
		{ name: 'SanationStatus_Name', type: 'string' }
	],
	ServiceType: [
		{ name: 'ServiceType_id', type: 'int' },
		{ name: 'ServiceType_Code', type: 'string' },
		{ name: 'ServiceType_SysNick', type: 'string' },
		{ name: 'ServiceType_Name', type: 'string' },
		{ name: 'ServiceType_begDate', type: 'date', dateFormat: 'd.m.Y' },
		{ name: 'ServiceType_endDate', type: 'date', dateFormat: 'd.m.Y' }
	],
	Sex: [
		{ name: 'Sex_id', type: 'int' },
		{ name: 'Sex_Code', type: 'int' },
		{ name: 'Sex_Name', type: 'string' }
	],
	SexualConditionType: [
		{ name: 'SexualConditionType_id', type: 'int' },
		{ name: 'SexualConditionType_Code', type: 'int' },
		{ name: 'SexualConditionType_Name', type: 'string' }
	],
	SFPrehospDirect: [
		{ name: 'SFPrehospDirect_id', type: 'int' },
		{ name: 'SFPrehospDirect_Code', type: 'int' },
		{ name: 'SFPrehospDirect_Name', type: 'string' },
		{ name: 'SFPrehospDirect_SysNick', type: 'string' }
	],
	Sickness: [
		{ name: 'Sickness_id', type: 'int' },
		{ name: 'Sickness_Code', type: 'int' },
		{ name: 'PrivilegeType_id', type: 'int' },
		{ name: 'Sickness_Name', type: 'string' }
	],
	SicknessDiag: [
		{ name: 'SicknessDiag_id', type: 'int' },
		{ name: 'Sickness_id', type: 'int' },
		{ name: 'Sickness_Code', type: 'int' },
		{ name: 'PrivilegeType_id', type: 'int' },
		{ name: 'Sickness_Name', type: 'string' },
		{ name: 'Diag_id', type: 'int' }
	],
	SocStatus: [
		{ name: 'SocStatus_id', type: 'int' },
		{ name: 'SocStatus_Code', type: 'string' },
		{ name: 'SocStatus_Name', type: 'string' }
	],
	SpecificType: [
		{ name: 'SpecificType_id', type: 'int' },
		{ name: 'SpecificType_Code', type: 'int' },
		{ name: 'SpecificType_Name', type: 'string' }
	],
	StateNormType: [
		{ name: 'StateNormType_id', type: 'int' },
		{ name: 'StateNormType_Code', type: 'int' },
		{ name: 'StateNormType_Name', type: 'string' }
	],
	StickCause: [
		{ name: 'StickCause_id', type: 'int' },
		{ name: 'StickCause_Code', type: 'string' },
		{ name: 'StickCause_Name', type: 'string' },
		{ name: 'StickCause_SysNick', type: 'string' }
	],
	StickCauseDopType: [
		{ name: 'StickCauseDopType_id', type: 'int' },
		{ name: 'StickCauseDopType_Code', type: 'int' },
		{ name: 'StickCauseDopType_Name', type: 'string' }
	],
	StickIrregularity: [
		{ name: 'StickIrregularity_id', type: 'int' },
		{ name: 'StickIrregularity_Code', type: 'string' },
		{ name: 'StickIrregularity_Name', type: 'string' }
	],
	StickLeaveType: [
		{ name: 'StickLeaveType_id', type: 'int' },
		{ name: 'StickLeaveType_Code', type: 'string' },
		{ name: 'StickLeaveType_Name', type: 'string' }
	],
	StickOrder: [
		{ name: 'StickOrder_id', type: 'int' },
		{ name: 'StickOrder_Code', type: 'int' },
		{ name: 'StickOrder_Name', type: 'string' }
	],
	StickRecipient: [
		{ name: 'StickRecipient_id', type: 'int' },
		{ name: 'StickRecipient_Code', type: 'int' },
		{ name: 'StickRecipient_Name', type: 'string' }
	],
	StickRegime: [
		{ name: 'StickRegime_id', type: 'int' },
		{ name: 'StickRegime_Code', type: 'int' },
		{ name: 'StickRegime_Name', type: 'string' }
	],
	StudyType: [
		{ name: 'StudyType_id', type: 'int' },
		{ name: 'StudyType_Code', type: 'int' },
		{ name: 'StudyType_Name', type: 'string' }
	],
	RelatedLinkType: [
		{ name: 'RelatedLinkType_id', type: 'int' },
		{ name: 'RelatedLinkType_Code', type: 'string' },
		{ name: 'RelatedLinkType_Name', type: 'string' }
	],
	StickType: [
		{ name: 'StickType_id', type: 'int' },
		{ name: 'StickType_Code', type: 'int' },
		{ name: 'StickType_Name', type: 'string' }
	],
	StickWorkType: [
		{ name: 'StickWorkType_id', type: 'int' },
		{ name: 'StickWorkType_Code', type: 'int' },
		{ name: 'StickWorkType_Name', type: 'string' }
	],
	SurgType: [
		{ name: 'SurgType_id', type: 'int' },
		{ name: 'SurgType_Code', type: 'int' },
		{ name: 'SurgType_Name', type: 'string' }
	],
	TimetableType: [
		{ name: 'TimetableType_id', type: 'int' },
		{ name: 'TimetableType_Code', type: 'int' },
		{ name: 'TimetableType_Name', type: 'string' }
	],
	ToothType: [
		{ name: 'ToothType_id', type: 'int' },
		{ name: 'ToothType_Code', type: 'int' },
		{ name: 'ToothType_Name', type: 'string' }
	],
	Usluga: [
		{ name: 'Usluga_id', type: 'int' },
		{ name: 'Usluga_pid', type: 'int' },
		{ name: 'UslugaType_id', type: 'int' },
		{ name: 'Usluga_begDT', type: 'date', dateFormat: 'd.m.Y' },
		{ name: 'Usluga_endDT', type: 'date', dateFormat: 'd.m.Y' },
		{ name: 'Usluga_Code', type: 'string' },
		{ name: 'Usluga_Name', type: 'string' },
		{ name: 'UslugaCategory_Code', type: 'string' }
	],
	UslugaClass: [
		{ name: 'UslugaClass_id', type: 'int' },
		{ name: 'UslugaClass_Code', type: 'int' },
		{ name: 'UslugaClass_Name', type: 'string' }
	],
	UslugaPlace: [
		{ name: 'UslugaPlace_id', type: 'int' },
		{ name: 'UslugaPlace_Code', type: 'int' },
		{ name: 'UslugaPlace_Name', type: 'string' }
	],
	UslugaType: [
		{ name: 'UslugaType_id', type: 'int' },
		{ name: 'UslugaType_Code', type: 'int' },
		{ name: 'UslugaType_Name', type: 'string' }
	],
	VizitClass: [
		{ name: 'VizitClass_id', type: 'int' },
		{ name: 'VizitClass_Code', type: 'int' },
		{ name: 'VizitClass_Name', type: 'string' }
	],
	VizitType: [
		{ name: 'VizitType_id', type: 'int' },
		{ name: 'VizitType_Code', type: 'int' },
		{ name: 'VizitType_SysNick', type: 'string' },
		{ name: 'VizitType_Name', type: 'string' }
	],
	WeightAbnormType: [
		{ name: 'WeightAbnormType_id', type: 'int' },
		{ name: 'WeightAbnormType_Code', type: 'int' },
		{ name: 'WeightAbnormType_Name', type: 'string' }
	],
	WeightMeasureType: [
		{ name: 'WeightMeasureType_id', type: 'int' },
		{ name: 'WeightMeasureType_Code', type: 'int' },
		{ name: 'WeightMeasureType_Name', type: 'string' }
	],
	WhsDocumentCostItemType: [
		{ name: 'WhsDocumentCostItemType_id', type: 'int' },
		{ name: 'WhsDocumentCostItemType_Code', type: 'int' },
		{ name: 'WhsDocumentCostItemType_Name', type: 'string' },
		{ name: 'WhsDocumentCostItemType_begDate', type: 'date', dateFormat: 'd.m.Y' },
		{ name: 'WhsDocumentCostItemType_endDate', type: 'date', dateFormat: 'd.m.Y' },
		{ name: 'DrugFinance_id', type: 'int' },
		{ name: 'MorbusType_id', type: 'int' }
	],
	WhsDocumentStatusType: [
		{ name: 'WhsDocumentStatusType_id', type: 'int' },
		{ name: 'WhsDocumentStatusType_Code', type: 'int' },
		{ name: 'WhsDocumentStatusType_Name', type: 'string' }
	],
	WhsDocumentSupplyType: [
		{ name: 'WhsDocumentSupplyType_id', type: 'int' },
		{ name: 'WhsDocumentSupplyType_Code', type: 'int' },
		{ name: 'WhsDocumentSupplyType_Name', type: 'string' }
	],
	WhsDocumentTitleType: [
		{ name: 'WhsDocumentTitleType_id', type: 'int' },
		{ name: 'WhsDocumentTitleType_Code', type: 'int' },
		{ name: 'WhsDocumentTitleType_Name', type: 'string' }
	],
	WhsDocumentType: [
		{ name: 'WhsDocumentType_id', type: 'int' },
		{ name: 'WhsDocumentType_Code', type: 'int' },
		{ name: 'WhsDocumentType_Name', type: 'string' }
	],
	YesNo: [
		{ name: 'YesNo_id', type: 'int' },
		{ name: 'YesNo_Code', type: 'int' },
		{ name: 'YesNo_Name', type: 'string' }
	],
	TariffClass: [
		{ name: 'TariffClass_id', type: 'int' },
		{ name: 'TariffClass_Code', type: 'int' },
		{ name: 'TariffClass_Name', type: 'string' },
		{ name: 'TariffClass_SysNick', type: 'string' }
	],
	TariffMesType: [
		{ name: 'TariffMesType_id', type: 'int' },
		{ name: 'TariffMesType_Name', type: 'string' }
	],
	LpuBuildingType: [
		{ name: 'LpuBuildingType_id', type: 'int' },
		{ name: 'LpuBuildingType_Name', type: 'string' }
	],
	LpuSectionHospType: [
		{ name: 'LpuSectionHospType_id', type: 'int' },
		{ name: 'LpuSectionHospType_Name', type: 'string' }
	],	
	LpuSectionPlanType: [
		{ name: 'LpuSectionPlanType_id', type: 'int' },
		{ name: 'LpuSectionPlanType_Name', type: 'string' }
	],
	DrugRequestKind: [
		{ name: 'DrugRequestKind_id', type: 'int' },
		{ name: 'DrugRequestKind_Code', type: 'int' },
		{ name: 'DrugRequestKind_Name', type: 'string' }
	],
	DrugRequestStatus: [
		{ name: 'DrugRequestStatus_id', type: 'int' },
		{ name: 'DrugRequestStatus_Code', type: 'int' },
		{ name: 'DrugRequestStatus_Name', type: 'string' }
	],
	DrugRequestPeriod: [
		{ name: 'DrugRequestPeriod_id', type: 'int' },
		{ name: 'DrugRequestPeriod_Name', type: 'string' },
		{ name: 'DrugRequestPeriod_begDate', type: 'string' },
		{ name: 'DrugRequestPeriod_endDate', type: 'string' }
	],
	DrugRequestType: [
		{ name: 'DrugRequestType_id', type: 'int' },
		{ name: 'DrugRequestType_Code', type: 'int' },
		{ name: 'DrugRequestType_Name', type: 'string' }
	],
	ReceptResult: [
		{ name: 'ReceptResult_id', type: 'int' },
		{ name: 'ReceptResult_Code', type: 'int' },
		{ name: 'ReceptResult_Name', type: 'string' }
	],
	ReceptMismatch: [
		{ name: 'ReceptMismatch_id', type: 'int' },
		{ name: 'ReceptMismatch_Code', type: 'int' },
		{ name: 'ReceptMismatch_Name', type: 'string' }
	],
	ContragentType: [
		{ name: 'ContragentType_id', type: 'int' },
		{ name: 'ContragentType_Code', type: 'int' },
		{ name: 'ContragentType_Name', type: 'string' }
	],
	DrugNds: [
		{ name: 'DrugNds_id', type: 'int' },
		{ name: 'DrugNds_Code', type: 'int' },
		{ name: 'DrugNds_Name', type: 'string' }
	],
	PrivilegeTypeWow: [
		{ name: 'PrivilegeTypeWow_id', type: 'int' },
		{ name: 'PrivilegeTypeWow_Name', type: 'string' }
	],
	DispWowSpec: [
		{ name: 'DispWowSpec_id', type: 'int' },
		{ name: 'DispWowSpec_Code', type: 'int' },
		{ name: 'DispWowSpec_Name', type: 'string' }
	],
	DispWowUslugaType: [
		{ name: 'DispWowUslugaType_id', type: 'int' },
		{ name: 'DispWowUslugaType_Code', type: 'int' },
		{ name: 'DispWowUslugaType_Name', type: 'string' }
	],
	ExaminationPlace: [
		{ name: 'ExaminationPlace_id', type: 'int' },
		{ name: 'ExaminationPlace_Code', type: 'int' },
		{ name: 'ExaminationPlace_Name', type: 'string' }
	],
    FinanceSource: [
        { name: 'FinanceSource_id', type: 'int' },
        { name: 'FinanceSource_Name', type: 'string' },
        { name: 'FinanceSource_Code', type: 'string' },
        { name: 'DrugFinance_id', type: 'int' },
        { name: 'WhsDocumentCostItemType_id', type: 'int' },
        { name: 'BudgetFormType_id', type: 'int' }
    ],
	
	// Справочник "Условие проведения лечения"
	TreatmentConditionsType: getCommonSprDescr('TreatmentConditionsType'),
	
	TreatmentMultiplicity: [
		{ name: 'TreatmentMultiplicity_id', type: 'int' },
		{ name: 'TreatmentMultiplicity_Code', type: 'int' },
		{ name: 'TreatmentMultiplicity_Name', type: 'string' }
	],
	TreatmentReview: [
		{ name: 'TreatmentReview_id', type: 'int' },
		{ name: 'TreatmentReview_Code', type: 'int' },
		{ name: 'TreatmentReview_Name', type: 'string' }
	],
	TreatmentSubjectType: [
		{ name: 'TreatmentSubjectType_id', type: 'int' },
		{ name: 'TreatmentSubjectType_Code', type: 'int' },
		{ name: 'TreatmentSubjectType_Name', type: 'string' }
	],
	TreatmentType: [
		{ name: 'TreatmentType_id', type: 'int' },
		{ name: 'TreatmentType_Code', type: 'int' },
		{ name: 'TreatmentType_Name', type: 'string' }
	],
	TreatmentUrgency: [
		{ name: 'TreatmentUrgency_id', type: 'int' },
		{ name: 'TreatmentUrgency_Code', type: 'int' },
		{ name: 'TreatmentUrgency_Name', type: 'string' }
	],
	TreatmentSenderType: [
		{ name: 'TreatmentSenderType_id', type: 'int' },
		{ name: 'TreatmentSenderType_Code', type: 'int' },
		{ name: 'TreatmentSenderType_Name', type: 'string' }
	]/*,
	DrugExtraLevel: [
		{ name: 'DrugExtraLevel_id', type: 'int' },
		{ name: 'DrugExtraLevel_Code', type: 'int' },
		{ name: 'DrugExtraLevel_Name', type: 'string' }
	]*/,
	
	ReceptUploadType: getCommonSprDescr('ReceptUploadType'),
	ReceptUploadDeliveryType: getCommonSprDescr('ReceptUploadDeliveryType'),
	ReceptUploadStatus: getCommonSprDescr('ReceptUploadStatus'),
	
	RefCategory: [
		{ name: 'RefCategory_id', type: 'int' },
		{ name: 'RefCategory_Code', type: 'int' },
		{ name: 'RefCategory_Name', type: 'string' }
	],
	RefValuesType: [
		{ name: 'RefValuesType_id', type: 'int' },
		{ name: 'RefValuesType_Code', type: 'int' },
		{ name: 'RefValuesType_Name', type: 'string' }
	],
	RefValuesUnit: [
		{ name: 'RefValuesUnit_id', type: 'int' },
		{ name: 'RefValuesUnit_Code', type: 'int' },
		{ name: 'RefValuesUnit_Name', type: 'string' }
	],
	RefValuesGroup: [
		{ name: 'RefValuesGroup_id', type: 'int' },
		{ name: 'RefValuesGroup_Code', type: 'int' },
		{ name: 'RefValuesGroup_Name', type: 'string' }
	],
	AgeUnit: [
		{ name: 'AgeUnit_id', type: 'int' },
		{ name: 'AgeUnit_Code', type: 'int' },
		{ name: 'AgeUnit_Name', type: 'string' }
	],
	HormonalPhaseType: [
		{ name: 'HormonalPhaseType_id', type: 'int' },
		{ name: 'HormonalPhaseType_Code', type: 'int' },
		{ name: 'HormonalPhaseType_Name', type: 'string' }
	],
	TimeOfDay: [
		{ name: 'TimeOfDay_id', type: 'int' },
		{ name: 'TimeOfDay_Code', type: 'int' },
		{ name: 'TimeOfDay_Name', type: 'string' }
	],
	RefMaterial: [
		{ name: 'RefMaterial_id', type: 'int' },
		{ name: 'RefMaterial_Code', type: 'int' },
		{ name: 'RefMaterial_Name', type: 'string' }
	],
	XmlTemplateSeparator: [
		{ name: 'XmlTemplateSeparator_id', type: 'int' },
		{ name: 'XmlTemplateSeparator_Code', type: 'string' },
		{ name: 'XmlTemplateSeparator_Name', type: 'string' }
	],
	RegistryStacType: [
		{ name: 'RegistryStacType_id', type: 'int' },
		{ name: 'RegistryStacType_Code', type: 'string' },
		{ name: 'RegistryStacType_Name', type: 'string' }
	],
	RegistryEventType: [
		{ name: 'RegistryEventType_id', type: 'int' },
		{ name: 'RegistryEventType_Code', type: 'string' },
		{ name: 'RegistryEventType_Name', type: 'string' }
	],
	rls_Countries: [
		{name: 'RlsCountries_id', type:'int'},
		{name: 'RlsCountries_Name', type:'string'}
	],
	rls_Firms: [
		{name: 'RlsFirms_id', type:'int'},
		{name: 'RlsFirms_Name', type:'string'}
	],
	rls_Actmatters: [
		{name: 'RlsActmatters_id', type:'int'},
		{name: 'RlsActmatters_RusName', type:'string'}
	],
	rls_Desctextes: [
		{name: 'RlsDesctextes_id', type:'int'},
		{name: 'RlsDesctextes_Code', type:'string'}
	],
	rls_Clspharmagroup: [
		{name: 'RlsPharmagroup_id', type:'int'},
		{name: 'RlsPharmagroup_Name', type:'string'}
	],
	rls_Clsiic: [
		{name: 'RlsClsiic_id', type:'int'},
		{name: 'RlsClsiic_Name', type:'string'}
	],
	rls_Clsatc: [
		{name: 'RlsClsatc_id', type:'int'},
		{name: 'RlsClsatc_Name', type:'string'}
	],
	rls_Clsdrugforms: [
		{name: 'RlsClsdrugforms_id', type:'int'},
		{name: 'RlsClsdrugforms_Name', type:'string'}
	],
	rls_Tradenames: [
		{name: 'Tradenames_id', type:'int'},
		{name: 'RlsTradenames_id', type:'int'},
		{name: 'RlsSynonim_id', type:'int'},
		{name: 'RlsTorg_Name', type:'string'}
	],
	ExpertiseNameType: [
		{name: 'ExpertiseNameType_id', type:'int'},
		{name: 'ExpertiseNameType_Name', type:'string'},
		{name: 'ExpertiseNameType_SysNick', type:'string'}
	],
	ExpertiseEventType: [
		{name: 'ExpertiseEventType_id', type:'int'},
		{name: 'ExpertiseEventType_Code', type:'string'},
		{name: 'ExpertiseEventType_Name', type:'string'}
	],
	PatientStatusType: [
		{name: 'PatientStatusType_id', type:'int'},
		{name: 'PatientStatusType_Name', type:'string'},
		{name: 'PatientStatusType_SysNick', type:'string'}
	],
	CauseTreatmentType: [
		{name: 'CauseTreatmentType_id', type:'int'},
		{name: 'CauseTreatmentType_Name', type:'string'}
	],
	ExpertiseNameSubjectType: [
		{name: 'ExpertiseNameSubjectType_id', type:'int'},
		{name: 'ExpertiseNameSubjectType_Name', type:'string'}
	],
	ExpertMedStaffType: [
		{name: 'ExpertMedStaffType_id', type:'int'},
		{name: 'ExpertMedStaffType_Name', type:'string'}
	],
	RecipientType: [ // схема msg
		{name: 'RecipientType_id', type:'int'},
		{name: 'RecipientType_Name', type:'string'}
	],
	NoticeType: [ // схема msg
		{name: 'NoticeType_id', type:'int'},
		{name: 'NoticeType_Name', type:'string'}
	],
	VidDeat: [
		{name: 'VidDeat_id', type:'int'},
		{name: 'VidDeat_Code', type:'string'},
		{name: 'VidDeat_Name', type:'string'}
	],
	BuildingType: [ // Тип постройки
		{name: 'BuildingType_id', type:'int'},
		{name: 'BuildingType_Code', type:'string'},
		{name: 'BuildingType_Name', type:'string'}
	],
	BuildingAppointmentType: [ // Назначение здания
		{name: 'BuildingAppointmentType_id', type:'int'},
		{name: 'BuildingAppointmentType_Code', type:'string'},
		{name: 'BuildingAppointmentType_Name', type:'string'}
	],
	BuildingHoldConstrType: [ // Несущие конструкции
		{name: 'BuildingHoldConstrType_id', type:'int'},
		{name: 'BuildingHoldConstrType_Code', type:'string'},
		{name: 'BuildingHoldConstrType_Name', type:'string'}
	],
	BuildingOverlapType: [ // Тип перекрытий
		{name: 'BuildingOverlapType_id', type:'int'},
		{name: 'BuildingOverlapType_Code', type:'string'},
		{name: 'BuildingOverlapType_Name', type:'string'}
	],
	LpuEquipmentType: [ // Тип оборудования
		{name: 'LpuEquipmentType_id', type:'int'},
		{name: 'LpuEquipmentType_Code', type:'string'},
		{name: 'LpuEquipmentType_Name', type:'string'}
	],
	PropertyType: [ // Отношение к собственности
		{name: 'PropertyType_id', type:'int'},
		{name: 'PropertyType_Code', type:'string'},
		{name: 'PropertyType_Name', type:'string'}
	],
    
    //ЛИС
	lis_Category: [
		{name: 'Category_id', type:'int'},
		{name: 'Category_Code', type:'string'},
		{name: 'Category_Name', type:'string'}
	],
	lis_CustomStates: [
		{name: 'CustomStates_id', type:'int'},
		{name: 'CustomStates_Code', type:'string'},
		{name: 'CustomStates_Name', type:'string'}
	],
	lis_DefectState: [
		{name: 'DefectState_id', type:'int'},
		{name: 'DefectState_Code', type:'string'},
		{name: 'DefectState_Name', type:'string'}
	],
	lis_Hospital: [
		{name: 'Hospital_id', type:'int'},
		{name: 'Hospital_Code', type:'string'},
		{name: 'Hospital_Name', type:'string'}
	],
	lis_HospitalDept: [
		{name: 'HospitalDept_id', type:'int'},
		{name: 'HospitalDept_Code', type:'string'},
		{name: 'HospitalDept_Name', type:'string'}
	],
	lis_PayCategory: [
		{name: 'PayCategory_id', type:'int'},
		{name: 'PayCategory_Code', type:'string'},
		{name: 'PayCategory_Name', type:'string'}
	],
	lis_Priority: [
		{name: 'Priority_id', type:'int'},
		{name: 'Priority_Code', type:'string'},
		{name: 'Priority_Name', type:'string'}
	],
	lis_RequestForm: [
		{name: 'RequestForm_id', type:'int'},
		{name: 'RequestForm_Code', type:'string'},
		{name: 'RequestForm_Name', type:'string'}
	],
	lis_Sex: [
		{name: 'Sex_id', type:'int'},
		{name: 'Sex_Code', type:'string'},
		{name: 'Sex_Name', type:'string'}
	],
	lis_States: [
		{name: 'States_id', type:'int'},
		{name: 'States_Code', type:'string'},
		{name: 'States_Name', type:'string'}
	],
	lis_Target: [
		{name: 'Target_id', type:'int'},
		{name: 'Target_Code', type:'string'},
		{name: 'Target_Name', type:'string'}
	],
	lis_Biomaterial: [
		{name: 'Biomaterial_id', type:'int'},
		{name: 'Biomaterial_Code', type:'string'},
		{name: 'Biomaterial_Name', type:'string'}
	]
	,
	lis_CyclePeriod: [
		{name: 'CyclePeriod_id', type:'int'},
		{name: 'CyclePeriod_Code', type:'string'},
		{name: 'CyclePeriod_Name', type:'string'}
	],

    //end ЛИС
	//Аутопсия: AutopsyPerformType
	AutopsyPerformType: getCommonSprDescr('AutopsyPerformType'),
	//Первично-множественная опухоль: TumorPrimaryMultipleType
	TumorPrimaryMultipleType: getCommonSprDescr('TumorPrimaryMultipleType'),
	//Взят на учет в ОД: OnkoRegType
	OnkoRegType: getCommonSprDescr('OnkoRegType'),
	//Причина снятия с учета: OnkoRegOutType
	OnkoRegOutType: getCommonSprDescr('OnkoRegOutType'),
	//Сторона поражения: OnkoLesionSide
	OnkoLesionSide: getCommonSprDescr('OnkoLesionSide'),
	//T: OnkoT
	OnkoT: getCommonSprDescr('OnkoT'),
	//N: OnkoN
	OnkoN: getCommonSprDescr('OnkoN'),
	//M: OnkoM
	OnkoM: getCommonSprDescr('OnkoM'),
	//Стадия опухолевого процесса: TumorStage
	TumorStage: getCommonSprDescr('TumorStage'),
	//Локализация отдаленных метастазов: OnkoMetastasesLocalType
	OnkoMetastasesLocalType: getCommonSprDescr('OnkoMetastasesLocalType'),
	//Метод подтверждения диагноза: OnkoDiagConfirmMethodType
	OnkoDiagConfirmMethodType: getCommonSprDescr('OnkoDiagConfirmMethodType'),
	//Обстоятельства выявления опухоли: TumorCircumIdentType
	TumorCircumIdentType: getCommonSprDescr('TumorCircumIdentType'),
	//Причины поздней диагностики: OnkoLateDiagCause
	OnkoLateDiagCause: getCommonSprDescr('OnkoLateDiagCause'),
	//Результат аутопсии применительно к данной опухоли: TumorAutopsyResultType
	TumorAutopsyResultType: getCommonSprDescr('TumorAutopsyResultType'),
	//Проведенное лечение первичной опухоли: TumorPrimaryTreatType
	TumorPrimaryTreatType: getCommonSprDescr('TumorPrimaryTreatType'),
	//Причины незавершенности радикального лечения: TumorRadicalTreatIncomplType
	TumorRadicalTreatIncomplType: getCommonSprDescr('TumorRadicalTreatIncomplType'),
	//Характер заболевания: SicknessKind
	SicknessKind: getCommonSprDescr('SicknessKind'),
	// Этническая группа: Ethnos
	Ethnos: getCommonSprDescr('Ethnos'),
	// Социально-профессиональная группа: OnkoOccupationClass
	OnkoOccupationClass: getCommonSprDescr('OnkoOccupationClass'),
	// Инвалидность по основному (онкологическому) заболеванию: OnkoInvalidType
	OnkoInvalidType: getCommonSprDescr('OnkoInvalidType'),
    // Общее состояние пациента: OnkoPersonStateType
    OnkoPersonStateType: getCommonSprDescr('OnkoPersonStateType'),
    // Состояние опухолевого процесса (мониторинг опухоли): OnkoTumorStatusType
    OnkoTumorStatusType: getCommonSprDescr('OnkoTumorStatusType'),
    // Состояние на конец отчетного года: OnkoStatusYearEndType
    OnkoStatusYearEndType: getCommonSprDescr('OnkoStatusYearEndType'),
    // Госпитализация: OnkoHospType
    OnkoHospType: getCommonSprDescr('OnkoHospType'),
    // Цель госпитализации: OnkoPurposeHospType
    OnkoPurposeHospType: getCommonSprDescr('OnkoPurposeHospType'),
    // Состояние при выписке: OnkoLeaveType
    OnkoLeaveType: getCommonSprDescr('OnkoLeaveType'),
    // Метод подтверждения диагноза: OnkoDiagConfType
    OnkoDiagConfType: getCommonSprDescr('OnkoDiagConfType'),
    // Выявлен врачом: OnkoPostType
    OnkoPostType: getCommonSprDescr('OnkoPostType'),
    // Сочетание видов лечения: OnkoCombiTreatType
    OnkoCombiTreatType: getCommonSprDescr('OnkoCombiTreatType'),
    // Позднее осложнение специального лечения: OnkoLateComplTreatType
    OnkoLateComplTreatType: getCommonSprDescr('OnkoLateComplTreatType'),
    // Вид планирования: OnkoPlanType
    OnkoPlanType: getCommonSprDescr('OnkoPlanType'),
    // Единиц: OnkoDrugUnitType
    OnkoDrugUnitType: getCommonSprDescr('OnkoDrugUnitType'),
    // Характер хирургического лечения: OnkoSurgTreatType
    OnkoSurgTreatType: getCommonSprDescr('OnkoSurgTreatType'),
	
	// Способ облучения при проведении лучевой терапии: OnkoUslugaBeamIrradiationType
	OnkoUslugaBeamIrradiationType: getCommonSprDescr('OnkoUslugaBeamIrradiationType'),
	// Вид лучевой терапии: OnkoUslugaBeamKindType
	OnkoUslugaBeamKindType: getCommonSprDescr('OnkoUslugaBeamKindType'),
	// Метод лучевой терапии: OnkoUslugaBeamMethodType
	OnkoUslugaBeamMethodType: getCommonSprDescr('OnkoUslugaBeamMethodType'),
	// Радиомодификаторы: OnkoUslugaBeamRadioModifType
	OnkoUslugaBeamRadioModifType: getCommonSprDescr('OnkoUslugaBeamRadioModifType'),
	// Преимущественная направленность лучевой терапии: OnkoUslugaBeamFocusType
	OnkoUslugaBeamFocusType: getCommonSprDescr('OnkoUslugaBeamFocusType'),
	// Грей / ТДФ (ВДФ): OnkoUslugaBeamUnitType
	OnkoUslugaBeamUnitType: getCommonSprDescr('OnkoUslugaBeamUnitType'),
	
	// Вид проведенного химиотерапевтического лечения: OnkoUslugaChemKindType
	OnkoUslugaChemKindType: getCommonSprDescr('OnkoUslugaChemKindType'),
	// Преимущественная направленность химиотерапии: OnkoUslugaChemFocusType
	OnkoUslugaChemFocusType: getCommonSprDescr('OnkoUslugaChemFocusType'),

	// Препарат: OnkoDrug
	OnkoDrug: [
		{ name: 'OnkoDrug_id', type: 'int' },
		{ name: 'OnkoDrug_pid', type: 'int' },
		{ name: 'OnkoDrugType_id', type: 'int' },
		{ name: 'OnkoDrug_Code', type: 'string' },
		{ name: 'OnkoDrug_Name', type: 'string' }
	],
	
	// МОРФОЛОГИЧЕСКАЯ КЛАССИФИКАЦИЯ НОВООБРАЗОВАНИЙ (МКБ-0): OnkoDiag
	OnkoDiag: [
		{ name: 'OnkoDiag_id', type: 'int' },
		{ name: 'OnkoDiag_pid', type: 'int' },
		{ name: 'OnkoDiag_Code', type: 'string' },
		{ name: 'OnkoDiag_Name', type: 'string' }
	],
	
	// Преимущественная направленность гормоноиммунотерапии: OnkoUslugaGormunFocusType
	OnkoUslugaGormunFocusType: getCommonSprDescr('OnkoUslugaGormunFocusType'),
	// Вид проведенной гормоноиммунотерапии: OnkoGormunType
	OnkoGormunType: getCommonSprDescr('OnkoGormunType'),

	//Исход заболевания: SicknessResult
	//SicknessResult: getCommonSprDescr('SicknessResult'),
	//Тип заболевания: SicknessType
	//SicknessType: getCommonSprDescr('SicknessType'),
	MorbusResult: getCommonSprDescr('MorbusResult'),
	
	CrazyHospType: getCommonSprDescr('CrazyHospType'),
	CrazySupplyType: getCommonSprDescr('CrazySupplyType'),
	CrazyDirectType: getCommonSprDescr('CrazyDirectType'),
	CrazySupplyOrderType: getCommonSprDescr('CrazySupplyOrderType'),
	CrazyJudgeDecisionArt35Type: getCommonSprDescr('CrazyJudgeDecisionArt35Type'),
	CrazyDirectFromType: getCommonSprDescr('CrazyDirectFromType'),	
	CrazyPurposeDirectType: getCommonSprDescr('CrazyPurposeDirectType'),
	CrazyLeaveInvalidType: getCommonSprDescr('CrazyLeaveInvalidType'),
	CrazySurveyHIVType: getCommonSprDescr('CrazySurveyHIVType'),
	CrazyLeaveType: getCommonSprDescr('CrazyLeaveType'),
	CrazyDeathCauseType: getCommonSprDescr('CrazyDeathCauseType'),
	CrazyForceTreatType: getCommonSprDescr('CrazyForceTreatType'),
	CrazyForceTreatResultType: getCommonSprDescr('CrazyForceTreatResultType'),
	CrazyAmbulMonitoringType: getCommonSprDescr('CrazyAmbulMonitoringType'),
	CrazyEducationType: getCommonSprDescr('CrazyEducationType'),
	CrazySourceLivelihoodType: getCommonSprDescr('CrazySourceLivelihoodType'),
	CrazyResideType: getCommonSprDescr('CrazyResideType'),
	CrazyResideConditionsType: getCommonSprDescr('CrazyResideConditionsType'),
	CrazyResultDeseaseType: getCommonSprDescr('CrazyResultDeseaseType'),
	CrazyDrugType: getCommonSprDescr('CrazyDrugType'),
	CrazyDrugReceptType: getCommonSprDescr('CrazyDrugReceptType'),
	CrazyDrugVolumeType: getCommonSprDescr('CrazyDrugVolumeType'),
	CrazyForceTreatJudgeDecisionType: getCommonSprDescr('CrazyForceTreatJudgeDecisionType'),
	
	TubResultChemClass: getCommonSprDescr('TubResultChemClass'),
	TubResultChemType: getCommonSprDescr('TubResultChemType'),
	TubSickGroupType: getCommonSprDescr('TubSickGroupType'),
	
	TubResultDeathType: getCommonSprDescr('TubResultDeathType'),
	TubDiag: getCommonSprDescr('TubDiag'),
	TubDiagNotify: getCommonSprDescr('TubDiagNotify'),
	VenerDetectType: getCommonSprDescr('VenerDetectType'),
	VenerDeRegCauseType: getCommonSprDescr('VenerDeRegCauseType'),
	PersonCategoryType: getCommonSprDescr('PersonCategoryType'),
	TubFluorSurveyPeriodType: getCommonSprDescr('TubFluorSurveyPeriodType'),
	TubDetectionPlaceType: getCommonSprDescr('TubDetectionPlaceType'),
	TubDetectionFactType: getCommonSprDescr('TubDetectionFactType'),
	TubSurveyGroupType: getCommonSprDescr('TubSurveyGroupType'),
	TubDetectionMethodType: getCommonSprDescr('TubDetectionMethodType'),
	TubMethodConfirmBactType: getCommonSprDescr('TubMethodConfirmBactType'),
	TubDiagSop: getCommonSprDescr('TubDiagSop'),
	TubRegCrazyType: getCommonSprDescr('TubRegCrazyType'),
	TubStudyType: getCommonSprDescr('TubStudyType'),
	TubTreatmentChemType: getCommonSprDescr('TubTreatmentChemType'),
	TubStandartConditChemType: getCommonSprDescr('TubStandartConditChemType'),
	TubStageChemType: getCommonSprDescr('TubStageChemType'),
	TubDrug: getCommonSprDescr('TubDrug'),
	TubMicrosResultType: getCommonSprDescr('TubMicrosResultType'),
	TubSeedResultType: getCommonSprDescr('TubSeedResultType'),
	TubXrayResultType: getCommonSprDescr('TubXrayResultType'),
	TubDiagnosticMaterialType: getCommonSprDescr('TubDiagnosticMaterialType'),
	TubTargetStudyType: getCommonSprDescr('TubTargetStudyType'),
	TubMicrosResultType: getCommonSprDescr('TubMicrosResultType'),
	TubVenueType: getCommonSprDescr('TubVenueType'),
	TubBreakChemType: getCommonSprDescr('TubBreakChemType'),
	TubAdviceResultType: getCommonSprDescr('TubAdviceResultType'),
	TubHistolResultType: getCommonSprDescr('TubHistolResultType'),
	
	VenerPathTransType: getCommonSprDescr('VenerPathTransType'),
	VenerPregPeriodType: getCommonSprDescr('VenerPregPeriodType'),
	VenerLabConfirmType: getCommonSprDescr('VenerLabConfirmType'),
	VenerDetectionPlaceType: getCommonSprDescr('VenerDetectionPlaceType'),
	VenerDetectionFactType: getCommonSprDescr('VenerDetectionFactType'),
	
	PersonCategoryType: getCommonSprDescr('PersonCategoryType'),

	DurationType: [
		{name: 'DurationType_id', type: 'int'},
		{name: 'DurationType_Code', type: 'int'},
		{name: 'DurationType_Nick', type: 'string'},
		{name: 'DurationType_SysNick', type: 'string'},
		{name: 'DurationType_Name', type: 'string'}
	],
	PerformanceType: getCommonSprDescr('PerformanceType'),
	XmlType: getCommonSprDescr('XmlType'),
	XmlTemplateScope: getCommonSprDescr('XmlTemplateScope'),
	
	LabSampleDefectiveType: getCommonSprDescr('LabSampleDefectiveType'),
	RefSample: [
		{name: 'RefSample_id', type:'int'},
		{name: 'RefMaterial_id', type:'int'},
		{name: 'RefSample_Name', type:'string'}
	],
	UslugaExecutionType: getCommonSprDescr('UslugaExecutionType'),
	UslugaExecutionReason: getCommonSprDescr('UslugaExecutionReason'),

	HepatitisEpidemicMedHistoryType: getCommonSprDescr('HepatitisEpidemicMedHistoryType'), // Эпиданамнез 
	HepatitisLabConfirmType: getCommonSprDescr('HepatitisLabConfirmType'),	// Лабораторные подтверждения. Тип 
	HepatitisFuncConfirmType: getCommonSprDescr('HepatitisFuncConfirmType'),	// Инструментальные подтверждения. Тип
	HepatitisDiagType: getCommonSprDescr('HepatitisDiagType'),	// Диагноз. Диагноз
	HepatitisDiagActiveType: getCommonSprDescr('HepatitisDiagActiveType'),	// Диагноз. Активность 
	HepatitisFibrosisType: getCommonSprDescr('HepatitisFibrosisType'),	// Диагноз. Фиброз  
	HepatitisResultClass: getCommonSprDescr('HepatitisResultClass'),	// Лечение. Результат
	HepatitisSideEffectType: getCommonSprDescr('HepatitisSideEffectType'),	// Лечение. Побочный эффект
	HepatitisCurePeriodType: getCommonSprDescr('HepatitisCurePeriodType'),	// Мониторинг эффективности лечения. Срок лечения
	HepatitisQualAnalysisType: getCommonSprDescr('HepatitisQualAnalysisType'),	// Мониторинг эффективности лечения. Качественный анализ
	HepatitisQueueType: getCommonSprDescr('HepatitisQueueType'),	// Тип очереди
	
	// регистры
	PersonRegisterOutCause: getCommonSprDescr('PersonRegisterOutCause'), // Причина исключения
	PersonRegisterFailIncludeCause: [ // Причина не включения
		{name: 'PersonRegisterFailIncludeCause_Name', type: 'string'},
		{name: 'PersonRegisterFailIncludeCause_SysNick', type: 'string'},
		{name: 'PersonRegisterFailIncludeCause_Code', type: 'int'},
		{name: 'PersonRegisterFailIncludeCause_id', type: 'int'}
	],
	
	// Анализаторы
	lis_AnalyzerClass: getCommonSprDescr('AnalyzerClass'),	// Тип взаимодействия анализатора
	lis_AnalyzerInteractionType: getCommonSprDescr('AnalyzerInteractionType'),	// Тип взаимодействия анализатора
	lis_AnalyzerTestType: getCommonSprDescr('AnalyzerTestType'),	// Тип теста
	lis_AnalyzerTestUslugaComplex: [ // Связь тестов с услугами
		{name: 'AnalyzerTestUslugaComplex_id', type:'int'},
		{name: 'AnalyzerTest_id', type:'int'},
		{name: 'UslugaComplex_id', type:'int'},
		{name: 'AnalyzerTestUslugaComplex_Deleted', type:'int'}
	],
	lis_QualitativeTestAnswer: getCommonSprDescr('QualitativeTestAnswer'), // Варианты ответа для качественных тестов
	lis_Unit: getCommonSprDescr('Unit'), // Единицы измерения
	LpuOrgServiceType: getCommonSprDescr('LpuOrgServiceType'),
	UslugaComplexAttributeType: [
		{ name: 'UslugaComplexAttributeType_id', type: 'int' },
		{ name: 'UslugaComplexAttributeType_Code', type: 'int' },
		{ name: 'AttributeValueType_id', type: 'int' },
		{ name: 'UslugaComplexAttributeType_Name', type: 'string' }
	],
	UslugaComplexTariffType: getCommonSprDescr('UslugaComplexTariffType'),
	AttributeValueType: getCommonSprDescr('AttributeValueType'),
	UslugaCategory: [
		{ name: 'UslugaCategory_id', type: 'int' },
		{ name: 'UslugaCategory_Code', type: 'int' },
		{ name: 'UslugaCategory_Name', type: 'string' },
		{ name: 'UslugaCategory_SysNick', type: 'string' }
	],
	AnalyzerWorksheetStatusType: getCommonSprDescr('AnalyzerWorksheetStatusType'),
	Biomaterial: getCommonSprDescr('Biomaterial'),
	OrgRSchetType: getCommonSprDescr('OrgRSchetType'),
	RegistryReceptType: getCommonSprDescr('RegistryReceptType'),
	Okv: [
		{ name: 'Okv_id', type: 'int' },
		{ name: 'Okv_Code', type: 'int' },
		{ name: 'Okv_Nick', type: 'string' },
		{ name: 'Okv_Name', type: 'string' },
		{ name: 'KLCountry_id', type: 'int' }
	],
	lis_AnalyzerWorksheetInteractionType: getCommonSprDescr('AnalyzerWorksheetInteractionType'),
	ResultDeseaseType: getCommonSprDescr('ResultDeseaseType'),
	PersonDoublesStatus: getCommonSprDescr('PersonDoublesStatus'),
	WaybillGas: [
		{ name: 'WaybillGas_id', type: 'int' },
		{ name: 'WaybillGas_Code', type: 'string' },
		{ name: 'WaybillGas_Name', type: 'string' }
	]


};

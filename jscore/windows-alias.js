/**
* windows.js - Алиасы окон
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2015 Swan Ltd.
* @author       SWAN developers
*/


WINDOWS_ALIAS = {
	// АРМ заведующего оперблоком
	'swWorkPlaceOperBlockWindow' : 'common.OperBlockWP.swWorkPlaceOperBlockWindow',
	'swEvnPrescrOperBlockPlanWindow' : 'common.OperBlockWP.swEvnPrescrOperBlockPlanWindow',
	// ЭМК
	'swPersonEmkWindowExt6' : 'common.swPersonEmkWindow',
	'swPersonSearchWindowExt6' : 'common.swPersonSearchWindow',
	// АРМ приемного отделения
	'swSelectLpuSectionWardExt6' : 'common.SelectLpuSectionWard',
	// Доп.формы ЭМК
	'swEvnDirectionEditWindowExt6' : 'common.EMK.EvnDirectionEditWindow',
	'swPrivilegeEditWindowExt6' : 'common.EMK.PrivilegeEditWindow',
    'swAnthropometricMeasuresAddWindow' : 'common.EMK.SignalInfo.AnthropometricMeasuresAddWindow',
	'swSignalInfoForDoctorForm' : 'common.EMK.SignalInfoForDoctor.SignalInfoForDoctorForm',
	'swNewslatterAcceptEditFormExt6' : 'common.EMK.NewslatterAcceptEditForm',
	'swTimeSeriesResultsWindow' : 'common.EMK.tools.swTimeSeriesResultsWindow',
	'swPersonProfileEditWindow' : 'common.EMK.PersonProfileEditWindow',
	'swMedicalFormEditWindow' : 'common.EMK.MedicalFormEditWindow',
	'swDrugTherapySchemeAddWindow' : 'common.EMK.DrugTherapySchemeAddWindow',
	'swEvnPLDispScreenOnkoWindow' : 'common.EMK.swEvnPLDispScreenOnkoWindow',
	// Назначения
	'swSpecificationDetailWnd' : 'common.EMK.tools.swSpecificationDetailWnd',
	'swEmptyTimetableWindow' : 'common.EMK.tools.swEmptyTimetableWindow',
	'swSelectEvnStatusCauseWindow' : 'common.Evn.swSelectEvnStatusCauseWindow',
	'swPacketPrescrCreateWindow' : 'common.EMK.tools.swPacketPrescrCreateWindow',
	'swPacketPrescrSelectWindow' : 'common.EMK.PacketPrescr.swPacketPrescrSelectWindow',
	'swPacketPrescrShareWindow' : 'common.EMK.PacketPrescr.swPacketPrescrShareWindow',
	'swPacketPrescrSelectWindowExt2' : 'common.EMK.PacketPrescrExt2.swPacketPrescrSelectWindowExt2',
	'swOpenWindowAllUslugaExt2' : 'common.EMK.tools.swOpenWindowAllUslugaExt2',
	'swEvnCourseProcEditWindow' : 'common.EMK.EvnCourseProcEditWindow',
	'swAutoSelectDateTimeWindow' : 'common.EMK.tools.swAutoSelectDateTimeWindow',
	'swSelectTimeSeriesUslugaWindow' : 'common.EMK.tools.swSelectTimeSeriesUslugaWindow',
	'swRegimeCreateWindow' : 'common.EMK.QuickPrescrSelect.swRegimeCreateWindow',
	'swVaccinationWindow' : 'common.EMK.Vaccination.swVaccinationWindow',
	'swVaccinesDosesWindow' : 'common.EMK.Vaccination.swVaccinesDosesWindow',
	'swVaccinesDosesCheckWindow' : 'common.EMK.Vaccination.swVaccinesDosesCheckWindow', 
	'swVaccinePersonSoglasieWindow' : 'common.EMK.Vaccination.swVaccinePersonSoglasieWindow',
	'swDietCreateWindow' : 'common.EMK.QuickPrescrSelect.swDietCreateWindow',
	'swEvnPrescrAllUslugaInputWnd' : 'common.EMK.tools.swEvnPrescrAllUslugaInputWnd',
	'swEvnPrescrTreatCreateWindow' : 'common.EMK.QuickPrescrSelect.swEvnPrescrTreatCreateWindow',
	'EvnCourseTreatInPacketWindowExt2' : 'common.EMK.PacketPrescrExt2.EvnCourseTreatInPacketWindowExt2',
	'swSelectMedServiceForCitoWnd' : 'common.EMK.tools.swSelectMedServiceForCitoWnd',
	'swPrescDirectionIncludeWindow' : 'common.EMK.tools.swPrescDirectionIncludeWindow',
	// Диспансерное наблюдение
	'swPersonDispEditWindowExt6' : 'common.EMK.PersonDispEditWindow',
	'swPersonDispSopDiagEditWindowExt6' : 'common.EMK.PersonDispSopDiagEditWindow',
	'swPersonDispVizitEditWindowExt6' : 'common.EMK.PersonDispVizitEditWindow',
	'swPersonDispTargetRateEditWindowExt6' : 'common.EMK.PersonDispTargetRateEditWindow',
	'swMorbusNephroLabWindowExt6' : 'common.EMK.MorbusNephroLabWindow',
	'swEvnNotifyNephroEditWindowExt6' : 'common.EMK.EvnNotifyNephroEditWindow',
	'swPersonDispDiagHistoryEditWindowExt6' : 'common.EMK.PersonDispDiagHistoryEditWindow',
	'swPregnancySpecComplicationEditWindowExt6' : 'common.EMK.PregnancySpecComplicationEditWindow',
	'swPregnancySpecExtragenitalDiseaseEditWindowExt6' : 'common.EMK.PregnancySpecExtragenitalDiseaseEditWindow',
	'swPregnancyGravidogamExt6' : 'common.swPregnancyGravidogam',
	'swPersonDispHistEditWindowExt6': 'common.EMK.PersonDispHistEditWindow',
	//ЛВН
	'swEvnStickEditWindowExt6' : 'common.Stick.EvnStickEditWindow',
	'swEvnStickESSConfirmEditWindowExt6' : 'common.Stick.EvnStickESSConfirmEditWindow',
	'swStickFSSDataEditWindowExt6' : 'common.Stick.StickFSSDataEditWindow',
	'swEvnStickWorkReleaseEditWindowExt6': 'common.Stick.EvnStickWorkReleaseEditWindow',
	'swOrgSearchWindowExt6': 'common.swOrgSearchWindow',
	// ТАП
	'swEvnPLFinishWindow' : 'common.EvnPL.swEvnPLFinishWindow',
	// Стомат. ТАП
	'swEvnPLStomFinishWindow' : 'common.EvnPLStom.swEvnPLStomFinishWindow',
	// АРМ поликлиники
	'swWorkPlacePolkaWindow' : 'common.PolkaWP.swWorkPlacePolkaWindow',
	'swTimeTableGrafRecListWindow' : 'common.PolkaWP.swTimeTableGrafRecListWindow',
	// АРМ стоматки
	'swWorkPlaceStomWindowExt6' : 'common.PolkaWP.swWorkPlaceStomWindow',
	// АРМ администратора МО
	'swLpuAdminWorkPlaceWindowExt6' : 'common.LpuAdminWP.swLpuAdminWorkPlaceWindow',
	// АРМ Пользователя МО
	'swLpuUserWorkPlaceWindowExt6' : 'common.LpuUserWP.swLpuUserWorkPlaceWindow',
	// АРМ регистратора поликлиники
	'swWorkPlacePolkaRegWindowExt6' : 'common.PolkaRegWP.swWorkPlacePolkaRegWindow',
	'swWorkPlacePolkaRegPrivateWindowExt6' : 'common.PolkaRegPrivateClinic.swWorkPlacePolkaRegPrivateWindow',
	'swPolkaRegPrivateRequestEditWindow' : 'common.PolkaRegPrivateClinic.swPolkaRegPrivateRequestEditWindow',
	'swSelectDestinationRouteListWindow' : 'common.PolkaRegWP.swSelectDestinationRouteListWindow',
	// Окно для выбора одного из двух ответов
	'swYesNoWindow' : 'common.swYesNoWindow',
	// Окно медотвода/отказа от вакинации
	'swExemptVaccineWindow' : 'common.EMK.Vaccination.swExemptVaccineWindow',
	// Онкоспецифика
	'swMorbusOnkoEditWindow' : 'common.MorbusOnko.swMorbusOnkoEditWindow',
	'swOnkoConsultEditWindowExt6' : 'common.MorbusOnko.swOnkoConsultEditWindow',
	'swMorbusOnkoSpecTreatEditWindowExt6' : 'common.MorbusOnko.swMorbusOnkoSpecTreatEditWindow',
	'swEvnUslugaOnkoBeamEditWindowExt6' : 'common.MorbusOnko.swEvnUslugaOnkoBeamEditWindow',
	'swEvnUslugaOnkoSurgEditWindowExt6' : 'common.MorbusOnko.swEvnUslugaOnkoSurgEditWindow',
	'swEvnUslugaOnkoChemEditWindowExt6' : 'common.MorbusOnko.swEvnUslugaOnkoChemEditWindow',
	'swEvnUslugaOnkoGormunEditWindowExt6' : 'common.MorbusOnko.swEvnUslugaOnkoGormunEditWindow',
	'swEvnUslugaOnkoNonSpecEditWindowExt6' : 'common.MorbusOnko.swEvnUslugaOnkoNonSpecEditWindow',
	'swEvnOnkoNotifyEditWindowExt6' : 'common.MorbusOnko.swEvnOnkoNotifyEditWindow',
	'swMorbusOnkoBasePersonStateWindowExt6' : 'common.MorbusOnko.swMorbusOnkoBasePersonStateWindow',
	'swMorbusOnkoTumorStatusWindowExt6' : 'common.MorbusOnko.swMorbusOnkoTumorStatusWindow',
	'swMorbusOnkoBasePSWindowExt6' : 'common.MorbusOnko.swMorbusOnkoBasePSWindow',
	'swMorbusOnkoDrugWindowExt6' : 'common.MorbusOnko.swMorbusOnkoDrugWindow',
	'swMorbusOnkoDrugSelectWindowExt6' : 'common.MorbusOnko.swMorbusOnkoDrugSelectWindow',
	'swMorbusOnkoRefusalWindowExt6' : 'common.MorbusOnko.swMorbusOnkoRefusalWindow',
	'swPersonDispSelectWindow' : 'common.MorbusOnko.swPersonDispSelectWindow',
	// Паллиативная помощь
	'swMorbusPalliatEditWindowExt6' : 'common.MorbusPalliat.swMorbusPalliatEditWindow',
	// РЭМД
	'swEMDCertificateViewWindow': 'emd.swEMDCertificateViewWindow',
	'swEMDCertificateEditWindow': 'emd.swEMDCertificateEditWindow',
	'swEMDSignWindow': 'emd.swEMDSignWindow',
	'swEMDSignatureInfoWindow': 'emd.swEMDSignatureInfoWindow',
	'swERSSignatureWindow': 'emd.swERSSignatureWindow',
	'swEMDVersionViewWindow': 'emd.swEMDVersionViewWindow',
	'swPINCodeWindow': 'emd.swPINCodeWindow',
	'swEMDSearchWindow': 'emd.swEMDSearchWindow',
	'swEMDDocumentSignRulesWindow': 'emd.swEMDDocumentSignRulesWindow',
	'swEMDMedStaffFactRoleWindow': 'emd.swEMDMedStaffFactRoleWindow',
	'swEMDSearchUnsignedWindow': 'emd.swEMDSearchUnsignedWindow',
	'swEMDJournalQueryWindow': 'emd.swEMDJournalQueryWindow',
	'swEMDJournalQueryDetalWindow': 'emd.swEMDJournalQueryDetalWindow',
	// Пользователи онлайн
	'swOnlineUsersWindow' : 'common.swOnlineUsersWindow',
	// Тарифы ТФОМС (Пенза)
	'swTariffValueEditWindow' : 'common.Admin.swTariffValueEditWindow',
	'swTariffValueListWindow' : 'common.Admin.swTariffValueListWindow',
	'swTariffValueImportWindow' : 'common.Admin.swTariffValueImportWindow',
	'swRecalcKSGKSLPWindow' : 'common.Admin.swRecalcKSGKSLPWindow',
	// Работа с реестрами
	'swRegistryHistoryViewWindow' : 'common.Admin.swRegistryHistoryViewWindow',
	'swTableRecordDataWindow' : 'common.Admin.swTableRecordDataWindow',
	// Клинические рекомендации
	'swCureStandartListWindow' : 'common.CureStandart.swCureStandartListWindow',
	'swCureStandartEditWindow' : 'common.CureStandart.swCureStandartEditWindow',
	'swCureStandartServiceWindow' : 'common.CureStandart.swCureStandartServiceWindow',
	'swCureStandartTreatmentDrugWindow' : 'common.CureStandart.swCureStandartTreatmentDrugWindow',
	'swCureStandartNutrMixtureWindow' : 'common.CureStandart.swCureStandartNutrMixtureWindow',
	'swCureStandartImplantWindow' : 'common.CureStandart.swCureStandartImplantWindow',
	'swCureStandartPresBloodWindow' : 'common.CureStandart.swCureStandartPresBloodWindow',
	// Шаблоны
	'swEvnXmlEditorWindow' : 'common.EvnXml.EditorWindow',
	'swXmlTemplateEditorWindow' : 'common.XmlTemplate.EditorWindow',
	'swXmlTemplateSaveWindow' : 'common.XmlTemplate.SaveWindow',
	'swXmlTemplateOpenTextWindow' : 'common.XmlTemplate.OpenTextWindow',
	'swXmlTemplateRenameWindow' : 'common.XmlTemplate.RenameWindow',
	'swXmlTemplateShareWindow' : 'common.XmlTemplate.ShareWindow',
	'swXmlTemplateFolderEditWindow' : 'common.XmlTemplate.FolderEditWindow',
	'swXmlTemplateInputBlockSelectWindow' : 'common.XmlTemplate.InputBlockSelectWindow',
	'swXmlTemplateParameterBlockSelectWindow' : 'common.XmlTemplate.ParameterBlockSelectWindow',
	'swXmlTemplateParameterBlockEditWindow' : 'common.XmlTemplate.ParameterBlockEditWindow',
	'swXmlTemplateSpecMarkerBlockSelectWindow' : 'common.XmlTemplate.SpecMarkerBlockSelectWindow',
	'swXmlTemplateMarkerBlockSelectWindow' : 'common.XmlTemplate.MarkerBlockSelectWindow',
	'swTablePropertiesWindow' : 'common.swTablePropertiesWindow',
	'swEvnXmlPreviousWindow' : 'common.EvnXml.PreviousWindow',
	// Видеосвязь
	'swVideoChatWindow' : 'videoChat.MainWindow',
	'swVideoChatOfferWindow' : 'videoChat.OfferWindow',
	'swVideoChatContactSelectWindow' : 'videoChat.ContactSelectWindow',
	'swVideoChatFileListWindow' : 'videoChat.FileListWindow',

	'swEvnUslugaDispDop13EditWindowExt6' : 'common.EMK.EvnPLDispDop.modalWindows.panelOptionsWindow',
	// УСЛУГИ
		// Общая
		'EvnUslugaCommonEditWindow' : 'usluga.common.EvnUslugaCommonEditWindow',
		// Оперативная
		'EvnUslugaOperEditWindow' : 'usluga.oper.EvnUslugaOperEditWindow',
			// СОПУТСВУЮЩИЕ ОКНА
			// Осложнение
			'EvnAggEditWindow' : 'usluga.associatedwindows.EvnAggEditWindow',
			// Анастезия
			'EvnUslugaOperAnestEditWindow' : 'usluga.associatedwindows.EvnUslugaOperAnestEditWindow',
			// Результат
			'uslugaResultWindow' : 'usluga.associatedwindows.uslugaResultWindow',
			// Врач (бригада)
			'EvnUslugaOperBrigEditWindow' : 'usluga.associatedwindows.EvnUslugaOperBrigEditWindow',
			// Динамика результатов исследований
			'uslugaDynamicsOfTestResultsWindow' : 'usluga.associatedwindows.uslugaDynamicsOfTestResultsWindow',

	// Поиск диагноза
	'DiagSearchTreeWindow': 'common.DiagSearchTreeWindow',
	//Выбор МО и АРМ по умолч.
	'swSelectLpuWindowExt6' : 'common.SelectLpuWindow',
	'swSelectWorkPlaceWindowExt6' : 'common.SelectWorkPlaceWindow',
	'EvnDrugEditWindow': 'common.Drug.EvnDrugEditWindow',
	//Дистанционный мониторинг
	'swRemoteMonitoringWindow': 'common.PolkaWP.RemoteMonitoring.RemoteMonitoringWindow',
	'swRemoteMonitoringRemoveWindow': 'common.PolkaWP.RemoteMonitoring.RemoveWindow',
	'swRemoteMonitoringConsentWindow': 'common.PolkaWP.RemoteMonitoring.ConsentWindow',
	'swRemoteMonitoringInviteWindow': 'common.PolkaWP.RemoteMonitoring.InviteWindow',
	'swRemoteMonitoringInviteHistoryWindow': 'common.PolkaWP.RemoteMonitoring.InviteHistoryWindow',
	'swRemoteMonitoringStatusWindow': 'common.PolkaWP.RemoteMonitoring.StatusWindow',
	'swRemoteMonitoringMessageWindow': 'common.PolkaWP.RemoteMonitoring.MessageWindow',
	// АРМ врача ВК
	'swRefuseEvnPrescrMseWindow' : 'common.Evn.swRefuseEvnPrescrMseWindow',
	//Конструктор форм
	'worksheetConstructor': 'common.Worksheet.worksheetConstructor',
	'worksheetListWindow': 'common.Worksheet.worksheetListWindow',
	'accessParamsWorksheet': 'common.Worksheet.accessParamsWorksheet',
	// Электронная очередь
    'swElectronicTalonHistoryWindow': 'common.ElectronicQueue.swElectronicTalonHistoryWindow',
	'swEvnQueueWaitingListJournal': 'common.Admin.swEvnQueueWaitingListJournal',
	'swChangeDoctorRoomWindow': 'common.ElectronicQueue.swChangeDoctorRoomWindow',
	'swWorkGraphMiddleWindow': 'common.WorkGraphMiddle.swWorkGraphMiddleWindow',
	'swWorkGraphMiddleEditWindow': 'common.WorkGraphMiddle.swWorkGraphMiddleEditWindow',
	// Расписание
	'swTimetableScheduleViewWindow' : 'common.Timetable.ScheduleViewWindow',
	'swTimetableScheduleEditWindow' : 'common.Timetable.ScheduleEditWindow',
	'swTimetableAnnotationEditWindow' : 'common.Timetable.AnnotationEditWindow',
	'swChangeDoctorRoomWindow': 'common.ElectronicQueue.swChangeDoctorRoomWindow',
	// Профилактические прививки
	'swVaccinationTypeWindow': 'common.VaccinationType.swVaccinationTypeWindow',
	'swVaccinationTypeAddWindow': 'common.VaccinationType.swVaccinationTypeAddWindow',
	'swVaccinationTypeEditWindow': 'common.VaccinationType.swVaccinationTypeEditWindow',
	'swVaccinationTypePrepEditWindow': 'common.VaccinationType.swVaccinationTypePrepEditWindow',
	'swVaccinationTypeVaccinationEditWindow': 'common.VaccinationType.swVaccinationTypeVaccinationEditWindow',
	//Поиск по удаленным документам
	'delDocsSearchWindow': 'common.delDocsSearchWindow',
};

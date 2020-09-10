<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
* 
* модель создает исходящий документ в формате HL7
* именно этот файл работает с документом типа 6 - протокол инструментального исследования
*
* @package      EMD
* @access       public
* @copyright    Copyright (c) 2020 Swan Ltd.
* @author       
* @version      10.08.2020
*/
require_once "DocAbstract.php";

class Doc34 extends DocAbstract
{
	
	/**
	* папка (относительная) я файлами стилей и проверки схемы имя папки равно коду документа по фед.справочнику
	* @var $folder string
	*/
	protected $folder="Doc34";
	
	/**
	* имя файла xls которое поставляют федералы
	* @var $file_xls_name string
	*/
	protected $file_xls_name="OrdMedExp.xsl";

	/**
	* имя файла xsd которое поставляют федералы
	* @var $file_xsd_name string
	*/
	protected $file_xsd_name="CDA.xsd";
	
	/**
	* имя view которое собственно создает XML
	* @var $view string
	*/
	protected $view="Doc34";


	/**
	 * Конструктор
	 * обязательно вызвать родительский конструктор
	 * для инициализации базы EMD
	 */
	function __construct() {
		parent::__construct();
	}
	
	/**
	* создание данных для генерации HL7
	* @param $data array
	* @return array
	* @throws Exception
	*/
	public function getInfo(array $data)
	{
		$resp = $this->queryResult("
			select top 1 
				Evn.EvnPrescrMse_id, /*идентификатор bigint*/
				/*Evn.EvnPrescrMse_setDT, - замена на EvnPrescrMse_issueDT*/
				/*Evn.EvnPrescrMse_updDT,*/
				
				Evn.EvnPrescrMse_issueDT,
				Evn.EvnPrescrMse_issueDT as EvnPrescrMse_setDT, /*взамен Evn.EvnPrescrMse_setDT - дата выдачи*/
				/*
				Evn.PrescriptionStatusType_id, /*статус назначения bigint*/
				Evn.EvnPrescrMse_Descr, /*комментарий nvarchar(4000)*/
				Evn.EvnPrescrMse_IsExec, /*признак выполнения (Да/Нет) bigint*/
				Evn.TimeTableGraf_id, /*ссылка на бирку поликлиники bigint*/
				*/
				Evn.EvnVK_id, /*протокол ВК bigint*/
				EvnVK.EvnVK_setDT, /*дата протокола ВК*/
				EvnVK.EvnVK_NumProtocol, /*номер протокола ВК*/
				Evn.EvnPrescrMse_IsFirstTime, /*направляется (Первично / Повторно) bigint*/
				/*
				Evn.Person_sid, /*Законный представитель ФЛ bigint*/
				*/
				Evn.InvalidGroupType_id, /*инвалидность bigint*/
				Evn.EvnPrescrMse_InvalidPercent, /*степень утраты профессиональной трудоспособности в процентах int*/
				Evn.EvnPrescrMse_IsWork, /*работает (Да/Нет) bigint*/
				Evn.Post_id, /*должность bigint*/
				Post.Post_Name, /*наименование должности*/
				Evn.EvnPrescrMse_ExpPost, /*стаж работы по должности (лет) int*/
				Evn.EvnPrescrMse_ExpProf, /*стаж работы по профессии (лет) int*/
				Evn.EvnPrescrMse_Spec, /*специальность nvarchar(32)*/
				Evn.EvnPrescrMse_ExpSpec, /*стаж работы по специальности (лет) int*/
				Evn.EvnPrescrMse_Skill, /*квалификация nvarchar(32)*/
				Evn.EvnPrescrMse_ExpSkill, /*стаж работы по квалификации (лет) int*/
				Evn.Org_id, /*организация (место работы) bigint*/
				Evn.EvnPrescrMse_CondWork, /*условия и характер выполняемого труда nvarchar(128)*/
				Evn.EvnPrescrMse_MainProf, /*основная профессия (специальность) nvarchar(32)*/
				Evn.EvnPrescrMse_MainProfSkill, /*квалификация по основной профессии (класс, разряд, категория, звание) nvarchar(32)*/
				Evn.Org_did, /*образовательное учреждение (место учебы) bigint*/
				Evn.EvnPrescrMse_Dop, /*дополнительно nvarchar(32)*/
				Evn.EvnPrescrMse_DiseaseHist, /*история заболевания nvarchar(-1)*/
				Evn.EvnPrescrMse_LifeHist, /*анамнез жизни nvarchar(-1)*/
				
				ODID.Org_Name as education_Org_Name, /*Наименование учреждения (Образование)*/
				AE.Address_Address as education_AddressText, /*Адрес учреждения (Образование)*/
				LGT.LearnGroupType_Name as education_LearnGroupType_Name, /*Группа/Класс/Курс (Образование)*/
				Evn.EvnPrescrMse_ProfTraining as education_EvnPrescrMse_ProfTraining, /*профессия (Образование)*/
				convert(varchar(8), Evn.EvnPrescrMse_InvalidDate, 112) as EvnPrescrMse_InvalidDate, /*Дата установления инвалидности datetime*/
				convert(varchar(8), Evn.EvnPrescrMse_InvalidEndDate, 112) as EvnPrescrMse_InvalidEndDate, /*дата, до которой установлена инвалидность datetime*/
				IPT.InvalidPeriodType_Code, /*Срок, на который установлена инвалидность*/
				IPT.InvalidPeriodType_Name, /*Срок, на который установлена инвалидность*/
				IPRA.IPRARegistry_Number, /*№ ИПРА*/
				IPRA.IPRARegistry_Protocol, /*Номер протокола проведения МСЭ*/
				convert(varchar(8), IPRA.IPRARegistry_ProtocolDate, 112) as IPRARegistry_ProtocolDate, /*Дата протокола проведения медико-социальной экспертизы*/
				MeasuresRehabEffect.MeasuresRehabEffect_Comment, /*Результаты и эффективность проведенных мероприятий медицинской реабилитации, рекомендованных индивидуальной программой реабилитации или абилитации инвалида (ребенка-инвалида) (ИПРА) (текстовое описание)*/
				ISNULL(Usluga_List.list, 'отсуствуют') AS UslugaComplex_list, /*Сведения о медицинских обследованиях, необходимых для получения клинико-функциональных данных в зависимости от заболевания при проведении медико-социальной экспертизы*/
				Evn.EvnPrescrMse_Recomm, /*Рекомендуемые мероприятия по мед. реабилитации или абилитации nvarchar(256)*/

				/*
				Evn.LearnGroupType_id, /*группа / Класс / Курс bigint*/
				convert(varchar(8), Evn.EvnPrescrMse_OrgMedDate, 112) as EvnPrescrMse_OrgMedDate, /*наблюдается в организациях, оказывающих лечебно-профилактическую помощь с (1950 – текущий год) datetime*/
				Evn.EvnPrescrMse_MedRes, /*результаты проведенных мероприятий по медицинской реабилитации в соответствии с индивидуальной программой реабилитации инвалида nvarchar(-1)*/
				*/
				Evn.EvnPrescrMse_State, /*состояние гражданина при направлении на медико-социальную экспертизу nvarchar(-1)*/
				/*
				Evn.EvnPrescrMse_DopRes, /*результаты дополнительных методов исследования nvarchar(-1)*/
				*/
				Evn.PersonWeight_id, /*вес bigint*/
				PersonWeight.PersonWeight_Weight,
				Evn.PersonHeight_id, /*рост bigint*/
				PersonHeight.PersonHeight_Height,
				/*
				Evn.StateNormType_id, /*оценка психофизиологической выносливости bigint*/
				Evn.StateNormType_did, /*оценка эмоциональной устойчивости bigint*/
				*/
				Evn.Diag_id, /*код основного заболевания по МКБ bigint*/
				Evn.Diag_sid, /*сопутствующее заболевание bigint*/
				Evn.Diag_aid, /*осложнение основного заболевания bigint*/
				Evn.MseDirectionAimType_id, /*цель направления на медико-социальную экспертизу bigint*/
				MseDirectionAimType.MseDirectionAimType_Code,
				MseDirectionAimType.MseDirectionAimType_Name,
				/*
				Evn.EvnPrescrMse_AimMseOver, /*другая цель nvarchar(256)*/
				Evn.ClinicalForecastType_id, /*клинический прогноз bigint*/
				Evn.ClinicalPotentialType_id, /*реабилитационный потенциал bigint*/
				Evn.ClinicalForecastType_did, /*реабилитационный прогноз bigint*/
				Evn.MedPersonal_sid, /*врач-пользователь, подписавший назначение bigint*/
				Evn.LpuSection_sid, /*отделение Врач-пользователь, подписавший назначение bigint*/
				Evn.MedPersonal_cid, /*врач-пользователь, отменивший назначение bigint*/
				Evn.LpuSection_cid, /*отделение Врач-пользователь, отменивший назначение bigint*/
				*/
				Evn.MedService_id, /*служба bigint*/
				/*
				Evn.EvnQueue_id, /*ссылка на бирку на услуги в службы bigint*/
				Evn.TimetableMedService_id, /*ссылка на бирку на услуги в службы bigint*/
				Evn.EvnPrescrMse_Prof, /*профессия nvarchar(50)*/
				Evn.EvnPrescrMse_ProfTraining, /*профессия (специальность), для получения которой проводится обучение nvarchar(150)*/
				*/
				Evn.EvnPrescrMse_MainDisease, /*Основное заболевание nvarchar(255)*/
				/*
				convert(varchar(8), IsNull(Evn.EvnPrescrMse_issueDT,EvnVK.EvnVK_setDate), 112) as EvnPrescrMse_issueDT, /*Дата выдачи datetime*/
				Evn.EvnPrescrMse_FilePath, /*Путь приложенных файлов varchar(4000)*/
				convert(varchar(8), Evn.EvnPrescrMse_appointDT, 112) as EvnPrescrMse_appointDT, /*datetime*/
				Evn.EvnPrescrMse_OrgMedDateMonth, /*аблюдается в организациях, оказывающих лечебно-профилактическую помощь с (месяц) int*/
				Evn.Lpu_gid, /*Направившее МО bigint*/
				Evn.Signatures_mid, /*Подпись врача поликлиники bigint*/
				Evn.Signatures_vid, /*Подпись Председателя ВК bigint*/
				*/
				Evn.MilitaryKind_id, /*Отношение к военной службе bigint*/
				MilitaryKind.MilitaryKind_Code,
				MilitaryKind.MilitaryKind_FullName,
				/*
				Evn.EvnPrescrMse_EaviiasGUID, /*GUID Направления на МСЭ на экспорт в ЕАВИИАС uniqueidentifier(0)*/
				Evn.Org_sid, /*Законный представитель ЮЛ bigint*/
				Evn.EvnPrescrMse_IsCanAppear, /*Может явиться в бюро bigint*/
				*/
				PT.PhysiqueType_Code,
				PT.PhysiqueType_Name,
				Evn.EvnPrescrMse_DailyPhysicDepartures, /*Суточный объем физиологических отправлений (мл) bigint*/
				Evn.EvnPrescrMse_Waist, /*Объем талии bigint*/
				Evn.EvnPrescrMse_Hips, /*Объем бедер bigint*/
				/*
				Evn.EvnPrescrMse_WeightBirth, /*Масса тела при рождении bigint*/
				Evn.EvnPrescrMse_PhysicalDevelopment, /*Физическое развитие nvarchar(255)*/
				*/
				Evn.EvnPrescrMse_MeasureSurgery, /*Рекомендуемые мероприятия по реконструктивной хирургии nvarchar(1024)*/
				Evn.EvnPrescrMse_MeasureProstheticsOrthotics, /*Рекомендуемые мероприятия по протезированию и ортезированию nvarchar(1024)*/
				Evn.EvnPrescrMse_HealthResortTreatment, /*санитарно-курортное лечение nvarchar(1024)*/
				convert(varchar(8), Evn.EvnPrescrMse_InvalidEndDate, 112) as EvnPrescrMse_InvalidEndDate, /*дата, до которой установлена инвалидность datetime*/
				Evn.EvnPrescrMse_InvalidPeriod, /*период, в течении которого гражданин находился на инвалидности bigint*/
				Evn.InvalidCouseType_id, /*причина инвалидности bigint*/
				/*
				Evn.EvnPrescrMse_InvalidCouseAnother, /*иные причины инвалидности nvarchar(1024)*/
				Evn.EvnPrescrMse_InvalidCouseAnotherLaw, /*причина инвалидности (другое законодательство) nvarchar(1024)*/
				Evn.ProfDisabilityPeriod_id, /*ссылка на справочник срок, на который установлена степень утраты профессиональной трудоспособности bigint*/
				convert(varchar(8), Evn.EvnPrescrMse_ProfDisabilityEndDate, 112) as EvnPrescrMse_ProfDisabilityEndDate, /*дата, до которой установлена степень утраты профессиональной трудоспособности datetime*/
				Evn.EvnPrescrMse_ProfDisabilityAgainPercent, /*Степень утраты профессиональной трудоспособности (в процентах), установленная по повторным несчастным случаям на производстве и профессиональным заболеваниям nvarchar(1024)*/
				Evn.Org_gid, /*Гражданин находитсья. Организация bigint*/
				Evn.EvnMse_id, /*Обратный талон МСЭ bigint*/
				*/
				case when Evn.EvnPrescrMse_IsPalliative = 2 then 'true' else 'false' end as EvnPrescrMse_IsPalliative, /*Гражданин нуждается в паллиативной медицинской помощи bigint*/
				/*
				Evn.EvnPrescrMse_Document, /*Документ, удостоверяющий полномочия законного (уполномоченного) представителя varchar(100)*/
				Evn.EvnPrescrMse_DocumentSer, /*Серия документа, удостоверяющего полномочия законного представителя varchar(20)*/
				Evn.EvnPrescrMse_DocumentNum, /*Номер документа, удостоверяющего полномочия законного представителя varchar(20)*/
				Evn.EvnPrescrMse_DocumentIssue, /*Кем выдан для документа, удостоверяющего полномочия законного представителя varchar(255)*/
				convert(varchar(8), Evn.EvnPrescrMse_DocumentDate, 112) as EvnPrescrMse_DocumentDate, /*Дата выдачи документа, удостоверяющего полномочия законного представителя datetime*/
				Evn.EvnPrescrMse_IsPersonInhabitation, /*Гражданин находится по месту жительства bigint*/
				Evn.DocumentAuthority_id, /*идентификатор документов, подтверждающих полномочия законного представителя bigint*/
				Evn.Address_eid, /*адрес образовательного учреждения bigint*/
				Evn.Address_oid, /*адрес организации bigint*/
				*/
				MeasuresRehabEffect.IPRAResult_cid, /*достижение компенсации*/
				MeasuresRehabEffect.IPRAResult_rid, /*воостановление функций*/
				convert(varchar(8), EvnStatusHistory.EvnStatusHistory_begDate, 112) as EvnStatusHistory_begDate, /*Дата и время передачи направления в Бюро МСЭ*/
				LpuOID.PassportToken_tid,
				Evn.Person_id,
				PS.Person_Snils,
				PS.Person_SurName,
				PS.Person_FirName,
				PS.Person_SecName,
				s.Sex_Code,
				s.Sex_Name,
				ua.Address_Address as UAddress_Address,
				ua.Address_Zip as UAddress_Zip,
				uasr.KLSubRgn_Name as UKLSubRgn_Name,
				uat.KLTown_Name as UKLTown_Name,
				uac.KLCity_Name as UKLCity_Name,
				uaco.KLCountry_Name as UKLCountry_Name,
				uas.KLStreet_Name as UKLStreet_Name,
				ua.Address_Corpus as UAddress_Corpus,
				ua.Address_House as UAddress_House,
				ua.Address_Flat as UAddress_Flat,
				ua.KLRgn_id as UKLRgn_id,
				pa.Address_Address as PAddress_Address,
				pa.Address_Zip as PAddress_Zip,
				pasr.KLSubRgn_Name as PKLSubRgn_Name,
				pat.KLTown_Name as PKLTown_Name,
				pac.KLCity_Name as PKLCity_Name,
				paco.KLCountry_Name as PKLCountry_Name,
				pas.KLStreet_Name as PKLStreet_Name,
				pa.Address_Corpus as PAddress_Corpus,
				pa.Address_House as PAddress_House,
				pa.Address_Flat as PAddress_Flat,
				pa.KLRgn_id as PKLRgn_id,
				ps.Person_Phone,
				VPI.PersonInfo_Email,
				L.Lpu_OGRN,
				L.Lpu_Phone,
				L.UAddress_id as LUAddress_id,
				lua.Address_Address as LUAddress_Address,
				lua.Address_Zip as LUAddress_Zip,
				luasr.KLSubRgn_Name as LUKLSubRgn_Name,
				luat.KLTown_Name as LUKLTown_Name,
				luac.KLCity_Name as LUKLCity_Name,
				luas.KLStreet_Name as LUKLStreet_Name,
				lua.Address_Corpus as LUAddress_Corpus,
				lua.Address_House as LUAddress_House,
				lua.KLRgn_id as LUKLRgn_id,
				L.PAddress_id as LPAddress_id,
				lpa.Address_Address as LPAddress_Address,
				lpa.Address_Zip as LPAddress_Zip,
				lpasr.KLSubRgn_Name as LPKLSubRgn_Name,
				lpat.KLTown_Name as LPKLTown_Name,
				lpac.KLCity_Name as LPKLCity_Name,
				lpas.KLStreet_Name as LPKLStreet_Name,
				lpa.Address_Corpus as LPAddress_Corpus,
				lpa.Address_House as LPAddress_House,
				lpa.KLRgn_id as LPKLRgn_id,
				ML.UAddress_id as MLUAddress_id,
				mlua.Address_Address as MLUAddress_Address,
				mlua.Address_Zip as MLUAddress_Zip,
				mluasr.KLSubRgn_Name as MLUKLSubRgn_Name,
				mluat.KLTown_Name as MLUKLTown_Name,
				mluac.KLCity_Name as MLUKLCity_Name,
				mluas.KLStreet_Name as MLUKLStreet_Name,
				mlua.Address_Corpus as MLUAddress_Corpus,
				mlua.Address_House as MLUAddress_House,
				mlua.KLRgn_id as MLUKLRgn_id,
				ML.PAddress_id as MLPAddress_id,
				mlpa.Address_Address as MLPAddress_Address,
				mlpa.Address_Zip as MLPAddress_Zip,
				mlpasr.KLSubRgn_Name as MLPKLSubRgn_Name,
				mlpat.KLTown_Name as MLPKLTown_Name,
				mlpac.KLCity_Name as MLPKLCity_Name,
				mlpas.KLStreet_Name as MLPKLStreet_Name,
				mlpa.Address_Corpus as MLPAddress_Corpus,
				mlpa.Address_House as MLPAddress_House,
				mlpa.KLRgn_id as MLPKLRgn_id,
				o.Org_Name,
				d.Diag_SCode as Diag_Code,
				d.Diag_Name,
				msf.Person_SurName as MedPersonal_SurName,
				msf.Person_FirName as MedPersonal_FirName,
				msf.Person_SecName as MedPersonal_SecName,
				msfs.Person_SurName as sign_MedPersonal_SurName,
				msfs.Person_FirName as sign_MedPersonal_FirName,
				msfs.Person_SecName as sign_MedPersonal_SecName,
				ml.Lpu_Name as MLpu_Name,
				MLpuOID.PassportToken_tid as MPassportToken_tid,
				L.Lpu_Name,
				msf.MedStaffFact_id,
				msfs.MedStaffFact_id as sign_MedStaffFact_id,
				convert(varchar(8), ps.Person_BirthDay, 112) as Person_BirthDay,
				ps.KLCountry_id,
				ISNULL(ps.NationalityStatus_IsTwoNation, 1) as NationalityStatus_IsTwoNation,
				YEAR(Evn.EvnPrescrMse_OrgMedDate) as EvnPrescrMse_OrgMedDateYear,
				cft.ClinicalForecastType_Name,
				cpt.ClinicalPotentialType_Name,
				cftd.ClinicalForecastType_Name as ClinicalForecastType_dName,
				doc.Document_Ser,
				doc.Document_Num,
				convert(varchar(8), doc.Document_begDate, 112) as Document_begDate,
				ndt.DocumentType_id,
				ndt.DocumentType_Name,
				mp.MedPost_Code,
				mp.MedPost_Name,
				mps.MedPost_Code as sign_MedPost_Code,
				mps.MedPost_Name as sign_MedPost_Name,
				IG.InvalidGroup_Code,
				IG.InvalidGroup_Name,
				EvnMse.EvnMse_SendStickDate,
				DATEDIFF(year, EvnMse.EvnMse_ProfDisabilityStartDate, EvnMse.EvnMse_ProfDisabilityEndDate) as ProfDisabilityPeriod,
				EvnMse.EvnMse_ProfDisabilityEndDate,
				PDP.ProfDisabilityPeriod_Code,
				PDP.ProfDisabilityPeriod_Name,
				ICT.InvalidCouseType_Code,
				ICT.InvalidCouseType_Name
			from
				dbo.v_EvnPrescrMse Evn with (nolock)
				left join fed.v_PassportToken LpuOID with(nolock) on LpuOID.Lpu_id = Evn.Lpu_id
				left join dbo.v_EvnVK EvnVK with (nolock) on EvnVK.EvnVK_id = Evn.EvnVK_id
				left join dbo.v_EvnMse EvnMse with (nolock) on EvnMse.EvnMse_id = Evn.EvnMse_id
				left join v_InvalidGroupTypeLink IGTL with (nolock) on IGTL.InvalidGroupType_id = EvnMse.InvalidGroupType_id
				left join nsi.v_InvalidGroup IG with (nolock) on IG.InvalidGroup_id = IGTL.InvalidGroup_nid
				left join v_ProfDisabilityPeriod PDP with (nolock) on PDP.ProfDisabilityPeriod_id = EvnMse.ProfDisabilityPeriod_id and PDP.ProfDisabilityPeriod_id <> 5 and EvnMse.InvalidGroupType_id <> 5 
				left join v_InvalidCouseTypeLink ICTL with (nolock) on ICTL.InvalidCouseType_id = EvnMse.InvalidCouseType_id
				left join nsi.v_InvalidCouseType ICT with (nolock) on ICT.InvalidCouseType_id = ICTL.InvalidCouseType_nid
				left join v_PhysiqueType PT with (nolock) on PT.PhysiqueType_id = Evn.PhysiqueType_id
				left join dbo.v_Post Post with (nolock) on Post.Post_id = Evn.Post_id
				left join dbo.v_PersonWeight PersonWeight with (nolock) on PersonWeight.PersonWeight_id = Evn.PersonWeight_id
				left join dbo.v_PersonHeight PersonHeight with (nolock) on PersonHeight.PersonHeight_id = Evn.PersonHeight_id
				left join dbo.v_MseDirectionAimType MseDirectionAimType with (nolock) on MseDirectionAimType.MseDirectionAimType_id = Evn.MseDirectionAimType_id
				left join dbo.v_MilitaryKind MilitaryKind with (nolock) on MilitaryKind.MilitaryKind_id = Evn.MilitaryKind_id
				left join dbo.v_MeasuresRehabEffect MeasuresRehabEffect with (nolock) on MeasuresRehabEffect.EvnPrescrMse_id = Evn.EvnPrescrMse_id
				left join dbo.v_EvnStatusHistory EvnStatusHistory with (nolock) on EvnStatusHistory.Evn_id = Evn.EvnPrescrMse_id and EvnStatusHistory.EvnStatus_id = 28
				left join v_PersonState ps with (nolock) on ps.Person_id = Evn.Person_id
				left join v_Document doc with (nolock) on doc.Document_id = ps.Document_id
				left join v_DocumentType dt with (nolock) on dt.DocumentType_id = doc.DocumentType_id
				left join nsi.v_DocumentType ndt with (nolock) on ndt.DocumentType_id = dt.Frmr_id
				left join v_Sex s with (nolock) on s.Sex_id = ps.Sex_id
				left join v_Address_all ua with (nolock) on ua.Address_id = ps.UAddress_id
				left join v_KLSubRgn uasr with (nolock) on uasr.KLSubRgn_id = ua.KLSubRgn_id
				left join v_KLTown uat with (nolock) on uat.KLTown_id = ua.KLTown_id
				left join v_KLCity uac with (nolock) on uac.KLCity_id = ua.KLCity_id
				left join v_KLCountry uaco with (nolock) on uaco.KLCountry_id = ua.KLCountry_id
				left join v_KLStreet uas with (nolock) on uas.KLStreet_id = ua.KLStreet_id
				left join v_Address_all pa with (nolock) on pa.Address_id = ps.PAddress_id
				left join v_KLSubRgn pasr with (nolock) on pasr.KLSubRgn_id = pa.KLSubRgn_id
				left join v_KLTown pat with (nolock) on pat.KLTown_id = pa.KLTown_id
				left join v_KLCity pac with (nolock) on pac.KLCity_id = pa.KLCity_id
				left join v_KLCountry paco with (nolock) on paco.KLCountry_id = pa.KLCountry_id
				left join v_KLStreet pas with (nolock) on pas.KLStreet_id = pa.KLStreet_id
				left join v_PersonInfo VPI with (nolock) on VPI.Person_id = PS.Person_id
				left join v_Lpu l with (nolock) on l.Lpu_id = Evn.Lpu_id
				left join v_Address_all lua with (nolock) on lua.Address_id = l.UAddress_id
				left join v_KLSubRgn luasr with (nolock) on luasr.KLSubRgn_id = lua.KLSubRgn_id
				left join v_KLTown luat with (nolock) on luat.KLTown_id = lua.KLTown_id
				left join v_KLCity luac with (nolock) on luac.KLCity_id = lua.KLCity_id
				left join v_KLStreet luas with (nolock) on luas.KLStreet_id = lua.KLStreet_id
				left join v_Address_all lpa with (nolock) on lpa.Address_id = l.PAddress_id
				left join v_KLSubRgn lpasr with (nolock) on lpasr.KLSubRgn_id = lpa.KLSubRgn_id
				left join v_KLTown lpat with (nolock) on lpat.KLTown_id = lpa.KLTown_id
				left join v_KLCity lpac with (nolock) on lpac.KLCity_id = lpa.KLCity_id
				left join v_KLStreet lpas with (nolock) on lpas.KLStreet_id = lpa.KLStreet_id
				left join v_MedService ms with (nolock) on ms.MedService_id = ISNULL(EvnVK.MedService_id, Evn.MedService_id)
				left join v_Lpu ml with (nolock) on ml.Lpu_id = ms.Lpu_id
				left join v_Address_all mlua with (nolock) on mlua.Address_id = ml.UAddress_id
				left join v_KLSubRgn mluasr with (nolock) on mluasr.KLSubRgn_id = mlua.KLSubRgn_id
				left join v_KLTown mluat with (nolock) on mluat.KLTown_id = mlua.KLTown_id
				left join v_KLCity mluac with (nolock) on mluac.KLCity_id = mlua.KLCity_id
				left join v_KLStreet mluas with (nolock) on mluas.KLStreet_id = mlua.KLStreet_id
				left join v_Address_all mlpa with (nolock) on mlpa.Address_id = ml.PAddress_id
				left join v_KLSubRgn mlpasr with (nolock) on mlpasr.KLSubRgn_id = mlpa.KLSubRgn_id
				left join v_KLTown mlpat with (nolock) on mlpat.KLTown_id = mlpa.KLTown_id
				left join v_KLCity mlpac with (nolock) on mlpac.KLCity_id = mlpa.KLCity_id
				left join v_KLStreet mlpas with (nolock) on mlpas.KLStreet_id = mlpa.KLStreet_id
				left join v_Org o with (nolock) on o.Org_id = Evn.Org_id
				left join v_Org ODID with (nolock) on ODID.Org_id = Evn.Org_did
				left join v_Address AE with(nolock) on AE.Address_id = Evn.Address_eid
				LEFT JOIN v_LearnGroupType LGT with(NOLOCK) ON LGT.LearnGroupType_id = Evn.LearnGroupType_id
				LEFT JOIN v_InvalidPeriodType IPT WITH(NOLOCK) ON IPT.InvalidPeriodType_id = Evn.InvalidPeriodType_id
				left join v_IPRARegistry IPRA with(nolock) on IPRA.IPRARegistry_id = MeasuresRehabEffect.IPRARegistry_id
				left join v_Diag d with (nolock) on d.Diag_id = Evn.Diag_id

				/*проблемное место, Evn.EvnPrescrMse_pid равно null, поэтому не можем получить ФИО,должность,код врача*/
				left join v_EvnVizit ev with (nolock) on ev.EvnVizit_id = Evn.EvnPrescrMse_pid
				left join v_EvnSection es with (nolock) on es.EvnSection_id = Evn.EvnPrescrMse_pid
				left join v_EvnPS eps with (nolock) on eps.EvnPS_id = Evn.EvnPrescrMse_pid
				outer apply (
					select top 1
						MedStaffFact_id
					from
						v_EvnSection es2 (nolock)
					where
						es2.EvnSection_pid = eps.EvnPS_id
						and es2.MedStaffFact_id is not null
					order by
						es2.EvnSection_setDT
				) es2
				left join v_EvnPL epl with (nolock) on epl.EvnPL_id = Evn.EvnPrescrMse_pid
				outer apply (
					select top 1
						MedStaffFact_id
					from
						v_EvnVizitPL ev2 (nolock)
					where
						ev2.EvnVizitPL_pid = epl.EvnPL_id
						and ev2.MedStaffFact_id is not null
					order by
						ev2.EvnVizitPL_setDT
				) ev2

				/*КОСТЫЛЬ, читаем доктора-автора по пол EvnVK.MedPersonal_id*/
				left join persis.v_MedWorker mw with (nolock)  on mw.MedWorker_Id = EvnVK.MedPersonal_id

				left join v_MedStaffFact msf with (nolock) on (msf.MedStaffFact_id = coalesce(ev.MedStaffFact_id, ev2.MedStaffFact_id, es.MedStaffFact_id, es2.MedStaffFact_id, eps.MedStaffFact_pid)
					/*КОСТЫЛЬ, если не сможем штатно считать, тогда читаем что можем*/
					or (msf.Person_id = mw.Person_id and Evn.Lpu_id=msf.Lpu_id and ev.MedStaffFact_id is null and ev2.MedStaffFact_id is null and es.MedStaffFact_id is null and es2.MedStaffFact_id is null and eps.MedStaffFact_pid is null)) 

				left join persis.Post p with (nolock) on p.id = msf.Post_id
				left join nsi.MedPost mp with (nolock) on mp.MedPost_id = p.MedPost_id
				left join v_MedStaffFact msfs with (nolock) on msfs.MedStaffFact_id = :MedStaffFact_id
				left join persis.Post psign with (nolock) on psign.id = msfs.Post_id
				left join nsi.MedPost mps with (nolock) on mps.MedPost_id = psign.MedPost_id
				left join fed.v_PassportToken MLpuOID with(nolock) on MLpuOID.Lpu_id = ML.Lpu_id
				left join v_ClinicalForecastType cft with (nolock) on cft.ClinicalForecastType_id = Evn.ClinicalForecastType_id
				left join v_ClinicalPotentialType cpt with (nolock) on cpt.ClinicalPotentialType_id = Evn.ClinicalPotentialType_id
				left join v_ClinicalForecastType cftd with (nolock) on cftd.ClinicalForecastType_id = Evn.ClinicalForecastType_did
				OUTER APPLY(
					SELECT STUFF(
						(
							SELECT 
									', ' + uc.UslugaComplex_Code + ' ' + uc.UslugaComplex_Name
								from
									EvnPrescrMseLink epml (nolock)
									inner join v_EvnUsluga eu (nolock) on eu.EvnUsluga_id = epml.EvnUsluga_id
									inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = eu.UslugaComplex_id
								where
									epml.EvnPrescrMse_id = Evn.EvnPrescrMse_id
							FOR XML PATH ('')
						), 1, 1, ''
					) as list
				) AS Usluga_List
			where
				Evn.EvnPrescrMse_id = :EvnPrescrMse_id
		", array(
			'EvnPrescrMse_id' => $data['EvnPrescrMse_id'],
			'MedStaffFact_id' => $data['MedStaffFact_id']
		));

		if (empty($resp[0]['EvnPrescrMse_id'])) {
			throw new Exception('Ошибка получения данных по направлению на МСЭ', 500);
		}

		if (!empty($resp[0]['PersonWeight_Weight'])) {
			$resp[0]['PersonWeight_WeightGr'] = $resp[0]['PersonWeight_Weight'] * 1000; // в граммах
		}

		if (!empty($resp[0]['PersonHeight_Height'])) {
			$resp[0]['PersonHeight_HeightM'] = $resp[0]['PersonHeight_Height'] / 100; // в метрах
		}

		if (!empty($resp[0]['PersonHeight_Height']) && !empty($resp[0]['PersonWeight_Weight'])) {
			$resp[0]['Person_IMT'] = $resp[0]['PersonWeight_Weight'] / ($resp[0]['PersonHeight_Height'] * $resp[0]['PersonHeight_Height']); // ИМТ
		}

		if (!empty($resp[0]['EvnPrescrMse_IsFirstTime']) && $resp[0]['EvnPrescrMse_IsFirstTime'] == 2) {
			$resp[0]['isFirstTime'] = 'Повторный';
			$resp[0]['isFirstTimeCode'] = '2';
		} else {
			$resp[0]['isFirstTime'] = 'Первичный';
			$resp[0]['isFirstTimeCode'] = '1';
		}

		if (!empty($resp[0]['KLCountry_id']) && $resp[0]['KLCountry_id'] == 643 && $resp[0]['NationalityStatus_IsTwoNation'] == 1) {
			$resp[0]['personNationCode'] = '1';
			$resp[0]['personNationName'] = 'Гражданин Российской Федерации';
		} else if (!empty($resp[0]['KLCountry_id']) && $resp[0]['KLCountry_id'] == 643 && $resp[0]['NationalityStatus_IsTwoNation'] == 2) {
			$resp[0]['personNationCode'] = '2';
			$resp[0]['personNationName'] = 'Гражданин Российской Федерации и иностранного государства (двойное гражданство)';
		} else if (!empty($resp[0]['KLCountry_id']) && $resp[0]['KLCountry_id'] != 643) {
			$resp[0]['personNationCode'] = '3';
			$resp[0]['personNationName'] = 'Иностранный гражданин';
		} else {
			$resp[0]['personNationCode'] = '4';
			$resp[0]['personNationName'] = 'Лицо без гражданства';
		}

		$resp[0]['assignedTime'] = date('YmdHisO');
		$resp[0]['isAssigned'] = 'S';

		/*Срабатывает только для инвалида и вторичного прихода*/
		if(!empty($resp[0]['EvnPrescrMse_InvalidPeriod'])){
			switch($resp[0]['EvnPrescrMse_InvalidPeriod']){
				case 1:
					$resp[0]['ProfDisabilityPeriodBeforeMSE_Name'] = 'Один год';
					$resp[0]['ProfDisabilityPeriodBeforeMSE_Code']=1;
					break;
				case 2:
					$resp[0]['ProfDisabilityPeriodBeforeMSE_Name'] = 'Два года';
					$resp[0]['ProfDisabilityPeriodBeforeMSE_Code']=2;
					break;
				case 3:
					$resp[0]['ProfDisabilityPeriodBeforeMSE_Name'] = 'Три года';
					$resp[0]['ProfDisabilityPeriodBeforeMSE_Code']=3;
					break;
				default:
					$resp[0]['ProfDisabilityPeriodBeforeMSE_Name'] = 'Четыре и более лет';
					$resp[0]['ProfDisabilityPeriodBeforeMSE_Code']=4;
			}
		}
		//обработка проблемной даты документа, в разных базах это работает по разному!
		//ВНИМАНИЕ! при изменении логики здесь, вносите изменения в аналогичную логику функции getMetaDoc
		if (!empty($resp[0]['EvnVK_setDT'])) {
			$resp[0]['EvnVK_setDTFormatted'] = $resp[0]['EvnVK_setDT']->format('d.m.Y');
			$resp[0]['EvnVK_setDT'] = $resp[0]['EvnVK_setDT']->format('YmdHisO');
		} else {
			$resp[0]['EvnVK_setDTFormatted'] = date('d.m.Y');
			$resp[0]['EvnVK_setDT'] = date('YmdHisO');
		}

		$resp[0]['EvnPrescrMse_setDTFormatted'] = '';
		if (!empty($resp[0]['EvnPrescrMse_setDT'])) {
			$resp[0]['EvnPrescrMse_setDTFormatted'] = $resp[0]['EvnPrescrMse_setDT']->format('d.m.Y');
			$resp[0]['EvnPrescrMse_setDT'] = $resp[0]['EvnPrescrMse_setDT']->format('YmdHisO');
		} else {
			//если не заполнена дата выдачи направления, считаем ее равной даты самой ВК
			$resp[0]['EvnPrescrMse_setDTFormatted'] =$resp[0]['EvnVK_setDTFormatted'];
			$resp[0]['EvnPrescrMse_setDT'] =$resp[0]['EvnVK_setDT'];
		}
		if (!empty($resp[0]['EvnPrescrMse_issueDT'])) {
			$resp[0]['EvnPrescrMse_issueDT'] = $resp[0]['EvnPrescrMse_issueDT']->format('YmdHisO');
		} else {
			//это на случай вообще если нет никакой даты
			$resp[0]['EvnPrescrMse_issueDT'] =$resp[0]['EvnVK_setDT'];
		}

		if (!empty($resp[0]['EvnMse_SendStickDate'])) {
			$resp[0]['EvnMse_SendStickDate'] = $resp[0]['EvnMse_SendStickDate']->format('YmdHisO');
		} else {
			$resp[0]['EvnMse_SendStickDate'] = date('YmdHisO');
		}
		if (!empty($resp[0]['EvnMse_ProfDisabilityEndDate'])) {
			$resp[0]['EvnMse_ProfDisabilityEndDate'] = $resp[0]['EvnMse_ProfDisabilityEndDate']->format('YmdHisO');
		}

		if (!empty($resp[0]['EvnPrescrMse_State'])) {
			$resp[0]['EvnPrescrMse_State'] = html_entity_decode(strip_tags($resp[0]['EvnPrescrMse_State']));
		}
		if (!empty($resp[0]['EvnPrescrMse_DiseaseHist'])) {
			$resp[0]['EvnPrescrMse_DiseaseHist'] = html_entity_decode(strip_tags($resp[0]['EvnPrescrMse_DiseaseHist']));
		}
		if (!empty($resp[0]['EvnPrescrMse_LifeHist'])) {
			$resp[0]['EvnPrescrMse_LifeHist'] = html_entity_decode(strip_tags($resp[0]['EvnPrescrMse_LifeHist']));
		}
		if (!empty($resp[0]['MseDirectionAimType_Name'])) {
			$resp[0]['MseDirectionAimType_Name'] = str_replace('"', "'", $resp[0]['MseDirectionAimType_Name']);
		}
		if (empty($resp[0]['EvnPrescrMse_DiseaseHist'])) {
			$resp[0]['EvnPrescrMse_DiseaseHist'] = 'нет данных.';
		}
		if (empty($resp[0]['EvnPrescrMse_LifeHist'])) {
			$resp[0]['EvnPrescrMse_LifeHist'] = 'нет данных.';
		}
		if (empty($resp[0]['EvnPrescrMse_State'])) {
			$resp[0]['EvnPrescrMse_State'] = 'нет данных.';
		}

		if (!empty($resp[0]['ProfDisabilityPeriod'])) {
			if ($resp[0]['ProfDisabilityPeriod'] <= 1) {
				$resp[0]['InvalidPeriod_Code'] = '1';
				$resp[0]['InvalidPeriod_Name'] = 'на один год';
			} else if ($resp[0]['ProfDisabilityPeriod'] <= 2) {
				$resp[0]['InvalidPeriod_Code'] = '2';
				$resp[0]['InvalidPeriod_Name'] = 'на два года';
			} else if ($resp[0]['ProfDisabilityPeriod'] <= 5) {
				$resp[0]['InvalidPeriod_Code'] = '3';
				$resp[0]['InvalidPeriod_Name'] = 'на пять лет';
			} else if ($resp[0]['ProfDisabilityPeriod'] <= 14) {
				$resp[0]['InvalidPeriod_Code'] = '4';
				$resp[0]['InvalidPeriod_Name'] = 'до 14 лет';
			} else {
				$resp[0]['InvalidPeriod_Code'] = '5';
				$resp[0]['InvalidPeriod_Name'] = 'до 18 лет';
			}
		} else {
			$resp[0]['InvalidPeriod_Code'] = '6';
			$resp[0]['InvalidPeriod_Name'] = 'бессрочно';
		}
		
		if( empty($resp[0]['education_EvnPrescrMse_ProfTraining']) && empty($resp[0]['education_EvnPrescrMse_Dop']) && empty($resp[0]['education_AddressText']) && empty($resp[0]['education_Org_Name'])){
			$resp[0]['code4100'] = 'нет данных';
		}else{
			$resp[0]['code4100'] = '';
			if(!empty($resp[0]['education_Org_Name'])) $resp[0]['code4100'] .= $resp[0]['education_Org_Name'];
			if(!empty($resp[0]['education_AddressText'])){
				if($resp[0]['code4100']) $resp[0]['code4100'] .= ', ';
				$resp[0]['code4100'] .= $resp[0]['education_AddressText'];
			}
			if(!empty($resp[0]['education_EvnPrescrMse_Dop'])){
				if($resp[0]['code4100']) $resp[0]['code4100'] .= ', ';
				$resp[0]['code4100'] .= $resp[0]['education_EvnPrescrMse_Dop'];
				if(!empty($resp[0]['education_LearnGroupType_Name'])){
					$resp[0]['code4100'] .= ' '.$resp[0]['education_LearnGroupType_Name'];
				}
			}
			if(!empty($resp[0]['education_EvnPrescrMse_ProfTraining'])){
				if($resp[0]['code4100']) $resp[0]['code4100'] .= ', ';
				$resp[0]['code4100'] .= $resp[0]['education_EvnPrescrMse_ProfTraining'];
			}
		}
		
		//сопутствующие заболевания и осложнения
		$resp[0]['concomitant_and_complications'] = [];
		$resp[0]['concomitant_and_complications']['complicationsFinalDisease'] = [];
		$resp[0]['concomitant_and_complications']['accompanyingIllnesses'] = [];
		if(!empty($data['EvnPrescrMse_id'])){
			//Осложнения основного заболевания
			$sqlComplicationsFinalDisease = "
				select EPMDL.Diag_oid as Diag_id, D.Diag_SCode as Diag_Code, D.Diag_Name,  ISNULL(EPMDL.EvnPrescrMseDiagLink_DescriptDiag,'') as DescriptDiag, EPMDL.EvnPrescrMseDiagLink_id
				from EvnPrescrMseDiagLink  EPMDL WITH(nolock)
				LEFT JOIN v_Diag D WITH(NOLOCK) ON EPMDL.Diag_oid = D.Diag_id
				where EPMDL.EvnPrescrMse_id = :EvnPrescrMse_id and EPMDL.Diag_oid is not NULL
			";
			$resp[0]['concomitant_and_complications']['complicationsFinalDisease'] = $this->queryResult($sqlComplicationsFinalDisease, [
				'EvnPrescrMse_id' => $data['EvnPrescrMse_id']
			]);
			//Сопутствующие заболевания
			$sqlAccompanyingIllnesses = "
				select EPMDL.Diag_id as Diag_id, D.Diag_SCode as Diag_Code, D.Diag_Name, ISNULL(EvnPrescrMseDiagLink_DescriptDiag,'') as DescriptDiag, EPMDL.EvnPrescrMseDiagLink_id
				from EvnPrescrMseDiagLink EPMDL with(nolock)
					LEFT JOIN v_Diag D WITH(NOLOCK) ON EPMDL.Diag_id = D.Diag_id
				where EPMDL.EvnPrescrMse_id = :EvnPrescrMse_id and EPMDL.Diag_id is not NULL
			";
			$accompanyingIllnesses = $this->queryResult($sqlAccompanyingIllnesses, [
				'EvnPrescrMse_id' => $data['EvnPrescrMse_id']
			]);
			//Осложнения сопутствующего заболевания (Complications of concomitant disease)
			$resp[0]['concomitant_and_complications']['accompanyingIllnesses'] = [];
			if($accompanyingIllnesses && is_array($accompanyingIllnesses) && count($accompanyingIllnesses)>0){
				foreach ($accompanyingIllnesses as $key => $line) {
					if(empty($line['EvnPrescrMseDiagLink_id'])) continue;
					$sqlComplicationsOfConcomitantDisease = "
						select EPMDL.Diag_id, D.Diag_SCode as Diag_Code, D.Diag_Name
						FROM EvnPrescrMseDiagMkb10Link EPMDL (nolock) 
							LEFT JOIN v_Diag D WITH(NOLOCK) ON EPMDL.Diag_id = D.Diag_id
						WHERE EPMDL.EvnPrescrMseDiagLink_id = :EvnPrescrMseDiagLink_id
					";
					$accompanyingIllnesses[$key]['complicationsOfConcomitantDisease'] = $this->queryResult($sqlComplicationsOfConcomitantDisease, [
						'EvnPrescrMseDiagLink_id' => $line['EvnPrescrMseDiagLink_id']
					]);
				}
				$resp[0]['concomitant_and_complications']['accompanyingIllnesses'] = $accompanyingIllnesses;
			}
		}
		
		if (empty($resp[0]['EvnPrescrMse_MeasureSurgery'])) {
			$resp[0]['EvnPrescrMse_MeasureSurgery'] = 'нет данных';
		}
		if (empty($resp[0]['EvnPrescrMse_MeasureProstheticsOrthotics'])) {
			$resp[0]['EvnPrescrMse_MeasureProstheticsOrthotics'] = 'нет данных';
		}
		if (empty($resp[0]['EvnPrescrMse_HealthResortTreatment'])) {
			$resp[0]['EvnPrescrMse_HealthResortTreatment'] = 'нет данных';
		}
		if (empty($resp[0]['EvnPrescrMse_Recomm'])) {
			$resp[0]['EvnPrescrMse_Recomm'] = 'нет данных';
		}

		$resp[0]['performers'] = [];
		if (!empty($resp[0]['EvnVK_id'])) {
			// 2 варианта заведения экспертов - обычный (EvnVKExpert), необычный (VoteExpertVK)
			$EvnVKExpert_id = $this->getFirstResultFromQuery("select top 1 EvnVKExpert_id from v_EvnVKExpert (nolock) where EvnVK_id = :EvnVK_id", [
				'EvnVK_id' => $resp[0]['EvnVK_id']
			]);
			
			if (!empty($EvnVKExpert_id)) {
				$from = "
					v_EvnVKExpert evke with (nolock)
					inner join v_EvnVK EVK with (nolock) on EVK.EvnVK_id = evke.EvnVK_id
				";
			} else {
				$from = "
					v_VoteExpertVK evke with (nolock)
					inner join v_VoteListVK VLVK with (nolock) on VLVK.VoteListVK_id = evke.VoteListVK_id
					inner join v_EvnVK EVK with (nolock) on EVK.EvnPrescrVK_id = VLVK.EvnPrescrVK_id
				";
			}
			
			$resp[0]['performers'] = $this->queryResult("
				select
					case when evke.ExpertMedStaffType_id = 1 then 'PPRF' else 'SPRF' end as prf_typeCode,
					mp.Person_SurName as prf_MedPersonal_SurName,
					mp.Person_FirName as prf_MedPersonal_FirName,
					mp.Person_SecName as prf_MedPersonal_SecName,
					mp.Person_Snils as prf_MedPersonal_Snils,
					mp.MedStaffFact_id as prf_MedStaffFact_id,
					mp.MedPost_Code as prf_MedPost_Code,
					mp.MedPost_Name as prf_MedPost_Name,
					mp.Post_Name as prf_Post_Name
				from
					{$from}
					inner join v_MedServiceMedPersonal msmp with (nolock) on msmp.MedServiceMedPersonal_id = evke.MedServiceMedPersonal_id
					inner join v_MedService ms with (nolock) on ms.MedService_id = msmp.MedService_id
					cross apply (
						select top 1
							MSF.Person_SurName,
							MSF.Person_FirName,
							MSF.Person_SecName,
							MSF.Person_Snils,
							MSF.MedStaffFact_id,
							mp.MedPost_Code,
							mp.MedPost_Name,
							Post.name as Post_Name
						from
							v_MedStaffFact MSF with(nolock)
							left join persis.Post Post with (nolock) on Post.id = msf.Post_id
							left join nsi.MedPost mp with (nolock) on mp.MedPost_id = Post.MedPost_id
						where
							MSF.MedPersonal_id = MSMP.MedPersonal_id
							and MSF.Lpu_id = MS.Lpu_id
						order by
							case when MSF.MedStaffFact_id = :MedStaffFact_id then 0 else 1 end,
							case when evke.MedStaffFact_id = MSF.MedStaffFact_id then 0 else 1 end,
							case when mp.MedPost_Code is not null then 0 else 1 end
					) MP
				where
					EVK.EvnVK_id = :EvnVK_id
			", [
				'EvnVK_id' => $resp[0]['EvnVK_id'],
				'MedStaffFact_id' => $data['MedStaffFact_id']
			]);
		}
		
		foreach($resp[0]['performers'] as $performer) {
			if (empty($performer['prf_MedPost_Code'])) {
				throw new Exception("Для специалиста {$performer['prf_Post_Name']} {$performer['prf_MedPersonal_SurName']} {$performer['prf_MedPersonal_FirName']} {$performer['prf_MedPersonal_SecName']} не указано значение из справочника должностей: форма \"Должности\", поле \"Номенклатура должностей\". Создание СЭМЭ невозможно. Обратитесь к администратору системы.");
			}
		}
		
		if (empty($resp[0]["PersonWeight_Weight"]) && empty($resp[0]["PersonHeight_HeightM"]) 
			&& empty($resp[0]["Person_IMT"]) && empty($resp[0]["PhysiqueType_Name"]) 
			&& empty($resp[0]["EvnPrescrMse_DailyPhysicDeparture"]) && empty($resp[0]["EvnPrescrMse_Hips"]) ){
			throw new Exception("Нет ни одного данного из антропометрических параметров (масса, рост и т.д.)");
		}
		//получим номер и ID следующей версии
		$nextEMD=$this->getNextVersion('EvnPrescrMse',$resp[0]["EvnPrescrMse_id"]);
		//добавим номер версии документа
		$resp[0]["EMDDocVersion"]=$nextEMD["Version"];
		$resp[0]["EMDVersion_id"]=$nextEMD["EMDVersion_id"];

		return $resp[0];
	}

	/**
	* получить метаданные по подписываемому документу
	* обязательно $data["Evn_id"] - ID события/записи, здесь МСЭ
	* возвращаемые данные см. запрос
	* @param array массив входных данных
	* @return array массив метаданных подписываемого документа
	*/
	public function getMetaDoc(array $data)
	{
		$frmoPriority = $this->config->item('EMD_FRMO_PRIORITY');
		$frmoPlace = $this->config->item('EMD_FRMO_PLACE');
		if (!empty($frmoPlace) && $frmoPlace == 'section') {
			if (!empty($frmoPriority) && $frmoPriority == 'service') {
				$LpuBuilding_frmo = "ISNULL(ls.LpuSection_FRMOSectionId, fs.FRMOSection_OID)";
			} else {
				$LpuBuilding_frmo = "ISNULL(fs.FRMOSection_OID, ls.LpuSection_FRMOSectionId)";
			}
		} else {
			if (!empty($frmoPriority) && $frmoPriority == 'service') {
				$LpuBuilding_frmo = "ISNULL(lu.LpuUnit_FRMOUnitID, fu.FRMOUnit_OID)";
			} else {
				$LpuBuilding_frmo = "ISNULL(fu.FRMOUnit_OID, lu.LpuUnit_FRMOUnitID)";
			}
		}

		$query = "
				SELECT TOP 1
					evn.Person_id,								/*ID пациента*/
					evn.Lpu_id,									/*ID LPU*/
					cls.EvnClass_id,							/*ID события МСЭ*/
					IsNull(Evn.EvnPrescrMse_issueDT,EvnVK.EvnVK_setDate) updDT, /*Дата выдачи datetime*/
					--e.Evn_updDT as updDT,
					lputid.PassportToken_tid as Lpu_tid,		/*OID LPU*/
					ps.Person_SurName,							/*ФИО пациента*/
      				ps.Person_FirName,
      				ps.Person_SecName,
      				ps.Person_Snils,							/*СНИЛС пациента*/
					ps.Person_EdNum,
					ps.Sex_id as Person_Gender,
					convert(varchar(10), ps.Person_Birthday, 120) as Person_Birthday,
					
					ls.LpuSection_id,
					ls.LpuSectionProfile_id,
					lsp.LpuSectionProfile_fedid,
					ls.LpuBuilding_id,
					-- 0 as Lpu_sid,
					{$LpuBuilding_frmo} as LpuBuilding_frmo,
					lu.LpuUnit_Name,
					evn.EvnPrescrMse_id as docNum				/*ID документа*/

				FROM v_EvnPrescrMse evn (nolock)
				left join v_Evn e (nolock) on e.Evn_id = evn.EvnPrescrMse_id
				left join dbo.v_EvnVK EvnVK with (nolock) on EvnVK.EvnVK_id = Evn.EvnVK_id
				left join v_PersonState ps (nolock) on ps.Person_id = evn.Person_id
				left join fed.v_PassportToken lputid (nolock) on lputid.Lpu_id = evn.Lpu_id
				
				left join v_EvnVizit ev with (nolock) on ev.EvnVizit_id = evn.EvnPrescrMse_pid
				left join v_EvnSection es with (nolock) on es.EvnSection_id = evn.EvnPrescrMse_pid
				left join v_EvnPS eps with (nolock) on eps.EvnPS_id = Evn.EvnPrescrMse_pid
					outer apply (
						select top 1
							MedStaffFact_id
						from
							v_EvnSection es2 (nolock)
						where
							es2.EvnSection_pid = eps.EvnPS_id
							and es2.MedStaffFact_id is not null
						order by
							es2.EvnSection_setDT
					) es2
					left join v_MedStaffFact msf with (nolock) on msf.MedStaffFact_id = coalesce(ev.MedStaffFact_id, es.MedStaffFact_id, es2.MedStaffFact_id, eps.MedStaffFact_pid)
					left join v_LpuSection ls with (nolock) on ls.LpuSection_id = msf.LpuSection_id
					LEFT JOIN nsi.v_FRMOSection fs (nolock) on fs.FRMOSection_id = ls.FRMOSection_id
					LEFT JOIN v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
					LEFT JOIN v_LpuUnit lu (nolock) on lu.LpuUnit_id = ls.LpuUnit_id
					LEFT JOIN nsi.v_FRMOUnit fu (nolock) on fu.FRMOUnit_id = ISNULL(ls.FRMOUnit_id, lu.FRMOUnit_id)

				OUTER APPLY(
					SELECT TOP 1 EvnClass_id
					FROM v_EvnClass (nolock)
					WHERE EvnClass_SysNick = 'EvnPrescrMse'
				) as cls
				WHERE 
					evn.EvnPrescrMse_id = :Evn_id
			";
		$resp=$this->queryResult($query, $data);
		
		if (empty($resp[0]['updDT'])) {
			//это крайний случай он же и в формирователе самого документа
			$resp[0]['updDT'] =$resp[0]['EvnVK_setDT'];
		}
		return $resp;
	}

}

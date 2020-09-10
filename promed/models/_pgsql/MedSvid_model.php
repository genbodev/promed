<?php defined('BASEPATH') or die ('No direct script access allowed');
class MedSvid_model extends SwPgModel {

	public $inputRules = array(
		'loadBirthSvid' => array(
			array('field' => 'BirthSvid_id', 'label' => 'Идентификатор свидетельства', 'rules' => 'required', 'type' => 'id')
		),
		'createBirthSvid' => array(
			array('field' => 'Person_id','label' => 'Идентификатор человека','rules' => 'required','type' => 'id'),
			array('field' => 'ReceptType_id','label' => 'Тип свидетельства','rules' => 'required','type' => 'id'),
			array('field' => 'BirthSvid_Ser','label' => 'Серия','rules' => 'required','type' => 'string'),
			array('field' => 'BirthSvid_Num','label' => 'Номер','rules' => 'required','type' => 'string'),
			array('field' => 'BirthSvid_GiveDate','label' => 'Дата выдачи свидетельства','rules' => 'required','type' => 'date'),
			array('field' => 'BirthEmployment_id','label' => 'Занятость','rules' => 'required','type' => 'id'),
			array('field' => 'BirthEducation_id','label' => 'Образование','rules' => 'required','type' => 'id'),
			array('field' => 'BirthFamilyStatus_id','label' => 'Семейное положение','rules' => '','type' => 'id'),
			array('field' => 'LpuSection_id','label' => 'Отделение','rules' => '','type' => 'id'),
			array('field' => 'MedStaffFact_id','label' => 'Рабочее место врача','rules' => 'required','type' => 'id'),
			array('field' => 'BirthMedPersonalType_id','label' => 'Вид мед персонала','rules' => 'required','type' => 'id'),
			array('field' => 'OrgHead_id','label' => 'Руководитель','rules' => 'required','type' => 'id'),
			array('field' => 'LpuLicence_id','label' => 'Лицензия','rules' => 'required','type' => 'id'),
			array('field' => 'BirthSvid_BirthDT','label' => 'Дата и время рождения','rules' => 'required','type' => 'datetime'),
			array('field' => 'BirthPlace_id','label' => 'Место рождения','rules' => 'required','type' => 'id'),
			array('field' => 'BirthSpecialist_id','label' => 'Принял роды','rules' => 'required','type' => 'id'),
			array('field' => 'BirthSvid_IsMnogoplod','label' => 'Многоплодные роды','rules' => 'required','type' => 'api_flag'),
			array('field' => 'BirthSvid_PlodIndex','label' => 'Который по счету','rules' => '','type' => 'int'),
			array('field' => 'BirthSvid_PlodCount','label' => 'Всего плодов','rules' => '','type' => 'int'),
			array('field' => 'BirthChildResult_id','label' => 'Ребенок родился','rules' => 'required','type' => 'id'),
			array('field' => 'BirthSvid_ChildCount','label' => 'Который ребенок','rules' => 'required','type' => 'int'),
			array('field' => 'BirthSvid_Week','label' => 'Первая явка, неделя','rules' => 'required','type' => 'int'),
			array('field' => 'BirthSvid_Mass','label' => 'Масса','rules' => 'required','type' => 'float'),
			array('field' => 'Okei_mid','label' => 'Единица измерения массы','rules' => 'required','type' => 'int'),
			array('field' => 'BirthSvid_Height','label' => 'Рост','rules' => 'required','type' => 'int'),
			array('field' => 'Sex_id','label' => 'Пол','rules' => 'required','type' => 'id'),
			array('field' => 'BirthSvid_ChildFamil','label' => 'Фамилия ребенка','rules' => '','type' => 'string'),
			array('field' => 'Address_rid','label' => 'Адрес (идентификатор)','rules' => '','type' => 'string'),
			array('field' => 'Person_rid','label' => 'Получатель','rules' => '','type' => 'id'),
			array('field' => 'BirthSvid_RcpDocument','label' => 'Документ','rules' => '','type' => 'string'),
			array('field' => 'DeputyKind_id','label' => 'Отношение к ребёнку','rules' => '','type' => 'id'),
			array('field' => 'BirthSvid_RcpDate','label' => 'Дата получения','rules' => 'required','type' => 'date'),
			array('field' => 'BirthSvid_IsFromMother','label' => 'Записано со слов матери','rules' => 'required','type' => 'api_flag')
		),
		'editBirthSvid' => array(
			array('field' => 'BirthSvid_id','label' => 'Идентификатор Свидетельства о рождении','rules' => 'required','type' => 'id'),
			array('field' => 'ReceptType_id','label' => 'Тип свидетельства','rules' => '','type' => 'id'),
			array('field' => 'BirthSvid_Ser','label' => 'Серия','rules' => '','type' => 'string'),
			array('field' => 'BirthSvid_Num','label' => 'Номер','rules' => '','type' => 'string'),
			array('field' => 'BirthSvid_GiveDate','label' => 'Дата выдачи свидетельства','rules' => '','type' => 'date'),
			array('field' => 'BirthEmployment_id','label' => 'Занятость','rules' => '','type' => 'id'),
			array('field' => 'BirthEducation_id','label' => 'Образование','rules' => '','type' => 'id'),
			array('field' => 'BirthFamilyStatus_id','label' => 'Семейное положение','rules' => '','type' => 'id'),
			array('field' => 'LpuSection_id','label' => 'Отделение','rules' => '','type' => 'id'),
			array('field' => 'MedStaffFact_id','label' => 'Рабочее место врача','rules' => '','type' => 'id'),
			array('field' => 'BirthMedPersonalType_id','label' => 'Вид мед персонала','rules' => '','type' => 'id'),
			array('field' => 'OrgHead_id','label' => 'Руководитель','rules' => '','type' => 'id'),
			array('field' => 'LpuLicence_id','label' => 'Лицензия','rules' => '','type' => 'id'),
			array('field' => 'BirthSvid_BirthDT','label' => 'Дата и время рождения','rules' => '','type' => 'datetime'),
			array('field' => 'BirthPlace_id','label' => 'Место рождения','rules' => '','type' => 'id'),
			array('field' => 'BirthSpecialist_id','label' => 'Принял роды','rules' => '','type' => 'id'),
			array('field' => 'BirthSvid_IsMnogoplod','label' => 'Многоплодные роды','rules' => '','type' => 'id'),
			array('field' => 'BirthSvid_PlodIndex','label' => 'Который по счету','rules' => '','type' => 'int'),
			array('field' => 'BirthSvid_PlodCount','label' => 'Всего плодов','rules' => '','type' => 'int'),
			array('field' => 'BirthChildResult_id','label' => 'Ребенок родился','rules' => '','type' => 'id'),
			array('field' => 'BirthSvid_ChildCount','label' => 'Который ребенок','rules' => '','type' => 'int'),
			array('field' => 'BirthSvid_Week','label' => 'Первая явка, неделя','rules' => '','type' => 'int'),
			array('field' => 'BirthSvid_Mass','label' => 'Масса','rules' => '','type' => 'float'),
			array('field' => 'Okei_mid','label' => 'Единица измерения массы','rules' => '','type' => 'int'),
			array('field' => 'BirthSvid_Height','label' => 'Рост','rules' => '','type' => 'int'),
			array('field' => 'Sex_id','label' => 'Пол','rules' => '','type' => 'id'),
			array('field' => 'BirthSvid_ChildFamil','label' => 'Фамилия ребенка','rules' => '','type' => 'string'),
			array('field' => 'Address_rid','label' => 'Адрес (идентификатор)','rules' => '','type' => 'string'),
			array('field' => 'Person_rid','label' => 'Получатель','rules' => '','type' => 'id'),
			array('field' => 'BirthSvid_RcpDocument','label' => 'Документ','rules' => '','type' => 'string'),
			array('field' => 'DeputyKind_id','label' => 'Отношение к ребёнку','rules' => '','type' => 'id'),
			array('field' => 'BirthSvid_RcpDate','label' => 'Дата получения','rules' => '','type' => 'date'),
			array('field' => 'BirthSvid_IsFromMother','label' => 'Записано со слов матери','rules' => '','type' => 'api_flag')
		),
		'loadBirthSvidListByPerson' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => 'required', 'type' => 'id')
		),
		'loadDeathSvid' => array(
			array('field' => 'DeathSvid_id', 'label' => 'Идентификатор свидетельства', 'rules' => 'required', 'type' => 'id')
		),
		'createDeathSvid' => array(
			array('field' => 'Person_id','label' => 'Идентификатор человека','rules' => 'required','type' => 'id'),
			array('field' => 'ReceptType_id','label' => 'Тип свидетельства','rules' => 'required','type' => 'id'),
			array('field' => 'DeathSvid_Ser','label' => 'Серия','rules' => 'required','type' => 'string'),
			array('field' => 'DeathSvid_Num','label' => 'Номер','rules' => 'required','type' => 'string'),
			array('field' => 'DeathSvid_GiveDate','label' => 'Дата выдачи свидетельства','rules' => 'required','type' => 'date'),
			array('field' => 'DeathSvidType_id','label' => 'Вид свидетельства','rules' => 'required','type' => 'id'),
			array('field' => 'DeathSvid_OldSer','label' => 'Серия','rules' => '','type' => 'string'),
			array('field' => 'DeathSvid_OldNum','label' => 'Номер','rules' => '','type' => 'string'),
			array('field' => 'DeathSvid_OldGiveDate','label' => 'Дата выдачи свидетельства','rules' => '','type' => 'date'),
			array('field' => 'DeathSvid_BirthDateStr','label' => 'Дата рождения','rules' => '','type' => 'date'),
			array('field' => 'DeathSvid_isBirthDate','label' => 'Неполная/неизвестная дата рождения','rules' => '','type' => 'int'),
			array('field' => 'DeathSvid_DeathDate_Date','label' => 'Дата смерти','rules' => '','type' => 'date'),
			array('field' => 'DeathSvid_DeathDate_Time','label' => 'Время смерти','rules' => '','type' => 'time'),
			array('field' => 'DeathSvid_DeathDateStr','label' => 'Неуточ. дата смерти','rules' => '','type' => 'string'),
			array('field' => 'DeathSvid_IsUnknownDeathDate','label' => 'Дата смерти неизвестна','rules' => 'required','type' => 'api_flag'),
			array('field' => 'DeathSvid_IsUnknownDeathTime','label' => 'Время смерти неизвестно','rules' => 'required','type' => 'api_flag'),
			array('field' => 'LpuSection_id','label' => 'Отделение','rules' => 'required','type' => 'id'),
			array('field' => 'MedStaffFact_id','label' => 'Рабочее место врача','rules' => 'required','type' => 'id'),
			array('field' => 'OrgHead_id','label' => 'Руководитель МО','rules' => '','type' => 'id'),
			array('field' => 'Person_mid','label' => 'ФИО матери','rules' => '','type' => 'id'),
			array('field' => 'Address_bid','label' => 'Место','rules' => '','type' => 'id'),
			array('field' => 'ChildTermType_id','label' => 'Доношенность','rules' => '','type' => 'id'),
			array('field' => 'DeathSvid_Mass','label' => 'Масса (г)','rules' => '','type' => 'int'),
			array('field' => 'DeathSvid_ChildCount','label' => 'Который ребенок','rules' => '','type' => 'int'),
			array('field' => 'DeathSvid_Month','label' => 'Месяц жизни','rules' => '','type' => 'int'),
			array('field' => 'DeathSvid_Day','label' => 'День жизни','rules' => '','type' => 'int'),
			array('field' => 'DeathEmployment_id','label' => 'Занятость','rules' => 'required','type' => 'id'),
			array('field' => 'DeathEducation_id','label' => 'Образование','rules' => 'required','type' => 'id'),
			array('field' => 'DeathPlace_id','label' => 'Тип места смерти','rules' => '','type' => 'id'),
			array('field' => 'Address_did','label' => 'Место смерти','rules' => '','type' => 'id'),
			array('field' => 'DeathSvid_IsNoPlace','label' => 'Место смерти неизвестно','rules' => 'required','type' => 'api_flag'),
			array('field' => 'DeathFamilyStatus_id','label' => 'Семейное положение','rules' => '','type' => 'id'),
			array('field' => 'DeathCause_id','label' => 'Причина смерти','rules' => 'required','type' => 'id'),
			array('field' => 'DeathSvid_TraumaDate_Date','label' => 'Дата н/случая, отравления, травмы','rules' => '','type' => 'date'),
			array('field' => 'DeathSvid_TraumaDate_Time','label' => 'Время н/случая, отравления, травмы','rules' => '','type' => 'time'),
			array('field' => 'DeathSvid_TraumaDateStr','label' => 'Неуточ. дата н/случая, отравления, травмы','rules' => '','type' => 'string'),
			array('field' => 'DeathTrauma_id','label' => 'Вид травмы','rules' => '','type' => 'id'),
			array('field' => 'DtpDeathTime_id','label' => 'Смерть от ДТП наступила','rules' => '','type' => 'id'),
			array('field' => 'DeathSvid_TraumaDescr','label' => 'Место и обстоятельства, при которых произошла травма (отравление)','rules' => '','type' => 'string'),
			array('field' => 'DeathSetType_id','label' => 'Причина смерти установлена','rules' => 'required','type' => 'id'),
			array('field' => 'DeathSetCause_id','label' => 'На основании','rules' => 'required','type' => 'id'),
			array('field' => 'Diag_iid','label' => 'Непосредственная причина смерти','rules' => 'required','type' => 'id'),
			array('field' => 'Diag_tid','label' => 'Патологическое состояние','rules' => 'required','type' => 'id'),
			array('field' => 'Diag_mid','label' => 'Основная причина смерти','rules' => 'required','type' => 'id'),
			array('field' => 'Diag_eid','label' => 'Внешние причины','rules' => 'required','type' => 'id'),
			array('field' => 'Diag_oid','label' => 'Прочие важные состояния','rules' => '','type' => 'id'),
			array('field' => 'DeathSvid_Oper','label' => 'Причины, не связанные с болезнью, а также операции','rules' => '','type' => 'string'),
			array('field' => 'Person_rid','label' => 'ФИО получателя','rules' => '','type' => 'id'),
			array('field' => 'DeathSvid_PolFio','label' => 'ФИО получателя (ручной ввод)','rules' => '','type' => 'string'),
			array('field' => 'DeathSvid_RcpDocument','label' => 'Документ','rules' => '','type' => 'string'),
			array('field' => 'DeathSvid_RcpDate','label' => 'Дата получения','rules' => '','type' => 'date')
		),
		'editDeathSvid' => array(
			array('field' => 'DeathSvid_id','label' => 'Идентификатор свидетельства','rules' => 'required','type' => 'id'),
			array('field' => 'ReceptType_id','label' => 'Тип свидетельства','rules' => '','type' => 'id'),
			array('field' => 'DeathSvid_Ser','label' => 'Серия','rules' => '','type' => 'string'),
			array('field' => 'DeathSvid_Num','label' => 'Номер','rules' => '','type' => 'string'),
			array('field' => 'DeathSvid_GiveDate','label' => 'Дата выдачи свидетельства','rules' => '','type' => 'date'),
			array('field' => 'DeathSvidType_id','label' => 'Вид свидетельства','rules' => '','type' => 'id'),
			array('field' => 'DeathSvid_OldSer','label' => 'Серия','rules' => '','type' => 'string'),
			array('field' => 'DeathSvid_OldNum','label' => 'Номер','rules' => '','type' => 'string'),
			array('field' => 'DeathSvid_OldGiveDate','label' => 'Дата выдачи свидетельства','rules' => '','type' => 'date'),
			array('field' => 'DeathSvid_BirthDateStr','label' => 'Дата рождения','rules' => '','type' => 'date'),
			array('field' => 'DeathSvid_isBirthDate','label' => 'Неполная/неизвестная дата рождения','rules' => '','type' => 'int'),
			array('field' => 'DeathSvid_DeathDate_Date','label' => 'Дата смерти','rules' => '','type' => 'date'),
			array('field' => 'DeathSvid_DeathDate_Time','label' => 'Время смерти','rules' => '','type' => 'time'),
			array('field' => 'DeathSvid_DeathDateStr','label' => 'Неуточ. дата смерти','rules' => '','type' => 'string'),
			array('field' => 'DeathSvid_IsUnknownDeathDate','label' => 'Дата смерти неизвестна','rules' => '','type' => 'api_flag'),
			array('field' => 'DeathSvid_IsUnknownDeathTime','label' => 'Время смерти неизвестно','rules' => '','type' => 'api_flag'),
			array('field' => 'LpuSection_id','label' => 'Отделение','rules' => '','type' => 'id'),
			array('field' => 'MedStaffFact_id','label' => 'Рабочее место врача','rules' => '','type' => 'id'),
			array('field' => 'OrgHead_id','label' => 'Руководитель МО','rules' => '','type' => 'id'),
			array('field' => 'Person_mid','label' => 'ФИО матери','rules' => '','type' => 'id'),
			array('field' => 'Address_bid','label' => 'Место','rules' => '','type' => 'id'),
			array('field' => 'ChildTermType_id','label' => 'Доношенность','rules' => '','type' => 'id'),
			array('field' => 'DeathSvid_Mass','label' => 'Масса (г)','rules' => '','type' => 'int'),
			array('field' => 'DeathSvid_ChildCount','label' => 'Который ребенок','rules' => '','type' => 'int'),
			array('field' => 'DeathSvid_Month','label' => 'Месяц жизни','rules' => '','type' => 'int'),
			array('field' => 'DeathSvid_Day','label' => 'День жизни','rules' => '','type' => 'int'),
			array('field' => 'DeathEmployment_id','label' => 'Занятость','rules' => '','type' => 'id'),
			array('field' => 'DeathEducation_id','label' => 'Образование','rules' => '','type' => 'id'),
			array('field' => 'DeathPlace_id','label' => 'Тип места смерти','rules' => '','type' => 'id'),
			array('field' => 'Address_did','label' => 'Место смерти','rules' => '','type' => 'id'),
			array('field' => 'DeathSvid_IsNoPlace','label' => 'Место смерти неизвестно','rules' => '','type' => 'api_flag'),
			array('field' => 'DeathFamilyStatus_id','label' => 'Семейное положение','rules' => '','type' => 'id'),
			array('field' => 'DeathCause_id','label' => 'Причина смерти','rules' => '','type' => 'id'),
			array('field' => 'DeathSvid_TraumaDate_Date','label' => 'Дата н/случая, отравления, травмы','rules' => '','type' => 'date'),
			array('field' => 'DeathSvid_TraumaDate_Time','label' => 'Время н/случая, отравления, травмы','rules' => '','type' => 'time'),
			array('field' => 'DeathSvid_TraumaDateStr','label' => 'Неуточ. дата н/случая, отравления, травмы','rules' => '','type' => 'string'),
			array('field' => 'DeathTrauma_id','label' => 'Вид травмы','rules' => '','type' => 'id'),
			array('field' => 'DtpDeathTime_id','label' => 'Смерть от ДТП наступила','rules' => '','type' => 'id'),
			array('field' => 'DeathSvid_TraumaDescr','label' => 'Место и обстоятельства, при которых произошла травма (отравление)','rules' => '','type' => 'string'),
			array('field' => 'DeathSetType_id','label' => 'Причина смерти установлена','rules' => '','type' => 'id'),
			array('field' => 'DeathSetCause_id','label' => 'На основании','rules' => '','type' => 'id'),
			array('field' => 'Diag_iid','label' => 'Непосредственная причина смерти','rules' => '','type' => 'id'),
			array('field' => 'Diag_tid','label' => 'Патологическое состояние','rules' => '','type' => 'id'),
			array('field' => 'Diag_mid','label' => 'Основная причина смерти','rules' => '','type' => 'id'),
			array('field' => 'Diag_eid','label' => 'Внешние причины','rules' => '','type' => 'id'),
			array('field' => 'Diag_oid','label' => 'Прочие важные состояния','rules' => '','type' => 'id'),
			array('field' => 'DeathSvid_Oper','label' => 'Причины, не связанные с болезнью, а также операции','rules' => '','type' => 'string'),
			array('field' => 'Person_rid','label' => 'ФИО получателя','rules' => '','type' => 'id'),
			array('field' => 'DeathSvid_PolFio','label' => 'ФИО получателя (ручной ввод)','rules' => '','type' => 'string'),
			array('field' => 'DeathSvid_RcpDocument','label' => 'Документ','rules' => '','type' => 'string'),
			array('field' => 'DeathSvid_RcpDate','label' => 'Дата получения','rules' => '','type' => 'date')
		),
		'loadDeathSvidListByPerson' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => 'required', 'type' => 'id')
		),
		'loadPntDeathSvid' => array(
			array('field' => 'PntDeathSvid_id', 'label' => 'Идентификатор свидетельства', 'rules' => 'required', 'type' => 'id')
		),
		'createPntDeathSvid' => array(
			array('field' => 'Person_id','label' => 'Идентификатор человека','rules' => 'required','type' => 'id'),
			array('field' => 'ReceptType_id','label' => 'Тип свидетельства','rules' => 'required','type' => 'id'),
			array('field' => 'PntDeathSvid_Ser','label' => 'Серия','rules' => 'required','type' => 'string'),
			array('field' => 'PntDeathSvid_Num','label' => 'Номер','rules' => 'required','type' => 'string'),
			array('field' => 'PntDeathSvid_GiveDate','label' => 'Дата выдачи свидетельства','rules' => 'required','type' => 'date'),
			array('field' => 'DeathSvidType_id','label' => 'Вид свидетельства','rules' => 'required','type' => 'id'),
			array('field' => 'PntDeathSvid_OldSer','label' => 'Серия','rules' => '','type' => 'string'),
			array('field' => 'PntDeathSvid_OldNum','label' => 'Номер','rules' => '','type' => 'string'),
			array('field' => 'PntDeathSvid_OldGiveDate','label' => 'Дата выдачи свидетельства','rules' => '','type' => 'date'),
			array('field' => 'PntDeathSvid_DeathDate','label' => 'Дата смерти','rules' => '','type' => 'datetime'),
			array('field' => 'PntDeathSvid_DeathDateStr','label' => 'Неуточ. дата смерти','rules' => '','type' => 'string'),
			array('field' => 'LpuSection_id','label' => 'Отделение','rules' => 'required','type' => 'id'),
			array('field' => 'MedStaffFact_id','label' => 'Рабочее место врача','rules' => 'required','type' => 'id'),
			array('field' => 'OrgHead_id','label' => 'Руководитель МО','rules' => '','type' => 'id'),
			array('field' => 'PntDeathSvid_ChildBirthDT','label' => 'Неуточненная дата родов','rules' => '','type' => 'datetime'),
			array('field' => 'PntDeathSvid_ChildBirthDateStr','label' => 'Неуточненная дата родов','rules' => '','type' => 'string'),
			array('field' => 'PntDeathPeriod_id','label' => 'Период смерти','rules' => '','type' => 'id'),
			array('field' => 'PntDeathTime_id','label' => 'Время смерти','rules' => '','type' => 'id'),
			array('field' => 'DeathEmployment_id','label' => 'Занятость','rules' => 'required','type' => 'id'),
			array('field' => 'PntDeathEducation_id','label' => 'Образование','rules' => 'required','type' => 'id'),
			array('field' => 'PntDeathFamilyStatus_id','label' => 'Семейное положение','rules' => '','type' => 'id'),
			array('field' => 'PntDeathSvid_BirthCount','label' => 'Которые роды','rules' => '','type' => 'int'),
			array('field' => 'PntDeathSvid_ChildCount','label' => 'Который ребенок','rules' => '','type' => 'int'),
			array('field' => 'PntDeathSvid_ChildFio','label' => 'ФИО ребенка','rules' => '','type' => 'string'),
			array('field' => 'PntDeathPlace_id','label' => 'Смерть наступила','rules' => 'required','type' => 'id'),
			array('field' => 'Address_did','label' => 'Адрес наступления смерти','rules' => 'required','type' => 'id'),
			array('field' => 'PntDeathSvid_IsNoPlace','label' => 'Место смерти неизвестно','rules' => 'required','type' => 'api_flag'),
			array('field' => 'Sex_id','label' => 'Пол ребенка','rules' => '','type' => 'id'),
			array('field' => 'PntDeathGetBirth_id','label' => 'Роды принял','rules' => '','type' => 'id'),
			array('field' => 'PntDeathSvid_Mass','label' => 'Масса при рождении','rules' => '','type' => 'int'),
			array('field' => 'PntDeathSvid_Height','label' => 'Рост при рождении','rules' => '','type' => 'int'),
			array('field' => 'PntDeathSvid_IsMnogoplod','label' => 'Многоплод. Роды','rules' => 'required','type' => 'api_flag'),
			array('field' => 'PntDeathSvid_PlodIndex','label' => 'Который по счету','rules' => '','type' => 'int'),
			array('field' => 'PntDeathSvid_PlodCount','label' => 'Всего плодов','rules' => '','type' => 'int'),
			array('field' => 'PntDeathCause_id','label' => 'Причина смерти','rules' => '','type' => 'id'),
			array('field' => 'PntDeathSvid_ActNumber','label' => 'Номер документа','rules' => '','type' => 'string'),
			array('field' => 'PntDeathSvid_ActDT','label' => 'Дата записи акта','rules' => '','type' => 'date'),
			array('field' => 'OrgDep_id','label' => 'Наименование органа ЗАГС','rules' => '','type' => 'id'),
			array('field' => 'PntDeathSvid_ZagsFIO','label' => 'ФИО работника органа ЗАГС','rules' => '','type' => 'string'),
			array('field' => 'PntDeathSvidType_id','label' => 'Причины смерти','rules' => '','type' => 'id'),
			array('field' => 'Diag_iid','label' => 'Основное заболевание ребенка','rules' => 'required','type' => 'id'),
			array('field' => 'Diag_eid','label' => 'Другие заболевания матери','rules' => '','type' => 'id'),
			array('field' => 'Diag_mid','label' => 'Основное заболевание матери','rules' => '','type' => 'id'),
			array('field' => 'Diag_tid','label' => 'Другие заболевания ребенка','rules' => '','type' => 'id'),
			array('field' => 'Diag_oid','label' => 'Другие обстоятельства','rules' => '','type' => 'id'),
			array('field' => 'PntDeathSetType_id','label' => 'Причина смерти установлена','rules' => 'required','type' => 'id'),
			array('field' => 'PntDeathSetCause_id','label' => 'На основании','rules' => 'required','type' => 'id'),
			array('field' => 'Person_rid','label' => 'Получатель свидетельства','rules' => '','type' => 'id'),
			array('field' => 'PntDeathSvid_PolFio','label' => 'ФИО получателя (ручной ввод)','rules' => '','type' => 'string'),
			array('field' => 'PntDeathSvid_RcpDoc','label' => 'Документ','rules' => '','type' => 'string'),
			array('field' => 'DeputyKind_id','label' => 'Отношение к ребёнку','rules' => '','type' => 'id'),
			array('field' => 'PntDeathSvid_RcpDate','label' => 'Дата получения','rules' => '','type' => 'date'),
			array('field' => 'PntDeathSvid_IsFromMother','label' => 'Записано со слов матери','rules' => 'required','type' => 'api_flag')
		),
		'editPntDeathSvid' => array(
			array('field' => 'PntDeathSvid_id','label' => 'Идентификатор свидетельства','rules' => 'required','type' => 'id'),
			array('field' => 'ReceptType_id','label' => 'Тип свидетельства','rules' => '','type' => 'id'),
			array('field' => 'PntDeathSvid_Ser','label' => 'Серия','rules' => '','type' => 'string'),
			array('field' => 'PntDeathSvid_Num','label' => 'Номер','rules' => '','type' => 'string'),
			array('field' => 'PntDeathSvid_GiveDate','label' => 'Дата выдачи свидетельства','rules' => '','type' => 'date'),
			array('field' => 'DeathSvidType_id','label' => 'Вид свидетельства','rules' => '','type' => 'id'),
			array('field' => 'PntDeathSvid_OldSer','label' => 'Серия','rules' => '','type' => 'string'),
			array('field' => 'PntDeathSvid_OldNum','label' => 'Номер','rules' => '','type' => 'string'),
			array('field' => 'PntDeathSvid_OldGiveDate','label' => 'Дата выдачи свидетельства','rules' => '','type' => 'date'),
			array('field' => 'PntDeathSvid_DeathDate','label' => 'Дата смерти','rules' => '','type' => 'datetime'),
			array('field' => 'PntDeathSvid_DeathDateStr','label' => 'Неуточ. дата смерти','rules' => '','type' => 'string'),
			array('field' => 'LpuSection_id','label' => 'Отделение','rules' => 'required','type' => 'id'),
			array('field' => 'MedStaffFact_id','label' => 'Рабочее место врача','rules' => '','type' => 'id'),
			array('field' => 'OrgHead_id','label' => 'Руководитель МО','rules' => '','type' => 'id'),
			array('field' => 'PntDeathSvid_ChildBirthDT','label' => 'Неуточненная дата родов','rules' => '','type' => 'datetime'),
			array('field' => 'PntDeathSvid_ChildBirthDateStr','label' => 'Неуточненная дата родов','rules' => '','type' => 'string'),
			array('field' => 'PntDeathPeriod_id','label' => 'Период смерти','rules' => '','type' => 'id'),
			array('field' => 'PntDeathTime_id','label' => 'Время смерти','rules' => '','type' => 'id'),
			array('field' => 'DeathEmployment_id','label' => 'Занятость','rules' => '','type' => 'id'),
			array('field' => 'PntDeathEducation_id','label' => 'Образование','rules' => '','type' => 'id'),
			array('field' => 'PntDeathFamilyStatus_id','label' => 'Семейное положение','rules' => '','type' => 'id'),
			array('field' => 'PntDeathSvid_BirthCount','label' => 'Которые роды','rules' => '','type' => 'int'),
			array('field' => 'PntDeathSvid_ChildCount','label' => 'Который ребенок','rules' => '','type' => 'int'),
			array('field' => 'PntDeathSvid_ChildFio','label' => 'ФИО ребенка','rules' => '','type' => 'string'),
			array('field' => 'PntDeathPlace_id','label' => 'Смерть наступила','rules' => '','type' => 'id'),
			array('field' => 'Address_did','label' => 'Адрес наступления смерти','rules' => '','type' => 'id'),
			array('field' => 'PntDeathSvid_IsNoPlace','label' => 'Место смерти неизвестно','rules' => '','type' => 'api_flag'),
			array('field' => 'Sex_id','label' => 'Пол ребенка','rules' => '','type' => 'id'),
			array('field' => 'PntDeathGetBirth_id','label' => 'Роды принял','rules' => '','type' => 'id'),
			array('field' => 'PntDeathSvid_Mass','label' => 'Масса при рождении','rules' => '','type' => 'int'),
			array('field' => 'PntDeathSvid_Height','label' => 'Рост при рождении','rules' => '','type' => 'int'),
			array('field' => 'PntDeathSvid_IsMnogoplod','label' => 'Многоплод. Роды','rules' => '','type' => 'api_flag'),
			array('field' => 'PntDeathSvid_PlodIndex','label' => 'Который по счету','rules' => '','type' => 'int'),
			array('field' => 'PntDeathSvid_PlodCount','label' => 'Всего плодов','rules' => '','type' => 'int'),
			array('field' => 'PntDeathCause_id','label' => 'Причина смерти','rules' => '','type' => 'id'),
			array('field' => 'PntDeathSvid_ActNumber','label' => 'Номер документа','rules' => '','type' => 'string'),
			array('field' => 'PntDeathSvid_ActDT','label' => 'Дата записи акта','rules' => '','type' => 'date'),
			array('field' => 'OrgDep_id','label' => 'Наименование органа ЗАГС','rules' => '','type' => 'id'),
			array('field' => 'PntDeathSvid_ZagsFIO','label' => 'ФИО работника органа ЗАГС','rules' => '','type' => 'string'),
			array('field' => 'PntDeathSvidType_id','label' => 'Причины смерти','rules' => '','type' => 'id'),
			array('field' => 'Diag_iid','label' => 'Основное заболевание ребенка','rules' => '','type' => 'id'),
			array('field' => 'Diag_eid','label' => 'Другие заболевания матери','rules' => '','type' => 'id'),
			array('field' => 'Diag_mid','label' => 'Основное заболевание матери','rules' => '','type' => 'id'),
			array('field' => 'Diag_tid','label' => 'Другие заболевания ребенка','rules' => '','type' => 'id'),
			array('field' => 'Diag_oid','label' => 'Другие обстоятельства','rules' => '','type' => 'id'),
			array('field' => 'PntDeathSetType_id','label' => 'Причина смерти установлена','rules' => '','type' => 'id'),
			array('field' => 'PntDeathSetCause_id','label' => 'На основании','rules' => '','type' => 'id'),
			array('field' => 'Person_rid','label' => 'Получатель свидетельства','rules' => '','type' => 'id'),
			array('field' => 'PntDeathSvid_PolFio','label' => 'ФИО получателя (ручной ввод)','rules' => '','type' => 'string'),
			array('field' => 'PntDeathSvid_RcpDoc','label' => 'Документ','rules' => '','type' => 'string'),
			array('field' => 'DeputyKind_id','label' => 'Отношение к ребёнку','rules' => '','type' => 'id'),
			array('field' => 'PntDeathSvid_RcpDate','label' => 'Дата получения','rules' => '','type' => 'date'),
			array('field' => 'PntDeathSvid_IsFromMother','label' => 'Записано со слов матери','rules' => '','type' => 'api_flag')
		),
		'loadPntDeathSvidListByPerson' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => 'required', 'type' => 'id')
		)
	);

	/**
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();
	}
	
	/**
	* Ищет свидетельства о смерти по различным атрибутам
	* @to-do добавить фильтры для поиска
	*/
	function getDeathSvidByAttr($data) {
		$filters = '(1=1)';
		
		if(!empty($data['Person_id']))
			$filters .= ' AND ds.Person_id = :Person_id';

		if(isset($data['DeathSvid_IsBad']))
		{
			if($data['DeathSvid_IsBad']==2)
				//ищем испорченные св-ва
				$filters .= ' AND ds.DeathSvid_IsBad = 2';
			else
				//ищем действительные св-ва
				$filters .= ' AND (ds.DeathSvid_IsBad = 1 or ds.DeathSvid_IsBad is null)';
		}
			
		$orderby = '';
		// проверяем нужна ли сортировка
		if(isset($data['OrderByDeathSvid_id']))
		{
			if($data['OrderByDeathSvid_id']=='asc')
				// сортировка по возрастанию
				$orderby = ' order by ds.DeathSvid_id asc';
			else
				// сортировка по убыванию
				$orderby = ' order by ds.DeathSvid_id desc';
		}
		
		$query = "
			select
                Server_id as \"Server_id\", 
                DeathPlace_id as \"DeathPlace_id\",
                DeathEducation_id as \"DeathEducation_id\",
                DeathTrauma_id as \"DeathTrauma_id\",
                DeathSetType_id as \"DeathSetType_id\",
                DeathSetCause_id as \"DeathSetCause_id\",
                DeathWomanType_id as \"DeathWomanType_id\",
                DeathEmployment_id as \"DeathEmployment_id\",
                DtpDeathTime_id as \"DtpDeathTime_id\",
                ChildTermType_id as \"ChildTermType_id\",
                Address_bid as \"Address_bid\",
                Address_did as \"Address_did\",
                Diag_iid as \"Diag_iid\",
                Diag_eid as \"Diag_eid\",
                Diag_mid as \"Diag_mid\",
                Diag_tid as \"Diag_tid\",
                Diag_oid as \"Diag_oid\",
                DeathSvid_Ser as \"DeathSvid_Ser\",
                DeathSvid_Num as \"DeathSvid_Num\",
                DeathSvid_OldSer as \"DeathSvid_OldSer\",
                DeathSvid_OldNum as \"DeathSvid_OldNum\",
                DeathSvid_GiveDate as \"DeathSvid_GiveDate\",
                DeathSvid_OldGiveDate as \"DeathSvid_OldGiveDate\",
                DeathSvid_DeathDate as \"DeathSvid_DeathDate\",
                DeathSvid_IsTerm as \"DeathSvid_IsTerm\",
                DeathSvid_Mass as \"DeathSvid_Mass\",
                DeathSvid_Month as \"DeathSvid_Month\",
                DeathSvid_Day as \"DeathSvid_Day\",
                DeathSvid_ChildCount as \"DeathSvid_ChildCount\",
                DeathSvid_TraumaDate as \"DeathSvid_TraumaDate\",
                DeathSvid_TraumaDescr as \"DeathSvid_TraumaDescr\",
                DeathSvid_Oper as \"DeathSvid_Oper\",
                DeathSvid_PribPeriod as \"DeathSvid_PribPeriod\",
                DeathSvid_RcpDate as \"DeathSvid_RcpDate\",
                DeathSvid_RcpDocument as \"DeathSvid_RcpDocument\",
                DeathSvid_IsBad as \"DeathSvid_IsBad\",
                ds.pmUser_insID as \"pmUser_insID\",
                ds.pmUser_updID as \"pmUser_updID\",
                DeathSvid_insDT as \"DeathSvid_insDT\",
                DeathSvid_updDT as \"DeathSvid_updDT\",
                FamilyStatus_id as \"FamilyStatus_id\",
                DeathSvid_IsSigned as \"DeathSvid_IsSigned\",
                pmUser_signID as \"pmUser_signID\",
                DeathSvid_signDT as \"DeathSvid_signDT\",
                DeathSvid_IsMarried as \"DeathSvid_IsMarried\",
                OrgHeadPost_id as \"OrgHeadPost_id\",
                Person_hid as \"Person_hid\",
                DeathSvid_IsDuplicate as \"DeathSvid_IsDuplicate\",
                DeathSvid_IsLose as \"DeathSvid_IsLose\",
                DeathSvid_BirthDateStr as \"DeathSvid_BirthDateStr\",
                DeathSvid_DeathDateStr as \"DeathSvid_DeathDateStr\",
                DeathSvid_PribPeriodPat as \"DeathSvid_PribPeriodPat\",
                DeathSvid_PribPeriodDom as \"DeathSvid_PribPeriodDom\",
                DeathSvid_PribPeriodExt as \"DeathSvid_PribPeriodExt\",
                DeathSvid_PribPeriodImp as \"DeathSvid_PribPeriodImp\",
                DeathSvid_LaborDateStr as \"DeathSvid_LaborDateStr\",
                MedStaffFact_id as \"MedStaffFact_id\",
                DeathSvid_PolFio as \"DeathSvid_PolFio\",
                DeathSvid_TraumaDateStr as \"DeathSvid_TraumaDateStr\",
                DeathSvid_IsActual as \"DeathSvid_IsActual\",
                DeathSvid_IsNoPlace as \"DeathSvid_IsNoPlace\",
                DeathSvid_isBirthDate as \"DeathSvid_isBirthDate\",
                DeathSvid_IsNoDeathTime as \"DeathSvid_IsNoDeathTime\",
                DeathSvid_IsNoAccidentTime as \"DeathSvid_IsNoAccidentTime\",
                DeathSvid_StacDate as \"DeathSvid_StacDate\",
                Diag_sid as \"Diag_sid\",
                DeathSvid_IsPrimDiagIID as \"DeathSvid_IsPrimDiagIID\",
                DeathSvid_IsPrimDiagTID as \"DeathSvid_IsPrimDiagTID\",
                DeathSvid_IsPrimDiagMID as \"DeathSvid_IsPrimDiagMID\",
                DeathSvid_IsPrimDiagEID as \"DeathSvid_IsPrimDiagEID\",
                MedStaffFact_did as \"MedStaffFact_did\",
                DeathSvid_TimePeriod as \"DeathSvid_TimePeriod\",
                Okei_id as \"Okei_id\",
                DeathSvid_TimePeriodPat as \"DeathSvid_TimePeriodPat\",
                Okei_patid as \"Okei_patid\",
                DeathSvid_TimePeriodDom as \"DeathSvid_TimePeriodDom\",
                Okei_domid as \"Okei_domid\",
                DeathSvid_TimePeriodExt as \"DeathSvid_TimePeriodExt\",
                Okei_extid as \"Okei_extid\",
                DeathSvid_TimePeriodImp as \"DeathSvid_TimePeriodImp\",
                Okei_impid as \"Okei_impid\",
                DeathSvid_id as \"DeathSvid_id\",
                Lpu_id as \"Lpu_id\",
                LpuSection_id as \"LpuSection_id\",
                Person_id as \"Person_id\",
                Person_mid as \"Person_mid\",
                Person_rid as \"Person_rid\",
                MedPersonal_id as \"MedPersonal_id\",
                ds.DeathSvidType_id as \"DeathSvidType_id\",
                ReceptType_id as \"ReceptType_id\",
                DeathCause_id as \"DeathCause_id\",
				DeathFamilyStatus_id as \"DeathFamilyStatus_id\",
				dst.DeathSvidType_Name as \"DeathSvidType_Name\"				
			from
				v_DeathSvid ds 
				left join v_DeathSvidType dst  on dst.DeathSvidType_id = ds.DeathSvidType_id
			where 
				{$filters}
			{$orderby}
			limit 100
		";
		
		$result = $this->db->query($query, $data);
		
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 *	Получение списка мед. свидетельств
	 */
	function loadMedSvidListGrid($data, $svid_type) {
		$query = "";
		$queryParams = array();
		$fields = "";
		$join = "";
		$where = "";

		if ($svid_type == "birth") {
			if (isset($data['Lpu_id']) && $data['Lpu_id'] > 0) $where .= " AND d.Lpu_id = ".$data['Lpu_id'];
			if (isset($data['IsBad']) && $data['IsBad'] > 0) $where .= " AND d.BirthSvid_isBad = ".$data['IsBad'];
			if (isset($data['Start_Date']) && strlen($data['Start_Date']) > 0) $where .= " AND cast(d.BirthSvid_insDT as date) >= '".substr($data['Start_Date'], 0, strpos($data['Start_Date'],"T"))."'";
			if (isset($data['End_Date']) && strlen($data['End_Date']) > 0) $where .= " AND cast(d.BirthSvid_insDT as date) <= '".substr($data['End_Date'], 0, strpos($data['End_Date'],"T"))."'";
			if (isset($data['Give_Date'][0]) && strlen($data['Give_Date'][0]) > 0) $where .= " and d.BirthSvid_GiveDate >= cast('".$data['Give_Date'][0]."' as date)";
			if (isset($data['Give_Date'][1]) && strlen($data['Give_Date'][1]) > 0) $where .= " and d.BirthSvid_GiveDate <= cast('".$data['Give_Date'][1]."' as date)";
			if (isset($data['Person_Surname']) && strlen($data['Person_Surname']) > 0) $where .= " AND p.Person_Surname iLIKE '".rtrim($data['Person_Surname'])."%'";

			if (isset($data['Person_Firname']) && strlen($data['Person_Firname']) > 0) $where .= " AND p.Person_Firname iLIKE '".rtrim($data['Person_Firname'])."%'";

			if (isset($data['Person_Secname']) && strlen($data['Person_Secname']) > 0) $where .= " AND p.Person_Secname iLIKE '".rtrim($data['Person_Secname'])."%'";


            if (isset($data['Child_BirthDate'][0]) && strlen($data['Child_BirthDate'][0]) > 0) $where .= " and cast(d.BirthSvid_BirthDT as date) >= cast('".$data['Child_BirthDate'][0]."' as date)";
            if (isset($data['Child_BirthDate'][1]) && strlen($data['Child_BirthDate'][1]) > 0) $where .= " and cast(d.BirthSvid_BirthDT as date)<= cast('".$data['Child_BirthDate'][1]."' as date)";
            if (isset($data['Child_Surname']) && strlen($data['Child_Surname']) > 0) $where .= " AND d.BirthSvid_ChildFamil iLIKE '".rtrim($data['Child_Surname'])."%'";

            if (isset($data['Sex_id']) && strlen($data['Sex_id']) > 0) $where .= " AND d.Sex_id = ".$data['Sex_id'];

			if (getRegionNick() == 'kz') {
				$fields .= ",case when OSL.Object_sid is null then 0 else 1 end as \"BirthSvid_isInRpn\"";
				$join .= " LEFT JOIN LATERAL(

						select Object_sid
						from v_ObjectSynchronLog OSL 

						where ObjectSynchronLogService_id = 2 and OSL.Object_id = d.BirthSvid_id
						limit 1
				    ) OSL ON true";
			} else {
				$fields .= ",0 as \"BirthSvid_isInRpn\"";
			}

            //var_dump($where);die;
			$query = "
				select
					-- select
					d.BirthSvid_id as \"BirthSvid_id\",
					d.BirthSvid_isBad as \"BirthSvid_isBad\",
					d.BirthSvid_Ser as \"BirthSvid_Ser\",
					d.BirthSvid_Num as \"BirthSvid_Num\",
					to_char(d.BirthSvid_RcpDate, 'DD.MM.YYYY') as \"BirthSvid_RcpDate\",
					COALESCE(rtrim(p.Person_Surname)||' ','') || COALESCE(rtrim(p.Person_Firname)||' ','') || COALESCE(rtrim(p.Person_Secname),'') as \"Person_FIO\",
					to_char(p.Person_Birthday, 'DD.MM.YYYY') as \"Person_Birthday\",
					COALESCE(d.BirthSvid_ChildFamil,'') as \"BirthSvid_ChildFamil\",
					to_char(d.BirthSvid_BirthDT, 'DD.MM.YYYY') as \"BirthSvid_BirthChildDate\",
					CS.Sex_Name as \"Child_Sex\",
					L.Lpu_Nick as \"Lpu_Nick\",
					to_char(d.BirthSvid_signDT, 'DD.MM.YYYY') as \"BirthSvid_signDT\",
					d.BirthSvid_IsSigned as \"BirthSvid_IsSigned\",
					COALESCE(rtrim(MP.Person_Surname)||' ','') || COALESCE(rtrim(MP.Person_Firname)||' ','') || COALESCE(rtrim(MP.Person_Secname),'') as \"MedPersonal_FIO\"
					{$fields}
					-- end select
				from
					-- from
					BirthSvid d 
					left join v_Person_ER p  on p.Person_id = d.Person_Id
					left join v_Lpu L  on L.Lpu_id = d.Lpu_id
					left join v_Sex CS  on CS.Sex_id = d.Sex_id
					LEFT JOIN LATERAL (
						select Person_Surname, Person_Firname, Person_Secname
						from v_MedPersonal 
						where MedPersonal_id = d.MedPersonal_id
							and Lpu_id = d.Lpu_id
					    limit 1
					) MP ON true
					{$join}
					-- end from
				where 
					-- where
					(1=1) {$where}
					-- end where
				order by 
					-- order by
					d.BirthSvid_id DESC
					-- end order by
			";
		}

		if (in_array($svid_type, array('death', 'pntdeath')) && !empty($data['Lpu_id'])) {

			//определяем тип МО
			$LpuType_Code = $this->getFirstResultFromQuery("
				select
					LT.LpuType_Code as \"LpuType_Code\"
				from
					v_Lpu L 

					left join v_LpuType LT  on LT.LpuType_id = L.LpuType_id

				where
					L.Lpu_id = :Lpu_id
				limit 1", array('Lpu_id' => $data['Lpu_id'])

			);
		}

		if (in_array($svid_type, array('death', /*'pntdeath'*/))) {

			if (!empty($data['DeathCause']) && in_array($data['DeathCause'], array('Diag_iid','Diag_tid','Diag_mid','Diag_eid','Diag_oid',))) {
				$join .= " left join v_Diag Dg  on d.{$data['DeathCause']} = Dg.Diag_id ";


				if (!empty($data['Diag_Code_From'])) {
					$where .= "and Dg.Diag_Code >= :Diag_Code_From ";
				}

				if (!empty($data['Diag_Code_To'])) {
					$where .= "and Dg.Diag_Code <= :Diag_Code_To ";
				}

				if (empty($data['Diag_Code_From']) && empty($data['Diag_Code_To'])) {
					$where .= "and d.{$data['DeathCause']} is not null ";
				}

				$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
				$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
			} else {
				if (!empty($data['Diag_Code_From']) || !empty($data['Diag_Code_To'])) {
					$join .= " left join v_Diag doid  on doid.Diag_id = d.Diag_oid ";

				}

				if (!empty($data['Diag_Code_From']) && empty($data['Diag_Code_To'])) {
					$where .= "and (dtid.Diag_Code >= :Diag_Code_From or deid.Diag_Code >= :Diag_Code_From or doid.Diag_Code >= :Diag_Code_From or dmid.Diag_Code >= :Diag_Code_From or diid.Diag_Code >= :Diag_Code_From)";
					$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
				} else if (!empty($data['Diag_Code_To']) && empty($data['Diag_Code_From'])) {
					$where .= "and (dtid.Diag_Code <= :Diag_Code_To or deid.Diag_Code <= :Diag_Code_To or doid.Diag_Code <= :Diag_Code_To or dmid.Diag_Code <= :Diag_Code_To or diid.Diag_Code <= :Diag_Code_To)";
					$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
				} else if (!empty($data['Diag_Code_From']) && !empty($data['Diag_Code_To'])) {
					$where .= "and (
						(dtid.Diag_Code >= :Diag_Code_From and dtid.Diag_Code <= :Diag_Code_To)
						or (deid.Diag_Code >= :Diag_Code_From and deid.Diag_Code <= :Diag_Code_To)
						or (doid.Diag_Code >= :Diag_Code_From and doid.Diag_Code <= :Diag_Code_To)
						or (dmid.Diag_Code >= :Diag_Code_From and dmid.Diag_Code <= :Diag_Code_To)
						or (diid.Diag_Code >= :Diag_Code_From and diid.Diag_Code <= :Diag_Code_To)
					)";

					$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
					$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
				}
			}
		}

		if ($svid_type == "death") {
			if (!empty($data['IsBad'])) {
				$where .= " AND d.DeathSvid_isBad = :IsBad";
				$queryParams['IsBad'] = $data['IsBad'];
			}

			if (!empty($data['IsActual'])) {
				$where .= " AND COALESCE(d.DeathSvid_IsActual, 2) = :IsActual";

				$queryParams['IsActual'] = $data['IsActual'];
			}

			if (!empty($data['Start_Date'])) {
				$where .= " and cast(d.DeathSvid_insDT as date) >= :Start_Date";
				$queryParams['Start_Date'] = $data['Start_Date'];
			}

			if (!empty($data['End_Date'])) {
				$where .= " and cast(d.DeathSvid_insDT as date) <= :End_Date";
				$queryParams['End_Date'] = $data['End_Date'];
			}

			if (!empty($data['Death_Date'][0])) {
				$where .= " and cast(d.DeathSvid_DeathDate as date) >= cast(:Death_Date0 as date)";
				$queryParams['Death_Date0'] = $data['Death_Date'][0];
			}

			if (!empty($data['Death_Date'][1])) {
				$where .= " and cast(d.DeathSvid_DeathDate as date) <= cast(:Death_Date1 as date)";
				$queryParams['Death_Date1'] = $data['Death_Date'][1];
			}

			if (!empty($data['Birth_Date'][0])) {
				$where .= " and p.Person_Birthday >= cast(:Birth_Date0 as date)";
				$queryParams['Birth_Date0'] = $data['Birth_Date'][0];
			}

			if (!empty($data['Birth_Date'][1])) {
				$where .= " and p.Person_Birthday <= cast(:Birth_Date1 as date)";
				$queryParams['Birth_Date1'] = $data['Birth_Date'][1];
			}

			if (!empty($data['Give_Date'][0])) {
				$where .= " and d.DeathSvid_GiveDate >= cast(:Give_Date0 as date)";
				$queryParams['Give_Date0'] = $data['Give_Date'][0];
			}

			if (!empty($data['Give_Date'][1])) {
				$where .= " and d.DeathSvid_GiveDate <= cast(:Give_Date1 as date)";
				$queryParams['Give_Date1'] = $data['Give_Date'][1];
			}

			if (!empty($data['Person_Surname'])) {
				$where .= " AND p.Person_Surname iLIKE :Person_Surname || '%'";

				$queryParams['Person_Surname'] = $data['Person_Surname'];
			}

			if (!empty($data['Person_Firname'])) {
				$where .= " AND p.Person_Firname iLIKE :Person_Firname || '%'";

				$queryParams['Person_Firname'] = $data['Person_Firname'];
			}

			if (!empty($data['Person_Secname'])) {
				$where .= " AND p.Person_Secname iLIKE :Person_Secname || '%'";

				$queryParams['Person_Secname'] = $data['Person_Secname'];
			}

			if (!empty($data['Svid_Num'])) {
				$where .= " AND d.DeathSvid_Num iLIKE :Svid_Num || '%'";

				$queryParams['Svid_Num'] = $data['Svid_Num'];
			}
			
			if (!empty($data['ReceptType_id'])) {
				$where .= " AND d.ReceptType_id = :ReceptType_id";
				$queryParams['ReceptType_id'] = $data['ReceptType_id'];
			}

			if (!empty($data['Lpu_id'])) {
				$lpuJoin = " left join v_Lpu L  on L.Lpu_id = d.Lpu_id ";

				if (!empty($data['viewMode']) && $data['viewMode'] == 2) {
					// режим по прикреплённому населению
					$filterlr = "";
					if (!empty($data['LpuRegion_id'])) {
						$filterlr .= " and COALESCE(LpuRegion_id,-1) = :LpuRegion_id";

						$queryParams['LpuRegion_id'] = $data['LpuRegion_id'];
					}
					if (isset($data['MedPersonal_id']) && ($data['MedPersonal_id'] > 0)) {

						$queryParams['MedPersonal_id'] = $data['MedPersonal_id'];

						$join .= "
						INNER JOIN LATERAL(

							select 
								PersonCard_id
							from
								v_PersonCard_all PC 

								inner join v_MedStaffRegion MedStaffRegion  on MedStaffRegion.LpuRegion_id = PC.LpuRegion_id and MedStaffRegion.MedPersonal_id = :MedPersonal_id

								left join v_MedStaffFact msf on msf.MedStaffFact_id = MedStaffRegion.MedStaffFact_id and MedStaffRegion.Lpu_id = msf.Lpu_id
								left join persis.Post p  on p.id = msf.Post_id

							where
								PC.Person_id = d.Person_id
								and PC.LpuAttachType_id = 1
								and MedStaffRegion.MedStaffRegion_isMain = 2 -- основной врач на участке
								and p.code in (74,47,40,117,111)
								and PersonCard_begDate <= COALESCE(d.DeathSvid_DeathDate, d.DeathSvid_GiveDate)

								and (CAST(PC.PersonCard_endDate AS DATE) >= CAST(COALESCE(d.DeathSvid_DeathDate, d.DeathSvid_GiveDate) AS date) OR PersonCard_endDate IS NULL)

								and PC.Lpu_id = :Lpu_id
								{$filterlr}
						    limit 1
						) pcs ON true
						-- left join v_PersonCardState pcs  on pcs.Person_id = d.Person_id and pcs.LpuAttachType_id = 1

					";
					} else {
						$join .= "
						INNER JOIN LATERAL(

							select 
								PersonCard_id
							from
								v_PersonCard_all  

							where
								Person_id = d.Person_id
								and LpuAttachType_id = 1
								and PersonCard_begDate <= COALESCE(d.DeathSvid_DeathDate, d.DeathSvid_GiveDate)

								and (CAST(PersonCard_endDate AS DATE) >= CAST(COALESCE(d.DeathSvid_DeathDate, d.DeathSvid_GiveDate) AS date) OR PersonCard_endDate IS NULL)

								and Lpu_id = :Lpu_id
								{$filterlr}
							limit 1
						) pcs ON true
						-- left join v_PersonCardState pcs  on pcs.Person_id = d.Person_id and pcs.LpuAttachType_id = 1

					";
									}
					$queryParams['Lpu_id'] = $data['Lpu_id'];

					if (getRegionNick() == 'ufa' && (empty($LpuType_Code) || $LpuType_Code != 111)){
						$where .= " AND LT.LpuType_Code != 111";
					}

				} else {
					// режим выписанные в МО
					$where .= " AND d.Lpu_id = :Lpu_id";
					$queryParams['Lpu_id'] = $data['Lpu_id'];
				}
			} else {
				$lpuJoin = " inner join v_Lpu L  on L.Lpu_id = d.Lpu_id ";

			}



			$query = "
				select
					-- select
					d.DeathSvid_id as \"DeathSvid_id\",
					d.DeathSvid_IsBad as \"DeathSvid_IsBad\",
					COALESCE(d.DeathSvid_IsActual, 2) as \"DeathSvid_IsActual\",

					d.DeathSvid_IsLose as \"DeathSvid_IsLose\",
					d.DeathSvid_Ser as \"DeathSvid_Ser\",
					d.DeathSvid_Num as \"DeathSvid_Num\",
					to_char(d.DeathSvid_GiveDate, 'DD.MM.YYYY') as \"DeathSvid_GiveDate\",

					COALESCE(rtrim(p.Person_Surname)||' ','') || COALESCE(rtrim(p.Person_Firname)||' ','') || COALESCE(rtrim(p.Person_Secname),'') as \"Person_FIO\",

					COALESCE(to_char(p.Person_Birthday, 'DD.MM.YYYY'), d.DeathSvid_BirthDateStr) as \"Person_Birthday\",


					dbo.Age2(p.Person_BirthDay, dbo.tzGetDate()) as \"Person_Age\",
					COALESCE(to_char(d.DeathSvid_DeathDate, 'DD.MM.YYYY'), d.DeathSvid_DeathDateStr) as \"DeathSvid_DeathDate\",
					to_char(d.DeathSvid_signDT, 'DD.MM.YYYY') as \"DeathSvid_signDT\",
					d.DeathSvid_IsSigned as \"DeathSvid_IsSigned\",

					L.Lpu_Nick as \"Lpu_Nick\",
					LT.LpuType_Code as \"LpuType_Code\",
					d.Lpu_id as \"Lpu_id\",
					COALESCE(rtrim(MP.Person_Surname)||' ','') || COALESCE(rtrim(MP.Person_Firname)||' ','') || COALESCE(rtrim(MP.Person_Secname),'') as \"MedPersonal_FIO\",

					dst.DeathSvidType_Name as \"DeathSvidType_Name\",
					d.DeathSvidType_id as \"DeathSvidType_id\",
					d.Person_rid as \"Person_rid\",
					d.Person_id as \"Person_id\",
					d.DeathSvid_IsDuplicate as \"DeathSvid_IsDuplicate\",
					COALESCE(diid.Diag_Code || '. ','') || diid.Diag_Name as \"Diag_iidName\",

					COALESCE(dmid.Diag_Code || '. ','') || dmid.Diag_Name as \"Diag_midName\",

                    COALESCE(deid.Diag_Code || '. ','') || deid.Diag_Name as \"Diag_eidName\",

					COALESCE(dtid.Diag_Code || '. ','') || dtid.Diag_Name as \"Diag_tidName\",

					case when PI.PersonInfo_IsParsDeath = 2 then 1 else 0 end as \"PersonInfo_IsParsDeath\",
					case when PI.PersonInfo_IsSetDeath = 2 then 1 else 0 end as \"PersonInfo_IsSetDeath\",
					d.ReceptType_id as \"ReceptType_id\"
					-- end select
				from
					-- from
					v_DeathSvid d 

					left join v_Diag diid  on diid.Diag_id = d.Diag_iid

					left join v_Diag dmid  on dmid.Diag_id = d.Diag_mid

					left join v_Diag deid  on deid.Diag_id = d.Diag_eid

					left join v_Diag dtid  on dtid.Diag_id = d.Diag_tid

					left join v_DeathSvidType dst  on dst.DeathSvidType_id = d.DeathSvidType_id

					LEFT JOIN LATERAL (

						select 
							p.Person_Secname,
							p.Person_Surname,
							p.Person_Firname,
							p.Person_Birthday
						from v_Person_ER p 

						where p.Person_id = d.Person_Id
                        limit 1
					)p ON true
					{$lpuJoin}
					left join v_LpuType LT  on L.LpuType_id = LT.LpuType_id

					LEFT JOIN LATERAL( 

						select 
							PInf.PersonInfo_IsParsDeath,
							PInf.PersonInfo_IsSetDeath
						from v_PersonInfo PInf 

						where PInf.Person_Id = d.Person_Id
                        limit 1
					) PI ON true
					LEFT JOIN LATERAL (

						select 
							Person_Surname,
							Person_Firname,
							Person_Secname
						from
							v_MedPersonal 

						where
							MedPersonal_id = d.MedPersonal_id
							and Lpu_id = d.Lpu_id
                        limit 1
					) MP ON true
					{$join}
					-- end from
				where 
					-- where
					(1=1) 
                    ".$where." 
					-- end where
				order by 
					-- order by
					d.DeathSvid_id DESC
					-- end order by
			";
		}
		
		if ($svid_type == "pntdeath") {
			if (!empty($data['IsBad'])) {
				$where .= " AND d.PntDeathSvid_isBad = :IsBad";
				$queryParams['IsBad'] = $data['IsBad'];
			}

			if (!empty($data['IsActual'])) {
				$where .= " AND COALESCE(d.PntDeathSvid_IsActual, 2) = :IsActual";

				$queryParams['IsActual'] = $data['IsActual'];
			}

			if (!empty($data['Start_Date'])) {
				$where .= " and cast(d.PntDeathSvid_insDT as date) >= :Start_Date";
				$queryParams['Start_Date'] = $data['Start_Date'];
			}

			if (!empty($data['End_Date'])) {
				$where .= " and cast(d.PntDeathSvid_insDT as date) <= :End_Date";
				$queryParams['End_Date'] = $data['End_Date'];
			}

			if (!empty($data['Death_Date'][0])) {
				$where .= " and cast(d.PntDeathSvid_DeathDate as date) >= cast(:Death_Date0 as date)";
				$queryParams['Death_Date0'] = $data['Death_Date'][0];
			}

			if (!empty($data['Death_Date'][1])) {
				$where .= " and cast(d.PntDeathSvid_DeathDate as date) <= cast(:Death_Date1 as date)";
				$queryParams['Death_Date1'] = $data['Death_Date'][1];
			}

			if (!empty($data['Child_BirthDate'][0])) {
				$where .= " and cast(d.PntDeathSvid_ChildBirthDT as date) >= cast(:Child_BirthDate0 as date)";
				$queryParams['Child_BirthDate0'] = $data['Child_BirthDate'][0];
			}

			if (!empty($data['Child_BirthDate'][1])) {
				$where .= " and cast(d.PntDeathSvid_ChildBirthDT as date) <= cast(:Child_BirthDate1 as date)";
				$queryParams['Child_BirthDate1'] = $data['Child_BirthDate'][1];
			}

			if (!empty($data['Give_Date'][0])) {
				$where .= " and d.PntDeathSvid_GiveDate >= cast(:Give_Date0 as date)";
				$queryParams['Give_Date0'] = $data['Give_Date'][0];
			}

			if (!empty($data['Give_Date'][1])) {
				$where .= " and d.PntDeathSvid_GiveDate <= cast(:Give_Date1 as date)";
				$queryParams['Give_Date1'] = $data['Give_Date'][1];
			}

			if (!empty($data['Person_Surname'])) {
				$where .= " AND p.Person_Surname iLIKE :Person_Surname || '%'";

				$queryParams['Person_Surname'] = $data['Person_Surname'];
			}

			if (!empty($data['Person_Firname'])) {
				$where .= " AND p.Person_Firname iLIKE :Person_Firname || '%'";

				$queryParams['Person_Firname'] = $data['Person_Firname'];
			}

			if (!empty($data['Person_Secname'])) {
				$where .= " AND p.Person_Secname iLIKE :Person_Secname || '%'";

				$queryParams['Person_Secname'] = $data['Person_Secname'];
			}

			if (!empty($data['Sex_id'])) {
				$where .= " AND d.Sex_id = :Sex_id";
				$queryParams['Sex_id'] = $data['Sex_id'];
			}

			if (!empty($data['Child_Surname'])) {
				$where .= " AND d.PntDeathSvid_ChildFio iLIKE :Child_Surname || '%'";

				$queryParams['Child_Surname'] = $data['Child_Surname'];
			}

			if (!empty($data['Svid_Num'])) {
				$where .= " AND d.PntDeathSvid_Num iLIKE :Svid_Num || '%'";

				$queryParams['Svid_Num'] = $data['Svid_Num'];
			}

			if (!empty($data['Lpu_id'])) {
				if (!empty($data['viewMode']) && $data['viewMode'] == 2) {
					// режим по прикреплённому населению
					$join .= " left join v_PersonCardState pcs  on pcs.Person_id = d.Person_id and pcs.LpuAttachType_id = 1

						left join v_LpuType LT  on L.LpuType_id = LT.LpuType_id";

					$where .= " AND pcs.Lpu_id = " . $data['Lpu_id'];

					if (getRegionNick() == 'ufa' && (empty($LpuType_Code) || $LpuType_Code != 111)){
						$where .= " AND LT.LpuType_Code != 111";
					}
				} else {
					// режим выписанные в МО
					$where .= " AND d.Lpu_id = " . $data['Lpu_id'];
				}
			}
			
			if (!empty($data['ReceptType_id'])) {
				$where .= " AND d.ReceptType_id = :ReceptType_id";
				$queryParams['ReceptType_id'] = $data['ReceptType_id'];
			}

            $query = "
				select
					-- select
					d.PntDeathSvid_id as \"PntDeathSvid_id\",
					d.PntDeathSvid_IsBad as \"PntDeathSvid_IsBad\",
					COALESCE(d.PntDeathSvid_IsActual, 2) as \"PntDeathSvid_IsActual\",

					d.PntDeathSvid_IsLose as \"PntDeathSvid_IsLose\",
					d.PntDeathSvid_Ser as \"PntDeathSvid_Ser\",
					d.PntDeathSvid_Num as \"PntDeathSvid_Num\",
					to_char(d.PntDeathSvid_GiveDate, 'DD.MM.YYYY') as \"PntDeathSvid_GiveDate\",

					COALESCE(to_char(d.PntDeathSvid_DeathDate, 'DD.MM.YYYY'), d.PntDeathSvid_DeathDateStr) as \"PntDeathSvid_DeathDate\",


					COALESCE(to_char(d.PntDeathSvid_ChildBirthDT, 'DD.MM.YYYY'), d.PntDeathSvid_BirthDateStr) as \"PntDeathSvid_BirthDate\",


					COALESCE(d.PntDeathSvid_ChildFio,'') as \"PntDeathSvid_ChildFio\",

					CS.Sex_Name as \"Child_Sex\",
					COALESCE(rtrim(p.Person_Surname)||' ','') || COALESCE(rtrim(p.Person_Firname)||' ','') || COALESCE(rtrim(p.Person_Secname),'') as \"Person_FIO\",

					to_char(p.Person_Birthday, 'DD.MM.YYYY') as \"Person_Birthday\",

					L.Lpu_Nick as \"Lpu_Nick\",
					d.Lpu_id as \"Lpu_id\",
					COALESCE(rtrim(MP.Person_Surname)||' ','') || COALESCE(rtrim(MP.Person_Firname)||' ','') || COALESCE(rtrim(MP.Person_Secname),'') as \"MedPersonal_FIO\",

					dst.DeathSvidType_Name as \"DeathSvidType_Name\",
					d.DeathSvidType_id as \"DeathSvidType_id\",
					d.Person_rid as \"Person_rid\",
					d.Person_id as \"Person_id\",
					d.PntDeathSvid_IsDuplicate as \"PntDeathSvid_IsDuplicate\",
					case when PI.PersonInfo_IsParsDeath = 2 then 1 else 0 end as \"PersonInfo_IsParsDeath\",
					case when PI.PersonInfo_IsSetDeath = 2 then 1 else 0 end as \"PersonInfo_IsSetDeath\"
					-- end select
				from
					-- from
					v_PntDeathSvid d 

					left join v_DeathSvidType dst  on dst.DeathSvidType_id = d.DeathSvidType_id

					left join v_Person_ER p  on p.Person_id = d.Person_Id

					left join v_Lpu L  on L.Lpu_id = d.Lpu_id

					left join v_Sex CS  on CS.Sex_id = d.Sex_id

					left join v_PersonInfo PI  on PI.Person_Id = d.Person_Id

					LEFT JOIN LATERAL (

						select 
							Person_Surname,
							Person_Firname,
							Person_Secname
						from
							v_MedPersonal 

						where
							MedPersonal_id = d.MedPersonal_id
							and Lpu_id = d.Lpu_id
                        limit 1
					) MP ON true
					{$join}
					-- end from
				where 
					-- where
					(1=1) 
                    ".$where." 
					-- end where
				order by 
					-- order by
					d.PntDeathSvid_id DESC
					-- end order by
			";
		}

		//echo getDebugSQL($query, $queryParams);die;
		$response = $this->getPagingResponse($query, $queryParams, $data['start'], $data['limit'], true);
		if (in_array($svid_type, array("birth", "death"))) {
			switch ($svid_type) {
				case 'birth':
					$tableName = 'BirthSvid';
					break;
				case 'death':
					$tableName = 'DeathSvid';
					break;
			}
			$EMDRegistry_ObjectIDs = [];
			foreach ($response['data'] as $one) {
				if (!empty($one[$tableName . '_id']) && $one[$tableName . '_IsSigned'] == 2 && !in_array($one[$tableName . '_id'], $EMDRegistry_ObjectIDs)) {
					$EMDRegistry_ObjectIDs[] = $one[$tableName . '_id'];
				}
			}

			$isEMDEnabled = $this->config->item('EMD_ENABLE');
			if(!empty($EMDRegistry_ObjectIDs) && !empty($isEMDEnabled)){
				$this->load->model('EMD_model');
				$MedStaffFact_id = $data['session']['CurMedStaffFact_id'] ?? null;
				if (
					empty($MedStaffFact_id)
					&& !empty($data['session']['CurMedService_id'])
					&& !empty($data['session']['medpersonal_id'])
				) {
					// получаем данные по мед. работнику службы
					$resp_ms = $this->queryResult("
						select
							msf.MedStaffFact_id as \"MedStaffFact_id\"
						from v_MedService ms
						inner join v_MedStaffFact msf on msf.LpuSection_id = ms.LpuSection_id
						where 
							ms.MedService_id = :MedService_id
							and msf.MedPersonal_id = :MedPersonal_id
						limit 1
					", array(
						'MedService_id' => $data['session']['CurMedService_id'],
						'MedPersonal_id' => $data['session']['medpersonal_id']
					));
	
					if (!empty($resp_ms[0]['MedStaffFact_id'])) {
						$MedStaffFact_id = $resp_ms[0]['MedStaffFact_id'];
					}
				}
	
				$signStatus = $this->EMD_model->getSignStatus([
					'EMDRegistry_ObjectName' => $tableName,
					'EMDRegistry_ObjectIDs' => $EMDRegistry_ObjectIDs,
					'MedStaffFact_id' => $MedStaffFact_id
				]);
				foreach ($response['data'] as $key => $one) {
					if (!empty($one[$tableName . '_id']) && $one[$tableName . '_IsSigned'] == 2) {
						if (isset($signStatus[$one[$tableName . '_id']])) {
							$response['data'][$key][$tableName . '_SignCount'] = $signStatus[$one[$tableName . '_id']]['signcount'];
							$response['data'][$key][$tableName . '_MinSignCount'] = $signStatus[$one[$tableName . '_id']]['minsigncount'];
							$response['data'][$key][$tableName . '_IsSigned'] = $signStatus[$one[$tableName . '_id']]['signed'];
						} else {
							$response['data'][$key][$tableName . '_SignCount'] = 0;
							$response['data'][$key][$tableName . '_MinSignCount'] = 0;
						}
					}
				}
			}
		}
		return $response;
	}

    /**
     * Получение списка номеров лицензий
     */
    function getLpuLicenceList($data){
        $queryParams = array();
        $and = '';
        if(isset($data['svidDate'])){
            $and .= " and LpuLicence_begDate <= :svidDate and COALESCE(LpuLicence_endDate, '2099-01-01')>= :svidDate";

            $queryParams['svidDate'] = $data['svidDate'];
        }

        $addLpuFilter = "";
        if(isset($data['fromMZ']) && $data['fromMZ'] == '2')
		{
			$addLpuFilter = "";
		}
		else
		{
			if(isset($data['Lpu_id'])){
	            $addLpuFilter .= ' and Lpu_id = :Lpu_id';
	            $queryParams['Lpu_id'] = $data['Lpu_id'];
        	}
		}
        /*if(isset($data['Lpu_id'])){
            $and .= ' and Lpu_id = :Lpu_id';
            $queryParams['Lpu_id'] = $data['Lpu_id'];
        }*/
        $query = "
                select 
                    Server_id as \"Server_id\",
                    LpuLicence_id as \"LpuLicence_id\",
                    Lpu_id as \"Lpu_id\",
                    VidDeat_id as \"VidDeat_id\",
                    LpuLicence_Ser as \"LpuLicence_Ser\",
                    LpuLicence_Num as \"LpuLicence_Num\",
                    LpuLicence_setDate as \"LpuLicence_setDate\",
                    LpuLicence_RegNum as \"LpuLicence_RegNum\",
                    KLCountry_id as \"KLCountry_id\",
                    KLRgn_id as \"KLRgn_id\",
                    KLSubRgn_id as \"KLSubRgn_id\",
                    KLCity_id as \"KLCity_id\",
                    KLTown_id as \"KLTown_id\",
                    LpuLicence_begDate as \"LpuLicence_begDate\",
                    LpuLicence_endDate as \"LpuLicence_endDate\",
                    pmUser_insID as \"pmUser_insID\",
                    pmUser_updID as \"pmUser_updID\",
                    LpuLicence_insDT as \"LpuLicence_insDT\",
                    LpuLicence_updDT as \"LpuLicence_updDT\",
                    Org_id as \"Org_id\",
                    LicsOperation_id as \"LicsOperation_id\",
                    LpuLicence_OperDT as \"LpuLicence_OperDT\"
                from v_LpuLicence 

                where (1=1)".$and.$addLpuFilter;
        //echo getDebugSQL($query,$queryParams);die;
        $result = $this->db->query($query,$queryParams);
        if (is_object($result))
            return $result->result('array');
        else
            return false;
    }

	/**
	 *	Сохранение мед. свидетельства
	 */
	function saveMedSvid($data, $svid_type) {
		try {
			$this->db->trans_begin();

			$tab_name = "";
			$param_array = array();
			$sql_p_array = array();
			$query = "";
			$query_parts = "";
			
			if ($svid_type == 'birth') $tab_name = "BirthSvid";
			if ($svid_type == 'death') $tab_name = "DeathSvid";
			if ($svid_type == 'pntdeath') $tab_name = "PntDeathSvid";
			
			if ($svid_type == 'birth') {
				$param_array = array(
					'BirthSvid_pid' => 'i',
					'Server_id' => 'i',
					'Person_id' => 'i',
					'Person_cid' => 'i',
					'Person_rid' => 'i',
					'BirthSvid_Ser' => 's',
					'BirthSvid_Num' => 's',
					'MedPersonal_id' => 'i',
					'MedStaffFact_id' => 'i',
					'LpuSection_id' => 'i',
					'BirthMedPersonalType_id' => 'i',
					'ReceptType_id' => 'i',
					'BirthEducation_id' => 'i',
					'BirthSvid_BirthDT' => 'i',
					'BirthPlace_id' => 'i',
					'Sex_id' => 'i',
					'BirthSvid_Week' => 's',
					'BirthSvid_ChildCount' => 's',
					'BirthFamilyStatus_id' => 'i',
					'BirthSvid_RcpDocument' => 's',
					'BirthSvid_RcpDate' => 'dt',
					'BirthEmployment_id' => 'i',
					'BirthSpecialist_id' => 'i',
					'BirthSvid_ChildFamil' => 's',
					'BirthSvid_IsMnogoplod' => 'i',
					'BirthSvid_PlodIndex' => 's',
					'BirthSvid_PlodCount' => 's',
					'BirthSvid_IsFromMother' => 'i',
					'BirthSvid_Height' => 's',
					'BirthSvid_Mass' => 's',
					'Okei_mid' => 's',
					'BirthSvid_GiveDate' => 'dt',
					'BirthChildResult_id' => 'i',//далее расчетные и из сессии
					'Lpu_id' => 'i',
					'Address_rid' => 'i',
					'pmUser_id' => 'i',
					'BirthSvid_IsBad' => 'i',
					'DeputyKind_id' => 'i',
					'OrgHead_id' => 'i',
	                'LpuLicence_id' => 'i',
                    'BirthSvid_IsOtherMO' => 'i',
                    'Org_id' => 's',
                    'MedStaffFact_cid' => 'i'
				);
			
				if (!empty($data['BirthSvid_BirthDT_Date'])) {
					$data['BirthSvid_BirthDT'] = $data['BirthSvid_BirthDT_Date'];
					if (!empty($data['BirthSvid_BirthDT_Time'])) {
						$data['BirthSvid_BirthDT'] .= " ".$data['BirthSvid_BirthDT_Time'].":00";
					}
				}
				
				$addr = $this->SaveAddress($data, 'B');
				if ($addr > 0) $data['Address_rid'] = $addr;
			}
					
			if ($svid_type == 'death') {
				$param_array = array(
					'DeathSvid_pid' => 'i',
					'Server_id' => 'i', 
					'Person_id' => 'i',
					'Person_mid' => 'i',
					'Person_rid' => 'i',
					'DeathSvid_PolFio' => 's',
					'MedPersonal_id' => 'i',
					'MedStaffFact_id' => 'i',
					'LpuSection_id' => 'i',
					'DeathSvidType_id' => 'i',
					'DeathSvid_IsDuplicate' => 'i',
					'DeathSvid_IsLose' => 'i',
					'DeathSvid_IsActual' => 'i',
					'ReceptType_id' => 'i',
					'DeathCause_id' => 'i',
					'DeathFamilyStatus_id' => 'i',
					'DeathPlace_id' => 'i',
					'DeathEducation_id' => 'i',
					'DeathTrauma_id' => 'i',
					'DeathSetType_id' => 'i',
					'DeathSetCause_id' => 'i',
					'DeathWomanType_id' => 'i',
					'DeathEmployment_id' => 'i',
					'DtpDeathTime_id' => 'i',
					'ChildTermType_id' => 'i',
					'Diag_iid' => 'i',
					'Diag_tid' => 'i',
					'Diag_mid' => 'i',
					'Diag_eid' => 'i',
					'Diag_oid' => 'i',
					'DeathSvid_IsNoPlace' => 'i',
	                'DeathSvid_isBirthDate' => 'i',
					'DeathSvid_TraumaDateStr' => 's',
					'DeathSvid_BirthDateStr' => 's',
					'DeathSvid_DeathDateStr' => 's',
					'DeathSvid_Ser' => 's',
					'DeathSvid_Num' => 's',
					'DeathSvid_OldSer' => 's',
					'DeathSvid_OldNum' => 's',
					'DeathSvid_DeathDate' => 'i',				
					'DeathSvid_IsNoDeathTime' => 'i',
	                'DeathSvid_IsNoAccidentTime' => 'i',
					//'DeathSvid_IsTerm' => 'i',
					'DeathSvid_Mass' => 'i',
					'DeathSvid_Month' => 'i',
					'DeathSvid_Day' => 'i',
					'DeathSvid_ChildCount' => 'i',
					'DeathSvid_TraumaDate' => 'i',
					'DeathSvid_TraumaDescr' => 's',
					'DeathSvid_Oper' => 's',
					'DeathSvid_PribPeriod' => 's',
					'DeathSvid_PribPeriodPat' => 's',
					'DeathSvid_PribPeriodDom' => 's',
					'DeathSvid_PribPeriodExt' => 's',
					'DeathSvid_PribPeriodImp' => 's',
					'DeathSvid_TimePeriod' => 's',
					'DeathSvid_TimePeriodPat' => 's',
					'DeathSvid_TimePeriodDom' => 's',
					'DeathSvid_TimePeriodExt' => 's',
					'DeathSvid_TimePeriodImp' => 's',
					'DeathSvidRelation_id' => 'i',
					'DeathSvid_RcpDate' => 'dt',
					'DeathSvid_GiveDate' => 'dt',
					'DeathSvid_OldGiveDate' => 'dt',
					'DeathSvid_RcpDocument' => 's', //далее расчетные и из сессии
					'Lpu_id' => 'i',
					'Address_bid' => 'i',
					'Address_did' => 'i',
					'pmUser_id' => 'i',
					'DeathSvid_IsBad' => 'i',
					'OrgHeadPost_id' => 'i',
					'Person_hid' => 'i',
					'DeathSvid_IsPrimDiagIID' => 'i',
					'DeathSvid_IsPrimDiagTID' => 'i',
					'DeathSvid_IsPrimDiagMID' => 'i',
					'DeathSvid_IsPrimDiagEID' => 'i',
					'Okei_id' => 'i',
					'Okei_patid' => 'i',
					'Okei_domid' => 'i',
					'Okei_extid' => 'i',
					'Okei_impid' => 'i',
					'MedStaffFact_did' => 'i',
					'MedPersonal_cid' => 'i',
					'DeathSvid_checkDate' => 'dt'
				);

				if (!empty($data['DeathSvid_IsNoPlace'])) {
					$data['DeathSvid_IsNoPlace'] = 2;
				} else {
					$data['DeathSvid_IsNoPlace'] = 1;
				}
	            if (!empty($data['DeathSvid_isBirthDate'])) {
	                $data['DeathSvid_isBirthDate'] = 2;
	            } else {
	                $data['DeathSvid_isBirthDate'] = 1;
	            }
	            if (!empty($data['DeathSvid_IsNoDeathTime'])) {
	                $data['DeathSvid_IsNoDeathTime'] = 2;
	            } else {
	                $data['DeathSvid_IsNoDeathTime'] = 1;
	            }
	            if (!empty($data['DeathSvid_IsNoAccidentTime'])) {
	                $data['DeathSvid_IsNoAccidentTime'] = 2;
	            } else {
	                $data['DeathSvid_IsNoAccidentTime'] = 1;
	            }
				$data['DeathSvid_IsPrimDiagIID'] = !empty($data['DeathSvid_IsPrimDiagIID']) ? 2 : 1;
				$data['DeathSvid_IsPrimDiagTID'] = !empty($data['DeathSvid_IsPrimDiagTID']) ? 2 : 1;
				$data['DeathSvid_IsPrimDiagMID'] = !empty($data['DeathSvid_IsPrimDiagMID']) ? 2 : 1;
				$data['DeathSvid_IsPrimDiagEID'] = !empty($data['DeathSvid_IsPrimDiagEID']) ? 2 : 1;
				if (!empty($data['DeathSvid_DeathDate_Date'])) {
					$data['DeathSvid_DeathDate'] = $data['DeathSvid_DeathDate_Date'];
					if ($data['DeathSvid_DeathDate_Time'] != "") {
						$data['DeathSvid_DeathDate'] .= " ".$data['DeathSvid_DeathDate_Time'].":00";
					}
				}

				if ( empty($data['DeathSvid_DeathDate']) && !$data['DeathSvid_IsUnknownDeathDate'] && empty($data['DeathSvid_DeathDateStr']) ) {
					$data['DeathSvid_DeathDate'] = $data['DeathSvid_GiveDate'];
				}
				else if ( $data['DeathSvid_IsUnknownDeathDate'] === true ) {
					$data['DeathSvid_DeathDate'] = null;
				}

				// проставить дату окончания региональной льготы (refs #6201)
				$querypp = "UPDATE PersonPrivilege SET PersonPrivilege_endDate = :endDate WHERE PersonPrivilege_endDate IS NULL AND Person_id = :Person_id AND exists((SELECT * FROM PrivilegeType  WHERE PrivilegeType_id = PersonPrivilege.PrivilegeType_id and ReceptFinance_id=2))";

				$this->db->query($querypp, array('Person_id' => $data['Person_id'], 'endDate' => (!empty($data['DeathSvid_DeathDate']) ? $data['DeathSvid_DeathDate'] : $data['DeathSvid_GiveDate'])));

				if (!empty($data['DeathSvid_TraumaDate_Date'])) {
					$data['DeathSvid_TraumaDate'] = $data['DeathSvid_TraumaDate_Date'];
					if ($data['DeathSvid_TraumaDate_Time'] != "") {
						$data['DeathSvid_TraumaDate'] .= " ".$data['DeathSvid_TraumaDate_Time'].":00";
					}
				}

				$addr = $this->SaveAddress($data, 'B');
				if ($addr > 0) $data['Address_bid'] = $addr;
				$addr = $this->SaveAddress($data, 'D');
				if ($addr > 0) $data['Address_did'] = $addr;
			}
			
			if ($svid_type == 'pntdeath') {
				$param_array = array(
					'Server_id' => 'i',				
					'Person_id' => 'i', 
					'Person_cid' => 'i',
					'Lpu_id' => 'i',
					'Person_rid' => 'i',
					'PntDeathSvid_BirthDateStr' => 's',
					'PntDeathSvid_DeathDateStr' => 's',
					'PntDeathSvid_Ser' => 's', 
					'PntDeathSvid_Num' => 's', 
					'PntDeathPeriod_id' => 'i', 
					'DeathSvidType_id' => 'i',
					'PntDeathSvid_IsDuplicate' => 'i',
					'PntDeathSvid_IsLose' => 'i',
					'PntDeathSvid_IsActual' => 'i',
					'PntDeathSvid_OldSer' => 's',
					'PntDeathSvid_OldNum' => 's', 
					'MedPersonal_id' => 'i',
					'MedStaffFact_id' => 'i',
					'LpuSection_id' => 'i',
					'PntDeathSvid_DeathDate' => 'i', 
					'Address_did' => 'i', 
					'ReceptType_id' => 'i', 
					'PntDeathSvid_ChildFio' => 's', 
					'PntDeathSvid_ChildBirthDT' => 'i', 
					'PntDeathSvid_PlodIndex' => 's', 
					'PntDeathSvid_PlodCount' => 's', 
					'PntDeathSvid_RcpDoc' => 's', 
					'PntDeathSvid_RcpDate' => 'dt', 
					'PntDeathFamilyStatus_id' => 'i', 
					'DeathEmployment_id' => 'i', 
					'PntDeathPlace_id' => 'i', 
					'PntDeathEducation_id' => 'i', 
					'Sex_id' => 'i', 
					'PntDeathSvid_ChildCount' => 's', 
					'PntDeathSvid_BirthCount' => 's', 
					'PntDeathGetBirth_id' => 'i', 
					'PntDeathTime_id' => 'i', 
					'PntDeathCause_id' => 'i', 
					'PntDeathSetType_id' => 'i', 
					'PntDeathSetCause_id' => 'i', 
					'Diag_iid' => 'i', 
					'Diag_eid' => 'i', 
					'Diag_mid' => 'i', 
					'Diag_tid' => 'i', 
					'Diag_oid' => 'i',
					'PntDeathSvid_IsNoPlace' => 'i',
					'PntDeathSvid_Mass' => 's', 
					'PntDeathSvid_Height' => 's', 
					'PntDeathSvid_IsMnogoplod' => 'i', 
					'PntDeathSvid_GiveDate' => 'dt', 
					'PntDeathSvid_OldGiveDate' => 'dt', 
					'pmUser_id' => 'i',
					'PntDeathSvid_IsBad' => 'i',
					'DeputyKind_id' => 'i',
					'PntDeathSvid_ActNumber' => 's',
					'PntDeathSvid_ActDT' => 'dt',
					'Org_id' => 'i',
					'PntDeathSvid_PolFio' => 's',
					'PntDeathSvid_ZagsFIO' => 's',
					'PntDeathSvid_IsFromMother' => 'i',
					//'OrgHead_id' => 'i',
					'OrgHeadPost_id' => 'i',
					'Person_hid' => 'i',
					'PntDeathSvidType_id' => 'i',
					'MedStaffFact_did' => 'i'
				);

				if (!empty($data['PntDeathSvid_IsNoPlace'])) {
					$data['PntDeathSvid_IsNoPlace'] = 2;
				} else {
					$data['PntDeathSvid_IsNoPlace'] = 1;
				}
				if (!empty($data['PntDeathSvid_DeathDate_Date'])) {
					$data['PntDeathSvid_DeathDate'] = $data['PntDeathSvid_DeathDate_Date'];
					if (!empty($data['PntDeathSvid_DeathDate_Time'])) {
						$data['PntDeathSvid_DeathDate'] .= " ".$data['PntDeathSvid_DeathDate_Time'].":00";
					}
				}

				if (!empty($data['PntDeathSvid_ChildBirthDT_Date'])) {
					$data['PntDeathSvid_ChildBirthDT'] = $data['PntDeathSvid_ChildBirthDT_Date'];
					if ($data['PntDeathSvid_ChildBirthDT_Time'] != "") {
						$data['PntDeathSvid_ChildBirthDT'] .= " ".$data['PntDeathSvid_ChildBirthDT_Time'].":00";
					}
				}

				$addr = $this->SaveAddress($data, 'D');
				if ($addr > 0) $data['Address_did'] = $addr;
			}
			
			if (!empty($data['BirthSvid_id'])) {
				$data['BirthSvid_pid'] = $data['BirthSvid_id'];
			}
			if (!empty($data['DeathSvid_id'])) {
				$data['DeathSvid_pid'] = $data['DeathSvid_id'];
			}
			$data['pmUser_id'] = $data['pmUser_id'];
			$data[$tab_name.'_IsBad'] = 1;
			$data[$tab_name.'_IsLose'] = 1;
			$data[$tab_name.'_IsActual'] = 2;

			if ($tab_name == 'BirthSvid') {
				//issue #197753 переделаны хранимки, в которых более 100 параметров.
				//Множестов параметров теперь передаются как один json. Пока только p_BirthSvid_ins
				$query_parts .= 'params := :params::jsonb,';
				$query_parts .= 'pmUser_id := :pmUser_id';
				$params = [];

				foreach ($param_array as $k => $v) { //формирование запроса
					if ($k != 'pmUser_id') {
						$params[strtolower($k)] = !empty($data[$k]) ? $data[$k] : null;
					}
				}

				$sql_p_array = [
					'params' => json_encode($params),
					'pmUser_id' => $data['pmUser_id'],
				];
			} else {
				foreach ($param_array as $k => $v) { //формирование запроса
					$query_parts .= " $k := :$k,";
					$sql_p_array[$k] = (!empty($data[$k]) ? $data[$k] : null);
				}
				$query_parts = substr($query_parts, 0, strlen($query_parts) - 1);
			}

			$query .= str_replace(",*", "", "
				select 
                	{$tab_name}_id as \"svid_id\", 
                    Error_Code as \"Error_Code\", 
                    Error_Message as \"Error_Message\"
				FROM dbo.p_{$tab_name}_ins(
					{$query_parts}
				);
			");
			//echo getDebugSQL($query, $sql_p_array);exit;

			$result = array();
			if ($query != "") {
				$result = array('success' => false);
				$res = $this->db->query($query, $sql_p_array);
				if (is_object($res)) {
					$res = $res->result('array');
					if (!empty($res[0]['Error_Message'])) {
						throw new Exception("Ошибка при выполнении запроса к БД: ".$res[0]['Error_Message']);
					} else if ($res[0]['svid_id'] > 0) {
						if (in_array($tab_name, array('BirthSvid', 'DeathSvid'))) {
							$this->load->model('ApprovalList_model');
							$this->ApprovalList_model->saveApprovalList(array(
								'ApprovalList_ObjectName' => $tab_name,
								'ApprovalList_ObjectId' => $res[0]['svid_id'],
								'pmUser_id' => $data['pmUser_id']
							));
						}
						if ($svid_type == 'death') {
							// для свидетельств о смерти может быть только 1 актуальное свидетельство о смерти
							$this->db->query("
								update
									{$tab_name}
								set
									{$tab_name}_IsActual = 1
								where
									{$tab_name}_id <> :{$tab_name}_id
									and Person_id = :Person_id
							", array(
								$tab_name.'_id' => $res[0]['svid_id'],
								'Person_id' => $data['Person_id']
							));
						}
						$result = array('success' => true, 'svid_id' => $res[0]['svid_id']);
					} else {
						throw new Exception("Ошибка при выполнении запроса к БД");
					}
				} else {
					throw new Exception("Ошибка при выполнении запроса к БД");
				}
			}

			if ($svid_type == 'death') {
				$query = "
					select 
                    	Error_Code as \"Error_Code\", 
                        Error_Message as \"Error_Message\"
					from dbo.p_Person_kill(
						Person_id := :Person_id,
						PersonCloseCause_id := :PersonCloseCause_id,
						Person_deadDT := :Person_deadDT,
						pmUser_id := :pmUser_id);
				";
				$query_params = array(
					'Person_id' => $data['Person_id'],
					'PersonCloseCause_id' => 1,
					'Person_deadDT' => (!empty($data['DeathSvid_DeathDate']) ? $data['DeathSvid_DeathDate'] : $data['DeathSvid_GiveDate']),
					'pmUser_id' => $data['pmUser_id']
				);
				$res = $this->db->query($query, $query_params);
				if (is_object($res)) {
					$res = $res->result('array');
					if (empty($res[0]['Error_Message'])) {
						$this->load->library('swPersonRegister');
						swPersonRegister::onPersonDead($query_params);
					} else {
						throw new Exception("Ошибка при выполнении запроса к БД: ".$res[0]['Error_Message']);
					}
				} else {
					throw new Exception("Ошибка при выполнении запроса к БД");
				}
			}
			if ($svid_type == 'pntdeath' && !empty($data['Person_cid'])) {
				$query = "
					select 
                    	Error_Code as \"Error_Code\", 
                        Error_Message as \"Error_Message\"
					from dbo.p_Person_kill(
						Person_id := :Person_id,
						PersonCloseCause_id := :PersonCloseCause_id,
						Person_deadDT := :Person_deadDT,
						pmUser_id := :pmUser_id);
				";
				$query_params = array(
					'Person_id' => $data['Person_cid'],
					'PersonCloseCause_id' => 1,
					'Person_deadDT' => (!empty($data['PntDeathSvid_DeathDate']) ? $data['PntDeathSvid_DeathDate'] : $data['PntDeathSvid_GiveDate']),
					'pmUser_id' => $data['pmUser_id']
				);
				$res = $this->db->query($query, $query_params);
				if (is_object($res)) {
					$res = $res->result('array');
					if (empty($res[0]['Error_Message'])) {
						$this->load->library('swPersonRegister');
						swPersonRegister::onPersonDead($query_params);
					} else {
						throw new Exception("Ошибка при выполнении запроса к БД: ".$res[0]['Error_Message']);
					}
				} else {
					throw new Exception("Ошибка при выполнении запроса к БД");
				}
			}
			$this->db->trans_commit();

			if ($svid_type == 'death' && !empty($result['svid_id'])) {

				$query = "
					select
						PR.PersonRegister_id as \"PersonRegister_id\"
					from v_PersonRegister PR 

					where PR.Person_id = :Person_id
						and PR.PersonRegister_disDate is null
						and (
							PR.PersonRegisterType_id in (7, 64, 62) or 
							PR.MorbusType_id in (7, 94, 91)
						) -- тубекулез, Паллиативная помощь, суицид
				";
				$res = $this->queryResult($query, [
					'Person_id' => $data['Person_id']
				]);
				$this->load->model('PersonRegister_model','PRegister_model');
				foreach($res as $row) {
					$params = [
						'PersonRegister_id' => $row['PersonRegister_id'],
						'PersonRegister_disDate' => (!empty($data['DeathSvid_DeathDate']) ? $data['DeathSvid_DeathDate'] : $data['DeathSvid_GiveDate']),
						'PersonRegisterOutCause_id' => 1,
						'MedPersonal_did' => $sql_p_array['MedPersonal_id'],
						'Lpu_did' => $sql_p_array['Lpu_id'],
						'pmUser_id' => $data['pmUser_id'],
						'autoExcept' => 1
					];
					$params = array_merge($params, getSessionParams());
					$this->PRegister_model->out($params);
				}
			}
		} catch (Exception $e){
			$this->db->trans_rollback();
			return array('success'=>false,'Error_Msg'=>$e->getMessage());
		}
		
		return $result;
	}
	
	/**
	 *	Какая-то проверка мед. свидетельства
	 */
	function checkMedSvidSimple($svid_id, $svid_type) { //простая проверка, делается перед созданием свидетельства на основе существующего
		$err_array = array();
		
		if ($svid_type == 'death') {
			//Свидетельство на данного человека уже заведено!
			$query = "
				select d.DeathSvid_id as \"id\"
				from v_DeathSvid d 

				where d.DeathSvid_id = :DeathSvid_id
					and exists (select t.DeathSvid_id from v_DeathSvid t  where t.Person_id = d.Person_id and COALESCE(t.DeathSvid_IsBad, 1) = 1)


			";
			$result = $this->db->query($query, array('DeathSvid_id' => $svid_id));
			$res = $result->result('array');
			if (is_array($res) && count($res) > 0 && !empty($res[0]['id'])) {
				$err_array[] = "Свидетельство на данного человека уже заведено!";
			}
		}
		
		if (count($err_array) == 0) {
			$resp = array('success' => true, 'Error_Msg' => $svid_id.' '.$svid_type);
		} else {
			$resp = array('success' => false, 'Error_Msg' => $err_array[0]);
		}
		return $resp;
	}

	/**
	 * Приходится делать обновление номера в генераторе из-за
	 * возможности изменения номера свидетельства вручную на Уфе
	 */
	/*function refreshMedSvidGeneratorForUfa($data, $svid_type) {
		$params = array('Lpu_id' => $data['Lpu_id']);

		switch($svid_type) {
			case 'birth':
				$params['MedSvid_Ser'] = $data['BirthSvid_Ser'];
				break;

			case 'death':
				$params['MedSvid_Ser'] = $data['DeathSvid_Ser'];
				break;

			case 'pntdeath':
				$params['MedSvid_Ser'] = $data['PntDeathSvid_Ser'];
				break;
		}

		$query = "
			declare @ser varchar(20) = :MedSvid_Ser;
			declare @num bigint = (
				select top 1 pmGen_Value
				from pmGen 

				where pmGen_ObjectName = 'MedSvidNum' and Lpu_id = :Lpu_id
			);
			with svid as (
				select
					cast(DeathSvid_Num as bigint) as num
				from v_DeathSvid 

				where
					DeathSvid_Ser = @ser
					and ISNUMERIC(DeathSvid_Num) = 1 and cast(DeathSvid_Num as bigint) > @num
				union
				select
					cast(PntDeathSvid_Num as bigint) as num
				from v_PntDeathSvid 

				where
					PntDeathSvid_Ser = @ser
					and ISNUMERIC(PntDeathSvid_Num) = 1 and cast(PntDeathSvid_Num as bigint) > @num
				union
				select
					cast(BirthSvid_Num as bigint) as num
				from v_BirthSvid 

				where
				 	BirthSvid_Ser = @ser
					and ISNUMERIC(BirthSvid_Num) = 1 and cast(BirthSvid_Num as bigint) > @num
			)
			select top 1
				svid_beg.num as num1,
				svid_end.num as num2
			from
				svid svid_beg 

				LEFT JOIN LATERAL(

					select top 1 min(s.num) as num
					from svid s 

					where not exists(select top 1 num from svid  where num = s.num + 1)

				) svid_end
			where svid_beg.num = @num + 1
		";

		$res = $this->getFirstRowFromQuery($query, $params);
		if (is_array($res) && !empty($res['num2'])) {
			$this->load->model('Options_model', 'opmodel');

			$this->opmodel->setPMGenValue($data, 'MedSvidNum', $res['num2']);
		}
	}*/

	/**
	 * Обновляем данные в PersonInfo по протоколам установления и разбора случая смерти
	 */
	function refreshPersonInfo($data) {

		//1. Тащим все данные из PersonInfo
		$personInfoFields = $this->getFirstResultFromQuery("
			SELECT (
				select
					string_agg(column_name, ', ')
				from
					information_schema.columns
				where
					table_name = 'personinfo'
					and table_schema = 'dbo'
					and column_name not in ('pmuser_insid','pmuser_updid','personinfo_insdt','personinfo_upddt','personinfo_rowversion')
			) as \"column_name\"
		");

		$query = "
			select {$personInfoFields}
			from
				PersonInfo
			where
				Person_id = :Person_id
		";

		$response = $this->queryResult($query, $data);
		if (!is_array($response)) {
			return false;
		}

		if (count($response) == 1 && !empty($response[0]['personinfo_id'])){
			$personInfoParams = $response[0];
			$proc = 'upd';
		} else {
			$personInfoParams['personinfo_id'] = 0;
			$proc = 'ins';
			$personInfoParams['Server_id'] = !empty($data['Server_id'])?$data['Server_id']:$data['session']['Server_id'];
			$personInfoParams['Person_id'] = $data['Person_id'];
		}

		$personInfoParams['pmUser_id'] = $data['pmUser_id'];
		$personInfoParams['personinfo_isparsdeath'] = (!empty($data['PersonInfo_IsParsDeath']) && in_array($data['PersonInfo_IsParsDeath'], Array(1, 'true')))?2:1;
		$personInfoParams['personinfo_issetdeath'] = (!empty($data['PersonInfo_IsSetDeath']) && in_array($data['PersonInfo_IsSetDeath'], Array(1, 'true')))?2:1;

		$fnParams = array();
		foreach($personInfoParams as $key => $value) {
			$fnParams[] = "{$key} := :{$key}";
		}
		$fnParams = implode(",\n", $fnParams);

		$query = "
			select
				PersonInfo_id as \"PersonInfo_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_PersonInfo_{$proc} (
				{$fnParams}
			)
		";

		$result = $this->db->query($query, $personInfoParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Резервирование номеров для свидетельств
	 */
	function reserveNums($data) {
		$params = array(
			'NumeratorObject_SysName' => 'BirthSvid',
			'Numerator_id' => $data['Numerator_id'],
			'Lpu_id' => $data['Lpu_id'],
			'Numerator_ReserveStart' => $data['Blank_FirstNum'],
			'Numerator_ReserveCount' => $data['Blank_Count'],
			'pmUser_id' => $data['pmUser_id']
		);

		switch ($data['svid_type']) {
			case 'birth': { $params['NumeratorObject_SysName'] = 'BirthSvid'; break; }
			case 'death': { $params['NumeratorObject_SysName'] = 'DeathSvid'; break; }
			case 'pntdeath': { $params['NumeratorObject_SysName'] = 'PntDeathSvid'; break; }
		}

		$this->load->model('Numerator_model');
		$resp = $this->Numerator_model->reserveNums($params);

		if (isset($resp['Numerator_Nums'])) {
			return $resp;
		} else {
			throw new Exception('Ошибка резервирования номеров');
		}
	}

	/**
	 *	Еще одна проверка мед. свидетельства
	 */
	function beforeSave($data, $svid_type, $numerator = null) {
		// Помечаем исходное м/с как утерянное.
		// если группы "Предыдущее свидетельство" заполнены, то при сохранении делать предыдущее свидетельство неактуальным (при совпадении параметров: серия, номер, пациент)
		if ($svid_type == 'death') {
			if (empty($data['DeathSvid_predid']) && !empty($data['DeathSvid_OldNum'])) {
				// попробуем найти
				$query = "
					select 
						DeathSvid_id as \"DeathSvid_id\"
					from
						v_DeathSvid 

					where
						Person_id = :Person_id
						and DeathSvid_Ser = :DeathSvid_OldSer
						and DeathSvid_Num = :DeathSvid_OldNum
				    limit 1
				";
				$resp = $this->queryResult($query, array(
					'Person_id' => $data['Person_id'],
					'DeathSvid_OldSer' => $data['DeathSvid_OldSer'],
					'DeathSvid_OldNum' => $data['DeathSvid_OldNum']
				));
				if (!empty($resp[0]['DeathSvid_id'])) {
					$data['DeathSvid_predid'] = $resp[0]['DeathSvid_id'];
				}
			}
			if (!empty($data['DeathSvid_predid'])) {
				if (!empty($data['DeathSvid_IsDuplicate']) && $data['DeathSvid_IsDuplicate'] == 2) {
					$this->setDeathSvidIsLose(array(
						'DeathSvid_id' => $data['DeathSvid_predid'],
						'IsLose' => 2
					));
				} else {
					$this->setDeathSvidIsActual(array(
						'DeathSvid_id' => $data['DeathSvid_predid'],
						'IsActual' => 1
					));
				}
			}
		}

		if ($svid_type == 'pntdeath') {
			if (empty($data['DeathSvid_predid']) && !empty($data['DeathSvid_OldNum'])) {
				// попробуем найти
				$query = "
					select 
						PntDeathSvid_id as \"PntDeathSvid_id\"
					from
						v_PntDeathSvid 

					where
						Person_id = :Person_id
						and PntDeathSvid_Ser = :PntDeathSvid_OldSer
						and PntDeathSvid_Num = :PntDeathSvid_OldNum
				    limit 1
				";
				$resp = $this->queryResult($query, array(
					'Person_id' => $data['Person_id'],
					'PntDeathSvid_OldSer' => $data['PntDeathSvid_OldSer'],
					'PntDeathSvid_OldNum' => $data['PntDeathSvid_OldNum']
				));
				if (!empty($resp[0]['PntDeathSvid_id'])) {
					$data['PntDeathSvid_predid'] = $resp[0]['PntDeathSvid_id'];
				}
			}
			if (!empty($data['PntDeathSvid_predid'])) {
				if (!empty($data['PntDeathSvid_IsDuplicate']) && $data['PntDeathSvid_IsDuplicate'] == 2) {
					$this->setDeathSvidIsLose(array(
						'PntDeathSvid_id' => $data['PntDeathSvid_predid'],
						'IsLose' => 2
					));
				} else {
					$this->setDeathSvidIsActual(array(
						'PntDeathSvid_id' => $data['PntDeathSvid_predid'],
						'IsActual' => 1
					));
				}
			}
		}

		$err_array = array();

		$SvidTable = "";
		switch($svid_type) {
			case 'birth':
				$SvidTable = "BirthSvid";
				break;
			case 'death':
				$SvidTable = "DeathSvid";
				break;
			case 'pntdeath':
				$SvidTable = "PntDeathSvid";
				break;
		}

		if (!empty($SvidTable)) {
			$this->load->model('Numerator_model');
			// проверка на дубли по номеру
			$filter = "";
			$queryParams = array(
				$SvidTable.'_Ser' => $data[$SvidTable.'_Ser'],
				$SvidTable.'_Num' => $data[$SvidTable.'_Num']
			);
			if (is_array($numerator) && !empty($numerator['NumeratorGenUpd_id'])) {
				switch ($numerator['NumeratorGenUpd_id']) {
					case 1: // день
						$filter .= " and date_part('DAY',CAST({$SvidTable}_GiveDate AS date)) = date_part('DAY',CAST(:{$SvidTable}_GiveDate AS date)) and date_part('MONTH',CAST({$SvidTable}_GiveDate AS date)) = date_part('MONTH',CAST(:{$SvidTable}_GiveDate AS date)) and date_part('YEAR',CAST({$SvidTable}_GiveDate AS date)) = date_part('YEAR',CAST(:{$SvidTable}_GiveDate AS date))";
						$queryParams["{$SvidTable}_GiveDate"] = $data["{$SvidTable}_GiveDate"];
						break;
					case 2: // неделя
						$filter .= " and date_part('WEEK', CAST({$SvidTable}_GiveDate AS date)) = date_part('WEEK', CAST(:{$SvidTable}_GiveDate AS date)) and date_part('MONTH',CAST({$SvidTable}_GiveDate AS date)) = date_part('MONTH',CAST(:{$SvidTable}_GiveDate AS date)) and date_part('YEAR',CAST({$SvidTable}_GiveDate AS date)) = date_part('YEAR',CAST(:{$SvidTable}_GiveDate AS date))";
						$queryParams["{$SvidTable}_GiveDate"] = $data["{$SvidTable}_GiveDate"];
						break;
					case 3: // месяц
						$filter .= " and date_part('MONTH',CAST({$SvidTable}_GiveDate AS date)) = date_part('MONTH',CAST(:{$SvidTable}_GiveDate AS date)) and date_part('YEAR',CAST({$SvidTable}_GiveDate AS date)) = date_part('YEAR',CAST(:{$SvidTable}_GiveDate AS date))";
						$queryParams["{$SvidTable}_GiveDate"] = $data["{$SvidTable}_GiveDate"];
						break;
					case 4: // год
						$filter .= " and date_part('YEAR',CAST({$SvidTable}_GiveDate AS date)) = date_part('YEAR',CAST(:{$SvidTable}_GiveDate AS date))";
						$queryParams["{$SvidTable}_GiveDate"] = $data["{$SvidTable}_GiveDate"];
						break;
				}
			}

			if (is_array($numerator) && !empty($numerator['Numerator_begDT'])) {
				$filter .= " and cast({$SvidTable}_GiveDate as date) >= :Numerator_begDT";
				$queryParams["Numerator_begDT"] = date('Y-m-d', strtotime($numerator['Numerator_begDT']));
			}

			if (is_array($numerator) && !empty($numerator['Numerator_endDT'])) {
				$filter .= " and cast({$SvidTable}_GiveDate as date) <= :Numerator_endDT";
				$queryParams["Numerator_endDT"] = date('Y-m-d', strtotime($numerator['Numerator_endDT']));
			}

			$query = "
				select
					{$SvidTable}_id as \"{$SvidTable}_id\"
				from
					{$SvidTable} 

				where
					COALESCE({$SvidTable}_IsBad, 1) = 1

					and {$SvidTable}_Ser = :{$SvidTable}_Ser
					and {$SvidTable}_Num = cast(:{$SvidTable}_Num as varchar)
					{$filter}
			    limit 1
			";
			//echo getDebugSQL($query, $queryParams); die();
			$resp = $this->queryResult($query, $queryParams);
			if (!is_array($resp)) {
				$err_array[] = "Ошибка проверки на существование свидетельств с теми же номером и серией!";
			}
			if (!empty($resp[0]["{$SvidTable}_id"])) {
				$err_array[] = "Свидетельство с данными номером и серией уже существует!";
			}

			if (getRegionNick() == 'ufa' && $data['ReceptType_id'] != 2) {
				// номер должен быть зарезервирован
				$reserved = $this->Numerator_model->checkNumInRezerv(array(
					'NumeratorObject_SysName' => $SvidTable,
					'Lpu_id' => $data['Lpu_id'],
					'Numerator_Num' => $data["{$SvidTable}_Num"]
				), $numerator);
				if (is_array($reserved) && !empty($reserved['Error_Msg'])) {
					$err_array[] = $reserved['Error_Msg'];
				} elseif (!$reserved) {
					$err_array[] = "Номер бланка должен попадать в один из введенных диапазонов резервирования.";
				}
			}
		}

		if ($svid_type == 'birth') {
			//Дата рождения не может быть больше даты выдачи свидетельства!
			$birth_date = strtotime($data['BirthSvid_BirthDT_Date']);
			$give_date = strtotime($data['BirthSvid_GiveDate']);
			if ($birth_date > $give_date)
				$err_array[] = "Дата рождения не может быть больше даты выдачи свидетельства!";
		
			//Дата выдачи не может быть позднее текущей даты/даты получения
			$give_date = strtotime($data['BirthSvid_GiveDate']);
			$rcp_date = strtotime($data['BirthSvid_RcpDate']);
			$now_date = date("U");
			if ($give_date > $now_date)
				$err_array[] = "Дата выдачи не может быть позднее текущей даты";

			if ( getRegionNick() != 'perm' && $give_date > $rcp_date)
				$err_array[] = "Дата выдачи не может быть позднее даты получения";
				
			//Дата получения не может быть меньше даты выдачи
			$rcp_date = strtotime($data['BirthSvid_RcpDate']);
			$give_date = strtotime($data['BirthSvid_GiveDate']);
			if ( getRegionNick() != 'perm' && $rcp_date < $give_date)
				$err_array[] = "Дата получения не может быть меньше даты выдачи";

			//Дата рождения ребенка не может быть меньше даты рождения матери
			$query = "
				select 
					to_char(Person_BirthDay, 'DD.MM.YYYY') as  \"mBirthDay\"

				from 
					v_Person_ER 

				where 
					Person_id = :person_id
			";
			$result = $this->db->query($query, array('person_id' => $data['Person_id']));
			$res = $result->result('array');
			$res = $res[0]['mBirthDay'];
			$birth_date = strtotime($data['BirthSvid_BirthDT_Date']);
			$mbirth_date = strtotime($res);
			
			if ($birth_date < $mbirth_date)
				$err_array[] = "Дата рождения ребенка не может быть меньше даты рождения матери!";
				
			//Свидетельство на данного человека заведено. Это возможно по причине рождения двойни. Остановить сохранение и вернутся к редактированию свидетельства?
			/*
			$query = "select count(*) cnt from (
				select (p.Person_SurName + ' ' + p.Person_FirName + ' ' + p.Person_SecName + ' ' + to_char(p.Person_BirthDay, 'DD.MM.YYYY')) pstr

				from BirthSvid d left join v_Person_ER p on p.Person_id = d.Person_id
				where d.BirthSvid_IsBad = 1
			) pp 
			where pstr = (select top 1 Person_SurName + ' ' + Person_FirName + ' ' + Person_SecName + ' ' + to_char(Person_BirthDay, 'DD.MM.YYYY') from v_Person_ER where Person_id = :person_id)";

			$result = $this->db->query($query, array('person_id' => $data['Person_id']));
			$res = $result->result('array');
			$res = $res[0]['cnt'];
			if ($res > 0)
				$err_array[] = "Свидетельство на данного человека заведено. Это возможно по причине рождения двойни. Остановить сохранение и вернутся к редактированию свидетельства?";
			*/
		}
		
		if ($svid_type == 'death') {
			if (empty($data['DeathSvid_DeathDate_Date']) && empty($data['DeathSvid_DeathDateStr']) && !$data['DeathSvid_IsUnknownDeathDate']) {
				$err_array[] = "Должна быть указана дата смерти либо неуточненная дата смерти, либо указано, что дата смерти неизвестна";
			}
			
			if (empty($data['DeathSvid_DeathDate_Time']) && empty($data['DeathSvid_DeathDateStr']) && !$data['DeathSvid_IsNoDeathTime']) {
				$err_array[] = "Не указано время смерти. Необходимо указать точное время смерти, либо указать, что время смерти неизвестно";
			}

			//Дата выдачи не может быть позднее текущей даты		
			$give_date = strtotime($data['DeathSvid_GiveDate']);
			$now_date = date("U");
			if ($give_date > $now_date)
				$err_array[] = "Дата выдачи не может быть позднее текущей даты";
				
			//Дата получения не может быть меньше даты выдачи
			$rcp_date = strtotime($data['DeathSvid_RcpDate']);
			$give_date = strtotime($data['DeathSvid_GiveDate']);
			if (!empty($rcp_date) && $rcp_date < $give_date)
				$err_array[] = "Дата получения не может быть меньше даты выдачи";
				
			//Дата смерти не может быть позднее даты выдачи
			$give_date = strtotime($data['DeathSvid_GiveDate']);
			$death_date = strtotime($data['DeathSvid_DeathDate_Date']);
			if ($give_date < $death_date)
				$err_array[] = "Дата смерти не может быть позднее даты выдачи";

			//Дата смерти не может быть позднее даты выдачи предыдущего свидетельства
			if ($data['DeathSvid_OldGiveDate'] != '') {
				$old_give_date = strtotime($data['DeathSvid_OldGiveDate']);
				if ($old_give_date < $death_date)
					$err_array[] = "Дата смерти не может быть позднее даты выдачи предыдущего свидетельства";
			}

			$resp_check = $this->queryResult("
				select 
					d2.DeathSvid_id as \"DeathSvid_id\",
					d2.DeathSvid_Ser as \"DeathSvid_Ser\",
					d2.DeathSvid_Num as \"DeathSvid_Num\",
					dst.DeathSvidType_Name as \"DeathSvidType_Name\"
				from
					v_DeathSvid d2 

					left join v_DeathSvidType dst  on dst.DeathSvidType_id = d2.DeathSvidType_id

				where
					d2.Person_id = :Person_id
					and COALESCE(d2.DeathSvid_IsActual, 2) = 2

			", array(
				'Person_id' => $data['Person_id']
			));
			if (!empty($resp_check[0]['DeathSvid_id'])) {
				$err_array[] = "Актуальное свидетельство {$resp_check[0]['DeathSvid_Ser']} №{$resp_check[0]['DeathSvid_Num']} ({$resp_check[0]['DeathSvidType_Name']}) о смерти данного человека уже существует. Новое свидетельство можно создать только на основе актуального";
			}

			// Для обычного м/с о смерти нужна проверка, что нет 2-х одинаковых диагнозов в разных причинах смерти. Выдавать сообщение "Один диагноз не может быть использован в нескольких причинах смерти"
			/*$Diags = array();
			$isSameDiag = false;
			if (!empty($data['Diag_iid'])) {
				if (in_array($data['Diag_iid'], $Diags)) {
					$isSameDiag = true;
				}
				$Diags[] = $data['Diag_iid'];
			}
			if (!empty($data['Diag_tid'])) {
				if (in_array($data['Diag_tid'], $Diags)) {
					$isSameDiag = true;
				}
				$Diags[] = $data['Diag_tid'];
			}
			if (!empty($data['Diag_mid'])) {
				if (in_array($data['Diag_mid'], $Diags)) {
					$isSameDiag = true;
				}
				$Diags[] = $data['Diag_mid'];
			}
			if (!empty($data['Diag_eid'])) {
				if (in_array($data['Diag_eid'], $Diags)) {
					$isSameDiag = true;
				}
				$Diags[] = $data['Diag_eid'];
			}
			if (!empty($data['Diag_oid'])) {
				if (in_array($data['Diag_oid'], $Diags)) {
					$isSameDiag = true;
				}
				$Diags[] = $data['Diag_oid'];
			}

			if ($isSameDiag) {
				$err_array[] = "Один диагноз не может быть использован в нескольких причинах смерти";
			}*/
		}
		
		if ($svid_type == 'pntdeath') {
			//Дата рождения не может быть позднее: текущей даты, даты смерти, даты выдачи
			$birth_date = strtotime($data['PntDeathSvid_ChildBirthDT_Date']);
			$death_date = strtotime($data['PntDeathSvid_DeathDate_Date']);
			$give_date = strtotime($data['PntDeathSvid_GiveDate']);
			$rcp_date = strtotime($data['PntDeathSvid_RcpDate']);
			$now_date = date("U");
			if (!empty($death_date) && $birth_date > $death_date)
				$err_array[] = "Дата рождения не может быть позднее даты смерти";
			if (!empty($birth_date) && $birth_date > $give_date)
				$err_array[] = "Дата рождения не может быть позднее даты выдачи";
			if (!empty($birth_date) && $birth_date > $now_date)
				$err_array[] = "Дата рождения не может быть позднее текущей даты";
			if ($give_date > $now_date)
				$err_array[] = "Дата выдачи не может быть позднее текущей даты";
			if (!empty($death_date) && $give_date < $death_date)
				$err_array[] = "Дата выдачи не может быть меньше даты смерти";
			if (!empty($rcp_date) && $rcp_date < $give_date)
				$err_array[] = "Дата получения не может быть меньше даты выдачи";

			//Дата смерти не может быть позже 7 дней после даты рождения
			if (!empty($data['PntDeathSvid_DeathDate_Date']) && !empty($data['PntDeathSvid_ChildBirthDT_Date'])) {
				$death_date = strtotime($data['PntDeathSvid_DeathDate_Date']) + $this->timeStrToSec($data['PntDeathSvid_DeathDate_Time']);
				$birth_date = strtotime($data['PntDeathSvid_ChildBirthDT_Date']) + $this->timeStrToSec($data['PntDeathSvid_ChildBirthDT_Time']);
				if ((($death_date - $birth_date) / 86400) > 7)
					$err_array[] = "Дата смерти не может быть позже 7 дней после даты рождения";
			}

			/*$DeathSvid_id = $this->getFirstResultFromQuery("
				select top 1
					d2.PntDeathSvid_id
				from
					v_PntDeathSvid d2 

				where
					d2.Person_id = :Person_id
					and d2.DeathSvidType_id = :DeathSvidType_id
					and COALESCE(d2.PntDeathSvid_IsLose, 1) = 1

					and COALESCE(d2.PntDeathSvid_IsBad, 1) = 1

					and d2.DeathSvidType_id not in (3,4)
			", array(
				'Person_id' => $data['Person_id'],
				'DeathSvidType_id' => $data['DeathSvidType_id']
			));
			if (!empty($DeathSvid_id)) {
				$err_array[] = "Уже существует неиспорченное свидетельство данного типа на данного человека";
			}*/
		}

		if (count($err_array) == 0)
			$resp = array('success' => true, 'Error_Msg' => '');
		else
			$resp = array('success' => false, 'Error_Msg' => $err_array[0]);
			
		return $resp;
	}
	
	/**
	 *	Сохранение адреса
	 *	возможные варианты: 
	 *	1. Удаление адреса   - если Address_id not null and другие поля пустые 
	 *	2. Добавление адреса - если Address_id null and другие поля заполнены		
	 */
	function SaveAddress($data, $prefix) {
		$Address_id = isset($data[$prefix.'Address_id']) && $data[$prefix.'Address_id'] > 0 ? $data[$prefix.'Address_id'] : 0;
		
		// создаем или редактируем адрес
		// Если строка адреса не пустая
		if (isset($data[$prefix.'Address_Address']) && $data[$prefix.'Address_Address'] != '') {
			// не было адреса
			if ($Address_id <= 0) {
				$sql = "
					select 
                    	Address_id as \"Address_id\", 
                        Error_Code as \"Error_Code\", 
                        Error_Message as \"Error_Message\"
					from dbo.p_Address_ins(
						Server_id := :Server_id,
						KLAreaType_id := Null, -- опреляется логикой в хранимке
						KLCountry_id := :KLCountry_id,
						KLRgn_id := :KLRGN_id,
						KLSubRgn_id := :KLSubRGN_id,
						KLCity_id := :KLCity_id,
						KLTown_id := :KLTown_id,
						KLStreet_id := :KLStreet_id,
						Address_Zip := :Address_Zip,
						Address_House := :Address_House,
						Address_Corpus := :Address_Corpus,
						Address_Flat := :Address_Flat,
						Address_Address := :Address_Address,
						pmUser_id := :pmUser_id);
				";
				$res = $this->db->query($sql, array(
					'Server_id' => $data['Server_id'],
					'KLCountry_id' => $data[$prefix.'KLCountry_id'],
					'KLRGN_id' => $data[$prefix.'KLRGN_id'],
					'KLSubRGN_id' => $data[$prefix.'KLSubRGN_id'],
					'KLCity_id' => $data[$prefix.'KLCity_id'],
					'KLTown_id' => $data[$prefix.'KLTown_id'],
					'KLStreet_id' => $data[$prefix.'KLStreet_id'],
					'Address_Zip' => $data[$prefix.'Address_Zip'],
					'Address_House' => $data[$prefix.'Address_House'],
					'Address_Corpus' => $data[$prefix.'Address_Corpus'],
					'Address_Flat' => $data[$prefix.'Address_Flat'],
					'Address_Address' => $data[$prefix.'Address_Address'],
					'pmUser_id' => $data['pmUser_id']
				));

				//print_r($res->result('array'));
				
				if (is_object($res)) {
					$sel = $res->result('array');
					if ( $sel[0]['Error_Code'] == '' ) {
						$Address_id = $sel[0]['Address_id'];
					} else
						return 0;
				} else
					return 0;
			} else { // обновляем адрес
				$sql = "
					select 
                    	Address_id as \"Address_id\", 
                        v_Error_Code as \"Error_Code\", 
                        v_Error_Message as \"Error_Message\"
					from dbo.p_Address_upd(
						Server_id := :Server_id,
						Address_id := :Address_id,
						KLAreaType_id := NULL, -- опреляется логикой в хранимке
						KLCountry_id := :KLCountry_id,
						KLRgn_id := :KLRGN_id,
						KLSubRgn_id := :KLSubRGN_id,
						KLCity_id := :KLCity_id,
						KLTown_id := :KLTown_id,
						KLStreet_id := :KLStreet_id,
						Address_Zip := :Address_Zip,
						Address_House := :Address_House,
						Address_Corpus := :Address_Corpus,
						Address_Flat := :Address_Flat,
						Address_Address := :Address_Address,
						pmUser_id := :pmUser_id);
				";
				$res = $this->db->query($sql, array(
					'Server_id' => $data['Server_id'],
					'Address_id' => $data[$prefix.'Address_id'],
					'KLCountry_id' => $data[$prefix.'KLCountry_id'],
					'KLRGN_id' => $data[$prefix.'KLRGN_id'],
					'KLSubRGN_id' => $data[$prefix.'KLSubRGN_id'],
					'KLCity_id' => $data[$prefix.'KLCity_id'],
					'KLTown_id' => $data[$prefix.'KLTown_id'],
					'KLStreet_id' => $data[$prefix.'KLStreet_id'],
					'Address_Zip' => $data[$prefix.'Address_Zip'],
					'Address_House' => $data[$prefix.'Address_House'],
					'Address_Corpus' => $data[$prefix.'Address_Corpus'],
					'Address_Flat' => $data[$prefix.'Address_Flat'],
					'Address_Address' => $data[$prefix.'Address_Address'],
					'pmUser_id' => $data['pmUser_id']
				));

				if (is_object($res)) {
					$sel = $res->result('array');
					if ( $sel[0]['Error_Code'] == '' ) {
						$Address_id = $sel[0]['Address_id'];
					} else
						return 0;
				} else
					return 0;
			}
		}
		
		return $Address_id;
	}

	/**
	 * Сохранение данных о получателе
	 */
	function savePntDeathRecipient($data) {
		$query = "
			update
				PntDeathSvid
			set
				Person_rid = :Person_rid,
				PntDeathSvid_PolFio = :PntDeathSvid_PolFio,
				PntDeathSvid_RcpDoc = :PntDeathSvid_RcpDoc,
				DeputyKind_id = :DeputyKind_id,
				PntDeathSvid_RcpDate = :PntDeathSvid_RcpDate,
				pmUser_updID = :pmUser_id,
				PntDeathSvid_updDT = dbo.tzGetDate()
			where
				PntDeathSvid_id = :PntDeathSvid_id
		";

		$this->db->query($query, $data);

		return array('Error_Msg' => '');
	}

	/**
	 * Сохранение данных о получателе
	 */
	function saveDeathRecipient($data) {
		$query = "
			update
				DeathSvid
			set
				Person_rid = :Person_rid,
				DeathSvid_PolFio = :DeathSvid_PolFio,
				DeathSvid_RcpDocument = :DeathSvid_RcpDocument,
				DeathSvid_RcpDate = :DeathSvid_RcpDate,
				DeathSvidRelation_id = :DeathSvidRelation_id,
				pmUser_updID = :pmUser_id,
				DeathSvid_updDT = dbo.tzGetDate()
			where
				DeathSvid_id = :DeathSvid_id
		";

		$this->db->query($query, $data);

		$this->load->model('ApprovalList_model');
		$this->ApprovalList_model->saveApprovalList(array(
			'ApprovalList_ObjectName' => 'DeathSvid',
			'ApprovalList_ObjectId' => $data['DeathSvid_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		return array('Error_Msg' => '');
	}

	/**
	 * Сохранение данных о получателе
	 */
	function saveBirthRecipient($data){
		$query = "
			update
				BirthSvid
			set
				Person_rid = :Person_rid,
				DeputyKind_id = :DeputyKind_id,
				BirthSvid_RcpDocument = :BirthSvid_RcpDocument,
				BirthSvid_RcpDate = :BirthSvid_RcpDate,
				pmUser_updID = :pmUser_id,
				BirthSvid_updDT = dbo.tzGetDate()
			where
				BirthSvid_id = :BirthSvid_id
		";

		$this->db->query($query, $data);
		
		$this->load->model('ApprovalList_model');
		$this->ApprovalList_model->saveApprovalList(array(
			'ApprovalList_ObjectName' => 'BirthSvid',
			'ApprovalList_ObjectId' => $data['BirthSvid_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		return array('Error_Msg' => '');
	}

	/**
	 *	Установка/снятие признака утерянного мед. свидетельства
	 */
	function setDeathSvidIsLose($data) {
		$table_name = "";
		if (!empty($data['DeathSvid_id'])) {
			$table_name = "DeathSvid";
		}
		if (!empty($data['PntDeathSvid_id'])) {
			$table_name = "PntDeathSvid";
		}
		if (!empty($table_name)) {
			$isActual = "(case when :IsLose = 1 and {$table_name}.{$table_name}_IsBad = 1 then 2 else 1 end)";
			// для свидетельств о смерти может быть только 1 актуальное свидетельство о смерти
			if ($table_name == 'DeathSvid') {
				$isActual = "(case when :IsLose = 1 and {$table_name}_IsBad = 1 and not exists(select {$table_name}_id from $table_name where {$table_name}_IsActual = 2 and {$table_name}_id <> :{$table_name}_id and Person_id = {$table_name}.Person_id) then 2 else 1 end)";
			}

			$query = "
				update
					{$table_name}
				set
					{$table_name}_IsLose = :IsLose,
					{$table_name}_IsActual = {$isActual}
				where
					{$table_name}_id = :{$table_name}_id
			";

			$this->db->query($query, $data);
		}

		return true;
	}

	/**
	 *	Установка/снятие признака актуальности мед. свидетельства
	 */
	function setDeathSvidIsActual($data) {
		$table_name = "";
		if (!empty($data['DeathSvid_id'])) {
			$table_name = "DeathSvid";
		}
		if (!empty($data['PntDeathSvid_id'])) {
			$table_name = "PntDeathSvid";
		}
		if (!empty($table_name)) {
			$query = "
				update
					{$table_name}
				set
					{$table_name}_IsActual = :IsActual
				where
					{$table_name}_id = :{$table_name}_id
			";

			$this->db->query($query, $data);
		}

		return true;
	}

	/**
	 *	Установка/снятие признака испорченного мед. свидетельства
	 */
	function setBadSvid($data) {
		$table_name = 'DeathSvid';
		switch ($data['svid_type']) {
			case 'birth': { $table_name = "BirthSvid"; break;}
			case 'death': { $table_name = "DeathSvid"; break;}
			case 'pntdeath': { $table_name = "PntDeathSvid"; break;}
		}
		
		if ($data['bad_id'] == 1 && in_array($data['svid_type'], array('death'))) {
			$DeathSvid_id = $this->getFirstResultFromQuery("
				select 
					d2.DeathSvid_id as \"DeathSvid_id\"
				from
					v_DeathSvid d1 

					inner join v_DeathSvid d2  on d2.Person_id = d1.Person_id and d2.DeathSvidType_id = d1.DeathSvidType_id and COALESCE(d2.DeathSvid_IsLose, 1) = 1 and COALESCE(d2.DeathSvid_IsBad, 1) = 1

				where
					d1.DeathSvid_id = :DeathSvid_id
					and d1.DeathSvidType_id not in (3,4)
				limit 1
			", array(
				'DeathSvid_id' => $data['svid_id']
			));
			if (!empty($DeathSvid_id)) {
				return array(array('Error_Msg' => 'Уже существует неиспорченное свидетельство данного типа на данного человека'));
			}
		}
		
		if ($data['bad_id'] == 1 && in_array($table_name, array('BirthSvid', 'DeathSvid'))) {
			$resp_bs = $this->queryResult("
				select
					bs.{$table_name}_id as \"svid_id\",
					bs.{$table_name}_Ser as \"svid_ser\",
					bs.{$table_name}_Num as \"svid_num\"
				from
					v_{$table_name} bs
				where
					bs.{$table_name}_pid = :svid_id
				limit 1
			", array(
				'svid_id' => $data['svid_id']
			));
			if (!empty($resp_bs[0]['svid_id'])) {
				return array(array('Error_Msg' => 'На основании текущего мед. свидетельства было оформлено новое: серия ' . $resp_bs[0]['svid_ser'] . ' номер ' . $resp_bs[0]['svid_num'] . '. Снять отметку «Испорченный» невозможно'));
			}
		}

		/*if ($data['bad_id'] == 1 && in_array($data['svid_type'], array('pntdeath'))) {
			$DeathSvid_id = $this->getFirstResultFromQuery("
				select top 1
					d2.PntDeathSvid_id
				from
					v_PntDeathSvid d1

					inner join v_PntDeathSvid d2  on d2.Person_id = d1.Person_id and d2.DeathSvidType_id = d1.DeathSvidType_id and COALESCE(d2.PntDeathSvid_IsLose, 1) = 1 and COALESCE(d2.PntDeathSvid_IsBad, 1) = 1


				where
					d1.PntDeathSvid_id = :PntDeathSvid_id
					and d1.PntDeathSvidType_id not in (3,4)
			", array(
				'DeathSvid_id' => $data['svid_id']
			));
			if (!empty($DeathSvid_id)) {
				return array(array('Error_Msg' => 'Уже существует неиспорченное свидетельство данного типа на данного человека'));
			}
		}*/
		
		if ($data['bad_id'] == 1) {//для всех испорченных свидетельств, проверка на дублирование номера и серии
			// определяем нумератор на дату выписки свидетельства
			$filter = "";
			$queryParams = array('svid_id' => $data['svid_id']);
			$this->load->model('Numerator_model');
			$resp = $this->queryResult("
				select
					Lpu_id as \"Lpu_id\",
					LpuSection_id as \"LpuSection_id\",
					to_char({$table_name}_GiveDate, 'YYYY-MM-DD HH24:MI:SS') as \"GiveDate\"

				from
					{$table_name} 

				where
					{$table_name}_id = :svid_id
			", array('svid_id' => $data['svid_id']));
			if (!empty($resp[0])) {
				$numerator = $this->Numerator_model->getActiveNumerator(array(
					'NumeratorObject_SysName' => $table_name,
					'Lpu_id' => $resp[0]['Lpu_id'],
					'LpuSection_id' => $resp[0]['LpuSection_id'],
					'onDate' => $resp[0]['GiveDate']
				));

				if (is_array($numerator) && !empty($numerator['NumeratorGenUpd_id'])) {
					switch ($numerator['NumeratorGenUpd_id']) {
						case 1: // день
							$filter .= " and date_part('DAY',CAST({$table_name}_GiveDate as date)) = date_part('DAY',CAST(:{$table_name}_GiveDate as date)) and date_part('MONTH',CAST({$table_name}_GiveDate as date)) = date_part('MONTH',CAST(:{$table_name}_GiveDate as date)) and date_part('YEAR',CAST({$table_name}_GiveDate as date)) = date_part('YEAR',CAST(:{$table_name}_GiveDate as date))";
							$queryParams["{$table_name}_GiveDate"] = $resp[0]['GiveDate'];
							break;
						case 2: // неделя
							$filter .= " and date_part('WEEK', CAST({$table_name}_GiveDate as date)) = date_part('WEEK', CAST(:{$table_name}_GiveDate as date)) and date_part('MONTH',CAST({$table_name}_GiveDate as date)) = date_part('MONTH',CAST(:{$table_name}_GiveDate as date)) and date_part('YEAR',CAST({$table_name}_GiveDate as date)) = date_part('YEAR',CAST(:{$table_name}_GiveDate as date))";
							$queryParams["{$table_name}_GiveDate"] = $resp[0]['GiveDate'];
							break;
						case 3: // месяц
							$filter .= " and date_part('MONTH',CAST({$table_name}_GiveDate as date)) = date_part('MONTH',CAST(:{$table_name}_GiveDate as date)) and date_part('YEAR',CAST({$table_name}_GiveDate as date)) = date_part('YEAR',CAST(:{$table_name}_GiveDate as date))";
							$queryParams["{$table_name}_GiveDate"] = $resp[0]['GiveDate'];
							break;
						case 4: // год
							$filter .= " and date_part('YEAR',CAST({$table_name}_GiveDate as date)) = date_part('YEAR',CAST(:{$table_name}_GiveDate as date))";
							$queryParams["{$table_name}_GiveDate"] = $resp[0]['GiveDate'];
							break;
					}
				}

				if (is_array($numerator) && !empty($numerator['Numerator_begDT'])) {
					$filter .= " and cast({$table_name}_GiveDate as date) >= :Numerator_begDT";
					$queryParams["Numerator_begDT"] = date('Y-m-d', strtotime($numerator['Numerator_begDT']));
				}

				if (is_array($numerator) && !empty($numerator['Numerator_endDT'])) {
					$filter .= " and cast({$table_name}_GiveDate as date) <= :Numerator_endDT";
					$queryParams["Numerator_endDT"] = date('Y-m-d', strtotime($numerator['Numerator_endDT']));
				}
			} else {
				return array(array('Error_Msg' => 'Ошибка получения информации по свидетельству'));
			}

			$query = "
				select d.{$table_name}_id as \"id\"
				from v_{$table_name} d 

				where d.{$table_name}_id = :svid_id
					and exists (
						select t.{$table_name}_id
						from v_{$table_name} t 

						where COALESCE(t.{$table_name}_Ser, '') = COALESCE(d.{$table_name}_Ser, '')

							and COALESCE(t.{$table_name}_Num, '') = COALESCE(d.{$table_name}_Num, '')

							and COALESCE(t.{$table_name}_IsBad, 1) = 1

							{$filter}
					)
				limit 1
			";
			$result = $this->db->query($query, $queryParams);
			$res = $result->result('array');
			if (is_array($res) && count($res) > 0 && !empty($res[0]['id'])) {
				return array(array('Error_Msg' => 'Уже существует свидетельство с данными серией и номером'));
			}
		}
		
		if (in_array($table_name, array("DeathSvid","PntDeathSvid"))) {//если свидетельства о смерти, необходимо "убивать" и "оживлять" людей
			$person_field = ($table_name=='PntDeathSvid')?"Person_cid":"Person_id";
			$query = "
				SELECT 
					{$person_field} as \"Person_id\",
					to_char({$table_name}_DeathDate, 'YYYY-MM-DD HH24:MI:SS') as \"DeathDate\",

					to_char({$table_name}_GiveDate, 'YYYY-MM-DD HH24:MI:SS') as \"GiveDate\"

				FROM {$table_name} 

				WHERE {$table_name}_id = {$data['svid_id']}
			";
			$result = $this->db->query($query, array());
			$res = $result->result('array');
			$res = $res[0];
			
			$query_params = array();
			$query_params['pmUser_id'] = $data['pmUser_id'];
			$query_params['Person_id'] = $res['Person_id'];
			
			$query = "";
			if ($data['bad_id'] == 2) {//свидетельство помечено как испорченное надо оживить человека
				$query = "
					select
						count(DeathSvid_id) as \"Count\"
					from
						v_DeathSvid DS 

					where
						DS.Person_id = :Person_id
						and COALESCE(DS.DeathSvid_IsBad, 1) = 1

						and DS.DeathSvid_id <> :Svid_id
					limit 1
				";

				$result = $this->db->query($query, array(
					'Person_id' => $query_params['Person_id'],
					'Svid_id' => $data['svid_id']
				));

				if (!is_object($result)) {
					return array(array('Error_Msg' => 'Ошибка запроса свидетельства о смерти!'));
				}

				$resp_arr = $result->result('array');

				if (!is_array($resp_arr) || count($resp_arr) == 0) {
					return array(array('Error_Msg' => 'Ошибка запроса свидетельства о смерти!'));
				} else if ($resp_arr[0]['Count'] > 0) {
					$query = ""; // не надо восстанавливать человека, т.к. есть ещё неиспорченное свидетельство.
				} else {
					$query = "
						select 
                        	Error_Code as \"Error_Code\", 
                            Error_Message as \"Error_Message\"
						from dbo.p_Person_revive(
							Person_id := :Person_id,
							pmUser_id := :pmUser_id);
					";
					
					$this->load->model('PersonRegister_model','PRegister_model');
					$res = $this->queryResult("
						select
							PR.PersonRegister_id as \"PersonRegister_id\"
						from v_PersonRegister PR
						where PR.Person_id = :Person_id
							and PR.PersonRegister_disDate is not null
							and PR.PersonRegisterOutCause_id = 1
							and (
								PR.PersonRegisterType_id in (62, 64) or
								PR.MorbusType_id = 94
							) -- Паллиативная помощь, суицид, туберкулез
					", ['Person_id' => $query_params['Person_id']]);
					foreach($res as $row) {
						$params = array_merge([
							'PersonRegister_id' => $row['PersonRegister_id'],
							'pmUser_id' => $data['pmUser_id']
						], getSessionParams());
						$this->PRegister_model->back($params);
					}
				}
			}
			if ($data['bad_id'] == 1) {//свидетельство помечено как действительное надо убить человека
				$query = "
					select Error_Code as \"Error_Code\", Error_Message as \"Error_Message\"
					from dbo.p_Person_kill(
						Person_id := :Person_id,
						PersonCloseCause_id := :PersonCloseCause_id,
						Person_deadDT := :Person_deadDT,
						pmUser_id := :pmUser_id)
				";				
				$query_params['PersonCloseCause_id'] = 1;
				$query_params['Person_deadDT'] = (!empty($res['DeathDate']) ? $res['DeathDate'] : $res['GiveDate']);
			}
			
			if (!empty($query) && !empty($query_params['Person_id'])) {
				/*print(getDebugSQL($query, $query_params));
				return array(array('Error_Msg' => ''));*/
				$result = $this->db->query($query, $query_params);
				$res = $result->result('array');
				$res = $res[0];
				if ($res['Error_Message'] != '') {
					return array(array('Error_Msg' => $res['Error_Message']));
				} else if ($data['bad_id'] == 1) {
					$this->load->library('swPersonRegister');
					swPersonRegister::onPersonDead($query_params);
				}
			}
		}

		$upd_query_params = array('Svid_id' => $data['svid_id']);

		if (in_array($table_name, array('DeathSvid', 'PntDeathSvid'))) {
			// меняем ещё и признак актуальности
			$isActual = "(case when ".$data['bad_id']." = 1 and ".$table_name."_isLose = 1 then 2 else 1 end)";
			// для свидетельств о смерти может быть только 1 актуальное свидетельство о смерти
			if ($table_name == 'DeathSvid') {
				$upd_query_params['Person_id'] = $query_params['Person_id'];
				$isActual = "(case when ".$data['bad_id']." = 1 and ".$table_name."_isLose = 1 and not exists(select ".$table_name."_id from $table_name where ".$table_name."_IsActual = 2 and ".$table_name."_id <> :Svid_id and Person_id = :Person_id limit 1) then 2 else 1 end)";
			}
			$query = "UPDATE {$table_name} SET ".$table_name."_isBad = ".$data['bad_id'].", ".$table_name."_IsActual = ".$isActual." WHERE ".$table_name."_id = :Svid_id";
		} else {
			$query = "UPDATE {$table_name} SET ".$table_name."_isBad = ".$data['bad_id']." WHERE ".$table_name."_id = :Svid_id";
		}

		$result = $this->db->query($query, $upd_query_params);
		
		if (in_array($table_name, array('BirthSvid', 'DeathSvid'))) {
			$this->load->model('ApprovalList_model');
			$this->ApprovalList_model->saveApprovalList(array(
				'ApprovalList_ObjectName' => $table_name,
				'ApprovalList_ObjectId' => $data['svid_id'],
				'pmUser_id' => $data['pmUser_id']
			));
		}

		$isEMDEnabled = $this->config->item('EMD_ENABLE');
		if ($data['bad_id'] == 2 && in_array($table_name, array('BirthSvid', 'DeathSvid')) && !empty($isEMDEnabled)) {
			$this->load->model('EMD_model');
			$EMDVersion_id = $this->EMD_model->setEMDVersionNotReady([
				'EMDRegistry_ObjectName' => $table_name,
				'EMDRegistry_ObjectID' => $data['svid_id']
			]);
			if (!empty($EMDVersion_id)) {
				// уже отправлен в РЭМД, выдаем сообщение об этом
				$resp_bs = $this->queryResult("
					select
						bs.{$table_name}_id as \"svid_id\",
						bs.{$table_name}_Ser as \"svid_ser\",
						bs.{$table_name}_Num as \"svid_num\",
						substring(ps.Person_SurName, 1, 1) as \"Person_SurName\",
						ps.Person_FirName as \"Person_FirName\",
						ps.Person_SecName as \"Person_SecName\"
					from
						v_{$table_name} bs
						inner join v_PersonState ps on ps.Person_id = bs.Person_rid
					where
						bs.{$table_name}_id = :svid_id
				", [
					'svid_id' => $data['svid_id']
				]);

				if (!empty($resp_bs[0]['svid_id'])) {
					return array(array('Error_Msg' => 'YesNo', 'Error_Code' => 100, 'Alert_Msg' => 'Медицинское свидетельство серия ' . $resp_bs[0]['svid_ser'] . ' номер ' . $resp_bs[0]['svid_num'] . ' передано получателю ' . $resp_bs[0]['Person_FirName'] . ' ' . $resp_bs[0]['Person_SecName'] . ' ' . $resp_bs[0]['Person_SurName'] . ' через сервис РЭМД. Для корректного отображения данных о свидетельстве необходимо оформить новое на основании текущего. Продолжить?'));
				}
			}
		}
		
		return array(array('Error_Msg' => ''));
	}
	
	/**
	 *	Получение данных для формы редактирования мед. свидетельства
	 */
	function loadMedSvidEditForm($data) {
		$svid_type = $data['svid_type'];
		$svid_id = $data['svid_id'];

		$join = "";
		$fields = "";

		if ($svid_type == "birth") {
			if (getRegionNick() == 'kz') {
				$join = " LEFT JOIN LATERAL(

						select Object_sid
						from v_ObjectSynchronLog OSL 

						where ObjectSynchronLogService_id = 2 and OSL.Object_id = d.BirthSvid_id
						limit 1
				    ) OSL ON true";
				$fields = ",case when OSL.Object_sid is null then 0 else 1 end as \"BirthSvid_isInRpn\"";
			} else {
				$fields = ",0 as \"BirthSvid_isInRpn\"";
			}

			$query = "
				SELECT 
					d.Server_id as \"Server_id\",
					d.BirthSvid_id as \"BirthSvid_id\",
					d.Lpu_id as \"Lpu_id\",
					d.Person_id as \"Person_id\",
					d.Person_rid as \"Person_rid\",
					COALESCE(rtrim(rp.Person_Surname)||' ','') || COALESCE(rtrim(rp.Person_Firname)||' ','') || COALESCE(rtrim(rp.Person_Secname),'') as \"Person_r_FIO\",

					d.BirthSvid_Ser as \"BirthSvid_Ser\",
					d.BirthSvid_Num as \"BirthSvid_Num\",
					d.MedStaffFact_id as \"MedStaffFact_id\",
					d.MedPersonal_id as \"MedPersonal_id\",
					d.LpuSection_id as \"LpuSection_id\",
					d.BirthMedPersonalType_id as \"BirthMedPersonalType_id\",
					d.ReceptType_id as \"ReceptType_id\",
					d.BirthEducation_id as \"BirthEducation_id\",
					d.BirthPlace_id as \"BirthPlace_id\",
					d.Sex_id as \"Sex_id\",
					d.BirthSvid_Week as \"BirthSvid_Week\",
					d.BirthSvid_ChildCount as \"BirthSvid_ChildCount\",
					d.BirthFamilyStatus_id as \"BirthFamilyStatus_id\",
					d.BirthEmployment_id as \"BirthEmployment_id\",
					d.BirthSpecialist_id as \"BirthSpecialist_id\",
					d.BirthSvid_ChildFamil as \"BirthSvid_ChildFamil\",
					d.BirthSvid_IsMnogoplod as \"BirthSvid_IsMnogoplod\",
					d.BirthSvid_PlodIndex as \"BirthSvid_PlodIndex\",
					d.BirthSvid_PlodCount as \"BirthSvid_PlodCount\",
					d.BirthSvid_IsFromMother as \"BirthSvid_IsFromMother\",
					d.BirthSvid_Height as \"BirthSvid_Height\",
					d.BirthSvid_Mass as \"BirthSvid_Mass\",
					d.OrgHead_id as \"OrgHead_id\",
					d.BirthChildResult_id as \"BirthChildResult_id\",
					d.Address_rid as \"Address_rid\",
					ba.Address_Zip as \"BAddress_Zip\",
					ba.KLCountry_id as \"BKLCountry_id\",
					ba.KLRGN_id as \"BKLRGN_id\",
					ba.KLSubRGN_id as \"BKLSubRGN_id\",
					ba.KLCity_id as \"BKLCity_id\",
					ba.KLTown_id as \"BKLTown_id\",
					ba.KLStreet_id as \"BKLStreet_id\",
					ba.Address_House as \"BAddress_House\",
					ba.Address_Corpus as \"BAddress_Corpus\",
					ba.Address_Flat as \"BAddress_Flat\",
					ba.Address_Address as \"BAddress_Address\",
					ba.Address_Address as \"BAddress_AddressText\",
					to_char(d.BirthSvid_GiveDate, 'DD.MM.YYYY') as \"BirthSvid_GiveDate\",

					to_char(d.BirthSvid_BirthDT, 'DD.MM.YYYY') as \"BirthSvid_BirthDT_Date\",

					substring(to_char(d.BirthSvid_BirthDT, 'HH24:MI:SS'),1,5) as \"BirthSvid_BirthDT_Time\",

					to_char(d.BirthSvid_RcpDate, 'DD.MM.YYYY') as \"BirthSvid_RcpDate\",

					d.BirthSvid_RcpDocument as \"BirthSvid_RcpDocument\",
					d.BirthSvid_IsBad as \"BirthSvid_IsBad\",
					d.DeputyKind_id as \"DeputyKind_id\",
					d.Okei_mid as \"Okei_mid\",
					d.LpuLicence_id as \"LpuLicence_id\",
					
					--d.BirthSvid_IsOtherMO as \"BirthSvid_IsOtherMO\",
					CASE WHEN d.BirthSvid_IsOtherMO=2 THEN 1 else 0 END as \"BirthSvid_IsOtherMO\",
					d.Org_id as \"Org_id\",
					d.MedStaffFact_cid as \"MedStaffFact_cid\",
					
					L.LpuLicence_Num as \"LpuLicence_Num\"
					{$fields}
				FROM
					BirthSvid d 

					left join v_Person_ER rp  on rp.Person_id = d.Person_rid

					left outer join Address ba  on ba.Address_id = d.Address_rid

				    left join LpuLicence L  on L.LpuLicence_id = d.LpuLicence_id

				    {$join}
				WHERE (1 = 1)
					AND BirthSvid_id = :svid_id
				LIMIT 1
			";

			$result = $this->db->query($query, array('svid_id' => $svid_id));
		}
		
		if ($svid_type == "death") {
			$query = "
				SELECT 
					d.DeathSvid_id as \"DeathSvid_id\",
					d.Server_id as \"Server_id\",
					d.Lpu_id as \"Lpu_id\",
					d.Person_id as \"Person_id\",
					d.Person_mid as \"Person_mid\",
					COALESCE(rtrim(mp.Person_Surname)||' ','') || COALESCE(rtrim(mp.Person_Firname)||' ','') || COALESCE(rtrim(mp.Person_Secname),'') as \"Person_m_FIO\",

					to_char(mp.Person_BirthDay, 'DD.MM.YYYY') as \"Mother_BirthDay\",

					d.Person_rid as \"Person_rid\",
					COALESCE(rtrim(rp.Person_Surname)||' ','') || COALESCE(rtrim(rp.Person_Firname)||' ','') || COALESCE(rtrim(rp.Person_Secname),'') as \"Person_r_FIO\",

					d.DeathSvid_PolFio as \"DeathSvid_PolFio\",
					d.MedStaffFact_id as \"MedStaffFact_id\",
					d.MedPersonal_id as \"MedPersonal_id\",
					d.LpuSection_id as \"LpuSection_id\",
					d.DeathSvidType_id as \"DeathSvidType_id\",
					d.DeathSvid_IsDuplicate as \"DeathSvid_IsDuplicate\",
					d.DeathSvid_IsLose as \"DeathSvid_IsLose\",
					d.ReceptType_id as \"ReceptType_id\",
					d.DeathCause_id as \"DeathCause_id\",
					d.DeathFamilyStatus_id as \"DeathFamilyStatus_id\",
					d.DeathPlace_id as \"DeathPlace_id\",
					d.DeathEducation_id as \"DeathEducation_id\",
					d.DeathTrauma_id as \"DeathTrauma_id\",
					d.DeathSetType_id as \"DeathSetType_id\",
					d.DeathSetCause_id as \"DeathSetCause_id\",
					d.DeathWomanType_id as \"DeathWomanType_id\",
					d.DeathEmployment_id as \"DeathEmployment_id\",
					d.DtpDeathTime_id as \"DtpDeathTime_id\",
					d.ChildTermType_id as \"ChildTermType_id\",
					d.Address_bid as \"Address_bid\",
					ba.Address_Zip as \"BAddress_Zip\",
					ba.KLCountry_id as \"BKLCountry_id\",
					ba.KLRGN_id as \"BKLRGN_id\",
					ba.KLSubRGN_id as \"BKLSubRGN_id\",
					ba.KLCity_id as \"BKLCity_id\",
					ba.KLTown_id as \"BKLTown_id\",
					ba.KLStreet_id as \"BKLStreet_id\",
					ba.Address_House as \"BAddress_House\",
					ba.Address_Corpus as \"BAddress_Corpus\",
					ba.Address_Flat as \"BAddress_Flat\",
					ba.Address_Address as \"BAddress_Address\",
					ba.Address_Address as \"BAddress_AddressText\",
					d.Address_did as \"Address_did\",
					da.Address_Zip as \"DAddress_Zip\",
					da.KLCountry_id as \"DKLCountry_id\",
					da.KLRGN_id as \"DKLRGN_id\",
					da.KLSubRGN_id as \"DKLSubRGN_id\",
					da.KLCity_id as \"DKLCity_id\",
					da.KLTown_id as \"DKLTown_id\",
					da.KLStreet_id as \"DKLStreet_id\",
					da.Address_House as \"DAddress_House\",
					da.Address_Corpus as \"DAddress_Corpus\",
					da.Address_Flat as \"DAddress_Flat\",
					da.Address_Address as \"DAddress_Address\",
					da.Address_Address as \"DAddress_AddressText\",
					d.Diag_iid as \"Diag_iid\",
					d.Diag_eid as \"Diag_eid\",
					d.Diag_mid as \"Diag_mid\",
					d.Diag_tid as \"Diag_tid\",
					d.Diag_oid as \"Diag_oid\",
					d.MedStaffFact_did as \"MedStaffFact_did\",
					d.Okei_id as \"Okei_id\",
					d.Okei_patid as \"Okei_patid\",
					d.Okei_domid as \"Okei_domid\",
					d.Okei_extid as \"Okei_extid\",
					d.Okei_impid as \"Okei_impid\",
					d.DeathSvid_IsPrimDiagIID as \"DeathSvid_IsPrimDiagIID\",
					d.DeathSvid_IsPrimDiagEID as \"DeathSvid_IsPrimDiagEID\",
					d.DeathSvid_IsPrimDiagMID as \"DeathSvid_IsPrimDiagMID\",
					d.DeathSvid_IsPrimDiagTID as \"DeathSvid_IsPrimDiagTID\",
					d.DeathSvid_BirthDateStr as \"DeathSvid_BirthDateStr\",
					d.DeathSvid_DeathDateStr as \"DeathSvid_DeathDateStr\",
					d.DeathSvid_Ser as \"DeathSvid_Ser\",
					d.DeathSvid_Num as \"DeathSvid_Num\",
					d.DeathSvid_OldSer as \"DeathSvid_OldSer\",
					d.DeathSvid_OldNum as \"DeathSvid_OldNum\",
					to_char(d.DeathSvid_GiveDate, 'DD.MM.YYYY') as \"DeathSvid_GiveDate\",

					to_char(d.DeathSvid_OldGiveDate, 'DD.MM.YYYY') as \"DeathSvid_OldGiveDate\",

					to_char(d.DeathSvid_DeathDate, 'DD.MM.YYYY') as \"DeathSvid_DeathDate_Date\",

					case when d.DeathSvid_IsNoDeathTime = 2 then null else substring(to_char(d.DeathSvid_DeathDate, 'HH24:MI:SS'),1,5) end as \"DeathSvid_DeathDate_Time\",

					case when d.DeathSvid_DeathDate is null and DeathSvid_DeathDateStr is null then 1 else 0 end as \"DeathSvid_IsUnknownDeathDate\",
					case when d.DeathSvid_IsNoDeathTime = 2 or d.DeathSvid_DeathDate is null then 1 else 0 end as \"DeathSvid_IsNoDeathTime\",
					case when d.DeathSvid_IsNoAccidentTime = 2 or d.DeathSvid_TraumaDate is null then 1 else 0 end as \"DeathSvid_IsNoAccidentTime\",
					case when d.DeathSvid_IsNoPlace = 2 then 1 else 0 end as \"DeathSvid_IsNoPlace\",
					case when d.DeathSvid_isBirthDate = 2 then 1 else 0 end as \"DeathSvid_isBirthDate\",
					d.DeathSvid_IsTerm as \"DeathSvid_IsTerm\",
					d.DeathSvid_Mass as \"DeathSvid_Mass\",
					d.DeathSvid_Month as \"DeathSvid_Month\",
					d.DeathSvid_Day as \"DeathSvid_Day\",
					d.DeathSvid_ChildCount as \"DeathSvid_ChildCount\",
					d.DeathSvid_TraumaDateStr as \"DeathSvid_TraumaDateStr\",
					to_char(d.DeathSvid_TraumaDate, 'DD.MM.YYYY') as \"DeathSvid_TraumaDate_Date\",

					case when d.DeathSvid_IsNoAccidentTime = 2 then null else substring(to_char(d.DeathSvid_TraumaDate, 'HH24:MI:SS'),1,5) end as \"DeathSvid_TraumaDate_Time\",

					--substring(to_char(d.DeathSvid_TraumaDate, 'HH24:MI:SS'),1,5) as DeathSvid_TraumaDate_Time,

					d.DeathSvid_TraumaDescr as \"DeathSvid_TraumaDescr\",
					d.DeathSvid_Oper as \"DeathSvid_Oper\",
					d.DeathSvid_PribPeriod as \"DeathSvid_PribPeriod\",
					d.DeathSvid_PribPeriodPat as \"DeathSvid_PribPeriodPat\",
					d.DeathSvid_PribPeriodDom as \"DeathSvid_PribPeriodDom\",
					d.DeathSvid_PribPeriodExt as \"DeathSvid_PribPeriodExt\",
					d.DeathSvid_PribPeriodImp as \"DeathSvid_PribPeriodImp\",
					d.DeathSvid_TimePeriod as \"DeathSvid_TimePeriod\",
					d.DeathSvid_TimePeriodPat as \"DeathSvid_TimePeriodPat\",
					d.DeathSvid_TimePeriodDom as \"DeathSvid_TimePeriodDom\",
					d.DeathSvid_TimePeriodExt as \"DeathSvid_TimePeriodExt\",
					d.DeathSvid_TimePeriodImp as \"DeathSvid_TimePeriodImp\",
					to_char(d.DeathSvid_RcpDate, 'DD.MM.YYYY') as \"DeathSvid_RcpDate\",
					d.DeathSvidRelation_id as \"DeathSvidRelation_id\",
					d.DeathSvid_RcpDocument as \"DeathSvid_RcpDocument\",
					d.DeathSvid_IsBad as \"DeathSvid_IsBad\",
					d.Person_hid as \"Person_hid\",
					d.OrgHeadPost_id as \"OrgHeadPost_id\",
					oh.OrgHead_id as \"OrgHead_id\",
					ohp.OrgHeadPost_Name as \"OrgHeadPost_Name\",
					(ph.Person_SurName||' '||ph.Person_FirName||' '||COALESCE(ph.Person_SecName,'')) as \"Person_hFIO\",

					pi.PersonInfo_IsParsDeath as \"PersonInfo_IsParsDeath\",
					pi.PersonInfo_IsSetDeath as \"PersonInfo_IsSetDeath\",
					d.MedPersonal_cid as \"MedPersonal_cid\",
					to_char(d.DeathSvid_checkDate, 'DD.MM.YYYY') as \"DeathSvid_checkDate\"
				FROM
					v_DeathSvid d 
					left join lateral (select * from v_Person_ER mp where  mp.Person_id = d.Person_mid limit 1) mp on true
					left join lateral (select * from v_Person_ER rp where  rp.Person_id = d.Person_rid limit 1) rp on true
					left outer join Address ba  on ba.Address_id = d.Address_bid
					left outer join Address da  on da.Address_id = d.Address_did
					left join v_OrgHead oh  on oh.Person_id = d.Person_hid and oh.OrgHeadPost_id = d.OrgHeadPost_id
					left join v_OrgHeadPost ohp  on ohp.OrgHeadPost_id = d.OrgHeadPost_id
					left join v_PersonState ph  on ph.Person_id = d.Person_hid
					left join v_PersonInfo pi  on pi.Person_id = d.Person_id
				WHERE (1 = 1)
					AND DeathSvid_id = :svid_id
				LIMIT 1";

			$result = $this->db->query($query, array('svid_id' => $svid_id));
		}
		
		if ($svid_type == "pntdeath") {
			$query = "
				SELECT
					d.PntDeathSvid_id as \"PntDeathSvid_id\",
					d.Server_id as \"Server_id\",
					d.Person_id as \"Person_id\",
					d.Person_cid as \"Person_cid\",
					d.Person_rid as \"Person_rid\",
					COALESCE(rtrim(rp.Person_Surname)||' ','') || COALESCE(rtrim(rp.Person_Firname)||' ','') || COALESCE(rtrim(rp.Person_Secname),'') as \"Person_r_FIO\",

					d.PntDeathSvid_PolFio as \"PntDeathSvid_PolFio\",
					d.PntDeathSvid_BirthDateStr as \"PntDeathSvid_BirthDateStr\",
					d.PntDeathSvid_DeathDateStr as \"PntDeathSvid_DeathDateStr\",
					d.PntDeathSvid_Ser as \"PntDeathSvid_Ser\",
					d.PntDeathSvid_Num as \"PntDeathSvid_Num\",
					d.PntDeathPeriod_id as \"PntDeathPeriod_id\",
					d.DeathSvidType_id as \"DeathSvidType_id\",
					d.PntDeathSvid_IsDuplicate as \"PntDeathSvid_IsDuplicate\",
					d.PntDeathSvid_IsLose as \"PntDeathSvid_IsLose\",
					d.PntDeathSvid_OldSer as \"PntDeathSvid_OldSer\",
					d.PntDeathSvid_OldNum as \"PntDeathSvid_OldNum\",
					d.MedStaffFact_id as \"MedStaffFact_id\",
					d.MedPersonal_id as \"MedPersonal_id\",
					d.LpuSection_id as \"LpuSection_id\",
					d.PntDeathSvid_DeathDate as \"PntDeathSvid_DeathDate\",
					to_char(d.PntDeathSvid_DeathDate, 'DD.MM.YYYY') as \"PntDeathSvid_DeathDate_Date\",

					substring(to_char(d.PntDeathSvid_DeathDate, 'HH24:MI:SS'),1,5) as \"PntDeathSvid_DeathDate_Time\",

					d.ReceptType_id as \"ReceptType_id\",
					d.PntDeathSvid_ChildFio as \"PntDeathSvid_ChildFio\",
					to_char(d.PntDeathSvid_ChildBirthDT, 'DD.MM.YYYY') as \"PntDeathSvid_ChildBirthDT_Date\",

					substring(to_char(d.PntDeathSvid_ChildBirthDT, 'HH24:MI:SS'),1,5) as \"PntDeathSvid_ChildBirthDT_Time\",

					d.PntDeathSvid_PlodIndex as \"PntDeathSvid_PlodIndex\",
					d.PntDeathSvid_PlodCount as \"PntDeathSvid_PlodCount\",
					d.PntDeathSvid_RcpDoc as \"PntDeathSvid_RcpDoc\",
					to_char(d.PntDeathSvid_RcpDate, 'DD.MM.YYYY') as \"PntDeathSvid_RcpDate\",

					d.PntDeathFamilyStatus_id as \"PntDeathFamilyStatus_id\",
					d.DeathEmployment_id as \"DeathEmployment_id\",
					d.PntDeathPlace_id as \"PntDeathPlace_id\",
					d.PntDeathEducation_id as \"PntDeathEducation_id\",
					d.Sex_id as \"Sex_id\",
					d.PntDeathSvid_ChildCount as \"PntDeathSvid_ChildCount\",
					d.PntDeathSvid_BirthCount as \"PntDeathSvid_BirthCount\",
					d.PntDeathGetBirth_id as \"PntDeathGetBirth_id\",
					d.PntDeathTime_id as \"PntDeathTime_id\",
					d.PntDeathCause_id as \"PntDeathCause_id\",
					d.PntDeathSetType_id as \"PntDeathSetType_id\",
					d.PntDeathSetCause_id as \"PntDeathSetCause_id\",
					d.MedStaffFact_did as \"MedStaffFact_did\",
					d.Diag_iid as \"Diag_iid\",
					d.Diag_eid as \"Diag_eid\",
					d.Diag_mid as \"Diag_mid\",
					d.Diag_tid as \"Diag_tid\",
					d.Diag_oid as \"Diag_oid\",
					case when d.PntDeathSvid_IsNoPlace = 2 then 1 else 0 end as \"PntDeathSvid_IsNoPlace\",
					d.PntDeathSvid_Mass as \"PntDeathSvid_Mass\",
					d.PntDeathSvid_Height as \"PntDeathSvid_Height\",
					d.PntDeathSvid_IsMnogoplod as \"PntDeathSvid_IsMnogoplod\",
					to_char(d.PntDeathSvid_GiveDate, 'DD.MM.YYYY') as \"PntDeathSvid_GiveDate\",

					to_char(d.PntDeathSvid_OldGiveDate, 'DD.MM.YYYY') as \"PntDeathSvid_OldGiveDate\",

					da.Address_Zip as \"DAddress_Zip\",
					da.KLCountry_id as \"DKLCountry_id\",
					da.KLRGN_id as \"DKLRGN_id\",
					da.KLSubRGN_id as \"DKLSubRGN_id\",
					da.KLCity_id as \"DKLCity_id\",
					da.KLTown_id as \"DKLTown_id\",
					da.KLStreet_id as \"DKLStreet_id\",
					da.Address_House as \"DAddress_House\",
					da.Address_Corpus as \"DAddress_Corpus\",
					da.Address_Flat as \"DAddress_Flat\",
					da.Address_Address as \"DAddress_Address\",
					da.Address_Address as \"DAddress_AddressText\",
					d.DeputyKind_id as \"DeputyKind_id\",
					d.PntDeathSvid_ActNumber as \"PntDeathSvid_ActNumber\",
					to_char(d.PntDeathSvid_ActDT, 'DD.MM.YYYY') as \"PntDeathSvid_ActDT\",

					OH.OrgHead_id as \"OrgHead_id\",
					d.Org_id as \"Org_id\",
					d.PntDeathSvid_ZagsFIO as \"PntDeathSvid_ZagsFIO\",
					d.PntDeathSvid_IsFromMother as \"PntDeathSvid_IsFromMother\",
					d.PntDeathSvidType_id as \"PntDeathSvidType_id\",
					pi.PersonInfo_IsParsDeath as \"PersonInfo_IsParsDeath\",
					pi.PersonInfo_IsSetDeath as \"PersonInfo_IsSetDeath\"
				FROM
					v_PntDeathSvid d 

					left join v_Person_ER rp  on rp.Person_id = d.Person_rid

					left outer join Address da  on da.Address_id = d.Address_did

					left join v_PersonInfo pi  on pi.Person_id = d.Person_id

					left join v_OrgHead OH  on OH.Person_id = d.Person_hid

				WHERE (1 = 1)
					AND PntDeathSvid_id = :svid_id
				LIMIT 1
			";
			
			$result = $this->db->query($query, array('svid_id' => $svid_id));
		}

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 *	Получение данных для печати мед. свидетельства
	 */
	function getMedSvidFields($data, $svid_type) {
		$svid_id = $data['svid_id'];
		
		if ($svid_type == "birth") {
			$query = "
				SELECT 
					d.Server_id as \"Server_id\",
					d.Server_id as \"MSBE_Server_id\",
					d.BirthSvid_id as \"BirthSvid_id\",
					d.Lpu_id as \"Lpu_id\",
					d.Person_id as \"Person_id\",
					d.Person_id as \"MSBE_Person_id\",
					d.Person_rid as \"Person_rid\",
					COALESCE(rtrim(p.Person_Surname)||' ','') || COALESCE(rtrim(p.Person_Firname)||' ','') || COALESCE(rtrim(p.Person_Secname),'') as \"Person_FIO\",

					to_char(p.Person_BirthDay, 'DD.MM.YYYY') as \"Person_BirthDay\",

					COALESCE(rtrim(rp.Person_Surname)||' ','') || COALESCE(rtrim(rp.Person_Firname)||' ','') || COALESCE(rtrim(rp.Person_Secname),'') as \"birthsvid_PolFio\",

					d.BirthSvid_Ser as \"BirthSvid_Ser\",
					d.BirthSvid_Num as \"BirthSvid_Num\",
					d.MedStaffFact_id as \"MedStaffFact_id\",
					d.MedPersonal_id as \"MedPersonal_id\",
					d.BirthMedPersonalType_id as \"BirthMedPersonalType_id\",
					d.ReceptType_id as \"ReceptType_id\",
					d.BirthEducation_id as \"BirthEducation_id\",
					be.BirthEducation_Code as \"BirthEducation_Code\",
					d.BirthPlace_id as \"BirthPlace_id\",
					d.Sex_id as \"BirthSex_id\",
					d.BirthSvid_Week as \"BirthSvid_Week\",
					d.BirthSvid_ChildCount as \"BirthSvid_ChildCount\",
					bfs.BirthFamilyStatus_Code as \"BirthFamilyStatus_Code\",
					bfs.BirthFamilyStatus_Name as \"BirthFamilyStatus_Name\",
					d.BirthFamilyStatus_id as \"BirthFamilyStatus_id\",
					d.BirthEmployment_id as \"BirthZanat_id\",
					d.BirthSpecialist_id as \"BirthSpecialist_id\",
					d.BirthSvid_ChildFamil as \"BirthSvid_ChildFamil\",
					d.BirthSvid_IsMnogoplod as \"BirthSvid_IsMnogoplod\",
					d.BirthSvid_PlodIndex as \"BirthSvid_PlodIndex\",
					d.BirthSvid_PlodCount as \"BirthSvid_PlodCount\",
					d.BirthSvid_IsFromMother as \"BirthSvid_IsFromMother\",
					d.BirthSvid_Height as \"BirthSvid_Height\",
					d.BirthSvid_Mass as \"BirthSvid_Mass\",
					d.BirthChildResult_id as \"BirthChildResult_id\",
					ba.KLCountry_id as \"BKLCountry_id\",
					ba.KLRGN_Name as \"BKLRGN_Name\",
					ba.KLSubRGN_Name as \"BKLSubRGN_Name\",
					ba.KLCity_Name as \"BKLCity_Name\",
					ba.KLTown_Name as \"BKLTown_Name\",
					ba.KlareaType_id as \"BKlareaType_id\",
					ba.KLSubRGN_id as \"BKLSubRGN_id\",
					ba.KLCity_id as \"BKLCity_id\",
					ba.KLTown_id as \"BKLTown_id\",
					ba.KLStreet_id as \"BKLStreet_id\",
					ba.Address_House as \"BAddress_House\",
					ba.Address_Corpus as \"BAddress_Corpus\",
					ba.Address_Flat as \"BAddress_Flat\",
					(COALESCE(ba.KLCity_Name,'') || COALESCE(case when ba.KLCity_Name is not null then ', ' else '' end || COALESCE(ba.KLTown_Socr||'. ','') || ba.KLTown_Name,'')) as \"BKLAddress_Summ\",

					pa.KLRGN_Name as \"KLRGN_Name\",
					pa.KLSubRGN_Name as \"KLSubRGN_Name\",
					pa.KLCity_Name as \"KLCity_Name\",
					pa.KLTown_Name as \"KLTown_Name\",
					(pa.KLStreet_name || ' ' || case when pa.KLStreet_Socr = 'УЛ' then '' else pa.KLStreet_Socr end) as \"KLStreet_name\",
					pa.Address_House as \"Address_House\",
					pa.Address_Flat as \"Address_Flat\",
					pa.KlareaType_id as \"KlareaType_id\",
					(COALESCE(pa.KLCity_Name,'') || COALESCE(case when pa.KLCity_Name is not null then ', ' else '' end || COALESCE(pa.KLTown_Socr||'. ','') || pa.KLTown_Name,'')) as \"KLAddress_Summ\",

					l.Lpu_Name as \"Lpu_Name\",
					(ll.LpuLicence_Ser || ' ' || ll.LpuLicence_Num) as \"LpuLicence_Num\",
					l.UAddress_Address as \"orgaddress_uaddress\",
					l.Org_OKPO as \"org_okpo\",
					(COALESCE(ohp.Person_SurName||' ','') || COALESCE(ohp.Person_FirName||' ','') || COALESCE(ohp.Person_SecName,'')) as \"OrgHead_Fio\",

					COALESCE(post.PostMed_Name, '') as \"MedPersonal_Post\",

					mp.Person_Fio as \"MedPersonal_FIO\",
					to_char(d.BirthSvid_GiveDate, 'DD.MM.YYYY') as \"BirthSvid_GiveDate\",

					to_char(d.BirthSvid_BirthDT, 'DD.MM.YYYY') as \"BirthSvid_BirthDate\",

					substring(to_char(d.BirthSvid_BirthDT, 'HH24:MI:SS'),1,5) as \"BirthSvid_BirthDT_Time\",

					to_char(d.BirthSvid_RcpDate, 'DD.MM.YYYY') as \"BirthSvid_RcpDate\",

					d.BirthSvid_RcpDocument as \"BirthSvid_RcpDocument\",
					d.BirthSvid_IsBad as \"BirthSvid_IsBad\",
					dk.DeputyKind_Name as \"DeputyKind_Name\",
					d.Okei_mid as \"Okei_mid\",
					(select Okei_NationSymbol from v_Okei o  WHERE o.Okei_id = d.Okei_mid) as \"Okei_mid_Okei_NationSymbol\",

					LLic.LpuLicence_Num as \"LpuLicence_Num\",
					to_char(LLic.LpuLicence_setDate, 'DD.MM.YYYY') as \"LpuLicence_setDate\"

				FROM
					BirthSvid d 

					left join v_Person_ER p  on p.Person_id = d.Person_id

					left join DeputyKind dk  on dk.DeputyKind_id = d.DeputyKind_id

					left join LpuLicence LLic  on LLic.LpuLicence_id = d.LpuLicence_id

					left outer join v_Person_ER rp  on rp.Person_id = d.Person_rid

					left outer join v_Address_all ba  on ba.Address_id = d.Address_rid

					left outer join v_Address_all pa  on pa.Address_id = p.UAddress_id

					left outer join v_Lpu_all l  on l.Lpu_id = d.Lpu_id

					left outer join v_MedPersonal mp  on mp.MedPersonal_id = d.MedPersonal_id

					left outer join v_MedStaffFact msf  on msf.MedStaffFact_id = d.MedStaffFact_id

					left outer join v_PostMed post  on post.PostMed_id = msf.Post_id

					left outer join v_BirthEducation be  on be.BirthEducation_id = d.BirthEducation_id

					left outer join v_BirthFamilyStatus bfs  on bfs.BirthFamilyStatus_id = d.BirthFamilyStatus_id

					left join v_OrgHead oh  on oh.OrgHead_id = d.OrgHead_id

					left outer join v_PersonState ohp  on ohp.Person_id = oh.Person_id

					LEFT JOIN LATERAL (select  * from LpuLicence 


									where Lpu_id =l.lpu_id and( LpuLicence_endDate>d.BirthSvid_BirthDT
									or LpuLicence_endDate is null)
                                    limit 1) as ll ON true
				WHERE (1 = 1)
					AND BirthSvid_id = :svid_id
				LIMIT 1";

			$result = $this->db->query($query, array('svid_id' => $svid_id));
		}
		
		if ($svid_type == "death") {
			$query = "
				SELECT 
					COALESCE(rtrim(p.Person_Surname)||' ','') || COALESCE(rtrim(p.Person_Firname)||' ','') || COALESCE(rtrim(p.Person_Secname),'') as \"Person_FIO\",

					p.Sex_id as \"PersonSex_id\",
					COALESCE(to_char(p.Person_BirthDay, 'DD.MM.YYYY'), d.DeathSvid_BirthDateStr) as \"Person_BirthDay\",


					d.Server_id as \"Server_id\",
					d.Server_id as \"MSDE_Server_id\",
					d.DeathSvid_id as \"DeathSvid_id\",
					d.Lpu_id as \"Lpu_id\",
					d.Person_id as \"Person_id\",
					d.Person_id as \"MSDE_Person_id\",
					d.Person_mid as \"Person_mid\",
					rtrim(mp.Person_Surname) || ' ' || rtrim(mp.Person_Firname) || ' ' || rtrim(mp.Person_Secname) as \"DeathSvid_MotherFio\",
					rtrim(mp.Person_Surname) as \"DeathSvid_MotherFamaly\",
					rtrim(mp.Person_Firname) as \"DeathSvid_MotherName\",
					rtrim(mp.Person_Secname) as \"DeathSvid_MotherSecName\",
					to_char(mp.Person_BirthDay, 'DD.MM.YYYY') as \"DeathSvid_MotherBirthday\",

					d.Person_rid as \"Person_rid\",
					rtrim(rp.Person_Surname) || ' ' || rtrim(rp.Person_Firname) || ' ' || rtrim(rp.Person_Secname) as \"DeathSvid_PolFio\",
					d.MedStaffFact_id as \"MedStaffFact_id\",
					d.MedPersonal_id as \"MedPersonal_id\",
					d.DeathSvidType_id as \"DeathSvidType_id\",
					d.DeathSvid_IsDuplicate as \"DeathSvid_IsDuplicate\",
					d.DeathSvid_IsLose as \"DeathSvid_IsLose\",
					d.ReceptType_id as \"ReceptType_id\",
					d.DeathCause_id as \"DeathCause_id\",
					d.DeathPlace_id as \"DeathPlace_id\",
					dp.DeathPlace_Code as \"DeathPlace_Code\",
					de.DeathEducation_Code as \"DeathEducation_Code\",
					d.DeathTrauma_id as \"DeathTrauma_id\",
					d.DeathSetType_id as \"DeathSetType_id\",
					d.DeathSetCause_id as \"DeathSetCause_id\",
					d.DeathWomanType_id as \"DeathWomanType_id\",
					d.DeathEmployment_id as \"DeathZanat_id\",
					dfs.DeathFamilyStatus_Code as \"DeathFamilyStatus_Code\",
					dfs.DeathFamilyStatus_Name as \"DeathFamilyStatus_Name\",
					d.DtpDeathTime_id as \"DeathDtpType_id\",
					d.ChildTermType_id as \"DonosType_id\",
					ba.Address_Zip as \"BAddress_Zip\",
					ba.KLCountry_id as \"BKLCountry_id\",
					ba.KLRGN_Name as \"BKLRGN_Name\",
					ba.KLSubRGN_Name as \"BKLSubRGN_Name\",
					ba.KLCity_Name as \"BKLCity_Name\",
					ba.KLTown_Name as \"BKLTown_Name\",
					(ba.KLStreet_name || ' ' || case when ba.KLStreet_Socr = 'УЛ' then '' else ba.KLStreet_Socr end) as \"BKLStreet_name\",
					ba.KlareaType_id as \"BKlareaType_id\",
					ba.Address_House as \"BAddress_House\",
					ba.Address_Corpus as \"BAddress_Corpus\",
					ba.Address_Flat as \"BAddress_Flat\",
					ba.Address_Address as \"BAddress_Address\",
					(COALESCE(ba.KLCity_Name,'') || COALESCE(case when ba.KLCity_Name is not null then ', ' else '' end || COALESCE(ba.KLTown_Socr||'. ','') || ba.KLTown_Name,'')) as \"BKLAddress_Summ\",

					da.Address_Zip as \"DAddress_Zip\",
					da.KLCountry_id as \"DKLCountry_id\",
					da.KLRGN_Name as \"DKLRGN_Name\",
					da.KLSubRGN_Name as \"DKLSubRGN_Name\",
					da.KLCity_Name as \"DKLCity_Name\",
					da.KLTown_Name as \"DKLTown_Name\",
					(da.KLStreet_name || ' ' || case when da.KLStreet_Socr = 'УЛ' then '' else da.KLStreet_Socr end) as \"DKLStreet_name\",
					da.KlareaType_id as \"DKlareaType_id\",
					da.Address_House as \"DAddress_House\",
					da.Address_Corpus as \"DAddress_Corpus\",
					da.Address_Flat as \"DAddress_Flat\",
					da.Address_Address as \"DAddress_Address\",
					(COALESCE(da.KLCity_Name,'') || COALESCE(case when da.KLCity_Name is not null then ', ' else '' end || COALESCE(da.KLTown_Socr||'. ','') || da.KLTown_Name,'')) as \"DKLAddress_Summ\",

					pa.Address_Zip as \"Address_Zip\",
					pa.KLCountry_id as \"KLCountry_id\",
					pa.KLRGN_Name as \"KLRGN_Name\",
					pa.KLSubRGN_Name as \"KLSubRGN_Name\",
					pa.KLCity_Name as \"KLCity_Name\",
					pa.KLTown_Name as \"KLTown_Name\",
					(pa.KLStreet_name || ' ' || case when pa.KLStreet_Socr = 'УЛ' then '' else pa.KLStreet_Socr end) as \"KLStreet_name\",
					pa.KlareaType_id as \"KlareaType_id\",
					pa.Address_House as \"Address_House\",
					pa.Address_Corpus as \"Address_Corpus\",
					pa.Address_Flat as \"Address_Flat\",
					pa.Address_Address as \"Address_Address\",
					(COALESCE(pa.KLCity_Name,'') || COALESCE(case when pa.KLCity_Name is not null then ', ' else '' end || COALESCE(pa.KLTown_Socr||'. ','') || pa.KLTown_Name,'')) as \"KLAddress_Summ\",

					d.Diag_iid as \"Diag_iid\",
					d.Diag_eid as \"Diag_eid\",
					d.Diag_mid as \"Diag_mid\",
					d.Diag_tid as \"Diag_tid\",
					d.Diag_oid as \"Diag_oid\",
					d.DeathSvid_Ser as \"DeathSvid_Ser\",
					d.DeathSvid_Num as \"DeathSvid_Num\",
					d.DeathSvid_OldSer as \"DeathSvid_OldSer\",
					d.DeathSvid_OldNum as \"DeathSvid_OldNum\",
					to_char(d.DeathSvid_GiveDate, 'DD.MM.YYYY') as \"DeathSvid_GiveDate\",

					to_char(d.DeathSvid_OldGiveDate, 'DD.MM.YYYY') as \"DeathSvid_disDate\",

					to_char(d.DeathSvid_DeathDate, 'DD.MM.YYYY') as \"DeathSvid_DeathDate_Date\",

					COALESCE(to_char(d.DeathSvid_DeathDate, 'DD.MM.YYYY'), d.DeathSvid_DeathDateStr) as \"DeathSvid_DeathDate_Date\",


					substring(to_char(d.DeathSvid_DeathDate, 'HH24:MI:SS'),1,5) as \"DeathSvid_DeathDate_Time\",

					--to_char(d.DeathSvid_DeathDate, 'HH24:MI:SS') as DeathSvid_DeathDate_Time,

					d.DeathSvid_IsTerm as \"DeathSvid_IsTerm\",
					d.DeathSvid_Mass as \"DeathSvid_Mass\",
					d.DeathSvid_Month as \"DeathSvid_Month\",
					d.DeathSvid_Day as \"DeathSvid_Day\",
					d.DeathSvid_ChildCount as \"DeathSvid_ChildCount\",
					to_char(d.DeathSvid_TraumaDate, 'DD.MM.YYYY') as \"DeathSvid_TraumaDate_Date\",

					substring(to_char(d.DeathSvid_TraumaDate, 'HH24:MI:SS'),1,5) as \"DeathSvid_TraumaDate_Time\",

					d.DeathSvid_TraumaDescr as \"DeathSvid_TraumaObst\",
					d.DeathSvid_Oper as \"DeathSvid_Oper\",
					d.DeathSvid_PribPeriod as \"DeathSvid_PribPeriod\",
					d.DeathSvid_PribPeriodPat as \"DeathSvid_PribPeriodPat\",
					d.DeathSvid_PribPeriodDom as \"DeathSvid_PribPeriodDom\",
					d.DeathSvid_PribPeriodExt as \"DeathSvid_PribPeriodExt\",
					d.DeathSvid_PribPeriodImp as \"DeathSvid_PribPeriodImp\",
					d.DeathSvid_TimePeriod as \"DeathSvid_TimePeriod\",
					d.DeathSvid_TimePeriodPat as \"DeathSvid_TimePeriodPat\",
					d.DeathSvid_TimePeriodDom as \"DeathSvid_TimePeriodDom\",
					d.DeathSvid_TimePeriodExt as \"DeathSvid_TimePeriodExt\",
					d.DeathSvid_TimePeriodImp as \"DeathSvid_TimePeriodImp\",
					to_char(d.DeathSvid_RcpDate, 'DD.MM.YYYY') as \"DeathSvid_PolDate\",

					d.DeathSvid_RcpDocument as \"DeathSvid_PolDoc\",
					d.DeathSvid_IsBad as \"DeathSvid_IsBad\",
					d1.Diag_Name as \"Diag1_Name\",
					d2.Diag_Name as \"Diag2_Name\",
					d3.Diag_Name as \"Diag3_Name\",
					d4.Diag_Name as \"Diag4_Name\",
					d5.Diag_Name as \"Diag5_Name\",
					d1.Diag_Code as \"Diag1_Code\",
					d2.Diag_Code as \"Diag2_Code\",
					d3.Diag_Code as \"Diag3_Code\",
					d4.Diag_Code as \"Diag4_Code\",
					d5.Diag_Code as \"Diag5_Code\",
					l.Lpu_Name as \"Lpu_Name\",
					l.UAddress_Address as \"orgaddress_uaddress\",
					l.Org_OKPO as \"org_okpo\",	
					--(COALESCE(ohp.Person_SurName+' ','') + COALESCE(ohp.Person_FirName+' ','') + COALESCE(ohp.Person_SecName,'')) as glvrach,

					COALESCE(post.PostMed_Name, '') as \"MedPersonal_Post\",

					medp.Person_Fio as \"MedPersonal_FIO\",
					dst.DeathSvidType_Name as \"DeathSvidType_Name\",
					case
						when d.Person_hid is null then ''
						else (ohpers.Person_SurName||' '||ohpers.Person_FirName||' '||COALESCE(ohpers.Person_SecName,''))

					end as \"OrgHead_Fio\",
					case when d.OrgHeadPost_id is null then '' else ohpost.OrgHeadPost_Name end as \"OrgHeadPost_Name\"
				FROM
					v_DeathSvid d 

					left join v_Person_ER p  on p.Person_id = d.Person_id

					left outer join v_Person_ER mp  on mp.Person_id = d.Person_mid

					left outer join v_Person_ER rp  on rp.Person_id = d.Person_rid

					left outer join v_Address_all ba  on ba.Address_id = d.Address_bid

					left outer join v_Address_all da  on da.Address_id = d.Address_did

					left outer join v_Address_all pa  on pa.Address_id = p.UAddress_id

					left outer join Diag d1  on d1.Diag_id = d.Diag_iid

					left outer join Diag d2  on d2.Diag_id = d.Diag_tid

					left outer join Diag d3  on d3.Diag_id = d.Diag_mid

					left outer join Diag d4  on d4.Diag_id = d.Diag_eid

					left outer join Diag d5  on d5.Diag_id = d.Diag_oid

					left outer join v_DeathEducation de  on de.DeathEducation_id = d.DeathEducation_id

					left outer join v_DeathFamilyStatus dfs  on dfs.DeathFamilyStatus_id = d.DeathFamilyStatus_id

					left outer join v_MedPersonal medp  on medp.MedPersonal_id = d.MedPersonal_id

					left outer join v_MedStaffFact msf  on msf.MedStaffFact_id = d.MedStaffFact_id

					left outer join v_PostMed post  on post.PostMed_id = msf.Post_id

					left outer join v_Lpu_all l  on l.Lpu_id = d.Lpu_id

					left outer join DeathSvidType dst  on dst.DeathSvidType_id = d.DeathSvidType_id

					left outer join DeathPlace dp  on dp.DeathPlace_id = d.DeathPlace_id

					/*left join v_OrgHead oh  on oh.Lpu_id = d.Lpu_id and oh.OrgHeadPost_id = 1

					left outer join v_PersonState ohp  on ohp.Person_id = oh.Person_id*/

					left join v_PersonState ohpers  on ohpers.Person_id = d.Person_hid

					left join v_OrgHeadPost ohpost  on ohpost.OrgHeadPost_id = d.OrgHeadPost_id

				WHERE (1 = 1)
					AND DeathSvid_id = :svid_id
				LIMIT 1";

			$result = $this->db->query($query, array('svid_id' => $svid_id));
		}

		if ($svid_type == "pntdeath") {
			$query = "
				SELECT 
					COALESCE(rtrim(p.Person_Surname)||' ','') || COALESCE(rtrim(p.Person_Firname)||' ','') || COALESCE(rtrim(p.Person_Secname),'') as \"Person_FIO\",

					d.Sex_id,
					(case
						when pdgb.PntDeathGetBirth_Code = 1 then 1
						when pdgb.PntDeathGetBirth_Code in (2,3) then 2
						when pdgb.PntDeathGetBirth_Code = 4 then 3
					end) as \"PntDeathGetBirth_Code\",
					d.PntDeathSvid_PlodIndex as \"PntDeathSvid_PlodIndex\",
					d.PntDeathSvid_PlodCount as \"PntDeathSvid_PlodCount\",
					d.PntDeathTime_id as \"PntDeathTime_id\",
					d.PntDeathSvid_ChildFio as \"PntDeathSvid_ChildFio\",
					to_char(p.Person_BirthDay, 'DD.MM.YYYY') as \"Person_BirthDay\",

					COALESCE(to_char(d.PntDeathSvid_ChildBirthDT, 'DD.MM.YYYY'), d.PntDeathSvid_DeathDateStr) as \"PntDeathSvid_ChildBirthDT\",


					substring(to_char(d.PntDeathSvid_ChildBirthDT, 'HH24:MI:SS'),1,5) as \"PntDeathSvid_ChildBirthDT_Time\",

					d.Server_id as \"Server_id\",
					d.Server_id as \"MSDE_Server_id\",
					d.PntDeathSvid_id as \"PntDeathSvid_id\",
					d.Lpu_id as \"Lpu_id\",
					d.Person_id as \"Person_id\",
					d.Person_id as \"MSDE_Person_id\",
					d.Person_rid as \"Person_rid\",
					COALESCE(rtrim(rp.Person_Surname)||' ','') || COALESCE(rtrim(rp.Person_Firname)||' ','') || COALESCE(rtrim(rp.Person_Secname),'') as \"PntDeathSvid_PolFio\",

					d.MedStaffFact_id as \"MedStaffFact_id\",
					d.MedPersonal_id as \"MedPersonal_id\",
					d.DeathSvidType_id as \"DeathSvidType_id\",
					d.ReceptType_id as \"ReceptType_id\",
					d.PntDeathCause_id as \"PntDeathCause_id\",
					pdfs.PntDeathFamilyStatus_Code as \"PntDeathFamilyStatus_Code\",
					pdfs.PntDeathFamilyStatus_Name as \"PntDeathFamilyStatus_Name\",
					d.PntDeathPlace_id as \"PntDeathPlace_id\",
					pde.PntDeathEducation_Code as \"PntDeathEducation_Code\",
					d.PntDeathSetType_id as \"PntDeathSetType_id\",
					d.PntDeathSetCause_id as \"PntDeathSetCause_id\",
					d.PntDeathCause_id as \"PntDeathBirthCause_id\",
					coalesce(b.BirthPlace_id, d.PntDeathPlace_id) as \"BirthPlace_id\",
					d.DeathEmployment_id as \"PntDeathZanat_id\",
					da.Address_Zip as \"DAddress_Zip\",
					da.KLCountry_id as \"DKLCountry_id\",
					da.KLRGN_Name as \"DKLRGN_Name\",
					da.KLSubRGN_Name as \"DKLSubRGN_Name\",
					da.KLCity_Name as \"DKLCity_Name\",
					da.KLTown_Name as \"DKLTown_Name\",
					(da.KLStreet_name || ' ' || case when da.KLStreet_Socr = 'УЛ' then '' else da.KLStreet_Socr end) as \"DKLStreet_name\",
					da.KlareaType_id as \"DKlareaType_id\",
					da.Address_House as \"DAddress_House\",
					da.Address_Corpus as \"DAddress_Corpus\",
					da.Address_Flat as \"DAddress_Flat\",
					da.Address_Address as \"DAddress_Address\",
					(COALESCE(da.KLCity_Name,'') || COALESCE(case when da.KLCity_Name is not null then ', ' else '' end || COALESCE(da.KLTown_Socr||'. ','') || da.KLTown_Name,'')) as \"DKLAddress_Summ\",

					pa.Address_Zip as \"Address_Zip\",
					pa.KLCountry_id as \"KLCountry_id\",
					pa.KLRGN_Name as \"KLRGN_Name\",
					pa.KLSubRGN_Name as \"KLSubRGN_Name\",
					pa.KLCity_Name as \"KLCity_Name\",
					pa.KLTown_Name as \"KLTown_Name\",
					(pa.KLStreet_name || ' ' || case when pa.KLStreet_Socr = 'УЛ' then '' else pa.KLStreet_Socr end) as \"KLStreet_name\",
					pa.KlareaType_id as \"KlareaType_id\",
					pa.Address_House as \"Address_House\",
					pa.Address_Corpus as \"Address_Corpus\",
					pa.Address_Flat as \"Address_Flat\",
					pa.Address_Address as \"Address_Address\",
					(COALESCE(pa.KLCity_Name,'') || COALESCE(case when pa.KLCity_Name is not null then ', ' else '' end || COALESCE(pa.KLTown_Socr||'. ','') || pa.KLTown_Name,'')) as \"KLAddress_Summ\",

					d.Diag_iid as \"Diag_iid\",
					d.Diag_eid as \"Diag_eid\",
					d.Diag_mid as \"Diag_mid\",
					d.Diag_tid as \"Diag_tid\",
					d.Diag_oid as \"Diag_oid\",
					d.PntDeathSvid_Ser as \"PntDeathSvid_Ser\",
					d.PntDeathSvid_Num as \"PntDeathSvid_Num\",
					d.PntDeathSvid_OldSer as \"PntDeathSvid_OldSer\",
					d.PntDeathSvid_OldNum as \"PntDeathSvid_OldNum\",
					to_char(d.PntDeathSvid_GiveDate, 'DD.MM.YYYY') as \"PntDeathSvid_GiveDate\",

					to_char(d.PntDeathSvid_OldGiveDate, 'DD.MM.YYYY') as \"PntDeathSvid_disDate\",

					COALESCE(to_char(d.PntDeathSvid_DeathDate, 'DD.MM.YYYY'), d.PntDeathSvid_DeathDateStr) as \"PntDeathSvid_DeathDate\",


					substring(to_char(d.PntDeathSvid_DeathDate, 'HH24:MI:SS'),1,5) as \"PntDeathSvid_DeathDate_Time\",

					d.PntDeathSvid_Mass as \"PntDeathSvid_Mass\",
					d.PntDeathSvid_Height as \"PntDeathSvid_Height\",
					d.PntDeathSvid_ChildCount as \"PntDeathSvid_ChildCount\",
					d.PntDeathSvid_IsMnogoplod as \"PntDeathSvid_IsMnogoplod\",
					to_char(d.PntDeathSvid_RcpDate, 'DD.MM.YYYY') as \"PntDeathSvid_PolDate\",

					d.PntDeathSvid_RcpDoc as \"PntDeathSvid_PolDoc\",
					d.PntDeathSvid_IsBad as \"PntDeathSvid_IsBad\",
					d1.Diag_Name as \"Diag1_Name\",
					d2.Diag_Name as \"Diag2_Name\",
					d3.Diag_Name as \"Diag3_Name\",
					d4.Diag_Name as \"Diag4_Name\",
					d5.Diag_Name as \"Diag5_Name\",
					d1.Diag_Code as \"Diag1_Code\",
					d2.Diag_Code as \"Diag2_Code\",
					d3.Diag_Code as \"Diag3_Code\",
					d4.Diag_Code as \"Diag4_Code\",
					d5.Diag_Code as \"Diag5_Code\",
					l.Lpu_Name as \"Lpu_Name\",
					l.UAddress_Address as \"orgaddress_uaddress\",
					l.Org_OKPO as \"org_okpo\",
					--(COALESCE(ohp.Person_SurName+' ','') + COALESCE(ohp.Person_FirName+' ','') + COALESCE(ohp.Person_SecName,'')) as \"glvrach\",

					COALESCE(post.PostMed_Name, '') as \"MedPersonal_Post\",

					medp.Person_Fio as \"MedPersonal_FIO\",
					dst.DeathSvidType_Name as \"DeathSvidType_Name\",
					dk.DeputyKind_Name as \"DeputyKind_Name\",
					d.PntDeathSvid_ActNumber as \"PntDeathSvid_ActNumber\",
					to_char(d.PntDeathSvid_ActDT, 'DD.MM.YYYY') as \"PntDeathSvid_ActDT\",

					d.Org_id as \"Org_id\",
					o.Org_Name as \"Org_Name\",
					d.PntDeathSvid_ZagsFIO as \"PntDeathSvid_ZagsFIO\",
					d.PntDeathSvid_IsFromMother as \"PntDeathSvid_IsFromMother\",
					case
						when d.Person_hid is null then ''
						else (ohpers.Person_SurName||' '||ohpers.Person_FirName||' '||COALESCE(ohpers.Person_SecName,''))

					end as \"OrgHead_Fio\",
					case when d.OrgHeadPost_id is null then '' else ohpost.OrgHeadPost_Name end as \"OrgHeadPost_Name\"
				FROM
					v_PntDeathSvid d 

					left join v_Person_ER p  on p.Person_id = d.Person_id

					LEFT JOIN LATERAL (

						select 
							BirthPlace_id
						from
							BirthSvid 

						where
							Person_id = p.Person_id
							and extract(day from d.PntDeathSvid_DeathDate-BirthSvid_BirthDT) <= 7
                        limit 1
					) as b ON true
					left join DeputyKind dk  on dk.DeputyKind_id = d.DeputyKind_id

					left outer join v_Person_ER rp  on rp.Person_id = d.Person_rid

					left outer join v_Address_all da  on da.Address_id = d.Address_did

					left outer join v_Address_all pa  on pa.Address_id = p.UAddress_id

					left outer join Diag d1  on d1.Diag_id = d.Diag_iid

					left outer join Diag d2  on d2.Diag_id = d.Diag_tid

					left outer join Diag d3  on d3.Diag_id = d.Diag_mid

					left outer join Diag d4  on d4.Diag_id = d.Diag_eid

					left outer join Diag d5  on d5.Diag_id = d.Diag_oid

					left outer join v_PntDeathEducation pde  on pde.PntDeathEducation_id = d.PntDeathEducation_id

					left outer join v_PntDeathFamilyStatus pdfs  on pdfs.PntDeathFamilyStatus_id = d.PntDeathFamilyStatus_id

					left outer join v_MedPersonal medp  on medp.MedPersonal_id = d.MedPersonal_id

					left outer join v_MedStaffFact msf  on msf.MedStaffFact_id = d.MedStaffFact_id

					left outer join v_PostMed post  on post.PostMed_id = msf.Post_id

					left outer join v_Lpu_all l  on l.Lpu_id = d.Lpu_id

					left outer join DeathSvidType dst  on dst.DeathSvidType_id = d.DeathSvidType_id

					--left join v_OrgHead oh  on oh.OrgHead_id = d.OrgHead_id

					--left outer join v_PersonState ohp  on ohp.Person_id = oh.Person_id

					left join v_OrgDep o  on o.OrgDep_id = d.Org_id

					left join v_PntDeathGetBirth pdgb  on pdgb.PntDeathGetBirth_id = d.PntDeathGetBirth_id

					left join v_PersonState ohpers  on ohpers.Person_id = d.Person_hid

					left join v_OrgHeadPost ohpost  on ohpost.OrgHeadPost_id = d.OrgHeadPost_id

				WHERE (1 = 1)
					AND PntDeathSvid_id = :svid_id
				LIMIT 1";

			$result = $this->db->query($query, array('svid_id' => $svid_id));
		}
		
		if ( is_object($result) ) {
			$res = $result->result('array');
			$glav = '';
			//ищем руководителя
			$lpu_id = ($res[0]['Lpu_id']);

			if (!in_array($svid_type,array('birth')) && empty($res[0]['OrgHead_Fio'])) {
				$query = "
					SELECT 
						(COALESCE(ohp.Person_SurName||' ','') || COALESCE(ohp.Person_FirName||' ','') || COALESCE(ohp.Person_SecName,'')) as \"OrgHead_Fio\"

					FROM
						v_OrgHead oh 

						inner join v_PersonState ohp  on ohp.Person_id = oh.Person_id

					WHERE (1 = 1)
						and oh.Lpu_id = :Lpu_id
						and oh.OrgHeadPost_id = 1
						and LpuUnit_id is null
					LIMIT 1
				";
				$result = $this->db->query($query, array('Lpu_id' => $lpu_id));
				if (is_object($result)) {
					$res2 = $result->result('array');
					if (isset($res2[0]) && isset($res2[0]['OrgHead_Fio']))
						$glav = $res2[0]['OrgHead_Fio'];
				}
				$res[0]['OrgHead_Fio'] = $glav;
			}
			return $res[0];
		} else {
			return false;
		}		
	}
	
		
	/**
	 *	Получение номера мед. свидетельства
	 */
	function getMedSvidNum($data, $numerator = null) {
		$params = array(
			'NumeratorObject_SysName' => 'BirthSvid',
			'Lpu_id' => $data['Lpu_id'],
			'pmUser_id' => $data['pmUser_id'],
			'LpuSection_id' => $data['LpuSection_id'],
			'onDate' => $data['onDate']
		);
		$name = 'Свидетельство о рождении';

		switch ($data['svid_type']) {
			case 'birth': { $params['NumeratorObject_SysName'] = 'BirthSvid'; $name = 'Свидетельство о рождении'; break; }
			case 'death': { $params['NumeratorObject_SysName'] = 'DeathSvid'; $name = 'Свидетельство о смерти'; break; }
			case 'pntdeath': { $params['NumeratorObject_SysName'] = 'PntDeathSvid'; $name = 'Свидетельство о перинатальной смерти'; break; }
			default:
				throw new Exception('Ошибка получения номера из нумератора, не указан тип свидетельства');
				break;
		}

		if (!empty($data['showOnly']) && $data['showOnly']) {
			$params['showOnly'] = true;
		}

		$this->load->model('Numerator_model');

		if (!empty($data['generateNew'])) {
			$params['showOnly'] = false;
			$resp = $this->Numerator_model->getNumeratorNum($params, $numerator);
			$params['showOnly'] = true;
		} else if($this->getRegionNick() == 'ufa' && !empty($data['ReceptType_id']) && $data['ReceptType_id'] == 1) {
			$numerator = $this->Numerator_model->getActiveNumerator($params);
			if (empty($numerator['Numerator_id'])) {
				return array('Error_Msg' => 'Не задан активный нумератор для "'.$name.'". Обратитесь к администратору системы.', 'Error_Code' => 'numerator404');
			}
			$resp = array(
				'Numerator_Num' => '',
				'Numerator_IntNum' => '',
				'Numerator_PreNum' => $numerator['Numerator_PreNum'],
				'Numerator_PostNum' => $numerator['Numerator_PostNum'],
				'Numerator_Ser' => $numerator['Numerator_Ser']
			);
			return $resp;
		} else {
			$resp = $this->Numerator_model->getNumeratorNum($params, $numerator);
		}

		if (!empty($resp['Numerator_Num'])) {
			return $resp;
		} else {
			if (!empty($resp['Error_Msg'])) {
				return array('Error_Msg' => $resp['Error_Msg'], 'success' => false);
			}
			if (getRegionNick() == 'ufa') {
				return array('Error_Msg' => 'Не задан активный нумератор для "'.$name.'". Обратитесь к администратору системы.', 'Error_Code' => 'numerator404');
			} else {
				return array('Error_Msg' => 'Не задан активный нумератор для "'.$name.'", ввод свидетельств возможен в режиме "1. На бланке".', 'Error_Code' => 'numerator404');
			}
		}
	}
	
	/**
	 *	Получение серии мед. свидетельства
	 */
	/*function getMedSvidSer($data) {
		if (isset($data['base_ser'])) {
			$ser = $data['base_ser'];
		} else {
			$ser = "1";
			$sql = "select COALESCE(Lpu_Ouz, 1) Lpu_Ouz from v_Lpu where Lpu_id = :lpu_id";

			$result = $this->db->query($sql, array('lpu_id' => $data['Lpu_id']));
			if (is_object($result)) {
				$res = $result->result('array');
				if (isset($res[0])) $ser = $res[0]['Lpu_Ouz'];
			}
			
			switch ($data['svid_type']) {
				case 'birth': { $ser .= '-Р'; break; }
				case 'death': { $ser .= '-С'; break; }
				case 'pntdeath': { $ser .= '-П'; break; }
			}
			
		}
		return $ser;
	}*/
	
	/**
	 *	Получение количества дубликатов свидетельств о рождении
	 */
	function getDoubleBirthSvidCnt($data) {
		$query = "
			select count(BirthSvid_id) as \"cnt\"
			from v_BirthSvid 

			where Person_id = :Person_id
				and COALESCE(BirthSvid_IsBad, 1) = 1

				and COALESCE(BirthSvid_id, 0) != :BirthSvid_id

		";
		$result = $this->db->query($query, array('BirthSvid_id' => $data['BirthSvid_id'], 'Person_id' => $data['Person_id']));

		if (is_object($result)) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Удаление мед. свидетельств при отмене сохранения движения в КВС
	 */
	function deleteMedSvidFromEvnSection($data)
	{
		$response = array('success'=>true,'Error_Msg'=>'');
		if (!empty($data['deathChildData'])) {
			$tmpstr = $data['deathChildData'];
			ConvertFromWin1251ToUTF8($tmpstr);
			$deathChildData = json_decode($tmpstr,true);
			//print_r(array('data'=>$data['deathChildData'],'array'=>$deathChildData));exit;
			if (!empty($deathChildData) && is_array($deathChildData)) {
				foreach ($deathChildData as $deathChild) {
					$params = array();
					array_walk($deathChild, 'ConvertFromUTF8ToWin1251');
					if ($deathChild['PntDeathSvid_id'] > 0) {
						$params['PntDeathSvid_id'] = $deathChild['PntDeathSvid_id'];
						$this->deleteMedSvid($params, 'pntdeath');
					}
				}
			}
		}
		if (!empty($data['childData'])) {
			$tmpstr = $data['childData'];
			ConvertFromWin1251ToUTF8($tmpstr);
			$childData = json_decode($tmpstr,true);
			if (!empty($childData) && is_array($childData)) {
				foreach ($childData as $child) {
					$params = array();
					array_walk($child, 'ConvertFromUTF8ToWin1251');
					if ($child['BirthSvid_id'] > 0) {
						$params['BirthSvid_id'] = $child['BirthSvid_id'];
						$this->deleteMedSvid($params, 'birth');
					}
				}
			}
		}
		return $response;
	}
	
	/**
	 * Проверка существования свидетельства о рождении, выписанного на основании текущего
	 */
	function checkBirthSvidExist($data)
	{
		$resp_bs = $this->queryResult("
			select
				bs.BirthSvid_id as \"BirthSvid_id\",
				bs.BirthSvid_Ser as \"BirthSvid_Ser\",
				bs.BirthSvid_Num as \"BirthSvid_Num\"
			from
				v_BirthSvid bs
			where
				bs.BirthSvid_pid = :BirthSvid_pid
			limit 1
		", [
			'BirthSvid_pid' => $data['BirthSvid_pid']
		]);

		if (!empty($resp_bs[0]['BirthSvid_id'])) {
			return ['Error_Msg' => 'На основании текущего мед. свидетельства было оформлено новое: серия ' . $resp_bs[0]['BirthSvid_Ser'] . ' номер ' . $resp_bs[0]['BirthSvid_Num'] . '. Повторное создание мед свидетельства на основании текущего невозможно'];
		}

		return ['Error_Msg' => ''];
	}

	/**
	 * Проверка объёма "Выбор диагнозов COVID-19 в МСС"
	 */
	function checkCOVIDVolume($data)
	{
		$resp = $this->queryResult("
			SELECT
				av.AttributeValue_id as \"AttributeValue_id\"
			FROM
				v_VolumeType vt
				inner join v_AttributeVision avis on avis.AttributeVision_TableName = 'dbo.VolumeType' and avis.AttributeVision_TablePKey = vt.VolumeType_id 
				inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
			WHERE
				av.AttributeValue_ValueIdent = :Lpu_id
				and avis.AttributeVision_IsKeyValue = 2
				and vt.VolumeType_Code = '2020-МСС_COVID'
				and COALESCE(av.AttributeValue_begDate, :onDate) <= :onDate
				and COALESCE(av.AttributeValue_endDate, :onDate) >= :onDate
			limit 1
		", [
			'Lpu_id' => $data['Lpu_id'],
			'onDate' => $data['onDate']
		]);

		if (!empty($resp[0]['AttributeValue_id'])) {
			return ['Error_Msg' => '', 'hasCOVIDVolume' => true];
		}

		return ['Error_Msg' => '', 'hasCOVIDVolume' => false];
	}
	
	/**
	 * Проверка существования свидетельства о смерти, выписанного на основании текущего
	 */
	function checkDeathSvidExist($data)
	{
		$queryParams = [];
		if (!empty($data['DeathSvid_pid'])) {
			$filter_ds = "ds.DeathSvid_pid = :DeathSvid_pid";
			$queryParams['DeathSvid_pid'] = $data['DeathSvid_pid'];
		} else if (!empty($data['Person_id'])) {
			$filter_ds = "ds.Person_id = :Person_id";
			$queryParams['Person_id'] = $data['Person_id'];
		} else {
			return ['Error_Msg' => ''];
		}

		$resp_bs = $this->queryResult("
			select
				ds.DeathSvid_id as \"DeathSvid_id\",
				ds.DeathSvid_Ser as \"DeathSvid_Ser\",
				ds.DeathSvid_Num as \"DeathSvid_Num\",
				substring(ps.Person_SurName, 1, 1) as \"Person_SurName\",
				ps.Person_FirName  as \"Person_FirName\",
				ps.Person_SecName as \"Person_SecName\"
			from
				v_DeathSvid ds
				inner join v_PersonState ps on ps.Person_id = ds.Person_rid
			where
				{$filter_ds}
			limit 1
		", $queryParams);

		if (!empty($resp_bs[0]['DeathSvid_id'])) {
			if (!empty($data['DeathSvid_pid'])) {
				return ['Error_Msg' => 'На основании текущего мед. свидетельства было оформлено новое: серия ' . $resp_bs[0]['DeathSvid_Ser'] . ' номер ' . $resp_bs[0]['DeathSvid_Num'] . '. Повторное создание мед свидетельства на основании текущего невозможно'];
			} else {
				return ['Error_Msg' => 'Выписка МС о смерти взамен испорченного пациенту недоступна, так как МС о смерти пациенту ' . $resp_bs[0]['Person_FirName'] . ' ' . $resp_bs[0]['Person_SecName'] . ' ' . $resp_bs[0]['Person_SurName'] . ' было оформлено ранее'];
			}
		}

		return ['Error_Msg' => ''];
	}

	/**
	 * Удаление мед. свидетельств при отмене сохранения движения в КВС
	 */
	function deleteMedSvid($data, $svid_type)
	{
		$tab_name = "";
		$sql_p_array = array();
		$query = "";

		if ($svid_type == 'pntdeath') {
			$tab_name = "PntDeathSvid";
			$sql_p_array['svid_id'] = $data['PntDeathSvid_id'];
		}
		if ($svid_type == 'birth') {
			$tab_name = "BirthSvid";
			$sql_p_array['svid_id'] = $data['BirthSvid_id'];
		}

		$query = "
			select Error_Code as \"Error_Code\", Error_Message as \"Error_Message\"
			from dbo.p_{$tab_name}_del(
				{$tab_name}_id := :svid_id);
		";

		$response = array('success' => false);
		//echo getDebugSQL($query, $sql_p_array);exit;
		$res = $this->db->query($query, $sql_p_array);
		if (is_object($res)) {
			$res = $res->result('array');
			if (is_array($res)) {
				$response['success'] = true;
			}
		}
		return $response;
	}

	/**
	 *	Перевод строкового представления времени в секунды
	 */
	function timeStrToSec($str) {
		$sec = 0;
		$t_arr = preg_split("[:]", $str, 3);
		if (count($t_arr) >= 2) {
			$sec += ($t_arr[0] * 3600) + ($t_arr[1] * 60) + (isset($t_arr[2]) ? $t_arr[2] : 0);
		}
		return $sec;
	}
	
    /**
     * Получение имени другой МО
     */
    function getAnotherMOName( $data ) {
        $query = "
            SELECT
                Org_name as \"Org_name\"
            FROM
                v_Org
            WHERE 
                Org_id = :Org_id
            limit 1
        ";

        $result = $this->db->query($query, array('Org_id' => $data['Org_id']));

        if ( is_object($result) ) {
            return $result->result('array');
        } else {
            return false;
        }
    }
	
	/**
	 *	Получение адреса рождения
	 */
	function getDefaultBirthAddress($data) {
		$query = "
			SELECT 
				ba.Address_Zip as \"BAddress_Zip\",
				ba.KLCountry_id as \"BKLCountry_id\",
				ba.KLRGN_id as \"BKLRGN_id\",
				ba.KLSubRGN_id as \"BKLSubRGN_id\",
				ba.KLCity_id as \"BKLCity_id\",
				ba.KLTown_id as \"BKLTown_id\",
				ba.KLStreet_id as \"BKLStreet_id\",
				ba.Address_House as \"BAddress_House\",
				ba.Address_Corpus as \"BAddress_Corpus\",
				ba.Address_Flat as \"BAddress_Flat\",
				ba.Address_Address as \"BAddress_Address\",
				ba.Address_Address as \"BAddress_AddressText\"
			FROM
				v_Lpu l 

				inner join Address ba  on ba.Address_id = l.PAddress_id

			WHERE l.Lpu_id = :Lpu_id
				and ba.KLRgn_id is not null
			LIMIT 1
		";

		$result = $this->db->query($query, array('Lpu_id' => $data['Lpu_id']));
		
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение DeathDiag
	 */
	function getDeathDiagStore($data) {
		$query = "
			select 
				DeathDiag_id as \"DeathDiag_id\",
				Diag_id as \"Diag_id\",
				DeathDiag_IsLowChance as \"DeathDiag_IsLowChance\",
				DeathDiag_IsNotUsed as \"DeathDiag_IsNotUsed\",
				DeathDiag_IsDiagIID as \"DeathDiag_IsDiagIID\",
				DeathDiag_IsDiagTID as \"DeathDiag_IsDiagTID\",
				DeathDiag_IsDiagMID as \"DeathDiag_IsDiagMID\",
				DeathDiag_IsDiagEID as \"DeathDiag_IsDiagEID\",
				DeathDiag_IsDiagOID as \"DeathDiag_IsDiagOID\",
				Sex_id as \"Sex_id\",
				DeathDiag_YearFrom as \"DeathDiag_YearFrom\",
				DeathDiag_MonthFrom as \"DeathDiag_MonthFrom\",
				DeathDiag_DayFrom as \"DeathDiag_DayFrom\",
				DeathDiag_YearTo as \"DeathDiag_YearTo\",
				DeathDiag_MonthTo as \"DeathDiag_MonthTo\",
				DeathDiag_DayTo as \"DeathDiag_DayTo\",
				DeathDiag_DiagChange as \"DeathDiag_DiagChange\",
				DeathDiag_Message as \"DeathDiag_Message\",
				Region_id as \"Region_id\",
				case when DeathDiag_DayFrom is not null then 1 else 0 end
				+ case when DeathDiag_DayTo is not null then 1 else 0 end
				+ case when DeathDiag_MonthFrom is not null then 1 else 0 end
				+ case when DeathDiag_MonthTo is not null then 1 else 0 end
				+ case when DeathDiag_YearFrom is not null then 1 else 0 end
				+ case when DeathDiag_YearTo is not null then 1 else 0 end
				+ case when Sex_id is not null then 1 else 0 end as \"DeathDiag_CriteriaCount\"
			from
				v_DeathDiag 

		";

		return $this->queryResult($query);
	}

	/**
	 * Получение адреса рождения пациента
	 */
	function getPacientBAddress($data) {
		$query = "
			SELECT 
				baddr.Address_Zip as \"BAddress_Zip\",
				baddr.KLCountry_id as \"BKLCountry_id\",
				baddr.KLRGN_id as \"BKLRGN_id\",
				baddr.KLSubRGN_id as \"BKLSubRGN_id\",
				baddr.KLCity_id as \"BKLCity_id\",
				baddr.KLTown_id as \"BKLTown_id\",
				baddr.KLStreet_id as \"BKLStreet_id\",
				baddr.Address_House as \"BAddress_House\",
				baddr.Address_Corpus as \"BAddress_Corpus\",
				baddr.Address_Flat as \"BAddress_Flat\",
				baddr.Address_Address as \"BAddress_Address\",
				baddr.Address_Address as \"BAddress_AddressText\",
				1 as \"AddressFound\",
				'' as \"Error_Msg\"
			FROM
				v_PersonState PS 

				left join PersonBirthPlace pbp  on PS.Person_id = pbp.Person_id

				left join v_Address baddr  on pbp.Address_id = baddr.Address_id

				left join v_AddressSpecObject baddrsp  on baddr.AddressSpecObject_id = baddrsp.AddressSpecObject_id

			WHERE
				PS.Person_id = :Person_id
			LIMIT 1
		";

		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return $resp[0];
			}
		}

		return array('Error_Msg' => '');
	}
	/**
	 * Получение адреса пациента
	 */
	function getPacientUAddress($data) {
		$query = "
			SELECT
				a.Address_Zip as \"BAddress_Zip\",
				a.KLCountry_id as \"BKLCountry_id\",
				a.KLRGN_id as \"BKLRGN_id\",
				a.KLSubRGN_id as \"BKLSubRGN_id\",
				a.KLCity_id as \"BKLCity_id\",
				a.KLTown_id as \"BKLTown_id\",
				a.KLStreet_id as \"BKLStreet_id\",
				a.Address_House as \"BAddress_House\",
				a.Address_Corpus as \"BAddress_Corpus\",
				a.Address_Flat as \"BAddress_Flat\",
				a.Address_Address as \"BAddress_Address\",
				a.Address_Address as \"BAddress_AddressText\",
				1 as \"AddressFound\",
				'' as \"Error_Msg\"
			FROM
				v_PersonState PS 

				inner join v_Address a  on a.Address_id = ps.UAddress_id

			WHERE
				PS.Person_id = :Person_id
			LIMIT 1
		";

		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return $resp[0];
			}
		}

		return array('Error_Msg' => '');
	}

	/**
	 * Получение адреса пациента
	 */
	function getPacientDeathAddress($data) {
		$query = "
			SELECT
				a.Address_Zip as \"BAddress_Zip\",
				a.KLCountry_id as \"BKLCountry_id\",
				a.KLRGN_id as \"BKLRGN_id\",
				a.KLSubRGN_id as \"BKLSubRGN_id\",
				a.KLCity_id as \"BKLCity_id\",
				a.KLTown_id as \"BKLTown_id\",
				a.KLStreet_id as \"BKLStreet_id\",
				a.Address_House as \"BAddress_House\",
				a.Address_Corpus as \"BAddress_Corpus\",
				a.Address_Flat as \"BAddress_Flat\",
				a.Address_Address as \"BAddress_Address\",
				a.Address_Address as \"BAddress_AddressText\",
				1 as \"AddressFound\",
				'' as \"Error_Msg\"
			FROM
				v_EvnPS eps 

				inner join v_LeaveType lt  on lt.LeaveType_id = eps.LeaveType_id

				inner join v_LpuSection ls  on ls.LpuSection_id = eps.LpuSection_id

				inner join v_LpuUnit lu  on lu.LpuUnit_id = ls.LpuUnit_id

				left join v_LpuBuilding lb  on lb.LpuBuilding_id = lu.LpuBuilding_id

				inner join v_Address a  on a.Address_id = COALESCE(lu.Address_id, lb.Address_id)


			WHERE
				eps.Person_id = :Person_id
				and lt.LeaveType_SysNick IN ('die', 'dsdie', 'ksdie')
			LIMIT 1
		";

		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return $resp[0];
			}
		}

		return array('Error_Msg' => '');
	}


	/**
	 *	Получение списка свидетельств о смерти
	 */
	function loadDeathSvidList($data) {
		$filter = '(1 = 1)';
		$queryParams = array();

		$filter .= " and DS.Person_id = :Person_id";
		$queryParams['Person_id'] = $data['Person_id'];

		if ( !empty($data['EvnPS_id']) ) {
			$filter .= " and DS.DeathSvid_id = :DeathSvid_id";
			$queryParams['DeathSvid_id'] = $data['DeathSvid_id'];
		}

		$query = "
			select
				 DS.DeathSvid_id as \"DeathSvid_id\"
				,RTRIM(COALESCE(DS.DeathSvid_Num, '')) || ', выдано ' || to_char(DS.DeathSvid_GiveDate, 'DD.MM.YYYY') as \"DeathSvid_Num\"


				,to_char(DS.DeathSvid_GiveDate, 'DD.MM.YYYY') as \"DeathSvid_GiveDate\"

			from
				v_DeathSvid DS 

			where " . $filter . "
			    and coalesce(DS.DeathSvid_IsActual, 2) = 2
			order by
				DS.DeathSvid_GiveDate
		";
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
 	*	Получение списка свидетельств о смерти и список свидетельств о перинатальной смерти
	*/
    	function loadDeathSvidListWithPntDeath($data) {
        	$result = [];
		$deathSvidList = $this->loadDeathSvidList($data);
		
        	if( $data['Person_id'] ){
            		$pntDeathList = $this->loadPntDeathSvidListByPerson($data);
            		// На фронте это одно поле, по этому приводим поля к единому виду, и ставим флаг того типа свидетельства
            		$tmpPntDeathList = [];
            		if( $pntDeathList ){
                		foreach ( $pntDeathList as $item ) {
                    			$tmpPntDeathList[] = [
                        		'DeathSvid_id' => $item['PntDeathSvid_id'],
                        		'DeathSvid_Num' => $item['PntDeathSvid_Num'],
					'DeathSvid_GiveDate' => $item['PntDeathSvid_GiveDate'],
					'Type_MSoS' => 'PntDeathSvid'
                    			];
                		}
            		}
        	}
        	$result = array_merge($deathSvidList, $tmpPntDeathList);
        	if ( count($result) ) {
			return $result;
		}
		else {
			return false;
		}
    	}
	
	
	/**
	 *	Получение данных по-умолчанию для новорожденного
	 */
	function getDefaultPersonChildValues($data) {
		$query = "
			SELECT 
				pc.PersonChild_CountChild as \"PersonChild_CountChild\",
				pc.ChildTermType_id as \"ChildTermType_id\",
				case when pw.Okei_id = 36 then
					PersonWeight_Weight
				else
					pw.PersonWeight_Weight * 1000
				end as \"PersonWeight_Weight\"
			FROM
				PersonChild pc 

				LEFT JOIN LATERAL (

					select
						PersonWeight_Weight,
						Okei_id
					from
						PersonWeight 

					where
						Person_id = pc.Person_id
					order by
						PersonWeight_setDT desc
                    limit 1
				) pw ON true
			WHERE (1 = 1)
				AND pc.Person_id = :Person_id
			LIMIT 1";
				
		$result = $this->db->query($query, array('Person_id' => $data['Person_id']));
		
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Обновление номера в свидетельстве
	 */
	function updateMedSvidNum($data, $svid_type) {
		$params = array(
			'MedSvid_id' => $data['MedSvid_id'],
			'MedSvid_Num' => $data['MedSvid_Num'],
		);

		$object = null;
		switch($svid_type) {
			case 'birth': $object = "BirthSvid";break;
		}
		if (empty($object)) {
			return $this->createError('Неверный тип свидетельства');
		}

		$query = "
			select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
            from dbo.p_objectnum_upd(
            	MedSvid_id := :MedSvid_id,
                MedSvid_Num := :MedSvid_Num,
                object_name := $${$object}$$
            );
		";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('','Ошибка при сохранении номера свидетельства');
		}
		return $response;
	}

	/**
	* Возвращает свидетельства о рождении по Персону. Метод для API
	*/
	function loadBirthSvidListByPerson($data) {
		$query = "
			select
				bs.BirthSvid_id as \"BirthSvid_id\",
				bs.BirthSvid_Ser as \"BirthSvid_Ser\",
				bs.BirthSvid_Num as \"BirthSvid_Num\",
				to_char(bs.BirthSvid_GiveDate, 'YYYY-MM-DD HH24:MI:SS') as \"BirthSvid_GiveDate\"

			from
				BirthSvid bs 

			where 
				bs.Person_id = :Person_id
		";
		
		$result = $this->db->query($query, $data);
		
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	* Возвращает Персону по свидетельству о рождении. Метод для API
	*/
	function loadBirthSvidData($data) {
		$query = "
			select
                BirthSvid_1817 as \"BirthSvid_1817\",
                BirthSvid_RcpDocument as \"BirthSvid_RcpDocument\",
                BirthSvid_RcpDate as \"BirthSvid_RcpDate\",
                BirthEmployment_id as \"BirthEmployment_id\",
                BirthSpecialist_id as \"BirthSpecialist_id\",
                Address_rid as \"Address_rid\",
                BirthSvid_ChildFamil as \"BirthSvid_ChildFamil\",
                BirthSvid_FirstSrok as \"BirthSvid_FirstSrok\",
                BirthSvid_IsMnogoplod as \"BirthSvid_IsMnogoplod\",
                BirthSvid_PlodIndex as \"BirthSvid_PlodIndex\",
                Server_id as \"Server_id\",
                BirthSvid_id as \"BirthSvid_id\",
                Lpu_id as \"Lpu_id\",
                Person_id as \"Person_id\",
                Person_rid as \"Person_rid\",
                BirthSvid_Ser as \"BirthSvid_Ser\",
                BirthSvid_Num as \"BirthSvid_Num\",
                MedPersonal_id as \"MedPersonal_id\",
                BirthMedPersonalType_id as \"BirthMedPersonalType_id\",
                Nation_id as \"Nation_id\",
                NationOther_id as \"NationOther_id\",
                ReceptType_id as \"ReceptType_id\",
                BirthEducation_id as \"BirthEducation_id\",
                BirthSvid_BirthDT as \"BirthSvid_BirthDT\",
                BirthPlace_id as \"BirthPlace_id\",
                Sex_id as \"Sex_id\",
                BirthSvid_Week as \"BirthSvid_Week\",
                BirthSvid_BirthCount as \"BirthSvid_BirthCount\",
                BirthSvid_PregnCount as \"BirthSvid_PregnCount\",
                BirthSvid_ChildCount as \"BirthSvid_ChildCount\",
                BirthSvid_IsBad as \"BirthSvid_IsBad\",
                BirthFamilyStatus_id as \"BirthFamilyStatus_id\",
                BirthSvid_PlodCount as \"BirthSvid_PlodCount\",
                BirthSvid_IsFromMother as \"BirthSvid_IsFromMother\",
                BirthSvid_New as \"BirthSvid_New\",
                BirthBirthType_id as \"BirthBirthType_id\",
                BirthSvid_Mass as \"BirthSvid_Mass\",
                BirthSvid_Height as \"BirthSvid_Height\",
                BirthSvid_Algar1 as \"BirthSvid_Algar1\",
                BirthSvid_Algar5 as \"BirthSvid_Algar5\",
                BirthSvid_IsBreath as \"BirthSvid_IsBreath\",
                BirthSvid_IsHeart as \"BirthSvid_IsHeart\",
                BirthSvid_IsPup as \"BirthSvid_IsPup\",
                BirthSvid_IsMove as \"BirthSvid_IsMove\",
                BirthSvid_1801 as \"BirthSvid_1801\",
                BirthSvid_1802 as \"BirthSvid_1802\",
                BirthSvid_1803 as \"BirthSvid_1803\",
                BirthSvid_1804 as \"BirthSvid_1804\",
                BirthSvid_1805 as \"BirthSvid_1805\",
                BirthSvid_1806 as \"BirthSvid_1806\",
                BirthSvid_1807 as \"BirthSvid_1807\",
                BirthSvid_1808 as \"BirthSvid_1808\",
                BirthSvid_1809 as \"BirthSvid_1809\",
                BirthSvid_1810 as \"BirthSvid_1810\",
                BirthSvid_1811 as \"BirthSvid_1811\",
                BirthSvid_1812 as \"BirthSvid_1812\",
                BirthSvid_1813 as \"BirthSvid_1813\",
                BirthSvid_1814 as \"BirthSvid_1814\",
                BirthSvid_1815 as \"BirthSvid_1815\",
                BirthSvid_2313 as \"BirthSvid_2313\",
                BirthSvid_18Other as \"BirthSvid_18Other\",
                BirthSvid_20Other as \"BirthSvid_20Other\",
                BirthSvid_21Other as \"BirthSvid_21Other\",
                BirthSvid_22Other as \"BirthSvid_22Other\",
                BirthSvid_23Other as \"BirthSvid_23Other\",
                pmUser_insID as \"pmUser_insID\",
                pmUser_updID as \"pmUser_updID\",
                BirthSvid_insDT as \"BirthSvid_insDT\",
                BirthSvid_updDT as \"BirthSvid_updDT\",
                BirthSvid_GiveDate as \"BirthSvid_GiveDate\",
                BirthChildResult_id as \"BirthChildResult_id\",
                LpuSection_id as \"LpuSection_id\",
                OrgHead_id as \"OrgHead_id\",
                FamilyStatus_id as \"FamilyStatus_id\",
                Person_cid as \"Person_cid\",
                BirthSvid_IsSigned as \"BirthSvid_IsSigned\",
                pmUser_signID as \"pmUser_signID\",
                BirthSvid_signDT as \"BirthSvid_signDT\",
                DeputyKind_id as \"DeputyKind_id\",
                BirthSvid_IsMarried as \"BirthSvid_IsMarried\",
                Okei_mid as \"Okei_mid\",
                LpuLicence_id as \"LpuLicence_id\",
                MedStaffFact_id as \"MedStaffFact_id\",
                BirthSvid_Deleted as \"BirthSvid_Deleted\",
                BirthSvid_delDT as \"BirthSvid_delDT\",
                BirthSvid_IsOtherMO as \"BirthSvid_IsOtherMO\",
                Org_id as \"Org_id\",
                BirthSvid_Rowversion as \"BirthSvid_Rowversion\",
                BirthSvid_1816 as \"BirthSvid_1816\",
                BirthSvid_1818 as \"BirthSvid_1818\",
                BirthSvid_1819 as \"BirthSvid_1819\",
                BirthSvid_1820 as \"BirthSvid_1820\",
                BirthSvid_1821 as \"BirthSvid_1821\",
                BirthSvid_1901 as \"BirthSvid_1901\",
                BirthSvid_1902 as \"BirthSvid_1902\",
                BirthSvid_2001 as \"BirthSvid_2001\",
                BirthSvid_2002 as \"BirthSvid_2002\",
                BirthSvid_2003 as \"BirthSvid_2003\",
                BirthSvid_2004 as \"BirthSvid_2004\",
                BirthSvid_2005 as \"BirthSvid_2005\",
                BirthSvid_2006 as \"BirthSvid_2006\",
                BirthSvid_2007 as \"BirthSvid_2007\",
                BirthSvid_2008 as \"BirthSvid_2008\",
                BirthSvid_2009 as \"BirthSvid_2009\",
                BirthSvid_2010 as \"BirthSvid_2010\",
                BirthSvid_2101 as \"BirthSvid_2101\",
                BirthSvid_2102 as \"BirthSvid_2102\",
                BirthSvid_2103 as \"BirthSvid_2103\",
                BirthSvid_2104 as \"BirthSvid_2104\",
                BirthSvid_2105 as \"BirthSvid_2105\",
                BirthSvid_2201 as \"BirthSvid_2201\",
                BirthSvid_2202 as \"BirthSvid_2202\",
                BirthSvid_2203 as \"BirthSvid_2203\",
                BirthSvid_2204 as \"BirthSvid_2204\",
                BirthSvid_2205 as \"BirthSvid_2205\",
                BirthSvid_2206 as \"BirthSvid_2206\",
                BirthSvid_2301 as \"BirthSvid_2301\",
                BirthSvid_2302 as \"BirthSvid_2302\",
                BirthSvid_2303 as \"BirthSvid_2303\",
                BirthSvid_2304 as \"BirthSvid_2304\",
                BirthSvid_2305 as \"BirthSvid_2305\",
                BirthSvid_2306 as \"BirthSvid_2306\",
                BirthSvid_2307 as \"BirthSvid_2307\",
                BirthSvid_2308 as \"BirthSvid_2308\",
                BirthSvid_2309 as \"BirthSvid_2309\",
                BirthSvid_2310 as \"BirthSvid_2310\",
                BirthSvid_2311 as \"BirthSvid_2311\",
                BirthSvid_2312 as \"BirthSvid_2312\"
  			from
				BirthSvid bs 

			where 
				bs.BirthSvid_id = :BirthSvid_id
		";
		
		$result = $this->db->query($query, $data);
		
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	* Получение информации по Свидетельству о рождении. Метод для API
	*/
	function loadBirthSvid($data) {
		$query = "
			select
				bs.BirthSvid_id as \"BirthSvid_id\",
				bs.ReceptType_id as \"ReceptType_id\",
				bs.BirthSvid_Ser as \"BirthSvid_Ser\",
				bs.BirthSvid_Num as \"BirthSvid_Num\",
				to_char(bs.BirthSvid_GiveDate, 'YYYY-MM-DD HH24:MI:SS') as \"BirthSvid_GiveDate\",

				bs.BirthEmployment_id as \"BirthEmployment_id\",
				bs.BirthEducation_id as \"BirthEducation_id\",
				bs.BirthFamilyStatus_id as \"BirthFamilyStatus_id\",
				bs.LpuSection_id as \"LpuSection_id\",
				bs.MedStaffFact_id as \"MedStaffFact_id\",
				bs.BirthMedPersonalType_id as \"BirthMedPersonalType_id\",
				bs.OrgHead_id as \"OrgHead_id\",
				bs.LpuLicence_id as \"LpuLicence_id\",
				to_char(bs.BirthSvid_BirthDT, 'YYYY-MM-DD HH24:MI:SS') as \"BirthSvid_BirthDT_Date\",

				bs.BirthPlace_id as \"BirthPlace_id\",
				bs.BirthSpecialist_id as \"BirthSpecialist_id\",
				case when bs.BirthSvid_IsMnogoplod = 2 then 1 else 0 end as \"BirthSvid_IsMnogoplod\",
				bs.BirthSvid_PlodIndex as \"BirthSvid_PlodIndex\",
				bs.BirthSvid_PlodCount as \"BirthSvid_PlodCount\",
				bs.BirthChildResult_id as \"BirthChildResult_id\",
				bs.BirthSvid_ChildCount as \"BirthSvid_ChildCount\",
				bs.BirthSvid_Week as \"BirthSvid_Week\",
				bs.BirthSvid_Mass as \"BirthSvid_Mass\",
				bs.Okei_mid as \"Okei_mid\",
				bs.BirthSvid_Height as \"BirthSvid_Height\",
				bs.Sex_id as \"Sex_id\",
				bs.BirthSvid_ChildFamil as \"BirthSvid_ChildFamil\",
				bs.Address_rid as \"Address_rid\",
				bs.Person_rid as \"Person_rid\",
				bs.BirthSvid_RcpDocument as \"BirthSvid_RcpDocument\",
				bs.DeputyKind_id as \"DeputyKind_id\",
				to_char(bs.BirthSvid_RcpDate, 'YYYY-MM-DD HH24:MI:SS') as \"BirthSvid_RcpDate\",

				case when bs.BirthSvid_IsFromMother = 2 then 1 else 0 end as \"BirthSvid_IsFromMother\"
			from
				v_BirthSvid bs 

			where 
				bs.BirthSvid_id = :BirthSvid_id
		";
		
		$result = $this->db->query($query, $data);
		
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *	Сохранение мед. свидетельства о рождении. Для API
	 */
	function saveMedSvidAPI($data, $svid_type) {
		try {
			$this->db->trans_begin();

			$tab_name = "";
			$param_array = array();
			$sql_p_array = array();
			$query = "";
			$query_parts = "";
			
			if ($svid_type == 'birth') $tab_name = "BirthSvid";
			if ($svid_type == 'death') $tab_name = "DeathSvid";
			if ($svid_type == 'pntdeath') $tab_name = "PntDeathSvid";
			
			if ($svid_type == 'birth') {
				$param_array = array(
					'Server_id' => 'i', 
					'Person_id' => 'i',
					'Person_cid' => 'i',
					'Person_rid' => 'i',
					'BirthSvid_Ser' => 's',
					'BirthSvid_Num' => 's',
					'MedPersonal_id' => 'i',
					'MedStaffFact_id' => 'i',
					'LpuSection_id' => 'i',
					'BirthMedPersonalType_id' => 'i',
					'ReceptType_id' => 'i',
					'BirthEducation_id' => 'i',
					'BirthSvid_BirthDT' => 'i',
					'BirthPlace_id' => 'i',
					'Sex_id' => 'i',
					'BirthSvid_Week' => 's',
					'BirthSvid_ChildCount' => 's',
					'BirthFamilyStatus_id' => 'i',
					'BirthSvid_RcpDocument' => 's',
					'BirthSvid_RcpDate' => 'dt',
					'BirthEmployment_id' => 'i',
					'BirthSpecialist_id' => 'i',
					'BirthSvid_ChildFamil' => 's',
					'BirthSvid_IsMnogoplod' => 'i',
					'BirthSvid_PlodIndex' => 's',
					'BirthSvid_PlodCount' => 's',
					'BirthSvid_IsFromMother' => 'i',
					'BirthSvid_Height' => 's',
					'BirthSvid_Mass' => 's',
					'Okei_mid' => 's',
					'BirthSvid_GiveDate' => 'dt',
					'BirthChildResult_id' => 'i',//далее расчетные и из сессии
					'Lpu_id' => 'i',
					'Address_rid' => 'i',
					'pmUser_id' => 'i',
					'BirthSvid_IsBad' => 'i',
					'DeputyKind_id' => 'i',
					'OrgHead_id' => 'i',
	                'LpuLicence_id' => 'i'
				);
			}

			if ($svid_type == 'death') {
				$param_array = array(
					'Server_id' => 'i', 
					'Person_id' => 'i',
					'Person_mid' => 'i',
					'Person_rid' => 'i',
					'DeathSvid_PolFio' => 's',
					'MedPersonal_id' => 'i',
					'MedStaffFact_id' => 'i',
					'LpuSection_id' => 'i',
					'DeathSvidType_id' => 'i',
					'DeathSvid_IsDuplicate' => 'i',
					'DeathSvid_IsLose' => 'i',
					'DeathSvid_IsActual' => 'i',
					'ReceptType_id' => 'i',
					'DeathCause_id' => 'i',
					'DeathFamilyStatus_id' => 'i',
					'DeathPlace_id' => 'i',
					'DeathEducation_id' => 'i',
					'DeathTrauma_id' => 'i',
					'DeathSetType_id' => 'i',
					'DeathSetCause_id' => 'i',
					'DeathWomanType_id' => 'i',
					'DeathEmployment_id' => 'i',
					'DtpDeathTime_id' => 'i',
					'ChildTermType_id' => 'i',
					'Diag_iid' => 'i',
					'Diag_tid' => 'i',
					'Diag_mid' => 'i',
					'Diag_eid' => 'i',
					'Diag_oid' => 'i',
					'DeathSvid_IsNoPlace' => 'i',
	                'DeathSvid_isBirthDate' => 'i',
					'DeathSvid_TraumaDateStr' => 's',
					'DeathSvid_BirthDateStr' => 's',
					'DeathSvid_DeathDateStr' => 's',
					'DeathSvid_Ser' => 's',
					'DeathSvid_Num' => 's',
					'DeathSvid_OldSer' => 's',
					'DeathSvid_OldNum' => 's',
					'DeathSvid_DeathDate' => 'i',				
					'DeathSvid_IsNoDeathTime' => 'i',
	                'DeathSvid_IsNoAccidentTime' => 'i',
					'DeathSvid_Mass' => 'i',
					'DeathSvid_Month' => 'i',
					'DeathSvid_Day' => 'i',
					'DeathSvid_ChildCount' => 'i',
					'DeathSvid_TraumaDate' => 'i',
					'DeathSvid_TraumaDescr' => 's',
					'DeathSvid_Oper' => 's',
					'DeathSvid_PribPeriod' => 's',
					'DeathSvid_PribPeriodPat' => 's',
					'DeathSvid_PribPeriodDom' => 's',
					'DeathSvid_PribPeriodExt' => 's',
					'DeathSvid_PribPeriodImp' => 's',
					'DeathSvid_RcpDate' => 'dt',
					'DeathSvid_GiveDate' => 'dt',
					'DeathSvid_OldGiveDate' => 'dt',
					'DeathSvid_RcpDocument' => 's', //далее расчетные и из сессии
					'Lpu_id' => 'i',
					'Address_bid' => 'i',
					'Address_did' => 'i',
					'pmUser_id' => 'i',
					'DeathSvid_IsBad' => 'i',
					'OrgHeadPost_id' => 'i',
					'Person_hid' => 'i',
					'DeathSvid_IsPrimDiagIID' => 'i',
					'DeathSvid_IsPrimDiagTID' => 'i',
					'DeathSvid_IsPrimDiagMID' => 'i',
					'DeathSvid_IsPrimDiagEID' => 'i'
				);

				if(!empty($data['OrgHead_id'])){
					$res = $this->loadPostHeadData(array('OrgHead_id'=>$data['OrgHead_id']));
				}
				$data['OrgHeadPost_id'] = (!empty($res[0]['OrgHeadPost_id'])?$res[0]['OrgHeadPost_id']:null);
				$data['Person_hid'] = (!empty($res[0]['Person_id'])?$res[0]['Person_id']:null);

				if (!empty($data['DeathSvid_IsNoPlace'])) {
					$data['DeathSvid_IsNoPlace'] = 2;
				} else {
					$data['DeathSvid_IsNoPlace'] = 1;
				}
	            if (!empty($data['DeathSvid_isBirthDate'])) {
	                $data['DeathSvid_isBirthDate'] = 2;
	            } else {
	                $data['DeathSvid_isBirthDate'] = 1;
	            }
	            if (!empty($data['DeathSvid_IsUnknownDeathTime'])) {
	                $data['DeathSvid_IsNoDeathTime'] = 2;
	            } else {
	                $data['DeathSvid_IsNoDeathTime'] = 1;
	            }
	            if (!empty($data['DeathSvid_IsNoAccidentTime'])) {
	                $data['DeathSvid_IsNoAccidentTime'] = 2;
	            } else {
	                $data['DeathSvid_IsNoAccidentTime'] = 1;
	            }
				$data['DeathSvid_IsPrimDiagIID'] = !empty($data['DeathSvid_IsPrimDiagIID']) ? 2 : 1;
				$data['DeathSvid_IsPrimDiagTID'] = !empty($data['DeathSvid_IsPrimDiagTID']) ? 2 : 1;
				$data['DeathSvid_IsPrimDiagMID'] = !empty($data['DeathSvid_IsPrimDiagMID']) ? 2 : 1;
				$data['DeathSvid_IsPrimDiagEID'] = !empty($data['DeathSvid_IsPrimDiagEID']) ? 2 : 1;
				if (!empty($data['DeathSvid_DeathDate_Date'])) {
					$data['DeathSvid_DeathDate'] = $data['DeathSvid_DeathDate_Date'];
					if ($data['DeathSvid_DeathDate_Time'] != "") {
						$data['DeathSvid_DeathDate'] .= " ".$data['DeathSvid_DeathDate_Time'].":00";
					}
				}

				if ( empty($data['DeathSvid_DeathDate']) && !$data['DeathSvid_IsUnknownDeathDate'] && empty($data['DeathSvid_DeathDateStr']) ) {
					$data['DeathSvid_DeathDate'] = $data['DeathSvid_GiveDate'];
				}
				else if ( !empty($data['DeathSvid_IsUnknownDeathDate']) ) {
					$data['DeathSvid_DeathDate'] = null;
				}

				// проставить дату окончания региональной льготы (refs #6201)
				$querypp = "UPDATE PersonPrivilege SET PersonPrivilege_endDate = :endDate WHERE PersonPrivilege_endDate IS NULL AND Person_id = :Person_id AND exists((SELECT PrivilegeType  WHERE PrivilegeType_id = PersonPrivilege.PrivilegeType_id and ReceptFinance_id=2))";

				$this->db->query($querypp, array('Person_id' => $data['Person_id'], 'endDate' => (!empty($data['DeathSvid_DeathDate']) ? $data['DeathSvid_DeathDate'] : $data['DeathSvid_GiveDate'])));

				if (!empty($data['DeathSvid_TraumaDate_Date'])) {
					$data['DeathSvid_TraumaDate'] = $data['DeathSvid_TraumaDate_Date'];
					if ($data['DeathSvid_TraumaDate_Time'] != "") {
						$data['DeathSvid_TraumaDate'] .= " ".$data['DeathSvid_TraumaDate_Time'].":00";
					}
				}
			}

			if ($svid_type == 'pntdeath') {
				$param_array = array(
					'Server_id' => 'i',				
					'Person_id' => 'i', 
					'Person_cid' => 'i',
					'Lpu_id' => 'i',
					'Person_rid' => 'i',
					'PntDeathSvid_BirthDateStr' => 's',
					'PntDeathSvid_DeathDateStr' => 's',
					'PntDeathSvid_Ser' => 's', 
					'PntDeathSvid_Num' => 's', 
					'PntDeathPeriod_id' => 'i', 
					'DeathSvidType_id' => 'i',
					'PntDeathSvid_IsDuplicate' => 'i',
					'PntDeathSvid_IsLose' => 'i',
					'PntDeathSvid_IsActual' => 'i',
					'PntDeathSvid_OldSer' => 's',
					'PntDeathSvid_OldNum' => 's', 
					'MedPersonal_id' => 'i',
					'MedStaffFact_id' => 'i',
					'LpuSection_id' => 'i',
					'PntDeathSvid_DeathDate' => 'i', 
					'Address_did' => 'i', 
					'ReceptType_id' => 'i', 
					'PntDeathSvid_ChildFio' => 's', 
					'PntDeathSvid_ChildBirthDT' => 'i', 
					'PntDeathSvid_PlodIndex' => 's', 
					'PntDeathSvid_PlodCount' => 's', 
					'PntDeathSvid_RcpDoc' => 's', 
					'PntDeathSvid_RcpDate' => 'dt', 
					'PntDeathFamilyStatus_id' => 'i', 
					'DeathEmployment_id' => 'i', 
					'PntDeathPlace_id' => 'i', 
					'PntDeathEducation_id' => 'i', 
					'Sex_id' => 'i', 
					'PntDeathSvid_ChildCount' => 's', 
					'PntDeathSvid_BirthCount' => 's', 
					'PntDeathGetBirth_id' => 'i', 
					'PntDeathTime_id' => 'i', 
					'PntDeathCause_id' => 'i', 
					'PntDeathSetType_id' => 'i', 
					'PntDeathSetCause_id' => 'i', 
					'Diag_iid' => 'i', 
					'Diag_eid' => 'i', 
					'Diag_mid' => 'i', 
					'Diag_tid' => 'i', 
					'Diag_oid' => 'i',
					'PntDeathSvid_IsNoPlace' => 'i',
					'PntDeathSvid_Mass' => 's', 
					'PntDeathSvid_Height' => 's', 
					'PntDeathSvid_IsMnogoplod' => 'i', 
					'PntDeathSvid_GiveDate' => 'dt', 
					'PntDeathSvid_OldGiveDate' => 'dt', 
					'pmUser_id' => 'i',
					'PntDeathSvid_IsBad' => 'i',
					'DeputyKind_id' => 'i',
					'PntDeathSvid_ActNumber' => 's',
					'PntDeathSvid_ActDT' => 'dt',
					'Org_id' => 'i',
					'PntDeathSvid_PolFio' => 's',
					'PntDeathSvid_ZagsFIO' => 's',
					'PntDeathSvid_IsFromMother' => 'i',
					'OrgHead_id' => 'i',
					'OrgHeadPost_id' => 'i',
					'Person_hid' => 'i',
					'PntDeathSvidType_id' => 'i'
				);

				if(!empty($data['OrgHead_id'])){
					$res = $this->loadPostHeadData(array('OrgHead_id'=>$data['OrgHead_id']));
				}
				$data['OrgHeadPost_id'] = (!empty($res[0]['OrgHeadPost_id'])?$res[0]['OrgHeadPost_id']:null);
				$data['Person_hid'] = (!empty($res[0]['Person_id'])?$res[0]['Person_id']:null);

				if(!empty($data['OrgDep_id'])){
					$res = $this->loadOrgDepData(array('OrgDep_id'=>$data['OrgDep_id']));
				}
				$data['Org_id'] = (!empty($res[0]['Org_id'])?$res[0]['Org_id']:null);

				if(!empty($data['PntDeathSvid_ChildBirthDateStr'])){
					$data['PntDeathSvid_BirthDateStr'] = $data['PntDeathSvid_ChildBirthDateStr'];
				}

				if (!empty($data['PntDeathSvid_IsNoPlace'])) {
					$data['PntDeathSvid_IsNoPlace'] = 2;
				} else {
					$data['PntDeathSvid_IsNoPlace'] = 1;
				}
				if (!empty($data['PntDeathSvid_DeathDate_Date'])) {
					$data['PntDeathSvid_DeathDate'] = $data['PntDeathSvid_DeathDate_Date'];
					if (!empty($data['PntDeathSvid_DeathDate_Time'])) {
						$data['PntDeathSvid_DeathDate'] .= " ".$data['PntDeathSvid_DeathDate_Time'].":00";
					}
				}

				if (!empty($data['PntDeathSvid_ChildBirthDT_Date'])) {
					$data['PntDeathSvid_ChildBirthDT'] = $data['PntDeathSvid_ChildBirthDT_Date'];
					if ($data['PntDeathSvid_ChildBirthDT_Time'] != "") {
						$data['PntDeathSvid_ChildBirthDT'] .= " ".$data['PntDeathSvid_ChildBirthDT_Time'].":00";
					}
				}
			}
			
			$data['pmUser_id'] = $data['pmUser_id'];
			$data[$tab_name.'_IsBad'] = 1;
			$data[$tab_name.'_IsLose'] = 1;
			$data[$tab_name.'_IsActual'] = 2;
			if(empty($data[$tab_name.'_id'])){
				$mod = 'ins';
				$set = '';
			} else {
				$mod = 'upd';
				//$set = 'set @'.$tab_name.'_id = :'.$tab_name.'_id;';
				$sql_p_array[$tab_name.'_id'] = $data[$tab_name.'_id'];
			}	

			//$query_parts .= "{$tab_name}_id := :{$tab_name}_id";
			//$query_parts .= ", @Error_Code := Error_Code output";
			//$query_parts .= ", @Error_Message = @Error_Message output";

			foreach($param_array as $k => $v) { //формирование запроса
				$query_parts .= " $k := :$k,";
				$sql_p_array[$k] = (!empty($data[$k]) ? $data[$k] : null);
			}
			$query_parts = substr($query_parts, 0, strlen($query_parts) - 1);

			$query .= str_replace(",*", "", "
				select 
                	{$tab_name}_id as \"svid_id\", 
                    Error_Code as \"Error_Code\", 
                    Error_Message as \"Error_Message\"
				from dbo.p_{$tab_name}_{$mod}(
					{$query_parts}
                    );
			");
			//echo getDebugSQL($query, $sql_p_array);exit;

			$result = array();
			if ($query != "") {
				$result = array('success' => false);
				$res = $this->db->query($query, $sql_p_array);
				if (is_object($res)) {
					$res = $res->result('array');
					if (!empty($res[0]['Error_Message'])) {
						throw new Exception("Ошибка при выполнении запроса к БД: ".$res[0]['Error_Message']);
					} else if ($res[0]['svid_id'] > 0) {
						$result = array('success' => true, 'svid_id' => $res[0]['svid_id']);
					} else {
						throw new Exception("Ошибка при выполнении запроса к БД");
					}
				} else {
					throw new Exception("Ошибка при выполнении запроса к БД");
				}
			}
			//#PROMEDWEB-7451
			if ($svid_type == 'death') {
				$this->load->model('Person_model');
				if ($data['DeathSvid_IsBad'] != 2) {
					$deathDate = $data['DeathSvid_isUnknownDeathDate'] == 1 || !empty($data['DeathSvid_DeathDateStr'])
						? $data['DeathSvid_GiveDate']
						: $data['DeathSvid_DeathDate_Date'];
					$params = [
						'Person_id' => $data['Person_id'],
						'Person_deadDT' => $deathDate,
						'pmUser_id' => $data['pmUser_id'],
					];
					$res = $this->Person_model->killPerson($params);
					if (!empty($res[0]['Error_Message'])) {
						throw new Exception("Ошибка при выполнении запроса к БД: " . $res[0]['Error_Message']);
					}
					$this->load->library('swPersonRegister');
					swPersonRegister::onPersonDead($data);
				}
				if ($data['DeathSvid_IsBad'] == 2) {
					$params = [
						'Person_id' => $data['Person_id'],
						'pmUser_id' => $data['pmUser_id'],
					];
					$res = $this->Person_model->revivePerson($params);
					if (!empty($res[0]['Error_Message'])) {
						throw new Exception("Ошибка при выполнении запроса к БД: " . $res[0]['Error_Message']);
					}
					$this->load->library('swPersonRegister');
					$res = $this->queryResult("
						select
							PR.PersonRegister_id as \"PersonRegister_id\"
						from v_PersonRegister PR
						where PR.Person_id = :Person_id
							and PR.PersonRegister_disDate is not null
							and PR.PersonRegisterOutCause_id = 1
							and (PR.PersonRegisterType_id = 64 or PR.MorbusType_id = 94) -- Паллиативная помощь
						", ['Person_id' => $data['Person_id']]);
					foreach ($res as $row) {
						$params = array_merge([
							'PersonRegister_id' => $row['PersonRegister_id'],
							'pmUser_id' => $data['pmUser_id']
						], getSessionParams());
						$this->PRegister_model->back($params);
					}
				}
			}
			$this->db->trans_commit();
		} catch (Exception $e){
			$this->db->trans_rollback();
			return array('success'=>false,'Error_Msg'=>$e->getMessage());
		}
		
		return $result;
	}

	/**
	* Возвращает свидетельства о смерти по Персону. Метод для API
	*/
	function loadDeathSvidListByPerson($data) {
		$query = "
			select
				bs.DeathSvid_id as \"DeathSvid_id\",
				bs.DeathSvid_Ser as \"DeathSvid_Ser\",
				bs.DeathSvid_Num as \"DeathSvid_Num\",
				to_char(bs.DeathSvid_GiveDate, 'YYYY-MM-DD HH24:MI:SS') as \"DeathSvid_GiveDate\"

			from
				DeathSvid bs 

			where 
				bs.Person_id = :Person_id
		";
		
		$result = $this->db->query($query, $data);
		
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	* Возвращает Персону по свидетельству о смерти. Метод для API
	*/
	function loadDeathSvidData($data) {
		$query = "
			select
                Server_id as \"Server_id\",
                DeathPlace_id as \"DeathPlace_id\",
                DeathEducation_id as \"DeathEducation_id\",
                DeathTrauma_id as \"DeathTrauma_id\",
                DeathSetType_id as \"DeathSetType_id\",
                DeathSetCause_id as \"DeathSetCause_id\",
                DeathWomanType_id as \"DeathWomanType_id\",
                DeathEmployment_id as \"DeathEmployment_id\",
                DtpDeathTime_id as \"DtpDeathTime_id\",
                ChildTermType_id as \"ChildTermType_id\",
                Address_bid as \"Address_bid\",
                Address_did as \"Address_did\",
                Diag_iid as \"Diag_iid\",
                Diag_eid as \"Diag_eid\",
                Diag_mid as \"Diag_mid\",
                Diag_tid as \"Diag_tid\",
                Diag_oid as \"Diag_oid\",
                DeathSvid_Ser as \"DeathSvid_Ser\",
                DeathSvid_Num as \"DeathSvid_Num\",
                DeathSvid_OldSer as \"DeathSvid_OldSer\",
                DeathSvid_OldNum as \"DeathSvid_OldNum\",
                DeathSvid_GiveDate as \"DeathSvid_GiveDate\",
                DeathSvid_OldGiveDate as \"DeathSvid_OldGiveDate\",
                DeathSvid_DeathDate as \"DeathSvid_DeathDate\",
                DeathSvid_IsTerm as \"DeathSvid_IsTerm\",
                DeathSvid_Mass as \"DeathSvid_Mass\",
                DeathSvid_Month as \"DeathSvid_Month\",
                DeathSvid_Day as \"DeathSvid_Day\",
                DeathSvid_ChildCount as \"DeathSvid_ChildCount\",
                DeathSvid_TraumaDate as \"DeathSvid_TraumaDate\",
                DeathSvid_TraumaDescr as \"DeathSvid_TraumaDescr\",
                DeathSvid_Oper as \"DeathSvid_Oper\",
                DeathSvid_PribPeriod as \"DeathSvid_PribPeriod\",
                DeathSvid_RcpDate as \"DeathSvid_RcpDate\",
                DeathSvid_RcpDocument as \"DeathSvid_RcpDocument\",
                DeathSvid_IsBad as \"DeathSvid_IsBad\",
                pmUser_insID as \"pmUser_insID\",
                pmUser_updID as \"pmUser_updID\",
                DeathSvid_insDT as \"DeathSvid_insDT\",
                DeathSvid_updDT as \"DeathSvid_updDT\",
                FamilyStatus_id as \"FamilyStatus_id\",
                DeathSvid_IsSigned as \"DeathSvid_IsSigned\",
                pmUser_signID as \"pmUser_signID\",
                DeathSvid_signDT as \"DeathSvid_signDT\",
                DeathSvid_IsMarried as \"DeathSvid_IsMarried\",
                OrgHeadPost_id as \"OrgHeadPost_id\",
                Person_hid as \"Person_hid\",
                DeathSvid_IsDuplicate as \"DeathSvid_IsDuplicate\",
                DeathSvid_IsLose as \"DeathSvid_IsLose\",
                DeathSvid_BirthDateStr as \"DeathSvid_BirthDateStr\",
                DeathSvid_DeathDateStr as \"DeathSvid_DeathDateStr\",
                DeathSvid_PribPeriodPat as \"DeathSvid_PribPeriodPat\",
                DeathSvid_PribPeriodDom as \"DeathSvid_PribPeriodDom\",
                DeathSvid_PribPeriodExt as \"DeathSvid_PribPeriodExt\",
                DeathSvid_PribPeriodImp as \"DeathSvid_PribPeriodImp\",
                DeathSvid_LaborDateStr as \"DeathSvid_LaborDateStr\",
                MedStaffFact_id as \"MedStaffFact_id\",
                DeathSvid_PolFio as \"DeathSvid_PolFio\",
                DeathSvid_TraumaDateStr as \"DeathSvid_TraumaDateStr\",
                DeathSvid_IsActual as \"DeathSvid_IsActual\",
                DeathSvid_IsNoPlace as \"DeathSvid_IsNoPlace\",
                DeathSvid_isBirthDate as \"DeathSvid_isBirthDate\",
                DeathSvid_IsNoDeathTime as \"DeathSvid_IsNoDeathTime\",
                DeathSvid_IsNoAccidentTime as \"DeathSvid_IsNoAccidentTime\",
                DeathSvid_StacDate as \"DeathSvid_StacDate\",
                Diag_sid as \"Diag_sid\",
                DeathSvid_IsPrimDiagIID as \"DeathSvid_IsPrimDiagIID\",
                DeathSvid_IsPrimDiagTID as \"DeathSvid_IsPrimDiagTID\",
                DeathSvid_IsPrimDiagMID as \"DeathSvid_IsPrimDiagMID\",
                DeathSvid_IsPrimDiagEID as \"DeathSvid_IsPrimDiagEID\",
                MedStaffFact_did as \"MedStaffFact_did\",
                DeathSvid_TimePeriod as \"DeathSvid_TimePeriod\",
                Okei_id as \"Okei_id\",
                DeathSvid_TimePeriodPat as \"DeathSvid_TimePeriodPat\",
                Okei_patid as \"Okei_patid\",
                DeathSvid_TimePeriodDom as \"DeathSvid_TimePeriodDom\",
                Okei_domid as \"Okei_domid\",
                DeathSvid_TimePeriodExt as \"DeathSvid_TimePeriodExt\",
                Okei_extid as \"Okei_extid\",
                DeathSvid_TimePeriodImp as \"DeathSvid_TimePeriodImp\",
                Okei_impid as \"Okei_impid\",
                DeathSvid_id as \"DeathSvid_id\",
                Lpu_id as \"Lpu_id\",
                LpuSection_id as \"LpuSection_id\",
                Person_id as \"Person_id\",
                Person_mid as \"Person_mid\",
                Person_rid as \"Person_rid\",
                MedPersonal_id as \"MedPersonal_id\",
                DeathSvidType_id as \"DeathSvidType_id\",
                ReceptType_id as \"ReceptType_id\",
                DeathCause_id as \"DeathCause_id\",
                DeathFamilyStatus_id as \"DeathFamilyStatus_id\"
  			from
				DeathSvid 

			where 
				DeathSvid_id = :DeathSvid_id
		";
		
		$result = $this->db->query($query, $data);
		
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	* Получение информации по Свидетельству о смерти. Метод для API
	*/
	function loadDeathSvid($data) {
		$query = "
			select
				bs.DeathSvid_id as \"DeathSvid_id\"
				,bs.ReceptType_id as \"ReceptType_id\"
				,bs.DeathSvid_Ser as \"DeathSvid_Ser\"
				,bs.DeathSvid_Num as \"DeathSvid_Num\"
				,to_char(bs.DeathSvid_GiveDate, 'YYYY-MM-DD HH24:MI:SS') as \"DeathSvid_GiveDate\"

				,bs.DeathSvidType_id as \"DeathSvidType_id\"
				,bs.DeathSvid_OldSer as \"DeathSvid_OldSer\"
				,bs.DeathSvid_OldNum as \"DeathSvid_OldNum\"
				,to_char(bs.DeathSvid_OldGiveDate, 'YYYY-MM-DD HH24:MI:SS') as \"DeathSvid_OldGiveDate\"

				,bs.DeathSvid_BirthDateStr as \"DeathSvid_BirthDateStr\"
				,bs.DeathSvid_isBirthDate as \"DeathSvid_isBirthDate\"
				,to_char(bs.DeathSvid_DeathDate, 'DD.MM.YYYY') as \"DeathSvid_DeathDate_Date\"

				,to_char(bs.DeathSvid_DeathDate, 'HH24:MI:SS') as \"DeathSvid_DeathDate_Time\"

				,bs.DeathSvid_DeathDateStr as \"DeathSvid_DeathDateStr\"
				,case when bs.DeathSvid_DeathDate is null and bs.DeathSvid_DeathDateStr is null then 1 else 0 end as \"DeathSvid_isUnknownDeathDate\"
				,bs.DeathSvid_IsNoDeathTime as \"DeathSvid_isUnknownDeathTime\"
				,bs.LpuSection_id as \"LpuSection_id\"
				,bs.MedStaffFact_id as \"MedStaffFact_id\"
				,oh.OrgHead_id as \"OrgHead_id\"
				,bs.Person_mid as \"Person_mid\"
				,bs.Address_bid as \"Address_bid\"
				,bs.ChildTermType_id as \"ChildTermType_id\"
				,bs.DeathSvid_Mass as \"DeathSvid_Mass\"
				,bs.DeathSvid_ChildCount as \"DeathSvid_ChildCount\"
				,bs.DeathSvid_Month as \"DeathSvid_Month\"
				,bs.DeathSvid_Day as \"DeathSvid_Day\"
				,extract(YEAR from dbo.tzGetDate()-mp.Person_BirthDay) as \"Mother_Age\"
				,to_char(mp.Person_BirthDay, 'DD.MM.YYYY') as \"Mother_BirthDay\"

				,bs.DeathEmployment_id as \"DeathEmployment_id\"
				,bs.DeathEducation_id as \"DeathEducation_id\"
				,bs.DeathPlace_id as \"DeathPlace_id\"
				,bs.Address_did as \"Address_did\"
				,bs.DeathSvid_IsNoPlace as \"DeathSvid_IsNoPlace\"
				,bs.DeathFamilyStatus_id as \"DeathFamilyStatus_id\"
				,bs.DeathCause_id as \"DeathCause_id\"
				,to_char(bs.DeathSvid_TraumaDate, 'DD.MM.YYYY') as \"DeathSvid_TraumaDate_Date\"

				,to_char(bs.DeathSvid_TraumaDate, 'HH24:MI:SS') as \"DeathSvid_TraumaDate_Time\"

				,bs.DeathSvid_TraumaDateStr as \"DeathSvid_TraumaDateStr\"
				,bs.DeathTrauma_id as \"DeathTrauma_id\"
				,bs.DtpDeathTime_id as \"DtpDeathTime_id\"
				,bs.DeathSvid_TraumaDescr as \"DeathSvid_TraumaDescr\"
				,bs.DeathSetType_id as \"DeathSetType_id\"
				,bs.DeathSetCause_id as \"DeathSetCause_id\"
				,bs.Diag_iid as \"Diag_iid\"
				,bs.Diag_tid as \"Diag_tid\"
				,bs.Diag_mid as \"Diag_mid\"
				,bs.Diag_eid as \"Diag_eid\"
				,bs.Diag_oid as \"Diag_oid\"
				,bs.DeathSvid_Oper as \"DeathSvid_Oper\"
				,bs.Person_rid as \"Person_rid\"
				,bs.DeathSvid_PolFio as \"DeathSvid_PolFio\"
				,bs.DeathSvid_RcpDocument as \"DeathSvid_RcpDocument\"
				,to_char(bs.DeathSvid_RcpDate, 'YYYY-MM-DD HH24:MI:SS') as \"DeathSvid_RcpDate\"

			from
				v_DeathSvid bs 

				left join v_Person_ER mp  on mp.Person_id = bs.Person_mid

				left join v_OrgHead oh  on oh.Person_id = bs.Person_hid and oh.OrgHeadPost_id = bs.OrgHeadPost_id

			where 
				bs.DeathSvid_id = :DeathSvid_id
		";
		
		$result = $this->db->query($query, $data);
		
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	* Получение информации по руководителю МО
	*/
	function loadPostHeadData($data) {
		$query = "
			select
				OrgHeadPost_id as \"OrgHeadPost_id\",
				Person_id as \"Person_id\"
			from
				v_OrgHead 

			where 
				OrgHead_id = :OrgHead_id
		";
		
		$result = $this->db->query($query, $data);
		
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	* Получение информации по Органу ЗАГС 
	*/
	function loadOrgDepData($data) {
		$query = "
			select
				Org_id as \"Org_id\"
			from
				v_OrgDep 

			where 
				OrgDep_id = :OrgDep_id
		";
		
		$result = $this->db->query($query, $data);
		
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	* Возвращает свидетельства о перинатальной смерти по Персону. Метод для API
	*/
	function loadPntDeathSvidListByPerson($data) {
		$query = "
			select
				bs.PntDeathSvid_id as \"PntDeathSvid_id\",
				bs.PntDeathSvid_Ser as \"PntDeathSvid_Ser\",
				bs.PntDeathSvid_Num as \"PntDeathSvid_Num\",
				to_char(bs.PntDeathSvid_GiveDate, 'YYYY-MM-DD HH24:MI:SS') as \"PntDeathSvid_GiveDate\"

			from
				PntDeathSvid bs 

			where 
				bs.Person_id = :Person_id
		";
		
		$result = $this->db->query($query, $data);
		
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	* Возвращает Персону по свидетельству о перинатальной смерти. Метод для API
	*/
	function loadPntDeathSvidData($data) {
		$query = "
			select
                PntDeathSvid_PlodCount as \"PntDeathSvid_PlodCount\",
                PntDeathSvid_RcpDoc as \"PntDeathSvid_RcpDoc\",
                PntDeathSvid_RcpDate as \"PntDeathSvid_RcpDate\",
                PntDeathFamilyStatus_id as \"PntDeathFamilyStatus_id\",
                DeathEmployment_id as \"DeathEmployment_id\",
                PntDeathPlace_id as \"PntDeathPlace_id\",
                Nation_id as \"Nation_id\",
                NationOther_id as \"NationOther_id\",
                PntDeathEducation_id as \"PntDeathEducation_id\",
                Sex_id as \"Sex_id\",
                PntDeathSvid_ChildCount as \"PntDeathSvid_ChildCount\",
                PntDeathSvid_BirthCount as \"PntDeathSvid_BirthCount\",
                PntDeathSvid_PregnCount as \"PntDeathSvid_PregnCount\",
                PntDeathGetBirth_id as \"PntDeathGetBirth_id\",
                PntDeathTime_id as \"PntDeathTime_id\",
                PntDeathCause_id as \"PntDeathCause_id\",
                PntDeathSetType_id as \"PntDeathSetType_id\",
                PntDeathSetCause_id as \"PntDeathSetCause_id\",
                Diag_iid as \"Diag_iid\",
                Diag_eid as \"Diag_eid\",
                Diag_mid as \"Diag_mid\",
                Diag_tid as \"Diag_tid\",
                Diag_oid as \"Diag_oid\",
                PntDeathSvid_Mass as \"PntDeathSvid_Mass\",
                PntDeathSvid_Height as \"PntDeathSvid_Height\",
                PntDeathSvid_IsMnogoplod as \"PntDeathSvid_IsMnogoplod\",
                PntDeathSvid_GiveDate as \"PntDeathSvid_GiveDate\",
                PntDeathSvid_OldGiveDate as \"PntDeathSvid_OldGiveDate\",
                pmUser_insID as \"pmUser_insID\",
                pmUser_updID as \"pmUser_updID\",
                PntDeathSvid_insDT as \"PntDeathSvid_insDT\",
                PntDeathSvid_updDT as \"PntDeathSvid_updDT\",
                PntDeathSvid_isBad as \"PntDeathSvid_isBad\",
                FamilyStatus_id as \"FamilyStatus_id\",
                Server_id as \"Server_id\",
                PntDeathSvid_id as \"PntDeathSvid_id\",
                Person_id as \"Person_id\",
                Lpu_id as \"Lpu_id\",
                LpuSection_id as \"LpuSection_id\",
                PntDeathSvid_Ser as \"PntDeathSvid_Ser\",
                PntDeathSvid_Num as \"PntDeathSvid_Num\",
                PntDeathPeriod_id as \"PntDeathPeriod_id\",
                DeathSvidType_id as \"DeathSvidType_id\",
                PntDeathSvid_OldSer as \"PntDeathSvid_OldSer\",
                PntDeathSvid_OldNum as \"PntDeathSvid_OldNum\",
                MedPersonal_id as \"MedPersonal_id\",
                PntDeathSvid_DeathDate as \"PntDeathSvid_DeathDate\",
                Address_did as \"Address_did\",
                Person_rid as \"Person_rid\",
                ReceptType_id as \"ReceptType_id\",
                PntDeathSvid_ChildFio as \"PntDeathSvid_ChildFio\",
                PntDeathSvid_ChildBirthDT as \"PntDeathSvid_ChildBirthDT\",
                PntDeathSvid_PlodIndex as \"PntDeathSvid_PlodIndex\",
                PntDeathSvid_IsSigned as \"PntDeathSvid_IsSigned\",
                pmUser_signID as \"pmUser_signID\",
                PntDeathSvid_signDT as \"PntDeathSvid_signDT\",
                DeputyKind_id as \"DeputyKind_id\",
                PntDeathSvid_IsMarried as \"PntDeathSvid_IsMarried\",
                Org_id as \"Org_id\",
                PntDeathSvid_ZagsFIO as \"PntDeathSvid_ZagsFIO\",
                PntDeathSvid_ActNumber as \"PntDeathSvid_ActNumber\",
                PntDeathSvid_ActDT as \"PntDeathSvid_ActDT\",
                PntDeathSvid_IsFromMother as \"PntDeathSvid_IsFromMother\",
                OrgHead_id as \"OrgHead_id\",
                PntDeathSvidType_id as \"PntDeathSvidType_id\",
                OrgHeadPost_id as \"OrgHeadPost_id\",
                Person_hid as \"Person_hid\",
                PntDeathSvid_IsDuplicate as \"PntDeathSvid_IsDuplicate\",
                PntDeathSvid_IsLose as \"PntDeathSvid_IsLose\",
                PntDeathSvid_BirthDateStr as \"PntDeathSvid_BirthDateStr\",
                PntDeathSvid_DeathDateStr as \"PntDeathSvid_DeathDateStr\",
                PntDeathSvid_LaborDateStr as \"PntDeathSvid_LaborDateStr\",
                MedStaffFact_id as \"MedStaffFact_id\",
                PntDeathSvid_PolFio as \"PntDeathSvid_PolFio\",
                PntDeathSvid_TraumaDateStr as \"PntDeathSvid_TraumaDateStr\",
                PntDeathSvid_IsActual as \"PntDeathSvid_IsActual\",
                PntDeathSvid_IsNoPlace as \"PntDeathSvid_IsNoPlace\",
                PntDeathSvid_isBirthDate as \"PntDeathSvid_isBirthDate\",
                PntDeathSvid_IsNoDeathTime as \"PntDeathSvid_IsNoDeathTime\",
                PntDeathSvid_IsNoAccidentTime as \"PntDeathSvid_IsNoAccidentTime\",
                Person_cid as \"Person_cid\",
                MedStaffFact_did as \"MedStaffFact_did\",
                PntDeathSvid_Deleted as \"PntDeathSvid_Deleted\",
                PntDeathSvid_delDT as \"PntDeathSvid_delDT\",
                PntDeathSvid_Rowversion as \"PntDeathSvid_Rowversion\"
  			from
				PntDeathSvid 

			where 
				PntDeathSvid_id = :PntDeathSvid_id
		";
		
		$result = $this->db->query($query, $data);
		
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	* Получение информации по Свидетельству о перинатальной смерти. Метод для API
	*/
	function loadPntDeathSvid($data) {
		$query = "
			select
				bs.PntDeathSvid_id as \"PntDeathSvid_id\"
				,bs.ReceptType_id as \"ReceptType_id\"
				,bs.PntDeathSvid_Ser as \"PntDeathSvid_Ser\"
				,bs.PntDeathSvid_Num as \"PntDeathSvid_Num\"
				,to_char(bs.PntDeathSvid_GiveDate, 'YYYY-MM-DD HH24:MI:SS') as \"PntDeathSvid_GiveDate\"

				,bs.DeathSvidType_id as \"DeathSvidType_id\"
				,bs.PntDeathSvid_OldSer as \"PntDeathSvid_OldSer\"
				,bs.PntDeathSvid_OldNum as \"PntDeathSvid_OldNum\"
				,to_char(bs.PntDeathSvid_OldGiveDate, 'YYYY-MM-DD HH24:MI:SS') as \"PntDeathSvid_OldGiveDate\"

				,to_char(bs.PntDeathSvid_DeathDate, 'YYYY-MM-DD HH24:MI:SS') as \"PntDeathSvid_DeathDate\"

				,bs.PntDeathSvid_DeathDateStr as \"PntDeathSvid_DeathDateStr\"
				,bs.LpuSection_id as \"LpuSection_id\"
				,bs.MedStaffFact_id as \"MedStaffFact_id\"
				,bs.OrgHead_id as \"OrgHead_id\"
				,to_char(bs.PntDeathSvid_ChildBirthDT, 'YYYY-MM-DD HH24:MI:SS') as \"PntDeathSvid_ChildBirthDT\"

				,bs.PntDeathSvid_BirthDateStr as \"PntDeathSvid_ChildBirthDateStr\"
				,bs.PntDeathTime_id as \"PntDeathTime_id\"
				,bs.PntDeathPeriod_id as \"PntDeathPeriod_id\"
				,bs.DeathEmployment_id as \"DeathEmployment_id\"
				,bs.PntDeathEducation_id as \"PntDeathEducation_id\"
				,bs.PntDeathFamilyStatus_id as \"PntDeathFamilyStatus_id\"
				,bs.PntDeathSvid_BirthCount as \"PntDeathSvid_BirthCount\"
				,bs.PntDeathSvid_ChildCount as \"PntDeathSvid_ChildCount\"
				,bs.PntDeathSvid_ChildFio as \"PntDeathSvid_ChildFio\"
				,bs.PntDeathPlace_id as \"PntDeathPlace_id\"
				,bs.Address_did as \"Address_did\"
				,case when bs.PntDeathSvid_IsNoPlace = 2 then 1 else 0 end as \"PntDeathSvid_IsNoPlace\"
				,bs.Sex_id as \"Sex_id\"
				,bs.PntDeathGetBirth_id as \"PntDeathGetBirth_id\"
				,bs.PntDeathSvid_Mass as \"PntDeathSvid_Mass\"
				,bs.PntDeathSvid_Height as \"PntDeathSvid_Height\"
				,bs.PntDeathSvid_IsMnogoplod as \"PntDeathSvid_IsMnogoplod\"
				,bs.PntDeathSvid_PlodIndex as \"PntDeathSvid_PlodIndex\"
				,bs.PntDeathSvid_PlodCount as \"PntDeathSvid_PlodCount\"
				,bs.PntDeathCause_id as \"PntDeathCause_id\"
				,bs.PntDeathSvid_ActNumber as \"PntDeathSvid_ActNumber\"
				,to_char(bs.PntDeathSvid_ActDT, 'YYYY-MM-DD HH24:MI:SS') as \"PntDeathSvid_ActDT\"

				,od.OrgDep_id as \"OrgDep_id\"
				,bs.PntDeathSvid_ZagsFIO as \"PntDeathSvid_ZagsFIO\"
				,bs.PntDeathSvidType_id as \"PntDeathSvidType_id\"
				,bs.Diag_iid as \"Diag_iid\"
				,bs.Diag_tid as \"Diag_tid\"
				,bs.Diag_mid as \"Diag_mid\"
				,bs.Diag_eid as \"Diag_eid\"
				,bs.Diag_oid as \"Diag_oid\"
				,bs.PntDeathSetType_id as \"PntDeathSetType_id\"
				,bs.PntDeathSetCause_id as \"PntDeathSetCause_id\"
				,bs.Person_rid as \"Person_rid\"
				,bs.PntDeathSvid_PolFio as \"PntDeathSvid_PolFio\"
				,bs.PntDeathSvid_RcpDoc as \"PntDeathSvid_RcpDoc\"
				,to_char(bs.PntDeathSvid_RcpDate, 'YYYY-MM-DD HH24:MI:SS') as \"PntDeathSvid_RcpDate\"

				,bs.DeputyKind_id as \"DeputyKind_id\"
				,bs.PntDeathSvid_IsFromMother as \"PntDeathSvid_IsFromMother\"
			from
				v_PntDeathSvid bs 

				left join v_OrgDep od  on od.Org_id = bs.Org_id

			where 
				bs.PntDeathSvid_id = :PntDeathSvid_id
		";
		
		$result = $this->db->query($query, $data);
		
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	
	/**
	 * Печать свидетельства о рождении в формате HL7
	 */
	function printBirthSvidHL7($data)
	{
		$resp = $this->queryResult("
			select
				bs.BirthSvid_id as \"BirthSvid_id\", /*идентификатор bigint*/
				to_char(bs.BirthSvid_GiveDate, 'YYYYMMDD') as \"BirthSvid_GiveDate\",
				LpuOID.PassportToken_tid as \"PassportToken_tid\",
				bs.Person_id as \"Person_id\",
				PS.Person_Snils as \"Person_Snils\",
				PS.Person_SurName as \"Person_SurName\",
				PS.Person_FirName as \"Person_FirName\",
				PS.Person_SecName as \"Person_SecName\",
				psr.Person_SurName as \"RPerson_SurName\",
				psr.Person_FirName as \"RPerson_FirName\",
				psr.Person_SecName as \"RPerson_SecName\",
				s.Sex_Code as \"Sex_Code\",
				s.Sex_Name as \"Sex_Name\",
				ua.Address_Address as \"Address_Address\",
				ua.KLRgn_id as \"KLRgn_id\",
				ps.Person_Phone as \"Person_Phone\",
				VPI.PersonInfo_Email as \"PersonInfo_Email\",
				L.Lpu_Nick as \"Lpu_Nick\",
				L.Lpu_Phone as \"Lpu_Phone\",
				OL.Org_Www as \"Lpu_Www\",
				lua.Address_Address as \"LAddress_Address\",
				lua.KLRgn_id as \"LKLRgn_id\",
				to_char(ps.Person_BirthDay, 'YYYYMMDD') as \"Person_BirthDay\",
				msf.MedPersonal_id as \"MedPersonal_id\",
				msf.Person_SurName as \"MedPersonal_SurName\",
				msf.Person_FirName as \"MedPersonal_FirName\",
				msf.Person_SecName as \"MedPersonal_SecName\",
				bs.BirthSvid_Ser as \"BirthSvid_Ser\",
				bs.BirthSvid_Num as \"BirthSvid_Num\",
				d.Document_Num as \"Document_Num\",
				d.Document_Ser as \"Document_Ser\",
				ndt.DocumentType_Name as \"DocumentType_Name\",
				ndt.DocumentType_Code as \"DocumentType_Code\",
				to_char(d.Document_begDate, 'DD.MM.YYYY') as \"Document_begDate\",
				o.Org_Name as \"DocOrg_Name\",
				o.Org_Code as \"DocOrg_Code\",
				bs.BirthSvid_ChildFamil as \"BirthSvid_ChildFamil\",
				to_char(bs.BirthSvid_BirthDT, 'YYYYMMDD') as \"BirthSvid_BirthDT\",
				to_char(bs.BirthSvid_BirthDT, 'YYYY-MM-DD HH24:MI:SS') as \"BirthSvid_BirthDT_Format\",
				bs.BirthSvid_IsMnogoplod as \"BirthSvid_IsMnogoplod\",
				coalesce(bs.BirthSvid_PlodCount, 1) as \"BirthSvid_PlodCount\",
				coalesce(bs.BirthSvid_ChildCount, 1) as \"BirthSvid_ChildCount\",
				coalesce(bs.BirthSvid_PlodIndex, 1) as \"BirthSvid_PlodIndex\",
				bs.BirthSvid_Mass as \"BirthSvid_Mass\",
				bs.BirthSvid_Height as \"BirthSvid_Height\",
				bs.Okei_mid as \"Okei_mid\",
				bp.BirthPlace_Code as \"BirthPlace_Code\",
				bp.BirthPlace_Name as \"BirthPlace_Name\",
				bsp.BirthSpecialist_Code as \"BirthSpecialist_Code\",
				bsp.BirthSpecialist_Name as \"BirthSpecialist_Name\",
				bs.BirthSvid_Week as \"BirthSvid_Week\",
				be.BirthEmployment_Code as \"BirthEmployment_Code\",
				be.BirthEmployment_Name as \"BirthEmployment_Name\",
				bed.BirthEducation_Code as \"BirthEducation_Code\",
				bed.BirthEducation_Name as \"BirthEducation_Name\",
				fs.FamilyStatus_Code as \"FamilyStatus_Code\",
				fs.FamilyStatus_Name as \"FamilyStatus_Name\",
				klat.KLAreaType_Code as \"KLAreaType_Code\",
				klat.KLAreaType_Name as \"KLAreaType_Name\",
				uasr.KLArea_Name as \"KLSubRgn_Name\",
				coalesce(uat.KLArea_Name, uac.KLArea_Name) as \"KLCity_Name\",
				uas.KLStreet_Name as \"KLStreet_Name\",
				ua.Address_Corpus as \"Address_Corpus\",
				ua.Address_House as \"Address_House\",
				ua.Address_Flat as \"Address_Flat\",
				COALESCE(uas.KLStreet_AOGUID, uat.KLArea_AOGUID, uac.KLArea_AOGUID, uasr.KLArea_AOGUID, uar.KLArea_AOGUID) as \"KLAreaGUID\",
				psm.Person_Snils as \"MedPersonal_Snils\",
				psm.Person_Phone as \"MedPersonal_Phone\",
				mua.Address_Address as \"MAddress_Address\",
				mua.KLRgn_id as \"MKLRgn_id\",
				to_char(bs.BirthSvid_RcpDate, 'YYYYMMDD') as \"BirthSvid_RcpDate\",
				psr.Person_Snils as \"RPerson_Snils\",
				mp.MedPost_Code as \"MedPost_Code\",
				mp.MedPost_Name as \"MedPost_Name\",
				dt.Frmr_id as \"Frmr_id\",
				bs.DeputyKind_id as \"DeputyKind_id\",
				psr.Sex_id as \"RSex_id\",
				ps.Polis_Num as \"Polis_Num\"
			from
				dbo.v_BirthSvid bs
				left join v_MedStaffFact msf on msf.MedStaffFact_id = bs.MedStaffFact_id
				left join persis.Post p on p.id = msf.Post_id
				left join nsi.v_MedPost mp on mp.MedPost_id = p.MedPost_id
				left join fed.v_PassportToken LpuOID on LpuOID.Lpu_id = bs.Lpu_id
				left join v_PersonState ps on ps.Person_id = bs.Person_id
				left join v_PersonState psr on psr.Person_id = bs.Person_rid
				left join v_PersonState psm on psm.Person_id = msf.Person_id
				left join v_Document d on d.Document_id = ps.Document_id
				left join v_DocumentType dt on dt.DocumentType_id = d.DocumentType_id
				left join nsi.v_DocumentType ndt on ndt.DocumentType_id = dt.Frmr_id
				left join v_Org o on o.Org_id = d.OrgDep_id
				left join v_Sex s on s.Sex_id = ps.Sex_id
				left join v_Address_all ua on ua.Address_id = ps.UAddress_id
				left join v_PersonInfo VPI on VPI.Person_id = PS.Person_id
				left join v_Lpu l on l.Lpu_id = bs.Lpu_id
				left join v_Org ol on ol.Org_id = l.Org_id
				left join v_Address_all lua on lua.Address_id = l.UAddress_id
				left join v_BirthPlace bp on bp.BirthPlace_id = bs.BirthPlace_id
				left join v_BirthSpecialist bsp on bsp.BirthSpecialist_id = bs.BirthSpecialist_id
				left join v_BirthEmployment be on be.BirthEmployment_id = bs.BirthEmployment_id
				left join v_BirthEducation bed on bed.BirthEducation_id = bs.BirthEducation_id
				left join v_FamilyStatus fs on fs.FamilyStatus_id = bs.BirthFamilyStatus_id
				left join v_Address_all ra on ra.Address_id = bs.Address_rid
				left join v_KLAreaType klat on klat.KLAreaType_id = ua.KLAreaType_id
				left join v_KLArea uar on uar.KLArea_id = ua.KLRgn_id
				left join v_KLArea uasr on uasr.KLArea_id = ua.KLSubRgn_id
				left join v_KLArea uac on uac.KLArea_id = ua.KLCity_id
				left join v_KLArea uat on uat.KLArea_id = ua.KLTown_id
				left join v_KLStreet uas on uas.KLStreet_id = ua.KLStreet_id
				left join v_Address_all mua on mua.Address_id = psm.UAddress_id
			where
				bs.BirthSvid_id = :BirthSvid_id
			limit 1
		", [
			'BirthSvid_id' => $data['BirthSvid_id']
		]);
		$errors=[];
		/*конвертируем дату рождения в не стандартный формат YYYY-MM-DD HH:mm:SS => YYYYMMDDHHmm+ЗОНА_времени*/
		$resp[0]["BirthSvid_BirthDT_Format"]=date("YmdHiO",strtotime($resp[0]["BirthSvid_BirthDT_Format"]));
		
		/*на всякий случай смотрим номера телефонов: как минимум должна быть одна цифра */
		if (preg_match("/\d/i",$resp[0]["Lpu_Phone"])!=1){
			$resp[0]["Lpu_Phone"]=0;
		}
		if (preg_match("/\d/i",$resp[0]["MedPersonal_Phone"])!=1){
			$resp[0]["MedPersonal_Phone"]=0;
		}

		if (empty($resp[0]['BirthSvid_id'])) {
			throw new Exception('Ошибка получения данных при создании МСР', 500);
		}

		$resp[0]['assignedTime'] = date('Y-m-d');
		$resp[0]['isAssigned'] = 'S';

		if ($resp[0]['BirthSvid_IsMnogoplod'] == 2) {
			$resp[0]['BirthSvid_IsMnogoplodCode'] = 2;
			$resp[0]['BirthSvid_IsMnogoplodName'] = 'многоплодные роды';
		} else {
			$resp[0]['BirthSvid_IsMnogoplodCode'] = 1;
			$resp[0]['BirthSvid_IsMnogoplodName'] = 'одноплодные роды';
		}

		if ($resp[0]['DeputyKind_id'] == 2) {
			$resp[0]['DeputyKind_FirstCode'] = 'PRS';
			if ($resp[0]['RSex_id'] == 2) {
				$resp[0]['DeputyKind_SecCode'] = 1;
				$resp[0]['DeputyKind_SecName'] = 'мать';
			} else {
				$resp[0]['DeputyKind_SecCode'] = 2;
				$resp[0]['DeputyKind_SecName'] = 'отец';
			}
		} else {
			$resp[0]['BirthSvid_IsMnogoplodCode'] = 1;
			$resp[0]['DeputyKind_FirstCode'] = 'AGNT';
			$resp[0]['DeputyKind_SecCode'] = 4;
			$resp[0]['DeputyKind_SecName'] = 'уполномоченное лицо';
		}

		if ($resp[0]['Okei_mid'] == 37) {
			$resp[0]['BirthSvid_Mass'] *= 1000;
		}

		$BirthSvid_BirthDT_TS = strtotime($resp[0]['BirthSvid_BirthDT']);
		$BirthSvid_GiveDate_TS = strtotime($resp[0]['BirthSvid_GiveDate']);
		$Person_BirthDay_TS = strtotime($resp[0]['Person_BirthDay']);

		$arMonthOf = [
			1 => "января",
			2 => "февраля",
			3 => "марта",
			4 => "апреля",
			5 => "мая",
			6 => "июня",
			7 => "июля",
			8 => "августа",
			9 => "сентября",
			10 => "октября",
			11 => "ноября",
			12 => "декабря",
		];

		$resp[0]['BirthSvid_BirthDTFormatted'] = (int)date('d', $BirthSvid_BirthDT_TS) . ' '
			. $arMonthOf[(int)date('m', $BirthSvid_BirthDT_TS)] . ' '
			. date('Y', $BirthSvid_BirthDT_TS) . ' года';

		$resp[0]['BirthSvid_GiveDateFormatted'] = '"' . (int)date('d', $BirthSvid_GiveDate_TS) . '" '
			. $arMonthOf[(int)date('m', $BirthSvid_GiveDate_TS)] . ' '
			. date('Y', $BirthSvid_GiveDate_TS) . ' г';

		$resp[0]['Person_BirthDayFormatted'] = (int)date('d', $Person_BirthDay_TS) . ' '
			. $arMonthOf[(int)date('m', $Person_BirthDay_TS)] . ' '
			. date('Y', $Person_BirthDay_TS) . ' года';
		
		//смотрим обязательные поля
		if (empty($resp[0]["KLAreaType_Code"])){
			$errors[]="Невозможно получить признак жителя города или села, не заполнен адрес регистрации матери в карточке пациента";
		}

		if (empty($resp[0]["FamilyStatus_Code"])){
			$errors[]="Не указано семейное положение матери";
		}

		if (empty($resp[0]["Polis_Num"])){
			$errors[]="Не указан полис ОМС в карточке пациента";
		}

		
		if (!empty($errors)){
			throw new Exception('<b>Обнаружены ошибки:</b> <br/>' . implode("<br />", $errors));
		}

		$this->load->library('parser');

		$xml = '<?xml version="1.0" encoding="UTF-8"?>';
		$xml .= '<?xml-stylesheet type="text/xsl" href="/documents/xsl/SertOfBirth.xsl"?>';
		$xml .= '<?valbuddy_schematron Schematron.sch?>';
		$xml .= $this->parser->parse('print_birthsvid_hl7', $resp[0], true);
		 
		// проверяем xml по xsd схеме
		$xsd = realpath('documents/xsd/MedSvid/CDA.xsd');
		$domDoc = new DOMDocument();
		$domDoc->loadXML($xml);
		libxml_use_internal_errors(true);
		if (!$domDoc->schemaValidate($xsd)) {
			$errors = array_map(function ($error) {
			return trim($error->message) . ' on line ' . $error->line;
		   }, libxml_get_errors());
		   libxml_clear_errors();
		   if (!empty($_REQUEST['getDebug'])) {
			echo "<textarea cols=150 rows=20>" . $xml . "</textarea>";
		   }
		   throw new Exception('Ошибка при проверке документа в формате HL7 по XSD схеме: <br>' . implode("<br>", $errors) . '<br><br>Сформированный HL7:<br><textarea cols="50" rows="10">'.$xml.'</textarea>');
		  }	
		return array('xml' => $xml);
	}
	
	/**
	 * Печать свидетельства о смерти в формате HL7
	 */
	function printDeathSvidHL7($data)
	{
		$resp = $this->queryResult("
			select
				ds.DeathSvid_id as \"DeathSvid_id\", 
				to_char(ds.DeathSvid_GiveDate, 'YYYYMMDD') as \"DeathSvid_GiveDate\",
				LpuOID.PassportToken_tid as \"PassportToken_tid\",
				ds.Person_id as \"Person_id\",
				PS.Person_Snils as \"Person_Snils\",
				PS.Person_SurName as \"Person_SurName\",
				PS.Person_FirName as \"Person_FirName\",
				PS.Person_SecName as \"Person_SecName\",
				psr.Person_SurName as \"RPerson_SurName\",
				psr.Person_FirName as \"RPerson_FirName\",
				psr.Person_SecName as \"RPerson_SecName\",
				s.Sex_Code as \"Sex_Code\",
				s.Sex_Name as \"Sex_Name\",
				ua.Address_Address as \"Address_Address\",
				ua.KLRgn_id as \"KLRgn_id\",
				ps.Person_Phone as \"Person_Phone\",
				VPI.PersonInfo_Email as \"PersonInfo_Email\",
				L.Lpu_Nick as \"Lpu_Nick\",
				L.Lpu_Phone as \"Lpu_Phone\",
				OL.Org_Www as \"Lpu_Www\",
				lua.Address_Address as \"LAddress_Address\",
				lua.KLRgn_id as \"LKLRgn_id\",
				to_char(ps.Person_BirthDay, 'YYYYMMDD') as \"Person_BirthDay\",
				msf.MedPersonal_id as \"MedPersonal_id\",
				msf.Person_SurName as \"MedPersonal_SurName\",
				msf.Person_FirName as \"MedPersonal_FirName\",
				msf.Person_SecName as \"MedPersonal_SecName\",
				msfh.MedPersonal_id as \"MedPersonal_hid\",
				msfh.Person_SurName as \"MedPersonal_hSurName\",
				msfh.Person_FirName as \"MedPersonal_hFirName\",
				msfh.Person_SecName as \"MedPersonal_hSecName\",
				ds.DeathSvid_Ser as \"DeathSvid_Ser\",
				ds.DeathSvid_Num as \"DeathSvid_Num\",
				d.Document_Num as \"Document_Num\",
				d.Document_Ser as \"Document_Ser\",
				to_char(d.Document_begDate, 'DD.MM.YYYY') as \"Document_begDate\",
				o.Org_Name as \"DocOrg_Name\",
				ds.DeathSvid_Mass as \"DeathSvid_Mass\",
				bp.DeathPlace_Code as \"DeathPlace_Code\",
				bp.DeathPlace_Name as \"DeathPlace_Name\",
				be.DeathEmployment_Code as \"DeathEmployment_Code\",
				be.DeathEmployment_Name as \"DeathEmployment_Name\",
				bed.DeathEducation_Code as \"DeathEducation_Code\",
				bed.DeathEducation_Name as \"DeathEducation_Name\",
				fs.FamilyStatus_Code as \"FamilyStatus_Code\",
				fs.FamilyStatus_Name as \"FamilyStatus_Name\",
				klat.KLAreaType_Code as \"KLAreaType_Code\",
				klat.KLAreaType_Name as \"KLAreaType_Name\",
				uasr.KLSubRgn_Name as \"KLSubRgn_Name\",
				coalesce(uac.KLCity_Name, uat.KLTown_Name) as \"KLCity_Name\",
				uas.KLStreet_Name as \"KLStreet_Name\",
				ua.Address_Corpus as \"Address_Corpus\",
				ua.Address_House as \"Address_House\",
				ua.Address_Flat as \"Address_Flat\",
				dklat.KLAreaType_Code as \"DKLAreaType_Code\",
				dklat.KLAreaType_Name as \"DKLAreaType_Name\",
				dasr.KLSubRgn_Name as \"DKLSubRgn_Name\",
				coalesce(dac.KLCity_Name, dat.KLTown_Name) as \"DKLCity_Name\",
				das.KLStreet_Name as \"DKLStreet_Name\",
				da.Address_Corpus as \"DAddress_Corpus\",
				da.Address_House as \"DAddress_House\",
				da.Address_Flat as \"DAddress_Flat\",
				da.Address_Address as \"DAddress_Address\",
				da.KLRgn_id as \"DKLRgn_id\",
				psm.Person_Snils as \"MedPersonal_Snils\",
				psm.Person_Phone as \"MedPersonal_Phone\",
				mua.Address_Address as \"MAddress_Address\",
				mua.KLRgn_id as \"MKLRgn_id\",
				psmh.Person_Snils as \"MedPersonal_hSnils\",
				psmh.Person_Phone as \"MedPersonal_hPhone\",
				mhua.Address_Address as \"MHAddress_Address\",
				mhua.KLRgn_id as \"MHKLRgn_id\",
				ds.DeathSvidRelation_id as \"DeathSvidRelation_id\",
				to_char(ds.DeathSvid_RcpDate, 'YYYYMMDD') as \"DeathSvid_RcpDate\",
				psr.Person_Snils as \"RPerson_Snils\",
				mp.MedPost_Code as \"MedPost_Code\",
				mp.MedPost_Name as \"MedPost_Name\",
				msfh.MedPost_Code as \"MedPost_hCode\",
				msfh.MedPost_Name as \"MedPost_hName\",
				dt.Frmr_id as \"Frmr_id\",
				psr.Sex_id as \"RSex_id\",
				dwt.DeathWomanType_Code as \"DeathWomanType_Code\",
				dwt.DeathWomanType_Name as \"DeathWomanType_Name\",
				ddt.DtpDeathTime_Code as \"DtpDeathTime_Code\",
				ddt.DtpDeathTime_Name as \"DtpDeathTime_Name\",
				od.Diag_Code as \"Diag_oCode\",
				od.Diag_Name as \"Diag_oName\",
				ed.Diag_Code as \"Diag_eCode\",
				ed.Diag_Name as \"Diag_eName\",
				td.Diag_Code as \"Diag_tCode\",
				td.Diag_Name as \"Diag_tName\",
				id.Diag_Code as \"Diag_iCode\",
				id.Diag_Name as \"Diag_iName\",
				md.Diag_Code as \"Diag_mCode\",
				md.Diag_Name as \"Diag_mName\",
				ds.DeathSvid_PribPeriod as \"DeathSvid_PribPeriod\",
				ds.DeathSvid_PribPeriodPat as \"DeathSvid_PribPeriodPat\",
				ds.DeathSvid_PribPeriodDom as \"DeathSvid_PribPeriodDom\",
				ds.DeathSvid_PribPeriodExt as \"DeathSvid_PribPeriodExt\",
				ds.DeathSvid_PribPeriodImp as \"DeathSvid_PribPeriodImp\",
				ds.DeathSvid_TimePeriod as \"DeathSvid_TimePeriod\",
				ds.DeathSvid_TimePeriodPat as \"DeathSvid_TimePeriodPat\",
				ds.DeathSvid_TimePeriodDom as \"DeathSvid_TimePeriodDom\",
				ds.DeathSvid_TimePeriodExt as \"DeathSvid_TimePeriodExt\",
				ds.DeathSvid_TimePeriodImp as \"DeathSvid_TimePeriodImp\",
				to_char(ds.DeathSvid_TraumaDate, 'YYYYMMDD') as \"DeathSvid_TraumaDate\",
				to_char(ds.DeathSvid_DeathDate, 'YYYYMMDD') as \"DeathSvid_DeathDate\",
				to_char(ds.DeathSvid_DeathDate, 'HH24:MI') as \"DeathSvid_DeathTime\",
				to_char(ds.DeathSvid_OldGiveDate, 'YYYYMMDD') as \"DeathSvid_OldGiveDate\",
				ds.DeathSvid_ChildCount as \"DeathSvid_ChildCount\",
				ds.DeathSvid_OldSer as \"DeathSvid_OldSer\",
				ds.DeathSvid_OldNum as \"DeathSvid_OldNum\",
				ctt.ChildTermType_Code as \"ChildTermType_Code\",
				ctt.ChildTermType_Name as \"ChildTermType_Name\",
				dsc.DeathSetCause_Code as \"DeathSetCause_Code\",
				dsc.DeathSetCause_Name as \"DeathSetCause_Name\",
				dst.DeathSetType_Code as \"DeathSetType_Code\",
				dst.DeathSetType_Name as \"DeathSetType_Name\",
				dc.DeathCause_Code as \"DeathCause_Code\",
				dc.DeathCause_Name as \"DeathCause_Name\",
				dfs.DeathFamilyStatus_Code as \"DeathFamilyStatus_Code\",
				dfs.DeathFamilyStatus_Name as \"DeathFamilyStatus_Name\",
				dsty.DeathSvidType_Code as \"DeathSvidType_Code\",
				dsty.DeathSvidType_Name as \"DeathSvidType_Name\"
			from
				dbo.v_DeathSvid ds
				left join v_MedStaffFact msf on msf.MedStaffFact_id = ds.MedStaffFact_id
				left join persis.Post p on p.id = msf.Post_id
				left join nsi.v_MedPost mp on mp.MedPost_id = p.MedPost_id
				left join v_OrgHead oh on oh.Person_id = ds.Person_hid and oh.OrgHeadPost_id = ds.OrgHeadPost_id
				LEFT JOIN LATERAL (
					select 
						MedStaffFact_id,
						msfh.MedPersonal_id,
						msfh.Person_SurName,
						msfh.Person_FirName,
						msfh.Person_SecName,
						msfh.Post_id,
						msfh.Person_id,
						mph.MedPost_Code,
						mph.MedPost_Name
					from
						v_MedStaffFact msfh
						inner join persis.Post ph on ph.id = msfh.Post_id
						inner join nsi.v_MedPost mph on mph.MedPost_id = ph.MedPost_id
					where
						msfh.Person_id = oh.Person_id
						and mph.MedPost_pid = 3
					limit 1
				) msfh ON true
				left join fed.v_PassportToken LpuOID on LpuOID.Lpu_id = ds.Lpu_id
				left join v_PersonState ps on ps.Person_id = ds.Person_id
				left join v_PersonState psr on psr.Person_id = ds.Person_rid
				left join v_PersonState psm on psm.Person_id = msf.Person_id
				left join v_PersonState psmh on psmh.Person_id = msfh.Person_id
				left join v_Document d on d.Document_id = ps.Document_id
				left join v_DocumentType dt on dt.DocumentType_id = d.DocumentType_id
				left join v_Org o on o.Org_id = d.OrgDep_id
				left join v_Sex s on s.Sex_id = ps.Sex_id
				left join v_Address_all ua on ua.Address_id = ps.UAddress_id
				left join v_PersonInfo VPI on VPI.Person_id = PS.Person_id
				left join v_Lpu l on l.Lpu_id = ds.Lpu_id
				left join v_Org ol on ol.Org_id = l.Org_id
				left join v_Address_all lua on lua.Address_id = l.UAddress_id
				left join v_DeathPlace bp on bp.DeathPlace_id = ds.DeathPlace_id
				left join v_DeathEmployment be on be.DeathEmployment_id = ds.DeathEmployment_id
				left join v_DeathEducation bed on bed.DeathEducation_id = ds.DeathEducation_id
				left join v_FamilyStatus fs on fs.FamilyStatus_id = ds.FamilyStatus_id
				left join v_KLAreaType klat on klat.KLAreaType_id = ua.KLAreaType_id
				left join v_KLSubRgn uasr on uasr.KLSubRgn_id = ua.KLSubRgn_id
				left join v_KLTown uat on uat.KLTown_id = ua.KLTown_id
				left join v_KLCity uac on uac.KLCity_id = ua.KLCity_id
				left join v_KLStreet uas on uas.KLStreet_id = ua.KLStreet_id
				left join v_Address_all mua on mua.Address_id = psm.UAddress_id
				left join v_Address_all mhua on mhua.Address_id = psmh.UAddress_id
				left join v_DeathWomanType dwt on dwt.DeathWomanType_id = ds.DeathWomanType_id
				left join v_DtpDeathTime ddt on ddt.DtpDeathTime_id = ds.DtpDeathTime_id
				left join v_DeathSetCause dsc on dsc.DeathSetCause_id = ds.DeathSetCause_id
				left join v_ChildTermType ctt on ctt.ChildTermType_id = ds.ChildTermType_id
				left join v_DeathSetType dst on dst.DeathSetType_id = ds.DeathSetType_id
				left join v_DeathCause dc on dc.DeathCause_id = ds.DeathCause_id
				left join v_DeathFamilyStatus dfs on dfs.DeathFamilyStatus_id = ds.DeathFamilyStatus_id
				left join v_DeathSvidType dsty on dsty.DeathSvidType_id = ds.DeathSvidType_id
				left join v_Address_all da on da.Address_id = ds.Address_did
				left join v_KLAreaType dklat on dklat.KLAreaType_id = da.KLAreaType_id
				left join v_KLSubRgn dasr on dasr.KLSubRgn_id = da.KLSubRgn_id
				left join v_KLTown dat on dat.KLTown_id = da.KLTown_id
				left join v_KLCity dac on dac.KLCity_id = da.KLCity_id
				left join v_KLStreet das on das.KLStreet_id = da.KLStreet_id
				left join v_Diag od on od.Diag_id = ds.Diag_oid
				left join v_Diag ed on ed.Diag_id = ds.Diag_eid
				left join v_Diag td on td.Diag_id = ds.Diag_tid
				left join v_Diag id on id.Diag_id = ds.Diag_iid
				left join v_Diag md on md.Diag_id = ds.Diag_mid
			where
				ds.DeathSvid_id = :DeathSvid_id
			limit 1
		", [
			'DeathSvid_id' => $data['DeathSvid_id']
		]);

		if (empty($resp[0]['DeathSvid_id'])) {
			throw new Exception('Ошибка получения данных по направлению на МСЭ', 500);
		}

		$resp[0]['assignedTime'] = date('Y-m-d');
		$resp[0]['isAssigned'] = 'S';

		$resp[0]['DeathSvid_GiveDateFormatted'] = !empty($resp[0]['DeathSvid_GiveDate']) ? date('d.m.Y', strtotime($resp[0]['DeathSvid_GiveDate'])) : '';
		$resp[0]['DeathSvid_TraumaDateFormatted'] = !empty($resp[0]['DeathSvid_TraumaDate']) ? date('d.m.Y', strtotime($resp[0]['DeathSvid_TraumaDate'])) : '';
		$resp[0]['DeathSvid_OldGiveDateFormatted'] = !empty($resp[0]['DeathSvid_OldGiveDate']) ? date('d.m.Y', strtotime($resp[0]['DeathSvid_OldGiveDate'])) : '';
		$resp[0]['Person_BirthDayFormatted'] = !empty($resp[0]['Person_BirthDay']) ? date('d.m.Y', strtotime($resp[0]['Person_BirthDay'])) : '';
		$resp[0]['DeathSvid_DeathDateFormatted'] = !empty($resp[0]['DeathSvid_DeathDate']) ? date('d.m.Y', strtotime($resp[0]['DeathSvid_DeathDate'])) : '';

		if (empty($resp[0]['DeathSvid_TimePeriod']) && !empty($resp[0]['DeathSvid_PribPeriod'])) {
			$resp[0]['DeathSvid_TimePeriod'] = $resp[0]['DeathSvid_PribPeriod'];
		}
		if (empty($resp[0]['DeathSvid_TimePeriodPat']) && !empty($resp[0]['DeathSvid_PribPeriodPat'])) {
			$resp[0]['DeathSvid_TimePeriodPat'] = $resp[0]['DeathSvid_PribPeriodPat'];
		}
		if (empty($resp[0]['DeathSvid_TimePeriodDom']) && !empty($resp[0]['DeathSvid_PribPeriodDom'])) {
			$resp[0]['DeathSvid_TimePeriodDom'] = $resp[0]['DeathSvid_PribPeriodDom'];
		}
		if (empty($resp[0]['DeathSvid_TimePeriodExt']) && !empty($resp[0]['DeathSvid_PribPeriodExt'])) {
			$resp[0]['DeathSvid_TimePeriodExt'] = $resp[0]['DeathSvid_PribPeriodExt'];
		}
		if (empty($resp[0]['DeathSvid_TimePeriodImp']) && !empty($resp[0]['DeathSvid_PribPeriodImp'])) {
			$resp[0]['DeathSvid_TimePeriodImp'] = $resp[0]['DeathSvid_PribPeriodImp'];
		}

		if (!empty($resp[0]['Diag_oName'])) {
			$resp[0]['Diag_oName'] = str_replace('"', '\'', $resp[0]['Diag_oName']);
		}
		if (!empty($resp[0]['Diag_eName'])) {
			$resp[0]['Diag_eName'] = str_replace('"', '\'', $resp[0]['Diag_eName']);
		}
		if (!empty($resp[0]['Diag_tName'])) {
			$resp[0]['Diag_tName'] = str_replace('"', '\'', $resp[0]['Diag_tName']);
		}
		if (!empty($resp[0]['Diag_iName'])) {
			$resp[0]['Diag_iName'] = str_replace('"', '\'', $resp[0]['Diag_iName']);
		}
		if (!empty($resp[0]['Diag_mName'])) {
			$resp[0]['Diag_mName'] = str_replace('"', '\'', $resp[0]['Diag_mName']);
		}

		if (!empty($resp[0]['DeathSvidRelation_id'])) {
			$resp[0]['DeathSvidRelation_FirstCode'] = ($resp[0]['DeathSvidRelation_id'] == '1') ? 'PRS' : 'AGNT';
		}
		$this->load->library('parser');

		$xml = '<?xml version="1.0" encoding="UTF-8"?>';
		$xml .= '<?xml-stylesheet type="text/xsl" href="/documents/xsl/MDC.xsl"?>';
		$xml .= $this->parser->parse('print_deathsvid_hl7', $resp[0], true);

		return array('xml' => $xml);
	}

    /**
     *	Функция для проверки на диагноз "R54"
     */
    function checkR54diagnose($data) {
        $error = false;
        //Если  в одном из полей причин смерти: «Непосредственная причина»; «Патологическое состояние»; «Первоначальная причина смерти»; «Внешняя причина смерти»; «Прочие важные состояния»  - значение диагноза «R54 Старость»
        $onDate = $data['DeathSvid_GiveDate'];
        if (!empty($data['DeathSvid_DeathDate_Date'])) {
            $onDate = $data['DeathSvid_DeathDate_Date'];
        }

        $resp_ps = $this->queryResult("
                    SELECT
                        ps.Person_id as \"Person_id\",
                        pd.PersonDisp_id \"PersonDisp_id\",
                        evpl.EvnVizitPL_id \"EvnVizitPL_id\",
                        eps.EvnPS_id \"EvnPS_id\"
                    FROM
                        v_PersonState ps
                        LEFT JOIN LATERAL (
                            SELECT
                                pd.PersonDisp_id
                            FROM
                                v_PersonDisp pd
                            WHERE
                                pd.Person_id = ps.Person_id
                                AND coalesce(pd.PersonDisp_begDate, :onDate) <= :onDate
                                AND coalesce(pd.PersonDisp_endDate, :onDate) >= :onDate
                            LIMIT 1
                        ) pd ON true
                        LEFT JOIN LATERAL (
                            SELECT
                                evpl.EvnVizitPL_id
                            FROM
                                v_EvnVizitPL evpl
                                INNER JOIN v_VizitType vt ON vt.VizitType_id = evpl.VizitType_id AND vt.VizitType_SysNick = 'desease' -- Заболевание
                            WHERE
                                evpl.Person_id = ps.Person_id
                                AND extract(day, :onDate - evpl.EvnVizitPL_setDT) <= 365
                            LIMIT 1
                        ) evpl ON true
                        LEFT JOIN LATERAL (
                            SELECT
                                eps.EvnPS_id
                            FROM
                                v_EvnPS eps
                            WHERE
                                eps.Person_id = ps.Person_id
                                AND extract(day, :onDate - eps.EvnPS_setDT) <= 365
                            LIMIT 1
                        ) eps ON true
                    WHERE
                        ps.Person_id = :Person_id
                        and coalesce(ps.Person_IsUnknown, 1) <> 2
                    LIMIT 1
                ", array(
            'Person_id' => $data['Person_id'],
            'onDate' => $onDate
        ));

        // Личность человека известна и у Человека открыта карта диспансерного наблюдения или в течении 365 дней с даты смерти либо с даты выписки мед. свидетельства (если дата смерти неизвестна) найден случай лечения КВС или ТАП с видом обращения = «1 Заболевание»
        if (!empty($resp_ps[0]['Person_id']) && (!empty($resp_ps[0]['EvnVizitPL_id']) || !empty($resp_ps[0]['EvnPS_id']) || !empty($resp_ps[0]['PersonDisp_id']))) {
            $error = true;
        }

        if (!$error) {
            $resp = array('success' => true);
        } else {
            $resp = array('success' => false);
        }
        return $resp;
    }
	function getDeathSvidData($data) {
		$query = "select
				DS.Person_id as \"Person_id\",
				DS.DeathSvid_DeathDate as \"DeathSvid_DeathDate\",
				DS.DeathSvid_GiveDate as \"DeathSvid_GiveDate\",
				DS.DeathSvid_IsPrimDiagIID as \"DeathSvid_IsPrimDiagIID\",
				DS.DeathSvid_IsPrimDiagTID as \"DeathSvid_IsPrimDiagTID\",
				DS.DeathSvid_IsPrimDiagMID as \"DeathSvid_IsPrimDiagMID\",
				DS.DeathSvid_IsPrimDiagEID as \"DeathSvid_IsPrimDiagEID\",
				DS.Diag_iid as \"Diag_iid\",
				DS.Diag_tid as \"Diag_tid\",
				DS.Diag_mid as \"Diag_mid\",
				DS.Diag_eid as \"Diag_eid\"
				from v_DeathSvid DS
				where DS.DeathSvid_id = :svid_id
				limit 1";
		return $this->getFirstRowFromQuery($query, $data);
	}
}

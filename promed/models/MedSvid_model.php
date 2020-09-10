<?php defined('BASEPATH') or die ('No direct script access allowed');
class MedSvid_model extends swModel {

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
			array('field' => 'Diag_iid','label' => 'Непосредственная причина смерти','rules' => '','type' => 'id'),
			array('field' => 'Diag_tid','label' => 'Патологическое состояние','rules' => '','type' => 'id'),
			array('field' => 'Diag_mid','label' => 'Основная причина смерти','rules' => '','type' => 'id'),
			array('field' => 'Diag_eid','label' => 'Внешние причины','rules' => '','type' => 'id'),
			array('field' => 'Diag_oid','label' => 'Прочие важные состояния','rules' => '','type' => 'id'),
			array('field' => 'DeathSvid_Oper','label' => 'Причины, не связанные с болезнью, а также операции','rules' => '','type' => 'string'),
			array('field' => 'Person_rid','label' => 'ФИО получателя','rules' => '','type' => 'id'),
			array('field' => 'DeathSvid_PolFio','label' => 'ФИО получателя (ручной ввод)','rules' => '','type' => 'string'),
			array('field' => 'DeathSvid_RcpDocument','label' => 'Документ','rules' => '','type' => 'string'),
			array('field' => 'DeathSvid_RcpDate','label' => 'Дата получения','rules' => '','type' => 'date'),
			array('field' => 'DeathSvid_IsPrimDiagEID','label' => 'Признак первоначальной причины смерти для: Внешней причины','rules' => '','type' => 'api_flag'),
			array('field' => 'DeathSvid_IsPrimDiagIID','label' => 'Признак первоначальной причины смерти для: Непосредственной причины','rules' => '','type' => 'api_flag'),
			array('field' => 'DeathSvid_IsPrimDiagMID','label' => 'Признак первоначальной причины смерти для: Первоначальной причины смерти','rules' => '','type' => 'api_flag'),
			array('field' => 'DeathSvid_IsPrimDiagTID','label' => 'Признак первоначальной причины смерти для: Паталогического состояния','rules' => '','type' => 'api_flag'),
			array('field' => 'DeathSvid_IsActual','label' => 'Признак действительности свидетельства о смерти','rules' => '','type' => 'int')
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
			array('field' => 'DeathSvid_RcpDate','label' => 'Дата получения','rules' => '','type' => 'date'),
			array('field' => 'DeathSvid_IsPrimDiagEID','label' => 'Признак первоначальной причины смерти для: Внешней причины','rules' => '','type' => 'api_flag'),
			array('field' => 'DeathSvid_IsPrimDiagIID','label' => 'Признак первоначальной причины смерти для: Непосредственной причины','rules' => '','type' => 'api_flag'),
			array('field' => 'DeathSvid_IsPrimDiagMID','label' => 'Признак первоначальной причины смерти для: Первоначальной причины смерти','rules' => '','type' => 'api_flag'),
			array('field' => 'DeathSvid_IsPrimDiagTID','label' => 'Признак первоначальной причины смерти для: Паталогического состояния','rules' => '','type' => 'api_flag'),
			array('field' => 'DeathSvid_IsActual','label' => 'Признак действительности свидетельства о смерти','rules' => '','type' => 'int')
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
			array('field' => 'LpuSection_id','label' => 'Отделение','rules' => '','type' => 'id'),
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
			select top 100
				ds.*,
				dst.DeathSvidType_Name
			from
				v_DeathSvid ds with (NOLOCK)
				left join v_DeathSvidType dst with (nolock) on dst.DeathSvidType_id = ds.DeathSvidType_id
			where 
				{$filters}
			{$orderby}
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
			if (isset($data['Person_Surname']) && strlen($data['Person_Surname']) > 0) $where .= " AND p.Person_Surname LIKE '".rtrim($data['Person_Surname'])."%'";
			if (isset($data['Person_Firname']) && strlen($data['Person_Firname']) > 0) $where .= " AND p.Person_Firname LIKE '".rtrim($data['Person_Firname'])."%'";
			if (isset($data['Person_Secname']) && strlen($data['Person_Secname']) > 0) $where .= " AND p.Person_Secname LIKE '".rtrim($data['Person_Secname'])."%'";

            if (isset($data['Child_BirthDate'][0]) && strlen($data['Child_BirthDate'][0]) > 0) $where .= " and cast(d.BirthSvid_BirthDT as date) >= cast('".$data['Child_BirthDate'][0]."' as date)";
            if (isset($data['Child_BirthDate'][1]) && strlen($data['Child_BirthDate'][1]) > 0) $where .= " and cast(d.BirthSvid_BirthDT as date)<= cast('".$data['Child_BirthDate'][1]."' as date)";
            if (isset($data['Child_Surname']) && strlen($data['Child_Surname']) > 0) $where .= " AND d.BirthSvid_ChildFamil LIKE '".rtrim($data['Child_Surname'])."%'";
            if (isset($data['Sex_id']) && strlen($data['Sex_id']) > 0) $where .= " AND d.Sex_id = ".$data['Sex_id'];

			if (getRegionNick() == 'kz') {
				$fields .= ",case when OSL.Object_sid is null then 0 else 1 end as BirthSvid_isInRpn";
				$join .= " outer apply(
						select top 1 Object_sid
						from v_ObjectSynchronLog OSL with(nolock)
						where ObjectSynchronLogService_id = 2 and OSL.Object_id = d.BirthSvid_id
				    ) OSL";
			} else {
				$fields .= ",0 as BirthSvid_isInRpn";
			}

            //var_dump($where);die;
			$query = "
				select
					-- select
					d.BirthSvid_id,
					d.BirthSvid_isBad,
					d.BirthSvid_Ser,
					d.BirthSvid_Num,
					CONVERT(varchar(10), d.BirthSvid_RcpDate, 104) as BirthSvid_RcpDate,
					isnull(rtrim(p.Person_Surname)+' ','') + isnull(rtrim(p.Person_Firname)+' ','') + isnull(rtrim(p.Person_Secname),'') as Person_FIO,
					CONVERT(varchar(10), p.Person_Birthday, 104) as Person_Birthday,
					ISNULL(d.BirthSvid_ChildFamil,'') as BirthSvid_ChildFamil,
					CONVERT(varchar(10), d.BirthSvid_BirthDT, 104) as BirthSvid_BirthChildDate,
					CS.Sex_Name as Child_Sex,
					L.Lpu_Nick,
					convert(varchar(10), d.BirthSvid_signDT, 104) as BirthSvid_signDT,
					d.BirthSvid_IsSigned,
					isnull(rtrim(MP.Person_Surname)+' ','') + isnull(rtrim(MP.Person_Firname)+' ','') + isnull(rtrim(MP.Person_Secname),'') as MedPersonal_FIO
					{$fields}
					-- end select
				from
					-- from
					BirthSvid d with (NOLOCK)
					left join v_Person_ER p with (NOLOCK) on p.Person_id = d.Person_Id
					left join v_Lpu L with (NOLOCK) on L.Lpu_id = d.Lpu_id
					left join v_Sex CS with (nolock) on CS.Sex_id = d.Sex_id
					outer apply (
						select top 1 Person_Surname, Person_Firname, Person_Secname
						from v_MedPersonal with (nolock)
						where MedPersonal_id = d.MedPersonal_id
							and Lpu_id = d.Lpu_id
					) MP
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
				select top 1
					LT.LpuType_Code
				from
					v_Lpu L with (nolock)
					left join v_LpuType LT with (nolock) on LT.LpuType_id = L.LpuType_id
				where
					L.Lpu_id = :Lpu_id", array('Lpu_id' => $data['Lpu_id'])
			);
		}

		if (in_array($svid_type, array('death', /*'pntdeath'*/))) {

			if (!empty($data['DeathCause']) && in_array($data['DeathCause'], array('Diag_iid','Diag_tid','Diag_mid','Diag_eid','Diag_oid',))) {
				$join .= " left join v_Diag Dg with (nolock) on d.{$data['DeathCause']} = Dg.Diag_id ";

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
					$join .= " left join v_Diag doid (nolock) on doid.Diag_id = d.Diag_oid ";
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
				$where .= " AND ISNULL(d.DeathSvid_IsActual, 2) = :IsActual";
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
				$where .= " AND p.Person_Surname LIKE :Person_Surname + '%'";
				$queryParams['Person_Surname'] = $data['Person_Surname'];
			}

			if (!empty($data['Person_Firname'])) {
				$where .= " AND p.Person_Firname LIKE :Person_Firname + '%'";
				$queryParams['Person_Firname'] = $data['Person_Firname'];
			}

			if (!empty($data['Person_Secname'])) {
				$where .= " AND p.Person_Secname LIKE :Person_Secname + '%'";
				$queryParams['Person_Secname'] = $data['Person_Secname'];
			}

			if (!empty($data['Svid_Num'])) {
				$where .= " AND d.DeathSvid_Num LIKE :Svid_Num + '%'";
				$queryParams['Svid_Num'] = $data['Svid_Num'];
			}
			
			if (!empty($data['ReceptType_id'])) {
				$where .= " AND d.ReceptType_id = :ReceptType_id";
				$queryParams['ReceptType_id'] = $data['ReceptType_id'];
			}

			if (!empty($data['Lpu_id'])) {
				$lpuJoin = " left join v_Lpu L with (nolock) on L.Lpu_id = d.Lpu_id ";
				if (!empty($data['viewMode']) && $data['viewMode'] == 2) {
					// режим по прикреплённому населению
					$filterlr = "";
					if (!empty($data['LpuRegion_id'])) {
						$filterlr .= " and ISNULL(LpuRegion_id,-1) = :LpuRegion_id";
						$queryParams['LpuRegion_id'] = $data['LpuRegion_id'];
					}
					if (isset($data['MedPersonal_id']) && ($data['MedPersonal_id'] > 0)) {

						$queryParams['MedPersonal_id'] = $data['MedPersonal_id'];

						$join .= "
						cross apply(
							select top 1
								PersonCard_id
							from
								v_PersonCard_all PC (nolock)
								inner join v_MedStaffRegion MedStaffRegion with (NOLOCK) on MedStaffRegion.LpuRegion_id = PC.LpuRegion_id and MedStaffRegion.MedPersonal_id = :MedPersonal_id
								left join v_MedStaffFact msf on msf.MedStaffFact_id = MedStaffRegion.MedStaffFact_id and MedStaffRegion.Lpu_id = msf.Lpu_id
								left join persis.Post p with (nolock) on p.id = msf.Post_id
							where
								PC.Person_id = d.Person_id
								and PC.LpuAttachType_id = 1
								and MedStaffRegion.MedStaffRegion_isMain = 2 -- основной врач на участке
								and p.code in (74,47,40,117,111)
								and PersonCard_begDate <= ISNULL(d.DeathSvid_DeathDate, d.DeathSvid_GiveDate)
								and (CAST(PC.PersonCard_endDate AS DATE) >= CAST(ISNULL(d.DeathSvid_DeathDate, d.DeathSvid_GiveDate) AS date) OR PersonCard_endDate IS NULL)
								and PC.Lpu_id = :Lpu_id
								{$filterlr}
						) pcs
						-- left join v_PersonCardState pcs with (nolock) on pcs.Person_id = d.Person_id and pcs.LpuAttachType_id = 1
					";
					} else {
						$join .= "
						cross apply(
							select top 1
								PersonCard_id
							from
								v_PersonCard_all  (nolock)
							where
								Person_id = d.Person_id
								and LpuAttachType_id = 1
								and PersonCard_begDate <= ISNULL(d.DeathSvid_DeathDate, d.DeathSvid_GiveDate)
								and (CAST(PersonCard_endDate AS DATE) >= CAST(ISNULL(d.DeathSvid_DeathDate, d.DeathSvid_GiveDate) AS date) OR PersonCard_endDate IS NULL)
								and Lpu_id = :Lpu_id
								{$filterlr}
						) pcs
						-- left join v_PersonCardState pcs with (nolock) on pcs.Person_id = d.Person_id and pcs.LpuAttachType_id = 1
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
				$lpuJoin = " inner join v_Lpu L with (nolock) on L.Lpu_id = d.Lpu_id ";
			}



			$query = "
				select
					-- select
					d.DeathSvid_id,
					d.DeathSvid_IsBad,
					ISNULL(d.DeathSvid_IsActual, 2) as DeathSvid_IsActual,
					d.DeathSvid_IsLose,
					d.DeathSvid_Ser,
					d.DeathSvid_Num,
					CONVERT(varchar(10), d.DeathSvid_GiveDate, 104) as DeathSvid_GiveDate,
					isnull(rtrim(p.Person_Surname)+' ','') + isnull(rtrim(p.Person_Firname)+' ','') + isnull(rtrim(p.Person_Secname),'') as Person_FIO,
					ISNULL(CONVERT(varchar(10), p.Person_Birthday, 104), d.DeathSvid_BirthDateStr) as Person_Birthday,
					dbo.Age2(p.Person_BirthDay, dbo.tzGetDate()) as Person_Age,
					isnull(CONVERT(varchar(10), d.DeathSvid_DeathDate, 104), d.DeathSvid_DeathDateStr) as DeathSvid_DeathDate,
					L.Lpu_Nick,
					convert(varchar(10), d.DeathSvid_signDT, 104) as DeathSvid_signDT,
					d.DeathSvid_IsSigned,
					LT.LpuType_Code,
					d.Lpu_id,
					isnull(rtrim(MP.Person_Surname)+' ','') + isnull(rtrim(MP.Person_Firname)+' ','') + isnull(rtrim(MP.Person_Secname),'') as MedPersonal_FIO,
					dst.DeathSvidType_Name,
					d.DeathSvidType_id,
					d.Person_rid,
					d.Person_id,
					d.DeathSvid_IsDuplicate,
					ISNULL(diid.Diag_Code + '. ','') + diid.Diag_Name as Diag_iidName,
					ISNULL(dmid.Diag_Code + '. ','') + dmid.Diag_Name as Diag_midName,
                    ISNULL(deid.Diag_Code + '. ','') + deid.Diag_Name as Diag_eidName,
					ISNULL(dtid.Diag_Code + '. ','') + dtid.Diag_Name as Diag_tidName,
					case when PI.PersonInfo_IsParsDeath = 2 then 1 else 0 end as PersonInfo_IsParsDeath,
					case when PI.PersonInfo_IsSetDeath = 2 then 1 else 0 end as PersonInfo_IsSetDeath,
					d.ReceptType_id,
					(case when psr.Person_SurName			is null then ' Фамилия получателя,'		ELSE '' end +
					 case when psr.Person_FirName			is null then ' Имя получателя,'			ELSE '' end +
					 case when dtfrmis.DocumentTypeFRMIS_id	is null then ' Тип документа,'			ELSE '' end +
					 case when psr.Document_Ser				is null then ' Серия документа,'		ELSE '' end +
					 case when psr.Document_Num				is null then ' Номер документа,'		ELSE '' end +
					 case when doc.Document_begDate			is null then ' Дата документа,'			ELSE '' end +
					 case when od.Org_id					is null then ' Кем выдан,'				ELSE '' end +
					 case when d.DeathSvid_GiveDate			is null then ' Дата получения МСС,'		ELSE '' end +
					 case when d.DeathSvidRelation_id		is null then ' Отношение к умершему,'	ELSE '' end )
					as MissingDataList
					-- end select
				from
					-- from
					v_DeathSvid d with (nolock)
					left join v_Diag diid (nolock) on diid.Diag_id = d.Diag_iid
					left join v_Diag dmid (nolock) on dmid.Diag_id = d.Diag_mid
					left join v_Diag deid (nolock) on deid.Diag_id = d.Diag_eid
					left join v_Diag dtid (nolock) on dtid.Diag_id = d.Diag_tid
					left join v_DeathSvidType dst with (nolock) on dst.DeathSvidType_id = d.DeathSvidType_id
					outer apply (
						select top 1
							p.Person_Secname,
							p.Person_Surname,
							p.Person_Firname,
							p.Person_Birthday
						from v_Person_ER p with (nolock)
						where p.Person_id = d.Person_Id
					)p
					{$lpuJoin}
					left join v_LpuType LT with (nolock) on L.LpuType_id = LT.LpuType_id
					outer apply( 
						select top 1
							PInf.PersonInfo_IsParsDeath,
							PInf.PersonInfo_IsSetDeath
						from v_PersonInfo PInf with (nolock)
						where PInf.Person_Id = d.Person_Id
					) PI
					outer apply (
						select top 1
							Person_Surname,
							Person_Firname,
							Person_Secname
						from
							v_MedPersonal with (nolock)
						where
							MedPersonal_id = d.MedPersonal_id
							and Lpu_id = d.Lpu_id
					) MP
					left join v_PersonState psr with (nolock) on psr.Person_id = d.Person_rid
					left join v_Document doc with (nolock) on doc.Document_id = psr.Document_id
					left join v_DocumentType dt with (NOLOCK) on dt.DocumentType_id = doc.DocumentType_id
					left join v_DocumentTypeFRMIS dtfrmis with (NOLOCK) on dt.DocumentTypeFRMIS_id = dtfrmis.DocumentTypeFRMIS_id
					left join v_OrgDep od with (nolock) on doc.OrgDep_id = od.OrgDep_id
					{$join}
					-- end from
				where 
					-- where
					(1=1) ".$where." 
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
				$where .= " AND ISNULL(d.PntDeathSvid_IsActual, 2) = :IsActual";
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
				$where .= " AND p.Person_Surname LIKE :Person_Surname + '%'";
				$queryParams['Person_Surname'] = $data['Person_Surname'];
			}

			if (!empty($data['Person_Firname'])) {
				$where .= " AND p.Person_Firname LIKE :Person_Firname + '%'";
				$queryParams['Person_Firname'] = $data['Person_Firname'];
			}

			if (!empty($data['Person_Secname'])) {
				$where .= " AND p.Person_Secname LIKE :Person_Secname + '%'";
				$queryParams['Person_Secname'] = $data['Person_Secname'];
			}

			if (!empty($data['Sex_id'])) {
				$where .= " AND d.Sex_id = :Sex_id";
				$queryParams['Sex_id'] = $data['Sex_id'];
			}

			if (!empty($data['Child_Surname'])) {
				$where .= " AND d.PntDeathSvid_ChildFio LIKE :Child_Surname + '%'";
				$queryParams['Child_Surname'] = $data['Child_Surname'];
			}

			if (!empty($data['Svid_Num'])) {
				$where .= " AND d.PntDeathSvid_Num LIKE :Svid_Num + '%'";
				$queryParams['Svid_Num'] = $data['Svid_Num'];
			}

			if (!empty($data['Lpu_id'])) {
				if (!empty($data['viewMode']) && $data['viewMode'] == 2) {
					// режим по прикреплённому населению
					$join .= " left join v_PersonCardState pcs with (nolock) on pcs.Person_id = d.Person_id and pcs.LpuAttachType_id = 1
						left join v_LpuType LT with (nolock) on L.LpuType_id = LT.LpuType_id";
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
					d.PntDeathSvid_id,
					d.PntDeathSvid_IsBad,
					ISNULL(d.PntDeathSvid_IsActual, 2) as PntDeathSvid_IsActual,
					d.PntDeathSvid_IsLose,
					d.PntDeathSvid_Ser,
					d.PntDeathSvid_Num,
					CONVERT(varchar(10), d.PntDeathSvid_GiveDate, 104) as PntDeathSvid_GiveDate,
					ISNULL(CONVERT(varchar(10), d.PntDeathSvid_DeathDate, 104), d.PntDeathSvid_DeathDateStr) as PntDeathSvid_DeathDate,
					ISNULL(CONVERT(varchar(10), d.PntDeathSvid_ChildBirthDT, 104), d.PntDeathSvid_BirthDateStr) as PntDeathSvid_BirthDate,
					isnull(d.PntDeathSvid_ChildFio,'') as PntDeathSvid_ChildFio,
					CS.Sex_Name as Child_Sex,
					isnull(rtrim(p.Person_Surname)+' ','') + isnull(rtrim(p.Person_Firname)+' ','') + isnull(rtrim(p.Person_Secname),'') as Person_FIO,
					CONVERT(varchar(10), p.Person_Birthday, 104) as Person_Birthday,
					L.Lpu_Nick,
					d.Lpu_id,
					isnull(rtrim(MP.Person_Surname)+' ','') + isnull(rtrim(MP.Person_Firname)+' ','') + isnull(rtrim(MP.Person_Secname),'') as MedPersonal_FIO,
					dst.DeathSvidType_Name,
					d.DeathSvidType_id,
					d.Person_rid,
					d.Person_id,
					d.PntDeathSvid_IsDuplicate,
					case when PI.PersonInfo_IsParsDeath = 2 then 1 else 0 end as PersonInfo_IsParsDeath,
					case when PI.PersonInfo_IsSetDeath = 2 then 1 else 0 end as PersonInfo_IsSetDeath
					-- end select
				from
					-- from
					v_PntDeathSvid d with (nolock)
					left join v_DeathSvidType dst with (nolock) on dst.DeathSvidType_id = d.DeathSvidType_id
					left join v_Person_ER p with (nolock) on p.Person_id = d.Person_Id
					left join v_Lpu L with (nolock) on L.Lpu_id = d.Lpu_id
					left join v_Sex CS with (nolock) on CS.Sex_id = d.Sex_id
					left join v_PersonInfo PI with (nolock) on PI.Person_Id = d.Person_Id
					outer apply (
						select top 1
							Person_Surname,
							Person_Firname,
							Person_Secname
						from
							v_MedPersonal with (nolock)
						where
							MedPersonal_id = d.MedPersonal_id
							and Lpu_id = d.Lpu_id
					) MP
					{$join}
					-- end from
				where 
					-- where
					(1=1) ".$where." 
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
			switch($svid_type) {
				case 'birth':
					$tableName = 'BirthSvid';
					break;
				case 'death':
					$tableName = 'DeathSvid';
					break;
			}
			$EMDRegistry_ObjectIDs = [];
			foreach($response['data'] as $one) {
				if (!empty($one[$tableName . '_id']) && $one[$tableName . '_IsSigned'] == 2 && !in_array($one[$tableName . '_id'], $EMDRegistry_ObjectIDs)) {
					$EMDRegistry_ObjectIDs[] = $one[$tableName.'_id'];
				}
			}

			$isEMDEnabled = $this->config->item('EMD_ENABLE');
			if(!empty($EMDRegistry_ObjectIDs) && !empty($isEMDEnabled)) {
				$this->load->model('EMD_model');
				$MedStaffFact_id = $data['session']['CurMedStaffFact_id'] ?? null;
				if (
					empty($MedStaffFact_id)
					&& !empty($data['session']['CurMedService_id'])
					&& !empty($data['session']['medpersonal_id'])
				) {
					// получаем данные по мед. работнику службы
					$resp_ms = $this->queryResult("
						select top 1
							msf.MedStaffFact_id
						from v_MedService ms (nolock)
						inner join v_MedStaffFact msf (nolock) on msf.LpuSection_id = ms.LpuSection_id
						where
							ms.MedService_id = :MedService_id
							and msf.MedPersonal_id = :MedPersonal_id
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
            $and .= " and LpuLicence_begDate <= :svidDate and isnull(LpuLicence_endDate, '2099-01-01')>= :svidDate";
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
                select *
                from v_LpuLicence with (nolock)
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
				$querypp = "UPDATE PersonPrivilege with (rowlock) SET PersonPrivilege_endDate = :endDate WHERE PersonPrivilege_endDate IS NULL AND Person_id = :Person_id AND exists((SELECT 1 FROM PrivilegeType with (nolock) WHERE PrivilegeType_id = PersonPrivilege.PrivilegeType_id and ReceptFinance_id=2))";
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
			$data['@pmUser_id'] = $data['pmUser_id'];		
			$data[$tab_name.'_IsBad'] = 1;
			$data[$tab_name.'_IsLose'] = 1;
			$data[$tab_name.'_IsActual'] = 2;

			$query_parts .= "@{$tab_name}_id = @{$tab_name}_id output";
			$query_parts .= ", @Error_Code = @Error_Code output";
			$query_parts .= ", @Error_Message = @Error_Message output";
			
			foreach($param_array as $k => $v) { //формирование запроса
				$query_parts .= ", @$k = :$k";
				$sql_p_array[$k] = (!empty($data[$k]) ? $data[$k] : null);
			}
			$query .= str_replace(",*", "", "
				declare
					@{$tab_name}_id bigint,
					@Error_Code bigint,
					@Error_Message varchar(4000);
				exec p_{$tab_name}_ins
					{$query_parts}
				select @{$tab_name}_id as svid_id, @Error_Code as Error_Code, @Error_Message as Error_Message
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
									{$tab_name} with (rowlock)
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
					declare
						@Error_Code bigint,
						@Error_Message varchar(4000);
					exec p_Person_kill
						@Person_id = :Person_id,
						@PersonCloseCause_id = :PersonCloseCause_id,
						@Person_deadDT = :Person_deadDT,
						@pmUser_id = :pmUser_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output
					select @Error_Code as Error_Code, @Error_Message as Error_Message
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
					declare
						@Error_Code bigint,
						@Error_Message varchar(4000);
					exec p_Person_kill
						@Person_id = :Person_id,
						@PersonCloseCause_id = :PersonCloseCause_id,
						@Person_deadDT = :Person_deadDT,
						@pmUser_id = :pmUser_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output
					select @Error_Code as Error_Code, @Error_Message as Error_Message
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
						PR.PersonRegister_id
					from v_PersonRegister PR (NOLOCK)
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
				select d.DeathSvid_id as id
				from v_DeathSvid d with (nolock)
				where d.DeathSvid_id = :DeathSvid_id
					and exists (select top 1 t.DeathSvid_id from v_DeathSvid t with (nolock) where t.Person_id = d.Person_id and ISNULL(t.DeathSvid_IsBad, 1) = 1)
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
				from pmGen (nolock)
				where pmGen_ObjectName = 'MedSvidNum' and Lpu_id = :Lpu_id
			);
			with svid as (
				select
					cast(DeathSvid_Num as bigint) as num
				from v_DeathSvid with(nolock)
				where
					DeathSvid_Ser = @ser
					and ISNUMERIC(DeathSvid_Num) = 1 and cast(DeathSvid_Num as bigint) > @num
				union
				select
					cast(PntDeathSvid_Num as bigint) as num
				from v_PntDeathSvid with(nolock)
				where
					PntDeathSvid_Ser = @ser
					and ISNUMERIC(PntDeathSvid_Num) = 1 and cast(PntDeathSvid_Num as bigint) > @num
				union
				select
					cast(BirthSvid_Num as bigint) as num
				from v_BirthSvid with(nolock)
				where
				 	BirthSvid_Ser = @ser
					and ISNUMERIC(BirthSvid_Num) = 1 and cast(BirthSvid_Num as bigint) > @num
			)
			select top 1
				svid_beg.num as num1,
				svid_end.num as num2
			from
				svid svid_beg with(nolock)
				outer apply(
					select top 1 min(s.num) as num
					from svid s with(nolock)
					where not exists(select top 1 num from svid with(nolock) where num = s.num + 1)
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
			SELECT STUFF(
			(
			 select
					', ' + cast(column_name as varchar)
				from
					dbo.v_columns
				where
					table_name = 'PersonInfo'
					and schema_name = 'dbo'
					and column_name not in ('pmUser_insID','pmUser_updID','PersonInfo_insDT','PersonInfo_updDT')
			FOR XML PATH ('')),1,1,'') as column_name
		");

		$query = "
			select {$personInfoFields}
			from
				PersonInfo
			where
				Person_id = :Person_id
		";

		$result = $this->db->query($query, $data);

		if ( !is_object($result) ) {
			return false;
		}

		$response = $result->result('array');

		if (is_array($response) && count($response) == 1 && !empty($response[0]['PersonInfo_id'])){
			$personInfoParams = $response[0];
			$proc = 'upd';
		} else {
			$personInfoParams['PersonInfo_id'] = 0;
			$proc = 'ins';
			$personInfoParams['Server_id'] = !empty($data['Server_id'])?$data['Server_id']:$data['session']['Server_id'];
			$personInfoParams['Person_id'] = $data['Person_id'];
		}

		$personInfoParams['pmUser_id'] = $data['pmUser_id'];
		$personInfoParams['PersonInfo_IsParsDeath'] = (!empty($data['PersonInfo_IsParsDeath']) && in_array($data['PersonInfo_IsParsDeath'], Array(1, 'true')))?2:1;
		$personInfoParams['PersonInfo_IsSetDeath'] = (!empty($data['PersonInfo_IsSetDeath']) && in_array($data['PersonInfo_IsSetDeath'], Array(1, 'true')))?2:1;

		$query = "
			declare
				@PersonInfo_id bigint = :PersonInfo_id,
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec p_PersonInfo_{$proc}
				@PersonInfo_id = @PersonInfo_id output
		";

		foreach ($personInfoParams as $key => $value){
			if ($key != 'PersonInfo_id') {
				$query .= ",@" . $key . " = :" . $key . "
				";
			}
		}

		$query .= "select @PersonInfo_id as PersonInfo_id, @Error_Code as Error_Code, @Error_Message as Error_Msg";

		//echo getDebugSQL($query, $personInfoParams);die;
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
					select top 1
						DeathSvid_id
					from
						v_DeathSvid (nolock)
					where
						Person_id = :Person_id
						and DeathSvid_Ser = :DeathSvid_OldSer
						and DeathSvid_Num = :DeathSvid_OldNum
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
					select top 1
						PntDeathSvid_id
					from
						v_PntDeathSvid (nolock)
					where
						Person_id = :Person_id
						and PntDeathSvid_Ser = :PntDeathSvid_OldSer
						and PntDeathSvid_Num = :PntDeathSvid_OldNum
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
						$filter .= " and DAY({$SvidTable}_GiveDate) = DAY(:{$SvidTable}_GiveDate) and MONTH({$SvidTable}_GiveDate) = MONTH(:{$SvidTable}_GiveDate) and YEAR({$SvidTable}_GiveDate) = YEAR(:{$SvidTable}_GiveDate)";
						$queryParams["{$SvidTable}_GiveDate"] = $data["{$SvidTable}_GiveDate"];
						break;
					case 2: // неделя
						$filter .= " and DATEPART(WEEK, {$SvidTable}_GiveDate) = DATEPART(WEEK, :{$SvidTable}_GiveDate) and MONTH({$SvidTable}_GiveDate) = MONTH(:{$SvidTable}_GiveDate) and YEAR({$SvidTable}_GiveDate) = YEAR(:{$SvidTable}_GiveDate)";
						$queryParams["{$SvidTable}_GiveDate"] = $data["{$SvidTable}_GiveDate"];
						break;
					case 3: // месяц
						$filter .= " and MONTH({$SvidTable}_GiveDate) = MONTH(:{$SvidTable}_GiveDate) and YEAR({$SvidTable}_GiveDate) = YEAR(:{$SvidTable}_GiveDate)";
						$queryParams["{$SvidTable}_GiveDate"] = $data["{$SvidTable}_GiveDate"];
						break;
					case 4: // год
						$filter .= " and YEAR({$SvidTable}_GiveDate) = YEAR(:{$SvidTable}_GiveDate)";
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
				select top 1
					{$SvidTable}_id
				from
					{$SvidTable} with (nolock)
				where
					ISNULL({$SvidTable}_IsBad, 1) = 1
					and {$SvidTable}_Ser = :{$SvidTable}_Ser
					and {$SvidTable}_Num = cast(:{$SvidTable}_Num as varchar)
					{$filter}
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
					convert(varchar,Person_BirthDay,104) mBirthDay
				from 
					v_Person_ER with (nolock)
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
				select (p.Person_SurName + ' ' + p.Person_FirName + ' ' + p.Person_SecName + ' ' + convert(varchar,p.Person_BirthDay,104)) pstr
				from BirthSvid d left join v_Person_ER p on p.Person_id = d.Person_id
				where d.BirthSvid_IsBad = 1
			) pp 
			where pstr = (select top 1 Person_SurName + ' ' + Person_FirName + ' ' + Person_SecName + ' ' + convert(varchar,Person_BirthDay,104) from v_Person_ER where Person_id = :person_id)";
			$result = $this->db->query($query, array('person_id' => $data['Person_id']));
			$res = $result->result('array');
			$res = $res[0]['cnt'];
			if ($res > 0)
				$err_array[] = "Свидетельство на данного человека заведено. Это возможно по причине рождения двойни. Остановить сохранение и вернутся к редактированию свидетельства?";
			*/
			if(getRegionNick() == 'vologda'){
				$query = "
				select
					BirthSvid_id
				from
					v_BirthSvid with (nolock)
				where
					ISNULL(BirthSvid_IsBad, 1) = 1
					and Person_id = :Person_id
					and BirthSvid_Mass = :BirthSvid_Mass
					and BirthSvid_Height = :BirthSvid_Height
					and cast(BirthSvid_BirthDT as date) = :BirthSvid_BirthDT
				";
				$result = $this->queryResult($query, array(
					'Person_id' => $data['Person_id'],
					'BirthSvid_BirthDT' => $data['BirthSvid_BirthDT_Date'],
					'BirthSvid_Mass' => $data['BirthSvid_Mass'],
					'BirthSvid_Height' => $data['BirthSvid_Height'],
				));
				if (!is_array($result)) {
					$err_array[] = 'Ошибка проверки на дублирование';
				}
				if (count($result) > 0) {
					$err_array[] = 'Данные документа не прошли проверку на дублирование';
				}
			}
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
			if ($this->getRegionNick() !='vologda') {
				$resp_check = $this->queryResult("
							select top 1
								d2.DeathSvid_id,
								d2.DeathSvid_Ser,
								d2.DeathSvid_Num,
								dst.DeathSvidType_Name
							from
								v_DeathSvid d2 (nolock)
								left join v_DeathSvidType dst (nolock) on dst.DeathSvidType_id = d2.DeathSvidType_id
							where
								d2.Person_id = :Person_id
								and ISNULL(d2.DeathSvid_IsActual, 2) = 2
						", array(
					'Person_id' => $data['Person_id']
				));
				if (!empty($resp_check[0]['DeathSvid_id'])) {
					$err_array[] = "Актуальное свидетельство {$resp_check[0]['DeathSvid_Ser']} №{$resp_check[0]['DeathSvid_Num']} ({$resp_check[0]['DeathSvidType_Name']}) о смерти данного человека уже существует. Новое свидетельство можно создать только на основе актуального";
				}
			}
			else{
				$filter =!empty($data['DeathSvid_BirthDateStr']) ? 'and DeathSvid_BirthDateStr = :DeathSvid_BirthDateStr ':
					'and DeathSvid_BirthDateStr is null ';
				$filter.=!empty($data['DeathSvid_DeathDateStr']) ? 'and DeathSvid_DeathDateStr = :DeathSvid_DeathDateStr ':
					'and DeathSvid_DeathDateStr is null ';
				$filter.= !empty($data['DeathSvid_DeathDate_Date']) ? 'and cast(DeathSvid_DeathDate as date) = :DeathSvid_DeathDate ':
					'and DeathSvid_DeathDate is null ';

				$query = "
					select
						DeathSvid_id
					from
						v_DeathSvid with (nolock)
					where
						ISNULL(DeathSvid_IsBad, 1) = 1
						and Person_id = :Person_id
						and DeathSvidType_id = :DeathSvidType_id
						{$filter}
				";
				$result = $this->queryResult($query, array(
					'Person_id' => $data['Person_id'],
					'DeathSvidType_id' => $data['DeathSvidType_id'],
					'DeathSvid_BirthDateStr' => $data['DeathSvid_BirthDateStr'],
					'DeathSvid_DeathDateStr' => $data['DeathSvid_DeathDateStr'],
					'DeathSvid_DeathDate' => $data['DeathSvid_DeathDate_Date']
				));
				if (!is_array($result)) {
					$err_array[] = 'Ошибка проверки на дублирование';
				}
				if (count($result) > 0) {
					$err_array[] = 'Данные документа не прошли проверку на дублирование';
				}
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
					v_PntDeathSvid d2 (nolock)
				where
					d2.Person_id = :Person_id
					and d2.DeathSvidType_id = :DeathSvidType_id
					and ISNULL(d2.PntDeathSvid_IsLose, 1) = 1
					and ISNULL(d2.PntDeathSvid_IsBad, 1) = 1
					and d2.DeathSvidType_id not in (3,4)
			", array(
				'Person_id' => $data['Person_id'],
				'DeathSvidType_id' => $data['DeathSvidType_id']
			));
			if (!empty($DeathSvid_id)) {
				$err_array[] = "Уже существует неиспорченное свидетельство данного типа на данного человека";
			}*/
			if(getRegionNick() == 'vologda'){
				$filter = !empty($data['PntDeathSvid_DeathDate_Date']) ? 'and cast(PntDeathSvid_DeathDate as date) = :PntDeathSvid_DeathDate ':
					'and PntDeathSvid_DeathDate is null ';
				$filter.=!empty($data['PntDeathSvid_DeathDateStr']) ? 'and PntDeathSvid_DeathDateStr = :PntDeathSvid_DeathDateStr ':
					'and PntDeathSvid_DeathDateStr is null ';
				$filter.= !empty($data['PntDeathSvid_ChildBirthDT_Date']) ? 'and cast(PntDeathSvid_ChildBirthDT as date) = :PntDeathSvid_ChildBirthDT ':
					'and PntDeathSvid_ChildBirthDT is null ';
				$filter.=!empty($data['PntDeathSvid_BirthDateStr']) ? 'and PntDeathSvid_BirthDateStr = :PntDeathSvid_BirthDateStr ':
					'and PntDeathSvid_BirthDateStr is null ';
				$filter.=!empty($data['PntDeathSvid_Mass']) ? 'and PntDeathSvid_Mass = :PntDeathSvid_Mass ':
					'and PntDeathSvid_Mass is null ';
				$filter.=!empty($data['PntDeathSvid_Height']) ? 'and PntDeathSvid_Height = :PntDeathSvid_Height ':
					'and PntDeathSvid_Height is null ';

				$query = "
				select
					PntDeathSvid_id
				from
					v_PntDeathSvid with (nolock)
				where
					ISNULL(PntDeathSvid_IsBad, 1) = 1
					and Person_id = :Person_id
					and DeathSvidType_id = :DeathSvidType_id
					{$filter}
				";
				$result = $this->queryResult($query, array(
					'Person_id' => $data['Person_id'],
					'DeathSvidType_id' => $data['DeathSvidType_id'],
					'PntDeathSvid_DeathDate' => $data['PntDeathSvid_DeathDate_Date'],
					'PntDeathSvid_DeathDateStr' => $data['PntDeathSvid_DeathDateStr'],
					'PntDeathSvid_ChildBirthDT' => $data['PntDeathSvid_ChildBirthDT_Date'],
					'PntDeathSvid_BirthDateStr' => $data['PntDeathSvid_BirthDateStr'],
					'PntDeathSvid_Mass' => $data['PntDeathSvid_Mass'],
					'PntDeathSvid_Height' => $data['PntDeathSvid_Height'],
				));
				if (!is_array($result)) {
					$err_array[] = 'Ошибка проверки на дублирование';
				}
				if (count($result) > 0) {
					$err_array[] = 'Данные документа не прошли проверку на дублирование';
				}
			}
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
					declare
						@Address_id bigint = null,
						@Error_Code bigint,
						@Error_Message varchar(4000);
					exec p_Address_ins
						@Server_id = :Server_id,
						@Address_id = @Address_id output,
						@KLAreaType_id = Null, -- опреляется логикой в хранимке
						@KLCountry_id = :KLCountry_id,
						@KLRgn_id = :KLRGN_id,
						@KLSubRgn_id = :KLSubRGN_id,
						@KLCity_id = :KLCity_id,
						@KLTown_id = :KLTown_id,
						@KLStreet_id = :KLStreet_id,
						@Address_Zip = :Address_Zip,
						@Address_House = :Address_House,
						@Address_Corpus = :Address_Corpus,
						@Address_Flat = :Address_Flat,
						@Address_Address = :Address_Address,
						@pmUser_id = :pmUser_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output
					select @Address_id as Address_id, @Error_Code as Error_Code, @Error_Message as Error_Message
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
					declare
						@Address_id bigint = :Address_id,
						@Error_Code bigint,
						@Error_Message varchar(4000);
					exec p_Address_upd
						@Server_id = :Server_id,
						@Address_id = @Address_id output,
						@KLAreaType_id = NULL, -- опреляется логикой в хранимке
						@KLCountry_id = :KLCountry_id,
						@KLRgn_id = :KLRGN_id,
						@KLSubRgn_id = :KLSubRGN_id,
						@KLCity_id = :KLCity_id,
						@KLTown_id = :KLTown_id,
						@KLStreet_id = :KLStreet_id,
						@Address_Zip = :Address_Zip,
						@Address_House = :Address_House,
						@Address_Corpus = :Address_Corpus,
						@Address_Flat = :Address_Flat,
						@Address_Address = :Address_Address,
						@pmUser_id = :pmUser_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output
					select @Address_id as Address_id, @Error_Code as Error_Code, @Error_Message as Error_Message
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
		if(getRegionNick() =='vologda'){

			$query = "
			select
				PntDeathSvid_id
			from
				v_PntDeathSvid with (nolock)
			where
				PntDeathSvid_IsActual != 2
				and PntDeathSvid_id = :PntDeathSvid_id
			";
			$result = $this->queryResult($query, array(
				'PntDeathSvid_id' => $data['PntDeathSvid_id']
			));
			if (!is_array($result)) {
				return array('success' => false, 'Error_Msg' => 'Ошибка проверки на актуальное свидетельство');
			}
			if (count($result) > 0) {
				return array('success' => false, 'Error_Msg' => 'Редактирование свидетельства, которое не является актуальным, невозможно.');
			}

			$query = "
				select
					PntDeathSvid_id
				from
					v_PntDeathSvid with (nolock)
				where
					ISNULL(PntDeathSvid_IsBad, 1) = 1
					and PntDeathSvid_Ser = :PntDeathSvid_Ser
					and PntDeathSvid_Num = :PntDeathSvid_Num
					and PntDeathSvid_id != :PntDeathSvid_id
			";
			$result = $this->queryResult($query, array(
				'PntDeathSvid_Ser' => $data['PntDeathSvid_Ser'],
				'PntDeathSvid_Num' => $data['PntDeathSvid_Num'],
				'PntDeathSvid_id' => $data['PntDeathSvid_id']
			));
			if (!is_array($result)) {
				return array('success' => false, 'Error_Msg' => 'Ошибка проверки на существование свидетельств с теми же номером и серией');
			}
			if (count($result) > 0) {
				return array('success' => false, 'Error_Msg' => 'Свидетельство с данными номером и серией уже существует');
			}

			$filter = !empty($data['PntDeathSvid_DeathDate_Date']) ? 'and cast(PntDeathSvid_DeathDate as date) = :PntDeathSvid_DeathDate ':
				'and PntDeathSvid_DeathDate is null ';
			$filter.=!empty($data['PntDeathSvid_DeathDateStr']) ? 'and PntDeathSvid_DeathDateStr = :PntDeathSvid_DeathDateStr ':
				'and PntDeathSvid_DeathDateStr is null ';
			$filter.= !empty($data['PntDeathSvid_ChildBirthDT_Date']) ? 'and cast(PntDeathSvid_ChildBirthDT as date) = :PntDeathSvid_ChildBirthDT ':
				'and PntDeathSvid_ChildBirthDT is null ';
			$filter.=!empty($data['PntDeathSvid_BirthDateStr']) ? 'and PntDeathSvid_BirthDateStr = :PntDeathSvid_BirthDateStr ':
				'and PntDeathSvid_BirthDateStr is null ';
			$filter.=!empty($data['PntDeathSvid_Mass']) ? 'and PntDeathSvid_Mass = :PntDeathSvid_Mass ':
				'and PntDeathSvid_Mass is null ';
			$filter.=!empty($data['PntDeathSvid_Height']) ? 'and PntDeathSvid_Height = :PntDeathSvid_Height ':
				'and PntDeathSvid_Height is null ';
			$query = "
				select
					PntDeathSvid_id
				from
					v_PntDeathSvid with (nolock)
				where
					ISNULL(PntDeathSvid_IsBad, 1) = 1
					and Person_id = :Person_id
					and DeathSvidType_id = :DeathSvidType_id
					and PntDeathSvid_id != :PntDeathSvid_id
					{$filter}
				";
			$result = $this->queryResult($query, array(
				'Person_id' => $data['Person_id'],
				'DeathSvidType_id' => $data['DeathSvidType_id'],
				'PntDeathSvid_DeathDate' => $data['PntDeathSvid_DeathDate_Date'],
				'PntDeathSvid_DeathDateStr' => $data['PntDeathSvid_DeathDateStr'],
				'PntDeathSvid_ChildBirthDT' => $data['PntDeathSvid_ChildBirthDT_Date'],
				'PntDeathSvid_BirthDateStr' => $data['PntDeathSvid_BirthDateStr'],
				'PntDeathSvid_Mass' => $data['PntDeathSvid_Mass'],
				'PntDeathSvid_Height' => $data['PntDeathSvid_Height'],
				'PntDeathSvid_id' => $data['PntDeathSvid_id']
			));
			if (!is_array($result)) {
				return array('success' => false, 'Error_Msg' => 'Ошибка проверки на дублирование');
			}
			if (count($result) > 0) {
				return array('success' => false, 'Error_Msg' => 'Не удалось изменить данные о свидетельстве, т.к. данные документа не прошли проверку на дублирование');
			}

		}
		$query = "
			update
				PntDeathSvid with (rowlock)
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
		if(getRegionNick() =='vologda'){

			$query = "
			select
				DeathSvid_id
			from
				v_DeathSvid with (nolock)
			where
				DeathSvid_IsActual != 2
				and DeathSvid_id = :DeathSvid_id
			";
			$result = $this->queryResult($query, array(
				'DeathSvid_id' => $data['DeathSvid_id']
			));
			if (!is_array($result)) {
				return array('success' => false, 'Error_Msg' => 'Ошибка проверки на актуальное свидетельство');
			}
			if (count($result) > 0) {
				return array('success' => false, 'Error_Msg' => 'Редактирование свидетельства, которое не является актуальным, невозможно.');
			}

			$query = "
				select
					DeathSvid_id
				from
					v_DeathSvid with (nolock)
				where
					ISNULL(DeathSvid_IsBad, 1) = 1
					and DeathSvid_Ser = :DeathSvid_Ser
					and DeathSvid_Num = :DeathSvid_Num
					and DeathSvid_id != :DeathSvid_id
			";
			$result = $this->queryResult($query, array(
				'DeathSvid_Ser' => $data['DeathSvid_Ser'],
				'DeathSvid_Num' => $data['DeathSvid_Num'],
				'DeathSvid_id' => $data['DeathSvid_id']
			));
			if (!is_array($result)) {
				return array('success' => false, 'Error_Msg' => 'Ошибка проверки на существование свидетельств с теми же номером и серией');
			}
			if (count($result) > 0) {
				return array('success' => false, 'Error_Msg' => 'Свидетельство с данными номером и серией уже существует');
			}

			$filter =!empty($data['DeathSvid_BirthDateStr']) ? 'and DeathSvid_BirthDateStr = :DeathSvid_BirthDateStr ':
				'and DeathSvid_BirthDateStr is null ';
			$filter.=!empty($data['DeathSvid_DeathDateStr']) ? 'and DeathSvid_DeathDateStr = :DeathSvid_DeathDateStr ':
				'and DeathSvid_DeathDateStr is null ';
			$filter.= !empty($data['DeathSvid_DeathDate_Date']) ? 'and cast(DeathSvid_DeathDate as date) = :DeathSvid_DeathDate ':
				'and DeathSvid_DeathDate is null ';

			$query = "
				select
						DeathSvid_id
				from
					v_DeathSvid with (nolock)
				where
					ISNULL(DeathSvid_IsBad, 1) = 1
					and Person_id = :Person_id
					and DeathSvidType_id = :DeathSvidType_id
					and DeathSvid_id != :DeathSvid_id
					{$filter}
			";

			$result = $this->queryResult($query, array(
				'Person_id' => $data['Person_id'],
				'DeathSvidType_id' => $data['DeathSvidType_id'],
				'DeathSvid_BirthDateStr' => $data['DeathSvid_BirthDateStr'],
				'DeathSvid_DeathDateStr' => $data['DeathSvid_DeathDateStr'],
				'DeathSvid_DeathDate' => $data['DeathSvid_DeathDate_Date'],
				'DeathSvid_id' => $data['DeathSvid_id']
			));
			if (!is_array($result)) {
				return array('success' => false, 'Error_Msg' => 'Ошибка проверки на дублирование');
			}
			if (count($result) > 0) {
				return array('success' => false, 'Error_Msg' => 'Не удалось изменить данные о свидетельстве, т.к. данные документа не прошли проверку на дублирование');
			}
		}
		$query = "
			update
				DeathSvid with (rowlock)
			set
				Person_rid = :Person_rid,
				DeathSvid_PolFio = :DeathSvid_PolFio,
				DeathSvid_RcpDocument = :DeathSvid_RcpDocument,
				DeathSvidRelation_id = :DeathSvidRelation_id,
				DeathSvid_RcpDate = :DeathSvid_RcpDate,
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
		if(getRegionNick() =='vologda'){

			$query = "
			select
				BirthSvid_id
			from
				v_BirthSvid with (nolock)
			where
				BirthSvid_IsBad = 2
				and BirthSvid_id = :BirthSvid_id
			";
			$result = $this->queryResult($query, array(
				'BirthSvid_id' => $data['BirthSvid_id']
			));
			if (!is_array($result)) {
				return array('success' => false, 'Error_Msg' => 'Ошибка проверки на отметку «Испорченный»');
			}
			if (count($result) > 0) {
				return array('success' => false, 'Error_Msg' => 'Редактирование свидетельства с отметкой «Испорченный» невозможно.');
			}

			$query = "
				select
					BirthSvid_id
				from
					v_BirthSvid with (nolock)
				where
					ISNULL(BirthSvid_IsBad, 1) = 1
					and BirthSvid_Ser = :BirthSvid_Ser
					and BirthSvid_Num = :BirthSvid_Num
					and BirthSvid_id != :BirthSvid_id
			";
			$result = $this->queryResult($query, array(
				'BirthSvid_Ser' => $data['BirthSvid_Ser'],
				'BirthSvid_Num' => $data['BirthSvid_Num'],
				'BirthSvid_id' => $data['BirthSvid_id']
			));
			if (!is_array($result)) {
				return array('success' => false, 'Error_Msg' => 'Ошибка проверки на существование свидетельств с теми же номером и серией');
			}
			if (count($result) > 0) {
				return array('success' => false, 'Error_Msg' => 'Свидетельство с данными номером и серией уже существует');
			}

			$query = "
				select
					BirthSvid_id
				from
					v_BirthSvid with (nolock)
				where
					ISNULL(BirthSvid_IsBad, 1) = 1
					and Person_id = :Person_id
					and BirthSvid_Mass = :BirthSvid_Mass
					and BirthSvid_Height = :BirthSvid_Height
					and cast(BirthSvid_BirthDT as date) = :BirthSvid_BirthDT
					and BirthSvid_id != :BirthSvid_id
			";
			$result = $this->queryResult($query, array(
				'Person_id' => $data['Person_id'],
				'BirthSvid_BirthDT' => $data['BirthSvid_BirthDT_Date'],
				'BirthSvid_Mass' => $data['BirthSvid_Mass'],
				'BirthSvid_Height' => $data['BirthSvid_Height'],
				'BirthSvid_id' => $data['BirthSvid_id']
			));
			if (!is_array($result)) {
				return array('success' => false, 'Error_Msg' => 'Ошибка проверки на дублирование');
			}
			if (count($result) > 0) {
				return array('success' => false, 'Error_Msg' => 'Не удалось изменить данные о свидетельстве, т.к. данные документа не прошли проверку на дублирование');
			}

		}
		$query = "
			update
				BirthSvid with (rowlock)
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
			$isActual = "(case when :IsLose = 1 and {$table_name}_IsBad = 1 then 2 else 1 end)";
			// для свидетельств о смерти может быть только 1 актуальное свидетельство о смерти
			if ($table_name == 'DeathSvid') {
				$isActual = "(case when :IsLose = 1 and {$table_name}_IsBad = 1 and not exists(select top 1 {$table_name}_id from $table_name where {$table_name}_IsActual = 2 and {$table_name}_id <> :{$table_name}_id and Person_id = svid.Person_id) then 2 else 1 end)";
			}

			$query = "
				update
					svid with (rowlock)
				set
					svid.{$table_name}_IsLose = :IsLose,
					svid.{$table_name}_IsActual = {$isActual}
				from
					{$table_name} svid
				where
					svid.{$table_name}_id = :{$table_name}_id
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
					{$table_name} with (rowlock)
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
				select top 1
					d2.DeathSvid_id
				from
					v_DeathSvid d1 (nolock)
					inner join v_DeathSvid d2 (nolock) on d2.Person_id = d1.Person_id and d2.DeathSvidType_id = d1.DeathSvidType_id and ISNULL(d2.DeathSvid_IsLose, 1) = 1 and ISNULL(d2.DeathSvid_IsBad, 1) = 1
				where
					d1.DeathSvid_id = :DeathSvid_id
					and d1.DeathSvidType_id not in (3,4)
			", array(
				'DeathSvid_id' => $data['svid_id']
			));
			if (!empty($DeathSvid_id)) {
				return array(array('Error_Msg' => 'Уже существует неиспорченное свидетельство данного типа на данного человека'));
			}
		}

		if ($data['bad_id'] == 1 && in_array($table_name, array('BirthSvid', 'DeathSvid'))) {
			$resp_bs = $this->queryResult("
				select top 1
					bs.{$table_name}_id as svid_id,
					bs.{$table_name}_Ser as svid_ser,
					bs.{$table_name}_Num as svid_num
				from
					v_{$table_name} bs (nolock)
				where
					bs.{$table_name}_pid = :svid_id
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
					v_PntDeathSvid d1 (nolock)
					inner join v_PntDeathSvid d2 (nolock) on d2.Person_id = d1.Person_id and d2.DeathSvidType_id = d1.DeathSvidType_id and ISNULL(d2.PntDeathSvid_IsLose, 1) = 1 and ISNULL(d2.PntDeathSvid_IsBad, 1) = 1
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
					Lpu_id,
					LpuSection_id,
					convert(varchar(10), {$table_name}_GiveDate, 120) as GiveDate
				from
					{$table_name} with (nolock)
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
							$filter .= " and DAY({$table_name}_GiveDate) = DAY(:{$table_name}_GiveDate) and MONTH({$table_name}_GiveDate) = MONTH(:{$table_name}_GiveDate) and YEAR({$table_name}_GiveDate) = YEAR(:{$table_name}_GiveDate)";
							$queryParams["{$table_name}_GiveDate"] = $resp[0]['GiveDate'];
							break;
						case 2: // неделя
							$filter .= " and DATEPART(WEEK, {$table_name}_GiveDate) = DATEPART(WEEK, :{$table_name}_GiveDate) and MONTH({$table_name}_GiveDate) = MONTH(:{$table_name}_GiveDate) and YEAR({$table_name}_GiveDate) = YEAR(:{$table_name}_GiveDate)";
							$queryParams["{$table_name}_GiveDate"] = $resp[0]['GiveDate'];
							break;
						case 3: // месяц
							$filter .= " and MONTH({$table_name}_GiveDate) = MONTH(:{$table_name}_GiveDate) and YEAR({$table_name}_GiveDate) = YEAR(:{$table_name}_GiveDate)";
							$queryParams["{$table_name}_GiveDate"] = $resp[0]['GiveDate'];
							break;
						case 4: // год
							$filter .= " and YEAR({$table_name}_GiveDate) = YEAR(:{$table_name}_GiveDate)";
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
				select top 1 d.{$table_name}_id as id
				from v_{$table_name} d with (nolock)
				where d.{$table_name}_id = :svid_id
					and exists (
						select top 1 t.{$table_name}_id
						from v_{$table_name} t with (nolock)
						where ISNULL(t.{$table_name}_Ser, '') = ISNULL(d.{$table_name}_Ser, '')
							and ISNULL(t.{$table_name}_Num, '') = ISNULL(d.{$table_name}_Num, '')
							and ISNULL(t.{$table_name}_IsBad, 1) = 1
							{$filter}
					)
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
					{$person_field} as Person_id,
					convert(varchar, {$table_name}_DeathDate, 120) as DeathDate,
					convert(varchar, {$table_name}_GiveDate, 120) as GiveDate
				FROM {$table_name} with (nolock)
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
					select top 1
						count(DeathSvid_id) as Count
					from
						v_DeathSvid DS with (nolock)
					where
						DS.Person_id = :Person_id
						and ISNULL(DS.DeathSvid_IsBad, 1) = 1
						and DS.DeathSvid_id <> :Svid_id
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
						declare
							@Error_Code bigint,
							@Error_Message varchar(4000);
						exec p_Person_revive
							@Person_id = :Person_id,
							@pmUser_id = :pmUser_id,
							@Error_Code = @Error_Code output,
							@Error_Message = @Error_Message output
						select @Error_Code as Error_Code, @Error_Message as Error_Message
					";
					
					$this->load->model('PersonRegister_model','PRegister_model');
					$res = $this->queryResult("
						select
							PR.PersonRegister_id
						from v_PersonRegister PR (NOLOCK)
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
					declare
						@Error_Code bigint,
						@Error_Message varchar(4000);
					exec p_Person_kill
						@Person_id = :Person_id,
						@PersonCloseCause_id = :PersonCloseCause_id,
						@Person_deadDT = :Person_deadDT,
						@pmUser_id = :pmUser_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output
					select @Error_Code as Error_Code, @Error_Message as Error_Message
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

		if (in_array($table_name, array('DeathSvid', 'PntDeathSvid'))) {
			// меняем ещё и признак актуальности
			$isActual = "(case when ".$data['bad_id']." = 1 and ".$table_name."_isLose = 1 then 2 else 1 end)";
			// для свидетельств о смерти может быть только 1 актуальное свидетельство о смерти
			if ($table_name == 'DeathSvid') {
				$isActual = "(case when ".$data['bad_id']." = 1 and ".$table_name."_isLose = 1 and not exists(select top 1 ".$table_name."_id from $table_name where ".$table_name."_IsActual = 2 and ".$table_name."_id <> :Svid_id and Person_id = svid.Person_id) then 2 else 1 end)";
			}
			$query = "UPDATE svid with (rowlock) SET svid.".$table_name."_isBad = ".$data['bad_id'].", svid.".$table_name."_IsActual = ".$isActual." FROM {$table_name} svid WHERE svid.".$table_name."_id = :Svid_id";
		} else {
			$query = "UPDATE svid with (rowlock) SET svid.".$table_name."_isBad = ".$data['bad_id']." FROM {$table_name} svid WHERE svid.".$table_name."_id = :Svid_id";
		}

		$result = $this->db->query($query, array(
			'Svid_id' => $data['svid_id']
		));

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
						bs.{$table_name}_id as svid_id,
						bs.{$table_name}_Ser as svid_ser,
						bs.{$table_name}_Num as svid_num,
						substring(ps.Person_SurName, 1, 1) as Person_SurName,
						ps.Person_FirName,
						ps.Person_SecName
					from
						v_{$table_name} bs (nolock)
						inner join v_PersonState ps (nolock) on ps.Person_id = bs.Person_rid
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
				$join = " outer apply(
						select top 1 Object_sid
						from v_ObjectSynchronLog OSL with(nolock)
						where ObjectSynchronLogService_id = 2 and OSL.Object_id = d.BirthSvid_id
				    ) OSL";
				$fields = ",case when OSL.Object_sid is null then 0 else 1 end as BirthSvid_isInRpn";
			} else {
				$fields = ",0 as BirthSvid_isInRpn";
			}

			$query = "
				SELECT TOP 1
					d.Server_id,
					d.BirthSvid_id,
					d.Lpu_id,
					d.Person_id,
					d.Person_rid,
					isnull(rtrim(rp.Person_Surname)+' ','') + isnull(rtrim(rp.Person_Firname)+' ','') + isnull(rtrim(rp.Person_Secname),'') as Person_r_FIO,
					d.BirthSvid_Ser,
					d.BirthSvid_Num,
					d.MedStaffFact_id,
					d.MedPersonal_id,
					d.LpuSection_id,
					d.BirthMedPersonalType_id,
					d.ReceptType_id,
					d.BirthEducation_id,
					d.BirthPlace_id,
					d.Sex_id,
					d.BirthSvid_Week,
					d.BirthSvid_ChildCount,
					d.BirthFamilyStatus_id,
					d.BirthEmployment_id,
					d.BirthSpecialist_id,
					d.BirthSvid_ChildFamil,
					d.BirthSvid_IsMnogoplod,
					d.BirthSvid_PlodIndex,
					d.BirthSvid_PlodCount,
					d.BirthSvid_IsFromMother,
					d.BirthSvid_Height,
					d.BirthSvid_Mass,
					d.OrgHead_id,
					d.BirthChildResult_id,
					d.Address_rid,
					ba.Address_Zip as BAddress_Zip,
					ba.KLCountry_id as BKLCountry_id,
					ba.KLRGN_id as BKLRGN_id,
					ba.KLSubRGN_id as BKLSubRGN_id,
					ba.KLCity_id as BKLCity_id,
					ba.KLTown_id as BKLTown_id,
					ba.KLStreet_id as BKLStreet_id,
					ba.Address_House as BAddress_House,
					ba.Address_Corpus as BAddress_Corpus,
					ba.Address_Flat as BAddress_Flat,
					ba.Address_Address as BAddress_Address,
					ba.Address_Address as BAddress_AddressText,
					convert(varchar,d.BirthSvid_GiveDate,104) as BirthSvid_GiveDate,
					convert(varchar,d.BirthSvid_BirthDT,104) as BirthSvid_BirthDT_Date,
					substring(convert(varchar,d.BirthSvid_BirthDT,108),1,5) as BirthSvid_BirthDT_Time,
					convert(varchar,d.BirthSvid_RcpDate,104) as BirthSvid_RcpDate,
					d.BirthSvid_RcpDocument,
					d.BirthSvid_IsBad,
					d.DeputyKind_id,
					d.Okei_mid,
					d.LpuLicence_id,
					
					--d.BirthSvid_IsOtherMO,
					CASE WHEN d.BirthSvid_IsOtherMO=2 THEN 1 else 0 END as BirthSvid_IsOtherMO,
					d.Org_id,
					d.MedStaffFact_cid,
					
					L.LpuLicence_Num
					{$fields}
				FROM
					BirthSvid d with (nolock)
					left join v_Person_ER rp with (nolock) on rp.Person_id = d.Person_rid
					left outer join Address ba with (nolock) on ba.Address_id = d.Address_rid
				    left join LpuLicence L with (nolock) on L.LpuLicence_id = d.LpuLicence_id
				    {$join}
				WHERE (1 = 1)
					AND BirthSvid_id = :svid_id
			";

            //echo getDebugSQL($query);exit;

			$result = $this->db->query($query, array('svid_id' => $svid_id));
		}
		
		if ($svid_type == "death") {
			$query = "
				SELECT TOP 1
					d.DeathSvid_id,
					d.Server_id,
					d.Lpu_id,
					d.Person_id,
					d.Person_mid,
					isnull(rtrim(mp.Person_Surname)+' ','') + isnull(rtrim(mp.Person_Firname)+' ','') + isnull(rtrim(mp.Person_Secname),'') as Person_m_FIO,
					convert(varchar,mp.Person_BirthDay,104) as Mother_BirthDay,
					d.Person_rid,
					isnull(rtrim(rp.Person_Surname)+' ','') + isnull(rtrim(rp.Person_Firname)+' ','') + isnull(rtrim(rp.Person_Secname),'') as Person_r_FIO,
					d.DeathSvid_PolFio,
					d.MedStaffFact_id,
					d.MedPersonal_id,
					d.LpuSection_id,
					d.DeathSvidType_id,
					d.DeathSvid_IsDuplicate,
					d.DeathSvid_IsLose,
					d.ReceptType_id,
					d.DeathCause_id,
					d.DeathFamilyStatus_id,
					d.DeathPlace_id,
					d.DeathEducation_id,
					d.DeathTrauma_id,
					d.DeathSetType_id,
					d.DeathSetCause_id,
					d.DeathWomanType_id,
					d.DeathEmployment_id,
					d.DtpDeathTime_id,
					d.ChildTermType_id,
					d.Address_bid,
					ba.Address_Zip as BAddress_Zip,
					ba.KLCountry_id as BKLCountry_id,
					ba.KLRGN_id as BKLRGN_id,
					ba.KLSubRGN_id as BKLSubRGN_id,
					ba.KLCity_id as BKLCity_id,
					ba.KLTown_id as BKLTown_id,
					ba.KLStreet_id as BKLStreet_id,
					ba.Address_House as BAddress_House,
					ba.Address_Corpus as BAddress_Corpus,
					ba.Address_Flat as BAddress_Flat,
					ba.Address_Address as BAddress_Address,
					ba.Address_Address as BAddress_AddressText,
					d.Address_did,
					da.Address_Zip as DAddress_Zip,
					da.KLCountry_id as DKLCountry_id,
					da.KLRGN_id as DKLRGN_id,
					da.KLSubRGN_id as DKLSubRGN_id,
					da.KLCity_id as DKLCity_id,
					da.KLTown_id as DKLTown_id,
					da.KLStreet_id as DKLStreet_id,
					da.Address_House as DAddress_House,
					da.Address_Corpus as DAddress_Corpus,
					da.Address_Flat as DAddress_Flat,
					da.Address_Address as DAddress_Address,
					da.Address_Address as DAddress_AddressText,
					d.Diag_iid,
					d.Diag_eid,
					d.Diag_mid,
					d.Diag_tid,
					d.Diag_oid,
					d.MedStaffFact_did,
					d.Okei_id,
					d.Okei_patid,
					d.Okei_domid,
					d.Okei_extid,
					d.Okei_impid,
					d.DeathSvid_IsPrimDiagIID,
					d.DeathSvid_IsPrimDiagEID,
					d.DeathSvid_IsPrimDiagMID,
					d.DeathSvid_IsPrimDiagTID,
					d.DeathSvid_BirthDateStr,
					d.DeathSvid_DeathDateStr,
					d.DeathSvid_Ser,
					d.DeathSvid_Num,
					d.DeathSvid_OldSer,
					d.DeathSvid_OldNum,
					convert(varchar,d.DeathSvid_GiveDate,104) as DeathSvid_GiveDate,
					convert(varchar,d.DeathSvid_OldGiveDate,104) as DeathSvid_OldGiveDate,
					convert(varchar,d.DeathSvid_DeathDate,104) as DeathSvid_DeathDate_Date,
					case when d.DeathSvid_IsNoDeathTime = 2 then null else substring(convert(varchar,d.DeathSvid_DeathDate,108),1,5) end as DeathSvid_DeathDate_Time,
					case when d.DeathSvid_DeathDate is null and DeathSvid_DeathDateStr is null then 1 else 0 end as DeathSvid_IsUnknownDeathDate,
					case when d.DeathSvid_IsNoDeathTime = 2 or d.DeathSvid_DeathDate is null then 1 else 0 end as DeathSvid_IsNoDeathTime,
					case when d.DeathSvid_IsNoAccidentTime = 2 or d.DeathSvid_TraumaDate is null then 1 else 0 end as DeathSvid_IsNoAccidentTime,
					case when d.DeathSvid_IsNoPlace = 2 then 1 else 0 end as DeathSvid_IsNoPlace,
					case when d.DeathSvid_isBirthDate = 2 then 1 else 0 end as DeathSvid_isBirthDate,
					d.DeathSvid_IsTerm,
					d.DeathSvid_Mass,
					d.DeathSvid_Month,
					d.DeathSvid_Day,
					d.DeathSvid_ChildCount,
					d.DeathSvid_TraumaDateStr,
					convert(varchar,d.DeathSvid_TraumaDate,104) as DeathSvid_TraumaDate_Date,
					case when d.DeathSvid_IsNoAccidentTime = 2 then null else substring(convert(varchar,d.DeathSvid_TraumaDate,108),1,5) end as DeathSvid_TraumaDate_Time,
					--substring(convert(varchar,d.DeathSvid_TraumaDate,108),1,5) as DeathSvid_TraumaDate_Time,
					d.DeathSvid_TraumaDescr,
					d.DeathSvid_Oper,
					d.DeathSvid_PribPeriod,
					d.DeathSvid_PribPeriodPat,
					d.DeathSvid_PribPeriodDom,
					d.DeathSvid_PribPeriodExt,
					d.DeathSvid_PribPeriodImp,
					d.DeathSvid_TimePeriod,
					d.DeathSvid_TimePeriodPat,
					d.DeathSvid_TimePeriodDom,
					d.DeathSvid_TimePeriodExt,
					d.DeathSvid_TimePeriodImp,
					convert(varchar,d.DeathSvid_RcpDate,104) as DeathSvid_RcpDate,
					d.DeathSvidRelation_id,
					d.DeathSvid_RcpDocument,
					d.DeathSvid_IsBad,
					d.Person_hid,
					d.OrgHeadPost_id,
					oh.OrgHead_id,
					ohp.OrgHeadPost_Name,
					(ph.Person_SurName+' '+ph.Person_FirName+' '+isnull(ph.Person_SecName,'')) as Person_hFIO,
					pi.PersonInfo_IsParsDeath,
					pi.PersonInfo_IsSetDeath,
					d.MedPersonal_cid,
					convert(varchar,d.DeathSvid_checkDate,104) as DeathSvid_checkDate
				FROM
					v_DeathSvid d with (nolock)
					left join v_Person_ER mp with (nolock) on mp.Person_id = d.Person_mid
					left outer join v_Person_ER rp with (nolock) on rp.Person_id = d.Person_rid
					left outer join Address ba with (nolock) on ba.Address_id = d.Address_bid
					left outer join Address da with (nolock) on da.Address_id = d.Address_did
					left join v_OrgHead oh with(nolock) on oh.Person_id = d.Person_hid and oh.OrgHeadPost_id = d.OrgHeadPost_id
					left join v_OrgHeadPost ohp with(nolock) on ohp.OrgHeadPost_id = d.OrgHeadPost_id
					left join v_PersonState ph with(nolock) on ph.Person_id = d.Person_hid
					left join v_PersonInfo pi with(nolock) on pi.Person_id = d.Person_id
				WHERE (1 = 1)
					AND DeathSvid_id = :svid_id";

			$result = $this->db->query($query, array('svid_id' => $svid_id));
		}
		
		if ($svid_type == "pntdeath") {
			$query = "
				SELECT TOP 1
					d.PntDeathSvid_id,
					d.Server_id,
					d.Person_id,
					d.Person_cid,
					d.Person_rid,
					isnull(rtrim(rp.Person_Surname)+' ','') + isnull(rtrim(rp.Person_Firname)+' ','') + isnull(rtrim(rp.Person_Secname),'') as Person_r_FIO,
					d.PntDeathSvid_PolFio,
					d.PntDeathSvid_BirthDateStr,
					d.PntDeathSvid_DeathDateStr,
					d.PntDeathSvid_Ser,
					d.PntDeathSvid_Num,
					d.PntDeathPeriod_id,
					d.DeathSvidType_id,
					d.PntDeathSvid_IsDuplicate,
					d.PntDeathSvid_IsLose,
					d.PntDeathSvid_OldSer,
					d.PntDeathSvid_OldNum,
					d.MedStaffFact_id,
					d.MedPersonal_id,
					d.LpuSection_id,
					d.PntDeathSvid_DeathDate,
					convert(varchar,d.PntDeathSvid_DeathDate,104) as PntDeathSvid_DeathDate_Date,
					substring(convert(varchar,d.PntDeathSvid_DeathDate,108),1,5) as PntDeathSvid_DeathDate_Time,
					d.ReceptType_id,
					d.PntDeathSvid_ChildFio,
					convert(varchar,d.PntDeathSvid_ChildBirthDT,104) as PntDeathSvid_ChildBirthDT_Date,
					substring(convert(varchar,d.PntDeathSvid_ChildBirthDT,108),1,5) as PntDeathSvid_ChildBirthDT_Time,
					d.PntDeathSvid_PlodIndex,
					d.PntDeathSvid_PlodCount,
					d.PntDeathSvid_RcpDoc,
					convert(varchar,d.PntDeathSvid_RcpDate,104) as PntDeathSvid_RcpDate,
					d.PntDeathFamilyStatus_id,
					d.DeathEmployment_id,
					d.PntDeathPlace_id,
					d.PntDeathEducation_id,
					d.Sex_id,
					d.PntDeathSvid_ChildCount,
					d.PntDeathSvid_BirthCount,
					d.PntDeathGetBirth_id,
					d.PntDeathTime_id,
					d.PntDeathCause_id,
					d.PntDeathSetType_id,
					d.PntDeathSetCause_id,
					d.MedStaffFact_did,
					d.Diag_iid,
					d.Diag_eid,
					d.Diag_mid,
					d.Diag_tid,
					d.Diag_oid,
					case when d.PntDeathSvid_IsNoPlace = 2 then 1 else 0 end as PntDeathSvid_IsNoPlace,
					d.PntDeathSvid_Mass,
					d.PntDeathSvid_Height,
					d.PntDeathSvid_IsMnogoplod,
					convert(varchar,d.PntDeathSvid_GiveDate,104) as PntDeathSvid_GiveDate,
					convert(varchar,d.PntDeathSvid_OldGiveDate,104) as PntDeathSvid_OldGiveDate,
					da.Address_Zip as DAddress_Zip,
					da.KLCountry_id as DKLCountry_id,
					da.KLRGN_id as DKLRGN_id,
					da.KLSubRGN_id as DKLSubRGN_id,
					da.KLCity_id as DKLCity_id,
					da.KLTown_id as DKLTown_id,
					da.KLStreet_id as DKLStreet_id,
					da.Address_House as DAddress_House,
					da.Address_Corpus as DAddress_Corpus,
					da.Address_Flat as DAddress_Flat,
					da.Address_Address as DAddress_Address,
					da.Address_Address as DAddress_AddressText,
					d.DeputyKind_id,
					d.PntDeathSvid_ActNumber,
					convert(varchar,d.PntDeathSvid_ActDT,104) as PntDeathSvid_ActDT,
					OH.OrgHead_id,
					d.Org_id,
					d.PntDeathSvid_ZagsFIO,
					d.PntDeathSvid_IsFromMother,
					d.PntDeathSvidType_id,
					pi.PersonInfo_IsParsDeath,
					pi.PersonInfo_IsSetDeath
				FROM
					v_PntDeathSvid d with (nolock)
					left join v_Person_ER rp with (nolock) on rp.Person_id = d.Person_rid
					left outer join Address da with (nolock) on da.Address_id = d.Address_did
					left join v_PersonInfo pi with(nolock) on pi.Person_id = d.Person_id
					left join v_OrgHead OH with(nolock) on OH.Person_id = d.Person_hid
				WHERE (1 = 1)
					AND PntDeathSvid_id = :svid_id
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
				SELECT TOP 1
					d.Server_id,
					d.Server_id as MSBE_Server_id,
					d.BirthSvid_id,
					d.Lpu_id,
					d.Person_id,
					d.Person_id as MSBE_Person_id,
					d.Person_rid,
					isnull(rtrim(p.Person_Surname)+' ','') + isnull(rtrim(p.Person_Firname)+' ','') + isnull(rtrim(p.Person_Secname),'') as Person_FIO,
					convert(varchar,p.Person_BirthDay,104) as Person_BirthDay,
					isnull(rtrim(rp.Person_Surname)+' ','') + isnull(rtrim(rp.Person_Firname)+' ','') + isnull(rtrim(rp.Person_Secname),'') as birthsvid_PolFio,
					d.BirthSvid_Ser,
					d.BirthSvid_Num,
					d.MedStaffFact_id,
					d.MedPersonal_id,
					d.BirthMedPersonalType_id,
					d.ReceptType_id,
					d.BirthEducation_id,
					be.BirthEducation_Code,
					d.BirthPlace_id,
					d.Sex_id as BirthSex_id,
					d.BirthSvid_Week,
					d.BirthSvid_ChildCount,
					bfs.BirthFamilyStatus_Code,
					bfs.BirthFamilyStatus_Name,
					d.BirthFamilyStatus_id,
					d.BirthEmployment_id as BirthZanat_id,
					d.BirthSpecialist_id,
					d.BirthSvid_ChildFamil,
					d.BirthSvid_IsMnogoplod,
					d.BirthSvid_PlodIndex,
					d.BirthSvid_PlodCount,
					d.BirthSvid_IsFromMother,
					d.BirthSvid_Height,
					d.BirthSvid_Mass,
					d.BirthChildResult_id,
					ba.KLCountry_id as BKLCountry_id,
					ba.KLRGN_Name as BKLRGN_Name,
					ba.KLSubRGN_Name as BKLSubRGN_Name,
					ba.KLCity_Name as BKLCity_Name,
					ba.KLTown_Name as BKLTown_Name,
					ba.KlareaType_id as BKlareaType_id,
					ba.KLSubRGN_id as BKLSubRGN_id,
					ba.KLCity_id as BKLCity_id,
					ba.KLTown_id as BKLTown_id,
					ba.KLStreet_id as BKLStreet_id,
					ba.Address_House as BAddress_House,
					ba.Address_Corpus as BAddress_Corpus,
					ba.Address_Flat as BAddress_Flat,
					(ISNULL(ba.KLCity_Name,'') + ISNULL(case when ba.KLCity_Name is not null then ', ' else '' end + ISNULL(ba.KLTown_Socr+'. ','') + ba.KLTown_Name,'')) as BKLAddress_Summ,
					pa.KLRGN_Name,
					pa.KLSubRGN_Name,
					pa.KLCity_Name,
					pa.KLTown_Name,
					(pa.KLStreet_name + ' ' + case when pa.KLStreet_Socr = 'УЛ' then '' else pa.KLStreet_Socr end) as KLStreet_name,
					pa.Address_House,
					pa.Address_Flat,
					pa.KlareaType_id,
					(ISNULL(pa.KLCity_Name,'') + ISNULL(case when pa.KLCity_Name is not null then ', ' else '' end + ISNULL(pa.KLTown_Socr+'. ','') + pa.KLTown_Name,'')) as KLAddress_Summ,
					l.Lpu_Name,
					(ll.LpuLicence_Ser + ' ' + ll.LpuLicence_Num) as LpuLicence_Num,
					l.UAddress_Address as orgaddress_uaddress,
					l.Org_OKPO as org_okpo,
					(isnull(ohp.Person_SurName+' ','') + isnull(ohp.Person_FirName+' ','') + isnull(ohp.Person_SecName,'')) as OrgHead_Fio,
					isnull(post.PostMed_Name, '') as MedPersonal_Post,
					mp.Person_Fio as MedPersonal_FIO,
					convert(varchar,d.BirthSvid_GiveDate,104) as BirthSvid_GiveDate,
					convert(varchar,d.BirthSvid_BirthDT,104) as BirthSvid_BirthDate,
					substring(convert(varchar,d.BirthSvid_BirthDT,108),1,5) as BirthSvid_BirthDT_Time,
					convert(varchar,d.BirthSvid_RcpDate,104) as BirthSvid_RcpDate,
					d.BirthSvid_RcpDocument,
					d.BirthSvid_IsBad,
					dk.DeputyKind_Name,
					d.Okei_mid,
					(select Okei_NationSymbol from v_Okei o with(nolock) WHERE o.Okei_id = d.Okei_mid) as Okei_mid_Okei_NationSymbol,
					LLic.LpuLicence_Num,
					convert(varchar,LLic.LpuLicence_setDate,104) as LpuLicence_setDate
				FROM
					BirthSvid d with (nolock)
					left join v_Person_ER p with (nolock) on p.Person_id = d.Person_id
					left join DeputyKind dk with (nolock) on dk.DeputyKind_id = d.DeputyKind_id
					left join LpuLicence LLic with (nolock) on LLic.LpuLicence_id = d.LpuLicence_id
					left outer join v_Person_ER rp with (nolock) on rp.Person_id = d.Person_rid
					left outer join v_Address_all ba with (nolock) on ba.Address_id = d.Address_rid
					left outer join v_Address_all pa with (nolock) on pa.Address_id = p.UAddress_id
					left outer join v_Lpu_all l with (nolock) on l.Lpu_id = d.Lpu_id
					left outer join v_MedPersonal mp with (nolock) on mp.MedPersonal_id = d.MedPersonal_id
					left outer join v_MedStaffFact msf with (nolock) on msf.MedStaffFact_id = d.MedStaffFact_id
					left outer join v_PostMed post with (nolock) on post.PostMed_id = msf.Post_id
					left outer join v_BirthEducation be with (nolock) on be.BirthEducation_id = d.BirthEducation_id
					left outer join v_BirthFamilyStatus bfs with (nolock) on bfs.BirthFamilyStatus_id = d.BirthFamilyStatus_id
					left join v_OrgHead oh with (nolock) on oh.OrgHead_id = d.OrgHead_id
					left outer join v_PersonState ohp with (nolock) on ohp.Person_id = oh.Person_id
					outer apply (select top 1 * from LpuLicence with (nolock)
									where Lpu_id =l.lpu_id and( LpuLicence_endDate>d.BirthSvid_BirthDT
									or LpuLicence_endDate is null)) as ll
				WHERE (1 = 1)
					AND BirthSvid_id = :svid_id";

			$result = $this->db->query($query, array('svid_id' => $svid_id));
		}
		
		if ($svid_type == "death") {
			$query = "
				SELECT TOP 1
					isnull(rtrim(p.Person_Surname)+' ','') + isnull(rtrim(p.Person_Firname)+' ','') + isnull(rtrim(p.Person_Secname),'') as Person_FIO,
					p.Sex_id as PersonSex_id,
					ISNULL(convert(varchar,p.Person_BirthDay,104), d.DeathSvid_BirthDateStr) as Person_BirthDay,
					d.Server_id,
					d.Server_id as MSDE_Server_id,
					d.DeathSvid_id,
					d.Lpu_id,
					d.Person_id,
					d.Person_id as MSDE_Person_id,
					d.Person_mid,
					rtrim(mp.Person_Surname) + ' ' + rtrim(mp.Person_Firname) + ' ' + rtrim(mp.Person_Secname) as DeathSvid_MotherFio,
					rtrim(mp.Person_Surname) as DeathSvid_MotherFamaly,
					rtrim(mp.Person_Firname) as DeathSvid_MotherName,
					rtrim(mp.Person_Secname) as DeathSvid_MotherSecName,
					convert(varchar,mp.Person_BirthDay,104) as DeathSvid_MotherBirthday,
					d.Person_rid,
					rtrim(rp.Person_Surname) + ' ' + rtrim(rp.Person_Firname) + ' ' + rtrim(rp.Person_Secname) as DeathSvid_PolFio,
					d.MedStaffFact_id,
					d.MedPersonal_id,
					d.DeathSvidType_id,
					d.DeathSvid_IsDuplicate,
					d.DeathSvid_IsLose,
					d.ReceptType_id,
					d.DeathCause_id,
					d.DeathPlace_id,
					dp.DeathPlace_Code,
					de.DeathEducation_Code,
					d.DeathTrauma_id,
					d.DeathSetType_id,
					d.DeathSetCause_id,
					d.DeathWomanType_id,
					d.DeathEmployment_id as DeathZanat_id,
					dfs.DeathFamilyStatus_Code,
					dfs.DeathFamilyStatus_Name,
					d.DtpDeathTime_id as DeathDtpType_id,
					d.ChildTermType_id as DonosType_id,
					ba.Address_Zip as BAddress_Zip,
					ba.KLCountry_id as BKLCountry_id,
					ba.KLRGN_Name as BKLRGN_Name,
					ba.KLSubRGN_Name as BKLSubRGN_Name,
					ba.KLCity_Name as BKLCity_Name,
					ba.KLTown_Name as BKLTown_Name,
					(ba.KLStreet_name + ' ' + case when ba.KLStreet_Socr = 'УЛ' then '' else ba.KLStreet_Socr end) as BKLStreet_name,
					ba.KlareaType_id as BKlareaType_id,
					ba.Address_House as BAddress_House,
					ba.Address_Corpus as BAddress_Corpus,
					ba.Address_Flat as BAddress_Flat,
					ba.Address_Address as BAddress_Address,
					(ISNULL(ba.KLCity_Name,'') + ISNULL(case when ba.KLCity_Name is not null then ', ' else '' end + ISNULL(ba.KLTown_Socr+'. ','') + ba.KLTown_Name,'')) as BKLAddress_Summ,
					da.Address_Zip as DAddress_Zip,
					da.KLCountry_id as DKLCountry_id,
					da.KLRGN_Name as DKLRGN_Name,
					da.KLSubRGN_Name as DKLSubRGN_Name,
					da.KLCity_Name as DKLCity_Name,
					da.KLTown_Name as DKLTown_Name,
					(da.KLStreet_name + ' ' + case when da.KLStreet_Socr = 'УЛ' then '' else da.KLStreet_Socr end) as DKLStreet_name,
					da.KlareaType_id as DKlareaType_id,
					da.Address_House as DAddress_House,
					da.Address_Corpus as DAddress_Corpus,
					da.Address_Flat as DAddress_Flat,
					da.Address_Address as DAddress_Address,
					(ISNULL(da.KLCity_Name,'') + ISNULL(case when da.KLCity_Name is not null then ', ' else '' end + ISNULL(da.KLTown_Socr+'. ','') + da.KLTown_Name,'')) as DKLAddress_Summ,
					pa.Address_Zip as Address_Zip,
					pa.KLCountry_id as KLCountry_id,
					pa.KLRGN_Name as KLRGN_Name,
					pa.KLSubRGN_Name as KLSubRGN_Name,
					pa.KLCity_Name as KLCity_Name,
					pa.KLTown_Name as KLTown_Name,
					(pa.KLStreet_name + ' ' + case when pa.KLStreet_Socr = 'УЛ' then '' else pa.KLStreet_Socr end) as KLStreet_name,
					pa.KlareaType_id as KlareaType_id,
					pa.Address_House as Address_House,
					pa.Address_Corpus as Address_Corpus,
					pa.Address_Flat as Address_Flat,
					pa.Address_Address as Address_Address,
					(ISNULL(pa.KLCity_Name,'') + ISNULL(case when pa.KLCity_Name is not null then ', ' else '' end + ISNULL(pa.KLTown_Socr+'. ','') + pa.KLTown_Name,'')) as KLAddress_Summ,
					d.Diag_iid,
					d.Diag_eid,
					d.Diag_mid,
					d.Diag_tid,
					d.Diag_oid,
					d.DeathSvid_Ser,
					d.DeathSvid_Num,
					d.DeathSvid_OldSer,
					d.DeathSvid_OldNum,
					convert(varchar,d.DeathSvid_GiveDate,104) as DeathSvid_GiveDate,
					convert(varchar,d.DeathSvid_OldGiveDate,104) as DeathSvid_disDate,
					convert(varchar,d.DeathSvid_DeathDate,104) as DeathSvid_DeathDate_Date,
					ISNULL(CONVERT(varchar(10), d.DeathSvid_DeathDate, 104), d.DeathSvid_DeathDateStr) as DeathSvid_DeathDate_Date,
					substring(convert(varchar,d.DeathSvid_DeathDate,108),1,5) as DeathSvid_DeathDate_Time,
					--convert(varchar,d.DeathSvid_DeathDate,108) as DeathSvid_DeathDate_Time,
					d.DeathSvid_IsTerm,
					d.DeathSvid_Mass,
					d.DeathSvid_Month,
					d.DeathSvid_Day,
					d.DeathSvid_ChildCount,
					convert(varchar,d.DeathSvid_TraumaDate,104) as DeathSvid_TraumaDate_Date,
					substring(convert(varchar,d.DeathSvid_TraumaDate,108),1,5) as DeathSvid_TraumaDate_Time,
					d.DeathSvid_TraumaDescr as DeathSvid_TraumaObst,
					d.DeathSvid_Oper,
					d.DeathSvid_PribPeriod,
					d.DeathSvid_PribPeriodPat,
					d.DeathSvid_PribPeriodDom,
					d.DeathSvid_PribPeriodExt,
					d.DeathSvid_PribPeriodImp,
					d.DeathSvid_TimePeriod,
					d.DeathSvid_TimePeriodPat,
					d.DeathSvid_TimePeriodDom,
					d.DeathSvid_TimePeriodExt,
					d.DeathSvid_TimePeriodImp,
					convert(varchar,d.DeathSvid_RcpDate,104) as DeathSvid_PolDate,
					d.DeathSvid_RcpDocument as DeathSvid_PolDoc,
					d.DeathSvid_IsBad,
					d1.Diag_Name as Diag1_Name,
					d2.Diag_Name as Diag2_Name,
					d3.Diag_Name as Diag3_Name,
					d4.Diag_Name as Diag4_Name,
					d5.Diag_Name as Diag5_Name,
					d1.Diag_Code as Diag1_Code,
					d2.Diag_Code as Diag2_Code,
					d3.Diag_Code as Diag3_Code,
					d4.Diag_Code as Diag4_Code,
					d5.Diag_Code as Diag5_Code,
					l.Lpu_Name,
					l.UAddress_Address as orgaddress_uaddress,
					l.Org_OKPO as org_okpo,	
					--(isnull(ohp.Person_SurName+' ','') + isnull(ohp.Person_FirName+' ','') + isnull(ohp.Person_SecName,'')) as glvrach,
					isnull(post.PostMed_Name, '') as MedPersonal_Post,
					medp.Person_Fio as MedPersonal_FIO,
					dst.DeathSvidType_Name,
					case
						when d.Person_hid is null then ''
						else (ohpers.Person_SurName+' '+ohpers.Person_FirName+' '+isnull(ohpers.Person_SecName,''))
					end as OrgHead_Fio,
					case when d.OrgHeadPost_id is null then '' else ohpost.OrgHeadPost_Name end as OrgHeadPost_Name
				FROM
					v_DeathSvid d with (nolock)
					left join v_Person_ER p with (nolock) on p.Person_id = d.Person_id
					left outer join v_Person_ER mp with (nolock) on mp.Person_id = d.Person_mid
					left outer join v_Person_ER rp with (nolock) on rp.Person_id = d.Person_rid
					left outer join v_Address_all ba with (nolock) on ba.Address_id = d.Address_bid
					left outer join v_Address_all da with (nolock) on da.Address_id = d.Address_did
					left outer join v_Address_all pa with (nolock) on pa.Address_id = p.UAddress_id
					left outer join Diag d1 with (nolock) on d1.Diag_id = d.Diag_iid
					left outer join Diag d2 with (nolock) on d2.Diag_id = d.Diag_tid
					left outer join Diag d3 with (nolock) on d3.Diag_id = d.Diag_mid
					left outer join Diag d4 with (nolock) on d4.Diag_id = d.Diag_eid
					left outer join Diag d5 with (nolock) on d5.Diag_id = d.Diag_oid
					left outer join v_DeathEducation de with (nolock) on de.DeathEducation_id = d.DeathEducation_id
					left outer join v_DeathFamilyStatus dfs with (nolock) on dfs.DeathFamilyStatus_id = d.DeathFamilyStatus_id
					left outer join v_MedPersonal medp with (nolock) on medp.MedPersonal_id = d.MedPersonal_id
					left outer join v_MedStaffFact msf with (nolock) on msf.MedStaffFact_id = d.MedStaffFact_id
					left outer join v_PostMed post with (nolock) on post.PostMed_id = msf.Post_id
					left outer join v_Lpu_all l with (nolock) on l.Lpu_id = d.Lpu_id
					left outer join DeathSvidType dst with (nolock) on dst.DeathSvidType_id = d.DeathSvidType_id
					left outer join DeathPlace dp with (nolock) on dp.DeathPlace_id = d.DeathPlace_id
					/*left join v_OrgHead oh with (nolock) on oh.Lpu_id = d.Lpu_id and oh.OrgHeadPost_id = 1
					left outer join v_PersonState ohp with (nolock) on ohp.Person_id = oh.Person_id*/
					left join v_PersonState ohpers with(nolock) on ohpers.Person_id = d.Person_hid
					left join v_OrgHeadPost ohpost with(nolock) on ohpost.OrgHeadPost_id = d.OrgHeadPost_id
				WHERE (1 = 1)
					AND DeathSvid_id = :svid_id";

			$result = $this->db->query($query, array('svid_id' => $svid_id));
		}

		if ($svid_type == "pntdeath") {
			$query = "
				SELECT TOP 1
					isnull(rtrim(p.Person_Surname)+' ','') + isnull(rtrim(p.Person_Firname)+' ','') + isnull(rtrim(p.Person_Secname),'') as Person_FIO,
					d.Sex_id,
					(case
						when pdgb.PntDeathGetBirth_Code = 1 then 1
						when pdgb.PntDeathGetBirth_Code in (2,3) then 2
						when pdgb.PntDeathGetBirth_Code = 4 then 3
					end) as PntDeathGetBirth_Code,
					d.PntDeathSvid_PlodIndex,
					d.PntDeathSvid_PlodCount,
					d.PntDeathTime_id,
					d.PntDeathSvid_ChildFio,
					convert(varchar,p.Person_BirthDay,104) as Person_BirthDay,
					ISNULL(convert(varchar,d.PntDeathSvid_ChildBirthDT,104), d.PntDeathSvid_DeathDateStr) as PntDeathSvid_ChildBirthDT,
					substring(convert(varchar,d.PntDeathSvid_ChildBirthDT,108),1,5) as PntDeathSvid_ChildBirthDT_Time,
					d.Server_id,
					d.Server_id as MSDE_Server_id,
					d.PntDeathSvid_id,
					d.Lpu_id,
					d.Person_id,
					d.Person_id as MSDE_Person_id,
					d.Person_rid,
					isnull(rtrim(rp.Person_Surname)+' ','') + isnull(rtrim(rp.Person_Firname)+' ','') + isnull(rtrim(rp.Person_Secname),'') as PntDeathSvid_PolFio,
					d.MedStaffFact_id,
					d.MedPersonal_id,
					d.DeathSvidType_id,
					d.ReceptType_id,
					d.PntDeathCause_id,
					pdfs.PntDeathFamilyStatus_Code,
					pdfs.PntDeathFamilyStatus_Name,
					d.PntDeathPlace_id,
					pde.PntDeathEducation_Code,
					d.PntDeathSetType_id,
					d.PntDeathSetCause_id,
					d.PntDeathCause_id as PntDeathBirthCause_id,
					coalesce(b.BirthPlace_id, d.PntDeathPlace_id) as BirthPlace_id,
					d.DeathEmployment_id as PntDeathZanat_id,
					da.Address_Zip as DAddress_Zip,
					da.KLCountry_id as DKLCountry_id,
					da.KLRGN_Name as DKLRGN_Name,
					da.KLSubRGN_Name as DKLSubRGN_Name,
					da.KLCity_Name as DKLCity_Name,
					da.KLTown_Name as DKLTown_Name,
					(da.KLStreet_name + ' ' + case when da.KLStreet_Socr = 'УЛ' then '' else da.KLStreet_Socr end) as DKLStreet_name,
					da.KlareaType_id as DKlareaType_id,
					da.Address_House as DAddress_House,
					da.Address_Corpus as DAddress_Corpus,
					da.Address_Flat as DAddress_Flat,
					da.Address_Address as DAddress_Address,
					(ISNULL(da.KLCity_Name,'') + ISNULL(case when da.KLCity_Name is not null then ', ' else '' end + ISNULL(da.KLTown_Socr+'. ','') + da.KLTown_Name,'')) as DKLAddress_Summ,
					pa.Address_Zip as Address_Zip,
					pa.KLCountry_id as KLCountry_id,
					pa.KLRGN_Name as KLRGN_Name,
					pa.KLSubRGN_Name as KLSubRGN_Name,
					pa.KLCity_Name as KLCity_Name,
					pa.KLTown_Name as KLTown_Name,
					(pa.KLStreet_name + ' ' + case when pa.KLStreet_Socr = 'УЛ' then '' else pa.KLStreet_Socr end) as KLStreet_name,
					pa.KlareaType_id as KlareaType_id,
					pa.Address_House as Address_House,
					pa.Address_Corpus as Address_Corpus,
					pa.Address_Flat as Address_Flat,
					pa.Address_Address as Address_Address,
					(ISNULL(pa.KLCity_Name,'') + ISNULL(case when pa.KLCity_Name is not null then ', ' else '' end + ISNULL(pa.KLTown_Socr+'. ','') + pa.KLTown_Name,'')) as KLAddress_Summ,
					d.Diag_iid,
					d.Diag_eid,
					d.Diag_mid,
					d.Diag_tid,
					d.Diag_oid,
					d.PntDeathSvid_Ser,
					d.PntDeathSvid_Num,
					d.PntDeathSvid_OldSer,
					d.PntDeathSvid_OldNum,
					convert(varchar,d.PntDeathSvid_GiveDate,104) as PntDeathSvid_GiveDate,
					convert(varchar,d.PntDeathSvid_OldGiveDate,104) as PntDeathSvid_disDate,
					ISNULL(convert(varchar,d.PntDeathSvid_DeathDate,104), d.PntDeathSvid_DeathDateStr) as PntDeathSvid_DeathDate,
					substring(convert(varchar,d.PntDeathSvid_DeathDate,108),1,5) as PntDeathSvid_DeathDate_Time,
					d.PntDeathSvid_Mass,
					d.PntDeathSvid_Height,
					d.PntDeathSvid_ChildCount,
					d.PntDeathSvid_IsMnogoplod,
					convert(varchar,d.PntDeathSvid_RcpDate,104) as PntDeathSvid_PolDate,
					d.PntDeathSvid_RcpDoc as PntDeathSvid_PolDoc,
					d.PntDeathSvid_IsBad,
					d1.Diag_Name as Diag1_Name,
					d2.Diag_Name as Diag2_Name,
					d3.Diag_Name as Diag3_Name,
					d4.Diag_Name as Diag4_Name,
					d5.Diag_Name as Diag5_Name,
					d1.Diag_Code as Diag1_Code,
					d2.Diag_Code as Diag2_Code,
					d3.Diag_Code as Diag3_Code,
					d4.Diag_Code as Diag4_Code,
					d5.Diag_Code as Diag5_Code,
					l.Lpu_Name,
					l.UAddress_Address as orgaddress_uaddress,
					l.Org_OKPO as org_okpo,
					--(isnull(ohp.Person_SurName+' ','') + isnull(ohp.Person_FirName+' ','') + isnull(ohp.Person_SecName,'')) as glvrach,
					isnull(post.PostMed_Name, '') as MedPersonal_Post,
					medp.Person_Fio as MedPersonal_FIO,
					dst.DeathSvidType_Name,
					dk.DeputyKind_Name,
					d.PntDeathSvid_ActNumber,
					convert(varchar,d.PntDeathSvid_ActDT,104) as PntDeathSvid_ActDT,
					d.Org_id,
					o.Org_Name,
					d.PntDeathSvid_ZagsFIO,
					d.PntDeathSvid_IsFromMother,
					case
						when d.Person_hid is null then ''
						else (ohpers.Person_SurName+' '+ohpers.Person_FirName+' '+isnull(ohpers.Person_SecName,''))
					end as OrgHead_Fio,
					case when d.OrgHeadPost_id is null then '' else ohpost.OrgHeadPost_Name end as OrgHeadPost_Name
				FROM
					v_PntDeathSvid d with (nolock)
					left join v_Person_ER p with (nolock) on p.Person_id = d.Person_id
					outer apply (
						select top 1
							BirthPlace_id
						from
							BirthSvid with(nolock)
						where
							Person_id = p.Person_id
							and DATEDIFF(\"d\", BirthSvid_BirthDT, d.PntDeathSvid_DeathDate) <= 7
					) as b
					left join DeputyKind dk with (nolock) on dk.DeputyKind_id = d.DeputyKind_id
					left outer join v_Person_ER rp with (nolock) on rp.Person_id = d.Person_rid
					left outer join v_Address_all da with (nolock) on da.Address_id = d.Address_did
					left outer join v_Address_all pa with (nolock) on pa.Address_id = p.UAddress_id
					left outer join Diag d1 with (nolock) on d1.Diag_id = d.Diag_iid
					left outer join Diag d2 with (nolock) on d2.Diag_id = d.Diag_tid
					left outer join Diag d3 with (nolock) on d3.Diag_id = d.Diag_mid
					left outer join Diag d4 with (nolock) on d4.Diag_id = d.Diag_eid
					left outer join Diag d5 with (nolock) on d5.Diag_id = d.Diag_oid
					left outer join v_PntDeathEducation pde with (nolock) on pde.PntDeathEducation_id = d.PntDeathEducation_id
					left outer join v_PntDeathFamilyStatus pdfs with (nolock) on pdfs.PntDeathFamilyStatus_id = d.PntDeathFamilyStatus_id
					left outer join v_MedPersonal medp with (nolock) on medp.MedPersonal_id = d.MedPersonal_id
					left outer join v_MedStaffFact msf with (nolock) on msf.MedStaffFact_id = d.MedStaffFact_id
					left outer join v_PostMed post with (nolock) on post.PostMed_id = msf.Post_id
					left outer join v_Lpu_all l with (nolock) on l.Lpu_id = d.Lpu_id
					left outer join DeathSvidType dst with (nolock) on dst.DeathSvidType_id = d.DeathSvidType_id
					--left join v_OrgHead oh with (nolock) on oh.OrgHead_id = d.OrgHead_id
					--left outer join v_PersonState ohp with (nolock) on ohp.Person_id = oh.Person_id
					left join v_OrgDep o with (nolock) on o.OrgDep_id = d.Org_id
					left join v_PntDeathGetBirth pdgb with (nolock) on pdgb.PntDeathGetBirth_id = d.PntDeathGetBirth_id
					left join v_PersonState ohpers with(nolock) on ohpers.Person_id = d.Person_hid
					left join v_OrgHeadPost ohpost with(nolock) on ohpost.OrgHeadPost_id = d.OrgHeadPost_id
				WHERE (1 = 1)
					AND PntDeathSvid_id = :svid_id";

			$result = $this->db->query($query, array('svid_id' => $svid_id));
		}
		
		if ( is_object($result) ) {
			$res = $result->result('array');
			$glav = '';
			//ищем руководителя
			$lpu_id = ($res[0]['Lpu_id']);

			if (!in_array($svid_type,array('birth')) && empty($res[0]['OrgHead_Fio'])) {
				$query = "
					SELECT TOP 1
						(isnull(ohp.Person_SurName+' ','') + isnull(ohp.Person_FirName+' ','') + isnull(ohp.Person_SecName,'')) as OrgHead_Fio
					FROM
						v_OrgHead oh with (nolock)
						inner join v_PersonState ohp with (nolock) on ohp.Person_id = oh.Person_id
					WHERE (1 = 1)
						and oh.Lpu_id = :Lpu_id
						and oh.OrgHeadPost_id = 1
						and LpuUnit_id is null
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
			$sql = "select isnull(Lpu_Ouz, 1) Lpu_Ouz from v_Lpu where Lpu_id = :lpu_id";
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
			select count(BirthSvid_id) as cnt
			from v_BirthSvid with (nolock)
			where Person_id = :Person_id
				and ISNULL(BirthSvid_IsBad, 1) = 1
				and ISNULL(BirthSvid_id, 0) != :BirthSvid_id
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
			select top 1
				bs.BirthSvid_id,
				bs.BirthSvid_Ser,
				bs.BirthSvid_Num
			from
				v_BirthSvid bs (nolock)
			where
				bs.BirthSvid_pid = :BirthSvid_pid
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
			SELECT top 1
				av.AttributeValue_id
			FROM
				v_VolumeType vt (nolock)
				inner join v_AttributeVision avis (nolock) on avis.AttributeVision_TableName = 'dbo.VolumeType' and avis.AttributeVision_TablePKey = vt.VolumeType_id 
				inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
			WHERE
				av.AttributeValue_ValueIdent = :Lpu_id
				and avis.AttributeVision_IsKeyValue = 2
				and vt.VolumeType_Code = '2020-МСС_COVID'
				and COALESCE(av.AttributeValue_begDate, :onDate) <= :onDate
				and COALESCE(av.AttributeValue_endDate, :onDate) >= :onDate
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
			select top 1
				ds.DeathSvid_id,
				ds.DeathSvid_Ser,
				ds.DeathSvid_Num,
				substring(ps.Person_SurName, 1, 1) as Person_SurName,
				ps.Person_FirName,
				ps.Person_SecName
			from
				v_DeathSvid ds (nolock)
				inner join v_PersonState ps (nolock) on ps.Person_id = ds.Person_rid
			where
				{$filter_ds}
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
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec p_{$tab_name}_del
				@{$tab_name}_id = :svid_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @Error_Code as Error_Code, @Error_Message as Error_Message
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
            SELECT TOP 1
                Org_name
            FROM
                v_Org
            WHERE 
                Org_id = :Org_id
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
			SELECT TOP 1
				ba.Address_Zip as BAddress_Zip,
				ba.KLCountry_id as BKLCountry_id,
				ba.KLRGN_id as BKLRGN_id,
				ba.KLSubRGN_id as BKLSubRGN_id,
				ba.KLCity_id as BKLCity_id,
				ba.KLTown_id as BKLTown_id,
				ba.KLStreet_id as BKLStreet_id,
				ba.Address_House as BAddress_House,
				ba.Address_Corpus as BAddress_Corpus,
				ba.Address_Flat as BAddress_Flat,
				ba.Address_Address as BAddress_Address,
				ba.Address_Address as BAddress_AddressText
			FROM
				v_Lpu l with (nolock)
				inner join [Address] ba with (nolock) on ba.Address_id = l.PAddress_id
			WHERE l.Lpu_id = :Lpu_id
				and ba.KLRgn_id is not null
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
				DeathDiag_id,
				Diag_id,
				DeathDiag_IsLowChance,
				DeathDiag_IsNotUsed,
				DeathDiag_IsDiagIID,
				DeathDiag_IsDiagTID,
				DeathDiag_IsDiagMID,
				DeathDiag_IsDiagEID,
				DeathDiag_IsDiagOID,
				Sex_id,
				DeathDiag_YearFrom,
				DeathDiag_MonthFrom,
				DeathDiag_DayFrom,
				DeathDiag_YearTo,
				DeathDiag_MonthTo,
				DeathDiag_DayTo,
				DeathDiag_DiagChange,
				DeathDiag_Message,
				Region_id,
				case when DeathDiag_DayFrom is not null then 1 else 0 end
				+ case when DeathDiag_DayTo is not null then 1 else 0 end
				+ case when DeathDiag_MonthFrom is not null then 1 else 0 end
				+ case when DeathDiag_MonthTo is not null then 1 else 0 end
				+ case when DeathDiag_YearFrom is not null then 1 else 0 end
				+ case when DeathDiag_YearTo is not null then 1 else 0 end
				+ case when Sex_id is not null then 1 else 0 end as DeathDiag_CriteriaCount
			from
				v_DeathDiag (nolock)
		";

		return $this->queryResult($query);
	}

	/**
	 * Получение адреса рождения пациента
	 */
	function getPacientBAddress($data) {
		$query = "
			SELECT TOP 1
				baddr.Address_Zip as BAddress_Zip,
				baddr.KLCountry_id as BKLCountry_id,
				baddr.KLRGN_id as BKLRGN_id,
				baddr.KLSubRGN_id as BKLSubRGN_id,
				baddr.KLCity_id as BKLCity_id,
				baddr.KLTown_id as BKLTown_id,
				baddr.KLStreet_id as BKLStreet_id,
				baddr.Address_House as BAddress_House,
				baddr.Address_Corpus as BAddress_Corpus,
				baddr.Address_Flat as BAddress_Flat,
				baddr.Address_Address as BAddress_Address,
				baddr.Address_Address as BAddress_AddressText,
				1 as AddressFound,
				'' as Error_Msg
			FROM
				v_PersonState PS (nolock)
				left join PersonBirthPlace pbp with (nolock) on PS.Person_id = pbp.Person_id
				left join v_Address baddr with (nolock) on pbp.Address_id = baddr.Address_id
				left join v_AddressSpecObject baddrsp with (nolock) on baddr.AddressSpecObject_id = baddrsp.AddressSpecObject_id
			WHERE
				PS.Person_id = :Person_id
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
			SELECT TOP 1
				a.Address_Zip as BAddress_Zip,
				a.KLCountry_id as BKLCountry_id,
				a.KLRGN_id as BKLRGN_id,
				a.KLSubRGN_id as BKLSubRGN_id,
				a.KLCity_id as BKLCity_id,
				a.KLTown_id as BKLTown_id,
				a.KLStreet_id as BKLStreet_id,
				a.Address_House as BAddress_House,
				a.Address_Corpus as BAddress_Corpus,
				a.Address_Flat as BAddress_Flat,
				a.Address_Address as BAddress_Address,
				a.Address_Address as BAddress_AddressText,
				1 as AddressFound,
				'' as Error_Msg
			FROM
				v_PersonState PS (nolock)
				inner join v_Address a (nolock) on a.Address_id = ps.UAddress_id
			WHERE
				PS.Person_id = :Person_id
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
			SELECT TOP 1
				a.Address_Zip as BAddress_Zip,
				a.KLCountry_id as BKLCountry_id,
				a.KLRGN_id as BKLRGN_id,
				a.KLSubRGN_id as BKLSubRGN_id,
				a.KLCity_id as BKLCity_id,
				a.KLTown_id as BKLTown_id,
				a.KLStreet_id as BKLStreet_id,
				a.Address_House as BAddress_House,
				a.Address_Corpus as BAddress_Corpus,
				a.Address_Flat as BAddress_Flat,
				a.Address_Address as BAddress_Address,
				a.Address_Address as BAddress_AddressText,
				1 as AddressFound,
				'' as Error_Msg
			FROM
				v_EvnPS eps (nolock)
				inner join v_LeaveType lt (nolock) on lt.LeaveType_id = eps.LeaveType_id
				inner join v_LpuSection ls (nolock) on ls.LpuSection_id = eps.LpuSection_id
				inner join v_LpuUnit lu (nolock) on lu.LpuUnit_id = ls.LpuUnit_id
				left join v_LpuBuilding lb (nolock) on lb.LpuBuilding_id = lu.LpuBuilding_id
				inner join v_Address a (nolock) on a.Address_id = ISNULL(lu.Address_id, lb.Address_id)
			WHERE
				eps.Person_id = :Person_id
				and lt.LeaveType_SysNick IN ('die', 'dsdie', 'ksdie')
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
				 DS.DeathSvid_id
				,RTRIM(ISNULL(DS.DeathSvid_Num, '')) + ', выдано ' + convert(varchar(10), DS.DeathSvid_GiveDate, 104) as DeathSvid_Num
				,convert(varchar(10), DS.DeathSvid_GiveDate, 104) as DeathSvid_GiveDate
			from
				v_DeathSvid DS with (nolock)
			where " . $filter . "
			    and ISNULL(DS.DeathSvid_IsActual, 2) = 2
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
			SELECT TOP 1
				pc.PersonChild_CountChild,
				pc.ChildTermType_id,
				case when pw.Okei_id = 36 then
					PersonWeight_Weight
				else
					pw.PersonWeight_Weight * 1000
				end as PersonWeight_Weight
			FROM
				PersonChild pc with (nolock)
				outer apply (
					select top 1
						PersonWeight_Weight,
						Okei_id
					from
						PersonWeight with (nolock)
					where
						Person_id = pc.Person_id
					order by
						PersonWeight_setDT desc
				) pw
			WHERE (1 = 1)
				AND pc.Person_id = :Person_id";
				
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
			declare
				@Error_Code int,
				@Error_Message varchar(4000);
			set nocount on
			begin try
				update {$object} with (rowlock)
				set {$object}_Num = :MedSvid_Num
				where {$object}_id = :MedSvid_id
			end try
			begin catch
				set @Error_Code = error_number()
				set @Error_Message = error_message()
			end catch
			select @Error_Code as Error_Code, @Error_Message as Error_Msg
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
				bs.BirthSvid_id,
				bs.BirthSvid_Ser,
				bs.BirthSvid_Num,
				CONVERT(varchar(10), bs.BirthSvid_GiveDate, 120) as BirthSvid_GiveDate
			from
				BirthSvid bs with (NOLOCK)
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
				*
			from
				BirthSvid bs with (NOLOCK)
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
				bs.BirthSvid_id,
				bs.ReceptType_id,
				bs.BirthSvid_Ser,
				bs.BirthSvid_Num,
				CONVERT(varchar(10), bs.BirthSvid_GiveDate, 120) as BirthSvid_GiveDate,
				bs.BirthEmployment_id,
				bs.BirthEducation_id,
				bs.BirthFamilyStatus_id,
				bs.LpuSection_id,
				bs.MedStaffFact_id,
				bs.BirthMedPersonalType_id,
				bs.OrgHead_id,
				bs.LpuLicence_id,
				CONVERT(varchar(19), bs.BirthSvid_BirthDT, 120) as BirthSvid_BirthDT_Date,
				bs.BirthPlace_id,
				bs.BirthSpecialist_id,
				case when bs.BirthSvid_IsMnogoplod = 2 then 1 else 0 end as BirthSvid_IsMnogoplod,
				bs.BirthSvid_PlodIndex,
				bs.BirthSvid_PlodCount,
				bs.BirthChildResult_id,
				bs.BirthSvid_ChildCount,
				bs.BirthSvid_Week,
				bs.BirthSvid_Mass,
				bs.Okei_mid,
				bs.BirthSvid_Height,
				bs.Sex_id,
				bs.BirthSvid_ChildFamil,
				bs.Address_rid,
				bs.Person_rid,
				bs.BirthSvid_RcpDocument,
				bs.DeputyKind_id,
				CONVERT(varchar(10), bs.BirthSvid_RcpDate, 120) as BirthSvid_RcpDate,
				case when bs.BirthSvid_IsFromMother = 2 then 1 else 0 end as BirthSvid_IsFromMother
			from
				v_BirthSvid bs with (NOLOCK)
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
				else if ($data['DeathSvid_IsUnknownDeathDate'] == 2) {
					$data['DeathSvid_DeathDate'] = null;
				}

				// проставить дату окончания региональной льготы (refs #6201)
				$querypp = "UPDATE PersonPrivilege with (rowlock) SET PersonPrivilege_endDate = :endDate WHERE PersonPrivilege_endDate IS NULL AND Person_id = :Person_id AND exists((SELECT 1 FROM PrivilegeType with (nolock) WHERE PrivilegeType_id = PersonPrivilege.PrivilegeType_id and ReceptFinance_id=2))";
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
			
			$data['@pmUser_id'] = $data['pmUser_id'];
			$data[$tab_name.'_IsBad'] = 1;
			$data[$tab_name.'_IsLose'] = 1;
			$data[$tab_name.'_IsActual'] = $data[$tab_name.'_IsActual'] == 2 ? 2 : 1;
			if(empty($data[$tab_name.'_id'])){
				$param_array['pmUser_id'] = 'i';
				$query_parts .= "@{$tab_name}_id = @{$tab_name}_id output";
				$query_parts .= ", @Error_Code = @Error_Code output";
				$query_parts .= ", @Error_Message = @Error_Message output";

				foreach($param_array as $k => $v) { //формирование запроса
					$query_parts .= ", @$k = :$k";
					$sql_p_array[$k] = (!empty($data[$k]) ? $data[$k] : null);
				}
				$query .= str_replace(",*", "", "
				declare
					@{$tab_name}_id bigint,
					@Error_Code bigint,
					@Error_Message varchar(4000);
				exec p_{$tab_name}_ins
					{$query_parts}
				select @{$tab_name}_id as svid_id, @Error_Code as Error_Code, @Error_Message as Error_Message
				");

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
			} else {

				$query_parts .= "pmUser_updID = :pmUser_updID ";
				$sql_p_array['pmUser_updID'] = $data['pmUser_id'];
				$sql_p_array[$tab_name.'_id'] = $data[$tab_name.'_id'];

				foreach($param_array as $k => $v) { //формирование запроса
					if(!empty($data[$k])) {
						$query_parts .= ", $k = :$k";
						$sql_p_array[$k] = $data[$k];
					}else{
						continue;
					}

				}
				$query .= "
				update {$tab_name}
				set {$query_parts}
				where {$tab_name}_id = :{$tab_name}_id
				";
				//echo getDebugSQL($query, $sql_p_array);exit;
				if ($query != "") {
					$result = array('success' => false);
					$res = $this->db->query($query, $sql_p_array);

					if ($res == true) {
							$result = array('success' => true, 'svid_id' => $data["{$tab_name}_id"]);
					} else {
						throw new Exception("Ошибка при выполнении запроса к БД");
					}
				}
			}

			//#PROMEDWEB-7451
			if ($svid_type == 'death') {
				$this->load->model('Person_model');
				if ($data['DeathSvid_IsActual'] == 2) {
					$deathDate = array_key_exists('DeathSvid_IsUnknownDeathDate', $data) && ($data['DeathSvid_IsUnknownDeathDate'] == 2 || !empty($data['DeathSvid_DeathDateStr']))
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
				if ($data['DeathSvid_IsActual'] != 2) {
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
							PR.PersonRegister_id
						from v_PersonRegister PR (NOLOCK)
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
				bs.DeathSvid_id,
				bs.DeathSvid_Ser,
				bs.DeathSvid_Num,
				CONVERT(varchar(10), bs.DeathSvid_GiveDate, 120) as DeathSvid_GiveDate
			from
				DeathSvid bs with (NOLOCK)
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
				*
			from
				DeathSvid with (NOLOCK)
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
				bs.DeathSvid_id
				,bs.ReceptType_id
				,bs.DeathSvid_Ser
				,bs.DeathSvid_Num
				,CONVERT(varchar(10), bs.DeathSvid_GiveDate, 120) as DeathSvid_GiveDate
				,bs.DeathSvidType_id
				,bs.DeathSvid_OldSer
				,bs.DeathSvid_OldNum
				,CONVERT(varchar(10), bs.DeathSvid_OldGiveDate, 120) as DeathSvid_OldGiveDate
				,bs.DeathSvid_BirthDateStr
				,bs.DeathSvid_isBirthDate
				,CONVERT(varchar(10), bs.DeathSvid_DeathDate, 104) as DeathSvid_DeathDate_Date
				,CONVERT(varchar(10), bs.DeathSvid_DeathDate, 108) as DeathSvid_DeathDate_Time
				,bs.DeathSvid_DeathDateStr
				,case when bs.DeathSvid_DeathDate is null and bs.DeathSvid_DeathDateStr is null then 1 else 0 end as DeathSvid_isUnknownDeathDate
				,bs.DeathSvid_IsNoDeathTime as DeathSvid_isUnknownDeathTime
				,bs.LpuSection_id
				,bs.MedStaffFact_id
				,oh.OrgHead_id
				,bs.Person_mid
				,bs.Address_bid
				,bs.ChildTermType_id
				,bs.DeathSvid_Mass
				,bs.DeathSvid_ChildCount
				,bs.DeathSvid_Month
				,bs.DeathSvid_Day
				,datediff(YEAR, mp.Person_BirthDay, dbo.tzGetDate()) as Mother_Age
				,convert(varchar,mp.Person_BirthDay,104) as Mother_BirthDay
				,bs.DeathEmployment_id
				,bs.DeathEducation_id
				,bs.DeathPlace_id
				,bs.Address_did
				,bs.DeathSvid_IsNoPlace
				,bs.DeathFamilyStatus_id
				,bs.DeathCause_id
				,CONVERT(varchar(10), bs.DeathSvid_TraumaDate, 104) as DeathSvid_TraumaDate_Date
				,CONVERT(varchar(10), bs.DeathSvid_TraumaDate, 108) as DeathSvid_TraumaDate_Time
				,bs.DeathSvid_TraumaDateStr
				,bs.DeathTrauma_id
				,bs.DtpDeathTime_id
				,bs.DeathSvid_TraumaDescr
				,bs.DeathSetType_id
				,bs.DeathSetCause_id
				,bs.Diag_iid
				,bs.Diag_tid
				,bs.Diag_mid
				,bs.Diag_eid
				,bs.Diag_oid
				,bs.DeathSvid_Oper
				,bs.Person_rid
				,bs.DeathSvid_PolFio
				,bs.DeathSvid_RcpDocument
				,CONVERT(varchar(10), bs.DeathSvid_RcpDate, 120) as DeathSvid_RcpDate
			from
				v_DeathSvid bs with (NOLOCK)
				left join v_Person_ER mp with (nolock) on mp.Person_id = bs.Person_mid
				left join v_OrgHead oh with(nolock) on oh.Person_id = bs.Person_hid and oh.OrgHeadPost_id = bs.OrgHeadPost_id
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
				OrgHeadPost_id,
				Person_id
			from
				v_OrgHead with (NOLOCK)
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
				Org_id
			from
				v_OrgDep with (NOLOCK)
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
				bs.PntDeathSvid_id,
				bs.PntDeathSvid_Ser,
				bs.PntDeathSvid_Num,
				CONVERT(varchar(10), bs.PntDeathSvid_GiveDate, 120) as PntDeathSvid_GiveDate
			from
				PntDeathSvid bs with (NOLOCK)
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
				*
			from
				PntDeathSvid with (NOLOCK)
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
				bs.PntDeathSvid_id
				,bs.ReceptType_id
				,bs.PntDeathSvid_Ser
				,bs.PntDeathSvid_Num
				,CONVERT(varchar(10), bs.PntDeathSvid_GiveDate, 120) as PntDeathSvid_GiveDate
				,bs.DeathSvidType_id
				,bs.PntDeathSvid_OldSer
				,bs.PntDeathSvid_OldNum
				,CONVERT(varchar(10), bs.PntDeathSvid_OldGiveDate, 120) as PntDeathSvid_OldGiveDate
				,CONVERT(varchar(20), bs.PntDeathSvid_DeathDate, 120) as PntDeathSvid_DeathDate
				,bs.PntDeathSvid_DeathDateStr
				,bs.LpuSection_id
				,bs.MedStaffFact_id
				,bs.OrgHead_id
				,convert(varchar,bs.PntDeathSvid_ChildBirthDT,120) as PntDeathSvid_ChildBirthDT
				,bs.PntDeathSvid_BirthDateStr as PntDeathSvid_ChildBirthDateStr
				,bs.PntDeathTime_id
				,bs.PntDeathPeriod_id
				,bs.DeathEmployment_id
				,bs.PntDeathEducation_id
				,bs.PntDeathFamilyStatus_id
				,bs.PntDeathSvid_BirthCount
				,bs.PntDeathSvid_ChildCount
				,bs.PntDeathSvid_ChildFio
				,bs.PntDeathPlace_id
				,bs.Address_did
				,case when bs.PntDeathSvid_IsNoPlace = 2 then 1 else 0 end as PntDeathSvid_IsNoPlace
				,bs.Sex_id
				,bs.PntDeathGetBirth_id
				,bs.PntDeathSvid_Mass
				,bs.PntDeathSvid_Height
				,bs.PntDeathSvid_IsMnogoplod
				,bs.PntDeathSvid_PlodIndex
				,bs.PntDeathSvid_PlodCount
				,bs.PntDeathCause_id
				,bs.PntDeathSvid_ActNumber
				,convert(varchar(10),bs.PntDeathSvid_ActDT,120) as PntDeathSvid_ActDT
				,od.OrgDep_id
				,bs.PntDeathSvid_ZagsFIO
				,bs.PntDeathSvidType_id
				,bs.Diag_iid
				,bs.Diag_tid
				,bs.Diag_mid
				,bs.Diag_eid
				,bs.Diag_oid
				,bs.PntDeathSetType_id
				,bs.PntDeathSetCause_id
				,bs.Person_rid
				,bs.PntDeathSvid_PolFio
				,bs.PntDeathSvid_RcpDoc
				,convert(varchar(10),bs.PntDeathSvid_RcpDate,120) as PntDeathSvid_RcpDate
				,bs.DeputyKind_id
				,bs.PntDeathSvid_IsFromMother
			from
				v_PntDeathSvid bs with (NOLOCK)
				left join v_OrgDep od with(nolock) on od.Org_id = bs.Org_id
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
			select top 1
				bs.BirthSvid_id, /*идентификатор bigint*/
				convert(varchar(8), bs.BirthSvid_GiveDate, 112) as BirthSvid_GiveDate,
				LpuOID.PassportToken_tid,
				bs.Person_id,
				PS.Person_Snils,
				PS.Person_SurName,
				PS.Person_FirName,
				PS.Person_SecName,
				psr.Person_SurName as RPerson_SurName,
				psr.Person_FirName as RPerson_FirName,
				psr.Person_SecName as RPerson_SecName,
				s.Sex_Code,
				s.Sex_Name,
				ua.Address_Address as Address_Address,
				ua.KLRgn_id as KLRgn_id,
				ps.Person_Phone,
				VPI.PersonInfo_Email,
				L.Lpu_Nick,
				L.Lpu_Phone,
				OL.Org_Www as Lpu_Www,
				lua.Address_Address as LAddress_Address,
				lua.KLRgn_id as LKLRgn_id,
				convert(varchar(8), ps.Person_BirthDay, 112) as Person_BirthDay,
				msf.MedPersonal_id,
				msf.Person_SurName as MedPersonal_SurName,
				msf.Person_FirName as MedPersonal_FirName,
				msf.Person_SecName as MedPersonal_SecName,
				bs.BirthSvid_Ser,
				bs.BirthSvid_Num,
				d.Document_Num,
				d.Document_Ser,
				ndt.DocumentType_Name,
				ndt.DocumentType_Code,
				convert(varchar(10), d.Document_begDate, 104) as Document_begDate,
				o.Org_Name as DocOrg_Name,
				o.Org_Code as DocOrg_Code,
				bs.BirthSvid_ChildFamil,
				convert(varchar(8), bs.BirthSvid_BirthDT, 112) as BirthSvid_BirthDT,
				convert(varchar(20), bs.BirthSvid_BirthDT, 120)  as BirthSvid_BirthDT_Format,
				bs.BirthSvid_IsMnogoplod,
				ISNULL(bs.BirthSvid_PlodCount, 1) as BirthSvid_PlodCount,
				ISNULL(bs.BirthSvid_ChildCount, 1) as BirthSvid_ChildCount,
				ISNULL(bs.BirthSvid_PlodIndex, 1) as BirthSvid_PlodIndex,
				bs.BirthSvid_Mass,
				bs.BirthSvid_Height,
				bs.Okei_mid,
				bp.BirthPlace_Code,
				bp.BirthPlace_Name,
				bsp.BirthSpecialist_Code,
				bsp.BirthSpecialist_Name,
				bs.BirthSvid_Week,
				be.BirthEmployment_Code,
				be.BirthEmployment_Name,
				bed.BirthEducation_Code,
				bed.BirthEducation_Name,
				fs.FamilyStatus_Code,
				fs.FamilyStatus_Name,
				klat.KLAreaType_Code,
				klat.KLAreaType_Name,
				uasr.KLArea_Name as KLSubRgn_Name,
				ISNULL(uat.KLArea_Name, uac.KLArea_Name) as KLCity_Name,
				uas.KLStreet_Name as KLStreet_Name,
				ua.Address_Corpus as Address_Corpus,
				ua.Address_House as Address_House,
				ua.Address_Flat as Address_Flat,
				COALESCE(uas.KLStreet_AOGUID, uat.KLArea_AOGUID, uac.KLArea_AOGUID, uasr.KLArea_AOGUID, uar.KLArea_AOGUID) as KLAreaGUID,
				psm.Person_Snils as MedPersonal_Snils,
				psm.Person_Phone as MedPersonal_Phone,
				mua.Address_Address as MAddress_Address,
				mua.KLRgn_id as MKLRgn_id,
				convert(varchar(8), bs.BirthSvid_RcpDate, 112) as BirthSvid_RcpDate,
				psr.Person_Snils as RPerson_Snils,
				mp.MedPost_Code,
				mp.MedPost_Name,
				dt.Frmr_id,
				bs.DeputyKind_id,
				psr.Sex_id as RSex_id, 
				ps.Polis_Num
			from
				dbo.v_BirthSvid bs with (nolock)
				left join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = bs.MedStaffFact_id
				left join persis.Post p (nolock) on p.id = msf.Post_id
				left join nsi.v_MedPost mp (nolock) on mp.MedPost_id = p.MedPost_id
				left join fed.v_PassportToken LpuOID with(nolock) on LpuOID.Lpu_id = bs.Lpu_id
				left join v_PersonState ps with (nolock) on ps.Person_id = bs.Person_id
				left join v_PersonState psr with (nolock) on psr.Person_id = bs.Person_rid
				left join v_PersonState psm with (nolock) on psm.Person_id = msf.Person_id
				left join v_Document d (nolock) on d.Document_id = ps.Document_id
				left join v_DocumentType dt (nolock) on dt.DocumentType_id = d.DocumentType_id
				left join nsi.v_DocumentType ndt with (nolock) on ndt.DocumentType_id = dt.Frmr_id
				left join v_Org o (nolock) on o.Org_id = d.OrgDep_id
				left join v_Sex s with (nolock) on s.Sex_id = ps.Sex_id
				left join v_Address_all ua with (nolock) on ua.Address_id = ps.UAddress_id
				left join v_PersonInfo VPI with (nolock) on VPI.Person_id = PS.Person_id
				left join v_Lpu l with (nolock) on l.Lpu_id = bs.Lpu_id
				left join v_Org ol with (nolock) on ol.Org_id = l.Org_id
				left join v_Address_all lua with (nolock) on lua.Address_id = l.UAddress_id
				left join v_BirthPlace bp with (nolock) on bp.BirthPlace_id = bs.BirthPlace_id
				left join v_BirthSpecialist bsp with (nolock) on bsp.BirthSpecialist_id = bs.BirthSpecialist_id
				left join v_BirthEmployment be with (nolock) on be.BirthEmployment_id = bs.BirthEmployment_id
				left join v_BirthEducation bed with (nolock) on bed.BirthEducation_id = bs.BirthEducation_id
				left join v_FamilyStatus fs with (nolock) on fs.FamilyStatus_id = bs.BirthFamilyStatus_id
				left join v_Address_all ra with (nolock) on ra.Address_id = bs.Address_rid
				left join v_KLAreaType klat with (nolock) on klat.KLAreaType_id = ua.KLAreaType_id
				left join v_KLArea uar with (nolock) on uar.KLArea_id = ua.KLRgn_id
				left join v_KLArea uasr with (nolock) on uasr.KLArea_id = ua.KLSubRgn_id
				left join v_KLArea uac with (nolock) on uac.KLArea_id = ua.KLCity_id
				left join v_KLArea uat with (nolock) on uat.KLArea_id = ua.KLTown_id
				left join v_KLStreet uas with (nolock) on uas.KLStreet_id = ua.KLStreet_id
				left join v_Address_all mua with (nolock) on mua.Address_id = psm.UAddress_id
			where
				bs.BirthSvid_id = :BirthSvid_id
		", [
			'BirthSvid_id' => $data['BirthSvid_id']
		]);
		//накапливаем все ошибки и сразу выдаем их все
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
			select top 1
				ds.DeathSvid_id,
				convert(varchar(8), ds.DeathSvid_GiveDate, 112) as DeathSvid_GiveDate,
				LpuOID.PassportToken_tid,
				ds.Person_id,
				PS.Person_Snils,
				PS.Person_SurName,
				PS.Person_FirName,
				PS.Person_SecName,
				psr.Person_SurName as RPerson_SurName,
				psr.Person_FirName as RPerson_FirName,
				psr.Person_SecName as RPerson_SecName,
				s.Sex_Code,
				s.Sex_Name,
				ua.Address_Address as Address_Address,
				ua.KLRgn_id as KLRgn_id,
				ps.Person_Phone,
				VPI.PersonInfo_Email,
				L.Lpu_Nick,
				L.Lpu_Phone,
				OL.Org_Www as Lpu_Www,
				lua.Address_Address as LAddress_Address,
				lua.KLRgn_id as LKLRgn_id,
				convert(varchar(8), ps.Person_BirthDay, 112) as Person_BirthDay,
				msf.MedPersonal_id,
				msf.Person_SurName as MedPersonal_SurName,
				msf.Person_FirName as MedPersonal_FirName,
				msf.Person_SecName as MedPersonal_SecName,
				msfh.MedPersonal_id as MedPersonal_hid,
				msfh.Person_SurName as MedPersonal_hSurName,
				msfh.Person_FirName as MedPersonal_hFirName,
				msfh.Person_SecName as MedPersonal_hSecName,
				ds.DeathSvid_Ser,
				ds.DeathSvid_Num,
				d.Document_Num,
				d.Document_Ser,
				convert(varchar(10), d.Document_begDate, 104) as Document_begDate,
				o.Org_Name as DocOrg_Name,
				ds.DeathSvid_Mass,
				bp.DeathPlace_Code,
				bp.DeathPlace_Name,
				be.DeathEmployment_Code,
				be.DeathEmployment_Name,
				bed.DeathEducation_Code,
				bed.DeathEducation_Name,
				fs.FamilyStatus_Code,
				fs.FamilyStatus_Name,
				klat.KLAreaType_Code,
				klat.KLAreaType_Name,
				uasr.KLSubRgn_Name as KLSubRgn_Name,
				ISNULL(uac.KLCity_Name, uat.KLTown_Name) as KLCity_Name,
				uas.KLStreet_Name as KLStreet_Name,
				ua.Address_Corpus as Address_Corpus,
				ua.Address_House as Address_House,
				ua.Address_Flat as Address_Flat,
				dklat.KLAreaType_Code as DKLAreaType_Code,
				dklat.KLAreaType_Name as DKLAreaType_Name,
				dasr.KLSubRgn_Name as DKLSubRgn_Name,
				ISNULL(dac.KLCity_Name, dat.KLTown_Name) as DKLCity_Name,
				das.KLStreet_Name as DKLStreet_Name,
				da.Address_Corpus as DAddress_Corpus,
				da.Address_House as DAddress_House,
				da.Address_Flat as DAddress_Flat,
				da.Address_Address as DAddress_Address,
				da.KLRgn_id as DKLRgn_id,
				psm.Person_Snils as MedPersonal_Snils,
				psm.Person_Phone as MedPersonal_Phone,
				mua.Address_Address as MAddress_Address,
				mua.KLRgn_id as MKLRgn_id,
				psmh.Person_Snils as MedPersonal_hSnils,
				psmh.Person_Phone as MedPersonal_hPhone,
				mhua.Address_Address as MHAddress_Address,
				mhua.KLRgn_id as MHKLRgn_id,
				ds.DeathSvidRelation_id,
				convert(varchar(8), ds.DeathSvid_RcpDate, 112) as DeathSvid_RcpDate,
				psr.Person_Snils as RPerson_Snils,
				mp.MedPost_Code,
				mp.MedPost_Name,
				msfh.MedPost_Code as MedPost_hCode,
				msfh.MedPost_Name as MedPost_hName,
				dt.Frmr_id,
				psr.Sex_id as RSex_id,
				dwt.DeathWomanType_Code,
				dwt.DeathWomanType_Name,
				ddt.DtpDeathTime_Code,
				ddt.DtpDeathTime_Name,
				od.Diag_Code as Diag_oCode,
				od.Diag_Name as Diag_oName,
				ed.Diag_Code as Diag_eCode,
				ed.Diag_Name as Diag_eName,
				td.Diag_Code as Diag_tCode,
				td.Diag_Name as Diag_tName,
				id.Diag_Code as Diag_iCode,
				id.Diag_Name as Diag_iName,
				md.Diag_Code as Diag_mCode,
				md.Diag_Name as Diag_mName,
				ds.DeathSvid_PribPeriod,
				ds.DeathSvid_PribPeriodPat,
				ds.DeathSvid_PribPeriodDom,
				ds.DeathSvid_PribPeriodExt,
				ds.DeathSvid_PribPeriodImp,
				ds.DeathSvid_TimePeriod,
				ds.DeathSvid_TimePeriodPat,
				ds.DeathSvid_TimePeriodDom,
				ds.DeathSvid_TimePeriodExt,
				ds.DeathSvid_TimePeriodImp,
				convert(varchar(8), ds.DeathSvid_TraumaDate, 112) as DeathSvid_TraumaDate,
				convert(varchar(8), ds.DeathSvid_DeathDate, 112) as DeathSvid_DeathDate,
				convert(varchar(5), ds.DeathSvid_DeathDate, 108) as DeathSvid_DeathTime,
				convert(varchar(8), ds.DeathSvid_OldGiveDate, 112) as DeathSvid_OldGiveDate,
				ds.DeathSvid_ChildCount,
				ds.DeathSvid_OldSer,
				ds.DeathSvid_OldNum,
				ctt.ChildTermType_Code,
				ctt.ChildTermType_Name,
				dsc.DeathSetCause_Code,
				dsc.DeathSetCause_Name,
				dst.DeathSetType_Code,
				dst.DeathSetType_Name,
				dc.DeathCause_Code,
				dc.DeathCause_Name,
				dfs.DeathFamilyStatus_Code,
				dfs.DeathFamilyStatus_Name,
				dsty.DeathSvidType_Code,
				dsty.DeathSvidType_Name
			from
				dbo.v_DeathSvid ds with (nolock)
				left join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = ds.MedStaffFact_id
				left join persis.Post p (nolock) on p.id = msf.Post_id
				left join nsi.v_MedPost mp (nolock) on mp.MedPost_id = p.MedPost_id
				left join v_OrgHead oh (nolock) on oh.Person_id = ds.Person_hid and oh.OrgHeadPost_id = ds.OrgHeadPost_id
				outer apply (
					select top 1
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
						v_MedStaffFact msfh (nolock)
						inner join persis.Post ph (nolock) on ph.id = msfh.Post_id
						inner join nsi.v_MedPost mph (nolock) on mph.MedPost_id = ph.MedPost_id
					where
						msfh.Person_id = oh.Person_id
						and mph.MedPost_pid = 3
				) msfh
				left join fed.v_PassportToken LpuOID with(nolock) on LpuOID.Lpu_id = ds.Lpu_id
				left join v_PersonState ps with (nolock) on ps.Person_id = ds.Person_id
				left join v_PersonState psr with (nolock) on psr.Person_id = ds.Person_rid
				left join v_PersonState psm with (nolock) on psm.Person_id = msf.Person_id
				left join v_PersonState psmh with (nolock) on psmh.Person_id = msfh.Person_id
				left join v_Document d (nolock) on d.Document_id = ps.Document_id
				left join v_DocumentType dt (nolock) on dt.DocumentType_id = d.DocumentType_id
				left join v_Org o (nolock) on o.Org_id = d.OrgDep_id
				left join v_Sex s with (nolock) on s.Sex_id = ps.Sex_id
				left join v_Address_all ua with (nolock) on ua.Address_id = ps.UAddress_id
				left join v_PersonInfo VPI with (nolock) on VPI.Person_id = PS.Person_id
				left join v_Lpu l with (nolock) on l.Lpu_id = ds.Lpu_id
				left join v_Org ol with (nolock) on ol.Org_id = l.Org_id
				left join v_Address_all lua with (nolock) on lua.Address_id = l.UAddress_id
				left join v_DeathPlace bp with (nolock) on bp.DeathPlace_id = ds.DeathPlace_id
				left join v_DeathEmployment be with (nolock) on be.DeathEmployment_id = ds.DeathEmployment_id
				left join v_DeathEducation bed with (nolock) on bed.DeathEducation_id = ds.DeathEducation_id
				left join v_FamilyStatus fs with (nolock) on fs.FamilyStatus_id = ds.FamilyStatus_id
				left join v_KLAreaType klat with (nolock) on klat.KLAreaType_id = ua.KLAreaType_id
				left join v_KLSubRgn uasr with (nolock) on uasr.KLSubRgn_id = ua.KLSubRgn_id
				left join v_KLTown uat with (nolock) on uat.KLTown_id = ua.KLTown_id
				left join v_KLCity uac with (nolock) on uac.KLCity_id = ua.KLCity_id
				left join v_KLStreet uas with (nolock) on uas.KLStreet_id = ua.KLStreet_id
				left join v_Address_all mua with (nolock) on mua.Address_id = psm.UAddress_id
				left join v_Address_all mhua with (nolock) on mhua.Address_id = psmh.UAddress_id
				left join v_DeathWomanType dwt with (nolock) on dwt.DeathWomanType_id = ds.DeathWomanType_id
				left join v_DtpDeathTime ddt with (nolock) on ddt.DtpDeathTime_id = ds.DtpDeathTime_id
				left join v_DeathSetCause dsc with (nolock) on dsc.DeathSetCause_id = ds.DeathSetCause_id
				left join v_ChildTermType ctt with (nolock) on ctt.ChildTermType_id = ds.ChildTermType_id
				left join v_DeathSetType dst with (nolock) on dst.DeathSetType_id = ds.DeathSetType_id
				left join v_DeathCause dc with (nolock) on dc.DeathCause_id = ds.DeathCause_id
				left join v_DeathFamilyStatus dfs with (nolock) on dfs.DeathFamilyStatus_id = ds.DeathFamilyStatus_id
				left join v_DeathSvidType dsty with (nolock) on dsty.DeathSvidType_id = ds.DeathSvidType_id
				left join v_Address_all da with (nolock) on da.Address_id = ds.Address_did
				left join v_KLAreaType dklat with (nolock) on dklat.KLAreaType_id = da.KLAreaType_id
				left join v_KLSubRgn dasr with (nolock) on dasr.KLSubRgn_id = da.KLSubRgn_id
				left join v_KLTown dat with (nolock) on dat.KLTown_id = da.KLTown_id
				left join v_KLCity dac with (nolock) on dac.KLCity_id = da.KLCity_id
				left join v_KLStreet das with (nolock) on das.KLStreet_id = da.KLStreet_id
				left join v_Diag od with (nolock) on od.Diag_id = ds.Diag_oid
				left join v_Diag ed with (nolock) on ed.Diag_id = ds.Diag_eid
				left join v_Diag td with (nolock) on td.Diag_id = ds.Diag_tid
				left join v_Diag id with (nolock) on id.Diag_id = ds.Diag_iid
				left join v_Diag md with (nolock) on md.Diag_id = ds.Diag_mid
			where
				ds.DeathSvid_id = :DeathSvid_id
		", [
			'DeathSvid_id' => $data['DeathSvid_id']
		]);

		if (empty($resp[0]['DeathSvid_id'])) {
			throw new Exception('Ошибка получения данных по направлению на МСЭ', 500);
		}

		$resp[0]['assignedTime'] = date('Y-m-d');
		$resp[0]['isAssigned'] = 'S';

		$resp[0]['DeputyKind_FirstCode'] = 'AGNT'; // представителю, не может же пациент сам свое свидетельство получить

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
                    select top 1
                        ps.Person_id,
                        pd.PersonDisp_id,
                        evpl.EvnVizitPL_id,
                        eps.EvnPS_id
                    from
                        v_PersonState ps (nolock)
                        outer apply (
                            select top 1
                                pd.PersonDisp_id
                            from
                                v_PersonDisp pd (nolock)
                            where
                                pd.Person_id = ps.Person_id
                                and ISNULL(pd.PersonDisp_begDate, :onDate) <= :onDate
                                and ISNULL(pd.PersonDisp_endDate, :onDate) >= :onDate
                        ) pd
                        outer apply (
                            select top 1
                                evpl.EvnVizitPL_id
                            from
                                v_EvnVizitPL evpl (nolock)
                                inner join v_VizitType vt (nolock) on vt.VizitType_id = evpl.VizitType_id and vt.VizitType_SysNick = 'desease' -- Заболевание
                            where
                                evpl.Person_id = ps.Person_id
                                and datediff(day, evpl.EvnVizitPL_setDT, :onDate) <= 365
                        ) evpl
                        outer apply (
                            select top 1
                                eps.EvnPS_id
                            from
                                v_EvnPS eps (nolock)
                            where
                                eps.Person_id = ps.Person_id
                                and datediff(day, eps.EvnPS_setDT, :onDate) <= 365
                        ) eps
                    where
                        ps.Person_id = :Person_id
                        and ISNULL(ps.Person_IsUnknown, 1) <> 2
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
		$query = "select top 1
				DS.Person_id as Person_id,
				DS.DeathSvid_DeathDate as DeathSvid_DeathDate,
				DS.DeathSvid_GiveDate as DeathSvid_GiveDate,
				DS.DeathSvid_IsPrimDiagIID as DeathSvid_IsPrimDiagIID,
				DS.DeathSvid_IsPrimDiagTID as DeathSvid_IsPrimDiagTID,
				DS.DeathSvid_IsPrimDiagMID as DeathSvid_IsPrimDiagMID,
				DS.DeathSvid_IsPrimDiagEID as DeathSvid_IsPrimDiagEID,
				DS.Diag_iid as Diag_iid,
				DS.Diag_tid as Diag_tid,
				DS.Diag_mid as Diag_mid,
				DS.Diag_eid as Diag_eid
				from v_DeathSvid DS with(nolock)
				where DS.DeathSvid_id = :svid_id";
		return $this->getFirstRowFromQuery($query, $data);
	}
}

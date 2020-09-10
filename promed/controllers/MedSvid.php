<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* MedSvid - контроллер для работы с медицинскими свидетельствами
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 - 2010 Swan Ltd.
* @author       Salakhov Rustam
* @version      14.12.2011
 * @property MedSvid_model dbmodel
*/
class MedSvid extends swController {
	public $inputRules = array(
		'getDeathDiagStore' => array(

		),
		'checkBirthSvidExist' => array(
			array(
				'field' => 'BirthSvid_pid',
				'label' => 'Идентификатор свидетельства о рождении',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'checkCOVIDVolume' => [
			['field' => 'onDate', 'label' => 'Дата', 'rules' => '', 'type' => 'date']
		],
		'checkDeathSvidExist' => array(
			array(
				'field' => 'DeathSvid_pid',
				'label' => 'Идентификатор свидетельства о смерти',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => '',
				'type' => 'id'
			)
		),
		'printBlanks' => array(
			array(
				'field' => 'svid_type',
				'label' => 'Тип свидетельства',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Numerator_IntNum',
				'label' => 'Начальный номер',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'Numerator_PreNum',
				'label' => 'Префикс',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Numerator_PostNum',
				'label' => 'Постфикс',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Numerator_Ser',
				'label' => 'Серия',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'BlankCount',
				'label' => 'Количество бланков',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'DoubleSide',
				'label' => 'Двусторонняя печать',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Numerator_Nums',
				'label' => 'Список номеров бланков',
				'rules' => '',
				'type' => 'string'
			)
		),
		'loadDeathSvidList' => array(
			array(
				'field' => 'DeathSvid_id',
				'label' => 'Идентификатор свидетельства о смерти',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadDeathSvidViewForm' => array(
			array(
				'field' => 'DeathSvid_id',
				'label' => 'Идентификатор свидетельства',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadBirthSvidViewForm' => array(
			array(
				'field' => 'BirthSvid_id',
				'label' => 'Идентификатор свидетельства',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadMedSvidListGrid' => array(
			array('default' => 100,'field' => 'limit','label' => 'Лимит записей','rules' => '','type' => 'int'),
			array('default' => 0,'field' => 'start','label' => 'Начальная запись','rules' => '','type' => 'int'),
			array('default' => 0,'field' => 'IsBad','label' => 'Bдентификатор IsBad','rules' => '','type' => 'int'),
			array('default' => 0,'field' => 'IsActual','label' => 'Bдентификатор IsActual','rules' => '','type' => 'int'),
			array('field' => 'LpuRegion_id','label' => 'Участок','rules' => '','type' => 'int'),
			array('field' => 'ReceptType_id','label' => 'Тип свидетельства', 'type' => 'int'), // ТИП СВИДЕТЕЛЬСТВА
			array('default' => '','field' => 'Start_Date','label' => 'Начало периода','rules' => '','type' => 'date'),
			array('default' => '','field' => 'End_Date','label' => 'Конец периода','rules' => '','type' => 'date'),
			array('default' => '','field' => 'Death_Date','label' => 'Дата смерти','rules' => 'trim','type' => 'daterange'),
			array('default' => '','field' => 'Birth_Date','label' => 'Дата рождения','rules' => 'trim','type' => 'daterange'),
			array('default' => '','field' => 'Give_Date','label' => 'Дата выдачи','rules' => 'trim','type' => 'daterange'),
			array('default' => '','field' => 'Person_Surname','label' => 'Фамилия','rules' => '','type' => 'string'),
			array('default' => '','field' => 'Person_Firname','label' => 'Имя','rules' => '','type' => 'string'),
			array('default' => '','field' => 'Person_Secname','label' => 'Отчество','rules' => '','type' => 'string'),
			array('default' => '','field' => 'Child_Surname','label' => 'Фамилия ребенка','rules' => '','type' => 'string'),
			array('default' => '','field' => 'Child_BirthDate','label' => 'Дата рождения ребенка','rules' => 'trim','type' => 'daterange'),
			array('default' => '','field' => 'Sex_id','label' => 'Пол ребенка','rules' => '','type' => 'id'),
			array('default' => '','field' => 'Svid_Num','label' => 'Номер свидетельства','rules' => '','type' => 'string'),
			array('default' => '','field' => 'viewMode','label' => 'Режим просмотра','rules' => '','type' => 'id'),
			array('field' => 'DeathCause','label' => 'Причина смерти','rules' => '','type' => 'string'),
			array('field' => 'Diag_Code_From','label' => 'Код диагноза от','rules' => '','type' => 'string'),
			array('field' => 'Diag_Code_To','label' => 'Код диагноза до','rules' => '','type' => 'string'),
			array('field' => 'Lpu_id','label' => 'Идентификатор ЛПУ','rules'=>'','type'=>'id' ),
            array('default' => '','field' => 'MedPersonal_id','label' => 'Врач','rules'=>'','type'=>'id' )
		),
		'getLpuLicence' => array(
			array(
				'field' => 'svidDate',
				'label' => 'Дата',
				'rules' => '',
				'type'  => 'string'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'ЛПУ',
				'rules' => '',
				'type'  => 'id'
			),
			array(
				'field'	=> 'fromMZ',
				'label'	=> 'Запуск из АРМ Минздрава',
				'rules'	=>'',
				'type'	=> 'string',
				'defauld' => '1'
			)
		),
		'savePntDeathRecipient' => array(
			array('field' => 'PntDeathSvid_id', 'label' => 'Идентификатор свидетельства о перинатальной смерти', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Person_rid','label' => 'Получатель','rules' => '','type' => 'id'),
			array('field' => 'Person_id','label' => 'Идентификатор матери','rules' => '','type' => 'id'),
			array('field' => 'DeathSvidType_id','label' => 'Вид свидетельства','rules' => 'required','type' => 'id'),
			array('field' => 'PntDeathSvid_DeathDate_Date','label' => 'Дата смерти','rules' => '','type' => 'date'),
			array('field' => 'PntDeathSvid_BirthDateStr','label' => 'Дата рождения','rules' => '','type' => 'string'),
			array('field' => 'PntDeathSvid_DeathDateStr','label' => 'Неуточ. дата смерти','rules' => '','type' => 'string'),
			array('field' => 'PntDeathSvid_ChildBirthDT_Date','label' => 'Дата рождения','rules' => '','type' => 'date'),
			array('field' => 'PntDeathSvid_Mass','label' => 'Масса при рождении','rules' => '','type' => 'int'),
			array('field' => 'PntDeathSvid_Height','label' => 'Рост при рождении','rules' => '','type' => 'int'),
			array('field' => 'PntDeathSvid_PolFio','label' => 'Получатель (ручной ввод)','rules' => '','type' => 'string'),
			array('field' => 'PntDeathSvid_RcpDoc','label' => 'Документ','rules' => '','type' => 'string'),
			array('field' => 'PntDeathSvid_Ser','label' => 'Серия','rules' => 'required','type' => 'string'),
			array('field' => 'PntDeathSvid_Num','label' => 'Номер','rules' => 'required','type' => 'string'),
			array('field' => 'DeputyKind_id','label' => 'Отношение к ребёнку','rules' => '','type' => 'id'),
			array('field' => 'PntDeathSvid_RcpDate','label' => 'Дата получения','rules' => '','type' => 'date')
		),
		'saveDeathRecipient' => array(
			array('field' => 'DeathSvid_id', 'label' => 'Идентификатор свидетельства о перинатальной смерти', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'DeathSvidType_id','label' => 'вид свидетельства','rules' => 'required','type' => 'id'),
			array('field' => 'Person_id','label' => 'Человек','rules' => 'required','type' => 'id'),
			array('field' => 'Person_rid','label' => 'Получатель','rules' => '','type' => 'id'),
			array('field' => 'DeathSvidRelation_id','label' => 'Отношение к умершему','rules' => '','type' => 'id'),
			array('field' => 'DeathSvid_Ser','label' => 'Серия','rules' => 'required','type' => 'string'),
			array('field' => 'DeathSvid_Num','label' => 'Номер','rules' => 'required','type' => 'string'),
			array('field' => 'DeathSvid_BirthDateStr','label' => 'Дата рождения','rules' => '','type' => 'string'),
			array('field' => 'DeathSvid_DeathDateStr','label' => 'Неуточ. дата смерти','rules' => '','type' => 'string'),
			array('field' => 'DeathSvid_DeathDate_Date','label' => 'Дата смерти','rules' => '','type' => 'date'),
			array('field' => 'DeathSvid_PolFio','label' => 'Получатель (ручной ввод)','rules' => '','type' => 'string'),
			array('field' => 'DeathSvid_RcpDocument','label' => 'Документ','rules' => '','type' => 'string'),
			array('field' => 'DeathSvid_RcpDate','label' => 'Дата получения','rules' => '','type' => 'date')
		),
		'saveBirthRecipient' => array(
			array('field' => 'BirthSvid_id', 'label' => 'Идентификатор свидетельства о рождении', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'BirthSvid_Ser', 'label' => 'Серия', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'BirthSvid_Num', 'label' => 'Номер', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'BirthSvid_BirthDT_Date','label' => 'Дата рождения','rules' => 'required','type' => 'date'),
			array('field' => 'BirthSvid_Height','label' => 'Рост','rules' => 'required','type' => 'int'),
			array('field' => 'BirthSvid_Mass','label' => 'Масса','rules' => 'required','type' => 'float'),
			array('field' => 'DeputyKind_id','label' => 'Отношение к ребенку','rules' => '','type' => 'id'),
			array('field' => 'BirthSvid_RcpDocument','label' => 'Документ','rules' => '','type' => 'string'),
			array('field' => 'Person_id','label' => 'Человек','rules' => 'required','type' => 'id'),
			array('field' => 'Person_rid','label' => 'Получатель','rules' => '','type' => 'id'),
			array('field' => 'BirthSvid_RcpDate','label' => 'Дата получения','rules' => '','type' => 'date')
		),
		'saveMedSvidBirth' => array(
			array('field' => 'BirthSvid_id', 'label' => 'Идентификатор свидетельства о рождении', 'rules' => '', 'type' => 'id'),
			array('field' => 'BirthEducation_id','label' => '','rules' => '','type' => 'string'),
			array('field' => 'Person_id','label' => 'Человек','rules' => 'required','type' => 'id'),
			array('field' => 'Person_cid','label' => 'Идентификатор ребенка','rules' => '','type' => 'id'),
			array('field' => 'Person_rid','label' => 'Получатель','rules' => '','type' => 'id'),
			array('field' => 'BirthSvid_Ser','label' => 'Серия','rules' => 'required','type' => 'string'),
			array('field' => 'BirthSvid_Num','label' => 'Номер','rules' => 'required','type' => 'string'),
			array('field' => 'MedStaffFact_id','label' => 'Рабочее место врача','rules' => 'required','type' => 'id'),
			array('field' => 'MedPersonal_id','label' => 'Врач','rules' => 'required','type' => 'id'),
			array('field' => 'LpuSection_id','label' => 'Отделение','rules' => '','type' => 'id'),
			array('field' => 'BirthMedPersonalType_id','label' => 'Вид мед персонала','rules' => 'required','type' => 'id'),
			array('field' => 'ReceptType_id','label' => 'Тип свидетельства','rules' => 'required','type' => 'id'),
			array('field' => 'BirthEducation_id','label' => 'Образование','rules' => 'required','type' => 'id'),
			array('field' => 'BirthSvid_BirthDT_Date','label' => 'Дата рождения','rules' => 'required','type' => 'date'),
			array('field' => 'BirthSvid_BirthDT_Time','label' => 'Время рождения','rules' => 'required','type' => 'time'),
			array('field' => 'LpuLicence_id','label' => 'Лицензия','rules' => 'required','type' => 'id'),
			array('field' => 'BirthPlace_id','label' => 'Место рождения','rules' => 'required','type' => 'id'),
			array('field' => 'Sex_id','label' => 'Пол','rules' => 'required','type' => 'id'),
			array('field' => 'BirthSvid_Week','label' => 'Первая явка, неделя','rules' => 'required','type' => 'int'),
			array('field' => 'BirthSvid_ChildCount','label' => 'Который ребенок','rules' => 'required','type' => 'int'),
			array('field' => 'BirthFamilyStatus_id','label' => 'Семейное положение','rules' => '','type' => 'id'),
			array('field' => 'BirthSvid_RcpDocument','label' => 'Документ','rules' => '','type' => 'string'),
            array('field' => 'BirthSvid_RcpDate','label' => 'Дата получения','rules' => '','type' => 'date'),
            array('field' => 'MedStaffFact_cid','label' => 'Место работы врача («Руководитель»)','rules' => 'required','type' => 'id'),
            array('field' => 'BirthSvid_IsOtherMO','label' => 'Выписано за другую МО','rules' => '','type' => 'swcheckbox'),
            array('field' => 'Org_id','label' => 'Идентификатор другой МО','rules' => '','type' => 'int'),
            array('field' => 'BirthEmployment_id','label' => 'Занятость','rules' => 'required','type' => 'id'),
			array('field' => 'BirthSpecialist_id','label' => 'Принял роды','rules' => 'required','type' => 'id'),
			array('field' => 'BirthSvid_ChildFamil','label' => 'Фамилия ребенка','rules' => '','type' => 'string'),
			array('field' => 'BirthSvid_IsMnogoplod','label' => 'Многоплодные роды','rules' => 'required','type' => 'id'),
			array('field' => 'BirthSvid_PlodIndex','label' => 'Который по счету','rules' => '','type' => 'int'),
			array('field' => 'BirthSvid_PlodCount','label' => 'Всего плодов','rules' => '','type' => 'int'),
			array('field' => 'BirthSvid_IsFromMother','label' => 'Записано со слов матери','rules' => 'required','type' => 'id'),
			array('field' => 'BirthSvid_Height','label' => 'Рост','rules' => 'required','type' => 'int'),
			array('field' => 'BirthSvid_Mass','label' => 'Масса','rules' => 'required','type' => 'float'),
			array('field' => 'Okei_mid','label' => 'Единица измерения массы','rules' => 'required','type' => 'int'),
			array('field' => 'BirthSvid_GiveDate','label' => 'Масса','rules' => 'required','type' => 'date'),
			array('field' => 'BirthChildResult_id','label' => 'Ребенок родился','rules' => 'required','type' => 'id'),
			array('field' => 'BAddress_id','label' => 'Адрес (идентификатор)','rules' => '','type' => 'string'),
			array('field' => 'BAddress_Zip','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'BKLCountry_id','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'BKLRGN_id','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'BKLSubRGN_id','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'BKLCity_id','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'BKLTown_id','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'BKLStreet_id','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'BAddress_House','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'BAddress_Corpus','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'BAddress_Flat','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'BAddress_Address','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'DeputyKind_id','label' => 'Отношение к ребёнку','rules' => '','type' => 'id'),
			array('field' => 'OrgHead_id','label' => 'Руководитель','rules' => '','type' => 'id')
		),
		'saveMedSvidDeath' => array(
			array('field' => 'DeathSvid_id', 'label' => 'Идентификатор свидетельства о смерти', 'rules' => '', 'type' => 'id'),
			array('field' => 'Person_id','label' => 'Человек','rules' => 'required','type' => 'id'),
			array('field' => 'Person_mid','label' => 'ФИО матери','rules' => '','type' => 'id'),
			array('field' => 'Person_rid','label' => 'ФИО получателя','rules' => '','type' => 'id'),
			array('field' => 'DeathSvid_PolFio','label' => 'ФИО получателя (ручной ввод)','rules' => '','type' => 'string'),
			array('field' => 'DeathSvid_BirthDateStr','label' => 'Дата рождения','rules' => '','type' => 'string'),
			array('field' => 'DeathSvid_DeathDateStr','label' => 'Неуточ. дата смерти','rules' => '','type' => 'string'),
            array('field' => 'DeathSvid_isBirthDate','label' => 'Неполная/неизвестная дата рождения','rules' => '','type' => 'checkbox'),
			array('field' => 'MedStaffFact_id','label' => 'Рабочее место врача','rules' => 'required','type' => 'id'),
			array('field' => 'MedPersonal_id','label' => 'Врач','rules' => 'required','type' => 'id'),
			array('field' => 'LpuSection_id','label' => 'Отделение','rules' => '','type' => 'id'),
			array('field' => 'DeathSvidType_id','label' => 'вид свидетельства','rules' => 'required','type' => 'id'),
			array('field' => 'ReceptType_id','label' => 'Тип свидетельства','rules' => 'required','type' => 'id'),
			array('field' => 'DeathCause_id','label' => 'Причина смерти','rules' => 'required','type' => 'id'),
			array('field' => 'DeathFamilyStatus_id','label' => 'Семейное положение','rules' => '','type' => 'id'),
			array('field' => 'DeathPlace_id','label' => 'Смерть наступила','rules' => 'required','type' => 'id'),
			array('field' => 'DeathEducation_id','label' => 'Образование','rules' => '','type' => 'id'),
			array('field' => 'DeathTrauma_id','label' => 'вид травмы','rules' => '','type' => 'id'),
			array('field' => 'DeathSetType_id','label' => 'Причина смерти установлена','rules' => 'required','type' => 'id'),
			array('field' => 'DeathSetCause_id','label' => 'На основании','rules' => 'required','type' => 'id'),
			array('field' => 'DeathWomanType_id','label' => 'Для женщин репрод. Возраста','rules' => '','type' => 'id'),
			array('field' => 'DeathEmployment_id','label' => 'Занятость','rules' => '','type' => 'id'),
			array('field' => 'DtpDeathTime_id','label' => 'Смерть от ДТП наступила','rules' => '','type' => 'id'),
			array('field' => 'ChildTermType_id','label' => 'Доношенность','rules' => '','type' => 'id'),
			array('field' => 'Diag_iid','label' => 'Непосредственная причина смерти','rules' => '','type' => 'id'),
			array('field' => 'Diag_tid','label' => 'Патологическое состояние','rules' => '','type' => 'id'),
			array('field' => 'Diag_mid','label' => 'Основная причина смерти','rules' => '','type' => 'id'),
			array('field' => 'Diag_eid','label' => 'Внешние причины','rules' => '','type' => 'id'),
			array('field' => 'Diag_oid','label' => 'Прочие важные состояния','rules' => '','type' => 'id'),
			array('field' => 'DeathSvid_Ser','label' => 'Серия','rules' => 'required','type' => 'string'),
			array('field' => 'DeathSvid_Num','label' => 'Номер','rules' => 'required','type' => 'string'),
			array('field' => 'DeathSvid_OldSer','label' => 'Пред. серия','rules' => '','type' => 'string'),
			array('field' => 'DeathSvid_OldNum','label' => 'Пред. номер','rules' => '','type' => 'string'),
			array('field' => 'DeathSvid_DeathDate_Date','label' => 'Дата смерти','rules' => '','type' => 'date'),
			array('field' => 'DeathSvid_DeathDate_Time','label' => 'Время смерти','rules' => '','type' => 'time'),
			array('field' => 'DeathSvid_IsUnknownDeathDate','label' => 'Дата смерти неизвестна','rules' => '','type' => 'checkbox'),
			array('field' => 'DeathSvid_IsNoDeathTime','label' => 'Время смерти неизвестно','rules' => '','type' => 'checkbox'),
            array('field' => 'DeathSvid_IsNoAccidentTime','label' => 'Время н/случая, отравления, травмы','rules' => '','type' => 'checkbox'),
			array('field' => 'DeathSvid_IsNoPlace','label' => 'Место смерти неизвестно','rules' => '','type' => 'checkbox'),
			array('field' => 'DeathSvid_IsTerm','label' => 'Доношенность (числовое значение)','rules' => '','type' => 'int'),
			array('field' => 'DeathSvid_Mass','label' => 'Масса (г)','rules' => '','type' => 'int'),
			array('field' => 'DeathSvid_Month','label' => 'Месяц жизни','rules' => '','type' => 'int'),
			array('field' => 'DeathSvid_Day','label' => 'День жизни','rules' => '','type' => 'int'),
			array('field' => 'DeathSvid_ChildCount','label' => 'Который ребенок','rules' => '','type' => 'int'),
			array('field' => 'DeathSvid_TraumaDateStr','label' => 'Неуточ. дата н/случая, отравления, травмы','rules' => '','type' => 'string'),
			array('field' => 'DeathSvid_TraumaDate_Date','label' => 'Дата н/случая, отравления, травмы','rules' => '','type' => 'date'),
			array('field' => 'DeathSvid_TraumaDate_Time','label' => 'Время н/случая, отравления, травмы','rules' => '','type' => 'time'),
			array('field' => 'DeathSvid_TraumaDescr','label' => 'Место и обстоятельства, при которых произошла травма (отравление)','rules' => '','type' => 'string'),
			array('field' => 'DeathSvid_Oper','label' => 'Причины, не связанные с болезнью, а также операции','rules' => '','type' => 'string'),
			array('field' => 'DeathSvid_PribPeriod','label' => 'Приблизительный период времени между началом патологического процесса и смертью','rules' => '','type' => 'string'),
			array('field' => 'DeathSvid_PribPeriodImp','label' => 'Приблизительный период времени между началом патологического процесса и смертью','rules' => '','type' => 'string'),
			array('field' => 'DeathSvid_PribPeriodExt','label' => 'Приблизительный период времени между началом патологического процесса и смертью','rules' => '','type' => 'string'),
			array('field' => 'DeathSvid_PribPeriodDom','label' => 'Приблизительный период времени между началом патологического процесса и смертью','rules' => '','type' => 'string'),
			array('field' => 'DeathSvid_PribPeriodPat','label' => 'Приблизительный период времени между началом патологического процесса и смертью','rules' => '','type' => 'string'),
			array('field' => 'DeathSvid_TimePeriod','label' => 'Приблизительный период времени между началом патологического процесса и смертью','rules' => '','type' => 'string'),
			array('field' => 'DeathSvid_TimePeriodImp','label' => 'Приблизительный период времени между началом патологического процесса и смертью','rules' => '','type' => 'string'),
			array('field' => 'DeathSvid_TimePeriodExt','label' => 'Приблизительный период времени между началом патологического процесса и смертью','rules' => '','type' => 'string'),
			array('field' => 'DeathSvid_TimePeriodDom','label' => 'Приблизительный период времени между началом патологического процесса и смертью','rules' => '','type' => 'string'),
			array('field' => 'DeathSvid_TimePeriodPat','label' => 'Приблизительный период времени между началом патологического процесса и смертью','rules' => '','type' => 'string'),
			array('field' => 'DeathSvidRelation_id','label' => 'Отношение к умершему','rules' => '','type' => 'int'),
			array('field' => 'DeathSvid_RcpDate','label' => 'Дата получения','rules' => '','type' => 'date'),
			array('field' => 'DeathSvid_GiveDate','label' => 'Дата выдачи','rules' => 'required','type' => 'date'),
			array('field' => 'DeathSvid_OldGiveDate','label' => 'Пред. дата выдачи','rules' => '','type' => 'date'),
			array('field' => 'DeathSvid_RcpDocument','label' => 'Документ','rules' => '','type' => 'string'),
			array('field' => 'BAddress_id','label' => 'Адрес (идентификатор)','rules' => '','type' => 'string'),
			array('field' => 'BAddress_Zip','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'BKLCountry_id','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'BKLRGN_id','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'BKLSubRGN_id','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'BKLCity_id','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'BKLTown_id','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'BKLStreet_id','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'BAddress_House','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'BAddress_Corpus','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'BAddress_Flat','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'BAddress_Address','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'DAddress_id','label' => 'Адрес (идентификатор)','rules' => '','type' => 'string'),
			array('field' => 'DAddress_Zip','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'DKLCountry_id','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'DKLRGN_id','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'DKLSubRGN_id','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'DKLCity_id','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'DKLTown_id','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'DKLStreet_id','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'DAddress_House','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'DAddress_Corpus','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'DAddress_Flat','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'DAddress_Address','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'Person_hid','label' => 'Идентификатор руковоителя','rules' => '','type' => 'id'),
			array('field' => 'OrgHeadPost_id','label' => 'Идентификатор должности руковоителя','rules' => '','type' => 'id'),
			array('field' => 'DeathSvid_predid','label' => 'Предыдущее свидетельство','rules' => '','type' => 'id'),
			array('field' => 'DeathSvid_IsDuplicate','label' => 'Дубликат','rules' => '','type' => 'id'),
			array('field' => 'DeathSvid_IsLose','label' => 'Утерянно','rules' => '','type' => 'id'),
			array('field' => 'DeathSvid_IsPrimDiagIID','label' => 'Непосредственная причина смерти - первоначальная причина','rules' => '','type' => 'checkbox'),
			array('field' => 'DeathSvid_IsPrimDiagTID','label' => 'Патологическое состояние - первоначальная причина','rules' => '','type' => 'checkbox'),
			array('field' => 'DeathSvid_IsPrimDiagMID','label' => 'Основная причина смерти - первоначальная причина','rules' => '','type' => 'checkbox'),
			array('field' => 'DeathSvid_IsPrimDiagEID','label' => 'Внешние причины - первоначальная причина','rules' => '','type' => 'checkbox'),
			array('field' => 'Okei_id','label' => 'Единицы измерения','rules' => '','type' => 'id'),
			array('field' => 'Okei_patid','label' => 'Единицы измерения','rules' => '','type' => 'id'),
			array('field' => 'Okei_domid','label' => 'Единицы измерения','rules' => '','type' => 'id'),
			array('field' => 'Okei_extid','label' => 'Единицы измерения','rules' => '','type' => 'id'),
			array('field' => 'Okei_impid','label' => 'Единицы измерения','rules' => '','type' => 'id'),
			array('field' => 'MedStaffFact_did','label' => 'Сотрудник установивший причину смерти','rules' => '','type' => 'id'),
			array('field' => 'MedPersonal_cid','label' => 'Свидетельство проверено врачом, ответственным за правильность заполнения медицинских свидетельств','rules' => '','type' => 'id'),
			array('field' => 'DeathSvid_checkDate','label' => 'Дата проверки','rules' => '','type' => 'date'),
		),
		'refreshPersonInfo' => array(
			array('field' => 'Person_id','label' => 'Человек','rules' => 'required','type' => 'id'),
			array('field' => 'PersonInfo_IsParsDeath','label' => 'Протокол разбора случая смерти','rules' => '','type' => 'string'),
			array('field' => 'PersonInfo_IsSetDeath','label' => 'Протокол установления смерти','rules' => '','type' => 'string')
		),
		'saveMedSvidPntDeath' => array(
			array('field' => 'Person_id','label' => 'Идентификатор матери','rules' => '','type' => 'id'),
			array('field' => 'Person_cid','label' => 'Идентификатор ребенка','rules' => '','type' => 'id'),
			array('field' => 'Person_rid','label' => 'Идентификатор получателя','rules' => '','type' => 'id'),
			array('field' => 'PntDeathSvid_PolFio','label' => 'ФИО получателя (ручной ввод)','rules' => '','type' => 'string'),
			array('field' => 'PntDeathSvid_BirthDateStr','label' => 'Дата рождения','rules' => '','type' => 'string'),
			array('field' => 'PntDeathSvid_DeathDateStr','label' => 'Неуточ. дата смерти','rules' => '','type' => 'string'),
			array('field' => 'PntDeathSvid_Ser','label' => 'Серия','rules' => 'required','type' => 'string'),
			array('field' => 'PntDeathSvid_Num','label' => 'Номер','rules' => 'required','type' => 'string'),
			array('field' => 'PntDeathPeriod_id','label' => 'Период смерти','rules' => '','type' => 'id'),
			array('field' => 'DeathSvidType_id','label' => 'Вид свидетельства','rules' => 'required','type' => 'id'),
			array('field' => 'PntDeathSvid_OldSer','label' => 'Пред. Серия','rules' => '','type' => 'string'),
			array('field' => 'PntDeathSvid_OldNum','label' => 'Пред. Номер','rules' => '','type' => 'string'),
			array('field' => 'MedStaffFact_id','label' => 'Рабочее место врача','rules' => 'required','type' => 'id'),
			array('field' => 'MedPersonal_id','label' => 'Врач','rules' => 'required','type' => 'id'),
			array('field' => 'LpuSection_id','label' => 'Отделение','rules' => '','type' => 'id'),
			array('field' => 'PntDeathSvid_DeathDate_Date','label' => 'Дата смерти','rules' => '','type' => 'date'),
			array('field' => 'PntDeathSvid_DeathDate_Time','label' => 'Время смерти','rules' => '','type' => 'time'),
			array('field' => 'ReceptType_id','label' => 'Тип свидетельства','rules' => 'required','type' => 'id'),
			array('field' => 'PntDeathSvid_ChildFio','label' => 'ФИО ребенка','rules' => '','type' => 'string'),
			array('field' => 'PntDeathSvid_ChildBirthDT_Date','label' => 'Дата рождения','rules' => '','type' => 'date'),
			array('field' => 'PntDeathSvid_ChildBirthDT_Time','label' => 'Время рождения','rules' => '','type' => 'time'),
			array('field' => 'PntDeathSvid_PlodIndex','label' => 'Который по счету','rules' => '','type' => 'int'),
			array('field' => 'PntDeathSvid_PlodCount','label' => 'Всего плодов','rules' => '','type' => 'int'),
			array('field' => 'PntDeathSvid_RcpDoc','label' => 'Документ','rules' => '','type' => 'string'),
			array('field' => 'PntDeathSvid_RcpDate','label' => 'Дата получения','rules' => '','type' => 'date'),
			array('field' => 'PntDeathFamilyStatus_id','label' => 'Семейное положение','rules' => '','type' => 'id'),
			array('field' => 'DeathEmployment_id','label' => 'Занятость','rules' => '','type' => 'id'),
			array('field' => 'PntDeathPlace_id','label' => 'Смерть наступила','rules' => '','type' => 'id'),
			array('field' => 'PntDeathEducation_id','label' => 'Образование','rules' => '','type' => 'id'),
			array('field' => 'Sex_id','label' => 'Пол ребенка','rules' => '','type' => 'id'),
			array('field' => 'PntDeathSvid_ChildCount','label' => 'Который ребенок','rules' => '','type' => 'int'),
			array('field' => 'PntDeathSvid_BirthCount','label' => 'Которые роды','rules' => '','type' => 'int'),
			array('field' => 'PntDeathGetBirth_id','label' => 'Роды принял','rules' => '','type' => 'id'),
			array('field' => 'PntDeathTime_id','label' => 'Время смерти','rules' => '','type' => 'id'),
			array('field' => 'PntDeathCause_id','label' => 'Причина смерти','rules' => '','type' => 'id'),
			array('field' => 'PntDeathSetType_id','label' => 'Причина смерти установлена','rules' => 'required','type' => 'id'),
			array('field' => 'PntDeathSetCause_id','label' => 'На основании','rules' => 'required','type' => 'id'),
			array('field' => 'Diag_iid','label' => 'Основное заболевание ребенка','rules' => '','type' => 'id'),
			array('field' => 'Diag_eid','label' => 'Другие заболевания матери','rules' => '','type' => 'id'),
			array('field' => 'Diag_mid','label' => 'Основное заболевание матери','rules' => '','type' => 'id'),
			array('field' => 'Diag_tid','label' => 'Другие заболевания ребенка','rules' => '','type' => 'id'),
			array('field' => 'Diag_oid','label' => 'Другие обстоятельства','rules' => '','type' => 'id'),
			array('field' => 'PntDeathSvid_IsNoPlace','label' => 'Место смерти неизвестно','rules' => '','type' => 'checkbox'),
			array('field' => 'PntDeathSvid_Mass','label' => 'Масса при рождении','rules' => '','type' => 'int'),
			array('field' => 'PntDeathSvid_Height','label' => 'Рост при рождении','rules' => '','type' => 'int'),
			array('field' => 'PntDeathSvid_IsMnogoplod','label' => 'Многоплод. Роды','rules' => '','type' => 'id'),			
			array('field' => 'PntDeathSvid_GiveDate','label' => 'Дата выдачи','rules' => 'required','type' => 'date'),
			array('field' => 'PntDeathSvid_OldGiveDate','label' => 'Пред. Дата выдачи','rules' => '','type' => 'date'),
			array('field' => 'DAddress_id','label' => 'Адрес (идентификатор)','rules' => '','type' => 'string'),
			array('field' => 'DAddress_Zip','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'DKLCountry_id','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'DKLRGN_id','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'DKLSubRGN_id','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'DKLCity_id','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'DKLTown_id','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'DKLStreet_id','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'DAddress_House','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'DAddress_Corpus','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'DAddress_Flat','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'DAddress_Address','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'DeputyKind_id','label' => 'Отношение к ребёнку','rules' => '','type' => 'id'),
			array('field' => 'PntDeathSvid_ActNumber','label' => 'Номер документа','rules' => '','type' => 'string'),
			array('field' => 'PntDeathSvid_ActDT','label' => 'Дата записи акта','rules' => '','type' => 'date'),
			array('field' => 'Org_id','label' => 'Наименование органа ЗАГС','rules' => '','type' => 'id'),
			array('field' => 'PntDeathSvid_ZagsFIO','label' => 'ФИО работника органа ЗАГС','rules' => '','type' => 'string'),
			array('field' => 'PntDeathSvid_IsFromMother','label' => 'Записано со слов матери','rules' => '','type' => 'id'),
			//array('field' => 'OrgHead_id','label' => 'Руководитель','rules' => '','type' => 'id'),
			array('field' => 'Person_hid','label' => 'Идентификатор руковоителя','rules' => '','type' => 'id'),
			array('field' => 'OrgHeadPost_id','label' => 'Идентификатор должности руковоителя','rules' => '','type' => 'id'),
			array('field' => 'PntDeathSvidType_id','label' => 'Причины смерти','rules' => '','type' => 'id'),
			array('field' => 'PntDeathSvid_predid','label' => 'Предыдущее свидетельство','rules' => '','type' => 'id'),
			array('field' => 'PntDeathSvid_IsDuplicate','label' => 'Дубликат','rules' => '','type' => 'id'),
			array('field' => 'PersonInfo_IsSetDeath','label' => 'Протокол установления смерти','rules' => '','type' => 'checkbox'),
			array('field' => 'PersonInfo_IsParsDeath','label' => 'Протокол разбора случая смерти','rules' => '','type' => 'checkbox'),
			array('field' => 'PntDeathSvid_IsLose','label' => 'Утерянно','rules' => '','type' => 'id')
		),
		'deleteMedSvidFromEvnSection' => array(
			array(
				'field' => 'deathChildData',
				'label' => 'Данные о мертворожденных',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'childData',
				'label' => 'Данные о детях',
				'rules' => '',
				'type' => 'string'
			)
		),
		'deleteMedSvidBirth' => array(
			array(
				'field' => 'BirthSvid_id',
				'label' => 'Идентификатор свидетельства о рождении',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'deleteMedSvidPntDeath' => array(
			array(
				'field' => 'PntDeathSvid_id',
				'label' => 'Идентификатор свидетельства о перинатальной смерти',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'setBadSvid' => array(
			array('field' => 'svid_id','label' => 'Идентификатор свидетельства','rules' => '','type' => 'int'),
			array('field' => 'bad_id','label' => 'Идентификатор isBad','rules' => '','type' => 'int'),
			array('field' => 'svid_type','label' => 'Тип свидетельства','rules' => '','type' => 'string')
		),
		'getMedSvidSerNum' => array(
			array('field' => 'svid_type','label' => 'Тип свидетельства','rules' => '','type' => 'string'),
			array('field' => 'LpuSection_id','label' => 'Отделение','rules' => '','type' => 'string'),
			array('field' => 'onDate','label' => 'Дата выписки','rules' => '','type' => 'date'),
			array('field' => 'generateNew','label' => 'Генерировать новый номер','rules' => '','type' => 'int'),
			array('field' => 'ReceptType_id','label' => 'Тип выдачи свидетельства','rules' => '','type' => 'id')
		),
		'reserveNums' => array(
			array(
				'field' => 'Numerator_id',
				'label' => 'Нумератор',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'svid_type',
				'label' => 'Тип свидетельства',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Blank_Count',
				'label' => 'Количество бланков',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Blank_FirstNum',
				'label' => 'Номер первого бланка',
				'rules' => '',
				'type' => 'int'
			),
		),
		'loadMedSvidEditForm' => array(
			array('field' => 'svid_id','label' => 'Идентификатор свидетельства','rules' => '','type' => 'int'),
			array('field' => 'svid_type','label' => 'Тип свидетельства','rules' => '','type' => 'string')
		),
		'getDoubleBirthSvidCnt' => array(
			array('field' => 'BirthSvid_id','label' => 'Идентификатор свидетельства','rules' => '','type' => 'id'),
			array('field' => 'Person_id','label' => 'Идентификатор человека','rules' => 'required','type' => 'id')
		),
		'checkMedSvidSimple' => array(
			array('field' => 'svid_id','label' => 'Идентификатор свидетельства','rules' => '','type' => 'id'),
			array('field' => 'svid_type','label' => 'Тип свидетельства','rules' => '','type' => 'string')
		),
		'getDefaultBirthAddress' => array(
			array('field' => 'Lpu_id','label' => 'Идентификатор ЛПУ','rules' => '','type' => 'id')
		),

        'getAnotherMOName' => array(
            array('field' => 'Org_id','label' => 'Идентификатор другой МО','rules' => '','type' => 'id')
        ),

		'getPacientUAddress' => array(
			array('field' => 'Person_id','label' => 'Идентификатор пациента','rules' => 'required','type' => 'id')
		),
		'getPacientBAddress' => array(
			array('field' => 'Person_id','label' => 'Идентификатор пациента','rules' => 'required','type' => 'id')
		),
		'getPacientDeathAddress' => array(
			array('field' => 'Person_id','label' => 'Идентификатор пациента','rules' => 'required','type' => 'id')
		),
		'getDeathSvidByAttr' => array(
			array('field' => 'Person_id','label' => 'Человек','rules' => '','type' => 'id'),
			array('field' => 'OrderByDeathSvid_id','label' => 'Сортировка по DeathSvid_id','rules' => '','type' => 'string'),
			array('field' => 'DeathSvid_IsBad','label' => 'Идентификатор isBad','rules' => '','type' => 'id')
		),
		'printMedSvid' => array(
			array('field' => 'svid_id','label' => 'Идентификатор свидетельства','rules' => '','type' => 'int'),			
			array('field' => 'svid_type','label' => 'Тип свидетельства','rules' => '','type' => 'string')
		),
		'getDefaultPersonChildValues' => array(
			array('field' => 'Person_id','label' => 'Идентификатор человека','rules' => 'required','type' => 'id')
		),
		'getEmdSignatureRules' => array(
			array('field' => 'EMDPersonRole_id','label' => 'Роль','rules' => 'required','type' => 'id'),
			array('field' => 'EMDDocumentType_id','label' => 'Тип документа','rules' => 'required','type' => 'id')
		),
        'checkR54diagnose' => array(
			array('field' => 'Person_id','label' => 'Идентификатор человека','rules' => 'required','type' => 'id'),
            array('field' => 'DeathSvid_GiveDate','label' => 'Дата выдачи','rules' => 'required','type' => 'date'),
            array('field' => 'DeathSvid_DeathDate_Date','label' => 'Дата смерти','rules' => '','type' => 'date'),
		)
	);

	/**
	 * construct
	 */
	function __construct() {
		parent::__construct();
		
		$this->load->database();
		$this->load->model('MedSvid_model', 'dbmodel');
	}
	
	
	/**
	*  Получение списка свидетельств о смерти
	*  Входящие данные: $_POST['DeathSvid_id'], $_POST['Person_id']
	*  На выходе: JSON-строка
	*  Используется: комбо выбора свидетельства о смерти на форме редактирования протокола патоморфогистологического исследования
	*/
	function loadDeathSvidList() {
		$data = $this->ProcessInputData('loadDeathSvidList', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadDeathSvidList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return false;
	}

	/**
	*  Получение списка свидетельств о смерти и список свидетельств о перинатальной смерти
	*  Входящие данные: $_POST['DeathSvid_id'], $_POST['Person_id']
	*  На выходе: JSON-строка
	*  Используется: комбо выбора свидетельства о смерти на форме редактирования протокола патоморфогистологического исследования
	*/
	function loadDeathSvidListWithPntDeath() {
		$data = $this->ProcessInputData('loadDeathSvidList', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadDeathSvidListWithPntDeath($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return false;
	}


	/**
	*  Ищет свидетельства о смерти по различным атрибутам
	*  На выходе: JSON-строка
	*/
	function getDeathSvidByAttr() {
		$data = $this->ProcessInputData('getDeathSvidByAttr', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getDeathSvidByAttr($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
		
	}
	
	/**
	 * Загрузка формы свидетельства о смерти
	 * Deprecated
	 */
	/*function loadDeathSvidViewForm() {
		$data = $this->ProcessInputData('loadDeathSvidViewForm', false);
		if ($data === false) { return false; }

		$this->ReturnData(array("success"=>true, "html" => "/?c=MedSvid&m=printMedSvid&svid_id=".$data['DeathSvid_id']."&svid_type=death"));
		return true;
	}*/
	
	
	/**
	 * Загрузка формы свидетельства о рождении
	 * Deprecated
	 */
	/*function loadBirthSvidViewForm() {
		$data = $this->ProcessInputData('loadBirthSvidViewForm', false);
		if ($data === false) { return false; }

		$this->ReturnData(array("success"=>true, "html" => "/?c=MedSvid&m=printMedSvid&svid_id=".$data['BirthSvid_id']."&svid_type=birth"));
		return true;
	}*/
	
	
	/**
	 * Загрузка списка свидетельств о рождении
	 */
	function loadMedSvidBirthListGrid() {
		return $this->loadMedSvidListGrid("birth");
	}
	
	
	/**
	 * Загрузка списка свидетельств о смерти
	 */
	function loadMedSvidDeathListGrid() {
		return $this->loadMedSvidListGrid("death");
	}
	
	
	/**
	 * Загрузка списка свидетельств о перинатальной смерти
	 */
	function loadMedSvidPntDeathListGrid() {
		return $this->loadMedSvidListGrid("pntdeath");
	}
	
	
	/**
	*  Получение списка свидетельств
	*  Входящие данные: нет
	*  На выходе: JSON-строка
	*/
	function loadMedSvidListGrid($medsvid_type) {
		$data = $this->ProcessInputData('loadMedSvidListGrid', false);
		if ($data === false) { return false; }

		$sp = getSessionParams();
		$data['session'] = $sp['session'];

		$response = $this->dbmodel->loadMedSvidListGrid($data, $medsvid_type);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
		
	}

	/**
	 * Получение списка номеров лицензий
	 */
	function getLpuLicence(){
		$data = $this->ProcessInputData('getLpuLicence',true);
		if ($data === false) {return false; }

		$response = $this->dbmodel->getLpuLicenceList($data);
		$this->ProcessModelList($response,true,true)->ReturnData();
		return true;
	}
	
	/**
	 * Сохранение свидетельства о рождении
	 */
	function saveMedSvidBirth() {
		return $this->saveMedSvid("birth");
	}
	
	
	/**
	 * Сохранение свидетельства о смерти
	 */
	function saveMedSvidDeath() {
		return $this->saveMedSvid("death");
	}
	
	
	/**
	 * Сохранение свидетельства о перинатальной смерти
	 */
	function saveMedSvidPntDeath() {
	    return $this->saveMedSvid("pntdeath");
	}
	
	
	/**
	 * Сохранение медицинского свидетельства
	 */
	function saveMedSvid($svid_type) {
        //$this->ReturnError( 'ERROR' );
        //return false;

		$userGroups = array();
		if (!empty($_SESSION['groups']) && is_string($_SESSION['groups'])) { //$_SESSION['groups']
			$userGroups = explode('|', $_SESSION['groups']);
		}
		if ((isSuperAdmin() || in_array('MedSvidDeath', $userGroups)) || (isset($_SESSION['medsvidgrant_add']) && $_SESSION['medsvidgrant_add'] == 1)) {
			$this->load->helper('Options');
			$this->load->model('Person_model', 'person_dbmodel');
			$this->load->model('MorbusOrphan_model', 'morbus_orphan_dbmodel');
			$this->load->model('Numerator_model');
			$this->load->model('Diag_model');

			$data = array();
			$val  = array();
			$date = time();
			$numerator = null;

            if ($svid_type == "birth") {
				$data = $this->ProcessInputData('saveMedSvidBirth', true);
				if ($data === false) { return false; }

				if ( getRegionNick() == 'perm' && $data['BirthSvid_IsOtherMO'] == 1 && !$data['BirthSvid_RcpDate'] ){
                    $this->ReturnError( 'Поле Дата получения обязательно.' );
                    return false;
                } elseif ( getRegionNick() != 'perm' && !$data['BirthSvid_RcpDate'] ){
                    $this->ReturnError( 'Поле Дата получения обязательно.' );
                    return false;
                }

				$numerator = $this->Numerator_model->getActiveNumerator(array(
					'NumeratorObject_SysName' => 'BirthSvid',
					'Lpu_id' => $data['Lpu_id'],
					'LpuSection_id' => $data['LpuSection_id'],
					'onDate' => $data['BirthSvid_GiveDate']
				));

				/*if ($data['ReceptType_id'] == 2) {
					$numData = $this->dbmodel->getMedSvidNum(array(
						'svid_type' => 'birth',
						'Lpu_id' => $data['Lpu_id'],
						'NeedNumerator_Num' => $data['BirthSvid_Num'],
						'pmUser_id' => $data['pmUser_id']
					), $numerator);
					if (!empty($numData['Error_Msg'])) {
						return $numData;
					}
					$data['BirthSvid_Num'] = $numData['Numerator_Num'];
				}*/
			}
			
			if ($svid_type == "death") {
				$data = $this->ProcessInputData('saveMedSvidDeath', true);
				if ($data === false) { return false; }

                if ( in_array(getRegionNick(), ['ufa','perm']) ) {
					$Diag_r54id = $this->Diag_model->getDiagidByFilter([
						'Diag_Code' => 'R54',
						'DiagLevel_id' => 4
					]);
					if (!empty($Diag_r54id)) {
						// •	Если  в одном из полей причин смерти: «Непосредственная причина»; «Патологическое состояние»; «Первоначальная причина смерти»; «Внешняя причина смерти»; «Прочие важные состояния»  - значение диагноза «R54 Старость»
						$onDate = $data['DeathSvid_GiveDate'];
						if (!empty($data['DeathSvid_DeathDate_Date'])) {
							$onDate = $data['DeathSvid_DeathDate_Date'];
						}

						if (
							$data['Diag_iid'] == $Diag_r54id
							|| $data['Diag_tid'] == $Diag_r54id
							|| $data['Diag_mid'] == $Diag_r54id
							|| $data['Diag_eid'] == $Diag_r54id
							|| $data['Diag_oid'] == $Diag_r54id
						) {
							//проверка возраста
							$respAge = $this->person_dbmodel->checkPersonAgeIsLess([
								'Person_id' => $data['Person_id'],
								'age' => 81
							]);
							if(!empty($respAge[0]['Person_id'])){
								$this->ReturnError('Выбор диагноза «R54. Старость» возможен только если в год смерти пациента ему(ей) исполнилось или должно было исполнится минимум 81 год');
								return false;
							}
							//#168314 Снятие ограничения в МСС при установлении причины смерти "R54 Старость. Возможно надо будет вернуть
							/*
							$resp_ps = $this->dbmodel->queryResult("
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
								$this->ReturnError('Диагноз "R54 Старость" не применяется для пациентов, состоявших на диспансерном учете или с заведенным случаем лечения в течение года до даты смерти.');
								return false;
							}
							*/
						}
					}
				}

				$numerator = $this->Numerator_model->getActiveNumerator(array(
					'NumeratorObject_SysName' => 'DeathSvid',
					'Lpu_id' => $data['Lpu_id'],
					'LpuSection_id' => $data['LpuSection_id'],
					'onDate' => $data['DeathSvid_GiveDate']
				));

				/*if ($data['ReceptType_id'] == 2) {
					$numData = $this->dbmodel->getMedSvidNum(array(
						'svid_type' => 'death',
						'Lpu_id' => $data['Lpu_id'],
						'NeedNumerator_Num' => $data['DeathSvid_Num'],
						'pmUser_id' => $data['pmUser_id']
					), $numerator);
					if (!empty($numData['Error_Msg'])) {
						return $numData;
					}
					$data['DeathSvid_Num'] = $numData['Numerator_Num'];
				}*/
			}

			if ($svid_type == "pntdeath") {
				$data = $this->ProcessInputData('saveMedSvidPntDeath', true);
				if ($data === false) { return false; }

				$numerator = $this->Numerator_model->getActiveNumerator(array(
					'NumeratorObject_SysName' => 'PntDeathSvid',
					'Lpu_id' => $data['Lpu_id'],
					'LpuSection_id' => $data['LpuSection_id'],
					'onDate' => $data['PntDeathSvid_GiveDate']
				));

				/*if ($data['ReceptType_id'] == 2) {
					$numData = $this->dbmodel->getMedSvidNum(array(
						'svid_type' => 'pntdeath',
						'Lpu_id' => $data['Lpu_id'],
						'NeedNumerator_Num' => $data['PntDeathSvid_Num'],
						'pmUser_id' => $data['pmUser_id']
					), $numerator);
					if (!empty($numData['Error_Msg'])) {
						return $numData;
					}
					$data['PntDeathSvid_Num'] = $numData['Numerator_Num'];
				}*/
			}

			// Стартуем транзакцию
			$this->dbmodel->beginTransaction();

			$response = $this->dbmodel->beforeSave($data, $svid_type, $numerator);

			if ($response['success']) {

			
				switch ($svid_type) {
					case "birth" :
					{
						$date = strtotime($data['BirthSvid_GiveDate']);
						break;
					}
					case "death" :
					{
						$date = strtotime($data['DeathSvid_GiveDate']);

						//Проверяем дату смерти - не должна быть раньше даты рождения
						if (!empty($data['DeathSvid_DeathDate_Date'])) {
							$DeathDate = $data['DeathSvid_DeathDate_Date'];
							if (!empty($data['DeathSvid_DeathDate_Time'])) {
								$DeathDate = $DeathDate.' '.$data['DeathSvid_DeathDate_Time'];
							}
							$response = $this->person_dbmodel->checkPersonDeathDate([
								'Person_id' => $data['Person_id'],
								'DeathDate' => $DeathDate
							]);
							if (!is_array($response) || count($response) == 0) {
								$this->ReturnError('Дата смерти не может быть раньше даты рождения');
								$this->dbmodel->rollbackTransaction();
								return false;
							}
						}
						break;
					}
					case "pntdeath" :
					{
						$date = strtotime($data['PntDeathSvid_GiveDate']);
						if (
							!empty($data['PntDeathSvid_DeathDate_Date']) && !empty($data['PntDeathSvid_ChildBirthDT_Date'])
							&& strtotime($data['PntDeathSvid_DeathDate_Date'])<strtotime($data['PntDeathSvid_ChildBirthDT_Date'])
						) {

							$this->ReturnError('Дата смерти не может быть раньше даты рождения');
							$this->dbmodel->rollbackTransaction();
							return false;
							
						}
						break;
					}
				}

				$response = $this->dbmodel->saveMedSvid($data, $svid_type);
				if (isset($response['svid_id']) && empty($response['Error_Msg'])) {
					$val['success'] = true;
					$val['svid_id'] = $response['svid_id'];
					// успешно сохранено свидетельство, подтверждаем транзацию
					$this->dbmodel->commitTransaction();
				} else {
					if(!empty($response['Error_Msg'])){
						$val = array('success' => false, 'Error_Msg' => 'Ошибка при сохранении свидетельства: '.$response['Error_Msg']);
					} else {
						$val = array('success' => false, 'Error_Msg' => 'Ошибка при сохранении свидетельства');
					}
					$this->dbmodel->rollbackTransaction();
				}
			} else {
				$val = $response;
				$this->dbmodel->rollbackTransaction();
			}

			/*if ($data['session']['region']['nick'] == 'ufa') {
				$this->dbmodel->refreshMedSvidGeneratorForUfa($data, $svid_type);
			}*/
			if ( $svid_type == 'death' ) {
				if ($val["success"] == true && empty($val["Error_Msg"]) && getRegionNick() != 'kz') {
					$this->load->model('MorbusTub_model');
					$TubSpecificFound = $this->MorbusTub_model->checkMorbusTubSpecIsSet($data);
					if ($TubSpecificFound) {
						$data["MorbusTub_id"] = $TubSpecificFound["MorbusTub_id"];
						$data["DeathSvid_IsPrimDiagIID"] = $data["DeathSvid_IsPrimDiagIID"] == true ? "2" : "1";
						$data["DeathSvid_IsPrimDiagTID"] = $data["DeathSvid_IsPrimDiagTID"] == true ? "2" : "1";
						$data["DeathSvid_IsPrimDiagMID"] = $data["DeathSvid_IsPrimDiagMID"] == true ? "2" : "1";
						$data["DeathSvid_IsPrimDiagEID"] = $data["DeathSvid_IsPrimDiagEID"] == true ? "2" : "1";
						$resp = $this->TubRegistryUpdateSpec($data);
					}
				}
				// Проверка на наличие записи в регистре
				$register = $this->morbus_orphan_dbmodel->checkPersonDead($data);
				if ($register!==false) {

					$this->load->model('Messages_model', 'msgmodel');
					$Person_FIO = $register[0]['Person_SurName'] . ' ' . $register[0]['Person_FirName'] . ' ' . $register[0]['Person_SecName'];
					$Person_BirthDay = date('j.m.Y', $register[0]['Person_BirthDay']);
					$messageData = array(
						 'autotype' => 1
						,'type' => 1
						,'pmUser_id' => $data['pmUser_id']
					);
					$messageData['text'] = "Пациент <a href=\"#\" onClick=\"getWnd('swPersonEmkWindow').show({Person_id: {$register[0]['Person_id']}, Server_id: {$register[0]['Server_id']}, PersonEvn_id: {$register[0]['PersonEvn_id']}, mode: 'workplace', ARMType: 'common'});\">{$Person_FIO}</a> {$Person_BirthDay} г.р. включен в регистр по орфанным заболеваниям, но у него указана дата смерти. Возможно, его нужно исключить из регистра.";
					$messageData['title'] = 'Запись регистра на пациента с указанной датой смерти';

					$usersOrphan = $this->morbus_orphan_dbmodel->getUsersOrphan($data);
					if ($usersOrphan!==false) {
						
						foreach ($usersOrphan as $v) {
							$messageData['User_rid'] = $v['PMUser_id'];
							$this->msgmodel->autoMessage($messageData);
						}
					}
				}
			}
			
		} else {
			$val = array('success' => false, 'Error_Msg' => 'У вас недостаточно прав для выписки мед свидетельств');
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');
		$this->ReturnData($val);
		return true;
	}


	/**
	 * Отметка свидетельства испорченным
	 */
	function setBadSvid() {
		$data = $this->ProcessInputData('setBadSvid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->setBadSvid($data);
		if (empty($response[0]["Error_Msg"]) && getRegionNick() != 'kz' && $data["svid_type"] == "death") {
			$this->load->model('MedSvid_model');
			$DeathSvidFound = $this->MedSvid_model->getDeathSvidData($data);
			if ($DeathSvidFound) {
				$this->load->model('MorbusTub_model');
				$TubSpecificFound = $this->MorbusTub_model->checkMorbusTubSpecIsSet($DeathSvidFound);
			}
			if (isset($TubSpecificFound) && !empty($TubSpecificFound)) {
				$params = array(
					'MorbusTub_id' => $TubSpecificFound["MorbusTub_id"],
					'DeathSvid_IsPrimDiagIID' => $DeathSvidFound["DeathSvid_IsPrimDiagIID"],
					'DeathSvid_IsPrimDiagTID' => $DeathSvidFound["DeathSvid_IsPrimDiagTID"],
					'DeathSvid_IsPrimDiagMID' => $DeathSvidFound["DeathSvid_IsPrimDiagMID"],
					'DeathSvid_IsPrimDiagEID' => $DeathSvidFound["DeathSvid_IsPrimDiagEID"],
					'Diag_iid' => $DeathSvidFound["Diag_iid"],
					'Diag_tid' => $DeathSvidFound["Diag_tid"],
					'Diag_mid' => $DeathSvidFound["Diag_mid"],
					'Diag_eid' => $DeathSvidFound["Diag_eid"],
					'DeathSvid_DeathDate_Date' => $DeathSvidFound["DeathSvid_DeathDate"],
					'DeathSvid_GiveDate' => $DeathSvidFound["DeathSvid_GiveDate"],
					'pmUser_id' => $data["pmUser_id"],
				);
				if ($data["bad_id"] == '1') {
					$resp = $this->TubRegistryUpdateSpec($params);
				}
				elseif ($data["bad_id"] == '2') {
					$params["MT_TubResultDeathType_id"] = $TubSpecificFound["TubResultDeathType_id"];
					$params["MT_MorbusTub_deadDT"] = $TubSpecificFound["MorbusTub_deadDT"];
					$params["bad_id"] = $data["bad_id"];
					$resp = $this->TubRegistryUpdateSpec($params);
				}
			}
		}
		$this->ProcessModelSave($response, true, 'При отметке испорченного свидетельства произошла ошибка')->ReturnData();

		return true;
	}

	/**
	 * Актуализация сведений о пациенте в Регистре по Таберкулёзу
	 * @param $data
	 * @return array|bool|int
	 */
	function TubRegistryUpdateSpec($data) {
		$TubDiag = array("2377", "2378", "2379", "2380", "2381", "2382", "2383", "2384", "2385", "2386", "2387", "2388", "2389", "2390", "2391", "2392", "2393", "2394",
			"2395", "2396", "2397", "2398", "2399", "2400", "2401", "2402", "2403", "2404", "2405", "2406", "2407", "2408", "2409", "2410", "2411", "2412", "2413");
		if ($data["DeathSvid_IsPrimDiagIID"] == "2") {
			if (in_array($data["Diag_iid"], $TubDiag)) {
				$TubResultDeathType_id = '1';
			} else {
				$TubResultDeathType_id = '2';
			}
		} elseif ($data["DeathSvid_IsPrimDiagTID"] == "2") {
			if (in_array($data["Diag_tid"], $TubDiag)) {
				$TubResultDeathType_id = '1';
			} else {
				$TubResultDeathType_id = '2';
			}
		} elseif ($data["DeathSvid_IsPrimDiagMID"] == "2") {
			if (in_array($data["Diag_mid"], $TubDiag)) {
				$TubResultDeathType_id = '1';
			} else {
				$TubResultDeathType_id = '2';
			}
		} elseif ($data["DeathSvid_IsPrimDiagEID"] == "2") {
			if (in_array($data["Diag_eid"], $TubDiag)) {
				$TubResultDeathType_id = '1';
			} else {
				$TubResultDeathType_id = '2';
			}
		}
		if (empty($data["DeathSvid_DeathDate_Date"])) {
			$DeathDate = $data["DeathSvid_GiveDate"];
		} else {
			$DeathDate = $data["DeathSvid_DeathDate_Date"];
		}
		$queryParams = array(
			'MorbusTub_id' => $data["MorbusTub_id"],
			'TubResultDeathType_id' => !empty($TubResultDeathType_id) ? $TubResultDeathType_id : '2',
			'MorbusTub_deadDT' => $DeathDate,
			'pmUser_id' => $data["pmUser_id"],
		);
		if (isset($data["bad_id"]) && $data["bad_id"] == '2') {
			if ($data["MT_MorbusTub_deadDT"] == $DeathDate &&
			$data["MT_TubResultDeathType_id"] == !empty($TubResultDeathType_id) ? $TubResultDeathType_id : null) {
				$queryParams ["TubResultDeathType_id"] = null;
				$queryParams ["MorbusTub_deadDT"] = null;
				$resp = $this->dbmodel->swUpdate('MorbusTub', $queryParams);
			} else {
				$resp = 0;
			}			
		} else {			
			$resp = $this->dbmodel->swUpdate('MorbusTub', $queryParams);
		}
		
		return $resp;
	}

	/**
	 * Сохранение данных о получателе
	 */
	function savePntDeathRecipient() {
		$data = $this->ProcessInputData('savePntDeathRecipient', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->savePntDeathRecipient($data);
		$this->ProcessModelSave($response, true, 'При сохранении данных о получателе произошла ошибка')->ReturnData();

		return true;
	}

	/**
	 * Сохранение данных о получателе
	 */
	function saveDeathRecipient() {
		$data = $this->ProcessInputData('saveDeathRecipient', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveDeathRecipient($data);
		$this->ProcessModelSave($response, true, 'При сохранении данных о получателе произошла ошибка')->ReturnData();

		return true;
	}

	/**
	 * Сохранение данных о получателе
	 */
	function saveBirthRecipient(){
		$data = $this->ProcessInputData('saveBirthRecipient', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveBirthRecipient($data);
		$this->ProcessModelSave($response, true, 'При сохранении данных о получателе произошла ошибка')->ReturnData();

		return true;
	}

	/**
	 * Сохранение данных о получателе
	 */
	function refreshPersonInfo() {
		$data = $this->ProcessInputData('refreshPersonInfo', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->refreshPersonInfo($data);
		$this->ProcessModelSave($response, true, 'При сохранении информации пациента произошла ошибка')->ReturnData();

		return true;
	}

	
	/**
	 * Загрузка формы редактирования медицинского свидетельства
	 */
	function loadMedSvidEditForm() {
		$data = $this->ProcessInputData('loadMedSvidEditForm', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadMedSvidEditForm($data);
		$this->ProcessModelList($response, true,true)->ReturnData();
		
		return true;
	}
	
	/**
	 * Возвращает html документ для печати
	 */
	function getPrintMedSvid() {
		$data = $this->ProcessInputData('printMedSvid', true);
		if ($data === false) { return false; }
		$html = $this->printMedSvid(true);
		$this->ReturnData(array("success"=>true, "html" => $html));
	}
	
	/**
	 * Печать медицинского свидетельства
	 */
	function printMedSvid($return = false) {
		$this->load->library('parser');

		$allow_svid_types = array('death', 'birth', 'pntdeath');
		$svid_type = $allow_svid_types[0];
		$default_value = '&nbsp;';

		$data = $this->ProcessInputData('printMedSvid', true);
		if ($data === false) { return false; }

		$data['Registry_id'] = 0;

		if (isset($data['svid_type']) && in_array($data['svid_type'], $allow_svid_types)) { $svid_type = $data['svid_type']; }
		
		if($svid_type=='birth'&&$data['session']['region']['nick'] == 'astra'){
			$template = 'print_svid_birth_astra';
		}else{
			$template = 'print_svid_'.$svid_type;
		}

		if ($data['svid_id'] > 0) {
			
			// Получаем данные
			$response = $this->dbmodel->getMedSvidFields($data, $svid_type);

			if ( (!is_array($response)) || (count($response) == 0) ) {
				echo 'Ошибка при получении данных по свидетельствам';
				return true;
			}

			if ($svid_type == 'birth') {

				if ($response['BKLAddress_Summ'] == '') {
					$response['BKLAddress_Summ'] = null;
				}
				else{
					//$response['BKLAddress_Summ'] = 'город (село) ' . $response['BKLAddress_Summ'];
				}
				if ($response['BKLSubRGN_Name'] == '') {
					$response['BKLSubRGN_Name'] = null;
				}
				else{
					//$response['BKLSubRGN_Name'] = 'район ' . $response['BKLSubRGN_Name'];
				}
				if ($response['BKLRGN_Name'] == '') {
					$response['BKLRGN_Name'] = null;
				}
				else{
					//$response['BKLRGN_Name'] = 'республика, край, область ' . $response['BKLRGN_Name'];
				}
				if(isset($response['BirthSvid_BirthDate'])){
					$date_array = explode('.', $response['BirthSvid_BirthDate']);
				}
				if(isset($response['BirthSvid_BirthDT_Time'])){
					$time_array = explode(':', $response['BirthSvid_BirthDT_Time']);
				}
				if(isset($response['Person_BirthDay'])){
					$person_date_array = explode('.', $response['Person_BirthDay']);
				}
				if(isset($response['BirthSvid_Mass']) && isset($response['Okei_mid_Okei_NationSymbol']) && $response['Okei_mid_Okei_NationSymbol'] == 'кг'){
					$response['BirthSvid_Mass'] = ($response['BirthSvid_Mass'] * 1000);
				}
				if(isset($response['BirthSvid_IsMnogoplod']) && ($response['BirthSvid_IsMnogoplod'] == 1)){
					$response['BirthSvid_PlodIndex'] = $default_value;
					$response['BirthSvid_PlodCount'] = $default_value;
				}

				$parse_data = array(
					'BirthSvid_Ser' => isset($response['BirthSvid_Ser']) ? $response['BirthSvid_Ser'] : $default_value,
					'BirthSvid_Num' => isset($response['BirthSvid_Num']) ? $response['BirthSvid_Num'] : $default_value,
					'BirthSvid_GiveDate' => isset($response['BirthSvid_GiveDate']) ? $response['BirthSvid_GiveDate'] : $default_value,
					'BirthSvid_BirthDate' => isset($response['BirthSvid_BirthDate']) ? $response['BirthSvid_BirthDate'] : $default_value,
					'BirthSvid_BirthDateDay' => isset($response['BirthSvid_BirthDate']) ? $date_array[0] : $default_value,
					'BirthSvid_BirthDateMonth' => isset($response['BirthSvid_BirthDate']) ? $date_array[1] : $default_value,
					'BirthSvid_BirthDateYear' => isset($response['BirthSvid_BirthDate']) ? $date_array[2] : $default_value,
					'BirthSvid_BirthTime' => isset($response['BirthSvid_BirthDT_Time']) ? $response['BirthSvid_BirthDT_Time'] : $default_value,
					'BirthSvid_BirthTimeHour' => isset($response['BirthSvid_BirthDT_Time']) ? $time_array[0] : $default_value,
					'BirthSvid_BirthTimeMin' => isset($response['BirthSvid_BirthDT_Time']) ? $time_array[1] : $default_value,
					'LpuLicence_Num' => isset($response['LpuLicence_Num']) ? $response['LpuLicence_Num'] : $default_value,
					'LpuLicence_setDate' => isset($response['LpuLicence_setDate']) ? $response['LpuLicence_setDate'] : "XX.XX.XXXX",
					'Person_FIO' => isset($response['Person_FIO']) ? $response['Person_FIO'] : $default_value,
					'Person_BirthDay' => isset($response['Person_BirthDay']) ? $response['Person_BirthDay'] : "XX.XX.XXXX",
					'Person_BirthDayDay' => isset($response['Person_BirthDay']) ? $person_date_array[0] : "XX",
					'Person_BirthDayMonth' => isset($response['Person_BirthDay']) ? $person_date_array[1] : "XX",
					'Person_BirthDayYear' => isset($response['Person_BirthDay']) ? $person_date_array[2] : "XXXX",
					'KLRGN_Name' => isset($response['KLRGN_Name']) ? $response['KLRGN_Name'] : $default_value,
					'KLSubRGN_Name' => isset($response['KLSubRGN_Name']) ? $response['KLSubRGN_Name'] : $default_value,
					'KLCity_Name' => isset($response['KLCity_Name']) ? $response['KLCity_Name'] : $default_value,
					'KLTown_Name' => isset($response['KLTown_Name']) ? $response['KLTown_Name'] : $default_value,
					'KLStreet_name' => isset($response['KLStreet_name']) ? $response['KLStreet_name'] : $default_value,
					'Address_House' => isset($response['Address_House']) ? $response['Address_House'] : $default_value,
					'Address_Flat' => isset($response['Address_Flat']) ? $response['Address_Flat'] : $default_value,
					'KlareaType_id' => isset($response['KlareaType_id']) ? $response['KlareaType_id'] : $default_value,
					'BirthSex_id' => isset($response['BirthSex_id']) ? $response['BirthSex_id'] : $default_value,
					'BirthSvid_IsFromMother_Text' => isset($response['BirthSvid_IsFromMother']) ? ($response['BirthSvid_IsFromMother'] == 2 ? "записано со слов матери" : $default_value) : $default_value,
					'Lpu_Name' => isset($response['Lpu_Name']) ? $response['Lpu_Name'] : $default_value,
					'orgaddress_uaddress' => isset($response['orgaddress_uaddress']) ? $response['orgaddress_uaddress'] : $default_value,
					'org_okpo' => isset($response['org_okpo']) ? $response['org_okpo'] : $default_value,
					'BirthFamilyStatus_id' => isset($response['BirthFamilyStatus_id']) ? $response['BirthFamilyStatus_id'] : -1,
					'BirthFamilyStatus_Code' => isset($response['BirthFamilyStatus_Code']) ? $response['BirthFamilyStatus_Code'] : -1,
					'BirthFamilyStatus_Name' => isset($response['BirthFamilyStatus_Name']) ? $response['BirthFamilyStatus_Name'] : $default_value,
					'BirthSvid_ChildFamil' => isset($response['BirthSvid_ChildFamil']) ? $response['BirthSvid_ChildFamil'] : $default_value,
					'BKLRGN_Name' => isset($response['BKLRGN_Name']) ? $response['BKLRGN_Name'] : "неизвестно",
					'BKLSubRGN_Name' => isset($response['BKLSubRGN_Name']) ? $response['BKLSubRGN_Name'] : "неизвестно",
					'BKLCity_Name' => isset($response['BKLCity_Name']) ? $response['BKLCity_Name'] : $default_value,
					'BKLTown_Name' => isset($response['BKLTown_Name']) ? $response['BKLTown_Name'] : $default_value,
					'BKlareaType_id' => isset($response['BKlareaType_id']) ? $response['BKlareaType_id'] : $default_value,
					'BirthPlace_id' => isset($response['BirthPlace_id']) ? $response['BirthPlace_id'] : $default_value,
					'MedPersonal_Post' => isset($response['MedPersonal_Post']) ? $response['MedPersonal_Post'] : $default_value,
					'MedPersonal_FIO' => isset($response['MedPersonal_FIO']) ? $response['MedPersonal_FIO'] : $default_value,
					'birthsvid_PolFio' => isset($response['birthsvid_PolFio']) ? $response['birthsvid_PolFio'] : $default_value,
					'BirthSvid_RcpDocument' => isset($response['BirthSvid_RcpDocument']) ? $response['BirthSvid_RcpDocument'] : $default_value,
					'BirthSvid_RcpDate' => isset($response['BirthSvid_RcpDate']) ? $response['BirthSvid_RcpDate'] : $default_value,
					'BirthEducation_id' => isset($response['BirthEducation_id']) ? $response['BirthEducation_id'] : $default_value,
					'BirthEducation_Code' => isset($response['BirthEducation_Code']) ? $response['BirthEducation_Code'] : $default_value,
					'BirthZanat_id' => isset($response['BirthZanat_id']) ? $response['BirthZanat_id'] : $default_value,
					'BirthSvid_Week' => isset($response['BirthSvid_Week']) ? $response['BirthSvid_Week'] : $default_value,
					'BirthSvid_ChildCount' => isset($response['BirthSvid_ChildCount']) ? $response['BirthSvid_ChildCount'] : $default_value,
					'BirthSvid_Mass' => isset($response['BirthSvid_Mass']) ? $response['BirthSvid_Mass'] : $default_value,
					'Okei_mid_Okei_NationSymbol' => isset($response['Okei_mid_Okei_NationSymbol']) ? $response['Okei_mid_Okei_NationSymbol'] : $default_value,
					'BirthSvid_Height' => isset($response['BirthSvid_Height']) ? $response['BirthSvid_Height'] : $default_value,
					'BirthSvid_IsMnogoplod_Checked' => isset($response['BirthSvid_IsMnogoplod']) ? ($response['BirthSvid_IsMnogoplod'] == 1 ? 'checked="checked"' : "") : "",
					'BirthSvid_PlodIndex' => isset($response['BirthSvid_PlodIndex']) ? $response['BirthSvid_PlodIndex'] : $default_value,
					'BirthSvid_PlodCount' => isset($response['BirthSvid_PlodCount']) ? $response['BirthSvid_PlodCount'] : $default_value,
					'BirthSpecialist_id' => isset($response['BirthSpecialist_id']) ? $response['BirthSpecialist_id'] : $default_value,				
					'OrgHead_Fio' => isset($response['OrgHead_Fio']) ? $response['OrgHead_Fio'] : $default_value,
					'BKLAddress_Summ' => isset($response['BKLAddress_Summ']) ? $response['BKLAddress_Summ'] : "неизвестно",
					'KLAddress_Summ' => isset($response['KLAddress_Summ']) ? $response['KLAddress_Summ'] : $default_value,
					'DeputyKind_Name' => isset($response['DeputyKind_Name']) ? "(".$response['DeputyKind_Name'].")" : $default_value,
					'LpuLicence_Num' =>  isset($response['LpuLicence_Num']) ? $response['LpuLicence_Num'] : ""
				);
			}
			if ($svid_type == 'death'){

				//Если Самара, то меняем шаблон:
				$is_ufa = $data['session']['region']['nick'] == 'ufa';
				if ($is_ufa)
					$template = $template.'_ufa';

				if(isset($response['DeathSvid_TraumaDate'])){
					$deathSvid_TraumaDate = $response['DeathSvid_TraumaDate'];
					$DeathSvid_TraumaYear = '<u>'.substr($deathSvid_TraumaDate,0,4).'</u>';
					$DeathSvid_TraumaMonth = '<u>'.substr($deathSvid_TraumaDate,5,2).'</u>';
					$DeathSvid_TraumaDay = '<u>'.substr($deathSvid_TraumaDate,8,2).'</u>';
					$DeathSvid_TraumaTime = '<u>'.substr($deathSvid_TraumaDate,11,5).'</u>';
				}
				else{
					$DeathSvid_TraumaYear = '<u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u>';
					$DeathSvid_TraumaMonth = '<u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u>';
					$DeathSvid_TraumaDay = '<u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u>';
					$DeathSvid_TraumaTime = '<u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u>';
				}
				$parse_data = array(
					'DeathSvid_Ser' => isset($response['DeathSvid_Ser']) ? $response['DeathSvid_Ser'] : $default_value,
					'DeathSvid_Num' => isset($response['DeathSvid_Num']) ? $response['DeathSvid_Num'] : $default_value,
					'DeathSvid_GiveDate' => isset($response['DeathSvid_GiveDate']) ? $response['DeathSvid_GiveDate'] : $default_value,
					'DeathSvidType_id' => isset($response['DeathSvidType_id']) ? $response['DeathSvidType_id'] : $default_value,
					'DeathSvid_disDate' => isset($response['DeathSvid_disDate']) ? $response['DeathSvid_disDate'] : $default_value,
					'Person_FIO' => isset($response['Person_FIO']) ? $response['Person_FIO'] : $default_value,
					'PersonSex_id' => isset($response['PersonSex_id']) ? $response['PersonSex_id'] : $default_value,
					'Person_BirthDay' => isset($response['Person_BirthDay']) ? $response['Person_BirthDay'] : $default_value,
					'Person_lessYear_Date' => isset($response['Person_BirthDay']) && isset($response['DeathSvid_Month']) && isset($response['DeathSvid_Day']) ? $response['Person_BirthDay'].', число месяцев '.$response['DeathSvid_Month'].' и дней жизни '.$response['DeathSvid_Day'] : $default_value,
					'DeathSvid_DeathDate_Date' => isset($response['DeathSvid_DeathDate_Date']) ? $response['DeathSvid_DeathDate_Date'] : $default_value,
					'DeathSvid_DeathDate_Time' => isset($response['DeathSvid_DeathDate_Time']) ? $response['DeathSvid_DeathDate_Time'] : $default_value,
					'KLRGN_Name' => isset($response['KLRGN_Name']) ? $response['KLRGN_Name'] : $default_value,
					'KLSubRGN_Name' => isset($response['KLSubRGN_Name']) ? $response['KLSubRGN_Name'] : $default_value,
					'KLCity_Name' => isset($response['KLCity_Name']) ? $response['KLCity_Name'] : $default_value,
					'KLTown_Name' => isset($response['KLTown_Name']) ? $response['KLTown_Name'] : $default_value,
					'KLStreet_name' => isset($response['KLStreet_name']) ? $response['KLStreet_name'] : $default_value,
					'Address_House' => isset($response['Address_House']) ? $response['Address_House'] : $default_value,
					'Address_Corpus' => isset($response['Address_Corpus']) ? $response['Address_Corpus'] : $default_value,
					'Address_Flat' => isset($response['Address_Flat']) ? $response['Address_Flat'] : $default_value,
					'DeathPlace_id' => isset($response['DeathPlace_id']) ? $response['DeathPlace_id'] : $default_value,
					'DeathPlace_Code' => isset($response['DeathPlace_Code']) ? $response['DeathPlace_Code'] : $default_value,
					'Person_Age' => isset($response['Person_Age']) ? $response['Person_Age'] : $default_value,
					'DeathSvid_Month' => isset($response['DeathSvid_Month']) ? $response['DeathSvid_Month'] : $default_value,
					'DeathSvid_Day' => isset($response['DeathSvid_Day']) ? $response['DeathSvid_Day'] : $default_value,
					'BAddress_Address' => isset($response['BAddress_Address']) ? $response['BAddress_Address'] : $default_value,
					'DeathSvid_MotherFio' => isset($response['DeathSvid_MotherFio']) ? $response['DeathSvid_MotherFio'] : $default_value,
					'Lpu_Name' => isset($response['Lpu_Name']) ? $response['Lpu_Name'] : $default_value,
					'orgaddress_uaddress' => isset($response['orgaddress_uaddress']) ? $response['orgaddress_uaddress'] : $default_value,
					'org_okpo' => isset($response['org_okpo']) ? $response['org_okpo'] : $default_value,				
					'DeathSvid_disDate' => isset($response['DeathSvid_disDate']) ? $response['DeathSvid_disDate'] : $default_value,
					'KlareaType_id' => isset($response['KlareaType_id']) ? $response['KlareaType_id'] : $default_value,
					'DKLRGN_Name' => isset($response['DKLRGN_Name']) ? $response['DKLRGN_Name'] : $default_value,
					'DKLSubRGN_Name' => isset($response['DKLSubRGN_Name']) ? $response['DKLSubRGN_Name'] : $default_value,
					'DKLCity_Name' => isset($response['DKLCity_Name']) ? $response['DKLCity_Name'] : $default_value,
					'DKLTown_Name' => isset($response['DKLTown_Name']) ? $response['DKLTown_Name'] : $default_value,
					'DKLStreet_name' => isset($response['DKLStreet_name']) ? $response['DKLStreet_name'] : $default_value,
					'DAddress_House' => isset($response['DAddress_House']) ? $response['DAddress_House'] : $default_value,
					'DAddress_Corpus' => isset($response['DAddress_Corpus']) ? $response['DAddress_Corpus'] : $default_value,
					'DAddress_Flat' => isset($response['DAddress_Flat']) ? $response['DAddress_Flat'] : $default_value,
					'DKlareaType_id' => isset($response['DKlareaType_id']) ? $response['DKlareaType_id'] : $default_value,
					'DonosType_id' => isset($response['DonosType_id']) ? $response['DonosType_id'] : $default_value,
					'DeathSvid_Mass' => isset($response['DeathSvid_Mass']) ? '<u>'.$response['DeathSvid_Mass'].'</u>' : '<u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u>',
					'DeathSvid_ChildCount' => isset($response['DeathSvid_ChildCount']) ? '<u>'.$response['DeathSvid_ChildCount'].'</u>' : '<u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u>',
					'DeathSvid_MotherBirthday' => isset($response['DeathSvid_MotherBirthday']) ? '<u>'.$response['DeathSvid_MotherBirthday'].'</u>' : '<u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u>',
					'DeathSvid_MotherAge' => isset($response['DeathSvid_MotherBirthday']) ? '<u>'.$this->BirthdayToAge($response['DeathSvid_MotherBirthday']).'</u>' : '<u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u>',
					'DeathSvid_MotherFamaly' => isset($response['DeathSvid_MotherFamaly']) ? '<u>'.$response['DeathSvid_MotherFamaly'].'</u>' : '<u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u>',
					'DeathSvid_MotherName' => isset($response['DeathSvid_MotherName']) ? '<u>'.$response['DeathSvid_MotherName'].'</u>' : '<u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u>',
					'DeathSvid_MotherSecName' => isset($response['DeathSvid_MotherSecName']) ? '<u>'.$response['DeathSvid_MotherSecName'].'</u>' : '<u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u>',
					'DeathFamilyStatus_Code' => isset($response['DeathFamilyStatus_Code']) ? $response['DeathFamilyStatus_Code'] : -1,
					'DeathFamilyStatus_Name' => isset($response['DeathFamilyStatus_Name']) ? $response['DeathFamilyStatus_Name'] : $default_value,
					'DeathEducation_Code' => isset($response['DeathEducation_Code']) ? $response['DeathEducation_Code'] : $default_value,
					'DeathZanat_id' => isset($response['DeathZanat_id']) ? $response['DeathZanat_id'] : $default_value,
					'DeathCause_id' => isset($response['DeathCause_id']) ? $response['DeathCause_id'] : $default_value,
					'DeathSvid_PribPeriod' => isset($response['DeathSvid_PribPeriod']) ? $response['DeathSvid_PribPeriod'] : $default_value,
					'DeathSvid_PribPeriodPat' => isset($response['DeathSvid_PribPeriodPat']) ? $response['DeathSvid_PribPeriodPat'] : $default_value,
					'DeathSvid_PribPeriodDom' => isset($response['DeathSvid_PribPeriodDom']) ? $response['DeathSvid_PribPeriodDom'] : $default_value,
					'DeathSvid_PribPeriodExt' => isset($response['DeathSvid_PribPeriodExt']) ? $response['DeathSvid_PribPeriodExt'] : $default_value,
					'DeathSvid_PribPeriodImp' => isset($response['DeathSvid_PribPeriodImp']) ? $response['DeathSvid_PribPeriodImp'] : $default_value,
					'DeathSvid_TimePeriod' => isset($response['DeathSvid_TimePeriod']) ? $response['DeathSvid_TimePeriod'] : $default_value,
					'DeathSvid_TimePeriodPat' => isset($response['DeathSvid_TimePeriodPat']) ? $response['DeathSvid_TimePeriodPat'] : $default_value,
					'DeathSvid_TimePeriodDom' => isset($response['DeathSvid_TimePeriodDom']) ? $response['DeathSvid_TimePeriodDom'] : $default_value,
					'DeathSvid_TimePeriodExt' => isset($response['DeathSvid_TimePeriodExt']) ? $response['DeathSvid_TimePeriodExt'] : $default_value,
					'DeathSvid_TimePeriodImp' => isset($response['DeathSvid_TimePeriodImp']) ? $response['DeathSvid_TimePeriodImp'] : $default_value,
					'Diag1_Name' => isset($response['Diag1_Name']) ? $response['Diag1_Name'] : $default_value,
					'Diag2_Name' => isset($response['Diag2_Name']) ? $response['Diag2_Name'] : $default_value,
					'Diag3_Name' => isset($response['Diag3_Name']) ? $response['Diag3_Name'] : $default_value,
					'Diag4_Name' => isset($response['Diag4_Name']) ? $response['Diag4_Name'] : $default_value,
					'Diag5_Name' => isset($response['Diag5_Name']) ? $response['Diag5_Name'] : $default_value,				
					'D11' => isset($response['Diag1_Code']) && isset($response['Diag1_Code'][0]) ? $response['Diag1_Code'][0] : $default_value,
					'D12' => isset($response['Diag1_Code']) && isset($response['Diag1_Code'][1]) ? $response['Diag1_Code'][1] : $default_value,
					'D13' => isset($response['Diag1_Code']) && isset($response['Diag1_Code'][2]) ? $response['Diag1_Code'][2] : $default_value,
					'D14' => isset($response['Diag1_Code']) && isset($response['Diag1_Code'][3]) ? $response['Diag1_Code'][3] : $default_value,
					'D15' => isset($response['Diag1_Code']) && isset($response['Diag1_Code'][4]) ? $response['Diag1_Code'][4] : $default_value,				
					'D21' => isset($response['Diag2_Code']) && isset($response['Diag2_Code'][0]) ? $response['Diag2_Code'][0] : $default_value,
					'D22' => isset($response['Diag2_Code']) && isset($response['Diag2_Code'][1]) ? $response['Diag2_Code'][1] : $default_value,
					'D23' => isset($response['Diag2_Code']) && isset($response['Diag2_Code'][2]) ? $response['Diag2_Code'][2] : $default_value,
					'D24' => isset($response['Diag2_Code']) && isset($response['Diag2_Code'][3]) ? $response['Diag2_Code'][3] : $default_value,
					'D25' => isset($response['Diag2_Code']) && isset($response['Diag2_Code'][4]) ? $response['Diag2_Code'][4] : $default_value,				
					'D31' => isset($response['Diag3_Code']) && isset($response['Diag3_Code'][0]) ? $response['Diag3_Code'][0] : $default_value,
					'D32' => isset($response['Diag3_Code']) && isset($response['Diag3_Code'][1]) ? $response['Diag3_Code'][1] : $default_value,
					'D33' => isset($response['Diag3_Code']) && isset($response['Diag3_Code'][2]) ? $response['Diag3_Code'][2] : $default_value,
					'D34' => isset($response['Diag3_Code']) && isset($response['Diag3_Code'][3]) ? $response['Diag3_Code'][3] : $default_value,
					'D35' => isset($response['Diag3_Code']) && isset($response['Diag3_Code'][4]) ? $response['Diag3_Code'][4] : $default_value,				
					'D41' => isset($response['Diag4_Code']) && isset($response['Diag4_Code'][0]) ? $response['Diag4_Code'][0] : $default_value,
					'D42' => isset($response['Diag4_Code']) && isset($response['Diag4_Code'][1]) ? $response['Diag4_Code'][1] : $default_value,
					'D43' => isset($response['Diag4_Code']) && isset($response['Diag4_Code'][2]) ? $response['Diag4_Code'][2] : $default_value,
					'D44' => isset($response['Diag4_Code']) && isset($response['Diag4_Code'][3]) ? $response['Diag4_Code'][3] : $default_value,
					'D45' => isset($response['Diag4_Code']) && isset($response['Diag4_Code'][4]) ? $response['Diag4_Code'][4] : $default_value,				
					'D51' => isset($response['Diag5_Code']) && isset($response['Diag5_Code'][0]) ? $response['Diag5_Code'][0] : $default_value,
					'D52' => isset($response['Diag5_Code']) && isset($response['Diag5_Code'][1]) ? $response['Diag5_Code'][1] : $default_value,
					'D53' => isset($response['Diag5_Code']) && isset($response['Diag5_Code'][2]) ? $response['Diag5_Code'][2] : $default_value,
					'D54' => isset($response['Diag5_Code']) && isset($response['Diag5_Code'][3]) ? $response['Diag5_Code'][3] : $default_value,
					'D55' => isset($response['Diag5_Code']) && isset($response['Diag5_Code'][4]) ? $response['Diag5_Code'][4] : $default_value,				
					'DeathSvid_Oper' => isset($response['DeathSvid_Oper']) ? $response['DeathSvid_Oper'] : $default_value,
					'DeathSvid_PolFio' => isset($response['DeathSvid_PolFio']) ? $response['DeathSvid_PolFio'] : $default_value,
					'DeathSvid_PolDoc' => isset($response['DeathSvid_PolDoc']) ? $response['DeathSvid_PolDoc'] : $default_value,
					'DeathSvid_PolDate' => isset($response['DeathSvid_PolDate']) ? $response['DeathSvid_PolDate'] : $default_value,
					'DeathSvid_TraumaDate_Date' => isset($response['DeathSvid_TraumaDate_Date']) ? $response['DeathSvid_TraumaDate_Date'] : $default_value,
					'DeathSvid_TraumaDate_Time' => isset($response['DeathSvid_TraumaDate_Time']) ? $response['DeathSvid_TraumaDate_Time'] : $default_value,
					'DeathSvid_TraumaYear' => $DeathSvid_TraumaYear,
					'DeathSvid_TraumaMonth' => $DeathSvid_TraumaMonth,
					'DeathSvid_TraumaDay' => $DeathSvid_TraumaDay,
					'DeathSvid_TraumaTime' => $DeathSvid_TraumaTime,
					'DeathSvid_TraumaObst' => isset($response['DeathSvid_TraumaObst']) ? $response['DeathSvid_TraumaObst'] : $default_value,
					'DeathSvid_TraumaObstUfa' => isset($response['DeathSvid_TraumaObst']) ? '<u>'.$response['DeathSvid_TraumaObst'].'</u>' : '<br><hr width="35%" align="left">',
					'DeathSetType_id' => isset($response['DeathSetType_id']) ? $response['DeathSetType_id'] : $default_value,
					'MedPersonal_Post' => isset($response['MedPersonal_Post']) ? $response['MedPersonal_Post'] : $default_value,
					'DeathSetCause_id' => isset($response['DeathSetCause_id']) ? $response['DeathSetCause_id'] : $default_value,
					'DeathDtpType_id' => isset($response['DeathDtpType_id']) ? $response['DeathDtpType_id'] : $default_value,
					'DeathWomanType_id' => isset($response['DeathWomanType_id']) ? $response['DeathWomanType_id'] : $default_value,
					'MedPersonal_FIO' => isset($response['MedPersonal_FIO']) ? $response['MedPersonal_FIO'] : $default_value,				
					'DeathSvidType_Name' => isset($response['DeathSvidType_Name']) ? $response['DeathSvidType_Name'] : $default_value,
					'subhead' => isset($response['DeathSvidType_id']) && ($response['DeathSvidType_id'] == 3 || $response['DeathSvidType_id'] == 4) ? "СЕРИЯ ".(isset($response['DeathSvid_OldSer']) ? $response['DeathSvid_OldSer'] : "")." № ".(isset($response['DeathSvid_OldNum']) ? $response['DeathSvid_OldNum'] : "") : $default_value,
					'OrgHead_Fio' => isset($response['OrgHead_Fio']) ? $response['OrgHead_Fio'] : $default_value,
					'OrgHeadPost_Name' => isset($response['OrgHeadPost_Name']) ? $response['OrgHeadPost_Name'] : $default_value,
					'BKLAddress_Summ' => isset($response['BKLAddress_Summ']) ? $response['BKLAddress_Summ'] : $default_value,
					'DKLAddress_Summ' => isset($response['DKLAddress_Summ']) ? $response['DKLAddress_Summ'] : $default_value,
					'KLAddress_Summ' => isset($response['KLAddress_Summ']) ? $response['KLAddress_Summ'] : $default_value
				);
			};
			if ($svid_type == 'pntdeath')
			{
				//Если Самара, то меняем шаблон:
				$parse_data = array(
					'PntDeathSvid_ChildBirthDT' => isset($response['PntDeathSvid_ChildBirthDT']) ? $response['PntDeathSvid_ChildBirthDT'] : $default_value,
					'PntDeathGetBirth_Code' => isset($response['PntDeathGetBirth_Code']) ? $response['PntDeathGetBirth_Code'] : $default_value,
					'PntDeathSvid_ChildFio' => isset($response['PntDeathSvid_ChildFio']) ? $response['PntDeathSvid_ChildFio'] : $default_value,
					'PntDeathSvid_Ser' => isset($response['PntDeathSvid_Ser']) ? $response['PntDeathSvid_Ser'] : $default_value,
					'PntDeathSvid_Num' => isset($response['PntDeathSvid_Num']) ? $response['PntDeathSvid_Num'] : $default_value,
					'PntDeathSvid_GiveDate' => isset($response['PntDeathSvid_GiveDate']) ? $response['PntDeathSvid_GiveDate'] : $default_value,
					'DeathSvidType_id' => isset($response['PntDeathSvidType_id']) ? $response['PntDeathSvidType_id'] : $default_value,
					'PntDeathSvid_disDate' => isset($response['PntDeathSvid_disDate']) ? $response['PntDeathSvid_disDate'] : $default_value,
					'Person_FIO' => isset($response['Person_FIO']) ? $response['Person_FIO'] : $default_value,
					'Sex_id' => isset($response['Sex_id']) ? $response['Sex_id'] : $default_value,
					'Person_Birthday' => isset($response['Person_BirthDay']) ? $response['Person_BirthDay'] : $default_value,
					'PntDeathSvid_PntDeathDate_Date' => isset($response['PntDeathSvid_PntDeathDate_Date']) ? $response['PntDeathSvid_PntDeathDate_Date'] : $default_value,
					'KLRGN_Name' => isset($response['KLRGN_Name']) ? $response['KLRGN_Name'] : $default_value,
					'KLSubRGN_Name' => isset($response['KLSubRGN_Name']) ? $response['KLSubRGN_Name'] : $default_value,
					'KLCity_Name' => isset($response['KLCity_Name']) ? $response['KLCity_Name'] : (isset($response['KLTown_Name'])?$response['KLTown_Name']:$default_value),
					'KLTown_Name' => isset($response['KLTown_Name']) ? $response['KLTown_Name'] : $default_value,
					'KLStreet_name' => isset($response['KLStreet_name']) ? $response['KLStreet_name'] : $default_value,
					'Address_House' => isset($response['Address_House']) ? $response['Address_House'] : $default_value,
					'Address_Corpus' => isset($response['Address_Corpus']) ? $response['Address_Corpus'] : $default_value,
					'Address_Flat' => isset($response['Address_Flat']) ? $response['Address_Flat'] : $default_value,
					'PntDeathPlace_id' => isset($response['PntDeathPlace_id']) ? $response['PntDeathPlace_id'] : $default_value,
					'Person_Age' => isset($response['Person_Age']) ? $response['Person_Age'] : $default_value,
					'PntDeathSvid_Month' => isset($response['PntDeathSvid_Month']) ? $response['PntDeathSvid_Month'] : $default_value,
					'PntDeathSvid_Day' => isset($response['PntDeathSvid_Day']) ? $response['PntDeathSvid_Day'] : $default_value,
					'PntDeathSvid_MotherFio' => isset($response['PntDeathSvid_MotherFio']) ? $response['PntDeathSvid_MotherFio'] : $default_value,
					'PntDeathFamilyStatus_Code' => isset($response['PntDeathFamilyStatus_Code']) ? $response['PntDeathFamilyStatus_Code'] : -1,
					'PntDeathFamilyStatus_Name' => isset($response['PntDeathFamilyStatus_Name']) ? $response['PntDeathFamilyStatus_Name'] : $default_value,
					'Lpu_Name' => isset($response['Lpu_Name']) ? $response['Lpu_Name'] : $default_value,
					'orgaddress_uaddress' => isset($response['orgaddress_uaddress']) ? $response['orgaddress_uaddress'] : $default_value,
					'org_okpo' => isset($response['org_okpo']) ? $response['org_okpo'] : $default_value,
					'KlareaType_id' => isset($response['KlareaType_id']) ? $response['KlareaType_id'] : $default_value,
					'DKLRGN_Name' => isset($response['DKLRGN_Name']) ? $response['DKLRGN_Name'] : $default_value,
					'DKLSubRGN_Name' => isset($response['DKLSubRGN_Name']) ? $response['DKLSubRGN_Name'] : $default_value,
					'DKLCity_Name' => isset($response['DKLCity_Name']) ? $response['DKLCity_Name'] : $default_value,
					'DKLTown_Name' => isset($response['DKLTown_Name']) ? $response['DKLTown_Name'] : $default_value,
					'DKLStreet_name' => isset($response['DKLStreet_name']) ? $response['DKLStreet_name'] : $default_value,
					'DAddress_House' => isset($response['DAddress_House']) ? $response['DAddress_House'] : $default_value,
					'DAddress_Flat' => isset($response['DAddress_Flat']) ? $response['DAddress_Flat'] : $default_value,
					'DKlareaType_id' => isset($response['DKlareaType_id']) ? $response['DKlareaType_id'] : $default_value,
					'PntDeathSvid_Mass' => isset($response['PntDeathSvid_Mass']) ? $response['PntDeathSvid_Mass'] : $default_value,
					'PntDeathSvid_Height' => isset($response['PntDeathSvid_Height']) ? $response['PntDeathSvid_Height'] : $default_value,
					'PntDeathSvid_ChildCount' => isset($response['PntDeathSvid_ChildCount']) ? $response['PntDeathSvid_ChildCount'] : $default_value,
					'PntDeathEducation_Code' => isset($response['PntDeathEducation_Code']) ? $response['PntDeathEducation_Code'] : $default_value,
					'PntDeathZanat_id' => isset($response['PntDeathZanat_id']) ? $response['PntDeathZanat_id'] : $default_value,
					'PntDeathCause_id' => isset($response['PntDeathCause_id']) ? $response['PntDeathCause_id'] : $default_value,
					'PntDeathSvid_PribPeriod' => isset($response['PntDeathSvid_PribPeriod']) ? $response['PntDeathSvid_PribPeriod'] : $default_value,
					'PntDeathSvid_TimePeriod' => isset($response['PntDeathSvid_TimePeriod']) ? $response['PntDeathSvid_TimePeriod'] : $default_value,
					'Diag1_Name' => isset($response['Diag1_Name']) ? $response['Diag1_Name'] : $default_value,
					'Diag2_Name' => isset($response['Diag2_Name']) ? $response['Diag2_Name'] : $default_value,
					'Diag3_Name' => isset($response['Diag3_Name']) ? $response['Diag3_Name'] : $default_value,
					'Diag4_Name' => isset($response['Diag4_Name']) ? $response['Diag4_Name'] : $default_value,
					'Diag5_Name' => isset($response['Diag5_Name']) ? $response['Diag5_Name'] : $default_value,				
					'D11' => isset($response['Diag1_Code']) && isset($response['Diag1_Code'][0]) ? $response['Diag1_Code'][0] : $default_value,
					'D12' => isset($response['Diag1_Code']) && isset($response['Diag1_Code'][1]) ? $response['Diag1_Code'][1] : $default_value,
					'D13' => isset($response['Diag1_Code']) && isset($response['Diag1_Code'][2]) ? $response['Diag1_Code'][2] : $default_value,
					'D14' => isset($response['Diag1_Code']) && isset($response['Diag1_Code'][3]) ? $response['Diag1_Code'][3] : $default_value,
					'D15' => isset($response['Diag1_Code']) && isset($response['Diag1_Code'][4]) ? $response['Diag1_Code'][4] : $default_value,				
					'D21' => isset($response['Diag2_Code']) && isset($response['Diag2_Code'][0]) ? $response['Diag2_Code'][0] : $default_value,
					'D22' => isset($response['Diag2_Code']) && isset($response['Diag2_Code'][1]) ? $response['Diag2_Code'][1] : $default_value,
					'D23' => isset($response['Diag2_Code']) && isset($response['Diag2_Code'][2]) ? $response['Diag2_Code'][2] : $default_value,
					'D24' => isset($response['Diag2_Code']) && isset($response['Diag2_Code'][3]) ? $response['Diag2_Code'][3] : $default_value,
					'D25' => isset($response['Diag2_Code']) && isset($response['Diag2_Code'][4]) ? $response['Diag2_Code'][4] : $default_value,				
					'D31' => isset($response['Diag3_Code']) && isset($response['Diag3_Code'][0]) ? $response['Diag3_Code'][0] : $default_value,
					'D32' => isset($response['Diag3_Code']) && isset($response['Diag3_Code'][1]) ? $response['Diag3_Code'][1] : $default_value,
					'D33' => isset($response['Diag3_Code']) && isset($response['Diag3_Code'][2]) ? $response['Diag3_Code'][2] : $default_value,
					'D34' => isset($response['Diag3_Code']) && isset($response['Diag3_Code'][3]) ? $response['Diag3_Code'][3] : $default_value,
					'D35' => isset($response['Diag3_Code']) && isset($response['Diag3_Code'][4]) ? $response['Diag3_Code'][4] : $default_value,				
					'D41' => isset($response['Diag4_Code']) && isset($response['Diag4_Code'][0]) ? $response['Diag4_Code'][0] : $default_value,
					'D42' => isset($response['Diag4_Code']) && isset($response['Diag4_Code'][1]) ? $response['Diag4_Code'][1] : $default_value,
					'D43' => isset($response['Diag4_Code']) && isset($response['Diag4_Code'][2]) ? $response['Diag4_Code'][2] : $default_value,
					'D44' => isset($response['Diag4_Code']) && isset($response['Diag4_Code'][3]) ? $response['Diag4_Code'][3] : $default_value,
					'D45' => isset($response['Diag4_Code']) && isset($response['Diag4_Code'][4]) ? $response['Diag4_Code'][4] : $default_value,				
					'D51' => isset($response['Diag5_Code']) && isset($response['Diag5_Code'][0]) ? $response['Diag5_Code'][0] : $default_value,
					'D52' => isset($response['Diag5_Code']) && isset($response['Diag5_Code'][1]) ? $response['Diag5_Code'][1] : $default_value,
					'D53' => isset($response['Diag5_Code']) && isset($response['Diag5_Code'][2]) ? $response['Diag5_Code'][2] : $default_value,
					'D54' => isset($response['Diag5_Code']) && isset($response['Diag5_Code'][3]) ? $response['Diag5_Code'][3] : $default_value,
					'D55' => isset($response['Diag5_Code']) && isset($response['Diag5_Code'][4]) ? $response['Diag5_Code'][4] : $default_value,				
					'PntDeathSvid_PolFio' => isset($response['PntDeathSvid_PolFio']) ? $response['PntDeathSvid_PolFio'] : $default_value,
					'PntDeathSvid_PolDoc' => isset($response['PntDeathSvid_PolDoc']) ? $response['PntDeathSvid_PolDoc'] : $default_value,
					'PntDeathSvid_PolDate' => isset($response['PntDeathSvid_PolDate']) ? $response['PntDeathSvid_PolDate'] : $default_value,
					'PntDeathSetType_id' => isset($response['PntDeathSetType_id']) ? $response['PntDeathSetType_id'] : $default_value,
					'MedPersonal_Post' => isset($response['MedPersonal_Post']) ? $response['MedPersonal_Post'] : $default_value,
					'PntDeathSetCause_id' => isset($response['PntDeathSetCause_id']) ? $response['PntDeathSetCause_id'] : $default_value,
					'MedPersonal_FIO' => isset($response['MedPersonal_FIO']) ? $response['MedPersonal_FIO'] : $default_value,
					'DeathSvidType_Name' => isset($response['DeathSvidType_Name']) ? $response['DeathSvidType_Name'] : $default_value,
					'PntDeathSvid_IsMnogoplod_Checked' => isset($response['PntDeathSvid_IsMnogoplod']) ? ($response['PntDeathSvid_IsMnogoplod'] == 1 ? 'checked="checked"' : "") : "",
					'PntDeathSvid_PlodIndex' => isset($response['PntDeathSvid_PlodIndex']) ? $response['PntDeathSvid_PlodIndex'] : $default_value,
					'PntDeathSvid_PlodCount' => isset($response['PntDeathSvid_PlodCount']) ? $response['PntDeathSvid_PlodCount'] : $default_value,
					'PntDeathTime_id' => isset($response['PntDeathTime_id']) ? $response['PntDeathTime_id'] : $default_value,
					'PntDeathBirthCause_id' => isset($response['PntDeathBirthCause_id']) ? $response['PntDeathBirthCause_id'] : $default_value,
					'BirthPlace_id' => isset($response['BirthPlace_id']) ? $response['BirthPlace_id'] : $default_value,
					'PntDeathSvid_DeathDate' => isset($response['PntDeathSvid_DeathDate']) ? $response['PntDeathSvid_DeathDate'] : $default_value,
					'subhead' => isset($response['PntDeathSvidType_id']) && ($response['PntDeathSvidType_id'] == 3 || $response['PntDeathSvidType_id'] == 4) ? "СЕРИЯ ".(isset($response['PntDeathSvid_OldSer']) ? $response['PntDeathSvid_OldSer'] : "")." № ".(isset($response['PntDeathSvid_OldNum']) ? $response['PntDeathSvid_OldNum'] : "") : $default_value,
					'PntDeathSvid_Condition_1' => isset($response['PntDeathSvid_ChildBirthDT']) && isset($response['PntDeathTime_id']) && ($response['PntDeathTime_id'] == 1 || $response['PntDeathTime_id'] == 2) ? $response['PntDeathSvid_ChildBirthDT'].' '.$response['PntDeathSvid_ChildBirthDT_Time'] : $default_value, //вычисляемые значения
					'PntDeathSvid_Condition_2_1' => isset($response['PntDeathSvid_ChildBirthDT']) && isset($response['PntDeathTime_id']) && ($response['PntDeathTime_id'] != 1 && $response['PntDeathTime_id'] != 2) ? $response['PntDeathSvid_ChildBirthDT'].' '.$response['PntDeathSvid_ChildBirthDT_Time'] : $default_value, 
					'PntDeathSvid_Condition_2_2' => isset($response['PntDeathSvid_DeathDate']) && isset($response['PntDeathTime_id']) && ($response['PntDeathTime_id'] != 1 && $response['PntDeathTime_id'] != 2) ? $response['PntDeathSvid_DeathDate'].' '.$response['PntDeathSvid_DeathDate_Time'] : $default_value,
					'OrgHead_Fio' => isset($response['OrgHead_Fio']) ? $response['OrgHead_Fio'] : $default_value,
					'OrgHeadPost_Name' => isset($response['OrgHeadPost_Name']) ? $response['OrgHeadPost_Name'] : $default_value,
					'DKLAddress_Summ' => isset($response['DKLAddress_Summ']) ? $response['DKLAddress_Summ'] : $default_value,
					'KLAddress_Summ' => isset($response['KLAddress_Summ']) ? $response['KLAddress_Summ'] : $default_value,
					'DeputyKind_Name' => isset($response['DeputyKind_Name']) ? "(".$response['DeputyKind_Name'].")" : $default_value,
					'PntDeathSvid_ActNumber' => isset($response['PntDeathSvid_ActNumber']) ? $response['PntDeathSvid_ActNumber'] : $default_value,
					'PntDeathSvid_ActDT' => isset($response['PntDeathSvid_ActDT']) ? $response['PntDeathSvid_ActDT'] : $default_value,
					'Org_Name' => isset($response['Org_Name']) ? $response['Org_Name'] : $default_value,
					'PntDeathSvid_ZagsFIO' => isset($response['PntDeathSvid_ZagsFIO']) ? $response['PntDeathSvid_ZagsFIO'] : $default_value
				);
			};
			$result = $this->parser->parse($template, $parse_data, $return);
		} else {
			$ser_num = $this->getMedSvidSerNumData($svid_type, false);
			if (!empty($ser_num['Error_Msg'])) {
				throw new Exception($ser_num['Error_Msg']);
			}
			$result = $this->parser->parse($template.'_blank', array('Svid_Ser' => $ser_num['ser'], 'Svid_Num' => $ser_num['num']), $return);

		}
		
		return $result;
	}
	
	/**
	 * Получение серии и номера медицинского свидетельства
	 */
	function getMedSvidSerNum() {
		$val = $this->getMedSvidSerNumData('default');
		array_walk($val, 'ConvertFromWin1251ToUTF8');
		$this->ReturnData($val);
	}

	/**
	 * Печать бланков
	 */
	function printBlanks()
	{
		$data = $this->ProcessInputData('printBlanks', true);
		if ($data === false) {
			return false;
		}

		$this->load->model('ReportRun_model');
		$k = 0;

		$path = EXPORTPATH_ROOT."medsvid_blanks/";
		if (!file_exists($path)) {
			mkdir( $path );
		}
		$out_dir = "blanks_".$data['Lpu_id'].'_'.$data['Numerator_IntNum'].'_'.time().'/';
		if (!file_exists($path.$out_dir)) {
			mkdir( $path.$out_dir );
		}

		$zip_file_path = $path.$out_dir.'blanks.zip';
		$zip = new ZipArchive();
		$zip->open($zip_file_path, ZIPARCHIVE::CREATE);

		$Numerator_Nums = array();
		if (!empty($data['Numerator_Nums'])) {
			$Numerator_Nums = json_decode($data['Numerator_Nums'], true);
		} else {
			for ($i = 0; $i < $data['BlankCount']; $i++) {
				$Numerator_Nums[] = $data['Numerator_IntNum'] + $i;
			}
		}

		foreach($Numerator_Nums as $Num) {
			$data['Numerator_Num'] = $Num;
			if (!empty($data['Numerator_PreNum'])) {
				$data['Numerator_Num'] = $data['Numerator_PreNum'] . $data['Numerator_Num'];
			}
			if (!empty($data['Numerator_PostNum'])) {
				$data['Numerator_Num'] = $data['Numerator_Num'] . $data['Numerator_PostNum'];
			}
			$data['Report_Params'] = urlencode('&paramNumStart=' . $data['Numerator_Num'] . '&paramSer=' . $data['Numerator_Ser']);
			$data['Report_Format'] = 'pdf';

			switch ($data['svid_type']) {
				case 'death':
					$data['Report_FileName'] = $this->usePostgre?'DeathSvid_empt_pg.rptdesign':'DeathSvid_empt.rptdesign';
					$response = $this->ReportRun_model->RunByFileName($data, true);
					$k++;
					$pdf_file_path = $path . $out_dir . $k . '.pdf';
					file_put_contents($pdf_file_path, $response);
					$zip->AddFile($pdf_file_path, $k . '.pdf');
					if (!empty($data['DoubleSide'])) {
						$data['Report_FileName'] = $this->usePostgre?'DeathSvid_Oborot_empt_pg.rptdesign':'DeathSvid_Oborot_empt.rptdesign';
						$response = $this->ReportRun_model->RunByFileName($data, true);
						$k++;
						$pdf_file_path = $path . $out_dir . $k . '.pdf';
						file_put_contents($pdf_file_path, $response);
						$zip->AddFile($pdf_file_path, $k . '.pdf');
					}
					break;
				case 'birth':
					$data['Report_FileName'] = $this->usePostgre?'BirthSvid_pg.rptdesign':'BirthSvid.rptdesign';
					$response = $this->ReportRun_model->RunByFileName($data, true);
					$k++;
					$pdf_file_path = $path . $out_dir . $k . '.pdf';
					file_put_contents($pdf_file_path, $response);
					$zip->AddFile($pdf_file_path, $k . '.pdf');

					$data['Report_FileName'] = $this->usePostgre?'BirthSvid_check_pg.rptdesign':'BirthSvid_check.rptdesign';// #133782
					$response = $this->ReportRun_model->RunByFileName($data, true);
					$pdf_file_path = $path . $out_dir . $k . 'a.pdf';
					file_put_contents($pdf_file_path, $response);
					$zip->AddFile($pdf_file_path, $k . 'a.pdf');
					break;
				case 'pntdeath':
					$data['Report_FileName'] = $this->usePostgre?'PntDeathSvid_empt_pg.rptdesign':'PntDeathSvid_empt.rptdesign';
					$response = $this->ReportRun_model->RunByFileName($data, true);
					$k++;
					$pdf_file_path = $path . $out_dir . $k . '.pdf';
					file_put_contents($pdf_file_path, $response);
					$zip->AddFile($pdf_file_path, $k . '.pdf');
					if (!empty($data['DoubleSide'])) {
						$data['Report_FileName'] = $this->usePostgre?'PntDeathSvid_Oborot_empt_pg.rptdesign':'PntDeathSvid_Oborot_empt.rptdesign';
						$response = $this->ReportRun_model->RunByFileName($data, true);
						$k++;
						$pdf_file_path = $path . $out_dir . $k . '.pdf';
						file_put_contents($pdf_file_path, $response);
						$zip->AddFile($pdf_file_path, $k . '.pdf');
					}
					break;
			}
		}

		if (empty($data['DoubleSide'])) {
			switch ($data['svid_type']) {
				case 'death':
					$data['Report_FileName'] = $this->usePostgre?'DeathSvid_Oborot_empt_pg.rptdesign':'DeathSvid_Oborot_empt.rptdesign';
					$response = $this->ReportRun_model->RunByFileName($data, true);
					$k++;
					$pdf_file_path = $path.$out_dir.$k.'.pdf';
					file_put_contents($pdf_file_path, $response);
					$zip->AddFile($pdf_file_path, $k.'.pdf');
					break;
				case 'pntdeath':
					$data['Report_FileName'] = $this->usePostgre?'PntDeathSvid_Oborot_empt_pg.rptdesign':'PntDeathSvid_Oborot_empt.rptdesign';
					$response = $this->ReportRun_model->RunByFileName($data, true);
					$k++;
					$pdf_file_path = $path.$out_dir.$k.'.pdf';
					file_put_contents($pdf_file_path, $response);
					$zip->AddFile($pdf_file_path, $k.'.pdf');
					break;
			}
		}

		$zip->close();

		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: public");
		header("Content-Description: File Transfer");
		header("Content-type: application/octet-stream");
		header("Content-Disposition: attachment; filename=\"blanks.zip\"");
		header("Content-Transfer-Encoding: binary");
		//header("Content-Length: ".filesize($zip_file_path));

		@readfile($zip_file_path);
	}

	/**
	 * Резервирование номеров для свидетельств
	 */
	function reserveNums() {
		$data = $this->ProcessInputData('reserveNums', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->reserveNums($data);
		$this->ProcessModelSave($response, true, 'Ошибка резервирования номеров')->ReturnData();

		return true;
	}
	
	
	/**
	 * Генерация серии и номера медицинского свидетельства
	 * Выполняется генерация нового номера, когда $showOnly = false, в противном
	 * случае из базы будет получен последний сгенерированный номер + 1
	 */
	function getMedSvidSerNumData($svid_type, $showOnly = true) {

		$val  = array();

		// Получаем сессионные переменные
		$data = $this->ProcessInputData('getMedSvidSerNum', true);
		if ($data === false) { return false; }

		if (in_array($svid_type, array('birth', 'death', 'pntdeath'))) {
			$data['svid_type'] = $svid_type;
		}

		$this->load->model('Options_model', 'opmodel');
		$options = $this->opmodel->getDataStorageOptions($data);
		/*$region_nick = isset($_SESSION['region']) && isset($_SESSION['region']['nick']) ? $_SESSION['region']['nick'] : 'undefined';
		if ( in_array($region_nick, array('ufa', 'khak')) ) { //для уфы и хакасии: получаем серию и начальный номер из DataStorage
			$default_ser = ($region_nick=='khak')?'95':'0000';
			$data['base_ser'] = isset($options['medsvid']) && isset($options['medsvid']['medsvid_ser']) ? $options['medsvid']['medsvid_ser'] : $default_ser;
			ConvertFromUTF8ToWin1251($data['base_ser']);
			//$data['base_num'] = isset($options['medsvid']) && isset($options['medsvid']['medsvid_num']) ? $options['medsvid']['medsvid_num'] : '0000';
		}
		
		$data['region_nick'] = $region_nick;*/

		$data['showOnly'] = $showOnly;
		$numData = $this->dbmodel->getMedSvidNum($data);
		if (!empty($numData['Error_Msg'])) {
			return $numData;
		}
		$val['num'] = $numData['Numerator_Num'];
		$val['intnum'] = $numData['Numerator_IntNum'];
		$val['prenum'] = $numData['Numerator_PreNum'];
		$val['postnum'] = $numData['Numerator_PostNum'];
		$val['ser'] = $numData['Numerator_Ser'];
		$val['success'] = true;

		return $val;
	}

	/**
	 * Удаление мед. свидетельств при отмене сохранения движения в КВС
	 */
	function deleteMedSvidFromEvnSection()
	{
		$data = $this->ProcessInputData('deleteMedSvidFromEvnSection', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteMedSvidFromEvnSection($data);
		$this->ProcessModelSave($response, true,true)->ReturnData();
		return true;
	}

	/**
	 * Проверка существования свидетельства о рождении, выписанного на основании текущего
	 */
	function checkBirthSvidExist()
	{
		$data = $this->ProcessInputData('checkBirthSvidExist', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkBirthSvidExist($data);
		$this->ProcessModelSave($response, true,'Ошибка проверки существования свидетельства')->ReturnData();
		return true;
	}

	/**
	 * Проверка объёма "Выбор диагнозов COVID-19 в МСС"
	 */
	function checkCOVIDVolume()
	{
		$data = $this->ProcessInputData('checkCOVIDVolume', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->checkCOVIDVolume($data);
		$this->ProcessModelSave($response, true,'Ошибка проверки существования объёма')->ReturnData();
		return true;
	}

	/**
	 * Проверка существования свидетельства о смерти, выписанного на основании текущего
	 */
	function checkDeathSvidExist()
	{
		$data = $this->ProcessInputData('checkDeathSvidExist', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkDeathSvidExist($data);
		$this->ProcessModelSave($response, true,'Ошибка проверки существования свидетельства')->ReturnData();
		return true;
	}

	/**
	 * deleteMedSvidPntDeath
	  */
	function deleteMedSvidPntDeath()
	{
		$this->deleteMedSvid('pntdeath');
	}

	/**
	 * Удаление мед. свидетельства
	 */
	function deleteMedSvid($svid_type)
	{
		if ($svid_type == 'pntdeath') $data = $this->ProcessInputData('deleteMedSvidPntDeath', true);
		if ($svid_type == 'birth') $data = $this->ProcessInputData('deleteMedSvidBirth', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteMedSvid($data, $svid_type);
		$this->ProcessModelSave($response, true,true)->ReturnData();
		return true;
	}

	/**
	 * TO-DO: Это что такое?
	 */
	function BirthdayToAge($birthday) {
		$age = 0;
		
		/*
		var nowdate = new Date(); //вычисление возраста матери
		var birdate = base_form.findField('Mother_BirthDay').getValue();
		if (birdate != "") Ext.getCmp('Mother_Age').setValue(nowdate.format('Y')-birdate.format('Y')-(((birdate.format('md')*1)-(nowdate.format('md')*1)) > 0 ? 1 : 0));
		*/
		return $age;
	}
	
	
	/**
	 * Проверка не заведено ли уже для человека свидетельство о рождении
	 */
	function getDoubleBirthSvidCnt() {
		$data = $this->ProcessInputData('getDoubleBirthSvidCnt', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getDoubleBirthSvidCnt($data);
		$this->ProcessModelSave($response, true,true)->ReturnData();
		return true;
	}
	
	
	/**
	 * Проверка, не заведено-ли уже свидетельство, делается перед созданием свидетельства на основе существующего
	 */
	function checkMedSvidSimple() {
		$val  = array();

		$data = $this->ProcessInputData('checkMedSvidSimple', true);
		if ($data === false) { return false; }
		
		$val = $this->dbmodel->checkMedSvidSimple($data['svid_id'], $data['svid_type']);
		array_walk($val, 'ConvertFromWin1251ToUTF8');
		$this->ReturnData($val);
	}

    /**
     * Получение имени другой МО
     */
    function getAnotherMOName() {

        $data = $this->ProcessInputData('getAnotherMOName', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->getAnotherMOName($data);
        $this->ProcessModelList($response, true,true)->ReturnData();

        return true;
    }
	
	/**
	 * Установка места рождения по умолчанию
	 */
	function getDefaultBirthAddress() {
		$val  = array();

		$data = $this->ProcessInputData('getDefaultBirthAddress', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->getDefaultBirthAddress($data);
		$this->ProcessModelList($response, true,true)->ReturnData();

		return true;
	}

	/**
	 * Получение DeathDiag
	 */
	function getDeathDiagStore() {
		$val  = array();

		$data = $this->ProcessInputData('getDeathDiagStore', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getDeathDiagStore($data);
		$this->ProcessModelList($response, true,true)->ReturnData();

		return true;
	}

	/**
	 * Получение адреса пациента
	 */
	function getPacientUAddress() {
		$data = $this->ProcessInputData('getPacientUAddress', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getPacientUAddress($data);
		$this->ProcessModelSave($response, true, 'Ошибка получения адреса пациента')->ReturnData();

		return true;
	}
	/**
	 * Получение адреса рождения пациента
	 */
	function getPacientBAddress() {
		$data = $this->ProcessInputData('getPacientBAddress', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getPacientBAddress($data);
		$this->ProcessModelSave($response, true, 'Ошибка получения адреса рождения пациента')->ReturnData();

		return true;
	}
	/**
	 * Получение адреса пациента
	 */
	function getPacientDeathAddress() {
		$data = $this->ProcessInputData('getPacientDeathAddress', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getPacientDeathAddress($data);
		$this->ProcessModelSave($response, true, 'Ошибка получения адреса пациента')->ReturnData();

		return true;
	}
	
	/**
	 * Функция для получения значений по умолчанию, связанных с данными о новорожденном	 
	 */
	function getDefaultPersonChildValues() {
		$data = $this->ProcessInputData('getDefaultPersonChildValues', true);
		if ($data === false) { return false; }		
		
		$response = $this->dbmodel->getDefaultPersonChildValues($data);
		$this->ProcessModelList($response, true,true)->ReturnData();
		
		return true;
	}

	/**
	 * Получаем правила подписания свидетельства
	 */
	function getEmdSignatureRules(){
		$data = $this->ProcessInputData('getEmdSignatureRules', true);
		if ($data === false) { return false; }

		$this->load->model('EMD_model');
		$response = $this->EMD_model->getEmdSignatureRules($data);

		$this->ProcessModelList($response, true,true)->ReturnData();
	}

    /**
     * Функция для проверки на диагноз "R54"
     */
    function checkR54diagnose() {
        $data = $this->ProcessInputData('checkR54diagnose', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->checkR54diagnose($data);
        $this->ReturnData($response);

        return true;
    }

}

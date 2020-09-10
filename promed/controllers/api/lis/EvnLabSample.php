<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * Class EvnLabSample
 * @OA\Tag(
 *     name="EvnLabSample",
 *     description="Пробы"
 * )
 *
 * @property EvnLabSample_model dbmodel
 * @property AsMlo_model AsMlo_model
 */
class EvnLabSample extends SwREST_Controller {
	protected  $inputRules = array(
		'getEvnLabSampleFromLisWithResultCount' => array(
			array('field' => 'MedService_id', 'label' => 'Идентификатор службы', 'rules' => 'required', 'type' => 'id'),
		),
		'approveEvnLabSampleResults' => array(
			array('field' => 'EvnLabSamples', 'label' => 'EvnLabSamples', 'rules' => '', 'type' => 'string'),
			array('field' => 'MedService_IsQualityTestApprove', 'label' => 'MedService_IsQualityTestApprove', 'rules' => '', 'type' => 'string'),
			array('field' => 'MedService_id', 'label' => 'MedService_id', 'rules' => '', 'type' => 'id'),
			array('field' => 'onlyNormal', 'label' => 'onlyNormal', 'rules' => '', 'type' => 'int')
		),
		'loadRefValues' => array(
			array('field' => 'EvnLabSample_setDT', 'label' => 'EvnLabSample_setDT', 'rules' => '', 'type' => 'datetime'),
			array('field' => 'MedService_id', 'label' => 'Служба', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnDirection_id', 'label' => 'Направление', 'rules' => '', 'type' => 'id'),
			array('field' => 'Person_id', 'label' => 'Человек', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'UslugaComplexTarget_id', 'label' => 'Услуга исследования', 'rules' => '', 'type' => 'id'),
			array('field' => 'UslugaComplex_ids', 'label' => 'Список услуг', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'Analyzer_id', 'label' => 'Анализатор', 'rules' => '', 'type' => 'id'),
		),
		'saveNewEvnLabSampleBarCode' => array(
			array('field' => 'EvnLabSample_id', 'label' => 'Идентификатор пробы', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnLabSample_BarCode', 'label' => 'Штрих-код', 'rules' => 'required', 'type' => 'string'),
		),
		'saveNewEvnLabSampleNum' => array(
			array('field' => 'EvnLabSample_id', 'label' => 'Идентификатор пробы', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnLabSample_ShortNum', 'label' => 'Номер', 'rules' => 'required', 'type' => 'int'),
		),
		'takeLabSample' => array(
			array('field' => 'MedServiceType_SysNick', 'label' => 'MedServiceType_SysNick', 'rules' => '', 'type' => 'string'),
			array('field' => 'MedService_did', 'label' => 'Служба, где взята проба', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnLabRequest_id', 'label' => 'Заявка', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnLabSample_id', 'label' => 'Проба', 'rules' => '', 'type' => 'id'),
			array('field' => 'RefSample_id', 'label' => 'Проба', 'rules' => '', 'type' => 'id'),
			array('field' => 'sendToLis', 'label' => 'sendToLis', 'rules' => '', 'type' => 'int'),
		),
		'getNewListEvnLabSampleNum' => array(
			array('field' => 'MedService_id', 'label' => 'Служба, для которой печатаются номера', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'quantity', 'label' => 'количество номеров', 'rules' => 'required', 'type' => 'int'),
		),
		'saveLabSample' => array(
			array('field' => 'MedService_id', 'label' => 'Служба', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnLabRequest_id', 'label' => 'Заявка', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'RefSample_id', 'label' => 'Проба', 'rules' => '', 'type' => 'id'),
		),
		'saveLabSampleResearches' => array(
			array('field' => 'MedService_id', 'label' => 'Служба', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnLabRequest_id', 'label' => 'Заявка', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'RefSample_id', 'label' => 'Проба', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnLabSample_id', 'label' => 'Идентификатор пробы', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'researches', 'label' => 'researches', 'rules' => '', 'type' => 'json_array'),
		),
		'saveEvnLabSampleDefect' => array(
			array('field' => 'DefectCauseType_id', 'label' => 'Идентификатор типа брака', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnLabSample_id', 'label' => 'Идентификатор пробы', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnLabSample_BarCode', 'label' => 'Штрих-код пробы', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'MedServiceType_SysNick', 'label' => 'Арм', 'rules' => '', 'type' => 'string'),
			array('field' => 'MedService_sid', 'label' => 'Текущая служба', 'rules' => '', 'type' => 'id'),
		),
		'deleteEvnLabSampleDefect' => array(
			array('field' => 'EvnLabSample_id', 'label' => 'Идентификатор пробы', 'rules' => 'required', 'type' => 'id'),
		),
		'loadLabSampleFrame' => array(
			array('field' => 'EvnLabRequest_id', 'label' => 'Заявка на лабораторное обследование', 'rules' => '', 'type' => 'id'),
			array('field' => 'UslugaComplex_id', 'label' => 'Выбранная корневая комплекснеая услуга в заявке', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedService_id', 'label' => 'Служба', 'rules' => '', 'type' => 'id'),
		),
		'updateResult' => array(
			array('field' => 'UslugaTest_id', 'label' => 'Идентификатор теста', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'UslugaTest_ResultValue', 'label' => 'Результат', 'rules' => 'notnull|trim', 'type' => 'string'),
			array('field' => 'UslugaTest_setDT', 'label' => 'Дата выполнения теста', 'rules' => 'trim', 'type' => 'datetime'),
			array('field' => 'UslugaTest_Unit', 'label' => 'Единицы измерения', 'rules' => 'notnull', 'type' => 'string'),
			array('field' => 'UslugaTest_Comment', 'label' => 'Комментарий', 'rules' => '', 'type' => 'string'),
			array('field' => 'UslugaTest_RefValues', 'label' => 'Референсные значения', 'rules' => 'notnull', 'type' => 'string'),
			array('field' => 'updateType', 'label' => 'Тип', 'rules' => '', 'type' => 'string'),
			array('field' => 'sourceName', 'label' => 'Источник', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnLabSample_id', 'label' => 'id пробы', 'rules' => '', 'type'  => 'string'),
			array('field' => 'UslugaTest_Code', 'label' => 'Код теста', 'rules' => '', 'type' => 'string'),
		),
		'getOverdueSamples' => array(
			array('field' => 'MedService_id', 'label' => 'Идентификатор лаборатории', 'rules' => 'required', 'type' => 'id'),
		),
		'prescrTest' => array(
			array('field' => 'EvnLabSample_id', 'label' => 'Идентификатор Пробы', 'rules' => '', 'type' => 'id'),
			array('field' => 'tests', 'label' => 'Идентификаторы услуг тестов', 'rules' => '', 'type' => 'json_array', 'assoc' => true),
			array('field' => 'EvnLabRequest_id', 'label' => 'Заявка на лабораторное обследование', 'rules' => '', 'type' => 'id'),
		),
		'cancelTest' => array(
			array('field' => 'EvnLabSample_id', 'label' => 'Идентификатор Пробы', 'rules' => '', 'type' => 'id'),
			array('field' => 'tests', 'label' => 'Идентификаторы услуг тестов', 'rules' => '', 'type' => 'json_array', 'assoc' => true),
			array('field' => 'EvnLabRequest_id', 'label' => 'Заявка на лабораторное обследование', 'rules' => '', 'type' => 'id'),
		),
		'transferLabSampleResearches' => array(
			array('field' => 'EvnLabSample_oldid', 'label' => 'Идентификатор старой пробы', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnLabSample_newid', 'label' => 'Идентификатор новой пробы', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'tests', 'label' => 'Тесты', 'rules' => 'required', 'type' => 'json_array', 'assoc' => true),
		),
		'approveResults' => array(
			array('field' => 'EvnLabSample_id', 'label' => 'Идентификатор Пробы', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'UslugaTest_id', 'label' => 'Идентификатор теста', 'rules' => '', 'type' => 'id'),
			array('field' => 'UslugaTest_ids', 'label' => 'Идентификаторы тестов', 'rules' => '', 'type' => 'string'),
			array('field' => 'onlyNorm', 'label' => 'Признак одобрения только результатов в норме', 'rules' => '', 'type' => 'checkbox'),
			array('field' => 'MedServiceType_SysNick', 'label' => 'Тип службы', 'rules' => '', 'type' => 'string')
		),
		'getSampleUsluga' => array(
			array('field' => 'EvnLabSample_id', 'label' => 'Идентификатор Пробы', 'rules' => 'required', 'type' => 'string'),
		),
		'saveLabSampleAnalyzer' => array(
			array('field' => 'EvnLabSamples', 'label' => 'Идентификатор пробы', 'rules' => '', 'type' => 'string'),
			array('field' => 'Analyzer_id', 'label' => 'Идентификатор анализатора', 'rules' => '', 'type' => 'id'),
		),
		'unapproveResults' => array(
			array('field' => 'EvnLabSample_id', 'label' => 'Идентификатор Пробы', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'UslugaTest_id', 'label' => 'Идентификатор теста', 'rules' => '', 'type' => 'id'),
			array('field' => 'UslugaTest_ids', 'label' => 'Идентификаторы тестов', 'rules' => '', 'type' => 'string'),
			array('field' => 'MedServiceType_SysNick', 'label' => 'Тип службы', 'rules' => '', 'type' => 'string')
		),
		'getLabSampleResultGrid' => array(
			array('field' => 'EvnDirection_id' ,'label' => 'Заявка','rules' => '','type' => 'id'),
			array('field' => 'EvnLabSample_id' ,'label' => 'Проба','rules' => '','type' => 'id'),
			array('field' => 'RefSample_id' ,'label' => 'Проба','rules' => '','type' => 'id'),
			array('field' => 'MethodsIFA_id', 'label' => 'Методика ИФА', 'rules' => '', 'type' => 'int'),
			array('field' => 'AnalyzerTest_id', 'label' => 'Исследование', 'rules' => '', 'type' => 'int'),
			array('field' => 'formMode', 'label' => 'Режим формы', 'rules' => '', 'type' => 'string'),
		),
		'loadWorkList' => array(
			array('field' => 'EvnLabSample_id','label' => 'Идентификатор Пробы','rules' => '','type' => 'id'),
			array('field' => 'EvnDirection_IsCito','label' => 'Cito!','rules' => '','type' => 'id', 'default' => null),
			array('field' => 'EvnLabSample_IsOutNorm','label' => 'Отклонение','rules' => '','type' => 'id', 'default' => null),
			array('field' => 'begDate','label' => 'Начало периода','rules' => '','type' => 'date', 'default' => null),
			array('field' => 'endDate','label' => 'Конец периода','rules' => '','type' => 'date', 'default' => null),
			array('field' => 'LpuSection_id','label' => 'Отделение','rules' => '','type' => 'id'),
			array('field' => 'MedPersonal_id','label' => 'Врач','rules' => '','type' => 'id'),
			array('field' => 'LabSampleStatus_id','label' => 'Статус пробы','rules' => '','type' => 'id'),
			array('field' => 'MedService_id','label' => 'Служба','rules' => 'required','type' => 'id'),
			array('field' => 'Person_ShortFio','label' => 'ФИО','rules' => '','type' => 'string'),
			array('field' => 'EvnDirection_Num','label' => 'Номер направления','rules' => '','type' => 'string'),
			array('field' => 'EvnLabSample_BarCode','label' => 'Штрих-код','rules' => '','type' => 'string'),
			array('field' => 'MedServiceType_SysNick','label' => 'Тип службы','rules' => '','type' => 'string'),
			array('field' => 'EvnLabSample_ShortNum','label' => 'Номер пробы','rules' => '','type' => 'string'),
			array('field' => 'filterNewELSByDate','label' => 'Фильтровать новые пробы по дате','rules' => '','type' => 'int'),
			array('field' => 'filterWorkELSByDate','label' => 'Фильтровать пробы в работе по дате','rules' => '','type' => 'int'),
			array('field' => 'filterDoneELSByDate','label' => 'Фильтровать пробы с результатами по дате','rules' => '','type' => 'int'),
			array('field' => 'AnalyzerTest_id', 'label' => 'Тест', 'rules' => '', 'type' => 'int'),
			array('field' => 'MethodsIFA_id', 'label' => 'Методика ИФА', 'rules' => '', 'type' => 'int'),
			array('field' => 'formMode', 'label' => 'Режим формы', 'rules' => '', 'type' => 'string'),
			array('field' => 'Lpu_sid', 'label' => 'Медицинская организация', 'rules' => '', 'type' => 'int' ),
			array('field' => 'LpuSection_id', 'label' => 'Отделение', 'rules' => '', 'type' => 'int' ),
			array('field' => 'MedStaffFact_id', 'label' => 'Врач', 'rules' => '', 'type' => 'int' ),
			array('field' => 'UslugaComplex_id', 'label' => 'Услуга', 'rules' => '', 'type' => 'int' ),
			array('field' => 'EvnLabRequest_RegNum', 'label' => 'Регистрационный номер', 'rules' => '', 'type' => 'string' ),
		),
		'loadDefectList' => array(
			array('field' => 'EvnLabSample_id','label' => 'Идентификатор Пробы','rules' => '','type' => 'id'),
			array('field' => 'EvnDirection_IsCito','label' => 'Срочность','rules' => '','type' => 'id', 'default' => null),
			array('field' => 'begDate','label' => 'Начало периода','rules' => '','type' => 'date', 'default' => null),
			array('field' => 'endDate','label' => 'Конец периода','rules' => '','type' => 'date', 'default' => null),
			array('field' => 'DefectCauseType_id','label' => 'Причина отбраковки','rules' => '','type' => 'id'),
			array('field' => 'RefMaterial_id','label' => 'Биоматериал','rules' => '','type' => 'id'),
			array('field' => 'UslugaComplex_id','label' => 'Исследование','rules' => '','type' => 'id'),
			array('field' => 'LpuSection_id','label' => 'Отделение','rules' => '','type' => 'id'),
			array('field' => 'MedPersonal_id','label' => 'Врач','rules' => '','type' => 'id'),
			array('field' => 'MedService_id','label' => 'Служба','rules' => '','type' => 'id'),
			array('field' => 'MedService_sid','label' => 'Текущая Служба','rules' => '','type' => 'id'),
		),
		'loadListForCandiPicker' => array(
			array('field' => 'AnalyzerWorksheet_id', 'label' => 'Рабочий список', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MedService_id', 'label' => 'Служба', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnLabRequest_BarCode', 'label' => 'Фильтр по штрих-коду', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnLabSample_Num', 'label' => 'Фильтр по номеру пробы', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnLabSample_ShortNum', 'label' => 'Фильтр по номеру пробы', 'rules' => '', 'type' => 'string'),
		),
		'loadBarCode' => array(
			array('field' => 'AnalyzerWorksheet_id', 'label' => 'Идентификатор Рабочего списка', 'rules' => '', 'type' => 'id'),
		),
		'checkEvnLabSampleUnique' => array(
			array('field' => 'MedService_id', 'label' => 'Идентификатор лаборатории', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnLabSample_Num', 'label' => 'Номер пробы', 'rules' => 'required', 'type' => 'id'),
		),
		'load' => array(
			array('field' => 'EvnLabSample_id', 'label' => 'Идентификатор Пробы', 'rules' => 'required', 'type' => 'id'),
			/*array('field' => 'EvnLabRequest_BarCode', 'label' => 'Штрих код пробы', 'rules' => '', 'type' => 'int'),
			array('field' => 'MedService_id', 'label' => 'Идентификатор лаборатории', 'rules' => '', 'type' => 'id'),*/
		),
		'loadEvnLabSampleListForWorksheet' => array(
			array('field' => 'AnalyzerWorksheet_id', 'label' => 'Рабочий список', 'rules' => 'required', 'type' => 'id'),
			array('default' => 100, 'field' => 'limit', 'label' => 'Количество', 'rules' => 'trim', 'type' => 'int'),
			array('default' => 0, 'field' => 'start', 'label' => 'Старт', 'rules' => 'trim', 'type' => 'int'),
		),
		'cancel' => array(
			array('field' => 'EvnLabSample_ids', 'label' => 'Идентификаторы проб', 'rules' => 'required', 'type' => 'string'),
		),
		'saveResearch' => array(
			array('field' => 'EvnUslugaPar_id', 'label' => 'Услуга', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnUslugaPar_setDate', 'label' => 'Дата выполнения', 'rules' => '', 'type' => 'date'),
			array('field' => 'EvnUslugaPar_setTime', 'label' => 'Время выполнения', 'rules' => '', 'type' => 'string'),
			array('field' => 'Lpu_aid', 'label' => 'МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_aid', 'label' => 'Отделение', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MedPersonal_aid', 'label' => 'Врач', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedPersonal_said', 'label' => 'Ср. медперсонал', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnUslugaPar_Comment', 'label' => 'Комментарий', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'EvnUslugaPar_IndexRep', 'label' => 'Признак повтороной подачи', 'rules' => '', 'type' => 'int'),
			array('field' => 'EvnUslugaPar_IndexRepInReg', 'label' => 'Признак вхождения в реестр повтороной подачи', 'rules' => '', 'type' => 'int'),
		),
		'saveComment' => array(
			array('field' => 'EvnUslugaPar_id', 'label' => 'Услуга', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnUslugaPar_Comment', 'label' => 'Комментарий', 'rules' => 'trim', 'type' => 'string'),
		),
		'loadResearchEditForm' => array(
			array('field' => 'EvnUslugaPar_id', 'label' => 'Услуга', 'rules' => 'required', 'type' => 'id'),
		),
		'getEvnLabSample' => array(
			array('field' => 'EvnUslugaPar_id', 'label' => 'Услуга', 'rules' => 'required', 'type' => 'id'),
		),
		'getTestListForm250' => array(
			array('field' => 'Date','label' => 'Дата','rules' => '','type' => 'date', 'default' => null),
			array('field' => 'MedService_id','label' => 'Служба','rules' => 'required','type' => 'id'),
			array('field' => 'MedServiceType_SysNick', 'label' => '', 'rules' => '', 'type' => 'string'),
			array('field' => 'UslugaComplex_id','label' => 'Услуга','rules' => '','type' => 'id'),
		),
		'loadSampleListForm250' => array(
			array('field' => 'Date','label' => 'Дата','rules' => '','type' => 'date', 'default' => null),
			array('field' => 'MedService_id','label' => 'Служба','rules' => 'required','type' => 'id'),
			array('field' => 'MedServiceType_SysNick', 'label' => '', 'rules' => '', 'type' => 'string'),
			array('field' => 'UslugaComplex_id','label' => 'Услуга','rules' => '','type' => 'id'),
		),
		'saveEvnLabSampleComment' => array(
			array('field' => 'EvnLabSample_id','label' => 'Проба','rules' => 'required','type' => 'id'),
			array('field' => 'EvnLabSample_Comment','label' => 'Примечание','rules' => '','type' => 'string'),
		),
		'saveEvnUslugaRoot' => array(
			array('field' => 'Lpu_id', 'label' => 'МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'Server_id','label' => 'Сервер','rules' => 'required','type' => 'int'),
			array('field' => 'EvnLabSample_id','label' => 'Проба','rules' => 'required','type' => 'id'),
			array('field' => 'UslugaComplex_id','label' => 'Услуга','rules' => '','type' => 'id'),
			array('field' => 'EvnDirection_id' ,'label' => 'Заявка','rules' => '','type' => 'id'),
			array('field' => 'EvnLabRequest_id', 'label' => 'Заявка', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PersonEvn_id', 'label' => 'Идентификатор периодики', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnPrescr_id', 'label' => 'Идентификатор назначения', 'rules' => '', 'type' => 'id'),
			array('field' => 'PayType_id', 'label' => 'Вид оплаты', 'rules' => '', 'type' => 'id'),
			array('field' => 'pmUser_id', 'label' => 'Идентификатор пользователя', 'rules' => 'required', 'type' => 'id'),
		),
		'loadPathologySamples' => array(
			array('field' => 'EvnLabSample_id', 'label' => 'Идентификатор пробы', 'rules' => 'required', 'type' => 'string')
		),
		'getPersonBySample' => array(
			array('field' => 'EvnLabSample_id', 'label' => 'Идентификатор пробы', 'rules' => 'required', 'type' => 'int'),
		),
		'loadResearchHistory' => array(
			array('field' => 'EvnLabSample_id', 'label' => 'Идентификатор пробы', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'Codes', 'label' => 'Список кодов услуг', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'MinDate', 'label' => 'Дата с', 'type' => 'string'),
			array('field' => 'MaxDate', 'label' => 'Дата по', 'type' => 'string')
		),
		'getUslugaTestResultForPortal' => array(
			array('field' => 'UslugaTest_pid', 'label' => 'Идентификатор услуги', 'rules' => 'required', 'type' => 'id')
		),
		'onChangeApproveResults' => [
			['field' => 'EvnUslugaParChanged', 'label' => 'Идентификатор изменившейся услуги', 'rules' => '', 'type' => 'json_array'],
			['field' => 'pmUser_id', 'label' => 'Идентификатор пользователя', 'rules' => 'required', 'type' => 'id'],
			['field' => 'MedPersonal_id', 'label' => 'Врач', 'rules' => '', 'type' => 'id'],
			['field' => 'methodAPI', 'label' => 'Метод', 'rules' => '', 'type' => 'string']
		],
		'saveEvnLabSampleBarcodeAndNum' => array(
			array(
				'field' => 'EvnLabSample_id',
				'label' => 'Идентификатор пробы',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnLabSample_BarCode',
				'label' => 'Номер штрих-кода',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'updateEvnLabSampleNum',
				'label' => 'признак что номер пробы тоже нужно сохранить',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'pmUser_id',
				'label' => 'идентификатор пользователя',
				'rules' => '',
				'type' => 'id'
			),
		),
		'createFromAPI' => [
			['field' => 'EvnDirection_id', 'label' => 'Идентификатор направления на лабораторное исследование', 'rules' => '', 'type' => 'id'],
			['field' => 'UslugaComplexList', 'label' => 'Список  идентфикаторов услуг, по которым взята проба', 'rules' => 'required', 'type' => 'string'],
			['field' => 'MedStaffFact_did', 'label' => 'Место работы врача, взявшего пробу', 'rules' => '', 'type' => 'id'],
			['field' => 'Lpu_did', 'label' => 'МО, взявшая пробу', 'rules' => '', 'type' => 'id'],
			['field' => 'LpuSection_did', 'label' => 'Отделение, взявшее пробу', 'rules' => '', 'type' => 'id'],
			['field' => 'MedPersonal_did', 'label' => 'Врач, взявший пробу', 'rules' => '', 'type' => 'id'],
			['field' => 'MedPersonal_sdid', 'label' => 'Средний медперсонал, взявший пробу', 'rules' => '', 'type' => 'id'],
			['field' => 'MedService_did', 'label' => 'Служба, в которой взята проба', 'rules' => '', 'type' => 'id'],
		],
		'updateFromAPI' => [
			['field' => 'EvnLabSample_id', 'label' => 'Идентификатор', 'rules' => 'required', 'type' => 'id'],
			['field' => 'DefectCauseType_id', 'label' => 'Идентификатор причины брака пробы', 'rules' => '', 'type' => 'int'],
			['field' => 'EvnLabSample_Comment', 'label' => 'Комментарий', 'rules' => '', 'type' => 'string'],
			['field' => 'EvnLabSample_AnalyzerDate', 'label' => 'Дата и время выполнения пробы на анализаторе', 'rules' => '', 'type' => 'datetime'],
			['field' => 'EvnLabSample_DelivDT', 'label' => 'Дата и время доставки пробы' , 'rules' => '', 'type' => 'datetime'],
			['field' => 'EvnLabSample_StudyDT', 'label' => 'Дата и время выполнения ислледования', 'rules' => '', 'type' => 'datetime'],
			['field' => 'LabSampleStatus_id', 'label' => 'Идентификатор статуса пробы (справочное значение)', 'rules' => 'required', 'type' => 'id'],
			['field' => 'Lpu_aid', 'label' => 'МО, выполнившая анализ', 'rules' => '', 'type' => 'id'],
			['field' => 'Lpu_did', 'label' => 'МО, взявшая пробу', 'rules' => '', 'type' => 'id'],
			['field' => 'LpuSection_aid', 'label' => 'Отделение, выполнившее анализ', 'rules' => '', 'type' => 'id'],
			['field' => 'LpuSection_did', 'label' => 'Отделение, взявшее пробу', 'rules' => '', 'type' => 'id'],
			['field' => 'MedPersonal_aid', 'label' => 'Врач, выполнивший анализ', 'rules' => '', 'type' => 'id'],
			['field' => 'MedPersonal_did', 'label' => 'Врач, взявший пробу', 'rules' => '', 'type' => 'id'],
			['field' => 'MedPersonal_said', 'label' => 'Средний медперсонал, выполнивший анализ', 'rules' => '', 'type' => 'id'],
			['field' => 'MedPersonal_sdid', 'label' => 'Средний медперсонал, взявший пробу', 'rules' => '', 'type' => 'id'],
			['field' => 'MedService_did', 'label' => 'Служба, в которой взята проба', 'rules' => '', 'type' => 'id'],
			['field' => 'MedService_id', 'label' => 'Служба, в которой выполняется заявка', 'rules' => '', 'type' => 'id'],
			['field' => 'MedService_sid', 'label' => 'Служба, забраковавшая пробу', 'rules' => '', 'type' => 'id'],
			['field' => 'MedStaffFact_aid', 'label' => 'Место работы врача, выполневшего анализ', 'rules' => '', 'type' => 'id'],
			['field' => 'MedStaffFact_did', 'label' => 'Место работы врача, взявшего пробу', 'rules' => '', 'type' => 'id'],
		],
		'createUslugaTest' => [
			['field' => 'EvnLabSample_id', 'label' => 'Идентификатор пробы.', 'rules' => 'required', 'type' => 'id'],
			['field' => 'Lpu_id', 'label' => 'Идентификатор МО, в которой выполнено исследование', 'rules' => '', 'type' => 'id'],
			['field' => 'PayType_id', 'label' => 'Вид оплаты', 'rules' => '', 'type' => 'id'],
			['field' => 'UslugaComplex_id', 'label' => 'Услуга (значение справочника dbo.UslugaComplex)', 'rules' => 'required', 'type' => 'id'],
			['field' => 'UslugaTest_ResultValue', 'label' => 'Результат выполнения исследования', 'rules' => '', 'type' => 'string'],
			['field' => 'Unit_id', 'label' => 'Единица измерения (справочник)', 'rules' => '', 'type' => 'id'],
			['field' => 'UslugaTest_ResultUnit', 'label' => 'Единица измерения (текстовое значение)', 'rules' => '', 'type' => 'string'],
			['field' => 'UslugaTest_Comment', 'label' => 'Комментарий', 'rules' => '', 'type' => 'string'],
			['field' => 'UslugaTest_setDT', 'label' => 'Дата выполнения', 'rules' => '', 'type' => 'date'],
			['field' => 'UslugaTest_ResultLower (', 'label' => 'Нижнее референсное значение', 'rules' => '', 'type' => 'string'],
			['field' => 'UslugaTest_ResultLowerCrit', 'label' => 'Нижнее критическое референсное значение', 'rules' => '', 'type' => 'string'],
			['field' => 'UslugaTest_ResultUpper', 'label' => 'Верхнее референсное значение', 'rules' => '', 'type' => 'string'],
			['field' => 'UslugaTest_ResultUpperCrit', 'label' => 'Верхнее критическое референсное значение', 'rules' => '', 'type' => 'string'],
			['field' => 'UslugaTest_ResultApproved', 'label' => 'Статус теста («Null» - не проводился, «1» - выполнен, «2» - одобрен)', 'rules' => '', 'type' => 'id'],
			['field' => 'UslugaTest_deleted', 'label' => 'Признак удаления («1» - не удален, «2» - удален)', 'rules' => 'required', 'type' => 'id'],
			['field' => 'UslugaTest_delDT', 'label' => 'Дата удаления теста (обязательно для заполнения, если UslugaTest_deleted=2)', 'rules' => '', 'type' => 'date'],
			['field' => 'UslugaTest_ResultCancelReason', 'label' => 'Причина отмены результата теста', 'rules' => '', 'type' => 'string'],
			['field' => 'UslugaTest_ResultAppDate', 'label' => 'Дата обновления значения результата выполнения', 'rules' => '', 'type' => 'id'],
			['field' => 'MedStaffFact_id', 'label' => 'Место работы врача, выполневшего тест', 'rules' => '', 'type' => 'id'],
		],
		'updateUslugaTest' => [
			['field' => 'UslugaTest_id', 'label' => 'Идентификатор лабораторного исследования ', 'rules' => 'required', 'type' => 'id'],
			['field' => 'Lpu_id', 'label' => 'Идентификатор МО, в которой выполнено исследование', 'rules' => '', 'type' => 'id'],
			['field' => 'PayType_id', 'label' => 'Вид оплаты', 'rules' => '', 'type' => 'id'],
			['field' => 'UslugaTest_ResultValue', 'label' => 'Результат выполнения исследования', 'rules' => '', 'type' => 'string'],
			['field' => 'Unit_id', 'label' => 'Единица измерения (справочник)', 'rules' => '', 'type' => 'id'],
			['field' => 'UslugaTest_ResultUnit', 'label' => 'Единица измерения (текстовое значение)', 'rules' => '', 'type' => 'string'],
			['field' => 'UslugaTest_Comment', 'label' => 'Комментарий', 'rules' => '', 'type' => 'string'],
			['field' => 'UslugaTest_setDT', 'label' => 'Дата выполнения', 'rules' => '', 'type' => 'date'],
			['field' => 'UslugaTest_ResultLower', 'label' => 'Нижнее референсное значение', 'rules' => '', 'type' => 'string'],
			['field' => 'UslugaTest_ResultLowerCrit', 'label' => 'Нижнее критическое референсное значение', 'rules' => '', 'type' => 'string'],
			['field' => 'UslugaTest_ResultUpper', 'label' => 'Верхнее референсное значение', 'rules' => '', 'type' => 'string'],
			['field' => 'UslugaTest_ResultUpperCrit', 'label' => 'Верхнее критическое референсное значение', 'rules' => '', 'type' => 'string'],
			['field' => 'UslugaTest_ResultApproved', 'label' => 'Статус теста («Null» - не проводился, «1» - выполнен, «2» - одобрен)', 'rules' => '', 'type' => 'id'],
			['field' => 'UslugaTest_deleted', 'label' => 'Признак удаления («1» - не удален, «2» - удален)', 'rules' => 'required', 'type' => 'id'],
			['field' => 'UslugaTest_delDT', 'label' => 'Дата удаления теста (обязательно для заполнения, если UslugaTest_deleted=2)', 'rules' => '', 'type' => 'date'],
			['field' => 'UslugaTest_ResultCancelReason', 'label' => 'Причина отмены результата теста', 'rules' => '', 'type' => 'string'],
			['field' => 'UslugaTest_ResultAppDate', 'label' => ' Дата обновления значения результата выполнения', 'rules' => '', 'type' => 'id'],
			['field' => 'MedStaffFact_id', 'label' => 'Место работы врача, выполневшего тест', 'rules' => '', 'type' => 'id'],
		],
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();

		$this->db = $this->load->database('lis', true);
		$this->load->model('EvnLabSample_model', 'dbmodel');

		$this->inputRules['save'] = $this->dbmodel->getInputRules();
	}

	/**
	 * @OA\Get(
	 *     path="/api/EvnLabSample",
	 *     tags={"EvnLabSample"},
	 *     summary="Получение данных пробы",
	 *     @OA\Parameter(
	 *     		name="EvnLabSample_id",
	 *     		in="query",
	 *     		description="Идентификатор пробы",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function index_get() {
		$data = $this->ProcessInputData('load', null, true);
		$this->dbmodel->EvnLabSample_id = $data['EvnLabSample_id'];
		$response = $this->dbmodel->load();
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Post(
	 *    path="/api/EvnLabSample",
	 *    tags={"EvnLabSample"},
	 *    summary="Создание пробы",
	 *    @OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="EvnLabSample_pid",
	 *     					description="Идентификатор родительского события",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabSample_rid",
	 *     					description="Идентификатор корневого события",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="PersonEvn_id",
	 *     					description="Идентификатор состояния человека",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabSample_setDT",
	 *     					description="Дата и время взятия пробы",
	 *     					type="string",
	 *     					format="date-time"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabSample_disDT",
	 *     					description="Дата",
	 *     					type="string",
	 *     					format="date"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabSample_didDT",
	 *     					description="Дата",
	 *     					type="string",
	 *     					format="date"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Morbus_id",
	 *     					description="Идентификатор заболевания",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabSample_IsSigned",
	 *     					description="Состояние подписи",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="pmUser_signID",
	 *     					description="Идентификатор подписавшего пользователя",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabSample_signDT",
	 *     					description="Дата и время подписания",
	 *     					type="string",
	 *     					format="date-time"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabRequest_id",
	 *     					description="Идентификатор заявки",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabSample_Num",
	 *     					description="Номер пробы",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabSample_BarCode",
	 *     					description="Штрих-код",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabSample_Comment",
	 *     					description="Комментарий",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="RefSample_id",
	 *     					description="Идентификатор записи справочника проб",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Lpu_did",
	 *     					description="Идентификатор МО, взявшей пробу",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="LpuSection_did",
	 *     					description="Идентификатор отделения, взявшего пробу",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="MedPersonal_did",
	 *     					description="Идентификатор врача, взявшего пробу",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="MedPersonal_sdid",
	 *     					description="Идентификатор среднего медперсонала, взявшего пробу",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="MedService_id",
	 *     					description="Идентификатор службы заявки",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="MedService_did",
	 *     					description="Идентификатор службы, взявшей пробу",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="MedService_sid",
	 *     					description="Идентификатор службы, взявшей пробу",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabSample_DelivDT",
	 *     					description="Дата и время доставки пробы",
	 *     					type="string",
	 *     					format="date-time"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Lpu_aid",
	 *     					description="Идентификатор МО, выполнившый анализ",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="LpuSection_aid",
	 *     					description="Идентификатор отделения, выполнившего анализ",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="MedPersonal_aid",
	 *     					description="Идентификатор врача, выполнившего анализ",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabSample_StudyDT",
	 *     					description="Идентификатор среднего медперсонала, выполнившего анализ",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="LabSampleDefectiveType_id",
	 *     					description="Идентификатор типа брака пробы",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="DefectCauseType_id",
	 *     					description="Идентификатор причины брака пробы",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Analyzer_id",
	 *     					description="Идентификатор анализатора",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="RecordStatus_Code",
	 *     					description="Код состояния записи",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="LabSample_Results",
	 *     					description="Результат",
	 *     					type="string"
	 * 					)
	 * 				)
	 * 			)
	 *		),
	 *    @OA\Response(
	 *    		response="200",
	 *    		description="JSON response",
	 *    		@OA\JsonContent()
	 * 	  )
	 * )
	 */
	function index_post() {
		$data = $this->ProcessInputData('save', null, true);

		if (isset($data['EvnLabSample_id']) && $data['EvnLabSample_id']) {
			$this->dbmodel->EvnLabSample_id = $data['EvnLabSample_id'];
			$this->dbmodel->load();
			// служба и служба взятия не приходят с формы, должны остаться теми же, что и были.
			$data['MedService_id'] = $this->dbmodel->fields['MedService_id'];
			$data['MedService_did'] = $this->dbmodel->fields['MedService_did'];
		}

		$this->dbmodel->assign($data);
		$response = $this->dbmodel->save($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Delete(
	 *     path="/api/EvnLabSample",
	 *     tags={"EvnLabSample"},
	 *     summary="Отмена взятия проб",
	 *     @OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="EvnLabSample_ids",
	 *     					description="Список идентификаторов проб в JSON-формате",
	 *     					type="string",
	 *     					format="json"
	 * 					),
	 *     				required={
	 *     					"EvnLabSample_ids"
	 * 					}
	 * 				)
	 * 			)
	 *		),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function index_delete() {
		$data = $this->ProcessInputData('cancel', null, true);
		$response = $this->dbmodel->cancel($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Patch(
	 *     path="/api/EvnLabSample/Comment",
	 *     tags={"EvnLabSample"},
	 *     summary="Изменение комментария",
	 *     @OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="EvnLabSample_id",
	 *     					description="Идентификатор пробы",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabSample_Comment",
	 *     					description="Комментарий",
	 *     					type="string"
	 * 					),
	 *     				required={
	 *     					"EvnLabSample_id"
	 * 					}
	 * 				)
	 * 			)
	 *		),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function Comment_patch()
	{
		$data = $this->ProcessInputData('saveEvnLabSampleComment', null, true);
		$response = $this->dbmodel->saveEvnLabSampleComment($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     path="/api/EvnLabSample/ResultCount/{MedService_id}",
	 *     tags={"EvnLabSample"},
	 *     summary="Получение количества проб из ЛИС с результатами",
	 *     @OA\Parameter(
	 *     		name="MedService_id",
	 *     		in="path",
	 *     		description="Идентификатор службы",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function ResultCount_get() {
		$data = $this->ProcessInputData('getEvnLabSampleFromLisWithResultCount', null, true);
		$response = $this->dbmodel->getEvnLabSampleFromLisWithResultCount($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Post(
	 *     path="/api/EvnLabSample/massApproveResults",
	 *     tags={"EvnLabSample"},
	 *     summary="Массовое одобрение результатов проб",
	 *     @OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="EvnLabSamples",
	 *     					description="Список идентификаторов проб в JSON-формате",
	 *     					type="string",
	 *     					format="json"
	 * 					)
	 * 				)
	 * 			)
	 *		),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function massApproveResults_post() {
		$data = $this->ProcessInputData('approveEvnLabSampleResults', null, true);
		$response = $this->dbmodel->approveEvnLabSampleResults($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     path="/api/EvnLabSample/RefValues",
	 *     tags={"EvnLabSample"},
	 *     summary="Получение списка референсных значений проб",
	 *     @OA\Parameter(
	 *     		name="EvnLabSample_setDT",
	 *     		in="query",
	 *     		description="Дата и время взятия пробы",
	 *     		required=false,
	 *     		@OA\Schema(type="string", format="date-time")
	 * 	   ),
	 *	   @OA\Parameter(
	 *     		name="MedService_id",
	 *     		in="query",
	 *     		description="Идентификатор службы",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="EvnDirection_id",
	 *     		in="query",
	 *     		description="Идентификатор направления",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *	   @OA\Parameter(
	 *     		name="Person_id",
	 *     		in="query",
	 *     		description="Идентификатор человека",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="UslugaComplexTarget_id",
	 *     		in="query",
	 *     		description="Идентификатор услуги исследования",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *	   @OA\Parameter(
	 *     		name="UslugaComplex_ids",
	 *     		in="query",
	 *     		description="Список идентификаторов услуг в JSON-формате",
	 *     		required=true,
	 *     		@OA\Schema(type="string")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="Analyzer_id",
	 *     		in="query",
	 *     		description="Идентификатор анализатора",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function RefValues_get() {
		$data = $this->ProcessInputData('loadRefValues', null, true);
		$response = $this->dbmodel->loadRefValues($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Patch(
	 *     path="/api/EvnLabSample/BarCode",
	 *     tags={"EvnLabSample"},
	 *     summary="Сохранение нового штрих-кода пробы",
	 *     @OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="EvnLabSample_id",
	 *     					description="Идентификатор пробы",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabSample_BarCode",
	 *     					description="Штрих-код",
	 *     					type="string"
	 * 					),
	 *     				required={
	 *     					"EvnLabSample_id",
	 *     					"EvnLabSample_BarCode"
	 * 					}
	 * 				)
	 * 			)
	 *		),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function BarCode_patch() {
		$data = $this->ProcessInputData('saveNewEvnLabSampleBarCode', null, true);
		$response = $this->dbmodel->saveNewEvnLabSampleBarCode($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Patch(
	 *     path="/api/EvnLabSample/Num",
	 *     tags={"EvnLabSample"},
	 *     summary="Сохранение нового номера пробы",
	 *     @OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="EvnLabSample_id",
	 *     					description="Идентификатор пробы",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabSample_ShortNum",
	 *     					description="Номер",
	 *     					type="int"
	 * 					),
	 *     				required={
	 *     					"EvnLabSample_id",
	 *     					"EvnLabSample_ShortNum"
	 * 					}
	 * 				)
	 * 			)
	 *		),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function Num_patch() {
		$data = $this->ProcessInputData('saveNewEvnLabSampleNum', null, true);
		$response = $this->dbmodel->saveNewEvnLabSampleNum($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Post(
	 *     path="/api/EvnLabSample/take",
	 *     tags={"EvnLabSample"},
	 *     summary="Взятие пробы",
	 *     @OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="EvnLabRequest_id",
	 *     					description="Идентификатор заявки",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabSample_id",
	 *     					description="Идентификатор пробы",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="MedServiceType_SysNick",
	 *     					description="Системное наименование типа службы",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="MedService_did",
	 *     					description="Идентификатор службы, в которой взята проба",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="RefSample_id",
	 *     					description="Идентификатор референсного значения пробы",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="sendToLis",
	 *     					description="Признак необходимости отправки в ЛИС (служба АС МЛО)",
	 *     					type="integer"
	 * 					),
	 *     				required={
	 *	 					"EvnLabRequest_id"
	 * 					}
	 * 				)
	 * 			)
	 *		),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function take_post() {
		$data = $this->ProcessInputData('takeLabSample', null, true);
		$response = $this->dbmodel->takeLabSample($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     path="/api/EvnLabSample/NumList/generate",
	 *     tags={"EvnLabSample"},
	 *     summary="Сгенерировать перечень номеров без привязки к пробе",
	 *     @OA\Parameter(
	 *     		name="MedService_id",
	 *     		in="query",
	 *     		description="Идентификатор службы",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="quantity",
	 *     		in="query",
	 *     		description="Количество",
	 *     		required=true,
	 *     		@OA\Schema(type="integer")
	 * 	   ),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function genNumList_get() {
		$data = $this->ProcessInputData('getNewListEvnLabSampleNum', null, true);
		$response = $this->dbmodel->getNewListEvnLabSampleNum($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Post(
	 *     path="/api/EvnLabSample/saveLabSample",
	 *     tags={"EvnLabSample"},
	 *     summary="Сохранение пробы",
	 *     @OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="MedService_id",
	 *     					description="Идентификатор службы",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabRequest_id",
	 *     					description="Идентификатор заявки",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="RefSample_id",
	 *     					description="Идентификатор референсного значения пробы",
	 *     					type="integer"
	 * 					),
	 *     				required={
	 *	 					"MedService_id",
	 *	 					"EvnLabRequest_id"
	 * 					}
	 * 				)
	 * 			)
	 *		),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function saveLabSample_post() {
		$data = $this->ProcessInputData('saveLabSample', null, true);
		$response = $this->dbmodel->saveLabSample($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Post(
	 *     path="/api/EvnLabSample/Researches",
	 *     tags={"EvnLabSample"},
	 *     summary="Сохранение исследований",
	 *     @OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="MedService_id",
	 *     					description="Идентификатор службы",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabRequest_id",
	 *     					description="Идентификатор заявки",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabSample_id",
	 *     					description="Идентификатор пробы",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="RefSample_id",
	 *     					description="Идентификатор заявки",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="researches",
	 *     					description="Список исследований в JSON-формате",
	 *     					type="string",
	 *     					format="json"
	 * 					),
	 *     				required={
	 *	 					"MedService_id",
	 *	 					"EvnLabRequest_id",
	 *	 					"EvnLabSample_id"
	 * 					}
	 * 				)
	 * 			)
	 *		),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function Researches_post() {
		$data = $this->ProcessInputData('saveLabSampleResearches', null, true);
		$response = $this->dbmodel->saveLabSampleResearches($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Post(
	 *     path="/api/EvnLabSample/Defect",
	 *     tags={"EvnLabSample"},
	 *     summary="Сохранение отбраковки",
	 *     @OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="DefectCauseType_id",
	 *     					description="Идентификатор типа брака",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabSample_id",
	 *     					description="Идентификатор пробы",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabSample_BarCode",
	 *     					description="Штрих-код",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="MedServiceType_SysNick",
	 *     					description="Системное наименование типа службы",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="MedService_sid",
	 *     					description="Идентификатор текущей службы",
	 *     					type="integer"
	 * 					),
	 *     				required={
	 *	 					"DefectCauseType_id",
	 *	 					"EvnLabSample_BarCode"
	 * 					}
	 * 				)
	 * 			)
	 *		),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function Defect_post() {
		$data = $this->ProcessInputData('saveEvnLabSampleDefect', null, true);
		$response = $this->dbmodel->saveEvnLabSampleDefect($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Delete(
	 *     path="/api/EvnLabSample/Defect",
	 *     tags={"EvnLabSample"},
	 *     summary="Удаление отбраковки",
	 *     @OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="EvnLabSample_id",
	 *     					description="Идентификатор пробы",
	 *     					type="integer"
	 * 					),
	 *     				required={
	 *	 					"EvnLabSample_id"
	 * 					}
	 * 				)
	 * 			)
	 *		),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function Defect_delete() {
		$data = $this->ProcessInputData('deleteEvnLabSampleDefect', null, true);
		$response = $this->dbmodel->deleteEvnLabSampleDefect($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     path="/api/EvnLabSample/List",
	 *     tags={"EvnLabSample"},
	 *     summary="Получение списка проб",
	 *     @OA\Parameter(
	 *     		name="EvnLabRequest_id",
	 *     		in="query",
	 *     		description="Идентификатор заявки",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="UslugaComplex_id",
	 *     		in="query",
	 *     		description="Идентификатор корневой услуги в заявке",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="MedService_id",
	 *     		in="query",
	 *     		description="Идентификатор службы",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function List_get() {
		$data = $this->ProcessInputData('loadLabSampleFrame', null, true);
		$response = $this->dbmodel->loadLabSampleFrame($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Put(
	 *     path="/api/EvnLabSample/Result",
	 *     tags={"EvnLabSample"},
	 *     summary="Изменение результатов теста",
	 *     @OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="UslugaTest_id",
	 *     					description="Идентификатор теста",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="UslugaTest_ResultValue",
	 *     					description="Результат",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="UslugaTest_setDT",
	 *     					description="Дата и время выполнения теста",
	 *     					type="string",
	 *     					format="date-time"
	 * 					),
	 *     				@OA\Property(
	 *     					property="UslugaTest_Unit",
	 *     					description="Единицы измерения",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="UslugaTest_Comment",
	 *     					description="Комментарий",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="UslugaTest_RefValues",
	 *     					description="Референсные значения",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="updateType",
	 *     					description="Тип изменения",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="sourceName",
	 *     					description="Источник",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabSample_id",
	 *     					description="Идентифифкатор пробы",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="UslugaTest_Code",
	 *     					description="Код теста",
	 *     					type="string"
	 * 					),
	 *     				required={
	 *	 					"UslugaTest_id"
	 * 					}
	 * 				)
	 * 			)
	 *		),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function Result_put() {
		$data = $this->ProcessInputData('updateResult', null, true);
		$data['UslugaTest_ResultValue'] = str_replace('PLUS', '+', $data['UslugaTest_ResultValue']);
		// 1. Обновили текущий тест
		$response = $this->dbmodel->updateResult($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		// 2. Обновляем расчетные тесты
		$this->load->model("AsMlo_model");
		$tests = $this->AsMlo_model->getSampleTests($data);

		$sample = array('id' => $data['EvnLabSample_id']);
		array_walk($tests, function($v, $k) use(&$sample) {
			$sample['tests'][$k]['code'] = $v['test_code'];
			$sample['tests'][$k]['value'] = $v['UslugaTest_ResultValue'];
			$sample['tests'][$k]['unit'] = $v['UslugaTest_ResultUnit'];
			$sample['tests'][$k]['UslugaTest_id'] = $v['UslugaTest_id'];
		});

		$formulaTemp = $this->AsMlo_model->getFormulaSample($sample);

		// 3. Проверка что изменяемый тест не расчетный
		$has_formula = true;
		array_map(function($v) use($data, &$has_formula){
			if($data['UslugaTest_id'] === $v['UslugaTest_id'] &&
				$data['UslugaTest_ResultValue'] !== $v['value']) {
				$has_formula = false;
			}
		}, $formulaTemp['tests']);

		// 4. Пересчитываем расчетные тесты
		$formula = array();
		if($has_formula) {
			$here = $this;
			array_walk($formulaTemp['tests'], function($v, $k) use($here, $data, &$formula){
				if(isset($v['is_formula']))
				{
					$formula[] = $v;
					$data['UslugaTest_ResultValue'] = $v['value'];
					$data['UslugaTest_id'] = $v['UslugaTest_id'];
					$here->dbmodel->updateResult($data);
				}
			});
		}

		$response[1] = count($formula) > 0 ? array_values($formula) : null;

		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     path="/api/EvnLabSample/Overdue/{MedService_id}",
	 *     tags={"EvnLabSample"},
	 *     summary="Получение количества просроченных проб",
	 *     @OA\Parameter(
	 *     		name="MedService_id",
	 *     		in="path",
	 *     		description="Идентификатор лаборатории",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function Overdue_get() {
		$data = $this->ProcessInputData('getOverdueSamples', null, true);
		$response = $this->dbmodel->getOverdueSamples($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Post(
	 *     path="/api/EvnLabSample/Test",
	 *     tags={"EvnLabSample"},
	 *     summary="Назначение тестов",
	 *     @OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="EvnLabSample_id",
	 *     					description="Идентификатор пробы",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabRequest_id",
	 *     					description="Идентификатор заявки",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="tests",
	 *     					description="Список тестов в JSON-формате",
	 *     					type="string",
	 *     					format="json"
	 * 					)
	 * 				)
	 * 			)
	 *		),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function Test_post() {
		$data = $this->ProcessInputData('prescrTest', null, true);
		$response = $this->dbmodel->prescrTest($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Delete(
	 *     path="/api/EvnLabSample/Test",
	 *     tags={"EvnLabSample"},
	 *     summary="Отмена тестов",
	 *     @OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="EvnLabSample_id",
	 *     					description="Идентификатор пробы",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabRequest_id",
	 *     					description="Идентификатор заявки",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="tests",
	 *     					description="Список тестов в JSON-формате",
	 *     					type="string",
	 *     					format="json"
	 * 					),
	 *     				required={
	 *	 					"EvnLabSample_id"
	 * 					}
	 * 				)
	 * 			)
	 *		),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function Test_delete() {
		$data = $this->ProcessInputData('cancelTest', null, true);
		$response = $this->dbmodel->cancelTest($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Post(
	 *     path="/api/EvnLabSample/transferResearches",
	 *     tags={"EvnLabSample"},
	 *     summary="Перенос тестов из одной пробы в другую",
	 *     @OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="EvnLabSample_oldid",
	 *     					description="Идентификатор старой пробы",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabSample_newid",
	 *     					description="Идентификатор новой пробы",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="tests",
	 *     					description="Список тестов в JSON-формате",
	 *     					type="string",
	 *     					format="json"
	 * 					),
	 *     				required={
	 *	 					"EvnLabSample_oldid",
	 *	 					"EvnLabSample_newid",
	 *	 					"tests"
	 * 					}
	 * 				)
	 * 			)
	 *		),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function transferResearches_post() {
		$data = $this->ProcessInputData('transferLabSampleResearches', null, true);
		$response = $this->dbmodel->transferLabSampleResearches($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Post(
	 *     path="/api/EvnLabSample/approveResults",
	 *     tags={"EvnLabSample"},
	 *     summary="Одобрение результатов пробы",
	 *     @OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="EvnLabSample_id",
	 *     					description="Идентификатор пробы",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="UslugaTest_id",
	 *     					description="Идентификатор теста",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="UslugaTest_ids",
	 *     					description="Список идентификаторов тестов в JSON-формате",
	 *     					type="string",
	 *     					format="json"
	 * 					),
	 *     				@OA\Property(
	 *     					property="onlyNorm",
	 *     					description="Признак одобрения только результатов в норме",
	 *     					type="integer"
	 * 					),
	 *     				required={
	 *	 					"EvnLabSample_id"
	 * 					}
	 * 				)
	 * 			)
	 *		),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function approveResults_post() {
		$data = $this->ProcessInputData('approveResults', null, true);
		$response = $this->dbmodel->approveResults($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Post(
	 *     path="/api/EvnLabSample/unapproveResults",
	 *     tags={"EvnLabSample"},
	 *     summary="Снятие одобрения результатов пробы",
	 *     @OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="EvnLabSample_id",
	 *     					description="Идентификатор пробы",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="UslugaTest_id",
	 *     					description="Идентификатор теста",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="UslugaTest_ids",
	 *     					description="Список идентификаторов тестов в JSON-формате",
	 *     					type="string",
	 *     					format="json"
	 * 					),
	 *     				required={
	 *	 					"EvnLabSample_id"
	 * 					}
	 * 				)
	 * 			)
	 *		),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function unapproveResults_post() {
		$data = $this->ProcessInputData('unapproveResults', null, true);
		$response = $this->dbmodel->unapproveResults($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     path="/api/EvnLabSample/ResultGrid",
	 *     tags={"EvnLabSample"},
	 *     summary="Получение списка результатов пробы",
	 *     @OA\Parameter(
	 *     		name="EvnDirection_id",
	 *     		in="query",
	 *     		description="Идентификатор направления",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="EvnLabSample_id",
	 *     		in="query",
	 *     		description="Идентификатор пробы",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="RefSample_id",
	 *     		in="query",
	 *     		description="Идентификатор референсного значения пробы",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function ResultGrid_get() {
		$data = $this->ProcessInputData('getLabSampleResultGrid', null, true);
		$response = $this->dbmodel->getLabSampleResultGrid($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     path="/api/EvnLabSample/Usluga",
	 *     tags={"EvnLabSample"},
	 *     summary="Получение списка услуг для пробы",
	 *     @OA\Parameter(
	 *     		name="EvnLabSample_id",
	 *     		in="query",
	 *     		description="Идентификатор пробы",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function Usluga_get() {
		$data = $this->ProcessInputData('getSampleUsluga', null, true);
		$response = $this->dbmodel->getSampleUsluga($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Post(
	 *     path="/api/EvnLabSample/Analyzer",
	 *     tags={"EvnLabSample"},
	 *     summary="Выбор анализатора для проб",
	 *     @OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="Analyzer_id",
	 *     					description="Идентификатор анализатора",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabSamples",
	 *     					description="Список идентификаторов проб в JSON-формате",
	 *     					type="string",
	 *     					format="json"
	 * 					),
	 *     				required={
	 *	 					"EvnLabSamples"
	 * 					}
	 * 				)
	 * 			)
	 *		),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function Analyzer_post() {
		$data = $this->ProcessInputData('saveLabSampleAnalyzer', null, true);

		$samples = json_decode($data['EvnLabSamples'], true);
		if (is_array($samples)) {
			$response = $this->dbmodel->saveLabSamplesAnalyzer($data);
		} else {
			$data['EvnLabSample_id'] = $data['EvnLabSamples'];
			$response = $this->dbmodel->saveLabSampleAnalyzer($data);
		}

		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     path="/api/EvnLabSample/WorkList",
	 *     tags={"EvnLabSample"},
	 *     summary="Получение списка проб для рабочего журнала",
	 *     @OA\Parameter(
	 *     		name="MedService_id",
	 *     		in="query",
	 *     		description="Идентификатор службы",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="MedServiceType_SysNick",
	 *     		in="query",
	 *     		description="Системное наименование типа службы",
	 *     		required=false,
	 *     		@OA\Schema(type="string")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="EvnLabSample_id",
	 *     		in="query",
	 *     		description="Идентификатор пробы",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="EvnDirection_IsCito",
	 *     		in="query",
	 *     		description="Cito",
	 *     		required=false,
	 *     		@OA\Schema(type="integer")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="EvnLabSample_IsOutNorm",
	 *     		in="query",
	 *     		description="Отклонение",
	 *     		required=false,
	 *     		@OA\Schema(type="integer")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="begDate",
	 *     		in="query",
	 *     		description="Начало периода",
	 *     		required=false,
	 *     		@OA\Schema(type="string", format="date")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="endDate",
	 *     		in="query",
	 *     		description="Конец периода",
	 *     		required=false,
	 *     		@OA\Schema(type="string", format="date")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="LpuSection_id",
	 *     		in="query",
	 *     		description="Идентификатор отделения",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="MedPersonal_id",
	 *     		in="query",
	 *     		description="Идентификатор врача",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="LabSampleStatus_id",
	 *     		in="query",
	 *     		description="Идентификатор статуса пробы",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="LabSampleStatus_id",
	 *     		in="query",
	 *     		description="Идентификатор статуса пробы",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="Person_ShortFio",
	 *     		in="query",
	 *     		description="ФИО",
	 *     		required=false,
	 *     		@OA\Schema(type="string")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="EvnDirection_Num",
	 *     		in="query",
	 *     		description="Номер направления",
	 *     		required=false,
	 *     		@OA\Schema(type="string")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="EvnLabSample_BarCode",
	 *     		in="query",
	 *     		description="Штрих-код пробы",
	 *     		required=false,
	 *     		@OA\Schema(type="string")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="EvnLabSample_ShortNum",
	 *     		in="query",
	 *     		description="Номер пробы",
	 *     		required=false,
	 *     		@OA\Schema(type="string")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="filterNewELSByDate",
	 *     		in="query",
	 *     		description="Флаг фильтрации новых проб по дате",
	 *     		required=false,
	 *     		@OA\Schema(type="integer")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="filterWorkELSByDate",
	 *     		in="query",
	 *     		description="Флаг фильтрации проб в работе по дате",
	 *     		required=false,
	 *     		@OA\Schema(type="integer")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="filterDoneELSByDate",
	 *     		in="query",
	 *     		description="Флаг фильтрации проб с результатами по дате",
	 *     		required=false,
	 *     		@OA\Schema(type="integer")
	 * 	   ),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function WorkList_get() {
		$data = $this->ProcessInputData('loadWorkList', null, true);
		$response = $this->dbmodel->loadWorkList($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     path="/api/EvnLabSample/DefectList",
	 *     tags={"EvnLabSample"},
	 *     summary="Получение списка проб для журнала отбраковки",
	 *     @OA\Parameter(
	 *     		name="EvnLabSample_id",
	 *     		in="query",
	 *     		description="Идентификатор пробы",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="EvnDirection_IsCito",
	 *     		in="query",
	 *     		description="Cito",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="begDate",
	 *     		in="query",
	 *     		description="Начало периода",
	 *     		required=false,
	 *     		@OA\Schema(type="string", format="date")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="endDate",
	 *     		in="query",
	 *     		description="Конец периода",
	 *     		required=false,
	 *     		@OA\Schema(type="string", format="date")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="DefectCauseType_id",
	 *     		in="query",
	 *     		description="Идентификатор причины отбраковки",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="RefMaterial_id",
	 *     		in="query",
	 *     		description="Идентификатор биоматериала",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="UslugaComplex_id",
	 *     		in="query",
	 *     		description="Идентификатор исследования",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="LpuSection_id",
	 *     		in="query",
	 *     		description="Идентификатор отделения",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="MedPersonal_id",
	 *     		in="query",
	 *     		description="Идентификатор врача",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="MedService_id",
	 *     		in="query",
	 *     		description="Идентификатор службы",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="MedService_sid",
	 *     		in="query",
	 *     		description="Идентификатор текущей службы",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function DefectList_get() {
		$data = $this->ProcessInputData('loadDefectList', null, true);
		$response = $this->dbmodel->loadDefectList($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     path="/api/EvnLabSample/ListForCandiPicker",
	 *     tags={"EvnLabSample"},
	 *     summary="Получение списка проб-кандидатов",
	 *     @OA\Parameter(
	 *     		name="AnalyzerWorksheet_id",
	 *     		in="query",
	 *     		description="Идентификатор рабочего списка",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="MedService_id",
	 *     		in="query",
	 *     		description="Идентификатор службы",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="EvnLabRequest_BarCode",
	 *     		in="query",
	 *     		description="Штрих-код пробы",
	 *     		required=false,
	 *     		@OA\Schema(type="string")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="EvnLabSample_Num",
	 *     		in="query",
	 *     		description="Номер пробы",
	 *     		required=false,
	 *     		@OA\Schema(type="string")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="EvnLabSample_ShortNum",
	 *     		in="query",
	 *     		description="Номер пробы",
	 *     		required=false,
	 *     		@OA\Schema(type="string")
	 * 	   ),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function ListForCandiPicker_get() {
		$data = $this->ProcessInputData('loadListForCandiPicker', null, true);
		$response = $this->dbmodel->loadListForCandiPicker($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		for ($i = 0; $i < count($response); $i++) {
			if (empty($response[$i]['EvnLabSample_ShortNum'])) {
				$response[$i]['EvnLabSample_ShortNum'] = substr($response[$i]['EvnLabSample_Num'], -4);
			}
		}

		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     path="/api/EvnLabSample/BarCode",
	 *     tags={"EvnLabSample"},
	 *     summary="Получение списка штрих-кодов",
	 *     @OA\Parameter(
	 *     		name="AnalyzerWorksheet_id",
	 *     		in="query",
	 *     		description="Идентификатор рабочего списка",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function BarCode_get() {
		$data = $this->ProcessInputData('loadBarCode', null, true);
		$response = $this->dbmodel->loadBarCode($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     path="/api/EvnLabSample/checkUnique",
	 *     tags={"EvnLabSample"},
	 *     summary="Проверка 12-ти значного номера пробы на уникальность",
	 *     @OA\Parameter(
	 *     		name="MedService_id",
	 *     		in="query",
	 *     		description="Идентификатор лаборатории",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="EvnLabSample_Num",
	 *     		in="query",
	 *     		description="Номер пробы",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function checkUnique_get() {
		$data = $this->ProcessInputData('checkEvnLabSampleUnique', null, true);
		$response = $this->dbmodel->checkEvnLabSampleUnique($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     path="/api/EvnLabSample/ListForWorksheet",
	 *     tags={"EvnLabSample"},
	 *     summary="Получение данных проб для рабочего списка",
	 *     @OA\Parameter(
	 *     		name="AnalyzerWorksheet_id",
	 *     		in="query",
	 *     		description="Идентификатор рабочего списка",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="start",
	 *     		in="query",
	 *     		description="Начало",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", default=0)
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="limit",
	 *     		in="query",
	 *     		description="Количество",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", default=100)
	 * 	   ),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function ListForWorksheet_get() {
		$data = $this->ProcessInputData('loadEvnLabSampleListForWorksheet', null, true);
		$response = $this->dbmodel->loadEvnLabSampleListForWorksheet();
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		for ($i = 0; $i < count($response['data']); $i++) {
			if (empty($response['data'][$i]['EvnLabSample_ShortNum'])) {
				$response['data'][$i]['EvnLabSample_ShortNum'] = substr($response['data'][$i]['EvnLabSample_Num'], -4);
			}
		}

		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Post(
	 *     path="/api/EvnLabSample/Research",
	 *     tags={"EvnLabSample"},
	 *     summary="Сохранение данных исследования",
	 *     @OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="EvnUslugaPar_id",
	 *     					description="Идентификатор услуги",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnUslugaPar_setDate",
	 *     					description="Дата выполнения",
	 *     					type="string",
	 *     					type="date"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnUslugaPar_setTime",
	 *     					description="Время выполнения",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="Lpu_aid",
	 *     					description="Идентификатор МО",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="LpuSection_aid",
	 *     					description="Идентификатор отделения",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="MedPersonal_aid",
	 *     					description="Идентификатор врача",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="MedPersonal_said",
	 *     					description="Идентификатор среднего мед.персонала",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnUslugaPar_Comment",
	 *     					description="Комментарий",
	 *     					type="string"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnUslugaPar_IndexRep",
	 *     					description="Признак повторной подачи",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnUslugaPar_IndexRepInReg",
	 *     					description="Признак вхождения в реестр повторной подачи",
	 *     					type="integer"
	 * 					),
	 *     				required={
	 *	 					"EvnUslugaPar_id"
	 * 					}
	 * 				)
	 * 			)
	 *		),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function Research_post() {
		$data = $this->ProcessInputData('saveResearch', null, true);
		$response = $this->dbmodel->saveResearch($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Patch(
	 *     path="/api/EvnLabSample/Research/Comment",
	 *     tags={"EvnLabSample"},
	 *     summary="Изменения комментария в исследовании",
	 *     @OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="EvnUslugaPar_id",
	 *     					description="Идентификатор услуги",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnUslugaPar_Comment",
	 *     					description="Комментарий",
	 *     					type="string"
	 * 					),
	 *     				required={
	 *	 					"EvnUslugaPar_id"
	 * 					}
	 * 				)
	 * 			)
	 *		),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function ResearchComment_patch() {
		$data = $this->ProcessInputData('saveComment', null, true);
		$response = $this->dbmodel->saveComment($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     path="/api/EvnLabSample/Research",
	 *     tags={"EvnLabSample"},
	 *     summary="Получение данных исследования",
	 *     @OA\Parameter(
	 *     		name="EvnUslugaPar_id",
	 *     		in="query",
	 *     		description="Идентификатор услуги",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function Research_get() {
		$data = $this->ProcessInputData('loadResearchEditForm', null, true);
		$response = $this->dbmodel->loadResearchEditForm($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     path="/api/EvnLabSample/fromUsluga",
	 *     tags={"EvnLabSample"},
	 *     summary="Получение пробы исследования",
	 *     @OA\Parameter(
	 *     		name="EvnUslugaPar_id",
	 *     		in="query",
	 *     		description="Идентификатор услуги",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function fromUsluga_get() {
		$data = $this->ProcessInputData('getEvnLabSample', null, true);
		$response = $this->dbmodel->getEvnLabSample($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     path="/api/EvnLabSample/TestListForm250",
	 *     tags={"EvnLabSample"},
	 *     summary="Получение списка всех тестов для анализа за выбранный день в выбранной лаборатории для столбцов формы 250у",
	 *     @OA\Parameter(
	 *     		name="MedService_id",
	 *     		in="query",
	 *     		description="Идентификатор службы",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="UslugaComplex_id",
	 *     		in="query",
	 *     		description="Идентификатор услуги",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="Date",
	 *     		in="query",
	 *     		description="Дата",
	 *     		required=false,
	 *     		@OA\Schema(type="string", format="date")
	 * 	   ),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function TestListForm250_get() {
		$data = $this->ProcessInputData('getTestListForm250', null, true);

		$response = $this->dbmodel->getTestListForm250($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response($response);
	}

	/**
	 * @OA\Get(
	 *     path="/api/EvnLabSample/SampleListForm250",
	 *     tags={"EvnLabSample"},
	 *     summary="Получение списка взятых проб из лис с результатами (для формы 250у)",
	 *     @OA\Parameter(
	 *     		name="MedService_id",
	 *     		in="query",
	 *     		description="Идентификатор службы",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="UslugaComplex_id",
	 *     		in="query",
	 *     		description="Идентификатор услуги",
	 *     		required=false,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="Date",
	 *     		in="query",
	 *     		description="Дата",
	 *     		required=false,
	 *     		@OA\Schema(type="string", format="date")
	 * 	   ),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function SampleListForm250_get() {
		$data = $this->ProcessInputData('loadSampleListForm250', null, true);

		$response = $this->dbmodel->loadSampleListForm250($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$sampleList = array(); //список всех найденных проб
		for($i = 0; $i < count($response); $i++) {
			$sampleId = $response[$i]['EvnLabSample_id'];
			if (empty($sampleList[$sampleId])) {
				$sampleList[$sampleId] = $response[$i];
			}

			$UslugaComplex_id = $response[$i]['UslugaComplex_id'];
			$testInfo = array(
				"UslugaTest_ResultValue" => $response[$i]['UslugaTest_ResultValue'],
				"UslugaTest_ResultUnit" => $response[$i]['UslugaTest_ResultUnit'],
				"UslugaTest_id" => $response[$i]['UslugaTest_id'],
				"UslugaComplex_id" => $response[$i]['UslugaComplex_id']
				//,"UslugaComplex_Code" => $response[$i]['UslugaComplex_Code']
			);
			if (empty($sampleList[$sampleId]["testList"])) {
				$sampleList[$sampleId]["testList"] = array();
			}
			//может быть несколько одинаковых услуг на одном анализаторе (чтоб не было дублей):
			if (empty($sampleList[$sampleId]["testList"][$UslugaComplex_id])) {
				$sampleList[$sampleId]["testList"][$UslugaComplex_id] = $testInfo;
			} else {
				$testInfo_old = $sampleList[$sampleId]["testList"][$UslugaComplex_id];
				foreach ($testInfo_old as $key => $value) {
					if (empty($value)) {
						$testInfo_old[$key] = $testInfo[$key];
					}
				}
			}
		}

		//добавляем в полученный массив проб результаты тестов:
		foreach ($sampleList as &$sample) {
			$testList = array();
			foreach ($sample["testList"] as $testId => $test) {
				$testList['UslugaComplex_'.$test['UslugaComplex_id']] = $test['UslugaTest_ResultValue'];
			}
			$sample = $sample + $testList;
		}

		$response = array_values($sampleList);

		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Post(
	 *     path="/api/EvnLabSample/EvnUslugaRoot",
	 *     tags={"EvnLabSample"},
	 *     summary="Создание родительской услуги для остальных в исследовании",
	 *     @OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="EvnLabSample_id",
	 *     					description="Идентификатор пробы",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="UslugaComplex_id",
	 *     					description="Идентификатор услуги",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnDirection_id",
	 *     					description="Идентификатор направления",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnLabRequest_id",
	 *     					description="Идентификатор заявки",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="PersonEvn_id",
	 *     					description="Идентификатор состояния человека",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="EvnPrescr_id",
	 *     					description="Идентификатор назначения",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="PayType_id",
	 *     					description="Идентификатор вида оплаты",
	 *     					type="integer"
	 * 					),
	 *     				required={
	 *	 					"EvnLabSample_id"
	 * 					}
	 * 				)
	 * 			)
	 *		),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function EvnUslugaRoot_post() {
		$data = $this->ProcessInputData('saveEvnUslugaRoot', null, true);
		$response = $this->dbmodel->saveEvnUslugaRoot($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 *     path="/api/EvnLabSample/PathologySamples",
	 *     tags={"EvnLabSample"},
	 *     summary="Получение проб для сортировки по патологиям",
	 *     @OA\Parameter(
	 *     		name="EvnLabSample_id",
	 *     		in="query",
	 *     		description="Идентификаторы проб",
	 *     		required=true,
	 *     		@OA\Schema(type="string")
	 * 	   ),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function PathologySamples_get() {
		$data = $this->ProcessInputData('loadPathologySamples', null, true);
		$response = $this->dbmodel->loadPathologySamples($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(['error_code' => 0, 'data' => $response]);
	}

	/**
	 * @OA\Get(
	 *     path="/api/EvnLabSample/PersonBySample",
	 *     tags={"EvnLabSample"},
	 *     summary="Получение данных человека из пробы",
	 *     @OA\Parameter(
	 *     		name="EvnLabSample_id",
	 *     		in="query",
	 *     		description="Идентификаторы пробы",
	 *     		required=true,
	 *     		@OA\Schema(type="string")
	 * 	   ),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function PersonBySample_get() {
		$data = $this->ProcessInputData('getPersonBySample', null, true);
		$response = $this->dbmodel->getPersonBySample($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(['error_code' => 0, 'data' => $response]);
	}

	/**
	 * @OA\Get(
	 *     path="/api/EvnLabSample/ResearchHistory",
	 *     tags={"EvnLabSample"},
	 *     summary="Получение истории исследований",
	 *     @OA\Parameter(
	 *     		name="EvnLabSample_id",
	 *     		in="query",
	 *     		description="Идентификаторы пробы",
	 *     		required=true,
	 *     		@OA\Schema(type="string")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="Codes",
	 *     		in="query",
	 *     		description="Список кодов услуг",
	 *     		required=true,
	 *     		@OA\Schema(type="string")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="MinDate",
	 *     		in="query",
	 *     		description="Дата с",
	 *     		@OA\Schema(type="string")
	 * 	   ),
	 *     @OA\Parameter(
	 *     		name="MaxDate",
	 *     		in="query",
	 *     		description="Дата по",
	 *     		@OA\Schema(type="string")
	 * 	   ),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function ResearchHistory_get() {
		$data = $this->ProcessInputData('loadResearchHistory', null, true);
		$response = $this->dbmodel->loadResearchHistory($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(['error_code' => 0, 'data' => $response]);
	}

	/**
	 * получить результаты услугм для портала
	 */
	function getUslugaTestResultForPortal_get() {

		$data = $this->ProcessInputData('getUslugaTestResultForPortal', null, true);
		$response = $this->dbmodel->getUslugaTestResultForPortal($data);
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Post(
	 *     path="/api/EvnLabSample/OnChangeApproveResults",
	 *     tags={"EvnLabSample"},
	 *     summary="Вызов методов при изменении данных по услуге",
	 *     @OA\RequestBody(
	 *			required=true,
	 *     		@OA\MediaType(
	 *     			mediaType="application/x-www-form-urlencoded",
	 *     			@OA\Schema(
	 *     				@OA\Property(
	 *     					property="EvnUslugaParChanged",
	 *     					description="Идентификатор пробы",
	 *     					type="string",
	 *     					format="json"
	 * 					),
	 *     				@OA\Property(
	 *     					property="pmUser_id",
	 *     					description="Идентификатор пользователя",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="MedPersonal_id",
	 *     					description="Идентификатор врача",
	 *     					type="integer"
	 * 					),
	 *     				@OA\Property(
	 *     					property="methodAPI",
	 *     					description="Метод",
	 *     					type="string"
	 * 					),
	 *     				required={
	 *	 					"EvnUslugaParChanged",
	 *     					"pmUser_id"
	 * 					}
	 * 				)
	 * 			)
	 *		),
	 *     @OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   )
	 * )
	 */
	function OnChangeApproveResults_post() {
		$data = $this->ProcessInputData('onChangeApproveResults', null, true);
		$response = $this->dbmodel->onChangeApproveResults($data);
		if (!empty($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(['error_code' => 0, 'data' => $response]);
	}

	/**
	 * сохраним навый штрих-код
	 */
	function saveEvnLabSampleBarcodeAndNum_post() {

		$data = $this->ProcessInputData('saveEvnLabSampleBarcodeAndNum', null, false, false);
		if (empty($_SESSION['pmuser_id'])) {
			$_SESSION['pmuser_id'] = $data['pmUser_id'];
		}

		if (empty($data['session']['medpersonal_id'])) {
			$data['session']['medpersonal_id'] = NULL;
		}

		$resp = $this->dbmodel->saveNewEvnLabSampleBarCode($data);
		if (!empty($resp['Error_Msg'])) {
			$this->response(array(
				'error_msg' => $resp['Error_Msg'],
				'error_code' => '6'
			));
		}

		if (!empty($data['updateEvnLabSampleNum'])) {

			$data['EvnLabSample_ShortNum'] = substr($data['EvnLabSample_BarCode'], -4);
			$resp = $this->dbmodel->saveNewEvnLabSampleNum($data);

			if (!empty($resp['Error_Msg'])) {
				$this->response(array(
					'error_msg' => $resp['Error_Msg'],
					'error_code' => '6'
				));
			}
		}

		$this->response(array('error_code' => 0));
	}

	/**
	 * добавление результатов взятия пробы
	 */
	function createFromAPI_post()
	{
		$data = $this->ProcessInputData('createFromAPI', null, true);

		if(empty($data['MedPersonal_did']) && !empty($data['MedStaffFact_did'])){
			$this->load->swapi('common');
			$medStaffFact = $this->common->GET('MedPersonal/Info', ['MedStaffFact_id' => $data['MedStaffFact_did']], 'single');
			if(is_array($medStaffFact) && !empty($medStaffFact[0]['MedPersonal_id'])) {
				$data['MedPersonal_did'] = $medStaffFact[0]['MedPersonal_id'];
			}
		}else if(empty($data['MedPersonal_did']) && !empty($data['session']['medpersonal_id'])){
			$data['MedPersonal_did'] = $data['session']['medpersonal_id'];
		}
		if(empty($data['MedPersonal_did'])){
			$this->response(array(
				'error_code' => 5,
				'Error_Msg' => 'Не указан врач, обработка пакета невозможна'
			));
		}

		$data['UslugaComplexList'] = array_map('intval', explode(',', $data['UslugaComplexList']));
		if(count($data['UslugaComplexList']) > 0){
			$result = $this->dbmodel->createEvnLabSampleAPI($data);
			if(!empty($result[0]['EvnLabSample_id'])){
				$this->response(array(
					'error_code' => 0,
					'EvnLabSample_id' => $result[0]['EvnLabSample_id']
				));
			}else{
				$this->response(array(
					'error_code' => 6,
					'Error_Msg' => ($result['Error_Msg']) ? $result['Error_Msg'] : 'Ошибка при добавление результатов взятия пробы'
				));
			}
		}else{
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
	}

	/**
	 * добавление результатов взятия пробы
	 */
	function updateFromAPI_put()
	{
		$data = $this->ProcessInputData('updateFromAPI', null, true);
		if(empty($data['MedPersonal_did']) && !empty($data['MedStaffFact_did'])){
			$this->load->swapi('common');
			$medStaffFact = $this->common->GET('MedPersonal/Info', ['MedStaffFact_id' => $data['MedStaffFact_did']], 'single');
			if(is_array($medStaffFact) && !empty($medStaffFact[0]['MedPersonal_id'])) {
				$data['MedPersonal_did'] = $medStaffFact[0]['MedPersonal_id'];
			}
		}else if(empty($data['MedPersonal_did']) && !empty($data['session']['medpersonal_id'])) {
			$data['MedPersonal_did'] = $data['session']['medpersonal_id'];
		}
		if(empty($data['MedPersonal_did'])) {
			$this->response([
				'error_code' => 5,
				'Error_Msg' => 'Не указан врач, обработка пакета невозможна'
			]);
		}
		if(empty($data['MedPersonal_aid']) && !empty($data['MedStaffFact_aid'])) {
			$this->load->swapi('common');
			$medStaffFact = $this->common->GET('MedPersonal/Info', ['MedStaffFact_id' => $data['MedStaffFact_aid']], 'single');
			if(is_array($medStaffFact) && !empty($medStaffFact[0]['MedPersonal_id'])) {
				$data['MedPersonal_aid'] = $medStaffFact[0]['MedPersonal_id'];
			}
		}else if(empty($data['MedPersonal_aid']) && !empty($data['session']['medpersonal_id'])) {
			$data['MedPersonal_aid'] = $data['session']['medpersonal_id'];
		}
		if(empty($data['MedPersonal_aid'])) {
			$this->response([
				'error_code' => 5,
				'Error_Msg' => 'Не указан врач, обработка пакета невозможна'
			]);
		}

		$result = $this->dbmodel->updateEvnLabSampleAPI($data);
		if(!$this->isSuccessful($result)) {
			$this->response([
				'error_code' => 500,
				'Error_Msg' => $result['Error_Msg']
			]);
		} else {
			$this->response([
				'error_code' => 0
			]);
		}
	}

	/**
	 * добавление результатов взятия пробы
	 * PS: считается, что проба взята в промеде, ее надо найти по параметрам и подставить значения. По сути это обновление записи, а не создание.
	 */
	function createUslugaTest_post()
	{
		$data = $this->ProcessInputData('createUslugaTest', null, true);
		if(!empty($data['MedStaffFact_id'])){
			$this->load->model('MedPersonal_model', 'mpmodel');
			$medStaffFact = $this->common->GET('MedPersonal/Info', ['MedStaffFact_id' => $data['MedStaffFact_id']], 'single');
			if(is_array($medStaffFact) && !empty($medStaffFact[0]['MedPersonal_id'])) $data['MedPersonal_aid'] = $medStaffFact[0]['MedPersonal_id'];
		}else if(empty($data['MedPersonal_aid']) && !empty($data['session']['medpersonal_id'])){
			$data['MedPersonal_aid'] = $data['session']['medpersonal_id'];
		}
		if(empty($data['MedPersonal_aid'])){
			$this->response(array(
				'error_code' => 5,
				'Error_Msg' => 'Не указан врач, обработка пакета невозможна'
			));
		}

		$result = $this->dbmodel->createUslugaTestAPI($data);

		if (is_array($result) && !$this->isSuccessful($result)) {
			$this->response(array(
				'error_code' => 404,
				'Error_Msg' => $result['Error_Msg']
			));
		}
		$this->response(array(
			'UslugaTest_id' => $result
		));
	}

	/**
	 * добавление результатов взятия пробы
	 */
	function updateUslugaTest_put()
	{
		$data = $this->ProcessInputData('updateUslugaTest', null, true);
		if(!empty($data['MedStaffFact_id'])){
			$this->load->model('MedPersonal_model', 'mpmodel');
			$medStaffFact = $this->common->GET('MedPersonal/Info', ['MedStaffFact_id' => $data['MedStaffFact_id']], 'single');
			if(is_array($medStaffFact) && !empty($medStaffFact[0]['MedPersonal_id'])) $data['MedPersonal_aid'] = $medStaffFact[0]['MedPersonal_id'];
		}else if(empty($data['MedPersonal_aid']) && !empty($data['session']['medpersonal_id'])){
			$data['MedPersonal_aid'] = $data['session']['medpersonal_id'];
		}
		if(empty($data['MedPersonal_aid'])){
			//MedPersonal_aid	врач выполнивший анализ
			$this->response(array(
				'error_code' => 5,
				'Error_Msg' => 'Не указан врач, обработка пакета невозможна'
			));
		}

		$this->load->model('EvnLabSample_model', 'EvnLabSample_model');
		$result = $this->EvnLabSample_model->updateUslugaTestAPI($data);
		if (is_array($result) && !$this->isSuccessful($result)) {
			$this->response(array(
				'error_code' => 404,
				'Error_Msg' => $result['Error_Msg']
			));
		}
		$this->response(array(
			'error_code' => 0
		));
	}
}


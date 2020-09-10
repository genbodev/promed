<?php
defined('BASEPATH') or die ('No direct script access allowed');

/**
 * Class BSME
 * @property-read BSME_model $dbmodel
 */
class BSME extends swController {
	public $inputRules = array(
		'saveEvnForensicGeneticRequest'=>array(
			//Блок общих полей
			//array('field' => 'EvnForensic_Date', 'label' => 'Дата заявки', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'EvnForensic_ResDate', 'label' => 'Дата постановления ', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'EvnForensicGenetic_id', 'label' => 'Идентификатор заявки', 'rules' => '', 'type' => 'id'),
			array('field' => 'Person_cid', 'label' => 'Направившее лицо', 'rules' => 'required', 'type' => 'id'),
			//Журнал регистрации вещественных доказательств и документов к ним в лаборатории
			array('field' => 'EvnForensicGeneticEvid_id', 'label' => 'Идентификатор журнала регистрации вещественных доказательств и документов к ним в лаборатории', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnForensicGeneticEvid_AccDocNum', 'label' => '№ основного сопроводительного документа', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnForensicGeneticEvid_AccDocDate', 'label' => 'Дата основного сопроводительного документа', 'rules' => '', 'type' => 'date'),
			array('field' => 'EvnForensicGeneticEvid_AccDocNumSheets', 'label' => 'Кол-во листов документов', 'rules' => '', 'type' => 'int'),
			array('field' => 'Org_id', 'label' => 'Идентификатор учреждения направившего', 'rules' => '', 'type' => 'id'),
			array('field' => 'Person', 'label' => 'Потерпевшие/обвиняемые', 'rules' => '', 'type' => 'string'),
			array('field' => 'Evidence', 'label' => 'Вещественные доказательства', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnForensicGeneticEvid_Facts', 'label' => 'Краткие обстоятельства дела', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnForensicGeneticEvid_Goal', 'label' => 'Цель экспертизы', 'rules' => '', 'type' => 'string'),
			//Журнал регистрации биологических образцов, изъятых у живых лиц в лаборатории
			array('field' => 'EvnForensicGeneticSampleLive_id', 'label' => 'Идентификатор журнала регистрации биологических образцов, изъятых у живых лиц в лаборатории', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnForensicGeneticSampleLive_TakeDT', 'label' => 'Дата изъятия образцов', 'rules' => '', 'type' => 'date'),
			array('field' => 'EvnForensicGeneticSampleLive_TakeTime', 'label' => 'Время изъятия образцов', 'rules' => '', 'type' => 'time'),
			array('field' => 'Person_zid', 'label' => 'Идентификатор исследуемого лица журнала регистрации биологических образцов, изъятых у живых лиц в лаборатории', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnForensicGeneticSampleLive_Basis', 'label' => 'Основания для получения образцов', 'rules' => '', 'type' => 'string'),
			array('field' => 'BioSample', 'label' => 'Биологические образцы', 'rules' => '', 'type' => 'string'),
			//Журнал регистрации биологических образцов, изъятых у живых лиц в лаборатории для молекулярно-генетического исследования
			array('field' => 'EvnForensicGeneticGenLive_id', 'label' => 'Идентификатор журнала регистрации биологических образцов, изъятых у живых лиц в лаборатории для молекулярно-генетического исследования', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnForensicGeneticGenLive_TakeDate', 'label' => 'Дата изъятия образцов', 'rules' => '', 'type' => 'date'),
			array('field' => 'PersonBioSample', 'label' => 'Список исследуемых лиц', 'rules' => '', 'type' => 'string'),
			array('field' => 'BioSampleForMolGenRes', 'label' => 'Список биологических образцов', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnForensicGeneticGenLive_Facts', 'label' => 'Краткие обстоятельства дела', 'rules' => '', 'type' => 'string'),
			//Журнал регистрации исследований мазков и тампонов в лаборатории
			array('field' => 'EvnForensicGeneticSmeSwab_id', 'label' => 'Идентификатор журнала регистрации исследований мазков и тампонов в лаборатории', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnForensicGeneticSmeSwab_DelivDT', 'label' => 'Дата поступления образцов', 'rules' => '', 'type' => 'date'),
			array('field' => 'EvnForensicGeneticSmeSwab_DelivTime', 'label' => 'Время поступления образцов', 'rules' => '', 'type' => 'time'),
			array('field' => 'ReasearchedPerson_id', 'label' => 'Исследуемое лицо журнала регистрации исследований мазков и тампонов в лаборатории', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnForensicGeneticSmeSwab_Basis', 'label' => 'Основания для получения образцов', 'rules' => '', 'type' => 'string'),
			array('field' => 'Sample', 'label' => 'Список образцов', 'rules' => '', 'type' => 'string'),
		),
		'saveForenPersRequest'=>array(
			//Блок общих полей
			//array('field' => 'EvnForensic_Date', 'label' => 'Дата заявки', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'EvnForensicSub_id', 'label' => 'Идентификатор заявки', 'rules' => '', 'type' => 'id', 'default'=>NULL),
			array('field' => 'EvnForensicSub_pid', 'label' => 'Идентификатор первичной экспертизы', 'rules' => '', 'type' => 'int'),
			array('field' => 'XmlType_id', 'label' => 'Идентификатор типа итогового документа', 'rules' => '', 'type' => 'id', 'default'=>13 /*Заключение по уголовному делу*/),
			array('field' => 'ForensicSubType_id', 'label' => 'Идентификатор типа заявки', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnForensicSub_Num', 'label' => 'Номер заявки', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnForensicSub_ExpertiseComeDate', 'label' => 'Дата поступления экспертизы', 'rules' => '', 'type' => 'date'),
			array('field' => 'EvnForensicSub_ExpertiseComeTime', 'label' => 'Время поступления экспертизы', 'rules' => '', 'type' => 'time'),
			array('field' => 'EvnForensicSub_ResDate', 'label' => 'Дата постановления', 'rules' => '', 'type' => 'date'),
			array('field' => 'EvnForensicSub_ResTime', 'label' => 'Время постановления', 'rules' => '', 'type' => 'time'),
			array('field' => 'Person_id', 'label' => 'Подэкспертное лицо', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Person_cid', 'label' => 'Инициатор', 'rules' => '', 'type' => 'id'),
			array('field' => 'Org_did', 'label' => 'Идентификатор учреждения направившего', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedPersonal_eid', 'label' => 'Идентификатор эксперта', 'rules' => '', 'type' => 'id'),
			array('field' => 'ForensicIniciatorPost_id', 'label' => 'Идентификатор должности инициатора', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnForensicSub_AccidentDate', 'label' => 'Дата происшествия', 'rules' => '', 'type' => 'date'),
			array('field' => 'EvnForensicSub_AccidentTime', 'label' => 'Время происшествия', 'rules' => '', 'type' => 'time'),
			array('field' => 'comment', 'label' => 'Описание файлов', 'rules' => '', 'type' => 'assoc_array'),
			array( 'field' => 'EvnForensicSub_Inherit', 'label' => 'Копировать разделы заключения из связной экспертизы?', 'rules' => '', 'type' => 'int' ),
		), 
		'saveForenChemOwnRequest'=>array(
			array('field' => 'EvnForensicChem_id', 'label' => 'Идентификатор заявки', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnForensicChemBiomat_id', 'label' => 'Идентификатор записи в журнале', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnForensicChemBiomat_ReceivedDate', 'label' => 'Дата поступления', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'Person_zid', 'label' => 'Исследуемое лицо', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Person_sid', 'label' => 'Назначившее лицо', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnForensicChemBiomat_Facts', 'label' => 'Краткие обстоятельства дела', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'EvnForensicChemBiomat_Objective', 'label' => 'Цели экспертизы', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'BioSample', 'label' => 'Биологические образцы', 'rules' => '', 'type' => 'string'),
		),
		'saveEvnForenCrimeOwnRequest'=>array(
			array('field' => 'EvnForensic_ResDate', 'label' => 'Дата постановления', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'Person_cid', 'label' => 'Назначившее лицо', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnForensicCrime_id', 'label' => 'Идентификатор заявки', 'rules' => '', 'type' => 'id'),
			//Журнал регистрации вещественных доказательств и документов к ним в лаборатории
			
			array('field' => 'EvnForensicCrimeEvid_id', 'label' => 'Идентификатор записи журнала вешественных доказательств', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnForensicCrimeEvid_ForDate', 'label' => 'Дата поступления вещественных доказательств', 'rules' => '', 'type' => 'date'),
			array('field' => 'EvnForensicCrimeEvid_AccDocNum', 'label' => '№ основного сопроводительного документа', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnForensicCrimeEvid_AccDocDate', 'label' => 'Дата основного сопроводительного документа', 'rules' => '', 'type' => 'date'),
			array('field' => 'EvnForensicCrimeEvid_AccDocNumSheets', 'label' => 'Кол-во листов документов', 'rules' => '', 'type' => 'int'),
			array('field' => 'Org_id', 'label' => 'Идентификатор учреждения направившего', 'rules' => '', 'type' => 'id'),
			array('field' => 'Person', 'label' => 'Потерпевшие/обвиняемые', 'rules' => '', 'type' => 'string'),
			array('field' => 'Evidence', 'label' => 'Вещественные доказательства', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnForensicCrimeEvid_Facts', 'label' => 'Краткие обстоятельства дела', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnForensicCrimeEvid_Goal', 'label' => 'Цель экспертизы', 'rules' => '', 'type' => 'string'),
			//Журнал регистрации фоторабот в медико-криминалистическом отделении
			array('field' => 'EvnForensicCrimePhot_id', 'label' => 'Идентификатор записи журнала фоторабот', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnForensicCrimePhot_ActNum', 'label' => '№ Акта', 'rules' => '', 'type' => 'int'),
			array('field' => 'EvnForensicCrimePhot_ShoDate', 'label' => 'Дата съёмки', 'rules' => '', 'type' => 'date'),
			array('field' => 'EvnForensicCrimePhot_Person_zid', 'label' => 'Исследуемое лицо', 'rules' => '', 'type' => 'id'),
			array('field' => 'Diag_id', 'label' => 'Судмед диагноз', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnForensicCrimePhot_PosKol', 'label' => 'Количество позитивов', 'rules' => '', 'type' => 'int'),
			array('field' => 'EvnForensicCrimePhot_NegKol', 'label' => 'Количество негативов', 'rules' => '', 'type' => 'int'),
			array('field' => 'EvnForensicCrimePhot_SighSho', 'label' => 'Обзорная съемка', 'rules' => '', 'type' => 'int'),
			array('field' => 'EvnForensicCrimePhot_Macro', 'label' => 'Макро съемка', 'rules' => '', 'type' => 'int'),
			array('field' => 'EvnForensicCrimePhot_Micro', 'label' => 'Микро съемка', 'rules' => '', 'type' => 'int'),
			//Журнал регистрации  разрушений почки на планктон
			array('field' => 'EvnForensicCrimeDesPlan_id', 'label' => 'Идентификатор записи журнала разрушений почки на планктон', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnForensicCrimeDesPlan_ForDate', 'label' => 'Дата поступления', 'rules' => '', 'type' => 'date'),
			array('field' => 'Person_zid', 'label' => 'Исследуемое лицо', 'rules' => '', 'type' => 'id'),
			array('field' => 'Person_eid', 'label' => 'Назначившее лицо', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnForensicCrimeDesPlan_ActCorpNum', 'label' => '№ акта вскрытия', 'rules' => '', 'type' => 'int'),
			array('field' => 'EvnForensicCrimeDesPlan_ActCorpDate', 'label' => 'Дата вскрытия', 'rules' => '', 'type' => 'date'),
			array('field' => 'EvnForensicCrimeDesPlan_Facts', 'label' => 'Краткие обстоятельства дела', 'rules' => '', 'type' => 'string'),
		),
		'saveForenMedCrimDirection'=>array(
			array('field' => 'Evn_pid', 'label' => 'Идентификатор родительской заявки', 'rules' => 'required', 'type' => 'id'),	
			array('field' => 'CrymeStudyType_id', 'label' => 'Тип исследования', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Person_zid', 'label' => 'Исследуемое лицо', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Evidence', 'label' => 'Материалы', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'EvnForensicCrimeExCorp_Liquid', 'label' => 'Фиксирующая жидкость', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'EvnForensicCrimeExCorp_OpinNum', 'label' => 'Заключение №', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'EvnForensicCrimeExCorp_TakeDT', 'label' => 'Дата взятия', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'EvnForensicCrimeExCorp_TakeTime', 'label' => 'Время взятия', 'rules' => 'required', 'type' => 'time'),
			array('field' => 'EvnForensicCrimeExCorp_Seal', 'label' => 'Опечатано печатью и оттиском', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'EvnForensicCrimeExCorp_Facts', 'label' => 'Краткие обстоятельства дела', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'EvnForensicCrimeExCorp_Ques', 'label' => 'Вопросы подлежащие разрешению', 'rules' => 'required', 'type' => 'string'),
		),
		'saveForenCorpOwnRequest'=>array(
			array('field' => 'Person_zid', 'label' => 'Доставившее лицо', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Person_id', 'label' => 'Исследуемое лицо', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Evidence', 'label' => 'Вещественные доказательства', 'rules' => '', 'type' => 'string'),
			array('field' => 'ValueStuff', 'label' => 'Ценные вещи', 'rules' => '', 'type' => 'string'),
		),
		'saveForenComplexOwnRequest'=>array(
			//array('field' => 'EvnForensic_Date', 'label' => 'Дата заявки', 'rules' => 'required', 'type' => 'date'),
			//array('field' => 'EvnForensic_ResDate', 'label' => 'Дата постановления ', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'EvnForensic_id', 'label' => 'Идентификатор заявки', 'rules' => '', 'type' => 'id'),
			array('field' => 'Person_cid', 'label' => 'Направившее лицо', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnForensicComplexResearch_id', 'label' => 'Идентификатор журнала регистрации судебно-медицинских исследований и медицинских судебных экспертиз', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnForensicComplexResearch_Base', 'label' => 'Основание для проведения экспертизы', 'rules' => '', 'type' => 'string'),
			array('field' => 'Evidence', 'label' => 'Перечень документов', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnForensicComplexResearchDopMat', 'label' => 'Дополнительно затребованные материалы', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnForensicComplexResearchComission', 'label' => 'Состав комиссии', 'rules' => '', 'type' => 'string')
		),
		'getNextRequestNumber'=>array(),
		'getNextForenPersRequestNumber'=>array(
			array('field' => 'ForensicSubType_id', 'label' => 'Идентификатор типа заявки', 'rules' => 'required', 'type' => 'id'),
		),
		'getJournalRequestList'=>array(
			array('field'=>'start', 'label'=>'','rules' => '', 'type' => 'int', 'default'=>0),
			array('field'=>'limit', 'label'=>'','rules' => '', 'type' => 'int', 'default'=>10),
			array('field'=>'JournalType', 'label'=>'Тип журнала','rules' => 'required', 'type' => 'string'),
			array('field'=>'ARMType', 'label'=>'Тип АРМ','rules' => '', 'type' => 'string'),
			array('field'=>'EvnStatus_SysNick', 'label'=>'Статус заявки','rules' => '', 'type' => 'string'),
			array('field'=>'filters', 'label' => 'Фильтры', 'rules'	=> '', 'type' => 'string'),
			//Скорее всего, поля поиска будут различаться как для армов так и для служб,поэтому вынесем их в json массив 
			//и будем разводить непосредственно в методах модели
			array('field'=>'search', 'label'=>'Параметры поиска','rules' => '', 'type' => 'string'),

			array('field' => 'EvnForensic_Num', 'label' => 'Номер заявки','rules' => '', 'type' => 'int'),
			array('field' => 'Evn_insDT', 'label' => 'Дата экспертизы','rules' => '', 'type' => 'string'),
			array('field' => 'Person_SurName', 'label' => 'Фамилия','rules' => '', 'type' => 'string'),
			array('field' => 'Person_FirName', 'label' => 'Имя','rules' => '', 'type' => 'string'),
			array('field' => 'Person_SecName', 'label' => 'Отчество','rules' => '', 'type' => 'string'),
			array( 'field' => 'Expert_id', 'label' => 'Идентификатор эксперта', 'rules' => '', 'type' => 'id' ),
		),
		'saveForenCorpBloodDirection'=>array(
			array('field' => 'Evn_pid', 'label' => 'Идентификатор родительской заявки', 'rules' => '', 'type' => 'id'),
			array('field' => 'ReasearchedPerson_id', 'label' => 'Исследуемое лицо', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnForensicGeneticCadBlood_TakeDate', 'label' => 'Дата взятия', 'rules' => '', 'type' => 'date'),
			array('field' => 'Evn_pid', 'label' => 'Идентификатор родительской заявки', 'rules' => '', 'type' => 'id'),
		),
		'saveForenKidneyPlanktDirection'=>array(
			array('field' => 'Person_zid', 'label' => 'Исследуемое лицо', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnForensic_OpenActNum', 'label' => '№ акта вскрытия', 'rules' => '', 'type' => 'int'),
			array('field' => 'EvnForensic_OpenAcrDate', 'label' => 'Дата вскрытия', 'rules' => '', 'type' => 'date'),
			array('field' => 'EvnForensic_Facts', 'label' => 'Краткие обстоятельства дела', 'rules' => '', 'type' => 'string'),
		),
		'saveForenEvidDirection'=>array(),
		'saveForenBioChemDirection'=>array(
			array('field' => 'Person_zid', 'label' => 'Исследуемое лицо', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnForensic_Goal', 'label' => 'Цель', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'EvnForensic', 'label' => 'Дата смерти', 'rules' => 'required', 'type' => 'datetime'),
			array('field' => 'EvnForensic', 'label' => '№ заключения о вскрытии', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'EvnForensic', 'label' => 'Дата заключения о вскрытии', 'rules' => 'required', 'type' => 'datetime'),
			array('field' => 'EvnForensic', 'label' => 'Обстоятельства дела', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'EvnForensic', 'label' => 'Известные на момент смерти заболевания', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'EvnForensic', 'label' => 'Принятые незадолго до смерти лекарства', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'EvnForensic', 'label' => 'Принятые незадолго до смерти алкогольные напитки', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'EvnForensic', 'label' => 'Принятые незадолго до смерти отравляющие и наркотические в-ва', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'EvnForensic', 'label' => 'Предполагаемая причина смерти', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'EvnForensic', 'label' => 'Дата забора материала', 'rules' => 'required', 'type' => 'datetime'),
			array('field' => 'EvnForensic', 'label' => 'Дата отправки материала на исследование', 'rules' => 'required', 'type' => 'datetime'),
		),
		'saveForenChemDirection'=>array(
			array('field' => 'Evn_pid', 'label' => 'Идентификатор родительской заявки', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnForensicChem_id', 'label' => 'Идентификатор заявки', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnForensicChemDirection_id', 'label' => 'Идентификатор записи в журнале', 'rules' => '', 'type' => 'id'),
			array('field' => 'Jar', 'label' => 'Банки', 'rules' => '', 'type' => 'string'),
			array('field' => 'Flak', 'label' => 'Флаконы', 'rules' => '', 'type' => 'string'),
			array('field' => 'Person_zid', 'label' => 'Исследуемое лицо', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnForensicChemDirection_DeathDate', 'label' => 'Дата смерти', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'EvnForensicChemDirection_DissectionDate', 'label' => 'Дата вскрытия', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'EvnForensicChemDirection_Facts', 'label' => 'Обстоятельства дела', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'EvnForensicChemDirection_CauseOfDeath', 'label' => 'Предполагаемая причина смерти', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'EvnForensicChemDirection_Resolve', 'label' => 'Вопросы для разрешения', 'rules' => 'required', 'type' => 'string'),
		),
		'saveForenBludSampleDirection'=>array(
			array('field' => 'Person_zid', 'label' => 'Исследуемое лицо', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnForensic_TakeDate', 'label' => 'Дата забора', 'rules' => 'required', 'type' => 'datetime'),
		),
		'printBludSampleResearchDirection'=>array(
			array('field' => 'Evn_id', 'label' => 'Идентификатор случая', 'rules' => 'required', 'type' => 'id'),
		),
		'saveForenVirusologicDirection'=>array(
			array('field' => 'Person_zid', 'label' => 'Исследуемое лицо', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnForensic', 'label' => 'Место обнаружения трупа', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'EvnForensic', 'label' => 'Дата заболевания', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'EvnForensic', 'label' => 'Дата заболевания', 'rules' => 'required', 'type' => 'datetime'),
			array('field' => 'EvnForensic', 'label' => 'Судебно-медицинский диагноз', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'EvnForensic', 'label' => 'Наименование секционного материала', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'EvnForensic', 'label' => 'Дата смерти', 'rules' => 'required', 'type' => 'datetime'),
			array('field' => 'EvnForensic', 'label' => 'Время смерти', 'rules' => 'required', 'type' => 'time'),
			array('field' => 'EvnForensic', 'label' => 'Дата вскрытия', 'rules' => 'required', 'type' => 'datetime'),
			array('field' => 'EvnForensic', 'label' => 'Время вскрытия', 'rules' => 'required', 'type' => 'time'),
			array('field' => 'EvnForensic', 'label' => 'Дата отправления материала на исследование', 'rules' => 'required', 'type' => 'datetime'),
			array('field' => 'EvnForensic', 'label' => 'Акт вскрытия №', 'rules' => 'required', 'type' => 'string'),
		),
		//array('field' => 'EvnForensic', 'label' => ' ', 'rules' => '', 'type' => 'id'),
		//array('field' => 'EvnForensic', 'label' => ' ', 'rules' => '', 'type' => 'string'),
		//array('field' => 'EvnForensic', 'label' => ' ', 'rules' => '', 'type' => 'datetime'),
		//array('field' => 'EvnForensic', 'label' => ' ', 'rules' => '', 'type' => 'int'),
		//array('field' => 'EvnForensic', 'label' => ' ', 'rules' => '', 'type' => 'time'),
		'printVirusologicResearchDirection'=>array(
			array('field' => 'Evn_id', 'label' => 'Идентификатор случая', 'rules' => 'required', 'type' => 'id'),
		),
		'getEvnForensicGeneticRequest'=>array(
			array('field' => 'EvnForensic_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
		),
		'getForenPersRequest'=>array(
			array('field' => 'EvnForensic_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
		),
		'getEvnForenCrimeRequest'=>array(
			array('field' => 'EvnForensic_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
		),
		'getEvnForenComplexRequest'=>array(
			array('field' => 'EvnForensic_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
		),
		
		'loadEvnForensicTypeList' => array(),
		'printEvnDirectionForensic' => array(
			array( 'field' => 'EvnForensic_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id' ),
			array( 'field' => 'armName', 'label' => 'Название АРМа', 'rules' => '', 'type' => 'string' ),
		),

		'checkEvnForensic' => array(
			array(
				'field' => 'EvnForensic_id',
				'label' => 'Идентификатор заявки',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStatusHistory_Cause',
				'label' => 'Комментарий',
				'rules' => '',
				'type' => 'string'
			),
		),
		'approveEvnForensic' => array(
			array(
				'field' => 'EvnForensic_id',
				'label' => 'Идентификатор заявки',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'revisionEvnForensic' => array(
			array( 'field' => 'EvnForensic_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id' ),
			array( 'field' => 'EvnXml_id','label' => 'Идентификатор документа','rules' => 'required','type' => 'id' ),
			array( 'field' => 'EvnStatusHistory_Cause', 'label' => 'Комментарий', 'rules' => '', 'type' => 'string' )
		),
		'saveEvnForensicGeneticExpertiseProtocol' => array(
			array('field' => 'EvnForensic_id','label' => 'Идентификатор заявки','rules' => 'required','type' => 'id'),
			array('field' => 'ActVersionForensic_id','label' => 'Идентификатор версии акта заключения','rules' => '','type' => 'id'),
			array('field' => 'ActVersionForensic_Num','label' => 'Номер акта заключения','rules' => '','type' => 'int'),
			array('field' => 'ActVersionForensic_Text','label' => 'Акт заключение эксперта','rules' => 'required','type' => 'string'),
			array('field' => 'ActVersionForensic_FactBegDT','label' => 'Акт заключение эксперта','rules' => 'required','type' => 'date'),
			array('field' => 'ActVersionForensic_FactEndDT','label' => 'Акт заключение эксперта','rules' => 'required','type' => 'date'),
			/* Экспертиза по журналу регистрации трупной крови в лаборатории */
			array('field' => 'EvnForensicGeneticCadBlood_id','label' => '','rules' => '','type' => 'id'),
			array('field' => 'EvnForensicGeneticCadBlood_StudyDate','label' => '','rules' => '','type' => 'date'),
			array('field' => 'EvnForensicGeneticCadBlood_IsIsosTestEA','label' => '','rules' => '','type' => 'checkbox'),
			array('field' => 'EvnForensicGeneticCadBlood_IsIsosTestEB','label' => '','rules' => '','type' => 'checkbox'),
			array('field' => 'EvnForensicGeneticCadBlood_IsIsosTestIsoB','label' => '','rules' => '','type' => 'checkbox'),
			array('field' => 'EvnForensicGeneticCadBlood_IsIsosTestIsoA','label' => '','rules' => '','type' => 'checkbox'),
			array('field' => 'EvnForensicGeneticCadBlood_IsIsosAntiA','label' => '','rules' => '','type' => 'checkbox'),
			array('field' => 'EvnForensicGeneticCadBlood_IsIsosAntiB','label' => '','rules' => '','type' => 'checkbox'),
			array('field' => 'EvnForensicGeneticCadBlood_IsIsosAntiH','label' => '','rules' => '','type' => 'checkbox'),
			array('field' => 'EvnForensicGeneticCadBlood_MatCondition','label' => '','rules' => '','type' => 'string'),
			array('field' => 'EvnForensicGeneticCadBlood_IsosOtherSystems','label' => '','rules' => '','type' => 'string'),
			array('field' => 'EvnForensicGeneticCadBlood_Result','label' => '','rules' => '','type' => 'string'),
			/* Экспертиза по журналу регистрации биологических образцов, изъятых у живых лиц в лаборатории */
			array('field' => 'EvnForensicGeneticSampleLive_id','label' => '','rules' => '','type' => 'id'),
			//array('field' => 'EvnForensicGeneticSampleLive_StudyDate','label' => '','rules' => '','type' => 'date'),
			array('field' => 'EvnForensicGeneticSampleLive_IsIsosTestEA','label' => '','rules' => '','type' => 'checkbox'),
			array('field' => 'EvnForensicGeneticSampleLive_IsIsosTestEB','label' => '','rules' => '','type' => 'checkbox'),
			array('field' => 'EvnForensicGeneticSampleLive_IsIsosCyclAntiA','label' => '','rules' => '','type' => 'checkbox'),
			array('field' => 'EvnForensicGeneticSampleLive_IsIsosCyclAntiB','label' => '','rules' => '','type' => 'checkbox'),
			array('field' => 'EvnForensicGeneticSampleLive_Result','label' => 'Результаты определения групп по исследованым системам','rules' => '','type' => 'string'),
			array('field' => 'EvnForensicGeneticSampleLive_IsosOtherSystems','label' => 'Изосерология: другие системы','rules' => '','type' => 'string'),
			array('field' => 'BioSample','label' => '','rules' => '','type' => 'string'),
		),
		'saveForenPersExpertiseProtocol' => array(
			array('field' => 'EvnForensic_id','label' => 'Идентификатор заявки','rules' => 'required','type' => 'id'),
			array('field' => 'ActVersionForensic_id','label' => 'Идентификатор версии акта заключения','rules' => '','type' => 'id'),
			array('field' => 'ActVersionForensic_Num','label' => 'Номер акта заключения','rules' => '','type' => 'int'),
			//array('field' => 'ActVersionForensic_Text','label' => 'Акт заключение эксперта','rules' => 'required','type' => 'string'),
			array('field' => 'ActVersionForensic_FactBegDT','label' => 'Фактическая дата начала экспертизы','rules' => 'required','type' => 'date'),
			array('field' => 'ActVersionForensic_FactEndDT','label' => 'Фактическая дата окончания экспертизы','rules' => 'required','type' => 'date'),
			
			array('field' => 'EvnXml_id','label' => 'Идентификатор документа','rules' => 'required','type' => 'id'),
			array('field' => 'XmlData', 'label' => 'Имена разделов со значениями', 'rules' => 'trim', 'type' => 'string' ),
		),
		'getActVersionForensicNum' => array(
			array('field' => 'EvnForensic_id','label' => 'Идентификатор заявки','rules' => 'required','type' => 'id'),
		),
		'saveForenPersDopMatQuery' => array(
			array('field' => 'EvnForensicSub_id','label' => 'Идентификатор заявки','rules' => 'required','type' => 'id'),
			array('field' => 'EvnForensicSubDopMatQuery_id','label' => 'Идентификатор запроса','rules' => '','type' => 'id'),
			array('field' => 'EvnForensicSubDopMatQuery_Name','label' => 'Запрашиваемый материал','rules' => 'required','type' => 'string'),
			array('field' => 'Org_id','label' => 'Органзация, в которой запрашивается материал','rules' => 'required','type' => 'id'),
			array('field' => 'EvnForensicSubDopMatQuery_ResearchDate','label' => 'Дата','rules' => 'required','type' => 'date'),
			array('field' => 'Person_aid','label' => 'Человек, на чье имя запрашивается материал','rules' => 'required','type' => 'id'),
		),
		'getForenPersDopMatQuery' => array(
			array('field' => 'EvnForensicSubDopMatQuery_id','label' => 'Идентификатор запроса','rules' => 'required','type' => 'id'),
		),
		'saveEvnForensicResultOut'=> array(
			array('field' => 'EvnForensic_id','label' => 'Идентификатор заявки','rules' => 'required','type' => 'id'),
			array('field' => 'Person_gid', 'label' => 'Лицо, получающее заключение', 'rules' => '', 'type' => 'id'),
			array('field' => 'RecipientIdentity_Num','label' => 'Номер удостоверения получающего заключение','rules' => 'max_length[100]','type' => 'string'),
			array('field' => 'PostTicket_Num','label' => 'Номер почтовой квитанции, куда отправлено заключение','rules' => 'max_length[100]','type' => 'string'),
			array('field' => 'PostTicket_Date','label' => 'Дата почтовой квитанции, куда отправлено заключение','rules' => '','type' => 'date'),
		),
		
		'getEvnForensicSubArchive' => array(
			array('field' => 'start', 'label' => '','rules' => '', 'type' => 'int', 'default' => 0),
			array('field' => 'limit', 'label' => '','rules' => '', 'type' => 'int', 'default' => 10),
			array('field' => 'XmlType_id', 'label' => 'Идентификатор типа итогового документа', 'rules' => '', 'type' => 'id', 'default' => 0),
			array('field' => 'ForensicSubType_id', 'label' => 'Идентификатор типа заявки', 'type' => 'id', 'default' => 0),
			array('field' => 'JournalType', 'label' => 'Тип журнала','rules' => 'required', 'type' => 'string'),
			//array('field' => 'filterField', 'label' => '','rules' => '', 'type' => 'string'),
			//array('field' => 'filterVal', 'label' => '','rules' => '', 'type' => 'string'),
			array('field' => 'begDate', 'label' => '','rules' => '', 'type' => 'date'),
			array('field' => 'endDate', 'label' => '','rules' => '', 'type' => 'date'),
			
			array('field' => 'EvnForensic_Num', 'label' => 'Значение поля фильтрации','rules' => '', 'type' => 'int'),
			array('field' => 'Person_SurName', 'label' => 'Фамилия','rules' => '', 'type' => 'string'),
			array('field' => 'Person_FirName', 'label' => 'Имя','rules' => '', 'type' => 'string'),
			array('field' => 'Person_SecName', 'label' => 'Отчество','rules' => '', 'type' => 'string'),
			array('field' => 'MedPersonal_eid', 'label'=>'Идентификатор эксперта', 'rules' => '', 'type' => 'id'),
		),
		'saveEvnForenSubDopMatQueryResult'=>array(
			array('field' => 'EvnForensicSubDopMatQuery_id','label' => 'Идентификатор направления','rules' => 'required','type' => 'id'),
			array('field' => 'EvnForensicSubDopMatQuery_ResultDate','label' => 'Дата получения','rules' => 'required','type' => 'date'),
			array('field' => 'EvnForensicSubDopMatQuery_ResultTime','label' => 'Время получения','rules' => 'required','type' => 'time'),
		),
		'deleteEvnForenSubDopMatQuery'=>array(
			array('field' => 'EvnForensicSubDopMatQuery_id','label' => 'Идентификатор направления','rules' => 'required','type' => 'id'),
		),
		'getRequestCount'=>array(
			array('field'=>'ARMType', 'label'=>'Тип АРМ','rules' => '', 'type' => 'string'),
			array('field'=>'JournalType', 'label'=>'Тип журнала','rules' => '', 'type' => 'string', 'default'=>null),
			//Дополнительный параметр для службы судмеда потерпевших обвиняемых и др. лиц
			array('field' => 'ForensicSubType_id','label' => 'Тип заявки','rules' => '','type' => 'id'),
			//Скорее всего, поля поиска будут различаться как для армов так и для служб,поэтому вынесем их в json массив 
			//и будем разводить непосредственно в методах модели
			array('field'=>'search', 'label'=>'Параметры поиска','rules' => '', 'type' => 'string'),
			
		),
		'deleteEvnForensic'=>array(
			array('field' => 'EvnForensic_id','label' => 'Идентификатор заявки','rules' => 'required','type' => 'id'),
		),
		'exportJournalRequestToDbf'=>array(
			array('field'=>'JournalType', 'label'=>'Тип журнала','rules' => 'required', 'type' => 'string'),
			array('field'=>'MedService_id', 'label'=>'Идентификатор службы','rules' => 'required', 'type' => 'id'),
			array('field'=>'begDate', 'label'=>'','rules' => 'required', 'type' => 'date'),
			array('field'=>'endDate', 'label'=>'','rules' => 'required', 'type' => 'date')
		),
		'getEvnForensicComplexArchive'=>array(
			array('field'=>'start', 'label'=>'','rules' => '', 'type' => 'int', 'default'=>0),
			array('field'=>'limit', 'label'=>'','rules' => '', 'type' => 'int', 'default'=>10),
			array('field'=>'JournalType', 'label'=>'Тип журнала','rules' => 'required', 'type' => 'string'),
			array('field'=>'filterField', 'label'=>'','rules' => '', 'type' => 'string'),
			array('field'=>'filterVal', 'label'=>'','rules' => '', 'type' => 'string'),
			array('field'=>'begDate', 'label'=>'','rules' => '', 'type' => 'string'),
			array('field'=>'endDate', 'label'=>'','rules' => '', 'type' => 'string')
		),
		'getEvnForensicSubDopDocQuery'=>array(
			array('field' => 'EvnForensicSub_id', 'label' => 'Идентификатор заявки', 'rules' => '', 'type' => 'id', 'default'=>NULL),
			array('field' => 'EvnForensicSubDopDocQuery_id', 'label' => 'Идентификатор запроса документа', 'rules' => '', 'type' => 'id', 'default'=>NULL),
		),
		'saveEvnForensicSubDopDocQuery'=>array(
			array('field' => 'EvnForensicSub_id', 'label' => 'Идентификатор заявки', 'rules' => '', 'type' => 'id', 'default'=>NULL),
			array('field' => 'EvnForensicSubDopDocQuery_id', 'label' => 'Идентификатор запроса документа', 'rules' => '', 'type' => 'id', 'default'=>NULL),
			array('field' => 'EvnForensicSubDopDocQuery_Num', 'label' => 'Номер запроса', 'rules' => 'max_length[128]', 'type' => 'string', 'default'=>NULL),
			array('field' => 'EvnForensicSubDopDocQuery_Date', 'label' => 'Дата заявки', 'rules' => '', 'type' => 'date'),
			array('field' => 'EvnForensicSubDopDocQuery_Iniciator','label' => 'Кому (ФИО)','rules' => 'required, max_length[255]','type' => 'string'),
			array('field' => 'EvnForensicSubDopDocQuery_IniciatorJob','label' => 'Кому (Должность, место работы)','rules' => 'required, max_length[255]','type' => 'string'),
			array('field' => 'EvnForensicSubDopDocQuery_Person','label' => 'Подэкспертный','rules' => 'required, max_length[255]','type' => 'string'),
			array('field' => 'EvnForensicSubDopDocQuery_Subject','label' => 'Что предоставить','rules' => 'required, max_length[512]','type' => 'string'),
		),
		'deleteEvnForenSubDopDocQuery'=>array(
			array('field' => 'EvnForensicSubDopDocQuery_id', 'label' => 'Идентификатор запроса на участие', 'rules' => 'required', 'type' => 'id', 'default'=>NULL),
		),
		'getEvnForensicSubDopPersQuery'=>array(
			array('field' => 'EvnForensicSub_id', 'label' => 'Идентификатор заявки', 'rules' => '', 'type' => 'id', 'default'=>NULL),
			array('field' => 'EvnForensicSubDopPersQuery_id', 'label' => 'Идентификатор запроса на участие', 'rules' => '', 'type' => 'id', 'default'=>NULL),
		),
		'saveEvnForensicSubDopPersQuery'=>array(
			array('field' => 'EvnForensicSub_id', 'label' => 'Идентификатор заявки', 'rules' => '', 'type' => 'id', 'default'=>NULL),
			array('field' => 'EvnForensicSubDopPersQuery_id', 'label' => 'Идентификатор запроса на участие', 'rules' => '', 'type' => 'id', 'default'=>NULL),
			array('field' => 'EvnForensicSubDopPersQuery_Num', 'label' => 'Номер запроса', 'rules' => 'max_length[128]', 'type' => 'string', 'default'=>NULL),
			array('field' => 'EvnForensicSubDopPersQuery_Date', 'label' => 'Дата заявки', 'rules' => '', 'type' => 'date'),
			array('field' => 'EvnForensicSubDopPersQuery_Iniciator','label' => 'Кому (ФИО)','rules' => 'required, max_length[255]','type' => 'string'),
			array('field' => 'EvnForensicSubDopPersQuery_IniciatorJob','label' => 'Кому (Должность, место работы)','rules' => 'required, max_length[255]','type' => 'string'),
			array('field' => 'EvnForensicSubDopPersQuery_ExpertFIO','label' => 'Запрашиваемый эксперт','rules' => 'required, max_length[255]','type' => 'string'),
			array('field' => 'EvnForensicSubDopPersQuery_ExpertRole','label' => 'В качестве кого вызывается эксперт','rules' => 'required, max_length[255]','type' => 'string'),
			array('field' => 'EvnForensicSubDopPersQuery_Person','label' => 'Подэкспертный','rules' => 'required, max_length[255]','type' => 'string'),
			array('field' => 'EvnForensicSubDopPersSubject_Goal','label' => 'Цель запроса','rules' => 'required, max_length[512]','type' => 'string'),
		),
		'deleteEvnForenSubDopPersQuery'=>array(
			array('field' => 'EvnForensicSubDopPersQuery_id', 'label' => 'Идентификатор сопроводительного письма', 'rules' => 'required', 'type' => 'id', 'default'=>NULL),
		),
		'getEvnForensicSubCoverLetter'=>array(
			array('field' => 'EvnForensicSub_id', 'label' => 'Идентификатор заявки', 'rules' => '', 'type' => 'id', 'default'=>NULL),
			array('field' => 'EvnForensicSubCoverLetter_id', 'label' => 'Идентификатор сопроводительного письма', 'rules' => '', 'type' => 'id', 'default'=>NULL),
		),
		'saveEvnForensicSubCoverLetter'=>array(
			array('field' => 'EvnForensicSub_id', 'label' => 'Идентификатор заявки', 'rules' => '', 'type' => 'id', 'default'=>NULL),
			array('field' => 'EvnForensicSubCoverLetter_id', 'label' => 'Идентификатор сопроводительного письма', 'rules' => '', 'type' => 'id', 'default'=>NULL),
			array('field' => 'EvnForensicSubCoverLetter_Num', 'label' => 'Номер запроса', 'rules' => 'max_length[128]', 'type' => 'string', 'default'=>NULL),
			array('field' => 'EvnForensicSubCoverLetter_Date', 'label' => 'Дата заявки', 'rules' => '', 'type' => 'date'),
			array('field' => 'EvnForensicSubCoverLetter_Iniciator','label' => 'Кому (ФИО)','rules' => 'required, max_length[255]','type' => 'string'),
			array('field' => 'EvnForensicSubCoverLetter_IniciatorJob','label' => 'Кому (Должность, место работы)','rules' => 'required, max_length[255]','type' => 'string'),
			array('field' => 'EvnForensicSubCoverLetter_Person','label' => 'Подэкспертный','rules' => 'required, max_length[255]','type' => 'string'),
			array('field' => 'EvnForensicSubCoverLetter_PersonBirthdate', 'label' => 'Дата рождения', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'EvnForensicSubCoverLetter_DocType', 'label' => 'Тип документа', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'EvnForensicSubCoverLetter_DocNum','label' => 'Номер документа','rules' => 'required, max_length[255]','type' => 'string'),
			array('field' => 'EvnForensicSubCoverLetter_DocDate', 'label' => 'Дата документа', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'EvnForensicSubCoverLetter_Attachment','label' => 'Цель запроса','rules' => 'max_length[512]','type' => 'string'),
		),
		'deleteEvnForenSubCoverLetter'=>array(
			array('field' => 'EvnForensicSubCoverLetter_id', 'label' => 'Идентификатор сопроводительного письма', 'rules' => 'required', 'type' => 'id', 'default'=>NULL),
		),
		'loadForensicValuationInjury' => array(),
		'loadForensicDefinitionSexualOffenses' => array(),
		'loadForensicSubDefinition' => array(),
		'saveForensicSubReportWorking' => array(
			array( 'field' => 'ForensicSubReportWorking_id', 'label' => 'Идентификатор отчета деятельности бюро', 'rules' => '', 'type' => 'id', 'default' => null ),
			array( 'field' => 'EvnForensicSub_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id' ),
			array( 'field' => 'ForensicValuationInjury_id', 'label' => 'Оценка вреда здоровью', 'rules' => '', 'type' => 'id', 'default' => null ),
			array( 'field' => 'ForensicDefinitionSexualOffenses_id', 'label' => 'Определение половых состояний', 'rules' => '', 'type' => 'id', 'default' => null ),
			array( 'field' => 'ForensicSubDefinition_id', 'label' => 'Определение', 'rules' => '', 'type' => 'id', 'default' => null ),
		),
		// Для этого запроса обязательно или идентификатор заявки или идентификатор отчета
		'getEvnForensicSubReportWorking' => array(
			array( 'field' => 'EvnForensicSub_id', 'label' => 'Идентификатор заявки', 'rules' => '', 'type' => 'id', 'default' => null ),
			array( 'field' => 'ForensicSubReportWorking_id', 'label' => 'Идентификатор отчета деятельности бюро', 'rules' => '', 'type' => 'id', 'default' => null ),
		),
		'printEvnXml' => array(
			array('field' => 'EvnForensic_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
			array( 'field' => 'EvnXml_id', 'label' => 'Идентификатор документа', 'rules' => 'required', 'type' => 'id' ),			
		),
		'setEvnForensicSubResult' => array(
			array( 'field' => 'EvnForensicSub_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
			array( 'field' => 'EvnForensicSub_Result', 'label' => 'Идентификатор заявки', 'rules' => '', 'type' => 'string', 'default'=>''),
		),
		'setEvnForensicSubReceiver' => array(
			array( 'field' => 'EvnForensicSub_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
			array( 'field' => 'EvnForensicSub_Receiver', 'label' => 'Получатель результата', 'rules' => '', 'type' => 'string', 'default'=>''),
		),
		'setEvnForensicSubExpertiseDT' => array(
			array( 'field' => 'EvnForensicSub_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
			array( 'field' => 'EvnForensicSub_ExpertiseDT', 'label' => 'Дата проведения экспертизы', 'rules' => '', 'type' => 'date', 'default'=>null),
		),
		'createEmpty' => array(
			array( 'field' => 'EvnXml_id','label' => 'Идентификатор документа', /* обязателен при перевыборе шаблона документа */ 'rules' => '', 'type' => 'id' ),
			array( 'field' => 'EvnForensic_id', 'label' => 'Идентификатор события', 'rules' => '', 'type' => 'id' ),
			array( 'field' => 'XmlTemplate_id', 'label' => 'Идентификатор шаблона', 'rules' => '', 'type' => 'id' ), 
			array( 'field' => 'XmlType_id', 'label' => 'Тип документа', 'rules' => 'required', 'type' => 'id'),
			array( 'field' => 'EvnClass_id', 'label' => 'Категория документа', 'rules' => '', 'type' => 'id' ),
			array( 'field' => 'MedStaffFact_id', 'label' => 'Рабочее место врача', 'rules' => '', 'type' => 'id' ),
		),
		'getForensicXmlVersionList' => array(
			array( 'field' => 'EvnForensic_id', 'label' => 'Идентификатор события', 'rules' => 'required', 'type' => 'id' ),
		),
	);
	
	/**
	 * default desc
	 */
	
	public function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('BSME_model', 'dbmodel');
		$this->load->model('EvnForensicSub_model', 'efsmodel');
		$this->load->model('EvnForensicGenetic_model', 'efgmodel');
	}
	/**
	 * Функция сохранения заявки для службы судебно-биологической экспертизы потерпевших, обвиняемых и других лиц
	 * @return boolean
	 */
	public function saveForenPersRequest() {
		$data = $this->ProcessInputData('saveForenPersRequest',true);
		if (!$data) return false;
		
		if (!empty($data['EvnForensicSub_AccidentDate'])) {
			$data['EvnForensicSub_AccidentTime'] = (!empty($data['EvnForensicSub_AccidentTime']))?$data['EvnForensicSub_AccidentTime']:'00:00';
			$data['EvnForensicSub_AccidentDT'] = $data['EvnForensicSub_AccidentDate'].' '.$data['EvnForensicSub_AccidentTime'].':00.000';
		} else {
			$data['EvnForensicSub_AccidentDT'] = '';
		}
		
		if (!empty($data['EvnForensicSub_ExpertiseComeDate'])) {
			$data['EvnForensicSub_ExpertiseComeTime'] = (!empty($data['EvnForensicSub_ExpertiseComeTime']))?$data['EvnForensicSub_ExpertiseComeTime']:'00:00';
			$data['EvnForensicSub_ExpertiseComeDate'] = $data['EvnForensicSub_ExpertiseComeDate'].' '.$data['EvnForensicSub_ExpertiseComeTime'].':00.000';
		} else {
			$data['EvnForensicSub_ExpertiseComeDate'] = '';
		}
		
		if (!empty($data['EvnForensicSub_ResDate'])) {
			$data['EvnForensicSub_ResTime'] = (!empty($data['EvnForensicSub_ResTime']))?$data['EvnForensicSub_ResTime']:'00:00';
			$data['EvnForensicSub_ResDate'] = $data['EvnForensicSub_ResDate'].' '.$data['EvnForensicSub_ResTime'].':00.000';
		} else {
			$data['EvnForensicSub_ResDate'] = '';
		}
		
		if (!empty($data['session']['CurMedService_id'])) {
			$data['MedService_id'] = $data['session']['CurMedService_id'];
		}
		
		
		$response = $this->efsmodel->saveForenPersRequest($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}
	/**
	 * Функция сохранения заявки для службы судебно-химической экспертизы
	 * @return boolean
	 */
	public function saveForenChemOwnRequest() {
		$data = $this->ProcessInputData('saveForenChemOwnRequest',true);
		if (!$data) return false;

		$BioSample = json_decode($data['BioSample'], true);
		if (($BioSample === NULL)||(!is_array($BioSample))) {
			$this->ProcessModelSave(array(array('success'=>false,'Error_Msg'=>'Список биологических образцов не является JSON-массивом')), true)->ReturnData();;
			return true;
		}
		$data['BioSample'] = $BioSample;
		
		$response = $this->dbmodel->saveForenChemOwnRequest($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}
	/**
	 * Функция сохранения заявки внутри службы судебно-биологической экспертизы с молекулярно-генетической лабораторией
	 * @return boolean
	 */
	public function saveEvnForensicGeneticRequest() {
		$data = $this->ProcessInputData('saveEvnForensicGeneticRequest',true);
		if (!$data) return false;
		
		$Person = json_decode($data['Person'], true);
		if (($Person === NULL)||(!is_array($Person))) {
			$this->ProcessModelSave(array(array('success'=>false,'Error_Msg'=>'Список потерпевших/обвиняемых не является JSON-массивом')), true)->ReturnData();;
			return true;
		}
		$data['Person'] = $Person;
		$Evidence = json_decode($data['Evidence'], true);
		if (($Evidence === NULL)||(!is_array($Evidence))) {
			$this->ProcessModelSave(array(array('success'=>false,'Error_Msg'=>'Список вещественных доказательств не является JSON-массивом')), true)->ReturnData();;
			return true;

		}
		$data['Evidence'] = $Evidence;
		$BioSample = json_decode($data['BioSample'], true);
		if (($BioSample === NULL)||(!is_array($BioSample))) {
			$this->ProcessModelSave(array(array('success'=>false,'Error_Msg'=>'Список биологических образцов не является JSON-массивом')), true)->ReturnData();;
			return true;
		}
		$data['BioSample'] = $BioSample;
		$PersonBioSample = json_decode($data['PersonBioSample'], true);
		if (($PersonBioSample === NULL)||(!is_array($PersonBioSample))) {
			$this->ProcessModelSave(array(array('success'=>false,'Error_Msg'=>'Список исследуемых лиц не является JSON-массивом')), true)->ReturnData();;
			return true;
		}
		$data['PersonBioSample'] = $PersonBioSample;
		$BioSampleForMolGenRes = json_decode($data['BioSampleForMolGenRes'], true);
		if (($BioSampleForMolGenRes === NULL)||(!is_array($BioSampleForMolGenRes))) {
			$this->ProcessModelSave(array(array('success'=>false,'Error_Msg'=>'Список биологических образцов не является JSON-массивом')), true)->ReturnData();;
			return true;
		}
		$data['BioSampleForMolGenRes'] = $BioSampleForMolGenRes;
		$Sample = json_decode($data['Sample'], true);
		if (($Sample === NULL)||(!is_array($Sample))) {
			$this->ProcessModelSave(array(array('success'=>false,'Error_Msg'=>'Список образцов не является JSON-массивом')), true)->ReturnData();;
			return true;
		}
		$data['Sample'] = $Sample;
		
		if (!empty($data['EvnForensicGeneticSampleLive_TakeDT']) && !empty($data['EvnForensicGeneticSampleLive_TakeTime'])) {
			$data['EvnForensicGeneticSampleLive_TakeDate'] = $data['EvnForensicGeneticSampleLive_TakeDT'].' '.$data['EvnForensicGeneticSampleLive_TakeTime'].':00.000';
		} else {
			$data['EvnForensicGeneticSampleLive_TakeDate'] = '';
		}
		
		if (!empty($data['EvnForensicGeneticSmeSwab_DelivDT']) && !empty($data['EvnForensicGeneticSmeSwab_DelivTime'])) {
			$data['EvnForensicGeneticSmeSwab_DelivDate'] = $data['EvnForensicGeneticSmeSwab_DelivDT'].' '.$data['EvnForensicGeneticSmeSwab_DelivTime'].':00.000';
		} else {
			$data['EvnForensicGeneticSmeSwab_DelivDate'] = '';
		}
		
		$response = $this->efgmodel->saveEvnForensicGeneticRequest($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}
	
	/**
	 * Получение следующего за последним номера заявки
	 * @return booleanъ
	 */
	public function getNextRequestNumber() {
		$data = $this->ProcessInputData('getNextRequestNumber');
		if (!$data) return false;
		$response = $this->dbmodel->getNextRequestNumber($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}
	
	/**
	 * Получение следующего за номера заявки службы судмедэкспертизы потерпевишх обвиняемых
	 * @return booleanъ
	 */
	public function getNextForenPersRequestNumber() {
		$data = $this->ProcessInputData('getNextForenPersRequestNumber');
		if (!$data) return false;
		$response = $this->efsmodel->getNextForenPersRequestNumber($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}
	/**
	 * Функция получения списка заявок для определённого типа журнала/службы
	 * @return boolean
	 */
	public function getJournalRequestList() {
		$data = $this->ProcessInputData('getJournalRequestList');
		if (!$data) return false;
		
		$search_params = json_decode($data['search'], true);
		if ($search_params != NULL) {
			$data = array_merge($data, $search_params);
		}
		
		switch ($data['JournalType']) {
			
			//
			// Судебно-биологическая экспертиза
			//
			
			// Список заявок службы судебно-биологической экспертизы с молекулярно-генетической лабораторией (СБЭ)
			case 'EvnForensicGenetic':
				$response =  $this->efgmodel->getEvnForensicGeneticList($data);
				break;
			//Журнал регистрации вещественных доказательств и документов к ним в лаборатории в службе СБЭ
			case 'EvnForensicGeneticEvid':
				$response =  $this->efgmodel->getEvnForensicGeneticEvidList($data);	
				break;
			//Журнал регистрации биологических образцов, изъятых у живых лиц в лаборатории
			case 'EvnForensicGeneticSampleLive':
				$response =  $this->efgmodel->getEvnForensicGeneticSampleLiveList($data);
				break;
			//Журнал регистрации биологических образцов, изъятых у живых лиц в лаборатории для молекулярно-генетического исследования
			case 'EvnForensicGeneticGenLive':
				$response =  $this->efgmodel->getEvnForensicGeneticGenLiveList($data);
				break;
			//Журнал регистрации исследований мазков и тампонов в лаборатории
			case 'EvnForensicGeneticSmeSwab':
				$response =  $this->efgmodel->getEvnForensicGeneticSmeSwabList($data);
				break;
			//Журнал регистрации трупной крови в лаборатории
			case 'EvnForensicGeneticCadBlood':
				$response =  $this->efgmodel->getEvnForensicGeneticCadBloodList($data);
				break;
			
			//
			// Экспертиза потерпевших, обвиняемых и других лиц 
			//
						
			//Список заявок службы судебно-медицинской экспертизы потерпевших, обвиняемых и других лиц 
			case 'EvnForensicSub':
				
				//Поскольку неясно дальнейшее развитие проекта, чтобы не ломать общую логику
				//будем добавлять по необходимости параметры для загрузки списка заявок здесь
				$extra_params = array(
					array('field' => 'ForensicSubType_id', 'label' => 'Идентификатор типа заявки', 'rules' => '', 'type' => 'id'),
				);
				
				$err = getInputParams($data, $extra_params);
				if ( strlen($err) > 0 ) {
					echo json_return_errors($err);
					return false;
				}
				
				
				$response =  $this->efsmodel->getEvnForensicSubList($data);
				break;
			/*	
			//Журнал судебно-медицинской экспертизы (исследования) подэкспертному
			case 'EvnForensicSubDir':
				$response =  $this->efsmodel->getEvnForensicSubDirList($data);
				break;
			//Журнал судебно-медицинской экспертизы (исследования) медицинских документов
			case 'EvnForensicSubDoc':
				$response =  $this->efsmodel->getEvnForensicSubDocList($data);
				break;
			//Журнал судебно-медицинской экспертизы медицинских документов с осмотром подэкспертного
			case 'EvnForensicSubInsp':
				$response =  $this->efsmodel->getEvnForensicSubInspList($data);
				break;
			//Журнал судебно-медицинской экспертизы  по личному заявлению потерпевшего
			case 'EvnForensicSubOwn':
				$response =  $this->efsmodel->getEvnForensicSubOwnList($data);
				break;
			*/

			//
			// Экспертиза потерпевших, обвиняемых и других лиц 
			//
						
			//Список заявок службы судебно-медицинской экспертизы трупов с судебно-гистологическим отделением
			case 'EvnForensicCorpHist':
				$response =  $this->dbmodel->getEvnForensicCorpHistList($data);
				break;
			
			//
			// Судебно-химическая экспертиза
			//
			
			case 'EvnForensicChem':
				$response =  $this->dbmodel->getEvnForensicChemList($data);
				break;

			// Судебно-гистологическое отделение
			case 'EvnForensicHist':
				$response =  $this->dbmodel->getEvnForensicHistList($data);
				break;
			
			
			//
			// Медико-криминалистическая экспертиза
			//
			
			// Список всех заявок медико-криминалистического отделения
			case 'EvnForensicCrime':
				$response =  $this->dbmodel->getEvnForensicCrimeList($data);
				break;
			// Список всех заявок медико-криминалистического отделения в которых присутствует запись в журнале
			// регистрации вещественных доказательств и документов к ним
			case 'EvnForensicCrimeEvid':
				$response =  $this->dbmodel->getEvnForensicCrimeEvidList($data);
				break;
			// Список всех заявок медико-криминалистического отделения в которых присутствует запись в журнале
			// регистрации фоторабот
			case 'EvnForensicCrimePhot':
				$response =  $this->dbmodel->getEvnForensicCrimePhotList($data);
				break;
			// Список всех заявок медико-криминалистического отделения в которых присутствует запись в журнале
			// регистрации разрушения почки на планктон
			case 'EvnForensicCrimeDesPlan':
				$response =  $this->dbmodel->getEvnForensicCrimeDesPlanList($data);
				break;


			//
			// Комиссионные и комплексные экспертизы
			//

			// Список всех заявок комиссионных и комплексных экспертиз
			case 'EvnForensicComplex':
				$response =  $this->dbmodel->getEvnForensicComplexList($data);
				break;
			// Список всех заявок медико-криминалистического отделения в которых присутствует запись в журнале
			// регистрации вещественных доказательств и документов к ним
			case 'EvnForensicComplexResearch':
				$response =  $this->dbmodel->getEvnForensicComplexResearchList($data);
				break;

			default:
				$response = array(array('success'=>false,'Error_Msg'=>'Неверный тип журнала'));
				break;
		}
		
		
		$this->ProcessModelMultiList($response, true, true , (!empty($response['Error_Msg']))?$response['Error_Msg']:'При запросе возникла ошибка' )->ReturnData();
		return true;
	}
	/**
	 * Функция получения журнала службы судебно-медицинской экспертизы потерпевших обвиняемых и других лиц
	 * @return boolean
	 */
	public function getEvnForensicSubArchive() {
		$data = $this->ProcessInputData('getEvnForensicSubArchive');
		if (!$data) return false;
		$response = $this->efsmodel->getEvnForensicSubArchive($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}
	/**
	 * Функция сохранения заявки внутри службы медико-криминалистической экспертизы
	 * @return boolean
	 */
	public function saveEvnForenCrimeOwnRequest() {
		$data = $this->ProcessInputData('saveEvnForenCrimeOwnRequest',true);
		if (!$data) return false;
		
		$Person = json_decode($data['Person'], true);
		if (($Person === NULL)||(!is_array($Person))) {
			$this->ProcessModelSave(array(array('success'=>false,'Error_Msg'=>'Список потерпевших/обвиняемых не является JSON-массивом')), true)->ReturnData();;
			return true;
		}
		$data['Person'] = $Person;
		$Evidence = json_decode($data['Evidence'], true);
		if (($Evidence === NULL)||(!is_array($Evidence))) {
			$this->ProcessModelSave(array(array('success'=>false,'Error_Msg'=>'Список вещественных доказательств не является JSON-массивом')), true)->ReturnData();;
			return true;
		}
		$data['Evidence'] = $Evidence;
		
		$response = $this->dbmodel->saveEvnForenCrimeOwnRequest($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 * Сохранение зявки внутри службы комиссионных и комплексных экспертиз
	 * @return bool
	 */
	public function saveForenComplexOwnRequest() {
		$data = $this->ProcessInputData('saveForenComplexOwnRequest',true);
		if (!$data) return false;

		$Evidence = json_decode($data['Evidence'], true);
		if (($Evidence === NULL)||(!is_array($Evidence))) {
			$this->ProcessModelSave(array(array('success'=>false,'Error_Msg'=>'Список биологических образцов не является JSON-массивом')), true)->ReturnData();;
			return true;
		}
		$data['Evidence'] = $Evidence;

		$response = $this->dbmodel->saveForenComplexOwnRequest($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}
	
	/**
	 * Функция сохранения направления на исследования трупной крови в службе судебно-биологического 
	 * отделения для службы судебно-медицинской экспертизы трупов с судебно-гистологическим отделением
	 * @return boolean
	 */
	public function saveForenCorpBloodDirection() {
		$data = $this->ProcessInputData('saveForenCorpBloodDirection',true);
		if (!$data) return false;
		$response = $this->dbmodel->saveForenCorpBloodDirection($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}
	/**
	 * Функция сохранения направления на исследования вещественных доказательств в службе медико-криминалистического
	 * отделения для службы судебно-медицинской экспертизы трупов с судебно-гистологическим отделением
	 * @return boolean
	 */
	public function saveForenEvidDirection() {
		$data = $this->ProcessInputData('saveForenEvidDirection',true);
		if (!$data) return false;
		$response = $this->dbmodel->saveForenEvidDirection($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}
	/**
	 * Функция сохранения направления на исследования разрушения почки на планктон в службе медико-криминалистического
	 * отделения для службы судебно-медицинской экспертизы трупов с судебно-гистологическим отделением
	 * @return boolean
	 */
	public function saveForenKidneyPlanktDirection() {
		$data = $this->ProcessInputData('saveForenKidneyPlanktDirection',true);
		if (!$data) return false;
		$response = $this->dbmodel->saveForenKidneyPlanktDirection($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}
	/**
	 * Функция сохранения направления на наличие диатомового планктона
	 * @return boolean
	 */
	public function saveForenDiamPlanktDirection(){
		$data = $this->ProcessInputData('saveForenDiamPlanktDirection',true);
		if (!$data) return false;
		$response = $this->dbmodel->saveForenDiamPlanktDirection($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}
	/**
	 * Функция сохранения направления на биохимическое исследование
	 * @return boolean
	 */
	public function saveForenBioChemDirection() {
		$data = $this->ProcessInputData('saveForenBioChemDirection',true);
		if (!$data) return false;
		$response = $this->dbmodel->saveForenBioChemDirection($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}
	/**
	 * Функция сохранения направления на судебно-химическое исследование
	 * @return boolean
	 */
	public function saveForenChemDirection() {
		$data = $this->ProcessInputData('saveForenChemDirection',true);
		if (!$data) return false;
		$Jar = json_decode($data['Jar'], true);
		if (($Jar === NULL)||(!is_array($Jar))) {
			$this->ProcessModelSave(array(array('success'=>false,'Error_Msg'=>'Список банок не является JSON-массивом')), true)->ReturnData();;
			return true;
		}
		$data['Jar'] = $Jar;
		
		$Flak = json_decode($data['Flak'], true);
		if (($Flak === NULL)||(!is_array($Flak))) {
			$this->ProcessModelSave(array(array('success'=>false,'Error_Msg'=>'Список флаконов не является JSON-массивом')), true)->ReturnData();;
			return true;
		}
		$data['Flak'] = $Flak;
		
		$response = $this->dbmodel->saveForenChemDirection($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}
	/**
	 * Функция сохранения направления на исследование образцов крови в ИФА на антитела к ВИЧ
	 * @return boolean
	 */
	public function saveForenBludSampleDirection(){
		$data = $this->ProcessInputData('saveForenBludSampleDirection',true);
		if (!$data) return false;
		$response = $this->dbmodel->saveForenBludSampleDirection($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}
	/**
	 * Функция получения отображения для печати направления на исследование образцов крови в ИФА на антитела к ВИЧы
	 * @return boolean
	 */
	public function printBludSampleResearchDirection(){
		$data = $this->ProcessInputData( 'printBludSampleResearchDirection', true );
		if ( $data === false ) {
			return false;
		}
		$response = $this->dbmodel->printBludSampleResearchDirection( $data );
		if ( !is_array( $response ) || !sizeof( $response ) ) {
			return (array(array('success'=>false,'Error_Msg'=>'Не удалось получить данные направления при печати.')));
		}
		$this->load->library('parser');
		return $this->parser->parse( 'print_bludsampleresearchdirection', $response[0] );
	}
	/**
	 * Функция сохранения направления на вирусологическое исследование
	 * @return boolean
	 */
	public function saveForenVirusologicDirection(){
		$data = $this->ProcessInputData('saveForenVirusologicDirection',true);
		if (!$data) return false;
		$response = $this->dbmodel->saveForenVirusologicDirection($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}
	/**
	 * Функция получения отображения для печати направления на вирусологическое исследование
	 * @return boolean
	 */
	public function printVirusologicResearchDirection(){
		$data = $this->ProcessInputData( 'printVirusologicResearchDirection', true );
		if ( $data === false ) {
			return false;
		}
		$response = $this->dbmodel->printBludSampleResearchDirection( $data );
		if ( !is_array( $response ) || !sizeof( $response ) ) {
			return (array(array('success'=>false,'Error_Msg'=>'Не удалось получить данные направления при печати.')));
		}
		$this->load->library('parser');
		return $this->parser->parse( 'print_virusologicdirection', $response[0] );
	}
	/**
	 * Функция получения заявки службы судебно-биологической экспертизы с молекулярно-генетической лабораторией для просмотра
	 * @return boolean
	 */
	public function getEvnForensicGeneticRequest() {
		$data = $this->ProcessInputData('getEvnForensicGeneticRequest',true);
		if (!$data) return false;
		$response = $this->efgmodel->getEvnForensicGeneticRequest($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}
	/**
	 * Функция получения заявки службы судебно-медицинской экспертизы потерпевших, обвиняемых и других лиц
	 * @return boolean
	 */
	public function getForenPersRequest() {
		$data = $this->ProcessInputData('getForenPersRequest',true);
		if (!$data) return false;
		$response = $this->efsmodel->getForenPersRequest($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}
	/**
	 * Функция получения заявки службы медико-криминалистической экспертизы
	 * @return boolean
	 */
	public function getEvnForenCrimeRequest() {
		$data = $this->ProcessInputData('getEvnForenCrimeRequest',true);
		if (!$data) return false;
		$response = $this->dbmodel->getEvnForenCrimeRequest($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}
	/**
	 * Функция получения заявки службы комплексных экспертиз
	 * @return boolean
	 */
	public function getForenComplexRequest() {
		$data = $this->ProcessInputData('getEvnForenComplexRequest',true);
		if (!$data) return false;
		$response = $this->dbmodel->getForenComplexRequest($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}
	
	/**
	 * Функция сохранения заявки для службы судебно-медицинской экспертизы трупов с судебно-гистологическим отделением
	 * @return boolean
	 */
	public function saveForenCorpOwnRequest() {
		$data = $this->ProcessInputData('saveForenCorpOwnRequest',true);
		if (!$data) return false;
		
		$Evidence = json_decode($data['Evidence'], true);
		if (($Evidence === NULL)||(!is_array($Evidence))) {
			$this->ProcessModelSave(array(array('success'=>false,'Error_Msg'=>'Список вещественных доказательств не является JSON-массивом')), true)->ReturnData();;
			return true;
		}
		$data['Evidence'] = $Evidence;
		
		$Evidence = json_decode($data['ValueStuff'], true);
		if (($Evidence === NULL)||(!is_array($Evidence))) {
			$this->ProcessModelSave(array(array('success'=>false,'Error_Msg'=>'Список ценных вещей/документов не является JSON-массивом')), true)->ReturnData();;
			return true;
		}
		$data['ValueStuff'] = $Evidence;
		
		$response = $this->dbmodel->saveForenCorpOwnRequest($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 * Возвращает список типов экспертизы
	 * Для комбобокса
	 *
	 * @return output JSON
	 */
	public function loadEvnForensicTypeList(){
		$data = $this->ProcessInputData( 'loadEvnForensicTypeList', true );
		if ( !$data ) {
			return false;
		}
		$response = $this->dbmodel->loadEvnForensicTypeList( $data );
		$this->ProcessModelList( $response, true, true )->ReturnData();
	}

	/**
	 * @return Шаблон печати поручения
	 */
	public function printEvnDirectionForensic(){
		$data = $this->ProcessInputData( 'printEvnDirectionForensic', true );
		if ( $data === false ) {
			return false;
		}
		
		$response = $this->dbmodel->printEvnDirectionForensic( $data );
		$response['_HeadName'] = $_SESSION['surname'].' '.mb_substr( $_SESSION['firname'],0,1).'.'.(isset($_SESSION['secname'])?' '.mb_substr( $_SESSION['secname'],0,1).'.':'');
		$settings = unserialize( $_SESSION['settings'] );
		$response['_ARMName'] = isset($data['armName'])?$data['armName']:$settings['defaultARM']['ARMName'];

		$this->load->library('parser');
		$this->parser->set_delimiters();
		return $this->parser->parse( 'print_evndirectionforensic', $response );
	}

	/**
	 * Загрузка формы
	 * @return bool
	 */
	public function loadForenBioOwnRequestForm(){
		$data = $this->ProcessInputData( 'loadForenBioOwnRequestForm', true );
		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->loadForenBioOwnRequestForm( $data );
		$this->ProcessModelList( $response, true, true )->ReturnData();
	}

	/**
	 * Отаправка экспертизы на проверку
	 */
	public function checkEvnForensic() {
		$data = $this->ProcessInputData( 'checkEvnForensic', true );
		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->changeEvnForensicStatus( $data, 'Check' );
		$this->ProcessModelSave( $response, true )->ReturnData();
	}

	/**
	 * Одобрение заявки
	 */
	public function approveEvnForensic() {
		$data = $this->ProcessInputData( 'approveEvnForensic', true );
		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->changeEvnForensicStatus( $data, 'Approved' );
		$this->ProcessModelSave( $response, true )->ReturnData();
	}

	/**
	 * Возвращение заявки на доработку
	 */
	public function revisionEvnForensic() {
		$data = $this->ProcessInputData( 'revisionEvnForensic', true );
		if ( $data === false ) {
			return false;
		}
		
		$response = $this->dbmodel->revisionEvnForensic( $data );
		$this->ProcessModelSave( $response, true )->ReturnData();
	}

	/**
	 * Сохранение экспертизы
	 * @return bool
	 */
	public function saveEvnForensicGeneticExpertiseProtocol() {
		$data = $this->ProcessInputData( 'saveEvnForensicGeneticExpertiseProtocol', false );
		if ( $data === false ) {
			return false;
		}
		$data['pmUser_id'] = $_SESSION['pmuser_id'];
		$data['Lpu_id'] = $_SESSION['lpu_id'];

		$BioSample = json_decode($data['BioSample'], true);
		if (($BioSample === NULL)||(!is_array($BioSample))) {
			$this->ProcessModelSave(array(array('success'=>false,'Error_Msg'=>'Список биологических образцов не является JSON-массивом')), true)->ReturnData();;
			return true;
		}
		$data['BioSample'] = $BioSample;

		$response = $this->efgmodel->saveEvnForensicGeneticExpertiseProtocol($data);
		$this->ProcessModelSave( $response, true )->ReturnData();
	}

	/**
	 * Сохранение экспертизы
	 * @return bool
	 */
	public function saveForenPersExpertiseProtocol() {
		$data = $this->ProcessInputData( 'saveForenPersExpertiseProtocol', true );
		if ( $data === false ) {
			return false;
		}
		$data['pmUser_id'] = $_SESSION['pmuser_id'];
		$data['Lpu_id'] = $_SESSION['lpu_id'];

		$response = $this->dbmodel->saveForenPersExpertiseProtocol($data);
		
		if (!$this->dbmodel->isSuccessful($response)) {
			$this->ProcessModelSave( $response, true )->ReturnData();
			return;
		}

		$this->load->library('swXmlTemplate');
		$instance = swXmlTemplate::getEvnXmlModelInstance();
		$save_xml_response = $instance->updateSectionContent(array(
			'EvnXml_id' => $data['EvnXml_id'],
			'XmlData' => $data['XmlData'],
			'isHTML'=>0,
			'session' => $data['session'],
			'pmUser_id' => $data['pmUser_id']
		));
		
		if (!empty($response[0]) && !empty($save_xml_response[0])) {
			$response[0] = array_merge($response[0], $save_xml_response[0]);
		}
		
		$this->ProcessModelSave( $response, true )->ReturnData();
	}

	/**
	 * Получение номера акта заключения
	 */
	public function getActVersionForensicNum() {
		$data = $this->ProcessInputData( 'getActVersionForensicNum', true );
		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->getActVersionForensicNum($data);
		$this->ProcessModelSave( $response, true )->ReturnData();
	}

	/**
	 * Сохранение направления в медико-криминалистическое отделение
	 * @return boolean
	 */
	public function saveForenMedCrimDirection() {
		$data = $this->ProcessInputData( 'saveForenMedCrimDirection', true );
		if ( $data === false ) {return false;}
		if (!empty($data['EvnForensicCrimeExCorp_TakeDT']) && !empty($data['EvnForensicCrimeExCorp_TakeTime'])) {
			$data['EvnForensicCrimeExCorp_TakeDate'] = $data['EvnForensicCrimeExCorp_TakeDT'].' '.$data['EvnForensicCrimeExCorp_TakeTime'].':00.000';
		} else {
			$data['EvnForensicGeneticSampleLive_TakeDate'] = '';
		}
		$Evidence = json_decode($data['Evidence'], true);
		if (($Evidence === NULL)||(!is_array($Evidence))) {
			$this->ProcessModelSave(array(array('success'=>false,'Error_Msg'=>'Список вещественных доказательств не является JSON-массивом')), true)->ReturnData();;
			return true;

		}
		$data['Evidence'] = $Evidence;
		$response = $this->dbmodel->saveForenMedCrimDirection($data);
		$this->ProcessModelSave( $response, true )->ReturnData();
		return true;
	}
	
	/**
	 * Функция "выдачи на руки" результатов экспертизы службы судебно-медицинской экспертизы потерпевших обвиняемых и других лиц
	 * @return boolean
	 */
	public function saveEvnForensicResultOut() {
		$data = $this->ProcessInputData( 'saveEvnForensicResultOut', true );
		if ( $data === false ) {return false;}
		$response = $this->dbmodel->saveEvnForensicResultOut($data);
		$this->ProcessModelSave( $response, true )->ReturnData();
		return true;
	}

	/**
	 * Сохранение заявки на дополнительные материалы в службе судебно-медицинской экспертизы потерпевших, обвиняемых и других лиц
	 * @return bool
	 */
	public function saveForenPersDopMatQuery() {
		$data = $this->ProcessInputData( 'saveForenPersDopMatQuery', true );
		if ( $data === false ) {return false;}

		$response = $this->efsmodel->saveForenPersDopMatQuery($data);
		$this->ProcessModelSave( $response, true )->ReturnData();
		return true;
	}
	/**
	 * Функция сохранения результата направления на дополнительные материалы в службу судмедэкспертизы потерпевших/обвиняемых
	 * @return boolean
	 */
	public function saveEvnForenSubDopMatQueryResult() {
		$data = $this->ProcessInputData( 'saveEvnForenSubDopMatQueryResult', true );
		if ( $data === false ) {return false;}
		
		if (!empty($data['EvnForensicSubDopMatQuery_ResultDate']) && !empty($data['EvnForensicSubDopMatQuery_ResultTime'])) {
			$data['EvnForensicSubDopMatQuery_ResultDT'] = $data['EvnForensicSubDopMatQuery_ResultDate'].' '.$data['EvnForensicSubDopMatQuery_ResultTime'].':00.000';
		} else {
			$data['EvnForensicSubDopMatQuery_ResultDT'] = '';
		}
		
		$response = $this->efsmodel->saveEvnForenSubDopMatQueryResult($data);
		$this->ProcessModelSave( $response, true )->ReturnData();
		return true;
	}
	/**
	 * Функция удаления направления на на дополнительные материалы в службу судмедэкспертизы потерпевших/обвиняемых
	 * @return boolean
	 */
	public function deleteEvnForenSubDopMatQuery() {
		$data = $this->ProcessInputData( 'deleteEvnForenSubDopMatQuery', true );
		if ( $data === false ) {return false;}

		$response = $this->efsmodel->deleteEvnForenSubDopMatQuery($data);
		$this->ProcessModelSave( $response, true )->ReturnData();
		return true;
	}
	
	/**
	 * Получение заявки на доп. материалы
	 * @return bool
	 */
	public function getForenPersDopMatQuery() {
		$data = $this->ProcessInputData( 'getForenPersDopMatQuery', true );
		if ( $data === false ) {return false;}

		$response = $this->efsmodel->getForenPersDopMatQuery($data);
		$this->ProcessModelSave( $response )->ReturnData();
		return true;
	}
	/**
	 * Функция получения количества заявок для вкладок армов БСМЕ
	 * @return boolean
	 */
	public function getRequestCount() {
		$data = $this->ProcessInputData( 'getRequestCount', true );
		if ( $data === false ) {return false;}
		
		$search_params = json_decode($data['search'], true);
		if ($search_params != NULL) {
			$data = array_merge($data, $search_params);
		}
		
		switch ($data['JournalType']) {
			case 'EvnForensicSub' :
				$response = $this->efsmodel->getRequestCount($data);
				break;
			default :
				$response = $this->dbmodel->getRequestCount($data);
				break;
		}
		
		$this->ProcessModelSave( $response )->ReturnData();
		return true;
	}
	
	/**
	 * Функция удаления заявки
	 * @return boolean
	 */
	public function deleteEvnForensic() {
		$data = $this->ProcessInputData( 'deleteEvnForensic', true );
		if ( $data === false ) {return false;}

		$response = $this->dbmodel->deleteEvnForensic($data);
		$this->ProcessModelSave( $response )->ReturnData();
		return true;
	}


	/**
	 * Экспорт журналов в DBF
	 * @return bool
	 */
	public function exportJournalRequestToDbf() {
		$data = $this->ProcessInputData( 'exportJournalRequestToDbf', true );
		if ( $data === false ) {return false;}

		$response = array();
		$exportData = array();
		switch ($data['JournalType']) {

			//
			// Экспертиза потерпевших, обвиняемых и других лиц
			//

			//Журнал судебно-медицинской экспертизы (исследования) подэкспертному
			case 'EvnForensicSubDir':
				$exportData =  $this->efsmodel->exportEvnForensicSubDirToDbf($data);
				break;
			//Журнал судебно-медицинской экспертизы медицинских документов с осмотром подэкспертного
			case 'EvnForensicSubInsp':
				$exportData =  $this->efsmodel->exportEvnForensicSubInspToDbf($data);
				break;
			//Журнал судебно-медицинской экспертизы  по личному заявлению потерпевшего
			case 'EvnForensicSubOwn':
				$exportData =  $this->efsmodel->exportEvnForensicSubOwnToDbf($data);
				break;
			//Журнал судебно-медицинской экспертизы (исследования) медицинских документов
			case 'EvnForensicSubDoc':
				$exportData =  $this->efsmodel->exportEvnForensicSubDocToDbf($data);
				break;

			default:
				$exportData = array(array('success'=>false,'Error_Msg'=>'Неверный тип журнала'));
				break;
		}

		if (isset($exportData[0]) && !empty($exportData[0]['Error_Msg'])) {
			$this->ReturnData($exportData[0]);
			return false;
		}
		if (count($exportData) == 0) {
			$this->ReturnData(array('success'=>false,'Error_Msg'=>'Нет данных для экспорта'));
			return false;
		}

		if (!file_exists(EXPORTPATH_BSME)) {
			mkdir(EXPORTPATH_BSME);
		}
		$out_dir = "bsme_" . $data['JournalType'];
		if (!file_exists(EXPORTPATH_BSME . $out_dir)) {
			mkdir(EXPORTPATH_BSME . $out_dir);
		}
		$files = array();

		$export_def = array();

		foreach ( $exportData[0] as $key => $value ) {
			$export_def[] = array($key, "C", "255", "0");
		}

		$fname = 'bsme_'.time();

		$file_name = EXPORTPATH_BSME . $out_dir . "/" . $fname . ".dbf";
		$files[$fname . ".dbf"] = $file_name;
		$h = dbase_create($file_name, $export_def);

		foreach ( $exportData as $row ) {
			array_walk($row, 'ConvertFromUtf8ToCp866');
			dbase_add_record($h, array_values($row));
		}

		dbase_close($h);

		$file_zip_name = EXPORTPATH_BSME . $out_dir . "/".$fname.".zip";
		$zip = new ZipArchive();
		$zip->open($file_zip_name, ZIPARCHIVE::CREATE);

		foreach ( $files as $key => $value ) {
			$zip->AddFile($value, $key);
		}

		$zip->close();

		foreach ( $files as $key => $value ) {
			unlink($value);
		}

		if ( !file_exists($file_zip_name) ) {
			$this->ReturnData(array('success' => false, 'Error_Msg' => 'Ошибка создания архива!'));
			return false;
		}

		$response = array('success'=>true,'Link'=>$file_zip_name);

		$this->ProcessModelSave( $response )->ReturnData();
		return true;
	}
	
	/**
	 * Функция получения данных запроса на получение дополнительных документов службы судебно-медицинской экспертизы потерпевших обвиняемых и других лиц
	 * @return boolean
	 */
	public function getEvnForensicSubDopDocQuery() {
		$data = $this->ProcessInputData('getEvnForensicSubDopDocQuery',true);
		if (!$data) return false;
		
		$response = $this->efsmodel->getEvnForensicSubDopDocQuery($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}
	/**
	 * Функция сохранения запроса на получение дополнительных документов службы судебно-медицинской экспертизы потерпевших обвиняемых и других лиц
	 * @return boolean
	 */
	public function saveEvnForensicSubDopDocQuery() {
		$data = $this->ProcessInputData('saveEvnForensicSubDopDocQuery',true);
		if (!$data) return false;
		
		$response = $this->efsmodel->saveEvnForensicSubDopDocQuery($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}
	/**
	 * Функция удаления запроса на получение дополнительных документов службы судебно-медицинской экспертизы потерпевших обвиняемых и других лиц
	 * @return boolean 
	 */
	public function deleteEvnForenSubDopDocQuery(){
		$data = $this->ProcessInputData('deleteEvnForenSubDopDocQuery',true);
		if (!$data) return false;
		
		$response = $this->efsmodel->deleteEvnForenSubDopDocQuery($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}
	/**
	 * Функция получения данных запроса на участие службы судебно-медицинской экспертизы потерпевших обвиняемых и других лиц
	 * @return boolean
	 */
	public function getEvnForensicSubDopPersQuery() {
		$data = $this->ProcessInputData('getEvnForensicSubDopPersQuery',true);
		if (!$data) return false;
		
		$response = $this->efsmodel->getEvnForensicSubDopPersQuery($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}
	/**
	 * Функция сохранения запроса на участие службы судебно-медицинской экспертизы потерпевших обвиняемых и других лиц
	 * @return boolean
	 */
	public function saveEvnForensicSubDopPersQuery() {
		$data = $this->ProcessInputData('saveEvnForensicSubDopPersQuery',true);
		if (!$data) return false;
		
		$response = $this->efsmodel->saveEvnForensicSubDopPersQuery($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}
	/**
	 * Функция удаления запроса на участие службы судебно-медицинской экспертизы потерпевших обвиняемых и других лиц
	 * @return boolean 
	 */
	public function deleteEvnForenSubDopPersQuery(){
		$data = $this->ProcessInputData('deleteEvnForenSubDopPersQuery',true);
		if (!$data) return false;
		
		$response = $this->efsmodel->deleteEvnForenSubDopPersQuery($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}
	/**
	 * Функция получения данных сопроводительного письма службы судебно-медицинской экспертизы потерпевших обвиняемых и других лиц
	 * @return boolean
	 */
	public function getEvnForensicSubCoverLetter() {
		$data = $this->ProcessInputData('getEvnForensicSubCoverLetter',true);
		if (!$data) return false;
		
		$response = $this->efsmodel->getEvnForensicSubCoverLetter($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}
	/**
	 * Функция сохранения сопроводительного письма службы судебно-медицинской экспертизы потерпевших обвиняемых и других лиц
	 * @return boolean
	 */
	public function saveEvnForensicSubCoverLetter() {
		$data = $this->ProcessInputData('saveEvnForensicSubCoverLetter',true);
		if (!$data) return false;
		
		$response = $this->efsmodel->saveEvnForensicSubCoverLetter($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}
	/**
	 * Функция удаления сопроводительного письма службы судебно-медицинской экспертизы потерпевших обвиняемых и других лиц
	 * @return boolean 
	 */
	public function deleteEvnForenSubCoverLetter(){
		$data = $this->ProcessInputData('deleteEvnForenSubCoverLetter',true);
		if (!$data) return false;
		
		$response = $this->efsmodel->deleteEvnForenSubCoverLetter($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 * Возвращает данные справочника "Оценка вреда здоровью"
	 *
	 * @return output JSON
	 */
	public function loadForensicValuationInjury(){
		$response = $this->efsmodel->loadForensicValuationInjury();
		$this->ProcessModelList( $response )->ReturnData();
	}

	/**
	 * Возвращает данные справочника "Определение половых состояний (преступлений)"
	 *
	 * @return output JSON
	 */
	public function loadForensicDefinitionSexualOffenses(){
		$response = $this->efsmodel->loadForensicDefinitionSexualOffenses();
		$this->ProcessModelList( $response )->ReturnData();
	}

	/**
	 * Возвращает данные справочника "Определение"
	 *
	 * @return output JSON
	 */
	public function loadForensicSubDefinition(){
		$response = $this->efsmodel->loadForensicSubDefinition();
		$this->ProcessModelList( $response )->ReturnData();
	}

	/**
	 * Сохранение отчета "Деятельность бюро"
	 *
	 * @return boolean
	 */
	public function saveForensicSubReportWorking() {
		if ( !( $data = $this->ProcessInputData( 'saveForensicSubReportWorking', true ) ) ) {
			return false;
		}

		$response = $this->efsmodel->saveForensicSubReportWorking( $data );
		$this->ProcessModelSave( $response, true )->ReturnData();
	}

	/**
	 *
	 * @return boolean
	 */
	public function getEvnForensicSubReportWorking() {
		if ( !( $data = $this->ProcessInputData( 'getEvnForensicSubReportWorking', true ) ) ) {
			return false;
		}

		$response = $this->efsmodel->getEvnForensicSubReportWorking( $data );
		$this->ProcessModelList( $response, true, true )->ReturnData();
	}

	/**
	 * Функция заполнения результата экспертизы службы потерпевших, обвиняемых и других лиц
	 * @return boolean
	 */
	public function setEvnForensicSubResult()  {
		$data = $this->ProcessInputData('setEvnForensicSubResult',true);
		if (!$data) return false;
		
		$response = $this->efsmodel->setEvnForensicSubResult($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}
	/**
	 * Функция сохранения информации о получателе результата экспертизы экспертизы службы потерпевших, обвиняемых и других лиц
	 * @return boolean
	 */
	public function setEvnForensicSubReceiver()  {
		$data = $this->ProcessInputData('setEvnForensicSubReceiver',true);
		if (!$data) return false;
		
		$response = $this->efsmodel->setEvnForensicSubReceiver($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}
	/**
	 * Функция сохранения даты проведеня экспертизы экспертизы службы потерпевших, обвиняемых и других лиц
	 * @return boolean
	 */
	public function setEvnForensicSubExpertiseDT()  {
		$data = $this->ProcessInputData('setEvnForensicSubExpertiseDT',true);
		if (!$data) return false;
		$response = $this->efsmodel->setEvnForensicSubExpertiseDT($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}
	
	/**
	 * Функция печати заключения с постановкой статуса "Завершен" 
	 * @return boolean
	 */
	public function printEvnXml() {
		
		$data = $this->ProcessInputData('printEvnXml',true);
		if (!$data) return false;
		
		$response = $this->dbmodel->changeEvnForensicStatus($data,'Done');
		if (!$this->dbmodel->isSuccessful($response)) {
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		}

		try {
			$this->load->library('swXmlTemplate');
			$instance = swXmlTemplate::getEvnXmlModelInstance();
			$xml_data = $instance->doLoadPrintData($data);
			return swEvnXml::doPrint(
				$xml_data[0],
				$data['session']['region']['nick'],
				false,//Флаг printHtml
				false //(!empty($data['useWkhtmltopdf']))
			);
		} catch (Exception $e) {
			echo $e->getMessage();
			return false;
		}
	}
	
	/**
	 * Функция сохранения пустого документа в БСМЕ
	 * @return boolean
	 */
	public function createEmpty() {
		$data = $this->ProcessInputData('createEmpty',true);
		
		if (!$data) return false;
		
		$data['Server_id'] = (!empty($data['session']['server_id']))?$data['session']['server_id']:null;
		
		$response = $this->dbmodel->createEmpty($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}
	/**
	 * Получение списка версий документа (заключения) для заявки
	 * @return boolean
	 */
	public function getForensicXmlVersionList() {
		$data = $this->ProcessInputData('getForensicXmlVersionList',true);
		if (!$data) return false;
		$response = $this->dbmodel->getForensicXmlVersionList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	
}

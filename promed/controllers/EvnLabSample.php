<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
* EvnLabSample - контроллер для работы с Пробой на лабораторное исследование
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* @package      common
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       gabdushev
* @version      март 2012

 * @property EvnLabSample_model $dbmodel
*/
class EvnLabSample extends swController {
	// Костыль для устранения ошибки https://redmine.swan.perm.ru/issues/47755
	// Используются в Lis_model.php, определяются только в контроллере Lis.php
	public $server = array(); // редактируются через форму "Общие настройки", умолчания прописаны в Options_model.php
	public $debug = false;

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		if ($this->usePostgreLis) {
			$this->load->swapi('lis');
		} else {
			$this->load->database();
			$this->load->model('EvnLabSample_model', 'dbmodel');
		}

		$this->inputRules = array(
			'takeLabSample' => array(
				array(
					'field' => 'MedServiceType_SysNick',
					'label' => 'MedServiceType_SysNick',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'MedService_did',
					'label' => 'Служба, где взята проба',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnLabRequest_id',
					'label' => 'Заявка',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnLabSample_id',
					'label' => 'Проба',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'RefSample_id',
					'label' => 'Проба',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'sendToLis',
					'label' => 'sendToLis',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadResearchEditForm' => array(
				array(
					'field' => 'EvnUslugaPar_id',
					'label' => 'Услуга',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'saveResearch' => array(
				array(
					'field' => 'EvnUslugaPar_id',
					'label' => 'Услуга',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaPar_setDate',
					'label' => 'Дата выполнения',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'EvnUslugaPar_setTime',
					'label' => 'Время выполнения',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Lpu_aid',
					'label' => 'МО',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_aid',
					'label' => 'Отделение',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_aid',
					'label' => 'Врач',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_said',
					'label' => 'Ср. медперсонал',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaPar_Comment',
					'label' => 'Комментарий',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'EvnUslugaPar_IndexRep',
					'label' => 'Признак повтороной подачи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnUslugaPar_IndexRepInReg',
					'label' => 'Признак вхождения в реестр повтороной подачи',
					'rules' => '',
					'type' => 'int'
				),
                array(
                    'field' => 'UslugaMedType_id',
                    'label' => 'Вид услуги',
                    'rules' => '',
                    'type' => 'id'
                ),
            ),
			'saveComment' => array(
				array(
					'field' => 'EvnUslugaPar_id',
					'label' => 'Услуга',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaPar_Comment',
					'label' => 'Комментарий',
					'rules' => 'trim',
					'type' => 'string'
				),
			),
			'saveLabSample' => array(
				array(
					'field' => 'MedService_id',
					'label' => 'Служба',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnLabRequest_id',
					'label' => 'Заявка',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'RefSample_id',
					'label' => 'Проба',
					'rules' => '',
					'type' => 'id'
				)
			),
			'saveNewEvnLabSampleBarCode' => array(
				array(
					'field' => 'EvnLabSample_id',
					'label' => 'Идентификатор пробы',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnLabSample_BarCode',
					'label' => 'Штрих-код',
					'rules' => 'required',
					'type' => 'string'
				)
			),
			'saveNewEvnLabSampleNum' => array(
				array(
					'field' => 'EvnLabSample_id',
					'label' => 'Идентификатор пробы',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnLabSample_ShortNum',
					'label' => 'Номер',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'getEvnLabSampleFromLisWithResultCount' => array(
				array(
					'field' => 'MedService_id',
					'label' => 'Идентификатор службы',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'saveLabSampleAnalyzer' => array(
				array(
					'field' => 'EvnLabSample_id',
					'label' => 'Идентификатор пробы',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Analyzer_id',
					'label' => 'Идентификатор анализатора',
					'rules' => '',
					'type' => 'id'
				)
			),
			'saveLabSamplesAnalyzer' => array(
				array(
					'field' => 'EvnLabSamples',
					'label' => 'Пробы',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Analyzer_id',
					'label' => 'Идентификатор анализатора',
					'rules' => '',
					'type' => 'id'
				)
			),
			'loadRefValues' => array(
				array(
					'field' => 'EvnLabSample_setDT',
					'label' => 'EvnLabSample_setDT',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'MedService_id',
					'label' => 'Служба',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnDirection_id',
					'label' => 'Направление',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Человек',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaComplexTarget_id',
					'label' => 'Услуга исследования',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaComplex_ids',
					'label' => 'Список услуг',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'Analyzer_id',
					'label' => 'Анализатор',
					'rules' => '',
					'type' => 'id'
				)
			),
			'loadList' => array(
				array(
					'field' => 'EvnLabRequest_id',
					'label' => 'Заявка на лабораторное обследование',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Выбранная корневая комплекснеая услуга в заявке',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedService_id',
					'label' => 'Служба',
					'rules' => '',
					'type' => 'id'
				)
			),
			'loadLabSampleFrame' => array(
				array(
					'field' => 'EvnLabRequest_id',
					'label' => 'Заявка на лабораторное обследование',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Выбранная корневая комплекснеая услуга в заявке',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedService_id',
					'label' => 'Служба',
					'rules' => '',
					'type' => 'id'
				)
			),
			'loadEvnLabSampleListForWorksheet' => array(
				array(
					'field' => 'AnalyzerWorksheet_id',
					'label' => 'Рабочий список',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'default' => 100,
					'field' => 'limit',
					'label' => 'Количество',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'default' => 0,
					'field' => 'start',
					'label' => 'Старт',
					'rules' => 'trim',
					'type' => 'int'
				)
			),
			'loadListForCandiPicker' => array(
				array(
					'field' => 'AnalyzerWorksheet_id',
					'label' => 'Рабочий список',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'MedService_id',
					'label' => 'Служба',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnLabRequest_BarCode',
					'label' => 'Фильтр по штрих-коду',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'EvnLabSample_Num',
					'label' => 'Фильтр по номеру пробы',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'EvnLabSample_ShortNum',
					'label' => 'Фильтр по номеру пробы',
					'rules' => '',
					'type' => 'string'
				)
			),
			'prescrTest' => array(
				array(
					'field' => 'EvnLabSample_id',
					'label' => 'Идентификатор Пробы',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'tests',
					'label' => 'Идентификаторы услуг тестов',
					'rules' => '',
					'type' => 'json_array',
					'assoc' => true
				),
				array(
					'field' => 'EvnLabRequest_id',
					'label' => 'Заявка на лабораторное обследование',
					'rules' => '',
					'type' => 'id'
				)
			),
			'cancelTest' => array(
				array(
					'field' => 'EvnLabSample_id',
					'label' => 'Идентификатор Пробы',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'tests',
					'label' => 'Идентификаторы услуг тестов',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'EvnLabRequest_id',
					'label' => 'Заявка на лабораторное обследование',
					'rules' => '',
					'type' => 'id'
				)
			),
			'loadBarCode' => array(
				array(
					'field' => 'AnalyzerWorksheet_id',
					'label' => 'Идентификатор Рабочего списка',
					'rules' => '',
					'type' => 'id'
				)
			),
			'checkEvnLabSampleUnique' => array(
				array(
					'field' => 'EvnLabSample_Num',
					'label' => 'Номер пробы',
					'rules' => 'required',
					'type' => 'id'
				),
                array(
					'field' => 'MedService_id',
					'label' => 'Идентификатор лаборатории',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getOverdueSamples' => array(
				array(
					'field' => 'MedService_id',
					'label' => 'Идентификатор лаборатории',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadPathologySamples' => array(
				array(
					'field' => 'EvnLabSample_id',
					'label' => 'Идентификатор пробы',
					'rules' => 'required',
					'type' => 'string'
				)
			),
			'getPersonBySample' => array(
				array(
					'field' => 'EvnLabSample_id',
					'label' => 'Идентификатор пробы',
					'rules' => 'required',
					'type' => 'string'
				)
			),
			'loadResearchHistory' => array(
				array(
					'field' => 'EvnLabSample_id',
					'label' => 'Идентификатор пробы',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'Codes',
					'label' => 'Список кодов услуг',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'MinDate',
					'label' => 'Дата с',
					'type' => 'string'
				),
				array(
					'field' => 'MaxDate',
					'label' => 'Дата по',
					'type' => 'string'
				)
			),
			'loadLabResearchResultHistory' => array(
				array(
					'field' => 'UslugaTest_id',
					'label' => 'Идентификатор теста',
					'rules' => 'required',
					'type' => 'string'
				)
			),
			'approveResults' => array(
				array(
					'field' => 'EvnLabSample_id',
					'label' => 'Идентификатор Пробы',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaTest_id',
					'label' => 'Идентификатор теста',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaTest_ids',
					'label' => 'Идентификаторы тестов',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'onlyNorm',
					'label' => 'Признак одобрения только результатов в норме',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'MedServiceType_SysNick',
					'label' => 'Тип службы',
					'rules' => '',
					'type' => 'string'
				)
			),
			'unapproveResults' => array(
				array(
					'field' => 'EvnLabSample_id',
					'label' => 'Идентификатор Пробы',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaTest_id',
					'label' => 'Идентификатор теста',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaTest_ids',
					'label' => 'Идентификаторы тестов',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'MedServiceType_SysNick',
					'label' => 'Тип службы',
					'rules' => '',
					'type' => 'string'
				)
			),
			'getSampleUsluga' => array(
				array(
					'field' => 'EvnLabSample_id',
					'label' => 'Идентификатор Пробы',
					'rules' => 'required',
					'type' => 'string'
				)
			),
			'load' => array(
				array(
					'field' => 'EvnLabSample_id',
					'label' => 'Идентификатор Пробы',
					'rules' => 'required',
					'type' => 'id'
				),
                array(
					'field' => 'EvnLabRequest_BarCode',
					'label' => 'Штрих код пробы',
					'rules' => '',
					'type' => 'int'
				),
                array(
					'field' => 'MedService_id',
					'label' => 'Идентификатор лаборатории',
					'rules' => '',
					'type' => 'id'
				)
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
			'getTestListForm250' => array(
				array('field' => 'Date','label' => 'Дата','rules' => '','type' => 'date', 'default' => null),
				array('field' => 'MedService_id','label' => 'Служба','rules' => 'required','type' => 'id'),
                array('field' => 'MedServiceType_SysNick', 'label' => '', 'rules' => '', 'type' => 'string'),
				array('field' => 'UslugaComplex_id','label' => 'Услуга','rules' => '','type' => 'id')
			),
			'loadSampleListForm250' => array(
				array('field' => 'Date','label' => 'Дата','rules' => '','type' => 'date', 'default' => null),
				array('field' => 'MedService_id','label' => 'Служба','rules' => 'required','type' => 'id'),
                array('field' => 'MedServiceType_SysNick', 'label' => '', 'rules' => '', 'type' => 'string'),
				array('field' => 'UslugaComplex_id','label' => 'Услуга','rules' => '','type' => 'id')
			),
			'saveEvnLabSampleComment' => array(
				array('field' => 'EvnLabSample_id','label' => 'Проба','rules' => 'required','type' => 'id'),
				array('field' => 'EvnLabSample_Comment','label' => 'Примечание','rules' => '','type' => 'string')
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
				array('field' => 'MedService_sid','label' => 'Текущая Служба','rules' => '','type' => 'id')
			),
			'getLabSampleResultGrid' => array(
				array('field' => 'EvnDirection_id' ,'label' => 'Заявка','rules' => '','type' => 'id'),
				array('field' => 'EvnLabSample_id' ,'label' => 'Проба','rules' => '','type' => 'id'),
				array('field' => 'RefSample_id' ,'label' => 'Проба','rules' => '','type' => 'id'),
				array('field' => 'MethodsIFA_id', 'label' => 'Методика ИФА', 'rules' => '', 'type' => 'int'),
				array('field' => 'AnalyzerTest_id', 'label' => 'Исследование', 'rules' => '', 'type' => 'int'),
				array('field' => 'formMode', 'label' => 'Режим формы', 'rules' => '', 'type' => 'string')
			),
			'save' => array(
				array('field'=>'EvnLabSample_id'          ,'label' => 'EvnLabSample_id'                       ,'rules' => '', 'type' => 'int'),
				array('field'=>'EvnLabSample_pid'         ,'label' => 'EvnLabSample_pid'                      ,'rules' => '', 'type' => 'id'),
				array('field'=>'EvnLabSample_rid'         ,'label' => 'EvnLabSample_rid'                      ,'rules' => '', 'type' => 'id'),
				array('field'=>'Lpu_id'                   ,'label' => 'Lpu_id'                                ,'rules' => '', 'type' => 'id'),
				array('field'=>'Server_id'                ,'label' => 'Server_id'                             ,'rules' => '', 'type' => 'int'),
				array('field'=>'PersonEvn_id'             ,'label' => 'PersonEvn_id'                          ,'rules' => '', 'type' => 'id'),
				array('field'=>'EvnLabSample_setDT'       ,'label' => 'EvnLabSample_setDT'                    ,'rules' => '', 'type' => 'datetime'),
				array('field'=>'EvnLabSample_disDT'       ,'label' => 'EvnLabSample_disDT'                    ,'rules' => '', 'type' => 'date'),
				array('field'=>'EvnLabSample_didDT'       ,'label' => 'EvnLabSample_didDT'                    ,'rules' => '', 'type' => 'date'),
				array('field'=>'EvnLabSample_insDT'       ,'label' => 'EvnLabSample_insDT'                    ,'rules' => '', 'type' => 'date'),
				array('field'=>'EvnLabSample_updDT'       ,'label' => 'EvnLabSample_updDT'                    ,'rules' => '', 'type' => 'date'),
				array('field'=>'EvnLabSample_Index'       ,'label' => 'EvnLabSample_Index'                    ,'rules' => '', 'type' => 'int'),
				array('field'=>'EvnLabSample_Count'       ,'label' => 'EvnLabSample_Count'                    ,'rules' => '', 'type' => 'int'),
				array('field'=>'Morbus_id'                ,'label' => 'Morbus_id'                             ,'rules' => '', 'type' => 'id'),
				array('field'=>'EvnLabSample_IsSigned'    ,'label' => 'EvnLabSample_IsSigned'                 ,'rules' => '', 'type' => 'id'),
				array('field'=>'pmUser_signID'            ,'label' => 'pmUser_signID'                         ,'rules' => '', 'type' => 'id'),
				array('field'=>'EvnLabSample_signDT'      ,'label' => 'EvnLabSample_signDT'                   ,'rules' => '', 'type' => 'id'),
				array('field'=>'EvnLabRequest_id'         ,'label' => 'Заявка на лабораторное исследование'   ,'rules' => '', 'type' => 'id'),
				array('field'=>'EvnLabSample_Num'         ,'label' => 'Номер пробы'                           ,'rules' => '', 'type' => 'string'),
				array('field'=>'EvnLabSample_BarCode'         ,'label' => 'Штрих-код пробы'                           ,'rules' => '', 'type' => 'string'),
				array('field'=>'EvnLabSample_Comment'         ,'label' => 'Комментарий'                           ,'rules' => '', 'type' => 'string'),
				array('field'=>'RefSample_id'             ,'label' => 'Справочник проб'                       ,'rules' => '', 'type' => 'id'),
				array('field'=>'Lpu_did'                  ,'label' => 'ЛПУ взявшее пробу'                     ,'rules' => '', 'type' => 'id'),
				array('field'=>'LpuSection_did'           ,'label' => 'Отделение взявшее пробу'               ,'rules' => '', 'type' => 'id'),
				array('field'=>'MedPersonal_did'          ,'label' => 'Врач взявший пробу'                    ,'rules' => '', 'type' => 'id'),
				array('field'=>'MedPersonal_sdid'         ,'label' => 'Средний медперсонал взявший пробу'     ,'rules' => '', 'type' => 'id'),
				array('field'=>'MedService_id'           ,'label' => 'Служба заявки'						  ,'rules' => '', 'type' => 'id'),
				array('field'=>'MedService_did'          ,'label' => 'Служба взявшая пробу'   				  ,'rules' => '', 'type' => 'id'),
				array('field'=>'MedService_sid'          ,'label' => 'Текущая служба'						  ,'rules' => '', 'type' => 'id'),
				array('field'=>'EvnLabSample_DelivDT'     ,'label' => 'Дата и время доставки пробы'           ,'rules' => '', 'type' => 'datetime'),
				array('field'=>'Lpu_aid'                  ,'label' => 'ЛПУ выполнившее анализ'                ,'rules' => '', 'type' => 'id'),
				array('field'=>'LpuSection_aid'           ,'label' => 'Отделение выполнившее анализ'          ,'rules' => '', 'type' => 'id'),
				array('field'=>'MedPersonal_aid'          ,'label' => 'Врач выполнивший анализ'               ,'rules' => '', 'type' => 'id'),
				array('field'=>'MedPersonal_said'         ,'label' => 'Средний медперсонал выполнивший анализ','rules' => '', 'type' => 'id'),
				array('field'=>'EvnLabSample_StudyDT'     ,'label' => 'Дата и время выполнения исследования'  ,'rules' => '', 'type' => 'datetime'),
				array('field'=>'LabSampleDefectiveType_id','label' => 'Брак пробы'                            ,'rules' => '', 'type' => 'id'),
				array('field'=>'DefectCauseType_id','label' => 'Брак пробы'                            ,'rules' => '', 'type' => 'id'),
				array('field'=>'Analyzer_id','label' => 'Анализатор','rules' => '', 'type' => 'id'),
				array('field'=>'pmUser_id'             ,'label' => 'идентификатор пользователя системы Промед','rules' => '', 'type' => 'id'),//
				array('field'=>'RecordStatus_Code'     ,'label' => 'идентификатор состояния записи','rules' => '', 'type' => 'int'),//
				array('field'=>'LabSample_Results'         ,'label' => 'Результаты пробы'                           ,'rules' => '', 'type' => 'string', 'onlyRule' => true),
			),
			'cancel' => array(
				array(
					'field' => 'EvnLabSample_ids',
					'label' => 'Идентификаторы проб',
					'rules' => 'required',
					'type' => 'string'
				)
			),
			'transferLabSampleResearches' => array(
				array(
					'field' => 'EvnLabSample_oldid',
					'label' => 'Идентификатор старой пробы',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnLabSample_newid',
					'label' => 'Идентификатор новой пробы',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'tests',
					'label' => 'Тесты',
					'rules' => 'required',
					'type' => 'json_array',
					'assoc' => true
				)
			),
			'updateResult' => array(
				array(
					'field' => 'UslugaTest_id',
					'label' => 'Идентификатор теста',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaTest_ResultValue',
					'label' => 'Результат',
					'rules' => 'notnull|trim',
					'type' => 'string'
				),
				array(
					'field' => 'UslugaTest_setDT',
					'label' => 'Дата выполнения теста',
					'rules' => 'trim',
					'type' => 'datetime'
				),
				array(
					'field' => 'UslugaTest_Unit',
					'label' => 'Единицы измерения',
					'rules' => 'notnull',
					'type' => 'string'
				),
				array(
					'field' => 'UslugaTest_Comment',
					'label' => 'Комментарий',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'UslugaTest_RefValues',
					'label' => 'Референсные значения',
					'rules' => 'notnull',
					'type' => 'string'
				),
				array(
					'field' => 'updateType',
					'label' => 'Тип',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'sourceName',
					'label' => 'Источник',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'EvnLabSample_id',
					'label' => 'id пробы',
					'rules' => '',
					'type'  => 'string'
				),
				array(
					'field' => 'UslugaTest_Code',
					'label' => 'Код теста',
					'rules' => '',
					'type' => 'string'
				),
			),
			'saveEvnLabSampleDefect' => array(
				array(
					'field' => 'DefectCauseType_id',
					'label' => 'Идентификатор типа брака',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnLabSample_id',
					'label' => 'Идентификатор пробы',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnLabSample_BarCode',
					'label' => 'Штрих-код пробы',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'MedServiceType_SysNick',
					'label' => 'Арм',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'MedService_sid',
					'label' => 'Текущая служба',
					'rules' => '',
					'type' => 'id'
				)
			),
			'deleteEvnLabSampleDefect' => array(
				array(
					'field' => 'EvnLabSample_id',
					'label' => 'Идентификатор пробы',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'approveEvnLabSampleResults' => array(
				array(
					'field' => 'EvnLabSamples',
					'label' => 'EvnLabSamples',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'onlyNormal',
					'label' => 'onlyNormal',
					'rules' => '',
					'type' => 'int'
				)
			),
			'saveLabSampleResearches' => array(
				array(
					'field' => 'MedService_id',
					'label' => 'Служба',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnLabRequest_id',
					'label' => 'Заявка',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'RefSample_id',
					'label' => 'Проба',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnLabSample_id',
					'label' => 'Идентификатор пробы',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'researches',
					'label' => 'researches',
					'rules' => '',
					'type' => 'string'
				)
			),
			'getEvnLabSample' => array(
				array(
					'field' => 'EvnUslugaPar_id',
					'label' => 'Услуга',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getNewListEvnLabSampleNum' => array(
				array(
					'field' => 'MedService_id',
					'label' => 'Служба, для которой печатаются номера',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'quantity',
					'label' => 'количество номеров',
					'rules' => 'required',
					'type' => 'int'
				)
			),
		);
	}

	/**
	 * Получение количества проб из лис с результатами
	 */
	function getEvnLabSampleFromLisWithResultCount() {
		$data = $this->ProcessInputData('getEvnLabSampleFromLisWithResultCount', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->GET("EvnLabSample/ResultCount", $data);
			$this->ProcessRestResponse($response, 'single')->ReturnData();
		} else {
			$response = $this->dbmodel->getEvnLabSampleFromLisWithResultCount($data);
			$this->ProcessModelSave($response, true)->ReturnData();
		}
	}

	/**
	 * Массовое одобрение результатов проб
	 */
	function approveEvnLabSampleResults() {
		$data = $this->ProcessInputData('approveEvnLabSampleResults', true);
		if ($data === false) return;
		$MedService_id = $data['session']['CurARM']['MedService_id'];
		$data['MedService_id'] = $MedService_id;

		$this->load->model('MedService_model', 'MedService_model');
		$MS = $this->MedService_model->loadEditForm(['MedService_id' => $MedService_id]);
		$data['MedService_IsQualityTestApprove'] = $MS && $MS[0] ? $MS[0]['MedService_IsQualityTestApprove'] : 0;

		if ($this->usePostgreLis) {
			$response = $this->lis->POST("EvnLabSample/massApproveResults", $data);
			if ($response && isset($response['data'])) $response = $response['data'];
			if ($response && isset($response['error_msg'])) {
				$response['Error_Msg'] = $response['error_msg'];
			}
			$this->ProcessModelSave($response, true)->ReturnData();
		} else {
			$response = $this->dbmodel->approveEvnLabSampleResults($data);
			$this->ProcessModelSave($response, true)->ReturnData();
		}
	}

	/**
	 * Получение списка референсных значений пробы
	 */
	function loadRefValues() {
		$data = $this->ProcessInputData('loadRefValues', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->GET("EvnLabSample/RefValues", $data);
			$this->ProcessRestResponse($response, 'single')->ReturnData();
		} else {
			$response = $this->dbmodel->loadRefValues($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}

	/**
	 * Сохранение нового штрих-кода пробы
	 */
	function saveNewEvnLabSampleBarCode() {
		$data = $this->ProcessInputData('saveNewEvnLabSampleBarCode', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->PATCH("EvnLabSample/BarCode", $data);
			$this->ProcessRestResponse($response, 'single')->ReturnData();
		} else {
			$response = $this->dbmodel->saveNewEvnLabSampleBarCode($data);
			$this->ProcessModelSave($response, true)->ReturnData();
		}
	}

	/**
	 * Сохранение нового номера пробы
	 */
	function saveNewEvnLabSampleNum() {
		$data = $this->ProcessInputData('saveNewEvnLabSampleNum', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->PATCH("EvnLabSample/Num", $data);
			$this->ProcessRestResponse($response, 'single')->ReturnData();
		} else {
			$response = $this->dbmodel->saveNewEvnLabSampleNum($data);
			$this->ProcessModelSave($response, true)->ReturnData();
		}
	}

	/**
	 * Взятие пробы
	 */
	function takeLabSample() {
		$data = $this->ProcessInputData('takeLabSample', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->POST("EvnLabSample/take", $data);
			$this->ProcessRestResponse($response, 'single')->ReturnData();
		} else {
			$response = $this->dbmodel->takeLabSample($data);
			$this->ProcessModelSave($response, true)->ReturnData();
		}
	}

	/**
	 * получить перечень номеров без привязки к пробе
	 */
	function getNewListEvnLabSampleNum() {
		$data = $this->ProcessInputData('getNewListEvnLabSampleNum', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->GET("EvnLabSample/NumList/generate", $data);
			$this->ProcessRestResponse($response, 'single')->ReturnData();
		} else {
			$response = $this->dbmodel->getNewListEvnLabSampleNum($data);
			$this->ProcessModelSave($response, true)->ReturnData();
		}
	}

	/**
	 * Сохранение пробы
	 */
	function saveLabSample() {
		$data = $this->ProcessInputData('saveLabSample', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->POST("EvnLabSample/saveLabSample", $data);
			$this->ProcessRestResponse($response, 'single')->ReturnData();
		} else {
			$response = $this->dbmodel->saveLabSample($data);
			$this->ProcessModelSave($response, true)->ReturnData();
		}
	}

	/**
	 * Сохранение исследований
	 */
	function saveLabSampleResearches() {
		$data = $this->ProcessInputData('saveLabSampleResearches', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->POST("EvnLabSample/Researches", $data);
			$this->ProcessRestResponse($response, 'single')->ReturnData();
		} else {
			$response = $this->dbmodel->saveLabSampleResearches($data);
			$this->ProcessModelSave($response, true)->ReturnData();
		}
	}

	/**
	 * Сохранение отбраковки
	 */
	function saveEvnLabSampleDefect() {
		$data = $this->ProcessInputData('saveEvnLabSampleDefect', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->POST("EvnLabSample/Defect", $data);
			$this->ProcessRestResponse($response, 'single')->ReturnData();
		} else {
			$response = $this->dbmodel->saveEvnLabSampleDefect($data);
			$this->ProcessModelSave($response, true)->ReturnData();
		}
	}

	/**
	 * Удаление отбраковки
	 */
	function deleteEvnLabSampleDefect() {
		$data = $this->ProcessInputData('deleteEvnLabSampleDefect', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->DELETE("EvnLabSample/Defect", $data);
			$this->ProcessRestResponse($response, 'single')->ReturnData();
		} else {
			$response = $this->dbmodel->deleteEvnLabSampleDefect($data);
			$this->ProcessModelSave($response, true)->ReturnData();
		}
	}

	/**
	 * Вывод списка
	 * @return int
	 */
	function loadLabSampleFrame() {
		$data = $this->ProcessInputData('loadLabSampleFrame', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->GET("EvnLabSample/List", $data);
			$this->ProcessRestResponse($response)->ReturnData();
		} else {
			$response = $this->dbmodel->loadLabSampleFrame($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}

	/**
	 * Изменение результатов теста
	 */
	function updateResult() {
		$data = $this->ProcessInputData('updateResult', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$data['UslugaTest_ResultValue'] = str_replace('+', 'PLUS', $data['UslugaTest_ResultValue']);
			$response = $this->lis->PUT("EvnLabSample/Result", $data);
			$this->ProcessRestResponse($response)->ReturnData();
		} else {
			// 1. Обновили текущий тест
			$response = $this->dbmodel->updateResult($data);

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

			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}

	/**
	 * Возвращаем пробы с патологией
	 */
	function loadPathologySamples() {
		$data = $this->ProcessInputData('loadPathologySamples', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->GET("EvnLabSample/PathologySamples", $data);
			if (!$this->isSuccessful($response)) {
				return $response;
			}
			$response = $response['data'];
		} else {
			$response = $this->dbmodel->loadPathologySamples($data);
		}

		$normalSamples = []; $allSamples = []; $pathologySamples = [];
		foreach($response as $res) {
			if ($res['EvnLabSample_IsOutNorm'] != 2) {
				array_push($normalSamples, $res['EvnLabSample_id']);
			} else {
				array_push($pathologySamples, $res['EvnLabSample_id']);
			}
			array_push($allSamples, $res['EvnLabSample_id']);
		}
		$this->ProcessModelList([
			"normalSamples" => $normalSamples,
			"pathologySamples" => $pathologySamples,
			"allSamples" => $allSamples
		], true)->ReturnData();
	}

	/**
	 * Получение данных человека из пробы
	 */
	function getPersonBySample() {
		$data = $this->ProcessInputData('getPersonBySample', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->GET('EvnLabSample/PersonBySample', $data);
			$this->ProcessRestResponse($response)->ReturnData();
		} else {
			$response = $this->dbmodel->getPersonBySample($data);
			if (isset($response['Error_Msg'])) {
				throw new Exception($response['Error_Msg']);
			}
			$this->ProcessModelList($response)->ReturnData();
		}
	}

	/**
	 * Возвращаем историю исследований
	 */
	function loadResearchHistory() {
		$data = $this->ProcessInputData('loadResearchHistory', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->GET('EvnLabSample/ResearchHistory', $data);
			$this->ProcessRestResponse($response)->ReturnData();
		} else {
			$response = $this->dbmodel->loadResearchHistory($data);
			if (isset($response['Error_Msg'])) {
				throw new Exception($response['Error_Msg']);
			}
			$this->ProcessModelList($response)->ReturnData();
		}
	}

	/**
	 * Проверяем наличие заявок не закрытых больше месяца
	 */
	function getOverdueSamples() {
		$data = $this->ProcessInputData('getOverdueSamples', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->GET("EvnLabSample/Overdue", $data);
			$this->ProcessRestResponse($response, 'single')->ReturnData();
		} else {
			$response = $this->dbmodel->getOverdueSamples($data);
			$this->ProcessModelSave($response, true)->ReturnData();
		}
	}

	/**
	 * Назначение теста
	 */
	function prescrTest() {
		$data = $this->ProcessInputData('prescrTest', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->POST("EvnLabSample/Test", $data);
			$this->ProcessRestResponse($response, 'single')->ReturnData();
		} else {
			$response = $this->dbmodel->prescrTest($data);
			$this->ProcessModelSave($response, true)->ReturnData();
		}
	}

	/**
	 * Отмена (удаление) теста
	 */
	function cancelTest() {
		$data = $this->ProcessInputData('cancelTest', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->DELETE("EvnLabSample/Test", $data);
			$this->ProcessRestResponse($response, 'single')->ReturnData();
		} else {
			$response = $this->dbmodel->cancelTest($data);
			$this->ProcessModelSave($response, true)->ReturnData();
		}
	}

	/**
	 * Перенос тестов из одной пробы в другую
	 */
	function transferLabSampleResearches() {
		$data = $this->ProcessInputData('transferLabSampleResearches', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->POST("EvnLabSample/transferResearches", $data);
			$this->ProcessRestResponse($response, 'single')->ReturnData();
		} else {
			$response = $this->dbmodel->transferLabSampleResearches($data);
			$this->ProcessModelSave($response, true)->ReturnData();
		}
	}

	/**
	 * Одобрение результатов пробы
	 */
	function approveResults() {
		$data = $this->ProcessInputData('approveResults', true);
		if ($data === false) return;
		$data['action'] = 'unapprove';

		if ($this->usePostgreLis) {
			$response = $this->lis->POST("EvnLabSample/approveResults", $data);
			$this->ProcessRestResponse($response, 'single')->ReturnData();
		} else {
			$response = $this->dbmodel->approveResults($data);
			$this->ProcessModelSave($response, true)->ReturnData();
		}
	}

	/**
	 * Выбор услуг для порб
	 */
	function getSampleUsluga() {
		$data = $this->ProcessInputData('getSampleUsluga', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->GET("EvnLabSample/Usluga", $data);
			$this->ProcessRestResponse($response)->ReturnData();
		} else {
			$response = $this->dbmodel->getSampleUsluga($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}

	/**
	 * Выбор анализатора для пробы
	 */
	function saveLabSampleAnalyzer() {
		$data = $this->ProcessInputData('saveLabSampleAnalyzer', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$data['EvnLabSamples'] = $data['EvnLabSample_id'];
			$response = $this->lis->POST("EvnLabSample/Analyzer", $data);
			$this->ProcessRestResponse($response, 'single')->ReturnData();
		} else {
			$response = $this->dbmodel->saveLabSampleAnalyzer($data);
			$this->ProcessModelSave($response, true)->ReturnData();
		}
	}

	/**
	 * Выбор анализатора для проб
	 */
	function saveLabSamplesAnalyzer() {
		$data = $this->ProcessInputData('saveLabSamplesAnalyzer', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			//нужно, чтобы правильно символы в url закодировались
			$data['EvnLabSamples'] = json_decode($data['EvnLabSamples'], true);
			$data['EvnLabSamples'] = json_encode($data['EvnLabSamples']);

			$response = $this->lis->POST("EvnLabSample/Analyzer", $data);
			$this->ProcessRestResponse($response, 'single')->ReturnData();
		} else {
			$response = $this->dbmodel->saveLabSamplesAnalyzer($data);
			$this->ProcessModelSave($response, true)->ReturnData();
		}
	}

	/**
	 * Снятие одобрения результатов пробы
	 */
	function unapproveResults() {
		$data = $this->ProcessInputData('unapproveResults', true);
		if ($data === false) return;
		$data['action'] = 'unapprove';

		if ($this->usePostgreLis) {
			//для старых заявок, что могут храниться в mssql
			//try-catch на случай, когда лис запустят на отдельных серверах
			//без доступа к default mssql db
			try {
				$this->load->model('EvnLabSample_model', 'dbmodel');
				$this->db = $this->load->database('default', true);
				$this->dbmodel->unapproveResults($data);
			} catch (\Exception $e) {

			}

			$response = $this->lis->POST("EvnLabSample/unapproveResults", $data);
			$this->ProcessRestResponse($response, 'single')->ReturnData();
		} else {
			$response = $this->dbmodel->unapproveResults($data);
			$this->ProcessModelSave($response, true)->ReturnData();
		}
	}

	/**
	 * Получение списка результатов пробы
	 */
	function getLabSampleResultGrid() {
		$data = $this->ProcessInputData('getLabSampleResultGrid', true);
		if ($data === false) return;

		if (empty($data['EvnLabSample_id']) && empty($data['RefSample_id'])) {
			$this->ReturnError('Не указана проба');
			return;
		}

		if ($this->usePostgreLis) {
			$response = $this->lis->GET("EvnLabSample/ResultGrid", $data);
			$this->ProcessRestResponse($response)->ReturnData();
		} else {
			$response = $this->dbmodel->getLabSampleResultGrid($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}

	/**
	 * Функция чтения рабочего журнала проб
	 * @return bool
	 */
	function loadWorkList() {
		$data = $this->ProcessInputData('loadWorkList', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->GET("EvnLabSample/WorkList", $data);
			$this->ProcessRestResponse($response)->ReturnData();
		} else {
			$response = $this->dbmodel->loadWorkList($data);
			$this->ProcessModelList($response, true, true)->formatDatetimeFields('d.m.Y H:i')->ReturnData();
		}
	}

	/**
	 * Функция чтения журнала отбраковки проб
	 */
	function loadDefectList() {
		$data = $this->ProcessInputData('loadDefectList', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->GET("EvnLabSample/DefectList", $data);
			$this->ProcessRestResponse($response)->ReturnData();
		} else {
			$response = $this->dbmodel->loadDefectList($data);
			$this->ProcessModelList($response, true, true)->formatDatetimeFields('d.m.Y H:i')->ReturnData();
		}
	}

	/**
	 * Загрузка грида проб-кандидатов
	 */
	function loadListForCandiPicker() {
		$data = $this->ProcessInputData('loadListForCandiPicker', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->GET("EvnLabSample/ListForCandiPicker", $data);
			$this->ProcessRestResponse($response)->ReturnData();
		} else {
			$response = $this->dbmodel->loadListForCandiPicker($data);

			for ($i = 0; $i < count($response); $i++) {
				if (empty($response[$i]['EvnLabSample_ShortNum'])) {
					$response[$i]['EvnLabSample_ShortNum'] = substr($response[$i]['EvnLabSample_Num'], -4);
				}
			}

			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}

	/**
	 * Загрузка списка штрихкодов для данного рабочего списка
	 */
	function loadBarCode() {
		$data = $this->ProcessInputData('loadBarCode', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->GET("EvnLabSample/BarCode", $data);
			$this->ProcessRestResponse($response)->ReturnData();
		} else {
			$response = $this->dbmodel->loadBarCode($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}

	/**
	 * Првоерка 12-ти значного номера пробы на уникальность
	 */
	function checkEvnLabSampleUnique() {
		$data = $this->ProcessInputData('checkEvnLabSampleUnique', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->GET("EvnLabSample/checkUnique", $data);
			$this->ProcessRestResponse($response)->ReturnData();
		} else {
			$response = $this->dbmodel->checkEvnLabSampleUnique($data);

			if (isset($response['Error_Msg']) && strlen($response['Error_Msg']) != 0) {
				$result = array('success' => false, 'Error_Msg' => toUTF($response['Error_Msg']));
			} else {
				$result = array('success' => true);
			}

			$this->ReturnData($result);
		}
	}

	/**
	 * Загрузка
	 */
	function load() {
		$data = $this->ProcessInputData('load', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->GET("EvnLabSample", $data);
			$this->ProcessRestResponse($response)->ReturnData();
		} else {
			$this->dbmodel->EvnLabSample_id = $data['EvnLabSample_id'];
			$response = $this->dbmodel->load();
			$this->ProcessModelList($response, true, true)->formatDatetimeFields('d.m.Y H:i')->ReturnData();
		}
	}

	/**
	 * Загрузка списка проб для рабочего списка
	 */
	function loadEvnLabSampleListForWorksheet() {
		$data = $this->ProcessInputData('loadEvnLabSampleListForWorksheet', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->GET("EvnLabSample/ListForWorksheet", $data);
			$this->ProcessRestResponse($response)->ReturnData();
		} else {
			$response = $this->dbmodel->loadEvnLabSampleListForWorksheet($data);

			for ($i = 0; $i < count($response['data']); $i++) {
				if (empty($response['data'][$i]['EvnLabSample_ShortNum'])) {
					$response['data'][$i]['EvnLabSample_ShortNum'] = substr($response['data'][$i]['EvnLabSample_Num'], -4);
				}
			}

			$this->ProcessModelMultiList($response, true, true)->ReturnData();
		}
	}

	/**
	 * Сохранение
	*/
	function save() {
		$data = $this->ProcessInputData('save', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->POST("EvnLabSample", $data);
			$this->ProcessRestResponse($response, 'single')->ReturnData();
		} else {
			if (isset($data['EvnLabSample_id']) && $data['EvnLabSample_id']){
				$this->dbmodel->EvnLabSample_id = $data['EvnLabSample_id'];
				$this->dbmodel->load();
				// служба и служба взятия не приходят с формы, должны остаться теми же, что и были.
				$data['MedService_id'] = $this->dbmodel->fields['MedService_id'];
				$data['MedService_did'] = $this->dbmodel->fields['MedService_did'];
			}
			$this->dbmodel->assign($data);
			$response = $this->dbmodel->save($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
		}
	}

	/**
	 * Удаление
	 */
	function cancel() {
		$data = $this->ProcessInputData('cancel', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->DELETE("EvnLabSample", $data);
			$this->ProcessRestResponse($response, 'single')->ReturnData();
		} else {
			$response = $this->dbmodel->cancel($data);
			$this->ProcessModelSave($response, true)->ReturnData();
		}
	}

	/**
	 * Сохранение параметров исследования
	 */
	function saveResearch() {
		$data = $this->ProcessInputData('saveResearch', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->POST("EvnLabSample/Research", $data);
			$this->ProcessRestResponse($response, 'single')->ReturnData();
		} else {
			$response = $this->dbmodel->saveResearch($data);
			$this->ProcessModelSave($response, true)->ReturnData();
		}
	}

	/**
	 * Сохранение комментария для исследования
	 */
	function saveComment() {
		$data = $this->ProcessInputData('saveComment', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->PATCH("EvnLabSample/Research/Comment", $data);
			$this->ProcessRestResponse($response, 'single')->ReturnData();
		} else {
			$response = $this->dbmodel->saveComment($data);
			$this->ProcessModelSave($response, true)->ReturnData();
		}
	}

	/**
	 * Загрузка параметров исследования
	 */
	function loadResearchEditForm() {
		$data = $this->ProcessInputData('loadResearchEditForm', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->GET("EvnLabSample/Research", $data);
			$this->ProcessRestResponse($response)->ReturnData();
		} else {
			$response = $this->dbmodel->loadResearchEditForm($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}

	/**
	 * Получение пробы
	 */
	function getEvnLabSample() {
		$data = $this->ProcessInputData('getEvnLabSample', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->GET("EvnLabSample/fromUsluga", $data);
			$this->ProcessRestResponse($response)->ReturnData();
		} else {
			$response = $this->dbmodel->getEvnLabSample($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}

	/**
	 * Получение списка всех тестов для анализа за выбранный день в выбранной лаборатории
	 * (для столбцов формы 250у)
	 */
	function getTestListForm250() {
		$data = $this->ProcessInputData('getTestListForm250', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->GET("EvnLabSample/TestListForm250", $data);
		} else {
			$response = $this->dbmodel->getTestListForm250($data);
		}

		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Получение списка взятых проб из лис с результатами
	 * (для формы 250у)
	 */
	function loadSampleListForm250() {
		$data = $this->ProcessInputData('loadSampleListForm250', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->GET("EvnLabSample/SampleListForm250", $data);
			$this->ProcessRestResponse($response)->ReturnData();
		} else {
			$response = $this->dbmodel->loadSampleListForm250($data);

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

			$response = $sampleList;

			$this->ProcessModelList($response, true, true)->formatDatetimeFields('d.m.Y H:i')->ReturnData();
		}
	}

	/**
	 * Сохранение комментария к пробе
	 */
	function saveEvnLabSampleComment() {
		$data = $this->ProcessInputData('saveEvnLabSampleComment', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->PATCH("EvnLabSample/Comment", $data);
			$this->ProcessRestResponse($response, 'single')->ReturnData();
		} else {
			$response = $this->dbmodel->saveEvnLabSampleComment($data);
			$this->ProcessModelSave($response, true)->ReturnData();
		}
	}

}

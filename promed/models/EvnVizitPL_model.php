<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package      PromedWeb
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @link         http://swan.perm.ru/PromedWeb
 */
require_once('EvnVizitAbstract_model.php');
/**
 * EvnVizitPL_model - Модель посещения поликлиники
 *
 * Содержит методы и свойства общие для всех объектов,
 * классы которых наследуют класс EvnVizitPL
 *
 * Посещения поликлиники и стоматки могут сохраняться из множества мест:
 * 1. Форма добавления/редактирования посещения в поточном вводе
 * 2. Форма добавления ТАП и посещения swEmkEvnPLEditWindow (addEvnPL)
 * 3. Форма добавления посещения (addEvnVizitPL)
 * 3. Форма редактирования посещения (editEvnVizitPL)
 * Должны отрабатывать все проверки, в т.ч. возможности изменения атрибутов
 * 3. Форма редактирования ТАП (editEvnPL, closeEvnPL)
 * Должны отрабатывать все проверки, в т.ч. возможности изменения атрибутов
 * 4. ЭМК - автоматическое создание ТАП и посещения (addEvnPL)
 * 5. ЭМК - автоматическое создание посещения (addEvnVizitPL)
 * 6. ЭМК - обновление отдельных атрибутов ТАП или посещения (openEvnPL)
 * Должны отрабатывать проверки возможности изменения этих атрибутов
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Александр Пермяков
 * @version      08.2014
 *
 * @property string $Time Время приема
 * @property int $ServiceType_id Место required
 * @property int $VizitType_id Цель посещения required
 * @property int $VizitClass_id Первично/повторно
 * @property int $ProfGoal_id Цель профосмотра
 * @property int $Diag_id Основной диагноз
 * @property int $DeseaseType_id Характер заболевания
 * @property int $TumorStage_id Стадия выявленного ЗНО
 * @property int $Diag_spid Подозрение на диагноз
 * @property int $Diag_agid Осложнение основного диагноза
 * @property int $HealthKind_id Группа здоровья
 * @property int $RiskLevel_id Фактор Риска
 * @property int $WellnessCenterAgeGroups_id Группа ЦЗ
 * @property int $HomeVisit_id вызов на дом
 * @property int $EvnPrescr_id Связанное назначение
 * @property int $DispClass_id Тип диспансеризации/мед. осмотра
 * @property int $DispProfGoalType_id Тип диспансеризации/мед. осмотра для  Уфы
 * @property int $EvnPLDisp_id Карта диспансеризации/мед. осмотра
 * @property int $LpuSectionProfile_id Профиль посещения
 * @property int $UslugaComplex_id Код посещения
 * @property int $EvnUslugaVizit_id Услуга сохраненного посещения
 * @property int $PainIntensity_id Интенсивность боли
 *
 * @property-read string $serviceTypeSysNick Код места посещения
 * @property-read string $vizitTypeSysNick Код цели посещения
 * @property-read bool $isUseVizitCode
 * @property-read string $vizitCode Код посещения
 * @property-read int $lpuUnitSetCode Код
 * @property-read array $evnUslugaList Список услуг посещения, в т.ч. с кодом посещения
 * @property-read array $lpuSectionData
 *
 * @property EvnPL_model $parent
 *
 * @property-read TimetableGraf_model $TimetableGraf_model
 * @property-read PersonIdentRequest_model $PersonIdentRequest_model
 * @property-read LpuStructure_model $LpuStructure_model
 * @property-read HomeVisit_model $HomeVisit_model
 * @property CureStandart_model $CureStandart_model
 * @property Kz_UslugaMedType_model $UslugaMedType_model
 */
class EvnVizitPL_model extends EvnVizitAbstract_model
{
	protected $_parentClass = 'EvnPL_model';
	/**
	 * @var string код места посещения
	 */
	private $_serviceTypeSysNick = null;
	/**
	 * @var string код цели посещения
	 */
	private $_vizitTypeSysNick = null;
	/**
	 * @var array Данные отделения
	 */
	private $_LpuSectionData = array();
	/**
	 * @var int Код
	 */
	private $_lpuUnitSetCode = null;
	/**
	 * @var string Код посещения
	 */
	private $_vizitCode = null;
	/**
	 * @var array Список услуг посещения, в т.ч. с кодом посещения
	 */
	private $_evnUslugaList = null;
	/**
	 * @var array Список исключений из номеров групп
	 */
	protected $_groupNumExceptions = array();
	/**
	 * @var int Счетчик номеров групп посещений
	 */
	protected $_groupNum = 0;
	// дубли
	protected $_doubles = array();
	// последние 3 цифры кодов однократного профилактического посещения
	static public $profOneVizitCodePartList = array('805', '811', '872', '890', '891', '892', '816', '817', '907', '908');
	// последние 3 цифры кодов однократного посещения по неотложке
	static public $citoOneVizitCodePartList = array('824', '825');
	// последние 3 цифры кодов однократного посещения по заболеванию
	static public $morbusOneVizitCodePartList = array('871');
	// последние 3 цифры кодов многократного посещения по заболеванию
	static public $morbusMultyVizitCodePartList = array('836', '865', '866', '888', '889');
	
	public $ignoreCheckMorbusOnko = null;
	/**
	 * @return array
	 */
	static public function oneVizitCodePartList()
	{
		return array_merge(self::$profOneVizitCodePartList,
			self::$citoOneVizitCodePartList,
			self::$morbusOneVizitCodePartList
		);
	}
	/**
	 * @param $code
	 * @return string
	 */
	static public function vizitCodePart($code)
	{
		return substr($code, strlen($code) - 3, 3);
	}
    /**
     * Конструктор объекта
     */
    function __construct()
    {
        parent::__construct();
	    $this->_setScenarioList(array(
		    self::SCENARIO_AUTO_CREATE,
		    self::SCENARIO_DO_SAVE,
		    self::SCENARIO_SET_ATTRIBUTE,
			self::SCENARIO_DELETE,
	    ));
    }

	/**
	 * Возвращает список всех используемых ключей атрибутов объекта
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();
		$arr[self::ID_KEY]['alias'] = 'EvnVizitPL_id';
		$arr['pid']['alias'] = 'EvnPL_id';
		$arr['setdate']['alias'] = 'EvnVizitPL_setDate';
		$arr['settime']['alias'] = 'EvnVizitPL_setTime';
		$arr['disdt']['alias'] = 'EvnVizitPL_disDT';
		$arr['diddt']['alias'] = 'EvnVizitPL_didDT';
		$arr['statusdate']['alias'] = 'EvnVizitPL_statusDate';
		$arr['isinreg']['alias'] = 'EvnVizitPL_IsInReg';
		$arr['ispaid']['alias'] = 'EvnVizitPL_IsPaid';
		$arr['uet']['alias'] = 'EvnVizitPL_Uet';
		$arr['uetoms']['alias'] = 'EvnVizitPL_UetOMS';
		$arr['healthkind_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'HealthKind_id',
			'label' => 'Группа здоровья',
			'save' => 'trim',
			'type' => 'id',
			'updateTable' => 'EvnVizitPL'
		);
		$arr['risklevel_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'RiskLevel_id',
			'label' => 'Фактор Риска',
			'save' => 'trim',
			'type' => 'id',
			'updateTable' => 'EvnVizitPL'
		);
		$arr['wellnesscenteragegroups_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'WellnessCenterAgeGroups_id',
			'label' => 'Группа ЦЗ',
			'save' => 'trim',
			'type' => 'id',
			'updateTable' => 'EvnVizitPL'
		);
		$arr['homevisit_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'HomeVisit_id',
			'label' => 'Идентификатор вызова на дом',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['evnprescr_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPrescr_id',
			'label' => 'Связанное назначение',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['dispclass_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'DispClass_id',
			'label' => 'Тип диспансеризации/мед. осмотра',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['dispprofgoaltype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'DispProfGoalType_id',
			'label' => 'Тип диспансеризации/мед. осмотра',
			'save' => 'trim',
			'type' => 'id',
			'updateTable' => 'EvnVizitPL'
		);
		$arr['evnpldisp_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDisp_id',
			'label' => 'Карта диспансеризации/мед. осмотра',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['persondisp_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'PersonDisp_id',
			'label' => 'Карта дисп. учёта',
			'save' => 'trim',
			'type' => 'id',
			'updateTable' => 'EvnVizitPL'
		);
		$arr['lpusectionprofile_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'LpuSectionProfile_id',
			'label' => 'Профиль посещения',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['treatmentclass_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'TreatmentClass_id',
			'label' => 'Вид обращения',
			'save' => '',
			'type' => 'id',
			'updateTable' => 'EvnVizitPL'
		);
		$arr['vizitpldouble_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_NOT_SAFE,
			),
			'alias' => 'VizitPLDouble_id',
			'label' => 'Признак включения дубля в реестр',
			'save' => '',
			'type' => 'id'
		);
		$arr['isotherdouble'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnVizitPL_IsOtherDouble',
			'label' => 'Признак дубля посещения в другом ТАП',
			'save' => '',
			'type' => 'id'
		);
		$arr['servicetype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'ServiceType_id',
			'label' => 'Место',
			'save' => 'required',
			'type' => 'id',
			'updateTable' => 'EvnVizitPL'
		);
		$arr['vizittype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'VizitType_id',
			'label' => 'Цель посещения',
			'save' => 'trim|required',
			'type' => 'id',
			'updateTable' => 'EvnVizitPL'
		);
		$arr['vizitclass_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'VizitClass_id',
			'label' => 'Первично/повторно',
			'save' => 'trim',
			'type' => 'id',
			'updateTable' => 'EvnVizitPL'
		);
		$arr['profgoal_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'ProfGoal_id',
			'label' => 'Цель профосмотра',
			'save' => 'trim',
			'type' => 'id',
			'updateTable' => 'EvnVizitPL'
		);
		$arr['diag_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Diag_id',
			'label' => 'Основной диагноз',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['deseasetype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'DeseaseType_id',
			'label' => 'Характер заболевания',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['tumorstage_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'TumorStage_id',
			'label' => 'Стадия выявленного ЗНО',
			'save' => 'trim',
			'type' => 'id',
			'updateTable' => 'EvnVizitPL'
		);
		$arr['diag_agid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Diag_agid',
			'label' => 'Осложнение основного диагноза',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['time'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnVizitPL_Time',
			'label' => 'Время приема',
			'save' => 'trim',
			'type' => 'int'
		);
		$arr['uslugacomplex_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'UslugaComplex_uid',
			'label' => 'Код посещения',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['lpudispcontract_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'LpuDispContract_id',
			'label' => 'По договору',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['rankinscale_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'RankinScale_id',
			'label' => 'Значение по шкале Рэнкина',
			'save' => 'trim',
			'type' => 'id',
			'updateTable' => 'EvnVizitPL'
		);
		$arr['medicalcarekind_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'MedicalCareKind_id',
			'label' => 'Вид мед. помощи',
			'save' => '',
			'type' => 'id',
			'updateTable' => 'EvnVizitPL'
		);
		$arr['isprimaryvizit'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_NOT_LOAD,
				self::PROPERTY_NOT_SAFE,
			),
			'alias' => 'EvnVizitPL_IsPrimaryVizit',
			'label' => 'Признак первичного посещения в текущем году',
			'save' => 'trim',
			'type' => 'id'
		);
		/*$arr['evnuslugavizit_id'] = array(
			'properties' => array(
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
			),
			'alias' => 'EvnUslugaCommon_id',
			'select' => 'v_EvnUsluga.EvnUsluga_id as EvnUslugaVizit_id',
			'join' => 'left join v_EvnUsluga with (nolock) on v_EvnUsluga.EvnUsluga_pid = {ViewName}.{PrimaryKey}
				and ISNULL(v_EvnUsluga.EvnUsluga_IsVizitCode, 1) = 2',
		);*/
		$arr['iszno'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnVizitPL_IsZNO',
			'label' => 'Подозрение на ЗНО',
			'save' => '',
			'type' => 'id',
			'updateTable' => 'EvnVizitPL'
		);
		$arr['isznoremove'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnVizitPL_IsZNORemove',
			'label' => 'Снятие признака подозрения на ЗНО',
			'save' => '',
			'type' => 'id',
			'updateTable' => 'EvnVizitPL'
		);
		$arr['biopsydate'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnVizitPL_BiopsyDate',
			'label' => 'Дата взятия биопсии',
			'save' => 'trim',
			'type' => 'date',
			'updateTable' => 'EvnVizitPL'
		);
		$arr['painintensity_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'PainIntensity_id',
			'label' => 'Интенсивность боли',
			'save' => '',
			'type' => 'id',
			'updateTable' => 'EvnVizitPL'
		);
		$arr['diag_spid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Diag_spid',
			'label' => 'Подозрение на диагноз',
			'save' => '',
			'type' => 'id',
			'updateTable' => 'EvnVizitPL'
		);
		$arr['numgroup'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnVizitPL_NumGroup',
			'label' => 'Номер группы',
			'save' => '',
			'type' => 'int',
			'updateTable' => 'EvnVizitPL'
		);
		return $arr;
	}

	
	
	/**
	 * Определение идентификатора класса события
	 * @return int
	 */
	static function evnClassId()
	{
		return 11;
	}

	/**
	 * Определение кода класса события
	 * @return string
	 */
	static function evnClassSysNick()
	{
		return 'EvnVizitPL';
	}

	/**
	 * Правила для контроллера для извлечения входящих параметров при сохранении
	 * @return array
	 */
	protected function _getSaveInputRules()
	{
		$all = parent::_getSaveInputRules();
		// параметры
		$all['from'] = array(
			'field' => 'from',
			'label' => 'Откула',
			'rules' => '',
			'type' => 'string'
		);
		$all['isMyOwnRecord'] = array(
			'field' => 'isMyOwnRecord',
			'label' => 'Флаг собственной записи',
			'rules' => '',
			'type' => 'string'
		);
		$all['streamInput'] = array(
			'field' => 'streamInput',
			'label' => 'Признак поточного ввода',
			'rules' => '',
			'type' => 'int'
		);
		$all['ignoreEvnUslugaCountCheck'] = array(
			'field' => 'ignoreEvnUslugaCountCheck',
			'label' => 'Признак игнорирования количества услуг',
			'rules' => '',
			'type' => 'int'
		);
		$all['ignoreDayProfileDuplicateVizit'] = array(
			'field' => 'ignoreDayProfileDuplicateVizit',
			'label' => 'Признак игнорирования дублей посещений по профилю',
			'rules' => '',
			'type' => 'int'
		);
		$all['ignoreMesUslugaCheck'] = array(
			'field' => 'ignoreMesUslugaCheck',
			'label' => 'Признак игнорирования проверки МЭС',
			'rules' => '',
			'type' => 'int'
		);
		$all['ignoreControl59536'] = array(
			'field' => 'ignoreControl59536',
			'label' => 'Признак игнорирования проверки по задаче 59536',
			'rules' => '',
			'type' => 'int'
		);
		$all['ignoreNoExecPrescr'] = array(
			'field' => 'ignoreNoExecPrescr',
			'label' => 'Признак игнорирования неисполненных/неотмененных назначений в случае АПЛ',
			'rules' => '',
			'type' => 'int'
		);
		$all['ignoreControl122430'] = array(
			'field' => 'ignoreControl122430',
			'label' => 'Признак игнорирования проверки по задаче 122430',
			'rules' => '',
			'type' => 'int'
		);
		$all['ignoreEvnDirectionProfile'] = array(
			'field' => 'ignoreEvnDirectionProfile',
			'label' => 'Признак игнорирования проверки соответсвия профиля направления профилю посещения',
			'rules' => '',
			'type' => 'int'
		);
		$all['ignoreCheckMorbusOnko'] = array(
			'field' => 'ignoreCheckMorbusOnko',
			'label' => 'Признак игнорирования проверки перед удалением специфики',
			'rules' => '',
			'type' => 'int'
		);
		$all['allowCreateEmptyEvnDoc'] = array(
			'field' => 'allowCreateEmptyEvnDoc',
			'label' => 'Признак необходимости создания протокола осмотра с данными по умолчанию',
			'rules' => '',
			'type' => 'int'
		);
		$all['copyEvnXml_id'] = array(
			'field' => 'copyEvnXml_id',
			'label' => 'Признак необходимости копирования указанного протокола осмотра',
			'rules' => '',
			'type' => 'int'
		);
		$all['aborttype_id'] = array(
			'field' => 'AbortType_id',
			'label' => 'AbortType_id',
			'rules' => 'trim',
			'type' => 'id'
		);
		$all['morbuspregnancy_id'] = array(
			'field' => 'MorbusPregnancy_id',
			'label' => 'MorbusPregnancy_id',
			'rules' => 'trim',
			'type' => 'id'
		);
		$all['morbus_id'] = array(
			'field' => 'Morbus_id',
			'label' => 'morbus_id',
			'rules' => 'trim',
			'type' => 'id'
		);
		$all['diag_id'] = array(
			'field' => 'Diag_id',
			'label' => 'Diag_id',
			'rules' => 'trim',
			'type' => 'id'
		);
		$all['birthresult_id'] = array(
			'field' => 'BirthResult_id',
			'label' => 'BirthResult_id',
			'rules' => 'trim',
			'type' => 'id'
		);
		$all['morbuspregnancy_ishiv'] = array(
			'field' => 'MorbusPregnancy_IsHIV',
			'label' => 'MorbusPregnancy_IsHIV',
			'rules' => 'trim',
			'type' => 'id'
		);
		$all['morbuspregnancy_ishivtest'] = array(
			'field' => 'MorbusPregnancy_IsHIVtest',
			'label' => 'MorbusPregnancy_IsHIVtest',
			'rules' => 'trim',
			'type' => 'id'
		);
		$all['morbuspregnancy_ismedicalabort'] = array(
			'field' => 'MorbusPregnancy_IsMedicalAbort',
			'label' => 'MorbusPregnancy_IsMedicalAbort',
			'rules' => 'trim',
			'type' => 'id'
		);
		$all['morbuspregnancypresent'] = array(
			'field' => 'MorbusPregnancyPresent',
			'label' => 'MorbusPregnancyPresent',
			'rules' => 'trim',
			'type' => 'id'
		);
		$all['morbuspregnancy_outcomperiod'] = array(
			'field' => 'MorbusPregnancy_OutcomPeriod',
			'label' => 'MorbusPregnancy_OutcomPeriod',
			'rules' => 'trim',
			'type' => 'int'
		);
		$all['morbuspregnancy_bloodloss'] = array(
			'field' => 'MorbusPregnancy_BloodLoss',
			'label' => 'MorbusPregnancy_BloodLoss',
			'rules' => 'trim',
			'type' => 'int'
		);
		$all['morbuspregnancy_countpreg'] = array(
			'field' => 'MorbusPregnancy_CountPreg',
			'label' => 'MorbusPregnancy_CountPreg',
			'rules' => 'trim',
			'type' => 'int'
		);
		
		$all['morbuspregnancy_outcomd'] = array(
			'field' => 'MorbusPregnancy_OutcomD',
			'label' => 'MorbusPregnancy_OutcomD',
			'rules' => 'trim',
			'type' => 'date'
		);
		$all['morbuspregnancy_outcomt'] = array(
			'field' => 'MorbusPregnancy_OutcomT',
			'label' => 'MorbusPregnancy_OutcomT',
			'rules' => 'trim',
			'type' => 'string'
		);
		$all['isFinish'] = array(
			'field' => 'EvnPL_IsFinish',
			'label' => 'EvnPL_IsFinish',
			'rules' => 'trim',
			'type' => 'id'
		);
		$all['PregnancyEvnVizitPL_Period'] = array(
			'field' => 'PregnancyEvnVizitPL_Period',
			'label' => 'Срок беременности',
			'rules' => '',
			'type' => 'int',
			'updateTable' => 'EvnVizitPL'
		);
		$all['vizit_kvs_control_check'] = array(
			'field' => 'vizit_kvs_control_check',
			'label' => 'Признак ',
			'rules' => '',
			'type' => 'int'
		);
		$all['vizit_intersection_control_check'] = array(
			'field' => 'vizit_intersection_control_check',
			'label' => 'Признак ',
			'rules' => '',
			'type' => 'int'
		);
		$all['ignoreLpuSectionProfileVolume'] = array(
			'field' => 'ignoreLpuSectionProfileVolume',
			'label' => 'Признак ',
			'rules' => '',
			'type' => 'int'
		);
		$all['ignoreDiagDispCheck'] = array(
			'field' => 'ignoreDiagDispCheck',
			'label' => 'Признак игнорирования проверки наличи карты диспансеризации при диагнозе из определенной группы',
			'rules' => '',
			'type' => 'int'
		);
		$all['ignoreCheckEvnUslugaChange'] = array(
			'field' => 'ignoreCheckEvnUslugaChange',
			'label' => 'Признак игнорирования проверки изменения привязок услуг',
			'rules' => '',
			'type' => 'int'
		);
		$all['ignoreCheckB04069333'] = array(
			'field' => 'ignoreCheckB04069333',
			'label' => 'Признак игнорирования проверки наличия услуги B04.069.333',
			'rules' => '',
			'type' => 'int'
		);
		$all['EvnVizitPLDoublesData'] = array(
			'field' => 'EvnVizitPLDoublesData',
			'label' => 'EvnVizitPLDoublesData',
			'rules' => '',
			'type' => 'json_array',
			'assoc' => true
		);
		$all['addB04069333'] = array(
			'field' => 'addB04069333',
			'label' => 'Признак необходимости добавить услугу B04.069.333',
			'rules' => '',
			'type' => 'int'
		);
		$all['DrugTherapyScheme_ids'] = array(
			'field' => 'DrugTherapyScheme_ids',
			'label' => 'Схема лекарственной терапии',
			'rules' => '',
			'type' => 'multipleid'
		);
        $all['UslugaMedType_id'] = [
            'field' => 'UslugaMedType_id',
            'label' => 'Вид услуги',
            'rules' => '',
            'type' => 'int'
        ];
        $all['PayTypeKAZ_id'] = [
            'field' => 'PayTypeKAZ_id',
            'label' => 'Тип оплаты',
            'rules' => '',
            'type' => 'id'
        ];
        $all['ScreenType_id'] = [
            'field' => 'ScreenType_id',
            'label' => 'Вид скрининга',
            'rules' => '',
            'type' => 'id'
        ];
		$all['RepositoryObservData'] = [
			'field' => 'RepositoryObservData',
			'label' => 'Анкета',
			'rules' => '',
			'type' => 'json_array',
			'assoc' => true
		];
		$all['VizitActiveType_id'] = [
			'field' => 'VizitActiveType_id',
			'label' => 'Вид активного посещения',
			'rules' => '',
			'type' => 'id'
		];

		return $all;
	}

	/**
	 * Извлечение значений параметров модели из входящих параметров,
	 * переданных из контроллера
	 * @param array $data
	 * @throws Exception
	 */
	function setParams($data)
	{
		parent::setParams($data);
		$this->_params['ignoreMesUslugaCheck'] = empty($data['ignoreMesUslugaCheck']) ? false : true;
		$this->_params['ignoreControl59536'] = empty($data['ignoreControl59536']) ? false : true;
		$this->_params['ignoreNoExecPrescr'] = empty($data['ignoreNoExecPrescr']) ? false : true;
		$this->_params['ignoreControl122430'] = empty($data['ignoreControl122430']) ? false : true;
		$this->_params['ignoreCheckMorbusOnko'] = empty($data['ignoreCheckMorbusOnko']) ? false : true;
		$this->_params['ignoreEvnDirectionProfile'] = empty($data['ignoreEvnDirectionProfile']) ? false : true;
		$this->_params['ignoreCheckEvnUslugaChange'] = !isset($data['ignoreCheckEvnUslugaChange']) ? null : $data['ignoreCheckEvnUslugaChange'];
		$this->_params['allowCreateEmptyEvnDoc'] = empty($data['allowCreateEmptyEvnDoc']) ? false : true;
		$this->_params['ignoreDiagDispCheck'] = empty($data['ignoreDiagDispCheck']) ? false : true;
		$this->_params['copyEvnXml_id'] = empty($data['copyEvnXml_id']) ? null : $data['copyEvnXml_id'];
		$this->_params['isMyOwnRecord'] = empty($data['isMyOwnRecord']) ? null : $data['isMyOwnRecord'];
		$this->_params['IsFinish'] = empty($data['EvnPL_IsFinish']) ? null : $data['EvnPL_IsFinish'];
		$this->_params['PregnancyEvnVizitPL_Period'] = empty($data['PregnancyEvnVizitPL_Period']) ? null : $data['PregnancyEvnVizitPL_Period'];
		$this->_params['vizit_kvs_control_check'] = empty($data['vizit_kvs_control_check']) ? false : true;
		$this->_params['ignore_vizit_kvs_control'] = empty($data['ignore_vizit_kvs_control']) ? false : true;
		$this->_params['vizit_intersection_control_check'] = empty($data['vizit_intersection_control_check']) ? false : true;
		$this->_params['ignore_vizit_intersection_control'] = empty($data['ignore_vizit_intersection_control']) ? false : true;
		$this->_params['ignoreLpuSectionProfileVolume'] = empty($data['ignoreLpuSectionProfileVolume']) ? false : true;
		$this->_params['ignoreDayProfileDuplicateVizit'] = empty($data['ignoreDayProfileDuplicateVizit']) ? false : true;
		$this->_params['ignoreCheckB04069333'] = empty($data['ignoreCheckB04069333']) ? false : true;
		$this->_params['addB04069333'] = empty($data['addB04069333']) ? false : true;
		$this->_params['streamInput'] = empty($data['streamInput']) ? false : true;
		$this->_params['copyEvnDiagPLStom'] = empty($data['copyEvnDiagPLStom']) ? false : true;
		$this->_params['EvnVizitPLDoublesData'] = !empty($data['EvnVizitPLDoublesData']) ? $data['EvnVizitPLDoublesData'] : null;
		$this->_params['DrugTherapyScheme_ids'] = !isset($data['DrugTherapyScheme_ids']) ? null : $data['DrugTherapyScheme_ids'];
        $this->_params['UslugaMedType_id'] = isset($data['UslugaMedType_id']) ? $data['UslugaMedType_id'] : null;
        $this->_params['PayTypeKAZ_id'] = isset($data['PayTypeKAZ_id']) ? $data['PayTypeKAZ_id'] : null;
        $this->_params['ScreenType_id'] = isset($data['ScreenType_id']) ? $data['ScreenType_id'] : null;
		$this->_params['RepositoryObservData'] = $data['RepositoryObservData'] ?? null;
		$this->_params['VizitActiveType_id'] = isset($data['VizitActiveType_id']) ? $data['VizitActiveType_id'] : null;
	}

	/**
	 * Сохранение фактического времени посещения
	 * @throws Exception
	 */
	protected function _saveVizitFactTime()
	{
		$this->load->model('TimetableGraf_model');
		$ttgdata = $this->TimetableGraf_model->onBeforeSaveEvnVizit($this);
		$this->setAttribute('TimetableGraf_id', $ttgdata['TimetableGraf_id']);
		$this->setAttribute('EvnDirection_id', $ttgdata['EvnDirection_id']);
		if ($this->isNewRecord && empty($this->TimetableGraf_id)) {
			throw new Exception('Ошибка при сохранении фактического времени приема!', 500);
		}
		if (false == $this->isNewRecord && !empty($this->_savedData['evndirection_id']) && $this->_isAttributeChanged('EvnDirection_id')) {
			throw new Exception('Нельзя изменить направление', 500);
		}
		if (false == $this->isNewRecord && $this->_isAttributeChanged('TimetableGraf_id')) {
			// бирка может измениться, если была изменена дата посещения
			//throw new Exception('Нельзя изменить бирку', 500);
		}
	}
	/**
	 * Проверка корректности данных модели для указанного сценария
	 * @throws Exception
	 */
	protected function _validate()
	{
		parent::_validate();
		// общие проверки выполнены

		if (in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_AUTO_CREATE))) {
			if ($this->_params['streamInput'] == false && $this->isNewRecord && !$this->parent->isNewRecord && $this->parent->_savedData[$this->parent->_getColumnName('isfinish')] == 2 && $this->parent->IsFinish == 2) {
				throw new Exception('Талон закрыт - добавление посещения невозможно.', 400);
			}
			if (empty($this->PayType_id) && getRegionNick()!='kz') {
				throw new Exception('Не указан вид оплаты', 400);
			}
			if (empty($this->MedPersonal_id)) {
				throw new Exception('Не указан врач', 400);
			}
			// проверки возможности изменения отдельных атрибутов
			$this->_checkPerson();
			$this->_checkChangeMedStaffFact();
			$this->_checkChangeLpuSection();
			$this->_checkChangeLpuSectionProfileId();
			$this->_checkChangeDiag();
			$this->_checkChangeVizitType();
			$this->_checkChangeServiceType();
			$this->_checkDiagDispCard();
			$this->_checkChangeEvnUsluga();
			$this->_checkEvnDirectionProfile();
			if(!$this->_params['copyEvnDiagPLStom']){
				$this->_checkChangeVizitCode();
			}
			$this->_checkChangeMes();
			$this->_checkChangeSetDate();
			//Проверка двойственности посещений пациентов
			$this->_controlDoubleVizit();
			//Проверка на второе посещение НМП
			$this->_controlDoubleNMP($this->parent->id, $this->id, strpos($this->evnClassSysNick(), 'Stom') != 0);
		}

		if (self::SCENARIO_DELETE == $this->scenario) {
			if ($this->parent->IsFinish == 2) {
				throw new Exception('Талон закрыт - удаление посещения невозможно.', 400);
			}

			if ($this->regionNick != 'perm' || $this->evnClassId != 13) { // для новой стоматки Перми данную проверку убрал, т.к. в ней нет основного диагноза (диагноз указывается в заболеваниях).
				$hasOtherVizitWithDiag = false;
				foreach ($this->parent->evnVizitList as $id => $row) {
					if ($id != $this->id && false == empty($row['Diag_id'])) {
						$hasOtherVizitWithDiag = true;
					}
				}
				if (count($this->parent->evnVizitList) > 1 && $this->parent->Diag_id > 0 && false == $hasOtherVizitWithDiag) {
					throw new Exception('Удаление невозможно. Случай лечения должен содержать хотя бы одно посещение с указанным основным диагнозом.');
				}
			}
		}
		
		if (!empty($this->id) && $this->scenario == self::SCENARIO_DO_SAVE && $this->evnClassId == 11) {
			$cnt = $this->getFirstResultFromQuery("
				select count(*) cnt
				from v_EvnDiag (nolock) 
				where EvnDiag_pid = :id and Diag_id = :Diag_id
			", [
				'id' => $this->id,
				'Diag_id' => $this->Diag_id
			]);
			if ($cnt > 0) {
				throw new Exception('Сопутствующий диагноз не должен совпадать с основным. Пожалуйста, проверьте корректность выбора основного и сопутствующих диагнозов');
			}
		}
	}

	/**
	 * Проверки услуг посещений
	 * @throws Exception
	 */
	function _checkChangeEvnUsluga()
	{
		// проверка наличия стомат.услуг при сохранении стомат.посещения из формы редактирования #40490
		if ( $this->regionNick == 'ufa' && !$this->isNewRecord
			&& $this->scenario == self::SCENARIO_DO_SAVE
			&& $this->evnClassId == 13
		) {
			$isEmptyEvnUslugaList = empty($this->evnUslugaList);
			// исключаем код посещения
			foreach ($this->evnUslugaList as $row) {
				if (1 == $row['EvnUsluga_IsVizitCode']) {
					$isEmptyEvnUslugaList = false;
					break;
				}
			}
			if ($isEmptyEvnUslugaList) {
				throw new Exception('Не введено ни одной услуги. Сохранение посещения невозможно');
			}
		}

		/*if ( $this->regionNick == 'perm'
			&& in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_SET_ATTRIBUTE))
			&& $this->evnClassId == 11
		) {
			$service_type = $this->getFirstRowFromQuery("
				select top 1
					ServiceType_SysNick,
					ServiceType_Name
				from v_ServiceType with(nolock)
				where ServiceType_id = :ServiceType_id
			", array('ServiceType_id' => $this->ServiceType_id));
			if (!$service_type) {
				throw new Exception('Ошибка при запросе данных для типа обслуживания');
			}
			$ServiceType_SysNick = $service_type['ServiceType_SysNick'];
			$ServiceType_Name = $service_type['ServiceType_Name'];

			foreach($this->evnUslugaList as $row) {
				if (
					$row['UslugaComplex_Code'] == 'B04.069.333'
					&& strtotime($this->setDate) >= strtotime('01.01.2015')
					&& in_array($ServiceType_SysNick, array('home', 'ahome', 'neotl'))
				) {
					throw new Exception("Невозможно сохранить изменения. Услуга «B04.069.333» не может быть указана для выбранного места оказания «{$ServiceType_Name}»");
				}
			}
		}*/

		if ( $this->regionNick == 'perm' && empty($this->_params['ignoreControl59536'])
			&& in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_SET_ATTRIBUTE))
			&& $this->evnClassId == 11
			&& $this->payTypeSysNick == 'oms'
			&& $this->person_Age < 18
		) {
			// проверяем по услугам в рамках посещения
			$uslugaComplexCodeList = array();
			foreach($this->evnUslugaList as $row) {
				$uslugaComplexCodeList[] = $row['UslugaComplex_Code'];
			}
			if (in_array('B01.003.004.099', $uslugaComplexCodeList)
				&& !(in_array('A06.30.003.001', $uslugaComplexCodeList) || in_array('A05.30.003', $uslugaComplexCodeList))
			) {
				$this->_saveResponse['Alert_Msg'] = "
					Случай не будет оплачен, так как услуга  B01.003.004.099 Анестезиологическое пособие оплачивается для детей
					только при наличии услуги A06.30.003.001 Проведение компьютерных томографических исследований
					или A05.30.003 Проведение магнитно-резонансных томографических исследований.  Продолжить сохранение?";
				//отменяем сохранение, пользователю показываем Alert_Msg и выводим вопрос: Продолжить сохранение?
				throw new Exception('YesNo', 103);
			}
		}

		if ( $this->regionNick == 'penza' && empty($this->_params['ignoreControl122430'])
			&& in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_SET_ATTRIBUTE))
			&& $this->evnClassId == 11
			&& $this->payTypeSysNick == 'oms'
			&& isset($this->lpuSectionData['LpuUnitType_SysNick']) && $this->lpuSectionData['LpuUnitType_SysNick'] == 'fap'
		) {
			// и в посещении не заведена услуга с атрибутом «Услуга ФАП», то выходит предупреждение «Посещение ФАП без услуги ФАП не будет оплачено»
			$hasFapUslugaComplex = false;
			if (!empty($this->UslugaComplex_id)) {
				// проверяем услугу кода посещения
				$resp_eu = $this->queryResult("
					select top 1
						uc.UslugaComplex_id
					from
						v_UslugaComplex uc (nolock)
					where
						uc.UslugaComplex_id = :UslugaComplex_id
						and exists (
							select top 1
								t1.UslugaComplexAttribute_id
							from
								UslugaComplexAttribute t1 with (nolock)
								inner join UslugaComplexAttributeType t2 with (nolock) on t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
							where
								t1.UslugaComplex_id = uc.UslugaComplex_id
								and t2.UslugaComplexAttributeType_SysNick in ('code_usl')
						)
				", array(
					'UslugaComplex_id' => $this->UslugaComplex_id
				));

				if (!empty($resp_eu[0]['UslugaComplex_id'])) {
					$hasFapUslugaComplex = true;
				}
			}
			if (!$hasFapUslugaComplex && !empty($this->id)) {
				// проверяем дополнительные услуги
				$resp_eu = $this->queryResult("
					select top 1
						eu.EvnUsluga_id
					from
						v_EvnUsluga eu (nolock)
					where
						eu.EvnUsluga_pid = :EvnUsluga_pid
						and exists (
							select top 1
								t1.UslugaComplexAttribute_id
							from
								UslugaComplexAttribute t1 with (nolock)
								inner join UslugaComplexAttributeType t2 with (nolock) on t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
							where
								t1.UslugaComplex_id = EU.UslugaComplex_id
								and t2.UslugaComplexAttributeType_SysNick in ('code_usl')
						)
						and ISNULL(eu.EvnUsluga_IsVizitCode, 1) = 1
				", array(
					'EvnUsluga_pid' => $this->id
				));

				if (!empty($resp_eu[0]['EvnUsluga_id'])) {
					$hasFapUslugaComplex = true;
				}
			}
			if (!$hasFapUslugaComplex) {
				$this->_saveResponse['ignoreParam'] = "ignoreControl122430";
				$this->_saveResponse['Alert_Msg'] = "Посещение ФАП без услуги ФАП не будет оплачено.  Продолжить сохранение?";
				//отменяем сохранение, пользователю показываем Alert_Msg и выводим вопрос: Продолжить сохранение?
				throw new Exception('YesNo', 105);
			}
		}

		if ( $this->regionNick == 'ekb'
			&& in_array($this->scenario, array(self::SCENARIO_DO_SAVE))
			&& $this->evnClassId == 11
			&& !empty($this->UslugaComplex_id)
		) {

			$Mes_code_query = "
			select 
				Mes_Code 
			from 
				v_MesOld
			where 
				Mes_id=:Mes_id
			";
			$Mes_code = $this->getFirstResultFromQuery($Mes_code_query,
				array("Mes_id" => $this->Mes_id));

			if (in_array($Mes_code,array(1703,1704))) {
				return;
			}
			$ServiceType_SysNick = $this->getFirstResultFromQuery("
				select top 1 ServiceType_SysNick
				from v_ServiceType with(nolock)
				where ServiceType_id = :ServiceType_id
			", array('ServiceType_id' => $this->ServiceType_id));
			if (!$ServiceType_SysNick) {
				throw new Exception('Ошибка при запросе данных для типа обслуживания');
			}

			if (in_array($ServiceType_SysNick, array('home','ahome','neotl'))) {
				$vizitUslugaComplexPartition_Code = null;
				$flag = false;
				foreach($this->evnUslugaList as $row) {
					if ($row['EvnUsluga_IsVizitCode'] != 2 && $row['UslugaComplex_Code'] == 'B04.069.333') {
						$flag = true;
					}
				}

				$vizitUslugaComplexPartition_Code = $this->getFirstResultFromQuery("
					declare
						@Sex_id bigint,
						@MedSpecOms_id bigint

					select top 1
						@Sex_id = P.Sex_id
					from v_Person_all P with(nolock)
					where P.PersonEvn_id = :PersonEvn_id and P.Server_id = :Server_id

					select top 1
						@MedSpecOms_id = MSF.MedSpecOms_id
					from v_MedStaffFact MSF with(nolock)
					where MSF.MedStaffFact_id = :MedStaffFact_id

					select top 1 UCP.UslugaComplexPartition_Code
					from r66.v_UslugaComplexPartitionLink UCPL with(nolock)
					inner join r66.v_UslugaComplexPartition UCP with(nolock) on UCP.UslugaComplexPartition_id = UCPL.UslugaComplexPartition_id
					where
						UCPL.UslugaComplex_id = :UslugaComplex_id
						and (UCPL.Sex_id is null or UCPL.Sex_id = @Sex_id)
						and (UCPL.MedSpecOms_id is null or UCPL.MedSpecOms_id = @MedSpecOms_id)
						and (UCPL.LpuSectionProfile_id is null or UCPL.LpuSectionProfile_id = :LpuSectionProfile_id)
						and UCPL.PayType_id = :PayType_id
						and ISNULL(UCPL.UslugaComplexPartitionLink_IsMes, 1) = :IsMes
				", array(
					'UslugaComplex_id' => $this->UslugaComplex_id,
					'PersonEvn_id' => $this->PersonEvn_id,
					'Server_id' => $this->Server_id,
					'MedStaffFact_id' => $this->MedStaffFact_id,
					'LpuSectionProfile_id' => $this->LpuSectionProfile_id,
					'PayType_id' => $this->PayType_id,
					'IsMes' => empty($this->Mes_id) ? 1 : 2
				));

				if ($vizitUslugaComplexPartition_Code == '300' && !$flag) {
					throw new Exception('В посещении с местом обслуживания «2. На дому» , «3. На дому: Актив» либо «4. На дому: НМП» не заведена услуга B04.069.333 «Оказание медицинской помощи вне медицинской организации (на дому)»');
				}
			}
		}
	}

	/**
	 * Проверка соответсвия профиля направления профилю посещения
	 */
	function _checkEvnDirectionProfile()
	{
		if ( empty($this->_params['ignoreEvnDirectionProfile'])
			&& in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_SET_ATTRIBUTE))
			&& $this->evnClassId == 11
		) {
			// Если в первом посещении ТАП профиль отделения, указанного в основном разделе, отличается от профиля электронного направления, выбранного в ТАП, то:
			$first = true;
			$vizitList = $this->parent->evnVizitList;
			if (!empty($this->setDT)) {
				foreach ($vizitList as $vizit) {
					if (
						$vizit['EvnVizitPL_id'] != $this->id
						&& !empty($vizit['EvnVizitPL_setDate'])
						&& strtotime($vizit['EvnVizitPL_setDate'] . ' ' . $vizit['EvnVizitPL_setTime']) < strtotime($this->setDT->format('Y-m-d H:i'))
					) {
						$first = false;
					}
				}
			}

			if ($first && !empty($this->LpuSectionProfile_id) && !empty($this->parent->EvnDirection_id)) {
				// получаем профиль
				$LpuSectionProfile_id = $this->getFirstResultFromQuery("select LpuSectionProfile_id from v_EvnDirection (nolock) where EvnDirection_id = :EvnDirection_id", array(
					'EvnDirection_id' => $this->parent->EvnDirection_id
				));

				if (!empty($LpuSectionProfile_id) && $LpuSectionProfile_id != $this->LpuSectionProfile_id) {
					$deny = false; // Предупреждение
					if (!empty($this->globalOptions['globals']['evndirection_check_profile']) && $this->globalOptions['globals']['evndirection_check_profile'] == 2) {
						$deny = true; // Ошибка
					}
					if (!$deny) {
						$this->_saveResponse['ignoreParam'] = "ignoreEvnDirectionProfile";
						$this->_saveResponse['Alert_Msg'] = "Профиль отделения первого посещения не совпадает с профилем выбранного электронного направления. Продолжить сохранение?";
						//отменяем сохранение, пользователю показываем Alert_Msg и выводим вопрос: Продолжить сохранение?
						throw new Exception('YesNo', 104);
					} else {
						throw new Exception('Необходимо совпадение профиля отделения в первом посещении с профилем выбранного электронного направления.', 400);
					}
				}
			}
		}
	}

	/**
	 * Проверки даты посещения
	 * @throws Exception
	 */
	function _checkChangeSetDate()
	{
		if (empty($this->setDT)) {
			throw new Exception('Дата и время посещения обязательны для заполнения', 400);
		}
		// проверка наличия ссылок в событиях (кроме услуг) на редактируемое стомат.посещение при изменении даты посещения
		if ( !$this->isNewRecord
			&& $this->evnClassId == 13
			&& $this->setDate != $this->_savedData[$this->_getColumnName('setdate')]
			&& $this->scenario == self::SCENARIO_DO_SAVE
		) {
			$result = $this->getFirstResultFromQuery("
				select count(Evn_id) as Count
				from v_Evn E with(nolock)
				where E.Evn_pid = :id and E.EvnClass_SysNick not in('EvnDiagPLStom','EvnUslugaStom','EvnUslugaCommon')
			", array('id' => $this->id));
			if ($result > 0) {
				throw new Exception('На посещение ссылаются другие события, которые требуют отмены или ручного изменения даты.', 400);
			}
		}

		//throw new Exception(var_export($this->globalOptions, true));
		//Проверяем, есть ли пересечения даты сохраняемого посещения с каким либо движением
		//$this->options['others']['vizit_kvs_control'] почему-то возвращает только значение по умолчанию
		$vizit_kvs_control = 1;
		if (array_key_exists('vizit_kvs_control', $this->globalOptions['globals'])) {
			$vizit_kvs_control = $this->globalOptions['globals']['vizit_kvs_control'];
		}
		$control_paytype = 0;
		if (array_key_exists('vizit_kvs_control', $this->globalOptions['globals'])) {
			$control_paytype = $this->globalOptions['globals']['vizit_kvs_control_paytype'];
		}
		if (empty($this->_params['ignore_vizit_kvs_control']) && empty($this->_params['ignoreDayProfileDuplicateVizit'])) {
			if ( $vizit_kvs_control == 3 || ($vizit_kvs_control == 2 && empty($this->_params['vizit_kvs_control_check'])) ) {
				$and = " and LUT.LpuUnitType_Code = 2";
				if ($this->regionNick == 'kareliya'){
					$and = "";
				}
				$queryParams = array(
					'EvnVizitPL_setDT' => $this->setDT->format('Y-m-d H:i'),
					'Person_id' => $this->Person_id,
					'PayType_id' => $this->PayType_id
				);
				$payTypeFilter = $control_paytype ? "and ES.PayType_id = :PayType_id" : "";
				$diagFilter = getAccessRightsDiagFilter('D.Diag_Code');
				$diagFilter = !empty($diagFilter) ? "and {$diagFilter}" : '';
				$query = "
					select top 1
						ES.EvnSection_id,
						convert(varchar(10), ES.EvnSection_setDate, 104) as EvnSection_setDate,
						convert(varchar(10), ES.EvnSection_disDate, 104) as EvnSection_disDate,
						L.Lpu_Nick,
						D.Diag_FullName,
						D.Diag_Code
					from
						v_EvnSection ES (nolock)
						inner join v_LpuSection LS with (nolock) on ES.LpuSection_id = LS.LpuSection_id
						inner join v_LpuUnit LU with (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
						inner join LpuUnitType LUT with (nolock) on LU.LpuUnitType_id = LUT.LpuUnitType_id {$and}
						left join v_Lpu L with (nolock) on L.Lpu_id = ES.Lpu_id
						left join v_Diag D with (nolock) on D.Diag_id = ES.Diag_id
					where
						ES.EvnSection_setDT < :EvnVizitPL_setDT
						and (ES.EvnSection_disDT > :EvnVizitPL_setDT or ES.EvnSection_disDT is null)
						and ES.Person_id = :Person_id
						and (ISNULL(ES.EvnSection_IsPriem, 1) != 2 or ES.EvnSection_Count = 1)
						{$diagFilter}
						{$payTypeFilter}
				";
				//throw new Exception(getDebugSQL($query, $queryParams));
				$result = $this->db->query($query, $queryParams);
				if (is_object($result)) {
					$checkEvnSection = $result->result('array');
				} else {
					throw new Exception('Не удалось проверить пересечение посещения с движением.');
				}
				if (is_array($checkEvnSection) && count($checkEvnSection) > 0 && !empty($checkEvnSection[0]['EvnSection_id']) ){
					$disDate = !empty($checkEvnSection[0]['EvnSection_disDate'])?$checkEvnSection[0]['EvnSection_disDate']:'текущее время';
					$diagFullName = checkDiagAccessRights($checkEvnSection[0]['Diag_Code'])?$checkEvnSection[0]['Diag_FullName']:'';
					if ($vizit_kvs_control == 3){
						//Запрет сохранения
						$msg = 'Данное посещение имеет пересечение со случаем стационарного лечения '.$checkEvnSection[0]['EvnSection_setDate'] . ' - '.$disDate.' / '.$diagFullName.' / '.$checkEvnSection[0]['Lpu_Nick'].'. Сохранить невозможно.';
						throw new Exception($msg);
					} else if (empty($this->_params['vizit_kvs_control_check'])) {
						//предупреждение
						$this->_saveResponse['ignoreParam'] = 'vizit_kvs_control_check';
						$this->_saveResponse['Alert_Msg'] = 'Данное посещение имеет пересечение со случаем стационарного лечения '.$checkEvnSection[0]['EvnSection_setDate'] . ' - '.$disDate.' / '.$diagFullName.' / '.$checkEvnSection[0]['Lpu_Nick'].'.';
						throw new Exception('YesNo', 111);
					}
				}
			}
		}
	}

	/**
	 * Определение кода цели сохраняемого/сохраненного посещения
	 * @return string
	 * @throws Exception
	 */
	function getVizitTypeSysNick()
	{
		if (empty($this->VizitType_id)) {
			return NULL;
		}
		if (empty($this->_vizitTypeSysNick)) {
			$this->_vizitTypeSysNick = $this->getFirstResultFromQuery('
				select VizitType_SysNick
				from v_VizitType with(nolock)
				where VizitType_id = :VizitType_id
			', array('VizitType_id' => $this->VizitType_id));
			if (empty($this->_vizitTypeSysNick)) {
				throw new Exception('Ошибка при получении типа цели посещения', 500);
			}
		}
		return $this->_vizitTypeSysNick;
	}

	/**
	 * Определение кода места сохраняемого/сохраненного посещения
	 * @return string
	 * @throws Exception
	 */
	function getServiceTypeSysNick()
	{
		if (empty($this->ServiceType_id)) {
			return NULL;
		}
		if (empty($this->_serviceTypeSysNick)) {
			$this->_serviceTypeSysNick = $this->getFirstResultFromQuery('
				select ServiceType_SysNick
				from v_ServiceType with(nolock)
				where ServiceType_id = :ServiceType_id
			', array('ServiceType_id' => $this->ServiceType_id));
			if (empty($this->_serviceTypeSysNick)) {
				throw new Exception('Ошибка при получении типа места посещения', 500);
			}
		}
		return $this->_serviceTypeSysNick;
	}

	/**
	 * @return array
	 * @throws Exception
	 */
	function getLpuSectionData()
	{
		if (empty($this->LpuSection_id)) {
			$this->_LpuSectionData = array();
		}
		if (!empty($this->_LpuSectionData['LpuSection_id']) && $this->_LpuSectionData['LpuSection_id'] != $this->LpuSection_id) {
			$this->_LpuSectionData = array();
		}
		if (empty($this->_LpuSectionData) && !empty($this->LpuSection_id)) {
			$this->_LpuSectionData = $this->getFirstRowFromQuery('
				select top 1
					LS.LpuSection_id,
					LS.LpuSectionProfile_id,
					LSP.LpuSectionProfile_Code,
					LS.LpuSectionAge_id,
					LUT.LpuUnitType_SysNick
				from v_LpuSection LS (nolock)
				left join v_LpuSectionProfile LSP (nolock) on LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id
				left join v_LpuUnit LU with (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_LpuUnitType LUT with (nolock) on LUT.LpuUnitType_id = LU.LpuUnitType_id
				where LS.LpuSection_id = :LpuSection_id
			', array('LpuSection_id' => $this->LpuSection_id));
			if (empty($this->_LpuSectionData)) {
				throw new Exception('Ошибка при получении данных отделения', 500);
			}
		}
		return $this->_LpuSectionData;
	}

	/**
	 * Проверка цели посещения
	 * @throws Exception
	 */
	protected function _checkChangeVizitType()
	{
		// Проверка заполнения поля "Цель посещения"
		if ( in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_SET_ATTRIBUTE))
			&& empty($this->VizitType_id)
		) {
			throw new Exception('Поле "Цель посещения" обязательно для заполнения', 400);
		}
		if ($this->vizitTypeSysNick != 'prof') {
			$this->setAttribute('ProfGoal_id', null);
		}
		if (
			$this->regionNick == 'astra'
			&& (
				$this->vizitTypeSysNick != 'cz'
				|| ($this->person_Age >= 18 && strtotime($this->setDate) >= strtotime('2017-07-21'))
				|| ($this->person_Age < 18 && strtotime($this->setDate) >= strtotime('2017-07-24'))
			)
		) {
			$this->setAttribute('RiskLevel_id', null);
		}
		if (
			$this->regionNick == 'astra'
			&& (
				$this->vizitTypeSysNick != 'cz'
				|| $this->person_Age < 2
				|| $this->person_Age >= 18
				|| strtotime($this->setDate) < strtotime('2017-07-24')
			)
		) {
			$this->setAttribute('WellnessCenterAgeGroups_id', null);
		}

		/*$this->load->model('LpuPassport_model');
		$lpu_tariff_class_list = $this->LpuPassport_model->getLpuTariffClassList($this->Lpu_id, $this->setDate);
		if (
			in_array('VisitSpec', $lpu_tariff_class_list)
			&& !empty($this->UslugaComplex_id)
			&& in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_SET_ATTRIBUTE))
		) {
			$usluga_profile_count = $this->getFirstResultFromQuery("
				select top 1 count(UslugaComplexProfile_id) as Count
				from v_UslugaComplexProfile with(nolock)
				where UslugaComplex_id = :UslugaComplex_id
				and UslugaComplexProfile_begDate <= :Date
				and (UslugaComplexProfile_endDate is null or UslugaComplexProfile_endDate > :Date)
			", array(
				'UslugaComplex_id' => $this->UslugaComplex_id,
				'Date' => $this->setDate
			));
			if ($usluga_profile_count === false) {
				throw new Exception('Ошибка при получении количества профилей на услуге', 500);
			}
			if ($usluga_profile_count > 0 && $this->getVizitTypeSysNick() != 'consul') {
				throw new Exception('Цель посещения не соответствует указанным значениям в Специальности и Коду посещения', 400);
			}
		}*/

		//учитывать в первую очередь параметр, переданный с формы
		if (!empty($this->_params['IsFinish'])) {
			$EvnPL_IsFinish = ($this->_params['IsFinish'] == 2);
		} else {
			$EvnPL_IsFinish = ($this->parent->IsFinish == 2);
		}

		if ( in_array($this->regionNick, array('buryatiya','kareliya','astra'))
			&& in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_SET_ATTRIBUTE))
		) {
			$EvnVizitPL_Count = count($this->parent->evnVizitList);

			$cntConsulDiagn = 0;
			$cntDesease = 0;
			$cntOther = 0;
			foreach ($this->parent->evnVizitList as $id => $row) {
				if ($id == $this->id) {
					continue;
				}
				if ($row['VizitType_SysNick'] == 'desease') {
					$cntDesease++;
				} else if ($row['VizitType_SysNick'] == 'ConsulDiagn') {
					$cntConsulDiagn++;
				} else {
					$cntOther++;
				}
			}
			if ( $this->isNewRecord && $EvnVizitPL_Count > 1 && $cntOther > 0 && !('kareliya' === $this->regionNick && strpos($this->setDate, '2017') >= 0) ) {
				throw new Exception('В ТАП более одного посещения и присутствуют посещения с целью, отличной от "Обращение по поводу заболевания"!', 400);
			}
			if (!$this->isNewRecord) {
				if ($this->vizitTypeSysNick == 'desease') {
					$cntDesease++;
				} else if ($this->vizitTypeSysNick == 'ConsulDiagn') {
					$cntConsulDiagn++;
				} else {
					$cntOther++;
				}
			}
			//Проверка соответствия переданного VizitType и IsFinish (ЭМК)
			if(!(isset($this->_params['streamInput']) && $this->_params['streamInput'])){
				if ( $this->vizitTypeSysNick == 'desease' && $EvnPL_IsFinish && $EvnVizitPL_Count == 1 && false === in_array($this->regionNick, array('buryatiya', 'astra'))) {
					throw new Exception('Сохранение закрытого ТАП по заболеванию с одним посещением невозможно', 400);
				}
				if ( $this->vizitTypeSysNick == 'desease' && $EvnPL_IsFinish && $EvnVizitPL_Count == 1 && $this->evnClassId == 11 && 'astra' === $this->regionNick) {
					throw new Exception('Сохранение закрытого ТАП по заболеванию с одним посещением невозможно', 400);
				}
			}
			/*if ( $this->vizitTypeSysNick == 'desease' && $EvnPL_IsFinish && $EvnVizitPL_Count == 1 && 'buryatiya' == $this->regionNick && $this->evnClassId == 11 && '301' == $this->parent->resultClassCode) {
				throw new Exception('Если в посещении указана цель Заболевание и Результат обращения 301, то в ТАП должно быть не меньше двух посещений', 400);
			}*/
			//Добавляемое посещение с целью отличной от desease должно быть единственным
			if ('kareliya' === $this->regionNick && strpos($this->setDate, '2017') >= 0) {
				if ( $this->isNewRecord && ($this->vizitTypeSysNick != 'desease' && $this->vizitTypeSysNick != 'consulspec') && $EvnVizitPL_Count > 1 ) {
					throw new Exception('Добавление посещения невозможно, т.к. в ТАП уже создано посещение!', 400);
				}
				if ( !$this->isNewRecord && ($this->vizitTypeSysNick != 'desease' && $this->vizitTypeSysNick != 'consulspec') && $EvnVizitPL_Count > 1 ) {
					throw new Exception('Сохранение посещения с целью отличной от "Обращение по поводу заболевания" или "Диспансерное наблюдение" невозможно, т.к. в ТАП более одного посещения и присутствуют посещения с целью "Обращение по поводу заболевания" или "Диспансерное наблюдение"!', 400);
				}
			} else {
				if ( $this->isNewRecord && !in_array($this->vizitTypeSysNick, array('ConsulDiagn', 'desease')) && $EvnVizitPL_Count > 1 ) {
					throw new Exception('Добавление посещения невозможно, т.к. в ТАП уже создано посещение!', 400);
				}
				if ( !$this->isNewRecord && $this->vizitTypeSysNick != 'desease' && $this->vizitTypeSysNick != 'ConsulDiagn' && $EvnVizitPL_Count > 1 ) {
					if ( $cntDesease > 0 ) {
						throw new Exception('Сохранение посещения с целью, отличной от "Обращение по поводу заболевания" невозможно, т.к. в ТАП более одного посещения, и присутствуют посещения с целью "Обращение по поводу заболевания"!', 400);
					}
					if ( $cntConsulDiagn > 0 ) {
						throw new Exception('Сохранение посещения с целью, отличной от "Консультативно-диагностическая" невозможно, т.к. в ТАП более одного посещения, и присутствуют посещения с целью "Консультативно-диагностическая"!', 400);
					}
				}

				if ( !$this->isNewRecord && $this->regionNick == 'astra' && $EvnVizitPL_Count > 1 ) {
					if ( $this->vizitTypeSysNick == 'desease' && $cntConsulDiagn > 0 ) {
						throw new Exception('Сохранение посещения с целью "Обращение по поводу заболевания" невозможно, т.к. в ТАП более одного посещения, и присутствуют посещения с целью "Консультативно-диагностическая"!', 400);
					}
					if ( $this->vizitTypeSysNick == 'ConsulDiagn' && $cntDesease > 0 && $EvnVizitPL_Count > 1 ) {
						throw new Exception('Сохранение посещения с целью "Консультативно-диагностическая" невозможно, т.к. в ТАП более одного посещения, и присутствуют посещения с целью "Обращение по поводу заболевания"!', 400);
					}
				}
			}
			
		}
		if (
			$this->getRegionNick() == 'kareliya'
			&& in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_SET_ATTRIBUTE))
			&& ($this->vizitTypeSysNick == 'npom' || $this->vizitTypeSysNick == 'nform')
			&& strtotime($this->setDate) >= strtotime('2015-05-01')
			&& $EvnPL_IsFinish
		) {
			$uslCmpCnt = $this->getFirstResultFromQuery("
				select top 1 count(EU.EvnUsluga_id) as Count
				from v_EvnUsluga EU with(nolock)
				where EU.EvnUsluga_pid = :EvnUsluga_pid and exists (
					select top 1 t1.UslugaComplexAttribute_id
					from UslugaComplexAttribute t1 with (nolock)
					inner join UslugaComplexAttributeType t2 with (nolock) on t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
					where t1.UslugaComplex_id = EU.UslugaComplex_id and t2.UslugaComplexAttributeType_SysNick in ('uslcmp')
				)
			", array('EvnUsluga_pid' => $this->id));
			if ($uslCmpCnt === false) {
				throw new Exception('Не удалось определить количество услуг из РК 20', 500);
			}
			if ($uslCmpCnt == 0) {
				throw new Exception('При посещении по поводу неотложной помощи должна быть указана хотя бы одна<br/>услуга из РК 20', 400);
			}
		}

		if (
			$this->getRegionNick() == 'kareliya'
			&& in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_SET_ATTRIBUTE))
		) {
			$result = $this->queryResult("
				select top 1
					EVPL.EvnVizitPL_id,
					cnt.cnt
				from
					v_EvnPL EPL (nolock)
					inner join v_EvnVizitPL EVPL with (nolock) on EVPL.EvnVizitPL_pid = EPL.EvnPL_id
					inner join v_VizitType VT with (nolock) on VT.VizitType_id = EVPL.VizitType_id and VT.VizitType_Code not in ('11', '23')
					outer apply (
						select
							COUNT (EvnVizitPL_id) as cnt
						from
							v_EvnVizitPL with (nolock)
						where
							EvnVizitPL_pid = EPL.EvnPL_id
					) cnt
				where
					EPL.EvnPL_id = :EvnPL_id 
					and EVPL.EvnVizitPL_id <> ISNULL(:exceptEvnVizitPL_id, 0)
					and YEAR(EVPL.EvnVizitPL_setDate) = 2017",
				array('EvnPL_id' => $this->parent->id, 'exceptEvnVizitPL_id' => $this->id)
			);
			if (!empty($result['EvnVizitPL_id']) && $result['cnt'] > 0){
				throw new Exception('Добавление посещения невозможно, т.к. в рамках текущего ТАП уже есть посещение.', 499);
			}
		}
	}

	/**
	 * Проверка места посещения
	 * @throws Exception
	 */
	protected function _checkChangeServiceType()
	{
		$isStom = (13 == $this->evnClassId);

		if ( in_array($this->regionNick, array('perm'))
			&& in_array($this->scenario, array(self::SCENARIO_SET_ATTRIBUTE))
			&& $isStom == false && $this->_params['ignoreCheckB04069333'] == false
			&& $this->setDT->getTimestamp() < strtotime('01.05.2018')
		) {
			$EvnUsluga_id = $this->getFirstResultFromQuery("
				select top 1 t1.EvnUsluga_id
				from v_EvnUsluga t1 with (nolock)
					inner join v_UslugaComplex t2 with (nolock) on t2.UslugaComplex_id = t1.UslugaComplex_id
				where t1.EvnUsluga_pid = :EvnVizitPL_id
					and t2.UslugaComplex_Code = 'B04.069.333'
			", array('EvnVizitPL_id' => $this->id));

			if ( in_array($this->serviceTypeSysNick, array('home', 'ahome', 'neotl')) && empty($EvnUsluga_id) ) {
				$UslugaComplex_Name = $this->getFirstResultFromQuery("
					select top 1 UslugaComplex_Name
					from v_UslugaComplex with (nolock)
					where UslugaComplex_Code = 'B04.069.333'
						and (UslugaComplex_begDT is null or UslugaComplex_begDT <= :setDate)
						and (UslugaComplex_endDT is null or UslugaComplex_endDT >= :setDate)
				", array('setDate' => $this->setDate), null);

				$this->_saveResponse['ignoreParam'] = 'ignoreCheckB04069333';
				$this->_saveResponse['Alert_Msg'] = 'Добавить в посещение услугу B04.069.333 «' . (!empty($UslugaComplex_Name) ? $UslugaComplex_Name : 'Оказание неотложной помощи вне медицинской организации (на дому)') .'»?';
				throw new Exception('YesNo', 131);
			}
		}
	}

	/**
	 * Проверка наличия карты диспансеризации при диагнозе из определенной группы (refs #169331)
	 * @throws Exception
	 */
	protected function _checkDiagDispCard()
	{
		if (in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_SET_ATTRIBUTE)) && !$this->_params['ignoreDiagDispCheck']) {
			// ищем прикрепление
			$query_attach = "
					select top 1
						PersonCard_id
					from
						v_PersonCard with (nolock)
					where
						Lpu_id = :Lpu_id and Person_id = :Person_id
				";
			
			$response_attach = $this->getFirstRowFromQuery($query_attach, array(
				'Person_id' => $this->Person_id,
				'Lpu_id' => $this->Lpu_id
			));
			
			if (!empty($response_attach)) {
				// если прикрепление есть, проверяем диагноз
				$query_diag = "
						select top 1
							DispSickDiag_id
						from
							v_DispSickDiag with (nolock)
						where
							Diag_id = :Diag_id
					";
				
				$response_diag = $this->getFirstRowFromQuery($query_diag, array(
					'Diag_id' => $this->Diag_id
				));
				
				if (!empty($response_diag)) {
					// если диагноз входит в список, проверяем карту диспансерного наблюдения
					$query_disp_card = "
							declare
								@date date = dbo.tzGetDate();
							
							select top 1
								 PersonDisp_id
							from
								v_PersonDisp with (nolock)
							where
								Person_id = :Person_id
								and Lpu_id = :Lpu_id
								and convert(varchar(10), :setDate, 104) between PersonDisp_begDate and COALESCE(PersonDisp_endDate, @date)
								and Diag_id = :Diag
						";
					
					$response_disp_card = $this->getFirstRowFromQuery($query_disp_card, array(
						'Person_id' => $this->Person_id,
						'Lpu_id' => $this->Lpu_id,
						'setDate' => $this->setDate,
						'Diag' => $this->Diag_id
					));
					
					if (empty($response_disp_card)) {
						$diag_code_result = $this->getFirstRowFromQuery('select top 1 Diag_Code from v_Diag (nolock) where Diag_id = :Diag_id', array('Diag_id' => $this->Diag_id));
						$diag_code = $diag_code_result['Diag_Code'];
						
						$this->_saveResponse['ignoreParam'] = 'ignoreDiagDispCheck';
						$this->_saveResponse['Alert_Msg'] = "Пациент с диагнозом $diag_code нуждается в диспансерном наблюдении. Создать карту диспансерного наблюдения?";
						throw new Exception('YesNo', 182);
					}
				}
			}
		}
	}

	/**
	 * Проверка на второе посещение НМП в Бурятии
	 * @throws Exception
	 */
	protected function _controlDoubleNMP($EvnID, $EvnVisitID = null, $Stom = false)
	{
		$stomStr = '';
		$whereVisit = '';

		if($Stom) $stomStr = 'Stom';
		if(!empty($EvnVisitID)) $whereVisit = "and EvnVizitPL{$stomStr}_id <> :EvnVisitID";

		if($this->regionNick == 'buryatiya') {
			$query = "
				select count(EVPL.TreatmentClass_id)
				from v_EvnVizitPL{$stomStr} EVPL with (nolock)
					inner join v_EvnPL{$stomStr} EPL with (nolock) on EPL.EvnPL{$stomStr}_id = EVPL.EvnVizitPL{$stomStr}_pid and EVPL.EvnVizitPL{$stomStr}_rid = EPL.EvnPL{$stomStr}_id
				where EPL.EvnPL{$stomStr}_id = :EvnID
					{$whereVisit}
					and EVPL.TreatmentClass_id = 2
			";
			$result = $this->getFirstResultFromQuery($query, array(
				'EvnID' => $EvnID,
				'EvnVisitID' => $EvnVisitID
			));
			if ( $result > 0 ) {
				throw new Exception("Случай лечения заболевания в неотложной форме должен состоять только из одного посещения.");
			}
		}
	}

	/**
	 * Проверка двойственности посещений пациентов
	 * @throws Exception
	 */
	protected function _controlDoubleVizit()
	{
		$double_vizit_control = $this->allOptions['polka']['double_vizit_control'];

		$isAllowControlDoubleVizit = (
			($this->scenario == self::SCENARIO_DO_SAVE || $this->scenario == self::SCENARIO_AUTO_CREATE)
			&& ($this->payTypeSysNick == 'oms' || in_array($this->regionNick, array('kz','vologda')))
			&& (
				$double_vizit_control == 3
				|| ($double_vizit_control == 2 && $this->_params['ignoreDayProfileDuplicateVizit'] == false)
			)
		);

		// отключаем данную проверку также, если коды посещений: 574%, 575%, 576%, 577%, 578%, 579%, 674%, 675%, 676%, 677%, 678%, 679%, 874%, 875%, 876%, 876%, 877%, 878%, 879%.
		/*if ($this->regionNick == 'ufa' && !empty($this->vizitCode)
			&& in_array(substr($this->vizitCode,0,3), array(574, 575, 576, 577, 578,
				579, 674, 675, 676, 677, 678, 679, 874, 875, 876, 876, 877, 878, 879))
		) {
			$isAllowControlDoubleVizit = false;
		}*/

		// Для Астрахани проверка вынесена в региональную модель
		// https://redmine.swan.perm.ru/issues/70829

		/*
		throw new Exception(var_export(array(
			'isAllowControlDoubleVizit' => $isAllowControlDoubleVizit,
			'double_vizit_control' => $this->allOptions['polka']['double_vizit_control'],
			'vizitCode' => $this->vizitCode,
			'substr_vizitCode' => substr($this->vizitCode,0,3),
			'ignoreDayProfileDuplicateVizit' => isset($_POST['ignoreDayProfileDuplicateVizit']),
			'evnClassId' => $this->evnClassId,
		), true));
		*/
		if ( $isAllowControlDoubleVizit ) {
			// Проверка для ККБ
			if ( $this->regionNick == 'perm' && $this->Lpu_id == 150185 && $this->evnClassId == 11) {
				$query = "
					select top 1 1 as rec
					from v_EvnVizitPL EVPL with (nolock)
						--inner join v_EvnPL EPL with (nolock) on EPL.EvnPL_id = EVPL.EvnVizitPL_pid and EVPL.EvnVizitPL_rid = EPL.EvnPL_id
						--inner join v_LpuSection LS with (nolock) on LS.LpuSection_id = EVPL.LpuSection_id
						--inner join v_PayType PT with (nolock) on EVPL.PayType_id = PT.PayType_id and PT.PayType_SysNick = 'oms'
						--inner join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EVPL.MedPersonal_id and MP.Lpu_id = EVPL.Lpu_id
					where (1 = 1)
						and EVPL.Lpu_id = :Lpu_id
						and EVPL.EvnVizitPL_id <> ISNULL(:EvnVizitPL_id, 0)
						and EVPL.MedPersonal_id = :MedPersonal_id
						and EVPL.EvnVizitPL_setDate = cast(:EvnVizitPL_setDate as datetime)
						and EVPL.PayType_id = :PayType_id
						and EVPL.Person_id = :Person_id
				";
				$result = $this->getFirstResultFromQuery($query, array(
					'EvnVizitPL_id' => $this->id,
					'EvnVizitPL_setDate' => $this->setDate,
					'Lpu_id' => $this->Lpu_id,
					'MedPersonal_id' => $this->MedPersonal_id,
					'PayType_id' => 1, //$this->PayType_id, // Для Перми 1 = 'oms', поскольку проверка только для одной Пермской МО, допускаем использование идентификатора
					'Person_id' => $this->Person_id
				));
				// результат будет false если ничего не найдено, поэтому вывод ошибки убираем
				/*if ( false === $result ) {
					throw new Exception('Ошибка при контроле двойственности посещений пациентов', 500);
				}*/
				if ( $result > 0 ) {
					if ( $double_vizit_control == 2 ) {
						$this->_saveResponse['ignoreParam'] = "ignoreDayProfileDuplicateVizit";
						$this->_saveResponse['Alert_Msg'] = "Данное посещение не войдет в реестр на оплату, как повторное посещение врача за один день. Продолжить сохранение?";
						throw new Exception('YesNo');
					}
					else if ( $double_vizit_control == 3 ) {
						throw new Exception("Данное посещение не войдет в реестр на оплату, как повторное посещение врача за один день");
					}
				}
			}
			$add_where = '';
			$add_params = array();
			if ( $this->evnClassId == 13 ) {
				$add_where .= "
						and EVPL.Diag_id = coalesce(CAST(:Diag_id as bigint),0)
						and EVPSPL.Tooth_id = coalesce(CAST(:Tooth_id as bigint),0)
					";
				$add_params['Diag_id'] = $this->Diag_id;
				$add_params['Tooth_id'] = $this->Tooth_id;
			}
			if (in_array($this->evnClassId, array(11, 13))) {
				switch($this->regionNick) {
					case 'ekb':
						$query = "
							select
								count(*) as rec
							from {$this->viewName()} evpl with (nolock)
								LEFT OUTER JOIN v_EvnVizitPLStom EVPSPL
									ON EVPL.{$this->primaryKey()} = EVPSPL.evnvizitplstom_id
								inner join v_PayType pt with (nolock) on pt.PayType_id = evpl.PayType_id
									and pt.PayType_SysNick = 'oms'
								inner join v_MedStaffFact msf with (nolock) on msf.MedStaffFact_id = evpl.MedStaffFact_id
								cross apply (
									select top 1 MedSpecOms_id
									from v_MedStaffFact with (nolock)
									where MedStaffFact_id = :MedStaffFact_id
								) curmsf
							where (1 = 1)
								and evpl.Lpu_id = :Lpu_id
								and evpl.{$this->primaryKey()} <> ISNULL(:EvnVizitPL_id, 0)
								and evpl.{$this->tableName()}_setDate = cast(:EvnVizitPL_setDate as datetime)
								and evpl.Person_id = :Person_id
								and msf.MedSpecOms_id = curmsf.MedSpecOms_id
								{$add_where}
						";
						$result = $this->getFirstResultFromQuery($query, array_merge($add_params, array(
							'EvnVizitPL_id' => $this->id,
							'EvnVizitPL_setDate' => $this->setDate,
							'Lpu_id' => $this->Lpu_id,
							'MedStaffFact_id' => $this->MedStaffFact_id,
							'Person_id' => $this->Person_id
						)));
						if ( false === $result ) {
							throw new Exception('Ошибка при контроле двойственности посещений пациентов', 500);
						}
						if ( $result > 0 ) {
							if ( $double_vizit_control == 2 ) {
								$this->_saveResponse['ignoreParam'] = "ignoreDayProfileDuplicateVizit";
								$this->_saveResponse['Alert_Msg'] = 'Данное посещение не войдет в реестр на оплату, как повторное посещение
						<br/>по одной специальности за один день (кол-во двойных записей: '. $result .'). Продолжить сохранение?';
								throw new Exception('YesNo');
							}
							else if ( $double_vizit_control == 3 ) {
								throw new Exception("Данное посещение не войдет в реестр на оплату, как повторное посещение
						<br/>по одной специальности за один день (кол-во двойных записей: ". $result .")");
							}
						}
						break;

					case 'kz':
						$query = "
							select
								count(*) as rec
							from {$this->viewName()} EVPL with (nolock)
							where (1 = 1)
								and EVPL.Lpu_id = :Lpu_id
								and EVPL.{$this->primaryKey()} <> ISNULL(:EvnVizitPL_id, 0)
								and (EVPL.MedPersonal_id = :MedPersonal_id or EVPL.MedPersonal_sid = :MedPersonal_sid)
								and EVPL.{$this->tableName()}_setDate = cast(:EvnVizitPL_setDate as datetime)
								and EVPL.Person_id = :Person_id
						";
						$result = $this->getFirstResultFromQuery($query, array_merge($add_params, array(
							'EvnVizitPL_id' => $this->id,
							'EvnVizitPL_setDate' => $this->setDate,
							'Lpu_id' => $this->Lpu_id,
							'MedPersonal_id' => $this->MedPersonal_id,
							'MedPersonal_sid' => $this->MedPersonal_sid,
							'Person_id' => $this->Person_id
						)));
						if ( false === $result ) {
							throw new Exception('Ошибка при контроле двойственности посещений пациентов [kz]', 500);
						}
						if ( $result > 0 ) {
							if ( $double_vizit_control == 2 ) {
								$this->_saveResponse['ignoreParam'] = "ignoreDayProfileDuplicateVizit";
								$this->_saveResponse['Alert_Msg'] = 'В системе уже сохранено посещение, у которого указана такая же дата посещения и такой же врач (фельдшер). Продолжить сохранение?';
								throw new Exception('YesNo');
							}
							else if ( $double_vizit_control == 3 ) {
								throw new Exception("В системе уже сохранено посещение, у которого указана такая же дата посещения и такой же врач (фельдшер).");
							}
						}
						break;

					case 'buryatiya':
						$query = "
							select top 1
								EVPL.{$this->primaryKey()} as id,
								convert(varchar(10), EVPL.{$this->tableName()}_setDate, 104) as setDate,
								ps.Person_SurName+ISNULL(' '+ps.Person_FirName,'')+ISNULL(' '+ps.Person_SecName,'') as Person_Fio,
								msf.Person_Fio + ISNULL(' (' + mso.MedSpecOms_Name + ')', '') as MedPersonal_Fio,
								vt.VizitType_Name
							from {$this->viewName()} EVPL with (nolock)
								left join v_VizitType vt with (nolock) on vt.VizitType_id = evpl.VizitType_id
								left join v_PersonState ps with (nolock) on ps.Person_id = evpl.Person_id
								left join v_MedStaffFact msf  with (nolock) on msf.MedStaffFact_id = evpl.MedStaffFact_id
								left join v_MedSpecOms mso with (nolock) on mso.MedSpecOms_id = msf.MedSpecOms_id
							where (1 = 1)
								and EVPL.Lpu_id = :Lpu_id
								and EVPL.{$this->primaryKey()} <> ISNULL(:EvnVizitPL_id, 0)
								and EVPL.MedPersonal_id = :MedPersonal_id -- по одному врачу
								and cast(EVPL.{$this->tableName()}_setDate as date) = cast(:EvnVizitPL_setDate as date) -- в один день
								and EVPL.Person_id = :Person_id -- на одного пациента
								and EVPL.Diag_id = :Diag_id -- с одинаковым диагнозом
								and EVPL.VizitType_id = :VizitType_id -- с одинаковой целью посещения
						";
						$resp_check = $this->queryResult($query, array_merge($add_params, array(
							'EvnVizitPL_id' => $this->id,
							'EvnVizitPL_setDate' => $this->setDate,
							'Lpu_id' => $this->Lpu_id,
							'MedPersonal_id' => $this->MedPersonal_id,
							'Person_id' => $this->Person_id,
							'Diag_id' => $this->Diag_id,
							'VizitType_id' => $this->VizitType_id
						)));
						if ( !empty($resp_check[0]['id']) ) {
							if ( $double_vizit_control == 2 ) {
								$this->_saveResponse['ignoreParam'] = "ignoreDayProfileDuplicateVizit";
								$this->_saveResponse['Alert_Msg'] = 'В системе уже имеется посещение ' .$resp_check[0]['Person_Fio']. ' от '.$resp_check[0]['setDate'].' с указанным диагнозом<br/>Врач - '.$resp_check[0]['MedPersonal_Fio'].';<br/>Цель посещения - '.$resp_check[0]['VizitType_Name'].'.<br/>Продолжить сохранение?';
								throw new Exception('YesNo');
							}
							else if ( $double_vizit_control == 3 ) {
								throw new Exception('В системе уже имеется посещение ' .$resp_check[0]['Person_Fio']. ' от '.$resp_check[0]['setDate'].' с указанным диагнозом<br/>Врач - '.$resp_check[0]['MedPersonal_Fio'].';<br/>Цель посещения - '.$resp_check[0]['VizitType_Name']);
							}
						}
						break;

					case 'kareliya':
						$query = "
							select top 1
								EVPL.{$this->primaryKey()} as id,
								convert(varchar(10), EVPL.{$this->tableName()}_setDate, 104) as setDate,
								ps.Person_SurName+ISNULL(' '+ps.Person_FirName,'')+ISNULL(' '+ps.Person_SecName,'') as Person_Fio,
								lsp.LpuSectionProfile_Name,
								ls.LpuSection_Name
							from {$this->viewName()} EVPL with (nolock)
								inner join v_PayType PT with (nolock) on EVPL.PayType_id = PT.PayType_id and PT.PayType_SysNick = 'oms'
								inner join v_LpuSection ls with (nolock) on ls.LpuSection_id = evpl.LpuSection_id
								inner join v_LpuSectionProfile lsp with (nolock) on lsp.LpuSectionProfile_id = ISNULL(evpl.LpuSectionProfile_id, ls.LpuSectionProfile_id)
								left join v_PersonState ps with (nolock) on ps.Person_id = evpl.Person_id
							where (1 = 1)
								and EVPL.Lpu_id = :Lpu_id
								and EVPL.{$this->primaryKey()} <> ISNULL(:EvnVizitPL_id, 0)
								and EVPL.LpuSection_id = :LpuSection_id -- в одно отделение
								and lsp.LpuSectionProfile_id = :LpuSectionProfile_id -- с одним профилем
								and cast(EVPL.{$this->tableName()}_setDate as date) = cast(:EvnVizitPL_setDate as date) -- в один день
								and EVPL.Person_id = :Person_id -- на одного пациента
						";
						$resp_check = $this->queryResult($query, array_merge($add_params, array(
							'EvnVizitPL_id' => $this->id,
							'EvnVizitPL_setDate' => $this->setDate,
							'Lpu_id' => $this->Lpu_id,
							'LpuSection_id' => $this->LpuSection_id,
							'LpuSectionProfile_id' => $this->LpuSectionProfile_id,
							'Person_id' => $this->Person_id,
							'Diag_id' => $this->Diag_id,
							'VizitType_id' => $this->VizitType_id
						)));
						if ( !empty($resp_check[0]['id']) ) {
							if ($double_vizit_control == 2) {
								$this->_saveResponse['ignoreParam'] = "ignoreDayProfileDuplicateVizit";
								$this->_saveResponse['Alert_Msg'] = 'В системе уже имеется посещение ' . $resp_check[0]['Person_Fio'] . ' от ' . $resp_check[0]['setDate'] . ', ' . $resp_check[0]['LpuSectionProfile_Name'] . ', ' . $resp_check[0]['LpuSection_Name'] . '<br/>Продолжить сохранение?';
								throw new Exception('YesNo');
							} else if ($double_vizit_control == 3) {
								throw new Exception('Сохранение посещения запрещено: в системе уже имеется посещение ' . $resp_check[0]['Person_Fio'] . ', ' . $resp_check[0]['setDate'] . ', ' . $resp_check[0]['LpuSectionProfile_Name'] . ', ' . $resp_check[0]['LpuSection_Name']);
							}
						}
						break;

					case 'perm':
						if (count($this->_doubles) > 0) {
							$firstDoubleEdit = true;
							$doublesEvnPL = array();
							if (strtotime($this->setDate) >= strtotime('01.06.2017')) {
								foreach ($this->_doubles as $double) {
									if ($double['EvnVizitPL_pid'] == $this->pid) {
										$doublesEvnPL[] = $double;
										if (!empty($double['VizitPLDouble_id'])) {
											$firstDoubleEdit = false;
										}
									}
								}
							}

							if (count($doublesEvnPL) > 0) {
								if (!empty($this->_params['EvnVizitPLDoublesData'])) {
									$doubleUpdate = array();
									foreach($this->_params['EvnVizitPLDoublesData'] as $oneDouble) {
										if ($oneDouble['EvnVizitPL_id'] == -1 || (!empty($this->id) && $this->id == $oneDouble['EvnVizitPL_id'])) {
											$this->setAttribute('vizitpldouble_id', $oneDouble['VizitPLDouble_id']);
										} else {
											$doubleUpdate[$oneDouble['EvnVizitPL_id']] = $oneDouble['VizitPLDouble_id'];
										}
									}

									foreach ($doublesEvnPL as $double) {
										if (isset($doubleUpdate[$double['EvnVizitPL_id']])) {
											$this->db->query("update EvnVizitPL with (rowlock) set VizitPLDouble_id = :VizitPLDouble_id where EvnVizitPL_id = :EvnVizitPL_id", array(
												'EvnVizitPL_id' => $double['EvnVizitPL_id'],
												'VizitPLDouble_id' => $doubleUpdate[$double['EvnVizitPL_id']]
											));
										}
									}
								} else {
									if (!empty($this->VizitPLDouble_id)) {
										$firstDoubleEdit = false;
									}

									if ($firstDoubleEdit) {
										$firstDouble = true;
										foreach ($doublesEvnPL as $key => $double) {
											if ($firstDouble) {
												$doublesEvnPL[$key]['VizitPLDouble_id'] = 1;
											} else {
												$doublesEvnPL[$key]['VizitPLDouble_id'] = 2;
											}

											$firstDouble = false;
										}
									}
									// Для посещений с датой после 01.06.2017 если дублирующее посещения находится в той же ТАП
									// вместо предупреждения выводится форма с таблицей, в которой отображаются все такие посещения, включая текущее.
									$doublesEvnPL[] = array(
										'EvnVizitPL_id' => !empty($this->id) ? $this->id : -1,
										'LpuSection_Name' => $this->getFirstResultFromQuery("select LpuSection_Name from v_LpuSection (nolock) where LpuSection_id = :LpuSection_id", array('LpuSection_id' => $this->LpuSection_id)),
										'MedPersonal_Fio' => $this->getFirstResultFromQuery("select Person_Fio from v_MedStaffFact (nolock) where MedStaffFact_id = :MedStaffFact_id", array('MedStaffFact_id' => $this->MedStaffFact_id)),
										'EvnVizitPL_setDate' => date('d.m.Y', strtotime($this->setDate)),
										'EvnVizitPL_pid' => $this->pid,
										'VizitPLDouble_id' => $firstDoubleEdit ? 2 : $this->VizitPLDouble_id,
										'accessType' => 'edit'
									);
									$this->_saveResponse['ignoreParam'] = "ignoreDayProfileDuplicateVizit";
									$this->_saveResponse['Alert_Msg'] = $doublesEvnPL;
									$this->_saveResponse['Cancel_Error_Handle'] = true;
									throw new Exception('EvnVizitPLDouble');
								}
							} else {
								$count = count($this->_doubles);
								if ($double_vizit_control == 2) {
									$this->_saveResponse['ignoreParam'] = "ignoreDayProfileDuplicateVizit";
									$this->_saveResponse['Alert_Msg'] = 'Данное посещение не войдет в реестр на оплату, как повторное посещение<br/>по одной специальности за один день (кол-во двойных записей: ' . $count . '). Продолжить сохранение?';
									throw new Exception('YesNo');
								} else if ($double_vizit_control == 3) {
									throw new Exception("Данное посещение не войдет в реестр на оплату, как повторное посещение<br/>по одной специальности за один день (кол-во двойных записей: " . $count . ")");
								}
							}
						}
					break;

					case 'ufa':
						if (count($this->_doubles) > 0) {
							$count = count($this->_doubles);

							if ($double_vizit_control == 2) {
								$this->_saveResponse['ignoreParam'] = "ignoreDayProfileDuplicateVizit";
								$this->_saveResponse['Alert_Msg'] = 'Данное посещение не войдет в реестр на оплату, как повторное посещение<br/>по одному профилю отделения к одному врачу за один день (кол-во двойных записей: ' . $count . '). Продолжить сохранение?';
								throw new Exception('YesNo');
							} else if ($double_vizit_control == 3) {
								throw new Exception('Данное посещение не войдет в реестр на оплату, как повторное посещение<br/>по одному профилю отделения к одному врачу за один день (кол-во двойных записей: ' . $count . ').');
							}
						}
					break;

					case 'vologda':
						$query = "
							select top 1
								EVPL.{$this->primaryKey()} as id,
								convert(varchar(10), EVPL.{$this->tableName()}_setDate, 104) as setDate,
								ps.Person_SurName+ISNULL(' '+ps.Person_FirName,'')+ISNULL(' '+ps.Person_SecName,'') as Person_Fio,
								lsp.LpuSectionProfile_Name,
								ls.LpuSection_Name
							from {$this->viewName()} EVPL with (nolock)
								inner join v_PayType PT with (nolock) on EVPL.PayType_id = PT.PayType_id
								inner join v_LpuSection ls with (nolock) on ls.LpuSection_id = evpl.LpuSection_id
								inner join v_LpuSectionProfile lsp with (nolock) on lsp.LpuSectionProfile_id = ISNULL(evpl.LpuSectionProfile_id, ls.LpuSectionProfile_id)
								left join v_PersonState ps with (nolock) on ps.Person_id = evpl.Person_id
							where (1 = 1)
								and EVPL.{$this->primaryKey()} <> ISNULL(:EvnVizitPL_id, 0)
								and EVPL.PayType_id = :PayType_id
								and EVPL.MedStaffFact_id = :MedStaffFact_id
								and cast(EVPL.{$this->tableName()}_setDate as date) = cast(:EvnVizitPL_setDate as date) -- в один день
								and EVPL.Person_id = :Person_id -- на одного пациента
								and EVPL.LpuSectionProfile_id = :LpuSectionProfile_id 
						";
						$resp_check = $this->queryResult($query, array_merge($add_params, array(
							'EvnVizitPL_id' => $this->id,
							'EvnVizitPL_setDate' => $this->setDate,
							'Lpu_id' => $this->Lpu_id,
							'PayType_id' => $this->PayType_id,
							'MedStaffFact_id' => $this->MedStaffFact_id,
							'Person_id' => $this->Person_id,
							'LpuSectionProfile_id' => $this->LpuSectionProfile_id
						)));
						if ( !empty($resp_check[0]['id']) ) {
							if ($double_vizit_control == 2) {
								$this->_saveResponse['ignoreParam'] = "ignoreDayProfileDuplicateVizit";
								$this->_saveResponse['Alert_Msg'] = 'В системе уже имеется посещение ' . $resp_check[0]['Person_Fio'] . ' от ' . $resp_check[0]['setDate'] . ', ' . $resp_check[0]['LpuSectionProfile_Name'] . ', ' . $resp_check[0]['LpuSection_Name'] . '<br/>Продолжить сохранение?';
								throw new Exception('YesNo');
							} else if ($double_vizit_control == 3) {
								throw new Exception('Сохранение посещения запрещено: в системе уже имеется посещение ' . $resp_check[0]['Person_Fio'] . ', ' . $resp_check[0]['setDate'] . ', ' . $resp_check[0]['LpuSectionProfile_Name'] . ', ' . $resp_check[0]['LpuSection_Name']);
							}
						}
						break;

					default:
						// https://redmine.swan.perm.ru/issues/18810
						// Для Уфы исключаем из проверки посещения с кодами 605%, 647%, 805%, 847%
						/*if ( $this->regionNick == 'ufa' ) {
							$add_where .= "
								and exists (
									select top 1 t1.EvnUsluga_id
									from v_EvnUsluga t1 with (nolock)
										inner join v_UslugaComplex t2 with (nolock) on t2.UslugaComplex_id = t1.UslugaComplex_id
										inner join v_UslugaCategory t3 with (nolock) on t3.UslugaCategory_id = t2.UslugaCategory_id
									where t1.EvnUsluga_pid = EVPL.{$this->primaryKey()} and t1.Lpu_id = EVPL.Lpu_id
										and t3.UslugaCategory_SysNick = 'lpusection'
										and left(t2.UslugaComplex_Code, 3) not in ('605', '647', '805', '847')
								)
							";
						}*/
						$query = "
							select
								count(*) as rec
							from {$this->viewName()} EVPL with (nolock)
								LEFT OUTER JOIN v_EvnVizitPLStom EVPSPL
									ON EVPL.{$this->primaryKey()} = EVPSPL.evnvizitplstom_id
								inner join v_LpuSection LS with (nolock) on LS.LpuSection_id = EVPL.LpuSection_id
								inner join v_PayType PT with (nolock) on EVPL.PayType_id = PT.PayType_id
									and PT.PayType_SysNick = 'oms'
							where (1 = 1)
								and EVPL.Lpu_id = :Lpu_id
								and EVPL.Person_id = :Person_id
								and EVPL.{$this->primaryKey()} <> ISNULL(:EvnVizitPL_id, 0)
								and EVPL.MedPersonal_id = :MedPersonal_id
								and LS.LpuSectionProfile_id = (select top 1 LpuSectionProfile_id from LpuSection (nolock) where LpuSection_id = :LpuSection_id)
								and EVPL.{$this->tableName()}_setDate = cast(:EvnVizitPL_setDate as datetime)
								and EVPL.Diag_id = ISNULL(:Diag_id,0)
								{$add_where}
						";
						$result = $this->getFirstResultFromQuery($query, array_merge($add_params, array(
							'EvnVizitPL_id' => $this->id,
							'EvnVizitPL_setDate' => $this->setDate,
							'Lpu_id' => $this->Lpu_id,
							'LpuSection_id' => $this->LpuSection_id,
							'MedPersonal_id' => $this->MedPersonal_id,
							'PayType_id' => $this->PayType_id,
							'Person_id' => $this->Person_id,
							'Diag_id'	=> $this->Diag_id
						)));
						if ( false === $result ) {
							throw new Exception('Ошибка при контроле двойственности посещений пациентов', 500);
						}
						if ( $result > 0 ) {
							if ( $double_vizit_control == 2 ) {
								$this->_saveResponse['ignoreParam'] = "ignoreDayProfileDuplicateVizit";
								$this->_saveResponse['Alert_Msg'] = 'Данное посещение не войдет в реестр на оплату, как повторное посещение<br/>по одной специальности за один день (кол-во двойных записей: '. $result .'). Продолжить сохранение?';
								throw new Exception('YesNo');
							}
							else if ( $double_vizit_control == 3 ) {
								throw new Exception("Данное посещение не войдет в реестр на оплату, как повторное посещение<br/>по одной специальности за один день (кол-во двойных записей: ". $result .")");
							}
						}
						break;
				}
			}
		}
	}

	/**
	 * Получение дублей до сохранения
	 */
	function _getEvnVizitPLOldDoubles($data) {
		$add_where = $fadd_field = '';

		if (empty($data['EvnVizitPL_id'])) {
			return array(); // если случай ещё не был сохранён, то и дублей не было
		}

		$parentAlias = 'EvnPL';
		$params = array('EvnVizitPL_id' => $data['EvnVizitPL_id']);
		
		// Получаем значения дублей
		$query = "
			select 
				top 1
					{$this->primaryKey()} as EvnVizitPL_id,
					Lpu_id,
					Person_id,
					convert(varchar(10), {$this->tableName()}_setDate, 120) as setDate,
					{$fadd_field}
					MedStaffFact_id,
					LpuSectionProfile_id,
					PayType_id
			from {$this->viewName()} EVPL with (nolock)
			where
				EVPL.{$this->primaryKey()} = :EvnVizitPL_id
		";
		$result = $this->queryResult($query, array(
			'EvnVizitPL_id' => $data['EvnVizitPL_id']
		));
		if (!empty($result[0]['EvnVizitPL_id'])) {
			$params = $result[0];
		} else {
			throw new Exception("Ошибка получения данных по случаю");
		}
		
		if ( $this->evnClassId == 13 ) {
			$parentAlias = 'EvnPLStom';
			if (!empty($result[0]['Tooth_id'])) {
				$add_where .= " and :Tooth_id = EVPLD.Tooth_id";
			} else {
				$add_where .= " and EVPLD.Tooth_id is null";
			}
			$fadd_field = 'Tooth_id,';
		}

		$join = "";
		if (getRegionNick() == 'ufa') {
			$msfd_filter = " and MSF.LpuSectionProfile_id = MSFD.LpuSectionProfile_id and MSF.MedPersonal_id = MSFD.MedPersonal_id";
		} else {
			$msfd_filter = " and MSF.MedSpecOms_id = MSFD.MedSpecOms_id";
			if (getRegionNick() == 'perm' && $this->evnClassId == 13) {
				$msfd_filter .= " and (MSF.MedSpecOms_id <> 73 or :LpuSectionProfile_id = EVPLD.LpuSectionProfile_id)"; // специальность не 171 или профили совпрадают
			}
			$join .= " inner join v_{$parentAlias} EPLD with (nolock) on EPLD.{$parentAlias}_id = EVPLD.{$this->tableName()}_pid
				and EPLD.{$parentAlias}_IsFinish = 2";
		}

		$add_field = '';
		if (getRegionNick() == 'perm') {
			$add_field .= "
				,case when EVPLD.{$this->tableName()}_setDate >= '2018-01-01' and '2018-01СверхПодуш' in (
					select vt.VolumeType_Code
					from v_AttributeVision avis (nolock)
					inner join v_VolumeType vt with(nolock) on vt.VolumeType_id = avis.AttributeVision_TablePKey
					inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
					where avis.AttributeVision_TableName = 'dbo.VolumeType'
					and avis.AttributeVision_IsKeyValue = 2
					and av.AttributeValue_ValueIdent = UC.UslugaComplex_id
					and av.AttributeValue_begDate <= EVPLD.{$this->tableName()}_setDate
					and (av.AttributeValue_endDate is null or av.AttributeValue_endDate > EVPLD.{$this->tableName()}_setDate)
				) then 1 else 0 end as isSverhPodush
			";
		}

		$query = "
			select
				EVPLD.{$this->primaryKey()} as EvnVizitPL_id,
				EVPLD.{$this->tableName()}_pid as EvnVizitPL_pid,
				EVPLD.VizitPLDouble_id,
				UC.UslugaComplex_Code
				{$add_field}
			from {$this->viewName()} EVPLD with (nolock)
				{$join} 
				left join v_UslugaComplex UC with(nolock) on UC.UslugaComplex_id = EVPLD.UslugaComplex_id
				inner join v_MedStaffFact MSF with(nolock) on MSF.MedStaffFact_id = :MedStaffFact_id
				inner join v_MedStaffFact MSFD with(nolock) on MSFD.MedStaffFact_id = EVPLD.MedStaffFact_id {$msfd_filter}
				inner join v_PayType PT with (nolock) on PT.PayType_id = :PayType_id and PT.PayType_SysNick = 'oms'
				inner join v_PayType PTD with (nolock) on EVPLD.PayType_id = PTD.PayType_id and PTD.PayType_SysNick = 'oms'
			where
				:Lpu_id = EVPLD.Lpu_id and :Person_id = EVPLD.Person_id and 
				:setDate = EVPLD.{$this->tableName()}_setDate and :EvnVizitPL_id <> EVPLD.{$this->tableName()}_id  
				{$add_where}
		";
		
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение дублей посещения
	 * @throws Exception
	 */
	public function _getEvnVizitPLDoubles() {
		if ($this->payTypeSysNick == 'oms') {
			$add_where = '';
			$add_field = '';
			$add_params = array();
			$parentAlias = 'EvnPL';
			if ($this->evnClassId == 13) {
				$parentAlias = 'EvnPLStom';
				$add_where .= "
					and ISNULL(EVPL.Tooth_id, 0) = ISNULL(:Tooth_id, 0)
				";
				$add_params['Tooth_id'] = $this->Tooth_id;
			}

			$accessType = "'edit'";
			if ( empty($this->sessionParams['isMedStatUser']) && !empty($this->sessionParams['medpersonal_id']) ) {
				$accessType = "case when msf.MedPersonal_id = :MedPersonal_id then 'edit' else 'view' end";
			}

			if ($this->regionNick == 'ufa') {
				$add_where .= "
				and msf.MedPersonal_id = @MedPersonal_id
				and msf.LpuSectionProfile_id = @LpuSectionProfile_id
				";
			} else {
				$add_where .= "
				and msf.MedSpecOms_id = @MedSpecOms_id
				and EPL.{$parentAlias}_IsFinish = 2
				and EPLD.{$parentAlias}_IsFinish = 2
				";

				if (getRegionNick() == 'perm' && $this->evnClassId == 13) {
					$add_where .= " and (msf.MedSpecOms_id <> 73 or EVPL.LpuSectionProfile_id = :LpuSectionProfile_id)"; // специальность не 171 или профили совпрадают
				}
			}

			if ($this->regionNick == 'perm') {
				$add_field .= "
					,case when EVPL.{$this->tableName()}_setDate >= '2018-01-01' and '2018-01СверхПодуш' in (
						select vt.VolumeType_Code
						from v_AttributeVision avis (nolock)
						inner join v_VolumeType vt with(nolock) on vt.VolumeType_id = avis.AttributeVision_TablePKey
						inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
						where avis.AttributeVision_TableName = 'dbo.VolumeType'
						and avis.AttributeVision_IsKeyValue = 2
						and av.AttributeValue_ValueIdent = UC.UslugaComplex_id
						and av.AttributeValue_begDate <= EVPL.{$this->tableName()}_setDate
						and (av.AttributeValue_endDate is null or av.AttributeValue_endDate > EVPL.{$this->tableName()}_setDate)
					) then 1 else 0 end as isSverhPodush
				";
			}

			$query = "
				declare
					@MedPersonal_id bigint,
					@LpuSectionProfile_id bigint,
					@MedSpecOms_id bigint;
				
				select top 1
					@MedPersonal_id = MedPersonal_id,
					@LpuSectionProfile_id = LpuSectionProfile_id,
					@MedSpecOms_id = MedSpecOms_id
				from v_MedStaffFact with(nolock)
				where MedStaffFact_id = :MedStaffFact_id;

				select
					EVPL.{$this->primaryKey()} as EvnVizitPL_id,
					LS.LpuSection_Name,
					msf.Person_Fio as MedPersonal_Fio,
					convert(varchar(10), EVPL.{$this->tableName()}_setDate, 104) as EvnVizitPL_setDate,
					EVPL.{$this->tableName()}_pid as EvnVizitPL_pid,
					EVPL.VizitPLDouble_id,
					{$accessType} as accessType,
					EPL.{$parentAlias}_NumCard as EvnPL_NumCard,
					EPLD.{$parentAlias}_NumCard as EvnPLDouble_NumCard,
					UC.UslugaComplex_Code
					{$add_field}
				from {$this->viewName()} EVPL with (nolock)
					inner join v_{$parentAlias} EPL with (nolock) on EPL.{$parentAlias}_id = :EvnVizitPL_pid
					inner join v_{$parentAlias} EPLD with (nolock) on EPLD.{$parentAlias}_id = EVPL.{$this->tableName()}_pid
					inner join v_LpuSection LS with (nolock) on LS.LpuSection_id = EVPL.LpuSection_id
					inner join v_PayType PT with (nolock) on EVPL.PayType_id = PT.PayType_id and PT.PayType_SysNick = 'oms'
					inner join v_MedStaffFact msf with (nolock) on msf.MedStaffFact_id = evpl.MedStaffFact_id
					left join v_UslugaComplex UC with(nolock) on UC.UslugaComplex_id = EVPL.UslugaComplex_id
				where (1 = 1)
					and EVPL.Lpu_id = :Lpu_id
					and EVPL.Person_id = :Person_id
					and EVPL.{$this->primaryKey()} <> ISNULL(:EvnVizitPL_id, 0)
					and EVPL.{$this->tableName()}_setDate = cast(:EvnVizitPL_setDate as datetime)
					{$add_where}
			";
			$params = array_merge($add_params, array(
				'EvnVizitPL_pid' => $this->pid,
				'EvnVizitPL_id' => $this->id,
				'EvnVizitPL_setDate' => $this->setDate,
				'Lpu_id' => $this->Lpu_id,
				'MedStaffFact_id' => $this->MedStaffFact_id,
				'LpuSectionProfile_id' => $this->LpuSectionProfile_id,
				'MedPersonal_id' => $this->MedPersonal_id,
				'PayType_id' => $this->PayType_id,
				'Person_id' => $this->Person_id,
			));
			//echo getDebugSQL($query, $params);exit;
			return $this->queryResult($query, $params);
		} else {
			return array();
		}
	}

	/**
	 * Проверка места работы
	 * @throws Exception
	 */
	protected function _checkChangeMedStaffFact()
	{
		if (in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_AUTO_CREATE))
			&& empty($this->MedStaffFact_id)
		) {
			throw new Exception('Не указано место работы', 400);
		}
		// для ЕКБ При сохранении посещения должны быть следующие контроли:
		// 1) У специальности врача GroupAPP?0.
		if ( $this->regionNick == 'ekb' && in_array($this->evnClassId, array(11, 36))) {
			// эта проверка из старого метода сохранения посещения полки и осмотра ВОВ
			$query = "
				select top 1 MSOG.MedSpecOMSGROUP_APP
				from v_MedStaffFact MSF with (nolock)
					inner join r66.v_MedSpecOMSGROUP MSOG with (nolock) on MSOG.MedSpecOMS_id = msf.MedSpecOMS_id
				where MSF.MedStaffFact_id = :MedStaffFact_id
					and ISNULL(MSF.WorkData_endDate, :date) >= :date
					and ISNULL(MSOG.MedSpecOMSGROUP_begDate, :date) <= :date
					and ISNULL(MSOG.MedSpecOMSGROUP_endDate, :date) >= :date
			";
			$result = $this->db->query($query, array(
				'MedStaffFact_id' => $this->MedStaffFact_id,
				'date' => $this->setDate
			));
			if ( !is_object($result) ) {
				throw new Exception('Ошибка при выполнении запроса к базе данных', 500);
			}
			$pos = $result->result('array');
			if (count($pos) > 0) {
				if ($this->payTypeSysNick == 'oms' && $pos[0]['MedSpecOMSGROUP_APP'] == 0) {
					throw new Exception('Специальность выбранного врача не может использоваться в поликлиническом случае', 400);
				}
			} else {
				throw new Exception('Указана некорректная специальность врача', 400);
			}
		}
	}

	/**
	 * Проверка профиля
	 * @throws Exception
	 */
	protected function _checkChangeLpuSectionProfileId()
	{
		// Проверка применяется только к Пензе, только к обычным ТАП (не стомат).
		if ($this->regionNick !== 'penza' || $this->evnClassId == 13) return true;


		// Узнаем код профиля отделения в ЛПУ (больнице), в который хочет попасть пациент (текущий профиль)
		$LpuSectionProfile_Code = $this->getFirstResultFromQuery("
				SELECT TOP 1
					LpuSectionProfile_Code as Code
				FROM
					v_LpuSectionProfile with (nolock)
				WHERE
					LpuSectionProfile_id = :LpuSectionProfile_id
					", array('LpuSectionProfile_id' => $this->LpuSectionProfile_id)
		);


		// В ТАП должны быть посещения только по одному профилю. Исключение являются профили с кодом 57 и 97, которые могут быть в одном ТАП (но не с другими)
		$codesAcceptedTogether = array(57, 97);

		// Проверка на редактирование единственного посещения, если id редактируемого посещения не пустой, то добавим дополнительное условие в запрос
		$checkIfEdit = empty($this->id) ? null : 'AND VZPL.EvnVizitPL_id != :EvnVizitPL_id';


		if ( in_array($LpuSectionProfile_Code, $codesAcceptedTogether) )
		{
			// Узнаем, были ли посещения профилей отделений, кроме разрешенных вместе 57 и 97, в рамках данного ТАП (EvnVizitPL_pid)
			$result = $this->getFirstResultFromQuery("
				SELECT TOP 1
					LSP.LpuSectionProfile_Code
				FROM
					v_EvnVizitPL VZPL  with (nolock) 
				JOIN 
					v_LpuSectionProfile LSP  with (nolock) ON VZPL.LpuSectionProfile_id = LSP.LpuSectionProfile_id
				WHERE
						VZPL.EvnVizitPL_pid = :EvnVizitPL_pid AND
						LSP.LpuSectionProfile_Code != 57 AND 
						LSP.LpuSectionProfile_Code != 97
						$checkIfEdit",
				array('EvnVizitPL_pid' => $this->pid, 'EvnVizitPL_id' => $this->id)
			);

		} else
		{
			// Узнаем, были ли посещения профилей отделений, отличных от текущего профиля, в рамках данного ТАП (EvnVizitPL_pid)
			$result = $this->getFirstResultFromQuery("
				SELECT TOP 1
					LSP.LpuSectionProfile_Code
				FROM
					v_EvnVizitPL VZPL  with (nolock) 
				JOIN 
					v_LpuSectionProfile LSP  with (nolock) ON VZPL.LpuSectionProfile_id = LSP.LpuSectionProfile_id
				WHERE
						VZPL.EvnVizitPL_pid = :EvnVizitPL_pid AND 
						LSP.LpuSectionProfile_Code != :LpuSectionProfile_Code 
						$checkIfEdit",
				array('EvnVizitPL_pid' => $this->pid, 'LpuSectionProfile_Code' => $LpuSectionProfile_Code, 'EvnVizitPL_id' => $this->id)
			);
		}


		// Если не было посещений, отличных от текущего сохраняемого профиля, то проверка пройдена, иначе ошибка
		if ( $result === false )
		{
			return true;
		} else
		{
			throw new Exception('В ТАП должны быть посещения только по одному профилю');
		}



		/*if (empty($this->LpuSectionProfile_id) && $this->regionNick == 'ekb' && 11 == $this->evnClassId) {
			//учитывать в первую очередь параметр, переданный с формы
			if (!empty($this->_params['IsFinish'])) {
				$EvnPL_IsFinish = ($this->_params['IsFinish'] == 2);
			} else {
				$EvnPL_IsFinish = ($this->parent->IsFinish == 2);
			}
			if ($EvnPL_IsFinish) {
				throw new Exception('Поле "Профиль" обязательное для заполнения', 400);
			}
		}*/
	}

	/**
	 * Проверка отделения
	 * @throws Exception
	 */
	protected function _checkChangeLpuSection()
	{
		if (in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_AUTO_CREATE))
			&& empty($this->LpuSection_id)) {
			throw new Exception('Не указано отделение', 400);
		}
		//Проверка не закрыто ли отделение
		if ($this->regionNick == 'ufa'
			&& in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_AUTO_CREATE))
		) {
			//Проверяем что отделение не закрыто
			$result = $this->getFirstResultFromQuery('
				declare @curdate date = dbo.tzGetDate();

                select top 1 1 as count
                from v_LpuSection with (nolock)
                where LpuSection_id = :LpuSection_id
                    and (IsNull(LpuSection_disDate, @curdate) >= @curdate)
           ', array('LpuSection_id' => $this->LpuSection_id));
			if ($result != 1) { // Если данные не найдутся, то будет false, а false != 1
				throw new Exception('Данное отделение закрыто или не найдено в базе данных. Сохранение невозможно.', 400);
			}

			// Проверка разрешения оплаты по ОМС для отделения
			if ($this->payTypeSysNick == 'oms') {
				$this->load->model('LpuStructure_model');
				$response = $this->LpuStructure_model->getLpuUnitIsOMS(array(
					'LpuSection_id' => $this->LpuSection_id
				));
				if (!$response[0]['LpuUnit_IsOMS']) {
					throw new Exception('Данное отделение не работает по ОМС', 400);
				}
			}
		}
	}

	/**
	 * Проверки человека
	 * @throws Exception
	 */
	protected function _checkPerson()
	{
		if ( ($this->options['polka']['check_person_birthday'] === true || $this->options['polka']['check_person_birthday'] == '1') && $this->person_BirthDay instanceof DateTime) {
			$compare_result = swCompareDates($this->person_BirthDay->format('d.m.Y'), $this->setDT->format('d.m.Y'));
			// Если дата рождения больше даты посещения...
			if ( in_array($compare_result[0], array(-1)) ) {
				throw new Exception('Дата рождения пациента больше, чем дата поликлинического обслуживания. Исправьте дату посещения', 400);
			}
		}
		// Для Уфы при добавлении посещения полки, стоматки, осмотра ВОВ
		// и включенном режиме полуавтоматической идентификации...
		if ( $this->regionNick == 'ufa'
			&& $this->isNewRecord
			&& !empty($this->globalOptions['globals']['enable_semiautomatic_identification'])
		) {
			// ... производится идентификация застрахованного
			$this->load->model('PersonIdentRequest_model');
			$response = $this->PersonIdentRequest_model->doPersonIdentOnEvnSave(array(
					'Server_id' => $this->Server_id,
					'Person_id' => $this->Person_id,
					'pmUser_id' => $this->promedUserId
				),
				$this->setDate . ' ' . $this->setTime . ':00',
				$this->globalOptions);
			if ( !empty($response['errorMsg']) ) {
				throw new Exception($response['errorMsg'], 400);
			}
		}
	}

	/**
	 * Обязательность основного диагноза посещения
	 * @return bool
	 */
	protected function _isRequiredDiag()
	{
		return (
			in_array($this->regionNick, array('ekb','pskov','ufa'))
			&& in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_SET_ATTRIBUTE))
		);
	}

	/**
	 * Проверка возможности изменения основного диагноза посещения
	 * @throws Exception
	 */
	protected function _checkChangeDiag()
	{
		// Проверка заполнения поля "Диагноз"
		if ($this->_isRequiredDiag() && empty($this->Diag_id)) {
			throw new Exception('Поле "Диагноз" обязательно для заполнения', 400);
		}

		if ( isset($this->_savedData['diag_id'])
			&& in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_SET_ATTRIBUTE))
			&& $this->_isAttributeChanged('diag_id')
		) {
			$this->ignoreCheckMorbusOnko = $this->_params['ignoreCheckMorbusOnko'];
			$this->load->library('swMorbus');
			$tmp = swMorbus::onBeforeChangeDiag($this);
			if ($tmp !== true && isset($tmp['Alert_Msg'])) {
				$this->_saveResponse['ignoreParam'] = $tmp['ignoreParam'];
				$this->_saveResponse['Alert_Msg'] = $tmp['Alert_Msg'];
				throw new Exception('YesNo', 289);
			}
			$this->load->library('swPersonRegister');
			swPersonRegister::onBeforeChangeDiag($this);
		}

		if ( $this->regionNick == 'ufa' && $this->payTypeSysNick == 'oms'
			&& !empty($this->Diag_id) && !empty($this->Person_id)
		) {
			//Проверяем что если пациенту больше 17 лет диагноз должен быть по ОМС
			//Проверяем что у инотера диагноз не из территориального финансирования
			$result = $this->getFirstRowFromQuery("
				select
					case when 
					((dbo.Age2(VPSA.Person_BirthDay, dbo.tzGetDate()) > 17
						and d.DiagFinance_isOms = 1) Or
						(DIAG.Diag_Code like 'Z80.%' 
						and d.Lpu_id!=:Lpu_id
						and dbo.Age2(VPSA.Person_BirthDay, dbo.tzGetDate()) > 17 )) 
						then 1 else 2
					end as Err_DiagOMS,
					case when Terr.KLRgn_id != 2
						and d.DiagFinance_IsAlien = 1
						then 1 else 2
					end as Err_inoDiag
				from v_PersonState_all VPSA with (nolock)
				left join v_DiagFinance d with (nolock) on d.Diag_id = :Diag_id 
					and d.PersonAgeGroup_id = (case 
						when (dbo.Age2(VPSA.Person_BirthDay, dbo.tzGetDate()) > 17) 
						then 1 else 2 end)
				left join v_Diag DIAG with(nolock) on DIAG.Diag_id = :Diag_id
				left join v_Polis VP with (nolock) on VP.Polis_id = VPSA.Polis_id
				left join v_OMSSprTerr Terr with (nolock) on VP.OMSSprTerr_id = Terr.OMSSprTerr_id
				where VPSA.Person_id = :Person_id
				", array(
				'Diag_id' => $this->Diag_id,
				'Person_id' => $this->Person_id,
				'Lpu_id' => $this->Lpu_id,
			));
			if (!is_array($result)) {
				throw new Exception('Не удалось проверить диагноз. Сохранение невозможно', 400);
			}
			if ($result['Err_DiagOMS'] != 2) {
				throw new Exception('У пациента старше 17 лет диагноз не по ОМС. Сохранение невозможно', 400);
			}
			if ($result['Err_inoDiag'] != 2) {
				throw new Exception('Диагноз у инотера относится к территориальной программе финансирования. Сохранение невозможно.', 400);
			}
		}

		if ( $this->regionNick == 'perm' && $this->evnClassId == 13
			&& isset($this->_savedData['Diag_id'])
			&& in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_SET_ATTRIBUTE))
			&& $this->_isAttributeChanged('Diag_id')
		) {
			// В Перми нет кода посещения, там где он есть его не нужно учитывать
			$isEmptyEvnUslugaList = empty($this->evnUslugaList);
			if ($isEmptyEvnUslugaList) {
				throw new Exception('Невозможно изменить диагноз, необходимо удалить услуги');
			}
		}

		$resp_diag = $this->getFirstResultFromQuery("
			select top 1
				Diag_Code
			from
				v_Diag with (nolock)
			where
				Diag_id = :Diag_id
		", array(
			'Diag_id' => $this->Diag_id
		));
		if (
			(
				(
					$this->regionNick == 'ufa'
					|| ($this->regionNick == 'buryatiya' && in_array($this->scenario, array(self::SCENARIO_SET_ATTRIBUTE)))
				) && (
					$resp_diag === false
					|| $resp_diag != 'Z03.1'
				)
			)
			|| ($this->regionNick != 'ufa' && getRegionNick() != 'krym' && ($resp_diag === false || substr($resp_diag, 0, 1) == 'C' || substr($resp_diag, 0, 2) == 'D0'))
		) {
			$this->setAttribute('iszno', 1);
			$this->setAttribute('diag_spid', null);
		}
		else if (
			(
				$this->regionNick == 'ufa'
				|| ($this->regionNick == 'buryatiya' && in_array($this->scenario, array(self::SCENARIO_SET_ATTRIBUTE)))
			)
			&& $resp_diag !== false && $resp_diag == 'Z03.1'
		) {
			$this->setAttribute('iszno', 2);
		}
	}

	/**
	 * Проверка возможности изменения кода посещения
	 * @throws Exception
	 */
	protected function _checkChangeVizitCode()
	{
		if (false == $this->isUseVizitCode) {
			return true;
		}
		// Проверка заполнения поля "Код посещения"
		if ( in_array($this->regionNick, array('pskov'))
			&& in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_SET_ATTRIBUTE))
			&& empty($this->UslugaComplex_id)
		) { // Если посещение создается автоматически, то код посещения не проверяем
			throw new Exception('Поле "Код посещения" обязательно для заполнения', 400);
		}
		if ( in_array($this->regionNick, array('buryatiya', 'vologda'))
			&& $this->evnClassId != 13 // Для стоматологии поле "Код посещения" необязательно #51803
			&& in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_SET_ATTRIBUTE))
			&& empty($this->UslugaComplex_id)
		) { // Если посещение создается автоматически, то код посещения не проверяем
			throw new Exception('Поле "Код посещения" обязательно для заполнения', 400);
		}
		if ( in_array($this->regionNick, array('buryatiya'))
			&& $this->evnClassId != 13
			&& in_array($this->scenario, array(self::SCENARIO_SET_ATTRIBUTE))
			&& !empty($this->UslugaComplex_id)
			&& empty($this->parent->Lpu_did)
		) {
			$this->load->model('Person_model', 'pmodel');

			$KLRgn_id = $this->pmodel->getPersonPolisRegionId(array(
				'PersonEvn_id' => $this->PersonEvn_id,
				'Server_id' => $this->Server_id
			));

			if ($KLRgn_id == getRegionNumber()) {
				$query = "
				select top 1
					count(UCA.UslugaComplex_id) as Count
				from
					v_UslugaComplexAttribute UCA with(nolock)
					inner join v_UslugaComplexAttributeType UCAT with(nolock) on UCAT.UslugaComplexAttributeType_id = UCA.UslugaComplexAttributeType_id
				where
					UCAT.UslugaComplexAttributeType_SysNick like 'mur'
					and UCA.UslugaComplex_id = :UslugaComplex_id
			";
				$count = $this->getFirstResultFromQuery($query, array('UslugaComplex_id' => $this->UslugaComplex_id));
				if ($count === false) {
					throw new Exception('Ошибка при проверке атрибута МУР', 500);
				}
				if ($count > 0) {
					throw new Exception('В посещении указана услуга МУРа. Необходимо указать информацию о медицинской организации, выдавшей направление', 400);
				}
			}
		}
		if ( in_array($this->regionNick, array('perm'))
			&& in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_SET_ATTRIBUTE))
			&& empty($this->UslugaComplex_id)
			&& strtotime($this->setDate) >= strtotime('01.11.2015')
			&& in_array($this->payTypeSysNick, array('oms'))
		) { // Если посещение создается автоматически, то код посещения не проверяем
			throw new Exception('Поле "Код посещения" обязательно для заполнения', 400);
		}
		
		/*
		 * исключили данную проверку по задаче №177946 "Регион: Пермь. Исключить контроль на корректность указания кода посещения"
		if ( in_array($this->regionNick, array('perm')) && !empty($this->UslugaComplex_id)) {
			$FedMedSpec_id = $this->getFirstResultFromQuery("
				select top 1 FMS.MedSpec_id
				from v_MedStaffFact MSF with(nolock)
				left join v_MedSpecOms MSO with(nolock) on MSO.MedSpecOms_id = MSF.MedSpecOms_id
				left join fed.v_MedSpec FMS with(nolock) on FMS.MedSpec_id = MSO.MedSpec_id
				where MSF.MedStaffFact_id = :MedStaffFact_id
			", array(
				'MedStaffFact_id' => $this->MedStaffFact_id
			), true);
			if ($FedMedSpec_id === false) {
				throw new Exception('Ошибка при получении специальности врача');
			}
			//дата текущего посещения - дата проверки кода посещения
			$setDT = $this->setDT;
			
			//если дата последнего посещения с видом оплаты текущего посещения больше даты текущего посещения - она будет датой проверки кода посещения
			foreach($this->parent->evnVizitList as $visit) {
				if ($visit['EvnVizitPL_id'] != $this->id && $visit['EvnVizitPL_setDT'] > $setDT && $visit['PayType_id'] == $this->PayType_id) {
					$setDT = $visit['EvnVizitPL_setDT'];
				}
			}
			
			$lastEvnUslugaWithPayType = $this->getFirstResultFromQuery("
				select top 1
					EvnUsluga_setDT
				from v_EvnUsluga with (nolock)
				where EvnUsluga_rid = :Evn_id and PayType_id = :PayType_id and (EvnUsluga_IsVizitCode is null or EvnUsluga_IsVizitCode <> 2)
				order by
					EvnUsluga_setDT desc
			", array(
				'Evn_id' => $this->parent->id,
				'PayType_id' => $this->PayType_id
			), true);
			//если дата выполнения последней услуги с видом оплаты текущего посещения больше - она будет датой проверки кода посещения
			if (!empty($lastEvnUslugaWithPayType) && $lastEvnUslugaWithPayType > $setDT) {
				$setDT = $lastEvnUslugaWithPayType;
			}
			// Проверяем наличие объёма для кода посещения.
			$this->load->model('TariffVolumes_model');
			$resp = $this->TariffVolumes_model->checkVizitCodeHasVolume(array(
				'UslugaComplex_id' => $this->UslugaComplex_id,
				'Lpu_id' => $this->Lpu_id,
				'LpuSectionProfile_id' => $this->LpuSectionProfile_id,
				'FedMedSpec_id' => $FedMedSpec_id,
				'VizitClass_id' => $this->VizitClass_id,
				'VizitType_id' => $this->VizitType_id,
				'TreatmentClass_id' => $this->TreatmentClass_id,
				'isPrimaryVizit' => isset($this->IsPrimaryVizit)?$this->IsPrimaryVizit:null,
				'UslugaComplex_Date' => $setDT->format('Y-m-d'),
				'EvnClass_SysNick' => $this->evnClassSysNick,
				'PayType_SysNick' => $this->payTypeSysNick
			));
			if (!$this->isSuccessful($resp)) {
				throw new Exception($resp[0]['Error_Msg'], $resp[0]['Error_Code']);
			}
		}
		*/
		if ( $this->regionNick == 'ekb'
			&& in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_SET_ATTRIBUTE))
			&& $this->evnClassId == 11
			&& empty($this->UslugaComplex_id)&& empty($this->Mes_id)
			&& $this->payTypeSysNick != 'bud'
			&& $this->payTypeSysNick != 'dms'
		) { // Если посещение создается автоматически, то не проверяем
			throw new Exception('Обязательно для заполнения одно из полей "МЭС" или "Код посещения"', 400);
		}
		
		if(
			$this->regionNick == 'ekb'
			&& in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_SET_ATTRIBUTE))
			&& $this->evnClassId == 13
			&& !in_array($this->payTypeSysNick, array('bud', 'dms'))
			&& !empty($this->Mes_id)
			&& empty($this->UslugaComplex_id)
		){
			$this->load->model('EvnVizit_model');
			$mesEkb = $this->EvnVizit_model->loadMesOldEkbList(array(
				'Mes_id' => $this->Mes_id,
				'Mes_Codes' => json_encode(array(5538))
			));
			if(!$mesEkb){
				//Поле «Вид оплаты» отлично от «ДМС» и «Местный бюджет»  и Поле «МЭС» отлично от «5538».
				throw new Exception('Поле "Код посещения" обязательно для заполнения', 400);
			}
		}
		/*
		if ( $this->regionNick == 'ekb'
			&& in_array($this->scenario, array(self::SCENARIO_DO_SAVE))
			&& $this->evnClassId == 13
			&& (empty($this->UslugaComplex_id) || empty($this->Mes_id))
			&& $this->payTypeSysNick != 'bud'
			&& $this->payTypeSysNick != 'dms'
		) { // Если посещение создается автоматически, то не проверяем
			throw new Exception('Обязательны для заполнения поля "МЭС" и "Код посещения"', 400);
		}
		if ( $this->regionNick == 'ekb'
			&& in_array($this->scenario, array(self::SCENARIO_SET_ATTRIBUTE))
			&& $this->evnClassId == 13
			&& empty($this->UslugaComplex_id) // при установке атрибута проверяем обязательность только одного поля, иначе его вообще не выбрать, если второе не выбрано.
			&& $this->payTypeSysNick != 'bud'
			&& $this->payTypeSysNick != 'dms'
		) { // Если посещение создается автоматически, то не проверяем
			throw new Exception('Обязательны для заполнения поля "МЭС" и "Код посещения"', 400);
		}
		*/
		if (in_array($this->regionNick, array('ekb', 'perm'/*,'pskov','ufa'*/))
			&& $this->_isAttributeChanged('UslugaComplex_id')
			&& !empty($this->UslugaComplex_id)
			&& !$this->isNewRecord
		) {
			// проверям что код посещения не занесен как отдельная услуга
			$isFound = false;
			foreach ($this->evnUslugaList as $row) {
				if ($row['UslugaComplex_id'] == $this->UslugaComplex_id && 1 == $row['EvnUsluga_IsVizitCode']) {
					$isFound = true;
					break;
				}
			}
			if ($isFound) {
				throw new Exception('Код посещения присутствует в списке услуг, сохранение невозможно', 400);
			}
		}

		if ($this->regionNick == 'perm'
			&& in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_SET_ATTRIBUTE))
			&& !empty($this->UslugaComplex_id)
			&& $this->payTypeSysNick == 'oms'
		) {
			$query = "
				declare
					@Person_Age int = dbo.Age2(:Person_BirthDay, :EvnVizitPL_setDate),
					@Person_AgeDays int = datediff(day, :Person_BirthDay, :EvnVizitPL_setDate);

				select top 1
					count(UCT.UslugaComplexTariff_id) as Count
				from v_UslugaComplexTariff UCT with(nolock)
				where
					UCT.UslugaComplex_id = :UslugaComplex_id
					and UCT.PayType_id = :PayType_id
					and (
						(@Person_Age >= 18 and UCT.MesAgeGroup_id = 1)
						or (@Person_Age < 18 and UCT.MesAgeGroup_id = 2)
						or (@Person_AgeDays > 28 and UCT.MesAgeGroup_id = 3)
						or (@Person_AgeDays <= 28 and UCT.MesAgeGroup_id = 4)
						or (@Person_Age < 18 and UCT.MesAgeGroup_id = 5)
						or (@Person_Age >= 18 and UCT.MesAgeGroup_id = 6)
						or (@Person_Age < 8 and UCT.MesAgeGroup_id = 7)
						or (@Person_Age >= 8 and UCT.MesAgeGroup_id = 8)
						or (@Person_AgeDays <= 90 and UCT.MesAgeGroup_id = 9)
						or (UCT.MesAgeGroup_id is NULL)
					)
					and UCT.UslugaComplexTariff_begDate <= :EvnVizitPL_setDate
					and (UCT.UslugaComplexTariff_endDate > :EvnVizitPL_setDate or UCT.UslugaComplexTariff_endDate is null)
			";
			$tariff_count = $this->getFirstResultFromQuery($query, array(
				'Person_BirthDay' => $this->person_BirthDay,
				'EvnVizitPL_setDate' => $this->setDate,
				'UslugaComplex_id' => $this->UslugaComplex_id,
				'PayType_id' => $this->PayType_id
			));
			if ($tariff_count === false) {
				throw new Exception('Ошибка при проверке наличия тарифов.', 500);
			}
			if ($tariff_count == 0) {
				$warningFrom = $this->_params['isEmk']?'ЭМК':'Посещение';
				$this->addWarningMsg($warningFrom.': На данную услугу нет тарифа!');
			}
		}
		if ($this->regionNick == 'ufa'
			&& in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_SET_ATTRIBUTE))
			&& $this->_isAttributeChanged('UslugaComplex_id')
		) {
			// Если ОМС (или ДД), то код посещения должен быть обязательным. Иначе - нет.
			if ( in_array($this->payTypeSysNick, array('oms'/*, 'dopdisp'*/)) && empty($this->UslugaComplex_id) ) {
				throw new Exception('Поле "Код посещения" обязательно для заполнения', 400);
			}

			//Проверка соответствия оказанной услуге полу пациента
			if (!empty($this->UslugaComplex_id) && !empty($this->Person_id)) {
				$query = "
                        select
                            case
                                when VPSA.Sex_id = 1 and (
                                    select
                                        left(UslugaComplex_code,3) as Usluga
                                    from
                                        v_UslugaComplex with(nolock)
                                    where
                                        UslugaComplex_id = :UslugaComplex_id
                                ) in ('522', '622') then 1
                                else 2
                            end as Err_SexUsluga
                        from
                            v_PersonState_all VPSA with(nolock)
                        where VPSA.Person_id = :Person_id
                    ";
				$result = $this->getFirstResultFromQuery($query, array(
					'Person_id' => $this->Person_id,
					'UslugaComplex_id' => $this->UslugaComplex_id
				));
				if (empty($result)) {
					throw new Exception('Не удалось проверить соответствие оказанной услуги полу пациента. Сохранение невозможно.', 400);
				}
				if ($result == 1) {
					throw new Exception('Оказываемая услуга не соответствует полу пациента. Сохранение невозможно.', 400);
				}
			}

			// [2013-01-29 16:40]
			// Проверка кодов посещений и группы диагнозов
			// https://redmine.swan.perm.ru/issues/15258
			if ( $this->UslugaComplex_id > 0 ) {
				if (isset($this->parent)) {
					$isFinish = (2 == $this->parent->IsFinish);
					$vizitCodePart = self::vizitCodePart($this->vizitCode);

					//Проверка соответствия результата коду посещения
					if ($isFinish && in_array($vizitCodePart, self::$morbusMultyVizitCodePartList)
						&& !empty($this->parent->leaveTypeCode)
						&& !in_array($this->parent->leaveTypeCode, array('301','313','305','306','311','307','309'))
					) {
						throw new Exception('Результат лечения не соответствует коду посещения. Сохранение невозможно.', 400);
					}

					//Проверки по кодам других посещений
					$otherVizitCnt = 0;
					$diagIdList = array();
					foreach ( $this->parent->evnVizitList as $id => $row ) {
						if (isset($row['Diag_id'])) {
							$diagIdList[] = $row['Diag_id'];
						}
						if (/*!empty($this->id) &&*/ $this->id == $id) {
							continue;
						}
						$otherVizitCnt++;
						if (empty($row['UslugaComplex_Code'])) {
							continue;
						}
						// Если сохраняемое посещение профилактическое или однократное посещение по заболеванию и в рамках ТАП имеются какие-либо другие коды посещений...
						if ( in_array($vizitCodePart, self::oneVizitCodePartList()) ) {
							// ... не сохранять, выдать ошибку
							$msg = 'профилактического/консультативного посещения';
							switch ($vizitCodePart) {
								case '871':
									$msg = 'однократного посещения по заболеванию';
									break;
								case '824':
								case '825':
									$msg = 'посещения по неотложной помощи';
									break;
							}
							throw new Exception('Сохранение ' . $msg . ' невозможно, т.к. в рамках текущего ТАП имеются другие посещения', 400);
						}
						$vizitCodePartAlt = self::vizitCodePart($row['UslugaComplex_Code']);
						// Если в рамках текущего ТАП имеется профилактическое посещение или однократное посещение по заболеванию...
						if ( in_array($vizitCodePartAlt, self::oneVizitCodePartList()) ) {
							// ... не сохранять, выдать ошибку
							$msg = 'профилактического посещения';
							switch ($vizitCodePartAlt) {
								case '871':
									$msg = 'однократного посещения по заболеванию';
									break;
								case '824':
								case '825':
									$msg = 'посещения по неотложной помощи';
									break;
							}
							throw new Exception('Сохранение посещения невозможно, т.к. в рамках текущего ТАП имеется посещение с кодом ' . $msg, 400);
						}
						// Если сохраняемое посещение по заболеванию и имеется посещения не по заболеванию...
						if ( in_array($vizitCodePart, self::$morbusMultyVizitCodePartList)
							&& !in_array($vizitCodePartAlt, self::$morbusMultyVizitCodePartList)
						) {
							// ... не сохранять, выдать ошибку
							throw new Exception('Сохранение посещения с кодом по заболеванию невозможно, т.к. в рамках текущего ТАП имеются посещения не по заболеванию', 400);
						}
						// Если сохраняемое посещение любое, кроме посещения по заболеванию...
						else if ( !in_array($vizitCodePart, self::$morbusMultyVizitCodePartList)
							&& in_array($vizitCodePartAlt, self::$morbusMultyVizitCodePartList)
						) {
							// ... не сохранять, выдать ошибку
							throw new Exception('Сохранение посещения невозможно, т.к. в рамках текущего ТАП допускаются только посещения по заболеванию', 400);
						}
					}
				} else {
					throw new Exception('Не удалось прочитать ТАП. Сохранение невозможно.', 500);
				}
				// добавил проверку по #39924
				if (!$this->isNewRecord && $isFinish
					&& in_array($vizitCodePart, self::$morbusMultyVizitCodePartList)
					&& empty($otherVizitCnt)
				) {
					throw new Exception('Сохранение посещения по заболеванию в закрытом ТАП с одним посещением невозможно.', 400);
				}

				// 3) При сохранении посещения по заболеванию требуется проверить, что группа диагнозов (до точки) одинаковая для всех посещений
				if ( in_array($vizitCodePart, self::$morbusMultyVizitCodePartList) && count($diagIdList) > 0) {
					$diagIdList = implode(',', $diagIdList);
					$query = "
						select distinct pd.Diag_Code
						from v_Diag d with (nolock)
							inner join v_Diag pd with (nolock) on pd.Diag_id = d.Diag_pid
						where d.DiagLevel_id = 4
							and d.Diag_id in ({$diagIdList})
					";
					$result = $this->db->query($query);
					if ( !is_object($result) ) {
						throw new Exception('Ошибка при выполнении запроса к базе данных (получение кодов посещений в рамках текущего ТАП)', 500);
					}
					$response = $result->result('array');
					if ( is_array($response) && count($response) > 1 ) {
						throw new Exception('В одном документе случая по заболеванию может быть только одна группа диагнозов. Измените диагнозы одного или нескольких посещений', 400);
					}
				}
			}
		}
		return true;
	}

	/**
	 * @return array
	 * @throws Exception
	 */
	protected function getEvnUslugaList()
	{
		if (!isset($this->_evnUslugaList)) {
			$selectIsVizitCode = 'ISNULL(eu.EvnUsluga_IsVizitCode, 1) as EvnUsluga_IsVizitCode';
			$add_join = '';
			if ($this->regionNick == 'ufa') {
				// для Уфы правильнее определять услугу посещения по ucat.UslugaCategory_SysNick,
				// т.к. eu.EvnUsluga_IsVizitCode появился позднее
				$add_join = 'left join v_UslugaCategory ucat with (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id';
				$selectIsVizitCode = "case when ucat.UslugaCategory_SysNick = 'lpusection' then 2 else 1 end as EvnUsluga_IsVizitCode";
			}
			$result = $this->db->query("
				select
					eu.EvnUsluga_id,
					eu.UslugaComplex_id,
					uc.UslugaComplex_Code,
					{$selectIsVizitCode}
				from v_EvnUsluga eu with (nolock)
				left join v_UslugaComplex uc with (nolock) on uc.UslugaComplex_id = eu.UslugaComplex_id
				{$add_join}
				where eu.EvnUsluga_pid = :id",
				array('id' => $this->id)
			);

			if ( is_object($result) ) {
				$this->_evnUslugaList = $result->result('array');
			} else {
				throw new Exception('Ошибка при чтении услуг посещения', 500);
			}
		}
		return $this->_evnUslugaList;
	}

	/**
	 * Логика перед валидацией
	 */
	protected function _beforeValidate() {
		parent::_beforeValidate();

		// это должно отработать до валидации
		if ($this->regionNick == 'ekb' && $this->scenario == self::SCENARIO_AUTO_CREATE && in_array($this->evnClassId, array(11, 13))) {
			$query = "
				select top 1
					msf.LpuSectionProfile_id,
					Staff.PayType_id,
					pt.PayType_SysNick
				from
					v_MedStaffFact msf (nolock)
					inner join persis.WorkPlace wp (nolock) on wp.id = msf.MedStaffFact_id
					inner join persis.Staff (nolock) on Staff.id = wp.Staff_id
					left join v_PayType pt (nolock) on pt.PayType_id = Staff.PayType_id
				where
					msf.MedStaffFact_id = :MedStaffFact_id
			";
			$msf = $this->getFirstRowFromQuery($query, array(
				'MedStaffFact_id' => $this->MedStaffFact_id
			));
			if (!empty($msf['PayType_id'])) {
				$this->setAttribute('paytype_id', $msf['PayType_id']);
				$this->PayType_id = $msf['PayType_id'];
				$this->payTypeSysNick = $msf['PayType_SysNick'];
			}
			if ($this->evnClassId == 11) {
				if (!empty($msf['LpuSectionProfile_id'])) {
					$this->setAttribute('lpusectionprofile_id', $msf['LpuSectionProfile_id']);
				} else if (isset($this->lpuSectionData['LpuSectionProfile_Code']) && $this->lpuSectionData['LpuSectionProfile_Code'] > 0) {
					$this->setAttribute('lpusectionprofile_id', $this->lpuSectionData['LpuSectionProfile_id']);
				}
			}
		}
		
		if ($this->regionNick != 'ekb' && $this->scenario == self::SCENARIO_AUTO_CREATE && 11 == $this->evnClassId) {

			if($this->getRegionNick() == 'vologda' && !empty($this->parent->evnVizitList) && is_array($this->parent->evnVizitList)){
				//значение lpusectionprofile_id из прошлого посещения
				//$arr = $this->parent->evnVizitList;
				$arr = array_filter($this->parent->evnVizitList, function($vizit){
					return (!empty($vizit['EvnVizitPL_id']));
				});
				usort($arr, function($a, $b){
					//return (time($a['EvnVizitPL_setDT']) - time($b['EvnVizitPL_setDT']));
					return ($a['EvnVizitPL_setDT'] > $b['EvnVizitPL_setDT']) ? -1 : 1;
				});
				$lastVisit = reset($arr);
				$firstVisit = end($arr);
				
				$this->load->model('LpuStructure_model');
				//полученим профили отделения (основной и дополнительные) из рабочего места текущего пользователя.
				$lpuSectionLpuSectionProfile = $this->LpuStructure_model->getLpuStructureProfileAll(array('LpuSection_id' => $this->LpuSection_id));
				$userProfileID = array();
				if(!empty($lastVisit['LpuSectionProfile_id']) && $lpuSectionLpuSectionProfile && is_array($lpuSectionLpuSectionProfile) && count($lpuSectionLpuSectionProfile)>0){
					foreach ($lpuSectionLpuSectionProfile as $row) {
						if(!empty($row['LpuSectionProfile_id'])) $userProfileID[] = $row['LpuSectionProfile_id'];
					}
					// если у пользователя существует профиль предыдущего
					if(in_array($lastVisit['LpuSectionProfile_id'], $userProfileID)){
						$this->setAttribute('lpusectionprofile_id', $lastVisit['LpuSectionProfile_id']);
					}
				}
			}

			if (empty($this->LpuSectionProfile_id) && isset($this->lpuSectionData['LpuSectionProfile_Code']) && $this->lpuSectionData['LpuSectionProfile_Code'] > 0) {
				$this->setAttribute('lpusectionprofile_id', $this->lpuSectionData['LpuSectionProfile_id']);
			}
		}

		$this->_setEvnVizitPLDoubles();
	}
	
	/**
	 * Простановка признака дубля
	 */
	function _setEvnVizitPLDoubles() {
		
		if (getRegionNick() == 'perm' && in_array($this->evnClassId, array(11, 13)) && $this->parent->IsFinish == 2 && $this->scenario != self::SCENARIO_DELETE) {
			// 1. ищем дубли до сохранения, если их не более двух, то дублирование надо будет снять
			$resp_double = $this->_getEvnVizitPLOldDoubles(array(
				'EvnVizitPL_id' => $this->id
			));

			$existsVizitPLDoubleYes = false;
			$oldDoubles = array();
			foreach($resp_double as $one_double) {
				$oldDoubles[$one_double['EvnVizitPL_id']] = $one_double;
				if ($one_double['VizitPLDouble_id'] == 1) {
					$existsVizitPLDoubleYes = true;
				}
			}

			$isSverhPodush = false;
			if ($this->setDT >= date_create('2018-01-01')) {
				$query = "
					select
						count(*) as cnt
					from
						v_AttributeVision avis (nolock)
						inner join v_VolumeType vt with(nolock) on vt.VolumeType_id = avis.AttributeVision_TablePKey
						inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
						inner join v_Attribute a (nolock) on a.Attribute_id = av.Attribute_id
						inner join v_UslugaComplex uc with(nolock) on uc.UslugaComplex_id = av.AttributeValue_ValueIdent
					where
						vt.VolumeType_Code = '2018-01СверхПодуш'
						and avis.AttributeVision_TableName = 'dbo.VolumeType'
						and avis.AttributeVision_IsKeyValue = 2
						and av.AttributeValue_ValueIdent = :UslugaComplex_id
						and av.AttributeValue_begDate <= :EvnVizitPL_setDate
						and (av.AttributeValue_endDate is null or av.AttributeValue_endDate > :EvnVizitPL_setDate)
				";
				$params = array(
					'UslugaComplex_id' => $this->UslugaComplex_id,
					'EvnVizitPL_setDate' => $this->setDate
				);
				$cnt = $this->getFirstResultFromQuery($query, $params);
				if ($cnt > 0) {
					$isSverhPodush = true;
				}
			}

			// 2. ищем новые дубли
			$this->_doubles = $this->_getEvnVizitPLDoubles();
			$IsOtherDouble = false;
			$IsThisDouble = false;
			if (count($this->_doubles) > 0) {
				foreach($this->_doubles as $double) {
					if ($double['isSverhPodush']) {
						continue;
					}
					if ($double['EvnVizitPL_pid'] != $this->pid) {
						$IsOtherDouble = true;
					} else {
						$IsThisDouble = true;
					}
				}
				if ($IsOtherDouble && !$isSverhPodush) {
					$this->setAttribute('isotherdouble', 2);
				} else {
					$this->setAttribute('isotherdouble', 1);
				}
			} else {
				$this->setAttribute('isotherdouble', 1);
			}

			if (!$IsThisDouble) {
				$this->setAttribute('vizitpldouble_id', null);
			}

			// 3. снимаем/ставим признаки дубля
			foreach($this->_doubles as $double) {
				if ($isSverhPodush) {
					continue;
				}
				if (!empty($oldDoubles[$double['EvnVizitPL_id']])) {
					unset($oldDoubles[$double['EvnVizitPL_id']]);
				}
				if ($IsOtherDouble && !$double['isSverhPodush']) {
					$this->db->query("update EvnVizitPL with (rowlock) set EvnVizitPL_IsOtherDouble = 2 where EvnVizitPL_id = :EvnVizitPL_id", array(
						'EvnVizitPL_id' => $double['EvnVizitPL_id']
					));
				} else {
					$this->db->query("update EvnVizitPL with (rowlock) set EvnVizitPL_IsOtherDouble = 1 where EvnVizitPL_id = :EvnVizitPL_id", array(
						'EvnVizitPL_id' => $double['EvnVizitPL_id']
					));
				}
			}
			if (count($oldDoubles) == 1) { // снимаем признак, только если он один остался
				foreach ($oldDoubles as $double) {
					$this->db->query("update EvnVizitPL with (rowlock) set EvnVizitPL_IsOtherDouble = 1, VizitPLDouble_id = null where EvnVizitPL_id = :EvnVizitPL_id", array(
						'EvnVizitPL_id' => $double['EvnVizitPL_id']
					));
				}
			} else if (count($oldDoubles) > 1) {
				// Если у посещения было указано «Выгружать в реестр» «Да» и в группе нет других посещений, у которых указано «Да»,
				// то у остальных посещений изменяется значение «Выгружать в реестр» на »/«Не определено»
				if (!empty($this->_savedData['vizitpldouble_id']) && $this->_savedData['vizitpldouble_id'] == 1 && !$existsVizitPLDoubleYes) {
					foreach ($oldDoubles as $double) {
						$this->db->query("update EvnVizitPL with (rowlock) set VizitPLDouble_id = 3 where EvnVizitPL_id = :EvnVizitPL_id", array(
							'EvnVizitPL_id' => $double['EvnVizitPL_id']
						));
					}
				}
			}
		}

		//https://redmine.swan.perm.ru/issues/116299
		$isAllowedCode = function($code) {
			return (
				in_array(substr($code, 1, 2), array(74, 75, 76, 77)) ||
				in_array(substr($code, 3, 3), array(874, 875, 822, 832, 833, 838, 839, 840, 862, 873, 857)) ||
				in_array(substr($code, 1, 5), array(22895, 13896, 13897, 22905, 22906)) ||
				in_array(substr($code, 1, 5), ['13896','13897','13929','13930','21887','21894','74730','74752',
					'74744','74731','74732','74753','74745','74733','74782','74734','74735','74746','74755','74754','74736',
					'74747','74737','74748','74738','74749','74739','74756','74750','74875','74740','74757','74743','74741',
					'74742','74758','74751','77851','76849','76850','22905','22906','22895','75779','75770','75759','75760',
					'75780','75771','75761','75772','75762','75773','75774','75764','75775','75763','75765','75766','75776',
					'75769','75781','75767','75777','75768','75778','22944','02783','02784','02785','81786','81787','81788',
					'21789','21790','76791','76792','76793','76794','76795','05936','05833','05822','05857','21796','21797',
					'21798','21799','21728','21729','21724','21725','21726','21727'
				])
			);
		};
		if (
			$this->scenario == self::SCENARIO_DELETE
			&& in_array($this->regionNick, ['perm', 'ufa'])
			&& in_array($this->evnClassId, array(11, 13))
			&& ($this->regionNick != 'perm' || $this->IsOtherDouble == 2)
		) {
			// 1. ищем дубли до удаления, если их не более двух, то дублирование надо будет снять
			$resp_double = $this->_getEvnVizitPLOldDoubles(array(
				'EvnVizitPL_id' => $this->id
			));

			$oldDoubles = array();
			foreach($resp_double as $one_double) {
				$oldDoubles[$one_double['EvnVizitPL_id']] = $one_double;
			}

			$this->setAttribute('isotherdouble', 1);
			$this->setAttribute('vizitpldouble_id', null);

			if (count($oldDoubles) == 1) {
				foreach($oldDoubles as $double) {
					$this->db->query("
						update EvnVizitPL with (rowlock) set EvnVizitPL_IsOtherDouble = 1, VizitPLDouble_id = null where EvnVizitPL_id = :EvnVizitPL_id
					", array(
						'EvnVizitPL_id' => $double['EvnVizitPL_id']
					));
				}
			}
		}
		if (getRegionNick() == 'ufa' && in_array($this->evnClassId, array(11, 13)) && $this->scenario != self::SCENARIO_DELETE) {
			// 1. ищем дубли до сохранения, если их не более двух, то дублирование надо будет снять
			$resp_double = $this->_getEvnVizitPLOldDoubles(array(
				'EvnVizitPL_id' => $this->id
			));

			$existsVizitPLDoubleYes = false;
			$oldDoubles = array();
			foreach($resp_double as $one_double) {
				$oldDoubles[$one_double['EvnVizitPL_id']] = $one_double;
				if ($one_double['VizitPLDouble_id'] == 1) {
					$existsVizitPLDoubleYes = true;
				}
			}

			// 2. ищем новые дубли
			if (!$isAllowedCode($this->vizitCode)) {
				$this->_doubles = $this->_getEvnVizitPLDoubles();
			}
			$IsOtherDouble = false;
			if (count($this->_doubles) > 0) {
				foreach($this->_doubles as $double) {
					if (!$isAllowedCode($double['UslugaComplex_Code'])) {
						if ($double['EvnVizitPL_pid'] != $this->pid) {
							$IsOtherDouble = true;
						}
					}
				}
				if ($IsOtherDouble) {
					$this->setAttribute('isotherdouble', 2);
				} else {
					$this->setAttribute('isotherdouble', 1);
				}
			} else {
				$this->setAttribute('isotherdouble', 1);
			}

			// 3. снимаем/ставим признаки дубля
			foreach($this->_doubles as $key => $double) {
				if (!empty($oldDoubles[$double['EvnVizitPL_id']])) {
					unset($oldDoubles[$double['EvnVizitPL_id']]);
				}
				if (!$isAllowedCode($double['UslugaComplex_Code'])) {
					if ($IsOtherDouble) {
						$this->db->query("update EvnVizitPL with (rowlock) set EvnVizitPL_IsOtherDouble = 2, VizitPLDouble_id = 1 where EvnVizitPL_id = :EvnVizitPL_id", array(
							'EvnVizitPL_id' => $double['EvnVizitPL_id']
						));
					} else {
						$this->db->query("update EvnVizitPL with (rowlock) set EvnVizitPL_IsOtherDouble = 1, VizitPLDouble_id = 1 where EvnVizitPL_id = :EvnVizitPL_id", array(
							'EvnVizitPL_id' => $double['EvnVizitPL_id']
						));
					}
				} else {
					// эти могут дублироваться, снимаем с них признак
					unset($this->_doubles[$key]);
					$this->db->query("update EvnVizitPL with (rowlock) set EvnVizitPL_IsOtherDouble = 1, VizitPLDouble_id = null where EvnVizitPL_id = :EvnVizitPL_id", array(
						'EvnVizitPL_id' => $double['EvnVizitPL_id']
					));
				}
			}

			if (count($this->_doubles) > 0) {
				$this->setAttribute('vizitpldouble_id', 1);
			} else {
				$this->setAttribute('vizitpldouble_id', null);
			}

			if (count($oldDoubles) == 1) { // снимаем признак, только если он один остался
				foreach ($oldDoubles as $double) {
					$this->db->query("update EvnVizitPL with (rowlock) set EvnVizitPL_IsOtherDouble = 1, VizitPLDouble_id = null where EvnVizitPL_id = :EvnVizitPL_id", array(
						'EvnVizitPL_id' => $double['EvnVizitPL_id']
					));
				}
			} else if (count($oldDoubles) > 1) {
				// Если у посещения было указано «Выгружать в реестр» «Да» и в группе нет других посещений, у которых указано «Да»,
				// то у остальных посещений изменяется значение «Выгружать в реестр» на »/«Не определено»
				if (!empty($this->_savedData['vizitpldouble_id']) && $this->_savedData['vizitpldouble_id'] == 1 && !$existsVizitPLDoubleYes) {
					foreach ($oldDoubles as $double) {
						$VizitPLDouble_id = 3;
						if (!$isAllowedCode($double['UslugaComplex_Code'])) {
							$VizitPLDouble_id = null;
						}
						$this->db->query("update EvnVizitPL with (rowlock) set VizitPLDouble_id = :VizitPLDouble_id where EvnVizitPL_id = :EvnVizitPL_id", array(
							'EvnVizitPL_id' => $double['EvnVizitPL_id'],
							'VizitPLDouble_id' => $VizitPLDouble_id,
						));
					}
				}
			}
		}
	}

	/**
	 * Определение MedicalCareKindId
	 */
	function setMedicalCareKindId() {
		// для Екб.
		if (getRegionNick() == 'ekb') {
			$LSMedicalCareKind_id = null;
			$Diag_Code = null;
			$FedMedSpec_Code = null;
			$FedMedSpecParent_Code = null;

			if (!empty($this->Diag_id)) {
				$resp_diag = $this->queryResult("
						select
							Diag_Code
						from
							v_Diag with (nolock)
						where
							Diag_id = :Diag_id
					", array(
					'Diag_id' => $this->Diag_id
				));

				if (!empty($resp_diag[0]['Diag_Code'])) {
					$Diag_Code = $resp_diag[0]['Diag_Code'];
				}
			}

			if (!empty($this->MedStaffFact_id)) {
				$resp_msf = $this->queryResult("
						select
							MSF.MedStaffFact_id,
							FMS.MedSpec_Code as FedMedSpec_Code,
							FMSP.MedSpec_Code as FedMedSpecParent_Code
						from
							v_MedStaffFact MSF with (nolock)
							left join v_MedSpecOms MSO with (nolock) on MSO.MedSpecOms_id = MSF.MedSpecOms_id
							left join fed.v_MedSpec FMS with (nolock) on FMS.MedSpec_id = MSO.MedSpec_id
							left join fed.v_MedSpec FMSP with (nolock) on FMSP.MedSpec_id = FMS.MedSpec_pid
						where
							MSF.MedStaffFact_id = :MedStaffFact_id
					", array(
					'MedStaffFact_id' => $this->MedStaffFact_id
				));

				if (!empty($resp_msf[0]['MedStaffFact_id'])) {
					$FedMedSpec_Code = $resp_msf[0]['FedMedSpec_Code'];
					$FedMedSpecParent_Code = $resp_msf[0]['FedMedSpecParent_Code'];
				}
			}

			if (!empty($this->LpuSection_id)) {
				$resp_ls = $this->queryResult("
						select
							LS.LpuSection_id,
							LSL.MedicalCareKind_id
						from
							v_LpuSection LS with (nolock)
							outer apply (
								select top 1 t2.MedicalCareKind_id, t2.MedicalCareKind_Code
								from r66.v_LpuSectionLink t1 with (nolock)
									left join fed.v_MedicalCareKind t2 with (nolock) on t2.MedicalCareKind_id = t1.MedicalCareKind_id
								where t1.LpuSection_id = LS.LpuSection_id
							) LSL
						where
							LS.LpuSection_id = :LpuSection_id
							and LSL.MedicalCareKind_Code not in ('4', '11', '12', '13')
					", array(
					'LpuSection_id' => $this->LpuSection_id
				));

				if (!empty($resp_ls[0]['LpuSection_id'])) {
					$LSMedicalCareKind_id = $resp_ls[0]['MedicalCareKind_id'];
				}
			}

			if ($this->payTypeSysNick == 'bud' && !empty($LSMedicalCareKind_id)) {
				$this->setAttribute('MedicalCareKind_id', $LSMedicalCareKind_id);
			} else if ($Diag_Code == 'Z51.5') {
				$this->setAttribute('MedicalCareKind_id', $this->getFirstResultFromQuery("select MedicalCareKind_id from fed.v_MedicalCareKind (nolock) where MedicalCareKind_Code = '4'"));
			} else if (!empty($FedMedSpecParent_Code)) {
				if ($FedMedSpecParent_Code == '204') { // если HIGH = 204;
					$this->setAttribute('MedicalCareKind_id', $this->getFirstResultFromQuery("select MedicalCareKind_id from fed.v_MedicalCareKind (nolock) where MedicalCareKind_Code = '11'"));
				} else { // если HIGH=0;
					if (!empty($FedMedSpec_Code) && in_array($FedMedSpec_Code, array('16', '22', '27'))) {
						$this->setAttribute('MedicalCareKind_id', $this->getFirstResultFromQuery("select MedicalCareKind_id from fed.v_MedicalCareKind (nolock) where MedicalCareKind_Code = '12'"));
					} else {
						$this->setAttribute('MedicalCareKind_id', $this->getFirstResultFromQuery("select MedicalCareKind_id from fed.v_MedicalCareKind (nolock) where MedicalCareKind_Code = '13'"));
					}
				}
			} else {
				$this->setAttribute('MedicalCareKind_id', null);
			}
		}
	}

	/**
	 * Проверки и другая логика перед сохранением объекта
	 * @param array $data Массив входящих параметров
	 * @throws Exception
	 */
	protected function _beforeSave($data = array())
	{
		parent::_beforeSave($data);

		$isStom = (13 == $this->evnClassId);

		if (getRegionNick() == 'kareliya' && !empty($this->VizitType_id)) {
			// проверяем значение VizitType_id
			$resp = $this->queryResult("
				select top 1
					VizitType_id
				from
					v_VizitType (nolock)
				where
					VizitType_id = :VizitType_id
					and ISNULL(VizitType_begDT, :EvnVizitPL_setDate) <= :EvnVizitPL_setDate
					and ISNULL(VizitType_endDT, :EvnVizitPL_setDate) >= :EvnVizitPL_setDate
			", array(
				'VizitType_id' => $this->VizitType_id,
				'EvnVizitPL_setDate' => $this->setDate
			));

			if (empty($resp[0]['VizitType_id'])) {
				throw new Exception('В поле "Цель посещения" выбрано закрытое значение.', 400);
			}
		}

		if ($isStom && !empty($this->diag_id) && empty($this->deseasetype_id)) {
			// если диагноз не из группы Z
			$resp_diag = $this->queryResult("select Diag_Code from v_Diag (nolock) where Diag_id = :Diag_id", array('Diag_id' => $this->diag_id));
			if (empty($resp_diag[0]['Diag_Code']) || mb_substr($resp_diag[0]['Diag_Code'], 0, 1) != 'Z') {
				throw new Exception('Поле "Характер заболевания" обязательно для заполнения при заполненном диагнозе.', 400);
			}
		}

		if ( !$isStom && !in_array(getRegionNick(), array('ufa', 'astra')) ) {
			// пациентов >= 18 лет нельзя принять в детском отделении
			if (!$this->hasPreviusChildVizit() && $this->person_Age >= 18 && $this->LpuSectionData['LpuSectionAge_id'] == 2) {
				throw new Exception('Возрастная группа отделения не соответствуют возрасту пациента. Приём невозможен.', 400);
			}
		}

		if (
			$this->regionNick == 'vologda' && in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_SET_ATTRIBUTE))
			&& $this->TreatmentClass_id == 4 && empty($this->PersonDisp_id)
		) {
			throw new Exception('При виде обращения "Диспансерное наблюдение (Заболевание)" поле "Карта дис. учета" обязательна для заполнения.', __LINE__);
		}

		if (
			$isStom === false && in_array(getRegionNick(), array('perm')) && in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_SET_ATTRIBUTE))
			&& in_array($this->serviceTypeSysNick, array('home', 'ahome', 'neotl')) && $this->_params['ignoreCheckB04069333'] == false
			&& $this->setDT->getTimestamp() < strtotime('01.05.2018')
		) {
			$EvnUsluga_id = $this->getFirstResultFromQuery("
				select top 1 t1.EvnUsluga_id
				from v_EvnUsluga t1 with (nolock)
					inner join v_UslugaComplex t2 with (nolock) on t2.UslugaComplex_id = t1.UslugaComplex_id
				where t1.EvnUsluga_pid = :EvnVizitPL_id
					and t2.UslugaComplex_Code = 'B04.069.333'
			", array('EvnVizitPL_id' => $this->id));

			if ( empty($EvnUsluga_id) ) {
				$UslugaComplex_Name = $this->getFirstResultFromQuery("
					select top 1 UslugaComplex_Name
					from v_UslugaComplex with (nolock)
					where UslugaComplex_Code = 'B04.069.333'
						and (UslugaComplex_begDT is null or UslugaComplex_begDT <= :setDate)
						and (UslugaComplex_endDT is null or UslugaComplex_endDT >= :setDate)
				", array('setDate' => $this->setDate), null);

				$this->_saveResponse['ignoreParam'] = 'ignoreCheckB04069333';
				$this->_saveResponse['Alert_Msg'] = 'Добавить в посещение услугу B04.069.333 ' . /*(!empty($UslugaComplex_Name) ? $UslugaComplex_Name :*/ '«Оказание неотложной помощи вне медицинской организации (на дому)»?';
				throw new Exception('YesNo', 131);
			}
		}

		//#89803 объём 2015-06Проф_МО удален. Проверка больше не нужна
		/*if ( !$isStom && getRegionNick() == 'perm' && empty($this->_params['ignoreLpuSectionProfileVolume']) ) {
			// проверяем наличие объёмов 2015-06Проф_МО для Профиля
			$resp_vol = $this->queryResult("
				declare @AttributeVision_TablePKey bigint = (select top 1 VolumeType_id from v_VolumeType (nolock) where VolumeType_Code = '2015-06Проф_МО')
				declare @LpuSectionProfile_id bigint = isnull(:LpuSectionProfile_id, (
					select top 1 LpuSectionProfile_id from LpuSection (nolock) where LpuSection_id = :LpuSection_id
				));

				SELECT  TOP 1
					av.AttributeValue_id
				FROM
					v_AttributeVision avis (nolock)
					inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
					inner join v_Attribute a (nolock) on a.Attribute_id = av.Attribute_id
					cross apply(
						select top 1
							av2.AttributeValue_ValueIdent
						from
							v_AttributeValue av2 (nolock)
							inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
						where
							av2.AttributeValue_rid = av.AttributeValue_id
							and a2.Attribute_TableName = 'dbo.Lpu'
							and ISNULL(av2.AttributeValue_ValueIdent,:Lpu_id) = :Lpu_id
					) MOFILTER
				WHERE
					avis.AttributeVision_TableName = 'dbo.VolumeType'
					and avis.AttributeVision_TablePKey = @AttributeVision_TablePKey
					and avis.AttributeVision_IsKeyValue = 2
					and ISNULL(av.AttributeValue_begDate, :EvnVizitPL_setDT) <= :EvnVizitPL_setDT
					and ISNULL(av.AttributeValue_endDate, :EvnVizitPL_setDT) >= :EvnVizitPL_setDT
					and av.AttributeValue_ValueIdent = @LpuSectionProfile_id
			", array(
				'LpuSection_id' => $this->LpuSection_id,
				'LpuSectionProfile_id' => $this->LpuSectionProfile_id,
				'Lpu_id' => $this->Lpu_id,
				'EvnVizitPL_setDT' => $this->setDT->format('Y-m-d H:i:s')
			));

			if (empty($resp_vol[0]['AttributeValue_id'])) {
				$this->_saveResponse['Alert_Msg'] = "Внимание! Выбранный профиль не входит в список разрешённых по МО. Продолжить сохранение?";
				//отменяем сохранение, пользователю показываем Alert_Msg и выводим вопрос: Продолжить сохранение?
				throw new Exception('YesNo', 113);
			}
		}*/

		// #167294 объём 2019-НМП_УслугиПосещения
		if ( !$isStom && getRegionNick() == 'pskov' && !empty($this->UslugaComplex_id) ) {

			$join = "";
			$filter = "";
			if ($this->TreatmentClass_id == 2) {
				$join .= "
					outer apply(
						select top 1
							av2.AttributeValue_ValueIdent
						from
							v_AttributeValue av2 (nolock)
							inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
						where
							av2.AttributeValue_rid = av.AttributeValue_id
							and a2.Attribute_TableName = 'dbo.MedSpecOms'
					) MSOFILTER
					outer apply(
						select top 1
							av2.AttributeValue_ValueIdent
						from
							v_AttributeValue av2 (nolock)
							inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
						where
							av2.AttributeValue_rid = av.AttributeValue_id
							and a2.Attribute_TableName = 'dbo.LpuSectionProfile'
					) LSPFILTER
				";

				$filter .= "
					and COALESCE(MSOFILTER.AttributeValue_ValueIdent, @MedSpecOms_id, 0) = ISNULL(@MedSpecOms_id, 0)
					and COALESCE(LSPFILTER.AttributeValue_ValueIdent, @LpuSectionProfile_id, 0) = COALESCE(@LpuSectionProfile_id, LSPFILTER.AttributeValue_ValueIdent, 0)
				";
			}

			$resp_vol = $this->queryResult("
				declare @AttributeVision_TablePKey bigint = (select top 1 VolumeType_id from v_VolumeType (nolock) where VolumeType_Code = '2019-НМП_УслугиПосещения');
				declare @LpuSectionProfile_id bigint = isnull(:LpuSectionProfile_id, (
					select top 1 LpuSectionProfile_id from v_LpuSection (nolock) where LpuSection_id = :LpuSection_id
				));
				declare @MedSpecOms_id bigint = (
					select top 1 MedSpecOms_id from v_MedStaffFact (nolock) where MedStaffFact_id = :MedStaffFact_id
				);

				select top 1
					av.AttributeValue_id
				from
					v_AttributeVision avis (nolock)
					inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
					inner join v_Attribute a (nolock) on a.Attribute_id = av.Attribute_id
					{$join}
				where
					avis.AttributeVision_TableName = 'dbo.VolumeType'
					and avis.AttributeVision_TablePKey = @AttributeVision_TablePKey
					and avis.AttributeVision_IsKeyValue = 2
					and ISNULL(av.AttributeValue_begDate, :EvnVizitPL_setDT) <= :EvnVizitPL_setDT
					and ISNULL(av.AttributeValue_endDate, :EvnVizitPL_setDT) >= :EvnVizitPL_setDT
					and av.AttributeValue_ValueIdent = :UslugaComplex_id
					{$filter}
			", array(
				'LpuSection_id' => $this->LpuSection_id,
				'LpuSectionProfile_id' => $this->LpuSectionProfile_id,
				'MedStaffFact_id' => $this->MedStaffFact_id,
				'UslugaComplex_id' => $this->UslugaComplex_id,
				'EvnVizitPL_setDT' => $this->setDT->format('Y-m-d H:i:s')
			));

			if (
				($this->TreatmentClass_id == 2 && empty($resp_vol[0]['AttributeValue_id']))
				|| ($this->TreatmentClass_id != 2 && !empty($resp_vol[0]['AttributeValue_id']))
			) {
				throw new Exception('Выбранный код посещения не соответствует виду обращения');
			}
		}

		// Проверяем есть ли услуги параклиники, которые не входят в пределы выбранных дат посещения. И есть ли в КВС услуги которые могли бы войти в данное движение refs #75644 %)
		$this->EvnUslugaLinkChange = null;

		if (!in_array(getRegionNick(), array('perm', 'kareliya', 'kz')) && $this->scenario == self::SCENARIO_DO_SAVE) {
			// ищем дату начала и дату конца ТАП
			$EvnPL_setDT = $this->setDT;
			$EvnPL_disDT = $this->setDT;
			foreach($this->parent->evnVizitList as $vizit) {
				if (empty($this->id) || $vizit['EvnVizitPL_id'] != $this->id) {
					if ($vizit['EvnVizitPL_setDT'] > $EvnPL_disDT) {
						$EvnPL_disDT = $vizit['EvnVizitPL_setDT'];
					}
					if ($vizit['EvnVizitPL_setDT'] < $EvnPL_setDT) {
						$EvnPL_setDT = $vizit['EvnVizitPL_setDT'];
					}
				}
			}

			$EvnPL_setDT = $EvnPL_setDT->format('Y-m-d H:i:s');
			$EvnPL_disDT = $EvnPL_disDT->format('Y-m-d H:i:s');

			$checkDateType = "datetime";
			if (getRegionNick() == "astra") $checkDateType = "date";

			$this->EvnUslugaLinkChange = $this->queryResult("
				declare
					@EvnPL_id bigint = :EvnPL_id,
					@EvnPL_isFinish bigint,
					@EvnPL_setDT datetime,
					@EvnPL_disDT datetime;
				
				select
					@EvnPL_isFinish = ISNULL(EvnPL_isFinish, 1),
					@EvnPL_setDT = :EvnPL_setDT,
					@EvnPL_disDT = :EvnPL_disDT
				from
					v_EvnPL with (nolock)
				where
					EvnPL_id = @EvnPL_id;
					
				SET NOCOUNT ON;
				select
					epd.EvnDirection_id,
					ep.EvnPrescr_pid
				into
					#tmp
				from
					v_EvnVizitPL evpl with (nolock)
					inner join v_EvnPrescr ep with (nolock) on ep.EvnPrescr_pid = evpl.EvnVizitPL_id
					inner join v_EvnPrescrDirection epd with (nolock) on epd.EvnPrescr_id = ep.EvnPrescr_id
				where
					evpl.EvnVizitPL_pid = @EvnPL_id;
				SET NOCOUNT OFF;
					
				select
					eup.EvnUslugaPar_id,
					D.EvnPrescr_pid,
					'unlink' as type
				from
					#tmp as D with (nolock)
					inner join v_EvnUslugaPar eup with (nolock) on eup.EvnDirection_id = D.EvnDirection_id
				where
					eup.EvnUslugaPar_pid is not null
					and ISNULL(eup.EvnUslugaPar_IsManual, 1) = 1
					and (cast(eup.EvnUslugaPar_setDT as {$checkDateType}) < cast(@EvnPL_setDT as {$checkDateType}) OR (cast(eup.EvnUslugaPar_setDT as {$checkDateType}) > cast(@EvnPL_disDT as {$checkDateType}) and @EvnPL_disDT is not null and @EvnPL_IsFinish = 2))

				union all

				select
					eup.EvnUslugaPar_id,
					D.EvnPrescr_pid,
					'link' as type
				from
					#tmp as D with (nolock)
					inner join v_EvnUslugaPar eup with (nolock) on eup.EvnDirection_id = D.EvnDirection_id
				where
					eup.EvnUslugaPar_pid is null
					and ISNULL(eup.EvnUslugaPar_IsManual, 1) = 1
					and (cast(eup.EvnUslugaPar_setDT as {$checkDateType}) >= cast(@EvnPL_setDT as {$checkDateType}) AND (cast(eup.EvnUslugaPar_setDT as {$checkDateType}) <= cast(@EvnPL_disDT as {$checkDateType}) OR @EvnPL_disDT is null OR @EvnPL_IsFinish = 1))
			", array(
				'EvnPL_id' => $this->pid,
				'EvnPL_setDT' => $EvnPL_setDT,
				'EvnPL_disDT' => $EvnPL_disDT
			));

			if (!empty($this->EvnUslugaLinkChange) && empty($this->_params['ignoreCheckEvnUslugaChange'])) {
				// выдаём YesNo
				$this->_saveResponse['ignoreParam'] = 'ignoreCheckEvnUslugaChange';
				$this->_saveResponse['Alert_Msg'] = 'Вы изменили период дат посещения пациента в отделении. Это приведет к изменению связей некоторых услуг и данного посещения. Продолжить сохранение?';
				throw new Exception('YesNo', 130);
			}
		}

		if (
			getRegionNick() != 'kz'
			&& (
				(!$isStom && !empty($this->setDT) && $this->setDT instanceof DateTime && $this->setDT->format('Y') >= 2016)
				||
				(
					$isStom
					&& (
						(empty($this->parent->setDT) && !empty($this->setDT) && $this->setDT instanceof DateTime && $this->setDT->getTimestamp() >= getEvnPLStomNewBegDate())
						|| ($this->parent->setDT instanceof DateTime && $this->parent->setDT->getTimestamp() >= getEvnPLStomNewBegDate()) // берём дату с ТАП, т.к. именно от неё зависит используемая форма ввода.
					)
				)
			)
		) {
			$xdate = strtotime('01.01.2016'); // для Перми поле появляется с 01.01.2016
			if (getRegionNick() != 'perm') {
				$xdate = getEvnPLStomNewBegDate(); // для остальных зависит от даты нового стомат.тап
			}

			if ($this->scenario == self::SCENARIO_AUTO_CREATE && getRegionNick() == 'ekb') {
				$this->setMedicalCareKindId();
			} else if ($this->scenario == self::SCENARIO_AUTO_CREATE) {
				// определяем на основе врача
				$query = "
					select top 1
						 fms.MedSpec_Code
						,fmsp.MedSpec_Code as ParentMedSpec_Code
					from
						v_MedStaffFact msf (nolock)
						left join v_MedSpecOms mso with (nolock) on mso.MedSpecOms_id = msf.MedSpecOms_id
						left join fed.v_MedSpec fms with (nolock) on fms.MedSpec_id = mso.MedSpec_id
						left join fed.v_MedSpec fmsp with (nolock) on fmsp.MedSpec_id = fms.MedSpec_pid
					where
						msf.MedStaffFact_id = :MedStaffFact_id
				";

				$resp = $this->queryResult($query, array(
					'MedStaffFact_id' => $this->MedStaffFact_id
				));

				// Если специальность врача из случая средняя, то вид мед. помощи = 11
				if (
					(!empty($resp[0]['ParentMedSpec_Code']) && $resp[0]['ParentMedSpec_Code'] == 204)
					|| (!empty($resp[0]['MedSpec_Code']) && $resp[0]['MedSpec_Code'] == 204)
				) {
					$this->MedicalCareKind_id = $this->getFirstResultFromQuery("select MedicalCareKind_id from fed.v_MedicalCareKind (nolock) where MedicalCareKind_Code = '11'");
				}
				// Если специальность врача из случая врачебная и равна 16, 22, 27 (терапевт, педиатр или ВОП), то вид мед. помощи = 12
				else if ( !empty($resp[0]['MedSpec_Code']) && in_array($resp[0]['MedSpec_Code'], array(16, 22, 27)) ) {
					$this->MedicalCareKind_id = $this->getFirstResultFromQuery("select MedicalCareKind_id from fed.v_MedicalCareKind (nolock) where MedicalCareKind_Code = '12'");
				}
				// 13 – В остальных случаях
				else {
					$this->MedicalCareKind_id = $this->getFirstResultFromQuery("select MedicalCareKind_id from fed.v_MedicalCareKind (nolock) where MedicalCareKind_Code = '13'");
				}

				if ($this->regionNick == 'ufa'
					&& $this->scenario == self::SCENARIO_AUTO_CREATE
					&& empty($this->medicalcarekind_id)
					&& !$isStom
				) {
					$query = "
						select
						MCKLSP.MedicalCareKind_id
						from v_LpuSection LS (nolock)
						inner join v_MedicalCareKindLpuSectionProfile MCKLSP (nolock) ON LS.LpuSectionProfile_id = MCKLSP.LpuSectionProfile_id
						where LS.LpuSection_id = :LpuSection_id
					";			
					$this->MedicalCareKind_id = $this->getFirstResultFromQuery($query, array('LpuSection_id' => $this->LpuSection_id));		
				}

				if ($this->regionNick == 'kareliya' && !empty($resp[0]['MedSpec_Code']) && !$isStom) {
					$query = "
						select
						MSL.MedicalCareKind_id
						from fed.v_MedSpec MS (nolock)
						inner join r10.v_MedSpecLink MSL (nolock) ON MS.MedSpec_id = MSL.MedSpec_id
						inner join fed.v_MedicalCareKind MCK (nolock) ON MSL.MedicalCareKind_id = MCK.MedicalCareKind_id
						where MS.MedSpec_Code = :MedSpec_Code and MCK.MedicalCareKind_Code in ('11','12','13','4')
					";			
					$this->MedicalCareKind_id = $this->getFirstResultFromQuery($query, array('MedSpec_Code' => $resp[0]['MedSpec_Code']));
				}

				$this->setAttribute('MedicalCareKind_id', $this->MedicalCareKind_id);
			} else if (
				empty($this->MedicalCareKind_id) &&
				(
					$this->evnClassId != 13
					|| ($this->parent->setDT instanceof DateTime && $this->parent->setDT->getTimestamp() >= $xdate)
				)
				&& (
					$this->regionNick != 'ufa'
					|| $this->payTypeSysNick == 'oms'
				)
			) { // @task https://redmine.swan.perm.ru/issues/89400
				if ($this->regionNick == 'kareliya') {
					// проверяем есть ли специальность у врача
					$query = "
						select top 1
							fms.MedSpec_Code
						from
							v_MedStaffFact msf (nolock)
							left join v_MedSpecOms mso with (nolock) on mso.MedSpecOms_id = msf.MedSpecOms_id
							left join fed.v_MedSpec fms with (nolock) on fms.MedSpec_id = mso.MedSpec_id
						where
							msf.MedStaffFact_id = :MedStaffFact_id
					";
					$resp = $this->queryResult($query, array(
						'MedStaffFact_id' => $this->MedStaffFact_id
					));
					if (empty($resp[0]['MedSpec_Code'])) {
						throw new Exception('Не указана специальность врача на выбранном месте работы. Невозможно определить "Вид мед. помощи".', 400);
					}
				}
				throw new Exception('Поле "Вид мед. помощи" обязательно для заполнения', 500);
			}
		}

		if ( 
			$this->regionNick == 'perm'
			&& $this->evnClassId == 11
		) {
			// проверяем наличие посещений в с типом оплаты ОМС в закрытом талоне с фед. исходом "313. Констатация смерти"
			$query = "
				select top 1 EvnPL_id
				from v_EvnPL epl with (nolock)
					inner join fed.v_LeaveType lt with (nolock) on lt.LeaveType_id = epl.LeaveType_fedid
				where
					epl.EvnPL_id = :EvnPL_id
					and lt.LeaveType_Code = '313'
					and epl.EvnPL_disDT >= '2015-01-01'
			";
			$EvnPL_id = $this->getFirstResultFromQuery($query, array('EvnPL_id' => $this->pid));

			if ( !empty($EvnPL_id) ) {
				$query = "
					select top 1 evpl.EvnVizitPL_id
					from v_EvnVizitPL evpl with (nolock)
						inner join v_PayType pt with (nolock) on pt.PayType_id = evpl.PayType_id
					where
						evpl.EvnVizitPL_pid = :EvnVizitPL_pid
						and evpl.EvnVizitPL_id != ISNULL(:EvnVizitPL_id, 0)
						and pt.PayType_SysNick = 'oms'

					union all

					select top 1 PayType_id as EvnVizitPL_id
					from v_PayType with (nolock)
					where PayType_id = :PayType_id
						and PayType_SysNick = 'oms'
				";
				$result = $this->db->query($query, array(
					 'EvnVizitPL_id' => (!empty($this->id) ? $this->id : null)
					,'EvnVizitPL_pid' => $this->pid
					,'PayType_id' => $this->PayType_id
				));
				if (is_object($result)) {
					$resp = $result->result('array');
					if (!empty($resp[0]['EvnVizitPL_id'])) {
						throw new Exception('Случаи с исходом "313 Констатация факта смерти в поликлинике" не подлежат оплате по ОМС. Для сохранения измените вид оплаты.', 400);
					}
				} else {
					throw new Exception('Ошибка проверки видов оплаты посещений.', 400);
				}
			}
		}

		if (
			$this->regionNick == 'perm'
			&& $this->payTypeSysNick == 'mbudtrans_mbud'
			&& $this->parent->IsFinish == 2
		){
			$this->load->model('EvnPL_model');
			$this->EvnPL_model->checkPayTypeMBT(array(
				'EvnPL_id' => $this->pid
			));
		}
			// Проверка на наличие хотя бы одного основного диагноза во всех посещениях ТАП
		// @task https://redmine.swan.perm.ru/issues/84915
		if (
			$this->evnClassId == 11
			&& empty($this->diag_id)
			&& in_array($this->scenario, array(self::SCENARIO_DO_SAVE))
			&& $this->parent->IsFinish == 2
		) {
			$query = "
				select Diag_id
				from v_EvnVizitPL with (nolock)
				where EvnVizitPL_pid = :EvnVizitPL_pid
					and EvnVizitPL_id <> ISNULL(:EvnVizitPL_id, 0)
			";
			$result = $this->db->query($query, array(
				 'EvnVizitPL_id' => (!empty($this->id) ? $this->id : null)
				,'EvnVizitPL_pid' => $this->pid
			));
			if (is_object($result)) {
				$resp = $result->result('array');
				$diag_exists = false;
				foreach ( $resp as $row ) {
					if ( !empty($row['Diag_id']) ) {
						$diag_exists = true;
						break;
					}
				}
				if (!$diag_exists) {
					throw new Exception('Законченный случай лечения должен иметь хотя бы один основной диагноз.', 400);
				}
			} else {
				throw new Exception('Ошибка проверки заполнения основных диагнозов в законченном случае лечения.', 400);
			}
		}

		/* refs #62946
		if ( 
			$this->regionNick == 'perm'
			&& $this->evnClassId == 11
		) {
			// проверяем наличие посещений в реестре по ->pid
			$query = "
				select top 1
					EvnVizitPL_id
				from
					v_EvnVizitPL (nolock)
				where
					EvnVizitPL_IsInReg = 2
					and ISNULL(EvnVizitPL_IsPaid, 2) = 2 -- или NULL или 2
					and EvnVizitPL_pid = :EvnVizitPL_pid
			";
			$result = $this->db->query($query, array(
				'EvnVizitPL_pid' => $this->pid
			));
			if (is_object($result)) {
				$resp = $result->result('array');
				if (!empty($resp[0]['EvnVizitPL_id'])) {
					throw new Exception('Сохранение невозможно, т.к. в ТАП имеются оплаченные посещения.', 400);
				}
			} else {
				throw new Exception('Ошибка проверки оплаченности посещений.', 400);
			}
		}*/
		
		//echo $this->IsPaid;
		if (
			in_array($this->evnClassId, array(11, 13))
			&& $this->isNewRecord
			&& $this->parent->hasEvnVizitInReg()
			&& ($this->regionNick != 'vologda' || $this->parent->IsFinish == 2)
		) {
			if ( in_array($this->regionNick, array('pskov', 'ufa', 'vologda')) ) {
				$paidField = 'Paid_id';
			}
			else {
				$paidField = 'RegistryData_IsPaid';
			}

			$registryStatusExceptions = array(4);

			if ( $this->regionNick == 'kareliya' || $this->regionNick == 'penza' ) {
				$registryStatusExceptions[] = 3; // @task https://redmine.swan.perm.ru//issues/124015
			}

			$registryStatusClause = "R.RegistryStatus_id not in (" . implode(',', $registryStatusExceptions) . ")";

			if ( $this->regionNick == 'vologda' ) {
				$registryStatusClause = "R.RegistryStatus_id = 2";
			}

			// проверяем наличие посещений в реестре по ->pid
			$query = "
				select top 1
					E.Evn_id
				from
					v_Evn E with (nolock)
					left join {$this->scheme}.v_RegistryData RD with (nolock) on RD.Evn_id = E.Evn_id
					left join {$this->scheme}.v_Registry R with (nolock) on RD.Registry_id = R.Registry_id
				where
					RD.Evn_id is not null
					and E.Evn_setDT >= '2014-12-01'
					and (
						{$registryStatusClause}
						or (
							R.RegistryStatus_id = 4
							and RD.{$paidField} = 2
						)
					)
					and E.Evn_pid = :Evn_pid
			";
			$dbreg = $this->load->database('registry', true);
			$result = $dbreg->query($query, array(
				'Evn_pid' => $this->pid
			));
			if (is_object($result)) {
				$resp = $result->result('array');
				if (!empty($resp[0]['Evn_id'])) {
					throw new Exception('Добавление нового посещения невозможно, т.к. есть посещения, входящие в реестр.', 400);
				}
			} else {
				throw new Exception('Ошибка проверки оплаченности посещений.', 400);
			}
		}
		
		if ($this->regionNick == 'pskov'){
			if ( $isStom === false ) {
				$filter ='';
				$params = array(
					'EvnVizitPl_pid' => $this->pid
				);
				if (!empty($this->id)){
					$filter =" and vizit.EvnVizitPl_id != :EvnVizitPl_id ";
					$params['EvnVizitPl_id'] =  $this->id;
				}

				$EvnVizitPl_id = $this->getFirstResultFromQuery("
						select top 1
							vizit.EvnVizitPl_id
						from
							v_EvnVizitPl vizit (nolock)
							inner join v_UslugaComplexAttribute uca (nolock) on uca.UslugaComplex_id = vizit.UslugaComplex_id
							inner join v_UslugaComplexAttributeType ucat (nolock) on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
								and ucat.UslugaComplexAttributeType_SysNick = 'vizit'
						where
							vizit.EvnVizitPl_pid = :EvnVizitPl_pid
							{$filter}
					", $params);
				/*
				if ($EvnVizitPl_id > 0) {
					throw new Exception('В случаях по посещениям к врачам не может быть заведено больше одного посещения!', 400);
				}
				*/

				if ($this->lpuSectionData['LpuSectionProfile_Code'] == '160' && $this->scenario == self::SCENARIO_AUTO_CREATE) {
					if ( !empty($this->Diag_id) ) {
						$resp_diag = $this->getFirstResultFromQuery("
							select Diag_Code from v_Diag with (nolock) where Diag_id = :Diag_id ",
							[ 'Diag_id' => $this->Diag_id ]
						);
					}

					if ( empty($resp_diag) || ($resp_diag !== false && substr($resp_diag, 0, 1) != 'Z') ) {
						$this->setAttribute('TreatmentClass_id', 2);
					}
				}
			}
		}

		//PROMEDWEB-8730
		//При сохранении посещения из ТАП не учитывались существующие бирки
		if (empty($this->EvnDirection_id) 
			&& empty($this->TimetableGraf_id)) {

			$begDate = date('Y-m-d',time());
			$endDate = date('Y-m-d',strtotime('+1days'));
			//Проверяем нет ли бирки для данного врача, на сегодня, для данного пациента. 
			//Получаем EvnDirection_id так как, если есть запись на бирку, то и есть напрадение, особенность бп
			$EvnDirection_id = $this->getFirstResultFromQuery("
			select 
				EvnDirection_id 
			from 
				v_TimeTableGraf vttg (nolock)
			where 
				vttg.Person_id = :Person_id
				and vttg.TimeTableGraf_begTime >= '{$begDate}'
				and vttg.TimeTableGraf_begTime < '{$endDate}'
				and vttg.MedStaffFact_id = :MedStaffFact_id
			", array(
				'Person_id' => $this->Person_id,
				'MedStaffFact_id' => $this->MedStaffFact_id
			));
			if (empty($EvnDirection_id)) {
				$EvnDirection_id = $this->getFirstResultFromQuery("
				select 
					EvnDirection_id 
				from 
					v_EvnPL vep (nolock)
				where 
					vep.EvnPL_id = :EvnPL_id
				", array(
					'EvnPL_id' => $this->parent->id
				));
			}
			$this->EvnDirection_id = $EvnDirection_id;
		}
		$this->_saveVizitFactTime();
		
		if (!empty($this->EvnPrescr_id)) {
			$params = array('pmUser_id' => $this->promedUserId);
			$params['EvnPrescr_id'] = $this->EvnPrescr_id;
			$this->execCommonSP('p_EvnPrescr_exec', $params);
		}
	}

	/**
	 * Получаем код
	 * @return int
	 * @throws Exception
	 */
	function getLpuUnitSetCode()
	{
		if (!isset($this->_lpuUnitSetCode) && !empty($this->UslugaComplex_id)) {
			// 1) получем код сохраняемого посещения
			$query = "
				select top 1 lu.LpuUnitSet_Code
				from v_LpuSection ls with (nolock)
				left join v_LpuUnit lu with (nolock) on lu.LpuUnit_id = ls.LpuUnit_id
				where ls.LpuSection_id = :LpuSection_id
			";
			$this->_lpuUnitSetCode = (int) $this->getFirstResultFromQuery($query, array(
				'LpuSection_id' => $this->LpuSection_id
			));
		}
		return $this->_lpuUnitSetCode;
	}

	/**
	 * Используется ли код посещения
	 */
	function getIsUseVizitCode()
	{
		return in_array($this->regionNick, array('ufa','pskov','ekb','buryatiya','kz','perm','vologda'));
	}

	/**
	 * Получаем код сохраняемого/сохраненного посещения
	 * @return string
	 * @throws Exception
	 */
	function getVizitCode()
	{
		if (empty($this->_vizitCode) && !empty($this->UslugaComplex_id) && $this->isUseVizitCode) {
			// 1) получем код сохраняемого посещения
			$query = "
				select top 1 uc.UslugaComplex_Code
				from v_UslugaComplex uc with (nolock)
				where uc.UslugaComplex_id = :UslugaComplex_id
			";
			$this->_vizitCode = $this->getFirstResultFromQuery($query, array(
				'UslugaComplex_id' => $this->UslugaComplex_id
			));
			if ( empty($this->_vizitCode) ){
				throw new Exception('Ошибка при получении кода посещения');
			}
			if ( $this->regionNick == 'ufa' && !in_array(mb_strlen($this->_vizitCode, 'utf-8'), array(6, 7)) ) {
				throw new Exception('Недопустимый код посещения');
			}
		}
		return $this->_vizitCode;
	}

	/**
	 * Логика после успешного сохранения объекта
	 * @throws Exception
	 */
	protected function _updateEvnUslugaVizit()
	{
		// сохраняем услугу посещения
		if ($this->isUseVizitCode) {
			/*$this->load->model('EvnUsluga_model');
			if ( !empty($this->UslugaComplex_id) ) {
				$usluga_data = array(
					'EvnUslugaCommon_id' => $this->EvnUslugaVizit_id,
					'EvnUslugaCommon_pid' => $this->id,
					'Lpu_id' => $this->Lpu_id,
					'Server_id' => $this->Server_id,
					'PersonEvn_id' => $this->PersonEvn_id,
					'Person_id' => $this->Person_id,
					'EvnUslugaCommon_setDate' => $this->setDate,
					'EvnUslugaCommon_setTime' => $this->setTime,
					'PayType_id' => $this->PayType_id,
					'Usluga_id' => NULL,
					'UslugaComplex_id' => $this->UslugaComplex_id,
					'HealthKind_id' => (!empty($this->HealthKind_id) ? $this->HealthKind_id : NULL),
					'MedPersonal_id' => $this->MedPersonal_id,
					'UslugaPlace_id' => 1, // Место выполнения: отделение
					'Lpu_uid' => NULL,
					'LpuSection_uid' => $this->LpuSection_id,
					'Org_uid' => NULL,
					'EvnUslugaCommon_Kolvo' => 1,
					'EvnUslugaCommon_IsVizitCode' => 2,
					'pmUser_id' => $this->promedUserId,
					'session' => $this->sessionParams,
				);
				$this->EvnUsluga_model->isAllowTransaction = false;
				$tmp = $this->EvnUsluga_model->saveEvnUslugaCommon($usluga_data);
				if (!empty($tmp[0]['Error_Msg'])) {
					//нужно откатить транзакцию
					throw new Exception($tmp[0]['Error_Msg']);
				}
				$this->_saveResponse['EvnUslugaCommon_id'] = $tmp[0]['EvnUslugaCommon_id'];
			}
			if (empty($this->UslugaComplex_id) && !empty($this->EvnUslugaVizit_id)) {
				// удаляем услугу посещения
				$this->EvnUsluga_model->isAllowTransaction = false;
				$tmp = $this->EvnUsluga_model->deleteEvnUsluga(array(
					'id' => $this->EvnUslugaVizit_id,
					'class' => 'EvnUslugaCommon',
					'pmUser_id' => $this->promedUserId
				));
				if (!empty($tmp[0]['Error_Msg'])) {
					//нужно откатить транзакцию
					throw new Exception($tmp[0]['Error_Msg']);
				}
				$this->_saveResponse['EvnUslugaCommon_id'] = null;
			}*/

			if ($this->regionNick == 'pskov' && !empty($this->pid)) {
				// обновляем код и услугу посещения во всех посещениях данного ТАП
				$query = "
					update
						e with (rowlock)
					set
						e.UslugaComplex_id = :UslugaComplex_id
					from
						EvnVizitPL e
						inner join v_EvnVizitPL evpl (nolock) on evpl.EvnVizitPL_id = e.EvnVizitPL_id
					where
						evpl.EvnVizitPL_pid = :EvnVizitPL_pid
				";
				$this->db->query($query, array(
					'EvnVizitPL_pid' => $this->pid,
					'UslugaComplex_id' => $this->UslugaComplex_id
				));

				$query = "
					update
						e with (rowlock)
					set
						e.UslugaComplex_id = :UslugaComplex_id
					from
						EvnUsluga e
						inner join v_EvnUsluga eu (nolock) on eu.EvnUsluga_id = e.EvnUsluga_id
						inner join v_EvnVizitPL evpl (nolock) on evpl.EvnVizitPL_id = eu.EvnUsluga_pid
					where
						evpl.EvnVizitPL_pid = :EvnVizitPL_pid
						and e.EvnUsluga_isVizitCode = 2
				";
				$this->db->query($query, array(
					'EvnVizitPL_pid' => $this->pid,
					'UslugaComplex_id' => $this->UslugaComplex_id
				));
			}
		}
	}

	/**
	 * Завершить обслуживание вызова на дом
	 * после успешного сохранения объекта
	 * @throws Exception
	 */
	protected function _completeHomeVisit()
	{
		if (!empty($this->HomeVisit_id)
			&& $this->isNewRecord
			&& $this->evnClassId == 11
		) {
			$home_visit_data = array(
				'HomeVisit_id' => $this->HomeVisit_id,
				'MedStaffFact_id' => $this->MedStaffFact_id,
				'MedPersonal_id' => $this->MedPersonal_id,
				'HomeVisit_LpuComment' => NULL,
				'pmUser_id' => $this->promedUserId
			);
			$this->load->model('HomeVisit_model');
			$tmp = $this->HomeVisit_model->completeHomeVisit($home_visit_data);
			if (!empty($tmp['Error_Msg'])) {
				throw new Exception($tmp['Error_Msg']);
			}
		}
	}

	/**
	 * Создание нового XML-документа протокола осмотра
	 *
	 * Создание протокола осмотра с данными по умолчанию
	 * или копирование указанного протокола осмотра
	 * после успешного сохранения объекта
	 * @throws Exception
	 */
	protected function _addEvnXml()
	{
		if (!in_array($this->evnClassId, array(11, 13))
			|| !$this->isNewRecord
			|| (empty($this->_params['allowCreateEmptyEvnDoc']) && empty($this->_params['copyEvnXml_id']))
		) {
			return true;
		}
		$this->load->library('swXmlTemplate');
		$instance = swXmlTemplate::getEvnXmlModelInstance();
		$response=array();
		// #77801 Если в настройках указано не копировать шаблон - берём шаблон по умолчанию
		if (!empty($this->_params['allowCreateEmptyEvnDoc']) && ($this->options['polka']['arm_evn_xml_copy'] != 2 || empty($this->_params['copyEvnXml_id']))) {
			$response = $instance->createEmpty(array(
				'session' => $this->sessionParams,
				'Evn_id' => $this->id,
				'MedStaffFact_id' => $this->MedStaffFact_id,//для получения шаблона по умолчанию
				'XmlType_id' => swEvnXml::EVN_VIZIT_PROTOCOL_TYPE_ID,
				'EvnClass_id' => $this->evnClassId,
				'Server_id' => $this->Server_id,
			), false);
		} else if(isset($this->_params['copyEvnXml_id'])) {
			$response = $instance->doCopy(array(
				'session' => $this->sessionParams,
				'EvnXml_id' => $this->_params['copyEvnXml_id'],
				'Evn_id' => $this->id,
			), false);
		}
		$alert_msg = null;
		if (count($response) > 0) {
			if(isset($response[0]['Error_Msg'])) {
				$alert_msg = $response[0]['Error_Msg'];
			}
			if(isset($response[0]['EvnXml_id'])) {
				$this->_saveResponse['EvnXml_id'] = $response[0]['EvnXml_id'];
			}
		} else {
			$alert_msg = 'Не удалось создать пустой XML-документ протокола осмотра';
		}
		if (isset($alert_msg)) {
			if (isset($this->_saveResponse['Alert_Msg'])) {
				$this->_saveResponse['Alert_Msg'] .= '<br>' . $alert_msg;
			} else {
				$this->_saveResponse['Alert_Msg'] = $alert_msg;
			}
		}
		return true;
	}

	/**
	 * Логика после успешного сохранения объекта
	 * @param array $result Результат выполнения запроса
	 * @throws Exception
	 */
	protected function _afterSave($result)
	{
		$this->_updateMes();
		$this->_updateEvnUslugaVizit();
		$this->_addEvnXml();
		$this->_completeHomeVisit();
		$this->_saveDrugTherapyScheme();
		$this->saveUslugaMedTypeLink();

		if (!in_array(getRegionNick(), array('perm', 'kareliya', 'kz')) && !empty($this->EvnUslugaLinkChange)) {
			$this->load->model('EvnUslugaPar_model');
			foreach($this->EvnUslugaLinkChange as $usl) {
				switch($usl['type']) {
					case 'unlink':
						$this->EvnUslugaPar_model->editEvnUslugaPar(array(
							'EvnUslugaPar_id' => $usl['EvnUslugaPar_id'],
							'EvnUslugaPar_pid' => null,
							'pmUser_id' => $this->promedUserId,
							'session' => $this->sessionParams
						));
						break;
					case 'link':
						$this->EvnUslugaPar_model->editEvnUslugaPar(array(
							'EvnUslugaPar_id' => $usl['EvnPrescr_pid'],
							'EvnUslugaPar_pid' => $this->id,
							'pmUser_id' => $this->promedUserId,
							'session' => $this->sessionParams
						));
						break;
				}
			}
		}

		//$this->_updateMedicalCareKind(array('EvnPL_id' => $this->pid)); // #75656 теперь не надо
		parent::_afterSave($result);
		$this->load->model('TimetableGraf_model');
		$ttgdata = $this->TimetableGraf_model->onAfterSaveEvnVizit($this);
		$this->_saveResponse['TimetableGraf_id'] = $this->TimetableGraf_id;

		$this->_savePregnancyEvnVizitPL();

		if ($this->_isAttributeChanged('diag_id')) {
			$query = "
				select top 1 movpld.MorbusOnkoVizitPLDop_id
				from v_MorbusOnkoVizitPLDop movpld (nolock)
				where 
					movpld.EvnVizit_id = :id
					and movpld.Diag_id not in (
						select Diag_id from v_EvnVizitPL (nolock) where EvnVizitPL_id = :id union
						select Diag_id from v_EvnDiag (nolock) where EvnDiag_pid = :id union
						select isnull(Diag_spid,0) as Diag_id from v_EvnVizitPL (nolock) where EvnVizitPL_id = :id union
						select isnull(Diag_spid,0) as Diag_id from v_EvnPLDispScreenOnko (nolock) where EvnPLDispScreenOnko_pid = :id
					)
			";
			//echo getDebugSQL($query, array('id' => $this->id));exit;
			$MorbusOnkoVizitPLDop_id = $this->getFirstResultFromQuery($query, array('id' => $this->id), true);
			if ($MorbusOnkoVizitPLDop_id === false) {
				throw new Exception('Ошибка при проверке талона дополнений больного ЗНО');
			}
			if (!empty($MorbusOnkoVizitPLDop_id)) {
				$this->load->model('MorbusOnkoVizitPLDop_model');
				$resp = $this->MorbusOnkoVizitPLDop_model->delete(array(
					'MorbusOnkoVizitPLDop_id' => $MorbusOnkoVizitPLDop_id,
					'pmUser_id' => $this->promedUserId
				));
				if (!$this->isSuccessful($resp)) {
					throw new Exception($resp[0]['Error_Msg']);
				}
			}
		}

		if ( getRegionNick() == 'perm' && $this->_params['addB04069333'] == true ) {
			$UslugaComplex_id = $this->getFirstResultFromQuery("
				select top 1 UslugaComplex_id
				from v_UslugaComplex with (nolock)
				where UslugaComplex_Code = 'B04.069.333'
					and (UslugaComplex_begDT is null or UslugaComplex_begDT <= :setDate)
					and (UslugaComplex_endDT is null or UslugaComplex_endDT >= :setDate)
			", array('setDate' => $this->setDate), null);

			if ( !empty($this->LpuSectionProfile_id) ) {
				$LpuSectionProfile_id = $this->LpuSectionProfile_id;
			}
			else {
				$LpuSectionProfile_id = $this->getFirstResultFromQuery("select top 1 LpuSectionProfile_id from v_LpuSection with (nolock) where LpuSection_id = :LpuSection_id", array('LpuSection_id' => $this->LpuSection_id));
			}

			if ( !empty($UslugaComplex_id) ) {
				$this->load->model('EvnUsluga_model');
				$usluga_data = array(
					'EvnUslugaCommon_id' => null,
					'EvnUslugaCommon_pid' => $this->id,
					'Lpu_id' => $this->Lpu_id,
					'Server_id' => $this->Server_id,
					'PersonEvn_id' => $this->PersonEvn_id,
					'Person_id' => $this->Person_id,
					'EvnUslugaCommon_setDate' => $this->setDate,
					'EvnUslugaCommon_setTime' => $this->setTime,
					'PayType_id' => $this->PayType_id,
					'Usluga_id' => NULL,
					'UslugaComplex_id' => $UslugaComplex_id,
					'HealthKind_id' => (!empty($this->HealthKind_id) ? $this->HealthKind_id : NULL),
					'MedPersonal_id' => $this->MedPersonal_id,
					'MedStaffFact_id' => $this->MedStaffFact_id,
					'LpuSectionProfile_id' => $LpuSectionProfile_id,
					'UslugaPlace_id' => 1, // Место выполнения: отделение
					'Lpu_uid' => NULL,
					'LpuSection_uid' => $this->LpuSection_id,
					'Org_uid' => NULL,
					'EvnUslugaCommon_Kolvo' => 1,
					'EvnUslugaCommon_IsVizitCode' => 1,
					'pmUser_id' => $this->promedUserId,
					'session' => $this->sessionParams,
				);
				$this->EvnUsluga_model->isAllowTransaction = false;
				$tmp = $this->EvnUsluga_model->saveEvnUslugaCommon($usluga_data);
				if (!empty($tmp[0]['Error_Msg'])) {
					//нужно откатить транзакцию
					throw new Exception($tmp[0]['Error_Msg']);
				}
			}
		}

		if (getRegionNick() == 'perm') {
			$this->load->model('EvnPL_model');
			$this->EvnPL_model->checkEvnPLCrossed(array(
				'EvnPL_id' => $this->pid
			));
		}

		if (getRegionNick() == 'kz') {
			$EvnLinkAPP_id = $this->getFirstResultFromQuery("select EvnLinkAPP_id from r101.EvnLinkAPP with(nolock) where Evn_id = ?", [$this->id]);
			$proc = !$EvnLinkAPP_id ? 'r101.p_EvnLinkAPP_ins' : 'r101.p_EvnLinkAPP_upd';
			
			if ($this->_params['PayTypeKAZ_id'] != null || $this->_params['VizitActiveType_id'] != null || $this->_params['ScreenType_id'] != null) {
				$this->execCommonSP($proc, [
					'EvnLinkAPP_id' => $EvnLinkAPP_id ? $EvnLinkAPP_id : null,
					'Evn_id' => $this->id,
					'PayTypeKAZ_id' => $this->_params['PayTypeKAZ_id'],
					'VizitActiveType_id' => $this->_params['VizitActiveType_id'],
					'ScreenType_id' => $this->_params['ScreenType_id'],
					'pmUser_id' => $this->promedUserId
				], 'array_assoc');
			} elseif ($EvnLinkAPP_id != false) {
				return $this->execCommonSP('r101.p_EvnLinkAPP_del', [
					'EvnLinkAPP_id' => $EvnLinkAPP_id
				], 'array_assoc');
			}
		}

		if (!empty($this->_params['RepositoryObservData'])) {
			$this->load->model('RepositoryObserv_model');
			$err = getInputParams(
				$this->_params['RepositoryObservData'], 
				$this->RepositoryObserv_model->getSaveRules(), 
				true, 
				$this->_params['RepositoryObservData']
			);
			if (empty($err)) {
				$this->_params['RepositoryObservData']['Evn_id'] = $this->id;
				$this->_params['RepositoryObservData']['Lpu_id'] = $this->Lpu_id;
				$this->_params['RepositoryObservData']['pmUser_id'] = $this->promedUserId;
				$this->RepositoryObserv_model->save($this->_params['RepositoryObservData']);
			}
		}
		
		$this->load->model('PersonPregnancy_model');
		$this->PersonPregnancy_model->checkAndSaveQuarantine([
			'Person_id' => $this->Person_id,
			'pmUser_id' => $this->promedUserId
		]);

		//Отправить в очередь на идентификацию
		$this->_toIdent();

		// Выполнить группировку
		$this->updateEvnVizitNumGroup($this->pid);
	}

	/**
	 * Сохранение информации о беременности, связанной с посещением
	 */
	function _savePregnancyEvnVizitPL() {
		$this->load->model('PregnancyEvnVizitPL_model');

		$resp = $this->PregnancyEvnVizitPL_model->savePregnancyEvnVizitPLData(array(
			'PregnancyEvnVizitPL_Period' => $this->_params['PregnancyEvnVizitPL_Period'],
			'EvnVizitPL_id' => $this->id,
			'pmUser_id' => $this->promedUserId
		));
		if (!$this->isSuccessful($resp)) {
			throw new Exception($resp[0]['Error_Msg'], $resp[0]['Error_Code']);
		}
	}

	/**
	 * Отправить в очередь на идентификацию
	 */
	protected function _toIdent() {
		if (getRegionNick() == 'penza' && !empty($this->id) && $this->isNewRecord && $this->payTypeSysNick == 'oms') {
			//Отправить человека в очередь на идентификацию
			$this->load->model('Person_model', 'pmodel');
			$this->pmodel->isAllowTransaction = false;
			$resp = $this->pmodel->addPersonRequestData(array(
				'Person_id' => $this->Person_id,
				'Evn_id' => $this->id,
				'PersonRequestSourceType_id' => 3,
				'pmUser_id' => $this->promedUserId,
			));
			$this->pmodel->isAllowTransaction = true;
			if (!$this->isSuccessful($resp) && !in_array($resp[0]['Error_Code'], array(302, 303))) {
				throw new Exception($resp[0]['Error_Msg']);
			}
		}
	}

	/**
	 * Обновление MedicalCareKind в ТАПе.
	 */
	function _updateMedicalCareKind($data) {
		if ( $this->regionNick == 'kareliya' ) {
			// Обновляем MedicalCareKind в зависимости от последнего посещения по данному тап.
			$query = "
				update
					e with (rowlock)
				set
					e.MedicalCareKind_id = mck.MedicalCareKind_id
				from
					EvnPL e
					inner join v_EvnPL epl (nolock) on epl.EvnPL_id = e.EvnPL_id
					inner join v_EvnClass ec (nolock) on ec.EvnClass_id = epl.EvnClass_id
					outer apply(
						select top 1
							epl.LpuSection_id
						from
							v_EvnVizitPL epl (nolock)
						where
							epl.EvnVizitPL_pid = e.EvnPL_id
						order by
							epl.EvnVizitPL_setDate desc
					) EVIZPL
					left join v_LpuSection ls (nolock) on ls.LpuSection_id = evizpl.LpuSection_id
					inner join v_MedicalCareKind mck (nolock) on mck.MedicalCareKind_Code = (case when ec.EvnClass_SysNick = 'EvnPLStom' then 9 when ls.LpuSectionProfile_Code = '57' then 8 else 1 end)
				where
					e.EvnPL_id = :EvnPL_id
			";
			$this->db->query($query, $data);
		}
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateDiagId($id, $value = null)
	{
		$updatedDiag = $this->_updateAttribute($id, 'diag_id', $value);
		$this->load->model('PersonPregnancy_model');
		$this->PersonPregnancy_model->checkAndSaveQuarantine([
			'Person_id' => $this->Person_id,
			'pmUser_id' => $this->promedUserId
		]);
		return $updatedDiag;
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateEvnVizitPLIsZNO($id, $value = null)
	{
		if(getRegionNick()=='ekb') {//обновить связку IsZNO - IsZNORemove
			$iszno_last = $this->getFirstResultFromQuery("
				select top 1 EvnVizitPL_IsZNO
				from v_EvnVizitPL with(nolock)
				where EvnVizitPL_id = :id
			", array('id' => $id));
			$result = $this->_updateAttribute($id, 'iszno', $value);
			if($result and empty($result['Error_Msg'])) {
				$znoremove = (($value!='2' and $iszno_last=='2') ? '2' : '1');
				$this->_updateAttribute($id, 'isznoremove', $znoremove);
			}
		} else {
			$result = $this->_updateAttribute($id, 'iszno', $value);
		}

		if ( $value != 2 ) {
			$this->updateDiagSpid($id, null);
		}
		return $result;
	}
	
	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateEvnVizitPLBiopsyDate($id, $value = null)
	{
		return $this->_updateAttribute($id, 'biopsydate', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateDiagSpid($id, $value = null)
	{
		return $this->_updateAttribute($id, 'diag_spid', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateDiagnewId($id, $value = null)
	{
		// приходят сразу два атрибута (диагноз + характер заболевания)
		$values = explode(':', $value);
		if (count($values) == 2) {
			if (empty($values[0]) || $values[0] == 'null') { $values[0] = null; }
			if (empty($values[1]) || $values[1] == 'null') { $values[1] = null; }

			$this->_updateAttribute($id, 'deseasetype_id', $values[1]);
			return $this->_updateAttribute($id, 'diag_id', $values[0]);
		}
		return false;
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateDeseaseTypeId($id, $value = null)
	{
		return $this->_updateAttribute($id, 'deseasetype_id', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateTumorStageId($id, $value = null)
	{
		return $this->_updateAttribute($id, 'tumorstage_id', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateHealthKindId($id, $value = null)
	{
		return $this->_updateAttribute($id, 'healthkind_id', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updatePainIntensityId($id, $value = null)
	{
		return $this->_updateAttribute($id, 'painintensity_id', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateRankinScaleId($id, $value = null)
	{
		return $this->_updateAttribute($id, 'rankinscale_id', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateVizitTypeId($id, $value = null)
	{
		return $this->_updateAttribute($id, 'vizittype_id', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateVizitClassId($id, $value = null)
	{
		return $this->_updateAttribute($id, 'vizitclass_id', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateRiskLevelId($id, $value = null)
	{
		return $this->_updateAttribute($id, 'risklevel_id', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateWellnessCenterAgeGroupsId($id, $value = null)
	{
		return $this->_updateAttribute($id, 'wellnesscenteragegroups_id', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateProfGoalId($id, $value = null)
	{
		return $this->_updateAttribute($id, 'profgoal_id', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateDispProfGoalTypeId($id, $value = null)
	{
		return $this->_updateAttribute($id, 'dispprofgoaltype_id', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateMedStaffFactId($id, $value = null)
	{
		$MedPersonal_id = $this->getFirstResultFromQuery("
			select top 1 MedPersonal_id
			from v_MedStaffFact with(nolock)
			where MedStaffFact_id = :MedStaffFact_id
		", array('MedStaffFact_id' => $value));
		$this->_updateAttribute($id, 'medpersonal_id', $MedPersonal_id);
		return $this->_updateAttribute($id, 'medstafffact_id', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateMedPersonalSid($id, $value = null)
	{
		return $this->_updateAttribute($id, 'medpersonal_sid', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateTreatmentClassId($id, $value = null)
	{
		return $this->_updateAttribute($id, 'treatmentclass_id', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateServiceTypeId($id, $value = null)
	{
		return $this->_updateAttribute($id, 'servicetype_id', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateUslugaComplexId($id, $value = null)
	{
		return $this->_updateAttribute($id, 'uslugacomplex_id', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateMedicalCareKindId($id, $value = null)
	{
		return $this->_updateAttribute($id, 'medicalcarekind_id', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateLpuSectionProfileId($id, $value = null)
	{
		return $this->_updateAttribute($id, 'lpusectionprofile_id', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updatePayTypeKAZId($id, $value = null)
	{
		return $this->saveEvnLinkAPP($id,'PayTypeKAZ_id',$value);
	}
	
	function updateVizitActiveTypeId($id, $value = null)
	{
		return $this->saveEvnLinkAPP($id,'VizitActiveType_id',$value);
	}

	/**
	 * Сохранение информации Данные для сервиса АПП
	 */
	function saveEvnLinkAPP($id, $valueColumn, $value = null) {
		if (getRegionNick() == 'kz') {
			$EvnLinkAPP = $this->getFirstRowFromQuery("
				select 
					EvnLinkAPP_id,
					PayTypeKAZ_id,
					VizitActiveType_id 
				from r101.EvnLinkAPP with(nolock) 
				where Evn_id = ?
			", [$id]);
			
			$proc = !$EvnLinkAPP['EvnLinkAPP_id'] ? 'r101.p_EvnLinkAPP_ins' : 'r101.p_EvnLinkAPP_upd';
			
			$otherColumn = ($valueColumn == 'PayTypeKAZ_id')?'VizitActiveType_id':'PayTypeKAZ_id';
			
			if ($value != null || !empty($EvnLinkAPP[$otherColumn])/* || !empty($EvnLinkAPP['VizitActiveType_id'])*/) {
				$this->execCommonSP($proc, [
					'EvnLinkAPP_id' => $EvnLinkAPP['EvnLinkAPP_id'] ? $EvnLinkAPP['EvnLinkAPP_id'] : null,
					'Evn_id' => $id,
					$valueColumn => $value,
					$otherColumn => $EvnLinkAPP[$otherColumn],
					'pmUser_id' => $this->promedUserId
				], 'array_assoc');
			} elseif ($EvnLinkAPP['EvnLinkAPP_id'] != false) {
				return $this->execCommonSP('r101.p_EvnLinkAPP_del', [
					'EvnLinkAPP_id' => $EvnLinkAPP['EvnLinkAPP_id']
				], 'array_assoc');
			}
		}

		return ['success' => true];
	}
	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateScreenTypeId($id, $value = null)
	{
		if (getRegionNick() != 'kz') {
			return ['success' => true];
		}

		$EvnLinkAPP_id = $this->getFirstResultFromQuery("select EvnLinkAPP_id from r101.EvnLinkAPP with(nolock) where Evn_id = ?", [$id]);

		if (!$EvnLinkAPP_id) {
			$this->execCommonSP('r101.p_EvnLinkAPP_ins', [
				'EvnLinkAPP_id' => $EvnLinkAPP_id ? $EvnLinkAPP_id : null,
				'Evn_id' => $id,
				'ScreenType_id' => $value,
				'pmUser_id' => $this->promedUserId
			], 'array_assoc');
		} else {
			$this->swUpdate('r101.EvnLinkAPP', array(
				'EvnLinkAPP_id' => $EvnLinkAPP_id,
				'ScreenType_id' => $value,
				'pmUser_id' => $this->promedUserId,
			), true);
		}

		return ['success' => true];
	}
	
	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updatePregnancyEvnVizitPLPeriod($id, $value = null)
	{
		if (!empty($value) && $value < 1 && $value > 45) {
			throw new Exception('Срок беременности должен быть от 1 до 45 недель');
		}

		$this->load->model('PregnancyEvnVizitPL_model');

		return $this->PregnancyEvnVizitPL_model->savePregnancyEvnVizitPLData(array(
			'PregnancyEvnVizitPL_Period' => $value,
			'EvnVizitPL_id' => $id,
			'pmUser_id' => $this->promedUserId
		));
	}

	/**
	 * @param string $key Ключ строчными символами
	 * @throws Exception
	 */
	protected function _beforeUpdateAttribute($key)
	{
		parent::_beforeUpdateAttribute($key);

		$isStom = (13 == $this->evnClassId);

		switch ($key) {
			case 'lpusectionprofile_id':
				$this->_checkChangeLpuSectionProfileId();
				break;
			case 'diag_id':
				if (getRegionNick() == 'ekb') {
					$this->setMedicalCareKindId();
				}
				$this->_checkChangeDiag();
				break;
			case 'paytype_id':
				$this->_setEvnVizitPLDoubles();
				if (getRegionNick() == 'ekb') {
					$this->setMedicalCareKindId();
				}

				$this->_checkOnkoSpecifics();

				if (getRegionNick() == 'kareliya') {
					// сбросить VizitType_id, если не соответсвует виду оплаты
					if ($this->payTypeSysNick == 'oms') {
						$deniedVizitTypeCodes = array('41', '51', '2.4', '3.1');
						if (strtotime($this->setDate) < strtotime('01.05.2019')) {
							$deniedVizitTypeCodes[] = '1.2';
						}

						$VizitType_Code = $this->getFirstResultFromQuery("select VizitType_Code from v_VizitType (nolock) where VizitType_id = :VizitType_id", array('VizitType_id' => $this->VizitType_id));
						if (!empty($VizitType_Code) && in_array($VizitType_Code, $deniedVizitTypeCodes)) {
							$this->setAttribute('vizittype_id', null);
							$this->_saveResponse['clearVizitTypeId'] = true;
						}
					}
				}
				break;
			case 'uslugacomplex_id':
				$this->_checkChangeVizitCode();
				$this->_checkChangeEvnUsluga();
				if ($this->regionNick == 'kz' && empty($this->PayType_id)) {
					$this->setAttribute('paytype_id', 152);
				}
				break;
			case 'treatmentclass_id':
				if ( !$isStom && $this->regionNick == 'perm' ) {
					$this->setAttribute('uslugacomplex_id', null);
				}

				// на Карелии поле скрыто, проверка не нужна
				if (!empty($this->VizitType_id) && !in_array($this->regionNick, ['kareliya', 'kz'])) {
					// сбросить VizitType_id, если не соответсвует TreatmentClassVizitType
					$resp_tcvt = $this->queryResult("
						select top 1
							TreatmentClassVizitType_id
						from
							v_TreatmentClassVizitType (nolock)
						where
							TreatmentClass_id = :TreatmentClass_id
							and VizitType_id = :VizitType_id
					", array(
						'TreatmentClass_id' => $this->TreatmentClass_id,
						'VizitType_id' => $this->VizitType_id
					));

					if (empty($resp_tcvt[0]['TreatmentClassVizitType_id'])) {
						$this->setAttribute('vizittype_id', null);
					}
				}

				break;
			case 'vizitclass_id':
				if ( !$isStom && $this->regionNick == 'perm' ) {
					$this->setAttribute('uslugacomplex_id', null);
				}
				break;
			case 'vizittype_id':
				if ( !$isStom && $this->regionNick == 'perm' ) {
					$this->setAttribute('uslugacomplex_id', null);
				}
				$this->_checkChangeVizitType();
				break;
			case 'servicetype_id':
				$this->_checkChangeServiceType();
				break;
			case 'medstafffact_id':
				$this->load->model('TimetableGraf_model');
				$ttgdata = $this->TimetableGraf_model->onBeforeSaveEvnVizit($this);
				$this->setAttribute('TimetableGraf_id', $ttgdata['TimetableGraf_id']);
				$this->setAttribute('EvnDirection_id', $ttgdata['EvnDirection_id']);
				break;
		}
	}

	/**
	 * @param string $key Ключ строчными символами
	 * @throws Exception
	 */
	protected function _afterUpdateAttribute($key)
	{
		parent::_afterUpdateAttribute($key);
		switch ($key) {
			case 'vizitclass_id':
			case 'vizittype_id':
				if ( $this->regionNick == 'perm' ) {
					$this->_updateEvnUslugaVizit();
				}
				break;
			case 'servicetype_id':
				if ( getRegionNick() == 'perm' && $this->_params['addB04069333'] == true ) {
					$UslugaComplex_id = $this->getFirstResultFromQuery("
						select top 1 UslugaComplex_id
						from v_UslugaComplex with (nolock)
						where UslugaComplex_Code = 'B04.069.333'
							and (UslugaComplex_begDT is null or UslugaComplex_begDT <= :setDate)
							and (UslugaComplex_endDT is null or UslugaComplex_endDT >= :setDate)
					", array('setDate' => $this->setDate), null);

					if ( !empty($this->LpuSectionProfile_id) ) {
						$LpuSectionProfile_id = $this->LpuSectionProfile_id;
					}
					else {
						$LpuSectionProfile_id = $this->getFirstResultFromQuery("select top 1 LpuSectionProfile_id from v_LpuSection with (nolock) where LpuSection_id = :LpuSection_id", array('LpuSection_id' => $this->LpuSection_id));
					}

					if ( !empty($UslugaComplex_id) ) {
						$this->load->model('EvnUsluga_model');
						$usluga_data = array(
							'EvnUslugaCommon_id' => null,
							'EvnUslugaCommon_pid' => $this->id,
							'Lpu_id' => $this->Lpu_id,
							'Server_id' => $this->Server_id,
							'PersonEvn_id' => $this->PersonEvn_id,
							'Person_id' => $this->Person_id,
							'EvnUslugaCommon_setDate' => $this->setDate,
							'EvnUslugaCommon_setTime' => $this->setTime,
							'PayType_id' => $this->PayType_id,
							'Usluga_id' => NULL,
							'UslugaComplex_id' => $UslugaComplex_id,
							'HealthKind_id' => (!empty($this->HealthKind_id) ? $this->HealthKind_id : NULL),
							'MedPersonal_id' => $this->MedPersonal_id,
							'MedStaffFact_id' => $this->MedStaffFact_id,
							'LpuSectionProfile_id' => $LpuSectionProfile_id,
							'UslugaPlace_id' => 1, // Место выполнения: отделение
							'Lpu_uid' => NULL,
							'LpuSection_uid' => $this->LpuSection_id,
							'Org_uid' => NULL,
							'EvnUslugaCommon_Kolvo' => 1,
							'EvnUslugaCommon_IsVizitCode' => 1,
							'pmUser_id' => $this->promedUserId,
							'session' => $this->sessionParams,
						);
						$this->EvnUsluga_model->isAllowTransaction = false;
						$tmp = $this->EvnUsluga_model->saveEvnUslugaCommon($usluga_data);
						if (!empty($tmp[0]['Error_Msg'])) {
							//нужно откатить транзакцию
							throw new Exception($tmp[0]['Error_Msg']);
						}
					}
				}
				break;
			case 'uslugacomplex_id':
				$this->_updateEvnUslugaVizit();
				break;
			case 'diag_id':
				if ($this->isLastVizit()) {
					$this->parent->setScenario(self::SCENARIO_SET_ATTRIBUTE);
					$resp = $this->parent->_updateAttribute($this->pid, 'diag_lid', $this->Diag_id, false);
					if (!empty($resp['Error_Msg'])) {
						throw new Exception($resp['Error_Msg'], $resp['Error_Code']);
					}
				}

				$query = "
					select top 1 movpld.MorbusOnkoVizitPLDop_id
					from v_EvnVizitPL evpl (nolock)
					inner join v_Diag Diag (nolock) on Diag.Diag_id = evpl.Diag_id
					inner join v_MorbusOnkoVizitPLDop movpld (nolock) on movpld.EvnVizit_id = evpl.EvnVizitPL_id
					where evpl.EvnVizitPL_id = :id
					and not ((Diag.Diag_Code >= 'C00' AND Diag.Diag_Code <= 'C97') or (Diag.Diag_Code >= 'D00' AND Diag.Diag_Code <= 'D09'))
				";
				//echo getDebugSQL($query, array('id' => $this->id));exit;
				$MorbusOnkoVizitPLDop_id = $this->getFirstResultFromQuery($query, array('id' => $this->id), true);
				if ($MorbusOnkoVizitPLDop_id === false) {
					throw new Exception('Ошибка при проверке талона дополнений больного ЗНО');
				}
				if (!empty($MorbusOnkoVizitPLDop_id)) {
					$this->load->model('MorbusOnkoVizitPLDop_model');
					$resp = $this->MorbusOnkoVizitPLDop_model->delete(array(
						'MorbusOnkoVizitPLDop_id' => $MorbusOnkoVizitPLDop_id,
						'pmUser_id' => $this->promedUserId
					));
					if (!$this->isSuccessful($resp)) {
						throw new Exception($resp[0]['Error_Msg']);
					}
					$this->_saveResponse['deletedMorbusOnkoVizitPLDop_id'] = $MorbusOnkoVizitPLDop_id;
				}

				$this->load->model('CureStandart_model');
				$cureStandartCountQuery = $this->CureStandart_model->getCountQuery('Diag', 'PS.Person_BirthDay', 'isnull(EvnVizit.EvnVizitPL_setDT,dbo.tzGetDate())');
				$diagFedMesFileNameQuery = $this->CureStandart_model->getDiagFedMesFileNameQuery('Diag');

				$tmp = $this->getFirstRowFromQuery("
					SELECT FM.CureStandart_Count, DFM.DiagFedMes_FileName
					FROM v_EvnVizitPL EvnVizit with (nolock)
						inner join v_PersonState PS with (nolock) on EvnVizit.Person_id = PS.Person_id
						inner join v_Diag Diag with (nolock) on Diag.Diag_id = EvnVizit.Diag_id
						outer apply (
							{$cureStandartCountQuery}
						) FM
						outer apply (
							{$diagFedMesFileNameQuery}
						) DFM
					WHERE EvnVizit.EvnVizitPL_id = :id
				", array('id' => $this->id));
				if (empty($tmp)) {
					$this->_saveResponse['CureStandart_Count'] = null;
					$this->_saveResponse['DiagFedMes_FileName'] = null;
				} else {
					$this->_saveResponse['CureStandart_Count'] = $tmp['CureStandart_Count'];
					$this->_saveResponse['DiagFedMes_FileName'] = $tmp['DiagFedMes_FileName'];
				}
				break;
			case 'diag_spid':
				if (in_array($this->regionNick, ['perm', 'msk'])) {
					$this->load->model('MorbusOnkoSpecifics_model');
					$this->MorbusOnkoSpecifics_model->checkAndCreateSpecifics($this);
				}
				break;
		}
		if (in_array($key, array('diag_id', 'person_id', 'setdate'))) {
			$this->_updateMorbus();
		}
	}

	/**
	 * Проверки и другая логика перед удалением объекта
	 * @param array $data Массив входящих параметров
	 * @throws Exception
	 */
	protected function _beforeDelete($data = array())
	{
		parent::_beforeDelete($data);
		// Очищаем фактическое время приема или удаляем бирки без записи
		$this->load->model('TimetableGraf_model');
		$this->TimetableGraf_model->onBeforeDeleteEvn($this);
		
		// отменить выполнение связанного назначения
		$params = array('pmUser_id' => $this->promedUserId);
		$params['EvnPrescr_id'] = $this->getFirstResultFromQuery("
			select top 1 EvnPrescr_id 
			from v_EvnVizitPL with(nolock)
			where EvnVizitPL_id = :Evn_id
		", array('Evn_id' => $this->id));
		if (!empty($params['EvnPrescr_id'])) {
			$tmp = $this->execCommonSP('p_EvnPrescr_unexec', $params);
			if (empty($tmp)) {
				throw new Exception('Ошибка запроса к БД', 500);
			}
			if (isset($tmp[0]['Error_Msg'])) {
				throw new Exception($tmp[0]['Error_Msg'], 500);
			}
		}

		if (
			in_array($this->evnClassId, array(11, 13))
			&& $this->parent->hasEvnVizitInReg()
			&& ($this->regionNick != 'vologda' || $this->parent->IsFinish == 2)
		) {
			if ( in_array($this->regionNick, array('pskov', 'ufa', 'vologda')) ) {
				$paidField = 'Paid_id';
			}
			else {
				$paidField = 'RegistryData_IsPaid';
			}

			$registryStatusExceptions = array(4);

			if ( $this->regionNick == 'kareliya' || $this->regionNick == 'penza' ) {
				$registryStatusExceptions[] = 3; // @task https://redmine.swan.perm.ru//issues/124015
			}

			$registryStatusClause = "R.RegistryStatus_id not in (" . implode(',', $registryStatusExceptions) . ")";

			if ( $this->regionNick == 'vologda' ) {
				$registryStatusClause = "R.RegistryStatus_id = 2";
			}

			// проверяем наличие посещений в реестре по ->pid
			$query = "
				select top 1
					E.Evn_id,
				    R.RegistryStatus_id
				from
					v_Evn E with (nolock)
					left join {$this->scheme}.v_RegistryData RD with (nolock) on RD.Evn_id = E.Evn_id
					left join {$this->scheme}.v_Registry R with (nolock) on RD.Registry_id = R.Registry_id
				where
					RD.Evn_id is not null
					and E.Evn_setDT >= '2014-12-01'
					and (
						{$registryStatusClause}
						or (
							R.RegistryStatus_id = 4
							and RD.{$paidField} = 2
						)
					)
					and E.Evn_pid = :Evn_pid
			";
			$dbreg = $this->load->database('registry', true);
			$result = $dbreg->query($query, array(
				'Evn_pid' => $this->pid
			));
			if (is_object($result)) {
				$resp = $result->result('array');
				if (!empty($resp[0]['Evn_id'])) {
					throw new Exception('Удаление посещения невозможно т.к. есть посещения входящие в реестр.', 400);
				}
			} else {
				throw new Exception('Ошибка проверки оплаченности посещений.', 400);
			}
		}

		// Проверяем есть ли услуги параклиники, которые привязаны к текущему движению
		$this->EvnUslugaLinkChange = null;

		if (!in_array(getRegionNick(), array('perm', 'kareliya', 'kz'))) {
			$this->EvnUslugaLinkChange = $this->queryResult("
				select
					eup.EvnUslugaPar_id,
					'unlink' as type
				from
					v_EvnUslugaPar eup (nolock)
				where
					eup.EvnUslugaPar_pid = :EvnVizitPL_id
			", array(
				'EvnVizitPL_id' => $this->id,
			));

			if (!empty($this->EvnUslugaLinkChange) && empty($data['ignoreCheckEvnUslugaChange'])) {
				// выдаём YesNo
				$this->_saveResponse['ignoreParam'] = 'ignoreCheckEvnUslugaChange';
				$this->_saveResponse['Alert_Msg'] = 'С этим посещением есть связные услуги. Удаление посещения приведет к разрыву связи. Продолжить?';
				throw new Exception('YesNo', 703);
			}

			$this->load->model('EvnUslugaPar_model');
			foreach($this->EvnUslugaLinkChange as $usl) {
				switch($usl['type']) {
					case 'unlink':
						// после удаления движения услуги привязываются к корню дерева, поэтому сделаем это перед удалением, иначе хранимка удалит услугу.
						$this->EvnUslugaPar_model->editEvnUslugaPar(array(
							'EvnUslugaPar_id' => $usl['EvnPrescr_pid'],
							'EvnUslugaPar_pid' => null,
							'pmUser_id' => $this->promedUserId,
							'session' => $this->sessionParams
						));
						break;
				}
			}
		}
	}

	/**
	 * Проверки и другая логика после удаления объекта
	 */
	protected function _afterDelete($result)
	{
		parent::_afterDelete($result);
		//$this->_updateMedicalCareKind(array('EvnPL_id' => $this->pid)); // #75656 теперь не надо
		if (getRegionNick() == 'perm' && !empty($this->pid)) {
			$this->load->model('EvnPL_model');
			$this->EvnPL_model->checkEvnPLCrossed(array(
				'EvnPL_id' => $this->pid
			));
		}
	}

	/**
	 * Проверка наличия предыдущих посещений детского отделения
	 */
	protected function hasPreviusChildVizit() {
		foreach($this->parent->evnVizitList as $vizit) {
			$setDT = date_create($vizit['EvnVizitPL_setDate'].' '.(empty($vizit['EvnVizitPL_setTime'])?'00:00':$vizit['EvnVizitPL_setTime']));
			if (!empty($vizit['LpuSectionAge_id']) && $vizit['LpuSectionAge_id'] == 2 && $setDT <= $this->setDT) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Проверка того, что посещение последнее в ТАП
	 */
	protected function isLastVizit() {
		$list = $this->parent->evnVizitList;
		if (end($list) && key($list) == $this->id) {
			return true;
		}
		return false;
	}

	/**
	 * Получение последнего посещения в ТАПе
	 */
	function getLastEvnVizitPL($data) {
		$filters = "";
		$params = array();

		if ( !empty($data['LpuSection_id']) ) {
			$filters .= " and LpuSection_id = :LpuSection_id";
			$params['LpuSection_id'] = $data['LpuSection_id'];
		}

		if ( !empty($data['Person_id']) ) {
			$filters .= " and Person_id = :Person_id";
			$params['Person_id'] = $data['Person_id'];
		}

		$query = "
			select top 1 EvnVizitPL_id
			from v_EvnVizitPL with (nolock)
			where (1 = 1)  {$filters}
			order by EvnVizitPL_setDT desc
		";
		//echo getDebugSQL($query, $params);exit;
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение дублей
	 */
	function getEvnVizitPLDoubles() {
		$doubles = $this->_getEvnVizitPLDoubles();
		$firstDoubleEdit = true;
		$doublesEvnPL = array();
		if (strtotime($this->setDate) >= strtotime('01.06.2017')) {
			foreach ($doubles as $double) {
				if ($double['EvnVizitPL_pid'] == $this->pid) {
					$doublesEvnPL[] = $double;
					if (!empty($double['VizitPLDouble_id'])) {
						$firstDoubleEdit = false;
					}
				}
			}
		}
		if (!empty($this->VizitPLDouble_id)) {
			$firstDoubleEdit = false;
		}

		if ($firstDoubleEdit) {
			$firstDouble = true;
			foreach ($doublesEvnPL as $key => $double) {
				if ($firstDouble) {
					$doublesEvnPL[$key]['VizitPLDouble_id'] = 1;
				} else {
					$doublesEvnPL[$key]['VizitPLDouble_id'] = 2;
				}

				$firstDouble = false;
			}
		}
		// Для посещений с датой после 01.06.2017 если дублирующее посещения находится в той же ТАП
		// вместо предупреждения выводится форма с таблицей, в которой отображаются все такие посещения, включая текущее.
		$doublesEvnPL[] = array(
			'EvnVizitPL_id' => !empty($this->id) ? $this->id : -1,
			'LpuSection_Name' => $this->getFirstResultFromQuery("select LpuSection_Name from v_LpuSection (nolock) where LpuSection_id = :LpuSection_id", array('LpuSection_id' => $this->LpuSection_id)),
			'MedPersonal_Fio' => $this->getFirstResultFromQuery("select Person_Fio from v_MedStaffFact (nolock) where MedStaffFact_id = :MedStaffFact_id", array('MedStaffFact_id' => $this->MedStaffFact_id)),
			'EvnVizitPL_setDate' => date('d.m.Y', strtotime($this->setDate)),
			'EvnVizitPL_pid' => $this->pid,
			'VizitPLDouble_id' => $firstDoubleEdit ? 2 : $this->VizitPLDouble_id,
			'accessType' => 'edit'
		);

		return $doublesEvnPL;
	}

	/**
	 * Редактирование посещения из АПИ
	 */
	function editEvnVizitPLFromAPI($data) {
		// получаем данные посещения
		$this->applyData(array(
			'EvnVizitPL_id' => !empty($data['EvnVizitPL_id'])?$data['EvnVizitPL_id']:null,
			'session' => $data['session']
		));

		// подменяем параметры, пришедшие от клиента
		$this->setAttribute('setdt', $data['Evn_setDT']);
		$this->setAttribute('setdate', date('Y-m-d', strtotime($data['Evn_setDT'])));
		$this->setAttribute('setTime', date('H:i', strtotime($data['Evn_setDT'])));
		$this->setAttribute('lpu_id', $data['Lpu_id']);
		if (!empty($data['VizitClass_id'])) {
			$this->setAttribute('vizitclass_id', $data['VizitClass_id']);
		}
		if (!empty($data['LpuSection_id'])) {
			$this->setAttribute('lpusection_id', $data['LpuSection_id']);
		}
		if (!empty($data['EvnPLBase_id'])) {
			// данные по пациенту берем из ТАП
			$this->setAttribute('pid', $data['EvnPLBase_id']);
			$resp = $this->queryResult("
				select
					EvnPL_id,
					Person_id,
					PersonEvn_id,
					Server_id,
					LpuSection_id
				from
					v_EvnPL (nolock)
				where
					EvnPL_id = :EvnPL_id
			", array(
				'EvnPL_id' => $data['EvnPLBase_id']
			));

			if (!empty($resp[0]['EvnPL_id'])) {
				$this->setAttribute('person_id', $resp[0]['Person_id']);
				$this->setAttribute('personevn_id', $resp[0]['PersonEvn_id']);
				$this->setAttribute('server_id', $resp[0]['Server_id']);
			}
		}
		if (!empty($data['MedStaffFact_id'])) {

			$MedStaffFact_id = $this->getFirstResultFromQuery( // LpuSection - проверка на то, чтобы средний персонал работал в переданном отделении, по ТЗ
				"select top 1 MedStaffFact_id from v_MedStaffFact (nolock) where MedStaffFact_id = :MedStaffFact_id and LpuSection_id = :LpuSection_id",
				array('MedStaffFact_id' => $data['MedStaffFact_id'], 'LpuSection_id' => $this->getAttribute('lpusection_id')) );

			if ($MedStaffFact_id === false)
			{
				throw new Exception('Место работы врача должно быть в указанном отделении');
			}

			$this->setAttribute('medstafffact_id', $MedStaffFact_id);
			$MedPersonal_id = $this->getFirstResultFromQuery("select MedPersonal_id from v_MedStaffFact (nolock) where MedStaffFact_id = :MedStaffFact_id", array('MedStaffFact_id' => $data['MedStaffFact_id']));
			if (!empty($MedPersonal_id)) {
				$this->setAttribute('medpersonal_id', $MedPersonal_id);
			}
		}
		
		if(!empty($data['LpuSection_id'])){
			$this->setAttribute('lpusection_id', $data['LpuSection_id']);
		}
		if (!empty($resp[0]['LpuSection_id'])) {
			// определим профиль 
			$LpuSectionProfile_id = $this->getFirstResultFromQuery("select top 1 LpuSectionProfile_id from v_LpuSection with (nolock) where LpuSection_id = :LpuSection_id", array('LpuSection_id' => $resp[0]['LpuSection_id']));
			if(!empty($LpuSectionProfile_id)){
				$this->setAttribute('lpusectionprofile_id', $LpuSectionProfile_id);
			}
		}
		
		if (!empty($data['TreatmentClass_id'])) {
			$this->setAttribute('treatmentclass_id', $data['TreatmentClass_id']);
		}
		if (!empty($data['ServiceType_id'])) {
			$this->setAttribute('servicetype_id', $data['ServiceType_id']);
		}
		if (!empty($data['VizitType_id'])) {
			$this->setAttribute('vizittype_id', $data['VizitType_id']);
		}
		if (!empty($data['PayType_id'])) {
			$this->setAttribute('paytype_id', $data['PayType_id']);
		}
		if (!empty($data['Mes_id'])) {
			$this->setAttribute('mes_id', $data['Mes_id']);
		}
		if (!empty($data['UslugaComplex_uid'])) {
			$this->setAttribute('uslugacomplex_id', $data['UslugaComplex_uid']);
		}
		if (!empty($data['EvnVizitPL_Time'])) {
			$this->setAttribute('time', $data['EvnVizitPL_Time']);
		}
		if (!empty($data['ProfGoal_id'])) {
			$this->setAttribute('profgoal_id', $data['ProfGoal_id']);
		}
		if (!empty($data['DispClass_id'])) {
			$this->setAttribute('dispclass_id', $data['DispClass_id']);
		}
		if (!empty($data['EvnPLDisp_id'])) {
			$this->setAttribute('evnpldisp_id', $data['EvnPLDisp_id']);
		}
		if (!empty($data['PersonDisp_id'])) {
			$this->setAttribute('persondisp_id', $data['PersonDisp_id']);
		}
		if (!empty($data['Diag_id'])) {
			$this->setAttribute('diag_id', $data['Diag_id']);
		}
		if (!empty($data['DeseaseType_id'])) {
			$this->setAttribute('deseasetype_id', $data['DeseaseType_id']);
		}
		if (!empty($data['Diag_agid'])) {
			$this->setAttribute('diag_agid', $data['Diag_agid']);
		}
		if (!empty($data['RankinScale_id'])) {
			$this->setAttribute('rankinscale_id', $data['RankinScale_id']);
		}
		if (!empty($data['HomeVisit_id'])) {
			$this->setAttribute('homevisit_id', $data['HomeVisit_id']);
		}
		if (!empty($data['MedicalCareKind_id'])) {
			$this->setAttribute('medicalcarekind_id', $data['MedicalCareKind_id']);
		}
		if (!empty($data['MedStaffFact_sid'])) {
			$MedPersonal_sid = $this->getFirstResultFromQuery("select MedPersonal_id from v_MedStaffFact (nolock) where MedStaffFact_id = :MedStaffFact_id", array('MedStaffFact_id' => $data['MedStaffFact_sid']));
			if (!empty($MedPersonal_id)) {
				$this->setAttribute('medpersonal_sid', $MedPersonal_sid);
			}
		}
		
		if($this->regionNick == 'ekb' && !empty($data['Mes_id'])){
			//МЭС для второго и следующих посещений в рамках одного ТАП должен совпадать с МЭС первого посещения
			$whereMes = "";
			if(!empty($data['EvnVizitPL_id'])){
				//если редактируем запись
				$whereMes = " and EVPL.EvnVizitPL_pid in (select top 1 EvnVizitPL_pid from v_EvnVizitPL where EvnVizitPL_id = :EvnVizitPL_id)";
			}elseif (!empty($data['EvnPLBase_id'])) {
				//если создаем новое посещение
				$whereMes = " and EVPL.EvnVizitPL_pid = :EvnPLBase_id";
			}
			if($whereMes){
				$queryMes = "
					SELECT
						EVPL.EvnVizitPL_id,
						EVPL.Mes_id,
						EVPL.EvnVizitPL_setDate,
						EVPL.EvnVizitPL_setTime
					from v_EvnVizitPL EVPL with (nolock)
					WHERE 1=1
						{$whereMes}
					ORDER BY EVPL.EvnVizitPL_setDate ASC, EVPL.EvnVizitPL_setTime ASC";
						
				$resultMes = $this->db->query($queryMes, $data);
				
				if ( is_object($resultMes) ) {
					$arrMes = $resultMes->result('array');
					if(count($arrMes) > 0 && !empty($arrMes[0]['Mes_id'])){
						if(
							(!empty($data['EvnPLBase_id']) && $arrMes[0]['Mes_id'] != $data['Mes_id'])
								||
							(!empty($data['EvnVizitPL_id']) && $data['EvnVizitPL_id'] != $arrMes[0]['EvnVizitPL_id'] && $arrMes[0]['Mes_id'] != $data['Mes_id'])
						){
							return array(array(
								'Error_Msg' => 'МЭС посещения не сопадает с МЭС первого посещения в рамках одного ТАП'
							));
						}
					}
				}
			}
		}
		
		// сохраняем бирку б/з
		$this->_saveVizitFactTime();

		// сохраняем посещение
		$resp = $this->_save();

		return $resp;
	}

	/**
	 * Редактирование посещения для мобильного ФАП
	 */
	function updateEvnVizitPL($input_data) {

		// только если существует EvnVizitPL_id
		if (!empty($input_data['EvnVizitPL_id'])) {
			$sessionParams = getSessionParams();

			// получаем данные посещения
			$this->applyData(array(
				'EvnVizitPL_id' => $input_data['EvnVizitPL_id'],
				'session' => $sessionParams['session']
			));

			// если пришло рабочее место врача, определяем его отделение и врача
			if (!empty($data['MedStaffFact_id'])) {

				$data['LpuSection_id'] = $this->getFirstResultFromQuery(
					"select top 1
					LpuSection_id
				from v_MedStaffFact (nolock)
				where MedStaffFact_id = :MedStaffFact_id",
					array('MedStaffFact_id' => $data['MedStaffFact_id'])
				);

				$data['MedPersonal_id'] = $this->getFirstResultFromQuery(
					"select top 1
					MedPersonal_id
				from v_MedStaffFact (nolock)
				where MedStaffFact_id = :MedStaffFact_id",
					array('MedStaffFact_id' => $data['MedStaffFact_id'])
				);
			}

			// если пришло рабочее место сред. мед. перса, определяем его
			if (!empty($data['MedStaffFact_sid'])) {

				$data['MedPersonal_sid'] = $this->getFirstResultFromQuery(
					"select top 1
					MedPersonal_id
				from v_MedStaffFact (nolock)
				where MedStaffFact_id = :MedStaffFact_sid",
					array('MedStaffFact_sid' => $data['MedStaffFact_sid'])
				);
			}

			// конвертируем некоторые пришедшие поля, в поля хранимой процедуры
			// хотя, почему бы сразу нельзя было назвать нормально???
			// так же уберем те поля которых нет в модели
			$input_data = $this->convertAliasesToStoredProcedureParams($input_data);

			// если параметр есть, устанавливаем его как значение атрибута модели
			foreach ($input_data as $key => $val) { $this->setAttribute(strtolower($key), $val);}

			// сохраняем бирку б/з
			$this->_saveVizitFactTime();

			// сохраняем посещение
			$resp = $this->_save();
			return $resp;

		} else return array("Error_Msg" => "Не указан EvnVizitPL_id");
	}

	/**
	 * Добавление нового посещения из ЭМК
	 */
	function addEvnVizitPL($data) {
		$this->load->model('EPH_model');
		$resp = $this->EPH_model->loadEvnPLForm($data);
		if (empty($resp[0]['accessType'])) {
			return array('Error_Msg' => 'Ошибка получения информации о случае АПЛ');
		} else if ($resp[0]['accessType'] == 'view') {
			if (!empty($resp[0]['AlertReg_Msg'])) {
				return array('Error_Msg' => $resp[0]['AlertReg_Msg']);
			} else if (empty($resp[0]['canCreateVizit'])) {
				return array('Error_Msg' => 'Случай АПЛ недоступен для редактирования');
			}
		}
		
		if($this->regionNick == 'penza' && $this->evnClassId != 13 && !empty($data['LpuSectionProfile_id'])) {
			$this->LpuSectionProfile_id = $data['LpuSectionProfile_id'];
			//$this->id = null;
			$this->pid = $data['EvnPL_id'];
			$this->_checkChangeLpuSectionProfileId();
		}
		
		if($this->regionNick == 'ufa' && $this->evnClassId != 13) {
			if ( $this->UslugaComplex_id > 0 ) {
				if (isset($this->parent)) {
					$isFinish = (2 == $this->parent->IsFinish);
					$vizitCodePart = self::vizitCodePart($this->vizitCode);

					//Проверки по кодам других посещений
					$otherVizitCnt = 0;
					$diagIdList = array();
					foreach ( $this->parent->evnVizitList as $id => $row ) {
						if (isset($row['Diag_id'])) {
							$diagIdList[] = $row['Diag_id'];
						}
						if ($this->id == $id) {
							continue;
						}
						$otherVizitCnt++;
						if (empty($row['UslugaComplex_Code'])) {
							continue;
						}
						// Если сохраняемое посещение профилактическое или однократное посещение по заболеванию и в рамках ТАП имеются какие-либо другие коды посещений...
						if ( in_array($vizitCodePart, self::oneVizitCodePartList()) ) {
							// ... не сохранять, выдать ошибку
							$msg = 'профилактического/консультативного посещения';
							switch ($vizitCodePart) {
								case '871':
									$msg = 'однократного посещения по заболеванию';
									break;
								case '824':
								case '825':
									$msg = 'посещения по неотложной помощи';
									break;
							}
							throw new Exception('Сохранение ' . $msg . ' невозможно, т.к. в рамках текущего ТАП имеются другие посещения', 400);
						}
						$vizitCodePartAlt = self::vizitCodePart($row['UslugaComplex_Code']);
						// Если в рамках текущего ТАП имеется профилактическое посещение или однократное посещение по заболеванию...
						if ( in_array($vizitCodePartAlt, self::oneVizitCodePartList()) ) {
							// ... не сохранять, выдать ошибку
							$msg = 'профилактического посещения';
							switch ($vizitCodePartAlt) {
								case '871':
									$msg = 'однократного посещения по заболеванию';
									break;
								case '824':
								case '825':
									$msg = 'посещения по неотложной помощи';
									break;
							}
							throw new Exception('Сохранение посещения невозможно, т.к. в рамках текущего ТАП имеется посещение с кодом ' . $msg, 400);
						}
					}
				} else {
					throw new Exception('Не удалось прочитать ТАП. Сохранение невозможно.', 500);
				}
			}
		}

		// получаем данные предыдущего посещения
		$query = "
			select top 1 
				EVPL.EvnVizitPL_id,
				EX.EvnXml_id
			from 
				v_EvnVizitPL EVPL with(nolock)
				outer apply (
					select top 1 EX.EvnXml_id
					from v_EvnXml EX with(nolock)
					where EX.Evn_id = EVPL.EvnVizitPL_id and EX.XmlType_id = 3
					order by EX.EvnXml_insDT desc
				) EX
			where 
				EvnVizitPL_pid = :EvnPL_id 
			order by 
				EvnVizitPL_setDT desc
		";
		$prev = $this->getFirstRowFromQuery($query, $data);
		if (!is_array($prev)) {
			return array('Error_Msg' => 'Не удалось определить предыдущее движение');
		}

		if (getRegionNick() == 'kareliya') {
			$query = "
				select
					EVPL.EvnVizitPL_setDate,
					vt.VizitType_SysNick
				from
					v_EvnVizitPL EVPL with (nolock)
					inner join v_EvnPL EPL with (nolock) on EPL.EvnPL_id = EVPL.EvnVizitPL_pid
					left join v_VizitType vt with (nolock) on vt.VizitType_id = EVPL.VizitType_id 
				where
					EVPL.EvnVizitPL_pid = :EvnPL_id 
					and EPL.EvnPL_IsFinish != 2 
			";

			$result = $this->queryResult($query, array(
				'EvnPL_id' => $data['EvnPL_id']
			));

			if (empty($result))
				throw new Exception('Не удалось найти случай по данному идентификатору');

			// Проверим, что во всех найденных посещениях указана допустимая цель обращения (по VizitType_SysNick).
			// Для посещений, осуществленных в период с 01.01.2017 до 31.12.2018, допускается 'desease' и 'dispnabl',
			// а для посещений, осуществленных, начиная с 01.01.2019, допускается только 'desease'.
			// Если найдено посещение с какой-либо другой целью, генерируется исключение с соответствующим текстом.
			foreach ($result as $vizit)
			{
				if (!($sysNick = $vizit['VizitType_SysNick']))
					throw new Exception('Не указана цель одного из предыдущих посещений.');
				
				if (!(($setDate = $vizit['EvnVizitPL_setDate']) &&
						($setDate = $setDate->format('Y-m-d'))))
					throw new Exception('Не указана дата одного из предыдущих посещений.');
			
				if ($setDate >= '2019-01-01')
				{
					if ($sysNick != 'desease')
						throw new Exception('Случай АПЛ с посещением, отличным от "Обращение по заболеванию", должен быть закрыт!');
				}
				else
				{
					if ($setDate >= '2017-01-01' && !in_array($sysNick, ['desease', 'dispnabl']))
						throw new Exception('Случай АПЛ с посещением, отличным от "Обращение по поводу заболевания" или "Диспансерное наблюдение", должен быть закрыт!');
				}
			}
		}

		if (getRegionNick() == 'vologda') {
			$this->load->model('EvnPL_model');
			$this->EvnPL_model->checkEvnVizitsPL(array('LpuSectionProfile_id' => $data['LpuSectionProfile_id'], 'EvnPL_id' => $data['EvnPL_id'], 'closeAPL' => 0));
		}

		//Проверка на второе посещение НМП
		$this->_controlDoubleNMP($data['EvnPL_id'], null, false);

		$this->applyData(array(
			'EvnVizitPL_id' => $prev['EvnVizitPL_id'],
			'session' => $data['session'],
		));

		$dt = date_create();

		// убираем лишние параметры
		$this->setAttribute('id', null);
		$this->setAttribute('setdt', $dt);
		$this->setAttribute('setdate', $dt->format('Y-m-d'));
		$this->setAttribute('setTime', $dt->format('H:i'));
		$this->setAttribute('uslugacomplex_id', null);
		$this->setAttribute('medstafffact_id', $data['MedStaffFact_id']);
		if (!empty($data['LpuSection_id'])) {
			$this->setAttribute('lpusection_id', $data['LpuSection_id']);
		}
		if (!empty($data['MedPersonal_id'])) {
			$this->setAttribute('medpersonal_id', $data['MedPersonal_id']);
		}

		$this->setAttribute('timetablegraf_id', (!empty($data['TimetableGraf_id']))?$data['TimetableGraf_id']:null);
		$this->setAttribute('evndirection_id', (!empty($data['EvnDirection_id']))?$data['EvnDirection_id']:null);

		if ( $this->regionNick == 'penza' ){
			$this->setAttribute('VizitType_id', null);
		}

		$this->setScenario(self::SCENARIO_AUTO_CREATE);
		$this->isNewRecord = true;

		// сохраняем посещение
		$this->beginTransaction();

		try {
			$this->setParams(array_merge($data, array(
				'copyEvnXml_id' => $prev['EvnXml_id'],
				'allowCreateEmptyEvnDoc' => 2,
			)));
			$this->_beforeSave();
			$resp = $this->_save();
			$this->setAttribute('id', $resp[0]['EvnVizitPL_id']);
			$this->_afterSave($resp);
		} catch(Exception $e) {
			$this->rollbackTransaction();
			if ($e->getMessage() == 'YesNo') {
				return array('success'=>false, 'Error_Code'=>$e->getCode(), 'Error_Msg'=>'YesNo', 'Alert_Msg'=>$this->_saveResponse['Alert_Msg'], 'ignoreParam'=>$this->_saveResponse['ignoreParam']);
			}
			throw $e;
		}

		$this->commitTransaction();

		return $resp;
	}

	/**
	 * Проверка корректности заполнения обязательных полей в онкоспецифике
	 */
	protected function _checkOnkoSpecifics() {
		if ( $this->regionNick != 'ufa' || !$this->isLastVizit() || $this->parent->IsFinish != 2 || $this->payTypeSysNick != 'oms' || empty($this->UslugaComplex_id) ) {
			return true;
		}

		$UslugaComplex_Code = $this->getFirstResultFromQuery("select top 1 UslugaComplex_Code from v_UslugaComplex with (nolock) where UslugaComplex_id = :UslugaComplex_id", array('UslugaComplex_id' => $this->UslugaComplex_id));

		if (
			!empty($UslugaComplex_Code)
			&& (
				in_array(substr($UslugaComplex_Code, 1, 2), array('74', '75', '76', '77'))
				|| in_array(substr($UslugaComplex_Code, -3), array('874', '875'))
			)
		) {
			return true;
		}

		$checkResult = $this->getFirstResultFromQuery("
			select top 1 evpl.EvnVizitPL_id
			from v_EvnVizitPL evpl (nolock)
				inner join v_Diag Diag (nolock) on Diag.Diag_id = evpl.Diag_id
				left join v_MorbusOnkoVizitPLDop movpld (nolock) on movpld.EvnVizit_id = evpl.EvnVizitPL_id 
			where 
				evpl.EvnVizitPL_id = :EvnVizitPL_id
				and movpld.EvnDiagPLSop_id is null
				and ((Diag.Diag_Code >= 'C00' AND Diag.Diag_Code <= 'C97') or (Diag.Diag_Code >= 'D00' AND Diag.Diag_Code <= 'D09'))
				and movpld.MorbusOnkoVizitPLDop_id is null
				/*and (
					movpld.MorbusOnkoVizitPLDop_id is null or 
					(
						not exists (select top 1 MorbusOnkoLink_id from v_MorbusOnkoLink MOL (nolock) where movpld.MorbusOnkoVizitPLDop_id = MOL.MorbusOnkoVizitPLDop_id) and 
						movpld.HistologicReasonType_id is null
					)
				)*/
		", array('EvnVizitPL_id' => $this->id));

		$checkResult2 = $this->getFirstResultFromQuery("
			select top 1 evpl.EvnVizitPL_id
			from v_EvnVizitPL evpl (nolock)
				inner join v_Diag Diag (nolock) on Diag.Diag_id = evpl.Diag_id
				inner join v_EvnDiagPLSop eds (nolock) on eds.EvnDiagPLSop_pid = evpl.EvnVizitPL_id
				inner join v_Diag DiagS (nolock) on DiagS.Diag_id = eds.Diag_id
				left join v_MorbusOnkoVizitPLDop movpld (nolock) on movpld.EvnVizit_id = evpl.EvnVizitPL_id and movpld.EvnDiagPLSop_id = eds.EvnDiagPLSop_id
			where 
				evpl.EvnVizitPL_id = :EvnVizitPL_id
				and (((DiagS.Diag_Code >= 'C00' AND DiagS.Diag_Code <= 'C80') or DiagS.Diag_Code = 'C97') and (Diag.Diag_Code = 'D70'))
				and movpld.MorbusOnkoVizitPLDop_id is null
				/*and (
					movpld.MorbusOnkoVizitPLDop_id is null or 
					(
						not exists (select top 1 MorbusOnkoLink_id from v_MorbusOnkoLink MOL (nolock) where movpld.MorbusOnkoVizitPLDop_id = MOL.MorbusOnkoVizitPLDop_id) and 
						movpld.HistologicReasonType_id is null
					)
				)*/
		", array('EvnVizitPL_id' => $this->id));

		if ( !empty($checkResult) || !empty($checkResult2) ) {
			throw new Exception('В случае лечения установлен диагноз из диапазона С00-C97 или D00-D09. Заполните раздел "Специфика (онкология)" или проверьте корректность заполнения обязательных полей данного раздела: «Повод обращения», «Стадия опухолевого процесса», «Т», «N», «M» (Стадия опухолевого процесса по системе TNM) в блоках ФОМС и Канцер регистр. Обязательные поля раздела отмечены символом *.');
		}

		return true;
	}

	/**
	 * получить данные из $this->_savedData
	 */
	function getEvnVizitPLSavedData($data) {

		$this->applyData(array(
			'EvnVizitPL_id' => $data['EvnVizitPL_id'],
			'session' => $data['session']
		));

		$response = array();
		$attributes = $this->defAttributes();
		foreach ($this->_savedData as $field => $val) {
			if (!empty($attributes[$field]['alias'])) {
				$response[$attributes[$field]['alias']] = $val;
			}
		}

		return $response;
	}

	/**
	 * Сохранение схем лекарственной терапии
	 */
	protected function _saveDrugTherapyScheme()
	{
		$DrugTherapyScheme_ids = $this->_params['DrugTherapyScheme_ids'];

		$resp = $this->queryResult("
			select
				EvnVizitPLDrugTherapyLink_id,
				DrugTherapyScheme_id
			from
				v_EvnVizitPLDrugTherapyLink (nolock)
			where
				EvnVizitPL_id = :EvnVizitPL_id
		", array(
			'EvnVizitPL_id' => $this->id
		));

		// могут сохранять одинаковые схемы, поэтому считаем количество схем
		$dtsArray = array();
		if (!empty($DrugTherapyScheme_ids) && is_array($DrugTherapyScheme_ids)) {
			foreach ($DrugTherapyScheme_ids as $one) {
				if (isset($dtsArray[$one])) {
					$dtsArray[$one]++;
				} else {
					$dtsArray[$one] = 1;
				}
			}
		}

		foreach ($resp as $respone) {
			// удаляем лишние
			if (isset($dtsArray[$respone['DrugTherapyScheme_id']]) && $dtsArray[$respone['DrugTherapyScheme_id']] > 0) {
				$dtsArray[$respone['DrugTherapyScheme_id']]--;
			} else {
				$resp_del = $this->queryResult("
					declare
						@ErrCode int,
						@ErrMessage varchar(4000);

					exec p_EvnVizitPLDrugTherapyLink_del
						@EvnVizitPLDrugTherapyLink_id = :EvnVizitPLDrugTherapyLink_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;

					select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				", array(
					'EvnVizitPLDrugTherapyLink_id' => $respone['EvnVizitPLDrugTherapyLink_id']
				));

				if (!empty($resp_del[0]['Error_Msg'])) {
					throw new Exception($resp_del[0]['Error_Msg']);
				}
			}
		}

		// добавляем новые
		foreach ($dtsArray as $DrugTherapyScheme_id => $count) {
			for ($i = 0; $i < $count; $i++) {
				$resp_save = $this->queryResult("
					declare
						@ErrCode int,
						@ErrMessage varchar(4000),
						@EvnVizitPLDrugTherapyLink_id bigint = null;

					exec p_EvnVizitPLDrugTherapyLink_ins
						@EvnVizitPLDrugTherapyLink_id = @EvnVizitPLDrugTherapyLink_id output,
						@EvnVizitPL_id = :EvnVizitPL_id,
						@DrugTherapyScheme_id = :DrugTherapyScheme_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;

					select @EvnVizitPLDrugTherapyLink_id as EvnVizitPLDrugTherapyLink_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				", array(
					'EvnVizitPL_id' => $this->id,
					'DrugTherapyScheme_id' => $DrugTherapyScheme_id,
					'pmUser_id' => $this->promedUserId
				));

				if (!empty($resp_save[0]['Error_Msg'])) {
					throw new Exception($resp_save[0]['Error_Msg']);
				}
			}
		}
	}

	protected function _getNextNumGroup() {
		$this->_groupNum++;

		while ( in_array($this->_groupNum, $this->_groupNumExceptions) ) {
			$this->_groupNum++;
		}

		return $this->_groupNum;
	}

	/**
	 * Группировка посещений
	 */
	public function updateEvnVizitNumGroup($EvnVizitPL_pid) {
		if ( $this->regionNick == 'vologda' ) {
			$query = "
				select
					evpl.EvnVizitPL_id,
					evpl.TreatmentClass_id,
					VT.VizitType_SysNick,
					evpl.PayType_id,
					LSP.LpuSectionProfile_Code,
					r.RegistryStatus_id,
					ISNULL(evpl.EvnVizitPL_IsPaid, 1) as EvnVizitPL_IsPaid,
					evpl.EvnVizitPL_NumGroup 
				from v_EvnVizitPL evpl with (nolock)
					left join v_VizitType VT with (nolock) on VT.VizitType_id = evpl.VizitType_id
					left join v_LpuSectionProfile LSP with(nolock) on LSP.LpuSectionProfile_id = evpl.LpuSectionProfile_id
					left join r35.v_Registry r with (nolock) on r.Registry_id = evpl.Registry_sid
				where evpl.EvnVizitPL_pid = :EvnVizitPL_pid
			";

			if ( $this->evnClassSysNick == 'EvnVizitPLStom' ) {
				$query .= "
					and exists (select top 1 EvnUslugaStom_id from v_EvnUslugaStom with (nolock) where EvnUslugaStom_pid = evpl.EvnVizitPL_id and ISNULL(EvnUslugaStom_IsVizitCode, 1) = 1)
					and not exists (
						select top 1 edpls.EvnDiagPLStom_id
						from v_EvnDiagPLStom edpls with (nolock)
							inner join v_EvnUslugaStom eus with (nolock) on eus.EvnDiagPLStom_id = edpls.EvnDiagPLStom_id
						where eus.EvnUslugaStom_pid = evpl.EvnVizitPL_id
							and ISNULL(edpls.EvnDiagPLStom_IsClosed, 1) = 1  
					)
				";
			}

			$list = $this->queryResult($query, array('EvnVizitPL_pid' => $EvnVizitPL_pid), true);

			if ( false === $list )  {
				throw new Exception('Ошибка при чтении списка посещений', 500);
			}
			else if ( !is_array($list) ) {
				$list = array();
			}

			$this->_groupNumExceptions = array();

			foreach ( $list as $evnVizit ) {
				if (
					!empty($evnVizit['EvnVizitPL_NumGroup'])
					&& ($evnVizit['EvnVizitPL_IsPaid'] == 2 || $evnVizit['RegistryStatus_id'] == 2)
				) {
					$this->_groupNumExceptions[] = $evnVizit['EvnVizitPL_NumGroup'];
				}
			}

			// Чистим номера групп
			$updateResp = $this->getFirstRowFromQuery("
					declare
						@Error_Code bigint,
						@Error_Message varchar(4000);
					
					set nocount on;
					
					begin try
						update EvnVizitPL with (rowlock)
						set EvnVizitPL_NumGroup = null
						where EvnVizitPL_id in (select EvnVizitPL_id from v_EvnVizitPL with (nolock) where EvnVizitPL_pid = :EvnVizitPL_pid)
							" . (count($this->_groupNumExceptions) > 0 ? "and EvnVizitPL_NumGroup not in (" . implode(",", $this->_groupNumExceptions) . ")" : "") . "
					end try
					
					begin catch
						set @Error_Code = error_number()
						set @Error_Message = error_message()
					end catch
					
					set nocount off;
					
					Select @Error_Code as Error_Code, @Error_Message as Error_Msg
				", array(
					'EvnVizitPL_pid' => $EvnVizitPL_pid,
				)
			);

			if ($updateResp === false || !is_array($updateResp) || count($updateResp) == 0) {
				throw new Exception('Ошибка запроса к БД', 500);
			}
			else if (!empty($updateResp['Error_Msg'])) {
				throw new Exception($updateResp['Error_Msg'], 500);
			}

			$PasportMO_IsAssignNasel = $this->getFirstResultFromQuery("select top 1 PasportMO_IsAssignNasel from fed.v_PasportMO with (nolock) where Lpu_id = :Lpu_id", array('Lpu_id' => $this->Lpu_id));

			$groupNumByPayType = array();
			$this->_groupNum = 0;

			foreach ( $list as $evnVizit ) {
				if ( !empty($evnVizit['EvnVizitPL_NumGroup']) && in_array($evnVizit['EvnVizitPL_NumGroup'], $this->_groupNumExceptions) ) {
					continue;
				}

				if ( !isset($groupNumByPayType[$evnVizit['PayType_id']]) ) {
					$groupNumByPayType[$evnVizit['PayType_id']] = array();

					$groupNumByPayType[$evnVizit['PayType_id']][2] = $this->_getNextNumGroup();
					$groupNumByPayType[$evnVizit['PayType_id']][3] = $this->_getNextNumGroup();
					$groupNumByPayType[$evnVizit['PayType_id']][4] = $this->_getNextNumGroup();
					$groupNumByPayType[$evnVizit['PayType_id']][5] = $this->_getNextNumGroup();
				}

				if ( $PasportMO_IsAssignNasel != 2 || $this->evnClassSysNick == 'EvnVizitPLStom' ) {
					if ( $evnVizit['TreatmentClass_id'] == 2 ) {
						$EvnVizitPLGroupNum = $this->_getNextNumGroup();
					} else {
						$EvnVizitPLGroupNum = $groupNumByPayType[$evnVizit['PayType_id']][5];
					}
				}
				else {
					if ( $evnVizit['TreatmentClass_id'] == 2 ) {
						$EvnVizitPLGroupNum = $this->_getNextNumGroup();
					} else if (in_array($evnVizit['LpuSectionProfile_Code'], array('3', '136', '137', '184'))) {
						$EvnVizitPLGroupNum = $groupNumByPayType[$evnVizit['PayType_id']][2];
					} else if ($evnVizit['VizitType_SysNick'] == 'centrrec') {
						$EvnVizitPLGroupNum = $groupNumByPayType[$evnVizit['PayType_id']][3];
					} else if ($evnVizit['TreatmentClass_id'] == 8) {
						$EvnVizitPLGroupNum = $groupNumByPayType[$evnVizit['PayType_id']][4];
					} else {
						$EvnVizitPLGroupNum = $groupNumByPayType[$evnVizit['PayType_id']][5];
					}
				}

				$updateResp = $this->getFirstRowFromQuery("
					declare
						@Error_Code bigint,
						@Error_Message varchar(4000);
					
					set nocount on;
					
					begin try
						update EvnVizitPL with (rowlock)
						set EvnVizitPL_NumGroup = :EvnVizitPL_NumGroup
						where EvnVizitPL_id = :EvnVizitPL_id
					end try
					
					begin catch
						set @Error_Code = error_number()
						set @Error_Message = error_message()
					end catch
					
					set nocount off;
					
					Select @Error_Code as Error_Code, @Error_Message as Error_Msg
				", array(
					'EvnVizitPL_id' => $evnVizit['EvnVizitPL_id'],
					'EvnVizitPL_NumGroup' => $EvnVizitPLGroupNum,
				));

				if ($updateResp === false || !is_array($updateResp) || count($updateResp) == 0) {
					throw new Exception('Ошибка запроса к БД', 500);
				}
				else if (!empty($updateResp['Error_Msg'])) {
					throw new Exception($updateResp['Error_Msg'], 500);
				}
			}
		}
	}

	/**
	 * Проверка наличия связок для отображения полей
	 */
	function checkMesOldUslugaComplexFields($data) {
		$response = array(
			'hasDrugTherapySchemeLinks' => false,
			'hasRehabScaleLinks' => false,
			'hasSofaLinks' => false,
			'Error_Msg' => ''
		);


		$data['MesType_id'] = 10;
		if (in_array($data['LpuUnitType_id'], array('6','7','9'))) {
			$data['MesType_id'] = 9;
		}

		$dtsQueries = array();
		$dtsParams = array(
			'MesType_id' => $data['MesType_id'],
			'EvnVizitPL_setDate' => $data['EvnVizitPL_setDate'] ?? date('Y-m-d')
		);
		if (!empty($data['Diag_id'])) {
			$drugTherapySchemeQueries[] = "
				select distinct
					mouc.DrugTherapyScheme_id
				from
					v_MesOldUslugaComplex mouc (nolock)
					inner join v_MesOld mo (nolock) on mo.Mes_id = mouc.Mes_id
					inner join v_DrugTherapyScheme dts (nolock) on dts.DrugTherapyScheme_id = mouc.DrugTherapyScheme_id
				where
					mouc.Diag_id = :Diag_id
					and mouc.DrugTherapyScheme_id is not null
					and ISNULL(mo.MesType_id, :MesType_id) = :MesType_id
					and isnull(dts.DrugTherapyScheme_endDate, :EvnVizitPL_setDate) >= :EvnVizitPL_setDate
			";
			$dtsParams['Diag_id'] = $data['Diag_id'];
		}
		if (!empty($data['EvnVizitPL_id'])) {
			$drugTherapySchemeQueries[] = "
				select distinct
					mouc.DrugTherapyScheme_id
				from
					v_EvnUsluga eu (nolock)
					inner join v_MesOldUslugaComplex mouc (nolock) on mouc.UslugaComplex_id = eu.UslugaComplex_id
					inner join v_MesOld mo (nolock) on mo.Mes_id = mouc.Mes_id
					inner join v_DrugTherapyScheme dts (nolock) on dts.DrugTherapyScheme_id = mouc.DrugTherapyScheme_id
				where
					eu.EvnUsluga_pid = :EvnVizitPL_id
					and mouc.DrugTherapyScheme_id is not null
					and ISNULL(mo.MesType_id, :MesType_id) = :MesType_id
					and isnull(dts.DrugTherapyScheme_endDate, :EvnVizitPL_setDate) >= :EvnVizitPL_setDate
			";
			$dtsParams['EvnVizitPL_id'] = $data['EvnVizitPL_id'];
		}
		if (!empty($drugTherapySchemeQueries)) {
			// проверяем наличие связок
			$resp = $this->queryResult(implode(" union ", $drugTherapySchemeQueries), $dtsParams);

			if (!empty($resp[0]['DrugTherapyScheme_id'])) {
				$response['hasDrugTherapySchemeLinks'] = true;
				$response['DrugTherapySchemeIds'] = array();
				foreach ($resp as $respone) {
					$response['DrugTherapySchemeIds'][] = $respone['DrugTherapyScheme_id'];
				}
			}
		}

		if (!empty($data['Diag_id'])) {
			$resp = $this->queryResult("
				select top 1
					mouc.MesOldUslugaComplex_id
				from
					v_MesOldUslugaComplex mouc (nolock)
					inner join v_MesOld mo (nolock) on mo.Mes_id = mouc.Mes_id
				where
					mouc.Diag_id = :Diag_id
					and mouc.MesOldUslugaComplex_SofaScalePoints is not null
					and ISNULL(mo.MesType_id, :MesType_id) = :MesType_id
			", array(
				'Diag_id' => $data['Diag_id'],
				'MesType_id' => $data['MesType_id']
			));

			if (!empty($resp[0]['MesOldUslugaComplex_id'])) {
				$response['hasSofaLinks'] = true;
			}
		}

		if (!empty($data['EvnVizitPL_id'])) {
			// проверяем наличие связок
			$resp = $this->queryResult("
				select top 1
					mouc.MesOldUslugaComplex_id
				from
					v_MesOldUslugaComplex mouc (nolock)
					left join v_MesOld mo (nolock) on mo.Mes_id = mouc.Mes_id
					left join EvnUsluga eu (nolock) on eu.UslugaComplex_id = mouc.UslugaComplex_id
					left join v_Evn ev (nolock) on ev.Evn_id = eu.Evn_id
				where
					ev.Evn_pid = :EvnVizitPL_id
					and mouc.RehabScale_id is not null
					and ISNULL(mo.MesType_id, :MesType_id) = :MesType_id
			", array(
				'EvnVizitPL_id' => $data['EvnVizitPL_id'],
				'MesType_id' => $data['MesType_id']
			));

			if (!empty($resp[0]['MesOldUslugaComplex_id'])) {
				$response['hasRehabScaleLinks'] = true;
			}
		}

		return $response;
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updatePersonDispId($id, $value = null)
	{
		return $this->_updateAttribute($id, 'persondisp_id', $value);
	}

	/**
	 * Получение вида обращения и цели посещения для вывода метки в печати осмотра
	 * @param $data
	 */
	function getDataForDispPrint($data) {
		return $this->queryResult("
			select top 1
				TC.TreatmentClass_Code,
				VT.VizitType_Code
			from
				v_EvnVizitPL EVPL (nolock)
				left join v_TreatmentClass TC (nolock) on TC.TreatmentClass_id = EVPL.TreatmentClass_id
				left join v_VizitType VT (nolock) on VT.VizitType_id = EVPL.VizitType_id
			where
				EVPL.EvnVizitPL_id = :EvnVizitPL_id
		", array(
			'EvnVizitPL_id' => $data['EvnVizitPL_id']
		));
	}


    /**
     * @param int $id
     * @param int|null $UslugaMedType_id
     * @return array
     * @throws Exception
     */
    public function updateUslugaMedTypeId($id, $UslugaMedType_id = null)
    {
        if (getRegionNick() === 'kz') {
            $this->load->model('UslugaMedType_model');

            return $this->UslugaMedType_model->saveUslugaMedTypeLink([
                'UslugaMedType_id' => $UslugaMedType_id,
                'Evn_id' => $id,
                'pmUser_id' => $this->promedUserId
            ]);

            if (!$this->isSuccessful($result)) {
                throw new Exception($result[0]['Error_Msg'], $result[0]['Error_Code']);
            }
        }
    }

    /**
     * @throws Exception
     */
    protected function saveUslugaMedTypeLink()
    {
        if (getRegionNick() === 'kz') {
            $result = $this->updateUslugaMedTypeId($this->id, $this->_params['UslugaMedType_id']);

            if (!$this->isSuccessful($result)) {
                throw new Exception($result[0]['Error_Msg'], $result[0]['Error_Code']);
            }
        }
    }

    function getEvnVizitPLSetDate($data)
	{
		return $this->db->query("
			select 
				EVPL.EvnVizitPL_setDate
			from 
				v_EvnVizitPL EVPL with (nolock)
			where 
				EVPL.EvnVizitPL_id = :EvnVizitPL_id
		", $data);
	}

	public function getOne($params) {
    	$query = "select
				EvnVizitPL_id,
				EvnVizitPL_setDT,
				EvnVizitPL_pid,
				EvnVizitPL_rid,
				Lpu_id,
				Server_id,
				Person_id,
				LpuSection_id,
				MedPersonal_id,
				EvnDirection_id,
				Diag_id
			from dbo.v_EvnVizitPL
			where EvnVizitPL_id = :EvnVizitPL_id
    	";
    	return $this->getFirstRowFromQuery($query, $params);
	}
}

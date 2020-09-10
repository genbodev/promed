<?php
defined("BASEPATH") or die ("No direct script access allowed");
require_once("EvnVizitAbstract_model.php");
require_once("EvnVizitPL_model_get.php");
require_once("EvnVizitPL_model_check.php");
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package      PromedWeb
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @link         http://swan.perm.ru/PromedWeb
 * 
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
 * @property int $Tooth_id
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
 * @property MorbusOnkoVizitPLDop_model $MorbusOnkoVizitPLDop_model
 * @property EvnUslugaPar_model $EvnUslugaPar_model
 * @property CureStandart_model $CureStandart_model
 * @property EPH_model $EPH_model
 * @property EvnPL_model $EvnPL_model
 * @property PregnancyEvnVizitPL_model $PregnancyEvnVizitPL_model
 * @property Person_model $pmodel
 */
class EvnVizitPL_model extends EvnVizitAbstract_model
{
	protected $_parentClass = "EvnPL_model";
	/**
	 * @var string код места посещения
	 */
	public $_serviceTypeSysNick = null;
	/**
	 * @var string код цели посещения
	 */
	public $_vizitTypeSysNick = null;
	/**
	 * @var array Данные отделения
	 */
	public $_LpuSectionData = [];
	/**
	 * @var int Код
	 */
	public $_lpuUnitSetCode = null;
	/**
	 * @var string Код посещения
	 */
	public $_vizitCode = null;
	/**
	 * @var array Список услуг посещения, в т.ч. с кодом посещения
	 */
	public $_evnUslugaList = null;
	/**
	 * @var array Список исключений из номеров групп
	 */
	public $_groupNumExceptions = [];
	/**
	 * @var int Счетчик номеров групп посещений
	 */
	public $_groupNum = 0;
	// дубли
	protected $_doubles = [];
	// последние 3 цифры кодов однократного профилактического посещения
	static public $profOneVizitCodePartList = ['805', '811', '872', '890', '891', '892', '816', '817', '907', '908'];
	// последние 3 цифры кодов однократного посещения по неотложке
	static public $citoOneVizitCodePartList = ['824', '825'];
	// последние 3 цифры кодов однократного посещения по заболеванию
	static public $morbusOneVizitCodePartList = ['871'];
	// последние 3 цифры кодов многократного посещения по заболеванию
	static public $morbusMultyVizitCodePartList = ['836', '865', '866', '888', '889'];

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
		$arr[self::ID_KEY]["alias"] = "EvnVizitPL_id";
		$arr["pid"]["alias"] = "EvnPL_id";
		$arr["setdate"]["alias"] = "EvnVizitPL_setDate";
		$arr["settime"]["alias"] = "EvnVizitPL_setTime";
		$arr["disdt"]["alias"] = "EvnVizitPL_disDT";
		$arr["diddt"]["alias"] = "EvnVizitPL_didDT";
		$arr["statusdate"]["alias"] = "EvnVizitPL_statusDate";
		$arr["isinreg"]["alias"] = "EvnVizitPL_IsInReg";
		$arr["ispaid"]["alias"] = "EvnVizitPL_IsPaid";
		$arr["uet"]["alias"] = "EvnVizitPL_Uet";
		$arr["uetoms"]["alias"] = "EvnVizitPL_UetOMS";
		$arr["healthkind_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "HealthKind_id",
			"label" => "Группа здоровья",
			"save" => "trim",
			"type" => "id",
			"updateTable" => "EvnVizitPL"
		];
		$arr["risklevel_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "RiskLevel_id",
			"label" => "Фактор Риска",
			"save" => "trim",
			"type" => "id",
			"updateTable" => "EvnVizitPL"
		];
		$arr["wellnesscenteragegroups_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "WellnessCenterAgeGroups_id",
			"label" => "Группа ЦЗ",
			"save" => "trim",
			"type" => "id",
			"updateTable" => "EvnVizitPL"
		];
		$arr["homevisit_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "HomeVisit_id",
			"label" => "Идентификатор вызова на дом",
			"save" => "trim",
			"type" => "id"
		];
		$arr["evnprescr_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "EvnPrescr_id",
			"label" => "Связанное назначение",
			"save" => "trim",
			"type" => "id"
		];
		$arr["dispclass_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "DispClass_id",
			"label" => "Тип диспансеризации/мед. осмотра",
			"save" => "trim",
			"type" => "id"
		];
		$arr["dispprofgoaltype_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "DispProfGoalType_id",
			"label" => "Тип диспансеризации/мед. осмотра",
			"save" => "trim",
			"type" => "id",
			"updateTable" => "EvnVizitPL"
		];
		$arr["evnpldisp_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "EvnPLDisp_id",
			"label" => "Карта диспансеризации/мед. осмотра",
			"save" => "trim",
			"type" => "id"
		];
		$arr["persondisp_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "PersonDisp_id",
			"label" => "Карта дисп. учёта",
			"save" => "trim",
			"type" => "id",
			"updateTable" => "EvnVizitPL"
		];
		$arr["lpusectionprofile_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "LpuSectionProfile_id",
			"label" => "Профиль посещения",
			"save" => "trim",
			"type" => "id"
		];
		$arr["treatmentclass_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "TreatmentClass_id",
			"label" => "Вид обращения",
			"save" => "",
			"type" => "id",
			"updateTable" => "EvnVizitPL"
		];
		$arr["vizitpldouble_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_NOT_SAFE,
			],
			"alias" => "VizitPLDouble_id",
			"label" => "Признак включения дубля в реестр",
			"save" => "",
			"type" => "id"
		];
		$arr["isotherdouble"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "EvnVizitPL_IsOtherDouble",
			"label" => "Признак дубля посещения в другом ТАП",
			"save" => "",
			"type" => "id"
		];
		$arr["servicetype_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "ServiceType_id",
			"label" => "Место",
			"save" => "required",
			"type" => "id",
			"updateTable" => "EvnVizitPL"
		];
		$arr["vizittype_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "VizitType_id",
			"label" => "Цель посещения",
			"save" => "trim|required",
			"type" => "id",
			"updateTable" => "EvnVizitPL"
		];
		$arr["vizitclass_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "VizitClass_id",
			"label" => "Первично/повторно",
			"save" => "trim",
			"type" => "id",
			"updateTable" => "EvnVizitPL"
		];
		$arr["profgoal_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "ProfGoal_id",
			"label" => "Цель профосмотра",
			"save" => "trim",
			"type" => "id",
			"updateTable" => "EvnVizitPL"
		];
		$arr["diag_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "Diag_id",
			"label" => "Основной диагноз",
			"save" => "trim",
			"type" => "id"
		];
		$arr["deseasetype_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "DeseaseType_id",
			"label" => "Характер заболевания",
			"save" => "trim",
			"type" => "id"
		];
		$arr["tumorstage_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "TumorStage_id",
			"label" => "Стадия выявленного ЗНО",
			"save" => "trim",
			"type" => "id",
			"updateTable" => "EvnVizitPL"
		];
		$arr["diag_agid"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "Diag_agid",
			"label" => "Осложнение основного диагноза",
			"save" => "trim",
			"type" => "id"
		];
		$arr["time"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "EvnVizitPL_Time",
			"label" => "Время приема",
			"save" => "trim",
			"type" => "int"
		];
		$arr["uslugacomplex_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "UslugaComplex_uid",
			"label" => "Код посещения",
			"save" => "trim",
			"type" => "id"
		];
		$arr["lpudispcontract_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "LpuDispContract_id",
			"label" => "По договору",
			"save" => "trim",
			"type" => "id"
		];
		$arr["rankinscale_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "RankinScale_id",
			"label" => "Значение по шкале Рэнкина",
			"save" => "trim",
			"type" => "id",
			"updateTable" => "EvnVizitPL"
		];
		$arr["medicalcarekind_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "MedicalCareKind_id",
			"label" => "Вид мед. помощи",
			"save" => "",
			"type" => "id",
			"updateTable" => "EvnVizitPL"
		];
		$arr["isprimaryvizit"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_NOT_LOAD,
				self::PROPERTY_NOT_SAFE,
			],
			"alias" => "EvnVizitPL_IsPrimaryVizit",
			"label" => "Признак первичного посещения в текущем году",
			"save" => "trim",
			"type" => "id"
		];
		$arr["iszno"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "EvnVizitPL_IsZNO",
			"label" => "Подозрение на ЗНО",
			"save" => "",
			"type" => "id",
			"updateTable" => "EvnVizitPL"
		];
		$arr["isznoremove"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "EvnVizitPL_IsZNORemove",
			"label" => "Снятие признака подозрения на ЗНО",
			"save" => "",
			"type" => "id",
			"updateTable" => "EvnVizitPL"
		];
		$arr["biopsydate"] = [
			"properties" => [
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "EvnVizitPL_BiopsyDate",
			"label" => "Дата взятия биопсии",
			"save" => "trim",
			"type" => "date",
			"updateTable" => "EvnVizitPL"
		];
		$arr["painintensity_id"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "PainIntensity_id",
			"label" => "Интенсивность боли",
			"save" => "",
			"type" => "id",
			"updateTable" => "EvnVizitPL"
		];
		$arr["diag_spid"] = [
			"properties" => [
				self::PROPERTY_IS_SP_PARAM,
			],
			"alias" => "Diag_spid",
			"label" => "Подозрение на диагноз",
			"save" => "",
			"type" => "id",
			"updateTable" => "EvnVizitPL"
		];
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
		return "EvnVizitPL";
	}

	/**
	 * @param $value
	 * @return mixed
	 */
	public function getParam($value)
	{
		return $this->_params[$value];
	}

	/**
	 * @param $key
	 * @param $value
	 */
	public function setSaveResponse($key, $value)
	{
		$this->_saveResponse[$key] = $value;
	}

	/**
	 * @param $key
	 * @return mixed
	 */
	public function getSavedData($key)
	{
		return $this->_savedData[$key] ?? null;
	}

	/**
	 * @param $key
	 * @param $value
	 */
	public function setRefactorAttribute($key, $value)
	{
		$this->setAttribute($key, $value);
	}

	/**
	 * @param $key
	 * @return bool
	 */
	public function isAttributeChanged($key)
	{
		return $this->_isAttributeChanged($key);
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
		$this->_params["ignoreMesUslugaCheck"] = empty($data["ignoreMesUslugaCheck"]) ? false : true;
		$this->_params["ignoreControl59536"] = empty($data["ignoreControl59536"]) ? false : true;
		$this->_params['ignoreNoExecPrescr'] = empty($data['ignoreNoExecPrescr']) ? false : true;
		$this->_params["ignoreControl122430"] = empty($data["ignoreControl122430"]) ? false : true;
		$this->_params["ignoreCheckMorbusOnko"] = empty($data["ignoreCheckMorbusOnko"]) ? false : true;
		$this->_params["ignoreEvnDirectionProfile"] = empty($data["ignoreEvnDirectionProfile"]) ? false : true;
		$this->_params["ignoreCheckEvnUslugaChange"] = !isset($data["ignoreCheckEvnUslugaChange"]) ? null : $data["ignoreCheckEvnUslugaChange"];
		$this->_params["allowCreateEmptyEvnDoc"] = empty($data["allowCreateEmptyEvnDoc"]) ? false : true;
		$this->_params["ignoreDiagDispCheck"] = empty($data["ignoreDiagDispCheck"]) ? false : true;
		$this->_params["copyEvnXml_id"] = empty($data["copyEvnXml_id"]) ? null : $data["copyEvnXml_id"];
		$this->_params["isMyOwnRecord"] = empty($data["isMyOwnRecord"]) ? null : $data["isMyOwnRecord"];
		$this->_params["IsFinish"] = empty($data["EvnPL_IsFinish"]) ? null : $data["EvnPL_IsFinish"];
		$this->_params["PregnancyEvnVizitPL_Period"] = empty($data["PregnancyEvnVizitPL_Period"]) ? null : $data["PregnancyEvnVizitPL_Period"];
		$this->_params["vizit_kvs_control_check"] = empty($data["vizit_kvs_control_check"]) ? false : true;
		$this->_params["ignore_vizit_kvs_control"] = empty($data["ignore_vizit_kvs_control"]) ? false : true;
		$this->_params["vizit_intersection_control_check"] = empty($data["vizit_intersection_control_check"]) ? false : true;
		$this->_params["ignore_vizit_intersection_control"] = empty($data["ignore_vizit_intersection_control"]) ? false : true;
		$this->_params["ignoreLpuSectionProfileVolume"] = empty($data["ignoreLpuSectionProfileVolume"]) ? false : true;
		$this->_params["ignoreDayProfileDuplicateVizit"] = empty($data["ignoreDayProfileDuplicateVizit"]) ? false : true;
		$this->_params["ignoreCheckB04069333"] = empty($data["ignoreCheckB04069333"]) ? false : true;
		$this->_params["addB04069333"] = empty($data["addB04069333"]) ? false : true;
		$this->_params["streamInput"] = empty($data["streamInput"]) ? false : true;
		$this->_params["copyEvnDiagPLStom"] = empty($data["copyEvnDiagPLStom"]) ? false : true;
		$this->_params["EvnVizitPLDoublesData"] = !empty($data["EvnVizitPLDoublesData"]) ? $data["EvnVizitPLDoublesData"] : null;
		$this->_params["DrugTherapyScheme_ids"] = !isset($data["DrugTherapyScheme_ids"]) ? null : $data["DrugTherapyScheme_ids"];
        $this->_params['UslugaMedType_id'] = isset($data['UslugaMedType_id']) ? $data['UslugaMedType_id'] : null;
        $this->_params['PayTypeKAZ_id'] = isset($data['PayTypeKAZ_id']) ? $data['PayTypeKAZ_id'] : null;
        $this->_params['ScreenType_id'] = isset($data['ScreenType_id']) ? $data['ScreenType_id'] : null;
		$this->_params['RepositoryObservData'] = $data['RepositoryObservData'] ?? null;
		$this->_params['VizitActiveType_id'] = isset($data['VizitActiveType_id']) ? $data['VizitActiveType_id'] : null;
	}
	#region get
	/**
	 * Определение кода цели сохраняемого/сохраненного посещения
	 * @return bool|float|int|string|null
	 * @throws Exception
	 */
	function getVizitTypeSysNick()
	{
		return EvnVizitPL_model_get::getVizitTypeSysNick($this);
	}

	/**
	 * Определение кода места сохраняемого/сохраненного посещения
	 * @return bool|float|int|string|null
	 * @throws Exception
	 */
	function getServiceTypeSysNick()
	{
		return EvnVizitPL_model_get::getServiceTypeSysNick($this);
	}

	/**
	 * @return array|bool
	 * @throws Exception
	 */
	function getLpuSectionData()
	{
		return EvnVizitPL_model_get::getLpuSectionData($this);
	}

	/**
	 * @return array
	 * @throws Exception
	 */
	protected function getEvnUslugaList()
	{
		return EvnVizitPL_model_get::getEvnUslugaList($this);
	}

	/**
	 * Получаем код
	 * @return int
	 */
	function getLpuUnitSetCode()
	{
		return EvnVizitPL_model_get::getLpuUnitSetCode($this);
	}

	/**
	 * Используется ли код посещения
	 * @return bool
	 */
	function getIsUseVizitCode()
	{
		return EvnVizitPL_model_get::getIsUseVizitCode($this);
	}

	/**
	 * Получаем код сохраняемого/сохраненного посещения
	 * @return bool|float|int|string
	 * @throws Exception
	 */
	function getVizitCode()
	{
		return EvnVizitPL_model_get::getVizitCode($this);
	}

	/**
	 * Получение последнего посещения в ТАПе
	 * @param $data
	 * @return array|false
	 */
	function getLastEvnVizitPL($data)
	{
		return EvnVizitPL_model_get::getLastEvnVizitPL($this, $data);
	}

	/**
	 * Получение дублей
	 * @return array
	 * @throws Exception
	 */
	function getEvnVizitPLDoubles()
	{
		return EvnVizitPL_model_get::getEvnVizitPLDoubles($this);
	}

	/**
	 * получить данные из $this->_savedData
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function getEvnVizitPLSavedData($data)
	{
		$this->applyData([
			"EvnVizitPL_id" => $data["EvnVizitPL_id"],
			"session" => $data["session"]
		]);
		$response = [];
		$attributes = $this->defAttributes();
		foreach ($this->_savedData as $field => $val) {
			if (!empty($attributes[$field]["alias"])) {
				$response[$attributes[$field]["alias"]] = $val;
			}
		}
		return $response;
	}

	/**
	 * Получение вида обращения и цели посещения для вывода метки в печати осмотра
	 * @param $data
	 * @return array|false
	 */
	function getDataForDispPrint($data)
	{
		return EvnVizitPL_model_get::getDataForDispPrint($this, $data);
	}

	/**
	 * Правила для контроллера для извлечения входящих параметров при сохранении
	 * @return array
	 */
	protected function _getSaveInputRules()
	{
		$all = parent::_getSaveInputRules();
		// параметры
		$all["from"] = ["field" => "from", "label" => "Откула", "rules" => "", "type" => "string"];
		$all["isMyOwnRecord"] = ["field" => "isMyOwnRecord", "label" => "Флаг собственной записи", "rules" => "", "type" => "string"];
		$all["streamInput"] = ["field" => "streamInput", "label" => "Признак поточного ввода", "rules" => "", "type" => "int"];
		$all["ignoreEvnUslugaCountCheck"] = ["field" => "ignoreEvnUslugaCountCheck", "label" => "Признак игнорирования количества услуг", "rules" => "", "type" => "int"];
		$all["ignoreDayProfileDuplicateVizit"] = ["field" => "ignoreDayProfileDuplicateVizit", "label" => "Признак игнорирования дублей посещений по профилю", "rules" => "", "type" => "int"];
		$all["ignoreMesUslugaCheck"] = ["field" => "ignoreMesUslugaCheck", "label" => "Признак игнорирования проверки МЭС", "rules" => "", "type" => "int"];
		$all["ignoreControl59536"] = ["field" => "ignoreControl59536", "label" => "Признак игнорирования проверки по задаче 59536", "rules" => "", "type" => "int"];
		$all['ignoreNoExecPrescr'] = ['field' => 'ignoreNoExecPrescr', 'label' => 'Признак игнорирования неисполненных/неотмененных назначений в случае АПЛ', 'rules' => '', 'type' => 'int'];
		$all["ignoreControl122430"] = ["field" => "ignoreControl122430", "label" => "Признак игнорирования проверки по задаче 122430", "rules" => "", "type" => "int"];
		$all["ignoreEvnDirectionProfile"] = ["field" => "ignoreEvnDirectionProfile", "label" => "Признак игнорирования проверки соответсвия профиля направления профилю посещения", "rules" => "", "type" => "int"];
		$all["ignoreCheckMorbusOnko"] = ["field" => "ignoreCheckMorbusOnko", "label" => "Признак игнорирования проверки перед удалением специфики", "rules" => "", "type" => "int"];
		$all["allowCreateEmptyEvnDoc"] = ["field" => "allowCreateEmptyEvnDoc", "label" => "Признак необходимости создания протокола осмотра с данными по умолчанию", "rules" => "", "type" => "int"];
		$all["ignoreDiagDispCheck"] = ["field" => "ignoreDiagDispCheck", "label" => "Признак игнорирования проверки наличи карты диспансеризации при диагнозе из определенной группы", "rules" => "", "type" => "int"];
		$all["copyEvnXml_id"] = ["field" => "copyEvnXml_id", "label" => "Признак необходимости копирования указанного протокола осмотра", "rules" => "", "type" => "int"];
		$all["aborttype_id"] = ["field" => "AbortType_id", "label" => "AbortType_id", "rules" => "trim", "type" => "id"];
		$all["morbuspregnancy_id"] = ["field" => "MorbusPregnancy_id", "label" => "MorbusPregnancy_id", "rules" => "trim", "type" => "id"];
		$all["morbus_id"] = ["field" => "Morbus_id", "label" => "morbus_id", "rules" => "trim", "type" => "id"];
		$all["diag_id"] = ["field" => "Diag_id", "label" => "Diag_id", "rules" => "trim", "type" => "id"];
		$all["birthresult_id"] = ["field" => "BirthResult_id", "label" => "BirthResult_id", "rules" => "trim", "type" => "id"];
		$all["morbuspregnancy_ishiv"] = ["field" => "MorbusPregnancy_IsHIV", "label" => "MorbusPregnancy_IsHIV", "rules" => "trim", "type" => "id"];
		$all["morbuspregnancy_ishivtest"] = ["field" => "MorbusPregnancy_IsHIVtest", "label" => "MorbusPregnancy_IsHIVtest", "rules" => "trim", "type" => "id"];
		$all["morbuspregnancy_ismedicalabort"] = ["field" => "MorbusPregnancy_IsMedicalAbort", "label" => "MorbusPregnancy_IsMedicalAbort", "rules" => "trim", "type" => "id"];
		$all["morbuspregnancypresent"] = ["field" => "MorbusPregnancyPresent", "label" => "MorbusPregnancyPresent", "rules" => "trim", "type" => "id"];
		$all["morbuspregnancy_outcomperiod"] = ["field" => "MorbusPregnancy_OutcomPeriod", "label" => "MorbusPregnancy_OutcomPeriod", "rules" => "trim", "type" => "int"];
		$all["morbuspregnancy_bloodloss"] = ["field" => "MorbusPregnancy_BloodLoss", "label" => "MorbusPregnancy_BloodLoss", "rules" => "trim", "type" => "int"];
		$all["morbuspregnancy_countpreg"] = ["field" => "MorbusPregnancy_CountPreg", "label" => "MorbusPregnancy_CountPreg", "rules" => "trim", "type" => "int"];
		$all["morbuspregnancy_outcomd"] = ["field" => "MorbusPregnancy_OutcomD", "label" => "MorbusPregnancy_OutcomD", "rules" => "trim", "type" => "date"];
		$all["morbuspregnancy_outcomt"] = ["field" => "MorbusPregnancy_OutcomT", "label" => "MorbusPregnancy_OutcomT", "rules" => "trim", "type" => "string"];
		$all["isFinish"] = ["field" => "EvnPL_IsFinish", "label" => "EvnPL_IsFinish", "rules" => "trim", "type" => "id"];
		$all["PregnancyEvnVizitPL_Period"] = ["field" => "PregnancyEvnVizitPL_Period", "label" => "Срок беременности", "rules" => "", "type" => "int", "updateTable" => "EvnVizitPL"];
		$all["vizit_kvs_control_check"] = ["field" => "vizit_kvs_control_check", "label" => "Признак ", "rules" => "", "type" => "int"];
		$all["vizit_intersection_control_check"] = ["field" => "vizit_intersection_control_check", "label" => "Признак ", "rules" => "", "type" => "int"];
		$all["ignoreLpuSectionProfileVolume"] = ["field" => "ignoreLpuSectionProfileVolume", "label" => "Признак ", "rules" => "", "type" => "int"];
		$all["ignoreCheckEvnUslugaChange"] = ["field" => "ignoreCheckEvnUslugaChange", "label" => "Признак игнорирования проверки изменения привязок услуг", "rules" => "", "type" => "int"];
		$all["ignoreCheckB04069333"] = ["field" => "ignoreCheckB04069333", "label" => "Признак игнорирования проверки наличия услуги B04.069.333", "rules" => "", "type" => "int"];
		$all["EvnVizitPLDoublesData"] = ["field" => "EvnVizitPLDoublesData", "label" => "EvnVizitPLDoublesData", "rules" => "", "type" => "json_array", "assoc" => true];
		$all["addB04069333"] = ["field" => "addB04069333", "label" => "Признак необходимости добавить услугу B04.069.333", "rules" => "", "type" => "int"];
		$all["DrugTherapyScheme_ids"] = ["field" => "DrugTherapyScheme_ids", "label" => "Схема лекарственной терапии", "rules" => "", "type" => "multipleid"];
		$all['UslugaMedType_id'] = ['field' => 'UslugaMedType_id', 'label' => 'Вид услуги', 'rules' => '', 'type' => 'int'];
        $all['PayTypeKAZ_id'] = ['field' => 'PayTypeKAZ_id', 'label' => 'Тип оплаты', 'rules' => '', 'type' => 'id'];
        $all['ScreenType_id'] = ['field' => 'ScreenType_id', 'label' => 'Вид скрининга', 'rules' => '', 'type' => 'id'];
		$all['RepositoryObservData'] = ['field' => 'RepositoryObservData', 'label' => 'Анкета', 'rules' => '', 'type' => 'json_array', 'assoc' => true];
		$all['VizitActiveType_id'] = ['field' => 'VizitActiveType_id', 'label' => 'Вид активного посещения', 'rules' => '', 'type' => 'id'];
		return $all;
	}

	/**
	 * Получение дублей до сохранения
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	function _getEvnVizitPLOldDoubles($data)
	{
		return EvnVizitPL_model_get::_getEvnVizitPLOldDoubles($this, $data);
	}

	/**
	 * Получение дублей посещения
	 * @return array|false
	 */
	public function _getEvnVizitPLDoubles()
	{
		return EvnVizitPL_model_get::_getEvnVizitPLDoubles($this);
	}

	/**
	 * @return int
	 */
	protected function _getNextNumGroup()
	{
		return EvnVizitPL_model_get::_getNextNumGroup($this);
	}

	public function getEvnVizitPLSetDate($data)
	{
		return EvnVizitPL_model_get::getEvnVizitPLSetDate($this, $data);
	}
	#endregion get
	#region check
	/**
	 * Проверка корректности заполнения обязательных полей в онкоспецифике
	 * @return bool
	 * @throws Exception
	 */
	protected function _checkOnkoSpecifics()
	{
		return EvnVizitPL_model_check::_checkOnkoSpecifics($this);
	}

	/**
	 * Проверка наличия связок для отображения полей
	 * @param $data
	 * @return array
	 */
	function checkMesOldUslugaComplexFields($data)
	{
		return EvnVizitPL_model_check::checkMesOldUslugaComplexFields($this, $data);
	}

	/**
	 * Проверки услуг посещений
	 * @throws Exception
	 */
	function _checkChangeEvnUsluga()
	{
		EvnVizitPL_model_check::_checkChangeEvnUsluga($this);
	}

	/**
	 * Проверка соответсвия профиля направления профилю посещения
	 * @throws Exception
	 */
	function _checkEvnDirectionProfile()
	{
		EvnVizitPL_model_check::_checkEvnDirectionProfile($this);
	}

	/**
	 * Проверки даты посещения
	 * @throws Exception
	 */
	function _checkChangeSetDate()
	{
		EvnVizitPL_model_check::_checkChangeSetDate($this);
	}

	/**
	 * Проверка цели посещения
	 * @throws Exception
	 */
	protected function _checkChangeVizitType()
	{
		EvnVizitPL_model_check::_checkChangeVizitType($this);
	}

	/**
	 * Проверка места посещения
	 * @throws Exception
	 */
	protected function _checkChangeServiceType()
	{
		EvnVizitPL_model_check::_checkChangeServiceType($this);
	}

	/**
	 * Проверка наличия карты диспансеризации при диагнозе из определенной группы (refs #169331)
	 * @throws Exception
	 */
	protected function _checkDiagDispCard()
	{
		EvnVizitPL_model_check::_checkDiagDispCard($this);
	}

	/**
	 * Проверка места работы
	 * @throws Exception
	 */
	protected function _checkChangeMedStaffFact()
	{
		EvnVizitPL_model_check::_checkChangeMedStaffFact($this);
	}

	/**
	 * Проверка профиля
	 * @return bool
	 * @throws Exception
	 */
	protected function _checkChangeLpuSectionProfileId()
	{
		return EvnVizitPL_model_check::_checkChangeLpuSectionProfileId($this);
	}

	/**
	 * Проверка отделения
	 * @throws Exception
	 */
	protected function _checkChangeLpuSection()
	{
		EvnVizitPL_model_check::_checkChangeLpuSection($this);
	}

	/**
	 * Проверки человека
	 * @throws Exception
	 */
	protected function _checkPerson()
	{
		EvnVizitPL_model_check::_checkPerson($this);
	}

	/**
	 * Проверка возможности изменения основного диагноза посещения
	 * @throws Exception
	 */
	protected function _checkChangeDiag()
	{
		EvnVizitPL_model_check::_checkChangeDiag($this);
	}

    /**
     * Проверка возможности изменения кода посещения
     * @return bool
     * @throws Exception
     */
    protected function _checkChangeVizitCode()
    {
        return EvnVizitPL_model_check::_checkChangeVizitCode($this);
    }

	#endregion check
	#region common
	/**
	 * @param string $key Ключ строчными символами
	 * @throws Exception
	 */
	protected function _afterUpdateAttribute($key)
	{
		parent::_afterUpdateAttribute($key);
		switch ($key) {
			case "vizitclass_id":
			case "vizittype_id":
				if ($this->regionNick == "perm") {
					$this->_updateEvnUslugaVizit();
				}
				break;
			case "servicetype_id":
				if (getRegionNick() == "perm" && $this->_params["addB04069333"] == true) {
					$query = "
						select UslugaComplex_id as \"UslugaComplex_id\"
						from v_UslugaComplex
						where UslugaComplex_Code = 'B04.069.333'
						  and (UslugaComplex_begDT is null or UslugaComplex_begDT <= :setDate)
						  and (UslugaComplex_endDT is null or UslugaComplex_endDT >= :setDate)
						limit 1
					";
					$queryParams = ["setDate" => $this->setDate];
					$UslugaComplex_id = $this->getFirstResultFromQuery($query, $queryParams, null);
					if (!empty($this->LpuSectionProfile_id)) {
						$LpuSectionProfile_id = $this->LpuSectionProfile_id;
					} else {
						$query = "
							select LpuSectionProfile_id as \"LpuSectionProfile_id\"
							from v_LpuSection
							where LpuSection_id = :LpuSection_id
							limit 1
						";
						$queryParams = ["LpuSection_id" => $this->LpuSection_id];
						$LpuSectionProfile_id = $this->getFirstResultFromQuery($query, $queryParams);
					}
					if (!empty($UslugaComplex_id)) {
						$this->load->model("EvnUsluga_model");
						$usluga_data = [
							"EvnUslugaCommon_id" => null,
							"EvnUslugaCommon_pid" => $this->id,
							"Lpu_id" => $this->Lpu_id,
							"Server_id" => $this->Server_id,
							"PersonEvn_id" => $this->PersonEvn_id,
							"Person_id" => $this->Person_id,
							"EvnUslugaCommon_setDate" => $this->setDate,
							"EvnUslugaCommon_setTime" => $this->setTime,
							"PayType_id" => $this->PayType_id,
							"Usluga_id" => null,
							"UslugaComplex_id" => $UslugaComplex_id,
							"HealthKind_id" => (!empty($this->HealthKind_id) ? $this->HealthKind_id : NULL),
							"MedPersonal_id" => $this->MedPersonal_id,
							"MedStaffFact_id" => $this->MedStaffFact_id,
							"LpuSectionProfile_id" => $LpuSectionProfile_id,
							"UslugaPlace_id" => 1,
							"Lpu_uid" => null,
							"LpuSection_uid" => $this->LpuSection_id,
							"Org_uid" => null,
							"EvnUslugaCommon_Kolvo" => 1,
							"EvnUslugaCommon_IsVizitCode" => 1,
							"pmUser_id" => $this->promedUserId,
							"session" => $this->sessionParams,
						];
						$this->EvnUsluga_model->isAllowTransaction = false;
						$tmp = $this->EvnUsluga_model->saveEvnUslugaCommon($usluga_data);
						if (!empty($tmp[0]["Error_Msg"])) {
							//нужно откатить транзакцию
							throw new Exception($tmp[0]["Error_Msg"]);
						}
					}
				}
				break;
			case "uslugacomplex_id":
				$this->_updateEvnUslugaVizit();
				break;
			case "diag_id":
				if ($this->isLastVizit()) {
					$this->parent->setScenario(self::SCENARIO_SET_ATTRIBUTE);
					$resp = $this->parent->_updateAttribute($this->pid, "diag_lid", $this->Diag_id, false);
					if (!empty($resp["Error_Msg"])) {
						throw new Exception($resp["Error_Msg"], $resp["Error_Code"]);
					}
				}
				$query = "
					select movpld.MorbusOnkoVizitPLDop_id as \"MorbusOnkoVizitPLDop_id\"
					from
						v_EvnVizitPL evpl
						inner join v_Diag Diag on Diag.Diag_id = evpl.Diag_id
						inner join v_MorbusOnkoVizitPLDop movpld on movpld.evnvizit_id = evpl.EvnVizitPL_id
					where evpl.EvnVizitPL_id = :id
					  and not ((Diag.Diag_Code >= 'C00' AND Diag.Diag_Code <= 'C97') or (Diag.Diag_Code >= 'D00' AND Diag.Diag_Code <= 'D09'))
					limit 1
				";
				$queryParams = ["id" => $this->id];
				$MorbusOnkoVizitPLDop_id = $this->getFirstResultFromQuery($query, $queryParams, true);
				if ($MorbusOnkoVizitPLDop_id === false) {
					throw new Exception("Ошибка при проверке талона дополнений больного ЗНО");
				}
				if (!empty($MorbusOnkoVizitPLDop_id)) {
					$this->load->model("MorbusOnkoVizitPLDop_model");
					$resp = $this->MorbusOnkoVizitPLDop_model->delete(["MorbusOnkoVizitPLDop_id" => $MorbusOnkoVizitPLDop_id]);
					if (!$this->isSuccessful($resp)) {
						throw new Exception($resp[0]["Error_Msg"]);
					}
					$this->_saveResponse["deletedMorbusOnkoVizitPLDop_id"] = $MorbusOnkoVizitPLDop_id;
				}
				$this->load->model("CureStandart_model");
				$cureStandartCountQuery = $this->CureStandart_model->getCountQuery("Diag", "PS.Person_BirthDay", "coalesce(EvnVizit.EvnVizitPL_setDT, tzgetdate())");
				$diagFedMesFileNameQuery = $this->CureStandart_model->getDiagFedMesFileNameQuery("Diag");

				$selectString = "
					FM.CureStandart_Count as \"CureStandart_Count\",
					DFM.DiagFedMes_FileName as \"DiagFedMes_FileName\"
				";
				$query = "
					select {$selectString}
					from v_EvnVizitPL EvnVizit
						inner join v_PersonState PS on EvnVizit.Person_id = PS.Person_id
						inner join v_Diag Diag on Diag.Diag_id = EvnVizit.Diag_id
						left join lateral (
							{$cureStandartCountQuery}
						) as FM on true
						left join lateral (
							{$diagFedMesFileNameQuery}
						) as DFM on true
					where EvnVizit.EvnVizitPL_id = :id
				";
				$queryParams = ["id" => $this->id];
				$tmp = $this->getFirstRowFromQuery($query, $queryParams);
				if (empty($tmp)) {
					$this->_saveResponse["CureStandart_Count"] = null;
					$this->_saveResponse["DiagFedMes_FileName"] = null;
				} else {
					$this->_saveResponse["CureStandart_Count"] = $tmp["CureStandart_Count"];
					$this->_saveResponse["DiagFedMes_FileName"] = $tmp["DiagFedMes_FileName"];
				}
				break;
			case 'diag_spid':
				if (in_array($this->regionNick, ['perm', 'msk'])) {
					$this->load->model('MorbusOnkoSpecifics_model');
					$this->MorbusOnkoSpecifics_model->checkAndCreateSpecifics($this);
				}
				break;
		}
		if (in_array($key, ["diag_id", "person_id", "setdate"])) {
			$this->_updateMorbus();
		}
	}

	/**
	 * Проверки и другая логика перед удалением объекта
	 * @param array $data Массив входящих параметров
	 * @throws Exception
	 */
	protected function _beforeDelete($data = [])
	{
		parent::_beforeDelete($data);
		// Очищаем фактическое время приема или удаляем бирки без записи
		$this->load->model("TimetableGraf_model");
		$this->TimetableGraf_model->onBeforeDeleteEvn($this);

		// отменить выполнение связанного назначения
		$params = ["pmUser_id" => $this->promedUserId];
		$params["EvnPrescr_id"] = $this->getFirstResultFromQuery("select EvnPrescr_id as \"EvnPrescr_id\" from v_EvnVizitPL where EvnVizitPL_id = :Evn_id limit 1", ["Evn_id" => $this->id]);
		if (!empty($params["EvnPrescr_id"])) {
			$tmp = $this->execCommonSP("p_EvnPrescr_unexec", $params);
			if (empty($tmp)) {
				throw new Exception("Ошибка запроса к БД", 500);
			}
			if (isset($tmp[0]["Error_Msg"])) {
				throw new Exception($tmp[0]["Error_Msg"], 500);
			}
		}
		if (in_array($this->evnClassId, [11, 13]) && $this->parent->hasEvnVizitInReg()) {
			$paidField = (in_array($this->regionNick, ["pskov", "ufa", "vologda"])) ? "Paid_id" : "RegistryData_IsPaid";
			$registryStatusExceptions = [4];
			if ($this->regionNick == "kareliya" || $this->regionNick == "penza") {
				$registryStatusExceptions[] = 3;
			}
			// проверяем наличие посещений в реестре по ->pid
			$registryStatusExceptionsString = implode(",", $registryStatusExceptions);
			$query = "
				select
					E.Evn_id as \"Evn_id\",
					R.RegistryStatus_id as \"RegistryStatus_id\"
				from
					v_Evn E
					left join v_RegistryData RD on RD.Evn_id = E.Evn_id
					left join v_Registry R on RD.Registry_id = R.Registry_id
				where RD.Evn_id is not null
				  and E.Evn_setDT >= '2014-12-01'
				  and (R.RegistryStatus_id not in ({$registryStatusExceptionsString}) or (R.RegistryStatus_id = 4 and RD.{$paidField} = 2))
				  and E.Evn_pid = :Evn_pid
				limit 1
			";
			$dbreg = $this->load->database("registry", true);
			/**@var CI_DB_result $result */
			$result = $dbreg->query($query, ["Evn_pid" => $this->pid]);
			if (!is_object($result)) {
				throw new Exception("Ошибка проверки оплаченности посещений.", 400);
			}
			$resp = $result->result("array");
			if (!empty($resp[0]["Evn_id"])) {
				throw new Exception("Удаление посещения невозможно т.к. есть посещения входящие в реестр.", 400);
			}
		}
		// Проверяем есть ли услуги параклиники, которые привязаны к текущему движению
		$this->EvnUslugaLinkChange = null;
		if (!in_array(getRegionNick(), ["perm", "kareliya", "kz"])) {
			$query = "
				select
					eup.EvnUslugaPar_id as \"EvnUslugaPar_id\",
					'unlink' as \"type\"
				from v_EvnUslugaPar eup
				where eup.EvnUslugaPar_pid = :EvnVizitPL_id
			";
			$queryParams = ["EvnVizitPL_id" => $this->id];
			$this->EvnUslugaLinkChange = $this->queryResult($query, $queryParams);
			if (!empty($this->EvnUslugaLinkChange) && empty($data["ignoreCheckEvnUslugaChange"])) {
				// выдаём YesNo
				$this->_saveResponse["Alert_Msg"] = "С этим посещением есть связные услуги. Удаление посещения приведет к разрыву связи. Продолжить?";
				throw new Exception("YesNo", 703);
			}
			$this->load->model("EvnUslugaPar_model");
			foreach ($this->EvnUslugaLinkChange as $usl) {
				switch ($usl["type"]) {
					case "unlink":
						// после удаления движения услуги привязываются к корню дерева, поэтому сделаем это перед удалением, иначе хранимка удалит услугу.
						$this->EvnUslugaPar_model->editEvnUslugaPar([
							"EvnUslugaPar_id" => $usl["EvnPrescr_pid"],
							"EvnUslugaPar_pid" => null,
							"pmUser_id" => $this->promedUserId,
							"session" => $this->sessionParams
						]);
						break;
				}
			}
		}
	}

	/**
	 * Проверка наличия предыдущих посещений детского отделения
	 * @return bool
	 */
	protected function hasPreviusChildVizit()
	{
		foreach ($this->parent->evnVizitList as $vizit) {
			$setDT = date_create($vizit["EvnVizitPL_setDate"] . " " . (empty($vizit["EvnVizitPL_setTime"]) ? "00:00" : $vizit["EvnVizitPL_setTime"]));
			if (!empty($vizit["LpuSectionAge_id"]) && $vizit["LpuSectionAge_id"] == 2 && $setDT <= ConvertDateFormat($this->setDT, 'Y-m-d H:i:s')) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Проверка того, что посещение последнее в ТАП
	 * @return bool
	 */
	protected function isLastVizit()
	{
		$list = $this->parent->evnVizitList;
		if (end($list) && key($list) == $this->id) {
			return true;
		}
		return false;
	}

	/**
	 * @param string $key
	 * @throws Exception
	 */
	protected function _beforeUpdateAttribute($key)
	{
		parent::_beforeUpdateAttribute($key);
		$isStom = (13 == $this->evnClassId);
		switch ($key) {
			case "lpusectionprofile_id":
				$this->_checkChangeLpuSectionProfileId();
				break;
			case "diag_id":
				if (getRegionNick() == "ekb") {
					$this->setMedicalCareKindId();
				}
				$this->_checkChangeDiag();
				break;
			case "paytype_id":
				$this->_setEvnVizitPLDoubles();
				if (getRegionNick() == "ekb") {
					$this->setMedicalCareKindId();
				}

				$this->_checkOnkoSpecifics();

				if (getRegionNick() == "kareliya") {
					// сбросить VizitType_id, если не соответсвует виду оплаты
					if ($this->payTypeSysNick == "oms") {
						$deniedVizitTypeCodes = ["41", "51", "2.4", "3.1"];
						if (strtotime($this->setDate) < strtotime("01.05.2019")) {
							$deniedVizitTypeCodes[] = "1.2";
						}
						$VizitType_Code = $this->getFirstResultFromQuery("select VizitType_Code as \"VizitType_Code\" from v_VizitType where VizitType_id = :VizitType_id", ["VizitType_id" => $this->VizitType_id]);
						if (!empty($VizitType_Code) && in_array($VizitType_Code, $deniedVizitTypeCodes)) {
							$this->setAttribute("vizittype_id", null);
							$this->_saveResponse["clearVizitTypeId"] = true;
						}
					}
				}
				break;
			case "uslugacomplex_id":
				$this->_checkChangeVizitCode();
				$this->_checkChangeEvnUsluga();
				if ($this->regionNick == 'kz' && empty($this->PayType_id)) {
					$this->setAttribute('paytype_id', 152);
				}
				break;
			case "treatmentclass_id":
				if (!$isStom && $this->regionNick == "perm") {
					$this->setAttribute("uslugacomplex_id", null);
				}
				if ($this->regionNick == 'kz' && empty($this->PayType_id)) {
					$this->setAttribute('paytype_id', 152);
				}
				// на Карелии поле скрыто, проверка не нужна
				if (!empty($this->VizitType_id) && !in_array($this->regionNick, ['kareliya', 'kz'])) {
					// сбросить VizitType_id, если не соответсвует TreatmentClassVizitType
					$query = "
						select TreatmentClassVizitType_id as \"TreatmentClassVizitType_id\"
						from v_TreatmentClassVizitType
						where TreatmentClass_id = :TreatmentClass_id
						  and VizitType_id = :VizitType_id
						limit 1
					";
					$queryParams = [
						"TreatmentClass_id" => $this->TreatmentClass_id,
						"VizitType_id" => $this->VizitType_id
					];
					$resp_tcvt = $this->queryResult($query, $queryParams);
					if (empty($resp_tcvt[0]["TreatmentClassVizitType_id"])) {
						$this->setAttribute("vizittype_id", null);
					}
				}
				break;
			case "vizitclass_id":
				if (!$isStom && $this->regionNick == "perm") {
					$this->setAttribute("uslugacomplex_id", null);
				}
				break;
			case "vizittype_id":
				if (!$isStom && $this->regionNick == "perm") {
					$this->setAttribute("uslugacomplex_id", null);
				}
				$this->_checkChangeVizitType();
				break;
			case "servicetype_id":
				$this->_checkChangeServiceType();
				break;
			case "medstafffact_id":
				$this->load->model("TimetableGraf_model");
				$ttgdata = $this->TimetableGraf_model->onBeforeSaveEvnVizit($this);
				$this->setAttribute("TimetableGraf_id", $ttgdata["TimetableGraf_id"]);
				$this->setAttribute("EvnDirection_id", $ttgdata["EvnDirection_id"]);
				break;
		}
	}

	/**
	 * Завершить обслуживание вызова на дом после успешного сохранения объекта
	 * @throws Exception
	 */
	protected function _completeHomeVisit()
	{
		if (!empty($this->HomeVisit_id) && $this->isNewRecord && $this->evnClassId == 11) {
			$home_visit_data = [
				"HomeVisit_id" => $this->HomeVisit_id,
				"MedStaffFact_id" => $this->MedStaffFact_id,
				"MedPersonal_id" => $this->MedPersonal_id,
				"HomeVisit_LpuComment" => NULL,
				"pmUser_id" => $this->promedUserId
			];
			$this->load->model("HomeVisit_model");
			$tmp = $this->HomeVisit_model->completeHomeVisit($home_visit_data);
			if (!empty($tmp["Error_Msg"])) {
				throw new Exception($tmp["Error_Msg"]);
			}
		}
	}

	/**
	 * @param $id
	 * @param null $value
	 * @return array
	 * @throws Exception
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
	 * Сохранение схем лекарственной терапии
	 * @throws Exception
	 */
	protected function _saveDrugTherapyScheme()
	{
		$DrugTherapyScheme_ids = $this->_params["DrugTherapyScheme_ids"];
		$query = "
			select
				EvnVizitPLDrugTherapyLink_id as \"EvnVizitPLDrugTherapyLink_id\",
				DrugTherapyScheme_id as \"DrugTherapyScheme_id\"
			from v_EvnVizitPLDrugTherapyLink
			where EvnVizitPL_id = :EvnVizitPL_id
		";
		$queryParams = ["EvnVizitPL_id" => $this->id];
		$resp = $this->queryResult($query, $queryParams);
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
			if (isset($dtsArray[$respone["DrugTherapyScheme_id"]]) && $dtsArray[$respone["DrugTherapyScheme_id"]] > 0) {
				$dtsArray[$respone["DrugTherapyScheme_id"]]--;
			} else {
				$query = "
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from p_EvnVizitPLDrugTherapyLink_del(EvnVizitPLDrugTherapyLink_id := :EvnVizitPLDrugTherapyLink_id)
				";
				$queryParams = ["EvnVizitPLDrugTherapyLink_id" => $respone["EvnVizitPLDrugTherapyLink_id"]];
				$resp_del = $this->queryResult($query, $queryParams);
				if (!empty($resp_del[0]["Error_Msg"])) {
					throw new Exception($resp_del[0]["Error_Msg"]);
				}
			}
		}
		// добавляем новые
		foreach ($dtsArray as $DrugTherapyScheme_id => $count) {
			for ($i = 0; $i < $count; $i++) {
				$query = "
					select
						EvnVizitPLDrugTherapyLink_id as \"EvnVizitPLDrugTherapyLink_id\",
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from p_EvnVizitPLDrugTherapyLink_ins(
						EvnVizitPL_id := :EvnVizitPL_id,
						DrugTherapyScheme_id := :DrugTherapyScheme_id,
						pmUser_id := :pmUser_id
					)
				";
				$queryParams = [
					"EvnVizitPL_id" => $this->id,
					"DrugTherapyScheme_id" => $DrugTherapyScheme_id,
					"pmUser_id" => $this->promedUserId
				];
				$resp_save = $this->queryResult($query, $queryParams);
				if (!empty($resp_save[0]["Error_Msg"])) {
					throw new Exception($resp_save[0]["Error_Msg"]);
				}
			}
		}
	}

	/**
	 * Группировка посещений
	 * @param $EvnVizitPL_pid
	 * @throws Exception
	 */
	public function updateEvnVizitNumGroup($EvnVizitPL_pid)
	{
		if ($this->regionNick == "vologda") {
			$query = "
				select
					evpl.EvnVizitPL_id as \"EvnVizitPL_id\",
					evpl.TreatmentClass_id as \"TreatmentClass_id\",
					VT.VizitType_SysNick as \"VizitType_SysNick\",
					evpl.PayType_id as \"PayType_id\",
					LSP.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
					r.RegistryStatus_id as \"RegistryStatus_id\",
					coalesce(evpl.EvnVizitPL_IsPaid, 1) as \"EvnVizitPL_IsPaid\",
					evpl.EvnVizitPL_NumGroup as \"EvnVizitPL_NumGroup\"
				from
					v_EvnVizitPL evpl
					left join v_VizitType VT on VT.VizitType_id = evpl.VizitType_id
					left join v_LpuSectionProfile LSP on LSP.LpuSectionProfile_id = evpl.LpuSectionProfile_id
					left join r35.v_Registry r on r.Registry_id = evpl.Registry_sid
				where evpl.EvnVizitPL_pid = :EvnVizitPL_pid
			";
			if ($this->evnClassSysNick == "EvnVizitPLStom") {
				$query .= "
					and exists (
						select EvnUslugaStom_id
						from v_EvnUslugaStom
						where EvnUslugaStom_pid = evpl.EvnVizitPL_id and coalesce(EvnUslugaStom_IsVizitCode, 1) = 1
					)
					and not exists (
						select edpls.EvnDiagPLStom_id
						from
							v_EvnDiagPLStom edpls
							inner join v_EvnUslugaStom eus on eus.EvnDiagPLStom_id = edpls.EvnDiagPLStom_id
						where eus.EvnUslugaStom_pid = evpl.EvnVizitPL_id
						  and coalesce(edpls.EvnDiagPLStom_IsClosed, 1) = 1  
					)
				";
			}
			$queryParams = ["EvnVizitPL_pid" => $EvnVizitPL_pid];
			$list = $this->queryResult($query, $queryParams, true);
			if (false === $list) {
				throw new Exception("Ошибка при чтении списка посещений", 500);
			}
			$list = (!is_array($list)) ? [] : $list;
			$this->_groupNumExceptions = [];
			foreach ($list as $evnVizit) {
				if (!empty($evnVizit["EvnVizitPL_NumGroup"]) && ($evnVizit["EvnVizitPL_IsPaid"] == 2 || $evnVizit["RegistryStatus_id"] == 2)) {
					$this->_groupNumExceptions[] = $evnVizit["EvnVizitPL_NumGroup"];
				}
			}
			// Чистим номера групп
			$groupNumExceptionsString = implode(",", $this->_groupNumExceptions);
			$filterString = (count($this->_groupNumExceptions) != 0) ? "and EvnVizitPL_NumGroup not in ({$groupNumExceptionsString})" : "";
			$query = "
				update EvnVizitPL
				set EvnVizitPL_NumGroup = null
				where Evn_id in (select EvnVizitPL_id from v_EvnVizitPL where EvnVizitPL_pid = :EvnVizitPL_pid)
					{$filterString}
			";
			$queryParams = ["EvnVizitPL_pid" => $EvnVizitPL_pid];
			$updateResp = $this->db->query($query, $queryParams);
			if (!$updateResp) {
				throw new Exception("Ошибка запроса к БД", 500);
			}
			$query = "
				select PasportMO_IsAssignNasel as \"PasportMO_IsAssignNasel\"
				from fed.v_PasportMO
				where Lpu_id = :Lpu_id
				limit 1
			";
			$queryParams = ["Lpu_id" => $this->Lpu_id];
			$PasportMO_IsAssignNasel = $this->getFirstResultFromQuery($query, $queryParams);
			$groupNumByPayType = [];
			$this->_groupNum = 0;
			foreach ($list as $evnVizit) {
				if (!empty($evnVizit["EvnVizitPL_NumGroup"]) && in_array($evnVizit["EvnVizitPL_NumGroup"], $this->_groupNumExceptions)) {
					continue;
				}
				if (!isset($groupNumByPayType[$evnVizit["PayType_id"]])) {
					$groupNumByPayType[$evnVizit["PayType_id"]] = [];
					$groupNumByPayType[$evnVizit["PayType_id"]][2] = $this->_getNextNumGroup();
					$groupNumByPayType[$evnVizit["PayType_id"]][3] = $this->_getNextNumGroup();
					$groupNumByPayType[$evnVizit["PayType_id"]][4] = $this->_getNextNumGroup();
					$groupNumByPayType[$evnVizit["PayType_id"]][5] = $this->_getNextNumGroup();
				}
				if ($PasportMO_IsAssignNasel != 2 || $this->evnClassSysNick == "EvnVizitPLStom") {
					$EvnVizitPLGroupNum = ($evnVizit["TreatmentClass_id"] == 2) ? $this->_getNextNumGroup() : $groupNumByPayType[$evnVizit["PayType_id"]][5];
				} else {
					if ($evnVizit["TreatmentClass_id"] == 2) {
						$EvnVizitPLGroupNum = $this->_getNextNumGroup();
					} else if (in_array($evnVizit["LpuSectionProfile_Code"], ["3", "136", "137", "184"])) {
						$EvnVizitPLGroupNum = $groupNumByPayType[$evnVizit["PayType_id"]][2];
					} else if ($evnVizit["VizitType_SysNick"] == "centrrec") {
						$EvnVizitPLGroupNum = $groupNumByPayType[$evnVizit["PayType_id"]][3];
					} else if ($evnVizit["TreatmentClass_id"] == 8) {
						$EvnVizitPLGroupNum = $groupNumByPayType[$evnVizit["PayType_id"]][4];
					} else {
						$EvnVizitPLGroupNum = $groupNumByPayType[$evnVizit["PayType_id"]][5];
					}
				}
				$query = "
					update EvnVizitPL
					set EvnVizitPL_NumGroup = :EvnVizitPL_NumGroup
					where Evn_id = :EvnVizitPL_id
				";
				$queryParams = [
					"EvnVizitPL_id" => $evnVizit["EvnVizitPL_id"],
					"EvnVizitPL_NumGroup" => $EvnVizitPLGroupNum,
				];
				$updateResp = $this->db->query($query, $queryParams);
				if (!$updateResp) {
					throw new Exception("Ошибка запроса к БД", 500);
				}
			}
		}
	}

	/**
	 * @param $id
	 * @param null $value
	 * @return array
	 * @throws Exception
	 */
	function updatePersonDispId($id, $value = null)
	{
		return $this->_updateAttribute($id, "persondisp_id", $value);
	}

	/**
	 * Логика после успешного сохранения объекта
	 * @throws Exception
	 */
	protected function _updateEvnUslugaVizit()
	{
		// сохраняем услугу посещения
		if ($this->isUseVizitCode) {
			if ($this->regionNick == "pskov" && !empty($this->pid)) {
				// обновляем код и услугу посещения во всех посещениях данного ТАП
				$query = "
					update EvnVizitPL
					set UslugaComplex_id = :UslugaComplex_id
					from
						EvnVizitPL e
						inner join v_EvnVizitPL evpl on evpl.EvnVizitPL_id = e.Evn_id
					where evpl.EvnVizitPL_pid = :EvnVizitPL_pid
				";
				$queryParams = [
					"EvnVizitPL_pid" => $this->pid,
					"UslugaComplex_id" => $this->UslugaComplex_id
				];
				$this->db->query($query, $queryParams);
				$query = "
					update EvnUsluga
					set UslugaComplex_id = :UslugaComplex_id
					from
						EvnUsluga e
						inner join v_EvnUsluga eu on eu.EvnUsluga_id = e.Evn_id
						inner join v_EvnVizitPL evpl on evpl.EvnVizitPL_id = eu.EvnUsluga_pid
					where evpl.EvnVizitPL_pid = :EvnVizitPL_pid
					  and e.EvnUsluga_isVizitCode = 2
				";
				$queryParams = [
					"EvnVizitPL_pid" => $this->pid,
					"UslugaComplex_id" => $this->UslugaComplex_id
				];
				$this->db->query($query, $queryParams);
			}
		}
	}

	/**
	 * Добавление нового посещения из ЭМК
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function addEvnVizitPL($data)
	{
		$this->load->model("EPH_model");
		$resp = $this->EPH_model->loadEvnPLForm($data);
		if (empty($resp[0]["accessType"])) {
			throw new Exception("Ошибка получения информации о случае АПЛ");
		} else if ($resp[0]["accessType"] == "view") {
			if (!empty($resp[0]["AlertReg_Msg"])) {
				throw new Exception($resp[0]["AlertReg_Msg"]);
			} else if (empty($resp[0]['canCreateVizit'])) {
				throw new Exception("Случай АПЛ недоступен для редактирования");
			}
		}

        if($this->regionNick == 'penza' && $this->evnClassId != 13 && !empty($data['LpuSectionProfile_id'])) {
            $this->LpuSectionProfile_id = $data['LpuSectionProfile_id'];
            //$this->id = null;
            $this->pid = $data['EvnPL_id'];
            $this->_checkChangeLpuSectionProfileId();
        }

		// получаем данные предыдущего посещения
		$query = "
			select
				EVPL.EvnVizitPL_id as \"EvnVizitPL_id\",
				EX.EvnXml_id as \"EvnXml_id\"
			from 
				v_EvnVizitPL EVPL
				left join lateral (
					select EX.EvnXml_id
					from v_EvnXml EX
					where EX.Evn_id = EVPL.EvnVizitPL_id
					  and EX.XmlType_id = 3
					order by EX.EvnXml_insDT desc
					limit 1
				) as EX on true
			where EvnVizitPL_pid = :EvnPL_id 
			order by EvnVizitPL_setDT desc
			limit 1
		";
		$prev = $this->getFirstRowFromQuery($query, $data);
		if (!is_array($prev)) {
			throw new Exception("Не удалось определить предыдущее движение");
		}

		if (getRegionNick() == 'kareliya') {
			$query = "
				select
					to_char(EVPL.EvnVizitPL_setDate, 'yyyy-mm-dd') as \"EvnVizitPL_setDate\",
					vt.VizitType_SysNick as \"VizitType_SysNick\"
				from
					v_EvnVizitPL EVPL
					left join v_EvnPL EPL on EPL.EvnPL_id = EVPL.EvnVizitPL_pid
					left join v_VizitType vt on vt.VizitType_id = EVPL.VizitType_id 
				where
					EVPL.EvnVizitPL_pid = :EvnPL_id 
					and EPL.EvnPL_IsFinish != 2 
			";

			$result = $this->queryResult($query, array(
				'EvnPL_id' => $data['EvnPL_id']
			));

			if (empty($result))
				throw new Exception('Не удалось найти случай по данному идентификатору');

			// Проверим, что во всех найденных посещениях указана допустимая цель обращения (VizitType_SysNick):
			// - в период с 01.01.2017 до 31.12.2018 допускается 'desease' и 'dispnabl',
			// - начиная с 01.01.2019 допускается только 'desease'.
			// Если найдено посещение с какой-либо другой целью, новое посещение создать нельзя, генерируем исключение
			// с соответствующим текстом.
			foreach ($result as $vizit)
			{
				if (!($sysNick = $vizit['VizitType_SysNick']))
					throw new Exception('Не указана цель одного из предыдущих посещений.');

				if (!(($setDate = $vizit['EvnVizitPL_setDate']) &&
						($setDate = DateTime::createFromFormat("Y-m-d", $setDate)->format('Y-m-d'))))
					throw new Exception('Не указана дата одного из предыдущих посещений.');
				
				if ($setDate >= '2019-01-01')
				{
					if ($sysNick != 'desease')
						throw new Exception('Случай АПЛ с посещением, отличным от "Обращение по заболеванию", должен быть закрыт!');
				}
				{
					if ($setDate >= '2017-01-01' && !in_array($sysNick, ['desease', 'dispnabl']))
						throw new Exception('Случай АПЛ с посещением, отличным от "Обращение по поводу заболевания" или "Диспансерное наблюдение", должен быть закрыт!');
				}
			}
		}

		//Проверка на второе посещение НМП
		$this->_controlDoubleNMP($data["EvnPL_id"], null, false);
		$this->applyData([
			"EvnVizitPL_id" => $prev["EvnVizitPL_id"],
			"session" => $data["session"],
		]);
		$dt = date_create();
		// убираем лишние параметры
		$this->setAttribute("id", null);
		$this->setAttribute("setdt", $dt);
		$this->setAttribute("setdate", $dt->format("Y-m-d"));
		$this->setAttribute("setTime", $dt->format("H:i"));
		$this->setAttribute("uslugacomplex_id", null);
		$this->setAttribute("medstafffact_id", $data["MedStaffFact_id"]);
		if (!empty($data["LpuSection_id"])) {
			$this->setAttribute("lpusection_id", $data["LpuSection_id"]);
		}
		if (!empty($data["MedPersonal_id"])) {
			$this->setAttribute("medpersonal_id", $data["MedPersonal_id"]);
		}
		$this->setAttribute("timetablegraf_id", (!empty($data["TimetableGraf_id"])) ? $data["TimetableGraf_id"] : null);
		$this->setAttribute("evndirection_id", (!empty($data["EvnDirection_id"])) ? $data["EvnDirection_id"] : null);
		if ($this->regionNick == "penza") {
			$this->setAttribute("VizitType_id", null);
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
	 * @throws Exception
	 */
	protected function saveUslugaMedTypeLink()
	{
		if (getRegionNick() === "kz") {
			$result = $this->updateUslugaMedTypeId($this->id, $this->_params["UslugaMedType_id"]);
			if (!$this->isSuccessful($result)) {
				throw new Exception($result[0]["Error_Msg"], $result[0]["Error_Code"]);
			}
		}
	}

	/**
	 * Редактирование посещения из АПИ
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	function editEvnVizitPLFromAPI($data)
	{
		// получаем данные посещения
		$funcParams = [
			"EvnVizitPL_id" => !empty($data["EvnVizitPL_id"]) ? $data["EvnVizitPL_id"] : null,
			"session" => $data["session"]
		];
		$this->applyData($funcParams);
		// подменяем параметры, пришедшие от клиента
		$this->setAttribute("setdt", $data["Evn_setDT"]);
		$this->setAttribute("setdate", date("Y-m-d", strtotime($data["Evn_setDT"])));
		$this->setAttribute("setTime", date("H:i", strtotime($data["Evn_setDT"])));
		$this->setAttribute("lpu_id", $data["Lpu_id"]);
		if (!empty($data["VizitClass_id"])) {
			$this->setAttribute("vizitclass_id", $data["VizitClass_id"]);
		}
		if (!empty($data["LpuSection_id"])) {
			$this->setAttribute("lpusection_id", $data["LpuSection_id"]);
		}
		if (!empty($data["EvnPLBase_id"])) {
			// данные по пациенту берем из ТАП
			$this->setAttribute("pid", $data["EvnPLBase_id"]);
			$query = "
				select
					EvnPL_id as \"EvnPL_id\",
					Person_id as \"Person_id\",
					PersonEvn_id as \"PersonEvn_id\",
					Server_id as \"Server_id\",
					LpuSection_id as \"LpuSection_id\"
				from v_EvnPL
				where EvnPL_id = :EvnPL_id
			";
			$queryParams = ["EvnPL_id" => $data["EvnPLBase_id"]];
			$resp = $this->queryResult($query, $queryParams);
			if (!empty($resp[0]["EvnPL_id"])) {
				$this->setAttribute("person_id", $resp[0]["Person_id"]);
				$this->setAttribute("personevn_id", $resp[0]["PersonEvn_id"]);
				$this->setAttribute("server_id", $resp[0]["Server_id"]);
			}
		}
		if (!empty($data["MedStaffFact_id"])) {
			//LpuSection - проверка на то, чтобы средний персонал работал в переданном отделении, по ТЗ
			$query = "
				select MedStaffFact_id as \"MedStaffFact_id\"
				from v_MedStaffFact
				where MedStaffFact_id = :MedStaffFact_id
				  and LpuSection_id = :LpuSection_id
				limit 1
			";
			$queryParams = [
				"MedStaffFact_id" => $data["MedStaffFact_id"],
				"LpuSection_id" => $this->getAttribute("lpusection_id")
			];
			$MedStaffFact_id = $this->getFirstResultFromQuery($query, $queryParams);
			if ($MedStaffFact_id === false) {
				throw new Exception("Место работы врача должно быть в указанном отделении");
			}
			$this->setAttribute("medstafffact_id", $MedStaffFact_id);
			$query = "
				select MedPersonal_id as \"MedPersonal_id\"
				from v_MedStaffFact
				where MedStaffFact_id = :MedStaffFact_id
			";
			$queryParams = ["MedStaffFact_id" => $data["MedStaffFact_id"]];
			$MedPersonal_id = $this->getFirstResultFromQuery($query, $queryParams);
			if (!empty($MedPersonal_id)) {
				$this->setAttribute("medpersonal_id", $MedPersonal_id);
			}
		}
		if (!empty($data["LpuSection_id"])) {
			$this->setAttribute("lpusection_id", $data["LpuSection_id"]);
		}
		if (!empty($resp[0]["LpuSection_id"])) {
			// определим профиль
			$query = "
				select LpuSectionProfile_id as \"LpuSectionProfile_id\"
				from v_LpuSection
				where LpuSection_id = :LpuSection_id
				limit 1
			";
			$queryParams = ["LpuSection_id" => $resp[0]["LpuSection_id"]];
			$LpuSectionProfile_id = $this->getFirstResultFromQuery($query, $queryParams);
			if (!empty($LpuSectionProfile_id)) {
				$this->setAttribute("lpusectionprofile_id", $LpuSectionProfile_id);
			}
		}
		if (!empty($data["TreatmentClass_id"])) {
			$this->setAttribute("treatmentclass_id", $data["TreatmentClass_id"]);
		}
		if (!empty($data["ServiceType_id"])) {
			$this->setAttribute("servicetype_id", $data["ServiceType_id"]);
		}
		if (!empty($data["VizitType_id"])) {
			$this->setAttribute("vizittype_id", $data["VizitType_id"]);
		}
		if (!empty($data["PayType_id"])) {
			$this->setAttribute("paytype_id", $data["PayType_id"]);
		}
		if (!empty($data["Mes_id"])) {
			$this->setAttribute("mes_id", $data["Mes_id"]);
		}
		if (!empty($data["UslugaComplex_uid"])) {
			$this->setAttribute("uslugacomplex_id", $data["UslugaComplex_uid"]);
		}
		if (!empty($data["EvnVizitPL_Time"])) {
			$this->setAttribute("time", $data["EvnVizitPL_Time"]);
		}
		if (!empty($data["ProfGoal_id"])) {
			$this->setAttribute("profgoal_id", $data["ProfGoal_id"]);
		}
		if (!empty($data["DispClass_id"])) {
			$this->setAttribute("dispclass_id", $data["DispClass_id"]);
		}
		if (!empty($data["EvnPLDisp_id"])) {
			$this->setAttribute("evnpldisp_id", $data["EvnPLDisp_id"]);
		}
		if (!empty($data["PersonDisp_id"])) {
			$this->setAttribute("persondisp_id", $data["PersonDisp_id"]);
		}
		if (!empty($data["Diag_id"])) {
			$this->setAttribute("diag_id", $data["Diag_id"]);
		}
		if (!empty($data["DeseaseType_id"])) {
			$this->setAttribute("deseasetype_id", $data["DeseaseType_id"]);
		}
		if (!empty($data["Diag_agid"])) {
			$this->setAttribute("diag_agid", $data["Diag_agid"]);
		}
		if (!empty($data["RankinScale_id"])) {
			$this->setAttribute("rankinscale_id", $data["RankinScale_id"]);
		}
		if (!empty($data["HomeVisit_id"])) {
			$this->setAttribute("homevisit_id", $data["HomeVisit_id"]);
		}
		if (!empty($data["MedicalCareKind_id"])) {
			$this->setAttribute("medicalcarekind_id", $data["MedicalCareKind_id"]);
		}
		if (!empty($data["MedStaffFact_sid"])) {
			$query = "
				select MedPersonal_id as \"MedPersonal_id\"
				from v_MedStaffFact
				where MedStaffFact_id = :MedStaffFact_id
			";
			$queryParams = ["MedStaffFact_id" => $data["MedStaffFact_sid"]];
			$MedPersonal_sid = $this->getFirstResultFromQuery($query, $queryParams);
			if (!empty($MedPersonal_id)) {
				$this->setAttribute("medpersonal_sid", $MedPersonal_sid);
			}
		}
		if ($this->regionNick == "ekb" && !empty($data["Mes_id"])) {
			//МЭС для второго и следующих посещений в рамках одного ТАП должен совпадать с МЭС первого посещения
			$whereMes = "";
			if (!empty($data["EvnVizitPL_id"])) {
				//если редактируем запись
				$whereMes = "where EVPL.EvnVizitPL_pid in (select EvnVizitPL_pid from v_EvnVizitPL where EvnVizitPL_id = :EvnVizitPL_id limit 1)";
			} elseif (!empty($data["EvnPLBase_id"])) {
				//если создаем новое посещение
				$whereMes = "where EVPL.EvnVizitPL_pid = :EvnPLBase_id";
			}
			if ($whereMes) {
				$query = "
					select
						EVPL.EvnVizitPL_id as \"EvnVizitPL_id\",
						EVPL.Mes_id as \"Mes_id\",
						EVPL.EvnVizitPL_setDate as \"EvnVizitPL_setDate\",
						EVPL.EvnVizitPL_setTime as \"EvnVizitPL_setTime\"
					from v_EvnVizitPL EVPL
					{$whereMes}
					order by EVPL.EvnVizitPL_setDate, EVPL.EvnVizitPL_setTime
				";
				$resultMes = $this->db->query($query, $data);
				if (!is_object($resultMes)) {
					throw new Exception("Ошибка запроса в БД.");
				}
				$arrMes = $resultMes->result("array");
				if (count($arrMes) > 0 && !empty($arrMes[0]["Mes_id"])) {
					if ((!empty($data["EvnPLBase_id"]) && $arrMes[0]["Mes_id"] != $data["Mes_id"]) || (!empty($data["EvnVizitPL_id"]) && $data["EvnVizitPL_id"] != $arrMes[0]["EvnVizitPL_id"] && $arrMes[0]["Mes_id"] != $data["Mes_id"])) {
						throw new Exception("МЭС посещения не сопадает с МЭС первого посещения в рамках одного ТАП");
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
	 * Определение MedicalCareKindId
	 */
	function setMedicalCareKindId()
	{
		// для Екб.
		if (getRegionNick() == "ekb") {
			$LSMedicalCareKind_id = null;
			$Diag_Code = null;
			$FedMedSpec_Code = null;
			$FedMedSpecParent_Code = null;

			if (!empty($this->Diag_id)) {
				$query = "
					select Diag_Code as \"Diag_Code\"
					from v_Diag
					where Diag_id = :Diag_id
				";
				$resp_diag = $this->queryResult($query, ["Diag_id" => $this->Diag_id]);
				if (!empty($resp_diag[0]["Diag_Code"])) {
					$Diag_Code = $resp_diag[0]["Diag_Code"];
				}
			}
			if (!empty($this->MedStaffFact_id)) {
				$query = "
					select
						MSF.MedStaffFact_id as \"MedStaffFact_id\",
						FMS.MedSpec_Code as \"FedMedSpec_Code\",
						FMSP.MedSpec_Code as \"FedMedSpecParent_Code\"
					from
						v_MedStaffFact MSF
						left join v_MedSpecOms MSO on MSO.MedSpecOms_id = MSF.MedSpecOms_id
						left join fed.v_MedSpec FMS on FMS.MedSpec_id = MSO.MedSpec_id
						left join fed.v_MedSpec FMSP on FMSP.MedSpec_id = FMS.MedSpec_pid
					where MSF.MedStaffFact_id = :MedStaffFact_id
				";
				$resp_msf = $this->queryResult($query, ["MedStaffFact_id" => $this->MedStaffFact_id]);
				if (!empty($resp_msf[0]["MedStaffFact_id"])) {
					$FedMedSpec_Code = $resp_msf[0]["FedMedSpec_Code"];
					$FedMedSpecParent_Code = $resp_msf[0]["FedMedSpecParent_Code"];
				}
			}
			if (!empty($this->LpuSection_id)) {
				$query = "
					select
						LS.LpuSection_id as \"LpuSection_id\",
						LSL.MedicalCareKind_id as \"MedicalCareKind_id\"
					from
						v_LpuSection LS
						left join lateral (
							select t2.MedicalCareKind_id, t2.MedicalCareKind_Code
							from
								r66.v_LpuSectionLink t1
								left join fed.v_MedicalCareKind t2 on t2.MedicalCareKind_id = t1.MedicalCareKind_id
							where t1.LpuSection_id = LS.LpuSection_id
							limit 1
						) LSL on true
					where LS.LpuSection_id = :LpuSection_id
					  and LSL.MedicalCareKind_Code not in ('4', '11', '12', '13')
				";
				$resp_ls = $this->queryResult($query, ["LpuSection_id" => $this->LpuSection_id]);
				if (!empty($resp_ls[0]["LpuSection_id"])) {
					$LSMedicalCareKind_id = $resp_ls[0]["MedicalCareKind_id"];
				}
			}
			if ($this->payTypeSysNick == "bud" && !empty($LSMedicalCareKind_id)) {
				$this->setAttribute("MedicalCareKind_id", $LSMedicalCareKind_id);
			} else if ($Diag_Code == "Z51.5") {
				$this->setAttribute("MedicalCareKind_id", $this->getFirstResultFromQuery("select MedicalCareKind_id as \"MedicalCareKind_id\" from fed.v_MedicalCareKind where MedicalCareKind_Code = '4'"));
			} else if (!empty($FedMedSpecParent_Code)) {
				if ($FedMedSpecParent_Code == "204") {
					$this->setAttribute("MedicalCareKind_id", $this->getFirstResultFromQuery("select MedicalCareKind_id as \"MedicalCareKind_id\" from fed.v_MedicalCareKind where MedicalCareKind_Code = '11'"));
				} else {
					if (!empty($FedMedSpec_Code) && in_array($FedMedSpec_Code, ["16", "22", "27"])) {
						$this->setAttribute("MedicalCareKind_id", $this->getFirstResultFromQuery("select MedicalCareKind_id as \"MedicalCareKind_id\" from fed.v_MedicalCareKind where MedicalCareKind_Code = '12'"));
					} else {
						$this->setAttribute("MedicalCareKind_id", $this->getFirstResultFromQuery("select MedicalCareKind_id as \"MedicalCareKind_id\" from fed.v_MedicalCareKind where MedicalCareKind_Code = '13'"));
					}
				}
			} else {
				$this->setAttribute("MedicalCareKind_id", null);
			}
		}
	}

	/**
	 * Создание нового XML-документа протокола осмотра
	 * Создание протокола осмотра с данными по умолчанию или копирование указанного протокола осмотра после успешного сохранения объекта
	 * @return bool
	 */
	protected function _addEvnXml()
	{
		if (!in_array($this->evnClassId, [11, 13]) || !$this->isNewRecord || (empty($this->_params["allowCreateEmptyEvnDoc"]) && empty($this->_params["copyEvnXml_id"]))) {
			return true;
		}
		$this->load->library("swXmlTemplate");
		$instance = swXmlTemplate::getEvnXmlModelInstance();
		$response = [];
		// #77801 Если в настройках указано не копировать шаблон - берём шаблон по умолчанию
		if (!empty($this->_params["allowCreateEmptyEvnDoc"]) && ($this->options["polka"]["arm_evn_xml_copy"] != 2 || empty($this->_params["copyEvnXml_id"]))) {
			$response = $instance->createEmpty([
				"session" => $this->sessionParams,
				"Evn_id" => $this->id,
				"MedStaffFact_id" => $this->MedStaffFact_id,//для получения шаблона по умолчанию
				"XmlType_id" => swEvnXml::EVN_VIZIT_PROTOCOL_TYPE_ID,
				"EvnClass_id" => $this->evnClassId,
				"Server_id" => $this->Server_id,
			], false);
		} else if (isset($this->_params["copyEvnXml_id"])) {
			$response = $instance->doCopy([
				"session" => $this->sessionParams,
				"EvnXml_id" => $this->_params["copyEvnXml_id"],
				"Evn_id" => $this->id,
			], false);
		}
		$alert_msg = null;
		if (count($response) > 0) {
			if (isset($response[0]["Error_Msg"])) {
				$alert_msg = $response[0]["Error_Msg"];
			}
			if (isset($response[0]["EvnXml_id"])) {
				$this->_saveResponse["EvnXml_id"] = $response[0]["EvnXml_id"];
			}
		} else {
			$alert_msg = "Не удалось создать пустой XML-документ протокола осмотра";
		}
		if (isset($alert_msg)) {
			if (isset($this->_saveResponse["Alert_Msg"])) {
				$this->_saveResponse["Alert_Msg"] .= "<br>" . $alert_msg;
			} else {
				$this->_saveResponse["Alert_Msg"] = $alert_msg;
			}
		}
		return true;
	}

	/**
	 * Логика после успешного сохранения объекта
	 * @param array $result
	 * @throws Exception
	 */
	protected function _afterSave($result)
	{
		$this->_updateMes();
		$this->_updateEvnUslugaVizit();
		$this->_addEvnXml();
		$this->_completeHomeVisit();
		$this->_saveDrugTherapyScheme();
		if (!in_array(getRegionNick(), ["perm", "kareliya", "kz"]) && !empty($this->EvnUslugaLinkChange)) {
			$this->load->model("EvnUslugaPar_model");
			foreach ($this->EvnUslugaLinkChange as $usl) {
				$funcParams = null;
				switch ($usl["type"]) {
					case "unlink":
						$funcParams = [
							"EvnUslugaPar_id" => $usl["EvnUslugaPar_id"],
							"EvnUslugaPar_pid" => null,
							"pmUser_id" => $this->promedUserId,
							"session" => $this->sessionParams
						];
						break;
					case "link":
						$funcParams = [
							"EvnUslugaPar_id" => $usl["EvnPrescr_pid"],
							"EvnUslugaPar_pid" => $this->id,
							"pmUser_id" => $this->promedUserId,
							"session" => $this->sessionParams
						];
						break;
				}
				if($funcParams != null) {
					$this->EvnUslugaPar_model->editEvnUslugaPar($funcParams);
				}
			}
		}
		parent::_afterSave($result);
		$this->load->model("TimetableGraf_model");
		$this->TimetableGraf_model->onAfterSaveEvnVizit($this);
		$this->_saveResponse["TimetableGraf_id"] = $this->TimetableGraf_id;
		$this->_savePregnancyEvnVizitPL();
		if ($this->_isAttributeChanged("diag_id")) {
			$query = "
				select movpld.MorbusOnkoVizitPLDop_id as \"MorbusOnkoVizitPLDop_id\"
				from v_MorbusOnkoVizitPLDop movpld
				where 
					movpld.EvnVizit_id = :id
					and movpld.Diag_id not in (
						select Diag_id from v_EvnVizitPL where EvnVizitPL_id = :id union
						select Diag_id from v_EvnDiag where EvnDiag_pid = :id union
						select coalesce(Diag_spid,0) as Diag_id from v_EvnVizitPL where EvnVizitPL_id = :id union
						select coalesce(Diag_spid,0) as Diag_id from v_EvnPLDispScreenOnko where EvnPLDispScreenOnko_pid = :id
					)
				limit 1
			";
			$MorbusOnkoVizitPLDop_id = $this->getFirstResultFromQuery($query, ["id" => $this->id], true);
			if ($MorbusOnkoVizitPLDop_id === false) {
				throw new Exception("Ошибка при проверке талона дополнений больного ЗНО");
			}
			if (!empty($MorbusOnkoVizitPLDop_id)) {
				$this->load->model("MorbusOnkoVizitPLDop_model");
				$resp = $this->MorbusOnkoVizitPLDop_model->delete([
					"MorbusOnkoVizitPLDop_id" => $MorbusOnkoVizitPLDop_id,
					'pmUser_id' => $this->promedUserId
				]);
				if (!$this->isSuccessful($resp)) {
					throw new Exception($resp[0]["Error_Msg"]);
				}
			}
		}
		if (getRegionNick() == "perm" && $this->_params["addB04069333"] == true) {
			$query = "
				select UslugaComplex_id as \"UslugaComplex_id\"
				from v_UslugaComplex
				where UslugaComplex_Code = 'B04.069.333'
				  and (UslugaComplex_begDT is null or UslugaComplex_begDT <= :setDate)
				  and (UslugaComplex_endDT is null or UslugaComplex_endDT >= :setDate)
				limit 1
			";
			$UslugaComplex_id = $this->getFirstResultFromQuery($query, ["setDate" => $this->setDate], null);
			$LpuSectionProfile_id = (!empty($this->LpuSectionProfile_id))
				?$this->LpuSectionProfile_id
				:$this->getFirstResultFromQuery("select LpuSectionProfile_id as \"LpuSectionProfile_id\" from v_LpuSection where LpuSection_id = :LpuSection_id limit 1", array('LpuSection_id' => $this->LpuSection_id));
			if (!empty($UslugaComplex_id)) {
				$this->load->model("EvnUsluga_model");
				$usluga_data = [
					"EvnUslugaCommon_id" => null,
					"EvnUslugaCommon_pid" => $this->id,
					"Lpu_id" => $this->Lpu_id,
					"Server_id" => $this->Server_id,
					"PersonEvn_id" => $this->PersonEvn_id,
					"Person_id" => $this->Person_id,
					"EvnUslugaCommon_setDate" => $this->setDate,
					"EvnUslugaCommon_setTime" => $this->setTime,
					"PayType_id" => $this->PayType_id,
					"Usluga_id" => null,
					"UslugaComplex_id" => $UslugaComplex_id,
					"HealthKind_id" => (!empty($this->HealthKind_id) ? $this->HealthKind_id : NULL),
					"MedPersonal_id" => $this->MedPersonal_id,
					"MedStaffFact_id" => $this->MedStaffFact_id,
					"LpuSectionProfile_id" => $LpuSectionProfile_id,
					"UslugaPlace_id" => 1, // Место выполнения: отделение
					"Lpu_uid" => null,
					"LpuSection_uid" => $this->LpuSection_id,
					"Org_uid" => null,
					"EvnUslugaCommon_Kolvo" => 1,
					"EvnUslugaCommon_IsVizitCode" => 1,
					"pmUser_id" => $this->promedUserId,
					"session" => $this->sessionParams,
				];
				$this->EvnUsluga_model->isAllowTransaction = false;
				$tmp = $this->EvnUsluga_model->saveEvnUslugaCommon($usluga_data);
				if (!empty($tmp[0]["Error_Msg"])) {
					//нужно откатить транзакцию
					throw new Exception($tmp[0]["Error_Msg"]);
				}
			}
		}
		if (getRegionNick() == "perm") {
			$this->load->model("EvnPL_model");
			$this->EvnPL_model->checkEvnPLCrossed(["EvnPL_id" => $this->pid]);
		}
		if (getRegionNick() == 'kz') {

			$EvnLinkAPP_id = $this->getFirstResultFromQuery("select EvnLinkAPP_id from r101.EvnLinkAPP where Evn_id = ?", [$this->id]);
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
	 * @throws Exception
	 */
	function _savePregnancyEvnVizitPL()
	{
		$this->load->model("PregnancyEvnVizitPL_model");
		$resp = $this->PregnancyEvnVizitPL_model->savePregnancyEvnVizitPLData([
			"PregnancyEvnVizitPL_Period" => $this->_params["PregnancyEvnVizitPL_Period"],
			"EvnVizitPL_id" => $this->id,
			"pmUser_id" => $this->promedUserId
		]);
		if (!$this->isSuccessful($resp)) {
			throw new Exception($resp[0]["Error_Msg"], $resp[0]["Error_Code"]);
		}
	}

	/**
	 * Отправить в очередь на идентификацию
	 * @throws Exception
	 */
	protected function _toIdent()
	{
		if (getRegionNick() == "penza" && !empty($this->id) && $this->isNewRecord && $this->payTypeSysNick == "oms") {
			//Отправить человека в очередь на идентификацию
			$this->load->model("Person_model", "pmodel");
			$this->pmodel->isAllowTransaction = false;
			$funcParams = [
				"Person_id" => $this->Person_id,
				"Evn_id" => $this->id,
				"PersonRequestSourceType_id" => 3,
				"pmUser_id" => $this->promedUserId,
			];
			$resp = $this->pmodel->addPersonRequestData($funcParams);
			$this->pmodel->isAllowTransaction = true;
			if (!$this->isSuccessful($resp) && !in_array($resp[0]["Error_Code"], [302, 303])) {
				throw new Exception($resp[0]["Error_Msg"]);
			}
		}
	}

	/**
	 * Проверки и другая логика после удаления объекта
	 * @param array $result
	 * @throws Exception
	 */
	protected function _afterDelete($result)
	{
		parent::_afterDelete($result);
		if (getRegionNick() == "perm" && !empty($this->pid)) {
			$this->load->model("EvnPL_model");
			$this->EvnPL_model->checkEvnPLCrossed(["EvnPL_id" => $this->pid]);
		}
	}

	/**
	 * Сохранение фактического времени посещения
	 * @throws Exception
	 */
	protected function _saveVizitFactTime()
	{
		$this->load->model("TimetableGraf_model");
		$ttgdata = $this->TimetableGraf_model->onBeforeSaveEvnVizit($this);
		$this->setAttribute("TimetableGraf_id", $ttgdata["TimetableGraf_id"]);
		$this->setAttribute("EvnDirection_id", $ttgdata["EvnDirection_id"]);
		if ($this->isNewRecord && empty($this->TimetableGraf_id)) {
			throw new Exception("Ошибка при сохранении фактического времени приема!", 500);
		}
		if (false == $this->isNewRecord && !empty($this->_savedData["evndirection_id"]) && $this->_isAttributeChanged("EvnDirection_id")) {
			throw new Exception("Нельзя изменить направление", 500);
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
		if (in_array($this->scenario, [self::SCENARIO_DO_SAVE, self::SCENARIO_AUTO_CREATE])) {
			if ($this->_params["streamInput"] == false && $this->isNewRecord && !$this->parent->isNewRecord && $this->parent->_savedData[$this->parent->_getColumnName("isfinish")] == 2 && $this->parent->IsFinish == 2) {
				throw new Exception("Талон закрыт - добавление посещения невозможно.", 400);
			}
			if (empty($this->PayType_id) && getRegionNick()!='kz') {
				throw new Exception("Не указан вид оплаты", 400);
			}
			if (empty($this->MedPersonal_id)) {
				throw new Exception("Не указан врач", 400);
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
			if (!$this->_params["copyEvnDiagPLStom"]) {
				$this->_checkChangeVizitCode();
			}
			$this->_checkChangeMes();
			$this->_checkChangeSetDate();
			//Проверка двойственности посещений пациентов
			$this->_controlDoubleVizit();
			//Проверка на второе посещение НМП
			$this->_controlDoubleNMP($this->parent->id, $this->id, strpos($this->evnClassSysNick(), "Stom") != 0);
		}
		if (self::SCENARIO_DELETE == $this->scenario) {
			if ($this->parent->IsFinish == 2) {
				throw new Exception("Талон закрыт - удаление посещения невозможно.", 400);
			}
			if ($this->regionNick != "perm" || $this->evnClassId != 13) {
				// для новой стоматки Перми данную проверку убрал, т.к. в ней нет основного диагноза (диагноз указывается в заболеваниях).
				$hasOtherVizitWithDiag = false;
				foreach ($this->parent->evnVizitList as $id => $row) {
					if ($id != $this->id && false == empty($row["Diag_id"])) {
						$hasOtherVizitWithDiag = true;
					}
				}
				if (count($this->parent->evnVizitList) > 1 && $this->parent->Diag_id > 0 && false == $hasOtherVizitWithDiag) {
					throw new Exception("Удаление невозможно. Случай лечения должен содержать хотя бы одно посещение с указанным основным диагнозом.");
				}
			}
		}
		if (!empty($this->id) && $this->scenario == self::SCENARIO_DO_SAVE && $this->evnClassId == 11) {
			$query = "
				select count(*) as \"cnt\"
				from v_EvnDiag
				where EvnDiag_pid = :id
				  and Diag_id = :Diag_id
			";
			$queryParams = [
				"id" => $this->id,
				"Diag_id" => $this->Diag_id
			];
			$cnt = $this->getFirstResultFromQuery($query, $queryParams);
			if ($cnt > 0) {
				throw new Exception("Сопутствующий диагноз не должен совпадать с основным. Пожалуйста, проверьте корректность выбора основного и сопутствующих диагнозов");
			}
		}
	}

	/**
	 * Проверка на второе посещение НМП в Бурятии
	 * @param $EvnID
	 * @param null $EvnVisitID
	 * @param bool $Stom
	 * @throws Exception
	 */
	protected function _controlDoubleNMP($EvnID, $EvnVisitID = null, $Stom = false)
	{
		$stomStr = "";
		$whereVisit = "";
		if ($Stom) {
			$stomStr = "Stom";
		}
		if (!empty($EvnVisitID)) {
			$whereVisit = "and EvnVizitPL{$stomStr}_id <> :EvnVisitID";
		}
		if ($this->regionNick == "buryatiya") {
			$selectString = "count(EVPL.TreatmentClass_id)";
			$fromString = "
				v_EvnVizitPL{$stomStr} EVPL
				inner join v_EvnPL{$stomStr} EPL on EVPL.EvnVizitPL{$stomStr}_rid = EPL.EvnPL{$stomStr}_id
			";
			$whereString = "
					EPL.EvnPL{$stomStr}_id = :EvnID
				{$whereVisit}
				and EVPL.TreatmentClass_id = 2
			";
			$query = "
				select {$selectString}
				from {$fromString}
				where {$whereString}
			";
			$queryParams = [
				"EvnID" => $EvnID,
				"EvnVisitID" => $EvnVisitID
			];
			$result = $this->getFirstResultFromQuery($query, $queryParams);
			if ($result > 0) {
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
		$double_vizit_control = $this->allOptions["polka"]["double_vizit_control"];
		$isAllowControlDoubleVizit = (
			($this->scenario == self::SCENARIO_DO_SAVE || $this->scenario == self::SCENARIO_AUTO_CREATE) &&
			($this->payTypeSysNick == "oms" || in_array($this->regionNick, ["kz", "vologda"])) &&
			($double_vizit_control == 3 || ($double_vizit_control == 2 && $this->_params["ignoreDayProfileDuplicateVizit"] == false))
		);
		if ($isAllowControlDoubleVizit) {
			// Проверка для ККБ
			if ($this->regionNick == "perm" && $this->Lpu_id == 150185 && $this->evnClassId == 11) {
				$query = "
					select 1 as \"rec\"
					from v_EvnVizitPL EVPL
					where EVPL.Lpu_id = :Lpu_id
					  and EVPL.EvnVizitPL_id <> coalesce(CAST(:EvnVizitPL_id as bigint), 0)
					  and EVPL.MedPersonal_id = :MedPersonal_id
					  and EVPL.EvnVizitPL_setDate = :EvnVizitPL_setDate::timestamp
					  and EVPL.PayType_id = :PayType_id
					  and EVPL.Person_id = :Person_id
					limit 1
				";
				$queryParams = [
					"EvnVizitPL_id" => $this->id,
					"EvnVizitPL_setDate" => $this->setDate,
					"Lpu_id" => $this->Lpu_id,
					"MedPersonal_id" => $this->MedPersonal_id,
					"PayType_id" => 1,
					"Person_id" => $this->Person_id
				];
				$result = $this->getFirstResultFromQuery($query, $queryParams);
				if ($result > 0) {
					if ($double_vizit_control == 2) {
						$this->_saveResponse['ignoreParam'] = "ignoreDayProfileDuplicateVizit";
						$this->_saveResponse["Alert_Msg"] = "Данное посещение не войдет в реестр на оплату, как повторное посещение врача за один день. Продолжить сохранение?";
						throw new Exception("YesNo");
					} else if ($double_vizit_control == 3) {
						throw new Exception("Данное посещение не войдет в реестр на оплату, как повторное посещение врача за один день");
					}
				}
			}
			$add_where = "";
			$add_params = [];
			if ($this->evnClassId == 13) {
				$add_where .= "
					and EVPL.Diag_id = coalesce(CAST(:Diag_id as bigint),0)
					and EVPSPL.Tooth_id = coalesce(CAST(:Tooth_id as bigint),0)
				";
				$add_params['Diag_id'] = $this->Diag_id;
				$add_params['Tooth_id'] = $this->Tooth_id;
			}
			if (in_array($this->evnClassId, [11, 13])) {
				switch ($this->regionNick) {
					case "ekb":
						$query = "
							select count(*) as \"rec\"
							from
								{$this->viewName()} evpl
								LEFT OUTER JOIN v_EvnVizitPLStom EVPSPL
								ON EVPL.{$this->primaryKey()} = EVPSPL.evnvizitplstom_id
								inner join v_PayType pt on pt.PayType_id = evpl.PayType_id and pt.PayType_SysNick = 'oms'
								inner join v_MedStaffFact msf on msf.MedStaffFact_id = evpl.MedStaffFact_id
								inner join lateral (
									select MedSpecOms_id
									from v_MedStaffFact
									where MedStaffFact_id = :MedStaffFact_id
									limit 1
								) as curmsf on true
							where evpl.Lpu_id = :Lpu_id
							  and evpl.{$this->primaryKey()} <> coalesce(CAST(:EvnVizitPL_id as bigint), 0)
							  and evpl.{$this->tableName()}_setDate = :EvnVizitPL_setDate::timestamp
							  and evpl.Person_id = :Person_id
							  and msf.MedSpecOms_id = curmsf.MedSpecOms_id
							  {$add_where}
						";
						$queryParams = [
							"EvnVizitPL_id" => $this->id,
							"EvnVizitPL_setDate" => $this->setDate,
							"Lpu_id" => $this->Lpu_id,
							"MedStaffFact_id" => $this->MedStaffFact_id,
							"Person_id" => $this->Person_id
						];
						$result = $this->getFirstResultFromQuery($query, array_merge($add_params, $queryParams));
						if (false === $result) {
							throw new Exception("Ошибка при контроле двойственности посещений пациентов", 500);
						}
						if ($result > 0) {
							if ($double_vizit_control == 2) {
								$this->_saveResponse['ignoreParam'] = "ignoreDayProfileDuplicateVizit";
								$this->_saveResponse["Alert_Msg"] = "Данное посещение не войдет в реестр на оплату, как повторное посещение<br/>по одной специальности за один день (кол-во двойных записей: {$result}). Продолжить сохранение?";
								throw new Exception("YesNo");
							} else if ($double_vizit_control == 3) {
								throw new Exception("Данное посещение не войдет в реестр на оплату, как повторное посещение<br/>по одной специальности за один день (кол-во двойных записей: {$result})");
							}
						}
						break;
					case "kz":
						$query = "
							select count(*) as \"rec\"
							from {$this->viewName()} EVPL
							where EVPL.Lpu_id = :Lpu_id
							  and EVPL.{$this->primaryKey()} <> coalesce(CAST(:EvnVizitPL_id as bigint), 0)
							  and (EVPL.MedPersonal_id = :MedPersonal_id or EVPL.MedPersonal_sid = :MedPersonal_sid)
							  and EVPL.{$this->tableName()}_setDate = :EvnVizitPL_setDate::timestamp
							  and EVPL.Person_id = :Person_id
						";
						$queryParams = [
							"EvnVizitPL_id" => $this->id,
							"EvnVizitPL_setDate" => $this->setDate,
							"Lpu_id" => $this->Lpu_id,
							"MedPersonal_id" => $this->MedPersonal_id,
							"MedPersonal_sid" => $this->MedPersonal_sid,
							"Person_id" => $this->Person_id
						];
						$result = $this->getFirstResultFromQuery($query, array_merge($add_params, $queryParams));
						if (false === $result) {
							throw new Exception("Ошибка при контроле двойственности посещений пациентов [kz]", 500);
						}
						if ($result > 0) {
							if ($double_vizit_control == 2) {
								$this->_saveResponse['ignoreParam'] = "ignoreDayProfileDuplicateVizit";
								$this->_saveResponse["Alert_Msg"] = "В системе уже сохранено посещение, у которого указана такая же дата посещения и такой же врач (фельдшер). Продолжить сохранение?";
								throw new Exception("YesNo");
							} else if ($double_vizit_control == 3) {
								throw new Exception("В системе уже сохранено посещение, у которого указана такая же дата посещения и такой же врач (фельдшер).");
							}
						}
						break;
					case "buryatiya":
						$query = "
							select
								EVPL.{$this->primaryKey()} as \"id\",
								to_char(EVPL.{$this->tableName()}_setDate, 'dd.mm.yyyy') as \"setDate\",
								ps.Person_SurName||coalesce(' '||ps.Person_FirName,'')||coalesce(' '||ps.Person_SecName,'') as \"Person_Fio\",
								msf.Person_Fio || coalesce(' (' || mso.MedSpecOms_Name || ')', '') as \"MedPersonal_Fio\",
								vt.VizitType_Name as \"VizitType_Name\"
							from
								{$this->viewName()} EVPL
								left join v_VizitType vt on vt.VizitType_id = evpl.VizitType_id
								left join v_PersonState ps on ps.Person_id = evpl.Person_id
								left join v_MedStaffFact msf  on msf.MedStaffFact_id = evpl.MedStaffFact_id
								left join v_MedSpecOms mso on mso.MedSpecOms_id = msf.MedSpecOms_id
							where EVPL.Lpu_id = :Lpu_id
							  and EVPL.{$this->primaryKey()} <> coalesce(CAST(:EvnVizitPL_id as bigint), 0)
							  and EVPL.MedPersonal_id = :MedPersonal_id -- по одному врачу
							  and cast(EVPL.{$this->tableName()}_setDate as date) = cast(:EvnVizitPL_setDate as date) -- в один день
							  and EVPL.Person_id = :Person_id -- на одного пациента
							  and EVPL.Diag_id = :Diag_id -- с одинаковым диагнозом
							  and EVPL.VizitType_id = :VizitType_id -- с одинаковой целью посещения
							limit 1
						";
						$queryParams = [
							"EvnVizitPL_id" => $this->id,
							"EvnVizitPL_setDate" => $this->setDate,
							"Lpu_id" => $this->Lpu_id,
							"MedPersonal_id" => $this->MedPersonal_id,
							"Person_id" => $this->Person_id,
							"Diag_id" => $this->Diag_id,
							"VizitType_id" => $this->VizitType_id
						];
						$resp_check = $this->queryResult($query, array_merge($add_params, $queryParams));
						if (!empty($resp_check[0]["id"])) {
							if ($double_vizit_control == 2) {
								$this->_saveResponse['ignoreParam'] = "ignoreDayProfileDuplicateVizit";
								$this->_saveResponse["Alert_Msg"] = "В системе уже имеется посещение {$resp_check[0]["Person_Fio"]} от {$resp_check[0]["setDate"]} с указанным диагнозом<br/>Врач - {$resp_check[0]["MedPersonal_Fio"]};<br/>Цель посещения - {$resp_check[0]["VizitType_Name"]}.<br/>Продолжить сохранение?";
								throw new Exception("YesNo");
							} else if ($double_vizit_control == 3) {
								throw new Exception("В системе уже имеется посещение {$resp_check[0]["Person_Fio"]} от {$resp_check[0]["setDate"]} с указанным диагнозом<br/>Врач - {$resp_check[0]["MedPersonal_Fio"]};<br/>Цель посещения - {$resp_check[0]["VizitType_Name"]}");
							}
						}
						break;
					case "kareliya":
						$query = "
							select
								EVPL.{$this->primaryKey()} as \"id\",
								to_char(EVPL.{$this->tableName()}_setDate, 'dd.mm.yyyy') as \"setDate\",
								ps.Person_SurName||coalesce(' '||ps.Person_FirName,'')||coalesce(' '||ps.Person_SecName,'') as \"Person_Fio\",
								lsp.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
								ls.LpuSection_Name as \"LpuSection_Name\"
							from
								{$this->viewName()} EVPL
								inner join v_PayType PT on EVPL.PayType_id = PT.PayType_id and PT.PayType_SysNick = 'oms'
								inner join v_LpuSection ls on ls.LpuSection_id = evpl.LpuSection_id
								inner join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = coalesce(evpl.LpuSectionProfile_id, ls.LpuSectionProfile_id)
								left join v_PersonState ps on ps.Person_id = evpl.Person_id
							where EVPL.Lpu_id = :Lpu_id
							  and EVPL.{$this->primaryKey()} <> coalesce(CAST(:EvnVizitPL_id as bigint), 0)
							  and EVPL.LpuSection_id = :LpuSection_id -- в одно отделение
							  and lsp.LpuSectionProfile_id = :LpuSectionProfile_id -- с одним профилем
							  and cast(EVPL.{$this->tableName()}_setDate as date) = cast(:EvnVizitPL_setDate as date) -- в один день
							  and EVPL.Person_id = :Person_id -- на одного пациента
							limit 1
						";
						$queryParams = [
							"EvnVizitPL_id" => $this->id,
							"EvnVizitPL_setDate" => $this->setDate,
							"Lpu_id" => $this->Lpu_id,
							"LpuSection_id" => $this->LpuSection_id,
							"LpuSectionProfile_id" => $this->LpuSectionProfile_id,
							"Person_id" => $this->Person_id,
							"Diag_id" => $this->Diag_id,
							"VizitType_id" => $this->VizitType_id
						];
						$resp_check = $this->queryResult($query, array_merge($add_params, $queryParams));
						if (!empty($resp_check[0]["id"])) {
							if ($double_vizit_control == 2) {
								$this->_saveResponse['ignoreParam'] = "ignoreDayProfileDuplicateVizit";
								$this->_saveResponse["Alert_Msg"] = "В системе уже имеется посещение {$resp_check[0]["Person_Fio"]} от {$resp_check[0]["setDate"]}, {$resp_check[0]["LpuSectionProfile_Name"]}, {$resp_check[0]["LpuSection_Name"]}<br/>Продолжить сохранение?";
								throw new Exception("YesNo");
							} else if ($double_vizit_control == 3) {
								throw new Exception("Сохранение посещения запрещено: в системе уже имеется посещение {$resp_check[0]["Person_Fio"]}, {$resp_check[0]["setDate"]}, {$resp_check[0]["LpuSectionProfile_Name"]}, {$resp_check[0]["LpuSection_Name"]}");
							}
						}
						break;
					case "perm":
						if (count($this->_doubles) > 0) {
							$firstDoubleEdit = true;
							$doublesEvnPL = [];
							if (strtotime($this->setDate) >= strtotime("01.06.2017")) {
								foreach ($this->_doubles as $double) {
									if ($double["EvnVizitPL_pid"] == $this->pid) {
										$doublesEvnPL[] = $double;
										if (!empty($double["VizitPLDouble_id"])) {
											$firstDoubleEdit = false;
										}
									}
								}
							}
							if (count($doublesEvnPL) > 0) {
								if (!empty($this->_params["EvnVizitPLDoublesData"])) {
									$doubleUpdate = [];
									foreach ($this->_params["EvnVizitPLDoublesData"] as $oneDouble) {
										if ($oneDouble["EvnVizitPL_id"] == -1 || (!empty($this->id) && $this->id == $oneDouble["EvnVizitPL_id"])) {
											$this->setAttribute("vizitpldouble_id", $oneDouble["VizitPLDouble_id"]);
										} else {
											$doubleUpdate[$oneDouble["EvnVizitPL_id"]] = $oneDouble["VizitPLDouble_id"];
										}
									}
									foreach ($doublesEvnPL as $double) {
										if (isset($doubleUpdate[$double["EvnVizitPL_id"]])) {
											$this->db->query("update EvnVizitPL set VizitPLDouble_id = :VizitPLDouble_id where Evn_id = :EvnVizitPL_id", [
												"EvnVizitPL_id" => $double["EvnVizitPL_id"],
												"VizitPLDouble_id" => $doubleUpdate[$double["EvnVizitPL_id"]]
											]);
										}
									}
								} else {
									if (!empty($this->VizitPLDouble_id)) {
										$firstDoubleEdit = false;
									}
									if ($firstDoubleEdit) {
										$firstDouble = true;
										foreach ($doublesEvnPL as $key => $double) {
											$doublesEvnPL[$key]["VizitPLDouble_id"] = ($firstDouble) ? 1 : 2;
											$firstDouble = false;
										}
									}
									// Для посещений с датой после 01.06.2017 если дублирующее посещения находится в той же ТАП
									// вместо предупреждения выводится форма с таблицей, в которой отображаются все такие посещения, включая текущее.
									$doublesEvnPL[] = [
										"EvnVizitPL_id" => !empty($this->id) ? $this->id : -1,
										"LpuSection_Name" => $this->getFirstResultFromQuery("select LpuSection_Name as \"LpuSection_Name\" from v_LpuSection where LpuSection_id = :LpuSection_id", ["LpuSection_id" => $this->LpuSection_id]),
										"MedPersonal_Fio" => $this->getFirstResultFromQuery("select Person_Fio as \"Person_Fio\" from v_MedStaffFact where MedStaffFact_id = :MedStaffFact_id", ["MedStaffFact_id" => $this->MedStaffFact_id]),
										"EvnVizitPL_setDate" => date("d.m.Y", strtotime($this->setDate)),
										"EvnVizitPL_pid" => $this->pid,
										"VizitPLDouble_id" => $firstDoubleEdit ? 2 : $this->VizitPLDouble_id,
										"accessType" => "edit"
									];
									$this->_saveResponse["Alert_Msg"] = $doublesEvnPL;
									$this->_saveResponse["Cancel_Error_Handle"] = true;
									throw new Exception("EvnVizitPLDouble");
								}
							} else {
								$count = count($this->_doubles);
								if ($double_vizit_control == 2) {
									$this->_saveResponse['ignoreParam'] = "ignoreDayProfileDuplicateVizit";
									$this->_saveResponse["Alert_Msg"] = "Данное посещение не войдет в реестр на оплату, как повторное посещение<br/>по одной специальности за один день (кол-во двойных записей: {$count}). Продолжить сохранение?";
									throw new Exception("YesNo");
								} else if ($double_vizit_control == 3) {
									throw new Exception("Данное посещение не войдет в реестр на оплату, как повторное посещение<br/>по одной специальности за один день (кол-во двойных записей: {$count})");
								}
							}
						}
						break;
					case "ufa":
						if (count($this->_doubles) > 0) {
							$count = count($this->_doubles);

							if ($double_vizit_control == 2) {
								$this->_saveResponse['ignoreParam'] = "ignoreDayProfileDuplicateVizit";
								$this->_saveResponse["Alert_Msg"] = "Данное посещение не войдет в реестр на оплату, как повторное посещение<br/>по одному профилю отделения к одному врачу за один день (кол-во двойных записей: {$count}). Продолжить сохранение?";
								throw new Exception("YesNo");
							} else if ($double_vizit_control == 3) {
								throw new Exception("Данное посещение не войдет в реестр на оплату, как повторное посещение<br/>по одному профилю отделения к одному врачу за один день (кол-во двойных записей: {$count}).");
							}
						}
						break;
					case "vologda":
						$query = "
							select
								EVPL.{$this->primaryKey()} as \"id\",
								to_char(EVPL.{$this->tableName()}_setDate, 'dd.mm.yyyy') as \"setDate\",
								ps.Person_SurName||coalesce(' '||ps.Person_FirName,'')||coalesce(' '||ps.Person_SecName,'') as \"Person_Fio\",
								lsp.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
								ls.LpuSection_Name as \"LpuSection_Name\"
							from
								{$this->viewName()} EVPL
								inner join v_PayType PT on EVPL.PayType_id = PT.PayType_id
								inner join v_LpuSection ls on ls.LpuSection_id = evpl.LpuSection_id
								inner join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = coalesce(evpl.LpuSectionProfile_id, ls.LpuSectionProfile_id)
								left join v_PersonState ps on ps.Person_id = evpl.Person_id
							where EVPL.{$this->primaryKey()} <> coalesce(CAST(:EvnVizitPL_id as bigint), 0)
							  and EVPL.PayType_id = :PayType_id
							  and EVPL.MedStaffFact_id = :MedStaffFact_id
							  and cast(EVPL.{$this->tableName()}_setDate as date) = cast(:EvnVizitPL_setDate as date) -- в один день
							  and EVPL.Person_id = :Person_id -- на одного пациента
							limit 1
						";
						$queryParams = [
							"EvnVizitPL_id" => $this->id,
							"EvnVizitPL_setDate" => $this->setDate,
							"Lpu_id" => $this->Lpu_id,
							"PayType_id" => $this->PayType_id,
							"MedStaffFact_id" => $this->MedStaffFact_id,
							"Person_id" => $this->Person_id
						];
						$resp_check = $this->queryResult($query, array_merge($add_params, $queryParams));
						if (!empty($resp_check[0]["id"])) {
							if ($double_vizit_control == 2) {
								$this->_saveResponse['ignoreParam'] = "ignoreDayProfileDuplicateVizit";
								$this->_saveResponse["Alert_Msg"] = "В системе уже имеется посещение {$resp_check[0]["Person_Fio"]} от {$resp_check[0]["setDate"]}, {$resp_check[0]["LpuSectionProfile_Name"]}, {$resp_check[0]["LpuSection_Name"]}<br/>Продолжить сохранение?";
								throw new Exception("YesNo");
							} else if ($double_vizit_control == 3) {
								throw new Exception("Сохранение посещения запрещено: в системе уже имеется посещение {$resp_check[0]["Person_Fio"]}, {$resp_check[0]["setDate"]}, {$resp_check[0]["LpuSectionProfile_Name"]}, {$resp_check[0]["LpuSection_Name"]}");
							}
						}
						break;
					default:
						$query = "
							select count(*) as \"rec\"
							from
								{$this->viewName()} EVPL
								LEFT OUTER JOIN v_EvnVizitPLStom EVPSPL
								ON EVPL.{$this->primaryKey()} = EVPSPL.evnvizitplstom_id
								inner join v_LpuSection LS on LS.LpuSection_id = EVPL.LpuSection_id
								inner join v_PayType PT on EVPL.PayType_id = PT.PayType_id and PT.PayType_SysNick = 'oms'
							where EVPL.Lpu_id = :Lpu_id
							  and EVPL.Person_id = :Person_id
							  and EVPL.{$this->primaryKey()} <> coalesce(CAST(:EvnVizitPL_id as bigint), 0)
							  and EVPL.MedPersonal_id = :MedPersonal_id
							  and LS.LpuSectionProfile_id = (
								select LpuSectionProfile_id
								from LpuSection
								where LpuSection_id = :LpuSection_id
								limit 1
							  )
							  and EVPL.{$this->tableName()}_setDate = :EvnVizitPL_setDate::timestamp
							  and EVPL.Diag_id = coalesce(CAST(:Diag_id as bigint),0)
							  {$add_where}
						";
						$queryParams = [
							"EvnVizitPL_id" => $this->id,
							"EvnVizitPL_setDate" => $this->setDate,
							"Lpu_id" => $this->Lpu_id,
							"LpuSection_id" => $this->LpuSection_id,
							"MedPersonal_id" => $this->MedPersonal_id,
							"PayType_id" => $this->PayType_id,
							"Person_id" => $this->Person_id,
							"Diag_id" => $this->Diag_id
						];
						$result = $this->getFirstResultFromQuery($query, array_merge($add_params, $queryParams));
						if (false === $result) {
							throw new Exception("Ошибка при контроле двойственности посещений пациентов", 500);
						}
						if ($result > 0) {
							if ($double_vizit_control == 2) {
								$this->_saveResponse['ignoreParam'] = "ignoreDayProfileDuplicateVizit";
								$this->_saveResponse["Alert_Msg"] = "Данное посещение не войдет в реестр на оплату, как повторное посещение<br/>по одной специальности за один день (кол-во двойных записей: {$result}). Продолжить сохранение?";
								throw new Exception("YesNo");
							} else if ($double_vizit_control == 3) {
								throw new Exception("Данное посещение не войдет в реестр на оплату, как повторное посещение<br/>по одной специальности за один день (кол-во двойных записей: {$result})");
							}
						}
						break;
				}
			}
		}
	}

	/**
	 * Обязательность основного диагноза посещения
	 * @return bool
	 */
	function _isRequiredDiag()
	{
		return (in_array($this->regionNick, ["ekb", "pskov", "ufa"]) && in_array($this->scenario, [self::SCENARIO_DO_SAVE, self::SCENARIO_SET_ATTRIBUTE]));
	}

	/**
	 * Проверки и другая логика перед сохранением объекта
	 * @param array $data Массив входящих параметров
	 * @throws Exception
	 */
	protected function _beforeSave($data = [])
	{
		parent::_beforeSave($data);
		$isStom = (13 == $this->evnClassId);
		if (getRegionNick() == "kareliya" && !empty($this->VizitType_id)) {
			// проверяем значение VizitType_id
			$query = "
				select VizitType_id as \"VizitType_id\"
				from v_VizitType
				where VizitType_id = :VizitType_id
				  and coalesce(VizitType_begDT, :EvnVizitPL_setDate) <= :EvnVizitPL_setDate
				  and coalesce(VizitType_endDT, :EvnVizitPL_setDate) >= :EvnVizitPL_setDate
				limit 1
			";
			$queryParams = [
				"VizitType_id" => $this->VizitType_id,
				"EvnVizitPL_setDate" => $this->setDate
			];
			$resp = $this->queryResult($query, $queryParams);
			if (empty($resp[0]["VizitType_id"])) {
				throw new Exception("В поле \"Цель посещения\" выбрано закрытое значение.", 400);
			}
		}
		if ($isStom && !empty($this->diag_id) && empty($this->deseasetype_id)) {
			// если диагноз не из группы Z
			$resp_diag = $this->queryResult("select Diag_Code as \"Diag_Code\" from v_Diag where Diag_id = :Diag_id", ["Diag_id" => $this->diag_id]);
			if (empty($resp_diag[0]["Diag_Code"]) || mb_substr($resp_diag[0]["Diag_Code"], 0, 1) != "Z") {
				throw new Exception("Поле \"Характер заболевания\" обязательно для заполнения при заполненном диагнозе.", 400);
			}
		}
		if (!$isStom && !in_array(getRegionNick(), ["ufa", "astra"])) {
			// пациентов >= 18 лет нельзя принять в детском отделении
			if (!$this->hasPreviusChildVizit() && $this->person_Age >= 18 && $this->LpuSectionData["LpuSectionAge_id"] == 2) {
				throw new Exception("Возрастная группа отделения не соответствуют возрасту пациента. Приём невозможен.", 400);
			}
		}
		if ($this->regionNick == "vologda" && in_array($this->scenario, [self::SCENARIO_DO_SAVE, self::SCENARIO_SET_ATTRIBUTE]) && $this->TreatmentClass_id == 4 && empty($this->PersonDisp_id)) {
			throw new Exception("При виде обращения \"Диспансерное наблюдение (Заболевание)\" поле \"Карта дис. учета\" обязательна для заполнения.", __LINE__);
		}
		if ($isStom === false && in_array(getRegionNick(), ["perm"]) && in_array($this->scenario, [self::SCENARIO_DO_SAVE, self::SCENARIO_SET_ATTRIBUTE]) && in_array($this->serviceTypeSysNick, ["home", "ahome", "neotl"]) && $this->_params["ignoreCheckB04069333"] == false && $this->setDT->getTimestamp() < strtotime("01.05.2018")) {
			$query = "
				select t1.EvnUsluga_id as \"EvnUsluga_id\"
				from
					v_EvnUsluga t1
					inner join v_UslugaComplex t2 on t2.UslugaComplex_id = t1.UslugaComplex_id
				where t1.EvnUsluga_pid = :EvnVizitPL_id
				  and t2.UslugaComplex_Code = 'B04.069.333'
				limit 1
			";
			$queryParams = ["EvnVizitPL_id" => $this->id];
			$EvnUsluga_id = $this->getFirstResultFromQuery($query, $queryParams);
			if (empty($EvnUsluga_id)) {
				$this->_saveResponse["Alert_Msg"] = "Добавить в посещение услугу B04.069.333 «Оказание неотложной помощи вне медицинской организации (на дому)»?";
				throw new Exception("YesNo", 131);
			}
		}
		if (!$isStom && getRegionNick() == "pskov" && !empty($this->UslugaComplex_id)) {
			// #167294 объём 2019-НМП_УслугиПосещения
			$join = "";
			$filter = "";
			if ($this->TreatmentClass_id == 2) {
				$join .= "
					left join lateral(
						select av2.AttributeValue_ValueIdent
						from
							v_AttributeValue av2
							inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
						where av2.AttributeValue_rid = av.AttributeValue_id
						  and a2.Attribute_TableName = 'dbo.MedSpecOms'
						limit 1
					) as MSOFILTER on true
					left join lateral(
						select av2.AttributeValue_ValueIdent
						from
							v_AttributeValue av2
							inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
						where av2.AttributeValue_rid = av.AttributeValue_id
						  and a2.Attribute_TableName = 'dbo.LpuSectionProfile'
						limit 1
					) as LSPFILTER on true
				";
				$filter .= "
					and coalesce(MSOFILTER.AttributeValue_ValueIdent, (select MedSpecOms_id from mv3) , 0) = coalesce((select MedSpecOms_id from mv3) , 0)
					and coalesce(LSPFILTER.AttributeValue_ValueIdent, (select LpuSectionProfile_id from mv2) , 0) = COALESCE((select LpuSectionProfile_id from mv2) , LSPFILTER.AttributeValue_ValueIdent, 0)
				";
			}
			$query = "
				with mv1 as (
					select VolumeType_id
					from v_VolumeType
					where VolumeType_Code = '2019-НМП_УслугиПосещения'
					limit 1
				), mv2 as (
					select LpuSectionProfile_id
					from v_LpuSection
					where LpuSection_id = :LpuSection_id
					limit 1
				), mv3(
					select MedSpecOms_id
					from v_MedStaffFact
					where MedStaffFact_id = :MedStaffFact_id
					limit 1
				)
				select av.AttributeValue_id as \"AttributeValue_id\"
				from
					v_AttributeVision avis
					inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
					inner join v_Attribute a on a.Attribute_id = av.Attribute_id
					{$join}
				where avis.AttributeVision_TableName = 'dbo.VolumeType'
				  and avis.AttributeVision_TablePKey = (select VolumeType_id from mv1)
				  and avis.AttributeVision_IsKeyValue = 2
				  and coalesce(av.AttributeValue_begDate, :EvnVizitPL_setDT) <= :EvnVizitPL_setDT
				  and coalesce(av.AttributeValue_endDate, :EvnVizitPL_setDT) >= :EvnVizitPL_setDT
				  and av.AttributeValue_ValueIdent = :UslugaComplex_id
				  {$filter}
				limit 1
			";
			$queryParams = [
				"LpuSection_id" => $this->LpuSection_id,
				"LpuSectionProfile_id" => $this->LpuSectionProfile_id,
				"MedStaffFact_id" => $this->MedStaffFact_id,
				"UslugaComplex_id" => $this->UslugaComplex_id,
				"EvnVizitPL_setDT" => ConvertDateFormat("Y-m-d H:i:s", $this->setDT)
			];
			$resp_vol = $this->queryResult($query, $queryParams);
			if (($this->TreatmentClass_id == 2 && empty($resp_vol[0]["AttributeValue_id"])) || ($this->TreatmentClass_id != 2 && !empty($resp_vol[0]["AttributeValue_id"]))) {
				throw new Exception("Выбранный код посещения не соответствует виду обращения");
			}
		}
		// Проверяем есть ли услуги параклиники, которые не входят в пределы выбранных дат посещения. И есть ли в КВС услуги которые могли бы войти в данное движение refs #75644 %)
		$this->EvnUslugaLinkChange = null;
		if (!in_array(getRegionNick(), ["perm", "kareliya", "kz"]) && $this->scenario == self::SCENARIO_DO_SAVE) {
			// ищем дату начала и дату конца ТАП
			$EvnPL_setDT = $this->setDT;
			$EvnPL_disDT = $this->setDT;
			foreach ($this->parent->evnVizitList as $vizit) {
				if (empty($this->id) || $vizit["EvnVizitPL_id"] != $this->id) {
					if ($vizit["EvnVizitPL_setDT"] > $EvnPL_disDT) {
						$EvnPL_disDT = $vizit["EvnVizitPL_setDT"];
					}
					if ($vizit["EvnVizitPL_setDT"] < $EvnPL_setDT) {
						$EvnPL_setDT = $vizit["EvnVizitPL_setDT"];
					}
				}
			}
			$EvnPL_setDT = ConvertDateFormat($EvnPL_setDT, "Y-m-d H:i:s");
			$EvnPL_disDT = ConvertDateFormat($EvnPL_disDT, "Y-m-d H:i:s");
			$checkDateType = "::timestamp";
			if (getRegionNick() == "astra") {
				$checkDateType = "::date";
			}
			$query = "
				with mv1 as (
					select
						CAST(:EvnPL_id as bigint) as EvnPL_id,
						coalesce(EvnPL_isFinish, 1) as EvnPL_isFinish,
						:EvnPL_setDT as EvnPL_setDT,
						:EvnPL_disDT as EvnPL_disDT
					from v_EvnPL
					where EvnPL_id = :EvnPL_id
				), mv2 as (
					select
						epd.EvnDirection_id,
						ep.EvnPrescr_pid
					from
						v_EvnVizitPL evpl
						inner join v_EvnPrescr ep on ep.EvnPrescr_pid = evpl.EvnVizitPL_id
						inner join v_EvnPrescrDirection epd on epd.EvnPrescr_id = ep.EvnPrescr_id
					where evpl.EvnVizitPL_pid = (select EvnPL_id from mv1)
				)
				select
					eup.EvnUslugaPar_id as \"EvnUslugaPar_id\",
					D.EvnPrescr_pid as \"EvnPrescr_pid\",
					'unlink' as \"type\"
				from
					mv2 as D
					inner join v_EvnUslugaPar eup on eup.EvnDirection_id = D.EvnDirection_id
				where eup.EvnUslugaPar_pid is not null
				  and coalesce(eup.EvnUslugaPar_IsManual, 1) = 1
				  and (eup.EvnUslugaPar_setDT{$checkDateType} < (select EvnPL_setDT from mv1){$checkDateType}
				  or (eup.EvnUslugaPar_setDT{$checkDateType} > (select EvnPL_disDT from mv1){$checkDateType}
				  and (select EvnPL_disDT from mv1) is not null and (select EvnPL_isFinish from mv1) = 2))
				union all
				select
					eup.EvnUslugaPar_id as \"EvnUslugaPar_id\",
					D.EvnPrescr_pid as \"EvnPrescr_pid\",
					'nlink' as \"type\"
				from
					mv2 as D
					inner join v_EvnUslugaPar eup on eup.EvnDirection_id = D.EvnDirection_id
				where eup.EvnUslugaPar_pid is null
				  and coalesce(eup.EvnUslugaPar_IsManual, 1) = 1
				  and (eup.EvnUslugaPar_setDT{$checkDateType} >= (select EvnPL_setDT from mv1){$checkDateType}
				  and (eup.EvnUslugaPar_setDT{$checkDateType} <= (select EvnPL_disDT from mv1){$checkDateType}
				  or (select EvnPL_disDT from mv1) is null OR (select EvnPL_isFinish from mv1) = 1))
			";
			$queryParams = [
				"EvnPL_id" => $this->pid,
				"EvnPL_setDT" => $EvnPL_setDT,
				"EvnPL_disDT" => $EvnPL_disDT
			];
			$this->EvnUslugaLinkChange = $this->queryResult($query, $queryParams);
			if (!empty($this->EvnUslugaLinkChange) && empty($this->_params["ignoreCheckEvnUslugaChange"])) {
				// выдаём YesNo
				$this->_saveResponse["Alert_Msg"] = "Вы изменили период дат посещения пациента в отделении. Это приведет к изменению связей некоторых услуг и данного посещения. Продолжить сохранение?";
				throw new Exception("YesNo", 130);
			}
		}
		if (getRegionNick() != "kz" &&
			(
				(!$isStom && !empty($this->setDT) && $this->setDT instanceof DateTime && $this->setDT->format("Y") >= 2016) ||
				($isStom && ((empty($this->parent->setDT) && !empty($this->setDT) && $this->setDT instanceof DateTime && $this->setDT->getTimestamp() >= getEvnPLStomNewBegDate()) || ($this->parent->setDT instanceof DateTime && $this->parent->setDT->getTimestamp() >= getEvnPLStomNewBegDate())))
			)
		) {
			$xdate = strtotime("01.01.2016"); // для Перми поле появляется с 01.01.2016
			if (getRegionNick() != "perm") {
				$xdate = getEvnPLStomNewBegDate(); // для остальных зависит от даты нового стомат.тап
			}
			if ($this->scenario == self::SCENARIO_AUTO_CREATE && getRegionNick() == "ekb") {
				$this->setMedicalCareKindId();
			} else if ($this->scenario == self::SCENARIO_AUTO_CREATE) {
				// определяем на основе врача
				$query = "
					select
						fms.MedSpec_Code as \"MedSpec_Code\",
						fmsp.MedSpec_Code as \"ParentMedSpec_Code\"
					from
						v_MedStaffFact msf
						left join v_MedSpecOms mso on mso.MedSpecOms_id = msf.MedSpecOms_id
						left join fed.v_MedSpec fms on fms.MedSpec_id = mso.MedSpec_id
						left join fed.v_MedSpec fmsp on fmsp.MedSpec_id = fms.MedSpec_pid
					where msf.MedStaffFact_id = :MedStaffFact_id
					limit 1
				";
				$resp = $this->queryResult($query, ["MedStaffFact_id" => $this->MedStaffFact_id]);
				if ((!empty($resp[0]["ParentMedSpec_Code"]) && $resp[0]["ParentMedSpec_Code"] == 204) || (!empty($resp[0]["MedSpec_Code"]) && $resp[0]["MedSpec_Code"] == 204)) {
					// Если специальность врача из случая средняя, то вид мед. помощи = 11
					$this->MedicalCareKind_id = $this->getFirstResultFromQuery("select MedicalCareKind_id as \"MedicalCareKind_id\" from fed.v_MedicalCareKind where MedicalCareKind_Code = '11'");
				} else if (!empty($resp[0]["MedSpec_Code"]) && in_array($resp[0]["MedSpec_Code"], [16, 22, 27])) {
					// Если специальность врача из случая врачебная и равна 16, 22, 27 (терапевт, педиатр или ВОП), то вид мед. помощи = 12
					$this->MedicalCareKind_id = $this->getFirstResultFromQuery("select MedicalCareKind_id as \"MedicalCareKind_id\" from fed.v_MedicalCareKind where MedicalCareKind_Code = '12'");
				} else {
					// 13 – В остальных случаях
					$this->MedicalCareKind_id = $this->getFirstResultFromQuery("select MedicalCareKind_id as \"MedicalCareKind_id\" from fed.v_MedicalCareKind where MedicalCareKind_Code = '13'");
				}
				if ($this->regionNick == "ufa" && $this->scenario == self::SCENARIO_AUTO_CREATE && empty($this->medicalcarekind_id) && !$isStom) {
					$query = "
						select MCKLSP.MedicalCareKind_id as \"MedicalCareKind_id\"
						from
							v_LpuSection LS
							inner join v_MedicalCareKindLpuSectionProfile MCKLSP on LS.LpuSectionProfile_id = MCKLSP.LpuSectionProfile_id
						where LS.LpuSection_id = :LpuSection_id
					";
					$this->MedicalCareKind_id = $this->getFirstResultFromQuery($query, ["LpuSection_id" => $this->LpuSection_id]);
				}
				if ($this->regionNick == "kareliya" && !empty($resp[0]["MedSpec_Code"]) && !$isStom) {
					$query = "
						select MSL.MedicalCareKind_id as \"MedicalCareKind_id\"
						from
							fed.v_MedSpec MS
							inner join r10.v_MedSpecLink MSL on MS.MedSpec_id = MSL.MedSpec_id
							inner join fed.v_MedicalCareKind MCK on MSL.MedicalCareKind_id = MCK.MedicalCareKind_id
						where MS.MedSpec_Code = :MedSpec_Code
						  and MCK.MedicalCareKind_Code in ('11','12','13','4')
					";
					$this->MedicalCareKind_id = $this->getFirstResultFromQuery($query, ["MedSpec_Code" => $resp[0]["MedSpec_Code"]]);
				}
				$this->setAttribute("MedicalCareKind_id", $this->MedicalCareKind_id);
			} elseif (empty($this->MedicalCareKind_id) && ($this->evnClassId != 13 || ($this->parent->setDT instanceof DateTime && $this->parent->setDT->getTimestamp() >= $xdate)) && ($this->regionNick != "ufa" || $this->payTypeSysNick == "oms")) {
				if ($this->regionNick == "kareliya") {
					// проверяем есть ли специальность у врача
					$query = "
						select fms.MedSpec_Code as \"MedSpec_Code\"
						from
							v_MedStaffFact msf
							left join v_MedSpecOms mso on mso.MedSpecOms_id = msf.MedSpecOms_id
							left join fed.v_MedSpec fms on fms.MedSpec_id = mso.MedSpec_id
						where msf.MedStaffFact_id = :MedStaffFact_id
						limit 1
					";
					$resp = $this->queryResult($query, ["MedStaffFact_id" => $this->MedStaffFact_id]);
					if (empty($resp[0]["MedSpec_Code"])) {
						throw new Exception("Не указана специальность врача на выбранном месте работы. Невозможно определить \"Вид мед. помощи\".", 400);
					}
				}
				throw new Exception("Поле \"Вид мед. помощи\" обязательно для заполнения", 500);
			}
		}
		if ($this->regionNick == "perm" && $this->evnClassId == 11) {
			// проверяем наличие посещений в с типом оплаты ОМС в закрытом талоне с фед. исходом "313. Констатация смерти"
			$query = "
				select EvnPL_id as \"EvnPL_id\"
				from
					v_EvnPL epl
					inner join fed.v_LeaveType lt on lt.LeaveType_id = epl.LeaveType_fedid
				where epl.EvnPL_id = :EvnPL_id
				  and lt.LeaveType_Code = '313'
				  and epl.EvnPL_disDT >= '2015-01-01'
				limit 1
			";
			$EvnPL_id = $this->getFirstResultFromQuery($query, ["EvnPL_id" => $this->pid]);
			if (!empty($EvnPL_id)) {
				$query = "
					select evpl.EvnVizitPL_id as \"EvnVizitPL_id\"
					from
						v_EvnVizitPL evpl
						inner join v_PayType pt on pt.PayType_id = evpl.PayType_id
					where evpl.EvnVizitPL_pid = :EvnVizitPL_pid
					  and evpl.EvnVizitPL_id != coalesce(CAST(:EvnVizitPL_id as bigint), 0)
					  and pt.PayType_SysNick = 'oms'
					union
					select PayType_id as \"EvnVizitPL_id\"
					from v_PayType
					where PayType_id = :PayType_id
					  and PayType_SysNick = 'oms'
					limit 1
				";
				$queryParams = [
					"EvnVizitPL_id" => (!empty($this->id) ? $this->id : null),
					"EvnVizitPL_pid" => $this->pid,
					"PayType_id" => $this->PayType_id
				];
				$result = $this->db->query($query, $queryParams);
				if (!is_object($result)) {
					throw new Exception("Ошибка проверки видов оплаты посещений.", 400);
				}
				$resp = $result->result("array");
				if (!empty($resp[0]["EvnVizitPL_id"])) {
					throw new Exception("Случаи с исходом \"313 Констатация факта смерти в поликлинике\" не подлежат оплате по ОМС. Для сохранения измените вид оплаты.", 400);
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
		
		if ($this->evnClassId == 11 && empty($this->diag_id) && in_array($this->scenario, [self::SCENARIO_DO_SAVE]) && $this->parent->IsFinish == 2) {
			// Проверка на наличие хотя бы одного основного диагноза во всех посещениях ТАП
			// @task https://redmine.swan.perm.ru/issues/84915
			$query = "
				select Diag_id as \"Diag_id\"
				from v_EvnVizitPL
				where EvnVizitPL_pid = :EvnVizitPL_pid
				  and EvnVizitPL_id <> coalesce(CAST(:EvnVizitPL_id as bigint), 0)
			";
			$queryParams = [
				"EvnVizitPL_id" => (!empty($this->id) ? $this->id : null),
				"EvnVizitPL_pid" => $this->pid
			];
			$result = $this->db->query($query, $queryParams);
			if (!is_object($result)) {
				throw new Exception("Ошибка проверки заполнения основных диагнозов в законченном случае лечения.", 400);
			}
			$resp = $result->result("array");
			$diag_exists = false;
			foreach ($resp as $row) {
				if (!empty($row["Diag_id"])) {
					$diag_exists = true;
					break;
				}
			}
			if (!$diag_exists) {
				throw new Exception("Законченный случай лечения должен иметь хотя бы один основной диагноз.", 400);
			}
		}
		if (in_array($this->evnClassId, [11, 13]) && $this->isNewRecord && $this->parent->hasEvnVizitInReg()) {
			$paidField = (in_array($this->regionNick, ["pskov", "ufa", "vologda"])) ? "Paid_id" : "RegistryData_IsPaid";
			$registryStatusExceptions = [4];
			if ($this->regionNick == "kareliya" || $this->regionNick == "penza") {
				$registryStatusExceptions[] = 3;
			}
			// проверяем наличие посещений в реестре по ->pid
			$registryStatusExceptionsString = implode(",", $registryStatusExceptions);
			$query = "
				select E.Evn_id as \"Evn_id\"
				from
					v_Evn E
					left join v_RegistryData RD on RD.Evn_id = E.Evn_id
					left join v_Registry R on RD.Registry_id = R.Registry_id
				where RD.Evn_id is not null
				  and E.Evn_setDT >= '2014-12-01'
				  and (R.RegistryStatus_id not in ({$registryStatusExceptionsString}) or (R.RegistryStatus_id = 4 and RD.{$paidField} = 2))
				  and E.Evn_pid = :Evn_pid
				limit 1
			";
			/**@var CI_DB_result $result */
			$dbreg = $this->load->database("registry", true);
			$result = $dbreg->query($query, ["Evn_pid" => $this->pid]);
			if (!is_object($result)) {
				throw new Exception("Ошибка проверки оплаченности посещений.", 400);
			}
			$resp = $result->result("array");
			if (!empty($resp[0]["Evn_id"])) {
				throw new Exception("Добавление нового посещения невозможно, т.к. есть посещения, входящие в реестр.", 400);
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
						select
							vizit.EvnVizitPl_id as \"EvnVizitPl_id\"
						from
							v_EvnVizitPl vizit
							inner join v_UslugaComplexAttribute uca on uca.UslugaComplex_id = vizit.UslugaComplex_id
							inner join v_UslugaComplexAttributeType ucat on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
								and ucat.UslugaComplexAttributeType_SysNick = 'vizit'
						where
							vizit.EvnVizitPl_pid = :EvnVizitPl_pid
							{$filter}
						limit 1
					", $params);
				/*
				if ($EvnVizitPl_id > 0) {
					throw new Exception('В случаях по посещениям к врачам не может быть заведено больше одного посещения!', 400);
				}
				*/

				if ($this->lpuSectionData['LpuSectionProfile_Code'] == '160' && $this->scenario == self::SCENARIO_AUTO_CREATE) {
					if ( !empty($this->Diag_id) ) {
						$resp_diag = $this->getFirstResultFromQuery("
							select Diag_Code as \"Diag_Code\" from v_Diag where Diag_id = :Diag_id ",
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
				v_TimeTableGraf vttg
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
					v_EvnPL vep
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
			$params = ["pmUser_id" => $this->promedUserId];
			$params["EvnPrescr_id"] = $this->EvnPrescr_id;
			$this->execCommonSP("p_EvnPrescr_exec", $params);
		}
	}

	/**
	 * Логика перед валидацией
	 * @throws Exception
	 */
	protected function _beforeValidate()
	{
		parent::_beforeValidate();
		// это должно отработать до валидации
		if ($this->regionNick == "ekb" && $this->scenario == self::SCENARIO_AUTO_CREATE && in_array($this->evnClassId, [11, 13])) {
			$query = "
				select
					msf.LpuSectionProfile_id as \"LpuSectionProfile_id\",
					Staff.PayType_id as \"PayType_id\",
					pt.PayType_SysNick as \"PayType_SysNick\"
				from
					v_MedStaffFact msf
					inner join persis.WorkPlace wp on wp.id = msf.MedStaffFact_id
					inner join persis.Staff on Staff.id = wp.Staff_id
					left join v_PayType pt on pt.PayType_id = Staff.PayType_id
				where msf.MedStaffFact_id = :MedStaffFact_id
				limit 1
			";
			$msf = $this->getFirstRowFromQuery($query, ["MedStaffFact_id" => $this->MedStaffFact_id]);
			if (!empty($msf["PayType_id"])) {
				$this->setAttribute("paytype_id", $msf["PayType_id"]);
				$this->PayType_id = $msf["PayType_id"];
				$this->payTypeSysNick = $msf["PayType_SysNick"];
			}
			if ($this->evnClassId == 11) {
				if (!empty($msf["LpuSectionProfile_id"])) {
					$this->setAttribute("lpusectionprofile_id", $msf["LpuSectionProfile_id"]);
				} else if (isset($this->lpuSectionData["LpuSectionProfile_Code"]) && $this->lpuSectionData["LpuSectionProfile_Code"] > 0) {
					$this->setAttribute("lpusectionprofile_id", $this->lpuSectionData["LpuSectionProfile_id"]);
				}
			}
		}
		if ($this->regionNick != "ekb" && $this->scenario == self::SCENARIO_AUTO_CREATE && 11 == $this->evnClassId) {
			if ($this->getRegionNick() == "vologda" && !empty($this->parent->evnVizitList) && is_array($this->parent->evnVizitList)) {
				$arr = array_filter($this->parent->evnVizitList, function ($vizit) {
					return (!empty($vizit["EvnVizitPL_id"]));
				});
				usort($arr, function ($a, $b) {
					return ($a["EvnVizitPL_setDT"] > $b["EvnVizitPL_setDT"]) ? -1 : 1;
				});
				$lastVisit = reset($arr);
				$this->load->model("LpuStructure_model");
				//полученим профили отделения (основной и дополнительные) из рабочего места текущего пользователя.
				$lpuSectionLpuSectionProfile = $this->LpuStructure_model->getLpuStructureProfileAll(["LpuSection_id" => $this->LpuSection_id]);
				$userProfileID = [];
				if (!empty($lastVisit["LpuSectionProfile_id"]) && $lpuSectionLpuSectionProfile && is_array($lpuSectionLpuSectionProfile) && count($lpuSectionLpuSectionProfile) > 0) {
					foreach ($lpuSectionLpuSectionProfile as $row) {
						if (!empty($row["LpuSectionProfile_id"])) $userProfileID[] = $row["LpuSectionProfile_id"];
					}
					// если у пользователя существует профиль предыдущего
					if (in_array($lastVisit["LpuSectionProfile_id"], $userProfileID)) {
						$this->setAttribute("lpusectionprofile_id", $lastVisit["LpuSectionProfile_id"]);
					}
				}
			}

			if (empty($this->LpuSectionProfile_id) && isset($this->lpuSectionData["LpuSectionProfile_Code"]) && $this->lpuSectionData["LpuSectionProfile_Code"] > 0) {
				$this->setAttribute("lpusectionprofile_id", $this->lpuSectionData["LpuSectionProfile_id"]);
			}
		}
		$this->_setEvnVizitPLDoubles();
	}

	/**
	 * Простановка признака дубля
	 * @throws Exception
	 */
	function _setEvnVizitPLDoubles()
	{
		if (getRegionNick() == "perm" && in_array($this->evnClassId, [11, 13]) && $this->parent->IsFinish == 2 && $this->scenario != self::SCENARIO_DELETE) {
			// 1. ищем дубли до сохранения, если их не более двух, то дублирование надо будет снять
			$resp_double = $this->_getEvnVizitPLOldDoubles(["EvnVizitPL_id" => $this->id]);
			$existsVizitPLDoubleYes = false;
			$oldDoubles = [];
			foreach ($resp_double as $one_double) {
				$oldDoubles[$one_double["EvnVizitPL_id"]] = $one_double;
				if ($one_double["VizitPLDouble_id"] == 1) {
					$existsVizitPLDoubleYes = true;
				}
			}
			$isSverhPodush = false;
			if ($this->setDT >= date_create('2018-01-01')) {
				$query = "
					select count(*) as \"cnt\"
					from
						v_AttributeVision avis
						inner join v_VolumeType vt on vt.VolumeType_id = avis.AttributeVision_TablePKey
						inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
						inner join v_Attribute a on a.Attribute_id = av.Attribute_id
						inner join v_UslugaComplex uc on uc.UslugaComplex_id = av.AttributeValue_ValueIdent
					where vt.VolumeType_Code = '2018-01СверхПодуш'
					  and avis.AttributeVision_TableName = 'dbo.VolumeType'
					  and avis.AttributeVision_IsKeyValue = 2
					  and av.AttributeValue_ValueIdent = :UslugaComplex_id
					  and av.AttributeValue_begDate <= :EvnVizitPL_setDate
					  and (av.AttributeValue_endDate is null or av.AttributeValue_endDate > :EvnVizitPL_setDate)
				";
				$params = [
					"UslugaComplex_id" => $this->UslugaComplex_id,
					"EvnVizitPL_setDate" => $this->setDate
				];
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
				foreach ($this->_doubles as $double) {
					if ($double["isSverhPodush"]) {
						continue;
					}
					if ($double["EvnVizitPL_pid"] != $this->pid) {
						$IsOtherDouble = true;
					} else {
						$IsThisDouble = true;
					}
				}
				if ($IsOtherDouble && !$isSverhPodush) {
					$this->setAttribute("isotherdouble", 2);
				} else {
					$this->setAttribute("isotherdouble", 1);
				}
			} else {
				$this->setAttribute("isotherdouble", 1);
			}
			if (!$IsThisDouble) {
				$this->setAttribute("vizitpldouble_id", null);
			}
			// 3. снимаем/ставим признаки дубля
			foreach ($this->_doubles as $double) {
				if ($isSverhPodush) {
					continue;
				}
				if (!empty($oldDoubles[$double["EvnVizitPL_id"]])) {
					unset($oldDoubles[$double["EvnVizitPL_id"]]);
				}
				if ($IsOtherDouble && !$double["isSverhPodush"]) {
					$this->db->query("update EvnVizitPL set EvnVizitPL_IsOtherDouble = 2 where Evn_id = :EvnVizitPL_id", ["EvnVizitPL_id" => $double["EvnVizitPL_id"]]);
				} else {
					$this->db->query("update EvnVizitPL set EvnVizitPL_IsOtherDouble = 1 where Evn_id = :EvnVizitPL_id", ["EvnVizitPL_id" => $double["EvnVizitPL_id"]]);
				}
			}
			if (count($oldDoubles) == 1) {
				// снимаем признак, только если он один остался
				foreach ($oldDoubles as $double) {
					$this->db->query("update EvnVizitPL set EvnVizitPL_IsOtherDouble = 1, VizitPLDouble_id = null where Evn_id = :EvnVizitPL_id", ["EvnVizitPL_id" => $double["EvnVizitPL_id"]]);
				}
			} elseif (count($oldDoubles) > 1) {
				// Если у посещения было указано «Выгружать в реестр» «Да» и в группе нет других посещений, у которых указано «Да»,
				// то у остальных посещений изменяется значение «Выгружать в реестр» на »/«Не определено»
				if (!empty($this->_savedData["vizitpldouble_id"]) && $this->_savedData["vizitpldouble_id"] == 1 && !$existsVizitPLDoubleYes) {
					foreach ($oldDoubles as $double) {
						$this->db->query("update EvnVizitPL set VizitPLDouble_id = 3 where Evn_id = :EvnVizitPL_id", ["EvnVizitPL_id" => $double["EvnVizitPL_id"]]);
					}
				}
			}
		}
		//https://redmine.swan.perm.ru/issues/116299
		$isAllowedCode = function ($code) {
			return (
				in_array(substr($code, 1, 2), [74, 75, 76, 77]) ||
				in_array(substr($code, 3, 3), [874, 875, 822, 832, 833, 838, 839, 840, 862, 873, 857]) ||
				in_array(substr($code, 1, 5), [22895, 13896, 13897, 22905, 22906]) ||
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
			&& in_array(getRegionNick(), ["perm", "ufa"])
			&& in_array($this->evnClassId, [11, 13])
			&& (getRegionNick() != 'perm' || $this->IsOtherDouble == 2)
		) {
			// 1. ищем дубли до удаления, если их не более двух, то дублирование надо будет снять
			$resp_double = $this->_getEvnVizitPLOldDoubles(["EvnVizitPL_id" => $this->id]);
			$oldDoubles = [];
			foreach ($resp_double as $one_double) {
				$oldDoubles[$one_double["EvnVizitPL_id"]] = $one_double;
			}
			$this->setAttribute("isotherdouble", 1);
			$this->setAttribute("vizitpldouble_id", null);
			if (count($oldDoubles) == 1) {
				foreach ($oldDoubles as $double) {
					$this->db->query("update EvnVizitPL set EvnVizitPL_IsOtherDouble = 1, VizitPLDouble_id = null where Evn_id = :EvnVizitPL_id", ["EvnVizitPL_id" => $double["EvnVizitPL_id"]]);
				}
			}
		}
		if (getRegionNick() == "ufa" && in_array($this->evnClassId, [11, 13]) && $this->scenario != self::SCENARIO_DELETE) {
			// 1. ищем дубли до сохранения, если их не более двух, то дублирование надо будет снять
			$resp_double = $this->_getEvnVizitPLOldDoubles(["EvnVizitPL_id" => $this->id]);
			$existsVizitPLDoubleYes = false;
			$oldDoubles = [];
			foreach ($resp_double as $one_double) {
				$oldDoubles[$one_double["EvnVizitPL_id"]] = $one_double;
				if ($one_double["VizitPLDouble_id"] == 1) {
					$existsVizitPLDoubleYes = true;
				}
			}
			// 2. ищем новые дубли
			if (!$isAllowedCode($this->vizitCode)) {
				$this->_doubles = $this->_getEvnVizitPLDoubles();
			}
			$IsOtherDouble = false;
			if (count($this->_doubles) > 0) {
				foreach ($this->_doubles as $double) {
					if (!$isAllowedCode($double["UslugaComplex_Code"])) {
						if ($double["EvnVizitPL_pid"] != $this->pid) {
							$IsOtherDouble = true;
						}
					}
				}
				if ($IsOtherDouble) {
					$this->setAttribute("isotherdouble", 2);
				} else {
					$this->setAttribute("isotherdouble", 1);
				}
			} else {
				$this->setAttribute("isotherdouble", 1);
			}
			// 3. снимаем/ставим признаки дубля
			foreach ($this->_doubles as $key => $double) {
				if (!empty($oldDoubles[$double["EvnVizitPL_id"]])) {
					unset($oldDoubles[$double["EvnVizitPL_id"]]);
				}
				if (!$isAllowedCode($double["UslugaComplex_Code"])) {
					if ($IsOtherDouble) {
						$this->db->query("update EvnVizitPL set EvnVizitPL_IsOtherDouble = 2, VizitPLDouble_id = 1 where Evn_id = :EvnVizitPL_id", ["EvnVizitPL_id" => $double["EvnVizitPL_id"]]);
					} else {
						$this->db->query("update EvnVizitPL set EvnVizitPL_IsOtherDouble = 1, VizitPLDouble_id = 1 where Evn_id = :EvnVizitPL_id", ["EvnVizitPL_id" => $double["EvnVizitPL_id"]]);
					}
				} else {
					// эти могут дублироваться, снимаем с них признак
					unset($this->_doubles[$key]);
					$this->db->query("update EvnVizitPL set EvnVizitPL_IsOtherDouble = 1, VizitPLDouble_id = null where Evn_id = :EvnVizitPL_id", ["EvnVizitPL_id" => $double["EvnVizitPL_id"]]);
				}
			}
			if (count($this->_doubles) > 0) {
				$this->setAttribute("vizitpldouble_id", 1);
			} else {
				$this->setAttribute("vizitpldouble_id", null);
			}
			if (count($oldDoubles) == 1) { // снимаем признак, только если он один остался
				foreach ($oldDoubles as $double) {
					$this->db->query("update EvnVizitPL set EvnVizitPL_IsOtherDouble = 1, VizitPLDouble_id = null where Evn_id = :EvnVizitPL_id", ["EvnVizitPL_id" => $double["EvnVizitPL_id"]]);
				}
			} else if (count($oldDoubles) > 1) {
				// Если у посещения было указано «Выгружать в реестр» «Да» и в группе нет других посещений, у которых указано «Да»,
				// то у остальных посещений изменяется значение «Выгружать в реестр» на »/«Не определено»
				if (!empty($this->_savedData["vizitpldouble_id"]) && $this->_savedData["vizitpldouble_id"] == 1 && !$existsVizitPLDoubleYes) {
					$VizitPLDouble_id = 3;
					if (!$isAllowedCode($double['UslugaComplex_Code'])) {
						$VizitPLDouble_id = null;
					}
					foreach ($oldDoubles as $double) {
						$this->db->query("update EvnVizitPL set VizitPLDouble_id = :VizitPLDouble_id where Evn_id = :EvnVizitPL_id", [
							"EvnVizitPL_id" => $double["EvnVizitPL_id"],
							"VizitPLDouble_id" => $VizitPLDouble_id,
						]);
					}
				}
			}
		}
	}
	#endregion common
	#region update
	/**
	 * Обновление MedicalCareKind в ТАПе.
	 * @param $data
	 */
	function _updateMedicalCareKind($data)
	{
		if ($this->regionNick == "kareliya") {
			// Обновляем MedicalCareKind в зависимости от последнего посещения по данному тап.
			$query = "
				update EvnPL
				set MedicalCareKind_id = mck.MedicalCareKind_id
				from
					EvnPL e
					inner join v_EvnPL epl on epl.EvnPL_id = e.Evn_id
					inner join v_EvnClass ec on ec.EvnClass_id = epl.EvnClass_id
					left join lateral(
						select epl.LpuSection_id
						from v_EvnVizitPL epl
						where epl.EvnVizitPL_pid = e.Evn_id
						order by epl.EvnVizitPL_setDate desc
						limit 1
					) as EVIZPL on true
					left join v_LpuSection ls on ls.LpuSection_id = evizpl.LpuSection_id
					inner join v_MedicalCareKind mck on mck.MedicalCareKind_Code = (case when ec.EvnClass_SysNick = 'EvnPLStom' then 9 when ls.LpuSectionProfile_Code = '57' then 8 else 1 end)
				where e.Evn_id = :EvnPL_id
			";
			$this->db->query($query, $data);
		}
	}

	/**
	 * @param $id
	 * @param null $value
	 * @return array
	 * @throws Exception
	 */
	function updateEvnVizitPLIsZNO($id, $value = null)
	{
		if (getRegionNick() == "ekb") {
			//обновить связку IsZNO - IsZNORemove
			$query = "
				select EvnVizitPL_IsZNO as \"EvnVizitPL_IsZNO\"
				from v_EvnVizitPL
				where EvnVizitPL_id = :id
				limit 1
			";
			$iszno_last = $this->getFirstResultFromQuery($query, ["id" => $id]);
			$result = $this->_updateAttribute($id, "iszno", $value);
			if ($result and empty($result["Error_Msg"])) {
				$znoremove = (($value != "2" and $iszno_last == "2") ? "2" : "1");
				$this->_updateAttribute($id, "isznoremove", $znoremove);
			}
		} else {
			$result = $this->_updateAttribute($id, "iszno", $value);
		}
		if ($value != 2) {
			$this->updateDiagSpid($id, null);
		}
		return $result;
	}

	/**
	 * @param $id
	 * @param null $value
	 * @return array
	 * @throws Exception
	 */
	function updateEvnVizitPLBiopsyDate($id, $value = null)
	{
		return $this->_updateAttribute($id, "biopsydate", $value);
	}

	/**
	 * @param $id
	 * @param null $value
	 * @return array
	 * @throws Exception
	 */
	function updateDiagSpid($id, $value = null)
	{
		return $this->_updateAttribute($id, "diag_spid", $value);
	}

	/**
	 * @param $id
	 * @param null $value
	 * @return array|bool
	 * @throws Exception
	 */
	function updateDiagnewId($id, $value = null)
	{
		// приходят сразу два атрибута (диагноз + характер заболевания)
		$values = explode(":", $value);
		if (count($values) == 2) {
			if (empty($values[0]) || $values[0] == "null") {
				$values[0] = null;
			}
			if (empty($values[1]) || $values[1] == "null") {
				$values[1] = null;
			}

			$this->_updateAttribute($id, "deseasetype_id", $values[1]);
			return $this->_updateAttribute($id, "diag_id", $values[0]);
		}
		return false;
	}

	/**
	 * @param $id
	 * @param null $value
	 * @return array
	 * @throws Exception
	 */
	function updateDeseaseTypeId($id, $value = null)
	{
		return $this->_updateAttribute($id, "deseasetype_id", $value);
	}

	/**
	 * @param $id
	 * @param null $value
	 * @return array
	 * @throws Exception
	 */
	function updateTumorStageId($id, $value = null)
	{
		return $this->_updateAttribute($id, "tumorstage_id", $value);
	}

	/**
	 * @param $id
	 * @param null $value
	 * @return array
	 * @throws Exception
	 */
	function updateHealthKindId($id, $value = null)
	{
		return $this->_updateAttribute($id, "healthkind_id", $value);
	}

	/**
	 * @param $id
	 * @param null $value
	 * @return array
	 * @throws Exception
	 */
	function updatePainIntensityId($id, $value = null)
	{
		return $this->_updateAttribute($id, "painintensity_id", $value);
	}

	/**
	 * @param $id
	 * @param null $value
	 * @return array
	 * @throws Exception
	 */
	function updateRankinScaleId($id, $value = null)
	{
		return $this->_updateAttribute($id, "rankinscale_id", $value);
	}

	/**
	 * @param $id
	 * @param null $value
	 * @return array
	 * @throws Exception
	 */
	function updateVizitTypeId($id, $value = null)
	{
		return $this->_updateAttribute($id, "vizittype_id", $value);
	}

	/**
	 * @param $id
	 * @param null $value
	 * @return array
	 * @throws Exception
	 */
	function updateVizitClassId($id, $value = null)
	{
		return $this->_updateAttribute($id, "vizitclass_id", $value);
	}

	/**
	 * @param $id
	 * @param null $value
	 * @return array
	 * @throws Exception
	 */
	function updateRiskLevelId($id, $value = null)
	{
		return $this->_updateAttribute($id, "risklevel_id", $value);
	}

	/**
	 * @param $id
	 * @param null $value
	 * @return array
	 * @throws Exception
	 */
	function updateWellnessCenterAgeGroupsId($id, $value = null)
	{
		return $this->_updateAttribute($id, "wellnesscenteragegroups_id", $value);
	}

	/**
	 * @param $id
	 * @param null $value
	 * @return array
	 * @throws Exception
	 */
	function updateProfGoalId($id, $value = null)
	{
		return $this->_updateAttribute($id, "profgoal_id", $value);
	}

	/**
	 * @param $id
	 * @param null $value
	 * @return array
	 * @throws Exception
	 */
	function updateDispProfGoalTypeId($id, $value = null)
	{
		return $this->_updateAttribute($id, "dispprofgoaltype_id", $value);
	}

	/**
	 * @param $id
	 * @param null $value
	 * @return array
	 * @throws Exception
	 */
	function updateMedStaffFactId($id, $value = null)
	{
		$query = "
			select MedPersonal_id as \"MedPersonal_id\"
			from v_MedStaffFact
			where MedStaffFact_id = :MedStaffFact_id
			limit 1
		";
		$MedPersonal_id = $this->getFirstResultFromQuery($query, ["MedStaffFact_id" => $value]);
		$this->_updateAttribute($id, "medpersonal_id", $MedPersonal_id);
		return $this->_updateAttribute($id, "medstafffact_id", $value);
	}

	/**
	 * @param $id
	 * @param null $value
	 * @return array
	 * @throws Exception
	 */
	function updateMedPersonalSid($id, $value = null)
	{
		return $this->_updateAttribute($id, "medpersonal_sid", $value);
	}

	/**
	 * @param $id
	 * @param null $value
	 * @return array
	 * @throws Exception
	 */
	function updateTreatmentClassId($id, $value = null)
	{
		return $this->_updateAttribute($id, "treatmentclass_id", $value);
	}

	/**
	 * @param $id
	 * @param null $value
	 * @return array
	 * @throws Exception
	 */
	function updateServiceTypeId($id, $value = null)
	{
		return $this->_updateAttribute($id, "servicetype_id", $value);
	}

	/**
	 * @param $id
	 * @param null $value
	 * @return array
	 * @throws Exception
	 */
	function updateUslugaComplexId($id, $value = null)
	{
		return $this->_updateAttribute($id, "uslugacomplex_id", $value);
	}

	/**
	 * @param $id
	 * @param null $value
	 * @return array
	 * @throws Exception
	 */
	function updateMedicalCareKindId($id, $value = null)
	{
		return $this->_updateAttribute($id, "medicalcarekind_id", $value);
	}

	/**
	 * @param $id
	 * @param null $value
	 * @return array
	 * @throws Exception
	 */
	function updateLpuSectionProfileId($id, $value = null)
	{
		return $this->_updateAttribute($id, "lpusectionprofile_id", $value);
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
	function saveEvnLinkAPP($id, $valueColumn, $value = null)
	{
		if (getRegionNick() == 'kz') {
			$EvnLinkAPP = $this->getFirstRowFromQuery("
				select 
					EvnLinkAPP_id as \"EvnLinkAPP_id\",
					PayTypeKAZ_id as \"PayTypeKAZ_id\",
					VizitActiveType_id as \"VizitActiveType_id\" 
				from r101.EvnLinkAPP 
				where Evn_id = ?
			", [$id]);

			$proc = !$EvnLinkAPP['EvnLinkAPP_id'] ? 'r101.p_EvnLinkAPP_ins' : 'r101.p_EvnLinkAPP_upd';

			$otherColumn = ($valueColumn == 'PayTypeKAZ_id')?'VizitActiveType_id':'PayTypeKAZ_id';

			if ($value != null || !empty($EvnLinkAPP[$otherColumn])) {
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

		$EvnLinkAPP_id = $this->getFirstResultFromQuery("select EvnLinkAPP_id from r101.EvnLinkAPP where Evn_id = ?", [$id]);

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
	 * @param $id
	 * @param null $value
	 * @return mixed
	 * @throws Exception
	 */
	function updatePregnancyEvnVizitPLPeriod($id, $value = null)
	{
		if (!empty($value) && $value < 1 && $value > 45) {
			throw new Exception("Срок беременности должен быть от 1 до 45 недель");
		}
		$this->load->model("PregnancyEvnVizitPL_model");
		$funcParams = [
			"PregnancyEvnVizitPL_Period" => $value,
			"EvnVizitPL_id" => $id,
			"pmUser_id" => $this->promedUserId
		];
		return $this->PregnancyEvnVizitPL_model->savePregnancyEvnVizitPLData($funcParams);
	}

	/**
	 * Редактирование посещения для мобильного ФАП
	 * @param $input_data
	 * @return array
	 * @throws Exception
	 */
	function updateEvnVizitPL($input_data)
	{
		// только если существует EvnVizitPL_id
		if (empty($input_data['EvnVizitPL_id'])) {
			throw new Exception("Не указан EvnVizitPL_id");
		}
		$sessionParams = getSessionParams();
		// получаем данные посещения
		$this->applyData([
			"EvnVizitPL_id" => $input_data["EvnVizitPL_id"],
			"session" => $sessionParams["session"]
		]);
		// если пришло рабочее место врача, определяем его отделение и врача
		if (!empty($data["MedStaffFact_id"])) {
			$data["LpuSection_id"] = $this->getFirstResultFromQuery("select LpuSection_id as \"LpuSection_id\" from v_MedStaffFact where MedStaffFact_id = :MedStaffFact_id limit 1", ["MedStaffFact_id" => $data["MedStaffFact_id"]]);
			$data["MedPersonal_id"] = $this->getFirstResultFromQuery("select MedPersonal_id as \"MedPersonal_id\" from v_MedStaffFact where MedStaffFact_id = :MedStaffFact_id limit 1", ["MedStaffFact_id" => $data["MedStaffFact_id"]]);
		}
		// если пришло рабочее место сред. мед. перса, определяем его
		if (!empty($data["MedStaffFact_sid"])) {
			$data["MedPersonal_sid"] = $this->getFirstResultFromQuery("select MedPersonal_id as \"MedPersonal_id\" from v_MedStaffFact where MedStaffFact_id = :MedStaffFact_sid limit 1", ["MedStaffFact_sid" => $data["MedStaffFact_sid"]]);
		}
		// конвертируем некоторые пришедшие поля, в поля хранимой процедуры
		// хотя, почему бы сразу нельзя было назвать нормально???
		// так же уберем те поля которых нет в модели
		$input_data = $this->convertAliasesToStoredProcedureParams($input_data);
		// если параметр есть, устанавливаем его как значение атрибута модели
		foreach ($input_data as $key => $val) {
			$this->setAttribute(strtolower($key), $val);
		}
		// сохраняем бирку б/з
		$this->_saveVizitFactTime();
		// сохраняем посещение
		$resp = $this->_save();
		return $resp;
	}

	/**
	 * @param $id
	 * @param null $UslugaMedType_id
	 * @return mixed
	 * @throws Exception
	 */
	public function updateUslugaMedTypeId($id, $UslugaMedType_id = null)
	{
		if (getRegionNick() === "kz") {
			$this->load->model("UslugaMedType_model");
			return $this->UslugaMedType_model->saveUslugaMedTypeLink([
				"UslugaMedType_id" => $UslugaMedType_id,
				"Evn_id" => $id,
				"pmUser_id" => $this->promedUserId
			]);
		}
		return [];
	}

	public function getOne($params) {
		$query = "select
				EvnVizitPL_id as \"EvnVizitPL_id\",
				EvnVizitPL_setDT as \"EvnVizitPL_setDT\",
				EvnVizitPL_pid as \"EvnVizitPL_pid\",
				EvnVizitPL_rid as \"EvnVizitPL_rid\",
				Lpu_id as \"Lpu_id\",
				Server_id as \"Server_id\",
				Person_id as \"Person_id\",
				LpuSection_id as \"LpuSection_id\",
				MedPersonal_id as \"MedPersonal_id\",
				EvnDirection_id as \"EvnDirection_id\",
				Diag_id as \"Diag_id\"
			from dbo.v_EvnVizitPL
			where EvnVizitPL_id = :EvnVizitPL_id
    	";
		return $this->getFirstRowFromQuery($query, $params);
	}

    public function tableName()
    {
        return parent::tableName();
    }
	#endregion update
}

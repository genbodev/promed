<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * QcControlMaterialValue_model - модель для работы с формой "Контрольные материалы" (swQcControlMaterialValueWindow)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @author       Magafurov Salavat
 * @version      01.07.2019
 */

require_once('Scenario_model.php');
class QcControlMaterialValue_model extends Scenario_model {
	var $table_name = 'QcControlMaterialValue';
	var $scheme = 'lis';
	var $saveAsNewObject = true;

	/**
	 * Сценарий установки расчитанных Xcp (среднее) и S (среднеквадратичное отклонение)
	 */
	const SCENARIO_GET_MAXVALUES = 'getMaxValues';

	/**
	 * Загрузка сводного грида
	 */
	const SCENARIO_LOAD_SVOD_GRID = 'loadSvodGrid';

	/**
	 * Загрузка услуг
	 */
	const SCENARIO_LOAD_USLUGACOMPLEX = 'loadUslugaComplex';

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		$this->_setScenarioList(array(
			self::SCENARIO_DO_SAVE,
			self::SCENARIO_LOAD_GRID,
			self::SCENARIO_DISABLE,
			self::SCENARIO_GET_MAXVALUES,
			self::SCENARIO_LOAD_COMBO_BOX,
			self::SCENARIO_LOAD_SVOD_GRID,
			self::SCENARIO_LOAD_USLUGACOMPLEX
		));
	}

	/**
	 * Возвращает массив описаний всех используемых атрибутов объекта в формате ключ => описание
	 * @return array
	 */
	static function defAttributes() {
		return array(
			self::ID_KEY => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME
				),
				'alias' => 'QcControlMaterialValue_id',
				'label' => 'Идентификатор',
				'save' => 'trim',
				'type' => 'id'
			),
			'qccontrolmaterial_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'QcControlMaterial_id',
				'label' => 'Материал',
				'save' => 'trim', //todo validate
				'type' => 'string'
			),
			'uslugacomplex_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'UslugaComplex_id',
				'label' => 'Код теста',
				'save' => '',
				'type' => 'id'
			),
			'analyzertest_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'AnalyzerTest_id',
				'label' => 'Методика КМ',
				'save' => '',
				'type' => 'id'
			),
			'qccontrolmaterialvalue_x' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'QcControlMaterialValue_X',
				'label' => 'Среднее Xcp',
				'save' => '',
				'type' => 'float'
			),
			'qccontrolmaterialvalue_s' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'QcControlMaterialValue_S',
				'label' => 'Отклонение Scp',
				'save' => '',
				'type' => 'float'
			),
			'medservice_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'MedService_id',
				'label' => 'Служба',
				'save' => '',
				'type' => 'id'
			),
			'analyzer_id' => array(
				'properties' => array(
					self::PROPERTY_NOT_SAFE,
					self::PROPERTY_NOT_LOAD
				),
				'alias' => 'Analyzer_id',
				'label' => 'Анализатор',
				'save' => '',
				'type' => 'id'
			),
			'qccontrolmaterialvalue_cv10' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'QcControlMaterialValue_CV10',
				'label' => 'CV10',
				'save' => '',
				'type' => 'float'
			),
			'qccontrolmaterialvalue_cv20' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'QcControlMaterialValue_CV20',
				'label' => 'CV20',
				'save' => '',
				'type' => 'float'
			),
			'qccontrolmaterialvalue_b10' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'QcControlMaterialValue_B10',
				'label' => 'B10',
				'save' => '',
				'type' => 'float'
			),
			'qccontrolmaterialvalue_b20' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'QcControlMaterialValue_B20',
				'label' => 'B20',
				'save' => '',
				'type' => 'float'
			),
			'begdt' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
					self::PROPERTY_DATE_TIME,
					self::PROPERTY_NEED_TABLE_NAME
				),
				'alias' => 'QcControlMaterialValue_begDT',
				'label' => 'Дата начала действия записи',
				'save' => '',
				'type' => 'date'
			),
			'enddt' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
					self::PROPERTY_DATE_TIME,
					self::PROPERTY_NEED_TABLE_NAME
				),
				'alias' => 'QcControlMaterialValue_endDT',
				'label' => 'Дата начала действия записи',
				'save' => '',
				'type' => 'date'
			)
		);
	}

	/**
	 * Получение правил для входящих параметров
	 * @param $name
	 * @return array
	 */
	function getInputRules($name) {
		$rules = parent::getInputRules($name);

		switch($name) {
			case self::SCENARIO_LOAD_GRID:
			case self::SCENARIO_LOAD_COMBO_BOX:
				$rules = $this->getInputRulesByAttributes(['qccontrolmaterial_id', 'medservice_id', 'analyzer_id']);
				break;
			case self::SCENARIO_GET_MAXVALUES:
				$rules = $this->getInputRulesByAttributes('uslugacomplex_id');
				break;
			case self::SCENARIO_LOAD_SVOD_GRID:
			case self::SCENARIO_LOAD_USLUGACOMPLEX:
				$rules = $this->getInputRulesByAttributes('analyzer_id');
				break;
		}

		return $rules;
	}

	/**
	 * Загрузка формы и грида (Контроль качества/Контрольные материалы)
	 * @param array $data
	 * @return array
	 * @throws Exception
	 */
	function doLoadData($data = array()) {

		$params = [];
		$params['Lpu_id'] = $this->getSessionParams()['lpu_id'];
		$where = '';

		if(!empty($data['QcControlMaterialValue_id'])) {
			$params['QcControlMaterialValue_id'] = $data['QcControlMaterialValue_id'];
			$where .= ' and QCMV.QcControlMaterialValue_id = :QcControlMaterialValue_id';
		}

		if(!empty($data['QcControlMaterial_id'])) {
			$params['QcControlMaterial_id'] = $data['QcControlMaterial_id'];
			$where .= ' and QCMV.QcControlMaterial_id = :QcControlMaterial_id';
		}

		if(!empty($data['MedService_id'])) {
			$params['MedService_id'] = $data['MedService_id'];
			$where .= ' and QCMV.MedService_id = :MedService_id';
		}

		if(!empty($data['Analyzer_id'])) {
			$params['Analyzer_id'] = $data['Analyzer_id'];
			$where .= ' and AT.Analyzer_id = :Analyzer_id';
		}

		$query = "
			SELECT
				QCMV.QcControlMaterialValue_id as \"QcControlMaterialValue_id\",
				QCMV.QcControlMaterial_id as \"QcControlMaterial_id\",
				QCMV.UslugaComplex_id as \"UslugaComplex_id\",
				QCMV.AnalyzerTest_id as \"AnalyzerTest_id\",
				QCMV.QcControlMaterialValue_X as \"QcControlMaterialValue_X\",
				QCMV.QcControlMaterialValue_S as \"QcControlMaterialValue_S\",
				coalesce(QCMV.QcControlMaterialValue_CV10, QMV.QcMaxValues_CV10) as \"QcControlMaterialValue_CV10\",
				QCMV.QcControlMaterialValue_CV20 as \"QcControlMaterialValue_CV20\",
				QCMV.QcControlMaterialValue_B10 as \"QcControlMaterialValue_B10\",
				QCMV.QcControlMaterialValue_B20 as \"QcControlMaterialValue_B20\",
				to_char(QCMV.QcControlMaterialValue_begDT, 'DD.MM.YYYY') as \"QcControlMaterialValue_begDT\",
				to_char(QCMV.QcControlMaterialValue_endDT, 'DD.MM.YYYY') as \"QcControlMaterialValue_endDT\",
				QCM.QcControlMaterial_Name as \"QcControlMaterial_Name\",
				QCM.QcControlMaterial_IsAttested as \"QcControlMaterial_IsAttested\",
				QCMT.QcControlMaterialType_Name as \"QcControlMaterialType_Name\",
				UC.UslugaComplex_Code as \"UslugaComplex_Code\",
				UC.UslugaComplex_Name as \"UslugaComplex_Name\",
				coalesce(AT.AnalyzerTest_Name, UC.UslugaComplex_Name) as \"AnalyzerTest_Name\",
				coalesce(AT.AnalyzerTest_Name, UC.UslugaComplex_Name) || ' / ' || MS.MedService_Name as \"labMethod\",
				A.Analyzer_id as \"Analyzer_id\",
				QCMV.MedService_id as \"MedService_id\",
				A.Analyzer_Name as \"Analyzer_Name\"
			FROM lis.QcControlMaterialValue QCMV
			LEFT JOIN lis.v_QcControlMaterial QCM on QCM.QcControlMaterial_id = QCMV.QcControlMaterial_id
			LEFT JOIN lis.v_QcControlMaterialType QCMT ON QCMT.QcControlMaterialType_id = QCM.QcControlMaterialType_id
			LEFT JOIN dbo.v_UslugaComplex UC on UC.UslugaComplex_id = QCMV.UslugaComplex_id
			LEFT JOIN lis.v_AnalyzerTest AT on AT.AnalyzerTest_id = QCMV.AnalyzerTest_id
			LEFT JOIN lis.v_Analyzer A on A.Analyzer_id = AT.Analyzer_id
			LEFT JOIN dbo.v_MedService MS on MS.MedService_id = QCMV.MedService_id
			LEFT JOIN LATERAL( SELECT * FROM lis.QcMaxValues WHERE UslugaComplex_id = QCMV.UslugaComplex_id limit 1) as QMV on true
			WHERE QCM.Lpu_id = :Lpu_id {$where}
			ORDER BY QCMV.QcControlMaterialValue_endDT desc, QCMV.QcControlMaterialValue_insDT desc
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Загрузка последних результатов серий методик по анализатору
	 */
	function loadMethodicsByAnalyzer($data) {
		if(empty($data['Analyzer_id'])) {
			throw new Exception('Не передан анализатор');
		}
		$params = array();
		$params['Analyzer_id'] = $data['Analyzer_id'];
		$params['QcControlStage_id'] = 3; //стадия контроль качества
		$query = "
			select
				cm.QcControlMaterial_id as \"QcControlMaterial_id\",
				cm.QcControlMaterial_Name as \"QcControlMaterial_Name\",
				cmv.QcControlMaterialValue_id as \"QcControlMaterialValue_id\",
				coalesce(AT.AnalyzerTest_Name, UC.UslugaComplex_Name) || '/' || parentSeries.QcControlSeries_Name as \"AnalyzerTest_Name\",
				childSeries.QcControlSeries_id as \"QcControlSeries_id\",
				childSeries.QcControlSeries_Xcp as \"QcControlSeries_Xcp\",
				childSeries.QcControlSeries_S as \"QcControlSeries_S\",
				CSV.UslugaTest_ResultValue as \"UslugaTest_ResultValue\",
				CSV.QcControlSeriesValue_id as \"QcControlSeriesValue_id\",
				CSV.QcControlSeriesValue_setDT as \"QcControlSeriesValue_setDT\",
				CSV.QcRule_id as \"QcRule_id\",
				csv.QcRule_Name as \"QcRule_Name\",
				AT.Analyzer_id as \"Analyzer_id\"
			from lis.v_QcControlMaterial CM
			left join lis.v_QcControlMaterialValue CMV on CMV.QcControlMaterial_id = CM.QcControlMaterial_id
			LEFT JOIN dbo.v_UslugaComplex UC on UC.UslugaComplex_id = CMV.UslugaComplex_id
			LEFT JOIN lis.v_AnalyzerTest AT on AT.AnalyzerTest_id = CMV.AnalyzerTest_id
			left join lis.v_QcControlSeries parentSeries on parentSeries.QcControlMaterialValue_id = CMV.QcControlMaterialValue_id
			LEFT JOIN LATERAL (
				select * 
				from lis.v_QcControlSeries
				where QcControlSeries_pid = parentSeries.QcControlSeries_id and QcControlStage_id = :QcControlStage_id
				order by QcControlSeries_begDT desc
				limit 1
			) as childSeries on true
			LEFT JOIN LATERAL (
				select
					v_QcControlSeriesValue.*,
					v_QcRule.QcRule_Name,
					v_UslugaTest.UslugaTest_ResultValue
				from lis.v_QcControlSeriesValue
				left join lis.v_QcRule on v_QcRule.QcRule_id = v_QcControlSeriesValue.QcRule_id
				LEFT JOIN v_UslugaTest on v_UslugaTest.UslugaTest_id = v_QcControlSeriesValue.UslugaTest_id
				where QcControlSeries_id = childSeries.QcControlSeries_id and coalesce(QcControlSeriesValue_isDisabled,1) = 1
				order by QcControlSeriesValue_setDT desc, QcControlSeriesValue_id desc
				limit 1
			) as CSV on true
			where CSV.QcControlSeriesValue_id is not null
				and AT.Analyzer_id = :Analyzer_id
				and childSeries.QcControlSeries_endDT is null
		";
		//echo getDebugSQL($query, $params); exit;
		return $this->queryResult($query, $params);
	}

	/**
	 * Выполнение сценарий не описанных в swModel
	 * @param array $data
	 * @return array
	 * @throws Exception
	 */
	function doScenario($data = array()) {
		$result = parent::doScenario($data);
		switch($this->getScenario()) {
			case self::SCENARIO_GET_MAXVALUES:
				$result = $this->getMaxValues($data);
				break;
			case self::SCENARIO_LOAD_SVOD_GRID:
				$result = $this->loadMethodicsByAnalyzer($data);
				break;
			case self::SCENARIO_LOAD_USLUGACOMPLEX:
				$result = $this->loadUslugaComplex($data);
		}
		return $result;
	}

	/**
	 * Проверка перед дизейблом
	 * @return void
	 * @throws Exception
	 */
	function _validateBeforeDisable() {

		if($this->saveAsNewObject) return;

		$params = array(
			'QcControlMaterialValue_id' => $this->getAttribute(self::ID_KEY)
		);

		$query = "
			SELECT *
			FROM lis.v_QcControlMaterialValue QMV
			left join lis.v_QcControlSeries QCS on QCS.QcControlMaterialValue_id = QMV.QcControlMaterialValue_id
			WHERE QMV.QcControlMaterialValue_id = :QcControlMaterialValue_id 
				and (QMV.QcControlMaterialValue_endDT is null
					or (QCS.QcControlSeries_id is not null and QCS.QcControlSeries_endDT is null))

		";

		$result = $this->getFirstResultFromQuery($query, $params);

		if($result) {
			throw new Exception('Требуется удалить все связанные данные');
		}
	}

	/**
	 * Загрузка параметров CV10,B10,CV20,B20 со справочника
	 * @param $data
	 * @return array|false
	 */
	function getMaxValues($data) {
		$params = [
			'UslugaComplex_id' => $data['UslugaComplex_id']
		];
		$query = "
			SELECT
				QcMaxValues_B10 as \"QcControlMaterialValue_B10\",
				QcMaxValues_CV10 as \"QcControlMaterialValue_CV10\",
				QcMaxValues_B20 as \"QcControlMaterialValue_B20\",
				QcMaxValues_CV20 as \"QcControlMaterialValue_CV20\"
			FROM lis.QcMaxValues
			WHERE UslugaComplex_id = :UslugaComplex_id
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Загрузка списка услуг
	 * @param $data
	 * @return array|false
	 */
	function loadUslugaComplex($data) {
		$params = [
			'Analyzer_id' => $data['Analyzer_id']
		];

		$query = "
			Select 
				DISTINCT
				--AT.AnalyzerTest_id,
				--AT.AnalyzerTest_pid,
				AT.UslugaComplex_id as \"UslugaComplex_id\",
				UC.UslugaComplex_Name as \"UslugaComplex_Name\",
				UC.UslugaComplex_Code as \"UslugaComplex_Code\"
			From lis.v_AnalyzerTest AT
			left join v_UslugaComplex UC on UC.UslugaComplex_id = AT.UslugaComplex_id
			Where Analyzer_id = :Analyzer_id and AnalyzerTEst_IsTest = 2
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Валидация перед сохранением
	 * @throws Exception
	 */
	function _beforeValidate()
	{
		parent::_beforeValidate();
		$MedService_id = $this->getAttribute('medservice_id');
		if(empty($MedService_id)) {
			throw new Exception('Не выбрана лаборатория');
		}
	}
}
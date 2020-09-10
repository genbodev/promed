<?php defined('BASEPATH') or die('No direct script access allowed');
/**
 * MethodsIFAAnalyzerTest_model - модель для работы с формой "Выбор методики" (swMethodsIFAAnalyzerTestWindow)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @author       Magafurov Salavat
 * @version      15.11.2019
 */

require_once('Scenario_model.php');
class MethodsIFAAnalyzerTest_model extends Scenario_model
{
	var $table_name = 'MethodsIFAAnalyzerTest';
	var $scheme = 'dbo';
	var $saveAsNewObject = true;

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->_setScenarioList(array(
			self::SCENARIO_DO_SAVE,
			self::SCENARIO_LOAD_GRID,
			self::SCENARIO_DELETE
		));
	}

	/**
	 * Возвращает массив описаний всех используемых атрибутов объекта в формате ключ => описание
	 * @return array
	 */
	static function defAttributes()
	{
		return array(
			self::ID_KEY => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME
				),
				'alias' => 'MethodsIFAAnalyzerTest_id',
				'label' => 'Идентификатор',
				'save' => '',
				'type' => 'id'
			),
			'methodsifa_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'MethodsIFA_id',
				'label' => 'Методика ИФА',
				'save' => '',
				'type' => 'int'
			),
			'analyzertest_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'AnalyzerTest_id',
				'label' => 'Тест',
				'save' => '',
				'type' => 'int'
			)
		);
	}

	/**
	 * Получение правил для входящих параметров
	 * @param $name
	 * @return array
	 * @throws Exception
	 */
	function getInputRules($name)
	{
		$rules = parent::getInputRules($name);

		switch ($name) {
			case self::SCENARIO_LOAD_GRID:
				$rules = $this->getInputRulesByAttributes(['analyzertest_id']);
				break;
		}

		return $rules;
	}

	/**
	 * Загрузка формы и грида (Контроль качества/Контрольные материалы)
	 * @param array $data
	 * @return array|false
	 */
	function doLoadGrid($data = array())
	{

		$params = [];
		$where = [];

		$where[] = "1=1";

		if(!empty($data['AnalyzerTest_id'])) {
			$params['AnalyzerTest_id'] = $data['AnalyzerTest_id'];
			$where[] = "MIAT.AnalyzerTest_id = :AnalyzerTest_id";
		}

		$where = implode($where, " and ");

		$query = "
			SELECT
				MIAT.MethodsIFAAnalyzerTest_id as \"MethodsIFAAnalyzerTest_id\",
				MI.MethodsIFA_id as \"MethodsIFA_id\",
				MI.MethodsIFA_Code as \"MethodsIFA_Code\",
				MI.MethodsIFA_Name as \"MethodsIFA_Name\",
				MI.MethodsIFA_TestVolume as \"MethodsIFA_TestVolume\",
				MI.MethodsIFA_Sens as \"MethodsIFA_Sens\",
				MI.MethodsIFA_IncTemp as \"MethodsIFA_IncTemp\",
				MI.MethodsIFA_IncTime as \"MethodsIFA_IncTime\",
				MI.MethodsIFA_MinValue as \"MethodsIFA_MinValue\",
				MI.MethodsIFA_MaxValue as \"MethodsIFA_MaxValue\",
				MI.MethodsIFA_Wavelength as \"MethodsIFA_Wavelength\",
				F.FULLNAME as \"FULLNAME\",
				U.Unit_Name as \"Unit_Name\"
			FROM v_MethodsIFAAnalyzerTest MIAT
			INNER JOIN v_MethodsIFA MI on MI.MethodsIFA_id = MIAT.MethodsIFA_id
			LEFT JOIN lis.v_Unit U on U.Unit_id = MI.Unit_id
			LEFT JOIN rls.v_FIRMS F on F.FIRMS_ID = MI.FIRMS_ID
			where {$where}
		";

		return $this->queryResult($query,$params);
	}


	/**
	 * Проверки и другая логика перед сохранением
	 * @throws Exception
	 */
	function _validate()
	{
		switch($this->getScenario()) {
			case self::SCENARIO_DO_SAVE:
				$this->beforeSave();
				break;

			case self::SCENARIO_DELETE:
				$this->beforeDelete();
				break;
		}
	}

	/**
	 * Проверка на дубль
	 * @return void
	 * @throws Exception
	 */
	function beforeSave()
	{
		$params = [
			'AnalyzerTest_id' => $this->getAttribute('analyzertest_id'),
			'MethodsIFA_id' => $this->getAttribute('methodsifa_id')
		];

		$query = "
			SELECT MethodsIFAAnalyzerTest_id as \"MethodsIFAAnalyzerTest_id\"
			FROM v_MethodsIFAAnalyzerTest
			where AnalyzerTest_id = :AnalyzerTest_id and MethodsIFA_id = :MethodsIFA_id
		";

		$id = $this->getFirstResultFromQuery($query, $params);

		if($id) {
			throw new Exception('Методика уже добавлена');
		}
	}

	/**
	 * Копируем референсные значения методик ифа
	 * @param $result
	 * @return void
	 * @throws Exception
	 */
	function _afterSave($result)
	{
		$params = [
			'MethodsIFA_id' => $this->getAttribute('methodsifa_id'),
			'AnalyzerTest_id' => $this->getAttribute('analyzertest_id')
		];

		$query = "
			SELECT
				MIRV.RefValues_id as \"RefValues_id\"
			FROM
				v_MethodsIFARefValues MIRV
				LEFT JOIN lis.v_AnalyzerTestRefValues ATRV on ATRV.RefValues_id = MIRV.RefValues_id and ATRV.AnalyzerTest_id = :AnalyzerTest_id
			WHERE
				MIRV.MethodsIFA_id = :MethodsIFA_id and ATRV.RefValues_id is null
		";

		$ifaRefValues = $this->queryResult($query,$params);

		if(!$ifaRefValues) return;

		$AnalyzerTest_id = $this->getAttribute('analyzertest_id');
		$pmUser_id = $this->getPromedUserId();

		foreach($ifaRefValues as $refValue) {
			$refValue['AnalyzerTest_id'] = $AnalyzerTest_id;
			$refValue['pmUser_id'] = $pmUser_id;
			$result = $this->execCommonSP('lis.p_AnalyzerTestRefValues_ins', $refValue);
			if(!$result || !$result['success']) {
				throw new Exception('Ошибка при сохранении');
			}
		}
	}

	/**
	 * Удаление референсных значений
	 * @param array
	 * @throws Exception
	 */
	protected function beforeDelete()
	{
		$params = [
			'AnalyzerTest_id' => $this->getAttribute('analyzertest_id'),
			'MethodsIFAAnalyzerTest_id' => $this->getAttribute(self::ID_KEY)
		];

		$query = "
			SELECT
				ATRV.AnalyzerTestRefValues_id as \"AnalyzerTestRefValues_id\"
			FROM
				lis.v_AnalyzerTestRefValues ATRV
				INNER JOIN v_MethodsIFAAnalyzerTest MIAT on MIAT.AnalyzerTest_id = ATRV.AnalyzerTest_id
				INNER JOIN v_MethodsIFARefValues MIRV on MIRV.MethodsIFA_id = MIAT.MethodsIFA_id and MIRV.RefValues_id = ATRV.RefValues_id
			WHERE
				ATRV.AnalyzerTest_id = :AnalyzerTest_id
			and MIAT.MethodsIFAAnalyzerTest_id = :MethodsIFAAnalyzerTest_id
		";

		$ifaRefValues = $this->queryResult($query, $params);

		if (!$ifaRefValues) return;

		foreach ($ifaRefValues as $refValue) {
			$result = $this->execCommonSP('lis.p_AnalyzerTestRefValues_del', $refValue );
			if (!$result || !$result['success']) {
				throw new Exception('Ошибка при сохранении');
			}
		}
	}
}
<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * QcControlSeriesValue_model - модель для добавления значений контрольных серий" (swQcControlSeriesWindow)
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
class QcControlSeriesValue_model extends Scenario_model {

	var $table_name = 'QcControlSeriesValue';
	var $scheme = 'lis';
	var $Lpu_id = null;
	var $MedService_id = null;
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		$this->_setScenarioList(array(
			self::SCENARIO_DO_SAVE_GRID,
			self::SCENARIO_LOAD_GRID,
			self::SCENARIO_DISABLE
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
				'alias' => 'QcControlSeriesValue_id',
				'label' => 'Идентификатор',
				'save' => '',
				'type' => 'id'
			),
			'data' => array(
				'properties' => array(
					self::PROPERTY_NOT_SAFE,
					self::PROPERTY_NOT_LOAD
				),
				'label' => 'Массив значений серии',
				'save' => '',
				'type' => 'string'
			),
			'qccontrolseries_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'QcControlSeries_id',
				'label' => 'Серия',
				'save' => '',
				'type' => 'id',
				'join' => 'left join lis.v_QcControlSeries QCS on QCS.QcControlSeries_id = v_QcControlSeriesValue.QcControlSeries_id'
			),
			'setdt' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
					self::PROPERTY_DATE_TIME,
					self::PROPERTY_NEED_TABLE_NAME
				),
				'alias' => 'setDT',
				'label' => 'Дата проведения',
				'save' => '',
				'type' => 'date',
				'applyMethod' => '_applySetDT',
				'dateKey'=>'setdate',
				'timeKey'=>'settime',
			),
			'setdate' => array(
				'properties' => array(
					self::PROPERTY_NOT_SAFE
				),
				'alias' => 'setDate',
				'label' => 'Дата проведения',
				'save' => 'trim',
				'type' => 'date',
				'select' => "to_char(QcControlSeriesValue_setDT, 'DD.MM.YYYY' ) as \"setDate\""
			),
			'settime' => array(
				'properties' => array(
					self::PROPERTY_NOT_SAFE
				),
				'alias' => 'setTime',
				'label' => 'Время проведения',
				'save' => 'trim',
				'type' => 'time',
				'select' => "to_char(QcControlSeriesValue_setDT, 'HH:MI:SS') as \"setTime\""
			),
			'qcrule_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'QcRule_id',
				'label' => 'Правило',
				'save' => '',
				'type' => 'id'
			),
			'uslugatest_resultvalue' => array(
				'properties' => array(
					self::PROPERTY_NOT_LOAD
				),
				'alias' => 'UslugaTest_ResultValue',
				'label' => 'Результат',
				'save' => '',
				'type' => 'string'
			),
			'qccontrolseriesvalue_comment' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'QcControlSeriesValue_Comment',
				'label' => 'Примечание',
				'save' => '',
				'type' => 'string'
			),
			'qccontrolseriesvalue_isdisabled' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'QcControlSeriesValue_isDisabled',
				'label' => 'Признак исключения',
				'save' => '',
				'type' => 'int'
			),
			'qccontrolseriesvalue_iscontrolpassed' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'QcControlSeriesValue_IsControlPassed',
				'label' => 'Контроль пройден',
				'save' => '',
				'type' => 'int'
			),
			'uslugatest_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'UslugaTest_id',
				'label' => 'Проба',
				'save' => '',
				'type' => 'int'
			),
			'qccontrolstage_id' => array(
				'properties' => array(),
				'alias' => 'QcControlStage_id',
				'type' => 'int',
				'save' => '',
				'select' => 'QCS.QcControlStage_id'
			),
			'qccontrolseries_pid' => array(
				'properties' => array(),
				'alias' => 'QcControlSeries_pid',
				'type' => 'int',
				'save' => '',
				'select' => 'QCS.QcControlSeries_pid'
			),
			'qccontrolseriesvalue_daterange' => array(
				'properties' => array(
					self::PROPERTY_NOT_LOAD
				),
				'alias' => 'QcControlSeriesValue_DateRange',
				'type' => 'daterange'
			),
			'iscull' => array(
				'properties' => array(
					self::PROPERTY_NOT_LOAD
				),
				'alias' => 'isCull',
				'type' => 'int'
			),
			'action' => array(
				'properties' => array(
					self::PROPERTY_NOT_LOAD
				),
				'alias' => 'action',
				'type' => 'string'
			)
		);
	}

	/**
	 * Извлечение даты и времени события из входящих параметров
	 *
	 * Устанавливаем значение атрибута, если оно пришло (есть нужные ключи в массиве)
	 * @param array $data
	 * @return bool
	 */
	protected function _applySetDT($data) {
		return $this->_applyDT($data, 'set');
	}

	/**
	 * Получение правил для входящих параметров
	 * @param $name
	 * @return array
	 */
	function getInputRules($name) {
		$rules = [];

		switch($name) {
			case self::SCENARIO_LOAD_GRID:
				$attributes = [
					'qccontrolseries_id',
					'qccontrolseriesvalue_daterange',
					'iscull',
					'qccontrolstage_id',
					'qccontrolseries_pid'
				];
				$rules = $this->getInputRulesByAttributes($attributes);
				break;
			case self::SCENARIO_DISABLE:
				$rules = $this->getInputRulesByAttributes([self::ID_KEY, 'qccontrolseriesvalue_comment','qccontrolseriesvalue_isdisabled']);
				break;
			case self::SCENARIO_DO_SAVE_GRID:
				$rules = $this->getInputRulesByAttributes(['data', 'action', 'qccontrolstage_id', 'qccontrolseries_id', 'qccontrolseries_pid']);
				break;
		}

		if(!count($rules)) {
			$rules = Scenario_model::getInputRules($name);
		}

		return $rules;
	}

	/**
	 * Загрузка формы и грида (Контроль качества/Контрольные материалы)
	 * @param array $data
	 * @return array|false
	 */
	function doLoadData($data = array()) {

		$params = [];
		$where = '';

		if(!empty($data['QcControlSeries_id'])) {
			$params['QcControlSeries_id'] = $data['QcControlSeries_id'];
			$where .= ' and QCS.QcControlSeries_id = :QcControlSeries_id';
		} else if(!empty($data['QcControlSeries_pid'])) {
			$params['QcControlSeries_pid'] = $data['QcControlSeries_pid'];
			$where .= ' and QCS.QcControlSeries_pid = :QcControlSeries_pid';
		}

		if(!empty($data['QcControlStage_id'])) {
			$params['QcControlStage_id'] = $data['QcControlStage_id'];
			$where .= ' and QCS.QcControlStage_id = :QcControlStage_id';
		}

		if(!empty($data['QcControlSeriesValue_DateRange'][0]) && !empty($data['QcControlSeriesValue_DateRange'][1])) {
			$params['begDate'] = $data['QcControlSeriesValue_DateRange'][0];
			$params['endDate'] = $data['QcControlSeriesValue_DateRange'][1];
			$where .= ' and cast(QCSV.QcControlSeriesValue_Date as date) between :begDate and :endDate';
		}

		if(isset($data['isCull'])) {
			$params['QcControlSeriesValue_isDisabled'] = $data['isCull'] ? 2 : 1;
			$where .= ' and coalesce(QCSV.QcControlSeriesValue_isDisabled, 1) = :QcControlSeriesValue_isDisabled';
		}

		if(!empty($data['QcControlSeriesValue_IsControlPassed'])) {
			$params['QcControlSeriesValue_IsControlPassed'] = $data['QcControlSeriesValue_IsControlPassed'];
			$where .= ' and coalesce(QCSV.QcControlSeriesValue_IsControlPassed, 1) = :QcControlSeriesValue_IsControlPassed';
		}

		$query = "
			SELECT *,
				ROW_NUMBER() OVER(ORDER BY db.\"QcControlSeriesValue_setDT\", db.\"QcControlSeriesValue_id\") as num
			FROM
				(SELECT
					QCSV.QcControlSeriesValue_id as \"QcControlSeriesValue_id\",
					QCSV.QcControlSeries_id as \"QcControlSeries_id\",
					to_char(QCSV.QcControlSeriesValue_setDT, 'DD.MM.YYYY') as \"setDate\",
					QCSV.QcControlSeriesValue_setDT as \"QcControlSeriesValue_setDT\",
					to_char(QCSV.QcControlSeriesValue_setDT, 'HH:MI:SS') as \"setTime\",
					QCSV.QcControlSeriesValue_IsControlPassed as \"QcControlSeriesValue_IsControlPassed\",
					QCSV.QcRule_id as \"QcRule_id\",
					QR.QcRule_Name as \"QcRule_Name\",
					QCSV.QcControlSeriesValue_Comment as \"QcControlSeriesValue_Comment\",
					QCSV.QcControlSeriesValue_isDisabled as \"QcControlSeriesValue_isDisabled\",
					QCS.QcControlStage_id as \"QcControlStage_id\",
					QCStage.QcControlStage_Name as \"QcControlStage_Name\",
					UT.UslugaTest_ResultValue as \"UslugaTest_ResultValue\",
					UT.UslugaTest_id as \"UslugaTest_id\"
				FROM lis.v_QcControlSeries QCS
				LEFT JOIN lis.v_QcControlSeries QCSpid on QCSpid.QcControlSeries_id = QCS.QcControlSeries_id
				INNER JOIN lis.v_QcControlSeriesValue QCSV on QCSV.QcControlSeries_id = QCS.QcControlSeries_id
				LEFT JOIN lis.v_QcRule QR on QR.QcRule_id = QCSV.QcRule_id
				LEFT JOIN lis.v_QcControlStage QCStage on QCStage.QcControlStage_id = QCS.QcControlStage_id
				LEFT JOIN v_UslugaTest UT on UT.UslugaTest_id = QCSV.UslugaTest_id
				WHERE (1=1) {$where}
				ORDER BY QCSV.QcControlSeriesValue_setDT DESC, QCSV.QcControlSeriesValue_id DESC
				LIMIT 30
				) as db
		";
		//echo getDebugSQL($query, $params); exit;
		return $this->queryResult($query, $params);
	}

	/**
	 * Загрузка данных для расчетов
	 * @param $data
	 * @return array|false
	 */
	function loadDataForCalculate($data) {
		$params = [];

		$where = ' and coalesce(QCSV.QcControlSeriesValue_isDisabled,1) = 1';

		if(!empty($data['QcControlSeries_id'])) {
			$params['QcControlSeries_id'] = $data['QcControlSeries_id'];
			$where .= ' and QCS.QcControlSeries_id = :QcControlSeries_id';
		} else if(!empty($data['QcControlSeries_pid'])) {
			$params['QcControlSeries_pid'] = $data['QcControlSeries_pid'];
			$where .= ' and QCS.QcControlSeries_pid = :QcControlSeries_pid';
		}

		$count = '';
		if(!empty($data['count'])) {
			$count = 'LIMIT '.$data['count'];
		}

		if(!empty($data['QcControlSeriesValue_IsControlPassed'])) {
			$params['QcControlSeriesValue_IsControlPassed'] = $data['QcControlSeriesValue_IsControlPassed'];
			$where .= ' and QCSV.QcControlSeriesValue_IsControlPassed = :QcControlSeriesValue_IsControlPassed';
		}

		if(!empty($data['_QcControlStage_id'])) {
			$params['_QcControlStage_id'] = $data['_QcControlStage_id'];
			$where .= ' and coalesce(QCS.QcControlStage_id,0) != :_QcControlStage_id';
		}

		$query = "
			SELECT
				db.QcControlSeriesValue_id as \"QcControlSeriesValue_id\",
				db.QcControlSeries_id as \"QcControlSeries_id\",
				db.setDT as \"setDT\",
				db.setDate as \"setDate\",
				db.setTime as \"setTime\",
				db.QcControlSeriesValue_setDT as \"QcControlSeriesValue_setDT\",
				db.QcControlSeriesValue_IsControlPassed as \"QcControlSeriesValue_IsControlPassed\",
				db.UslugaTest_ResultValue as \"UslugaTest_ResultValue\",
				db.QcControlSeriesValue_Comment as \"QcControlSeriesValue_Comment\",
				db.QcControlSeriesValue_isDisabled as \"QcControlSeriesValue_isDisabled\",
				db.QcControlStage_id as \"QcControlStage_id\"
			FROM
				(SELECT
					QCSV.QcControlSeriesValue_id,
					QCSV.QcControlSeries_id,
					QCSV.QcControlSeriesValue_setDT as setDT,
					to_char(QCSV.QcControlSeriesValue_setDT, 'DD.MM.YYYY') as setDate,
					to_char(QCSV.QcControlSeriesValue_setDT, 'HH:MI:SS') as setTime,
					QcControlSeriesValue_setDT,
					QCSV.QcControlSeriesValue_IsControlPassed,
					UT.UslugaTest_ResultValue,
					QCSV.QcControlSeriesValue_Comment,
					QCSV.QcControlSeriesValue_isDisabled,
					QCS.QcControlStage_id
				FROM lis.v_QcControlSeries QCS
				LEFT JOIN lis.v_QcControlSeries QCSpid on QCSpid.QcControlSeries_id = QCS.QcControlSeries_id
				INNER JOIN lis.v_QcControlSeriesValue QCSV on QCSV.QcControlSeries_id = QCS.QcControlSeries_id
				LEFT JOIN v_UslugaTest UT on UT.UslugaTest_id = QCSV.UslugaTest_id
				WHERE (1=1) {$where}
				ORDER BY QCSV.QcControlSeriesValue_setDT desc, QCSV.QcControlSeriesValue_id desc) as db
				{$count}
		";

		//echo getDebugSQL($query, $params); exit;
		return $this->queryResult($query, $params);
	}

	/**
	 * Валидация перед сохранением грида
	 * @param $data
	 * @throws Exception
	 */
	function validateBeforeSaveGrid($data) {
		if( empty($data['data'])
			|| empty($data['QcControlSeries_id'])
			|| empty($data['QcControlSeries_pid'])
			|| empty($data['QcControlStage_id'])
		) {
			throw new Exception('Не переданы параметры серии');
		}
	}

	/**
	 * Сохранение грида
	 * @param array $data
	 * @return array|bool
	 * @throws Exception
	 */
	function doSaveGrid($data = array()) {
		$this->validateBeforeSaveGrid($data);

		$rows = json_decode($data['data'], true);

		$this->setAttributes($data);

		foreach($rows as $key=>$row) {
			$value = strval($row['UslugaTest_ResultValue']);
			$date = substr($row['setDate'],0,10);
			$time = $row['setTime'];
			$rows[$key]['QcControlSeries_id'] = $this->getAttribute('qccontrolseries_id');
			$rows[$key]['setDate'] = $date;
			$rows[$key]['setDT'] = DateTime::createFromFormat('Y-m-d H:i', $date . ' ' . $time);
			$rows[$key]['UslugaTest_ResultValue'] = $value;
			if(!isset($value) || $value == "" || empty($date) || empty($time)) {
				return $this->createError('','Необходимо заполнить пустые поля');
			}
		}

		if(!count($rows)) return false;

		try {
			//запускаем транзакцию, значит надо отключить остальные транзакции
			if ( !$this->db->trans_begin() ) {
				throw new Exception('Ошибка при попытке запустить транзакцию');
			}

			if($this->getScenario() == self::SCENARIO_DO_SAVE_GRID) {
				$rows = $this->_beforeSaveGrid($rows);
			}

			$response = [
				'success' => true,
				'data' => array() //
			];

			foreach($rows as $row) {
				$row['session'] = $this->getSessionParams();

				$result = [];

				$result = $this->doSave($row, false);

				if(!empty($result['Error_Code']) || !empty($result['Error_Msg'])) {
					throw new Exception($result['Error_Msg']);
				} else {
					array_push($response['data'], [
						'num' => $row['num'],
						'QcControlSeriesValue_id' => $result['QcControlSeriesValue_id']
					]);
				}
			}

			if ( !$this->db->trans_commit() ) {
				throw new Exception('Не удалось зафиксировать транзакцию', 500);
			}
			return $response;

		}
		catch( Exception $e) {
			$this->rollbackTransaction();
			return array('Error_Msg'=>$e->getMessage(),'Error_Code'=>$e->getCode());
		}
	}

	/**
	 * Исключение записей
	 * @param array $data
	 * @param bool $isAllowTransaction
	 * @return array|Exception
	 */
	function doDisable($data = array(), $isAllowTransaction = true) {
		return $this->doSave($data);
	}

	/**
	 * Проверка на то что значение (value) выходит за рамки (Xcp +/- S)
	 * @param float $value
	 * @param float $Xcp
	 * @param float $S
	 * @return Boolean
	 */
	function isRule($value, $Xcp, $S) {
		return $value < $Xcp - $S || $Xcp + $S < $value;
	}

	/**
	 * Сравнение для проверки правил (6X, 8X, 10X) - принадлежность двух результатов к одной и той же стороне от $Xcp
	 * @param float $lastValue
	 * @param float $newValue
	 * @param float $Xcp
	 * @param int $S
	 * @return Boolean
	 */
	function isXRule($lastValue, $newValue, $Xcp, $S = 0) {
		return ($lastValue >= $Xcp + $S && $newValue >= $Xcp + $S) || ($lastValue < $Xcp - $S && $newValue < $Xcp - $S);
	}

	/**
	 * Сравнение для проверки правила (R4S) - проверка расстояния между точками
	 * @param float $lastValue
	 * @param float $newValue
	 * @param float $S
	 * @return Boolean
	 */
	function isRRule($lastValue, $newValue, $S) {
		return abs($lastValue - $newValue) > $S;
	}

	/**
	 * Получение даты последнего результата
	 * @return bool|float|int|string
	 * @throws Exception
	 */
	function getLastDate() {
		$params = [
			'QcControlSeries_pid' => $this->getAttribute('qccontrolseries_pid')
		];
		if(!$params['QcControlSeries_pid']) {
			throw new Exception('Серия не определена');
		}
		$query = "
			select
				cSeriesValue.QcControlSeriesValue_setDT as \"QcControlSeriesValue_setDT\"
			from lis.v_QcControlSeries parentSeries
			left join lis.v_QcControlSeries childSeries on childSeries.QcControlSeries_pid = parentSeries.QcControlSeries_id
			left join lis.v_QcControlSeriesValue cSeriesValue on cSeriesValue.QcControlSeries_id = childSeries.QcControlSeries_id
			where coalesce(cSeriesValue.QcControlSeriesValue_isDisabled,1) = 1
				and parentSeries.QcControlSeries_id = :QcControlSeries_pid
			order by QcControlSeriesValue_setDT desc
			limit 1
		";

		//echo getDebugSQL($query,$params); exit;
		return $this->getFirstResultFromQuery($query, $params );
	}

	/**
	 * Проверка дат перед сохранением
	 * @param $data - Массив результатов
	 * @throws Exception
	 */
	function validateDate($data) {
		$minDate = null;
		$currentDate = $this->getCurrentDT();
		$lastDate = $this->getLastDate();
		foreach($data as $result) {

			if(!empty($result['QcControlSeriesValue_id'])) {
				continue;
			}

			$date = $result['setDT'];
			if(!$minDate || $date < $minDate) {
				$minDate = $date;
			}

			if($date > $currentDate) {
				throw new Exception("Дата не может превышать дату текущего дня");
			}
		}
		if($this->getAttribute('qccontrolstage_id') == QcControlSeries_model::STAGE_INST_SERIES) {
			if(!self::dateValidateForInstallSeries($data)) {
				throw new Exception('Разрешено не более 3х измерений в день для установочной серии');
			}
		}
		if($lastDate && $lastDate > $minDate) {
			throw new Exception('Пересечение дат');
		}
	}

	/**
	 * Проверка дат для установочной серии, не более 3х в день
	 * @param array $results - Массив результатов
	 * @return Boolean
	 */
	static function dateValidateForInstallSeries($results) {
		$countPerDay = array();
		foreach($results as $key => $object) {
			$date = $object['setDate'];
			if(empty($countPerDay[$date])) {
				$countPerDay[$date] = 1;
			} else {
				$countPerDay[$date]++;
			}
		}
		foreach ($countPerDay as $key => $count) {
			if($count > 3) {
				return false;
			}
		}
		return true;
	}


	/**
	 * Логика до сохранения грида - проверим результаты по правилам КК
	 * @param array $data - Массив результатов переданных на сохранение
	 * @return array
	 * @throws Exception
	 */
	function _beforeSaveGrid($data = array()) {

		$resultCount = count($data);
		$resultMaxCount = 12;
		$QcControlSeries_id = $this->getAttribute('qccontrolseries_id');
		$QcControlSeries_pid = $this->getAttribute('qccontrolseries_pid');
		$QcControlStage_id = $this->getAttribute('qccontrolstage_id');

		if(!$resultCount) {
			throw new Exception('Нет данных для сохранения');
		};

		//загрузим серию
		$params = array();
		$params['QcControlSeries_id'] = $QcControlSeries_id;
		$this->load->model('QcControlSeries_model', 'series_model');
		$seriesObj = $this->series_model->doLoadData($params);

		if(!$seriesObj || empty($seriesObj[0]['QcControlSeries_id'])) {
			throw new Exception('Не удалось получить данные серии');
		}

		$this->Lpu_id = $seriesObj[0]['Lpu_id'];
		$this->MedService_id = $seriesObj[0]['MedService_id'];

		$results = [];
		if($QcControlStage_id != QcControlSeries_model::STAGE_CONVERGENCE) {
			//для расчета нужно N значений в зависимости от применяемых правил, пока минимум 12
			$params = array();
			$params['count'] = $resultMaxCount-$resultCount;
			$params['QcControlSeries_pid'] = $QcControlSeries_pid;
			$params['_QcControlStage_id'] = 1;
			if($params['count'] > 0) {
				$results = $this->loadDataForCalculate($params); //отсортировано в обратном порядке
			}
			//проверим даты
			$this->validateDate(array_merge($data, $results));
		}


		//только для стадии КК
		if($QcControlStage_id != QcControlSeries_model::STAGE_QC) {
			return $data;
		}

		$Xcp = $seriesObj[0]['QcControlSeries_Xcp'];
		$S = $seriesObj[0]['QcControlSeries_S'];

		//загрузим настройки лаборатории
		$params = array();
		$params['MedService_id'] = $this->MedService_id;
		$this->load->model('AnalyzerQualityControl_model', 'aqc_model');
		$medServiceObj = $this->aqc_model->doLoadData($params);

		//загрузим список правил лаборатории или мо
		$rulesObjName = $medServiceObj[0]['MedService_IsGeneralQcRule'] ? 'QcRuleLpu' : 'QcRuleLab';
		$this->load->model(''.$rulesObjName.'_model', 'rule_model');
		$allRules = $this->rule_model->doLoadData($medServiceObj[0]);
		$currentRules = array();

		foreach($allRules as $ind => $rule) {
			if($rule['isOn']) {
				$currentRules[] = $rule;
			}
		}

		if(!$currentRules) {
			throw new Exception('Необходимо установить правила для выполнения проверок');
		}

		$valueField = 'UslugaTest_ResultValue';
		$ruleIdField = 'QcRule_id';
		$ruleNameField = 'QcRule_Name';
		$isStrongField = $rulesObjName.'_isStrong';
		$isControlPassedField = 'QcControlSeriesValue_IsControlPassed';

		$resultObj = $data[0];

		$value = $resultObj[$valueField];
		$resultObj[$isControlPassedField] = 2;

		//порядок QcRule_id asc
		foreach($currentRules as $idx => $rule) {
			$isRule = false;
			$ruleName = $rule[$ruleNameField];
			switch($ruleName) {
				case '1 2S':
					$isRule = $this->isRule($value,$Xcp, 2 * $S);
					break;
				case '1 3S':
					$isRule = $this->isRule($value,$Xcp, 3 * $S);
					break;
				case '2 2S':
				case '3 1S':
				case '4 1S':
					if($this->isRule($value, $Xcp, $S)) {
						$isRule = $this->isMultipleRule($value, $results, $Xcp, $S, $ruleName);
					}
					break;
				case 'R4S':
					$isRule = $this->isMultipleRule($value, $results, $Xcp, $S, $ruleName);
					break;
				case '6X':
				case '8X':
				case '10X':
					$isRule = $this->isMultipleRule($value, $results, $Xcp, null, $ruleName);
					break;
				case '7T':
				case '10T':
				case '12T':
					$isRule = $this->isMultipleRule(null, array_merge($data, $results), null, null, $ruleName);
					break;
			}
			if($isRule) {
				$resultObj[$ruleIdField] = $rule[$ruleIdField];
				if($rule[$isStrongField]) {
					$resultObj[$isControlPassedField] = 1;
					break;
				}
			}
		}
		return [$resultObj];
	}

	/**
	 * Проверка на наличие тенденции убывания/возрастания
	 * @param array data - массив результатов
	 * @param int count - количество проверяемых результатов
	 * @param bool upward - тенденция возрастания/убывания
	 * @return bool
	 */
	function isTrend($data, $count, $upward) {
		$valueField = 'UslugaTest_ResultValue';
		$isRule = true;
		for($i=1;$i<$count; ++$i) {
			if($upward) {
				if($data[$i-1][$valueField] < $data[$i][$valueField]) {
					$isRule = false;
					break;
				}
			} else {
				if($data[$i-1][$valueField] > $data[$i][$valueField]) {
					$isRule = false;
					break;
				}
			}
		}
		return $isRule;
	}

	/**
	 * Функция выявления правил
	 * @param $value
	 * @param $data
	 * @param $Xcp
	 * @param $S
	 * @param $ruleName
	 * @return bool
	 * @throws Exception
	 */
	function isMultipleRule($value,$data, $Xcp, $S, $ruleName) {
		$valueField = 'UslugaTest_ResultValue';
		$isRule = true;

		$ruleParams = $this->getRuleParams($ruleName);
		if(!$ruleParams)
			return false;

		for($i=0; $i < $ruleParams['resultCount'] - 1; ++$i) {
			$resObj = $data[$i];
			switch($ruleName) {
				case '2 2S':
				case '3 1S':
				case '4 1S':
				case '6X':
				case '8X':
				case '10X':
					$isRule = $this->isXRule($value, $resObj[$valueField], $Xcp, $ruleParams['multiplier'] * $S);
					break;
				case 'R4S':
					$isRule = $this->isRRule($value, $resObj[$valueField], $ruleParams['multiplier'] * $S);
					break;
				case '7T':
				case '10T':
				case '12T':
					$isRule = $this->isTrend($data, $ruleParams['resultCount'], true);
					if(!$isRule)
						$isRule = $this->isTrend($data, $ruleParams['resultCount'], false);
					return $isRule;
			}
			if(!$isRule) {
				break;
			}
		}
		return $isRule;
	}

	/**
	 * Получение параметров для правила по наименованию
	 * multiplier - множитель для S
	 * resultCount - количество результатов необходимое для выявления правила
	 * @param string paramName
	 * @return array|null
	 * @throws Exception
	 */
	function getRuleParams($ruleName) {
		$rule = array(
			'1 2S' => array(
				'resultCount' => 1,
				'multiplier' => 2
			),
			'1 3S' => array(
				'resultCount' => 1,
				'multiplier' => 3
			),
			'2 2S' => array(
				'resultCount' => 2,
				'multiplier' => 2
			),
			'3 1S' => array(
				'resultCount' => 3,
				'multiplier' => 1
			),
			'4 1S' => array(
				'resultCount' => 4,
				'multiplier' => 1
			),
			'R4S' => array(
				'resultCount' => 2,
				'multiplier' => 4
			),
			'6X' => array(
				'resultCount' => 6,
				'multiplier' => 0
			),
			'8X' => array(
				'resultCount' => 8,
				'multiplier' => 0
			),
			'10X' => array(
				'resultCount' => 10,
				'multiplier' => 0
			),
			'7T' => array(
				'resultCount' => 7,
				'multiplier' => 0
			),
			'10T' => array(
				'resultCount' => 10,
				'multiplier' => 0
			),
			'12T' => array(
				'resultCount' => 12,
				'multiplier' => 0
			)
		);
		if(array_key_exists($ruleName,$rule)) {
			return $rule[$ruleName];
		} else {
			throw new Exception('Правило не описано');
		}
	}

	/**
	 * Логика после определения объекта
	 */
	function _beforeValidate() {
		$scenario = $this->getScenario();

		switch($scenario) {
			case self::SCENARIO_DISABLE:
				$QcControlSeries_id = $this->getAttribute('qccontrolseries_id');

				$this->load->model('QcControlSeries_model');
				$this->QcControlSeries_model->_load($QcControlSeries_id);

				$QcControlStage_id = $this->QcControlSeries_model->getAttribute('qccontrolstage_id');

				$params = [];
				$params['QcControlSeries_id'] = $QcControlSeries_id;
				$params['QcControlSeries_IsControlPassed'] = null;
				$params['pmUser_id'] = $this->getPromedUserId();

				if($QcControlStage_id != QcControlSeries_model::STAGE_QC) {
					$params['QcControlSeries_B'] = null;
					$params['QcControlSeries_CV'] = null;
					$params['QcControlSeries_Xcp'] = null;
					$params['QcControlSeries_S'] = null;
				}

				$result = $this->swUpdate('lis.QcControlSeries', $params);

				if(!$result || empty($result[0]) || empty($result[0]['success'])) {
					throw new Exception('Не удалось сохранить серию');
				}
				break;

			case self::SCENARIO_DO_SAVE_GRID:
				$this->load->model('EvnLabSample_model', 'LabSample');
				$date = $this->getAttribute('setdt');
				$value = $this->getAttribute('uslugatest_resultvalue');
				$params = [
					'UslugaTest_SetDT' => $date ? $date->format('Y-m-d H:i:s') : null,
					'Lpu_id' => $this->Lpu_id,
					'MedService_id' => $this->MedService_id,
					'Server_id' => $this->sessionParams['server_id'],
					'pmUser_id' => $this->sessionParams['pmuser_id'],
					'UslugaTest_ResultValue' => $value
				];
				$result = $this->LabSample->saveQcSampleTest($params);
				if(!$result || empty($result['UslugaTest_id'])) {
					throw new Exception('Не удалось сохранить результат');
				}
				$this->setAttribute('uslugatest_id', $result['UslugaTest_id']);
				break;
		}
	}
}
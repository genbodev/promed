<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* QcControlSeries_model - модель для работы с формой "Контрольные материалы" (swQcControlSeriesWindow)
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
class QcControlSeries_model extends Scenario_model {

	const SCENARIO_CALCULATE = 'calculate';
	//стадия сходимости
	const STAGE_CONVERGENCE = 1;
	//стадия установочные серии
	const STAGE_INST_SERIES = 2;
	//стадия контроль качества
	const STAGE_QC = 3;
	var $table_name = 'QcControlSeries';
	var $scheme = 'lis';
	var $nextstage_id = null;
	var $QcControlSeries_Xcp = null;
	var $QcControlSeries_S = null;
	var $QcControlSeries_B = null;
	var $QcControlSeries_CV = null;
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		$this->_setScenarioList(array(
			self::SCENARIO_DO_SAVE,
			self::SCENARIO_LOAD_GRID,
			self::SCENARIO_DISABLE,
			self::SCENARIO_CALCULATE,
			self::SCENARIO_LOAD_COMBO_BOX
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
				'alias' => 'QcControlSeries_id',
				'label' => 'Идентификатор',
				'save' => 'trim',
				'type' => 'id'
			),
			'qccontrolseries_pid' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'QcControlSeries_pid',
				'label' => 'Серия',
				'save' => '',
				'type' => 'id'
			),
			'qccontrolseries_name' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'QcControlSeries_Name',
				'label' => 'Имя контрольной серии',
				'save' => '',
				'type' => 'string'
			),
			'qccontrolstage_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'QcControlStage_id',
				'label' => 'Стадия',
				'save' => '',
				'type' => 'int'
			),
			'qccontrolmaterialvalue_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'QcControlMaterialValue_id',
				'label' => 'Методика',
				'save' => 'trim',
				'type' => 'int'
			),
			'medservice_id' => array(
				'properties' => array(
					self::PROPERTY_NOT_LOAD,
					self::PROPERTY_NOT_SAFE
				),
				'alias' => 'MedService_id',
				'label' => 'Лаборатория',
				'save' => '',
				'type' => 'int'
			),
			'qccontrolseries_xcp' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'QcControlSeries_Xcp',
				'label' => 'Среднее значение (X)',
				'save' => '',
				'type' => 'float'
			),
			'qccontrolseries_s' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'QcControlSeries_S',
				'label' => 'Среднеквадратичное отклонение (S)',
				'save' => '',
				'type' => 'float'
			),
			'qccontrolseries_b' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'QcControlSeries_B',
				'label' => 'Коэффициент правильности (B)',
				'save' => '',
				'type' => 'float'
			),
			'qccontrolseries_cv' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'QcControlSeries_CV',
				'label' => 'Коэффициент вариации (CV)',
				'save' => '',
				'type' => 'float'
			),
			'qccontrolseries_iscontrolpassed' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'QcControlSeries_IsControlPassed',
				'label' => 'Признак прохождения контроля',
				'save' => '',
				'type' => 'int'
			),
			'begdt' => array( //дата начала действия записи
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
					self::PROPERTY_DATE_TIME,
					self::PROPERTY_NOT_SAFE,
					self::PROPERTY_NEED_TABLE_NAME
				),
				'alias' => 'QcControlSeries_begDT',
				'label' => 'Дата начала действия записи',
				'type' => 'date'
			),
			'enddt' => array( //дата окончания действия
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
					self::PROPERTY_DATE_TIME,
					self::PROPERTY_NOT_SAFE,
					self::PROPERTY_NEED_TABLE_NAME
				),
				'alias' => 'QcControlSeries_endDT',
				'label' => 'Дата окончания серии',
				'type' => 'date'
			),
			'nextstage_id' => array(
				'properties' => array(
					self::PROPERTY_NOT_LOAD,
					self::PROPERTY_NOT_SAFE //обработать в beforeSave
				),
				'alias' => 'NextStage_id',
				'label' => 'Следующая стадия',
				'save' => '',
				'type' => 'int'
			)
		);
	}

	/**
	 * Получение правил для входящих параметров
	 */
	function getInputRules($name) {
		$rules = parent::getInputRules($name);

		switch($name) {
			case self::SCENARIO_LOAD_GRID:
				$attributes = [ 'medservice_id', 'qccontrolmaterialvalue_id', self::ID_KEY ];
				$rules = $this->getInputRulesByAttributes($attributes);
				break;
			case self::SCENARIO_CALCULATE:
				$attributes = [ self::ID_KEY, 'qccontrolseries_pid', 'qccontrolstage_id' ];
				$rules = $this->getInputRulesByAttributes($attributes);
				break;
			case self::SCENARIO_LOAD_COMBO_BOX:
				$rules = $this->getInputRulesByAttributes('qccontrolseries_pid');
				break;
		}

		return $rules;
	}


	/**
	 * Загрузка формы или грида
	 */
	function doLoadData($data = array()) {

		$params = [];
		$where = '';

		if(!empty($data['QcControlSeries_id'])) {
			$params['QcControlSeries_id'] = $data['QcControlSeries_id'];
			$where .= 'and childSeries.QcControlSeries_id = :QcControlSeries_id';
		} else if(!empty($data['QcControlSeries_pid'])) {
			$params['QcControlSeries_pid'] = $data['QcControlSeries_pid'];
			$where .= ' and parentSeries.QcControlSeries_id = :QcControlSeries_pid';
		}

		if(!empty($data['QcControlMaterialValue_id'])) {
			$params['QcControlMaterialValue_id'] = $data['QcControlMaterialValue_id'];
			$where .= ' and QCMV.QcControlMaterialValue_id = :QcControlMaterialValue_id';
		}

		if(!empty($data['MedService_id'])) {
			$params['MedService_id'] = $data['MedService_id'];
			$where .= ' and QCMV.MedService_id = :MedService_id';
		}

		$where .= ' and parentSeries.QcControlSeries_pid is null';

		//ищем родителей и последних наследников
		$query = "
			SELECT
				childSeries.QcControlSeries_id,
				childSeries.QcControlSeries_pid,
				parentSeries.QcControlSeries_Name,
				parentSeries.QcControlMaterialValue_id,
				childSeries.QcControlStage_id,
				QCStage.QcControlStage_Name,
				AT.Analyzer_id,
				isnull(AT.AnalyzerTest_Name,UC.UslugaComplex_Name) as AnalyzerTest_Name,
				childSeries.QcControlSeries_Xcp,
				childSeries.QcControlSeries_S,
				childSeries.QcControlSeries_CV,
				childSeries.QcControlSeries_B,
				childSeries.QcControlSeries_IsControlPassed,
				convert(varchar(10),childSeries.QcControlSeries_begDT,104) as QcControlSeries_begDT,
				convert(varchar(10),childSeries.QcControlSeries_endDT,104) as QcControlSeries_endDT,
				QCM.QcControlMaterial_id,
				QCM.QcControlMaterial_Name,
				QCM.QcControlMaterial_IsAttested,
				QCMV.QcControlMaterialValue_X,
				QCMV.QcControlMaterialValue_S,
				QCMV.QcControlMaterialValue_B10,
				QCMV.QcControlMaterialValue_B20,
				QCMV.QcControlMaterialValue_CV10,
				QCMV.QcControlMaterialValue_CV20,
				QCMV.MedService_id,
				MS.Lpu_id
			FROM lis.v_QcControlSeries parentSeries WITH(nolock)
			OUTER APPLY (SELECT TOP 1 *
						FROM lis.v_QcControlSeries WITH(NOLOCK)
						WHERE QcControlSeries_pid = parentSeries.QcControlSeries_id
						ORDER BY QcControlSeries_insDT desc) childSeries
			LEFT JOIN lis.v_QcControlStage QCStage WITH(NOLOCK) on QCStage.QcControlStage_id = childSeries.QcControlStage_id
			LEFT JOIN lis.v_QcControlMaterialValue QCMV WITH(NOLOCK) on QCMV.QcControlMaterialValue_id = isnull(parentSeries.QcControlMaterialValue_id, childSeries.QcControlMaterialValue_id)
			LEFT JOIN lis.v_QcControlMaterial QCM WITH(NOLOCK) on QCM.QcControlMaterial_id = QCMV.QcControlMaterial_id
			LEFT JOIN lis.v_AnalyzerTest AT WITH(NOLOCK) on AT.AnalyzerTest_id = QCMV.AnalyzerTest_id
			LEFT JOIN dbo.v_UslugaComplex UC WITH(NOLOCK) on UC.UslugaComplex_id = QCMV.UslugaComplex_id
			LEFT JOIN dbo.v_MedService MS WITH(NOLOCK) on MS.MedService_id = QCMV.MedService_id
			WHERE (1=1) $where
			ORDER BY childSeries.QcControlSeries_begDT desc, childSeries.QcControlSeries_endDT
		";

		//echo getDebugSQL($query, $params); exit;
		return $this->queryResult($query, $params);
	}


	/**
	 * Загрузка комбобокса по ид родителя
	 */
	public function doLoadCombo($data = array()) {
		$params = [
			'QcControlSeries_pid' => $data['QcControlSeries_pid']
		];
		$query = "
			select 
				QCS.QcControlSeries_id,
				QCS.QcControlSeries_pid,
				begDT.begDT,
				endDT.endDT,
				QCS.QcControlSeries_endDT,
				QCS.QcControlSeries_begDT,
				QCS.QcControlStage_id,
				QCS.QcControlSeries_IsControlPassed,
				QCS.QcControlSeries_Xcp,
				QCS.QcControlSeries_S,
				QCS.QcControlSeries_CV,
				QCS.QcControlSeries_B,
				QcStage.QcControlStage_Name
			from lis.v_QcControlSeries QCS with (nolock)
			left join lis.v_QcControlStage QcStage with(nolock) on QcStage.QcControlStage_id = QCS.QcControlStage_id
			outer apply (
				select TOP 1
					MIN(QcControlSeriesValue_setDT) as begDT
				from
					lis.QcControlSeriesValue with(nolock)
				where
					QcControlSeries_id = QCS.QcControlSeries_id) begDT
			outer apply (
				select TOP 1
					MAX(QcControlSeriesValue_setDT) as endDT
				from
					lis.QcControlSeriesValue with(nolock)
				where
					QcControlSeries_id = QCS.QcControlSeries_id) endDT
			where QCS.QcControlSeries_pid = :QcControlSeries_pid
			order by QCS.QcControlSeries_begDT desc
		";
		//echo getDebugSQL($query, $params); exit;
		return $this->queryResult($query, $params);
	}

	/**
	 * Проверки и другая логика перед сохранением объекта
	 *
	 * При запросах данных этого объекта из БД будут возвращены старые данные!
	 * @param array $data Массив входящих параметров
	 * @throws Exception
	 */
	protected function _beforeSave($data = array()) {

		$this->QcControlSeries_Xcp = isset($data['QcControlSeries_Xcp']) ? $data['QcControlSeries_Xcp'] : null;
		$this->QcControlSeries_S = isset($data['QcControlSeries_S']) ? $data['QcControlSeries_S'] : null;
		$this->QcControlSeries_B = isset($data['QcControlSeries_B']) ? $data['QcControlSeries_B'] : null;
		$this->QcControlSeries_CV = isset($data['QcControlSeries_CV']) ? $data['QcControlSeries_CV'] : null;

		//Не сохраняем в третей стадии для текущей серии
		if( !empty($data['NextStage_id']) && $data['QcControlStage_id'] == self::STAGE_QC ) {
			unset($data['QcControlSeries_CV']);
			unset($data['QcControlSeries_B']);
			unset($data['QcControlSeries_S']);
			unset($data['QcControlSeries_Xcp']);
		}

		if (!empty($data)) {
			$this->applyData($data);
		}

		$id = $this->getAttribute(self::ID_KEY);
		$pid = $this->getAttribute('qccontrolseries_pid');
		$isChildren = !empty($pid);

		//при переходе на новую стадию закрываем текущую дочернюю серию и создаем новую
		if(!empty($data['NextStage_id'])) {
			$this->nextstage_id = $data['NextStage_id'];
			if($isChildren) {
				$this->setAttribute('enddt', date('Y-m-d H:i:s'));
			}
		}

		//проставляем begdt при добавлении
		if(empty($id)) {
			$this->setAttribute('begdt',date('Y-m-d H:i:s'));
		}

		//при сохранении дочернего элемента 
		if($isChildren) {
			$this->setAttribute('qccontrolmaterialvalue_id', null);
			$this->setAttribute('qccontrolseries_name',null);
		}


	}

	/**
	 * Логика после успешного выполнения запроса сохранения объекта
	 *
	 * Если сохранение выполняется внутри транзакции,
	 * то при запросах данных этого объекта из БД будут возвращены старые данные!
	 * @param array $result Результат выполнения запроса
	 * @throws Exception
	 */
	protected function _afterSave($result) {
		//создаем дочернюю серию со следующей стадией
		$nextStage = $this->nextstage_id;
		$this->nextstage_id = null;
		if(!empty($nextStage)) {
			$pid = $this->getAttribute('qccontrolseries_pid');
			$id = $this->getAttribute(self::ID_KEY);
			$this->reset();
			$session = getSessionParams();
			
			$params = [
				'QcControlSeries_pid' => empty($pid) ? $id : $pid,
				'QcControlStage_id' => $nextStage,
				'QcControlSeries_begDT' => date('Y-m-d H:i:s'),
				'session' => $session['session']
			];
			if($nextStage == self::STAGE_QC) {
				$params['QcControlSeries_Xcp'] = $this->QcControlSeries_Xcp;
				$params['QcControlSeries_S'] = $this->QcControlSeries_S;
				$params['QcControlSeries_B'] = $this->QcControlSeries_B;
				$params['QcControlSeries_CV'] = $this->QcControlSeries_CV;
			}

			$Series_model = new QcControlSeries_model();
			$tmp = $Series_model->doSave($params,false);
			if(!empty($tmp['Error_Msg'])){
				throw new Exception('Ошибка при сохранении');
			}
			$this->setAttribute(self::ID_KEY, $tmp['QcControlSeries_id']);
		}
	}

	/**
	 * Выполнение логики сценария
	 * @param array $data Массив, полученный методом ProcessInputData контроллера
	 * @return array||boolean
	 */
	public function doScenario($data = array()) {
		$result = parent::doScenario($data);
		switch($this->getScenario()) {
			case self::SCENARIO_CALCULATE:
				$result = $this->calculateForStage($data);
				break;
		}
		return $result;
	}



	/**
	 * Вычисляет значения вариации, сходимости, среднеквадратичного отклонения, среднеего значения
	 * по id выбираем одну запись, по pid выбираем все дочерние
	 * @param array $data
	 * @return array
	 */
	function calculateForStage($data) { 

		$params = [];
		$isFirstSubStage = null;
		$valueField = 'UslugaTest_ResultValue';

		if(empty($data['QcControlStage_id'])) {
			return $this->createError('', 'Не выбрана стадия');
		}
		$stage = $data['QcControlStage_id'];

		if(!empty($data['QcControlSeries_id'])) {
			$params['QcControlSeries_id'] = $data['QcControlSeries_id'];
		} else {
			return $this->createError('', 'Не выбрана серия');
		}

		$material = $this->doLoadData($params);
		if(!$material) {
			return $this->createError('','Ошибка при выполнении запроса');
		}

		$isAttested = $material[0]['QcControlMaterial_IsAttested'] == 2;
		$materialX = $material[0]['QcControlMaterialValue_X'];
		$materialCV = null;
		$materialB = null;

		$this->load->model('QcControlSeriesValue_model', 'seriesValueModel');

		switch($stage) {

			case self::STAGE_CONVERGENCE:
				$params['count'] = 10;
				$results = $this->seriesValueModel->loadDataForCalculate($params);

				if(count($results) < 10) {
					return $this->createError('','Для расчета нужно минимум 10 значений');
				}

				$materialCV = $material[0]['QcControlMaterialValue_CV10'];
				$date = null;
				foreach ($results as $key => $object) {
					if(!$date) {
						$date = $object['setDate'];
					}
					if($date != $object['setDate']) {
						return $this->createError('', 'Результаты должны быть из одной серии');
					}
				}
				break;

			case self::STAGE_INST_SERIES:
				$params['count'] = 20;
				$results = $this->seriesValueModel->loadDataForCalculate($params);

				if(count($results) < 10) {
					return $this->createError('','Для расчета нужно минимум 10 значений');
				}
				if(!QcControlSeriesValue_model::dateValidateForInstallSeries($results)) {
					return $this->createError('', 'Разрешено не более 3х измерений в день для установочной серии');
				}
				$isFirstSubStage = count($results) < 20;
				$materialCV = $isFirstSubStage ? $material[0]['QcControlMaterialValue_CV10'] : $material[0]['QcControlMaterialValue_CV20'];
				$materialB = $isFirstSubStage ? $material[0]['QcControlMaterialValue_B10'] : $material[0]['QcControlMaterialValue_B20'];
				break;

			case self::STAGE_QC:
				$params['count'] = 30;
				$results = $this->seriesValueModel->loadDataForCalculate($params);
				if(count($results) < 30) {
					return $this->createError('', 'Для расчета необходимо 30 результатов');
				}
				break;
		}

		$Xcp = 0;
		$S = 0;
		$CV = null;
		$B = null;
		$msg = '';

		//среднее значение Xcp
		foreach ($results as $object) {
			$Xcp += $object[$valueField];
		}
		$Xcp /= count($results);

		//среднеквадратичное отклонение S
		foreach ($results as $object) {
			$S += pow($object[$valueField] - $Xcp, 2);
		}
		$S = sqrt($S / (count($results) - 1));

		//вариация CV
		$CV = ($S / $Xcp) * 100;

		if($isAttested) {
			$B = 100 * ($Xcp - $materialX) / $Xcp;
		}

		//Признак завершения стадии
		$controlPassed = null;
		switch($stage) {
			case self::STAGE_CONVERGENCE:
				$controlPassed = 0.5 * $materialCV > $CV ? 2 : 1;
				$msg = $controlPassed == 1 ? 'CV' : '';
				break;
			case self::STAGE_INST_SERIES:
				$flag = $materialCV > $CV ;
				$msg = !$flag ? 'CV' : '';

				if($isAttested) {
					$flag = $flag && $materialB > abs($B);
					$msg = !$flag ? 'B' : '';
				}

				if(!$isFirstSubStage && $flag) {
					foreach($results as $res) {
						if($this->seriesValueModel->isRule($res[$valueField], $Xcp, 3 * $S)) {
							$flag = false;
							$msg = '3S';
							break;
						}
					}
					$controlPassed = $flag ? 2 : null;
				}
				break;
		}

		$params = [
			'success' => true,
			'QcControlSeries_B' => $B,
			'QcControlSeries_CV' => $CV,
			'QcControlSeries_Xcp' => $Xcp,
			'QcControlSeries_S' => $S,
			'QcControlSeries_IsControlPassed' => $controlPassed,
			'msg' => $msg
		];
		return $params;
	}

	/**
	 * Логика после дизейбла. Дизейблим родителя
	 */
	function _afterDisable ($data) {
		$pid = $this->getAttribute('qccontrolseries_pid');
		if(empty($pid)) return;
		$params = [
			'QcControlSeries_id' => $pid,
			'session' => getSessionParams()['session']
		];
		$Series_model = new QcControlSeries_model();
		$result = $Series_model->doDisable($params);
		if(!empty($result['Error_Msg'])) {
			throw new Exception('Ошибка при сохранении');
		}
	}
}
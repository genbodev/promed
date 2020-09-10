<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * QcRuleLpu_model - модель для работы с контрольными материалами для анализаторов
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
class QcRuleLpu_model extends Scenario_model {
	var $table_name = 'QcRuleLpu';
	var $scheme = 'lis';

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		$this->_setScenarioList(array(
			self::SCENARIO_DO_SAVE,
			self::SCENARIO_DO_SAVE_GRID,
			self::SCENARIO_LOAD_GRID,
			self::SCENARIO_DELETE
		));
	}

	/**
	 * Определение правил для входящих параметров
	 * @param string $name
	 * @return array
	 */
	function getInputRules($name) {
		$rules = SwModel::getInputRules($name);
		switch($name) {
			case self::SCENARIO_DO_SAVE_GRID:
				$rules = $this->getInputRulesByAttributes('data');
				break;
		}
		return $rules;
	}

	/**
	 * Возвращает массив описаний всех используемых атрибутов объекта в формате ключ => описание
	 * @return array
	 */
	static function defAttributes() {
		return array(
			'data' => array(
				'properties' => array(
					self::PROPERTY_NOT_SAFE,
					self::PROPERTY_NOT_LOAD
				),
				'label' => 'Данные грида',
				'save' => '',
				'type' => 'string'
			),
			'object' => array(
				'properties' => array(
					self::PROPERTY_NOT_SAFE,
					self::PROPERTY_NOT_LOAD
				),
				'label' => 'Таблица',
				'save' => '',
				'type' => 'string'
			),
			self::ID_KEY => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_NOT_NULL
				),
				'alias' => 'QcRuleLpu_id',
				'label' => 'Идентификатор правила МО',
				'save' => 'trim',
				'type' => 'id'
			),
			'qcrule_id' => array(
				'properties' => array(
					self::PROPERTY_NOT_NULL,
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'QcRule_id',
				'label' => 'Идентификатор правила',
				'save' => '',
				'type' => 'id'
			),
			'qcrulelpu_isstrong' => array(
				'properties' => array(
					self::PROPERTY_NOT_NULL,
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'QcRuleLpu_isStrong',
				'label' => 'Строгое правило',
				'save' => '',
				'type' => 'id'
			),
			'lpu_id' => array(
				'properties' => array(
					self::PROPERTY_NOT_NULL,
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'Lpu_id',
				'label' => 'МО',
				'save' => '',
				'type' => 'id'
			)
		);
	}

	/**
	 * Загрузка данных
	 */
	function doLoadData($data = array()) {
		$params = [
			'Lpu_id' => $data['Lpu_id']
		];

		$query = "
			SELECT 
				QCRLpu.QcRuleLpu_id as \"QcRuleLpu_id\",
				QCR.QcRule_id as \"QcRule_id\",
				QCR.QcRule_Name as \"QcRule_Name\",
				case when QCRLpu.QcRuleLpu_isStrong = 2
					then 1
					else 0
				end as \"QcRuleLpu_isStrong\",
				case when QCRLpu.QcRuleLpu_id is null 
					then 0
					else 1
				end as \"isOn\"
			FROM lis.v_QcRule QCR
			LEFT JOIN lis.v_QcRuleLpu QCRLpu
				ON QCRLpu.QcRule_id = QCR.QcRule_id
				AND QCRLpu.Lpu_id = :Lpu_id
		";
		//echo getDebugSQL($query, $params); exit;
		return $this->queryResult($query,$params);
	}

	/**
	 * Сохранение правил для МО и лабораторий
	 */
	function doSaveGrid($data = array()) {
		if(empty($data['data']))
			return false;

		try {
			//запускаем транзакцию, значит надо отключить остальные транзакции
			if ( !$this->db->trans_begin() ) {
				throw new Exception('Ошибка при попытке запустить транзакцию');
			}
			$rules = [];

			$rules = json_decode($data['data'], true);

			if(!count($rules)) return false;

			$response = [
				'success' => true,
				'data' => array() //
			];
			foreach($rules as $rule) {
				$rule['session'] = $data['session'];
				$rule['pmUser_id'] = $data['pmUser_id'];
				$isStrong = !empty($rule['QcRuleLpu_isStrong']) ? $rule['QcRuleLpu_isStrong'] : 0;
				$rule['QcRuleLpu_isStrong'] = $isStrong ? 2 : 1;
				$rule['Lpu_id'] = $this->getSessionParams()['lpu_id'];

				//проверка на уникальность по МО и Правилу
				$QcRuleLpu_id = $this->isExistObjectRecord('QcRuleLpu', [
					'QcRule_id' => $rule['QcRule_id'],
					'Lpu_id' => $rule['Lpu_id']
				], 'lis');
				$rule['QcRuleLpu_id'] = $QcRuleLpu_id;

				$result = [];

				//Если включено то сохраняем, иначе удаляем
				if(!empty($rule['isOn'])) {


					$this->setScenario(self::SCENARIO_DO_SAVE);
					$result = $this->doSave($rule,false);

					$id = !empty($result['QcRuleLpu_id']) ? $result['QcRuleLpu_id'] : null;
					$isStrong = !empty($result['QcRuleLpu_id']) ? $isStrong : false;

					$respRule = [
						'QcRule_id' => $rule['QcRule_id'],
						'QcRuleLpu_id' => $id,
						'QcRuleLpu_isStrong' => $isStrong
					];

				} else {

					//удаляем если передан идентификатор
					$this->setScenario(self::SCENARIO_DELETE);
					if(!empty($rule['QcRuleLpu_id'])) {
						$result = $this->doDelete($rule, false);
					}
					$respRule = [
						'QcRule_id' => $rule['QcRule_id'],
						'QcRuleLpu_id' => null,
						'QcRuleLpu_isStrong' => null
					];
				}
				array_push($response['data'], $respRule);

				if(!empty($result['Error_Code']) || !empty($result['Error_Msg'])) {
					throw new Exception('Ошибка при сохранении',666);
				}
			}
			if ( !$this->db->trans_commit() ) {
				throw new Exception('Не удалось зафиксировать транзакцию', 500);
			}
			return $response;
		} catch(Exception $e) {
			$this->rollbackTransaction();
			return array('Error_Message'=>$e->getMessage(),'Error_Code'=>$e->getCode());
		}
	}
}
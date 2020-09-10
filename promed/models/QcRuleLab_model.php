<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * QcRuleLab_model - модель для работы с контрольными материалами для анализаторов
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
class QcRuleLab_model extends Scenario_model {
	var $table_name = 'QcRuleLab';
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
			self::ID_KEY => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_NOT_NULL
				),
				'alias' => 'QcRuleLab_id',
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
			'qcrulelab_isstrong' => array(
				'properties' => array(
					self::PROPERTY_NOT_NULL,
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'QcRuleLab_isStrong',
				'label' => 'Строгое правило',
				'save' => '',
				'type' => 'id'
			),
			'medservice_id' => array(
				'properties' => array(
					self::PROPERTY_NOT_NULL,
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'MedService_id',
				'label' => 'Служба',
				'save' => '',
				'type' => 'id'
			)
		);
	}

	/**
	 * Определение правил для входящих параметров
	 * @param string $name
	 * @return array
	 */
	function getInputRules($name) {
		$rules = SwModel::getInputRules($name);

		switch ($name) {
			case self::SCENARIO_LOAD_GRID:
				$rules = $this->getInputRulesByAttributes(['medservice_id']);
				break;
			case self::SCENARIO_DO_SAVE_GRID:
				$rules = $this->getInputRulesByAttributes(['data','medservice_id']);
				break;
		}
		return $rules;
	}

	/**
	 * Загрузка данных
	 */
	function doLoadData($data = array()) {
		$params = [
			'MedService_id' => $data['MedService_id']
		];

		$query = "
			SELECT 
				QCRLab.QcRuleLab_id,
				QCR.QcRule_id,
				QCR.QcRule_Name,
				case when QCRLab.QcRuleLab_isStrong = 2
					then 1
					else 0
				end as QcRuleLab_isStrong,
				case when QCRLab.QcRuleLab_id is null 
					then 0
					else 1
				end as isOn
			FROM lis.v_QcRule QCR with(nolock)
			LEFT JOIN lis.v_QcRuleLab QCRLab with(nolock)
				ON QCRLab.QcRule_id = QCR.QcRule_id
				AND QCRLab.MedService_id = :MedService_id
		";
		return $this->queryResult($query,$params);
	}

	/**
	 * Сохранение правил для МО и лабораторий
	 */
	function doSaveGrid($data = array()) {
		try {
			//запускаем транзакцию, значит надо отключить остальные транзакции
			if ( !$this->db->trans_begin() ) {
				throw new Exception('Ошибка при попытке запустить транзакцию');
			}
			$rules = [];

			if(empty($data['data'])) return false;

			$rules = json_decode($data['data'], true);

			if(!count($rules)) return false;

			$response = [
				'success' => true,
				'data' => array() //
			];
			foreach($rules as $rule) {
				$rule['session'] = $data['session'];
				$isStrong = !empty($rule['QcRuleLab_isStrong']) ? $rule['QcRuleLab_isStrong'] : 0;
				$rule['QcRuleLab_isStrong'] = $isStrong ? 2 : 1;
				$rule['MedService_id'] = $data['MedService_id'];

				//проверка на уникальность по МО и Правилу
				$QcRuleLab_id = $this->isExistObjectRecord('QcRuleLab', [
					'QcRule_id' => $rule['QcRule_id'],
					'MedService_id' => $rule['MedService_id']
				], 'lis');
				$rule['QcRuleLab_id'] = $QcRuleLab_id;

				if(!empty($rule['isOn'])) {

					//если правило включено то добавляем или обновляем
					$this->setScenario(self::SCENARIO_DO_SAVE);
					$result = $this->doSave($rule, false);

					$id = !empty($result['QcRuleLab_id']) ? $result['QcRuleLab_id'] : null;
					$isStrong = !empty($result['QcRuleLab_id']) ? $isStrong : false;

					$respRule = [
						'QcRule_id' => $rule['QcRule_id'],
						'QcRuleLab_id' => $id,
						'QcRuleLab_isStrong' => $isStrong
					];

				} else {

					//иначе удаляем, если передан идентификатор
					$this->setScenario(self::SCENARIO_DELETE);
					if(!empty($rule['QcRuleLab_id'])) {
						$result = $this->doDelete($rule, false);
					}
					$respRule = [
						'QcRule_id' => $rule['QcRule_id'],
						'QcRuleLab_id' => null,
						'QcRuleLab_isStrong' => null
					];
				}
				array_push($response['data'], $respRule);

				if(!empty($result['Error_Code']) || !empty($result['Error_Msg'])) {
					throw new Exception($result['Error_Msg'],$result['Error_Code']);
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
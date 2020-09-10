<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* AnalyzerQualityControl_model - модель для работы с формой "Контрольные материалы" (swAnalyzerQualityControlWindow)
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
class AnalyzerQualityControl_model extends Scenario_model {

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		$this->_setScenarioList(array(
			self::SCENARIO_DO_SAVE,
			self::SCENARIO_LOAD_EDIT_FORM
		));
	}

	/**
	 * Возвращает массив описаний всех используемых атрибутов объекта в формате ключ => описание
	 * @return array
	 */
	static function defAttributes() {
		return array(
			'medservice_id' => array(
				'properties' => array(
					self::PROPERTY_NOT_LOAD
				),
				'alias' => 'MedService_id',
				'label' => 'Лаборатория',
				'save' => 'required',
				'type' => 'id'
			),
			'medservice_isgeneralqcrule' => array(
				'properties' => array(
					self::PROPERTY_NOT_LOAD
				),
				'alias' => 'MedService_IsGeneralQcRule',
				'label' => 'Использовать общие правила',
				'save' => '',
				'type' => 'string',
				//'applyMethod' => '_applyCheckbox'
			)
		);
	}

	/**
	 * Получение правил для входящих параметров
	 */
	function getInputRules($name) {
		$rules = array();
		switch($name) {
			case self::SCENARIO_LOAD_EDIT_FORM:
			case self::SCENARIO_DO_SAVE:
				$rules = $this->getInputRulesByAttributes(['medservice_id', 'medservice_isgeneralqcrule']);
			break;
		}
		return $rules;
	}

	/**
	 * Сохранение
	 */
	function doSave($data = array(), $isAllowTransaction = true) {
		$isGeneral = $data['MedService_IsGeneralQcRule'] == 'on' ? 2 : 1;
		$params = [
			'MedService_id' => $data['MedService_id'],
			'MedService_IsGeneralQcRule' => $isGeneral,
			'pmUser_id' => $data['pmUser_id']
		];
		return $this->swUpdate('MedService', $params, true);
	}

	/**
	 * Загрузка формы или грида
	 */
	function doLoadData($data = array()) {

		$params = [];
		$where = '';

		if(empty($data['MedService_id'])) {
			return $this->createError('', 'Выберите лабораторию');
		}
		
		$params['MedService_id'] = $data['MedService_id'];
		$where .= ' and MS.MedService_id = :MedService_id';
		

		$query = "
			SELECT
				MedService_id,
				Lpu_id,
				case when MS.MedService_IsGeneralQcRule = 2
					then 1 
					else 0
				end as MedService_IsGeneralQcRule
			FROM v_MedService MS WITH(nolock)
			WHERE (1=1) $where
		";

		return $this->queryResult($query, $params);
	}
}
<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* QcControlMaterial_model - модель для работы с формой "Контрольные материалы" (swQcControlMaterialWindow)
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
class QcControlMaterial_model extends Scenario_model {
	var $table_name = 'QcControlMaterial';
	var $scheme = 'lis';
	var $saveAsNewObject = true;

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		$this->_setScenarioList(array(
			self::SCENARIO_DO_SAVE,
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
				'alias' => 'QcControlMaterial_id',
				'label' => 'Идентификатор',
				'save' => 'trim',
				'type' => 'id'
			),
			'qccontrolmaterial_name' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'QcControlMaterial_Name',
				'label' => 'Наименование материала',
				'save' => '',
				'type' => 'string'
			),
			'qccontrolmaterialtype_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'QcControlMaterialType_id',
				'label' => 'Тип материала',
				'save' => '',
				'type' => 'id'
			),
			'lpu_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'Lpu_id',
				'label' => 'МО',
				'save' => '',
				'type' => 'id'
			),
			'qccontrolmaterial_isattested' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'QcControlMaterial_IsAttested',
				'label' => 'Аттестованный',
				'save' => '',
				'type' => 'checkbox',
				'applyMethod' => '_applyIsAttested'
			),
			'qccontrolmaterial_lotnum' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'QcControlMaterial_LotNum',
				'label' => 'Номер лота',
				'save' => '',
				'type' => 'string'
			),
			'qccontrolmaterial_catalognum' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'QcControlMaterial_CatalogNum',
				'label' => 'Каталожный номер',
				'save' => '',
				'type' => 'string'
			),
			'qccontrolmaterial_expdate' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'QcControlMaterial_ExpDate',
				'label' => 'Срок годности',
				'save' => '',
				'type' => 'date'
			),
			'qccontrolmaterialvalue_id' => array(
				'properties' => array(
					self::PROPERTY_NOT_SAFE,
					self::PROPERTY_NOT_LOAD
				),
				'alias' => 'QcControlMaterialValue_id',
				'label' => 'Методика',
				'save' => '', //todo validate on save
				'type' => 'int'
			),
			'begdt' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
					self::PROPERTY_DATE_TIME,
					self::PROPERTY_NEED_TABLE_NAME
				),
				'alias' => 'QcControlMaterial_begDT',
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
				'alias' => 'QcControlMaterial_endDT',
				'label' => 'Дата начала действия записи',
				'save' => '',
				'type' => 'date'
			)

		);
	}

	/**
	 * Обработка чекбокса IsAttested
	 */
	function _applyIsAttested($data) {
		return $this->_applyCheckboxValue($data, 'qccontrolmaterial_isattested');
	}


	/**
	 * Получение правил для входящих параметров
	 */
	function getInputRules($name) {
		$rules = parent::getInputRules($name);

		switch($name) {
			case self::SCENARIO_LOAD_GRID:
			case self::SCENARIO_LOAD_COMBO_BOX:
				$attributes = [
					self::ID_KEY,
					'lpu_id',
					'qccontrolmaterialvalue_id',
					'qccontrolmaterial_name',
					'qccontrolmaterialtype_id'
				];
				$rules = $this->getInputRulesByAttributes($attributes);
				break;
		}

		return $rules;
	}

	/**
	 * Загрузка формы и грида (Контроль качества/Контрольные материалы)
	 */
	function doLoadData($data = array()) {

		$params = [];
		$params['Lpu_id'] = $this->getSessionParams()['lpu_id'];
		$where = '';
		$join = '';

		if(!empty($data['QcControlMaterial_id'])) {
			$params['QcControlMaterial_id'] = $data['QcControlMaterial_id'];
			$where .= ' and QCM.QcControlMaterial_id = :QcControlMaterial_id';
		}

		if(!empty($data['QcControlMaterialValue_id'])) {
			$params['QcControlMaterialValue_id'] = $data['QcControlMaterialValue_id'];
			$where .= ' and QCMV.QcControlMaterialValue_id = :QcControlMaterialValue_id';
			$join .= 'LEFT JOIN lis.QcControlMaterialValue QCMV WITH(NOLOCK) ON QCMV.QcControlMaterial_id = QCM.QcControlMaterial_id';
		}

		if(!empty($data['QcControlMaterial_Name'])) {
			$params['QcControlMaterial_Name'] = $data['QcControlMaterial_Name'];
			$where .= " and QCM.QcControlMaterial_Name like '%'+:QcControlMaterial_Name+'%'";
		}

		if(!empty($data['QcControlMaterialType_id'])) {
			$params['QcControlMaterialType_id'] = $data['QcControlMaterialType_id'];
			$where .= " and QCM.QcControlMaterialType_id = :QcControlMaterialType_id";
		}

		$query = "
			SELECT
				QCM.QcControlMaterial_id,
				QCM.QcControlMaterial_Name,
				QCM.QcControlMaterial_LotNum,
				QCM.QcControlMaterial_CatalogNum,
				case when QCM.QcControlMaterial_IsAttested = 2
					then 'true'
					else 'false'
				end as QcControlMaterial_IsAttested,
				convert(varchar(10),QCM.QcControlMaterial_ExpDate,104) as QcControlMaterial_ExpDate,
				convert(varchar(10),QCM.QcControlMaterial_begDT,104) as QcControlMaterial_begDT,
				convert(varchar(10),QCM.QcControlMaterial_endDT,104) as QcControlMaterial_endDT,
				QCM.QcControlMaterialType_id,
				QCMT.QcControlMaterialType_Name
			FROM lis.QcControlMaterial QCM WITH(NOLOCK)
			LEFT JOIN lis.QcControlMaterialType QCMT WITH(NOLOCK) ON QCMT.QcControlMaterialType_id = QCM.QcControlMaterialType_id
			{$join}
			WHERE QCM.Lpu_id = :Lpu_id {$where}
			ORDER BY QCM.QcControlMaterial_endDT asc, QCM.QcControlMaterial_id desc
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Проверка перед дизейблом
	 * @return null|Exception
	 */
	function _validateBeforeDisable() {

		if($this->saveAsNewObject) return;

		$params = array(
			'QcControlMaterial_id' => $this->getAttribute(self::ID_KEY)
		);

		$query = "
			SELECT *
			FROM lis.v_QcControlMaterial QCM
			left join lis.v_QcControlMaterialValue QMV on QMV.QcControlMaterial_id = QCM.QcControlMaterial_id
			left join lis.v_QcControlSeries QCS on QCS.QcControlMaterialValue_id = QMV.QcControlMaterialValue_id
			WHERE QCM.QcControlMaterial_id = :QcControlMaterial_id
				and QCM.QcControlMaterial_endDT is null
				and (QMV.QcControlMaterialValue_endDT is null
					or (QCS.QcControlSeries_id is not null and QCS.QcControlSeries_endDT is null))
		";

		$result = $this->getFirstResultFromQuery($query, $params);

		if($result) { 
			throw new Exception('Требуется удалить все связанные данные');
		}
	}
}
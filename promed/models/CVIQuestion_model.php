<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * CVIQuestion_model - модель для работы с формой Анкета КВИ
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Common
 * @access       public
 * @author       Magafurov Salavat
 * @version      25.03.2020
 */

require_once('Scenario_model.php');
class CVIQuestion_model extends Scenario_model
{
	var $table_name = 'CVIQuestion';

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		$this->_setScenarioList([ self::SCENARIO_DO_SAVE ]);
	}

	/**
	 * Возвращает массив описаний всех используемых атрибутов объекта в формате ключ => описание
	 * @return array
	 */
	static function defAttributes() {
		return array(
			self::ID_KEY => array(
				'properties' => [self::PROPERTY_NEED_TABLE_NAME],
				'label' => 'Идентификатор',
				'save' => '',
				'type' => 'int'
			),
			'person_id' => array(
				'alias' => 'Person_id',
				'label' => 'Пациент',
				'properties' => [self::PROPERTY_IS_SP_PARAM],
				'save' => '',
				'type' => 'int'
			),
			'cmpcallcard_id' => array(
				'alias' => 'CmpCallCard_id',
				'label' => 'Карта вызова',
				'properties' => [self::PROPERTY_IS_SP_PARAM],
				'save' => '',
				'type' => 'int'
			),
			'homevisit_id' => array(
				'alias' => 'HomeVisit_id',
				'label' => 'МО',
				'properties' => [self::PROPERTY_IS_SP_PARAM],
				'save' => '',
				'type' => 'int'
			)
		);
	}

}
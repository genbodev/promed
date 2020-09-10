<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * CVIQuestionAnswer_model - модель для работы с формой Анкета КВИ
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
class CVIQuestionAnswer_model extends Scenario_model
{
	var $table_name = 'CVIQuestionAnswer';

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
			'cviquestion_id' => array(
				'alias' => 'CVIQuestion_id',
				'label' => 'Анкета',
				'properties' => [self::PROPERTY_IS_SP_PARAM],
				'save' => 'required',
				'type' => 'int'
			),
			'cviquestiontype_id' => array(
				'alias' => 'CVIQuestionType_id',
				'label' => 'Вопросы',
				'properties' => [self::PROPERTY_IS_SP_PARAM],
				'save' => 'required',
				'type' => 'int'
			),
			'cviquestionanswer_refid' => array(
				'alias' => 'CVIQuestionAnswer_refId',
				'label' => 'Ссылка',
				'properties' => [self::PROPERTY_IS_SP_PARAM],
				'save' => '',
				'type' => 'int'
			),
			'cviquestionanswer_freeform' => array(
				'alias' => 'CVIQuestionAnswer_FreeForm',
				'label' => 'Строка',
				'properties' => [self::PROPERTY_IS_SP_PARAM],
				'save' => '',
				'type' => 'int'
			),
			'cviquestionanswer_setdt' => array(
				'alias' => 'CVIQuestionAnswer_setDT',
				'label' => 'Дата',
				'properties' => [self::PROPERTY_IS_SP_PARAM],
				'save' => '',
				'type' => 'int'
			)
		);
	}
}
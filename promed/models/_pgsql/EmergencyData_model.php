<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package      PromedWeb
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @link         http://swan.perm.ru/PromedWeb
 */

/**
 * EmergencyData_model - Модель "Данные о вызове скорой помощи"
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Александр Пермяков
 * @version      09.2014
 *
 * @property-read int $id идентификатор
 * @property-read int $TimetableStac_id идентификатор бирки стационара
 * @property-read int $Person_id человек
 * @property-read string $BrigadeNum номер бригады
 * @property-read string $CallNum номер вызова
 * @property-read int $CmpDiag_id диагноз скорой
 * @property-read int $Diag_id диагноз МКБ-10
 * @property-read int $Person_lid человек идентифицированный в ЛПУ
 */
class EmergencyData_model extends SwPgModel
{
	/**
	 * 	Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->_setScenarioList(array(
			self::SCENARIO_SET_ATTRIBUTE,
			/*self::SCENARIO_AUTO_CREATE,
			self::SCENARIO_DO_SAVE,
			self::SCENARIO_DELETE,*/
		));
	}

	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName()
	{
		return 'EmergencyData';
	}

	/**
	 * Возвращает список всех используемых ключей атрибутов объекта
	 * @todo доработать описание
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = array(
			self::ID_KEY => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_NOT_NULL,
				),
				'alias' => 'EmergencyData_id',
				'label' => 'Идентификатор',
				'save' => 'trim',
				'type' => 'id'
			)
		);
		$arr['emergencydata_brigadenum'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_NOT_NULL,
			),
			'alias' => 'EmergencyData_BrigadeNum',
		);
		$arr['emergencydata_callnum'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_NOT_NULL,
			),
			'alias' => 'EmergencyData_CallNum',
		);
		$arr['timetablestac_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_NOT_NULL,
			),
			'alias' => 'TimetableStac_id',
		);
		$arr['person_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Person_id',
		);
		$arr['person_lid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Person_lid',
		);
		$arr['cmpdiag_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'CmpDiag_id',
		);
		$arr['diag_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Diag_id',
		);
		return $arr;
	}
}
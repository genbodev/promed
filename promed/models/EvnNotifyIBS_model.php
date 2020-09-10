<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package      PromedWeb
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @link         http://swan.perm.ru/PromedWeb
 */

require_once('EvnNotifyAbstract_model.php');

/**
 * EvnNotifyIBS_model - Модель "Извещение по ИБС"
 *
 * @package      IBS
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Александр Пермяков
 * @version      12.2014
 *
 * @property-read int $Diag_id Диагноз, справочник МКБ-10
 * @property-read DateTime $diagDate Дата установки
 * @property-read DateTime $firstDate Дата возникновения симптомов до установления диагноза
 * @property-read int $IBSDiagConfType_id Способ установления диагноза
 * @property-read int $IBSCRIType_id Наличие ХПН
 * @property-read int $IsHyperten Артериальная гипертензия (Да/Нет)
 * @property-read string $Treatment Назначенное лечение
 */
class EvnNotifyIBS_model extends EvnNotifyAbstract_model
{
	/**
	 * Возвращает список всех используемых ключей атрибутов объекта
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();
		$arr[self::ID_KEY]['alias'] = 'EvnNotifyIBS_id';
		$arr['pid']['alias'] = 'EvnNotifyIBS_pid';
		$arr['setdate']['alias'] = 'EvnNotifyIBS_setDate';
		$arr['disdt']['alias'] = 'EvnNotifyIBS_disDT';
		$arr['diddt']['alias'] = 'EvnNotifyIBS_didDT';
		$arr['nidate']['alias'] = 'EvnNotifyIBS_niDate';
		$arr['diag_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Diag_id',
			'label' => 'Диагноз',
			'save' => 'trim|required',
			'type' => 'id'
		);
		$arr['diagdate'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_DATE_TIME,
			),
			'applyMethod'=>'_applyDiagDate',
			'alias' => 'EvnNotifyIBS_diagDate',
			'label' => 'Дата установки',
			'save' => 'trim|required',
			'type' => 'date'
		);
		$arr['firstdate'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_DATE_TIME,
			),
			'applyMethod'=>'_applyFirstDate',
			'alias' => 'EvnNotifyIBS_firstDate',
			'label' => 'Дата возникновения симптомов до установления диагноза',
			'save' => 'trim|required',
			'type' => 'date'
		);
		$arr['IBSdiagconftype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'IBSDiagConfType_id',
			'label' => 'Способ установления диагноза',
			'save' => 'trim|required',
			'type' => 'id'
		);
		$arr['IBScritype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'IBSCRIType_id',
			'label' => 'Наличие ХПН|required',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['ishyperten'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnNotifyIBS_IsHyperten',
			'label' => 'Артериальная гипертензия',
			'save' => 'trim|required',
			'type' => 'id'
		);
		$arr['treatment'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnNotifyIBS_Treatment',
			'label' => 'Назначенное лечение',
			'save' => 'trim',
			'type' => 'string'
		);
		return $arr;
	}

	/**
	 * Определение идентификатора класса события
	 * @return int
	 */
	static function evnClassId()
	{
		return 172;
	}

	/**
	 * Определение кода класса события
	 * @return string
	 */
	static function evnClassSysNick()
	{
		return 'EvnNotifyIBS';
	}

	/**
	 * Определение типа заболевания
	 * @return string
	 */
	function getMorbusTypeSysNick()
	{
		return 'ibs';
	}

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Извлечение даты из входящих параметров
	 * @param array $data
	 * @return bool
	 */
	protected function _applyFirstDate($data)
	{
		return $this->_applyDate($data, 'firstdate');
	}

	/**
	 * Извлечение даты из входящих параметров
	 * @param array $data
	 * @return bool
	 */
	protected function _applyDiagDate($data)
	{
		return $this->_applyDate($data, 'diagdate');
	}

	/**
	 * Получение данных для формы
	 */
	function doLoadEditForm($data)
	{
		$response = parent::doLoadEditForm($data);
		$response[0]['Diag_id'] = $this->Diag_id;
		$response[0]['EvnNotifyIBS_diagDate'] = $this->diagDate->format('d.m.Y');
		$response[0]['EvnNotifyIBS_firstDate'] = $this->firstDate->format('d.m.Y');
		$response[0]['IBSDiagConfType_id'] = $this->IBSDiagConfType_id;
		$response[0]['IBSCRIType_id'] = $this->IBSCRIType_id;
		$response[0]['EvnNotifyIBS_IsHyperten'] = $this->IsHyperten;
		$response[0]['EvnNotifyIBS_Treatment'] = $this->Treatment;
		return $response;
	}
}
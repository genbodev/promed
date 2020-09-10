<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package      PromedWeb
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @link         http://swan.perm.ru/PromedWeb
 */

require_once('EvnAbstract_model.php');
/**
 * EvnUslugaAbstract_model - Модель "Оказание услуги"
 *
 * Содержит методы и свойства общие для всех объектов,
 * классы которых наследуют класс EvnUsluga
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Александр Пермяков
 * @version      10.2014
 *
 * @property-read int $rid КВС или ТАП
 * @property-read int $pid Движение в отделении (профильном или приемном) или посещение
 * @property-read int $EvnDirection_id Направление, по которому была оказана услуга
 * @property-read int $EvnPrescr_id Назначение, по которому была оказана услуга
 * @property-read int $EvnCourse_id Курс назначений, по которому была оказана услуга
 * @property-read int $PayType_id Тип оплаты
 * @property-read int $UslugaPlace_id Место оказания услуги
 * @property-read int $MedPersonal_id Врач, оказавший услугу
 * @property-read int $LpuSection_uid Отделение, в котором оказана услуга
 * @property-read int $Lpu_uid МО, в котором оказана услуга
 * @property-read int $Org_uid Организация, в котором оказана услуга
 * @property-read int $UslugaComplex_id Услуга (используемый справочник услуг)
 * @property-read int $UslugaComplexTariff_id Тарифы на оказание услуг
 * @property-read int $CoeffTariff Коэффициент изменения тарифа на услугу
 * @property-read int $MesOperType_id Тип лечения
 * @property-read int $IsModern Услуга по модернизации (Да/Нет)
 * @property-read float $Kolvo Кол-во оказанных услуг
 * @property-read int $isCito Срочность (Да/Нет)
 * @property-read float $Price стоимость
 * @property-read float $Summa сумма
 * @property-read string $Result Результат
 * @property-read int $MedPersonal_sid врач, подписавший оказание услуги
 * @property-read int $IsVizitCode Признак, что это услуга с кодом посещения (Да/Нет)
 *
 * @property-read int $Usluga_id Услуга (устаревший справочник услуг)
 * @ property-read int $EvnPrescrTimeTable_id График исполнения назначения (не должно использоваться после реализации курсов назначений)
 *
 * @property-read EvnSection_model $parent Или EvnPS_model
 */
abstract class EvnUslugaAbstract_model extends EvnAbstract_model
{
	//protected $_parentClass = 'EvnSection_model';

    /**
     * Конструктор объекта
     */
    function __construct()
    {
        parent::__construct();
    }

	/**
	 * Проверка корректности данных модели для указанного сценария
	 * @throws Exception
	 */
	protected function _validate()
	{
		parent::_validate();
		if (in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_AUTO_CREATE))) {
			/*if ( empty($this->pid) ) {
				throw new Exception('Не указано родительское событие');
			}*/
			if ( empty($this->UslugaPlace_id) && self::SCENARIO_AUTO_CREATE == $this->scenario) {
				// по умолчанию Отделение ЛПУ	lpusection
				// $this->setAttribute('UslugaPlace_id', 1);
			}
			if ( empty($this->Kolvo) && self::SCENARIO_AUTO_CREATE == $this->scenario) {
				$this->setAttribute('Kolvo', 1);
			}
			if ( empty($this->Kolvo) ) {
				throw new Exception('Не указано количество');
			}
			if ( empty($this->PayType_id) && self::SCENARIO_AUTO_CREATE == $this->scenario) {
				// по умолчанию ОМС
				$this->setAttribute('PayType_id', $this->loadPayTypeIdBySysNick($this->payTypeSysNickOMS));
			}
			if ( empty($this->PayType_id) ) {
				throw new Exception('Не указан Тип оплаты');
			}
		}
	}

	/**
	 * Возвращает список всех используемых ключей атрибутов объекта
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();
		$arr[self::ID_KEY]['label'] = 'Событие оказания услуги';
		$arr[self::ID_KEY]['alias'] = 'EvnUsluga_id';
		$arr['pid']['alias'] = 'EvnUsluga_pid';
		$arr['rid']['alias'] = 'EvnUsluga_rid';
		$arr['setdate']['label'] = 'Дата оказания услуги';
		$arr['setdate']['alias'] = 'EvnUsluga_setDate';
		$arr['settime']['label'] = 'Время оказания услуги';
		$arr['settime']['alias'] = 'EvnUsluga_setTime';
		$arr['evndirection_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnDirection_id',
			'label' => 'Направление, по которому была оказана услуга',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['evnprescr_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPrescr_id',
			'label' => 'Назначение, по которому была оказана услуга',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['evncourse_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnCourse_id',
			'label' => 'Курс назначений, по которому была оказана услуга',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['paytype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'PayType_id',
			'label' => 'Тип оплаты',
			'save' => 'trim',//required
			'type' => 'id'
		);
		$arr['uslugaplace_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'UslugaPlace_id',
			'label' => 'Место оказания услуги',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['medpersonal_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'MedPersonal_id',
			'label' => 'Врач, оказавший услугу',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['lpusection_uid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'LpuSection_uid',
			'label' => 'Отделение, в котором оказана услуга',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['lpu_uid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Lpu_uid',
			'label' => 'МО, в котором оказана услуга',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['org_uid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Org_uid',
			'label' => 'Организация, в котором оказана услуга',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['usluga_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Usluga_id',
			'label' => 'Услуга',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['uslugacomplex_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'UslugaComplex_id',
			'label' => 'Услуга',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['uslugacomplextariff_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'UslugaComplexTariff_id',
			'label' => 'Тарифы на оказание услуг',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['mesopertype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'MesOperType_id',
			'label' => 'Тип лечения',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['medpersonal_sid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'MedPersonal_sid',
			'label' => 'Врач, подписавший оказание услуги',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['coefftariff'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnUsluga_CoeffTariff',
			'label' => 'Коэффициент изменения тарифа на услугу',
			'save' => 'trim',
			'type' => 'int'//numeric
		);
		$arr['iscito'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnUsluga_isCito',
			'label' => 'Срочность',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['ismodern'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnUsluga_IsModern',
			'label' => 'Услуга по модернизации',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['isvizitcode'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_NOT_SAFE,
			),
		);
		$arr['result'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnUsluga_Result',
			'label' => 'Результат',
			'save' => 'trim',
			'type' => 'string'
		);
		$arr['kolvo'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnUsluga_Kolvo',
			'label' => 'Количество оказанных услуг',
			'save' => 'trim',
			'type' => 'float'
		);
		$arr['price'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnUsluga_Price',
			'label' => 'Стоимость',
			'save' => 'trim',
			'type' => 'float'
		);
		$arr['summa'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnUsluga_Summa',
			'label' => 'Сумма',
			'save' => 'trim',
			'type' => 'float'
		);
		return $arr;
	}

	/**
	 * Определение идентификатора класса события
	 * @return int
	 */
	static function evnClassId()
	{
		return 21;
	}

	/**
	 * Определение кода класса события
	 * @return string
	 */
	static function evnClassSysNick()
	{
		return 'EvnUsluga';
	}
}
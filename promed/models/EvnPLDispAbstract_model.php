<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package      PromedWeb
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @link         http://swan.perm.ru/PromedWeb
 */

require_once('EvnPLAbstract_model.php');
/**
 * EvnPLAbstract_model - Модель абстрактного ТАП
 *
 * Содержит методы и свойства общие для всех объектов,
 * классы которых наследуют класс EvnPLBase
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Александр Пермяков
 * @version      08.2014
 *
 * @property int $VizitCount
 * @property int $IsFinish
 * @property int $Person_Age
 */
abstract class EvnPLDispAbstract_model extends EvnPLAbstract_model
{
    /**
     * Конструктор объекта
     */
    function __construct()
    {
        parent::__construct();
    }

	/**
	 * Возвращает список всех используемых ключей атрибутов объекта
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();
		$arr[self::ID_KEY]['alias'] = 'EvnPLDisp_id';
		$arr[self::ID_KEY]['label'] = 'Идентификатор талона диспансеризации';
		$arr['setdt'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_DATE_TIME,
			),
			'alias' => 'EvnPLDisp_setDT',
			'label' => 'Дата начала случая',
			'save' => 'required',
			'type' => 'date'
		);
		$arr['attachtype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'AttachType_id',
			'label' => 'Тип прикрепления',
			'save' => 'required',
			'type' => 'id',
			'default' => 2
		);
		$arr['lpu_aid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Lpu_aid',
			'label' => 'МО постоянного прикрепления',
			'save' => '',
			'type' => 'id'
		);
		$arr['isinreg'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
			),
			'alias' => 'EvnPLDisp_IsInReg',
		);
		$arr['consdt'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_DATE_TIME,
			),
			'alias' => 'EvnPLDisp_consDT',
			'label' => 'Дата подписания согласия',
			'save' => 'required',
			'type' => 'date'
		);
		$arr['dispclass_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'DispClass_id',
			'label' => 'Вид диспансеризации',
			'save' => 'required',
			'type' => 'id'
		);
		$arr['fid'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDisp_fid',
			'label' => 'Идентификатор карты предыдущего этапа',
			'save' => '',
			'type' => 'id'
		);
		$arr['ismobile'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDisp_IsMobile',
			'label' => 'Случай обслужен мобильной бригадой',
			'save' => '',
			'type' => 'id'
		);
		$arr['lpu_mid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Lpu_mid',
			'label' => 'МО мобильной бригады',
			'save' => '',
			'type' => 'id'
		);
		$arr['ispaid'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
			),
			'alias' => 'EvnPLDisp_IsPaid',
			'label' => 'Случай оплачен',
			'save' => '',
			'type' => 'int',
		);
		$arr['isrefusal'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDisp_IsRefusal',
			'label' => 'Отказ от всех услуг',
			'save' => '',
			'type' => 'id'
		);
		$arr['indexrep'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDisp_IndexRep',
			'label' => 'Признак повторной подачи',
			'save' => '',
			'type' => 'int',
		);
		$arr['indexrepinreg'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDisp_IndexRepInReg',
			'label' => 'Признак вхождения в реестр повторной подачи',
		);
		$arr['paytype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'PayType_id',
			'label' => 'Вид оплаты',
			'save' => 'required',
			'type' => 'id'
		);
		$arr['percent'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDisp_Percent',
			'label' => 'Процент количества пройденных осмотров / исследований',
			'save' => '',
			'type' => 'float'
		);
		$arr['medstafffact_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'MedStaffFact_id',
			'label' => 'Рабочее место врача',
			'save' => '',
			'type' => 'id'
		);
		$arr['evndirection_aid'] = array(
			'properties' => array(
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnDirection_aid',
			'label' => 'Отказ от всех услуг',
			'save' => '',
			'type' => 'id'
		);

		return $arr;
	}

	/**
	 * Определение идентификатора класса события
	 * @return int
	 */
	static function evnClassId()
	{
		return 7;
	}

	/**
	 * Определение кода класса события
	 * @return string
	 */
	static function evnClassSysNick()
	{
		return 'EvnPLDisp';
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updatePayTypeId($id, $value = null)
	{
		return $this->_updateAttribute($id, 'paytype_id', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateConsDT($id, $value = null)
	{
		return $this->_updateAttribute($id, 'consdt', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateSetDT($id, $value = null)
	{
		return $this->_updateAttribute($id, 'setdt', $value);
	}

	/**
	 * Проверка однотипная, вынес в абстрактную модель
	 * @param int $id
	 */
	function checkZnoDirection($data, $sysNick)
	{
		if (
			!empty($data["{$sysNick}_id"]) &&
			!empty($sysNick) &&
			(!empty($data["{$sysNick}_disDate"]) || !empty($data["{$sysNick}_disDT"])) &&
			$data["{$sysNick}_IsSuspectZNO"] == 2
		) {
			// направлен на консультацию
			$DispAppoint_id = $this->getFirstResultFromQuery('select top 1 DispAppoint_id from DispAppoint (nolock) da where da.DispAppointType_id = 2 and da.EvnPLDisp_id = ?', array($data["{$sysNick}_id"]));
			$EvnDirection_id = $this->getFirstResultFromQuery('
				select top 1 EvnDirection_id 
				from v_EvnDirection_all (nolock) ed
				inner join v_EvnPLDisp epd (nolock) on ed.EvnDirection_rid = epd.EvnPLDisp_id
				where 
					ed.DirType_id in (3,16) and 
					ed.EvnStatus_id not in (12,13) and 
					epd.EvnPLDisp_id = ?
			', array($data["{$sysNick}_id"]));
			
			if ($DispAppoint_id && !$EvnDirection_id) {
				throw new Exception('При подозрении на ЗНО и назначении с типом "2. Направлен на консультацию в иную медицинскую организацию" должно быть выписано направление на дообследование с типом «на консультацию» или «на поликлинический прием». Добавьте направление на форме осмотра.');
			}
			
			// направлен на обследование
			$DispAppoint_id = $this->getFirstResultFromQuery('select top 1 DispAppoint_id from DispAppoint (nolock) da where da.DispAppointType_id = 3 and da.EvnPLDisp_id = ?', array($data["{$sysNick}_id"]));
			$EvnDirection_id = $this->getFirstResultFromQuery('
				select top 1 EvnDirection_id 
				from v_EvnDirection_all (nolock) ed
				inner join v_EvnPLDisp epd (nolock) on ed.EvnDirection_rid = epd.EvnPLDisp_id
				where 
					ed.DirType_id = 10 and 
					ed.EvnStatus_id not in (12,13) and 
					epd.EvnPLDisp_id = ?
			', array($data["{$sysNick}_id"]));
			
			if ($DispAppoint_id && !$EvnDirection_id) {
				throw new Exception('При подозрении на ЗНО и назначении с типом "3. Направлен на обследование" должно быть выписано направление на дообследование с типом «на исследование». Добавьте направление на форме осмотра.');
			}
		}
	}
	
}
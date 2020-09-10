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
 * CureStandartUslugaComplexLink_model
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Александр Пермяков
 * @version      08.2014
 *
 * @property-read int $CureStandart_id Стандарт лечения
 * @property-read int $UslugaComplex_id Услуга категории ГОСТ-2004 или ГОСТ-2011, которая связывает услуги стандарта с позициями в профиле
 * @property-read int $UslugaComplex_sysprid Услуга категории "Системные профили исследований"
 */
class CureStandartUslugaComplexLink_model extends SwPgModel
{
	/**
	 * Обновление связей услуги категории "Системные профили исследований"
	 * со стандартами лечения
	 */
	const SCENARIO_UPDATE_LINKS = 'updateLinks';
    /**
     * Конструктор объекта
     */
    function __construct()
    {
        parent::__construct();
	    $this->_setScenarioList(array(
		    self::SCENARIO_UPDATE_LINKS,
	    ));
    }

	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName()
	{
		return 'CureStandartUslugaComplexLink';
	}

	/**
	 * Возвращает массив описаний всех используемых атрибутов объекта в формате ключ => описание
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();
		unset($arr['code']);
		unset($arr['name']);
		$arr[self::ID_KEY]['alias'] = 'CureStandartUslugaComplexLink_id';
		$arr[self::ID_KEY]['label'] = 'Идентификатор связи';
		$arr['insdt']['alias'] = 'CureStandartUslugaComplexLink_insDT';
		$arr['upddt']['alias'] = 'CureStandartUslugaComplexLink_updDT';
		$arr['curestandart_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'CureStandart_id',
		);
		$arr['uslugacomplex_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'UslugaComplex_id',
		);
		$arr['uslugacomplex_sysprid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'UslugaComplex_sysprid',
		);
		return $arr;
	}

	/**
	 * Проверка корректности данных модели для указанного сценария
	 * @throws Exception
	 */
	protected function _validate()
	{
		parent::_validate();
		if (in_array($this->scenario, array(
			self::SCENARIO_UPDATE_LINKS,
		)) && empty($this->UslugaComplex_sysprid)) {
			throw new Exception('Не указана услуга категории "Системные профили исследований"', 400);
		}
	}

	/**
	 * Обновление связей услуги категории "Системные профили исследований"
	 * со стандартами лечения
	 * @todo лучше вынести эту логику в хранимку
	 */
 	function updateLinks($data)
	{
		$this->setScenario(self::SCENARIO_UPDATE_LINKS);
		$this->setParams($data);
		$this->setAttributes($data);
		$this->_validate();
		//Получение списка связей
		$result = $this->db->query("select * from {$this->viewName()} 
		where UslugaComplex_sysprid = :UslugaComplex_sysprid", array(
			'UslugaComplex_sysprid' => $this->UslugaComplex_sysprid
		));
		if ( !is_object($result) ) {
			throw new Exception('Ошибка запроса списка связей услуги категории "Системные профили исследований"');
		}
		$tmp = $result->result('array');
		$savedList = array();
		foreach ($tmp as $row) {
			$key = $row['UslugaComplex_sysprid'].$row['CureStandart_id'].$row['UslugaComplex_id'];
			$savedList[$key] = $row;
		}
		//Получение списка услуг стандартов лечения, которые есть в составе услуги категории "Системные профили исследований"
		$result = $this->db->query("
			WITH CSU AS
			(
				select
					csds.CureStandart_id,
					csds.UslugaComplex_id
				from
					v_CureStandartDiagnosis csds 
				union all
				select
					cst.CureStandart_id,
					cstu.UslugaComplex_id
				from
					v_CureStandartTreatment cst 
					inner join v_CureStandartTreatmentUsluga cstu  on cst.CureStandartTreatment_id = cstu.CureStandartTreatment_id
			)
			select
				lab.UslugaComplex_id as \"UslugaComplex_sysprid\",
				CSU.CureStandart_id as \"CureStandart_id\",
				CSU.UslugaComplex_id as \"UslugaComplex_id\"
			from
				v_UslugaComplex lab 
				inner join v_UslugaComplexComposition ucc  on ucc.UslugaComplex_pid = lab.UslugaComplex_id
				inner join v_UslugaComplex item  on item.UslugaComplex_id = ucc.UslugaComplex_id
				inner join CSU  on item.UslugaComplex_2011id = CSU.UslugaComplex_id
					or item.UslugaComplex_2004id = CSU.UslugaComplex_id
			where
				lab.UslugaComplex_id = :UslugaComplex_sysprid", array(
			'UslugaComplex_sysprid' => $this->UslugaComplex_sysprid
		));
		if ( !is_object($result) ) {
			throw new Exception('Ошибка запроса списка услуг стандартов лечения');
		}
		$tmp = $result->result('array');
		$newList = array();
		foreach ($tmp as $row) {
			$key = $row['UslugaComplex_sysprid'].$row['CureStandart_id'].$row['UslugaComplex_id'];
			$newList[$key] = $row;
		}
		foreach ($newList as $key => $row) {
			if (empty($savedList[$key])) {
				$row[$this->primaryKey()] = null;
				$row['pmUser_id'] = $this->promedUserId;
				$tmp = $this->execCommonSP($this->createProcedureName(), $row);
				if (empty($tmp)) {
					throw new Exception('Ошибка запроса');
				}
				if (isset($tmp[0]['Error_Msg'])) {
					throw new Exception($tmp[0]['Error_Msg']);
				}
			}
		}
		foreach ($savedList as $key => $row) {
			if (empty($newList[$key])) {
				$tmp = $this->execCommonSP($this->deleteProcedureName(), array(
					$this->primaryKey() => $row[$this->primaryKey()],
				));
				if (empty($tmp)) {
					throw new Exception('Ошибка запроса');
				}
				if (isset($tmp[0]['Error_Msg'])) {
					throw new Exception($tmp[0]['Error_Msg']);
				}
			}
		}
	}
}
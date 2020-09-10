<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package      PromedWeb
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @link         http://swan.perm.ru/PromedWeb
 */
require_once('EvnDiagAbstract_model.php');
/**
 * EvnDiagNephro_model - Модель "Установка диагноза в регистре по нефрологии"
 *
 * Можно редактировать только 2 поля:
 * Дата установления
 * Диагноз
 *
 * При добавлении нового заболевания с типом "Нефрология"
 * автоматически создается новая запись в списке "Диагноз" с датой заболевания
 *
 * Нельзя удалить/редактировать первую запись списка диагнозов.
 *
 * При редактировании диагноза в последней записи списка должен обновляться Morbus.Diag_id,
 * если нет более свежего диагноза в посещении?
 * При удалении последней записи списка должен обновляться Morbus.Diag_id
 *
 * @package      Nephro
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Александр Пермяков
 * @version      11.2014
 */
class EvnDiagNephro_model extends EvnDiagAbstract_model
{
    /**
     * Конструктор объекта
     */
    function __construct()
    {
        parent::__construct();
	    $this->_setScenarioList(array(
		    self::SCENARIO_LOAD_GRID,
		    self::SCENARIO_VIEW_DATA,
		    self::SCENARIO_LOAD_EDIT_FORM,
		    self::SCENARIO_AUTO_CREATE,
		    self::SCENARIO_DO_SAVE,
		    self::SCENARIO_DELETE,
	    ));
    }

	/**
	 * Проверка корректности данных модели для указанного сценария
	 * @throws Exception
	 */
	protected function _validate()
	{
		// begin swModel::_validate();
		if (!in_array($this->scenario, $this->scenarioList)) {
			throw new Exception('Эта функция не реализована', 500);
		}
		if (in_array($this->scenario, array(self::SCENARIO_DELETE, self::SCENARIO_LOAD_EDIT_FORM))) {
			if ( empty($this->id) ) {
				throw new Exception('Не указан идентификатор объекта', 500);
			}
		}
		// end swModel::_validate();

		// begin EvnAbstract_model::_validate();
		if (in_array($this->scenario, array(
			self::SCENARIO_DELETE,
			self::SCENARIO_AUTO_CREATE,
			self::SCENARIO_DO_SAVE
		))) {
			if ( empty($this->sessionParams['lpu_id']) ) {
				throw new Exception('Не указан идентификатор МО пользователя');
			}
			if ( $this->isNewRecord) {
				$this->setAttribute('lpu_id', $this->sessionParams['lpu_id']);
			}
			if ( $this->sessionParams['lpu_id'] != $this->Lpu_id
				&& false == $this->isNewRecord
				&& !isSuperAdmin()
				&& !isLpuAdmin($this->Lpu_id)
			) {
				throw new Exception('Вы не можете изменить объект, созданный в другой МО ');
			}
		}
		if (in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_AUTO_CREATE))) {
			if ( !isset($this->Server_id) || $this->Server_id < 0 ) {
				throw new Exception('Не указан источник данных');
			}
			if ( empty($this->PersonEvn_id) || $this->PersonEvn_id < 0 ) {
				throw new Exception('Не указан человек');
			}
		}
		// end EvnAbstract_model::_validate();

		if (in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_AUTO_CREATE))) {
			if ( empty($this->Diag_id) ) {
				throw new Exception('Не указан диагноз МКБ-10');
			}
			if ( empty($this->setDT) ) {
				throw new Exception('Не указана дата');
			}
		}
		if (in_array($this->scenario, array(
			self::SCENARIO_VIEW_DATA,self::SCENARIO_LOAD_GRID,
			self::SCENARIO_DO_SAVE, self::SCENARIO_AUTO_CREATE
		))) {
			if ( empty($this->Morbus_id) ) {
				throw new Exception('Не указано заболевание', 400);
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
		$arr[self::ID_KEY]['alias'] = 'EvnDiagNephro_id';
		$arr['pid']['alias'] = 'EvnDiagNephro_pid';
		$arr['pid']['save'] = 'trim';
		$arr['pid']['properties'] = array(
			self::PROPERTY_NEED_TABLE_NAME,
			self::PROPERTY_READ_ONLY,
			self::PROPERTY_NOT_SAFE,
		);
		$arr['setdate']['alias'] = 'EvnDiagNephro_setDate';
		$arr['settime']['alias'] = 'EvnDiagNephro_setTime';
		$arr['diagsetclass_id']['save'] = 'trim';
		$arr['diagsetclass_id']['properties'] = array(
			self::PROPERTY_READ_ONLY,
			self::PROPERTY_NOT_SAFE,
			self::PROPERTY_IS_SP_PARAM,
		);
		$arr['morbus_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Morbus_id',
			'label' => 'Заболевание',
			'save' => 'trim|required',
			'type' => 'id'
		);
		return $arr;
	}

	/**
	 * Определение идентификатора класса события
	 * @return int
	static function evnClassId()
	{
		return 16;
	}
	 */

	/**
	 * Определение кода класса события
	 * @return string
	static function evnClassSysNick()
	{
		return 'EvnDiagNephro';
	}
	 */


	/**
	 * Проверки и другая логика перед сохранением объекта
	 *
	 * При запросах данных этого объекта из БД будут возвращены старые данные!
	 * @param array $data Массив входящих параметров
	 * @throws Exception
	 */
	protected function _beforeSave($data = array())
	{
		parent::_beforeSave($data);
		$this->setAttribute('pid', null);
		$this->setAttribute('diagsetclass_id', 1);
	}

	/**
	 * Получение данных для формы редактирования
	 */
	function doLoadEditForm($data)
	{
		$data['scenario'] = self::SCENARIO_LOAD_EDIT_FORM;
		$this->applyData($data);
		$this->_validate();
		return array(array(
			'EvnDiagNephro_id' => $this->id,
			'EvnDiagNephro_setDate' => $this->setDT->format('d.m.Y'),
			'Diag_id' => $this->Diag_id,
			'Morbus_id' => $this->Morbus_id,
			'Server_id' => $this->Server_id,
			'Person_id' => $this->Person_id,
			'PersonEvn_id' => $this->PersonEvn_id,
		));
	}

	/**
	 * Получение данных для панели просмотра
	 */
	function loadViewData($data)
	{
		//$data['scenario'] = self::SCENARIO_VIEW_DATA;
		$query = "
			WITH EvnDiagNephro AS
		    (
		        SELECT
					Evn.EvnDiag_id as EvnDiagNephro_id,
					Evn.EvnDiag_setDT as EvnDiagNephro_setDT,
		            Evn.Diag_id,
		            Evn.Person_id,
		            Evn.PersonEvn_id,
		            Evn.Server_id,
		            MV.Morbus_disDT
				from
					v_MorbusNephro MV
					inner join v_EvnDiag Evn on Evn.Morbus_id = MV.Morbus_id
				where
					MV.MorbusNephro_id = :MorbusNephro_id 
		    )
			select
				case when EvnDiagNephro.Morbus_disDT is null /*and EvnDiagNephro.EvnDiagNephro_id != MainRec.EvnDiagNephro_id*/ then 'edit' else 'view' end as \"accessType\",
				case when EvnDiagNephro.EvnDiagNephro_id = MainRec.EvnDiagNephro_id then 1 else 0 end as \"isMainRec\",
				EvnDiagNephro.EvnDiagNephro_id as \"EvnDiagNephro_id\",
				to_char(EvnDiagNephro.EvnDiagNephro_setDT,'dd.mm.yyyy') as \"EvnDiagNephro_setDate\",
				Diag.Diag_id as \"Diag_id\",
				Diag.Diag_Name as \"Diag_Name\",
				Diag.Diag_Code as \"Diag_Code\",
				EvnDiagNephro.Person_id as \"Person_id\",
	            EvnDiagNephro.PersonEvn_id as \"PersonEvn_id\",
	            EvnDiagNephro.Server_id as \"Server_id\",
				:Evn_id as \"MorbusNephro_pid\"
			from
				EvnDiagNephro
				inner join v_Diag Diag on Diag.Diag_id = EvnDiagNephro.Diag_id
				LEFT JOIN LATERAL (
					select EvnDiagNephro_id
					from EvnDiagNephro
					order by EvnDiagNephro_setDT asc
                    limit 1
				) MainRec ON true
			order by EvnDiagNephro.EvnDiagNephro_setDT
		";
		//echo getDebugSql($query, $data);die();
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return array();
		}
	}
}
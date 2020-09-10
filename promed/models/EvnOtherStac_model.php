<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package      PromedWeb
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @link         http://swan.perm.ru/PromedWeb
 */

require_once('EvnLeaveAbstract_model.php');

/**
 * EvnOtherStac_model - Модель "Выписка в стационар другого типа"
 *
 * @package      Hospital
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       swan developers
 * @version      09.2014
 *
 * @property-read int $LpuUnitType_oid Тип подразделения, куда производится выписка
 * @property-read int $LpuSection_oid Отделение, куда производится выписка
 */
class EvnOtherStac_model extends EvnLeaveAbstract_model
{
	/**
	 * Возвращает список всех используемых ключей атрибутов объекта
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();
		$arr[self::ID_KEY]['alias'] = 'EvnOtherStac_id';
		$arr['pid']['alias'] = 'EvnOtherStac_pid';
		$arr['setdate']['alias'] = 'EvnOtherStac_setDate';
		$arr['settime']['alias'] = 'EvnOtherStac_setTime';
		$arr['disdt']['alias'] = 'EvnOtherStac_disDT';
		$arr['diddt']['alias'] = 'EvnOtherStac_didDT';
		$arr['ukl']['alias'] = 'EvnOtherStac_UKL';
		$arr['lpuunittype_oid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'LpuUnitType_oid',
			'label' => 'Тип подразделения, куда производится выписка',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['lpusection_oid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'LpuSection_oid',
			'label' => 'Отделение, куда производится выписка',
			'save' => 'trim',
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
		return 42;
	}

	/**
	 * Определение кода класса события
	 * @return string
	 */
	static function evnClassSysNick()
	{
		return 'EvnOtherStac';
	}

	/**
	 * Конструктор
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
		if (empty($this->LpuUnitType_oid)
			&& in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_AUTO_CREATE))
		) {
			throw new Exception('Не указан тип стационара', 400);
		}
		if (empty($this->LpuSection_oid)
			&& in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_AUTO_CREATE))
		) {
			throw new Exception('Не указано отделение', 400);
		}
	}


	/**
	 * Удаление
	 */
	function deleteEvnOtherStac($data)
	{
		return array($this->doDelete($data));
	}

	/**
	 * Сохранение
	 */
	function saveEvnOtherStac($data)
	{
		if (empty($data['scenario'])) {
			$data['scenario'] = self::SCENARIO_DO_SAVE;
		}
		return array($this->doSave($data));
	}

	/**
	 * Получение данных для копии
	 */
	function doLoadCopyData($data)
	{
		$response = parent::doLoadCopyData($data);
		$response['LpuSection_oid'] = $this->LpuSection_oid;
		$response['LpuUnitType_oid'] = $this->LpuUnitType_oid;
		return $response;
	}

	/**
	 * Загрузка данных для формы
	 * @todo Сделать по аналогии с EvnLeave_model::loadEvnLeaveEditForm
	 */
	function loadEvnOtherStacEditForm($data) {
		$query = "
			SELECT TOP 1
				EOS.EvnOtherStac_id,
				EOS.EvnOtherStac_pid,
				convert(varchar(10), EOS.EvnOtherStac_setDT, 104) as EvnOtherStac_setDate,
				EOS.EvnOtherStac_setTime,
				ROUND(EOS.EvnOtherStac_UKL, 3) as EvnOtherStac_UKL,
				EOS.LeaveCause_id,
				EOS.LpuSection_oid,
				EOS.LpuUnitType_oid,
				EOS.Person_id,
				EOS.PersonEvn_id,
				EOS.ResultDesease_id,
				EOS.Server_id
			FROM
				v_EvnOtherStac EOS with (nolock)
			WHERE (1 = 1)
				and EOS.EvnOtherStac_id = :EvnOtherStac_id
				and EOS.Lpu_id = :Lpu_id
		";
		$result = $this->db->query($query, array(
			'EvnOtherStac_id' => $data['EvnOtherStac_id'],
			'Lpu_id' => $data['Lpu_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
}
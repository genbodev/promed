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
 * EvnOtherSection_model - Модель "Выписка в другое отделение"
 *
 * @package      Hospital
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       swan developers
 * @version      09.2014
 *
 * @property-read int $LpuSection_oid Отделение, куда производится выписка
 */
class EvnOtherSection_model extends EvnLeaveAbstract_model
{
	/**
	 * Возвращает список всех используемых ключей атрибутов объекта
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();
		$arr[self::ID_KEY]['alias'] = 'EvnOtherSection_id';
		$arr['pid']['alias'] = 'EvnOtherSection_pid';
		$arr['setdate']['alias'] = 'EvnOtherSection_setDate';
		$arr['settime']['alias'] = 'EvnOtherSection_setTime';
		$arr['disdt']['alias'] = 'EvnOtherSection_disDT';
		$arr['diddt']['alias'] = 'EvnOtherSection_didDT';
		$arr['ukl']['alias'] = 'EvnOtherSection_UKL';
		$arr['lpusection_oid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'LpuSection_oid',
			'label' => 'Отделение, куда производится выписка',
			'save' => 'trim|required',
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
		return 41;
	}

	/**
	 * Определение кода класса события
	 * @return string
	 */
	static function evnClassSysNick()
	{
		return 'EvnOtherSection';
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
		if (empty($this->LpuSection_oid)
			&& in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_AUTO_CREATE))
		) {
			throw new Exception('Не указано отделение', 400);
		}
	}


	/**
	 * Удаление
	 */
	function deleteEvnOtherSection($data)
	{
		return array($this->doDelete($data));
	}

	/**
	 * Сохранение
	 */
	function saveEvnOtherSection($data)
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
		return $response;
	}

	/**
	 * Загрузка данных для формы
	 * @todo Сделать по аналогии с EvnLeave_model::loadEvnLeaveEditForm
	 */
	function loadEvnOtherSectionEditForm($data) {
		$query = "
			SELECT TOP 1
				EOLS.EvnOtherSection_id,
				EOLS.EvnOtherSection_pid,
				convert(varchar(10), EOLS.EvnOtherSection_setDT, 104) as EvnOtherSection_setDate,
				EOLS.EvnOtherSection_setTime,
				ROUND(EOLS.EvnOtherSection_UKL, 3) as EvnOtherSection_UKL,
				EOLS.LeaveCause_id,
				EOLS.LpuSection_oid,
				EOLS.Person_id,
				EOLS.PersonEvn_id,
				EOLS.ResultDesease_id,
				EOLS.Server_id
			FROM
				v_EvnOtherSection EOLS with (nolock)
			WHERE (1 = 1)
				and EOLS.EvnOtherSection_id = :EvnOtherSection_id
				and EOLS.Lpu_id = :Lpu_id
		";
		$result = $this->db->query($query, array(
			'EvnOtherSection_id' => $data['EvnOtherSection_id'],
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
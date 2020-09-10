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
 * EvnOtherLpu_model - Модель "Выписка в другое ЛПУ"
 *
 * @package      Hospital
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       swan developers
 * @version      09.2014
 *
 * @property-read int $Lpu_oid ЛПУ, куда производится выписка
 * @property-read int $Org_oid Организация, куда производится выписка
 */
class EvnOtherLpu_model extends EvnLeaveAbstract_model
{
	/**
	 * Возвращает список всех используемых ключей атрибутов объекта
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();
		$arr[self::ID_KEY]['alias'] = 'EvnOtherLpu_id';
		$arr['pid']['alias'] = 'EvnOtherLpu_pid';
		$arr['setdate']['alias'] = 'EvnOtherLpu_setDate';
		$arr['settime']['alias'] = 'EvnOtherLpu_setTime';
		$arr['disdt']['alias'] = 'EvnOtherLpu_disDT';
		$arr['diddt']['alias'] = 'EvnOtherLpu_didDT';
		$arr['ukl']['alias'] = 'EvnOtherLpu_UKL';
		$arr['lpu_oid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Lpu_oid',
			'label' => 'ЛПУ, куда производится выписка',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['org_oid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Org_oid',
			'label' => 'Организация, куда производится выписка',
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
		return 40;
	}

	/**
	 * Определение кода класса события
	 * @return string
	 */
	static function evnClassSysNick()
	{
		return 'EvnOtherLpu';
	}
	/**
	 *	Конструктор
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
		if (empty($this->Org_oid)
			&& in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_AUTO_CREATE))
		) {
			throw new Exception('Не указано ЛПУ', 400);
		}
	}

	/**
	 *	Удаление исхода
	 */
	function deleteEvnOtherLpu($data)
	{
		return array($this->doDelete($data));
	}

	/**
	 * Сохранение
	 */
	function saveEvnOtherLpu($data)
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
		$response['Lpu_oid'] = $this->Lpu_oid;
		$response['Org_oid'] = $this->Org_oid;
		return $response;
	}
	/**
	 * Получение данных для копии
	 * @todo Сделать по аналогии с EvnLeave_model::getEvnLeaveCopyData
	 */
	function getEvnOtherLpuCopyData($data) {
		$query = "
			select
				EOL.PersonEvn_id as \"PersonEvn_id\",
				EOL.Server_id as \"Server_id\",
				EOL.EvnOtherLpu_setTime as \"EvnOtherLpu_setTime\",
				EOL.LeaveCause_id as \"LeaveCause_id\",
				EOL.ResultDesease_id as \"ResultDesease_id\",
				EOL.Org_oid as \"Org_oid\",
				EOL.EvnOtherLpu_UKL as \"EvnOtherLpu_UKL\"
			from
				v_EvnOtherLpu EOL
			where
				EOL.EvnOtherLpu_pid = :EvnOtherLpu_pid
			limit 1
		";

		$queryParams = array(
			'EvnOtherLpu_pid' => $data['EvnOtherLpu_pid']
		);

		// echo getDebugSQL($query, $queryParams); exit();

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$response = $result->result('array');

			if ( count($response) > 0 ) {
				return $response[0];
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
	}


	/**
	 * Загрузка данных для формы
	 * @todo Сделать по аналогии с EvnLeave_model::loadEvnLeaveEditForm
	 */
	function loadEvnOtherLpuEditForm($data) {
		$query = "
			SELECT
				EOL.EvnOtherLpu_id as \"EvnOtherLpu_id\",
				EOL.EvnOtherLpu_pid as \"EvnOtherLpu_pid\",
				to_char(EOL.EvnOtherLpu_setDT,'DD.MM.YYYY') as \"EvnOtherLpu_setDate\",
				EOL.EvnOtherLpu_setTime as \"EvnOtherLpu_setTime\",
				ROUND(EOL.EvnOtherLpu_UKL, 3) as \"EvnOtherLpu_UKL\",
				EOL.LeaveCause_id as \"LeaveCause_id\",
				EOL.Org_oid as \"Org_oid\",
				EOL.Person_id as \"Person_id\",
				EOL.PersonEvn_id as \"PersonEvn_id\",
				EOL.ResultDesease_id as \"ResultDesease_id\",
				EOL.Server_id as \"Server_id\"
			FROM
				v_EvnOtherLpu EOL
			WHERE (1 = 1)
				and EOL.EvnOtherLpu_id = :EvnOtherLpu_id
				and EOL.Lpu_id = :Lpu_id
			LIMIT 1
		";
		$result = $this->db->query($query, array(
			'EvnOtherLpu_id' => $data['EvnOtherLpu_id'],
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

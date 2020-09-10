<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package      PromedWeb
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @link         http://swan.perm.ru/PromedWeb
 */

require_once('EvnOtherSection_model.php');

/**
 * EvnOtherSectionBedProfile_model - Модель исхода с узкой койки в другое отделение
 *
 * @package      Hospital
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       swan developers
 * @version      09.2014
 *
 * @property-read int $LpuSectionBedProfile_oid Профиль коек отделения, куда производится выписка
 */
class EvnOtherSectionBedProfile_model extends EvnOtherSection_model
{
	/**
	 * Возвращает список всех используемых ключей атрибутов объекта
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();
		$arr[self::ID_KEY]['alias'] = 'EvnOtherSectionBedProfile_id';
		$arr['pid']['alias'] = 'EvnOtherSectionBedProfile_pid';
		$arr['setdate']['alias'] = 'EvnOtherSectionBedProfile_setDate';
		$arr['settime']['alias'] = 'EvnOtherSectionBedProfile_setTime';
		$arr['disdt']['alias'] = 'EvnOtherSectionBedProfile_disDT';
		$arr['diddt']['alias'] = 'EvnOtherSectionBedProfile_didDT';
		$arr['ukl']['alias'] = 'EvnOtherSectionBedProfile_UKL';
		$arr['lpusectionbedprofile_oid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'LpuSectionBedProfile_oid',
			'label' => 'Профиль коек отделения, куда производится выписка',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['lpusectionbedprofilelink_fedid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'LpuSectionBedProfileLink_fedid',
			'label' => 'Ссылка на таблицу связки регионального и ФЕД профиля коек',
			'save' => 'trim',
			'type' => 'id'
		);
		return $arr;
	}

	/**
	 * construct
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Определение идентификатора класса события
	 * @return int
	 */
	static function evnClassId()
	{
		return 113;
	}

	/**
	 * Определение кода класса события
	 * @return string
	 */
	static function evnClassSysNick()
	{
		return 'EvnOtherSectionBedProfile';
	}


	/**
	 * Удаление
	 */
	function deleteEvnOtherSectionBedProfile($data)
	{
		return array($this->doDelete($data));
	}

	/**
	 * Сохранение
	 */
	function saveEvnOtherSectionBedProfile($data)
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
		$response['LpuSectionBedProfile_oid'] = $this->LpuSectionBedProfile_oid;
		return $response;
	}

	/**
	 * Загрузка данных для формы
	 * @todo Сделать по аналогии с EvnLeave_model::loadEvnLeaveEditForm
	 */
	function loadEvnOtherSectionBedProfileEditForm($data) {
		$query = "
			SELECT TOP 1
				EOSBP.EvnOtherSectionBedProfile_id,
				EOSBP.EvnOtherSectionBedProfile_pid,
				convert(varchar(10), EOSBP.EvnOtherSectionBedProfile_setDT, 104) as EvnOtherSectionBedProfile_setDate,
				EOSBP.EvnOtherSectionBedProfile_setTime,
				ROUND(EOSBP.EvnOtherSectionBedProfile_UKL, 3) as EvnOtherSectionBedProfile_UKL,
				EOSBP.LeaveCause_id,
				EOSBP.LpuSection_oid,
				EOSBP.LpuSectionBedProfile_oid,
				EOSBP.Person_id,
				EOSBP.PersonEvn_id,
				EOSBP.ResultDesease_id,
				EOSBP.Server_id
			FROM
				v_EvnOtherSectionBedProfile EOSBP with (nolock)
			WHERE (1 = 1)
				and EOSBP.EvnOtherSectionBedProfile_id = :EvnOtherSectionBedProfile_id
				and EOSBP.Lpu_id = :Lpu_id
		";
		$result = $this->db->query($query, array(
			'EvnOtherSectionBedProfile_id' => $data['EvnOtherSectionBedProfile_id'],
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
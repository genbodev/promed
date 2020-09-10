<?php
/**
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      All
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stanislav Bykov
* @version      15.05.2014
*/
require_once(APPPATH.'models/_pgsql/LpuStructure_model.php');

class Ekb_LpuStructure_model extends LpuStructure_model
{
	protected $_scheme = "r66";

	/**
	 * Это Doc-блок
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Получение списка профилей отделений
	 */
	function loadLpuSectionProfileList($data) {
		$params = array();
		$response = array();
		$list = array();

		if (!empty($data['LpuSection_ids'])) {
			$list = json_decode($data['LpuSection_ids']);
		}
		if (!empty($data['LpuSection_id'])) {
			$list = array($data['LpuSection_id']);
		}

		if (count($list) == 0) {
			return $response;
		}
		$list_str = implode(',', $list);

		$cond = "";
		//Условие: если есть доп. профили, то не включать основной
		if (empty($data['additionWithDefault']) || $data['additionWithDefault'] == 1) {
			$cond = "and not exists(
				select * from v_LpuSectionLpuSectionProfile
				where LpuSection_id = ls.LpuSection_id
					and LpuSectionLpuSectionProfile_begDate <= (select dt from mv)
					and (LpuSectionLpuSectionProfile_endDate > (select dt from mv) or LpuSectionLpuSectionProfile_endDate is null)
			)";
		}

		$query = "
			with mv as (
				select dbo.tzGetDate() as dt
			)
			
			select distinct
				LpuSectionProfile_id as \"LpuSectionProfile_id\",
				LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
				LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
				LpuSectionProfile_SysNick as \"LpuSectionProfile_SysNick\"
			from
			(select
				lsp.LpuSectionProfile_id,
				lsp.LpuSectionProfile_Code,
				lsp.LpuSectionProfile_Name,
				lsp.LpuSectionProfile_SysNick
			from
				v_LpuSectionLpuSectionProfile lslsp
				inner join v_LpuSection ls on ls.LpuSection_id = lslsp.LpuSection_id
				inner join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = lslsp.LpuSectionProfile_id
			where
				ls.LpuSection_id in ({$list_str})
				and lslsp.LpuSectionLpuSectionProfile_begDate <= (select dt from mv)
				and (lslsp.LpuSectionLpuSectionProfile_endDate > (select dt from mv) or lslsp.LpuSectionLpuSectionProfile_endDate is null)
			union
			select
				lsp.LpuSectionProfile_id,
				lsp.LpuSectionProfile_Code,
				lsp.LpuSectionProfile_Name,
				lsp.LpuSectionProfile_SysNick
			from
				v_LpuSection ls
				inner join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
			where
				ls.LpuSection_id in ({$list_str})
				{$cond}
			) t
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		$response = $result->result('array');

		return $response;
	}

	/**
	 * Сохранение доп. параметров
	 */
	function saveOtherLpuSectionParams($data) {
		$response = array(array('Error_Msg' => ''));

		// Сохраняем ссылку на fed.MedicalCareKind
		// https://redmine.swan.perm.ru/issues/52786

		$data['LpuSectionLink_id'] = $this->getFirstResultFromQuery('select LpuSectionLink_id as "LpuSectionLink_id" from r66.v_LpuSectionLink where LpuSection_id = :LpuSection_id', array('LpuSection_id' => $data['LpuSection_id']));

		if ( !empty($data['MedicalCareKind_id']) ) {
			$query = "
				select
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\",
					LpuSectionLink_id as \"LpuSectionLink_id\"
				from {$this->_scheme}.p_LpuSectionLink_" . (!empty($data['LpuSectionLink_id']) ? "upd" : "ins") . "(
					LpuSectionLink_id := :LpuSectionLink_id,
					LpuSection_id := :LpuSection_id,
					MedicalCareKind_id := :MedicalCareKind_id,
					pmUser_id := :pmUser_id
				)
			";
			$result = $this->db->query($query, array(
				'LpuSectionLink_id' => (!empty($data['LpuSectionLink_id']) ? $data['LpuSectionLink_id'] : NULL),
				'LpuSection_id' => $data['LpuSection_id'],
				'MedicalCareKind_id' => $data['MedicalCareKind_id'],
				'pmUser_id' => $data['pmUser_id']
			));

			if ( !is_object($result) ) {
				return false;
			}

			$response = $result->result('array');

			//Дополнительная проверка на дубли
			$LpuSectionDoubleCheck = $this->getFirstResultFromQuery('select LpuSectionLink_id as "LpuSectionLink_id" from r66.v_LpuSectionLink where LpuSection_id = :LpuSection_id and LpuSectionLink_id <> :LpuSectionLink_id', array('LpuSection_id' => $data['LpuSectionLink_id'], 'LpuSectionLink_id' => $response[0]['LpuSectionLink_id']));

			if (!empty($LpuSectionDoubleCheck)){
				return array(array('Error_Msg' => 'Обнаружен дубль отделения в стыковочной таблице. Повторите попытку сохранения отделения.'));
			}
		}
		else if ( !empty($data['LpuSectionLink_id']) ) {
			$query = "
				select
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from {$this->_scheme}.p_LpuSectionLink_del(
					LpuSectionLink_id := :LpuSectionLink_id
				)
			";
			$result = $this->db->query($query, array(
				'LpuSectionLink_id' => $data['LpuSectionLink_id']
			));

			if ( !is_object($result) ) {
				return false;
			}

			$response = $result->result('array');
		}

		return $response;
	}

	/**
	 * Дополнительные поля для выборки списка отделений и данных для формы редактирования отделения
	 */
	function getLpuSectionListAdditionalFields() {
		return '
			,LSL.MedicalCareKind_id as "MedicalCareKind_id"
		';
	}

	/**
	 * Дополнительные джойны для выборки списка отделений и данных для формы редактирования отделения
	 */
	function getLpuSectionListAdditionalJoin() {
		return '
			left join lateral(
				select MedicalCareKind_id
				from r66.v_LpuSectionLink
				where LpuSection_id = LpuSection.LpuSection_id
				limit 1
			) LSL on true
		';
	}
}

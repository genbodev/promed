<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ElectronicDigitalSign_model - модель для работы с ЭЦП
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Bykov Stanislav (savage@swan.perm.ru)
 * @version			01.11.2013
 */

class ElectronicDigitalSign_model extends swPgModel {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Получение юзера для которого надо проверять валидность подписи
	 */
	function getPmUserIdForSignedDoc($docparams) {
		$table = 'Evn';
		$idField = 'Evn_id';
		$pmuserField = "pmUser_signID";
		switch($docparams[0]) {
			case 'EvnReceptOtv':
				$table = "EvnRecept";
				$idField = "EvnRecept_id";
				$pmuserField = "pmUser_signotvID";
				break;
		}
		$query = "
			select
				{$pmuserField} as \"{$pmuserField}\"
			from
				v_{$table}
			where
				{$idField} = :{$idField}
			limit 1
		";

		$result = $this->db->query($query, array(
			$idField => $docparams[1]
		));

		if (is_object($result)) {
			$resp = $result->result('array');
			if (count($resp) > 0 && !empty($resp[0][$pmuserField])) {
				return $resp[0][$pmuserField];
			}
		}


		return null;
	}

	/**
	 * Обработка после верификации
	 */
	function onValidateEvn($data, $docparams, $valid) {
		$table = 'Evn';
		$idField = 'Evn_id';
		$signedField = 'Evn_IsSigned';
		$dateField = "Evn_signDT";
		switch($docparams[0]) {
			case 'EvnReceptOtv':
				$table = "EvnRecept";
				$idField = "EvnRecept_id";
				$signedField = "EvnRecept_IsOtvSigned";
				$dateField = "EvnRecept_signotvDT";
				break;
		}
		$query = "
			with mv as (
				select
					case 
						when :{$signedField} = 2 then 2
						when {$signedField} IS NOT NULL then 1
						else null
					end as {$signedField}
				from
					v_{$table}
				where
					{$idField} = :{$idField}
				limit 1
			)
				
			select {$signedField} as \"valid\";
			
			update
				{$table} with
			set
				{$signedField} = (select {$signedField} from mv),
				{$dateField} = dbo.tzGetdate()
			where
				{$idField} = :{$idField}
		";

		$result = $this->db->query($query, array(
			$idField => $docparams[1],
			$signedField => $valid
		));

		if (is_object($result)) {
			$resp = $result->result('array');
			if (count($resp) > 0 && !empty($resp[0]['valid'])) {
				$valid = $resp[0]['valid'];
			}
		}


		return $valid;
	}

	/**
	 * Обработка ответа сервиса
	 */
	function loadDocumentVersionList($data = array()) {
		$res = array();

		$userids = array();
		$usernames = array();
		foreach ( $data as $array ) {
			$userids[] = $array['user_id'];
		}
		if (count($userids) > 0) {
			$userids = implode(',', $userids);
			$query = "
				SELECT pmUser_id as \"pmUser_id\", pmUser_Name as \"pmUser_Name\" FROM v_pmUserCache WHERE pmUser_id IN ({$userids})
			";
			$result = $this->db->query($query);
			if (is_object($result)) {
				$resp = $result->result('array');
				foreach($resp as $one) {
					$usernames[$one['pmUser_id']] = $one['pmUser_Name'];
				}
			}
		}


		foreach ( $data as $array ) {
			$res[] = array(
				'Doc_unicId' => $array['doc_id'] . '_' . $array['version'],
				'Doc_id' => $array['doc_id'],
				'pmUser_id' => $array['user_id'],
				'pmUser_Name' => !empty($usernames[$array['user_id']])?$usernames[$array['user_id']]:'',
				'Doc_Version' => $array['version'],
				'Doc_DateTime' => date('d.m.Y H:i:s', $array['timestamp'])
			);
		}
		return $res;
	}

	/**
	 * Функция вывода JSON_P
	 */
	function json_p($data) {
		if (!empty($_REQUEST['callback'])) {
			echo $_REQUEST['callback']."(".json_encode($data).")";
		} else {
			echo json_encode($data);
		}
	}
}
<?php
/**
* Perm_Person_model - модель для работы с перс. данными (Пермь)
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stanislav Bykov (savage@swan.perm.ru)
* @version      05.09.2016
*/

require_once(APPPATH.'models/_pgsql/Person_model.php');

class Perm_Person_model extends Person_model {
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Проверка единого номера полиса на уникальность
	 * @task https://redmine.swan.perm.ru/issues/88654
	 * Вынесено в региональную модель по задаче https://redmine.swan.perm.ru/issues/93041
	 */
	function checkFederalNumUnique($data) {
		if ( empty($data['Federal_Num']) ) {
			return true;
		}

		$filterList = array(
			'ps.Person_EdNum = :Person_EdNum'
		);
		$queryParams = array(
			'Person_EdNum' => $data['Federal_Num']
		);

		if ( !empty($data['Person_id']) ) {
			$filterList[] = "ps.Person_id <> :Person_id";
			$queryParams['Person_id'] = $data['Person_id'];
		}

		$query = "
			SELECT ps.Person_id as \"Person_id\"
			FROM v_PersonState ps
			WHERE " . implode(' and ', $filterList) . "
			LIMIT 1
		";
		// echo getDebugSQL($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			// Ошибка запроса
			return false;
		}

		$response = $result->result('array');

		if ( is_array($response) && count($response) > 0 ) {
			// Найдено совпадение
			return false;
		}

		return true;
	}
}
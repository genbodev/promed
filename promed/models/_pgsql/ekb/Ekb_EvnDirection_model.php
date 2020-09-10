<?php
/**
* Ekb_EvnDirection_model - модель для работы с направлениями (Екатеринбург)
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009-2014 Swan Ltd.
*/

require_once(APPPATH.'models/_pgsql/EvnDirection_model.php');

class Ekb_EvnDirection_model extends EvnDirection_model {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * @task https://redmine.swan.perm.ru/issues/41896
	 * @param $data
	 * @return bool
	 */
	function getEvnDirectionNumber($data, $tryCount = 0) {
		$query = "
			select	EvnDirection_Num as \"EvnDirection_Num\"
			from	xp_GenpmID( 
				ObjectName = 'EvnDirection',
				Lpu_id = :Lpu_id,
				ObjectValue = :ObjectValue )
		";
		$result = $this->db->query($query, array(
			 'Lpu_id' => $data['Lpu_id']
			,'ObjectValue' => (!empty($data['year']) ? $data['year'] : date('Y'))
		));

		if ( !is_object($result) ) {
			return false;
		}

		$responseEDNum = $result->result('array');

		if ( !is_array($responseEDNum) || count($responseEDNum) == 0 ) {
			return false;
		}

		return $responseEDNum;

		/*$query = "
			select top 1 Lpu_RegNomN2
			from v_Lpu with (nolock)
			where Lpu_id = :Lpu_id
		";
		$result = $this->db->query($query, array(
			'Lpu_id' => $data['Lpu_id']
		));

		if ( !is_object($result) ) {
			return false;
		}

		$responseLpu = $result->result('array');

		if ( !is_array($responseLpu) || count($responseLpu) == 0 ) {
			return false;
		}

		$EvnDirection_Num = (!empty($data['year']) ? $data['year'] : date('Y')) . sprintf('%04d', $responseLpu[0]['Lpu_RegNomN2']) . sprintf('%06d', $responseEDNum[0]['EvnDirection_Num']);

		// проверяем, а не занят ли такой номер направления в данной МО.
		$resp_check = $this->queryResult("
			select
				EvnDirection_id
			from
				v_EvnDirection_all (nolock)
			where
				Lpu_id = :Lpu_id
				and EvnDirection_Num = :EvnDirection_Num
				and YEAR(EvnDirection_setDT) = :Year
		", array(
			'Lpu_id' => $data['Lpu_id'],
			'EvnDirection_Num' => $EvnDirection_Num,
			'Year' => (!empty($data['year']) ? $data['year'] : date('Y'))
		));
		if (!empty($resp_check[0]['EvnDirection_id']) && $tryCount < 100) { // ограничение, на всякий случай.
			$tryCount++;
			return $this->getEvnDirectionNumber($data, $tryCount);
		}

		return array(array('EvnDirection_Num' => $EvnDirection_Num));*/
	}
}

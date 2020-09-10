<?php
/**
* Khak_EvnDirection_model - модель для работы с направлениями (Хакасия)
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009-2014 Swan Ltd.
*/

require_once(APPPATH.'models/EvnDirection_model.php');

class Khak_EvnDirection_model extends EvnDirection_model {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * @task https://redmine.swan.perm.ru/issues/43218
	 * @param $data
	 * @return mixed
	 */
	public function getEvnDirectionNumber($data, $tryCount = 0) {
		$query = "
			declare @EvnDirection_Num bigint;
			exec xp_GenpmID @ObjectName = 'EvnDirection', @Lpu_id = :Lpu_id, @ObjectID = @EvnDirection_Num output, @ObjectValue = :ObjectValue;
			select @EvnDirection_Num as EvnDirection_Num;
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

		$query = "select top 1 Lpu_RegNomN2 from v_Lpu with (nolock) where Lpu_id = :Lpu_id";
		$queryParams = array('Lpu_id' => $data['Lpu_id']);

		$Lpu_RegNomN2 = $this->getFirstResultFromQuery($query, $queryParams);

		$EvnDirection_Num = (!empty($data['year']) ? $data['year'] : date('Y')) . sprintf('%04d', $Lpu_RegNomN2) . sprintf('%06d', $responseEDNum[0]['EvnDirection_Num']);

		return array(array('EvnDirection_Num' => $EvnDirection_Num));
	}
}

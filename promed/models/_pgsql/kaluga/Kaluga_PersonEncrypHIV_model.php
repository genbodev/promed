<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PersonEncrypHIV_model - модель для работы с шифрами вич-инфецированных
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2020 Swan Ltd.
 * @author			Valery Bondarev
 * @version			01.2020
 */
require_once(APPPATH.'models/_pgsql/PersonEncrypHIV_model.php');

class Kaluga_PersonEncrypHIV_model extends PersonEncrypHIV_model {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Получение шифра
	 */
	function getPersonEncrypHIVEncryp($data) {
		$params = array(
			'Person_id' => !empty($data['Person_id'])?$data['Person_id']:null,
			'PersonEncrypHIV_setDT' => $data['PersonEncrypHIV_setDT'],
			'PersonEncrypHIV_Num' => !empty($data['PersonEncrypHIV_Num'])?$data['PersonEncrypHIV_Num']:null,
		);

		$query = "
			
			select
				(
					'00'
					||'-'||right(cast(EXTRACT(YEAR FROM :PersonEncrypHIV_setDT) as varchar),2)
					||'-'||left(((
							(select PS.Person_SurName
							from v_PersonState PS
							where PS.Person_id = :Person_id
							limit 1)
							union 
							(select '-'
							from v_PersonState PS
							where :Person_id is NULL
							limit 1)
						)),1)
					||'-'||right('0000'||cast(COALESCE(:PersonEncrypHIV_Num, ((COALESCE((
						select max(cast(substring(PEH.PersonEncrypHIV_Encryp,9,4) as int))
						from v_PersonEncrypHIV PEH
						where EXTRACT(YEAR FROM PEH.PersonEncrypHIV_setDT) = EXTRACT(YEAR FROM :PersonEncrypHIV_setDT)
						limit 1
					), 0)+1)) as varchar),4)
				) as \"PersonEncrypHIV_Encryp\",
				(select EncrypHIVTerr_id
					from v_EncrypHIVTerr
					where EncrypHIVTerr_Code = 20
					limit 1
				) as \"EncrypHIVTerr_id\",
				20 as \"EncrypHIVTerr_Code\"
			limit 1
		";
		//echo getDebugSQL($query, $params);exit;
		$result = $this->queryResult($query, $params);
		if (!$this->isSuccessful($result)) {
			return $result;
		}
		return array(array(
			'success' => true,
			'PersonEncrypHIV_Encryp' => $result[0]['PersonEncrypHIV_Encryp'],
			'EncrypHIVTerr_id' => $result[0]['EncrypHIVTerr_id'],
			'EncrypHIVTerr_Code' => $result[0]['EncrypHIVTerr_Code'],
			'Error_Msg' => ''
		));
	}
}
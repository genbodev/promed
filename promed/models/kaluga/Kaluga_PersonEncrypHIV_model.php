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
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			25.03.2015
 */
require_once(APPPATH.'models/PersonEncrypHIV_model.php');

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
			declare
				@EncrypHIVTerr_Code int,
				@PersonEncrypHIV_Num int = :PersonEncrypHIV_Num,
				@Person_SurName varchar(30) = '-',
				@Person_id bigint = :Person_id,
				@year int = year(:PersonEncrypHIV_setDT);

			if @Person_id is not null
				set @Person_SurName = (
					select top 1 PS.Person_SurName
					from v_PersonState PS with(nolock)
					where PS.Person_id = @Person_id
				);
			else set @EncrypHIVTerr_Code = 20;

			if @PersonEncrypHIV_Num is null
				set @PersonEncrypHIV_Num = (isnull((
					select top 1 max(cast(substring(PEH.PersonEncrypHIV_Encryp,9,4) as int))
					from v_PersonEncrypHIV PEH with(nolock)
					where year(PEH.PersonEncrypHIV_setDT) = @year
				), 0)+1);

			select top 1
				(
					'00'
					+'-'+right(cast(@year as varchar),2)
					+'-'+left(@Person_SurName,1)
					+'-'+right('0000'+cast(@PersonEncrypHIV_Num as varchar),4)
				) as PersonEncrypHIV_Encryp,
				(select top 1 EncrypHIVTerr_id
					from v_EncrypHIVTerr with(nolock)
					where EncrypHIVTerr_Code = @EncrypHIVTerr_Code
				) as EncrypHIVTerr_id,
				@EncrypHIVTerr_Code as EncrypHIVTerr_Code
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
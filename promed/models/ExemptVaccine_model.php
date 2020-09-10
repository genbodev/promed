<?php defined('BASEPATH') or die ('No direct script access allowed');

/**
 * Class ExemptVaccine_model медотвод / отказ от вакцинации
 *
 * @access       public
 * @copyright    Copyright (c) 2020
 * @author       Islamov AN
 * @version      21.04.2020
 */
class ExemptVaccine_model extends swModel
{
	/**
	 * ExemptVaccine_model constructor.
	 */
	public function __construct()
	{
		parent::__construct();
	}


	/**
	 * Вывод списка типов отвода пациента
	 *
	 * @param $data
	 * @return bool
	 */
	public function getPersonVaccinationRefuseTypeList()
	{

		$query = <<<SQL
select vrt.VaccinationRefuseType_id as id,
        vrt.VaccinationRefuseType_Name as name
from vc.VaccinationRefuseType vrt
where vrt.VaccinationRefuseType_deleted is null
SQL;

        $result = $this->db->query($query);
        if (is_object($result)) {
			return $result->result('array');
		}
		return false;
	}

}

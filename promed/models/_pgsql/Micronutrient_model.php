<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Micronutrient - модель для работы с микронутриентами
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Cook
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			01.10.2013
 */

class Micronutrient_model extends SwPgModel {

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Возвращает список микронутриентов
	 */
	function loadMicronutrientList($data)
	{
		$filters = '(1=1)';
		$params = array();

		if (!empty($data['Micronutrient_id'])) {
			$filters .= " and Micronutrient_id = :Micronutrient_id";
			$params['Micronutrient_id'] = $data['Micronutrient_id'];
		}
		else {
			if (!empty($data['query'])) {
				$filters .= " and Micronutrient_Name ilike :Micronutrient_Name";
				$params['Micronutrient_Name'] = $data['query'].'%';
			}
		}

		$query = "
			select
				M.Micronutrient_id as \"Micronutrient_id\",
				M.Micronutrient_Code as \"Micronutrient_Code\",
				M.Micronutrient_Name as \"Micronutrient_Name\",
				M.Okei_id as \"Okei_id\"
			from
				v_Micronutrient M
			where
				{$filters}
			order by
				M.Micronutrient_Name
			limit 100
			";

		$result = $this->db->query($query, $params);

		if (is_object($result))
		{
			$response = array();
			$response = $result->result('array');
			return $response;
		}
		return false;
	}
}
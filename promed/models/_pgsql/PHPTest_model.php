<?php
/**
* PHPTest_model - модель для получения тестов из БД
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
*/

class PHPTest_model extends Model {
	/**
	 * PHPTest_model constructor.
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Получение тестовых даных из базы по логам
	 */
	function getTestCase($data)
	{
		$this->load->database();
		
		$filter = "";
		$queryParams = array();
		
		if ( isset($data['Controller']) ) {
			$filter .= " and Controller = :Controller ";
			$queryParams['Controller'] = $data['Controller'];
		}
		$sql = "
			select
				Controller as \"Controller\",
				Method as \"Method\", 
				max(QueryString) as \"QueryString\",
				max(Post) as \"Post\"
			from PHPLog 
			where POST is not NULL
				and Controller != 'portal'
				and Controller != 'JSConstants'
				and Controller != 'promed'
				and (Method like 'get%' or Method like 'load%')
				{$filter}
			group by
				Controller, Method
		";
		
		return $this->db->query($sql, $queryParams)->result_array();
	}
 
}
?>

<?php

require_once(APPPATH.'models/MedPersonal_model.php');

class Samara_MedPersonal_model extends MedPersonal_model {
	
    function __construct() {
		parent::__construct();
    }
    
	/**
	 * Получение списка медицинского персонала. Для гридов и комбобоксов
	 * Используется: окно просмотра и редактирования мед. персонала
	 */	
	public function getMedPersonalComboByLpu($data) {
		$fromtable = "v_MedPersonal ";
		if ($data['session']['region']['nick'] == 'ufa') $fromtable = "v_MedPersonal_old ";
		$query = "";
		if (isset($data['Lpu_did'])) $query = " and Lpu_id = ".$data['Lpu_did'].$query;
		if (isset($data['query'])) $query = $query." and Person_SurName like '".$data['query']."%'"."and Upper(Dolgnost_Name) not like '%СЕСТРА%'";
		
		$sql = "
			SELECT
				MedPersonal_id,
				isnull(MedPersonal_TabCode, '') as MedPersonal_TabCode,
				isnull(MedPersonal_Code, '') as MedPersonal_Code,
				ltrim(rtrim(Person_SurName)) + ' ' + ltrim(rtrim(Person_FirName)) + ' ' + ltrim(rtrim(Person_SecName)) as MedPersonal_FIO,
				CASE WHEN WorkData_IsDlo = 1 THEN 'false' ELSE 'true' END as MedPersonal_IsDlo
			FROM ".$fromtable."
			WHERE (1 = 1) {$query}			
		";
		$res = $this->db->query($sql); //, array($data['Lpu_did']));
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}
}
?>

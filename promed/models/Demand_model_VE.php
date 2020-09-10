<?php
/**
* PromedWeb - The New Generation of Medical Statistic Software
* модификация имеющейся модели. В дальнейшем функционал разнесу по RegistryUfa_model или Demond_model
*
*
* @package      All
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Vasinsky Igor
* @version      10.09.2013
*/
class Demand_model_VE extends CI_Model {
	/**
	* constructor
	*/
	public function __construct() {
		parent::__construct();
	}

    /**
    * Модель выборки Уфимских СМО 
    * модификация оригинального Demand_model_VE.php для групповой постановке реестров на очередь формирования Task#18011
    * /?c=Demandve&m=getOrgSmoList
    */
	function getOrgSmoUfaList() {	
	    //Для рабочей БД - необходимо корректировать запрос

		$query = "
             select  
             O.OrgSMO_id Smo_id, 
             Replace(REPLACE(O.OrgSMO_Nick,'(РЕСПУБЛИКА БАШКОРТОСТАН)',''), '''', '') Smo_Name, 
             Replace(REPLACE(O.OrgSMO_Nick,'(РЕСПУБЛИКА БАШКОРТОСТАН)',''), '''', '') Smo_Nick
             from v_orgSmo O (nolock) where KLRgn_id=2 and OrgSmo_endDate is null
		";
        
		$res = $this->db->query($query, array());

		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		} 

	}
}
           
?>
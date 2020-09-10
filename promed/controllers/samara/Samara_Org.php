<?php defined('BASEPATH') or die ('No direct script access allowed');


require_once(APPPATH.'controllers/Org.php');

class Samara_Org extends Org {
  
	function __construct() {
		parent::__construct();
	}    

	/**
     * Получение списка организаций по заданным фильтрам
     */
    function getOrgList() {

        $this->load->model("Samara_Org_model", "orgmodel");

        $data = $this->ProcessInputData('getOrgList',true);
        if ($data === false) {return false;}

        switch ($data['OrgType']) {
            case 'anatom':
                $org_data = $this->orgmodel->getOrgAnatomList($data);
                break;

            case 'bank':
                $org_data = $this->orgmodel->getOrgBankList($data);
                break;

            case 'dep':
                $org_data = $this->orgmodel->getOrgDepList($data);
                break;

            case 'farm':
                $org_data = $this->orgmodel->getOrgFarmacyList($data);
                break;
			case 'lpu_all':
            case 'lpu':
                $org_data = $this->orgmodel->getLpuList($data);
                break;

            case 'lic':
                $org_data = $this->orgmodel->getOrgLicList($data);
                break;

            case 'military':
                $org_data = $this->orgmodel->getOrgMilitaryList($data);
                break;

            case 'smo':
                $err = getInputParams($data, $this->inputRules['getOrgSmoList'], false);

                if ( strlen($err) > 0 ) {
                    echo json_return_errors($err);
                    return false;
                }

                $org_data = $this->orgmodel->getOrgSmoList($data);
                break;

            case 'smodms':
                $err = getInputParams($data, $this->inputRules['getOrgSmoList'], false);

                if ( strlen($err) > 0 ) {
                    echo json_return_errors($err);
                    return false;
                }

                $org_data = $this->orgmodel->getOrgSmoDmsList($data);
                break;

            default:
                $org_data = $this->orgmodel->getOrgList($data);
                break;
        }
        $this->ProcessModelList($org_data, true, true)->ReturnData();

        return true;
    }   
    
}

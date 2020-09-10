<?php defined('BASEPATH') or die ('No direct script access allowed');
require_once(APPPATH.'models/EvnPrescrTreat_model.php');

class Ufa_EvnPrescrTreat_model extends EvnPrescrTreat_model {
	/**
	 * construct
	 */
	function __construct() {
		//parent::__construct();
            parent::__construct();
	}
	
	/**
     * Получение значения по умолчанию для полей в лекарственном лечении
	*/
    function getDrugPackData($data) {
        $query = "";
		$set = "";
	
		if (!empty($data['Drug_id'])) {
			$set = "Set @Drug_id =  {$data['Drug_id']}; ";
		}
		if (!empty($data['DrugComplexMnn_id'])) {
			$set .= "Set @DrugComplexMnn_id = {$data['DrugComplexMnn_id']}; ";
		}
		
		$query = "
		
		Declare
			@DrugComplexMnn_id bigint,
			@Drug_id bigint;

		{$set}

		if @DrugComplexMnn_id is  null
			Select top 1 @DrugComplexMnn_id = DrugComplexMnn_id 
				from rls.v_drug with (nolock)
					where Drug_id = @Drug_id;
				
		with tbl as (
				Select DrugComplexMnn_id, GoodsUnitType_id,
					case when GoodsUnitType_id = 1 then Kol2Parent else 0 end Fas_Kolvo,
					case when GoodsUnitType_id = 1 then Kol2Parent else 0 end Fas_NKolvo,
					case when GoodsUnitType_id = 1 then GoodsUnit_id else 0 end GoodsUnit_id,
					case when GoodsUnitType_id = 1 then GoodsUnit_Nick else '' end GoodsUnit_Nick,
					case when GoodsUnitType_id = 2 then Kol2Parent else 0 end FasMass_Kolvo,
					case when GoodsUnitType_id = 2 then GoodsUnit_id else 0 end FasMass_GoodsUnit_id,
					case when GoodsUnitType_id = 2 then GoodsUnit_Nick else '' end FasMass_GoodsUnit_Nick,
					case when GoodsUnitType_id = 3 then Kol2Parent else 0 end DoseMass_Kolvo,
					isnull(DoseMass_Type, '') DoseMass_Type,
					case when GoodsUnitType_id = 3 then GoodsUnit_id else 0 end DoseMass_GoodsUnit_id,
					case when GoodsUnitType_id = 3 then GoodsUnit_Nick else '' end DoseMass_GoodsUnit_Nick
				 from r2.fn_GoodsPackCount(@DrugComplexMnn_id)
				 )
				 Select DrugComplexMnn_id, 
					max(Fas_Kolvo) Fas_Kolvo,
					max(Fas_NKolvo) Fas_NKolvo,
					max(GoodsUnit_id) GoodsUnit_id,
					max(GoodsUnit_Nick) GoodsUnit_Nick,
					max(FasMass_Kolvo) FasMass_Kolvo,
					max(FasMass_GoodsUnit_id) FasMass_GoodsUnit_id,
					max(FasMass_GoodsUnit_Nick) FasMass_GoodsUnit_Nick,
					max(DoseMass_Kolvo) DoseMass_Kolvo,
					max(DoseMass_Type) DoseMass_Type,
					max(DoseMass_GoodsUnit_id) DoseMass_GoodsUnit_id,
					max(DoseMass_GoodsUnit_Nick) DoseMass_GoodsUnit_Nick
				  from tbl 
					group by DrugComplexMnn_id
		";
		
        if (!empty($query)) {
            $result = $this->getFirstRowFromQuery($query, $data);
            $result['success'] = true;
            return $result;
        } else {
            return false;
        }
    }

}
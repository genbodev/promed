<?php defined('BASEPATH') or die ('No direct script access allowed');
require_once(APPPATH . 'models/_pgsql/EvnPrescrTreat_model.php');

class Ufa_EvnPrescrTreat_model extends EvnPrescrTreat_model
{
	/**
	 * construct
	 */
	function __construct()
	{
		//parent::__construct();
		parent::__construct();
	}

	/**
	 * Получение значения по умолчанию для полей в лекарственном лечении
	 */
	function getDrugPackData($data)
	{
		$query = "";
		$set = "";
		
		$params = [
			'Drug_id' => !empty($data['Drug_id'])?$data['Drug_id']:null,
			'DrugComplexMnn_id' => !empty($data['DrugComplexMnn_id'])?$data['DrugComplexMnn_id']:null,
		];

		$query = "
		with tbl as (
				select DrugComplexMnn_id, GoodsUnitType_id,
					case when GoodsUnitType_id = 1 then Kol2Parent else 0 end Fas_Kolvo,
					case when GoodsUnitType_id = 1 then Kol2Parent else 0 end Fas_NKolvo,
					case when GoodsUnitType_id = 1 then GoodsUnit_id else 0 end GoodsUnit_id,
					case when GoodsUnitType_id = 1 then GoodsUnit_Nick else '' end GoodsUnit_Nick,
					case when GoodsUnitType_id = 2 then Kol2Parent else 0 end FasMass_Kolvo,
					case when GoodsUnitType_id = 2 then GoodsUnit_id else 0 end FasMass_GoodsUnit_id,
					case when GoodsUnitType_id = 2 then GoodsUnit_Nick else '' end FasMass_GoodsUnit_Nick,
					case when GoodsUnitType_id = 3 then Kol2Parent else 0 end DoseMass_Kolvo,
					coalesce(DoseMass_Type, '') DoseMass_Type,
					case when GoodsUnitType_id = 3 then GoodsUnit_id else 0 end DoseMass_GoodsUnit_id,
					case when GoodsUnitType_id = 3 then GoodsUnit_Nick else '' end DoseMass_GoodsUnit_Nick
				from r2.fn_GoodsPackCount(
				 	coalesce(
				 		:DrugComplexMnn_id::bigint, (select DrugComplexMnn_id from rls.v_drug where Drug_id = :Drug_id limit 1)
				 		)
				 	)
				)
				select
				 	DrugComplexMnn_id as \"DrugComplexMnn_id\",
					max(Fas_Kolvo) as \"Fas_Kolvo\",
					max(Fas_NKolvo) as \"Fas_NKolvo\",
					max(GoodsUnit_id) as \"GoodsUnit_id\",
					max(GoodsUnit_Nick) as \"GoodsUnit_Nick\",
					max(FasMass_Kolvo) as \"FasMass_Kolvo\",
					max(FasMass_GoodsUnit_id) as \"FasMass_GoodsUnit_id\",
					max(FasMass_GoodsUnit_Nick) as \"FasMass_GoodsUnit_Nick\",
					max(DoseMass_Kolvo) as \"DoseMass_Kolvo\",
					max(DoseMass_Type) as \"DoseMass_Type\",
					max(DoseMass_GoodsUnit_id) as \"DoseMass_GoodsUnit_id\",
					max(DoseMass_GoodsUnit_Nick) as \"DoseMass_GoodsUnit_Nick\"
				from tbl 
					group by \"DrugComplexMnn_id\"
		";

		if (!empty($query)) {
			$result = $this->getFirstRowFromQuery($query, $params);
			$result['success'] = true;
			return $result;
		} else {
			return false;
		}
	}

}
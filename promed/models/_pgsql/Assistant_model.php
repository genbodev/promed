<?php
defined('BASEPATH') or die ('No direct script access allowed');

class Assistant_model extends SwPgModel
{

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	public function loadLabRequestGrid($data)
	{
		
		$query = "
			select 
				elr.EvnLabRequest_id as \"EvnLabRequest_id\",
				case when ed.EvnDirection_IsCito = 1 then 'true' else 'false' end as \"EvnDirection_IsCito\",
				ed.EvnDirection_Descr as \"EvnDirection_Descr\",
				ed.EvnDirection_Num as \"EvnDirection_Num\",
				ed.EvnDirection_setDate as \"EvnDirection_setDate\",
				pt.PrehospType_Name as \"PrehospType_Name\",
				trim(coalesce(PS.Person_Surname, '') || ' ' || coalesce(PS.Person_Firname, '') || ' ' || coalesce(PS.Person_Secname, '')) as \"Person_FIO\"
			from 
				v_EvnLabRequest elr
				left join v_EvnDirection ed on elr.EvnDirection_id = ed.EvnDirection_id
				left join v_PrehospType pt on pt.PrehospType_id = ed.PrehospType_did
				left join v_PersonState PS on PS.Person_id = elr.Person_id
			where
				elr.Lpu_id = :Lpu_id
		";
		
		$result = $this->db->query($query, [
			'Lpu_id' => $data['Lpu_id']
		]);

		if (!is_object($result) )
			return false;

		return $result->result('array');
	}

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	public function loadLabRequest($data)
	{
		
		$query = "
			select 
				elr.EvnLabRequest_id as \"EvnLabRequest_id\",
				ed.EvnDirection_IsCito as \"EvnDirection_IsCito\",
				ed.EvnDirection_Descr as \"EvnDirection_Descr\",
				ed.EvnDirection_Num as \"EvnDirection_Num\",
				ed.EvnDirection_setDate as \"EvnDirection_setDate\",
				pt.PrehospType_Name as \"PrehospType_Name\",
				trim(coalesce(PS.Person_Surname, '') || ' ' || coalesce (PS.Person_Firname, '') || ' ' || coalesce(PS.Person_Secname, '')) as \"Person_FIO\"
			from 
				v_EvnLabRequest elr
				left join v_EvnDirection ed on elr.EvnDirection_id = ed.EvnDirection_id
				left join v_PrehospType pt on pt.PrehospType_id = ed.PrehospType_did
				left join v_PersonState PS on PS.Person_id = elr.Person_id
			where
				elr.Lpu_id = :Lpu_id and
				elr.EvnLabRequest_id = :EvnLabRequest_id
		";
		
		$result = $this->db->query($query, [
			'Lpu_id' => $data['Lpu_id'],
			'EvnLabRequest_id' => $data['EvnLabRequest_id']
		]);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
		
	}

	/**
	 * @param $data
	 */
	function saveLabRequest($data) {
		
		
		
	}

	function checkReagentsGodnDate($data){
		
		$query = "
			select
				vs.Storage_id as \"Storage_id\"
			from 
				v_StorageStructLevel ssl
			left join v_Storage vs on vs.Storage_id = ssl.Storage_id
			where
				MedService_id = :MedService_id
		";

		$resp = $this->db->query($query, array(
			'MedService_id' => $data['MedService_id']
		));

		if(!is_object($resp)){
			return false;
		}

		$result = $resp->result('array');

		$reagents = array();

		foreach ($result as $res) {

			$data['Storage_id'] = $res['Storage_id'];

			$query = "
				select 
					ST.Storage_name as \"Storage_name\",
					PS.PrepSeries_Ser as \"PrepSeries_Ser\",
					PS.PrepSeries_GodnDate as \"PrepSeries_GodnDate\",
					DG.Drug_id as \"Drug_id\",
					DG.Drug_name as \"Drug_name\",
					DS.DrugShipment_Name as \"DrugShipment_Name\"		
				from 
					v_DrugOstatRegistry DOR
				left join rls.PrepSeries PS on PS.PrepSeries_id = DOR.PrepSeries_id
				left join v_Storage ST on ST.Storage_id = DOR.Storage_id
				left join rls.Drug DG on DG.Drug_id = DOR.Drug_id
				left join v_DrugShipment DS on DS.DrugShipment_id = DOR.DrugShipment_id
				where 
					DOR.Storage_id = :Storage_id
					and (DOR.DrugOstatRegistry_Kolvo is not null and DOR.DrugOstatRegistry_Kolvo > 0)
				";

			$resp = $this->db->query($query, array(
				'Storage_id' => $data['Storage_id']
			));

			if (is_object($resp)) {

				$reagents_data = $resp->result('array');

				$ReagentsGodnDate = strtotime(date("d.m.Y", mktime(0, 0, 0, date('m'), date('d') + $data['ReagentsGodnDate'], date('Y'))));

				foreach ($reagents_data as $reagent) {
					$reagent['PrepSeries_GodnDate'] = strtotime($reagent['PrepSeries_GodnDate']);
					if ($reagent['PrepSeries_GodnDate'] <= $ReagentsGodnDate) {
						if ($reagent['PrepSeries_GodnDate'] <= (strtotime(date('d.m.Y')))) {
							$reagent['Ostat_GodnDate'] = 0;
						} else {
							$seconds = abs($ReagentsGodnDate - $reagent['PrepSeries_GodnDate']);
							$reagent['Ostat_GodnDate'] = $data['ReagentsGodnDate'] - floor($seconds / 86400);
						}
						$reagent['PrepSeries_GodnDate'] = date('d.m.Y', $reagent['PrepSeries_GodnDate']);

						$reagents[] = $reagent;
					}
				}
			}

			usort($reagents, function ($a, $b) {
				return ($a['Ostat_GodnDate'] - $b['Ostat_GodnDate']);
			});
		}
		return $reagents;
	}
}
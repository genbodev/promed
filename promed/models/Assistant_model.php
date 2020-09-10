<?php
defined('BASEPATH') or die ('No direct script access allowed');

class Assistant_model extends CI_Model
{
	/**
	 * Assistant_model constructor.
	 */
    function __construct()
    {
        parent::__construct();
    }

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	function loadLabRequestGrid($data) {
		
		$query = "select 
			elr.EvnLabRequest_id,
			case when ed.EvnDirection_IsCito = 1 then 'true' else 'false' end as EvnDirection_IsCito,
			ed.EvnDirection_Descr,
			ed.EvnDirection_Num,
			ed.EvnDirection_setDate,
			pt.PrehospType_Name,
			RTRIM(LTRIM(ISNULL(PS.Person_Surname, '') + ' ' + ISNULL(PS.Person_Firname, '') + ' ' + ISNULL(PS.Person_Secname, ''))) as Person_FIO
			from 
				v_EvnLabRequest elr WITH (NOLOCK)
				left join v_EvnDirection ed with (nolock) on elr.EvnDirection_id = ed.EvnDirection_id
				left join v_PrehospType pt with (nolock) on pt.PrehospType_id = ed.PrehospType_did
				left join v_PersonState PS with (nolock) on PS.Person_id = elr.Person_id
			where
				elr.Lpu_id = :Lpu_id";
		
		$result = $this->db->query($query, array(
			'Lpu_id' => $data['Lpu_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
		
	}

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	function loadLabRequest($data) {
		
		$query = "select 
			elr.EvnLabRequest_id,
			ed.EvnDirection_IsCito,
			ed.EvnDirection_Descr,
			ed.EvnDirection_Num,
			ed.EvnDirection_setDate,
			pt.PrehospType_Name,
			RTRIM(LTRIM(ISNULL(PS.Person_Surname, '') + ' ' + ISNULL(PS.Person_Firname, '') + ' ' + ISNULL(PS.Person_Secname, ''))) as Person_FIO
			from 
				v_EvnLabRequest elr WITH (NOLOCK)
				left join v_EvnDirection ed with (nolock) on elr.EvnDirection_id = ed.EvnDirection_id
				left join v_PrehospType pt with (nolock) on pt.PrehospType_id = ed.PrehospType_did
				left join v_PersonState PS with (nolock) on PS.Person_id = elr.Person_id
			where
				elr.Lpu_id = :Lpu_id and
				elr.EvnLabRequest_id = :EvnLabRequest_id";
		
		$result = $this->db->query($query, array(
			'Lpu_id' => $data['Lpu_id'],
			'EvnLabRequest_id' => $data['EvnLabRequest_id']
		));

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
				vs.Storage_id
			from 
				v_StorageStructLevel ssl with (nolock)
			left join v_Storage vs with (nolock) on vs.Storage_id = ssl.Storage_id
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
					ST.Storage_name,
					PS.PrepSeries_Ser,
					PS.PrepSeries_GodnDate,
					DG.Drug_id,
					DG.Drug_name,
					DS.DrugShipment_Name		
				from 
					v_DrugOstatRegistry DOR with (nolock)
				left join rls.PrepSeries PS with (nolock) on PS.PrepSeries_id = DOR.PrepSeries_id
				left join v_Storage ST with (nolock) on ST.Storage_id = DOR.Storage_id
				left join rls.Drug DG with (nolock) on DG.Drug_id = DOR.Drug_id
				left join v_DrugShipment DS with (nolock) on DS.DrugShipment_id = DOR.DrugShipment_id
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
						$reagent['PrepSeries_GodnDate'] = $reagent['PrepSeries_GodnDate']->format('d.m.Y');
						if (strtotime($reagent['PrepSeries_GodnDate']) <= $ReagentsGodnDate) {
							if (strtotime($reagent['PrepSeries_GodnDate']) <= (strtotime(date('d.m.Y')))) {
								$reagent['Ostat_GodnDate'] = 0;
							} else {
								$seconds = abs($ReagentsGodnDate - strtotime($reagent['PrepSeries_GodnDate']));
								$reagent['Ostat_GodnDate'] = $data['ReagentsGodnDate'] - floor($seconds / 86400);
							}

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

?>

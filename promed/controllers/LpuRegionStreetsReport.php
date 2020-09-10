<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* LpuRegionStreetsReport - контроллер для отчета по зонам обслуживания.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Pshenitcyn Ivan aka IvP (ipshon@rambler.ru)
* @version      29.12.2009
*/

class LpuRegionStreetsReport extends swController {
	/**
	 * LpuRegionStreetsReport constructor.
	 */
	function __construct()
	{
		parent::__construct();
		$this->inputRules = array(			
		);
	}

	/**
	 * Некая функция
	 */
	function ViewLpuRegionStreetsReport()
	{
		$data = getSessionParams();		
		$this->load->database();
		$this->load->model("LpuRegionStreets_model", "lrsmodel");
		$medpersonalregions_data = $this->lrsmodel->getLpuRegionMedPersonal($data);
		$regions_mp = array();
		if ( $medpersonalregions_data !== false )
		{
			foreach ( $medpersonalregions_data as $row )
			{
				if ( isset($regions_mp[$row['LpuRegion_id']]) )
				{
					$regions_mp[$row['LpuRegion_id']] .= "<br>".$row['Person_fio'];
				}
				else
				{
					$regions_mp[$row['LpuRegion_id']] = $row['Person_fio'];
				}
			}
		}
		$lpuregion_data = $this->lrsmodel->getLpuRegionStreetsReport($data);
		$lpu_regions = array();
		if ( $lpuregion_data !== false )
		{
			$lpu_nick = '';
			foreach ( $lpuregion_data as $row )
			{
				if ( count($lpu_regions) == 0 || $lpu_regions[count($lpu_regions) - 1]['LpuRegion_Name'] != $row['LpuRegion_Name'] )
				{
					$mp = '';
					if ( array_key_exists($row['LpuRegion_id'], $regions_mp) )
						$mp = $regions_mp[$row['LpuRegion_id']];
					$lpu_regions[] = array('LpuRegion_Name'=>$row['LpuRegion_Name'], 'LpuRegionType_Name'=>$row['LpuRegionType_Name'], 'Attached_Count'=>$row['attached_count'], 'streets_count' => 1, 'MedPersonal_Name' => $mp, 'streets'=>array());
					if ( $lpu_nick == '' )
						$lpu_nick = $row['Lpu_Nick'];
				}
				$lpu_regions[count($lpu_regions) - 1]['streets'][] = array('KLArea_Name' => $row['KLArea_Name']." ".$row['LpuRegionStreet_HouseSet']);
				$lpu_regions[count($lpu_regions) - 1]['streets_count']++;
			}
			// выводим собственно форму
			$this->load->library('parser');
			$this->parser->parse("attach_streets_report", array('lpu_region_streets_data' => $lpu_regions, 'Lpu_Nick' => $lpu_nick));
		}
	}
}

?>

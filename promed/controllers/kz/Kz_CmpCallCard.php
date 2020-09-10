<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* CmpCalLCard - операции с СМП
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*/
require_once(APPPATH.'controllers/CmpCallCard.php');

class Kz_CmpCallCard extends CmpCallCard {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();	
	}
	
	/**
	 * Print 110u
	 */
	function printCmpCloseCard110() {
		$this->load->library('parser');		

		$data = $this->ProcessInputData('printCmpCloseCard110', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->printCmpCloseCard110($data);
		if ( (!is_array($response)) || (count($response) == 0) || (count($response[0]) == 0) ) {
			echo 'Для карты вызова не заполнена 110у';
			return true;
		} else {
			$response=$response[0];
		}
		$pd = array();
		foreach ($response as $k => $resp) {						
			$pd[$k] = isset($resp) ? $resp : '&nbsp;';			
		}
				
		$parse_data = $pd+array(
			'Condition_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Condition_id'),
			'Cons_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Cons_id'),
			'Behavior_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Behavior_id'),
			'Pupil_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Pupil_id'),
			'Light_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Light_id'),
			'Aniz_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Aniz_id'),
			'Kozha_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Kozha_id'),
			'Heart_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Heart_id'),
			'Noise_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Noise_id'),
			'Pulse_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Pulse_id'),
			'Exkurs_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Exkurs_id'),
			'Hale_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Hale_id'), 
			'Rattle_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Rattle_id'), 
			'Shortwind_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Shortwind_id'), 
			'Nev_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Nev_id'), 
			'Menen_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Menen_id'), 
			'Eye_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Eye_id'),
			'Chmn_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Chmn_id'), 
			'Reflex_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Reflex_id'),
			'Move_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Move_id'), 
			'Bol_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Bol_id'), 
			'Afaz_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Afaz_id'), 
			'Sbabin_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Sbabin_id'), 
			'Soppen_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Soppen_id'), 
			'Zev_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Zev_id'), 
			'Mindal_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Mindal_id'), 
			'Lang_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Lang_id'), 
			'Gaste_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Gaste_id'),
			'Sympt_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Sympt_id'),
			'Liver_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Liver_id'),
			'Selez_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Selez_id'),
			'Moch_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Moch_id'),
			'Menst_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Menst_id'),
			'Per_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Per_id'),
			'Result_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Result_id'),
			'ResultV_id' => $this->getComboRel($response['CmpCloseCard_id'], 'ResultV_id'),
			'Travm_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Travm_id')
		);
		//var_dump($parse_data);
		$this->parser->parse('print_form110u_kz', $parse_data);
	}
	
}

<?php defined('BASEPATH') or die('No direct script access allowed');
/**
 * CmpCallCard - контроллер для СМП. Версия для Уфы
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 */

require_once(APPPATH.'controllers/CmpCallCard.php');

class Ufa_CmpCallCard extends CmpCallCard {

	
	/**
	 * @desc описание
	 */
	function printCmpCloseCard110() {	
		$this->load->library('parser');
		$data = $this->ProcessInputData('printCmpCallCardHeader', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->printCmpCallCardHeader($data);
		if ( (!is_array($response)) || (count($response[0]) == 0) ) {
			echo 'Ошибка при получении данных';
			return true;
		} else {
			$response=$response[0];
		}
		$pd = array();
		foreach ($response as $k => $resp) {
			$pd[$k] = isset($resp) ? $resp : '&nbsp;';
		}
		$this->parser->parse('print_form110u_ufa', $pd);
		/*
		$this->load->library('parser');
	
		

		$data = $this->ProcessInputData('printCmpCloseCard110', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->printCmpCloseCard110($data);
		if ( (!is_array($response)) || (count($response[0]) == 0) ) {
			echo 'Ошибка при получении данных';
			return true;
		} else {
			$response=$response[0];
		}
		$pd = array();
		foreach ($response as $k => $resp) {						
			$pd[$k] = isset($resp) ? $resp : '&nbsp;';			
		}
		
		if (
			$pd['AcceptDate'] != '&nbsp;' 
			&& $pd['AcceptDate'] != '' 
			&& $pd['AcceptDate'] != '01.01.1900') $pd['CallCardDate'] = $pd['AcceptDate'];
		
		$parse_data = $pd+array(
			'C_PersonRegistry_id' => $this->getComboRel($response['CmpCloseCard_id'], 'PersonRegistry_id')
			,'C_AgeType' => $this->getComboRel($response['CmpCloseCard_id'], 'AgeType_id')
			,'C_CallTeamPlace_id' => $this->getComboRel($response['CmpCloseCard_id'], 'CallTeamPlace_id')
			,'C_Delay_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Delay_id')
			,'C_TeamComplect_id' => $this->getComboRel($response['CmpCloseCard_id'], 'TeamComplect_id')
			,'C_CallPlace_id' => $this->getComboRel($response['CmpCloseCard_id'], 'CallPlace_id')
			,'C_AccidentReason_id' => $this->getComboRel($response['CmpCloseCard_id'], 'AccidentReason_id')			
			,'Condition_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Condition_id')
			,'Behavior_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Behavior_id')
			,'Cons_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Cons_id')
			,'Pupil_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Pupil_id')
			,'Kozha_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Kozha_id')
			,'Hypostas_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Hypostas_id')
			,'Crop_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Crop_id')
			,'Hale_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Hale_id')
			,'Rattle_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Rattle_id')
			,'Shortwind_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Shortwind_id')
			,'Heart_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Heart_id')
			,'Noise_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Noise_id')
			,'Pulse_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Pulse_id')
			,'Lang_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Lang_id')
			,'Gaste_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Gaste_id')
			,'Liver_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Liver_id')
			
			,'Complicat_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Complicat_id')
			,'ComplicatEf_id' => $this->getComboRel($response['CmpCloseCard_id'], 'ComplicatEf_id')
			,'Result_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Result_id')
			,'Patient_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Patient_id')
			,'TransToAuto_id' => $this->getComboRel($response['CmpCloseCard_id'], 'TransToAuto_id')
			//,'DeportClose_id' => $this->getComboRel($response['CmpCloseCard_id'], 'DeportClose_id')
			//,'DeportFail_id' => $this->getComboRel($response['CmpCloseCard_id'], 'DeportFail_id')			
			,'ResultUfa_id' => $this->getComboRel($response['CmpCloseCard_id'], 'ResultUfa_id')			
			
		);
		
		$this->parser->parse('print_form110u', $parse_data);
		 */
	}
	

	
	/**
	 * @desc Шапка для печати
	 */
	function printCmpCallCardHeader() {
		$this->load->library('parser');
		$data = $this->ProcessInputData('printCmpCallCardHeader', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->printCmpCallCardHeader($data);
		if ( (!is_array($response)) || (count($response[0]) == 0) ) {
			echo 'Ошибка при получении данных';
			return true;
		} else {
			$response=$response[0];
		}
		$pd = array();
		foreach ($response as $k => $resp) {
			$pd[$k] = isset($resp) ? $resp : '&nbsp;';
		}

		$this->parser->parse('print_form110u_ufa', $pd);
	}	
	
	
}
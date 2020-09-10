<?php defined('BASEPATH') or die('No direct script access allowed');
/**
 * CmpCallCard - контроллер для СМП. Версия для Астрахани
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 */

require_once(APPPATH.'controllers/CmpCallCard.php');

class Astra_CmpCallCard extends CmpCallCard {

	/**
	 * Печать карты закрытия вызова 110у
	 */	
	public function printCmpCloseCard110() {
		$data = $this->ProcessInputData( 'printCmpCloseCard110', true );
		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->printCmpCloseCard110( $data );

		if ( !is_array( $response ) || !sizeof( $response ) || !sizeof( $response[ 0 ] ) ) {
			echo 'Для карты вызова не заполнена 110у';
			return true;
		}
		
		$response = $response[0];
		$response['druglist'] = $this->dbmodel->loadCmpCallCardDrugList($data);
		$response['uslugalist'] = $this->dbmodel->loadCmpCallCardUslugaGrid($data);
		$pd = array();
		foreach( $response as $k => $resp ){
			$pd[ $k ] = isset( $resp ) ? $resp : '&nbsp;';
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

			,'C_CallPovodNew_id' => $this->getCombo($response['CallPovodNew_id'], 'CmpReasonNew')
			,'C_CmpCallPlaceType_id' => $this->getCombo($response['CmpCallPlaceType_id'], 'CmpCallPlaceType')
			//,'C_CallPlace_id' => $this->getComboRel($response['CmpCloseCard_id'], 'CallPlace_id')
			,'C_AccidentReason_id' => $this->getComboRel($response['CmpCloseCard_id'], 'AccidentReason_id')
			,'C_PersonSocial_id' => $this->getComboRel($response['CmpCloseCard_id'], 'PersonSocial_id')
			,'C_Trauma_id' => $this->getComboRel($response['CmpCloseCard_id'], 'Trauma_id')
			//,'С_AppealReason_id' => $this->getComboRel($response['CmpCloseCard_id'], 'AppealReason_id')
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
		);


		
		$parse_data["CmpCallType_Code"] = '';
		
		if(!empty($response["CmpCallType_Code"])){
			$parse_data["CmpCallType_Code"] = '<div class="innerwrapper">Первичный';
			$parse_data["CmpCallType_Code"] .= ($response["CmpCallType_Code"]==1)?'<div class="v_ok"></div></div>':'<div class="v_no"></div></div>';
			
			$parse_data["CmpCallType_Code"] .= '<div class="innerwrapper">Повторный';
			$parse_data["CmpCallType_Code"] .= ($response["CmpCallType_Code"]==2)?'<div class="v_ok"></div></div>':'<div class="v_no"></div></div>';
			
			$parse_data["CmpCallType_Code"] .= '<div class="innerwrapper">Вызов на себя другой бригады';
			$parse_data["CmpCallType_Code"] .= ($response["CmpCallType_Code"]==9)?'<div class="v_ok"></div></div>':'<div class="v_no"></div></div>';
			
			$parse_data["CmpCallType_Code"] .= '<div class="innerwrapper">В пути';
			$parse_data["CmpCallType_Code"] .= ($response["CmpCallType_Code"]==4)?'<div class="v_ok"></div></div>':'<div class="v_no"></div></div>';
		}
		
		$parse_data["isAlcoBlocks"] = $this->printYesNoBlocks($parse_data["isAlco"]);
		$parse_data["isHaleBlocks"] = $this->printYesNoBlocks($parse_data["isHale"]);
		$parse_data["isPeritBlocks"] = $this->printYesNoBlocks($parse_data["isPerit"]);
		
		$parse_data["Patient_id"] .= '<div class="innerwrapper">Сигнальный лист вручен';
		$parse_data["Patient_id"] .= ($parse_data["CmpCloseCard_IsSignList"]==2)?'<div class="v_ok"></div></div>':'<div class="v_no"></div></div>';
		//var_dump($parse_data["isAlcoBlocks"]); exit;
		$parse_data['ResultUfa_id'] = $this->getComboRel($response['CmpCloseCard_id'], 'ResultUfa_id');
		
		$equipment = $this->dbmodel->loadCmpCloseCardEquipmentPrintForm( array( 'CmpCloseCard_id' => $response[ 'CmpCloseCard_id' ] ) );
		if ( !empty( $equipment ) ) {
			$parse_data[ 'equipment' ] = $equipment;
		}

		$this->load->library( 'parser' );

		$this->parser->parse( 'print_form110u_astra', $parse_data );
	}

	/**
	 * мотороллер был не мой, я просто разместил объяву...
	 */
	private function printYesNoBlocks($val){
		$data = '<div class="innerwrapper">Да';
			$data .= ($val==2)?'<div class="v_ok"></div></div>':'<div class="v_no"></div></div>';
			
		$data .= '<div class="innerwrapper">Нет';
			$data .= ($val==1)?'<div class="v_ok"></div></div>':'<div class="v_no"></div></div>';
			
		return $data;
	}
	
}
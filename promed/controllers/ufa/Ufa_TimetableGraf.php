<?php

/**
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package	  All
 * @access	   public
 * @copyright	Copyright (c) 2009 Swan Ltd.
 * @author	   Andrew Markoff
 * @version	  01.09.2009
 * @property LpuStructure_model dbmodel
 */
require_once(APPPATH . 'controllers/TimetableGraf.php');

class Ufa_TimetableGraf extends TimetableGraf {

	
	/**
	 * Запись человека на бирку
	 */
	function Apply() {
		$this->load->model("Options_model", "opmodel");
		$this->load->model('EvnDirection_model', 'EvnDirection');
		$this->inputRules['Apply'] = array_merge($this->inputRules['Apply'], $this->EvnDirection->getSaveRules());
		$data = $this->ProcessInputData('Apply',true);
		if ($data === false) {
			return false; 
		}
		$data['Day'] = TimeToDay(time()) ;
		$data['object'] = 'TimetableGraf';
		$data['TimetableObject_id'] = 1;
		
		// Проверка наличия блокирующего примечания
		$this->load->model("Annotation_model", "anmodel");
		$anncheck = $this->anmodel->checkBlockAnnotation($data);		
		if (is_array($anncheck) && count($anncheck)) {
			$this->ReturnData(array (
				'success' => false,
				'Error_Msg' => "Запись на бирку невозможна. См. примечание."
			));
			return false;
		}
		
		$globalOptions = $this->opmodel->getOptionsGlobals($data);

		$enable_semiautomatic_identification = (isset($globalOptions['globals']['enable_semiautomatic_identification']) && $globalOptions['globals']['enable_semiautomatic_identification'] == 1 ? true : false);

		// Для Уфы при добавлении посещения и включенном режиме полуавтоматической идентификации...
		if ( empty($data['TimetableGraf_id']) && $enable_semiautomatic_identification === true ) {
			// ... производится идентификация застрахованного
			$this->load->model('PersonIdentRequest_model', 'identmodel');

			$response = $this->identmodel->doPersonIdentOnEvnSave($data, null, $globalOptions);

			if ( $response['success'] === false && strlen($response['errorMsg']) > 0 ) {
				echo json_return_errors($response['errorMsg']);
				return false;
			}
		}
		
		$this->dbmodel->beginTransaction();
		
		do { // обертываем в цикл для возможности выхода при ошибке
		
			if (!empty($data['Unscheduled']) && 'polka' == $data['LpuUnitType_SysNick']) {
				// В случае незапланированного приема создается дополнительная бирка с текущим временем и запись производится на нее
				if ( !$this->createUnscheduled($data) ) {
					$val = array (
						'success' => false
					);
					break;
				}
			}
			
			$response = $this->dbmodel->Apply($data);
			
			if ( $response['success'] ) {
				$data['EvnDirection_id'] = $response['EvnDirection_id'];
				
				$val = array(
					'success' => true,
					'object' => $response['object'],
					'id' => $response['id']
				);

				// сохраняем заказ, если есть необходимость
				$this->load->model('EvnUsluga_model', 'eumodel');
				try {
					$this->eumodel->saveUslugaOrder($data);
				} catch (Exception $e) {
					$this->dbmodel->rollbackTransaction();
					$val['success'] = false;
					$val['Error_Msg'] = toUTF($e->getMessage());
					$this->ReturnData($val);
					return false;
				}
				
				// Подсчитаем факт использования расписания
				if ( !$this->countApply($data) ) {
					$val = array (
						'success' => false
					);
					break;
				}
				
				// Генерируем уведомления
				if( $data['LpuUnitType_SysNick'] == 'polka' ) {
					if ( !$this->genNotice($data) ) {
						$val = array (
							'success' => false
						);
						break;
					}
				}
				$this->dbmodel->commitTransaction();
			} elseif ( isset($response['queue']) ) {
				array_walk($response['queue'], 'ConvertFromWin1251ToUTF8');
				$val = array(
					'success' => false,
					'Person_id' => $response['Person_id'],
					'Server_id' => $response['Server_id'],
					'PersonEvn_id' => $response['PersonEvn_id'],
					'queue' => $response['queue']
				);
				break;
			} elseif ( isset($response['warning']) ) {
				$val = array(
					'success' => false,
					'Person_id' => $response['Person_id'],
					'Server_id' => $response['Server_id'],
					'PersonEvn_id' => $response['PersonEvn_id'],
					'warning' => toUTF($response['warning'])
				);
				break;
			} else {
				$val['success'] = false;
				$val['Error_Msg'] = toUTF($response['Error_Msg']);
				break;
			}
			
		} while (0);

		if ( !$val['success'] ) {
			// если что-то пошло не так, откатываем транзакцию
			$this->dbmodel->rollbackTransaction();
		}
		
		$this->ReturnData($val);
	}
}

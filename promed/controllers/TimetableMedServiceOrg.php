<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * TimetableMedServiceOrg - работа с расписанием службы, услуг службы
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2011 Swan Ltd.
 * @author       Petukhov Ivan aka Lich (megatherion@list.ru)
 * @version      27.12.2011
 */

/**
 * Загрузка базового контроллера для работы с расписанием
 */
require_once("Timetable.php");

/**
 * @property TimetableMedServiceOrg_model dbmodel
 * @property MedService_model msmodel
 */
class TimetableMedServiceOrg extends Timetable
{
	
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	
		$this->inputRules += array(
			'getTimetableMedServiceOrgForEdit' => array(
				array(
					'field' => 'StartDay',
					'label' => 'Дата начала расписания',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'MedService_id',
					'label' => 'Идентификатор службы',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'PanelID',
					'label' => 'Идентификатор панели на клиенте',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'IsForDirection',
					'label' => 'Вывод расписания для направления?',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'readOnly',
					'label' => 'Только просмотр',
					'rules' => '',
					'type' => 'checkbox'
				),
			),
			'createTTMSOSchedule' => array(
				array(
					'field' => 'CreateDateRange',
					'label' => 'Даты приёмов',
					'rules' => 'required',
					'type' => 'daterange'
				),
				array(
					'field' => 'CopyToDateRange',
					'label' => 'Вставить в диапазон',
					'rules' => '',
					'type' => 'daterange'
				),
				array(
					'field' => 'MedService_id',
					'label' => 'Идентификатор службы',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'ScheduleCreationType',
					'label' => 'Тип создания расписания',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'StartTime',
					'label' => 'Начало приёма',
					'rules' => '',
					'type' => 'time'
				),
				array(
					'field' => 'EndTime',
					'label' => 'Конец приёма',
					'rules' => '',
					'type' => 'time',
				),
				array(
					'field' => 'Duration',
					'label' => 'Длительность бирки',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'TimetableType_id',
					'label' => 'Тип бирки',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'CopyDayComments',
					'label' => 'Копировать примечания на день',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'TimetableExtend_Descr',
					'label' => 'Примечание на бирку',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'CopyTTMSOComments',
					'label' => 'Копировать примечания на бирку',
					'rules' => '',
					'type' => 'checkbox'
				),
			),
			'ClearDay' => array(
				array(
					'field' => 'Day',
					'label' => 'Идентификатор дня',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'MedService_id',
					'label' => 'Идентификатор службы',
					'rules' => '',
					'type' => 'id'
				)
			),
			
			'checkBeforeLock' => array(
				array(
					'field' => 'TimetableMedServiceOrg_id',
					'label' => 'Идентификатор бирки',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Org_id',
					'label' => 'Идентификатор человека',
					'rules' => 'required',
					'type' => 'id'
				),
			),
			
			
			'Delete' => array(
				array(
					'field' => 'TimetableMedServiceOrg_id',
					'label' => 'Идентификатор бирки',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'TimetableMedServiceOrgGroup',
					'label' => 'Набор идентификаторов бирок служб',
					'rules' => '',
					'type' => 'string'
				)
			),
			
			'Apply' => array(
				array(
					'field' => 'TimetableMedServiceOrg_id',
					'label' => 'Идентификатор бирки',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Org_id',
					'label' => 'Идентификатор организации',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'ARMType_id',
					'label' => 'Идентификатор типа АРМа',
					'rules' => '',
					'type' => 'id'
				),
			),
			'Clear' => array(
				array(
					'field' => 'cancelType',
					'label' => 'Тип отмены направления',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'TimetableMedServiceOrg_id',
					'label' => 'Идентификатор бирки',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'DirFailType_id',
					'label' => 'Причина отмены направления',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnComment_Comment',
					'label' => 'Комментарий',
					'rules' => 'trim',
					'type' => 'string'
				)
			),
			'editTTMSO' => array(
				array(
					'field' => 'selectedTTMSO',
					'label' => 'Набор идентификаторов бирок, которые редактируются',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'TimetableType_id',
					'label' => 'Тип бирки',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'TimetableExtend_Descr',
					'label' => 'Примечание',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'ChangeTTMSOType',
					'label' => 'Изменить тип бирки',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'ChangeTTMSODescr',
					'label' => 'Изменить примечание',
					'rules' => '',
					'type' => 'checkbox'
				),
			),
			'getFirstTimetableMedServiceOrgDate' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => 'required',
					'type' => 'id'
				)
			)
			
		);
		
		// В конструкторе контроллера сразу открываем хелпер Reg
		$this->load->helper('Reg');
		
		$this->load->database();
		$this->load->model("TimetableMedServiceOrg_model", "dbmodel");
    }

	/**
	 * Получение расписания службы для редактирования в виде чистого HTML
	 */
	function getTimetableMedServiceOrgForEdit()
	{
		
		$data = $this->ProcessInputData('getTimetableMedServiceOrgForEdit', true, true);
		if ($data) {
			$response = $this->dbmodel->getTimetableMedServiceOrgForEdit($data);
			$response['PanelID'] = $data['PanelID'];
			$response['readOnly'] = $data['readOnly'];

			if ( isset($response['success']) && !$response['success']) {
				$this->load->view(
					'reg/timetable_general_error',
					array(
						'Error_Msg' => $response['Error_Msg']
					)
				);
				return true;
			}
			
			$this->load->view(
				'reg/timetable_general_css'
			);
			
			/*$this->load->model("MedService_model", "mpmodel");
			$mpresponse = $this->mpmodel->getMedServiceComment($data);

			$this->load->view(
				'reg/medservice_comment',
				array(
					'data' => $mpresponse
				)
			);*/
			
			
			$this->load->view(
				'reg/timetablemedserviceorg_general_header',
				array(
					'data' => $response
				)
			);
			
			$this->load->model("MedService_model", "msmodel");
			$response['msData'] =  $this->msmodel->getMedServiceInfoForReg($data);
			$response['pmUserData'] = $data;
			$response['PanelID'] = $data['PanelID'];
			$response['readOnly'] = $data['readOnly'];
			
			$this->load->library("TTimetableMedServiceOrg");
			$this->load->view(
				'reg/timetablemedserviceorg_edit_data',
				array(
					'data' => $response
				)
			);
			
			$this->load->view(
				'reg/timetablemedserviceorg_edit_footer',
				array(
					'data' => $response
				)
			);
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Печать расписания в виде таблицы для редактирования
	 */
	function printTimetableMedServiceOrgForEdit()
	{
		
		//Dirty trick, перекидываем параметры из GET в POST
		$_POST = array_merge($_POST, $_GET);
		$data = $this->ProcessInputData('getTimetableMedServiceOrgForEdit', true, true);
		if ($data) {
			
			if ( isset($data['MedService_id']) ) {
				$response = $this->dbmodel->getTimetableMedServiceOrgForEdit($data);
				$response['PanelID'] = $data['PanelID'];
				$response['pmUserData'] = $data;
				$this->load->model("MedService_model", "msmodel");
				$response['msData'] =  $this->msmodel->getMedServiceInfoForReg($data);
				
				if ( isset($response['success']) && !$response['success']) {
					$this->load->view(
						'reg/timetable_general_error',
						array(
							'Error_Msg' => $response['Error_Msg']
						)
					);
					return true;
				}

				$this->load->view(
					'reg/timetable_general_css'
				);
				
				$this->load->view(
					'reg/timetablemedserviceorg_general_header',
					array(
						'data' => $response
					)
				);
				
				
				$this->load->library("TTimetableMedServiceOrg");
				$this->load->view(
					'reg/timetablemedserviceorg_edit_data',
					array(
						'data' => $response
					)
				);
			}
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Создание расписания для службы
	 */
	function createTTMSOSchedule() {
		
		$data = $this->ProcessInputData('createTTMSOSchedule', true, true);
		if ($data) {
			if ( isset($data['MedService_id']) ) {
				If ($data['ScheduleCreationType'] == 1) {
					$response = $this->dbmodel->createTTMSOSchedule($data);
				} else {
					$response = $this->dbmodel->copyTTMSOSchedule($data);
				}
			} else {
				If ($data['ScheduleCreationType'] == 1) {
					$response = $this->dbmodel->createTTMSOScheduleUslugaComplex($data);
				} else {
					$response = $this->dbmodel->copyTTMSOScheduleUslugaComplex($data);
				}
			}
			
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	
	/**
	 * Освобождение дня в расписании службы
	 */
	function ClearDay() {
		
		$data = $this->ProcessInputData('ClearDay', true, true);
		if ($data) {
			$response = $this->dbmodel->ClearDay($data);
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	
	
	
	
	/**
	 * Удаление бирки службы
	 */
	function Delete() {

		$data = $this->ProcessInputData('Delete', true, true);

		if ($data) {
			$response = $this->dbmodel->Delete($data);
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	
	/**
	 * Получение расписания для записи в виде чистого HTML
	 */
	function getTimetableMedServiceOrg() {
		
		$data = $this->ProcessInputData('getTimetableMedServiceOrgForEdit', true, true);
		if ($data) {
			
			//Очистка заблокированных бирок
			$this->dbmodel->unlockByUser($data);
			
			$response = $this->dbmodel->getTimetableMedServiceOrgForEdit($data);
			$response['PanelID'] = $data['PanelID'];
			
			if ( isset($response['success']) && !$response['success']) {
				$this->load->view(
					'reg/timetable_general_error',
					array(
						'Error_Msg' => $response['Error_Msg']
					)
				);
				return true;
			}
			
			$this->load->view(
				'reg/timetable_general_css'
			);
			
			/*$this->load->model("MedService_model", "mpmodel");
			$mpresponse = $this->mpmodel->getMedServiceComment($data);

			$this->load->view(
				'reg/medservice_comment',
				array(
					'data' => $mpresponse
				)
			);*/
			
			$this->load->view(
				'reg/timetablemedserviceorg_general_header',
				array(
					'data' => $response
				)
			);
			
			$this->load->model("MedService_model", "msmodel");
			$response['msData'] =  $this->msmodel->getMedServiceInfoForReg($data);
			$response['pmUserData'] = $data;
			$response['PanelID'] = $data['PanelID'];

			$this->load->library("TTimetableMedServiceOrg");
			if ( !isset($data['IsForDirection']) ) {
				$view = 'reg/timetablemedserviceorg_data';
			} else {
				$view = 'reg/timetablemedserviceorg_data_dir';
			}
			$this->load->view(
				$view,
				array(
					'data' => $response
				)
			);

			$view = 'reg/timetablemedserviceorg_footer';

			$this->load->view(
				$view,
				array(
					'data' => $response
				)
			);
			return true;
		} else {
			return false;
		}
	}
	
	
	/**
	 * Получение расписания для записи на один день в службу
	 * $ForPrint - вариант для печати?
	 */
	function getTimetableMedServiceOrgOneDay($ForPrint = false)
	{
		/**
		 * Дополнительная обработка данных
		 * Обрезаем лишнюю часть адреса
		 */
		function ProcessData($row, $ctrl) {
			
			
			return $row;
		}
		
		$data = $this->ProcessInputData('getTimetableMedServiceOrgForEdit', true, true);
		if ($data === false) { return false; }
		
		//Очистка заблокированных бирок
		$this->dbmodel->unlockByUser($data);
		
		$response = $this->dbmodel->getTimetableMedServiceOrgOneDay($data);
		$response['PanelID'] = $data['PanelID'];
		
		if ( isset($response['success']) && !$response['success']) {
			$this->load->view(
				'reg/timetable_general_error',
				array(
					'Error_Msg' => $response['Error_Msg']
				)
			);
			return true;
		}
		
		$this->load->model("MedService_model", "msmodel");
		$response['msData'] =  $this->msmodel->getMedServiceInfoForReg($data);
		$response['pmUserData'] = $data;
		$response['PanelID'] = $data['PanelID'];
		
		foreach($response['data'] as &$row) {
			$row = ProcessData($row, $this);
		}
		$this->load->library("TTimetableMedServiceOrg");
		
		if ($ForPrint) {
			$view = 'reg/timetablemedserviceorgoneday_print';
		} else {
			$view = 'reg/timetablemedserviceorgoneday';
		}
		
		$this->load->view(
			$view,
			array(
				'data' => $response
			)
		);
		return true;
	}

	/**
	 * Запись человека на бирку
	 */
	function Apply() {
		$data = $this->ProcessInputData('Apply',true);

		if ($data === false) {
			return false; 
		}
		$data['Day'] = TimeToDay(time()) ;

		$data['object'] = 'TimetableMedServiceOrg';
		$data['TimetableObject_id'] = 3;
		$this->dbmodel->beginTransaction();
		
		do { // обертываем в цикл для возможности выхода при ошибке
			
			$response = $this->dbmodel->Apply($data);

			if (!isset($response['success'])) {
				if (!empty($response[0]['Error_Msg'])) {
					$this->ReturnError($response[0]['Error_Msg']);
				} else if (!empty($response['Error_Msg'])) {
					$this->ReturnError($response['Error_Msg']);
				} else {
					$this->ReturnError('Ошибка записи на бирку');
				}
				return false;
			}
			
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

				$this->dbmodel->commitTransaction();
				
			} elseif ( isset($response['queue']) ) {
				array_walk($response['queue'], 'ConvertFromWin1251ToUTF8');
				$val = array(
					'success' => false,
					'Org_id' => $response['Org_id'],
					'Server_id' => $response['Server_id'],
					'queue' => $response['queue']
				);
				break;
			} elseif ( isset($response['warning']) ) {
				$val = array(
					'success' => false,
					'Org_id' => $response['Org_id'],
					'Server_id' => $response['Server_id'],
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
	
	/**
	 * Освобождение бирки
	 */
	function Clear() {
		
		$data = $this->ProcessInputData('Clear', true, true);
		if ($data) {
			$data['object'] = 'TimetableMedServiceOrg';
			$response = $this->dbmodel->Clear($data);
			
			// Пересчитываем кэш по дню, когда была запись
			// Пересчет теперь прямо в хранимке
			$this->ProcessModelSave($response, true, 'Ошибка освобождения бирки.')->ReturnData();
			
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Печать расписания для записи на один день
	 */
	function printTimetableMedServiceOrgOneDay()
	{
		$this->load->view(
			'reg/timetable_general_css'
		);
		$this->getTimetableMedServiceOrgOneDay(true);
		$this->load->view(
			'reg/timetablegrafoneday_print_footer'
		);
	}

	
	/**
	 * Проверка времени записи перед блокировкой бирки
	 */
	function checkBeforeLock() {
		$data = $this->ProcessInputData('checkBeforeLock', true, true);
		if ($data) {
			$response = $this->dbmodel->checkBeforeLock($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}


	/**
	 * Получение первой записи МО на защиту
	 */
	function getFirstTimetableMedServiceOrgDate() {
		$data = $this->ProcessInputData('getFirstTimetableMedServiceOrgDate', true, true);
		if ($data) {
			$response = $this->dbmodel->getFirstTimetableMedServiceOrgDate($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

}
<?php

defined('BASEPATH') or die('No direct script access allowed');

/**
 * TimetableGraf - работа с расписанием в стационаре
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Reg
 * @access       public
 * @copyright    Copyright (c) 2012 Swan Ltd.
 * @author       Petukhov Ivan aka Lich (ethereallich@gmail.com)
 * @version      19.03.2012
 *
 * @property TimetableStac_model $dbmodel
 * @property EvnDirection_model $EvnDirection
 * @property LpuStructure_model $LpuStructure
 */
class TimetableStac extends swController {

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->inputRules = array(
			'setTTSType' => array(
				array(
					'field' => 'TimetableStac_id',
					'label' => 'Идентификатор бирки',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'TimetableType_id',
					'label' => 'Тип бирки',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'TimetableStacGroup',
					'label' => 'Группа идентификаторов бирок стационара',
					'rules' => '',
					'type' => 'string'
				),
			),
			'getTTSHistory' => array(
				array(
					'field' => 'TimetableStac_id',
					'label' => 'Идентификатор бирки',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getStacSheduleSettings' => array(
				array(
					'field' => 'Day_id',
					'label' => 'Идентификатор дня',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'Delete' => array(
				array(
					'field' => 'TimetableStac_id',
					'label' => 'Идентификатор бирки',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'TimetableStacGroup',
					'label' => 'Группа идентификаторов бирок стационара',
					'rules' => '',
					'type' => 'string'
				)
			),
			'getTimetableStacForEdit' => array(
				array(
					'field' => 'StartDay',
					'label' => 'Дата начала расписания',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'PanelID',
					'label' => 'Идентификатор панели на клиенте',
					'rules' => '',
					'type' => 'string',
					'default' => 'TTSSchedulePanel'
				),
			),
			'getTimetableStacSummary' => array(
				array(
					'field' => 'StartDay',
					'label' => 'Дата начала расписания',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'PanelID',
					'label' => 'Идентификатор панели на клиенте',
					'rules' => '',
					'type' => 'string',
					'default' => 'swTTSSSummarySheduleForm'
				),
			),
			'getTimetableStac' => array(
				array(
					'field' => 'StartDay',
					'label' => 'Дата начала расписания',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PanelID',
					'label' => 'Идентификатор панели на клиенте',
					'rules' => '',
					'type' => 'string',
					'default' => 'TTSSchedulePanel'
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
				)
			),
			'getTTSDayComment' => array(
				array(
					'field' => 'Day',
					'label' => 'Идентификатор дня',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => 'required',
					'type' => 'id'
				),
			),
			'saveTTSDayComment' => array(
				array(
					'field' => 'Day',
					'label' => 'Идентификатор дня',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSectionDay_Descr',
					'label' => 'Примечание на день по отделению',
					'rules' => '',
					'type' => 'string'
				)
			),
			'createTTSSchedule' => array(
				array(
					'field' => 'CreateDateRange',
					'label' => 'Даты приёмов',
					'rules' => 'required',
					'type' => 'daterange'
				),
				array(
					'field' => 'StartTime',
					'label' => 'Время начала',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'EndTime',
					'label' => 'Время окончания',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Duration',
					'label' => 'Длительность приема, мин',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Faster',
					'label' => 'Кол-во экстренных бирок',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Regular',
					'label' => 'Кол-во обычных бирок',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'CopyToDateRange',
					'label' => 'Вставить в диапазон',
					'rules' => '',
					'type' => 'daterange'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'ScheduleCreationType',
					'label' => 'Тип создания расписания',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'ManBeds',
					'label' => 'Мужских коек',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'WomanBeds',
					'label' => 'Женских коек',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'CommonBeds',
					'label' => 'Общих коек',
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
					'field' => 'CopyTTSComments',
					'label' => 'Копировать примечания на бирку',
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
					'field' => 'CopyTTSComments',
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
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор врача',
					'rules' => 'required',
					'type' => 'id',
				)
			),
			'Clear' => array(
				array(
					'field' => 'cancelType',
					'label' => 'Тип отмены направления',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'TimetableStac_id',
					'label' => 'Идентификатор бирки',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuUnitType_SysNick',
					'label' => 'Тип',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'DirFailType_id',
					'label' => 'Причина отмены направления',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnStatusCause_id',
					'label' => 'Причина смены статуса',
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
			'Apply' => array(
				array(
					'field' => 'TimetableStac_id',
					'label' => 'Идентификатор бирки',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Evn_pid',
					'label' => 'Идентификатор родительного события',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Evn_id',
					'label' => 'Идентификатор родительного события',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'ARMType_id',
					'label' => 'Идентификатор типа АРМа',
					'rules' => '',
					'type' => 'id'
				),
				array(
					// Отвергнуть предупреждение
					'field' => 'OverrideWarning',
					'label' => 'Отвергнуть предупреждение',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'AnswerQueue',
					'label' => 'Ответ об отмене записи в очередь',
					'rules' => '',
					'type' => 'int'
				)
			),
			'editTTS' => array(
				array(
					'field' => 'selectedTTS',
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
					'field' => 'ChangeTTSType',
					'label' => 'Изменить тип бирки',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'ChangeTTSDescr',
					'label' => 'Изменить примечание',
					'rules' => '',
					'type' => 'checkbox'
				),
			)
		);

		// В конструкторе контроллера сразу открываем хелпер Reg
		$this->load->helper('Reg');

		$this->load->database();
		$this->load->model('TimetableStac_model', 'dbmodel');
	}

	/**
	 * Смена типа бирки в стационаре
	 */
	function setTTSType() {
		$data = $this->ProcessInputData('setTTSType', true, true);
		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->setTTSType($data);
		$this->ProcessModelSave($response, true, $response)->ReturnData();
	}

	/**
	 * Получение истории изменения бирки стационара
	 */
	function getTTSHistory() {
		$data = $this->ProcessInputData('getTTSHistory', true, true);
		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->getTTSHistory($data);
		$this->ProcessModelList($response, true, true, $response)->ReturnData();
	}

	/**
	 * Получение истории изменения бирки стационара
	 */
	function getStacSheduleSettings() {
		$data = $this->ProcessInputData('getStacSheduleSettings', false, true);
		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->getStacSheduleSettings($data['Day_id'], $data['LpuSection_id']);
		$this->ProcessModelList($response, true, true, $response)->ReturnData();
	}

	/**
	 * Удаление бирки стационара
	 */
	function Delete() {
		$data = $this->ProcessInputData('Delete', true, true);
		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->Delete($data);
		$this->ProcessModelSave($response, true, $response)->ReturnData();
	}

	/**
	 * Получение расписания для редактирования в виде чистого HTML
	 */
	function getTimetableStacForEdit() {
		$data = $this->ProcessInputData('getTimetableStacForEdit', true, true);
		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->getTimetableStacForEdit($data);
		$response['PanelID'] = $data['PanelID'];

		if ( isset($response['success']) && !$response['success'] ) {
			$this->load->view(
				'reg/timetable_general_error', array(
					'Error_Msg' => $response['Error_Msg']
				)
			);
			return;
		}

		$this->load->view(
			'reg/timetable_general_css'
		);

		$this->load->model("LpuStructure_model", "LpuStructure");
		$lsresponse = $this->LpuStructure->getLpuSectionInfoForReg($data);
		$lsresponse['PanelID'] = $data['PanelID'];

		$this->load->view(
			'reg/lpusectionstac_comment', array(
				'data' => $lsresponse
			)
		);

		$this->load->view(
			'reg/timetablestac_general_header', array(
				'data' => $response
			)
		);

		$response['lsData'] = $lsresponse;
		$response['pmUserData'] = $data;
		$response['PanelID'] = $data['PanelID'];

		$this->load->library("TTimetableStac");
		$this->load->view(
			'reg/timetablestac_edit_data', array(
				'data' => $response
			)
		);

		$this->load->view(
			'reg/timetablestac_edit_footer', array(
				'data' => $response
			)
		);
	}

	/**
	 * Получение сводного расписания для редактирования в виде чистого HTML
	 */
	function getTimetableStacSummary() {
		$data = $this->ProcessInputData('getTimetableStacSummary', true, true);
		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->getTimetableStacSummary($data);
		$response['PanelID'] = $data['PanelID'];

		if ( isset($response['success']) && !$response['success'] ) {
			$this->load->view(
				'reg/timetable_general_error', array(
					'Error_Msg' => $response['Error_Msg']
				)
			);
			return;
		}

		$this->load->view(
			'reg/timetable_general_css'
		);

		$this->load->view(
			'reg/timetablestac_general_header', array(
				'data' => $response
			)
		);

		$response['pmUserData'] = $data;
		$response['PanelID'] = $data['PanelID'];

		$this->load->library("TTimetableStac");
		$this->load->view(
			'reg/timetablestac_summary_data', array(
				'data' => $response
			)
		);
	}

	/**
	 * Получение расписания для просмотра/записи в виде чистого HTML
	 */
	function getTimetableStac() {
		$data = $this->ProcessInputData('getTimetableStac', true, true);
		if ( $data === false ) {
			return false;
		}

		//Очистка заблокированных бирок
		$this->dbmodel->unlockByUser($data);

		$response = $this->dbmodel->getTimetableStac($data);
		$response['PanelID'] = $data['PanelID'];

		if ( isset($response['success']) && !$response['success'] ) {
			$this->load->view(
				'reg/timetable_general_error', array(
					'Error_Msg' => $response['Error_Msg']
				)
			);
			return;
		}

		$this->load->view(
			'reg/timetable_general_css'
		);

		$this->load->model("LpuStructure_model", "LpuStructure");
		$lsresponse = $this->LpuStructure->getLpuSectionInfoForReg($data);
		$lsresponse['PanelID'] = $data['PanelID'];

		$this->load->view(
			'reg/lpusectionstac_comment', array(
				'data' => $lsresponse
			)
		);

		$this->load->view(
			'reg/timetablestac_general_header', array(
				'data' => $response
			)
		);

		$view = 'reg/timetablestac_data';

		$response['lsData'] = $lsresponse;
		$response['pmUserData'] = $data;
		$response['PanelID'] = $data['PanelID'];

		$this->load->library("TTimetableStac");
		$this->load->view(
			$view, array(
				'data' => $response
			)
		);

		$this->load->model("Queue_model", "qmodel");
		$data['LpuSection_did'] = $data['LpuSection_id'];
		$r = $this->qmodel->checkQueueTTSOnFree($data);
		if (false === $r) {
			$response['checkQueue'] = 'true';
		} else {
			$response['checkQueue'] = 'false';
		}

		if ( !isset($data['IsForDirection']) ) {
			$view = 'reg/timetablestac_footer';
		} else {
			$view = 'reg/timetablestac_footer_dir';
		}
		$this->load->view(
			$view, array(
				'data' => $response
			)
		);
	}

	/**
	 * Получение комментария на день для отделения
	 */
	function getTTSDayComment() {
		$data = $this->ProcessInputData('getTTSDayComment', true, true);
		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->getTTSDayComment($data);
		$this->ProcessModelList($response, true, true, $response)->ReturnData();
	}

	/**
	 * Сохранение комментария на день для врача
	 */
	function saveTTSDayComment() {
		$data = $this->ProcessInputData('saveTTSDayComment', true, true);
		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->saveTTSDayComment($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении примечания на день.')->ReturnData();
	}

	/**
	 * Создание расписания в стационаре
	 */
	function createTTSSchedule() {
		$data = $this->ProcessInputData('createTTSSchedule', true, true);
		if ( $data === false ) {
			return false;
		}

		If ( $data['ScheduleCreationType'] == 1 ) {
			$response = $this->dbmodel->createTTSSchedule($data);
		} else {
			$response = $this->dbmodel->copyTTSSchedule($data);
		}
		$this->ProcessModelSave($response, true, $response)->ReturnData();
	}

	/**
	 * Освобождение дня в стационаре
	 */
	function ClearDay() {
		$data = $this->ProcessInputData('ClearDay', true, true);
		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->ClearDayTTS($data);
		$this->ProcessModelSave($response, true, $response)->ReturnData();
	}

	/**
	 * Запись человека на бирку
	 */
	function Apply() {

		$this->load->model('EvnDirection_model', 'EvnDirection');
		$this->inputRules['Apply'] = array_merge($this->inputRules['Apply'], $this->EvnDirection->getSaveRules());
		$data = $this->ProcessInputData('Apply',true);

		if ($data === false) {
			return false;
		}
		$data['Day'] = TimeToDay(time()) ;

		$data['object'] = 'TimetableStac';
		$data['TimetableObject_id'] = 2;

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
					'TimetableStac_id' => $data['TimetableStac_id'],
					'EvnDirection_id' => $data['EvnDirection_id'],
					'success' => true,
				);

				$val['EvnDirection_id'] = $response['EvnDirection_id'];
				if (!empty($response['EvnDirection_TalonCode'])) {
					$val['EvnDirection_TalonCode'] = $response['EvnDirection_TalonCode'];
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

	/**
	 * Получение расписания для записи на один день
	 * $ForPrint - вариант для печати?
	 */
	function getTimetableStacOneDay( $ForPrint = false ) {

		/**
		 * Дополнительная обработка данных
		 * Обрезаем лишнюю часть адреса
		 */
		function ProcessData( $row, $ctrl ) {
			if ( isset($row['Person_Address']) ) {
				$row['Person_Address'] = str_replace('РОССИЯ, ПЕРМСКИЙ КРАЙ, ', '', $row['Person_Address']);
			}

			return $row;
		}

		$data = $this->ProcessInputData('getTimetableStac', true, true);
		if ( $data === false ) {
			return false;
		}

		//Очистка заблокированных бирок
		$this->dbmodel->unlockByUser($data);

		$response = $this->dbmodel->getTimetableStacOneDay($data);
		$response['PanelID'] = $data['PanelID'];

		if ( isset($response['success']) && !$response['success'] ) {
			$this->load->view(
				'reg/timetable_general_error', array(
					'Error_Msg' => $response['Error_Msg']
				)
			);
			return;
		}

		$this->load->model("LpuStructure_model", "LpuStructure");
		$lsresponse = $this->LpuStructure->getLpuSectionInfoForReg($data);
		$lsresponse['PanelID'] = $data['PanelID'];

		$this->load->view(
			'reg/lpusectionstac_comment', array(
				'data' => $lsresponse
			)
		);

		$response['lsData'] = $lsresponse;
		$response['pmUserData'] = $data;
		$response['PanelID'] = $data['PanelID'];
		$response['readOnly'] = $data['readOnly'];

		foreach ( $response['data'] as &$row ) {
			$row = ProcessData($row, $this);
		}
		$this->load->library("TTimetableStac");

		if ( $ForPrint ) {
			$view = 'reg/timetablestaconeday_print';
		} else {
			$view = 'reg/timetablestaconeday';
		}

		$this->load->view(
			$view, array(
				'data' => $response
			)
		);
	}

	/**
	 * Печать расписания для записи на один день
	 */
	function printTimetableStacOneDay() {
		$this->load->view(
			'reg/timetable_general_css'
		);

		$this->getTimetableStacOneDay(true);
		$this->load->view(
			'reg/timetablegrafoneday_print_footer'
		);
	}

	/**
	 * Редактирование переданных бирок
	 */
	function editTTS() {
		$data = $this->ProcessInputData('editTTS', true, true);
		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->editTTSSet($data);
		$this->ProcessModelSave($response, true, 'Ошибка при редактировании бирок')->ReturnData();
	}

	/**
	 * Печать расписания в виде таблицы для редактирования
	 */
	function printTimetableStacForEdit() {
		$data = $this->ProcessInputData('getTimetableStacForEdit', true, true);
		//var_dump($data);
		if ( $data ) {
			$response = $this->dbmodel->getTimetableStacForEdit($data);

			if ( isset($response['success']) && !$response['success'] ) {
				$this->load->view(
					'reg/timetable_general_error', array(
						'Error_Msg' => $response['Error_Msg']
					)
				);
				return;
			}

			$this->load->model("LpuStructure_model", "mpmodel");
			$lsresponse = $this->mpmodel->getLpuSectionInfoForReg($data);
			$lsresponse['PanelID'] = $data['PanelID'];

			$this->load->view(
				'reg/timetable_general_css'
			);

			$this->load->view(
				'reg/lpusectionstac_comment', array(
					'data' => $lsresponse
				)
			);

			$this->load->view(
				'reg/timetablestac_general_header',
				array(
					'data' => $response
				)
			);

			$response['lsData'] = $lsresponse;
			$response['pmUserData'] = $data;
			$response['PanelID'] = $data['PanelID'];

			$this->load->library("TTimetableStac");
			$this->load->view(
				'reg/timetablestac_edit_data', array(
					'data' => $response
				)
			);
		} else {
			return false;
		}
	}

	/**
	 * Печать суммарного расписания в виде таблицы для редактирования
	 */
	function printTimetableStacSummary() {
		$data = $this->ProcessInputData('getTimetableStacSummary', true, true);
		//var_dump($data);
		if ( $data ) {
			$response = $this->dbmodel->getTimetableStacSummary($data);

			if ( isset($response['success']) && !$response['success'] ) {
				$this->load->view(
					'reg/timetable_general_error', array(
						'Error_Msg' => $response['Error_Msg']
					)
				);
				return;
			}

			$this->load->view(
				'reg/timetablestac_general_header',
				array(
					'data' => $response
				)
			);

			$response['pmUserData'] = $data;
			$response['PanelID'] = $data['PanelID'];

			$this->load->library("TTimetableStac");
			$this->load->view(
				'reg/timetablestac_summary_data', array(
					'data' => $response
				)
			);
		} else {
			return false;
		}
	}

	/**
	 * Освобождение бирки
	 */
	function Clear() {
		$data = $this->ProcessInputData('Clear', true);
		if ( $data === false ) {
			return false;
		}

		$data['object'] = 'TimetableStac';

		$response = $this->dbmodel->Clear($data);

		if ( $response['success'] ) {
			// Пересчет кэша коек по дням теперь прямо в хранимке
			$val['success'] = true;
		} else {
			$val['success'] = false;
			$val['Error_Msg'] = toUTF($response['Error_Msg']);
		}
		$this->ReturnData($val);
	}
}
?>
<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * TimetableMedService - работа с расписанием службы, услуг службы
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
 * @property TimetableMedService_model dbmodel
 * @property MedService_model msmodel
 */
class TimetableMedService extends Timetable
{
	
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	
		$this->inputRules += array(
			'getTimetableMedServiceForEdit' => array(
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
					'field' => 'EvnPrescr_id',
					'label' => 'Идентификатор назначения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PanelID',
					'label' => 'Идентификатор панели на клиенте',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'isExt6',
					'label' => 'Загрузка расписания для формы ExtJS 6',
					'rules' => '',
					'type' => 'int'
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
			'getTimetableUslugaComplexForEdit' => array(
				array(
					'field' => 'StartDay',
					'label' => 'Дата начала расписания',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'UslugaComplexMedService_id',
					'label' => 'Идентификатор услуги',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'MedService_id',
					'label' => 'Идентификатор службы',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPrescr_id',
					'label' => 'Идентификатор назначения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PanelID',
					'label' => 'Идентификатор панели на клиенте',
					'rules' => '',
					'type' => 'string',
					'default' => 'TTMSSchedulePanel'
				),
				array(
					'field' => 'isExt6',
					'label' => 'Загрузка расписания для формы ExtJS 6',
					'rules' => '',
					'type' => 'int'
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
			'createTTMSSchedule' => array(
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
					'field' => 'UslugaComplexMedService_id',
					'label' => 'Идентификатор услуги',
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
					'field' => 'CopyTTMSComments',
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
				),
				array(
					'field' => 'UslugaComplexMedService_id',
					'label' => 'Идентификатор услуги',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getTTMSHistory' => array(
				array(
					'field' => 'TimetableMedService_id',
					'label' => 'Идентификатор бирки',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'ShowFullHistory',
					'label' => 'Показывать всю историю изменений на время',
					'rules' => '',
					'type' => 'id'
				),
			),
			'checkBeforeLock' => array(
				array(
					'field' => 'TimetableMedService_id',
					'label' => 'Идентификатор бирки',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => 'required',
					'type' => 'id'
				),
			),
			'setTTMSType' => array(
				array(
					'field' => 'TimetableMedService_id',
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
					'field' => 'TimetableMedServiceGroup',
					'label' => 'Набор идентификаторов бирок служб',
					'rules' => '',
					'type' => 'string'
				)
			),
			'Delete' => array(
				array(
					'field' => 'TimetableMedService_id',
					'label' => 'Идентификатор бирки',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'TimetableMedServiceGroup',
					'label' => 'Набор идентификаторов бирок служб',
					'rules' => '',
					'type' => 'string'
				)
			),
			'addTTMSDop' => array(
				array(
					'field' => 'Day',
					'label' => 'Идентификатор дня',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedService_id',
					'label' => 'Идентификатор службы',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaComplexMedService_id',
					'label' => 'Идентификатор услуги',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'StartTime',
					'label' => 'Начало приёма',
					'rules' => '',
					'type' => 'time'
				),
				array(
					'field' => 'TimetableExtend_Descr',
					'label' => 'Примечание на бирку',
					'rules' => '',
					'type' => 'string'
				),
			),
			'getTTMSDayComment' => array(
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
				),
				array(
					'field' => 'UslugaComplexMedService_id',
					'label' => 'Идентификатор услуги службы',
					'rules' => '',
					'type' => 'id'
				),
			),
			'saveTTMSDayComment' => array(
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
				),
				array(
					'field' => 'UslugaComplexMedService_id',
					'label' => 'Идентификатор услуги службы',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedServiceDay_Descr',
					'label' => 'Примечание на день к службе',
					'rules' => '',
					'type' => 'string'
				)
			),
			// todo: Бешеной собаке семь верст не крюк, а многократное описание одних и тех же рулесов в разных моделях - не проблема
			'saveEvnUslugaComplexOrder' => array( // в чистом виде взято из контроллера EvnUsluga.php
				array(
					'field' => 'object',
					'label' => 'Объект',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'EvnUsluga_id',
					'label' => 'Идентификатор комплексной услуги',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUsluga_pid',
					'label' => 'Идентификатор родителя',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'ЛПУ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PrehospDirect_id',
					'label' => 'Идентификатор типа направления',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_did',
					'label' => 'Идентификатор ЛПУ заказавшего услугу',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_did',
					'label' => 'Идентификатор отделения ЛПУ заказавшего услугу',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_did',
					'label' => 'Идентификатор врача заказавшего услугу',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Org_did',
					'label' => 'Идентификатор организации заказавшей услугу',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaComplex_setDate',
					'label' => 'Дата оказания комплексной услуги',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'EvnUslugaComplex_setTime',
					'label' => 'Время оказания комплексной услуги',
					'rules' => '',
					'type' => 'time'
				),
				array(
					'field' => 'Lpu_uid',
					'label' => 'ЛПУ, которому назначается оказание услуги',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Org_uid',
					'label' => 'Другая организация',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_uid',
					'label' => 'Отделение, которому назначается оказание услуги',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Врач',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'Usluga_isCito',
					'label' => 'Cito',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'time_table',
					'label' => 'Тип расписания (параклиника, поликлиника, стационар)',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'TimetablePar_id',
					'label' => 'Идентификатор записи расписания параклиники',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'TimetableMedService_id',
					'label' => 'Идентификатор записи расписания службы/услуги',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'PersonEvn_id',
					'label' => 'Идентификатор состояния человека',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Server_id',
					'label' => 'Идентификатор сервера',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Услуга',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'checked',
					'label' => 'пометки',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'EvnDirection_id',
					'label' => 'Направление',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPrescr_id',
					'label' => 'Идентификатор',
					'rules' => '',
					'type' => 'id'
				)
			),
			'Apply' => array(
				array(
					'field' => 'TimetableMedService_id',
					'label' => 'Идентификатор бирки',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Evn_id',
					'label' => 'Идентификатор связанного события',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'AnswerQueue',
					'label' => 'Ответ об отмене записи в очередь',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'IgnoreCheckAlreadyHasRecordOnThisTime',
					'label' => 'Игнорировать проверку',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'ARMType_id',
					'label' => 'Идентификатор типа АРМа',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Идентификатор места работы',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'HIVContingentTypeFRMIS_id',
					'label' => 'Код контингента ВИЧ',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'CovidContingentType_id',
					'label' => 'Код контингента COVID',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'HormonalPhaseType_id',
					'label' => 'Фаза цикла',
					'rules' => '',
					'type' => 'int'
				),
				[
					'field' => 'ignoreCanRecord',
					'label' => '', 'rules',
					'type' => 'string'
				],
				[
					'field' => 'IncludeInDirection',
					'label' => 'В какое направелние включить текущее',
					'rules' => '',
					'type' => 'string'
				],
				[
					'field' => 'order',
					'label' => 'Детали заявки',
					'rules' => '',
					'type' => 'string'
				],

			),
			'Clear' => array(
				array(
					'field' => 'cancelType',
					'label' => 'Тип отмены направления',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'TimetableMedService_id',
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
			'editTTMS' => array(
				array(
					'field' => 'selectedTTMS',
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
					'field' => 'ChangeTTMSType',
					'label' => 'Изменить тип бирки',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'ChangeTTMSDescr',
					'label' => 'Изменить примечание',
					'rules' => '',
					'type' => 'checkbox'
				),
			),
			'acceptWithoutRecord' => array(
				array(
					'field' => 'EvnDirection_id',
					'label' => 'Направление',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadAllUslugaTTList' => array(
				//array('field' => 'Evn_id', 'label' => 'Идентификатор случая', 'rules' => 'required', 'type' => 'int'),
				array('field' => 'StartDay', 'label' => 'Дата начала расписания', 'rules' => '', 'type' => 'date'),
				array('field' => 'arrRes', 'label' => 'Ресурсы', 'rules' => '', 'type' => 'string'),
				array('field' => 'arrMS', 'label' => 'Службы', 'rules' => '', 'type' => 'string'),
				array('field' => 'arrUsl', 'label' => 'Службы', 'rules' => '', 'type' => 'string')
			),
			'loadTTListByDay' => array(
				array('field' => 'StartDay', 'label' => 'Дата начала расписания', 'rules' => 'required', 'type' => 'date'),
				array('field' => 'UslugaComplexMedService_id', 'label' => 'Услуга', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedService_id', 'label' => 'Служба', 'rules' => '', 'type' => 'id'),
				array('field' => 'pzm_MedService_id', 'label' => 'Пункт забора', 'rules' => '', 'type' => 'id'),
				array('field' => 'Resource_id', 'label' => 'Ресурс', 'rules' => '', 'type' => 'id'),
				array('field' => 'pzm_UslugaComplexMedService_id', 'label' => 'Идентификатор услуги ПЗ', 'rules' => '', 'type' => 'id')
			),
			'loadAnnotateByDay' => array(
				array('field' => 'StartDay', 'label' => 'Дата начала расписания', 'rules' => 'required', 'type' => 'date'),
				array('field' => 'MedService_id', 'label' => 'Служба', 'rules' => '', 'type' => 'id'),
				array('field' => 'Resource_id', 'label' => 'Ресурс', 'rules' => '', 'type' => 'id'),
				array('field' => 'UslugaComplexMedService_id', 'label' => 'Услуга', 'rules' => '', 'type' => 'id'),
				array('field' => 'Lpu_id', 'label' => 'МО', 'rules' => '', 'type' => 'id')
			)
		);
		
		// В конструкторе контроллера сразу открываем хелпер Reg
		$this->load->helper('Reg');
		
		$this->load->database();
		$this->load->model("TimetableMedService_model", "dbmodel");
    }
	
	/**
	 * Приём без записи
	 */
	function acceptWithoutRecord() {
		$data = $this->ProcessInputData('acceptWithoutRecord', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->acceptWithoutRecord($data);
		$this->ProcessModelSave($response, true, 'Ошибка при создании бирки')->ReturnData();
		
		return true;
	}
	
	/**
	 * Получение расписания службы для редактирования в виде чистого HTML
	 */
	function getTimetableMedServiceForEdit()
	{
		
		$data = $this->ProcessInputData('getTimetableMedServiceForEdit', true, true);
		if ($data) {
			//Не учитывать максимальную дату записи, указанную в системных настройках, на форме редактирвоания расписания
			$data['dntUseFilterMaxDayRecord'] = true;

			$response = $this->dbmodel->getTimetableMedServiceForEdit($data);
			$response['PanelID'] = $data['PanelID'];
			$response['EvnPrescr_id'] = $data['EvnPrescr_id'];
			$response['isExt6'] = $data['isExt6'];
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
				'reg/timetablemedservice_general_header',
				array(
					'data' => $response
				)
			);
			
			$this->load->model("MedService_model", "msmodel");
			$response['msData'] =  $this->msmodel->getMedServiceInfoForReg($data);
			$response['pmUserData'] = $data;
			$response['PanelID'] = $data['PanelID'];
			$response['EvnPrescr_id'] = $data['EvnPrescr_id'];
			$response['isExt6'] = $data['isExt6'];
			$response['readOnly'] = $data['readOnly'];
			
			$this->load->library("TTimetableMedService");
			$this->load->view(
				'reg/timetablemedservice_edit_data',
				array(
					'data' => $response
				)
			);
			
			$this->load->view(
				'reg/timetablemedservice_edit_footer',
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
	 * Получение расписания услуги для редактирования в виде чистого HTML
	 */
	function getTimetableUslugaComplexForEdit()
	{
		
		$data = $this->ProcessInputData('getTimetableUslugaComplexForEdit', true, true);
		if ($data) {
			//Не учитывать максимальную дату записи, указанную в системных настройках, на форме редактирвоания расписания
			$data['dntUseFilterMaxDayRecord'] = true;

			$response = $this->dbmodel->getTimetableUslugaComplexForEdit($data);
			$response['PanelID'] = $data['PanelID'];
			$response['EvnPrescr_id'] = $data['EvnPrescr_id'];
			$response['isExt6'] = $data['isExt6'];
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
				'reg/timetableuslugacomplex_general_header',
				array(
					'data' => $response
				)
			);
			
			$this->load->model("MedService_model", "msmodel");
			$response['msData'] =  $this->msmodel->getUslugaComplexInfoForReg($data);
			$response['pmUserData'] = $data;
			$response['PanelID'] = $data['PanelID'];
			$response['EvnPrescr_id'] = $data['EvnPrescr_id'];
			$response['isExt6'] = $data['isExt6'];
			$response['readOnly'] = $data['readOnly'];
			
			$this->load->library("TTimetableMedService");
			$this->load->view(
				'reg/timetableuslugacomplex_edit_data',
				array(
					'data' => $response
				)
			);
			
			$this->load->view(
				'reg/timetableuslugacomplex_edit_footer',
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
	 * Печать расписания на ресурсы
	 */
	function printTimetableResourceForEdit()
	{

		$data = $this->ProcessInputData('getTimetableResourceForEdit', true, true);
		if ($data) {
			$response = $this->dbmodel->getTimetableResourceForEdit($data);
			$response['PanelID'] = $data['PanelID'];
			$response['EvnPrescr_id'] = $data['EvnPrescr_id'];
			$response['isExt6'] = $data['isExt6'];
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

			$this->load->view(
				'reg/timetableresource_general_header',
				array(
					'data' => $response
				)
			);

			$this->load->model("MedService_model", "msmodel");
			$response['msData'] =  $this->msmodel->getResourceInfoForReg($data);
			$response['pmUserData'] = $data;
			$response['PanelID'] = $data['PanelID'];
			$response['EvnPrescr_id'] = $data['EvnPrescr_id'];
			$response['isExt6'] = $data['isExt6'];
			$response['readOnly'] = $data['readOnly'];

			$this->load->library("TTimetableResource");
			$this->load->view(
				'reg/timetableresource_edit_data',
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
	function printTimetableMedServiceForEdit()
	{
		
		//Dirty trick, перекидываем параметры из GET в POST
		$_POST = array_merge($_POST, $_GET);
		$data = $this->ProcessInputData('getTimetableMedServiceForEdit', true, true);
		if ($data) {
			
			if ( isset($data['MedService_id']) ) {
				$response = $this->dbmodel->getTimetableMedServiceForEdit($data);
				$response['PanelID'] = $data['PanelID'];
				$response['EvnPrescr_id'] = $data['EvnPrescr_id'];
				$response['isExt6'] = $data['isExt6'];
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
					'reg/timetablemedservice_general_header',
					array(
						'data' => $response
					)
				);
				
				
				$this->load->library("TTimetableMedService");
				$this->load->view(
					'reg/timetablemedservice_edit_data',
					array(
						'data' => $response
					)
				);
			} else {
				$response = $this->dbmodel->getTimetableUslugaComplexForEdit($data);
				$response['PanelID'] = $data['PanelID'];
				$response['EvnPrescr_id'] = $data['EvnPrescr_id'];
				$response['isExt6'] = $data['isExt6'];
				
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
					'reg/timetablemedservice_general_header',
					array(
						'data' => $response
					)
				);
				
				$this->load->model("MedService_model", "msmodel");
				$response['msData'] =  $this->msmodel->getUslugaComplexInfoForReg($data);
				$response['pmUserData'] = $data;
				$response['PanelID'] = $data['PanelID'];
				$response['EvnPrescr_id'] = $data['EvnPrescr_id'];
				$response['isExt6'] = $data['isExt6'];
				
				$this->load->library("TTimetableMedService");
				$this->load->view(
					'reg/timetablemedservice_edit_data',
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
	 * Печать расписания в виде таблицы для редактирования
	 */
	function printTimetableMedServiceForEditUslugaComplex()
	{
		
		//Dirty trick, перекидываем параметры из GET в POST
		$_POST = array_merge($_POST, $_GET);
		$data = $this->ProcessInputData('getTimetableUslugaComplexForEdit', true, true);
		if ($data) {
			
			$response = $this->dbmodel->getTimetableUslugaComplexForEdit($data);
			$response['PanelID'] = $data['PanelID'];
			$response['EvnPrescr_id'] = $data['EvnPrescr_id'];
			$response['isExt6'] = $data['isExt6'];
			
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
				'reg/timetableuslugacomplex_general_header',
				array(
					'data' => $response
				)
			);
			
			$this->load->model("MedService_model", "msmodel");
			$response['msData'] =  $this->msmodel->getUslugaComplexInfoForReg($data);
			$response['pmUserData'] = $data;
			$response['PanelID'] = $data['PanelID'];
			$response['EvnPrescr_id'] = $data['EvnPrescr_id'];
			$response['isExt6'] = $data['isExt6'];
			
			$this->load->library("TTimetableMedService");
			$this->load->view(
				'reg/timetableuslugacomplex_edit_data',
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
	 * Создание расписания для службы
	 */
	function createTTMSSchedule() {
		
		$data = $this->ProcessInputData('createTTMSSchedule', true, true);
		if ($data) {
			if ( isset($data['MedService_id']) ) {
				If ($data['ScheduleCreationType'] == 1) {
					$response = $this->dbmodel->createTTMSSchedule($data);
				} else {
					$response = $this->dbmodel->copyTTMSSchedule($data);
				}
			} else {
				If ($data['ScheduleCreationType'] == 1) {
					$days = ceil(abs(strtotime($data['CreateDateRange'][1]) - strtotime($data['CreateDateRange'][0])) / 86400);
					if ( $days > 30 ) {
						$this->ReturnError('За один раз можно создавать расписание не более чем на месяц.');
						return;
					} else {
						$response = $this->dbmodel->createTTMSScheduleUslugaComplex($data);
					}
				} else {
					$response = $this->dbmodel->copyTTMSScheduleUslugaComplex($data);
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
			if ( isset($data['MedService_id']) ) {
				$response = $this->dbmodel->ClearDay($data);
			} else {
				$response = $this->dbmodel->ClearDayUslugaComplex($data);
			}
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	
	/**
	 * Получение истории изменения бирки службы
	 */
	function getTTMSHistory() {
		
		$data = $this->ProcessInputData('getTTMSHistory', true, true);
		if ($data) {
			$response = $this->dbmodel->getTTMSHistory($data);
			$this->ProcessModelList($response, true, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Смена типа бирки для службы
	 */
	function setTTMSType() {
		
		$data = $this->ProcessInputData('setTTMSType', true, true);
		if ($data) {
			$response = $this->dbmodel->setTTMSType($data);
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
	 * Добавление дополнительной бирки для службы
	 */
	function addTTMSDop() {
		
		$data = $this->ProcessInputData('addTTMSDop', true, true);
		if ($data) {
			if ( isset($data['MedService_id']) ) {
				$response = $this->dbmodel->addTTMSDop($data);
			} else {
				$response = $this->dbmodel->addTTMSDopUslugaComplex($data);
			}
			
			
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	
	/**
	 * Получение комментария на день для службы
	 */
	function getTTMSDayComment() {
		
		$data = $this->ProcessInputData('getTTMSDayComment', true, true);
		if ($data) {
			$response = $this->dbmodel->getTTMSDayComment($data);
			$this->ProcessModelList($response, true, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	
	/**
	 * Сохранение комментария на день для службы
	 */
	function saveTTMSDayComment() {
		
		$data = $this->ProcessInputData('saveTTMSDayComment', true, true);
		if ($data) {
			$response = $this->dbmodel->saveTTMSDayComment($data);
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении примечания на день.')->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	
	/**
	 * Получение расписания для записи в виде чистого HTML
	 */
	function getTimetableMedService() {
		
		$data = $this->ProcessInputData('getTimetableMedServiceForEdit', true, true);
		if ($data) {
			
			//Очистка заблокированных бирок
			$this->dbmodel->unlockByUser($data);
			
			$response = $this->dbmodel->getTimetableMedServiceForEdit($data);
			$response['PanelID'] = $data['PanelID'];
			$response['EvnPrescr_id'] = $data['EvnPrescr_id'];
			$response['isExt6'] = $data['isExt6'];
			
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
				'reg/timetablemedservice_general_header',
				array(
					'data' => $response
				)
			);
			
			$this->load->model("MedService_model", "msmodel");
			$response['msData'] =  $this->msmodel->getMedServiceInfoForReg($data);
			$response['pmUserData'] = $data;
			$response['PanelID'] = $data['PanelID'];
			$response['EvnPrescr_id'] = $data['EvnPrescr_id'];
			$response['isExt6'] = $data['isExt6'];

			$this->load->library("TTimetableMedService");
			if ( !isset($data['IsForDirection']) ) {
				$view = 'reg/timetablemedservice_data';
			} else {
				$view = 'reg/timetablemedservice_data_dir';
			}
			$this->load->view(
				$view,
				array(
					'data' => $response
				)
			);

			$view = 'reg/timetablemedservice_footer';

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
	function getTimetableMedServiceOneDay($ForPrint = false)
	{
		/**
		 * Дополнительная обработка данных
		 * Обрезаем лишнюю часть адреса
		 */
		function ProcessData($row, $ctrl) {
			if (isset($row['Person_Address'])) {
				$row['Person_Address'] = str_replace('РОССИЯ, ПЕРМСКИЙ КРАЙ, ', '', $row['Person_Address']);
			}
			
			return $row;
		}
		
		$data = $this->ProcessInputData('getTimetableMedServiceForEdit', true, true);
		if ($data === false) { return false; }
		
		//Очистка заблокированных бирок
		$this->dbmodel->unlockByUser($data);
		
		$response = $this->dbmodel->getTimetableMedServiceOneDay($data);
		$response['PanelID'] = $data['PanelID'];
		$response['EvnPrescr_id'] = $data['EvnPrescr_id'];
		$response['isExt6'] = $data['isExt6'];
		
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
		$response['EvnPrescr_id'] = $data['EvnPrescr_id'];
		$response['isExt6'] = $data['isExt6'];
		$response['readOnly'] = $data['readOnly'];

		foreach($response['data'] as &$row) {
			$row = ProcessData($row, $this);
		}
		$this->load->library("TTimetableMedService");
		
		if ($ForPrint) {
			$view = 'reg/timetablemedserviceoneday_print';
		} else {
			$view = 'reg/timetablemedserviceoneday';
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
	 * Получение расписания для записи в виде чистого HTML
	 */
	function getTimetableUslugaComplex() {
		
		$data = $this->ProcessInputData('getTimetableUslugaComplexForEdit', true, true);
		if ($data) {
			
			//Очистка заблокированных бирок
			$this->dbmodel->unlockByUser($data);

			$response = $this->dbmodel->getTimetableUslugaComplexForEdit($data);
			$response['PanelID'] = $data['PanelID'];
			$response['EvnPrescr_id'] = $data['EvnPrescr_id'];
			$response['isExt6'] = $data['isExt6'];
			
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
				'reg/timetablemedservice_general_header',
				array(
					'data' => $response
				)
			);
			
			$this->load->model("MedService_model", "msmodel");
			$response['msData'] =  $this->msmodel->getUslugaComplexInfoForReg($data);
			$response['pmUserData'] = $data;
			$response['PanelID'] = $data['PanelID'];
			$response['EvnPrescr_id'] = $data['EvnPrescr_id'];
			$response['isExt6'] = $data['isExt6'];
			
			$this->load->library("TTimetableMedService");
			$this->load->view(
				'reg/timetablemedservice_data',
				array(
					'data' => $response
				)
			);
			
			$this->load->view(
				'reg/timetablemedservice_footer',
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
	 * Получение расписания для записи на один день на услугу
	 * $ForPrint - вариант для печати?
	 */
	function getTimetableUslugaComplexOneDay($ForPrint = false) {
		/**
		 * Дополнительная обработка данных
		 * Обрезаем лишнюю часть адреса
		 */
		function ProcessData($row, $ctrl) {
			if (isset($row['Person_Address'])) {
				$row['Person_Address'] = str_replace('РОССИЯ, ПЕРМСКИЙ КРАЙ, ', '', $row['Person_Address']);
			}
			
			return $row;
		}
		
		$data = $this->ProcessInputData('getTimetableUslugaComplexForEdit', true, true);
		if ($data === false) { return false; }
		
		//Очистка заблокированных бирок
		$this->dbmodel->unlockByUser($data);
		
		$response = $this->dbmodel->getTimetableUslugaComplexOneDay($data);
		$response['PanelID'] = $data['PanelID'];
		$response['EvnPrescr_id'] = $data['EvnPrescr_id'];
		$response['isExt6'] = $data['isExt6'];
		
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
		$response['msData'] =  $this->msmodel->getUslugaComplexInfoForReg($data);
		$response['pmUserData'] = $data;
		$response['PanelID'] = $data['PanelID'];
		$response['EvnPrescr_id'] = $data['EvnPrescr_id'];
		$response['isExt6'] = $data['isExt6'];
		
		foreach($response['data'] as &$row) {
			$row = ProcessData($row, $this);
		}
		$this->load->library("TTimetableMedService");
		
		if ($ForPrint) {
			$view = 'reg/timetablemedserviceoneday_print';
		} else {
			$view = 'reg/timetablemedserviceoneday';
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

		$this->load->model('EvnDirection_model', 'EvnDirection');
		$this->inputRules['Apply'] = array_merge($this->inputRules['Apply'], $this->EvnDirection->getSaveRules(array(
			'lpuSectionProfileNotRequired' => true
		)));
		$data = $this->ProcessInputData('Apply',true);

		if ($data === false) {
			return false; 
		}

		if (empty($data['LpuSectionProfile_id']) && !in_array($data['DirType_id'], [6,9]) && getRegionNick() != 'ekb') { // для ВК профиль не обязателен (refs #83337)
			$this->ReturnError('Поле "Профиль" обязательно для заполнения');
			return false;
		}

		$data['Day'] = TimeToDay(time()) ;

		$data['object'] = 'TimetableMedService';
		$data['TimetableObject_id'] = 3;

		if(!empty($data['LpuSection_id']) && empty($data['MedPersonal_zid'])){
			$data['MedPersonal_zid'] = $this->dbmodel->getMedPesonalZid($data);
		}
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

			if ( isset($response['success']) && $response['success'] ) {
				$data['EvnDirection_id'] = $response['EvnDirection_id'];
				// Сколько можно переприсваивать массив ответа %)
				$val = array(
					'success' => true,
					'object' => $response['object'],
					'id' => $response['id']
				);
				if(!empty($response['addingMsg'])) $val['addingMsg'] = $response['addingMsg'];
				if(!empty($response['EvnDirectionInfo'])) $val['EvnDirectionInfo'] = $response['EvnDirectionInfo'];
				if(!empty($response['UslugaList'])) $val['UslugaList'] = $response['UslugaList'];
				$val['EvnDirection_id'] = $response['EvnDirection_id'];
				if (!empty($response['EvnDirection_TalonCode'])) {
					$val['EvnDirection_TalonCode'] = $response['EvnDirection_TalonCode'];
				}

				if (getRegionNick() == 'ufa') {
					$this->load->model('PersonDetailEvnDirection_model', 'edpdmodel');
					if ( !empty($data['HIVContingentTypeFRMIS_id']) || !empty($data['CovidContingentType_id']) || !empty($data['HormonalPhaseType_id'])) {
						$pdresponse = $this->edpdmodel->doSave($data);
					} else {
						$orderObj = json_decode($data['order'], true);
						if (is_array($orderObj) && (!empty($orderObj['HIVContingentTypeFRMIS_id']) || !empty($orderObj['CovidContingentType_id']) || !empty($orderObj['HormonalPhaseType_id']))) {
							$orderObj['EvnDirection_id'] = $response['EvnDirection_id'];
							$orderObj['session'] = $data['session'];
							$orderObj['pmUser_id'] = $data['pmUser_id'];
							$pdresponse = $this->edpdmodel->doSave($orderObj);
						}
					}
				}

				// сохраняем заказ, если есть необходимость
				//todo: зачем здесь заказ услуги, если он есть в Timetable_model->Apply???
				/*if (empty($data['redirectEvnDirection'])) {
					$this->load->model('EvnUsluga_model', 'eumodel');
					try {
						$this->eumodel->saveUslugaOrder($data);
					} catch (Exception $e) {
						$val['success'] = false;
						$val['Error_Msg'] = toUTF($e->getMessage());
						$this->ReturnData($val);
						return false;
					}
				}*/

				//$this->dbmodel->commitTransaction();
				
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
			} elseif ( isset($response['alreadyHasRecordOnThisTime']) ) {
				$val = array(
					'success' => false,
					'alreadyHasRecordOnThisTime' => toUTF($response['alreadyHasRecordOnThisTime'])
				);
				break;
			} else {
				$val['success'] = false;
				if (isset($response[0]) && isset($response[0]['Error_Msg'])) {
					$val['Error_Msg'] = $response[0]['Error_Msg'];
				} else {
					$val['Error_Msg'] = $response['Error_Msg'];
				}
				break;
			}
			
		} while (0);

		if ( !$val['success'] ) {
			// если что-то пошло не так, откатываем транзакцию
			$this->dbmodel->rollbackTransaction();
		} else {

			$this->load->model('Resource_model', 'resmodel');
			$this->resmodel->transferDirection($data, $val);
		}
		
		$this->ReturnData($val);
	}
	
	/**
	 * Освобождение бирки
	 */
	function Clear() {
		
		$data = $this->ProcessInputData('Clear', true, true);
		if ($data) {
			$data['object'] = 'TimetableMedService';
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
	function printTimetableMedServiceOneDay()
	{
		$this->load->view(
			'reg/timetable_general_css'
		);
		$this->getTimetableMedServiceOneDay(true);
		$this->load->view(
			'reg/timetablegrafoneday_print_footer'
		);
	}
	
		/**
	 * Печать расписания для записи на один день
	 */
	function printTimetableUslugaComplexOneDay()
	{
		$this->load->view(
			'reg/timetable_general_css'
		);
		$this->getTimetableUslugaComplexOneDay(true);
		$this->load->view(
			'reg/timetablegrafoneday_print_footer'
		);
	}
	
	/**
	 * Редактирование переданных бирок
	 */
	function editTTMS() {
		
		$data = $this->ProcessInputData('editTTMS', true, true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->editTTMSSet($data);
		$this->ProcessModelSave($response, true, 'Ошибка при редактировании бирок')->ReturnData();
		return true;
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
	 * Загрузка расписания на все записанные услуги
	 */
	function loadAllUslugaTTList() {
		$data = $this->ProcessInputData('loadAllUslugaTTList', true);
		if ($data) {
			$response = $this->dbmodel->loadAllUslugaTTList($data);
			//$this->ProcessModelList($response, true, true)->ReturnData();
			$this->ProcessModelSave($response)->ReturnData();
			return true;
		}
		else
			return false;

	}

	/**
	 * Загрузка расписания на все записанные услуги
	 */
	function loadTTListByDay() {
		$data = $this->ProcessInputData('loadTTListByDay', true);
		if(empty($data['Resource_id'])
			&& empty($data['MedService_id'])
			&& empty($data['pzm_MedService_id'])
			&& empty($data['UslugaComplexMedService_id'])
		    && empty($data['pzm_UslugaComplexMedService_id'])){
			$this->ReturnData(array('success'=>false,'Error_Msg'=> toUTF('Необходима служба, услуга или ресурс')));
			return false;
		}

		if ($data) {
			$response = array();

			if (!empty($data['MedService_id']) || !empty($data['pzm_MedService_id']))
				$response = $this->dbmodel->loadMedServiceTTListByDay($data);
			else
				$response = $this->dbmodel->loadResourceTTListByDay($data);

			$this->ProcessModelList($response, true, true)->ReturnData();
			//$this->ProcessModelSave($response)->ReturnData();
			return true;
		}
		else
			return false;

	}

	/**
	 * Загрузка расписания на все записанные услуги
	 */
	function loadAnnotateByDay() {
		$data = $this->ProcessInputData('loadAnnotateByDay', true);
		if(empty($data['Resource_id']) && empty($data['MedService_id']) && empty($data['UslugaComplexMedService_id'])){
			$this->ReturnData(array('success'=>false,'Error_Msg'=> toUTF('Необходима служба, ресурс или услуга')));
			return false;
		}
		if ($data) {
			if(!empty($data['Resource_id']))
				$response = $this->dbmodel->loadResourceAnnotateByDay($data);
			else
				$response = $this->dbmodel->loadMedServiceAnnotateByDay($data);
			$this->ProcessModelSave($response, true)->ReturnData();
		}
		else
			return false;

	}

}
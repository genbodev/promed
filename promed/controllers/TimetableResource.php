<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * TimetableResource - работа с расписанием ресурса
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
 * @property TimetableResource_model dbmodel
 * @property MedService_model msmodel
 */
class TimetableResource extends Timetable
{

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->inputRules += array(
			'getTimetableResourceForEdit' => array(
				array(
					'field' => 'StartDay',
					'label' => 'Дата начала расписания',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'Resource_id',
					'label' => 'Идентификатор ресурса',
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
			'createTTRSchedule' => array(
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
					'field' => 'Resource_id',
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
				// Примечания на бирках в новом формате
				array(
					'field' => 'AnnotationType_id',
					'label' => 'Тип примечания',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'AnnotationVison_id',
					'label' => 'Видимость примечания',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Annotation_Comment',
					'label' => 'Текст примечания',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Annotation_begTime',
					'label' => 'Время действия',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Annotation_endTime',
					'label' => 'Время действия',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'ignore_doubles',
					'label' => 'Игнорировать дубли',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'CreateAnnotation',
					'label' => 'Создать примечание',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'copyAnnotationGridData',
					'label' => 'Данные для копирования',
					'rules' => '',
					'type' => 'json_array'
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
					'field' => 'Resource_id',
					'label' => 'Идентификатор службы',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getTTRHistory' => array(
				array(
					'field' => 'TimetableResource_id',
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
					'field' => 'TimetableResource_id',
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
			'setTTRType' => array(
				array(
					'field' => 'TimetableResource_id',
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
					'field' => 'TimetableResourceGroup',
					'label' => 'Набор идентификаторов бирок служб',
					'rules' => '',
					'type' => 'string'
				)
			),
			'Delete' => array(
				array(
					'field' => 'TimetableResource_id',
					'label' => 'Идентификатор бирки',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'TimetableResourceGroup',
					'label' => 'Набор идентификаторов бирок служб',
					'rules' => '',
					'type' => 'string'
				)
			),
			'getTTRDayComment' => array(
				array(
					'field' => 'Day',
					'label' => 'Идентификатор дня',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Resource_id',
					'label' => 'Идентификатор ресурса',
					'rules' => '',
					'type' => 'id'
				),
			),
			'saveTTRDayComment' => array(
				array(
					'field' => 'Day',
					'label' => 'Идентификатор дня',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Resource_id',
					'label' => 'Идентификатор ресурса',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'ResourceDay_Descr',
					'label' => 'Примечание на день к ресурсу',
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
					'field' => 'TimetableResource_id',
					'label' => 'Идентификатор записи расписания ресурса',
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
					'field' => 'TimetableResource_id',
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
					'field' => 'ElectronicTalon_id',
					'label' => 'Идентификатор талона ЭО',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'ElectronicService_id',
					'label' => 'Идентификатор пункта обслуживания ЭО',
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
					// кто направил, для записи должности врача
					'field' => 'From_MedStaffFact_id',
					'label' => 'Рабочее место врача',
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
					'field' => 'TimetableResource_id',
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
			'editTTR' => array(
				array(
					'field' => 'selectedTTR',
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
					'field' => 'ChangeTTRType',
					'label' => 'Изменить тип бирки',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'ChangeTTRDescr',
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
			'addTTRDop' => array(
				array(
					'field' => 'Day',
					'label' => 'Идентификатор дня',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Resource_id',
					'label' => 'Идентификатор ресурса',
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
					'field' => 'StartTime',
					'label' => 'Начало приёма',
					'rules' => '',
					'type' => 'time'
				),
				// Примечания на бирках в новом формате
				array(
					'field' => 'AnnotationType_id',
					'label' => 'Тип примечания',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'AnnotationVison_id',
					'label' => 'Видимость примечания',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Annotation_Comment',
					'label' => 'Текст примечания',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'ignore_doubles',
					'label' => 'Игнорировать дубли',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'CreateAnnotation',
					'label' => 'Создать примечание',
					'rules' => '',
					'type' => 'id'
				),
			),
			'getTTRInfo' => array(
				array(
					'field' => 'TimetableResource_id',
					'label' => 'Идентификатор интересующей бирки',
					'rules' => 'required',
					'type' => 'id'
				),
			)
		);

		// В конструкторе контроллера сразу открываем хелпер Reg
		$this->load->helper('Reg');

		$this->load->database();
		$this->load->model("TimetableResource_model", "dbmodel");
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
	 * Получение расписания для записи в виде чистого HTML
	 */
	function getTimetableResource() {

		$data = $this->ProcessInputData('getTimetableResourceForEdit', true, true);
		if ($data) {

			//Очистка заблокированных бирок
			$this->dbmodel->unlockByUser($data);

			$response = $this->dbmodel->getTimetableResourceForEdit($data);
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
		
			$this->load->model("Annotation_model", "anmodel");
			$rannotation = $this->anmodel->getRegAnnotation($data);

			$this->load->view(
				'reg/timetable_general_css'
			);
			
			$this->load->view(
				'reg/resource_comment',
				array(
					'data' => $rannotation
				)
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

			$this->load->library("TTimetableResource");
			$this->load->view(
				'reg/timetableresource_data',
				array(
					'data' => $response
				)
			);

			$this->load->view(
				'reg/timetableresource_footer',
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
	 * Получение расписания ресурса для редактирования
	 */
	function getTimetableResourceForEdit()
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
		
			$this->load->model("Annotation_model", "anmodel");
			$rannotation = $this->anmodel->getRegAnnotation($data);

			$this->load->view(
				'reg/timetable_general_css'
			);
	
			$this->load->view(
				'reg/resource_comment',
				array(
					'data' => $rannotation
				)
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

			$this->load->view(
				'reg/timetableresource_edit_footer',
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
	 * Получение расписания для записи на один день на ресурс
	 * $ForPrint - вариант для печати?
	 */
	function getTimetableResourceOneDay($ForPrint = false)
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

		$data = $this->ProcessInputData('getTimetableResourceForEdit', true, true);
		if ($data === false) { return false; }

		//Очистка заблокированных бирок
		$this->dbmodel->unlockByUser($data);

		$response = $this->dbmodel->getTimetableResourceOneDay($data);
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
		$response['msData'] =  $this->msmodel->getResourceInfoForReg($data);
		$response['pmUserData'] = $data;
		$response['PanelID'] = $data['PanelID'];
		$response['EvnPrescr_id'] = $data['EvnPrescr_id'];
		$response['isExt6'] = $data['isExt6'];
		$response['readOnly'] = $data['readOnly'];

		foreach($response['data'] as &$row) {
			$row = ProcessData($row, $this);
		}
		$this->load->library("TTimetableResource");

		if ($ForPrint) {
			$view = 'reg/timetableresourceoneday_print';
		} else {
			$view = 'reg/timetableresourceoneday';
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
	 * Печать расписания для записи на один день
	 */
	function printTimetableResourceOneDay()
	{
		$this->load->view(
			'reg/timetable_general_css'
		);
		$this->getTimetableResourceOneDay(true);
		$this->load->view(
			'reg/timetableresourceoneday_print_footer'
		);
	}

	/**
	 * Печать расписания в виде таблицы для редактирования
	 */
	function printTimetableResourceForEdit()
	{

		//Dirty trick, перекидываем параметры из GET в POST
		$_POST = array_merge($_POST, $_GET);
		$data = $this->ProcessInputData('getTimetableResourceForEdit', true, true);
		if ($data) {

			$response = $this->dbmodel->getTimetableResourceForEdit($data);
			$response['PanelID'] = $data['PanelID'];
			$response['EvnPrescr_id'] = $data['EvnPrescr_id'];
			$response['isExt6'] = $data['isExt6'];
			$response['pmUserData'] = $data;

			$this->load->model("MedService_model", "msmodel");
			$response['msData'] =  $this->msmodel->getResourceInfoForReg($data);

			if ( isset($response['success']) && !$response['success']) {
				$this->load->view(
					'reg/timetable_general_error',
					array(
						'Error_Msg' => $response['Error_Msg']
					)
				);
				return true;
			}
		
			$this->load->model("Annotation_model", "anmodel");
			$rannotation = $this->anmodel->getRegAnnotation($data);

			$this->load->view(
				'reg/timetable_general_css'
			);
			
			$this->load->view(
				'reg/resource_comment',
				array(
					'data' => $rannotation
				)
			);

			$this->load->view(
				'reg/timetableresource_general_header',
				array(
					'data' => $response
				)
			);


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
	 * Создание расписания для службы
	 */
	function createTTRSchedule() {

		$data = $this->ProcessInputData('createTTRSchedule', true, true);
		if ($data) {
			If ($data['ScheduleCreationType'] == 1) {
				$response = $this->dbmodel->createTTRSchedule($data);
			} else {
				$response = $this->dbmodel->copyTTRSchedule($data);
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
	 * Получение истории изменения бирки службы
	 */
	function getTTRHistory() {

		$data = $this->ProcessInputData('getTTRHistory', true, true);
		if ($data) {
			$response = $this->dbmodel->getTTRHistory($data);
			$this->ProcessModelList($response, true, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Смена типа бирки для службы
	 */
	function setTTRType() {

		$data = $this->ProcessInputData('setTTRType', true, true);
		if ($data) {
			$response = $this->dbmodel->setTTRType($data);
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
	 * Получение комментария на день для службы
	 */
	function getTTRDayComment() {

		$data = $this->ProcessInputData('getTTRDayComment', true, true);
		if ($data) {
			$response = $this->dbmodel->getTTRDayComment($data);
			$this->ProcessModelList($response, true, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}


	/**
	 * Сохранение комментария на день для службы
	 */
	function saveTTRDayComment() {

		$data = $this->ProcessInputData('saveTTRDayComment', true, true);
		if ($data) {
			$response = $this->dbmodel->saveTTRDayComment($data);
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении примечания на день.')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Запись человека на бирку
	 */
	function Apply() {

		$this->load->model('EvnDirection_model', 'EvnDirection');
		$this->inputRules['Apply'] = array_merge($this->inputRules['Apply'], $this->EvnDirection->getSaveRules());
		$data = $this->ProcessInputData('Apply', true);

		if ($data === false) {
			return false;
		}
		$data['Day'] = TimeToDay(time()) ;

		$data['object'] = 'TimetableResource';
		$data['TimetableObject_id'] = 3;

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

				$val['EvnDirection_id'] = $response['EvnDirection_id'];
				if (!empty($response['EvnDirection_TalonCode'])) {
					$val['EvnDirection_TalonCode'] = $response['EvnDirection_TalonCode'];
				}

				// сохраняем заказ, если есть необходимость
				if (empty($data['redirectEvnDirection'])) {
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
				}

				// перенаправляем талон электронной очереди, если эта служба связана с ЭО
				if (!empty($data['ElectronicTalon_id'])) {

					$this->load->model('ElectronicTalon_model', 'eqmodel');
					$ElectronicService_id = $this->dbmodel->getFirstResultFromQuery("
						select top 1 mseq.ElectronicService_id
						from v_MedServiceElectronicQueue mseq (nolock)
						inner join v_ElectronicService es (nolock) on es.ElectronicService_id = mseq.ElectronicService_id
						inner join v_ElectronicQueueInfo eqi (nolock) on eqi.ElectronicQueueInfo_id = es.ElectronicQueueInfo_id
						where
						 	mseq.Resource_id = :Resource_id
						 	and eqi.ElectronicQueueInfo_IsOff != 2
						", array('Resource_id' => $data['Resource_id'])
					);

					if (!empty($ElectronicService_id)) {

						$data['ElectronicService_id'] = $ElectronicService_id;

						try {

							$redirectTalon = $this->eqmodel->redirectElectronicTalon($data);
							if (!empty($redirectTalon[0]['ElectronicTalonRedirect_id'])
								&& !empty($redirectTalon[0]['ElectronicService_Name'])
							) {
								$val['redirectedElectronicServiceName'] = $redirectTalon[0]['ElectronicService_Name'];
							}

						} catch (Exception $e) {
							$this->dbmodel->rollbackTransaction();
							$val['success'] = false;
							$val['Error_Msg'] = toUTF($e->getMessage());
							$this->ReturnData($val);
							return false;
						}
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
			} elseif ( isset($response['alreadyHasRecordOnThisTime']) ) {
				$val = array(
					'success' => false,
					'alreadyHasRecordOnThisTime' => toUTF($response['alreadyHasRecordOnThisTime'])
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
		} else {
			// Отправка данных направлений с типом функциональная диагностика в сторонние сервисы
			$this->load->model('Resource_model', 'resmodel');
			$this->resmodel->transferDirection($data, $val);
		}

		$this->ReturnData($val);
		return true;
	}

	/**
	 * Освобождение бирки
	 */
	function Clear() {

		$data = $this->ProcessInputData('Clear', true, true);
		if ($data) {
			$data['object'] = 'TimetableResource';
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
	function printTimetableUslugaComplexOneDay()
	{
		$this->load->view(
			'reg/timetable_general_css'
		);
		$this->getTimetableResourceOneDay(true);
		$this->load->view(
			'reg/timetablegrafoneday_print_footer'
		);
	}

	/**
	 * Редактирование переданных бирок
	 */
	function editTTR() {

		$data = $this->ProcessInputData('editTTR', true, true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->editTTRSet($data);
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
	 * Добавление дополнительной бирки для ресурса
	 */
	function addTTRDop() {

		$data = $this->ProcessInputData('addTTRDop', true, true);
		if ($data) {
			$response = $this->dbmodel->addTTRDop($data);

			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Получение информации по бирке
	 */
	function getTTRInfo() {
		$data = $this->ProcessInputData('getTTRInfo',true,true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->getTTRInfo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
}
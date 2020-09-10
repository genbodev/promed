<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * 
 * EvnFuncRequest - контроллер для работы с заявками на исследование
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Polka
 * @access			public
 * @copyright		Copyright (c) 2009 Swan Ltd.
 * @version			апрель.2012
 *
 * @property EvnFuncRequestProc_model $dbmodel
 */
class EvnFuncRequestProc extends swController
{
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		
		$this->load->database();
		$this->load->model('EvnFuncRequestProc_model', 'dbmodel');
		
		$this->inputRules = array(
			'checkUslugaComplexMedServiceTimeTable' => array(
				array(
					'field' => 'EvnDirection_id',
					'label' => 'Направление',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'MedService_id',
					'label' => 'Служба',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'begDate',
					'label' => 'Начало периода',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'endDate',
					'label' => 'Окончание периода',
					'rules' => 'required',
					'type' => 'date'
				)
			),
			'loadEvnFuncRequestViewList' => array(
				array(
					'field' => 'wnd_id',
					'label' => 'Идентификатор формы',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'Search_SurName',
					'label' => 'Фамилия пациента',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Search_FirName',
					'label' => 'Имя пациента',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Search_SecName',
					'label' => 'Отчество пациента',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Search_BirthDay',
					'label' => 'ДР пациента',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'EvnDirection_Num',
					'label' => 'Номер направления',
					'rules' => 'trim',
					'type' => 'string'
				),
                array(
                    'field' => 'Person_Phone',
                    'label' => 'Телефон',
                    'rules' => '',
                    'type' => 'string'
                ),
				array(
					'field' => 'Search_Usluga',
					'label' => 'Услуга',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnDirection_IsCito',
					'label' => 'Cito',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'begDate',
					'label' => 'Начало периода',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'endDate',
					'label' => 'Окончание периода',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'UslugaComplex_id', 
					'label' => 'Услуга', 
					'rules' => 'trim', 
					'type' => 'id'
				),
				array(
					'field' => 'MedService_id', 
					'label' => 'Служба', 
					'rules' => 'required', 
					'type' => 'id'
				)
			),
			'loadEvnFuncRequestList' => array(
				array(
					'field' => 'wnd_id',
					'label' => 'Идентификатор формы',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'Search_SurName',
					'label' => 'Фамилия пациента',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Search_FirName',
					'label' => 'Имя пациента',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Search_SecName',
					'label' => 'Отчество пациента',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Search_BirthDay',
					'label' => 'ДР пациента',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'EvnDirection_Num',
					'label' => 'Номер направления',
					'rules' => 'trim',
					'type' => 'string'
				),
                array(
                    'field' => 'Person_Phone',
                    'label' => 'Телефон',
                    'rules' => '',
                    'type' => 'string'
                ),
				array(
					'field' => 'Search_Usluga',
					'label' => 'Услуга',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnDirection_IsCito',
					'label' => 'Cito',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'begDate',
					'label' => 'Начало периода',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'endDate',
					'label' => 'Окончание периода',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'UslugaComplex_id', 
					'label' => 'Услуга', 
					'rules' => 'trim', 
					'type' => 'id'
				),
				array(
					'field' => 'MedService_id', 
					'label' => 'Служба', 
					'rules' => 'required', 
					'type' => 'id'
				)
			),
			'loadEvnFuncRequestListDoneStatus' => array(
				array(
					'field' => 'wnd_id',
					'label' => 'Идентификатор формы',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'Search_SurName',
					'label' => 'Фамилия пациента',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Search_FirName',
					'label' => 'Имя пациента',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Search_SecName',
					'label' => 'Отчество пациента',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Search_BirthDay',
					'label' => 'ДР пациента',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'EvnDirection_Num',
					'label' => 'Номер направления',
					'rules' => 'trim',
					'type' => 'string'
				),
                array(
                    'field' => 'Person_Phone',
                    'label' => 'Телефон',
                    'rules' => '',
                    'type' => 'string'
                ),
				array(
					'field' => 'Search_Usluga',
					'label' => 'Услуга',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnDirection_IsCito',
					'label' => 'Cito',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'begDate',
					'label' => 'Начало периода',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'endDate',
					'label' => 'Окончание периода',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Услуга',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'MedService_id',
					'label' => 'Служба',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getEvnFuncRequest' => array(
				array(
					'field' => 'EvnFuncRequest_id',
					'label' => 'ID заявки',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'EvnDirection_id',
					'label' => 'Направление',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'getEvnProcRequest' => array(
				array(
					'field' => 'EvnFuncRequest_id',
					'label' => 'ID заявки',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'EvnDirection_id',
					'label' => 'Направление',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'saveEvnFuncRequest' => array(
				array(
					'field' => 'EvnFuncRequest_id',
					'label' => 'ID заявки',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'EvnDirection_id',
					'label' => 'Направление',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedService_id',
					'label' => 'Служба',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'TimetableMedService_id',	
					'label' => 'ID бирки',	
					'rules' => 'trim',	
					'type' => 'id'
				),
				array(
					'field' => 'PersonEvn_id',
					'label' => 'Идентификатор состояния человека',
					'rules' => 'required','type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Server_id',
					'label' => 'Сервер',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'MedService_id',
					'label' => 'Служба',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnDirection_Num',
					'label' => 'Номер направления',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'EvnDirection_setDT',
					'label' => 'Дата направления',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'PrehospDirect_id',
					'label' => 'Кем направлен',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Org_sid',
					'label' => 'Направившая организация',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_sid',
					'label' => 'Направившее ЛПУ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Направившее отделение ЛПУ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnLabRequest_Ward',
					'label' => 'Палата',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Врач',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Врач',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnDirection_IsCito',
					'label' => 'Cito',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'PayType_id',
					'label' => 'Вид оплаты',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Diag_id',
					'label' => 'Диагноз',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'parentEvnClass_SysNick',
					'label' => 'Класс события',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'uslugaData',
					'label' => 'Массив данных по услугам',
					'rules' => '',
					'type' => 'string'
				)
			),
			'saveEvnProcRequest' => [
				[
					'field' => 'EvnFuncRequest_id',
					'label' => 'ID заявки',
					'rules' => 'trim',
					'type' => 'int'
				],
				[
					'field' => 'EvnDirection_id',
					'label' => 'Направление',
					'rules' => '',
					'type' => 'id'
				],
				[
					'field' => 'TimetableMedService_id',	
					'label' => 'ID бирки',	
					'rules' => 'trim',	
					'type' => 'id'
				],
				[
					'field' => 'PersonEvn_id',
					'label' => 'Идентификатор состояния человека',
					'rules' => 'required',
					'type' => 'id'
				],
				[
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => '',
					'type' => 'id'
				],
				[
					'field' => 'Server_id',
					'label' => 'Сервер',
					'rules' => 'required',
					'type' => 'int'
				],
				[
					'field' => 'MedService_id',
					'label' => 'Служба',
					'rules' => '',
					'type' => 'id'
				],
				[
					'field' => 'PrescriptionStatusType_id',
					'label' => 'Статус назначения',
					'rules' => '',
					'type' => 'id'
				],
				
				[
					'field' => 'EvnPrescrProc_CountInDay',
					'label' => 'Повторов в сутки',
					'rules' => '',
					'type' => 'int'
				],
				[
					'field' => 'EvnPrescrProc_CourseDuration',
					'label' => 'Продолжительность курса',
					'rules' => '',
					'type' => 'int'
				],
				[
					'field' => 'EvnPrescrProc_ContReception',
					'label' => 'Непрерывный прием',
					'rules' => '',
					'type' => 'int'
				],
				[
					'field' => 'EvnPrescrProc_Interval',
					'label' => 'Перерыв',
					'rules' => '',
					'type' => 'int'
				],
				[
					'field' => 'EvnCourseProc_id',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				],
				[
					'field' => 'EvnPrescrProc_id',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				],
				[
					'field' => 'DurationType_id',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				],
				[
					'field' => 'DurationType_nid',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				],
				[
					'field' => 'DurationType_sid',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				],
				[
					'field' => 'EvnDirection_Num',
					'label' => 'Номер направления',
					'rules' => '',
					'type' => 'string'
				],
				[
					'field' => 'EvnDirection_setDT',
					'label' => 'Дата направления',
					'rules' => '',
					'type' => 'date'
				],
				[
					'field' => 'PrehospDirect_id',
					'label' => 'Кем направлен',
					'rules' => '',
					'type' => 'id'
				],
				[
					'field' => 'Org_sid',
					'label' => 'Направившая организация',
					'rules' => '',
					'type' => 'id'
				],
				[
					'field' => 'Lpu_sid',
					'label' => 'Направившее ЛПУ',
					'rules' => '',
					'type' => 'id'
				],
				[
					'field' => 'LpuSection_id',
					'label' => 'Направившее отделение ЛПУ',
					'rules' => '',
					'type' => 'id'
				],
				[
					'field' => 'EvnLabRequest_Ward',
					'label' => 'Палата',
					'rules' => '',
					'type' => 'string'
				],
				[
					'field' => 'MedPersonal_id',
					'label' => 'Врач',
					'rules' => '',
					'type' => 'id'
				],
				[
					'field' => 'EvnDirection_IsCito',
					'label' => 'Cito',
					'rules' => '',
					'type' => 'string'
				],
				[
					'field' => 'PayType_id',
					'label' => 'Вид оплаты',
					'rules' => 'required',
					'type' => 'id'
				],
				[
					'field' => 'UslugaComplex_id',
					'label' => 'Услуга',
					'rules' => '',
					'type' => 'id'
				],
				[
					'field' => 'Diag_id',
					'label' => 'Диагноз',
					'rules' => '',
					'type' => 'id'
				],
				[
					'field' => 'EvnPrescr_IsExec',
					'label' => 'Выполнено',
					'rules' => '',
					'type' => 'string'
				],
				[
					'field' => 'EvnPrescrProc_didDT',
					'label' => 'Время выполнения',
					'rules' => '',
					'type' => 'string'
				],
				[
					'field' => 'EvnPrescrProc_Descr',
					'label' => 'Время выполнения',
					'rules' => '',
					'type' => 'string'
				]
			],
			'loadEvnUslugaEditForm' => array(
				array(
					'field' => 'EvnUslugaPar_id',
					'label' => 'ID услуги',
					'rules' => 'trim|required',
					'type' => 'id'
				)
			), 
			'saveEvnUslugaEditForm' => array(
				array('field' => 'EvnUslugaPar_id',	'label' => 'ID услуги',	'rules' => 'trim|required',	'type' => 'id'),
				array('field' => 'EvnDirection_id','label' => 'ID направления','rules' => 'trim','type' => 'id'),
				array('field' => 'Lpu_id','label' => 'ЛПУ','rules' => 'required','type' => 'id'),
				array('field' => 'PersonEvn_id','label' => 'Идентификатор состояния пациента','rules' => 'required','type' => 'id'),
				array('field' => 'Server_id','label' => 'Идентификатор сервера','rules' => 'required','type' => 'int'),
				array('field' => 'EvnUslugaPar_setDate','label' => 'Дата оказания услуги','rules' => 'trim|required','type' => 'date'),
				array('field' => 'EvnUslugaPar_setTime','label' => 'Время оказания услуги','rules' => 'trim','type' => 'time'),
				array('field' => 'Org_uid','label' => 'Организация','rules' => 'required','type' => 'id'),
				array('field' => 'LpuSection_uid','label' => 'Отделение','rules' => 'required','type' => 'id'),
				array('field' => 'MedStaffFact_id','label' => 'Рабочее место врача','rules' => '','type' => 'id'),
				array('field' => 'MedPersonal_uid','label' => 'Врач','rules' => 'required','type' => 'id'),
				array('field' => 'MedPersonal_sid','label' => 'Cр. мед. персонал','rules' => '','type' => 'id'),
				array('field' => 'UslugaComplex_id', 'label' => 'Услуга', 'rules' => 'trim|required', 'type' => 'id'),
				array('field' => 'PayType_id','label' => 'Вид оплаты','rules' => 'required','type' => 'id'),
				array('field' => 'AssociatedResearches','label' => 'Прикрепленные исследования','rules' => '','type' => 'string'),
				array('field' => 'EvnUslugaPar_Regime','label' => 'Режим','rules' => '','type' => 'int'),
				array('field' => 'EvnUslugaPar_Comment','label' => 'Комментарий','rules' => '','type' => 'string'),
				
			),
			'addEvnFuncRequest' => array(
				array(
					'field' => 'PersonEvn_id',
					'label' => 'Идентификатор состояния человека',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Server_id',
					'label' => 'Сервер',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'MedService_id',
					'label' => 'Служба',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Выбранная услуга',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'PayType_id',
					'label' => 'Вид оплаты',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getEvnFuncRequestWithAssociatedResearches'=>array(
				array('field' => 'Person_id','label' => 'Идентификатор человека','rules' => '','type' => 'id'),
			),
			'cancelEvnUslugaPar' => array(
				array(
					'field' => 'EvnUslugaPar_id',
					'label' => 'Идентификатор услуги',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'cancelDirection' => array(
				array(
					'field' => 'EvnDirection_id',
					'label' => 'Идентификатор направления',
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
			'delete' => array(
				array(
					'field' => 'EvnFuncRequest_id',
					'label' => 'Идентификатор заявки на функциональную диагностику',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'sendUslugaParToRCC' => array(
				array('field' => 'EvnUslugaPar_id','label' => 'Идентификатор параклинической услуги','rules' => 'required','type' => 'id'),
				array('field' => 'MedService_lid','label' => 'Идентификатор службы ФД','rules' => '','type' => 'id'),
			),
			'loadRemoteConsultCenterResearchList' => array(
				array('field' => 'begDate','label' => 'Дата с','rules' => 'trim|required','type' => 'date'),
				array('field' => 'endDate','label' => 'Дата по','rules' => 'trim|required','type' => 'date'),
				array('field' => 'MedService_id','label' => 'Идентификатор службы ЦУК','rules' => '','type' => 'id'),
			),
		);
		
	}
	
	/**
	 * Отмена направления
	 *
	 * @return bool
	 */
	function cancelDirection() {
		$data = $this->ProcessInputData('cancelDirection', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->cancelDirection($data);
		$this->ProcessModelSave($response, true, 'Ошибка отмены направления')->ReturnData();
		
		return true;
	}

	/**
	 * Отмена выполнения услуги
	 */
	function cancelEvnUslugaPar() {
		$data = $this->ProcessInputData('cancelEvnUslugaPar', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->cancelEvnUslugaPar($data);
		$this->ProcessModelSave($response, true, 'Ошибка отмены выполнения услуги')->ReturnData();

		return true;
	}
	
	/**
	 * Удаление
	 *
	 * @return bool
	 */
	function delete() {
		$data = $this->ProcessInputData('delete', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->delete($data);
		$this->ProcessModelSave($response, true, 'Ошибка удаления заявки')->ReturnData();
		
		return true;
	}

	/**
	 * Получение данных для грида формы АРМ ФД
	 * @return bool
	 */
	function loadEvnFuncRequestViewList() {
		$data = $this->ProcessInputData('loadEvnFuncRequestList', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadEvnFuncRequestList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Получение данных для грида формы АРМ Физиотерапевта Журнал проведенных процедур
	 * @return bool
	 */
	function loadEvnFuncRequestListDoneStatus() {
		$data = $this->ProcessInputData('loadEvnFuncRequestList', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadEvnFuncRequestListDoneStatus($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
	/**
	 * Получение данных для грида формы АРМ ФД
	 * @return bool
	 */
	function loadEvnFuncRequestList() {
		$data = $this->ProcessInputData('loadEvnFuncRequestList', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadEvnFuncRequestList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
	/**
	 * Проверка наличия расписания на услугу
	 * @return bool
	 */
	function checkUslugaComplexMedServiceTimeTable() {
		$data = $this->ProcessInputData('checkUslugaComplexMedServiceTimeTable', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->checkUslugaComplexMedServiceTimeTable($data);
		$this->ProcessModelSave($response, true, 'Ошибка проверки наличия расписания на услугу')->ReturnData();

		return true;
	}

	/**
	 * Отдаёт данные для формы заявки на исследование
	 * 
	 * @return bool
	 */
	function getEvnProcRequest() {
		$data = $this->ProcessInputData('getEvnProcRequest', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->getEvnProcRequest($data);
		return $this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * @return bool
	 */
	function getEvnFuncRequest() {
		$data = $this->ProcessInputData('getEvnFuncRequest', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->getEvnFuncRequest($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
	}
	
	/**
	 * @return bool
	 */
	function getEvnFuncRequestWithAssociatedResearches() {
		$data = $this->ProcessInputData('getEvnFuncRequestWithAssociatedResearches', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->getEvnFuncRequestWithAssociatedResearches($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
	}	

	/**
	 * Создание/обновление заявки с направлением
	 * @return bool
	 */
	function saveEvnFuncRequest() {
		$data = $this->ProcessInputData('saveEvnFuncRequest', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->saveEvnFuncRequest($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}

	/**
	 * Создание заявки без направления
	 * @return bool
	 */
	function addEvnFuncRequest() {
		$data = $this->ProcessInputData('addEvnFuncRequest', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->addEvnFuncRequest($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}

	/**
	 * Создание/обновление заявки на исследование
	 * 
	 * @return bool
	 */
	function saveEvnProcRequest() {
		$data = $this->ProcessInputData('saveEvnProcRequest', true);
		if ($data === false) {
			return false;
		}
		
		try {
			$response = $this->dbmodel->saveEvnProcRequest($data);
		} catch (Exception $e) {
			$response = [
				'Error_Msg' => $e->getMessage(),
				'Error_Code' => $e->getCode()
			];
		}
		
		return $this->ProcessModelSave($response)->ReturnData();
	}

	/**
	 * Получение данных для формы результат выполнения услуги
	 * @return bool
	 */
	function loadEvnUslugaEditForm() {
		$data = $this->ProcessInputData('loadEvnUslugaEditForm', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadEvnUslugaEditForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
	}

	/**
	 * Сохранение данных формы "Результат выполнения услуги"
	 * @return bool
	 */
	function saveEvnUslugaEditForm() {
		$data = $this->ProcessInputData('saveEvnUslugaEditForm', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->saveEvnUslugaEditForm($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		
		return true;
	}
	
	/**
	 * Отправка исследования в центр удаленной консультации
	 */
	function sendUslugaParToRCC() {
		
	
		$data = $this->ProcessInputData('sendUslugaParToRCC', true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->sendUslugaParToRCC($data);
		
		$this->ProcessModelSave($response, true)->ReturnData();
		
		return true;
	}
	
	/**
	 * 
	 * Загрузка грида в АРМ Центра удалённой конслуьтации
	 */
	function loadRemoteConsultCenterResearchList() {
		$data = $this->ProcessInputData('loadRemoteConsultCenterResearchList', true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->loadRemoteConsultCenterResearchList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
	}
	
	
}

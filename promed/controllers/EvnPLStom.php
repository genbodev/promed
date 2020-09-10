<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnPlStom - Класс контроллера для работы с ТАП по стоматологии
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package				Common
 * @copyright			Copyright (c) 2010-2011 Swan Ltd.
 * @author				Stas Bykov aka Savage (savage1981@gmail.com)
 * @link				http://swan.perm.ru/PromedWeb
 * @version				?
 */

/**
 * @property EvnPLStom_model $dbmodel
 * @property EvnVizitPLStom_model $EvnVizitPLStom_model
 */
class EvnPLStom extends swController {
	public $inputRules = array(
		'getEvnPLDate' => array(
			array(
				'field' => 'EvnPL_id',
				'label' => 'Идентификатор талона амбулаторного пациента',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getEvnPLStomNumber' => array(
			array('field' => 'year','label' => 'Год','rules' => '','type' => 'int')
		),
		'addEvnVizitPLStom' => array(
			array(
				'field' => 'EvnPLStom_id',
				'label' => 'Идентификатор стомат. ТАП',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'Рабочее место врача',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Отделение',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'TimetableGraf_id',
				'label' => 'Идентификатор бирки',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Врач',
				'rules' => 'required',
				'type' => 'id'
			),
			[
				'field' => 'LpuSectionProfile_id',
				'label' => 'Профиль',
				'rules' => '',
				'type' => 'id'
			],
			[
				'field' => 'TimetableGraf_id',
				'label' => 'Бирка',
				'rules' => '',
				'type' => 'id'
			],
			[
				'field' => 'EvnDirection_id',
				'label' => 'Идентификатор направления',
				'rules' => '',
				'type' => 'id'
			],
			[
				'field' => 'vizit_kvs_control_check',
				'label' => 'Контроль пересечения посещения с КВС',
				'rules' => 'trim',
				'type' => 'int'
			],
			array(
				'field' => 'ignoreDayProfileDuplicateVizit',
				'label' => 'Признак игнорирования дублей посещений по профилю',
				'rules' => 'trim',
				'type' => 'int'
			)
		),
		'saveEvnVizitFromEMK' => array(
			array(
				'field' => 'EvnVizitPLStom_id',
				'label' => 'Идентификатор стомат. посещения',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnVizitPLStom_setDate',
				'label' => 'Дата посещения',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'EvnVizitPLStom_setTime',
				'label' => 'Время посещения',
				'rules' => 'required',
				'type' => 'time'
			),
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'Рабочее место врача',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_sid',
				'label' => 'Средний мед. персонал',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'TreatmentClass_id',
				'label' => 'Вид обращения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ServiceType_id',
				'label' => 'Место',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'VizitType_id',
				'label' => 'Цель посещения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'VizitClass_id',
				'label' => 'Прием',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedicalCareKind_id',
				'label' => 'Вид мед. помощи',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplex_uid',
				'label' => 'Код посещения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplexTariff_id',
				'label' => 'Тариф',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnUslugaStom_UED',
				'label' => 'УЕТ врача',
				'rules' => '',
				'type' => 'float'
			),
			array(
				'field' => 'LpuSectionProfile_id',
				'label' => 'Профиль',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PayType_id',
				'label' => 'Вид оплаты',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_id',
				'label' => 'Основной диагноз',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DeseaseType_id',
				'label' => 'Характер заболевания',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnVizitPLStom_IsPrimaryVizit',
				'label' => 'Первично в текущем году',
				'rules' => '',
				'type' => 'id'
			),
			[
				'field' => 'BitePersonType_id',
				'label' => 'Прикус',
				'rules' => '',
				'type' => 'id'
			],
			[
				'field' => 'PersonDisp_id',
				'label' => 'Карта дис. учета',
				'rules' => '',
				'type' => 'id'
			],
			[
				'field' => 'DispClass_id',
				'label' => 'В рамках дисп./мед.осмотра',
				'rules' => '',
				'type' => 'id'
			],
			[
				'field' => 'DispProfGoalType_id',
				'label' => 'В рамках дисп./мед.осмотра',
				'rules' => '',
				'type' => 'id'
			],
			[
				'field' => 'Mes_id',
				'label' => 'МЭС',
				'rules' => '',
				'type' => 'id'
			],
			[
				'field' => 'EvnPLDisp_id',
				'label' => 'Карта дисп./мед.осмотра',
				'rules' => '',
				'type' => 'id'
			]
		),
		'loadEvnPLStomEditForm' => array(
			array(
				'field' => 'EvnPLStom_id',
				'label' => 'Идентификатор талона',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'delDocsView',
				'label' => 'Просмотр удаленных документов',
				'rules' => '',
				'type' => 'id'
			)
		),
		'getCurrentBitePersonData' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadEmkEvnPLStomEditForm' => array(
			array(
				'field' => 'EvnPLStom_id',
				'label' => 'Идентификатор талона',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnVizitPLStom_id',
				'label' => 'Идентификатор посещения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'loadLast',
				'label' => 'Загружать последнее',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadEvnVizitPLStomGrid' => array(
			array(
				'field' => 'EvnPLStom_id',
				'label' => 'Идентификатор талона',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnVizitPL_id',
				'label' => 'Идентификатор посещения',
				'rules' => '',
				'type' => 'id'
			)
		),
		'printEvnPLStomUfa' => array(
			array(
				'field' => 'EvnPLStom_id',
				'label' => 'Идентификатор талона амбулаторного пациента',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'printEvnPLStomPerm' => array(
			array(
				'field' => 'EvnPLStom_id',
				'label' => 'Идентификатор талона амбулаторного пациента',
				'rules' => 'required',
				'type' => 'id'
			)
		),		
		'loadEvnPLStomStreamList' => array(
			array(
				'default' => NULL,
				'field' => 'begDate',
				'label' => 'Дата начала',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'default' => NULL,
				'field' => 'begTime',
				'label' => 'Время начала',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество записей',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Начальная запись',
				'rules' => '',
				'type' => 'int'
			),
		),
		'getEvnDiagPLStom' => array(
			array(
				'field' => 'EvnPLStom_id',
				'label' => 'Идентификатор талона амбулаторного пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnVizitPLStom_id',
				'label' => 'Идентификатор посещения пациента',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadEvnPLStomFinishForm' => array(
			array(
				'field' => 'EvnPLStom_id',
				'label' => 'Идентификатор стомат. ТАП',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'saveBitePersonType' => array(
			array(
				'field' => 'BitePersonData_id',
				'label' => 'Идентификатор родительского события',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnVizitPLStom_id',
				'label' => 'Идентификатор посещения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'BitePersonData_setDate',
				'label' => 'Дата посещения',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'BitePersonData_disDate',
				'label' => 'Дата посещения',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'BitePersonType_id',
				'label' => 'Идентификатор типа прикуса',
				'rules' => '',
				'type' => 'id'
			)
		),
		'saveEvnPLStomFinishForm' => array(
			array(
				'field' => 'EvnPLStom_id',
				'label' => 'Идентификатор стомат. ТАП',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLStom_IsFinish',
				'label' => 'Случай закончен',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLStom_IsSurveyRefuse',
				'label' => 'Отказ от прохождения медицинских обследований',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ResultClass_id',
				'label' => 'Результат лечения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'InterruptLeaveType_id',
				'label' => 'Случай прерван',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ResultDeseaseType_id',
				'label' => 'Исход',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLStom_UKL',
				'label' => 'УКЛ',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'DirectType_id',
				'label' => 'Направление',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DirectClass_id',
				'label' => 'Куда направлен',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_lid',
				'label' => 'Закл. диагноз',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_concid',
				'label' => 'Закл. внешняя причина',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PrehospTrauma_id',
				'label' => 'Вид травмы (внеш. возд)',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLStom_IsUnlaw',
				'label' => 'Противоправная',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLStom_IsUnport',
				'label' => 'Нетранспортабельность',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LeaveType_fedid',
				'label' => 'Фед. результат',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ResultDeseaseType_fedid',
				'label' => 'Фед. исход',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLStom_IsSan',
				'label' => 'Санирован',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'SanationStatus_id',
				'label' => 'Санация',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ignoreParentEvnDateCheck',
				'label' => 'Признак игнорирования проверки периода выполенения услуги',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ignoreMesUslugaCheck',
				'label' => 'Признак игнорирования проверки МЭС',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ignoreKsgInMorbusCheck',
				'label' => 'Ошибка при проверке заполнения поля КСГ',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ignoreUetSumInNonMorbusCheck',
				'label' => 'Флаг игнорирования проверки превышения суммы УЕТ в услугах максимального КСГ по указанному диагнозу',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ignoreCheckEvnUslugaChange',
				'label' => 'Игнорировать проверку наличия паракл. услуг',
				'rules' => '',
				'type' => 'int'
			)
		)
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		
		$this->load->database();
		$this->load->model('EvnPLStom_model', 'dbmodel');
	}

	/**
	 * Получение номера талона амбулаторного пациента
	 * Входящие данные: нет
	 * На выходе: JSON-строка
	 * Используется: форма редактирования талона амбулаторного пациента
	 */
	function getEvnPLStomNumber() {
		$data = $this->ProcessInputData('getEvnPLStomNumber', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->getEvnPLStomNumber($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}


	/**
	 * Получение данных для формы редактирования ТАП
	 * Входящие данные: $_POST['EvnPLStom_id']
	 * На выходе: JSON-строка
	 * Используется: форма редактирования ТАП
	 */
	function loadEvnPLStomEditForm() {
		$data = $this->ProcessInputData('loadEvnPLStomEditForm', true);
		if ( $data === false ) { return false; }
		
		
		if($data['delDocsView'] && $data['delDocsView'] == 1)
			$response = $this->dbmodel->loadEvnPLStomEditFormForDelDocs($data);
		else
			$response = $this->dbmodel->loadEvnPLStomEditForm($data);
		
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение данных для формы редактирования ТАП, вызываемой из ЭМК
	 * Входящие данные: $_POST['EvnPLStom_id'], $_POST['EvnVizitPLStom_id']
	 * На выходе: JSON-строка
	 * Используется: дополнительная форма редактирования ТАП
	 */
	function loadEmkEvnPLStomEditForm() {
		$data = $this->ProcessInputData('loadEmkEvnPLStomEditForm', true, true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadEmkEvnPLStomEditForm($data);
		$this->ProcessModelList($response)->ReturnData();

		return true;
	}

	/**
	 * Получение списка ТАП для потокового ввода
	 * Входящие данные: $_POST['begDate'],
	 *                  $_POST['begTime']
	 * На выходе: JSON-строка
	 * Используется: форма потокового ввода ТАП
	 */
	function loadEvnPLStomStreamList() {
		$data = $this->ProcessInputData('loadEvnPLStomStreamList', true);
		if ($data === false) { return false; }

		$outdata = array();
		$response = $this->dbmodel->loadEvnPLStomStreamList($data);
		$outdata['data'] = $this->ProcessModelList($response, true, true)->GetOutData();
		$this->ReturnData($outdata);
		
		return true;
	}


	/**
	 * Получение списка посещений пациентом поликлиники
	 * Входящие данные: $_POST['EvnPLStom_id'],
	 *                  $_POST['EvnVizitPLStom_id']
	 * На выходе: JSON-строка
	 * Используется: форма редактирования талона амбулаторного пациента
	 *               электронный паспорт здоровья
	 */
	function loadEvnVizitPLStomGrid() {
		$data = $this->ProcessInputData('loadEvnVizitPLStomGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadEvnVizitPLStomGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Печать талона амбулаторного пациента
	 * Входящие данные: $_GET['EvnPLStom_id']
	 * На выходе: форма для печати талона амбулаторного пациента
	 * Используется: форма редактирования талона амбулаторного пациента
	 */
	function printEvnPLStom() {
		switch ( $_SESSION['region']['nick'] ) {
			case 'perm':
				$this->printEvnPLStomPerm();
			break;

			case 'ufa':
				$this->printEvnPLStomUfa();
			break;

			default:
				$this->printEvnPLStomPerm();
			break;
		}

		return true;
	}
	
	
	/**
	 * Печать талона амбулаторного пациента (Пермский край)
	 * Входящие данные: $_GET['EvnPLStom_id']
	 * На выходе: форма для печати талона амбулаторного пациента
	 * Используется: форма редактирования талона амбулаторного пациента
	 */
	function printEvnPLStomPerm($EvnPLStom_id = null, $ReturnString = false) {
		$this->load->helper('Options');
		$this->load->library('parser');

		$data = $this->ProcessInputData('printEvnPLStomPerm', true);
		if ($data === false) { return false; }

		// Получаем настройки
		$options = getOptions();

		// Получаем данные по талону
		$response = $this->dbmodel->getEvnPLStomFieldsPerm($data);

		if ( !is_array($response) || count($response) == 0 ) {
			echo 'Ошибка при получении данных по талону';
			return true;
		}

		$evn_diag_pl_osn_data = array();
		$evn_diag_pl_sop_data = array();
		$evn_stick_data = array();
		$evn_stick_work_release_data = array();
		$evn_vizit_pl_data = array();
		$person_disp_data = array();

		$response_temp = $this->dbmodel->getEvnVizitPLStomDataPerm($data);
		if ( is_array($response_temp) ) {
			$evn_vizit_pl_data = $response_temp;

			if ( count($evn_vizit_pl_data) < 4 ) {
				for ( $i = count($evn_vizit_pl_data); $i < 4; $i++ ) {
					$evn_vizit_pl_data[$i] = array(
						'EVPL_EvnVizitPL_setDate' => '&nbsp;',
						'EVPL_LpuSection_Code' => '&nbsp;',
						'EVPL_MedPersonal_Fio' => '&nbsp;',
						'EVPL_MidMedPersonal_Code' => '&nbsp;',
						'EVPL_EvnVizitPL_Name' => '&nbsp;',
						'EVPL_ServiceType_Name' => '&nbsp;',
						'EVPL_VizitType_Name' => '&nbsp;',
						'EVPL_PayType_Name' => '&nbsp;'
					);
				}
			}
		}

		$response_temp = $this->dbmodel->getEvnDiagPLOsnData($data);
		if ( is_array($response_temp) ) {
			$evn_diag_pl_osn_data = $response_temp;

			if ( count($evn_diag_pl_osn_data) < 2 ) {
				for ( $i = count($evn_diag_pl_osn_data); $i < 2; $i++ ) {
					$evn_diag_pl_osn_data[$i] = array(
						'EvnDiagPL_setDate' => '&nbsp;',
						'LpuSection_Name' => '&nbsp;',
						'MedPersonal_Code' => '&nbsp;',
						'Diag_Code' => '&nbsp;',
						'DeseaseType_Name' => '&nbsp;'
					);
				}
			}
		}

		$response_temp = $this->dbmodel->getEvnDiagPLSopData($data);
		if ( is_array($response_temp) ) {
			$evn_diag_pl_sop_data = $response_temp;

			if ( count($evn_diag_pl_sop_data) < 2 ) {
				for ( $i = count($evn_diag_pl_sop_data); $i < 2; $i++ ) {
					$evn_diag_pl_sop_data[$i] = array(
						'EvnDiagPL_setDate' => '&nbsp;',
						'LpuSection_Name' => '&nbsp;',
						'MedPersonal_Code' => '&nbsp;',
						'Diag_Code' => '&nbsp;',
						'DeseaseType_Name' => '&nbsp;'
					);
				}
			}
		}

		$response_temp = $this->dbmodel->getEvnStickData($data);
		if ( is_array($response_temp) ) {
			$evn_stick_data = $response_temp;

			if ( count($evn_stick_data) < 2 ) {
				for ( $i = count($evn_stick_data); $i < 2; $i++ ) {
					$evn_stick_data[$i] = array(
						'EvnStick_begDate' => '&nbsp;',
						'EvnStick_endDate' => '&nbsp;',
						'StickType_Name' => '&nbsp;',
						'EvnStick_Ser' => '&nbsp;',
						'EvnStick_Num' => '&nbsp;',
						'StickCause_Name' => '&nbsp;',
						'StickIrregularity_Name' => '&nbsp;',
						'Sex_Name' => '&nbsp;',
						'EvnStick_Age' => '&nbsp;'
					);
				}
			}
		}

		$response_temp = $this->dbmodel->getEvnStickWorkReleaseData($data);
		if ( is_array($response_temp) ) {
			$evn_stick_work_release_data = $response_temp;

			if ( count($evn_stick_work_release_data) < 4 ) {
				for ( $i = count($evn_stick_work_release_data); $i < 4; $i++ ) {
					$evn_stick_work_release_data[$i] = array(
						'EvnStick_SerNum' => '&nbsp;',
						'EvnStickWorkRelease_begDate' => '&nbsp;',
						'EvnStickWorkRelease_endDate' => '&nbsp;',
						'MedPersonal_Fio' => '&nbsp;'
					);
				}
			}
		}

		for ( $i = count($person_disp_data); $i < 2; $i++ ) {
			$person_disp_data[$i] = array(
				'PersonDisp_Name' => '&nbsp;',
				'Diag_Code' => '&nbsp;',
				'PersonDisp_nextDate' => '&nbsp;',
				'PersonDisp_begDate' => '&nbsp;',
				'PersonDisp_endDate' => '&nbsp;',
				'DispOutType_Name' => '&nbsp;'
			);
		}

        $highlight_style = 'font-weight: bold;';
        if ($data['session']['region']['nick'] == 'khak') {
            $template = 'evn_pl_template_list_a4_hakasiya';
            $highlight_style = 'text-decoration:underline;';
        } else {$template = 'evn_pl_template_list_a4_perm';}

		$print_data = array(
            'DeseaseTypeSop_Code' => returnValidHTMLString($response[0]['DeseaseTypeSop_Code']),
            'DeseaseType_id' => returnValidHTMLString($response[0]['DeseaseType_id']),
            //'DeseaseTypeSop_id' => returnValidHTMLString($response[0]['DeseaseTypeSop_id']),
            'PersonDisp' => returnValidHTMLString($response[0]['PersonDisp']),
            'PersonDispSop' => returnValidHTMLString($response[0]['PersonDispSop']),
			'DirectOrg_Name' => returnValidHTMLString($response[0]['DirectOrg_Name']),
			'DirectClass_Name' => returnValidHTMLString($response[0]['DirectClass_Name']),
			'DirectType_Name' => returnValidHTMLString($response[0]['DirectType_Name']),
			'DirectType_Code' => returnValidHTMLString($response[0]['DirectType_Code']),
			'Document_begDate' => returnValidHTMLString($response[0]['Document_begDate']),
			'Document_Num' => returnValidHTMLString($response[0]['Document_Num']),
			'Document_Ser' => returnValidHTMLString($response[0]['Document_Ser']),
			'DocumentType_Name' => returnValidHTMLString($response[0]['DocumentType_Name']),
            'Person_Docum' => returnValidHTMLString($response[0]['DocumentType_Name'] . ' ' . $response[0]['Document_Ser'] . ' ' . $response[0]['Document_Num']),
			'EvnDiagPLOsnData' => $evn_diag_pl_osn_data,
			'EvnDiagPLSopData' => $evn_diag_pl_sop_data,
			'EvnPL_IsFinish' => $response[0]['EvnPLStom_IsFinish'] == 1 ? 'X' : '&nbsp;',
			'EvnPL_IsNotFinish' => $response[0]['EvnPLStom_IsFinish'] == 1 ? '&nbsp;' : 'X',
			'EvnPL_IsUnlaw' => $response[0]['EvnPLStom_IsUnlaw'] == 1 ? 'X' : '&nbsp;',
			'EvnPL_IsUnport' => $response[0]['EvnPLStom_IsUnport'] == 1 ? 'X' : '&nbsp;',
			'EvnPL_NumCard' => returnValidHTMLString($response[0]['EvnPLStom_NumCard']),
			'EvnPL_UKL' => $response[0]['EvnPLStom_UKL'] > 0 ? $response[0]['EvnPLStom_UKL'] : '&nbsp;',
			'EvnPLTemplateTitle' => 'Печать талона амбулаторного пациента',
			'EvnStickData' => $evn_stick_data,
            'EvnStick_Open' => returnValidHTMLString($response[0]['EvnStick_Open']),
			'EvnStickWorkReleaseData' => $evn_stick_work_release_data,
			'EvnUdost_Num' => returnValidHTMLString($response[0]['EvnUdost_Num']),
			'EvnUdost_Ser' => returnValidHTMLString($response[0]['EvnUdost_Ser']),
			'EvnVizitPLData' => $evn_vizit_pl_data,
			'KLAreaType_Name' => returnValidHTMLString($response[0]['KLAreaType_Name']),
			'Lpu_Name' => returnValidHTMLString($response[0]['Lpu_Name']),
			'Lpu_OGRN' => returnValidHTMLString($response[0]['Lpu_OGRN']),
			'EvnPL_setDate' => returnValidHTMLString($response[0]['EvnPL_setDate']),
			'Lpu_Address' => returnValidHTMLString($response[0]['Lpu_Address']),
			'MedPersonal_TabCode' => returnValidHTMLString($response[0]['MedPersonal_TabCode']),
			'MedPersonal_Fio' => returnValidHTMLString($response[0]['MedPersonal_Fio']),
			'Person_Snils' => returnValidHTMLString($response[0]['Person_Snils']),
			'DiagSop_Code' => returnValidHTMLString($response[0]['DiagSop_Code']),
			'FinalDiag_Code' => returnValidHTMLString($response[0]['FinalDiag_Code']),
			'EvnStick_Age' => ($response[0]['EvnStick_Age'] != 0)?returnValidHTMLString($response[0]['EvnStick_Age']):(''),
			'EvnStick_begDate' => returnValidHTMLString($response[0]['EvnStick_begDate']),
			'EvnStick_endDate' => returnValidHTMLString($response[0]['EvnStick_endDate']),
			'LpuRegion_Name' => returnValidHTMLString($response[0]['LpuRegion_Name']),
			'Org_Name' => returnValidHTMLString($response[0]['Org_Name']),
			'OrgDep_Name' => returnValidHTMLString($response[0]['OrgDep_Name']),
			'OrgSmo_Name' => returnValidHTMLString($response[0]['OrgSmo_Name']),
			'PAddress_Name' => returnValidHTMLString($response[0]['PAddress_Name']),
            'PayType_Code' => returnValidHTMLString($response[0]['PayType_Code']),
			'Person_Birthday' => returnValidHTMLString($response[0]['Person_Birthday']),
			'Person_Fio' => returnValidHTMLString($response[0]['Person_Fio']),
			'PersonCard_Code' => returnValidHTMLString($response[0]['PersonCard_Code']),
			'PersonDispData' => $person_disp_data,
			'PersonPrivilege_begDate' => returnValidHTMLString($response[0]['PersonPrivilege_begDate']),
			'Polis_begDate' => returnValidHTMLString($response[0]['Polis_begDate']),
			'Polis_endDate' => returnValidHTMLString($response[0]['Polis_endDate']),
			'Polis_Num' => returnValidHTMLString($response[0]['Polis_Num']),
			'Polis_Ser' => returnValidHTMLString($response[0]['Polis_Ser']),
			'PolisType_Name' => returnValidHTMLString($response[0]['PolisType_Name']),
			'Post_Name' => returnValidHTMLString($response[0]['Post_Name']),
			'PrehospDiag_Code' => returnValidHTMLString($response[0]['PrehospDiag_Code']),
			'PrehospDiag_Name' => returnValidHTMLString($response[0]['PrehospDiag_Name']),
			'PrehospDirect_Name' => returnValidHTMLString($response[0]['PrehospDirect_Name']),
			'PrehospOrg_Name' => returnValidHTMLString($response[0]['PrehospOrg_Name']),
			'PrehospTrauma_Name' => returnValidHTMLString($response[0]['PrehospTrauma_Name']),
			'PrivilegeType_Name' => returnValidHTMLString($response[0]['PrivilegeType_Name']),
			'PrivilegeType_Code' => returnValidHTMLString($response[0]['PrivilegeType_Code']),
			'PrivilegeType_gr' => returnValidHTMLString($response[0]['PrivilegeType_gr']),
			'PrivilegeType_fromBirth' => returnValidHTMLString($response[0]['PrivilegeType_fromBirth']),
			'ResultClass_Name' => returnValidHTMLString($response[0]['ResultClass_Name']),
            'ResultClass_Code' => returnValidHTMLString($response[0]['ResultClass_Code']),
            'ServiceType_Code' => returnValidHTMLString($response[0]['ServiceType_Code']),
			'Sex_Name' => returnValidHTMLString($response[0]['Sex_Name']),
            'Sex_id' => returnValidHTMLString($response[0]['Sex_id']),
			'SocStatus_Name' => returnValidHTMLString($response[0]['SocStatus_Name']),
            'SocStatus_Code' => returnValidHTMLString($response[0]['SocStatus_Code']),
			'UAddress_Name' => returnValidHTMLString($response[0]['UAddress_Name'])
		);

        switch ( $response[0]['PrivilegeType_gr'] ) {
            case '81':
                $print_data['PrivilegeType_1gr'] = $highlight_style;
                break;
            case '82':
                $print_data['PrivilegeType_2gr'] = $highlight_style;
                break;
            case '83':
                $print_data['PrivilegeType_3gr'] = $highlight_style;
                break;
            case '84':
                $print_data['PrivilegeType_child'] = $highlight_style;
                break;
        }

        if ( $response[0]['PrivilegeType_fromBirth'] ) { $print_data['PrivilegeType_fromBirth'] = $highlight_style; }

        switch ( $response[0]['VizitType_SysNick'] ) {
            case 'desease':
                $print_data['VizitType_Code'] = 1;
                $print_data['VizitType_1'] = $highlight_style;
                break;

            case 'prof':
                $print_data['VizitType_Code'] = 2;
                $print_data['VizitType_2'] = $highlight_style;
                break;

            case 'patron':
                $print_data['VizitType_Code'] = 3;
                $print_data['VizitType_3'] = $highlight_style;
                break;

            default:
                $print_data['VizitType_Code'] = 4;
        }

        switch ( $response[0]['DirectType_SysNick'] ) {
            case 'stac':
                $print_data['DirectType_1'] = $highlight_style;
                break;

            case 'dstac':
                $print_data['DirectType_2'] = $highlight_style;
                break;

            case 'kons':
                $print_data['DirectType_3'] = $highlight_style;
                break;

            case 'other':
                $print_data['DirectType_31'] = $highlight_style;
                break;
        }

        switch ( $response[0]['ResultClass_SysNick'] ) {
            case 'vizdor':
                $print_data['ResultClass_1'] = $highlight_style;
                break;

            case 'better':
                $print_data['ResultClass_2'] = $highlight_style;
                break;

            case 'dinam':
                $print_data['ResultClass_3'] = $highlight_style;
                break;

            case 'worse':
                $print_data['ResultClass_4'] = $highlight_style;
                break;

            case 'die':
                $print_data['ResultClass_5'] = $highlight_style;
                break;
        }

        switch ( $response[0]['StickType_SysNick'] ) {
            case 'spravka':
                $print_data['StickType_1'] = $highlight_style;
                break;

            case 'blist':
                $print_data['StickType_11'] = $highlight_style;
                break;

            case 'dinam':
                $print_data['StickType_2'] = $highlight_style;
                break;

            case 'worse':
                $print_data['StickType_3'] = $highlight_style;
                break;
        }

        switch ( $response[0]['FinalDeseaseType_SysNick'] ) {
            case 'good':
                $print_data['FinalDeseaseType_0'] = $highlight_style;
                break;

            case 'sharp':
                $print_data['FinalDeseaseType_1'] = $highlight_style;
                break;

            case 'hrnew':
                $print_data['FinalDeseaseType_2'] = $highlight_style;
                break;

            case 'hrold':
                $print_data['FinalDeseaseType_3'] = $highlight_style;
                break;

            case 'hrobostr':
                $print_data['FinalDeseaseType_4'] = $highlight_style;
                break;

            case 'otrav':
                $print_data['FinalDeseaseType_5'] = $highlight_style;
                break;

            case 'trauma':
                $print_data['FinalDeseaseType_6'] = $highlight_style;
                break;
        }

        switch ( $response[0]['DeseaseTypeSop_SysNick'] ) {
            case 'sharp':
            case 'hrnew':
                $print_data['DeseaseTypeSop_1'] = $highlight_style;
                break;

            case 'hrold':
                $print_data['DeseaseTypeSop_2'] = $highlight_style;
                break;
        }

        switch ( $response[0]['PrehospTrauma_Code'] ) {
            case 1:
                $print_data['PrehospTrauma_1'] = $highlight_style;
                break;

            case 2:
                $print_data['PrehospTrauma_2'] = $highlight_style;
                break;

            case 3:
                $print_data['PrehospTrauma_21'] = $highlight_style;
                break;

            case 4:
                $print_data['PrehospTrauma_3'] = $highlight_style;
                break;

            case 5:
                $print_data['PrehospTrauma_4'] = $highlight_style;
                break;

            case 6:
                $print_data['PrehospTrauma_6'] = $highlight_style;
                break;

            case 7:
                $print_data['PrehospTrauma_7'] = $highlight_style;
                break;

            case 8:
                $print_data['PrehospTrauma_8'] = $highlight_style;
                break;

            case 9:
                $print_data['PrehospTrauma_81'] = $highlight_style;
                break;

            case 10:
                $print_data['PrehospTrauma_9'] = $highlight_style;
                break;

            case 11:
                $print_data['PrehospTrauma_10'] = $highlight_style;
                break;

            case 12:
                $print_data['PrehospTrauma_11'] = $highlight_style;
                break;
        }

        switch ( $response[0]['StickCause_SysNick'] ) {
            case 'desease':
                $print_data['StickCause_1'] = $highlight_style;
                break;

            case 'uhod':
                $print_data['StickCause_2'] = $highlight_style;
                break;

            case 'karantin':
                $print_data['StickCause_3'] = $highlight_style;
                break;

            case 'abort':
                $print_data['StickCause_4'] = $highlight_style;
                break;

            case 'pregn':
                $print_data['StickCause_5'] = $highlight_style;
                break;

            case 'kurort':
                $print_data['StickCause_6'] = $highlight_style;
                break;
        }

        switch ( $response[0]['EvnStick_Sex'] ) {
            case 1:
                $print_data['EvnStick_Sex1'] = $highlight_style;
                break;

            case 2:
                $print_data['EvnStick_Sex2'] = $highlight_style;
                break;
        }

		return $this->parser->parse($template, $print_data, $ReturnString);
	}


	/**
	 * Печать талона амбулаторного пациента (Башкирия)
	 * Входящие данные: $_GET['EvnPLStom_id']
	 * На выходе: форма для печати талона амбулаторного пациента
	 * Используется: форма редактирования талона амбулаторного пациента
	 */
	function printEvnPLStomUfa() {
		$this->load->library('parser');

		$data = $this->ProcessInputData('printEvnPLStomUfa', true);
		if ($data === false) { return false; }

		// Получаем данные по талону
		$response = $this->dbmodel->getEvnPLStomFieldsUfa($data);

		if ( !is_array($response) || count($response) == 0 ) {
			echo 'Ошибка при получении данных по талону';
			return true;
		}

		$evn_recept_data = array(
			array(
				'ER_EvnRecept_setDate' => '&nbsp;',
				'ER_EvnRecept_Ser' => '&nbsp;',
				'ER_EvnRecept_Num' => '&nbsp;',
				'ER_Diag_Code' => '&nbsp;',
				'ER_Drug_Name' => '&nbsp;',
				'ER_Drug_Dose' => '&nbsp;',
				'ER_EvnRecept_Kolvo' => '&nbsp;'
			),
			array(
				'ER_EvnRecept_setDate' => '&nbsp;',
				'ER_EvnRecept_Ser' => '&nbsp;',
				'ER_EvnRecept_Num' => '&nbsp;',
				'ER_Diag_Code' => '&nbsp;',
				'ER_Drug_Name' => '&nbsp;',
				'ER_Drug_Dose' => '&nbsp;',
				'ER_EvnRecept_Kolvo' => '&nbsp;'
			),
			array(
				'ER_EvnRecept_setDate' => '&nbsp;',
				'ER_EvnRecept_Ser' => '&nbsp;',
				'ER_EvnRecept_Num' => '&nbsp;',
				'ER_Diag_Code' => '&nbsp;',
				'ER_Drug_Name' => '&nbsp;',
				'ER_Drug_Dose' => '&nbsp;',
				'ER_EvnRecept_Kolvo' => '&nbsp;'
			),
			array(
				'ER_EvnRecept_setDate' => '&nbsp;',
				'ER_EvnRecept_Ser' => '&nbsp;',
				'ER_EvnRecept_Num' => '&nbsp;',
				'ER_Diag_Code' => '&nbsp;',
				'ER_Drug_Name' => '&nbsp;',
				'ER_Drug_Dose' => '&nbsp;',
				'ER_EvnRecept_Kolvo' => '&nbsp;'
			)
		);

		$evn_vizit_data = array(
			array(
				'EVPL_EvnVizitPL_setDate1' => '&nbsp;',
				'EVPL_UslugaComplex_Code1' => '&nbsp;',
				'EVPL_EvnVizitPL_UKL1' => '&nbsp;',
				'EVPL_EvnVizitPL_setDate2' => '&nbsp;',
				'EVPL_UslugaComplex_Code2' => '&nbsp;',
				'EVPL_EvnVizitPL_UKL2' => '&nbsp;'
			),
			array(
				'EVPL_EvnVizitPL_setDate1' => '&nbsp;',
				'EVPL_UslugaComplex_Code1' => '&nbsp;',
				'EVPL_EvnVizitPL_UKL1' => '&nbsp;',
				'EVPL_EvnVizitPL_setDate2' => '&nbsp;',
				'EVPL_UslugaComplex_Code2' => '&nbsp;',
				'EVPL_EvnVizitPL_UKL2' => '&nbsp;'
			),
			array(
				'EVPL_EvnVizitPL_setDate1' => '&nbsp;',
				'EVPL_UslugaComplex_Code1' => '&nbsp;',
				'EVPL_EvnVizitPL_UKL1' => '&nbsp;',
				'EVPL_EvnVizitPL_setDate2' => '&nbsp;',
				'EVPL_UslugaComplex_Code2' => '&nbsp;',
				'EVPL_EvnVizitPL_UKL2' => '&nbsp;'
			),
			array(
				'EVPL_EvnVizitPL_setDate1' => '&nbsp;',
				'EVPL_UslugaComplex_Code1' => '&nbsp;',
				'EVPL_EvnVizitPL_UKL1' => '&nbsp;',
				'EVPL_EvnVizitPL_setDate2' => '&nbsp;',
				'EVPL_UslugaComplex_Code2' => '&nbsp;',
				'EVPL_EvnVizitPL_UKL2' => '&nbsp;'
			)
		);

		$response_temp = $this->dbmodel->getEvnReceptData($data);
		if ( is_array($response_temp) ) {
			for ( $i = 0; $i < ( count($response_temp) <= 4 ? count($response_temp) : 4); $i++ ) {
				$evn_recept_data[$i]['ER_EvnRecept_setDate'] = $response_temp[$i]['ER_EvnRecept_setDate'];
				$evn_recept_data[$i]['ER_EvnRecept_Ser'] = $response_temp[$i]['ER_EvnRecept_Ser'];
				$evn_recept_data[$i]['ER_EvnRecept_Num'] = $response_temp[$i]['ER_EvnRecept_Num'];
				$evn_recept_data[$i]['ER_Diag_Code'] = $response_temp[$i]['ER_Diag_Code'];
				$evn_recept_data[$i]['ER_Drug_Name'] = $response_temp[$i]['ER_Drug_Name'];
				$evn_recept_data[$i]['ER_EvnRecept_Kolvo'] = $response_temp[$i]['ER_EvnRecept_Kolvo'];
			}
		}

		$response_temp = $this->dbmodel->getEvnVizitPLStomDataUfa($data);
		if ( is_array($response_temp) ) {
			for ( $i = 0; $i < ( count($response_temp) <= 4 ? count($response_temp) : 4); $i++ ) {
				$evn_vizit_data[$i]['EVPL_EvnVizitPL_setDate1'] = $response_temp[$i]['EvnVizitPL_setDate'];
				$evn_vizit_data[$i]['EVPL_UslugaComplex_Code1'] = $response_temp[$i]['UslugaComplex_Code'];
				$evn_vizit_data[$i]['EVPL_EvnVizitPL_UKL1'] = $response_temp[$i]['EvnVizitPL_UKL'];
			}

			if ( count($response_temp) > 4 ) {
				for ( $i = 0; $i < ( count($response_temp) - 4 <= 4 ? count($response_temp) - 4 : 4); $i++ ) {
					$evn_vizit_data[$i]['EVPL_EvnVizitPL_setDate2'] = $response_temp[$i]['EvnVizitPL_setDate'];
					$evn_vizit_data[$i]['EVPL_UslugaComplex_Code2'] = $response_temp[$i]['UslugaComplex_Code'];
					$evn_vizit_data[$i]['EVPL_EvnVizitPL_UKL2'] = $response_temp[$i]['EvnVizitPL_UKL'];
				}
			}
		}

		$highlight_style = 'font-weight: bold;';
		$template = 'evn_pl_template_list_a4_ufa';

		$print_data = array(
			'DeseaseTypeSop_1' => '',
			'DeseaseTypeSop_2' => '',
			'DiagAgg_Code' => returnValidHTMLString($response[0]['DiagAgg_Code']),
			'DiagSop_Code' => returnValidHTMLString($response[0]['DiagSop_Code']),
			'DirectType_1' => '',
			'DirectType_2' => '',
			'DirectType_3' => '',
			'DirectType_31' => '',
			'EvnPL_setDate' => returnValidHTMLString($response[0]['EvnPLStom_setDate']),
			'EvnPLTemplateTitle' => 'Печать талона амбулаторного пациента',
			'EvnReceptData' => $evn_recept_data,
			'EvnStick_Age' => returnValidHTMLString($response[0]['EvnStick_Age']),
			'EvnStick_begDate' => returnValidHTMLString($response[0]['EvnStick_begDate']),
			'EvnStick_endDate' => returnValidHTMLString($response[0]['EvnStick_endDate']),
			'EvnStick_Sex1' => '',
			'EvnStick_Sex2' => '',
			'Person_Birthday' => returnValidHTMLString($response[0]['Person_Birthday']),
			'Sex_Name' => returnValidHTMLString($response[0]['Sex_Name']),
			'Lpu_Name' => returnValidHTMLString($response[0]['Lpu_Name']),
			'EvnVizitData' => $evn_vizit_data,
			'FinalDeseaseType_0' => '',
			'FinalDeseaseType_1' => '',
			'FinalDeseaseType_2' => '',
			'FinalDeseaseType_3' => '',
			'FinalDeseaseType_4' => '',
			'FinalDeseaseType_5' => '',
			'FinalDeseaseType_6' => '',
			'FinalDiag_Code' => returnValidHTMLString($response[0]['FinalDiag_Code']),
			'LpuRegion_Name' => returnValidHTMLString($response[0]['LpuRegion_Name']),
			'LpuSectionProfile_Code' => returnValidHTMLString($response[0]['LpuSectionProfile_Code']),
			'LpuSectionProfile_Name' => returnValidHTMLString($response[0]['LpuSectionProfile_Name']),
			'MedPersonal_Fio' => returnValidHTMLString($response[0]['MedPersonal_Fio']),
			'OrgJob_Name' => returnValidHTMLString($response[0]['OrgJob_Name']),
			'OrgSmo_Name' => returnValidHTMLString($response[0]['OrgSmo_Name']),
			'PayType_Name' => returnValidHTMLString($response[0]['PayType_Name']),
			'Person_Address' => returnValidHTMLString($response[0]['PAddress_Name']),
			'Person_Docum' => returnValidHTMLString($response[0]['Document_Ser']) . ' ' . returnValidHTMLString($response[0]['Document_Num']) . ' ' . returnValidHTMLString($response[0]['Document_begDate']),
			'Person_Fio' => returnValidHTMLString($response[0]['Person_Fio']),
			'Person_INN' => returnValidHTMLString($response[0]['Person_INN']),
			'Person_Snils' => returnValidHTMLString($response[0]['Person_Snils']),
			'PersonCard_Code' => returnValidHTMLString($response[0]['PersonCard_Code']),
			'Polis_begDate' => returnValidHTMLString($response[0]['Polis_begDate']),
			'Polis_endDate' => returnValidHTMLString($response[0]['Polis_endDate']),
			'Polis_Num' => returnValidHTMLString($response[0]['Polis_Num']),
			'Polis_Ser' => returnValidHTMLString($response[0]['Polis_Ser']),
			'PrehospDiag_Code' => returnValidHTMLString($response[0]['PrehospDiag_Code']),
			'PrehospDiag_regDate' => returnValidHTMLString($response[0]['PrehospDiag_regDate']),
			'PrehospTrauma_1' => '',
			'PrehospTrauma_2' => '',
			'PrehospTrauma_21' => '',
			'PrehospTrauma_3' => '',
			'PrehospTrauma_4' => '',
			'PrehospTrauma_6' => '',
			'PrehospTrauma_7' => '',
			'PrehospTrauma_8' => '',
			'PrehospTrauma_81' => '',
			'PrehospTrauma_9' => '',
			'PrehospTrauma_10' => '',
			'PrehospTrauma_11' => '',
			'ResultClass_1' => '',
			'ResultClass_2' => '',
			'ResultClass_3' => '',
			'ResultClass_4' => '',
			'ResultClass_5' => '',
			'ServiceType_Name' => returnValidHTMLString($response[0]['ServiceType_Name']),
			'SocStatus_Name' => returnValidHTMLString($response[0]['SocStatus_Name']),
			'StickCause_1' => '',
			'StickCause_2' => '',
			'StickCause_3' => '',
			'StickCause_4' => '',
			'StickCause_5' => '',
			'StickCause_6' => '',
			'StickType_1' => '',
			'StickType_11' => '',
			'StickType_2' => '',
			'StickType_3' => '',
			'VizitType_1' => '',
			'VizitType_2' => '',
			'VizitType_3' => '',
			'VizitType_4' => '',
			'VizitType_41' => '',
			'VizitType_5' => '',
			'PersonDeputy_Fio' => returnValidHTMLString($response[0]['PersonDeputy_Fio'])
		);

		switch ( $response[0]['VizitType_SysNick'] ) {
			case 'desease':
				$print_data['VizitType_1'] = $highlight_style;
			break;

			case 'prof':
				$print_data['VizitType_2'] = $highlight_style;
			break;

			case 'patron':
				$print_data['VizitType_3'] = $highlight_style;
			break;

			case 'disp':
				$print_data['VizitType_4'] = $highlight_style;
			break;

			case 'sert':
				$print_data['VizitType_41'] = $highlight_style;
			break;

			case 'rehab':
				$print_data['VizitType_5'] = $highlight_style;
			break;
		}

		switch ( $response[0]['DirectType_SysNick'] ) {
			case 'stac':
				$print_data['DirectType_1'] = $highlight_style;
			break;

			case 'dstac':
				$print_data['DirectType_2'] = $highlight_style;
			break;

			case 'kons':
				$print_data['DirectType_3'] = $highlight_style;
			break;

			case 'other':
				$print_data['DirectType_31'] = $highlight_style;
			break;
		}

		switch ( $response[0]['ResultClass_SysNick'] ) {
			case 'vizdor':
				$print_data['ResultClass_1'] = $highlight_style;
			break;

			case 'better':
				$print_data['ResultClass_2'] = $highlight_style;
			break;

			case 'dinam':
				$print_data['ResultClass_3'] = $highlight_style;
			break;

			case 'worse':
				$print_data['ResultClass_4'] = $highlight_style;
			break;

			case 'die':
				$print_data['ResultClass_5'] = $highlight_style;
			break;
		}

		switch ( $response[0]['StickType_SysNick'] ) {
			case 'spravka':
				$print_data['StickType_1'] = $highlight_style;
			break;

			case 'blist':
				$print_data['StickType_11'] = $highlight_style;
			break;

			case 'dinam':
				$print_data['StickType_2'] = $highlight_style;
			break;

			case 'worse':
				$print_data['StickType_3'] = $highlight_style;
			break;
		}

		switch ( $response[0]['FinalDeseaseType_SysNick'] ) {
			case 'good':
				$print_data['FinalDeseaseType_0'] = $highlight_style;
			break;

			case 'sharp':
				$print_data['FinalDeseaseType_1'] = $highlight_style;
			break;

			case 'hrnew':
				$print_data['FinalDeseaseType_2'] = $highlight_style;
			break;

			case 'hrold':
				$print_data['FinalDeseaseType_3'] = $highlight_style;
			break;

			case 'hrobostr':
				$print_data['FinalDeseaseType_4'] = $highlight_style;
			break;

			case 'otrav':
				$print_data['FinalDeseaseType_5'] = $highlight_style;
			break;

			case 'trauma':
				$print_data['FinalDeseaseType_6'] = $highlight_style;
			break;
		}

		switch ( $response[0]['DeseaseTypeSop_SysNick'] ) {
			case 'sharp':
			case 'hrnew':
				$print_data['DeseaseTypeSop_1'] = $highlight_style;
			break;

			case 'hrold':
				$print_data['DeseaseTypeSop_2'] = $highlight_style;
			break;
		}

		switch ( $response[0]['PrehospTrauma_Code'] ) {
			case 1:
				$print_data['PrehospTrauma_1'] = $highlight_style;
			break;

			case 2:
				$print_data['PrehospTrauma_2'] = $highlight_style;
			break;

			case 3:
				$print_data['PrehospTrauma_21'] = $highlight_style;
			break;

			case 4:
				$print_data['PrehospTrauma_3'] = $highlight_style;
			break;

			case 5:
				$print_data['PrehospTrauma_4'] = $highlight_style;
			break;

			case 6:
				$print_data['PrehospTrauma_6'] = $highlight_style;
			break;

			case 7:
				$print_data['PrehospTrauma_7'] = $highlight_style;
			break;

			case 8:
				$print_data['PrehospTrauma_8'] = $highlight_style;
			break;

			case 9:
				$print_data['PrehospTrauma_81'] = $highlight_style;
			break;

			case 10:
				$print_data['PrehospTrauma_9'] = $highlight_style;
			break;

			case 11:
				$print_data['PrehospTrauma_10'] = $highlight_style;
			break;

			case 12:
				$print_data['PrehospTrauma_11'] = $highlight_style;
			break;
		}

		switch ( $response[0]['StickCause_SysNick'] ) {
			case 'desease':
				$print_data['StickCause_1'] = $highlight_style;
			break;

			case 'uhod':
				$print_data['StickCause_2'] = $highlight_style;
			break;

			case 'karantin':
				$print_data['StickCause_3'] = $highlight_style;
			break;

			case 'abort':
				$print_data['StickCause_4'] = $highlight_style;
			break;

			case 'pregn':
				$print_data['StickCause_5'] = $highlight_style;
			break;

			case 'kurort':
				$print_data['StickCause_6'] = $highlight_style;
			break;
		}

		switch ( $response[0]['EvnStick_Sex'] ) {
			case 1:
				$print_data['EvnStick_Sex1'] = $highlight_style;
			break;

			case 2:
				$print_data['EvnStick_Sex2'] = $highlight_style;
			break;
		}

		return $this->parser->parse($template, $print_data);
	}

	/**
	 * Создание талона и посещения с данными по умолчанию
	 */
	function createEvnPLStom() {
		$this->load->model('EvnVizitPLStom_model');
		$this->inputRules['createEvnPLStom'] = array_merge(array(
				array(
					'field' => 'MedicalCareKind_vid',
					'label' => 'Вид мед. помощи',
					'rules' => '',
					'type' => 'id'
				),
				array('field' => 'EvnDiagPLStom_ids', 'label' => 'Идентификаторы заболеваний', 'rules' => '', 'type' => 'string'),
				array('field' => 'copyEvnDiagPLStom', 'label' => 'флаг копирования заболевания', 'rules' => '', 'type' => 'int')
			),
			$this->dbmodel->getInputRules(swModel::SCENARIO_DO_SAVE),
			$this->EvnVizitPLStom_model->getInputRules(swModel::SCENARIO_DO_SAVE));

		if (getRegionNick() == 'kz') {
			// делаем поле не обязательным
			$this->inputRules['createEvnPLStom']['PayType_id']['rules'] = 'trim';
		}
		$data = $this->ProcessInputData('createEvnPLStom', true);
		if ( $data === false ) { return false; }
		if (!empty($data['isAutoCreate'])) {
			$data['EvnPLStom_IsFinish'] = 1;
			$data['Diag_id'] = null;
			$data['scenario'] = swModel::SCENARIO_AUTO_CREATE;
		} else {
			$data['scenario'] = swModel::SCENARIO_DO_SAVE;
		}
		$className = get_class($this->dbmodel);
		$instance = new $className();
		$instance->applyData($data);
		$instance->setEvnVizitInputData($data);
		$response =  $instance->doSave();
		// Сохранение заболеваний в посещении
		if(!empty($response['EvnVizitPLStom_id']) && !empty($response['EvnPLStom_id']) && !empty($data['copyEvnDiagPLStom'])){
			$this->load->model('EvnDiagPLStom_model');
			$evnDiagPLStom_ids = array();
			if(strpos($data['EvnDiagPLStom_ids'],',') !== false){
				$evnDiagPLStom_ids = explode(',',$data['EvnDiagPLStom_ids']);
			} else {
				$evnDiagPLStom_ids[0] = $data['EvnDiagPLStom_ids'];
			}
			if(count($evnDiagPLStom_ids) > 0){
				$sp = getSessionParams();
				foreach ($evnDiagPLStom_ids as $value) {
					$sp['EvnDiagPLStom_id'] = $value;
					$resp = $this->EvnDiagPLStom_model->loadEvnDiagPLStomEditForm($sp);
					if($resp && !empty($resp[0]) && !empty($resp[0]['EvnDiagPLStom_id'])){
						$evnDiagPLStomData = $resp[0];
						$evnDiagPLStomData['EvnDiagPLStom_id'] = 0;
						$evnDiagPLStomData['EvnDiagPLStom_pid'] = $response['EvnVizitPLStom_id'];
						$evnDiagPLStomData['EvnDiagPLStom_rid'] = $response['EvnPLStom_id'];
						$evnDiagPLStomData['EvnDiagPLStom_setDate'] = date('Y-m-d');
						$evnDiagPLStomData['EvnDiagPLStom_disDate'] = null;
						$evnDiagPLStomData['EvnDiagPLStom_IsClosed'] = null;
						$evnDiagPLStomData['EvnDiagPLStom_IsZNO'] = null;
						$evnDiagPLStomData['Diag_spid'] = null;
						$evnDiagPLStomData['PainIntensity_id'] = null;
						$evnDiagPLStomData['ignoreCheckKSGPeriod'] = 1;
						$evnDiagPLStomData['ignoreEmptyKsg'] = 1;
						$evnDiagPLStomData['ignoreCheckTNM'] = 1;
						$evnDiagPLStomData['ignoreUetSumInNonMorbusCheck'] = 1;
						$evnDiagPLStomData['pmUser_id'] = $data['pmUser_id'];
						$evnDiagPLStomData['isAutoCreate'] = 1;
						$evnDiagPLStomData['Mes_id'] = null;
						$res = $this->EvnDiagPLStom_model->saveEvnDiagPLStom($evnDiagPLStomData);

						$sp['EvnDiagPLStomSop_pid'] = $value;
						$respSop = $this->EvnDiagPLStom_model->loadEvnDiagPLStomSopGrid($sp);
						if($respSop && is_array($respSop) && count($respSop) > 0 && $res && !empty($res[0]) && !empty($res[0]['EvnDiagPLStom_id'])){
							foreach ($respSop as $val) {
								if(!empty($val['EvnDiagPLStomSop_id'])){
									$sp['EvnDiagPLStomSop_id'] = $val['EvnDiagPLStomSop_id'];
									$sop = $this->EvnDiagPLStom_model->loadEvnDiagPLStomSopEditForm($sp);
									if($sop && !empty($sop[0]) && !empty($sop[0]['EvnDiagPLStomSop_id'])){
										$evnDiagPLStomSopData = $sop[0];
										$evnDiagPLStomSopData['EvnDiagPLStomSop_id'] = null;
										$evnDiagPLStomSopData['EvnDiagPLStomSop_pid'] = $res[0]['EvnDiagPLStom_id'];
										$evnDiagPLStomSopData['pmUser_id'] = $data['pmUser_id'];
										$evnDiagPLStomSopData['EvnDiagPLStomSop_setDate'] = date('Y-m-d',strtotime($evnDiagPLStomSopData['EvnDiagPLStomSop_setDate']));
										$resSop = $this->EvnDiagPLStom_model->saveEvnDiagPLStomSop($evnDiagPLStomSopData);
									}
								}
							}
						}
					}
				}
				
			}
		}
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении талона амбулаторного пациента')
			->ReturnData();
		return true;
	}

	/**
	 * Сохранение талона амбулаторного пациента
	 * Входящие данные: ...
	 * На выходе: JSON-строка
	 * Используется: новая форма редактирования талона амбулаторного пациента
	 */
	function saveEvnPLStom() {
		$this->inputRules['saveEvnPLStom'] = $this->dbmodel->getInputRules(swModel::SCENARIO_DO_SAVE);
		$data = $this->ProcessInputData('saveEvnPLStom', true);
		if ( $data === false ) { return false; }
		if (empty($data['isAutoCreate'])) {
			$data['scenario'] = swModel::SCENARIO_DO_SAVE;
		} else {
			// если с формы пришло EvnPLStom_IsFinish=2,
			// то при сохранении ТАП талон будет закрыт
			// при отмене сохранения будет удален открытый ТАП
			$data['EvnPLStom_IsFinish'] = 1;
			$data['scenario'] = swModel::SCENARIO_AUTO_CREATE;
		}
		$className = get_class($this->dbmodel);
		$instance = new $className();
		$instance->applyData($data);
		if (!$instance->isNewRecord && !empty($data['EvnDirection_id']) && !empty($instance->evnVizitList)) {
			$first_EvnVizitPL_id = 0;
			foreach ($instance->evnVizitList as $id => $row) {
				$first_EvnVizitPL_id = $id;
				break;
			}
			if ($first_EvnVizitPL_id && empty($instance->evnVizitList[$first_EvnVizitPL_id]['EvnDirection_id'])) {
				$instance->setEvnVizitInputData(array(
					'session' => $data['session'],
					'scenario' => $data['scenario'],
					'EvnVizitPLStom_id' => $first_EvnVizitPL_id,
					'EvnDirection_vid' => $data['EvnDirection_id'],
					'ignore_vizit_kvs_control' => 1
				));
			}
		}
		$response = $instance->doSave();
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении талона амбулаторного пациента')
			->ReturnData();
		return true;
	}

	/**
	 * Проверки и получение данных перед открытием формы добавления посещения из ЭМК
	 */
	function checkAddEvnVizit()
	{
		$this->inputRules['checkAddEvnVizit'] = $this->dbmodel->getInputRules('checkAddEvnVizit');
		$data = $this->ProcessInputData('checkAddEvnVizit', true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->checkAddEvnVizit($data);
		$this->ProcessModelSave($response, false, 'Ошибка')->ReturnData();
		return true;
	}

	/**
	 * Сохранение посещения пациентом поликлиники
	 * Входящие данные: ...
	 * На выходе: JSON-строка
	 * Используется: новая форма редактирования посещения пациентом поликлиники
	 */
	function saveEvnVizitPLStom() {
		$this->load->model('EvnVizitPLStom_model');
		$this->inputRules['saveEvnVizitPLStom'] = $this->EvnVizitPLStom_model->getInputRules(swModel::SCENARIO_DO_SAVE);
		$data = $this->ProcessInputData('saveEvnVizitPLStom', true);
		if ( $data === false ) { return false; }
		if (empty($data['isAutoCreate'])) {
			$data['scenario'] = swModel::SCENARIO_DO_SAVE;
		} else {
			$data['scenario'] = swModel::SCENARIO_AUTO_CREATE;
		}
		$response = $this->EvnVizitPLStom_model->doSave($data);

		//@todo возможно костыль, но через setParams я не понял как делать
		$postData = $_POST;
		if(!empty($response['EvnVizitPLStom_id']) && !empty($_POST['BitePersonType_id']) && !empty($data['Person_id'])){
			$data['BitePersonType_id'] = $_POST['BitePersonType_id'];
			$data['EvnVizitPLStom_id'] = $response['EvnVizitPLStom_id'];
			$data['BitePersonData_setDate'] = $data['EvnVizitPLStom_setDate'];
			$resp = $this->EvnVizitPLStom_model->saveBitePersonType($data);
		}

		$this->ProcessModelSave($response, false, 'Ошибка при сохранении посещения пациентом стоматологической поликлиники')
			->ReturnData();
		return true;
	}

	/**
	 *  Сохранение прикуса
	 */
	function saveBitePersonType() {
		$data = $this->ProcessInputData('saveBitePersonType', true);
		if ($data === false) { return false; }
		$this->load->model('EvnVizitPLStom_model');
		$response = $this->EvnVizitPLStom_model->saveBitePersonType($data);
		$this->ProcessModelSave($response,true,'Ошибка сохранения прикуса')->ReturnData();

		return true;
	}

	/**
	 * Получение даты ТАП (используется для печати ТАП)
	 */
	function getEvnPLDate() {
		$data = $this->ProcessInputData('getEvnPLDate', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->getEvnPLDate($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}

	/**
	 * Получение текущего прикуса
	 */
	function getCurrentBitePersonData() {
		$data = $this->ProcessInputData('getCurrentBitePersonData', true);
		if ( $data === false ) { return false; }
		$this->load->model('EvnVizitPLStom_model');
		$response = $this->EvnVizitPLStom_model->getCurrentBitePersonData($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}

	/**
	 * Получение данных по заболеваниям в посещении для проверки перед копированием посещения 
	 */
	function getEvnDiagPLStom() {
		$data = $this->ProcessInputData('getEvnDiagPLStom', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->getEvnDiagPLStom($data);
		$this->ProcessModelList($response, true)->ReturnData();

		return true;
	}

	/**
	 * Добавление нового посещения из ЭМК
	 */
	function addEvnVizitPLStom()
	{
		$data = $this->ProcessInputData('addEvnVizitPLStom', true);
		if ( $data === false ) { return false; }

		$this->load->model('EvnVizitPLStom_model');
		$response = $this->EvnVizitPLStom_model->addEvnVizitPLStom($data);
		$this->ProcessModelSave($response, false, 'Ошибка при сохранении посещения пациентом поликлиники')->ReturnData();

		return true;
	}

	/**
	 * Сохранение посещения из ЭМК
	 * На выходе: JSON-строка
	 * Используется: стандартная форма редактирования посещения пациентом поликлиники
	 * @return bool
	 */
	function saveEvnVizitFromEMK()
	{
		$data = $this->ProcessInputData('saveEvnVizitFromEMK', false);
		if ( $data === false ) { return false; }
		$session = getSessionParams();
		$data['session'] = $session['session'];
		$data['Lpu_id'] = $session['Lpu_id'];
		$data['pmUser_id'] = $session['pmUser_id'];

		$this->load->model('EvnVizitPLStom_model');
		$data['scenario'] = swModel::SCENARIO_DO_SAVE;
		$data['vizit_kvs_control_check'] = 1;
		$data['UslugaComplexTariff_uid'] = $data['UslugaComplexTariff_id'];
		$response = $this->EvnVizitPLStom_model->doSave($data);
		$this->ProcessModelSave($response, false, 'Ошибка при сохранении посещения пациентом поликлиники')->ReturnData();

		return true;
	}

	/**
	 * Завершение случая лечения
	 * На выходе: JSON-строка
	 * Используется: стандартная форма редактирования посещения пациентом поликлиники
	 * @return bool
	 */
	function saveEvnPLStomFinishForm()
	{
		$data = $this->ProcessInputData('saveEvnPLStomFinishForm', false);
		if ( $data === false ) { return false; }

		$session = getSessionParams();
		$data['session'] = $session['session'];
		$data['Lpu_id'] = $session['Lpu_id'];
		$data['pmUser_id'] = $session['pmUser_id'];

		$data['scenario'] = swModel::SCENARIO_DO_SAVE;
		$response = $this->dbmodel->doSave($data);
		$this->ProcessModelSave($response, false, 'Ошибка при сохранении завершения случая лечения')->ReturnData();

		return true;
	}

	/**
	 * Загрузка формы завершения случая лечения
	 */
	function loadEvnPLStomFinishForm()
	{
		$data = $this->ProcessInputData('loadEvnPLStomFinishForm', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadEvnPLStomFinishForm($data);
		$this->ProcessModelList($response, false, true)->ReturnData();

		return true;
	}
}

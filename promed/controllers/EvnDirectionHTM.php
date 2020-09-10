<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnDirectionHTM - контроллер для с работы с направлениями на ВМП
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Htm
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			25.07.2014
 *
 * @property EvnDirectionHTM_model dbmodel
 */

class EvnDirectionHTM extends swController {
	protected  $inputRules = array(
		'getEvnDirectionHTMNumber' => array(
		
		),
		'loadInfoForEvnDirectionHTM' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор МО',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadEvnDirectionHTMForm' => array(
			array(
				'field' => 'EvnDirectionHTM_id',
				'label' => 'Идентификатор направления на ВМП',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadEvnDirectionHTMGrid' => array(
			array(
				'field' => 'MedService_id',
				'label' => 'Идентификатор службы',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Person_SurName',
				'label' => 'Фамилия',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Search_FirName',
				'label' => 'Имя',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Search_SecName',
				'label' => 'Отчество',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Search_BirthDay',
				'label' => 'День роджения',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'begDate',
				'label' => 'Начало периода поиска',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'endDate',
				'label' => 'Окончание периода поиска',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'HTMFinance_id',
				'label' => 'Вид оплаты',
				'rules' => '',
				'type' => 'id'
			),
		),
		'loadEvnDirectionHTMRegistry' => array(
			array(
				'field' => 'EvnStatus_id',
				'label' => 'Статус',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'HTMFinance_id',
				'label' => 'Источник финансирования',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'HTMedicalCareType_id',
				'label' => 'Вид ВМП',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'HTMedicalCareClass_id',
				'label' => 'Метод ВМП',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'Направившая МО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirectionHTM_Num',
				'label' => 'Номер направления',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'HTMRegion_id',
				'label' => 'Регион, куда направлен',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuHTM_id',
				'label' => 'МО, куда направлен',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_SurName',
				'label' => 'Фамилия',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_FirName',
				'label' => 'Имя',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_SecName',
				'label' => 'Отчество',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_BirthDay',
				'label' => 'ДР',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'begDate',
				'label' => 'Начало периода поиска',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'endDate',
				'label' => 'Окончание периода поиска',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'start',
				'label' => 'Начало страницы',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'limit',
				'label' => 'Размер страницы',
				'rules' => '',
				'type' => 'int'
			)
		),
		'saveEvnDirectionHTM' => array(
			array(
				'field' => 'EvnDirectionHTM_id',
				'label' => 'Идентификатор направления на ВМП',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirectionHTM_pid',
				'label' => 'Идентификатор направления на ВМП',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirection_pid',
				'label' => 'Идентификатор случая в ЭМК',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnLink_id',
				'label' => 'EvnLink',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirectionHTM_Num',
				'label' => 'Номер направления',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Server_id',
				'label' => 'Идентификатор сервера',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PersonEvn_id',
				'label' => 'Идентификатор периодики человека',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PrivilegeType_id',
				'label' => 'Идентификатор категории льготы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'HTMSocGroup_id',
				'label' => 'Идентификатор социальной группы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirectionHTM_setDate',
				'label' => 'Дата оформления талона',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnDirectionHTM_VKProtocolNum',
				'label' => 'Номер протокола ВК',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnDirectionHTM_VKProtocolDate',
				'label' => 'Дата протокола ВК',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnDirectionHTM_IsHTM',
				'label' => 'Идентификатор обращения пациента за ВМП',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'HTMFinance_id',
				'label' => 'Идентификатор источника финансировнаия',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'HTMOrgDirect_id',
				'label' => 'Идентификатор организации для направления на ВМП',
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
				'field' => 'MedStaffFact_id',
				'label' => 'Идентификатор направившего врача',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Идентификатор направившего врача',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор направившего МО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Идентификатор направившего отделения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_did',
				'label' => 'Идентификатор МО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuUnit_did',
				'label' => 'Идентификатор группы отделений',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_did',
				'label' => 'Идентификатор группы отделений',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'TreatmentType_id',
				'label' => 'Тип предстоящего лечения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'TimetableMedService_id',
				'label' => 'Бирка раписания службы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'withCreateDirection',
				'label' => 'Надо ли создавать направление',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSectionProfile_id',
				'label' => 'Профиль направления',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirectionHTM_planDate',
				'label' => 'Планируемая дата поступления / госпитализации',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'Diag_id',
				'label' => 'Диагноз направления',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuHTM_id',
				'label' => 'МО, куда направляется пациент',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirectionHTM_directDate',
				'label' => 'Дата направления',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnDirectionHTM_TalonNum',
				'label' => 'Номер талона',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'HTMedicalCareType_id',
				'label' => 'Вид ВМП',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PrehospType_did',
				'label' => 'Вид госпитализации',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'HTMedicalCareClass_id',
				'label' => 'Метод ВМП',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonInfo_Email',
				'label' => 'Адрес электронной почты пациента',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnStatus_id',
				'label' => 'Статус события',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Org_id',
				'label' => 'МО, направившая',
				'rules' => '',
				'type' => 'id'
			),
		),
		'exportDirectionHTM' => array(
			array(
				'field' => 'Month',
				'label' => 'Месяц',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Year',
				'label' => 'Год',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'HTMFinance_id',
				'label' => 'Идентификатор источника финансировнаия',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'setEvnDirectionHTMStatus' => array(
			array(
				'field' => 'EvnDirectionHTM_id',
				'label' => 'Идентификатор направления на ВМП',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStatus_id',
				'label' => 'Идентификатор статуса',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStatusCause_id',
				'label' => 'Идентификатор причины смены',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStatusHistory_Cause',
				'label' => 'Комментарий',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'pmUser_id',
				'label' => 'Идентификатор пользователя',
				'rules' => 'required',
				'type' => 'id'
			)
		)
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('EvnDirectionHTM_model', 'dbmodel');
	}

	/**
	 * Получение справочной информации для отображения в направлении на ВМП
	 */
	function loadInfoForEvnDirectionHTM() {
		$data = $this->ProcessInputData('loadInfoForEvnDirectionHTM', true);
		if ($data === false) { return false; }

		$response = array('data' => array(), 'success' => true);

		$resp = $this->dbmodel->loadPersonInfoForEvnDirectionHTM($data);
		if ($resp && is_array($resp)) {
			$response['data'] = $resp[0];
		} else {
			$this->ReturnError('Ошибка при получении данных о пациенте');
			return false;
		}

		$resp = $this->dbmodel->loadOrgInfoForEvnDirectionHTM($data);
		if ($resp && is_array($resp)) {
			$response['data'] = array_merge($response['data'], $resp[0]);
		} else {
			$this->ReturnError('Ошибка при получении данных об ОУЗ');
			return false;
		}

		$this->ProcessModelSave($response)->ReturnData();
	}

	/**
	 * Получение данных направления на ВМП для редактирования
	 */
	function loadEvnDirectionHTMForm() {
		$data = $this->ProcessInputData('loadEvnDirectionHTMForm', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadEvnDirectionHTMForm($data);

		$this->ProcessModelList($response)->ReturnData();
	}

	/**
	 * Получение списка направлений на ВМП (для АРМ ВМП)
	 */
	function loadEvnDirectionHTMGrid() {
		$data = $this->ProcessInputData('loadEvnDirectionHTMGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadEvnDirectionHTMGrid($data);

		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	* Получение списка направлений на ВМП (для регистра ВМП)
	*/
	function loadEvnDirectionHTMRegistry()
	{
		$data = $this->ProcessInputData('loadEvnDirectionHTMRegistry', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadEvnDirectionHTMRegistry($data);
		
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	 * Сохранение направления на ВМП
	 */
	function saveEvnDirectionHTM() {
		$data = $this->ProcessInputData('saveEvnDirectionHTM', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveEvnDirectionHTM($data);

		$this->ProcessModelSave($response)->ReturnData();
	}

	/**
	 * Экспорт направлений на ВМП
	 */
	function exportDirectionHTM() {
		$data = $this->ProcessInputData('exportDirectionHTM', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->exportDirectionHTM($data);

		$this->ProcessModelSave($response)->ReturnData();
	}
	
	/**
	 * Получение номера направления на ВМП
	 * Входящие данные: нет
	 * На выходе: JSON-строка
	 * Используется: форма редактирования направления
	 */
	function getEvnDirectionHTMNumber() {
		$data = $this->ProcessInputData('getEvnDirectionHTMNumber', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getEvnDirectionHTMNumber($data);
		$this->ProcessModelSave($response, true, 'Ошибка получения номера направления')->ReturnData();

		return true;
	}

	/**
	 * Изменение статуса направления на ВМП
	 */
	function setEvnDirectionHTMStatus() {
		$data = $this->ProcessInputData('setEvnDirectionHTMStatus', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->setEvnDirectionHTMStatus($data);

		$this->ProcessModelSave($response)->ReturnData();
	}
}

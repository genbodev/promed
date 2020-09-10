<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* EvnPLDispScreenChild - контроллер для управления профосмотрами
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package			DLO
* @access			public
* @copyright		Copyright (c) 2013 Swan Ltd.
* @author			Dmitry Vlasenko
* @originalauthor	Petukhov Ivan aka Lich (megatherion@list.ru) / Stas Bykov aka Savage (savage1981@gmail.com)
* @version			20.06.2013
*/

class EvnPLDispScreenChild extends swController
{
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->load->database();
		$this->load->model('EvnPLDispScreenChild_model', 'dbmodel');
		
		$this->inputRules = array(
			'saveDopDispQuestionGrid' => array(
				array(
					'field' => 'EvnPLDisp_id',
					'label' => 'Идентификатор талона по срининговому исследованию',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'DopDispQuestionData',
					'label' => 'Данные грида с анкетированием',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'NeedCalculation',
					'label' => 'Необходимость произвести расчёт',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DopDispQuestion_setDate',
					'label' => 'Дата',
					'rules' => '',
					'type' => 'date'
				)
			),
			'loadEvnPLDispScreenChildEditForm' => array(
				array(
					'field' => 'EvnPLDispScreenChild_id',
					'label' => 'Идентификатор талона по срининговому исследованию',
					'rules' => 'trim|required',
					'type' => 'id'
				)
			),
			'deleteEvnPLDispScreenChild' => array(
				array(
					'field' => 'EvnPLDispScreenChild_id',
					'label' => 'Идентификатор талона по срининговому исследованию',
					'rules' => 'trim|required',
					'type' => 'id'
				)
			),
			'checkAddAvailability' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'checkIfEvnPLDispScreenChildExists' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => 'trim|required',
					'type' => 'id'
				)
			),
			'checkIfEvnPLDispScreenChildExistsInTwoYear' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => 'trim|required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDisp_consDate',
					'label' => 'Дата согласия',
					'rules' => 'required',
					'type' => 'date'
				)
			),
			'saveEvnUslugaDispDop' => array(
				array(
					'field' => 'results',
					'label' => 'Результаты',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'EvnUslugaDispDop_id',
					'label' => 'Идентификатор услуги',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaDispDop_pid',
					'label' => 'Идентификатор талона скринигового исследования',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'SurveyType_id',
					'label' => 'Идентификатор осмотра/исследования',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnDirection_id',
					'label' => 'Идентификатор направления',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Server_id',
					'label' => 'Идентификатор талона по доп. диспансеризации',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'PersonEvn_id',
					'label' => 'Идентификатор талона по доп. диспансеризации',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'VizitKind_id',
					'label' => 'Вид посещения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaDispDop_ExamPlace',
					'label' => 'Место проведения',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'EvnUslugaDispDop_setDate',
					'label' => 'Дата',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'EvnUslugaDispDop_setTime',
					'label' => 'Время',
					'rules' => 'trim',
					'type' => 'time'
				),
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Услуга',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaDispDop_didDate',
					'label' => 'Дата начала выполнения',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'EvnUslugaDispDop_didTime',
					'label' => 'Время начала выполнения',
					'rules' => '',
					'type' => 'time'
				),
				array(
					'field' => 'EvnUslugaDispDop_disDate',
					'label' => 'Дата окончания выполнения',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'EvnUslugaDispDop_disTime',
					'label' => 'Время окончания выполнения',
					'rules' => '',
					'type' => 'time'
				),
				array(
					'field' => 'ExaminationPlace_id',
					'label' => 'Место выполнения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Отделение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_uid',
					'label' => 'МО',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSectionProfile_id',
					'label' => 'Профиль',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedSpecOms_id',
					'label' => 'Специальность',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Врач',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Рабочее место врача',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Diag_id',
					'label' => 'Диагноз',
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
					'field' => 'DeseaseStage',
					'label' => 'Стадия',
					'rules' => '',
					'type'	=> 'id'
				),
				array(
					'field' => 'DispClass_id',
					'label' => 'Идентификатор этапа',
					'rules' => '',
					'type'	=> 'id'
				),
				// куча различных полей результатов
				array(
					'label' => 'Патология',
					'field' => 'electro_cardio_gramm',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'label' => 'Положительный результат',
					'field' => 'gemokult_test',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'label' => 'Уровень холестерина (ммоль/л)',
					'field' => 'total_cholesterol',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'label' => 'Уровень триглицеридов (ммоль/л)',
					'field' => 'bio_blood_triglycerid',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'label' => 'Глюкоза (ммоль/л)',
					'field' => 'glucose',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'label' => 'Результат',
					'field' => 'pap_test',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'label' => 'Результат',
					'field' => 'res_mammo_graph',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'label' => 'Тип измерения',
					'field' => 'pressure_measure',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'label' => 'Результат, левый глаз (мм рт.ст.)',
					'field' => 'eye_pressure_left',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'label' => 'Результат, правый глаз (мм рт.ст.)',
					'field' => 'eye_pressure_right',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'label' => 'Результат',
					'field' => 'survey_result',
					'rules' => 'trim',
					'type' => 'string'
				)
			),
			'loadEvnUslugaDispDopGrid' => array(
				array(
					'field' => 'EvnPLDispScreenChild_id',
					'label' => 'Идентификатор талона по срининговому исследованию',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'ScreenType_id',
					'label' => 'Целевая категория',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'AgeGroupDisp_id',
					'label' => 'Возрастная группа',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispScreenChild_IsLowWeight',
					'label' => 'Недоношенные дети с массой тела менее 1500 г при рождении',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'saveEvnPLDispScreenChild' => array(
				array(
					'field' => 'ignoreEvnPLDispScreenChildExists',
					'label' => 'Признак игнорирования существования карты',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'ScreenType_id',
					'label' => 'Целевая категория',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'МО создания скрининга',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'ScreenEndCause_id',
					'label' => 'Причина завершения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'RiskFactorTypeData',
					'label' => 'Выявлены поведенческие факторы риска',
					'rules' => '',
					'type' => 'json_array',
					'assoc' => true
				),
				array(
					'field' => 'EvnPLDispScreenChild_id',
					'label' => 'Идентификатор карты',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'AgeGroupDisp_id',
					'label' => 'Возрастная группа',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispScreenChild_IsLowWeight',
					'label' => 'Недоношенные дети с массой тела менее 1500 г при рождении',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispScreenChild_Head',
					'label' => 'Окружность головы (см)',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnPLDispScreenChild_Breast',
					'label' => 'Окружность грудной клетки (см)',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnPLDispScreenChild_IsActivity',
					'label' => 'Физическая активность, ежедневная физическая нагрузка (зарядка, пешие прогулки, посещение спортивных секций и т.д.) не менее 30 минут',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispScreenChild_IsDecreaseEar',
					'label' => 'Определение остроты слуха',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispScreenChild_IsDecreaseEye',
					'label' => 'Определение остроты зрения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispScreenChild_IsFlatFoot',
					'label' => 'Оценка плантограммы',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PsychicalConditionType_id',
					'label' => 'Оценка нервно-психического развития',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'SexualConditionType_id',
					'label' => 'Оценка полового развития',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispScreenChild_IsAbuse',
					'label' => 'Признаки жестокого обращения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispScreenChild_IsHealth',
					'label' => 'Здоров',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispScreenChild_IsPMSP',
					'label' => 'Направлен к врачу ПМСП',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DispClass_id',
					'label' => 'Идентификатор этапа',
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
					'field' => 'PersonEvn_id',
					'label' => 'Идентификатор пациента',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Server_id',
					'label' => 'Server_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'PersonHeight_Height',
					'label' => 'Рост (см)',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'PersonWeight_Weight',
					'label' => 'Вес (кг)',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'EvnPLDispScreenChild_ArteriaSistolPress',
					'label' => 'Артериально давление (систолическое)',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnPLDispScreenChild_ArteriaDiastolPress',
					'label' => 'Артериально давление (диастолическое)',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnPLDispScreenChild_SystlcPressure',
					'label' => 'Артериально давление (систолическое) 2-е',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnPLDispScreenChild_DiastlcPressure',
					'label' => 'Артериально давление (диастолическое) 2-е',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'HealthKind_id',
					'label' => 'Группа динамического наблюдения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispScreenChild_IsEndStage',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispScreenChild_IsAlco',
					'label' => 'Употребление алкогольных напитков',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispScreenChild_IsSmoking',
					'label' => 'Курение (хотя бы одну сигарету в день)',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispScreenChild_IsInvalid',
					'label' => 'Установлена инвалидность',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'InvalidGroup_id',
					'label' => 'Группа инвалидности',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispScreenChild_YearInvalid',
					'label' => 'Год установления инвалидности',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnPLDispScreenChild_InvalidPeriod',
					'label' => 'На какой срок установлена инвалидность (до 16 лет) (в годах)',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'InvalidDiag_id',
					'label' => 'Диагноз по инвалидности',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getEvnPLDispScreenChildYears' => array(
			),
			'loadEvnUslugaDispDop' => array(
				array(
					'field' => 'EvnUslugaDispDop_id',
					'label' => 'Идентификатор осмотра (исследования)',
					'rules' => 'trim|required',
					'type' => 'id'
				)
			)
		);
	}

	/**
	 * Сохранение данных по анкетированию
	 */
	function saveDopDispQuestionGrid() {
		$data = $this->ProcessInputData('saveDopDispQuestionGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveDopDispQuestionGrid($data);
		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();

		return true;
	}

	/**
	 * Удаление посещения по срининговому исследованию
	 */
	function deleteEvnPLDispScreenChild() {
		$data = $this->ProcessInputData('deleteEvnPLDispScreenChild', true);
		if ($data === false) { return false; }

	    $response = $this->dbmodel->deleteEvnPLDispScreenChild($data);
		$this->ProcessModelSave($response, true, 'При удалении талона ДД возникли ошибки')->ReturnData();

		return true;
	}

	/**
	*  Проверка на наличие талона на этого человека в этом году
	*  Входящие данные: $_POST['Person_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования талона по ДД
	*/
	function checkIfEvnPLDispScreenChildExists()
	{
		$data = $this->ProcessInputData('checkIfEvnPLDispScreenChildExists', true);
		if ($data === false) { return false; }
	
		$response = $this->dbmodel->checkIfEvnPLDispScreenChildExists($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	 *  Проверка возможности добавления карты
	 */
	function checkAddAvailability()
	{
		$data = $this->ProcessInputData('checkAddAvailability', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkAddAvailability($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	*  Проверка на наличие карты проф осмотра в этом или предыдущем году
	*  Входящие данные: $_POST['Person_id'], $_POST['EvnPLDisp_consDate']
	*  На выходе: JSON-строка
	*  Используется: swEvnPLDispDop13EditWindow
	*/
	function checkIfEvnPLDispScreenChildExistsInTwoYear()
	{
		$data = $this->ProcessInputData('checkIfEvnPLDispScreenChildExistsInTwoYear', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkIfEvnPLDispScreenChildExistsInTwoYear($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	 *	Перенос карты ДВН в профосмотр
	 */
	function transferEvnPLDispDopToEvnPLDispScreenChild()
	{
		$data = $this->ProcessInputData('transferEvnPLDispDopToEvnPLDispScreenChild', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->transferEvnPLDispDopToEvnPLDispScreenChild($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}

	
	/**
	 * Получение данных для формы редактирования талона по ДД
	 * Входящие данные: $_POST['EvnPLDispScreenChild_id']
	 * На выходе: JSON-строка
	 * Используется: форма редактирования талона по ДД
	 */
	function loadEvnPLDispScreenChildEditForm()
	{
		$data = $this->ProcessInputData('loadEvnPLDispScreenChildEditForm', true);
		if ($data === false) { return false; }
	
		$response = $this->dbmodel->loadEvnPLDispScreenChildEditForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *  Сохранение осмотра (исследования)
	 */
	function saveEvnUslugaDispDop() {
		$data = $this->ProcessInputData('saveEvnUslugaDispDop', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveEvnUslugaDispDop($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении осмотра (исследования)')->ReturnData();
	}
	
	/**
	 * Получение списка исследований в талоне по ДД
	 * Входящие данные: $_POST['EvnPLDispScreenChild_id']
	 * На выходе: JSON-строка
	 * Используется: форма редактирования талона по ДД
	 */
	function loadEvnUslugaDispDopGrid()
	{
		$data = $this->ProcessInputData('loadEvnUslugaDispDopGrid', true, true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadEvnUslugaDispDopGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
	}

	/**
	 * Получение данных формы осмотра (исследования)
	 * Входящие данные: $_POST['EvnUslugaDispDop_id']
	 * На выходе: JSON-строка
	 * Используется: форма редактирования талона по ДД
	 */
	function loadEvnUslugaDispDop()
	{
		$data = $this->ProcessInputData('loadEvnUslugaDispDop', true, true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadEvnUslugaDispDop($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Сохранение талона амбулаторного пациента
	 * Входящие данные: ...
	 * На выходе: JSON-строка
	 * Используется: форма редактирования талона амбулаторного пациента
	 */
	function saveEvnPLDispScreenChild()
	{
		$data = $this->ProcessInputData('saveEvnPLDispScreenChild', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->saveEvnPLDispScreenChild($data);
		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();
	}


	/**
	 * Получение числа талонов с распределением по годам, для формирования списка на клиенте
	 * Входящие данные: ...
	 * На выходе: JSON-строка
	 * Используется: форма поиска/потокового ввода талонов по ДД
	 */
	function getEvnPLDispScreenChildYears()
	{
		$data = getSessionParams();

		$year = date('Y');
		$info = $this->dbmodel->getEvnPLDispScreenChildYears($data);
		$outdata = $this->ProcessModelList($info, true, true)->GetOutData();
		
		$flag = false;
		foreach ($outdata as $row) {
			if ( $row['EvnPLDispScreenChild_Year'] == $year ) { $flag = true; }
		}
		if (!$flag) { $outdata[] = array('EvnPLDispScreenChild_Year'=>$year, 'count'=>0); }
		
		$this->ReturnData($outdata);
	}
}
?>
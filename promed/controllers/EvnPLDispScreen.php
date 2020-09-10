<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* EvnPLDispScreen - контроллер для управления профосмотрами
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

class EvnPLDispScreen extends swController
{
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->load->database();
		$this->load->model('EvnPLDispScreen_model', 'dbmodel');
		
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
			'loadEvnPLDispScreenEditForm' => array(
				array(
					'field' => 'EvnPLDispScreen_id',
					'label' => 'Идентификатор талона по срининговому исследованию',
					'rules' => 'trim|required',
					'type' => 'id'
				)
			),
			'deleteEvnPLDispScreen' => array(
				array(
					'field' => 'EvnPLDispScreen_id',
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
			'checkIfEvnPLDispScreenExists' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => 'trim|required',
					'type' => 'id'
				)
			),
			'checkIfEvnPLDispScreenExistsInTwoYear' => array(
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
			'deleteEvnUslugaDispDop' => array(
				array(
					'field' => 'EvnUslugaDispDop_id',
					'label' => 'Идентификатор услуги',
					'rules' => 'required',
					'type' => 'id'
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
					'field' => 'terapevt_vop',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'label' => 'Результат',
					'field' => 'accoucheur_gynecologist',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'label' => 'Результат',
					'field' => 'colposcopy',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'label' => 'Результат',
					'field' => 'biopsy',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'label' => 'Результат',
					'field' => 'rectoromanoscopy',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'label' => 'Результат',
					'field' => 'in_prostate_cancer',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'label' => 'Результат',
					'field' => 'cancer_stomach',
					'rules' => '',
					'type' => 'string'
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
				array('label' => 'Результат', 'field' => 'el_cardiography', 'rules' => '', 'type' => 'id'),
				array('label' => 'Результат', 'field' => 'fec_occult_blood', 'rules' => '', 'type' => 'id'),
				array('label' => 'Результат', 'field' => 'fec_occult_blood_conducted', 'rules' => '', 'type' => 'id'),
				array('label' => 'Результат', 'field' => 'blood_cholest_lvl', 'rules' => '', 'type' => 'id'),
				array('label' => 'Результат', 'field' => 'blood_sugar_lvl', 'rules' => '', 'type' => 'id'),
				array('label' => 'Результат', 'field' => 'uteri_carvix_scrning', 'rules' => '', 'type' => 'id'),
				array('label' => 'Результат', 'field' => 'mammography_scrning', 'rules' => '', 'type' => 'id'),
				array('label' => 'Результат', 'field' => 'mammography_scrning_conducted', 'rules' => '', 'type' => 'id'),
				array('label' => 'Результат', 'field' => 'intraocular_tens', 'rules' => '', 'type' => 'id'),
				array('label' => 'Результат', 'field' => 'colposcopy_res', 'rules' => '', 'type' => 'id'),
				array('label' => 'Результат', 'field' => 'biopsy_res', 'rules' => '', 'type' => 'id'),
				array('label' => 'Результат', 'field' => 'coloscopy_res', 'rules' => '', 'type' => 'id')
			),
			'loadEvnUslugaDispDopGrid' => array(
				array(
					'field' => 'EvnPLDispScreen_id',
					'label' => 'Идентификатор талона по срининговому исследованию',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'AgeGroupDisp_id',
					'label' => 'Возрастная группа',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'ScreenType_id',
					'label' => 'Целевая категория',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispScreen_setDate',
					'label' => 'Дата начала',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'withoutAgeGroups',
					'rules' => '',
					'type' => 'boolean'
				)
			),
			'saveEvnPLDispScreen' => array(
				array(
					'field' => 'data',
					'rules' => '',
					'type' => 'string'
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
					'field' => 'withoutAgeGroups',
					'rules' => '',
					'type' => 'boolean'
				),
				array(
					'field' => 'EvnPLDispScreen_id',
					'label' => 'Идентификатор карты',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispScreen_setDate',
					'label' => 'Дата начала',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'modifiedData',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'AgeGroupDisp_id',
					'label' => 'Возрастная группа',
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
					'field' => 'EvnPLDispScreen_PersonWaist',
					'label' => 'Окружность талии',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnPLDispScreen_QueteletIndex',
					'label' => 'Индекс Кетле',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'EvnPLDispScreen_ArteriaSistolPress',
					'label' => 'Артериально давление (систолическое)',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnPLDispScreen_ArteriaDiastolPress',
					'label' => 'Артериально давление (диастолическое)',
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
					'field' => 'EvnPLDispScreen_IsEndStage',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'AlcoholIngestType_bid',
					'label' => 'Норма еженедельного потребления пива',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'AlcoholIngestType_vid',
					'label' => 'Норма еженедельного потребления водки',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'AlcoholIngestType_wid',
					'label' => 'Норма еженедельного потребления вина',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispScreen_IsAlco',
					'label' => 'Употребление алкогольных напитков',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispScreen_IsBleeding',
					'label' => 'Бывают ли контактные кровотечения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispScreen_IsCoronary',
					'label' => 'Болезни сердца у пациента ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispScreen_IsHeadache',
					'label' => 'Головные боли',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispScreen_IsHeartache',
					'label' => 'Боль или другие неприятные ощущения за грудиной',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispScreen_IsHighPressure',
					'label' => 'Повышение артериального давления',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispScreen_IsParCoronary',
					'label' => 'Болезни сердца у родителей',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispScreen_IsSmoking',
					'label' => 'Курение (хотя бы одну сигарету в день)',
					'rules' => '',
					'type' => 'id'
				),
				/*array(
					'field' => 'WaistCircumference_id',
					'label' => 'Окружность талии',
					'rules' => '',
					'type' => 'id'
				),*/
				array('field' => 'EvnPLDispScreen_IsBlurVision', 'label' => '', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnPLDispScreen_IsDailyPhysAct', 'label' => '', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnPLDispScreen_IsDirectedPMSP', 'label' => '', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnPLDispScreen_IsGenPredisposed', 'label' => '', 'rules' => '', 'type' => 'swcheckbox'),
				array('field' => 'EvnPLDispScreen_IsGlaucoma', 'label' => '', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnPLDispScreen_IsHealthy', 'label' => '', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnPLDispScreen_IsHighMyopia', 'label' => '', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnPLDispScreen_IsHyperglycaemia', 'label' => '', 'rules' => '', 'type' => 'swcheckbox'),
				array('field' => 'EvnPLDispScreen_IsHyperlipidemia', 'label' => '', 'rules' => '', 'type' => 'swcheckbox'),
				array('field' => 'EvnPLDispScreen_IsHypertension', 'label' => '', 'rules' => '', 'type' => 'swcheckbox'),
				array('field' => 'EvnPLDispScreen_IsLowPhysAct', 'label' => '', 'rules' => '', 'type' => 'swcheckbox'),
				array('field' => 'EvnPLDispScreen_IsOverweight', 'label' => '', 'rules' => '', 'type' => 'swcheckbox'),
				array('field' => 'EvnPLDispScreen_IsVisImpair', 'label' => '', 'rules' => '', 'type' => 'id'),
				array('field' => 'FecalCasts_id', 'label' => '', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnPLDispScreen_IsAlcoholAbuse', 'label' => '', 'rules' => '', 'type' => 'swcheckbox'),
				array('field' => 'EvnPLDispScreen_IsDisability', 'label' => '', 'rules' => '', 'type' => 'swcheckbox'),
				array('field' => 'EvnPLDispScreen_DisabilityPeriod', 'label' => 'less_than_equal_to[16]', 'rules' => '', 'type' => 'int'),
				array('field' => 'EvnPLDispScreen_DisabilityYear', 'label' => '', 'rules' => 'exact_length[4]', 'type' => 'int'),
				array('field' => 'Diag_disid', 'label' => '', 'rules' => '', 'type' => 'id')
			),
			'getEvnPLDispScreenYears' => array(
			),
			'loadEvnUslugaDispDop' => array(
				array(
					'field' => 'EvnUslugaDispDop_id',
					'label' => 'Идентификатор осмотра (исследования)',
					'rules' => 'trim|required',
					'type' => 'id'
				)
			),
			'getEvnUslugaDispDopResult' => array(
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Услуга',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'ScreenType_id',
					'label' => 'Целевая категория',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaDispDop_pid',
					'label' => 'Родительское событие (скрининг)',
					'rules' => '',
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
	function deleteEvnPLDispScreen() {
		$data = $this->ProcessInputData('deleteEvnPLDispScreen', true);
		if ($data === false) { return false; }

	    $response = $this->dbmodel->deleteEvnPLDispScreen($data);
		$this->ProcessModelSave($response, true, 'При удалении талона ДД возникли ошибки')->ReturnData();

		return true;
	}

	/**
	*  Проверка на наличие талона на этого человека в этом году
	*  Входящие данные: $_POST['Person_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования талона по ДД
	*/
	function checkIfEvnPLDispScreenExists()
	{
		$data = $this->ProcessInputData('checkIfEvnPLDispScreenExists', true);
		if ($data === false) { return false; }
	
		$response = $this->dbmodel->checkIfEvnPLDispScreenExists($data);
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
	function checkIfEvnPLDispScreenExistsInTwoYear()
	{
		$data = $this->ProcessInputData('checkIfEvnPLDispScreenExistsInTwoYear', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkIfEvnPLDispScreenExistsInTwoYear($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	 *	Перенос карты ДВН в профосмотр
	 */
	function transferEvnPLDispDopToEvnPLDispScreen()
	{
		$data = $this->ProcessInputData('transferEvnPLDispDopToEvnPLDispScreen', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->transferEvnPLDispDopToEvnPLDispScreen($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}

	
	/**
	 * Получение данных для формы редактирования талона по ДД
	 * Входящие данные: $_POST['EvnPLDispScreen_id']
	 * На выходе: JSON-строка
	 * Используется: форма редактирования талона по ДД
	 */
	function loadEvnPLDispScreenEditForm()
	{
		$data = $this->ProcessInputData('loadEvnPLDispScreenEditForm', true);
		if ($data === false) { return false; }
	
		$response = $this->dbmodel->loadEvnPLDispScreenEditForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *  Удаление посещения/осмотра/исследования по доп. диспансеризации
	 */
	function deleteEvnUslugaDispDop() {
		$data = $this->ProcessInputData('deleteEvnUslugaDispDop', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteEvnUslugaDispDop($data);
		$this->ProcessModelSave($response, true, 'Ошибка при удалении осмотра (исследования)')->ReturnData();
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
	 * Входящие данные: $_POST['EvnPLDispScreen_id']
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
	function saveEvnPLDispScreen()
	{
		$data = $this->ProcessInputData('saveEvnPLDispScreen', true);
		if ($data === false) { return false; }

		if(isset($data['data'])) {
			$data['data'] = json_decode($data['data']);
		}
		
		$response = $this->dbmodel->saveEvnPLDispScreen($data);
		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();
	}


	/**
	 * Получение числа талонов с распределением по годам, для формирования списка на клиенте
	 * Входящие данные: ...
	 * На выходе: JSON-строка
	 * Используется: форма поиска/потокового ввода талонов по ДД
	 */
	function getEvnPLDispScreenYears()
	{
		$data = getSessionParams();

		$year = date('Y');
		$info = $this->dbmodel->getEvnPLDispScreenYears($data);
		$outdata = $this->ProcessModelList($info, true, true)->GetOutData();
		
		$flag = false;
		foreach ($outdata as $row) {
			if ( $row['EvnPLDispScreen_Year'] == $year ) { $flag = true; }
		}
		if (!$flag) { $outdata[] = array('EvnPLDispScreen_Year'=>$year, 'count'=>0); }
		
		$this->ReturnData($outdata);
	}
	
	function getEvnUslugaDispDopResult() {
		$data = $this->ProcessInputData('getEvnUslugaDispDopResult', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getEvnUslugaDispDopResult($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
}
?>
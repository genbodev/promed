<?php
defined('BASEPATH') or die('No direct script access allowed');
/**
 * ufa_Reab_Register_User - контроллер для регистра реабилитации (Башкирия)
 *  серверная часть
 *
 *
 * @package			
 * @author			 
 * @version			17.01.2017
 */
class Ufa_Reab_Register_User extends swController {

	var $model = "ufa/Ufa_Reab_Register_User_model";

	//var $model = "Ufa_Reab_Register_User_model";

	/**
	 *  * Конструктор
	 */
	function __construct() {
		$this->result = array(); 
		$this->start = true;

		parent::__construct();



		//$this->load->database('testUfa');
		$this->load->database();
		$this->load->model($this->model, 'dbmodel');

		$this->inputRules = array(
			'SeekRegistr' => array(
				array(
					'field' => 'Person_id',
					'label' => '',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'saveInReabRegister' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Человек',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'DirectType_id',
					'label' => 'Профиль',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'StageType_id',
					'label' => 'Этап',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'ReabEvent_setDate',
					'label' => 'Дата включения в регистр/старт этапа',
					'rules' => 'required',
					'type' => 'datetime'
				),
				array(
					'field' => 'MedPersonal_iid',
					'label' => 'Добавил человека в регистр - врач',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_iid',
					'label' => 'Добавил человека в регистр - ЛПУ',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getICFTreeData' => array(
				array(
				'field' => 'node',
				'label' => 'node',
				'rules' => 'required',
				'type' => 'string'
			     ),
				array(
				'field' => 'ICF_pid',
				'label' => 'Уровень',
				'rules' => 'required',
				'type' => 'string'
				),
				array(
				'field' => 'ICF_code',
				'label' => 'Код домена',
				'rules' => 'required',
				'type' => 'string'
				),
				array(
				'field' => 'ICF_code_filter',
				'label' => 'Фильтр Кода домена',
				'rules' => 'required',
				'type' => 'string'
				),
				array(
				'field' => 'ICF_Name_filter',
				'label' => 'Фильтр контента наименования домена',
				'rules' => 'required',
				'type' => 'string'
				),
			),
			'SaveICFRating'  => array(
				array(
				'field' => 'Person_id',
				'label' => 'пациент',
				'rules' => 'required',
				'type' => 'id'
			     ),
				array(
				'field' => 'ReabEvent_id',
				'label' => 'Случай реабилитации',
				'rules' => 'required',
				'type' => 'id'
				),
				array(
				'field' => 'ICFRating_setDate',
				'label' => 'Дата проведения оценки',
				'rules' => 'required',
				'type' => 'date'
				),
				array(
				'field' => 'ICF_id',
				'label' => 'Код домена',
				'rules' => '',
				'type' => 'id'
				),
				array(
				'field' => 'ICFSeverity_id',
				'label' => 'Код выраженности нарушения',
				'rules' => '',
				'type' => 'id'
				),
				array(
				'field' => 'ICFNature_id',
				'label' => 'Код характера нарушения',
				'rules' => '',
				'type' => 'id'
				),
				array(
				'field' => 'ICFLocalization_id',
				'label' => 'Код локализации нарушения',
				'rules' => '',
				'type' => 'id'
				),
				array(
				'field' => 'ReabICFRating_TargetRealiz',
				'label' => 'Код цели реализации',
				'rules' => '',
				'type' => 'id'
				),
				array(
				'field' => 'ReabICFRating_TargetCapasit',
				'label' => 'Код Цели по капаситету',
				'rules' => '',
				'type' => 'id'
				),
				array(
				'field' => 'ICFRating_CapasitEval',
				'label' => 'Код оценки по капаситету',
				'rules' => '',
				'type' => 'id'
				),
				array(
				'field' => 'ReabICFRating_id',
				'label' => 'id проведенной оценки',
				'rules' => '',
				'type' => 'id'
				),
				array(
				'field' => 'ICFEnvFactors_id',
				'label' => 'Код оценки окружающей среды',
				'rules' => '',
				'type' => 'id'
				),
				array(
				'field' => 'ReabICFRating_FactorsTarget',
				'label' => 'Код цели по окружающей среде',
				'rules' => '',
				'type' => 'id'
				),
				array(
				'field' => 'MedStaffFact_id',
				'label' => 'Код врача',
				'rules' => 'required',
				'type' => 'id'
				),
				array(
				'field' => 'Func',
				'label' => 'операция с данными',
				'rules' => 'required',
				'type' => 'string'
				),
			),
			'DeleteICFRating' => array(
				array(
				'field' => 'Person_id',
				'label' => 'пациент',
				'rules' => 'required',
				'type' => 'id'
			     ),
				array(
				'field' => 'ReabEvent_id',
				'label' => 'Случай реабилитации',
				'rules' => 'required',
				'type' => 'id'
				),
				array(
				'field' => 'ReabICFRating_id',
				'label' => 'id проведенной оценки',
				'rules' => 'required',
				'type' => 'id'
				),
			),
			'getListObjectsCurrentUser' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Person_id',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getListTestUserReab' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Person_id',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'DirectType_id',
					'label' => 'DirectType_id',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'StageType_id',
					'label' => 'код этапа',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'getListHeartRateUserReab' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Person_id',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'DirectType_id',
					'label' => 'DirectType_id',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'StageType_id',
					'label' => 'Код этапа',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'getListScalesDirectCurrentUser' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Person_id',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'DirectType_id',
					'label' => 'DirectType_id',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getListICF_Verdict' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Person_id',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'ReabEvent_id',
					'label' => 'ReabEvent_id',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Domen',
					'label' => 'Домен ICF',
					'rules' => 'required',
					'type' => 'string'
				)
			),
			'getReabDiagICF' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Person_id',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'ReabEvent_id',
					'label' => 'ReabEvent_id',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'CanselCloseStage' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Person_id',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'ReabEvent_id',
					'label' => 'ReabEvent_id',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'CloseRegistrStage' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Человек',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'DirectType_id',
					'label' => 'Профиль',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'ReabEvent_id',
					'label' => 'Случай реабилитации',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'StageType_id',
					'label' => 'Этап',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'StageName',
					'label' => 'Значение этапа',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'ReabEvent_disDate',
					'label' => 'Дата завершения этапа',
					'rules' => 'required',
					'type' => 'datetime'
				),
				array(
					'field' => 'MedPersonal_did',
					'label' => 'Закрыл этап в регистре - врач',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_did',
					'label' => 'Закрыл этап в регистре - ЛПУ',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'ReabOutCause_id',
					'label' => 'Причина закрытия этапа',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'AddRegistrProfStage' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Человек',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'DirectType_id',
					'label' => 'Профиль',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'StageType_id',
					'label' => 'Этап',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'ReabEvent_setDate',
					'label' => 'Дата включения в регистр/старт этапа',
					'rules' => 'required',
					'type' => 'datetime'
				),
				array(
					'field' => 'StageName',
					'label' => 'Значение этапа',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'MedPersonal_iid',
					'label' => 'Добавил человека в регистр - врач',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_iid',
					'label' => 'Добавил человека в регистр - ЛПУ',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'SeekProfReab' => array(),
			'SeekStageReab' => array(),
			'ICFSpr' => array(),
			'SeekOutCauseReab' => array(),
			//			'SeekOutCauseReab' => array(
			//				array(
			//					'field' => 'ListCombo',
			//					'label' => 'Перечень элементов списка',
			//					'rules' => 'required',
			//					'type' => 'string'
			//				)
			//			),
			'ReabSpr' => array(
				array(
					'field' => 'SprNumber',
					'label' => 'Код справочника',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'SprNumberGroup',
					'label' => 'Группа справочника',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'CreateAnketa' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Человек',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'DirectType_id',
					'label' => 'Код Профиля',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'StageType_id',
					'label' => 'Код Этапа реабилитации',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'loadAnketa' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Человек',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'DirectType_id',
					'label' => 'Код Профиля',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'StageType_id',
					'label' => 'Код Этапа реабилитации',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'DateAnketa',
					'label' => 'Дата составления анкеты',
					'rules' => 'required',
					'type' => 'date'
				)
			),
			'saveRegistrAnketa' => array(
				array(
					'field' => 'ReabQuestion_setDate',
					'label' => 'Дата составления анкеты',
					'rules' => 'required',
					'type' => 'datetime'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Человек',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'DirectType_id',
					'label' => 'Код Профиля',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'StageType_id',
					'label' => 'Код Этапа реабилитации',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'MedPersonal_iid',
					'label' => 'Добавил человека в регистр - врач',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_iid',
					'label' => 'Добавил человека в регистр - ЛПУ',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'parameter',
					'label' => 'Перечень параметров анкеты',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'ReabPotent',
					'label' => 'Реабилитационный потенциал',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'isButtonAdd',
					'label' => 'Признак сохранения insert',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'isButtonEdit',
					'label' => 'Признак сохранения update)',
					'rules' => '',
					'type' => 'string'
				)
			),
			'saveRegistrTest' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Человек',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'DirectType_id',
					'label' => 'Код Профиля',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'StageType_id',
					'label' => 'Код Этапа реабилитации',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'ReabTest_setDate',
					'label' => 'Дата  и время Проведения теста',
					'rules' => 'required',
					'type' => 'datetime'
				),
				array(
					'field' => 'ReabTestParam_id',
					'label' => 'Код наименования теста',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'ReabTestValue_id',
					'label' => 'Код значения теста',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'MedPersonal_iid',
					'label' => 'Добавил человека в регистр - врач',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_iid',
					'label' => 'Добавил человека в регистр - ЛПУ',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'isButton',
					'label' => 'Режим: добавление,редактирование,удаление',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'ReabResultTest_id',
					'label' => 'id записи',
					'rules' => '',
					'type' => 'id'
				)
			),
			'saveHeartRate' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Человек',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'DirectType_id',
					'label' => 'Код Профиля',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'StageType_id',
					'label' => 'Код Этапа реабилитации',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'ReabHeartRate_setDate',
					'label' => 'Дата  и время Проведения теста',
					'rules' => 'required',
					'type' => 'datetime'
				),
				array(
					'field' => 'ReabHeartRate_peace',
					'label' => 'ЧСС покоя',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'ReabHeartRate_max',
					'label' => 'МАХ ЧСС',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'MedPersonal_iid',
					'label' => 'Добавил человека в регистр - врач',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_iid',
					'label' => 'Добавил человека в регистр - ЛПУ',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'isButton',
					'label' => 'Режим: добавление,удаление',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'ReabHeartRate_id',
					'label' => 'id записи',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getListScales' => array(
				array(
					'field' => 'ScaleType_SysNick',
					'label' => 'Наименование шкал',
					'rules' => 'required',
					'type' => 'string'
				)
			),
			'scaleSpr' => array(
				array(
					'field' => 'SysNick',
					'label' => 'Наименование шкалы',
					'rules' => 'required',
					'type' => 'string'
				)),
			'scaleSpr_Data' => array(
				array(
					'field' => 'SysNick',
					'label' => 'Наименование шкалы',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Человек',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'DirectType_id',
					'label' => 'Код Профиля',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'ReabEvent_id',
					'label' => 'Идентификатор случая в регистре',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'deleteRegistrScale' => array(
				array(
					'field' => 'ReabScale_setDate',
					'label' => 'Дата Проведения оценки',
					'rules' => 'required',
					'type' => 'datetime'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Человек',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'DirectType_id',
					'label' => 'Код Профиля',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'ReabEvent_id',
					'label' => 'Идентификатор записи в регистре',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'MedPersonal_did',
					'label' => 'Добавил человека в регистр - врач',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_did',
					'label' => 'Добавил человека в регистр - ЛПУ',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Scale_SysNick',
					'label' => 'Наименование шкалы',
					'rules' => 'required',
					'type' => 'string'
				)
			),
			'saveRegistrScale' => array(
				array(
					'field' => 'ReabScale_setDate',
					'label' => 'Дата Проведения оценки',
					'rules' => 'required',
					'type' => 'datetime'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Человек',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'DirectType_id',
					'label' => 'Код Профиля',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'ReabEvent_id',
					'label' => 'Идентификатор записи в регистре',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'MedPersonal_iid',
					'label' => 'Добавил человека в регистр - врач',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_iid',
					'label' => 'Добавил человека в регистр - ЛПУ',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'ReabScaleParameter',
					'label' => 'Перечень параметров анкеты',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'ReabScaleResult',
					'label' => 'Итоговая оценка по шкале',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'isButtonAdd',
					'label' => 'Признак сохранения insert',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'isButtonEdit',
					'label' => 'Признак сохранения update',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'ReabScaleRefinement',
					'label' => 'Уточняющие сообщения',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Scale_SysNick',
					'label' => 'Наименование шкалы',
					'rules' => 'required',
					'type' => 'string'
				)
			),
			'getTreeDatesScales' => array(
				array(
					'field' => 'DirectType_id',
					'label' => 'Профиль наблюдения',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => '',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Scale_SysNick',
					'label' => 'Наименование шкалы',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'ReabEvent_id',
					'label' => 'Индикатор записи в регистре',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getDiagPerson' => array(
				array(
					'field' => 'Person_id',
					'label' => '',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'DiagMKB',
					'label' => 'Перечень диагнозов МКБ',
					'rules' => 'required',
					'type' => 'string'
				)
			)
		);
	}

	/**
	 * Перечень профилей
	 */
	function ListParamReab() {
		$data['Profil'] = "('cnsReab', 'cardiologyReab','travmReab')";
		$data['Region'] = 2;
		// $data['RegisterSysNick'] = 'reability'; // Тип регистра
		return $data;
	}

	/**
	 *  Построение древа дат измерений по шкалам, относительно предмета наблюдения
	 */
	function getTreeDatesScales() {
		$data = $this->ProcessInputData('getTreeDatesScales', false);

		if ($data === false) {
			return false;
		}

		$list = $this->dbmodel->getTreeDatesScales($data);

		//echo '<pre>' . print_r($list, 1) . '</pre>';

		$dates = array();

		foreach ($list as $k => $v) {
			$dates[] = $v;
		}

		$this->ReturnData($dates);
	}

	/**
	 * Загрузка данных из справочника шкал (Для GRACE)
	 */
	function scaleSpr() {
		$data = $this->ProcessInputData('scaleSpr', false);
		if ($data === false) {
			return false;
		}
		$dataIn['SysNick'] = $data['SysNick'];
		$list = $this->dbmodel->scaleSpr($dataIn);

		$this->ReturnData(array(
			'SprScale' => $list
		));
	}

	/**
	 * Загрузка данных из справочника + конкретные данные
	 */
	function scaleSpr_Data() {
		$data = $this->ProcessInputData('scaleSpr_Data', false);
		if ($data === false) {
			return false;
		}
		// Сначала справочник
		$dataIn['SysNick'] = $data['SysNick'];
		$list = $this->dbmodel->scaleSpr($dataIn);

		//Данные по пациенту
		$dataIn['Person_id'] = $data['Person_id'];
		$dataIn['DirectType_id'] = $data['DirectType_id'];
		$dataIn['ReabEvent_id'] = $data['ReabEvent_id'];

		$response = $this->dbmodel->scaleDataPers($dataIn);

		if ($data['SysNick'] == 'VAScale') {
			$aParam['CodSpr'] = 202;
			$aParam['CodGroupSpr'] = 1;
			$listspr = $this->dbmodel->ReabSpr($aParam);
			$this->ReturnData(array(
				'SprScale' => $list,
				'DataScale' => $response,
				'SprParam' => $listspr
			));
			return;
		}
		if ($data['SysNick'] == 'MedResCouncil') {
			$aParam['CodSpr'] = 203;
			$aParam['CodGroupSpr'] = 1;
			$listspr1 = $this->dbmodel->ReabSpr($aParam);
			$aParam['CodSpr'] = 204;
			$aParam['CodGroupSpr'] = 1;
			$listspr2 = $this->dbmodel->ReabSpr($aParam);
			$aParam['CodSpr'] = 205;
			$aParam['CodGroupSpr'] = 1;
			$listspr3 = $this->dbmodel->ReabSpr($aParam);
			$this->ReturnData(array(
				'SprScale' => $list,
				'DataScale' => $response,
				'SprParam1' => $listspr1,
				'SprParam2' => $listspr2,
				'SprParam3' => $listspr3
			));
			return;
		}

		if ($data['SysNick'] == 'Lequesne') {
			$aParam['CodSpr'] = 46;
			$aParam['CodGroupSpr'] = 1;
			$listspr1 = $this->dbmodel->ReabSpr($aParam);
			$aParam['CodSpr'] = 47;
			$aParam['CodGroupSpr'] = 1;
			$listspr2 = $this->dbmodel->ReabSpr($aParam);

			$listspr3 = $this->dbmodel->ReabSpr($aParam);
			$this->ReturnData(array(
				'SprScale' => $list,
				'DataScale' => $response,
				'SprParam1' => $listspr1,
				'SprParam2' => $listspr2
			));
			return;
		}

		$this->ReturnData(array(
			'SprScale' => $list,
			'DataScale' => $response
		));
	}

	/**
	 * Наименование шкал
	 */
	function getListScales() {
		$data = $this->ProcessInputData('getListScales', false);
		if ($data === false) {
			return false;
		}
		//echo "EvnScale_Name";
		//echo '<pre>' . print_r($data['EvnScale_Name'], 1) . '</pre>';
		$dataIn['ScaleType_SysNick'] = $data['ScaleType_SysNick'];
		$list = $this->dbmodel->getListScales($dataIn);
		$this->ReturnData(array('data' => $list));
	}

	/**
	 * удаление измерений по шкалам
	 */
	function deleteRegistrScale() {
		$data = $this->ProcessInputData('deleteRegistrScale', true);
		if ($data === false) {
			return false;
		}

		$dataIn['pmUser_id'] = $data['pmUser_id'];
		$dataIn['ReabScale_setDate'] = $data['ReabScale_setDate'];
		$dataIn['MedPersonal_did'] = $data['MedPersonal_did'];
		$dataIn['Lpu_did'] = $data['Lpu_did'];
		$dataIn['DirectType_id'] = $data['DirectType_id'];
		$dataIn['Person_id'] = $data['Person_id'];
		$dataIn['Scale_SysNick'] = $data['Scale_SysNick'];

		$response = $this->dbmodel->deleteRegistrScale($dataIn);

		//Данные по пациенту
		$dataPers['Person_id'] = $data['Person_id'];
		$dataPers['DirectType_id'] = $data['DirectType_id'];
		$dataPers['SysNick'] = $data['Scale_SysNick'];
		$dataPers['ReabEvent_id'] = $data['ReabEvent_id'];


		$listScales = $this->dbmodel->scaleDataPers($dataPers);

		//  echo '$listScales';
		//   echo '<pre>' . print_r($listScales) . '</pre>';

		if (is_array($response)) {
			if (isset($response[0]['Error_Code'])) {
				return $this->ReturnData(array(
							'success' => false,
							'Error_Msg' => toUTF($response[0]['Error_Message'])
				));
			} else {
				//  echo 'нет ошибки ';
				return $this->ReturnData(array(
							'success' => true,
							'Error_Msg' => toUTF('Все в норме!'),
							'listScales' => $listScales
				));
			}
		} else {
			// echo 'Что-то такое ';
			return $response;
		}
	}

	/**
	 * Сохранение измерений по шкалам
	 */
	function saveRegistrScale() {
		$data = $this->ProcessInputData('saveRegistrScale', true);
		if ($data === false) {
			return false;
		}
		//Проверка для Лекена
		if ($data['Scale_SysNick'] == "Lequesne") {
			$data_con['ReabScale_setDate'] = substr($data['ReabScale_setDate'], 0, 10);
			$data_con['ReabScaleParameter'] = $data['ReabScaleParameter'];
			$data_con['Scale_SysNick'] = $data['Scale_SysNick'];
			$data_con['Person_id'] = $data['Person_id'];

			$list = $this->dbmodel->contrScale($data_con);

			if (is_array($list)) 
			{
				if ($list[0]['nKol'] > 0) 
				{
					return $this->ReturnData(array(
								'success' => false,
								'Error_Msg' => "На указанную дату " . $data_con['ReabScale_setDate'] . " по данному виду сустава и стороне имеется заполненная шкала!"
					));
				}
			} 
			else
			{
				return $this->ReturnData(array(
							'success' => false,
							'Error_Msg' => "Ошибка в БД!"
				));
			}
		}

		$dataIn['pmUser_id'] = $data['pmUser_id'];
		$dataIn['ReabScale_setDate'] = $data['ReabScale_setDate'];
		$dataIn['MedPersonal_iid'] = $data['MedPersonal_iid'];
		$dataIn['Lpu_iid'] = $data['Lpu_iid'];
		$dataIn['ReabScaleParameter'] = $data['ReabScaleParameter'];
		$dataIn['DirectType_id'] = $data['DirectType_id'];
		$dataIn['ReabScaleResult'] = $data['ReabScaleResult'];
		$dataIn['Person_id'] = $data['Person_id'];
		$dataIn['isButtonAdd'] = $data['isButtonAdd'];
		$dataIn['isButtonEdit'] = $data['isButtonEdit'];
		$dataIn['Scale_SysNick'] = $data['Scale_SysNick'];
		//  $dataIn['StageType_id'] = $data['StageType_id'];
		$dataIn['ReabEvent_id'] = $data['ReabEvent_id'];
		$dataIn['ReabScaleRefinement'] = $data['ReabScaleRefinement'];


		// echo "filter";
		$response = $this->dbmodel->saveRegistrScale($dataIn);

		// Данные по пациенту
		//Данные по пациенту
		$dataPers['Person_id'] = $data['Person_id'];
		$dataPers['DirectType_id'] = $data['DirectType_id'];
		$dataPers['SysNick'] = $data['Scale_SysNick'];
		$dataPers['ReabEvent_id'] = $data['ReabEvent_id'];
		$listScales = $this->dbmodel->scaleDataPers($dataPers);


		if (is_array($response))
		{
			// echo '<pre>' . print_r($response[0]['Error_Code'], 1) . '</pre>';
			if (isset($response[0]['Error_Code']))
			{
				return $this->ReturnData(array(
							'success' => false,
							'Error_Msg' => toUTF($response[0]['Error_Message'])
				));
			}
			else
			{
				//  echo 'нет ошибки ';
				// переделать -- прокинуть шапки анкет
				return $this->ReturnData(array(
							'success' => true,
							'Error_Msg' => toUTF('Все в норме!'),
							'listScales' => $listScales
				));
			}
		}
		else
		{
			// echo 'Что-то такое ';
			return $response;
		}
	}

	/**
	 * Сохранение тестов
	 */
	function saveRegistrTest() {
		$data = $this->ProcessInputData('saveRegistrTest', true);
		if ($data === false) {
			return false;
		}

		$dataIn['DirectType_id'] = $data['DirectType_id'];
		$dataIn['StageType_id'] = $data['StageType_id'];
		$dataIn['Person_id'] = $data['Person_id'];
		$dataIn['ReabTest_setDate'] = $data['ReabTest_setDate'];
		$dataIn['ReabTestParam_id'] = $data['ReabTestParam_id'];
		$dataIn['ReabTestValue_id'] = $data['ReabTestValue_id'];

		$dataIn['pmUser_id'] = $data['pmUser_id'];
		$dataIn['MedPersonal_iid'] = $data['MedPersonal_iid'];
		$dataIn['Lpu_iid'] = $data['Lpu_iid'];
		$dataIn['isButton'] = $data['isButton'];
		$dataIn['ReabResultTest_id'] = $data['ReabResultTest_id'];

		//echo "isButtonEdit";
		//echo '<pre>' . print_r($data['isButtonEdit'], 1) . '</pre>';

		$response = $this->dbmodel->saveRegistrTest($dataIn);




		if (is_array($response)) {
			// echo '<pre>' . print_r($response[0]['Error_Code'], 1) . '</pre>';
			if (isset($response[0]['Error_Code'])) {
				return $this->ReturnData(array(
							'success' => false,
							'Error_Msg' => toUTF($response[0]['Error_Message'])
				));
			} else {
				//  echo 'нет ошибки ';
				// переделать -- прокинуть шапки анкет
				return $this->ReturnData(array(
							'success' => true,
							'Error_Msg' => toUTF('Все в норме!')
				));
			}
		} else {
			return $this->ReturnData(array(
						'success' => false,
						'Error_Msg' => toUTF('Проблема в базе. Обратитесь к разработчику!')
			));
		}
	}

	/**
	 * Сохранение измерений ЧСС
	 */
	function saveHeartRate() {
		$data = $this->ProcessInputData('saveHeartRate', true);
		if ($data === false) {
			return false;
		}

		$dataIn['DirectType_id'] = $data['DirectType_id'];
		$dataIn['StageType_id'] = $data['StageType_id'];
		$dataIn['Person_id'] = $data['Person_id'];
		$dataIn['ReabHeartRate_setDate'] = $data['ReabHeartRate_setDate'];
		$dataIn['ReabHeartRate_peace'] = $data['ReabHeartRate_peace'];
		$dataIn['ReabHeartRate_max'] = $data['ReabHeartRate_max'];

		$dataIn['pmUser_id'] = $data['pmUser_id'];
		$dataIn['MedPersonal_iid'] = $data['MedPersonal_iid'];
		$dataIn['Lpu_iid'] = $data['Lpu_iid'];
		$dataIn['isButton'] = $data['isButton'];
		$dataIn['ReabHeartRate_id'] = $data['ReabHeartRate_id'];

		//echo "isButtonEdit";
		//echo '<pre>' . print_r($data['isButtonEdit'], 1) . '</pre>';

		$response = $this->dbmodel->saveHeartRate($dataIn);

		if (is_array($response)) {
			// echo '<pre>' . print_r($response[0]['Error_Code'], 1) . '</pre>';
			if (isset($response[0]['Error_Code'])) {
				return $this->ReturnData(array(
							'success' => false,
							'Error_Msg' => toUTF($response[0]['Error_Message'])
				));
			} else {
				//  echo 'нет ошибки ';
				// переделать -- прокинуть шапки анкет
				return $this->ReturnData(array(
							'success' => true,
							'Error_Msg' => toUTF('Все в норме!')
				));
			}
		} else {
			return $this->ReturnData(array(
						'success' => false,
						'Error_Msg' => toUTF('Проблема в базе. Обратитесь к разработчику!')
			));
		}
	}

	/**
	 * Сохранение анкеты
	 */ 
    function saveRegistrAnketa() {
		$data = $this->ProcessInputData('saveRegistrAnketa', true);
		if ($data === false) {
			return false;
		}

		$dataIn['pmUser_id'] = $data['pmUser_id'];
		$dataIn['ReabQuestion_setDate'] = $data['ReabQuestion_setDate'];
		$dataIn['MedPersonal_iid'] = $data['MedPersonal_iid'];
		$dataIn['Lpu_iid'] = $data['Lpu_iid'];
		$dataIn['ReabPotent'] = $data['ReabPotent'];
		$dataIn['DirectType_id'] = $data['DirectType_id'];
		$dataIn['StageType_id'] = $data['StageType_id'];
		$dataIn['Person_id'] = $data['Person_id'];

		//Формируем строку
		$cParam = (string) $data['parameter'];
		//		echo "cParam";
		//		echo '<pre>' . print_r($cParam, 1) . '</pre>';
		$cParam = str_replace("{", "", $cParam);
		$cParam = str_replace("}", ",", $cParam);
		$cParam = str_replace('"', "", $cParam);
		$dataIn['parameter'] = $cParam;

		$dataIn['isButtonAdd'] = $data['isButtonAdd'];
		$dataIn['isButtonEdit'] = $data['isButtonEdit'];

		//echo "isButtonAdd";
		//echo '<pre>' . print_r($data['isButtonAdd'], 1) . '</pre>';
		//echo "isButtonEdit";
		//echo '<pre>' . print_r($data['isButtonEdit'], 1) . '</pre>';

		$response = $this->dbmodel->saveRegistrAnketa($dataIn);

		//Получение заголовков
		//Тянем шапки анкет
		$aParamAnketa['Person_id'] = $data['Person_id'];
		$aParamAnketa['DirectType_id'] = $data['DirectType_id'];
		$aParamAnketa['StageType_id'] = $data['StageType_id'];
		$headAnketa = $this->dbmodel->headAnketa($aParamAnketa);


		if (is_array($response)) {
			// echo '<pre>' . print_r($response[0]['Error_Code'], 1) . '</pre>';
			if (isset($response[0]['Error_Code'])) {
				return $this->ReturnData(array(
							'success' => false,
							'Error_Msg' => toUTF($response[0]['Error_Message'])
				));
			} else {
				//  echo 'нет ошибки ';
				// переделать -- прокинуть шапки анкет
				return $this->ReturnData(array(
							'success' => true,
							'Error_Msg' => toUTF('Все в норме!'),
							'headAnketa' => $headAnketa
				));
			}
		} else {
			// echo 'Что-то такое ';
			return $response;
		}
	}

	/**
	 * тянем шаблон, справочники,шапки анкет, данные анкет
	 */
	function CreateAnketa() {
		$data = $this->ProcessInputData('CreateAnketa', false);
		if ($data === false) {
			return false;
		}
		$list = $this->dbmodel->CreateAnketa($data);
		if (count($list) == 0) {
			return $this->ReturnData(array(
						'success' => false,
						'Error_Msg' => toUTF('Ошибка в шаблоне')
			));
		}

		// Далее отрабатываем
		$vGroup = 0;
		$nGroup = 0; //Сформируем номер группы
		foreach ($list as $k => $v) {
			// Формирование подразделов
			if ($vGroup != $v['Group_id']) {
				$nGroup++;
				$vGroup = $v['Group_id'];

				//$groups[$v['Group_id']] = array(
				$groups[$nGroup] = array(
					'id' => $v['Group_id'],
					'group' => $v['Group_Name'],
					'numGroup' => $nGroup
				);
			}
			// Формирование объектов
			// $TemplAnket[$v['Parameter_id']] = array(
			$TemplAnket[$v['Number']] = array(
				'Parameter_id' => $v['Parameter_id'],
				'Group_id' => $v['Group_id'],
				'Elem_Type' => $v['Elem_Type'],
				'Parameter_Name' => $v['Parameter_Name'],
				'Spr_Cod' => $v['Spr_Cod'],
				'Global' => $v['Global'],
				'PriznSumm' => $v['PriznSumm'],
				'Number' => $v['Number'],
				'TemplJoin' => $v['TemplJoin'],
				'id' => $v['id']
			);
		}

		//Тянем весь справочник
		$aParam['CodSpr'] = '';
		$aParam['CodGroupSpr'] = '';
		$result = $this->dbmodel->ReabSpr11($aParam);

		//Тянем шапки анкет
		$aParamAnketa['Person_id'] = $data['Person_id'];
		$aParamAnketa['DirectType_id'] = $data['DirectType_id'];
		$aParamAnketa['StageType_id'] = $data['StageType_id'];

		//  echo "$aParamAnketa";
		//  echo '<pre>' . print_r($aParamAnketa, 1) . '</pre>';

		$headAnketa = $this->dbmodel->headAnketa($aParamAnketa);

		if (count($headAnketa) == 0) {
			//  echo "Должно быть так";
			$bodyAnketa = array();
		} else {
			for ($num = 0; $num < count($headAnketa); $num++) {

				if ($headAnketa[$num]['StageType_id'] == $data['StageType_id']) {
					//Запрос данных анкеты
					$abody['ReabQuestion_id'] = $headAnketa[$num]['ReabAnketa_id'];
					$result1 = $this->dbmodel->bodyAnketa($abody);

					//Универсальность
					$bodyAnketa = $this->MadeAnketaData($TemplAnket, $result1);
					break;
				}
			}
		}
		return $this->ReturnData(array(
					'groups' => $groups,
					'Element' => $TemplAnket,
					'Spr' => $result,
					'headAnketa' => $headAnketa,
					'bodyAnketa' => $bodyAnketa
		));
	}

	/**
	 * Получение диагнозов для анкеты(Травмвтология - 1 этап)
	 */
	function getDiagPerson() {
		$data = $this->ProcessInputData('getDiagPerson', false);
		if ($data === false) {
			return false;
		}
		// $Indata['Person_id'] = 2586374;
		$Indata['Person_id'] = $data['Person_id'];

		//Определение типа обработки строки диагнозов
		if (!(strpos($data['DiagMKB'], '-') === false)) 
		{
			//   echo 'Имеется -';
			//			$Indata['DiagMKB'] = "dd.Diag_Code >= '" . substr($data['DiagMKB'], 0, strpos($data['DiagMKB'], '-'))
			//	. "' and dd.Diag_Code <= '" . substr($data['DiagMKB'], strpos($data['DiagMKB'], '-')) . "'";
			$diag_code = explode("-", $data['DiagMKB']);
			//$Indata['DiagMKB'] = " dd.Diag_Code BETWEEN '" . substr($data['DiagMKB'], 0, strpos($data['DiagMKB'], '-')) ."' and '" . substr($data['DiagMKB'], strpos($data['DiagMKB'], '-')) . "'";
			$Indata['DiagMKB'] = " dd.Diag_Code BETWEEN '" . $diag_code[0] ."' and '" . $diag_code[1] . "'";
		}
		if (!(strpos($data['DiagMKB'], ',') === false)) {
			//  echo 'Имеется ,';
			//$Indata['DiagMKB'] = "dd.Diag_Code in ('J03.8','J06.9','K00.8')";
			$data['DiagMKB'] = str_replace(" ", "", $data['DiagMKB']);
			$Indata['DiagMKB'] = "dd.Diag_Code in ('" . str_replace(",", "','", $data['DiagMKB']) . "')";
			//			echo 'DiagMKB3' ;
			//			echo '<pre>' . print_r($Indata['DiagMKB'], 1) . '</pre>'; 
		}


		$list = $this->dbmodel->getDiagPerson($Indata);

		if (is_array($list)) {
			$this->ReturnData(array('data' => $list));
		} else {
			return $this->ReturnData(array(
						'success' => false,
						'Error_Msg' => toUTF('Некорректность записей в регистре реабилитации!')
			));
		}
	}

	/**
     * Формирование анкеты на дату
     */
    function loadAnketa() {
		$data = $this->ProcessInputData('loadAnketa', false);
		if ($data === false) {
			return false;
		}
		// echo 'loadAnketa' ;
		// echo '<pre>' . print_r($data, 1) . '</pre>'; 
		$list = $this->dbmodel->loadAnketa($data);
		if (is_array($list) && count($list) == 1)
		{
			// Получаем данные анкеты (из таблицы)
			$param['ReabQuestion_id'] = $list[0]['ReabAnketa_id'];
			$result1 = $this->dbmodel->bodyAnketa($param);
			//			echo 'bodyAnketa';
			//			echo '<pre>' . print_r($result1, 1) . '</pre>';
			if (is_array($result1) && count($result1) > 0) 
			{
				// загрузить шаблон + обработать данные
				//шаблон
				$templ = $this->dbmodel->CreateAnketa($data);
				foreach ($templ as $k => $v)
				{
					// Формирование объектов

					$TemplAnket[$v['Number']] = array(
						'Parameter_id' => $v['Parameter_id'],
						'Group_id' => $v['Group_id'],
						'Elem_Type' => $v['Elem_Type'],
						'Parameter_Name' => $v['Parameter_Name'],
						'Spr_Cod' => $v['Spr_Cod'],
						'Global' => $v['Global'],
						'PriznSumm' => $v['PriznSumm'],
						'Number' => $v['Number'],
						'TemplJoin' => $v['TemplJoin'],
						'id' => $v['id']
					);
				}
				//Начинаем универсальность
				$bodyAnketa = $this->MadeAnketaData($TemplAnket, $result1);
				// echo '$bodyAnketa' ;
				//  echo '<pre>' . print_r($bodyAnketa, 1) . '</pre>'; 
				return $this->ReturnData(array(
							'bodyAnketa' => $bodyAnketa
				));
			} else {
				return $this->ReturnData(array(
							'success' => false,
							'Error_Msg' => toUTF('Некорректность записей в регистре реабилитации!')
				));
			}
		} 
		else 
		{
			return $this->ReturnData(array(
						'success' => false,
						'Error_Msg' => toUTF('Некорректность записей в регистре реабилитации!')
			));
		}
	}
	/**
     * Формирование данных для анкеты
     */
	function MadeAnketaData($TemplAnket, $result1){
		//		echo "777";
		//		echo '<pre>' . print_r($result1, 1) . '</pre>';

		foreach ($result1 as $k => $v) {
			$bodyAnketa[$v['Param']] = array(
				'DataAnketa' => $v['DataAnketa']
			);
		}
		//		echo "666";
		//		echo '<pre>' . print_r($bodyAnketa, 1) . '</pre>';
		//		echo "888";
		//		echo '<pre>' . print_r($TemplAnket, 1) . '</pre>';

		//Обработка данных для GRIDов
		foreach ($TemplAnket as $ii => $jj) {

			if ($TemplAnket[$jj['Number']]['Elem_Type'] == 'Grid1' ||
					$TemplAnket[$jj['Number']]['Elem_Type'] == 'Grid8') {
				$aSplit = str_replace(";", "','", $bodyAnketa[$TemplAnket[$jj['Number']]['Parameter_id']]['DataAnketa']);
				$aGrid['cKod'] = "'" . $aSplit . "'";
				$aGrid['Elem_Type'] = $TemplAnket[$jj['Number']]['Elem_Type'];
				$result2 = $this->dbmodel->AnketaGrid($aGrid);

				$cSplit = str_replace("'", "", $aSplit);
				$mSplit = explode(",", $cSplit);
				$mGrid = array();
				foreach ($mSplit as $k => $v) {
					foreach ($result2 as $t => $z) {
						if ($z['Diag_Code'] == $v) { // Cовпало
							array_push($mGrid, $z);
							break;
						}
					}
				}

				$bodyAnketa[$TemplAnket[$jj['Number']]['Parameter_id']]['DataAnketa'] = $mGrid;
			}

			if ($TemplAnket[$jj['Number']]['Elem_Type'] == 'Grid2' || $TemplAnket[$jj['Number']]['Elem_Type'] == 'Grid7')
			{
				$aSplit = $bodyAnketa[$TemplAnket[$jj['Number']]['Parameter_id']]['DataAnketa'];
				$mSplit = explode(";", $aSplit);
				$cFilter = '';
				foreach ($mSplit as $k => $v)
				{
					$ff = strpos($v, '||');
					// echo '$ff' ;
					// echo '<pre>' . print_r($ff, 1) . '</pre>'; 
					$dd = substr($v, $ff + 2);
					$cFilter = $cFilter . "'" . $dd . "',";
				}
				if (strlen($cFilter) > 1) {
					$cFilter = substr($cFilter, 0, -1);
				}
				$aGrid1['cKod'] = $cFilter;

				$aGrid1['Elem_Type'] = $TemplAnket[$jj['Number']]['Elem_Type'];
				$result3 = $this->dbmodel->AnketaGrid($aGrid1);

				$mGrid1 = array();
				foreach ($mSplit as $k => $v) {
					//  echo '$mSplit[]' ;
					// echo '<pre>' . print_r($v, 1) . '</pre>'; 
					for ($num = 0; $num < count($result3); $num++) {
						//echo '$v' ;
						// echo '<pre>' . print_r($v, 1) . '</pre>'; 
						$ff = strpos($v, '||');

						if (substr($v, $ff + 2) == $result3[$num]['Diag_Code']) { // Cовпало
							//  echo 'Cовпало' ;
							$ff = strpos($v, '||');
							$mGrid11 = array(
								'Diag_Code' => $result3[$num]['Diag_Code'],
								'Diag_Name' => $result3[$num]['Diag_Name'],
								'Diag_id' => $result3[$num]['Diag_id'],
								'Travm_setDate' => substr($v, 0, $ff)
							);
							array_push($mGrid1, $mGrid11);
							break;
						}
					}
				}
				//   echo '$mGrid1' ;
				//   echo '<pre>' . print_r($mGrid1, 1) . '</pre>'; 
				$bodyAnketa[$TemplAnket[$jj['Number']]['Parameter_id']]['DataAnketa'] = $mGrid1;
			}

			if ($TemplAnket[$jj['Number']]['Elem_Type'] == 'Grid4') {
				$aSplit = $bodyAnketa[$TemplAnket[$jj['Number']]['Parameter_id']]['DataAnketa'];
				$mSplit = explode(";", $aSplit);
				$cFilter = '';
				foreach ($mSplit as $k => $v) {
					$ff = strpos($v, '||');
					// echo '$ff' ;
					// echo '<pre>' . print_r($ff, 1) . '</pre>'; 
					$dd = substr($v, 0, $ff);
					$cFilter = $cFilter . "'" . $dd . "',";
				}
				if (strlen($cFilter) > 1) {
					$cFilter = substr($cFilter, 0, -1);
				}

				$aGrid4['Elem_Type'] = $TemplAnket[$jj['Number']]['Elem_Type'];
				$aGrid4['cKod'] = $cFilter;
				$result4 = $this->dbmodel->AnketaGrid($aGrid4); // МКБ
				//Получаем справочник
				$aSpr['CodSpr'] = $TemplAnket[$jj['Number']]['Spr_Cod'];
				if (strpos($aSpr['CodSpr'], ";") == false) {
					//echo 'работаем далее' ;
					$aSpr['CodGroupSpr'] = '1';
					$Sprresult = $this->dbmodel->ReabSpr($aSpr);
					//Скомпонуем
					foreach ($Sprresult as $k => $v) {
						$SprAnk[$v['ReabSpr_Elem_id']] = array(
							'StageComp_id' => $v['ReabSpr_Elem_id'],
							'StageComp_Name' => $v['ReabSpr_Elem_Name'],
							'StageComp_Weight' => $v['ReabSpr_Elem_Weight']
						);
					}
					$mGrid1 = array();
					foreach ($mSplit as $t => $z) {
						for ($num = 0; $num < count($result4); $num++) {
							if (substr($z, 0, strpos($z, '||')) == $result4[$num]['Diag_Code']) {
								$mGrid12 = array(
									'Diag_Code' => $result4[$num]['Diag_Code'],
									'Diag_Name' => $result4[$num]['Diag_Name'],
									'Diag_id' => $result4[$num]['Diag_id'],
									'StageComp_id' => $SprAnk[substr($z, -1)]['StageComp_id'],
									'StageComp_Name' => $SprAnk[substr($z, -1)]['StageComp_Name'],
									'StageComp_Weight' => $SprAnk[substr($z, -1)]['StageComp_Weight']
								);

								array_push($mGrid1, $mGrid12);
								break;
							}
						}
					}
					//echo '$mGrid1' ;
					//echo '<pre>' . print_r($mGrid1, 1) . '</pre>'; 
					$bodyAnketa[$TemplAnket[$jj['Number']]['Parameter_id']]['DataAnketa'] = $mGrid1;
				} else {
					echo 'косяк';
				}
			}

			if ($TemplAnket[$jj['Number']]['Elem_Type'] == 'RGrid2')
			{
				$mGrid5 = array();
				$aSplit = $bodyAnketa[$TemplAnket[$jj['Number']]['Parameter_id']]['DataAnketa'];
				if (strlen($aSplit) > 1) 
				{
					$mSplit = explode(";", $aSplit);
					$cFilter = '';
					foreach ($mSplit as $k => $v)
					{
						if (!(strpos($v, '||') === false))
						{
							$ff = strpos($v, '||');
							// echo '$ff' ;
							// echo '<pre>' . print_r($ff, 1) . '</pre>'; 
							$dd = substr($v, $ff + 2);
							$cFilter = $cFilter . "'" . $dd . "',";
						}
						if (!(strpos($v, '`') === false))
						{
							$mDataCombo = array(
								'Data_Combo' => $v
							);
							//echo '$mDataCombo=' ;
							//echo '<pre>' . print_r($mDataCombo, 1) . '</pre>';
						}
					}

					$cFilter = substr($cFilter, 0, -1);
					// echo '$cFilter=' ;
					//     echo '<pre>' . print_r($cFilter, 1) . '</pre>';
					$aGrid5['cKod'] = $cFilter;
					$aGrid5['Elem_Type'] = $TemplAnket[$jj['Number']]['Elem_Type'];
					$result5 = $this->dbmodel->AnketaGrid($aGrid5); // МКБ

					foreach ($mSplit as $k => $v) 
					{
						for ($num = 0; $num < count($result5); $num++) {
							//echo '$v' ;
							// echo '<pre>' . print_r($v, 1) . '</pre>'; 
							$ff = strpos($v, '||');
							//echo '$v1' ;
							// echo '<pre>' . print_r(substr($v,$ff+2), 1) . '</pre>'; 
							// echo '$result[$num]' ;
							//echo '<pre>' . print_r($result3[$num]['Diag_Code'], 1) . '</pre>'; 

							if (substr($v, $ff + 2) == $result5[$num]['Diag_Code']) { // Cовпало
								//  echo 'Cовпало' ;
								$ff = strpos($v, '||');
								$mGrid11 = array(
									'Diag_Code' => $result5[$num]['Diag_Code'],
									'Diag_Name' => $result5[$num]['Diag_Name'],
									'Diag_id' => $result5[$num]['Diag_id'],
									'Travm_setDate' => substr($v, 0, $ff)
								);
								array_push($mGrid5, $mGrid11);
								break;
							}
						}
					}
					array_push($mGrid5, $mDataCombo);
				}

				$bodyAnketa[$TemplAnket[$jj['Number']]['Parameter_id']]['DataAnketa'] = $mGrid5;
			}
		}

		//        echo "777";
		//        //return $this->ReturnData($list);
		//         echo '<pre>' . print_r($bodyAnketa, 1) . '</pre>';

		return $bodyAnketa;
	}


    /**
	 * справочник для combo
	 */
	function ReabSpr() {
		// echo "555";
		$data = $this->ProcessInputData('ReabSpr', false);
		if ($data === false) {
			return false;
		}
		$aParam['CodSpr'] = $data['SprNumber'];
		$aParam['CodGroupSpr'] = $data['SprNumberGroup'];
		if ($aParam['CodSpr'] != 6 && $aParam['CodSpr'] != 7 && $aParam['CodSpr'] != 8 && $aParam['CodSpr'] != 34) {
			$list = $this->dbmodel->ReabSpr($aParam);
			return $this->ReturnData($list);
		}
		//echo "555";

		$list = $this->dbmodel->ReabSpr1($aParam);
		//echo '<pre>' . print_r($list, 1) . '</pre>';
		$this->ReturnData(array('data' => $list));
	}

	/**
	 * Поиск Профилей в справочнике для combo
	 */
	function SeekProfReab() {
		//  echo "555";
		$aParam = $this->ListParamReab();
		$data = $this->ProcessInputData('SeekProfReab', false);
		if ($data === false) {
			return false;
		}
		$list = $this->dbmodel->SeekProfReab($aParam);
		return $this->ReturnData($list);
	}

	/**
	 * Поиск причин завершения этапа в справочнике для combo
	 */
	function SeekOutCauseReab() {
		//  echo "555";
		// $aParam = $this->ListParamReab();
		$data = $this->ProcessInputData('SeekOutCauseReab', false);
		if ($data === false) {
			return false;
		}

		$list = $this->dbmodel->SeekOutCauseReab($data);
		return $this->ReturnData($list);
	}

	/**
	 * Поиск этапов реабилитации в справочнике для combo
	 */
    function SeekStageReab() {
		//  echo "555";
		// $aParam = $this->ListParamReab();
		$data = $this->ProcessInputData('SeekStageReab', false);
		if ($data === false) {
			return false;
		}
		$list = $this->dbmodel->SeekStageReab($data);
		return $this->ReturnData($list);
	}
	
	/**
	 * Закачка всех определителей для ICF
	 */
    function ICFSpr() {
		//  echo "555";
		// $aParam = $this->ListParamReab();
		$data = $this->ProcessInputData('ICFSpr', false);
		if ($data === false) {
			return false;
		}
		$list1 = $this->dbmodel->ICFSeverity($data);
		$list2 = $this->dbmodel->ICFNature($data);
		$list3 = $this->dbmodel->ICFLocalization($data);
		$list4 = $this->dbmodel->ICFEnvFactors($data);
		
		return $this->ReturnData(array(
					'ICFSeverity' => $list1,
					'ICFNature' => $list2,
					'ICFLocalization' => $list3,
					'ICFEnvFactors' => $list4
		));
		//return $this->ReturnData($list);
	}
	

	/**
	 * Завершение этапа с контролем даты завершения
	 */
	function CloseRegistrStage()
    {
		$data = $this->ProcessInputData('CloseRegistrStage', true);
		if ($data === false) {
			return false;
		}
		//Проверка на оценку по ICF и причина смерть
		if($data['ReabOutCause_id'] != 3)
 {
			$recordsICF = $this->dbmodel->checkrecordsICF($data);
			if (count($recordsICF) > 0) {
				return $this->ReturnData(array(
							'success' => false,
							'Error_Msg' => toUTF('Закрытие этапа невозможно! Не проведена оценка состояния пациента по МКФ перед выпиской!')
				));
			} 
		}

		$checkPersonReabRegister = $this->dbmodel->checkPersonReabRegister($data);
		$nRec = count($checkPersonReabRegister);
		if ($nRec === 0) {
			return $this->ReturnData(array(
						'success' => false,
						'Error_Msg' => toUTF('У пациента отсутствует указанный профиль реабилитации!')
			));
		} else {
			//перебираем массив - опеределяем актуальные случаи
			$nKolOpen = 0;
			for ($i = 0; $i < $nRec; $i++) {
				if ($checkPersonReabRegister[$i]['ReabOutCause_id'] == 0) {
					$nKolOpen ++;
				}
			}
			switch ($nKolOpen) {
				case 0: //нет открытых этапов
					return $this->ReturnData(array(
								'success' => false,
								'Error_Msg' => toUTF('у пациента по данному профилю нет открытых этапов реабилитации!')
					));
					break;
				case 1:
					if ($checkPersonReabRegister[0]['ReabOutCause_id'] == 0) {
						//echo 'данные=';
						//echo '<pre>' . print_r($checkPersonReabRegister[0]['ReabRegister_Stage'], 1) . '</pre>';
						//echo '<pre>' . print_r($data['ReabRegister_Stage'], 1) . '</pre>';
						if ($checkPersonReabRegister[0]['ReabStageType_id'] == $data['StageType_id']) 
						{
							//Готовим дату
							if (is_object($data['ReabEvent_disDate'])) 
							{
								//echo 'ReabRegister_disDate объект';
								$d1 = $data['ReabEvent_disDate'];
								$data['ReabRegister_Date'] = '';
							} else 
							{
								//echo 'ReabRegister_disDate не объект';
								$d1 = new DateTime($data['ReabEvent_disDate']);
								$data['ReabRegister_Date'] = $data['ReabEvent_disDate'];
							}
							if ($d1 >= $checkPersonReabRegister[0]['ReabEvent_setDate']) 
							{
								//echo 'Будем думать';
								$data['ReabEvent_id'] = $checkPersonReabRegister[0]['ReabEvent_id'];
								$Result = $this->dbmodel->CloseRegistrStage($data);
								if (is_array($Result))
								{
									if (isset($Result[0]['Error_Code']))
									{
										return $this->ReturnData(array(
											'success' => false,
											'Error_Msg' => toUTF($Result[0]['Error_Message'])
												));
									}
									else
									{
										//echo 'нет ошибки ';
										return $this->ReturnData(array(
													'success' => true,
													'Error_Msg' => toUTF('Все в норме!')
										));
									}
								}
							} 
							else 
							{
								return $this->ReturnData(array(
											'success' => false,
											'Error_Msg' => toUTF('Дата закрытия этапа реабилитации меньше даты открытия этапа!')
								));
								break;
							}
						} 
						else 
						{
							return $this->ReturnData(array(
										'success' => false,
										'Error_Msg' => toUTF('у пациента по данному профилю нет открытого ' . $data['StageName'] . ' этапа реабилитации!')
							));
							break;
						}
					} 
					else 
					{
						return $this->ReturnData(array(
									'success' => false,
									'Error_Msg' => toUTF('Ошибка в реестре реабилитации!')
						));
					}
					break;
				default: // ошибка в базе
					return $this->ReturnData(array(
								'success' => false,
								'Error_Msg' => toUTF('Ошибка в реестре реабилитации!')
					));
					break;
			}
		}
	}

	/**
	 * Отмена завершения этапа
	 */
	function CanselCloseStage()
	{
		$data = $this->ProcessInputData('CanselCloseStage', true);
		if ($data === false) {
			return false;
		}
		$Result = $this->dbmodel->CanselCloseStage($data);
		if (is_array($Result)) {
			if (isset($Result[0]['Error_Code'])) {
				return $this->ReturnData(array(
							'success' => false,
							'Error_Msg' => toUTF($Result[0]['Error_Message'])
				));
			} else {
				//echo 'нет ошибки ';
				return $this->ReturnData(array(
							'success' => true,
							'Error_Msg' => toUTF('Все в норме!')
				));
			}
		}
	}
	
	/**
	 * Добавление профилей и этапов с контролем имеющегося
	 */
	function AddRegistrProfStage() {

		$data = $this->ProcessInputData('AddRegistrProfStage', true);
		if ($data === false) {
			return false;
		}
		//          echo " DirectType_id= ";
		//        echo '<pre>' . print_r($data['DirectType_id'], 1) . '</pre>';
		// Подумать над пересечением периодов по одному профилю
		$checkPersonReabRegister = $this->dbmodel->checkPersonReabRegister($data);
		$nRec = count($checkPersonReabRegister);

		if ($nRec === 0) {
			// echo 'Пришли  на первый Insert';
			//Параметр для определения ID регистра
			$aParam = $this->ListParamReab();
			$Result = $this->dbmodel->saveInReabRegister($data);

			if (is_array($Result)) {
				if (isset($response[0]['Error_Code'])) {
					//echo 'это ошибка ';
					return $this->ReturnData(array(
								'success' => false,
								'Error_Msg' => toUTF('Ошибка записи в регистр реабилитации!')
					));
				} else {
					//echo 'нет ошибки ';
					return $this->ReturnData(array(
								'success' => true,
								'Error_Msg' => toUTF('Все в норме!')
					));
				}
			}
			if (is_string($Result)) {
				return $this->ReturnData(array(
							'success' => false,
							'Error_Msg' => toUTF($Result)
				));
			}
		} else {
			//перебираем массив - опеределяем актуальные случаи
			$nKolOpen = 0;
			$nKolClose = 0;
			for ($i = 0; $i < $nRec; $i++) {
				if ($checkPersonReabRegister[$i]['ReabOutCause_id'] == 0) {
					$nKolOpen ++;
				} else {
					$nKolClose ++;
				}
			}

			switch ($nKolOpen) {
				case 0: //анализ дат
					//echo '<pre>' . print_r($checkPersonReabRegister[0]['ReabRegister_disDate'], 1) . '</pre>';
					if (is_object($data['ReabEvent_setDate'])) {
						// echo 'ReabRegister_setDate объект';
						$d1 = new DateTime($data['ReabEvent_setDate']->format('Y-m-d H:i:s'));
					} else {
						//echo 'ReabRegister_setDate не объект';
						$d1 = new DateTime($data['ReabEvent_setDate']);
					}

					if ($d1 > $checkPersonReabRegister[0]['ReabEvent_disDate']) {
						//Добавляем запись
						$Result = $this->dbmodel->saveInReabRegister($data);
						if (is_array($Result)) {
							if (isset($response[0]['Error_Code'])) {
								//echo 'это ошибка ';
								return $this->ReturnData(array(
											'success' => false,
											'Error_Msg' => toUTF('Ошибка записи в регистр реабилитации!')
								));
							} else {
								//echo 'нет ошибки ';
								return $this->ReturnData(array(
											'success' => true,
											'Error_Msg' => toUTF('Все в норме!')
								));
							}
						}
						if (is_string($Result)) {
							return $this->ReturnData(array(
										'success' => false,
										'Error_Msg' => toUTF($Result)
							));
						}
					} else {
						if ($d1 == $checkPersonReabRegister[0]['ReabEvent_disDate']) {
							//Сообщение
							return $this->ReturnData(array(
										'success' => false,
										'Error_Msg' => toUTF('Дата открытия этапа равна дате закрытого предыдущего этапа реабилитации!')
							));
						} else {
							//Сообщение
							return $this->ReturnData(array(
										'success' => false,
										'Error_Msg' => toUTF('Дата открытия этапа меньше даты закрытого предыдущего этапа реабилитации!')
							));
						}
					}
					break;
				case 1: //2 сообщения
					if ($checkPersonReabRegister[0]['ReabStageType_id'] == $data['StageType_id']) {
						//echo 'Сообщить, что этап уже открыт';
						return $this->ReturnData(array(
									'success' => false,
									'Error_Msg' => toUTF('Данный пациент уже проходит данный этап реабилитации!')
						));
					} else {
						return $this->ReturnData(array(
									'success' => false,
									'Error_Msg' => toUTF('Для открытия ' . $data['StageName'] . ' этапа реабилитации пациенту необходимо закрыть ' . $checkPersonReabRegister[0]['StageName'] . ' этап реабилитации!')
						));
					}
					break;
				default : // Косяк
					return $this->ReturnData(array(
								'success' => false,
								'Error_Msg' => toUTF('Проблема в регистре реабилитации!')
					));
					break;
			}
		}
	}

	/**
	 * Поиск пациента в регистре (по PersonRegister)
	 */
	function SeekRegistr() {
		$data = $this->ProcessInputData('SeekRegistr', true);

		if ($data === false) {
			return false;
		}

		//$aParam = $this->ListParamReab(); 
		$params['Person_id'] = $data['Person_id'];
		// $params['RegisterSysNick'] = $aParam['RegisterSysNick'];
		$checkPersonInRegister = $this->dbmodel->checkPersonInRegister($params);
		if ($checkPersonInRegister === true) {
			//   echo 'имеется персонаж';
			return $this->ReturnData(array(
						'success' => false,
						'Error_Msg' => toUTF('Данный пациент уже присутствует в регистре реабилитации!')
			));
		} else {
			//   echo 'отсутствует персонаж';
			return $this->ReturnData(array(
						'success' => true,
						'Error_Msg' => toUTF('Все хорошо')
			));
		}
	}

	/**
	 * Включение пациента в регистр реабилитации
	 */
	function saveInReabRegister() {

		$data = $this->ProcessInputData('saveInReabRegister', true);
		if ($data === false) {
			return false;
		}
		$aParam = $this->ListParamReab();
		// $OutParams['RegisterSysNick'] = $aParam['RegisterSysNick'];
		//Дата постановки
		if (is_object($data['ReabEvent_setDate']) && get_class($data['ReabEvent_setDate']) == 'DateTime') {
			$OutParams['ReabEvent_setDate'] = $data['ReabEvent_setDate']->format('Y-m-d H:i:s');
			//echo 'это 1';
		} else if (!empty($data['ReabEvent_setDate'])) {
			$OutParams['ReabEvent_setDate'] = $data['ReabEvent_setDate'];
		};
		$OutParams['Person_id'] = $data['Person_id'];
		$OutParams['DirectType_id'] = $data['DirectType_id'];
		$OutParams['StageType_id'] = $data['StageType_id'];
		$OutParams['MedPersonal_iid'] = $data['MedPersonal_iid'];
		$OutParams['Lpu_iid'] = $data['Lpu_iid'];
		$OutParams['pmUser_id'] = $data['pmUser_id'];
		//sql_log_message('error','QWERTY exec query: ',getDebugSql($query, $params));
		//запись в ReabEvent
		$response = $this->dbmodel->saveInReabRegister($OutParams);
		//echo 'отлавливаем ошибку2 = ';
		//   echo '<pre>' . print_r($response, 1) . '</pre>';


		if (is_array($response)) {
			// echo '<pre>' . print_r($response[0]['Error_Code'], 1) . '</pre>';
			if (isset($response[0]['Error_Code'])) {
				//echo 'это ошибка ';
				return $this->ReturnData(array(
							'success' => false,
							'Error_Msg' => toUTF('Ошибка записи в регистр реабилитации!')
				));
			} else {
				//echo 'нет ошибки ';
				return $this->ReturnData(array(
							'success' => true,
							'Error_Msg' => toUTF('Все в норме!')
				));
			}
		}
		return $response;
	}

	/**
	 * Получение списка проведенных тестов
	 */
	function getListTestUserReab() {
		$data = $this->ProcessInputData('getListTestUserReab', false);

		if ($data === false) {
			return false;
		}

		$dataIn['Person_id'] = $data['Person_id'];
		$dataIn['DirectType_id'] = $data['DirectType_id'];
		$dataIn['StageType_id'] = $data['StageType_id'];
		//echo 'Person_id=';
		//echo '<pre>' . print_r($dataIn['Person_id'], 1) . '</pre>';

		$list = $this->dbmodel->getListTestUserReab($dataIn);
		//echo '$list=';
		//echo '<pre>' . print_r($list, 1) . '</pre>';

		return $this->ReturnData($list);
	}

	/**
	 * Получение списка проведенных измерений ЧСС
	 */
	function getListHeartRateUserReab() {
		$data = $this->ProcessInputData('getListHeartRateUserReab', false);

		if ($data === false) {
			return false;
		}

		$dataIn['Person_id'] = $data['Person_id'];
		$dataIn['DirectType_id'] = $data['DirectType_id'];
		$dataIn['StageType_id'] = $data['StageType_id'];
		//echo 'Person_id=';
		//echo '<pre>' . print_r($dataIn['Person_id'], 1) . '</pre>';

		$list = $this->dbmodel->getListHeartRateUserReab($dataIn);
		//         echo '$list=';
		//        echo '<pre>' . print_r($list, 1) . '</pre>';

		return $this->ReturnData($list);
	}

	/**
	 * Получение Шапок всех анкет по профилю
	 */
	function getListScalesDirectCurrentUser() {
		$data = $this->ProcessInputData('getListScalesDirectCurrentUser', false);

		if ($data === false) {
			return false;
		}

		$dataIn['Person_id'] = $data['Person_id'];
		$dataIn['DirectType_id'] = $data['DirectType_id'];


		$list = $this->dbmodel->getListScalesDirectCurrentUser($dataIn);
		//		echo '$list=';
		//		echo '<pre>' . print_r($list, 1) . '</pre>';

		return $this->ReturnData($list);
	}
	/**
	 * Получение списка предметов наблюдения для конкретного пациента
	 */
	function getListObjectsCurrentUser() {
		$data = $this->ProcessInputData('getListObjectsCurrentUser', false);

		if ($data === false) {
			return false;
		}
		$aParam = $this->ListParamReab();
		$dataIn['Profil'] = $aParam['Profil'];
		$dataIn['Person_id'] = $data['Person_id'];

		$list = $this->dbmodel->getListObjectsCurrentUser($dataIn);

		return $this->ReturnData($list);
	}
	
	/**
	 * Возвращает данные для дерева справочника ICF
	 * @return bool
	 */
	function getICFTreeData()
	{
		$data = $this->ProcessInputData('getICFTreeData',false);
		if ($data === false) {return false;}

		$response = $this->dbmodel->getICFTreeData($data);

		$this->ProcessModelList($response,true,true)->ReturnData();
		return true;
	}
	/**
	 * Работа с таблицей ReabICFRating (Оценка состояния здоровья по ICF - удаление)
	 */
	function DeleteICFRating()
	{
		$data = $this->ProcessInputData('DeleteICFRating',true);
		if ($data === false) {return false;}
		$response = $this->dbmodel->DeleteICFRating($data);

		if (is_array($response))
		{
			if (isset($response[0]['Error_Code']))
			{
				return $this->ReturnData(array(
							'success' => false,
							'Error_Msg' => toUTF($response[0]['Error_Message'])
				));
			}
			else
			{
				//  echo 'нет ошибки ';
				// переделать -- прокинуть шапки анкет
				return $this->ReturnData(array(
							'success' => true,
							'Error_Msg' => toUTF('Все в норме!')
				));
			}
		}
		else
		{
			return $response;
		}
	}
	/**
	 * Работа с таблицей ReabICFRating (Оценка состояния здоровья по ICF - добавление, редактирование)
	 */
	function SaveICFRating()
	{
		$data = $this->ProcessInputData('SaveICFRating',true);
		if ($data === false) {return false;}
		
		//Контроль одного домена на 1 день
		$data_con['ICFRating_setDate'] = $data['ICFRating_setDate'];
		$data_con['ICF_id'] = $data['ICF_id'];
		$data_con['Person_id'] = $data['Person_id'];
		$data_con['ReabEvent_id'] = $data['ReabEvent_id'];
		$data_con['ReabICFRating_id'] = $data['ReabICFRating_id'];
		$data_con['Func'] = $data['Func'];
		
		$list = $this->dbmodel->contrICF($data_con);

		//		echo '<pre>' . print_r($list, 1) . '</pre>';
		if (is_array($list)) {
			if ($list[0]['nKol'] > 0) 
			{
				return $this->ReturnData(array(
							'success' => false,
							'Error_Msg' => "На указанную дату " . $data['ICFRating_setDate'] . " по выбранному домену оценка уже имеется!"
				));
			}
		} else 
		{
			return $this->ReturnData(array(
						'success' => false,
						'Error_Msg' => "Ошибка в БД!"
			));
		}

		$response = $this->dbmodel->SaveICFRating($data);

		if (is_array($response))
		{
			if (isset($response[0]['Error_Code']))
			{
				return $this->ReturnData(array(
							'success' => false,
							'Error_Msg' => toUTF($response[0]['Error_Message'])
				));
			}
			else
			{
				//  echo 'нет ошибки ';
				// переделать -- прокинуть шапки анкет
				return $this->ReturnData(array(
							'success' => true,
							'Error_Msg' => toUTF('Все в норме!')
				));
			}
		}
		else
		{
			// echo 'Что-то такое ';
			return $response;
		}
	}
	/**
	 * Получение списка оценок по ICF
	 */
	function getListICF_Verdict() {
		$data = $this->ProcessInputData('getListICF_Verdict', false);

		if ($data === false) {
			return false;
		}
		
		$list = $this->dbmodel->getListICF_Verdict($data);

		return $this->ReturnData($list);
	}
	
	/**
	 * Получение списка оценок по ICF для реабилитационного диагноза
	 */
	function getReabDiagICF() {
		$data = $this->ProcessInputData('getReabDiagICF', false);

		if ($data === false) {
			return false;
		}

		$list = $this->dbmodel->getReabDiagICF($data);

		return $this->ReturnData($list);
	}

}

<?php
defined("BASEPATH") or die ("No direct script access allowed");
require_once('EvnPLDispAbstract_model.php');
/**
 * EvnPLDispDop13_model - модель для работы с талонами по диспансеризации взрослого населения
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @author       Dmitry Vlasenko
 * @originalauthor	Petukhov Ivan aka Lich (megatherion@list.ru) / Stas Bykov aka Savage (savage1981@gmail.com)
 * @version      16.05.2013
 *
 * @property EvnDiagDopDisp_model $evndiagdopdisp
 * @property HeredityDiag_model $hereditydiag
 * @property ProphConsult_model $prophconsult
 * @property NeedConsult_model $needconsult
 * @property Person_model $pmodel
 */

class EvnPLDispDop13_model extends EvnPLDispAbstract_model
{
	/**
	 *	Конструктор
	 */	
    function __construct()
    {
        parent::__construct();

		$this->inputRules = array(
			'checkDispClass2Volume' => array(

			),
			'getAllowedLpuAttachIds' => array(

			),
			'loadEvnUslugaDispDopTransferFailGrid' => array(
				array(
					'field' => 'EvnPLDispDop13_id',
					'label' => 'Идентификатор талона по доп. диспансеризации',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'checkPersonInWowRegistry' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadEvnUslugaDispDopTransferSuccessGrid' => array(
				array(
					'field' => 'EvnPLDispDop13_id',
					'label' => 'Идентификатор талона по доп. диспансеризации',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'checkPersons' => array(
				array(
					'field' => 'personIds',
					'label' => 'Идентификаторы пациентов',
					'rules' => '',
					'type' => 'string'
				)
			),
			'getDopDispInfoConsentChanges' => array(
				array(
					'field' => 'EvnPLDispDop13_id',
					'label' => 'Идентификатор талона по доп. диспансеризации',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispDop13_fid',
					'label' => 'Идентификатор карты предыдущего этапа',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispDop13_IsNewOrder',
					'label' => 'Признак переопределения услуг по новому приказу',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
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
					'field' => 'EvnPLDispDop13_consDate',
					'label' => 'Дата согласия/отказа',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'EvnPLDispDop13_newConsDate',
					'label' => 'Дата согласия/отказа новая',
					'rules' => 'required',
					'type' => 'date'
				)
			),
			'loadDopDispInfoConsent' => array(
				array(
					'field' => 'EvnPLDispDop13_id',
					'label' => 'Идентификатор талона по доп. диспансеризации',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispDop13_fid',
					'label' => 'Идентификатор карты предыдущего этапа',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispDop13_IsNewOrder',
					'label' => 'Признак переопределения услуг по новому приказу',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
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
					'field' => 'EvnPLDispDop13_consDate',
					'label' => 'Дата согласия/отказа',
					'rules' => 'required',
					'type' => 'date'
				)
			),
			'saveDopDispQuestionGrid' => array(
				array(
					'field' => 'EvnPLDisp_id',
					'label' => 'Идентификатор талона по доп. диспансеризации',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_uid',
					'label' => 'Отделение',
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
					'field' => 'DopDispDiagType_id',
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
				),
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Идентификатор услуги',
					'rules' => '',
					'type' => 'id'
				)
			),
			'saveDopDispQuestions' => array(
				array(
					'field' => 'EvnPLDisp_id',
					'label' => 'Идентификатор талона по доп. диспансеризации',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_uid',
					'label' => 'Отделение',
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
					'field' => 'DopDispDiagType_id',
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
					'field' => 'DopDispQuestionData',
					'label' => 'Данные анкетирования',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'DopDispQuestion_setDate',
					'label' => 'Дата',
					'rules' => '',
					'type' => 'date'
				)
			),
			'getDopDispInfoConsentFormDate' => array(
				array(
					'field' => 'EvnPLDispDop13_id',
					'label' => 'Идентификатор талона по доп. диспансеризации',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'saveDopDispInfoConsent' => array(
				array(
					'field' => 'EvnPLDispDop13_id',
					'label' => 'Идентификатор талона по доп. диспансеризации',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DispClass_id',
					'label' => 'Идентификатор этапа',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'PayType_id',
					'label' => 'Вид оплаты',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispDop13_fid',
					'label' => 'Идентификатор карты предыдущего этапа',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispDop13_IsNewOrder',
					'label' => 'Признак переопределения услуг по новому приказу',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_mid',
					'label' => 'МО мобильной бригады',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispDop13_IsMobile',
					'label' => 'Обслужен мобильной бригадой',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'EvnPLDispDop13_IsOutLpu',
					'label' => 'Проведён вне МО',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'EvnPLDispDop13_consDate',
					'label' => 'Дата подписания согласия/отказа',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'PersonEvn_id',
					'label' => 'Идентификатор человека в событии',
					'rules' => 'trim|required',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => 'trim|required',
					'type' => 'id'
				),
				array(
					'field' => 'Server_id',
					'label' => 'Server_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DopDispInfoConsentData',
					'label' => 'Данные грида по информир. добр. согласию',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'AttachmentAnswer',
					'label' => 'Прикреплению пациента к МО',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadEvnPLDispDop13EditForm' => array(
				array(
					'field' => 'EvnPLDispDop13_id',
					'label' => 'Идентификатор талона по доп. диспансеризации',
					'rules' => 'trim|required',
					'type' => 'id'
				),
				array(
					'field' => 'isExt6',
					'label' => 'Данные для новой версии интерфейса',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadEvnVizitDispDopEditForm' => array(
				array(
					'field' => 'EvnVizitDispDop_id',
					'label' => 'Идентификатор посещения по доп. диспансеризации',
					'rules' => 'trim|required',
					'type' => 'id'
				)
			),
			'deleteEvnPLDispDop13' => array(
				array(
					'field' => 'EvnPLDispDop13_id',
					'label' => 'Идентификатор талона по доп. диспансеризации',
					'rules' => 'trim|required',
					'type' => 'id'
				),
				array(
					'field' => 'ignoreCheckRegistry',
					'label' => 'Игнорирование проверок на наличие в реестре',
					'rules' => '',
					'type' => 'int'
				)
			),
			'checkIfEvnPLDispDop13Exists' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => 'trim|required',
					'type' => 'id'
				)
			),
			'loadEvnVizitDispDopGrid' => array(
				array(
					'field' => 'EvnPLDispDop13_id',
					'label' => 'Идентификатор талона по доп. диспансеризации',
					'rules' => 'trim|required',
					'type' => 'id'
				)
			),
			'loadEvnUslugaDispDopGrid' => array(
				array(
					'field' => 'EvnPLDispDop13_id',
					'label' => 'Идентификатор талона по доп. диспансеризации',
					'rules' => 'trim|required',
					'type' => 'id'
				)
			),
			'searchEvnPLDispDop13' => array(
				array(
					'field' => 'DocumentType_id',
					'label' => 'Тип документа удостовряющего личность',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispDop13_disDate',
					'label' => 'Дата завершения случая',
					'rules' => 'trim',
					'type' => 'daterange'
				),
				array(
					'field' => 'EvnPLDispDop13_IsFinish',
					'label' => 'Случай завершен',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispDop13_setDate',
					'label' => 'Дата начала случая',
					'rules' => 'trim',
					'type' => 'daterange'
				),
				array(
					'field' => 'OMSSprTerr_id',
					'label' => 'Территория страхования',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'Org_id',
					'label' => 'Место работы',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'OrgDep_id',
					'label' => 'Организация выдавшая документ',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'OrgSmo_id',
					'label' => 'Страховая компания',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'PersonAge_Min',
					'label' => 'Возраст с',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'PersonAge_Max',
					'label' => 'Возраст по',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'PersonCard_Code',
					'label' => 'Номер амб. карты',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'LpuRegion_id',
					'label' => 'Участок',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'Person_Birthday',
					'label' => 'Дата рождения',
					'rules' => 'trim',
					'type' => 'daterange'
				),
				array(
					'field' => 'Person_Surname',
					'label' => 'Фамилия',
					'rules' => 'trim',
					'type' => 'russtring'
				),
				array(
					'field' => 'Person_Firname',
					'label' => 'Имя',
					'rules' => 'trim',
					'type' => 'russtring'
				),
				array(
					'field' => 'Person_Secname',
					'label' => 'Отчество',
					'rules' => 'trim',
					'type' => 'russtring'
				),
				array(
					'field' => 'Person_Snils',
					'label' => 'СНИЛС',
					'rules' => 'trim',
					'type' => 'snils'
				),
				array(
					'field' => 'PolisType_id',
					'label' => 'Тип полиса',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'Post_id',
					'label' => 'Должность',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'PrivilegeType_id',
					'label' => 'Категория льготы',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'Sex_id',
					'label' => 'Пол',
					'rules' => 'trim',
					'type' => 'id',
					'default' => -1
				),
				array(
					'field' => 'SocStatus_id',
					'label' => 'Социальный статус',
					'rules' => 'trim',
					'type' => 'id'
				),
			),
			'saveEvnPLDispDop13' => array(
				array(
					'label' => 'Систолическое АД (мм рт.ст.)',
					'field' => 'systolic_blood_pressure',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'label' => 'Диастолическое АД (мм рт.ст.)',
					'field' => 'diastolic_blood_pressure',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'label' => 'Вес (кг)',
					'field' => 'person_weight',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'label' => 'Рост (см)',
					'field' => 'person_height',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'label' => 'Окружность талии (см)',
					'field' => 'waist_circumference',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'label' => 'Индекс массы тела (кг/м2)',
					'field' => 'body_mass_index',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'EvnPLDispDop13_id',
					'label' => 'Идентификатор карты',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispDop13_IsNewOrder',
					'label' => 'Признак переопределения услуг по новому приказу',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispDop13_IndexRep',
					'label' => 'Признак повторной подачи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnPLDispDop13_IndexRepInReg',
					'label' => 'Признак повторной подачи в реестре',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnPLDispDop13_Percent',
					'label' => 'Общее количество осмотров / исследований (%)',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'DispClass_id',
					'label' => 'Идентификатор этапа',
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
					'field' => 'EvnPLDispDop13_fid',
					'label' => 'Идентификатор карты предыдущего этапа',
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
					'field' => 'Lpu_mid',
					'label' => 'МО мобильной бригады',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispDop13_IsMobile',
					'label' => 'Обслужен мобильной бригадой',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'EvnPLDispDop13_IsOutLpu',
					'label' => 'Проведён вне МО',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'EvnPLDispDop13_IsShortCons',
					'label' => 'Проведено индивидуальное краткое профилактическое консультирование',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispDop13_disDate',
					'label' => 'Дата окончания случая',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'Server_id',
					'label' => 'Server_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnPLDispDop13_IsStenocard',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispDop13_IsBrain',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispDop13_IsDoubleScan',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispDop13_IsTub',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispDop13_IsTIA',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispDop13_IsRespiratory',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispDop13_IsLungs',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispDop13_IsTopGastro',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispDop13_IsBotGastro',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispDop13_IsSpirometry',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispDop13_IsHeartFailure',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispDop13_IsOncology',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispDop13_IsEsophag',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispDop13_IsSmoking',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispDop13_IsRiskAlco',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispDop13_IsAlcoDepend',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispDop13_IsLowActiv',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispDop13_IsIrrational',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispDop13_IsUseNarko',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispDop13_IsDisp',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'NeedDopCure_id',
					'label' => 'Признак необходимости дополнительного лечения (обследования)',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'CardioRiskType_id',
					'label' => 'Риск сердечно-сосудистых заболеваний',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispDop13_IsStac',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispDop13_IsSanator',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispDop13_SumRick',
					'label' => 'Риск',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'RiskType_id',
					'label' => 'Тип риска',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispDop13_IsSchool',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispDop13_IsProphCons',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispDop13_IsHypoten',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispDop13_IsLipid',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispDop13_IsHypoglyc',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'HealthKind_id',
					'label' => 'Группа здоровья',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispDop13_IsEndStage',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispDop13_IsTwoStage',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Diag_id',
					'label' => 'Подозрение на хроническое неинфекционное заболевание, требующее дообследования',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Diag_sid',
					'label' => 'Подозрение на некоторые инфекционные и паразитарные болезни',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnCostPrint_setDT',
					'label' => 'Дата выдачи справки/отказа',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'EvnCostPrint_IsNoPrint',
					'label' => 'Отказ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispDop13_IsSuspectZNO',
					'label' => 'Подозрение на ЗНО',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Diag_spid',
					'label' => 'Подозрение на диагноз',
					'rules' => '',
					'type' => 'int'
				),
				array('field' => 'DispAppointData', 'label' => 'JSON-массив назначений', 'rules' => '', 'type' => 'json_array', 'assoc' => true),
				array(
					'field' => 'checkAttributeforLpuSection',
					'label' => 'Признак атрибута у отделения',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadEvnPLDispDop13StreamList' => array(
				array(
					'field' => 'begDate',
					'label' => 'Дата',
					'rules' => 'trim|required',
					'type' => 'date'
				),
				array(
					'field' => 'begTime',
					'label' => 'Время',
					'rules' => 'trim|required',
					'type' => 'string'
				)
			),
			'loadEvnPLDispDop13ForPerson' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'printEvnPLDispDop13' => array(
				array(
					'field' => 'EvnPLDispDop13_id',
					'label' => 'Идентификатор талона по ДД',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'blank_only',
					'label' => 'Флаг бланка',
					'rules' => '',
					'type' => 'int'
				)
			),
			'printEvnPLDispDop13Passport' => array(
				array(
					'field' => 'EvnPLDispDop13_id',
					'label' => 'Идентификатор талона по ДД',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'printDiag',
					'label' => 'Параметр для печати диагнозов в п.10',
					'rules' => '',
					'type'  => 'id'
				)
			),
			'getEvnPLDispDop13Years' => array(
				array(
					'field' => 'EvnPLDispDop13_IsTwoStage',
					'label' => 'Признак второго этапа',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getEvnPLDispDop13YearsSec' => array(
				array(
					'field' => 'EvnPLDispDop13_IsTwoStage',
					'label' => 'Признак второго этапа',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'get2018Dvn185Volume' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Пациент',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'onDate',
					'label' => 'Дата',
					'rules' => 'required',
					'type' => 'date'
				)
			),
			'CheckDiag' => array(
				array(
					'field' => 'EvnPLDispDop13_id',
					'label' => 'Идентификатор талона по ДД',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Diag_id',
					'label' => 'Идентификатор диагноза',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'DeseaseDispType_id',
					'label' => 'Характер заболевания',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaDispDop_id',
					'label' => 'Идентификатор услуги по диспансеризации',
					'rules' => '',
					'type' => 'id'
				),
			),
			'loadEvnPLDispDop13PrescrList' => array(
				array('field' => 'EvnPLDispDop13_id', 'label' => 'Идентификатор ДВН', 'rules' => 'trim|required', 'type' => 'id'),
				array('field' => 'UslugaComplexList', 'label' => 'Список услуг для назначений', 'rules' => '', 'type' => 'string'),
				array('field' => 'userLpuSection_id', 'label' => 'Идентификатор отделения пользователя', 'rules' => 'required', 'type' => 'int'),
			),
			//yl:факторы риска
			'loadEvnPLDispDop13FactorType' => array(//yl:меню
				array('field' => 'EvnPLDispDop13_id', 'label' => 'Идентификатор ДВН', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'EvnPLDispDop13_fid', 'label' => 'Идентификатор ДВН первого этапа', 'rules' => '', 'type' => 'id')
			),
			'loadEvnPLDispDop13FactorRisk' => array(//yl:грид
				array('field' => 'EvnPLDispDop13_id', 'label' => 'Идентификатор ДВН', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'EvnPLDispDop13_fid', 'label' => 'Идентификатор ДВН первого этапа', 'rules' => '', 'type' => 'id')
			),
			'addEvnPLDispDop13FactorRisk' => array(//yl:добавить
				array('field' => 'EvnPLDispDop13_id', 'label' => 'Идентификатор ДВН', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'RiskFactorType_id', 'label' => 'Идентификатор Типа Фактора Риска', 'rules' => 'required', 'type' => 'id'),
			),
			'delEvnPLDispDop13FactorRisk' => array(//yl:удалить
				array('field' => 'DispRiskFactor_id', 'label' => 'Идентификатор фактора риска', 'rules' => 'required', 'type' => 'id'),
			),
			//yl:подозрения
			'loadEvnPLDispDop13DispDeseaseSuspType' => array(//yl:меню Подозрения
				array('field' => 'EvnPLDispDop13_id', 'label' => 'Идентификатор ДВН', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'EvnPLDispDop13_fid', 'label' => 'Идентификатор ДВН первого этапа', 'rules' => '', 'type' => 'id')
			),
			'loadEvnPLDispDop13Desease' => array(//yl:грид - общий на Подозрения и Заболевания
				array('field' => 'EvnPLDispDop13_id', 'label' => 'Идентификатор ДВН', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'EvnPLDispDop13_fid', 'label' => 'Идентификатор ДВН первого этапа', 'rules' => '', 'type' => 'id')
			),
			'addEvnPLDispDop13DispDeseaseSuspType' => array(//yl:добавить Подозрения
				array('field' => 'EvnPLDispDop13_id', 'label' => 'Идентификатор ДВН', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'DispDeseaseSuspType_id', 'label' => 'Идентификатор Подозрения', 'rules' => 'required', 'type' => 'id'),
			),
			'delEvnPLDispDop13DispDeseaseSusp' => array(//yl:удалить Подозрения
				array('field' => 'DispDeseaseSusp_id', 'label' => 'Идентификатор Подозрения', 'rules' => 'required', 'type' => 'id'),
			),
			//yl:заболевания
			'addEvnPLDispDop13EvnDiagDopDisp' => array(//yl:добавить Диагноз
				array('field' => 'EvnPLDispDop13_id', 'label' => 'Идентификатор ДВН', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Diag_id', 'label' => 'Идентификатор Диагноза', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'PersonEvn_id', 'label' => 'Идентификатор Пациента', 'rules' => 'required', 'type' => 'id'),
			),
			'delEvnPLDispDop13EvnDiagDopDisp' => array(//yl:удалить Диагноз
				array('field' => 'EvnDiagDopDisp_id', 'label' => 'Идентификатор Диагноза в связке', 'rules' => 'required', 'type' => 'id'),
			),
			'updEvnPLDispDop13DeseaseDiagSetClass' => array(//yl:изменить Тип Диагноза
				array('field' => 'EvnDiagDopDisp_id', 'label' => 'Идентификатор Диагноза в связке', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'DiagSetClass_id', 'label' => 'Идентификатор Типа Диагноза', 'rules' => 'required', 'type' => 'id'),
			),
			'getFormalizedInspectionParamsBySurveyType' => [
				['field' => 'SurveyType_id', 'label' => '', 'rules' => 'required', 'type' => 'id'],
				['field' => 'EvnPLDispDop13_id', 'label' => '', 'rules' => 'required', 'type' => 'id'],
			],
			'saveFormalizedInspectionParamsText' => [
				['field' => 'EvnPLDisp_id', 'label' => '', 'rules' => 'required', 'type' => 'int'],
				['field' => 'responseValue', 'label' => '', 'rules' => '', 'type' => 'string']
			],
			'saveFormalizedInspectionParamsCheck' => [
				['field' => 'EvnPLDisp_id', 'label' => '', 'rules' => 'required', 'type' => 'int'],
				['field' => 'id', 'label' => '', 'rules' => '', 'type' => 'string'],
				['field' => 'check', 'label' => '', 'rules' => '', 'type' => 'string']
			],
			'getDVNPanelsLastUpdater' => [
				['field' => 'EvnPLDispDop13_id', 'label' => '', 'rules' => 'required', 'type' => 'id'],
				['field' => 'panelCode', 'label' => '', 'rules' => '', 'type' => 'string'],
			],
			'checkEvnPLDispDop13Access' => [
				['field' => 'EvnPLDispDop13_id', 'label' => '', 'rules' => 'required', 'type' => 'id']
			],
			'getDVNExecuteWindowRDS' => [
				['field' => 'fieldName', 'label' => '', 'rules' => 'required', 'type' => 'string']
			],
			//yl:Осмотр фельдшером (акушеркой) или врачом акушером-гинекологом 31
			'loadEvnPLDispDop13GynecologistText' => array(//yl:загрузить
				array('field' => 'EvnPLDispDop13_id', 'label' => 'Идентификатор ДВН', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'SurveyType_id', 	  'label' => 'Тип исследования', 'rules' => 'required', 'type' => 'id'),
			),
			'saveEvnPLDispDop13GynecologistText' => array(//yl:обновить текст осмотра
				array('field' => 'EvnPLDispDop13_id',	'label' => 'Идентификатор ДВН', 		'rules' => 'required', 'type' => 'id'),
				array('field' => 'SurveyType_id',		'label' => 'Тип исследования', 			'rules' => 'required', 'type' => 'id'),
				array('field' => 'GynecologistText',	'label' => 'Текст осмотра', 			'rules' => '',			'type' => 'string'),
				//вспомогательные поля
				array('field' => 'SurveyTypeLink_id',	'label' => 'SurveyTypeLink_id',			'rules' => 'required', 'type' => 'id'),
				array('field' => 'DopDispInfoConsent_id','label'=>'DopDispInfoConsent_id',		'rules' => 'required', 	'type' => 'id'),
				array('field' => 'MedPersonal_id',		'label' => 'MedPersonal_id', 			'rules' => 'required', 'type' => 'id'),
				array('field' => 'UslugaComplex_id',	'label' => 'UslugaComplex_id', 			'rules' => 'required', 'type' => 'id'),
				array('field' => 'EvnUsluga_id',		'label' => 'EvnUsluga_id',				'rules' => '', 		   'type' => 'id'),
				array('field' => 'Server_id',			'label' => 'Server_id', 				'rules' => 'required', 'type' => 'id'),
				array('field' => 'Person_id',			'label' => 'Person_id', 				'rules' => 'required', 'type' => 'id'),
				array('field' => 'PersonEvn_id',		'label' => 'PersonEvn_id', 				'rules' => 'required', 'type' => 'id'),
				//пустышки
				array(
					'field' => 'LpuSection_uid',
					'label' => 'Отделение',
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
					'field' => 'DopDispDiagType_id',
					'label' => 'Характер заболевания',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DeseaseStage',
					'label' => 'Стадия',
					'rules' => '',
					'type' => 'id'
				),

			),
			'getIndiProfConsult' => [
				['field' => 'EvnPLDispDop13_id', 'label' => '', 'rules' => 'required', 'type' => 'id']
			],
			'saveEvnPLDispDop13_SumRick' => [
				['field' => 'EvnPLDispDop13_id', 'label' => 'Идентификатор карты ДВН', 'rules' => 'required', 'type' => 'id']
			],
			'saveEvnPLDispDop13_SuspectZNO' => [
				[
					'field' => 'EvnPLDispDop13_id',
					'label' => 'Идентификатор карты ДВН',
					'rules' => 'required',
					'type' => 'id'
				], [
					'field' => 'EvnPLDispDop13_IsSuspectZNO',
					'label' => 'Подозрение на ЗНО',
					'rules' => 'required',
					'type' => 'id' //[0-1 | 2]
				], [
					'field' => 'Diag_spid',
					'label' => 'Диагноз',
					'rules' => '',
					'type' => 'id'
				]
			]
		);
		$this->inputRules['searchEvnPLDispDop13'] = array_merge($this->inputRules['searchEvnPLDispDop13'],getAddressSearchFilter());
    }

	/**
	 * Получение входящих параметров
	 */
	function getInputRulesAdv($rule = null) {
		if (empty($rule)) {
			return $this->inputRules;
		} else {
			return $this->inputRules[$rule];
		}
	}

	/**
	 *	Проверка наличия объёма
	 */
	function checkDispClass2Volume($data) {
		$resp = array(
			'volumeExists' => false,
			'Error_Msg' => ''
		);

		if (!empty($data['Lpu_id'])) {
			$queryParams = array(
				'Lpu_id' => $data['Lpu_id']
			);

			$queryParams['AttributeVision_TablePKey'] = $this->getFirstResultFromQuery("select VolumeType_id from v_VolumeType where VolumeType_Code = '2018_ДВН1_85' limit 1");
			$queryParams['curDate'] = $this->getFirstResultFromQuery("select dbo.tzGetDate()");

			$resp_vol = $this->queryResult("
				SELECT
					av.AttributeValue_id as \"AttributeValue_id\"
				FROM
					v_AttributeVision avis
					inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
					inner join v_Attribute a on a.Attribute_id = av.Attribute_id
					INNER JOIN LATERAL (
						select
							av2.AttributeValue_ValueIdent
						from
							v_AttributeValue av2
							inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
						where
							av2.AttributeValue_rid = av.AttributeValue_id
							and a2.Attribute_TableName = 'dbo.Lpu'
							and COALESCE(av2.AttributeValue_ValueIdent,:Lpu_id) = :Lpu_id
						limit 1
					) MOFILTER on true
				WHERE
					avis.AttributeVision_TableName = 'dbo.VolumeType'
					and avis.AttributeVision_TablePKey = :AttributeVision_TablePKey
					and avis.AttributeVision_IsKeyValue = 2
					and COALESCE(av.AttributeValue_begDate, :curDate) <= :curDate
					and COALESCE(av.AttributeValue_endDate, :curDate) >= :curDate
				LIMIT 1
			", $queryParams);
			
			if (!empty($resp_vol[0]['AttributeValue_id'])) {
				$resp['volumeExists'] = true;
			}
		}

		return $resp;
	}

	/**
	 * Получение доступных МО прикрепления
	 */
	function getAllowedLpuAttachIds($data) {
		$lpus = array();

		if (!empty($data['Lpu_id'])) {
			$lpus[] = $data['Lpu_id'];

			$queryParams = array(
				'Lpu_id' => $data['Lpu_id']
			);

			$queryParams['AttributeVision_TablePKey'] = $this->getFirstResultFromQuery("select VolumeType_id from v_VolumeType where VolumeType_Code = 'ДВН_Ч_МО' limit 1"); // Мед. диспансеризация взрослого населения в чужой МО
			$queryParams['curDate'] = $this->getFirstResultFromQuery("select dbo.tzGetDate()");

			$resp_vol = $this->queryResult("
				SELECT
					av.AttributeValue_ValueIdent as \"AttributeValue_ValueIdent\"
				FROM
					v_AttributeVision avis
					inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
					inner join v_Attribute a on a.Attribute_id = av.Attribute_id
					INNER JOIN LATERAL (
						select
							av2.AttributeValue_ValueIdent
						from
							v_AttributeValue av2
							inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
						where
							av2.AttributeValue_rid = av.AttributeValue_id
							and a2.Attribute_TableName = 'dbo.Lpu'
							and COALESCE(av2.AttributeValue_ValueIdent,:Lpu_id) = :Lpu_id
						limit 1
					) MOFILTER on true
				WHERE
					avis.AttributeVision_TableName = 'dbo.VolumeType'
					and avis.AttributeVision_TablePKey = :AttributeVision_TablePKey
					and avis.AttributeVision_IsKeyValue = 2
					and COALESCE(av.AttributeValue_begDate, :curDate) <= :curDate
					and COALESCE(av.AttributeValue_endDate, :curDate) >= :curDate
					
			", $queryParams);

			foreach($resp_vol as $one_vol) {
				if (!in_array($one_vol['AttributeValue_ValueIdent'], $lpus)) {
					$lpus[] = $one_vol['AttributeValue_ValueIdent'];
				}
			}
		}

		return array('Lpus' => $lpus, 'Error_Msg' => '');
	}

	/**
	 * Метод возвращает список id всех МО региона или хотя бы id текущей МО из настроек
	 *
	 * @return array
	 */
	function getAllRegionMoIds()
	{
		$lpus = array();
		$query = '
			Select
				Lpu_id as "Lpu_id"
			From
				v_Lpu
		';

		$results = $this->queryResult($query);

		if (count($results) > 0) {
			$lpus = array_column($results, 'Lpu_id');
		}

		return array('Lpus' => $lpus, 'Error_Msg' => null);
	}

	/**
	 * Метод возвращает список доступных для выбора id МО в зависимости от наличия открытого объема ДВН_Б_ПРИК
	 *
	 * @param $data
	 * @return array
	 */
	function getLpuIdsIfVolumeIsDvn_B_PrikOrNot($data)
	{
		if ($this->lpuHasOpenVolumeDvn_B_Prik($data) === false)
		{
			$results = $this->getAllowedLpuAttachIds($data);
			$results['Dvn_B_Prik'] = false;

		} else
		{
			$results = $this->getAllRegionMoIds();
			$results['Dvn_B_Prik'] = true;
		}

		return $results;
	}

	/**
	 * Метод определеяет, существует ли у МО открытый объем ДВН_Б_ПРИК
	 *
	 * @param $data
	 * @return bool|float|int|string
	 */
	function lpuHasOpenVolumeDvn_B_Prik($data)
	{
		$queryParams = array(
			'Lpu_id' => $data['Lpu_id']
		);

		$queryParams['AttributeVision_TablePKey'] = $this->getFirstResultFromQuery("select VolumeType_id from v_VolumeType where VolumeType_Code = 'ДВН_Б_ПРИК' limit 1"); // ДВН без прикрепления
		$queryParams['curDate'] = $this->getFirstResultFromQuery("select dbo.tzGetDate()");

		$result = $this->getFirstResultFromQuery("
			SELECT
				av.AttributeValue_id as \"AttributeValue_id\"
			FROM
				v_AttributeVision avis
				inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
				inner join v_Attribute a on a.Attribute_id = av.Attribute_id
			WHERE
				avis.AttributeVision_TableName = 'dbo.VolumeType'
				and av.AttributeValue_ValueIdent = :Lpu_id
				and avis.AttributeVision_TablePKey = :AttributeVision_TablePKey
				and avis.AttributeVision_IsKeyValue = 2
				and COALESCE(av.AttributeValue_begDate, :curDate) <= :curDate
				and COALESCE(av.AttributeValue_endDate, :curDate) >= :curDate
			LIMIT 1
		", $queryParams);

		return $result;
	}
	
	/**
	 *	Проверка на ДВН
	 */
	function checkPersons($data) {
		$response = array();
		$response['Persons'] = array();
		$disp_class = array(
			'1' => 'ДВН',
			'5' => 'ПОВН',
			'6' => 'МОН',
			'9' => 'МОН',
			'10' => 'МОН'
		);
		
		$personIds = json_decode($data['personIds'], true);		
		
		$add_filter = "";
		if (in_array($data['session']['region']['nick'], array('ufa', 'ekb', 'kareliya', 'krasnoyarsk', 'penza', 'astra'))) {
			$add_filter = " or exists (select PersonPrivilegeWOW_id from v_PersonPrivilegeWOW where Person_id = PS.Person_id limit 1)";
		}

		$maxage = 999;

		$personPrivilegeCodeList = $this->getPersonPrivilegeCodeList();

		if ( count($personIds) > 0 ) {
			$queryParams = array(
				'PersonDopDisp_Year' => date('Y'),
				'PersonDopDisp_YearPrev' => date('Y') - 1,
				'PersonDopDisp_YearEndDate' => date('Y') . '-12-31'
			);

			$query = "
				with PersonSt as (
					select
						Person_id,
						Person_SurName,
						Person_FirName,
						Person_SecName,
						Person_BirthDay,
						dbo.Age2(Person_BirthDay, dbo.tzGetDate()) as Person_AgeNow,
						dbo.Age2(Person_BirthDay, cast(:PersonDopDisp_YearEndDate as timestamp)) as Person_Age,
						cast(date_part('month', age(Person_BirthDay, dbo.tzGetDate())) as integer) % 12 as Person_AgeMonth
					from
						v_PersonState
					where
						Person_id in (" . implode(', ', $personIds) . ")
				)
					
				select
					ps.Person_SurName || ' ' || ps.Person_FirName || ' ' || COALESCE(ps.Person_SecName, '') as \"Person_Fio\",
					to_char(ps.Person_BirthDay, 'DD.MM.YYYY') as \"Person_BirthDay\",
					ps.Person_Age as \"Person_Age\",
					l.Lpu_Nick as \"Lpu_Nick\",
					pc.LpuRegion_Name as \"LpuRegion_Name\",
					/*-- пройденное ---*/
					passed.DispClass_id as \"DispClass_id\",
					to_char(passed.EvnPLDisp_setDate, 'DD.MM.YYYY') as \"EvnPLDisp_setDate\",
					to_char(passed.EvnPLDisp_disDate, 'DD.MM.YYYY') as \"EvnPLDisp_disDate\",
					passed.AgeGroupDisp_Name as \"AgeGroupDisp_Name\",	
					/*-- планируемое ---*/
					case when planned.PersonDopDispPlan_id is not null then 'v' else '' end as \"pEvnPLDisp_plan\",
					planned.DispClass_id as \"pDispClass_id\",
					to_char(planned.EvnPLDisp_setDate, 'DD.MM.YYYY') as \"pEvnPLDisp_setDate\",
					planned.AgeGroupDisp_Name as \"pAgeGroupDisp_Name\"
				from
					PersonSt ps
					left join v_PersonCard pc on (PC.Person_id = ps.Person_id and pc.LpuAttachType_id = 1)
					left join v_Lpu l on l.Lpu_id = pc.Lpu_id
					LEFT JOIN LATERAL (
						select count(*) as count
						from v_EvnPLDisp epld 
						where epld.Person_id = PS.Person_id and date_part('year', epld.EvnPLDisp_setDate) = :PersonDopDisp_YearPrev and epld.DispClass_id = 5
					) as EplDispProfLastYear on true
					LEFT JOIN LATERAL (
						select 
							epld.DispClass_id,
							epld.EvnPLDisp_setDate,
							epld.EvnPLDisp_disDate,
							ag.AgeGroupDisp_Name
						from v_EvnPLDisp epld 
						left join v_EvnPLDispTeenInspection epldti on epldti.EvnPLDispTeenInspection_id = epld.EvnPLDisp_id
						left join v_AgeGroupDisp ag on ag.AgeGroupDisp_id = epldti.AgeGroupDisp_id
						where epld.Person_id = PS.Person_id and epld.DispClass_id in(1,5,6,9,10) and (epld.EvnPLDisp_disDate is not null or date_part('year', epld.EvnPLDisp_consDT) < :PersonDopDisp_Year)
						order by COALESCE(epld.EvnPLDisp_disDate,epld.EvnPLDisp_setDate) desc
						limit 1
					) as passed on true
					LEFT JOIN LATERAL (
						select * from (
							/*--- уже заведена карта ----*/
							(select
								pddp.PersonDopDispPlan_id,
								epld.DispClass_id,
								case when epld.DispClass_id in(1,5) then epld.EvnPLDisp_setDate else null end as EvnPLDisp_setDate,
								ag.AgeGroupDisp_Name
							from v_EvnPLDisp epld 
							left join v_EvnPLDispTeenInspection epldti on epldti.EvnPLDispTeenInspection_id = epld.EvnPLDisp_id
							left join v_AgeGroupDisp ag on ag.AgeGroupDisp_id = epldti.AgeGroupDisp_id
							left join v_PlanPersonList ppl on ppl.Person_id = epld.Person_id
							left join v_PersonDopDispPlan pddp on ppl.PersonDopDispPlan_id = pddp.PersonDopDispPlan_id and pddp.DispClass_id = epld.DispClass_id
							where epld.Person_id = PS.Person_id and epld.DispClass_id in(1,5,6,9,10) and date_part('year', epld.EvnPLDisp_consDT) = :PersonDopDisp_Year and epld.EvnPLDisp_disDate is null
							order by epld.EvnPLDisp_setDate asc
							limit 1)
							/*--- включен в план, но не заведена карта ----*/
							union all
							(select
								pddp.PersonDopDispPlan_id,
								pddp.DispClass_id,
								null as EvnPLDisp_setDate,
								null as AgeGroupDisp_Name
							from v_PlanPersonList ppl
							inner join v_PersonDopDispPlan pddp on ppl.PersonDopDispPlan_id = pddp.PersonDopDispPlan_id
							where 
								ppl.Person_id = PS.Person_id and 
								pddp.DispClass_id in(1,5) and 
								pddp.PersonDopDispPlan_Year = :PersonDopDisp_Year and 
								not exists (select EvnPLDisp_id from v_EvnPLDisp where date_part('year', EvnPLDisp_consDT) = pddp.PersonDopDispPlan_Year and DispClass_id = pddp.DispClass_id and Person_id = ppl.Person_id limit 1)
							order by ppl.PlanPersonList_id asc
							limit 1)
							/*--- теоретически подлежащие МОН ----*/							
							union all
							(select
								null as PersonDopDispPlan_id,
								6 as DispClass_id,
								null as EvnPLDisp_setDate,
								agd.AgeGroupDisp_Name as AgeGroupDisp_Name
							from v_AgeGroupDisp agd
							where 
								ps.Person_Age < 18 and
								agd.DispType_id = 4 and 
								/*agd.AgeGroupDisp_From <= ps.Person_AgeNow and */
								agd.AgeGroupDisp_To >= ps.Person_AgeNow and 
								/*agd.AgeGroupDisp_monthFrom <= ps.Person_AgeMonth and */
								agd.AgeGroupDisp_monthTo >= ps.Person_AgeMonth and
								not exists (select EvnPLDisp_id from v_EvnPLDisp where date_part('year', EvnPLDisp_consDT) = :PersonDopDisp_Year and AgeGroupDisp_id = agd.AgeGroupDisp_id and DispClass_id in(6,9,10) and Person_id = PS.Person_id limit 1)
							order by agd.AgeGroupDisp_From asc, agd.AgeGroupDisp_monthFrom asc
							limit 1)
							/*--- теоретически подлежащие ДВН ----*/
							union all
							(select
								null as PersonDopDispPlan_id,
								1 as DispClass_id,
								null as EvnPLDisp_setDate,
								null as AgeGroupDisp_Name
							where
								(
									(ps.Person_Age >= 21 and ps.Person_Age %3 = 0)
									" . (count($personPrivilegeCodeList) > 0 ? "or (ps.Person_Age >= 18 and exists (select pp.PersonPrivilege_id from v_PersonPrivilege pp inner join v_PrivilegeType pt on pt.PrivilegeType_id = pp.PrivilegeType_id where pt.PrivilegeType_Code IN ('" . implode("','", $personPrivilegeCodeList) . "') and pp.Person_id = PS.Person_id and pp.PersonPrivilege_begDate <= cast(:PersonDopDisp_YearEndDate as timestamp) and (pp.PersonPrivilege_endDate >= cast(:PersonDopDisp_YearEndDate as timestamp) or pp.PersonPrivilege_endDate is null) limit 1))" : "") . "
								)
								and not exists (select EvnPLDisp_id from v_EvnPLDisp where date_part('year', EvnPLDisp_consDT) = :PersonDopDisp_Year and DispClass_id = 1 and Person_id = PS.Person_id limit 1)
							limit 1)
							/*--- теоретически подлежащие ПОВН ----*/
							union all
							(select
								null as PersonDopDispPlan_id,
								5 as DispClass_id,
								null as EvnPLDisp_setDate,
								null as AgeGroupDisp_Name
							where
								ps.Person_Age >= 18 and
								ps.Person_Age % 3 != 0 and
								EplDispProfLastYear.count = 0 and
								not exists (select EvnPLDisp_id from v_EvnPLDisp where date_part('year', EvnPLDisp_consDT) = :PersonDopDisp_Year and DispClass_id = 5 and Person_id = PS.Person_id limit 1)
							limit 1)
						) as t
						limit 1
					) as planned on true
				order by
					ps.Person_SurName, ps.Person_FirName
			";
			
			//echo getDebugSql($query, array()); exit;
			$result = $this->db->query($query, $queryParams);
			$response['CurYear'] = date('Y');
			if (is_object($result)) {
				$resp = $result->result('array');
				foreach ($resp as &$r) {
					$r['DispClass_id'] = strtr($r['DispClass_id'], $disp_class);
					$r['pDispClass_id'] = strtr($r['pDispClass_id'], $disp_class);
				}
				$response['Persons'] = $resp;
			}
		}
		
		return $response;
	}
	
	/**
	 *	Удаление аттрибутов
	 */	
	function deleteAttributes($attr, $EvnPLDispDop13_id, $pmUser_id) {
		// Сперва получаем список
		switch ( $attr ) {
			case 'EvnVizitDispDop':
				$query = "
					select
						EVDD.EvnVizitDispDop_id as id
					from
						v_EvnUslugaDispDop EUDD
						inner join v_EvnVizitDispDop EVDD on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
						inner join v_DopDispInfoConsent DDIC on DDIC.DopDispInfoConsent_id = EVDD.DopDispInfoConsent_id
						inner join v_SurveyTypeLink STL on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
						inner join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
					where
						EUDD.EvnUslugaDispDop_rid = :EvnPLDispDop13_id
						and COALESCE(DDIC.DopDispInfoConsent_IsAgree, 1) = 1
						and COALESCE(DDIC.DopDispInfoConsent_IsEarlier, 1) = 1
						and ST.SurveyType_Code not in (1, 2, 48)
						and COALESCE(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
				";
			break;

			// Специально для удаления анкетирования
			case 'EvnUslugaDispDop':
				$query = "
					select
						EUDD.EvnUslugaDispDop_id as id
					from
						v_EvnUslugaDispDop EUDD
						inner join v_SurveyTypeLink STL on STL.UslugaComplex_id = EUDD.UslugaComplex_id
						inner join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
						inner join v_DopDispInfoConsent DDIC on DDIC.SurveyTypeLink_id = STL.SurveyTypeLink_id
					where
						EUDD.EvnUslugaDispDop_rid = :EvnPLDispDop13_id
						and DDIC.EvnPLDisp_id = :EvnPLDispDop13_id
						and COALESCE(DDIC.DopDispInfoConsent_IsAgree, 1) = 1
						and COALESCE(DDIC.DopDispInfoConsent_IsEarlier, 1) = 1
						and ST.SurveyType_Code = 2
						and COALESCE(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
				";
			break;

			case 'EvnDiagDopDisp':
				$query = "
					select " . $attr . "_id as id
					from v_" . $attr . "
					where EvnDiagDopDisp_pid = :EvnPLDispDop13_id
				";
			break;

			case 'DopDispInfoConsent':
				$query = "
					select DDIC.DopDispInfoConsent_id as id
					from v_DopDispInfoConsent DDIC
						inner join v_SurveyTypeLink STL on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
						inner join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
					where DDIC.EvnPLDisp_id = :EvnPLDispDop13_id
						and ST.SurveyType_Code NOT IN (1,48)
				";
			break;

			default:
				$query = "
					select " . $attr . "_id as id
					from v_" . $attr . "
					where EvnPLDisp_id = :EvnPLDispDop13_id
				";
			break;
		}

		$result = $this->db->query($query, array('EvnPLDispDop13_id' => $EvnPLDispDop13_id));

		if ( !is_object($result) ) {
			return 'Ошибка при выполнении запроса к базе данных';
		}

		$response = $result->result('array');

		if ( is_array($response) && count($response) > 0 ) {
			foreach ( $response as $array ) {
				$query = "
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from p_" . $attr . "_del (
						" . $attr . "_id := :id
						" . (in_array($attr, array('EvnDiagDopDisp', 'EvnUslugaDispDop', 'EvnVizitDispDop')) ? ",pmUser_id := :pmUser_id" : "") . "
					)
				";
				$result = $this->db->query($query, array('id' => $array['id'], 'pmUser_id' => $pmUser_id));

				if ( !is_object($result) ) {
					return 'Ошибка при выполнении запроса к базе данных';
				}

				$res = $result->result('array');

				if ( is_array($res) && count($res) > 0 && !empty($res[0]['Error_Msg']) ) {
					return $res[0]['Error_Msg'];
				}
			}
		}

		return '';
	}
	
	/**
	 *	Получение кода комлпексной услуги
	 */	
	function getUslugaComplexCode($data) {
		$query = "
			select
				UslugaComplex_Code as \"UslugaComplex_Code\"
			from
				v_UslugaComplex
			where
				UslugaComplex_id = :UslugaComplex_id
		";
		
		$res = $this->db->query($query, $data);
		
		if ( is_object($res) ) {
 	    	$resp = $res->result('array');
			if (count($resp) > 0) {
				return $resp[0]['UslugaComplex_Code'];
			}
		}
		
		return '';
	}
	
	/**
	 *	Получение идентификатора согласия для сурвейтайпа
	 */	
	function getDopDispInfoConsentForSurveyType($EvnPLDisp_id, $SurveyType_id) {
		$query = "
			select
				DopDispInfoConsent_id as \"DopDispInfoConsent_id\"
			from
				v_DopDispInfoConsent
			where
				EvnPLDisp_id = :EvnPLDisp_id
				and SurveyType_id = :SurveyType_id
			limit 1
		";
		
		$result = $this->db->query($query, array(
			'EvnPLDisp_id' => $EvnPLDisp_id,
			'SurveyType_id' => $SurveyType_id
		));
		
		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return $resp[0]['DopDispInfoConsent_id'];
			}
		}
		
		return null;
	}

	/**
	 *	Получение идентификатора согласия для сурвейтайплинка
	 */	
	function getDopDispInfoConsentForSurveyTypeLink($EvnPLDisp_id, $SurveyTypeLink_id) {
		$query = "
			select
				DopDispInfoConsent_id as \"DopDispInfoConsent_id\"
			from
				v_DopDispInfoConsent
			where
				EvnPLDisp_id = :EvnPLDisp_id
				and SurveyTypeLink_id = :SurveyTypeLink_id
			limit 1
		";

		$result = $this->db->query($query, array(
			'EvnPLDisp_id' => $EvnPLDisp_id,
			'SurveyTypeLink_id' => $SurveyTypeLink_id
		));
		
		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return $resp[0]['DopDispInfoConsent_id'];
			}
		}
		
		return null;
	}

	/**
	 * Сохранение согласия для цитологического исследования
	 */
	function saveCytoDopDispInfoConsent($data) {
		// ищем сохранено ли уже согласие
		$CytoDopDispInfoConsent_id = null;
		$resp_cyto = $this->queryResult("
			select
				ddic.DopDispInfoConsent_id as \"DopDispInfoConsent_id\",
				ddic.DopDispInfoConsent_IsAgree as \"DopDispInfoConsent_IsAgree\",
				ddic.DopDispInfoConsent_IsEarlier as \"DopDispInfoConsent_IsEarlier\",
				ddic.DopDispInfoConsent_IsImpossible as \"DopDispInfoConsent_IsImpossible\"
			from
				v_DopDispInfoConsent ddic
				inner join v_SurveyTypeLink stl on stl.SurveyTypeLink_id = ddic.SurveyTypeLink_id
			where
				ddic.EvnPLDisp_id = :EvnPLDisp_id
				and stl.SurveyTypeLink_ComplexSurvey = 2
			limit 1
		", [
			'EvnPLDisp_id' => $data['EvnPLDisp_id']
		]);
		if (!empty($resp_cyto[0]['DopDispInfoConsent_id'])) {
			$CytoDopDispInfoConsent_id = $resp_cyto[0]['DopDispInfoConsent_id'];

			// если согласие и запись уже существует, то не надо апдейтить (т.к. пользователь мог уже внести отказ в самой услуге)
			if (
				!empty($data['ifNotExist'])
				&& ($data['DopDispInfoConsent_IsAgree'] == 2 || $data['DopDispInfoConsent_IsEarlier'] == 2)
				&& $resp_cyto[0]['DopDispInfoConsent_IsAgree'] == 1
				&& $resp_cyto[0]['DopDispInfoConsent_IsEarlier'] == 1
				&& $resp_cyto[0]['DopDispInfoConsent_IsImpossible'] == 1
			) {
				return $resp_cyto;
			}
		}

		// апдейтим согласие
		$cytoddicproc = "p_DopDispInfoConsent_ins";
		if (!empty($CytoDopDispInfoConsent_id)) {
			$cytoddicproc = "p_DopDispInfoConsent_upd";
		}

		if (!isset($data['Person_id']) || !isset($data['EvnPLDisp_consDate'])) {
			// берём из карты
			$query = "
				select
					Person_id as \"Person_id\",
					to_char(EvnPLDisp_consDT, 'YYYY-MM-DD HH24:MI:SS') as \"EvnPLDisp_consDate\"
				from
					v_EvnPLDisp
				where
					EvnPLDisp_id = :EvnPLDisp_id
			";

			$resp = $this->queryResult($query, array(
				'EvnPLDisp_id' => $data['EvnPLDisp_id']
			));

			if (!empty($resp[0]['Person_id'])) {
				$data['Person_id'] = $resp[0]['Person_id'];
				$data['EvnPLDisp_consDate'] = $resp[0]['EvnPLDisp_consDate'];
			} else {
				return [[ 'Error_Msg' => 'Не удалось получить данные карты ДД' ]];
			}
		}

		// определяем SurveyTypeLink_id
		$CytoSurveyTypeLink_id = null;
		$stlFilter = "";
		$stlQueryParams = [
			'Person_id' => $data['Person_id'],
			'EvnPLDisp_consDate' => $data['EvnPLDisp_consDate'],
		];
		if (!empty($data['CytoUslugaComplex_id'])) {
			$stlFilter .= " and UslugaComplex_id = :UslugaComplex_id";
			$stlQueryParams['UslugaComplex_id'] = $data['CytoUslugaComplex_id'];
		}

		$resp_ps = $this->queryResult("
			select
				person_id as person_id,
				COALESCE(Sex_id, 3) as sex_id,
				dbo.Age2(Person_BirthDay, cast(:EvnPLDisp_YearEndDate as date)) as age
			from v_PersonState ps
			where ps.Person_id = :Person_id
			limit 1
		", [
			'Person_id' => $data['Person_id'],
			'EvnPLDisp_YearEndDate' => mb_substr($data['EvnPLDisp_consDate'], 0, 4) . '-12-31'
		]);

		if (empty($resp_ps[0]['person_id'])) {
			throw new Exception('Ошибка получения данных по пациенту');
		}

		$resp_ps[0] = $this->getAgeModification([
			'onDate' => $data['EvnPLDisp_consDate']
		], $resp_ps[0]);

		$stlQueryParams['dispclass_id'] = $data['DispClass_id'];
		$stlQueryParams['sex_id'] = $resp_ps[0]['sex_id'];
		$stlQueryParams['age'] = $resp_ps[0]['age'];

		$query = "
			select
				STL.SurveyTypeLink_id as \"SurveyTypeLink_id\"
			from
				v_SurveyTypeLink STL
			where
				STL.SurveyTypeLink_ComplexSurvey = 2
				and (:age between COALESCE(STL.SurveyTypeLink_From, 0) and  COALESCE(STL.SurveyTypeLink_To, 999))
				and STL.DispClass_id = :dispclass_id
				and (COALESCE(STL.Sex_id, :sex_id) = :sex_id) -- по полу
				and (STL.SurveyTypeLink_begDate is null or STL.SurveyTypeLink_begDate <= :EvnPLDisp_consDate)
				and (STL.SurveyTypeLink_endDate is null or STL.SurveyTypeLink_endDate >= :EvnPLDisp_consDate)
				{$stlFilter}
			limit 1
		";
		$stlResp = $this->queryResult($query, $stlQueryParams);
		if (!empty($stlResp[0]['SurveyTypeLink_id'])) {
			$CytoSurveyTypeLink_id = $stlResp[0]['SurveyTypeLink_id'];
		} else {
			return [['Error_Msg' => 'Не удалось сохранить согласие для услуги цитологического исследования']];
		}

		$query = "
			select
				DopDispInfoConsent_id as \"DopDispInfoConsent_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$cytoddicproc} (
				DopDispInfoConsent_id := :DopDispInfoConsent_id,
				EvnPLDisp_id := :EvnPLDisp_id,
				DopDispInfoConsent_IsAgree := :DopDispInfoConsent_IsAgree,
				DopDispInfoConsent_IsEarlier := :DopDispInfoConsent_IsEarlier,
				DopDispInfoConsent_IsImpossible := :DopDispInfoConsent_IsImpossible,
				SurveyTypeLink_id := :SurveyTypeLink_id,
				pmUser_id := :pmUser_id
			)
		";
		return $this->queryResult($query, [
			'EvnPLDisp_id' => $data['EvnPLDisp_id'],
			'DopDispInfoConsent_id' => $CytoDopDispInfoConsent_id,
			'DopDispInfoConsent_IsAgree' => $data['DopDispInfoConsent_IsAgree'],
			'DopDispInfoConsent_IsEarlier' => $data['DopDispInfoConsent_IsEarlier'],
			'DopDispInfoConsent_IsImpossible' => $data['DopDispInfoConsent_IsImpossible'],
			'SurveyTypeLink_id' => $CytoSurveyTypeLink_id,
			'pmUser_id' => $data['pmUser_id']
		]);
	}
	
	/**
	 *	Сохранение согласий
	 */	
	function saveDopDispInfoConsent($data)
	{
		$checkResult = $this->checkPersonData($data);
		If ( empty($checkResult['PersonEvn_id']) ) {
			return $checkResult;
		}
		
		// Стартуем транзакцию
		$this->db->trans_begin();

		$onDate = $data['EvnPLDispDop13_consDate'];
		if (!empty($data['EvnPLDispDop13_IsNewOrder']) && $data['EvnPLDispDop13_IsNewOrder'] == 2) {
			$onDate = date('Y', strtotime($data['EvnPLDispDop13_consDate'])) . '-12-31';
		}

		if ( $data['DispClass_id'] == 1 && !$this->allowDVN($data['Person_id'], $onDate) ) {
			$dateX = $this->getNewDVNDate();

			if ( !empty($dateX) ) {
				return array('Error_Msg' => 'Указана некорректная дата информационного согласия. По приказу, действующему до ' . date('d.m.Y', strtotime($dateX)) . ', пациент не подлежит ДВН в указанном году.');
			}
			else {
				return array('Error_Msg' => 'Пациент не подлежит ДВН в указанном году.');
			}
		}

		$EvnPLDispDopIsNew = false;

		if ($data['EvnPLDispDop13_IsMobile']) { $data['EvnPLDispDop13_IsMobile'] = 2; } else { $data['EvnPLDispDop13_IsMobile'] = 1;	}
		if ($data['EvnPLDispDop13_IsOutLpu']) { $data['EvnPLDispDop13_IsOutLpu'] = 2; } else { $data['EvnPLDispDop13_IsOutLpu'] = 1;	}

		// Екб: Дата подписания согласия 1 этапа должна быть строго меньше даты согласия 2 этапа
		if ($this->getRegionNick() == 'ekb' || $this->getRegionNick() == 'buryatiya') {
			$resp = $this->queryResult("
				select 
					EvnPLDispDop13_id as \"EvnPLDispDop13_id\"
				from 
					v_EvnPLDispDop13 
				where 
					EvnPLDispDop13_fid = :EvnPLDispDop13_fid and
					EvnPLDispDop13_consDT <= :EvnPLDispDop13_consDate
			", array(
				'EvnPLDispDop13_fid' => $data['EvnPLDispDop13_id'],
				'EvnPLDispDop13_consDate' => $data['EvnPLDispDop13_consDate']
			));

			if (!empty($resp[0]['EvnPLDispDop13_id'])) {
				return array('Error_Msg' => 'Дата подписания согласия/отказа должна быть раньше даты подписания/отказа второго этапа');
			}
		}

		// Дата подписания информированного согласия-2 этап должна быть больше/равна дате осмотра врача-терапевта(ВОП) 1 этапа. 
		if (!empty($data['EvnPLDispDop13_fid'])) {
			$query = "
				select
					EvnPLDispDop13_id as \"EvnPLDispDop13_id\"
				from
					v_EvnPLDispDop13
				where
					EvnPLDispDop13_id = :EvnPLDispDop13_fid and EvnPLDispDop13_disDate > :EvnPLDispDop13_consDate
				limit 1
			";
			
			$res = $this->db->query($query, $data);
			if ( is_object($res) ) {
				$resp = $res->result('array');
				if (count($resp) > 0) {
					$this->db->trans_rollback();
					return array('Error_Msg' => 'Дата начала диспансеризации 2 этапа должна быть не раньше даты окончания 1 этапа');
				}
			}
		}

		// @task https://redmine.swan.perm.ru/issues/103181
		$allowWithoutAttach = false;

		if ( getRegionNick() == 'ekb' ) {
			// проверяем наличие объёма "Без прикрепления"
			$queryParams = array(
				'EvnPLDispDop13_consDate' => $data['EvnPLDispDop13_consDate'],
				'Lpu_id' => $data['Lpu_id']
			);

			$queryParams['AttributeVision_TablePKey'] = $this->getFirstResultFromQuery("select VolumeType_id from v_VolumeType where VolumeType_Code = 'ДВН_Б_ПРИК' limit 1"); // ДВН без прикрепления

			$resp_vol = $this->queryResult("
				SELECT
					av.AttributeValue_id as \"AttributeValue_id\"
				FROM
					v_AttributeVision avis
					inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
					inner join v_Attribute a on a.Attribute_id = av.Attribute_id
				WHERE
					avis.AttributeVision_TableName = 'dbo.VolumeType'
					and av.AttributeValue_ValueIdent = :Lpu_id
					and avis.AttributeVision_TablePKey = :AttributeVision_TablePKey
					and avis.AttributeVision_IsKeyValue = 2
					and COALESCE(av.AttributeValue_begDate, :EvnPLDispDop13_consDate) <= :EvnPLDispDop13_consDate
					and COALESCE(av.AttributeValue_endDate, :EvnPLDispDop13_consDate) >= :EvnPLDispDop13_consDate
				LIMIT 1
			", $queryParams);

			if (!empty($resp_vol[0]['AttributeValue_id'])) {
				// Если МО имеет объем открытый объем «ДВН_Б_ПРИК», то проверку на прикрепление к разрешенным МО не проводим
				$allowWithoutAttach = true;
			}
		}

		if ( $allowWithoutAttach === false ) {
			// Если выбранный персон на дату подписания информированного согласия не имеет основного прикрепления к ЛПУ пользователя, то выводить сообщение
			// «Пациент имеет основное прикрепление к другой МО». Сохранение отменить.
			// UPD #110181: Если на дату подписания информированного согласия основное прикрепление пациента не к МО пользователя или у пользователя нет основного
			// прикрепления, то выводить сообщение: "Пациент не имеет основного прикрепления или прикреплен к другой МО. ОК", сохранение согласия отменить.
			$lpuList = array($data['Lpu_id']);
			if (getRegionNick() == 'ekb') {
				// #100008 Регион: Свердловская область.
				// Если на дату подписания информационного согласия основное прикрепление пациента не к текущей МО и не к МО, разрешившая проводить профилактические
				// мероприятия по своему прикрепленному населения (включенные в объем «Мед. диспансеризация взрослого населения в чужой МО»), то выводить сообщение:
				// "Пациент имеет основное прикрепление к другой МО. ОК", сохранение согласия отменить.
				// Если у пациента нет активного прикрепления, то проверку проводить.
				$resp = $this->getAllowedLpuAttachIds($data);
				if (!empty($resp['Lpus'])) {
					$lpuList = $resp['Lpus'];
				}
			}
			$sql = "
				SELECT
					PersonCard_id as \"PersonCard_id\",
					Lpu_id as \"Lpu_id\"
				FROM v_PersonCard_all
				WHERE
					Person_id = :Person_id
					and LpuAttachType_id = 1
					and cast(PersonCard_begDate as date) <= :EvnPLDispDop13_consDate
					and COALESCE(PersonCard_endDate, cast('2030-01-01' as date)) > :EvnPLDispDop13_consDate
			";
			$res = $this->db->query($sql, $data);
			if ( !is_object($res) ) {
				$this->db->trans_rollback();
				return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (строка ' . __LINE__ . ')');
			}
			$sel = $res->result('array');
			if ( !is_array($sel) ) {
				$this->db->trans_rollback();
				return array('Error_Msg' => 'Ошибка при определении МО прикрепления пациента (строка ' . __LINE__ . ')');
			}
			$hasLpuAttach = false;
			foreach ( $sel as $row ) {
				if ( !empty($row['Lpu_id']) && in_array($row['Lpu_id'], $lpuList) ) {
					$hasLpuAttach = true;
					break;
				}
			}
			if ($hasLpuAttach == false && !in_array(getRegionNick(), ['kareliya', 'krym', 'buryatiya'])) { // если не нашли прикрепление в своей МО
				$this->db->trans_rollback();
				return ['Error_Msg' => 'Пациент не имеет основного прикрепления или прикреплен к другой МО'];
			} else if ($hasLpuAttach == false && in_array(getRegionNick(), ['kareliya', 'krym', 'buryatiya']) && empty($data['AttachmentAnswer']) && $data['DispClass_id'] == 1) {
				$this->db->trans_rollback();
				return ['Alert_Msg' => 'Пациент не имеет основного прикрепления или прикреплен к другой МО. Продолжить сохранение?'];
			}
			if ($data['DispClass_id'] == 2 && !empty($data['EvnPLDispDop13_fid']) && in_array(getRegionNick(), array('kareliya','krym','buryatiya')) ){
				$resp_first_lpu = $this->getFirstResultFromQuery("
					select
						Lpu_id as Lpu_fid
					from
						v_EvnPLDispDop13 epldd13f
					where
						epldd13f.EvnPLDispDop13_id = :EvnPLDispDop13_fid
					limit 1
				", array(
					'EvnPLDispDop13_fid' => $data['EvnPLDispDop13_fid']
				));
				if($data['Lpu_id']!=$resp_first_lpu){
					$this->db->trans_rollback();
					return array('Error_Msg' => 'Данный пациент направлен на 2 этап другой медицинской организацией. Второй этап должен проводиться в той же медицинской организации, где проведен первый этап.');
				}
			}
		}
		
		if (empty($data['EvnPLDispDop13_id'])) {
			// берём последнюю периодику (которая прошла проверку)
			$data['PersonEvn_id'] = $checkResult['PersonEvn_id'];
			$data['Server_id'] = $checkResult['Server_id'];
			
			// Проверям наличие карт ДВН за выбраный год
			// https://redmine.swan.perm.ru/issues/23095
			$query = "
				select EvnPLDispDop13_id as \"EvnPLDispDop13_id\"
				from v_EvnPLDispDop13
				where Person_id = :Person_id
					and date_part('year', EvnPLDispDop13_consDT) = :EvnPLDispDop13_consYear
					and DispClass_id = :DispClass_id
				limit 1
			";

			$result = $this->db->query($query, array(
				 'DispClass_id' => $data['DispClass_id']
				,'EvnPLDispDop13_consYear' => mb_substr($data['EvnPLDispDop13_consDate'], 0, 4)
				,'Person_id' => $data['Person_id']
			));

			if ( !is_object($result) ) {
				$this->db->trans_rollback();
				return array(array('Error_Msg' => 'Ошибка при проверке наличия карт ДВН в указанном году'));
			}

			$resp = $result->result('array');

			if ( is_array($resp) && count($resp) > 0 && !empty($resp[0]['EvnPLDispDop13_id']) ) {
				$this->db->trans_rollback();
				return array(array('Error_Msg' => 'У человека уже имеется сохраненная карта ДВН в указанном году. Повторите поиск.'));
			}

			// Проверям наличие карт профосмотра за выбраный год
			// https://redmine.swan.perm.ru/issues/61990
			$query = "
				select EvnPLDispProf_id as \"EvnPLDispProf_id\"
				from v_EvnPLDispProf
				where Person_id = :Person_id
					and date_part('year', EvnPLDispProf_consDT) = :EvnPLDispDop13_consYear
					and DispClass_id = :DispClass_id
				limit 1
			";

			$result = $this->db->query($query, array(
				'DispClass_id' => 5
				,'EvnPLDispDop13_consYear' => mb_substr($data['EvnPLDispDop13_consDate'], 0, 4)
				,'Person_id' => $data['Person_id']
			));

			if ( !is_object($result) ) {
				$this->db->trans_rollback();
				return array(array('Error_Msg' => 'Ошибка при проверке наличия карт профосмотра в указанном году'));
			}

			$resp = $result->result('array');

			if ( is_array($resp) && count($resp) > 0 && !empty($resp[0]['EvnPLDispProf_id']) ) {
				$this->db->trans_rollback();
				return array(array('Error_Msg' => 'В указанном году пациент прошел профилактический осмотр. Проведение диспансеризации взрослого населения невозможно.'));
			}

			$EvnPLDispDopIsNew = true;

			// Автоматически заполнять поля / поля списков информацией с соответствующих полей / полей списка с первого этапа диспансеризации (за исключением поля «Случай диспансеризации 2 этап закончен»)
			$data['Diag_id'] = null;
			$data['Diag_sid'] = null;
			$data['EvnPLDispDop13_IsDisp'] = null;
			$data['NeedDopCure_id'] = null;
			$data['EvnPLDispDop13_IsStac'] = null;
			$data['EvnPLDispDop13_IsSanator'] = null;
			$data['EvnPLDispDop13_SumRick'] = null;
			$data['RiskType_id'] = null;
			$data['EvnPLDispDop13_IsSchool'] = null;
			$data['EvnPLDispDop13_IsProphCons'] = null;
			$data['EvnPLDispDop13_IsHypoten'] = null;
			$data['EvnPLDispDop13_IsLipid'] = null;
			$data['EvnPLDispDop13_IsHypoglyc'] = null;
			$data['HealthKind_id'] = null;
			$data['EvnPLDispDop13_IsEndStage'] = null;
			$data['EvnPLDispDop13_IsTwoStage'] = null;
			$data['CardioRiskType_id'] = null;
			$data['EvnPLDispDop13_IsStenocard'] = null;
			$data['EvnPLDispDop13_IsShortCons'] = null;
			$data['EvnPLDispDop13_IsBrain'] = null;
			$data['EvnPLDispDop13_IsDoubleScan'] = null;
			$data['EvnPLDispDop13_IsTub'] = null;
			$data['EvnPLDispDop13_IsTIA'] = null;
			$data['EvnPLDispDop13_IsRespiratory'] = null;
			$data['EvnPLDispDop13_IsLungs'] = null;
			$data['EvnPLDispDop13_IsTopGastro'] = null;
			$data['EvnPLDispDop13_IsBotGastro'] = null;
			$data['EvnPLDispDop13_IsSpirometry'] = null;
			$data['EvnPLDispDop13_IsHeartFailure'] = null;
			$data['EvnPLDispDop13_IsOncology'] = null;
			$data['EvnPLDispDop13_IsEsophag'] = null;
			$data['EvnPLDispDop13_IsSmoking'] = null;
			$data['EvnPLDispDop13_IsRiskAlco'] = null;
			$data['EvnPLDispDop13_IsAlcoDepend'] = null;
			$data['EvnPLDispDop13_IsLowActiv'] = null;
			$data['EvnPLDispDop13_IsIrrational'] = null;
			$data['EvnPLDispDop13_IsUseNarko'] = null;

			if (!empty($data['EvnPLDispDop13_fid'])) {
				$query = "
					select
						EPLDD13.Diag_id as \"Diag_id\",
						EPLDD13.Diag_sid as \"Diag_sid\",
						EPLDD13.EvnPLDispDop13_IsDisp as \"EvnPLDispDop13_IsDisp\",
						EPLDD13.NeedDopCure_id as \"NeedDopCure_id\",
						EPLDD13.EvnPLDispDop13_IsStac as \"EvnPLDispDop13_IsStac\",
						EPLDD13.EvnPLDispDop13_IsSanator as \"EvnPLDispDop13_IsSanator\",
						EPLDD13.EvnPLDispDop13_SumRick as \"EvnPLDispDop13_SumRick\",
						EPLDD13.RiskType_id as \"RiskType_id\",
						EPLDD13.EvnPLDispDop13_IsSchool as \"EvnPLDispDop13_IsSchool\",
						EPLDD13.EvnPLDispDop13_IsProphCons as \"EvnPLDispDop13_IsProphCons\",
						EPLDD13.EvnPLDispDop13_IsHypoten as \"EvnPLDispDop13_IsHypoten\",
						EPLDD13.EvnPLDispDop13_IsLipid as \"EvnPLDispDop13_IsLipid\",
						EPLDD13.EvnPLDispDop13_IsHypoglyc as \"EvnPLDispDop13_IsHypoglyc\",
						EPLDD13.HealthKind_id as \"HealthKind_id\",
						EPLDD13.CardioRiskType_id as \"CardioRiskType_id\",
						EPLDD13.EvnPLDispDop13_IsStenocard as \"EvnPLDispDop13_IsStenocard\",
						EPLDD13.EvnPLDispDop13_IsShortCons as \"EvnPLDispDop13_IsShortCons\",
						EPLDD13.EvnPLDispDop13_IsBrain as \"EvnPLDispDop13_IsBrain\",
						EPLDD13.EvnPLDispDop13_IsDoubleScan as \"EvnPLDispDop13_IsDoubleScan\",
						EPLDD13.EvnPLDispDop13_IsTub as \"EvnPLDispDop13_IsTub\",
						EPLDD13.EvnPLDispDop13_IsTIA as \"EvnPLDispDop13_IsTIA\",
						EPLDD13.EvnPLDispDop13_IsRespiratory as \"EvnPLDispDop13_IsRespiratory\",
						EPLDD13.EvnPLDispDop13_IsLungs as \"EvnPLDispDop13_IsLungs\",
						EPLDD13.EvnPLDispDop13_IsTopGastro as \"EvnPLDispDop13_IsTopGastro\",
						EPLDD13.EvnPLDispDop13_IsBotGastro as \"EvnPLDispDop13_IsBotGastro\",
						EPLDD13.EvnPLDispDop13_IsSpirometry as \"EvnPLDispDop13_IsSpirometry\",
						EPLDD13.EvnPLDispDop13_IsHeartFailure as \"EvnPLDispDop13_IsHeartFailure\",
						EPLDD13.EvnPLDispDop13_IsOncology as \"EvnPLDispDop13_IsOncology\",
						EPLDD13.EvnPLDispDop13_IsEsophag as \"EvnPLDispDop13_IsEsophag\",
						EPLDD13.EvnPLDispDop13_IsSmoking as \"EvnPLDispDop13_IsSmoking\",
						EPLDD13.EvnPLDispDop13_IsRiskAlco as \"EvnPLDispDop13_IsRiskAlco\",
						EPLDD13.EvnPLDispDop13_IsAlcoDepend as \"EvnPLDispDop13_IsAlcoDepend\",
						EPLDD13.EvnPLDispDop13_IsLowActiv as \"EvnPLDispDop13_IsLowActiv\",
						EPLDD13.EvnPLDispDop13_IsIrrational as \"EvnPLDispDop13_IsIrrational\",
						EPLDD13.EvnPLDispDop13_IsUseNarko as \"EvnPLDispDop13_IsUseNarko\"
					from
						v_EvnPLDispDop13 EPLDD13
					where
						EPLDD13.EvnPLDispDop13_id = :EvnPLDispDop13_fid
					limit 1
				";
				
				$result = $this->db->query($query, $data);
				
				if ( is_object($result) ) {
					$resp = $result->result('array');
					if ( count($resp) > 0 ) {
						$data['Diag_id'] = $resp[0]['Diag_id'];
						$data['Diag_sid'] = $resp[0]['Diag_sid'];
						$data['EvnPLDispDop13_IsDisp'] = $resp[0]['EvnPLDispDop13_IsDisp'];
						$data['NeedDopCure_id'] = $resp[0]['NeedDopCure_id'];
						$data['EvnPLDispDop13_IsStac'] = $resp[0]['EvnPLDispDop13_IsStac'];
						$data['EvnPLDispDop13_IsSanator'] = $resp[0]['EvnPLDispDop13_IsSanator'];
						$data['EvnPLDispDop13_SumRick'] = $resp[0]['EvnPLDispDop13_SumRick'];
						$data['RiskType_id'] = $resp[0]['RiskType_id'];
						$data['EvnPLDispDop13_IsSchool'] = $resp[0]['EvnPLDispDop13_IsSchool'];
						$data['EvnPLDispDop13_IsProphCons'] = $resp[0]['EvnPLDispDop13_IsProphCons'];
						$data['EvnPLDispDop13_IsHypoten'] = $resp[0]['EvnPLDispDop13_IsHypoten'];
						$data['EvnPLDispDop13_IsLipid'] = $resp[0]['EvnPLDispDop13_IsLipid'];
						$data['EvnPLDispDop13_IsHypoglyc'] = $resp[0]['EvnPLDispDop13_IsHypoglyc'];
						$data['HealthKind_id'] = $resp[0]['HealthKind_id'];
						$data['CardioRiskType_id'] = $resp[0]['CardioRiskType_id'];
						$data['EvnPLDispDop13_IsStenocard'] = $resp[0]['EvnPLDispDop13_IsStenocard'];
						$data['EvnPLDispDop13_IsShortCons'] = $resp[0]['EvnPLDispDop13_IsShortCons'];
						$data['EvnPLDispDop13_IsBrain'] = $resp[0]['EvnPLDispDop13_IsBrain'];
						$data['EvnPLDispDop13_IsDoubleScan'] = $resp[0]['EvnPLDispDop13_IsDoubleScan'];
						$data['EvnPLDispDop13_IsTub'] = $resp[0]['EvnPLDispDop13_IsTub'];
						$data['EvnPLDispDop13_IsTIA'] = $resp[0]['EvnPLDispDop13_IsTIA'];
						$data['EvnPLDispDop13_IsRespiratory'] = $resp[0]['EvnPLDispDop13_IsRespiratory'];
						$data['EvnPLDispDop13_IsLungs'] = $resp[0]['EvnPLDispDop13_IsLungs'];
						$data['EvnPLDispDop13_IsTopGastro'] = $resp[0]['EvnPLDispDop13_IsTopGastro'];
						$data['EvnPLDispDop13_IsBotGastro'] = $resp[0]['EvnPLDispDop13_IsBotGastro'];
						$data['EvnPLDispDop13_IsSpirometry'] = $resp[0]['EvnPLDispDop13_IsSpirometry'];
						$data['EvnPLDispDop13_IsHeartFailure'] = $resp[0]['EvnPLDispDop13_IsHeartFailure'];
						$data['EvnPLDispDop13_IsOncology'] = $resp[0]['EvnPLDispDop13_IsOncology'];
						$data['EvnPLDispDop13_IsEsophag'] = $resp[0]['EvnPLDispDop13_IsEsophag'];
						$data['EvnPLDispDop13_IsSmoking'] = $resp[0]['EvnPLDispDop13_IsSmoking'];
						$data['EvnPLDispDop13_IsRiskAlco'] = $resp[0]['EvnPLDispDop13_IsRiskAlco'];
						$data['EvnPLDispDop13_IsAlcoDepend'] = $resp[0]['EvnPLDispDop13_IsAlcoDepend'];
						$data['EvnPLDispDop13_IsLowActiv'] = $resp[0]['EvnPLDispDop13_IsLowActiv'];
						$data['EvnPLDispDop13_IsIrrational'] = $resp[0]['EvnPLDispDop13_IsIrrational'];
						$data['EvnPLDispDop13_IsUseNarko'] = $resp[0]['EvnPLDispDop13_IsUseNarko'];
					} else {
						$this->db->trans_rollback();
						return array(array('Error_Msg' => 'Не найдена карта ДВН 1 этапа, сохранение невозможно'));
					}
				} else {
					$this->db->trans_rollback();
					return array(array('Error_Msg' => 'Ошибка поиска карты ДВН 1 этапа, сохранение невозможно'));
				}
			}
			
			// добавляем новый талон ДД
			$query = "
				select
					EvnPLDispDop13_id as \"EvnPLDispDop13_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from p_EvnPLDispDop13_ins (
					MedStaffFact_id := :MedStaffFact_id,
					EvnPLDispDop13_pid := null,  
					Lpu_id := :Lpu_id, 
					Server_id := :Server_id, 
					PersonEvn_id := :PersonEvn_id,
					EvnPLDispDop13_setDT := :EvnPLDispDop13_setDate, 
					EvnPLDispDop13_disDT := null, 
					EvnPLDispDop13_didDT := null, 
					Morbus_id := null, 
					EvnPLDispDop13_IsSigned := null, 
					pmUser_signID := null, 
					EvnPLDispDop13_signDT := null, 
					EvnPLDispDop13_VizitCount := null, 
					EvnPLDispDop13_IsFinish := null, 
					Person_Age := null, 
					AttachType_id := 2, 
					Lpu_aid := null, 
					EvnPLDispDop13_IsStenocard := :EvnPLDispDop13_IsStenocard, 
					EvnPLDispDop13_IsShortCons := :EvnPLDispDop13_IsShortCons,
					EvnPLDispDop13_IsBrain := :EvnPLDispDop13_IsBrain,
					EvnPLDispDop13_IsDoubleScan := :EvnPLDispDop13_IsDoubleScan, 
					EvnPLDispDop13_IsTub := :EvnPLDispDop13_IsTub,
					EvnPLDispDop13_IsTIA := :EvnPLDispDop13_IsTIA,
					EvnPLDispDop13_IsRespiratory := :EvnPLDispDop13_IsRespiratory,
					EvnPLDispDop13_IsLungs := :EvnPLDispDop13_IsLungs,
					EvnPLDispDop13_IsTopGastro := :EvnPLDispDop13_IsTopGastro,
					EvnPLDispDop13_IsBotGastro := :EvnPLDispDop13_IsBotGastro,
					EvnPLDispDop13_IsSpirometry := :EvnPLDispDop13_IsSpirometry,
					EvnPLDispDop13_IsHeartFailure := :EvnPLDispDop13_IsHeartFailure,
					EvnPLDispDop13_IsOncology := :EvnPLDispDop13_IsOncology, 
					EvnPLDispDop13_IsEsophag := :EvnPLDispDop13_IsEsophag, 
					EvnPLDispDop13_IsSmoking := :EvnPLDispDop13_IsSmoking, 
					EvnPLDispDop13_IsRiskAlco := :EvnPLDispDop13_IsRiskAlco, 
					EvnPLDispDop13_IsAlcoDepend := :EvnPLDispDop13_IsAlcoDepend, 
					EvnPLDispDop13_IsLowActiv := :EvnPLDispDop13_IsLowActiv, 
					EvnPLDispDop13_IsIrrational := :EvnPLDispDop13_IsIrrational, 
					EvnPLDispDop13_IsUseNarko := :EvnPLDispDop13_IsUseNarko, 
					Diag_id := :Diag_id, 
					Diag_sid := :Diag_sid, 
					EvnPLDispDop13_IsDisp := :EvnPLDispDop13_IsDisp, 
					NeedDopCure_id := :NeedDopCure_id, 
					EvnPLDispDop13_IsStac := :EvnPLDispDop13_IsStac, 
					EvnPLDispDop13_IsSanator := :EvnPLDispDop13_IsSanator, 
					EvnPLDispDop13_SumRick := :EvnPLDispDop13_SumRick, 
					RiskType_id := :RiskType_id, 
					EvnPLDispDop13_IsSchool := :EvnPLDispDop13_IsSchool, 
					EvnPLDispDop13_IsProphCons := :EvnPLDispDop13_IsProphCons, 
					EvnPLDispDop13_IsHypoten := :EvnPLDispDop13_IsHypoten, 
					EvnPLDispDop13_IsLipid := :EvnPLDispDop13_IsLipid, 
					EvnPLDispDop13_IsHypoglyc := :EvnPLDispDop13_IsHypoglyc, 
					HealthKind_id := :HealthKind_id, 
					EvnPLDispDop13_IsEndStage := null, 
					EvnPLDispDop13_IsTwoStage := null,
					EvnPLDispDop13_consDT := :EvnPLDispDop13_consDate,
					EvnPLDispDop13_IsMobile := :EvnPLDispDop13_IsMobile,
					EvnPLDispDop13_IsOutLpu := :EvnPLDispDop13_IsOutLpu,
					Lpu_mid := :Lpu_mid,
					CardioRiskType_id := :CardioRiskType_id, 
					DispClass_id := :DispClass_id,
					PayType_id := :PayType_id,
					EvnPLDispDop13_fid := :EvnPLDispDop13_fid,
					EvnPLDispDop13_IsNewOrder := :EvnPLDispDop13_IsNewOrder,
					pmUser_id := :pmUser_id
				)
			";
			$result = $this->db->query($query, array(
				'EvnPLDispDop13_id' => $data['EvnPLDispDop13_id'],
				'MedStaffFact_id' => !empty($data['session']['CurARM']['MedStaffFact_id'])?$data['session']['CurARM']['MedStaffFact_id']:null,
				'Lpu_id' => $data['Lpu_id'],
				'Server_id' => $data['Server_id'],
				'PersonEvn_id' => $data['PersonEvn_id'],
				'Diag_id' => $data['Diag_id'],
				'Diag_sid' => $data['Diag_sid'],
				'EvnPLDispDop13_IsDisp' => $data['EvnPLDispDop13_IsDisp'],
				'NeedDopCure_id' => $data['NeedDopCure_id'],
				'EvnPLDispDop13_IsStac' => $data['EvnPLDispDop13_IsStac'],
				'EvnPLDispDop13_IsSanator' => $data['EvnPLDispDop13_IsSanator'],
				'EvnPLDispDop13_SumRick' => $data['EvnPLDispDop13_SumRick'],
				'RiskType_id' => $data['RiskType_id'],
				'EvnPLDispDop13_IsSchool' => $data['EvnPLDispDop13_IsSchool'],
				'EvnPLDispDop13_IsProphCons' => $data['EvnPLDispDop13_IsProphCons'],
				'EvnPLDispDop13_IsHypoten' => $data['EvnPLDispDop13_IsHypoten'],
				'EvnPLDispDop13_IsLipid' => $data['EvnPLDispDop13_IsLipid'],
				'EvnPLDispDop13_IsHypoglyc' => $data['EvnPLDispDop13_IsHypoglyc'],
				'HealthKind_id' => $data['HealthKind_id'],
				'CardioRiskType_id' => $data['CardioRiskType_id'],
				'EvnPLDispDop13_IsStenocard' => $data['EvnPLDispDop13_IsStenocard'],
				'EvnPLDispDop13_IsShortCons' => $data['EvnPLDispDop13_IsShortCons'],
				'EvnPLDispDop13_IsBrain' => $data['EvnPLDispDop13_IsBrain'],
				'EvnPLDispDop13_IsDoubleScan' => $data['EvnPLDispDop13_IsDoubleScan'],
				'EvnPLDispDop13_IsTub' => $data['EvnPLDispDop13_IsTub'],
				'EvnPLDispDop13_IsTIA' => $data['EvnPLDispDop13_IsTIA'],
				'EvnPLDispDop13_IsRespiratory' => $data['EvnPLDispDop13_IsRespiratory'],
				'EvnPLDispDop13_IsLungs' => $data['EvnPLDispDop13_IsLungs'],
				'EvnPLDispDop13_IsTopGastro' => $data['EvnPLDispDop13_IsTopGastro'],
				'EvnPLDispDop13_IsBotGastro' => $data['EvnPLDispDop13_IsBotGastro'],
				'EvnPLDispDop13_IsSpirometry' => $data['EvnPLDispDop13_IsSpirometry'],
				'EvnPLDispDop13_IsHeartFailure' => $data['EvnPLDispDop13_IsHeartFailure'],
				'EvnPLDispDop13_IsOncology' => $data['EvnPLDispDop13_IsOncology'],
				'EvnPLDispDop13_IsEsophag' => $data['EvnPLDispDop13_IsEsophag'],
				'EvnPLDispDop13_IsSmoking' => $data['EvnPLDispDop13_IsSmoking'],
				'EvnPLDispDop13_IsRiskAlco' => $data['EvnPLDispDop13_IsRiskAlco'],
				'EvnPLDispDop13_IsAlcoDepend' => $data['EvnPLDispDop13_IsAlcoDepend'],
				'EvnPLDispDop13_IsLowActiv' => $data['EvnPLDispDop13_IsLowActiv'],
				'EvnPLDispDop13_IsIrrational' => $data['EvnPLDispDop13_IsIrrational'],
				'EvnPLDispDop13_IsUseNarko' => $data['EvnPLDispDop13_IsUseNarko'],
				'EvnPLDispDop13_setDate' => $data['EvnPLDispDop13_consDate'],
				'EvnPLDispDop13_consDate' => $data['EvnPLDispDop13_consDate'],
				'EvnPLDispDop13_IsMobile' => $data['EvnPLDispDop13_IsMobile'],
				'EvnPLDispDop13_IsOutLpu' => $data['EvnPLDispDop13_IsOutLpu'],
				'Lpu_mid' => $data['Lpu_mid'],
				'DispClass_id' => $data['DispClass_id'],
				'PayType_id' => $data['PayType_id'],
				'EvnPLDispDop13_fid' => $data['EvnPLDispDop13_fid'],
				'EvnPLDispDop13_IsNewOrder' => $data['EvnPLDispDop13_IsNewOrder'],
				'pmUser_id' => $data['pmUser_id']
			));
			
			if ( is_object($result) ) {
				$resp = $result->result('array');
				if (isset($resp[0]['EvnPLDispDop13_id'])) {
					$data['EvnPLDispDop13_id'] = $resp[0]['EvnPLDispDop13_id'];
				} else {
					$this->db->trans_rollback();
					return $resp; // иначе выдаем.. там видимо ошибка
				}
			}

			if (getRegionNick() == 'penza') {
				//Отправить человека в очередь на идентификацию
				$this->load->model('Person_model', 'pmodel');
				$this->pmodel->isAllowTransaction = false;
				$resTmp = $this->pmodel->addPersonRequestData(array(
					'Person_id' => $data['Person_id'],
					'pmUser_id' => $data['pmUser_id'],
					'Evn_id' => $data['EvnPLDispDop13_id'],
					'PersonRequestSourceType_id' => 3,
				));
				$this->pmodel->isAllowTransaction = true;
				if (!$this->isSuccessful($resTmp) && !in_array($resTmp[0]['Error_Code'], array(302, 303))) {
					$this->db->trans_rollback();
					return $resTmp[0];
				}
			}
		}
		
		$this->load->model('EvnDiagDopDisp_model', 'evndiagdopdisp');
		$this->load->model('ProphConsult_model', 'prophconsult');
		$this->load->model('HeredityDiag_model', 'hereditydiag');
		$this->load->model('NeedConsult_model', 'needconsult');
		
		// При наличии карты дисп. учета пациента с периодом действия включающим создаваемую карту ДВН/ПОВН (по дате инф. согласия) добавить диагноз с карты дисп. учета. (refs #22327)
		$query = "
			select
				pd.Diag_id as \"Diag_id\",
				to_char(pd.PersonDisp_begDate, 'DD.MM.YYYY') as \"PersonDisp_begDate\"
			from
				v_PersonDisp pd
				inner join v_Diag d on d.Diag_id = pd.Diag_id
				left join v_ProfileDiag pdiag on pdiag.Diag_id = d.Diag_pid
			where
				pd.Person_id = :Person_id
				and (pd.PersonDisp_begDate <= :EvnPLDispDop13_consDate OR pd.PersonDisp_begDate IS NULL)
				and (pd.PersonDisp_endDate >= :EvnPLDispDop13_consDate OR pd.PersonDisp_endDate IS NULL)
				and pdiag.ProfileDiagGroup_id IS NULL
		";
		$result = $this->db->query($query, array(
			'EvnPLDispDop13_consDate' => $data['EvnPLDispDop13_consDate'],
			'Person_id' => $data['Person_id']
		));
		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0 && !empty($resp[0]['Diag_id'])) {
				$data['EvnPLDisp_id'] = $data['EvnPLDispDop13_id'];
                foreach ($resp as $item){
					$data['EvnDiagDopDisp_setDate'] = !empty($item['PersonDisp_begDate'])?date('Y-m-d', strtotime($item['PersonDisp_begDate'])):null;
                    $this->evndiagdopdisp->addEvnDiagDopDispBefore($data, $item['Diag_id']);
                }
			}
		}

		if (!empty($data['EvnPLDispDop13_fid']) && $EvnPLDispDopIsNew) {
			// нужно скопировать все поля списков EvnDiagDopDisp, HeredityDiag, ProphConsult
			$records = $this->evndiagdopdisp->loadEvnDiagDopDispGrid(array('EvnPLDisp_id' => $data['EvnPLDispDop13_fid'], 'DeseaseDispType_id' => 1));
			if (is_array($records)) {
				foreach($records as $record) {
					$found = false; //чтобы не дублировать диагнозы которые только что добавили выше
					foreach ($resp as $item){
						if($item['Diag_id'] == $record['Diag_id'])
							$found = true;
					}
					if($found) continue;
					$record['EvnDiagDopDisp_id'] = null;
					$record['EvnDiagDopDisp_setDate'] = !empty($record['EvnDiagDopDisp_setDate'])?date('Y-m-d', strtotime($record['EvnDiagDopDisp_setDate'])):null;
					$record['DeseaseDispType_id'] = 1;
					$record['pmUser_id'] = $data['pmUser_id'];
					$record['EvnDiagDopDisp_pid'] = $data['EvnPLDispDop13_id'];
					$this->evndiagdopdisp->saveEvnDiagDopDisp($record);
				}
			}
			
			$records = $this->evndiagdopdisp->loadEvnDiagDopDispGrid(array('EvnPLDisp_id' => $data['EvnPLDispDop13_fid'], 'DeseaseDispType_id' => 2));
			if (is_array($records)) {
				foreach($records as $record) {
					$record['EvnDiagDopDisp_id'] = null;
					$record['EvnDiagDopDisp_setDate'] = !empty($record['EvnDiagDopDisp_setDate'])?date('Y-m-d', strtotime($record['EvnDiagDopDisp_setDate'])):null;
					$record['DeseaseDispType_id'] = 2;
					$record['pmUser_id'] = $data['pmUser_id'];
					$record['EvnDiagDopDisp_pid'] = $data['EvnPLDispDop13_id'];
					$this->evndiagdopdisp->saveEvnDiagDopDisp($record);
				}
			}
			
			$records = $this->prophconsult->loadProphConsultGrid(array('EvnPLDisp_id' => $data['EvnPLDispDop13_fid']));
			if (is_array($records)) {
				foreach($records as $record) {
					$record['ProphConsult_id'] = null;
					$record['pmUser_id'] = $data['pmUser_id'];
					$record['EvnPLDisp_id'] = $data['EvnPLDispDop13_id'];
					$this->prophconsult->saveProphConsult($record);
				}
			}
			
			$records = $this->hereditydiag->loadHeredityDiagGrid(array('EvnPLDisp_id' => $data['EvnPLDispDop13_fid']));
			if (is_array($records)) {
				foreach($records as $record) {
					$record['HeredityDiag_id'] = null;
					$record['pmUser_id'] = $data['pmUser_id'];
					$record['EvnPLDisp_id'] = $data['EvnPLDispDop13_id'];
					$this->hereditydiag->saveHeredityDiag($record);
				}
			}
			
			$records = $this->needconsult->loadNeedConsultGrid(array('EvnPLDisp_id' => $data['EvnPLDispDop13_fid']));
			if (is_array($records)) {
				foreach($records as $record) {
					$record['NeedConsult_id'] = null;
					$record['pmUser_id'] = $data['pmUser_id'];
					$record['EvnPLDisp_id'] = $data['EvnPLDispDop13_id'];
					$this->needconsult->saveNeedConsult($record);
				}
			}
		}

		// сохраняем данные по информир. добр. согласию для EvnPLDispDop13_id = $data['EvnPLDispDop13_id']
		ConvertFromWin1251ToUTF8($data['DopDispInfoConsentData']);
		$items = json_decode($data['DopDispInfoConsentData'], true);
		$itemsCount = 0;

		// Массив идентификаторов DopDispInfoConsent_id, которые надо удалить
		// Выполняться должно после удаления посещений, т.к. в посещениях сейчас есть ссылка на DopDispInfoConsent
		$DopDispInfoConsentToDel = array();

		// Список идентификаторов DopDispInfoConsent_id, которые 
		// https://redmine.swan.perm.ru/issues/29017
		$DopDispInfoConsentList = array();

		foreach($items as $item) {
			// Добавил доп. условия, т.к. с клиента может приходить не только 0 и 1, но и true и false
			// https://redmine.swan.perm.ru/issues/22236
			if ( (!empty($item['DopDispInfoConsent_IsEarlier']) && $item['DopDispInfoConsent_IsEarlier'] == '1') || $item['DopDispInfoConsent_IsEarlier'] === true ) {
				$item['DopDispInfoConsent_IsEarlier'] = 2;
			} else {
				$item['DopDispInfoConsent_IsEarlier'] = 1;
			}
			
			if ( (!empty($item['DopDispInfoConsent_IsAgree']) && $item['DopDispInfoConsent_IsAgree'] == '1') || $item['DopDispInfoConsent_IsAgree'] === true ) {
				$item['DopDispInfoConsent_IsAgree'] = 2;
			} else {
				$item['DopDispInfoConsent_IsAgree'] = 1;
			}

			if (!empty($item['DopDispInfoConsent_IsImpossible']) && ($item['DopDispInfoConsent_IsImpossible'] == '1' || $item['DopDispInfoConsent_IsImpossible'] === true)) {
				$item['DopDispInfoConsent_IsImpossible'] = 2;
			} else {
				$item['DopDispInfoConsent_IsImpossible'] = 1;
			}

			if ( $item['DopDispInfoConsent_IsEarlier'] == 2 || $item['DopDispInfoConsent_IsAgree'] == 2 ) {
				$itemsCount++;
			}
			
			// получаем идентификатор DopDispInfoConsent_id для SurveyTypeLink_id и EvnPLDisp_id (должна быть только одна запись для каждой пары значений)
			$item['DopDispInfoConsent_id'] = $this->getDopDispInfoConsentForSurveyTypeLink($data['EvnPLDispDop13_id'], $item['SurveyTypeLink_id']);
			
			if (!empty($item['DopDispInfoConsent_id']) && $item['DopDispInfoConsent_id'] > 0) {
				$DopDispInfoConsentList[] = $item['DopDispInfoConsent_id'];
				$proc = 'p_DopDispInfoConsent_upd';
			} else {
				$proc = 'p_DopDispInfoConsent_ins';
				$item['DopDispInfoConsent_id'] = null;
			}

			$dateX = $this->getNewDVNDate();
			$onDate = $data['EvnPLDispDop13_consDate'];
			if (!empty($data['EvnPLDispDop13_IsNewOrder']) && $data['EvnPLDispDop13_IsNewOrder'] == 2) {
				$onDate = date('Y', strtotime($data['EvnPLDispDop13_consDate'])) . '-12-31';
            } else if (!in_array(getRegionNick(), [ 'ekb', 'perm' ]) && !empty($dateX) && $data['DispClass_id'] == 2 && !empty($data['EvnPLDispDop13_fid'])) {
				// достаём дату согласия из первого этапа, т.к. связки загружаются именно на неё
				$resp_first = $this->queryResult("
					select
						to_char(epldd13f.EvnPLDispDop13_consDT, 'YYYY-MM-DD') as \"EvnPLDispDop13_firstConsDate\"
					from
						v_EvnPLDispDop13 epldd13f
					where
						epldd13f.EvnPLDispDop13_id = :EvnPLDispDop13_fid
				", array(
					'EvnPLDispDop13_fid' => $data['EvnPLDispDop13_fid']
				));
				if (!empty($resp_first[0]['EvnPLDispDop13_firstConsDate'])) {
					$onDate = $resp_first[0]['EvnPLDispDop13_firstConsDate'];
				}
			}

			// проверяем что сохраняемое SurveyTypeLink удовлетворяет по датам дате согласия / можно будет убрать после того как выяснится причина некорректных сохранений.
			$SurveyTypeLink_id = $this->getFirstResultFromQuery("
				select
					STL.SurveyTypeLink_id as \"SurveyTypeLink_id\"
				from
					v_SurveyTypeLink STL
				where
					STL.SurveyTypeLink_id = :SurveyTypeLink_id
					and (STL.SurveyTypeLink_begDate is null or STL.SurveyTypeLink_begDate <= :EvnPLDispDop13_consDate)
					and (STL.SurveyTypeLink_endDate is null or STL.SurveyTypeLink_endDate >= :EvnPLDispDop13_consDate)
				limit 1
			", array(
				'SurveyTypeLink_id' => $item['SurveyTypeLink_id'],
				'EvnPLDispDop13_consDate' => $onDate
			));

			if (empty($SurveyTypeLink_id)) {
				$this->db->trans_rollback();
				return array('Error_Msg' => 'Ошибка при сохранении информированного добровольного согласия (указанный SurveyTypeLink не удовлетворяет дате согласия)');
			}

			// если убирают согласие для удалённого SurveyTypeLink, то удаляем его из DopDispInfoConsent. (refs #21573)
			if (!empty($item['DopDispInfoConsent_id']) && $item['DopDispInfoConsent_id'] > 0 && !empty($item['SurveyTypeLink_IsDel']) && $item['SurveyTypeLink_IsDel'] == '2' && $item['DopDispInfoConsent_IsEarlier'] == 1 && $item['DopDispInfoConsent_IsAgree'] == 1) {
				// Удаление перенесено 
				$DopDispInfoConsentToDel[] = $item['DopDispInfoConsent_id'];
			}
			else {
				if (empty($item['SurveyTypeLink_id'])) {
					$this->db->trans_rollback();
					return array(
						'success' => false,
						'Error_Msg' => 'Ошибка при сохранении информированного добровольного согласия (отсутсвует ссылка на SurveyTypeLink)'
					);
				}
				
				$query = "
					select
						DopDispInfoConsent_id as \"DopDispInfoConsent_id\",
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from {$proc} (
						DopDispInfoConsent_id := :DopDispInfoConsent_id, 
						EvnPLDisp_id := :EvnPLDispDop13_id, 
						DopDispInfoConsent_IsAgree := :DopDispInfoConsent_IsAgree, 
						DopDispInfoConsent_IsEarlier := :DopDispInfoConsent_IsEarlier, 
						DopDispInfoConsent_IsImpossible := :DopDispInfoConsent_IsImpossible,
						SurveyTypeLink_id := :SurveyTypeLink_id,
						pmUser_id := :pmUser_id
					)
				";
				$result = $this->db->query($query, array(
					'EvnPLDispDop13_id' => $data['EvnPLDispDop13_id'],
					'DopDispInfoConsent_id' => $item['DopDispInfoConsent_id'],
					'DopDispInfoConsent_IsAgree' => $item['DopDispInfoConsent_IsAgree'],
					'DopDispInfoConsent_IsEarlier' => $item['DopDispInfoConsent_IsEarlier'],
					'DopDispInfoConsent_IsImpossible' => $item['DopDispInfoConsent_IsImpossible'],
					'SurveyTypeLink_id' => $item['SurveyTypeLink_id'],
					'pmUser_id' => $data['pmUser_id']
				));

				if ( is_object($result) ) {
					$res = $result->result('array');

					if ( is_array($res) && count($res) > 0 ) {
						if ( !empty($res[0]['Error_Msg']) ) {
							$this->db->trans_rollback();
							return array(
								'success' => false,
								'Error_Msg' => $res[0]['Error_Msg']
							);
						}

						if ( !in_array($res[0]['DopDispInfoConsent_id'], $DopDispInfoConsentList) ) {
							$DopDispInfoConsentList[] = $res[0]['DopDispInfoConsent_id'];
						}

						$SurveyType_Code = $this->getFirstResultFromQuery("
							select
								SurveyType_Code
							from
								v_SurveyType st
								inner join v_SurveyTypeLink stl on stl.SurveyType_id = st.SurveyType_id
							where
								stl.SurveyTypeLink_id = :SurveyTypeLink_id
						", array('SurveyTypeLink_id' => $item['SurveyTypeLink_id']));

						if ($data['DispClass_id'] == 1 && $SurveyType_Code && $SurveyType_Code == 20 && (
							in_array(getRegionNick(), array('ekb'))
							|| (
								in_array(getRegionNick(), array('astra'))
								&& DateTime::createFromFormat('Y-m-d', $onDate) < DateTime::createFromFormat('d.m.Y', '01.01.2018')
							)
							|| (
								in_array(getRegionNick(), array('perm'))
								&& DateTime::createFromFormat('Y-m-d', $onDate) < DateTime::createFromFormat('d.m.Y', '01.06.2019')
							)
						)) {
							$respCytoSave = $this->saveCytoDopDispInfoConsent(array(
								'DispClass_id' => $data['DispClass_id'],
								'EvnPLDisp_id' => $data['EvnPLDispDop13_id'],
								'EvnPLDisp_consDate' => $data['EvnPLDispDop13_consDate'],
								'Person_id' => $data['Person_id'],
								'DopDispInfoConsent_IsAgree' => $item['DopDispInfoConsent_IsAgree'],
								'DopDispInfoConsent_IsEarlier' => $item['DopDispInfoConsent_IsEarlier'],
								'DopDispInfoConsent_IsImpossible' => $item['DopDispInfoConsent_IsImpossible'],
								'pmUser_id' => $data['pmUser_id'],
								'ifNotExist' => true
							));

							if (!empty($respCytoSave[0]['Error_Msg'])) {
								return $respCytoSave;
							}

							$DopDispInfoConsentList[] = $respCytoSave[0]['DopDispInfoConsent_id'];
						}
					}
				}
				else {
					$this->db->trans_rollback();
					return array(
						'success' => false,
						'Error_Msg' => 'Ошибка при выполнении запроса к базе данных (строка ' . __LINE__ . ')'
					);
				}
			}
		}

		if ( $EvnPLDispDopIsNew === false )  {
			// Обновляем дату EvnPLDispDop13_consDate и чистим атрибуты на карте, если пациент отказался от ДД
			$query = "
				select
					 EvnPLDispDop13_pid as \"EvnPLDispDop13_pid\"
					,EvnPLDispDop13_fid as \"EvnPLDispDop13_fid\"
					,Lpu_id as \"Lpu_id\"
					,Server_id as \"Server_id\"
					,PersonEvn_id as \"PersonEvn_id\"
					,to_char(EvnPLDispDop13_setDT, 'YYYY-MM-DD HH24:MI:SS') as \"EvnPLDispDop13_setDT\"
					,to_char(EvnPLDispDop13_disDT, 'YYYY-MM-DD HH24:MI:SS') as \"EvnPLDispDop13_disDT\"
					,to_char(EvnPLDispDop13_didDT, 'YYYY-MM-DD HH24:MI:SS') as \"EvnPLDispDop13_didDT\"
					,Morbus_id as \"Morbus_id\"
					,EvnPLDispDop13_IsSigned as \"EvnPLDispDop13_IsSigned\"
					,pmUser_signID as \"pmUser_signID\"
					,EvnPLDispDop13_signDT as \"EvnPLDispDop13_signDT\"
					,EvnPLDispDop13_IsFinish as \"EvnPLDispDop13_IsFinish\"
					,EvnPLDispDop13_IsNewOrder as \"EvnPLDispDop13_IsNewOrder\"
					,EvnPLDispDop13_IndexRep as \"EvnPLDispDop13_IndexRep\"
					,EvnPLDispDop13_IndexRepInReg as \"EvnPLDispDop13_IndexRepInReg\"
					,EvnDirection_aid as \"EvnDirection_aid\"
					,Person_Age as \"Person_Age\"
					,AttachType_id as \"AttachType_id\"
					,Lpu_aid as \"Lpu_aid\"
					,DispClass_id as \"DispClass_id\"
					," . ($itemsCount == 0 ? "null" : "EvnPLDispDop13_IsStenocard") . " as \"EvnPLDispDop13_IsStenocard\"
					," . ($itemsCount == 0 ? "null" : "EvnPLDispDop13_IsShortCons") . " as \"EvnPLDispDop13_IsShortCons\"
					," . ($itemsCount == 0 ? "null" : "EvnPLDispDop13_IsBrain") . " as \"EvnPLDispDop13_IsBrain\"
					," . ($itemsCount == 0 ? "null" : "EvnPLDispDop13_IsDoubleScan") . " as \"EvnPLDispDop13_IsDoubleScan\"
					," . ($itemsCount == 0 ? "null" : "EvnPLDispDop13_IsTub") . " as \"EvnPLDispDop13_IsTub\"
					," . ($itemsCount == 0 ? "null" : "EvnPLDispDop13_IsTIA") . " as \"EvnPLDispDop13_IsTIA\"
					," . ($itemsCount == 0 ? "null" : "EvnPLDispDop13_IsRespiratory") . " as \"EvnPLDispDop13_IsRespiratory\"
					," . ($itemsCount == 0 ? "null" : "EvnPLDispDop13_IsLungs") . " as \"EvnPLDispDop13_IsLungs\"
					," . ($itemsCount == 0 ? "null" : "EvnPLDispDop13_IsTopGastro") . " as \"EvnPLDispDop13_IsTopGastro\"
					," . ($itemsCount == 0 ? "null" : "EvnPLDispDop13_IsBotGastro") . " as \"EvnPLDispDop13_IsBotGastro\"
					," . ($itemsCount == 0 ? "null" : "EvnPLDispDop13_IsSpirometry") . " as \"EvnPLDispDop13_IsSpirometry\"
					," . ($itemsCount == 0 ? "null" : "EvnPLDispDop13_IsHeartFailure") . " as \"EvnPLDispDop13_IsHeartFailure\"
					," . ($itemsCount == 0 ? "null" : "EvnPLDispDop13_IsOncology") . " as \"EvnPLDispDop13_IsOncology\"
					," . ($itemsCount == 0 ? "null" : "EvnPLDispDop13_IsEsophag") . " as \"EvnPLDispDop13_IsEsophag\"
					," . ($itemsCount == 0 ? "null" : "EvnPLDispDop13_IsSmoking") . " as \"EvnPLDispDop13_IsSmoking\"
					," . ($itemsCount == 0 ? "null" : "EvnPLDispDop13_IsRiskAlco") . " as \"EvnPLDispDop13_IsRiskAlco\"
					," . ($itemsCount == 0 ? "null" : "EvnPLDispDop13_IsAlcoDepend") . " as \"EvnPLDispDop13_IsAlcoDepend\"
					," . ($itemsCount == 0 ? "null" : "EvnPLDispDop13_IsLowActiv") . " as \"EvnPLDispDop13_IsLowActiv\"
					," . ($itemsCount == 0 ? "null" : "EvnPLDispDop13_IsIrrational") . " as \"EvnPLDispDop13_IsIrrational\"
					," . ($itemsCount == 0 ? "null" : "EvnPLDispDop13_IsUseNarko") . " as \"EvnPLDispDop13_IsUseNarko\"
					," . ($itemsCount == 0 ? "null" : "Diag_id") . " as \"Diag_id\"
					," . ($itemsCount == 0 ? "null" : "Diag_sid") . " as \"Diag_sid\"
					," . ($itemsCount == 0 ? "null" : "EvnPLDispDop13_IsDisp") . " as \"EvnPLDispDop13_IsDisp\"
					," . ($itemsCount == 0 ? "null" : "NeedDopCure_id") . " as \"NeedDopCure_id\"
					," . ($itemsCount == 0 ? "null" : "EvnPLDispDop13_IsStac") . " as \"EvnPLDispDop13_IsStac\"
					," . ($itemsCount == 0 ? "null" : "EvnPLDispDop13_IsSanator") . " as \"EvnPLDispDop13_IsSanator\"
					," . ($itemsCount == 0 ? "null" : "EvnPLDispDop13_SumRick") . " as \"EvnPLDispDop13_SumRick\"
					," . ($itemsCount == 0 ? "null" : "RiskType_id") . " as \"RiskType_id\"
					," . ($itemsCount == 0 ? "null" : "EvnPLDispDop13_IsSchool") . " as \"EvnPLDispDop13_IsSchool\"
					," . ($itemsCount == 0 ? "null" : "EvnPLDispDop13_IsProphCons") . " as \"EvnPLDispDop13_IsProphCons\"
					," . ($itemsCount == 0 ? "null" : "EvnPLDispDop13_IsHypoten") . " as \"EvnPLDispDop13_IsHypoten\"
					," . ($itemsCount == 0 ? "null" : "EvnPLDispDop13_IsLipid") . " as \"EvnPLDispDop13_IsLipid\"
					," . ($itemsCount == 0 ? "null" : "EvnPLDispDop13_IsHypoglyc") . " as \"EvnPLDispDop13_IsHypoglyc\"
					," . ($itemsCount == 0 ? "null" : "HealthKind_id") . " as \"HealthKind_id\"
					," . ($itemsCount == 0 ? "null" : "EvnPLDispDop13_IsEndStage") . " as \"EvnPLDispDop13_IsEndStage\"
					," . ($itemsCount == 0 ? "null" : "EvnPLDispDop13_IsTwoStage") . " as \"EvnPLDispDop13_IsTwoStage\"
					," . ($itemsCount == 0 ? "null" : "CardioRiskType_id") . " as \"CardioRiskType_id\"
				from v_EvnPLDispDop13
				where EvnPLDispDop13_id = :EvnPLDispDop13_id
				limit 1
			";
			$result = $this->db->query($query, array(
				'EvnPLDispDop13_id' => $data['EvnPLDispDop13_id']
			));
			
			if ( is_object($result) ) {
				$resp = $result->result('array');

				if ( is_array($resp) && count($resp) > 0 ) {
					$resp[0]['EvnPLDispDop13_setDT'] = $data['EvnPLDispDop13_consDate'];
					$resp[0]['EvnPLDispDop13_consDT'] = $data['EvnPLDispDop13_consDate'];
					$resp[0]['EvnPLDispDop13_IsNewOrder'] = $data['EvnPLDispDop13_IsNewOrder'];
					$resp[0]['pmUser_id'] = $data['pmUser_id'];
					$resp[0]['EvnPLDispDop13_IsMobile'] = $data['EvnPLDispDop13_IsMobile'];
					$resp[0]['EvnPLDispDop13_IsOutLpu'] = $data['EvnPLDispDop13_IsOutLpu'];
					$resp[0]['Lpu_mid'] = $data['Lpu_mid'];
					$resp[0]['PayType_id'] = $data['PayType_id'];
					$resp[0]['MedStaffFact_id'] = !empty($data['session']['CurARM']['MedStaffFact_id'])?$data['session']['CurARM']['MedStaffFact_id']:null;

					$query = "
						select
							EvnPLDispDop13_id as \"EvnPLDispDop13_id\",
							Error_Code as \"Error_Code\",
							Error_Message as \"Error_Msg\"
						from p_EvnPLDispDop13_upd (
							EvnPLDispDop13_id := :EvnPLDispDop13_id
					";

					foreach ( $resp[0] as $key => $value ) {
						$query .= "," . $key . " := :" . $key;
					}

					$query .= "
						)
					";

					$resp[0]['EvnPLDispDop13_id'] = $data['EvnPLDispDop13_id'];

					$result = $this->db->query($query, $resp[0]);
					
					if ( is_object($result) ) {
						$resp = $result->result('array');

						if ( is_array($resp) && count($resp) > 0 && !empty($resp[0]['Error_Msg']) ) {
							$this->db->trans_rollback();
							return $resp;
						}
					}
				}
			}

			// Чистим атрибуты и услуги
			$attrArray = array(
				 'EvnVizitDispDop' // Посещения и услуги
				,'EvnUslugaDispDop' // Специально для удаления анкетирования
			);

			if ( $itemsCount == 0 ) {
				$attrArray[] = 'EvnDiagDopDisp'; // Ранее известные имеющиеся заболевания, впервые выявленные заболевания
				$attrArray[] = 'HeredityDiag'; // Наследственность по заболеваниям
				$attrArray[] = 'ProphConsult'; // Показания к углубленному профилактическому консультированию
				$attrArray[] = 'NeedConsult'; // Показания к консультации врача-специалиста
				$attrArray[] = 'DopDispInfoConsent';
			}

			foreach ( $attrArray as $attr ) {
				$deleteResult = $this->deleteAttributes($attr, $data['EvnPLDispDop13_id'], $data['pmUser_id']);

				if ( !empty($deleteResult) ) {
					$this->db->trans_rollback();
					return array(
						'success' => false,
						'Error_Msg' => $deleteResult . ' (строка ' . __LINE__ . ')'
					);
				}
			}

			if ( $itemsCount > 0 && count($DopDispInfoConsentToDel) > 0 ) {
				foreach ( $DopDispInfoConsentToDel as $DopDispInfoConsent_id ) {
					$query = "
						select
							Error_Code as \"Error_Code\",
							Error_Message as \"Error_Msg\"
						from p_DopDispInfoConsent_del (
							DopDispInfoConsent_id := :DopDispInfoConsent_id,
							pmUser_id := :pmUser_id
						)
					";
					$result = $this->db->query($query, array(
						'DopDispInfoConsent_id' => $DopDispInfoConsent_id,
						'pmUser_id' => $data['pmUser_id']
					));

					if ( is_object($result) ) {
						$res = $result->result('array');

						if ( is_array($res) && count($res) > 0 && !empty($res[0]['Error_Msg']) ) {
							$this->db->trans_rollback();
							return array(
								'success' => false,
								'Error_Msg' => $res[0]['Error_Msg']
							);
						}
					}
					else {
						$this->db->trans_rollback();
						return array(
							'success' => false,
							'Error_Msg' => 'Ошибка при выполнении запроса к базе данных (строка ' . __LINE__ . ')'
						);
					}
				}
			}
		}
		
		// проставляем признак отказа
		if ( $itemsCount == 0 ) {
			$query = "
				update EvnPLDisp set EvnPLDisp_IsRefusal = 2 where Evn_id = :EvnPLDisp_id
			";
			$this->db->query($query, array(
				'EvnPLDisp_id' => $data['EvnPLDispDop13_id']
			));
		} else {
			$query = "
				update EvnPLDisp set EvnPLDisp_IsRefusal = 1 where Evn_id = :EvnPLDisp_id
			";
			$this->db->query($query, array(
				'EvnPLDisp_id' => $data['EvnPLDispDop13_id']
			));
		}
		
		// второй этап
		$sec_resp = $this->queryResult("
			select 
				EvnPLDispDop13_id as \"EvnPLDispDop13_id\"
			from 
				v_EvnPLDispDop13 
			where 
				EvnPLDispDop13_fid = :EvnPLDispDop13_fid
		", array(
			'EvnPLDispDop13_fid' => $data['EvnPLDispDop13_id']
		));

		// на Екб - если есть второй этап, статус не меняем
		// меняем, только если нет карты 2-го этапа
		if ( !count($sec_resp) ) {
			$query = "
				update EvnPLBase set EvnPLBase_IsFinish = 1 where Evn_id = :EvnPLDispDop13_id;
				update EvnPLDispDop13 set EvnPLDispDop13_IsEndStage = 1 where Evn_id = :EvnPLDispDop13_id;
			";
			$this->db->query($query, array(
				'EvnPLDispDop13_id' => $data['EvnPLDispDop13_id']
			));
		}

		// Определяем записи, которые необходимо удалить
		// https://redmine.swan.perm.ru/issues/29017
		if ( count($DopDispInfoConsentList) > 0 ) {
			$query = "
				select
					 DDIC.DopDispInfoConsent_id as \"DopDispInfoConsent_id\"
					,EVDD.EvnVizitDispDop_id as \"EvnVizitDispDop_id\"
					,EUDD.EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\"
					,STL.SurveyType_id as \"SurveyType_id\"
				from v_DopDispInfoConsent DDIC
					left join v_EvnVizitDispDop EVDD on EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
					left join v_EvnUslugaDispDop EUDD on EUDD.EvnUslugaDispDop_pid = EVDD.EvnVizitDispDop_id
					left join v_SurveyTypeLink STL on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
				where DDIC.EvnPLDisp_id = :EvnPLDisp_id
					and DDIC.DopDispInfoConsent_id not in (" . implode(', ', $DopDispInfoConsentList) . ")
			";
			$result = $this->db->query($query, array(
				'EvnPLDisp_id' => $data['EvnPLDispDop13_id']
			));

			if ( !is_object($result) ) {
				$this->db->trans_rollback();
				return array(
					'success' => false,
					'Error_Msg' => 'Ошибка при выполнении запроса к базе данных (строка ' . __LINE__ . ')'
				);
			}

			$res = $result->result('array');

			if ( is_array($res) && count($res) > 0 ) {
				foreach ( $res as $array ) {
					// Удаляем посещения
					if ( !empty($array['EvnVizitDispDop_id']) ) {
						$resp_ddic = array();
						if (!empty($data['EvnPLDispDop13_IsNewOrder']) && $data['EvnPLDispDop13_IsNewOrder'] == 2 && !empty($array['SurveyType_id'])) {
							// Попытаемся найти новое согласие для посещения
							$resp_ddic = $this->queryResult("
								select
									DDIC.DopDispInfoConsent_id as \"DopDispInfoConsent_id\",
									STL.UslugaComplex_id as \"UslugaComplex_id\"
								from
									v_DopDispInfoConsent DDIC
									inner join v_SurveyTypeLink STL on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
									left join v_EvnVizitDispDop EVDD on EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
								where
									DDIC.EvnPLDisp_id = :EvnPLDisp_id
									and STL.SurveyType_id = :SurveyType_id
									and EVDD.EvnVizitDispDop_id is null
								limit 1
							", array(
								'EvnPLDisp_id' => $data['EvnPLDispDop13_id'],
								'SurveyType_id' => $array['SurveyType_id']
							));
						}

						if (!empty($resp_ddic[0]['DopDispInfoConsent_id'])) {
							// перевяжем услугу к новому согласию
							$query = "
								update
									EvnVizitDisp
								set
									DopDispInfoConsent_id = :DopDispInfoConsent_id
								where
									Evn_id = :EvnVizitDispDop_id;
									
								update
									EvnVizitDispDop
								set
									UslugaComplex_id = :UslugaComplex_id
								where
									Evn_id = :EvnVizitDispDop_id;
									
								update
									EvnUslugaDispDop
								set
									DopDispInfoConsent_id = :DopDispInfoConsent_id
								where
									Evn_id = :EvnUslugaDispDop_id;
									
								update
									EvnUsluga
								set
									UslugaComplex_id = :UslugaComplex_id
								where
									Evn_id = :EvnUslugaDispDop_id
								returning '' as \"Error_Msg\", 0 as \"Error_Code\";
							";
							$result = $this->db->query($query, array(
								'EvnVizitDispDop_id' => $array['EvnVizitDispDop_id'],
								'EvnUslugaDispDop_id' => $array['EvnUslugaDispDop_id'],
								'UslugaComplex_id' => $resp_ddic[0]['UslugaComplex_id'],
								'DopDispInfoConsent_id' => $resp_ddic[0]['DopDispInfoConsent_id'],
								'pmUser_id' => $data['pmUser_id']
							));

							if (!$result) {
								$this->db->trans_rollback();
								return array(
									'success' => false,
									'Error_Msg' => 'Ошибка при выполнении запроса к базе данных (строка ' . __LINE__ . ')'
								);
							}
						} else {
							$query = "
								select
									Error_Code as \"Error_Code\",
									Error_Message as \"Error_Msg\"
								from p_EvnVizitDispDop_del (
									EvnVizitDispDop_id := :EvnVizitDispDop_id,
									pmUser_id := :pmUser_id
								)
							";
							$result = $this->db->query($query, array('EvnVizitDispDop_id' => $array['EvnVizitDispDop_id'], 'pmUser_id' => $data['pmUser_id']));

							if (!is_object($result)) {
								$this->db->trans_rollback();
								return array(
									'success' => false,
									'Error_Msg' => 'Ошибка при выполнении запроса к базе данных (строка ' . __LINE__ . ')'
								);
							}

							$resTmp = $result->result('array');

							if (is_array($resTmp) && count($resTmp) > 0 && !empty($resTmp[0]['Error_Msg'])) {
								$this->db->trans_rollback();
								return array(
									'success' => false,
									'Error_Msg' => $resTmp[0]['Error_Msg']
								);
							}
						}
					}

					// Удаляем записи информированного добровольного согласия
					$query = "
						select
							Error_Code as \"Error_Code\",
							Error_Message as \"Error_Msg\"
						from p_DopDispInfoConsent_del (
							DopDispInfoConsent_id := :DopDispInfoConsent_id,
							pmUser_id := :pmUser_id
						)
					";
					$result = $this->db->query($query, array(
						'DopDispInfoConsent_id' => $array['DopDispInfoConsent_id'],
						'pmUser_id' => $data['pmUser_id']
					));

					if ( !is_object($result) ) {
						$this->db->trans_rollback();
						return array(
							'success' => false,
							'Error_Msg' => 'Ошибка при выполнении запроса к базе данных (строка ' . __LINE__ . ')'
						);
					}

					$resTmp = $result->result('array');

					if ( is_array($resTmp) && count($resTmp) > 0 && !empty($resTmp[0]['Error_Msg']) ) {
						$this->db->trans_rollback();
						return array(
							'success' => false,
							'Error_Msg' => $resTmp[0]['Error_Msg']
						);
					}
				}
			}
		}

		$this->db->trans_commit();

		return array(
			'success' => true,
			'Error_Msg' => '',
			'EvnPLDispDop13_id' => $data['EvnPLDispDop13_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'Server_id' => $data['Server_id']
		);
	}
	
	/**
	 *	Получение диагноза по коду
	 */	
	function getDiagIdByCode($diag_code)
	{
		$query = "
			select
				Diag_id as \"Diag_id\"
			from v_Diag
			where Diag_Code = :Diag_Code
			limit 1
		";
		
		$result = $this->db->query($query, array('Diag_Code' => $diag_code));
	
        if (is_object($result))
        {
            $resp = $result->result('array');
			if (count($resp) > 0) {
				return $resp[0]['Diag_id'];
			}
        }
		
		return false;
	}
	
	/**
	 *	Получение данных карты
	 */	
	function getEvnPLDispDop13Data($data)
	{
		$query = "
			SELECT
				EvnPLDispDop13_id as \"EvnPLDispDop13_id\",
				PersonEvn_id as \"PersonEvn_id\",
				Server_id as \"Server_id\"
			FROM
				v_EvnPLDispDop13 EPLDD
			WHERE
				EPLDD.EvnPLDispDop13_id = :EvnPLDisp_id
			LIMIT 1
		";
        $result = $this->db->query($query, $data);

        if (is_object($result))
        {
            $resp = $result->result('array');
			if (count($resp) > 0) {
				return $resp[0];
			}
        }
		
		return false;
	}
	
	/**
	 *	Сохранение анкетирования
	 */	
	function saveDopDispQuestionGrid($data) {
		// Стартуем транзакцию
		$this->db->trans_begin();

		// получаем данные о карте ДД
		$dd = $this->getEvnPLDispDop13Data($data);

		if ( empty($dd) ) {
			$this->db->trans_rollback();
			return array('success' => false, 'Error_Msg' => 'Ошибка получения данных карты диспансеризации');
		}

		$data['PersonEvn_id'] = $dd['PersonEvn_id'];
		$data['Server_id'] = $dd['Server_id'];

		$sql = "
			select
				 evdd.EvnVizitDispDop_id as \"EvnVizitDispDop_id\",
				 eudd.EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\",
				 stl.DispClass_id as \"DispClass_id\",
				 st.SurveyType_Code as \"SurveyType_Code\"
			from v_EvnUslugaDispDop eudd
				inner join v_EvnVizitDispDop evdd on evdd.EvnVizitDispDop_id = eudd.EvnUslugaDispDop_pid
				inner join v_DopDispInfoConsent ddic on ddic.DopDispInfoConsent_id = evdd.DopDispInfoConsent_id
				inner join v_SurveyTypeLink stl on stl.SurveyTypeLink_id = ddic.SurveyTypeLink_id
				inner join v_SurveyType st on st.SurveyType_id = stl.SurveyType_id
			where evdd.EvnVizitDispDop_pid = :EvnVizitDispDop_pid
				and st.SurveyType_Code IN (19,27)
				and cast(eudd.EvnUslugaDispDop_didDT as date) < :DopDispQuestion_setDate
			limit 1
		";
		$res = $this->db->query($sql, array(
			'EvnVizitDispDop_pid' => $data['EvnPLDisp_id'],
			'DopDispQuestion_setDate' => $data['DopDispQuestion_setDate']
		));

		if ( is_object($res) ) {
			$resp = $res->result('array');
			if ( is_array($resp) && count($resp) > 0 ) {
				$this->db->trans_rollback();
				if ($resp[0]['SurveyType_Code'] == 19) {
					return array(array('Error_Msg' => 'Дата любого осмотра / исследования не может быть больше даты осмотра врача-терапевта (ВОП).'));
				} else {
					return array(array('Error_Msg' => 'Дата любого осмотра / исследования не может быть больше даты осмотра врача-педиатра (ВОП).'));
				}
			}
		}
		
		// Нужно сохранять услугу по анкетированию (refs #20465)
		// Ищем услугу с UslugaComplex_id для SurveyType_Code = 2, если нет то создаём новую, иначе обновляем.
		$query = "
			select
				STL.UslugaComplex_id as \"UslugaComplex_id\", -- услуга которую нужно сохранить
				EUDDData.EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\"
			from v_SurveyTypeLink STL
				inner join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
				inner join v_DopDispInfoConsent ddic on ddic.SurveyTypeLink_id = stl.SurveyTypeLink_id
				LEFT JOIN LATERAL (
					select 
						EvnUslugaDispDop_id
					from
						v_EvnUslugaDispDop EUDD
					where
						EUDD.EvnUslugaDispDop_pid = :EvnPLDisp_id and EUDD.UslugaComplex_id IN (select UslugaComplex_id from v_SurveyTypeLink where SurveyType_id = STL.SurveyType_id)
						and COALESCE(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
					limit 1
				) EUDDData on true
			where
				ST.SurveyType_Code = 2
				and ddic.EvnPLDisp_id = :EvnPLDisp_id
			limit 1
		";
		$result = $this->db->query($query, $data);

		if ( !is_object($result) ) {
			$this->db->trans_rollback();
			return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных (получение идентификатора услуги)');
		}

		$resp = $result->result('array');

		if ( !is_array($resp) || count($resp) == 0 ) {
			$this->db->trans_rollback();
			return array('success' => false, 'Error_Msg' => 'Ошибка при получении идентификатора услуги');
		}

		// Сохраняем услугу
		if (!empty($resp[0]['EvnUslugaDispDop_id'])) {
			$data['EvnUslugaDispDop_id'] = $resp[0]['EvnUslugaDispDop_id'];
			$proc = 'p_EvnUslugaDispDop_upd';
		} else {
			$data['EvnUslugaDispDop_id'] = null;
			$proc = 'p_EvnUslugaDispDop_ins';
		}

		if (empty($data['UslugaComplex_id'])) {
			$data['UslugaComplex_id'] = $resp[0]['UslugaComplex_id'];
		}

		if (empty($data['PayType_id'])) {
			$data['PayType_id'] = $this->getFirstResultFromQuery("select PayType_id from v_PayType where PayType_SysNick = 'dopdisp' limit 1");
		}

		$query = "
			select
				EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from " . $proc . " (
				EvnUslugaDispDop_id := :EvnUslugaDispDop_id,
				EvnUslugaDispDop_pid := :EvnPLDisp_id,
				Lpu_id := :Lpu_id,
				Server_id := :Server_id,
				EvnDirection_id := NULL,
				PersonEvn_id := :PersonEvn_id,
				PayType_id := :PayType_id,
				UslugaPlace_id := 1,
				EvnUslugaDispDop_setDT := NULL,
				UslugaComplex_id := :UslugaComplex_id,
				EvnUslugaDispDop_didDT := :DopDispQuestion_setDate,
				ExaminationPlace_id := NULL,
				Diag_id := :Diag_id,
				DopDispDiagType_id := :DopDispDiagType_id,
				EvnUslugaDispDop_DeseaseStage := :DeseaseStage,
				LpuSection_uid := :LpuSection_uid,
				MedPersonal_id := :MedPersonal_id,
				MedStaffFact_id := :MedStaffFact_id,
				EvnUslugaDispDop_ExamPlace := NULL,
				EvnPrescrTimetable_id := null,
				EvnPrescr_id := null,
				pmUser_id := :pmUser_id
			)
		";
		$result = $this->db->query($query, $data);

		if ( !is_object($result) ) {
			$this->db->trans_rollback();
			return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение услуги)');
		}

		$resp = $result->result('array');

		if ( !is_array($resp) || count($resp) == 0 ) {
			$this->db->trans_rollback();
			return array('success' => false, 'Error_Msg' => 'Ошибка при сохранении услуги');
		}
		else if ( !empty($resp[0]['Error_Msg']) ) {
			$this->db->trans_rollback();
			return array('success' => false, 'Error_Msg' => $resp[0]['Error_Msg']);
		}

		if (!empty($resp[0]['EvnUslugaDispDop_id'])) {
			$data['EvnUslugaDispDop_id'] = $resp[0]['EvnUslugaDispDop_id'];
		} else {
			$this->db->trans_rollback();
			return array('success' => false, 'Error_Msg' => 'Ошибка при сохранении услуги');
		}

		ConvertFromWin1251ToUTF8($data['DopDispQuestionData']);
		$items = json_decode($data['DopDispQuestionData'], true);
		
		$this->load->model('EvnDiagDopDisp_model', 'evndiagdopdisp');
		$this->load->model('HeredityDiag_model', 'hereditydiag');
		$this->load->model('ProphConsult_model', 'prophconsult');
		$this->load->model('NeedConsult_model', 'needconsult');

		$Sex_id = $this->getFirstResultFromQuery("
			select
				ps.Sex_id as \"Sex_id\"
			from
				v_EvnPLDisp epld
				inner join v_PersonState ps on ps.Person_id = epld.Person_id
			where
				epld.EvnPLDisp_id = :EvnPLDisp_id
		", [
			'EvnPLDisp_id' => $data['EvnPLDisp_id']
		], true);

		// Получаем существующие данные из БД
		$ExistingDopDispQuestionData = array();

		$query = "
			select
				 QuestionType_id as \"QuestionType_id\"
				,DopDispQuestion_id as \"DopDispQuestion_id\"
				,DopDispQuestion_ValuesStr as \"DopDispQuestion_ValuesStr\"
			from v_DopDispQuestion
			where EvnPLDisp_id = :EvnPLDisp_id
		";
		$result = $this->db->query($query, array(
			'EvnPLDisp_id' => $data['EvnPLDisp_id']
		));

		if ( !is_object($result) ) {
			$this->db->trans_rollback();
			return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных (получение списка имеющихся данных анкетирования)');
		}

		$resp = $result->result('array');

		if ( is_array($resp) && count($resp) > 0 ) {
			foreach ( $resp as $dataArray ) {
				if ($dataArray['QuestionType_id'] == 8 && !empty($data['NeedCalculation']) && $data['NeedCalculation'] == 1) {
					$this->evndiagdopdisp->delEvnDiagDopDispBefore($data, $dataArray['DopDispQuestion_ValuesStr']);
				}
				$ExistingDopDispQuestionData[$dataArray['QuestionType_id']] = $dataArray['DopDispQuestion_id'];
			}
		}
		
		$data['EvnDiagDopDisp_setDate'] = $data['DopDispQuestion_setDate'];

		$dataToUpdate = array(
			'ProphConsult' => array(),
			'NeedConsult' => array()
		);

		$useCounterOne = false;
		$counterOne = 0;
		$usePohud = false;
		$isPohud = false;
		$isPohudDepend = false;
		$isPohudDependTwo = false;
		$usePoorNutrition = false;
		$isPoorNutrition = false;
		$isPoorNutritionTwo = false;
		$useOnko = false;
		$isOnko = false;
		$isOnkoTwo = false;
		$isOnkoThree = false;
		$useHirurg = false;
		$isHirurg = false;
		$isHirurgTwo = false;
		$isHirurgThree = false;
		$useAlco = false;
		$alcoSum = 0;
		foreach ($items as $item) {
			if (!empty($data['NeedCalculation']) && $data['NeedCalculation'] == 1) {
				switch ($item['QuestionType_id']) {
					// Ранее известные имеющиеся заболевания
					case 2:
					case 94:
					case 142:
					case 675:
						if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] == 2)) {
							$this->evndiagdopdisp->addEvnDiagDopDispBefore($data, $this->getDiagIdByCode('I20.9'));
						} else {
							$this->evndiagdopdisp->delEvnDiagDopDispBefore($data, $this->getDiagIdByCode('I20.9'));
						}
						break;
					case 3:
					case 95:
					case 145:
					case 721:
						if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] == 2)) {
							$this->evndiagdopdisp->addEvnDiagDopDispBefore($data, $this->getDiagIdByCode('Z03.4'));
						} else {
							$this->evndiagdopdisp->delEvnDiagDopDispBefore($data, $this->getDiagIdByCode('Z03.4'));
						}
						break;
					case 4:
					case 96:
					case 146:
					case 676:
					case 722:
						if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] == 2)) {
							$this->evndiagdopdisp->addEvnDiagDopDispBefore($data, $this->getDiagIdByCode('I67.9'));
						} else {
							$this->evndiagdopdisp->delEvnDiagDopDispBefore($data, $this->getDiagIdByCode('I67.9'));
						}
						break;
					case 5:
						if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] == 2)) {
							$this->evndiagdopdisp->addEvnDiagDopDispBefore($data, $this->getDiagIdByCode('E14.9')); // Диагноз изменен согласно https://redmine.swan.perm.ru/issues/20964
						} else {
							$this->evndiagdopdisp->delEvnDiagDopDispBefore($data, $this->getDiagIdByCode('E14.9')); // Диагноз изменен согласно https://redmine.swan.perm.ru/issues/20964
						}
						break;
					case 6:
					case 100:
					case 681:
						if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] == 2)) {
							$this->evndiagdopdisp->addEvnDiagDopDispBefore($data, $this->getDiagIdByCode('K29.7'));
						} else {
							$this->evndiagdopdisp->delEvnDiagDopDispBefore($data, $this->getDiagIdByCode('K29.7'));
						}
						break;
					case 7:
					case 101:
					case 148:
					case 682:
						if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] == 2)) {
							$this->evndiagdopdisp->addEvnDiagDopDispBefore($data, $this->getDiagIdByCode('N28.8'));
						} else {
							$this->evndiagdopdisp->delEvnDiagDopDispBefore($data, $this->getDiagIdByCode('N28.8'));
						}
						break;
					case 8:
					case 102:
					case 144:
					case 683:
					case 717:
						if ($item['DopDispQuestion_IsTrue'] == 2) {
							$this->evndiagdopdisp->addEvnDiagDopDispBefore($data, $item['DopDispQuestion_ValuesStr']);
						}
						break;
					case 9:
					case 98:
					case 678:
						if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] == 2)) {
							$this->evndiagdopdisp->addEvnDiagDopDispBefore($data, $this->getDiagIdByCode('A16.2'));
						} else {
							$this->evndiagdopdisp->delEvnDiagDopDispBefore($data, $this->getDiagIdByCode('A16.2'));
						}
						break;
					case 99:
					case 143:
					case 679:
					case 715:
						if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] == 2)) {
							$this->evndiagdopdisp->addEvnDiagDopDispBefore($data, $this->getDiagIdByCode('O24.3'));
						} else {
							$this->evndiagdopdisp->delEvnDiagDopDispBefore($data, $this->getDiagIdByCode('O24.3'));
						}
						break;

					// Наследственность по заболеваниям
					case 10:
						if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
							$this->hereditydiag->addHeredityDiag($data, $this->getDiagIdByCode('Z03.4'), ($item['DopDispQuestion_ValuesStr'] == 2) ? 1 : 2);
						} else {
							$this->hereditydiag->delHeredityDiagByDiag($data, $this->getDiagIdByCode('Z03.4'));
						}
						break;
					case 11:
					case 104:
					case 689:
						if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
							$this->hereditydiag->addHeredityDiag($data, $this->getDiagIdByCode('I64.'), ($item['DopDispQuestion_ValuesStr'] == 2) ? 1 : 2);
						} else {
							$this->hereditydiag->delHeredityDiagByDiag($data, $this->getDiagIdByCode('I64.'));
						}
						break;
					case 12:
						if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
							$this->hereditydiag->addHeredityDiag($data, $this->getDiagIdByCode('C16.9'), ($item['DopDispQuestion_ValuesStr'] == 2) ? 1 : 2);
						} else {
							$this->hereditydiag->delHeredityDiagByDiag($data, $this->getDiagIdByCode('C16.9'));
						}
						break;
					case 105:
						if ($item['DopDispQuestion_IsTrue'] == 2) {
							$this->hereditydiag->addHeredityDiag($data, $item['DopDispQuestion_ValuesStr'], ($item['DopDispQuestion_ValuesStr'] == 2) ? 1 : 2);
						}
						break;
					case 690:
						if ($item['DopDispQuestion_IsTrue'] == 2) {
							$this->hereditydiag->addHeredityDiag($data, $item['DopDispQuestion_ValuesStr'], ($item['DopDispQuestion_ValuesStr'] == 2) ? 1 : 2);
						}

						$type = 'del';
						if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
							$type = 'ins';
						}
						$dataToUpdate['NeedConsult'][$type]['31_1'] = array(
							'Post_id' => 31,
							'ConsultationType_id' => 1
						);
						break;

					// Показания к углубленному профилактическому консультированию
					case 831:
					case 872:
					case 913:
					case 954:
						$type = 'del';
						if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
							$type = 'ins';
						}
						$dataToUpdate['ProphConsult'][$type][9] = array(
							'RiskFactorType_id' => 9
						);
						break;
					case 673:
					case 713:
					case 815:
					case 856:
					case 897:
					case 938:
					case 1013:
					case 1053:
					case 1093:
					case 1133:
						$type = 'del';
						if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
							$type = 'ins';
						}
						$dataToUpdate['ProphConsult'][$type][18] = array(
							'RiskFactorType_id' => 18
						);
						break;
					case 731:
					case 732:
					case 1036:
					case 1037:
					case 1076:
					case 1077:
					case 1116:
					case 1117:
					case 1156:
					case 1157:
						$type = 'del';
						if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
							$type = 'ins';
						}
						$dataToUpdate['ProphConsult'][$type][25] = array(
							'RiskFactorType_id' => 25
						);
						$dataToUpdate['NeedConsult'][$type]['63_2'] = array(
							'Post_id' => 63,
							'ConsultationType_id' => 2
						);
						break;
					case 736:
					case 1041:
					case 1081:
					case 1121:
					case 1161:
						$useCounterOne = true;
						$type = 'del';
						if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
							$counterOne++;
							$type = 'ins';
						}
						$dataToUpdate['ProphConsult'][$type][26] = array(
							'RiskFactorType_id' => 26
						);
						$dataToUpdate['NeedConsult'][$type]['37_1'] = array(
							'Post_id' => 37,
							'ConsultationType_id' => 1
						);
						break;
					case 737:
					case 1042:
					case 1082:
					case 1122:
					case 1162:
						$useCounterOne = true;
						$type = 'del';
						if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
							$counterOne++;
							$type = 'ins';
						}
						$dataToUpdate['ProphConsult'][$type][22] = array(
							'RiskFactorType_id' => 22
						);
						$dataToUpdate['NeedConsult'][$type]['43_2'] = array(
							'Post_id' => 43,
							'ConsultationType_id' => 2
						);
						break;
					case 738:
					case 1043:
					case 1083:
					case 1123:
					case 1163:
						$useCounterOne = true;
						$type = 'del';
						if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
							$counterOne++;
							$type = 'ins';
						}
						$dataToUpdate['ProphConsult'][$type][23] = array(
							'RiskFactorType_id' => 23
						);
						$dataToUpdate['NeedConsult'][$type]['10656_2'] = array(
							'Post_id' => 10656,
							'ConsultationType_id' => 2
						);
						break;
					case 739:
					case 1044:
					case 1084:
					case 1124:
					case 1164:
						$useCounterOne = true;
						$type = 'del';
						if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
							$counterOne++;
							$type = 'ins';
						}
						$dataToUpdate['ProphConsult'][$type][24] = array(
							'RiskFactorType_id' => 24
						);
						$dataToUpdate['NeedConsult'][$type]['37_1'] = array(
							'Post_id' => 37,
							'ConsultationType_id' => 1
						);
						break;
					case 746:
					case 1051:
					case 1091:
					case 1131:
					case 1171:
						$type = 'del';
						if (!empty($item['DopDispQuestion_Answer']) && is_numeric($item['DopDispQuestion_Answer']) && intval($item['DopDispQuestion_Answer']) >= 5) {
							$type = 'ins';
						}
						$dataToUpdate['ProphConsult'][$type][28] = array(
							'RiskFactorType_id' => 28
						);
						break;
					case 848:
					case 889:
					case 930:
					case 971:
						$type = 'del';
						if ($item['DopDispQuestion_ValuesStr'] == 1) {
							$type = 'ins';
						}
						$dataToUpdate['ProphConsult'][$type][6] = array(
							'RiskFactorType_id' => 6
						);
						break;
					case 851:
					case 892:
					case 933:
					case 974:
						$type = 'del';
						if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
							$type = 'ins';
						}
						$dataToUpdate['ProphConsult'][$type][19] = array(
							'RiskFactorType_id' => 19
						);
						break;
					case 1040:
					case 1080:
					case 1120:
					case 1160:
						$type = 'del';
						if ($item['DopDispQuestion_IsTrue'] == 1) {
							$type = 'ins';
						}
						$dataToUpdate['ProphConsult'][$type][6] = array(
							'RiskFactorType_id' => 6
						);
						break;
					case 849:
					case 890:
					case 931:
					case 972:
					case 1038:
					case 1078:
					case 1118:
					case 1158:
						$usePoorNutrition = true;
						if ($item['DopDispQuestion_IsTrue'] == 1) {
							$isPoorNutrition = true;
						}
						break;
					case 1039:
					case 1079:
					case 1119:
					case 1159:
						$usePoorNutrition = true;
						if ($item['DopDispQuestion_IsTrue'] == 1) {
							$isPoorNutritionTwo = true;
						}
						break;
					case 850:
					case 891:
					case 932:
					case 973:
						$usePoorNutrition = true;
						if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
							$isPoorNutritionTwo = true;
						}
						break;
						
					// Показания к консультации врача-специалиста
					case 693:
					case 694:
					case 695:
					case 726:
					case 727:
					case 728:
						$type = 'del';
						if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
							$type = 'ins';
						}
						$dataToUpdate['NeedConsult'][$type]['37_2'] = array(
							'Post_id' => 37,
							'ConsultationType_id' => 2
						);
						break;
					case 835:
					case 876:
					case 917:
					case 958:
					case 836:
					case 877:
					case 918:
					case 959:
					case 837:
					case 878:
					case 919:
					case 960:
						$type = 'del';
						if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
							$type = 'ins';
						}
						$dataToUpdate['NeedConsult'][$type]['37_1'] = array(
							'Post_id' => 37,
							'ConsultationType_id' => 1
						);
						break;
					case 832:
					case 873:
					case 914:
					case 955:
						$useHirurg = true;
						if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
							$isHirurg = true;
							$isHirurgTwo = true;
							$isHirurgThree = true;
						}
						break;
					case 843:
					case 884:
					case 925:
					case 966:
						$useHirurg = true;
						if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
							$isHirurg = true;
						}
						break;
					case 844:
					case 885:
					case 926:
					case 967:
						$useHirurg = true;
						if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
							$isHirurgTwo = true;
						}
						break;
					case 845:
					case 886:
					case 927:
					case 968:
						$useHirurg = true;
						if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
							$isHirurgThree = true;
						}
						break;
					case 740:
					case 1045:
					case 1085:
					case 1125:
					case 1165:
						$useCounterOne = true;
						$type = 'del';
						if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
							$counterOne++;
							$type = 'ins';
						}
						
						$Post_id = 86;
						if ($Sex_id == 2) {
							$Post_id = 12;
						}

						$dataToUpdate['NeedConsult'][$type][$Post_id . '_2'] = array(
							'Post_id' => $Post_id,
							'ConsultationType_id' => 2
						);
						break;
					case 833:
					case 874:
					case 915:
					case 956:
					case 1024:
					case 1064:
					case 1104:
					case 1144:
						$useCounterOne = true;
						$type = 'del';
						if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
							$counterOne++;
							$type = 'ins';
						}

						$dataToUpdate['NeedConsult'][$type]['182_2'] = array(
							'Post_id' => 182,
							'ConsultationType_id' => 2
						);
						break;
					case 741:
					case 742:
					case 743:
					case 1046:
					case 1086:
					case 1126:
					case 1166:
					case 1047:
					case 1087:
					case 1127:
					case 1167:
						$useCounterOne = true;
						if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
							$counterOne++;
						}
						break;
					case 1048:
					case 1088:
					case 1128:
					case 1168:
						$useCounterOne = true;
						$useOnko = true;
						if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
							$counterOne++;
							$isOnko = true;
						}
						break;
					case 1049:
					case 1089:
					case 1129:
					case 1169:
						$useOnko = true;
						if ($item['DopDispQuestion_IsTrue'] == 1) {
							$isOnkoTwo = true;
						}
						break;
					case 1050:
					case 1090:
					case 1130:
					case 1170:
						$useOnko = true;
						if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
							$isOnkoThree = true;
						}
						break;
					case 701:
						$usePohud = true;
						if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
							$isPohud = true;
						}
						break;
					case 702:
						$usePohud = true;
						if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
							$isPohudDepend = true;
						}
						break;
					case 703:
						$usePohud = true;
						if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
							$isPohudDependTwo = true;
						}
						break;
					case 852:
					case 893:
					case 934:
					case 975:
					case 853:
					case 894:
					case 935:
					case 976:
					case 854:
					case 895:
					case 936:
					case 977:
						$useAlco = true;
						$alcoSum += intval($item['DopDispQuestion_ValuesStr']) - 64;
						break;
				}
			}

			if (array_key_exists($item['QuestionType_id'], $ExistingDopDispQuestionData)) {
				$item['DopDispQuestion_id'] = $ExistingDopDispQuestionData[$item['QuestionType_id']];
			}

			$item['DopDispQuestion_Answer'] = toAnsi($item['DopDispQuestion_Answer']);

			if (!empty($item['DopDispQuestion_id']) && $item['DopDispQuestion_id'] > 0) {
				$proc = 'p_DopDispQuestion_upd';
			} else {
				$proc = 'p_DopDispQuestion_ins';
				$item['DopDispQuestion_id'] = null;
			}

			if (empty($item['DopDispQuestion_IsTrue'])) {
				$item['DopDispQuestion_IsTrue'] = null;
			}

			$query = "
				select
					DopDispQuestion_id as \"DopDispQuestion_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from {$proc} (
					DopDispQuestion_id := :DopDispQuestion_id, 
					EvnPLDisp_id := :EvnPLDisp_id, 
					QuestionType_id := :QuestionType_id, 
					DopDispQuestion_IsTrue := :DopDispQuestion_IsTrue, 
					DopDispQuestion_Answer := :DopDispQuestion_Answer, 
					DopDispQuestion_ValuesStr := :DopDispQuestion_ValuesStr,
					pmUser_id := :pmUser_id
				)
			";
			$result = $this->db->query($query, array(
				'EvnPLDisp_id' => $data['EvnPLDisp_id'],
				'DopDispQuestion_id' => $item['DopDispQuestion_id'],
				'QuestionType_id' => $item['QuestionType_id'],
				'DopDispQuestion_IsTrue' => $item['DopDispQuestion_IsTrue'],
				'DopDispQuestion_Answer' => $item['DopDispQuestion_Answer'],
				'DopDispQuestion_ValuesStr' => !empty($item['DopDispQuestion_ValuesStr']) ? $item['DopDispQuestion_ValuesStr'] : null,
				'pmUser_id' => $data['pmUser_id']
			));

			if ( !is_object($result) ) {
				$this->db->trans_rollback();
				return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение ответов на вопросы)');
			}

			$resp = $result->result('array');

			if ( !is_array($resp) || count($resp) == 0 ) {
				$this->db->trans_rollback();
				return array('success' => false, 'Error_Msg' => 'Ошибка при сохранении ответов на вопросы');
			} else if ( !empty($resp[0]['Error_Msg']) ) {
				$this->db->trans_rollback();
				return array('success' => false, 'Error_Msg' => $resp[0]['Error_Msg']);
			}
		}

		if ($useCounterOne) {
			$type = 'del';
			if ($counterOne >= 3) {
				$type = 'ins';
			}
			$dataToUpdate['ProphConsult'][$type][27] = array(
				'RiskFactorType_id' => 27
			);
			$dataToUpdate['NeedConsult'][$type]['63_2'] = array(
				'Post_id' => 19,
				'ConsultationType_id' => 1
			);
		}

		if ($usePohud) {
			$type = 'del';
			if ($isPohud && ($isPohudDepend || $isPohudDependTwo)) {
				$type = 'ins';
			}
			$dataToUpdate['NeedConsult'][$type]['63_2'] = array(
				'Post_id' => 31,
				'ConsultationType_id' => 2
			);
		}

		if (!empty($dataToUpdate['ProphConsult']['ins'])) {
			foreach($dataToUpdate['ProphConsult']['ins'] as $key => $value) {
				unset($dataToUpdate['ProphConsult']['del'][$key]);
				$this->prophconsult->addProphConsult($data, $value['RiskFactorType_id']);
			}
		}

		if (!empty($dataToUpdate['ProphConsult']['del'])) {
			foreach($dataToUpdate['ProphConsult']['del'] as $key => $value) {
				$this->prophconsult->delProphConsult($data, $value['RiskFactorType_id']);
			}
		}

		if (!empty($dataToUpdate['NeedConsult']['ins'])) {
			foreach($dataToUpdate['NeedConsult']['ins'] as $key => $value) {
				unset($dataToUpdate['NeedConsult']['del'][$key]);
				$this->needconsult->addNeedConsult($data, $value['Post_id'], $value['ConsultationType_id']);
			}
		}

		if (!empty($dataToUpdate['NeedConsult']['del'])) {
			foreach($dataToUpdate['NeedConsult']['del'] as $key => $value) {
				$this->needconsult->delNeedConsult($data, $value['Post_id'], $value['ConsultationType_id']);
			}
		}

		// http://redmine.swan.perm.ru/issues/84088
		// Добавляем повторную проверку на наличие дублей
		$sql = "
			select EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\"
			from v_EvnUslugaDispDop
			where EvnUslugaDispDop_pid = :EvnPLDisp_id
				and UslugaComplex_id = :UslugaComplex_id
				and EvnUslugaDispDop_id != :EvnUslugaDispDop_id
			limit 1
		";
		$res = $this->db->query($sql, $data);

		if (!is_object($res)) {
			$this->db->trans_rollback();
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (получение идентификаторов дублирующих посещений)'));
		}

		$resp = $res->result('array');

		if (is_array($resp) && count($resp) > 0 && !empty($resp[0]['EvnUslugaDispDop_id'])) {
			$this->db->trans_rollback();
			return array(array('Error_Msg' => 'Обнаружены дубль по услуге анкетирования. Произведен откат транзакции. Пожалуйста, повторите сохранение.'));
		}

		$this->db->trans_commit();

		return array(
			'success' => true,
			'Error_Msg' => ''
		);
	}
	
	/**
	 *	Загрузка списка изменившихся согласий
	 */	
	function getDopDispInfoConsentChanges($data) {
		$ddicold = $this->loadDopDispInfoConsent($data);
		$data['EvnPLDispDop13_consDate'] = $data['EvnPLDispDop13_newConsDate'];
		$ddicnew = $this->loadDopDispInfoConsent($data);
		$ddicnewarr = array();
		foreach($ddicnew as $ddicnewone) {
			$ddicnewarr[$ddicnewone['SurveyTypeLink_id']] = $ddicnewone['SurveyType_Name'];
		}

		$arrchanged = array();
		foreach($ddicold as $ddicoldone) {
			if (empty($ddicnewarr[$ddicoldone['SurveyTypeLink_id']])) {
				$arrchanged[] = $ddicoldone['SurveyType_Name'];
			}
		}

		return array('Error_Msg' => '', 'changed' => implode($arrchanged, '<br>'));
	}

	/**
	 *  Проверка пациент в регистре ли ВОВ
	 */
	function checkPersonInWowRegistry($data) {
		$query = "
			select PersonPrivilegeWOW_id as \"PersonPrivilegeWOW_id\" from v_PersonPrivilegeWOW where Person_id = :Person_id limit 1
		";
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$resp = $result->result('array');
			if (!empty($resp[0]['PersonPrivilegeWOW_id'])) {
				return array('Error_Msg' => '', 'inWowRegister' => 1);
			}
		}

		return array('Error_Msg' => '', 'inWowRegister' => 0);
	}

	/**
	 * Получение модификации возраста
	 */
	function getAgeModification($data, $persData) {
		$dateX = $this->getNewDVNDate();
		$newDVN = (!empty($dateX) && strtotime($data['onDate']) >= strtotime($dateX));

		if (!in_array($this->getRegionNick(), ['kz']) && strtotime($data['onDate']) >= strtotime('01.01.2018')) {
			// ДВН с 2018 года.
			$personPrivilegeCodeList = $this->getPersonPrivilegeCodeList();

			if ( count($personPrivilegeCodeList) > 0 ) {
				if (
					($newDVN != true || $persData['age'] < 40)
					&& $persData['age'] % 3 != 0
				) {
					$resp_check = $this->queryResult("
						select
							pp.PersonPrivilege_id as \"PersonPrivilege_id\"
						from
							v_PersonPrivilege pp
							inner join v_PrivilegeType pt on pt.PrivilegeType_id = pp.PrivilegeType_id
						where
							pp.Person_id = :Person_id
							and pt.PrivilegeType_Code in ('" . implode("','", $personPrivilegeCodeList) . "')
							and pp.PersonPrivilege_begDate <= :EvnPLDispDop13_YearEndDate
							and (pp.PersonPrivilege_endDate > :EvnPLDispDop13_YearEndDate or pp.PersonPrivilege_endDate is null)
						limit 1
					", array(
						'Person_id' => $persData['person_id'],
						'EvnPLDispDop13_YearEndDate' => mb_substr($data['onDate'], 0, 4) . '-12-31'
					));

					if (!empty($resp_check[0]['PersonPrivilege_id'])) {
						$persData['age'] = round($persData['age'] / 3) / 3; // округление до ближайшего кратного трём
					}
				}
			}
			else if (in_array($this->getRegionNick(), ['ufa', 'ekb', 'kareliya', 'krasnoyarsk', 'penza', 'astra'])) {
				if (
					($newDVN != true || $persData['age'] < 40)
					&& $persData['age'] % 3 != 0
				) {
					$resp_check = $this->queryResult("
						select
							PersonPrivilegeWOW_id as \"PersonPrivilegeWOW_id\"
						from
							v_PersonPrivilegeWOW
						where
							Person_id = :Person_id
						limit 1
					", array(
						'Person_id' => $persData['person_id']
					));

					if (!empty($resp_check[0]['PersonPrivilegeWOW_id'])) {
						$persData['age'] = round($persData['age'] / 3) / 3; // округление до ближайшего кратного трём
					}
				}
			}
			elseif (
				// ДВН с 2020 года.
				strtotime($data['onDate']) >= strtotime('2020-01-01') &&
				// Кроме регионов Казахстан, Карелия, Хакасия, Бурятия, Уфа.
				!in_array($this->getRegionNick(), ['kz', 'kareliya', 'khak', 'buryatiya', 'ufa'])
			) {
				if (
					// от 18 до 39 (включительно) лет.
					$persData['age'] >= 18 && $persData['age'] <= 39 &&
					// не кранен 3-м.
					$persData['age'] % 3 != 0
				) {
					// округление до ближайшего кратного трём.
					$persData['age'] = round($persData['age'] / 3) / 3;
				}
			}
		}
		else {
			$persData['age'] = round($persData['age'] / 3) / 3; // округление до ближайшего кратного трём
		}

		if ( $newDVN == false ) {
			if ($persData['age'] == 18) {
				$persData['age'] = 21;
			}
		}

		if ($persData['age'] > 99) {
			$persData['age'] = 99;
		}

		return $persData;
	}

	/**
	 * Загрузка согласий
	 *
	 * Вызов:
	 *   Диспансеризация взрослого населения – 1 этап: (Просмотр|Редактирование) > Осмотр, исследование.
	 */
	function loadDopDispInfoConsent($data) {
		$filter = "";
		$joinList = array();
		$params = array(
			'EvnPLDispDop13_id' => $data['EvnPLDispDop13_id'],
			'Person_id' => $data['Person_id'],
			'DispClass_id' => $data['DispClass_id']
		);

		$dateX = $this->getNewDVNDate();
		if ($data['DispClass_id'] == 1 && !empty($data['EvnPLDispDop13_IsNewOrder']) && $data['EvnPLDispDop13_IsNewOrder'] == 2) {
			$data['EvnPLDispDop13_consDate'] = date('Y', strtotime($data['EvnPLDispDop13_consDate'])) . '-12-31';
		} else if (!in_array($this->getRegionNick(), ['ekb', 'perm']) && !empty($dateX) && $data['DispClass_id'] == 2 && !empty($data['EvnPLDispDop13_fid'])) {
			// достаём дату согласия из первого этапа, т.к. связки загружаются именно на неё
			$resp_first = $this->queryResult("
				select
					to_char(epldd13f.EvnPLDispDop13_consDT, 'YYYY-MM-DD') as \"EvnPLDispDop13_firstConsDate\",
					coalesce(epldd13f.EvnPLDispDop13_IsNewOrder, 1) as \"EvnPLDispDop13_IsNewOrder\"
				from
					v_EvnPLDispDop13 epldd13f
				where
					epldd13f.EvnPLDispDop13_id = :EvnPLDispDop13_fid
			", array(
				'EvnPLDispDop13_fid' => $data['EvnPLDispDop13_fid']
			));
			if (!empty($resp_first[0]['EvnPLDispDop13_firstConsDate']) && $resp_first[0]['EvnPLDispDop13_firstConsDate'] < $dateX && $resp_first[0]['EvnPLDispDop13_IsNewOrder'] == 1) {
				$data['EvnPLDispDop13_consDate'] = $resp_first[0]['EvnPLDispDop13_firstConsDate'];
			}
		}

		$params['EvnPLDispDop13_consDate'] = $data['EvnPLDispDop13_consDate'];

		if ($this->getRegionNick() == 'ufa') { // для уфы дополнительно отфильтровываем услуги посещений по LpuLevel 
			$filter .= " and (COALESCE(ucat.UslugaCategory_SysNick, '') != 'lpusection' or ((case when LpuLevel.LpuLevel_code in (2,6) then '6' when LpuLevel.LpuLevel_code in (3,5) then '5' when LpuLevel.LpuLevel_code in (1,8) then '8' end) = left(UC.UslugaComplex_Code,1)))";
			$filter .= " and (UC.UslugaComplex_id is not null or Stl.UslugaComplex_id is null)";

			$joinList[] = "left join v_Lpu lpu on lpu.Lpu_id = :Lpu_id";
			$joinList[] = "left join v_LpuLevel LpuLevel on LpuLevel.LpuLevel_id = lpu.LpuLevel_id";
			$joinList[] = "left join v_UslugaCategory ucat on ucat.UslugaCategory_id = UC.UslugaCategory_id";

			$params['Lpu_id'] = $data['Lpu_id'];
		}

		$noFilterByAgeInFirstTime = "";
		if (strtotime($data['EvnPLDispDop13_consDate']) >= strtotime('01.04.2015')) {
			if (!$this->checkIsPrimaryFlow(array(
				'Person_id' => $data['Person_id'],
				'EvnPLDisp_id' => $data['EvnPLDispDop13_id']
			))) {
				$noFilterByAgeInFirstTime = "or STL.SurveyTypeLink_IsPrimaryFlow = 2";
			}
		}

		if (
			$data['DispClass_id'] == 1
			&& (
				($this->getRegionNick() == 'khak' && strtotime($data['EvnPLDispDop13_consDate']) >= strtotime('01.05.2015'))
				||
				($this->getRegionNick() == 'pskov' && strtotime($data['EvnPLDispDop13_consDate']) >= strtotime('01.05.2015') && strtotime($data['EvnPLDispDop13_consDate']) < strtotime($dateX))
			)
		) {
			$filter .= " and (STL.SurveyTypeLink_IsPay = 2 or STL.SurveyTypeLink_Period = 2)";
		}

		if ($this->getRegionNick() == 'penza' && strtotime($data['EvnPLDispDop13_consDate']) < strtotime($dateX)) {
			// фильтруем по SurveyTypeLink_IsWow
			$PersonPrivilegeWOW_id = $this->getFirstResultFromQuery("
				select PersonPrivilegeWOW_id
				from v_PersonPrivilegeWOW ppw
				where Person_id = :Person_id
				limit 1
			", $params);

			if ($data['DispClass_id'] == 1 && !empty($PersonPrivilegeWOW_id)) {
				$filter .= " and STL.SurveyTypeLink_IsWow = 2";
			}
			else {
				$filter .= " and COALESCE(STL.SurveyTypeLink_IsWow, 1) = 1";
			}
		}

		$select = "
			select
				COALESCE(MAX(DDIC.DopDispInfoConsent_id), -MAX(STL.SurveyTypeLink_id)) as \"DopDispInfoConsent_id\",
				MAX(DDIC.EvnPLDisp_id) as \"EvnPLDispDop13_id\",
				MAX(STL.SurveyTypeLink_id) as \"SurveyTypeLink_id\",
				COALESCE(MAX(STL.SurveyTypeLink_IsNeedUsluga), 1) as \"SurveyTypeLink_IsNeedUsluga\",
				COALESCE(MAX(STL.SurveyTypeLink_IsDel), 1) as \"SurveyTypeLink_IsDel\",
				MAX(ST.SurveyType_Code) as \"SurveyType_Code\",
				MAX(ST.SurveyType_Name) as \"SurveyType_Name\",
				case WHEN MAX(DDIC.DopDispInfoConsent_id) is null or MAX(DDIC.DopDispInfoConsent_IsAgree) = 2 then 1 else 0 end as \"DopDispInfoConsent_IsAgree\",
				case WHEN MAX(DDIC.DopDispInfoConsent_IsEarlier) = 2 then 1 else 0 end as \"DopDispInfoConsent_IsEarlier\",
				case WHEN COALESCE(MAX(SurveyTypeLink_IsImpossible), 1) = 1 then 'hidden' WHEN MAX(DDIC.DopDispInfoConsent_IsImpossible) = 2 then '1' else '0' end as \"DopDispInfoConsent_IsImpossible\",
				MAX(STL.SurveyTypeLink_IsUslPack) as \"SurveyTypeLink_IsUslPack\",
				case when (MAX(STL.SurveyTypeLink_IsPrimaryFlow) = 2 and :age not between COALESCE(MAX(STL.SurveyTypeLink_From), 0) and  COALESCE(MAX(STL.SurveyTypeLink_To), 999)) then 0 else 1 end as \"DopDispInfoConsent_IsAgeCorrect\",
				case when MAX(ST.SurveyType_Code) IN (1,48) then 0 else 1 end as \"sortOrder\"
			from v_SurveyTypeLink STL
				left join v_DopDispInfoConsent DDIC on DDIC.SurveyTypeLink_id = STL.SurveyTypeLink_id
					and DDIC.EvnPLDisp_id = :EvnPLDispDop13_id
				left join v_UslugaComplex UC on UC.UslugaComplex_id = STL.UslugaComplex_id
				left join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
				LEFT JOIN LATERAL (
					select EvnUslugaDispDop_id
					from v_EvnUslugaDispDop
					where UslugaComplex_id = UC.UslugaComplex_id
						and EvnUslugaDispDop_rid = :EvnPLDispDop13_id
						and COALESCE(EvnUslugaDispDop_IsVizitCode, 1) = 1
					limit 1
				) EUDD on true
				" . implode(' ', $joinList) . "
			where 
				COALESCE(STL.DispClass_id, :DispClass_id) = :DispClass_id -- этап
				and (COALESCE(STL.Sex_id, :sex_id) = :sex_id) -- по полу
				and ((:age between COALESCE(SurveyTypeLink_From, 0) and  COALESCE(SurveyTypeLink_To, 999))
					{$noFilterByAgeInFirstTime}
				) -- по возрасту, в принципе по библии Иссак лет 800 жил же
				and (COALESCE(STL.SurveyTypeLink_IsDel, 1) = 1 or (EUDD.EvnUslugaDispDop_id is not null and DDIC.DopDispInfoConsent_id IS NOT NULL))
				and (STL.SurveyTypeLink_begDate is null or STL.SurveyTypeLink_begDate <= :EvnPLDispDop13_consDate)
				and (STL.SurveyTypeLink_endDate is null or STL.SurveyTypeLink_endDate >= :EvnPLDispDop13_consDate)
				and COALESCE(STL.SurveyTypeLink_ComplexSurvey, 1) = 1
				and COALESCE(STL.SurveyTypeLink_IsEarlier, 1) = 1
				and (STL.SurveyTypeLink_Period is null or STL.SurveyTypeLink_From % STL.SurveyTypeLink_Period = :age % STL.SurveyTypeLink_Period)
				" . $filter . "
		";

		$union = "";

		$resp_ps = $this->queryResult("
			select
				person_id,
				COALESCE(Sex_id, 3) as sex_id,
				dbo.Age2(Person_BirthDay, :EvnPLDispDop13_YearEndDate) as age
			from v_PersonState ps
			where ps.Person_id = :Person_id
			limit 1
		", array(
			'Person_id' => $data['Person_id'],
			'EvnPLDispDop13_YearEndDate' => mb_substr($data['EvnPLDispDop13_consDate'], 0, 4) . '-12-31'
		));

		if (empty($resp_ps[0]['person_id'])) {
			throw new Exception('Ошибка получения данных по пациенту');
		}

		$originalAge = $resp_ps[0]['age'];

		$resp_ps[0] = $this->getAgeModification(array(
			'onDate' => $data['EvnPLDispDop13_consDate']
		), $resp_ps[0]);

		$params['sex_id'] = $resp_ps[0]['sex_id'];
		$params['age'] = $resp_ps[0]['age'];

		if ($this->getRegionNick() == 'ufa' && $originalAge != $resp_ps[0]['age']) {
			// грузим ещё по одной возрастной группе
			$union = "union all
			" . str_replace(":age", ":originalAge", $select)."
					and ST.SurveyType_Code not in (1, 19)
				group by ST.SurveyType_id, STL.SurveyTypeLink_IsDel
			";
			$params['originalAge'] = $resp_ps[0]['originalAge'];
		}

		$query = "
			{$select}
			group by ST.SurveyType_id, STL.SurveyTypeLink_IsDel
			
			{$union}	
			
			order by \"sortOrder\", \"SurveyType_Code\"		
		";
		if ($this->isDebug) {
			$debug_sql = getDebugSql($query, $params);
		}
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			$response = $result->result('array');

			/**
			 * Добавляем к результату "Цитологическое исследование".
			 * Выполняется при сохранении карты.
			 *
			 * @see saveEvnPLDispDop13()
			 * @see loadDopDispInfoConsent()
			 */
			if (isset($data['isDopUsl']) && $data['isDopUsl']==1) {
				// нужно ещё согласие цитологической услуги
				$query = "
					select
						DDIC.DopDispInfoConsent_id as \"DopDispInfoConsent_id\",
						DDIC.EvnPLDisp_id as \"EvnPLDispDop13_id\",
						DDIC.SurveyTypeLink_id as \"SurveyTypeLink_id\",
						STL.SurveyTypeLink_IsNeedUsluga as \"SurveyTypeLink_IsNeedUsluga\",
						STL.SurveyTypeLink_IsDel as \"SurveyTypeLink_IsDel\",
						ST.SurveyType_Code as \"SurveyType_Code\",
						'Цитологическое исследование' as \"SurveyType_Name\",
						case WHEN DDIC.DopDispInfoConsent_IsAgree = 2 then 1 else 0 end as \"DopDispInfoConsent_IsAgree\",
						case WHEN DDIC.DopDispInfoConsent_IsEarlier = 2 then 1 else 0 end as \"DopDispInfoConsent_IsEarlier\",
						case WHEN DDIC.DopDispInfoConsent_IsImpossible = 2 then 1 else 0 end as \"DopDispInfoConsent_IsImpossible\",
						STL.SurveyTypeLink_IsUslPack as \"SurveyTypeLink_IsUslPack\",
						1 as \"DopDispInfoConsent_IsAgeCorrect\"
					from
						v_DopDispInfoConsent DDIC
						inner join v_SurveyTypeLink stl on stl.SurveyTypeLink_id = ddic.SurveyTypeLink_id
						inner join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
					where
						DDIC.EvnPLDisp_id = :EvnPLDispDop13_id
						and stl.SurveyTypeLink_ComplexSurvey = 2
					limit 1
				";
				$respcyto = $this->queryResult($query, array(
					'EvnPLDispDop13_id' => $data['EvnPLDispDop13_id']
				));

				if (!empty($respcyto[0]['DopDispInfoConsent_id'])) {
					$response[] = $respcyto[0];
				}
			}

			// #196187
			/*if (in_array(getRegionNick(), array('vologda'))) {
				// убираем те исследования, которые не положены, т.к. человек прошёл их в прошлом/позапрошлом году refs #167041
				$resp_prev = $this->queryResult("
					with mv as (
						select
							dbo.Age2(Person_BirthDay, cast(:Year::varchar || '-12-31' as date)) as age
						from v_PersonState ps
						where
							ps.Person_id = :Person_id
					)

					select
						st.SurveyType_Code as \"SurveyType_Code\",
						date_part('year', evdd.EvnVizitDispDop_setDate) as \"Year\"
					from
						v_EvnPLDisp epld
						inner join v_DopDispInfoConsent ddic on ddic.EvnPLDisp_id = epld.EvnPLDisp_id
						inner join v_EvnVizitDispDop evdd on ddic.DopDispInfoConsent_id = evdd.DopDispInfoConsent_id
						inner join v_SurveyTypeLink stl on stl.SurveyTypeLink_id = ddic.SurveyTypeLink_id
						inner join v_SurveyType st on st.SurveyType_id = stl.SurveyType_id
					where
						epld.Person_id = :Person_id
						and st.SurveyType_Code in (14,20,21)
						and date_part('year', evdd.EvnVizitDispDop_setDate) >= :Year
						and ((select age from mv) <= 64 or st.SurveyType_Code <> 14)
				", array(
					'Person_id' => $data['Person_id'],
					'Year' => mb_substr($data['EvnPLDispDop13_consDate'], 0, 4) - 2 // за 2 года
				));

				$exceptSurveyTypes = array();
				foreach ($resp_prev as $one_prev) {
					if (
						(in_array($one_prev['SurveyType_Code'], array(14, 21)) && $one_prev['Year'] == mb_substr($data['EvnPLDispDop13_consDate'], 0, 4) - 1)
						|| ($one_prev['SurveyType_Code'] == 20 && $one_prev['Year'] >= mb_substr($data['EvnPLDispDop13_consDate'], 0, 4) - 2 && $one_prev['Year'] < mb_substr($data['EvnPLDispDop13_consDate'], 0, 4))
					) {
						$exceptSurveyTypes[] = $one_prev['SurveyType_Code'];
					}
				}

				foreach ($response as $key => $value) {
					if (in_array($value['SurveyType_Code'], $exceptSurveyTypes)) {
						unset($response[$key]);
					}
				}
			}*/

			// Запрос для получения возраста.
			$query_get_person_age = "
					with mv as (
						select
							cast(substring(:EvnPLDispDop13_consDate, 1, 4) || '-12-31' as timestamp) as dt
					)

					select
						dbo.Age2(Person_BirthDay, (select dt from mv)) as \"Person_Age\"
					from
						v_PersonState ps
					where
						ps.Person_id = :Person_id
					limit 1
				";

			if ($this->getRegionNick() == 'astra' && $data['DispClass_id'] == 1 && strtotime($data['EvnPLDispDop13_consDate']) >= strtotime($dateX)) {
				$persData = $this->queryResult($query_get_person_age, $params);

				$Person_Age = 0;
				if (!empty($persData[0]['Person_Age'])) {
					$Person_Age = $persData[0]['Person_Age'];
				}

				// помечаем обязательные
				foreach ($response as $key => $value) {
					if (
						empty($value['DopDispInfoConsent_IsAgeCorrect'])
						|| (
							$value['SurveyType_Code'] == 14
							&& $Person_Age % 2 > 0
							&& $Person_Age <= 64
						)
						|| (
							$value['SurveyType_Code'] == 21
							&& $Person_Age % 2 > 0
						)
						|| (
							$value['SurveyType_Code'] == 20
							&& $Person_Age % 3 > 0
						)
					) {
						if ($value['DopDispInfoConsent_id'] < 0) {
							$response[$key]['DopDispInfoConsent_IsAgree'] = 0;
						}
					} else {
						$response[$key]['SurveyType_Name'] .= ' *';
					}
				}
			}

			return $response;
		}
		else {
			return false;
		}
	}

	/**
	 * Проверка первичного прохождения ДВН/ПОВН
	 */
	function checkIsPrimaryFlow($data) {
		if (!in_array(getRegionNick(), array('astra', 'krasnoyarsk', 'kareliya', 'perm', 'vologda'))) {
			return false;
		}

		if (empty($data['Person_id'])) {
			if (!empty($data['EvnPLDisp_id'])) {
				$data['Person_id'] = $this->getFirstResultFromQuery("select Person_id from v_EvnPLDisp where EvnPLDisp_id = :EvnPLDisp_id", array('EvnPLDisp_id' => $data['EvnPLDisp_id']));
				if (empty($data['Person_id'])) {
					return false;
				}
			} else {
				return false;
			}
		}

		$queryParams = array('Person_id' => $data['Person_id']);
		$filter = "";

		if (!empty($data['EvnPLDisp_id'])) {
			$queryParams['EvnPLDisp_id'] = $data['EvnPLDisp_id'];
			$filter .= " and EvnPLDisp_id <> :EvnPLDisp_id";
		}

		$query = "
			select
				EvnPLDisp_id as \"EvnPLDisp_id\"
			from
				v_EvnPLDisp EPLD
			where
				COALESCE(DispClass_id,1) IN (1,5)
				and Person_id = :Person_id
				{$filter}
			limit 1
		";

		$resp = $this->queryResult($query, $queryParams);
		if (!empty($resp[0]['EvnPLDisp_id'])) {
			return true;
		}

		$archive_database_enable = $this->config->item('archive_database_enable');
		if (!empty($archive_database_enable)) {
			// если включена архивная БД, то надо проверить и на архивной БД
			$archdb = $this->load->database('archive', true);
			$resp = $this->queryResult($query, $queryParams, $archdb);
			if (!empty($resp[0]['EvnPLDisp_id'])) {
				return true;
			}
		}

		return false;
	}

	/**
	 *	Загрузка согласий (для ext6)
	 */
	function loadDopDispInfoConsentWithUsluga($data) {
		$filter = "";
		$joinList = array();
		$params = array(
			'EvnPLDispDop13_id' => $data['EvnPLDispDop13_id'],
			'Person_id' => $data['Person_id'],
			'DispClass_id' => $data['DispClass_id'],
			'EvnPLDispDop13_consDate' => $data['EvnPLDispDop13_consDate']
		);

		$dateX = $this->getNewDVNDate();
		if (!empty($data['EvnPLDispDop13_IsNewOrder']) && $data['EvnPLDispDop13_IsNewOrder'] == 2) {
			$data['EvnPLDispDop13_consDate'] = date('Y', strtotime($data['EvnPLDispDop13_consDate'])) . '-12-31';
		} else if (!in_array(getRegionNick(), [ 'ekb', 'perm' ]) && !empty($dateX) && $data['EvnPLDispDop13_consDate'] >= $dateX && $data['DispClass_id'] == 2 && !empty($data['EvnPLDispDop13_fid'])) {
			// достаём дату согласия из первого этапа, т.к. связки загружаются именно на неё
			$resp_first = $this->queryResult("
				select
					to_char(epldd13f.EvnPLDispDop13_consDT, 'YYYY-MM-DD') as \"EvnPLDispDop13_firstConsDate\",
					COALESCE(epldd13f.EvnPLDispDop13_IsNewOrder, 1) as \"EvnPLDispDop13_IsNewOrder\"
				from
					v_EvnPLDispDop13 epldd13f
				where
					epldd13f.EvnPLDispDop13_id = :EvnPLDispDop13_fid
			", array(
				'EvnPLDispDop13_fid' => $data['EvnPLDispDop13_fid']
			));
			if (!empty($resp_first[0]['EvnPLDispDop13_firstConsDate']) && $resp_first[0]['EvnPLDispDop13_IsNewOrder'] == 1) {
				$data['EvnPLDispDop13_consDate'] = $resp_first[0]['EvnPLDispDop13_firstConsDate'];
			}
		}

		$params['EvnPLDispDop13_consDate'] = $data['EvnPLDispDop13_consDate'];

		if ($this->getRegionNick() == 'ufa') { // для уфы дополнительно отфильтровываем услуги посещений по LpuLevel 
			$filter .= " and (COALESCE(ucat.UslugaCategory_SysNick, '') != 'lpusection' or ((case when LpuLevel.LpuLevel_code in (2,6) then '6' when LpuLevel.LpuLevel_code in (3,5) then '5' when LpuLevel.LpuLevel_code in (1,8) then '8' end) = left(UC.UslugaComplex_Code,1)))";
			$filter .= " and (UC.UslugaComplex_id is not null or Stl.UslugaComplex_id is null)";

			$joinList[] = "left join v_Lpu lpu on lpu.Lpu_id = :Lpu_id";
			$joinList[] = "left join v_LpuLevel LpuLevel on LpuLevel.LpuLevel_id = lpu.LpuLevel_id";
			$joinList[] = "left join v_UslugaComplex UC on UC.UslugaComplex_id = STL.UslugaComplex_id";
			$joinList[] = "left join v_UslugaCategory ucat on ucat.UslugaCategory_id = UC.UslugaCategory_id";

			$params['Lpu_id'] = $data['Lpu_id'];
		}

		$noFilterByAgeInFirstTime = "";
		if (in_array($this->getRegionNick(), array('astra','kareliya','perm', 'vologda')) && strtotime($data['EvnPLDispDop13_consDate']) >= strtotime('01.04.2015')) {
			$noFilterByAgeInFirstTime = "or (STL.SurveyTypeLink_IsPrimaryFlow = 2 and not exists (
				select
					EvnPLDispDop13_id
				from
					v_EvnPLDispDop13 EPLD
				where
					COALESCE(DispClass_id,1) = 1
					and Person_id = :Person_id
					and EvnPLDispDop13_id <> COALESCE(:EvnPLDispDop13_id, 0::bigint)
					limit 1
			))";
		}

		if ($data['DispClass_id'] == 1 && in_array(getRegionNick(), array('pskov', 'khak')) && strtotime($data['EvnPLDispDop13_consDate']) >= strtotime('01.05.2015')) {
			$filter .= " and (STL.SurveyTypeLink_IsPay = 2 or STL.SurveyTypeLink_Period = 2)";
		}

		if ($this->regionNick == 'penza') {
			// фильтруем по SurveyTypeLink_IsWow
			$PersonPrivilegeWOW_id = $this->getFirstResultFromQuery("
				select PersonPrivilegeWOW_id
				from v_PersonPrivilegeWOW ppw
				where Person_id = :Person_id
				limit 1
			", $params);

			if ($data['DispClass_id'] == 1 && !empty($PersonPrivilegeWOW_id)) {
				$filter .= " and STL.SurveyTypeLink_IsWow = 2";
			}
			else {
				$filter .= " and COALESCE(STL.SurveyTypeLink_IsWow, 1) = 1";
			}
		}

		$selectST = "
			select
				ST.SurveyType_id as SurveyType_id
				,STL.SurveyTypeLink_IsDel as SurveyTypeLink_IsDel
				,MAX(STL.SurveyTypeLink_id) as SurveyTypeLink_id
			from v_SurveyTypeLink STL
				left join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
				" . implode(' ', $joinList) . "
			where 
				STL.SurveyType_id not in (1,48)
				and COALESCE(STL.DispClass_id, '1') = '1'
				and (COALESCE(STL.Sex_id, :sex_id) = :sex_id)
				and (
					(:age between COALESCE(SurveyTypeLink_From, 0) and COALESCE(SurveyTypeLink_To, 999))
					{$noFilterByAgeInFirstTime}
				)
				and (STL.SurveyTypeLink_begDate is null or STL.SurveyTypeLink_begDate <= '2020-01-27')
				and (STL.SurveyTypeLink_endDate is null or STL.SurveyTypeLink_endDate >= '2020-01-27')
				and COALESCE(STL.SurveyTypeLink_ComplexSurvey, 1) = 1
				and COALESCE(STL.SurveyTypeLink_IsEarlier, 1) = 1
				and (STL.SurveyTypeLink_Period is null or STL.SurveyTypeLink_From % STL.SurveyTypeLink_Period = :age % STL.SurveyTypeLink_Period)
				" . $filter . "
		";

		$select = "
			with survtypes as (
				{$selectST}
			group by ST.SurveyType_id, STL.SurveyTypeLink_IsDel
			)
			select
				STL.UslugaComplex_id as \"UslugaComplex_id\"
				,EUDD.EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\"
				,DDIC.DopDispInfoConsent_id as \"DopDispInfoConsent_id\"
				,COALESCE(DDIC.DopDispInfoConsent_id, -STL.SurveyTypeLink_id) as \"DopDispInfoConsent_id\"
				,DDIC.EvnPLDisp_id as \"EvnPLDispDop13_id\"
				,STL.SurveyTypeLink_id as \"SurveyTypeLink_id\"
				,COALESCE(STL.SurveyTypeLink_IsNeedUsluga, 1) as \"SurveyTypeLink_IsNeedUsluga\"
				,COALESCE(STL.SurveyTypeLink_IsDel, 1) as \"SurveyTypeLink_IsDel\"
				,ST.SurveyType_Code as \"SurveyType_Code\"
				,ST.SurveyType_Name as \"SurveyType_Name\"
				,ST.SurveyType_RecNotNeeded as \"SurveyType_RecNotNeeded\"
				,ST.SurveyType_IsVizit as \"SurveyType_IsVizit\"
				,case WHEN DDIC.DopDispInfoConsent_id is null or DDIC.DopDispInfoConsent_IsAgree = 2 then 1 else 0 end as \"DopDispInfoConsent_IsAgree\"
				,case WHEN DDIC.DopDispInfoConsent_IsEarlier = 2 then 1 else 0 end as \"DopDispInfoConsent_IsEarlier\"
				,case WHEN COALESCE(SurveyTypeLink_IsImpossible, 1) = 1 then 'hidden' WHEN DDIC.DopDispInfoConsent_IsImpossible = 2 then '1' else '0' end as \"DopDispInfoConsent_IsImpossible\"
				,STL.SurveyTypeLink_IsUslPack as \"SurveyTypeLink_IsUslPack\"
				,case when (STL.SurveyTypeLink_IsPrimaryFlow = 2 and :age not between COALESCE(STL.SurveyTypeLink_From, 0) and COALESCE(STL.SurveyTypeLink_To, 999)) then 0 else 1 end as \"DopDispInfoConsent_IsAgeCorrect\"
				,EUDD.EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\"
				,EUDD.EvnUslugaDispDop_didDate as \"EvnUslugaDispDop_didDate\"
				,EUDD.EvnUslugaDispDop_Lpu_Nick as \"EvnUslugaDispDop_Lpu_Nick\"
				,EUDD.EvnUslugaDispDop_MedPersonalFio as \"EvnUslugaDispDop_MedPersonalFio\"
				,survtypes.SurveyType_id as \"SurveyType_id\"
				,survtypes.SurveyTypeLink_IsDel as \"SurveyTypeLink_IsDel\"
				,survtypes.SurveyTypeLink_id as \"SurveyTypeLink_id\"
				,OutUsluga.OutUsluga_id as \"OutUsluga_id\"
				,OutUsluga.OutUsluga_Date as \"OutUsluga_Date\"
				,OutUsluga.OutUsluga_Lpu_Nick as \"OutUsluga_Lpu_Nick\"
				,OutUsluga.OutUslugaComplex_id as \"OutUslugaComplex_id\"
				,OutUsluga.OutMedPersonalFIO as \"OutMedPersonalFIO\"
			from
				survtypes
				left join v_SurveyTypeLink STL on STL.SurveyTypeLink_id = survtypes.SurveyTypeLink_id
				left join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
				left join v_DopDispInfoConsent DDIC on DDIC.SurveyTypeLink_id = STL.SurveyTypeLink_id
					and DDIC.EvnPLDisp_id = :EvnPLDispDop13_id
				left join v_UslugaComplex UC on UC.UslugaComplex_id = STL.UslugaComplex_id
				LEFT JOIN LATERAL (
					select 
						EUDD1.EvnUslugaDispDop_id as EvnUslugaDispDop_id
						,to_char(EUDD1.EvnUslugaDispDop_didDate, 'DD.MM.YYYY') as EvnUslugaDispDop_didDate
						,lpu1.Lpu_Nick as EvnUslugaDispDop_Lpu_Nick
						,MSF1.Person_Fio as EvnUslugaDispDop_MedPersonalFio
					from v_EvnUslugaDispDop EUDD1
						left join v_MedStaffFact MSF1 on MSF1.MedStaffFact_id = EUDD1.MedStaffFact_id
						left join v_Lpu lpu1 on lpu1.Lpu_id = EUDD1.Lpu_id
					where EUDD1.UslugaComplex_id = UC.UslugaComplex_id
						and EUDD1.EvnUslugaDispDop_rid = :EvnPLDispDop13_id
						and COALESCE(EUDD1.EvnUslugaDispDop_IsVizitCode, 1) = 1
						limit 1
				) EUDD on true
				LEFT JOIN LATERAL (
					select
						EU.EvnUsluga_id as OutUsluga_id
						,EU.UslugaComplex_id as OutUslugaComplex_id
						,L.Lpu_Nick as OutUsluga_Lpu_Nick
						,MP.Person_Fio as OutMedPersonalFIO
						,to_char(EP.EvnPrescr_didDate, 'DD.MM.YYYY') as OutUsluga_Date
					from 
						v_SurveyTypeLink STL2
						inner join v_SurveyType ST2 on ST2.SurveyType_id = STL2.SurveyType_id
						inner join v_EvnUsluga EU on EU.UslugaComplex_id = STL2.UslugaComplex_id
						inner join v_EvnPrescr EP on EP.EvnPrescr_id = EU.EvnPrescr_id
						left join v_MedPersonal MP on MP.MedPersonal_id = EU.MedPersonal_id
						left join v_Lpu L on L.Lpu_id = EU.Lpu_id
					where 
						STL2.SurveyType_id = STL.SurveyType_id
						and EU.Person_id = :Person_id
						and YEAR(EU.EvnUsluga_setDate) = YEAR(dbo.tzGetDate())
						and EP.EvnPrescr_IsExec = 2
						and (ST.SurveyType_IsVizit = 1 or EU.EvnUsluga_pid = :EvnPLDispDop13_id)
					order by EU.EvnUsluga_setDate DESC
					limit 1
				) OutUsluga on true
			where (COALESCE(STL.SurveyTypeLink_IsDel, 1) = 1 or (EUDD.EvnUslugaDispDop_id is not null and DDIC.DopDispInfoConsent_id IS NOT NULL))
				and ST.SurveyType_Code not in (1,48)
		";

		$row = $this->getFirstRowFromQuery("select
      	COALESCE(Sex_id, 3) as \"sex_id\",
		dbo.Age2(Person_BirthDay, cast(substring(:EvnPLDispDop13_consDate::varchar, 1, 4) || '-12-31' as TIMESTAMP)) as \"age\"
   		from v_PersonState ps
   		where ps.Person_id = :Person_id
   		limit 1", $params);
		$params['sex_id'] = $row['sex_id'];
		$params['age'] = $row['age'];

		$params = $this->getAgeModification(array(
			'onDate' => $data['EvnPLDispDop13_consDate']
		), $params);
		
		$query = "
			{$select}
			order by ST.SurveyType_Code	
		";
		
		//~ echo getDebugSql($query, $params);exit;
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			$response = $result->result('array');

			if (isset($data['isDopUsl']) && $data['isDopUsl']==1) {
				// нужно ещё согласие цитологической услуги
				$query = "
					select
						DDIC.DopDispInfoConsent_id as \"DopDispInfoConsent_id\",
						DDIC.EvnPLDisp_id as \"EvnPLDispDop13_id\",
						DDIC.SurveyTypeLink_id as \"SurveyTypeLink_id\",
						STL.SurveyTypeLink_IsNeedUsluga as \"SurveyTypeLink_IsNeedUsluga\",
						STL.SurveyTypeLink_IsDel as \"SurveyTypeLink_IsDel\",
						ST.SurveyType_Code as \"SurveyType_Code\",
						'Цитологическое исследование' as \"SurveyType_Name\",
						case WHEN DDIC.DopDispInfoConsent_IsAgree = 2 then 1 else 0 end as \"DopDispInfoConsent_IsAgree\",
						case WHEN DDIC.DopDispInfoConsent_IsEarlier = 2 then 1 else 0 end as \"DopDispInfoConsent_IsEarlier\",
						case WHEN DDIC.DopDispInfoConsent_IsImpossible = 2 then 1 else 0 end as \"DopDispInfoConsent_IsImpossible\",
						STL.SurveyTypeLink_IsUslPack as \"SurveyTypeLink_IsUslPack\",
						1 as \"DopDispInfoConsent_IsAgeCorrect\"
					from
						v_DopDispInfoConsent DDIC
						inner join v_SurveyTypeLink stl on stl.SurveyTypeLink_id = ddic.SurveyTypeLink_id
						inner join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
					where
						DDIC.EvnPLDisp_id = :EvnPLDispDop13_id
						and stl.SurveyTypeLink_ComplexSurvey = 2
						limit 1
				";
				$respcyto = $this->queryResult($query, array(
					'EvnPLDispDop13_id' => $data['EvnPLDispDop13_id']
				));

				if (!empty($respcyto[0]['DopDispInfoConsent_id'])) {
					$response[] = $respcyto[0];
				}
			}

			return $response;
		}
		else {
			return false;
		}
	}
	
	/**
	 *	Удаление карты
	 */	
	function deleteEvnPLDispDop13($data) {
		$query = "
			select count(EvnPLDispDop13_id) as cnt
			from v_EvnPLDispDop13
			where EvnPLDispDop13_fid = :EvnPLDispDop13_id
		";

		$result = $this->db->query($query, array(
			'EvnPLDispDop13_id' => $data['EvnPLDispDop13_id']
		));

		if ( !is_object($result) ) {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (проверка наличия карты ДВН 2-го этапа) (' . __LINE__ . ')'));
		}

		$response = $result->result('array');

		if ( !is_array($response) || count($response) == 0 ) {
			return array(array('Error_Msg' => 'Ошибка при проверке наличия карты ДВН 2-го этапа (' . __LINE__ . ')'));
		}
		else if ( !empty($response[0]['cnt']) ) {
			return array(array('Error_Msg' => 'Удаление невозможно, т.к. существует связанная карта ДВН 2-го этапа (' . __LINE__ . ')'));
		}

		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_EvnPLDispDop13_del (
				EvnPLDispDop13_id := :EvnPLDispDop13_id,
				pmUser_id := :pmUser_id
			)
		";
		$result = $this->db->query($query, array(
			'EvnPLDispDop13_id' => $data['EvnPLDispDop13_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if (!is_object($result)) {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление талона ДД) (' . __LINE__ . ')'));
		}

		$response = $result->result('array');

		if (!is_array($response) || count($response) == 0) {
			return array(array('Error_Msg' => 'Ошибка при удалении талона ДД (' . __LINE__ . ')'));
		} else if (!empty($response[0]['Error_Msg'])) {
			return $response;
		}

		$attrArray = array(
			'HeredityDiag',
			'ProphConsult',
			'NeedConsult',
			'DopDispInfoConsent'
		);

		foreach ($attrArray as $attr) {
			$deleteResult = $this->deleteAttributes($attr, $data['EvnPLDispDop13_id'], $data['pmUser_id']);

			if (!empty($deleteResult)) {
				return array(array(
					'success' => false,
					'Error_Msg' => $deleteResult . ' (строка ' . __LINE__ . ')'
				));
			}
		}

		return $response;
	}

	
	/**
	 * Получение талонов ДД для истории лечения человека
	 * @param array $data Фильтры
	 * @return array Ассоциативный массив талонов ДД человека
	 */
	function loadEvnPLDispDop13ForPerson($data) {
		$query = "
			select
				EPLDD.EvnPLDispDop13_id as \"EvnPLDispDop13_id\",
				EPLDD.Person_id as \"Person_id\",
				EPLDD.Server_id as \"Server_id\",
				EPLDD.PersonEvn_id as \"PersonEvn_id\",
				EPLDD.EvnPLDispDop13_VizitCount as \"EvnPLDispDop13_VizitCount\",
				IsFinish.YesNo_Name as \"EvnPLDispDop13_IsFinish\",
				to_char(EPLDD.EvnPLDispDop13_setDate, 'DD.MM.YYYY') as \"EvnPLDispDop13_setDate\",
				to_char(EPLDD.EvnPLDispDop13_disDate, 'DD.MM.YYYY') as \"EvnPLDispDop13_disDate\"
			from v_PersonState PS
				inner join v_EvnPLDispDop13 EPLDD on PS.Person_id = EPLDD.Person_id and EPLDD.Lpu_id = :Lpu_id
				left join YesNo IsFinish on IsFinish.YesNo_id = EPLDD.EvnPLDispDop13_IsFinish
			where (1 = 1)
				and EPLDD.Person_id = :Person_id
			order by
				-- order by
				PS.Person_SurName,
				PS.Person_FirName,
				PS.Person_SecName
				-- end order by
		";
		$result = $this->db->query($query, array('Lpu_id' => $data['Lpu_id'], 'Person_id' => $data['Person_id']));

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 *	Получение данных формы просмотра карты
	 */	
	function loadEvnPLDispDop13EditForm($data)
	{
		$accessType = '1=1';
		
		if ( $data['session']['region']['nick'] == 'ekb' ) {
			$accessType .= " and COALESCE(EPLDD13.EvnPLDispDop13_isPaid, 1) = 1";
		}
		if ( $data['session']['region']['nick'] == 'pskov' ) {
			$accessType .= "and COALESCE(EPLDD13.EvnPLDispDop13_isPaid, 1) = 1
				and not exists(
					select RD.Registry_id
					from r60.v_RegistryData RD
						inner join v_Registry R on R.Registry_id = RD.Registry_id
						inner join v_RegistryStatus RS on RS.RegistryStatus_id = R.RegistryStatus_id
					where
						RD.Evn_id = EPLDD13.EvnPLDispDop13_id
						and RS.RegistryStatus_SysNick not in ('work','paid')
					limit 1
				)
			";
		}
		
		$query = "
			SELECT
				case when {$accessType} then 'edit' else 'view' end as \"accessType\",
				EPLDD13.EvnPLDispDop13_id as \"EvnPLDispDop13_id\",
				COALESCE(EPLDD13.EvnPLDispDop13_IsPaid, 1) as \"EvnPLDispDop13_IsPaid\",
				COALESCE(EPLDD13.EvnPLDispDop13_IsNewOrder, 1) as \"EvnPLDispDop13_IsNewOrder\",
				COALESCE(EPLDD13.EvnPLDispDop13_IndexRep, 0) as \"EvnPLDispDop13_IndexRep\",
				COALESCE(EPLDD13.EvnPLDispDop13_IndexRepInReg, 1) as \"EvnPLDispDop13_IndexRepInReg\",
				EPLDD13.EvnPLDispDop13_fid as \"EvnPLDispDop13_fid\",
				EPLDD13.Person_id as \"Person_id\",
				EPLDD13.PersonEvn_id as \"PersonEvn_id\",
				COALESCE(EPLDD13.DispClass_id, 1) as \"DispClass_id\",
				EPLDD13.PayType_id as \"PayType_id\",
				EPLDD13.EvnPLDispDop13_pid as \"EvnPLDispDop13_pid\",
				to_char(EPLDD13.EvnPLDispDop13_setDate, 'DD.MM.YYYY') as \"EvnPLDispDop13_setDate\",
				to_char(EPLDD13.EvnPLDispDop13_disDate, 'DD.MM.YYYY') as \"EvnPLDispDop13_disDate\",
				to_char(EPLDD13.EvnPLDispDop13_consDT, 'DD.MM.YYYY') as \"EvnPLDispDop13_consDate\",
				EPLDD13.Server_id as \"Server_id\",
				case when EPLDD13.EvnPLDispDop13_IsMobile = 2 then 1 else 0 end as \"EvnPLDispDop13_IsMobile\",
				case when EPLDD13.EvnPLDispDop13_IsOutLpu = 2 then 1 else 0 end as \"EvnPLDispDop13_IsOutLpu\",
				EPLDD13.Lpu_mid as \"Lpu_mid\",
				EPLDD13.EvnPLDispDop13_IsStenocard as \"EvnPLDispDop13_IsStenocard\",
				EPLDD13.EvnPLDispDop13_IsShortCons as \"EvnPLDispDop13_IsShortCons\",
				EPLDD13.EvnPLDispDop13_IsBrain as \"EvnPLDispDop13_IsBrain\",
				EPLDD13.EvnPLDispDop13_IsDoubleScan as \"EvnPLDispDop13_IsDoubleScan\",
				EPLDD13.EvnPLDispDop13_IsTub as \"EvnPLDispDop13_IsTub\",
				EPLDD13.EvnPLDispDop13_IsTIA as \"EvnPLDispDop13_IsTIA\",
				EPLDD13.EvnPLDispDop13_IsRespiratory as \"EvnPLDispDop13_IsRespiratory\",
				EPLDD13.EvnPLDispDop13_IsLungs as \"EvnPLDispDop13_IsLungs\",
				EPLDD13.EvnPLDispDop13_IsTopGastro as \"EvnPLDispDop13_IsTopGastro\",
				EPLDD13.EvnPLDispDop13_IsBotGastro as \"EvnPLDispDop13_IsBotGastro\",
				EPLDD13.EvnPLDispDop13_IsSpirometry as \"EvnPLDispDop13_IsSpirometry\",
				EPLDD13.EvnPLDispDop13_IsHeartFailure as \"EvnPLDispDop13_IsHeartFailure\",
				EPLDD13.EvnPLDispDop13_IsOncology as \"EvnPLDispDop13_IsOncology\",
				EPLDD13.EvnPLDispDop13_IsEsophag as \"EvnPLDispDop13_IsEsophag\",
				EPLDD13.EvnPLDispDop13_IsSmoking as \"EvnPLDispDop13_IsSmoking\",
				EPLDD13.EvnPLDispDop13_IsRiskAlco as \"EvnPLDispDop13_IsRiskAlco\",
				EPLDD13.EvnPLDispDop13_IsAlcoDepend as \"EvnPLDispDop13_IsAlcoDepend\",
				EPLDD13.EvnPLDispDop13_IsLowActiv as \"EvnPLDispDop13_IsLowActiv\",
				EPLDD13.EvnPLDispDop13_IsIrrational as \"EvnPLDispDop13_IsIrrational\",
				EPLDD13.EvnPLDispDop13_IsUseNarko as \"EvnPLDispDop13_IsUseNarko\",
				EPLDD13.Diag_id as \"Diag_id\",
				EPLDD13.Diag_sid as \"Diag_sid\",
				EPLDD13.EvnPLDispDop13_IsDisp as \"EvnPLDispDop13_IsDisp\",
				EPLDD13.NeedDopCure_id as \"NeedDopCure_id\",
				EPLDD13.EvnPLDispDop13_IsStac as \"EvnPLDispDop13_IsStac\",
				EPLDD13.EvnPLDispDop13_IsSanator as \"EvnPLDispDop13_IsSanator\",
				EPLDD13.EvnPLDispDop13_SumRick as \"EvnPLDispDop13_SumRick\",
				EPLDD13.RiskType_id as \"RiskType_id\",
				EPLDD13.EvnPLDispDop13_IsSchool as \"EvnPLDispDop13_IsSchool\",
				EPLDD13.EvnPLDispDop13_IsProphCons as \"EvnPLDispDop13_IsProphCons\",
				EPLDD13.EvnPLDispDop13_IsHypoten as \"EvnPLDispDop13_IsHypoten\",
				EPLDD13.EvnPLDispDop13_IsLipid as \"EvnPLDispDop13_IsLipid\",
				EPLDD13.EvnPLDispDop13_IsHypoglyc as \"EvnPLDispDop13_IsHypoglyc\",
				EPLDD13.HealthKind_id as \"HealthKind_id\",
				COALESCE(EPLDD13.EvnPLDispDop13_IsEndStage, 1) as \"EvnPLDispDop13_IsEndStage\",
				EPLDD13.EvnPLDispDop13_IsTwoStage as \"EvnPLDispDop13_IsTwoStage\",
				EPLDD13.CardioRiskType_id as \"CardioRiskType_id\",
				EvnPLDispDop13Sec.EvnPLDispDop13_id as \"EvnPLDispDop13Sec_id\",
				to_char(ecp.EvnCostPrint_setDT, 'DD.MM.YYYY') as \"EvnCostPrint_setDT\",
				ecp.EvnCostPrint_Number as \"EvnCostPrint_Number\",
				ecp.EvnCostPrint_IsNoPrint as \"EvnCostPrint_IsNoPrint\",
				EPLDD13.EvnPLDispDop13_IsSuspectZNO as \"EvnPLDispDop13_IsSuspectZNO\",
				EPLDD13.Diag_spid as \"Diag_spid\",
				to_char(epldd13f.EvnPLDispDop13_consDT, 'DD.MM.YYYY') as \"EvnPLDispDop13_firstConsDate\",
				PD.PersonDisp_id as \"PersonDisp_id\"
			FROM
				v_EvnPLDispDop13 EPLDD13
				left join v_EvnCostPrint ecp on ecp.Evn_id = EPLDD13.EvnPLDispDop13_id
				left join v_EvnPLDispDop13 epldd13f on epldd13f.EvnPLDispDop13_id = EPLDD13.EvnPLDispDop13_fid 
				LEFT JOIN LATERAL (
					select
						EvnPLDispDop13_id
					from v_EvnPLDispDop13
					where
						EvnPLDispDop13_fid = EPLDD13.EvnPLDispDop13_id
					limit 1
				) EvnPLDispDop13Sec on true
				left join lateral(
					select
						PD.PersonDisp_id
					from v_PersonDisp PD
						left join v_Diag D on D.Diag_id = PD.Diag_id
					where PD.Person_id = EPLDD13.Person_id and PD.DispOutType_id is null 
						and Diag_Code not in ('Z32.1', 'Z34', 'Z34.0', 'Z34.8', 'Z343.9',
						'Z35','Z35.0','Z35.1','Z35.2','Z35.3','Z35.4','Z35.5','Z35.6','Z35.7','Z35.8','Z35.9')
					limit 1
				) PD on true
			WHERE
				(1 = 1)
				and EPLDD13.EvnPLDispDop13_id = :EvnPLDispDop13_id
			LIMIT 1
		";
		//echo getDebugSQL($query, array( 'EvnPLDispDop13_id' => $data['EvnPLDispDop13_id'])); exit();
        $result = $this->db->query($query, array( 'EvnPLDispDop13_id' => $data['EvnPLDispDop13_id']));

        if (is_object($result))
        {
            $resp = $result->result('array');
			// нужно получить значения результатов услуг из EvnUslugaRate для родительской карты
			if (!empty($resp[0]['EvnPLDispDop13_fid'])) {
				$query = "
					select 
						RT.RateType_SysNick as nick,
						RVT.RateValueType_SysNick as \"RateValueType_SysNick\",
						CASE RVT.RateValueType_SysNick
							WHEN 'int' THEN cast(R.Rate_ValueInt as varchar)
							WHEN 'float' THEN cast(cast(R.Rate_ValueFloat as decimal(16,3)) as varchar)
							WHEN 'string' THEN R.Rate_ValueStr
							WHEN 'template' THEN R.Rate_ValueStr
							WHEN 'reference' THEN cast(R.Rate_ValuesIs as varchar)
						END as value
					from v_DopDispInfoConsent DDIC
						left join v_SurveyTypeLink STL on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
						left join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
						left join v_UslugaComplex UC on UC.UslugaComplex_id = STL.UslugaComplex_id
						LEFT JOIN LATERAL (
							select
								EUDD.EvnUslugaDispDop_id
							from v_EvnUslugaDispDop EUDD
								inner join v_EvnVizitDispDop EVDD on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
							where
								EVDD.EvnVizitDispDop_pid = :EvnPLDispDop13_id and EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
								and COALESCE(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
							limit 1
						) EUDDData on true
						left join v_EvnUslugaRate eur on eur.EvnUsluga_id = EUDDData.EvnUslugaDispDop_id
						left join v_Rate r on r.Rate_id = eur.Rate_id 
						left join v_RateType rt on rt.RateType_id = r.RateType_id
						left join RateValueType rvt on rvt.RateValueType_id = rt.RateValueType_id
					where DDIC.EvnPLDisp_id = :EvnPLDispDop13_id and (DDIC.DopDispInfoConsent_IsAgree = 2 OR DDIC.DopDispInfoConsent_IsEarlier = 2) and ST.SurveyType_Code NOT IN (1,48)
						and (ST.SurveyType_Code IN (6,12) OR RT.RateType_SysNick <> 'glucose')
				";
				//echo getDebugSQL($query, array( 'EvnPLDispDop13_id' => $resp[0]['EvnPLDispDop13_fid'])); exit();
				$result = $this->db->query($query, array(
					'EvnPLDispDop13_id' => $resp[0]['EvnPLDispDop13_fid']
				));
				if (is_object($result)) {
					$results = $result->result('array');
					foreach ($results as $oneresult) {
						if ($oneresult['RateValueType_SysNick'] == 'float') {
							if ($oneresult['nick'] == 'bio_blood_kreatinin') {
								// Ничего не делаем
							} else if (in_array($oneresult['nick'], array('AsAt', 'AlAt'))) {
								// Убираем последнюю цифру в значении
								if (!empty($oneresult['value'])) {
									$oneresult['value'] = substr($oneresult['value'], 0, strlen($oneresult['value']) - 1);
								}
							} else {
								// Убираем последние 2 цифры в значении
								if (!empty($oneresult['value'])) {
									$oneresult['value'] = substr($oneresult['value'], 0, strlen($oneresult['value']) - 2);
								}
							}
						}

						$resp[0][$oneresult['nick']] = $oneresult['value'];
					}
				}
			}
			
			// нужно получить значения результатов услуг из EvnUslugaRate
			if (!empty($resp[0]['EvnPLDispDop13_id'])) {
				$query = "
					select 
						RT.RateType_SysNick as nick,
						RVT.RateValueType_SysNick as \"RateValueType_SysNick\",
						CASE RVT.RateValueType_SysNick
							WHEN 'int' THEN cast(R.Rate_ValueInt as varchar)
							WHEN 'float' THEN cast(cast(R.Rate_ValueFloat as decimal(16,3)) as varchar)
							WHEN 'string' THEN R.Rate_ValueStr
							WHEN 'template' THEN R.Rate_ValueStr
							WHEN 'reference' THEN cast(R.Rate_ValuesIs as varchar)
						END as value
					from v_DopDispInfoConsent DDIC
						left join v_SurveyTypeLink STL on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
						left join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
						left join v_UslugaComplex UC on UC.UslugaComplex_id = STL.UslugaComplex_id
						LEFT JOIN LATERAL (
							select
								EUDD.EvnUslugaDispDop_id
							from v_EvnUslugaDispDop EUDD
								inner join v_EvnVizitDispDop EVDD on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
							where
								EVDD.EvnVizitDispDop_pid = :EvnPLDispDop13_id and EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
								and COALESCE(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
							limit 1
						) EUDDData on true
						left join v_EvnUslugaRate eur on eur.EvnUsluga_id = EUDDData.EvnUslugaDispDop_id
						left join v_Rate r on r.Rate_id = eur.Rate_id 
						left join v_RateType rt on rt.RateType_id = r.RateType_id
						left join RateValueType rvt on rvt.RateValueType_id = rt.RateValueType_id
					where DDIC.EvnPLDisp_id = :EvnPLDispDop13_id and (DDIC.DopDispInfoConsent_IsAgree = 2 OR DDIC.DopDispInfoConsent_IsEarlier = 2) and ST.SurveyType_Code NOT IN (1,48)
						and (ST.SurveyType_Code IN (6,12) OR RT.RateType_SysNick <> 'glucose')
				";
				//echo getDebugSQL($query, array( 'EvnPLDispDop13_id' => $resp[0]['EvnPLDispDop13_id'])); exit();
				$result = $this->db->query($query, array(
					'EvnPLDispDop13_id' => $resp[0]['EvnPLDispDop13_id']
				));
				if (is_object($result)) {
					$results = $result->result('array');
					foreach ($results as $oneresult) {
						if ($oneresult['RateValueType_SysNick'] == 'float') {
							if ($oneresult['nick'] == 'bio_blood_kreatinin') {
								// Ничего не делаем
							} else if (in_array($oneresult['nick'], array('AsAt', 'AlAt'))) {
								// Убираем последнюю цифру в значении
								if (!empty($oneresult['value'])) {
									$oneresult['value'] = substr($oneresult['value'], 0, strlen($oneresult['value']) - 1);
								}
							} else {
								// Убираем последние 2 цифры в значении
								if (!empty($oneresult['value'])) {
									$oneresult['value'] = substr($oneresult['value'], 0, strlen($oneresult['value']) - 2);
								}
							}
						}

						$resp[0][$oneresult['nick']] = $oneresult['value'];
					}
				}
			}

			if (getRegionNick() == 'buryatiya') {
				// нужно получить значения результатов с услуги анкетирования из EvnUslugaRate
				if (!empty($resp[0]['EvnPLDispDop13_id'])) {
					$query = "
						select
							RT.RateType_SysNick as nick,
							RVT.RateValueType_SysNick as \"RateValueType_SysNick\",
							CASE RVT.RateValueType_SysNick
								WHEN 'int' THEN cast(R.Rate_ValueInt as varchar)
								WHEN 'float' THEN cast(cast(R.Rate_ValueFloat as decimal(16,3)) as varchar)
								WHEN 'string' THEN R.Rate_ValueStr
								WHEN 'template' THEN R.Rate_ValueStr
								WHEN 'reference' THEN cast(R.Rate_ValuesIs as varchar)
							END as value
						from
						 	v_EvnUslugaDispDop EUDD
							inner join v_SurveyTypeLink STL on STL.UslugaComplex_id = EUDD.UslugaComplex_id
							inner join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id and ST.SurveyType_Code = 2
							left join v_EvnUslugaRate eur on eur.EvnUsluga_id = EUDD.EvnUslugaDispDop_id
							left join v_Rate r on r.Rate_id = eur.Rate_id
							left join v_RateType rt on rt.RateType_id = r.RateType_id
							left join RateValueType rvt on rvt.RateValueType_id = rt.RateValueType_id
						where
							EUDD.EvnUslugaDispDop_pid = :EvnPLDispDop13_id
					";
					//echo getDebugSQL($query, array( 'EvnPLDispDop13_id' => $resp[0]['EvnPLDispDop13_id'])); exit();
					$result = $this->db->query($query, array(
						'EvnPLDispDop13_id' => $resp[0]['EvnPLDispDop13_id']
					));
					if (is_object($result)) {
						$results = $result->result('array');
						foreach ($results as $oneresult) {
							if ($oneresult['RateValueType_SysNick'] == 'float') {
								if ($oneresult['nick'] == 'bio_blood_kreatinin') {
									// Ничего не делаем
								} else if (in_array($oneresult['nick'], array('AsAt', 'AlAt'))) {
									// Убираем последнюю цифру в значении
									if (!empty($oneresult['value'])) {
										$oneresult['value'] = substr($oneresult['value'], 0, strlen($oneresult['value']) - 1);
									}
								} else {
									// Убираем последние 2 цифры в значении
									if (!empty($oneresult['value'])) {
										$oneresult['value'] = substr($oneresult['value'], 0, strlen($oneresult['value']) - 2);
									}
								}
							}

							$resp[0][$oneresult['nick']] = $oneresult['value'];
						}
					}
				}
			}

			// нужно получить диагноз посещения терапевта для добавления специфики
			if (!empty($data['isExt6']) && !empty($resp[0]['EvnPLDispDop13_id'])) {
				$query = "
					select
						DDIC.DopDispInfoConsent_id as \"DopDispInfoConsent_id\",
						STL.SurveyTypeLink_id as \"SurveyTypeLink_id\",
						ST.SurveyType_Name as \"SurveyType_Name\",
						ST.SurveyType_Code as \"SurveyType_Code\",
						COALESCE(ST.SurveyType_IsVizit, 1) as \"SurveyType_IsVizit\",
						EUDDData.EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\",
						to_char(EUDDData.EvnUslugaDispDop_setDate, 'DD.MM.YYYY') as \"EvnUslugaDispDop_setDate\",
						to_char(EUDDData.EvnUslugaDispDop_didDate, 'DD.MM.YYYY') as \"EvnUslugaDispDop_didDate\",
						EUDDData.Diag_id as \"Diag_id\",
						EUDDData.Diag_Code as \"Diag_Code\"
					from v_DopDispInfoConsent DDIC
						 left join v_SurveyTypeLink STL on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
						 left join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
						 LEFT JOIN LATERAL(
							select
								EUDD.EvnUslugaDispDop_id,
								EUDD.EvnUslugaDispDop_setDate,
								EUDD.EvnUslugaDispDop_setTime,
								EUDD.EvnUslugaDispDop_didDate,
								d.Diag_id,
								d.Diag_Code
							from v_EvnUslugaDispDop EUDD
								left join v_EvnVizitDispDop EVDD on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
								left join v_Diag d on d.Diag_id = EVDD.Diag_id
							where
								EVDD.EvnVizitDispDop_pid = DDIC.EvnPLDisp_id
								and EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
								and COALESCE(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
							limit 1
						) EUDDData on true
					where
						  DDIC.EvnPLDisp_id = :EvnPLDispDop13_id
						  and (DDIC.DopDispInfoConsent_IsAgree = 2 or DDIC.DopDispInfoConsent_IsEarlier = 2)
						  and ST.SurveyType_Code = 19
						  and COALESCE(STL.SurveyTypeLink_ComplexSurvey, 1) = 1
						  and (COALESCE(STL.SurveyTypeLink_IsDel, 1) = 1 or EUDDData.EvnUslugaDispDop_id is not null) -- только если сохранено согласие
					LIMIT 1
				";
				//echo getDebugSQL($query, array( 'EvnPLDispDop13_id' => $resp[0]['EvnPLDispDop13_id'])); exit();
				$result = $this->queryResult($query, array(
					'EvnPLDispDop13_id' => $resp[0]['EvnPLDispDop13_id']
				));
				if ( !empty($result) ) {
					//$results = $result->result('array');
					$resp[0]['TherapistViewData'] = $result;
				}
			}
			
			return $resp;
        }
 	    else
 	    {
            return false;
        }
	}
	
	/**
	 *	Получение полей карты
	 */	
	function getEvnPLDispDop13Fields($data)
	{
		$query = "
			SELECT
				rtrim(lp.Lpu_Name) as \"Lpu_Name\",
				rtrim(COALESCE(lp1.Lpu_Name, '')) as \"Lpu_AName\",
				rtrim(COALESCE(addr1.Address_Address, '')) as \"Lpu_AAddress\",
				rtrim(lp.Lpu_OGRN) as \"Lpu_OGRN\",
				COALESCE(pc.PersonCard_Code, '') as \"PersonCard_Code\",
				ps.Person_SurName || ' ' || ps.Person_FirName || ' ' || COALESCE(ps.Person_SecName, '') as \"Person_FIO\",
				sx.Sex_Name as \"Sex_Name\",
				COALESCE(osmo.OrgSMO_Nick, '') as \"OrgSMO_Nick\",
				COALESCE(case when pls.PolisType_id = 4 then '' else pls.Polis_Ser end, '') as \"Polis_Ser\",
				COALESCE(case when pls.PolisType_id = 4 then ps.Person_EdNum else pls.Polis_Num end, '') as \"Polis_Num\",
				COALESCE(osmo.OrgSMO_Name, '') as \"OrgSMO_Name\",
				to_char(ps.Person_BirthDay, 'DD.MM.YYYY') as \"Person_BirthDay\",
				COALESCE(addr.Address_Address, '') as \"Person_Address\",
				jborg.Org_Nick as \"Org_Nick\",
				case when EPLDD.EvnPLDispDop13_IsBud = 2 then 'Да' else 'Нет' end as \"EvnPLDispDop13_IsBud\",
				atype.AttachType_Name as \"AttachType_Name\",
				to_char(EPLDD.EvnPLDispDop13_disDate, 'DD.MM.YYYY') as \"EvnPLDispDop13_disDate\"
			FROM
				v_EvnPLDispDop13 EPLDD
				inner join v_Lpu lp on lp.Lpu_id = EPLDD.Lpu_id
				left join v_Lpu lp1 on lp1.Lpu_id = EPLDD.Lpu_aid
				left join Address addr1 on addr1.Address_id = lp1.UAddress_id
				left join v_PersonCard pc on pc.Person_id = EPLDD.Person_id and pc.LpuAttachType_id = 1
				inner join v_PersonState ps on ps.Person_id = EPLDD.Person_id
				inner join Sex sx on sx.Sex_id = ps.Sex_id
				left join Polis pls on pls.Polis_id = ps.Polis_id
				left join v_OrgSmo osmo on osmo.OrgSmo_id = pls.OrgSmo_id
				left join Address addr on addr.Address_id = ps.PAddress_id
				left join Job jb on jb.Job_id = ps.Job_id
				left join Org jborg on jborg.Org_id = jb.Org_id
				left join AttachType atype on atype.AttachType_id = EPLDD.AttachType_id
			WHERE
				(1 = 1)
				and EPLDD.EvnPLDispDop13_id = ?
				and EPLDD.Lpu_id = ?
			LIMIT 1
		";
        $result = $this->db->query($query, array($data['EvnPLDispDop13_id'], $data['Lpu_id']));

        if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение списка осмотров врача-специалиста в талоне по ДД
	 * Входящие данные: $data['EvnPLDispDop13_id']
	 * На выходе: ассоциативный массив результатов запроса
	 */
	function loadEvnVizitDispDopGrid($data)
	{
		
		$query = "
			select
				EVZDD.EvnVizitDispDop_id as \"EvnVizitDispDop_id\",
				to_char(EVZDD.EvnVizitDispDop_setDate, 'DD.MM.YYYY') as \"EvnVizitDispDop_setDate\",
				RTRIM(LS.LpuSection_Name) as \"LpuSection_Name\",
				RTRIM(MP.Person_Fio) as \"MedPersonal_Fio\",
				RTRIM(DDS.DopDispSpec_Name) as \"DopDispSpec_Name\",
				RTRIM(D.Diag_Code) as \"Diag_Code\",
				EVZDD.MedPersonal_id as \"MedPersonal_id\",
				EVZDD.DopDispSpec_id as \"DopDispSpec_id\",
				EVZDD.LpuSection_id as \"LpuSection_id\",
				EVZDD.Diag_id as \"Diag_id\",
				EVZDD.DopDispDiagType_id as \"DopDispDiagType_id\",
				EVZDD.DeseaseStage_id as \"DeseaseStage_id\",
				EVZDD.HealthKind_id as \"HealthKind_id\",
				EVZDD.EvnVizitDispDop_IsSanKur as \"EvnVizitDispDop_IsSanKur\",
				EVZDD.EvnVizitDispDop_IsOut,				
				EVZDD.DopDispAlien_id as \"DopDispAlien_id\",
				EVZDD.EvnVizitDispDop_Recommendations as \"EvnVizitDispDop_Recommendations\",
				1 as \"Record_Status\"
			from v_EvnVizitDispDop EVZDD
				left join LpuSection LS on LS.LpuSection_id = EVZDD.LpuSection_id
				left join v_MedPersonal MP on MP.MedPersonal_id = EVZDD.MedPersonal_id
				left join DopDispSpec DDS on DDS.DopDispSpec_id = EVZDD.DopDispSpec_id
				left join Diag D on D.Diag_id = EVZDD.Diag_id
			where EVZDD.EvnVizitDispDop_pid = ?
		";
		$result = $this->db->query($query, array($data['EvnPLDispDop13_id']));

        if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 * Получение данных для редактирования посещения врача-специалиста в талоне по ДД
	 * Входящие данные: $data['EvnVizitDispDop_id']
	 * На выходе: ассоциативный массив результатов запроса
	 */
	function loadEvnVizitDispDopEditForm($data)
	{
		$this->load->helper('MedStaffFactLink');
		$med_personal_list = getMedPersonalListWithLinks();
		
		$query = "
			select
				EVZDD.EvnVizitDispDop_id as \"EvnVizitDispDop_id\",
				to_char(EVZDD.EvnVizitDispDop_setDate, 'DD.MM.YYYY') as \"EvnVizitDispDop_setDate\",
				RTRIM(LS.LpuSection_Name) as \"LpuSection_Name\",
				RTRIM(MP.Person_Fio) as \"MedPersonal_Fio\",
				RTRIM(DDS.DopDispSpec_Name) as \"DopDispSpec_Name\",
				RTRIM(D.Diag_Code) as \"Diag_Code\",
				EVZDD.MedPersonal_id as \"MedPersonal_id\",
				EVZDD.DopDispSpec_id as \"DopDispSpec_id\",
				EVZDD.LpuSection_id as \"LpuSection_id\",
				EVZDD.Diag_id as \"Diag_id\",
				EVZDD.DopDispDiagType_id as \"DopDispDiagType_id\",
				EVZDD.DeseaseStage_id as \"DeseaseStage_id\",
				EVZDD.HealthKind_id as \"HealthKind_id\",
				EVZDD.EvnVizitDispDop_IsSanKur as \"EvnVizitDispDop_IsSanKur\",
				EVZDD.EvnVizitDispDop_IsOut,				
				EVZDD.DopDispAlien_id as \"DopDispAlien_id\",
				EVZDD.EvnVizitDispDop_Recommendations as \"EvnVizitDispDop_Recommendations\",
				1 as \"RecordStatus\",
				case when EVZDD.Lpu_id = :Lpu_id " . (count($med_personal_list)>0 ? "and EVZDD.MedPersonal_id in (".implode(',',$med_personal_list).")" : "") . " then 'edit' else 'view' end as \"accessType\"
			from v_EvnVizitDispDop EVZDD
				left join LpuSection LS on LS.LpuSection_id = EVZDD.LpuSection_id
				left join v_MedPersonal MP on MP.MedPersonal_id = EVZDD.MedPersonal_id and MP.Lpu_id = EVZDD.Lpu_id
				left join DopDispSpec DDS on DDS.DopDispSpec_id = EVZDD.DopDispSpec_id
				left join Diag D on D.Diag_id = EVZDD.Diag_id
			where EVZDD.EvnVizitDispDop_id = :EvnVizitDispDop_id
			limit 1
		";
		$result = $this->db->query($query, array('EvnVizitDispDop_id' => $data['EvnVizitDispDop_id'], 'Lpu_id' => $data['Lpu_id']));

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 * Получение списка осмотров врача-специалиста в талоне по ДД
	 * Входящие данные: $data['EvnPLDispDop13_id']
	 * На выходе: ассоциативный массив результатов запроса
	 */
	function loadEvnVizitDispDopData($data)
	{
		
		$query = "
			select
				EVZDD.EvnVizitDispDop_id as \"EvnVizitDispDop_id\",
				to_char(EVZDD.EvnVizitDispDop_setDate, 'DD.MM.YYYY') as \"EvnVizitDispDop_setDate\",
				RTRIM(MP.Person_Fio) as \"MedPersonal_Fio\",
				RTRIM(COALESCE(MP.MedPersonal_TabCode, '')) as \"MedPersonal_TabCode\",
				RTRIM(DDS.DopDispSpec_Name) as \"DopDispSpec_Name\",
				RTRIM(D.Diag_Code) as \"Diag_Code\",
				EVZDD.MedPersonal_id as \"MedPersonal_id\",
				EVZDD.DopDispSpec_id as \"DopDispSpec_id\",
				EVZDD.LpuSection_id as \"LpuSection_id\",
				EVZDD.Diag_id as \"Diag_id\",
				EVZDD.DopDispDiagType_id as \"DopDispDiagType_id\",
				EVZDD.DeseaseStage_id as \"DeseaseStage_id\",
				EVZDD.HealthKind_id as \"HealthKind_id\",
				EVZDD.EvnVizitDispDop_IsSanKur as \"EvnVizitDispDop_IsSanKur\",
				EVZDD.EvnVizitDispDop_IsOut,				
				EVZDD.DopDispAlien_id as \"DopDispAlien_id\",
				EVZDD.EvnVizitDispDop_Recommendations as \"EvnVizitDispDop_Recommendations\",
				1 as \"Record_Status\"
			from v_EvnVizitDispDop EVZDD
				left join LpuSection LS on LS.LpuSection_id = EVZDD.LpuSection_id
				left join v_MedPersonal MP on MP.MedPersonal_id = EVZDD.MedPersonal_id
				left join DopDispSpec DDS on DDS.DopDispSpec_id = EVZDD.DopDispSpec_id
				left join Diag D on D.Diag_id = EVZDD.Diag_id
			where EVZDD.EvnVizitDispDop_pid = ?
		";
		$result = $this->db->query($query, array($data['EvnPLDispDop13_id']));

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *  Получение списка исследований невозможных для переноса в профосмотр
	 *  Входящие данные: EvnPLDispDop13_id
	 */
	function loadEvnUslugaDispDopTransferFailGrid($data) {
		$query = "
			select
				EUDD.EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\",
				ST.SurveyType_Name as \"SurveyType_Name\",
				case WHEN DDIC.DopDispInfoConsent_IsEarlier = 2 then 'true' else 'false' end as \"DopDispInfoConsent_IsEarlier\",
				to_char(EUDD.EvnUslugaDispDop_didDate, 'DD.MM.YYYY') as \"EvnUslugaDispDop_didDate\",
				L.Lpu_Nick as \"Lpu_Nick\",
				MP.Person_Fio as \"MedPersonal_Fio\"
			from
				v_EvnUslugaDispDop EUDD
				inner join v_EvnVizitDispDop EVDD on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
				inner join v_DopDispInfoConsent DDIC on DDIC.DopDispInfoConsent_id = EVDD.DopDispInfoConsent_id
				inner join v_SurveyTypeLink STL on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
				inner join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
				left join v_Lpu L on L.Lpu_id = COALESCE(EUDD.Lpu_uid, EUDD.Lpu_id)
				left join v_MedStaffFact MP on MP.MedStaffFact_id = EUDD.MedStaffFact_id
			where
				EVDD.EvnVizitDispDop_pid = :EvnPLDispDop13_id
				and ST.SurveyType_Code NOT IN (2,3,4,5,6,7,8,9,14,16,17,19,21,31,96,97)
		";
		//echo getDebugSql($query, array('EvnPLDispDop13_id' => $data['EvnPLDispDop13_id']));
		$result = $this->db->query($query, array('EvnPLDispDop13_id' => $data['EvnPLDispDop13_id']));

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *  Получение списка исследований возможных для переноса в профосмотр
	 *  Входящие данные: EvnPLDispDop13_id
	 */
	function loadEvnUslugaDispDopTransferSuccessGrid($data) {
		$query = "
			select
				EUDD.EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\",
				ST.SurveyType_Name as \"SurveyType_Name\",
				case WHEN DDIC.DopDispInfoConsent_IsEarlier = 2 then 'true' else 'false' end as \"DopDispInfoConsent_IsEarlier\",
				to_char(EUDD.EvnUslugaDispDop_didDate, 'DD.MM.YYYY') as \"EvnUslugaDispDop_didDate\",
				L.Lpu_Nick as \"Lpu_Nick\",
				MP.Person_Fio as \"MedPersonal_Fio\"
			from
				v_EvnUslugaDispDop EUDD
				inner join v_EvnVizitDispDop EVDD on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
				inner join v_DopDispInfoConsent DDIC on DDIC.DopDispInfoConsent_id = EVDD.DopDispInfoConsent_id
				inner join v_SurveyTypeLink STL on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
				inner join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
				left join v_Lpu L on L.Lpu_id = COALESCE(EUDD.Lpu_uid, EUDD.Lpu_id)
				left join v_MedStaffFact MP on MP.MedStaffFact_id = EUDD.MedStaffFact_id
			where
				EVDD.EvnVizitDispDop_pid = :EvnPLDispDop13_id
				and ST.SurveyType_Code IN (3,4,5,6,7,8,9,14,16,17,19,21,31,96,97)

			union all

			select
				EUDD.EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\",
				ST.SurveyType_Name as \"SurveyType_Name\",
				case WHEN DDIC.DopDispInfoConsent_IsEarlier = 2 then 'true' else 'false' end as \"DopDispInfoConsent_IsEarlier\",
				to_char(EUDD.EvnUslugaDispDop_didDate, 'DD.MM.YYYY') as \"EvnUslugaDispDop_didDate\",
				L.Lpu_Nick as \"Lpu_Nick\",
				MP.Person_Fio as \"MedPersonal_Fio\"
			from
				v_EvnUslugaDispDop EUDD
				inner join v_DopDispInfoConsent DDIC on DDIC.EvnPLDisp_id = EUDD.EvnUslugaDispDop_pid
				inner join v_SurveyTypeLink STL on STL.SurveyType_id = (2) and STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
				inner join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
				left join v_Lpu L on L.Lpu_id = COALESCE(EUDD.Lpu_uid, EUDD.Lpu_id)
				left join v_MedStaffFact MP on MP.MedStaffFact_id = EUDD.MedStaffFact_id
			where
				EUDD.EvnUslugaDispDop_pid = :EvnPLDispDop13_id
		";
		//echo getDebugSql($query, array('EvnPLDispDop13_id' => $data['EvnPLDispDop13_id']));
		$result = $this->db->query($query, array('EvnPLDispDop13_id' => $data['EvnPLDispDop13_id']));

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 * Получение списка исследований в талоне по ДД
	 *
	 * Вызов:
	 *   Диспансеризация взрослого населения – 1 этап: (Просмотр|Редактирование) > Маршрутная карта.
	 *
	 * Входящие данные: $data['EvnPLDispDop13_id']
	 * На выходе: ассоциативный массив результатов запроса
	 */
	function loadEvnUslugaDispDopGrid($data)
	{
		$queryunion = "";
		if (!empty($data['isDopUsl'])) {
			// считаем услугу цитологическое исследование
			$queryunion .= "
				union

				(select
					DDIC.DopDispInfoConsent_id as \"DopDispInfoConsent_id\",
					STL.SurveyTypeLink_id as \"SurveyTypeLink_id\",
					ST.SurveyType_Name as \"SurveyType_Name\",
					ST.SurveyType_Code as \"SurveyType_Code\",
					COALESCE(ST.SurveyType_IsVizit, 1) as \"SurveyType_IsVizit\",
					EUDDData.UslugaComplex_Code as \"UslugaComplex_Code\",
					EUDDData.EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\",
					EUDDData.EvnUslugaDispDop_ExamPlace as \"EvnUslugaDispDop_ExamPlace\",
					to_char(EUDDData.EvnUslugaDispDop_setDate + EUDDData.EvnUslugaDispDop_setTime, 'DD Mon YYYY HH24:MI:SS') as \"EvnUslugaDispDop_setDate\",
					to_char(EUDDData.EvnUslugaDispDop_didDate, 'DD.MM.YYYY') as \"EvnUslugaDispDop_didDate\",
					null as \"Diag_Code\",
					STL.SurveyTypeLink_IsUslPack as \"SurveyTypeLink_IsUslPack\",
					COALESCE(DDIC.DopDispInfoConsent_IsEarlier, 1) as \"DopDispInfoConsent_IsEarlier\",
					case when el.Evn_lid is not null then 'true' else 'false' end as \"EvnUslugaDispDop_WithDirection\",
					ep.evn_id as \"EvnPrescr_id\",
					COALESCE(ep.EvnPrescr_pid, ed.EvnDirection_pid) as \"EvnPrescr_pid\",
					ep.PrescriptionType_id as \"PrescriptionType_id\",
					ST.OrpDispSpec_id as \"OrpDispSpec_id\",
					COALESCE(ep.EvnDirection_id, ed.EvnDirection_id) as \"EvnDirection_id\"
				from v_DopDispInfoConsent DDIC
					inner join v_SurveyTypeLink STL on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
					inner join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
					LEFT JOIN LATERAL (
						Select * from v_EvnDirection ed where ed.DopDispInfoConsent_id = ddic.DopDispInfoConsent_id and ed.EvnStatus_id not in (12,13) limit 1
					) ed on true
					LEFT JOIN LATERAL (
						select
							ep.evn_id,
							Evn.Evn_pid as EvnPrescr_pid,
							ep.PrescriptionType_id,
							ed2.EvnDirection_id
						from
							EvnPrescr ep
							inner join Evn on Evn.Evn_id = ep.evn_id and Evn.Evn_deleted = 1
							LEFT JOIN LATERAL (
								Select ed2.EvnDirection_id from v_EvnPrescrDirection epd 
								inner join v_EvnDirection_all ed2 on ed2.EvnDirection_id = epd.EvnDirection_id and ed2.EvnStatus_id not in (12,13)
								where epd.EvnPrescr_id = ep.evn_id
								limit 1
							) ed2 on true
						where
							ep.DopDispInfoConsent_id = ddic.DopDispInfoConsent_id
						limit 1
					) ep on true
					LEFT JOIN LATERAL (
						select
							EUDD.EvnUslugaDispDop_id,
							EUDD.EvnUslugaDispDop_setDate,
							EUDD.EvnUslugaDispDop_setTime,
							EUDD.EvnUslugaDispDop_didDate,
							EUDD.EvnUslugaDispDop_ExamPlace,
							UC.UslugaComplex_Code
						from
							v_EvnUslugaDispDop EUDD
							left join v_UslugaComplex UC on UC.UslugaComplex_id = EUDD.UslugaComplex_id
						where
							EUDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
							and COALESCE(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
						limit 1
					) EUDDData on true
					left join v_EvnLink el on el.Evn_id = EUDDData.EvnUslugaDispDop_id
				where DDIC.EvnPLDisp_id = :EvnPLDispDop13_id
					and STL.SurveyTypeLink_ComplexSurvey = 2 and EUDDData.EvnUslugaDispDop_id is not null
				limit 1)
			";
		}

		$query = "
			select
				DDIC.DopDispInfoConsent_id as \"DopDispInfoConsent_id\",
				STL.SurveyTypeLink_id as \"SurveyTypeLink_id\",
				ST.SurveyType_Name as \"SurveyType_Name\",
				ST.SurveyType_Code as \"SurveyType_Code\",
				COALESCE(ST.SurveyType_IsVizit, 1) as \"SurveyType_IsVizit\",
				EUDDData.UslugaComplex_Code as \"UslugaComplex_Code\",
				EUDDData.EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\",
				EUDDData.EvnUslugaDispDop_ExamPlace as \"EvnUslugaDispDop_ExamPlace\",
				to_char(EUDDData.EvnUslugaDispDop_setDate + EUDDData.EvnUslugaDispDop_setTime, 'DD Mon YYYY HH24:MI:SS') as \"EvnUslugaDispDop_setDate\",
				to_char(EUDDData.EvnUslugaDispDop_didDate, 'DD.MM.YYYY') as \"EvnUslugaDispDop_didDate\",
				null as \"Diag_Code\",
				STL.SurveyTypeLink_IsUslPack as \"SurveyTypeLink_IsUslPack\",
				COALESCE(DDIC.DopDispInfoConsent_IsEarlier, 1) as \"DopDispInfoConsent_IsEarlier\",
				case when el.Evn_lid is not null then 'true' else 'false' end as \"EvnUslugaDispDop_WithDirection\",
				ep.EvnPrescr_id as \"EvnPrescr_id\",
				COALESCE(ep.EvnPrescr_pid, ed.EvnDirection_pid) as \"EvnPrescr_pid\",
				ep.PrescriptionType_id as \"PrescriptionType_id\",
				ST.OrpDispSpec_id as \"OrpDispSpec_id\",
				COALESCE(ep.EvnDirection_id, ed.EvnDirection_id) as \"EvnDirection_id\"
			from v_DopDispInfoConsent DDIC
				left join v_SurveyTypeLink STL on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
				left join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
				LEFT JOIN LATERAL (
					Select * from v_EvnDirection ed where ed.DopDispInfoConsent_id = ddic.DopDispInfoConsent_id and ed.EvnStatus_id not in (12,13) limit 1
				) ed on true
				LEFT JOIN LATERAL (
					select
						ep.Evn_id as EvnPrescr_id,
						Evn.Evn_pid as EvnPrescr_pid,
						ep.PrescriptionType_id,
						ed2.EvnDirection_id
					from
						EvnPrescr ep
						inner join Evn on Evn.Evn_id = ep.Evn_id and Evn.Evn_deleted = 1
						LEFT JOIN LATERAL (
							Select ed2.EvnDirection_id from v_EvnPrescrDirection epd 
							inner join v_EvnDirection_all ed2 on ed2.EvnDirection_id = epd.EvnDirection_id and ed2.EvnStatus_id not in (12,13)
							where epd.EvnPrescr_id = ep.Evn_id
							limit 1
						) ed2 on true
					where
						ep.DopDispInfoConsent_id = ddic.DopDispInfoConsent_id
					limit 1
				) ep on true
				LEFT JOIN LATERAL (
					select
						EUDD.EvnUslugaDispDop_id,
						EUDD.EvnUslugaDispDop_setDate,
						EUDD.EvnUslugaDispDop_setTime,
						EUDD.EvnUslugaDispDop_didDate,
						EUDD.EvnUslugaDispDop_ExamPlace,
						UC.UslugaComplex_Code
					from
						v_EvnUslugaDispDop EUDD
						left join v_UslugaComplex UC on UC.UslugaComplex_id = EUDD.UslugaComplex_id
					where
						EUDD.EvnUslugaDispDop_pid = :EvnPLDispDop13_id
						and COALESCE(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
					limit 1
				) EUDDData on true
				left join v_EvnLink el on el.Evn_id = EUDDData.EvnUslugaDispDop_id
			where DDIC.EvnPLDisp_id = :EvnPLDispDop13_id
				and ST.SurveyType_Code = 2
				and (DDIC.DopDispInfoConsent_IsAgree = 2 OR DDIC.DopDispInfoConsent_IsEarlier = 2)
				and (COALESCE(STL.SurveyTypeLink_IsDel, 1) = 1 or EUDDData.EvnUslugaDispDop_id is not null ) -- только если сохранено согласие

			union

			select
				DDIC.DopDispInfoConsent_id as \"DopDispInfoConsent_id\",
				STL.SurveyTypeLink_id as \"SurveyTypeLink_id\",
				ST.SurveyType_Name as \"SurveyType_Name\",
				ST.SurveyType_Code as \"SurveyType_Code\",
				COALESCE(ST.SurveyType_IsVizit, 1) as \"SurveyType_IsVizit\",
				EUDDData.UslugaComplex_Code as \"UslugaComplex_Code\",
				EUDDData.EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\",
				EUDDData.EvnUslugaDispDop_ExamPlace as \"EvnUslugaDispDop_ExamPlace\",
				to_char(EUDDData.EvnUslugaDispDop_setDate + EUDDData.EvnUslugaDispDop_setTime, 'DD Mon YYYY HH24:MI:SS') as \"EvnUslugaDispDop_setDate\",
				to_char(EUDDData.EvnUslugaDispDop_didDate, 'DD.MM.YYYY') as \"EvnUslugaDispDop_didDate\",
				EUDDData.Diag_Code as \"Diag_Code\",
				STL.SurveyTypeLink_IsUslPack as \"SurveyTypeLink_IsUslPack\",
				COALESCE(DDIC.DopDispInfoConsent_IsEarlier, 1) as \"DopDispInfoConsent_IsEarlier\",
				case when el.Evn_lid is not null then 'true' else 'false' end as \"EvnUslugaDispDop_WithDirection\",
				ep.EvnPrescr_id as \"EvnPrescr_id\",
				COALESCE(ep.EvnPrescr_pid, ed.EvnDirection_pid) as \"EvnPrescr_pid\",
				ep.PrescriptionType_id as \"PrescriptionType_id\",
				ST.OrpDispSpec_id as \"OrpDispSpec_id\",
				COALESCE(ep.EvnDirection_id, ed.EvnDirection_id) as \"EvnDirection_id\"
			from v_DopDispInfoConsent DDIC
				left join v_SurveyTypeLink STL on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
				left join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
				LEFT JOIN LATERAL (
					Select * from v_EvnDirection ed where ed.DopDispInfoConsent_id = ddic.DopDispInfoConsent_id and ed.EvnStatus_id not in (12,13) limit 1
				) ed on true
				LEFT JOIN LATERAL (
					select
						ep.Evn_id as EvnPrescr_id,
						Evn.Evn_pid as EvnPrescr_pid,
						ep.PrescriptionType_id,
						ed2.EvnDirection_id
					from
						EvnPrescr ep 
						inner join Evn on Evn.Evn_id = ep.Evn_id and Evn.Evn_deleted = 1
						LEFT JOIN LATERAL (
							Select ed2.EvnDirection_id from v_EvnPrescrDirection epd 
							inner join v_EvnDirection_all ed2 on ed2.EvnDirection_id = epd.EvnDirection_id and ed2.EvnStatus_id not in (12,13)
							where epd.EvnPrescr_id = ep.Evn_id
							limit 1
						) ed2 on true
					where
						ep.DopDispInfoConsent_id = ddic.DopDispInfoConsent_id
					limit 1
				) ep on true
				LEFT JOIN LATERAL (
					select
						EUDD.EvnUslugaDispDop_id,
						EUDD.EvnUslugaDispDop_setDate,
						EUDD.EvnUslugaDispDop_setTime,
						EUDD.EvnUslugaDispDop_didDate,
						EUDD.EvnUslugaDispDop_ExamPlace,
						d.Diag_Code,
						UC.UslugaComplex_Code
					from v_EvnUslugaDispDop EUDD
						left join v_EvnVizitDispDop EVDD on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
						LEFT JOIN v_Diag d ON d.Diag_id = EVDD.Diag_id
						left join v_UslugaComplex UC on UC.UslugaComplex_id = EUDD.UslugaComplex_id
					where
						EVDD.EvnVizitDispDop_pid = :EvnPLDispDop13_id
						and EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
						and COALESCE(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
					limit 1
				) EUDDData on true
				left join v_EvnLink el on el.Evn_id = EUDDData.EvnUslugaDispDop_id
			where DDIC.EvnPLDisp_id = :EvnPLDispDop13_id
				and (DDIC.DopDispInfoConsent_IsAgree = 2 OR DDIC.DopDispInfoConsent_IsEarlier = 2)
				and ST.SurveyType_Code NOT IN (1, 2, 48)
				and COALESCE(STL.SurveyTypeLink_ComplexSurvey, 1) = 1
				and (COALESCE(STL.SurveyTypeLink_IsDel, 1) = 1 or EUDDData.EvnUslugaDispDop_id is not null ) -- только если сохранено согласие

			{$queryunion}
		";
		//echo getDebugSql($query, array('EvnPLDispDop13_id' => $data['EvnPLDispDop13_id']));
		$result = $this->db->query($query, array('EvnPLDispDop13_id' => $data['EvnPLDispDop13_id']));

		if (is_object($result)) {
			$response = $result->result('array');
			return $response;
		} else {
			return false;
		}
	}
	
	/**
	 *	Получение списка карт для поточного ввода
	 */	
	function loadEvnPLDispDop13StreamList($data)
	{
		$filter = '';
		$queryParams = array();

       	$filter .= " and EPL.pmUser_insID = :pmUser_id ";
		$queryParams['pmUser_id'] = $data['pmUser_id'];

		if ( preg_match('/^\d{2}:\d{2}:\d{2}$/', $data['begTime']) )
		{
        	$filter .= " and EPL.EvnPL_insDT >= :date_time";
			$queryParams['date_time'] = $data['begDate'] . " " . $data['begTime'];
		}

        if ( isset($data['Lpu_id']) )
        {
        	$filter .= " and EPL.Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
        }

        $query = "
        	SELECT DISTINCT
				EPL.EvnPL_id as \"EvnPL_id\",
				EPL.Person_id as \"Person_id\",
				EPL.Server_id as \"Server_id\",
				EPL.PersonEvn_id \"as PersonEvn_id\",
				RTRIM(EPL.EvnPL_NumCard) as \"EvnPL_NumCard\",
				RTRIM(PS.Person_Surname) as \"Person_Surname\",
				RTRIM(PS.Person_Firname) as \"Person_Firname\",
				RTRIM(PS.Person_Secname) as \"Person_Secname\",
				to_char(PS.Person_Birthday, 'DD.MM.YYYY') as \"Person_Birthday\",
				to_char(EPL.EvnPL_setDate, 'DD.MM.YYYY') as \"EvnPL_setDate\",
				to_char(EPL.EvnPL_disDate, 'DD.MM.YYYY') as \"EvnPL_disDate\",
				EPL.EvnPL_VizitCount as \"EvnPL_VizitCount\",
				IsFinish.YesNo_Name as \"EvnPL_IsFinish\"
			FROM v_EvnPL EPL
				inner join v_PersonState PS on PS.Person_id = EPL.Person_id
				left join YesNo IsFinish on IsFinish.YesNo_id = EPL.EvnPL_IsFinish
			WHERE (1 = 1)
				" . $filter . "
			ORDER BY EPL.EvnPL_id desc
			LIMIT 100
    	";
        $result = $this->db->query($query, $queryParams);

        if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *	Получение списка посещений
	 */	
	function loadEvnVizitPLDispDopGrid($data)
	{
		$query = "
			select
				EVPL.EvnVizitPL_id as \"EvnVizitPL_id\",
				EVPL.LpuSection_id as \"LpuSection_id\",
				EVPL.MedPersonal_id as \"MedPersonal_id\",
				EVPL.MedPersonal_sid as \"MedPersonal_sid\",
				EVPL.PayType_id as \"PayType_id\",
				EVPL.ProfGoal_id as \"ProfGoal_id\",
				EVPL.ServiceType_id as \"ServiceType_id\",
				EVPL.VizitType_id as \"VizitType_id\",
				EVPL.EvnVizitPL_Time as \"EvnVizitPL_Time\",
				to_char(EVPL.EvnVizitPL_setDate, 'DD.MM.YYYY') as \"EvnVizitPL_setDate\",
				EVPL.EvnVizitPL_setTime as \"EvnVizitPL_setTime\",
				RTrim(LS.LpuSection_Name) as \"LpuSection_Name\",
				RTrim(MP.Person_Fio) as \"MedPersonal_Fio\",
				RTrim(PT.PayType_Name) as \"PayType_Name\",
				RTrim(ST.ServiceType_Name) as \"ServiceType_Name\",
				RTrim(VT.VizitType_Name) as \"VizitType_Name\",
				1 as \"Record_Status\"
			from v_EvnVizitPL EVPL
				left join LpuSection LS on LS.LpuSection_id = EVPL.LpuSection_id
				left join v_MedPersonal MP on MP.MedPersonal_id = EVPL.MedPersonal_id
				left join PayType PT on PT.PayType_id = EVPL.PayType_id
				left join ServiceType ST on ST.ServiceType_id = EVPL.ServiceType_id
				left join VizitType VT on VT.VizitType_id = EVPL.VizitType_id
			where EVPL.EvnVizitPL_pid = ?
		";
		$result = $this->db->query($query, array($data['EvnPL_id']));

        if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение 85% из объёма
	 * @param $data
	 * @return null
	 */
	function get2018Dvn185Volume($data) {
		$response = array('Error_Msg' => '');

		$resp_ps = $this->queryResult("
			select
				person_id,
				COALESCE(Sex_id, 3) as sex_id,
				dbo.Age2(Person_BirthDay, :EvnPLDispDop13_YearEndDate) as age
			from v_PersonState ps
			where ps.Person_id = :Person_id
			limit 1
		", array(
			'Person_id' => $data['Person_id'],
			'EvnPLDispDop13_YearEndDate' => mb_substr($data['onDate'], 0, 4) . '-12-31'
		));

		if (empty($resp_ps[0]['person_id'])) {
			throw new Exception('Ошибка получения данных по пациенту');
		}

		$resp_ps[0] = $this->getAgeModification(array(
			'onDate' => $data['onDate']
		), $resp_ps[0]);

		$queryParams = array(
			'Person_id' => $data['Person_id'],
			'onDate' => $data['onDate'],
			'age' => $resp_ps[0]['age'],
			'sex_id' => $resp_ps[0]['sex_id']
		);
		$AttributeVision_TablePKey = $this->getFirstResultFromQuery("select VolumeType_id from v_VolumeType where VolumeType_Code = '2018_ДВН1_85' limit 1");
		$AttributeVision_TablePKey = $AttributeVision_TablePKey == FALSE ? NULL : $AttributeVision_TablePKey;
		$queryParams['AttributeVision_TablePKey'] = $AttributeVision_TablePKey;

		$resp = $this->queryResult("
			SELECT
				av.AttributeValue_id as \"AttributeValue_id\",
				av.AttributeValue_ValueInt as \"AttributeValue_ValueInt\"
			FROM
				v_AttributeVision avis
				inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
				inner join v_Attribute a on a.Attribute_id = av.Attribute_id
				INNER JOIN LATERAL (
					select
						av2.AttributeValue_ValueIdent
					from
						v_AttributeValue av2
						inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
					where
						av2.AttributeValue_rid = av.AttributeValue_id
						and a2.Attribute_SysNick = 'Age'
						and av2.AttributeValue_ValueInt = :age
					limit 1
				) AGE on true
				INNER JOIN LATERAL (
					select
						av2.AttributeValue_ValueIdent
					from
						v_AttributeValue av2
						inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
					where
						av2.AttributeValue_rid = av.AttributeValue_id
						and a2.Attribute_TableName = 'dbo.Sex'
						and COALESCE(av2.AttributeValue_ValueIdent, :sex_id) = :sex_id
					limit 1
				) SEX on true
			WHERE
				avis.AttributeVision_TableName = 'dbo.VolumeType'
				and avis.AttributeVision_TablePKey = :AttributeVision_TablePKey
				and avis.AttributeVision_IsKeyValue = 2
				and COALESCE(av.AttributeValue_begDate, :onDate) <= :onDate
				and COALESCE(av.AttributeValue_endDate, :onDate) >= :onDate
		", $queryParams);

		if (isset($resp[0]['AttributeValue_ValueInt'])) {
			$response['count85Percent'] = $resp[0]['AttributeValue_ValueInt'];
		}

		return $response;
	}

	/**
	 *	Проверка того, что человек есть в регистре по ДД и у него заведены все необходимые данные
	 */
	function checkPersonData($data)
	{
		$errors = array();
		
		$query = "
			select
				PS.PersonEvn_id as \"PersonEvn_id\",
				PS.Server_id as \"Server_id\",
				to_char(PS.Person_DeadDT, 'DD.MM.YYYY') as \"Person_deadDT\",
				uaddr.Address_Zip as \"UAddress_Zip\",
				baddr.Address_Address as \"BAddress_Address\"
			from v_PersonState ps
				left join PersonBirthPlace pbp on ps.Person_id = pbp.Person_id
				left join v_Address baddr on baddr.Address_id = pbp.Address_id
				left join v_Address uaddr on ps.UAddress_id = uaddr.Address_id
			where ps.Person_id = ?
		";
		$result = $this->db->query($query, array($data['Person_id']));

		if ( !$result ) {
			return array('Error_Msg' => 'Ошибка проверки данных пациента');
		}

		$response = $result->result('array');

		if ( !is_array($response) || count($response) == 0 ) {
			return array('Error_Msg' => 'Ошибка проверки данных пациента');
		}
		
		if ( !empty($response[0]['Person_deadDT']) && !empty($data['EvnPLDispDop13_consDate']) && strtotime($response[0]['Person_deadDT']) < strtotime($data['EvnPLDispDop13_consDate']) ) {
			$errors[] = 'У пациента проставлена дата смерти, прохождение диспансеризации невозможно';
		}
		
		// только для Уфы (refs #21850)
		if ( $data['session']['region']['nick'] == 'ufa' ) {
			if ( empty($response[0]['BAddress_Address']) ) {
				$errors[] = 'У пациента не указано место рождения';
			}

			if (empty($response[0]['UAddress_Zip'])) {
				$errors[] = 'У пациента не указан индекс в адресе регистрации';
			}
		}
		
		if (count($errors)>0) { // есть ошибки в заведении
			$errstr = implode("<br/>", $errors);
			return array('Error_Msg' => $errstr);
		}

		return $response[0];
	}

	/**
	 * Проверка атрибута у отделения
	 */
	function checkAttributeforLpuSection($data)
	{
		$query = "
			select 
				EVZDD.EvnVizitDispDop_didDT as \"EvnVizitDispDop_didDT\",
				ASVal.AttributeSign_id as \"AttributeSign_id\",
				ASVal.AttributeSignValue_begDate as \"AttributeSignValue_begDate\",
				ASVal.AttributeSignValue_endDate as \"AttributeSignValue_endDate\"
			from
				v_EvnVizitDispDop EVZDD 
				left join LpuSection LS on LS.LpuSection_id = EVZDD.LpuSection_id
				left join LATERAL (
					select 
						AS1.AttributeSign_id as AttributeSign_id,
						ASV.AttributeSignValue_begDate as AttributeSignValue_begDate,
						ASV.AttributeSignValue_endDate as AttributeSignValue_endDate
					from
						v_AttributeSignValue ASV 
						inner join v_AttributeSign AS1 on AS1.AttributeSign_id = ASV.AttributeSign_id
					where
						AS1.AttributeSign_TableName = 'dbo.LpuSection'
						and ASV.AttributeSignValue_TablePKey = EVZDD.LpuSection_id
						and AS1.AttributeSign_Name = 'Передвижные подразделения'
				) ASVal on true
			where
				EVZDD.EvnVizitDispDop_pid = :EvnPLDispDop13_id
		";

		$result = $this->db->query($query, array('EvnPLDispDop13_id'=>$data['EvnPLDispDop13_id']));
		$response = $result->result('array');

		if ( is_array($response) && count($response) > 0 ) {

			$col_lpusection=0;
			foreach($response as $res) {
				if (
					!empty($res['AttributeSign_id'])
					&& (is_null($res['AttributeSignValue_begDate']) || (isset($res['AttributeSignValue_begDate']) && $res['AttributeSignValue_begDate']<=$res['EvnVizitDispDop_didDT']))
					&& (is_null($res['AttributeSignValue_endDate']) || (isset($res['AttributeSignValue_endDate']) && $res['AttributeSignValue_endDate']>=$res['EvnVizitDispDop_didDT']))
				){
					$col_lpusection++;
				}
			}

			if(count($response)==$col_lpusection){
				return 'Все осмотры и исследования карты обслужены мобильной бригадой. Установить флаг "Случай обслужен мобильной бригадой" для всей карты?';
			}
		}
		
		return array( "Ok");
	}
	
	/**
	 *	Получение минимальной, максимальной дат
	 */	
	function getEvnUslugaDispDopMinMaxDates($data)
	{
		$filter = " and (DDIC.DopDispInfoConsent_IsAgree = 2 OR DDIC.DopDispInfoConsent_IsEarlier = 2)";
		if (getRegionNick() == 'pskov' && $data['DispClass_id'] == 1) {
			// Для ДВН1 так же не учитываем услуги проведенные ранее.
			$filter = " and DDIC.DopDispInfoConsent_IsAgree = 2";
		}

		$params = array(
			'EvnPLDispDop13_id' => $data['EvnPLDispDop13_id']
		);

		$params['getdate'] = $this->getFirstResultFromQuery("select dbo.tzGetDate()");

		$query = "
			select
				to_char(COALESCE(MIN(EUDDData.EvnUslugaDispDop_didDate), :getdate), 'YYYY-MM-DD') as mindate,
				to_char(COALESCE(MAX(EUDDData.EvnUslugaDispDop_didDate), :getdate), 'YYYY-MM-DD') as maxdate
			from v_DopDispInfoConsent DDIC
				left join v_SurveyTypeLink STL on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
				left join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
				LEFT JOIN LATERAL (
					select
						EUDD.EvnUslugaDispDop_didDate
					from v_EvnUslugaDispDop EUDD
						inner join v_EvnVizitDispDop EVDD on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
					where
						EVDD.EvnVizitDispDop_pid = :EvnPLDispDop13_id
						and EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
						and COALESCE(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
					limit 1
				) EUDDData on true
			where
				DDIC.EvnPLDisp_id = :EvnPLDispDop13_id
				and ST.SurveyType_Code NOT IN (1,48)
				{$filter}
		";
		
		$result = $this->db->query($query, $params);
	
        if (is_object($result))
        {
            $resp = $result->result('array');
			if (count($resp) > 0) {
				return $resp[0];
			}
        }
		
		return false;
	}

	/**
	 * Получение данных для отображения в ЭМК
	 */
	function getEvnPLDispDop13ViewData($data) {
		$queryParams = array(
			'EvnPLDisp_id' => $data['EvnPLDisp_id']
		);
		// – Редактирование карты диспансеризации / профосмотра доступно только из АРМ врача поликлиники, пользователем с привязкой к врачу терапевту (ВОП) / педиатру (ВОП),
		// отделение места работы которого совпадает с отделением места работы врача, создавшего карту.
		$accessType = "'view' as \"accessType\",";
		if (false && !empty($data['session']['CurARM']['PostMed_id']) && in_array($data['session']['CurARM']['PostMed_id'], array(73,74,75,76,40,46,47)) && !empty($data['session']['CurARM']['LpuSection_id'])) {
			$accessType = "case when COALESCE(msf.LpuSection_id, :LpuSection_id) = :LpuSection_id then 'edit' else 'view' end as \"accessType\",";
			$queryParams['LpuSection_id'] = $data['session']['CurARM']['LpuSection_id'];
		}

		$query = "
			select
				epldd13.EvnPLDispDop13_id as \"EvnPLDispDop13_id\",
				epldd13.EvnPLDispDop13_fid as \"EvnPLDispDop13_fid\",
				case
					when epldd13.MedStaffFact_id is not null then COALESCE(l.Lpu_Nick || ' ', '') || COALESCE(ls.LpuSection_Name || ' ', '') || COALESCE(msf.Person_Fio, '') 
					else COALESCE(l.Lpu_Nick || ' ', '') || COALESCE(pu.pmUser_Name, '')
				end as \"AuthorInfo\",
				'EvnPLDispDop13' as \"Object\",
				epldd13.DispClass_id as \"DispClass_id\",
				epldd13.Person_id as \"Person_id\",
				epldd13.PersonEvn_id as \"PersonEvn_id\",
				epldd13.Server_id as \"Server_id\",
				dc.DispClass_Code as \"DispClass_Code\",
				dc.DispClass_Name as \"DispClass_Name\",
				{$accessType}
				epldd13.PayType_id as \"PayType_id\",
				pt.PayType_Name as \"PayType_Name\",
				to_char(epldd13.EvnPLDispDop13_setDT, 'DD.MM.YYYY') as \"EvnPLDispDop13_setDate\",
				to_char(epldd13.EvnPLDispDop13_consDT, 'DD.MM.YYYY') as \"EvnPLDispDop13_consDate\",
				epldd13.HealthKind_id as \"HealthKind_id\",
				hk.HealthKind_Name as \"HealthKind_Name\",
				COALESCE(epldd13.EvnPLDispDop13_IsFinish, 1) as \"EvnPLDispDop13_IsFinish\",
				COALESCE(epldd13.EvnPLDispDop13_IsEndStage, 1) as \"EvnPLDispDop13_IsEndStage\",
				COALESCE(epldd13.EvnPLDispDop13_IsTwoStage, 1) as \"EvnPLDispDop13_IsTwoStage\",
				COALESCE(epldd13.EvnPLDispDop13_IsMobile, 1) as \"EvnPLDispDop13_IsMobile\",
				COALESCE(epldd13.EvnPLDispDop13_IsOutLpu, 1) as \"EvnPLDispDop13_IsOutLpu\",
				epldd13.RiskType_id as \"RiskType_id\",
				rt.RiskType_Name as \"RiskType_Name\",
				epldd13.EvnPLDispDop13_SumRick as \"EvnPLDispDop13_SumRick\",
				epldd13.EvnPLDispDop13_IsSanator as \"EvnPLDispDop13_IsSanator\",
				epldd13.NeedDopCure_id as \"NeedDopCure_id\",
				ndc.NeedDopCure_Name as \"NeedDopCure_Name\",
				epldd13.EvnPLDispDop13_IsDisp as \"EvnPLDispDop13_IsDisp\",
				epldd13.Diag_id as \"Diag_id\",
				d.Diag_Name as \"Diag_Name\",
				epldd13.Diag_sid as \"Diag_sid\",
				ds.Diag_Name as \"Diag_sName\",
				epldd13.EvnPLDispDop13_IsHypoglyc as \"EvnPLDispDop13_IsHypoglyc\",
				epldd13.EvnPLDispDop13_IsLipid as \"EvnPLDispDop13_IsLipid\",
				epldd13.CardioRiskType_id as \"CardioRiskType_id\",
				crt.CardioRiskType_Name as \"CardioRiskType_Name\",
				epldd13.EvnPLDispDop13_IsHypoten as \"EvnPLDispDop13_IsHypoten\",
				epldd13.EvnPLDispDop13_IsShortCons as \"EvnPLDispDop13_IsShortCons\",
				epldd13.EvnPLDispDop13_IsIrrational as \"EvnPLDispDop13_IsIrrational\",
				epldd13.EvnPLDispDop13_IsLowActiv as \"EvnPLDispDop13_IsLowActiv\",
				epldd13.EvnPLDispDop13_IsAlcoDepend as \"EvnPLDispDop13_IsAlcoDepend\",
				epldd13.EvnPLDispDop13_IsRiskAlco as \"EvnPLDispDop13_IsRiskAlco\",
				epldd13.EvnPLDispDop13_IsSmoking as \"EvnPLDispDop13_IsSmoking\",
				epldd13.EvnPLDispDop13_IsEsophag as \"EvnPLDispDop13_IsEsophag\",
				epldd13.EvnPLDispDop13_IsTub as \"EvnPLDispDop13_IsTub\",
				epldd13.EvnPLDispDop13_IsDoubleScan as \"EvnPLDispDop13_IsDoubleScan\",
				epldd13.EvnPLDispDop13_IsBrain as \"EvnPLDispDop13_IsBrain\",
				epldd13.EvnPLDispDop13_IsStenocard as \"EvnPLDispDop13_IsStenocard\",
				EvnPLDispDop13Sec.EvnPLDispDop13_id as \"EvnPLDispDop13Sec_id\"
			from
				v_EvnPLDispDop13 epldd13
				left join v_Lpu l on l.Lpu_id = epldd13.Lpu_id
				left join v_MedStaffFact msf on msf.MedStaffFact_id = epldd13.MedStaffFact_id
				left join v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id
				left join v_pmUser pu on pu.pmUser_id = epldd13.pmUser_updID
				left join v_DispClass dc on dc.DispClass_id = epldd13.DispClass_id
				left join v_PayType pt on pt.PayType_id = epldd13.PayType_id
				left join v_HealthKind hk on hk.HealthKind_id = epldd13.HealthKind_id
				left join v_RiskType rt on rt.RiskType_id = epldd13.RiskType_id
				left join v_NeedDopCure ndc on ndc.NeedDopCure_id = epldd13.NeedDopCure_id
				left join v_Diag d on d.Diag_id = epldd13.Diag_id
				left join v_Diag ds on ds.Diag_id = epldd13.Diag_sid
				left join v_CardioRiskType crt on crt.CardioRiskType_id = epldd13.CardioRiskType_id
				LEFT JOIN LATERAL (
					select
						EvnPLDispDop13_id
					from v_EvnPLDispDop13
					where
						EvnPLDispDop13_fid = EPLDD13.EvnPLDispDop13_id
					limit 1
				) EvnPLDispDop13Sec on true
			where
				epldd13.EvnPLDispDop13_id = :EvnPLDisp_id
		";
		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return false;
		}

		$resp = $result->result('array');

		if ( !empty($resp[0]['EvnPLDispDop13_fid']) ) {
			$query = "
				select 
					RT.RateType_SysNick as nick,
					RVT.RateValueType_SysNick as \"RateValueType_SysNick\",
					CASE RVT.RateValueType_SysNick
						WHEN 'int' THEN cast(R.Rate_ValueInt as varchar)
						WHEN 'float' THEN cast(cast(R.Rate_ValueFloat as decimal(16,3)) as varchar)
						WHEN 'string' THEN R.Rate_ValueStr
						WHEN 'template' THEN R.Rate_ValueStr
						WHEN 'reference' THEN cast(R.Rate_ValuesIs as varchar)
					END as value
				from v_DopDispInfoConsent DDIC
					left join v_SurveyTypeLink STL on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
					left join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
					left join v_UslugaComplex UC on UC.UslugaComplex_id = STL.UslugaComplex_id
					LEFT JOIN LATERAL (
						select
							EUDD.EvnUslugaDispDop_id
						from v_EvnUslugaDispDop EUDD
							inner join v_EvnVizitDispDop EVDD on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
						where
							EVDD.EvnVizitDispDop_pid = :EvnPLDispDop13_id and EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
							and COALESCE(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
						limit 1
					) EUDDData on true
					left join v_EvnUslugaRate eur on eur.EvnUsluga_id = EUDDData.EvnUslugaDispDop_id
					left join v_Rate r on r.Rate_id = eur.Rate_id 
					left join v_RateType rt on rt.RateType_id = r.RateType_id
					left join RateValueType rvt on rvt.RateValueType_id = rt.RateValueType_id
				where DDIC.EvnPLDisp_id = :EvnPLDispDop13_id and (DDIC.DopDispInfoConsent_IsAgree = 2 OR DDIC.DopDispInfoConsent_IsEarlier = 2) and ST.SurveyType_Code NOT IN (1,48)
					and (ST.SurveyType_Code IN (6,12) OR RT.RateType_SysNick <> 'glucose')
			";
			$result = $this->db->query($query, array(
				'EvnPLDispDop13_id' => $resp[0]['EvnPLDispDop13_fid']
			));

			if ( is_object($result) ) {
				$results = $result->result('array');

				foreach ( $results as $oneresult ) {
					if ( $oneresult['RateValueType_SysNick'] == 'float' ) {
						if ( $oneresult['nick'] == 'bio_blood_kreatinin' ) {
							// Ничего не делаем
						}
						else if ( in_array($oneresult['nick'], array('AsAt', 'AlAt')) ) {
							// Убираем последнюю цифру в значении
							if (!empty($oneresult['value'])) {
								$oneresult['value'] = substr($oneresult['value'], 0, strlen($oneresult['value']) - 1);
							}
						}
						else {
							// Убираем последние 2 цифры в значении
							if (!empty($oneresult['value'])) {
								$oneresult['value'] = substr($oneresult['value'], 0, strlen($oneresult['value']) - 2);
							}
						}
					}

					$resp[0][$oneresult['nick']] = $oneresult['value'];
				}
			}
		}
		
		// нужно получить значения результатов услуг из EvnUslugaRate
		if ( !empty($resp[0]['EvnPLDispDop13_id']) ) {
			$query = "
				select 
					RT.RateType_SysNick as nick,
					RVT.RateValueType_SysNick as \"RateValueType_SysNick\",
					CASE RVT.RateValueType_SysNick
						WHEN 'int' THEN cast(R.Rate_ValueInt as varchar)
						WHEN 'float' THEN cast(cast(R.Rate_ValueFloat as decimal(16,3)) as varchar)
						WHEN 'string' THEN R.Rate_ValueStr
						WHEN 'template' THEN R.Rate_ValueStr
						WHEN 'reference' THEN cast(R.Rate_ValuesIs as varchar)
					END as value
				from v_DopDispInfoConsent DDIC
					left join v_SurveyTypeLink STL on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
					left join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
					left join v_UslugaComplex UC on UC.UslugaComplex_id = STL.UslugaComplex_id
					LEFT JOIN LATERAL (
						select
							EUDD.EvnUslugaDispDop_id
						from v_EvnUslugaDispDop EUDD
							inner join v_EvnVizitDispDop EVDD on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
						where
							EVDD.EvnVizitDispDop_pid = :EvnPLDispDop13_id and EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
							and COALESCE(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
						limit 1
					) EUDDData on true
					left join v_EvnUslugaRate eur on eur.EvnUsluga_id = EUDDData.EvnUslugaDispDop_id
					left join v_Rate r on r.Rate_id = eur.Rate_id 
					left join v_RateType rt on rt.RateType_id = r.RateType_id
					left join RateValueType rvt on rvt.RateValueType_id = rt.RateValueType_id
				where DDIC.EvnPLDisp_id = :EvnPLDispDop13_id and (DDIC.DopDispInfoConsent_IsAgree = 2 OR DDIC.DopDispInfoConsent_IsEarlier = 2) and ST.SurveyType_Code NOT IN (1,48)
					and (ST.SurveyType_Code IN (6,12) OR RT.RateType_SysNick <> 'glucose')
			";
			$result = $this->db->query($query, array(
				'EvnPLDispDop13_id' => $resp[0]['EvnPLDispDop13_id']
			));

			if ( is_object($result) ) {
				$results = $result->result('array');

				foreach ( $results as $oneresult ) {
					if ( $oneresult['RateValueType_SysNick'] == 'float' ) {
						if ( $oneresult['nick'] == 'bio_blood_kreatinin' ) {
							// Ничего не делаем
						}
						else if ( in_array($oneresult['nick'], array('AsAt', 'AlAt')) ) {
							// Убираем последнюю цифру в значении
							if ( !empty($oneresult['value']) ) {
								$oneresult['value'] = substr($oneresult['value'], 0, strlen($oneresult['value']) - 1);
							}
						}
						else {
							// Убираем последние 2 цифры в значении
							if ( !empty($oneresult['value']) ) {
								$oneresult['value'] = substr($oneresult['value'], 0, strlen($oneresult['value']) - 2);
							}
						}
					}

					$resp[0][$oneresult['nick']] = $oneresult['value'];
				}
			}
		}

		return $resp;
	}

	/**
	 * Контроль на обязательные осмотры/исследования (для Уфы)
	 */
	function checkEvnVizitDispDopExists($data) {
		$query = "
				select
					ST.SurveyType_Name as \"SurveyType_Name\",
					case
						when EUDD.EvnUslugaDispDop_didDate is null then 0 else 1
					end as \"EvnVizitDispDop_IsExists\"
				from v_DopDispInfoConsent DDIC
					left join SurveyTypeLink STL on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
					left join SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
					left join v_EvnVizitDispDop EVDD on EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
					left join v_EvnUslugaDispDop EUDD on EUDD.EvnUslugaDispDop_pid = EVDD.EvnVizitDispDop_id
					left join v_YesNo IsAgree on IsAgree.YesNo_id = DDIC.DopDispInfoConsent_IsAgree
				where
					DDIC.EvnPLDisp_id = :EvnPLDisp_id
					and ST.SurveyType_Code in (13,18,21)
					and IsAgree.YesNo_Code = 1
					and COALESCE(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
			";
		$params['EvnPLDisp_id'] = $data['EvnPLDispDop13_id'];
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (контроль осмотров/исследований)');
		}
		$response = $result->result('array');
		foreach($response as $item) {
			if (!$item['EvnVizitDispDop_IsExists']) {
				return array('Error_Msg' => 'Сохранены не все обязательные осмотры/исследования.');
			}
		}
		return true;
	}

	/**
	 * Сохранение карты
	 * @todo Завернуть все в транзакцию
	 */	
    public function saveEvnPLDispDop13($data)
    {
		// Стартуем транзакцию, т.к. при сохранении карты ДВН сохраняются и другие объекты
		$this->db->trans_begin();

		$savedData = array();
		if (!empty($data['EvnPLDispDop13_id'])) {
			$savedData = $this->getFirstRowFromQuery("
		  		select
					PayType_id as \"PayType_id\",
					Lpu_CodeSMO as \"Lpu_CodeSMO\",
					EvnPLDispDop13_Percent as \"EvnPLDispDop13_Percent\",
					MedStaffFact_id as \"MedStaffFact_id\",
					EvnPLDispDop13_IsOutLpu as \"EvnPLDispDop13_IsOutLpu\",
					EvnPLDispDop13_IsSuspectZNO as \"EvnPLDispDop13_IsSuspectZNO\",
					Diag_spid as \"Diag_spid\",
					EvnDirection_aid as \"EvnDirection_aid\",
					EvnPLDispDop13_IsInRegZNO as \"EvnPLDispDop13_IsInRegZNO\",
					Registry_sid as \"Registry_sid\",
					EvnPLDispDop13_IsNewOrder as \"EvnPLDispDop13_IsNewOrder\",
					EvnPLDispDop13_IsStenocard as \"EvnPLDispDop13_IsStenocard\",
					EvnPLDispDop13_IsDoubleScan as \"EvnPLDispDop13_IsDoubleScan\",
					EvnPLDispDop13_IsTub as \"EvnPLDispDop13_IsTub\",
					EvnPLDispDop13_IsEsophag as \"EvnPLDispDop13_IsEsophag\",
					EvnPLDispDop13_IsSmoking as \"EvnPLDispDop13_IsSmoking\",
					EvnPLDispDop13_IsRiskAlco as \"EvnPLDispDop13_IsRiskAlco\",
					EvnPLDispDop13_IsAlcoDepend as \"EvnPLDispDop13_IsAlcoDepend\",
					EvnPLDispDop13_IsLowActiv as \"EvnPLDispDop13_IsLowActiv\",
					EvnPLDispDop13_IsIrrational as \"EvnPLDispDop13_IsIrrational\",
					Diag_id as \"Diag_id\",
					EvnPLDispDop13_IsDisp as \"EvnPLDispDop13_IsDisp\",
					EvnPLDispDop13_IsAmbul as \"EvnPLDispDop13_IsAmbul\",
					EvnPLDispDop13_IsStac as \"EvnPLDispDop13_IsStac\",
					EvnPLDispDop13_IsSanator as \"EvnPLDispDop13_IsSanator\",
					EvnPLDispDop13_SumRick as \"EvnPLDispDop13_SumRick\",
					RiskType_id as \"RiskType_id\",
					EvnPLDispDop13_IsSchool as \"EvnPLDispDop13_IsSchool\",
					EvnPLDispDop13_IsProphCons as \"EvnPLDispDop13_IsProphCons\",
					HealthKind_id as \"HealthKind_id\",
					EvnPLDispDop13_IsEndStage as \"EvnPLDispDop13_IsEndStage\",
					EvnPLDispDop13_IsTwoStage as \"EvnPLDispDop13_IsTwoStage\",
					CardioRiskType_id as \"CardioRiskType_id\",
					NeedDopCure_id as \"NeedDopCure_id\",
					EvnPLDispDop13_IsHypoten as \"EvnPLDispDop13_IsHypoten\",
					EvnPLDispDop13_IsLipid as \"EvnPLDispDop13_IsLipid\",
					EvnPLDispDop13_IsHypoglyc as \"EvnPLDispDop13_IsHypoglyc\",
					Diag_sid as \"Diag_sid\",
					EvnPLDispDop13_IsBrain as \"EvnPLDispDop13_IsBrain\",
					EvnPLDispDop13_IsShortCons as \"EvnPLDispDop13_IsShortCons\",
					EvnPLDispDop13_IsUseNarko as \"EvnPLDispDop13_IsUseNarko\",
					EvnPLDispDop13_IsTIA as \"EvnPLDispDop13_IsTIA\",
					EvnPLDispDop13_IsRespiratory as \"EvnPLDispDop13_IsRespiratory\",
					EvnPLDispDop13_IsLungs as \"EvnPLDispDop13_IsLungs\",
					EvnPLDispDop13_IsTopGastro as \"EvnPLDispDop13_IsTopGastro\",
					EvnPLDispDop13_IsBotGastro as \"EvnPLDispDop13_IsBotGastro\",
					EvnPLDispDop13_IsSpirometry as \"EvnPLDispDop13_IsSpirometry\",
					EvnPLDispDop13_IsHeartFailure as \"EvnPLDispDop13_IsHeartFailure\",
					EvnPLDispDop13_IsOncology as \"EvnPLDispDop13_IsOncology\",
					EvnClass_id as \"EvnClass_id\",
					EvnClass_Name as \"EvnClass_Name\",
					EvnPLDispDop13_id as \"EvnPLDispDop13_id\",
					EvnPLDispDop13_setDate as \"EvnPLDispDop13_setDate\",
					EvnPLDispDop13_setTime as \"EvnPLDispDop13_setTime\",
					EvnPLDispDop13_didDate as \"EvnPLDispDop13_didDate\",
					EvnPLDispDop13_didTime as \"EvnPLDispDop13_didTime\",
					EvnPLDispDop13_disDate as \"EvnPLDispDop13_disDate\",
					EvnPLDispDop13_disTime as \"EvnPLDispDop13_disTime\",
					EvnPLDispDop13_pid as \"EvnPLDispDop13_pid\",
					EvnPLDispDop13_rid as \"EvnPLDispDop13_rid\",
					Lpu_id as \"Lpu_id\",
					Server_id as \"Server_id\",
					PersonEvn_id as \"PersonEvn_id\",
					EvnPLDispDop13_setDT as \"EvnPLDispDop13_setDT\",
					EvnPLDispDop13_disDT as \"EvnPLDispDop13_disDT\",
					EvnPLDispDop13_didDT as \"EvnPLDispDop13_didDT\",
					EvnPLDispDop13_insDT as \"EvnPLDispDop13_insDT\",
					EvnPLDispDop13_updDT as \"EvnPLDispDop13_updDT\",
					EvnPLDispDop13_Index as \"EvnPLDispDop13_Index\",
					EvnPLDispDop13_Count as \"EvnPLDispDop13_Count\",
					pmUser_insID as \"pmUser_insID\",
					pmUser_updID as \"pmUser_updID\",
					Person_id as \"Person_id\",
					Morbus_id as \"Morbus_id\",
					EvnPLDispDop13_IsSigned as \"EvnPLDispDop13_IsSigned\",
					pmUser_signID as \"pmUser_signID\",
					EvnPLDispDop13_signDT as \"EvnPLDispDop13_signDT\",
					EvnPLDispDop13_IsArchive as \"EvnPLDispDop13_IsArchive\",
					EvnPLDispDop13_Guid as \"EvnPLDispDop13_Guid\",
					EvnPLDispDop13_IndexMinusOne as \"EvnPLDispDop13_IndexMinusOne\",
					EvnStatus_id as \"EvnStatus_id\",
					EvnPLDispDop13_statusDate as \"EvnPLDispDop13_statusDate\",
					EvnPLDispDop13_IsTransit as \"EvnPLDispDop13_IsTransit\",
					EvnPLDispDop13_VizitCount as \"EvnPLDispDop13_VizitCount\",
					EvnPLDispDop13_IsFinish as \"EvnPLDispDop13_IsFinish\",
					Person_Age as \"Person_Age\",
					EvnPLDispDop13_isMseDirected as \"EvnPLDispDop13_isMseDirected\",
					AttachType_id as \"AttachType_id\",
					Lpu_aid as \"Lpu_aid\",
					EvnPLDispDop13_IsInReg as \"EvnPLDispDop13_IsInReg\",
					EvnPLDispDop13_consDT as \"EvnPLDispDop13_consDT\",
					DispClass_id as \"DispClass_id\",
					EvnPLDispDop13_fid as \"EvnPLDispDop13_fid\",
					EvnPLDispDop13_IsMobile as \"EvnPLDispDop13_IsMobile\",
					Lpu_mid as \"Lpu_mid\",
					EvnPLDispDop13_IsPaid as \"EvnPLDispDop13_IsPaid\",
					EvnPLDispDop13_IsRefusal as \"EvnPLDispDop13_IsRefusal\",
					EvnPLDispDop13_IndexRep as \"EvnPLDispDop13_IndexRep\",
					EvnPLDispDop13_IndexRepInReg as \"EvnPLDispDop13_IndexRepInReg\"
		  		from v_EvnPLDispDop13
		  		where EvnPLDispDop13_id = :EvnPLDispDop13_id
		  		limit 1
			", $data, true);
			if ($savedData === false) {
				$this->db->trans_rollback();
				return array('Error_Msg' => 'Ошибка при получении данных карты');
			}
		}

		// Если случай закрыт и задана дата справки, то сохраняем справку.
		if (!empty($data['EvnPLDispDop13_id']) && !empty($data['EvnPLDispDop13_IsEndStage']) && $data['EvnPLDispDop13_IsEndStage'] == 2 && !empty($data['EvnCostPrint_setDT']))
		{
			// сохраняем справку
			$this->load->model('CostPrint_model');
			$this->CostPrint_model->saveEvnCostPrint(array(
				'Evn_id' => $data['EvnPLDispDop13_id'],
				'CostPrint_IsNoPrint' => $data['EvnCostPrint_IsNoPrint'],
				'CostPrint_setDT' => $data['EvnCostPrint_setDT'],
				'pmUser_id' => $data['pmUser_id']
			));
		}

		$checkResult = $this->checkPersonData($data);
		If ( empty($checkResult['PersonEvn_id']) ) {
			$this->db->trans_rollback();
			return $checkResult;
		}

		if ($data['EvnPLDispDop13_IsTwoStage'] == 2 && $data['EvnPLDispDop13_IsEndStage'] != 2) {
			return array('Error_Msg' => 'На второй этап диспансеризации могут быть переведены только пациенты, окончившие первый этап');
		}

		if (!empty($data['EvnPLDispDop13_id']) && ($data['EvnPLDispDop13_IsEndStage'] != 2 || $data['EvnPLDispDop13_IsTwoStage'] != 2)) {
			// проверяем наличие карты 2 этапа, если есть не даём сохранить.
			$resp = $this->queryResult("
				select EvnPLDispDop13_id as \"EvnPLDispDop13_id\" from v_EvnPLDispDop13 where EvnPLDispDop13_fid = :EvnPLDispDop13_fid
			", array(
				'EvnPLDispDop13_fid' => $data['EvnPLDispDop13_id']
			));

			if (!empty($resp[0]['EvnPLDispDop13_id'])) {
				$this->db->trans_rollback();
				return array('Error_Msg' => 'У пациента имеется карта 2 этапа, нельзя сохранить карту 1 этапа без полей "1 этап закончен" и "Направлен на 2 этап диспансеризации"');
			}
		}

		if (getRegionNick() == 'perm' && !empty($data['EvnPLDispDop13_id'])) {
			// Если сопутствующий диагноз  в одном из осмотров (исследований)  повторяется в списке «Впервые выявленное заболевание» с типом «Сопутствующий»  с разными характерами заболевания,
			// то  показывать сообщение об ошибке:  «Ошибка. Повторение диагнозов с разным характером заболевания в блоке Сопутствующие заболевания. ОК.»
			$resp = $this->queryResult("
				select 
					EDDD.EvnDiagDopDisp_id as \"EvnDiagDopDisp_id\"
				from
					v_EvnDiagDopDisp EDDD
					INNER JOIN LATERAL (
						select
							EDDD2.EvnDiagDopDisp_id
						from
							v_EvnDiagDopDisp EDDD2
							inner join v_EvnUslugaDispDop eudd on eudd.EvnUslugaDispDop_id = EDDD2.EvnDiagDopDisp_pid
							inner join v_EvnVizitDispDop evdd on evdd.EvnVizitDispDop_id = eudd.EvnUslugaDispDop_pid
						where
							evdd.EvnVizitDispDop_pid = :EvnPLDispDop13_id
							and EDDD2.DiagSetClass_id = 3
							and EDDD2.Diag_id = EDDD.Diag_id
							and EDDD2.DeseaseDispType_id <> EDDD.DeseaseDispType_id
						limit 1
					) EDDD2 on true
				where
					EDDD.EvnDiagDopDisp_pid = :EvnPLDispDop13_id
					and EDDD.DiagSetClass_id = 3
				limit 1
			", array(
				'EvnPLDispDop13_id' => $data['EvnPLDispDop13_id']
			));
			if (!empty($resp[0]['EvnDiagDopDisp_id'])) {
				$this->db->trans_rollback();
				return array('Error_Msg' => 'Повторение диагнозов с разным характером заболевания в блоке Сопутствующие заболевания');
			}
		}

		if ($this->getRegionNick() == 'krasnoyarsk' && $data['DispClass_id'] == 1) {
			if (!$data['checkAttributeforLpuSection']) {
				$checkDate = $this->checkAttributeforLpuSection(array(
					'EvnPLDispDop13_id' => $data['EvnPLDispDop13_id']
				));

				If ($checkDate[0] != "Ok") {
					return array('Error_Msg' => 'YesNo', 'Alert_Msg' => $checkDate, 'Error_Code' => 110);
				}

			}else if($data['checkAttributeforLpuSection']==2) {
				$data['EvnPLDispDop13_IsMobile'] = 'on';
			}
		}
		
		if ($data['EvnPLDispDop13_IsMobile']) { $data['EvnPLDispDop13_IsMobile'] = 2; } else { $data['EvnPLDispDop13_IsMobile'] = 1; }
		if ($data['EvnPLDispDop13_IsOutLpu']) { $data['EvnPLDispDop13_IsOutLpu'] = 2; } else { $data['EvnPLDispDop13_IsOutLpu'] = 1; }

		$data['EvnPLDispDop13_Percent'] = null;
		// считаем доли и записываем в _Percent правильное значение
		$data['EvnPLDispDop13_consDate'] = $this->getFirstResultFromQuery("
			select to_char(EvnPLDispDop13_consDT, 'YYYY-MM-DD') from v_EvnPLDispDop13 where EvnPLDispDop13_id = :EvnPLDispDop13_id
		", $data);

		$onDate = $data['EvnPLDispDop13_consDate'];
		if (!empty($data['EvnPLDispDop13_IsNewOrder']) && $data['EvnPLDispDop13_IsNewOrder'] == 2) {
			$onDate = date('Y', strtotime($data['EvnPLDispDop13_consDate'])) . '-12-31';
		}

		if ( $data['DispClass_id'] == 1 && !$this->allowDVN($data['Person_id'], $onDate) ) {
			$dateX = $this->getNewDVNDate();

			if ( !empty($dateX) ) {
				return array('Error_Msg' => 'Указана некорректная дата информационного согласия. По приказу, действующему до ' . date('d.m.Y', strtotime($dateX)) . ', пациент не подлежит ДВН в указанном году.');
			}
			else {
				return array('Error_Msg' => 'Пациент не подлежит ДВН в указанном году.');
			}
		}

		// Проверяем наличие услуг, у которых в согласии указан признак "Выполнено ранее", но дата больше даты подписания согласия
		// @task https://redmine.swan.perm.ru/issues/66282
		if ( $this->getRegionNick() == 'perm' /*&& $data['DispClass_id'] == 1*/ && !empty($data['EvnPLDispDop13_id']) && !empty($data['EvnPLDispDop13_consDate']) ) {
			$query = "
				select
					evdd.EvnVizitDispDop_id as \"EvnVizitDispDop_id\",
					1 as \"ErrorType\"
				from v_EvnVizitDispDop evdd
					inner join v_DopDispInfoConsent ddic on ddic.DopDispInfoConsent_id = evdd.DopDispInfoConsent_id
				where
					evdd.EvnVizitDispDop_pid = :EvnPLDispDop13_id
					and COALESCE(ddic.DopDispInfoConsent_IsEarlier, 1) = 2
					and cast(evdd.EvnVizitDispDop_setDT as date) >= :EvnPLDispDop13_consDate

				union all

				select
					evdd.EvnVizitDispDop_id as \"EvnVizitDispDop_id\",
					2 as \"ErrorType\"
				from v_EvnVizitDispDop evdd
					inner join v_DopDispInfoConsent ddic on ddic.DopDispInfoConsent_id = evdd.DopDispInfoConsent_id
				where
					evdd.EvnVizitDispDop_pid = :EvnPLDispDop13_id
					and COALESCE(ddic.DopDispInfoConsent_IsEarlier, 1) = 1
					and cast(evdd.EvnVizitDispDop_setDT as date) < :EvnPLDispDop13_consDate
			";
			$result = $this->db->query($query, $data);

			if ( !is_object($result) ) {
				$this->db->trans_rollback();
				return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (проверка соответствия дат проведения осмотров/иследований и даты подписания добровольного информированного согласия)');
			}

			$resp = $result->result('array');

			if ( is_array($resp) && count($resp) > 0 ) {
				$isEarlier = 0;
				$notEarlier = 0;

				foreach ( $resp as $rec ) {
					if ( $rec['ErrorType'] == 1 ) {
						$isEarlier++;
					}
					else {
						$notEarlier++;
					}
				}

				$errorText = "Обнаружено несоответствие даты проведения осмотра/исследования и даты подписания согласия:<br />";

				if ( !empty($isEarlier) ) {
					$errorText .= 'Кол-во услуг с отметкой "Пройдено ранее": ' . $isEarlier . '<br />';
				}

				if ( !empty($notEarlier) ) {
					$errorText .= 'Кол-во услуг без отметки "Пройдено ранее": ' . $notEarlier . '<br />';
				}

				$this->db->trans_rollback();

				return array('Error_Msg' => $errorText);
			}
		}

		if (in_array($this->getRegionNick(), array('perm', 'vologda')) && $data['EvnPLDispDop13_IsEndStage'] == 2 && $data['DispClass_id'] == 1 && strtotime($data['EvnPLDispDop13_consDate']) >= strtotime('01.04.2015')) {
			// 0. получаем актуальные согласия
			$dopdispinfoconsent = $this->loadDopDispInfoConsent(array(
				'EvnPLDispDop13_id' => $data['EvnPLDispDop13_id'],
				'EvnPLDispDop13_fid' => $data['EvnPLDispDop13_fid'],
				'Lpu_id' => $data['Lpu_id'],
				'Person_id' => $data['Person_id'],
				'DispClass_id' => $data['DispClass_id'],
				'EvnPLDispDop13_consDate' => $data['EvnPLDispDop13_consDate'],
				'EvnPLDispDop13_IsNewOrder' => $data['EvnPLDispDop13_IsNewOrder'],
				'session' => $data['session'],
				'isDopUsl' => 1
			));

			// 1. собираем пакеты
			$Packets = array();
			foreach($dopdispinfoconsent as $consent) {
				if (!empty($consent['SurveyTypeLink_IsUslPack'])) {
					if (empty($Packets[$consent['SurveyTypeLink_IsUslPack']])) {
						$Packets[$consent['SurveyTypeLink_IsUslPack']] = 1;
					} else {
						$Packets[$consent['SurveyTypeLink_IsUslPack']]++;
					}
				}
			}

			$consTime = 0;
			$resp_consDT = $this->queryResult("select to_char(EvnPLDispDop13_consDT, 'DD.MM.YYYY') as \"EvnPLDispDop13_consDT\" from v_EvnPLDispDop13 where EvnPLDispDop13_id = :EvnPLDispDop13_id limit 1", array(
				'EvnPLDispDop13_id' => $data['EvnPLDispDop13_id']
			));
			if (!empty($resp_consDT[0])) {
				$consTime = strtotime($resp_consDT[0]['EvnPLDispDop13_consDT']);
			}
			
			// 2. считаем ПД и ПРД (Проведенное в рамках ДВН количество долей (ПД) и Проведенное в рамках ДВН и выполненных ранее количество долей (ПРД))
			$kolvoEarlier = 0;
			$kolvoDid = 0;
			$evnuslugadispdop = $this->loadEvnUslugaDispDopGrid(array(
				'EvnPLDispDop13_id' => $data['EvnPLDispDop13_id'],
				'isDopUsl' => 1
			));
			foreach($evnuslugadispdop as $usluga) {
				if (!empty($usluga['DopDispInfoConsent_id']) && !empty($usluga['EvnUslugaDispDop_didDate'])) {
					if ( !empty($usluga['SurveyTypeLink_IsUslPack']) && !empty($Packets[$usluga['SurveyTypeLink_IsUslPack']]) ) {
						if (strtotime($usluga['EvnUslugaDispDop_didDate']) < $consTime) {
							$kolvoEarlier = $kolvoEarlier + 1 / $Packets[$usluga['SurveyTypeLink_IsUslPack']];
						} else {
							$kolvoDid = $kolvoDid + 1 / $Packets[$usluga['SurveyTypeLink_IsUslPack']];
						}
					} else {
						if (strtotime($usluga['EvnUslugaDispDop_didDate']) < $consTime) {
							$kolvoEarlier++;
						} else {
							$kolvoDid++;
						}
					}
				}
			};
			//echo 'ПРД='.$kolvoEarlier." - ПД=".$kolvoDid." ";
			
			
			// 3. считаем ТД (Требуемое количество долей для ДВН)
			// это всё из согласия за исключением SurveyType_Code = 1 и SurveyType_Code = 48 и отказов и невозможно по показаниям
			$kolvo = 0;
			foreach($dopdispinfoconsent as $consent) {
				if (
					!in_array($consent['SurveyType_Code'], array(1,48))
					&& (
						$consent['DopDispInfoConsent_id'] > 0
						|| $consent['SurveyType_Code'] == 20
					)
					&& (
						// отказы берем только если не "невозможно по показаниям" и положена по возрасту
						($consent['DopDispInfoConsent_IsImpossible'] === 'hidden' && $consent['DopDispInfoConsent_IsAgeCorrect'] == '1')
						|| $consent['DopDispInfoConsent_IsAgree'] == 1
						|| $consent['DopDispInfoConsent_IsEarlier'] == 1
					)
				) {
					if ( !empty($consent['SurveyTypeLink_IsUslPack']) && !empty($Packets[$consent['SurveyTypeLink_IsUslPack']]) ) {
						$kolvo = $kolvo + 1 / $Packets[$consent['SurveyTypeLink_IsUslPack']];
					} else {
						$kolvo++;
					}
				}
			}

			$PdKolvo = ceil(round($kolvoDid*100)/100);
			$PrdKolvo = ceil(round(($kolvoDid + $kolvoEarlier)*100)/100);
			if (in_array(getRegionNick(), ['krasnoyarsk', 'perm']) && (
				$data['EvnPLDispDop13_consDate'] >= $this->getNewDVNDate()
				|| $data['EvnPLDispDop13_IsNewOrder'] == 2
			)) {
				$TdKolvo = ceil(round($kolvo * 85)/100);
			} else {
				$count85Percent = $this->get2018Dvn185Volume(array(
					'Person_id' => $data['Person_id'],
					'onDate' => $data['EvnPLDispDop13_consDate']
				));
				if (!empty($count85Percent['count85Percent'])) {
					$TdKolvo = $count85Percent['count85Percent'];
				} else {
					return array('Error_Msg' => 'Для сохранения карты ДВН администратору необходимо завести объём 2018_ДВН1_85');
				}
			}
			
			//echo " ".$kolvo." / ПД=".$PdKolvo." - ПРД=".$PrdKolvo." - ТД=".$TdKolvo." ";

			// 4. записываем _Percent
			if ($PdKolvo >= $TdKolvo) {
				$data['EvnPLDispDop13_Percent'] = 1;
			} else if ($PrdKolvo >= $TdKolvo) {
				$data['EvnPLDispDop13_Percent'] = 2;
			} else {
				// ПРД < ТД => нельзя закрыть карту
				$this->db->trans_rollback();
				return array('Error_Msg' => 'Количество осмотров/исследований недостаточно для закрытия карты диспансеризации взрослого населения.');
			}
			//echo " % = ".$data['EvnPLDispDop13_Percent']." ";
		}

		if ($this->getRegionNick() == 'pskov' && $data['EvnPLDispDop13_IsEndStage'] == 2 && strtotime($data['EvnPLDispDop13_consDate']) >= strtotime('01.05.2015')) {
			// 0. получаем актуальные согласия
			$dopdispinfoconsent = $this->loadDopDispInfoConsent(array(
				'EvnPLDispDop13_id' => $data['EvnPLDispDop13_id'],
				'EvnPLDispDop13_fid' => $data['EvnPLDispDop13_fid'],
				'Lpu_id' => $data['Lpu_id'],
				'Person_id' => $data['Person_id'],
				'DispClass_id' => $data['DispClass_id'],
				'EvnPLDispDop13_consDate' => $data['EvnPLDispDop13_consDate'],
				'EvnPLDispDop13_IsNewOrder' => $data['EvnPLDispDop13_IsNewOrder'],
				'session' => $data['session']
			));
			$evnuslugadispdop = $this->loadEvnUslugaDispDopGrid(array(
				'EvnPLDispDop13_id' => $data['EvnPLDispDop13_id']
			));

			// ищем дату терапевта
			$EvnUslugaDispDop_TerDate = null;
			foreach($evnuslugadispdop as $usluga) {
				if ($usluga['SurveyType_Code'] == 19) {
					// терапевт
					$EvnUslugaDispDop_TerDate = strtotime($usluga['EvnUslugaDispDop_didDate']);
				}
			}

			// считаем количество выполненных услуг
			$kolvoEarlier = 0;
			$kolvoDid = 0;
			foreach($evnuslugadispdop as $usluga) {
				if (!empty($usluga['DopDispInfoConsent_id']) && !empty($usluga['EvnUslugaDispDop_didDate'])) {
					$DopDispInfoConsent_IsEarlier = $usluga['DopDispInfoConsent_IsEarlier'];
					if (!empty($EvnUslugaDispDop_TerDate)) {
						$DopDispInfoConsent_IsEarlier = 1;
						$DopDispInfoConsent_IsAgree = 2;

						// если проведена ранее чем за 30 дней от терапевта, значит проведено ранее
						if ($EvnUslugaDispDop_TerDate - strtotime($usluga['EvnUslugaDispDop_didDate']) > 30 * 24 * 60 * 60) {
							$DopDispInfoConsent_IsEarlier = 2;
							$DopDispInfoConsent_IsAgree = 1;
						}

						if ($DopDispInfoConsent_IsEarlier != $usluga['DopDispInfoConsent_IsEarlier']) {
							// надо обновить признаки в согласии
							$this->db->query("
								update
									DopDispInfoConsent
								set
									DopDispInfoConsent_IsEarlier = :DopDispInfoConsent_IsEarlier,
									DopDispInfoConsent_IsAgree = :DopDispInfoConsent_IsAgree
								where
									DopDispInfoConsent_id = :DopDispInfoConsent_id
							", array(
								'DopDispInfoConsent_IsEarlier' => $DopDispInfoConsent_IsEarlier,
								'DopDispInfoConsent_IsAgree' => $DopDispInfoConsent_IsAgree,
								'DopDispInfoConsent_id' => $usluga['DopDispInfoConsent_id']
							));
						}
					}

					if ($DopDispInfoConsent_IsEarlier == 2) {
						$kolvoEarlier++;
					} else {
						$kolvoDid++;
					}
				}
			};

			// считаем общее количество
			$kolvo = 0;
			foreach($dopdispinfoconsent as $consent) {
				if (
					!in_array($consent['SurveyType_Code'], array(1,48))
					&& (
						$consent['DopDispInfoConsent_id'] > 0
						|| $consent['SurveyType_Code'] == 20
					)
					&& (
						// отказы берем только если не "невозможно по показаниям" и положена по возрасту
						($consent['DopDispInfoConsent_IsImpossible'] === 'hidden' && $consent['DopDispInfoConsent_IsAgeCorrect'] == '1')
						|| $consent['DopDispInfoConsent_IsAgree'] == 1
						|| $consent['DopDispInfoConsent_IsEarlier'] == 1
					)
				) {
					$kolvo++;
				}
			}

			if (
				$data['EvnPLDispDop13_consDate'] >= $this->getNewDVNDate()
				|| $data['EvnPLDispDop13_IsNewOrder'] == 2
			) {
				$TdKolvo = round($kolvo * 0.85);
			} else {
				$count85Percent = $this->get2018Dvn185Volume(array(
					'Person_id' => $data['Person_id'],
					'onDate' => $data['EvnPLDispDop13_consDate']
				));
				if (!empty($count85Percent['count85Percent'])) {
					$TdKolvo = $count85Percent['count85Percent'];
				} else {
					return array('Error_Msg' => 'Для сохранения карты ДВН администратору необходимо завести объём 2018_ДВН1_85');
				}
			}

			if ($kolvoDid + $kolvoEarlier >= $TdKolvo && $kolvoEarlier >= round($kolvo * 0.15)) {
				$data['EvnPLDispDop13_Percent'] = 2;
			} else {
				$data['EvnPLDispDop13_Percent'] = 1;
			}
		}

		if ($this->getRegionNick() == 'penza' && $data['EvnPLDispDop13_IsEndStage'] == 2) {
			// получаем актуальные согласия
			$dopdispinfoconsent = $this->loadDopDispInfoConsent(array(
				'EvnPLDispDop13_id' => $data['EvnPLDispDop13_id'],
				'EvnPLDispDop13_fid' => $data['EvnPLDispDop13_fid'],
				'Lpu_id' => $data['Lpu_id'],
				'Person_id' => $data['Person_id'],
				'DispClass_id' => $data['DispClass_id'],
				'EvnPLDispDop13_consDate' => $data['EvnPLDispDop13_consDate'],
				'EvnPLDispDop13_IsNewOrder' => $data['EvnPLDispDop13_IsNewOrder'],
				'session' => $data['session']
			));

			// получаем услуги
			$evnuslugadispdop = $this->loadEvnUslugaDispDopGrid(array(
				'EvnPLDispDop13_id' => $data['EvnPLDispDop13_id']
			));

			// считаем количество выполненных услуг
			$kolvoDid = 0;
			foreach($evnuslugadispdop as $usluga) {
				if (!empty($usluga['DopDispInfoConsent_id']) && !empty($usluga['EvnUslugaDispDop_didDate'])) {
					$kolvoDid++;
				}
			};

			// считаем общее количество
			$kolvo = 0;
			foreach($dopdispinfoconsent as $consent) {
				if (!in_array($consent['SurveyType_Code'], array(1,48))) {
					$kolvo++;
				}
			}

			$count85Percent = $this->get2018Dvn185Volume(array(
				'Person_id' => $data['Person_id'],
				'onDate' => $data['EvnPLDispDop13_consDate']
			));
			if (!empty($count85Percent['count85Percent'])) {
				$TdKolvo = $count85Percent['count85Percent'];
			} else {
				return array('Error_Msg' => 'Для сохранения карты ДВН администратору необходимо завести объём 2018_ДВН1_85');
			}

			if ($kolvoDid >= $TdKolvo) {
				// Если количество осмотров/исследований, выполненных в рамках ДВН, равно либо превышает значение объёма, то сохраняется значение EvnPLDisp_Percent=1
				$data['EvnPLDispDop13_Percent'] = 1;
			} else {
				// иначе – сохраняется значение EvnPLDisp_Percent=2
				$data['EvnPLDispDop13_Percent'] = 2;
			}
		}

		if ($this->getRegionNick() == 'penza' && !empty($data['EvnPLDispDop13_disDate'])) {
			// 4.4 Если в период диспансеризации (дата подписания согласия – дата осмотра врача терапевта) у пациента была госпитализация в круглосуточный стационар (с типом оплаты «ОМС»)
			// с пересекающимся периодом диспансеризации или было посещение (с типом оплаты «ОМС») с датой, пересекающейся периодом диспансеризации,
			// то выводить сообщение «В период проведения диспансеризации пациент получил поликлиническую помощь (был госпитализирован). Случай диспансеризации будет отклонен от оплаты. ОК».
			$query = "
				with mv as (
					select PayType_id
					from v_PayType
					where PayType_SysNick = 'oms'
					limit 1
				)

				select *
				from (
					(select
						EvnVizitPL_id as \"Evn_id\"
					from
						v_EvnVizitPL
					where
						EvnVizitPL_setDate >= :EvnPLDispDop13_consDate
						and EvnVizitPL_setDate <= :EvnPLDispDop13_disDate
						and Person_id = :Person_id
						and PayType_id = (select PayType_id from mv)
					limit 1)
	
					union all
					(select
						es.EvnSection_id as \"Evn_id\"
					from
						v_EvnSection es
						inner join v_LpuSection ls on ls.LpuSection_id = es.LpuSection_id
						inner join v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
					where
						(
							(es.EvnSection_setDate >= :EvnPLDispDop13_consDate and es.EvnSection_setDate <= :EvnPLDispDop13_disDate) -- начало входит в промежуток
							OR (es.EvnSection_setDate < :EvnPLDispDop13_consDate and (es.EvnSection_disDate >= :EvnPLDispDop13_consDate OR es.EvnSection_disDate IS NULL)) -- начало до промежутка, а конец после
						)
						and lu.LpuUnitType_id = 1
						and es.Person_id = :Person_id
						and es.PayType_id = (select PayType_id from mv)
					limit 1)
				) t
			";
			$result = $this->db->query($query, $data);
			if (is_object($result)) {
				$resp = $result->result('array');
				if (!empty($resp[0]['Evn_id'])) {
					$this->db->trans_rollback();
					return array('Error_Msg' => 'В период проведения диспансеризации пациент получил поликлиническую помощь (был госпитализирован). Случай диспансеризации будет отклонен от оплаты.');
				}
			}
		}


		$setDtField = "(select EvnPLDispDop13_consDT from mv)";
		// получаем даты начала и конца услуг внутри диспансеризации.
		$minmaxdates = $this->getEvnUslugaDispDopMinMaxDates($data);
		if (is_array($minmaxdates)) {
			if (getRegionNick() == 'pskov') {
				// Для Пскова в качестве даты надо сохранять минимальную дату из услуг
				$setDtField = ":EvnPLDispDop13_setDate";
				$data['EvnPLDispDop13_setDate'] = $minmaxdates['mindate'];
			}
			$data['EvnPLDispDop13_disDate'] = $minmaxdates['maxdate'];
		} else {
			$data['EvnPLDispDop13_disDate'] = date('Y-m-d');
		}

		// если не закончен дата окончания нулевая.
		if (empty($data['EvnPLDispDop13_IsEndStage']) || $data['EvnPLDispDop13_IsEndStage'] == 1) {
			$data['EvnPLDispDop13_disDate'] = NULL;
			$data['EvnPLDispDop13_IsTwoStage'] = NULL; // не направлен на 2 этап, раз первый не закончен.
		} else {
			if (getRegionNick() == 'ufa') {
				$response = $this->checkEvnVizitDispDopExists($data);
				if ($response !== true) {
					$this->db->trans_rollback();
					return $response;
				}
			}
		}

		$data['MedStaffFact_id'] = !empty($data['session']['CurARM']['MedStaffFact_id'])?$data['session']['CurARM']['MedStaffFact_id']:null;
		
		$this->checkZnoDirection($data, 'EvnPLDispDop13');

   		$query = "
			with mv as (
				select
					EvnPLDispDop13_IsRefusal,
					EvnDirection_aid,
					EvnPLDispDop13_consDT
				from v_EvnPLDispDop13
				where EvnPLDispDop13_id = :EvnPLDispDop13_id
				limit 1
			)
			select
				EvnPLDispDop13_id as \"EvnPLDispDop13_id\",
				(select EvnPLDispDop13_consDT from mv) as \"EvnPLDispDop13_setDT\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_EvnPLDispDop13_upd (
				EvnPLDispDop13_id := :EvnPLDispDop13_id,
				MedStaffFact_id := :MedStaffFact_id,
				EvnPLDispDop13_IsNewOrder := :EvnPLDispDop13_IsNewOrder,
				EvnPLDispDop13_IndexRep := :EvnPLDispDop13_IndexRep,
				EvnPLDispDop13_IndexRepInReg := :EvnPLDispDop13_IndexRepInReg,
				PersonEvn_id := :PersonEvn_id,
				EvnPLDispDop13_setDT := {$setDtField},
				EvnPLDispDop13_disDT := :EvnPLDispDop13_disDate,
				Server_id := :Server_id,
				Lpu_id := :Lpu_id,
				DispClass_id := :DispClass_id,
				PayType_id := :PayType_id,
				EvnPLDispDop13_fid := :EvnPLDispDop13_fid,
				AttachType_id := 2,
				EvnPLDispDop13_IsStenocard := :EvnPLDispDop13_IsStenocard,
				EvnPLDispDop13_IsShortCons := :EvnPLDispDop13_IsShortCons,
				EvnPLDispDop13_IsBrain := :EvnPLDispDop13_IsBrain,
				EvnPLDispDop13_IsDoubleScan := :EvnPLDispDop13_IsDoubleScan,
				EvnPLDispDop13_IsTub := :EvnPLDispDop13_IsTub,
				EvnPLDispDop13_IsTIA := :EvnPLDispDop13_IsTIA,
				EvnPLDispDop13_IsRespiratory := :EvnPLDispDop13_IsRespiratory,
				EvnPLDispDop13_IsLungs := :EvnPLDispDop13_IsLungs,
				EvnPLDispDop13_IsTopGastro := :EvnPLDispDop13_IsTopGastro,
				EvnPLDispDop13_IsBotGastro := :EvnPLDispDop13_IsBotGastro,
				EvnPLDispDop13_IsSpirometry := :EvnPLDispDop13_IsSpirometry,
				EvnPLDispDop13_IsHeartFailure := :EvnPLDispDop13_IsHeartFailure,
				EvnPLDispDop13_IsOncology := :EvnPLDispDop13_IsOncology,
				EvnPLDispDop13_IsEsophag := :EvnPLDispDop13_IsEsophag,
				EvnPLDispDop13_IsSmoking := :EvnPLDispDop13_IsSmoking,
				EvnPLDispDop13_IsRiskAlco := :EvnPLDispDop13_IsRiskAlco,
				EvnPLDispDop13_IsAlcoDepend := :EvnPLDispDop13_IsAlcoDepend,
				EvnPLDispDop13_IsLowActiv := :EvnPLDispDop13_IsLowActiv,
				EvnPLDispDop13_IsIrrational := :EvnPLDispDop13_IsIrrational,
				EvnPLDispDop13_IsUseNarko := :EvnPLDispDop13_IsUseNarko,
				Diag_id := :Diag_id,
				Diag_sid := :Diag_sid,
				EvnPLDispDop13_IsDisp := :EvnPLDispDop13_IsDisp,
				NeedDopCure_id := :NeedDopCure_id,
				EvnPLDispDop13_IsStac := :EvnPLDispDop13_IsStac,
				EvnPLDispDop13_IsSanator := :EvnPLDispDop13_IsSanator,
				EvnPLDispDop13_SumRick := :EvnPLDispDop13_SumRick,
				RiskType_id := :RiskType_id,
				EvnPLDispDop13_IsSchool := :EvnPLDispDop13_IsSchool,
				EvnPLDispDop13_IsProphCons := :EvnPLDispDop13_IsProphCons,
				EvnPLDispDop13_IsHypoten := :EvnPLDispDop13_IsHypoten,
				EvnPLDispDop13_IsLipid := :EvnPLDispDop13_IsLipid,
				EvnPLDispDop13_IsHypoglyc := :EvnPLDispDop13_IsHypoglyc,
				HealthKind_id := :HealthKind_id,
				EvnPLDispDop13_IsEndStage := :EvnPLDispDop13_IsEndStage,
				EvnPLDispDop13_IsFinish := :EvnPLDispDop13_IsEndStage,
				EvnPLDispDop13_IsTwoStage := :EvnPLDispDop13_IsTwoStage,
				EvnPLDispDop13_consDT := (select EvnPLDispDop13_consDT from mv),
				EvnPLDispDop13_IsMobile := :EvnPLDispDop13_IsMobile, 
				EvnPLDispDop13_IsOutLpu := :EvnPLDispDop13_IsOutLpu, 
				Lpu_mid := :Lpu_mid,
				CardioRiskType_id := :CardioRiskType_id,
				EvnPLDispDop13_IsRefusal := (select EvnPLDispDop13_IsRefusal from mv),
				EvnDirection_aid := (select EvnDirection_aid from mv),
				EvnPLDispDop13_Percent := :EvnPLDispDop13_Percent,
				EvnPLDispDop13_IsSuspectZNO := :EvnPLDispDop13_IsSuspectZNO,
				Diag_spid := :Diag_spid,
				pmUser_id := :pmUser_id
			)
		";
		$result = $this->db->query($query, $data);
		
        if ( !is_object($result) ) {
			$this->db->trans_rollback();
			return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение карты ДД)');
		}

		$resp = $result->result('array');

		if ( empty($resp[0]['EvnPLDispDop13_id']) ) {
			$this->db->trans_rollback();
			return array('Error_Msg' => 'Ошибка при сохранении карты ДВН');
		}
		else if ( !empty($resp[0]['Error_Msg']) ) {
			$this->db->trans_rollback();
			return array('Error_Msg' => 'Ошибка при сохранении карты ДВН');
		}

		$data['EvnPLDispDop13_id'] = $resp[0]['EvnPLDispDop13_id'];
		$data['EvnPLDispDop13_setDT'] = $resp[0]['EvnPLDispDop13_setDT'];

		// Сохраняем скрытую услугу для Бурятии, если случай закончен
		// @task https://redmine.swan.perm.ru/issues/52175
		// Добавлен Крым
		// @task https://redmine.swan.perm.ru/issues/88196
		if (
			in_array($data['session']['region']['nick'], array('buryatiya', 'krym', 'msk')) && !empty($data['EvnPLDispDop13_id'])
			&& !empty($data['EvnPLDispDop13_IsEndStage']) && $data['EvnPLDispDop13_IsEndStage'] == 2
		) {
			// Ищем существующую услугу
			$query = "
				select
					EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\",
					UslugaComplex_id as \"UslugaComplex_id\",
					PayType_id as \"PayType_id\",
					to_char(EvnUslugaDispDop_setDT, 'DD.MM.YYYY') as \"EvnUslugaDispDop_setDate\"
				from v_EvnUslugaDispDop
				where EvnUslugaDispDop_pid = :EvnUslugaDispDop_pid
					and EvnUslugaDispDop_IsVizitCode = 2
				limit 1
			";
			$result = $this->db->query($query, array(
				'EvnUslugaDispDop_pid' => $data['EvnPLDispDop13_id']
			));

			if (!is_object($result)) {
				$this->db->trans_rollback();
				return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (поиск услуги)');
			}

			$response = $result->result('array');

			if (is_array($response) && count($response) > 0) {
				$uslugaData = $response[0];
			} else {
				$uslugaData = array();
			}

			$onDate = $data['EvnPLDispDop13_consDate'];
			if (!empty($data['EvnPLDispDop13_IsNewOrder']) && $data['EvnPLDispDop13_IsNewOrder'] == 2) {
				$onDate = date('Y', strtotime($data['EvnPLDispDop13_consDate'])) . '-12-31';
			}

			$resp_ps = $this->queryResult("
				select
					person_id,
					COALESCE(Sex_id, 3) as sex_id,
					dbo.Age2(Person_BirthDay, :EvnPLDispDop13_YearEndDate) as age
				from v_PersonState ps
				where ps.Person_id = :Person_id
				limit 1
			", array(
				'Person_id' => $data['Person_id'],
				'EvnPLDispDop13_YearEndDate' => mb_substr($onDate, 0, 4) . '-12-31'
			));

			if (empty($resp_ps[0]['person_id'])) {
				throw new Exception('Ошибка получения данных по пациенту');
			}

			$resp_ps[0] = $this->getAgeModification(array(
				'onDate' => $onDate
			), $resp_ps[0]);

			$queryParams = array(
				'DispClass_id' => $data['DispClass_id'],
				'EvnPLDispDop13_consDate' => $onDate,
				'EvnPLDispDop13_setDT' => $data['EvnPLDispDop13_setDT'],
				'Person_id' => $data['Person_id'],
				'sex_id' => $resp_ps[0]['sex_id'],
				'age' => $resp_ps[0]['age']
			);

			// Определяем UslugaComplex_id через $schema.UslugaSurveyLink
			$query = "
				select USL.UslugaComplex_id as \"UslugaComplex_id\"
				from {$data['session']['region']['schema']}.v_UslugaSurveyLink USL
				where
					USL.DispClass_id = :DispClass_id
					and COALESCE(USL.Sex_id, :sex_id) = :sex_id
					and :age between COALESCE(USL.UslugaSurveyLink_From, 0)
					and COALESCE(USL.UslugaSurveyLink_To, 999)
					and COALESCE(USL.UslugaSurveyLink_IsDel, 1) = 1
					and (USL.UslugaSurveyLink_begDate is null or USL.UslugaSurveyLink_begDate <= :EvnPLDispDop13_consDate)
					and (USL.UslugaSurveyLink_endDate is null or USL.UslugaSurveyLink_endDate >= :EvnPLDispDop13_consDate)
				limit 1
			";
			$result = $this->db->query($query, $queryParams);

			if (!is_object($result)) {
				$this->db->trans_rollback();
				return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (получение идентификатора)');
			}

			$response = $result->result('array');

			if (is_array($response) && count($response) > 0) {
				$UslugaComplex_id = $response[0]['UslugaComplex_id'];
			} else {
				$UslugaComplex_id = null;
			}

			// Добавляем/обновляем при необходимости
			if (!empty($UslugaComplex_id)) {
				$query = "
					select
						EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\",
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from p_EvnUslugaDispDop_" . (!empty($uslugaData['EvnUslugaDispDop_id']) ? "upd" : "ins") . " (
						EvnUslugaDispDop_id := :EvnUslugaDispDop_id,
						EvnUslugaDispDop_pid := :EvnUslugaDispDop_pid,
						UslugaComplex_id := :UslugaComplex_id,
						EvnUslugaDispDop_setDT := :EvnUslugaDispDop_setDT,
						EvnUslugaDispDop_IsVizitCode := 2,
						Lpu_id := :Lpu_id,
						Server_id := :Server_id,
						PersonEvn_id := :PersonEvn_id,
						PayType_id := :PayType_id,
						UslugaPlace_id := 1,
						pmUser_id := :pmUser_id
					)
				";
				$result = $this->db->query($query, array(
					'EvnUslugaDispDop_id' => (!empty($uslugaData['EvnUslugaDispDop_id']) ? $uslugaData['EvnUslugaDispDop_id'] : null),
					'EvnUslugaDispDop_pid' => $data['EvnPLDispDop13_id'],
					'UslugaComplex_id' => $UslugaComplex_id,
					'EvnUslugaDispDop_setDT' => (!empty($data['EvnPLDispDop13_setDT']) ? $data['EvnPLDispDop13_setDT'] : null),
					'Lpu_id' => $data['Lpu_id'],
					'Server_id' => $data['Server_id'],
					'PersonEvn_id' => $data['PersonEvn_id'],
					'PayType_id' => (!empty($uslugaData['PayType_id']) ? $uslugaData['PayType_id'] : $this->getFirstResultFromQuery("select PayType_id from v_PayType where PayType_SysNick = 'dopdisp' limit 1")),
					'pmUser_id' => $data['pmUser_id']
				));

				if (!is_object($result)) {
					$this->db->trans_rollback();
					return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление услуги) (' . __LINE__ . ')'));
				}

				$response = $result->result('array');

				if (!is_array($response) || count($response) == 0) {
					$this->db->trans_rollback();
					return array(array('Error_Msg' => 'Ошибка при удалении услуги (' . __LINE__ . ')'));
				} else if (!empty($response[0]['Error_Msg'])) {
					$this->db->trans_rollback();
					return array(array('Error_Msg' => $response[0]['Error_Msg'] . ' (' . __LINE__ . ')'));
				}
			} // Удаляем
			else if (!empty($uslugaData['EvnUslugaDispDop_id'])) {
				$query = "
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from p_EvnUslugaDispDop_del (
						EvnUslugaDispDop_id := :EvnUslugaDispDop_id,
						pmUser_id := :pmUser_id
					)
				";
				$result = $this->db->query($query, array(
					'EvnUslugaDispDop_id' => $uslugaData['EvnUslugaDispDop_id'],
					'pmUser_id' => $data['pmUser_id']
				));

				if (!is_object($result)) {
					$this->db->trans_rollback();
					return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление услуги) (' . __LINE__ . ')'));
				}

				$response = $result->result('array');

				if (!is_array($response) || count($response) == 0) {
					$this->db->trans_rollback();
					return array(array('Error_Msg' => 'Ошибка при удалении услуги (' . __LINE__ . ')'));
				} else if (!empty($response[0]['Error_Msg'])) {
					$this->db->trans_rollback();
					return array(array('Error_Msg' => $response[0]['Error_Msg'] . ' (' . __LINE__ . ')'));
				}
			}
		}

		if (getRegionNick() == 'buryatiya') {
			// получаем услугу анкетирование
			$EvnUsluga_id = $this->getFirstResultFromQuery("
				select
					EUDD.EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\"
				from
					v_EvnUslugaDispDop EUDD
					inner join v_SurveyTypeLink STL on STL.UslugaComplex_id = EUDD.UslugaComplex_id
					inner join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id and ST.SurveyType_Code = 2
				where
					EUDD.EvnUslugaDispDop_pid = :EvnPLDispDop13_id
				limit 1
			", $data);
			if (!empty($EvnUsluga_id)) {
				// сохраняем
				$inresults = array('systolic_blood_pressure', 'diastolic_blood_pressure', 'person_weight', 'person_height', 'waist_circumference', 'body_mass_index');
				$this->load->model('EvnUslugaDispDop_model');
				foreach ($inresults as $inresult) {
					if (!isset($data[$inresult]) || $data[$inresult] == '') {
						$data[$inresult] = NULL;
					}

					// получаем идентификатор EvnUslugaRate и тип сохраняемых данных
					$inresultdata = $this->EvnUslugaDispDop_model->getRateData($inresult, $EvnUsluga_id);

					if (!empty($inresultdata['RateType_id'])) {
						// если такого результата в бд ещё нет, то добавляем
						if (empty($inresultdata['EvnUslugaRate_id'])) {
							// сначала p_Rate_ins
							$sql = "
								select
									Rate_id as \"Rate_id\",
									Error_Code as \"Error_Code\",
									Error_Message as \"Error_Msg\"
								from p_Rate_ins (
									RateType_id := :RateType_id,
									Rate_ValueInt := :Rate_ValueInt,
									Rate_ValueFloat := :Rate_ValueFloat,
									Rate_ValueStr := :Rate_ValueStr,
									Rate_ValuesIs := :Rate_ValuesIs,
									Server_id := :Server_id,
									pmUser_id := :pmUser_id
								)
							";
							$queryParams = array(
								'Rate_id' => NULL,
								'RateType_id' => $inresultdata['RateType_id'],
								'Rate_ValueInt' => NULL,
								'Rate_ValueFloat' => NULL,
								'Rate_ValueStr' => NULL,
								'Rate_ValuesIs' => NULL,
								'Server_id' => $data['Server_id'],
								'pmUser_id' => $data['pmUser_id']
							);

							switch ($inresultdata['RateValueType_SysNick']) {
								case 'int':
									$queryParams['Rate_ValueInt'] = $data[$inresult];
									break;
								case 'float':
									$queryParams['Rate_ValueFloat'] = $data[$inresult];
									break;
								case 'string':
									$queryParams['Rate_ValueStr'] = $data[$inresult];
									break;
								case 'reference':
									$queryParams['Rate_ValuesIs'] = $data[$inresult];
									break;
							}

							$res = $this->db->query($sql, $queryParams);

							if (!is_object($res)) {
								$this->db->trans_rollback();
								return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение показателя услуги)'));
							}

							$resprate = $res->result('array');

							if (!is_array($resprate) || count($resprate) == 0) {
								$this->db->trans_rollback();
								return array(array('Error_Msg' => 'Ошибка при сохранении показателя услуги'));
							} else if (!empty($resprate[0]['Error_Msg'])) {
								$this->db->trans_rollback();
								return $resprate;
							}

							// затем p_EvnUslugaRate_ins
							$sql = "
								select
									EvnUslugaRate_id as \"EvnUslugaRate_id\",
									Error_Code as \"Error_Code\",
									Error_Message as \"Error_Msg\"
								from p_EvnUslugaRate_ins (
									EvnUsluga_id := :EvnUsluga_id,
									Rate_id := :Rate_id,
									Server_id := :Server_id,
									pmUser_id := :pmUser_id
								)
							";

							$queryParams = array(
								'EvnUslugaRate_id' => NULL,
								'EvnUsluga_id' => $EvnUsluga_id,
								'Rate_id' => $resprate[0]['Rate_id'],
								'Server_id' => $data['Server_id'],
								'pmUser_id' => $data['pmUser_id']
							);

							$res = $this->db->query($sql, $queryParams);

							if (!is_object($res)) {
								$this->db->trans_rollback();
								return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение показателя услуги)'));
							}

							$resp = $res->result('array');

							if (!is_array($resp) || count($resp) == 0) {
								$this->db->trans_rollback();
								return array(array('Error_Msg' => 'Ошибка при сохранении показателя услуги'));
							} else if (!empty($resp[0]['Error_Msg'])) {
								$this->db->trans_rollback();
								return $resp;
							}
						} // иначе обновляем тот, что есть
						else {
							// p_Rate_upd
							$sql = "
								select
									Rate_id as \"Rate_id\",
									Error_Code as \"Error_Code\",
									Error_Message as \"Error_Msg\"
								from p_Rate_upd (
									Rate_id := :Rate_id,
									RateType_id := :RateType_id,
									Rate_ValueInt := :Rate_ValueInt,
									Rate_ValueFloat := :Rate_ValueFloat,
									Rate_ValueStr := :Rate_ValueStr,
									Rate_ValuesIs := :Rate_ValuesIs,
									Server_id := :Server_id,
									pmUser_id := :pmUser_id
								)
							";
							$queryParams = array(
								'Rate_id' => $inresultdata['Rate_id'],
								'RateType_id' => $inresultdata['RateType_id'],
								'Rate_ValueInt' => NULL,
								'Rate_ValueFloat' => NULL,
								'Rate_ValueStr' => NULL,
								'Rate_ValuesIs' => NULL,
								'Server_id' => $data['Server_id'],
								'pmUser_id' => $data['pmUser_id']
							);

							switch ($inresultdata['RateValueType_SysNick']) {
								case 'int':
									$queryParams['Rate_ValueInt'] = $data[$inresult];
									break;
								case 'float':
									$queryParams['Rate_ValueFloat'] = $data[$inresult];
									break;
								case 'string':
									$queryParams['Rate_ValueStr'] = $data[$inresult];
									break;
								case 'reference':
									$queryParams['Rate_ValuesIs'] = $data[$inresult];
									break;
							}

							$res = $this->db->query($sql, $queryParams);

							if (!is_object($res)) {
								$this->db->trans_rollback();
								return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (обновление показателя услуги)'));
							}

							$resp = $res->result('array');

							if (!is_array($resp) || count($resp) == 0) {
								$this->db->trans_rollback();
								return array(array('Error_Msg' => 'Ошибка при обновлении показателя услуги'));
							} else if (!empty($resp[0]['Error_Msg'])) {
								$this->db->trans_rollback();
								return $resp;
							}
						}
					}
				}
			}
		}

		// Обработка данных из списка назначений
		if ( !empty($data['DispAppointData']) && is_array($data['DispAppointData']) && count($data['DispAppointData']) > 0 ) {
			// Если группа здоровья не указана или одна из I, II, то все записи помечаем на удаление
			// Исключаем Крым, задача #149473
			if ( $this->regionNick != 'krym' && (empty($data['HealthKind_id']) || in_array($data['HealthKind_id'], array(1, 2))) ) {
				foreach ( $data['DispAppointData'] as $key => $record ) {
					$data['DispAppointData'][$key]['RecordStatus_Code'] = 3;
				}
			}

			$this->load->model('DispAppoint_model');

			// Сперва удаляем все записи помеченные на удаление
			foreach ( $data['DispAppointData'] as $record ) {
				if ( $record['RecordStatus_Code'] == 3 ) {
					$resTmp = $this->DispAppoint_model->deleteDispAppoint($record);

					if ( $resTmp === false || !is_array($resTmp) ) {
						$this->db->trans_rollback();
						return array('Error_Msg' => 'Ошибка при удалении назначения из БД');
					}
					else if ( !empty($resTmp[0]['Error_Msg']) ) {
						$this->db->trans_rollback();
						return array('Error_Msg' => $resTmp[0]['Error_Msg']);
					}
				}
			}

			// Затем добавляем/обновляем записи
			foreach ( $data['DispAppointData'] as $record ) {
				if ( in_array($record['RecordStatus_Code'], array(0, 2)) ) {
					$record['EvnPLDisp_id'] = $data['EvnPLDispDop13_id'];
					$record['pmUser_id'] = $data['pmUser_id'];
					$record['mode'] = 'local';

					$resTmp = $this->DispAppoint_model->saveDispAppoint($record);

					if ( $resTmp === false || !is_array($resTmp) ) {
						$this->db->trans_rollback();
						return array('Error_Msg' => 'Ошибка при сохранении назначения в БД');
					}
					else if ( !empty($resTmp[0]['Error_Msg']) ) {
						$this->db->trans_rollback();
						return array('Error_Msg' => $resTmp[0]['Error_Msg']);
					}
				}
			}
		}

		$justClosed = (
			!empty($data['EvnPLDispDop13_IsEndStage']) && $data['EvnPLDispDop13_IsEndStage'] == 2 && (
				empty($savedData) || $savedData['EvnPLDispDop13_IsEndStage'] != 2
			)
		);

		if (getRegionNick() == 'penza' && (empty($savedData) || $justClosed)) {
			//Отправить человека в очередь на идентификацию
			$this->load->model('Person_model', 'pmodel');
			$this->pmodel->isAllowTransaction = false;
			$resTmp = $this->pmodel->addPersonRequestData(array(
				'Person_id' => $data['Person_id'],
				'Evn_id' => $data['EvnPLDispDop13_id'],
				'pmUser_id' => $data['pmUser_id'],
				'PersonRequestSourceType_id' => 3,
			));
			$this->pmodel->isAllowTransaction = true;
			if (!$this->isSuccessful($resTmp) && !in_array($resTmp[0]['Error_Code'], array(302, 303))) {
				$this->db->trans_rollback();
				return array('Error_Msg' => $resTmp[0]['Error_Msg']);
			}
		}

		if (in_array($this->regionNick, ['perm', 'msk']) && !empty($data['Diag_spid'])) {
			$this->id = $data['EvnPLDispDop13_id'];
			$this->evnClassId = 101;
			$this->Person_id = $data['Person_id'];
			$this->Diag_spid = $data['Diag_spid'];
			$this->sessionParams = $data['session'];

			$this->load->model('MorbusOnkoSpecifics_model');
			$this->MorbusOnkoSpecifics_model->checkAndCreateSpecifics($this);
		}

		$this->db->trans_commit();

		return $resp;
    }
	
	
	/**
	 * Получение списка лет, в которые выписывались талоны по ДД с количеством талонов, для комбобокса
	 */
	function getEvnPLDispDop13Years($data)
    {


  		$sql = "
			select
	            count(EPLDD13.EvnPLDispDop13_id) as count,
	            date_part('year', EPLDD13.EvnPLDispDop13_setDate) as \"EvnPLDispDop13_Year\"
	        from
				-- from
                v_PersonState PS
                inner join v_EvnPLDispDop13 EPLDD13 on PS.Person_id = EPLDD13.Person_id and EPLDD13.Lpu_id = :Lpu_id and EPLDD13.DispClass_id IN (1)
				-- end from
			where
				-- where
				EPLDD13.EvnPLDispDop13_setDate >= cast('2013-01-01' as timestamp)
				and EPLDD13.EvnPLDispDop13_setDate <= cast('2013-12-31' as timestamp)
				and exists (
					select
						personcard_id
					from v_PersonCard PC
						left join v_LpuRegion LR on LR.LpuRegion_id = PC.LpuRegion_id
					WHERE PC.Person_id = PS.Person_id
						and PC.Lpu_id = :Lpu_id
					limit 1
				)
				-- end where
			GROUP BY
				date_part('year', EPLDD13.EvnPLDispDop13_setDate)
			ORDER BY
				date_part('year', EPLDD13.EvnPLDispDop13_setDate)
		";

        //echo getDebugSQL($sql, $data); die;
		$res = $this->db->query($sql, $data);
		if ( is_object($res) )
 	    	return $res->result('array');
 	    else
 	    	return false;
    }

	/**
	 * Получение списка лет, в которые выписывались талоны по ДД с количеством талонов, диспансеризация второй этап
	 */
	function getEvnPLDispDop13YearsSec($data)
    {
		$addfilter = "";
		$maxage = 999;
		$personPrivilegeCodeList = $this->getPersonPrivilegeCodeList();

		if ( count($personPrivilegeCodeList) > 0 ) {
			$addfilter .= "
				or (
					(dbo.Age2(PS.Person_BirthDay, cast(cast(date_part('year', EPLDD13.EvnPLDispDop13_setDate) as varchar) || '-12-31' as date)) BETWEEN 18 AND {$maxage})
					and exists (
						select pp.PersonPrivilege_id
						from v_PersonPrivilege pp
							inner join v_PrivilegeType pt on pt.PrivilegeType_id = pp.PrivilegeType_id
						where pt.PrivilegeType_Code in ('" . implode("','", $personPrivilegeCodeList) . "')
							and pp.Person_id = PS.Person_id
							and pp.PersonPrivilege_begDate <= cast(cast(date_part('year', EPLDD13.EvnPLDispDop13_setDate) as varchar) || '-12-31' as date)
							and (pp.PersonPrivilege_endDate >= cast(cast(date_part('year', EPLDD13.EvnPLDispDop13_setDate) as varchar) || '-12-31' as date) or pp.PersonPrivilege_endDate is null)
						limit 1
					)
				) -- refs #23044
			";
		}
		else if ( in_array($this->regionNick, array('ufa', 'ekb', 'kareliya', 'krasnoyarsk', 'penza', 'astra')) ) {
			$addfilter .= "
				or (
					(dbo.Age2(PS.Person_BirthDay, cast(cast(date_part('year', EPLDD13.EvnPLDispDop13_setDate) as varchar) || '-12-31' as date)) BETWEEN 18 AND {$maxage})
					and exists (
						select PersonPrivilegeWOW_id
						from v_PersonPrivilegeWOW
						where Person_id = PS.Person_id
						limit 1
					)
				)
			";
		}

  		$sql = "
			select
	            count(EPLDD13.EvnPLDispDop13_id) as count,
	            date_part('year', EPLDD13.EvnPLDispDop13_setDate) as \"EvnPLDispDop13_Year\"
	        from
		        v_PersonState PS
		        left join v_EvnPLDispDop13 EPLDD13 on PS.Person_id = EPLDD13.Person_id and EPLDD13.Lpu_id = :Lpu_id and COALESCE(DispClass_id,1) = 1 and date_part('year', EvnPLDispDop13_setDate) = date_part('year', EPLDD13.EvnPLDispDop13_setDate)
	        where
	            COALESCE(EPLDD13.EvnPLDispDop13_IsTwoStage, 1) = '2'
            	and (
					(dbo.Age2(PS.Person_BirthDay, cast(cast(date_part('year', EPLDD13.EvnPLDispDop13_setDate) as varchar) || '-12-31' as date)) - 21 >= 0 and (dbo.Age2(PS.Person_BirthDay, cast(cast(date_part('year', EPLDD13.EvnPLDispDop13_setDate) as varchar) || '-12-31' as date)) - 21) % 3 = 0)
					{$addfilter}
				)
				and dbo.Age2(PS.Person_BirthDay, cast(cast(date_part('year', EPLDD13.EvnPLDispDop13_setDate) as varchar) || '-12-31' as date)) <= {$maxage}
				and exists (
					select
						personcard_id
					from v_PersonCard PC
						left join v_LpuRegion LR on LR.LpuRegion_id = PC.LpuRegion_id
					WHERE PC.Person_id = PS.Person_id
						and (PC.PersonCard_endDate is null or cast(PC.PersonCard_endDate as timestamp) > dbo.tzGetDate())
						and PC.Lpu_id = :Lpu_id
					limit 1
					)
				and date_part('year', EPLDD13.EvnPLDispDop13_setDate) >= 2013
				and EPLDD13.EvnPLDispDop13_setDate is not null
			GROUP BY
				date_part('year', EPLDD13.EvnPLDispDop13_setDate)
			ORDER BY
				date_part('year', EPLDD13.EvnPLDispDop13_setDate)
		";

        //echo getDebugSQL($sql, array($data['Lpu_id'])); die;
		$res = $this->db->query($sql, $data);
		if ( is_object($res) )
 	    	return $res->result('array');
 	    else
 	    	return false;
    }

	/**
	 * Получение списка лет, в которые выписывались талоны по ДД с количеством талонов, профосмотры
	 */
	function getEvnPLDispProfYears($data)
    {
		$addfilter = "";
		$maxage = 999;
		$personPrivilegeCodeList = $this->getPersonPrivilegeCodeList();

		if ( count($personPrivilegeCodeList) > 0 ) {
			$addfilter .= "
				or (
					(dbo.Age2(PS.Person_BirthDay, cast(cast(date_part('year', EPLDD13.EvnPLDispDop13_setDate) as varchar) || '-12-31' as date)) BETWEEN 18 AND {$maxage})
					and exists (
						select pp.PersonPrivilege_id
						from v_PersonPrivilege pp
							inner join v_PrivilegeType pt on pt.PrivilegeType_id = pp.PrivilegeType_id
						where pt.PrivilegeType_Code in ('" . implode("','", $personPrivilegeCodeList) . "')
							and pp.Person_id = PS.Person_id
							and pp.PersonPrivilege_begDate <= cast(date_part('year', EPLDD13.EvnPLDispDop13_setDate) as varchar) || '-12-31'
							and (pp.PersonPrivilege_endDate >= cast(date_part('year', EPLDD13.EvnPLDispDop13_setDate) as varchar) || '-12-31' or pp.PersonPrivilege_endDate is null)
						limit 1
					)
				) -- refs #23044
			";
		}
		else if ( in_array($this->regionNick, array('ufa', 'ekb', 'kareliya', 'krasnoyarsk', 'penza', 'astra')) ) {
			$addfilter .= "
				or (
					(dbo.Age2(PS.Person_BirthDay, cast(date_part('year', EPLDD13.EvnPLDispDop13_setDate) as varchar) || '-12-31') BETWEEN 18 AND {$maxage})
					and exists (
						select PersonPrivilegeWOW_id
						from v_PersonPrivilegeWOW
						where Person_id = PS.Person_id
						limit 1
					)
				)
			";
		}

  		$sql = "
			select
	            count(EPLDD13.EvnPLDispDop13_id) as count,
	            date_part('year', EPLDD13.EvnPLDispDop13_setDate) as \"EvnPLDispDop13_Year\"
	        from
		        v_PersonState PS
		        left join v_EvnPLDispDop13 EPLDD13 on PS.Person_id = EPLDD13.Person_id and EPLDD13.Lpu_id = :Lpu_id and COALESCE(DispClass_id,1) = 1 and date_part('year', EvnPLDispDop13_setDate) = date_part('year', EPLDD13.EvnPLDispDop13_setDate)
	        where
	            COALESCE(EPLDD13.EvnPLDispDop13_IsTwoStage, 1) = '2'
            	and (
					(dbo.Age2(PS.Person_BirthDay, cast(cast(date_part('year', EPLDD13.EvnPLDispDop13_setDate) as varchar) || '-12-31' as date)) - inteval '21 days' >= 0 and (dbo.Age2(PS.Person_BirthDay, cast(cast(date_part('year', EPLDD13.EvnPLDispDop13_setDate) as varchar) || '-12-31' as date)) - interval '21 days') % 3 = 0)
					{$addfilter}
				)
				and dbo.Age2(PS.Person_BirthDay, cast(cast(date_part('year', EPLDD13.EvnPLDispDop13_setDate) as varchar) || '-12-31' as date)) <= {$maxage}
				and exists (
					select
						personcard_id
					from v_PersonCard PC
						left join v_LpuRegion LR on LR.LpuRegion_id = PC.LpuRegion_id
					WHERE PC.Person_id = PS.Person_id
						and (PC.PersonCard_endDate is null or cast(PC.PersonCard_endDate as timestamp) > dbo.tzGetDate())
						and PC.Lpu_id = :Lpu_id
					limit 1
					)
				and date_part('year', EPLDD13.EvnPLDispDop13_setDate) >= 2013
				and EPLDD13.EvnPLDispDop13_setDate is not null
			GROUP BY
				date_part('year', EPLDD13.EvnPLDispDop13_setDate)
			ORDER BY
				date_part('year', EPLDD13.EvnPLDispDop13_setDate)
		";

        //echo getDebugSQL($sql, array($data['Lpu_id'])); die;
		$res = $this->db->query($sql, $data);
		if ( is_object($res) )
 	    	return $res->result('array');
 	    else
 	    	return false;
    }

	/**
	 * Проверка, есть ли талон на этого человека в этом году
	 */
	function checkIfEvnPLDispDop13Exists($data)
    {
  		$sql = "
			SELECT
				count(EvnPLDispDop13_id) as count
			FROM
				v_EvnPLDispDop13
			WHERE
				Person_id = ? and Lpu_id = ? and date_part('year', EvnPLDispDop13_setDate) = date_part('year', dbo.tzGetDate())
		";

		$res = $this->db->query($sql, array($data['Person_id'], $data['Lpu_id']));
		if ( is_object($res) )
		{
 	    	$sel = $res->result('array');
			if ( $sel[0]['count'] == 0 )
				return array(array('isEvnPLDispDop13Exists' => false, 'Error_Msg' => ''));
			else
				return array(array('isEvnPLDispDop13Exists' => true, 'Error_Msg' => ''));
		}
 	    else
 	    	return false;
    }
	
	
	/**
	 * Данные человека по талону
	 */
	function getEvnPLDispDop13PassportFields($data) {
		$dt = array();
		$person_id = 0;
		
  		$sql = "
			SELECT 
				dd.EvnPLDispDop13_setDT as \"EvnPLDispDop13_setDT\",
				case when EvnPLDispDop13_setDate >= cast('2015-04-01' as date) then 1 else 0 end as is_new_event,
				dd.Person_id as \"Person_id\",
				ps.Person_FirName as \"Person_FirName\",
				ps.Person_SecName as \"Person_SecName\",
				ps.Person_SurName as \"Person_SurName\",
				ps.Person_Phone as \"Person_Phone\",
				ps.Sex_id as \"Sex_id\",
				to_char(ps.Person_BirthDay, 'DD') as \"Person_BirthDay_Day\",
				to_char(ps.Person_BirthDay, 'MM') as \"Person_BirthDay_Month\",
				to_char(ps.Person_BirthDay, 'YYYY') as \"Person_BirthDay_Year\",
				ua.Address_House as \"Address_House\",
				ua.Address_Corpus as \"Address_Corpus\",
				ua.Address_Flat as \"Address_Flat\",
				ua.KLStreet_Name as \"KLStreet_Name\",
				(
						ua.KLRGN_Name || ' ' || ua.KLRGN_Socr
						|| COALESCE(', ' || ua.KLCity_Socr || ' ' || ua.KLCity_Name,'')
						|| COALESCE(', ' || ua.KLTown_Socr || ' ' || ua.KLTown_Name,'')
				) as \"Address_Info\",
				l.Lpu_Name as \"Lpu_Name\",
				l.Org_Phone as \"Org_Phone\",
				l2.PAddress_Address as l_address,
				pc.PersonCard_Code as \"PersonCard_Code\",
				pc.LpuRegion_Name as \"LpuRegion_Name\",
				mp.Person_Fio as \"MedPerson_Fio\",
				case when pls.PolisType_id = 4 then '' else pls.Polis_Ser end as \"Polis_Ser\",
				case when pls.PolisType_id = 4 then ps.Person_EdNum else pls.Polis_Num end as \"Polis_Num\",
				OS.OrgSmo_Name as polis_orgnick,
				ps.Person_Snils as \"Person_Snils\",
				dd.EvnPLDispDop13_IsSmoking as \"IsSmoking\",
				dd.EvnPLDispDop13_IsRiskAlco as \"IsRiskAlco\",
				dd.EvnPLDispDop13_IsLowActiv as \"IsLowActiv\",
				dd.EvnPLDispDop13_IsIrrational as \"IsIrrational\"
			FROM 
				v_EvnPLDispDop13 dd
				inner join v_PersonState ps on ps.Person_id = dd.Person_id
				left join v_Address_all ua on ua.Address_id = ps.UAddress_id
				left join v_Lpu_all l on l.Lpu_id = dd.Lpu_id
				left join v_Lpu l2 on l2.Lpu_id = dd.Lpu_id
				left join v_PersonCard pc on (PC.Person_id = ps.Person_id and pc.LpuAttachType_id = 1)
				left join v_MedStaffRegion msr on msr.LpuRegion_id = pc.LpuRegion_id
				left join v_MedPersonal mp on mp.MedPersonal_id = msr.MedPersonal_id and mp.Dolgnost_Code = '73' and mp.WorkType_id = '1'
				left join v_Polis pls on pls.Polis_id = ps.Polis_id
				left join v_OrgSmo OS on OS.OrgSmo_id = pls.OrgSmo_id
			where
				EvnPLDispDop13_id = :EvnPLDispDop13_id
		";

		$res = $this->db->query($sql, array('EvnPLDispDop13_id' => $data['EvnPLDispDop13_id']));
		if (is_object($res)) {
 	    	$res = $res->result('array');
			$dt = array_merge($dt, $res[0]);
			if (isset($res[0]['Person_id']) && $res[0]['Person_id'] != '')
				$person_id = $res[0]['Person_id'];
		}

		$sql = "
			select
				--EPDD.EvnPLDispDop13_IsFinish as \"EvnPLDispDop13_IsFinish\",
				EPDD.EvnPLDispDop13_IsEndStage as \"EvnPLDispDop13_IsEndStage\",
				HK.HealthKind_Name as \"value\",
				to_char(EVDD.EvnVizitDispDop_setDate, 'DD.MM.YYYY') as \"date\",
				to_char(EVDD.EvnVizitDispDop_setDate, 'YYYY') as \"year\"
			from v_EvnPLDispDop13 EPDD
				inner join v_EvnVizitDispDop EVDD on EVDD.EvnVizitDispDop_pid = EPDD.EvnPLDispDop13_id
				left join DopDispSpec DDS on DDS.DopDispSpec_id = EVDD.DopDispSpec_id
				left join HealthKind HK on HK.HealthKind_id = EPDD.HealthKind_id
			where
				EPDD.Person_id = '".$person_id."'
				and EPDD.EvnPLDispDop13_IsEndStage = 2
				ORDER BY year, date desc 
		";
		$res = $this->db->query($sql, array('Person_id' => $person_id));
		$dt['health_groups'] = array();
		if (is_object($res)) {
 	    	$res = $res->result('array');
			$groups = array();
			foreach($res as $row) {
				$year = $row['year'];				
				$groups[$year] = array('date' => $row['date'], 'value' => $row['value']);				
			}			
			$dt['health_groups'] = $groups;
		}

		//Отдельно получим дату осмотра терапевта
		$sql = "
			select	distinct
				st.SurveyType_Code as \"SurveyType_Code\",
				to_char(EUDD.EvnUslugaDispDop_didDate, 'DD.MM.YYYY') as \"EvnUslugaDispDop_didDate\",
				(/*mp.Dolgnost_Name*/ps.PostMed_Name || '<br>' || mp.Person_Fio) as \"Med_Personal\"
			from v_EvnUslugaDispDop EUDD
				left join v_EvnVizitDispDop evdd on eudd.EvnUslugaDispDop_pid = evdd.EvnVizitDispDop_id
				left join v_MedPersonal mp on mp.MedPersonal_id = evdd.MedPersonal_id and EUDD.Lpu_id=mp.Lpu_id
				left join v_MedStaffFact msf on msf.LpuSection_id = EUDD.LpuSection_uid and msf.MedPersonal_id = mp.MedPersonal_id and msf.Lpu_id = mp.Lpu_id
				left join v_PostMed ps on ps.PostMed_id=msf.Post_id
				left join v_DopDispInfoConsent ddic on evdd.DopDispInfoConsent_id = ddic.DopDispInfoConsent_id
				left join v_SurveyTypeLink stl on stl.SurveyTypeLink_id = ddic.SurveyTypeLink_id
				left join v_SurveyType st on st.SurveyType_id = stl.SurveyType_id
			where
				EUDD.EvnUslugaDispDop_rid = :EvnPLDispDop13_id
				and st.SurveyType_Code = '19'
				and COALESCE(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
		";
		//$res = $this->db->query($sql, array('Person_id' => $person_id));
		$res = $this->db->query($sql, array('EvnPLDispDop13_id' => $data['EvnPLDispDop13_id']));
		if(is_object($res)){
			$res = $res->result('array');
			if(count($res)>0){
				$dt['diddate_19'] = $res[0]['EvnUslugaDispDop_didDate'];
				$dt['Med_Personal'] = $res[0]['Med_Personal'];
			}
		}

		//Установленные заболевания
		//shorev, 23.10.2013 - закомментил этот запрос (удалять не стал, а то мало ли), т.к. в рамках задачи https://redmine.swan.perm.ru/issues/26202 нужно брать из другого места.
		/*$sql = "
				select       --в рамках осмотра по дд
					to_char(EUDDData.EvnUslugaDispDop_didDate, 'DD.MM.YYYY') as \"Diag_date\",
					EUDDData.Diag_Name as \"Diag_Name\",
					EUDDData.Diag_Code as \"Diag_Code\"
				from v_DopDispInfoConsent DDIC
					left join v_SurveyTypeLink STL on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
					left join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
					LEFT JOIN LATERAL (
						select
							EUDD.EvnUslugaDispDop_id,
							EUDD.EvnUslugaDispDop_pid,
							EUDD.EvnUslugaDispDop_didDate,
							D.Diag_Code,
							D.Diag_Name
						from v_EvnUslugaDispDop EUDD
							left join v_EvnVizitDispDop EVDD on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
							inner join v_Diag D on D.Diag_id = EVDD.Diag_id
						where
							EVDD.EvnVizitDispDop_pid = :EvnPLDispDop13_id
							and EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
							and COALESCE(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
						limit 1
					) EUDDData on true
				where DDIC.EvnPLDisp_id = :EvnPLDispDop13_id
					and (DDIC.DopDispInfoConsent_IsAgree = 2 OR DDIC.DopDispInfoConsent_IsEarlier = 2)
					and ST.SurveyType_Code NOT IN (2)
					and (COALESCE(STL.SurveyTypeLink_IsDel, 1) = 1 or EUDDData.EvnUslugaDispDop_id is not null ) -- только если сохранено согласие
					and EUDDData.Diag_Code not ilike 'Z%'

			union

				select          --диспучет
					to_char(PD.PersonDisp_begDate, 'DD.MM.YYYY') as \"Diag_date\",
					D.Diag_Name as \"Diag_Name\",
					D.Diag_Code as \"Diag_Code\"
				from v_PersonDisp PD
					left join v_Diag D on D.Diag_id = PD.Diag_id
				where PD.Person_id = :Person_id
					and PD.PersonDisp_endDate is null

			order by Diag_date

		";
		*/
		$query = "
			--Ранее известные и впервые выявленные заболевания
			select
				to_char(EDDD.EvnDiagDopDisp_setDate, 'DD.MM.YYYY') as \"Diag_date\",
				D.Diag_Name as \"Diag_Name\",
				D.Diag_Code as \"Diag_Code\"
			from
				v_EvnDiagDopDisp EDDD
				left join v_DiagSetClass DSC on DSC.DiagSetClass_id = EDDD.DiagSetClass_id
				left join v_Diag D on D.Diag_id = EDDD.Diag_id
			where
				EDDD.EvnDiagDopDisp_pid = :EvnPLDispDop13_id
				AND D.Diag_Code not ilike 'Z%'
			union
			--Подозрение на наличие стенокардии
			select
				null as \"Diag_date\",
				'Подозрение на наличие стенокардии напряжения' as \"Diag_Name\",
				null as \"Diag_Code\"
			from v_EvnPLDispDop13 EPLDD
			where EPLDD.EvnPLDispDop13_id = :EvnPLDispDop13_id
			and EPLDD.EvnPLDispDop13_IsStenocard=2
			union
			--Подозрение на наличие туберкулеза
			select
				null as \"Diag_date\",
				'Подозрение на наличие туберкулеза, хронического заболевания легких или новообразования легких' as \"Diag_Name\",
				null as \"Diag_Code\"
			from v_EvnPLDispDop13 EPLDD
			where EPLDD.EvnPLDispDop13_id = :EvnPLDispDop13_id
			and EPLDD.EvnPLDispDop13_IsTub=2
		";
		$res = $this->db->query($query, array('EvnPLDispDop13_id' => $data['EvnPLDispDop13_id']));
		//$res = $this->db->query($sql,array('EvnPLDispDop13_id' => $data['EvnPLDispDop13_id'], 'Person_id' => $person_id));
		$dt['diags'] = array();
		if(is_object($res)){
			$dt['diags'] = $res->result('array');
		}
 	    return $dt;
    }

	/**
	 * @param $RateType_SysNick
	 * @param $EvnUslugaDispDop13_id
	 * @return string
	 */
	function getRiskRateValue($RateType_SysNick, $EvnUslugaDispDop13_id)
	{
		$params = array();
		$params['RateType_SysNick'] = $RateType_SysNick;
		$params['EvnPLDispDop13_id'] = $EvnUslugaDispDop13_id;
		$join = '';
		$and = '';
		$rate_value = '';
		if($RateType_SysNick == 'glucose')
		{
			$join = 'left join v_UslugaComplex UC on UC.UslugaComplex_id = EUDD.UslugaComplex_id';
			$and = "and UC.UslugaComplex_Name not ilike '%мочи%'";
		}

		$query_riskrate_value = "
			select
				COALESCE(CAST(R.Rate_ValueInt as varchar),CAST(R.Rate_ValueStr as varchar),CAST(R.Rate_ValueFloat as varchar),'') as rate_value
				from v_EvnUslugaDispDop EUDD
				left join v_EvnUslugaRate EUR on EUR.EvnUsluga_id = EUDD.EvnUslugaDispDop_id
				{$join}
				left join v_Rate R on R.Rate_id = EUR.Rate_id
				left join v_RateType RT on RT.RateType_id = R.RateType_id
				where EUDD.EvnUslugaDispDop_rid = :EvnPLDispDop13_id
				and RT.RateType_SysNick = :RateType_SysNick
				and COALESCE(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
				{$and}
			limit 1
		";
		$result_riskrate_value = $this->db->query($query_riskrate_value,$params);
		if(is_object($result_riskrate_value)){
			$result_riskrate_value = $result_riskrate_value->result('array');
			if(count($result_riskrate_value)>0)
			{
				$rate_value = trim($result_riskrate_value[0]['rate_value']);
			}
		}
		return $rate_value;
	}

	/**
	 * Получение данных для пункат "Факторы риска" паспорта здоровья
	 */
	function getRiskFactorsForPassport($data)
	{
		$dt = array();
		$params = array();
		$params['Person_id'] = $data['Person_id'];

		$query_epldd = "
			select
				EPLDD.EvnPLDispDop13_id as \"EvnPLDispDop13_id\",
				to_char(EPLDD.EvnPLDispDop13_setDate, 'DD.MM.YYYY') as \"dd_date\",
				case when EPLDD.EvnPLDispDop13_IsSmoking = 2 then 'Да' else 'Нет' end as \"IsSmoking\",
				case when EPLDD.EvnPLDispDop13_IsRiskAlco = 2 then 'Да' else 'Нет' end as \"IsRiskAlco\",
				case when EPLDD.EvnPLDispDop13_IsLowActiv = 2 then 'Да' else 'Нет' end as \"IsLowActiv\",
				case when EPLDD.EvnPLDispDop13_IsIrrational = 2 then 'Да' else 'Нет' end as \"IsIrrational\",
				COALESCE(HK.HealthKind_Name,'') as \"HealthKind_Name\",
				COALESCE(EPLDD.EvnPLDispDop13_SumRick,null) as \"EvnPLDispDop13_SumRick\",
				COALESCE(RT.RiskType_Name,'') as \"RiskType_Name\"

			from v_EvnPLDispDop13 EPLDD
			left join HealthKind HK on HK.HealthKind_id = EPLDD.HealthKind_id
			left join RiskType RT on RT.RiskType_id=EPLDD.RiskType_id
			where EPLDD.Person_id = :Person_id
			order by EPLDD.EvnPLDispDop13_setDate desc
			limit 5
		";
		$result_epldd = $this->db->query($query_epldd,$params);
		if(is_object($result_epldd))
		{
			$result_epldd = $result_epldd->result('array');
			if(count($result_epldd) > 0){
				for($i=0; $i < count($result_epldd); $i++){
					$dt[$i] = $result_epldd[$i];
					$EvnPLDispDop13_id = $result_epldd[$i]['EvnPLDispDop13_id'];
					$dt[$i]['systolic_blood_pressure'] 	= (float)$this->getRiskRateValue('systolic_blood_pressure',	$EvnPLDispDop13_id);
					$dt[$i]['diastolic_blood_pressure'] = (float)$this->getRiskRateValue('diastolic_blood_pressure',	$EvnPLDispDop13_id);
					$dt[$i]['person_pressure'] = '';
					if($dt[$i]['systolic_blood_pressure']!='' && $dt[$i]['diastolic_blood_pressure']!='')
						$dt[$i]['person_pressure'] = $dt[$i]['systolic_blood_pressure'].'/'.$dt[$i]['diastolic_blood_pressure'];
					$dt[$i]['person_weight'] 			= $this->getRiskRateValue('person_weight',				$EvnPLDispDop13_id);
					$dt[$i]['person_height'] 			= $this->getRiskRateValue('person_height',				$EvnPLDispDop13_id);
					$dt[$i]['body_mass_index'] 			= $this->getRiskRateValue('body_mass_index',			$EvnPLDispDop13_id);
					$dt[$i]['glucose'] 					= (float)$this->getRiskRateValue('glucose',					$EvnPLDispDop13_id);
					$dt[$i]['total_cholesterol'] 		= (float)$this->getRiskRateValue('total_cholesterol',			$EvnPLDispDop13_id);
					$dt[$i]['risk_narco'] = 'Нет';
					$query_risk_narco = "
						select COUNT(DDQ.DopDispQuestion_IsTrue) as \"DDQ_Count\"
						from v_DopDispQuestion DDQ
						left join v_QuestionType QT on QT.QuestionType_id = DDQ.QuestionType_id
						where EvnPLDisp_id = :EvnPLDispDop13_id
						and QT.QuestionType_Code in (40,41,42,43,44)
						and DDQ.DopDispQuestion_IsTrue = 2
					";
					$result_risk_narco = $this->db->query($query_risk_narco,array('EvnPLDispDop13_id' => $EvnPLDispDop13_id));
					if(is_object($result_risk_narco)){
						$result_risk_narco = $result_risk_narco->result('array');
						if(count($result_risk_narco)>0) {
							$dt[$i]['risk_narco'] = ($result_risk_narco[0]['DDQ_Count'] > 0)?'Да':'Нет';
						}
					}

					//$dt[$i]['summ_risk'] = '';
					$dt[$i]['summ_risk'] = $result_epldd[$i]['EvnPLDispDop13_SumRick'] . '; ' . $result_epldd[$i]['RiskType_Name'];

					$dt[$i]['dd_medpersonal'] = '';
					$query_dd_medpersonal = "
						select	distinct
							st.SurveyType_Code as \"SurveyType_Code\",
							(COALESCE(ps.PostMed_Name,'') || ' ' || COALESCE(msf.Person_Fio,'')) as \"Med_Personal\"
						from v_EvnUslugaDispDop EUDD
							left join v_EvnVizitDispDop evdd on eudd.EvnUslugaDispDop_pid = evdd.EvnVizitDispDop_id
							left join v_MedStaffFact msf on msf.MedStaffFact_id = EUDD.MedStaffFact_id
							left join v_PostMed ps on ps.PostMed_id=msf.Post_id
							left join v_DopDispInfoConsent ddic on evdd.DopDispInfoConsent_id = ddic.DopDispInfoConsent_id
							left join v_SurveyTypeLink stl on stl.SurveyTypeLink_id = ddic.SurveyTypeLink_id
							left join v_SurveyType st on st.SurveyType_id = stl.SurveyType_id
						where
							EUDD.EvnUslugaDispDop_rid = :EvnPLDispDop13_id
							and st.SurveyType_Code = '19'
							and COALESCE(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
					";
					$result_dd_medpersona = $this->db->query($query_dd_medpersonal,array('EvnPLDispDop13_id' => $EvnPLDispDop13_id));
					if(is_object($result_dd_medpersona)){
						$result_dd_medpersona = $result_dd_medpersona->result('array');
						if(count($result_dd_medpersona)>0){
							$dt[$i]['dd_medpersonal'] = $result_dd_medpersona[0]['SurveyType_Code'] .'-' . $result_dd_medpersona[0]['Med_Personal'];
						}
					}

					//Найдем отдельно данные по отягощенный наследственности
					$dt[$i]['her_diag'] = '';
					$evnpldispdop_id = $dt[$i]['EvnPLDispDop13_id'];
					$sql = "
						select
							D.Diag_Code as \"Diag_Code\",
							D.Diag_Name as \"Diag_Name\",
							HT.HeredityType_id as \"HeredityType_id\"
						from
							v_HeredityDiag HD
							left join v_Diag D on D.Diag_id = HD.Diag_id
							left join v_HeredityType HT on HT.HeredityType_id = HD.HeredityType_id
						where
							HD.EvnPLDisp_id = :EvnPLDispDop13_id
							and HT.HeredityType_id = '1'
						order by
							HD.HeredityDiag_id
					";
					$res = $this->db->query($sql, array('EvnPLDispDop13_id' => $evnpldispdop_id));
					if(is_object($res)){
						$res = $res->result('array');
						$her_diag = '';
						$rec = array();
						foreach($res as $row){
							$her_diag = $her_diag." ".$row['Diag_Code'].";";
						}
						$dt[$i]['her_diag'] = $her_diag;
					}

				}
			}
		}
		return $dt;
	}

	/**
	 * Получение данных для пункат "Факторы риска" паспорта здоровья
	 */
	function getRiskFactorsForPassport_old($data)
	{
		$dt = array();
		$params = array();
		$params['EvnPLDispDop13_id'] = $data['EvnPLDispDop13_id'];
		$query = "
			select
				EPLDD.EvnPLDispDop13_id as \"EvnPLDispDop13_id\",
				to_char(EPLDD.EvnPLDispDop13_setDate, 'DD.MM.YYYY') as dd_date,
				systolic_blood_pressure.systolic_blood_pressure,
				diastolic_blood_pressure.diastolic_blood_pressure,
				cast(systolic_blood_pressure.systolic_blood_pressure as varchar) || '/' || cast(diastolic_blood_pressure.diastolic_blood_pressure as varchar) as person_pressure,
				--case when (CAST(systolic_blood_pressure.systolic_blood_pressure as numeric) > 140 or cast(diastolic_blood_pressure.diastolic_blood_pressure as numeric) > 90) then 'Да' else 'Нет' end as risk_high_pressure,
				person_weight.person_weight,
				person_height.person_height,
				body_mass_index.body_mass_index,
				--case when CAST(body_mass_index.body_mass_index as numeric) >= 25 then 'Да' else 'Нет' end as risk_overweight,
				glucose.glucose,
				--case when cast(glucose.glucose as numeric) > 6 then 'Да' else 'Нет' end as risk_gluk,
				total_cholesterol.total_cholesterol,
				--case when cast(total_cholesterol.total_cholesterol as numeric) > 5 then 'Да' else 'Нет' end as risk_dyslipidemia,
				case when EPLDD.EvnPLDispDop13_IsSmoking = 2 then 'Да' else 'Нет' end as \"IsSmoking\",
				case when EPLDD.EvnPLDispDop13_IsRiskAlco = 2 then 'Да' else 'Нет' end as \"IsRiskAlco\",
				case when EPLDD.EvnPLDispDop13_IsLowActiv = 2 then 'Да' else 'Нет' end as \"IsLowActiv\",
				case when EPLDD.EvnPLDispDop13_IsIrrational = 2 then 'Да' else 'Нет' end as \"IsIrrational\",
				case when DDQ_Count.DDQ_Count > 1 then 'Да' else 'Нет' end as risk_narco,
				cast(summ_risk.EvnPLDispDop13_SumRick as varchar) || '; ' || COALESCE(summ_risk.RiskType_Name,'') as summ_risk
				,m_personal.Med_Personal as \"Med_Personal\"
				,CAST(m_personal.SurveyType_Code as varchar) || '-' || m_personal.Med_Personal as dd_medpersonal
				,COALESCE(HK.HealthKind_Name,'') as \"HealthKind_Name\"
			from v_EvnPLDispDop13 EPLDD_F
			left join v_EvnPLDispDop13 EPLDD on EPLDD.Person_id = EPLDD_F.Person_id
			left join HealthKind HK on HK.HealthKind_id = EPLDD_F.HealthKind_id
			LEFT JOIN LATERAL (
				select --CAST(COALESCE(R.Rate_ValueInt,R.Rate_ValueStr,R.Rate_ValueFloat,'') as NUMERIC(10)) as systolic_blood_pressure
				COALESCE(CAST(R.Rate_ValueInt as varchar),CAST(R.Rate_ValueStr as varchar),CAST(R.Rate_ValueFloat as varchar),'') as systolic_blood_pressure
				from v_EvnUslugaDispDop EUDD
				left join v_EvnUslugaRate EUR on EUR.EvnUsluga_id = EUDD.EvnUslugaDispDop_id
				left join v_Rate R on R.Rate_id = EUR.Rate_id
				left join v_RateType RT on RT.RateType_id = R.RateType_id
				where EUDD.EvnUslugaDispDop_rid = EPLDD.EvnPLDispDop13_id
				and RT.RateType_SysNick = 'systolic_blood_pressure'
				and COALESCE(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
			) systolic_blood_pressure on true
			LEFT JOIN LATERAL (
				select --CAST(COALESCE(R.Rate_ValueInt,R.Rate_ValueStr,R.Rate_ValueFloat,'') as NUMERIC(10)) as diastolic_blood_pressure
				COALESCE(CAST(R.Rate_ValueInt as varchar),CAST(R.Rate_ValueStr as varchar),CAST(R.Rate_ValueFloat as varchar),'') as diastolic_blood_pressure
				from v_EvnUslugaDispDop EUDD
				left join v_EvnUslugaRate EUR on EUR.EvnUsluga_id = EUDD.EvnUslugaDispDop_id
				left join v_Rate R on R.Rate_id = EUR.Rate_id
				left join v_RateType RT on RT.RateType_id = R.RateType_id
				where EUDD.EvnUslugaDispDop_rid = EPLDD.EvnPLDispDop13_id
				and RT.RateType_SysNick = 'diastolic_blood_pressure'
				and COALESCE(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
			) diastolic_blood_pressure on true
			LEFT JOIN LATERAL (
				select --CAST(COALESCE(R.Rate_ValueInt,R.Rate_ValueStr,R.Rate_ValueFloat,'') as NUMERIC(10,2)) as person_weight
				COALESCE(CAST(R.Rate_ValueInt as varchar),CAST(R.Rate_ValueStr as varchar),CAST(R.Rate_ValueFloat as varchar),'') as person_weight
				from v_EvnUslugaDispDop EUDD
				left join v_EvnUslugaRate EUR on EUR.EvnUsluga_id = EUDD.EvnUslugaDispDop_id
				left join v_Rate R on R.Rate_id = EUR.Rate_id
				left join v_RateType RT on RT.RateType_id = R.RateType_id
				where EUDD.EvnUslugaDispDop_rid = EPLDD.EvnPLDispDop13_id
				and RT.RateType_SysNick = 'person_weight'
				and COALESCE(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
			) person_weight on true
			LEFT JOIN LATERAL (
				select --CAST(COALESCE(R.Rate_ValueInt,R.Rate_ValueStr,R.Rate_ValueFloat,'') as NUMERIC(10,2)) as person_height
				COALESCE(CAST(R.Rate_ValueInt as varchar),CAST(R.Rate_ValueStr as varchar),CAST(R.Rate_ValueFloat as varchar),'') as person_height
				from v_EvnUslugaDispDop EUDD
				left join v_EvnUslugaRate EUR on EUR.EvnUsluga_id = EUDD.EvnUslugaDispDop_id
				left join v_Rate R on R.Rate_id = EUR.Rate_id
				left join v_RateType RT on RT.RateType_id = R.RateType_id
				where EUDD.EvnUslugaDispDop_rid = EPLDD.EvnPLDispDop13_id
				and RT.RateType_SysNick = 'person_height'
				and COALESCE(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
			) person_height on true
			LEFT JOIN LATERAL (
				select --CAST(COALESCE(R.Rate_ValueInt,R.Rate_ValueStr,R.Rate_ValueFloat,'') as NUMERIC(10,2)) as body_mass_index
				COALESCE(CAST(R.Rate_ValueInt as varchar),CAST(R.Rate_ValueStr as varchar),CAST(R.Rate_ValueFloat as varchar),'') as body_mass_index
				from v_EvnUslugaDispDop EUDD
				left join v_EvnUslugaRate EUR on EUR.EvnUsluga_id = EUDD.EvnUslugaDispDop_id
				left join v_Rate R on R.Rate_id = EUR.Rate_id
				left join v_RateType RT on RT.RateType_id = R.RateType_id
				where EUDD.EvnUslugaDispDop_rid = EPLDD.EvnPLDispDop13_id
				and RT.RateType_SysNick = 'body_mass_index'
				and COALESCE(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
			) body_mass_index on true
			LEFT JOIN LATERAL (
				select-- CAST(COALESCE(R.Rate_ValueInt,R.Rate_ValueStr,R.Rate_ValueFloat,'') as NUMERIC(10,2)) as glucose
				COALESCE(CAST(R.Rate_ValueInt as varchar),CAST(R.Rate_ValueStr as varchar),CAST(R.Rate_ValueFloat as varchar),'') as glucose
				from v_EvnUslugaDispDop EUDD
				left join v_UslugaComplex UC on UC.UslugaComplex_id = EUDD.UslugaComplex_id
				left join v_EvnUslugaRate EUR on EUR.EvnUsluga_id = EUDD.EvnUslugaDispDop_id
				left join v_Rate R on R.Rate_id = EUR.Rate_id
				left join v_RateType RT on RT.RateType_id = R.RateType_id
				where EUDD.EvnUslugaDispDop_rid = EPLDD.EvnPLDispDop13_id
				and RT.RateType_SysNick = 'glucose'
				and COALESCE(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
				and UC.UslugaComplex_Name not ilike '%мочи%'
			) glucose on true
			LEFT JOIN LATERAL (
				select --CAST(COALESCE(R.Rate_ValueInt,R.Rate_ValueStr,R.Rate_ValueFloat,'') as NUMERIC(10,2)) as total_cholesterol
				COALESCE(CAST(R.Rate_ValueInt as varchar),CAST(R.Rate_ValueStr as varchar),CAST(R.Rate_ValueFloat as varchar),'') as total_cholesterol
				from v_EvnUslugaDispDop EUDD
				left join v_EvnUslugaRate EUR on EUR.EvnUsluga_id = EUDD.EvnUslugaDispDop_id
				left join v_Rate R on R.Rate_id = EUR.Rate_id
				left join v_RateType RT on RT.RateType_id = R.RateType_id
				where EUDD.EvnUslugaDispDop_rid = EPLDD.EvnPLDispDop13_id
				and RT.RateType_SysNick = 'total_cholesterol'
				and COALESCE(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
			) total_cholesterol on true
			LEFT JOIN LATERAL (
			select COUNT(DDQ.DopDispQuestion_IsTrue) as DDQ_Count
						from v_DopDispQuestion DDQ
						left join v_QuestionType QT on QT.QuestionType_id = DDQ.QuestionType_id
						where EvnPLDisp_id = EPLDD.EvnPLDispDop13_id
						and QT.QuestionType_Code in (40,41,42,43,44)
						and DDQ.DopDispQuestion_IsTrue = 2
			) DDQ_Count on true
			LEFT JOIN LATERAL (
				select
				CAST(EPLDD2.EvnPLDispDop13_SumRick as numeric(10)) as EvnPLDispDop13_SumRick,
				RT.RiskType_Name
				from EvnPLDispDop13 EPLDD2
				left join RiskType RT on RT.RiskType_id=EPLDD.RiskType_id
				where EPLDD2.EvnPLDispDop13_id = EPLDD.EvnPLDispDop13_id
			) summ_risk on true
			LEFT JOIN LATERAL (
			select	distinct
							st.SurveyType_Code as \"SurveyType_Code\",
							(ps.PostMed_Name || ' ' || msf.Person_Fio) as Med_Personal
						from v_EvnUslugaDispDop EUDD
							left join v_EvnVizitDispDop evdd on eudd.EvnUslugaDispDop_pid = evdd.EvnVizitDispDop_id
							left join v_MedStaffFact msf on msf.MedStaffFact_id = EUDD.MedStaffFact_id
							left join v_PostMed ps on ps.PostMed_id=msf.Post_id
							left join v_DopDispInfoConsent ddic on evdd.DopDispInfoConsent_id = ddic.DopDispInfoConsent_id
							left join v_SurveyTypeLink stl on stl.SurveyTypeLink_id = ddic.SurveyTypeLink_id
							left join v_SurveyType st on st.SurveyType_id = stl.SurveyType_id
						where
							EUDD.EvnUslugaDispDop_rid = EPLDD.EvnPLDispDop13_id
							and st.SurveyType_Code = '19'
							and COALESCE(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
			) m_personal on true
			where EPLDD_F.EvnPLDispDop13_id = :EvnPLDispDop13_id-- 1261360--1264267
			and EPLDD.EvnPLDispDop13_setDate <= EPLDD_F.EvnPLDispDop13_setDate
			and (person_weight.person_weight is not null or person_height.person_height is not null or glucose.glucose is not null)
			order by EPLDD.EvnPLDispDop13_setDate desc
			limit 5
		";
		//where EPLDD_F.EvnPLDispDop13_id in(1264267,1261360)-- 1261360--1264267
		$result = $this->db->query($query,$params);
		if(is_object($result)){
			$result = $result->result('array');
			if(count($result > 0)){
				for($i=0; $i < count($result); $i++)
				{
					$dt[$i] = $result[$i];
					//Найдем отдельно данные по отягощенный наследственности
					$dt[$i]['her_diag'] = '';
					$evnpldispdop_id = $dt[$i]['EvnPLDispDop13_id'];
					$sql = "
						select
							D.Diag_Code as \"Diag_Code\",
							D.Diag_Name as \"Diag_Name\",
							HT.HeredityType_id as \"HeredityType_id\"
						from
							v_HeredityDiag HD
							left join v_Diag D on D.Diag_id = HD.Diag_id
							left join v_HeredityType HT on HT.HeredityType_id = HD.HeredityType_id
						where
							HD.EvnPLDisp_id = :EvnPLDispDop13_id
							and HT.HeredityType_id = '1'
						order by
							HD.HeredityDiag_id
					";
					$res = $this->db->query($sql, array('EvnPLDispDop13_id' => $evnpldispdop_id));
					if(is_object($res)){
						$res = $res->result('array');
						$her_diag = '';
						$rec = array();
						foreach($res as $row){
							$her_diag = $her_diag." ".$row['Diag_Code'].";";
						}
						$dt[$i]['her_diag'] = $her_diag;
					}
				}
			}
			//var_dump($dt);die;
		}
		return $dt;
	}

	/**
	 *	Получение идентификатора посещения
	 */
	function getEvnVizitDispDopId($EvnPLDispDop13_id = null, $DopDispInfoConsent_id = null) {
		$query = "
			select
				EUDD.EvnUslugaDispDop_pid as \"EvnVizitDispDop_id\"
			from
				v_DopDispInfoConsent DDIC
				inner join v_SurveyTypeLink STL on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
				inner join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
				inner join v_EvnUslugaDispDop EUDD on EUDD.UslugaComplex_id = STL.UslugaComplex_id
			where
				DDIC.DopDispInfoConsent_id = :DopDispInfoConsent_id
				and EUDD.EvnUslugaDispDop_rid = :EvnPLDispDop13_id
				and COALESCE(DDIC.DopDispInfoConsent_IsAgree, 1) = 1
				and COALESCE(DDIC.DopDispInfoConsent_IsEarlier, 1) = 1
				and ST.SurveyType_Code NOT IN (1,48)
				and COALESCE(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
			limit 1
		";
		
		$result = $this->db->query($query, array(
			 'DopDispInfoConsent_id' => $DopDispInfoConsent_id
			,'EvnPLDispDop13_id' => $EvnPLDispDop13_id
		));
	
        if ( is_object($result) ) {
            $res = $result->result('array');

			if ( is_array($res) && count($res) > 0 && !empty($res[0]['EvnVizitDispDop_id']) ) {
				return $res[0]['EvnVizitDispDop_id'];
			}
			else {
				return null;
			}
        }
        else {
            return false;
        }
	}

	/**
	 * Получение списка категорий льготы, которые участвуют в ДВН
	 * @task https://redmine.swan.perm.ru/issues/37296
	 * @task https://redmine.swan.perm.ru/issues/115088 - только Крым и Пермь
	 */
	public function getPersonPrivilegeCodeList($date = null) {
		return array();
	}

	/**
	 * Возвращает список всех используемых ключей атрибутов объекта
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();
		$arr[self::ID_KEY]['alias'] = 'EvnPLDispDop13_id';
		$arr[self::ID_KEY]['label'] = 'Идентификатор талона диспансеризации';
		$arr['isstenocard'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispDop13_IsStenocard',
			'label' => 'Подозрение на наличие стенокардии напряжения',
			'save' => '',
			'type' => 'id'
		);
		$arr['isdoublescan'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispDop13_IsDoubleScan',
			'label' => 'Показания к проведению дуплексного сканирования брахицефальных артерий',
			'save' => '',
			'type' => 'id'
		);
		$arr['istub'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispDop13_IsTub',
			'label' => 'Подозрение на наличие туберкулеза, хронического заболевания легких или новообразования легких',
			'save' => '',
			'type' => 'id'
		);
		$arr['istia'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispDop13_IsTIA',
			'label' => 'Имеется вероятность транзиторной ишемической атаки (ТИА) или перенесенного ОНМК',
			'save' => '',
			'type' => 'id'
		);
		$arr['isrespiratory'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispDop13_IsRespiratory',
			'label' => 'Имеется вероятность хронического заболевания нижних дыхательных путей',
			'save' => '',
			'type' => 'id'
		);
		$arr['islungs'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispDop13_IsLungs',
			'label' => 'Подозрение на заболевания легких (Бронхоэктазы, онкопатология, туберкулез)',
			'save' => '',
			'type' => 'id'
		);
		$arr['istopgastro'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispDop13_IsTopGastro',
			'label' => 'Вероятность заболеваний верхних отделов желудочнокишечного тракта',
			'save' => '',
			'type' => 'id'
		);
		$arr['isbotgastro'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispDop13_IsBotGastro',
			'label' => 'Вероятность заболевания нижних отделов ЖКТ',
			'save' => '',
			'type' => 'id'
		);
		$arr['isspirometry'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispDop13_IsSpirometry',
			'label' => 'Показания к проведению спирометрии',
			'save' => '',
			'type' => 'id'
		);
		$arr['isheartfailure'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispDop13_IsHeartFailure',
			'label' => 'Вероятно наличие сердечной недостаточности',
			'save' => '',
			'type' => 'id'
		);
		$arr['isoncology'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispDop13_IsOncology',
			'label' => 'Вероятность онкопатологии',
			'save' => '',
			'type' => 'id'
		);
		$arr['isesophag'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispDop13_IsEsophag',
			'label' => 'Показания к проведению эзофагогастродуоденоскопии',
			'save' => '',
			'type' => 'id'
		);
		$arr['issmoking'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispDop13_IsSmoking',
			'label' => 'Курение',
			'save' => '',
			'type' => 'id'
		);
		$arr['isriskalco'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispDop13_IsRiskAlco',
			'label' => 'Риск пагубного потребления алкоголя',
			'save' => '',
			'type' => 'id'
		);
		$arr['isalcodepend'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispDop13_IsAlcoDepend',
			'label' => 'Подозрение на зависимость от алкоголя',
			'save' => '',
			'type' => 'id'
		);
		$arr['islowactiv'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispDop13_IsLowActiv',
			'label' => 'Низкая физическая активность',
			'save' => '',
			'type' => 'id'
		);
		$arr['isirrational'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispDop13_IsIrrational',
			'label' => 'Нерациональное питание',
			'save' => '',
			'type' => 'id'
		);
		$arr['isusenarko'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispDop13_IsUseNarko',
			'label' => 'Потребление наркотических средств без назначения врача',
			'save' => '',
			'type' => 'id'
		);
		$arr['diag_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Diag_id',
			'label' => 'Подозрение на хроническое неинфекционное заболевание, требующее дообследования',
			'save' => '',
			'type' => 'id'
		);
		$arr['isdisp'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispDop13_IsDisp',
			'label' => 'Взят на диспансерное наблюдение',
			'save' => '',
			'type' => 'id'
		);
		$arr['isambul'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispDop13_IsAmbul',
			'label' => 'Нуждается в амбулаторном дополнительном лечении (обследовании)',
			'save' => '',
			'type' => 'id'
		);
		$arr['isstac'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispDop13_IsStac',
			'label' => 'Нуждается в стац. спец., в т.ч. высокотехнологичном дополнительном лечении (обследовании)',
			'save' => '',
			'type' => 'id'
		);
		$arr['issanator'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispDop13_IsSanator',
			'label' => 'Нуждается в санаторно-курортном лечении',
			'save' => '',
			'type' => 'id'
		);
		$arr['sumrick'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispDop13_SumRick',
			'label' => 'Суммарный сердечно-сосудистый риск',
			'save' => '',
			'type' => 'int'
		);
		$arr['risktype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'RiskType_id',
			'label' => 'Тип риска',
			'save' => '',
			'type' => 'id'
		);
		$arr['isschool'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispDop13_IsSchool',
			'label' => 'Школа пациента',
			'save' => '',
			'type' => 'id'
		);
		$arr['isprophcons'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispDop13_IsProphCons',
			'label' => 'Углубленное профилактическое консультирование',
			'save' => '',
			'type' => 'id'
		);
		$arr['healthkind_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'HealthKind_id',
			'label' => 'Группа здоровья',
			'save' => '',
			'type' => 'id'
		);
		$arr['isendstage'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispDop13_IsEndStage',
			'label' => 'Случай профосмотра закончен',
			'save' => '',
			'type' => 'id'
		);
		$arr['istwostage'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispDop13_IsTwoStage',
			'label' => 'Направлен на 2 этап диспансеризации',
			'save' => '',
			'type' => 'id'
		);
		$arr['cardiorisktype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'CardioRiskType_id',
			'label' => 'Риск сердечно-сосудистых заболеваний',
			'save' => '',
			'type' => 'id'
		);
		$arr['needdopcure_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'NeedDopCure_id',
			'label' => 'Дополнительное лечение',
			'save' => '',
			'type' => 'id'
		);
		$arr['ishypoten'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispDop13_IsHypoten',
			'label' => 'Гипотензивная терапия',
			'save' => '',
			'type' => 'id'
		);
		$arr['islipid'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispDop13_IsLipid',
			'label' => 'Гиполипидемическая терапия',
			'save' => '',
			'type' => 'id'
		);
		$arr['ishypoglyc'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispDop13_IsHypoglyc',
			'label' => 'Гипогликемическая терапия',
			'save' => '',
			'type' => 'id'
		);
		$arr['diag_sid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Diag_sid',
			'label' => 'Подозрение на инфекционные и паразитарные болезни',
			'save' => '',
			'type' => 'id'
		);
		$arr['isbrain'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispDop13_IsBrain',
			'label' => 'Подозрение ранее перенесенное нарушение мозгового кровообращения',
			'save' => '',
			'type' => 'id'
		);
		$arr['isshortcons'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispDop13_IsShortCons',
			'label' => 'Проведено индивидуальное краткое профилактическое консультирование',
			'save' => '',
			'type' => 'id'
		);

		return $arr;
	}

	/**
	 * Определение идентификатора класса события
	 * @return int
	 */
	static function evnClassId()
	{
		return 101;
	}

	/**
	 * Определение кода класса события
	 * @return string
	 */
	static function evnClassSysNick()
	{
		return 'EvnPLDispDop13';
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 * @throws Exception
	 */
	function updateHealthKindId($id, $value = null)
	{
		return $this->_updateAttribute($id, 'healthkind_id', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 * @throws Exception
	 */
	function updateIsTwoStage($id, $value = null)
	{
		return $this->_updateAttribute($id, 'istwostage', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 * @throws Exception
	 */
	function updateIsEndStage($id, $value = null)
	{
		return $this->_updateAttribute($id, 'isendstage', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 * @throws Exception
	 */
	function updateIsStenocard($id, $value = null)
	{
		return $this->_updateAttribute($id, 'isstenocard', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 * @throws Exception
	 */
	function updateIsBrain($id, $value = null)
	{
		return $this->_updateAttribute($id, 'isbrain', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 * @throws Exception
	 */
	function updateIsDoubleScan($id, $value = null)
	{
		return $this->_updateAttribute($id, 'isdoublescan', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 * @throws Exception
	 */
	function updateIsTub($id, $value = null)
	{
		return $this->_updateAttribute($id, 'istub', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 * @throws Exception
	 */
	function updateIsEsophag($id, $value = null)
	{
		return $this->_updateAttribute($id, 'isesophag', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 * @throws Exception
	 */
	function updateIsSmoking($id, $value = null)
	{
		return $this->_updateAttribute($id, 'issmoking', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 * @throws Exception
	 */
	function updateIsRiskAlco($id, $value = null)
	{
		return $this->_updateAttribute($id, 'isriskalco', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 * @throws Exception
	 */
	function updateIsAlcoDepend($id, $value = null)
	{
		return $this->_updateAttribute($id, 'isalcodepend', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 * @throws Exception
	 */
	function updateIsLowActiv($id, $value = null)
	{
		return $this->_updateAttribute($id, 'islowactiv', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 * @throws Exception
	 */
	function updateIsIrrational($id, $value = null)
	{
		return $this->_updateAttribute($id, 'isirrational', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 * @throws Exception
	 */
	function updateIsShortCons($id, $value = null)
	{
		return $this->_updateAttribute($id, 'isshortcons', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 * @throws Exception
	 */
	function updateIsHypoten($id, $value = null)
	{
		return $this->_updateAttribute($id, 'ishypoten', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 * @throws Exception
	 */
	function updateIsLipid($id, $value = null)
	{
		return $this->_updateAttribute($id, 'islipid', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 * @throws Exception
	 */
	function updateIsHypoglyc($id, $value = null)
	{
		return $this->_updateAttribute($id, 'ishypoglyc', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 * @throws Exception
	 */
	function updateIsDisp($id, $value = null)
	{
		return $this->_updateAttribute($id, 'isdisp', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 * @throws Exception
	 */
	function updateIsSanator($id, $value = null)
	{
		return $this->_updateAttribute($id, 'issanator', $value);
	}

	/**
	 * Дата перехода на новый приказ ДВН (приказ 124н).
	 *
	 * @return string
	 */
	public function getNewDVNDate() {
		$dateX = '';

		switch ( $this->regionNick ) {
			case 'ufa':
			case 'adygeya':
				$dateX = '2019-07-01';
				break;

			case 'kareliya':
			case 'krasnoyarsk':
			case 'perm':
				$dateX = '2019-06-01';
				break;

			default:
				$dateX = '2019-05-06';
				break;
		}

		return $dateX;
	}

	/**
	 * @param int $Person_id
	 * @param string $onDate
	 * @return boolean
	 */
	public function allowDVN($Person_id, $onDate) {
		$dateX = $this->getNewDVNDate();
		$Year = date('Y', strtotime($onDate));
		$YearEndDate = $Year . '-12-31';

		$queryParams = array(
			'onDate' => date('Y-m-d', strtotime($onDate)),
			'Person_id' => $Person_id,
			'YearEndDate' => $YearEndDate,
		);

		$PersonAgeData = $this->getFirstRowFromQuery("
			SELECT
				PS.Sex_id as \"Sex_id\",
				dbo.Age2(PS.Person_BirthDay, :onDate) as \"Person_Age\",
				dbo.Age2(PS.Person_BirthDay, :YearEndDate) as \"Person_AgeOnYearEnd\"
			FROM v_PersonState PS
			WHERE PS.Person_id = :Person_id
			LIMIT 1
		", $queryParams);

		if ($PersonAgeData !== false && is_array($PersonAgeData)) {
			$Person_AgeOnYearEnd = $PersonAgeData['Person_AgeOnYearEnd'];

			if (!empty($dateX) && $queryParams['onDate'] >= $dateX) {
				if ($Person_AgeOnYearEnd > 39 || $Person_AgeOnYearEnd % 3 == 0) {
					return true;
				}
			} else {
				if ($Person_AgeOnYearEnd >= 21 && $Person_AgeOnYearEnd % 3 == 0) {
					return true;
				} else if ($queryParams['onDate'] >= '2018-01-01') {
					switch ($PersonAgeData['Sex_id']) {
						case 2:
							if ($Person_AgeOnYearEnd >= 48 && $Person_AgeOnYearEnd <= 73) {
								return true;
							}
							break;
						case 1:
							if ($Person_AgeOnYearEnd >= 49 && $Person_AgeOnYearEnd <= 73 && $Person_AgeOnYearEnd % 2 == 1) {
								return true;
							}
							break;
					}
				}
			}
		}

		$queryArray = array();

		$personPrivilegeCodeList = $this->getPersonPrivilegeCodeList($queryParams['onDate']);

		if ( in_array($this->regionNick, array('ufa', 'ekb', 'kareliya', 'krasnoyarsk', 'penza', 'astra')) ) {
			$queryArray[] = "
				select PersonPrivilegeWOW_id as id
				from v_PersonPrivilegeWOW
				where Person_id = :Person_id
				limit 1
			";
		}

		if ( count($personPrivilegeCodeList) > 0 ) {
			$queryArray[] = "
				select pp.PersonPrivilege_id as id
				from v_PersonPrivilege pp
					inner join v_PrivilegeType pt on pt.PrivilegeType_id = pp.PrivilegeType_id
				where pt.PrivilegeType_Code in ('" . implode("','", $personPrivilegeCodeList) . "')
					and pp.Person_id = :Person_id
					and pp.PersonPrivilege_begDate <= :YearEndDate
					and (pp.PersonPrivilege_endDate >= :YearEndDate or pp.PersonPrivilege_endDate is null)
				limit 1
			";
		}

		if ( count($queryArray) > 0 ) {
			$res = $this->getFirstResultFromQuery(implode(' union all ', $queryArray), $queryParams);

			if ( $res !== false && !empty($res) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Проверка в формах ДВН добавляемого диагноза.
	 */
	function CheckDiag($data) {
		$params = array(
			'EvnPLDispDop13_id' => $data['EvnPLDispDop13_id'],
			'Diag_id' => $data['Diag_id'],
			'DeseaseDispType_id' => $data['DeseaseDispType_id'],
			'EvnUslugaDispDop_id' => $data['EvnUslugaDispDop_id'],
			'Diag_Code' => ''
		);

		$sql = "
			SELECT
				D.Diag_Code as \"Diag_Code\"
			FROM v_Diag D
			WHERE D.Diag_id = :Diag_id
			limit 1
		";

		$code = $this->getFirstResultFromQuery($sql, $params);
		if(empty($code)) return false;
		$code = substr($code, 0,3);

		$params['Diag_Code'] = $code;
		$filter = "";
		$join = "";
		if(!empty($params['EvnUslugaDispDop_id'])) {//если диагноз из осмотра/исследования, то уцепиться можем только за EvnUslugaDispDop_id/Diag_id
			$filter.=" and coalesce(EvnUslugaDispDop_id, 0) != :EvnUslugaDispDop_id";
			$join .= " left join v_EvnUslugaDispDop EU on EU.Diag_id = ED3.Diag_id and EU.EvnUslugaDispDop_rid = ED3.EvnDiagDopDisp_rid";
		}

		$sql = "
			select *
			from
				(SELECT
					D.Diag_id as \"Diag_id\",
					case when ED3.Diag_id=:Diag_id
						then 0
						else 1
					end as \"eqsort\"
				FROM v_EvnDiagDopDisp ED3
					left join v_Diag D on D.Diag_id = ED3.Diag_id
					{$join}
				WHERE ED3.EvnDiagDopDisp_rid = :EvnPLDispDop13_id
					and substring(D.Diag_Code, 1,3) = :Diag_Code
					{$filter}
				union
				SELECT
					D.Diag_id as \"Diag_id\",
					case when EU.Diag_id=:Diag_id
						then 0
						else 1
					end as \"eqsort\"
				FROM v_EvnUslugaDispDop EU
					left join v_Diag D on D.Diag_id = EU.Diag_id
				WHERE EU.EvnUslugaDispDop_rid = :EvnPLDispDop13_id
					and substring(D.Diag_Code, 1,3) = :Diag_Code
					{$filter}) t
			order by \"eqsort\"
		";

		$diag = $this->getFirstResultFromQuery($sql, $params);

		if(empty($diag) && !empty($data['DeseaseDispType_id']) && $data['DeseaseDispType_id'] == 2 ) {
			$sql = "
				select
					*
				from (
					(SELECT
						EPL.Diag_id as \"Diag_id\"
					FROM v_EvnPLDispDop13 E13
						inner join v_EvnPL EPL on EPL.Person_id = E13.Person_id
					WHERE E13.EvnPLDispDop13_id = :EvnPLDispDop13_id
						and EPL.Diag_id = :Diag_id
					limit 1)
					
					union
					(SELECT
						EVPL.Diag_id as \"Diag_id\"
					FROM v_EvnPLDispDop13 E13
						inner join v_EvnVizitPL EVPL on EVPL.Person_id = E13.Person_id
					WHERE E13.EvnPLDispDop13_id = :EvnPLDispDop13_id
						and EVPL.Diag_id = :Diag_id
					limit 1)
					
					union
					(SELECT
						PD.Diag_id as \"Diag_id\"
					FROM v_EvnPLDispDop13 E13
						inner join v_PersonDisp PD on PD.Person_id = E13.Person_id
					where E13.EvnPLDispDop13_id = :EvnPLDispDop13_id
						and PD.Diag_id = :Diag_id
					limit 1)
				) t
			";

			$diag = $this->getFirstResultFromQuery($sql, $params);
			if(!empty($diag)) $diag = -1;
		}

		return $diag;
	}
	
	/**
	 * Получить список назначений и направлений раздела направления ДВН
	 */
	function loadEvnPLDispDop13PrescrList($data) {
		$UslugaComplexList = json_decode($data['UslugaComplexList']);

		// Для тестирования #156667
	/*	if(getRegionNick() == 'perm'){
			$data['userLpu_id'] = 10010833; // для тестовой Перми сработало
			//$data['userLpu_id'] = 101;
			$data['userLpuBuilding_id'] = null;
			$data['userLpuUnit_id'] = null;
		} else */
		
		$params = array(
			'LpuSection_id' => $data['userLpuSection_id'],
		);
		$sql = "
			Select
				user_ls.Lpu_id as \"Lpu_id\",
				user_lu.LpuBuilding_id as \"LpuBuilding_id\",
				user_ls.LpuUnit_id as \"LpuUnit_id\"
			from v_LpuSection user_ls
			inner join v_LpuUnit user_lu on user_lu.LpuUnit_id = user_ls.LpuUnit_id
			where user_ls.LpuSection_id = :LpuSection_id
		";
		//exit(getDebugSQL($sql, $params));
		$result = $this->db->query($sql, $params);
		if (is_object($result))
		{
			$rc = $result->result('array');
			if (count($rc)>0 && is_array($rc[0])) {
				$data['userLpu_id'] = $rc[0]['Lpu_id'];
				$data['userLpuBuilding_id'] = $rc[0]['LpuBuilding_id'];
				$data['userLpuUnit_id'] = $rc[0]['LpuUnit_id'];
			}
		}
		

		$params = array(
			'EvnPrescr_pid' => $data['EvnPLDispDop13_id']
		);

		$query = "
			WITH EvnPrescr
			AS (SELECT 
					COALESCE(EPLD.EvnPrescrLabDiag_id,EPFDU.EvnPrescrFuncDiag_id,EPCO.EvnPrescrConsUsluga_id) AS EvnPrescr_id,
					COALESCE(EPLD.UslugaComplex_id, EPFDU.UslugaComplex_id, EPCO.UslugaComplex_id) AS UslugaComplex_id,
					pt.PrescriptionType_Code,
					COALESCE(EP.EvnPrescr_IsExec,1) as EvnPrescr_IsExec
				FROM v_EvnPrescr EP
					LEFT JOIN v_EvnPrescrLabDiag EPLD
						ON EPLD.EvnPrescrLabDiag_id = EP.EvnPrescr_id
						   AND EP.PrescriptionType_id = 11
					LEFT JOIN EvnPrescrFuncDiagUsluga EPFDU
						ON EPFDU.EvnPrescrFuncDiag_id = EP.EvnPrescr_id
						   AND EP.PrescriptionType_id = 12
					LEFT JOIN v_EvnPrescrConsUsluga EPCO
						ON EPCO.EvnPrescrConsUsluga_id = EP.EvnPrescr_id
						   AND EP.PrescriptionType_id = 13
					LEFT JOIN v_PrescriptionType pt
						ON pt.PrescriptionType_id = EP.PrescriptionType_id
				WHERE EP.EvnPrescr_pid = :EvnPrescr_pid 
				--'730023881307390'
					  AND EP.PrescriptionType_id IN ( 11, 12, 13 )
					  AND EP.PrescriptionStatusType_id != 3
			)
			
			SELECT 
				uc.UslugaComplex_id as \"UslugaComplex_id\",
				uc.UslugaComplex_Code as \"UslugaComplex_Code\",
				uc.UslugaComplex_Name as \"UslugaComplex_Name\",
				EP.EvnPrescr_id as \"EvnPrescr_id\",
				EP.EvnPrescr_IsExec as \"EvnPrescr_IsExec\",
				EvnStatus.EvnStatus_SysNick as \"EvnStatus_SysNick\",
				COALESCE(EP.PrescriptionType_Code,attr.PrescriptionType_Code) AS \"PrescriptionType_Code\",
				case	
					when COALESCE(EP.PrescriptionType_Code,attr.PrescriptionType_Code) = 11 then 'EvnPrescrLabDiag'
					when COALESCE(EP.PrescriptionType_Code,attr.PrescriptionType_Code) = 12 then 'EvnPrescrFuncDiag'
					when COALESCE(EP.PrescriptionType_Code,attr.PrescriptionType_Code) = 13 then 'EvnPrescrConsUsluga'
					else ''
				end as \"object\",
				case
						when TTMS.TimetableMedService_id is not null then COALESCE(to_char(TTMS.TimetableMedService_begTime, 'DD.MM.YYYY MI:SS'),'')
						when TTR.TimetableResource_id is not null then COALESCE(to_char(TTR.TimetableResource_begTime, 'DD.MM.YYYY MI:SS'),'')
						when TTG.TimetableGraf_id is not null then COALESCE(to_char(TTG.TimetableGraf_begTime, 'DD.MM.YYYY MI:SS'),'')
						when EQ.EvnQueue_id is not null then 'В очереди с '|| COALESCE(to_char(EQ.EvnQueue_setDate, 'DD.MM.YYYY'),'')
					else '' end as \"RecDate\",
				case
					when TTMS.TimetableMedService_id is not null then COALESCE(MS.MedService_Name,'')
					when TTR.TimetableResource_id is not null then COALESCE(R.Resource_Name,'') ||' / '|| COALESCE(MS.MedService_Name,'')
					when TTG.TimetableGraf_id is not null then COALESCE(LS.LpuSection_Name,'') ||' / '|| COALESCE(LS.LpuSectionProfile_Name,'')
					when EQ.EvnQueue_id is not null then
						case
							when MS.MedService_id is not null and  MS.LpuSection_id is null and MS.LpuUnit_id is null
							then COALESCE(MS.MedService_Name,'')
							when MS.MedService_id is not null and  MS.LpuSection_id is null and MS.LpuUnit_id is not null
							then COALESCE(MS.MedService_Name,'') ||' / '|| COALESCE(LU.LpuUnit_Name,'')
							when MS.MedService_id is not null and  MS.LpuSection_id is not null and MS.LpuUnit_id is not null
							then COALESCE(MS.MedService_Name,'') ||' / '|| COALESCE(LSPD.LpuSectionProfile_Name,'') ||' / '|| COALESCE(LU.LpuUnit_Name,'')
							else COALESCE(LSPD.LpuSectionProfile_Name,'') ||' / '|| COALESCE(LU.LpuUnit_Name,'')
						end
				else '' end as \"RecTo\",
				ED.EvnDirection_id as \"EvnDirection_id\",
				ED.EvnQueue_id as \"EvnQueue_id\",
				TTG.TimetableGraf_id as \"TimetableGraf_id\",
				MS.MedService_Nick as \"MedService_Nick\",
				EUP.EvnUslugaPar_id as \"EvnUslugaPar_id\"
			FROM v_UslugaComplex uc
				left join lateral (
					SELECT
						   *
					FROM v_UslugaComplex uc11
					WHERE uc.UslugaComplex_2011id = uc11.UslugaComplex_id
					limit 1
				) uc11 on true
				left join lateral (
					SELECT *
					FROM EvnPrescr ep
					WHERE ep.UslugaComplex_id = uc.UslugaComplex_id
				) EP on true
				left join lateral (
					Select
						ED.EvnDirection_id
						,ED.EvnQueue_id
						,ED.EvnDirection_Num
						,ED.MedService_id
						,ED.Resource_id
						,ED.LpuSection_did
						,ED.LpuUnit_did
						,ED.LpuSectionProfile_id
						,ED.EvnStatus_id
					from v_EvnPrescrDirection epd
					inner join v_EvnDirection_all ED on epd.EvnDirection_id = ED.EvnDirection_id
					where EP.EvnPrescr_id is not null 
						AND epd.EvnPrescr_id = EP.EvnPrescr_id
						AND COALESCE(ED.EvnStatus_id, 16) not in (12,13)
					order by 
						case when COALESCE(ED.EvnStatus_id, 16) in (12,13) then 2 else 1 end /* первым неотмененное/неотклоненное направление */
						,epd.EvnPrescrDirection_insDT desc
					limit 1
				) ED on true
				-- заказанная услуга для параклиники
				left join lateral (
					select EvnUslugaPar_id FROM v_EvnUslugaPar where EvnDirection_id = ED.EvnDirection_id
				) EUP on true
				--left join v_EvnUslugaPar EUP on EUP.EvnDirection_id = ED.EvnDirection_id
				-- службы и параклиника
				left join lateral (
					select TimetableMedService_id, 
						TimetableMedService_begTime 
					from v_TimetableMedService_lite TTMS 
					where TTMS.EvnDirection_id = ED.EvnDirection_id
					limit 1
				) TTMS on true
				-- очередь
				left join lateral (
					select EQ.EvnQueue_id, EQ.LpuUnit_did, EQ.LpuSectionProfile_did, Lpu_id, EQ.EvnQueue_setDate, EQ.EvnQueue_failDT, EQ.QueueFailCause_id 
					from v_EvnQueue EQ
					where EQ.EvnDirection_id = ED.EvnDirection_id
					and EQ.EvnQueue_recDT is null
					-- это костыль для того, чтобы направления у которых есть связь по EQ.EvnQueue_id = ED.EvnQueue_id, но нет реальной записи в TimetableMedService можно было отменить
					union
					select EQ.EvnQueue_id, EQ.LpuUnit_did, EQ.LpuSectionProfile_did, Lpu_id, EQ.EvnQueue_setDate, EQ.EvnQueue_failDT, EQ.QueueFailCause_id 
					from v_EvnQueue EQ
					where (EQ.EvnQueue_id = ED.EvnQueue_id)
						and (EQ.EvnQueue_recDT is null or TTMS.TimetableMedService_id is null)
						and EQ.EvnQueue_failDT is null
					limit 1
				) EQ on true
				-- ресурсы
				left join lateral (
					select TimetableResource_id, TimetableResource_begTime 
					from v_TimetableResource_lite TTR 
					where TTR.EvnDirection_id = ED.EvnDirection_id
					limit 1
				) TTR on true
				-- бирки к врачу
				left join lateral (
					select TimetableGraf_id, TimetableGraf_begTime 
					from dbo.v_TimeTableGraf_lite TTG 
					where TTG.EvnDirection_id = ED.EvnDirection_id
					limit 1
				) TTG on true
				-- сам ресрс
				left join v_Resource R on R.Resource_id = ED.Resource_id
				left join v_MedService MS on MS.MedService_id = ED.MedService_id -- ED.MedService_did должно быть
				-- отделение для полки и стаца и для очереди
				left join v_LpuSection LS on LS.LpuSection_id = coalesce(ED.LpuSection_did, MS.LpuSection_id)
				-- подразделение для очереди и служб
				left join v_LpuUnit LU on coalesce(ED.LpuUnit_did,EQ.LpuUnit_did,MS.LpuUnit_id) = LU.LpuUnit_id -- todo: в ED.LpuUnit_did пусто, чего не должно быть
				-- профиль для очереди
				left join v_LpuSectionProfile LSPD on coalesce(ED.LpuSectionProfile_id,EQ.LpuSectionProfile_did,LS.LpuSectionProfile_id) = LSPD.LpuSectionProfile_id -- todo: тут на примере оказалось что почему то ED.LpuSectionProfile_id != EQ.LpuSectionProfile_did, чего не должно быть
				left join lateral(
					select ESH.EvnStatus_id, ESH.EvnStatusCause_id, ESH.pmUser_insID, ESH.EvnStatusHistory_Cause
					from EvnStatusHistory ESH
					where ESH.Evn_id = ED.EvnDirection_id
						and ESH.EvnStatus_id = ED.EvnStatus_id
					order by ESH.EvnStatusHistory_begDate desc
					limit 1
				) ESH on true
				left join EvnStatus on EvnStatus.EvnStatus_id = ESH.EvnStatus_id
				left join lateral (
					SELECT
					case	
						when t2.UslugaComplexAttributeType_SysNick = 'lab' then 11
						when t2.UslugaComplexAttributeType_SysNick = 'func' then 12
						when t2.UslugaComplexAttributeType_SysNick = 'consult' then 13
						else 0
					end as PrescriptionType_Code
					
					/*CASE WHEN t2.UslugaComplexAttributeType_SysNick = 'lab' THEN 11
					ELSE CASE WHEN t2.UslugaComplexAttributeType_SysNick = 'func' THEN 12 END
					END AS PrescriptionType_Code*/
						   
					FROM v_UslugaComplexAttribute t1
						INNER JOIN v_UslugaComplexAttributeType t2
							ON t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
					WHERE t1.UslugaComplex_id = COALESCE(uc11.UslugaComplex_id, uc.UslugaComplex_id)
						  AND t2.UslugaComplexAttributeType_SysNick IN ( 'lab','func','consult')
					limit 1
				) attr on true
			WHERE uc.UslugaComplex_id IN (" . implode(',', $UslugaComplexList) . ")
			
			--uc.UslugaComplex_id IN ( 4634872, 4426005, 206896, 201667, 200884, 200886, 200885 );
		";
		//exit(getDebugSQL($query, $params));
		$result = $this->db->query($query, $params);
		//EvnPLDispScreenOnko_id: 730023881307390
		if ( is_object($result) ) {
			$UslugaList = $result->result('array');
		} else {
			// ошибка - нет назначений (исследований)
			return false;
		}


		$FuncUslList = $LabUslList = $OtherUslList = array();
		foreach ($UslugaList as $key => $usl) {
			if(empty($usl['EvnDirection_id'])){
				switch ($usl['object']) {
					case 'EvnPrescrLabDiag':
						$LabUslList[] = $usl['UslugaComplex_id'];
						break;
					case 'EvnPrescrFuncDiag':
						$FuncUslList[] = $usl['UslugaComplex_id'];
						break;
					case 'EvnPrescrConsUsluga':
						$OtherUslList[] = $usl['UslugaComplex_id'];
						break;
					default;
				}
			}
		}
		$this->load->model('MedService_model');
		$resourceList = $this->MedService_model->getResourceListByFirstTT($data, $FuncUslList);
		$LabAndPZList = $this->MedService_model->getLabAndPZListByFirstTT($data, $LabUslList);
		$OtherMedServiceList = $this->MedService_model->getMedServiceListByFirstTT($data, $OtherUslList);

		if(!empty($resourceList))
			foreach($resourceList as $res)
				$resourceList[$res['UslugaComplex_id']] = $res;

		if(!empty($LabAndPZList))
			foreach($LabAndPZList as $lab)
				$LabAndPZList[$lab['UslugaComplex_id']] = $lab;

		if(!empty($OtherMedServiceList))
			foreach($OtherMedServiceList as $ms)
				$OtherMedServiceList[$ms['UslugaComplex_id']] = $ms;

		if(!empty($resourceList) || !empty($LabAndPZList) || !empty($OtherMedServiceList)){
			foreach($UslugaList as $key => $usl){
				switch ($usl['object']) {
					case 'EvnPrescrLabDiag':
						if(!empty($LabAndPZList[$usl['UslugaComplex_id']]))
							$UslugaList[$key] = array_merge($UslugaList[$key],$LabAndPZList[$usl['UslugaComplex_id']]);
						break;

					case 'EvnPrescrFuncDiag':
						if(!empty($resourceList[$usl['UslugaComplex_id']]))
							$UslugaList[$key] = array_merge($UslugaList[$key],$resourceList[$usl['UslugaComplex_id']]);
						break;

					case 'EvnPrescrConsUsluga':
						if(!empty($OtherMedServiceList[$usl['UslugaComplex_id']]))
							$UslugaList[$key] = array_merge($UslugaList[$key],$OtherMedServiceList[$usl['UslugaComplex_id']]);
						break;
					default;
				}
			}
		}

		return $UslugaList;
	}


	/**
	 * Получение справочника и имеющейся информации по указанной карте ДВН для Индивидуального профилактического консультирования
	 */
	function getIndiProfConsult($data) {
		$params = array('EvnPLDispDop13_id' => $data['EvnPLDispDop13_id']);
		$query = "
			select DRFC.DispRiskFactorCons_id as \"DispRiskFactorCons_id\",
				DRFC.DispRiskFactorCons_Name as \"DispRiskFactorCons_Name\",
				DPC.DispProfCons_Text as \"DispProfCons_Text\",
				DC.DispCons_id as \"DispCons_id\",
				DC.DispCons_Text as \"DispCons_Text\"
			from v_DispRiskFactorCons DRFC
			left join v_DispProfCons DPC on DPC.DispRiskFactorCons_id = DRFC.DispRiskFactorCons_id
			left join v_DispCons DC on DC.DispRiskFactorCons_id = DRFC.DispRiskFactorCons_id and DC.EvnPLDisp_id = :EvnPLDispDop13_id
		";
		$result = $this->db->query($query, $params);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 * Загрузка граничных значений для АД
	 */
	function GetArteriaPressGroundValues() {
		$query = "
			select 
				RT.RateType_id as \"RateType_id\", 
				RT.RateType_Name as \"RateType_Name\",
				LR.LabelRate_Max as \"LabelRate_Max\",
				LR.LabelRate_Min as \"LabelRate_Min\"
			from dbo.RateType RT
			left join dbo.LabelRate LR on LR.RateType_id = RT.RateType_id
			where RT.RateType_id in (53, 54)
			order by RT.RateType_id
		";

		$result = $this->db->query($query);

		if(is_object($result)) {
			$res = $result->result('array');

			if(is_array($res) && count($res) > 0)
				return array(
					'SystolicBP' => $res[0],
					'DiastolicBP' => $res[1]
				);
		}
		return false;
	}

	/**
	 * Загрузка граничных значений для ИВД
	 */
	function GetEyePressGroundValues() {
		$query = "
			select
				RT.RateType_id as \"RateType_id\", 
				RT.RateType_Name as \"RateType_Name\",
				LR.LabelRate_Max as \"LabelRate_Max\",
				LR.LabelRate_Min as \"LabelRate_Min\"
			from dbo.RateType RT
			left join dbo.LabelRate LR on LR.RateType_id = RT.RateType_id
			where RT.RateType_id in (84, 85)
			order by RT.RateType_id
		";

		$result = $this->db->query($query);

		if(is_object($result)) {
			$res = $result->result('array');

			if(is_array($res) && count($res) > 0)
				return array(
					'ODBP' => $res[0],
					'OSBP' => $res[1]
				);
		}
		return false;
	}

	/**
	 *  Типы факторов риска для меню на добавление
	 * //yl:кроме уже добавленных в обоих этапах
	 */
	function loadEvnPLDispDop13FactorType($data) {
		$stageOne = (!empty($data["EvnPLDispDop13_fid"])?"or(EvnPLDisp_id=:EvnPLDispDop13_fid)":"");
		$sql = "
			select
				RiskFactorType_id as \"RiskFactorType_id\",
				RiskFactorType_Name as \"RiskFactorType_Name\"
			from
				v_RiskFactorType
			where
				RiskFactorType_id not in(
					select drf.RiskFactorType_id from v_DispRiskFactor drf where (EvnPLDisp_id=:EvnPLDisp_id){$stageOne}
				)
			order by
				RiskFactorType_Name
		";//exit($sql);
		$result = $this->db->query($sql, array(
			"EvnPLDisp_id" => $data["EvnPLDispDop13_id"],
			"EvnPLDispDop13_fid" => $data["EvnPLDispDop13_fid"]
		));
		if (!is_object($result)) return false;$response = $result->result("array");
		return array(
			"data" => array_values($response),
			"totalCount" => count($response)
		);
	}

	/**
	 *  Факторы риска в гриде
	 * //yl:по этапам
	 */
	function loadEvnPLDispDop13FactorRisk($data) {
		$stageOne = (!empty($data["EvnPLDispDop13_fid"])?"or(EvnPLDisp_id=:EvnPLDispDop13_fid)":"");
		$sql = "
			SELECT
				drf.DispRiskFactor_id as \"DispRiskFactor_id\",
				drf.RiskFactorType_id as \"RiskFactorType_id\",
				rft.RiskFactorType_Name as \"RiskFactorType_Name\",
				to_char(DispRiskFactor_insDT, 'DD.MM.YYYY') as DispRiskFactor_insDT
			FROM v_DispRiskFactor drf
			LEFT JOIN v_RiskFactorType rft ON rft.RiskFactorType_id=drf.RiskFactorType_id
			WHERE (EvnPLDisp_id=:EvnPLDisp_id){$stageOne}
		";//exit($sql);
		$result = $this->db->query($sql, array(
			"EvnPLDisp_id" => $data["EvnPLDispDop13_id"],
			"EvnPLDispDop13_fid" => $data["EvnPLDispDop13_fid"]
		));
		if (!is_object($result)) return false;$response = $result->result("array");
		return array(
			"data" => array_values($response),
			"totalCount" => count($response)
		);
	}

	/**
	 *  Добавить Фактор риска
	 * //yl:с возвратом добавленной
	 */
	function addEvnPLDispDop13FactorRisk($data) {
		$sql = "
			SELECT
				DispRiskFactor_id as \"DispRiskFactor_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			FROM dbo.p_DispRiskFactor_ins (
				EvnPLDisp_id => :EvnPLDisp_id,
				RiskFactorType_id => :RiskFactorType_id,
				pmUser_id => :pmUser_id
			)
		";
		$result = $this->db->query($sql, array(
			"EvnPLDisp_id" => $data["EvnPLDispDop13_id"],
			"RiskFactorType_id" => $data["RiskFactorType_id"],
			"pmUser_id" => $data["pmUser_id"]
		));
		if (!is_object($result)) return false;$response = $result->result("array");
		if(!empty($response[0]["Error_Msg"])){return $response;}

		//если нет ошибок - вернём добавленную запись
		return $this->queryResult("
			select
				drf.DispRiskFactor_id as \"DispRiskFactor_id\",
				drf.RiskFactorType_id as \"RiskFactorType_id\",
				rft.RiskFactorType_Name as \"RiskFactorType_Name\", 
				to_char(DispRiskFactor_insDT, 'DD.MM.YYYY') as \"DispRiskFactor_insDT\"
			from 
				v_DispRiskFactor drf
				left join v_RiskFactorType rft on rft.RiskFactorType_id=drf.RiskFactorType_id   
			where
				DispRiskFactor_id = :DispRiskFactor_id;
			", array(
			"DispRiskFactor_id" => $response[0]["DispRiskFactor_id"]
		));
	}

	/**
	 *  Удалить Фактор риска //yl:
	 */
	function delEvnPLDispDop13FactorRisk($data){
		return $this->queryResult("
			SELECT 
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			FROM dbo.p_DispRiskFactor_del (
				DispRiskFactor_id => :DispRiskFactor_id,
				pmUser_id => :pmUser_id,
				IsRemove => 2
			)
		", array(
			"DispRiskFactor_id" => $data["DispRiskFactor_id"],
			"pmUser_id" => $data["pmUser_id"]
		));
	}

	/**
	 *  Подозрения в меню на добавление
	 * //yl:кроме уже добавленных в обоих этапах
	 */
	function loadEvnPLDispDop13DispDeseaseSuspType($data) {
		$stageOne = (!empty($data["EvnPLDispDop13_fid"])?"or(EvnPLDisp_id=:EvnPLDispDop13_fid)":"");
		$sql = "
			select
				DispDeseaseSuspType_id as \"DispDeseaseSuspType_id\",
				DispDeseaseSuspType_Name as \"DispDeseaseSuspType_Name\"
			from
				v_DispDeseaseSuspType
			where
				DispDeseaseSuspType_id not in(
					select DispDeseaseSuspDict_id from v_DispDeseaseSusp where (EvnPLDisp_id=:EvnPLDisp_id){$stageOne}
				)
			order by
				DispDeseaseSuspType_Name
		";
		$result = $this->db->query($sql, array(
			"EvnPLDisp_id" => $data["EvnPLDispDop13_id"],
			"EvnPLDispDop13_fid" => $data["EvnPLDispDop13_fid"]
		));
		if (!is_object($result)) return false;
		$response = $result->result("array");
		return array(
			"data" => array_values($response),
			"totalCount" => count($response)
		);
	}

	/**
	 *  Подозрения и Заболевания в одном гриде
	 * //yl:по этапам
	 */
	function loadEvnPLDispDop13Desease($data) {
		$stageOne_Susp = (!empty($data["EvnPLDispDop13_fid"])?"or(EvnPLDisp_id=:EvnPLDispDop13_fid)":"");
		$stageOne_Diag = (!empty($data["EvnPLDispDop13_fid"])?"or(EvnDiagDopDisp_pid=:EvnPLDispDop13_fid)":"");
		$sql = "
			SELECT --Подозрения
				DispDeseaseSusp_id as \"DispDeseaseSusp_id\",
				DispDeseaseSuspDict_id as \"DispDeseaseSuspType_id\",
				DispDeseaseSuspType_Name as \"DispDeseaseSuspType_Name\",
				null as \"Lpu_Name\",
				null as \"EvnDiagDopDisp_id\",
				null as \"DiagSetClass_id\",
				null as \"DiagSetClass_Name\",
				null as \"Diag_Code\",
				null as \"Diag_Name\",
				to_char(DispDeseaseSusp_insDT, 'DD.MM.YYYY') as \"Date_insDT\"
			FROM v_DispDeseaseSusp
			LEFT JOIN v_DispDeseaseSuspType ON DispDeseaseSuspType_id=DispDeseaseSuspDict_id
			WHERE (EvnPLDisp_id=:EvnPLDispDop13_id){$stageOne_Susp}

			union 
						
			select --Диагнозы
				null as \"DispDeseaseSusp_id\",
				null as \"DispDeseaseSuspType_id\",
				null as \"DispDeseaseSuspType_Name\",
				Lpu_Name as \"Lpu_Name\",
				EvnDiagDopDisp_id as \"EvnDiagDopDisp_id\",
				dsc.DiagSetClass_id as \"DiagSetClass_id\",
				dsc.DiagSetClass_Name as \"DiagSetClass_Name\",
				d.Diag_Code as \"Diag_Code\",
				d.Diag_Name as \"Diag_Name\",
				to_char(EvnDiagDopDisp_insDT, 'DD.MM.YYYY') as \"Date_insDT\"
			from 
				v_EvnDiagDopDisp eddd
				left join v_Diag d on d.Diag_id=eddd.Diag_id   
				left join v_DiagSetClass dsc on dsc.DiagSetClass_id=eddd.DiagSetClass_id
				left join v_Lpu on v_Lpu.Lpu_id=eddd.Lpu_id
			where
				(EvnDiagDopDisp_pid = :EvnPLDispDop13_id){$stageOne_Diag}
			
			order by \"Date_insDT\" desc
		";
		$result = $this->db->query($sql, array(
			"EvnPLDispDop13_id" => $data["EvnPLDispDop13_id"],
			"EvnPLDispDop13_fid" => $data["EvnPLDispDop13_fid"]
		));
		if (!is_object($result)) return false;$response = $result->result("array");
		return array(
			"data" => array_values($response),
			"totalCount" => count($response)
		);
	}

	/**
	 *  Добавить Подозрение
	 * //yl:с возвратом добавленной
	 */
	function addEvnPLDispDop13DispDeseaseSuspType($data) {
		$sql = "
			SELECT
				DispDeseaseSusp_id as \"DispDeseaseSusp_id\",
				Error_Code as \"Error_Code\",
				Error_Msg as \"Error_Msg\"
			FROM dbo.p_DispDeseaseSusp_ins (
				EvnPLDisp_id => :EvnPLDisp_id,
				DispDeseaseSuspDict_id => :DispDeseaseSuspType_id,
				pmUser_id => :pmUser_id,
			)
		";
		$result = $this->db->query($sql, array(
			"EvnPLDisp_id" => $data["EvnPLDispDop13_id"],
			"DispDeseaseSuspType_id" => $data["DispDeseaseSuspType_id"],
			"pmUser_id" => $data["pmUser_id"]
		));
		if (!is_object($result)) return false;$response = $result->result("array");
		if(!empty($response[0]["Error_Msg"])){return $response;}

		//если нет ошибок - вернём добавленную запись
		return $this->queryResult("
			select
				DispDeseaseSusp_id as \"DispDeseaseSusp_id\",
				DispDeseaseSuspDict_id as \"DispDeseaseSuspType_id\",
				DispDeseaseSuspType_Name as \"DispDeseaseSusp_id\",
				to_char(DispDeseaseSusp_insDT, 'DD.MM.YYYY') as \"Date_insDT\"
			from 
				v_DispDeseaseSusp
				left join v_DispDeseaseSuspType on DispDeseaseSuspType_id=DispDeseaseSuspDict_id   
			where
				DispDeseaseSusp_id = :DispDeseaseSusp_id;
			", array(
			"DispDeseaseSusp_id" => $response[0]["DispDeseaseSusp_id"]
		));
	}

	/**
	 *  Удалить Фактор риска //yl:
	 */
	function delEvnPLDispDop13DispDeseaseSusp($data){
		return $this->queryResult("
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from dbo.p_DispDeseaseSusp_del (
				DispDeseaseSusp_id => :DispDeseaseSusp_id,
				pmUser_id => :pmUser_id,
				IsRemove => 2
			)
		", array(
			"DispDeseaseSusp_id" => $data["DispDeseaseSusp_id"],
			"pmUser_id" => $data["pmUser_id"]
		));
	}


	/**
	 *  Добавить Диагноз
	 * //yl:с возвратом добавленной
	 */
	function addEvnPLDispDop13EvnDiagDopDisp($data) {
		//сначала надо проверить нет ли такого уже диагноза?
		$sql = "
			select
				EvnDiagDopDisp_id
			from 
				v_EvnDiagDopDisp
			where
				EvnDiagDopDisp_pid = :EvnPLDispDop13_id
				and Diag_id=:Diag_id
			limit 1
		";$result = $this->db->query($sql, array(
			"EvnPLDispDop13_id" => $data["EvnPLDispDop13_id"],
			"Diag_id" => $data["Diag_id"],
		));
		if (!is_object($result)) return false;$response = $result->result("array");
		if(count($response)>0){
			return [["Error_Msg" => "Такой Диагноз уже добавлен"]];
		};

		$sql = "
			SELECT
				EvnDiagDopDisp_id as \"EvnDiagDopDisp_id\",
				Error_Code as \"Error_Code\",
				Error_Msg as \"Error_Msg\"
			FROM dbo.p_EvnDiagDopDisp_ins (
				EvnDiagDopDisp_pid => :EvnPLDispDop13_id,
				Lpu_id => :Lpu_id,
				Diag_id => :Diag_id,
				DiagSetClass_id => 3,--сопутствующий
				pmUser_id => :pmUser_id,
				PersonEvn_id => :PersonEvn_id,
				Server_id => :Server_id,
			)
		";//print_r(array($data["EvnPLDispDop13_id"],$data["Lpu_id"],$data["Diag_id"],$data["pmUser_id"]));exit($sql);
		$result = $this->db->query($sql, array(
			"EvnPLDispDop13_id" => $data["EvnPLDispDop13_id"],
			"Lpu_id" => $data["Lpu_id"],
			"Diag_id" => $data["Diag_id"],
			"pmUser_id" => $data["pmUser_id"],
			"PersonEvn_id" => $data["PersonEvn_id"],
			"Server_id" => $data["Server_id"],
		));
		if (!is_object($result)) return false;$response = $result->result("array");
		if(!empty($response[0]["Error_Msg"])){return $response;}

		//если нет ошибок - вернём добавленную запись: EvnDiagDopDisp.EvnDiagDopDisp_pid = EvnPLDispDop13.EvnPLDispDop13_id
		return $this->queryResult("
			select
				Lpu_Name as \"Lpu_Name\",
				EvnDiagDopDisp_id as \"EvnDiagDopDisp_id\",
				dsc.DiagSetClass_id as \"DiagSetClass_id\",
				dsc.DiagSetClass_Name as \"DiagSetClass_Name\",
				d.Diag_Code as \"Diag_Code\",
				d.Diag_Name as \"Diag_Name\",
				convert(varchar(10),EvnDiagDopDisp_insDT,104) as \"Date_insDT\"
			from 
				v_EvnDiagDopDisp eddd
				left join v_Diag d on d.Diag_id=eddd.Diag_id   
				left join v_DiagSetClass dsc on dsc.DiagSetClass_id=eddd.DiagSetClass_id
				left join v_Lpu on v_Lpu.Lpu_id=eddd.Lpu_id
			where
				EvnDiagDopDisp_id = :EvnDiagDopDisp_id
				and
				EvnDiagDopDisp_pid = :EvnPLDispDop13_id
			", array(
			"EvnDiagDopDisp_id" => $response[0]["EvnDiagDopDisp_id"],
			"EvnPLDispDop13_id" => $data["EvnPLDispDop13_id"],
		));
	}

	/**
	 *  Удалить Диагноз //yl:
	 */
	function delEvnPLDispDop13EvnDiagDopDisp($data){
		return $this->queryResult("
			SELECT
				Error_Code as \"Error_Code\",
				Error_Msg as \"Error_Msg\",
			FROM dbo.p_EvnDiagDopDisp_del (
				EvnDiagDopDisp_id => :EvnDiagDopDisp_id,
				pmUser_id => :pmUser_id
			)
		", array(
			"EvnDiagDopDisp_id" => $data["EvnDiagDopDisp_id"],
			"pmUser_id" => $data["pmUser_id"]
		));
	}
	
	/**
	 *	ДВН6: Добавление фактора риска
	 */	
	function addEvnDiagDopDispFirst($data, $RiskFactorType_id) {
		// проверяем есть ли такой фактор уже, если нет, то добавляем
		if (!empty($RiskFactorType_id)) {
			$data['RiskFactorType_id'] = $RiskFactorType_id;
			$query = "
				select
					EvnDiagDopDisp_id as \"EvnDiagDopDisp_id\"
				from v_DispRiskFactor
				where EvnPLDisp_id = :EvnPLDisp_id and RiskFactorType_id = :RiskFactorType_id
				limit 1
			";
			
			$result = $this->db->query($query, $data);

			if (is_object($result))
			{
				$resp = $result->result('array');
				if (count($resp) > 0) {
					return false;
				}
			}

			$params = array(
				'EvnDiagDopDisp_id' => null,
				'EvnDiagDopDisp_setDate' => $data['EvnDiagDopDisp_setDate'],
				'EvnDiagDopDisp_pid' => $data['EvnPLDisp_id'],
				'Diag_id' => $data['Diag_id'],
				'DiagSetClass_id' => 1,
				'DeseaseDispType_id' => 2,
				'EvnDiagDopDisp_IsSystemDataAdd' => 2,
				'Lpu_id' => $data['Lpu_id'],
				'Server_id' => $data['Server_id'],
				'PersonEvn_id' => $data['PersonEvn_id'],
				'pmUser_id' => $data['pmUser_id']
			);
			$this->saveEvnDiagDopDisp($params);
		}
	}
	
	/**
	 *	ДВН6: Сохранение фактора риска
	 */	
	function saveDipsRiskFactor($data) {
		if (!empty($data['DipsRiskFactor_id']) && $data['DipsRiskFactor_id'] > 0) {
			$proc = "p_DipsRiskFactor_upd";
		} else {
			$proc = "p_DipsRiskFactor_ins";
		}

		$sql = "
			declare
				@DipsRiskFactor_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @DipsRiskFactor_id = :DipsRiskFactor_id;
			exec {$proc}
				@DipsRiskFactor_id = @DipsRiskFactor_id output,
				@EvnPLDisp_id = :EvnPLDisp_id,
				@RiskFactorType_id = :RiskFactorType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @DipsRiskFactor_id as DipsRiskFactor_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
			$res = $this->db->query($sql, $data);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 * ДВН6: удаление фактора риска
	 */
	function delDipsRiskFactor($data){
		return $this->queryResult("
			DECLARE
				@ErrCode int,
				@ErrMessage varchar(4000);

			EXEC dbo.p_DispRiskFactor_del
				@DispRiskFactor_id = :DispRiskFactor_id,
				@pmUser_id = :pmUser_id,
				@IsRemove = 2,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			SELECT @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		", array(
			"DispRiskFactor_id" => $data["DispRiskFactor_id"],
			"pmUser_id" => $data["pmUser_id"]
		));
	}

	/**
	 *  Типы диагнозов в комбе грида //yl:
	 */
	function loadEvnPLDispDop13DiagSetClass() {
		$sql = "
			select
				DiagSetClass_id as \"DiagSetClass_id\",
				DiagSetClass_Name as \"DiagSetClass_Name\"
			from
				v_DiagSetClass
			order by
				DiagSetClass_Name
		";//exit($sql);
		$result = $this->db->query($sql);
		if (!is_object($result)) return false;$response = $result->result("array");
		return array(
			"data" => array_values($response),
			"totalCount" => count($response)
		);
	}

	/**
	 *  Изменить тип диагноза
	 * //yl:с возвратом изменённой
	 */
	function updEvnPLDispDop13DeseaseDiagSetClass($data) {
		$sql = "update EvnDiag with (rowlock) set DiagSetClass_id = :DiagSetClass_id where EvnDiag_id = :EvnDiagDopDisp_id";
		$result = $this->db->query($sql, array(
			"EvnDiagDopDisp_id" => $data["EvnDiagDopDisp_id"],
			"DiagSetClass_id" => $data["DiagSetClass_id"],
		));//print_r($result);exit($sql);
		if(!$result)return [["Error_Msg" => "Не удалось изменить Тип Диагноза"]];

		//если нет ошибок - вернём изменённую запись
		return $this->queryResult("
			select
				Lpu_Name as \"Lpu_Name\",
				EvnDiagDopDisp_id as \"EvnDiagDopDisp_id\",
				dsc.DiagSetClass_id as \"DiagSetClass_id\",
				dsc.DiagSetClass_Name as \"DiagSetClass_Name\",
				d.Diag_Code as \"Diag_Code\",
				d.Diag_Name as \"Diag_Name\",
				convert(varchar(10),EvnDiagDopDisp_insDT,104) as \"Date_insDT\"
			from 
				v_EvnDiagDopDisp eddd
				left join v_Diag d on d.Diag_id=eddd.Diag_id   
				left join v_DiagSetClass dsc on dsc.DiagSetClass_id=eddd.DiagSetClass_id
				left join v_Lpu on v_Lpu.Lpu_id=eddd.Lpu_id
			where
				EvnDiagDopDisp_id = :EvnDiagDopDisp_id
			", array(
				"EvnDiagDopDisp_id" => $data["EvnDiagDopDisp_id"],
			)
		);
	}



	/**
	 * //yl: Осмотр фельдшером (акушеркой) или врачом акушером-гинекологом 31
	 */
	//yl:загрузить
	function loadEvnPLDispDop13GynecologistText($data) {
		$FormalizedInspection_id=$this->getFormalizedInspection_id($data);
		if(!$FormalizedInspection_id){
			return array(
				"data" => array(),
				"totalCount" => 1
			);
		}

		$sql = "select FormalizedInspection_Result from v_FormalizedInspection where FormalizedInspection_id={$FormalizedInspection_id}";
		$result = $this->db->query($sql, $data);
		if (!is_object($result)) throw new Exception("Ошибка получения FormalizedInspection");
		$resarr = $result->result("array");
		if(count($resarr)>0){
			return array(
				"data" => $resarr,
				"totalCount" => 1
			);
		}
	}
	//yl:обновить
	function saveEvnPLDispDop13GynecologistText($data) {
		$FormalizedInspection_id=$this->getFormalizedInspection_id($data,true);//создать если нет
		if(!$FormalizedInspection_id) throw new Exception("Ошибка обновления Осмотра: не найден FormalizedInspection_id");

		$FormalizedInspectionParams_id=$this->getFormalizedInspectionParams_id($data,false);
		$EvnUslugaDispDop_id=$this->getEvnUslugaDispDop_id($data,false);

		$sql = "
			select
				FormalizedInspection_id as \"FormalizedInspection_id\",
				ErrCode as \"Error_Code\",
				ErrMessage as \"Error_Msg\"
			from dbo.p_FormalizedInspection_upd (
				FormalizedInspection_id => {$FormalizedInspection_id},
				EvnUslugaDispDop_id => {$EvnUslugaDispDop_id},
				FormalizedInspectionParams_id => {$FormalizedInspectionParams_id},
				FormalizedInspection_Result => :GynecologistText,
				FormalizedInspection_DirectoryAnswer_id => 0,
				FormalizedInspection_PathologySize => 0,
				FormalizedInspection_NResult => NULL,
				pmUser_id => :pmUser_id
			)
		";//exit(getDebugSql($sql, $data));
		$result = $this->db->query($sql, $data);
		if (!is_object($result)) throw new Exception("Ошибка обновления FormalizedInspection");
		return $FormalizedInspectionParams_id;
	}
	//yl:создать FormalizedInspection
	function getFormalizedInspection_id($data,$create=false){
		$FormalizedInspectionParams_id=$this->getFormalizedInspectionParams_id($data,$create);
		$EvnUslugaDispDop_id=$this->getEvnUslugaDispDop_id($data,$create);

		if($FormalizedInspectionParams_id && $EvnUslugaDispDop_id){
			//выборка
			$data["FormalizedInspectionParams_id"]=$FormalizedInspectionParams_id;
			$data["EvnUslugaDispDop_id"]=$EvnUslugaDispDop_id;
			$sql = "select FormalizedInspection_id as \"FormalizedInspection_id\"
					from v_FormalizedInspection
					where
						FormalizedInspectionParams_id = :FormalizedInspectionParams_id
						and
						EvnUslugaDispDop_id = :EvnUslugaDispDop_id
					limit 1
			";
			$result = $this->db->query($sql, $data);
			if (!is_object($result)) throw new Exception("Ошибка получения FormalizedInspection_id");
			$resarr = $result->result("array");
			if(count($resarr)>0 && isset($resarr[0]["FormalizedInspection_id"]) && !empty($resarr[0]["FormalizedInspection_id"])){
				return $resarr[0]["FormalizedInspection_id"];
			}

			if($create){
				//создание
				$sql = "
					SELECT
						FormalizedInspection_id as \"FormalizedInspection_id\",
						Error_Code as \"Error_Code\",
						Error_Msg as \"Error_Msg\"
					FROM dbo.p_FormalizedInspection_ins (
						EvnUslugaDispDop_id => :EvnUslugaDispDop_id,
						FormalizedInspectionParams_id => :FormalizedInspectionParams_id,
						FormalizedInspection_Result => :GynecologistText,
						FormalizedInspection_DirectoryAnswer_id => 0,
						FormalizedInspection_PathologySize => 0,
						FormalizedInspection_NResult => NULL,					
						pmUser_id => :pmUser_id
					)
				";//exit(getDebugSql($sql, $data));
				$result = $this->db->query($sql, $data);
				if (!is_object($result)) throw new Exception("Ошибка создания FormalizedInspection");
				$resarr = $result->result("array");
				if(count($resarr)>0 && isset($resarr[0]["FormalizedInspection_id"]) && !empty($resarr[0]["FormalizedInspection_id"])){
					return $resarr[0]["FormalizedInspection_id"];
				}else{
					throw new Exception("Ошибка при создании FormalizedInspection - не вернулся id");
				};
			}

		}elseif(create){
			throw new Exception("Ошибка создания FormalizedInspection - не хватает параметров");
		}
	}
	//yl:создать FormalizedInspectionParams
	function getFormalizedInspectionParams_id($data,$create=false){
		//выборка
		$sql = "select FormalizedInspectionParams_id 
			from FormalizedInspectionParams 
			where SurveyType_id=:SurveyType_id
			limit 1";
		$result = $this->db->query($sql, array(
			"SurveyType_id" => $data["SurveyType_id"],
		));
		if (!is_object($result)) throw new Exception("Ошибка получения FormalizedInspectionParams_id");
		$resarr = $result->result("array");
		if(count($resarr)>0 && isset($resarr[0]["FormalizedInspectionParams_id"]) && !empty($resarr[0]["FormalizedInspectionParams_id"])){
			return $resarr[0]["FormalizedInspectionParams_id"];
		}elseif($create == false) {
			return false;
		}

		//создание
		$sql = "
			select FormalizedInspectionParams_id as \"FormalizedInspectionParams_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from dbo.p_FormalizedInspectionParams_ins (
				SurveyType_id := :SurveyType_id,
				FormalizedInspectionParams_Name := 'Осмотр',
				@FormalizedInspectionParams_IsDefault = 0,
				@FormalizedInspectionParams_Directory = '',
				@pmUser_id = :pmUser_id,
			)
		";
		//exit(getDebugSql($sql, $data));
		$result = $this->db->query($sql, array(
			"SurveyType_id" => $data["SurveyType_id"],
			"pmUser_id" => $data["pmUser_id"]
		));
		if (!is_object($result)) throw new Exception("Ошибка создания FormalizedInspectionParams");
		$resarr = $result->result("array");
		if(count($resarr)>0 && isset($resarr[0]["FormalizedInspectionParams_id"]) && !empty($resarr[0]["FormalizedInspectionParams_id"])){
			return $resarr[0]["FormalizedInspectionParams_id"];
		}else{
			throw new Exception("Ошибка при создании FormalizedInspectionParams - не вернулся id");
		};
	}
	//yl:создать EvnUslugaDispDop
	function getEvnUslugaDispDop_id($data,$create=false){
		//пришло
		if(isset($data["EvnUslugaDispDop_id"]) && !empty($data["EvnUslugaDispDop_id"])){
			return $data["EvnUslugaDispDop_id"];//откуда??
		}

		//выборка
		$sql = "
			select EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\"
			from v_EvnUslugaDispDop
			where 
				EvnUslugaDispDop_rid=:EvnPLDispDop13_id
				and
				UslugaComplex_id=:UslugaComplex_id
		";//exit(getDebugSql($sql, $data));
				/* по идее надо ещё добавить, но они при создании не вставляются почему-то ... хотя я их передаю
				and
				DopDispInfoConsent_id=:DopDispInfoConsent_id
				and
				SurveyType_id=:SurveyType_id
				*/
		$result = $this->db->query($sql, array(
			"EvnPLDispDop13_id" => $data["EvnPLDispDop13_id"],
			"DopDispInfoConsent_id" => $data["DopDispInfoConsent_id"],
			"SurveyType_id" => $data["SurveyType_id"],
			"UslugaComplex_id" => $data["UslugaComplex_id"],
		));
		if (!is_object($result)) throw new Exception("Ошибка получения EvnUslugaDispDop_id");
		$resarr = $result->result("array");
		if(count($resarr) > 0 && isset($resarr[0]["EvnUslugaDispDop_id"]) && !empty($resarr[0]["EvnUslugaDispDop_id"])) {
			return $resarr[0]["EvnUslugaDispDop_id"];
		}elseif($create == false) {
			return false;
		};

	}
	
	/**
	 * Проверка наличия направления на обследование/консультацию
	 */
	function checkZnoDirectionExt6($data, $sysNick)
	{
		if (
			!empty($data["{$sysNick}_id"]) &&
			!empty($sysNick) &&
			(!empty($data["{$sysNick}_disDate"]) || !empty($data["{$sysNick}_disDT"])) &&
			$data["{$sysNick}_IsSuspectZNO"] == 2
		) {
			// направлен на консультацию
			$EvnDirection_id = $this->getFirstResultFromQuery('
				select EvnDirection_id 
				from v_EvnDirection_all ed
				inner join v_EvnPLDisp epd on ed.EvnDirection_rid = epd.EvnPLDisp_id
				where 
					ed.DirType_id in (3,16) and 
					ed.EvnStatus_id not in (12,13) and 
					epd.EvnPLDisp_id = ?
				limit 1
			', array($data["{$sysNick}_id"]));
			
			if (!$EvnDirection_id) {
				throw new Exception('При подозрении на ЗНО должно быть выписано направление на дообследование с типом «на консультацию» или «на поликлинический прием». Добавьте направление в разделе «Направления на исследования».');
			}
			
			// направлен на обследование
			$EvnDirection_id = $this->getFirstResultFromQuery('
				select EvnDirection_id as \"EvnDirection_id\"
				from v_EvnDirection_all ed
				inner join v_EvnPLDisp epd on ed.EvnDirection_rid = epd.EvnPLDisp_id
				where 
					ed.DirType_id = 10 and 
					ed.EvnStatus_id not in (12,13) and 
					epd.EvnPLDisp_id = ?
				limit 1
			', array($data["{$sysNick}_id"]));
			
			if (!$EvnDirection_id) {
				throw new Exception('При подозрении на ЗНО должно быть выписано направление на дообследование с типом «на исследование». Добавьте направление в разделе «Направления на исследования».');
			}
		}
	}
	
	/**
	 * Проверка на наличие подозрений в двн
	 */
	function checkDesease($data) {
		
		$alertDesease = array();
		
		if($data["EvnPLDispDop13_IsSuspectZNO"] == 2) {
			
			$diag_zno_name = $this->getFirstResultFromQuery("
				select Di.Diag_Name as \"Diag_Name\"
				from v_EvnPLDispDop13 EPL
				left join v_Diag Di on Di.Diag_id = EPL.Diag_spid
				where EPL.EvnPLDispDop13_id = :EvnPLDispDop13_id
				limit 1
			", $data);
			$alertDesease[] = 'Подозрение на ЗНО: '.$diag_zno_name;
		}
		
		$sql = "
			select
				DispDeseaseSuspType_Name as \"DispDeseaseSuspType_Name\"
			from
				v_DispDeseaseSusp DDS
				left join v_DispDeseaseSuspType DDST on DDST.DispDeseaseSuspType_id = DDS.DispDeseaseSuspDict_id
			where
				EvnPLDisp_id=:EvnPLDispDop13_id
			order by
				DispDeseaseSuspType_Name
		";
		$desease = $this->queryResult($sql, $data);

		foreach($desease as $des) {
			$alertDesease[]= $des['DispDeseaseSuspType_Name'];
		}
		return $alertDesease;
	}
	
	/**
	 * Завершение диспансеризации
	 * Ext6
	 */
	function saveEvnPLDispDop13Ext6($data) {
		$this->db->trans_begin();

		$savedData = array();
		if (!empty($data['EvnPLDispDop13_id'])) {
			$savedData = $this->getFirstRowFromQuery("
		  		select * 
		  		from v_EvnPLDispDop13
		  		where EvnPLDispDop13_id = :EvnPLDispDop13_id
		  		limit 1
			", $data, true);
			if ($savedData === false) {
				$this->db->trans_rollback();
				return array('Error_Msg' => 'Ошибка при получении данных карты');
			}
		}
		
		$checkResult = $this->checkPersonData($data);
		If ( empty($checkResult['PersonEvn_id']) ) {
			$this->db->trans_rollback();
			return $checkResult;
		}
		
		if ($data['EvnPLDispDop13_IsTwoStage'] == 2 && $data['EvnPLDispDop13_IsEndStage'] != 2) {
			return array('Error_Msg' => 'На второй этап диспансеризации могут быть переведены только пациенты, окончившие первый этап');
		}

		if (!empty($data['EvnPLDispDop13_id']) && ($data['EvnPLDispDop13_IsEndStage'] != 2 || $data['EvnPLDispDop13_IsTwoStage'] != 2)) {
			// проверяем наличие карты 2 этапа, если есть не даём сохранить.
			$resp = $this->queryResult("
				select EvnPLDispDop13_id as \"EvnPLDispDop13_id\" 
				from v_EvnPLDispDop13 
				where EvnPLDispDop13_fid = :EvnPLDispDop13_fid
			", array(
				'EvnPLDispDop13_fid' => $data['EvnPLDispDop13_id']
			));

			if (!empty($resp[0]['EvnPLDispDop13_id'])) {
				$this->db->trans_rollback();
				return array('Error_Msg' => 'У пациента имеется карта 2 этапа, нельзя сохранить карту 1 этапа без полей "1 этап закончен" и "Направлен на 2 этап диспансеризации"');
			}
		}
		
		if ($data['EvnPLDispDop13_IsMobile']) { $data['EvnPLDispDop13_IsMobile'] = 2; } else { $data['EvnPLDispDop13_IsMobile'] = 1; }
		if ($data['EvnPLDispDop13_IsOutLpu']) { $data['EvnPLDispDop13_IsOutLpu'] = 2; } else { $data['EvnPLDispDop13_IsOutLpu'] = 1; }
	
		$minmaxdates = $this->getEvnUslugaDispDopMinMaxDates($data);
		if (is_array($minmaxdates)) {
			$data['EvnPLDispDop13_disDate'] = $minmaxdates['maxdate'];
		} else {
			$data['EvnPLDispDop13_disDate'] = date('Y-m-d');
		}

		// если не закончен дата окончания нулевая.
		if (empty($data['EvnPLDispDop13_IsEndStage']) || $data['EvnPLDispDop13_IsEndStage'] == 1) {
			$data['EvnPLDispDop13_disDate'] = NULL;
			$data['EvnPLDispDop13_IsTwoStage'] = NULL; // не направлен на 2 этап, раз первый не закончен.
		}
		
		$data['MedStaffFact_id'] = !empty($data['session']['CurARM']['MedStaffFact_id'])?$data['session']['CurARM']['MedStaffFact_id']:null;

		//$this->checkZnoDirectionExt6($data, 'EvnPLDispDop13');
		/*
		if($data['ignoreCheckDesease']=='2') {
			$alertDesease = array();
			
			if($data["EvnPLDispDop13_IsSuspectZNO"] == 2) {
				
				$diag_zno_name = $this->getFirstResultFromQuery("
					select top 1 Di.Diag_Name 
					from v_EvnPLDispDop13 EPL (nolock)
					left join v_Diag Di (nolock) on Di.Diag_id = EPL.Diag_spid
					where EPL.EvnPLDispDop13_id = :EvnPLDispDop13_id
				", $data);
				$alertDesease[] = 'Подозрение на ЗНО: '.$diag_zno_name;
			}
			
			$desease = $this->checkDesease($data);
			foreach($desease as $des) {
				$alertDesease[]= $des['DispDeseaseSuspType_Name'];
			}
			//$alertDesease = implode(',<br>', $alertDesease);
			if(count($alertDesease)>0)
			throw new Exception('Внимание! У пациента есть:<br>'.implode(',<br>', $alertDesease).'.<br>Вы действительно хотите завершить диспансеризацию?');
		}*/
		
		$setDtField = "(select cte.EvnPLDispDop13_consDate from cte)";
		$query = "
			with cte as (
				select
					--dbo.tzGetDate() as curdate,
					EvnPLDispDop13_IsRefusal,
					EvnDirection_aid,
					EvnPLDispDop13_consDT as EvnPLDispDop13_consDate
				from v_EvnPLDispDop13
				where EvnPLDispDop13_id = :EvnPLDispDop13_id
				limit 1
			)
			select
				EvnPLDispDop13_id as \"EvnPLDispDop13_id\",
				to_char(cte.EvnPLDispDop13_consDate, 'YYYY-MM-DD') as \"EvnPLDispDop13_setDT\"
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from
				p_EvnPLDispDop13_upd (
					EvnPLDispDop13_id := :EvnPLDispDop13_id,
					MedStaffFact_id := :MedStaffFact_id,
					EvnPLDispDop13_IsNewOrder := EvnPLDispDop13_IsNewOrder,
					EvnPLDispDop13_IndexRep := :EvnPLDispDop13_IndexRep,
					EvnPLDispDop13_IndexRepInReg := :EvnPLDispDop13_IndexRepInReg,
					PersonEvn_id := :PersonEvn_id,
					EvnPLDispDop13_setDT := {$setDtField},
					EvnPLDispDop13_disDT := :EvnPLDispDop13_disDate,
					Server_id := :Server_id,
					Lpu_id := :Lpu_id,
					DispClass_id := :DispClass_id,
					PayType_id := :PayType_id,
					EvnPLDispDop13_fid := :EvnPLDispDop13_fid,
					AttachType_id := 2,
					EvnPLDispDop13_IsStenocard := :EvnPLDispDop13_IsStenocard,
					EvnPLDispDop13_IsShortCons := :EvnPLDispDop13_IsShortCons,
					EvnPLDispDop13_IsBrain := :EvnPLDispDop13_IsBrain,
					EvnPLDispDop13_IsDoubleScan := :EvnPLDispDop13_IsDoubleScan,
					EvnPLDispDop13_IsTub := :EvnPLDispDop13_IsTub,
					EvnPLDispDop13_IsTIA := :EvnPLDispDop13_IsTIA,
					EvnPLDispDop13_IsRespiratory := :EvnPLDispDop13_IsRespiratory,
					EvnPLDispDop13_IsLungs := :EvnPLDispDop13_IsLungs,
					EvnPLDispDop13_IsTopGastro := :EvnPLDispDop13_IsTopGastro,
					EvnPLDispDop13_IsBotGastro := :EvnPLDispDop13_IsBotGastro,
					EvnPLDispDop13_IsSpirometry := :EvnPLDispDop13_IsSpirometry,
					EvnPLDispDop13_IsHeartFailure := :EvnPLDispDop13_IsHeartFailure,
					EvnPLDispDop13_IsOncology := :EvnPLDispDop13_IsOncology,
					EvnPLDispDop13_IsEsophag := :EvnPLDispDop13_IsEsophag,
					EvnPLDispDop13_IsSmoking := :EvnPLDispDop13_IsSmoking,
					EvnPLDispDop13_IsRiskAlco := :EvnPLDispDop13_IsRiskAlco,
					EvnPLDispDop13_IsAlcoDepend := :EvnPLDispDop13_IsAlcoDepend,
					EvnPLDispDop13_IsLowActiv := :EvnPLDispDop13_IsLowActiv,
					EvnPLDispDop13_IsIrrational := :EvnPLDispDop13_IsIrrational,
					EvnPLDispDop13_IsUseNarko := :EvnPLDispDop13_IsUseNarko,
					Diag_id := :Diag_id,
					Diag_sid := :Diag_sid,
					EvnPLDispDop13_IsDisp := :EvnPLDispDop13_IsDisp,
					NeedDopCure_id := :NeedDopCure_id,
					EvnPLDispDop13_IsStac := :EvnPLDispDop13_IsStac,
					EvnPLDispDop13_IsSanator := :EvnPLDispDop13_IsSanator,
					EvnPLDispDop13_SumRick := :EvnPLDispDop13_SumRick,
					RiskType_id := :RiskType_id,
					EvnPLDispDop13_IsSchool := :EvnPLDispDop13_IsSchool,
					EvnPLDispDop13_IsProphCons := :EvnPLDispDop13_IsProphCons,
					EvnPLDispDop13_IsHypoten := :EvnPLDispDop13_IsHypoten,
					EvnPLDispDop13_IsLipid := :EvnPLDispDop13_IsLipid,
					EvnPLDispDop13_IsHypoglyc := :EvnPLDispDop13_IsHypoglyc,
					HealthKind_id := :HealthKind_id,
					EvnPLDispDop13_IsEndStage := :EvnPLDispDop13_IsEndStage,
					EvnPLDispDop13_IsFinish := :EvnPLDispDop13_IsEndStage,
					EvnPLDispDop13_IsTwoStage := :EvnPLDispDop13_IsTwoStage,
					EvnPLDispDop13_consDT := (SELECT EvnPLDispDop13_consDate from cte),
					EvnPLDispDop13_IsMobile := :EvnPLDispDop13_IsMobile, 
					EvnPLDispDop13_IsOutLpu := :EvnPLDispDop13_IsOutLpu, 
					Lpu_mid := :Lpu_mid,
					CardioRiskType_id := :CardioRiskType_id,
					EvnPLDispDop13_IsRefusal := (SELECT EvnPLDispDop13_IsRefusal from cte),
					EvnDirection_aid := (SELECT EvnDirection_aid from cte),
					EvnPLDispDop13_Percent := :EvnPLDispDop13_Percent,
					EvnPLDispDop13_IsSuspectZNO := :EvnPLDispDop13_IsSuspectZNO,
					Diag_spid := :Diag_spid,
					pmUser_id := :pmUser_id
				)
		";
		//~ exit(getDebugSQL($query, $data));
		$result = $this->db->query($query, $data);
		
        if ( !is_object($result) ) {
			$this->db->trans_rollback();
			return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение карты ДД)');
		}

		$resp = $result->result('array');

		if ( empty($resp[0]['EvnPLDispDop13_id']) ) {
			$this->db->trans_rollback();
			return array('Error_Msg' => 'Ошибка при сохранении карты ДВН');
		}
		else if ( !empty($resp[0]['Error_Msg']) ) {
			$this->db->trans_rollback();
			return array('Error_Msg' => 'Ошибка при сохранении карты ДВН');
		}

		$data['EvnPLDispDop13_id'] = $resp[0]['EvnPLDispDop13_id'];
		$data['EvnPLDispDop13_setDT'] = $resp[0]['EvnPLDispDop13_setDT'];
		
		$this->db->trans_commit();

		return $resp;
	}
	
	/**
	 * Сохранить ССР
	 */
	function saveEvnPLDispDop13_SumRick($data) {
		
		// 1. читаем необходимые параметры
		$query = "
			select
				dbo.Age2(PS.Person_BirthDay, cast(YEAR(EPLD.EvnPLDisp_consDT)::varchar || '-12-31' as date)) as \"Person_Age\",
				PS.Sex_id as \"Sex_id\",
				case 
					when DDQkur.value = 3 then 1
					when DDQkur.value is null and DD13kur.value13 is null then DP.valueP
					else case when COALESCE(DDQkur.value,0) = 0 then DD13kur.value13 else DDQkur.value end 
				end as \"EvnPLDisp_IsSmoking\",
				USsys.value as \"systolic_blood_pressure\",
				USchol.value as \"total_cholesterol\"
			from
				v_EvnPLDisp EPLD
				left join v_PersonState PS on ps.Person_id = EPLD.Person_id
				LEFT JOIN LATERAL(
					select 
						DopDispQuestion_ValuesStr as value 
					from
					 	DopDispQuestion DDQ
					 	left join v_QuestionType QT on QT.QuestionType_id = DDQ.QuestionType_id
					where
						QT.QuestionType_Name='Курите ли Вы? (курение одной и более сигарет в день)'
						and QT.DispClass_id =EPLD.DispClass_id
						and COALESCE(QT.QuestionType_begDate, EPLD.EvnPLDisp_consDT) <= EPLD.EvnPLDisp_consDT
						and COALESCE(QT.QuestionType_endDate, EPLD.EvnPLDisp_consDT) >= EPLD.EvnPLDisp_consDT
						and COALESCE(QT.QuestionType_AgeFrom, Person_Age) <= Person_Age
						and COALESCE(QT.QuestionType_AgeTo, Person_Age) >= Person_Age
					 	and DDQ.EvnPLDisp_id = EPLD.EvnPLDisp_id
				) DDQkur on true
				LEFT JOIN LATERAL(
					select DD13.EvnPLDispDop13_IsSmoking as value13 from v_EvnPLDispDop13 DD13 where DD13.EvnPLDispDop13_id = EPLD.EvnPLDisp_id
				) DD13kur on true
				LEFT JOIN LATERAL(
					select EvnPLDispProf_IsSmoking as valueP
					from v_EvnPLDispProf
					where EvnPLDispProf_id = EPLD.EvnPLDisp_id		
				)DP on true
				LEFT JOIN LATERAL(
					select 
						CASE RVT.RateValueType_SysNick
							WHEN 'int' THEN cast(cast(FI.FormalizedInspection_NResult as numeric(16)) as varchar)
							WHEN 'float' THEN cast(cast(FI.FormalizedInspection_NResult as numeric(16,3)) as varchar)
							WHEN 'string' THEN FI.FormalizedInspection_Result
							WHEN 'template' THEN FI.FormalizedInspection_Result
							WHEN 'reference' THEN cast(FI.FormalizedInspection_DirectoryAnswer_id as varchar)
							WHEN 'datetime' THEN to_char(FI.FormalizedInspection_Result, 'DD.MM.YYYY')
						END as value
					from
						v_EvnVizitDispDop evdd
						left join v_EvnUslugaDispDop eudd on eudd.EvnUslugaDispDop_pid = evdd.EvnVizitDispDop_id
						left join v_FormalizedInspection FI on FI.EvnUslugaDispDop_id = eudd.EvnUslugaDispDop_id
						left join v_FormalizedInspectionParams FIP on FIP.FormalizedInspectionParams_id = FI.FormalizedInspectionParams_id
						left join RateValueType RVT on RVT.RateValueType_id = FIP.RateValueType_id
					where 
						evdd.EvnVizitDispDop_pid = EPLD.EvnPLDisp_id and FIP.FormalizedInspectionParams_SysNick = 'systolic_blood_pressure'
				) USsys on true
				LEFT JOIN LATERAL(
					select 
						CASE RVT.RateValueType_SysNick
							WHEN 'int' THEN cast(cast(FI.FormalizedInspection_NResult as numeric(16)) as varchar)
							WHEN 'float' THEN cast(cast(FI.FormalizedInspection_NResult as numeric(16,3)) as varchar)
							WHEN 'string' THEN FI.FormalizedInspection_Result
							WHEN 'template' THEN FI.FormalizedInspection_Result
							WHEN 'reference' THEN cast(FI.FormalizedInspection_DirectoryAnswer_id as varchar)
							WHEN 'datetime' THEN to_char(FI.FormalizedInspection_Result, 'DD.MM.YYYY')
						END as value
					from
						v_EvnVizitDispDop evdd
						left join v_EvnUslugaDispDop eudd on eudd.EvnUslugaDispDop_pid = evdd.EvnVizitDispDop_id
						left join v_FormalizedInspection FI on FI.EvnUslugaDispDop_id = eudd.EvnUslugaDispDop_id
						left join v_FormalizedInspectionParams FIP on FIP.FormalizedInspectionParams_id = FI.FormalizedInspectionParams_id
						left join RateValueType RVT on RVT.RateValueType_id = FIP.RateValueType_id
					where 
						evdd.EvnVizitDispDop_pid = EPLD.EvnPLDisp_id and FIP.FormalizedInspectionParams_SysNick = 'total_cholesterol'
				) USchol on true
			where
				EPLD.EvnPLDisp_id = :EvnPLDispDop13_id
			limit 1
		";
		//exit(getDebugSQL($query, $data));
		$result = $this->db->query($query, $data);
		
		if (is_object($result))
		{
			$resp = $result->result('array');
			if (count($resp) > 0) {
				$data['systolic_blood_pressure'] = $resp[0]['systolic_blood_pressure'];
				$data['Person_Age'] = $resp[0]['Person_Age'];
				$data['total_cholesterol'] = $resp[0]['total_cholesterol'];
				$data['Sex_id'] = $resp[0]['Sex_id'];
				$data['EvnPLDisp_IsSmoking'] = $resp[0]['EvnPLDisp_IsSmoking'];
			}
		}
		
		$errors = array();
		if (empty($data['systolic_blood_pressure']))
		{
			$errors[] = 'давление';
		}
		if (empty($data['Person_Age']))
		{
			$errors[] = 'возраст';
		}
		if (empty($data['total_cholesterol']))
		{
			$errors[] = 'холестерин';
		}
		if (empty($data['Sex_id']))
		{
			$errors[] = 'пол';
		}
		if (empty($data['EvnPLDisp_IsSmoking']))
		{
			$errors[] = 'курение';
		}		
		if (count($errors) > 0) {
			return array('success' => false, 'Error_Msg' => 'Не указаны необходимые параметры для расчёта: '.implode($errors,','));
			return array('success' => false);
		}
		$scorevalue = null;
		// 2. запрос значения SCORE только при всех заданных параметрах
		if (!empty($data['systolic_blood_pressure']) && !empty($data['Person_Age']) && !empty($data['total_cholesterol']) && !empty($data['Sex_id']) && !empty($data['EvnPLDisp_IsSmoking'])) {
			$query = "
				select
					ScoreValues_Values as \"ScoreValues_Values\"
				from
					v_ScoreValues
				where
					(cast (:systolic_blood_pressure as float) BETWEEN COALESCE(ScoreValues_MinPress,0) and COALESCE(ScoreValues_MaxPress,900)) and
					(:Person_Age BETWEEN COALESCE(ScoreValues_AgeFrom,0) and COALESCE(ScoreValues_AgeTo,900)) and
					(cast (:total_cholesterol as float) BETWEEN COALESCE(ScoreValues_MinChol,0) and COALESCE(ScoreValues_MaxChol,900)) and
					:Sex_id = COALESCE(Sex_id, :Sex_id) and
					:EvnPLDisp_IsSmoking = ScoreValues_IsSmoke 
			";
			//exit(getDebugSQL($query, $data));
			$result = $this->db->query($query, $data);
			
			if (is_object($result))
			{
				$resp = $result->result('array');
				
				if (count($resp) > 0) {
					$scorevalue = $resp[0]['ScoreValues_Values'];
				}
			}
		}
		if(!isset($scorevalue)) {
			return array('success' => false, 'Error_Msg' => 'Не удалось рассчитать значение сердечно-сосудистого риска');
		} else {
			$RiskType_id = 4;
			if($scorevalue<1) $RiskType_id = 1;
			else if($scorevalue >= 1 && $scorevalue < 5) $RiskType_id = 2;
			else if($scorevalue >= 5 && $scorevalue < 10) $RiskType_id = 3;
						
			$query = "
				update EvnPLDispDop13
				set EvnPLDispDop13_SumRick = :EvnPLDispDop13_SumRick, 
					RiskType_id = :RiskType_id
				where Evn_id = :EvnPLDispDop13_id
			";
			$params = array(
				'EvnPLDispDop13_id'=>$data['EvnPLDispDop13_id'],
				'EvnPLDispDop13_SumRick' => $scorevalue,
				'RiskType_id' => $RiskType_id
			);
			$this->db->query($query, $params);
			
			return array('success' => true, 'SCORE' => $scorevalue, 'RiskType_id' => $RiskType_id );
		}
	}
	
	/**
	 * Сохранение анкетирования в ext6
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function saveDopDispQuestions($data)
	{
		// Стартуем транзакцию
		$this->db->trans_begin();
		// получаем данные о карте ДД
		$dd = $this->getEvnPLDispDop13Data($data);
		if (empty($dd)) {
			$this->db->trans_rollback();
			throw new Exception("Ошибка получения данных карты диспансеризации");
		}
		$data["PersonEvn_id"] = $dd["PersonEvn_id"];
		$data["Server_id"] = $dd["Server_id"];
		$sql = "
			select
				evdd.EvnVizitDispDop_id,
			    eudd.EvnUslugaDispDop_id,
			    stl.DispClass_id,
			    st.SurveyType_Code
			from
				v_EvnUslugaDispDop eudd
				inner join v_EvnVizitDispDop evdd on evdd.EvnVizitDispDop_id = eudd.EvnUslugaDispDop_pid
				inner join v_DopDispInfoConsent ddic on ddic.DopDispInfoConsent_id = evdd.DopDispInfoConsent_id
				inner join v_SurveyTypeLink stl on stl.SurveyTypeLink_id = ddic.SurveyTypeLink_id
				inner join v_SurveyType st on st.SurveyType_id = stl.SurveyType_id
			where evdd.EvnVizitDispDop_pid = :EvnVizitDispDop_pid
			  and st.SurveyType_Code in (19,27)
			  and cast(eudd.EvnUslugaDispDop_didDT as date) < :DopDispQuestion_setDate
			limit 1
		";
		$sqlParams = [
			"EvnVizitDispDop_pid" => $data["EvnPLDisp_id"],
			"DopDispQuestion_setDate" => $data["DopDispQuestion_setDate"]
		];
		/**
		 * @var CI_DB_result $res
		 * @var CI_DB_result $result
		 */
		$res = $this->db->query($sql, $sqlParams);
		if (is_object($res)) {
			$resp = $res->result_array();
			if (is_array($resp) && count($resp) > 0) {
				$this->db->trans_rollback();
				if ($resp[0]["SurveyType_Code"] == 19) {
					throw new Exception("Дата любого осмотра / исследования не может быть больше даты осмотра врача-терапевта (ВОП).");
				} else {
					throw new Exception("Дата любого осмотра / исследования не может быть больше даты осмотра врача-педиатра (ВОП).");
				}
			}
		}
		// Нужно сохранять услугу по анкетированию (refs #20465)
		// Ищем услугу с UslugaComplex_id для SurveyType_Code = 2, если нет то создаём новую, иначе обновляем.
		$query = "
			select
				STL.UslugaComplex_id as \"UslugaComplex_id\", -- услуга которую нужно сохранить
				EUDDData.EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\"
			from
				v_SurveyTypeLink STL
				inner join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
				inner join v_DopDispInfoConsent ddic on ddic.SurveyTypeLink_id = stl.SurveyTypeLink_id
				left join lateral (
					select EvnUslugaDispDop_id
					from v_EvnUslugaDispDop EUDD
					where EUDD.EvnUslugaDispDop_pid = :EvnPLDisp_id
					  and EUDD.UslugaComplex_id IN (select UslugaComplex_id from v_SurveyTypeLink where SurveyType_id = STL.SurveyType_id)
					  and COALESCE(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
					limit 1
				) EUDDData on true
			where ST.SurveyType_Code = 2
				and ddic.EvnPLDisp_id = :EvnPLDisp_id
			limit 1
		";
		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			$this->db->trans_rollback();
			throw new Exception("Ошибка при выполнении запроса к базе данных (получение идентификатора услуги)");
		}
		$resp = $result->result_array();
		if (!is_array($resp) || count($resp) == 0) {
			$this->db->trans_rollback();
			throw new Exception("Ошибка при получении идентификатора услуги");
		}
		// Сохраняем услугу
		$data["EvnUslugaDispDop_id"] = (!empty($resp[0]["EvnUslugaDispDop_id"])) ? $resp[0]["EvnUslugaDispDop_id"] : null;
		$proc = (!empty($resp[0]["EvnUslugaDispDop_id"])) ? "p_EvnUslugaDispDop_upd" : "p_EvnUslugaDispDop_ins";
		$data["UslugaComplex_id"] = $resp[0]["UslugaComplex_id"];
		$query = "
			with cte as(
				select PayType_id from v_PayType where PayType_SysNick = 'dopdisp'
			)
			select
				EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\",
				error_code as \"Error_Code\",
				error_message as \"Error_Msg\"
			from {$proc} (
				EvnUslugaDispDop_id := :EvnUslugaDispDop_id,
				EvnUslugaDispDop_pid := :EvnPLDisp_id,
				Lpu_id := :Lpu_id,
				Server_id := :Server_id,
				EvnDirection_id := NULL,
				PersonEvn_id := :PersonEvn_id,
				PayType_id := (select PayType_id from cte),
				UslugaPlace_id := 1,
				EvnUslugaDispDop_setDT := NULL,
				UslugaComplex_id := :UslugaComplex_id,
				EvnUslugaDispDop_didDT := :DopDispQuestion_setDate,
				ExaminationPlace_id := NULL,
				Diag_id := :Diag_id,
				DopDispDiagType_id := :DopDispDiagType_id,
				EvnUslugaDispDop_DeseaseStage := :DeseaseStage,
				LpuSection_uid := :LpuSection_uid,
				MedPersonal_id := :MedPersonal_id,
				MedStaffFact_id := :MedStaffFact_id,
				EvnUslugaDispDop_ExamPlace := NULL,
				EvnPrescrTimetable_id := null,
				EvnPrescr_id := null,
				pmUser_id := :pmUser_id
			)
		";
		
		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			$this->db->trans_rollback();
			throw new Exception("Ошибка при выполнении запроса к базе данных (сохранение услуги)");
		}
		$resp = $result->result_array();
		if (!is_array($resp) || count($resp) == 0) {
			$this->db->trans_rollback();
			throw new Exception("Ошибка при сохранении услуги");
		} else if (!empty($resp[0]["Error_Msg"])) {
			$this->db->trans_rollback();
			throw new Exception($resp[0]["Error_Msg"]);
		}
		if (empty($resp[0]["EvnUslugaDispDop_id"])) {
			$this->db->trans_rollback();
			throw new Exception("Ошибка при сохранении услуги");
		}
		$data["EvnUslugaDispDop_id"] = $resp[0]["EvnUslugaDispDop_id"];

		ConvertFromWin1251ToUTF8($data["DopDispQuestionData"]);
		$items = json_decode($data["DopDispQuestionData"], true);
		$this->load->model("EvnDiagDopDisp_model", "evndiagdopdisp");
		$this->load->model("HeredityDiag_model", "hereditydiag");
		$this->load->model("ProphConsult_model", "prophconsult");
		$this->load->model("NeedConsult_model", "needconsult");
		
		$PersonAgeData = $this->getFirstRowFromQuery("
			SELECT
				PS.Sex_id as \"Sex_id\",
				dbo.Age2(PS.Person_BirthDay, dbo.tzgetdate()) as \"Person_Age\"
			FROM v_EvnPLDisp epld
				inner join v_PersonState PS on PS.Person_id = epld.Person_id
			WHERE epld.EvnPLDisp_id = :EvnPLDisp_id
			LIMIT 1
		", [
			'EvnPLDisp_id' => $data['EvnPLDisp_id']
		]);
		
		$Sex_id = $PersonAgeData["Sex_id"];
		$Person_Age = $PersonAgeData["Person_Age"];
		
		// Получаем существующие данные из БД
		$ExistingDopDispQuestionData = [];

		$query = "
			select
				QuestionType_id as \"QuestionType_id\",
			    DopDispQuestion_id as \"DopDispQuestion_id\",
			    DopDispQuestion_ValuesStr as \"DopDispQuestion_ValuesStr\"
			from v_DopDispQuestion
			where EvnPLDisp_id = :EvnPLDisp_id
		";
		$result = $this->db->query($query, ["EvnPLDisp_id" => $data["EvnPLDisp_id"]]);
		if (!is_object($result)) {
			$this->db->trans_rollback();
			throw new Exception("Ошибка при выполнении запроса к базе данных (получение списка имеющихся данных анкетирования)");
		}
		$resp = $result->result_array();
		if (is_array($resp) && count($resp) > 0) {
			foreach ($resp as $dataArray) {
				if ($dataArray["QuestionType_id"] == 8 && !empty($data["NeedCalculation"]) && $data["NeedCalculation"] == 1) {
					$this->evndiagdopdisp->delEvnDiagDopDispBefore($data, $dataArray["DopDispQuestion_ValuesStr"]);
				}
				$ExistingDopDispQuestionData[$dataArray["QuestionType_id"]] = $dataArray["DopDispQuestion_id"];
			}
		}
		$data["EvnDiagDopDisp_setDate"] = $data["DopDispQuestion_setDate"];
		$dataToUpdate = [
			"ProphConsult" => [],
			"NeedConsult" => [],
			"DispRiskFactor" => [], //в раздел факторы риска
			"DispDeseaseSusp" => [] //в раздел заболевания
		];
		
		$useCounterOne = false;
		$counterOne = 0;
		$usePohud = false;
		$isPohud = false;
		$isPohudDepend = false;
		$isPohudDependTwo = false;
		$usePoorNutrition = false;
		$isPoorNutrition = false;
		$isPoorNutritionTwo = false;
		$useOnko = false;
		$isOnko = false;
		$isOnkoTwo = false;
		$isOnkoThree = false;
		$useHirurg = false;
		$isHirurg = false;
		$isHirurgTwo = false;
		$isHirurgThree = false;
		$useAlco = false;
		$alcoSum = 0;
		
		foreach ($items as $item) {
			switch ($item["QuestionType_id"]) {
				// Ранее известные имеющиеся заболевания
				case 2:
				case 94:
				case 142:
				case 675:
				case 817: //Вопрос 1.2
					if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] == 2)) {
						$this->evndiagdopdisp->addEvnDiagDopDispBefore($data, $this->getDiagIdByCode('I20.9'));
					} else {
						$this->evndiagdopdisp->delEvnDiagDopDispBefore($data, $this->getDiagIdByCode('I20.9'));
					}
					/*if($PersonAge<65) {
						$dataToUpdate['ProphConsult'][$type][9] = array(
							'RiskFactorType_id' => 9
						);
					}*/
					break;
				case 3:
				case 95:
				case 145:
				case 721:
					if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] == 2)) {
						$this->evndiagdopdisp->addEvnDiagDopDispBefore($data, $this->getDiagIdByCode('Z03.4'));
					} else {
						$this->evndiagdopdisp->delEvnDiagDopDispBefore($data, $this->getDiagIdByCode('Z03.4'));
					}
					break;
				case 4:
				case 96:
				case 146:
				case 676:
				case 722:
					if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] == 2)) {
						$this->evndiagdopdisp->addEvnDiagDopDispBefore($data, $this->getDiagIdByCode('I67.9'));
					} else {
						$this->evndiagdopdisp->delEvnDiagDopDispBefore($data, $this->getDiagIdByCode('I67.9'));
					}
					break;
				case 5:
					if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] == 2)) {
						$this->evndiagdopdisp->addEvnDiagDopDispBefore($data, $this->getDiagIdByCode('E14.9')); // Диагноз изменен согласно https://redmine.swan.perm.ru/issues/20964
					} else {
						$this->evndiagdopdisp->delEvnDiagDopDispBefore($data, $this->getDiagIdByCode('E14.9')); // Диагноз изменен согласно https://redmine.swan.perm.ru/issues/20964
					}
					break;
				case 6:
				case 100:
				case 681:
					if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] == 2)) {
						$this->evndiagdopdisp->addEvnDiagDopDispBefore($data, $this->getDiagIdByCode('K29.7'));
					} else {
						$this->evndiagdopdisp->delEvnDiagDopDispBefore($data, $this->getDiagIdByCode('K29.7'));
					}
					break;
				case 7:
				case 101:
				case 148:
				case 682:
					if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] == 2)) {
						$this->evndiagdopdisp->addEvnDiagDopDispBefore($data, $this->getDiagIdByCode('N28.8'));
					} else {
						$this->evndiagdopdisp->delEvnDiagDopDispBefore($data, $this->getDiagIdByCode('N28.8'));
					}
					break;
				case 8:
				case 102:
				case 144:
				case 683:
				case 717:
					if ($item['DopDispQuestion_IsTrue'] == 2) {
						$this->evndiagdopdisp->addEvnDiagDopDispBefore($data, $item['DopDispQuestion_ValuesStr']);
					}
					break;
				case 9:
				case 98:
				case 678:
					if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] == 2)) {
						$this->evndiagdopdisp->addEvnDiagDopDispBefore($data, $this->getDiagIdByCode('A16.2'));
					} else {
						$this->evndiagdopdisp->delEvnDiagDopDispBefore($data, $this->getDiagIdByCode('A16.2'));
					}
					break;
				case 99:
				case 143:
				case 679:
				case 715:
					if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] == 2)) {
						$this->evndiagdopdisp->addEvnDiagDopDispBefore($data, $this->getDiagIdByCode('O24.3'));
					} else {
						$this->evndiagdopdisp->delEvnDiagDopDispBefore($data, $this->getDiagIdByCode('O24.3'));
					}
					break;

				// Наследственность по заболеваниям
				case 10:
					if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
						$this->hereditydiag->addHeredityDiag($data, $this->getDiagIdByCode('Z03.4'), ($item['DopDispQuestion_ValuesStr'] == 2) ? 1 : 2);
					} else {
						$this->hereditydiag->delHeredityDiagByDiag($data, $this->getDiagIdByCode('Z03.4'));
					}
					break;
				case 11:
				case 104:
				case 689:
					if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
						$this->hereditydiag->addHeredityDiag($data, $this->getDiagIdByCode('I64.'), ($item['DopDispQuestion_ValuesStr'] == 2) ? 1 : 2);
					} else {
						$this->hereditydiag->delHeredityDiagByDiag($data, $this->getDiagIdByCode('I64.'));
					}
					break;
				case 12:
					if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
						$this->hereditydiag->addHeredityDiag($data, $this->getDiagIdByCode('C16.9'), ($item['DopDispQuestion_ValuesStr'] == 2) ? 1 : 2);
					} else {
						$this->hereditydiag->delHeredityDiagByDiag($data, $this->getDiagIdByCode('C16.9'));
					}
					break;
				case 105:
					if ($item['DopDispQuestion_IsTrue'] == 2) {
						$this->hereditydiag->addHeredityDiag($data, $item['DopDispQuestion_ValuesStr'], ($item['DopDispQuestion_ValuesStr'] == 2) ? 1 : 2);
					}
					break;
				case 690:
					if ($item['DopDispQuestion_IsTrue'] == 2) {
						$this->hereditydiag->addHeredityDiag($data, $item['DopDispQuestion_ValuesStr'], ($item['DopDispQuestion_ValuesStr'] == 2) ? 1 : 2);
					}

					$type = 'del';
					if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
						$type = 'ins';
					}
					$dataToUpdate['NeedConsult'][$type]['31_1'] = array(
						'Post_id' => 31,
						'ConsultationType_id' => 1
					);
					break;

				// Показания к углубленному профилактическому консультированию
				case 831:
				case 872:
				case 913:
				case 954:
					$type = 'del';
					if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
						$type = 'ins';
					}
					$dataToUpdate['ProphConsult'][$type][9] = array(
						'RiskFactorType_id' => 9
					);
					break;
				case 673:
				case 713:
				case 815:
				case 856:
				case 897:
				case 938:
				case 1013:
				case 1053:
				case 1093:
				case 1133:
					$type = 'del';
					if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
						$type = 'ins';
					}
					$dataToUpdate['ProphConsult'][$type][18] = array(
						'RiskFactorType_id' => 18
					);
					break;
				case 731:
				case 732:
				case 1036:
				case 1037:
				case 1076:
				case 1077:
				case 1116:
				case 1117:
				case 1156:
				case 1157:
					$type = 'del';
					if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
						$type = 'ins';
					}
					$dataToUpdate['ProphConsult'][$type][25] = array(
						'RiskFactorType_id' => 25
					);
					$dataToUpdate['NeedConsult'][$type]['63_2'] = array(
						'Post_id' => 63,
						'ConsultationType_id' => 2
					);
					break;
				case 736:
				case 1041:
				case 1081:
				case 1121:
				case 1161:
					$useCounterOne = true;
					$type = 'del';
					if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
						$counterOne++;
						$type = 'ins';
					}
					$dataToUpdate['ProphConsult'][$type][26] = array(
						'RiskFactorType_id' => 26
					);
					$dataToUpdate['NeedConsult'][$type]['37_1'] = array(
						'Post_id' => 37,
						'ConsultationType_id' => 1
					);
					break;
				case 737:
				case 1042:
				case 1082:
				case 1122:
				case 1162:
					$useCounterOne = true;
					$type = 'del';
					if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
						$counterOne++;
						$type = 'ins';
					}
					$dataToUpdate['ProphConsult'][$type][22] = array(
						'RiskFactorType_id' => 22
					);
					$dataToUpdate['NeedConsult'][$type]['43_2'] = array(
						'Post_id' => 43,
						'ConsultationType_id' => 2
					);
					break;
				case 738:
				case 1043:
				case 1083:
				case 1123:
				case 1163:
					$useCounterOne = true;
					$type = 'del';
					if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
						$counterOne++;
						$type = 'ins';
					}
					$dataToUpdate['ProphConsult'][$type][23] = array(
						'RiskFactorType_id' => 23
					);
					$dataToUpdate['NeedConsult'][$type]['10656_2'] = array(
						'Post_id' => 10656,
						'ConsultationType_id' => 2
					);
					break;
				case 739:
				case 1044:
				case 1084:
				case 1124:
				case 1164:
					$useCounterOne = true;
					$type = 'del';
					if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
						$counterOne++;
						$type = 'ins';
					}
					$dataToUpdate['ProphConsult'][$type][24] = array(
						'RiskFactorType_id' => 24
					);
					$dataToUpdate['NeedConsult'][$type]['37_1'] = array(
						'Post_id' => 37,
						'ConsultationType_id' => 1
					);
					break;
				case 746:
				case 1051:
				case 1091:
				case 1131:
				case 1171:
					$type = 'del';
					if (!empty($item['DopDispQuestion_Answer']) && is_numeric($item['DopDispQuestion_Answer']) && intval($item['DopDispQuestion_Answer']) >= 5) {
						$type = 'ins';
					}
					$dataToUpdate['ProphConsult'][$type][28] = array(
						'RiskFactorType_id' => 28
					);
					break;
				case 848:
				case 889:
				case 930:
				case 971:
					$type = 'del';
					if ($item['DopDispQuestion_ValuesStr'] == 1) {
						$type = 'ins';
					}
					$dataToUpdate['ProphConsult'][$type][6] = array(
						'RiskFactorType_id' => 6
					);
					break;
				case 851:
				case 892:
				case 933:
				case 974:
					$type = 'del';
					if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
						$type = 'ins';
					}
					$dataToUpdate['ProphConsult'][$type][19] = array(
						'RiskFactorType_id' => 19
					);
					break;
				case 1040:
				case 1080:
				case 1120:
				case 1160:
					$type = 'del';
					if ($item['DopDispQuestion_IsTrue'] == 1) {
						$type = 'ins';
					}
					$dataToUpdate['ProphConsult'][$type][6] = array(
						'RiskFactorType_id' => 6
					);
					break;
				case 849:
				case 890:
				case 931:
				case 972:
				case 1038:
				case 1078:
				case 1118:
				case 1158:
					$usePoorNutrition = true;
					if ($item['DopDispQuestion_IsTrue'] == 1) {
						$isPoorNutrition = true;
					}
					break;
				case 1039:
				case 1079:
				case 1119:
				case 1159:
					$usePoorNutrition = true;
					if ($item['DopDispQuestion_IsTrue'] == 1) {
						$isPoorNutritionTwo = true;
					}
					break;
				case 850:
				case 891:
				case 932:
				case 973:
					$usePoorNutrition = true;
					if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
						$isPoorNutritionTwo = true;
					}
					break;
					
				// Показания к консультации врача-специалиста
				case 693:
				case 694:
				case 695:
				case 726:
				case 727:
				case 728:
					$type = 'del';
					if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
						$type = 'ins';
					}
					$dataToUpdate['NeedConsult'][$type]['37_2'] = array(
						'Post_id' => 37,
						'ConsultationType_id' => 2
					);
					break;
				case 835:
				case 876:
				case 917:
				case 958:
				case 836:
				case 877:
				case 918:
				case 959:
				case 837:
				case 878:
				case 919:
				case 960:
					$type = 'del';
					if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
						$type = 'ins';
					}
					$dataToUpdate['NeedConsult'][$type]['37_1'] = array(
						'Post_id' => 37,
						'ConsultationType_id' => 1
					);
					break;
				case 832:
				case 873:
				case 914:
				case 955:
					$useHirurg = true;
					if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
						$isHirurg = true;
						$isHirurgTwo = true;
						$isHirurgThree = true;
					}
					break;
				case 843:
				case 884:
				case 925:
				case 966:
					$useHirurg = true;
					if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
						$isHirurg = true;
					}
					break;
				case 844:
				case 885:
				case 926:
				case 967:
					$useHirurg = true;
					if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
						$isHirurgTwo = true;
					}
					break;
				case 845:
				case 886:
				case 927:
				case 968:
					$useHirurg = true;
					if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
						$isHirurgThree = true;
					}
					break;
				case 740:
				case 1045:
				case 1085:
				case 1125:
				case 1165:
					$useCounterOne = true;
					$type = 'del';
					if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
						$counterOne++;
						$type = 'ins';
					}
					
					$Post_id = 86;
					if ($Sex_id == 2) {
						$Post_id = 12;
					}

					$dataToUpdate['NeedConsult'][$type][$Post_id . '_2'] = array(
						'Post_id' => $Post_id,
						'ConsultationType_id' => 2
					);
					break;
				case 833:
				case 874:
				case 915:
				case 956:
				case 1024:
				case 1064:
				case 1104:
				case 1144:
					$useCounterOne = true;
					$type = 'del';
					if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
						$counterOne++;
						$type = 'ins';
					}

					$dataToUpdate['NeedConsult'][$type]['182_2'] = array(
						'Post_id' => 182,
						'ConsultationType_id' => 2
					);
					break;
				case 741:
				case 742:
				case 743:
				case 1046:
				case 1086:
				case 1126:
				case 1166:
				case 1047:
				case 1087:
				case 1127:
				case 1167:
					$useCounterOne = true;
					if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
						$counterOne++;
					}
					break;
				case 1048:
				case 1088:
				case 1128:
				case 1168:
					$useCounterOne = true;
					$useOnko = true;
					if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
						$counterOne++;
						$isOnko = true;
					}
					break;
				case 1049:
				case 1089:
				case 1129:
				case 1169:
					$useOnko = true;
					if ($item['DopDispQuestion_IsTrue'] == 1) {
						$isOnkoTwo = true;
					}
					break;
				case 1050:
				case 1090:
				case 1130:
				case 1170:
					$useOnko = true;
					if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
						$isOnkoThree = true;
					}
					break;
				case 701:
					$usePohud = true;
					if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
						$isPohud = true;
					}
					break;
				case 702:
					$usePohud = true;
					if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
						$isPohudDepend = true;
					}
					break;
				case 703:
					$usePohud = true;
					if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
						$isPohudDependTwo = true;
					}
					break;
				case 852:
				case 893:
				case 934:
				case 975:
				case 853:
				case 894:
				case 935:
				case 976:
				case 854:
				case 895:
				case 936:
				case 977:
					$useAlco = true;
					$alcoSum += intval($item['DopDispQuestion_ValuesStr']) - 64;
					break;
			}
		

			if (array_key_exists($item['QuestionType_id'], $ExistingDopDispQuestionData)) {
				$item['DopDispQuestion_id'] = $ExistingDopDispQuestionData[$item['QuestionType_id']];
			}

			$item['DopDispQuestion_Answer'] = toAnsi($item['DopDispQuestion_Answer']);

			if (!empty($item['DopDispQuestion_id']) && $item['DopDispQuestion_id'] > 0) {
				$proc = 'p_DopDispQuestion_upd';
			} else {
				$proc = 'p_DopDispQuestion_ins';
				$item['DopDispQuestion_id'] = null;
			}

			if (empty($item['DopDispQuestion_IsTrue'])) {
				$item['DopDispQuestion_IsTrue'] = null;
			}

			$query = "
				select
					DopDispQuestion_id as \"DopDispQuestion_id\",
					error_message as \"Error_Msg\",
					error_code as \"Error_Code\"
				from {$proc} (
					DopDispQuestion_id := :DopDispQuestion_id, 
					EvnPLDisp_id := :EvnPLDisp_id, 
					QuestionType_id := :QuestionType_id, 
					DopDispQuestion_IsTrue := :DopDispQuestion_IsTrue, 
					DopDispQuestion_Answer := :DopDispQuestion_Answer, 
					DopDispQuestion_ValuesStr := :DopDispQuestion_ValuesStr,
					pmUser_id := :pmUser_id
				)
			";
			$result = $this->db->query($query, array(
				'EvnPLDisp_id' => $data['EvnPLDisp_id'],
				'DopDispQuestion_id' => $item['DopDispQuestion_id'],
				'QuestionType_id' => $item['QuestionType_id'],
				'DopDispQuestion_IsTrue' => $item['DopDispQuestion_IsTrue'],
				'DopDispQuestion_Answer' => $item['DopDispQuestion_Answer'],
				'DopDispQuestion_ValuesStr' => !empty($item['DopDispQuestion_ValuesStr']) ? $item['DopDispQuestion_ValuesStr'] : null,
				'pmUser_id' => $data['pmUser_id']
			));

			if (!is_object($result)) {
				$this->db->trans_rollback();
				return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение ответов на вопросы)');
			}

			$resp = $result->result('array');

			if (!is_array($resp) || count($resp) == 0) {
				$this->db->trans_rollback();
				return array('success' => false, 'Error_Msg' => 'Ошибка при сохранении ответов на вопросы');
			} else if (!empty($resp[0]['Error_Msg'])) {
				$this->db->trans_rollback();
				return array('success' => false, 'Error_Msg' => $resp[0]['Error_Msg']);
			}
		}

		if ($useCounterOne) {
			$type = 'del';
			if ($counterOne >= 3) {
				$type = 'ins';
			}
			$dataToUpdate['ProphConsult'][$type][27] = array(
				'RiskFactorType_id' => 27
			);
			$dataToUpdate['NeedConsult'][$type]['63_2'] = array(
				'Post_id' => 19,
				'ConsultationType_id' => 1
			);
		}

		if ($usePohud) {
			$type = 'del';
			if ($isPohud && ($isPohudDepend || $isPohudDependTwo)) {
				$type = 'ins';
			}
			$dataToUpdate['NeedConsult'][$type]['63_2'] = array(
				'Post_id' => 31,
				'ConsultationType_id' => 2
			);
		}

		if ($useOnko) {
			$type = 'del';
			if ($isOnko && $isOnkoTwo && $isOnkoThree) {
				$type = 'ins';
			}
			$dataToUpdate['ProphConsult'][$type][21] = array(
				'RiskFactorType_id' => 21
			);
		}

		if ($usePoorNutrition) {
			$type = 'del';
			if ($isPoorNutrition || $isPoorNutritionTwo) {
				$type = 'ins';
			}
			$dataToUpdate['ProphConsult'][$type][5] = array(
				'RiskFactorType_id' => 5
			);
		}

		if ($useHirurg) {
			$type = 'del';
			if ($isHirurg && ($isHirurgTwo || $isHirurgThree)) {
				$type = 'ins';
			}
			$dataToUpdate['NeedConsult'][$type]['91_1'] = array(
				'Post_id' => 91,
				'ConsultationType_id' => 1
			);
		}
		
		if ($useAlco) {
			$type = 'del';
			if ($alcoSum >= 4 || ($Sex_id == 2 && $alcoSum >= 3)) {
				$type = 'ins';
			}
			$dataToUpdate['ProphConsult'][$type][4] = array(
				'RiskFactorType_id' => 4
			);
		}

		if (!empty($dataToUpdate['ProphConsult']['ins'])) {
			foreach ($dataToUpdate['ProphConsult']['ins'] as $key => $value) {
				unset($dataToUpdate['ProphConsult']['del'][$key]);
				$this->prophconsult->addProphConsult($data, $value['RiskFactorType_id']);
			}
		}

		if (!empty($dataToUpdate['ProphConsult']['del'])) {
			foreach ($dataToUpdate['ProphConsult']['del'] as $key => $value) {
				$this->prophconsult->delProphConsult($data, $value['RiskFactorType_id']);
			}
		}

		if (!empty($dataToUpdate['NeedConsult']['ins'])) {
			foreach ($dataToUpdate['NeedConsult']['ins'] as $key => $value) {
				unset($dataToUpdate['NeedConsult']['del'][$key]);
				$this->needconsult->addNeedConsult($data, $value['Post_id'], $value['ConsultationType_id']);
			}
		}

		if (!empty($dataToUpdate['NeedConsult']['del'])) {
			foreach ($dataToUpdate['NeedConsult']['del'] as $key => $value) {
				$this->needconsult->delNeedConsult($data, $value['Post_id'], $value['ConsultationType_id']);
			}
		}

		// http://redmine.swan.perm.ru/issues/84088
		// Добавляем повторную проверку на наличие дублей
		$sql = "
			select EvnUslugaDispDop_id
			from v_EvnUslugaDispDop
			where EvnUslugaDispDop_pid = :EvnPLDisp_id
				and UslugaComplex_id = :UslugaComplex_id
				and EvnUslugaDispDop_id != :EvnUslugaDispDop_id
			limit 1
		";
		$res = $this->db->query($sql, $data);

		if (!is_object($res)) {
			$this->db->trans_rollback();
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (получение идентификаторов дублирующих посещений)'));
		}

		$resp = $res->result('array');

		if (is_array($resp) && count($resp) > 0 && !empty($resp[0]['EvnUslugaDispDop_id'])) {
			$this->db->trans_rollback();
			return array(array('Error_Msg' => 'Обнаружены дубль по услуге анкетирования. Произведен откат транзакции. Пожалуйста, повторите сохранение.'));
		}

		$this->db->trans_commit();

		return array(
			'success' => true,
			'Error_Msg' => '',
			'EvnUslugaDispDop_id' => $data["EvnUslugaDispDop_id"]
		);
	}

	/**
	 * Сохранение флага ЗНО в карте ДВН
	 * используется в Ext6
	 */
	function saveEvnPLDispDop13_SuspectZNO($data) {
		
		$query = "
			select
				 EvnPLDispDop13_pid as \"EvnPLDispDop13_pid\"
				,EvnPLDispDop13_rid as \"EvnPLDispDop13_rid\"
				,EvnPLDispDop13_fid as \"EvnPLDispDop13_fid\"
				,Lpu_id as \"Lpu_id\"
				,Server_id as \"Server_id\"
				,PersonEvn_id as \"PersonEvn_id\"
				,to_char(EvnPLDispDop13_consDT, 'YYYY-MM-DD') as \"EvnPLDispDop13_consDT\"
				,to_char(EvnPLDispDop13_setDT, 'YYYY-MM-DD') as \"EvnPLDispDop13_setDT\"
				,to_char(EvnPLDispDop13_disDT, 'YYYY-MM-DD') as \"EvnPLDispDop13_disDT\"
				,to_char(EvnPLDispDop13_didDT, 'YYYY-MM-DD') as \"EvnPLDispDop13_didDT\"
				,Morbus_id as \"Morbus_id\"
				,EvnPLDispDop13_IsSigned as \"EvnPLDispDop13_IsSigned\"
				,pmUser_signID as \"pmUser_signID\"
				,EvnPLDispDop13_signDT as \"EvnPLDispDop13_signDT\"
				,EvnPLDispDop13_IsFinish as \"EvnPLDispDop13_IsFinish\"
				,EvnPLDispDop13_IsNewOrder as \"EvnPLDispDop13_IsNewOrder\"
				,EvnPLDispDop13_IndexRep as \"EvnPLDispDop13_IndexRep\"
				,EvnPLDispDop13_IndexRepInReg as \"EvnPLDispDop13_IndexRepInReg\"
				,EvnDirection_aid as \"EvnDirection_aid\"
				,Person_Age as \"Person_Age\"
				,AttachType_id as \"AttachType_id\"
				,Lpu_aid as \"Lpu_aid\"
				,DispClass_id as \"DispClass_id\"
				,MedStaffFact_id as \"MedStaffFact_id\"
				,PayType_id as \"PayType_id\"
				,Lpu_mid as \"Lpu_mid\"
				,EvnPLDispDop13_IsOutLpu as \"EvnPLDispDop13_IsOutLpu\"
			from v_EvnPLDispDop13
			where EvnPLDispDop13_id = :EvnPLDispDop13_id
			limit 1
		";
		$result = $this->db->query($query, array(
			'EvnPLDispDop13_id' => $data['EvnPLDispDop13_id']
		));
		
		if ( is_object($result) ) {
			$resp = $result->result('array');
		}
		
		if ( is_array($resp) && count($resp) > 0 ) {
			
			$resp[0]['pmUser_id'] = $data['pmUser_id'];
			$resp[0]['EvnPLDispDop13_IsSuspectZNO'] = $data['EvnPLDispDop13_IsSuspectZNO'];
			$resp[0]['Diag_spid'] = $data['Diag_spid'];
			
			$query = "
				select
					EvnPLDispDop13_id as \"EvnPLDispDop13_id\",
					error_code as \"Error_Code\",
					error_message as \"Error_Msg\"
				from p_EvnPLDispDop13_upd (
					EvnPLDispDop13_id := :EvnPLDispDop13_id
			";

			foreach ( $resp[0] as $key => $value ) {
				$query .= "," . $key . " := :" . $key;
			}

			$query .= ")";
			$resp[0]['EvnPLDispDop13_id'] = $data['EvnPLDispDop13_id'];
			
			$this->db->trans_begin();
			$result = $this->db->query($query, $resp[0]);
			
			
			//exit(getDebugSQL($query, $resp[0]));
			if ( is_object($result) ) {
				$resp = $result->result('array');

				if ( is_array($resp) && count($resp) > 0 && !empty($resp[0]['Error_Msg']) ) {
					$this->db->trans_rollback();
					//return $resp;
					
					
					return array(array('Error_Msg' => 'Произошла ошибка при сохранении карты ДВН. Пожалуйста, повторите сохранение.'));
		
				}
			}
			$this->db->trans_commit();
		}
		
		return array(
			'success' => true,
			'Error_Msg' => ''
		);
	}
}
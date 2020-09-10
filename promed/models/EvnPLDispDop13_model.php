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
			'saveEvnPLDispDop13Ext6' => array(
				array(
					'field' => 'EvnPLDispDop13_id',
					'label' => 'Идентификатор карты',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'ignoreCheckDesease',
					'label' => 'Игнорировать проверку подозрений',
					'rules' => '',
					'type' => 'int',
					'default' => '0'
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
			$resp_vol = $this->queryResult("
				declare @AttributeVision_TablePKey bigint = (select top 1 VolumeType_id from v_VolumeType (nolock) where VolumeType_Name = 'ДВН2')
				declare @curDate datetime = dbo.tzGetDate();
			
				SELECT  TOP 1
					av.AttributeValue_id
				FROM
					v_AttributeVision avis (nolock)
					inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
					inner join v_Attribute a (nolock) on a.Attribute_id = av.Attribute_id
					cross apply(
						select top 1
							av2.AttributeValue_ValueIdent
						from
							v_AttributeValue av2 (nolock)
							inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
						where
							av2.AttributeValue_rid = av.AttributeValue_id
							and a2.Attribute_TableName = 'dbo.Lpu'
							and ISNULL(av2.AttributeValue_ValueIdent,:Lpu_id) = :Lpu_id
					) MOFILTER
				WHERE
					avis.AttributeVision_TableName = 'dbo.VolumeType'
					and avis.AttributeVision_TablePKey = @AttributeVision_TablePKey
					and avis.AttributeVision_IsKeyValue = 2
					and ISNULL(av.AttributeValue_begDate, @curDate) <= @curDate
					and ISNULL(av.AttributeValue_endDate, @curDate) >= @curDate
					
			", array(
				'Lpu_id' => $data['Lpu_id']
			));
			
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

			$resp_vol = $this->queryResult("
				declare @AttributeVision_TablePKey bigint = (select top 1 VolumeType_id from v_VolumeType (nolock) where VolumeType_Code = 'ДВН_Ч_МО'); -- Мед. диспансеризация взрослого населения в чужой МО
				declare @curDate datetime = dbo.tzGetDate();
			
				SELECT
					av.AttributeValue_ValueIdent
				FROM
					v_AttributeVision avis (nolock)
					inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
					inner join v_Attribute a (nolock) on a.Attribute_id = av.Attribute_id
					cross apply(
						select top 1
							av2.AttributeValue_ValueIdent
						from
							v_AttributeValue av2 (nolock)
							inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
						where
							av2.AttributeValue_rid = av.AttributeValue_id
							and a2.Attribute_TableName = 'dbo.Lpu'
							and ISNULL(av2.AttributeValue_ValueIdent,:Lpu_id) = :Lpu_id
					) MOFILTER
				WHERE
					avis.AttributeVision_TableName = 'dbo.VolumeType'
					and avis.AttributeVision_TablePKey = @AttributeVision_TablePKey
					and avis.AttributeVision_IsKeyValue = 2
					and ISNULL(av.AttributeValue_begDate, @curDate) <= @curDate
					and ISNULL(av.AttributeValue_endDate, @curDate) >= @curDate
					
			", array(
				'Lpu_id' => $data['Lpu_id']
			));

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
				Lpu_id
			From
				v_Lpu with (nolock)
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
		$result = $this->getFirstResultFromQuery("
						declare @AttributeVision_TablePKey bigint = (select top 1 VolumeType_id from v_VolumeType (nolock) where VolumeType_Code = 'ДВН_Б_ПРИК'); -- ДВН без прикрепления
						declare @curDate datetime = dbo.tzGetDate();

						SELECT  TOP 1
							av.AttributeValue_id
						FROM
							v_AttributeVision avis (nolock)
							inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
							inner join v_Attribute a (nolock) on a.Attribute_id = av.Attribute_id
						WHERE
							avis.AttributeVision_TableName = 'dbo.VolumeType'
							and av.AttributeValue_ValueIdent = :Lpu_id
							and avis.AttributeVision_TablePKey = @AttributeVision_TablePKey
							and avis.AttributeVision_IsKeyValue = 2
							and ISNULL(av.AttributeValue_begDate, @curDate) <= @curDate
							and ISNULL(av.AttributeValue_endDate, @curDate) >= @curDate

					", array(
			'Lpu_id' => $data['Lpu_id']
		));

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
		if (in_array($data['session']['region']['nick'], array('ufa', 'ekb', 'kareliya', 'penza', 'astra'))) {
			$add_filter = " or exists (select top 1 PersonPrivilegeWOW_id from v_PersonPrivilegeWOW (nolock) where Person_id = PS.Person_id)";
		}

		$maxage = 999;

		$personPrivilegeCodeList = $this->getPersonPrivilegeCodeList();

		if ( count($personIds) > 0 ) {
			$query = "
				declare
					@PersonDopDisp_Year bigint = YEAR(dbo.tzGetDate()),
					@PersonDopDisp_YearPrev bigint = YEAR(dbo.tzGetDate())-1;
					
				with PersonSt as (
					select
						Person_id,
						Person_SurName,
						Person_FirName,
						Person_SecName,
						Person_BirthDay,
						dbo.Age2(Person_BirthDay, dbo.tzGetDate()) as Person_AgeNow,
						dbo.Age2(Person_BirthDay, cast(@PersonDopDisp_Year as varchar) + '-12-31') as Person_Age,
						datediff(month, Person_BirthDay, dbo.tzGetDate()) % 12 as Person_AgeMonth
					from
						v_PersonState (nolock)
					where
						Person_id in (" . implode(', ', $personIds) . ")
				)
					
				select
					ps.Person_SurName + ' ' + ps.Person_FirName + ' ' + isnull(ps.Person_SecName, '') as Person_Fio,
					convert(varchar(10), ps.Person_BirthDay, 104) as Person_BirthDay,
					ps.Person_Age,
					l.Lpu_Nick,
					pc.LpuRegion_Name,
					/*-- пройденное ---*/
					passed.DispClass_id,
					convert(varchar(10), passed.EvnPLDisp_setDate, 104) as EvnPLDisp_setDate,
					convert(varchar(10), passed.EvnPLDisp_disDate, 104) as EvnPLDisp_disDate,
					passed.AgeGroupDisp_Name,	
					/*-- планируемое ---*/
					case when planned.PersonDopDispPlan_id is not null then 'v' else '' end as pEvnPLDisp_plan,
					planned.DispClass_id as pDispClass_id,
					convert(varchar(10), planned.EvnPLDisp_setDate, 104) as pEvnPLDisp_setDate,
					planned.AgeGroupDisp_Name as pAgeGroupDisp_Name
				from
					PersonSt ps (nolock)
					left join v_PersonCard pc (nolock) on (PC.Person_id = ps.Person_id and pc.LpuAttachType_id = 1)
					left join v_Lpu l (nolock) on l.Lpu_id = pc.Lpu_id
					outer apply (
						select count(*) [count]
						from v_EvnPLDisp epld with (nolock) 
						where epld.Person_id = PS.Person_id and YEAR(epld.EvnPLDisp_setDate) = @PersonDopDisp_YearPrev and epld.DispClass_id = 5
					) as EplDispProfLastYear
					outer apply (
						select top 1 
							epld.DispClass_id,
							epld.EvnPLDisp_setDate,
							epld.EvnPLDisp_disDate,
							ag.AgeGroupDisp_Name
						from v_EvnPLDisp epld with (nolock) 
						left join v_EvnPLDispTeenInspection epldti (nolock) on epldti.EvnPLDispTeenInspection_id = epld.EvnPLDisp_id
						left join v_AgeGroupDisp ag (nolock) on ag.AgeGroupDisp_id = epldti.AgeGroupDisp_id
						where epld.Person_id = PS.Person_id and epld.DispClass_id in(1,5,6,9,10) and (epld.EvnPLDisp_disDate is not null or YEAR(epld.EvnPLDisp_consDT) < @PersonDopDisp_Year)
						order by isnull(epld.EvnPLDisp_disDate,epld.EvnPLDisp_setDate) desc
					) as passed
					outer apply (
						select top 1 * from (
							/*--- уже заведена карта ----*/
							select top 1
								pddp.PersonDopDispPlan_id,
								epld.DispClass_id,
								case when epld.DispClass_id in(1,5) then epld.EvnPLDisp_setDate else null end as EvnPLDisp_setDate,
								ag.AgeGroupDisp_Name
							from v_EvnPLDisp epld with (nolock) 
							left join v_EvnPLDispTeenInspection epldti (nolock) on epldti.EvnPLDispTeenInspection_id = epld.EvnPLDisp_id
							left join v_AgeGroupDisp ag (nolock) on ag.AgeGroupDisp_id = epldti.AgeGroupDisp_id
							left join v_PlanPersonList ppl (nolock) on ppl.Person_id = epld.Person_id
							left join v_PersonDopDispPlan pddp with (nolock) on ppl.PersonDopDispPlan_id = pddp.PersonDopDispPlan_id and pddp.DispClass_id = epld.DispClass_id
							where epld.Person_id = PS.Person_id and epld.DispClass_id in(1,5,6,9,10) and YEAR(epld.EvnPLDisp_consDT) = @PersonDopDisp_Year and epld.EvnPLDisp_disDate is null
							order by epld.EvnPLDisp_setDate asc
							/*--- включен в план, но не заведена карта ----*/
							union all
							select top 1
								pddp.PersonDopDispPlan_id,
								pddp.DispClass_id,
								null as EvnPLDisp_setDate,
								null as AgeGroupDisp_Name
							from v_PlanPersonList ppl with (nolock)
							inner join v_PersonDopDispPlan pddp with (nolock) on ppl.PersonDopDispPlan_id = pddp.PersonDopDispPlan_id
							where 
								ppl.Person_id = PS.Person_id and 
								pddp.DispClass_id in(1,5) and 
								pddp.PersonDopDispPlan_Year = @PersonDopDisp_Year and 
								not exists (select top 1 EvnPLDisp_id from v_EvnPLDisp (nolock) where YEAR(EvnPLDisp_consDT) = pddp.PersonDopDispPlan_Year and DispClass_id = pddp.DispClass_id and Person_id = ppl.Person_id)
							order by ppl.PlanPersonList_id asc
							/*--- теоретически подлежащие МОН ----*/							
							union all
							select top 1
								null as PersonDopDispPlan_id,
								6 as DispClass_id,
								null as EvnPLDisp_setDate,
								agd.AgeGroupDisp_Name as AgeGroupDisp_Name
							from v_AgeGroupDisp agd with (nolock)
							where 
								ps.Person_Age < 18 and
								agd.DispType_id = 4 and 
								/*agd.AgeGroupDisp_From <= ps.Person_AgeNow and */
								agd.AgeGroupDisp_To >= ps.Person_AgeNow and 
								/*agd.AgeGroupDisp_monthFrom <= ps.Person_AgeMonth and */
								agd.AgeGroupDisp_monthTo >= ps.Person_AgeMonth and
								not exists (select top 1 EvnPLDisp_id from v_EvnPLDisp (nolock) where YEAR(EvnPLDisp_consDT) = @PersonDopDisp_Year and AgeGroupDisp_id = agd.AgeGroupDisp_id and DispClass_id in(6,9,10) and Person_id = PS.Person_id)
							order by agd.AgeGroupDisp_From asc, agd.AgeGroupDisp_monthFrom asc
							/*--- теоретически подлежащие ДВН ----*/
							union all
							select top 1
								null as PersonDopDispPlan_id,
								1 as DispClass_id,
								null as EvnPLDisp_setDate,
								null as AgeGroupDisp_Name
							where
								(
									(ps.Person_Age >= 21 and ps.Person_Age %3 = 0)
									" . (count($personPrivilegeCodeList) > 0 ? "or (ps.Person_Age >= 18 and exists (select top 1 pp.PersonPrivilege_id from v_PersonPrivilege pp (nolock) inner join v_PrivilegeType pt (nolock) on pt.PrivilegeType_id = pp.PrivilegeType_id where pt.PrivilegeType_Code IN ('" . implode("','", $personPrivilegeCodeList) . "') and pp.Person_id = PS.Person_id and pp.PersonPrivilege_begDate <= cast(@PersonDopDisp_Year as varchar) + '-12-31' and (pp.PersonPrivilege_endDate >= cast(@PersonDopDisp_Year as varchar) + '-12-31' or pp.PersonPrivilege_endDate is null)))" : "") . "
								)
								and not exists (select top 1 EvnPLDisp_id from v_EvnPLDisp (nolock) where YEAR(EvnPLDisp_consDT) = @PersonDopDisp_Year and DispClass_id = 1 and Person_id = PS.Person_id)
							/*--- теоретически подлежащие ПОВН ----*/
							union all
							select top 1
								null as PersonDopDispPlan_id,
								5 as DispClass_id,
								null as EvnPLDisp_setDate,
								null as AgeGroupDisp_Name
							where
								ps.Person_Age >= 18 and
								ps.Person_Age % 3 != 0 and
								EplDispProfLastYear.count = 0 and
								not exists (select top 1 EvnPLDisp_id from v_EvnPLDisp (nolock) where YEAR(EvnPLDisp_consDT) = @PersonDopDisp_Year and DispClass_id = 5 and Person_id = PS.Person_id)
						) as t
					) as planned
				order by
					ps.Person_SurName, ps.Person_FirName
			";
			
			//echo getDebugSql($query, array()); exit;
			$result = $this->db->query($query);
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
						v_EvnUslugaDispDop EUDD with (nolock)
						inner join v_EvnVizitDispDop EVDD with (nolock) on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
						inner join v_DopDispInfoConsent DDIC with (nolock) on DDIC.DopDispInfoConsent_id = EVDD.DopDispInfoConsent_id
						inner join v_SurveyTypeLink STL with (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
						inner join v_SurveyType ST with (nolock) on ST.SurveyType_id = STL.SurveyType_id
					where
						EUDD.EvnUslugaDispDop_rid = :EvnPLDispDop13_id
						and ISNULL(DDIC.DopDispInfoConsent_IsAgree, 1) = 1
						and ISNULL(DDIC.DopDispInfoConsent_IsEarlier, 1) = 1
						and ST.SurveyType_Code not in (1, 2, 48)
						and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
				";
			break;

			// Специально для удаления анкетирования
			case 'EvnUslugaDispDop':
				$query = "
					select
						EUDD.EvnUslugaDispDop_id as id
					from
						v_EvnUslugaDispDop EUDD with (nolock)
						inner join v_SurveyTypeLink STL with (nolock) on STL.UslugaComplex_id = EUDD.UslugaComplex_id
						inner join v_SurveyType ST with (nolock) on ST.SurveyType_id = STL.SurveyType_id
						inner join v_DopDispInfoConsent DDIC with (nolock) on DDIC.SurveyTypeLink_id = STL.SurveyTypeLink_id
					where
						EUDD.EvnUslugaDispDop_rid = :EvnPLDispDop13_id
						and DDIC.EvnPLDisp_id = :EvnPLDispDop13_id
						and ISNULL(DDIC.DopDispInfoConsent_IsAgree, 1) = 1
						and ISNULL(DDIC.DopDispInfoConsent_IsEarlier, 1) = 1
						and ST.SurveyType_Code = 2
						and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
				";
			break;

			case 'EvnDiagDopDisp':
				$query = "
					select " . $attr . "_id as id
					from v_" . $attr . " with (nolock)
					where EvnDiagDopDisp_pid = :EvnPLDispDop13_id
				";
			break;

			case 'DopDispInfoConsent':
				$query = "
					select DDIC.DopDispInfoConsent_id as id
					from v_DopDispInfoConsent DDIC with (nolock)
						inner join v_SurveyTypeLink STL with (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
						inner join v_SurveyType ST with (nolock) on ST.SurveyType_id = STL.SurveyType_id
					where DDIC.EvnPLDisp_id = :EvnPLDispDop13_id
						and ST.SurveyType_Code NOT IN (1,48)
				";
			break;

			default:
				$query = "
					select " . $attr . "_id as id
					from v_" . $attr . " with (nolock)
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
					declare
						@ErrCode int,
						@ErrMessage varchar(4000);

					exec p_" . $attr . "_del
						@" . $attr . "_id = :id,
						" . (in_array($attr, array('EvnDiagDopDisp', 'EvnUslugaDispDop', 'EvnVizitDispDop')) ? "@pmUser_id = :pmUser_id," : "") . "
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;

					select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
				UslugaComplex_Code
			from
				v_UslugaComplex (nolock)
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
			select top 1
				DopDispInfoConsent_id
			from
				v_DopDispInfoConsent (nolock)
			where
				EvnPLDisp_id = :EvnPLDisp_id
				and SurveyType_id = :SurveyType_id
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
			select top 1
				DopDispInfoConsent_id
			from
				v_DopDispInfoConsent (nolock)
			where
				EvnPLDisp_id = :EvnPLDisp_id
				and SurveyTypeLink_id = :SurveyTypeLink_id
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
			select top 1
				ddic.DopDispInfoConsent_id,
				ddic.DopDispInfoConsent_IsAgree,
				ddic.DopDispInfoConsent_IsEarlier,
				ddic.DopDispInfoConsent_IsImpossible
			from
				v_DopDispInfoConsent ddic (nolock)
				inner join v_SurveyTypeLink stl (nolock) on stl.SurveyTypeLink_id = ddic.SurveyTypeLink_id
			where
				ddic.EvnPLDisp_id = :EvnPLDisp_id
				and stl.SurveyTypeLink_ComplexSurvey = 2
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
					Person_id,
					convert(varchar, EvnPLDisp_consDT, 120) as EvnPLDisp_consDate
				from
					v_EvnPLDisp (nolock)
				where
					EvnPLDisp_id = :EvnPLDisp_id
			";

			$resp = $this->queryResult($query, [
				'EvnPLDisp_id' => $data['EvnPLDisp_id']
			]);

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
			'DispClass_id' => $data['DispClass_id'],
		];
		if (!empty($data['CytoUslugaComplex_id'])) {
			$stlFilter .= " and UslugaComplex_id = :UslugaComplex_id";
			$stlQueryParams['UslugaComplex_id'] = $data['CytoUslugaComplex_id'];
		}
		$query = "
			Declare
				@sex_id bigint,
				@EvnPLDisp_YearEndDate datetime = cast(substring(:EvnPLDisp_consDate, 1, 4) + '-12-31' as datetime),
				@age int

			select top 1
				@sex_id = ISNULL(Sex_id, 3),
				@age = cast(dbo.Age2(Person_BirthDay, @EvnPLDisp_YearEndDate) as int)
			from v_PersonState ps (nolock)
			where ps.Person_id = :Person_id

			if ( @age = 18 )
				set @age = 21;
				
			if ( @age > 99 )
				set @age = 99;

			select top 1
				STL.SurveyTypeLink_id
			from
				v_SurveyTypeLink STL (nolock)
			where
				STL.SurveyTypeLink_ComplexSurvey = 2
				and (@age between Isnull(STL.SurveyTypeLink_From, 0) and  Isnull(STL.SurveyTypeLink_To, 999))
				and STL.DispClass_id = :DispClass_id
				and (IsNull(STL.Sex_id, @sex_id) = @sex_id) -- по полу
				and (STL.SurveyTypeLink_begDate is null or STL.SurveyTypeLink_begDate <= :EvnPLDisp_consDate)
				and (STL.SurveyTypeLink_endDate is null or STL.SurveyTypeLink_endDate >= :EvnPLDisp_consDate)
				{$stlFilter}
		";
		$stlResp = $this->queryResult($query, $stlQueryParams);
		if (!empty($stlResp[0]['SurveyTypeLink_id'])) {
			$CytoSurveyTypeLink_id = $stlResp[0]['SurveyTypeLink_id'];
		} else {
			return [[ 'Error_Msg' => 'Не удалось сохранить согласие для услуги цитологического исследования' ]];
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :DopDispInfoConsent_id;

			exec {$cytoddicproc}
				@DopDispInfoConsent_id = @Res output,
				@EvnPLDisp_id = :EvnPLDisp_id,
				@DopDispInfoConsent_IsAgree = :DopDispInfoConsent_IsAgree,
				@DopDispInfoConsent_IsEarlier = :DopDispInfoConsent_IsEarlier,
				@DopDispInfoConsent_IsImpossible = :DopDispInfoConsent_IsImpossible,
				@SurveyTypeLink_id = :SurveyTypeLink_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output

			select @Res as DopDispInfoConsent_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
		if ($data['DispClass_id'] == 1 && !empty($data['EvnPLDispDop13_IsNewOrder']) && $data['EvnPLDispDop13_IsNewOrder'] == 2) {
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
					EvnPLDispDop13_id
				from 
					v_EvnPLDispDop13 (nolock) 
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
				select top 1
					EvnPLDispDop13_id
				from
					v_EvnPLDispDop13 (nolock)
				where
					EvnPLDispDop13_id = :EvnPLDispDop13_fid and EvnPLDispDop13_disDate > :EvnPLDispDop13_consDate
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
			$resp_vol = $this->queryResult("
				declare @AttributeVision_TablePKey bigint = (select top 1 VolumeType_id from v_VolumeType (nolock) where VolumeType_Code = 'ДВН_Б_ПРИК'); -- ДВН без прикрепления
				declare @date datetime = :EvnPLDispDop13_consDate;

				SELECT  TOP 1
					av.AttributeValue_id
				FROM
					v_AttributeVision avis (nolock)
					inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
					inner join v_Attribute a (nolock) on a.Attribute_id = av.Attribute_id
				WHERE
					avis.AttributeVision_TableName = 'dbo.VolumeType'
					and av.AttributeValue_ValueIdent = :Lpu_id
					and avis.AttributeVision_TablePKey = @AttributeVision_TablePKey
					and avis.AttributeVision_IsKeyValue = 2
					and ISNULL(av.AttributeValue_begDate, @date) <= @date
					and ISNULL(av.AttributeValue_endDate, @date) >= @date

			", array(
				'EvnPLDispDop13_consDate' => $data['EvnPLDispDop13_consDate'],
				'Lpu_id' => $data['Lpu_id']
			));
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
			// UPD #174498: Регион Крым, Карелия
				//1 этап
					//Если на дату подписания информированного согласия основное прикрепление пациента не к МО пользователя или у пациента нет основного прикрепления,
					// то выводится предупреждение: «Пациент не имеет основного прикрепления или прикреплен к другой МО. Продолжить сохранение? Да. Нет.»
					// При нажатии кнопки «Нет» сохранение согласия отменяется. При нажатии кнопки «Да» процедура сохранения продолжается.
				//2 этап
					//Если сохранение согласия для выбранной карты ДВН 2 этап выполняется не в МО, направившей на 2 этап диспансеризации,
					// то выводится сообщение: «Данный пациент направлен на 2 этап другой медицинской организацией. Второй этап должен проводиться в той же медицинской организации, где проведен первый этап. ОК».
					// При нажатии «ОК» сообщение закрывается, сохранение отменяется
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
					PersonCard_id, Lpu_id
				FROM v_PersonCard_all with (nolock)
				WHERE
					Person_id = :Person_id
					and LpuAttachType_id = 1
					and cast(PersonCard_begDate as date) <= :EvnPLDispDop13_consDate
					and ISNULL(PersonCard_endDate, '2030-01-01') > :EvnPLDispDop13_consDate
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
			if ( $hasLpuAttach == false && !in_array(getRegionNick(), array('kareliya','krym','buryatiya'))) { // если не нашли прикрепление в своей МО
				$this->db->trans_rollback();
				return array('Error_Msg' => 'Пациент не имеет основного прикрепления или прикреплен к другой МО');
			}else if( $hasLpuAttach == false && in_array(getRegionNick(), array('kareliya','krym','buryatiya')) && empty($data['AttachmentAnswer']) && $data['DispClass_id'] == 1) {
				$this->db->trans_rollback();
				return array('Alert_Msg' => 'Пациент не имеет основного прикрепления или прикреплен к другой МО. Продолжить сохранение?');
			}
			if ($data['DispClass_id'] == 2 && !empty($data['EvnPLDispDop13_fid']) && in_array(getRegionNick(), array('kareliya','krym','buryatiya')) ){
				$resp_first_lpu = $this->getFirstResultFromQuery("
					select top 1
						Lpu_id as Lpu_fid
					from
						v_EvnPLDispDop13 epldd13f (nolock)
					where
						epldd13f.EvnPLDispDop13_id = :EvnPLDispDop13_fid
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
				select top 1 EvnPLDispDop13_id
				from v_EvnPLDispDop13 with (nolock)
				where Person_id = :Person_id
					and YEAR(EvnPLDispDop13_consDT) = YEAR(:EvnPLDispDop13_consDT)
					and DispClass_id = :DispClass_id
			";

			$result = $this->db->query($query, array(
				 'DispClass_id' => $data['DispClass_id']
				,'EvnPLDispDop13_consDT' => $data['EvnPLDispDop13_consDate']
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
				select top 1 EvnPLDispProf_id
				from v_EvnPLDispProf with (nolock)
				where Person_id = :Person_id
					and YEAR(EvnPLDispProf_consDT) = YEAR(:EvnPLDispDop13_consDT)
					and DispClass_id = :DispClass_id
			";

			$result = $this->db->query($query, array(
				'DispClass_id' => 5
				,'EvnPLDispDop13_consDT' => $data['EvnPLDispDop13_consDate']
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
			$data['EvnPLDispDop13_IsNewOrder'] = null;

			if (!empty($data['EvnPLDispDop13_fid'])) {
				$query = "
					select top 1
						EPLDD13.Diag_id,
						EPLDD13.Diag_sid,
						EPLDD13.EvnPLDispDop13_IsDisp,
						EPLDD13.NeedDopCure_id,
						EPLDD13.EvnPLDispDop13_IsStac,
						EPLDD13.EvnPLDispDop13_IsSanator,
						EPLDD13.EvnPLDispDop13_SumRick,
						EPLDD13.RiskType_id,
						EPLDD13.EvnPLDispDop13_IsSchool,
						EPLDD13.EvnPLDispDop13_IsProphCons,
						EPLDD13.EvnPLDispDop13_IsHypoten,
						EPLDD13.EvnPLDispDop13_IsLipid,
						EPLDD13.EvnPLDispDop13_IsHypoglyc,
						EPLDD13.HealthKind_id,
						EPLDD13.CardioRiskType_id,
						EPLDD13.EvnPLDispDop13_IsStenocard,
						EPLDD13.EvnPLDispDop13_IsShortCons,
						EPLDD13.EvnPLDispDop13_IsBrain,
						EPLDD13.EvnPLDispDop13_IsDoubleScan,
						EPLDD13.EvnPLDispDop13_IsTub,
						EPLDD13.EvnPLDispDop13_IsTIA,
						EPLDD13.EvnPLDispDop13_IsRespiratory,
						EPLDD13.EvnPLDispDop13_IsLungs,
						EPLDD13.EvnPLDispDop13_IsTopGastro,
						EPLDD13.EvnPLDispDop13_IsBotGastro,
						EPLDD13.EvnPLDispDop13_IsSpirometry,
						EPLDD13.EvnPLDispDop13_IsHeartFailure,
						EPLDD13.EvnPLDispDop13_IsOncology,
						EPLDD13.EvnPLDispDop13_IsEsophag,
						EPLDD13.EvnPLDispDop13_IsSmoking,
						EPLDD13.EvnPLDispDop13_IsRiskAlco,
						EPLDD13.EvnPLDispDop13_IsAlcoDepend,
						EPLDD13.EvnPLDispDop13_IsLowActiv,
						EPLDD13.EvnPLDispDop13_IsIrrational,
						EPLDD13.EvnPLDispDop13_IsUseNarko
					from
						v_EvnPLDispDop13 EPLDD13 (nolock)
					where
						EPLDD13.EvnPLDispDop13_id = :EvnPLDispDop13_fid
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
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
					
				set @Res = :EvnPLDispDop13_id;
				
				exec p_EvnPLDispDop13_ins
					@EvnPLDispDop13_id = @Res output, 
					@MedStaffFact_id = :MedStaffFact_id,
					@EvnPLDispDop13_pid = null, 
					@EvnPLDispDop13_rid = null, 
					@Lpu_id = :Lpu_id, 
					@Server_id = :Server_id, 
					@PersonEvn_id = :PersonEvn_id,
					@EvnPLDispDop13_setDT = :EvnPLDispDop13_setDate, 
					@EvnPLDispDop13_disDT = null, 
					@EvnPLDispDop13_didDT = null, 
					@Morbus_id = null, 
					@EvnPLDispDop13_IsSigned = null, 
					@pmUser_signID = null, 
					@EvnPLDispDop13_signDT = null, 
					@EvnPLDispDop13_VizitCount = null, 
					@EvnPLDispDop13_IsFinish = null, 
					@Person_Age = null, 
					@AttachType_id = 2, 
					@Lpu_aid = null, 
					@EvnPLDispDop13_IsStenocard = :EvnPLDispDop13_IsStenocard, 
					@EvnPLDispDop13_IsShortCons = :EvnPLDispDop13_IsShortCons,
					@EvnPLDispDop13_IsBrain = :EvnPLDispDop13_IsBrain,
					@EvnPLDispDop13_IsDoubleScan = :EvnPLDispDop13_IsDoubleScan, 
					@EvnPLDispDop13_IsTub = :EvnPLDispDop13_IsTub,
					@EvnPLDispDop13_IsTIA = :EvnPLDispDop13_IsTIA,
					@EvnPLDispDop13_IsRespiratory = :EvnPLDispDop13_IsRespiratory,
					@EvnPLDispDop13_IsLungs = :EvnPLDispDop13_IsLungs,
					@EvnPLDispDop13_IsTopGastro = :EvnPLDispDop13_IsTopGastro,
					@EvnPLDispDop13_IsBotGastro = :EvnPLDispDop13_IsBotGastro,
					@EvnPLDispDop13_IsSpirometry = :EvnPLDispDop13_IsSpirometry,
					@EvnPLDispDop13_IsHeartFailure = :EvnPLDispDop13_IsHeartFailure,
					@EvnPLDispDop13_IsOncology = :EvnPLDispDop13_IsOncology, 
					@EvnPLDispDop13_IsEsophag = :EvnPLDispDop13_IsEsophag, 
					@EvnPLDispDop13_IsSmoking = :EvnPLDispDop13_IsSmoking, 
					@EvnPLDispDop13_IsRiskAlco = :EvnPLDispDop13_IsRiskAlco, 
					@EvnPLDispDop13_IsAlcoDepend = :EvnPLDispDop13_IsAlcoDepend, 
					@EvnPLDispDop13_IsLowActiv = :EvnPLDispDop13_IsLowActiv, 
					@EvnPLDispDop13_IsIrrational = :EvnPLDispDop13_IsIrrational, 
					@EvnPLDispDop13_IsUseNarko = :EvnPLDispDop13_IsUseNarko, 
					@Diag_id = :Diag_id, 
					@Diag_sid = :Diag_sid, 
					@EvnPLDispDop13_IsDisp = :EvnPLDispDop13_IsDisp, 
					@NeedDopCure_id = :NeedDopCure_id, 
					@EvnPLDispDop13_IsStac = :EvnPLDispDop13_IsStac, 
					@EvnPLDispDop13_IsSanator = :EvnPLDispDop13_IsSanator, 
					@EvnPLDispDop13_SumRick = :EvnPLDispDop13_SumRick, 
					@RiskType_id = :RiskType_id, 
					@EvnPLDispDop13_IsSchool = :EvnPLDispDop13_IsSchool, 
					@EvnPLDispDop13_IsProphCons = :EvnPLDispDop13_IsProphCons, 
					@EvnPLDispDop13_IsHypoten = :EvnPLDispDop13_IsHypoten, 
					@EvnPLDispDop13_IsLipid = :EvnPLDispDop13_IsLipid, 
					@EvnPLDispDop13_IsHypoglyc = :EvnPLDispDop13_IsHypoglyc, 
					@HealthKind_id = :HealthKind_id, 
					@EvnPLDispDop13_IsEndStage = null, 
					@EvnPLDispDop13_IsTwoStage = null,
					@EvnPLDispDop13_consDT = :EvnPLDispDop13_consDate,
					@EvnPLDispDop13_IsMobile = :EvnPLDispDop13_IsMobile,
					@EvnPLDispDop13_IsOutLpu = :EvnPLDispDop13_IsOutLpu,
					@Lpu_mid = :Lpu_mid,
					@CardioRiskType_id = :CardioRiskType_id, 
					@DispClass_id = :DispClass_id,
					@PayType_id = :PayType_id,
					@EvnPLDispDop13_fid = :EvnPLDispDop13_fid,
					@EvnPLDispDop13_IsNewOrder = :EvnPLDispDop13_IsNewOrder,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output
 
				select @Res as EvnPLDispDop13_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
				pd.Diag_id,
				convert(varchar(10), pd.PersonDisp_begDate, 104) as PersonDisp_begDate
			from
				v_PersonDisp pd (nolock)
				inner join v_Diag d (nolock) on d.Diag_id = pd.Diag_id
				left join v_ProfileDiag pdiag (nolock) on pdiag.Diag_id = d.Diag_pid
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
			if ($data['DispClass_id'] == 1 && !empty($data['EvnPLDispDop13_IsNewOrder']) && $data['EvnPLDispDop13_IsNewOrder'] == 2) {
				$onDate = date('Y', strtotime($data['EvnPLDispDop13_consDate'])) . '-12-31';
			} else if (!in_array(getRegionNick(), [ 'ekb', 'perm' ]) && !empty($dateX) && $data['DispClass_id'] == 2 && !empty($data['EvnPLDispDop13_fid'])) {
				// достаём дату согласия из первого этапа, т.к. связки загружаются именно на неё
				$resp_first = $this->queryResult("
					select
						convert(varchar(10), epldd13f.EvnPLDispDop13_consDT, 120) as EvnPLDispDop13_firstConsDate,
						ISNULL(epldd13f.EvnPLDispDop13_IsNewOrder, 1) as EvnPLDispDop13_IsNewOrder
					from
						v_EvnPLDispDop13 epldd13f (nolock)
					where
						epldd13f.EvnPLDispDop13_id = :EvnPLDispDop13_fid
				", array(
					'EvnPLDispDop13_fid' => $data['EvnPLDispDop13_fid']
				));
				if (!empty($resp_first[0]['EvnPLDispDop13_firstConsDate']) && $resp_first[0]['EvnPLDispDop13_firstConsDate'] < $dateX && $resp_first[0]['EvnPLDispDop13_IsNewOrder'] == 1) {
					$onDate = $resp_first[0]['EvnPLDispDop13_firstConsDate'];
				}
			}

			// проверяем что сохраняемое SurveyTypeLink удовлетворяет по датам дате согласия / можно будет убрать после того как выяснится причина некорректных сохранений.
			$SurveyTypeLink_id = $this->getFirstResultFromQuery("
				select top 1
					STL.SurveyTypeLink_id
				from
					v_SurveyTypeLink STL (nolock)
				where
					STL.SurveyTypeLink_id = :SurveyTypeLink_id
					and (STL.SurveyTypeLink_begDate is null or STL.SurveyTypeLink_begDate <= :EvnPLDispDop13_consDate)
					and (STL.SurveyTypeLink_endDate is null or STL.SurveyTypeLink_endDate >= :EvnPLDispDop13_consDate)
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
					declare
						@Res bigint,
						@ErrCode int,
						@ErrMessage varchar(4000);
					set @Res = :DopDispInfoConsent_id;
					
					exec {$proc}
						@DopDispInfoConsent_id = @Res output, 
						@EvnPLDisp_id = :EvnPLDispDop13_id, 
						@DopDispInfoConsent_IsAgree = :DopDispInfoConsent_IsAgree, 
						@DopDispInfoConsent_IsEarlier = :DopDispInfoConsent_IsEarlier, 
						@DopDispInfoConsent_IsImpossible = :DopDispInfoConsent_IsImpossible,
						@SurveyTypeLink_id = :SurveyTypeLink_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output
	 
					select @Res as DopDispInfoConsent_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
								v_SurveyType st (nolock)
								inner join v_SurveyTypeLink stl (nolock) on stl.SurveyType_id = st.SurveyType_id
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
				select top 1
					 EvnPLDispDop13_pid
					,EvnPLDispDop13_rid
					,EvnPLDispDop13_fid
					,Lpu_id
					,Server_id
					,PersonEvn_id
					,convert(varchar(20), EvnPLDispDop13_setDT, 120) as EvnPLDispDop13_setDT
					,convert(varchar(20), EvnPLDispDop13_disDT, 120) as EvnPLDispDop13_disDT
					,convert(varchar(20), EvnPLDispDop13_didDT, 120) as EvnPLDispDop13_didDT
					,Morbus_id
					,EvnPLDispDop13_IsSigned
					,pmUser_signID
					,EvnPLDispDop13_signDT
					,EvnPLDispDop13_IsFinish
					,EvnPLDispDop13_IsNewOrder
					,EvnPLDispDop13_IndexRep
					,EvnPLDispDop13_IndexRepInReg
					,EvnDirection_aid
					,Person_Age
					,AttachType_id
					,Lpu_aid
					,DispClass_id
					," . ($itemsCount == 0 ? "null as " : "") . "EvnPLDispDop13_IsStenocard
					," . ($itemsCount == 0 ? "null as " : "") . "EvnPLDispDop13_IsShortCons
					," . ($itemsCount == 0 ? "null as " : "") . "EvnPLDispDop13_IsBrain
					," . ($itemsCount == 0 ? "null as " : "") . "EvnPLDispDop13_IsDoubleScan
					," . ($itemsCount == 0 ? "null as " : "") . "EvnPLDispDop13_IsTub
					," . ($itemsCount == 0 ? "null as " : "") . "EvnPLDispDop13_IsTIA
					," . ($itemsCount == 0 ? "null as " : "") . "EvnPLDispDop13_IsRespiratory
					," . ($itemsCount == 0 ? "null as " : "") . "EvnPLDispDop13_IsLungs
					," . ($itemsCount == 0 ? "null as " : "") . "EvnPLDispDop13_IsTopGastro
					," . ($itemsCount == 0 ? "null as " : "") . "EvnPLDispDop13_IsBotGastro
					," . ($itemsCount == 0 ? "null as " : "") . "EvnPLDispDop13_IsSpirometry
					," . ($itemsCount == 0 ? "null as " : "") . "EvnPLDispDop13_IsHeartFailure
					," . ($itemsCount == 0 ? "null as " : "") . "EvnPLDispDop13_IsOncology
					," . ($itemsCount == 0 ? "null as " : "") . "EvnPLDispDop13_IsEsophag
					," . ($itemsCount == 0 ? "null as " : "") . "EvnPLDispDop13_IsSmoking
					," . ($itemsCount == 0 ? "null as " : "") . "EvnPLDispDop13_IsRiskAlco
					," . ($itemsCount == 0 ? "null as " : "") . "EvnPLDispDop13_IsAlcoDepend
					," . ($itemsCount == 0 ? "null as " : "") . "EvnPLDispDop13_IsLowActiv
					," . ($itemsCount == 0 ? "null as " : "") . "EvnPLDispDop13_IsIrrational
					," . ($itemsCount == 0 ? "null as " : "") . "EvnPLDispDop13_IsUseNarko
					," . ($itemsCount == 0 ? "null as " : "") . "Diag_id
					," . ($itemsCount == 0 ? "null as " : "") . "Diag_sid
					," . ($itemsCount == 0 ? "null as " : "") . "EvnPLDispDop13_IsDisp
					," . ($itemsCount == 0 ? "null as " : "") . "NeedDopCure_id
					," . ($itemsCount == 0 ? "null as " : "") . "EvnPLDispDop13_IsStac
					," . ($itemsCount == 0 ? "null as " : "") . "EvnPLDispDop13_IsSanator
					," . ($itemsCount == 0 ? "null as " : "") . "EvnPLDispDop13_SumRick
					," . ($itemsCount == 0 ? "null as " : "") . "RiskType_id
					," . ($itemsCount == 0 ? "null as " : "") . "EvnPLDispDop13_IsSchool
					," . ($itemsCount == 0 ? "null as " : "") . "EvnPLDispDop13_IsProphCons
					," . ($itemsCount == 0 ? "null as " : "") . "EvnPLDispDop13_IsHypoten
					," . ($itemsCount == 0 ? "null as " : "") . "EvnPLDispDop13_IsLipid
					," . ($itemsCount == 0 ? "null as " : "") . "EvnPLDispDop13_IsHypoglyc
					," . ($itemsCount == 0 ? "null as " : "") . "HealthKind_id
					," . ($itemsCount == 0 ? "null as " : "") . "EvnPLDispDop13_IsEndStage
					," . ($itemsCount == 0 ? "null as " : "") . "EvnPLDispDop13_IsTwoStage
					," . ($itemsCount == 0 ? "null as " : "") . "CardioRiskType_id
				from v_EvnPLDispDop13 with (nolock)
				where EvnPLDispDop13_id = :EvnPLDispDop13_id
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
						declare
							@Res bigint,
							@ErrCode int,
							@ErrMessage varchar(4000);
							
						set @Res = :EvnPLDispDop13_id;
						
						exec p_EvnPLDispDop13_upd
							@EvnPLDispDop13_id = @Res output, 
					";

					foreach ( $resp[0] as $key => $value ) {
						$query .= "@" . $key . " = :" . $key . ",";
					}

					$query .= "
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output
 
						select @Res as EvnPLDispDop13_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
						declare
							@ErrCode int,
							@ErrMessage varchar(4000);
						
						exec p_DopDispInfoConsent_del
							@DopDispInfoConsent_id = :DopDispInfoConsent_id,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output
		 
						select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
				update EvnPLDisp with (rowlock) set EvnPLDisp_IsRefusal = 2 where EvnPLDisp_id = :EvnPLDisp_id
			";
			$this->db->query($query, array(
				'EvnPLDisp_id' => $data['EvnPLDispDop13_id']
			));
		} else {
			$query = "
				update EvnPLDisp with (rowlock) set EvnPLDisp_IsRefusal = 1 where EvnPLDisp_id = :EvnPLDisp_id
			";
			$this->db->query($query, array(
				'EvnPLDisp_id' => $data['EvnPLDispDop13_id']
			));
		}
		
		// второй этап
		$sec_resp = $this->queryResult("
			select 
				EvnPLDispDop13_id
			from 
				v_EvnPLDispDop13 (nolock) 
			where 
				EvnPLDispDop13_fid = :EvnPLDispDop13_fid
		", array(
			'EvnPLDispDop13_fid' => $data['EvnPLDispDop13_id']
		));

		// на Екб - если есть второй этап, статус не меняем
		// меняем, только если нет карты 2-го этапа
		if ( !count($sec_resp) ) {
			$query = "
				update EvnPLBase with (rowlock) set EvnPLBase_IsFinish = 1 where EvnPLBase_id = :EvnPLDispDop13_id;
				update EvnPLDispDop13 with (rowlock) set EvnPLDispDop13_IsEndStage = 1 where EvnPLDispDop13_id = :EvnPLDispDop13_id;
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
					 DDIC.DopDispInfoConsent_id
					,EVDD.EvnVizitDispDop_id
					,EUDD.EvnUslugaDispDop_id
					,STL.SurveyType_id
				from v_DopDispInfoConsent DDIC with (nolock)
					left join v_EvnVizitDispDop EVDD with (nolock) on EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
					left join v_EvnUslugaDispDop EUDD with (nolock) on EUDD.EvnUslugaDispDop_pid = EVDD.EvnVizitDispDop_id
					left join v_SurveyTypeLink STL with (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
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
						if ($data['DispClass_id'] == 1 && !empty($data['EvnPLDispDop13_IsNewOrder']) && $data['EvnPLDispDop13_IsNewOrder'] == 2 && !empty($array['SurveyType_id'])) {
							// Попытаемся найти новое согласие для посещения
							$resp_ddic = $this->queryResult("
								select top 1
									DDIC.DopDispInfoConsent_id,
									STL.UslugaComplex_id
								from
									v_DopDispInfoConsent DDIC with (nolock)
									inner join v_SurveyTypeLink STL with (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
									left join v_EvnVizitDispDop EVDD with (nolock) on EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
								where
									DDIC.EvnPLDisp_id = :EvnPLDisp_id
									and STL.SurveyType_id = :SurveyType_id
									and EVDD.EvnVizitDispDop_id is null
							", array(
								'EvnPLDisp_id' => $data['EvnPLDispDop13_id'],
								'SurveyType_id' => $array['SurveyType_id']
							));
						}

						if (!empty($resp_ddic[0]['DopDispInfoConsent_id'])) {
							// перевяжем услугу к новому согласию
							$query = "
								declare
									@ErrCode int,
									@ErrMsg varchar(400);
					
								set nocount on;
					
								begin try
								
								update
									EvnVizitDisp with (rowlock)
								set
									DopDispInfoConsent_id = :DopDispInfoConsent_id
								where
									EvnVizitDisp_id = :EvnVizitDispDop_id
									
								update
									EvnVizitDispDop with (rowlock)
								set
									UslugaComplex_id = :UslugaComplex_id
								where
									EvnVizitDispDop_id = :EvnVizitDispDop_id
									
								update
									EvnUslugaDispDop with (rowlock)
								set
									DopDispInfoConsent_id = :DopDispInfoConsent_id
								where
									EvnUslugaDispDop_id = :EvnUslugaDispDop_id
									
								update
									EvnUsluga with (rowlock)
								set
									UslugaComplex_id = :UslugaComplex_id
								where
									EvnUsluga_id = :EvnUslugaDispDop_id
									
								end try
								begin catch
									set @ErrCode = error_number();
									set @ErrMsg = error_message();
								end catch
					
								set nocount off;
					
								select @ErrCode as Error_Code, @ErrMsg as Error_Msg;
							";
							$result = $this->db->query($query, array(
								'EvnVizitDispDop_id' => $array['EvnVizitDispDop_id'],
								'EvnUslugaDispDop_id' => $array['EvnUslugaDispDop_id'],
								'UslugaComplex_id' => $resp_ddic[0]['UslugaComplex_id'],
								'DopDispInfoConsent_id' => $resp_ddic[0]['DopDispInfoConsent_id'],
								'pmUser_id' => $data['pmUser_id']
							));

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
						} else {
							$query = "
								declare
									@ErrCode int,
									@ErrMessage varchar(4000);
	
								exec p_EvnVizitDispDop_del
									@EvnVizitDispDop_id = :EvnVizitDispDop_id,
									@pmUser_id = :pmUser_id,
									@Error_Code = @ErrCode output,
									@Error_Message = @ErrMessage output;
	
								select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
						declare
							@ErrCode int,
							@ErrMessage varchar(4000);
						
						exec p_DopDispInfoConsent_del
							@DopDispInfoConsent_id = :DopDispInfoConsent_id, 
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output
		 
						select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
			select top 1
				Diag_id
			from v_Diag (nolock)
			where Diag_Code = :Diag_Code
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
			SELECT TOP 1
				EvnPLDispDop13_id,
				PersonEvn_id,
				Server_id
			FROM
				v_EvnPLDispDop13 EPLDD (nolock)
			WHERE
				EPLDD.EvnPLDispDop13_id = :EvnPLDisp_id
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
	 * Сохранение анкетирования
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function saveDopDispQuestionGrid($data)
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
			select top 1
				evdd.EvnVizitDispDop_id,
			    eudd.EvnUslugaDispDop_id,
			    stl.DispClass_id,
			    st.SurveyType_Code
			from
				v_EvnUslugaDispDop eudd (nolock)
				inner join v_EvnVizitDispDop evdd (nolock) on evdd.EvnVizitDispDop_id = eudd.EvnUslugaDispDop_pid
				inner join v_DopDispInfoConsent ddic (nolock) on ddic.DopDispInfoConsent_id = evdd.DopDispInfoConsent_id
				inner join v_SurveyTypeLink stl (nolock) on stl.SurveyTypeLink_id = ddic.SurveyTypeLink_id
				inner join v_SurveyType st (nolock) on st.SurveyType_id = stl.SurveyType_id
			where evdd.EvnVizitDispDop_pid = :EvnVizitDispDop_pid
			  and st.SurveyType_Code in (19,27)
			  and cast(eudd.EvnUslugaDispDop_didDT as date) < :DopDispQuestion_setDate
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
			select top 1
				STL.UslugaComplex_id, -- услуга которую нужно сохранить
				EUDDData.EvnUslugaDispDop_id
			from
				v_SurveyTypeLink STL (nolock)
				inner join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
				inner join v_DopDispInfoConsent ddic (nolock) on ddic.SurveyTypeLink_id = stl.SurveyTypeLink_id
				outer apply(
					select top 1 EvnUslugaDispDop_id
					from v_EvnUslugaDispDop EUDD with (nolock)
					where EUDD.EvnUslugaDispDop_pid = :EvnPLDisp_id
					  and EUDD.UslugaComplex_id IN (select UslugaComplex_id from v_SurveyTypeLink (nolock) where SurveyType_id = STL.SurveyType_id)
					  and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
				) EUDDData
			where ST.SurveyType_Code = 2
			  and ddic.EvnPLDisp_id = :EvnPLDisp_id
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
		if (empty($data["UslugaComplex_id"])) {
			$data["UslugaComplex_id"] = $resp[0]["UslugaComplex_id"];
		}
		$query = "
			declare
				@EvnUslugaDispDop_id bigint,
				@PayType_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @EvnUslugaDispDop_id = :EvnUslugaDispDop_id;
			set @PayType_id = (select top 1 PayType_id from v_PayType with (nolock) where PayType_SysNick = 'dopdisp');
			exec {$proc}
				@EvnUslugaDispDop_id = @EvnUslugaDispDop_id output,
				@EvnUslugaDispDop_pid = :EvnPLDisp_id,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@EvnDirection_id = NULL,
				@PersonEvn_id = :PersonEvn_id,
				@PayType_id = @PayType_id,
				@UslugaPlace_id = 1,
				@EvnUslugaDispDop_setDT = NULL,
				@UslugaComplex_id = :UslugaComplex_id,
				@EvnUslugaDispDop_didDT = :DopDispQuestion_setDate,
				@ExaminationPlace_id = NULL,
				@Diag_id = :Diag_id,
				@DopDispDiagType_id = :DopDispDiagType_id,
				@EvnUslugaDispDop_DeseaseStage = :DeseaseStage,
				@LpuSection_uid = :LpuSection_uid,
				@MedPersonal_id = :MedPersonal_id,
				@MedStaffFact_id = :MedStaffFact_id,
				@EvnUslugaDispDop_ExamPlace = NULL,
				@EvnPrescrTimetable_id = null,
				@EvnPrescr_id = null,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @EvnUslugaDispDop_id as EvnUslugaDispDop_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		//exit(getDebugSQL($query, $data));
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

		$Sex_id = $this->getFirstResultFromQuery("
			select
				ps.Sex_id
			from
				v_EvnPLDisp epld (nolock)
				inner join v_PersonState ps (nolock) on ps.Person_id = epld.Person_id
			where
				epld.EvnPLDisp_id = :EvnPLDisp_id
		", [
			'EvnPLDisp_id' => $data['EvnPLDisp_id']
		], true);

		// Получаем существующие данные из БД
		$ExistingDopDispQuestionData = [];

		$query = "
			select
				QuestionType_id,
			    DopDispQuestion_id,
			    DopDispQuestion_ValuesStr
			from v_DopDispQuestion with (nolock)
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
			"NeedConsult" => []
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
			if (!empty($data["NeedCalculation"]) && $data["NeedCalculation"] == 1) {
				switch ($item["QuestionType_id"]) {
					// Ранее известные имеющиеся заболевания
					case 2:
					case 94:
					case 142:
					case 675:
					case 817:
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
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @Res = :DopDispQuestion_id;
				
				exec {$proc}
					@DopDispQuestion_id = @Res output, 
					@EvnPLDisp_id = :EvnPLDisp_id, 
					@QuestionType_id = :QuestionType_id, 
					@DopDispQuestion_IsTrue = :DopDispQuestion_IsTrue, 
					@DopDispQuestion_Answer = :DopDispQuestion_Answer, 
					@DopDispQuestion_ValuesStr = :DopDispQuestion_ValuesStr,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output
 
				select @Res as DopDispQuestion_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
			select top 1 EvnUslugaDispDop_id
			from v_EvnUslugaDispDop with (nolock)
			where EvnUslugaDispDop_pid = :EvnPLDisp_id
				and UslugaComplex_id = :UslugaComplex_id
				and EvnUslugaDispDop_id != :EvnUslugaDispDop_id
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
	 * Получение формализованных параметров по SurveyType_id
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function getFormalizedInspectionParamsBySurveyType($data)
	{
		if (empty($data["SurveyType_id"])) {
			throw new Exception("Не указан обязательный параметр SurveyType_id");
		}
		if (empty($data["EvnPLDispDop13_id"])) {
			throw new Exception("Не указан обязательный параметр EvnPLDispDop13_id");
		}
		$query = "
			select
			    pp.FormalizedInspectionParams_id,
			    FormalizedInspectionParams_Name,
			    FormalizedInspectionParams_IsDefault,
			    FormalizedInspectionParams_Directory
			from v_FormalizedInspectionParams pp
			where pp.SurveyType_id = :SurveyType_id
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, ["SurveyType_id" => $data["SurveyType_id"]]);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных");
		}
		$returnResult = $result->result_array();
		$idxs = [];
		foreach ($returnResult as $returnResultItem) {
			$idxs[] = $returnResultItem["FormalizedInspectionParams_id"];
		}
		$idxsString = implode(",", $idxs);

		$query = "
			select EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\"
			from v_EvnUslugaDispDop with (nolock)
			where EvnUslugaDispDop_rid = :EvnPLDisp_id
		";
		$result = $this->db->query($query, ["EvnPLDisp_id" => $data["EvnPLDispDop13_id"]]);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных");
		}
		$result = $result->result_array();
		if (count($result) == 0) {
			throw new Exception("Не найден EvnUslugaDispDop_id для указанного EvnPLDisp_id");
		}
		$EvnUslugaDispDop_id = $result[0]["EvnUslugaDispDop_id"];
		$query = "
			select
				FormalizedInspection_id as \"FormalizedInspection_id\",
			    FormalizedInspectionParams_id as \"FormalizedInspectionParams_id\",
			    FormalizedInspection_Result as \"FormalizedInspection_Result\"
			from v_FormalizedInspection
			where EvnUslugaDispDop_id = {$EvnUslugaDispDop_id}
			  and FormalizedInspectionParams_id in ({$idxsString})
		";
		$result = $this->db->query($query);
		$result = $result->result_array();
		foreach ($returnResult as &$returnResultItem) {
			foreach ($result as $resultItem) {
				if($returnResultItem["FormalizedInspectionParams_id"] == $resultItem["FormalizedInspectionParams_id"]) {
					$returnResultItem["value"] = $resultItem["FormalizedInspection_Result"];
				}
			}
		}
		return $returnResult;
	}

	/**
	 * Форма ДВН(Панели) Получение данных о последнем изменившем и время изменения
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function getDVNPanelsLastUpdater($data)
	{
		if (empty($data["EvnPLDispDop13_id"])) {
			throw new Exception("Не указан обязательный параметр EvnPLDispDop13_id");
		}
		$panelCode = null;
		if (!empty($data["panelCode"])) {
			$panelCode = $data["panelCode"];
		}
		$returnResult = [
			["panelCode" => "AnketaPanel", "lastUpdater" => "", "lastUpdateDateTime" => ""],
			["panelCode" => "AntropoPanel", "lastUpdater" => "", "lastUpdateDateTime" => ""],
			["panelCode" => "PrescrPanel", "lastUpdater" => "", "lastUpdateDateTime" => ""],
			["panelCode" => "TherapistViewPanel", "lastUpdater" => "", "lastUpdateDateTime" => ""],
		];
		/*
		if ($panelCode != null) {

		}*/
		return $returnResult;
	}

	/**
	 * Форма ДВН(Окно выполнения услуги) Получение справочников
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function getDVNExecuteWindowRDS($data)
	{
		if (empty($data["fieldName"])) {
			throw new Exception("Не указан обязательный параметр fieldName");
		}
		$result = [];
		if ($data["fieldName"] == "UslugaPlace") {
			$query = "
				select
				    UslugaPlace_id as \"UslugaPlace_id\",
				    UslugaPlace_Code as \"UslugaPlace_Code\",
				    UslugaPlace_Name as \"UslugaPlace_Name\",
				    UslugaPlace_SysNick as \"UslugaPlace_SysNick\"
				from v_UslugaPlace with(nolock)
			";
			/**@var CI_DB_result $result */
			$result = $this->db->query($query);
			if (!is_object($result)) {
				throw new Exception("Ошибка при выполнении запроса к базе данных");
			}
			$result = $result->result_array();
		}
		return $result;
	}

	/**
	 * Проверка доступа текущего пользователя на изменения указанной ДВП(по EvnPLDispDop13_id)
	 * @param $data
	 * @return bool
	 * @throws Exception
	 */
	function checkEvnPLDispDop13Access($data)
	{
		$query = "
			select count(0) as cnt
			from v_EvnPLDispDop13
			where EvnPLDispDop13_id = :EvnPLDispDop13_id
			  and pmUser_insID = :pmUser_id
		";
		$queryParams = [
			"pmUser_id" => $data["pmUser_id"],
			"EvnPLDispDop13_id" => $data["EvnPLDispDop13_id"],
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных");
		}
		$result = $result->result_array();
		return ($result[0]["cnt"] == 0) ? false : true;
	}

	/**
	 * Сохранение формализованных параметров (Checkbox)
	 * @param array $data
	 * @return bool
	 * @throws Exception
	 */
	function saveFormalizedInspectionParamsCheck($data)
	{
		$query = "
			select EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\"
			from v_EvnUslugaDispDop with (nolock)
			where EvnUslugaDispDop_rid = :EvnPLDisp_id
		";
		/**
		 * @var CI_DB_result $result
		 * @var CI_DB_result $findResult
		 */
		$result = $this->db->query($query, ["EvnPLDisp_id" => $data["EvnPLDisp_id"]]);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных");
		}
		$result = $result->result_array();
		if (count($result) == 0) {
			throw new Exception("Не найден EvnUslugaDispDop_id для указанного EvnPLDisp_id");
		}
		$EvnUslugaDispDop_id = $result[0]["EvnUslugaDispDop_id"];
		$findQuery = "
			select FormalizedInspection_id as \"FormalizedInspection_id\"
			from v_FormalizedInspection
			where EvnUslugaDispDop_id = :EvnUslugaDispDop_id
			  and FormalizedInspectionParams_id = :FormalizedInspectionParams_id
		";
		$this->beginTransaction();
		$findQueryParams = [
			"EvnUslugaDispDop_id" => $EvnUslugaDispDop_id,
			"FormalizedInspectionParams_id" => $data["id"]
		];
		$findResult = $this->db->query($findQuery, $findQueryParams);
		if (!is_object($findResult)) {
			$this->rollbackTransaction();
			throw new Exception("Ошибка при выполнении запроса к базе данных");
		}
		$findResult = $findResult->result_array();
		$procedure = (count($findResult) == 0) ? "p_FormalizedInspection_ins" : "p_FormalizedInspection_upd";
		$FormalizedInspection_id = (count($findResult) != 0) ? $findResult[0]["FormalizedInspection_id"] : null;
		$query = "
			declare
				@FormalizedInspection_id bigint,
				@PayType_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @FormalizedInspection_id = :FormalizedInspection_id;
			exec {$procedure}
				@FormalizedInspection_id = @FormalizedInspection_id output,
				@EvnUslugaDispDop_id = :EvnUslugaDispDop_id,
				@FormalizedInspectionParams_id = :FormalizedInspectionParams_id,
				@FormalizedInspection_Result = :FormalizedInspection_Result,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @FormalizedInspection_id as FormalizedInspection_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$queryParams = [
			"FormalizedInspection_id" => $FormalizedInspection_id,
			"EvnUslugaDispDop_id" => $EvnUslugaDispDop_id,
			"FormalizedInspectionParams_id" => $data["id"],
			"FormalizedInspection_Result" => $data["check"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			$this->rollbackTransaction();
			throw new Exception("Ошибка при выполнении запроса к базе данных");
		}
		$this->commitTransaction();
		return true;
	}

	/**
	 * Сохранение формализованных параметров
	 * @param array $data
	 * @return bool
	 * @throws Exception
	 */
	function saveFormalizedInspectionParamsText($data)
	{
		/**
		 * EvnPLDisp_id: "590930000008321"
		 * checks: "["checkedValue_64","checkedValue_66"]"
		 * responseValue: "<p>111</p><p>2222</p>"
		 */
		$query = "
			select EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\"
			from v_EvnUslugaDispDop with (nolock)
			where EvnUslugaDispDop_rid = :EvnPLDisp_id
		";
		/**
		 * @var CI_DB_result $result
		 * @var CI_DB_result $findResult
		 */
		$result = $this->db->query($query, ["EvnPLDisp_id" => $data["EvnPLDisp_id"]]);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных");
		}
		$result = $result->result_array();
		if (count($result) == 0) {
			throw new Exception("Не найден EvnUslugaDispDop_id для указанного EvnPLDisp_id");
		}
		$EvnUslugaDispDop_id = $result[0]["EvnUslugaDispDop_id"];
		$query = "
			select FormalizedInspectionParams_id as \"FormalizedInspectionParams_id\"
			from v_FormalizedInspectionParams
			where SurveyType_id = 19
			  and FormalizedInspectionParams_Directory is null
		";
		$result = $this->db->query($query);
		if (!is_object($result)) {
			throw new Exception("Ошибка при выполнении запроса к базе данных");
		}
		$result = $result->result_array();
		if (count($result) == 0) {
			throw new Exception("Не найден параметр текста");
		}
		$FormalizedInspectionParams_id = $result[0]["FormalizedInspectionParams_id"];
		$findQuery = "
			select FormalizedInspection_id as \"FormalizedInspection_id\"
			from v_FormalizedInspection
			where EvnUslugaDispDop_id = :EvnUslugaDispDop_id
			  and FormalizedInspectionParams_id = :FormalizedInspectionParams_id
		";
		$this->beginTransaction();
		$findQueryParams = [
			"EvnUslugaDispDop_id" => $EvnUslugaDispDop_id,
			"FormalizedInspectionParams_id" => $FormalizedInspectionParams_id
		];
		$findResult = $this->db->query($findQuery, $findQueryParams);
		if (!is_object($findResult)) {
			$this->rollbackTransaction();
			throw new Exception("Ошибка при выполнении запроса к базе данных");
		}
		$findResult = $findResult->result_array();
		$procedure = (count($findResult) == 0) ? "p_FormalizedInspection_ins" : "p_FormalizedInspection_upd";
		$FormalizedInspection_id = (count($findResult) != 0) ? $findResult[0]["FormalizedInspection_id"] : null;
		$query = "
			declare
				@FormalizedInspection_id bigint,
				@PayType_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @FormalizedInspection_id = :FormalizedInspection_id;
			exec {$procedure}
				@FormalizedInspection_id = @FormalizedInspection_id output,
				@EvnUslugaDispDop_id = :EvnUslugaDispDop_id,
				@FormalizedInspectionParams_id = :FormalizedInspectionParams_id,
				@FormalizedInspection_Result = :FormalizedInspection_Result,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @FormalizedInspection_id as FormalizedInspection_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$queryParams = [
			"FormalizedInspection_id" => $FormalizedInspection_id,
			"EvnUslugaDispDop_id" => $EvnUslugaDispDop_id,
			"FormalizedInspectionParams_id" => $FormalizedInspectionParams_id,
			"FormalizedInspection_Result" => $data["responseValue"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			$this->rollbackTransaction();
			throw new Exception("Ошибка при выполнении запроса к базе данных");
		}
		$this->commitTransaction();
		return true;
	}

	/**
	 * Загрузка списка изменившихся согласий
	 * @throws Exception
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
			select top 1 PersonPrivilegeWOW_id from v_PersonPrivilegeWOW (nolock) where Person_id = :Person_id
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
	function getAgeModification($data) {
		$ageModification = "";
		$dateX = $this->getNewDVNDate();
		$newDVN = (!empty($dateX) && strtotime($data['onDate']) >= strtotime($dateX));

		if (!in_array($this->getRegionNick(), ['kz']) && strtotime($data['onDate']) >= strtotime('01.01.2018')) {
			// ДВН с 2018 года.
			$personPrivilegeCodeList = $this->getPersonPrivilegeCodeList();

			if ( count($personPrivilegeCodeList) > 0 ) {
				$ageModification .= "
					if ( " . ($newDVN == true ? "@age < 40 and " : "") . "@age % 3 != 0 )
						begin
							declare @PersonPrivilege_id bigint;

							set @PersonPrivilege_id = (
								select top 1 pp.PersonPrivilege_id
								from v_PersonPrivilege pp (nolock)
									inner join v_PrivilegeType pt (nolock) on pt.PrivilegeType_id = pp.PrivilegeType_id
								where pp.Person_id = :Person_id
									and pt.PrivilegeType_Code in ('" . implode("','", $personPrivilegeCodeList) . "')
									and pp.PersonPrivilege_begDate <= @EvnPLDispDop13_YearEndDate
									and (pp.PersonPrivilege_endDate > @EvnPLDispDop13_YearEndDate or pp.PersonPrivilege_endDate is null)
							);

							if ( @PersonPrivilege_id is not null )
								set @age = cast(round(@age/3.0, 0) * 3 as int); -- округление до ближайшего кратного трём
						end
				";
			}
			else if (in_array($this->getRegionNick(), ['ufa', 'ekb', 'kareliya', 'krasnoyarsk', 'penza', 'astra'])) {
				$ageModification .= "
					if ( " . ($newDVN == true ? "@age < 40 and " : "") . "@age % 3 != 0 )
						begin
							declare @PersonPrivilegeWOW_id bigint;

							set @PersonPrivilegeWOW_id = (
								select top 1 PersonPrivilegeWOW_id
								from v_PersonPrivilegeWOW (nolock)
								where Person_id = :Person_id
							);

							if ( @PersonPrivilegeWOW_id is not null )
								set @age = cast(round(@age/3.0, 0) * 3 as int); -- округление до ближайшего кратного трём
						end
				";
			}
			elseif (
				// ДВН с 2020 года.
				strtotime($data['onDate']) >= strtotime('2020-01-01') &&
				// Кроме регионов Казахстан, Карелия, Хакасия, Бурятия, Уфа.
				!in_array($this->getRegionNick(), ['kz', 'kareliya', 'khak', 'buryatiya', 'ufa'])
			) {
				$ageModification .= "
					-- от 18 до 39 (включительно) лет.
					-- не кранен 3-м.
					if (@age >= 18 AND @age <= 39 AND @age % 3 <> 0)
					begin
						-- возрастная группа кратная 3-м.
						-- округление до ближайшего кратного трём.
						set @age = cast(round(@age/3.0, 0) * 3 as int);
					end
				";
			}
		}
		else {
			$ageModification .= "
				set @age = cast(round(@age/3.0, 0) * 3 as int); -- округление до ближайшего кратного трём
			";
		}

		if ( $newDVN == false ) {
			$ageModification .= "
				if ( @age = 18 )
					set @age = 21;
			";
		}

		$ageModification .= "
			if ( @age > 99 )
				set @age = 99;
		";

		return $ageModification;
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
					convert(varchar(10), epldd13f.EvnPLDispDop13_consDT, 120) as EvnPLDispDop13_firstConsDate,
					ISNULL(epldd13f.EvnPLDispDop13_IsNewOrder, 1) as EvnPLDispDop13_IsNewOrder
				from
					v_EvnPLDispDop13 epldd13f (nolock)
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

		if ( $data['session']['region']['nick'] == 'ufa' ) { // для уфы дополнительно отфильтровываем услуги посещений по LpuLevel 
			$filter .= " and (ISNULL(ucat.UslugaCategory_SysNick, '') != 'lpusection' or ((case when LpuLevel.LpuLevel_code in (2,6) then '6' when LpuLevel.LpuLevel_code in (3,5) then '5' when LpuLevel.LpuLevel_code in (1,8) then '8' end) = left(UC.UslugaComplex_Code,1)))";
			$filter .= " and (UC.UslugaComplex_id is not null or Stl.UslugaComplex_id is null)";

			$joinList[] = "left join v_Lpu lpu with (nolock) on lpu.Lpu_id = :Lpu_id";
			$joinList[] = "left join v_LpuLevel LpuLevel with (nolock) on LpuLevel.LpuLevel_id = lpu.LpuLevel_id";
			$joinList[] = "left join v_UslugaCategory ucat with (nolock) on ucat.UslugaCategory_id = UC.UslugaCategory_id";

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
				select top 1 PersonPrivilegeWOW_id
				from v_PersonPrivilegeWOW ppw (nolock)
				where Person_id = :Person_id
			", $params);

			if ($data['DispClass_id'] == 1 && !empty($PersonPrivilegeWOW_id)) {
				$filter .= " and STL.SurveyTypeLink_IsWow = 2";
			}
			else {
				$filter .= " and ISNULL(STL.SurveyTypeLink_IsWow, 1) = 1";
			}
		}

		$ageModification = $this->getAgeModification(array(
			'onDate' => $data['EvnPLDispDop13_consDate']
		));

		$select = "
			select
				ISNULL(MAX(DDIC.DopDispInfoConsent_id), -MAX(STL.SurveyTypeLink_id)) as DopDispInfoConsent_id,
				MAX(DDIC.EvnPLDisp_id) as EvnPLDispDop13_id,
				MAX(STL.SurveyTypeLink_id) as SurveyTypeLink_id,
				ISNULL(MAX(STL.SurveyTypeLink_IsNeedUsluga), 1) as SurveyTypeLink_IsNeedUsluga,
				ISNULL(MAX(STL.SurveyTypeLink_IsDel), 1) as SurveyTypeLink_IsDel,
				MAX(ST.SurveyType_Code) as SurveyType_Code,
				MAX(ST.SurveyType_Name) as SurveyType_Name,
				case WHEN MAX(DDIC.DopDispInfoConsent_id) is null or MAX(DDIC.DopDispInfoConsent_IsAgree) = 2 then 1 else 0 end as DopDispInfoConsent_IsAgree,
				case WHEN MAX(DDIC.DopDispInfoConsent_IsEarlier) = 2 then 1 else 0 end as DopDispInfoConsent_IsEarlier,
				case WHEN ISNULL(MAX(SurveyTypeLink_IsImpossible), 1) = 1 then 'hidden' WHEN MAX(DDIC.DopDispInfoConsent_IsImpossible) = 2 then '1' else '0' end as DopDispInfoConsent_IsImpossible,
				MAX(STL.SurveyTypeLink_IsUslPack) as SurveyTypeLink_IsUslPack,
				case when (MAX(STL.SurveyTypeLink_IsPrimaryFlow) = 2 and @age not between Isnull(MAX(STL.SurveyTypeLink_From), 0) and  Isnull(MAX(STL.SurveyTypeLink_To), 999)) then 0 else 1 end as DopDispInfoConsent_IsAgeCorrect,
				case when MAX(ST.SurveyType_Code) IN (1,48) then 0 else 1 end as sortOrder
			from v_SurveyTypeLink STL (nolock)
				left join v_DopDispInfoConsent DDIC (nolock) on DDIC.SurveyTypeLink_id = STL.SurveyTypeLink_id
					and DDIC.EvnPLDisp_id = :EvnPLDispDop13_id
				left join v_UslugaComplex UC (nolock) on UC.UslugaComplex_id = STL.UslugaComplex_id
				left join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
				outer apply (
					select top 1 EvnUslugaDispDop_id
					from v_EvnUslugaDispDop with (nolock)
					where UslugaComplex_id = UC.UslugaComplex_id
						and EvnUslugaDispDop_rid = :EvnPLDispDop13_id
						and ISNULL(EvnUslugaDispDop_IsVizitCode, 1) = 1
				) EUDD
				" . implode(' ', $joinList) . "
			where 
				IsNull(STL.DispClass_id, :DispClass_id) = :DispClass_id -- этап
				and (IsNull(STL.Sex_id, @sex_id) = @sex_id) -- по полу
				and ((@age between Isnull(SurveyTypeLink_From, 0) and  Isnull(SurveyTypeLink_To, 999))
					{$noFilterByAgeInFirstTime}
				) -- по возрасту, в принципе по библии Иссак лет 800 жил же
				and (ISNULL(STL.SurveyTypeLink_IsDel, 1) = 1 or (EUDD.EvnUslugaDispDop_id is not null and DDIC.DopDispInfoConsent_id IS NOT NULL))
				and (STL.SurveyTypeLink_begDate is null or STL.SurveyTypeLink_begDate <= :EvnPLDispDop13_consDate)
				and (STL.SurveyTypeLink_endDate is null or STL.SurveyTypeLink_endDate >= :EvnPLDispDop13_consDate)
				and ISNULL(STL.SurveyTypeLink_ComplexSurvey, 1) = 1
				and ISNULL(STL.SurveyTypeLink_IsEarlier, 1) = 1
				and (STL.SurveyTypeLink_Period is null or STL.SurveyTypeLink_From % STL.SurveyTypeLink_Period = @age % STL.SurveyTypeLink_Period)
				" . $filter . "
		";

		$union = "";
		if ($this->getRegionNick() == 'ufa') {
			// грузим ещё по одной возрастной группе
			$union = "union all
			" . str_replace("@age", "@originalAge", $select)."
					and ST.SurveyType_Code not in (1, 19)
				group by ST.SurveyType_id, STL.SurveyTypeLink_IsDel
			";
		}

		$query = "
			declare
				@age int,
				@originalAge int,
				@EvnPLDispDop13_YearEndDate datetime = cast(substring(:EvnPLDispDop13_consDate, 1, 4) + '-12-31' as datetime),
				@sex_id bigint;

			select top 1
				@sex_id = ISNULL(Sex_id, 3),
				@age = dbo.Age2(Person_BirthDay, @EvnPLDispDop13_YearEndDate)
			from v_PersonState ps (nolock)
			where ps.Person_id = :Person_id
			
			set @originalAge = @age;

			{$ageModification}
			
			if (@age = @originalAge)
				set @originalAge = null; -- не надо грузить ещё раз по тому же возрасту

			{$select}
			group by ST.SurveyType_id, STL.SurveyTypeLink_IsDel
			
			{$union}	
			
			order by sortOrder, SurveyType_Code		
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
					select top 1
						DDIC.DopDispInfoConsent_id,
						DDIC.EvnPLDisp_id as EvnPLDispDop13_id,
						DDIC.SurveyTypeLink_id,
						STL.SurveyTypeLink_IsNeedUsluga as SurveyTypeLink_IsNeedUsluga,
						STL.SurveyTypeLink_IsDel as SurveyTypeLink_IsDel,
						ST.SurveyType_Code,
						'Цитологическое исследование' as SurveyType_Name,
						case WHEN DDIC.DopDispInfoConsent_IsAgree = 2 then 1 else 0 end as DopDispInfoConsent_IsAgree,
						case WHEN DDIC.DopDispInfoConsent_IsEarlier = 2 then 1 else 0 end as DopDispInfoConsent_IsEarlier,
						case WHEN DDIC.DopDispInfoConsent_IsImpossible = 2 then 1 else 0 end as DopDispInfoConsent_IsImpossible,
						STL.SurveyTypeLink_IsUslPack as SurveyTypeLink_IsUslPack,
						1 as DopDispInfoConsent_IsAgeCorrect
					from
						v_DopDispInfoConsent DDIC (nolock)
						inner join v_SurveyTypeLink stl (nolock) on stl.SurveyTypeLink_id = ddic.SurveyTypeLink_id
						inner join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
					where
						DDIC.EvnPLDisp_id = :EvnPLDispDop13_id
						and stl.SurveyTypeLink_ComplexSurvey = 2
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
					declare
						@age int,
						@EvnPLDispDop13_YearEndDate datetime = cast(cast(:Year as varchar) + '-12-31' as datetime);
		
					select top 1
						@age = dbo.Age2(Person_BirthDay, @EvnPLDispDop13_YearEndDate)
					from
						v_PersonState ps (nolock)
					where
						ps.Person_id = :Person_id
					
					select
						st.SurveyType_Code,
						year(evdd.EvnVizitDispDop_setDate) as Year
					from
						v_EvnPLDisp epld (nolock)
						inner join v_DopDispInfoConsent ddic (nolock) on ddic.EvnPLDisp_id = epld.EvnPLDisp_id
						inner join v_EvnVizitDispDop evdd (nolock) on ddic.DopDispInfoConsent_id = evdd.DopDispInfoConsent_id
						inner join v_SurveyTypeLink stl (nolock) on stl.SurveyTypeLink_id = ddic.SurveyTypeLink_id
						inner join v_SurveyType st (nolock) on st.SurveyType_id = stl.SurveyType_id
					where
						epld.Person_id = :Person_id
						and st.SurveyType_Code in (14,20,21)
						and YEAR(evdd.EvnVizitDispDop_setDate) >= :Year
						and (@age <= 64 or st.SurveyType_Code <> 14)
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
					declare
						@EvnPLDispDop13_YearEndDate datetime = cast(substring(:EvnPLDispDop13_consDate, 1, 4) + '-12-31' as datetime);
		
					select top 1
						dbo.Age2(Person_BirthDay, @EvnPLDispDop13_YearEndDate) as Person_Age
					from
						v_PersonState ps (nolock)
					where
						ps.Person_id = :Person_id
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
				$data['Person_id'] = $this->getFirstResultFromQuery("select Person_id from v_EvnPLDisp (nolock) where EvnPLDisp_id = :EvnPLDisp_id", array('EvnPLDisp_id' => $data['EvnPLDisp_id']));
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
			$filter .= " and EvnPLDisp_id <> ISNULL(:EvnPLDisp_id, 0)";
		}

		$query = "
			select top 1
				EvnPLDisp_id
			from
				v_EvnPLDisp EPLD (nolock)
			where
				ISNULL(DispClass_id,1) IN (1,5)
				and Person_id = :Person_id
				{$filter}
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
					convert(varchar(10), epldd13f.EvnPLDispDop13_consDT, 120) as EvnPLDispDop13_firstConsDate,
					ISNULL(epldd13f.EvnPLDispDop13_IsNewOrder, 1) as EvnPLDispDop13_IsNewOrder
				from
					v_EvnPLDispDop13 epldd13f (nolock)
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
			$filter .= " and (ISNULL(ucat.UslugaCategory_SysNick, '') != 'lpusection' or ((case when LpuLevel.LpuLevel_code in (2,6) then '6' when LpuLevel.LpuLevel_code in (3,5) then '5' when LpuLevel.LpuLevel_code in (1,8) then '8' end) = left(UC.UslugaComplex_Code,1)))";
			$filter .= " and (UC.UslugaComplex_id is not null or Stl.UslugaComplex_id is null)";

			$joinList[] = "left join v_Lpu lpu with (nolock) on lpu.Lpu_id = :Lpu_id";
			$joinList[] = "left join v_LpuLevel LpuLevel with (nolock) on LpuLevel.LpuLevel_id = lpu.LpuLevel_id";
			$joinList[] = "left join v_UslugaComplex UC (nolock) on UC.UslugaComplex_id = STL.UslugaComplex_id";
			$joinList[] = "left join v_UslugaCategory ucat with (nolock) on ucat.UslugaCategory_id = UC.UslugaCategory_id";

			$params['Lpu_id'] = $data['Lpu_id'];
		}

		$noFilterByAgeInFirstTime = "";
		if (in_array($this->getRegionNick(), array('astra','kareliya','perm', 'vologda')) && strtotime($data['EvnPLDispDop13_consDate']) >= strtotime('01.04.2015')) {
			$noFilterByAgeInFirstTime = "or (STL.SurveyTypeLink_IsPrimaryFlow = 2 and not exists (
				select top 1
					EvnPLDispDop13_id
				from
					v_EvnPLDispDop13 EPLD (nolock)
				where
					ISNULL(DispClass_id,1) = 1
					and Person_id = :Person_id
					and EvnPLDispDop13_id <> ISNULL(:EvnPLDispDop13_id, 0)
			))";
		}

		if ($data['DispClass_id'] == 1 && in_array(getRegionNick(), array('pskov', 'khak')) && strtotime($data['EvnPLDispDop13_consDate']) >= strtotime('01.05.2015')) {
			$filter .= " and (STL.SurveyTypeLink_IsPay = 2 or STL.SurveyTypeLink_Period = 2)";
		}

		if ($this->regionNick == 'penza') {
			// фильтруем по SurveyTypeLink_IsWow
			$PersonPrivilegeWOW_id = $this->getFirstResultFromQuery("
				select top 1 PersonPrivilegeWOW_id
				from v_PersonPrivilegeWOW ppw (nolock)
				where Person_id = :Person_id
			", $params);

			if ($data['DispClass_id'] == 1 && !empty($PersonPrivilegeWOW_id)) {
				$filter .= " and STL.SurveyTypeLink_IsWow = 2";
			}
			else {
				$filter .= " and ISNULL(STL.SurveyTypeLink_IsWow, 1) = 1";
			}
		}

		$ageModification = $this->getAgeModification(array(
			'onDate' => $data['EvnPLDispDop13_consDate']
		));

		$selectST = "
			select
				ST.SurveyType_id
				,STL.SurveyTypeLink_IsDel
				,MAX(STL.SurveyTypeLink_id) as SurveyTypeLink_id
			from v_SurveyTypeLink STL (nolock)
				left join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
				" . implode(' ', $joinList) . "
			where 
				STL.SurveyType_id not in (1,48)
				and IsNull(STL.DispClass_id, '1') = '1'
				and (IsNull(STL.Sex_id, @sex_id) = @sex_id)
				and (
					(@age between Isnull(SurveyTypeLink_From, 0) and  Isnull(SurveyTypeLink_To, 999))
					{$noFilterByAgeInFirstTime}
				)
				and (STL.SurveyTypeLink_begDate is null or STL.SurveyTypeLink_begDate <= '2020-01-27')
				and (STL.SurveyTypeLink_endDate is null or STL.SurveyTypeLink_endDate >= '2020-01-27')
				and ISNULL(STL.SurveyTypeLink_ComplexSurvey, 1) = 1
				and ISNULL(STL.SurveyTypeLink_IsEarlier, 1) = 1
				and (STL.SurveyTypeLink_Period is null or STL.SurveyTypeLink_From % STL.SurveyTypeLink_Period = @age % STL.SurveyTypeLink_Period)
				" . $filter . "
		";

		$select = "
			declare @curYear int = YEAR(dbo.tzGetDate());
			with survtypes as (
				{$selectST}
			group by ST.SurveyType_id, STL.SurveyTypeLink_IsDel
			)
			select
				STL.UslugaComplex_id
				,EUDD.EvnUslugaDispDop_id
				,DDIC.DopDispInfoConsent_id
				,ISNULL(DDIC.DopDispInfoConsent_id, -STL.SurveyTypeLink_id) as DopDispInfoConsent_id
				,DDIC.EvnPLDisp_id as EvnPLDispDop13_id
				,STL.SurveyTypeLink_id as SurveyTypeLink_id
				,ISNULL(STL.SurveyTypeLink_IsNeedUsluga, 1) as SurveyTypeLink_IsNeedUsluga
				,ISNULL(STL.SurveyTypeLink_IsDel, 1) as SurveyTypeLink_IsDel
				,ST.SurveyType_Code as SurveyType_Code
				,ST.SurveyType_Name as SurveyType_Name
				,ST.SurveyType_RecNotNeeded as SurveyType_RecNotNeeded
				,ST.SurveyType_IsVizit
				,case WHEN DDIC.DopDispInfoConsent_id is null or DDIC.DopDispInfoConsent_IsAgree = 2 then 1 else 0 end as DopDispInfoConsent_IsAgree
				,case WHEN DDIC.DopDispInfoConsent_IsEarlier = 2 then 1 else 0 end as DopDispInfoConsent_IsEarlier
				,case WHEN ISNULL(SurveyTypeLink_IsImpossible, 1) = 1 then 'hidden' WHEN DDIC.DopDispInfoConsent_IsImpossible = 2 then '1' else '0' end as DopDispInfoConsent_IsImpossible
				,STL.SurveyTypeLink_IsUslPack as SurveyTypeLink_IsUslPack
				,case when (STL.SurveyTypeLink_IsPrimaryFlow = 2 and @age not between Isnull(STL.SurveyTypeLink_From, 0) and  Isnull(STL.SurveyTypeLink_To, 999)) then 0 else 1 end as DopDispInfoConsent_IsAgeCorrect
				,EUDD.*
				,survtypes.*
				,OutUsluga.OutUsluga_id
				,OutUsluga.OutUsluga_Date
				,OutUsluga.OutUsluga_Lpu_Nick
				,OutUsluga.OutUslugaComplex_id
				,OutUsluga.OutMedPersonalFIO
			from
				survtypes
				left join v_SurveyTypeLink STL on STL.SurveyTypeLink_id = survtypes.SurveyTypeLink_id
				left join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
				left join v_DopDispInfoConsent DDIC (nolock) on DDIC.SurveyTypeLink_id = STL.SurveyTypeLink_id
					and DDIC.EvnPLDisp_id = :EvnPLDispDop13_id
				left join v_UslugaComplex UC (nolock) on UC.UslugaComplex_id = STL.UslugaComplex_id
				outer apply (
					select top 1 
						EUDD1.EvnUslugaDispDop_id
						,convert(varchar(10), EUDD1.EvnUslugaDispDop_didDate, 104) as EvnUslugaDispDop_didDate
						,lpu1.Lpu_Nick as EvnUslugaDispDop_Lpu_Nick
						,MSF1.Person_Fio as EvnUslugaDispDop_MedPersonalFio
					from v_EvnUslugaDispDop EUDD1 with (nolock)
						left join v_MedStaffFact MSF1 (nolock) on MSF1.MedStaffFact_id = EUDD1.MedStaffFact_id
						left join v_Lpu lpu1 (nolock) on lpu1.Lpu_id = EUDD1.Lpu_id
					where EUDD1.UslugaComplex_id = UC.UslugaComplex_id
						and EUDD1.EvnUslugaDispDop_rid = :EvnPLDispDop13_id
						and ISNULL(EUDD1.EvnUslugaDispDop_IsVizitCode, 1) = 1
				) EUDD
				outer apply(
					select top 1 
						EU.EvnUsluga_id as OutUsluga_id
						,EU.UslugaComplex_id as OutUslugaComplex_id
						,L.Lpu_Nick as OutUsluga_Lpu_Nick
						,MP.Person_Fio as OutMedPersonalFIO
						,convert(varchar(10), EP.EvnPrescr_didDate, 104) as OutUsluga_Date
					from 
						v_SurveyTypeLink STL2
						inner join v_SurveyType ST2 (nolock) on ST2.SurveyType_id = STL2.SurveyType_id
						inner join v_EvnUsluga EU (nolock) on EU.UslugaComplex_id = STL2.UslugaComplex_id
						inner join v_EvnPrescr EP (nolock) on EP.EvnPrescr_id = EU.EvnPrescr_id
						left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EU.MedPersonal_id
						left join v_Lpu L with(nolock) on L.Lpu_id = EU.Lpu_id
					where 
						STL2.SurveyType_id = STL.SurveyType_id
						and EU.Person_id = :Person_id
						and YEAR(EU.EvnUsluga_setDate) = @ThisYear
						and EP.EvnPrescr_IsExec = 2
						and (ST.SurveyType_IsVizit = 1 or EU.EvnUsluga_pid = :EvnPLDispDop13_id)
					order by EU.EvnUsluga_setDate DESC
				) OutUsluga
			where (ISNULL(STL.SurveyTypeLink_IsDel, 1) = 1 or (EUDD.EvnUslugaDispDop_id is not null and DDIC.DopDispInfoConsent_id IS NOT NULL))
				and ST.SurveyType_Code not in (1,48)
		";
		
		$query = "
			declare
				@age int,
				@originalAge int,
				@EvnPLDispDop13_YearEndDate datetime = cast(substring(:EvnPLDispDop13_consDate, 1, 4) + '-12-31' as datetime),
				@sex_id bigint,
				@ThisYear int = YEAR(dbo.tzGetDate());

			select top 1
				@sex_id = ISNULL(Sex_id, 3),
				@age = dbo.Age2(Person_BirthDay, @EvnPLDispDop13_YearEndDate)
			from v_PersonState ps (nolock)
			where ps.Person_id = :Person_id
			
			set @originalAge = @age;

			{$ageModification}
			
			if (@age = @originalAge)
				set @originalAge = null;

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
					select top 1
						DDIC.DopDispInfoConsent_id,
						DDIC.EvnPLDisp_id as EvnPLDispDop13_id,
						DDIC.SurveyTypeLink_id,
						STL.SurveyTypeLink_IsNeedUsluga as SurveyTypeLink_IsNeedUsluga,
						STL.SurveyTypeLink_IsDel as SurveyTypeLink_IsDel,
						ST.SurveyType_Code,
						'Цитологическое исследование' as SurveyType_Name,
						case WHEN DDIC.DopDispInfoConsent_IsAgree = 2 then 1 else 0 end as DopDispInfoConsent_IsAgree,
						case WHEN DDIC.DopDispInfoConsent_IsEarlier = 2 then 1 else 0 end as DopDispInfoConsent_IsEarlier,
						case WHEN DDIC.DopDispInfoConsent_IsImpossible = 2 then 1 else 0 end as DopDispInfoConsent_IsImpossible,
						STL.SurveyTypeLink_IsUslPack as SurveyTypeLink_IsUslPack,
						1 as DopDispInfoConsent_IsAgeCorrect
					from
						v_DopDispInfoConsent DDIC (nolock)
						inner join v_SurveyTypeLink stl (nolock) on stl.SurveyTypeLink_id = ddic.SurveyTypeLink_id
						inner join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
					where
						DDIC.EvnPLDisp_id = :EvnPLDispDop13_id
						and stl.SurveyTypeLink_ComplexSurvey = 2
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
			from v_EvnPLDispDop13 with (nolock)
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
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_EvnPLDispDop13_del
				@EvnPLDispDop13_id = :EvnPLDispDop13_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, array(
			'EvnPLDispDop13_id' => $data['EvnPLDispDop13_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if ( !is_object($result) ) {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление талона ДД) (' . __LINE__ . ')'));
		}

		$response = $result->result('array');

		if ( !is_array($response) || count($response) == 0 ) {
			return array(array('Error_Msg' => 'Ошибка при удалении талона ДД (' . __LINE__ . ')'));
		}
		else if ( !empty($response[0]['Error_Msg']) ) {
			return $response;
		}

		$attrArray = array(
			'HeredityDiag',
			'ProphConsult',
			'NeedConsult',
			'DopDispInfoConsent'
		);

		foreach ( $attrArray as $attr ) {
			$deleteResult = $this->deleteAttributes($attr, $data['EvnPLDispDop13_id'], $data['pmUser_id']);

			if ( !empty($deleteResult) ) {
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
					[EPLDD].[EvnPLDispDop13_id] as [EvnPLDispDop13_id],
					[EPLDD].[Person_id] as [Person_id],
					[EPLDD].[Server_id] as [Server_id],
					[EPLDD].[PersonEvn_id] as [PersonEvn_id],
					[EPLDD].[EvnPLDispDop13_VizitCount] as [EvnPLDispDop13_VizitCount],
					[IsFinish].[YesNo_Name] as [EvnPLDispDop13_IsFinish],
					convert(varchar(10), [EPLDD].[EvnPLDispDop13_setDate], 104) as [EvnPLDispDop13_setDate],
					convert(varchar(10), [EPLDD].[EvnPLDispDop13_disDate], 104) as [EvnPLDispDop13_disDate]
			from
							v_PersonState PS with (nolock)
						inner join [v_EvnPLDispDop13] [EPLDD] with (nolock) on [PS].[Person_id] = [EPLDD].[Person_id] and [EPLDD].Lpu_id = :Lpu_id
					left join [YesNo] [IsFinish] with (nolock) on [IsFinish].[YesNo_id] = [EPLDD].[EvnPLDispDop13_IsFinish]
			where
				(1 = 1)
				and [EPLDD].Person_id = :Person_id
			order by
						-- order by
						PS.Person_SurName,
						PS.Person_FirName,
						PS.Person_SecName
						-- end order by
		";
		$result = $this->db->query($query, array('Lpu_id' => $data['Lpu_id'], 'Person_id' => $data['Person_id']));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
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
			$accessType .= " and ISNULL(EPLDD13.EvnPLDispDop13_isPaid, 1) = 1";
		}
		if ( $data['session']['region']['nick'] == 'pskov' ) {
			$accessType .= "and ISNULL(EPLDD13.EvnPLDispDop13_isPaid, 1) = 1
				and not exists(
					select top 1 RD.Registry_id
					from r60.v_RegistryData RD with(nolock)
						inner join v_Registry R with(nolock) on R.Registry_id = RD.Registry_id
						inner join v_RegistryStatus RS with(nolock) on RS.RegistryStatus_id = R.RegistryStatus_id
					where
						RD.Evn_id = EPLDD13.EvnPLDispDop13_id
						and RS.RegistryStatus_SysNick not in ('work','paid')
				)
			";
		}
		
		$query = "
			SELECT TOP 1
				case when {$accessType} then 'edit' else 'view' end as accessType,
				EPLDD13.EvnPLDispDop13_id,
				ISNULL(EPLDD13.EvnPLDispDop13_IsPaid, 1) as EvnPLDispDop13_IsPaid,
				COALESCE(epldd13f.EvnPLDispDop13_IsNewOrder, EPLDD13.EvnPLDispDop13_IsNewOrder, 1) as EvnPLDispDop13_IsNewOrder,
				ISNULL(EPLDD13.EvnPLDispDop13_IndexRep, 0) as EvnPLDispDop13_IndexRep,
				ISNULL(EPLDD13.EvnPLDispDop13_IndexRepInReg, 1) as EvnPLDispDop13_IndexRepInReg,
				EPLDD13.EvnPLDispDop13_fid,
				EPLDD13.Person_id,
				EPLDD13.PersonEvn_id,
				ISNULL(EPLDD13.DispClass_id, 1) as DispClass_id,
				EPLDD13.PayType_id,
				EPLDD13.EvnPLDispDop13_pid,
				convert(varchar(10), EPLDD13.EvnPLDispDop13_setDate, 104) as EvnPLDispDop13_setDate,
				convert(varchar(10), EPLDD13.EvnPLDispDop13_disDate, 104) as EvnPLDispDop13_disDate,
				convert(varchar(10), EPLDD13.EvnPLDispDop13_consDT, 104) as EvnPLDispDop13_consDate,
				EPLDD13.Server_id,
				case when EPLDD13.EvnPLDispDop13_IsMobile = 2 then 1 else 0 end as EvnPLDispDop13_IsMobile,
				case when EPLDD13.EvnPLDispDop13_IsOutLpu = 2 then 1 else 0 end as EvnPLDispDop13_IsOutLpu,
				EPLDD13.Lpu_mid,
				EPLDD13.EvnPLDispDop13_IsStenocard,
				EPLDD13.EvnPLDispDop13_IsShortCons,
				EPLDD13.EvnPLDispDop13_IsBrain,
				EPLDD13.EvnPLDispDop13_IsDoubleScan,
				EPLDD13.EvnPLDispDop13_IsTub,
				EPLDD13.EvnPLDispDop13_IsTIA,
				EPLDD13.EvnPLDispDop13_IsRespiratory,
				EPLDD13.EvnPLDispDop13_IsLungs,
				EPLDD13.EvnPLDispDop13_IsTopGastro,
				EPLDD13.EvnPLDispDop13_IsBotGastro,
				EPLDD13.EvnPLDispDop13_IsSpirometry,
				EPLDD13.EvnPLDispDop13_IsHeartFailure,
				EPLDD13.EvnPLDispDop13_IsOncology,
				EPLDD13.EvnPLDispDop13_IsEsophag,
				EPLDD13.EvnPLDispDop13_IsSmoking,
				EPLDD13.EvnPLDispDop13_IsRiskAlco,
				EPLDD13.EvnPLDispDop13_IsAlcoDepend,
				EPLDD13.EvnPLDispDop13_IsLowActiv,
				EPLDD13.EvnPLDispDop13_IsIrrational,
				EPLDD13.EvnPLDispDop13_IsUseNarko,
				EPLDD13.Diag_id,
				EPLDD13.Diag_sid,
				EPLDD13.EvnPLDispDop13_IsDisp,
				EPLDD13.NeedDopCure_id,
				EPLDD13.EvnPLDispDop13_IsStac,
				EPLDD13.EvnPLDispDop13_IsSanator,
				EPLDD13.EvnPLDispDop13_SumRick,
				EPLDD13.RiskType_id,
				EPLDD13.EvnPLDispDop13_IsSchool,
				EPLDD13.EvnPLDispDop13_IsProphCons,
				EPLDD13.EvnPLDispDop13_IsHypoten,
				EPLDD13.EvnPLDispDop13_IsLipid,
				EPLDD13.EvnPLDispDop13_IsHypoglyc,
				EPLDD13.HealthKind_id,
				ISNULL(EPLDD13.EvnPLDispDop13_IsEndStage, 1) as EvnPLDispDop13_IsEndStage,
				EPLDD13.EvnPLDispDop13_IsTwoStage,
				EPLDD13.CardioRiskType_id,
				EvnPLDispDop13Sec.EvnPLDispDop13_id as EvnPLDispDop13Sec_id,
				convert(varchar(10), ecp.EvnCostPrint_setDT, 104) as EvnCostPrint_setDT,
				ecp.EvnCostPrint_Number,
				ecp.EvnCostPrint_IsNoPrint,
				EPLDD13.EvnPLDispDop13_IsSuspectZNO,
				EPLDD13.Diag_spid,
				convert(varchar(10), epldd13f.EvnPLDispDop13_consDT, 104) as EvnPLDispDop13_firstConsDate,
				PD.PersonDisp_id
			FROM
				v_EvnPLDispDop13 EPLDD13 (nolock)
				left join v_EvnCostPrint ecp (nolock) on ecp.Evn_id = EPLDD13.EvnPLDispDop13_id
				left join v_EvnPLDispDop13 epldd13f (nolock) on epldd13f.EvnPLDispDop13_id = EPLDD13.EvnPLDispDop13_fid 
				outer apply(
					select top 1
						EvnPLDispDop13_id
					from v_EvnPLDispDop13 with (nolock)
					where
						EvnPLDispDop13_fid = EPLDD13.EvnPLDispDop13_id
				) EvnPLDispDop13Sec
				outer apply(
					select top 1 PD.PersonDisp_id
					from v_PersonDisp PD
						left join v_Diag D on D.Diag_id = PD.Diag_id
					where PD.Person_id = EPLDD13.Person_id and PD.DispOutType_id is null 
						and Diag_Code not in ('Z32.1', 'Z34', 'Z34.0', 'Z34.8', 'Z343.9',
						'Z35','Z35.0','Z35.1','Z35.2','Z35.3','Z35.4','Z35.5','Z35.6','Z35.7','Z35.8','Z35.9')
				) PD
			WHERE
				(1 = 1)
				and EPLDD13.EvnPLDispDop13_id = :EvnPLDispDop13_id
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
						RVT.RateValueType_SysNick,
						CASE RVT.RateValueType_SysNick
							WHEN 'int' THEN cast(R.Rate_ValueInt as varchar)
							WHEN 'float' THEN cast(cast(R.Rate_ValueFloat as decimal(16,3)) as varchar)
							WHEN 'string' THEN R.Rate_ValueStr
							WHEN 'template' THEN R.Rate_ValueStr
							WHEN 'reference' THEN cast(R.Rate_ValuesIs as varchar)
						END as value
					from v_DopDispInfoConsent DDIC (nolock)
						left join v_SurveyTypeLink STL (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
						left join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
						left join v_UslugaComplex UC (nolock) on UC.UslugaComplex_id = STL.UslugaComplex_id
						outer apply(
							select top 1
								EUDD.EvnUslugaDispDop_id
							from v_EvnUslugaDispDop EUDD (nolock)
								inner join v_EvnVizitDispDop EVDD (nolock) on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
							where
								EVDD.EvnVizitDispDop_pid = :EvnPLDispDop13_id and EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
								and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
						) EUDDData
						left join v_EvnUslugaRate eur (nolock) on eur.EvnUsluga_id = EUDDData.EvnUslugaDispDop_id
						left join v_Rate r (nolock) on r.Rate_id = eur.Rate_id 
						left join v_RateType rt (nolock) on rt.RateType_id = r.RateType_id
						left join RateValueType rvt (nolock) on rvt.RateValueType_id = rt.RateValueType_id
					where DDIC.EvnPLDisp_id = :EvnPLDispDop13_id and (DDIC.DopDispInfoConsent_IsAgree = 2 OR DDIC.DopDispInfoConsent_IsEarlier = 2) and ST.SurveyType_Code NOT IN (1,48)
						and (ST.SurveyType_Code IN (6,12) OR RT.RateType_SysNick <> 'glucose')
				";
				//echo getDebugSQL($query, array( 'EvnPLDispDop13_id' => $resp[0]['EvnPLDispDop13_fid'])); exit();
				$result = $this->db->query($query, array(
					'EvnPLDispDop13_id' => $resp[0]['EvnPLDispDop13_fid']
				));
				if ( is_object($result) ) {
					$results = $result->result('array');
					foreach($results as $oneresult) {
						if ($oneresult['RateValueType_SysNick'] == 'float') {
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
			if (!empty($resp[0]['EvnPLDispDop13_id'])) {
				$query = "
					select 
						RT.RateType_SysNick as nick,
						RVT.RateValueType_SysNick,
						CASE RVT.RateValueType_SysNick
							WHEN 'int' THEN cast(R.Rate_ValueInt as varchar)
							WHEN 'float' THEN cast(cast(R.Rate_ValueFloat as decimal(16,3)) as varchar)
							WHEN 'string' THEN R.Rate_ValueStr
							WHEN 'template' THEN R.Rate_ValueStr
							WHEN 'reference' THEN cast(R.Rate_ValuesIs as varchar)
						END as value
					from v_DopDispInfoConsent DDIC (nolock)
						left join v_SurveyTypeLink STL (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
						left join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
						left join v_UslugaComplex UC (nolock) on UC.UslugaComplex_id = STL.UslugaComplex_id
						outer apply(
							select top 1
								EUDD.EvnUslugaDispDop_id
							from v_EvnUslugaDispDop EUDD (nolock)
								inner join v_EvnVizitDispDop EVDD (nolock) on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
							where
								EVDD.EvnVizitDispDop_pid = :EvnPLDispDop13_id and EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
								and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
						) EUDDData
						left join v_EvnUslugaRate eur (nolock) on eur.EvnUsluga_id = EUDDData.EvnUslugaDispDop_id
						left join v_Rate r (nolock) on r.Rate_id = eur.Rate_id 
						left join v_RateType rt (nolock) on rt.RateType_id = r.RateType_id
						left join RateValueType rvt (nolock) on rvt.RateValueType_id = rt.RateValueType_id
					where DDIC.EvnPLDisp_id = :EvnPLDispDop13_id and (DDIC.DopDispInfoConsent_IsAgree = 2 OR DDIC.DopDispInfoConsent_IsEarlier = 2) and ST.SurveyType_Code NOT IN (1,48)
						and (ST.SurveyType_Code IN (6,12) OR RT.RateType_SysNick <> 'glucose')
				";
				//echo getDebugSQL($query, array( 'EvnPLDispDop13_id' => $resp[0]['EvnPLDispDop13_id'])); exit();
				$result = $this->db->query($query, array(
					'EvnPLDispDop13_id' => $resp[0]['EvnPLDispDop13_id']
				));
				if ( is_object($result) ) {
					$results = $result->result('array');
					foreach($results as $oneresult) {
						if ($oneresult['RateValueType_SysNick'] == 'float') {
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

			if (getRegionNick() == 'buryatiya') {
				// нужно получить значения результатов с услуги анкетирования из EvnUslugaRate
				if (!empty($resp[0]['EvnPLDispDop13_id'])) {
					$query = "
						select
							RT.RateType_SysNick as nick,
							RVT.RateValueType_SysNick,
							CASE RVT.RateValueType_SysNick
								WHEN 'int' THEN cast(R.Rate_ValueInt as varchar)
								WHEN 'float' THEN cast(cast(R.Rate_ValueFloat as decimal(16,3)) as varchar)
								WHEN 'string' THEN R.Rate_ValueStr
								WHEN 'template' THEN R.Rate_ValueStr
								WHEN 'reference' THEN cast(R.Rate_ValuesIs as varchar)
							END as value
						from
						 	v_EvnUslugaDispDop EUDD with (nolock)
							inner join v_SurveyTypeLink STL with (nolock) on STL.UslugaComplex_id = EUDD.UslugaComplex_id
							inner join v_SurveyType ST with (nolock) on ST.SurveyType_id = STL.SurveyType_id and ST.SurveyType_Code = 2
							left join v_EvnUslugaRate eur (nolock) on eur.EvnUsluga_id = EUDD.EvnUslugaDispDop_id
							left join v_Rate r (nolock) on r.Rate_id = eur.Rate_id
							left join v_RateType rt (nolock) on rt.RateType_id = r.RateType_id
							left join RateValueType rvt (nolock) on rvt.RateValueType_id = rt.RateValueType_id
						where
							EUDD.EvnUslugaDispDop_pid = :EvnPLDispDop13_id
					";
					//echo getDebugSQL($query, array( 'EvnPLDispDop13_id' => $resp[0]['EvnPLDispDop13_id'])); exit();
					$result = $this->db->query($query, array(
						'EvnPLDispDop13_id' => $resp[0]['EvnPLDispDop13_id']
					));
					if ( is_object($result) ) {
						$results = $result->result('array');
						foreach($results as $oneresult) {
							if ($oneresult['RateValueType_SysNick'] == 'float') {
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
			}

			// нужно получить диагноз посещения терапевта для добавления специфики
			if (!empty($data['isExt6']) && !empty($resp[0]['EvnPLDispDop13_id'])) {
				$params = array( 'EvnPLDispDop13_id' => $resp[0]['EvnPLDispDop13_id']);
				
				
				$query = "
					select top 1
						DDIC.DopDispInfoConsent_id,
						STL.SurveyTypeLink_id,
						ST.SurveyType_Name,
						ST.SurveyType_Code,
						ISNULL(ST.SurveyType_IsVizit, 1) as SurveyType_IsVizit,
						EUDDData.EvnUslugaDispDop_id,
						convert(varchar(20), EUDDData.EvnUslugaDispDop_setDate + EUDDData.EvnUslugaDispDop_setTime, 113) as EvnUslugaDispDop_setDate,
						convert(varchar(10), EUDDData.EvnUslugaDispDop_didDate, 104) as EvnUslugaDispDop_didDate,
						EUDDData.Diag_id,
						EUDDData.Diag_Code,
						EUDDData.OnkoDiag_Code
					from v_DopDispInfoConsent DDIC(nolock)
						 left join v_SurveyTypeLink STL(nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
						 left join v_SurveyType ST(nolock) on ST.SurveyType_id = STL.SurveyType_id
						 outer apply(
							select top 1
								EUDD.EvnUslugaDispDop_id,
								EUDD.EvnUslugaDispDop_setDate,
								EUDD.EvnUslugaDispDop_setTime,
								EUDD.EvnUslugaDispDop_didDate,
								d.Diag_id,
								d.Diag_Code,
								onkodiag.Diag_Code as OnkoDiag_Code
							from v_EvnUslugaDispDop EUDD(nolock)
								left join v_EvnVizitDispDop EVDD(nolock) on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
								left join v_Diag d with (nolock) on d.Diag_id = EVDD.Diag_id
								left join MorbusOnkoVizitPLDop movpld(nolock) on movpld.EvnVizit_id = EVDD.EvnVizitDispDop_id
								left join v_Diag onkodiag (nolock) on onkodiag.Diag_id = movpld.Diag_id
							where
								EVDD.EvnVizitDispDop_pid = DDIC.EvnPLDisp_id
								and EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
								and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
						) EUDDData
					where
						  DDIC.EvnPLDisp_id = :EvnPLDispDop13_id
						  and (DDIC.DopDispInfoConsent_IsAgree = 2 or DDIC.DopDispInfoConsent_IsEarlier = 2)
						  and ST.SurveyType_Code = 19
						  and ISNULL(STL.SurveyTypeLink_ComplexSurvey, 1) = 1
						  and (ISNULL(STL.SurveyTypeLink_IsDel, 1) = 1 or EUDDData.EvnUslugaDispDop_id is not null) -- только если сохранено согласие
				";
				//echo getDebugSQL($query, $params); exit();
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
			SELECT TOP 1
				rtrim(lp.Lpu_Name) as Lpu_Name,
				rtrim(isnull(lp1.Lpu_Name, '')) as Lpu_AName,
				rtrim(isnull(addr1.Address_Address, '')) as Lpu_AAddress,
				rtrim(lp.Lpu_OGRN) as Lpu_OGRN,
				isnull(pc.PersonCard_Code, '') as PersonCard_Code,
				ps.Person_SurName + ' ' + ps.Person_FirName + ' ' + isnull(ps.Person_SecName, '') as Person_FIO,
				sx.Sex_Name,
				isnull(osmo.OrgSMO_Nick, '') as OrgSMO_Nick,
				isnull(case when pls.PolisType_id = 4 then '' else pls.Polis_Ser end, '') as Polis_Ser,
				isnull(case when pls.PolisType_id = 4 then ps.Person_EdNum else pls.Polis_Num end, '') as Polis_Num,
				isnull(osmo.OrgSMO_Name, '') as OrgSMO_Name,
				convert(varchar(10), ps.Person_BirthDay, 104) as Person_BirthDay,
				isnull(addr.Address_Address, '') as Person_Address,
				jborg.Org_Nick,
				case when EPLDD.EvnPLDispDop13_IsBud = 2 then 'Да' else 'Нет' end as EvnPLDispDop13_IsBud,
				atype.AttachType_Name,
				convert(varchar(10),  EPLDD.EvnPLDispDop13_disDate, 104) as EvnPLDispDop13_disDate
			FROM
				v_EvnPLDispDop13 EPLDD (nolock)
				inner join v_Lpu lp (nolock) on lp.Lpu_id = EPLDD.Lpu_id
				left join v_Lpu lp1 (nolock) on lp1.Lpu_id = EPLDD.Lpu_aid
				left join Address addr1 (nolock) on addr1.Address_id = lp1.UAddress_id
				left join v_PersonCard pc (nolock) on pc.Person_id = EPLDD.Person_id and pc.LpuAttachType_id = 1
				inner join v_PersonState ps (nolock) on ps.Person_id = EPLDD.Person_id
				inner join Sex sx (nolock) on sx.Sex_id = ps.Sex_id
				left join Polis pls (nolock) on pls.Polis_id = ps.Polis_id
				left join v_OrgSmo osmo (nolock) on osmo.OrgSmo_id = pls.OrgSmo_id
				left join Address addr (nolock) on addr.Address_id = ps.PAddress_id
				left join Job jb (nolock) on jb.Job_id = ps.Job_id
				left join Org jborg (nolock) on jborg.Org_id = jb.Org_id
				left join AttachType atype (nolock) on atype.AttachType_id = EPLDD.AttachType_id
			WHERE
				(1 = 1)
				and EPLDD.EvnPLDispDop13_id = ?
				and EPLDD.Lpu_id = ?
		";
        $result = $this->db->query($query, array($data['EvnPLDispDop13_id'], $data['Lpu_id']));

        if (is_object($result))
        {
            return $result->result('array');
        }
 	    else
 	    {
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
				EVZDD.EvnVizitDispDop_id,
				convert(varchar(10), EVZDD.EvnVizitDispDop_setDate, 104) as EvnVizitDispDop_setDate,
				RTRIM(LS.LpuSection_Name) as LpuSection_Name,
				RTRIM(MP.Person_Fio) as MedPersonal_Fio,
				RTRIM(DDS.DopDispSpec_Name) as DopDispSpec_Name,
				RTRIM(D.Diag_Code) as Diag_Code,
				EVZDD.MedPersonal_id,
				EVZDD.DopDispSpec_id,
				EVZDD.LpuSection_id,
				EVZDD.Diag_id,
				EVZDD.DopDispDiagType_id,
				EVZDD.DeseaseStage_id,
				EVZDD.HealthKind_id,
				EVZDD.EvnVizitDispDop_IsSanKur,
				EVZDD.EvnVizitDispDop_IsOut,				
				EVZDD.DopDispAlien_id,
				EVZDD.EvnVizitDispDop_Recommendations,
				1 as Record_Status
			from v_EvnVizitDispDop EVZDD (nolock)
				left join LpuSection LS (nolock) on LS.LpuSection_id = EVZDD.LpuSection_id
				left join v_MedPersonal MP (nolock) on MP.MedPersonal_id = EVZDD.MedPersonal_id
				left join DopDispSpec DDS (nolock) on DDS.DopDispSpec_id = EVZDD.DopDispSpec_id
				left join Diag D (nolock) on D.Diag_id = EVZDD.Diag_id
			where EVZDD.EvnVizitDispDop_pid = ?
		";
		$result = $this->db->query($query, array($data['EvnPLDispDop13_id']));

        if (is_object($result))
        {
            return $result->result('array');
        }
        else
        {
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
			select top 1
				EVZDD.EvnVizitDispDop_id,
				convert(varchar(10), EVZDD.EvnVizitDispDop_setDate, 104) as EvnVizitDispDop_setDate,
				RTRIM(LS.LpuSection_Name) as LpuSection_Name,
				RTRIM(MP.Person_Fio) as MedPersonal_Fio,
				RTRIM(DDS.DopDispSpec_Name) as DopDispSpec_Name,
				RTRIM(D.Diag_Code) as Diag_Code,
				EVZDD.MedPersonal_id,
				EVZDD.DopDispSpec_id,
				EVZDD.LpuSection_id,
				EVZDD.Diag_id,
				EVZDD.DopDispDiagType_id,
				EVZDD.DeseaseStage_id,
				EVZDD.HealthKind_id,
				EVZDD.EvnVizitDispDop_IsSanKur,
				EVZDD.EvnVizitDispDop_IsOut,				
				EVZDD.DopDispAlien_id,
				EVZDD.EvnVizitDispDop_Recommendations,
				1 as RecordStatus,
				case when EVZDD.Lpu_id = :Lpu_id " . (count($med_personal_list)>0 ? "and EVZDD.MedPersonal_id in (".implode(',',$med_personal_list).")" : "") . " then 'edit' else 'view' end as accessType
			from v_EvnVizitDispDop EVZDD (nolock)
				left join LpuSection LS (nolock) on LS.LpuSection_id = EVZDD.LpuSection_id
				left join v_MedPersonal MP (nolock) on MP.MedPersonal_id = EVZDD.MedPersonal_id and MP.Lpu_id = EVZDD.Lpu_id
				left join DopDispSpec DDS (nolock) on DDS.DopDispSpec_id = EVZDD.DopDispSpec_id
				left join Diag D (nolock) on D.Diag_id = EVZDD.Diag_id
			where EVZDD.EvnVizitDispDop_id = :EvnVizitDispDop_id
		";
		$result = $this->db->query($query, array('EvnVizitDispDop_id' => $data['EvnVizitDispDop_id'], 'Lpu_id' => $data['Lpu_id']));

        if (is_object($result))
        {
            return $result->result('array');
        }
        else
        {
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
				EVZDD.EvnVizitDispDop_id,
				convert(varchar(10), EVZDD.EvnVizitDispDop_setDate, 104) as EvnVizitDispDop_setDate,
				RTRIM(MP.Person_Fio) as MedPersonal_Fio,
				RTRIM(isnull(MP.MedPersonal_TabCode, '')) as MedPersonal_TabCode,
				RTRIM(DDS.DopDispSpec_Name) as DopDispSpec_Name,
				RTRIM(D.Diag_Code) as Diag_Code,
				EVZDD.MedPersonal_id,
				EVZDD.DopDispSpec_id,
				EVZDD.LpuSection_id,
				EVZDD.Diag_id,
				EVZDD.DopDispDiagType_id,
				EVZDD.DeseaseStage_id,
				EVZDD.HealthKind_id,
				EVZDD.EvnVizitDispDop_IsSanKur,
				EVZDD.EvnVizitDispDop_IsOut,				
				EVZDD.DopDispAlien_id,
				EVZDD.EvnVizitDispDop_Recommendations,
				1 as Record_Status
			from v_EvnVizitDispDop EVZDD (nolock)
				left join LpuSection LS (nolock) on LS.LpuSection_id = EVZDD.LpuSection_id
				left join v_MedPersonal MP (nolock) on MP.MedPersonal_id = EVZDD.MedPersonal_id
				left join DopDispSpec DDS (nolock) on DDS.DopDispSpec_id = EVZDD.DopDispSpec_id
				left join Diag D (nolock) on D.Diag_id = EVZDD.Diag_id
			where EVZDD.EvnVizitDispDop_pid = ?
		";
		$result = $this->db->query($query, array($data['EvnPLDispDop13_id']));

        if (is_object($result))
        {
            return $result->result('array');
        }
        else
        {
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
				EUDD.EvnUslugaDispDop_id,
				ST.SurveyType_Name,
				case WHEN DDIC.DopDispInfoConsent_IsEarlier = 2 then 'true' else 'false' end as DopDispInfoConsent_IsEarlier,
				convert(varchar(10), EUDD.EvnUslugaDispDop_didDate, 104) as EvnUslugaDispDop_didDate,
				L.Lpu_Nick,
				MP.Person_Fio as MedPersonal_Fio
			from
				v_EvnUslugaDispDop EUDD (nolock)
				inner join v_EvnVizitDispDop EVDD (nolock) on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
				inner join v_DopDispInfoConsent DDIC (nolock) on DDIC.DopDispInfoConsent_id = EVDD.DopDispInfoConsent_id
				inner join v_SurveyTypeLink STL (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
				inner join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
				left join v_Lpu L (nolock) on L.Lpu_id = ISNULL(EUDD.Lpu_uid, EUDD.Lpu_id)
				left join v_MedStaffFact MP (nolock) on MP.MedStaffFact_id = EUDD.MedStaffFact_id
			where
				EVDD.EvnVizitDispDop_pid = :EvnPLDispDop13_id
				and ST.SurveyType_Code NOT IN (2,3,4,5,6,7,8,9,14,16,17,19,21,31,96,97)
		";
		//echo getDebugSql($query, array('EvnPLDispDop13_id' => $data['EvnPLDispDop13_id']));
		$result = $this->db->query($query, array('EvnPLDispDop13_id' => $data['EvnPLDispDop13_id']));

		if (is_object($result))
		{
			return $result->result('array');
		}
		else
		{
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
				EUDD.EvnUslugaDispDop_id,
				ST.SurveyType_Name,
				case WHEN DDIC.DopDispInfoConsent_IsEarlier = 2 then 'true' else 'false' end as DopDispInfoConsent_IsEarlier,
				convert(varchar(10), EUDD.EvnUslugaDispDop_didDate, 104) as EvnUslugaDispDop_didDate,
				L.Lpu_Nick,
				MP.Person_Fio as MedPersonal_Fio
			from
				v_EvnUslugaDispDop EUDD (nolock)
				inner join v_EvnVizitDispDop EVDD (nolock) on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
				inner join v_DopDispInfoConsent DDIC (nolock) on DDIC.DopDispInfoConsent_id = EVDD.DopDispInfoConsent_id
				inner join v_SurveyTypeLink STL (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
				inner join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
				left join v_Lpu L (nolock) on L.Lpu_id = ISNULL(EUDD.Lpu_uid, EUDD.Lpu_id)
				left join v_MedStaffFact MP (nolock) on MP.MedStaffFact_id = EUDD.MedStaffFact_id
			where
				EVDD.EvnVizitDispDop_pid = :EvnPLDispDop13_id
				and ST.SurveyType_Code IN (3,4,5,6,7,8,9,14,16,17,19,21,31,96,97)

			union all

			select
				EUDD.EvnUslugaDispDop_id,
				ST.SurveyType_Name,
				case WHEN DDIC.DopDispInfoConsent_IsEarlier = 2 then 'true' else 'false' end as DopDispInfoConsent_IsEarlier,
				convert(varchar(10), EUDD.EvnUslugaDispDop_didDate, 104) as EvnUslugaDispDop_didDate,
				L.Lpu_Nick,
				MP.Person_Fio as MedPersonal_Fio
			from
				v_EvnUslugaDispDop EUDD (nolock)
				inner join v_DopDispInfoConsent DDIC (nolock) on DDIC.EvnPLDisp_id = EUDD.EvnUslugaDispDop_pid
				inner join v_SurveyTypeLink STL (nolock) on STL.SurveyType_id = (2) and STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
				inner join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
				left join v_Lpu L (nolock) on L.Lpu_id = ISNULL(EUDD.Lpu_uid, EUDD.Lpu_id)
				left join v_MedStaffFact MP (nolock) on MP.MedStaffFact_id = EUDD.MedStaffFact_id
			where
				EUDD.EvnUslugaDispDop_pid = :EvnPLDispDop13_id
		";
		//echo getDebugSql($query, array('EvnPLDispDop13_id' => $data['EvnPLDispDop13_id']));
		$result = $this->db->query($query, array('EvnPLDispDop13_id' => $data['EvnPLDispDop13_id']));

		if (is_object($result))
		{
			return $result->result('array');
		}
		else
		{
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

		/**
		 * Выполняется при сохранении карты.
		 * 
		 * @see saveEvnPLDispDop13()
		 * @see loadDopDispInfoConsent()
		 */ 
		if (!empty($data['isDopUsl'])) {
			// считаем услугу цитологическое исследование
			$queryunion .= "
				union

				select top 1
					DDIC.DopDispInfoConsent_id,
					STL.SurveyTypeLink_id,
					ST.SurveyType_Name,
					ST.SurveyType_Code,
					ISNULL(ST.SurveyType_IsVizit, 1) as SurveyType_IsVizit,
					EUDDData.UslugaComplex_Code,
					EUDDData.EvnUslugaDispDop_id,
					EUDDData.EvnUslugaDispDop_ExamPlace,
					convert(varchar(20), EUDDData.EvnUslugaDispDop_setDate + EUDDData.EvnUslugaDispDop_setTime, 113) as EvnUslugaDispDop_setDate,
					convert(varchar(10), EUDDData.EvnUslugaDispDop_didDate, 104) as EvnUslugaDispDop_didDate,
					null as Diag_Code,
					STL.SurveyTypeLink_IsUslPack,
					ISNULL(DDIC.DopDispInfoConsent_IsEarlier, 1) as DopDispInfoConsent_IsEarlier,
					case when el.Evn_lid is not null then 'true' else 'false' end as EvnUslugaDispDop_WithDirection,
					ST.OrpDispSpec_id
					/*
					ep.EvnPrescr_id,
					isnull(ep.EvnPrescr_pid, ed.EvnDirection_pid) as EvnPrescr_pid,
					ep.PrescriptionType_id,
					isnull(ep.EvnDirection_id, ed.EvnDirection_id) as EvnDirection_id
					*/
				from v_DopDispInfoConsent DDIC (nolock)
					inner join v_SurveyTypeLink STL (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
					inner join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
					/*
					outer apply (
						Select top 1 * from v_EvnDirection ed (nolock) 
							where 
								ed.DopDispInfoConsent_id = ddic.DopDispInfoConsent_id 
								-- Статус направления: не Отменено [12], Отклонено [13].
								and ed.EvnStatus_id not in (12,13)
					) ed
					outer apply (
						select top 1
							ep.EvnPrescr_id,
							Evn.Evn_pid as EvnPrescr_pid,
							ep.PrescriptionType_id,
							ed2.EvnDirection_id
						from
							EvnPrescr ep (nolock)
							inner join Evn (nolock) on Evn.Evn_id = ep.EvnPrescr_id and Evn.Evn_deleted = 1
							outer apply(
								Select top 1 ed2.EvnDirection_id from v_EvnPrescrDirection epd (nolock) 
								inner join v_EvnDirection_all ed2 (nolock) 
									on 
										ed2.EvnDirection_id = epd.EvnDirection_id 
										-- Статус направления: не Отменено [12], Отклонено [13].
										and ed2.EvnStatus_id not in (12,13)
								where epd.EvnPrescr_id = ep.EvnPrescr_id
							) ed2
						where
							ep.DopDispInfoConsent_id = ddic.DopDispInfoConsent_id
					) ep
					*/
					outer apply(
						select top 1
							EUDD.EvnUslugaDispDop_id,
							EUDD.EvnUslugaDispDop_setDate,
							EUDD.EvnUslugaDispDop_setTime,
							EUDD.EvnUslugaDispDop_didDate,
							EUDD.EvnUslugaDispDop_ExamPlace,
							UC.UslugaComplex_Code
						from
							v_EvnUslugaDispDop EUDD (nolock)
							left join v_UslugaComplex UC (nolock) on UC.UslugaComplex_id = EUDD.UslugaComplex_id
						where
							EUDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
							-- Оказание услуги по дополнительной диспансеризации: Нет
							and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
					) EUDDData
					left join v_EvnLink el (nolock) on el.Evn_id = EUDDData.EvnUslugaDispDop_id
				where DDIC.EvnPLDisp_id = :EvnPLDispDop13_id
					-- Комплексное обследование: Да.
					and STL.SurveyTypeLink_ComplexSurvey = 2 and EUDDData.EvnUslugaDispDop_id is not null
			";
		}

		// Тип услуг "Опрос (анкетирование)".
		$query = "
			select
				DDIC.DopDispInfoConsent_id,
				STL.SurveyTypeLink_id,
				ST.SurveyType_Name,
				ST.SurveyType_Code,
				ISNULL(ST.SurveyType_IsVizit, 1) as SurveyType_IsVizit,
				EUDDData.UslugaComplex_Code,
				EUDDData.EvnUslugaDispDop_id,
				EUDDData.EvnUslugaDispDop_ExamPlace,
				convert(varchar(20), EUDDData.EvnUslugaDispDop_setDate + EUDDData.EvnUslugaDispDop_setTime, 113) as EvnUslugaDispDop_setDate,
				convert(varchar(10), EUDDData.EvnUslugaDispDop_didDate, 104) as EvnUslugaDispDop_didDate,
				null as Diag_Code,
				STL.SurveyTypeLink_IsUslPack,
				ISNULL(DDIC.DopDispInfoConsent_IsEarlier, 1) as DopDispInfoConsent_IsEarlier,
				case when el.Evn_lid is not null then 'true' else 'false' end as EvnUslugaDispDop_WithDirection,
				ST.OrpDispSpec_id
			    /*
			    ep.EvnPrescr_id,
				isnull(ep.EvnPrescr_pid, ed.EvnDirection_pid) as EvnPrescr_pid,
				ep.PrescriptionType_id,
				isnull(ep.EvnDirection_id, ed.EvnDirection_id) as EvnDirection_id
				*/
			from v_DopDispInfoConsent DDIC (nolock)
				left join v_SurveyTypeLink STL (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
				left join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
				/*
				outer apply (
					Select top 1 * from v_EvnDirection ed (nolock) 
					where 
						ed.DopDispInfoConsent_id = ddic.DopDispInfoConsent_id 
						-- Статус направления: все кроме Отменено [12], Отклонено [13].
						and ed.EvnStatus_id not in (12,13)
				) ed
				outer apply (
					select top 1
						ep.EvnPrescr_id,
						Evn.Evn_pid as EvnPrescr_pid,
						ep.PrescriptionType_id,
						ed2.EvnDirection_id
					from
						EvnPrescr ep (nolock)
						inner join Evn (nolock) on Evn.Evn_id = ep.EvnPrescr_id and Evn.Evn_deleted = 1
						outer apply(
							Select top 1 ed2.EvnDirection_id from v_EvnPrescrDirection epd (nolock) 
							inner join v_EvnDirection_all ed2 (nolock) 
								on 
									ed2.EvnDirection_id = epd.EvnDirection_id 
									-- Статус направления: все кроме Отменено [12], Отклонено [13].
									and ed2.EvnStatus_id not in (12,13)
							where epd.EvnPrescr_id = ep.EvnPrescr_id
						) ed2
					where
						ep.DopDispInfoConsent_id = ddic.DopDispInfoConsent_id
				) ep
				*/
				outer apply(
					select top 1
						EUDD.EvnUslugaDispDop_id,
						EUDD.EvnUslugaDispDop_setDate,
						EUDD.EvnUslugaDispDop_setTime,
						EUDD.EvnUslugaDispDop_didDate,
						EUDD.EvnUslugaDispDop_ExamPlace,
						UC.UslugaComplex_Code
					from
						v_EvnUslugaDispDop EUDD (nolock)
						left join v_UslugaComplex UC (nolock) on UC.UslugaComplex_id = EUDD.UslugaComplex_id
					where
						EUDD.EvnUslugaDispDop_pid = :EvnPLDispDop13_id
						-- Оказание услуги по дополнительной диспансеризации: Нет.
						and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
				) EUDDData
				left join v_EvnLink el (nolock) on el.Evn_id = EUDDData.EvnUslugaDispDop_id
			where DDIC.EvnPLDisp_id = :EvnPLDispDop13_id
				-- Типы услуг: Опрос (анкетирование) [2].
				and ST.SurveyType_Code = 2 
				--  Опрос по услугам (Осмотр, исследование)
				and (
					--  Пройдено ранее: Да.
					DDIC.DopDispInfoConsent_IsEarlier = 2
					--  Согласие гражданина: Да.
					OR DDIC.DopDispInfoConsent_IsAgree = 2 
				)
				-- Запись удалена: Нет.
				and (ISNULL(STL.SurveyTypeLink_IsDel, 1) = 1 or EUDDData.EvnUslugaDispDop_id is not null ) -- только если сохранено согласие
		";
		
		// Остальные типы услуг (кроме "Первый этап диспансеризации", "Опрос (анкетирование)", "Второй этап диспансеризации").
		$query .= "
			union

			select
				DDIC.DopDispInfoConsent_id,
				STL.SurveyTypeLink_id,
				ST.SurveyType_Name,
				ST.SurveyType_Code,
				ISNULL(ST.SurveyType_IsVizit, 1) as SurveyType_IsVizit,
				EUDDData.UslugaComplex_Code,
				EUDDData.EvnUslugaDispDop_id,
				EUDDData.EvnUslugaDispDop_ExamPlace,
				convert(varchar(20), EUDDData.EvnUslugaDispDop_setDate + EUDDData.EvnUslugaDispDop_setTime, 113) as EvnUslugaDispDop_setDate,
				convert(varchar(10), EUDDData.EvnUslugaDispDop_didDate, 104) as EvnUslugaDispDop_didDate,
				EUDDData.Diag_Code,
				STL.SurveyTypeLink_IsUslPack,
				ISNULL(DDIC.DopDispInfoConsent_IsEarlier, 1) as DopDispInfoConsent_IsEarlier,
				case when el.Evn_lid is not null then 'true' else 'false' end as EvnUslugaDispDop_WithDirection,
				ST.OrpDispSpec_id
				/*
				ep.EvnPrescr_id,
				isnull(ep.EvnPrescr_pid, ed.EvnDirection_pid) as EvnPrescr_pid,
				ep.PrescriptionType_id,
				isnull(ep.EvnDirection_id, ed.EvnDirection_id) as EvnDirection_id
				*/
			from v_DopDispInfoConsent DDIC (nolock)
				left join v_SurveyTypeLink STL (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
				left join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
				/*
				outer apply (
					Select top 1 * from v_EvnDirection ed (nolock) 
					where 
						ed.DopDispInfoConsent_id = ddic.DopDispInfoConsent_id 
						-- Статус направления: все кроме Отменено [12], Отклонено [13].
						and ed.EvnStatus_id not in (12,13)
				) ed
				outer apply (
					select top 1
						ep.EvnPrescr_id,
						Evn.Evn_pid as EvnPrescr_pid,
						ep.PrescriptionType_id,
						ed2.EvnDirection_id
					from
						EvnPrescr ep (nolock)
						inner join Evn (nolock) on Evn.Evn_id = ep.EvnPrescr_id and Evn.Evn_deleted = 1
						outer apply(
							Select top 1 ed2.EvnDirection_id from v_EvnPrescrDirection epd (nolock) 
							inner join v_EvnDirection_all ed2 (nolock) 
								on 
									ed2.EvnDirection_id = epd.EvnDirection_id 
									-- Статус направления: все кроме Отменено [12], Отклонено [13].
									and ed2.EvnStatus_id not in (12,13)
							where epd.EvnPrescr_id = ep.EvnPrescr_id
						) ed2
					where
						ep.DopDispInfoConsent_id = ddic.DopDispInfoConsent_id
				) ep
				*/
				outer apply(
					select top 1
						EUDD.EvnUslugaDispDop_id,
						EUDD.EvnUslugaDispDop_setDate,
						EUDD.EvnUslugaDispDop_setTime,
						EUDD.EvnUslugaDispDop_didDate,
						EUDD.EvnUslugaDispDop_ExamPlace,
						d.Diag_Code,
						UC.UslugaComplex_Code
					from v_EvnUslugaDispDop EUDD (nolock)
						left join v_EvnVizitDispDop EVDD (nolock) on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
						LEFT JOIN v_Diag d WITH (NOLOCK) ON d.Diag_id = EVDD.Diag_id
						left join v_UslugaComplex UC (nolock) on UC.UslugaComplex_id = EUDD.UslugaComplex_id
					where
						EVDD.EvnVizitDispDop_pid = :EvnPLDispDop13_id
						and EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
						-- Оказание услуги по дополнительной диспансеризации: Нет
						and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
				) EUDDData
				left join v_EvnLink el (nolock) on el.Evn_id = EUDDData.EvnUslugaDispDop_id
			where DDIC.EvnPLDisp_id = :EvnPLDispDop13_id
				--  Опрос по услугам (Осмотр, исследование)
				and (
					--  Пройдено ранее: Да.
					DDIC.DopDispInfoConsent_IsEarlier = 2
					--  Согласие гражданина: Да.
					OR DDIC.DopDispInfoConsent_IsAgree = 2 
				)
				-- Типы услуг: Первый этап диспансеризации [1], Опрос (анкетирование) [2], Второй этап диспансеризации [48].
				and ST.SurveyType_Code NOT IN (1, 2, 48)
				-- Комплексное обследование: Нет.
				and ISNULL(STL.SurveyTypeLink_ComplexSurvey, 1) = 1
				-- Записть удалена: Нет.
				and (ISNULL(STL.SurveyTypeLink_IsDel, 1) = 1 or EUDDData.EvnUslugaDispDop_id is not null ) -- только если сохранено согласие
		";

		/**
		 * Выполняется при сохранении карты.
		 *
		 * @see saveEvnPLDispDop13()
		 * @see loadDopDispInfoConsent()
		 */
		$query .= $queryunion; 
		
		if ($this->isDebug) {
			$debug_sql = getDebugSql($query, array('EvnPLDispDop13_id' => $data['EvnPLDispDop13_id']));
		}
		$result = $this->db->query($query, array('EvnPLDispDop13_id' => $data['EvnPLDispDop13_id']));
	
        if (is_object($result))
        {
            $response = $result->result('array');
            return $response;
        }
        else
        {
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

       	$filter .= " and [EPL].[pmUser_insID] = :pmUser_id ";
		$queryParams['pmUser_id'] = $data['pmUser_id'];

		if ( preg_match('/^\d{2}:\d{2}:\d{2}$/', $data['begTime']) )
		{
        	$filter .= " and [EPL].[EvnPL_insDT] >= :date_time";
			$queryParams['date_time'] = $data['begDate'] . " " . $data['begTime'];
		}

        if ( isset($data['Lpu_id']) )
        {
        	$filter .= " and [EPL].[Lpu_id] = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
        }

        $query = "
        	SELECT DISTINCT TOP 100
				[EPL].[EvnPL_id] as [EvnPL_id],
				[EPL].[Person_id] as [Person_id],
				[EPL].[Server_id] as [Server_id],
				[EPL].[PersonEvn_id] as [PersonEvn_id],
				RTRIM([EPL].[EvnPL_NumCard]) as [EvnPL_NumCard],
				RTRIM([PS].[Person_Surname]) as [Person_Surname],
				RTRIM([PS].[Person_Firname]) as [Person_Firname],
				RTRIM([PS].[Person_Secname]) as [Person_Secname],
				convert(varchar(10), [PS].[Person_Birthday], 104) as [Person_Birthday],
				convert(varchar(10), [EPL].[EvnPL_setDate], 104) as [EvnPL_setDate],
				convert(varchar(10), [EPL].[EvnPL_disDate], 104) as [EvnPL_disDate],
				[EPL].[EvnPL_VizitCount] as [EvnPL_VizitCount],
				[IsFinish].[YesNo_Name] as [EvnPL_IsFinish]
			FROM [v_EvnPL] [EPL] (nolock)
				inner join [v_PersonState] [PS] (nolock) on [PS].[Person_id] = [EPL].[Person_id]
				left join [YesNo] [IsFinish] (nolock) on [IsFinish].[YesNo_id] = [EPL].[EvnPL_IsFinish]
			WHERE (1 = 1)
				" . $filter . "
			ORDER BY [EPL].[EvnPL_id] desc
    	";
        $result = $this->db->query($query, $queryParams);

        if (is_object($result))
        {
            return $result->result('array');
        }
 	    else
 	    {
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
				EVPL.EvnVizitPL_id,
				EVPL.LpuSection_id,
				EVPL.MedPersonal_id,
				EVPL.MedPersonal_sid,
				EVPL.PayType_id,
				EVPL.ProfGoal_id,
				EVPL.ServiceType_id,
				EVPL.VizitType_id,
				EVPL.EvnVizitPL_Time,
				convert(varchar(10), EVPL.EvnVizitPL_setDate, 104) as EvnVizitPL_setDate,
				EVPL.EvnVizitPL_setTime,
				RTrim(LS.LpuSection_Name) as LpuSection_Name,
				RTrim(MP.Person_Fio) as MedPersonal_Fio,
				RTrim(PT.PayType_Name) as PayType_Name,
				RTrim(ST.ServiceType_Name) as ServiceType_Name,
				RTrim(VT.VizitType_Name) as VizitType_Name,
				1 as Record_Status
			from v_EvnVizitPL EVPL (nolock)
				left join LpuSection LS (nolock) on LS.LpuSection_id = EVPL.LpuSection_id
				left join v_MedPersonal MP (nolock) on MP.MedPersonal_id = EVPL.MedPersonal_id
				left join PayType PT (nolock) on PT.PayType_id = EVPL.PayType_id
				left join ServiceType ST (nolock) on ST.ServiceType_id = EVPL.ServiceType_id
				left join VizitType VT (nolock) on VT.VizitType_id = EVPL.VizitType_id
			where EVPL.EvnVizitPL_pid = ?
		";
		$result = $this->db->query($query, array($data['EvnPL_id']));

        if (is_object($result))
        {
            return $result->result('array');
        }
        else
        {
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

		$ageModification = $this->getAgeModification(array(
			'onDate' => $data['onDate']
		));

		$resp = $this->queryResult("
			declare @AttributeVision_TablePKey bigint = (select top 1 VolumeType_id from v_VolumeType (nolock) where VolumeType_Code = '2018_ДВН1_85');
			declare
				@age int,
				@EvnPLDispDop13_YearEndDate datetime = cast(substring(:onDate, 1, 4) + '-12-31' as datetime),
				@sex_id bigint;

			select top 1
				@sex_id = ISNULL(Sex_id, 3),
				@age = dbo.Age2(Person_BirthDay, @EvnPLDispDop13_YearEndDate)
			from v_PersonState ps (nolock)
			where ps.Person_id = :Person_id

			{$ageModification}
			
			SELECT
				av.AttributeValue_id,
				av.AttributeValue_ValueInt
			FROM
				v_AttributeVision avis (nolock)
				inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
				inner join v_Attribute a (nolock) on a.Attribute_id = av.Attribute_id
				cross apply(
					select top 1
						av2.AttributeValue_ValueIdent
					from
						v_AttributeValue av2 (nolock)
						inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
					where
						av2.AttributeValue_rid = av.AttributeValue_id
						and a2.Attribute_SysNick = 'Age'
						and av2.AttributeValue_ValueInt = @age
				) AGE
				cross apply(
					select top 1
						av2.AttributeValue_ValueIdent
					from
						v_AttributeValue av2 (nolock)
						inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
					where
						av2.AttributeValue_rid = av.AttributeValue_id
						and a2.Attribute_TableName = 'dbo.Sex'
						and ISNULL(av2.AttributeValue_ValueIdent, @sex_id) = @sex_id
				) SEX
			WHERE
				avis.AttributeVision_TableName = 'dbo.VolumeType'
				and avis.AttributeVision_TablePKey = @AttributeVision_TablePKey
				and avis.AttributeVision_IsKeyValue = 2
				and ISNULL(av.AttributeValue_begDate, :onDate) <= :onDate
				and ISNULL(av.AttributeValue_endDate, :onDate) >= :onDate
		", array(
			'Person_id' => $data['Person_id'],
			'onDate' => $data['onDate']
		));

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
				PS.PersonEvn_id,
				PS.Server_id,
				convert(varchar(10), PS.Person_DeadDT, 104) as Person_deadDT,
				uaddr.Address_Zip as UAddress_Zip,
				baddr.Address_Address as BAddress_Address
			from v_PersonState ps (nolock)
				left join PersonBirthPlace pbp with (nolock) on ps.Person_id = pbp.Person_id
				left join v_Address baddr with (nolock) on baddr.Address_id = pbp.Address_id
				left join v_Address uaddr with (nolock) on ps.UAddress_id = uaddr.Address_id
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
				EVZDD.EvnVizitDispDop_didDT,
				ASVal.AttributeSign_id ,
				ASVal.AttributeSignValue_begDate,
				ASVal.AttributeSignValue_endDate 
			from
				v_EvnVizitDispDop EVZDD with(nolock)
				left join LpuSection LS with(nolock) on LS.LpuSection_id = EVZDD.LpuSection_id
				outer apply (
					select 
						[AS].AttributeSign_id,
						ASV.AttributeSignValue_begDate,
						ASV.AttributeSignValue_endDate 
					from
						v_AttributeSignValue ASV with(nolock)
						inner join v_AttributeSign [AS] with(nolock) on [AS].AttributeSign_id = ASV.AttributeSign_id
					where
						[AS].AttributeSign_TableName = 'dbo.LpuSection'
						and ASV.AttributeSignValue_TablePKey = EVZDD.LpuSection_id
						and [AS].AttributeSign_Name = 'Передвижные подразделения'
				) ASVal
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
		
		$query = "
			declare @getdate datetime = dbo.tzGetDate();

			select
				convert(varchar(10),ISNULL(MIN(EUDDData.EvnUslugaDispDop_didDate), @getdate),120) as mindate,
				convert(varchar(10),ISNULL(MAX(EUDDData.EvnUslugaDispDop_didDate), @getdate),120) as maxdate
			from v_DopDispInfoConsent DDIC (nolock)
				left join v_SurveyTypeLink STL (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
				left join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
				outer apply(
					select top 1
						EUDD.EvnUslugaDispDop_didDate
					from v_EvnUslugaDispDop EUDD (nolock)
						inner join v_EvnVizitDispDop EVDD (nolock) on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
					where
						EVDD.EvnVizitDispDop_pid = :EvnPLDispDop13_id
						and EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
						and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
				) EUDDData
			where
				DDIC.EvnPLDisp_id = :EvnPLDispDop13_id
				and ST.SurveyType_Code NOT IN (1,48)
				{$filter}
		";
		
		$result = $this->db->query($query, array('EvnPLDispDop13_id' => $data['EvnPLDispDop13_id']));
	
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
		$accessType = "'view' as accessType,";
		if (false && !empty($data['session']['CurARM']['PostMed_id']) && in_array($data['session']['CurARM']['PostMed_id'], array(73,74,75,76,40,46,47)) && !empty($data['session']['CurARM']['LpuSection_id'])) {
			$accessType = "case when ISNULL(msf.LpuSection_id, :LpuSection_id) = :LpuSection_id then 'edit' else 'view' end as accessType,";
			$queryParams['LpuSection_id'] = $data['session']['CurARM']['LpuSection_id'];
		}

		$query = "
			select
				epldd13.EvnPLDispDop13_id,
				epldd13.EvnPLDispDop13_fid,
				case
					when epldd13.MedStaffFact_id is not null then ISNULL(l.Lpu_Nick + ' ', '') + ISNULL(ls.LpuSection_Name + ' ', '') + ISNULL(msf.Person_Fio, '') 
					else ISNULL(l.Lpu_Nick + ' ', '') + ISNULL(pu.pmUser_Name, '')
				end as AuthorInfo,
				'EvnPLDispDop13' as Object,
				epldd13.DispClass_id,
				epldd13.Person_id,
				epldd13.PersonEvn_id,
				epldd13.Server_id,
				dc.DispClass_Code,
				dc.DispClass_Name,
				{$accessType}
				epldd13.PayType_id,
				pt.PayType_Name,
				convert(varchar(10), epldd13.EvnPLDispDop13_setDT, 104) as EvnPLDispDop13_setDate,
				convert(varchar(10), epldd13.EvnPLDispDop13_consDT, 104) as EvnPLDispDop13_consDate,
				epldd13.HealthKind_id,
				hk.HealthKind_Name,
				ISNULL(epldd13.EvnPLDispDop13_IsFinish, 1) as EvnPLDispDop13_IsFinish,
				ISNULL(epldd13.EvnPLDispDop13_IsEndStage, 1) as EvnPLDispDop13_IsEndStage,
				ISNULL(epldd13.EvnPLDispDop13_IsTwoStage, 1) as EvnPLDispDop13_IsTwoStage,
				ISNULL(epldd13.EvnPLDispDop13_IsMobile, 1) as EvnPLDispDop13_IsMobile,
				ISNULL(epldd13.EvnPLDispDop13_IsOutLpu, 1) as EvnPLDispDop13_IsOutLpu,
				epldd13.RiskType_id,
				rt.RiskType_Name,
				epldd13.EvnPLDispDop13_SumRick,
				epldd13.EvnPLDispDop13_IsSanator,
				epldd13.NeedDopCure_id,
				ndc.NeedDopCure_Name,
				epldd13.EvnPLDispDop13_IsDisp,
				epldd13.Diag_id,
				d.Diag_Name,
				epldd13.Diag_sid,
				ds.Diag_Name as Diag_sName,
				epldd13.EvnPLDispDop13_IsHypoglyc,
				epldd13.EvnPLDispDop13_IsLipid,
				epldd13.CardioRiskType_id,
				crt.CardioRiskType_Name,
				epldd13.EvnPLDispDop13_IsHypoten,
				epldd13.EvnPLDispDop13_IsShortCons,
				epldd13.EvnPLDispDop13_IsIrrational,
				epldd13.EvnPLDispDop13_IsLowActiv,
				epldd13.EvnPLDispDop13_IsAlcoDepend,
				epldd13.EvnPLDispDop13_IsRiskAlco,
				epldd13.EvnPLDispDop13_IsSmoking,
				epldd13.EvnPLDispDop13_IsEsophag,
				epldd13.EvnPLDispDop13_IsTub,
				epldd13.EvnPLDispDop13_IsDoubleScan,
				epldd13.EvnPLDispDop13_IsBrain,
				epldd13.EvnPLDispDop13_IsStenocard,
				EvnPLDispDop13Sec.EvnPLDispDop13_id as EvnPLDispDop13Sec_id
			from
				v_EvnPLDispDop13 epldd13 (nolock)
				left join v_Lpu l (nolock) on l.Lpu_id = epldd13.Lpu_id
				left join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = epldd13.MedStaffFact_id
				left join v_LpuSection ls (nolock) on ls.LpuSection_id = msf.LpuSection_id
				left join v_pmUser pu (nolock) on pu.pmUser_id = epldd13.pmUser_updID
				left join v_DispClass dc (nolock) on dc.DispClass_id = epldd13.DispClass_id
				left join v_PayType pt (nolock) on pt.PayType_id = epldd13.PayType_id
				left join v_HealthKind hk (nolock) on hk.HealthKind_id = epldd13.HealthKind_id
				left join v_RiskType rt (nolock) on rt.RiskType_id = epldd13.RiskType_id
				left join v_NeedDopCure ndc (nolock) on ndc.NeedDopCure_id = epldd13.NeedDopCure_id
				left join v_Diag d (nolock) on d.Diag_id = epldd13.Diag_id
				left join v_Diag ds (nolock) on ds.Diag_id = epldd13.Diag_sid
				left join v_CardioRiskType crt (nolock) on crt.CardioRiskType_id = epldd13.CardioRiskType_id
				outer apply(
					select top 1
						EvnPLDispDop13_id
					from v_EvnPLDispDop13 with (nolock)
					where
						EvnPLDispDop13_fid = EPLDD13.EvnPLDispDop13_id
				) EvnPLDispDop13Sec
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
					RVT.RateValueType_SysNick,
					CASE RVT.RateValueType_SysNick
						WHEN 'int' THEN cast(R.Rate_ValueInt as varchar)
						WHEN 'float' THEN cast(cast(R.Rate_ValueFloat as decimal(16,3)) as varchar)
						WHEN 'string' THEN R.Rate_ValueStr
						WHEN 'template' THEN R.Rate_ValueStr
						WHEN 'reference' THEN cast(R.Rate_ValuesIs as varchar)
					END as value
				from v_DopDispInfoConsent DDIC (nolock)
					left join v_SurveyTypeLink STL (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
					left join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
					left join v_UslugaComplex UC (nolock) on UC.UslugaComplex_id = STL.UslugaComplex_id
					outer apply(
						select top 1
							EUDD.EvnUslugaDispDop_id
						from v_EvnUslugaDispDop EUDD (nolock)
							inner join v_EvnVizitDispDop EVDD (nolock) on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
						where
							EVDD.EvnVizitDispDop_pid = :EvnPLDispDop13_id and EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
							and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
					) EUDDData
					left join v_EvnUslugaRate eur (nolock) on eur.EvnUsluga_id = EUDDData.EvnUslugaDispDop_id
					left join v_Rate r (nolock) on r.Rate_id = eur.Rate_id 
					left join v_RateType rt (nolock) on rt.RateType_id = r.RateType_id
					left join RateValueType rvt (nolock) on rvt.RateValueType_id = rt.RateValueType_id
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
					RVT.RateValueType_SysNick,
					CASE RVT.RateValueType_SysNick
						WHEN 'int' THEN cast(R.Rate_ValueInt as varchar)
						WHEN 'float' THEN cast(cast(R.Rate_ValueFloat as decimal(16,3)) as varchar)
						WHEN 'string' THEN R.Rate_ValueStr
						WHEN 'template' THEN R.Rate_ValueStr
						WHEN 'reference' THEN cast(R.Rate_ValuesIs as varchar)
					END as value
				from v_DopDispInfoConsent DDIC (nolock)
					left join v_SurveyTypeLink STL (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
					left join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
					left join v_UslugaComplex UC (nolock) on UC.UslugaComplex_id = STL.UslugaComplex_id
					outer apply(
						select top 1
							EUDD.EvnUslugaDispDop_id
						from v_EvnUslugaDispDop EUDD (nolock)
							inner join v_EvnVizitDispDop EVDD (nolock) on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
						where
							EVDD.EvnVizitDispDop_pid = :EvnPLDispDop13_id and EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
							and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
					) EUDDData
					left join v_EvnUslugaRate eur (nolock) on eur.EvnUsluga_id = EUDDData.EvnUslugaDispDop_id
					left join v_Rate r (nolock) on r.Rate_id = eur.Rate_id 
					left join v_RateType rt (nolock) on rt.RateType_id = r.RateType_id
					left join RateValueType rvt (nolock) on rvt.RateValueType_id = rt.RateValueType_id
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
					ST.SurveyType_Name,
					case
						when EUDD.EvnUslugaDispDop_didDate is null then 0 else 1
					end as EvnVizitDispDop_IsExists
				from v_DopDispInfoConsent DDIC with(nolock)
					left join SurveyTypeLink STL with(nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
					left join SurveyType ST with(nolock) on ST.SurveyType_id = STL.SurveyType_id
					left join v_EvnVizitDispDop EVDD with(nolock) on EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
					left join v_EvnUslugaDispDop EUDD with(nolock) on EUDD.EvnUslugaDispDop_pid = EVDD.EvnVizitDispDop_id
					left join v_YesNo IsAgree with (nolock) on IsAgree.YesNo_id = DDIC.DopDispInfoConsent_IsAgree
				where
					DDIC.EvnPLDisp_id = :EvnPLDisp_id
					and ST.SurveyType_Code in (13,18,21)
					and IsAgree.YesNo_Code = 1
					and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
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
		  		select top 1 * 
		  		from v_EvnPLDispDop13 with(nolock) 
		  		where EvnPLDispDop13_id = :EvnPLDispDop13_id
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
				select EvnPLDispDop13_id from v_EvnPLDispDop13 (nolock) where EvnPLDispDop13_fid = :EvnPLDispDop13_fid
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
				select top 1 
					EDDD.EvnDiagDopDisp_id
				from
					v_EvnDiagDopDisp EDDD (nolock)
					cross apply (
						select top 1
							EDDD2.EvnDiagDopDisp_id
						from
							v_EvnDiagDopDisp EDDD2 (nolock)
							inner join v_EvnUslugaDispDop eudd (nolock) on eudd.EvnUslugaDispDop_id = EDDD2.EvnDiagDopDisp_pid
							inner join v_EvnVizitDispDop evdd (nolock) on evdd.EvnVizitDispDop_id = eudd.EvnUslugaDispDop_pid
						where
							evdd.EvnVizitDispDop_pid = :EvnPLDispDop13_id
							and EDDD2.DiagSetClass_id = 3
							and EDDD2.Diag_id = EDDD.Diag_id
							and EDDD2.DeseaseDispType_id <> EDDD.DeseaseDispType_id
					) EDDD2
				where
					EDDD.EvnDiagDopDisp_pid = :EvnPLDispDop13_id
					and EDDD.DiagSetClass_id = 3
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
			select convert(varchar(10), EvnPLDispDop13_consDT, 120) from v_EvnPLDispDop13 (nolock) where EvnPLDispDop13_id = :EvnPLDispDop13_id
		", $data);

		$onDate = $data['EvnPLDispDop13_consDate'];
		if ($data['DispClass_id'] == 1 && !empty($data['EvnPLDispDop13_IsNewOrder']) && $data['EvnPLDispDop13_IsNewOrder'] == 2) {
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
					evdd.EvnVizitDispDop_id,
					1 as ErrorType
				from v_EvnVizitDispDop evdd with (nolock)
					inner join v_DopDispInfoConsent ddic with (nolock) on ddic.DopDispInfoConsent_id = evdd.DopDispInfoConsent_id
				where
					evdd.EvnVizitDispDop_pid = :EvnPLDispDop13_id
					and ISNULL(ddic.DopDispInfoConsent_IsEarlier, 1) = 2
					and cast(evdd.EvnVizitDispDop_setDT as date) >= :EvnPLDispDop13_consDate

				union all

				select
					evdd.EvnVizitDispDop_id,
					2 as ErrorType
				from v_EvnVizitDispDop evdd with (nolock)
					inner join v_DopDispInfoConsent ddic with (nolock) on ddic.DopDispInfoConsent_id = evdd.DopDispInfoConsent_id
				where
					evdd.EvnVizitDispDop_pid = :EvnPLDispDop13_id
					and ISNULL(ddic.DopDispInfoConsent_IsEarlier, 1) = 1
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
			$resp_consDT = $this->queryResult("select top 1 convert(varchar(10), EvnPLDispDop13_consDT, 104) as EvnPLDispDop13_consDT from v_EvnPLDispDop13 (nolock) where EvnPLDispDop13_id = :EvnPLDispDop13_id", array(
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
			if (in_array(getRegionNick(), ['krasnoyarsk', 'perm'])  && (
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
									DopDispInfoConsent with (rowlock)
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
				declare @PayType_id bigint = (select top 1 PayType_id from v_PayType with (nolock) where PayType_SysNick = 'oms');

				select top 1
					EvnVizitPL_id as Evn_id
				from
					v_EvnVizitPL (nolock)
				where
					EvnVizitPL_setDate >= :EvnPLDispDop13_consDate
					and EvnVizitPL_setDate <= :EvnPLDispDop13_disDate
					and Person_id = :Person_id
					and PayType_id = @PayType_id

				union all

				select top 1
					es.EvnSection_id as Evn_id
				from
					v_EvnSection es (nolock)
					inner join v_LpuSection ls (nolock) on ls.LpuSection_id = es.LpuSection_id
					inner join v_LpuUnit lu (nolock) on lu.LpuUnit_id = ls.LpuUnit_id
				where
					(
						(es.EvnSection_setDate >= :EvnPLDispDop13_consDate and es.EvnSection_setDate <= :EvnPLDispDop13_disDate) -- начало входит в промежуток
						OR (es.EvnSection_setDate < :EvnPLDispDop13_consDate and (es.EvnSection_disDate >= :EvnPLDispDop13_consDate OR es.EvnSection_disDate IS NULL)) -- начало до промежутка, а конец после
					)
					and lu.LpuUnitType_id = 1
					and es.Person_id = :Person_id
					and es.PayType_id = @PayType_id
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


		$setDtField = "@EvnPLDispDop13_consDate";
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
		    declare
		        @EvnPLDispDop13_id bigint,
				@EvnPLDispDop13_IsRefusal bigint,
				@EvnDirection_aid bigint,
				@EvnPLDispDop13_consDate date,
				@ErrCode int,
				@ErrMessage varchar(4000),
				@curdate datetime;

			set @curdate = dbo.tzGetDate();
			set @EvnPLDispDop13_id = :EvnPLDispDop13_id;

			select top 1
				@EvnPLDispDop13_IsRefusal = EvnPLDispDop13_IsRefusal,
				@EvnDirection_aid = EvnDirection_aid,
				@EvnPLDispDop13_consDate = EvnPLDispDop13_consDT
			from v_EvnPLDispDop13 (nolock)
			where EvnPLDispDop13_id = :EvnPLDispDop13_id;

			exec p_EvnPLDispDop13_upd
				@EvnPLDispDop13_id = @EvnPLDispDop13_id output,
				@MedStaffFact_id = :MedStaffFact_id,
				@EvnPLDispDop13_IsNewOrder = :EvnPLDispDop13_IsNewOrder,
				@EvnPLDispDop13_IndexRep = :EvnPLDispDop13_IndexRep,
				@EvnPLDispDop13_IndexRepInReg = :EvnPLDispDop13_IndexRepInReg,
				@PersonEvn_id = :PersonEvn_id,
				@EvnPLDispDop13_setDT = {$setDtField},
				@EvnPLDispDop13_disDT = :EvnPLDispDop13_disDate,
				@Server_id = :Server_id,
				@Lpu_id = :Lpu_id,
				@DispClass_id = :DispClass_id,
				@PayType_id = :PayType_id,
				@EvnPLDispDop13_fid = :EvnPLDispDop13_fid,
				@AttachType_id = 2,
				@EvnPLDispDop13_IsStenocard = :EvnPLDispDop13_IsStenocard,
				@EvnPLDispDop13_IsShortCons = :EvnPLDispDop13_IsShortCons,
				@EvnPLDispDop13_IsBrain = :EvnPLDispDop13_IsBrain,
				@EvnPLDispDop13_IsDoubleScan = :EvnPLDispDop13_IsDoubleScan,
				@EvnPLDispDop13_IsTub = :EvnPLDispDop13_IsTub,
				@EvnPLDispDop13_IsTIA = :EvnPLDispDop13_IsTIA,
				@EvnPLDispDop13_IsRespiratory = :EvnPLDispDop13_IsRespiratory,
				@EvnPLDispDop13_IsLungs = :EvnPLDispDop13_IsLungs,
				@EvnPLDispDop13_IsTopGastro = :EvnPLDispDop13_IsTopGastro,
				@EvnPLDispDop13_IsBotGastro = :EvnPLDispDop13_IsBotGastro,
				@EvnPLDispDop13_IsSpirometry = :EvnPLDispDop13_IsSpirometry,
				@EvnPLDispDop13_IsHeartFailure = :EvnPLDispDop13_IsHeartFailure,
				@EvnPLDispDop13_IsOncology = :EvnPLDispDop13_IsOncology,
				@EvnPLDispDop13_IsEsophag = :EvnPLDispDop13_IsEsophag,
				@EvnPLDispDop13_IsSmoking = :EvnPLDispDop13_IsSmoking,
				@EvnPLDispDop13_IsRiskAlco = :EvnPLDispDop13_IsRiskAlco,
				@EvnPLDispDop13_IsAlcoDepend = :EvnPLDispDop13_IsAlcoDepend,
				@EvnPLDispDop13_IsLowActiv = :EvnPLDispDop13_IsLowActiv,
				@EvnPLDispDop13_IsIrrational = :EvnPLDispDop13_IsIrrational,
				@EvnPLDispDop13_IsUseNarko = :EvnPLDispDop13_IsUseNarko,
				@Diag_id = :Diag_id,
				@Diag_sid = :Diag_sid,
				@EvnPLDispDop13_IsDisp = :EvnPLDispDop13_IsDisp,
				@NeedDopCure_id = :NeedDopCure_id,
				@EvnPLDispDop13_IsStac = :EvnPLDispDop13_IsStac,
				@EvnPLDispDop13_IsSanator = :EvnPLDispDop13_IsSanator,
				@EvnPLDispDop13_SumRick = :EvnPLDispDop13_SumRick,
				@RiskType_id = :RiskType_id,
				@EvnPLDispDop13_IsSchool = :EvnPLDispDop13_IsSchool,
				@EvnPLDispDop13_IsProphCons = :EvnPLDispDop13_IsProphCons,
				@EvnPLDispDop13_IsHypoten = :EvnPLDispDop13_IsHypoten,
				@EvnPLDispDop13_IsLipid = :EvnPLDispDop13_IsLipid,
				@EvnPLDispDop13_IsHypoglyc = :EvnPLDispDop13_IsHypoglyc,
				@HealthKind_id = :HealthKind_id,
				@EvnPLDispDop13_IsEndStage = :EvnPLDispDop13_IsEndStage,
				@EvnPLDispDop13_IsFinish = :EvnPLDispDop13_IsEndStage,
				@EvnPLDispDop13_IsTwoStage = :EvnPLDispDop13_IsTwoStage,
				@EvnPLDispDop13_consDT = @EvnPLDispDop13_consDate,
				@EvnPLDispDop13_IsMobile = :EvnPLDispDop13_IsMobile, 
				@EvnPLDispDop13_IsOutLpu = :EvnPLDispDop13_IsOutLpu, 
				@Lpu_mid = :Lpu_mid,
				@CardioRiskType_id = :CardioRiskType_id,
				@EvnPLDispDop13_IsRefusal = @EvnPLDispDop13_IsRefusal,
				@EvnDirection_aid = @EvnDirection_aid,
				@EvnPLDispDop13_Percent = :EvnPLDispDop13_Percent,
				@EvnPLDispDop13_IsSuspectZNO = :EvnPLDispDop13_IsSuspectZNO,
				@Diag_spid = :Diag_spid,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
				
			select @EvnPLDispDop13_id as EvnPLDispDop13_id, convert(varchar(10), @EvnPLDispDop13_consDate, 120) as EvnPLDispDop13_setDT, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
				select top 1
					EvnUslugaDispDop_id,
					UslugaComplex_id,
					PayType_id,
					convert(varchar(10), EvnUslugaDispDop_setDT, 104) as EvnUslugaDispDop_setDate
				from v_EvnUslugaDispDop with (nolock)
				where EvnUslugaDispDop_pid = :EvnUslugaDispDop_pid
					and EvnUslugaDispDop_IsVizitCode = 2
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
			if ($data['DispClass_id'] == 1 && !empty($data['EvnPLDispDop13_IsNewOrder']) && $data['EvnPLDispDop13_IsNewOrder'] == 2) {
				$onDate = date('Y', strtotime($data['EvnPLDispDop13_consDate'])) . '-12-31';
			}

			$ageModification = $this->getAgeModification(array(
				'onDate' => $onDate
			));

			// Определяем UslugaComplex_id через $schema.UslugaSurveyLink
			$query = "
				declare
					@sex_id bigint,
					@age int,
					@year varchar(4),
					@EvnPLDispDop13_YearEndDate datetime = cast(substring(:EvnPLDispDop13_consDate, 1, 4) + '-12-31' as datetime);

				if ( :EvnPLDispDop13_setDT is not null ) 
					set @year = substring(:EvnPLDispDop13_setDT, 1, 4);

				if ( @year is null )
					set @year = cast(year(dbo.tzGetDate()) as varchar(4));

				select top 1
					@sex_id = ISNULL(Sex_id, 3),
					@age = dbo.Age2(Person_BirthDay, cast(@year + '-12-31' as datetime))
				from v_PersonState ps (nolock)
				where ps.Person_id = :Person_id

				{$ageModification}

				select top 1 USL.UslugaComplex_id
				from {$data['session']['region']['schema']}.v_UslugaSurveyLink USL with (nolock)
				where
					USL.DispClass_id = :DispClass_id
					and ISNULL(USL.Sex_id, @sex_id) = @sex_id
					and @age between ISNULL(USL.UslugaSurveyLink_From, 0) and ISNULL(USL.UslugaSurveyLink_To, 999)
					and ISNULL(USL.UslugaSurveyLink_IsDel, 1) = 1
					and (USL.UslugaSurveyLink_begDate is null or USL.UslugaSurveyLink_begDate <= :EvnPLDispDop13_consDate)
					and (USL.UslugaSurveyLink_endDate is null or USL.UslugaSurveyLink_endDate >= :EvnPLDispDop13_consDate)
			";
			$result = $this->db->query($query, array(
				'DispClass_id' => $data['DispClass_id'],
				'EvnPLDispDop13_consDate' => $onDate,
				'EvnPLDispDop13_setDT' => $data['EvnPLDispDop13_setDT'],
				'Person_id' => $data['Person_id']
			));

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
					declare
						@id bigint = :EvnUslugaDispDop_id,
						@pt bigint = :PayType_id,
						@ErrCode int,
						@ErrMessage varchar(4000);

					if ( @pt is null )
						set @pt = (select top 1 PayType_id from v_PayType with (nolock) where PayType_SysNick = 'dopdisp');

					exec p_EvnUslugaDispDop_" . (!empty($uslugaData['EvnUslugaDispDop_id']) ? "upd" : "ins") . "
						@EvnUslugaDispDop_id = @id output,
						@EvnUslugaDispDop_pid = :EvnUslugaDispDop_pid,
						@UslugaComplex_id = :UslugaComplex_id,
						@EvnUslugaDispDop_setDT = :EvnUslugaDispDop_setDT,
						@EvnUslugaDispDop_IsVizitCode = 2,
						@Lpu_id = :Lpu_id,
						@Server_id = :Server_id,
						@PersonEvn_id = :PersonEvn_id,
						@PayType_id = @pt,
						@UslugaPlace_id = 1,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;

					select @id as EvnUslugaDispDop_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				$result = $this->db->query($query, array(
					'EvnUslugaDispDop_id' => (!empty($uslugaData['EvnUslugaDispDop_id']) ? $uslugaData['EvnUslugaDispDop_id'] : null),
					'EvnUslugaDispDop_pid' => $data['EvnPLDispDop13_id'],
					'UslugaComplex_id' => $UslugaComplex_id,
					'EvnUslugaDispDop_setDT' => (!empty($data['EvnPLDispDop13_setDT']) ? $data['EvnPLDispDop13_setDT'] : null),
					'Lpu_id' => $data['Lpu_id'],
					'Server_id' => $data['Server_id'],
					'PersonEvn_id' => $data['PersonEvn_id'],
					'PayType_id' => (!empty($uslugaData['PayType_id']) ? $uslugaData['PayType_id'] : null),
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
					declare
						@ErrCode int,
						@ErrMessage varchar(4000);

					exec p_EvnUslugaDispDop_del
						@EvnUslugaDispDop_id = :EvnUslugaDispDop_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;

					select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
				select top 1
					EUDD.EvnUslugaDispDop_id
				from
					v_EvnUslugaDispDop EUDD with (nolock)
					inner join v_SurveyTypeLink STL with (nolock) on STL.UslugaComplex_id = EUDD.UslugaComplex_id
					inner join v_SurveyType ST with (nolock) on ST.SurveyType_id = STL.SurveyType_id and ST.SurveyType_Code = 2
				where
					EUDD.EvnUslugaDispDop_pid = :EvnPLDispDop13_id
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
								declare
									@Rate_id bigint,
									@ErrCode int,
									@ErrMessage varchar(4000);
								set @Rate_id = :Rate_id;
								exec p_Rate_ins
									@Rate_id = @Rate_id output,
									@RateType_id = :RateType_id,
									@Rate_ValueInt = :Rate_ValueInt,
									@Rate_ValueFloat = :Rate_ValueFloat,
									@Rate_ValueStr = :Rate_ValueStr,
									@Rate_ValuesIs = :Rate_ValuesIs,
									@Server_id = :Server_id,
									@pmUser_id = :pmUser_id,
									@Error_Code = @ErrCode output,
									@Error_Message = @ErrMessage output;
								select @Rate_id as Rate_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
								declare
									@EvnUslugaRate_id bigint,
									@ErrCode int,
									@ErrMessage varchar(4000);
								set @EvnUslugaRate_id = :EvnUslugaRate_id;
								exec p_EvnUslugaRate_ins
									@EvnUslugaRate_id = @EvnUslugaRate_id output,
									@EvnUsluga_id = :EvnUsluga_id,
									@Rate_id = :Rate_id,
									@Server_id = :Server_id,
									@pmUser_id = :pmUser_id,
									@Error_Code = @ErrCode output,
									@Error_Message = @ErrMessage output;
								select @EvnUslugaRate_id as EvnUslugaRate_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
								declare
									@Rate_id bigint,
									@ErrCode int,
									@ErrMessage varchar(4000);
								set @Rate_id = :Rate_id;
								exec p_Rate_upd
									@Rate_id = @Rate_id output,
									@RateType_id = :RateType_id,
									@Rate_ValueInt = :Rate_ValueInt,
									@Rate_ValueFloat = :Rate_ValueFloat,
									@Rate_ValueStr = :Rate_ValueStr,
									@Rate_ValuesIs = :Rate_ValuesIs,
									@Server_id = :Server_id,
									@pmUser_id = :pmUser_id,
									@Error_Code = @ErrCode output,
									@Error_Message = @ErrMessage output;
								select @Rate_id as Rate_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
	            year(EPLDD13.EvnPLDispDop13_setDate) as EvnPLDispDop13_Year
	        from
				-- from
                v_PersonState PS with (nolock)
                inner join [v_EvnPLDispDop13] [EPLDD13] with (nolock) on [PS].[Person_id] = [EPLDD13].[Person_id] and [EPLDD13].Lpu_id = :Lpu_id and [EPLDD13].DispClass_id IN (1)
				-- end from
			where
				-- where
				[EPLDD13].EvnPLDispDop13_setDate >= cast('2013-01-01' as datetime)
				and [EPLDD13].EvnPLDispDop13_setDate <= cast('2013-12-31' as datetime)
				and exists (select top 1 personcard_id from v_PersonCard PC with (nolock)  left join v_LpuRegion LR with (nolock) on LR.LpuRegion_id = PC.LpuRegion_id WHERE PC.Person_id = PS.Person_id and PC.Lpu_id = :Lpu_id)
				-- end where
			GROUP BY
				year(EPLDD13.EvnPLDispDop13_setDate)
			ORDER BY
				year(EPLDD13.EvnPLDispDop13_setDate)
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
					(dbo.Age2(PS.Person_BirthDay, cast(year(EPLDD13.EvnPLDispDop13_setDate) as varchar) + '-12-31') BETWEEN 18 AND {$maxage})
					and exists (
						select top 1 pp.PersonPrivilege_id
						from v_PersonPrivilege pp (nolock)
							inner join v_PrivilegeType pt (nolock) on pt.PrivilegeType_id = pp.PrivilegeType_id
						where pt.PrivilegeType_Code in ('" . implode("','", $personPrivilegeCodeList) . "')
							and pp.Person_id = PS.Person_id
							and pp.PersonPrivilege_begDate <= cast(year(EPLDD13.EvnPLDispDop13_setDate) as varchar) + '-12-31'
							and (pp.PersonPrivilege_endDate >= cast(year(EPLDD13.EvnPLDispDop13_setDate) as varchar) + '-12-31' or pp.PersonPrivilege_endDate is null)
					)
				) -- refs #23044
			";
		}
		else if ( in_array($this->regionNick, array('ufa', 'ekb', 'kareliya', 'penza', 'astra')) ) {
			$addfilter .= "
				or (
					(dbo.Age2(PS.Person_BirthDay, cast(year(EPLDD13.EvnPLDispDop13_setDate) as varchar) + '-12-31') BETWEEN 18 AND {$maxage})
					and exists (
						select top 1 PersonPrivilegeWOW_id
						from v_PersonPrivilegeWOW (nolock)
						where Person_id = PS.Person_id
					)
				)
			";
		}

  		$sql = "
			select
	            count(EPLDD13.EvnPLDispDop13_id) as count,
	            year(EPLDD13.EvnPLDispDop13_setDate) as EvnPLDispDop13_Year
	        from
		        v_PersonState PS with (nolock)
		        left join [v_EvnPLDispDop13] [EPLDD13] with (nolock) on [PS].[Person_id] = [EPLDD13].[Person_id] and [EPLDD13].Lpu_id = :Lpu_id and ISNULL(DispClass_id,1) = 1 and YEAR(EvnPLDispDop13_setDate) = year(EPLDD13.EvnPLDispDop13_setDate)
	        where
	            isnull([EPLDD13].EvnPLDispDop13_IsTwoStage, 1) = '2'
            	and (
					(dbo.Age2(PS.Person_BirthDay, cast(year(EPLDD13.EvnPLDispDop13_setDate) as varchar) + '-12-31') - 21 >= 0 and (dbo.Age2(PS.Person_BirthDay, cast(year(EPLDD13.EvnPLDispDop13_setDate) as varchar) + '-12-31') - 21) % 3 = 0)
					{$addfilter}
				)
				and dbo.Age2(PS.Person_BirthDay, cast(year(EPLDD13.EvnPLDispDop13_setDate) as varchar) + '-12-31') <= {$maxage}
				and exists (select top 1 personcard_id from v_PersonCard PC with (nolock)  left join v_LpuRegion LR with (nolock) on LR.LpuRegion_id = PC.LpuRegion_id WHERE PC.Person_id = PS.Person_id and (PC.PersonCard_endDate is null or cast(PC.PersonCard_endDate as datetime) > dbo.tzGetDate()) and PC.Lpu_id = :Lpu_id)
				and year(EPLDD13.EvnPLDispDop13_setDate) >= 2013
				and EPLDD13.EvnPLDispDop13_setDate is not null
			GROUP BY
				year(EPLDD13.EvnPLDispDop13_setDate)
			ORDER BY
				year(EPLDD13.EvnPLDispDop13_setDate)
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
					(dbo.Age2(PS.Person_BirthDay, cast(year(EPLDD13.EvnPLDispDop13_setDate) as varchar) + '-12-31') BETWEEN 18 AND {$maxage})
					and exists (
						select top 1 pp.PersonPrivilege_id
						from v_PersonPrivilege pp (nolock)
							inner join v_PrivilegeType pt (nolock) on pt.PrivilegeType_id = pp.PrivilegeType_id
						where pt.PrivilegeType_Code in ('" . implode("','", $personPrivilegeCodeList) . "')
							and pp.Person_id = PS.Person_id
							and pp.PersonPrivilege_begDate <= cast(year(EPLDD13.EvnPLDispDop13_setDate) as varchar) + '-12-31'
							and (pp.PersonPrivilege_endDate >= cast(year(EPLDD13.EvnPLDispDop13_setDate) as varchar) + '-12-31' or pp.PersonPrivilege_endDate is null)
					)
				) -- refs #23044
			";
		}
		else if ( in_array($this->regionNick, array('ufa', 'ekb', 'kareliya', 'penza', 'astra')) ) {
			$addfilter .= "
				or (
					(dbo.Age2(PS.Person_BirthDay, cast(year(EPLDD13.EvnPLDispDop13_setDate) as varchar) + '-12-31') BETWEEN 18 AND {$maxage})
					and exists (
						select top 1 PersonPrivilegeWOW_id
						from v_PersonPrivilegeWOW (nolock)
						where Person_id = PS.Person_id
					)
				)
			";
		}

  		$sql = "
			select
	            count(EPLDD13.EvnPLDispDop13_id) as count,
	            year(EPLDD13.EvnPLDispDop13_setDate) as EvnPLDispDop13_Year
	        from
		        v_PersonState PS with (nolock)
		        left join [v_EvnPLDispDop13] [EPLDD13] with (nolock) on [PS].[Person_id] = [EPLDD13].[Person_id] and [EPLDD13].Lpu_id = :Lpu_id and ISNULL(DispClass_id,1) = 1 and YEAR(EvnPLDispDop13_setDate) = year(EPLDD13.EvnPLDispDop13_setDate)
	        where
	            isnull([EPLDD13].EvnPLDispDop13_IsTwoStage, 1) = '2'
            	and (
					(dbo.Age2(PS.Person_BirthDay, cast(year(EPLDD13.EvnPLDispDop13_setDate) as varchar) + '-12-31') - 21 >= 0 and (dbo.Age2(PS.Person_BirthDay, cast(year(EPLDD13.EvnPLDispDop13_setDate) as varchar) + '-12-31') - 21) % 3 = 0)
					{$addfilter}
				)
				and dbo.Age2(PS.Person_BirthDay, cast(year(EPLDD13.EvnPLDispDop13_setDate) as varchar) + '-12-31') <= {$maxage}
				and exists (select top 1 personcard_id from v_PersonCard PC with (nolock)  left join v_LpuRegion LR with (nolock) on LR.LpuRegion_id = PC.LpuRegion_id WHERE PC.Person_id = PS.Person_id and (PC.PersonCard_endDate is null or cast(PC.PersonCard_endDate as datetime) > dbo.tzGetDate()) and PC.Lpu_id = :Lpu_id)
				and year(EPLDD13.EvnPLDispDop13_setDate) >= 2013
				and EPLDD13.EvnPLDispDop13_setDate is not null
			GROUP BY
				year(EPLDD13.EvnPLDispDop13_setDate)
			ORDER BY
				year(EPLDD13.EvnPLDispDop13_setDate)
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
				v_EvnPLDispDop13 (nolock)
			WHERE
				Person_id = ? and Lpu_id = ? and year(EvnPLDispDop13_setDate) = year(dbo.tzGetDate())
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
				dd.EvnPLDispDop13_setDT,
				case when EvnPLDispDop13_setDate >= '2015-04-01' then 1 else 0 end as is_new_event,
				dd.Person_id,
				ps.Person_FirName,
				ps.Person_SecName,
				ps.Person_SurName,
				ps.Person_Phone,
				ps.Sex_id,
				datepart(DD,ps.Person_BirthDay) as Person_BirthDay_Day,
				datepart(MM,ps.Person_BirthDay) as Person_BirthDay_Month,
				datepart(YYYY,ps.Person_BirthDay) as Person_BirthDay_Year,
				ua.Address_House,
				ua.Address_Corpus,
				ua.Address_Flat,
				ua.KLStreet_Name,
				(
						ua.KLRGN_Name+' '+ua.KLRGN_Socr
						+ISNULL(', '+ua.KLCity_Socr+' '+ua.KLCity_Name,'')
						+ISNULL(', '+ua.KLTown_Socr+' '+ua.KLTown_Name,'')
				) as Address_Info,
				l.Lpu_Name,
				l.Org_Phone,
				l2.PAddress_Address as l_address,
				pc.PersonCard_Code,
				pc.LpuRegion_Name,
				mp.Person_Fio as MedPerson_Fio,
				case when pls.PolisType_id = 4 then '' else pls.Polis_Ser end as Polis_Ser,
				case when pls.PolisType_id = 4 then ps.Person_EdNum else pls.Polis_Num end as Polis_Num,
				OS.OrgSmo_Name as polis_orgnick,
				ps.Person_Snils,
				dd.EvnPLDispDop13_IsSmoking as IsSmoking,
				dd.EvnPLDispDop13_IsRiskAlco as IsRiskAlco,
				dd.EvnPLDispDop13_IsLowActiv as IsLowActiv,
				dd.EvnPLDispDop13_IsIrrational as IsIrrational
			FROM 
				v_EvnPLDispDop13 dd (nolock)
				inner join v_PersonState ps (nolock) on ps.Person_id = dd.Person_id
				left join v_Address_all ua (nolock) on ua.Address_id = ps.UAddress_id
				left join v_Lpu_all l (nolock) on l.Lpu_id = dd.Lpu_id
				left join v_Lpu l2 (nolock) on l2.Lpu_id = dd.Lpu_id
				left join v_PersonCard pc (nolock) on (PC.Person_id = ps.Person_id and pc.LpuAttachType_id = 1)
				left join v_MedStaffRegion msr (nolock) on msr.LpuRegion_id = pc.LpuRegion_id
				left join v_MedPersonal mp (nolock) on mp.MedPersonal_id = msr.MedPersonal_id and mp.Dolgnost_Code = '73' and mp.WorkType_id = '1'
				left join v_Polis pls with (nolock) on pls.Polis_id = ps.Polis_id
				left join v_OrgSmo OS (nolock) on OS.OrgSmo_id = pls.OrgSmo_id
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
				--EPDD.EvnPLDispDop13_IsFinish,
				EPDD.EvnPLDispDop13_IsEndStage,
				HK.HealthKind_Name as value,
				CONVERT(varchar(10), EVDD.EvnVizitDispDop_setDate, 104) as date,
				datepart(year,EVDD.EvnVizitDispDop_setDate) as year
			from v_EvnPLDispDop13 EPDD (nolock)
				inner join v_EvnVizitDispDop EVDD (nolock) on EVDD.EvnVizitDispDop_pid = EPDD.EvnPLDispDop13_id
				left join DopDispSpec DDS (nolock) on DDS.DopDispSpec_id = EVDD.DopDispSpec_id
				left join HealthKind HK (nolock) on HK.HealthKind_id = EPDD.HealthKind_id
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
				st.SurveyType_Code,
				convert(varchar(10), EUDD.EvnUslugaDispDop_didDate, 104) as EvnUslugaDispDop_didDate,
				(/*mp.Dolgnost_Name*/ps.PostMed_Name + '<br>' + mp.Person_Fio) as Med_Personal
			from v_EvnUslugaDispDop EUDD (nolock)
				left join v_EvnVizitDispDop evdd (nolock) on eudd.EvnUslugaDispDop_pid = evdd.EvnVizitDispDop_id
				left join v_MedPersonal mp (nolock) on mp.MedPersonal_id = evdd.MedPersonal_id and EUDD.Lpu_id=mp.Lpu_id
				left join v_MedStaffFact msf with(nolock) on msf.LpuSection_id = EUDD.LpuSection_uid and msf.MedPersonal_id = mp.MedPersonal_id and msf.Lpu_id = mp.Lpu_id
				left join v_PostMed ps with (nolock) on ps.PostMed_id=msf.Post_id
				left join v_DopDispInfoConsent ddic (nolock) on evdd.DopDispInfoConsent_id = ddic.DopDispInfoConsent_id
				left join v_SurveyTypeLink stl (nolock) on stl.SurveyTypeLink_id = ddic.SurveyTypeLink_id
				left join v_SurveyType st (nolock) on st.SurveyType_id = stl.SurveyType_id
			where
				EUDD.EvnUslugaDispDop_rid = :EvnPLDispDop13_id
				and st.SurveyType_Code = '19'
				and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
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
					convert(varchar(10), EUDDData.EvnUslugaDispDop_didDate, 104) as Diag_date,
					EUDDData.Diag_Name,
					EUDDData.Diag_Code
				from v_DopDispInfoConsent DDIC (nolock)
					left join v_SurveyTypeLink STL (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
					left join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
					outer apply(
						select top 1
							EUDD.EvnUslugaDispDop_id,
							EUDD.EvnUslugaDispDop_pid,
							EUDD.EvnUslugaDispDop_didDate,
							D.Diag_Code,
							D.Diag_Name
						from v_EvnUslugaDispDop EUDD (nolock)
							left join v_EvnVizitDispDop EVDD (nolock) on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
							inner join v_Diag D (nolock) on D.Diag_id = EVDD.Diag_id
						where
							EVDD.EvnVizitDispDop_pid = :EvnPLDispDop13_id
							and EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
							and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
					) EUDDData
				where DDIC.EvnPLDisp_id = :EvnPLDispDop13_id
					and (DDIC.DopDispInfoConsent_IsAgree = 2 OR DDIC.DopDispInfoConsent_IsEarlier = 2)
					and ST.SurveyType_Code NOT IN (2)
					and (ISNULL(STL.SurveyTypeLink_IsDel, 1) = 1 or EUDDData.EvnUslugaDispDop_id is not null ) -- только если сохранено согласие
					and EUDDData.Diag_Code not like 'Z%'

			union

				select          --диспучет
					convert(varchar(10), PD.PersonDisp_begDate, 104) as Diag_date,
					D.Diag_Name,
					D.Diag_Code
				from v_PersonDisp PD (nolock)
					left join v_Diag D (nolock) on D.Diag_id = PD.Diag_id
				where PD.Person_id = :Person_id
					and PD.PersonDisp_endDate is null

			order by Diag_date

		";
		*/
		$query = "
			--Ранее известные и впервые выявленные заболевания
			select
				convert(varchar(10), EDDD.EvnDiagDopDisp_setDate, 104) as Diag_date,
				D.Diag_Name,
				D.Diag_Code
			from
				v_EvnDiagDopDisp EDDD (nolock)
				left join v_DiagSetClass DSC (nolock) on DSC.DiagSetClass_id = EDDD.DiagSetClass_id
				left join v_Diag D (nolock) on D.Diag_id = EDDD.Diag_id
			where
				EDDD.EvnDiagDopDisp_pid = :EvnPLDispDop13_id
				AND D.Diag_Code not like 'Z%'
			union
			--Подозрение на наличие стенокардии
			select
				null as Diag_date,
				'Подозрение на наличие стенокардии напряжения' as Diag_Name,
				null as Diag_Code
			from v_EvnPLDispDop13 EPLDD with (nolock)
			where EPLDD.EvnPLDispDop13_id = :EvnPLDispDop13_id
			and EPLDD.EvnPLDispDop13_IsStenocard=2
			union
			--Подозрение на наличие туберкулеза
			select
				null as Diag_date,
				'Подозрение на наличие туберкулеза, хронического заболевания легких или новообразования легких' as Diag_Name,
				null as Diag_Code
			from v_EvnPLDispDop13 EPLDD with (nolock)
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
			$join = 'left join v_UslugaComplex UC (nolock) on UC.UslugaComplex_id = EUDD.UslugaComplex_id';
			$and = "and UC.UslugaComplex_Name not like '%мочи%'";
		}

		$query_riskrate_value = "
		select top 1
				COALESCE(CAST(R.Rate_ValueInt as varchar),CAST(R.Rate_ValueStr as varchar),CAST(R.Rate_ValueFloat as varchar),'') as rate_value
				from v_EvnUslugaDispDop EUDD (nolock)
				left join v_EvnUslugaRate EUR (nolock) on EUR.EvnUsluga_id = EUDD.EvnUslugaDispDop_id
				{$join}
				left join v_Rate R (nolock) on R.Rate_id = EUR.Rate_id
				left join v_RateType RT (nolock) on RT.RateType_id = R.RateType_id
				where EUDD.EvnUslugaDispDop_rid = :EvnPLDispDop13_id
				and RT.RateType_SysNick = :RateType_SysNick
				and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
				{$and}
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
			select top 5
				EPLDD.EvnPLDispDop13_id,
				convert(varchar(10), EPLDD.EvnPLDispDop13_setDate, 104) as dd_date,
				case when EPLDD.EvnPLDispDop13_IsSmoking = 2 then 'Да' else 'Нет' end as IsSmoking,
				case when EPLDD.EvnPLDispDop13_IsRiskAlco = 2 then 'Да' else 'Нет' end as IsRiskAlco,
				case when EPLDD.EvnPLDispDop13_IsLowActiv = 2 then 'Да' else 'Нет' end as IsLowActiv,
				case when EPLDD.EvnPLDispDop13_IsIrrational = 2 then 'Да' else 'Нет' end as IsIrrational,
				ISNULL(HK.HealthKind_Name,'') as HealthKind_Name,
				ISNULL(EPLDD.EvnPLDispDop13_SumRick,'') as EvnPLDispDop13_SumRick,
				ISNULL(RT.RiskType_Name,'') as RiskType_Name

			from v_EvnPLDispDop13 EPLDD
			left join HealthKind HK (nolock) on HK.HealthKind_id = EPLDD.HealthKind_id
			left join RiskType RT (nolock) on RT.RiskType_id=EPLDD.RiskType_id
			where EPLDD.Person_id = :Person_id
			order by EPLDD.EvnPLDispDop13_setDate desc
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
						select COUNT(DDQ.DopDispQuestion_IsTrue) as DDQ_Count
						from v_DopDispQuestion DDQ (nolock)
						left join v_QuestionType QT (nolock) on QT.QuestionType_id = DDQ.QuestionType_id
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
							st.SurveyType_Code,
							(ISNULL(ps.PostMed_Name,'') + ' ' + ISNULL(msf.Person_Fio,'')) as Med_Personal
						from v_EvnUslugaDispDop EUDD (nolock)
							left join v_EvnVizitDispDop evdd (nolock) on eudd.EvnUslugaDispDop_pid = evdd.EvnVizitDispDop_id
							left join v_MedStaffFact msf on msf.MedStaffFact_id = EUDD.MedStaffFact_id
							left join v_PostMed ps with (nolock) on ps.PostMed_id=msf.Post_id
							left join v_DopDispInfoConsent ddic (nolock) on evdd.DopDispInfoConsent_id = ddic.DopDispInfoConsent_id
							left join v_SurveyTypeLink stl (nolock) on stl.SurveyTypeLink_id = ddic.SurveyTypeLink_id
							left join v_SurveyType st (nolock) on st.SurveyType_id = stl.SurveyType_id
						where
							EUDD.EvnUslugaDispDop_rid = :EvnPLDispDop13_id
							and st.SurveyType_Code = '19'
							and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
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
							D.Diag_Code,
							D.Diag_Name,
							HT.HeredityType_id
						from
							v_HeredityDiag HD (nolock)
							left join v_Diag D (nolock) on D.Diag_id = HD.Diag_id
							left join v_HeredityType HT (nolock) on HT.HeredityType_id = HD.HeredityType_id
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
			select top 5 EPLDD.EvnPLDispDop13_id,
				convert(varchar(10), EPLDD.EvnPLDispDop13_setDate, 104) as dd_date,
				systolic_blood_pressure.systolic_blood_pressure,
				diastolic_blood_pressure.diastolic_blood_pressure,
				CONVERT(varchar(3),systolic_blood_pressure.systolic_blood_pressure) + '/' + CONVERT(varchar(3),diastolic_blood_pressure.diastolic_blood_pressure) as person_pressure,
				--case when (CAST(systolic_blood_pressure.systolic_blood_pressure as numeric) > 140 or cast(diastolic_blood_pressure.diastolic_blood_pressure as numeric) > 90) then 'Да' else 'Нет' end as risk_high_pressure,
				person_weight.person_weight,
				person_height.person_height,
				body_mass_index.body_mass_index,
				--case when CAST(body_mass_index.body_mass_index as numeric) >= 25 then 'Да' else 'Нет' end as risk_overweight,
				glucose.glucose,
				--case when cast(glucose.glucose as numeric) > 6 then 'Да' else 'Нет' end as risk_gluk,
				total_cholesterol.total_cholesterol,
				--case when cast(total_cholesterol.total_cholesterol as numeric) > 5 then 'Да' else 'Нет' end as risk_dyslipidemia,
				case when EPLDD.EvnPLDispDop13_IsSmoking = 2 then 'Да' else 'Нет' end as IsSmoking,
				case when EPLDD.EvnPLDispDop13_IsRiskAlco = 2 then 'Да' else 'Нет' end as IsRiskAlco,
				case when EPLDD.EvnPLDispDop13_IsLowActiv = 2 then 'Да' else 'Нет' end as IsLowActiv,
				case when EPLDD.EvnPLDispDop13_IsIrrational = 2 then 'Да' else 'Нет' end as IsIrrational,
				case when DDQ_Count.DDQ_Count > 1 then 'Да' else 'Нет' end as risk_narco,
				ISNULL(CONVERT(varchar(10), summ_risk.EvnPLDispDop13_SumRick),'') + '; ' + ISNULL(summ_risk.RiskType_Name,'') as summ_risk
				 ,m_personal.Med_Personal
				,CAST(m_personal.SurveyType_Code as varchar) + '-' + m_personal.Med_Personal as dd_medpersonal
				,ISNULL(HK.HealthKind_Name,'') as HealthKind_Name
			from v_EvnPLDispDop13 EPLDD_F (nolock)
			left join v_EvnPLDispDop13 EPLDD (nolock) on EPLDD.Person_id = EPLDD_F.Person_id
			left join HealthKind HK (nolock) on HK.HealthKind_id = EPLDD_F.HealthKind_id
			outer apply (
				select --CAST(COALESCE(R.Rate_ValueInt,R.Rate_ValueStr,R.Rate_ValueFloat,'') as NUMERIC(10)) as systolic_blood_pressure
				COALESCE(CAST(R.Rate_ValueInt as varchar),CAST(R.Rate_ValueStr as varchar),CAST(R.Rate_ValueFloat as varchar),'') as systolic_blood_pressure
				from v_EvnUslugaDispDop EUDD (nolock)
				left join v_EvnUslugaRate EUR (nolock) on EUR.EvnUsluga_id = EUDD.EvnUslugaDispDop_id
				left join v_Rate R (nolock) on R.Rate_id = EUR.Rate_id
				left join v_RateType RT (nolock) on RT.RateType_id = R.RateType_id
				where EUDD.EvnUslugaDispDop_rid = EPLDD.EvnPLDispDop13_id
				and RT.RateType_SysNick = 'systolic_blood_pressure'
				and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
			) systolic_blood_pressure
			outer apply (
				select --CAST(COALESCE(R.Rate_ValueInt,R.Rate_ValueStr,R.Rate_ValueFloat,'') as NUMERIC(10)) as diastolic_blood_pressure
				COALESCE(CAST(R.Rate_ValueInt as varchar),CAST(R.Rate_ValueStr as varchar),CAST(R.Rate_ValueFloat as varchar),'') as diastolic_blood_pressure
				from v_EvnUslugaDispDop EUDD (nolock)
				left join v_EvnUslugaRate EUR (nolock) on EUR.EvnUsluga_id = EUDD.EvnUslugaDispDop_id
				left join v_Rate R (nolock) on R.Rate_id = EUR.Rate_id
				left join v_RateType RT (nolock) on RT.RateType_id = R.RateType_id
				where EUDD.EvnUslugaDispDop_rid = EPLDD.EvnPLDispDop13_id
				and RT.RateType_SysNick = 'diastolic_blood_pressure'
				and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
			) diastolic_blood_pressure
			outer apply (
				select --CAST(COALESCE(R.Rate_ValueInt,R.Rate_ValueStr,R.Rate_ValueFloat,'') as NUMERIC(10,2)) as person_weight
				COALESCE(CAST(R.Rate_ValueInt as varchar),CAST(R.Rate_ValueStr as varchar),CAST(R.Rate_ValueFloat as varchar),'') as person_weight
				from v_EvnUslugaDispDop EUDD (nolock)
				left join v_EvnUslugaRate EUR (nolock) on EUR.EvnUsluga_id = EUDD.EvnUslugaDispDop_id
				left join v_Rate R (nolock) on R.Rate_id = EUR.Rate_id
				left join v_RateType RT (nolock) on RT.RateType_id = R.RateType_id
				where EUDD.EvnUslugaDispDop_rid = EPLDD.EvnPLDispDop13_id
				and RT.RateType_SysNick = 'person_weight'
				and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
			) person_weight
			outer apply (
				select --CAST(COALESCE(R.Rate_ValueInt,R.Rate_ValueStr,R.Rate_ValueFloat,'') as NUMERIC(10,2)) as person_height
				COALESCE(CAST(R.Rate_ValueInt as varchar),CAST(R.Rate_ValueStr as varchar),CAST(R.Rate_ValueFloat as varchar),'') as person_height
				from v_EvnUslugaDispDop EUDD (nolock)
				left join v_EvnUslugaRate EUR (nolock) on EUR.EvnUsluga_id = EUDD.EvnUslugaDispDop_id
				left join v_Rate R (nolock) on R.Rate_id = EUR.Rate_id
				left join v_RateType RT (nolock) on RT.RateType_id = R.RateType_id
				where EUDD.EvnUslugaDispDop_rid = EPLDD.EvnPLDispDop13_id
				and RT.RateType_SysNick = 'person_height'
				and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
			) person_height
			outer apply (
				select --CAST(COALESCE(R.Rate_ValueInt,R.Rate_ValueStr,R.Rate_ValueFloat,'') as NUMERIC(10,2)) as body_mass_index
				COALESCE(CAST(R.Rate_ValueInt as varchar),CAST(R.Rate_ValueStr as varchar),CAST(R.Rate_ValueFloat as varchar),'') as body_mass_index
				from v_EvnUslugaDispDop EUDD (nolock)
				left join v_EvnUslugaRate EUR (nolock) on EUR.EvnUsluga_id = EUDD.EvnUslugaDispDop_id
				left join v_Rate R (nolock) on R.Rate_id = EUR.Rate_id
				left join v_RateType RT (nolock) on RT.RateType_id = R.RateType_id
				where EUDD.EvnUslugaDispDop_rid = EPLDD.EvnPLDispDop13_id
				and RT.RateType_SysNick = 'body_mass_index'
				and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
			) body_mass_index
			outer apply (
				select-- CAST(COALESCE(R.Rate_ValueInt,R.Rate_ValueStr,R.Rate_ValueFloat,'') as NUMERIC(10,2)) as glucose
				COALESCE(CAST(R.Rate_ValueInt as varchar),CAST(R.Rate_ValueStr as varchar),CAST(R.Rate_ValueFloat as varchar),'') as glucose
				from v_EvnUslugaDispDop EUDD (nolock)
				left join v_UslugaComplex UC (nolock) on UC.UslugaComplex_id = EUDD.UslugaComplex_id
				left join v_EvnUslugaRate EUR (nolock) on EUR.EvnUsluga_id = EUDD.EvnUslugaDispDop_id
				left join v_Rate R (nolock) on R.Rate_id = EUR.Rate_id
				left join v_RateType RT (nolock) on RT.RateType_id = R.RateType_id
				where EUDD.EvnUslugaDispDop_rid = EPLDD.EvnPLDispDop13_id
				and RT.RateType_SysNick = 'glucose'
				and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
				and UC.UslugaComplex_Name not like '%мочи%'
			) glucose
			outer apply (
				select --CAST(COALESCE(R.Rate_ValueInt,R.Rate_ValueStr,R.Rate_ValueFloat,'') as NUMERIC(10,2)) as total_cholesterol
				COALESCE(CAST(R.Rate_ValueInt as varchar),CAST(R.Rate_ValueStr as varchar),CAST(R.Rate_ValueFloat as varchar),'') as total_cholesterol
				from v_EvnUslugaDispDop EUDD (nolock)
				left join v_EvnUslugaRate EUR (nolock) on EUR.EvnUsluga_id = EUDD.EvnUslugaDispDop_id
				left join v_Rate R (nolock) on R.Rate_id = EUR.Rate_id
				left join v_RateType RT (nolock) on RT.RateType_id = R.RateType_id
				where EUDD.EvnUslugaDispDop_rid = EPLDD.EvnPLDispDop13_id
				and RT.RateType_SysNick = 'total_cholesterol'
				and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
			) total_cholesterol
			outer apply(
			select COUNT(DDQ.DopDispQuestion_IsTrue) as DDQ_Count
						from v_DopDispQuestion DDQ (nolock)
						left join v_QuestionType QT (nolock) on QT.QuestionType_id = DDQ.QuestionType_id
						where EvnPLDisp_id = EPLDD.EvnPLDispDop13_id
						and QT.QuestionType_Code in (40,41,42,43,44)
						and DDQ.DopDispQuestion_IsTrue = 2
			) DDQ_Count
			outer apply(
				select
				CAST(EPLDD2.EvnPLDispDop13_SumRick as numeric(10)) as EvnPLDispDop13_SumRick,
				RT.RiskType_Name
				from EvnPLDispDop13 EPLDD2 (nolock)
				left join RiskType RT (nolock) on RT.RiskType_id=EPLDD.RiskType_id
				where EPLDD2.EvnPLDispDop13_id = EPLDD.EvnPLDispDop13_id
			) summ_risk
			outer apply(
			select	distinct
							st.SurveyType_Code,
							(ps.PostMed_Name + ' ' + msf.Person_Fio) as Med_Personal
						from v_EvnUslugaDispDop EUDD (nolock)
							left join v_EvnVizitDispDop evdd (nolock) on eudd.EvnUslugaDispDop_pid = evdd.EvnVizitDispDop_id
							left join v_MedStaffFact msf on msf.MedStaffFact_id = EUDD.MedStaffFact_id
							left join v_PostMed ps with (nolock) on ps.PostMed_id=msf.Post_id
							left join v_DopDispInfoConsent ddic (nolock) on evdd.DopDispInfoConsent_id = ddic.DopDispInfoConsent_id
							left join v_SurveyTypeLink stl (nolock) on stl.SurveyTypeLink_id = ddic.SurveyTypeLink_id
							left join v_SurveyType st (nolock) on st.SurveyType_id = stl.SurveyType_id
						where
							EUDD.EvnUslugaDispDop_rid = EPLDD.EvnPLDispDop13_id
							and st.SurveyType_Code = '19'
							and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
			) m_personal
			where EPLDD_F.EvnPLDispDop13_id = :EvnPLDispDop13_id-- 1261360--1264267
			and EPLDD.EvnPLDispDop13_setDate <= EPLDD_F.EvnPLDispDop13_setDate
			and (person_weight.person_weight is not null or person_height.person_height is not null or glucose.glucose is not null)
			order by EPLDD.EvnPLDispDop13_setDate desc
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
							D.Diag_Code,
							D.Diag_Name,
							HT.HeredityType_id
						from
							v_HeredityDiag HD (nolock)
							left join v_Diag D (nolock) on D.Diag_id = HD.Diag_id
							left join v_HeredityType HT (nolock) on HT.HeredityType_id = HD.HeredityType_id
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
			select top 1
				EUDD.EvnUslugaDispDop_pid as EvnVizitDispDop_id
			from
				v_DopDispInfoConsent DDIC with (nolock)
				inner join v_SurveyTypeLink STL with (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
				inner join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
				inner join v_EvnUslugaDispDop EUDD with (nolock) on EUDD.UslugaComplex_id = STL.UslugaComplex_id
			where
				DDIC.DopDispInfoConsent_id = :DopDispInfoConsent_id
				and EUDD.EvnUslugaDispDop_rid = :EvnPLDispDop13_id
				and ISNULL(DDIC.DopDispInfoConsent_IsAgree, 1) = 1
				and ISNULL(DDIC.DopDispInfoConsent_IsEarlier, 1) = 1
				and ST.SurveyType_Code NOT IN (1,48)
				and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
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
			SELECT top 1
				PS.Sex_id,
				dbo.Age2(PS.Person_BirthDay, :onDate) as Person_Age,
				dbo.Age2(PS.Person_BirthDay, :YearEndDate) as Person_AgeOnYearEnd
			FROM v_PersonState PS (nolock)
			WHERE PS.Person_id = :Person_id
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
				select top 1 PersonPrivilegeWOW_id as id
				from v_PersonPrivilegeWOW (nolock)
				where Person_id = :Person_id
			";
		}

		if ( count($personPrivilegeCodeList) > 0 ) {
			$queryArray[] = "
				select top 1 pp.PersonPrivilege_id as id
				from v_PersonPrivilege pp (nolock)
					inner join v_PrivilegeType pt (nolock) on pt.PrivilegeType_id = pp.PrivilegeType_id
				where pt.PrivilegeType_Code in ('" . implode("','", $personPrivilegeCodeList) . "')
					and pp.Person_id = :Person_id
					and pp.PersonPrivilege_begDate <= :YearEndDate
					and (pp.PersonPrivilege_endDate >= :YearEndDate or pp.PersonPrivilege_endDate is null)
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
			SELECT TOP 1 D.Diag_Code
			FROM v_Diag D (nolock) 
			WHERE D.Diag_id = :Diag_id
		";

		$code = $this->getFirstResultFromQuery($sql, $params);
		if(empty($code)) return false;
		$code = substr($code, 0,3);

		$params['Diag_Code'] = $code;
		$filter = "";
		$join = "";
		if(!empty($params['EvnUslugaDispDop_id'])) {//если диагноз из осмотра/исследования, то уцепиться можем только за EvnUslugaDispDop_id/Diag_id
			$filter.=" and isnull(EvnUslugaDispDop_id, '') != :EvnUslugaDispDop_id";
			$join .= " left join v_EvnUslugaDispDop EU (nolock) on EU.Diag_id = ED3.Diag_id and EU.EvnUslugaDispDop_rid = ED3.EvnDiagDopDisp_rid";
		}

		$sql = "
			SELECT D.Diag_id, case when ED3.Diag_id=:Diag_id then 0 else 1 end as eqsort
			FROM v_EvnDiagDopDisp ED3 (nolock)
				left join v_Diag D (nolock) on D.Diag_id = ED3.Diag_id
				{$join}
			WHERE ED3.EvnDiagDopDisp_rid = :EvnPLDispDop13_id and substring(D.Diag_Code, 1,3) = :Diag_Code {$filter}
			union
			SELECT D.Diag_id, case when EU.Diag_id=:Diag_id then 0 else 1 end as eqsort
			FROM v_EvnUslugaDispDop EU (nolock)
				left join v_Diag D (nolock) on D.Diag_id = EU.Diag_id
			WHERE EU.EvnUslugaDispDop_rid = :EvnPLDispDop13_id and substring(D.Diag_Code, 1,3) = :Diag_Code {$filter}
			order by eqsort
		";

        $diag = $this->getFirstResultFromQuery($sql, $params);

        if(empty($diag) && !empty($data['DeseaseDispType_id']) && $data['DeseaseDispType_id'] == 2 ) {
			$sql = "
				SELECT TOP 1 EPL.Diag_id
				FROM v_EvnPLDispDop13 E13 (nolock) 
					inner join v_EvnPL EPL (nolock) on EPL.Person_id = E13.Person_id
				WHERE E13.EvnPLDispDop13_id = :EvnPLDispDop13_id and EPL.Diag_id = :Diag_id
				union
				SELECT TOP 1 EVPL.Diag_id
				FROM v_EvnPLDispDop13 E13 (nolock) 
					inner join v_EvnVizitPL EVPL (nolock) on EVPL.Person_id = E13.Person_id
				WHERE E13.EvnPLDispDop13_id = :EvnPLDispDop13_id and EVPL.Diag_id = :Diag_id
				union
				SELECT TOP 1 PD.Diag_id
				FROM v_EvnPLDispDop13 E13 (nolock)  
					inner join v_PersonDisp PD (nolock) on PD.Person_id = E13.Person_id
				where E13.EvnPLDispDop13_id = :EvnPLDispDop13_id and PD.Diag_id = :Diag_id
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
		{
			$params = array(
				'LpuSection_id' => $data['userLpuSection_id'],
			);
			$sql = "
			Select
				user_ls.Lpu_id,
				user_lu.LpuBuilding_id,
				user_ls.LpuUnit_id
			from v_LpuSection user_ls with (nolock)
			inner join v_LpuUnit user_lu with (nolock) on user_lu.LpuUnit_id = user_ls.LpuUnit_id
			where user_ls.LpuSection_id = :LpuSection_id
		";
		
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
					isnull(EP.EvnPrescr_IsExec,1) as EvnPrescr_IsExec
				FROM v_EvnPrescr EP WITH (NOLOCK)
					LEFT JOIN EvnPrescrLabDiag EPLD WITH (NOLOCK)
						ON EPLD.EvnPrescrLabDiag_id = EP.EvnPrescr_id
						   AND EP.PrescriptionType_id = 11
					LEFT JOIN EvnPrescrFuncDiagUsluga EPFDU WITH (NOLOCK)
						ON EPFDU.EvnPrescrFuncDiag_id = EP.EvnPrescr_id
						   AND EP.PrescriptionType_id = 12
					LEFT JOIN dbo.EvnPrescrConsUsluga EPCO WITH (NOLOCK)
						ON EPCO.EvnPrescrConsUsluga_id = EP.EvnPrescr_id
						   AND EP.PrescriptionType_id = 13
					LEFT JOIN v_PrescriptionType pt WITH (NOLOCK)
						ON pt.PrescriptionType_id = EP.PrescriptionType_id
				WHERE EP.EvnPrescr_pid = :EvnPrescr_pid 
				--'730023881307390'
					  AND EP.PrescriptionType_id IN ( 11, 12, 13 )
					  AND EP.PrescriptionStatusType_id != 3
			)
			
			SELECT 
				uc.UslugaComplex_id,
				uc.UslugaComplex_Code,
				uc.UslugaComplex_Name,
				EP.EvnPrescr_id,
				EP.EvnPrescr_IsExec,
				EvnStatus.EvnStatus_SysNick,
				COALESCE(EP.PrescriptionType_Code,attr.PrescriptionType_Code) AS PrescriptionType_Code,
				case	
					when COALESCE(EP.PrescriptionType_Code,attr.PrescriptionType_Code) = 11 then 'EvnPrescrLabDiag'
					when COALESCE(EP.PrescriptionType_Code,attr.PrescriptionType_Code) = 12 then 'EvnPrescrFuncDiag'
					when COALESCE(EP.PrescriptionType_Code,attr.PrescriptionType_Code) = 13 then 'EvnPrescrConsUsluga'
					else ''
				end as object,
				case
						when TTMS.TimetableMedService_id is not null then isnull(convert(varchar(10), TTMS.TimetableMedService_begTime, 104),'')+' '+isnull(convert(varchar(5), TTMS.TimetableMedService_begTime, 108),'')
						when TTR.TimetableResource_id is not null then isnull(convert(varchar(10), TTR.TimetableResource_begTime, 104),'')+' '+isnull(convert(varchar(5), TTR.TimetableResource_begTime, 108),'')
						when TTG.TimetableGraf_id is not null then isnull(convert(varchar(10), TTG.TimetableGraf_begTime, 104),'')+' '+isnull(convert(varchar(5), TTG.TimetableGraf_begTime, 108),'')
						when EQ.EvnQueue_id is not null then 'В очереди с '+ isnull(convert(varchar(10), EQ.EvnQueue_setDate, 104),'')
					else '' end as RecDate,
				case
					when TTMS.TimetableMedService_id is not null then isnull(MS.MedService_Name,'')
					when TTR.TimetableResource_id is not null then isnull(R.Resource_Name,'') +' / '+ isnull(MS.MedService_Name,'')
					when TTG.TimetableGraf_id is not null then isnull(LS.LpuSection_Name,'') +' / '+ isnull(LS.LpuSectionProfile_Name,'')
					when EQ.EvnQueue_id is not null then
						case
							when MS.MedService_id is not null and  MS.LpuSection_id is null and MS.LpuUnit_id is null
							then isnull(MS.MedService_Name,'')
							when MS.MedService_id is not null and  MS.LpuSection_id is null and MS.LpuUnit_id is not null
							then isnull(MS.MedService_Name,'') +' / '+ isnull(LU.LpuUnit_Name,'')
							when MS.MedService_id is not null and  MS.LpuSection_id is not null and MS.LpuUnit_id is not null
							then isnull(MS.MedService_Name,'') +' / '+ isnull(LSPD.LpuSectionProfile_Name,'') +' / '+ isnull(LU.LpuUnit_Name,'')
							else isnull(LSPD.LpuSectionProfile_Name,'') +' / '+ isnull(LU.LpuUnit_Name,'')
						end
				else '' end as RecTo,
				ED.*,
				TTG.TimetableGraf_id,
				MS.MedService_Nick,
				EUP.EvnUslugaPar_id
			FROM v_UslugaComplex uc with (nolock)
				OUTER APPLY (
					SELECT TOP 1
						   *
					FROM v_UslugaComplex uc11 WITH (NOLOCK)
					WHERE uc.UslugaComplex_2011id = uc11.UslugaComplex_id
				) uc11
				OUTER APPLY (
					SELECT *
					FROM EvnPrescr ep with (nolock)
					WHERE ep.UslugaComplex_id = uc.UslugaComplex_id
				) AS EP
				outer apply (
					Select top 1 
						ED.EvnDirection_id
						,ED.EvnQueue_id
						,ED.EvnDirection_Num
						,ED.MedService_id
						,ED.Resource_id
						,ED.LpuSection_did
						,ED.LpuUnit_did
						,ED.LpuSectionProfile_id
						,ED.EvnStatus_id
					from v_EvnPrescrDirection epd with (nolock)
					inner join v_EvnDirection_all ED with (nolock) on epd.EvnDirection_id = ED.EvnDirection_id
					where EP.EvnPrescr_id is not null 
						AND epd.EvnPrescr_id = EP.EvnPrescr_id
						AND ISNULL(ED.EvnStatus_id, 16) not in (12,13)
					order by 
						case when ISNULL(ED.EvnStatus_id, 16) in (12,13) then 2 else 1 end /* первым неотмененное/неотклоненное направление */
						,epd.EvnPrescrDirection_insDT desc
				) as ED
				-- заказанная услуга для параклиники
				outer apply (
					Select top 1 EvnUslugaPar_id FROM v_EvnUslugaPar with (nolock) where EvnDirection_id = ED.EvnDirection_id
				) EUP
				--left join v_EvnUslugaPar EUP with (NOLOCK) on EUP.EvnDirection_id = ED.EvnDirection_id
				-- службы и параклиника
				outer apply (
					Select top 1 TimetableMedService_id, TimetableMedService_begTime from v_TimetableMedService_lite TTMS with (nolock) where TTMS.EvnDirection_id = ED.EvnDirection_id
				) TTMS
				-- очередь
				outer apply (
					Select top 1 EQ.EvnQueue_id, EQ.LpuUnit_did, EQ.LpuSectionProfile_did, Lpu_id, EQ.EvnQueue_setDate, EQ.EvnQueue_failDT, EQ.QueueFailCause_id 
					from v_EvnQueue EQ with (nolock)
					where EQ.EvnDirection_id = ED.EvnDirection_id
					and EQ.EvnQueue_recDT is null
					-- это костыль для того, чтобы направления у которых есть связь по EQ.EvnQueue_id = ED.EvnQueue_id, но нет реальной записи в TimetableMedService можно было отменить
					union
					Select top 1 EQ.EvnQueue_id, EQ.LpuUnit_did, EQ.LpuSectionProfile_did, Lpu_id, EQ.EvnQueue_setDate, EQ.EvnQueue_failDT, EQ.QueueFailCause_id 
					from v_EvnQueue EQ with (nolock)
					where (EQ.EvnQueue_id = ED.EvnQueue_id)
					and (EQ.EvnQueue_recDT is null or TTMS.TimetableMedService_id is null)
					and EQ.EvnQueue_failDT is null
				) EQ
				-- ресурсы
				outer apply (
						Select top 1 TimetableResource_id, TimetableResource_begTime from v_TimetableResource_lite TTR with (nolock) where TTR.EvnDirection_id = ED.EvnDirection_id
				) TTR
				-- бирки к врачу
				outer apply (
						Select top 1 TimetableGraf_id, TimetableGraf_begTime from dbo.v_TimeTableGraf_lite TTG with (nolock) where TTG.EvnDirection_id = ED.EvnDirection_id
				) TTG
				-- сам ресрс
				left join v_Resource R with (nolock) on R.Resource_id = ED.Resource_id
				left join v_MedService MS with (nolock) on MS.MedService_id = ED.MedService_id -- ED.MedService_did должно быть
				-- отделение для полки и стаца и для очереди
				left join v_LpuSection LS with (nolock) on LS.LpuSection_id = coalesce(ED.LpuSection_did, MS.LpuSection_id)
				-- подразделение для очереди и служб
				left join v_LpuUnit LU with (nolock) on coalesce(ED.LpuUnit_did,EQ.LpuUnit_did,MS.LpuUnit_id) = LU.LpuUnit_id -- todo: в ED.LpuUnit_did пусто, чего не должно быть
				-- профиль для очереди
				left join v_LpuSectionProfile LSPD with (nolock) on coalesce(ED.LpuSectionProfile_id,EQ.LpuSectionProfile_did,LS.LpuSectionProfile_id) = LSPD.LpuSectionProfile_id -- todo: тут на примере оказалось что почему то ED.LpuSectionProfile_id != EQ.LpuSectionProfile_did, чего не должно быть
				outer apply(
						select top 1 ESH.EvnStatus_id, ESH.EvnStatusCause_id, ESH.pmUser_insID, ESH.EvnStatusHistory_Cause
						from EvnStatusHistory ESH with(nolock)
						where ESH.Evn_id = ED.EvnDirection_id
							and ESH.EvnStatus_id = ED.EvnStatus_id
						order by ESH.EvnStatusHistory_begDate desc
					) ESH
				left join EvnStatus with(nolock) on EvnStatus.EvnStatus_id = ESH.EvnStatus_id
				OUTER APPLY (
					SELECT TOP 1
					
					case	
						when t2.UslugaComplexAttributeType_SysNick = 'lab' then 11
						when t2.UslugaComplexAttributeType_SysNick = 'func' then 12
						when t2.UslugaComplexAttributeType_SysNick = 'consult' then 13
						else ''
					end as PrescriptionType_Code
					
					
					/*CASE WHEN t2.UslugaComplexAttributeType_SysNick = 'lab' THEN 11
					ELSE CASE WHEN t2.UslugaComplexAttributeType_SysNick = 'func' THEN 12 END
					END AS PrescriptionType_Code*/
						   
					FROM v_UslugaComplexAttribute t1 WITH (NOLOCK)
						INNER JOIN v_UslugaComplexAttributeType t2 WITH (NOLOCK)
							ON t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
					WHERE t1.UslugaComplex_id = ISNULL(uc11.UslugaComplex_id, uc.UslugaComplex_id)
						  AND t2.UslugaComplexAttributeType_SysNick IN ( 'lab','func','consult')
				) AS attr
			WHERE uc.UslugaComplex_id IN (" . implode(',', $UslugaComplexList) . ")
			
			--uc.UslugaComplex_id IN ( 4634872, 4426005, 206896, 201667, 200884, 200886, 200885 );
		";
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
			select DRFC.DispRiskFactorCons_id,
				DRFC.DispRiskFactorCons_Name,
				DPC.DispProfCons_Text,
				DC.DispCons_id,
				DC.DispCons_Text
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
			select RT.RateType_id, RT.RateType_Name, LR.LabelRate_Max, LR.LabelRate_Min
			from dbo.RateType RT with(nolock)
			left join dbo.LabelRate LR with(nolock) on LR.RateType_id = RT.RateType_id
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
			select RT.RateType_id, RT.RateType_Name, LR.LabelRate_Max, LR.LabelRate_Min
			from dbo.RateType RT with(nolock)
			left join dbo.LabelRate LR with(nolock) on LR.RateType_id = RT.RateType_id
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
				RiskFactorType_id,
				RiskFactorType_Name
			from
				v_RiskFactorType with (nolock)
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
				drf.DispRiskFactor_id,
				drf.RiskFactorType_id,
				rft.RiskFactorType_Name,
				convert(varchar(10),DispRiskFactor_insDT,104) as DispRiskFactor_insDT
			FROM v_DispRiskFactor drf with (nolock)
			LEFT JOIN v_RiskFactorType rft with (nolock) ON rft.RiskFactorType_id=drf.RiskFactorType_id
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
			DECLARE
				@DispRiskFactor_id BIGINT,
				@ErrCode int,
				@ErrMessage varchar(4000);

			SET @DispRiskFactor_id = null;

			EXEC dbo.p_DispRiskFactor_ins
				@DispRiskFactor_id = @DispRiskFactor_id OUTPUT,
				@EvnPLDisp_id = :EvnPLDisp_id,							
				@RiskFactorType_id = :RiskFactorType_id,					
				@pmUser_id = :pmUser_id,								
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			SELECT
				@DispRiskFactor_id as DispRiskFactor_id,
				@ErrCode as Error_Code,
				@ErrMessage as Error_Msg;
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
				drf.DispRiskFactor_id,
				drf.RiskFactorType_id,
				rft.RiskFactorType_Name, 
				convert(varchar(10),DispRiskFactor_insDT,104) as DispRiskFactor_insDT
			from 
				v_DispRiskFactor drf with (nolock)
				left join v_RiskFactorType rft with (nolock) on rft.RiskFactorType_id=drf.RiskFactorType_id   
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
	 *  Подозрения в меню на добавление
	 * //yl:кроме уже добавленных в обоих этапах
	 */
	function loadEvnPLDispDop13DispDeseaseSuspType($data) {
		$stageOne = (!empty($data["EvnPLDispDop13_fid"])?"or(EvnPLDisp_id=:EvnPLDispDop13_fid)":"");
		$sql = "
			select
				DispDeseaseSuspType_id,
				DispDeseaseSuspType_Name
			from
				v_DispDeseaseSuspType with (nolock)
			where
				DispDeseaseSuspType_id not in(
					select DispDeseaseSuspDict_id from v_DispDeseaseSusp with (nolock) where (EvnPLDisp_id=:EvnPLDisp_id){$stageOne}
				)
			order by
				DispDeseaseSuspType_Name
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
	 *  Подозрения и Заболевания в одном гриде
	 * //yl:по этапам
	 */
	function loadEvnPLDispDop13Desease($data) {
		$stageOne_Susp = (!empty($data["EvnPLDispDop13_fid"])?"or(EvnPLDisp_id=:EvnPLDispDop13_fid)":"");
		$stageOne_Diag = (!empty($data["EvnPLDispDop13_fid"])?"or(EvnDiagDopDisp_pid=:EvnPLDispDop13_fid)":"");
		$sql = "
			SELECT --Подозрения
				DispDeseaseSusp_id,
				DispDeseaseSuspDict_id as DispDeseaseSuspType_id,
				DispDeseaseSuspType_Name,

				null as Lpu_Name,
				null as EvnDiagDopDisp_id,
				null as DiagSetClass_id,
				null as DiagSetClass_Name,
				null as Diag_Code,
				null as Diag_Name,

				convert(varchar(10),DispDeseaseSusp_insDT,104) as Date_insDT
			FROM v_DispDeseaseSusp with (nolock)
			LEFT JOIN v_DispDeseaseSuspType with (nolock) ON DispDeseaseSuspType_id=DispDeseaseSuspDict_id
			WHERE (EvnPLDisp_id=:EvnPLDispDop13_id){$stageOne_Susp}

			union 
						
			select --Диагнозы
				null as DispDeseaseSusp_id,
				null as DispDeseaseSuspType_id,
				null as DispDeseaseSuspType_Name,

				Lpu_Name,
				EvnDiagDopDisp_id,
				dsc.DiagSetClass_id,
				dsc.DiagSetClass_Name,
				d.Diag_Code,
				d.Diag_Name,

				convert(varchar(10),EvnDiagDopDisp_insDT,104) as Date_insDT
			from 
				v_EvnDiagDopDisp eddd with (nolock)
				left join v_Diag d with (nolock) on d.Diag_id=eddd.Diag_id   
				left join v_DiagSetClass dsc with (nolock) on dsc.DiagSetClass_id=eddd.DiagSetClass_id
				left join v_Lpu with (nolock) on v_Lpu.Lpu_id=eddd.Lpu_id
			where
				(EvnDiagDopDisp_pid = :EvnPLDispDop13_id){$stageOne_Diag}
			
			order by Date_insDT desc
		";//exit($sql);
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
			DECLARE
				@DispDeseaseSusp_id BIGINT,
				@ErrCode int,
				@ErrMessage varchar(4000);

			SET @DispDeseaseSusp_id = null;

			EXEC dbo.p_DispDeseaseSusp_ins
				@DispDeseaseSusp_id = @DispDeseaseSusp_id OUTPUT,
				@EvnPLDisp_id = :EvnPLDisp_id,							
				@DispDeseaseSuspDict_id = :DispDeseaseSuspType_id,					
				@pmUser_id = :pmUser_id,								
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			SELECT
				@DispDeseaseSusp_id as DispDeseaseSusp_id,
				@ErrCode as Error_Code,
				@ErrMessage as Error_Msg;
		";//exit($sql);
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
				DispDeseaseSusp_id,
				DispDeseaseSuspDict_id as DispDeseaseSuspType_id,
				DispDeseaseSuspType_Name, 
				convert(varchar(10),DispDeseaseSusp_insDT,104) as Date_insDT
			from 
				v_DispDeseaseSusp with (nolock)
				left join v_DispDeseaseSuspType with (nolock) on DispDeseaseSuspType_id=DispDeseaseSuspDict_id   
			where
				DispDeseaseSusp_id = :DispDeseaseSusp_id;
			", array(
			"DispDeseaseSusp_id" => $response[0]["DispDeseaseSusp_id"]
		));
	}

	/**
	 *  Удалить подозрение //yl:
	 */
	function delEvnPLDispDop13DispDeseaseSusp($data){
		return $this->queryResult("
			DECLARE
				@ErrCode int,
				@ErrMessage varchar(4000);

			EXEC dbo.p_DispDeseaseSusp_del
				@DispDeseaseSusp_id = :DispDeseaseSusp_id,				   
				@pmUser_id = :pmUser_id,						   
				@IsRemove = 2,						   
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			SELECT @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
			select top 1
				EvnDiagDopDisp_id
			from 
				v_EvnDiagDopDisp with (nolock)
			where
				EvnDiagDopDisp_pid = :EvnPLDispDop13_id
				and
				Diag_id=:Diag_id
		";$result = $this->db->query($sql, array(
			"EvnPLDispDop13_id" => $data["EvnPLDispDop13_id"],
			"Diag_id" => $data["Diag_id"],
		));
		if (!is_object($result)) return false;$response = $result->result("array");
		if(count($response)>0){
			return [["Error_Msg" => "Такой Диагноз уже добавлен"]];
		};

		$sql = "
			DECLARE
				@EvnDiagDopDisp_id	  BIGINT,
				@ErrCode int,
				@ErrMessage varchar(4000);

			SET @EvnDiagDopDisp_id = null;

			EXEC dbo.p_EvnDiagDopDisp_ins
				@EvnDiagDopDisp_id = @EvnDiagDopDisp_id OUTPUT,

				@EvnDiagDopDisp_pid = :EvnPLDispDop13_id,
				@Lpu_id = :Lpu_id,
				@Diag_id = :Diag_id,
				@DiagSetClass_id = 3,--сопутствующий
				@pmUser_id = :pmUser_id,
				@PersonEvn_id = :PersonEvn_id,
				@Server_id = :Server_id,

				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			SELECT
				@EvnDiagDopDisp_id as EvnDiagDopDisp_id,
				@ErrCode as Error_Code,
				@ErrMessage as Error_Msg;
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
				Lpu_Name,
				EvnDiagDopDisp_id,
				dsc.DiagSetClass_id,
				dsc.DiagSetClass_Name,
				d.Diag_Code,
				d.Diag_Name,
				convert(varchar(10),EvnDiagDopDisp_insDT,104) as Date_insDT
			from 
				v_EvnDiagDopDisp eddd with (nolock)
				left join v_Diag d with (nolock) on d.Diag_id=eddd.Diag_id   
				left join v_DiagSetClass dsc with (nolock) on dsc.DiagSetClass_id=eddd.DiagSetClass_id
				left join v_Lpu with (nolock) on v_Lpu.Lpu_id=eddd.Lpu_id
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
			DECLARE
				@ErrCode int,
				@ErrMessage varchar(4000);

			EXEC dbo.p_EvnDiagDopDisp_del
				@EvnDiagDopDisp_id = :EvnDiagDopDisp_id ,				   
				@pmUser_id = :pmUser_id,						   
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			SELECT @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
				select top 1
					EvnDiagDopDisp_id
				from v_DispRiskFactor (nolock)
				where EvnPLDisp_id = :EvnPLDisp_id and RiskFactorType_id = :RiskFactorType_id
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
				DiagSetClass_id,
				DiagSetClass_Name
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
				Lpu_Name,
				EvnDiagDopDisp_id,
				dsc.DiagSetClass_id,
				dsc.DiagSetClass_Name,
				d.Diag_Code,
				d.Diag_Name,
				convert(varchar(10),EvnDiagDopDisp_insDT,104) as Date_insDT
			from 
				v_EvnDiagDopDisp eddd with (nolock)
				left join v_Diag d with (nolock) on d.Diag_id=eddd.Diag_id   
				left join v_DiagSetClass dsc with (nolock) on dsc.DiagSetClass_id=eddd.DiagSetClass_id
				left join v_Lpu with (nolock) on v_Lpu.Lpu_id=eddd.Lpu_id
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

		$sql = "select FormalizedInspection_Result from v_FormalizedInspection with (nolock) where FormalizedInspection_id={$FormalizedInspection_id}";
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
			DECLARE
				@FormalizedInspection_id BIGINT,
				@ErrCode int,
				@ErrMessage varchar(4000);
			EXEC dbo.p_FormalizedInspection_upd
				@FormalizedInspection_id = {$FormalizedInspection_id},
				@EvnUslugaDispDop_id = {$EvnUslugaDispDop_id},								
				@FormalizedInspectionParams_id = {$FormalizedInspectionParams_id},						
				@FormalizedInspection_Result = :GynecologistText,						
				@FormalizedInspection_DirectoryAnswer_id = 0,			
				@FormalizedInspection_PathologySize = 0,				
				@FormalizedInspection_NResult = NULL,					
				@pmUser_id = :pmUser_id,											
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @FormalizedInspection_id as FormalizedInspection_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
			$sql = "select top 1 FormalizedInspection_id
					from v_FormalizedInspection with (nolock) 
					where
						FormalizedInspectionParams_id=:FormalizedInspectionParams_id
						and
						EvnUslugaDispDop_id=:EvnUslugaDispDop_id
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
					DECLARE
						@FormalizedInspection_id BIGINT,
						@ErrCode int,
						@ErrMessage varchar(4000);
					EXEC dbo.p_FormalizedInspection_ins
						@FormalizedInspection_id = @FormalizedInspection_id OUTPUT,
						@EvnUslugaDispDop_id = :EvnUslugaDispDop_id,								
						@FormalizedInspectionParams_id = :FormalizedInspectionParams_id,						
						@FormalizedInspection_Result = :GynecologistText,						
						@FormalizedInspection_DirectoryAnswer_id = 0,			
						@FormalizedInspection_PathologySize = 0,				
						@FormalizedInspection_NResult = NULL,					
						@pmUser_id = :pmUser_id,											
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @FormalizedInspection_id as FormalizedInspection_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
		$sql = "select top 1 FormalizedInspectionParams_id from FormalizedInspectionParams with (nolock) where SurveyType_id=:SurveyType_id";
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
			DECLARE
				@FormalizedInspectionParams_id BIGINT,
				@ErrCode int,
				@ErrMessage varchar(4000);
			EXEC dbo.p_FormalizedInspectionParams_ins
				@FormalizedInspectionParams_id = @FormalizedInspectionParams_id OUTPUT,
				@SurveyType_id = :SurveyType_id,													
				@FormalizedInspectionParams_Name = 'Осмотр',								
				@FormalizedInspectionParams_IsDefault = 0,							
				@FormalizedInspectionParams_Directory = '',							
				@pmUser_id = :pmUser_id,								
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @FormalizedInspectionParams_id as FormalizedInspectionParams_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";//exit(getDebugSql($sql, $data));
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
			select top 1 EvnUslugaDispDop_id
			from v_EvnUslugaDispDop with (nolock) 
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
				select top 1 EvnDirection_id 
				from v_EvnDirection_all (nolock) ed
				inner join v_EvnPLDisp epd (nolock) on ed.EvnDirection_rid = epd.EvnPLDisp_id
				where 
					ed.DirType_id in (3,16) and 
					ed.EvnStatus_id not in (12,13) and 
					epd.EvnPLDisp_id = ?
			', array($data["{$sysNick}_id"]));
			
			if (!$EvnDirection_id) {
				throw new Exception('При подозрении на ЗНО должно быть выписано направление на дообследование с типом «на консультацию» или «на поликлинический прием». Добавьте направление в разделе «Направления на исследования».');
			}
			
			// направлен на обследование
			$EvnDirection_id = $this->getFirstResultFromQuery('
				select top 1 EvnDirection_id 
				from v_EvnDirection_all (nolock) ed
				inner join v_EvnPLDisp epd (nolock) on ed.EvnDirection_rid = epd.EvnPLDisp_id
				where 
					ed.DirType_id = 10 and 
					ed.EvnStatus_id not in (12,13) and 
					epd.EvnPLDisp_id = ?
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
				select top 1 Di.Diag_Name 
				from v_EvnPLDispDop13 EPL (nolock)
				left join v_Diag Di (nolock) on Di.Diag_id = EPL.Diag_spid
				where EPL.EvnPLDispDop13_id = :EvnPLDispDop13_id
			", $data);
			$alertDesease[] = 'Подозрение на ЗНО: '.$diag_zno_name;
		}
		
		$sql = "
			select
				DispDeseaseSuspType_Name
			from
				v_DispDeseaseSusp DDS (nolock)
				left join v_DispDeseaseSuspType DDST (nolock) on DDST.DispDeseaseSuspType_id = DDS.DispDeseaseSuspDict_id
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
		  		select top 1 * 
		  		from v_EvnPLDispDop13 with(nolock) 
		  		where EvnPLDispDop13_id = :EvnPLDispDop13_id
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
				select EvnPLDispDop13_id from v_EvnPLDispDop13 (nolock) where EvnPLDispDop13_fid = :EvnPLDispDop13_fid
			", array(
				'EvnPLDispDop13_fid' => $data['EvnPLDispDop13_id']
			));

			if (!empty($resp[0]['EvnPLDispDop13_id'])) {
				$this->db->trans_rollback();
				return array('Error_Msg' => 'У пациента имеется карта 2 этапа, нельзя сохранить карту 1 этапа без полей "1 этап закончен" и "Направлен на 2 этап диспансеризации"');
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
		
		$setDtField = "@EvnPLDispDop13_consDate";
		$query = "
		    declare
		        @EvnPLDispDop13_id bigint,
				@EvnPLDispDop13_IsRefusal bigint,
				@EvnDirection_aid bigint,
				@EvnPLDispDop13_consDate date,
				@ErrCode int,
				@ErrMessage varchar(4000),
				@curdate datetime;

			set @curdate = dbo.tzGetDate();
			set @EvnPLDispDop13_id = :EvnPLDispDop13_id;

			select top 1
				@EvnPLDispDop13_IsRefusal = EvnPLDispDop13_IsRefusal,
				@EvnDirection_aid = EvnDirection_aid,
				@EvnPLDispDop13_consDate = EvnPLDispDop13_consDT
			from v_EvnPLDispDop13 (nolock)
			where EvnPLDispDop13_id = :EvnPLDispDop13_id;

			exec p_EvnPLDispDop13_upd
				@EvnPLDispDop13_id = @EvnPLDispDop13_id output,
				@MedStaffFact_id = :MedStaffFact_id,
				@EvnPLDispDop13_IsNewOrder = :EvnPLDispDop13_IsNewOrder,
				@EvnPLDispDop13_IndexRep = :EvnPLDispDop13_IndexRep,
				@EvnPLDispDop13_IndexRepInReg = :EvnPLDispDop13_IndexRepInReg,
				@PersonEvn_id = :PersonEvn_id,
				@EvnPLDispDop13_setDT = {$setDtField},
				@EvnPLDispDop13_disDT = :EvnPLDispDop13_disDate,
				@Server_id = :Server_id,
				@Lpu_id = :Lpu_id,
				@DispClass_id = :DispClass_id,
				@PayType_id = :PayType_id,
				@EvnPLDispDop13_fid = :EvnPLDispDop13_fid,
				@AttachType_id = 2,
				@EvnPLDispDop13_IsStenocard = :EvnPLDispDop13_IsStenocard,
				@EvnPLDispDop13_IsShortCons = :EvnPLDispDop13_IsShortCons,
				@EvnPLDispDop13_IsBrain = :EvnPLDispDop13_IsBrain,
				@EvnPLDispDop13_IsDoubleScan = :EvnPLDispDop13_IsDoubleScan,
				@EvnPLDispDop13_IsTub = :EvnPLDispDop13_IsTub,
				@EvnPLDispDop13_IsTIA = :EvnPLDispDop13_IsTIA,
				@EvnPLDispDop13_IsRespiratory = :EvnPLDispDop13_IsRespiratory,
				@EvnPLDispDop13_IsLungs = :EvnPLDispDop13_IsLungs,
				@EvnPLDispDop13_IsTopGastro = :EvnPLDispDop13_IsTopGastro,
				@EvnPLDispDop13_IsBotGastro = :EvnPLDispDop13_IsBotGastro,
				@EvnPLDispDop13_IsSpirometry = :EvnPLDispDop13_IsSpirometry,
				@EvnPLDispDop13_IsHeartFailure = :EvnPLDispDop13_IsHeartFailure,
				@EvnPLDispDop13_IsOncology = :EvnPLDispDop13_IsOncology,
				@EvnPLDispDop13_IsEsophag = :EvnPLDispDop13_IsEsophag,
				@EvnPLDispDop13_IsSmoking = :EvnPLDispDop13_IsSmoking,
				@EvnPLDispDop13_IsRiskAlco = :EvnPLDispDop13_IsRiskAlco,
				@EvnPLDispDop13_IsAlcoDepend = :EvnPLDispDop13_IsAlcoDepend,
				@EvnPLDispDop13_IsLowActiv = :EvnPLDispDop13_IsLowActiv,
				@EvnPLDispDop13_IsIrrational = :EvnPLDispDop13_IsIrrational,
				@EvnPLDispDop13_IsUseNarko = :EvnPLDispDop13_IsUseNarko,
				@Diag_id = :Diag_id,
				@Diag_sid = :Diag_sid,
				@EvnPLDispDop13_IsDisp = :EvnPLDispDop13_IsDisp,
				@NeedDopCure_id = :NeedDopCure_id,
				@EvnPLDispDop13_IsStac = :EvnPLDispDop13_IsStac,
				@EvnPLDispDop13_IsSanator = :EvnPLDispDop13_IsSanator,
				@EvnPLDispDop13_SumRick = :EvnPLDispDop13_SumRick,
				@RiskType_id = :RiskType_id,
				@EvnPLDispDop13_IsSchool = :EvnPLDispDop13_IsSchool,
				@EvnPLDispDop13_IsProphCons = :EvnPLDispDop13_IsProphCons,
				@EvnPLDispDop13_IsHypoten = :EvnPLDispDop13_IsHypoten,
				@EvnPLDispDop13_IsLipid = :EvnPLDispDop13_IsLipid,
				@EvnPLDispDop13_IsHypoglyc = :EvnPLDispDop13_IsHypoglyc,
				@HealthKind_id = :HealthKind_id,
				@EvnPLDispDop13_IsEndStage = :EvnPLDispDop13_IsEndStage,
				@EvnPLDispDop13_IsFinish = :EvnPLDispDop13_IsEndStage,
				@EvnPLDispDop13_IsTwoStage = :EvnPLDispDop13_IsTwoStage,
				@EvnPLDispDop13_consDT = @EvnPLDispDop13_consDate,
				@EvnPLDispDop13_IsMobile = :EvnPLDispDop13_IsMobile, 
				@EvnPLDispDop13_IsOutLpu = :EvnPLDispDop13_IsOutLpu, 
				@Lpu_mid = :Lpu_mid,
				@CardioRiskType_id = :CardioRiskType_id,
				@EvnPLDispDop13_IsRefusal = @EvnPLDispDop13_IsRefusal,
				@EvnDirection_aid = @EvnDirection_aid,
				@EvnPLDispDop13_Percent = :EvnPLDispDop13_Percent,
				@EvnPLDispDop13_IsSuspectZNO = :EvnPLDispDop13_IsSuspectZNO,
				@Diag_spid = :Diag_spid,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
				
			select @EvnPLDispDop13_id as EvnPLDispDop13_id, convert(varchar(10), @EvnPLDispDop13_consDate, 120) as EvnPLDispDop13_setDT, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
			select top 1
				dbo.Age2(PS.Person_BirthDay, cast(cast(YEAR(EPLD.EvnPLDisp_consDT) as varchar) + '-12-31' as datetime)) as Person_Age,
				PS.Sex_id,
				case 
					when DDQkur.value = 3 then 1
					when DDQkur.value is null and DD13kur.value13 is null then DP.valueP
					else case when isnull(DDQkur.value,0) = 0 then DD13kur.value13 else DDQkur.value end 
				end as EvnPLDisp_IsSmoking,
				USsys.value as systolic_blood_pressure,
				USchol.value as total_cholesterol
			from
				v_EvnPLDisp EPLD (nolock)
				left join v_PersonState PS (nolock) on ps.Person_id = EPLD.Person_id
				outer apply(
					
					
					select 
						DopDispQuestion_ValuesStr as value 
					from
					 	DopDispQuestion DDQ (nolock)
					 	left join v_QuestionType QT (nolock) on QT.QuestionType_id = DDQ.QuestionType_id
					where
						QT.QuestionType_Name='Курите ли Вы? (курение одной и более сигарет в день)'
						and QT.DispClass_id =EPLD.DispClass_id
						and ISNULL(QT.QuestionType_begDate, EPLD.EvnPLDisp_consDT) <= EPLD.EvnPLDisp_consDT
						and ISNULL(QT.QuestionType_endDate, EPLD.EvnPLDisp_consDT) >= EPLD.EvnPLDisp_consDT
						and ISNULL(QT.QuestionType_AgeFrom, Person_Age) <= Person_Age
						and ISNULL(QT.QuestionType_AgeTo, Person_Age) >= Person_Age
					 	and DDQ.EvnPLDisp_id = EPLD.EvnPLDisp_id

				) DDQkur
				outer apply(
					select DD13.EvnPLDispDop13_IsSmoking as value13 from v_EvnPLDispDop13 DD13 (nolock) where DD13.EvnPLDispDop13_id = EPLD.EvnPLDisp_id
				) DD13kur
				outer apply(
					select EvnPLDispProf_IsSmoking as valueP
					from v_EvnPLDispProf
					where EvnPLDispProf_id = EPLD.EvnPLDisp_id		
				)DP
				outer apply(
					select 
						CASE RVT.RateValueType_SysNick
							WHEN 'int' THEN cast(cast(FI.FormalizedInspection_NResult as decimal(16)) as varchar)
							WHEN 'float' THEN cast(cast(FI.FormalizedInspection_NResult as decimal(16,3)) as varchar)
							WHEN 'string' THEN FI.FormalizedInspection_Result
							WHEN 'template' THEN FI.FormalizedInspection_Result
							WHEN 'reference' THEN cast(FI.FormalizedInspection_DirectoryAnswer_id as varchar)
							WHEN 'datetime' THEN convert(varchar(10), FI.FormalizedInspection_Result, 104)
						END as value
					from
						v_EvnVizitDispDop evdd (nolock)
						left join v_EvnUslugaDispDop eudd (nolock) on eudd.EvnUslugaDispDop_pid = evdd.EvnVizitDispDop_id
						left join v_FormalizedInspection FI (nolock) on FI.EvnUslugaDispDop_id = eudd.EvnUslugaDispDop_id
						left join v_FormalizedInspectionParams FIP (nolock) on FIP.FormalizedInspectionParams_id = FI.FormalizedInspectionParams_id
						left join RateValueType RVT (nolock) on RVT.RateValueType_id = FIP.RateValueType_id
					where 
						evdd.EvnVizitDispDop_pid = EPLD.EvnPLDisp_id and FIP.FormalizedInspectionParams_SysNick = 'systolic_blood_pressure'
				) USsys
				outer apply(
					select 
						CASE RVT.RateValueType_SysNick
							WHEN 'int' THEN cast(cast(FI.FormalizedInspection_NResult as decimal(16)) as varchar)
							WHEN 'float' THEN cast(cast(FI.FormalizedInspection_NResult as decimal(16,3)) as varchar)
							WHEN 'string' THEN FI.FormalizedInspection_Result
							WHEN 'template' THEN FI.FormalizedInspection_Result
							WHEN 'reference' THEN cast(FI.FormalizedInspection_DirectoryAnswer_id as varchar)
							WHEN 'datetime' THEN convert(varchar(10), FI.FormalizedInspection_Result, 104)
						END as value
					from
						v_EvnVizitDispDop evdd (nolock)
						left join v_EvnUslugaDispDop eudd (nolock) on eudd.EvnUslugaDispDop_pid = evdd.EvnVizitDispDop_id
						left join v_FormalizedInspection FI (nolock) on FI.EvnUslugaDispDop_id = eudd.EvnUslugaDispDop_id
						left join v_FormalizedInspectionParams FIP (nolock) on FIP.FormalizedInspectionParams_id = FI.FormalizedInspectionParams_id
						left join RateValueType RVT (nolock) on RVT.RateValueType_id = FIP.RateValueType_id
					where 
						evdd.EvnVizitDispDop_pid = EPLD.EvnPLDisp_id and FIP.FormalizedInspectionParams_SysNick = 'total_cholesterol'
				) USchol
			where
				EPLD.EvnPLDisp_id = :EvnPLDispDop13_id
		";
		//~ exit(getDebugSQL($query, $data));
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
					ScoreValues_Values
				from
					v_ScoreValues (nolock)
				where
					(cast (:systolic_blood_pressure as float) BETWEEN ISNULL(ScoreValues_MinPress,0) and ISNULL(ScoreValues_MaxPress,900)) and
					(:Person_Age BETWEEN ISNULL(ScoreValues_AgeFrom,0) and ISNULL(ScoreValues_AgeTo,900)) and
					(cast (:total_cholesterol as float) BETWEEN ISNULL(ScoreValues_MinChol,0) and ISNULL(ScoreValues_MaxChol,900)) and
					:Sex_id = ISNULL(Sex_id, :Sex_id) and
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
				update EvnPLDispDop13 with(rowlock)
				set EvnPLDispDop13_SumRick = :EvnPLDispDop13_SumRick, 
					RiskType_id = :RiskType_id
				where EvnPLDispDop13_id = :EvnPLDispDop13_id
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
			select top 1
				evdd.EvnVizitDispDop_id,
			    eudd.EvnUslugaDispDop_id,
			    stl.DispClass_id,
			    st.SurveyType_Code
			from
				v_EvnUslugaDispDop eudd (nolock)
				inner join v_EvnVizitDispDop evdd (nolock) on evdd.EvnVizitDispDop_id = eudd.EvnUslugaDispDop_pid
				inner join v_DopDispInfoConsent ddic (nolock) on ddic.DopDispInfoConsent_id = evdd.DopDispInfoConsent_id
				inner join v_SurveyTypeLink stl (nolock) on stl.SurveyTypeLink_id = ddic.SurveyTypeLink_id
				inner join v_SurveyType st (nolock) on st.SurveyType_id = stl.SurveyType_id
			where evdd.EvnVizitDispDop_pid = :EvnVizitDispDop_pid
			  and st.SurveyType_Code in (19,27)
			  and cast(eudd.EvnUslugaDispDop_didDT as date) < :DopDispQuestion_setDate
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
			select top 1
				STL.UslugaComplex_id, -- услуга которую нужно сохранить
				EUDDData.EvnUslugaDispDop_id
			from
				v_SurveyTypeLink STL (nolock)
				inner join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
				inner join v_DopDispInfoConsent ddic (nolock) on ddic.SurveyTypeLink_id = stl.SurveyTypeLink_id
				outer apply(
					select top 1 EvnUslugaDispDop_id
					from v_EvnUslugaDispDop EUDD with (nolock)
					where EUDD.EvnUslugaDispDop_pid = :EvnPLDisp_id
					  and EUDD.UslugaComplex_id IN (select UslugaComplex_id from v_SurveyTypeLink (nolock) where SurveyType_id = STL.SurveyType_id)
					  and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
				) EUDDData
			where ST.SurveyType_Code = 2
			  and ddic.EvnPLDisp_id = :EvnPLDisp_id
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
			declare
				@EvnUslugaDispDop_id bigint,
				@PayType_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @EvnUslugaDispDop_id = :EvnUslugaDispDop_id;
			set @PayType_id = (select top 1 PayType_id from v_PayType with (nolock) where PayType_SysNick = 'dopdisp');
			exec {$proc}
				@EvnUslugaDispDop_id = @EvnUslugaDispDop_id output,
				@EvnUslugaDispDop_pid = :EvnPLDisp_id,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@EvnDirection_id = NULL,
				@PersonEvn_id = :PersonEvn_id,
				@PayType_id = @PayType_id,
				@UslugaPlace_id = 1,
				@EvnUslugaDispDop_setDT = NULL,
				@UslugaComplex_id = :UslugaComplex_id,
				@EvnUslugaDispDop_didDT = :DopDispQuestion_setDate,
				@ExaminationPlace_id = NULL,
				@Diag_id = :Diag_id,
				@DopDispDiagType_id = :DopDispDiagType_id,
				@EvnUslugaDispDop_DeseaseStage = :DeseaseStage,
				@LpuSection_uid = :LpuSection_uid,
				@MedPersonal_id = :MedPersonal_id,
				@MedStaffFact_id = :MedStaffFact_id,
				@EvnUslugaDispDop_ExamPlace = NULL,
				@EvnPrescrTimetable_id = null,
				@EvnPrescr_id = null,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @EvnUslugaDispDop_id as EvnUslugaDispDop_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		//~ exit(getDebugSQL($query, $data));
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

		/*$Sex_id = $this->getFirstResultFromQuery("
			select
				ps.Sex_id, ps.*
			from
				v_EvnPLDisp epld (nolock)
				inner join v_PersonState ps (nolock) on ps.Person_id = epld.Person_id
			where
				epld.EvnPLDisp_id = :EvnPLDisp_id
		", [
			'EvnPLDisp_id' => $data['EvnPLDisp_id']
		], true);*/
		
		$PersonAgeData = $this->getFirstRowFromQuery("
			DECLARE
				@curDate datetime = dbo.tzGetDate();
			SELECT top 1
				PS.Sex_id,
				dbo.Age2(PS.Person_BirthDay, @curDate) as Person_Age
			FROM v_EvnPLDisp epld (nolock)
				inner join v_PersonState PS (nolock) on PS.Person_id = epld.Person_id
			WHERE epld.EvnPLDisp_id = :EvnPLDisp_id
		", [
			'EvnPLDisp_id' => $data['EvnPLDisp_id']
		]);
		
		$Sex_id = $PersonAgeData["Sex_id"];
		$Person_Age = $PersonAgeData["Person_Age"];
		
		// Получаем существующие данные из БД
		$ExistingDopDispQuestionData = [];

		$query = "
			select
				QuestionType_id,
			    DopDispQuestion_id,
			    DopDispQuestion_ValuesStr
			from v_DopDispQuestion with (nolock)
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
		
		foreach ($items as $item) { ///ext6
			switch ($item["QuestionType_id"]) {
				//num=1.1 //Говорил ли Вам врач когда-либо, что у Вас имеется гипертоническая болезнь (повышенное артериальное давление)?
				/* это id родительких вопросов:
				case 815: //<65
				case 856:
				case 897:
				case 938:
					
				case 1013: //>65
				case 1053:
				case 1093:
				case 1133:
				*/
				//если да
				case 816: //<65
				case 857:
				case 898:
				case 939:
				case 1014: //>65
				case 1054:
				case 1094:
				case 1134:
					$type = 'del';
					if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
						$type = 'ins';
					}
					$dataToUpdate['DispRiskFactor'][$type][18] = array(
						'RiskFactorType_id' => 18
					);
					break;
				//num=1.2 <65
				case 817: //Говорил ли Вам врач когда-либо, что у Вас имеется ишемическая болезнь сердца (стенокардия)?
				case 858:
				case 899:
				case 940:
					if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] == 2)) {
						$this->evndiagdopdisp->addEvnDiagDopDispExt6($data, $this->getDiagIdByCode('I20.9'));
					} else {
						$this->evndiagdopdisp->delEvnDiagDopDispExt6($data, $this->getDiagIdByCode('I20.9'));
					}
					break;
				//num=1.3 <65 лет //Говорил ли Вам врач когда-либо, что у Вас имеется цереброваскулярное заболевание (заболевание сосудов головного мозга)?
				case 818:
				case 859:
				case 900:
				case 941:
					if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] == 2)) {
						$this->evndiagdopdisp->addEvnDiagDopDispExt6($data, $this->getDiagIdByCode('I67.9'));
					} else {
						$this->evndiagdopdisp->delEvnDiagDopDispExt6($data, $this->getDiagIdByCode('I67.9'));
					}
					break;
				//num=1.3 >65
				case 1017:
					break;
				//num=1.5 <65 //<65 Говорил ли Вам врач когда-либо, что у Вас имеется туберкулез (легких или иных локализаций)?
				case 820:
				case 861:
				case 902:
				case 943:
					if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] == 2)) {
						$this->evndiagdopdisp->addEvnDiagDopDispExt6($data, $this->getDiagIdByCode('A16.2'));
					} else {
						$this->evndiagdopdisp->delEvnDiagDopDispExt6($data, $this->getDiagIdByCode('A16.2'));
					}
					break;
				//num=1.6 <65 //<65 Говорил ли Вам врач когда-либо, что у Вас сахарный диабет или повышенный уровень сахара в крови?
				case 821:
				case 862:
				case 903:
				case 944:
					if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] == 2)) {
						$this->evndiagdopdisp->addEvnDiagDopDispExt6($data, $this->getDiagIdByCode('O24.3'));
					} else {
						$this->evndiagdopdisp->delEvnDiagDopDispExt6($data, $this->getDiagIdByCode('O24.3'));
					}
					break;
				//num=1.7 <65 //<65 Говорил ли Вам врач когда-либо, что у Вас заболевания желудка (гастрит, язвенная болезнь)?
				case 823:
				case 864:
				case 905:
				case 946:
					if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] == 2)) {
						$this->evndiagdopdisp->addEvnDiagDopDispExt6($data, $this->getDiagIdByCode('K29.7'));
					} else {
						$this->evndiagdopdisp->delEvnDiagDopDispExt6($data, $this->getDiagIdByCode('K29.7'));
					}
					break;
				//num=1.8 <65 //<65 Говорил ли Вам врач когда-либо, что у Вас хроническое заболевание почек?
				case 824:
				case 865:
				case 906:
				case 947:
					if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] == 2)) {
						$this->evndiagdopdisp->addEvnDiagDopDispExt6($data, $this->getDiagIdByCode('N28.8'));
					} else {
						$this->evndiagdopdisp->delEvnDiagDopDispExt6($data, $this->getDiagIdByCode('N28.8'));
					}
					break;
				//num=3 <65 //Был ли у Вас инсульт?
				case 830:
				case 871:
				case 912:
				case 953:
					if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] == 2)) {
						$this->evndiagdopdisp->addEvnDiagDopDispExt6($data, $this->getDiagIdByCode('I64'));
					} else {
						$this->evndiagdopdisp->delEvnDiagDopDispExt6($data, $this->getDiagIdByCode('I64'));
					}
					break;
				//num=4 <65 //Был ли инфаркт миокарда или инсульт у Ваших близких родственников в молодом или среднем возрасте (до 65 лет  у матери или  родных сестер или  до 55 лет у отца или  родных братьев)?
				case 831:
				case 872:
				case 913:
				case 954:
					$type = 'del';
					if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
						$type = 'ins';
					}
					$dataToUpdate['DispRiskFactor'][$type][29] = array(
						'RiskFactorType_id' => 29
					);
					break;
				//num=5 <65 //Были ли у Ваших близких родственников в молодом или среднем возрасте злокачественные новообразования (легкого, желудка, кишечника, толстой или прямой кишки, предстательной железы, молочной железы, матки, опухоли других локализаций) или полипоз желудка, семейный аденоматоз/диффузный полипоз толстой кишки? (нужное подчеркнуть)
				case 832:
				case 873:
				case 914:
				case 955:
					break;
				//num=6 <65 //Возникает ли у Вас, когда поднимаетесь по лестнице, идете в гору или спешите, или при выходе из теплого помещения на холодный воздух, боль или ощущение давления, жжения, тяжести или явного дискомфорта за грудиной и (или) в левой половине грудной клетки,  и (или) в левом плече, и (или) в левой руке?
				case 833: 
				case 874:
				case 915:
				case 956:
				//num=7 <65 //Если на вопрос 6 ответ «Да», то указанные боли/ощущения/дискомфорт исчезают сразу или в течение не более чем 20 мин после прекращения ходьбы/адаптации к холоду/ в тепле/в покое и (или) они исчезают через 1-5 мин после приема нитроглицерина?
				case 834: 
				case 875:
				case 916:
				case 957:
					$type = 'del';
					if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
						$type = 'ins';
					}
					$dataToUpdate['DispDeseaseSusp'][$type][26] = array(
						'DispDeseaseSuspType_id' => 26
					);
					break;
				//num=8 <65 //Возникала ли у Вас когда-либо внезапная кратковременная слабость или неловкость при движении в одной руке (ноге) либо руке и ноге одновременно так, что Вы не могли взять или удержать предмет, встать со стула, пройтись по комнате? 
				case 835: 
				case 876:
				case 917:
				case 958:
				//num=9 <65 //Возникало ли у Вас когда-либо внезапное без явных причин кратковременное онемение в одной руке, ноге или половине лица, губы или языка?
				case 836:
				case 877:
				case 918:
				case 959:
				//num=10 <65 //Возникала ли у Вас когда-либо внезапно кратковременная потеря зрения на один глаз?
				case 837:
				case 878:
				case 919:
				case 960:
					//вероятность
					$type = 'del';
					if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
						$type = 'ins';
					}
					$dataToUpdate['DispDeseaseSusp'][$type][9] = array(
						'DispDeseaseSuspType_id' => 9
					);
					break;
				//num=11 <65 //Бывают ли у Вас ежегодно периоды ежедневного кашля с отделением мокроты на протяжении примерно 3-х месяцев в году?
				case 838:
				case 879:
				case 920:
				case 961:
				//num=12 <65 //Бывают ли у Вас свистящие или жужжащие хрипы в грудной клетке при дыхании, не проходящие при откашливании?
				case 839:
				case 880:
				case 921:
				case 962:
					//вероятность
					$type = 'del';
					if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
						$type = 'ins';
					}
					$dataToUpdate['DispDeseaseSusp'][$type][6] = array(
						'DispDeseaseSuspType_id' => 6
					);
					break;
				//num=13 <65 //Бывало ли у Вас когда-либо кровохарканье?
				case 840:
				case 881:
				case 922:
				case 963:
					//вероятность
					$type = 'del';
					if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
						$type = 'ins';
					}
					$dataToUpdate['DispDeseaseSusp'][$type][8] = array(
						'DispDeseaseSuspType_id' => 8
					);
					break;
				//num=14 <65 //Беспокоят ли Вас боли в области верхней части живота (в области желудка), отрыжка, тошнота, рвота, ухудшение или отсутствие аппетита?
				case 841:
				case 882:
				case 923:
				case 964:
				//num=15 <64 //Бывает ли у Вас неоформленный (полужидкий) черный или дегтеобразный стул?
				case 842:
				case 883:
				case 924:
				case 965:
				//num=16 <65 //Похудели ли Вы за последнее время без видимых причин (т.е. без соблюдения диеты или увеличения физической активности и пр.)?
				case 843:
				case 884:
				case 925:
				case 966:
				//num=17 <65 //Бывает ли у Вас боль в области заднепроходного отверстия?
				case 844:
				case 885:
				case 926:
				case 967:
					//вероятность
					$type = 'del';
					if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
						$type = 'ins';
					}
					$dataToUpdate['DispDeseaseSusp'][$type][10] = array(
						'DispDeseaseSuspType_id' => 10
					);
					break;
				//num=19 <65 //КУРЕНИЕ. Курите ли Вы? (курение одной и более сигарет в день)
				case 846:
				case 887:
				case 928:
				case 969:
					$type = 'del';
					if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] >= 2)) {
						$type = 'ins';
					}
					$dataToUpdate['DispRiskFactor'][$type][3] = array(
						'RiskFactorType_id' => 3
					);
					break;
				//num=21 <65 //Сколько минут в день Вы тратите на ходьбу в умеренном или быстром темпе (включая дорогу до места работы и обратно)?
				case 848:
				case 889:
				case 930:
				case 971:
					$type = 'del';
					if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] == 1)) {
						$type = 'ins';
					}
					$dataToUpdate['DispRiskFactor'][$type][6] = array(
						'RiskFactorType_id' => 6 //низкая физическая активность
					);
					break;
				//num=22 <65 //Присутствует ли в Вашем ежедневном рационе 400-500 г сырых овощей и фруктов?
				case 849:
				case 890:
				case 931:
				case 972:
					$usePoorNutrition = true;
					if ($item['DopDispQuestion_IsTrue'] == 1) {
						$isPoorNutrition = true;
					}
					break;
				//num=23 <65 //Имеете ли Вы привычку подсаливать приготовленную пищу, не пробуя ее?
				case 850:
				case 891:
				case 932:
				case 973:
					$usePoorNutrition = true;
					if ($item['DopDispQuestion_IsTrue'] == 2) {
						$isPoorNutritionTwo = true;
					}
					break;
				//num=24 <65 //Принимали ли Вы за последний год психотропные или наркотические вещества без назначения врача?
				case 851:
				case 892:
				case 933:
				case 974:
					$type = 'del';
					if (($item['AnswerType_id'] == 1 && $item['DopDispQuestion_IsTrue'] == 2) || ($item['AnswerType_id'] == 3 && $item['DopDispQuestion_ValuesStr'] == 1)) {
						$type = 'ins';
					}
					$dataToUpdate['DispRiskFactor'][$type][19] = array(
						'RiskFactorType_id' => 19 //Потребление наркотических средств
					);
					break;
				//num=25 <65 //Как часто Вы употребляете алкогольные напитки?
				case 852:
				case 893:
				case 934:
				case 975:
				//num=26 <65 //Какое количество алкогольных напитков (сколько порций) вы выпиваете обычно за один раз? 1 порция равна 12 мл чистого этанола ИЛИ 30 мл крепкого алкоголя (водки) ИЛИ 100 мл сухого вина ИЛИ 300 мл пива
				case 853:
				case 894:
				case 935:
				case 976:
				//num=27 <65 //Как часто Вы употребляете за один раз 6 или более порций? 6 порций равны ИЛИ 180 мл крепкого алкоголя (водки) ИЛИ 600 мл сухого вина ИЛИ 1,8 л пива
				case 854:
				case 895:
				case 936:
				case 977:
					$useAlco = true;
					$alcoSum += intval($item['DopDispQuestion_ValuesStr']) - 64;
					break;
				//-----------old version:
				/*
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
					break;*/
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
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @Res = :DopDispQuestion_id;
				
				exec {$proc}
					@DopDispQuestion_id = @Res output, 
					@EvnPLDisp_id = :EvnPLDisp_id, 
					@QuestionType_id = :QuestionType_id, 
					@DopDispQuestion_IsTrue = :DopDispQuestion_IsTrue, 
					@DopDispQuestion_Answer = :DopDispQuestion_Answer, 
					@DopDispQuestion_ValuesStr = :DopDispQuestion_ValuesStr,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output
 
				select @Res as DopDispQuestion_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
			$dataToUpdate['DispRiskFactor'][$type][4] = array(
				'RiskFactorType_id' => 4
			);
		}

		if (!empty($dataToUpdate['DispRiskFactor']['ins'])) {
			foreach ($dataToUpdate['DispRiskFactor']['ins'] as $key => $value) {
				unset($dataToUpdate['DispRiskFactor']['del'][$key]);
				$this->addDispRiskFactor($data, $value['RiskFactorType_id']);
			}
		}

		if (!empty($dataToUpdate['DispRiskFactor']['del'])) {
			foreach ($dataToUpdate['DispRiskFactor']['del'] as $key => $value) {
				$this->delDispRiskFactor($data, $value['RiskFactorType_id']);
			}
		}
		
		if (!empty($dataToUpdate['DispDeseaseSusp']['ins'])) {
			foreach ($dataToUpdate['DispDeseaseSusp']['ins'] as $key => $value) {
				unset($dataToUpdate['DispDeseaseSusp']['del'][$key]);
				$this->addDispDeseaseSusp($data, $value['DispDeseaseSuspType_id']);
			}
		}

		if (!empty($dataToUpdate['DispDeseaseSusp']['del'])) {
			foreach ($dataToUpdate['DispDeseaseSusp']['del'] as $key => $value) {
				$this->delDispDeseaseSusp($data, $value['DispDeseaseSuspType_id']);
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
			select top 1 EvnUslugaDispDop_id
			from v_EvnUslugaDispDop with (nolock)
			where EvnUslugaDispDop_pid = :EvnPLDisp_id
				and UslugaComplex_id = :UslugaComplex_id
				and EvnUslugaDispDop_id != :EvnUslugaDispDop_id
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
	 * Добавить фактор риска по анкете, с проверкой
	 */
	function addDispRiskFactor($data, $RiskFactorType_id) {
		// проверяем есть ли такой, если нет, то добавляем
		if (!empty($RiskFactorType_id)) {
			$data['RiskFactorType_id'] = $RiskFactorType_id;
			$query = "
				select top 1
					DispRiskFactor_id
				from v_DispRiskFactor (nolock)
				where EvnPLDisp_id = :EvnPLDisp_id and RiskFactorType_id = :RiskFactorType_id
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
				'EvnPLDispDop13_id' => $data['EvnPLDisp_id'],
				'RiskFactorType_id' => $data['RiskFactorType_id'],
				'pmUser_id' => $data['pmUser_id']
			);
			$this->addEvnPLDispDop13FactorRisk($params);
		}
	}
	
	/**
	 * Удалить фактор риска по анкете, с проверкой
	 */
	function delDispRiskFactor($data, $RiskFactorType_id) {
		// проверяем есть ли такой, если есть, то удаляем
		if (!empty($RiskFactorType_id)) {
			$data['RiskFactorType_id'] = $RiskFactorType_id;
			$query = "
				select top 1
					DispRiskFactor_id
				from v_DispRiskFactor (nolock)
				where EvnPLDisp_id = :EvnPLDisp_id and RiskFactorType_id = :RiskFactorType_id
			";

			$result = $this->db->query($query, $data);

			if (is_object($result))
			{
				$resp = $result->result('array');
				if (count($resp) > 0 && !empty($resp[0]['DispRiskFactor_id'])) {
					
					$res = $this->delEvnPLDispDop13FactorRisk(array(
						'DispRiskFactor_id' => $resp[0]['DispRiskFactor_id'],
						'pmUser_id' => $data['pmUser_id']
					));
					
					if ( is_object($res) ) {
						return $res->result('array');
					} else {
						return false;
					}
				}
			}
		}
	}

	/**
	 * Добавить подозрение по анкете, с проверкой
	 */
	function addDispDeseaseSusp($data, $DispDeseaseSuspType_id) {
		// проверяем, если нет, то добавляем
		if (!empty($DispDeseaseSuspType_id)) {
			$data['DispDeseaseSuspType_id'] = $DispDeseaseSuspType_id;
			$query = "
				select top 1
					DispDeseaseSusp_id
				from v_DispDeseaseSusp (nolock)
				where EvnPLDisp_id = :EvnPLDisp_id and DispDeseaseSuspDict_id = :DispDeseaseSuspType_id
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
				'EvnPLDispDop13_id' => $data['EvnPLDisp_id'],
				'DispDeseaseSuspType_id' => $data['DispDeseaseSuspType_id'],
				'pmUser_id' => $data['pmUser_id']
			);
			$this->addEvnPLDispDop13DispDeseaseSuspType($params);
		}
	}
	
	/**
	 * Удалить подозрение по анкете, с проверкой
	 */
	function delDispDeseaseSusp($data, $DispDeseaseSuspType_id) {
		// проверяем, если есть, то удаляем
		if (!empty($DispDeseaseSuspType_id)) {
			$data['DispDeseaseSuspType_id'] = $DispDeseaseSuspType_id;
			$query = "
				select top 1
					DispDeseaseSusp_id
				from v_DispDeseaseSusp (nolock)
				where EvnPLDisp_id = :EvnPLDisp_id and DispDeseaseSuspDict_id = :DispDeseaseSuspType_id
			";

			$result = $this->db->query($query, $data);

			if (is_object($result))
			{
				$resp = $result->result('array');
				if (count($resp) > 0 && !empty($resp[0]['DispDeseaseSusp_id'])) {
					
					$res = $this->delEvnPLDispDop13DispDeseaseSusp(array(
						'DispDeseaseSusp_id' => $resp[0]['DispDeseaseSusp_id'],
						'pmUser_id' => $data['pmUser_id']
					));
					
					if ( is_object($res) ) {
						return $res->result('array');
					} else {
						return false;
					}
				}
			}
		}
	}
	/**
	 * 
	 */
	function saveEvnPLDispDop13_SuspectZNO($data) {
		
		$query = "
				select top 1
					 EvnPLDispDop13_pid
					,EvnPLDispDop13_rid
					,EvnPLDispDop13_fid
					,Lpu_id
					,Server_id
					,PersonEvn_id
					,convert(varchar(20), EvnPLDispDop13_consDT, 120) as EvnPLDispDop13_consDT
					,convert(varchar(20), EvnPLDispDop13_setDT, 120) as EvnPLDispDop13_setDT
					,convert(varchar(20), EvnPLDispDop13_disDT, 120) as EvnPLDispDop13_disDT
					,convert(varchar(20), EvnPLDispDop13_didDT, 120) as EvnPLDispDop13_didDT
					,Morbus_id
					,EvnPLDispDop13_IsSigned
					,pmUser_signID
					,EvnPLDispDop13_signDT
					,EvnPLDispDop13_IsFinish
					,EvnPLDispDop13_IsNewOrder
					,EvnPLDispDop13_IndexRep
					,EvnPLDispDop13_IndexRepInReg
					,EvnDirection_aid
					,Person_Age
					,AttachType_id
					,Lpu_aid
					,DispClass_id
					,MedStaffFact_id
					,PayType_id
					,Lpu_mid
					,EvnPLDispDop13_IsOutLpu
					
					
				from v_EvnPLDispDop13 with (nolock)
				where EvnPLDispDop13_id = :EvnPLDispDop13_id
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
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
					
				set @Res = :EvnPLDispDop13_id;
				
				exec p_EvnPLDispDop13_upd
					@EvnPLDispDop13_id = @Res output, 
			";

			foreach ( $resp[0] as $key => $value ) {
				$query .= "@" . $key . " = :" . $key . ",";
			}

			$query .= "
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output

				select @Res as EvnPLDispDop13_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
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
?>
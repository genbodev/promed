<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnPLDispTeenInspection_model - модель для работы с талонами по периодическим осмотрам несовершеннолетних
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @author       Власенко Дмитрий
 * @version      01.08.2013
 */

require_once('EvnPLDispAbstract_model.php');

class EvnPLDispTeenInspection_model extends EvnPLDispAbstract_model
{
	/**
	 *	Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->inputRules = array(
			'loadEvnUslugaDispDopDirection' => array(
				array(
					'field' => 'EvnUslugaDispDop_id',
					'label' => 'Идентификатор осмотра (исследования)',
					'rules' => 'trim|required',
					'type' => 'id'
				)
			),
			'getEvnPLDispTeenInspectionYears' => array(
				array(
					'field' => 'DispClass_id',
					'label' => 'Вид диспансеризации',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadEvnUslugaDispDopGridForDirection' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'PersonDispOrp_id',
					'label' => 'Идентификатор направления',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EducationInstitutionType_id',
					'label' => 'Тип образовательного учреждения',
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
					'field' => 'DispClass_id',
					'label' => 'Идентификатор этапа',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'checkIfEvnPLDispTeenInspectionExists' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => 'trim|required',
					'type' => 'id'
				),
				array(
					'field' => 'DispClass_id',
					'label' => 'Идентификатор этапа',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDisp_Year',
					'label' => 'Год',
					'rules' => '',
					'type' => 'int'
				),
			),
			'loadEvnDiagAndRecomendationGrid' => array(
				array(
					'field' => 'EvnPLDispTeenInspection_id',
					'label' => 'Идентификатор осмотра',
					'rules' => 'trim|required',
					'type' => 'id'
				)
			),
			'loadEvnDiagAndRecomendationSecGrid' => array(
				array(
					'field' => 'EvnPLDispTeenInspection_id',
					'label' => 'Идентификатор осмотра',
					'rules' => 'trim|required',
					'type' => 'id'
				)
			),
			'loadEvnUslugaDispDopSecGrid' => array(
				array(
					'field' => 'EvnPLDispTeenInspection_id',
					'label' => 'Идентификатор осмотра',
					'rules' => 'trim|required',
					'type' => 'id'
				)
			),
			'loadEvnVizitDispDopSecGrid' => array(
				array(
					'field' => 'EvnPLDispTeenInspection_id',
					'label' => 'Идентификатор осмотра',
					'rules' => 'trim|required',
					'type' => 'id'
				)
			),
			'deleteEvnPLDispTeenInspection' => array(
				array(
					'field' => 'EvnPLDispTeenInspection_id',
					'label' => 'Идентификатор осмотра',
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
			'saveEvnUslugaDispDopDirection' => array(
				array(
					'field' => 'EvnUslugaDispDop_id',
					'label' => 'Идентификатор услуги',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PersonDispOrp_id',
					'label' => 'Идентификатор направления',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'DispClass_id',
					'label' => 'Идентификатор этапа',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'SurveyTypeLink_id',
					'label' => 'Идентификатор записи из списка добровольного информированного согласия',
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
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => 'required',
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
					'rules' => 'trim|required',
					'type' => 'date'
				),
				array(
					'field' => 'EvnUslugaDispDop_setTime',
					'label' => 'Время',
					'rules' => 'trim',
					'type' => 'time'
				)
			),
			'loadEvnPLDispTeenInspectionEditForm' => array(
				array(
					'field' => 'EvnPLDispTeenInspection_id',
					'label' => 'Идентификатор талона по доп. диспансеризации',
					'rules' => 'trim|required',
					'type' => 'id'
				)
			),
			'loadEvnUslugaDispDopGrid' => array(
				array(
					'field' => 'EvnPLDispTeenInspection_id',
					'label' => 'Идентификатор талона по доп. диспансеризации',
					'rules' => 'trim|required',
					'type' => 'id'
				)
			),
			'loadDopDispInfoConsent' => array(
				array(
					'field' => 'EvnPLDispTeenInspection_id',
					'label' => 'Идентификатор талона по доп. диспансеризации',
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
					'field' => 'EducationInstitutionType_id',
					'label' => 'Тип образовательного учреждения',
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
					'field' => 'DispClass_id',
					'label' => 'Идентификатор этапа',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispTeenInspection_consDate',
					'label' => 'Дата согласия/отказа',
					'rules' => 'required',
					'type' => 'date'
				)
			),
			'saveDopDispInfoConsent' => array(
				array(
					'field' => 'EvnPLDispTeenInspection_id',
					'label' => 'Идентификатор талона по доп. диспансеризации',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PersonDispOrp_id',
					'label' => 'Идентификатор в реестре',
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
					'field' => 'EvnPLDispTeenInspection_fid',
					'label' => 'Идентификатор карты предыдущего этапа',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EducationInstitutionType_id',
					'label' => 'Тип образовательного учреждения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Org_id',
					'label' => 'Организация',
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
					'field' => 'Lpu_mid',
					'label' => 'МО мобильной бригады',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispTeenInspection_IsMobile',
					'label' => 'Обслужен мобильной бригадой',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'EvnPLDispTeenInspection_IsOutLpu',
					'label' => 'Проведён вне МО',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'EvnPLDispTeenInspection_consDate',
					'label' => 'Дата подписания согласия/отказа',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'EvnPLDispTeenInspection_setDate',
					'label' => 'Дата начала медицинского осмотра',
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
				)
			),
			'saveEvnDiagAndRecomendation' => array(
				array(
					'field' => 'EvnVizitDispDop_id',
					'label' => 'Идентификатор посещений',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'FormDataJSON',
					'label' => 'Диагнозы и рекомендации',
					'rules' => '',
					'type' => 'string'
				)
			),
			'saveEvnPLDispTeenInspection' => array(
				array(
					'field' => 'AssessmentHealthVaccinData',
					'label' => 'Прививки',
					'rules' => '',
					'type' => 'json_array',
					'assoc' => true
				),
				array(
					'field' => 'EvnPLDispTeenInspection_id',
					'label' => 'Идентификатор карты',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispTeenInspection_IndexRep',
					'label' => 'Признак повторной подачи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnPLDispTeenInspection_IndexRepInReg',
					'label' => 'Признак повторной подачи в реестре',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'PersonDispOrp_id',
					'label' => 'Идентификатор в реестре',
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
					'field' => 'EvnPLDispTeenInspection_fid',
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
					'field' => 'EvnPLDispTeenInspection_consDate',
					'label' => 'Дата начала случая',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'EvnPLDispTeenInspection_setDate',
					'label' => 'Дата начала случая',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'EvnPLDispTeenInspection_disDate',
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
					'field' => 'EvnPLDispTeenInspection_eduDT',
					'label' => 'Дата поступления',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'EducationInstitutionClass_id',
					'label' => 'Образовательное учреждение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Org_id',
					'label' => 'Образовательное учреждение',
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
					'field' => 'Lpu_mid',
					'label' => 'МО мобильной бригады',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispTeenInspection_IsMobile',
					'label' => 'Обслужен мобильной бригадой',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'EvnPLDispTeenInspection_IsOutLpu',
					'label' => 'Проведён вне МО',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'InstitutionNatureType_id',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'InstitutionType_id',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispTeenInspection_IsTwoStage',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispTeenInspection_IsFinish',
					'label' => 'Признак',
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
					'field' => 'EvnPLDispTeenInspection_IsSuspectZNO',
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
			'saveEvnPLDispTeenInspectionSec' => array(
				array(
					'field' => 'EvnPLDispTeenInspection_id',
					'label' => 'Идентификатор карты',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispTeenInspection_IndexRep',
					'label' => 'Признак повторной подачи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnPLDispTeenInspection_IndexRepInReg',
					'label' => 'Признак повторной подачи в реестре',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'PersonDispOrp_id',
					'label' => 'Идентификатор в реестре',
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
					'field' => 'EvnPLDispTeenInspection_fid',
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
					'field' => 'EvnPLDispTeenInspection_consDate',
					'label' => 'Дата начала случая',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'EvnPLDispTeenInspection_setDate',
					'label' => 'Дата начала случая',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'EvnPLDispTeenInspection_disDate',
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
					'field' => 'EvnPLDispTeenInspection_eduDT',
					'label' => 'Дата поступления',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'EducationInstitutionClass_id',
					'label' => 'Образовательное учреждение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Org_id',
					'label' => 'Образовательное учреждение',
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
					'field' => 'Lpu_mid',
					'label' => 'МО мобильной бригады',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispTeenInspection_IsMobile',
					'label' => 'Обслужен мобильной бригадой',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'EvnPLDispTeenInspection_IsOutLpu',
					'label' => 'Проведён вне МО',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'InstitutionNatureType_id',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'InstitutionType_id',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispTeenInspection_IsTwoStage',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispTeenInspection_IsFinish',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DispAppointData',
					'label' => 'Массив данных DispAppointData',
					'rules' => '',
					'type' => 'json_array',
					'assoc' => true
				),
				array(
					'field' => 'EvnVizitDispDop',
					'label' => 'Массив данных EvnVizitDispDop',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'EvnUslugaDispDop',
					'label' => 'Массив данных EvnUslugaDispDop',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'EvnDiagAndRecomendation',
					'label' => 'Массив данных EvnDiagAndRecomendation',
					'rules' => '',
					'type' => 'string'
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
					'field' => 'ignoreOsmotrDlit',
					'label' => 'Признак игнорирования проверки длительности случая',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnPLDispTeenInspection_IsSuspectZNO',
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
			),
			'saveEvnVizitDispDop' => array(
				array(
					'field' => 'EvnVizitDispDop_id',
					'label' => 'Идентификатор осмотра',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispTeenInspection_id',
					'label' => 'Идентификатор карты',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'PersonEvn_id',
					'label' => '',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Server_id',
					'label' => '',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'Lpu_uid',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaComplex_id',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_id',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedStaffFact_id',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSectionProfile_id',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedSpecOms_id',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Diag_id',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Diag_id',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnDiagDopDispGridData',
					'label' => '',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'EvnVizitDispDop_setDate',
					'label' => '',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'EvnVizitDispDop_setTime',
					'label' => '',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'EvnVizitDispDop_disDate',
					'label' => '',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'EvnVizitDispDop_disTime',
					'label' => '',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'DopDispDiagType_id',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DopDispAlien_id',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				),
			),
			'deleteEvnVizitDispDop' => array(
				array(
					'field' => 'EvnVizitDispDop_id',
					'label' => 'Идентификатор осмотра',
					'rules' => '',
					'type' => 'id'
				),
			)
		);
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
	 * Получение возрастной группы из направления
	 */
	function getAgeGroupDispFromPersonDispOrp($data)
	{
		return $this->getFirstResultFromQuery("SELECT AgeGroupDisp_id as \"AgeGroupDisp_id\" FROM v_PersonDispOrp WHERE PersonDispOrp_id = :PersonDispOrp_id limit 1", array('PersonDispOrp_id' => $data['PersonDispOrp_id']));
	}

	/**
	 * Получение даты направления
	 */
	function getPersonDispOrpSetDateFromPersonDispOrp($data)
	{
		return $this->getFirstResultFromQuery("SELECT to_char(PersonDispOrp_setDate, 'yyyy-mm-dd') as \"PersonDispOrp_setDate\" FROM v_PersonDispOrp WHERE PersonDispOrp_id = :PersonDispOrp_id limit 1", array('PersonDispOrp_id' => $data['PersonDispOrp_id']));
	}

	/**
	 * Проверка, есть ли талон на этого человека в этом году с указанной возрастной группой
	 */
	function checkEvnPLDispTeenInspectionAgeGroup($data)
	{
		$sql = "
            select
            	count(EvnPLDispTeenInspection_id) as \"count\"
            from v_EvnPLDispTeenInspection
            where
                Person_id = :Person_id
				and DispClass_id = :DispClass_id
				and date_part('year', EvnPLDispTeenInspection_consDT) = date_part('year', CAST(:EvnPLDispTeenInspection_consDate as date))
				and EvnPLDispTeenInspection_id != coalesce(cast(:EvnPLDispTeenInspection_id as bigint), 0)
        ";

		if ( in_array($data['DispClass_id'], array(10, 12)) ) {
			$sql .= "
				and AgeGroupDisp_id = :AgeGroupDisp_id
			";
		}

		$res = $this->db->query($sql, array(
			'Person_id' => $data['Person_id'],
			'Lpu_id' => $data['Lpu_id'],
			'DispClass_id' => $data['DispClass_id'],
			'AgeGroupDisp_id' => $data['AgeGroupDisp_id'],
			'EvnPLDispTeenInspection_id' => (!empty($data['EvnPLDispTeenInspection_id']) ? $data['EvnPLDispTeenInspection_id'] : NULL),
			'EvnPLDispTeenInspection_consDate' => $data['EvnPLDispTeenInspection_consDate']
		));
		$result = false;
		if ( is_object($res) ){
			$sel = $res->result('array');
			if ( $sel[0]['count'] == 0) {
				$result = true;
			}
		}
		return $result;
	}

	/**
	 * Проверка, есть ли талон на этого человека в этом году
	 */
	function checkIfEvnPLDispTeenInspectionExists($data)
	{
		if (in_array($data['DispClass_id'], array(10))) {
			// проверяем наличие карты ДДС
			$query = "
				SELECT
					epldo.EvnPLDispOrp_id as \"EvnPLDispOrp_id\"
				FROM
					v_EvnPLDispOrp epldo
					inner join v_PersonState ps on ps.Person_id = epldo.Person_id
				WHERE
					epldo.Person_id = :Person_id
					and date_part('year', epldo.EvnPLDispOrp_setDT) = date_part('year', dbo.tzGetDate())
					and epldo.EvnPLDispOrp_IsFinish = 2 -- закрытый
					and dbo.Age2(ps.Person_BirthDay, dbo.tzGetDate()) >= 3 -- 3 лет и старше
				limit 1
			";
			$checkResult = $this->queryResult($query, array(
				'Person_id' => $data['Person_id']
			));
			if (!empty($checkResult[0]['EvnPLDispOrp_id'])) {
				return array('Error_Msg' => 'На выбранного пациента в текущем году уже сохранена карта диспансеризации несовершеннолетнего (ДДС).');
			}
		}

		$sql = "
			SELECT
				SUM(case when epldti.Lpu_id = :Lpu_id then 1 else 0 end) as \"count\",
				count(epldti.EvnPLDispTeenInspection_id) as \"countAll\",
				max(l.Lpu_Nick) as \"Lpu_Nick\",
				to_char(max(epldti.EvnPLDispTeenInspection_setDate), 'dd.mm.yyyy') as \"EvnPLDispTeenInspection_setDate\"
			FROM
				v_EvnPLDispTeenInspection epldti
				left join v_Lpu l on l.Lpu_id = epldti.Lpu_id
			WHERE
				epldti.Person_id = :Person_id
				and date_part('year', epldti.EvnPLDispTeenInspection_setDate) = date_part('year', dbo.tzGetDate())
				and epldti.DispClass_id = :DispClass_id
		";

		$res = $this->db->query($sql, array(
			'Person_id' => $data['Person_id'],
			'Lpu_id' => $data['Lpu_id'],
			'DispClass_id' => $data['DispClass_id']
		));
		if ( is_object($res) )
		{
			$sel = $res->result('array');
			if ( $sel[0]['count'] == 0 || in_array($data['DispClass_id'], array(9,10)) ) {
				$response = array('isEvnPLDispTeenInspectionExists' => false, 'Error_Msg' => '');
				if ($sel[0]['countAll'] > 0) {
					$response['countAll'] = $sel[0]['countAll'];
					$response['Lpu_Nick'] = $sel[0]['Lpu_Nick'];
					$response['EvnPLDispTeenInspection_setDate'] = $sel[0]['EvnPLDispTeenInspection_setDate'];
				}

				// карты ещё нет, поиск человека в регистре
				$sql = "
					select
						PersonDispOrp_id as \"PersonDispOrp_id\",
						EducationInstitutionType_id as \"EducationInstitutionType_id\",
						Org_id as \"Org_id\"
					from
						v_PersonDispOrp pdo
					where
						pdo.Person_id = :Person_id
						and pdo.PersonDispOrp_Year = coalesce(:EvnPLDisp_Year, date_part('year', dbo.tzGetDate()))
						AND pdo.Lpu_id = :Lpu_id
						and CategoryChildType_id = case when :DispClass_id = 6 then 8 when :DispClass_id = 9 then 9 when :DispClass_id = 10 then 10 end
					limit 1
				";

				$res = $this->db->query($sql, array(
					'Person_id' => $data['Person_id'],
					'Lpu_id' => $data['Lpu_id'],
					'DispClass_id' => $data['DispClass_id'],
					'EvnPLDisp_Year' => $data['EvnPLDisp_Year']
				));
				if ( is_object($res) )
				{
					$sel = $res->result('array');
					if (count($sel) > 0) {
						$response['PersonDispOrp_id'] = $sel[0]['PersonDispOrp_id'];
						$response['Org_id'] = $sel[0]['Org_id'];
						$response['EducationInstitutionType_id'] = $sel[0]['EducationInstitutionType_id'];
					}
				}

				// поиск карты первого этапа
				$filter_1st = " and (1=0)";
				if ($data['DispClass_id'] == 11) {
					$filter_1st = " and epldti.DispClass_id IN (9)";
				}
				if ($data['DispClass_id'] == 12) {
					$filter_1st = " and epldti.DispClass_id IN (10)";
				}

				$sql = "
					SELECT
						epldti.EvnPLDispTeenInspection_id as \"EvnPLDispTeenInspection_fid\",
						eic.EducationInstitutionType_id as \"EducationInstitutionType_id\",
						epldti.Org_id as \"Org_id\",
						epldti.AgeGroupDisp_id as \"AgeGroupDisp_id\"
					FROM
						v_EvnPLDispTeenInspection epldti
						left join v_EducationInstitutionClass eic on eic.EducationInstitutionClass_id = epldti.EducationInstitutionClass_id
					WHERE
						epldti.Person_id = :Person_id
						and epldti.EvnPLDispTeenInspection_IsFinish = 2
						and epldti.EvnPLDispTeenInspection_IsTwoStage = 2
						and not exists (
					  		select
					  			EvnPLDispTeenInspection_id
					  		from v_EvnPLDispTeenInspection
					  		where EvnPLDispTeenInspection_fid = epldti.EvnPLDispTeenInspection_id
					  		limit 1
					  	)
						{$filter_1st}
					ORDER BY
						epldti.EvnPLDispTeenInspection_setDT desc
					limit 1
				";

				$res = $this->db->query($sql, array(
					'Person_id' => $data['Person_id']
				));
				if ( is_object($res) )
				{
					$sel = $res->result('array');
					if (count($sel) > 0) {
						$response['EvnPLDispTeenInspection_fid'] = $sel[0]['EvnPLDispTeenInspection_fid'];
						$response['EducationInstitutionType_id'] = $sel[0]['EducationInstitutionType_id'];
						$response['Org_id'] = $sel[0]['Org_id'];
						$response['AgeGroupDisp_id'] = $sel[0]['AgeGroupDisp_id'];
					}
				}

				return $response;
			} else {
				return array(array('isEvnPLDispTeenInspectionExists' => true, 'Error_Msg' => ''));
			}
		}
		else
			return false;
	}

	/**
	 *	Получение минимальной и максимальной дат оказания услуг
	 */
	function getEvnUslugaDispDopMinMaxDates($data)
	{
		// @task https://redmine.swan.perm.ru/issues/93000
		// Разделил запрос в зависимости от вида осмотра
		// Предварительные осмотры несовершеннолетних 2-ой этап
		// Профилактические осмотры несовершеннолетних 2-ой этап
		if ( !empty($data['DispClass_id']) && in_array($data['DispClass_id'], array(11, 12)) ) {
			$query = "
				select
					to_char(coalesce(MIN(EUDD.EvnUslugaDispDop_setDate),dbo.tzGetDate()), 'yyyy-mm-dd') as \"mindate\",
					to_char(coalesce(MAX(EUDD.EvnUslugaDispDop_setDate),dbo.tzGetDate()), 'yyyy-mm-dd') as \"maxdate\"
				from v_EvnUslugaDispDop EUDD
					inner join v_EvnVizitDispDop EVDD on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
				where
					EVDD.EvnVizitDispDop_pid = :EvnPLDispTeenInspection_id 
					and coalesce(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
			";
		}
		else {
			$query = "
				select
					to_char(coalesce(MIN(EUDDData.EvnUslugaDispDop_didDate),dbo.tzGetDate()), 'yyyy-mm-dd') as \"mindate\",
					to_char(coalesce(MAX(EUDDData.EvnUslugaDispDop_didDate),dbo.tzGetDate()), 'yyyy-mm-dd') as \"maxdate\"
				from v_DopDispInfoConsent DDIC
					left join v_SurveyTypeLink STL on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
					left join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
					left join lateral(
						select
							EUDD.EvnUslugaDispDop_didDate
						from v_EvnUslugaDispDop EUDD
							inner join v_EvnVizitDispDop EVDD on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
						where
							EVDD.EvnVizitDispDop_pid = :EvnPLDispTeenInspection_id
							and EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
							and coalesce(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
						limit 1
					) EUDDData on true
				where DDIC.EvnPLDisp_id = :EvnPLDispTeenInspection_id
					and (DDIC.DopDispInfoConsent_IsAgree = 2 OR DDIC.DopDispInfoConsent_IsEarlier = 2)
					and ST.SurveyType_Code NOT IN (50,67,68)
			";
		}

		$result = $this->db->query($query, array('EvnPLDispTeenInspection_id' => $data['EvnPLDispTeenInspection_id']));

		if (is_object($result))
		{
			$resp = $result->result('array');
			if (is_array($resp) && count($resp) > 0) {
				return $resp[0];
			}
		}

		return false;
	}

	/**
	 * Получение списка лет, в которые выписывались талоны по ДД с количеством талонов, для комбобокса
	 */
	function getEvnPLDispTeenInspectionYears($data)
	{
		$sql = "
			SELECT
				count(EPLDTI.EvnPLDispTeenInspection_id) as \"count\",
				date_part('year', EPLDTI.EvnPLDispTeenInspection_setDate) as \"EvnPLDispTeenInspection_Year\"
			FROM
				v_PersonState PS
                inner join v_EvnPLDispTeenInspection EPLDTI on PS.Person_id = EPLDTI.Person_id
                	and EPLDTI.Lpu_id = :Lpu_id
			WHERE
                coalesce(EPLDTI.DispClass_id, 6) = :DispClass_id
                and exists (
                	select
                		personcard_id
                	from v_PersonCard PC
                		left join v_LpuRegion LR on LR.LpuRegion_id = PC.LpuRegion_id
                	WHERE PC.Person_id = PS.Person_id
                		and PC.Lpu_id = :Lpu_id
                	limit 1
                	)
				and date_part('year', EPLDTI.EvnPLDispTeenInspection_setDate) >= 2013
			GROUP BY
				date_part('year', EPLDTI.EvnPLDispTeenInspection_setDate)
			ORDER BY
				date_part('year', EPLDTI.EvnPLDispTeenInspection_setDate)
		";

		/*echo getDebugSQL($sql, array(
			'Lpu_id' => $data['Lpu_id'],
			'DispClass_id' => $data['DispClass_id'])); die;*/

		$res = $this->db->query($sql, array(
			'Lpu_id' => $data['Lpu_id'],
			'DispClass_id' => $data['DispClass_id']
		));
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Проверка атрибута у отделения
	 */
	function checkAttributeforLpuSection($data)
	{
		$query = "
			select 
				EVDO.EvnVizitDispDop_disDate as \"EvnVizitDispDop_disDate\",
				ASVal.AttributeSign_id as \"AttributeSign_id\",
				ASVal.AttributeSignValue_begDate as \"AttributeSignValue_begDate\",
				ASVal.AttributeSignValue_endDate as \"AttributeSignValue_endDate\"
			from
				v_EvnVizitDispDop EVDO 
				left join LpuSection LS on LS.LpuSection_id = EVDO.LpuSection_id
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
						and ASV.AttributeSignValue_TablePKey = EVDO.LpuSection_id
						and AS1.AttributeSign_Name = 'Передвижные подразделения'
				) ASVal on true
			where
				EVDO.EvnVizitDispDop_pid = :EvnPLDispTeenInspection_id
		";

		$result = $this->db->query($query, array('EvnPLDispTeenInspection_id'=>$data['EvnPLDispTeenInspection_id']));
		$response = $result->result('array');

		if ( is_array($response) && count($response) > 0 ) {

			$col_lpusection=0;
			foreach($response as $res) {
				if (
					!empty($res['AttributeSign_id'])
					&& (is_null($res['AttributeSignValue_begDate']) || (isset($res['AttributeSignValue_begDate']) && $res['AttributeSignValue_begDate']<=$res['EvnVizitDispDop_disDate']))
					&& (is_null($res['AttributeSignValue_endDate']) || (isset($res['AttributeSignValue_endDate']) && $res['AttributeSignValue_endDate']>=$res['EvnVizitDispDop_disDate']))
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
	 * Проверка правильности адреса человека
	 */
	function checkPersonAddress($data) {
		$query = "
			select
				ua.Address_id as \"UAddress_id\",
				ua.Address_Zip as \"UAddress_Zip\",
				uklr.KLRgn_Actual as \"UKLRgn_Actual\",
				uklsr.KLSubRgn_Actual as \"UKLSubRgn_Actual\",
				uklc.KLCity_Actual as \"UKLCity_Actual\",
				uklt.KLTown_Actual as \"UKLTown_Actual\",
				ukls.KLAdr_Actual as \"UKLStreet_Actual\",
				pa.Address_id as \"PAddress_id\",
				pa.Address_Zip as \"PAddress_Zip\",
				pklr.KLRgn_Actual as \"PKLRgn_Actual\",
				pklsr.KLSubRgn_Actual as \"PKLSubRgn_Actual\",
				pklc.KLCity_Actual as \"PKLCity_Actual\",
				pklt.KLTown_Actual as \"PKLTown_Actual\",
				pkls.KLAdr_Actual as \"PKLStreet_Actual\"
			from
				--v_PersonState ps
				v_Person_all p
				left join v_Address ua on ua.Address_id = p.UAddress_id
				left join v_KLRgn uklr on uklr.KLRgn_id = ua.KLRgn_id
				left join v_KLSubRgn uklsr on uklsr.KLSubRgn_id = ua.KLSubRgn_id
				left join v_KLCity uklc on uklc.KLCity_id = ua.KLCity_id
				left join v_KLTown uklt on uklt.KLTown_id = ua.KLTown_id
				left join v_KLStreet ukls on ukls.KLStreet_id = ua.KLStreet_id
				left join v_Address pa on pa.Address_id = p.PAddress_id
				left join v_KLRgn pklr on pklr.KLRgn_id = pa.KLRgn_id
				left join v_KLSubRgn pklsr on pklsr.KLSubRgn_id = pa.KLSubRgn_id
				left join v_KLCity pklc on pklc.KLCity_id = pa.KLCity_id
				left join v_KLTown pklt on pklt.KLTown_id = pa.KLTown_id
				left join v_KLStreet pkls on pkls.KLStreet_id = pa.KLStreet_id
			where
				p.Person_id = :Person_id
				and p.PersonEvn_id = :PersonEvn_id
		";

		$resp = $this->queryResult($query, array(
			'Person_id' => $data['Person_id'],
			'PersonEvn_id' => $data['PersonEvn_id']
		));

		if (!empty($resp[0]['UAddress_id'])) {
			if (
				!empty($resp[0]['UKLRgn_Actual'])
				|| !empty($resp[0]['UKLSubRgn_Actual'])
				|| !empty($resp[0]['UKLCity_Actual'])
				|| !empty($resp[0]['UKLTown_Actual'])
				|| !empty($resp[0]['UKLStreet_Actual'])
			) {
				return 'Некорректно указан адрес регистрации';
			}

			if (!empty($resp[0]['UAddress_Zip'])) {
				if (!preg_match('/^[1-9][0-9]{5}$/', $resp[0]['UAddress_Zip'])) {
					return 'Некорректно указан индекс в адресе регистрации';
				}
			}
		}

		if (!empty($resp[0]['PAddress_id'])) {
			if (
				!empty($resp[0]['PKLRgn_Actual'])
				|| !empty($resp[0]['PKLSubRgn_Actual'])
				|| !empty($resp[0]['PKLCity_Actual'])
				|| !empty($resp[0]['PKLTown_Actual'])
				|| !empty($resp[0]['PKLStreet_Actual'])
			) {
				return 'Некорректно указан адрес проживания';
			}

			if (!empty($resp[0]['PAddress_Zip'])) {
				if (!preg_match('/^[1-9][0-9]{5}$/', $resp[0]['PAddress_Zip'])) {
					return 'Некорректно указан индекс в адресе проживания';
				}
			}
		}

		return '';
	}

	/**
	 * Получение строки EvnPLDispTeenInspection по id
	 */
	function getFirstEvnPLDispTeenInspection($data)
	{
		return $this->getFirstRowFromQuery("
		  	select
				EvnClass_id as \"EvnClass_id\",
				EvnClass_Name as \"EvnClass_Name\",
				EvnPLDispTeenInspection_id as \"EvnPLDispTeenInspection_id\",
				EvnPLDispTeenInspection_setDate as \"EvnPLDispTeenInspection_setDate\",
				EvnPLDispTeenInspection_setTime as \"EvnPLDispTeenInspection_setTime\",
				EvnPLDispTeenInspection_didDate as \"EvnPLDispTeenInspection_didDate\",
				EvnPLDispTeenInspection_didTime as \"EvnPLDispTeenInspection_didTime\",
				EvnPLDispTeenInspection_disDate as \"EvnPLDispTeenInspection_disDate\",
				EvnPLDispTeenInspection_disTime as \"EvnPLDispTeenInspection_disTime\",
				EvnPLDispTeenInspection_pid as \"EvnPLDispTeenInspection_pid\",
				EvnPLDispTeenInspection_rid as \"EvnPLDispTeenInspection_rid\",
				Lpu_id as \"Lpu_id\",
				Server_id as \"Server_id\",
				PersonEvn_id as \"PersonEvn_id\",
				EvnPLDispTeenInspection_setDT as \"EvnPLDispTeenInspection_setDT\",
				EvnPLDispTeenInspection_disDT as \"EvnPLDispTeenInspection_disDT\",
				EvnPLDispTeenInspection_didDT as \"EvnPLDispTeenInspection_didDT\",
				EvnPLDispTeenInspection_insDT as \"EvnPLDispTeenInspection_insDT\",
				EvnPLDispTeenInspection_updDT as \"EvnPLDispTeenInspection_updDT\",
				EvnPLDispTeenInspection_Index as \"EvnPLDispTeenInspection_Index\",
				EvnPLDispTeenInspection_Count as \"EvnPLDispTeenInspection_Count\",
				pmUser_insID as \"pmUser_insID\",
				pmUser_updID as \"pmUser_updID\",
				Person_id as \"Person_id\",
				Morbus_id as \"Morbus_id\",
				EvnPLDispTeenInspection_IsSigned as \"EvnPLDispTeenInspection_IsSigned\",
				pmUser_signID as \"pmUser_signID\",
				EvnPLDispTeenInspection_signDT as \"EvnPLDispTeenInspection_signDT\",
				EvnPLDispTeenInspection_IsArchive as \"EvnPLDispTeenInspection_IsArchive\",
				EvnPLDispTeenInspection_Guid as \"EvnPLDispTeenInspection_Guid\",
				EvnPLDispTeenInspection_IndexMinusOne as \"EvnPLDispTeenInspection_IndexMinusOne\",
				EvnStatus_id as \"EvnStatus_id\",
				EvnPLDispTeenInspection_statusDate as \"EvnPLDispTeenInspection_statusDate\",
				EvnPLDispTeenInspection_IsTransit as \"EvnPLDispTeenInspection_IsTransit\",
				EvnPLDispTeenInspection_VizitCount as \"EvnPLDispTeenInspection_VizitCount\",
				EvnPLDispTeenInspection_IsFinish as \"EvnPLDispTeenInspection_IsFinish\",
				Person_Age as \"Person_Age\",
				EvnPLDispTeenInspection_isMseDirected as \"EvnPLDispTeenInspection_isMseDirected\",
				AttachType_id as \"AttachType_id\",
				Lpu_aid as \"Lpu_aid\",
				EvnPLDispTeenInspection_IsInReg as \"EvnPLDispTeenInspection_IsInReg\",
				EvnPLDispTeenInspection_consDT as \"EvnPLDispTeenInspection_consDT\",
				DispClass_id as \"DispClass_id\",
				EvnPLDispTeenInspection_fid as \"EvnPLDispTeenInspection_fid\",
				EvnPLDispTeenInspection_IsMobile as \"EvnPLDispTeenInspection_IsMobile\",
				Lpu_mid as \"Lpu_mid\",
				EvnPLDispTeenInspection_IsPaid as \"EvnPLDispTeenInspection_IsPaid\",
				EvnPLDispTeenInspection_IsRefusal as \"EvnPLDispTeenInspection_IsRefusal\",
				EvnPLDispTeenInspection_IndexRep as \"EvnPLDispTeenInspection_IndexRep\",
				EvnPLDispTeenInspection_IndexRepInReg as \"EvnPLDispTeenInspection_IndexRepInReg\",
				PayType_id as \"PayType_id\",
				Lpu_CodeSMO as \"Lpu_CodeSMO\",
				EvnPLDispTeenInspection_Percent as \"EvnPLDispTeenInspection_Percent\",
				MedStaffFact_id as \"MedStaffFact_id\",
				EvnPLDispTeenInspection_IsOutLpu as \"EvnPLDispTeenInspection_IsOutLpu\",
				EvnPLDispTeenInspection_IsSuspectZNO as \"EvnPLDispTeenInspection_IsSuspectZNO\",
				Diag_spid as \"Diag_spid\",
				EvnDirection_aid as \"EvnDirection_aid\",
				EvnPLDispTeenInspection_IsInRegZNO as \"EvnPLDispTeenInspection_IsInRegZNO\",
				Registry_sid as \"Registry_sid\",
				EvnPLDispTeenInspection_IsNewOrder as \"EvnPLDispTeenInspection_IsNewOrder\",
				EducationInstitutionClass_id as \"EducationInstitutionClass_id\",
				InstitutionNatureType_id as \"InstitutionNatureType_id\",
				InstitutionType_id as \"InstitutionType_id\",
				EvnPLDispTeenInspection_eduDT as \"EvnPLDispTeenInspection_eduDT\",
				EvnPLDispTeenInspection_IsTwoStage as \"EvnPLDispTeenInspection_IsTwoStage\",
				AgeGroupDisp_id as \"AgeGroupDisp_id\",
				Org_id as \"Org_id\",
				PersonDispOrp_id as \"PersonDispOrp_id\",
				EvnDirection_id as \"EvnDirection_id\"
		  	from v_EvnPLDispTeenInspection 
		  	where EvnPLDispTeenInspection_id = :EvnPLDispTeenInspection_id
		  	limit 1
		", $data, true);
	}

	/**
	 *	Сохранение карты мед. осмотра несовершеннолетнего
	 */
	public function saveEvnPLDispTeenInspection($data)
	{
		// Стартуем транзакцию, т.к. при сохранении карты МОН сохраняются и другие объекты
		$this->db->trans_begin();

		$savedData = array();
		if (!empty($data['EvnPLDispTeenInspection_id'])) {
			$savedData = $this->getFirstEvnPLDispTeenInspection($data);
			if ($savedData === false) {
				$this->db->trans_rollback();
				return array('Error_Msg' => 'Ошибка при получении данных карты');
			}
		}

		if ( in_array($data['DispClass_id'], array(10, 12)) && empty($data['AgeGroupDisp_id']) ) {
			$this->db->trans_rollback();
			return array('Error_Msg' => 'Не указана возрастная группа, сохранение невозможно');
		}

		if (getRegionNick() == 'krasnoyarsk') {
			if (!$data['checkAttributeforLpuSection']) {
				$checkDate = $this->checkAttributeforLpuSection(array(
					'EvnPLDispTeenInspection_id' => $data['EvnPLDispTeenInspection_id']
				));

				If ($checkDate[0] != "Ok") {
					return array('Error_Msg' => 'YesNo', 'Alert_Msg' => $checkDate, 'Error_Code' => 110);
				}

			}else if($data['checkAttributeforLpuSection']==2) {
				$data['EvnPLDispTeenInspection_IsMobile'] = 'on';
			}
		}

		// Если случай закрыт и задана дата справки, то сохраняем справку.
		if (!empty($data['EvnPLDispTeenInspection_id']) && !empty($data['EvnPLDispTeenInspection_IsFinish']) && $data['EvnPLDispTeenInspection_IsFinish'] == 2 && !empty($data['EvnCostPrint_setDT']))
		{
			// сохраняем справку
			$this->load->model('CostPrint_model');
			$this->CostPrint_model->saveEvnCostPrint(array(
				'Evn_id' => $data['EvnPLDispTeenInspection_id'],
				'CostPrint_IsNoPrint' => $data['EvnCostPrint_IsNoPrint'],
				'CostPrint_setDT' => $data['EvnCostPrint_setDT'],
				'pmUser_id' => $data['pmUser_id']
			));
		}

		// Если случай закрыт, то проверяем правильность адреса
		if (getRegionNick() != 'ufa' && !empty($data['EvnPLDispTeenInspection_IsFinish']) && $data['EvnPLDispTeenInspection_IsFinish'] == 2) {
			$checkResult = $this->checkPersonAddress($data);

			If ( !empty($checkResult) ) {
				$this->db->trans_rollback();
				return array('Error_Msg' => $checkResult);
			}
		}

		// получаем даты начала и конца услуг внутри диспансеризации.
		$minmaxdates = $this->getEvnUslugaDispDopMinMaxDates($data);
		if (is_array($minmaxdates)) {
			// $data['EvnPLDispTeenInspection_setDate'] = $minmaxdates['mindate'];
			$data['EvnPLDispTeenInspection_disDate'] = $minmaxdates['maxdate'];
		} else {
			// $data['EvnPLDispTeenInspection_setDate'] = date('Y-m-d');
			$data['EvnPLDispTeenInspection_disDate'] = date('Y-m-d');
		}

		// если не закончен дата окончания нулевая.
		if (empty($data['EvnPLDispTeenInspection_IsFinish']) || $data['EvnPLDispTeenInspection_IsFinish'] == 1) {
			$data['EvnPLDispTeenInspection_disDate'] = NULL;
		}

		if ($data['EvnPLDispTeenInspection_IsMobile']) { $data['EvnPLDispTeenInspection_IsMobile'] = 2; } else { $data['EvnPLDispTeenInspection_IsMobile'] = 1;	}
		if ($data['EvnPLDispTeenInspection_IsOutLpu']) { $data['EvnPLDispTeenInspection_IsOutLpu'] = 2; } else { $data['EvnPLDispTeenInspection_IsOutLpu'] = 1;	}

		// Проверки
		if (!in_array($data['session']['region']['nick'], array('astra','buryatiya','kareliya','penza','vologda'))) {
			// Проверки на допустимость сохранения карты на указанную дату
			$checkResult = $this->checkEvnPLDispTeenInspectionCanBeSaved($data, 'saveEvnPLDispTeenInspection');

			if ( !empty($checkResult) ) {
				$this->db->trans_rollback();
				return array(array('Error_Msg' => $checkResult));
			}
		}

		// Проверяем что нет карты ДДС
		$query = "
			SELECT
				epldo.EvnPLDispOrp_id as \"EvnPLDispOrp_id\"
			FROM
				v_EvnPLDispOrp epldo
				inner join v_PersonState ps on ps.Person_id = epldo.Person_id
			WHERE
				epldo.Person_id = :Person_id
				and date_part('year', epldo.EvnPLDispOrp_setDT) = date_part('year', CAST(:EvnPLDispTeenInspection_setDate as date))
				and epldo.EvnPLDispOrp_IsFinish = 2 -- закрытый
				and dbo.Age2(ps.Person_BirthDay, :EvnPLDispTeenInspection_setDate) >= 3 -- 3 лет и старше
			limit 1
		";
		$checkResult = $this->queryResult($query, array(
			'Person_id' => $data['Person_id'],
			'EvnPLDispTeenInspection_setDate' => $data['EvnPLDispTeenInspection_setDate']
		));
		if (!empty($checkResult[0]['EvnPLDispTeenInspection_id'])) {
			$this->db->trans_rollback();
			return array('Error_Msg' => 'На выбранного пациента в текущем году уже сохранена Карта диспансеризации несовершеннолетнего (ДДС).');
		}

		$data['MedStaffFact_id'] = !empty($data['session']['CurARM']['MedStaffFact_id'])?$data['session']['CurARM']['MedStaffFact_id']:null;

		if (empty($data['EvnDirection_id']) && !empty($savedData) && !empty($savedData['EvnDirection_id'])) {
			$data['EvnDirection_id'] = $savedData['EvnDirection_id'];
		}

		if (empty($data['EvnDirection_id'])) $data['EvnDirection_id'] = null;

		if (in_array($data['DispClass_id'], array(10, 12))) {
			$this->checkZnoDirection($data, 'EvnPLDispTeenInspection');
		}

		$query = "
			with mv as (
				select
					case when :EvnPLDispTeenInspection_id is not null
						then EvnPLDispTeenInspection_IsRefusal
						else null
					end as EvnPLDispTeenInspection_IsRefusal,
					case when :EvnPLDispTeenInspection_id is not null
						then EvnDirection_aid
						else null
					end as EvnDirection_aid
				from v_EvnPLDispTeenInspection
				where EvnPLDispTeenInspection_id = :EvnPLDispTeenInspection_id
				limit 1
			)
			
			select
				EvnPLDispTeenInspection_id as \"EvnPLDispTeenInspection_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_EvnPLDispTeenInspection_upd(
				EvnPLDispTeenInspection_id := :EvnPLDispTeenInspection_id,
				MedStaffFact_id := :MedStaffFact_id,
				EvnPLDispTeenInspection_IndexRep := :EvnPLDispTeenInspection_IndexRep,
				EvnPLDispTeenInspection_IndexRepInReg := :EvnPLDispTeenInspection_IndexRepInReg,
				PersonDispOrp_id := :PersonDispOrp_id,
				EvnPLDispTeenInspection_pid := null,
				Lpu_id := :Lpu_id, 
				Server_id := :Server_id, 
				PersonEvn_id := :PersonEvn_id, 
				EvnPLDispTeenInspection_setDT := :EvnPLDispTeenInspection_setDate,
				EvnPLDispTeenInspection_disDT := :EvnPLDispTeenInspection_disDate,
				EvnPLDispTeenInspection_didDT := null, 
				Morbus_id := null, 
				EvnPLDispTeenInspection_IsSigned := null, 
				pmUser_signID := null, 
				EvnPLDispTeenInspection_signDT := null, 
				EvnPLDispTeenInspection_VizitCount := null, 
				EvnPLDispTeenInspection_IsFinish := :EvnPLDispTeenInspection_IsFinish, 
				EvnPLDispTeenInspection_IsTwoStage := :EvnPLDispTeenInspection_IsTwoStage,
				Person_Age := null, 
				AttachType_id := 2,
				Lpu_aid := null, 
				EvnPLDispTeenInspection_consDT := :EvnPLDispTeenInspection_consDate, 
				EvnPLDispTeenInspection_IsMobile := :EvnPLDispTeenInspection_IsMobile, 
				EvnPLDispTeenInspection_IsOutLpu := :EvnPLDispTeenInspection_IsOutLpu, 
				Lpu_mid := :Lpu_mid, 
				DispClass_id := :DispClass_id,
				PayType_id := :PayType_id,
				EvnPLDispTeenInspection_fid := null, 
				EducationInstitutionClass_id := :EducationInstitutionClass_id, 
				AgeGroupDisp_id := :AgeGroupDisp_id, 
				Org_id := :Org_id, 
				InstitutionNatureType_id := :InstitutionNatureType_id, 
				InstitutionType_id := :InstitutionType_id, 
				EvnPLDispTeenInspection_eduDT := :EvnPLDispTeenInspection_eduDT,
				EvnPLDispTeenInspection_IsRefusal := (select EvnPLDispTeenInspection_IsRefusal from mv),
				EvnDirection_aid := (select EvnDirection_aid from mv),
				EvnPLDispTeenInspection_IsSuspectZNO := :EvnPLDispTeenInspection_IsSuspectZNO,
				Diag_spid := :Diag_spid,
				pmUser_id := :pmUser_id
			)
		";
		$result = $this->db->query($query, $data);

		if (!is_object($result))
		{
			$this->db->trans_rollback();
			return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение осмотра)');
		}

		$response = $result->result('array');

		if (!is_array($response) || count($response) == 0)
		{
			$this->db->trans_rollback();
			return false;
		}
		else if (!empty($response[0]['Error_Msg']))
		{
			$this->db->trans_rollback();
			return $response[0];
		}

		if ( !isset($data['EvnPLDispTeenInspection_id']) )
		{
			$data['EvnPLDispTeenInspection_id'] = $response[0]['EvnPLDispTeenInspection_id'];
		}

		// Ищем AssessmentHealth связанный с EvnPLDispTeenInspection_id, если нет его то добавляем новый, иначе обновляем
		$data['AssessmentHealth_id'] = NULL;
		$query = "
			select
				AssessmentHealth_id as \"AssessmentHealth_id\"
			from v_AssessmentHealth
			where EvnPLDisp_id = :EvnPLDispTeenInspection_id
			limit 1
		";
		$result = $this->db->query($query, $data);
		if (is_object($result))
		{
			$resp = $result->result('array');
			if (count($resp) > 0) {
				$data['AssessmentHealth_id'] = $resp[0]['AssessmentHealth_id'];
			}
		}

		// запихивание чекбоксов в YesNo
		if ($data['AssessmentHealth_IsRegular']) { $data['AssessmentHealth_IsRegular'] = 2; } else { $data['AssessmentHealth_IsRegular'] = 1;	}
		if ($data['AssessmentHealth_IsIrregular']) { $data['AssessmentHealth_IsIrregular'] = 2; } else { $data['AssessmentHealth_IsIrregular'] = 1;	}
		if ($data['AssessmentHealth_IsAbundant']) { $data['AssessmentHealth_IsAbundant'] = 2; } else { $data['AssessmentHealth_IsAbundant'] = 1;	}
		if ($data['AssessmentHealth_IsModerate']) { $data['AssessmentHealth_IsModerate'] = 2; } else { $data['AssessmentHealth_IsModerate'] = 1;	}
		if ($data['AssessmentHealth_IsScanty']) { $data['AssessmentHealth_IsScanty'] = 2; } else { $data['AssessmentHealth_IsScanty'] = 1;	}
		if ($data['AssessmentHealth_IsPainful']) { $data['AssessmentHealth_IsPainful'] = 2; } else { $data['AssessmentHealth_IsPainful'] = 1;	}
		if ($data['AssessmentHealth_IsPainless']) { $data['AssessmentHealth_IsPainless'] = 2; } else { $data['AssessmentHealth_IsPainless'] = 1;	}

		if ($data['AssessmentHealth_IsMental']) { $data['AssessmentHealth_IsMental'] = 2; } else { $data['AssessmentHealth_IsMental'] = 1;	}
		if ($data['AssessmentHealth_IsOtherPsych']) { $data['AssessmentHealth_IsOtherPsych'] = 2; } else { $data['AssessmentHealth_IsOtherPsych'] = 1;	}
		if ($data['AssessmentHealth_IsLanguage']) { $data['AssessmentHealth_IsLanguage'] = 2; } else { $data['AssessmentHealth_IsLanguage'] = 1;	}
		if ($data['AssessmentHealth_IsVestibular']) { $data['AssessmentHealth_IsVestibular'] = 2; } else { $data['AssessmentHealth_IsVestibular'] = 1;	}
		if ($data['AssessmentHealth_IsVisual']) { $data['AssessmentHealth_IsVisual'] = 2; } else { $data['AssessmentHealth_IsVisual'] = 1;	}
		if ($data['AssessmentHealth_IsMeals']) { $data['AssessmentHealth_IsMeals'] = 2; } else { $data['AssessmentHealth_IsMeals'] = 1;	}
		if ($data['AssessmentHealth_IsMotor']) { $data['AssessmentHealth_IsMotor'] = 2; } else { $data['AssessmentHealth_IsMotor'] = 1;	}
		if ($data['AssessmentHealth_IsDeform']) { $data['AssessmentHealth_IsDeform'] = 2; } else { $data['AssessmentHealth_IsDeform'] = 1;	}
		if ($data['AssessmentHealth_IsGeneral']) { $data['AssessmentHealth_IsGeneral'] = 2; } else { $data['AssessmentHealth_IsGeneral'] = 1;	}

		$this->load->model('AssessmentHealth_model');
		$data['scenario'] = swModel::SCENARIO_DO_SAVE;
		$data['EvnPLDisp_id'] = $data['EvnPLDispTeenInspection_id'];
		$response = $this->AssessmentHealth_model->doSave($data);

		// Сохраняем скрытую услугу для Бурятии, если случай закончен
		// @task https://redmine.swan.perm.ru/issues/52175
		// Добавлен Крым
		// @task https://redmine.swan.perm.ru/issues/88196
		if (
			in_array($data['session']['region']['nick'], array('buryatiya', 'krym')) && !empty($data['EvnPLDispTeenInspection_id'])
			&& !empty($data['EvnPLDispTeenInspection_IsFinish']) && $data['EvnPLDispTeenInspection_IsFinish'] == 2
		) {
			// Ищем существующую услугу
			$query = "
				select
					EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\",
					UslugaComplex_id as \"UslugaComplex_id\",
					PayType_id as \"PayType_id\",
					to_char(EvnUslugaDispDop_setDT, 'dd.mm.yyyy') as \"EvnUslugaDispDop_setDate\"
				from v_EvnUslugaDispDop
				where EvnUslugaDispDop_pid = :EvnUslugaDispDop_pid
					and EvnUslugaDispDop_IsVizitCode = 2
				limit 1
			";
			$result = $this->db->query($query, array(
				'EvnUslugaDispDop_pid' => $data['EvnPLDispTeenInspection_id']
			));

			if ( !is_object($result) ) {
				$this->db->trans_rollback();
				return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (поиск услуги)');
			}

			$response = $result->result('array');

			if ( is_array($response) && count($response) > 0 ) {
				$uslugaData = $response[0];
			}
			else {
				$uslugaData = array();
			}

			$filters = "";
			$addit_query = "";

			$params = array(
				'DispClass_id' => $data['DispClass_id'],
				'EvnPLDispTeenInspection_setDT' => $data['EvnPLDispTeenInspection_setDate'],
				'Person_id' => $data['Person_id']
			);

			if (in_array($data['DispClass_id'], array(6,9))) {
				$addit_query .= "
					, adit as (
					select
						eic.EducationInstitutionType_id as EducationInstitutionType_id
					from v_EducationInstitutionClass eic
					where eic.EducationInstitutionClass_id = :EducationInstitutionClass_id
					limit 1)
				";
				$filters .= " and USL.EducationInstitutionType_id = (select EducationInstitutionType_id from adit)";
				$params['EducationInstitutionClass_id'] = $data['EducationInstitutionClass_id'];
			}

			if (in_array($data['DispClass_id'], array(10,12))) {
				$lowerAgeLimit = ($data['session']['region']['nick'] == 'krym' ? 2 : 3);
				$higherAgeLimit = $lowerAgeLimit + 1;

				// Определяем UslugaComplex_id через $schema.UslugaSurveyLink
				$query = "
					with mv as (
						select
							coalesce(Sex_id, 3) as sex_id,
							dbo.Age_newborn_2(Person_BirthDay, coalesce(:EvnPLDispTeenInspection_setDT, dbo.tzGetDate())) as age,
							dbo.Age2(Person_BirthDay, cast(date_part('year', coalesce(:EvnPLDispTeenInspection_setDT, dbo.tzGetDate())) || '-12-31' as date)) as age_year
						from v_PersonState
						where ps.Person_id = :Person_id
						limit 1
					){$addit_query}

					select
						USL.UslugaComplex_id as \"UslugaComplex_id\"
					from {$data['session']['region']['schema']}.v_UslugaSurveyLink USL
					where
						USL.DispClass_id = :DispClass_id
						and coalesce(USL.Sex_id, (select sex_id from mv)) = (select sex_id from mv)
						and (
							((select age_year from mv) >= {$higherAgeLimit}
								and (select age_year from mv) between USL.UslugaSurveyLink_From and USL.UslugaSurveyLink_To)
							or ((select age_year from mv) <= {$lowerAgeLimit}
								and (select age from mv) between (coalesce(USL.UslugaSurveyLink_From, 0) * 12 + coalesce(USL.UslugaSurveyLink_monthFrom, 0))
									and (coalesce(USL.UslugaSurveyLink_To, 999) * 12 + coalesce(USL.UslugaSurveyLink_monthTo, 11)))
						)
						and coalesce(USL.UslugaSurveyLink_IsDel, 1) = 1
						and (USL.UslugaSurveyLink_begDate is null or USL.UslugaSurveyLink_begDate <= :EvnPLDispTeenInspection_setDT)
						and (USL.UslugaSurveyLink_endDate is null or USL.UslugaSurveyLink_endDate >= :EvnPLDispTeenInspection_setDT)
						{$filters}
					limit 1
				";
			} else {
				// Определяем UslugaComplex_id через $schema.UslugaSurveyLink
				$query = "
					with mv as(
						select
							coalesce(Sex_id, 3) as sex_id,
							dbo.Age_newborn_2(Person_BirthDay, coalesce(:EvnPLDispTeenInspection_setDT, dbo.tzGetDate())) as age,
						from v_PersonState ps
						where ps.Person_id = :Person_id
						limit 1
					){$addit_query}

					select USL.UslugaComplex_id as \"UslugaComplex_id\"
					from {$data['session']['region']['schema']}.v_UslugaSurveyLink USL
					where
						USL.DispClass_id = :DispClass_id
						and coalesce(USL.Sex_id, (select sex_id from mv)) = (select sex_id from mv)
						and (select age from mv) between (coalesce(USL.UslugaSurveyLink_From, 0)*12+coalesce(USL.UslugaSurveyLink_monthFrom, 0)) and (coalesce(USL.UslugaSurveyLink_To, 999)*12+coalesce(USL.UslugaSurveyLink_monthTo, 0))
						and coalesce(USL.UslugaSurveyLink_IsDel, 1) = 1
						and (USL.UslugaSurveyLink_begDate is null or USL.UslugaSurveyLink_begDate <= :EvnPLDispTeenInspection_setDT)
						and (USL.UslugaSurveyLink_endDate is null or USL.UslugaSurveyLink_endDate >= :EvnPLDispTeenInspection_setDT)
						{$filters}
					limit 1
				";
			}
			$result = $this->db->query($query, $params);

			if ( !is_object($result) ) {
				$this->db->trans_rollback();
				return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (получение идентификатора)');
			}

			$response = $result->result('array');

			if ( is_array($response) && count($response) > 0 ) {
				$UslugaComplex_id = $response[0]['UslugaComplex_id'];
			}
			else {
				$UslugaComplex_id = null;
			}

			// Добавляем/обновляем при необходимости
			if ( !empty($UslugaComplex_id) ) {
				$query = "
					with mv as (
						select
							case when :PayType_id is null
								then PayType_id
								else :PayType_id
							end as pt
						from v_PayType
						where PayType_SysNick = 'dopdisp'
					)
					
					select
						EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\",
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from p_EvnUslugaDispDop_" . (!empty($uslugaData['EvnUslugaDispDop_id']) ? "upd" : "ins") . "(
						EvnUslugaDispDop_id := :EvnUslugaDispDop_id,
						EvnUslugaDispDop_pid := :EvnUslugaDispDop_pid,
						UslugaComplex_id := :UslugaComplex_id,
						EvnUslugaDispDop_setDT := :EvnUslugaDispDop_setDT,
						EvnUslugaDispDop_IsVizitCode := 2,
						Lpu_id := :Lpu_id,
						Server_id := :Server_id,
						PersonEvn_id := :PersonEvn_id,
						PayType_id := (select pt from mv),
						UslugaPlace_id := 1,
						pmUser_id := :pmUser_id
					)
				";
				$result = $this->db->query($query, array(
					'EvnUslugaDispDop_id' => (!empty($uslugaData['EvnUslugaDispDop_id']) ? $uslugaData['EvnUslugaDispDop_id'] : null),
					'EvnUslugaDispDop_pid' => $data['EvnPLDispTeenInspection_id'],
					'UslugaComplex_id' => $UslugaComplex_id,
					'EvnUslugaDispDop_setDT' => (!empty($data['EvnPLDispTeenInspection_setDate']) ? $data['EvnPLDispTeenInspection_setDate'] : null),
					'Lpu_id' => $data['Lpu_id'],
					'Server_id' => $data['Server_id'],
					'PersonEvn_id' => $data['PersonEvn_id'],
					'PayType_id' => (!empty($uslugaData['PayType_id']) ? $uslugaData['PayType_id'] : null),
					'pmUser_id' => $data['pmUser_id']
				));

				if ( !is_object($result) ) {
					$this->db->trans_rollback();
					return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление услуги) (' . __LINE__ . ')');
				}

				$response = $result->result('array');

				if ( !is_array($response) || count($response) == 0 ) {
					$this->db->trans_rollback();
					return array('Error_Msg' => 'Ошибка при удалении услуги (' . __LINE__ . ')');
				}
				else if ( !empty($response[0]['Error_Msg']) ) {
					$this->db->trans_rollback();
					return array('Error_Msg' => $response[0]['Error_Msg'] . ' (' . __LINE__ . ')');
				}
			}
			// Удаляем
			else if ( !empty($uslugaData['EvnUslugaDispDop_id']) ) {
				$query = "
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from p_EvnUslugaDispDop_del(
						EvnUslugaDispDop_id := :EvnUslugaDispDop_id,
						pmUser_id := :pmUser_id
					)
				";
				$result = $this->db->query($query, array(
					'EvnUslugaDispDop_id' => $uslugaData['EvnUslugaDispDop_id'],
					'pmUser_id' => $data['pmUser_id']
				));

				if ( !is_object($result) ) {
					$this->db->trans_rollback();
					return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление услуги) (' . __LINE__ . ')');
				}

				$response = $result->result('array');

				if ( !is_array($response) || count($response) == 0 ) {
					$this->db->trans_rollback();
					return array('Error_Msg' => 'Ошибка при удалении услуги (' . __LINE__ . ')');
				}
				else if ( !empty($response[0]['Error_Msg']) ) {
					$this->db->trans_rollback();
					return array('Error_Msg' => $response[0]['Error_Msg'] . ' (' . __LINE__ . ')');
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
					$record['EvnPLDisp_id'] = $data['EvnPLDispTeenInspection_id'];
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
			!empty($data['EvnPLDispTeenInspection_IsFinish']) && $data['EvnPLDispTeenInspection_IsFinish'] == 2 && (
				empty($savedData) || $savedData['EvnPLDispTeenInspection_IsFinish'] != 2
			)
		);

		if (getRegionNick() == 'penza' && (empty($savedData) || $justClosed)) {
			//Отправить человека в очередь на идентификацию
			$this->load->model('Person_model', 'pmodel');
			$this->pmodel->isAllowTransaction = false;
			$resTmp = $this->pmodel->addPersonRequestData(array(
				'Person_id' => $data['Person_id'],
				'Evn_id' => $data['EvnPLDispTeenInspection_id'],
				'pmUser_id' => $data['pmUser_id'],
				'PersonRequestSourceType_id' => 3,
			));
			$this->pmodel->isAllowTransaction = true;
			if (!$this->isSuccessful($resTmp) && !in_array($resTmp[0]['Error_Code'], array(302, 303))) {
				$this->db->trans_rollback();
				return array('Error_Msg' => $resTmp[0]['Error_Msg']);
			}
		}

		$this->db->trans_commit();

		return array(0 => array('EvnPLDispTeenInspection_id' => $data['EvnPLDispTeenInspection_id'], 'Error_Msg' => ''));
	}

	/**
	 * Возвращает данные
	 */
	function getEvnUslugaDispDopForEvnVizit($EvnVizitDispDop_id) {
		$query = "
			select
				EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\"
			from
				v_EvnUslugaDispDop
			where
				EvnUslugaDispDop_pid = :EvnVizitDispDop_id
			limit 1
		";

		$result = $this->db->query($query, array(
			'EvnVizitDispDop_id' => $EvnVizitDispDop_id
		));

		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return $resp[0]['EvnUslugaDispDop_id'];
			}
		}

		return null;
	}

	/**
	 * Возвращает данные
	 */
	function getUslugaComplexForDopDispInfoConsent($DopDispInfoConsent_id)
	{
		$params = array(
			'DopDispInfoConsent_id' => $DopDispInfoConsent_id
		);

		$query = "
			select
				stl.UslugaComplex_id as \"UslugaComplex_id\"
			from
				DopDispInfoConsent ddic
				inner join v_SurveyTypeLink stl on stl.SurveyTypeLink_id = ddic.SurveyTypeLink_id
			where
				DopDispInfoConsent_id = :DopDispInfoConsent_id
		";

		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return $resp[0]['UslugaComplex_id'];
			}
		}

		return null;
	}

	/**
	 * Возвращает данные
	 */
	function getMedCareForEvnVizitDispOrp($EvnVizitDispOrp_id, $MedCareType_id) {
		$query = "
			select
				MedCare_id as \"MedCare_id\"
			from
				v_MedCare
			where
				EvnVizitDisp_id = :EvnVizitDispOrp_id
				and MedCareType_id = :MedCareType_id
			limit 1
		";

		$result = $this->db->query($query, array(
			'EvnVizitDispOrp_id' => $EvnVizitDispOrp_id,
			'MedCareType_id' => $MedCareType_id
		));

		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return $resp[0]['MedCare_id'];
			}
		}

		return null;
	}

	/**
	 *	Сохранение карты мед. осмотра несовершеннолетнего 2 этапа
	 */
	function saveEvnPLDispTeenInspectionSec($data)
	{
		// begin проверки перенесённые с формы
		// получаем возраст пациента
		$resp_ps = $this->queryResult("
			select
				dbo.Age2(PS.Person_BirthDay, cast(:EvnPLDispTeenInspection_consDate as date)) as \"Person_Age\",
				dbo.Age2(PS.Person_BirthDay, cast(:EndYearDate as date)) as \"Person_Age_EndYear\",
				to_char(epldti.EvnPLDispTeenInspection_setDate, 'yyyy-mm-dd') as \"EvnPLDispTeenInspection_firSetDate\"
			from
				v_PersonState PS
				left join v_EvnPLDispTeenInspection epldti on epldti.EvnPLDispTeenInspection_id = :EvnPLDispTeenInspection_fid
			where
				PS.Person_id = :Person_id
			limit 1
		", array(
			'EvnPLDispTeenInspection_consDate' => $data['EvnPLDispTeenInspection_consDate'],
			'EndYearDate' => substr($data['EvnPLDispTeenInspection_setDate'], 0, 4) . '-12-31',
			'EvnPLDispTeenInspection_fid' => $data['EvnPLDispTeenInspection_fid'],
			'Person_id' => $data['Person_id']
		));

		if (!isset($resp_ps[0]['Person_Age'])) {
			return array('Error_Msg' => 'Ошибка при определении возраста пациента');
		}

		$Person_Age = $resp_ps[0]['Person_Age'];
		$Person_Age_EndYear = $resp_ps[0]['Person_Age_EndYear'];
		$EvnPLDispTeenInspection_firSetDate = $resp_ps[0]['EvnPLDispTeenInspection_firSetDate'];

		$pedcodes = array('01090128');
		if (in_array(getRegionNick(), ['perm', 'krasnoyarsk', 'yaroslavl'])) {
			if ( $data['EvnPLDispTeenInspection_consDate'] >= '2018-01-01' ) {
				$pedcodes = array(); // https://redmine.swan-it.ru/issues/162413
				$resp_uc = $this->queryResult("
					select distinct
						uc.UslugaComplex_Code as \"UslugaComplex_Code\"
					from
						v_SurveyTypeLink stl
						inner join v_SurveyType st on st.SurveyType_id = stl.SurveyType_id
						inner join v_UslugaComplex uc on uc.UslugaComplex_id = stl.UslugaComplex_id
					where
						st.SurveyType_Code = '27'
						and stl.DispClass_id = 10
						and stl.SurveyTypeLink_begDate <= :EvnPLDispTeenInspection_consDate
						and coalesce(stl.SurveyTypeLink_endDate, :EvnPLDispTeenInspection_consDate) >=  :EvnPLDispTeenInspection_consDate
				", array(
					'EvnPLDispTeenInspection_consDate' => $data['EvnPLDispTeenInspection_consDate']
				));
				foreach($resp_uc as $one_uc) {
					$pedcodes[] = $one_uc['UslugaComplex_Code'];
				}
			}
			else {
				$pedcodes = array('B04.031.002','B04.031.004','B04.026.002'); // https://redmine.swan.perm.ru/issues/56948 добавил коды B04.031.004 и B04.026.002
			}
		} else if (getRegionNick() == 'ekb') {
			$pedcodes = array('B04.031.002', 'B04.000.002', 'B04.026.002');
		} else if (getRegionNick() == 'astra'|| getRegionNick() == 'vologda') {
			$pedcodes = array('B04.026.002','B04.031.004');
		} else if (getRegionNick() == 'pskov') {
			$pedcodes = array('B04.031.001');
		} else if (getRegionNick() == 'krym') {
			$pedcodes = array('B04.031.001');
		} else if (getRegionNick() == 'buryatiya') {
			$pedcodes = array('161014', '161078', '161150');
		} else if (getRegionNick() == 'ufa') {
			$pedcodes = array('B04.031.002','B04.031.004','B04.026.002');
		}

		// Вытаскиваем минимальную и максимальную дату осмотра и дату осмотра врачом терапевтом
		$EvnVizitDispDop_pedDate = null;
		$EvnVizitDispDop_maxDate = null;
		$EvnVizitDispDop_minDate = null;

		if ( !empty($data['EvnPLDispTeenInspection_id']) && (!is_array($data['EvnVizitDispDop']) || count($data['EvnVizitDispDop']) == 0) ) {
			$data['EvnVizitDispDop'] = $this->queryResult("
				select
					to_char(EVDO.EvnVizitDispDop_setDT, 'yyyy-mm-dd') as \"EvnVizitDispDop_setDate\",
					UC.UslugaComplex_Code as \"UslugaComplex_Code\",
					1 as \"Record_Status\"
				from
					v_EvnVizitDispDop EVDO
					inner join v_UslugaComplex UC on UC.UslugaComplex_id = EVDO.UslugaComplex_id 
				where
					EVDO.EvnVizitDispDop_pid = :EvnPLDispTeenInspection_id
			", $data);
		}

		if ( !is_array($data['EvnVizitDispDop']) ) {
			$data['EvnVizitDispDop'] = array();
		}

		foreach ($data['EvnVizitDispDop'] as $record) {
			if((int)$record['Record_Status'] == 3) continue;// #136178
			if (!empty($record['UslugaComplex_Code']) && in_array($record['UslugaComplex_Code'], $pedcodes)) {
				$EvnVizitDispDop_pedDate = $record['EvnVizitDispDop_setDate'];
			} else {
				if (empty($EvnVizitDispDop_maxDate) || $EvnVizitDispDop_maxDate < $record['EvnVizitDispDop_setDate']) {
					$EvnVizitDispDop_maxDate = $record['EvnVizitDispDop_setDate'];
				}

				if (empty($EvnVizitDispDop_minDate) || $EvnVizitDispDop_minDate > $record['EvnVizitDispDop_setDate']) {
					$EvnVizitDispDop_minDate = $record['EvnVizitDispDop_setDate'];
				}
			}
		}

		if (!in_array(getRegionNick(), array('kareliya', 'penza', 'pskov', 'adygeya')) && empty($EvnVizitDispDop_pedDate) && (int)$data['EvnPLDispTeenInspection_IsFinish'] == 2) {
			if ( getRegionNick() == 'perm' && $data['EvnPLDispTeenInspection_consDate'] >= '2018-01-01' ) {
				$Error_Msg = 'Карта должна содержать осмотр врача-педиатра либо осмотр врача общей практики';
			}
			else if ( getRegionNick() == 'astra' ) {
				$Error_Msg = 'Карта должна содержать осмотр участкового врача-педиатра или врача общей практики (семейного врача). Проверьте корректность заполнения карты.';
			}
			else {
				$Error_Msg = 'Случай не может быть закончен, так как не сохранен осмотр врача-педиатра (ВОП) (' . implode(', ', $pedcodes) . ')';
			}

			return array('Error_Msg' => $Error_Msg);
		}


		// https://redmine.swan.perm.ru/issues/20485
		if ( !empty($EvnVizitDispDop_pedDate) && strtotime($EvnVizitDispDop_pedDate) - 63*24*60*60 > strtotime($EvnPLDispTeenInspection_firSetDate) && empty($data['ignoreOsmotrDlit']) ) {
			if ( $this->regionNick == 'krym' ) {
				return array('Error_Msg' => 'YesNo', 'Alert_Msg' => 'Длительность 1 и 2 этапов профилактического осмотра несовершеннолетнего не может быть больше 45 рабочих дней. Продолжить сохранение?', 'Error_Code' => 110);
			}
			else {
				return array('Error_Msg' => 'Длительность 1 и 2 этапов профилактического осмотра несовершеннолетнего не может быть больше 45 рабочих дней.');
			}
		}

		// https://redmine.swan.perm.ru/issues/20499
		if ( !empty($EvnVizitDispDop_pedDate) && $EvnVizitDispDop_pedDate < $data['EvnPLDispTeenInspection_consDate'] ) {
			return array('Error_Msg' => 'Дата осмотра врача-педиатра не может быть раньше, чем дата начала профилактического осмотра.');
		}

		// Вытаскиваем минимальную и максимальную дату услуги, а также дату проведения флюорографии
		$EvnUslugaDispDop_maxDate = null;
		$EvnUslugaDispDop_minDate = null;
		foreach ($data['EvnUslugaDispDop'] as $record) {
			if (empty($EvnUslugaDispDop_maxDate) || $EvnUslugaDispDop_maxDate < $record['EvnUslugaDispDop_setDate']) {
				$EvnUslugaDispDop_maxDate = $record['EvnUslugaDispDop_setDate'];
			}

			if (empty($EvnUslugaDispDop_minDate) || $EvnUslugaDispDop_minDate > $record['EvnUslugaDispDop_setDate']) {
				$EvnUslugaDispDop_minDate = $record['EvnUslugaDispDop_setDate'];
			}
		}

		// Получаем максимальную дату осмотра/исследования
		$maxDate = null;
		$minDate = null;
		if (!empty($EvnVizitDispDop_maxDate)) {
			$maxDate = $EvnVizitDispDop_maxDate;
		}
		if (empty($maxDate) || (!empty($EvnUslugaDispDop_maxDate) && $maxDate < $EvnUslugaDispDop_maxDate)) {
			$maxDate = $EvnUslugaDispDop_maxDate;
		}
		if (!empty($EvnVizitDispDop_minDate)) {
			$minDate = $EvnVizitDispDop_minDate;
		}
		if (empty($minDate) || (!empty($EvnUslugaDispDop_minDate) && $minDate > $EvnUslugaDispDop_minDate)) {
			$minDate = $EvnUslugaDispDop_minDate;
		}

		// https://redmine.swan.perm.ru/issues/20485
		if ( !empty($maxDate) && !empty($EvnVizitDispDop_pedDate) && $maxDate > $EvnVizitDispDop_pedDate ) {
			return array('Error_Msg' => 'Дата любого осмотра/исследования не может быть больше даты осмотра врача-педиатра (ВОП).');
		}

		// https://redmine.swan.perm.ru/issues/20485
		if (!empty($minDate) && !empty($EvnVizitDispDop_pedDate) && DateTime::createFromFormat('Y-m-d', $minDate) < DateTime::createFromFormat('Y-m-d', $EvnVizitDispDop_pedDate)->sub(new DateInterval('P'.($Person_Age < 2 ? 1 : 3).'M'))) {
			return array('Error_Msg' => 'Дата любого исследования не может быть раньше, чем ' . ($Person_Age < 2 ? "1 месяц" : "3 месяца") . ' до даты осмотра врача-педиатра.');
		}
		// end проверки перенесённые с формы

		$savedData = array();
		if (!empty($data['EvnPLDispTeenInspection_id'])) {
			$savedData = $this->getFirstEvnPLDispTeenInspection($data);
			if ($savedData === false) {
				return array('Error_Msg' => 'Ошибка при получении данных карты');
			}
		}

		$this->load->model('EvnUsluga_model');
		// Если случай закрыт и задана дата справки, то сохраняем справку.
		if (!empty($data['EvnPLDispTeenInspection_id']) && !empty($data['EvnPLDispTeenInspection_IsFinish']) && $data['EvnPLDispTeenInspection_IsFinish'] == 2 && !empty($data['EvnCostPrint_setDT']))
		{
			// сохраняем справку
			$this->load->model('CostPrint_model');
			$this->CostPrint_model->saveEvnCostPrint(array(
				'Evn_id' => $data['EvnPLDispTeenInspection_id'],
				'CostPrint_IsNoPrint' => $data['EvnCostPrint_IsNoPrint'],
				'CostPrint_setDT' => $data['EvnCostPrint_setDT'],
				'pmUser_id' => $data['pmUser_id']
			));
		}

		// получаем даты начала и конца услуг внутри диспансеризации.
		$minmaxdates = $this->getEvnUslugaDispDopMinMaxDates($data);
		if (is_array($minmaxdates)) {
			// $data['EvnPLDispTeenInspection_setDate'] = $minmaxdates['mindate'];
			$data['EvnPLDispTeenInspection_disDate'] = $minmaxdates['maxdate'];
		} else {
			// $data['EvnPLDispTeenInspection_setDate'] = date('Y-m-d');
			$data['EvnPLDispTeenInspection_disDate'] = date('Y-m-d');
		}

		// если не закончен дата окончания нулевая.
		if (empty($data['EvnPLDispTeenInspection_IsFinish']) || $data['EvnPLDispTeenInspection_IsFinish'] == 1) {
			$data['EvnPLDispTeenInspection_disDate'] = NULL;
		}

		if ($data['EvnPLDispTeenInspection_IsMobile']) { $data['EvnPLDispTeenInspection_IsMobile'] = 2; } else { $data['EvnPLDispTeenInspection_IsMobile'] = 1;	}
		if ($data['EvnPLDispTeenInspection_IsOutLpu']) { $data['EvnPLDispTeenInspection_IsOutLpu'] = 2; } else { $data['EvnPLDispTeenInspection_IsOutLpu'] = 1;	}

		$proc = "p_EvnPLDispTeenInspection_ins";
		if (!empty($data['EvnPLDispTeenInspection_id'])) {
			$proc = "p_EvnPLDispTeenInspection_upd";
		}

		$data['MedStaffFact_id'] = !empty($data['session']['CurARM']['MedStaffFact_id'])?$data['session']['CurARM']['MedStaffFact_id']:null;

		if (in_array($data['DispClass_id'], array(10, 12))) {
			$this->checkZnoDirection($data, 'EvnPLDispTeenInspection');
		}

		$query = "
			with mv as (
				select
					case when :EvnPLDispTeenInspection_id is not null
						then EvnPLDispTeenInspection_IsRefusal
						else null
					end as EvnPLDispTeenInspection_IsRefusal,
					case when :EvnPLDispTeenInspection_id is not null
						then EvnDirection_aid
						else null
					end as EvnDirection_aid
				from v_EvnPLDispTeenInspection
				where EvnPLDispTeenInspection_id = :EvnPLDispTeenInspection_id
				limit 1
			)
			
			select
				EvnPLDispTeenInspection_id as \"EvnPLDispTeenInspection_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$proc}(
				EvnPLDispTeenInspection_id := :EvnPLDispTeenInspection_id,
				MedStaffFact_id := :MedStaffFact_id,
				EvnPLDispTeenInspection_IndexRep := :EvnPLDispTeenInspection_IndexRep,
				EvnPLDispTeenInspection_IndexRepInReg := :EvnPLDispTeenInspection_IndexRepInReg,
				PersonDispOrp_id := :PersonDispOrp_id,
				EvnPLDispTeenInspection_pid := null, 
				Lpu_id := :Lpu_id, 
				Server_id := :Server_id, 
				PersonEvn_id := :PersonEvn_id, 
				EvnPLDispTeenInspection_setDT := :EvnPLDispTeenInspection_setDate,
				EvnPLDispTeenInspection_disDT := :EvnPLDispTeenInspection_disDate,
				EvnPLDispTeenInspection_didDT := null, 
				Morbus_id := null, 
				EvnPLDispTeenInspection_IsSigned := null, 
				pmUser_signID := null, 
				EvnPLDispTeenInspection_signDT := null, 
				EvnPLDispTeenInspection_VizitCount := null, 
				EvnPLDispTeenInspection_IsFinish := :EvnPLDispTeenInspection_IsFinish, 
				EvnPLDispTeenInspection_IsTwoStage := :EvnPLDispTeenInspection_IsTwoStage,
				Person_Age := null, 
				AttachType_id := 2,
				Lpu_aid := null, 
				EvnPLDispTeenInspection_consDT := :EvnPLDispTeenInspection_consDate, 
				EvnPLDispTeenInspection_IsMobile := :EvnPLDispTeenInspection_IsMobile, 
				EvnPLDispTeenInspection_IsOutLpu := :EvnPLDispTeenInspection_IsOutLpu, 
				Lpu_mid := :Lpu_mid, 
				DispClass_id := :DispClass_id,
				PayType_id := :PayType_id,
				EvnPLDispTeenInspection_fid := :EvnPLDispTeenInspection_fid, 
				EducationInstitutionClass_id := :EducationInstitutionClass_id, 
				AgeGroupDisp_id := :AgeGroupDisp_id, 
				Org_id := :Org_id, 
				InstitutionNatureType_id := :InstitutionNatureType_id, 
				InstitutionType_id := :InstitutionType_id, 
				EvnPLDispTeenInspection_eduDT := :EvnPLDispTeenInspection_eduDT,
				EvnPLDispTeenInspection_IsRefusal := (select EvnPLDispTeenInspection_IsRefusal from mv),
				EvnDirection_aid := (select EvnDirection_aid from mv),
				EvnPLDispTeenInspection_IsSuspectZNO := :EvnPLDispTeenInspection_IsSuspectZNO,
				Diag_spid := :Diag_spid,
				pmUser_id := :pmUser_id
			)
		";
		$result = $this->db->query($query, $data);

		if (!is_object($result))
		{
			return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение осмотра)');
		}

		$response = $result->result('array');

		if (!is_array($response) || count($response) == 0)
		{
			return false;
		}
		else if ($response[0]['Error_Msg'])
		{
			return $response;
		}

		if ( !isset($data['EvnPLDispTeenInspection_id']) )
		{
			$data['EvnPLDispTeenInspection_id'] = $response[0]['EvnPLDispTeenInspection_id'];
		}

		// Ищем AssessmentHealth связанный с EvnPLDispTeenInspection_id, если нет его то добавляем новый, иначе обновляем
		$data['AssessmentHealth_id'] = NULL;
		$query = "
			select
				AssessmentHealth_id as \"AssessmentHealth_id\"
			from v_AssessmentHealth
			where EvnPLDisp_id = :EvnPLDispTeenInspection_id 
			limit 1
		";
		$result = $this->db->query($query, $data);
		if (is_object($result))
		{
			$resp = $result->result('array');
			if (count($resp) > 0) {
				$data['AssessmentHealth_id'] = $resp[0]['AssessmentHealth_id'];
			}
		}

		// запихивание чекбоксов в YesNo
		if ($data['AssessmentHealth_IsRegular']) { $data['AssessmentHealth_IsRegular'] = 2; } else { $data['AssessmentHealth_IsRegular'] = 1;	}
		if ($data['AssessmentHealth_IsIrregular']) { $data['AssessmentHealth_IsIrregular'] = 2; } else { $data['AssessmentHealth_IsIrregular'] = 1;	}
		if ($data['AssessmentHealth_IsAbundant']) { $data['AssessmentHealth_IsAbundant'] = 2; } else { $data['AssessmentHealth_IsAbundant'] = 1;	}
		if ($data['AssessmentHealth_IsModerate']) { $data['AssessmentHealth_IsModerate'] = 2; } else { $data['AssessmentHealth_IsModerate'] = 1;	}
		if ($data['AssessmentHealth_IsScanty']) { $data['AssessmentHealth_IsScanty'] = 2; } else { $data['AssessmentHealth_IsScanty'] = 1;	}
		if ($data['AssessmentHealth_IsPainful']) { $data['AssessmentHealth_IsPainful'] = 2; } else { $data['AssessmentHealth_IsPainful'] = 1;	}
		if ($data['AssessmentHealth_IsPainless']) { $data['AssessmentHealth_IsPainless'] = 2; } else { $data['AssessmentHealth_IsPainless'] = 1;	}

		if ($data['AssessmentHealth_IsMental']) { $data['AssessmentHealth_IsMental'] = 2; } else { $data['AssessmentHealth_IsMental'] = 1;	}
		if ($data['AssessmentHealth_IsOtherPsych']) { $data['AssessmentHealth_IsOtherPsych'] = 2; } else { $data['AssessmentHealth_IsOtherPsych'] = 1;	}
		if ($data['AssessmentHealth_IsLanguage']) { $data['AssessmentHealth_IsLanguage'] = 2; } else { $data['AssessmentHealth_IsLanguage'] = 1;	}
		if ($data['AssessmentHealth_IsVestibular']) { $data['AssessmentHealth_IsVestibular'] = 2; } else { $data['AssessmentHealth_IsVestibular'] = 1;	}
		if ($data['AssessmentHealth_IsVisual']) { $data['AssessmentHealth_IsVisual'] = 2; } else { $data['AssessmentHealth_IsVisual'] = 1;	}
		if ($data['AssessmentHealth_IsMeals']) { $data['AssessmentHealth_IsMeals'] = 2; } else { $data['AssessmentHealth_IsMeals'] = 1;	}
		if ($data['AssessmentHealth_IsMotor']) { $data['AssessmentHealth_IsMotor'] = 2; } else { $data['AssessmentHealth_IsMotor'] = 1;	}
		if ($data['AssessmentHealth_IsDeform']) { $data['AssessmentHealth_IsDeform'] = 2; } else { $data['AssessmentHealth_IsDeform'] = 1;	}
		if ($data['AssessmentHealth_IsGeneral']) { $data['AssessmentHealth_IsGeneral'] = 2; } else { $data['AssessmentHealth_IsGeneral'] = 1;	}

		$this->load->model('AssessmentHealth_model');
		$data['scenario'] = swModel::SCENARIO_DO_SAVE;
		$data['EvnPLDisp_id'] = $data['EvnPLDispTeenInspection_id'];
		$response = $this->AssessmentHealth_model->doSave($data);

		// Назначения
		$this->load->model('DispAppoint_model');
		foreach ($data['DispAppointData'] as $key => $record) {
			if ($record['RecordStatus_Code'] == 3) {// удаление назначений
				$response = $this->DispAppoint_model->deleteDispAppoint(array(
					'DispAppoint_id' => $record['DispAppoint_id'],
					'pmUser_id' => $data['pmUser_id']
				));

				if (!is_array($response) || count($response) == 0)
				{
					return array(0 => array('Error_Msg' => 'Ошибка при удалении лабораторного исследования'));
				}
				else if (strlen($response[0]['Error_Msg']) > 0)
				{
					return $response;
				}
			}
			else {
				$params = array(
					'DispAppoint_id' => (!empty($record['DispAppoint_id']) && $record['DispAppoint_id'] > 0)?$record['DispAppoint_id']:null,
					'EvnPLDisp_id' => $data['EvnPLDisp_id'],
					'DispAppointType_id' => !empty($record['DispAppointType_id'])?$record['DispAppointType_id']:null,
					'MedSpecOms_id' => !empty($record['MedSpecOms_id'])?$record['MedSpecOms_id']:null,
					'ExaminationType_id' => !empty($record['ExaminationType_id'])?$record['ExaminationType_id']:null,
					'LpuSectionProfile_id' => !empty($record['LpuSectionProfile_id'])?$record['LpuSectionProfile_id']:null,
					'LpuSectionBedProfile_id' => !empty($record['LpuSectionBedProfile_id'])?$record['LpuSectionBedProfile_id']:null,
					'pmUser_id' => $data['pmUser_id']
				);

				$response = $this->DispAppoint_model->saveDispAppoint($params);

				if (!is_array($response) || count($response) == 0)
				{
					return array(0 => array('Error_Msg' => 'Ошибка при удалении лабораторного исследования'));
				}
				else if (strlen($response[0]['Error_Msg']) > 0)
				{
					return $response;
				}
			}
		}

		// Грид "Диагнозы и результаты"
		foreach($data['EvnDiagAndRecomendation'] as $record) {
			// получаем MedCare_id для MedCareType_id = 1
			$json = json_decode($record['FormDataJSON'], true);
			$MedCare_id = $this->getMedCareForEvnVizitDispDop($record['EvnVizitDispDop_id'], 1);
			$this->saveMedCare(array(
				'MedCare_id' => $MedCare_id,
				'EvnVizitDispDop_id' => $record['EvnVizitDispDop_id'],
				'ConditMedCareType_nid' => empty($json['ConditMedCareType1_nid'])?null:$json['ConditMedCareType1_nid'],
				'PlaceMedCareType_nid' => empty($json['PlaceMedCareType1_nid'])?null:$json['PlaceMedCareType1_nid'],
				'ConditMedCareType_id' => empty($json['ConditMedCareType1_id'])?null:$json['ConditMedCareType1_id'],
				'PlaceMedCareType_id' => empty($json['PlaceMedCareType1_id'])?null:$json['PlaceMedCareType1_id'],
				'LackMedCareType_id' => empty($json['LackMedCareType1_id'])?null:$json['LackMedCareType1_id'],
				'MedCareType_id' => 1,
				'pmUser_id' => $data['pmUser_id']
			));

			// получаем MedCare_id для MedCareType_id = 2
			$MedCare_id = $this->getMedCareForEvnVizitDispDop($record['EvnVizitDispDop_id'], 2);
			$this->saveMedCare(array(
				'MedCare_id' => $MedCare_id,
				'EvnVizitDispDop_id' => $record['EvnVizitDispDop_id'],
				'ConditMedCareType_nid' => empty($json['ConditMedCareType2_nid'])?null:$json['ConditMedCareType2_nid'],
				'PlaceMedCareType_nid' => empty($json['PlaceMedCareType2_nid'])?null:$json['PlaceMedCareType2_nid'],
				'ConditMedCareType_id' => empty($json['ConditMedCareType2_id'])?null:$json['ConditMedCareType2_id'],
				'PlaceMedCareType_id' => empty($json['PlaceMedCareType2_id'])?null:$json['PlaceMedCareType2_id'],
				'LackMedCareType_id' => empty($json['LackMedCareType2_id'])?null:$json['LackMedCareType2_id'],
				'MedCareType_id' => 2,
				'pmUser_id' => $data['pmUser_id']
			));

			// получаем MedCare_id для MedCareType_id = 3
			$MedCare_id = $this->getMedCareForEvnVizitDispDop($record['EvnVizitDispDop_id'], 3);
			$this->saveMedCare(array(
				'MedCare_id' => $MedCare_id,
				'EvnVizitDispDop_id' => $record['EvnVizitDispDop_id'],
				'ConditMedCareType_nid' => empty($json['ConditMedCareType3_nid'])?null:$json['ConditMedCareType3_nid'],
				'PlaceMedCareType_nid' => empty($json['PlaceMedCareType3_nid'])?null:$json['PlaceMedCareType3_nid'],
				'ConditMedCareType_id' => empty($json['ConditMedCareType3_id'])?null:$json['ConditMedCareType3_id'],
				'PlaceMedCareType_id' => empty($json['PlaceMedCareType3_id'])?null:$json['PlaceMedCareType3_id'],
				'LackMedCareType_id' => empty($json['LackMedCareType3_id'])?null:$json['LackMedCareType3_id'],
				'MedCareType_id' => 3,
				'pmUser_id' => $data['pmUser_id']
			));

			// сохраняем вмп и и Диспансерное наблюдение DispSurveilType_id и EvnVizitDisp_IsVMP
			// пока просто update todo
			$query = "
				update EvnVizitDisp
				set
					DispSurveilType_id = :DispSurveilType_id,
					EvnVizitDisp_IsVMP = :EvnVizitDisp_IsVMP,
					EvnVizitDisp_IsFirstTime = :EvnVizitDisp_IsFirstTime
				where Evn_id = :EvnVizitDisp_id
			";

			$result = $this->db->query($query, array(
				'DispSurveilType_id' => empty($json['DispSurveilType_id'])?null:$json['DispSurveilType_id'],
				'EvnVizitDisp_IsVMP' => empty($json['EvnVizitDisp_IsVMP'])?null:$json['EvnVizitDisp_IsVMP'],
				'EvnVizitDisp_IsFirstTime' => empty($json['EvnVizitDisp_IsFirstTime'])?null:$json['EvnVizitDisp_IsFirstTime'],
				'EvnVizitDisp_id' => $record['EvnVizitDispDop_id'],
			));
		}

		// Лабораторные исследования
		foreach ($data['EvnUslugaDispDop'] as $key => $record) {
			if ($record['Record_Status'] == 3) {// удаление исследований
				// получаем id посещения и услуги
				$resp_usluga = $this->getFirstRowFromQuery("
					select
						EVDD.EvnVizitDispDop_id as \"EvnVizitDispDop_id\",
						EUDD.EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\"
					from
						v_EvnVizitDispDop EVDD
						left join v_EvnUslugaDispDop EUDD on EUDD.EvnUslugaDispDop_pid = EVDD.EvnVizitDispDop_id
					where
						EUDD.EvnUslugaDispDop_id = :EvnUslugaDispDop_id
				", array(
					'EvnUslugaDispDop_id' => $record['EvnUslugaDispDop_id']
				));

				if (!empty($resp_usluga['EvnVizitDispDop_id'])) {
					$query = "
						select
							Error_Code as \"Error_Code\",
							Error_Message as \"Error_Msg\"
						from p_EvnVizitDispDop_del(
							EvnVizitDispDop_id := :EvnVizitDispDop_id,
							pmUser_id := :pmUser_id
						)
					";
					$result = $this->db->query($query, [
						'EvnVizitDispDop_id' => $resp_usluga['EvnVizitDispDop_id'],
						'pmUser_id' => $data['pmUser_id']
					]);

					if (!is_object($result)) {
						return array(0 => array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление осмотра врача-специалиста)'));
					}

					$response = $result->result('array');

					if (!is_array($response) || count($response) == 0) {
						return array(0 => array('Error_Msg' => 'Ошибка при удалении осмотра врача-специалиста'));
					} else if (strlen($response[0]['Error_Msg']) > 0) {
						return $response;
					}
				}

				if (!empty($resp_usluga['EvnUslugaDispDop_id'])) {
					$query = "
						select
							Error_Code as \"Error_Code\",
							Error_Message as \"Error_Msg\"
						from p_EvnUslugaDispDop_del(
							EvnUslugaDispDop_id := :EvnUslugaDispDop_id,
							pmUser_id := :pmUser_id
						)
					";
					$result = $this->db->query($query, array($resp_usluga['EvnUslugaDispDop_id'], $data['pmUser_id']));
					$result = $this->db->query($query, [
						'EvnUslugaDispDop_id' => $resp_usluga['EvnUslugaDispDop_id'],
						'pmUser_id' => $data['pmUser_id']
					]);
					if (!is_object($result))
					{
						return array(0 => array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление лабораторного исследования)'));
					}

					$response = $result->result('array');

					if (!is_array($response) || count($response) == 0)
					{
						return array(0 => array('Error_Msg' => 'Ошибка при удалении лабораторного исследования'));
					}
					else if (strlen($response[0]['Error_Msg']) > 0)
					{
						return $response;
					}
				}
			}
			else {
				if ($record['Record_Status'] == 0)
				{
					$procedure = 'p_EvnUslugaDispDop_ins';
				}
				else
				{
					$procedure = 'p_EvnUslugaDispDop_upd';
				}

				// 1. ищем DopDispInfoConsent_id
				$record['DopDispInfoConsent_id'] = null;
				if (!empty($record['EvnUslugaDispDop_id'])) {
					$query = "
						select
							EUDD.DopDispInfoConsent_id as \"DopDispInfoConsent_id\",
							EVDD.EvnVizitDispDop_id as \"EvnVizitDispDop_id\"
						from
							v_EvnVizitDispDop EVDD
							left join v_EvnUslugaDispDop EUDD on EUDD.EvnUslugaDispDop_pid = EVDD.EvnVizitDispDop_id
						where
							EUDD.EvnUslugaDispDop_id = :EvnUslugaDispDop_id
						limit 1
					";

					$result = $this->db->query($query, $record);
					if (is_object($result)) {
						$resp = $result->result('array');
						if (!empty($resp[0]['DopDispInfoConsent_id'])) {
							$record['DopDispInfoConsent_id'] = $resp[0]['DopDispInfoConsent_id'];
							$record['EvnVizitDispDop_id'] = $resp[0]['EvnVizitDispDop_id'];
						}
					}
				}

				// 2. обновляем/добавляем согласие
				$ddicproc = "p_DopDispInfoConsent_ins";
				if (!empty($record['DopDispInfoConsent_id'])) {
					$ddicproc = "p_DopDispInfoConsent_upd";
				}

				$query = "
					select
						DopDispInfoConsent_id as \"DopDispInfoConsent_id\",
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from {$ddicproc}(
						DopDispInfoConsent_id := :DopDispInfoConsent_id,
						EvnPLDisp_id := :EvnPLDisp_id, 
						SurveyTypeLink_id := NULL,
						DopDispInfoConsent_IsAgree := 2, 
						DopDispInfoConsent_IsEarlier := 1, 
						pmUser_id := :pmUser_id
					)
				";

				$result = $this->db->query($query, array(
					'EvnPLDisp_id' => $data['EvnPLDispTeenInspection_id'],
					'pmUser_id' => $data['pmUser_id'],
					'DopDispInfoConsent_id' => $record['DopDispInfoConsent_id']
				));

				if (is_object($result)) {
					$resp = $result->result('array');
					if (!empty($resp[0]['DopDispInfoConsent_id'])) {
						$record['DopDispInfoConsent_id'] = $resp[0]['DopDispInfoConsent_id'];
					}
				}

				if (empty($record['DopDispInfoConsent_id'])) {
					return array('Error_Msg' => 'Ошибка сохранения согласия');
				}

				$procedure_viz = "p_EvnVizitDispDop_ins";
				if (!empty($record['EvnVizitDispDop_id'])) {
					$procedure_viz = "p_EvnVizitDispDop_upd";
				}

				$setDT = $record['EvnUslugaDispDop_setDate'];
				if (!empty($record['EvnUslugaDispDop_setTime'])) {
					$setDT .= ' '.$record['EvnUslugaDispDop_setTime'];
				}
				$disDT = null;
				if (!empty($record['EvnUslugaDispDop_disDate'])) {
					$disDT = $record['EvnUslugaDispDop_disDate'];

					if (!empty($record['EvnUslugaDispDop_disTime'])) {
						$disDT .= ' '.$record['EvnUslugaDispDop_disTime'];
					}
				}

				$query = "
					select
						EvnVizitDispDop_id as \"EvnVizitDispDop_id\",
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from {$procedure_viz}(
						EvnVizitDispDop_id := :EvnVizitDispDop_id,
						EvnVizitDispDop_pid := :EvnVizitDispDop_pid,
						Lpu_id := :Lpu_id,
						Server_id := :Server_id,
						PersonEvn_id := :PersonEvn_id,
						EvnVizitDispDop_setDT := :EvnVizitDispDop_setDT,
						EvnVizitDispDop_disDT := :EvnVizitDispDop_disDT,
						EvnVizitDispDop_didDT := null,
						LpuSection_id := :LpuSection_id,
						MedSpecOms_id := :MedSpecOms_id,
						LpuSectionProfile_id := :LpuSectionProfile_id,
						MedPersonal_id := :MedPersonal_id,
						MedStaffFact_id := :MedStaffFact_id,
						MedPersonal_sid := null,
						PayType_id := null,
						UslugaComplex_id := :UslugaComplex_id,
						DopDispSpec_id := null,
						DopDispInfoConsent_id := :DopDispInfoConsent_id,
						Diag_id := :Diag_id,
						DopDispDiagType_id := :DopDispDiagType_id,
						DopDispAlien_id := :DopDispAlien_id,
						pmUser_id := :pmUser_id
					)
				";
				$result = $this->db->query($query, array(
					'EvnVizitDispDop_id' => (!empty($record['EvnVizitDispDop_id']) ? $record['EvnVizitDispDop_id'] : NULL),
					'EvnVizitDispDop_pid' => $data['EvnPLDispTeenInspection_id'],
					'Lpu_id' => (!empty($record['Lpu_uid']) ? $record['Lpu_uid'] : $data['Lpu_id']),
					'MedSpecOms_id' => (!empty($record['MedSpecOms_id']) ? $record['MedSpecOms_id'] : NULL),
					'LpuSectionProfile_id' => (!empty($record['LpuSectionProfile_id']) ? $record['LpuSectionProfile_id'] : NULL),
					'Server_id' => $data['Server_id'],
					'PersonEvn_id' => $data['PersonEvn_id'],
					'EvnVizitDispDop_setDT' => $setDT,
					'EvnVizitDispDop_disDT' => $disDT,
					'LpuSection_id' => (!empty($record['LpuSection_id']) ? $record['LpuSection_id'] : NULL),
					'MedPersonal_id' => (!empty($record['MedPersonal_id']) ? $record['MedPersonal_id'] : NULL),
					'MedStaffFact_id' => (!empty($record['MedStaffFact_id']) ? $record['MedStaffFact_id'] : NULL),
					'UslugaComplex_id' => $record['UslugaComplex_id'],
					'DopDispInfoConsent_id' => $record['DopDispInfoConsent_id'],
					'Diag_id' => (!empty($record['Diag_id']) ? $record['Diag_id'] : NULL),
					'DopDispDiagType_id' => (isset($record['DopDispDiagType_id']) && $record['DopDispDiagType_id'] > 0) ? $record['DopDispDiagType_id'] : null,
					'DopDispAlien_id' => null,
					'pmUser_id' => $data['pmUser_id']
				));
				if (!is_object($result))
				{
					return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение посещения)');
				}
				$response_viz = $result->result('array');
				if (!is_array($response_viz) || count($response_viz) == 0)
				{
					return false;
				}
				else if ($response_viz[0]['Error_Msg'])
				{
					return $response_viz;
				}

				if (!empty($response_viz[0]['EvnVizitDispDop_id'])) {
					$query = "
						with mv as (
							select
								PayType_id as pt
							from v_PayType
							where PayType_SysNick = 'dopdisp'
							limit 1
						)
						select
							EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\",
							Error_Code as \"Error_Code\",
							Error_Message as \"Error_Msg\"
						from " . $procedure . "(
							EvnUslugaDispDop_id := :EvnUslugaDispDop_id,
							EvnUslugaDispDop_pid := :EvnUslugaDispDop_pid,
							Lpu_id := :Lpu_id,
							Server_id := :Server_id,
							PersonEvn_id := :PersonEvn_id,
							EvnUslugaDispDop_setDT := :EvnUslugaDispDop_setDT,
							EvnUslugaDispDop_disDT := :EvnUslugaDispDop_disDT,
							EvnUslugaDispDop_didDT := :EvnUslugaDispDop_didDT,
							LpuSection_uid := :LpuSection_uid,
							MedSpecOms_id := :MedSpecOms_id,
							LpuSectionProfile_id := :LpuSectionProfile_id,
							MedPersonal_id := :MedPersonal_id,
							MedStaffFact_id := :MedStaffFact_id,
							UslugaComplex_id := :UslugaComplex_id,
							DopDispInfoConsent_id := :DopDispInfoConsent_id,
							PayType_id := (select pt from mv),
							UslugaPlace_id := 1,
							Lpu_uid := :Lpu_uid,
							EvnUslugaDispDop_Kolvo := 1,
							ExaminationPlace_id := :ExaminationPlace_id,
							EvnPrescrTimetable_id := null,
							EvnPrescr_id := null,
							EvnUslugaDispDop_Result := :EvnUslugaDispDop_Result,
							Diag_id := :Diag_id,
							pmUser_id := :pmUser_id
						)
					";

					$result = $this->db->query($query, array(
						'EvnUslugaDispDop_id' => (!empty($record['Record_Status']) ? $record['EvnUslugaDispDop_id'] : NULL),
						'EvnUslugaDispDop_pid' => $response_viz[0]['EvnVizitDispDop_id'],
						'Lpu_uid' => (!empty($record['Lpu_uid']) ? $record['Lpu_uid'] : $data['Lpu_id']),
						'MedSpecOms_id' => (!empty($record['MedSpecOms_id']) ? $record['MedSpecOms_id'] : NULL),
						'LpuSectionProfile_id' => (!empty($record['LpuSectionProfile_id']) ? $record['LpuSectionProfile_id'] : NULL),
						'Server_id' => $data['Server_id'],
						'PersonEvn_id' => $data['PersonEvn_id'],
						'EvnUslugaDispDop_setDT' => $setDT,
						'EvnUslugaDispDop_disDT' => $disDT,
						'EvnUslugaDispDop_didDT' => (!empty($record['EvnUslugaDispDop_didDate']) ? $record['EvnUslugaDispDop_didDate'] : NULL),
						'LpuSection_uid' => (!empty($record['LpuSection_id']) ? $record['LpuSection_id'] : NULL),
						'MedPersonal_id' => (!empty($record['MedPersonal_id']) ? $record['MedPersonal_id'] : NULL),
						'MedStaffFact_id' => (!empty($record['MedStaffFact_id']) ? $record['MedStaffFact_id'] : NULL),
						'UslugaComplex_id' => $record['UslugaComplex_id'],
						'DopDispInfoConsent_id' => $record['DopDispInfoConsent_id'],
						'Lpu_id' => $data['Lpu_id'],
						'ExaminationPlace_id' => (!empty($record['ExaminationPlace_id']) ? $record['ExaminationPlace_id'] : NULL),
						'EvnUslugaDispDop_Result' => (!empty($record['EvnUslugaDispDop_Result']) ? $record['EvnUslugaDispDop_Result'] : NULL),
						'Diag_id' => (!empty($record['Diag_id']) ? $record['Diag_id'] : NULL),
						'pmUser_id' => $data['pmUser_id']
					));

					if (!is_object($result)) {
						return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение посещения)');
					}
					$response = $result->result('array');

					if (!is_array($response) || count($response) == 0) {
						return false;
					} else if ($response[0]['Error_Msg']) {
						return $response;
					}

					$record['EvnUslugaDispDop_id'] = $response[0]['EvnUslugaDispDop_id'];
				}
			}
		}

		// чистим в карте согласия по которым больше нет услуг
		$query = "
			select 
				DopDispInfoConsent_id as \"DopDispInfoConsent_id\"
			from 
				v_DopDispInfoConsent ddic
			where EvnPLDisp_id = :EvnPLDisp_id 
			and (not exists (select EvnVizitDispDop_id from v_EvnVizitDispDop where DopDispInfoConsent_id = ddic.DopDispInfoConsent_id limit 1))
			and (not exists (select EvnUslugaDispDop_id from v_EvnUslugaDispDop where DopDispInfoConsent_id = ddic.DopDispInfoConsent_id limit 1))
		";
		$result = $this->db->query($query, array(
			'EvnPLDisp_id' => $data['EvnPLDispTeenInspection_id']
		));
		if (is_object($result)) {
			$resp = $result->result('array');
			foreach($resp as $one) {
				$query = "
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from p_DopDispInfoConsent_del(
						DopDispInfoConsent_id := :DopDispInfoConsent_id,
						pmUser_id := :pmUser_id
					)
				";

				$this->db->query($query, array(
					'DopDispInfoConsent_id' => $one['DopDispInfoConsent_id'],
					'pmUser_id' => $data['pmUser_id']
				));
			}
		}

		// Сохраняем скрытую услугу для Бурятии, если случай закончен
		// @task https://redmine.swan.perm.ru/issues/52175
		// Добавлен Крым
		// @task https://redmine.swan.perm.ru/issues/88196
		if (
			in_array($data['session']['region']['nick'], array('buryatiya', 'krym')) && !empty($data['EvnPLDispTeenInspection_id'])
			&& !empty($data['EvnPLDispTeenInspection_IsFinish']) && $data['EvnPLDispTeenInspection_IsFinish'] == 2
		) {
			// Ищем существующую услугу
			$query = "
				select
					EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\",
					UslugaComplex_id as \"UslugaComplex_id\",
					PayType_id as \"PayType_id\",
					to_char(EvnUslugaDispDop_setDT, 'dd.mm.yyyy') as \"EvnUslugaDispDop_setDate\"
				from v_EvnUslugaDispDop
				where EvnUslugaDispDop_pid = :EvnUslugaDispDop_pid
					and EvnUslugaDispDop_IsVizitCode = 2
				limit 1
			";
			$result = $this->db->query($query, array(
				'EvnUslugaDispDop_pid' => $data['EvnPLDispTeenInspection_id']
			));

			if ( !is_object($result) ) {
				return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (поиск услуги)');
			}

			$response = $result->result('array');

			if ( is_array($response) && count($response) > 0 ) {
				$uslugaData = $response[0];
			}
			else {
				$uslugaData = array();
			}

			$filters = "";
			$addit_query = "";

			$params = array(
				'DispClass_id' => $data['DispClass_id'],
				'Usluga_Date' => $data['EvnPLDispTeenInspection_setDate'],
				'EvnPLDispTeenInspection_setDT' => $data['EvnPLDispTeenInspection_setDate'],
				'Person_id' => $data['Person_id']
			);

			if ($data['DispClass_id'] == 11) {
				$addit_query .= "
					, e as (
						select
							eic.EducationInstitutionType_id as e
						from v_EducationInstitutionClass eic
						where eic.EducationInstitutionClass_id = :EducationInstitutionClass_id
						limit 1
					)
				";
				$filters .= " and USL.EducationInstitutionType_id = (select e from e)";
				$params['EducationInstitutionClass_id'] = $data['EducationInstitutionClass_id'];
			}

			if ($this->regionNick == 'buryatiya' && $data['DispClass_id'] == 12 && $Person_Age_EndYear >= 3) {
				$params['Usluga_Date'] = substr($data['EvnPLDispTeenInspection_setDate'], 0, 4) . '-12-31';
			}

			// Определяем UslugaComplex_id через $schema.UslugaSurveyLink
			$query = "
				with mv as(
						select
							coalesce(Sex_id, 3) as sex_id,
							dbo.Age_newborn_2(Person_BirthDay, coalesce(:Usluga_Date, dbo.tzGetDate())) as age,
						from v_PersonState ps
						where ps.Person_id = :Person_id
						limit 1
					){$addit_query}

				select USL.UslugaComplex_id as \"UslugaComplex_id\"
				from {$data['session']['region']['schema']}.v_UslugaSurveyLink USL
				where
					USL.DispClass_id = :DispClass_id
					and coalesce(USL.Sex_id, (select sex_id from mv)) = (select sex_id from mv)
					and (select age from mv) between (coalesce(USL.UslugaSurveyLink_From, 0)*12+coalesce(USL.UslugaSurveyLink_monthFrom, 0)) and (coalesce(USL.UslugaSurveyLink_To, 999)*12+coalesce(USL.UslugaSurveyLink_monthTo, 0))
					and coalesce(USL.UslugaSurveyLink_IsDel, 1) = 1
					and (USL.UslugaSurveyLink_begDate is null or USL.UslugaSurveyLink_begDate <= :EvnPLDispTeenInspection_setDT)
					and (USL.UslugaSurveyLink_endDate is null or USL.UslugaSurveyLink_endDate >= :EvnPLDispTeenInspection_setDT)
					{$filters}
				limit 1
			";
			$result = $this->db->query($query, $params);

			if ( !is_object($result) ) {
				return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (получение идентификатора)');
			}

			$response = $result->result('array');

			if ( is_array($response) && count($response) > 0 ) {
				$UslugaComplex_id = $response[0]['UslugaComplex_id'];
			}
			else {
				$UslugaComplex_id = null;
			}

			// Добавляем/обновляем при необходимости
			if ( !empty($UslugaComplex_id) ) {
				$query = "
					with mv as (
						select
							case when :PayType_id is null
								then PayType_id
								else :PayType_id
							end as pt
						from v_PayType
						where PayType_SysNick = 'dopdisp'
					)
					
					select
						EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\",
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from p_EvnUslugaDispDop_" . (!empty($uslugaData['EvnUslugaDispDop_id']) ? "upd" : "ins") . "(
						EvnUslugaDispDop_id := :EvnUslugaDispDop_id,
						EvnUslugaDispDop_pid := :EvnUslugaDispDop_pid,
						UslugaComplex_id := :UslugaComplex_id,
						EvnUslugaDispDop_setDT := :EvnUslugaDispDop_setDT,
						EvnUslugaDispDop_IsVizitCode := 2,
						Lpu_id := :Lpu_id,
						Server_id := :Server_id,
						PersonEvn_id := :PersonEvn_id,
						PayType_id := (select pt from mv),
						UslugaPlace_id := 1,
						pmUser_id := :pmUser_id
					)
				";
				$result = $this->db->query($query, array(
					'EvnUslugaDispDop_id' => (!empty($uslugaData['EvnUslugaDispDop_id']) ? $uslugaData['EvnUslugaDispDop_id'] : null),
					'EvnUslugaDispDop_pid' => $data['EvnPLDispTeenInspection_id'],
					'UslugaComplex_id' => $UslugaComplex_id,
					'EvnUslugaDispDop_setDT' => (!empty($data['EvnPLDispTeenInspection_setDate']) ? $data['EvnPLDispTeenInspection_setDate'] : null),
					'Lpu_id' => $data['Lpu_id'],
					'Server_id' => $data['Server_id'],
					'PersonEvn_id' => $data['PersonEvn_id'],
					'PayType_id' => (!empty($uslugaData['PayType_id']) ? $uslugaData['PayType_id'] : null),
					'pmUser_id' => $data['pmUser_id']
				));

				if ( !is_object($result) ) {
					return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление услуги) (' . __LINE__ . ')'));
				}

				$response = $result->result('array');

				if ( !is_array($response) || count($response) == 0 ) {
					return array(array('Error_Msg' => 'Ошибка при удалении услуги (' . __LINE__ . ')'));
				}
				else if ( !empty($response[0]['Error_Msg']) ) {
					return array(array('Error_Msg' => $response[0]['Error_Msg'] . ' (' . __LINE__ . ')'));
				}
			}
			// Удаляем
			else if ( !empty($uslugaData['EvnUslugaDispDop_id']) ) {
				$query = "
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from p_EvnUslugaDispDop_del(
						EvnUslugaDispDop_id := :EvnUslugaDispDop_id,
						pmUser_id := :pmUser_id
					)
				";
				$result = $this->db->query($query, array(
					'EvnUslugaDispDop_id' => $uslugaData['EvnUslugaDispDop_id'],
					'pmUser_id' => $data['pmUser_id']
				));

				if ( !is_object($result) ) {
					return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление услуги) (' . __LINE__ . ')'));
				}

				$response = $result->result('array');

				if ( !is_array($response) || count($response) == 0 ) {
					return array(array('Error_Msg' => 'Ошибка при удалении услуги (' . __LINE__ . ')'));
				}
				else if ( !empty($response[0]['Error_Msg']) ) {
					return array(array('Error_Msg' => $response[0]['Error_Msg'] . ' (' . __LINE__ . ')'));
				}
			}
		}

		$justClosed = (
			!empty($data['EvnPLDispTeenInspection_IsFinish']) && $data['EvnPLDispTeenInspection_IsFinish'] == 2 && (
				empty($savedData) || $savedData['EvnPLDispTeenInspection_IsFinish'] != 2
			)
		);

		if (getRegionNick() == 'penza' && (empty($savedData) || $justClosed)) {
			//Отправить человека в очередь на идентификацию
			$this->load->model('Person_model', 'pmodel');
			$this->pmodel->isAllowTransaction = false;
			$resTmp = $this->pmodel->addPersonRequestData(array(
				'Person_id' => $data['Person_id'],
				'Evn_id' => $data['EvnPLDispTeenInspection_id'],
				'pmUser_id' => $data['pmUser_id'],
				'PersonRequestSourceType_id' => 3,
			));
			$this->pmodel->isAllowTransaction = true;
			if (!$this->isSuccessful($resTmp) && !in_array($resTmp[0]['Error_Code'], array(302, 303))) {
				return array('Error_Msg' => $resTmp[0]['Error_Msg']);
			}
		}

		return array(0 => array('EvnPLDispTeenInspection_id' => $data['EvnPLDispTeenInspection_id'], 'Error_Msg' => ''));
	}

	/**
	 *	Удаление атрибутов карты мед. осмотра несовершеннолетнего
	 */
	function deleteAttributes($attr, $EvnPLDispTeenInspection_id, $pmUser_id) {
		// Сперва получаем список
		switch ( $attr ) {
			case 'EvnVizitDispDop':
				$query = "
					select
						EVDD.EvnVizitDispDop_id as \"id\"
					from
						v_EvnUslugaDispDop EUDD
						inner join v_EvnVizitDispDop EVDD on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
						inner join v_DopDispInfoConsent DDIC on DDIC.DopDispInfoConsent_id = EVDD.DopDispInfoConsent_id
						inner join v_SurveyTypeLink STL on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
						inner join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
					where
						EUDD.EvnUslugaDispDop_rid = :EvnPLDispTeenInspection_id
						and coalesce(DDIC.DopDispInfoConsent_IsAgree, 1) = 1
						and coalesce(DDIC.DopDispInfoConsent_IsEarlier, 1) = 1
						and ST.SurveyType_Code not in (2,50,67,68)
						and coalesce(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
				";
				break;

			// Специально для удаления анкетирования
			case 'EvnUslugaDispDop':
				$query = "
					select
						EUDD.EvnUslugaDispDop_id as \"id\"
					from
						v_EvnUslugaDispDop EUDD
						inner join v_SurveyTypeLink STL on STL.UslugaComplex_id = EUDD.UslugaComplex_id
						inner join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
						inner join v_DopDispInfoConsent DDIC on DDIC.SurveyTypeLink_id = STL.SurveyTypeLink_id
					where
						EUDD.EvnUslugaDispDop_rid = :EvnPLDispTeenInspection_id
						and DDIC.EvnPLDisp_id = :EvnPLDispTeenInspection_id
						and coalesce(DDIC.DopDispInfoConsent_IsAgree, 1) = 2
						and coalesce(DDIC.DopDispInfoConsent_IsEarlier, 1) = 1
						and ST.SurveyType_Code = 2
						and coalesce(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
				";
				break;

			case 'EvnDiagDopDisp':
				$query = "
					select " . $attr . "_id as \"id\"
					from v_" . $attr . "
					where EvnDiagDopDisp_pid = :EvnPLDispTeenInspection_id
				";
				break;

			case 'DopDispInfoConsent':
				$query = "
					select DDIC.DopDispInfoConsent_id as \"id\"
					from v_DopDispInfoConsent DDIC
						inner join v_SurveyTypeLink STL on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
						inner join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
					where DDIC.EvnPLDisp_id = :EvnPLDispTeenInspection_id
						and ST.SurveyType_Code NOT IN (50,67,68)
				";
				break;

			default:
				$query = "
					select " . $attr . "_id as \"id\"
					from v_" . $attr . "
					where EvnPLDisp_id = :EvnPLDispTeenInspection_id
				";
				break;
		}

		$result = $this->db->query($query, array('EvnPLDispTeenInspection_id' => $EvnPLDispTeenInspection_id));

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
					from p_" . $attr . "_del(
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
	 *	Получение идентификатора из списка добровольного информированного согласия по $SurveyTypeLink_id
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
	 * Получение данных для отображения в ЭМК
	 */
	function getEvnPLDispTeenInspectionViewData($data) {
		$queryParams = array(
			'EvnPLDisp_id' => $data['EvnPLDisp_id']
		);
		// – Редактирование карты диспансеризации / профосмотра доступно только из АРМ врача поликлиники, пользователем с привязкой к врачу терапевту (ВОП) / педиатру (ВОП),
		// отделение места работы которого совпадает с отделением места работы врача, создавшего карту.
		$accessType = "'view' as \"accessType\",";
		if (false && !empty($data['session']['CurARM']['PostMed_id']) && in_array($data['session']['CurARM']['PostMed_id'], array(73,74,75,76,40,46,47)) && !empty($data['session']['CurARM']['LpuSection_id'])) {
			$accessType = "case when coalesce(msf.LpuSection_id, :LpuSection_id) = :LpuSection_id then 'edit' else 'view' end as \"accessType\",";
			$queryParams['LpuSection_id'] = $data['session']['CurARM']['LpuSection_id'];
		}
		if (!empty($_SESSION['CurArmType']) && $_SESSION['CurArmType'] == 'profosmotr') {
			$accessType = "'edit' as \"accessType\",";
		}

		$query = "
			select
				epldti.EvnPLDispTeenInspection_id as \"EvnPLDispTeenInspection_id\",
				case when epldti.MedStaffFact_id is not null
					then coalesce(l.Lpu_Nick || ' ', '') || coalesce(ls.LpuSection_Name || ' ', '') || coalesce(msf.Person_Fio, '') 
					else coalesce(l.Lpu_Nick || ' ', '') || coalesce(pu.pmUser_Name, '')
				end as \"AuthorInfo\",
				'EvnPLDispTeenInspection' as \"Object\",
				epldti.DispClass_id as \"DispClass_id\",
				epldti.Person_id as \"Person_id\",
				epldti.PersonEvn_id as \"PersonEvn_id\",
				epldti.Server_id as \"Server_id\",
				dc.DispClass_Code as \"DispClass_Code\",
				dc.DispClass_Name as \"DispClass_Name\",
				{$accessType}
				epldti.PayType_id as \"PayType_id\",
				pt.PayType_Name as \"PayType_Name\",
				epldti.AgeGroupDisp_id as \"AgeGroupDisp_id\",
				agd.AgeGroupDisp_Name as \"AgeGroupDisp_Name\",
				to_char(epldti.EvnPLDispTeenInspection_setDT, 'dd.mm.yyyy') as \"EvnPLDispTeenInspection_setDate\",
				to_char(epldti.EvnPLDispTeenInspection_consDT, 'dd.mm.yyyy') as \"EvnPLDispTeenInspection_consDate\",
				to_char(epldti.EvnPLDispTeenInspection_eduDT, 'dd.mm.yyyy') as \"EvnPLDispTeenInspection_eduDate\",
				epldti.EducationInstitutionClass_id as \"EducationInstitutionClass_id\",
				eic.EducationInstitutionClass_Name as \"EducationInstitutionClass_Name\",
				epldti.InstitutionNatureType_id as \"InstitutionNatureType_id\",
				int.InstitutionNatureType_Name as \"InstitutionNatureType_Name\",
				epldti.InstitutionType_id as \"InstitutionType_id\",
				it.InstitutionType_Name as \"InstitutionType_Name\",
				ah.AssessmentHealth_Weight as \"AssessmentHealth_Weight\",
				ah.AssessmentHealth_Height as \"AssessmentHealth_Height\",
				ah.WeightAbnormType_id as \"WeightAbnormType_id\",
				wat.WeightAbnormType_Name as \"WeightAbnormType_Name\",
				ah.HeightAbnormType_id as \"HeightAbnormType_id\",
				hat.HeightAbnormType_Name as \"HeightAbnormType_Name\",
				ah.AssessmentHealth_Gnostic as \"AssessmentHealth_Gnostic\",
				ah.AssessmentHealth_Motion as \"AssessmentHealth_Motion\",
				ah.AssessmentHealth_Social as \"AssessmentHealth_Social\",
				ah.AssessmentHealth_Speech as \"AssessmentHealth_Speech\",
				ah.NormaDisturbanceType_id as \"NormaDisturbanceType_id\",
				ndt.NormaDisturbanceType_Name as \"NormaDisturbanceType_Name\",
				ah.NormaDisturbanceType_uid as \"NormaDisturbanceType_uid\",
				ndtu.NormaDisturbanceType_Name as \"NormaDisturbanceTypeU_Name\",
				ah.NormaDisturbanceType_eid as \"NormaDisturbanceType_eid\",
				ndte.NormaDisturbanceType_Name as \"NormaDisturbanceTypeE_Name\",
				ah.AssessmentHealth_P as \"AssessmentHealth_P\",
				ah.AssessmentHealth_Ax as \"AssessmentHealth_Ax\",
				ah.AssessmentHealth_Fa as \"AssessmentHealth_Fa\",
				ah.AssessmentHealth_Ma as \"AssessmentHealth_Ma\",
				ah.AssessmentHealth_Me as \"AssessmentHealth_Me\",
				ah.HealthKind_id as \"HealthKind_id\",
				hk.HealthKind_Name as \"HealthKind_Name\",
				coalesce(epldti.EvnPLDispTeenInspection_IsFinish, 1) as \"EvnPLDispTeenInspection_IsFinish\",
				coalesce(epldti.EvnPLDispTeenInspection_IsTwoStage, 1) as \"EvnPLDispTeenInspection_IsTwoStage\",
				et.ElectronicTalon_id as \"ElectronicTalon_id\",
				et.ElectronicService_id as \"ElectronicService_id\",
				es.ElectronicService_Num as \"ElectronicService_Num\",
				et.ElectronicTalonStatus_id as \"ElectronicTalonStatus_id\",
				eqi.ElectronicQueueInfo_IsOff as \"ElectronicQueueInfo_IsOff\"
			from
				v_EvnPLDispTeenInspection epldti
				left join v_Lpu l on l.Lpu_id = epldti.Lpu_id
				left join v_MedStaffFact msf on msf.MedStaffFact_id = epldti.MedStaffFact_id
				left join v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id
				left join v_pmUser pu on pu.pmUser_id = epldti.pmUser_updID
				left join lateral(
					select * from v_AssessmentHealth where EvnPLDisp_id = EPLDTI.EvnPLDispTeenInspection_id limit 1
				) ah on true
				left join v_DispClass dc on dc.DispClass_id = epldti.DispClass_id
				left join v_PayType pt on pt.PayType_id = epldti.PayType_id
				left join v_AgeGroupDisp agd on agd.AgeGroupDisp_id = epldti.AgeGroupDisp_id
				left join v_EducationInstitutionClass eic on eic.EducationInstitutionClass_id = epldti.EducationInstitutionClass_id
				left join v_InstitutionNatureType int on int.InstitutionNatureType_id = epldti.InstitutionNatureType_id
				left join v_InstitutionType it on it.InstitutionType_id = epldti.InstitutionType_id
				left join v_HeightAbnormType hat on hat.HeightAbnormType_id = ah.HeightAbnormType_id
				left join v_WeightAbnormType wat on wat.WeightAbnormType_id = ah.WeightAbnormType_id
				left join v_NormaDisturbanceType ndt on ndt.NormaDisturbanceType_id = ah.NormaDisturbanceType_id
				left join v_NormaDisturbanceType ndtu on ndtu.NormaDisturbanceType_id = ah.NormaDisturbanceType_uid
				left join v_NormaDisturbanceType ndte on ndte.NormaDisturbanceType_id = ah.NormaDisturbanceType_eid
				left join v_HealthKind hk on hk.HealthKind_id = ah.HealthKind_id
				left join v_ElectronicTalon et on et.EvnDirection_id = epldti.EvnDirection_id
				left join v_ElectronicQueueInfo eqi on eqi.ElectronicQueueInfo_id = et.ElectronicQueueInfo_id
				left join v_MedServiceElectronicQueue mseq on et.ElectronicService_id = mseq.ElectronicService_id
				left join v_ElectronicService es on et.ElectronicService_id = es.ElectronicService_id
				left join v_MedServiceMedPersonal msp on msp.MedServiceMedPersonal_id = mseq.MedServiceMedPersonal_id
			where
				epldti.EvnPLDispTeenInspection_id = :EvnPLDisp_id
		";

		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Проверка на возможность добавления осмотра человеку
	 */
	function checkEvnPLDispTeenInspectionCanBeSaved($data, $mode) {
		if (in_array($data['DispClass_id'], array(9, 10))) {
			$queryParams = array(
				'Person_id' => $data['Person_id'],
				'Lpu_id' => $data['Lpu_id']
			);
			if ($mode == 'saveDopDispInfoConsent') {
				// Если выбранный персон на момент согласия не имеет основного прикрепления к ЛПУ пользователя, то выводить сообщение «Пациент имеет основное прикрепление к другой МО». Добавление отменить.
				$queryParams['date'] = $data['EvnPLDispTeenInspection_consDate'];
			} else if (!empty($data['EvnPLDispTeenInspection_disDate'])) {
				// Если выбранный персон на дату осмотра терапевта не имеет основного прикрепления к ЛПУ пользователя, то выводить сообщение «Пациент имеет основное прикрепление к другой МО». Сохранение отменить.
				$queryParams['date'] = $data['EvnPLDispTeenInspection_disDate'];
			}

			if (!empty($queryParams['date'])) {
				$filters = "";
				if (getRegionNick() == 'ekb') {
					$allowWithoutAttach = false;
					// проверяем наличие объёма "Без прикрепления"
					$resp_vol = $this->queryResult("
						with mv as (
							select
								VolumeType_id as v
							from v_VolumeType
							where VolumeType_Code = 'ОН_Б_ПРИК'
							limit 1
						) -- ОН_Б_ПРИК
		
						SELECT
							av.AttributeValue_id as \"AttributeValue_id\"
						FROM
							v_AttributeVision avis
							inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
							inner join v_Attribute a on a.Attribute_id = av.Attribute_id
						WHERE
							avis.AttributeVision_TableName = 'dbo.VolumeType'
							and av.AttributeValue_ValueIdent = :Lpu_id
							and avis.AttributeVision_TablePKey = (select v from mv)
							and avis.AttributeVision_IsKeyValue = 2
							and :date between coalesce(av.AttributeValue_begDate, :date) and coalesce(av.AttributeValue_endDate, :date)
						limit 1
					", $queryParams);
					if (!empty($resp_vol[0]['AttributeValue_id'])) {
						// Если МО имеет объем открытый объем «ОН_Б_ПРИК», то проверку на прикрепление к разрешенным МО не проводим
						$allowWithoutAttach = true;
					}

					if ($allowWithoutAttach) {
						return ''; // всё гуд, можно и без прикрепления и с любым прикреплением.
					}

					$data['VolumeType_id'] = 88; // Мед. осмотры несовершеннолетних в чужой МО
					if (!empty($data['VolumeType_id'])) {
						$filters .= "
							and (
								pcard.Lpu_id = :Lpu_id
								or exists (
									SELECT
										av.AttributeValue_id
									FROM
										v_AttributeVision avis
										inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
										inner join v_Attribute a on a.Attribute_id = av.Attribute_id
										inner join lateral(
											select
												av2.AttributeValue_ValueIdent
											from
												v_AttributeValue av2
												inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
											where
												av2.AttributeValue_rid = av.AttributeValue_id
												and a2.Attribute_TableName = 'dbo.Lpu'
												and coalesce(av2.AttributeValue_ValueIdent, :Lpu_id) = :Lpu_id -- МО проведения
											limit 1
										) MOFILTER on true
									WHERE
										avis.AttributeVision_TableName = 'dbo.VolumeType'
										and avis.AttributeVision_TablePKey = :VolumeType_id
										and avis.AttributeVision_IsKeyValue = 2
										and coalesce(av.AttributeValue_begDate, :date) <= :date
										and coalesce(av.AttributeValue_endDate, :date) >= :date
										and av.AttributeValue_ValueIdent = pcard.Lpu_id -- МО прикрепления
									limit 1
								)
							)
						";
						$queryParams['VolumeType_id'] = $data['VolumeType_id'];
					} else {
						$filters .= " and pcard.Lpu_id = :Lpu_id";
					}
				} else {
					$filters .= " and pcard.Lpu_id = :Lpu_id";
				}

				$sql = "
					SELECT
						pcard.PersonCard_id as \"PersonCard_id\"
					FROM
						v_PersonCard_all pcard
					WHERE
						pcard.Person_id = :Person_id
						and pcard.LpuAttachType_id = 1
						and cast(pcard.PersonCard_begDate as date) <= :date
						and coalesce(pcard.PersonCard_endDate, '2030-01-01') >= :date
						{$filters}
					limit 1
				";

				$res = $this->db->query($sql, $queryParams);

				if (!is_object($res)) {
					return 'Ошибка при выполнении запроса к базе данных (строка ' . __LINE__ . ')';
				}

				$sel = $res->result('array');

				if (!is_array($sel) || count($sel) == 0 || empty($sel[0]['PersonCard_id'])) {
					return 'Пациент имеет основное прикрепление к другой МО';
				}
			}
		}

		return '';
	}

	/**
	 *	Сохранение добровольного информированного согласия
	 */
	function saveDopDispInfoConsent($data) {
		// Проверки
		if (!in_array($data['session']['region']['nick'], array('astra', 'by', 'buryatiya', 'kareliya', 'penza', 'vologda'))) {
			$checkResult = $this->checkEvnPLDispTeenInspectionCanBeSaved($data, 'saveDopDispInfoConsent');

			if ( !empty($checkResult) ) {
				return array(array('Error_Msg' => $checkResult));
			}
		}

		if ( in_array($data['DispClass_id'], array(10, 12)) && empty($data['AgeGroupDisp_id']) ) {
			return array(array('Error_Msg' => 'Не указана возрастная группа, сохранение невозможно'));
		}

		// Стартуем транзакцию
		$this->db->trans_begin();

		$EvnPLDispDopIsNew = false;

		if ($data['EvnPLDispTeenInspection_IsMobile']) { $data['EvnPLDispTeenInspection_IsMobile'] = 2; } else { $data['EvnPLDispTeenInspection_IsMobile'] = 1;	}
		if ($data['EvnPLDispTeenInspection_IsOutLpu']) { $data['EvnPLDispTeenInspection_IsOutLpu'] = 2; } else { $data['EvnPLDispTeenInspection_IsOutLpu'] = 1;	}

		if (empty($data['EvnPLDispTeenInspection_id'])) {
			if (!in_array($data['DispClass_id'], array(9, 11))) {
				// Проверям наличие карт за выбраный год
				// https://redmine.swan.perm.ru/issues/23095
				/*
					6	Периодические осмотры несовершеннолетних
					9	Предварительные осмотры несовершеннолетних 1-ый этап
					10	Профилактические осмотры несовершеннолетних 1-ый этап
					11	Предварительные осмотры несовершеннолетних 2-ой этап
					12	Профилактические осмотры несовершеннолетних 2-ой этап
				*/
				$query = "
					select
						EvnPLDispTeenInspection_id as \"EvnPLDispTeenInspection_id\"
					from v_EvnPLDispTeenInspection
					where Person_id = :Person_id
						and date_part('year', EvnPLDispTeenInspection_consDT) = date_part('year', CAST(:EvnPLDispTeenInspection_consDT as date))
						and DispClass_id = :DispClass_id
				";

				if ( in_array($data['DispClass_id'], array(10, 12)) ) {
					$query .= "
						and AgeGroupDisp_id = :AgeGroupDisp_id
					";
				}
				
				$query .= "
					limit 1
				";

				$result = $this->db->query($query, array(
					'DispClass_id' => $data['DispClass_id']
				,'EvnPLDispTeenInspection_consDT' => $data['EvnPLDispTeenInspection_consDate']
				,'Person_id' => $data['Person_id']
				,'AgeGroupDisp_id' => $data['AgeGroupDisp_id']
				));

				if ( !is_object($result) ) {
					$this->db->trans_rollback();
					return array(array('Error_Msg' => 'Ошибка при проверке наличия карт в указанном году'));
				}

				$resp = $result->result('array');

				if ( is_array($resp) && count($resp) > 0 && !empty($resp[0]['EvnPLDispTeenInspection_id']) ) {
					$this->db->trans_rollback();
					return array(array('Error_Msg' => 'У человека уже имеется сохраненный осмотр в указанном году.'));
				}
			}

			$EvnPLDispDopIsNew = true;

			$data['EducationInstitutionClass_id'] = null;
			if (!empty($data['EducationInstitutionType_id'])) {
				$data['EducationInstitutionClass_id'] = $this->getFirstResultFromQuery("
					SELECT
						EducationInstitutionClass_id as \"EducationInstitutionClass_id\"
					FROM v_EducationInstitutionClass
					WHERE EducationInstitutionType_id = :EducationInstitutionType_id
					limit 1
					", array('EducationInstitutionType_id' => $data['EducationInstitutionType_id']));
			}

			// добавляем новый талон ДД
			$query = "
				select
					EvnPLDispTeenInspection_id as \"EvnPLDispTeenInspection_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from p_EvnPLDispTeenInspection_ins(
					EvnPLDispTeenInspection_id := :EvnPLDispTeenInspection_id, 
					MedStaffFact_id := :MedStaffFact_id,
					PersonDispOrp_id := :PersonDispOrp_id,
					EvnPLDispTeenInspection_pid := null,
					Lpu_id := :Lpu_id, 
					Server_id := :Server_id, 
					PersonEvn_id := :PersonEvn_id, 
					EvnPLDispTeenInspection_setDT := :EvnPLDispTeenInspection_setDate, 
					EvnPLDispTeenInspection_disDT := null, 
					EvnPLDispTeenInspection_didDT := null, 
					Morbus_id := null, 
					EvnPLDispTeenInspection_IsSigned := null, 
					pmUser_signID := null, 
					EvnPLDispTeenInspection_signDT := null, 
					EvnPLDispTeenInspection_VizitCount := null, 
					EvnPLDispTeenInspection_IsFinish := 1, 
					EvnPLDispTeenInspection_IsTwoStage := 1, 
					Person_Age := null, 
					AttachType_id := 2, 
					Lpu_aid := null, 
					EvnPLDispTeenInspection_consDT := :EvnPLDispTeenInspection_consDate, 
					EvnPLDispTeenInspection_IsMobile := :EvnPLDispTeenInspection_IsMobile,
					EvnPLDispTeenInspection_IsOutLpu := :EvnPLDispTeenInspection_IsOutLpu,
					Lpu_mid := :Lpu_mid,
					DispClass_id := :DispClass_id, 
					PayType_id := :PayType_id, 
					EvnPLDispTeenInspection_fid := null, 
					EducationInstitutionClass_id := :EducationInstitutionClass_id, 
					AgeGroupDisp_id := :AgeGroupDisp_id,
					Org_id := :Org_id, 
					InstitutionNatureType_id := null, 
					InstitutionType_id := null, 
					EvnPLDispTeenInspection_eduDT := null,
					EvnDirection_id := :EvnDirection_id,
					pmUser_id := :pmUser_id
				)
			";
			$result = $this->db->query($query, array(
				'EvnPLDispTeenInspection_id' => $data['EvnPLDispTeenInspection_id'],
				'MedStaffFact_id' => !empty($data['session']['CurARM']['MedStaffFact_id'])?$data['session']['CurARM']['MedStaffFact_id']:null,
				'Lpu_id' => $data['Lpu_id'],
				'Server_id' => $data['Server_id'],
				'PersonEvn_id' => $data['PersonEvn_id'],
				'EvnDirection_id' => (!empty($data['EvnDirection_id']) ? $data['EvnDirection_id'] : NULL ),
				'EvnPLDispTeenInspection_setDate' => $data['EvnPLDispTeenInspection_setDate'],
				'EvnPLDispTeenInspection_consDate' => $data['EvnPLDispTeenInspection_consDate'],
				'EvnPLDispTeenInspection_IsMobile' => $data['EvnPLDispTeenInspection_IsMobile'],
				'EvnPLDispTeenInspection_IsOutLpu' => $data['EvnPLDispTeenInspection_IsOutLpu'],
				'Lpu_mid' => $data['Lpu_mid'],
				'DispClass_id' => $data['DispClass_id'],
				'PayType_id' => $data['PayType_id'],
				'PersonDispOrp_id' => $data['PersonDispOrp_id'],
				'AgeGroupDisp_id' => $data['AgeGroupDisp_id'],
				'EducationInstitutionClass_id' => $data['EducationInstitutionClass_id'],
				'Org_id' => $data['Org_id'],
				'pmUser_id' => $data['pmUser_id']
			));

			if ( is_object($result) ) {
				$resp = $result->result('array');
				if (isset($resp[0]['EvnPLDispTeenInspection_id'])) {
					$data['EvnPLDispTeenInspection_id'] = $resp[0]['EvnPLDispTeenInspection_id'];
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
					'Evn_id' => $data['EvnPLDispTeenInspection_id'],
					'pmUser_id' => $data['pmUser_id'],
					'PersonRequestSourceType_id' => 3,
				));
				$this->pmodel->isAllowTransaction = true;
				if (!$this->isSuccessful($resTmp) && !in_array($resTmp[0]['Error_Code'], array(302, 303))) {
					return array('Error_Msg' => $resTmp[0]['Error_Msg']);
				}
			}
		}

		// сохраняем данные по информир. добр. согласию для EvnPLDispTeenInspection_id = $data['EvnPLDispTeenInspection_id']
		ConvertFromWin1251ToUTF8($data['DopDispInfoConsentData']);
		$items = json_decode($data['DopDispInfoConsentData'], true);

		$itemsCount = 0;
		$savedDopDispInfoConsents = array();
		foreach($items as $item) {
			if (!empty($item['DopDispInfoConsent_IsEarlier']) && $item['DopDispInfoConsent_IsEarlier'] == '1') {
				$item['DopDispInfoConsent_IsEarlier'] = 2;
			} else {
				$item['DopDispInfoConsent_IsEarlier'] = 1;
			}

			if (!empty($item['DopDispInfoConsent_IsAgree']) && $item['DopDispInfoConsent_IsAgree'] == '1') {
				$item['DopDispInfoConsent_IsAgree'] = 2;
			} else {
				$item['DopDispInfoConsent_IsAgree'] = 1;
			}

			if ( $item['DopDispInfoConsent_IsEarlier'] == 2 || $item['DopDispInfoConsent_IsAgree'] == 2 ) {
				$itemsCount++;
			}

			// получаем идентификатор DopDispInfoConsent_id для SurveyTypeLink_id и EvnPLDisp_id (должна быть только одна запись для каждой пары значений)
			$item['DopDispInfoConsent_id'] = $this->getDopDispInfoConsentForSurveyTypeLink($data['EvnPLDispTeenInspection_id'], $item['SurveyTypeLink_id']);

			if (!empty($item['DopDispInfoConsent_id']) && $item['DopDispInfoConsent_id'] > 0) {
				$proc = 'p_DopDispInfoConsent_upd';
			} else {
				$proc = 'p_DopDispInfoConsent_ins';
				$item['DopDispInfoConsent_id'] = null;
			}

			// если убирают согласие для удалённого SurveyTypeLink, то удаляем его из DopDispInfoConsent. (refs #21573)
			if (!empty($item['DopDispInfoConsent_id']) && $item['DopDispInfoConsent_id'] > 0 && !empty($item['SurveyTypeLink_IsDel']) && $item['SurveyTypeLink_IsDel'] == '2' && $item['DopDispInfoConsent_IsEarlier'] == 1 && $item['DopDispInfoConsent_IsAgree'] == 1) {
				$query = "
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from p_DopDispInfoConsent_del(
						DopDispInfoConsent_id := :DopDispInfoConsent_id,
						pmUser_id := :pmUser_id
					)
				";
				$result = $this->db->query($query, array(
					'DopDispInfoConsent_id' => $item['DopDispInfoConsent_id'],
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
			} else {
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
					from {$proc}(
						DopDispInfoConsent_id := :DopDispInfoConsent_id, 
						EvnPLDisp_id := :EvnPLDispTeenInspection_id, 
						DopDispInfoConsent_IsAgree := :DopDispInfoConsent_IsAgree, 
						DopDispInfoConsent_IsEarlier := :DopDispInfoConsent_IsEarlier, 
						SurveyTypeLink_id := :SurveyTypeLink_id, 
						pmUser_id := :pmUser_id
					)
				";
				$result = $this->db->query($query, array(
					'EvnPLDispTeenInspection_id' => $data['EvnPLDispTeenInspection_id'],
					'DopDispInfoConsent_id' => $item['DopDispInfoConsent_id'],
					'DopDispInfoConsent_IsAgree' => $item['DopDispInfoConsent_IsAgree'],
					'DopDispInfoConsent_IsEarlier' => $item['DopDispInfoConsent_IsEarlier'],
					'SurveyTypeLink_id' => $item['SurveyTypeLink_id'],
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
					} else if ( is_array($res) && count($res) > 0 ) {
						$savedDopDispInfoConsents[] = $res[0]['DopDispInfoConsent_id'];
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

		/*
		 * Т.к. набор согласий мог поменяться если выбрана другая возрастная группа или другой тип образовательного учреждения,
		 * то все согласия которые не были сохранены сейчас нужно либо удалить, либо пометить отказом.
		 */
		if (count($savedDopDispInfoConsents) > 0)
		{
			$query = "
				select
					DDIC.DopDispInfoConsent_id as \"DopDispInfoConsent_id\",
					DDIC.SurveyTypeLink_id as \"SurveyTypeLink_id\"
				from
					v_DopDispInfoConsent DDIC
				where
					DDIC.DopDispInfoConsent_id is not null
					and DDIC.DopDispInfoConsent_id not in (".implode(',',$savedDopDispInfoConsents).")
					and DDIC.EvnPLDisp_id = :EvnPLDispTeenInspection_id
			";

			$wrongddics = $this->db->query($query, array(
				'EvnPLDispTeenInspection_id' => $data['EvnPLDispTeenInspection_id']
			));

			if ( is_object($wrongddics) ) {
				$wrongddics_array = $wrongddics->result('array');
				foreach($wrongddics_array as $wr) {
					// Удаляем
					// @task https://redmine.swan.perm.ru/issues/68534
					$query = "
						select
							Error_Code as \"Error_Code\",
							Error_Message as \"Error_Msg\"
						from p_DopDispInfoConsent_del(
							DopDispInfoConsent_id := :DopDispInfoConsent_id,
							pmUser_id := :pmUser_id
						)
					";
					$result = $this->db->query($query, array(
						'DopDispInfoConsent_id' => $wr['DopDispInfoConsent_id'],
						'pmUser_id' => $data['pmUser_id']
					));

					if ( !is_object($result) ) {
						$this->db->trans_rollback();
						return array(
							'success' => false,
							'Error_Msg' => 'Ошибка при выполнении запроса к базе данных (строка ' . __LINE__ . ')'
						);
					}

					$res = $result->result('array');

					if ( !is_array($res) || count($res) == 0 ) {
						$this->db->trans_rollback();
						return array(
							'success' => false,
							'Error_Msg' => 'Ошибка при выполнении запроса к базе данных (строка ' . __LINE__ . ')'
						);
					}
					else if ( !empty($res[0]['Error_Msg']) ) {
						$this->db->trans_rollback();
						return array(
							'success' => false,
							'Error_Msg' => $res[0]['Error_Msg']
						);
					}
				}
			}

		}

		if ( $EvnPLDispDopIsNew === false )  {
			// Обновляем дату EvnPLDispTeenInspection_consDate и чистим атрибуты на карте, если пациент отказался от ДД
			$query = "
				select
					 EvnPLDispTeenInspection_pid as \"EvnPLDispTeenInspection_pid\"
					,EvnPLDispTeenInspection_rid as \"EvnPLDispTeenInspection_rid\"
					,EvnPLDispTeenInspection_fid as \"EvnPLDispTeenInspection_fid\"
					,Lpu_id as \"Lpu_id\"
					,Server_id as \"Server_id\"
					,PersonEvn_id as \"PersonEvn_id\"
					,to_char(EvnPLDispTeenInspection_disDT, 'yyyy-mm-dd') as \"EvnPLDispTeenInspection_disDT\"
					,to_char(EvnPLDispTeenInspection_didDT, 'yyyy-mm-dd') as \"EvnPLDispTeenInspection_didDT\"
					,Morbus_id as \"Morbus_id\"
					,EvnPLDispTeenInspection_IsSigned as \"EvnPLDispTeenInspection_IsSigned\"
					,EvnPLDispTeenInspection_IndexRep as \"EvnPLDispTeenInspection_IndexRep\"
					,EvnPLDispTeenInspection_IndexRepInReg as \"EvnPLDispTeenInspection_IndexRepInReg\"
					,EvnDirection_aid as \"EvnDirection_aid\"
					,pmUser_signID as \"pmUser_signID\"
					,EvnPLDispTeenInspection_signDT as \"EvnPLDispTeenInspection_signDT\"
					,EvnPLDispTeenInspection_IsFinish as \"EvnPLDispTeenInspection_IsFinish\"
					,EvnPLDispTeenInspection_IsTwoStage as \"EvnPLDispTeenInspection_IsTwoStage\"
					,Person_Age as \"Person_Age\"
					,AttachType_id as \"AttachType_id\"
					,Lpu_aid as \"Lpu_aid\"
					,DispClass_id as \"DispClass_id\"
					,EducationInstitutionClass_id as \"EducationInstitutionClass_id\"
					,AgeGroupDisp_id as \"AgeGroupDisp_id\"
					,EvnDirection_id as \"EvnDirection_id\"
					,PersonDispOrp_id as \"PersonDispOrp_id\"
					," . ($itemsCount == 0 ? "null as " : "Org_id") . "\"Org_id\"
					," . ($itemsCount == 0 ? "null as " : "InstitutionNatureType_id") . "\"InstitutionNatureType_id\"
					," . ($itemsCount == 0 ? "null as " : "InstitutionType_id") . "\"InstitutionType_id\"
					," . ($itemsCount == 0 ? "null as " : "EvnPLDispTeenInspection_eduDT") . "\"EvnPLDispTeenInspection_eduDT\"
				from v_EvnPLDispTeenInspection
				where EvnPLDispTeenInspection_id = :EvnPLDispTeenInspection_id
				limit 1
			";
			$result = $this->db->query($query, array(
				'EvnPLDispTeenInspection_id' => $data['EvnPLDispTeenInspection_id']
			));

			if ( is_object($result) ) {
				$resp = $result->result('array');

				if ( is_array($resp) && count($resp) > 0 ) {
					$resp[0]['EvnPLDispTeenInspection_consDT'] = $data['EvnPLDispTeenInspection_consDate'];
					$resp[0]['EvnPLDispTeenInspection_setDT'] = $data['EvnPLDispTeenInspection_setDate'];
					$resp[0]['pmUser_id'] = $data['pmUser_id'];
					$resp[0]['AgeGroupDisp_id'] = $data['AgeGroupDisp_id'];
					$resp[0]['Org_id'] = $data['Org_id'];
					$resp[0]['EvnPLDispTeenInspection_IsMobile'] = $data['EvnPLDispTeenInspection_IsMobile'];
					$resp[0]['EvnPLDispTeenInspection_IsOutLpu'] = $data['EvnPLDispTeenInspection_IsOutLpu'];
					$resp[0]['Lpu_mid'] = $data['Lpu_mid'];
					$resp[0]['PayType_id'] = $data['PayType_id'];
					$resp[0]['MedStaffFact_id'] = !empty($data['session']['CurARM']['MedStaffFact_id'])?$data['session']['CurARM']['MedStaffFact_id']:null;
					if (!empty($data['EducationInstitutionType_id'])) {
						$EducationInstitutionClass_id = $this->getFirstResultFromQuery("
							SELECT
								EducationInstitutionClass_id as \"EducationInstitutionClass_id\"
							FROM v_EducationInstitutionClass
							WHERE EducationInstitutionType_id = :EducationInstitutionType_id
								and EducationInstitutionClass_id = :EducationInstitutionClass_id
							limit 1
						", array('EducationInstitutionType_id' => $data['EducationInstitutionType_id'], 'EducationInstitutionClass_id' => $resp[0]['EducationInstitutionClass_id']));
						if (empty($EducationInstitutionClass_id)) {
							$EducationInstitutionClass_id = $this->getFirstResultFromQuery("
							SELECT
								EducationInstitutionClass_id as \"EducationInstitutionClass_id\"
							FROM v_EducationInstitutionClass
							WHERE EducationInstitutionType_id = :EducationInstitutionType_id
							limit 1
						", array('EducationInstitutionType_id' => $data['EducationInstitutionType_id']));
						}
						$resp[0]['EducationInstitutionClass_id'] = $EducationInstitutionClass_id;
					}

					$query = "
						select
							EvnPLDispTeenInspection_id as \"DopDispInfoConsent_id\",
							Error_Code as \"Error_Code\",
							Error_Message as \"Error_Msg\"
						from p_EvnPLDispTeenInspection_upd(
							EvnPLDispTeenInspection_id := :EvnPLDispTeenInspection_id
					";

					foreach ( $resp[0] as $key => $value ) {
						$query .= "," . $key . " := :" . $key;
					}

					$query .= "
							)
					";

					$resp[0]['EvnPLDispTeenInspection_id'] = $data['EvnPLDispTeenInspection_id'];
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
				$attrArray[] = 'AssessmentHealth'; // Оценка здоровья
				$attrArray[] = 'DopDispInfoConsent';
			}

			foreach ( $attrArray as $attr ) {
				$deleteResult = $this->deleteAttributes($attr, $data['EvnPLDispTeenInspection_id'], $data['pmUser_id']);

				if ( !empty($deleteResult) ) {
					$this->db->trans_rollback();
					return array(
						'success' => false,
						'Error_Msg' => $deleteResult . ' (строка ' . __LINE__ . ')'
					);
				}
			}
		}

		// проставляем признак отказа
		if ( $itemsCount == 0 ) {
			$query = "
				update EvnPLDisp
				set EvnPLDisp_IsRefusal = 2
				where Evn_id = :EvnPLDisp_id
			";
			$this->db->query($query, array(
				'EvnPLDisp_id' => $data['EvnPLDispTeenInspection_id']
			));
		} else {
			$query = "
				update EvnPLDisp
				set EvnPLDisp_IsRefusal = 1
				where Evn_id = :EvnPLDisp_id
			";
			$this->db->query($query, array(
				'EvnPLDisp_id' => $data['EvnPLDispTeenInspection_id']
			));
		}

		$query = "
			update EvnPLBase
			set EvnPLBase_IsFinish = 1
			where Evn_id = :EvnPLBase_id
		";
		$this->db->query($query, array(
			'EvnPLBase_id' => $data['EvnPLDispTeenInspection_id']
		));

		$this->db->trans_commit();

		return array(
			'success' => true,
			'Error_Msg' => '',
			'EvnPLDispTeenInspection_id' => $data['EvnPLDispTeenInspection_id']
		);
	}

	/**
	 * Получение списка "Диагнозы и рекоменации"
	 * Входящие данные: $data['EvnPLDispTeenInspection_id']
	 * На выходе: ассоциативный массив результатов запроса
	 */
	function loadEvnDiagAndRecomendationGrid($data)
	{
		$query = "
			select
				EVDD.EvnVizitDispDop_id as \"EvnVizitDispDop_id\",
				D.Diag_id as \"Diag_id\",
				D.Diag_Code || '. ' || D.Diag_Name as \"Diag_Name\",
				coalesce(ODS.OrpDispSpec_Name, mso.MedSpecOms_Name) as \"OrpDispSpec_Name\"
			from v_EvnVizitDispDop EVDD
				left join v_DopDispInfoConsent DDIC on DDIC.DopDispInfoConsent_id = EVDD.DopDispInfoConsent_id
				left join v_SurveyTypeLink STL on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
				left join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
				left join v_OrpDispSpec ODS on ODS.OrpDispSpec_id = ST.OrpDispSpec_id
				left join v_Diag D on D.Diag_id = EVDD.Diag_id
				left join v_MedStaffFact msf on msf.MedStaffFact_id = evdd.MedStaffFact_id
				left join v_MedSpecOms mso on mso.MedSpecOms_id = msf.MedSpecOms_id
			where EVDD.EvnVizitDispDop_pid = ?
				and LEFT(D.Diag_Code, 1) <> 'Z'
		";
		$result = $this->db->query($query, array($data['EvnPLDispTeenInspection_id']));

		if (is_object($result))
		{
			$resp = $result->result('array');
			foreach($resp as &$item) {
				// для каждой строки получаем данные формы "Состояние здоровья: Редактирование" и запихиваем в JSON
				$query = "
					select
						EVDD.DopDispDiagType_id as \"DopDispDiagType_id\",
						EVDD.DispSurveilType_id as \"DispSurveilType_id\",
						coalesce(EVDD.EvnVizitDispDop_IsVMP,1) as \"EvnVizitDisp_IsVMP\",
						EVDD.EvnVizitDispDop_IsFirstTime as \"EvnVizitDisp_IsFirstTime\",
						coalesce(MC1.ConditMedCareType_nid,1) as \"ConditMedCareType1_nid\",
						MC1.PlaceMedCareType_nid as \"PlaceMedCareType1_nid\",
						MC1.ConditMedCareType_id as \"ConditMedCareType1_id\",
						MC1.PlaceMedCareType_id as \"PlaceMedCareType1_id\",
						MC1.LackMedCareType_id as \"LackMedCareType1_id\",
						coalesce(MC2.ConditMedCareType_nid,1) as \"ConditMedCareType2_nid\",
						MC2.PlaceMedCareType_nid as \"PlaceMedCareType2_nid\",
						MC2.ConditMedCareType_id as \"ConditMedCareType2_id\",
						MC2.PlaceMedCareType_id as \"PlaceMedCareType2_id\",
						MC2.LackMedCareType_id as \"LackMedCareType2_id\",
						coalesce(MC3.ConditMedCareType_nid,1) as \"ConditMedCareType3_nid\",
						MC3.PlaceMedCareType_nid as \"PlaceMedCareType3_nid\",
						MC3.ConditMedCareType_id as \"ConditMedCareType3_id\",
						MC3.PlaceMedCareType_id as \"PlaceMedCareType3_id\",
						MC3.LackMedCareType_id as \"LackMedCareType3_id\"
					from v_EvnVizitDispDop EVDD
						left join v_Diag D on D.Diag_id = EVDD.Diag_id
						-- дополнительные консультации и исследования
						left join lateral(
							select *
							from v_MedCare MC
							where MC.MedCareType_id = 1
								and MC.EvnVizitDisp_id = EVDD.EvnVizitDispDop_id
							limit 1
						) MC1 on true
						-- лечение
						left join lateral(
							select *
							from v_MedCare MC
							where MC.MedCareType_id = 2
								and MC.EvnVizitDisp_id = EVDD.EvnVizitDispDop_id
							limit 1
						) MC2 on true
						-- медицинская реабилитация / санаторно-курортное лечение
						left join lateral(
							select *
							from v_MedCare MC
							where MC.MedCareType_id = 3
								and MC.EvnVizitDisp_id = EVDD.EvnVizitDispDop_id
							limit 1
						) MC3 on true
					where EVDD.EvnVizitDispDop_id = :EvnVizitDispDop_id
					limit 1
				";
				$resultmc = $this->db->query($query, [
					'EvnVizitDispDop_id' => $item['EvnVizitDispDop_id']
				]);
				$item['FormDataJSON'] = json_encode([]);
				if (is_object($resultmc))
				{
					$respmc = $resultmc->result('array');
					if (count($respmc) > 0) {
						$item['FormDataJSON'] = json_encode($respmc[0]);
					}
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
	 * Получение списка "Диагнозы и рекоменации"
	 * Входящие данные: $data['EvnPLDispTeenInspection_id']
	 * На выходе: ассоциативный массив результатов запроса
	 */
	function loadEvnDiagAndRecomendationSecGrid($data)
	{
		$query = "
			select
				EVZDD.EvnVizitDispDop_id as \"EvnVizitDispDop_id\",
				D.Diag_id as \"Diag_id\",
				D.Diag_Code || '. ' || D.Diag_Name as \"Diag_Name\",
				UC.UslugaComplex_Name as \"UslugaComplex_Name\"
			from v_EvnVizitDispDop EVZDD
				left join v_UslugaComplex UC on UC.UslugaComplex_id = EVZDD.UslugaComplex_id
				left join v_Diag D on D.Diag_id = EVZDD.Diag_id
			where EVZDD.EvnVizitDispDop_pid = :EvnVizitDispDop_pid
				and LEFT(D.Diag_Code,1) <> 'Z'
		";
		$result = $this->db->query($query, [
			'EvnVizitDispDop_pid' => $data['EvnPLDispTeenInspection_id']
		]);

		if (is_object($result))
		{
			$resp = $result->result('array');
			foreach($resp as &$item) {
				// для каждой строки получаем данные формы "Состояние здоровья: Редактирование" и запихиваем в JSON
				$query = "
					select
						EVDO.DispSurveilType_id as \"DispSurveilType_id\",
						coalesce(EVDO.EvnVizitDispDop_IsVMP,1) as \"EvnVizitDisp_IsVMP\",
						EVDO.EvnVizitDispDop_IsFirstTime as \"EvnVizitDisp_IsFirstTime\",
						coalesce(MC1.ConditMedCareType_nid,1) as \"ConditMedCareType1_nid\",
						MC1.PlaceMedCareType_nid as \"PlaceMedCareType1_nid\",
						MC1.ConditMedCareType_id as \"ConditMedCareType1_id\",
						MC1.PlaceMedCareType_id as \"PlaceMedCareType1_id\",
						MC1.LackMedCareType_id as \"LackMedCareType1_id\",
						MC2.ConditMedCareType_nid as \"ConditMedCareType2_nid\",
						MC2.PlaceMedCareType_nid as \"PlaceMedCareType2_nid\",
						coalesce(MC2.ConditMedCareType_id,1) as \"ConditMedCareType2_id\",
						MC2.PlaceMedCareType_id as \"PlaceMedCareType2_id\",
						MC2.LackMedCareType_id as \"LackMedCareType2_id\",
						MC3.ConditMedCareType_nid as \"ConditMedCareType3_nid\",
						MC3.PlaceMedCareType_nid as \"PlaceMedCareType3_nid\",
						coalesce(MC3.ConditMedCareType_id,1) as \"ConditMedCareType3_id\",
						MC3.PlaceMedCareType_id as \"PlaceMedCareType3_id\",
						MC3.LackMedCareType_id as \"LackMedCareType3_id\"
					from v_EvnVizitDispDop EVDO
						left join v_Diag D on D.Diag_id = EVDO.Diag_id
						-- дополнительные консультации и исследования
						left join lateral(
							select *
							from v_MedCare MC
							where MC.MedCareType_id = 1
								and MC.EvnVizitDisp_id = EVDO.EvnVizitDispDop_id
							limit 1
						) MC1 on true
						-- лечение
						left join lateral(
							select *
							from v_MedCare MC
							where MC.MedCareType_id = 2
								and MC.EvnVizitDisp_id = EVDO.EvnVizitDispDop_id
							limit 1
						) MC2 on true
						-- медицинская реабилитация / санаторно-курортное лечение
						left join lateral(
							select *
							from v_MedCare MC
							where MC.MedCareType_id = 3
								and MC.EvnVizitDisp_id = EVDO.EvnVizitDispDop_id
							limit 1
						) MC3 on true
					where EVDO.EvnVizitDispDop_id = :EvnVizitDispDop_id
					limit 1
				";
				$resultmc = $this->db->query($query, array('EvnVizitDispDop_id' => $item['EvnVizitDispDop_id']));
				$item['FormDataJSON'] = json_encode(array());
				if (is_object($resultmc))
				{
					$respmc = $resultmc->result('array');
					if (count($respmc) > 0) {
						$item['FormDataJSON'] = json_encode($respmc[0]);
					}
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
	 * Получение списка осмотров врача-специалиста в талоне по ДД
	 * Входящие данные: $data['EvnPLDispTeenInspection_id']
	 * На выходе: ассоциативный массив результатов запроса
	 */
	function loadEvnVizitDispDopSecGrid($data)
	{

		$query = "
			select
				EVZDD.EvnVizitDispDop_id as \"EvnVizitDispDop_id\",
				EVZDD.Server_id as \"Server_id\",
				EVZDD.PersonEvn_id as \"PersonEvn_id\",
				to_char(EVZDD.EvnVizitDispDop_setDate, 'dd.mm.yyyy') as \"EvnVizitDispDop_setDate\",
				EVZDD.EvnVizitDispDop_setTime as \"EvnVizitDispDop_setTime\",
				to_char(EVZDD.EvnVizitDispDop_disDate, 'dd.mm.yyyy') as \"EvnVizitDispDop_disDate\",
				EVZDD.EvnVizitDispDop_disTime as \"EvnVizitDispDop_disTime\",
				RTRIM(LS.LpuSection_Name) as \"LpuSection_Name\",
				RTRIM(MP.Person_Fio) as \"MedPersonal_Fio\",
				RTRIM(UC.UslugaComplex_Name) as \"UslugaComplex_Name\",
				UC.UslugaComplex_Code as \"UslugaComplex_Code\",
				RTRIM(D.Diag_Code) as \"Diag_Code\",
				EVZDD.MedPersonal_id as \"MedPersonal_id\",
				EVZDD.UslugaComplex_id as \"UslugaComplex_id\",
				EVZDD.DopDispInfoConsent_id as \"DopDispInfoConsent_id\",
				EVZDD.LpuSection_id as \"LpuSection_id\",
				EVZDD.Lpu_id as \"Lpu_uid\",
				EVZDD.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				EVZDD.MedSpecOms_id as \"MedSpecOms_id\",
				EVZDD.Diag_id as \"Diag_id\",
				EVZDD.DopDispDiagType_id as \"DopDispDiagType_id\",
				EVZDD.DopDispAlien_id as \"DopDispAlien_id\",
				DDA.DopDispAlien_Name as \"DopDispAlien_Name\",
				1 as \"Record_Status\"
			from v_EvnVizitDispDop EVZDD
				left join LpuSection LS on LS.LpuSection_id = EVZDD.LpuSection_id
				left join v_MedPersonal MP on MP.MedPersonal_id = EVZDD.MedPersonal_id
				left join v_UslugaComplex UC on UC.UslugaComplex_id = EVZDD.UslugaComplex_id
				left join v_DopDispAlien DDA on DDA.DopDispAlien_id = EVZDD.DopDispAlien_id
				left join Diag D on D.Diag_id = EVZDD.Diag_id
			where
				EVZDD.EvnVizitDispDop_pid = :EvnPLDispTeenInspection_id
				AND EVZDD.Diag_id IS NOT NULL
		";

		$result = $this->db->query($query, array(
			'EvnPLDispTeenInspection_id' => $data['EvnPLDispTeenInspection_id']
		));

		if (is_object($result))
		{
			$resp = $result->result('array');
			foreach($resp as &$respone) {
				$respone['EvnDiagDopDispGridData'] = array();
				// для каждого осмотра надо подгрузить сопутствующие диагнозы
				$query = "
					select
						EDDD.EvnDiagDopDisp_id as \"EvnDiagDopDisp_id\",
						EDDD.Diag_id as \"Diag_id\",
						EDDD.DeseaseDispType_id as \"DeseaseDispType_id\",
						D.Diag_Code as \"Diag_Code\",
						D.Diag_Name as \"Diag_Name\",
						DDT.DeseaseDispType_Name as \"DeseaseDispType_Name\",
						1 as \"Record_Status\"
					from
						v_EvnDiagDopDisp EDDD
						left join v_DeseaseDispType DDT on DDT.DeseaseDispType_id = EDDD.DeseaseDispType_id
						left join v_Diag D on D.Diag_id = EDDD.Diag_id
					where
						EDDD.EvnDiagDopDisp_pid = :EvnDiagDopDisp_pid
						and EDDD.DiagSetClass_id = 3
					order by
						EDDD.EvnDiagDopDisp_id
				";

				$result_dddgd = $this->db->query($query, array(
					'EvnDiagDopDisp_pid' => $respone['EvnVizitDispDop_id']
				));

				if ( is_object($result_dddgd) ) {
					$respone['EvnDiagDopDispGridData'] = $result_dddgd->result('array');
				}

				$respone['EvnDiagDopDispGridData'] = json_encode($respone['EvnDiagDopDispGridData']);
			}
			return $resp;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Получение списка исследований в талоне по ДД
	 * Входящие данные: $data['EvnPLDispTeenInspection_id']
	 * На выходе: ассоциативный массив результатов запроса
	 */
	function loadEvnUslugaDispDopSecGrid($data)
	{

		$query = "
			select
				EUDD.EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\",
				EUDD.Server_id as \"Server_id\",
				EUDD.PersonEvn_id as \"PersonEvn_id\",
				to_char(EUDD.EvnUslugaDispDop_setDate, 'dd.mm.yyyy') as \"EvnUslugaDispDop_setDate\",
				EUDD.EvnUslugaDispDop_setTime as \"EvnUslugaDispDop_setTime\",
				to_char(EUDD.EvnUslugaDispDop_disDate, 'dd.mm.yyyy') as \"EvnUslugaDispDop_disDate\",
				EUDD.EvnUslugaDispDop_disTime as \"EvnUslugaDispDop_disTime\",
				to_char(EUDD.EvnUslugaDispDop_didDate, 'dd.mm.yyyy') as \"EvnUslugaDispDop_didDate\",
				EUDD.LpuSection_uid as \"LpuSection_id\",
				EUDD.Lpu_id as \"Lpu_uid\",
				EUDD.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				EUDD.MedSpecOms_id as \"MedSpecOms_id\",
				RTRIM(LS.LpuSection_Name) as \"LpuSection_Name\",
				EUDD.MedPersonal_id as \"MedPersonal_id\",
				RTRIM(MP.Person_Fio) as \"MedPersonal_Fio\",
				EUDD.UslugaComplex_id as \"UslugaComplex_id\",
				RTRIM(UC.UslugaComplex_Name) as \"UslugaComplex_Name\",
				RTRIM(UC.UslugaComplex_Code) as \"UslugaComplex_Code\",
				EUDD.ExaminationPlace_id as \"ExaminationPlace_id\",
				EP.ExaminationPlace_Name as \"ExaminationPlace_Name\",
				EUDD.EvnUslugaDispDop_Result as \"EvnUslugaDispDop_Result\",
				1 as \"Record_Status\"
			from
			 	v_EvnVizitDispDop EVDD
				inner join v_EvnUslugaDispDop EUDD on EUDD.EvnUslugaDispDop_pid = EVDD.EvnVizitDispDop_id
				left join v_LpuSection LS on LS.LpuSection_id = EUDD.LpuSection_uid
				left join v_MedPersonal MP on MP.MedPersonal_id = EUDD.MedPersonal_id
				left join v_UslugaComplex UC on UC.UslugaComplex_id = EUDD.UslugaComplex_id
				left join v_ExaminationPlace EP on EP.ExaminationPlace_id = EUDD.ExaminationPlace_id
			where
				EVDD.EvnVizitDispDop_pid = :EvnPLDispTeenInspection_id
				AND EVDD.Diag_id IS NULL
		";

		$result = $this->db->query($query, array(
			'EvnPLDispTeenInspection_id' => $data['EvnPLDispTeenInspection_id']
		));

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
	 *	Получение идентификатора медицинской помощи
	 */
	function getMedCareForEvnVizitDispDop($EvnVizitDispDop_id, $MedCareType_id) {
		$query = "
			select
				MedCare_id as \"MedCare_id\"
			from
				v_MedCare
			where
				EvnVizitDisp_id = :EvnVizitDispDop_id
				and MedCareType_id = :MedCareType_id
			limit 1
		";

		$result = $this->db->query($query, array(
			'EvnVizitDispDop_id' => $EvnVizitDispDop_id,
			'MedCareType_id' => $MedCareType_id
		));

		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return $resp[0]['MedCare_id'];
			}
		}

		return null;
	}

	/**
	 *	Сохранение оказания медицинской помощи
	 */
	function saveMedCare($data) {
		if (!empty($data['MedCare_id']) && $data['MedCare_id'] > 0) {
			$proc = 'p_MedCare_upd';
		} else {
			$proc = 'p_MedCare_ins';
			$data['MedCare_id'] = null;
		}

		if (empty($data['EvnVizitDispDop_id'])) {
			$data['EvnVizitDispDop_id'] = $data['EvnVizitDispDop_id'];
		}

		$query = "
			select
				MedCare_id as \"MedCare_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$proc}(
				MedCare_id := :MedCare_id, 
				EvnVizitDisp_id := :EvnVizitDispDop_id,
				LackMedCareType_id := :LackMedCareType_id,
				ConditMedCareType_nid := :ConditMedCareType_nid,
				PlaceMedCareType_nid := :PlaceMedCareType_nid,
				ConditMedCareType_id := :ConditMedCareType_id,
				PlaceMedCareType_id := :PlaceMedCareType_id,
				MedCareType_id := :MedCareType_id,
				pmUser_id := :pmUser_id
			)
		";
		// echo getDebugSql($query, $data); die();
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *	Сохранение списка диагнозов и рекомендаций
	 */
	function saveEvnDiagAndRecomendation($data) {
		// получаем MedCare_id для MedCareType_id = 1
		$json = json_decode($data['FormDataJSON'], true);
		$MedCare_id = $this->getMedCareForEvnVizitDispDop($data['EvnVizitDispDop_id'], 1);
		$this->saveMedCare(array(
			'MedCare_id' => $MedCare_id,
			'EvnVizitDispDop_id' => $data['EvnVizitDispDop_id'],
			'ConditMedCareType_nid' => empty($json['ConditMedCareType1_nid'])?null:$json['ConditMedCareType1_nid'],
			'PlaceMedCareType_nid' => empty($json['PlaceMedCareType1_nid'])?null:$json['PlaceMedCareType1_nid'],
			'ConditMedCareType_id' => empty($json['ConditMedCareType1_id'])?null:$json['ConditMedCareType1_id'],
			'PlaceMedCareType_id' => empty($json['PlaceMedCareType1_id'])?null:$json['PlaceMedCareType1_id'],
			'LackMedCareType_id' => empty($json['LackMedCareType1_id'])?null:$json['LackMedCareType1_id'],
			'MedCareType_id' => 1,
			'pmUser_id' => $data['pmUser_id']
		));

		// получаем MedCare_id для MedCareType_id = 2
		$MedCare_id = $this->getMedCareForEvnVizitDispDop($data['EvnVizitDispDop_id'], 2);
		$this->saveMedCare(array(
			'MedCare_id' => $MedCare_id,
			'EvnVizitDispDop_id' => $data['EvnVizitDispDop_id'],
			'ConditMedCareType_nid' => empty($json['ConditMedCareType2_nid'])?null:$json['ConditMedCareType2_nid'],
			'PlaceMedCareType_nid' => empty($json['PlaceMedCareType2_nid'])?null:$json['PlaceMedCareType2_nid'],
			'ConditMedCareType_id' => empty($json['ConditMedCareType2_id'])?null:$json['ConditMedCareType2_id'],
			'PlaceMedCareType_id' => empty($json['PlaceMedCareType2_id'])?null:$json['PlaceMedCareType2_id'],
			'LackMedCareType_id' => empty($json['LackMedCareType2_id'])?null:$json['LackMedCareType2_id'],
			'MedCareType_id' => 2,
			'pmUser_id' => $data['pmUser_id']
		));

		// получаем MedCare_id для MedCareType_id = 3
		$MedCare_id = $this->getMedCareForEvnVizitDispDop($data['EvnVizitDispDop_id'], 3);
		$this->saveMedCare(array(
			'MedCare_id' => $MedCare_id,
			'EvnVizitDispDop_id' => $data['EvnVizitDispDop_id'],
			'ConditMedCareType_nid' => empty($json['ConditMedCareType3_nid'])?null:$json['ConditMedCareType3_nid'],
			'PlaceMedCareType_nid' => empty($json['PlaceMedCareType3_nid'])?null:$json['PlaceMedCareType3_nid'],
			'ConditMedCareType_id' => empty($json['ConditMedCareType3_id'])?null:$json['ConditMedCareType3_id'],
			'PlaceMedCareType_id' => empty($json['PlaceMedCareType3_id'])?null:$json['PlaceMedCareType3_id'],
			'LackMedCareType_id' => empty($json['LackMedCareType3_id'])?null:$json['LackMedCareType3_id'],
			'MedCareType_id' => 3,
			'pmUser_id' => $data['pmUser_id']
		));

		// сохраняем вмп и и Диспансерное наблюдение DispSurveilType_id и EvnVizitDisp_IsVMP
		// пока просто update todo
		$query = "
			update EvnVizitDisp
			set DispSurveilType_id = :DispSurveilType_id,
				EvnVizitDisp_IsVMP = :EvnVizitDisp_IsVMP,
				EvnVizitDisp_IsFirstTime = :EvnVizitDisp_IsFirstTime
			where Evn_id = :EvnVizitDisp_id
		";

		$result = $this->db->query($query, array(
			'DispSurveilType_id' => empty($json['DispSurveilType_id'])?null:$json['DispSurveilType_id'],
			'EvnVizitDisp_IsVMP' => empty($json['EvnVizitDisp_IsVMP'])?null:$json['EvnVizitDisp_IsVMP'],
			'EvnVizitDisp_IsFirstTime' => empty($json['EvnVizitDisp_IsFirstTime'])?null:$json['EvnVizitDisp_IsFirstTime'],
			'EvnVizitDisp_id' => $data['EvnVizitDispDop_id'],
		));

		return array(array('Error_Msg' => ''));
	}

	/**
	 *	Загрузка списка добровольного информированного согласия
	 */
	function loadDopDispInfoConsent($data) {
		$filter = "";
		$joinList = array();
		$params = array(
			'EvnPLDispTeenInspection_id' => $data['EvnPLDispTeenInspection_id'],
			'Person_id' => $data['Person_id'],
			'DispClass_id' => $data['DispClass_id'],
			'EvnPLDispTeenInspection_consDate' => $data['EvnPLDispTeenInspection_consDate']
		);

		if ( getRegionNick() == 'ufa' ) { // для уфы дополнительно отфильтровываем услуги посещений по LpuLevel
			$filter .= " and (coalesce(ucat.UslugaCategory_SysNick, '') != 'lpusection' or ((case when LpuLevel.LpuLevel_code in (2,6) then '6' when LpuLevel.LpuLevel_code in (3,5) then '5' when LpuLevel.LpuLevel_code in (1,8) then '8' end) = left(UC.UslugaComplex_Code,1)))";
			$filter .= " and (UC.UslugaComplex_id is not null or Stl.UslugaComplex_id is null)";

			$joinList[] = "left join v_UslugaComplex UC on UC.UslugaComplex_id = STL.UslugaComplex_id";
			$joinList[] = "left join v_Lpu lpu on lpu.Lpu_id = :Lpu_id";
			$joinList[] = "left join v_LpuLevel LpuLevel on LpuLevel.LpuLevel_id = lpu.LpuLevel_id";
			$joinList[] = "left join v_UslugaCategory ucat on ucat.UslugaCategory_id = UC.UslugaCategory_id";

			$params['Lpu_id'] = $data['Lpu_id'];
		}

		$orderby = "
			order by
				case when DDIC.DopDispInfoConsent_id is not null then 0 else 1 end asc -- в первую очередь сохраненные
		";

		if ( getRegionNick() == 'ekb' ) {
			$joinList[] = "left join r66.v_UslugaComplexPartitionLink ucpl on ucpl.UslugaComplex_id = STL.UslugaComplex_id";
			$orderby = "
				order by
					case when DDIC.DopDispInfoConsent_id is not null then 0 else 1 end asc, -- в первую очередь сохраненные
					case when ucpl.MedSpecOms_id is null then 0 else 1 end asc -- иначе те у которых MedSpecOms_id пустой
			";
		}

		if (!empty($data['EducationInstitutionType_id'])) {
			$filter .= " and coalesce(STL.EducationInstitutionType_id, :EducationInstitutionType_id) = :EducationInstitutionType_id";
			$params['EducationInstitutionType_id'] = $data['EducationInstitutionType_id'];
		}

		$preselect = "";
		$agefilter = "and ((select age from mv) between coalesce(SurveyTypeLink_From, 0) and  coalesce(SurveyTypeLink_To, 999)) -- по возрасту, в принципе по библии Иссак лет 800 жил же";
		if (!empty($data['AgeGroupDisp_id'])) {
			$preselect = "
				, pres as (
					select
						AgeGroupDisp_From as agfrom,
						AgeGroupDisp_To as agto,
						AgeGroupDisp_monthFrom as mfrom,
						AgeGroupDisp_monthTo as mto
					from v_AgeGroupDisp agd
					where agd.AgeGroupDisp_id = :AgeGroupDisp_id
					limit 1
				)
			";
			$params['AgeGroupDisp_id'] = $data['AgeGroupDisp_id'];

			$agefilter = "
				and ((
					coalesce(SurveyTypeLink_From, 0) = coalesce((select agfrom from pres), 0)
					and coalesce(SurveyTypeLink_To, 0) = coalesce((select agto from pres), 0)
					and coalesce(SurveyTypeLink_monthFrom, 0) = coalesce((select mfrom from pres), 0)
					and coalesce(SurveyTypeLink_monthTo, 0) = coalesce((select mto from pres), 0)
				) or ST.SurveyType_Code IN (50,67,68))
			";
		}

		$query = "
			with mv as (
				select
					coalesce(Sex_id, 3) as sex_id,
					dbo.Age2(Person_BirthDay, CAST(:EvnPLDispTeenInspection_consDate as date)) as age
				from v_PersonState ps
				where ps.Person_id = :Person_id
				limit 1
			){$preselect}
			
			select
				coalesce(STL.DopDispInfoConsent_id, -STL.SurveyTypeLink_id) as \"DopDispInfoConsent_id\",
				STL.EvnPLDisp_id as \"EvnPLDispTeenInspection_id\",
				STL.SurveyTypeLink_id as \"SurveyTypeLink_id\",
				coalesce(STL.SurveyTypeLink_IsNeedUsluga, 1) as \"SurveyTypeLink_IsNeedUsluga\",
				coalesce(STL.SurveyTypeLink_IsDel, 1) as \"SurveyTypeLink_IsDel\",
				ST.SurveyType_Code as \"SurveyType_Code\",
				ST.SurveyType_Name as \"SurveyType_Name\",
				ST.SurveyType_IsVizit as \"SurveyType_IsVizit\",
				case WHEN (STL.DopDispInfoConsent_id is null AND :EvnPLDispTeenInspection_id is NULL) or STL.DopDispInfoConsent_IsAgree = 2 then 1 else 0 end as \"DopDispInfoConsent_IsAgree\",
				case WHEN STL.DopDispInfoConsent_IsEarlier = 2 then 1 else 0 end as \"DopDispInfoConsent_IsEarlier\"
			from
				v_SurveyType ST
				inner join lateral(
					-- не удалённые
					(select
						STL.SurveyTypeLink_id,
						STL.UslugaComplex_id,
						STL.SurveyTypeLink_IsNeedUsluga,
						STL.SurveyTypeLink_IsDel,
						DDIC.DopDispInfoConsent_id,
						DDIC.EvnPLDisp_id,
						DDIC.DopDispInfoConsent_IsEarlier,
						DDIC.DopDispInfoConsent_IsAgree
					from
						v_SurveyTypeLink STL
						left join v_DopDispInfoConsent DDIC on DDIC.SurveyTypeLink_id = STL.SurveyTypeLink_id and DDIC.EvnPLDisp_id = :EvnPLDispTeenInspection_id
						" . implode(' ', $joinList) . "
					where
						ST.SurveyType_id = STL.SurveyType_id
						and coalesce(STL.DispClass_id, :DispClass_id) = :DispClass_id -- этап
						and (coalesce(STL.Sex_id, (select sex_id from mv)) = (select sex_id from mv)) -- по полу
						{$agefilter}
						and coalesce(STL.SurveyTypeLink_IsDel, 1) = 1
						and (STL.SurveyTypeLink_begDate is null or STL.SurveyTypeLink_begDate <= :EvnPLDispTeenInspection_consDate)
						and (STL.SurveyTypeLink_endDate is null or STL.SurveyTypeLink_endDate >= :EvnPLDispTeenInspection_consDate)
						{$filter}
					{$orderby}
					limit 1)
						
					union
					
					-- удалённые, но сохранённые в согласии
					(select
						STL.SurveyTypeLink_id as \"SurveyTypeLink_id\",
						STL.UslugaComplex_id as \"UslugaComplex_id\",
						STL.SurveyTypeLink_IsNeedUsluga as \"SurveyTypeLink_IsNeedUsluga\",
						STL.SurveyTypeLink_IsDel as \"SurveyTypeLink_IsDel\",
						DDIC.DopDispInfoConsent_id as \"DopDispInfoConsent_id\",
						DDIC.EvnPLDisp_id as \"EvnPLDisp_id\",
						DDIC.DopDispInfoConsent_IsEarlier as \"DopDispInfoConsent_IsEarlier\",
						DDIC.DopDispInfoConsent_IsAgree as \"DopDispInfoConsent_IsAgree\"
					from
						v_SurveyTypeLink STL
						inner join v_DopDispInfoConsent DDIC on DDIC.SurveyTypeLink_id = STL.SurveyTypeLink_id and DDIC.EvnPLDisp_id = :EvnPLDispTeenInspection_id
						" . implode(' ', $joinList) . "
					where
						ST.SurveyType_id = STL.SurveyType_id
						and coalesce(STL.DispClass_id, :DispClass_id) = :DispClass_id -- этап
						and (coalesce(STL.Sex_id, (select sex_id from mv)) = (select sex_id from mv)) -- по полу
						{$agefilter}
						and STL.SurveyTypeLink_IsDel = 2
						and (STL.SurveyTypeLink_begDate is null or STL.SurveyTypeLink_begDate <= :EvnPLDispTeenInspection_consDate)
						and (STL.SurveyTypeLink_endDate is null or STL.SurveyTypeLink_endDate >= :EvnPLDispTeenInspection_consDate)
						{$filter}
					limit 1)
				) STL on true
			order by
				case when ST.SurveyType_Code IN (50,67,68) then 0 else 1 end, ST.SurveyType_Code
			
		";
		// echo getDebugSql($query, $params); die();
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получение списка исследований в талоне по ДД
	 * Входящие данные: $data['EvnPLDispTeenInspection_id']
	 * На выходе: ассоциативный массив результатов запроса
	 */
	function loadEvnUslugaDispDopGrid($data)
	{
		$query = "
			select
				DDIC.DopDispInfoConsent_id as \"DopDispInfoConsent_id\",
				STL.SurveyTypeLink_id as \"SurveyTypeLink_id\",
				STL.DispClass_id as \"DispClass_id\",
				ODS.OrpDispSpec_Code as \"OrpDispSpec_Code\",
				ST.SurveyType_Name as \"SurveyType_Name\",
				ST.SurveyType_Code as \"SurveyType_Code\",
				coalesce(ST.SurveyType_IsVizit, 1) as \"SurveyType_IsVizit\",
				EUDDData.EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\",
				EUDDData.EvnUslugaDispDop_ExamPlace as \"EvnUslugaDispDop_ExamPlace\",
				to_char(EUDDData.EvnUslugaDispDop_setDate + CAST(EUDDData.EvnUslugaDispDop_setTime as time), 'DD Mon YYYY HH24:MI:SS') as \"EvnUslugaDispDop_setDate\",
				to_char(EUDDData.EvnUslugaDispDop_didDate, 'dd.mm.yyyy') as \"EvnUslugaDispDop_didDate\",
				case when el.Evn_lid is not null then 'true' else 'false' end as \"EvnUslugaDispDop_WithDirection\",
				ST.OrpDispSpec_id as \"OrpDispSpec_id\"
			from v_DopDispInfoConsent DDIC
				left join v_SurveyTypeLink STL on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
				left join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
				left join lateral (
					Select * from v_EvnDirection ed where ed.DopDispInfoConsent_id = ddic.DopDispInfoConsent_id and ed.EvnStatus_id not in (12,13) limit 1
				) ed on true
				left join v_OrpDispSpec ODS on ODS.OrpDispSpec_id = ST.OrpDispSpec_id
				left join lateral(
					select
						EUDD.EvnUslugaDispDop_id,
						EUDD.EvnUslugaDispDop_setDate,
						EUDD.EvnUslugaDispDop_setTime,
						EUDD.EvnUslugaDispDop_didDate,
						EUDD.EvnUslugaDispDop_ExamPlace
					from
						v_EvnUslugaDispDop EUDD
					where
						EUDD.EvnUslugaDispDop_pid = :EvnPLDispTeenInspection_id
						and coalesce(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
					limit 1
				) EUDDData on true
				left join v_EvnLink el on el.Evn_id = EUDDData.EvnUslugaDispDop_id
			where DDIC.EvnPLDisp_id = :EvnPLDispTeenInspection_id
				and ST.SurveyType_Code = 2
				and (DDIC.DopDispInfoConsent_IsAgree = 2 OR DDIC.DopDispInfoConsent_IsEarlier = 2)
				and (coalesce(STL.SurveyTypeLink_IsDel, 1) = 1 or EUDDData.EvnUslugaDispDop_id is not null ) -- только если сохранено согласие

			union

			select
				DDIC.DopDispInfoConsent_id as \"DopDispInfoConsent_id\",
				STL.SurveyTypeLink_id as \"SurveyTypeLink_id\",
				STL.DispClass_id as \"DispClass_id\",
				ODS.OrpDispSpec_Code as \"OrpDispSpec_Code\",
				ST.SurveyType_Name as \"SurveyType_Name\",
				ST.SurveyType_Code as \"SurveyType_Code\",
				coalesce(ST.SurveyType_IsVizit, 1) as \"SurveyType_IsVizit\",
				EUDDData.EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\",
				EUDDData.EvnUslugaDispDop_ExamPlace as \"EvnUslugaDispDop_ExamPlace\",
				to_char(EUDDData.EvnUslugaDispDop_setDate + CAST(EUDDData.EvnUslugaDispDop_setTime as time), 'DD Mon YYYY HH24:MI:SS') as \"EvnUslugaDispDop_setDate\",
				to_char(EUDDData.EvnUslugaDispDop_didDate, 'dd.mm.yyyy') as \"EvnUslugaDispDop_didDate\",
				case when el.Evn_lid is not null then 'true' else 'false' end as \"EvnUslugaDispDop_WithDirection\",
				ST.OrpDispSpec_id as \"OrpDispSpec_id\"
			from v_DopDispInfoConsent DDIC
				left join v_SurveyTypeLink STL on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
				left join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
				left join lateral (
					Select * from v_EvnDirection ed where ed.DopDispInfoConsent_id = ddic.DopDispInfoConsent_id and ed.EvnStatus_id not in (12,13) limit 1
				) ed on true
				left join v_OrpDispSpec ODS on ODS.OrpDispSpec_id = ST.OrpDispSpec_id
				left join lateral(
					select
						EUDD.EvnUslugaDispDop_id,
						EUDD.EvnUslugaDispDop_setDate,
						EUDD.EvnUslugaDispDop_setTime,
						EUDD.EvnUslugaDispDop_didDate,
						EUDD.EvnUslugaDispDop_ExamPlace
					from v_EvnUslugaDispDop EUDD
						left join v_EvnVizitDispDop EVDD on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
					where
						EVDD.EvnVizitDispDop_pid = :EvnPLDispTeenInspection_id
						and EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
						and coalesce(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
					limit 1
				) EUDDData on true
				left join v_EvnLink el on el.Evn_id = EUDDData.EvnUslugaDispDop_id
			where DDIC.EvnPLDisp_id = :EvnPLDispTeenInspection_id
				and (DDIC.DopDispInfoConsent_IsAgree = 2 OR DDIC.DopDispInfoConsent_IsEarlier = 2)
				and ST.SurveyType_Code NOT IN (2,50,67,68)
				and (coalesce(STL.SurveyTypeLink_IsDel, 1) = 1 or EUDDData.EvnUslugaDispDop_id is not null ) -- только если сохранено согласие
		";

		$result = $this->db->query($query, array('EvnPLDispTeenInspection_id' => $data['EvnPLDispTeenInspection_id']));

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
	 *	Удаление карты мед. осмотра несовершеннолетнего
	 */
	function deleteEvnPLDispTeenInspection($data) {
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_EvnPLDispTeenInspection_del(
				EvnPLDispTeenInspection_id := :EvnPLDispTeenInspection_id,
				pmUser_id := :pmUser_id
			)
		";
		$result = $this->db->query($query, array(
			'EvnPLDispTeenInspection_id' => $data['EvnPLDispTeenInspection_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление талона ДД)');
		}

		$attrArray = array(
			'DopDispInfoConsent'
		);
		foreach ( $attrArray as $attr ) {
			$deleteResult = $this->deleteAttributes($attr, $data['EvnPLDispTeenInspection_id'], $data['pmUser_id']);

			if ( !empty($deleteResult) ) {
				$this->db->trans_rollback();
				return array(
					'success' => false,
					'Error_Msg' => $deleteResult . ' (строка ' . __LINE__ . ')'
				);
			}
		}
	}

	/**
	 *	Получение данных для формы редактирования карты мед. осмотра несовершеннолетнего
	 */
	function loadEvnPLDispTeenInspectionEditForm($data)
	{
		$accessType = '
			case
				when EPLDTI.Lpu_id = :Lpu_id then 1
				' . (count($data['session']['linkedLpuIdList']) > 1 ? 'when EPLDTI.Lpu_id in (' . implode(',', $data['session']['linkedLpuIdList']) . ') and coalesce(EPLDTI.EvnPLDispTeenInspection_IsTransit, 1) = 2 then 1' : '') . '
				else 0
			end = 1
		';

		if ( $data['session']['region']['nick'] == 'ekb' ) {
			$accessType .= " and coalesce(EPLDTI.EvnPLDispTeenInspection_isPaid, 1) = 1";
		}
		if ( $data['session']['region']['nick'] == 'pskov' ) {
			$accessType .= "and coalesce(EPLDTI.EvnPLDispTeenInspection_isPaid, 1) = 1
				and not exists(
					select
						RD.Registry_id
					from r60.v_RegistryData RD
						inner join v_Registry R on R.Registry_id = RD.Registry_id
						inner join v_RegistryStatus RS on RS.RegistryStatus_id = R.RegistryStatus_id
					where
						RD.Evn_id = EPLDTI.EvnPLDispTeenInspection_id
						and RS.RegistryStatus_SysNick not in ('work','paid')
					limit 1
				)
			";
		}

		$query = "
			SELECT
				case when {$accessType} then 'edit' else 'view' end as \"accessType\",
				EPLDTI.EvnPLDispTeenInspection_id as \"EvnPLDispTeenInspection_id\",
				coalesce(EPLDTI.EvnPLDispTeenInspection_IsPaid, 1) as \"EvnPLDispTeenInspection_IsPaid\",
				coalesce(EPLDTI.EvnPLDispTeenInspection_IndexRep, 0) as \"EvnPLDispTeenInspection_IndexRep\",
				coalesce(EPLDTI.EvnPLDispTeenInspection_IndexRepInReg, 1) as \"EvnPLDispTeenInspection_IndexRepInReg\",
				EPLDTI.EvnPLDispTeenInspection_fid as \"EvnPLDispTeenInspection_fid\",
				EPLDTI.Person_id as \"Person_id\",
				EPLDTI.PersonEvn_id as \"PersonEvn_id\",
				EPLDTI.PersonDispOrp_id as \"PersonDispOrp_id\",
				coalesce(EPLDTI.DispClass_id, 6) as \"DispClass_id\",
				EPLDTI.PayType_id as \"PayType_id\",
				EPLDTI.EvnPLDispTeenInspection_pid as \"EvnPLDispTeenInspection_pid\",
				to_char(EPLDTI.EvnPLDispTeenInspection_setDate, 'dd.mm.yyyy') as \"EvnPLDispTeenInspection_setDate\",
				to_char(EPLDTI_FIR.EvnPLDispTeenInspection_setDate, 'dd.mm.yyyy') as \"EvnPLDispTeenInspection_firSetDate\",
				to_char(EPLDTI.EvnPLDispTeenInspection_disDate, 'dd.mm.yyyy') as \"EvnPLDispTeenInspection_disDate\",
				to_char(EPLDTI.EvnPLDispTeenInspection_consDT, 'dd.mm.yyyy') as \"EvnPLDispTeenInspection_consDate\",
				to_char(EPLDTI.EvnPLDispTeenInspection_eduDT, 'dd.mm.yyyy') as \"EvnPLDispTeenInspection_eduDT\",
				EPLDTI.Server_id as \"Server_id\",
				case when EPLDTI.EvnPLDispTeenInspection_IsMobile = 2 then 1 else 0 end as \"EvnPLDispTeenInspection_IsMobile\",
				case when EPLDTI.EvnPLDispTeenInspection_IsOutLpu = 2 then 1 else 0 end as \"EvnPLDispTeenInspection_IsOutLpu\",
				EPLDTI.Lpu_mid as \"Lpu_mid\",
				coalesce(EPLDTI.EvnPLDispTeenInspection_IsFinish, 1) as \"EvnPLDispTeenInspection_IsFinish\",
				coalesce(EPLDTI.EvnPLDispTeenInspection_IsTwoStage, 1) as \"EvnPLDispTeenInspection_IsTwoStage\",
				EPLDTI.EvnPLDispTeenInspection_fid as \"EvnPLDispTeenInspection_fid\",
				coalesce(EIC.EducationInstitutionType_id, PDISPORP.EducationInstitutionType_id) as \"EducationInstitutionType_id\",
				EPLDTI.EducationInstitutionClass_id as \"EducationInstitutionClass_id\",
				coalesce(EPLDTI.Org_id, PDISPORP.Org_id) as \"Org_id\",
				CASE WHEN coalesce(EPLDTI.Org_id, PDISPORP.Org_id) is null THEN 'false' ELSE 'true' END as \"OrgExist\",
				coalesce(EPLDTI.AgeGroupDisp_id, PDISPORP.AgeGroupDisp_id) as \"AgeGroupDisp_id\",
				EPLDTI.InstitutionNatureType_id as \"InstitutionNatureType_id\",
				EPLDTI.InstitutionType_id as \"InstitutionType_id\",
				AH.AssessmentHealth_Weight as \"AssessmentHealth_Weight\",
				AH.AssessmentHealth_Height as \"AssessmentHealth_Height\",
				AH.AssessmentHealth_Head as \"AssessmentHealth_Head\",
				case when AH.WeightAbnormType_id is null then 1 else 2 end as \"WeightAbnormType_YesNo\",
				AH.WeightAbnormType_id as \"WeightAbnormType_id\",
				case when AH.HeightAbnormType_id is null then 1 else 2 end as \"HeightAbnormType_YesNo\",
				AH.HeightAbnormType_id as \"HeightAbnormType_id\",
				AH.AssessmentHealth_Gnostic as \"AssessmentHealth_Gnostic\",
				AH.AssessmentHealth_Motion as \"AssessmentHealth_Motion\",
				AH.AssessmentHealth_Social as \"AssessmentHealth_Social\",
				AH.AssessmentHealth_Speech as \"AssessmentHealth_Speech\",
				AH.AssessmentHealth_P as \"AssessmentHealth_P\",
				AH.AssessmentHealth_Ax as \"AssessmentHealth_Ax\",
				AH.AssessmentHealth_Fa as \"AssessmentHealth_Fa\",
				AH.AssessmentHealth_Ma as \"AssessmentHealth_Ma\",
				AH.AssessmentHealth_Me as \"AssessmentHealth_Me\",
				AH.AssessmentHealth_Years as \"AssessmentHealth_Years\",
				AH.AssessmentHealth_Month as \"AssessmentHealth_Month\",
				AH.AssessmentHealth_VaccineName as \"AssessmentHealth_VaccineName\",
				AH.AssessmentHealth_HealthRecom as \"AssessmentHealth_HealthRecom\",
				AH.AssessmentHealth_DispRecom as \"AssessmentHealth_DispRecom\",
				case when AH.AssessmentHealth_IsRegular = 2 then 1 else 0 end as \"AssessmentHealth_IsRegular\",
				case when AH.AssessmentHealth_IsIrregular = 2 then 1 else 0 end as \"AssessmentHealth_IsIrregular\",
				case when AH.AssessmentHealth_IsAbundant = 2 then 1 else 0 end as \"AssessmentHealth_IsAbundant\",
				case when AH.AssessmentHealth_IsModerate = 2 then 1 else 0 end as \"AssessmentHealth_IsModerate\",
				case when AH.AssessmentHealth_IsScanty = 2 then 1 else 0 end as \"AssessmentHealth_IsScanty\",
				case when AH.AssessmentHealth_IsPainful = 2 then 1 else 0 end as \"AssessmentHealth_IsPainful\",
				case when AH.AssessmentHealth_IsPainless = 2 then 1 else 0 end as \"AssessmentHealth_IsPainless\",
				AH.InvalidType_id as \"InvalidType_id\",
				to_char(AH.AssessmentHealth_setDT, 'dd.mm.yyyy') as \"AssessmentHealth_setDT\",
				to_char(AH.AssessmentHealth_reExamDT, 'dd.mm.yyyy') as \"AssessmentHealth_reExamDT\",
				AH.InvalidDiagType_id as \"InvalidDiagType_id\",
				case when AH.AssessmentHealth_IsMental = 2 then 1 else 0 end as \"AssessmentHealth_IsMental\",
				case when AH.AssessmentHealth_IsOtherPsych = 2 then 1 else 0 end as \"AssessmentHealth_IsOtherPsych\",
				case when AH.AssessmentHealth_IsLanguage = 2 then 1 else 0 end as \"AssessmentHealth_IsLanguage\",
				case when AH.AssessmentHealth_IsVestibular = 2 then 1 else 0 end as \"AssessmentHealth_IsVestibular\",
				case when AH.AssessmentHealth_IsVisual = 2 then 1 else 0 end as \"AssessmentHealth_IsVisual\",
				case when AH.AssessmentHealth_IsMeals = 2 then 1 else 0 end as \"AssessmentHealth_IsMeals\",
				case when AH.AssessmentHealth_IsMotor = 2 then 1 else 0 end as \"AssessmentHealth_IsMotor\",
				case when AH.AssessmentHealth_IsDeform = 2 then 1 else 0 end as \"AssessmentHealth_IsDeform\",
				case when AH.AssessmentHealth_IsGeneral = 2 then 1 else 0 end as \"AssessmentHealth_IsGeneral\",
				to_char(AH.AssessmentHealth_ReabDT, 'dd.mm.yyyy') as \"AssessmentHealth_ReabDT\",
				AH.RehabilitEndType_id as \"RehabilitEndType_id\",
				CASE WHEN AH.AssessmentHealth_id IS NULL THEN 1 else AH.ProfVaccinType_id end as \"ProfVaccinType_id\",
				AH.HealthGroupType_oid as \"HealthGroupType_oid\",
				AH.HealthGroupType_id as \"HealthGroupType_id\",
				AH.HealthKind_id as \"HealthKind_id\",
				AH.NormaDisturbanceType_id as \"NormaDisturbanceType_id\",
				AH.NormaDisturbanceType_uid as \"NormaDisturbanceType_uid\",
				AH.NormaDisturbanceType_eid as \"NormaDisturbanceType_eid\",
				to_char(ecp.EvnCostPrint_setDT, 'dd.mm.yyyy') as \"EvnCostPrint_setDT\",
				ecp.EvnCostPrint_Number as \"EvnCostPrint_Number\",
				ecp.EvnCostPrint_IsNoPrint as \"EvnCostPrint_IsNoPrint\",
				EPLDTI.EvnPLDispTeenInspection_IsSuspectZNO as \"EvnPLDispTeenInspection_IsSuspectZNO\",
				EPLDTI.Diag_spid as \"Diag_spid\",
				AH.AssessmentHealth_id as \"AssessmentHealth_id\"
			FROM
				v_EvnPLDispTeenInspection EPLDTI
				left join v_EvnCostPrint ecp on ecp.Evn_id = EPLDTI.EvnPLDispTeenInspection_id
				left join v_EvnPLDispTeenInspection EPLDTI_FIR on EPLDTI_FIR.EvnPLDispTeenInspection_id = EPLDTI.EvnPLDispTeenInspection_fid
				left join v_EducationInstitutionClass EIC on EIC.EducationInstitutionClass_id = EPLDTI.EducationInstitutionClass_id
				left join lateral(
					select * from v_AssessmentHealth where EvnPLDisp_id = EPLDTI.EvnPLDispTeenInspection_id limit 1
				) AH on true
				left join lateral(
					select EducationInstitutionType_id, AgeGroupDisp_id, Org_id from v_PersonDispOrp pdo where pdo.PersonDispOrp_id = EPLDTI.PersonDispOrp_id limit 1
				) PDISPORP on true
			WHERE
				(1 = 1)
				and EPLDTI.EvnPLDispTeenInspection_id = :EvnPLDispTeenInspection_id
			limit 1
		";

		$result = $this->db->query($query, array('Lpu_id' => $data['Lpu_id'], 'EvnPLDispTeenInspection_id' => $data['EvnPLDispTeenInspection_id']));

		if (is_object($result))
		{
			$resp = $result->result('array');
			$AssessmentHealthVaccinData = array();
			if (!empty($resp[0]['AssessmentHealth_id'])) {
				// получаем данные прививок
				$query = "
					select
						AssessmentHealthVaccin_id as \"AssessmentHealthVaccin_id\",
						VaccinType_id as \"VaccinType_id\"
					from
						v_AssessmentHealthVaccin
					where
						AssessmentHealth_id = :AssessmentHealth_id
				";
				$resp_vac = $this->queryResult($query, array(
					'AssessmentHealth_id' => $resp[0]['AssessmentHealth_id']
				));
				foreach($resp_vac as $resp_vacone) {
					$AssessmentHealthVaccinData[] = $resp_vacone['VaccinType_id'];
				}
			}
			if (!empty($resp[0])) {
				$resp[0]['AssessmentHealthVaccinData'] = $AssessmentHealthVaccinData;
			}
			return $resp;
		}
		else
		{
			return false;
		}
	}

	/**
	 *	Получение списка направлений
	 */
	function loadEvnUslugaDispDopGridForDirection($data) {
		$filter = "";
		$joinList = array();
		$params = array(
			'Person_id' => $data['Person_id'],
			'DispClass_id' => $data['DispClass_id'],
			'PersonDispOrp_id' => $data['PersonDispOrp_id'],
			'EvnPLDispTeenInspection_consDate' => date('Y-m-d') // на текущую дату
		);

		$params['EvnPLDispTeenInspection_id'] = null; // надо найти карту, если есть
		$query = "
			select
				EvnPLDispTeenInspection_id as \"EvnPLDispTeenInspection_id\"
			from
				v_EvnPLDispTeenInspection
			where
				PersonDispOrp_id = :PersonDispOrp_id
			limit 1
		";
		$result = $this->db->query($query, $params);
		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (is_array($resp) && count($resp) > 0) {
				$params['EvnPLDispTeenInspection_id'] = $resp[0]['EvnPLDispTeenInspection_id'];
			}
		}

		if ( empty($params['EvnPLDispTeenInspection_id']) ) {
			$filter .= " and coalesce(STL.SurveyTypeLink_IsDel, 1) = 1";
		}
		else {
			$filter .= " and (coalesce(STL.SurveyTypeLink_IsDel, 1) = 1 or EUDDData.EvnUslugaDispDop_id is not null)";
		}

		if ( $data['session']['region']['nick'] == 'ufa' ) { // для уфы дополнительно отфильтровываем услуги посещений по LpuLevel
			$filter .= " and (coalesce(ucat.UslugaCategory_SysNick, '') != 'lpusection' or ((case when LpuLevel.LpuLevel_code in (2,6) then '6' when LpuLevel.LpuLevel_code in (3,5) then '5' when LpuLevel.LpuLevel_code in (1,8) then '8' end) = left(UC.UslugaComplex_Code,1)))";
			$filter .= " and (UC.UslugaComplex_id is not null or Stl.UslugaComplex_id is null)";

			$joinList[] = "left join v_Lpu lpu on lpu.Lpu_id = :Lpu_id";
			$joinList[] = "left join v_LpuLevel LpuLevel on LpuLevel.LpuLevel_id = lpu.LpuLevel_id";
			$joinList[] = "left join v_UslugaCategory ucat on ucat.UslugaCategory_id = UC.UslugaCategory_id";

			$params['Lpu_id'] = $data['Lpu_id'];
		}

		if (!empty($data['EducationInstitutionType_id'])) {
			$filter .= " and coalesce(STL.EducationInstitutionType_id, :EducationInstitutionType_id) = :EducationInstitutionType_id";
			$params['EducationInstitutionType_id'] = $data['EducationInstitutionType_id'];
		}

		$preselect = "";
		$agefilter = "and ((select age from mv) between coalesce(SurveyTypeLink_From, 0) and  coalesce(SurveyTypeLink_To, 999)) -- по возрасту, в принципе по библии Иссак лет 800 жил же";
		if (!empty($data['AgeGroupDisp_id'])) {
			$preselect = "
				, pres as (
					select
						AgeGroupDisp_From as agfrom,
						AgeGroupDisp_To as agto,
						AgeGroupDisp_monthFrom as mfrom,
						AgeGroupDisp_monthTo as mto
					from v_AgeGroupDisp agd
					where agd.AgeGroupDisp_id = :AgeGroupDisp_id
					limit 1
				)
			";
			$params['AgeGroupDisp_id'] = $data['AgeGroupDisp_id'];

			$agefilter = "
				and (
					coalesce(SurveyTypeLink_From, 0) = coalesce((select agfrom from pres), 0)
					and coalesce(SurveyTypeLink_To, 0) = coalesce((select agto from pres), 0)
					and coalesce(SurveyTypeLink_monthFrom, 0) = coalesce((select mfrom from pres), 0)
					and coalesce(SurveyTypeLink_monthTo, 0) = coalesce((select mto from pres), 0)
				)
			";
		}

		$query = "
			with mv as(
				select
					coalesce(Sex_id, 3) as sex_id,
					dbo.Age2(Person_BirthDay, :EvnPLDispTeenInspection_consDate) as age
				from v_PersonState ps
				where ps.Person_id = :Person_id
				limit 1
			){$preselect}
			
			select
				MAX(STL.SurveyTypeLink_id) as \"SurveyTypeLink_id\",
				MAX(DDIC.EvnPLDisp_id) as \"EvnPLDispTeenInspection_id\",
				coalesce(MAX(STL.SurveyTypeLink_IsDel), 1) as \"SurveyTypeLink_IsDel\",
				MAX(ST.SurveyType_Code) as \"SurveyType_Code\",
				MAX(ST.SurveyType_Name) as \"SurveyType_Name\",
				MAX(EUDDData.EvnUslugaDispDop_id) as \"EvnUslugaDispDop_id\",
				MAX(EUDDData.EvnUslugaDispDop_ExamPlace) as \"EvnUslugaDispDop_ExamPlace\",
				to_char(MAX(EUDDData.EvnUslugaDispDop_setDT), 'yyyy-mm-dd') as \"EvnUslugaDispDop_setDate\",
				case when MAX(ST.SurveyType_Code) IN (50,67,68) then 0 else 1 end
			from v_SurveyTypeLink STL
				left join v_DopDispInfoConsent DDIC on DDIC.SurveyTypeLink_id = STL.SurveyTypeLink_id
					and DDIC.EvnPLDisp_id = :EvnPLDispTeenInspection_id
				left join lateral(
					select
						EUDD.EvnUslugaDispDop_id,
						EUDD.EvnUslugaDispDop_setDT,
						EUDD.EvnUslugaDispDop_didDate,
						EUDD.EvnUslugaDispDop_ExamPlace
					from v_EvnUslugaDispDop EUDD
						left join v_EvnVizitDispDop EVDD on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
					where
						EVDD.EvnVizitDispDop_pid = :EvnPLDispTeenInspection_id
						and EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
						and :PersonDispOrp_id IS NOT NULL
						and coalesce(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
					limit 1
				) EUDDData on true
				left join v_UslugaComplex UC on UC.UslugaComplex_id = STL.UslugaComplex_id
				left join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
				" . implode(' ', $joinList) . "
			where 
				coalesce(STL.DispClass_id, :DispClass_id) = :DispClass_id -- этап
				and (coalesce(STL.Sex_id, (select sex_id from mv)) = (select sex_id from mv)) -- по полу
				{$agefilter}
				and (STL.SurveyTypeLink_begDate is null or STL.SurveyTypeLink_begDate <= :EvnPLDispTeenInspection_consDate)
				and (STL.SurveyTypeLink_endDate is null or STL.SurveyTypeLink_endDate >= :EvnPLDispTeenInspection_consDate)
				and ST.SurveyType_id NOT IN (1,2,48,49,50,67,68) -- нужны только услуги
				" . $filter . "
			group by ST.SurveyType_id, STL.SurveyTypeLink_IsDel
			order by case when MAX(ST.SurveyType_Code) IN (50,67,68) then 0 else 1 end, MAX(ST.SurveyType_Code)
			
		";
		// echo getDebugSql($query, $params); die();
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Сохранение направления на осмотр/исследование по доп. диспансеризации
	 */
	function saveEvnUslugaDispDopDirection($data) {
		$this->db->trans_begin();

		// 1. Ищем карту дд
		$data['EvnPLDispTeenInspection_id'] = null; // надо найти карту, если есть
		$query = "
			select
				EvnPLDispTeenInspection_id as \"EvnPLDispTeenInspection_id\"
			from
				v_EvnPLDispTeenInspection
			where
				PersonDispOrp_id = :PersonDispOrp_id
			limit 1
		";
		$result = $this->db->query($query, $data);

		if ( !is_object($result) ) {
			$this->db->trans_rollback();
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (получение идентификатора карты)'));
		}

		$resp = $result->result('array');

		if ( is_array($resp) && count($resp) == 1 ) {
			$data['EvnPLDispTeenInspection_id'] = $resp[0]['EvnPLDispTeenInspection_id'];
		}

		// 2. Если не нашли карту дд, то создаём новую
		if ( empty($data['EvnPLDispTeenInspection_id']) ) {
			$data['MedStaffFact_id'] = !empty($data['session']['CurARM']['MedStaffFact_id'])?$data['session']['CurARM']['MedStaffFact_id']:null;

			$query = "
				with mv as (
					select
						PayType_id as pt
					from v_PayType
					where PayType_SysNick = 'oms'
					limit 1
				)
				
				select
					EvnPLDispTeenInspection_id as \"EvnPLDispTeenInspection_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from p_EvnPLDispTeenInspection_ins(
					EvnPLDispTeenInspection_id := :EvnPLDispTeenInspection_id,
					MedStaffFact_id := :MedStaffFact_id,
					EvnPLDispTeenInspection_pid := null, 
					PersonDispOrp_id := :PersonDispOrp_id,
					Lpu_id := :Lpu_id, 
					Server_id := :Server_id, 
					PersonEvn_id := :PersonEvn_id, 
					EvnPLDispTeenInspection_setDT := :EvnUslugaDispDop_setDate, 
					EvnPLDispTeenInspection_disDT := null, 
					EvnPLDispTeenInspection_didDT := null, 
					Morbus_id := null, 
					EvnPLDispTeenInspection_IsSigned := null, 
					pmUser_signID := null, 
					EvnPLDispTeenInspection_signDT := null, 
					EvnPLDispTeenInspection_VizitCount := null, 
					EvnPLDispTeenInspection_IsFinish := 1, 
					EvnPLDispTeenInspection_IsTwoStage := 1,
					Person_Age := null, 
					AttachType_id := 2, 
					Lpu_aid := null, 
					EvnPLDispTeenInspection_consDT := :EvnUslugaDispDop_setDate, 
					DispClass_id := :DispClass_id, 
					PayType_id := (select pt from mv), 
					EvnPLDispTeenInspection_fid := null, 
					EducationInstitutionClass_id := null, 
					AgeGroupDisp_id := null, 
					Org_id := null, 
					InstitutionNatureType_id := null, 
					InstitutionType_id := null, 
					EvnPLDispTeenInspection_eduDT := null,
					EvnPLDispTeenInspection_IsRefusal := 1,
					pmUser_id := :pmUser_id
				)
			";

			// echo getDebugSql($query, $data);die();

			$result = $this->db->query($query, $data);

			if ( !is_object($result) ) {
				$this->db->trans_rollback();
				return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение карты)'));
			}

			$res = $result->result('array');

			if ( !is_array($res) || count($res) == 0 ) {
				$this->db->trans_rollback();
				return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение карты)'));
			}
			else if ( !empty($res[0]['Error_Msg']) ) {
				$this->db->trans_rollback();
				return $res;
			}

			$data['EvnPLDispTeenInspection_id'] = $res[0]['EvnPLDispTeenInspection_id'];
		}

		$data['EvnVizitDispDop_pid'] = $data['EvnPLDispTeenInspection_id'];

		// 3. ищем соответсвующее SurveyTypeLink_id и EvnPLDisp_id согласие
		$data['DopDispInfoConsent_id'] = NULL;

		$query = "
			select
				DopDispInfoConsent_id as \"DopDispInfoConsent_id\"
			from
				v_DopDispInfoConsent
			where
				EvnPLDisp_id = :EvnPLDispTeenInspection_id
				and SurveyTypeLink_id = :SurveyTypeLink_id
			limit 1
		";
		$result = $this->db->query($query, $data);

		if ( !is_object($result) ) {
			$this->db->trans_rollback();
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (получение идентификатора добровольного информированного согласия)'));
		}

		$res = $result->result('array');

		if ( is_array($res) && count($res) == 1 ) {
			$data['DopDispInfoConsent_id'] = $res[0]['DopDispInfoConsent_id'];
		}

		// 4. проставляем согласие по услуге
		if ( empty($data['DopDispInfoConsent_id']) ) {
			$proc = 'p_DopDispInfoConsent_ins';
		}
		else {
			$proc = 'p_DopDispInfoConsent_upd';
		}

		$query = "
			select
				DopDispInfoConsent_id as \"DopDispInfoConsent_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$proc}(
				DopDispInfoConsent_id := :DopDispInfoConsent_id, 
				EvnPLDisp_id := :EvnPLDispTeenInspection_id, 
				DopDispInfoConsent_IsAgree := :DopDispInfoConsent_IsAgree, 
				DopDispInfoConsent_IsEarlier := :DopDispInfoConsent_IsEarlier, 
				SurveyTypeLink_id := :SurveyTypeLink_id, 
				pmUser_id := :pmUser_id
			)
		";
		$result = $this->db->query($query, array(
			'EvnPLDispTeenInspection_id' => $data['EvnPLDispTeenInspection_id'],
			'DopDispInfoConsent_id' => $data['DopDispInfoConsent_id'],
			'DopDispInfoConsent_IsAgree' => 2,
			'DopDispInfoConsent_IsEarlier' => 1,
			'SurveyTypeLink_id' => $data['SurveyTypeLink_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if ( !is_object($result) ) {
			$this->db->trans_rollback();
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение добровольного информированного согласия)'));
		}

		$res = $result->result('array');

		if ( !is_array($res) || count($res) == 0 ) {
			$this->db->trans_rollback();
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение добровольного информированного согласия)'));
		}
		else if ( !empty($res[0]['Error_Msg']) ) {
			$this->db->trans_rollback();
			return $res;
		}

		$data['DopDispInfoConsent_id'] = $res[0]['DopDispInfoConsent_id'];

		// 5. ищем соответсвующее EvnPLDisp_id согласие для всей карты вцелом
		$data['SurveyTypeLinkForCard_id'] = NULL;

		$query = "
			select
				stl.SurveyTypeLink_id as \"SurveyTypeLink_id\",
				ddic.DopDispInfoConsent_id as \"DopDispInfoConsent_id\"
			from
				v_SurveyTypeLink stl
				inner join v_SurveyType st on st.SurveyType_id = stl.SurveyType_id
				left join v_DopDispInfoConsent ddic on ddic.SurveyTypeLink_id = stl.SurveyTypeLink_id
					and ddic.EvnPLDIsp_id = :EvnPLDispTeenInspection_id
			where
				ST.SurveyType_Code IN (50,67,68)
				and DispClass_id = :DispClass_id
			limit 1
		";
		$result = $this->db->query($query, $data);

		if ( !is_object($result) ) {
			$this->db->trans_rollback();
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (получение идентификатора добровольного информированного согласия для всей карты)'));
		}

		$res = $result->result('array');

		if ( is_array($res) && count($res) == 1 ) {
			$data['DopDispInfoConsentForCard_id'] = $res[0]['DopDispInfoConsent_id'];
			$data['SurveyTypeLinkForCard_id'] = $res[0]['SurveyTypeLink_id'];
		}

		if ( empty($data['SurveyTypeLinkForCard_id']) ) {
			$this->db->trans_rollback();
			return array(array('Error_Msg' => 'Отсутствует SurveyTypeLink для осмотра вцелом'));
		}

		// 6. проставляем согласие по карте вцелом
		if ( empty($data['DopDispInfoConsentForCard_id']) ) {
			$proc = 'p_DopDispInfoConsent_ins';
		}
		else {
			$proc = 'p_DopDispInfoConsent_upd';
		}

		$query = "
			select
				DopDispInfoConsent_id as \"DopDispInfoConsent_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$proc}(
				DopDispInfoConsent_id := :DopDispInfoConsent_id, 
				EvnPLDisp_id := :EvnPLDispTeenInspection_id, 
				DopDispInfoConsent_IsAgree := :DopDispInfoConsent_IsAgree, 
				DopDispInfoConsent_IsEarlier := :DopDispInfoConsent_IsEarlier, 
				SurveyTypeLink_id := :SurveyTypeLink_id, 
				pmUser_id := :pmUser_id
			)
		";
		$result = $this->db->query($query, array(
			'EvnPLDispTeenInspection_id' => $data['EvnPLDispTeenInspection_id'],
			'DopDispInfoConsent_id' => $data['DopDispInfoConsentForCard_id'],
			'DopDispInfoConsent_IsAgree' => 2,
			'DopDispInfoConsent_IsEarlier' => 1,
			'SurveyTypeLink_id' => $data['SurveyTypeLinkForCard_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if ( !is_object($result) ) {
			$this->db->trans_rollback();
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение добровольного информированного согласия для карты)'));
		}

		$res = $result->result('array');

		if ( !is_array($res) || count($res) == 0 ) {
			$this->db->trans_rollback();
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение добровольного информированного согласия для карты)'));
		}
		else if ( !empty($res[0]['Error_Msg']) ) {
			$this->db->trans_rollback();
			return $res;
		}

		$data['DopDispInfoConsentForCard_id'] = $res[0]['DopDispInfoConsent_id'];

		if ( empty($data['DopDispInfoConsentForCard_id']) ) {
			// ошибка сохранения согласия
			$this->db->trans_rollback();
			return array(array('Error_Msg' => 'Ошибка сохранения согласия'));
		}

		// Получаем EvnVizitDispDop_id по DopDispInfoConsent_id
		$query = "
			select
				EvnVizitDispDop_id as \"EvnVizitDispDop_id\"
			from v_EvnVizitDispDop
			where DopDispInfoConsent_id = :DopDispInfoConsent_id
				and Lpu_id = :Lpu_id
			limit 1
		";
		$result = $this->db->query($query, $data);

		if ( !is_object($result) ) {
			$this->db->trans_rollback();
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (получение идентификатора посещения)'));
		}

		$resp = $result->result('array');

		if ( is_array($resp) && count($resp) > 0 ) {
			$data['EvnVizitDispDop_id'] = $resp[0]['EvnVizitDispDop_id'];
			$procvizit = "p_EvnVizitDispDop_upd";

			// Получаем EvnUslugaDispDop_id по EvnVizitDispDop_id
			$sql = "
				select
					EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\"
				from
					v_EvnUslugaDispDop
				where
					EvnUslugaDispDop_pid = :EvnUslugaDispDop_pid
					and coalesce(EvnUslugaDispDop_IsVizitCode, 1) = 1
				limit 1
			";
			$res = $this->db->query($sql, array('EvnUslugaDispDop_pid' => $data['EvnVizitDispDop_id']));

			if ( !is_object($res) ) {
				$this->db->trans_rollback();
				return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (получение идентификатора услуги)'));
			}

			$resp = $res->result('array');

			if ( !is_array($resp) || count($resp) == 0 ) {
				$this->db->trans_rollback();
				return array(array('Error_Msg' => 'Дождитесь выполнения предыдущего запроса на сохранение осмотра/исследования'));
			}

			$data['EvnUslugaDispDop_id'] = $resp[0]['EvnUslugaDispDop_id'];
			$proc = "p_EvnUslugaDispDop_upd";
		}
		else {
			$data['EvnUslugaDispDop_id'] = null;
			$data['EvnVizitDispDop_id'] = null;

			$proc = "p_EvnUslugaDispDop_ins";
			$procvizit = "p_EvnVizitDispDop_ins";
		}

		if ( !empty($data['EvnUslugaDispDop_setTime']) ) {
			$data['EvnUslugaDispDop_setDate'] .= ' ' . $data['EvnUslugaDispDop_setTime'] . ':00.000';
		}

		$data['Diag_id'] = $this->getFirstResultFromQuery("
				SELECT
					Diag_id as \"Diag_id\"
				FROM
					v_Diag
				WHERE
					Diag_Code = 
						(case when (
							select
								ST.SurveyType_Code
							from v_SurveyTypeLink STL
							inner join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
							WHERE STL.SurveyTypeLink_id = :SurveyTypeLink_id
							limit 1
						) = 27 then 'Z00.1' else 'Z01.7' end
					)
				limit 1
			",
			array(
				'SurveyTypeLink_id' => $data['SurveyTypeLink_id']
			)
		);

		$data['UslugaComplex_id'] = $this->getFirstResultFromQuery("
				SELECT
					UslugaComplex_id as \"UslugaComplex_id\"
				FROM
					v_SurveyTypeLink
				WHERE
					SurveyTypeLink_id = :SurveyTypeLink_id
				limit 1
			",
			array(
				'SurveyTypeLink_id' => $data['SurveyTypeLink_id']
			)
		);

		// сначала сохраняем посещение, затем в него услугу, затем к ней сохраняем её результаты %)
		$query = "
			select
				EvnVizitDispDop_id as \"EvnVizitDispDop_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from " . $procvizit . "(
				EvnVizitDispDop_id := :EvnVizitDispDop_id,
				EvnVizitDispDop_pid := :EvnVizitDispDop_pid,
				Lpu_id := :Lpu_id,
				Server_id := :Server_id,
				PersonEvn_id := :PersonEvn_id,
				EvnVizitDispDop_setDT := :EvnUslugaDispDop_setDate,
				EvnVizitDispDop_didDT := null,
				Diag_id := :Diag_id, 
				UslugaComplex_id := :UslugaComplex_id,
				LpuSection_id := null,
				MedPersonal_id := null,
				DopDispDiagType_id := null, 
				DopDispInfoConsent_id := :DopDispInfoConsent_id, 
				pmUser_id := :pmUser_id
			)
		";
		// echo getDebugSQL($query, $data);
		$res = $this->db->query($query, $data);

		if ( !is_object($res) ) {
			$this->db->trans_rollback();
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение посещения)'));
		}

		$resp = $res->result('array');

		if ( !is_array($resp) || count($resp) == 0 ) {
			$this->db->trans_rollback();
			return array(array('Error_Msg' => 'Ошибка при сохранении посещения'));
		}
		else if ( !empty($resp[0]['Error_Msg']) ) {
			$this->db->trans_rollback();
			return $resp;
		}

		$data['EvnVizitDispDop_id'] = $resp[0]['EvnVizitDispDop_id'];

		$query = "
			with mv as (
				select
					PayType_id as pt
				from v_PayType
				where PayType_SysNick = 'dopdisp'
				limit 1
			)
			select
				EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$proc}(
				EvnUslugaDispDop_id := :EvnUslugaDispDop_id,
				EvnUslugaDispDop_pid := :EvnVizitDispDop_id,
				Lpu_id := :Lpu_id,
				Server_id := :Server_id,
				EvnDirection_id := :EvnDirection_id,
				PersonEvn_id := :PersonEvn_id,
				PayType_id := (select pt from mv),
				EvnUslugaDispDop_setDT := :EvnUslugaDispDop_setDate,
				Diag_id := :Diag_id,
				UslugaComplex_id := :UslugaComplex_id,
				EvnUslugaDispDop_didDT := null,
				ExaminationPlace_id := null,
				LpuSection_uid := null,
				MedPersonal_id := null,
				EvnUslugaDispDop_ExamPlace := :EvnUslugaDispDop_ExamPlace,
				EvnPrescrTimetable_id := null,
				EvnPrescr_id := null,
				pmUser_id := :pmUser_id
			)
		";
		// echo getDebugSQL($query, $data);
		$res = $this->db->query($query, $data);

		if ( !is_object($res) ) {
			$this->db->trans_rollback();
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение услуги)'));
		}

		$resp = $res->result('array');

		if ( !is_array($resp) || count($resp) == 0 ) {
			$this->db->trans_rollback();
			return array(array('Error_Msg' => 'Ошибка при сохранении услуги'));
		}
		else if ( !empty($resp[0]['Error_Msg']) ) {
			$this->db->trans_rollback();
			return $resp;
		}

		$EvnUsluga_id = $resp[0]['EvnUslugaDispDop_id'];

		// https://redmine.swan.perm.ru/issues/33554
		// Добавляем повторную проверку на наличие дублей
		$sql = "
			select
				EvnVizitDispDop_id as \"EvnVizitDispDop_id\"
			from v_EvnVizitDispDop
			where DopDispInfoConsent_id = :DopDispInfoConsent_id
				and Lpu_id = :Lpu_id
				and EvnVizitDispDop_id != :EvnVizitDispDop_id
			limit 1
		";
		$res = $this->db->query($sql, $data);

		if ( !is_object($res) ) {
			$this->db->trans_rollback();
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (получение идентификаторов дублирующих посещений)'));
		}

		$resp = $res->result('array');

		if ( is_array($resp) && count($resp) > 0 && !empty($resp[0]['EvnVizitDispDop_id']) ) {
			$this->db->trans_rollback();
			return array(array('Error_Msg' => 'Обнаружены дубли посещений по выбранному исследованию/осмотру. Произведен откат транзакции. Пожалуйста, повторите сохранение.'));
		}

		$this->db->trans_commit();

		return array(array('EvnUslugaDispDop_id' => $EvnUsluga_id, 'EvnPLDispTeenInspection_id' => $data['EvnPLDispTeenInspection_id'], 'Error_Code' => '', 'Error_Msg' => ''));
	}

	/**
	 *	Получение идентификатора посещения по идентицикатору услуги
	 */
	function getEvnVizitDispDopForEvnUsluga($data) {
		$query = "
			select
				EVDD.EvnVizitDispDop_id as \"EvnVizitDispDop_id\"
			from
				v_EvnUslugaDispDop EUDD
				left join v_EvnVizitDispDop EVDD on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
			where
				EUDD.EvnUslugaDispDop_id = :EvnUslugaDispDop_id
					and coalesce(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
			limit 1
		";

		$res = $this->db->query($query, $data);

		if ( is_object($res) ) {
			$resp = $res->result('array');

			if ( is_array($resp) && count($resp) > 0 && !empty($resp[0]['EvnVizitDispDop_id']) ) {
				return $resp[0]['EvnVizitDispDop_id'];
			}
		}

		return true;
	}

	/**
	 *	Получение данных по направлению
	 */
	function loadEvnUslugaDispDopDirection($data) {
		$query = "
			select 
				EUDD.EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\",
				EUDD.EvnUslugaDispDop_pid as \"EvnUslugaDispDop_pid\",
				EUDD.PersonEvn_id as \"PersonEvn_id\",
				DDIC.SurveyTypeLink_id as \"SurveyTypeLink_id\",
				EUDD.Server_id as \"Server_id\",
				EUDD.EvnUslugaDispDop_ExamPlace as \"EvnUslugaDispDop_ExamPlace\",
				to_char(EUDD.EvnUslugaDispDop_setDT, 'dd.mm.yyyy') as \"EvnUslugaDispDop_setDate\",
				EUDD.EvnUslugaDispDop_setTime as \"EvnUslugaDispDop_setTime\",
				EVDD.EvnVizitDispDop_pid as \"EvnVizitDispDop_pid\",
				EVDD.DopDispInfoConsent_id as \"DopDispInfoConsent_id\",
				EVDD.EvnVizitDispDop_pid as \"EvnPLDispTeenInspection_id\"
			from
				v_EvnUslugaDispDop EUDD
				inner join v_EvnVizitDispDop EVDD on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
				inner join v_DopDispInfoConsent DDIC on DDIC.DopDispInfoConsent_id = EVDD.DopDispInfoConsent_id
			where
				EUDD.EvnUslugaDispDop_id = :EvnUslugaDispDop_id
				and coalesce(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
		";
		$result = $this->db->query($query, array(
			'EvnUslugaDispDop_id' => $data['EvnUslugaDispDop_id']
		));

		if ( is_object($result) ) {
			$resp = $result->result('array');
			return $resp;
		}
		else {
			return false;
		}
	}

	/**
	 * Возвращает список всех используемых ключей атрибутов объекта
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();
		$arr[self::ID_KEY]['alias'] = 'EvnPLDispTeenInspection_id';
		$arr[self::ID_KEY]['label'] = 'Идентификатор талона диспансеризации';
		$arr['edudt'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_DATE_TIME,
			),
			'alias' => 'EvnPLDispTeenInspection_eduDT',
			'label' => 'Дата поступления',
			'save' => '',
			'type' => 'date'
		);
		$arr['educationinstitutionclass_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EducationInstitutionClass_id',
			'label' => 'Образовательное учреждение',
			'save' => '',
			'type' => 'id'
		);
		$arr['institutionnaturetype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'InstitutionNatureType_id',
			'label' => 'Характер учреждения',
			'save' => '',
			'type' => 'id'
		);
		$arr['institutiontype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'InstitutionType_id',
			'label' => 'Вид учреждения',
			'save' => '',
			'type' => 'id'
		);
		$arr['istwostage'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispTeenInspection_IsTwoStage',
			'label' => 'Направлен на 2 этап',
			'save' => '',
			'type' => 'id'
		);
		$arr['agegroupdisp_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'AgeGroupDisp_id',
			'label' => 'Возрастная группа',
			'save' => '',
			'type' => 'id'
		);
		$arr['org_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Org_id',
			'label' => 'Организация',
			'save' => '',
			'type' => 'id'
		);
		$arr['persondisporp_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'PersonDispOrp_id',
			'label' => 'Идентификатор в реестре',
			'save' => '',
			'type' => 'id'
		);
		$arr['evndirection_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'evndirection_id',
			'label' => 'Идентификатор направления',
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
		return 104;
	}

	/**
	 * Определение кода класса события
	 * @return string
	 */
	static function evnClassSysNick()
	{
		return 'EvnPLDispTeenInspection';
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateAgeGroupDispId($id, $value = null)
	{
		return $this->_updateAttribute($id, 'agegroupdisp_id', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateEduDT($id, $value = null)
	{
		return $this->_updateAttribute($id, 'edudt', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateEducationInstitutionClassId($id, $value = null)
	{
		return $this->_updateAttribute($id, 'educationinstitutionclass_id', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateInstitutionNatureTypeId($id, $value = null)
	{
		return $this->_updateAttribute($id, 'institutionnaturetype_id', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateInstitutionTypeId($id, $value = null)
	{
		return $this->_updateAttribute($id, 'institutiontype_id', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateIsTwoStage($id, $value = null)
	{
		return $this->_updateAttribute($id, 'istwostage', $value);
	}

	/**
	 * Получение объекта AssessmentHealth и создание оного в случае необходимости
	 */
	function getAssessmentHealthOrCreate($EvnPLDisp_id) {
		// Ищем AssessmentHealth связанный с EvnPLDispTeenInspection_id, если нет его то добавляем новый, иначе обновляем
		$AssessmentHealth_id = NULL;
		$query = "
			select
				AssessmentHealth_id as \"AssessmentHealth_id\"
			from v_AssessmentHealth
			where EvnPLDisp_id = :EvnPLDispTeenInspection_id
			limit 1
		";
		$result = $this->db->query($query, array(
			'EvnPLDispTeenInspection_id' => $EvnPLDisp_id
		));
		if (is_object($result))
		{
			$resp = $result->result('array');
			if (count($resp) > 0) {
				$AssessmentHealth_id = $resp[0]['AssessmentHealth_id'];
			}
		}

		if (empty($AssessmentHealth_id)) {
			$data['scenario'] = swModel::SCENARIO_DO_SAVE;
			$result = $this->AssessmentHealth_model->doSave(array(
				'AssessmentHealth_id' => null,
				'EvnPLDisp_id' => $EvnPLDisp_id,
				'scenario' => swModel::SCENARIO_DO_SAVE,
				'session' => $this->sessionParams
			));

			if (!empty($result['AssessmentHealth_id'])) {
				// теперь есть к чему сохранять
				$AssessmentHealth_id = $result['AssessmentHealth_id'];
			}
		}

		return $AssessmentHealth_id;
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateHealthKindId($id, $value = null)
	{
		$this->load->model('AssessmentHealth_model');
		$AssessmentHealth_id = $this->getAssessmentHealthOrCreate($id);

		if (!empty($AssessmentHealth_id)) {
			$this->AssessmentHealth_model->setScenario(EvnAbstract_model::SCENARIO_SET_ATTRIBUTE);
			return $this->AssessmentHealth_model->_updateAttribute($AssessmentHealth_id, 'healthkind_id', $value);
		}
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateWeightAbnormTypeId($id, $value = null)
	{
		$this->load->model('AssessmentHealth_model');
		$AssessmentHealth_id = $this->getAssessmentHealthOrCreate($id);

		if (!empty($AssessmentHealth_id)) {
			$this->AssessmentHealth_model->setScenario(EvnAbstract_model::SCENARIO_SET_ATTRIBUTE);
			return $this->AssessmentHealth_model->_updateAttribute($AssessmentHealth_id, 'weightabnormtype_id', $value);
		}
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateHeightAbnormTypeId($id, $value = null)
	{
		$this->load->model('AssessmentHealth_model');
		$AssessmentHealth_id = $this->getAssessmentHealthOrCreate($id);

		if (!empty($AssessmentHealth_id)) {
			$this->AssessmentHealth_model->setScenario(EvnAbstract_model::SCENARIO_SET_ATTRIBUTE);
			return $this->AssessmentHealth_model->_updateAttribute($AssessmentHealth_id, 'heightabnormtype_id', $value);
		}
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateNormaDisturbanceTypeId($id, $value = null)
	{
		$this->load->model('AssessmentHealth_model');
		$AssessmentHealth_id = $this->getAssessmentHealthOrCreate($id);

		if (!empty($AssessmentHealth_id)) {
			$this->AssessmentHealth_model->setScenario(EvnAbstract_model::SCENARIO_SET_ATTRIBUTE);
			return $this->AssessmentHealth_model->_updateAttribute($AssessmentHealth_id, 'normadisturbancetype_id', $value);
		}
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateNormaDisturbanceTypeUid($id, $value = null)
	{
		$this->load->model('AssessmentHealth_model');
		$AssessmentHealth_id = $this->getAssessmentHealthOrCreate($id);

		if (!empty($AssessmentHealth_id)) {
			$this->AssessmentHealth_model->setScenario(EvnAbstract_model::SCENARIO_SET_ATTRIBUTE);
			return $this->AssessmentHealth_model->_updateAttribute($AssessmentHealth_id, 'normadisturbancetype_uid', $value);
		}
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateNormaDisturbanceTypeEid($id, $value = null)
	{
		$this->load->model('AssessmentHealth_model');
		$AssessmentHealth_id = $this->getAssessmentHealthOrCreate($id);

		if (!empty($AssessmentHealth_id)) {
			$this->AssessmentHealth_model->setScenario(EvnAbstract_model::SCENARIO_SET_ATTRIBUTE);
			return $this->AssessmentHealth_model->_updateAttribute($AssessmentHealth_id, 'normadisturbancetype_eid', $value);
		}
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateAssessmentHealthParam($id, $value = null, $param_name = null)
	{
		$this->load->model('AssessmentHealth_model');
		$AssessmentHealth_id = $this->getAssessmentHealthOrCreate($id);

		if (!empty($AssessmentHealth_id)) {

			$fieldName = $param_name;

			if (!empty($param_name)) {
				$exploded_params = explode('_', $param_name);
				if (!empty($exploded_params[1])) {
					$fieldName = strtolower($exploded_params[1]);
				}
			}

			$this->AssessmentHealth_model->setScenario(EvnAbstract_model::SCENARIO_SET_ATTRIBUTE);
			return $this->AssessmentHealth_model->_updateAttribute($AssessmentHealth_id, $fieldName, $value);
		}
	}

	/**
	 * Сохранение осмотра
	 */
	function saveEvnVizitDispDop($data)
	{
		if (empty($data['EvnVizitDispDop_id'])) {
			$procedure = 'p_EvnVizitDispDop_ins';
		}
		else
		{
			$procedure = 'p_EvnVizitDispDop_upd';
		}

		// 1. ищем DopDispInfoConsent_id
		$data['DopDispInfoConsent_id'] = null;
		if (!empty($data['EvnVizitDispDop_id'])) {
			$query = "
				select
					EVDD.DopDispInfoConsent_id as \"DopDispInfoConsent_id\",
					EUDD.EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\"
				from
					v_EvnVizitDispDop EVDD
					left join v_EvnUslugaDispDop EUDD on EUDD.EvnUslugaDispDop_pid = EVDD.EvnVizitDispDop_id
				where
					EVDD.EvnVizitDispDop_id = :EvnVizitDispDop_id
				limit 1
			";

			$result = $this->db->query($query, $data);
			if (is_object($result)) {
				$resp = $result->result('array');
				if (!empty($resp[0]['DopDispInfoConsent_id'])) {
					$data['DopDispInfoConsent_id'] = $resp[0]['DopDispInfoConsent_id'];
					$data['EvnUslugaDispDop_id'] = $resp[0]['EvnUslugaDispDop_id'];
				}
			}
		}

		// 2. обновляем/добавляем согласие
		$ddicproc = "p_DopDispInfoConsent_ins";
		if (!empty($data['DopDispInfoConsent_id'])) {
			$ddicproc = "p_DopDispInfoConsent_upd";
		}

		$query = "
			select
				DopDispInfoConsent_id as \"DopDispInfoConsent_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$ddicproc}(
				DopDispInfoConsent_id := :DopDispInfoConsent_id,
				EvnPLDisp_id := :EvnPLDisp_id, 
				SurveyTypeLink_id := NULL,
				DopDispInfoConsent_IsAgree := 2, 
				DopDispInfoConsent_IsEarlier := 1, 
				pmUser_id := :pmUser_id
			)
		";

		$result = $this->db->query($query, array(
			'EvnPLDisp_id' => $data['EvnPLDispTeenInspection_id'],
			'pmUser_id' => $data['pmUser_id'],
			'DopDispInfoConsent_id' => $data['DopDispInfoConsent_id']
		));

		if (is_object($result)) {
			$resp = $result->result('array');
			if (!empty($resp[0]['DopDispInfoConsent_id'])) {
				$data['DopDispInfoConsent_id'] = $resp[0]['DopDispInfoConsent_id'];
			}
		}

		if (empty($data['DopDispInfoConsent_id'])) {
			return array('Error_Msg' => 'Ошибка сохранения согласия');
		}

		$setDT = $data['EvnVizitDispDop_setDate'];
		if (!empty($data['EvnVizitDispDop_setTime'])) {
			$setDT .= ' '.$data['EvnVizitDispDop_setTime'];
		}
		$disDT = null;
		if (!empty($data['EvnVizitDispDop_disDate'])) {
			$disDT = $data['EvnVizitDispDop_disDate'];

			if (!empty($data['EvnVizitDispDop_disTime'])) {
				$disDT .= ' '.$data['EvnVizitDispDop_disTime'];
			}
		}

		$query = "
			select
				EvnVizitDispDop_id as \"EvnVizitDispDop_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				EvnVizitDispDop_id := :EvnVizitDispDop_id,
				EvnVizitDispDop_pid := :EvnVizitDispDop_pid,
				Lpu_id := :Lpu_id,
				Server_id := :Server_id,
				PersonEvn_id := :PersonEvn_id,
				EvnVizitDispDop_setDT := :EvnVizitDispDop_setDT,
				EvnVizitDispDop_disDT := :EvnVizitDispDop_disDT,
				EvnVizitDispDop_didDT := null,
				LpuSection_id := :LpuSection_id,
				MedSpecOms_id := :MedSpecOms_id,
				LpuSectionProfile_id := :LpuSectionProfile_id,
				MedPersonal_id := :MedPersonal_id,
				MedStaffFact_id := :MedStaffFact_id,
				MedPersonal_sid := null,
				PayType_id := null,
				UslugaComplex_id := :UslugaComplex_id,
				DopDispSpec_id := null,
				DopDispInfoConsent_id := :DopDispInfoConsent_id,
				Diag_id := :Diag_id,
				DopDispDiagType_id := :DopDispDiagType_id,
				DopDispAlien_id := :DopDispAlien_id,
				pmUser_id := :pmUser_id
			)
		";
		$result = $this->db->query($query, array(
			'EvnVizitDispDop_id' => $data['EvnVizitDispDop_id'],
			'EvnVizitDispDop_pid' => $data['EvnPLDispTeenInspection_id'],
			'Lpu_id' => (!empty($data['Lpu_uid']) ? $data['Lpu_uid'] : $data['Lpu_id']),
			'MedSpecOms_id' => (!empty($data['MedSpecOms_id']) ? $data['MedSpecOms_id'] : NULL),
			'LpuSectionProfile_id' => (!empty($data['LpuSectionProfile_id']) ? $data['LpuSectionProfile_id'] : NULL),
			'Server_id' => $data['Server_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'EvnVizitDispDop_setDT' => $setDT,
			'EvnVizitDispDop_disDT' => $disDT,
			'LpuSection_id' => (!empty($data['LpuSection_id']) ? $data['LpuSection_id'] : NULL),
			'MedPersonal_id' => (!empty($data['MedPersonal_id']) ? $data['MedPersonal_id'] : NULL),
			'MedStaffFact_id' => (!empty($data['MedStaffFact_id']) ? $data['MedStaffFact_id'] : NULL),
			'UslugaComplex_id' => (!empty($data['UslugaComplex_id']) ? $data['UslugaComplex_id'] : NULL),
			'DopDispInfoConsent_id' => $data['DopDispInfoConsent_id'],
			'Diag_id' => $data['Diag_id'],
			'DopDispDiagType_id' => (isset($data['DopDispDiagType_id']) && $data['DopDispDiagType_id'] > 0) ? $data['DopDispDiagType_id'] : null,
			'DopDispAlien_id' => $data['DopDispAlien_id'],
			'pmUser_id' => $data['pmUser_id']
		));
		if (!is_object($result))
		{
			return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение посещения)');
		}
		$response = $result->result('array');
		if (!is_array($response) || count($response) == 0)
		{
			return false;
		}
		else if ($response[0]['Error_Msg'])
		{
			return $response;
		}

		if (!empty($response[0]['EvnVizitDispDop_id'])) {
			$procedure_usl = 'p_EvnUslugaDispDop_ins';
			if (!empty($data['EvnUslugaDispDop_id'])) {
				$procedure_usl = 'p_EvnUslugaDispDop_upd';
			}
			// сохраняем услугу
			$query = "
				with mv as (
					select
						PayType_id as pt
					from v_PayType
					where PayType_SysNick = 'dopdisp'
					limit 1
				)
				
				select
					EvnUslugaDispDop_id as \"EvnUslugaDispDop_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from " . $procedure_usl . "(
					EvnUslugaDispDop_id := :EvnUslugaDispDop_id,
					EvnUslugaDispDop_pid := :EvnUslugaDispDop_pid,
					Lpu_id := :Lpu_id,
					Server_id := :Server_id,
					PersonEvn_id := :PersonEvn_id,
					EvnUslugaDispDop_setDT := :EvnUslugaDispDop_setDT,
					EvnUslugaDispDop_disDT := :EvnUslugaDispDop_disDT,
					EvnUslugaDispDop_didDT := :EvnUslugaDispDop_didDT,
					LpuSection_uid := :LpuSection_uid,
					MedSpecOms_id := :MedSpecOms_id,
					LpuSectionProfile_id := :LpuSectionProfile_id,
					MedPersonal_id := :MedPersonal_id,
					MedStaffFact_id := :MedStaffFact_id,
					UslugaComplex_id := :UslugaComplex_id,
					DopDispInfoConsent_id := :DopDispInfoConsent_id,
					PayType_id := (select pt from mv),
					UslugaPlace_id := 1,
					Lpu_uid := :Lpu_uid,
					EvnUslugaDispDop_Kolvo := 1,
					ExaminationPlace_id := :ExaminationPlace_id,
					Diag_id := :Diag_id,
					EvnPrescrTimetable_id := null,
					EvnPrescr_id := null,
					pmUser_id := :pmUser_id
				)
			";

			$result = $this->db->query($query, array(
				'EvnUslugaDispDop_id' => (!empty($data['EvnUslugaDispDop_id']) ? $data['EvnUslugaDispDop_id'] : NULL),
				'EvnUslugaDispDop_pid' => $response[0]['EvnVizitDispDop_id'],
				'Lpu_uid' => (!empty($data['Lpu_uid']) ? $data['Lpu_uid'] : $data['Lpu_id']),
				'MedSpecOms_id' => (!empty($data['MedSpecOms_id']) ? $data['MedSpecOms_id'] : NULL),
				'LpuSectionProfile_id' => (!empty($data['LpuSectionProfile_id']) ? $data['LpuSectionProfile_id'] : NULL),
				'Server_id' => $data['Server_id'],
				'PersonEvn_id' => $data['PersonEvn_id'],
				'EvnUslugaDispDop_setDT' => $setDT,
				'EvnUslugaDispDop_disDT' => $disDT,
				'EvnUslugaDispDop_didDT' => (!empty($data['EvnUslugaDispDop_didDate']) ? $data['EvnUslugaDispDop_didDate'] : NULL),
				'LpuSection_uid' => (!empty($data['LpuSection_id']) ? $data['LpuSection_id'] : NULL),
				'MedPersonal_id' => (!empty($data['MedPersonal_id']) ? $data['MedPersonal_id'] : NULL),
				'MedStaffFact_id' => (!empty($data['MedStaffFact_id']) ? $data['MedStaffFact_id'] : NULL),
				'UslugaComplex_id' => $data['UslugaComplex_id'],
				'DopDispInfoConsent_id' => $data['DopDispInfoConsent_id'],
				'Lpu_id' => $data['Lpu_id'],
				'ExaminationPlace_id' => (!empty($data['ExaminationPlace_id']) ? $data['ExaminationPlace_id'] : NULL),
				'Diag_id' => $data['Diag_id'],
				'pmUser_id' => $data['pmUser_id']
			));

			if (!is_object($result))
			{
				return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение посещения)');
			}
			$response_usl = $result->result('array');

			if (!is_array($response_usl) || count($response_usl) == 0)
			{
				return false;
			}
			else if ($response_usl[0]['Error_Msg'])
			{
				return $response_usl;
			}

			// сохраняем сопутствующие диагнозы
			if (!empty($data['EvnDiagDopDispGridData'])) {
				$data['EvnDiagDopDispGridData'] = json_decode($data['EvnDiagDopDispGridData'], true);
			} else {
				$data['EvnDiagDopDispGridData'] = array();
			}
			foreach($data['EvnDiagDopDispGridData'] as $EvnDiagDopDisp) {
				if ($EvnDiagDopDisp['Record_Status'] == 3) {// удаление
					$query = "
						select
							Error_Code as \"Error_Code\",
							Error_Message as \"Error_Msg\"
						from p_EvnDiagDopDisp_del(
							EvnDiagDopDisp_id := :EvnDiagDopDisp_id,
							pmUser_id := :pmUser_id
						)
					";
					$result_eddd = $this->db->query($query, array(
							'EvnDiagDopDisp_id' => $EvnDiagDopDisp['EvnDiagDopDisp_id'],
							'pmUser_id' => $data['pmUser_id'])
					);
					if (!is_object($result_eddd))
					{
						return array(0 => array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление сопутствующего диагноза)'));
					}
					$resp_eddd = $result_eddd->result('array');
					if (!is_array($resp_eddd) || count($resp_eddd) == 0)
					{
						return array(0 => array('Error_Msg' => 'Ошибка при удалении сопутствующего диагноза'));
					}
					else if (strlen($resp_eddd[0]['Error_Msg']) > 0)
					{
						return $resp_eddd;
					}
				} else {
					if ($EvnDiagDopDisp['Record_Status'] == 0)
					{
						$proc_evdd = 'p_EvnDiagDopDisp_ins';
					}
					else
					{
						$proc_evdd = 'p_EvnDiagDopDisp_upd';
					}

					// проверяем, есть ли уже такой диагноз
					$query = "
						select
							count(*) as \"cnt\"
						from
							v_EvnDiagDopDisp
						where
							EvnDiagDopDisp_pid = :EvnDiagDopDisp_pid
							and Diag_id = :Diag_id
							and DiagSetClass_id = 3
							and ( EvnDiagDopDisp_id <> coalesce(:EvnDiagDopDisp_id, 0) )
					";
					$result_eddd = $this->db->query(
						$query, [
							'EvnDiagDopDisp_pid' => $response[0]['EvnVizitDispDop_id'],
							'Diag_id' => $EvnDiagDopDisp['Diag_id'],
							'EvnDiagDopDisp_id' => $EvnDiagDopDisp['Record_Status'] == 0 ? null : $EvnDiagDopDisp['EvnDiagDopDisp_id']
						]
					);
					if (!is_object($result_eddd))
					{
						return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение сопутствующего диагноза)'));
					}
					$resp_eddd = $result_eddd->result('array');
					if (!is_array($resp_eddd) || count($resp_eddd) == 0)
					{
						return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение сопутствующего диагноза)'));
					}
					else if ($resp_eddd[0]['cnt'] >= 1)
					{
						return array(array('Error_Msg' => 'Обнаружено дублирование сопутствующих диагнозов, это недопустимо.'));
					}

					$query = "
						select
							EvnDiagDopDisp_id as \"EvnDiagDopDisp_id\",
							Error_Code as \"Error_Code\",
							Error_Message as \"Error_Msg\"
						from {$proc_evdd}(
							EvnDiagDopDisp_id := :EvnDiagDopDisp_id,
							EvnDiagDopDisp_setDT := dbo.tzGetDate(),
							EvnDiagDopDisp_pid := :EvnDiagDopDisp_pid,
							Diag_id := :Diag_id,
							DiagSetClass_id := :DiagSetClass_id,
							DeseaseDispType_id := :DeseaseDispType_id,
							Lpu_id := :Lpu_id,
							Server_id := :Server_id,
							PersonEvn_id := :PersonEvn_id,
							pmUser_id := :pmUser_id
						)
					";
					$result_eddd = $this->db->query($query, array(
						'EvnDiagDopDisp_id' => $EvnDiagDopDisp['Record_Status'] == 0 ? null : $EvnDiagDopDisp['EvnDiagDopDisp_id'],
						'EvnDiagDopDisp_pid' => $response[0]['EvnVizitDispDop_id'],
						'Diag_id' => $EvnDiagDopDisp['Diag_id'],
						'DiagSetClass_id' => 3,
						'DeseaseDispType_id' => !empty($EvnDiagDopDisp['DeseaseDispType_id'])?$EvnDiagDopDisp['DeseaseDispType_id']:null,
						'Lpu_id' => $data['Lpu_id'],
						'Server_id' => $data['Server_id'],
						'PersonEvn_id' => $data['PersonEvn_id'],
						'pmUser_id' => $data['pmUser_id']
					));
					if (!is_object($result_eddd))
					{
						return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение посещения)');
					}
					$resp_eddd = $result_eddd->result('array');
					if (!is_array($resp_eddd) || count($resp_eddd) == 0)
					{
						return false;
					}
					else if ($resp_eddd[0]['Error_Msg'])
					{
						return $resp_eddd;
					}
				}
			}
		}

		return $response;
	}

	/**
	 * Удаление осмотра
	 */
	function deleteEvnVizitDispDop($data)
	{
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_EvnVizitDispDop_del(
				EvnVizitDispDop_id := :EvnVizitDispDop_id,
				pmUser_id := :pmUser_id
			)
		";
		return $this->queryResult($query, array($data['EvnVizitDispDop_id'], $data['pmUser_id']));
	}
}

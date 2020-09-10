<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* EvnPLDispProf - контроллер для управления профосмотрами
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
* @property EvnPLDispProf_model $dbmodel
*/

class EvnPLDispProf extends swController
{
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->load->database();
		$this->load->model('EvnPLDispProf_model', 'dbmodel');
		
		$this->inputRules = array(
			'loadDopDispInfoConsent' => array(
				array(
					'field' => 'EvnPLDispProf_id',
					'label' => 'Идентификатор талона по доп. диспансеризации',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispProf_IsNewOrder',
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
					'field' => 'EvnPLDispProf_consDate',
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
					'field' => 'PayType_id',
					'label' => 'Вид оплаты',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Идентификатор услуги',
					'rules' => '',
					'type' => 'id'
				)
			),
			'loadEvnPLDispProfList' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'checkBeforeAddEvnPLDisp' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getDopDispInfoConsentFormDate' => array(
				array(
					'field' => 'EvnPLDispProf_id',
					'label' => 'Идентификатор талона по доп. диспансеризации',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'saveDopDispInfoConsent' => array(
				array(
					'field' => 'EvnPLDispProf_id',
					'label' => 'Идентификатор талона по доп. диспансеризации',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispProf_IsNewOrder',
					'label' => 'Признак переопределения услуг по новому приказу',
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
					'field' => 'EvnPLDispProf_fid',
					'label' => 'Идентификатор карты предыдущего этапа',
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
					'field' => 'EvnPLDispProf_IsMobile',
					'label' => 'Обслужен мобильной бригадой',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'EvnPLDispProf_IsOutLpu',
					'label' => 'Проведён вне МО',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'EvnPLDispProf_consDate',
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
					'field' => 'ignoreDVN',
					'label' => 'Признак игнорирования проверки проведения ДВН в указанном году',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'AttachmentAnswer',
					'label' => 'Прикреплению пациента к МО',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadEvnPLDispProfEditForm' => array(
				array(
					'field' => 'EvnPLDispProf_id',
					'label' => 'Идентификатор талона по доп. диспансеризации',
					'rules' => 'trim|required',
					'type' => 'id'
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
			'deleteEvnPLDispProf' => array(
				array(
					'field' => 'EvnPLDispProf_id',
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
			'checkIfEvnPLDispProfExists' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => 'trim|required',
					'type' => 'id'
				)
			),
			'transferEvnPLDispDopToEvnPLDispProf' => array(
				array(
					'field' => 'EvnPLDispDop13_id',
					'label' => 'Идентификатор талона по доп. диспансеризации',
					'rules' => 'trim|required',
					'type' => 'id'
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
				)
			),
			'checkIfEvnPLDispProfExistsInTwoYear' => array(
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
			'loadEvnVizitDispDopGrid' => array(
				array(
					'field' => 'EvnPLDispProf_id',
					'label' => 'Идентификатор талона по доп. диспансеризации',
					'rules' => 'trim|required',
					'type' => 'id'
				)
			),
			'loadEvnUslugaDispDopGrid' => array(
				array(
					'field' => 'EvnPLDispProf_id',
					'label' => 'Идентификатор талона по доп. диспансеризации',
					'rules' => 'trim|required',
					'type' => 'id'
				)
			),
			'searchEvnPLDispProf' => array(
				array(
						'field' => 'DocumentType_id',
						'label' => 'Тип документа удостовряющего личность',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'EvnPLDispProf_disDate',
						'label' => 'Дата завершения случая',
						'rules' => 'trim',
						'type' => 'daterange'
					),
				array(
						'field' => 'EvnPLDispProf_IsFinish',
						'label' => 'Случай завершен',
						'rules' => 'trim',
						'type' => 'id'
					),
				array(
						'field' => 'EvnPLDispProf_setDate',
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
			'saveEvnPLDispProf' => array(
                array(
                    'field' => 'AttachmentAnswer',
                    'label' => 'Прикреплению пациента к МО',
                    'rules' => '',
                    'type' => 'int'
                ),
				array(
					'field' => 'EvnPLDispProf_id',
					'label' => 'Идентификатор карты',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispProf_IsNewOrder',
					'label' => 'Признак переопределения услуг по новому приказу',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispProf_IndexRep',
					'label' => 'Признак повторной подачи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnPLDispProf_IndexRepInReg',
					'label' => 'Признак повторной подачи в реестре',
					'rules' => '',
					'type' => 'int'
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
					'field' => 'EvnPLDispProf_fid',
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
					'field' => 'EvnPLDispProf_IsMobile',
					'label' => 'Обслужен мобильной бригадой',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'EvnPLDispProf_IsOutLpu',
					'label' => 'Проведён вне МО',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'EvnPLDispProf_consDate',
					'label' => 'Дата начала случая',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'EvnPLDispProf_setDate',
					'label' => 'Дата начала случая',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'EvnPLDispProf_disDate',
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
					'field' => 'EvnPLDispProf_IsStenocard',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispProf_IsStenocard',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispProf_IsDoubleScan',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispProf_IsTub',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispProf_IsEsophag',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispProf_IsSmoking',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispProf_IsRiskAlco',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispProf_IsAlcoDepend',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispProf_IsLowActiv',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispProf_IsIrrational',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispProf_IsDisp',
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
					'field' => 'EvnPLDispProf_IsStac',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispProf_IsSanator',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispProf_SumRick',
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
					'field' => 'EvnPLDispProf_IsSchool',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispProf_IsProphCons',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispProf_IsHypoten',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispProf_IsLipid',
					'label' => 'Признак',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispProf_IsHypoglyc',
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
					'field' => 'EvnPLDispProf_IsEndStage',
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
					'field' => 'EvnPLDispProf_IsSuspectZNO',
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
				array(
					'field' => 'EvnPLDispProf_IsKKND',
					'label' => 'Создан из ККДН',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'checkAttributeforLpuSection',
					'label' => 'Признак атрибута у отделения',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadEvnPLDispProfStreamList' => array(
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
			'loadEvnPLDispProfForPerson' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'printEvnPLDispProf' => array(
				array(
					'field' => 'EvnPLDispProf_id',
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
			'printEvnPLDispProfPassport' => array(
				array(
					'field' => 'EvnPLDispProf_id',
					'label' => 'Идентификатор талона по ДД',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getEvnPLDispProfYears' => array(
			)
		);
		$this->inputRules['searchEvnPLDispProf'] = array_merge($this->inputRules['searchEvnPLDispProf'],getAddressSearchFilter());
	}
	
	/**
	*  Получение грида "информированное добровольное согласие по ДД 2013"
	*  Входящие данные: EvnPLDispProf_id
	*/	
	function loadDopDispInfoConsent() {
		$data = $this->ProcessInputData('loadDopDispInfoConsent', true);
		if ($data === false) { return false; }
	
		$response = $this->dbmodel->loadDopDispInfoConsent($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	*  Печать талона ДД
	*  Входящие данные: $_GET['EvnPLDispProf_id']
	*  На выходе: форма для печати талона ДД
	*  Используется: форма редактирования талона ДД
	*/
	function printEvnPLDispProf() {
		$this->load->helper('Options');
		$this->load->library('parser');

		$data = $this->ProcessInputData('printEvnPLDispProf', true);
		if ($data === false) { return false; }

		if ( isset($data['blank_only']) && $data['blank_only'] == 2 ) {
			$data['blank_only'] = true;
		} else {
			$data['blank_only'] = false;
		}

		//// Получаем настройки
		//$options = getOptions();

		// Получаем данные по талону ДД
		$response = $this->dbmodel->getEvnPLDispProfFields($data);

		if ( !is_array($response) || count($response) == 0 ) {
			echo 'Ошибка при получении данных по талону ДД';
			return true;
		}
		
		$evn_vizit_pl_dd_data = array();
		$evn_usluga_pl_dd_data = array();

		$evn_vizit_pl_dd_data = array('1' => array(), '2' => array(), '3' => array(), '4' => array(), '5' => array(), '6' => array());
		foreach ( $evn_vizit_pl_dd_data as $key => $val)
		{
			$evn_vizit_pl_dd_data[$key] = array('', '', '', '', '', '', '', '', '', '', '', '');
		}
		$response_temp = $this->dbmodel->loadEvnVizitDispDopData($data);
		if ( is_array($response_temp) ) {
			foreach ($response_temp as $row)
			{
				switch ($row['DopDispSpec_id'])
				{
					case 1: 
						$key = '1';
					break;
					case 2: 
						$key = '2';
					break;
					case 3: 
						$key = '3';
					break;
					case 5: 
						$key = '4';
					break;
					case 6: 
						$key = '5';
					break;
					default: 
						$key = '6';					
				}
				
				$evn_vizit_pl_dd_data[$key][0] = $row['MedPersonal_TabCode'];
				$evn_vizit_pl_dd_data[$key][1] = $row['EvnVizitDispDop_setDate'];
				if ( $row['DopDispDiagType_id'] == 1 )
				{
					$evn_vizit_pl_dd_data[$key][2]	= $row['Diag_Code'];
				}
				else
				{
					$evn_vizit_pl_dd_data[$key][3]	= $row['Diag_Code'];
				}
				if ( $row['DeseaseStage_id'] == 2 )
					$evn_vizit_pl_dd_data[$key][4]	= $row['Diag_Code'];
				switch ( $row['HealthKind_id'] )
				{
					case 1: 
						$evn_vizit_pl_dd_data[$key][5] = '+';
					break;
					case 2: 
						$evn_vizit_pl_dd_data[$key][6] = '+';
					break;
					case 3: 
						$evn_vizit_pl_dd_data[$key][7] = '+';
						if ( $row['DopDispDiagType_id'] == 2 )
							$evn_vizit_pl_dd_data[$key][8] = '+';
					break;
					case 4: 
						$evn_vizit_pl_dd_data[$key][9] = '+';
					break;
					case 5: 
						$evn_vizit_pl_dd_data[$key][10] = '+';
					break;						
				}
				if ( $row['EvnVizitDispDop_IsSanKur'] == 2 )
					$evn_vizit_pl_dd_data[$key][11] = '+';
			}
		}
		
		$evn_usluga_pl_dd_data = array('1' => array(), '2' => array(), '3' => array(), '4' => array(), '5' => array(), '6' => array(), '7' => array(), '8' => array(), '9' => array(), '10' => array(), '11' => array(), '12' => array(), '13' => array(), '14' => array(), '15' => array(), '16' => array(), '17' => array(), '18' => array(), '19' => array());
		foreach ( $evn_usluga_pl_dd_data as $key => $val)
		{
			$evn_usluga_pl_dd_data[$key] = array('', '');
		}
		
		$this->load->model('EvnUslugaDispDop_model', 'euddmodel');
		$data['EvnPLDisp_id'] = $data['EvnPLDispProf_id'];
		$response_temp = $this->euddmodel->loadEvnUslugaDispDopData($data);
		if ( is_array($response_temp) ) {
			foreach ($response_temp as $row)
			{
				switch ($row['DopDispUslugaType_id'])
				{
					case 1: 
						$key = '4';
					break;
					case 2: 
						$key = '11';
					break;
					case 3: 
						$key = '1';
					break;
					case 4: 
						$key = '12';
					break;
					case 5: 
						$key = '17';
					break;
					case 6: 
						$key = '16';
					break;
					case 7: 
						$key = '15';
					break;
					case 8: 
						$key = '19';
					break;
					case 9: 
						$key = '5';
					break;
					case 10: 
						$key = '6';
					break;
					case 11: 
						$key = '13';
					break;
					case 12: 
						$key = '14';
					break;
					case 13: 
						$key = '3';
					break;
					case 14: 
						$key = '7';
					break;
					case 15: 
						$key = '8';
					break;
					case 16: 
						$key = '9';
					break;
					case 17: 
						$key = '10';
					break;
					case 18: 
						$key = '18';
					break;
				}				
				$evn_usluga_pl_dd_data[$key][0] = !empty($row['EvnUslugaDispDop_setDate'])?$row['EvnUslugaDispDop_setDate']:'';
				$evn_usluga_pl_dd_data[$key][1] = !empty($row['EvnUslugaDispDop_didDate'])?$row['EvnUslugaDispDop_didDate']:'';
			}
		}

		$template = 'evn_pl_disp_dop_template_list_a4';
		if ( $data['blank_only'] === true )
			$template = 'evn_pl_disp_dop_template_list_a4_empty';

		$print_data = $response[0];
		$print_data['evn_vizit_pl_dd_data'] = $evn_vizit_pl_dd_data;
		$print_data['evn_usluga_pl_dd_data'] = $evn_usluga_pl_dd_data;

		return $this->parser->parse($template, $print_data);
	}
	
	
	/**
	*  Печать паспорта здоровья
	*  Входящие данные: $_GET['EvnPLDispProf_id']
	*  На выходе: форма для печати паспорта здоровья
	*  Используется: форма редактирования/просмотра/поиска/поточного ввода талонов ДД
	*/
	function printEvnPLDispProfPassport() {
		$this->load->helper('Options');
		$this->load->library('parser');

		$template = 'evn_pl_passport_template_2013';
		$default_val = '&nbsp;';
		$yrdt = array();
		$years = array();
		$print_data = array();
		$data = array();
		
		$start_year = 2013;
		$data['start_year'] = $start_year;
		
		$data = $this->ProcessInputData('printEvnPLDispProf', true);
		if ($data === false) { return false; }
		
		// Получаем данные		
		//года
		$now = getdate();
		for ($i = 1; $i <= 5; $i++) {
			$yr = $start_year+$i-1;
			$print_data["yr$i"] = $yr;
			$print_data["year$i"] = $yr <= $now['year'] ? $yr : '20_____г.';
			$yrdt[$yr] = ($yr <= $now['year']);
			$years[] = $yr;
		}
		
		//данные по талону
		$passport_data = $this->dbmodel->getEvnPLDispProfPassportFields($data);
		$print_data['IIIa'] = "
                                III группа состояния здоровья - граждане, не имеющие хронические неинфекционные заболевания, но требующие установления диспансерного наблюдения или
                                оказания специализированной, в том числе высокотехнологичной, медицинской помощи по поводу иных заболеваний, а также граждане с подозрением на наличие
                                этих заболеваний, нуждающиеся в дополнительном обследовании.
                                ";
		$print_data['IIIb'] = "&nbsp;";
		if($passport_data['is_new_event'] == 1) //Если дата случая - позднее 01 апреля 2015
		{
			$print_data['IIIa'] = "
                                    IIIа группа состояния здоровья - граждане, имеющие хронические неинфекционные заболевания, требующие установления диспансерного наблюдения или оказания
                                    специализированной, в том числе высокотехнологичной, медицинской помощи, а также граждане с подозрением на наличие этих заболеваний (состояний), нуждающиеся
                                    в дополнительном обследовании.
                                    ";
			$print_data['IIIb'] = "
                                    IIIб группа состояния здоровья - граждане, не имеющие хронические неинфекционные заболевания, но требующие установления диспансерного наблюдения или оказания
                                    специализированной, в том числе высокотехнологичной, медицинской помощи по поводу иных заболеваний, а также граждане с подозрением на наличие этих заболеваний,
                                    нуждающиеся в дополнительном обследовании.
                                    ";
		}

		$print_data['person_surname'] = $passport_data['Person_SurName'];
		$print_data['person_firname'] = $passport_data['Person_FirName'];
		$print_data['person_secname'] = $passport_data['Person_SecName'];
		$print_data['sex_id'] = $passport_data['Sex_id'];
		$print_data['polis_ser'] = $passport_data['Polis_Ser'];
		$print_data['polis_num'] = $passport_data['Polis_Num'];
		$print_data['person_phone'] = $passport_data['Person_Phone'];
		$print_data['p_bd_d'] = $passport_data['Person_BirthDay_Day'];
		$print_data['p_bd_m'] = $passport_data['Person_BirthDay_Month'];
		$print_data['p_bd_y'] = $passport_data['Person_BirthDay_Year'];
		$print_data['p_a'] = $passport_data['Address_Info'];
		$print_data['area_type'] = $passport_data['KLAreaType_id'];
		$print_data['p_a_st'] = $passport_data['KLStreet_Name'];
		$print_data['p_a_h'] = $passport_data['Address_House'];
		$print_data['p_a_c'] = $passport_data['Address_Corpus'];
		$print_data['p_a_fl'] = $passport_data['Address_Flat'];		
		$print_data['dd_lpu'] = $passport_data['Lpu_Name'];
		$print_data['l_address'] = $passport_data['l_address'];
		$print_data['personcard_code'] = $passport_data['PersonCard_Code'];
		$print_data['dd_lpu_phone'] = $passport_data['Org_Phone'];
		$print_data['IsSmoking'] = ($passport_data['IsSmoking']==2)?'Да':'Нет';
		$print_data['IsRiskAlco'] = ($passport_data['IsRiskAlco']==2)?'Да':'Нет';
		$print_data['IsLowActiv'] = ($passport_data['IsLowActiv']==2)?'Да':'Нет';
		$print_data['IsIrrational'] = ($passport_data['IsIrrational']==2)?'Да':'Нет';

		//Установленные заболевания
		for ($i=0;$i<10;$i++){
			$print_data['Diag_Name_'.$i] = '&nbsp;';
			$print_data['Diag_Code_'.$i] = '&nbsp;';
			$print_data['Diag_date_'.$i] = '&nbsp;';
		}
		$passport_data['diddate_19'] = isset($passport_data['diddate_19']) ? $passport_data['diddate_19'] : null;
		if(isset($passport_data['diags']) && isset($_GET['printDiag']) && $_GET['printDiag'] == '1') {
			for ($i=0;$i<10;$i++){
				if(isset($passport_data['diags'][$i])){
					$print_data['Diag_Name_'.$i] = $passport_data['diags'][$i]['Diag_Name'];
					$print_data['Diag_Code_'.$i] = $passport_data['diags'][$i]['Diag_Code'];
					$print_data['Diag_date_'.$i] = is_null($passport_data['diags'][$i]['Diag_date']) ? $passport_data['diddate_19'] : $passport_data['diags'][$i]['Diag_date'];
				}
			}
		}
		//Основные показатели
		/*$print_data['person_weight'] = '&nbsp;';
		$print_data['person_height'] = '&nbsp;';
		$print_data['glucose'] = '&nbsp;';
		$print_data['total_cholesterol'] = '&nbsp;';
		$print_data['person_pressure'] = '&nbsp;';
		$print_data['body_mass_index'] = '&nbsp';
		$print_data['diddate_19'] = '&nbsp;';
		$print_data['risk_gluk'] = '&nbsp;';
		$print_data['risk_high_pressure'] = 'Нет';
		$print_data['risk_dyslipidemia'] = 'Нет';
		$print_data['risk_overweight'] = 'Нет';
		$print_data['Med_Personal'] = '&nbsp';
		if(isset($passport_data['basic_indicators']['person_weight'][0]['Rate_ValueFloat'])){
			$print_data['person_weight'] = round($passport_data['basic_indicators']['person_weight'][0]['Rate_ValueFloat'],2);
		}
		if(isset($passport_data['basic_indicators']['person_height'][0]['Rate_ValueFloat'])){
			$print_data['person_height'] = round($passport_data['basic_indicators']['person_height'][0]['Rate_ValueFloat'],2);
		}
		if(isset($passport_data['basic_indicators']['body_mass_index'][0]['Rate_ValueFloat'])){
			$mass_index = round($passport_data['basic_indicators']['body_mass_index'][0]['Rate_ValueFloat'],2);
			$print_data['body_mass_index'] = round($passport_data['basic_indicators']['body_mass_index'][0]['Rate_ValueFloat'],2);
			$print_data['risk_overweight'] = ($mass_index>=25)?'Да':'Нет';
		}
		if(isset($passport_data['basic_indicators']['glucose'][0]['Rate_ValueFloat'])){
			$gluk = round($passport_data['basic_indicators']['glucose'][0]['Rate_ValueFloat'],2);
			$print_data['glucose'] = $gluk;
			$print_data['risk_gluk'] = ($gluk>6)?'Да':'Нет';
		}
		if(isset($passport_data['basic_indicators']['total_cholesterol'][0]['Rate_ValueFloat'])){
			$total_cholesterol = round($passport_data['basic_indicators']['total_cholesterol'][0]['Rate_ValueFloat'],2);
			$print_data['total_cholesterol'] = round($passport_data['basic_indicators']['total_cholesterol'][0]['Rate_ValueFloat'],2);
			$print_data['risk_dyslipidemia'] = ($total_cholesterol>5)?'Да':'Нет';
		}
		if(isset($passport_data['basic_indicators']['systolic_blood_pressure'][0]['Rate_ValueInt']) && (isset($passport_data['basic_indicators']['diastolic_blood_pressure'][0]['Rate_ValueInt']))){
			$print_data['person_pressure'] = $passport_data['basic_indicators']['systolic_blood_pressure'][0]['Rate_ValueInt'].'/'.$passport_data['basic_indicators']['diastolic_blood_pressure'][0]['Rate_ValueInt'];
			$systolic_blood_pressure = $passport_data['basic_indicators']['systolic_blood_pressure'][0]['Rate_ValueInt'];
			$diastolic_blood_pressure = $passport_data['basic_indicators']['diastolic_blood_pressure'][0]['Rate_ValueInt'];
			$print_data['person_pressure'] = $systolic_blood_pressure.'/'.$diastolic_blood_pressure;
			$print_data['risk_high_pressure'] = (($systolic_blood_pressure>140)||($diastolic_blood_pressure>90))?'Да':'Нет';
		}
		//Внезапно выяснилось, что данные по артериальному давлению стали попадать не в RateValue_Int, а в Rate_ValueFloat, поэтому перепишем определение еще и по этому полю
		if(isset($passport_data['basic_indicators']['systolic_blood_pressure'][0]['Rate_ValueFloat']) && (isset($passport_data['basic_indicators']['diastolic_blood_pressure'][0]['Rate_ValueFloat']))){
			$systolic_blood_pressure = (int)$passport_data['basic_indicators']['systolic_blood_pressure'][0]['Rate_ValueFloat'];
			$diastolic_blood_pressure = (int)$passport_data['basic_indicators']['diastolic_blood_pressure'][0]['Rate_ValueFloat'];
			$print_data['person_pressure'] = (int)$passport_data['basic_indicators']['systolic_blood_pressure'][0]['Rate_ValueFloat'].'/'.(int)$passport_data['basic_indicators']['diastolic_blood_pressure'][0]['Rate_ValueFloat'];
			$print_data['risk_high_pressure'] = (($systolic_blood_pressure>140)||($diastolic_blood_pressure>90))?'Да':'Нет';
		}*/
		//Факторы риска
		/*$print_data['risk_gippodinamy'] = 'Нет';
		$print_data['risk_alcohol'] = '&nbsp;';
		$print_data['summ_risk'] = 'Нет';
		$print_data['rick_narco'] = 'Нет';
		if(isset($passport_data['risk_factors']['6'][0]['value'])) {
			$print_data['risk_gippodinamy'] = $passport_data['risk_factors']['6'][0]['value']; //низкая физическая активность
		}
		if(isset($passport_data['summ_risk'][0]['EvnPLDispProf_SumRick'])){
			$print_data['summ_risk'] = $passport_data['summ_risk'][0]['EvnPLDispProf_SumRick'];
		}
		if(isset($passport_data['summ_risk'][0]['RiskType_Name'])){
			$print_data['summ_risk'] .= "; ".$passport_data['summ_risk'][0]['RiskType_Name'];
		}
		if(isset($passport_data['risk_narco'][0])){
			$print_data['risk_narco'] = ($passport_data['risk_narco'][0]['DDQ_Count']>1)?'Да':'Нет';
		}
		if(isset($passport_data['diddate_19'])){
			$print_data['diddate_19'] = $passport_data['diddate_19'];
		}
		if(isset($passport_data['Med_Personal'])){
			$print_data['Med_Personal'] = $passport_data['Med_Personal'];
		}
		if(isset($passport_data['her_diag'])) {
			$print_data['her_diag'] = $passport_data['her_diag'];
		}

		if(isset($passport_data['HealthKind_Name'])){
			$print_data['dd2v1'] = $passport_data['HealthKind_Name'];
		}*/
		//Факторы риска
		for ($i=1; $i <= 5; $i++)
		{
			$print_data['dd_date_'.$i] = '&nbsp;';
			$print_data['person_height_'.$i] = '&nbsp;';
			$print_data['person_weight_'.$i] = '&nbsp;';
			$print_data['body_mass_index_'.$i] = '&nbsp;';
			$print_data['risk_overweight_'.$i] = '&nbsp;';
			$print_data['total_cholesterol_'.$i] = '&nbsp;';
			$print_data['risk_dyslipidemia_'.$i] = '&nbsp;';
			$print_data['glucose_'.$i] = '&nbsp;';
			$print_data['risk_gluk_'.$i] = '&nbsp;';
			$print_data['person_pressure_'.$i] = '&nbsp;';
			$print_data['risk_high_pressure_'.$i] = '&nbsp;';
			$print_data['IsSmoking_'.$i] = '&nbsp;';
			$print_data['IsLowActiv_'.$i] = '&nbsp;';
			$print_data['IsIrrational_'.$i] = '&nbsp;';
			$print_data['IsRiskAlco_'.$i] = '&nbsp;';
			$print_data['risk_narco_'.$i] = '&nbsp;';
			$print_data['summ_risk_'.$i] = '&nbsp;';
			$print_data['her_diag_'.$i] = '&nbsp;';
			$print_data['dd_medpersonal_'.$i] = '&nbsp;';
			$print_data['hk_'.$i] = '&nbsp;';
		}
		//$risks = $this->dbmodel->getRiskFactorsForPassport($data);
		$risks = $this->dbmodel->getRiskFactorsForPassport(array('Person_id' => $passport_data['Person_id']));
		//var_dump($risks);die;
		for($i=4; $i>=0; $i--)
		{
			if(isset($risks[$i])){
				$print_data['dd_date_'.($i+1)] 				= $risks[$i]['dd_date'];
				$print_data['person_height_'.($i+1)] 		= ($risks[$i]['person_height']=='')?'':(float)$risks[$i]['person_height'];
				$print_data['person_weight_'.($i+1)] 		= ($risks[$i]['person_weight']=='')?'':(float)$risks[$i]['person_weight'];
				$print_data['body_mass_index_'.($i+1)] 		= ($risks[$i]['body_mass_index']=='')?'':(float)$risks[$i]['body_mass_index'];

				$risk_overweight 							= $risks[$i]['body_mass_index'] > 25 ? 'Да' : 'Нет';
				$print_data['risk_overweight_'.($i+1)] 		= $risk_overweight;// $risks[$i]['risk_overweight'];

				$print_data['total_cholesterol_'.($i+1)] 	= ($risks[$i]['total_cholesterol']=='')?'':(float)$risks[$i]['total_cholesterol'];
				$risk_dyslipidemia 							= $risks[$i]['total_cholesterol'] > 5 ? 'Да' : 'Нет';;
				$print_data['risk_dyslipidemia_'.($i+1)] 	= $risk_dyslipidemia;//isset($risks[$i]['total_cholesterol'])?$risks[$i]['risk_dyslipidemia']:'';

				$print_data['glucose_'.($i+1)] 				= ($risks[$i]['glucose']=='')?'':(float)$risks[$i]['glucose'];
				$risk_gluk 									= $risks[$i]['glucose'] > 6 ? 'Да' : 'Нет';
				$print_data['risk_gluk_'.($i+1)] 			= $risk_gluk;//isset($risks[$i]['glucose'])?$risks[$i]['risk_gluk']:'';

				$print_data['person_pressure_'.($i+1)] 		= (($risks[$i]['systolic_blood_pressure']!='')&&($risks[$i]['diastolic_blood_pressure']!=''))?$risks[$i]['person_pressure']:'';
				$risk_high_pressure 						= ($risks[$i]['systolic_blood_pressure'] > 140 || $risks[$i]['diastolic_blood_pressure'] > 90) ? 'Да' : 'Нет';
				$print_data['risk_high_pressure_'.($i+1)] 	= $risk_high_pressure;//(isset($risks[$i]['systolic_blood_pressure'])&&isset($risks[$i]['diastolic_blood_pressure']))?$risks[$i]['risk_high_pressure']:'';

				$print_data['IsSmoking_'.($i+1)] 			= $risks[$i]['IsSmoking'];
				$print_data['IsLowActiv_'.($i+1)] 			= $risks[$i]['IsLowActiv'];
				$print_data['IsIrrational_'.($i+1)] 		= $risks[$i]['IsIrrational'];
				$print_data['IsRiskAlco_'.($i+1)] 			= $risks[$i]['IsRiskAlco'];
				$print_data['risk_narco_'.($i+1)] 			= $risks[$i]['risk_narco'];
				$print_data['summ_risk_'.($i+1)] 			= ($risks[$i]['summ_risk']=='; ')?'':$risks[$i]['summ_risk'];
				$print_data['her_diag_'.($i+1)] 			= $risks[$i]['her_diag'];
				$print_data['dd_medpersonal_'.($i+1)] 		= $risks[$i]['dd_medpersonal'];
				$print_data['hk_'.($i+1)]					= $risks[$i]['HealthKind_Name'];

			}
		}
		return $this->parser->parse($template, $print_data);
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
	 * Сохранение данных по информир. добр. согласию
	 */
	function saveDopDispInfoConsent() {
		$data = $this->ProcessInputData('saveDopDispInfoConsent', true);
		if ($data === false) { return false; }

		$this->load->library('swFilterResponse'); 
		$response = $this->dbmodel->saveDopDispInfoConsent($data);
		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();

		return true;
	}
	
	/**
	 * Удаление посещения по дополнительной диспансеризации
	 */
	function deleteEvnPLDispProf() {
		$data = $this->ProcessInputData('deleteEvnPLDispProf', true);
		if ($data === false) { return false; }

		$this->load->model('Registry' . (getRegionNick() == 'ufa' ? 'Ufa' : '') . '_model', 'Reg_model');
        $registryData = $this->Reg_model->checkEvnAccessInRegistry($data);

        if ( is_array($registryData) ) {
            $response = $registryData;
        } else {
		    $response = $this->dbmodel->deleteEvnPLDispProf($data);
        }

		$this->ProcessModelSave($response, true, 'При удалении талона ДД возникли ошибки')->ReturnData();

		return true;
	}

	/**
	*  Проверка на наличие талона на этого человека в этом году
	*  Входящие данные: $_POST['Person_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования талона по ДД
	*/
	function checkIfEvnPLDispProfExists()
	{
		$data = $this->ProcessInputData('checkIfEvnPLDispProfExists', true);
		if ($data === false) { return false; }
	
		$response = $this->dbmodel->checkIfEvnPLDispProfExists($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	*  Проверка на наличие карты проф осмотра в этом или предыдущем году
	*  Входящие данные: $_POST['Person_id'], $_POST['EvnPLDisp_consDate']
	*  На выходе: JSON-строка
	*  Используется: swEvnPLDispDop13EditWindow
	*/
	function checkIfEvnPLDispProfExistsInTwoYear()
	{
		$data = $this->ProcessInputData('checkIfEvnPLDispProfExistsInTwoYear', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkIfEvnPLDispProfExistsInTwoYear($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	 *	Перенос карты ДВН в профосмотр
	 */
	function transferEvnPLDispDopToEvnPLDispProf()
	{
		$data = $this->ProcessInputData('transferEvnPLDispDopToEvnPLDispProf', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->transferEvnPLDispDopToEvnPLDispProf($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}

	
	/**
	 *  Получение талонов ДД для человека
	 *  Входящие данные: $_POST['Person_id'],
	 *  На выходе: JSON-строка
	 *  Используется: окно истории лечения
	 */
	 
	function loadEvnPLDispProfForPerson()
	{
		$data = $this->ProcessInputData('loadEvnPLDispProfForPerson', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadEvnPLDispProfForPerson($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	
	/**
	 * Получение данных для формы редактирования талона по ДД
	 * Входящие данные: $_POST['EvnPLDispProf_id']
	 * На выходе: JSON-строка
	 * Используется: форма редактирования талона по ДД
	 */
	function loadEvnPLDispProfEditForm()
	{
		$data = $this->ProcessInputData('loadEvnPLDispProfEditForm', true);
		if ($data === false) { return false; }
	
		$response = $this->dbmodel->loadEvnPLDispProfEditForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}


	/**
	 * Получение списка талонов по ДД для потокового ввода
	 * Входящие данные: $_POST['begDate'],
	 *                 $_POST['begTime']
	 * На выходе: JSON-строка
	 * Используется: форма потокового ввода талонов по ДД
	 */
	function loadEvnPLDispProfStreamList()
	{
		$data = $this->ProcessInputData('loadEvnPLDispProfStreamList', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadEvnPLDispProfStreamList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Получение данных формы редактирования посещений в талоне по ДД
	 * Входящие данные: $_POST['EvnVizitDispDop_id']
	 * На выходе: JSON-строка
	 * Используется: форма редактирования посещения по ДД
	 */
	function loadEvnVizitDispDopEditForm()
	{
		$data = $this->ProcessInputData('loadEvnVizitDispDopEditForm', true, true);
		if ($data === false) { return false; }

		if ($data['Lpu_id'] == 0)
		{
			$this->ReturnData(array('success' => false));
			return true;
		}
		$response = $this->dbmodel->loadEvnVizitDispDopEditForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
	
	/**
	 * Получение списка посещений в талоне по ДД
	 * Входящие данные: $_POST['EvnPLDispProf_id']
	 * На выходе: JSON-строка
	 * Используется: форма редактирования талона по ДД
	 */
	function loadEvnVizitDispDopGrid()
	{
		$data = $this->ProcessInputData('loadEvnVizitDispDopGrid', true, true);
		if ($data === false) { return false; }
		
		if ($data['Lpu_id'] == 0)
		{
			$this->ReturnData(array('success' => false));
			return true;
		}
		$response = $this->dbmodel->loadEvnVizitDispDopGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
	}
	
	/**
	 * Получение списка исследований в талоне по ДД
	 * Входящие данные: $_POST['EvnPLDispProf_id']
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
	 * Сохранение талона амбулаторного пациента
	 * Входящие данные: ...
	 * На выходе: JSON-строка
	 * Используется: форма редактирования талона амбулаторного пациента
	 */
	function saveEvnPLDispProf()
	{
		$data = $this->ProcessInputData('saveEvnPLDispProf', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->saveEvnPLDispProf($data);
		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();
	}


	/**
	 * Получение числа талонов с распределением по годам, для формирования списка на клиенте
	 * Входящие данные: ...
	 * На выходе: JSON-строка
	 * Используется: форма поиска/потокового ввода талонов по ДД
	 */
	function getEvnPLDispProfYears()
	{
		$data = getSessionParams();

		$year = date('Y');
		$info = $this->dbmodel->getEvnPLDispProfYears($data);
		$outdata = $this->ProcessModelList($info, true, true)->GetOutData();
		
		$flag = false;
		foreach ($outdata as $row) {
			if ( $row['EvnPLDispProf_Year'] == $year ) { $flag = true; }
		}
		if (!$flag) { $outdata[] = array('EvnPLDispProf_Year'=>$year, 'count'=>0); }
		
		$this->ReturnData($outdata);
	}

	/**
	 * Получение даты формирования списка
	 */
	function getDopDispInfoConsentFormDate()
	{
		$data = $this->ProcessInputData('getDopDispInfoConsentFormDate', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->getDopDispInfoConsentFormDate($data);
		$this->ProcessModelList($response, true, 'При получении даты формирования возникли ошибки')->ReturnData();
	}

	/**
	 * Получение списка случаев ПОВН по пациенту
	 * Используется на форме редактирования карты дисп. наблюдения
	 */
	function loadEvnPLDispProfList()
	{
		$data = $this->ProcessInputData('loadEvnPLDispProfList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadEvnPLDispProfList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Проверка наличия у пациента карты ДВН или ПОВН в текущем году перед добавлением новой карты ПОВН
	 * Используется на форме редактирования карты дисп. наблюдения
	 */
	function checkBeforeAddEvnPLDisp()
	{
		$data = $this->ProcessInputData('checkBeforeAddEvnPLDisp', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkBeforeAddEvnPLDisp($data);
		$this->ProcessModelSave($response, true, 'Ошибка проверки наличия карты ДВН или ПОВН')->ReturnData();
	}
}
?>
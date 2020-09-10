<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnPLDispOrp13_model - модель для работы с талонами по доп. диспансеризации
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Марков Андрей aka Зачетный Копипастер (вообще все что связано с детьми-сиротами - все скопировано с ДД, по уму то конечно многое надо бы переписать... но времени нету - на талон и регистр по детям-сиротам три дня)
 * @version      май 2010
 */

require_once('EvnPLDispAbstract_model.php');

class EvnPLDispOrp13_model extends EvnPLDispAbstract_model
{

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->inputRules = array(
			'loadEvnPLDispOrpEditForm' => array(
				array(
					'field' => 'EvnPLDispOrp_id',
					'label' => 'Идентификатор талона по диспасеризации детей-сирот',
					'rules' => 'trim|required',
					'type' => 'id'
				)
			),
			'deleteEvnPLDispOrp' => array(
				array(
					'field' => 'EvnPLDispOrp_id',
					'label' => 'Идентификатор талона по диспасеризации детей-сирот',
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
			'saveDopDispInfoConsent' => array(
				array(
					'field' => 'EvnPLDispOrp_id',
					'label' => 'Идентификатор талона по доп. диспансеризации',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispOrp_consDate',
					'label' => 'Дата согласия/отказа',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'EvnPLDispOrp_setDate',
					'label' => 'Дата начала диспансеризации',
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
					'field' => 'Server_id',
					'label' => 'Server_id',
					'rules' => 'trim|required',
					'type' => 'int'
				),
				array(
					'field' => 'DispClass_id',
					'label' => 'Вид диспансеризации',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'DopDispInfoConsentData',
					'label' => 'Данные грида по информир. добр. согласию',
					'rules' => '',
					'type' => 'string'
				)
			),
			'checkIfEvnPLDispOrpExists' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => 'trim|required',
					'type' => 'id'
				), array(
					'field' => 'stage',
					'label' => 'Идентификатор этапа',
					'rules' => 'trim|required',
					'type' => 'int'
				), array(
					'default' => date('Y'),
					'field' => 'Year',
					'label' => 'Год',
					'rules' => 'trim|required',
					'type' => 'int'
				)
			),
			'loadEvnVizitDispOrpGrid' => array(
				array(
					'field' => 'EvnPLDispOrp_id',
					'label' => 'Идентификатор талона по диспасеризации детей-сирот',
					'rules' => 'trim|required',
					'type' => 'id'
				)
			),
			'loadEvnVizitDispOrpSecGrid' => array(
				array(
					'field' => 'EvnPLDispOrp_id',
					'label' => 'Идентификатор талона по диспасеризации детей-сирот',
					'rules' => 'trim|required',
					'type' => 'id'
				)
			),
			'loadEvnDiagAndRecomendationGrid' => array(
				array(
					'field' => 'EvnPLDispOrp_id',
					'label' => 'Идентификатор талона по диспасеризации детей-сирот',
					'rules' => 'trim|required',
					'type' => 'id'
				)
			),
			'loadEvnDiagAndRecomendationSecGrid' => array(
				array(
					'field' => 'EvnPLDispOrp_id',
					'label' => 'Идентификатор талона по диспасеризации детей-сирот',
					'rules' => 'trim|required',
					'type' => 'id'
				)
			),
			'loadEvnUslugaDispOrpGrid' => array(
				array(
					'field' => 'EvnPLDispOrp_id',
					'label' => 'Идентификатор талона по диспасеризации детей-сирот',
					'rules' => 'trim|required',
					'type' => 'id'
				)
			),
			'loadEvnUslugaDispOrpSecGrid' => array(
				array(
					'field' => 'EvnPLDispOrp_id',
					'label' => 'Идентификатор талона по диспасеризации детей-сирот',
					'rules' => 'trim|required',
					'type' => 'id'
				)
			),
			'searchEvnPLDispOrp' => array(
				array(
					'field' => 'DocumentType_id',
					'label' => 'Тип документа удостовряющего личность',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispOrp_disDate',
					'label' => 'Дата завершения случая',
					'rules' => 'trim',
					'type' => 'daterange'
				),
				array(
					'field' => 'EvnPLDispOrp_IsFinish',
					'label' => 'Случай завершен',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispOrp_setDate',
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
			'loadDopDispInfoConsent' => array(
				array(
					'field' => 'EvnPLDispOrp_id',
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
					'field' => 'DispClass_id',
					'label' => 'Вид диспансеризации',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispOrp_setDate',
					'label' => 'Дата начала диспансеризации',
					'rules' => 'required',
					'type' => 'date'
				)
			),
			'saveEvnPLDispOrp' => array(
				array(
					'field' => 'AssessmentHealthVaccinData',
					'label' => 'Прививки',
					'rules' => '',
					'type' => 'json_array',
					'assoc' => true
				),
				array(
					'field' => 'EvnPLDispOrp_id',
					'label' => 'Идентификатор талона по ДД',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispOrp_IndexRep',
					'label' => 'Признак повторной подачи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnPLDispOrp_IndexRepInReg',
					'label' => 'Признак повторной подачи в реестре',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnPLDispOrp_fid',
					'label' => 'Идентификатор предыдущего талона по ДД',
					'rules' => 'trim',
					'type' => 'id'
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
					'field' => 'DispClass_id',
					'label' => 'Вид диспансеризации',
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
					'field' => 'EvnPLDispOrp_IsFinish',
					'label' => 'Случай закончен',
					'rules' => 'trim|required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispOrp_IsTwoStage',
					'label' => 'Направлен на 2 этап',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'ChildStatusType_id',
					'label' => 'Статус ребёнка',
					'rules' => 'trim|required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispOrp_setDate',
					'label' => 'Дата',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'Lpu_mid',
					'label' => 'МО мобильной бригады',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispOrp_IsMobile',
					'label' => 'Обслужен мобильной бригадой',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'EvnPLDispOrp_IsOutLpu',
					'label' => 'Проведён вне МО',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'EvnPLDispOrp_consDate',
					'label' => 'Дата',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'Server_id',
					'label' => 'Идентификатор сервера',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DispAppointData',
					'label' => 'Массив данных DispAppointData',
					'rules' => '',
					'type' => 'json_array',
					'assoc' => true
				),
				array(
					'field' => 'EvnVizitDispOrp',
					'label' => 'Массив данных EvnVizitDispOrp',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'EvnUslugaDispOrp',
					'label' => 'Массив данных EvnUslugaDispOrp',
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
					'field' => 'ignoreParentEvnDateCheck',
					'label' => 'Признак игнорирования проверки периода выполенения услуги',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'ignoreOsmotrDlit',
					'label' => 'Признак игнорирования проверки длительности случая',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnPLDispOrp_IsSuspectZNO',
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
					'field' => 'checkAttributeforLpuSection',
					'label' => 'Признак атрибута у отделения',
					'rules' => '',
					'type' => 'int'
				)
			),
			'saveEvnPLDispOrpSec' => array(
				array(
					'field' => 'EvnPLDispOrp_id',
					'label' => 'Идентификатор талона по ДД',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispOrp_IndexRep',
					'label' => 'Признак повторной подачи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnPLDispOrp_IndexRepInReg',
					'label' => 'Признак повторной подачи в реестре',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnPLDispOrp_fid',
					'label' => 'Идентификатор предыдущего талона по ДД',
					'rules' => 'trim',
					'type' => 'id'
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
					'field' => 'DispClass_id',
					'label' => 'Вид диспансеризации',
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
					'field' => 'EvnPLDispOrp_IsFinish',
					'label' => 'Случай закончен',
					'rules' => 'trim|required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispOrp_IsTwoStage',
					'label' => 'Направлен на 2 этап',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'ChildStatusType_id',
					'label' => 'Статус ребёнка',
					'rules' => 'trim|required',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_mid',
					'label' => 'МО мобильной бригады',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispOrp_IsMobile',
					'label' => 'Обслужен мобильной бригадой',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'EvnPLDispOrp_IsOutLpu',
					'label' => 'Проведён вне МО',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'EvnPLDispOrp_consDate',
					'label' => 'Дата',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'Server_id',
					'label' => 'Идентификатор сервера',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DispAppointData',
					'label' => 'Массив данных DispAppointData',
					'rules' => '',
					'type' => 'json_array',
					'assoc' => true
				),
				array(
					'field' => 'EvnVizitDispOrp',
					'label' => 'Массив данных EvnVizitDispOrp',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'EvnUslugaDispOrp',
					'label' => 'Массив данных EvnUslugaDispOrp',
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
					'field' => 'ignoreParentEvnDateCheck',
					'label' => 'Признак игнорирования проверки периода выполенения услуги',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'ignoreOsmotrDlit',
					'label' => 'Признак игнорирования проверки длительности случая',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnPLDispOrp_IsSuspectZNO',
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
			'loadEvnPLDispOrpStreamList' => array(
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
			'saveEvnVizitDispOrp' => array(
				array(
					'field' => 'EvnVizitDispOrp_id',
					'label' => 'Идентификатор осмотра',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispOrp_id',
					'label' => 'Идентификатор талона по ДД',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'DispClass_id',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PersonEvn_id',
					'label' => '',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'OrpDispSpec_id',
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
					'field' => 'DopDispInfoConsent_id',
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
					'field' => 'Lpu_uid',
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
					'field' => 'LpuSectionProfile_id',
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
					'field' => 'Diag_id',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'TumorStage_id',
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
				array(
					'field' => 'EvnVizitDispOrp_setDate',
					'label' => '',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'EvnVizitDispOrp_setTime',
					'label' => '',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'EvnVizitDispOrp_disDate',
					'label' => '',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'EvnVizitDispOrp_disTime',
					'label' => '',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Server_id',
					'label' => '',
					'rules' => 'required',
					'type' => 'int'
				),
			),
			'deleteEvnVizitDispOrp' => array(
				array(
					'field' => 'EvnVizitDispOrp_id',
					'label' => 'Идентификатор осмотра',
					'rules' => 'required',
					'type' => 'id'
				),
			),
		);
		$this->inputRules['searchEvnPLDispOrp'] = array_merge($this->inputRules['searchEvnPLDispOrp'], getAddressSearchFilter());
	}

	/**
	 * Получение входящих параметров
	 */
	function getInputRulesAdv($rule = null)
	{
		if (empty($rule)) {
			return $this->inputRules;
		} else {
			return $this->inputRules[$rule];
		}
	}

	/**
	 * Удаление атрибутов
	 */
	function deleteAttributes($attr, $EvnPLDispOrp_id, $pmUser_id, $DispClass_id)
	{
		// Сперва получаем список
		switch ($attr) {
			case 'EvnUslugaDispOrp':
				$query = "
					select
						EUDO.EvnUslugaDispOrp_id as \"id\"
					from
						v_EvnUslugaDispOrp EUDO
						left join lateral (
							select 
								t2.DopDispInfoConsent_IsAgree,
								t2.DopDispInfoConsent_IsEarlier
							from v_SurveyTypeLink t1
								left join v_DopDispInfoConsent t2 on t2.SurveyTypeLink_id = t1.SurveyTypeLink_id
									and t2.EvnPLDisp_id = :EvnPLDispOrp_id
							where t1.UslugaComplex_id = EUDO.UslugaComplex_id
								and coalesce(t1.DispClass_id, 3) = :DispClass_id
							order by t2.DopDispInfoConsent_id desc
							limit 1
						) DDIC on true
					where
						EUDO.EvnUslugaDispOrp_rid = :EvnPLDispOrp_id
						and coalesce(DDIC.DopDispInfoConsent_IsAgree, 1) = 1
						and coalesce(DDIC.DopDispInfoConsent_IsEarlier, 1) = 1
						and coalesce(EUDO.EvnUslugaDispOrp_IsVizitCode, 1) = 1
				";
				break;

			case 'EvnVizitDispOrp':
				$query = "
					select
						EVDO.EvnVizitDispOrp_id as \"id\"
					from
						v_EvnVizitDispOrp EVDO
						left join v_DopDispInfoConsent DDIC on DDIC.DopDispInfoConsent_id = EVDO.DopDispInfoConsent_id 
						left join v_SurveyTypeLink STL on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
					where
						EVDO.EvnVizitDispOrp_pid = :EvnPLDispOrp_id
						and coalesce(DDIC.DopDispInfoConsent_IsAgree, 1) = 1
						and coalesce(DDIC.DopDispInfoConsent_IsEarlier, 1) = 1
						and coalesce(STL.DispClass_id, 3) = :DispClass_id
				";
				break;

			case 'DopDispInfoConsent':
				$query = "
					select DDIC.DopDispInfoConsent_id as \"id\"
					from v_DopDispInfoConsent DDIC
						inner join v_SurveyTypeLink STL on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
						inner join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
					where DDIC.EvnPLDisp_id = :EvnPLDispOrp_id
						and ST.SurveyType_Code >= 2
				";
				break;

			default:
				$query = "
					select " . $attr . "_id as \"id\"
					from v_" . $attr . "
					where EvnPLDisp_id = :EvnPLDispOrp_id
				";
				break;
		}

		if (!empty($query)) {
			$result = $this->db->query($query, array('EvnPLDispOrp_id' => $EvnPLDispOrp_id, 'DispClass_id' => $DispClass_id));

			if (!is_object($result)) {
				return 'Ошибка при выполнении запроса к базе данных';
			}

			$response = $result->result('array');

			if (is_array($response) && count($response) > 0) {
				foreach ($response as $array) {
					$query = "
						select 
							error_code as \"Error_Code\", 
            				error_message as \"Error_Msg\"
            			from p_" . $attr . "_del(
            				" . $attr . "_id := :id
							" . (in_array($attr, array('EvnUslugaDispOrp', 'EvnVizitDispOrp')) ? ", pmuser_id := :pmUser_id" : "") . "
            			);
					";
					$result = $this->db->query($query, array('id' => $array['id'], 'pmUser_id' => $pmUser_id));

					if (!is_object($result)) {
						return 'Ошибка при выполнении запроса к базе данных';
					}

					$res = $result->result('array');

					if (is_array($res) && count($res) > 0 && !empty($res[0]['Error_Msg'])) {
						return $res[0]['Error_Msg'];
					}
				}
			}
		}

		return '';
	}

	/**
	 * Удаление карты диспансеризации
	 */
	function deleteEvnPLDispOrp($data)
	{
		$query = "
			select 
				error_code as \"Error_Code\", 
				error_message as \"Error_Msg\"
			from p_EvnPLDispOrp_del(
			    evnpldisporp_id := :EvnPLDispOrp_id,
				pmuser_id := :pmUser_id
			);
		";
		$result = $this->db->query($query, array(
			'EvnPLDispOrp_id' => $data['EvnPLDispOrp_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление талона ДД)'));
		}
	}

	/**
	 * Возвращает данные карты диспансеризации
	 */
	function loadEvnPLDispOrpEditForm($data)
	{
		$additionalFields = array();
		$additionalJoins = array();

		$accessType = '
			case
				when EPLDD.Lpu_id = :Lpu_id then 1
				' . (count($data['session']['linkedLpuIdList']) > 1 ? 'when EPLDD.Lpu_id in (' . implode(',', $data['session']['linkedLpuIdList']) . ') and coalesce(EPLDD.EvnPLDispOrp_IsTransit, 1) = 2 then 1' : '') . '
				else 0
			end = 1
		';

		if ($data['session']['region']['nick'] == 'ekb') {
			$accessType .= " and coalesce(EPLDD.EvnPLDispOrp_isPaid, 1) = 1";
		}
		if ($data['session']['region']['nick'] == 'pskov') {
			$accessType .= "and coalesce(EPLDD.EvnPLDispOrp_isPaid, 1) = 1
			 	and not exists(
					select  RD.Registry_id
					from r60.v_RegistryData RD
						inner join v_Registry R on R.Registry_id = RD.Registry_id
						inner join v_RegistryStatus RS on RS.RegistryStatus_id = R.RegistryStatus_id
					where
						RD.Evn_id = EPLDD.EvnPLDispOrp_id
						and RS.RegistryStatus_SysNick not in ('work','paid')
					limit 1
				)
			";
		}

		if ($data['session']['region']['nick'] == 'buryatiya') {
			$additionalFields[] = "eudo_vizit.UslugaComplex_id";
			$additionalJoins[] = "
				left join lateral(
					select UslugaComplex_id
					from v_EvnUslugaDispOrp
					where
						EvnUslugaDispOrp_IsVizitCode = 2
						and EvnUslugaDispOrp_pid = EPLDD.EvnPLDispOrp_id
					limit 1
				) eudo_vizit on true
			";
		}

		$query = "
			SELECT
				case when {$accessType} then 'edit' else 'view' end as \"accessType\",
				EPLDD.EvnPLDispOrp_id as \"EvnPLDispOrp_id\",
				COALESCE(EPLDD.EvnPLDispOrp_IsPaid, 1) as \"EvnPLDispOrp_IsPaid\",
				COALESCE(EPLDD.EvnPLDispOrp_IndexRep, 0) as \"EvnPLDispOrp_IndexRep\",
				COALESCE(EPLDD.EvnPLDispOrp_IndexRepInReg, 1) as \"EvnPLDispOrp_IndexRepInReg\",
				EPLDD.EvnPLDispOrp_fid as \"EvnPLDispOrp_fid\",
				EPLDD.EvnPLDispOrp_IsFinish as \"EvnPLDispOrp_IsFinish\",
				EPLDD.EvnPLDispOrp_IsTwoStage as \"EvnPLDispOrp_IsTwoStage\",
				EPLDD.ChildStatusType_id as \"ChildStatusType_id\",
				to_char(EPLDD.EvnPLDispOrp_setDate, 'dd.mm.yyyy') as \"EvnPLDispOrp_setDate\",
				to_char(EPLDD_FIR.EvnPLDispOrp_setDate, 'dd.mm.yyyy') as \"EvnPLDispOrp_firSetDate\",
				to_char(EPLDD.EvnPLDispOrp_consDT, 'dd.mm.yyyy') as \"EvnPLDispOrp_consDate\",
				EPLDD.AttachType_id as \"AttachType_id\",
				EPLDD.Lpu_aid as \"Lpu_aid\",
				case when EPLDD.EvnPLDispOrp_IsMobile = 2 then 1 else 0 end as \"EvnPLDispOrp_IsMobile\",
				case when EPLDD.EvnPLDispOrp_IsOutLpu = 2 then 1 else 0 end as \"EvnPLDispOrp_IsOutLpu\",
				EPLDD.Lpu_mid as \"Lpu_mid\",
				EPLDD.PersonEvn_id as \"PersonEvn_id\",
				EPLDD.Server_id as \"Server_id\",
				COALESCE(EPLDD.DispClass_id,3) as \"DispClass_id\",
				EPLDD.PayType_id as \"PayType_id\",
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
				AH.AssessmentHealth_HealthRecom as \"AssessmentHealth_HealthRecom\",
				AH.RehabilitEndType_id as \"RehabilitEndType_id\",
				CASE WHEN AH.AssessmentHealth_id IS NULL THEN 1 else AH.ProfVaccinType_id end as \"ProfVaccinType_id\",
				AH.HealthKind_id as \"HealthKind_id\",
				AH.NormaDisturbanceType_id as \"NormaDisturbanceType_id\",
				AH.NormaDisturbanceType_uid as \"NormaDisturbanceType_uid\",
				AH.NormaDisturbanceType_eid as \"NormaDisturbanceType_eid\",
				to_char(ecp.EvnCostPrint_setDT, 'dd.mm.yyyy') as \"EvnCostPrint_setDT\",
				ecp.EvnCostPrint_Number as \"EvnCostPrint_Number\",
				ecp.EvnCostPrint_IsNoPrint as \"EvnCostPrint_IsNoPrint\",
				EPLDD.EvnPLDispOrp_IsSuspectZNO as \"EvnPLDispOrp_IsSuspectZNO\",
				EPLDD.Diag_spid as \"Diag_spid\",
				AH.AssessmentHealth_id as \"AssessmentHealth_id\"
				" . (count($additionalFields) > 0 ? "," . implode(", ", $additionalFields) : "") . "
			FROM
				v_EvnPLDispOrp EPLDD
				left join v_EvnCostPrint ecp on ecp.Evn_id = EPLDD.EvnPLDispOrp_id
				left join v_EvnPLDispOrp EPLDD_FIR on EPLDD_FIR.EvnPLDispOrp_id = EPLDD.EvnPLDispOrp_fid
				left join lateral(
					select * from v_AssessmentHealth where EvnPLDisp_id = EPLDD.EvnPLDispOrp_id LIMIT 1
				) AH on true
				" . (count($additionalJoins) > 0 ? implode(" ", $additionalJoins) : "") . "
			WHERE
				EPLDD.EvnPLDispOrp_id = :EvnPLDispOrp_id
			LIMIT 1
		";
		$result = $this->db->query($query, array('EvnPLDispOrp_id' => $data['EvnPLDispOrp_id'], 'Lpu_id' => $data['Lpu_id']));

		if (is_object($result)) {
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
				foreach ($resp_vac as $resp_vacone) {
					$AssessmentHealthVaccinData[] = $resp_vacone['VaccinType_id'];
				}
			}
			if (!empty($resp[0])) {
				$resp[0]['AssessmentHealthVaccinData'] = $AssessmentHealthVaccinData;
			}
			return $resp;
		} else {
			return false;
		}
	}

	/**
	 * Возвращает данные
	 */
	function getEvnPLDispOrpFields($data)
	{
		$query = "
			SELECT
				rtrim(lp.Lpu_Name) as \"Lpu_Name\",
				rtrim(coalesce(addr0.Address_Address, '')) as \"Lpu_Address\",
				rtrim(coalesce(lp1.Lpu_Name, '')) as \"Lpu_AName\",
				rtrim(coalesce(addr1.Address_Address, '')) as \"Lpu_AAddress\",
				rtrim(lp.Lpu_OGRN) as \"Lpu_OGRN\",
				coalesce(pc.PersonCard_Code, '') as \"PersonCard_Code\",
				ps.Person_SurName || ' ' || ps.Person_FirName || ' ' || coalesce(ps.Person_SecName, '') as \"Person_FIO\",
				sx.Sex_Name as \"Sex_Name\",
				sx.Sex_id as \"Sex_id\",
				CST.ChildStatusType_Code as \"ChildStatusType_Code\",
				case when coalesce(CCT.CategoryChildType_Code,1) = 5 then 2
					else case when CCT.CategoryChildType_Code = 6 then 3
					else case when CCT.CategoryChildType_Code = 7 then 4
					end end end as \"CategoryChildType_Code\",

			    	coalesce(CAST(AH.AssessmentHealth_Weight as decimal),0) as \"AssesmentHealth_Weight\",
				coalesce(AH.AssessmentHealth_Height,0) as \"AssesmentHealth_Height\",
				coalesce(AH.AssessmentHealth_Head,0) as \"AssessmentHealth_Head\",
				coalesce(AH.WeightAbnormType_id,0) as \"WeightAbnormType_id\",
				coalesce(AH.HeightAbnormType_id,0) as \"HeightAbnormType_id\",

				coalesce(AH.AssessmentHealth_Gnostic,0) as \"AssessmentHealth_Gnostic\",
				coalesce(AH.AssessmentHealth_Motion,0) as \"AssessmentHealth_Motion\",
				coalesce(AH.AssessmentHealth_Social,0) as \"AssessmentHealth_Social\",
				coalesce(AH.AssessmentHealth_Speech,0) as \"AssessmentHealth_Speech\",
				coalesce(AH.NormaDisturbanceType_id,0) as \"NormaDisturbanceType_id\",
				coalesce(AH.NormaDisturbanceType_uid,0) as \"NormaDisturbanceType_uid\",
				coalesce(AH.NormaDisturbanceType_eid,0) as \"NormaDisturbanceType_eid\",

				coalesce(AH.AssessmentHealth_P,0) as \"AssessmentHealth_P\",
				coalesce(AH.AssessmentHealth_Ax,0) as \"AssessmentHealth_Ax\",
				coalesce(AH.AssessmentHealth_Fa,0) as \"AssessmentHealth_Fa\",
				coalesce(AH.AssessmentHealth_Ma,0) as \"AssessmentHealth_Ma\",
				coalesce(AH.AssessmentHealth_Me,0) as \"AssessmentHealth_Me\",

				coalesce(AH.AssessmentHealth_Years,1) as \"AssessmentHealth_Years\",
				coalesce(AH.AssessmentHealth_Month,1) as \"AssessmentHealth_Month\",
				coalesce(AH.AssessmentHealth_IsRegular,1) as \"AssessmentHealth_IsRegular\",
				coalesce(AH.AssessmentHealth_IsIrregular,1) as \"AssessmentHealth_IsIrregular\",
				coalesce(AH.AssessmentHealth_IsAbundant,1) as \"AssessmentHealth_IsAbundant\",
				coalesce(AH.AssessmentHealth_IsModerate,1) as \"AssessmentHealth_IsModerate\",
				coalesce(AH.AssessmentHealth_IsScanty,1) as \"AssessmentHealth_IsScanty\",
				coalesce(AH.AssessmentHealth_IsPainful,1) as \"AssessmentHealth_IsPainful\",
				coalesce(AH.AssessmentHealth_IsPainless,1) as \"AssessmentHealth_IsPainless\",
                		coalesce(AH.HealthKind_id,0) as \"HealthKind_id\",
               			coalesce(AH.InvalidType_id,1) as \"InvalidType_id\",
                		to_char(AH.AssessmentHealth_setDT, 'dd.mm.yyyy') as \"AssessmentHealth_setDT\",
				to_char(AH.AssessmentHealth_reExamDT, 'dd.mm.yyyy') as \"AssessmentHealth_reExamDT\",
				coalesce(AH.InvalidDiagType_id,0) as \"InvalidDiagType_id\",

				coalesce(AH.AssessmentHealth_IsMental,0) as \"AssessmentHealth_IsMental\",
				coalesce(AH.AssessmentHealth_IsOtherPsych,0) as \"AssessmentHealth_IsOtherPsych\",
				coalesce(AH.AssessmentHealth_IsLanguage,0) as \"AssessmentHealth_IsLanguage\",
				coalesce(AH.AssessmentHealth_IsVestibular,0) as \"AssessmentHealth_IsVestibular\",
				coalesce(AH.AssessmentHealth_IsVisual,0) as \"AssessmentHealth_IsVisual\",
				coalesce(AH.AssessmentHealth_IsMeals,0) as \"AssessmentHealth_IsMeals\",
				coalesce(AH.AssessmentHealth_IsMotor,0) as \"AssessmentHealth_IsMotor\",
				coalesce(AH.AssessmentHealth_IsDeform,0) as \"AssessmentHealth_IsDeform\",
				coalesce(AH.AssessmentHealth_IsGeneral,0) as \"AssessmentHealth_IsGeneral\",

				to_char(AH.AssessmentHealth_ReabDT, 'dd.mm.yyyy') as \"AssessmentHealth_ReabDT\",
				coalesce(AH.RehabilitEndType_id,0) as \"RehabilitEndType_id\",
				coalesce(AH.ProfVaccinType_id,0) as \"ProfVaccinType_id\",

				coalesce(osmo.OrgSMO_Nick, '') as \"OrgSMO_Nick\",
				coalesce(case when pls.PolisType_id = 4 then '' else pls.Polis_Ser end, '') as \"Polis_Ser\",
				coalesce(case when pls.PolisType_id = 4 then ps.Person_EdNum else pls.Polis_Num end, '') as \"Polis_Num\",
				coalesce(osmo.OrgSMO_Name, '') as \"OrgSMO_Name\",
				to_char(ps.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\",
				to_char(ps.Person_BirthDay, 'yyyy-mm-dd hh24:mi:ss') as \"Person_BirthDay2\",
				coalesce(ps.Person_Snils,'') as \"Person_Snils\",
				coalesce(addr.Address_Address, '') as \"Person_Address\",
				jborg.Org_Nick as \"Org_Nick\",
				atype.AttachType_Name as \"AttachType_Name\",
				to_char(EPLDD.EvnPLDispOrp_setDate, 'dd.mm.yyyy') as \"EvnPLDispOrp_setDate\",
				to_char(EPLDD.EvnPLDispOrp_setDate, 'yyyy-mm-dd hh24:mi:ss') as \"EvnPLDispOrp_setDate2\",
				to_char(EPLDD.EvnPLDispOrp_disDate, 'dd.mm.yyyy') as \"EvnPLDispOrp_disDate\",
				null as \"EvnPLDispOrp_IsBud\",
				OSTAC.Org_Name as \"OrgStac_Name\",
				to_char(PDO.PersonDispOrp_setDate, 'dd.mm.yyyy') as \"PersonDispOrp_setDate\",
				PDO.DisposalCause_id as \"DisposalCause_id\",
				to_char(PDO.PersonDispOrp_DisposDate, 'dd.mm.yyyy') as \"PersonDispOrp_DisposDate\",
				coalesce(AH.AssessmentHealth_HealthRecom,'') as \"AssessmentHealth_HealthRecom\",
				coalesce(AH.AssessmentHealth_DispRecom,'') as \"AssessmentHealth_DispRecom\"
			FROM
				v_EvnPLDispOrp EPLDD 
				--left join v_PersonDispOrp PDO  on PDO.Person_id = EPLDD.Person_id
				left join v_PersonDispOrp PDO on (PDO.Person_id = EPLDD.Person_id and PDO.PersonDispOrp_Year = EXTRACT(YEAR FROM EPLDD.EvnPLDispOrp_setDate))
				left join v_Org OSTAC on OSTAC.Org_id = PDO.Org_id
				left join v_AssessmentHealth AH on AH.EvnPLDisp_id = EPLDD.EvnPLDispOrp_id
				left join v_CategoryChildType CCT on CCT.CategoryChildType_id = PDO.CategoryChildType_id
				inner join v_Lpu lp on lp.Lpu_id = EPLDD.Lpu_id
				left join v_Lpu lp1 on lp1.Lpu_id = EPLDD.Lpu_aid
				left join Address addr1 on addr1.Address_id = lp1.UAddress_id
				left join Address addr0 on addr0.Address_id = lp.UAddress_id
				left join v_PersonCard pc on pc.Person_id = EPLDD.Person_id and pc.LpuAttachType_id = 1
				inner join v_PersonState ps on ps.Person_id = EPLDD.Person_id
				inner join Sex sx on sx.Sex_id = ps.Sex_id
				left join ChildStatusType CST on CST.ChildStatusType_id = EPLDD.ChildStatusType_id
				left join Polis pls on pls.Polis_id = ps.Polis_id
				left join v_OrgSmo osmo on osmo.OrgSmo_id = pls.OrgSmo_id
				left join Address addr on addr.Address_id = ps.PAddress_id
				left join Job jb on jb.Job_id = ps.Job_id
				left join Org jborg on jborg.Org_id = jb.Org_id
				left join AttachType atype on atype.AttachType_id = EPLDD.AttachType_id
			WHERE
				(1 = 1)
				and EPLDD.EvnPLDispOrp_id = :EvnPLDispOrp_id
		";
		$result = $this->db->query($query, array('EvnPLDispOrp_id' => $data['EvnPLDispOrp_id']));

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение списка осмотров врача-специалиста в талоне по ДД
	 * Входящие данные: $data['EvnPLDispOrp_id']
	 * На выходе: ассоциативный массив результатов запроса
	 */
	function loadEvnVizitDispOrpGrid($data)
	{

		$query = "
			select distinct
				EVZDD.EvnVizitDispOrp_id  as \"EvnVizitDispOrp_id\",
				EVZDD.Server_id as \"Server_id\",
				EVZDD.PersonEvn_id as \"PersonEvn_id\",
				to_char(EVZDD.EvnVizitDispOrp_setDate, 'dd.mm.yyyy') as \"EvnVizitDispOrp_setDate\",
				EVZDD.EvnVizitDispOrp_setTime as \"EvnVizitDispOrp_setTime\",
				to_char(EVZDD.EvnVizitDispOrp_disDate, 'dd.mm.yyyy') as \"EvnVizitDispOrp_disDate\",
				EVZDD.EvnVizitDispOrp_disTime as \"EvnVizitDispOrp_disTime\",
				RTRIM(LS.LpuSection_Name) as \"LpuSection_Name\",
				RTRIM(MP.Person_Fio) as \"MedPersonal_Fio\",
				RTRIM(DDS.OrpDispSpec_Name) as \"OrpDispSpec_Name\",
				DDS.OrpDispSpec_Code as \"OrpDispSpec_Code\",
				RTRIM(D.Diag_Code) as \"Diag_Code\",
				EVZDD.MedPersonal_id as \"MedPersonal_id\",
				EVZDD.MedStaffFact_id as \"MedStaffFact_id\",
				EVZDD.OrpDispSpec_id as \"OrpDispSpec_id\",
				EVZDD.UslugaComplex_id as \"UslugaComplex_id\",
				EVZDD.DopDispInfoConsent_id as \"DopDispInfoConsent_id\",
				EVZDD.LpuSection_id as \"LpuSection_id\",
				EVZDD.Lpu_id as \"Lpu_uid\",
				EVZDD.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				EVZDD.MedSpecOms_id as \"MedSpecOms_id\",
				EVZDD.Diag_id as \"Diag_id\",
				EVZDD.TumorStage_id as \"TumorStage_id\",
				TS.TumorStage_Name as \"TumorStage_Name\",
				EVZDD.DopDispDiagType_id as \"DopDispDiagType_id\",
				EVZDD.DopDispAlien_id as \"DopDispAlien_id\",
				1 as \"Record_Status\"
			from v_EvnVizitDispOrp EVZDD
				left join LpuSection LS on LS.LpuSection_id = EVZDD.LpuSection_id
				left join v_MedPersonal MP on MP.MedPersonal_id = EVZDD.MedPersonal_id
				left join OrpDispSpec DDS on DDS.OrpDispSpec_id = EVZDD.OrpDispSpec_id
				left join Diag D on D.Diag_id = EVZDD.Diag_id
				left join v_TumorStage TS on TS.TumorStage_id = EVZDD.TumorStage_id
			where EVZDD.EvnVizitDispOrp_pid = :EvnPLDispOrp_id
		";
		$result = $this->db->query($query, array('EvnPLDispOrp_id' => $data['EvnPLDispOrp_id']));

		if (is_object($result)) {
			$resp = $result->result('array');
			foreach ($resp as &$respone) {
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
					'EvnDiagDopDisp_pid' => $respone['EvnVizitDispOrp_id']
				));

				if (is_object($result_dddgd)) {
					$respone['EvnDiagDopDispGridData'] = $result_dddgd->result('array');
				}

				$respone['EvnDiagDopDispGridData'] = json_encode($respone['EvnDiagDopDispGridData']);
			}
			return $resp;
		} else {
			return false;
		}
	}

	/**
	 * Получение списка осмотров врача-специалиста в талоне по ДД
	 * Входящие данные: $data['EvnPLDispOrp_id']
	 * На выходе: ассоциативный массив результатов запроса
	 */
	function loadEvnVizitDispOrpSecGrid($data)
	{

		$query = "
			select
				EVZDD.EvnVizitDispOrp_id as \"EvnVizitDispOrp_id\",
				EVZDD.Server_id as \"Server_id\",
				EVZDD.PersonEvn_id as \"PersonEvn_id\",
				to_char(EVZDD.EvnVizitDispOrp_setDate, 'dd.mm.yyyy') as \"EvnVizitDispOrp_setDate\",
				EVZDD.EvnVizitDispOrp_setTime as \"EvnVizitDispOrp_setTime\",
				to_char(EVZDD.EvnVizitDispOrp_disDate, 'dd.mm.yyyy') as \"EvnVizitDispOrp_disDate\",
				EVZDD.EvnVizitDispOrp_disTime as \"EvnVizitDispOrp_disTime\",
				RTRIM(LS.LpuSection_Name) as \"LpuSection_Name\",
				RTRIM(MP.Person_Fio) as \"MedPersonal_Fio\",
				RTRIM(UC.UslugaComplex_Name) as \"UslugaComplex_Name\",
				UC.UslugaComplex_Code as \"UslugaComplex_Code\",
				RTRIM(D.Diag_Code) as \"Diag_Code\",
				EVZDD.MedPersonal_id as \"MedPersonal_id\",
				EVZDD.MedStaffFact_id as \"MedStaffFact_id\",
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
			from v_EvnVizitDispOrp EVZDD
				left join LpuSection LS on LS.LpuSection_id = EVZDD.LpuSection_id
				left join v_MedPersonal MP on MP.MedPersonal_id = EVZDD.MedPersonal_id
				left join v_UslugaComplex UC on UC.UslugaComplex_id = EVZDD.UslugaComplex_id
				left join v_DopDispAlien DDA  on DDA.DopDispAlien_id = EVZDD.DopDispAlien_id
				left join Diag D on D.Diag_id = EVZDD.Diag_id
			where EVZDD.EvnVizitDispOrp_pid = :EvnPLDispOrp_id
		";
		$result = $this->db->query($query, array('EvnPLDispOrp_id' => $data['EvnPLDispOrp_id']));

		if (is_object($result)) {
			$resp = $result->result('array');
			foreach ($resp as &$respone) {
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
					'EvnDiagDopDisp_pid' => $respone['EvnVizitDispOrp_id']
				));

				if (is_object($result_dddgd)) {
					$respone['EvnDiagDopDispGridData'] = $result_dddgd->result('array');
				}

				$respone['EvnDiagDopDispGridData'] = json_encode($respone['EvnDiagDopDispGridData']);
			}
			return $resp;
		} else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function loadEvnDiagAndRecomendation($data)
	{
		$filter = " and EVZDD.EvnVizitDispOrp_pid = :EvnPLDisp_id ";
		$query = "
        select EVDO.DispSurveilType_id as \"DispSurveilType_id\",
						COALESCE(EVDO.EvnVizitDispOrp_IsVMP,1) as \"EvnVizitDisp_IsVMP\",
						EVDO.EvnVizitDispOrp_IsFirstTime as \"EvnVizitDisp_IsFirstTime\",
						COALESCE(MC1.ConditMedCareType_nid,1) as \"ConditMedCareType1_nid\",
						MC1.PlaceMedCareType_nid as \"PlaceMedCareType1_nid\",
						MC1.ConditMedCareType_id as \"ConditMedCareType1_id\",
						MC1.PlaceMedCareType_id as \"PlaceMedCareType1_id\",
						MC1.LackMedCareType_id as \"LackMedCareType1_id\",
						MC2.ConditMedCareType_nid as \"ConditMedCareType2_nid\",
						MC2.PlaceMedCareType_nid as \"PlaceMedCareType2_nid\",
						COALESCE(MC2.ConditMedCareType_id,1) as \"ConditMedCareType2_id\",
						MC2.PlaceMedCareType_id as \"PlaceMedCareType2_id\",
						MC2.LackMedCareType_id as \"LackMedCareType2_id\",
						MC3.ConditMedCareType_nid as \"ConditMedCareType3_nid\",
						MC3.PlaceMedCareType_nid as \"PlaceMedCareType3_nid\",
						COALESCE(MC3.ConditMedCareType_id,1) as \"ConditMedCareType3_id\",
						MC3.PlaceMedCareType_id as \"PlaceMedCareType3_id\",
						MC3.LackMedCareType_id as \"LackMedCareType3_id\",
						D.Diag_Code as \"Diag_Code\"
                        from v_EvnVizitDispOrp EVZDD
                        left join OrpDispSpec ODS on ODS.OrpDispSpec_id = EVZDD.OrpDispSpec_id
                        left join v_Diag D on D.Diag_id = EVZDD.Diag_id
                        left join v_EvnVizitDispOrp EVDO on EVDO.EvnVizitDispOrp_id = EVZDD.EvnVizitDispOrp_id
                        left join lateral(
							select * from v_MedCare MC where MC.MedCareType_id = 1 and MC.EvnVizitDisp_id = EVDO.EvnVizitDispOrp_id LIMIT 1
						) MC1 on true
						-- лечение
						left join lateral(
							select * from v_MedCare MC where MC.MedCareType_id = 2 and MC.EvnVizitDisp_id = EVDO.EvnVizitDispOrp_id LIMIT 1
						) MC2 on true
						-- медицинская реабилитация / санаторно-курортное лечение
						left join lateral(
							select * from v_MedCare MC where MC.MedCareType_id = 3 and MC.EvnVizitDisp_id = EVDO.EvnVizitDispOrp_id LIMIT 1
						) MC3 ON true
                        where (1=1) {$filter}
                        and LEFT(D.Diag_Code,1) <> 'Z'
        ";
		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение списка "Диагнозы и рекоменации"
	 * Входящие данные: $data['EvnPLDispOrp_id']
	 * На выходе: ассоциативный массив результатов запроса
	 */
	function loadEvnDiagAndRecomendationGrid($data)
	{
		$query = "
			select
				EVZDD.EvnVizitDispOrp_id as \"EvnVizitDispOrp_id\",
				D.Diag_id as \"Diag_id\",
				D.Diag_Code || '. ' || D.Diag_Name as \"Diag_Name\",
				D.Diag_Code \"Diag_Code\",
				ODS.OrpDispSpec_Name as \"OrpDispSpec_Name\"
			from v_EvnVizitDispOrp EVZDD
				left join OrpDispSpec ODS on ODS.OrpDispSpec_id = EVZDD.OrpDispSpec_id
				left join v_Diag D on D.Diag_id = EVZDD.Diag_id
			where EVZDD.EvnVizitDispOrp_pid = :EvnPLDispOrp_id and LEFT(D.Diag_Code,1) <> 'Z'
		";
		$result = $this->db->query($query, array('EvnPLDispOrp_id' => $data['EvnPLDispOrp_id']));

		if (is_object($result)) {
			$resp = $result->result('array');
			foreach ($resp as &$item) {
				// для каждой строки получаем данные формы "Состояние здоровья: Редактирование" и запихиваем в JSON
				$query = "
					select
						EVDO.DopDispDiagType_id as \"DopDispDiagType_id\",
						EVDO.DispSurveilType_id as \"DispSurveilType_id\",
					    COALESCE(EVDO.EvnVizitDispOrp_IsVMP,1) as \"EvnVizitDisp_IsVMP\",
						EVDO.EvnVizitDispOrp_IsFirstTime as \"EvnVizitDisp_IsFirstTime\",
						COALESCE(MC1.ConditMedCareType_nid,1) as \"ConditMedCareType1_nid\",
						MC1.PlaceMedCareType_nid as \"PlaceMedCareType1_nid\",
						MC1.ConditMedCareType_id as \"ConditMedCareType1_id\",
						MC1.PlaceMedCareType_id as \"PlaceMedCareType1_id\",
						MC1.LackMedCareType_id as \"LackMedCareType1_id\",
						MC2.ConditMedCareType_nid as \"ConditMedCareType2_nid\",
						MC2.PlaceMedCareType_nid as \"PlaceMedCareType2_nid\",
						COALESCE(MC2.ConditMedCareType_id,1) as \"ConditMedCareType2_id\",
						MC2.PlaceMedCareType_id as \"PlaceMedCareType2_id\",
						MC2.LackMedCareType_id as \"LackMedCareType2_id\",
						MC3.ConditMedCareType_nid as \"ConditMedCareType3_nid\",
						MC3.PlaceMedCareType_nid as \"PlaceMedCareType3_nid\",
						COALESCE(MC3.ConditMedCareType_id,1) as \"ConditMedCareType3_id\",
						MC3.PlaceMedCareType_id as \"PlaceMedCareType3_id\",
						MC3.LackMedCareType_id as \"LackMedCareType3_id\"
					from v_EvnVizitDispOrp EVDO 
						left join v_Diag D on D.Diag_id = EVDO.Diag_id
						-- дополнительные консультации и исследования
						left join lateral(
							select * from v_MedCare MC where MC.MedCareType_id = 1 and MC.EvnVizitDisp_id = EVDO.EvnVizitDispOrp_id LIMIT 1
						) MC1 on true
						-- лечение
						left join lateral(
							select * from v_MedCare MC where MC.MedCareType_id = 2 and MC.EvnVizitDisp_id = EVDO.EvnVizitDispOrp_id LIMIT 1
						) MC2 on true
						-- медицинская реабилитация / санаторно-курортное лечение
						left join lateral(
							select * from v_MedCare MC where MC.MedCareType_id = 3 and MC.EvnVizitDisp_id = EVDO.EvnVizitDispOrp_id LIMIT 1
						) MC3 on true
					where EVDO.EvnVizitDispOrp_id = :EvnVizitDispOrp_id
					LIMIT 1
				";
				$resultmc = $this->db->query($query, array('EvnVizitDispOrp_id' => $item['EvnVizitDispOrp_id']));
				$item['FormDataJSON'] = json_encode(array());
				if (is_object($resultmc)) {
					$respmc = $resultmc->result('array');
					if (count($respmc) > 0) {
						$item['FormDataJSON'] = json_encode($respmc[0]);
					}
				}

			}
			return $resp;
		} else {
			return false;
		}
	}

	/**
	 * Получение списка "Диагнозы и рекоменации"
	 * Входящие данные: $data['EvnPLDispOrp_id']
	 * На выходе: ассоциативный массив результатов запроса
	 */
	function loadEvnDiagAndRecomendationSecGrid($data)
	{
		$query = "
			select
				EVZDD.EvnVizitDispOrp_id as \"EvnVizitDispOrp_id\",
				D.Diag_id as \"Diag_id\",
				D.Diag_Code || '. ' || D.Diag_Name as \"Diag_Name\",
				UC.UslugaComplex_Name as \"UslugaComplex_Name\"
			from v_EvnVizitDispOrp EVZDD 
				left join v_UslugaComplex UC on UC.UslugaComplex_id = EVZDD.UslugaComplex_id
				left join v_Diag D on D.Diag_id = EVZDD.Diag_id
			where EVZDD.EvnVizitDispOrp_pid = :EvnPLDispOrp_id and LEFT(D.Diag_Code,1) <> 'Z'
		";
		$result = $this->db->query($query, array('EvnPLDispOrp_id' => $data['EvnPLDispOrp_id']));

		if (is_object($result)) {
			$resp = $result->result('array');
			foreach ($resp as &$item) {
				// для каждой строки получаем данные формы "Состояние здоровья: Редактирование" и запихиваем в JSON
				$query = "
					select
						EVDO.DispSurveilType_id as \"DispSurveilType_id\",
						COALESCE(EVDO.EvnVizitDispOrp_IsVMP,1) as \"EvnVizitDisp_IsVMP\",
						EVDO.EvnVizitDispOrp_IsFirstTime as \"EvnVizitDisp_IsFirstTime\",
						COALESCE(MC1.ConditMedCareType_nid,1) as \"ConditMedCareType1_nid\",
						MC1.PlaceMedCareType_nid as \"PlaceMedCareType1_nid\",
						MC1.ConditMedCareType_id as \"ConditMedCareType1_id\",
						MC1.PlaceMedCareType_id as \"PlaceMedCareType1_id\",
						MC1.LackMedCareType_id as \"LackMedCareType1_id\",
						MC2.ConditMedCareType_nid as \"ConditMedCareType2_nid\",
						MC2.PlaceMedCareType_nid as \"PlaceMedCareType2_nid\",
						COALESCE(MC2.ConditMedCareType_id,1) as \"ConditMedCareType2_id\",
						MC2.PlaceMedCareType_id as \"PlaceMedCareType2_id\",
						MC2.LackMedCareType_id as \"LackMedCareType2_id\",
						MC3.ConditMedCareType_nid as \"ConditMedCareType3_nid\",
						MC3.PlaceMedCareType_nid as \"PlaceMedCareType3_nid\",
						COALESCE(MC3.ConditMedCareType_id,1) as \"ConditMedCareType3_id\",
						MC3.PlaceMedCareType_id as \"PlaceMedCareType3_id\",
						MC3.LackMedCareType_id as \"LackMedCareType3_id\"
					from v_EvnVizitDispOrp EVDO
						left join v_Diag D on D.Diag_id = EVDO.Diag_id
						-- дополнительные консультации и исследования
						left join lateral(
							select * from v_MedCare MC where MC.MedCareType_id = 1 and MC.EvnVizitDisp_id = EVDO.EvnVizitDispOrp_id LIMIT 1
						) MC1 on true
						-- лечение
						left join lateral(
							select * from v_MedCare MC where MC.MedCareType_id = 2 and MC.EvnVizitDisp_id = EVDO.EvnVizitDispOrp_id LIMIT 1
						) MC2 on true
						-- медицинская реабилитация / санаторно-курортное лечение
						left join lateral(
							select * from v_MedCare MC where MC.MedCareType_id = 3 and MC.EvnVizitDisp_id = EVDO.EvnVizitDispOrp_id LIMIT 1
						) MC3 on true
					where EVDO.EvnVizitDispOrp_id = :EvnVizitDispOrp_id
				";
				$resultmc = $this->db->query($query, array('EvnVizitDispOrp_id' => $item['EvnVizitDispOrp_id']));
				$item['FormDataJSON'] = json_encode(array());
				if (is_object($resultmc)) {
					$respmc = $resultmc->result('array');
					if (count($respmc) > 0) {
						$item['FormDataJSON'] = json_encode($respmc[0]);
					}
				}

			}
			return $resp;
		} else {
			return false;
		}
	}

	/**
	 * Получение списка осмотров врача-специалиста в талоне по ДД
	 * Входящие данные: $data['EvnPLDispOrp_id']
	 * На выходе: ассоциативный массив результатов запроса
	 */
	function loadEvnVizitDispOrpData($data)
	{

		$query = "
			select
				EVZDD.EvnVizitDispOrp_id as \"EvnVizitDispOrp_id\",
				EVZDD.Server_id as \"Server_id\",
				EVZDD.PersonEvn_id as \"PersonEvn_id\",
				to_char(EVZDD.EvnVizitDispOrp_setDate, 'dd.mm.yyyy') as \"EvnVizitDispOrp_setDate\",
				to_char(EVZDD.EvnVizitDispOrp_disDate, 'dd.mm.yyyy') as \"EvnVizitDispOrp_disDate\",
				RTRIM(MP.Person_Fio) as \"MedPersonal_Fio\",
				RTRIM(coalesce(MP.MedPersonal_TabCode, '')) as \"MedPersonal_TabCode\",
				RTRIM(DDS.OrpDispSpec_Name) as \"OrpDispSpec_Name\",
				DDS.OrpDispSpec_Code as \"OrpDispSpec_Code\",
				RTRIM(D.Diag_Code) as \"Diag_Code\",
				EVZDD.MedPersonal_id as \"MedPersonal_id\",
				EVZDD.OrpDispSpec_id as \"OrpDispSpec_id\",
				EVZDD.DeseaseStage_id as \"DeseaseStage_id\",
				EVZDD.HealthKind_id as \"HealthKind_id\",
				EVZDD.EvnVizitDispOrp_IsSanKur as \"EvnVizitDispOrp_IsSanKur\",
				EVZDD.EvnVizitDispOrp_IsOut as \"EvnVizitDispOrp_IsOut\",
				EVZDD.LpuSection_id as \"LpuSection_id\",
				EVZDD.Diag_id as \"Diag_id\",
				EVZDD.DopDispDiagType_id as \"DopDispDiagType_id\",
				EVZDD.DopDispAlien_id as \"DopDispAlien_id\",
				1 as \"Record_Status\"
			from v_EvnVizitDispOrp EVZDD
				left join LpuSection LS  on LS.LpuSection_id = EVZDD.LpuSection_id
				left join v_MedPersonal MP  on MP.MedPersonal_id = EVZDD.MedPersonal_id
				left join OrpDispSpec DDS on DDS.OrpDispSpec_id = EVZDD.OrpDispSpec_id
				left join Diag D on D.Diag_id = EVZDD.Diag_id
			where EVZDD.EvnVizitDispOrp_pid = :EvnPLDispOrp_id
		";
		$result = $this->db->query($query, array('EvnPLDispOrp_id' => $data['EvnPLDispOrp_id']));

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение списка исследований в талоне по ДД
	 * Входящие данные: $data['EvnPLDispOrp_id']
	 * На выходе: ассоциативный массив результатов запроса
	 */
	function loadEvnUslugaDispOrpGrid($data)
	{

		$query = "
			select
				EUDD.EvnUslugaDispOrp_id as \"EvnUslugaDispOrp_id\",
				EUDD.Server_id as \"Server_id\",
				EUDD.PersonEvn_id as \"PersonEvn_id\",
				to_char(EUDD.EvnUslugaDispOrp_setDate, 'dd.mm.yyyy') as \"EvnUslugaDispOrp_setDate\",
				EUDD.EvnUslugaDispOrp_setTime as \"EvnUslugaDispOrp_setTime\",
				to_char(EUDD.EvnUslugaDispOrp_disDate, 'dd.mm.yyyy') as \"EvnUslugaDispOrp_disDate\",
				EUDD.EvnUslugaDispOrp_disTime as \"EvnUslugaDispOrp_disTime\",
				to_char(EUDD.EvnUslugaDispOrp_didDate, 'dd.mm.yyyy') as \"EvnUslugaDispOrp_didDate\",
				EUDD.OrpDispUslugaType_id as \"OrpDispUslugaType_id\",
				RTRIM(DDUT.OrpDispUslugaType_Name) as \"OrpDispUslugaType_Name\",
				EUDD.LpuSection_uid as \"LpuSection_id\",
				EUDD.Lpu_uid as \"Lpu_uid\",
				EUDD.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				EUDD.MedSpecOms_id as \"MedSpecOms_id\",
				RTRIM(LS.LpuSection_Name) as \"LpuSection_Name\",
				EUDD.MedPersonal_id as \"MedPersonal_id\",
				EUDD.MedStaffFact_id as \"MedStaffFact_id\",
				RTRIM(MP.Person_Fio) as \"MedPersonal_Fio\",
				EUDD.UslugaComplex_id as \"UslugaComplex_id\",
				RTRIM(UC.UslugaComplex_Name) as \"UslugaComplex_Name\",
				RTRIM(UC.UslugaComplex_Code) as \"UslugaComplex_Code\",
				EUDD.ExaminationPlace_id as \"ExaminationPlace_id\",
				EUDD.EvnUslugaDispOrp_Result as \"EvnUslugaDispOrp_Result\",
				1 as \"Record_Status\"
			from v_EvnUslugaDispOrp EUDD
				left join OrpDispUslugaType DDUT on DDUT.OrpDispUslugaType_id = EUDD.OrpDispUslugaType_id
				left join v_LpuSection LS on LS.LpuSection_id = EUDD.LpuSection_uid
				left join lateral (
					select Person_Fio
					from v_MedPersonal
					where MedPersonal_id = EUDD.MedPersonal_id
				    limit 1
				) MP on true
				left join v_UslugaComplex UC on UC.UslugaComplex_id = EUDD.UslugaComplex_id
			where EUDD.EvnUslugaDispOrp_pid = :EvnPLDispOrp_id
				and COALESCE(EUDD.EvnUslugaDispOrp_IsVizitCode, 1) = 1
		";

		$result = $this->db->query($query, array('EvnPLDispOrp_id' => $data['EvnPLDispOrp_id']));

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение списка исследований в талоне по ДД
	 * Входящие данные: $data['EvnPLDispOrp_id']
	 * На выходе: ассоциативный массив результатов запроса
	 */
	function loadEvnUslugaDispOrpSecGrid($data)
	{

		$query = "
			select
				EUDD.EvnUslugaDispOrp_id as \"EvnUslugaDispOrp_id\",
				EUDD.Server_id as \"Server_id\",
				EUDD.PersonEvn_id as \"PersonEvn_id\",
				to_char(EUDD.EvnUslugaDispOrp_setDate, 'dd.mm.yyyy') as \"EvnUslugaDispOrp_setDate\",
				EUDD.EvnUslugaDispOrp_setTime as \"EvnUslugaDispOrp_setTime\",
				to_char(EUDD.EvnUslugaDispOrp_disDate, 'dd.mm.yyyy') as \"EvnUslugaDispOrp_disDate\",
				EUDD.EvnUslugaDispOrp_disTime as \"EvnUslugaDispOrp_disTime\",
				to_char(EUDD.EvnUslugaDispOrp_didDate, 'dd.mm.yyyy') as \"EvnUslugaDispOrp_didDate\",
				EUDD.OrpDispUslugaType_id as \"OrpDispUslugaType_id\",
				RTRIM(DDUT.OrpDispUslugaType_Name) as \"OrpDispUslugaType_Name\",
				EUDD.LpuSection_uid as \"LpuSection_id\",
				EUDD.Lpu_uid as \"Lpu_uid\",
				EUDD.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				EUDD.MedSpecOms_id as \"MedSpecOms_id\",
				RTRIM(LS.LpuSection_Name) as \"LpuSection_Name\",
				EUDD.MedPersonal_id as \"MedPersonal_id\",
				EUDD.MedStaffFact_id as \"MedStaffFact_id\",
				RTRIM(MP.Person_Fio) as \"MedPersonal_Fio\",
				EUDD.UslugaComplex_id as \"UslugaComplex_id\",
				RTRIM(UC.UslugaComplex_Name) as \"UslugaComplex_Name\",
				RTRIM(UC.UslugaComplex_Code) as \"UslugaComplex_Code\",
				EUDD.ExaminationPlace_id as \"ExaminationPlace_id\",
				EUDD.EvnUslugaDispOrp_Result as \"EvnUslugaDispOrp_Result\",
				EP.ExaminationPlace_Name as \"ExaminationPlace_Name\",
				1 as \"Record_Status\"
			from v_EvnUslugaDispOrp EUDD
				left join OrpDispUslugaType DDUT on DDUT.OrpDispUslugaType_id = EUDD.OrpDispUslugaType_id
				left join v_LpuSection LS on LS.LpuSection_id = EUDD.LpuSection_uid
				left join v_MedPersonal MP on MP.MedPersonal_id = EUDD.MedPersonal_id
				left join v_UslugaComplex UC on UC.UslugaComplex_id = EUDD.UslugaComplex_id
				left join v_ExaminationPlace EP on EP.ExaminationPlace_id = EUDD.ExaminationPlace_id
			where EUDD.EvnUslugaDispOrp_pid = :EvnPLDispOrp_id
				and COALESCE(EUDD.EvnUslugaDispOrp_IsVizitCode, 1) = 1
		";

		$result = $this->db->query($query, array('EvnPLDispOrp_id' => $data['EvnPLDispOrp_id']));

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение списка исследований в талоне по ДД
	 * Входящие данные: $data['EvnPLDispOrp_id']
	 * На выходе: ассоциативный массив результатов запроса
	 */
	function loadEvnUslugaDispOrpData($data)
	{
		$query = "
			select
				EUDD.EvnUslugaDispOrp_id as \"EvnUslugaDispOrp_id\",
				to_char(EUDD.EvnUslugaDispOrp_setDate, 'dd.mm.yyyy') as \"EvnUslugaDispOrp_setDate\",
				to_char(EUDD.EvnUslugaDispOrp_didDate, 'dd.mm.yyyy') as \"EvnUslugaDispOrp_didDate\",
				EUDD.OrpDispUslugaType_id as \"OrpDispUslugaType_id\"
			from v_EvnUslugaDispOrp EUDD
			where EUDD.EvnUslugaDispOrp_pid = :EvnPLDispOrp_id
				and COALESCE(EUDD.EvnUslugaDispOrp_IsVizitCode, 1) = 1
		";

		$result = $this->db->query($query, array('EvnPLDispOrp_id' => $data['EvnPLDispOrp_id']));

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Возвращает данные
	 */
	function loadEvnPLDispOrpStreamList($data)
	{
		$filter = '';
		$queryParams = array();

		$filter .= " and EPL.pmUser_insID = :pmUser_id ";
		$queryParams['pmUser_id'] = $data['pmUser_id'];

		if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $data['begTime'])) {
			$filter .= " and EPL.EvnPL_insDT >= :date_time";
			$queryParams['date_time'] = $data['begDate'] . " " . $data['begTime'];
		}

		if (isset($data['Lpu_id'])) {
			$filter .= " and EPL.Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}

		$query = "
        	SELECT
				EPL.EvnPL_id as \"EvnPL_id\",
				EPL.Person_id as \"Person_id\",
				EPL.Server_id as \"Server_id\",
				EPL.PersonEvn_id as \"PersonEvn_id\",
				RTRIM(EPL.EvnPL_NumCard) as \"EvnPL_NumCard\",
				RTRIM(PS.Person_Surname) as \"Person_Surname\",
				RTRIM(PS.Person_Firname) as \"Person_Firname\",
				RTRIM(PS.Person_Secname) as \"Person_Secname\",
				to_char(PS.Person_Birthday, 'dd.mm.yyyy') as \"Person_Birthday\",
				to_char(EPL.EvnPL_setDate, 'dd.mm.yyyy') as \"EvnPL_setDate\",
				to_char(EPL.EvnPL_disDate, 'dd.mm.yyyy') as \"EvnPL_disDate\",
				EPL.EvnPL_VizitCount as \"EvnPL_VizitCount\",
				IsFinish.YesNo_Name as \"EvnPL_IsFinish\"
			FROM v_EvnPL EPL 
				inner join v_PersonState PS  on PS.Person_id = EPL.Person_id
				left join YesNo IsFinish  on IsFinish.YesNo_id = EPL.EvnPL_IsFinish
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
	 * Возвращает данные о посещениях
	 */
	function loadEvnVizitPLDispOrpGrid($data)
	{
		$query = "
			select
				EVPL.EvnVizitPL_id as \"EvnVizitPL_id\",
				EVPL.Server_id as \"Server_id\",
				EVPL.PersonEvn_id as \"PersonEvn_id\",
				EVPL.LpuSection_id as \"LpuSection_id\",
				EVPL.MedPersonal_id as \"MedPersonal_id\",
				EVPL.MedPersonal_sid as \"MedPersonal_sid\",
				EVPL.PayType_id as \"PayType_id\",
				EVPL.ProfGoal_id as \"ProfGoal_id\",
				EVPL.ServiceType_id as \"ServiceType_id\",
				EVPL.VizitType_id as \"VizitType_id\",
				EVPL.EvnVizitPL_Time as \"EvnVizitPL_Time\",
				to_char(EVPL.EvnVizitPL_setDate, 'dd.mm.yyyy') as \"EvnVizitPL_setDate\",
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
				left join PayType PT  on PT.PayType_id = EVPL.PayType_id
				left join ServiceType ST on ST.ServiceType_id = EVPL.ServiceType_id
				left join VizitType VT  on VT.VizitType_id = EVPL.VizitType_id
			where EVPL.EvnVizitPL_pid = :EvnPL_id
		";
		$result = $this->db->query($query, array('EvnPL_id' => $data['EvnPL_id']));

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Проверка того, что человек есть в регистре по ДД и у него заведены все необходимые данные
	 */
	function checkPersonData($data)
	{
		$query = "
			select
				Sex_id as \"Sex_id\",
				SocStatus_id as \"SocStatus_id\",
				ps.UAddress_id as \"Person_UAddress_id\",
				ps.Polis_Ser as \"Polis_Ser\",
				ps.Polis_Num as \"Polis_Num\",
				o.Org_Name as \"Org_Name\",
				o.Org_INN as \"Org_INN\",
				o.Org_OGRN as \"Org_OGRN\",
				o.UAddress_id as \"Org_UAddress_id\",
				--o.Okved_id,
				os.OrgSmo_Name as \"OrgSmo_Name\",
				(datediff('year', PS.Person_Birthday, dbo.tzGetDate())
				+ case when EXTRACT(MONTH FROM ps.Person_Birthday) > EXTRACT(MONTH FROM dbo.tzGetDate())
				or (EXTRACT(MONTH FROM ps.Person_Birthday) = EXTRACT(MONTH FROM dbo.tzGetDate()) and EXTRACT(DAY FROM ps.Person_Birthday) > EXTRACT(DAY FROM dbo.tzGetDate()))
				then -1 else 0 end) as \"Person_Age\",
				to_char(PS.Person_Birthday, 'dd.mm.yyyy') as \"Person_Birthday\"
			from v_persondisporp pdd
			left join v_PersonState ps on ps.Person_id=pdd.Person_id
			left join v_Job j on j.Job_id=ps.Job_id
			left join v_Org o on o.Org_id=j.Org_id
			left join v_Polis pol on pol.Polis_id=ps.Polis_id
			left join v_OrgSmo os on os.OrgSmo_id=pol.OrgSmo_id
			where pdd.Person_id = :Person_id
		";

		$result = $this->db->query($query, array('Person_id' => $data['Person_id']));
		$response = $result->result('array');

		if (!is_array($response) || count($response) == 0)
			return array(array('Error_Msg' => 'Этого человека нет в регистре по диспансеризации детей-сирот!'));

		$error = Array();
		if (ArrayVal($response[0], 'Sex_id') == '')
			$errors[] = 'Не заполнен Пол';
		if (ArrayVal($response[0], 'SocStatus_id') == '')
			$errors[] = 'Не заполнен Соц. статус';
		if (ArrayVal($response[0], 'Person_UAddress_id') == '')
			$errors[] = 'Не заполнен Адрес по месту регистрации';
		if (ArrayVal($response[0], 'Polis_Num') == '')
			$errors[] = 'Не заполнен Номер полиса';
		if (ArrayVal($response[0], 'Polis_Ser') == '')
			$errors[] = 'Не заполнена Серия полиса';
		if (ArrayVal($response[0], 'OrgSmo_id') == '')
			$errors[] = 'Не заполнена Организация, выдавшая полис';
		if (ArrayVal($response[0], 'Org_UAddress_id') == '')
			$errors[] = 'Не заполнен Адрес места работы';
		if (ArrayVal($response[0], 'Org_INN') == '')
			$errors[] = 'Не заполнен ИНН места работы';
		if (ArrayVal($response[0], 'Org_OGRN') == '')
			$errors[] = 'Не заполнена ОГРН организации, в которой содержится ребенок';


		If (count($error) > 0) { // есть ошибки в заведении
			$errstr = implode("<br/>", $errors);
			return array(array('Error_Msg' => 'Проверьте полноту заведения данных у человека!<br/>' . $errstr));
		}
		return array("Ok", ArrayVal($response[0], 'Sex_id'), ArrayVal($response[0], 'Person_Age'), ArrayVal($response[0], 'Person_Birthday'));
	}

	/**
	 * Проверка атрибута у отделения
	 */
	function checkAttributeforLpuSection($data)
	{
		$query = "
			select 
				EVZDD.EvnVizitDispOrp_didDT as \"EvnVizitDispOrp_didDT\",
				ASVal.AttributeSign_id as \"AttributeSign_id\",
				ASVal.AttributeSignValue_begDate as \"AttributeSignValue_begDate\",
				ASVal.AttributeSignValue_endDate as \"AttributeSignValue_endDate\"
			from
				v_EvnVizitDispOrp EVZDD 
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
				EVZDD.EvnVizitDispOrp_pid = :EvnPLDispOrp_id
		";

		$result = $this->db->query($query, array('EvnPLDispOrp_id'=>$data['EvnPLDispOrp_id']));
		$response = $result->result('array');

		if ( is_array($response) && count($response) > 0 ) {

			$col_lpusection=0;
			foreach($response as $res) {
				if (
					!empty($res['AttributeSign_id'])
					&& (is_null($res['AttributeSignValue_begDate']) || (isset($res['AttributeSignValue_begDate']) && $res['AttributeSignValue_begDate']<=$res['EvnVizitDispOrp_didDT']))
					&& (is_null($res['AttributeSignValue_endDate']) || (isset($res['AttributeSignValue_endDate']) && $res['AttributeSignValue_endDate']>=$res['EvnVizitDispOrp_didDT']))
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
	 * Возварщает данные о согласии на диспансеризации
	 */
	function loadDopDispInfoConsent($data)
	{
		$pre_select = "";
		$select = "";
		$filter = "";
		$joinList = array();
		$params = array(
			'EvnPLDispOrp_id' => $data['EvnPLDispOrp_id'],
			'Person_id' => $data['Person_id'],
			'DispClass_id' => $data['DispClass_id'],
			'EvnPLDispOrp_setDate' => (!empty($data['EvnPLDispOrp_setDate']) ? $data['EvnPLDispOrp_setDate'] : null)
		);

		if (getRegionNick() == 'ufa') { // для уфы дополнительно отфильтровываем услуги посещений по LpuLevel
			$filter .= " and (COALESCE(ucat.UslugaCategory_SysNick, '') != 'lpusection' or ((case when LpuLevel.LpuLevel_code in (2,6) then '6' when LpuLevel.LpuLevel_code in (3,5) then '5' when LpuLevel.LpuLevel_code in (1,8) then '8' end) = left(UC.UslugaComplex_Code,1)))";
			$filter .= " and (UC.UslugaComplex_id is not null or Stl.UslugaComplex_id is null)";

			$joinList[] = "left join v_UslugaComplex UC on UC.UslugaComplex_id = STL.UslugaComplex_id";
			$joinList[] = "left join v_Lpu lpu on lpu.Lpu_id = :Lpu_id";
			$joinList[] = "left join v_LpuLevel LpuLevel  on LpuLevel.LpuLevel_id = lpu.LpuLevel_id";
			$joinList[] = "left join v_UslugaCategory ucat  on ucat.UslugaCategory_id = UC.UslugaCategory_id";

			$params['Lpu_id'] = $data['Lpu_id'];
		}

		$orderby = "
			order by
				case when DDIC.DopDispInfoConsent_id is not null then 0 else 1 end asc -- в первую очередь сохраненные
		";

		if (getRegionNick() == 'ekb') {
			$pre_select .= " ,stl.MedSpecOms_id as \"MedSpecOms\"";
			$select .= " ,ucpl.MedSpecOms_id as MedSpecOms";
			$joinList[] = "left join r66.v_UslugaComplexPartitionLink ucpl on ucpl.UslugaComplex_id = STL.UslugaComplex_id";
			$orderby = "
				order by
					case when DDIC.DopDispInfoConsent_id is not null then 0 else 1 end asc, -- в первую очередь сохраненные
					case when ucpl.MedSpecOms_id is null then 0 else 1 end asc -- иначе те у которых MedSpecOms_id пустой
			";
		}

		// @task https://redmine.swan.perm.ru/issues/123599
		if (in_array(getRegionNick(), array('buryatiya', 'krym'))) {
			if (!empty($params['EvnPLDispOrp_setDate'])) {
				$params['ageDate'] = substr($params['EvnPLDispOrp_setDate'], 0, 4);
			} else {
				$params['ageDate'] = date('Y');
			}

			$params['ageDate'] .= '-12-31';
		} else {
			$params['ageDate'] = $params['EvnPLDispOrp_setDate'];
		}

		$query = "
			select
				COALESCE(STL.DopDispInfoConsent_id, -STL.SurveyTypeLink_id) as \"DopDispInfoConsent_id\",
				STL.EvnPLDisp_id as \"EvnPLDispOrp_id\",
				UC.UslugaComplex_Code as \"UslugaComplex_Code\",
				ODS.OrpDispSpec_Code as \"OrpDispSpec_Code\",
				STL.SurveyTypeLink_id as \"SurveyTypeLink_id\",
				COALESCE(STL.SurveyTypeLink_IsNeedUsluga, 1) as \"SurveyTypeLink_IsNeedUsluga\",
				ST.SurveyType_Code as \"SurveyType_Code\",
				ST.SurveyType_Name as \"SurveyType_Name\",
				case WHEN :EvnPLDispOrp_id IS NULL OR STL.DopDispInfoConsent_IsAgree = 2 then 1 else 0 end as \"DopDispInfoConsent_IsAgree\", -- для новой карты проставляем чекбоксы
				case WHEN STL.DopDispInfoConsent_IsEarlier = 2 then 1 else 0 end as \"DopDispInfoConsent_IsEarlier\"
				{$pre_select}
			from v_SurveyType ST 
				inner join lateral(
					(				
					-- не удалённые
					select 
						STL.SurveyTypeLink_id,
						STL.UslugaComplex_id,
						STL.SurveyTypeLink_IsNeedUsluga,
						STL.SurveyTypeLink_IsDel,
						DDIC.DopDispInfoConsent_id,
						DDIC.EvnPLDisp_id,
						DDIC.DopDispInfoConsent_IsEarlier,
						DDIC.DopDispInfoConsent_IsAgree
						{$select}
					from
						v_SurveyTypeLink STL
						left join v_DopDispInfoConsent DDIC on DDIC.SurveyTypeLink_id = STL.SurveyTypeLink_id and DDIC.EvnPLDisp_id = :EvnPLDispOrp_id
						" . implode(' ', $joinList) . ",
					     (SELECT coalesce(Sex_id, 3) as Sex_id,
							dbo.Age2(Person_BirthDay, coalesce(CAST(:ageDate as date), dbo.tzGetDate())) as Age
							from v_PersonState ps
							where ps.Person_id = :Person_id
							limit 1) pers
					where
						ST.SurveyType_id = STL.SurveyType_id
						and COALESCE(STL.DispClass_id, 3) = :DispClass_id -- дети-сироты, 1 этап
						and (COALESCE(STL.Sex_id, pers.Sex_id) = pers.Sex_id) -- по полу
						and (pers.age between COALESCE(SurveyTypeLink_From, 0) and  COALESCE(SurveyTypeLink_To, 999)) -- по возрасту, в принципе по библии Иссак лет 800 жил же
						and COALESCE(STL.SurveyTypeLink_IsDel, 1) = 1
						and (STL.SurveyTypeLink_begDate is null or STL.SurveyTypeLink_begDate <= :EvnPLDispOrp_setDate)
						and (STL.SurveyTypeLink_endDate is null or STL.SurveyTypeLink_endDate >= :EvnPLDispOrp_setDate)
						" . $filter . "
					{$orderby}
					LIMIT 1
					)
					union
					(
					-- удалённые, но сохранённые в согласии
					select
						STL.SurveyTypeLink_id,
						STL.UslugaComplex_id,
						STL.SurveyTypeLink_IsNeedUsluga,
						STL.SurveyTypeLink_IsDel,
						DDIC.DopDispInfoConsent_id,
						DDIC.EvnPLDisp_id,
						DDIC.DopDispInfoConsent_IsEarlier,
						DDIC.DopDispInfoConsent_IsAgree
						{$select}
					from
						v_SurveyTypeLink STL
						inner join v_DopDispInfoConsent DDIC on DDIC.SurveyTypeLink_id = STL.SurveyTypeLink_id and DDIC.EvnPLDisp_id = :EvnPLDispOrp_id
						" . implode(' ', $joinList) . ",
						( SELECT coalesce(Sex_id, 3) as Sex_id,
							dbo.Age2(Person_BirthDay, coalesce(CAST(:ageDate as date), dbo.tzGetDate())) as Age
							from v_PersonState ps
							where ps.Person_id = :Person_id
							limit 1) pers
					where
						ST.SurveyType_id = STL.SurveyType_id
						and COALESCE(STL.DispClass_id, 3) = :DispClass_id -- дети-сироты, 1 этап
						and (COALESCE(STL.Sex_id, pers.Sex_id) = pers.Sex_id) -- по полу
						and (pers.age between COALESCE(SurveyTypeLink_From, 0) and  COALESCE(SurveyTypeLink_To, 999)) -- по возрасту, в принципе по библии Иссак лет 800 жил же
						and STL.SurveyTypeLink_IsDel = 2
						and (STL.SurveyTypeLink_begDate is null or STL.SurveyTypeLink_begDate <= :EvnPLDispOrp_setDate)
						and (STL.SurveyTypeLink_endDate is null or STL.SurveyTypeLink_endDate >= :EvnPLDispOrp_setDate)
						" . $filter . "
					limit 1
					)
				) STL on true
				left join v_UslugaComplex UC on UC.UslugaComplex_id = STL.UslugaComplex_id
				left join v_OrpDispSpec ODS on ODS.OrpDispSpec_id = ST.OrpDispSpec_id
			order by
				ST.SurveyType_Code
			
			";

		//echo getDebugSql($query, $params);die();

		$result = $this->db->query($query, $params);
		/*echo getDebugSql($query, array(
			'EvnPLDispOrp_id' => $data['EvnPLDispOrp_id']
		));die();*/
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Возвращает данные
	 */
	function getDopDispInfoConsentForSurveyTypeLink($EvnPLDisp_id, $SurveyTypeLink_id)
	{
		$query = "
			select
				DopDispInfoConsent_id as \"DopDispInfoConsent_id\"
			from
				v_DopDispInfoConsent
			where
				EvnPLDisp_id = :EvnPLDisp_id
				and SurveyTypeLink_id = case when length( cast( :SurveyTypeLink_id as varchar ) ) = 0 then 0 else cast(:SurveyTypeLink_id as bigint) end
		";

		$result = $this->db->query($query, array(
			'EvnPLDisp_id' => $EvnPLDisp_id,
			'SurveyTypeLink_id' => $SurveyTypeLink_id
		));

		if (is_object($result)) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return $resp[0]['DopDispInfoConsent_id'];
			}
		}

		return null;
	}

	/**
	 * Сохранение информированного согласия
	 */
	function saveDopDispInfoConsent($data)
	{
		// Стартуем транзакцию
		$this->db->trans_begin();

		ConvertFromWin1251ToUTF8($data['DopDispInfoConsentData']);
		$items = json_decode($data['DopDispInfoConsentData'], true);
		$itemsCount = 0;
		$dopdispicarray = array(-1); // массив под id сохранненых согласий

		foreach ($items as $item) {
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

			if ($item['DopDispInfoConsent_IsEarlier'] == 2 || $item['DopDispInfoConsent_IsAgree'] == 2) {
				$itemsCount++;
			}

			// получаем идентификатор DopDispInfoConsent_id для SurveyType_id и EvnPLDisp_id (должна быть только одна запись для каждой пары значений)
			$item['DopDispInfoConsent_id'] = $this->getDopDispInfoConsentForSurveyTypeLink($data['EvnPLDispOrp_id'], $item['SurveyTypeLink_id']);

			if (!empty($item['DopDispInfoConsent_id']) && $item['DopDispInfoConsent_id'] > 0) {
				$proc = 'p_DopDispInfoConsent_upd';
			} else {
				$proc = 'p_DopDispInfoConsent_ins';
				$item['DopDispInfoConsent_id'] = null;
			}

			if (empty($item['SurveyTypeLink_id'])) {
				$this->db->trans_rollback();
				return array(
					'success' => false,
					'Error_Msg' => 'Ошибка при сохранении информированного добровольного согласия (отсутсвует ссылка на SurveyTypeLink)'
				);
			}

			$query = "
				SELECT
					dopdispinfoconsent_id as \"DopDispInfoConsent_id\", 
					error_code as \"Error_Code\", 
					error_message as \"Error_Msg\"
				FROM {$proc} (
						DopDispInfoConsent_id := :DopDispInfoConsent_id, 
						EvnPLDisp_id := :EvnPLDispOrp_id, 
						SurveyTypeLink_id := :SurveyTypeLink_id,
						DopDispInfoConsent_IsAgree := :DopDispInfoConsent_IsAgree, 
						DopDispInfoConsent_IsEarlier := :DopDispInfoConsent_IsEarlier, 
						pmUser_id := :pmUser_id
				);";
			$result = $this->db->query($query, array(
				'EvnPLDispOrp_id' => $data['EvnPLDispOrp_id'],
				'DopDispInfoConsent_id' => $item['DopDispInfoConsent_id'],
				'SurveyTypeLink_id' => $item['SurveyTypeLink_id'],
				'DopDispInfoConsent_IsAgree' => $item['DopDispInfoConsent_IsAgree'],
				'DopDispInfoConsent_IsEarlier' => $item['DopDispInfoConsent_IsEarlier'],
				'pmUser_id' => $data['pmUser_id']
			));

			if (is_object($result)) {
				$res = $result->result('array');

				if (is_array($res) && count($res) > 0 && !empty($res[0]['Error_Msg'])) {
					$this->db->trans_rollback();
					return array(
						'success' => false,
						'Error_Msg' => $res[0]['Error_Msg']
					);
				}

				if (is_array($res) && count($res) > 0 && !empty($res[0]['DopDispInfoConsent_id'])) {
					$dopdispicarray[] = $res[0]['DopDispInfoConsent_id'];
				}
			} else {
				$this->db->trans_rollback();
				return array(
					'success' => false,
					'Error_Msg' => 'Ошибка при выполнении запроса к базе данных (строка ' . __LINE__ . ')'
				);
			}
		}

		// удаляем все согласия не удовлетворяющие посещению (т.е. все не сохранённые только что согласия)
		$query = "select DopDispInfoConsent_id as \"DopDispInfoConsent_id\" from v_DopDispInfoConsent where DopDispInfoConsent_id not in (" . implode(',', $dopdispicarray) . ") and EvnPLDisp_id = :EvnPLDispOrp_id";
		$result = $this->db->query($query, array('EvnPLDispOrp_id' => $data['EvnPLDispOrp_id']));
		if (is_object($result)) {
			$res = $result->result('array');
			if (is_array($res) && count($res) > 0) {
				foreach ($res as $resone) {
					$selectString = "
						error_code as \"Error_Code\", 
						error_message as \"Error_Msg\"
					";
					$queryParams = array(
						'DopDispInfoConsent_id' => $resone['DopDispInfoConsent_id'],
						'pmUser_id' => $data['pmUser_id']
					);
					$query = "
						select {$selectString}
						FROM {$proc}(
							DopDispInfoConsent_id := :DopDispInfoConsent_id,
							pmUser_id := :pmUser_id
						);
					";
					$delresult = $this->db->query($query, $queryParams);

					if (is_object($delresult)) {
						$resp = $delresult->result('array');

						if (is_array($resp) && count($resp) > 0 && !empty($resp[0]['Error_Msg'])) {
							$this->db->trans_rollback();
							return array(
								'success' => false,
								'Error_Msg' => $resp[0]['Error_Msg']
							);
						}
					} else {
						$this->db->trans_rollback();
						return array(
							'success' => false,
							'Error_Msg' => 'Ошибка при выполнении запроса к базе данных (строка ' . __LINE__ . ')'
						);
					}
				}
			}
		}

		// Чистим атрибуты и услуги
		$attrArray = array(
			'EvnUslugaDispOrp', // Услуги с отказом
			'EvnVizitDispOrp' // Осмотры с отказом
		);

		if ($itemsCount == 0) {
			//$attrArray[] = 'EvnDiagDopDisp'; // Ранее известные имеющиеся заболевания, впервые выявленные заболевания
			//$attrArray[] = 'HeredityDiag'; // Наследственность по заболеваниям
			//$attrArray[] = 'ProphConsult'; // Показания к углубленному профилактическому консультированию
			//$attrArray[] = 'NeedConsult'; // Показания к консультации врача-специалиста
			$attrArray[] = 'DopDispInfoConsent';
			$attrArray[] = 'AssessmentHealth';
		}

		foreach ($attrArray as $attr) {
			$deleteResult = $this->deleteAttributes($attr, $data['EvnPLDispOrp_id'], $data['pmUser_id'], $data['DispClass_id']);

			if (!empty($deleteResult)) {
				$this->db->trans_rollback();
				return array(
					'success' => false,
					'Error_Msg' => $deleteResult . ' (строка ' . __LINE__ . ')'
				);
			}
		}

		// проставляем признак отказа
		if ($itemsCount == 0) {
			$data['EvnPLDisp_IsRefusal'] = 2;
		} else {
			$data['EvnPLDisp_IsRefusal'] = 1;
		}

		// Надо обновить в карте дату согласия
		$query = "
			update EvnPLDisp set EvnPLDisp_consDT = :EvnPLDispOrp_consDate, EvnPLDisp_IsRefusal = :EvnPLDisp_IsRefusal where Evn_id = :EvnPLDispOrp_id
		";
		$result = $this->db->query($query, $data);

		// Обновляется дата начала диспансеризации
		$query = "
			update Evn set Evn_setDT = :EvnPLDispOrp_setDate where Evn_id = :EvnPLDispOrp_id
		";
		$result = $this->db->query($query, $data);

		$query = "
			update EvnPLBase set EvnPLBase_IsFinish = 1 where Evn_id = :EvnPLBase_id
		";
		$this->db->query($query, array(
			'EvnPLBase_id' => $data['EvnPLDispOrp_id']
		));

		$this->db->trans_commit();

		return array(
			'success' => true,
			'Error_Msg' => ''
		);
	}

	/**
	 * Возвращает данные
	 */
	function getEvnUslugaDispOrpForEvnVizit($EvnVizitDispOrp_id)
	{
		$query = "
			select
				EvnUslugaDispOrp_id as \"EvnUslugaDispOrp_id\"
			from
				v_EvnUslugaDispOrp
			where
				EvnUslugaDispOrp_pid = :EvnVizitDispOrp_id
			  	and COALESCE(EvnUslugaDispOrp_IsVizitCode, 1) = 1
			LIMIT 1
		";

		$result = $this->db->query($query, array(
			'EvnVizitDispOrp_id' => $EvnVizitDispOrp_id
		));

		if (is_object($result)) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return $resp[0]['EvnUslugaDispOrp_id'];
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

		if (is_object($result)) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return $resp[0]['UslugaComplex_id'];
			}
		}

		return null;
	}

	/**
	 * Проверка правильности адреса человека
	 */
	function checkPersonAddress($data)
	{
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
				--v_PersonState ps (nolock)
				v_Person_all p
				left join v_Address ua on ua.Address_id = p.UAddress_id
				left join v_KLRgn uklr on uklr.KLRgn_id = ua.KLRgn_id
				left join v_KLSubRgn uklsr on uklsr.KLSubRgn_id = ua.KLSubRgn_id
				left join v_KLCity uklc  on uklc.KLCity_id = ua.KLCity_id
				left join v_KLTown uklt on uklt.KLTown_id = ua.KLTown_id
				left join v_KLStreet ukls on ukls.KLStreet_id = ua.KLStreet_id
				left join v_Address pa  on pa.Address_id = p.PAddress_id
				left join v_KLRgn pklr  on pklr.KLRgn_id = pa.KLRgn_id
				left join v_KLSubRgn pklsr  on pklsr.KLSubRgn_id = pa.KLSubRgn_id
				left join v_KLCity pklc  on pklc.KLCity_id = pa.KLCity_id
				left join v_KLTown pklt  on pklt.KLTown_id = pa.KLTown_id
				left join v_KLStreet pkls  on pkls.KLStreet_id = pa.KLStreet_id
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
	 * Сохраняет карту диспансеризации
	 */
	function saveEvnPLDispOrp($data)
	{
		$savedData = array();
		if (!empty($data['EvnPLDispOrp_id'])) {
			$savedData = $this->getFirstRowFromQuery("
		  		select 
					EvnClass_id as \"EvnClass_id\",
					EvnClass_Name as \"EvnClass_Name\",
					EvnPLDispOrp_id as \"EvnPLDispOrp_id\",
					EvnPLDispOrp_setDate as \"EvnPLDispOrp_setDate\",
					EvnPLDispOrp_setTime as \"EvnPLDispOrp_setTime\",
					EvnPLDispOrp_didDate as \"EvnPLDispOrp_didDate\",
					EvnPLDispOrp_didTime as \"EvnPLDispOrp_didTime\",
					EvnPLDispOrp_disDate as \"EvnPLDispOrp_disDate\",
					EvnPLDispOrp_disTime as \"EvnPLDispOrp_disTime\",
					EvnPLDispOrp_pid as \"EvnPLDispOrp_pid\",
					EvnPLDispOrp_rid as \"EvnPLDispOrp_rid\",
					pmUser_updID as \"pmUser_updID\",
					Lpu_id as \"Lpu_id\",
					Person_id as \"Person_id\",
					Server_id as \"Server_id\",
					Morbus_id as \"Morbus_id\",
					EvnPLDispOrp_IsSigned as \"EvnPLDispOrp_IsSigned\",
					pmUser_signID as \"pmUser_signID\",
					EvnPLDispOrp_signDT as \"EvnPLDispOrp_signDT\",
					PersonEvn_id as \"PersonEvn_id\",
					EvnPLDispOrp_IsArchive as \"EvnPLDispOrp_IsArchive\",
					EvnPLDispOrp_Guid as \"EvnPLDispOrp_Guid\",
					EvnPLDispOrp_IndexMinusOne as \"EvnPLDispOrp_IndexMinusOne\",
					EvnStatus_id as \"EvnStatus_id\",
					EvnPLDispOrp_setDT as \"EvnPLDispOrp_setDT\",
					EvnPLDispOrp_statusDate as \"EvnPLDispOrp_statusDate\",
					EvnPLDispOrp_IsTransit as \"EvnPLDispOrp_IsTransit\",
					EvnPLDispOrp_VizitCount as \"EvnPLDispOrp_VizitCount\",
					EvnPLDispOrp_disDT as \"EvnPLDispOrp_disDT\",
					EvnPLDispOrp_IsFinish as \"EvnPLDispOrp_IsFinish\",
					Person_Age as \"Person_Age\",
					EvnPLDispOrp_didDT as \"EvnPLDispOrp_didDT\",
					EvnPLDispOrp_isMseDirected as \"EvnPLDispOrp_isMseDirected\",
					AttachType_id as \"AttachType_id\",
					Lpu_aid as \"Lpu_aid\",
					EvnPLDispOrp_insDT as \"EvnPLDispOrp_insDT\",
					EvnPLDispOrp_IsInReg as \"EvnPLDispOrp_IsInReg\",
					EvnPLDispOrp_consDT as \"EvnPLDispOrp_consDT\",
					DispClass_id as \"DispClass_id\",
					EvnPLDispOrp_updDT as \"EvnPLDispOrp_updDT\",
					EvnPLDispOrp_fid as \"EvnPLDispOrp_fid\",
					EvnPLDispOrp_IsMobile as \"EvnPLDispOrp_IsMobile\",
					EvnPLDispOrp_Index as \"EvnPLDispOrp_Index\",
					Lpu_mid as \"Lpu_mid\",
					EvnPLDispOrp_Count as \"EvnPLDispOrp_Count\",
					EvnPLDispOrp_IsPaid as \"EvnPLDispOrp_IsPaid\",
					EvnPLDispOrp_IsRefusal as \"EvnPLDispOrp_IsRefusal\",
					pmUser_insID as \"pmUser_insID\",
					EvnPLDispOrp_IndexRep as \"EvnPLDispOrp_IndexRep\",
					EvnPLDispOrp_IndexRepInReg as \"EvnPLDispOrp_IndexRepInReg\",
					PayType_id as \"PayType_id\",
					Lpu_CodeSMO as \"Lpu_CodeSMO\",
					EvnPLDispOrp_Percent as \"EvnPLDispOrp_Percent\",
					MedStaffFact_id as \"MedStaffFact_id\",
					EvnPLDispOrp_IsOutLpu as \"EvnPLDispOrp_IsOutLpu\",
					EvnPLDispOrp_IsSuspectZNO as \"EvnPLDispOrp_IsSuspectZNO\",
					Diag_spid as \"Diag_spid\",
					EvnDirection_aid as \"EvnDirection_aid\",
					EvnPLDispOrp_IsInRegZNO as \"EvnPLDispOrp_IsInRegZNO\",
					Registry_sid as \"Registry_sid\",
					EvnPLDispOrp_IsNewOrder as \"EvnPLDispOrp_IsNewOrder\",
					ChildStatusType_id as \"ChildStatusType_id\",
					EvnPLDispOrp_IsTwoStage as \"EvnPLDispOrp_IsTwoStage\"
		  		from v_EvnPLDispOrp
		  		where EvnPLDispOrp_id = :EvnPLDispOrp_id
				limit 1
			", $data, true);
			if ($savedData === false) {
				return array('Error_Msg' => 'Ошибка при получении данных карты');
			}
		}

		$this->load->model('EvnUsluga_model');
		// Если случай закрыт и задана дата справки, то сохраняем справку.
		if (!empty($data['EvnPLDispOrp_id']) && !empty($data['EvnPLDispOrp_IsFinish']) && $data['EvnPLDispOrp_IsFinish'] == 2 && !empty($data['EvnCostPrint_setDT'])) {
			// сохраняем справку
			$this->load->model('CostPrint_model');
			$this->CostPrint_model->saveEvnCostPrint(array(
				'Evn_id' => $data['EvnPLDispOrp_id'],
				'CostPrint_IsNoPrint' => $data['EvnCostPrint_IsNoPrint'],
				'CostPrint_setDT' => $data['EvnCostPrint_setDT'],
				'pmUser_id' => $data['pmUser_id']
			));
		}

		// Если случай закрыт, то проверяем правильность адреса
		if (getRegionNick() != 'ufa' && !empty($data['EvnPLDispOrp_IsFinish']) && $data['EvnPLDispOrp_IsFinish'] == 2) {
			$checkResult = $this->checkPersonAddress($data);

			If (!empty($checkResult)) {
				return array('Error_Msg' => $checkResult);
			}
		}

		// Проверяем что человек находится в регистре по ДД и у него заведены все необходимые данные
		$checkResult = $this->checkPersonData($data);

		If ($checkResult[0] != "Ok") {
			return $checkResult;
		}

		if (getRegionNick() == 'krasnoyarsk') {
			if (!$data['checkAttributeforLpuSection']) {
				$checkDate = $this->checkAttributeforLpuSection(array(
					'EvnPLDispOrp_id' => $data['EvnPLDispOrp_id']
				));

				If ($checkDate[0] != "Ok") {
					return array('Error_Msg' => 'YesNo', 'Alert_Msg' => $checkDate, 'Error_Code' => 110);
				}

			}else if($data['checkAttributeforLpuSection']==2) {
				$data['EvnPLDispOrp_IsMobile'] = 'on';
			}
		}
		
		$procedure = 'p_EvnPLDispOrp_ins';
		$data['EvnPLDispOrp_setDT'] = date('Y-m-d');
		$data['EvnPLDispOrp_disDT'] = null;
		$data['EvnPLDispOrp_didDT'] = null;
		$data['EvnPLDispOrp_VizitCount'] = 0;

		if (!empty($data['EvnPLDispOrp_id'])) {
			// достаем дату начала, дату окончания, количество посещений
			$query = "
				select
					to_char(cast(EvnPLDispOrp_setDT as timestamp),'yyyymmdd') as \"EvnPLDispOrp_setDT\",
					to_char(cast(EvnPLDispOrp_disDT as timestamp),'yyyymmdd') as \"EvnPLDispOrp_disDT\",
					to_char(cast(EvnPLDispOrp_didDT as timestamp),'yyyymmdd') as \"EvnPLDispOrp_didDT\",
					EvnPLDispOrp_VizitCount as \"EvnPLDispOrp_VizitCount\"
				from
					v_EvnPLDispOrp
				where EvnPLDispOrp_id = ?
			";
			$result = $this->db->query($query, array($data['EvnPLDispOrp_id']));
			if (is_object($result)) {
				$response = $result->result('array');
				if (is_array($response) && count($response) > 0) {
					$data['EvnPLDispOrp_setDT'] = $response[0]['EvnPLDispOrp_setDT'];
					$data['EvnPLDispOrp_disDT'] = $response[0]['EvnPLDispOrp_disDT'];
					$data['EvnPLDispOrp_didDT'] = $response[0]['EvnPLDispOrp_didDT'];
					$data['EvnPLDispOrp_VizitCount'] = $response[0]['EvnPLDispOrp_VizitCount'];
				}
			}

			$procedure = 'p_EvnPLDispOrp_upd';
		}

		$data['EvnPLDispOrp_setDT'] = $data['EvnPLDispOrp_setDate'];
		if ($data['EvnPLDispOrp_IsMobile']) {
			$data['EvnPLDispOrp_IsMobile'] = 2;
		} else {
			$data['EvnPLDispOrp_IsMobile'] = 1;
		}
		if ($data['EvnPLDispOrp_IsOutLpu']) {
			$data['EvnPLDispOrp_IsOutLpu'] = 2;
		} else {
			$data['EvnPLDispOrp_IsOutLpu'] = 1;
		}

		// Проверяем что нет профосмотра
		$query = "
			SELECT 
			       epldti.EvnPLDispTeenInspection_id as \"EvnPLDispTeenInspection_id\"
			FROM
				v_EvnPLDispTeenInspection epldti
				inner join v_PersonState ps on ps.Person_id = epldti.Person_id
			WHERE
				epldti.Person_id = :Person_id
				and EXTRACT(YEAR FROM epldti.EvnPLDispTeenInspection_setDT) = EXTRACT(YEAR FROM :EvnPLDispOrp_setDT::timestamp)
				and epldti.DispClass_id = 10 -- профилактический осмотр
				and epldti.EvnPLDispTeenInspection_IsFinish = 2 -- закрытый
				and dbo.Age2(ps.Person_BirthDay, :EvnPLDispOrp_setDT) >= 3 -- 3 лет и старше
			LIMIT 1
		";
		$checkResult = $this->queryResult($query, array(
			'Person_id' => $data['Person_id'],
			'EvnPLDispOrp_setDT' => $data['EvnPLDispOrp_setDT']
		));
		if (!empty($checkResult[0]['EvnPLDispTeenInspection_id'])) {
			return array('Error_Msg' => 'На выбранного пациента в выбранном году уже сохранена карта профилактического осмотра несовершеннолетнего.');
		}

		$data['MedStaffFact_id'] = !empty($data['session']['CurARM']['MedStaffFact_id']) ? $data['session']['CurARM']['MedStaffFact_id'] : null;

		$this->checkZnoDirection($data, 'EvnPLDispOrp');

		if (empty($data['EvnPLDispOrp_id'])) {
			$cte = "
				select
					cast(null as bigint) as aid, 
					cast(null as bigint) as ref
			";
		} else {
			$cte = "
				select
					EvnPLDispOrp_IsRefusal as ref,
					EvnDirection_aid as aid
				from v_EvnPLDispOrp
				where EvnPLDispOrp_id = :EvnPLDispOrp_id
				limit 1
			";
		}
		$query = "
			with mv as (
				{$cte}
			)
			select
				EvnPLDispOrp_id as \"EvnPLDispOrp_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				EvnPLDispOrp_id := :EvnPLDispOrp_id,
				MedStaffFact_id := :MedStaffFact_id,
				EvnPLDispOrp_IndexRep := :EvnPLDispOrp_IndexRep,
				EvnPLDispOrp_IndexRepInReg := :EvnPLDispOrp_IndexRepInReg,
				EvnPLDispOrp_fid := :EvnPLDispOrp_fid,
				Lpu_id := :Lpu_id,
				Server_id := :Server_id,
				PersonEvn_id := :PersonEvn_id,
				EvnPLDispOrp_setDT := :EvnPLDispOrp_setDT,
				EvnPLDispOrp_disDT := :EvnPLDispOrp_disDT,
				EvnPLDispOrp_didDT := :EvnPLDispOrp_didDT,
				EvnPLDispOrp_VizitCount := :EvnPLDispOrp_VizitCount,
				EvnPLDispOrp_IsFinish := :EvnPLDispOrp_IsFinish,
				EvnPLDispOrp_IsTwoStage := :EvnPLDispOrp_IsTwoStage,
				ChildStatusType_id := :ChildStatusType_id,
				AttachType_id := 2, -- доп. диспансеризация
				DispClass_id := :DispClass_id, -- ддс
				PayType_id := :PayType_id,
				EvnPLDispOrp_consDT := :EvnPLDispOrp_consDate,
				EvnPLDispOrp_IsMobile := :EvnPLDispOrp_IsMobile, 
				EvnPLDispOrp_IsOutLpu := :EvnPLDispOrp_IsOutLpu, 
				Lpu_mid := :Lpu_mid,
				EvnPLDispOrp_IsRefusal := (select ref from mv),
				EvnDirection_aid := (select aid from mv),
				EvnPLDispOrp_IsSuspectZNO := :EvnPLDispOrp_IsSuspectZNO,
				Diag_spid := :Diag_spid,
				pmUser_id := :pmUser_id
			)
		";
		$result = $this->db->query($query, $data);

		if (!is_object($result)) {
			return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение талона по ДД)');
		}

		$response = $result->result('array');

		if (!is_array($response) || count($response) == 0) {
			return false;
		} else if ($response[0]['Error_Msg']) {
			return $response;
		}

		if (!isset($data['EvnPLDispOrp_id'])) {
			$data['EvnPLDispOrp_id'] = $response[0]['EvnPLDispOrp_id'];
		}

		// Ищем AssessmentHealth связанный с EvnPLDispOrp_id, если нет его то добавляем новый, иначе обновляем
		$data['AssessmentHealth_id'] = NULL;
		$query = "
			select 
			       AssessmentHealth_id as \"AssessmentHealth_id\" 
			from v_AssessmentHealth 
			where EvnPLDisp_id = :EvnPLDispOrp_id
			limit 1
		";
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				$data['AssessmentHealth_id'] = $resp[0]['AssessmentHealth_id'];
			}
		}

		// запихивание чекбоксов в YesNo
		if ($data['AssessmentHealth_IsRegular']) {
			$data['AssessmentHealth_IsRegular'] = 2;
		} else {
			$data['AssessmentHealth_IsRegular'] = 1;
		}
		if ($data['AssessmentHealth_IsIrregular']) {
			$data['AssessmentHealth_IsIrregular'] = 2;
		} else {
			$data['AssessmentHealth_IsIrregular'] = 1;
		}
		if ($data['AssessmentHealth_IsAbundant']) {
			$data['AssessmentHealth_IsAbundant'] = 2;
		} else {
			$data['AssessmentHealth_IsAbundant'] = 1;
		}
		if ($data['AssessmentHealth_IsModerate']) {
			$data['AssessmentHealth_IsModerate'] = 2;
		} else {
			$data['AssessmentHealth_IsModerate'] = 1;
		}
		if ($data['AssessmentHealth_IsScanty']) {
			$data['AssessmentHealth_IsScanty'] = 2;
		} else {
			$data['AssessmentHealth_IsScanty'] = 1;
		}
		if ($data['AssessmentHealth_IsPainful']) {
			$data['AssessmentHealth_IsPainful'] = 2;
		} else {
			$data['AssessmentHealth_IsPainful'] = 1;
		}
		if ($data['AssessmentHealth_IsPainless']) {
			$data['AssessmentHealth_IsPainless'] = 2;
		} else {
			$data['AssessmentHealth_IsPainless'] = 1;
		}

		if ($data['AssessmentHealth_IsMental']) {
			$data['AssessmentHealth_IsMental'] = 2;
		} else {
			$data['AssessmentHealth_IsMental'] = 1;
		}
		if ($data['AssessmentHealth_IsOtherPsych']) {
			$data['AssessmentHealth_IsOtherPsych'] = 2;
		} else {
			$data['AssessmentHealth_IsOtherPsych'] = 1;
		}
		if ($data['AssessmentHealth_IsLanguage']) {
			$data['AssessmentHealth_IsLanguage'] = 2;
		} else {
			$data['AssessmentHealth_IsLanguage'] = 1;
		}
		if ($data['AssessmentHealth_IsVestibular']) {
			$data['AssessmentHealth_IsVestibular'] = 2;
		} else {
			$data['AssessmentHealth_IsVestibular'] = 1;
		}
		if ($data['AssessmentHealth_IsVisual']) {
			$data['AssessmentHealth_IsVisual'] = 2;
		} else {
			$data['AssessmentHealth_IsVisual'] = 1;
		}
		if ($data['AssessmentHealth_IsMeals']) {
			$data['AssessmentHealth_IsMeals'] = 2;
		} else {
			$data['AssessmentHealth_IsMeals'] = 1;
		}
		if ($data['AssessmentHealth_IsMotor']) {
			$data['AssessmentHealth_IsMotor'] = 2;
		} else {
			$data['AssessmentHealth_IsMotor'] = 1;
		}
		if ($data['AssessmentHealth_IsDeform']) {
			$data['AssessmentHealth_IsDeform'] = 2;
		} else {
			$data['AssessmentHealth_IsDeform'] = 1;
		}
		if ($data['AssessmentHealth_IsGeneral']) {
			$data['AssessmentHealth_IsGeneral'] = 2;
		} else {
			$data['AssessmentHealth_IsGeneral'] = 1;
		}

		$this->load->model('AssessmentHealth_model');
		$data['scenario'] = swModel::SCENARIO_DO_SAVE;
		$data['EvnPLDisp_id'] = $data['EvnPLDispOrp_id'];
		$response = $this->AssessmentHealth_model->doSave($data);

		// Назначения
		$this->load->model('DispAppoint_model');
		foreach ($data['DispAppointData'] as $key => $record) {
			if ($record['RecordStatus_Code'] == 3) {// удаление назначений
				$response = $this->DispAppoint_model->deleteDispAppoint(array(
					'DispAppoint_id' => $record['DispAppoint_id'],
					'pmUser_id' => $data['pmUser_id']
				));

				if (!is_array($response) || count($response) == 0) {
					return array(0 => array('Error_Msg' => 'Ошибка при удалении лабораторного исследования'));
				} else if (strlen($response[0]['Error_Msg']) > 0) {
					return $response;
				}
			} else {
				$params = array(
					'DispAppoint_id' => (!empty($record['DispAppoint_id']) && $record['DispAppoint_id'] > 0) ? $record['DispAppoint_id'] : null,
					'EvnPLDisp_id' => $data['EvnPLDisp_id'],
					'DispAppointType_id' => !empty($record['DispAppointType_id']) ? $record['DispAppointType_id'] : null,
					'MedSpecOms_id' => !empty($record['MedSpecOms_id']) ? $record['MedSpecOms_id'] : null,
					'ExaminationType_id' => !empty($record['ExaminationType_id']) ? $record['ExaminationType_id'] : null,
					'LpuSectionProfile_id' => !empty($record['LpuSectionProfile_id']) ? $record['LpuSectionProfile_id'] : null,
					'LpuSectionBedProfile_id' => !empty($record['LpuSectionBedProfile_id']) ? $record['LpuSectionBedProfile_id'] : null,
					'pmUser_id' => $data['pmUser_id']
				);

				$response = $this->DispAppoint_model->saveDispAppoint($params);

				if (!is_array($response) || count($response) == 0) {
					return array(0 => array('Error_Msg' => 'Ошибка при удалении лабораторного исследования'));
				} else if (strlen($response[0]['Error_Msg']) > 0) {
					return $response;
				}
			}
		}

		// Грид "Диагнозы и результаты"
		foreach ($data['EvnDiagAndRecomendation'] as $record) {
			// получаем MedCare_id для MedCareType_id = 1
			$json = json_decode($record['FormDataJSON'], true);
			$MedCare_id = $this->getMedCareForEvnVizitDispOrp($record['EvnVizitDispOrp_id'], 1);
			$this->saveMedCare(array(
				'MedCare_id' => $MedCare_id,
				'EvnVizitDispOrp_id' => $record['EvnVizitDispOrp_id'],
				'ConditMedCareType_nid' => empty($json['ConditMedCareType1_nid']) ? null : $json['ConditMedCareType1_nid'],
				'PlaceMedCareType_nid' => empty($json['PlaceMedCareType1_nid']) ? null : $json['PlaceMedCareType1_nid'],
				'ConditMedCareType_id' => empty($json['ConditMedCareType1_id']) ? null : $json['ConditMedCareType1_id'],
				'PlaceMedCareType_id' => empty($json['PlaceMedCareType1_id']) ? null : $json['PlaceMedCareType1_id'],
				'LackMedCareType_id' => empty($json['LackMedCareType1_id']) ? null : $json['LackMedCareType1_id'],
				'MedCareType_id' => 1,
				'pmUser_id' => $data['pmUser_id']
			));

			// получаем MedCare_id для MedCareType_id = 2
			$MedCare_id = $this->getMedCareForEvnVizitDispOrp($record['EvnVizitDispOrp_id'], 2);
			$this->saveMedCare(array(
				'MedCare_id' => $MedCare_id,
				'EvnVizitDispOrp_id' => $record['EvnVizitDispOrp_id'],
				'ConditMedCareType_nid' => empty($json['ConditMedCareType2_nid']) ? null : $json['ConditMedCareType2_nid'],
				'PlaceMedCareType_nid' => empty($json['PlaceMedCareType2_nid']) ? null : $json['PlaceMedCareType2_nid'],
				'ConditMedCareType_id' => empty($json['ConditMedCareType2_id']) ? null : $json['ConditMedCareType2_id'],
				'PlaceMedCareType_id' => empty($json['PlaceMedCareType2_id']) ? null : $json['PlaceMedCareType2_id'],
				'LackMedCareType_id' => empty($json['LackMedCareType2_id']) ? null : $json['LackMedCareType2_id'],
				'MedCareType_id' => 2,
				'pmUser_id' => $data['pmUser_id']
			));

			// получаем MedCare_id для MedCareType_id = 3
			$MedCare_id = $this->getMedCareForEvnVizitDispOrp($record['EvnVizitDispOrp_id'], 3);
			$this->saveMedCare(array(
				'MedCare_id' => $MedCare_id,
				'EvnVizitDispOrp_id' => $record['EvnVizitDispOrp_id'],
				'ConditMedCareType_nid' => empty($json['ConditMedCareType3_nid']) ? null : $json['ConditMedCareType3_nid'],
				'PlaceMedCareType_nid' => empty($json['PlaceMedCareType3_nid']) ? null : $json['PlaceMedCareType3_nid'],
				'ConditMedCareType_id' => empty($json['ConditMedCareType3_id']) ? null : $json['ConditMedCareType3_id'],
				'PlaceMedCareType_id' => empty($json['PlaceMedCareType3_id']) ? null : $json['PlaceMedCareType3_id'],
				'LackMedCareType_id' => empty($json['LackMedCareType3_id']) ? null : $json['LackMedCareType3_id'],
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
				'DispSurveilType_id' => empty($json['DispSurveilType_id']) ? null : $json['DispSurveilType_id'],
				'EvnVizitDisp_IsVMP' => empty($json['EvnVizitDisp_IsVMP']) ? null : $json['EvnVizitDisp_IsVMP'],
				'EvnVizitDisp_IsFirstTime' => empty($json['EvnVizitDisp_IsFirstTime']) ? null : $json['EvnVizitDisp_IsFirstTime'],
				'EvnVizitDisp_id' => $record['EvnVizitDispOrp_id'],
			));
		}
		$this->load->model('EvnUsluga_model');
		// Лабораторные исследования
		foreach ($data['EvnUslugaDispOrp'] as $key => $record) {
			if ($record['Record_Status'] == 3) {// удаление исследований
				$query = "
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from p_EvnUslugaDispOrp_del(
						EvnUslugaDispOrp_id := ?,
						pmUser_id := ?
					)
				";
				$result = $this->db->query($query, array($record['EvnUslugaDispOrp_id'], $data['pmUser_id']));

				if (!is_object($result)) {
					return array(0 => array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление лабораторного исследования)'));
				}

				$response = $result->result('array');

				if (!is_array($response) || count($response) == 0) {
					return array(0 => array('Error_Msg' => 'Ошибка при удалении лабораторного исследования'));
				} else if (strlen($response[0]['Error_Msg']) > 0) {
					return $response;
				}
			} else {
				if ($record['Record_Status'] == 0) {
					$procedure = 'p_EvnUslugaDispOrp_ins';
				} else {
					$procedure = 'p_EvnUslugaDispOrp_upd';
				}

				// проверяем, есть ли уже такое исследование
				$query = "
					select
						EvnUslugaDispOrp_id as \"EvnUslugaDispOrp_id\"
					from
						v_EvnUslugaDispOrp
					where
						EvnUslugaDispOrp_pid = ?
						and UslugaComplex_id = ?
						and EvnUslugaDispOrp_id <> coalesce(cast(? as bigint), 0)
						and coalesce(EvnUslugaDispOrp_IsVizitCode, 1) = 1
					limit 1
				";
				$result = $this->db->query(
					$query,
					array(
						$data['EvnPLDispOrp_id'],
						$record['UslugaComplex_id'],
						$record['Record_Status'] == 0 ? null : $record['EvnUslugaDispOrp_id']
					)
				);
				if (!is_object($result)) {
					return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение исследования)'));
				}
				$response = $result->result('array');
				if (is_array($response) && count($response) > 0) {
					return array(array('Error_Msg' => 'Обнаружено дублирование исследований, это недопустимо.'));
				}
				// окончание проверки

				$setDT = $record['EvnUslugaDispOrp_setDate'];
				if (!empty($record['EvnUslugaDispOrp_setTime'])) {
					$setDT .= ' ' . $record['EvnUslugaDispOrp_setTime'];
				}
				$disDT = null;
				if (!empty($record['EvnUslugaDispOrp_disDate'])) {
					$disDT = $record['EvnUslugaDispOrp_disDate'];

					if (!empty($record['EvnUslugaDispOrp_disTime'])) {
						$disDT .= ' ' . $record['EvnUslugaDispOrp_disTime'];
					}
				}

				if ($record['LpuSection_id'] == '')
					$record['LpuSection_id'] = Null;
				$query = "
					select
						EvnUslugaDispOrp_id as \"EvnUslugaDispOrp_id\",
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from " . $procedure . "(
						EvnUslugaDispOrp_id := :EvnUslugaDispOrp_id,
						EvnUslugaDispOrp_pid := :EvnUslugaDispOrp_pid,
						Lpu_id := :Lpu_id,
						Server_id := :Server_id,
						PersonEvn_id := :PersonEvn_id,
						EvnUslugaDispOrp_setDT := :EvnUslugaDispOrp_setDT,
						EvnUslugaDispOrp_disDT := :EvnUslugaDispOrp_disDT,
						EvnUslugaDispOrp_didDT := :EvnUslugaDispOrp_didDT,
						LpuSection_uid := :LpuSection_uid,
						MedSpecOms_id := :MedSpecOms_id,
						LpuSectionProfile_id := :LpuSectionProfile_id,
						MedPersonal_id := :MedPersonal_id,
						MedStaffFact_id := :MedStaffFact_id,
						UslugaComplex_id := :UslugaComplex_id,
						PayType_id := (select PayType_id from v_PayType where PayType_SysNick = 'dopdisp' limit 1),
						UslugaPlace_id := 1,
						Lpu_uid := :Lpu_uid,
						EvnUslugaDispOrp_Kolvo := 1,
						ExaminationPlace_id := :ExaminationPlace_id,
						EvnPrescrTimetable_id := null,
						EvnPrescr_id := null,
						EvnUslugaDispOrp_Result := :EvnUslugaDispOrp_Result,
						pmUser_id := :pmUser_id
					);
				";

				$result = $this->db->query($query, array(
					'EvnUslugaDispOrp_id' => (!empty($record['Record_Status']) ? $record['EvnUslugaDispOrp_id'] : NULL),
					'EvnUslugaDispOrp_pid' => $data['EvnPLDispOrp_id'],
					'Lpu_id' => $data['Lpu_id'],
					'MedSpecOms_id' => (!empty($record['MedSpecOms_id']) ? $record['MedSpecOms_id'] : NULL),
					'LpuSectionProfile_id' => (!empty($record['LpuSectionProfile_id']) ? $record['LpuSectionProfile_id'] : NULL),
					'Server_id' => $data['Server_id'],
					'PersonEvn_id' => $data['PersonEvn_id'],
					'EvnUslugaDispOrp_setDT' => $setDT,
					'EvnUslugaDispOrp_disDT' => $disDT,
					'EvnUslugaDispOrp_didDT' => (!empty($record['EvnUslugaDispOrp_didDate']) ? $record['EvnUslugaDispOrp_didDate'] : NULL),
					'LpuSection_uid' => (!empty($record['LpuSection_id']) ? $record['LpuSection_id'] : NULL),
					'MedPersonal_id' => (!empty($record['MedPersonal_id']) ? $record['MedPersonal_id'] : NULL),
					'MedStaffFact_id' => (!empty($record['MedStaffFact_id']) ? $record['MedStaffFact_id'] : NULL),
					'UslugaComplex_id' => $record['UslugaComplex_id'],
					'Lpu_uid' => (!empty($record['Lpu_uid']) ? $record['Lpu_uid'] : NULL),
					'ExaminationPlace_id' => (!empty($record['ExaminationPlace_id']) ? $record['ExaminationPlace_id'] : NULL),
					'EvnUslugaDispOrp_Result' => (!empty($record['EvnUslugaDispOrp_Result']) ? $record['EvnUslugaDispOrp_Result'] : NULL),
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

				$record['EvnUslugaDispOrp_id'] = $response[0]['EvnUslugaDispOrp_id'];
			}
		}

		// Сохраняем скрытую услугу для Бурятии, если случай закончен
		// @task https://redmine.swan.perm.ru/issues/52175
		// Добавлен Крым
		// @task https://redmine.swan.perm.ru/issues/88196
		if (
			in_array($data['session']['region']['nick'], array('buryatiya', 'krym')) && !empty($data['EvnPLDispOrp_id'])
			&& !empty($data['EvnPLDispOrp_IsFinish']) && $data['EvnPLDispOrp_IsFinish'] == 2
		) {
			// Ищем существующую услугу
			$query = "
				select
					EvnUslugaDispOrp_id as \"EvnUslugaDispOrp_id\",
					UslugaComplex_id as \"UslugaComplex_id\",
					PayType_id as \"PayType_id\",
					to_char(EvnUslugaDispOrp_setDT, 'dd.mm.yyyy') as \"EvnUslugaDispOrp_setDate\"
				from v_EvnUslugaDispOrp
				where EvnUslugaDispOrp_pid = :EvnUslugaDispOrp_pid
					and EvnUslugaDispOrp_IsVizitCode = 2
				limit 1
			";
			$result = $this->db->query($query, array(
				'EvnUslugaDispOrp_pid' => $data['EvnPLDispOrp_id']
			));

			if (!is_object($result)) {
				return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (поиск услуги)');
			}

			$response = $result->result('array');

			if (is_array($response) && count($response) > 0) {
				$uslugaData = $response[0];
			} else {
				$uslugaData = array();
			}

			$data['ageDate'] = substr($data['EvnPLDispOrp_setDT'], 0, 4) . '-12-31';

			$filter = "";
			if (getRegionNick() == 'buryatiya') {
				$filter .= "
					and (
						not (UslugaSurveyLink_From = 0 and UslugaSurveyLink_To = 1 and DispClass_id IN (3,7))
						or exists(
							select
								eudo.EvnUslugaDispOrp_id
							from
								v_EvnUslugaDispOrp eudo
								inner join v_UslugaComplex uc on uc.UslugaComplex_id = eudo.UslugaComplex_id
							where
								eudo.EvnUslugaDispOrp_pid = :EvnPLDispOrp_id and
								uc.UslugaComplex_Code IN ('004043', '161204')
							limit 1
						)
					)				
				";
			}

			// Определяем UslugaComplex_id через $schema.UslugaSurveyLink
			$query = "
				with mv as (
					select
						coalesce(Sex_id, 3) as sex_id,
						dbo.Age2(Person_BirthDay, coalesce(:ageDate, dbo.tzGetDate())) as age
					from v_PersonState ps
					where ps.Person_id = :Person_id
					limit 1
				)

				select
					USL.UslugaComplex_id as \"UslugaComplex_id\"
				from {$data['session']['region']['schema']}.v_UslugaSurveyLink USL
				where
					USL.DispClass_id = :DispClass_id
					and coalesce(USL.Sex_id, (select sex_id from mv)) = (select sex_id from mv)
					and (select age from mv) between coalesce(USL.UslugaSurveyLink_From, 0) and coalesce(USL.UslugaSurveyLink_To, 999)
					and coalesce(USL.UslugaSurveyLink_IsDel, 1) = 1
					and (USL.UslugaSurveyLink_begDate is null or USL.UslugaSurveyLink_begDate <= :EvnPLDispOrp_setDT)
					and (USL.UslugaSurveyLink_endDate is null or USL.UslugaSurveyLink_endDate >= :EvnPLDispOrp_setDT)
					{$filter}
				limit 1
			";
			$result = $this->db->query($query, array(
				'DispClass_id' => $data['DispClass_id'],
				'EvnPLDispOrp_id' => $data['EvnPLDispOrp_id'],
				'EvnPLDispOrp_setDT' => $data['EvnPLDispOrp_setDT'],
				'Person_id' => $data['Person_id'],
				'ageDate' => $data['ageDate'],
			));

			if (!is_object($result)) {
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
				SELECT 
				    evnuslugadisporp_id as \"EvnUslugaDispOrp_id\",
					error_code as \"Error_Code\",
					error_message as \"Error_Msg\"
				FROM p_EvnUslugaDispOrp_" . (!empty($uslugaData['EvnUslugaDispOrp_id']) ? "upd" : "ins") . " (
					EvnUslugaDispOrp_id := :EvnUslugaDispOrp_id,
					EvnUslugaDispOrp_pid := :EvnUslugaDispOrp_pid,
					UslugaComplex_id := :UslugaComplex_id,
					EvnUslugaDispOrp_setDT := :EvnUslugaDispOrp_setDT,
					EvnUslugaDispOrp_IsVizitCode := 2,
					Lpu_id := :Lpu_id,
					Server_id := :Server_id,
					PersonEvn_id := :PersonEvn_id,
					PayType_id := (select PayType_id from v_PayType where PayType_SysNick = 'dopdisp' LIMIT 1),
					UslugaPlace_id := 1,
					pmUser_id := :pmUser_id
				);
				";
				$result = $this->db->query($query, array(
					'EvnUslugaDispOrp_id' => (!empty($uslugaData['EvnUslugaDispOrp_id']) ? $uslugaData['EvnUslugaDispOrp_id'] : null),
					'EvnUslugaDispOrp_pid' => $data['EvnPLDispOrp_id'],
					'UslugaComplex_id' => $UslugaComplex_id,
					'EvnUslugaDispOrp_setDT' => (!empty($data['EvnPLDispOrp_setDT']) ? $data['EvnPLDispOrp_setDT'] : null),
					'Lpu_id' => $data['Lpu_id'],
					'Server_id' => $data['Server_id'],
					'PersonEvn_id' => $data['PersonEvn_id'],
					'PayType_id' => (!empty($uslugaData['PayType_id']) ? $uslugaData['PayType_id'] : null),
					'pmUser_id' => $data['pmUser_id']
				));

				if (!is_object($result)) {
					return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление услуги) (' . __LINE__ . ')'));
				}

				$response = $result->result('array');

				if (!is_array($response) || count($response) == 0) {
					return array(array('Error_Msg' => 'Ошибка при удалении услуги (' . __LINE__ . ')'));
				} else if (!empty($response[0]['Error_Msg'])) {
					return array(array('Error_Msg' => $response[0]['Error_Msg'] . ' (' . __LINE__ . ')'));
				}
			} // Удаляем
			else if (!empty($uslugaData['EvnUslugaDispOrp_id'])) {
				$query = "
				SELECT 
					error_code as \"Error_Code\", 
            		error_message as \"Error_Msg\"
            	FROM p_EvnUslugaDispOrp_del (
            		EvnUslugaDispOrp_id := :EvnUslugaDispOrp_id,
					pmUser_id := :pmUser_id
            	);
				";
				$result = $this->db->query($query, array(
					'EvnUslugaDispOrp_id' => $uslugaData['EvnUslugaDispOrp_id'],
					'pmUser_id' => $data['pmUser_id']
				));

				if (!is_object($result)) {
					return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление услуги) (' . __LINE__ . ')'));
				}

				$response = $result->result('array');

				if (!is_array($response) || count($response) == 0) {
					return array(array('Error_Msg' => 'Ошибка при удалении услуги (' . __LINE__ . ')'));
				} else if (!empty($response[0]['Error_Msg'])) {
					return array(array('Error_Msg' => $response[0]['Error_Msg'] . ' (' . __LINE__ . ')'));
				}
			}
		}

		$justClosed = (
			!empty($data['EvnPLDispOrp_IsFinish']) && $data['EvnPLDispOrp_IsFinish'] == 2 && (
				empty($savedData) || $savedData['EvnPLDispOrp_IsFinish'] != 2
			)
		);

		if (getRegionNick() == 'penza' && (empty($savedData) || $justClosed)) {
			//Отправить человека в очередь на идентификацию
			$this->load->model('Person_model', 'pmodel');
			$this->pmodel->isAllowTransaction = false;
			$resTmp = $this->pmodel->addPersonRequestData(array(
				'Person_id' => $data['Person_id'],
				'Evn_id' => $data['EvnPLDispOrp_id'],
				'pmUser_id' => $data['pmUser_id'],
				'PersonRequestSourceType_id' => 3,
			));
			$this->pmodel->isAllowTransaction = true;
			if (!$this->isSuccessful($resTmp) && !in_array($resTmp[0]['Error_Code'], array(302, 303))) {
				return array('Error_Msg' => $resTmp[0]['Error_Msg']);
			}
		}

		return array(array('EvnPLDispOrp_id' => $data['EvnPLDispOrp_id'], 'Error_Msg' => ''));
	}

	/**
	 * Сохраняет карту диспансеризации
	 */
	function saveEvnPLDispOrpSec($data)
	{
		// begin проверки перенесённые с формы
		// получаем возраст пациента
		$resp_ps = $this->queryResult("
			select
				Age2(PS.Person_BirthDay, :EvnPLDispOrp_consDate) as \"Person_Age\",
				to_char(epldo.EvnPLDispOrp_setDate, 'yyyy-mm-dd hh24:mi') as \"EvnPLDispOrp_firSetDate\"
			from
				v_PersonState PS
				left join v_EvnPLDispOrp epldo on epldo.EvnPLDispOrp_id = :EvnPLDispOrp_fid
			where
				PS.Person_id = :Person_id
		", array(
			'EvnPLDispOrp_consDate' => $data['EvnPLDispOrp_consDate'],
			'EvnPLDispOrp_fid' => $data['EvnPLDispOrp_fid'],
			'Person_id' => $data['Person_id']
		));

		if (isset($resp_ps[0]['Person_Age'])) {
			$Person_Age = $resp_ps[0]['Person_Age'];
			$EvnPLDispOrp_firSetDate = $resp_ps[0]['EvnPLDispOrp_firSetDate'];
		} else {
			return array('Error_Msg' => 'Ошибка при определении возраста пациента');
		}

		$pedcodes = array('01090128');
		if (in_array(getRegionNick(), ['perm', 'krasnoyarsk', 'yaroslavl'])) {
			if ($data['EvnPLDispOrp_consDate'] >= '2018-01-01') {
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
						and stl.DispClass_id = 3
						and stl.SurveyTypeLink_begDate <= :EvnPLDispOrp_consDate
						and coalesce(stl.SurveyTypeLink_endDate, :EvnPLDispOrp_consDate) >=  :EvnPLDispOrp_consDate
				", array(
					'EvnPLDispOrp_consDate' => $data['EvnPLDispOrp_consDate']
				));
				foreach ($resp_uc as $one_uc) {
					$pedcodes[] = $one_uc['UslugaComplex_Code'];
				}
			} else {
				$pedcodes = array('B04.031.002', 'B04.031.004', 'B04.026.002'); // https://redmine.swan.perm.ru/issues/56948 добавил коды B04.031.004 и B04.026.002
			}
		} else if (getRegionNick() == 'ekb') {
			$pedcodes = array('B04.031.002');
		} else if (getRegionNick() == 'astra' || getRegionNick() == 'vologda') {
			$pedcodes = array('B04.031.004');
		} else if (getRegionNick() == 'pskov') {
			$pedcodes = array('B04.031.001');
		} else if (getRegionNick() == 'krym') {
			$pedcodes = array('B04.031.001');
		} else if (getRegionNick() == 'buryatiya') {
			$pedcodes = array('161014', '161078', '161150');
		}

		// Вытаскиваем минимальную и максимальную дату осмотра и дату осмотра врачом терапевтом
		$EvnVizitDispOrp_pedDate = null;
		$EvnVizitDispOrp_maxDate = null;
		$EvnVizitDispOrp_minDate = null;

		if (!empty($data['EvnPLDispOrp_id']) && (!is_array($data['EvnVizitDispOrp']) || count($data['EvnVizitDispOrp']) == 0)) {
			$data['EvnVizitDispOrp'] = $this->queryResult("
				select
					to_char(EVDO.EvnVizitDispOrp_setDT, 'yyyy-mm-dd hh24:mi') as \"EvnVizitDispOrp_setDate\",
					UC.UslugaComplex_Code as \"UslugaComplex_Code\",
				    1 as \"Record_Status\"
				from
					v_EvnVizitDispOrp EVDO
					inner join v_UslugaComplex UC on UC.UslugaComplex_id = EVDO.UslugaComplex_id 
				where
					EVDO.EvnVizitDispOrp_pid = :EvnPLDispOrp_id
			", $data);
		}

		if (!is_array($data['EvnVizitDispOrp'])) {
			$data['EvnVizitDispOrp'] = array();
		}

		foreach ($data['EvnVizitDispOrp'] as $record) {
			if ((int)$record['Record_Status'] == 3) continue;// #136178
			if (!empty($record['UslugaComplex_Code']) && in_array($record['UslugaComplex_Code'], $pedcodes)) {
				$EvnVizitDispOrp_pedDate = $record['EvnVizitDispOrp_setDate'];
			} else {
				if (empty($EvnVizitDispOrp_maxDate) || $EvnVizitDispOrp_maxDate < $record['EvnVizitDispOrp_setDate']) {
					$EvnVizitDispOrp_maxDate = $record['EvnVizitDispOrp_setDate'];
				}

				if (empty($EvnVizitDispOrp_minDate) || $EvnVizitDispOrp_minDate > $record['EvnVizitDispOrp_setDate']) {
					$EvnVizitDispOrp_minDate = $record['EvnVizitDispOrp_setDate'];
				}
			}
		}

		if (!in_array(getRegionNick(), ['kareliya', 'penza', 'pskov', 'adygeya']) && empty($EvnVizitDispOrp_pedDate) && (int)$data['EvnPLDispOrp_IsFinish'] == 2) {
			if (getRegionNick() == 'perm' && $data['EvnPLDispOrp_consDate'] >= '2018-01-01') {
				$Error_Msg = 'Карта должна содержать осмотр врача-педиатра либо осмотр врача общей практики';
			} else {
				$Error_Msg = 'Случай не может быть закончен, так как не сохранен осмотр врача-педиатра (ВОП) (' . implode(', ', $pedcodes) . ')';
			}
			return ['Error_Msg' => $Error_Msg];
		}


		// https://redmine.swan.perm.ru/issues/20485
		if (!empty($EvnVizitDispOrp_pedDate) && strtotime($EvnVizitDispOrp_pedDate) - 63 * 24 * 60 * 60 > strtotime($EvnPLDispOrp_firSetDate) && empty($data['ignoreOsmotrDlit'])) {
			if ($this->regionNick == 'krym') {
				return array('Error_Msg' => 'YesNo', 'Alert_Msg' => 'Длительность 1 и 2 этапов диспансеризации несовершеннолетнего не может быть больше 45 рабочих дней. Продолжить сохранение?', 'Error_Code' => 110);
			} else {
				return array('Error_Msg' => 'Длительность 1 и 2 этапов диспансеризации несовершеннолетнего не может быть больше 45 рабочих дней.');
			}
		}

		// https://redmine.swan.perm.ru/issues/20499
		if (!empty($EvnVizitDispOrp_pedDate) && $EvnVizitDispOrp_pedDate < $data['EvnPLDispOrp_consDate']) {
			return array('Error_Msg' => 'Дата осмотра врача-педиатра не может быть раньше, чем дата начала диспансеризации.');
		}

		// Вытаскиваем минимальную и максимальную дату услуги, а также дату проведения флюорографии
		$EvnUslugaDispOrp_maxDate = null;
		$EvnUslugaDispOrp_minDate = null;
		foreach ($data['EvnUslugaDispOrp'] as $record) {
			if (empty($EvnUslugaDispOrp_maxDate) || $EvnUslugaDispOrp_maxDate < $record['EvnUslugaDispOrp_setDate']) {
				$EvnUslugaDispOrp_maxDate = $record['EvnUslugaDispOrp_setDate'];
			}

			if (empty($EvnUslugaDispOrp_minDate) || $EvnUslugaDispOrp_minDate > $record['EvnUslugaDispOrp_setDate']) {
				$EvnUslugaDispOrp_minDate = $record['EvnUslugaDispOrp_setDate'];
			}
		}

		// Получаем максимальную дату осмотра/исследования
		$maxDate = null;
		$minDate = null;
		if (!empty($EvnVizitDispOrp_maxDate)) {
			$maxDate = $EvnVizitDispOrp_maxDate;
		}
		if (empty($maxDate) || (!empty($EvnUslugaDispOrp_maxDate) && $maxDate < $EvnUslugaDispOrp_maxDate)) {
			$maxDate = $EvnUslugaDispOrp_maxDate;
		}
		if (!empty($EvnVizitDispOrp_minDate)) {
			$minDate = $EvnVizitDispOrp_minDate;
		}
		if (empty($minDate) || (!empty($EvnUslugaDispOrp_minDate) && $minDate > $EvnUslugaDispOrp_minDate)) {
			$minDate = $EvnUslugaDispOrp_minDate;
		}

		// https://redmine.swan.perm.ru/issues/20485
		if (!empty($maxDate) && !empty($EvnVizitDispOrp_pedDate) && $maxDate > $EvnVizitDispOrp_pedDate) {
			return array('Error_Msg' => 'Дата осмотра/исследования по диспансеризации несовершеннолетнего не может быть больше даты осмотра врача-педиатра.');
		}

		// https://redmine.swan.perm.ru/issues/20485
		if (!empty($minDate) && !empty($EvnVizitDispOrp_pedDate) && DateTime::createFromFormat('Y-m-d', $minDate) < DateTime::createFromFormat('Y-m-d', $EvnVizitDispOrp_pedDate)->sub(new DateInterval('P' . ($Person_Age < 2 ? 1 : 3) . 'M'))) {
			return array('Error_Msg' => 'Дата любого исследования не может быть раньше, чем ' . ($Person_Age < 2 ? "1 месяц" : "3 месяца") . ' до даты осмотра врача-педиатра.');
		}

		// https://redmine.swan.perm.ru/issues/20001
		if (!empty($EvnVizitDispOrp_pedDate) && empty($data['HealthKind_id'])) {
			return array('Error_Msg' => 'Поле "Группа здоровья" обязательно для заполнения, если проведен осмотр врача-педиатра');
		}
		// end проверки перенесённые с формы

		$savedData = array();
		if (!empty($data['EvnPLDispOrp_id'])) {
			$savedData = $this->getFirstRowFromQuery("
		  		select 
					EvnClass_id as \"EvnClass_id\",
					EvnClass_Name as \"EvnClass_Name\",
					EvnPLDispOrp_id as \"EvnPLDispOrp_id\",
					EvnPLDispOrp_setDate as \"EvnPLDispOrp_setDate\",
					EvnPLDispOrp_setTime as \"EvnPLDispOrp_setTime\",
					EvnPLDispOrp_didDate as \"EvnPLDispOrp_didDate\",
					EvnPLDispOrp_didTime as \"EvnPLDispOrp_didTime\",
					EvnPLDispOrp_disDate as \"EvnPLDispOrp_disDate\",
					EvnPLDispOrp_disTime as \"EvnPLDispOrp_disTime\",
					EvnPLDispOrp_pid as \"EvnPLDispOrp_pid\",
					EvnPLDispOrp_rid as \"EvnPLDispOrp_rid\",
					pmUser_updID as \"pmUser_updID\",
					Lpu_id as \"Lpu_id\",
					Person_id as \"Person_id\",
					Server_id as \"Server_id\",
					Morbus_id as \"Morbus_id\",
					EvnPLDispOrp_IsSigned as \"EvnPLDispOrp_IsSigned\",
					pmUser_signID as \"pmUser_signID\",
					EvnPLDispOrp_signDT as \"EvnPLDispOrp_signDT\",
					PersonEvn_id as \"PersonEvn_id\",
					EvnPLDispOrp_IsArchive as \"EvnPLDispOrp_IsArchive\",
					EvnPLDispOrp_Guid as \"EvnPLDispOrp_Guid\",
					EvnPLDispOrp_IndexMinusOne as \"EvnPLDispOrp_IndexMinusOne\",
					EvnStatus_id as \"EvnStatus_id\",
					EvnPLDispOrp_setDT as \"EvnPLDispOrp_setDT\",
					EvnPLDispOrp_statusDate as \"EvnPLDispOrp_statusDate\",
					EvnPLDispOrp_IsTransit as \"EvnPLDispOrp_IsTransit\",
					EvnPLDispOrp_VizitCount as \"EvnPLDispOrp_VizitCount\",
					EvnPLDispOrp_disDT as \"EvnPLDispOrp_disDT\",
					EvnPLDispOrp_IsFinish as \"EvnPLDispOrp_IsFinish\",
					Person_Age as \"Person_Age\",
					EvnPLDispOrp_didDT as \"EvnPLDispOrp_didDT\",
					EvnPLDispOrp_isMseDirected as \"EvnPLDispOrp_isMseDirected\",
					AttachType_id as \"AttachType_id\",
					Lpu_aid as \"Lpu_aid\",
					EvnPLDispOrp_insDT as \"EvnPLDispOrp_insDT\",
					EvnPLDispOrp_IsInReg as \"EvnPLDispOrp_IsInReg\",
					EvnPLDispOrp_consDT as \"EvnPLDispOrp_consDT\",
					DispClass_id as \"DispClass_id\",
					EvnPLDispOrp_updDT as \"EvnPLDispOrp_updDT\",
					EvnPLDispOrp_fid as \"EvnPLDispOrp_fid\",
					EvnPLDispOrp_IsMobile as \"EvnPLDispOrp_IsMobile\",
					EvnPLDispOrp_Index as \"EvnPLDispOrp_Index\",
					Lpu_mid as \"Lpu_mid\",
					EvnPLDispOrp_Count as \"EvnPLDispOrp_Count\",
					EvnPLDispOrp_IsPaid as \"EvnPLDispOrp_IsPaid\",
					EvnPLDispOrp_IsRefusal as \"EvnPLDispOrp_IsRefusal\",
					pmUser_insID as \"pmUser_insID\",
					EvnPLDispOrp_IndexRep as \"EvnPLDispOrp_IndexRep\",
					EvnPLDispOrp_IndexRepInReg as \"EvnPLDispOrp_IndexRepInReg\",
					PayType_id as \"PayType_id\",
					Lpu_CodeSMO as \"Lpu_CodeSMO\",
					EvnPLDispOrp_Percent as \"EvnPLDispOrp_Percent\",
					MedStaffFact_id as \"MedStaffFact_id\",
					EvnPLDispOrp_IsOutLpu as \"EvnPLDispOrp_IsOutLpu\",
					EvnPLDispOrp_IsSuspectZNO as \"EvnPLDispOrp_IsSuspectZNO\",
					Diag_spid as \"Diag_spid\",
					EvnDirection_aid as \"EvnDirection_aid\",
					EvnPLDispOrp_IsInRegZNO as \"EvnPLDispOrp_IsInRegZNO\",
					Registry_sid as \"Registry_sid\",
					EvnPLDispOrp_IsNewOrder as \"EvnPLDispOrp_IsNewOrder\",
					ChildStatusType_id as \"ChildStatusType_id\",
					EvnPLDispOrp_IsTwoStage as \"EvnPLDispOrp_IsTwoStage\"		  		
		  		from v_EvnPLDispOrp
		  		where EvnPLDispOrp_id = :EvnPLDispOrp_id
				limit 1
			", $data, true);
			if ($savedData === false) {
				return array('Error_Msg' => 'Ошибка при получении данных карты');
			}
		}

		$this->load->model('EvnUsluga_model');
		// Если случай закрыт и задана дата справки, то сохраняем справку.
		if (!empty($data['EvnPLDispOrp_id']) && !empty($data['EvnPLDispOrp_IsFinish']) && $data['EvnPLDispOrp_IsFinish'] == 2 && !empty($data['EvnCostPrint_setDT'])) {
			// сохраняем справку
			$this->load->model('CostPrint_model');
			$this->CostPrint_model->saveEvnCostPrint(array(
				'Evn_id' => $data['EvnPLDispOrp_id'],
				'CostPrint_IsNoPrint' => $data['EvnCostPrint_IsNoPrint'],
				'CostPrint_setDT' => $data['EvnCostPrint_setDT'],
				'pmUser_id' => $data['pmUser_id']
			));
		}

		// Проверяем что человек находится в регистре по ДД и у него заведены все необходимые данные
		$checkResult = $this->checkPersonData($data);

		If ($checkResult[0] != "Ok") {
			return $checkResult;
		}

		$procedure = 'p_EvnPLDispOrp_ins';
		$data['EvnPLDispOrp_setDT'] = date('Y-m-d');
		$data['EvnPLDispOrp_disDT'] = null;
		$data['EvnPLDispOrp_didDT'] = null;
		$data['EvnPLDispOrp_VizitCount'] = 0;

		if (!empty($data['EvnPLDispOrp_id'])) {
			// достаем дату начала, дату окончания, количество посещений
			$query = "
				select
					to_char(cast(EvnPLDispOrp_setDT as timestamp),'dd.mm.yyyy') as \"EvnPLDispOrp_setDT\",
					to_char(cast(EvnPLDispOrp_disDT as timestamp),'dd.mm.yyyy') as \"EvnPLDispOrp_disDT\",
					to_char(cast(EvnPLDispOrp_didDT as timestamp),'dd.mm.yyyy') as \"EvnPLDispOrp_didDT\",
					EvnPLDispOrp_VizitCount as \"EvnPLDispOrp_VizitCount\"
				from
					v_EvnPLDispOrp
				where EvnPLDispOrp_id = :EvnPLDispOrp_id
			";
			$result = $this->db->query($query, ['EvnPLDispOrp_id' => $data['EvnPLDispOrp_id']]);
			if (is_object($result)) {
				$response = $result->result('array');
				if (count($response) > 0) {
					$data['EvnPLDispOrp_setDT'] = $response[0]['EvnPLDispOrp_setDT'];
					$data['EvnPLDispOrp_disDT'] = $response[0]['EvnPLDispOrp_disDT'];
					$data['EvnPLDispOrp_didDT'] = $response[0]['EvnPLDispOrp_didDT'];
					$data['EvnPLDispOrp_VizitCount'] = $response[0]['EvnPLDispOrp_VizitCount'];
				}
			}

			$procedure = 'p_EvnPLDispOrp_upd';
		}

		$data['EvnPLDispOrp_setDT'] = $data['EvnPLDispOrp_consDate'];
		if ($data['EvnPLDispOrp_IsMobile']) {
			$data['EvnPLDispOrp_IsMobile'] = 2;
		} else {
			$data['EvnPLDispOrp_IsMobile'] = 1;
		}
		if ($data['EvnPLDispOrp_IsOutLpu']) {
			$data['EvnPLDispOrp_IsOutLpu'] = 2;
		} else {
			$data['EvnPLDispOrp_IsOutLpu'] = 1;
		}

		$data['MedStaffFact_id'] = !empty($data['session']['CurARM']['MedStaffFact_id']) ? $data['session']['CurARM']['MedStaffFact_id'] : null;

		$this->checkZnoDirection($data, 'EvnPLDispOrp');


		$selectString = "
            evnpldisporp_id as \"EvnPLDispOrp_id\", 
            error_code as \"Error_Code\", 
            error_message as \"Error_Msg\"
        ";

		$query = "
			SELECT {$selectString}
			FROM {$procedure} (
				EvnPLDispOrp_id := :EvnPLDispOrp_id,
				MedStaffFact_id := :MedStaffFact_id,
				EvnPLDispOrp_IndexRep := :EvnPLDispOrp_IndexRep,
				EvnPLDispOrp_IndexRepInReg := :EvnPLDispOrp_IndexRepInReg,
				EvnPLDispOrp_fid := :EvnPLDispOrp_fid,
				Lpu_id := :Lpu_id,
				Server_id := :Server_id,
				PersonEvn_id := :PersonEvn_id,
				EvnPLDispOrp_setDT := :EvnPLDispOrp_setDT,
				EvnPLDispOrp_disDT := :EvnPLDispOrp_disDT,
				EvnPLDispOrp_didDT := :EvnPLDispOrp_didDT,
				EvnPLDispOrp_VizitCount := :EvnPLDispOrp_VizitCount,
				EvnPLDispOrp_IsFinish := :EvnPLDispOrp_IsFinish,
				EvnPLDispOrp_IsTwoStage := :EvnPLDispOrp_IsTwoStage,
				ChildStatusType_id := :ChildStatusType_id,
				AttachType_id := 2, -- доп. диспансеризация
				DispClass_id := :DispClass_id, -- ддс
				PayType_id := :PayType_id,
				EvnPLDispOrp_consDT := :EvnPLDispOrp_consDate,
				EvnPLDispOrp_IsMobile := :EvnPLDispOrp_IsMobile, 
				EvnPLDispOrp_IsOutLpu := :EvnPLDispOrp_IsOutLpu, 
				Lpu_mid := :Lpu_mid,
				EvnPLDispOrp_IsRefusal := (select EvnPLDispOrp_IsRefusal
									from v_EvnPLDispOrp 
									where EvnPLDispOrp_id = :EvnPLDispOrp_id LIMIT 1),
				EvnDirection_aid := (select EvnDirection_aid
									from v_EvnPLDispOrp 
									where EvnPLDispOrp_id = :EvnPLDispOrp_id LIMIT 1),
				EvnPLDispOrp_IsSuspectZNO := :EvnPLDispOrp_IsSuspectZNO,
				Diag_spid := :Diag_spid,
				pmUser_id := :pmUser_id
			);";

		$result = $this->db->query($query, $data);

		if (!is_object($result)) {
			return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение талона по ДД)');
		}

		$response = $result->result('array');

		if (!is_array($response) || count($response) == 0) {
			return false;
		} else if ($response[0]['Error_Msg']) {
			return $response;
		}

		if (!isset($data['EvnPLDispOrp_id'])) {
			$data['EvnPLDispOrp_id'] = $response[0]['EvnPLDispOrp_id'];
		}

		// Ищем AssessmentHealth связанный с EvnPLDispOrp_id, если нет его то добавляем новый, иначе обновляем
		$data['AssessmentHealth_id'] = NULL;
		$query = "
			select
				AssessmentHealth_id as \"AssessmentHealth_id\" 
			from v_AssessmentHealth 
			where EvnPLDisp_id = :EvnPLDispOrp_id
		";
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				$data['AssessmentHealth_id'] = $resp[0]['AssessmentHealth_id'];
			}
		}

		// запихивание чекбоксов в YesNo
		if ($data['AssessmentHealth_IsRegular']) {
			$data['AssessmentHealth_IsRegular'] = 2;
		} else {
			$data['AssessmentHealth_IsRegular'] = 1;
		}
		if ($data['AssessmentHealth_IsIrregular']) {
			$data['AssessmentHealth_IsIrregular'] = 2;
		} else {
			$data['AssessmentHealth_IsIrregular'] = 1;
		}
		if ($data['AssessmentHealth_IsAbundant']) {
			$data['AssessmentHealth_IsAbundant'] = 2;
		} else {
			$data['AssessmentHealth_IsAbundant'] = 1;
		}
		if ($data['AssessmentHealth_IsModerate']) {
			$data['AssessmentHealth_IsModerate'] = 2;
		} else {
			$data['AssessmentHealth_IsModerate'] = 1;
		}
		if ($data['AssessmentHealth_IsScanty']) {
			$data['AssessmentHealth_IsScanty'] = 2;
		} else {
			$data['AssessmentHealth_IsScanty'] = 1;
		}
		if ($data['AssessmentHealth_IsPainful']) {
			$data['AssessmentHealth_IsPainful'] = 2;
		} else {
			$data['AssessmentHealth_IsPainful'] = 1;
		}
		if ($data['AssessmentHealth_IsPainless']) {
			$data['AssessmentHealth_IsPainless'] = 2;
		} else {
			$data['AssessmentHealth_IsPainless'] = 1;
		}

		if ($data['AssessmentHealth_IsMental']) {
			$data['AssessmentHealth_IsMental'] = 2;
		} else {
			$data['AssessmentHealth_IsMental'] = 1;
		}
		if ($data['AssessmentHealth_IsOtherPsych']) {
			$data['AssessmentHealth_IsOtherPsych'] = 2;
		} else {
			$data['AssessmentHealth_IsOtherPsych'] = 1;
		}
		if ($data['AssessmentHealth_IsLanguage']) {
			$data['AssessmentHealth_IsLanguage'] = 2;
		} else {
			$data['AssessmentHealth_IsLanguage'] = 1;
		}
		if ($data['AssessmentHealth_IsVestibular']) {
			$data['AssessmentHealth_IsVestibular'] = 2;
		} else {
			$data['AssessmentHealth_IsVestibular'] = 1;
		}
		if ($data['AssessmentHealth_IsVisual']) {
			$data['AssessmentHealth_IsVisual'] = 2;
		} else {
			$data['AssessmentHealth_IsVisual'] = 1;
		}
		if ($data['AssessmentHealth_IsMeals']) {
			$data['AssessmentHealth_IsMeals'] = 2;
		} else {
			$data['AssessmentHealth_IsMeals'] = 1;
		}
		if ($data['AssessmentHealth_IsMotor']) {
			$data['AssessmentHealth_IsMotor'] = 2;
		} else {
			$data['AssessmentHealth_IsMotor'] = 1;
		}
		if ($data['AssessmentHealth_IsDeform']) {
			$data['AssessmentHealth_IsDeform'] = 2;
		} else {
			$data['AssessmentHealth_IsDeform'] = 1;
		}
		if ($data['AssessmentHealth_IsGeneral']) {
			$data['AssessmentHealth_IsGeneral'] = 2;
		} else {
			$data['AssessmentHealth_IsGeneral'] = 1;
		}

		$this->load->model('AssessmentHealth_model');
		$data['scenario'] = swModel::SCENARIO_DO_SAVE;
		$data['EvnPLDisp_id'] = $data['EvnPLDispOrp_id'];
		$response = $this->AssessmentHealth_model->doSave($data);

		// Назначения
		$this->load->model('DispAppoint_model');
		foreach ($data['DispAppointData'] as $key => $record) {
			if ($record['RecordStatus_Code'] == 3) {// удаление назначений
				$response = $this->DispAppoint_model->deleteDispAppoint(array(
					'DispAppoint_id' => $record['DispAppoint_id'],
					'pmUser_id' => $data['pmUser_id']
				));

				if (!is_array($response) || count($response) == 0) {
					return array(0 => array('Error_Msg' => 'Ошибка при удалении лабораторного исследования'));
				} else if (strlen($response[0]['Error_Msg']) > 0) {
					return $response;
				}
			} else {
				$params = array(
					'DispAppoint_id' => (!empty($record['DispAppoint_id']) && $record['DispAppoint_id'] > 0) ? $record['DispAppoint_id'] : null,
					'EvnPLDisp_id' => $data['EvnPLDisp_id'],
					'DispAppointType_id' => !empty($record['DispAppointType_id']) ? $record['DispAppointType_id'] : null,
					'MedSpecOms_id' => !empty($record['MedSpecOms_id']) ? $record['MedSpecOms_id'] : null,
					'ExaminationType_id' => !empty($record['ExaminationType_id']) ? $record['ExaminationType_id'] : null,
					'LpuSectionProfile_id' => !empty($record['LpuSectionProfile_id']) ? $record['LpuSectionProfile_id'] : null,
					'LpuSectionBedProfile_id' => !empty($record['LpuSectionBedProfile_id']) ? $record['LpuSectionBedProfile_id'] : null,
					'pmUser_id' => $data['pmUser_id']
				);

				$response = $this->DispAppoint_model->saveDispAppoint($params);

				if (!is_array($response) || count($response) == 0) {
					return array(0 => array('Error_Msg' => 'Ошибка при удалении лабораторного исследования'));
				} else if (strlen($response[0]['Error_Msg']) > 0) {
					return $response;
				}
			}
		}

		// Грид "Диагнозы и результаты"
		foreach ($data['EvnDiagAndRecomendation'] as $record) {
			// получаем MedCare_id для MedCareType_id = 1
			$json = json_decode($record['FormDataJSON'], true);
			$MedCare_id = $this->getMedCareForEvnVizitDispOrp($record['EvnVizitDispOrp_id'], 1);
			$this->saveMedCare(array(
				'MedCare_id' => $MedCare_id,
				'EvnVizitDispOrp_id' => $record['EvnVizitDispOrp_id'],
				'ConditMedCareType_nid' => empty($json['ConditMedCareType1_nid']) ? null : $json['ConditMedCareType1_nid'],
				'PlaceMedCareType_nid' => empty($json['PlaceMedCareType1_nid']) ? null : $json['PlaceMedCareType1_nid'],
				'ConditMedCareType_id' => empty($json['ConditMedCareType1_id']) ? null : $json['ConditMedCareType1_id'],
				'PlaceMedCareType_id' => empty($json['PlaceMedCareType1_id']) ? null : $json['PlaceMedCareType1_id'],
				'LackMedCareType_id' => empty($json['LackMedCareType1_id']) ? null : $json['LackMedCareType1_id'],
				'MedCareType_id' => 1,
				'pmUser_id' => $data['pmUser_id']
			));

			// получаем MedCare_id для MedCareType_id = 2
			$MedCare_id = $this->getMedCareForEvnVizitDispOrp($record['EvnVizitDispOrp_id'], 2);
			$this->saveMedCare(array(
				'MedCare_id' => $MedCare_id,
				'EvnVizitDispOrp_id' => $record['EvnVizitDispOrp_id'],
				'ConditMedCareType_nid' => empty($json['ConditMedCareType2_nid']) ? null : $json['ConditMedCareType2_nid'],
				'PlaceMedCareType_nid' => empty($json['PlaceMedCareType2_nid']) ? null : $json['PlaceMedCareType2_nid'],
				'ConditMedCareType_id' => empty($json['ConditMedCareType2_id']) ? null : $json['ConditMedCareType2_id'],
				'PlaceMedCareType_id' => empty($json['PlaceMedCareType2_id']) ? null : $json['PlaceMedCareType2_id'],
				'LackMedCareType_id' => empty($json['LackMedCareType2_id']) ? null : $json['LackMedCareType2_id'],
				'MedCareType_id' => 2,
				'pmUser_id' => $data['pmUser_id']
			));

			// получаем MedCare_id для MedCareType_id = 3
			$MedCare_id = $this->getMedCareForEvnVizitDispOrp($record['EvnVizitDispOrp_id'], 3);
			$this->saveMedCare(array(
				'MedCare_id' => $MedCare_id,
				'EvnVizitDispOrp_id' => $record['EvnVizitDispOrp_id'],
				'ConditMedCareType_nid' => empty($json['ConditMedCareType3_nid']) ? null : $json['ConditMedCareType3_nid'],
				'PlaceMedCareType_nid' => empty($json['PlaceMedCareType3_nid']) ? null : $json['PlaceMedCareType3_nid'],
				'ConditMedCareType_id' => empty($json['ConditMedCareType3_id']) ? null : $json['ConditMedCareType3_id'],
				'PlaceMedCareType_id' => empty($json['PlaceMedCareType3_id']) ? null : $json['PlaceMedCareType3_id'],
				'LackMedCareType_id' => empty($json['LackMedCareType3_id']) ? null : $json['LackMedCareType3_id'],
				'MedCareType_id' => 3,
				'pmUser_id' => $data['pmUser_id']
			));

			// сохраняем вмп и и Диспансерное наблюдение DispSurveilType_id и EvnVizitDisp_IsVMP
			// пока просто update todo
			$query = "
				update EvnVizitDisp 
				set DispSurveilType_id = :DispSurveilType_id, EvnVizitDisp_IsVMP = :EvnVizitDisp_IsVMP, EvnVizitDisp_IsFirstTime = :EvnVizitDisp_IsFirstTime 
				where Evn_id = :EvnVizitDisp_id
			";

			$result = $this->db->query($query, array(
				'DispSurveilType_id' => empty($json['DispSurveilType_id']) ? null : $json['DispSurveilType_id'],
				'EvnVizitDisp_IsVMP' => empty($json['EvnVizitDisp_IsVMP']) ? null : $json['EvnVizitDisp_IsVMP'],
				'EvnVizitDisp_IsFirstTime' => empty($json['EvnVizitDisp_IsFirstTime']) ? null : $json['EvnVizitDisp_IsFirstTime'],
				'EvnVizitDisp_id' => $record['EvnVizitDispOrp_id'],
			));
		}

		$this->load->model('EvnUsluga_model');
		// Лабораторные исследования
		foreach ($data['EvnUslugaDispOrp'] as $key => $record) {
			if ($record['Record_Status'] == 3) {// удаление исследований
				$query = "
				SELECT 
					error_code as \"Error_Code\", 
            		error_message as \"Error_Msg\"
            	FROM p_EvnUslugaDispOrp_del (
            	    EvnUslugaDispOrp_id := :EvnUslugaDispOrp_id,
            	    pmUser_id := :pmUser_id
            	);
				";
				$result = $this->db->query($query, array('EvnUslugaDispOrp_id' => $record['EvnUslugaDispOrp_id'], 'pmUser_id' => $data['pmUser_id']));

				if (!is_object($result)) {
					return array(0 => array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление лабораторного исследования)'));
				}

				$response = $result->result('array');

				if (!is_array($response) || count($response) == 0) {
					return array(0 => array('Error_Msg' => 'Ошибка при удалении лабораторного исследования'));
				} else if (strlen($response[0]['Error_Msg']) > 0) {
					return $response;
				}
			} else if ($record['Record_Status'] != 1) {
				if ($record['Record_Status'] == 0) {
					$procedure = 'p_EvnUslugaDispOrp_ins';
				} else {
					$procedure = 'p_EvnUslugaDispOrp_upd';
				}

				// 1. ищем DopDispInfoConsent_id
				$record['DopDispInfoConsent_id'] = null;
				if (!empty($record['EvnUslugaDispOrp_id'])) {
					$query = "
						select
							DopDispInfoConsent_id as \"DopDispInfoConsent_id\"
						from
							v_EvnUslugaDispOrp
						where
							EvnUslugaDispOrp_id = :EvnUslugaDispOrp_id
							and coalesce(EvnUslugaDispOrp_IsVizitCode, 1) = 1
						limit 1
					";

					$result = $this->db->query($query, $record);
					if (is_object($result)) {
						$resp = $result->result('array');
						if (!empty($resp[0]['DopDispInfoConsent_id'])) {
							$record['DopDispInfoConsent_id'] = $resp[0]['DopDispInfoConsent_id'];
						}
					}
				}

				// 2. обновляем/добавляем согласие
				$ddicproc = "p_DopDispInfoConsent_ins";
				if (!empty($record['DopDispInfoConsent_id'])) {
					$ddicproc = "p_DopDispInfoConsent_upd";
				}

				$selectString = "
					dopdispinfoconsent_id as \"DopDispInfoConsent_id\", 
					error_code as \"Error_Code\", 
					error_message as \"Error_Msg\"
				";

				$query = "
				SELECT {$selectString} 
				FROM {$ddicproc} (
						DopDispInfoConsent_id := :DopDispInfoConsent_id,
						EvnPLDisp_id := :EvnPLDisp_id, 
						SurveyTypeLink_id := NULL,
						DopDispInfoConsent_IsAgree := 2, 
						DopDispInfoConsent_IsEarlier := 1, 
						pmUser_id := :pmUser_id
				);";

				$result = $this->db->query($query, array(
					'EvnPLDisp_id' => $data['EvnPLDispOrp_id'],
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

				$setDT = $record['EvnUslugaDispOrp_setDate'];
				if (!empty($record['EvnUslugaDispOrp_setTime'])) {
					$setDT .= ' ' . $record['EvnUslugaDispOrp_setTime'];
				}
				$disDT = null;
				if (!empty($record['EvnUslugaDispOrp_disDate'])) {
					$disDT = $record['EvnUslugaDispOrp_disDate'];

					if (!empty($record['EvnUslugaDispOrp_disTime'])) {
						$disDT .= ' ' . $record['EvnUslugaDispOrp_disTime'];
					}
				}

				if ($record['LpuSection_id'] == '')
					$record['LpuSection_id'] = Null;

				$selectString = "
					evnuslugadisporp_id as \"EvnUslugaDispOrp_id\", 
					error_code as \"Error_Code\", 
					error_message as \"Error_Msg\"
				";

				$query = "
				SELECT {$selectString} 
				FROM {$procedure} (
						EvnUslugaDispOrp_id := :EvnUslugaDispOrp_id,
						EvnUslugaDispOrp_pid := :EvnUslugaDispOrp_pid,
						Lpu_id := :Lpu_id,
						Server_id := :Server_id,
						PersonEvn_id := :PersonEvn_id,
						EvnUslugaDispOrp_setDT := :EvnUslugaDispOrp_setDT,
						EvnUslugaDispOrp_disDT := :EvnUslugaDispOrp_disDT,
						EvnUslugaDispOrp_didDT := :EvnUslugaDispOrp_didDT,
						LpuSection_uid := :LpuSection_uid,
						MedSpecOms_id := :MedSpecOms_id,
						LpuSectionProfile_id := :LpuSectionProfile_id,
						MedPersonal_id := :MedPersonal_id,
						MedStaffFact_id := :MedStaffFact_id,
						UslugaComplex_id := :UslugaComplex_id,
						DopDispInfoConsent_id := :DopDispInfoConsent_id,
						PayType_id := (select PayType_id from v_PayType where PayType_SysNick = 'dopdisp' LIMIT 1),
						UslugaPlace_id := 1,
						Lpu_uid := :Lpu_uid,
						EvnUslugaDispOrp_Kolvo := 1,
						ExaminationPlace_id := :ExaminationPlace_id,
						EvnPrescrTimetable_id := null,
						EvnPrescr_id := null,
						EvnUslugaDispOrp_Result := :EvnUslugaDispOrp_Result,
						pmUser_id := :pmUser_id
				);";


				$result = $this->db->query($query, array(
					'EvnUslugaDispOrp_id' => (!empty($record['Record_Status']) ? $record['EvnUslugaDispOrp_id'] : NULL),
					'EvnUslugaDispOrp_pid' => $data['EvnPLDispOrp_id'],
					'Lpu_id' => $data['Lpu_id'],
					'MedSpecOms_id' => (!empty($record['MedSpecOms_id']) ? $record['MedSpecOms_id'] : NULL),
					'LpuSectionProfile_id' => (!empty($record['LpuSectionProfile_id']) ? $record['LpuSectionProfile_id'] : NULL),
					'Server_id' => $data['Server_id'],
					'PersonEvn_id' => $data['PersonEvn_id'],
					'EvnUslugaDispOrp_setDT' => $setDT,
					'EvnUslugaDispOrp_disDT' => $disDT,
					'EvnUslugaDispOrp_didDT' => (!empty($record['EvnUslugaDispOrp_didDate']) ? $record['EvnUslugaDispOrp_didDate'] : NULL),
					'LpuSection_uid' => (!empty($record['LpuSection_id']) ? $record['LpuSection_id'] : NULL),
					'MedPersonal_id' => (!empty($record['MedPersonal_id']) ? $record['MedPersonal_id'] : NULL),
					'MedStaffFact_id' => (!empty($record['MedStaffFact_id']) ? $record['MedStaffFact_id'] : NULL),
					'UslugaComplex_id' => $record['UslugaComplex_id'],
					'DopDispInfoConsent_id' => $record['DopDispInfoConsent_id'],
					'Lpu_uid' => (!empty($record['Lpu_uid']) ? $record['Lpu_uid'] : NULL),
					'ExaminationPlace_id' => (!empty($record['ExaminationPlace_id']) ? $record['ExaminationPlace_id'] : NULL),
					'EvnUslugaDispOrp_Result' => (!empty($record['EvnUslugaDispOrp_Result']) ? $record['EvnUslugaDispOrp_Result'] : NULL),
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

				$record['EvnUslugaDispOrp_id'] = $response[0]['EvnUslugaDispOrp_id'];
			}
		}

		// чистим в карте согласия по которым больше нет услуг
		$query = "
			select 
				DopDispInfoConsent_id as \"DopDispInfoConsent_id\"
			from 
				v_DopDispInfoConsent ddic
			where EvnPLDisp_id = :EvnPLDisp_id 
			and (not exists (select EvnVizitDispOrp_id from v_EvnVizitDispOrp where DopDispInfoConsent_id = ddic.DopDispInfoConsent_id LIMIT 1))
			and (not exists (select EvnUslugaDispOrp_id from v_EvnUslugaDispOrp where DopDispInfoConsent_id = ddic.DopDispInfoConsent_id LIMIT 1))
		";
		$result = $this->db->query($query, array(
			'EvnPLDisp_id' => $data['EvnPLDispOrp_id']
		));
		if (is_object($result)) {
			$resp = $result->result('array');
			foreach ($resp as $one) {
				$query = "
				SELECT 
					error_code as \"Error_Code\", 
            		error_message as \"Error_Msg\"
            	FROM p_DopDispInfoConsent_del (
            	    	DopDispInfoConsent_id := :DopDispInfoConsent_id,
						pmUser_id := :pmUser_id
            	);
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
			in_array($data['session']['region']['nick'], array('buryatiya', 'krym')) && !empty($data['EvnPLDispOrp_id'])
			&& !empty($data['EvnPLDispOrp_IsFinish']) && $data['EvnPLDispOrp_IsFinish'] == 2
		) {
			// Ищем существующую услугу
			$query = "
				select
					EvnUslugaDispOrp_id as \"EvnUslugaDispOrp_id\",
					UslugaComplex_id as \"UslugaComplex_id\",
					PayType_id as \"PayType_id\",
					to_char(EvnUslugaDispOrp_setDT, 'dd.mm.yyyy') as \"EvnUslugaDispOrp_setDate\"
				from v_EvnUslugaDispOrp
				where EvnUslugaDispOrp_pid = :EvnUslugaDispOrp_pid
					and EvnUslugaDispOrp_IsVizitCode = 2
				limit 1
			";
			$result = $this->db->query($query, array(
				'EvnUslugaDispOrp_pid' => $data['EvnPLDispOrp_id']
			));

			if (!is_object($result)) {
				return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (поиск услуги)');
			}

			$response = $result->result('array');

			if (is_array($response) && count($response) > 0) {
				$uslugaData = $response[0];
			} else {
				$uslugaData = array();
			}

			$data['ageDate'] = substr($data['EvnPLDispOrp_setDT'], 0, 4) . '-12-31';

			$filter = "";
			if (getRegionNick() == 'buryatiya') {
				$filter .= "
					and (
						not (UslugaSurveyLink_From = 0 and UslugaSurveyLink_To = 1 and DispClass_id IN (3,7))
						or exists(
							select
								eudo.EvnUslugaDispOrp_id
							from
								v_EvnUslugaDispOrp eudo
								inner join v_UslugaComplex uc on uc.UslugaComplex_id = eudo.UslugaComplex_id
							where
								eudo.EvnUslugaDispOrp_pid = :EvnPLDispOrp_id and
								uc.UslugaComplex_Code IN ('004043', '161204')
							limit 1
						)
					)				
				";
			}

			// Определяем UslugaComplex_id через $schema.UslugaSurveyLink
			$query = "
			select USL.UslugaComplex_id as \"UslugaComplex_id\"
				from {$data['session']['region']['schema']}.v_UslugaSurveyLink USL
				where
					USL.DispClass_id = :DispClass_id
					and COALESCE(USL.Sex_id, (
							SELECT COALESCE(Sex_id, 3),
							from v_PersonState ps
							where ps.Person_id = :Person_id LIMIT 1)) = (
							SELECT COALESCE(Sex_id, 3),
							from v_PersonState ps
							where ps.Person_id = :Person_id LIMIT 1)
					and (
							dbo.Age2(Person_BirthDay, COALESCE(CAST(:ageDate as date), dbo.tzGetDate()))
							from v_PersonState ps
							where ps.Person_id = :Person_id LIMIT 1) between COALESCE(USL.UslugaSurveyLink_From, 0) and COALESCE(USL.UslugaSurveyLink_To, 999)
					and COALESCE(USL.UslugaSurveyLink_IsDel, 1) = 1
					and (USL.UslugaSurveyLink_begDate is null or USL.UslugaSurveyLink_begDate <= :EvnPLDispOrp_setDT)
					and (USL.UslugaSurveyLink_endDate is null or USL.UslugaSurveyLink_endDate >= :EvnPLDispOrp_setDT)
					{$filter}
				LIMIT 1";

			$result = $this->db->query($query, array(
				'DispClass_id' => $data['DispClass_id'],
				'EvnPLDispOrp_id' => $data['EvnPLDispOrp_fid'],
				'EvnPLDispOrp_setDT' => $data['EvnPLDispOrp_setDT'],
				'Person_id' => $data['Person_id'],
				'ageDate' => $data['ageDate'],
			));

			if (!is_object($result)) {
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
				$selectString = "
					evnuslugadisporp_id as \"EvnUslugaDispOrp_id\", 
					error_code as \"Error_Code\", 
					error_message as \"Error_Msg\"
				";

				$query = "
				SELECT {$selectString}
				FROM p_EvnUslugaDispOrp_" . (!empty($uslugaData['EvnUslugaDispOrp_id']) ? "upd" : "ins") . " (
						EvnUslugaDispOrp_id := :EvnUslugaDispOrp_id,
						EvnUslugaDispOrp_pid := :EvnUslugaDispOrp_pid,
						UslugaComplex_id := :UslugaComplex_id,
						EvnUslugaDispOrp_setDT := :EvnUslugaDispOrp_setDT,
						EvnUslugaDispOrp_IsVizitCode := 2,
						Lpu_id := :Lpu_id,
						Server_id := :Server_id,
						PersonEvn_id := :PersonEvn_id,
						PayType_id := (select PayType_id from v_PayType where PayType_SysNick = 'dopdisp' LIMIT 1),
						UslugaPlace_id := 1,
						pmUser_id := :pmUser_id
				);
				";
				$result = $this->db->query($query, array(
					'EvnUslugaDispOrp_id' => (!empty($uslugaData['EvnUslugaDispOrp_id']) ? $uslugaData['EvnUslugaDispOrp_id'] : null),
					'EvnUslugaDispOrp_pid' => $data['EvnPLDispOrp_id'],
					'UslugaComplex_id' => $UslugaComplex_id,
					'EvnUslugaDispOrp_setDT' => (!empty($data['EvnPLDispOrp_setDT']) ? $data['EvnPLDispOrp_setDT'] : null),
					'Lpu_id' => $data['Lpu_id'],
					'Server_id' => $data['Server_id'],
					'PersonEvn_id' => $data['PersonEvn_id'],
					'PayType_id' => (!empty($uslugaData['PayType_id']) ? $uslugaData['PayType_id'] : null),
					'pmUser_id' => $data['pmUser_id']
				));

				if (!is_object($result)) {
					return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление услуги) (' . __LINE__ . ')'));
				}

				$response = $result->result('array');

				if (!is_array($response) || count($response) == 0) {
					return array(array('Error_Msg' => 'Ошибка при удалении услуги (' . __LINE__ . ')'));
				} else if (!empty($response[0]['Error_Msg'])) {
					return array(array('Error_Msg' => $response[0]['Error_Msg'] . ' (' . __LINE__ . ')'));
				}
			} // Удаляем
			else if (!empty($uslugaData['EvnUslugaDispOrp_id'])) {
				$query = "
				SELECT 
					error_code as \"Error_Code\", 
            		error_message as \"Error_Msg\"
            	FROM p_EvnUslugaDispOrp_del (
            	    	EvnUslugaDispOrp_id := :EvnUslugaDispOrp_id,
						pmUser_id := :pmUser_id
            	);
            	";
				$result = $this->db->query($query, array(
					'EvnUslugaDispOrp_id' => $uslugaData['EvnUslugaDispOrp_id'],
					'pmUser_id' => $data['pmUser_id']
				));

				if (!is_object($result)) {
					return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление услуги) (' . __LINE__ . ')'));
				}

				$response = $result->result('array');

				if (!is_array($response) || count($response) == 0) {
					return array(array('Error_Msg' => 'Ошибка при удалении услуги (' . __LINE__ . ')'));
				} else if (!empty($response[0]['Error_Msg'])) {
					return array(array('Error_Msg' => $response[0]['Error_Msg'] . ' (' . __LINE__ . ')'));
				}
			}
		}

		$justClosed = (
			!empty($data['EvnPLDispOrp_IsFinish']) && $data['EvnPLDispOrp_IsFinish'] == 2 && (
				empty($savedData) || $savedData['EvnPLDispOrp_IsFinish'] != 2
			)
		);

		if (getRegionNick() == 'penza' && (empty($savedData) || $justClosed)) {
			//Отправить человека в очередь на идентификацию
			$this->load->model('Person_model', 'pmodel');
			$this->pmodel->isAllowTransaction = false;
			$resTmp = $this->pmodel->addPersonRequestData(array(
				'Person_id' => $data['Person_id'],
				'Evn_id' => $data['EvnPLDispOrp_id'],
				'pmUser_id' => $data['pmUser_id'],
				'PersonRequestSourceType_id' => 3,
			));
			$this->pmodel->isAllowTransaction = true;
			if (!$this->isSuccessful($resTmp) && !in_array($resTmp[0]['Error_Code'], array(302, 303))) {
				return array('Error_Msg' => $resTmp[0]['Error_Msg']);
			}
		}

		return array(0 => array('EvnPLDispOrp_id' => $data['EvnPLDispOrp_id'], 'Error_Msg' => ''));
	}

	/**
	 * Сохранение
	 */
	function saveMedCare($data)
	{
		if (!empty($data['MedCare_id']) && $data['MedCare_id'] > 0) {
			$proc = 'p_MedCare_upd';
		} else {
			$proc = 'p_MedCare_ins';
			$data['MedCare_id'] = null;
		}

		$selectString = "
            medcare_id as \"MedCare_id\", 
            error_code as \"Error_Code\", 
            error_message as \"Error_Msg\"
        ";

		$query = "
		SELECT {$selectString}
		FROM {$proc} (
				MedCare_id := :MedCare_id, 
				EvnVizitDisp_id := :EvnVizitDispOrp_id,
				LackMedCareType_id := :LackMedCareType_id,
				ConditMedCareType_nid := :ConditMedCareType_nid,
				PlaceMedCareType_nid := :PlaceMedCareType_nid,
				ConditMedCareType_id := :ConditMedCareType_id,
				PlaceMedCareType_id := :PlaceMedCareType_id,
				MedCareType_id := :MedCareType_id,
				pmUser_id := :pmUser_id
		);
		";
		// echo getDebugSql($query, $data); die();
		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Возвращает данные
	 */
	function getMedCareForEvnVizitDispOrp($EvnVizitDispOrp_id, $MedCareType_id)
	{
		$query = "
			select 
			       MedCare_id as \"MedCare_id\"
			from
				v_MedCare
			where
				EvnVizitDisp_id = :EvnVizitDispOrp_id
				and MedCareType_id = :MedCareType_id
			LIMIT 1
		";

		$result = $this->db->query($query, array(
			'EvnVizitDispOrp_id' => $EvnVizitDispOrp_id,
			'MedCareType_id' => $MedCareType_id
		));

		if (is_object($result)) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return $resp[0]['MedCare_id'];
			}
		}

		return null;
	}

	/**
	 * Поиск талонов по ДД
	 */
	function searchEvnPLDispOrp($data)
	{
		$filter = "";
		$join_str = "";

		if ($data['PersonAge_Min'] > $data['PersonAge_Max']) {
			return false;
		}

		$queryParams = array();

		if (($data['DocumentType_id'] > 0) || ($data['OrgDep_id'] > 0)) {
			$join_str .= " inner join Document  on Document.Document_id = PS.Document_id";

			if ($data['DocumentType_id'] > 0) {
				$join_str .= " and Document.DocumentType_id = :DocumentType_id";
				$queryParams['DocumentType_id'] = $data['DocumentType_id'];
			}

			if ($data['OrgDep_id'] > 0) {
				$join_str .= " and Document.OrgDep_id = :OrgDep_id";
				$queryParams['OrgDep_id'] = $data['OrgDep_id'];
			}
		}

		if (($data['OMSSprTerr_id'] > 0) || ($data['OrgSmo_id'] > 0) || ($data['PolisType_id'] > 0)) {
			$join_str .= " inner join Polis  on Polis.Polis_id = PS.Polis_id";

			if ($data['OMSSprTerr_id'] > 0) {
				$join_str .= " and Polis.OmsSprTerr_id = :OMSSprTerr_id";
				$queryParams['OMSSprTerr_id'] = $data['OMSSprTerr_id'];
			}

			if ($data['OrgSmo_id'] > 0) {
				$join_str .= " and Polis.OrgSmo_id = :OrgSmo_id";
				$queryParams['OrgSmo_id'] = $data['OrgSmo_id'];
			}

			if ($data['PolisType_id'] > 0) {
				$join_str .= " and Polis.PolisType_id = :PolisType_id";
				$queryParams['PolisType_id'] = $data['PolisType_id'];
			}
		}

		if (($data['Org_id'] > 0) || ($data['Post_id'] > 0)) {
			$join_str .= " inner join Job  on Job.Job_id = PS.Job_id";

			if ($data['Org_id'] > 0) {
				$join_str .= " and Job.Org_id = :Org_id";
				$queryParams['Org_id'] = $data['Org_id'];
			}

			if ($data['Post_id'] > 0) {
				$join_str .= " and Job.Post_id = :Post_id";
				$queryParams['Post_id'] = $data['Post_id'];
			}
		}

		if (($data['KLRgn_id'] > 0) || ($data['KLSubRgn_id'] > 0) || ($data['KLCity_id'] > 0) || ($data['KLTown_id'] > 0) || ($data['KLStreet_id'] > 0) || (strlen($data['Address_House']) > 0)) {
			$join_str .= " inner join Address  on Address.Address_id = PS.UAddress_id";

			if ($data['KLRgn_id'] > 0) {
				$filter .= " and Address.KLRgn_id = :KLRgn_id";
				$queryParams['KLRgn_id'] = $data['KLRgn_id'];
			}

			if ($data['KLSubRgn_id'] > 0) {
				$filter .= " and Address.KLSubRgn_id = :KLSubRgn_id";
				$queryParams['KLSubRgn_id'] = $data['KLSubRgn_id'];
			}

			if ($data['KLCity_id'] > 0) {
				$filter .= " and Address.KLCity_id = :KLCity_id";
				$queryParams['KLCity_id'] = $data['KLCity_id'];
			}

			if ($data['KLTown_id'] > 0) {
				$filter .= " and Address.KLTown_id = :KLTown_id";
				$queryParams['KLTown_id'] = $data['KLTown_id'];
			}

			if ($data['KLStreet_id'] > 0) {
				$filter .= " and Address.KLStreet_id = :KLStreet_id";
				$queryParams['KLStreet_id'] = $data['KLStreet_id'];
			}

			if (strlen($data['Address_House']) > 0) {
				$filter .= " and Address.Address_House = :Address_House";
				$queryParams['Address_House'] = $data['Address_House'];
			}
		}

		if (isset($data['EvnPLDispOrp_disDate'][1])) {
			$filter .= " and EvnPLDispOrp.EvnPLDispOrp_disDate <= :EvnPLDispOrp_disDate1";
			$queryParams['EvnPLDispOrp_disDate1'] = $data['EvnPLDispOrp_disDate'][1];
		}

		if (isset($data['EvnPLDispOrp_disDate'][0])) {
			$filter .= " and EvnPLDispOrp.EvnPLDispOrp_disDate >= :EvnPLDispOrp_disDate1";
			$queryParams['EvnPLDispOrp_disDate0'] = $data['EvnPLDispOrp_disDate'][0];
		}

		if ($data['EvnPLDispOrp_IsFinish'] > 0) {
			$filter .= " and EvnPLDispOrp.EvnPLDispOrp_IsFinish = :EvnPLDispOrp_IsFinish";
			$queryParams['EvnPLDispOrp_IsFinish'] = $data['EvnPLDispOrp_IsFinish'];
		}

		if (isset($data['EvnPLDispOrp_setDate'][1])) {
			$filter .= " and EvnPLDispOrp.EvnPLDispOrp_setDate <= :EvnPLDispOrp_setDate1";
			$queryParams['EvnPLDispOrp_setDate1'] = $data['EvnPLDispOrp_setDate'][1];
		}

		if (isset($data['EvnPLDispOrp_setDate'][0])) {
			$filter .= " and EvnPLDispOrp.EvnPLDispOrp_setDate >= :EvnPLDispOrp_setDate0";
			$queryParams['EvnPLDispOrp_setDate0'] = $data['EvnPLDispOrp_setDate'][0];
		}

		if ($data['PersonAge_Max'] > 0) {
			$filter .= " and EvnPLDispOrp.Person_Age <= :PersonAge_Max";
			$queryParams['PersonAge_Max'] = $data['PersonAge_Max'];
		}

		if ($data['PersonAge_Min'] > 0) {
			$filter .= " and EvnPLDispOrp.Person_Age >= :PersonAge_Min";
			$queryParams['PersonAge_Min'] = $data['PersonAge_Min'];
		}

		if (($data['PersonCard_Code'] != '') || ($data['LpuRegion_id'] > 0)) {
			$join_str .= " inner join v_PersonCard PC  on PC.Person_id = PS.Person_id";

			if (strlen($data['PersonCard_Code']) > 0) {
				$filter .= " and PC.PersonCard_Code = :PersonCard_Code";
				$queryParams['PersonCard_Code'] = $data['PersonCard_Code'];
			}

			if (strlen($data['LpuRegion_id']) > 0) {
				$filter .= " and PC.LpuRegion_id = :LpuRegion_id";
				$queryParams['LpuRegion_id'] = $data['LpuRegion_id'];
			}
		}
		if (isset($data['Person_Birthday'][1])) {
			$filter .= " and PS.Person_Birthday <= :Person_Birthday1";
			$queryParams['Person_Birthday1'] = $data['Person_Birthday'][1];
		}

		if (isset($data['Person_Birthday'][0])) {
			$filter .= " and PS.Person_Birthday >= :Person_Birthday0";
			$queryParams['Person_Birthday0'] = $data['Person_Birthday'][0];
		}

		if (strlen($data['Person_Firname']) > 0) {
			$filter .= " and lower(PS.Person_Firname) like lower(:Person_Firname)";
			$queryParams['Person_Firname'] = $data['Person_Firname'] . "%";
		}

		if (strlen($data['Person_Secname']) > 0) {
			$filter .= " and lower(PS.Person_Secname) like lower(:Person_Secname)";
			$queryParams['Person_Secname'] = $data['Person_Secname'] . "%";
		}

		if ($data['Person_Snils'] > 0) {
			$filter .= " and PS.Person_Snils = :Person_Snils";
			$queryParams['Person_Snils'] = $data['Person_Snils'];
		}

		if (strlen($data['Person_Surname']) > 0) {
			$filter .= " and lower(PS.Person_Surname) like lower(:Person_Surname)";
			$queryParams['Person_Surname'] = $data['Person_Surname'] . "%";
		}

		if ($data['PrivilegeType_id'] > 0) {
			$join_str .= " inner join v_PersonPrivilege PP  on PP.Person_id = EvnPLDispOrp.Person_id and PP.PrivilegeType_id = :PrivilegeType_id and PP.PersonPrivilege_begDate is not null and PP.PersonPrivilege_begDate <= dbo.tzGetDate() and (PP.PersonPrivilege_endDate is null or PP.PersonPrivilege_endDate >= cast(to_char(dbo.tzGetDate(), 'yyyy.mm.dd') as timestamp)) and PP.Lpu_id = :Lpu_id";
			$queryParams['PrivilegeType_id'] = $data['PrivilegeType_id'];
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}

		if ($data['Sex_id'] >= 0) {
			$filter .= " and PS.Sex_id = :Sex_id";
			$queryParams['Sex_id'] = $data['Sex_id'];
		}

		if ($data['SocStatus_id'] > 0) {
			$filter .= " and PS.SocStatus_id = :SocStatus_id";
			$queryParams['SocStatus_id'] = $data['SocStatus_id'];
		}

		$query = "
			SELECT
				EvnPLDispOrp.EvnPLDispOrp_id as \"EvnPLDispOrp_id\",
				EvnPLDispOrp.Person_id as \"Person_id\",
				EvnPLDispOrp.Server_id as \"Server_id\",
				EvnPLDispOrp.PersonEvn_id as \"PersonEvn_id\",
				RTRIM(PS.Person_Surname) as \"Person_Surname\",
				RTRIM(PS.Person_Firname) as \"Person_Firname\",
				RTRIM(PS.Person_Secname) as \"Person_Secname\",
				to_char(PS.Person_Birthday, 'dd.mm.yyyy') as \"Person_Birthday\",
				EvnPLDispOrp.EvnPLDispOrp_VizitCount as \"EvnPLDispOrp_VizitCount\",
				IsFinish.YesNo_Name as \"EvnPLDispOrp_IsFinish\",
				to_char(EvnPLDispOrp.EvnPLDispOrp_setDate, 'dd.mm.yyyy') as \"EvnPLDispOrp_setDate\",
				to_char(EvnPLDispOrp.EvnPLDispOrp_disDate, 'dd.mm.yyyy') as \"EvnPLDispOrp_disDate\"
			FROM v_EvnPLDispOrp EvnPLDispOrp
				inner join v_PersonState PS on PS.Person_id = EvnPLDispOrp.Person_id
				left join YesNo IsFinish on IsFinish.YesNo_id = EvnPLDispOrp.EvnPLDispOrp_IsFinish
				" . $join_str . "
			WHERE (1 = 1)
				" . $filter . "
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
	 * Получение списка записей для потокового ввода
	 */
	function getEvnPLDispOrpStreamList($data)
	{

		$query = "
			SELECT
				EvnPLDispOrp.EvnPLDispOrp_id as \"EvnPLDispOrp_id\",
				EvnPLDispOrp.Person_id as \"Person_id\",
				EvnPLDispOrp.Server_id as \"Server_id\",
				EvnPLDispOrp.PersonEvn_id as \"PersonEvn_id\",
				RTRIM(PS.Person_Surname) || ' ' || RTRIM(PS.Person_Firname) || ' ' || RTRIM(PS.Person_Secname) as \"Person_Fio\",
				to_char(PS.Person_Birthday, 'dd.mm.yyyy') as \"Person_Birthday\",
				EvnPLDispOrp.EvnPLDispOrp_VizitCount as \"EvnPLDispOrp_VizitCount\",
				IsFinish.YesNo_Name as \"EvnPLDispOrp_IsFinish\",
				to_char(EvnPLDispOrp.EvnPLDispOrp_setDate, 'dd.mm.yyyy') as \"EvnPLDispOrp_setDate\",
				to_char(EvnPLDispOrp.EvnPLDispOrp_disDate, 'dd.mm.yyyy') as \"EvnPLDispOrp_disDate\"
			FROM v_EvnPLDispOrp EvnPLDispOrp
				inner join v_PersonState PS on PS.Person_id = EvnPLDispOrp.Person_id
				left join YesNo IsFinish on IsFinish.YesNo_id = EvnPLDispOrp.EvnPLDispOrp_IsFinish
			WHERE EvnPLDispOrp_updDT >= :begDate and EvnPLDispOrp.pmUser_updID= :pmUser_id 
			LIMIT 100";

		$result = $this->db->query($query, array($data['begDate'] . " " . $data['begTime'], $data['pmUser_id']));

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение списка лет, в которые выписывались талоны по ДД с количеством талонов, для комбобокса
	 */
	function getEvnPLDispOrpYears($data)
	{
		$sql = "
        select
			count(EPLDO.EvnPLDispOrp_id) as \"count\",
			EXTRACT(YEAR FROM EPLDO.EvnPLDispOrp_setDate) as \"EvnPLDispOrp_Year\"
		from
			v_PersonState PS
			inner join v_EvnPLDispOrp EPLDO on PS.Person_id = EPLDO.Person_id and EPLDO.Lpu_id = :Lpu_id and EPLDO.DispClass_id IN (3,7)
		where
  		    exists (
  		        select
  		            personcard_id
                from v_PersonCard PC
                    left join v_LpuRegion LR on LR.LpuRegion_id = PC.LpuRegion_id
                WHERE PC.Person_id = PS.Person_id
                and PC.Lpu_id = :Lpu_id LIMIT 1)
                and EXTRACT(YEAR FROM EPLDO.EvnPLDispOrp_setDate) >= 2013
			GROUP BY
				EXTRACT(YEAR FROM EPLDO.EvnPLDispOrp_setDate)
			ORDER BY
				EXTRACT(YEAR FROM EPLDO.EvnPLDispOrp_setDate)
		";

		//echo getDebugSQL($sql, $data); die;
		$res = $this->db->query($sql, $data);
		if (is_object($res))
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Получение списка лет, в которые выписывались талоны по ДД с количеством талонов, для комбобокса
	 */
	function getEvnPLDispOrpYearsSec($data)
	{
		$sql = "
        select
			count(EPLDO.EvnPLDispOrp_id) as \"count\",
			EXTRACT(YEAR FROM EPLDO.EvnPLDispOrp_setDate) as \"EvnPLDispOrp_Year\"
		from
			v_PersonState PS
			inner join v_EvnPLDispOrp EPLDO on PS.Person_id = EPLDO.Person_id and EPLDO.Lpu_id = :Lpu_id and EPLDO.DispClass_id IN (4,8)
		where
		EPLDO.EvnPLDispOrp_setDate >= cast('2013-01-01' as timestamp)  and EPLDO.EvnPLDispOrp_setDate <= cast('2013-12-31' as timestamp)  and  exists (select personcard_id from v_PersonCard PC  left join v_LpuRegion LR  on LR.LpuRegion_id = PC.LpuRegion_id WHERE PC.Person_id = PS.Person_id and PC.Lpu_id = :Lpu_id LIMIT 1)
			--year(EPLDO.EvnPLDispOrp_setDate) >= 2013
			GROUP BY
				EXTRACT(YEAR FROM EPLDO.EvnPLDispOrp_setDate)
			ORDER BY
				EXTRACT(YEAR FROM EPLDO.EvnPLDispOrp_setDate)
		";

		//echo getDebugSQL($sql, $data); die;
		$res = $this->db->query($sql, $data);
		if (is_object($res))
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Проверка, есть ли талон на этого человека в этом году
	 */
	function checkIfEvnPLDispOrpExists($data)
	{
		$filter = ($data['stage'] == 2) ? " and DispClass_id IN (4,8)" : " and DispClass_id IN (3,7)";
		$filter_1st = ($data['stage'] == 2) ? " and epldorp.DispClass_id IN (3,7)" : " and (1=0)";
		$filter_for1st = ($data['stage'] == 2)?" and persdo.PersonDispOrp_Year = (SELECT PersonDispOrp_Year FROM cte2)":" and persdo.PersonDispOrp_Year = :Year";

		$sql = "
			WITH cte1 AS (
			select
				dbo.Age2(Person_BirthDay, dbo.tzGetDate()) as age
			from
				v_PersonState ps
			where
				ps.Person_id = :Person_id
			limit 1
            ),
            cte2 AS (
			select 
				epldorp.EvnPLDispOrp_id as EvnPLDispOrp_fid,
				date_part('year', epldorp.EvnPLDispOrp_setDate) as PersonDispOrp_Year
			from
				v_EvnPLDispOrp epldorp
			where
				epldorp.Person_id = :Person_id
                 and epldorp.EvnPLDispOrp_IsTwoStage = 2 and epldorp.EvnPLDispOrp_IsFinish = 2
				and not exists(
					select EvnPLDispOrp_id from v_EvnPLDispOrp epldorpsec where epldorpsec.EvnPLDispOrp_fid = epldorp.EvnPLDispOrp_id
				)
				{$filter_1st}
			order by
				epldorp.EvnPLDispOrp_setDate desc
			limit 1)
            
			select 
				case when persdo.CategoryChildType_id in (5,6,7) then 'orpadopted' else 'orp' end as \"CategoryChildType\",
				evnpl.EvnPLDispOrp_id as \"EvnPLDispOrp_id\",
				evnprof.EvnPLDispTeenInspection_id as \"EvnPLDispTeenInspection_id\",
				(SELECT EvnPLDispOrp_fid FROM cte2) as \"EvnPLDispOrp_fid\"
			from
				v_PersonDispOrp persdo
				LEFT JOIN LATERAL(
					SELECT 
						EvnPLDispOrp_id
					FROM
						v_EvnPLDispOrp
					WHERE
						Person_id = persdo.Person_id and date_part('year', EvnPLDispOrp_setDate) = persdo.PersonDispOrp_Year -- Контроль внутри и между МО. 
						{$filter}
					LIMIT 1
				) evnpl ON true
				LEFT JOIN LATERAL(
					SELECT 
						epldti.EvnPLDispTeenInspection_id
					FROM
						v_EvnPLDispTeenInspection epldti
					WHERE
						epldti.Person_id = persdo.Person_id
						and date_part('year', epldti.EvnPLDispTeenInspection_setDT) = persdo.PersonDispOrp_Year -- Контроль внутри и между МО.
						and epldti.DispClass_id = 10 -- профилактический осмотр
						and epldti.EvnPLDispTeenInspection_IsFinish = 2 -- закрытый
						and (SELECT age FROM cte1) >= 3 -- 3 лет и старше
                     LIMIT 1
				) evnprof ON true
			where 
				persdo.Person_id = :Person_id
				and persdo.CategoryChildType_id < 8
				{$filter_for1st}
            limit 1
		";

		$res = $this->db->query($sql, array('Person_id' => $data['Person_id'], 'Lpu_id' => $data['Lpu_id'], 'Year' => $data['Year']));
		if (is_object($res)) {
			$sel = $res->result('array');
			if (count($sel) > 0) {
				return array(array(
					'Error_Msg' => '',
					'CategoryChildType' => $sel[0]['CategoryChildType'],
					'EvnPLDispOrp_fid' => $sel[0]['EvnPLDispOrp_fid'],
					'isEvnPLDispOrpExists' => !empty($sel[0]['EvnPLDispOrp_id']),
					'isEvnPLDispTeenProfExists' => !empty($sel[0]['EvnPLDispTeenInspection_id'])
				));
			} else {
				return array(array('Error_Msg' => 'Человек не найден в регистре'));
			}
		} else
			return false;
	}

	/**
	 * Получение данных для отображения в ЭМК
	 */
	function getEvnPLDispOrpViewData($data)
	{
		$queryParams = array(
			'EvnPLDisp_id' => $data['EvnPLDisp_id']
		);
		// – Редактирование карты диспансеризации / профосмотра доступно только из АРМ врача поликлиники, пользователем с привязкой к врачу терапевту (ВОП) / педиатру (ВОП),
		// отделение места работы которого совпадает с отделением места работы врача, создавшего карту.
		$accessType = "'view' as \"accessType\",";
		if (!empty($data['session']['CurARM']['PostMed_id']) && in_array($data['session']['CurARM']['PostMed_id'], array(73, 74, 75, 76, 40, 46, 47)) && !empty($data['session']['CurARM']['LpuSection_id'])) {
			$accessType = "case when COALESCE(msf.LpuSection_id, :LpuSection_id) = :LpuSection_id then 'edit' else 'view' end as \"accessType\",";
			$queryParams['LpuSection_id'] = $data['session']['CurARM']['LpuSection_id'];
		}

		$query = "
			select
				epldo.EvnPLDispOrp_id as \"EvnPLDispOrp_id\",
				case
					when epldo.MedStaffFact_id is not null then coalesce(l.Lpu_Nick || ' ', '') || coalesce(ls.LpuSection_Name || ' ', '') || coalesce(msf.Person_Fio, '') 
					else COALESCE(l.Lpu_Nick || ' ', '') || coalesce(pu.pmUser_Name, '')
				end as \"AuthorInfo\",
				'EvnPLDispOrp' as \"Object\",
				epldo.DispClass_id as \"DispClass_id\",
				epldo.Person_id as \"Person_id\",
				epldo.PersonEvn_id as \"PersonEvn_id\",
				epldo.Server_id as \"Server_id\",
				dc.DispClass_Code as \"DispClass_Code\",
				dc.DispClass_Name as \"DispClass_Name\",
				{$accessType}
				epldo.PayType_id as \"PayType_id\",
				pt.PayType_Name as \"PayType_Name\",
				to_char(epldo.EvnPLDispOrp_setDT, 'dd.mm.yyyy') as \"EvnPLDispOrp_setDate\",
				to_char(epldo.EvnPLDispOrp_consDT, 'dd.mm.yyyy') as \"EvnPLDispOrp_consDate\",
				ah.HealthKind_id as \"HealthKind_id\",
				hk.HealthKind_Name as \"HealthKind_Name\",
				coalesce(epldo.EvnPLDispOrp_IsFinish, 1) as \"EvnPLDispOrp_IsFinish\"
			from
				v_EvnPLDispOrp epldo
				left join v_Lpu l on l.Lpu_id = epldo.Lpu_id
				left join v_MedStaffFact msf on msf.MedStaffFact_id = epldo.MedStaffFact_id
				left join v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id
				left join v_pmUser pu on pu.pmUser_id = epldo.pmUser_updID
				left join lateral(
					select * from v_AssessmentHealth where EvnPLDisp_id = epldo.EvnPLDispOrp_id LIMIT 1
				) ah on true
				left join v_DispClass dc on dc.DispClass_id = epldo.DispClass_id
				left join v_PayType pt on pt.PayType_id = epldo.PayType_id
				left join v_HealthKind hk on hk.HealthKind_id = ah.HealthKind_id
			where
				epldo.EvnPLDispOrp_id = :EvnPLDisp_id
		";

		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Сохранение осмотров
	 */
	function saveEvnVizitDispOrp($data)
	{

		if (empty($data['EvnVizitDispOrp_id'])) {
			$procedure = 'p_EvnVizitDispOrp_ins';
		} else {
			$procedure = 'p_EvnVizitDispOrp_upd';
		}

		if (empty($data['DopDispInfoConsent_id'])) {
			return array(0 => array('Error_Msg' => 'Ошибка, для осмотра не найдено согласие')); // чтобы удостовериться что для всех осмотров сохраняются ссылки на их DopDispInfoConsent'ы.
		}

		// проверяем, есть ли уже такое посещение
		$query = "
			select 
				count(*) as \"cnt\"
			from
				v_EvnVizitDispOrp
			where
				EvnVizitDispOrp_pid = coalesce(cast(:EvnPLDispOrp_id as bigint), 0)
				and DopDispInfoConsent_id = :DopDispInfoConsent_id
				and ( EvnVizitDispOrp_id <> coalesce(cast(:EvnVizitDispOrp_id as bigint), 0) )
		";
		$result = $this->db->query(
			$query,
			[
				'EvnPLDispOrp_id' => $data['EvnPLDispOrp_id'],
				'DopDispInfoConsent_id' => $data['DopDispInfoConsent_id'],
				'EvnVizitDispOrp_id' => $data['EvnVizitDispOrp_id']
			]
		);
		if (!is_object($result)) {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение посещения)'));
		}
		$response = $result->result('array');
		if (!is_array($response) || count($response) == 0) {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение посещения)'));
		} else if ($response[0]['cnt'] >= 1) {
			return array(array('Error_Msg' => 'Обнаружено дублирование осмотров, это недопустимо.'));
		}

		$setDT = $data['EvnVizitDispOrp_setDate'];
		if (!empty($data['EvnVizitDispOrp_setTime'])) {
			$setDT .= ' ' . $data['EvnVizitDispOrp_setTime'];
		}
		$disDT = null;
		if (!empty($data['EvnVizitDispOrp_disDate'])) {
			$disDT = $data['EvnVizitDispOrp_disDate'];

			if (!empty($data['EvnVizitDispOrp_disTime'])) {
				$disDT .= ' ' . $data['EvnVizitDispOrp_disTime'];
			}
		}

		if (!empty($data['UslugaComplex_id'])) {
			// Надо проверить что сохраняемая услуга соответствует списку возможных, чтобы пользователи никак не могли сохранить левую услугу. (refs #71538)
			$sql = "
				SELECT
					stl.SurveyTypeLink_id as \"SurveyTypeLink_id\"
				FROM
					v_SurveyTypeLink stl
					inner join v_SurveyType st on st.SurveyType_id = stl.SurveyType_id
				WHERE
					st.OrpDispSpec_id = :OrpDispSpec_id
					and stl.UslugaComplex_id = :UslugaComplex_id
				LIMIT 1
			";
			$res = $this->db->query($sql, array(
				'OrpDispSpec_id' => $data['OrpDispSpec_id'],
				'UslugaComplex_id' => $data['UslugaComplex_id']
			));

			if (!is_object($res)) {
				$this->db->trans_rollback();
				return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (получение списка возможных услуг)'));
			}

			$resp = $res->result('array');
			if (empty($resp[0]['SurveyTypeLink_id'])) {
				$this->db->trans_rollback();
				return array(array('Error_Msg' => 'Указана некорректная услуга для осмотра/исследования'));
			}
		} else {
			// ищем UslugaComplex_id
			$data['UslugaComplex_id'] = $this->getUslugaComplexForDopDispInfoConsent($data['DopDispInfoConsent_id']);
		}

		// окончание проверки
		$selectString = "
            evnvizitdisporp_id as \"EvnVizitDispOrp_id\", 
            error_code as \"Error_Code\", 
            error_message as \"Error_Msg\"
        ";
		$query = "
			SELECT {$selectString}
			FROM {$procedure} (
				EvnVizitDispOrp_id := :EvnVizitDispOrp_id,
				EvnVizitDispOrp_pid := :EvnVizitDispOrp_pid,
				Lpu_id := :Lpu_id,
				Server_id := :Server_id,
				PersonEvn_id := :PersonEvn_id,
				EvnVizitDispOrp_setDT := :EvnVizitDispOrp_setDT,
				EvnVizitDispOrp_disDT := :EvnVizitDispOrp_disDT,
				EvnVizitDispOrp_didDT := null,
				LpuSection_id := :LpuSection_id,
				MedSpecOms_id := :MedSpecOms_id,
				LpuSectionProfile_id := :LpuSectionProfile_id,
				MedPersonal_id := :MedPersonal_id,
				MedStaffFact_id := :MedStaffFact_id,
				MedPersonal_sid := null,
				PayType_id := null,
				UslugaComplex_id := :UslugaComplex_id,
				OrpDispSpec_id := :OrpDispSpec_id,
				DopDispInfoConsent_id := :DopDispInfoConsent_id,
				Diag_id := :Diag_id,
				TumorStage_id := :TumorStage_id,
				DopDispDiagType_id := :DopDispDiagType_id,
				DopDispAlien_id := :DopDispAlien_id,
				pmUser_id := :pmUser_id
		);
		";
		$result = $this->db->query($query, array(
			'EvnVizitDispOrp_id' => $data['EvnVizitDispOrp_id'],
			'EvnVizitDispOrp_pid' => $data['EvnPLDispOrp_id'],
			'Lpu_id' => (!empty($data['Lpu_uid']) ? $data['Lpu_uid'] : $data['Lpu_id']),
			'MedSpecOms_id' => (!empty($data['MedSpecOms_id']) ? $data['MedSpecOms_id'] : NULL),
			'LpuSectionProfile_id' => (!empty($data['LpuSectionProfile_id']) ? $data['LpuSectionProfile_id'] : NULL),
			'Server_id' => $data['Server_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'EvnVizitDispOrp_setDT' => $setDT,
			'EvnVizitDispOrp_disDT' => $disDT,
			'LpuSection_id' => (!empty($data['LpuSection_id']) ? $data['LpuSection_id'] : NULL),
			'MedPersonal_id' => (!empty($data['MedPersonal_id']) ? $data['MedPersonal_id'] : NULL),
			'MedStaffFact_id' => (!empty($data['MedStaffFact_id']) ? $data['MedStaffFact_id'] : NULL),
			'UslugaComplex_id' => (!empty($data['UslugaComplex_id']) ? $data['UslugaComplex_id'] : NULL),
			'OrpDispSpec_id' => $data['OrpDispSpec_id'],
			'DopDispInfoConsent_id' => $data['DopDispInfoConsent_id'],
			'Diag_id' => $data['Diag_id'],
			//'TumorStage_id' => $data['TumorStage_id'],
			'TumorStage_id' => (!empty($data['TumorStage_id']) ? $data['TumorStage_id'] : NULL),
			'DopDispDiagType_id' => (isset($data['DopDispDiagType_id']) && $data['DopDispDiagType_id'] > 0) ? $data['DopDispDiagType_id'] : null,
			'DopDispAlien_id' => $data['DopDispAlien_id'],
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

		if (!empty($response[0]['EvnVizitDispOrp_id'])) {
			// сохраняем сопутствующие диагнозы
			if (!empty($data['EvnDiagDopDispGridData'])) {
				$data['EvnDiagDopDispGridData'] = json_decode($data['EvnDiagDopDispGridData'], true);
			} else {
				$data['EvnDiagDopDispGridData'] = array();
			}
			foreach ($data['EvnDiagDopDispGridData'] as $EvnDiagDopDisp) {
				if ($EvnDiagDopDisp['Record_Status'] == 3) {// удаление
					$query = "
					SELECT 
						error_code as \"Error_Code\", 
            			error_message as \"Error_Msg\"
            		FROM p_EvnDiagDopDisp_del (
            		    	EvnDiagDopDisp_id := :EvnDiagDopDisp_id,
							pmUser_id := :pmUser_id
            		);
					";
					$result_eddd = $this->db->query($query, array(
							'EvnDiagDopDisp_id' => $EvnDiagDopDisp['EvnDiagDopDisp_id'],
							'pmUser_id' => $data['pmUser_id'])
					);
					if (!is_object($result_eddd)) {
						return array(0 => array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление сопутствующего диагноза)'));
					}
					$resp_eddd = $result_eddd->result('array');
					if (!is_array($resp_eddd) || count($resp_eddd) == 0) {
						return array(0 => array('Error_Msg' => 'Ошибка при удалении сопутствующего диагноза'));
					} else if (strlen($resp_eddd[0]['Error_Msg']) > 0) {
						return $resp_eddd;
					}
				} else {
					if ($EvnDiagDopDisp['Record_Status'] == 0) {
						$proc_evdd = 'p_EvnDiagDopDisp_ins';
					} else {
						$proc_evdd = 'p_EvnDiagDopDisp_upd';
					}

					// проверяем, есть ли уже такой диагноз
					$query = "
						select
							count(*) as \"cnt\"
						from
							v_EvnDiagDopDisp
						where
							EvnDiagDopDisp_pid = :EvnDiagDopDisp_id
							and Diag_id = :Diag_id
							and DiagSetClass_id = 3
							and ( EvnDiagDopDisp_id <> coalesce(cast(:EvnDiagDopDisp_id as bigint), 0) )
					";
					$result_eddd = $this->db->query(
						$query,
						array(
							'EvnVizitDispOrp_id' => $response[0]['EvnVizitDispOrp_id'],
							'Diag_id' => $EvnDiagDopDisp['Diag_id'],
							'EvnDiagDopDisp_id' => $EvnDiagDopDisp['Record_Status'] == 0 ? null : $EvnDiagDopDisp['EvnDiagDopDisp_id']
						)
					);
					if (!is_object($result_eddd)) {
						return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение сопутствующего диагноза)'));
					}
					$resp_eddd = $result_eddd->result('array');
					if (!is_array($resp_eddd) || count($resp_eddd) == 0) {
						return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение сопутствующего диагноза)'));
					} else if ($resp_eddd[0]['cnt'] >= 1) {
						return array(array('Error_Msg' => 'Обнаружено дублирование сопутствующих диагнозов, это недопустимо.'));
					}

					$selectString = "
						evndiagdopdisp_id as \"EvnDiagDopDisp_id\", 
						error_code as \"Error_Code\", 
						error_message as \"Error_Msg\"
					";

					$query = "
					SELECT {$selectString}
					FROM {$proc_evdd} (
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
					);
					";
					$result_eddd = $this->db->query($query, array(
						'EvnDiagDopDisp_id' => $EvnDiagDopDisp['Record_Status'] == 0 ? null : $EvnDiagDopDisp['EvnDiagDopDisp_id'],
						'EvnDiagDopDisp_pid' => $response[0]['EvnVizitDispOrp_id'],
						'Diag_id' => $EvnDiagDopDisp['Diag_id'],
						'DiagSetClass_id' => 3,
						'DeseaseDispType_id' => !empty($EvnDiagDopDisp['DeseaseDispType_id']) ? $EvnDiagDopDisp['DeseaseDispType_id'] : null,
						'Lpu_id' => $data['Lpu_id'],
						'Server_id' => $data['Server_id'],
						'PersonEvn_id' => $data['PersonEvn_id'],
						'pmUser_id' => $data['pmUser_id']
					));
					if (!is_object($result_eddd)) {
						return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение посещения)');
					}
					$resp_eddd = $result_eddd->result('array');
					if (!is_array($resp_eddd) || count($resp_eddd) == 0) {
						return false;
					} else if ($resp_eddd[0]['Error_Msg']) {
						return $resp_eddd;
					}
				}
			}

			// в $data['EvnDiagAndRecomendation'] ищем соответсвующую строку и прописываем ей правильный id-шник
			/*foreach($data['EvnDiagAndRecomendation'] as &$item) {
				if ($item['EvnVizitDispOrp_id'] == $data['EvnVizitDispOrp_id']) {
					$item['EvnVizitDispOrp_id'] = $response[0]['EvnVizitDispOrp_id'];
				}
			}*/
			// к посещению нужно привязывать услугу EvnUslugaDispOrp с соответсвующей UslugaComplex.
			// 1. проверяем не созадана ли уже услуга по этому посещению
			$EvnUslugaDispOrp_id = $this->getEvnUslugaDispOrpForEvnVizit($response[0]['EvnVizitDispOrp_id']);
			if (!empty($EvnUslugaDispOrp_id)) {
				$proc = 'p_EvnUslugaDispOrp_upd';
			} else {
				$proc = 'p_EvnUslugaDispOrp_ins';
			}
			// 3. сохраняем услугу
			if (!empty($data['UslugaComplex_id'])) {
				$selectString = "
					evnuslugadisporp_id as \"EvnUslugaDispOrp_id\", 
					error_code as \"Error_Code\", 
					error_message as \"Error_Msg\"
				";
				$query = "
				SELECT {$selectString}
				FROM {$proc} (
						EvnUslugaDispOrp_id := :EvnUslugaDispOrp_id,
						EvnUslugaDispOrp_pid := :EvnUslugaDispOrp_pid,
						UslugaComplex_id := :UslugaComplex_id,
						Lpu_id := :Lpu_id,
						Server_id := :Server_id,
						PayType_id := (select PayType_id from v_PayType where PayType_SysNick = 'dopdisp' LIMIT 1),
						UslugaPlace_id := 1,
						PersonEvn_id := :PersonEvn_id,
						EvnPrescrTimetable_id := null,
						EvnPrescr_id := null,
						Diag_id := :Diag_id,
						LpuSection_uid := :LpuSection_id,
						MedPersonal_id := :MedPersonal_id,
						MedStaffFact_id := :MedStaffFact_id,
						pmUser_id := :pmUser_id
				);
				";
				$result = $this->db->query($query, array(
					'EvnUslugaDispOrp_id' => $EvnUslugaDispOrp_id,
					'EvnUslugaDispOrp_pid' => $response[0]['EvnVizitDispOrp_id'],
					'UslugaComplex_id' => $data['UslugaComplex_id'],
					'Diag_id' => $data['Diag_id'],
					'pmUser_id' => $data['pmUser_id'],
					'Lpu_id' => $data['Lpu_id'],
					'Server_id' => $data['Server_id'],
					'PersonEvn_id' => $data['PersonEvn_id'],
					'LpuSection_id' => (!empty($data['LpuSection_id']) ? $data['LpuSection_id'] : NULL),
					'MedPersonal_id' => (!empty($data['MedPersonal_id']) ? $data['MedPersonal_id'] : NULL),
					'MedStaffFact_id' => (!empty($data['MedStaffFact_id']) ? $data['MedStaffFact_id'] : NULL),
				));

				if (!is_object($result)) {
					return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение услуги) (' . __LINE__ . ')'));
				}

				$resp_usl = $result->result('array');

				if (!is_array($resp_usl) || count($resp_usl) == 0) {
					return array(array('Error_Msg' => 'Ошибка при сохранении услуги (' . __LINE__ . ')'));
				} else if (!empty($resp_usl[0]['Error_Msg'])) {
					return array(array('Error_Msg' => $resp_usl[0]['Error_Msg'] . ' (' . __LINE__ . ')'));
				}
			} else if (!empty($EvnUslugaDispOrp_id)) {
				$query = "
				SELECT 
						error_code as \"Error_Code\", 
						error_message as \"Error_Msg\"
				FROM p_EvnUslugaDispOrp_del (
				    	EvnUslugaDispOrp_id := :EvnUslugaDispOrp_id,
						pmUser_id := :pmUser_id
				);
				";
				$result = $this->db->query($query, array(
					'EvnUslugaDispOrp_id' => $EvnUslugaDispOrp_id,
					'pmUser_id' => $data['pmUser_id']
				));

				if (!is_object($result)) {
					return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление услуги) (' . __LINE__ . ')'));
				}

				$resp_usl = $result->result('array');

				if (!is_array($resp_usl) || count($resp_usl) == 0) {
					return array(array('Error_Msg' => 'Ошибка при удалении услуги (' . __LINE__ . ')'));
				} else if (!empty($resp_usl[0]['Error_Msg'])) {
					return array(array('Error_Msg' => $resp_usl[0]['Error_Msg'] . ' (' . __LINE__ . ')'));
				}
			}
		}

		return $response;
	}

	/**
	 * Удаление осмотра
	 */
	function deleteEvnVizitDispOrp($data)
	{
		$query = "
		SELECT 
			error_code as \"Error_Code\", 
            error_message as \"Error_Msg\"
        FROM p_EvnVizitDispOrp_del (
            EvnVizitDispOrp_id := :EvnVizitDispOrp_id,
            pmUser_id := :pmUser_id
        );
        ";
		return $this->queryResult($query, array('EvnVizitDispOrp_id' => $data['EvnVizitDispOrp_id'], 'pmUser_id' => $data['pmUser_id']));
	}

	/**
	 * Сохранение осмотров
	 */
	function saveEvnVizitDispOrpSec($data)
	{

		if (empty($data['EvnVizitDispOrp_id'])) {
			$procedure = 'p_EvnVizitDispOrp_ins';
		} else {
			$procedure = 'p_EvnVizitDispOrp_upd';
		}

		// 1. ищем DopDispInfoConsent_id
		$data['DopDispInfoConsent_id'] = null;
		if (!empty($data['EvnVizitDispOrp_id'])) {
			$query = "
				select 
				       DopDispInfoConsent_id as \"DopDispInfoConsent_id\"
				from
					v_EvnVizitDispOrp
				where
					EvnVizitDispOrp_id = :EvnVizitDispOrp_id
				LIMIT 1
			";

			$result = $this->db->query($query, $data);
			if (is_object($result)) {
				$resp = $result->result('array');
				if (!empty($resp[0]['DopDispInfoConsent_id'])) {
					$data['DopDispInfoConsent_id'] = $resp[0]['DopDispInfoConsent_id'];
				}
			}
		}

		// 2. обновляем/добавляем согласие
		$ddicproc = "p_DopDispInfoConsent_ins";
		if (!empty($data['DopDispInfoConsent_id'])) {
			$ddicproc = "p_DopDispInfoConsent_upd";
		}

		$query = "
		SELECT 
		    dopdispinfoconsent_id as \"DopDispInfoConsent_id\",   
			error_code as \"Error_Code\", 
            error_message as \"Error_Msg\"
        FROM {$ddicproc} (
				DopDispInfoConsent_id := :DopDispInfoConsent_id,
				EvnPLDisp_id := :EvnPLDisp_id, 
				SurveyTypeLink_id := NULL,
				DopDispInfoConsent_IsAgree := 2, 
				DopDispInfoConsent_IsEarlier := 1, 
				pmUser_id := :pmUser_id
        );
        ";

		$result = $this->db->query($query, array(
			'EvnPLDisp_id' => $data['EvnPLDispOrp_id'],
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

		$setDT = $data['EvnVizitDispOrp_setDate'];
		if (!empty($data['EvnVizitDispOrp_setTime'])) {
			$setDT .= ' ' . $data['EvnVizitDispOrp_setTime'];
		}
		$disDT = null;
		if (!empty($data['EvnVizitDispOrp_disDate'])) {
			$disDT = $data['EvnVizitDispOrp_disDate'];

			if (!empty($data['EvnVizitDispOrp_disTime'])) {
				$disDT .= ' ' . $data['EvnVizitDispOrp_disTime'];
			}
		}

		$query = "
		SELECT 
			evnvizitdisporp_id as \"EvnVizitDispOrp_id\", 
            error_code as \"Error_Code\", 
            error_message as \"Error_Msg\"
        FROM {$procedure} (
				EvnVizitDispOrp_id := :EvnVizitDispOrp_id,
				EvnVizitDispOrp_pid := :EvnVizitDispOrp_pid,
				Lpu_id := :Lpu_id,
				Server_id := :Server_id,
				PersonEvn_id := :PersonEvn_id,
				EvnVizitDispOrp_setDT := :EvnVizitDispOrp_setDT,
				EvnVizitDispOrp_disDT := :EvnVizitDispOrp_disDT,
				EvnVizitDispOrp_didDT := null,
				LpuSection_id := :LpuSection_id,
				MedSpecOms_id := :MedSpecOms_id,
				LpuSectionProfile_id := :LpuSectionProfile_id,
				MedPersonal_id := :MedPersonal_id,
				MedStaffFact_id := :MedStaffFact_id,
				MedPersonal_sid := null,
				PayType_id := null,
				UslugaComplex_id := :UslugaComplex_id,
				OrpDispSpec_id := null,
				DopDispInfoConsent_id := :DopDispInfoConsent_id,
				Diag_id := :Diag_id,
				DopDispDiagType_id := :DopDispDiagType_id,
				DopDispAlien_id := :DopDispAlien_id,
				pmUser_id := :pmUser_id
        );
        ";
		$result = $this->db->query($query, array(
			'EvnVizitDispOrp_id' => $data['EvnVizitDispOrp_id'],
			'EvnVizitDispOrp_pid' => $data['EvnPLDispOrp_id'],
			'Lpu_id' => (!empty($data['Lpu_uid']) ? $data['Lpu_uid'] : $data['Lpu_id']),
			'MedSpecOms_id' => (!empty($data['MedSpecOms_id']) ? $data['MedSpecOms_id'] : NULL),
			'LpuSectionProfile_id' => (!empty($data['LpuSectionProfile_id']) ? $data['LpuSectionProfile_id'] : NULL),
			'Server_id' => $data['Server_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'EvnVizitDispOrp_setDT' => $setDT,
			'EvnVizitDispOrp_disDT' => $disDT,
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
		if (!is_object($result)) {
			return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение посещения)');
		}
		$response = $result->result('array');
		if (!is_array($response) || count($response) == 0) {
			return false;
		} else if ($response[0]['Error_Msg']) {
			return $response;
		}

		if (!empty($response[0]['EvnVizitDispOrp_id'])) {
			// сохраняем сопутствующие диагнозы
			if (!empty($data['EvnDiagDopDispGridData'])) {
				$data['EvnDiagDopDispGridData'] = json_decode($data['EvnDiagDopDispGridData'], true);
			} else {
				$data['EvnDiagDopDispGridData'] = array();
			}
			foreach ($data['EvnDiagDopDispGridData'] as $EvnDiagDopDisp) {
				if ($EvnDiagDopDisp['Record_Status'] == 3) {// удаление

					$query = "
					SELECT 
						error_code as \"Error_Code\", 
            			error_message as \"Error_Msg\"
            		FROM p_EvnDiagDopDisp_del (
            		    	EvnDiagDopDisp_id := :EvnDiagDopDisp_id,
							pmUser_id := :pmUser_id
            		);
            		";
					$result_eddd = $this->db->query($query, array(
							'EvnDiagDopDisp_id' => $EvnDiagDopDisp['EvnDiagDopDisp_id'],
							'pmUser_id' => $data['pmUser_id'])
					);
					if (!is_object($result_eddd)) {
						return array(0 => array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление сопутствующего диагноза)'));
					}
					$resp_eddd = $result_eddd->result('array');
					if (!is_array($resp_eddd) || count($resp_eddd) == 0) {
						return array(0 => array('Error_Msg' => 'Ошибка при удалении сопутствующего диагноза'));
					} else if (strlen($resp_eddd[0]['Error_Msg']) > 0) {
						return $resp_eddd;
					}
				} else {
					if ($EvnDiagDopDisp['Record_Status'] == 0) {
						$proc_evdd = 'p_EvnDiagDopDisp_ins';
					} else {
						$proc_evdd = 'p_EvnDiagDopDisp_upd';
					}

					// проверяем, есть ли уже такой диагноз
					$query = "
						select
							count(*) as \"cnt\"
						from
							v_EvnDiagDopDisp
						where
							EvnDiagDopDisp_pid = :EvnVizitDispOrp_id
							and Diag_id = :Diag_id
							and DiagSetClass_id = 3
							and ( EvnDiagDopDisp_id <> coalesce(EvnDiagDopDisp_id, 0) )
					";
					$result_eddd = $this->db->query(
						$query,
						array(
							'EvnVizitDispOrp_id' => $response[0]['EvnVizitDispOrp_id'],
							'Diag_id' => $EvnDiagDopDisp['Diag_id'],
							'EvnDiagDopDisp_id' => $EvnDiagDopDisp['Record_Status'] == 0 ? null : $EvnDiagDopDisp['EvnDiagDopDisp_id']
						)
					);
					if (!is_object($result_eddd)) {
						return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение сопутствующего диагноза)'));
					}
					$resp_eddd = $result_eddd->result('array');
					if (!is_array($resp_eddd) || count($resp_eddd) == 0) {
						return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение сопутствующего диагноза)'));
					} else if ($resp_eddd[0]['cnt'] >= 1) {
						return array(array('Error_Msg' => 'Обнаружено дублирование сопутствующих диагнозов, это недопустимо.'));
					}

					$query = "
					SELECT 
						evndiagdopdisp_id as \"EvnDiagDopDisp_id\", 
            			error_code as \"Error_Code\", 
            			error_message as \"Error_Msg\"
            		FROM {$proc_evdd} (
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
            		);
            		";
					$result_eddd = $this->db->query($query, array(
						'EvnDiagDopDisp_id' => $EvnDiagDopDisp['Record_Status'] == 0 ? null : $EvnDiagDopDisp['EvnDiagDopDisp_id'],
						'EvnDiagDopDisp_pid' => $response[0]['EvnVizitDispOrp_id'],
						'Diag_id' => $EvnDiagDopDisp['Diag_id'],
						'DiagSetClass_id' => 3,
						'DeseaseDispType_id' => !empty($EvnDiagDopDisp['DeseaseDispType_id']) ? $EvnDiagDopDisp['DeseaseDispType_id'] : null,
						'Lpu_id' => $data['Lpu_id'],
						'Server_id' => $data['Server_id'],
						'PersonEvn_id' => $data['PersonEvn_id'],
						'pmUser_id' => $data['pmUser_id']
					));
					if (!is_object($result_eddd)) {
						return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение посещения)');
					}
					$resp_eddd = $result_eddd->result('array');
					if (!is_array($resp_eddd) || count($resp_eddd) == 0) {
						return false;
					} else if ($resp_eddd[0]['Error_Msg']) {
						return $resp_eddd;
					}
				}
			}

			// к посещению нужно привязывать услугу EvnUslugaDispOrp с соответсвующей UslugaComplex.
			// 1. проверяем не созадана ли уже услуга по этому посещению
			$EvnUslugaDispOrp_id = $this->getEvnUslugaDispOrpForEvnVizit($response[0]['EvnVizitDispOrp_id']);
			if (!empty($EvnUslugaDispOrp_id)) {
				$proc = 'p_EvnUslugaDispOrp_upd';
			} else {
				$proc = 'p_EvnUslugaDispOrp_ins';
			}
			// 3. сохраняем услугу
			if (!empty($data['UslugaComplex_id'])) {
				$query = "
				with mv as (
					select PayType_id
					from v_PayType
					where PayType_SysNick = 'dopdisp'
					limit 1
				)
				SELECT 
				       	evnuslugadisporp_id as \"EvnUslugaDispOrp_id\",
            			error_code as \"Error_Code\", 
            			error_message as \"Error_Msg\"
            	FROM {$proc} (
						EvnUslugaDispOrp_id := :EvnUslugaDispOrp_id,
						EvnUslugaDispOrp_pid := :EvnUslugaDispOrp_pid,
						UslugaComplex_id := :UslugaComplex_id,
						Lpu_id := :Lpu_id,
						Server_id := :Server_id,
						PayType_id := (select PayType_id from mv),
						UslugaPlace_id := 1,
						PersonEvn_id := :PersonEvn_id,
						EvnPrescrTimetable_id := null,
						EvnPrescr_id := null,
						LpuSection_uid := :LpuSection_id,
						MedPersonal_id := :MedPersonal_id,
						MedStaffFact_id := :MedStaffFact_id,
						Diag_id := :Diag_id,
						pmUser_id := :pmUser_id
            	);
            	";
				$result = $this->db->query($query, array(
					'EvnUslugaDispOrp_id' => $EvnUslugaDispOrp_id,
					'EvnUslugaDispOrp_pid' => $response[0]['EvnVizitDispOrp_id'],
					'UslugaComplex_id' => $data['UslugaComplex_id'],
					'pmUser_id' => $data['pmUser_id'],
					'Lpu_id' => $data['Lpu_id'],
					'Server_id' => $data['Server_id'],
					'PersonEvn_id' => $data['PersonEvn_id'],
					'LpuSection_id' => (!empty($data['LpuSection_id']) ? $data['LpuSection_id'] : NULL),
					'MedPersonal_id' => (!empty($data['MedPersonal_id']) ? $data['MedPersonal_id'] : NULL),
					'MedStaffFact_id' => (!empty($data['MedStaffFact_id']) ? $data['MedStaffFact_id'] : NULL),
					'Diag_id' => $data['Diag_id'],
				));

				if (!is_object($result)) {
					return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение услуги) (' . __LINE__ . ')'));
				}

				$resp_usl = $result->result('array');

				if (!is_array($resp_usl) || count($resp_usl) == 0) {
					return array(array('Error_Msg' => 'Ошибка при сохранении услуги (' . __LINE__ . ')'));
				} else if (!empty($resp_usl[0]['Error_Msg'])) {
					return array(array('Error_Msg' => $resp_usl[0]['Error_Msg'] . ' (' . __LINE__ . ')'));
				}
			} else if (!empty($EvnUslugaDispOrp_id)) {
				$query = "
				SELECT 
					error_code as \"Error_Code\", 
            		error_message as \"Error_Msg\"
            	FROM p_EvnUslugaDispOrp_del (
            	    	EvnUslugaDispOrp_id := :EvnUslugaDispOrp_id,
						pmUser_id := :pmUser_id
            	);
            	";
				$result = $this->db->query($query, array(
					'EvnUslugaDispOrp_id' => $EvnUslugaDispOrp_id,
					'pmUser_id' => $data['pmUser_id']
				));

				if (!is_object($result)) {
					return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление услуги) (' . __LINE__ . ')'));
				}

				$resp_usl = $result->result('array');

				if (!is_array($resp_usl) || count($resp_usl) == 0) {
					return array(array('Error_Msg' => 'Ошибка при удалении услуги (' . __LINE__ . ')'));
				} else if (!empty($resp_usl[0]['Error_Msg'])) {
					return array(array('Error_Msg' => $resp_usl[0]['Error_Msg'] . ' (' . __LINE__ . ')'));
				}
			}
		}

		return $response;
	}

	/**
	 * Возвращает список всех используемых ключей атрибутов объекта
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();
		$arr[self::ID_KEY]['alias'] = 'EvnPLDispOrp_id';
		$arr[self::ID_KEY]['label'] = 'Идентификатор талона диспансеризации';
		$arr['childstatustype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'ChildStatusType_id',
			'label' => 'Статус ребёнка',
			'save' => 'required',
			'type' => 'id'
		);
		$arr['istwostage'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPLDispOrp_IsTwoStage',
			'label' => 'Направлен на 2 этап',
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
		return 9;
	}

	/**
	 * Определение кода класса события
	 * @return string
	 */
	static function evnClassSysNick()
	{
		return 'EvnPLDispOrp';
	}
}

?>
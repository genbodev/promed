<?php	defined('BASEPATH') or die ('No direct script access allowed');
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
		$this->inputRules['searchEvnPLDispOrp'] = array_merge($this->inputRules['searchEvnPLDispOrp'],getAddressSearchFilter());
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
	 * Удаление атрибутов
	 */
	function deleteAttributes($attr, $EvnPLDispOrp_id, $pmUser_id, $DispClass_id) {
		// Сперва получаем список
		switch ( $attr ) {
			case 'EvnUslugaDispOrp':
				$query = "
					select
						EUDO.EvnUslugaDispOrp_id as id
					from
						v_EvnUslugaDispOrp EUDO with (nolock)
						outer apply (
							select top 1
								t2.DopDispInfoConsent_IsAgree,
								t2.DopDispInfoConsent_IsEarlier
							from v_SurveyTypeLink t1 with (nolock)
								left join v_DopDispInfoConsent t2 with (nolock) on t2.SurveyTypeLink_id = t1.SurveyTypeLink_id
									and t2.EvnPLDisp_id = :EvnPLDispOrp_id
							where t1.UslugaComplex_id = EUDO.UslugaComplex_id
								and ISNULL(t1.DispClass_id, 3) = :DispClass_id
							order by t2.DopDispInfoConsent_id desc
						) DDIC
					where
						EUDO.EvnUslugaDispOrp_rid = :EvnPLDispOrp_id
						and ISNULL(DDIC.DopDispInfoConsent_IsAgree, 1) = 1
						and ISNULL(DDIC.DopDispInfoConsent_IsEarlier, 1) = 1
						and ISNULL(EUDO.EvnUslugaDispOrp_IsVizitCode, 1) = 1
				";
				break;

			case 'EvnVizitDispOrp':
				$query = "
					select
						EVDO.EvnVizitDispOrp_id as id
					from
						v_EvnVizitDispOrp EVDO with (nolock)
						left join v_DopDispInfoConsent DDIC with (nolock) on DDIC.DopDispInfoConsent_id = EVDO.DopDispInfoConsent_id 
						left join v_SurveyTypeLink STL with (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
					where
						EVDO.EvnVizitDispOrp_pid = :EvnPLDispOrp_id
						and ISNULL(DDIC.DopDispInfoConsent_IsAgree, 1) = 1
						and ISNULL(DDIC.DopDispInfoConsent_IsEarlier, 1) = 1
						and ISNULL(STL.DispClass_id, 3) = :DispClass_id
				";
				break;

			case 'DopDispInfoConsent':
				$query = "
					select DDIC.DopDispInfoConsent_id as id
					from v_DopDispInfoConsent DDIC with (nolock)
						inner join v_SurveyTypeLink STL with (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
						inner join v_SurveyType ST with (nolock) on ST.SurveyType_id = STL.SurveyType_id
					where DDIC.EvnPLDisp_id = :EvnPLDispOrp_id
						and ST.SurveyType_Code >= 2
				";
				break;

			default:
				$query = "
					select " . $attr . "_id as id
					from v_" . $attr . " with (nolock)
					where EvnPLDisp_id = :EvnPLDispOrp_id
				";
				break;
		}

		if ( !empty($query) ) {
			$result = $this->db->query($query, array('EvnPLDispOrp_id' => $EvnPLDispOrp_id, 'DispClass_id' => $DispClass_id));

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
							" . (in_array($attr, array('EvnUslugaDispOrp', 'EvnVizitDispOrp')) ? "@pmUser_id = :pmUser_id," : "") . "
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
		}

		return '';
	}

	/**
	 * Удаление карты диспансеризации
	 */
	function deleteEvnPLDispOrp($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_EvnPLDispOrp_del
				@EvnPLDispOrp_id = :EvnPLDispOrp_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, array(
			'EvnPLDispOrp_id' => $data['EvnPLDispOrp_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
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
				' . (count($data['session']['linkedLpuIdList']) > 1 ? 'when EPLDD.Lpu_id in (' . implode(',', $data['session']['linkedLpuIdList']) . ') and ISNULL(EPLDD.EvnPLDispOrp_IsTransit, 1) = 2 then 1' : '') . '
				else 0
			end = 1
		';

		if ( $data['session']['region']['nick'] == 'ekb' ) {
			$accessType .= " and ISNULL(EPLDD.EvnPLDispOrp_isPaid, 1) = 1";
		}
		if ($data['session']['region']['nick'] == 'pskov') {
			$accessType .= "and ISNULL(EPLDD.EvnPLDispOrp_isPaid, 1) = 1
			 	and not exists(
					select top 1 RD.Registry_id
					from r60.v_RegistryData RD with(nolock)
						inner join v_Registry R with(nolock) on R.Registry_id = RD.Registry_id
						inner join v_RegistryStatus RS with(nolock) on RS.RegistryStatus_id = R.RegistryStatus_id
					where
						RD.Evn_id = EPLDD.EvnPLDispOrp_id
						and RS.RegistryStatus_SysNick not in ('work','paid')
				)
			";
		}

		if ( $data['session']['region']['nick'] == 'buryatiya' ) {
			$additionalFields[] = "eudo_vizit.UslugaComplex_id";
			$additionalJoins[] = "
				outer apply(
					select top 1 UslugaComplex_id
					from v_EvnUslugaDispOrp (nolock)
					where
						EvnUslugaDispOrp_IsVizitCode = 2
						and EvnUslugaDispOrp_pid = EPLDD.EvnPLDispOrp_id
				) eudo_vizit
			";
		}

		$query = "
			SELECT TOP 1
				case when {$accessType} then 'edit' else 'view' end as accessType,
				EPLDD.EvnPLDispOrp_id,
				ISNULL(EPLDD.EvnPLDispOrp_IsPaid, 1) as EvnPLDispOrp_IsPaid,
				ISNULL(EPLDD.EvnPLDispOrp_IndexRep, 0) as EvnPLDispOrp_IndexRep,
				ISNULL(EPLDD.EvnPLDispOrp_IndexRepInReg, 1) as EvnPLDispOrp_IndexRepInReg,
				EPLDD.EvnPLDispOrp_fid,
				EPLDD.EvnPLDispOrp_IsFinish,
				EPLDD.EvnPLDispOrp_IsTwoStage,
				EPLDD.ChildStatusType_id,
				convert(varchar(10), EPLDD.EvnPLDispOrp_setDate, 104) as EvnPLDispOrp_setDate,
				convert(varchar(10), EPLDD_FIR.EvnPLDispOrp_setDate, 104) as EvnPLDispOrp_firSetDate,
				convert(varchar(10), EPLDD.EvnPLDispOrp_consDT, 104) as EvnPLDispOrp_consDate,
				--Okved_id as EvnPLDispOrp_Okved_id,
				EPLDD.AttachType_id,
				EPLDD.Lpu_aid,
				case when EPLDD.EvnPLDispOrp_IsMobile = 2 then 1 else 0 end as EvnPLDispOrp_IsMobile,
				case when EPLDD.EvnPLDispOrp_IsOutLpu = 2 then 1 else 0 end as EvnPLDispOrp_IsOutLpu,
				EPLDD.Lpu_mid,
				EPLDD.PersonEvn_id,
				EPLDD.Server_id,
				ISNULL(EPLDD.DispClass_id,3) as DispClass_id,
				EPLDD.PayType_id,
				AH.AssessmentHealth_Weight,
				AH.AssessmentHealth_Height,
				AH.AssessmentHealth_Head,
				case when AH.WeightAbnormType_id is null then 1 else 2 end as WeightAbnormType_YesNo,
				AH.WeightAbnormType_id,
				case when AH.HeightAbnormType_id is null then 1 else 2 end as HeightAbnormType_YesNo,
				AH.HeightAbnormType_id,
				AH.AssessmentHealth_Gnostic,
				AH.AssessmentHealth_Motion,
				AH.AssessmentHealth_Social,
				AH.AssessmentHealth_Speech,
				AH.AssessmentHealth_P,
				AH.AssessmentHealth_Ax,
				AH.AssessmentHealth_Fa,
				AH.AssessmentHealth_Ma,
				AH.AssessmentHealth_Me,
				AH.AssessmentHealth_Years,
				AH.AssessmentHealth_Month,
				case when AH.AssessmentHealth_IsRegular = 2 then 1 else 0 end as AssessmentHealth_IsRegular,
				case when AH.AssessmentHealth_IsIrregular = 2 then 1 else 0 end as AssessmentHealth_IsIrregular,
				case when AH.AssessmentHealth_IsAbundant = 2 then 1 else 0 end as AssessmentHealth_IsAbundant,
				case when AH.AssessmentHealth_IsModerate = 2 then 1 else 0 end as AssessmentHealth_IsModerate,
				case when AH.AssessmentHealth_IsScanty = 2 then 1 else 0 end as AssessmentHealth_IsScanty,
				case when AH.AssessmentHealth_IsPainful = 2 then 1 else 0 end as AssessmentHealth_IsPainful,
				case when AH.AssessmentHealth_IsPainless = 2 then 1 else 0 end as AssessmentHealth_IsPainless,
				AH.InvalidType_id,
				convert(varchar(10), AH.AssessmentHealth_setDT, 104) as AssessmentHealth_setDT,
				convert(varchar(10), AH.AssessmentHealth_reExamDT, 104) as AssessmentHealth_reExamDT,
				AH.InvalidDiagType_id,
				case when AH.AssessmentHealth_IsMental = 2 then 1 else 0 end as AssessmentHealth_IsMental,
				case when AH.AssessmentHealth_IsOtherPsych = 2 then 1 else 0 end as AssessmentHealth_IsOtherPsych,
				case when AH.AssessmentHealth_IsLanguage = 2 then 1 else 0 end as AssessmentHealth_IsLanguage,
				case when AH.AssessmentHealth_IsVestibular = 2 then 1 else 0 end as AssessmentHealth_IsVestibular,
				case when AH.AssessmentHealth_IsVisual = 2 then 1 else 0 end as AssessmentHealth_IsVisual,
				case when AH.AssessmentHealth_IsMeals = 2 then 1 else 0 end as AssessmentHealth_IsMeals,
				case when AH.AssessmentHealth_IsMotor = 2 then 1 else 0 end as AssessmentHealth_IsMotor,
				case when AH.AssessmentHealth_IsDeform = 2 then 1 else 0 end as AssessmentHealth_IsDeform,
				case when AH.AssessmentHealth_IsGeneral = 2 then 1 else 0 end as AssessmentHealth_IsGeneral,
				convert(varchar(10), AH.AssessmentHealth_ReabDT, 104) as AssessmentHealth_ReabDT,
				AH.AssessmentHealth_HealthRecom,
				AH.RehabilitEndType_id,
				CASE WHEN AH.AssessmentHealth_id IS NULL THEN 1 else AH.ProfVaccinType_id end as ProfVaccinType_id,
				AH.HealthKind_id,
				AH.NormaDisturbanceType_id,
				AH.NormaDisturbanceType_uid,
				AH.NormaDisturbanceType_eid,
				convert(varchar(10), ecp.EvnCostPrint_setDT, 104) as EvnCostPrint_setDT,
				ecp.EvnCostPrint_Number,
				ecp.EvnCostPrint_IsNoPrint,
				EPLDD.EvnPLDispOrp_IsSuspectZNO,
				EPLDD.Diag_spid,
				AH.AssessmentHealth_id
				" . (count($additionalFields) > 0 ? "," . implode(", ", $additionalFields) : "") . "
			FROM
				v_EvnPLDispOrp EPLDD (nolock)
				left join v_EvnCostPrint ecp (nolock) on ecp.Evn_id = EPLDD.EvnPLDispOrp_id
				left join v_EvnPLDispOrp EPLDD_FIR (nolock) on EPLDD_FIR.EvnPLDispOrp_id = EPLDD.EvnPLDispOrp_fid
				outer apply(
					select top 1 * from v_AssessmentHealth (nolock) where EvnPLDisp_id = EPLDD.EvnPLDispOrp_id
				) AH
				" . (count($additionalJoins) > 0 ? implode(" ", $additionalJoins) : "") . "
			WHERE
				EPLDD.EvnPLDispOrp_id = :EvnPLDispOrp_id
		";
		$result = $this->db->query($query, array('EvnPLDispOrp_id' => $data['EvnPLDispOrp_id'], 'Lpu_id' => $data['Lpu_id']));

		if (is_object($result))
		{
			$resp = $result->result('array');
			$AssessmentHealthVaccinData = array();
			if (!empty($resp[0]['AssessmentHealth_id'])) {
				// получаем данные прививок
				$query = "
					select
						AssessmentHealthVaccin_id,
						VaccinType_id
					from
						v_AssessmentHealthVaccin (nolock)
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
	 * Возвращает данные
	 */
	function getEvnPLDispOrpFields($data)
	{
		$query = "
			SELECT TOP 1
				rtrim(lp.Lpu_Name) as Lpu_Name,
				rtrim(isnull(addr0.Address_Address, '')) as Lpu_Address,
				rtrim(isnull(lp1.Lpu_Name, '')) as Lpu_AName,
				rtrim(isnull(addr1.Address_Address, '')) as Lpu_AAddress,
				rtrim(lp.Lpu_OGRN) as Lpu_OGRN,
				isnull(pc.PersonCard_Code, '') as PersonCard_Code,
				ps.Person_SurName + ' ' + ps.Person_FirName + ' ' + isnull(ps.Person_SecName, '') as Person_FIO,
				sx.Sex_Name,
				sx.Sex_id,
				CST.ChildStatusType_Code,
				case when isnull(CCT.CategoryChildType_Code,1) = 5 then 2
					else case when CCT.CategoryChildType_Code = 6 then 3
					else case when CCT.CategoryChildType_Code = 7 then 4
					end end end as CategoryChildType_Code,

			    ISNULL(CAST(AH.AssessmentHealth_Weight as float),0) as AssesmentHealth_Weight,
				ISNULL(AH.AssessmentHealth_Height,0) as AssesmentHealth_Height,
				ISNULL(AH.AssessmentHealth_Head,0) as AssessmentHealth_Head,
				ISNULL(AH.WeightAbnormType_id,0) as WeightAbnormType_id,
				ISNULL(AH.HeightAbnormType_id,0) as HeightAbnormType_id,

				ISNULL(AH.AssessmentHealth_Gnostic,0) as AssessmentHealth_Gnostic,
				ISNULL(AH.AssessmentHealth_Motion,0) as AssessmentHealth_Motion,
				ISNULL(AH.AssessmentHealth_Social,0) as AssessmentHealth_Social,
				ISNULL(AH.AssessmentHealth_Speech,0) as AssessmentHealth_Speech,
				ISNULL(AH.NormaDisturbanceType_id,0) as NormaDisturbanceType_id,
				ISNULL(AH.NormaDisturbanceType_uid,0) as NormaDisturbanceType_uid,
				ISNULL(AH.NormaDisturbanceType_eid,0) as NormaDisturbanceType_eid,

				ISNULL(AH.AssessmentHealth_P,0) as AssessmentHealth_P,
				ISNULL(AH.AssessmentHealth_Ax,0) as AssessmentHealth_Ax,
				ISNULL(AH.AssessmentHealth_Fa,0) as AssessmentHealth_Fa,
				ISNULL(AH.AssessmentHealth_Ma,0) as AssessmentHealth_Ma,
				ISNULL(AH.AssessmentHealth_Me,0) as AssessmentHealth_Me,

				ISNULL(AH.AssessmentHealth_Years,1) as AssessmentHealth_Years,
				ISNULL(AH.AssessmentHealth_Month,1) as AssessmentHealth_Month,
				ISNULL(AH.AssessmentHealth_IsRegular,1) as AssessmentHealth_IsRegular,
				ISNULL(AH.AssessmentHealth_IsIrregular,1) as AssessmentHealth_IsIrregular,
				ISNULL(AH.AssessmentHealth_IsAbundant,1) as AssessmentHealth_IsAbundant,
				ISNULL(AH.AssessmentHealth_IsModerate,1) as AssessmentHealth_IsModerate,
				ISNULL(AH.AssessmentHealth_IsScanty,1) as AssessmentHealth_IsScanty,
				ISNULL(AH.AssessmentHealth_IsPainful,1) as AssessmentHealth_IsPainful,
				ISNULL(AH.AssessmentHealth_IsPainless,1) as AssessmentHealth_IsPainless,
                ISNULL(AH.HealthKind_id,0) as HealthKind_id,
                ISNULL(AH.InvalidType_id,1) as InvalidType_id,
                convert(varchar(10), AH.AssessmentHealth_setDT, 104) as AssessmentHealth_setDT,
				convert(varchar(10), AH.AssessmentHealth_reExamDT, 104) as ssessmentHealth_reExamDT,
				ISNULL(AH.InvalidDiagType_id,0) as InvalidDiagType_id,

				ISNULL(AH.AssessmentHealth_IsMental,0) as AssessmentHealth_IsMental,
				ISNULL(AH.AssessmentHealth_IsOtherPsych,0) as AssessmentHealth_IsOtherPsych,
				ISNULL(AH.AssessmentHealth_IsLanguage,0) as AssessmentHealth_IsLanguage,
				ISNULL(AH.AssessmentHealth_IsVestibular,0) as AssessmentHealth_IsVestibular,
				ISNULL(AH.AssessmentHealth_IsVisual,0) as AssessmentHealth_IsVisual,
				ISNULL(AH.AssessmentHealth_IsMeals,0) as AssessmentHealth_IsMeals,
				ISNULL(AH.AssessmentHealth_IsMotor,0) as AssessmentHealth_IsMotor,
				ISNULL(AH.AssessmentHealth_IsDeform,0) as AssessmentHealth_IsDeform,
				ISNULL(AH.AssessmentHealth_IsGeneral,0) as AssessmentHealth_IsGeneral,

				convert(varchar(10), AH.AssessmentHealth_ReabDT, 104) as AssessmentHealth_ReabDT,
				ISNULL(AH.RehabilitEndType_id,0) as RehabilitEndType_id,
				ISNULL(AH.ProfVaccinType_id,0) as ProfVaccinType_id,

				isnull(osmo.OrgSMO_Nick, '') as OrgSMO_Nick,
				isnull(case when pls.PolisType_id = 4 then '' else pls.Polis_Ser end, '') as Polis_Ser,
				isnull(case when pls.PolisType_id = 4 then ps.Person_EdNum else pls.Polis_Num end, '') as Polis_Num,
				isnull(osmo.OrgSMO_Name, '') as OrgSMO_Name,
				convert(varchar(10), ps.Person_BirthDay, 104) as Person_BirthDay,
				convert(varchar(20), ps.Person_BirthDay, 120) as Person_BirthDay2,
				isnull(ps.Person_Snils,'') as Person_Snils,
				isnull(addr.Address_Address, '') as Person_Address,
				jborg.Org_Nick,
				atype.AttachType_Name,
				convert(varchar(10),  EPLDD.EvnPLDispOrp_setDate, 104) as EvnPLDispOrp_setDate,
				convert(varchar(20),  EPLDD.EvnPLDispOrp_setDate, 120) as EvnPLDispOrp_setDate2,
				convert(varchar(10),  EPLDD.EvnPLDispOrp_disDate, 104) as EvnPLDispOrp_disDate,
				null as EvnPLDispOrp_IsBud,
				OSTAC.Org_Name as OrgStac_Name,
				convert(varchar(10),  PDO.PersonDispOrp_setDate, 104) as PersonDispOrp_setDate,
				PDO.DisposalCause_id,
				convert(varchar(10),  PDO.PersonDispOrp_DisposDate, 104) as PersonDispOrp_DisposDate,
				
				ISNULL(AH.AssessmentHealth_HealthRecom,'') as AssessmentHealth_HealthRecom,
				ISNULL(AH.AssessmentHealth_DispRecom,'') as AssessmentHealth_DispRecom
			FROM
				v_EvnPLDispOrp EPLDD (nolock)
				--left join v_PersonDispOrp PDO (nolock) on PDO.Person_id = EPLDD.Person_id
				left join v_PersonDispOrp PDO with (nolock) on (PDO.Person_id = EPLDD.Person_id and PDO.PersonDispOrp_Year = YEAR(EPLDD.EvnPLDispOrp_setDate))
				left join v_Org OSTAC with (nolock) on OSTAC.Org_id = PDO.Org_id
				left join v_AssessmentHealth AH (nolock) on AH.EvnPLDisp_id = EPLDD.EvnPLDispOrp_id
				left join v_CategoryChildType CCT (nolock) on CCT.CategoryChildType_id = PDO.CategoryChildType_id
				inner join v_Lpu lp (nolock) on lp.Lpu_id = EPLDD.Lpu_id
				left join v_Lpu lp1 (nolock) on lp1.Lpu_id = EPLDD.Lpu_aid
				left join Address addr1 (nolock) on addr1.Address_id = lp1.UAddress_id
				left join Address addr0 (nolock) on addr0.Address_id = lp.UAddress_id
				left join v_PersonCard pc (nolock) on pc.Person_id = EPLDD.Person_id and pc.LpuAttachType_id = 1
				inner join v_PersonState ps (nolock) on ps.Person_id = EPLDD.Person_id
				inner join Sex sx (nolock) on sx.Sex_id = ps.Sex_id
				left join ChildStatusType CST (nolock) on CST.ChildStatusType_id = EPLDD.ChildStatusType_id
				left join Polis pls (nolock) on pls.Polis_id = ps.Polis_id
				left join v_OrgSmo osmo (nolock) on osmo.OrgSmo_id = pls.OrgSmo_id
				left join Address addr (nolock) on addr.Address_id = ps.PAddress_id
				left join Job jb (nolock) on jb.Job_id = ps.Job_id
				left join Org jborg (nolock) on jborg.Org_id = jb.Org_id
				left join AttachType atype (nolock) on atype.AttachType_id = EPLDD.AttachType_id
			WHERE
				(1 = 1)
				and EPLDD.EvnPLDispOrp_id = ?
		";
		$result = $this->db->query($query, array($data['EvnPLDispOrp_id']));

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
	 * Входящие данные: $data['EvnPLDispOrp_id']
	 * На выходе: ассоциативный массив результатов запроса
	 */
	function loadEvnVizitDispOrpGrid($data)
	{

		$query = "
			select distinct
				EVZDD.EvnVizitDispOrp_id,
				EVZDD.Server_id,
				EVZDD.PersonEvn_id,
				convert(varchar(10), EVZDD.EvnVizitDispOrp_setDate, 104) as EvnVizitDispOrp_setDate,
				EVZDD.EvnVizitDispOrp_setTime,
				convert(varchar(10), EVZDD.EvnVizitDispOrp_disDate, 104) as EvnVizitDispOrp_disDate,
				EVZDD.EvnVizitDispOrp_disTime,
				RTRIM(LS.LpuSection_Name) as LpuSection_Name,
				RTRIM(MP.Person_Fio) as MedPersonal_Fio,
				RTRIM(DDS.OrpDispSpec_Name) as OrpDispSpec_Name,
				DDS.OrpDispSpec_Code,
				RTRIM(D.Diag_Code) as Diag_Code,
				EVZDD.MedPersonal_id,
				EVZDD.MedStaffFact_id,
				EVZDD.OrpDispSpec_id,
				EVZDD.UslugaComplex_id,
				EVZDD.DopDispInfoConsent_id,
				EVZDD.LpuSection_id,
				EVZDD.Lpu_id as Lpu_uid,
				EVZDD.LpuSectionProfile_id,
				EVZDD.MedSpecOms_id,
				EVZDD.Diag_id,
				EVZDD.TumorStage_id,
				TS.TumorStage_Name,
				EVZDD.DopDispDiagType_id,
				EVZDD.DopDispAlien_id,
				1 as Record_Status
			from v_EvnVizitDispOrp EVZDD (nolock)
				left join LpuSection LS (nolock) on LS.LpuSection_id = EVZDD.LpuSection_id
				left join v_MedPersonal MP (nolock) on MP.MedPersonal_id = EVZDD.MedPersonal_id
				left join OrpDispSpec DDS (nolock) on DDS.OrpDispSpec_id = EVZDD.OrpDispSpec_id
				left join Diag D (nolock) on D.Diag_id = EVZDD.Diag_id
				left join v_TumorStage TS (nolock) on TS.TumorStage_id = EVZDD.TumorStage_id
			where EVZDD.EvnVizitDispOrp_pid = ?
		";
		$result = $this->db->query($query, array($data['EvnPLDispOrp_id']));

		if (is_object($result))
		{
			$resp = $result->result('array');
			foreach($resp as &$respone) {
				$respone['EvnDiagDopDispGridData'] = array();
				// для каждого осмотра надо подгрузить сопутствующие диагнозы
				$query = "
					select
						EDDD.EvnDiagDopDisp_id,
						EDDD.Diag_id,
						EDDD.DeseaseDispType_id,
						D.Diag_Code,
						D.Diag_Name,
						DDT.DeseaseDispType_Name,
						1 as Record_Status
					from
						v_EvnDiagDopDisp EDDD (nolock)
						left join v_DeseaseDispType DDT (nolock) on DDT.DeseaseDispType_id = EDDD.DeseaseDispType_id
						left join v_Diag D (nolock) on D.Diag_id = EDDD.Diag_id
					where
						EDDD.EvnDiagDopDisp_pid = :EvnDiagDopDisp_pid
						and EDDD.DiagSetClass_id = 3
					order by
						EDDD.EvnDiagDopDisp_id
				";

				$result_dddgd = $this->db->query($query, array(
					'EvnDiagDopDisp_pid' => $respone['EvnVizitDispOrp_id']
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
	 * Получение списка осмотров врача-специалиста в талоне по ДД
	 * Входящие данные: $data['EvnPLDispOrp_id']
	 * На выходе: ассоциативный массив результатов запроса
	 */
	function loadEvnVizitDispOrpSecGrid($data)
	{

		$query = "
			select
				EVZDD.EvnVizitDispOrp_id,
				EVZDD.Server_id,
				EVZDD.PersonEvn_id,
				convert(varchar(10), EVZDD.EvnVizitDispOrp_setDate, 104) as EvnVizitDispOrp_setDate,
				EVZDD.EvnVizitDispOrp_setTime,
				convert(varchar(10), EVZDD.EvnVizitDispOrp_disDate, 104) as EvnVizitDispOrp_disDate,
				EVZDD.EvnVizitDispOrp_disTime,
				RTRIM(LS.LpuSection_Name) as LpuSection_Name,
				RTRIM(MP.Person_Fio) as MedPersonal_Fio,
				RTRIM(UC.UslugaComplex_Name) as UslugaComplex_Name,
				UC.UslugaComplex_Code,
				RTRIM(D.Diag_Code) as Diag_Code,
				EVZDD.MedPersonal_id,
				EVZDD.MedStaffFact_id,
				EVZDD.UslugaComplex_id,
				EVZDD.DopDispInfoConsent_id,
				EVZDD.LpuSection_id,
				EVZDD.Lpu_id as Lpu_uid,
				EVZDD.LpuSectionProfile_id,
				EVZDD.MedSpecOms_id,
				EVZDD.Diag_id,
				EVZDD.DopDispDiagType_id,
				EVZDD.DopDispAlien_id,
				DDA.DopDispAlien_Name,
				1 as Record_Status
			from v_EvnVizitDispOrp EVZDD (nolock)
				left join LpuSection LS (nolock) on LS.LpuSection_id = EVZDD.LpuSection_id
				left join v_MedPersonal MP (nolock) on MP.MedPersonal_id = EVZDD.MedPersonal_id
				left join v_UslugaComplex UC (nolock) on UC.UslugaComplex_id = EVZDD.UslugaComplex_id
				left join v_DopDispAlien DDA (nolock) on DDA.DopDispAlien_id = EVZDD.DopDispAlien_id
				left join Diag D (nolock) on D.Diag_id = EVZDD.Diag_id
			where EVZDD.EvnVizitDispOrp_pid = ?
		";
		$result = $this->db->query($query, array($data['EvnPLDispOrp_id']));

		if (is_object($result))
		{
			$resp = $result->result('array');
			foreach($resp as &$respone) {
				$respone['EvnDiagDopDispGridData'] = array();
				// для каждого осмотра надо подгрузить сопутствующие диагнозы
				$query = "
					select
						EDDD.EvnDiagDopDisp_id,
						EDDD.Diag_id,
						EDDD.DeseaseDispType_id,
						D.Diag_Code,
						D.Diag_Name,
						DDT.DeseaseDispType_Name,
						1 as Record_Status
					from
						v_EvnDiagDopDisp EDDD (nolock)
						left join v_DeseaseDispType DDT (nolock) on DDT.DeseaseDispType_id = EDDD.DeseaseDispType_id
						left join v_Diag D (nolock) on D.Diag_id = EDDD.Diag_id
					where
						EDDD.EvnDiagDopDisp_pid = :EvnDiagDopDisp_pid
						and EDDD.DiagSetClass_id = 3
					order by
						EDDD.EvnDiagDopDisp_id
				";

				$result_dddgd = $this->db->query($query, array(
					'EvnDiagDopDisp_pid' => $respone['EvnVizitDispOrp_id']
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
	 * @param $data
	 * @return bool
	 */
	function loadEvnDiagAndRecomendation($data){
		$filter = " and EVZDD.EvnVizitDispOrp_pid = :EvnPLDisp_id ";
		$query = "
        select EVDO.DispSurveilType_id,
						ISNULL(EVDO.EvnVizitDispOrp_IsVMP,1) as EvnVizitDisp_IsVMP,
						EVDO.EvnVizitDispOrp_IsFirstTime as EvnVizitDisp_IsFirstTime,
						ISNULL(MC1.ConditMedCareType_nid,1) as ConditMedCareType1_nid,
						MC1.PlaceMedCareType_nid as PlaceMedCareType1_nid,
						MC1.ConditMedCareType_id as ConditMedCareType1_id,
						MC1.PlaceMedCareType_id as PlaceMedCareType1_id,
						MC1.LackMedCareType_id as LackMedCareType1_id,
						MC2.ConditMedCareType_nid as ConditMedCareType2_nid,
						MC2.PlaceMedCareType_nid as PlaceMedCareType2_nid,
						ISNULL(MC2.ConditMedCareType_id,1) as ConditMedCareType2_id,
						MC2.PlaceMedCareType_id as PlaceMedCareType2_id,
						MC2.LackMedCareType_id as LackMedCareType2_id,
						MC3.ConditMedCareType_nid as ConditMedCareType3_nid,
						MC3.PlaceMedCareType_nid as PlaceMedCareType3_nid,
						ISNULL(MC3.ConditMedCareType_id,1) as ConditMedCareType3_id,
						MC3.PlaceMedCareType_id as PlaceMedCareType3_id,
						MC3.LackMedCareType_id as LackMedCareType3_id,
						D.Diag_Code
                        from v_EvnVizitDispOrp EVZDD with (nolock)
                        left join OrpDispSpec ODS (nolock) on ODS.OrpDispSpec_id = EVZDD.OrpDispSpec_id
                        left join v_Diag D (nolock) on D.Diag_id = EVZDD.Diag_id
                        left join v_EvnVizitDispOrp EVDO with (nolock) on EVDO.EvnVizitDispOrp_id = EVZDD.EvnVizitDispOrp_id
                        outer apply(
							select top 1 * from v_MedCare MC (nolock) where MC.MedCareType_id = 1 and MC.EvnVizitDisp_id = EVDO.EvnVizitDispOrp_id
						) MC1
						-- лечение
						outer apply(
							select top 1 * from v_MedCare MC (nolock) where MC.MedCareType_id = 2 and MC.EvnVizitDisp_id = EVDO.EvnVizitDispOrp_id
						) MC2
						-- медицинская реабилитация / санаторно-курортное лечение
						outer apply(
							select top 1 * from v_MedCare MC (nolock) where MC.MedCareType_id = 3 and MC.EvnVizitDisp_id = EVDO.EvnVizitDispOrp_id
						) MC3
                        where (1=1) {$filter}
                        and LEFT(D.Diag_Code,1) <> 'Z'
        ";
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
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
				EVZDD.EvnVizitDispOrp_id,
				D.Diag_id,
				D.Diag_Code + '. ' + D.Diag_Name as Diag_Name,
				D.Diag_Code,
				ODS.OrpDispSpec_Name
			from v_EvnVizitDispOrp EVZDD (nolock)
				left join OrpDispSpec ODS (nolock) on ODS.OrpDispSpec_id = EVZDD.OrpDispSpec_id
				left join v_Diag D (nolock) on D.Diag_id = EVZDD.Diag_id
			where EVZDD.EvnVizitDispOrp_pid = ? and LEFT(D.Diag_Code,1) <> 'Z'
		";
		$result = $this->db->query($query, array($data['EvnPLDispOrp_id']));

		if (is_object($result))
		{
			$resp = $result->result('array');
			foreach($resp as &$item) {
				// для каждой строки получаем данные формы "Состояние здоровья: Редактирование" и запихиваем в JSON
				$query = "
					select top 1
						EVDO.DopDispDiagType_id,
						EVDO.DispSurveilType_id,
						ISNULL(EVDO.EvnVizitDispOrp_IsVMP,1) as EvnVizitDisp_IsVMP,
						EVDO.EvnVizitDispOrp_IsFirstTime as EvnVizitDisp_IsFirstTime,
						ISNULL(MC1.ConditMedCareType_nid,1) as ConditMedCareType1_nid,
						MC1.PlaceMedCareType_nid as PlaceMedCareType1_nid,
						MC1.ConditMedCareType_id as ConditMedCareType1_id,
						MC1.PlaceMedCareType_id as PlaceMedCareType1_id,
						MC1.LackMedCareType_id as LackMedCareType1_id,
						MC2.ConditMedCareType_nid as ConditMedCareType2_nid,
						MC2.PlaceMedCareType_nid as PlaceMedCareType2_nid,
						ISNULL(MC2.ConditMedCareType_id,1) as ConditMedCareType2_id,
						MC2.PlaceMedCareType_id as PlaceMedCareType2_id,
						MC2.LackMedCareType_id as LackMedCareType2_id,
						MC3.ConditMedCareType_nid as ConditMedCareType3_nid,
						MC3.PlaceMedCareType_nid as PlaceMedCareType3_nid,
						ISNULL(MC3.ConditMedCareType_id,1) as ConditMedCareType3_id,
						MC3.PlaceMedCareType_id as PlaceMedCareType3_id,
						MC3.LackMedCareType_id as LackMedCareType3_id
					from v_EvnVizitDispOrp EVDO (nolock)
						left join v_Diag D (nolock) on D.Diag_id = EVDO.Diag_id
						-- дополнительные консультации и исследования
						outer apply(
							select top 1 * from v_MedCare MC (nolock) where MC.MedCareType_id = 1 and MC.EvnVizitDisp_id = EVDO.EvnVizitDispOrp_id
						) MC1
						-- лечение
						outer apply(
							select top 1 * from v_MedCare MC (nolock) where MC.MedCareType_id = 2 and MC.EvnVizitDisp_id = EVDO.EvnVizitDispOrp_id
						) MC2
						-- медицинская реабилитация / санаторно-курортное лечение
						outer apply(
							select top 1 * from v_MedCare MC (nolock) where MC.MedCareType_id = 3 and MC.EvnVizitDisp_id = EVDO.EvnVizitDispOrp_id
						) MC3
					where EVDO.EvnVizitDispOrp_id = :EvnVizitDispOrp_id
				";
				$resultmc = $this->db->query($query, array('EvnVizitDispOrp_id' => $item['EvnVizitDispOrp_id']));
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
	 * Получение списка "Диагнозы и рекоменации"
	 * Входящие данные: $data['EvnPLDispOrp_id']
	 * На выходе: ассоциативный массив результатов запроса
	 */
	function loadEvnDiagAndRecomendationSecGrid($data)
	{
		$query = "
			select
				EVZDD.EvnVizitDispOrp_id,
				D.Diag_id,
				D.Diag_Code + '. ' + D.Diag_Name as Diag_Name,
				UC.UslugaComplex_Name
			from v_EvnVizitDispOrp EVZDD (nolock)
				left join v_UslugaComplex UC (nolock) on UC.UslugaComplex_id = EVZDD.UslugaComplex_id
				left join v_Diag D (nolock) on D.Diag_id = EVZDD.Diag_id
			where EVZDD.EvnVizitDispOrp_pid = ? and LEFT(D.Diag_Code,1) <> 'Z'
		";
		$result = $this->db->query($query, array($data['EvnPLDispOrp_id']));

		if (is_object($result))
		{
			$resp = $result->result('array');
			foreach($resp as &$item) {
				// для каждой строки получаем данные формы "Состояние здоровья: Редактирование" и запихиваем в JSON
				$query = "
					select top 1
						EVDO.DispSurveilType_id,
						ISNULL(EVDO.EvnVizitDispOrp_IsVMP,1) as EvnVizitDisp_IsVMP,
						EVDO.EvnVizitDispOrp_IsFirstTime as EvnVizitDisp_IsFirstTime,
						ISNULL(MC1.ConditMedCareType_nid,1) as ConditMedCareType1_nid,
						MC1.PlaceMedCareType_nid as PlaceMedCareType1_nid,
						MC1.ConditMedCareType_id as ConditMedCareType1_id,
						MC1.PlaceMedCareType_id as PlaceMedCareType1_id,
						MC1.LackMedCareType_id as LackMedCareType1_id,
						MC2.ConditMedCareType_nid as ConditMedCareType2_nid,
						MC2.PlaceMedCareType_nid as PlaceMedCareType2_nid,
						ISNULL(MC2.ConditMedCareType_id,1) as ConditMedCareType2_id,
						MC2.PlaceMedCareType_id as PlaceMedCareType2_id,
						MC2.LackMedCareType_id as LackMedCareType2_id,
						MC3.ConditMedCareType_nid as ConditMedCareType3_nid,
						MC3.PlaceMedCareType_nid as PlaceMedCareType3_nid,
						ISNULL(MC3.ConditMedCareType_id,1) as ConditMedCareType3_id,
						MC3.PlaceMedCareType_id as PlaceMedCareType3_id,
						MC3.LackMedCareType_id as LackMedCareType3_id
					from v_EvnVizitDispOrp EVDO (nolock)
						left join v_Diag D (nolock) on D.Diag_id = EVDO.Diag_id
						-- дополнительные консультации и исследования
						outer apply(
							select top 1 * from v_MedCare MC (nolock) where MC.MedCareType_id = 1 and MC.EvnVizitDisp_id = EVDO.EvnVizitDispOrp_id
						) MC1
						-- лечение
						outer apply(
							select top 1 * from v_MedCare MC (nolock) where MC.MedCareType_id = 2 and MC.EvnVizitDisp_id = EVDO.EvnVizitDispOrp_id
						) MC2
						-- медицинская реабилитация / санаторно-курортное лечение
						outer apply(
							select top 1 * from v_MedCare MC (nolock) where MC.MedCareType_id = 3 and MC.EvnVizitDisp_id = EVDO.EvnVizitDispOrp_id
						) MC3
					where EVDO.EvnVizitDispOrp_id = :EvnVizitDispOrp_id
				";
				$resultmc = $this->db->query($query, array('EvnVizitDispOrp_id' => $item['EvnVizitDispOrp_id']));
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
	 * Входящие данные: $data['EvnPLDispOrp_id']
	 * На выходе: ассоциативный массив результатов запроса
	 */
	function loadEvnVizitDispOrpData($data)
	{

		$query = "
			select
				EVZDD.EvnVizitDispOrp_id,
				EVZDD.Server_id,
				EVZDD.PersonEvn_id,
				convert(varchar(10), EVZDD.EvnVizitDispOrp_setDate, 104) as EvnVizitDispOrp_setDate,
				convert(varchar(10), EVZDD.EvnVizitDispOrp_disDate, 104) as EvnVizitDispOrp_disDate,
				RTRIM(MP.Person_Fio) as MedPersonal_Fio,
				RTRIM(isnull(MP.MedPersonal_TabCode, '')) as MedPersonal_TabCode,
				RTRIM(DDS.OrpDispSpec_Name) as OrpDispSpec_Name,
				DDS.OrpDispSpec_Code,
				RTRIM(D.Diag_Code) as Diag_Code,
				EVZDD.MedPersonal_id,
				EVZDD.OrpDispSpec_id,
				EVZDD.DeseaseStage_id,
				EVZDD.HealthKind_id,
				EVZDD.EvnVizitDispOrp_IsSanKur,
				EVZDD.EvnVizitDispOrp_IsOut,
				EVZDD.LpuSection_id,
				EVZDD.Diag_id,
				EVZDD.DopDispDiagType_id,
				EVZDD.DopDispAlien_id,
				1 as Record_Status
			from v_EvnVizitDispOrp EVZDD (nolock)
				left join LpuSection LS (nolock) on LS.LpuSection_id = EVZDD.LpuSection_id
				left join v_MedPersonal MP (nolock) on MP.MedPersonal_id = EVZDD.MedPersonal_id
				left join OrpDispSpec DDS (nolock) on DDS.OrpDispSpec_id = EVZDD.OrpDispSpec_id
				left join Diag D (nolock) on D.Diag_id = EVZDD.Diag_id
			where EVZDD.EvnVizitDispOrp_pid = ?
		";
		$result = $this->db->query($query, array($data['EvnPLDispOrp_id']));

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
	 * Входящие данные: $data['EvnPLDispOrp_id']
	 * На выходе: ассоциативный массив результатов запроса
	 */
	function loadEvnUslugaDispOrpGrid($data)
	{

		$query = "
			select
				EUDD.EvnUslugaDispOrp_id,
				EUDD.Server_id,
				EUDD.PersonEvn_id,
				convert(varchar(10), EUDD.EvnUslugaDispOrp_setDate, 104) as EvnUslugaDispOrp_setDate,
				EUDD.EvnUslugaDispOrp_setTime,
				convert(varchar(10), EUDD.EvnUslugaDispOrp_disDate, 104) as EvnUslugaDispOrp_disDate,
				EUDD.EvnUslugaDispOrp_disTime,
				convert(varchar(10), EUDD.EvnUslugaDispOrp_didDate, 104) as EvnUslugaDispOrp_didDate,
				EUDD.OrpDispUslugaType_id,
				RTRIM(DDUT.OrpDispUslugaType_Name) as OrpDispUslugaType_Name,
				EUDD.LpuSection_uid as LpuSection_id,
				EUDD.Lpu_uid,
				EUDD.LpuSectionProfile_id,
				EUDD.MedSpecOms_id,
				RTRIM(LS.LpuSection_Name) as LpuSection_Name,
				EUDD.MedPersonal_id,
				EUDD.MedStaffFact_id,
				RTRIM(MP.Person_Fio) as MedPersonal_Fio,
				EUDD.UslugaComplex_id as UslugaComplex_id,
				RTRIM(UC.UslugaComplex_Name) as UslugaComplex_Name,
				RTRIM(UC.UslugaComplex_Code) as UslugaComplex_Code,
				EUDD.ExaminationPlace_id,
				EUDD.EvnUslugaDispOrp_Result,
				1 as Record_Status
			from v_EvnUslugaDispOrp EUDD (nolock)
				left join OrpDispUslugaType DDUT (nolock) on DDUT.OrpDispUslugaType_id = EUDD.OrpDispUslugaType_id
				left join v_LpuSection LS (nolock) on LS.LpuSection_id = EUDD.LpuSection_uid
				outer apply (
					select top 1 Person_Fio
					from v_MedPersonal (nolock)
					where MedPersonal_id = EUDD.MedPersonal_id
				) MP
				left join v_UslugaComplex UC (nolock) on UC.UslugaComplex_id = EUDD.UslugaComplex_id
			where EUDD.EvnUslugaDispOrp_pid = ?
				and ISNULL(EUDD.EvnUslugaDispOrp_IsVizitCode, 1) = 1
		";

		$result = $this->db->query($query, array($data['EvnPLDispOrp_id']));

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
	 * Входящие данные: $data['EvnPLDispOrp_id']
	 * На выходе: ассоциативный массив результатов запроса
	 */
	function loadEvnUslugaDispOrpSecGrid($data)
	{

		$query = "
			select
				EUDD.EvnUslugaDispOrp_id,
				EUDD.Server_id,
				EUDD.PersonEvn_id,
				convert(varchar(10), EUDD.EvnUslugaDispOrp_setDate, 104) as EvnUslugaDispOrp_setDate,
				EUDD.EvnUslugaDispOrp_setTime,
				convert(varchar(10), EUDD.EvnUslugaDispOrp_disDate, 104) as EvnUslugaDispOrp_disDate,
				EUDD.EvnUslugaDispOrp_disTime,
				convert(varchar(10), EUDD.EvnUslugaDispOrp_didDate, 104) as EvnUslugaDispOrp_didDate,
				EUDD.OrpDispUslugaType_id,
				RTRIM(DDUT.OrpDispUslugaType_Name) as OrpDispUslugaType_Name,
				EUDD.LpuSection_uid as LpuSection_id,
				EUDD.Lpu_uid,
				EUDD.LpuSectionProfile_id,
				EUDD.MedSpecOms_id,
				RTRIM(LS.LpuSection_Name) as LpuSection_Name,
				EUDD.MedPersonal_id,
				EUDD.MedStaffFact_id,
				RTRIM(MP.Person_Fio) as MedPersonal_Fio,
				EUDD.UslugaComplex_id as UslugaComplex_id,
				RTRIM(UC.UslugaComplex_Name) as UslugaComplex_Name,
				RTRIM(UC.UslugaComplex_Code) as UslugaComplex_Code,
				EUDD.ExaminationPlace_id,
				EUDD.EvnUslugaDispOrp_Result,
				EP.ExaminationPlace_Name,
				1 as Record_Status
			from v_EvnUslugaDispOrp EUDD (nolock)
				left join OrpDispUslugaType DDUT (nolock) on DDUT.OrpDispUslugaType_id = EUDD.OrpDispUslugaType_id
				left join v_LpuSection LS (nolock) on LS.LpuSection_id = EUDD.LpuSection_uid
				left join v_MedPersonal MP (nolock) on MP.MedPersonal_id = EUDD.MedPersonal_id
				left join v_UslugaComplex UC (nolock) on UC.UslugaComplex_id = EUDD.UslugaComplex_id
				left join v_ExaminationPlace EP (nolock) on EP.ExaminationPlace_id = EUDD.ExaminationPlace_id
			where EUDD.EvnUslugaDispOrp_pid = ?
				and ISNULL(EUDD.EvnUslugaDispOrp_IsVizitCode, 1) = 1
		";

		$result = $this->db->query($query, array($data['EvnPLDispOrp_id']));

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
	 * Входящие данные: $data['EvnPLDispOrp_id']
	 * На выходе: ассоциативный массив результатов запроса
	 */
	function loadEvnUslugaDispOrpData($data)
	{
		$query = "
			select
				EUDD.EvnUslugaDispOrp_id,
				convert(varchar(10), EUDD.EvnUslugaDispOrp_setDate, 104) as EvnUslugaDispOrp_setDate,
				convert(varchar(10), EUDD.EvnUslugaDispOrp_didDate, 104) as EvnUslugaDispOrp_didDate,
				EUDD.OrpDispUslugaType_id
			from v_EvnUslugaDispOrp EUDD (nolock)
			where EUDD.EvnUslugaDispOrp_pid = ?
				and ISNULL(EUDD.EvnUslugaDispOrp_IsVizitCode, 1) = 1
		";

		$result = $this->db->query($query, array($data['EvnPLDispOrp_id']));

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
	 * Возвращает данные
	 */
	function loadEvnPLDispOrpStreamList($data)
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
	 * Возвращает данные о посещениях
	 */
	function loadEvnVizitPLDispOrpGrid($data)
	{
		$query = "
			select
				EVPL.EvnVizitPL_id,
				EVPL.Server_id,
				EVPL.PersonEvn_id,
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
	 * Проверка того, что человек есть в регистре по ДД и у него заведены все необходимые данные
	 */
	function checkPersonData($data)
	{
		$query = "
			select
				Sex_id,
				SocStatus_id,
				ps.UAddress_id as Person_UAddress_id,
				ps.Polis_Ser,
				ps.Polis_Num,
				o.Org_Name,
				o.Org_INN,
				o.Org_OGRN,
				o.UAddress_id as Org_UAddress_id,
				--o.Okved_id,
				os.OrgSmo_Name,
				(datediff(year, PS.Person_Birthday, dbo.tzGetDate())
				+ case when month(ps.Person_Birthday) > month(dbo.tzGetDate())
				or (month(ps.Person_Birthday) = month(dbo.tzGetDate()) and day(ps.Person_Birthday) > day(dbo.tzGetDate()))
				then -1 else 0 end) as Person_Age,
				convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday
			from v_persondisporp pdd (nolock)
			left join v_PersonState ps (nolock) on ps.Person_id=pdd.Person_id
			left join v_Job j (nolock) on j.Job_id=ps.Job_id
			left join v_Org o (nolock) on o.Org_id=j.Org_id
			left join v_Polis pol (nolock) on pol.Polis_id=ps.Polis_id
			left join v_OrgSmo os (nolock) on os.OrgSmo_id=pol.OrgSmo_id
			where pdd.Person_id = ?
		";

		$result = $this->db->query($query, array($data['Person_id']));
		$response = $result->result('array');

		if ( !is_array($response) || count($response) == 0 )
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


		If (count($error)>0) { // есть ошибки в заведении
			$errstr = implode("<br/>", $errors);
			return array(array('Error_Msg' => 'Проверьте полноту заведения данных у человека!<br/>'.$errstr));
		}
		return array( "Ok", ArrayVal($response[0], 'Sex_id'), ArrayVal($response[0], 'Person_Age'), ArrayVal($response[0], 'Person_Birthday') );
	}

	/**
	 * Проверка атрибута у отделения
	 */
	function checkAttributeforLpuSection($data)
	{
		$query = "
			select 
				EVZDD.EvnVizitDispOrp_didDT,
				ASVal.AttributeSign_id,
				ASVal.AttributeSignValue_begDate,
				ASVal.AttributeSignValue_endDate 
			from
				v_EvnVizitDispOrp EVZDD with(nolock)
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
	function loadDopDispInfoConsent($data) {
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

		if ( getRegionNick() == 'ufa' ) { // для уфы дополнительно отфильтровываем услуги посещений по LpuLevel
			$filter .= " and (ISNULL(ucat.UslugaCategory_SysNick, '') != 'lpusection' or ((case when LpuLevel.LpuLevel_code in (2,6) then '6' when LpuLevel.LpuLevel_code in (3,5) then '5' when LpuLevel.LpuLevel_code in (1,8) then '8' end) = left(UC.UslugaComplex_Code,1)))";
			$filter .= " and (UC.UslugaComplex_id is not null or Stl.UslugaComplex_id is null)";

			$joinList[] = "left join v_UslugaComplex UC (nolock) on UC.UslugaComplex_id = STL.UslugaComplex_id";
			$joinList[] = "left join v_Lpu lpu with (nolock) on lpu.Lpu_id = :Lpu_id";
			$joinList[] = "left join v_LpuLevel LpuLevel with (nolock) on LpuLevel.LpuLevel_id = lpu.LpuLevel_id";
			$joinList[] = "left join v_UslugaCategory ucat with (nolock) on ucat.UslugaCategory_id = UC.UslugaCategory_id";

			$params['Lpu_id'] = $data['Lpu_id'];
		}

		$orderby = "
			order by
				case when DDIC.DopDispInfoConsent_id is not null then 0 else 1 end asc -- в первую очередь сохраненные
		";

		if ( getRegionNick() == 'ekb' ) {
			$pre_select .= " ,stl.MedSpecOms_id";
			$select .= " ,ucpl.MedSpecOms_id";
			$joinList[] = "left join r66.v_UslugaComplexPartitionLink ucpl (nolock) on ucpl.UslugaComplex_id = STL.UslugaComplex_id";
			$orderby = "
				order by
					case when DDIC.DopDispInfoConsent_id is not null then 0 else 1 end asc, -- в первую очередь сохраненные
					case when ucpl.MedSpecOms_id is null then 0 else 1 end asc -- иначе те у которых MedSpecOms_id пустой
			";
		}

		// @task https://redmine.swan.perm.ru/issues/123599
		if (in_array(getRegionNick(), array('buryatiya', 'krym'))) {
			if ( !empty($params['EvnPLDispOrp_setDate']) ) {
				$params['ageDate'] = substr($params['EvnPLDispOrp_setDate'], 0, 4);
			}
			else {
				$params['ageDate'] = date('Y');
			}

			$params['ageDate'] .= '-12-31';
		}
		else {
			$params['ageDate'] = $params['EvnPLDispOrp_setDate'];
		}

		$query = "
			declare @sex_id bigint, @age int, @ageDate datetime;

			set @ageDate = :ageDate;

			select top 1
				@sex_id = ISNULL(Sex_id, 3),
				@age = dbo.Age2(Person_BirthDay, IsNull(@ageDate, dbo.tzGetDate()))
			from v_PersonState ps (nolock)
			where ps.Person_id = :Person_id;

			select
				ISNULL(STL.DopDispInfoConsent_id, -STL.SurveyTypeLink_id) as DopDispInfoConsent_id,
				STL.EvnPLDisp_id as EvnPLDispOrp_id,
				UC.UslugaComplex_Code as UslugaComplex_Code,
				ODS.OrpDispSpec_Code as OrpDispSpec_Code,
				STL.SurveyTypeLink_id as SurveyTypeLink_id,
				ISNULL(STL.SurveyTypeLink_IsNeedUsluga, 1) as SurveyTypeLink_IsNeedUsluga,
				ST.SurveyType_Code as SurveyType_Code,
				ST.SurveyType_Name as SurveyType_Name,
				case WHEN :EvnPLDispOrp_id IS NULL OR STL.DopDispInfoConsent_IsAgree = 2 then 1 else 0 end as DopDispInfoConsent_IsAgree, -- для новой карты проставляем чекбоксы
				case WHEN STL.DopDispInfoConsent_IsEarlier = 2 then 1 else 0 end as DopDispInfoConsent_IsEarlier
				{$pre_select}
			from v_SurveyType ST (nolock)
				cross apply(
					-- не удалённые
					select top 1
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
						v_SurveyTypeLink STL (nolock)
						left join v_DopDispInfoConsent DDIC (nolock) on DDIC.SurveyTypeLink_id = STL.SurveyTypeLink_id and DDIC.EvnPLDisp_id = :EvnPLDispOrp_id
						" . implode(' ', $joinList) . "
					where
						ST.SurveyType_id = STL.SurveyType_id
						and IsNull(STL.DispClass_id, 3) = :DispClass_id -- дети-сироты, 1 этап
						and (IsNull(STL.Sex_id, @sex_id) = @sex_id) -- по полу
						and (@age between Isnull(SurveyTypeLink_From, 0) and  Isnull(SurveyTypeLink_To, 999)) -- по возрасту, в принципе по библии Иссак лет 800 жил же
						and ISNULL(STL.SurveyTypeLink_IsDel, 1) = 1
						and (STL.SurveyTypeLink_begDate is null or STL.SurveyTypeLink_begDate <= :EvnPLDispOrp_setDate)
						and (STL.SurveyTypeLink_endDate is null or STL.SurveyTypeLink_endDate >= :EvnPLDispOrp_setDate)
						" . $filter . "
					{$orderby}
					
					union
					
					-- удалённые, но сохранённые в согласии
					select top 1
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
						v_SurveyTypeLink STL (nolock)
						inner join v_DopDispInfoConsent DDIC (nolock) on DDIC.SurveyTypeLink_id = STL.SurveyTypeLink_id and DDIC.EvnPLDisp_id = :EvnPLDispOrp_id
						" . implode(' ', $joinList) . "
					where
						ST.SurveyType_id = STL.SurveyType_id
						and IsNull(STL.DispClass_id, 3) = :DispClass_id -- дети-сироты, 1 этап
						and (IsNull(STL.Sex_id, @sex_id) = @sex_id) -- по полу
						and (@age between Isnull(SurveyTypeLink_From, 0) and  Isnull(SurveyTypeLink_To, 999)) -- по возрасту, в принципе по библии Иссак лет 800 жил же
						and STL.SurveyTypeLink_IsDel = 2
						and (STL.SurveyTypeLink_begDate is null or STL.SurveyTypeLink_begDate <= :EvnPLDispOrp_setDate)
						and (STL.SurveyTypeLink_endDate is null or STL.SurveyTypeLink_endDate >= :EvnPLDispOrp_setDate)
						" . $filter . "
				) STL
				left join v_UslugaComplex UC (nolock) on UC.UslugaComplex_id = STL.UslugaComplex_id
				left join v_OrpDispSpec ODS (nolock) on ODS.OrpDispSpec_id = ST.OrpDispSpec_id
			order by
				ST.SurveyType_Code
			
		";
		//echo getDebugSql($query, $params);die();

		$result = $this->db->query($query, $params);
		/*echo getDebugSql($query, array(
			'EvnPLDispOrp_id' => $data['EvnPLDispOrp_id']
		));die();*/
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Возвращает данные
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
	 * Сохранение информированного согласия
	 */
	function saveDopDispInfoConsent($data) {
		// Стартуем транзакцию
		$this->db->trans_begin();

		ConvertFromWin1251ToUTF8($data['DopDispInfoConsentData']);
		$items = json_decode($data['DopDispInfoConsentData'], true);
		$itemsCount = 0;
		$dopdispicarray = array(-1); // массив под id сохранненых согласий

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
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @Res = :DopDispInfoConsent_id;
				
				exec {$proc}
					@DopDispInfoConsent_id = @Res output, 
					@EvnPLDisp_id = :EvnPLDispOrp_id, 
					@SurveyTypeLink_id = :SurveyTypeLink_id,
					@DopDispInfoConsent_IsAgree = :DopDispInfoConsent_IsAgree, 
					@DopDispInfoConsent_IsEarlier = :DopDispInfoConsent_IsEarlier, 
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output
 
				select @Res as DopDispInfoConsent_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$result = $this->db->query($query, array(
				'EvnPLDispOrp_id' => $data['EvnPLDispOrp_id'],
				'DopDispInfoConsent_id' => $item['DopDispInfoConsent_id'],
				'SurveyTypeLink_id' => $item['SurveyTypeLink_id'],
				'DopDispInfoConsent_IsAgree' => $item['DopDispInfoConsent_IsAgree'],
				'DopDispInfoConsent_IsEarlier' => $item['DopDispInfoConsent_IsEarlier'],
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

				if ( is_array($res) && count($res) > 0 && !empty($res[0]['DopDispInfoConsent_id']) ) {
					$dopdispicarray[] = $res[0]['DopDispInfoConsent_id'];
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

		// удаляем все согласия не удовлетворяющие посещению (т.е. все не сохранённые только что согласия)
		$query = "select DopDispInfoConsent_id from v_DopDispInfoConsent (nolock) where DopDispInfoConsent_id not in (".implode(',', $dopdispicarray).") and EvnPLDisp_id = :EvnPLDispOrp_id";
		$result = $this->db->query($query, array('EvnPLDispOrp_id' => $data['EvnPLDispOrp_id']));
		if ( is_object($result) ) {
			$res = $result->result('array');
			if ( is_array($res) && count($res) > 0 ) {
				foreach ( $res as $resone ) {
					$query = "
						declare
							@ErrCode int,
							@ErrMessage varchar(4000);

						exec p_DopDispInfoConsent_del
							@DopDispInfoConsent_id = :DopDispInfoConsent_id,
							@pmUser_id = :pmUser_id, 
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;

						select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					";
					$delresult = $this->db->query($query, array('DopDispInfoConsent_id' => $resone['DopDispInfoConsent_id'],'pmUser_id' => $data['pmUser_id']));

					if ( is_object($delresult) ) {
						$resp = $delresult->result('array');

						if ( is_array($resp) && count($resp) > 0 && !empty($resp[0]['Error_Msg']) ) {
							$this->db->trans_rollback();
							return array(
								'success' => false,
								'Error_Msg' => $resp[0]['Error_Msg']
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

		// Чистим атрибуты и услуги
		$attrArray = array(
			'EvnUslugaDispOrp', // Услуги с отказом
			'EvnVizitDispOrp' // Осмотры с отказом
		);

		if ( $itemsCount == 0 ) {
			//$attrArray[] = 'EvnDiagDopDisp'; // Ранее известные имеющиеся заболевания, впервые выявленные заболевания
			//$attrArray[] = 'HeredityDiag'; // Наследственность по заболеваниям
			//$attrArray[] = 'ProphConsult'; // Показания к углубленному профилактическому консультированию
			//$attrArray[] = 'NeedConsult'; // Показания к консультации врача-специалиста
			$attrArray[] = 'DopDispInfoConsent';
			$attrArray[] = 'AssessmentHealth';
		}

		foreach ( $attrArray as $attr ) {
			$deleteResult = $this->deleteAttributes($attr, $data['EvnPLDispOrp_id'], $data['pmUser_id'], $data['DispClass_id']);

			if ( !empty($deleteResult) ) {
				$this->db->trans_rollback();
				return array(
					'success' => false,
					'Error_Msg' => $deleteResult . ' (строка ' . __LINE__ . ')'
				);
			}
		}

		// проставляем признак отказа
		if ( $itemsCount == 0 ) {
			$data['EvnPLDisp_IsRefusal'] = 2;
		} else {
			$data['EvnPLDisp_IsRefusal'] = 1;
		}

		// Надо обновить в карте дату согласия
		$query = "
			update EvnPLDisp with (rowlock) set EvnPLDisp_consDT = :EvnPLDispOrp_consDate, EvnPLDisp_IsRefusal = :EvnPLDisp_IsRefusal where EvnPLDisp_id = :EvnPLDispOrp_id
		";
		$result = $this->db->query($query, $data);

		// Обновляется дата начала диспансеризации
		$query = "
			update Evn with (rowlock) set Evn_setDT = :EvnPLDispOrp_setDate where Evn_id = :EvnPLDispOrp_id
		";
		$result = $this->db->query($query, $data);

		$query = "
			update EvnPLBase with (rowlock) set EvnPLBase_IsFinish = 1 where EvnPLBase_id = :EvnPLBase_id
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
	function getEvnUslugaDispOrpForEvnVizit($EvnVizitDispOrp_id) {
		$query = "
			select top 1
				EvnUslugaDispOrp_id
			from
				v_EvnUslugaDispOrp (nolock)
			where
				EvnUslugaDispOrp_pid = :EvnVizitDispOrp_id
				and ISNULL(EvnUslugaDispOrp_IsVizitCode, 1) = 1
		";

		$result = $this->db->query($query, array(
			'EvnVizitDispOrp_id' => $EvnVizitDispOrp_id
		));

		if ( is_object($result) ) {
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
				stl.UslugaComplex_id
			from
				DopDispInfoConsent ddic (nolock)
				inner join v_SurveyTypeLink stl (nolock) on stl.SurveyTypeLink_id = ddic.SurveyTypeLink_id
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
	 * Проверка правильности адреса человека
	 */
	function checkPersonAddress($data) {
		$query = "
			select
				ua.Address_id as UAddress_id,
				ua.Address_Zip as UAddress_Zip,
				uklr.KLRgn_Actual as UKLRgn_Actual,
				uklsr.KLSubRgn_Actual as UKLSubRgn_Actual,
				uklc.KLCity_Actual as UKLCity_Actual,
				uklt.KLTown_Actual as UKLTown_Actual,
				ukls.KLAdr_Actual as UKLStreet_Actual,
				pa.Address_id as PAddress_id,
				pa.Address_Zip as PAddress_Zip,
				pklr.KLRgn_Actual as PKLRgn_Actual,
				pklsr.KLSubRgn_Actual as PKLSubRgn_Actual,
				pklc.KLCity_Actual as PKLCity_Actual,
				pklt.KLTown_Actual as PKLTown_Actual,
				pkls.KLAdr_Actual as PKLStreet_Actual
			from
				--v_PersonState ps (nolock)
				v_Person_all p (nolock)
				left join v_Address ua (nolock) on ua.Address_id = p.UAddress_id
				left join v_KLRgn uklr (nolock) on uklr.KLRgn_id = ua.KLRgn_id
				left join v_KLSubRgn uklsr (nolock) on uklsr.KLSubRgn_id = ua.KLSubRgn_id
				left join v_KLCity uklc (nolock) on uklc.KLCity_id = ua.KLCity_id
				left join v_KLTown uklt (nolock) on uklt.KLTown_id = ua.KLTown_id
				left join v_KLStreet ukls (nolock) on ukls.KLStreet_id = ua.KLStreet_id
				left join v_Address pa (nolock) on pa.Address_id = p.PAddress_id
				left join v_KLRgn pklr (nolock) on pklr.KLRgn_id = pa.KLRgn_id
				left join v_KLSubRgn pklsr (nolock) on pklsr.KLSubRgn_id = pa.KLSubRgn_id
				left join v_KLCity pklc (nolock) on pklc.KLCity_id = pa.KLCity_id
				left join v_KLTown pklt (nolock) on pklt.KLTown_id = pa.KLTown_id
				left join v_KLStreet pkls (nolock) on pkls.KLStreet_id = pa.KLStreet_id
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
		  		select top 1 * 
		  		from v_EvnPLDispOrp with(nolock) 
		  		where EvnPLDispOrp_id = :EvnPLDispOrp_id
			", $data, true);
			if ($savedData === false) {
				return array('Error_Msg' => 'Ошибка при получении данных карты');
			}
		}

		$this->load->model('EvnUsluga_model');
		// Если случай закрыт и задана дата справки, то сохраняем справку.
		if (!empty($data['EvnPLDispOrp_id']) && !empty($data['EvnPLDispOrp_IsFinish']) && $data['EvnPLDispOrp_IsFinish'] == 2 && !empty($data['EvnCostPrint_setDT']))
		{
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

			If ( !empty($checkResult) ) {
				return array('Error_Msg' => $checkResult);
			}
		}

		// Проверяем что человек находится в регистре по ДД и у него заведены все необходимые данные
		$checkResult = $this->checkPersonData($data);

		If ( $checkResult[0]!="Ok" ) {
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

		if ( !empty($data['EvnPLDispOrp_id']) )
		{
			// достаем дату начала, дату окончания, количество посещений
			$query = "
				select
					convert(varchar,cast(EvnPLDispOrp_setDT as datetime),112) as EvnPLDispOrp_setDT,
					convert(varchar,cast(EvnPLDispOrp_disDT as datetime),112) as EvnPLDispOrp_disDT,
					convert(varchar,cast(EvnPLDispOrp_didDT as datetime),112) as EvnPLDispOrp_didDT,
					EvnPLDispOrp_VizitCount
				from
					v_EvnPLDispOrp (nolock)
				where EvnPLDispOrp_id = ?
			";
			$result = $this->db->query($query, array($data['EvnPLDispOrp_id']));
			if (is_object($result))
			{
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
		if ($data['EvnPLDispOrp_IsMobile']) { $data['EvnPLDispOrp_IsMobile'] = 2; } else { $data['EvnPLDispOrp_IsMobile'] = 1; }
		if ($data['EvnPLDispOrp_IsOutLpu']) { $data['EvnPLDispOrp_IsOutLpu'] = 2; } else { $data['EvnPLDispOrp_IsOutLpu'] = 1; }

		// Проверяем что нет профосмотра
		$query = "
			SELECT top 1
				epldti.EvnPLDispTeenInspection_id
			FROM
				v_EvnPLDispTeenInspection epldti (nolock)
				inner join v_PersonState ps (nolock) on ps.Person_id = epldti.Person_id
			WHERE
				epldti.Person_id = :Person_id
				and year(epldti.EvnPLDispTeenInspection_setDT) = YEAR(:EvnPLDispOrp_setDT)
				and epldti.DispClass_id = 10 -- профилактический осмотр
				and epldti.EvnPLDispTeenInspection_IsFinish = 2 -- закрытый
				and dbo.Age2(ps.Person_BirthDay, :EvnPLDispOrp_setDT) >= 3 -- 3 лет и старше
		";
		$checkResult = $this->queryResult($query, array(
			'Person_id' => $data['Person_id'],
			'EvnPLDispOrp_setDT' => $data['EvnPLDispOrp_setDT']
		));
		if (!empty($checkResult[0]['EvnPLDispTeenInspection_id'])) {
			return array('Error_Msg' => 'На выбранного пациента в выбранном году уже сохранена карта профилактического осмотра несовершеннолетнего.');
		}

		$data['MedStaffFact_id'] = !empty($data['session']['CurARM']['MedStaffFact_id'])?$data['session']['CurARM']['MedStaffFact_id']:null;

		$this->checkZnoDirection($data, 'EvnPLDispOrp');

		$query = "
			declare
				@Res bigint,
				@EvnPLDispOrp_IsRefusal bigint,
				@EvnDirection_aid bigint,
				@ErrCode int,
				@ErrMessage varchar(4000),
				@curdate datetime;

			set @curdate = dbo.tzGetDate();
			set @Res = :EvnPLDispOrp_id;

			if ( @Res is not null )
				select top 1
					@EvnPLDispOrp_IsRefusal = EvnPLDispOrp_IsRefusal,
					@EvnDirection_aid = EvnDirection_aid
				from v_EvnPLDispOrp (nolock)
				where EvnPLDispOrp_id = :EvnPLDispOrp_id;

			exec {$procedure}
				@EvnPLDispOrp_id = @Res output,
				@MedStaffFact_id = :MedStaffFact_id,
				@EvnPLDispOrp_IndexRep = :EvnPLDispOrp_IndexRep,
				@EvnPLDispOrp_IndexRepInReg = :EvnPLDispOrp_IndexRepInReg,
				@EvnPLDispOrp_fid = :EvnPLDispOrp_fid,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@EvnPLDispOrp_setDT = :EvnPLDispOrp_setDT,
				@EvnPLDispOrp_disDT = :EvnPLDispOrp_disDT,
				@EvnPLDispOrp_didDT = :EvnPLDispOrp_didDT,
				@EvnPLDispOrp_VizitCount = :EvnPLDispOrp_VizitCount,
				@EvnPLDispOrp_IsFinish = :EvnPLDispOrp_IsFinish,
				@EvnPLDispOrp_IsTwoStage = :EvnPLDispOrp_IsTwoStage,
				@ChildStatusType_id = :ChildStatusType_id,
				@AttachType_id = 2, -- доп. диспансеризация
				@DispClass_id = :DispClass_id, -- ддс
				@PayType_id = :PayType_id,
				@EvnPLDispOrp_consDT = :EvnPLDispOrp_consDate,
				@EvnPLDispOrp_IsMobile = :EvnPLDispOrp_IsMobile, 
				@EvnPLDispOrp_IsOutLpu = :EvnPLDispOrp_IsOutLpu, 
				@Lpu_mid = :Lpu_mid,
				@EvnPLDispOrp_IsRefusal = @EvnPLDispOrp_IsRefusal,
				@EvnDirection_aid = @EvnDirection_aid,
				@EvnPLDispOrp_IsSuspectZNO = :EvnPLDispOrp_IsSuspectZNO,
				@Diag_spid = :Diag_spid,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as EvnPLDispOrp_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, $data);

		if (!is_object($result))
		{
			return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение талона по ДД)');
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

		if ( !isset($data['EvnPLDispOrp_id']) )
		{
			$data['EvnPLDispOrp_id'] = $response[0]['EvnPLDispOrp_id'];
		}

		// Ищем AssessmentHealth связанный с EvnPLDispOrp_id, если нет его то добавляем новый, иначе обновляем
		$data['AssessmentHealth_id'] = NULL;
		$query = "
			select top 1 AssessmentHealth_id from v_AssessmentHealth (nolock) where EvnPLDisp_id = :EvnPLDispOrp_id
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
			$MedCare_id = $this->getMedCareForEvnVizitDispOrp($record['EvnVizitDispOrp_id'], 1);
			$this->saveMedCare(array(
				'MedCare_id' => $MedCare_id,
				'EvnVizitDispOrp_id' => $record['EvnVizitDispOrp_id'],
				'ConditMedCareType_nid' => empty($json['ConditMedCareType1_nid'])?null:$json['ConditMedCareType1_nid'],
				'PlaceMedCareType_nid' => empty($json['PlaceMedCareType1_nid'])?null:$json['PlaceMedCareType1_nid'],
				'ConditMedCareType_id' => empty($json['ConditMedCareType1_id'])?null:$json['ConditMedCareType1_id'],
				'PlaceMedCareType_id' => empty($json['PlaceMedCareType1_id'])?null:$json['PlaceMedCareType1_id'],
				'LackMedCareType_id' => empty($json['LackMedCareType1_id'])?null:$json['LackMedCareType1_id'],
				'MedCareType_id' => 1,
				'pmUser_id' => $data['pmUser_id']
			));

			// получаем MedCare_id для MedCareType_id = 2
			$MedCare_id = $this->getMedCareForEvnVizitDispOrp($record['EvnVizitDispOrp_id'], 2);
			$this->saveMedCare(array(
				'MedCare_id' => $MedCare_id,
				'EvnVizitDispOrp_id' => $record['EvnVizitDispOrp_id'],
				'ConditMedCareType_nid' => empty($json['ConditMedCareType2_nid'])?null:$json['ConditMedCareType2_nid'],
				'PlaceMedCareType_nid' => empty($json['PlaceMedCareType2_nid'])?null:$json['PlaceMedCareType2_nid'],
				'ConditMedCareType_id' => empty($json['ConditMedCareType2_id'])?null:$json['ConditMedCareType2_id'],
				'PlaceMedCareType_id' => empty($json['PlaceMedCareType2_id'])?null:$json['PlaceMedCareType2_id'],
				'LackMedCareType_id' => empty($json['LackMedCareType2_id'])?null:$json['LackMedCareType2_id'],
				'MedCareType_id' => 2,
				'pmUser_id' => $data['pmUser_id']
			));

			// получаем MedCare_id для MedCareType_id = 3
			$MedCare_id = $this->getMedCareForEvnVizitDispOrp($record['EvnVizitDispOrp_id'], 3);
			$this->saveMedCare(array(
				'MedCare_id' => $MedCare_id,
				'EvnVizitDispOrp_id' => $record['EvnVizitDispOrp_id'],
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
				update EvnVizitDisp set DispSurveilType_id = :DispSurveilType_id, EvnVizitDisp_IsVMP = :EvnVizitDisp_IsVMP, EvnVizitDisp_IsFirstTime = :EvnVizitDisp_IsFirstTime where EvnVizitDisp_id = :EvnVizitDisp_id
			";

			$result = $this->db->query($query, array(
				'DispSurveilType_id' => empty($json['DispSurveilType_id'])?null:$json['DispSurveilType_id'],
				'EvnVizitDisp_IsVMP' => empty($json['EvnVizitDisp_IsVMP'])?null:$json['EvnVizitDisp_IsVMP'],
				'EvnVizitDisp_IsFirstTime' => empty($json['EvnVizitDisp_IsFirstTime'])?null:$json['EvnVizitDisp_IsFirstTime'],
				'EvnVizitDisp_id' => $record['EvnVizitDispOrp_id'],
			));
		}
		$this->load->model('EvnUsluga_model');
		// Лабораторные исследования
		foreach ($data['EvnUslugaDispOrp'] as $key => $record) {
			if ($record['Record_Status'] == 3) {// удаление исследований
				$query = "
					declare
						@ErrCode int,
						@ErrMessage varchar(4000);
					exec p_EvnUslugaDispOrp_del "
					. "@EvnUslugaDispOrp_id = ?, "
					. "@pmUser_id = ?, "
					. "@Error_Code = @ErrCode output, "
					. "@Error_Message = @ErrMessage output;
					select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				$result = $this->db->query($query, array($record['EvnUslugaDispOrp_id'], $data['pmUser_id']));

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
			else {
				if ($record['Record_Status'] == 0)
				{
					$procedure = 'p_EvnUslugaDispOrp_ins';
				}
				else
				{
					$procedure = 'p_EvnUslugaDispOrp_upd';
				}

				// проверяем, есть ли уже такое исследование
				$query = "
					select top 1
						EvnUslugaDispOrp_id
					from
						v_EvnUslugaDispOrp (nolock)
					where
						EvnUslugaDispOrp_pid = ?
						and UslugaComplex_id = ?
						and EvnUslugaDispOrp_id <> isnull(?, 0)
						and ISNULL(EvnUslugaDispOrp_IsVizitCode, 1) = 1
				";
				$result = $this->db->query(
					$query,
					array(
						$data['EvnPLDispOrp_id'],
						$record['UslugaComplex_id'],
						$record['Record_Status'] == 0 ? null : $record['EvnUslugaDispOrp_id']
					)
				);
				if (!is_object($result))
				{
					return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение исследования)'));
				}
				$response = $result->result('array');
				if ( is_array($response) && count($response) > 0 )
				{
					return array(array('Error_Msg' => 'Обнаружено дублирование исследований, это недопустимо.'));
				}
				// окончание проверки

				$setDT = $record['EvnUslugaDispOrp_setDate'];
				if (!empty($record['EvnUslugaDispOrp_setTime'])) {
					$setDT .= ' '.$record['EvnUslugaDispOrp_setTime'];
				}
				$disDT = null;
				if (!empty($record['EvnUslugaDispOrp_disDate'])) {
					$disDT = $record['EvnUslugaDispOrp_disDate'];

					if (!empty($record['EvnUslugaDispOrp_disTime'])) {
						$disDT .= ' '.$record['EvnUslugaDispOrp_disTime'];
					}
				}

				if ($record['LpuSection_id']=='')
					$record['LpuSection_id'] = Null;
				$query = "
					declare
						@pt bigint,
						@Res bigint,
						@ErrCode int,
						@ErrMessage varchar(4000);

					set @pt = (select top 1 PayType_id from v_PayType with (nolock) where PayType_SysNick = 'dopdisp');
					set @Res = :EvnUslugaDispOrp_id;

					exec " . $procedure . "
						@EvnUslugaDispOrp_id = @Res output,
						@EvnUslugaDispOrp_pid = :EvnUslugaDispOrp_pid,
						@Lpu_id = :Lpu_id,
						@Server_id = :Server_id,
						@PersonEvn_id = :PersonEvn_id,
						@EvnUslugaDispOrp_setDT = :EvnUslugaDispOrp_setDT,
						@EvnUslugaDispOrp_disDT = :EvnUslugaDispOrp_disDT,
						@EvnUslugaDispOrp_didDT = :EvnUslugaDispOrp_didDT,
						@LpuSection_uid = :LpuSection_uid,
						@MedSpecOms_id = :MedSpecOms_id,
						@LpuSectionProfile_id = :LpuSectionProfile_id,
						@MedPersonal_id = :MedPersonal_id,
						@MedStaffFact_id = :MedStaffFact_id,
						@UslugaComplex_id = :UslugaComplex_id,
						@PayType_id = @pt,
						@UslugaPlace_id = 1,
						@Lpu_uid = :Lpu_uid,
						@EvnUslugaDispOrp_Kolvo = 1,
						@ExaminationPlace_id = :ExaminationPlace_id,
						@EvnPrescrTimetable_id = null,
						@EvnPrescr_id = null,
						@EvnUslugaDispOrp_Result = :EvnUslugaDispOrp_Result,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;

					select @Res as EvnUslugaDispOrp_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
				select top 1
					EvnUslugaDispOrp_id,
					UslugaComplex_id,
					PayType_id,
					convert(varchar(10), EvnUslugaDispOrp_setDT, 104) as EvnUslugaDispOrp_setDate
				from v_EvnUslugaDispOrp with (nolock)
				where EvnUslugaDispOrp_pid = :EvnUslugaDispOrp_pid
					and EvnUslugaDispOrp_IsVizitCode = 2
			";
			$result = $this->db->query($query, array(
				'EvnUslugaDispOrp_pid' => $data['EvnPLDispOrp_id']
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

			$data['ageDate'] = substr($data['EvnPLDispOrp_setDT'], 0, 4) . '-12-31';

			$filter = "";
			if (getRegionNick() == 'buryatiya') {
				$filter .= "
					and (
						not (UslugaSurveyLink_From = 0 and UslugaSurveyLink_To = 1 and DispClass_id IN (3,7))
						or exists(
							select top 1
								eudo.EvnUslugaDispOrp_id
							from
								v_EvnUslugaDispOrp eudo (nolock)
								inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = eudo.UslugaComplex_id
							where
								eudo.EvnUslugaDispOrp_pid = :EvnPLDispOrp_id and
								uc.UslugaComplex_Code IN ('004043', '161204')
						)
					)				
				";
			}

			// Определяем UslugaComplex_id через $schema.UslugaSurveyLink
			$query = "
				declare @sex_id bigint, @age int, @ageDate datetime;

				set @ageDate = :ageDate;

				select top 1
					@sex_id = ISNULL(Sex_id, 3),
					@age = dbo.Age2(Person_BirthDay, ISNULL(@ageDate, dbo.tzGetDate()))
				from v_PersonState ps (nolock)
				where ps.Person_id = :Person_id

				select top 1 USL.UslugaComplex_id
				from {$data['session']['region']['schema']}.v_UslugaSurveyLink USL with (nolock)
				where
					USL.DispClass_id = :DispClass_id
					and ISNULL(USL.Sex_id, @sex_id) = @sex_id
					and @age between ISNULL(USL.UslugaSurveyLink_From, 0) and ISNULL(USL.UslugaSurveyLink_To, 999)
					and ISNULL(USL.UslugaSurveyLink_IsDel, 1) = 1
					and (USL.UslugaSurveyLink_begDate is null or USL.UslugaSurveyLink_begDate <= :EvnPLDispOrp_setDT)
					and (USL.UslugaSurveyLink_endDate is null or USL.UslugaSurveyLink_endDate >= :EvnPLDispOrp_setDT)
					{$filter}
			";
			$result = $this->db->query($query, array(
				'DispClass_id' => $data['DispClass_id'],
				'EvnPLDispOrp_id' => $data['EvnPLDispOrp_id'],
				'EvnPLDispOrp_setDT' => $data['EvnPLDispOrp_setDT'],
				'Person_id' => $data['Person_id'],
				'ageDate' => $data['ageDate'],
			));

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
					declare
						@id bigint = :EvnUslugaDispOrp_id,
						@pt bigint = :PayType_id,
						@ErrCode int,
						@ErrMessage varchar(4000);

					if ( @pt is null )
						set @pt = (select top 1 PayType_id from v_PayType with (nolock) where PayType_SysNick = 'dopdisp');

					exec p_EvnUslugaDispOrp_" . (!empty($uslugaData['EvnUslugaDispOrp_id']) ? "upd" : "ins") . "
						@EvnUslugaDispOrp_id = @id output,
						@EvnUslugaDispOrp_pid = :EvnUslugaDispOrp_pid,
						@UslugaComplex_id = :UslugaComplex_id,
						@EvnUslugaDispOrp_setDT = :EvnUslugaDispOrp_setDT,
						@EvnUslugaDispOrp_IsVizitCode = 2,
						@Lpu_id = :Lpu_id,
						@Server_id = :Server_id,
						@PersonEvn_id = :PersonEvn_id,
						@PayType_id = @pt,
						@UslugaPlace_id = 1,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;

					select @id as EvnUslugaDispOrp_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
			else if ( !empty($uslugaData['EvnUslugaDispOrp_id']) ) {
				$query = "
					declare
						@ErrCode int,
						@ErrMessage varchar(4000);

					exec p_EvnUslugaDispOrp_del
						@EvnUslugaDispOrp_id = :EvnUslugaDispOrp_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;

					select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				$result = $this->db->query($query, array(
					'EvnUslugaDispOrp_id' => $uslugaData['EvnUslugaDispOrp_id'],
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
	function saveEvnPLDispOrpSec($data) {
		// begin проверки перенесённые с формы
		// получаем возраст пациента
		$resp_ps = $this->queryResult("
			select top 1
				dbo.Age2(PS.Person_BirthDay, :EvnPLDispOrp_consDate) as Person_Age,
				convert(varchar(10), epldo.EvnPLDispOrp_setDate, 120) as EvnPLDispOrp_firSetDate
			from
				v_PersonState PS (nolock)
				left join v_EvnPLDispOrp epldo (nolock) on epldo.EvnPLDispOrp_id = :EvnPLDispOrp_fid
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
			if ( $data['EvnPLDispOrp_consDate'] >= '2018-01-01' ) {
				$pedcodes = array(); // https://redmine.swan-it.ru/issues/162413
				$resp_uc = $this->queryResult("
					select distinct
						uc.UslugaComplex_Code
					from
						v_SurveyTypeLink stl (nolock)
						inner join v_SurveyType st (nolock) on st.SurveyType_id = stl.SurveyType_id
						inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = stl.UslugaComplex_id
					where
						st.SurveyType_Code = '27'
						and stl.DispClass_id = 3
						and stl.SurveyTypeLink_begDate <= :EvnPLDispOrp_consDate
						and ISNULL(stl.SurveyTypeLink_endDate, :EvnPLDispOrp_consDate) >=  :EvnPLDispOrp_consDate
				", array(
					'EvnPLDispOrp_consDate' => $data['EvnPLDispOrp_consDate']
				));
				foreach($resp_uc as $one_uc) {
					$pedcodes[] = $one_uc['UslugaComplex_Code'];
				}
			}
			else {
				$pedcodes = array('B04.031.002','B04.031.004','B04.026.002'); // https://redmine.swan.perm.ru/issues/56948 добавил коды B04.031.004 и B04.026.002
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

		if ( !empty($data['EvnPLDispOrp_id']) && (!is_array($data['EvnVizitDispOrp']) || count($data['EvnVizitDispOrp']) == 0) ) {
			$data['EvnVizitDispOrp'] = $this->queryResult("
				select
					convert(varchar(10), EVDO.EvnVizitDispOrp_setDT, 120) as EvnVizitDispOrp_setDate,
					UC.UslugaComplex_Code,
					1 as Record_Status
				from
					v_EvnVizitDispOrp EVDO with (nolock)
					inner join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = EVDO.UslugaComplex_id 
				where
					EVDO.EvnVizitDispOrp_pid = :EvnPLDispOrp_id
			", $data);
		}

		if ( !is_array($data['EvnVizitDispOrp']) ) {
			$data['EvnVizitDispOrp'] = array();
		}

		foreach ($data['EvnVizitDispOrp'] as $record) {
			if((int)$record['Record_Status'] == 3) continue;// #136178
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
			if ( getRegionNick() == 'perm' && $data['EvnPLDispOrp_consDate'] >= '2018-01-01' ) {
				$Error_Msg = 'Карта должна содержать осмотр врача-педиатра либо осмотр врача общей практики';
			} else {
				$Error_Msg = 'Случай не может быть закончен, так как не сохранен осмотр врача-педиатра (ВОП) (' . implode(', ', $pedcodes) . ')';
			}
			return ['Error_Msg' => $Error_Msg];
		}


		// https://redmine.swan.perm.ru/issues/20485
		if ( !empty($EvnVizitDispOrp_pedDate) && strtotime($EvnVizitDispOrp_pedDate) - 63*24*60*60 > strtotime($EvnPLDispOrp_firSetDate) && empty($data['ignoreOsmotrDlit']) ) {
			if ( $this->regionNick == 'krym' ) {
				return array('Error_Msg' => 'YesNo', 'Alert_Msg' => 'Длительность 1 и 2 этапов диспансеризации несовершеннолетнего не может быть больше 45 рабочих дней. Продолжить сохранение?', 'Error_Code' => 110);
			}
			else {
				return array('Error_Msg' => 'Длительность 1 и 2 этапов диспансеризации несовершеннолетнего не может быть больше 45 рабочих дней.');
			}
		}

		// https://redmine.swan.perm.ru/issues/20499
		if ( !empty($EvnVizitDispOrp_pedDate) && $EvnVizitDispOrp_pedDate < $data['EvnPLDispOrp_consDate'] ) {
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
		if ( !empty($maxDate) && !empty($EvnVizitDispOrp_pedDate) && $maxDate > $EvnVizitDispOrp_pedDate ) {
			return array('Error_Msg' => 'Дата осмотра/исследования по диспансеризации несовершеннолетнего не может быть больше даты осмотра врача-педиатра.');
		}

		// https://redmine.swan.perm.ru/issues/20485
		if (!empty($minDate) && !empty($EvnVizitDispOrp_pedDate) && DateTime::createFromFormat('Y-m-d', $minDate) < DateTime::createFromFormat('Y-m-d', $EvnVizitDispOrp_pedDate)->sub(new DateInterval('P'.($Person_Age < 2 ? 1 : 3).'M'))) {
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
		  		select top 1 * 
		  		from v_EvnPLDispOrp with(nolock) 
		  		where EvnPLDispOrp_id = :EvnPLDispOrp_id
			", $data, true);
			if ($savedData === false) {
				return array('Error_Msg' => 'Ошибка при получении данных карты');
			}
		}

		$this->load->model('EvnUsluga_model');
		// Если случай закрыт и задана дата справки, то сохраняем справку.
		if (!empty($data['EvnPLDispOrp_id']) && !empty($data['EvnPLDispOrp_IsFinish']) && $data['EvnPLDispOrp_IsFinish'] == 2 && !empty($data['EvnCostPrint_setDT']))
		{
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

		If ( $checkResult[0]!="Ok" ) {
			return $checkResult;
		}

		$procedure = 'p_EvnPLDispOrp_ins';
		$data['EvnPLDispOrp_setDT'] = date('Y-m-d');
		$data['EvnPLDispOrp_disDT'] = null;
		$data['EvnPLDispOrp_didDT'] = null;
		$data['EvnPLDispOrp_VizitCount'] = 0;

		if ( !empty($data['EvnPLDispOrp_id']) )
		{
			// достаем дату начала, дату окончания, количество посещений
			$query = "
				select
					convert(varchar,cast(EvnPLDispOrp_setDT as datetime),112) as EvnPLDispOrp_setDT,
					convert(varchar,cast(EvnPLDispOrp_disDT as datetime),112) as EvnPLDispOrp_disDT,
					convert(varchar,cast(EvnPLDispOrp_didDT as datetime),112) as EvnPLDispOrp_didDT,
					EvnPLDispOrp_VizitCount
				from
					v_EvnPLDispOrp (nolock)
				where EvnPLDispOrp_id = ?
			";
			$result = $this->db->query($query, array($data['EvnPLDispOrp_id']));
			if (is_object($result))
			{
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
		if ($data['EvnPLDispOrp_IsMobile']) { $data['EvnPLDispOrp_IsMobile'] = 2; } else { $data['EvnPLDispOrp_IsMobile'] = 1; }
		if ($data['EvnPLDispOrp_IsOutLpu']) { $data['EvnPLDispOrp_IsOutLpu'] = 2; } else { $data['EvnPLDispOrp_IsOutLpu'] = 1; }

		$data['MedStaffFact_id'] = !empty($data['session']['CurARM']['MedStaffFact_id'])?$data['session']['CurARM']['MedStaffFact_id']:null;

		$this->checkZnoDirection($data, 'EvnPLDispOrp');

		$query = "
			declare
				@Res bigint,
				@EvnPLDispOrp_IsRefusal bigint,
				@EvnDirection_aid bigint,
				@ErrCode int,
				@ErrMessage varchar(4000),
				@curdate datetime;

			set @curdate = dbo.tzGetDate();
			set @Res = :EvnPLDispOrp_id;

			if ( @Res is not null )
				select top 1
					@EvnPLDispOrp_IsRefusal = EvnPLDispOrp_IsRefusal,
					@EvnDirection_aid = EvnDirection_aid
				from v_EvnPLDispOrp (nolock)
				where EvnPLDispOrp_id = :EvnPLDispOrp_id;

			exec {$procedure}
				@EvnPLDispOrp_id = @Res output,
				@MedStaffFact_id = :MedStaffFact_id,
				@EvnPLDispOrp_IndexRep = :EvnPLDispOrp_IndexRep,
				@EvnPLDispOrp_IndexRepInReg = :EvnPLDispOrp_IndexRepInReg,
				@EvnPLDispOrp_fid = :EvnPLDispOrp_fid,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@EvnPLDispOrp_setDT = :EvnPLDispOrp_setDT,
				@EvnPLDispOrp_disDT = :EvnPLDispOrp_disDT,
				@EvnPLDispOrp_didDT = :EvnPLDispOrp_didDT,
				@EvnPLDispOrp_VizitCount = :EvnPLDispOrp_VizitCount,
				@EvnPLDispOrp_IsFinish = :EvnPLDispOrp_IsFinish,
				@EvnPLDispOrp_IsTwoStage = :EvnPLDispOrp_IsTwoStage,
				@ChildStatusType_id = :ChildStatusType_id,
				@AttachType_id = 2, -- доп. диспансеризация
				@DispClass_id = :DispClass_id, -- ддс
				@PayType_id = :PayType_id,
				@EvnPLDispOrp_consDT = :EvnPLDispOrp_consDate,
				@EvnPLDispOrp_IsMobile = :EvnPLDispOrp_IsMobile, 
				@EvnPLDispOrp_IsOutLpu = :EvnPLDispOrp_IsOutLpu, 
				@Lpu_mid = :Lpu_mid,
				@EvnPLDispOrp_IsRefusal = @EvnPLDispOrp_IsRefusal,
				@EvnDirection_aid = @EvnDirection_aid,
				@EvnPLDispOrp_IsSuspectZNO = :EvnPLDispOrp_IsSuspectZNO,
				@Diag_spid = :Diag_spid,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as EvnPLDispOrp_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, $data);

		if (!is_object($result))
		{
			return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение талона по ДД)');
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

		if ( !isset($data['EvnPLDispOrp_id']) )
		{
			$data['EvnPLDispOrp_id'] = $response[0]['EvnPLDispOrp_id'];
		}

		// Ищем AssessmentHealth связанный с EvnPLDispOrp_id, если нет его то добавляем новый, иначе обновляем
		$data['AssessmentHealth_id'] = NULL;
		$query = "
			select top 1 AssessmentHealth_id from v_AssessmentHealth (nolock) where EvnPLDisp_id = :EvnPLDispOrp_id
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
			$MedCare_id = $this->getMedCareForEvnVizitDispOrp($record['EvnVizitDispOrp_id'], 1);
			$this->saveMedCare(array(
				'MedCare_id' => $MedCare_id,
				'EvnVizitDispOrp_id' => $record['EvnVizitDispOrp_id'],
				'ConditMedCareType_nid' => empty($json['ConditMedCareType1_nid'])?null:$json['ConditMedCareType1_nid'],
				'PlaceMedCareType_nid' => empty($json['PlaceMedCareType1_nid'])?null:$json['PlaceMedCareType1_nid'],
				'ConditMedCareType_id' => empty($json['ConditMedCareType1_id'])?null:$json['ConditMedCareType1_id'],
				'PlaceMedCareType_id' => empty($json['PlaceMedCareType1_id'])?null:$json['PlaceMedCareType1_id'],
				'LackMedCareType_id' => empty($json['LackMedCareType1_id'])?null:$json['LackMedCareType1_id'],
				'MedCareType_id' => 1,
				'pmUser_id' => $data['pmUser_id']
			));

			// получаем MedCare_id для MedCareType_id = 2
			$MedCare_id = $this->getMedCareForEvnVizitDispOrp($record['EvnVizitDispOrp_id'], 2);
			$this->saveMedCare(array(
				'MedCare_id' => $MedCare_id,
				'EvnVizitDispOrp_id' => $record['EvnVizitDispOrp_id'],
				'ConditMedCareType_nid' => empty($json['ConditMedCareType2_nid'])?null:$json['ConditMedCareType2_nid'],
				'PlaceMedCareType_nid' => empty($json['PlaceMedCareType2_nid'])?null:$json['PlaceMedCareType2_nid'],
				'ConditMedCareType_id' => empty($json['ConditMedCareType2_id'])?null:$json['ConditMedCareType2_id'],
				'PlaceMedCareType_id' => empty($json['PlaceMedCareType2_id'])?null:$json['PlaceMedCareType2_id'],
				'LackMedCareType_id' => empty($json['LackMedCareType2_id'])?null:$json['LackMedCareType2_id'],
				'MedCareType_id' => 2,
				'pmUser_id' => $data['pmUser_id']
			));

			// получаем MedCare_id для MedCareType_id = 3
			$MedCare_id = $this->getMedCareForEvnVizitDispOrp($record['EvnVizitDispOrp_id'], 3);
			$this->saveMedCare(array(
				'MedCare_id' => $MedCare_id,
				'EvnVizitDispOrp_id' => $record['EvnVizitDispOrp_id'],
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
				update EvnVizitDisp set DispSurveilType_id = :DispSurveilType_id, EvnVizitDisp_IsVMP = :EvnVizitDisp_IsVMP, EvnVizitDisp_IsFirstTime = :EvnVizitDisp_IsFirstTime where EvnVizitDisp_id = :EvnVizitDisp_id
			";

			$result = $this->db->query($query, array(
				'DispSurveilType_id' => empty($json['DispSurveilType_id'])?null:$json['DispSurveilType_id'],
				'EvnVizitDisp_IsVMP' => empty($json['EvnVizitDisp_IsVMP'])?null:$json['EvnVizitDisp_IsVMP'],
				'EvnVizitDisp_IsFirstTime' => empty($json['EvnVizitDisp_IsFirstTime'])?null:$json['EvnVizitDisp_IsFirstTime'],
				'EvnVizitDisp_id' => $record['EvnVizitDispOrp_id'],
			));
		}

		$this->load->model('EvnUsluga_model');
		// Лабораторные исследования
		foreach ($data['EvnUslugaDispOrp'] as $key => $record) {
			if ($record['Record_Status'] == 3) {// удаление исследований
				$query = "
					declare
						@ErrCode int,
						@ErrMessage varchar(4000);
					exec p_EvnUslugaDispOrp_del "
					. "@EvnUslugaDispOrp_id = ?, "
					. "@pmUser_id = ?, "
					. "@Error_Code = @ErrCode output, "
					. "@Error_Message = @ErrMessage output;
					select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				$result = $this->db->query($query, array($record['EvnUslugaDispOrp_id'], $data['pmUser_id']));

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
			else if ($record['Record_Status'] != 1) {
				if ($record['Record_Status'] == 0)
				{
					$procedure = 'p_EvnUslugaDispOrp_ins';
				}
				else
				{
					$procedure = 'p_EvnUslugaDispOrp_upd';
				}

				// 1. ищем DopDispInfoConsent_id
				$record['DopDispInfoConsent_id'] = null;
				if (!empty($record['EvnUslugaDispOrp_id'])) {
					$query = "
						select top 1
							DopDispInfoConsent_id
						from
							v_EvnUslugaDispOrp (nolock)
						where
							EvnUslugaDispOrp_id = :EvnUslugaDispOrp_id
							and ISNULL(EvnUslugaDispOrp_IsVizitCode, 1) = 1
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

				$query = "
					declare
						@DopDispInfoConsent_id bigint,
						@ErrCode int,
						@ErrMessage varchar(4000);
					set @DopDispInfoConsent_id = :DopDispInfoConsent_id;
					exec {$ddicproc}
						@DopDispInfoConsent_id = @DopDispInfoConsent_id output,
						@EvnPLDisp_id = :EvnPLDisp_id, 
						@SurveyTypeLink_id = NULL,
						@DopDispInfoConsent_IsAgree = 2, 
						@DopDispInfoConsent_IsEarlier = 1, 
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @DopDispInfoConsent_id as DopDispInfoConsent_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";

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
					$setDT .= ' '.$record['EvnUslugaDispOrp_setTime'];
				}
				$disDT = null;
				if (!empty($record['EvnUslugaDispOrp_disDate'])) {
					$disDT = $record['EvnUslugaDispOrp_disDate'];

					if (!empty($record['EvnUslugaDispOrp_disTime'])) {
						$disDT .= ' '.$record['EvnUslugaDispOrp_disTime'];
					}
				}

				if ($record['LpuSection_id']=='')
					$record['LpuSection_id'] = Null;
				$query = "
					declare
						@pt bigint,
						@Res bigint,
						@ErrCode int,
						@ErrMessage varchar(4000);

					set @pt = (select top 1 PayType_id from v_PayType with (nolock) where PayType_SysNick = 'dopdisp');
					set @Res = :EvnUslugaDispOrp_id;

					exec " . $procedure . "
						@EvnUslugaDispOrp_id = @Res output,
						@EvnUslugaDispOrp_pid = :EvnUslugaDispOrp_pid,
						@Lpu_id = :Lpu_id,
						@Server_id = :Server_id,
						@PersonEvn_id = :PersonEvn_id,
						@EvnUslugaDispOrp_setDT = :EvnUslugaDispOrp_setDT,
						@EvnUslugaDispOrp_disDT = :EvnUslugaDispOrp_disDT,
						@EvnUslugaDispOrp_didDT = :EvnUslugaDispOrp_didDT,
						@LpuSection_uid = :LpuSection_uid,
						@MedSpecOms_id = :MedSpecOms_id,
						@LpuSectionProfile_id = :LpuSectionProfile_id,
						@MedPersonal_id = :MedPersonal_id,
						@MedStaffFact_id = :MedStaffFact_id,
						@UslugaComplex_id = :UslugaComplex_id,
						@DopDispInfoConsent_id = :DopDispInfoConsent_id,
						@PayType_id = @pt,
						@UslugaPlace_id = 1,
						@Lpu_uid = :Lpu_uid,
						@EvnUslugaDispOrp_Kolvo = 1,
						@ExaminationPlace_id = :ExaminationPlace_id,
						@EvnPrescrTimetable_id = null,
						@EvnPrescr_id = null,
						@EvnUslugaDispOrp_Result = :EvnUslugaDispOrp_Result,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;

					select @Res as EvnUslugaDispOrp_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
					'DopDispInfoConsent_id' => $record['DopDispInfoConsent_id'],
					'Lpu_uid' => (!empty($record['Lpu_uid']) ? $record['Lpu_uid'] : NULL),
					'ExaminationPlace_id' => (!empty($record['ExaminationPlace_id']) ? $record['ExaminationPlace_id'] : NULL),
					'EvnUslugaDispOrp_Result' => (!empty($record['EvnUslugaDispOrp_Result']) ? $record['EvnUslugaDispOrp_Result'] : NULL),
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

				$record['EvnUslugaDispOrp_id'] = $response[0]['EvnUslugaDispOrp_id'];
			}
		}

		// чистим в карте согласия по которым больше нет услуг
		$query = "
			select 
				DopDispInfoConsent_id
			from 
				v_DopDispInfoConsent (nolock) ddic
			where EvnPLDisp_id = :EvnPLDisp_id 
			and (not exists (select top 1 EvnVizitDispOrp_id from v_EvnVizitDispOrp (nolock) where DopDispInfoConsent_id = ddic.DopDispInfoConsent_id))
			and (not exists (select top 1 EvnUslugaDispOrp_id from v_EvnUslugaDispOrp (nolock) where DopDispInfoConsent_id = ddic.DopDispInfoConsent_id))
		";
		$result = $this->db->query($query, array(
			'EvnPLDisp_id' => $data['EvnPLDispOrp_id']
		));
		if (is_object($result)) {
			$resp = $result->result('array');
			foreach($resp as $one) {
				$query = "
					declare
						@ErrCode int,
						@ErrMessage varchar(4000);

					exec p_DopDispInfoConsent_del
						@DopDispInfoConsent_id = :DopDispInfoConsent_id,
						@pmUser_id = :pmUser_id, 
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;

					select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
				select top 1
					EvnUslugaDispOrp_id,
					UslugaComplex_id,
					PayType_id,
					convert(varchar(10), EvnUslugaDispOrp_setDT, 104) as EvnUslugaDispOrp_setDate
				from v_EvnUslugaDispOrp with (nolock)
				where EvnUslugaDispOrp_pid = :EvnUslugaDispOrp_pid
					and EvnUslugaDispOrp_IsVizitCode = 2
			";
			$result = $this->db->query($query, array(
				'EvnUslugaDispOrp_pid' => $data['EvnPLDispOrp_id']
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

			$data['ageDate'] = substr($data['EvnPLDispOrp_setDT'], 0, 4) . '-12-31';

			$filter = "";
			if (getRegionNick() == 'buryatiya') {
				$filter .= "
					and (
						not (UslugaSurveyLink_From = 0 and UslugaSurveyLink_To = 1 and DispClass_id IN (3,7))
						or exists(
							select top 1
								eudo.EvnUslugaDispOrp_id
							from
								v_EvnUslugaDispOrp eudo (nolock)
								inner join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = eudo.UslugaComplex_id
							where
								eudo.EvnUslugaDispOrp_pid = :EvnPLDispOrp_id and
								uc.UslugaComplex_Code IN ('004043', '161204')
						)
					)				
				";
			}

			// Определяем UslugaComplex_id через $schema.UslugaSurveyLink
			$query = "
				declare @sex_id bigint, @age int, @ageDate datetime;

				set @ageDate = :ageDate;

				select top 1
					@sex_id = ISNULL(Sex_id, 3),
					@age = dbo.Age2(Person_BirthDay, ISNULL(@ageDate, dbo.tzGetDate()))
				from v_PersonState ps (nolock)
				where ps.Person_id = :Person_id

				select top 1 USL.UslugaComplex_id
				from {$data['session']['region']['schema']}.v_UslugaSurveyLink USL with (nolock)
				where
					USL.DispClass_id = :DispClass_id
					and ISNULL(USL.Sex_id, @sex_id) = @sex_id
					and @age between ISNULL(USL.UslugaSurveyLink_From, 0) and ISNULL(USL.UslugaSurveyLink_To, 999)
					and ISNULL(USL.UslugaSurveyLink_IsDel, 1) = 1
					and (USL.UslugaSurveyLink_begDate is null or USL.UslugaSurveyLink_begDate <= :EvnPLDispOrp_setDT)
					and (USL.UslugaSurveyLink_endDate is null or USL.UslugaSurveyLink_endDate >= :EvnPLDispOrp_setDT)
					{$filter}
			";
			$result = $this->db->query($query, array(
				'DispClass_id' => $data['DispClass_id'],
				'EvnPLDispOrp_id' => $data['EvnPLDispOrp_fid'],
				'EvnPLDispOrp_setDT' => $data['EvnPLDispOrp_setDT'],
				'Person_id' => $data['Person_id'],
				'ageDate' => $data['ageDate'],
			));

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
					declare
						@id bigint = :EvnUslugaDispOrp_id,
						@pt bigint = :PayType_id,
						@ErrCode int,
						@ErrMessage varchar(4000);

					if ( @pt is null )
						set @pt = (select top 1 PayType_id from v_PayType with (nolock) where PayType_SysNick = 'dopdisp');

					exec p_EvnUslugaDispOrp_" . (!empty($uslugaData['EvnUslugaDispOrp_id']) ? "upd" : "ins") . "
						@EvnUslugaDispOrp_id = @id output,
						@EvnUslugaDispOrp_pid = :EvnUslugaDispOrp_pid,
						@UslugaComplex_id = :UslugaComplex_id,
						@EvnUslugaDispOrp_setDT = :EvnUslugaDispOrp_setDT,
						@EvnUslugaDispOrp_IsVizitCode = 2,
						@Lpu_id = :Lpu_id,
						@Server_id = :Server_id,
						@PersonEvn_id = :PersonEvn_id,
						@PayType_id = @pt,
						@UslugaPlace_id = 1,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;

					select @id as EvnUslugaDispOrp_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
			else if ( !empty($uslugaData['EvnUslugaDispOrp_id']) ) {
				$query = "
					declare
						@ErrCode int,
						@ErrMessage varchar(4000);

					exec p_EvnUslugaDispOrp_del
						@EvnUslugaDispOrp_id = :EvnUslugaDispOrp_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;

					select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				$result = $this->db->query($query, array(
					'EvnUslugaDispOrp_id' => $uslugaData['EvnUslugaDispOrp_id'],
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
	function saveMedCare($data) {
		if (!empty($data['MedCare_id']) && $data['MedCare_id'] > 0) {
			$proc = 'p_MedCare_upd';
		} else {
			$proc = 'p_MedCare_ins';
			$data['MedCare_id'] = null;
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :MedCare_id;
			
			exec {$proc}
				@MedCare_id = @Res output, 
				@EvnVizitDisp_id = :EvnVizitDispOrp_id,
				@LackMedCareType_id = :LackMedCareType_id,
				@ConditMedCareType_nid = :ConditMedCareType_nid,
				@PlaceMedCareType_nid = :PlaceMedCareType_nid,
				@ConditMedCareType_id = :ConditMedCareType_id,
				@PlaceMedCareType_id = :PlaceMedCareType_id,
				@MedCareType_id = :MedCareType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as MedCare_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
	 * Возвращает данные
	 */
	function getMedCareForEvnVizitDispOrp($EvnVizitDispOrp_id, $MedCareType_id) {
		$query = "
			select top 1
				MedCare_id
			from
				v_MedCare (nolock)
			where
				EvnVizitDisp_id = :EvnVizitDispOrp_id
				and MedCareType_id = :MedCareType_id
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
	 * Поиск талонов по ДД
	 */
	function searchEvnPLDispOrp($data)
	{
		$filter    = "";
		$join_str  = "";

		if ($data['PersonAge_Min'] > $data['PersonAge_Max'])
		{
			return false;
		}

		$queryParams = array();

		if (($data['DocumentType_id'] > 0) || ($data['OrgDep_id'] > 0))
		{
			$join_str .= " inner join [Document] (nolock) on [Document].[Document_id] = [PS].[Document_id]";

			if ($data['DocumentType_id'] > 0)
			{
				$join_str .= " and [Document].[DocumentType_id] = :DocumentType_id";
				$queryParams['DocumentType_id'] = $data['DocumentType_id'];
			}

			if ($data['OrgDep_id'] > 0)
			{
				$join_str .= " and [Document].[OrgDep_id] = :OrgDep_id";
				$queryParams['OrgDep_id'] = $data['OrgDep_id'];
			}
		}

		if (($data['OMSSprTerr_id'] > 0) || ($data['OrgSmo_id'] > 0) || ($data['PolisType_id'] > 0))
		{
			$join_str .= " inner join [Polis] (nolock) on [Polis].[Polis_id] = [PS].[Polis_id]";

			if ($data['OMSSprTerr_id'] > 0)
			{
				$join_str .= " and [Polis].[OmsSprTerr_id] = :OMSSprTerr_id";
				$queryParams['OMSSprTerr_id'] = $data['OMSSprTerr_id'];
			}

			if ($data['OrgSmo_id'] > 0)
			{
				$join_str .= " and [Polis].[OrgSmo_id] = :OrgSmo_id";
				$queryParams['OrgSmo_id'] = $data['OrgSmo_id'];
			}

			if ($data['PolisType_id'] > 0)
			{
				$join_str .= " and [Polis].[PolisType_id] = :PolisType_id";
				$queryParams['PolisType_id'] = $data['PolisType_id'];
			}
		}

		if (($data['Org_id'] > 0) || ($data['Post_id'] > 0))
		{
			$join_str .= " inner join [Job] (nolock) on [Job].[Job_id] = [PS].[Job_id]";

			if ($data['Org_id'] > 0)
			{
				$join_str .= " and [Job].[Org_id] = :Org_id";
				$queryParams['Org_id'] = $data['Org_id'];
			}

			if ($data['Post_id'] > 0)
			{
				$join_str .= " and [Job].[Post_id] = :Post_id";
				$queryParams['Post_id'] = $data['Post_id'];
			}
		}

		if (($data['KLRgn_id'] > 0) || ($data['KLSubRgn_id'] > 0) || ($data['KLCity_id'] > 0) || ($data['KLTown_id'] > 0) || ($data['KLStreet_id'] > 0) || (strlen($data['Address_House']) > 0))
		{
			$join_str .= " inner join [Address] (nolock) on [Address].[Address_id] = [PS].[UAddress_id]";

			if ($data['KLRgn_id'] > 0)
			{
				$filter .= " and [Address].[KLRgn_id] = :KLRgn_id";
				$queryParams['KLRgn_id'] = $data['KLRgn_id'];
			}

			if ($data['KLSubRgn_id'] > 0)
			{
				$filter .= " and [Address].[KLSubRgn_id] = :KLSubRgn_id";
				$queryParams['KLSubRgn_id'] = $data['KLSubRgn_id'];
			}

			if ($data['KLCity_id'] > 0)
			{
				$filter .= " and [Address].[KLCity_id] = :KLCity_id";
				$queryParams['KLCity_id'] = $data['KLCity_id'];
			}

			if ($data['KLTown_id'] > 0)
			{
				$filter .= " and [Address].[KLTown_id] = :KLTown_id";
				$queryParams['KLTown_id'] = $data['KLTown_id'];
			}

			if ($data['KLStreet_id'] > 0)
			{
				$filter .= " and [Address].[KLStreet_id] = :KLStreet_id";
				$queryParams['KLStreet_id'] = $data['KLStreet_id'];
			}

			if (strlen($data['Address_House']) > 0)
			{
				$filter .= " and [Address].[Address_House] = :Address_House";
				$queryParams['Address_House'] = $data['Address_House'];
			}
		}

		if ( isset($data['EvnPLDispOrp_disDate'][1]) )
		{
			$filter .= " and [EvnPLDispOrp].[EvnPLDispOrp_disDate] <= :EvnPLDispOrp_disDate1";
			$queryParams['EvnPLDispOrp_disDate1'] = $data['EvnPLDispOrp_disDate'][1];
		}

		if ( isset($data['EvnPLDispOrp_disDate'][0]) )
		{
			$filter .= " and [EvnPLDispOrp].[EvnPLDispOrp_disDate] >= :EvnPLDispOrp_disDate1";
			$queryParams['EvnPLDispOrp_disDate0'] = $data['EvnPLDispOrp_disDate'][0];
		}

		if ($data['EvnPLDispOrp_IsFinish'] > 0)
		{
			$filter .= " and [EvnPLDispOrp].[EvnPLDispOrp_IsFinish] = :EvnPLDispOrp_IsFinish";
			$queryParams['EvnPLDispOrp_IsFinish'] = $data['EvnPLDispOrp_IsFinish'];
		}

		if ( isset($data['EvnPLDispOrp_setDate'][1]) )
		{
			$filter .= " and [EvnPLDispOrp].[EvnPLDispOrp_setDate] <= :EvnPLDispOrp_setDate1";
			$queryParams['EvnPLDispOrp_setDate1'] = $data['EvnPLDispOrp_setDate'][1];
		}

		if ( isset($data['EvnPLDispOrp_setDate'][0]) )
		{
			$filter .= " and [EvnPLDispOrp].[EvnPLDispOrp_setDate] >= :EvnPLDispOrp_setDate0";
			$queryParams['EvnPLDispOrp_setDate0'] = $data['EvnPLDispOrp_setDate'][0];
		}

		if ($data['PersonAge_Max'] > 0)
		{
			$filter .= " and [EvnPLDispOrp].[Person_Age] <= :PersonAge_Max";
			$queryParams['PersonAge_Max'] = $data['PersonAge_Max'];
		}

		if ($data['PersonAge_Min'] > 0)
		{
			$filter .= " and [EvnPLDispOrp].[Person_Age] >= :PersonAge_Min";
			$queryParams['PersonAge_Min'] = $data['PersonAge_Min'];
		}

		if (($data['PersonCard_Code'] != '') || ($data['LpuRegion_id'] > 0))
		{
			$join_str .= " inner join [v_PersonCard] PC (nolock) on [PC].[Person_id] = [PS].[Person_id]";

			if (strlen($data['PersonCard_Code']) > 0)
			{
				$filter .= " and [PC].[PersonCard_Code] = :PersonCard_Code";
				$queryParams['PersonCard_Code'] = $data['PersonCard_Code'];
			}

			if (strlen($data['LpuRegion_id']) > 0)
			{
				$filter .= " and [PC].[LpuRegion_id] = :LpuRegion_id";
				$queryParams['LpuRegion_id'] = $data['LpuRegion_id'];
			}
		}
		if ( isset($data['Person_Birthday'][1]) )
		{
			$filter .= " and [PS].[Person_Birthday] <= :Person_Birthday1";
			$queryParams['Person_Birthday1'] = $data['Person_Birthday'][1];
		}

		if ( isset($data['Person_Birthday'][0]) )
		{
			$filter .= " and [PS].[Person_Birthday] >= :Person_Birthday0";
			$queryParams['Person_Birthday0'] = $data['Person_Birthday'][0];
		}

		if (strlen($data['Person_Firname']) > 0)
		{
			$filter .= " and [PS].[Person_Firname] like :Person_Firname";
			$queryParams['Person_Firname'] = $data['Person_Firname']."%";
		}

		if (strlen($data['Person_Secname']) > 0)
		{
			$filter .= " and [PS].[Person_Secname] like :Person_Secname";
			$queryParams['Person_Secname'] = $data['Person_Secname']."%";
		}

		if ($data['Person_Snils'] > 0)
		{
			$filter .= " and [PS].[Person_Snils] = :Person_Snils";
			$queryParams['Person_Snils'] = $data['Person_Snils'];
		}

		if (strlen($data['Person_Surname']) > 0)
		{
			$filter .= " and [PS].[Person_Surname] like :Person_Surname";
			$queryParams['Person_Surname'] = $data['Person_Surname']."%";
		}

		if ($data['PrivilegeType_id'] > 0)
		{
			$join_str .= " inner join [v_PersonPrivilege] [PP] (nolock) on [PP].[Person_id] = [EvnPLDispOrp].[Person_id] and [PP].[PrivilegeType_id] = :PrivilegeType_id and [PP].[PersonPrivilege_begDate] is not null and [PP].[PersonPrivilege_begDate] <= dbo.tzGetDate() and ([PP].[PersonPrivilege_endDate] is null or [PP].[PersonPrivilege_endDate] >= cast(convert(char(10), dbo.tzGetDate(), 112) as datetime)) and [PP].[Lpu_id] = :Lpu_id";
			$queryParams['PrivilegeType_id'] = $data['PrivilegeType_id'];
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}

		if ($data['Sex_id'] >= 0)
		{
			$filter .= " and [PS].[Sex_id] = :Sex_id";
			$queryParams['Sex_id'] = $data['Sex_id'];
		}

		if ($data['SocStatus_id'] > 0)
		{
			$filter .= " and [PS].[SocStatus_id] = :SocStatus_id";
			$queryParams['SocStatus_id'] = $data['SocStatus_id'];
		}

		$query = "
			SELECT DISTINCT TOP 100
				[EvnPLDispOrp].[EvnPLDispOrp_id] as [EvnPLDispOrp_id],
				[EvnPLDispOrp].[Person_id] as [Person_id],
				[EvnPLDispOrp].[Server_id] as [Server_id],
				[EvnPLDispOrp].[PersonEvn_id] as [PersonEvn_id],
				RTRIM([PS].[Person_Surname]) as [Person_Surname],
				RTRIM([PS].[Person_Firname]) as [Person_Firname],
				RTRIM([PS].[Person_Secname]) as [Person_Secname],
				convert(varchar(10), [PS].[Person_Birthday], 104) as [Person_Birthday],
				[EvnPLDispOrp].[EvnPLDispOrp_VizitCount] as [EvnPLDispOrp_VizitCount],
				[IsFinish].[YesNo_Name] as [EvnPLDispOrp_IsFinish],
				convert(varchar(10), [EvnPLDispOrp].[EvnPLDispOrp_setDate], 104) as [EvnPLDispOrp_setDate],
				convert(varchar(10), [EvnPLDispOrp].[EvnPLDispOrp_disDate], 104) as [EvnPLDispOrp_disDate]
			FROM [v_EvnPLDispOrp] [EvnPLDispOrp] (nolock)
				inner join [v_PersonState] [PS] (nolock) on [PS].[Person_id] = [EvnPLDispOrp].[Person_id]
				left join [YesNo] [IsFinish] (nolock) on [IsFinish].[YesNo_id] = [EvnPLDispOrp].[EvnPLDispOrp_IsFinish]
				" . $join_str . "
			WHERE (1 = 1)
				" . $filter . "
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
	 * Получение списка записей для потокового ввода
	 */
	function getEvnPLDispOrpStreamList($data)
	{

		$query = "
			SELECT DISTINCT TOP 100
				[EvnPLDispOrp].[EvnPLDispOrp_id] as [EvnPLDispOrp_id],
				[EvnPLDispOrp].[Person_id] as [Person_id],
				[EvnPLDispOrp].[Server_id] as [Server_id],
				[EvnPLDispOrp].[PersonEvn_id] as [PersonEvn_id],
				RTRIM([PS].[Person_Surname]) + ' ' + RTRIM([PS].[Person_Firname]) + ' ' + RTRIM([PS].[Person_Secname]) as [Person_Fio],
				convert(varchar(10), [PS].[Person_Birthday], 104) as [Person_Birthday],
				[EvnPLDispOrp].[EvnPLDispOrp_VizitCount] as [EvnPLDispOrp_VizitCount],
				[IsFinish].[YesNo_Name] as [EvnPLDispOrp_IsFinish],
				convert(varchar(10), [EvnPLDispOrp].[EvnPLDispOrp_setDate], 104) as [EvnPLDispOrp_setDate],
				convert(varchar(10), [EvnPLDispOrp].[EvnPLDispOrp_disDate], 104) as [EvnPLDispOrp_disDate]
			FROM [v_EvnPLDispOrp] [EvnPLDispOrp] (nolock)
				inner join [v_PersonState] [PS] (nolock) on [PS].[Person_id] = [EvnPLDispOrp].[Person_id]
				left join [YesNo] [IsFinish] (nolock) on [IsFinish].[YesNo_id] = [EvnPLDispOrp].[EvnPLDispOrp_IsFinish]
			WHERE EvnPLDispOrp_updDT >= ? and [EvnPLDispOrp].pmUser_updID= ? ";

		$result = $this->db->query($query, array($data['begDate']." ".$data['begTime'], $data['pmUser_id']));

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
	 * Получение списка лет, в которые выписывались талоны по ДД с количеством талонов, для комбобокса
	 */
	function getEvnPLDispOrpYears($data)
	{
		$sql = "
        select
			count(EPLDO.EvnPLDispOrp_id) as count,
			year(EPLDO.EvnPLDispOrp_setDate) as EvnPLDispOrp_Year
		from
			v_PersonState PS with (nolock)
			inner join [v_EvnPLDispOrp] [EPLDO] with (nolock) on [PS].[Person_id] = [EPLDO].[Person_id] and [EPLDO].Lpu_id = :Lpu_id and [EPLDO].DispClass_id IN (3,7)
		where
  		    exists (
  		        select top 1
  		            personcard_id
                from v_PersonCard PC with (nolock)
                    left join v_LpuRegion LR with (nolock) on LR.LpuRegion_id = PC.LpuRegion_id
                WHERE PC.Person_id = PS.Person_id
                and PC.Lpu_id = :Lpu_id)
                and year([EPLDO].EvnPLDispOrp_setDate) >= 2013
			GROUP BY
				year(EPLDO.EvnPLDispOrp_setDate)
			ORDER BY
				year(EPLDO.EvnPLDispOrp_setDate)
		";

		//echo getDebugSQL($sql, $data); die;
		$res = $this->db->query($sql, $data);
		if ( is_object($res) )
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
			count(EPLDO.EvnPLDispOrp_id) as count,
			year(EPLDO.EvnPLDispOrp_setDate) as EvnPLDispOrp_Year
		from
			v_PersonState PS with (nolock)
			inner join [v_EvnPLDispOrp] [EPLDO] with (nolock) on [PS].[Person_id] = [EPLDO].[Person_id] and [EPLDO].Lpu_id = :Lpu_id and [EPLDO].DispClass_id IN (4,8)
		where
		[EPLDO].EvnPLDispOrp_setDate >= cast('2013-01-01' as datetime)  and [EPLDO].EvnPLDispOrp_setDate <= cast('2013-12-31' as datetime)  and  exists (select top 1 personcard_id from v_PersonCard PC with (nolock)  left join v_LpuRegion LR with (nolock) on LR.LpuRegion_id = PC.LpuRegion_id WHERE PC.Person_id = PS.Person_id and PC.Lpu_id = :Lpu_id)
			--year([EPLDO].EvnPLDispOrp_setDate) >= 2013
			GROUP BY
				year(EPLDO.EvnPLDispOrp_setDate)
			ORDER BY
				year(EPLDO.EvnPLDispOrp_setDate)
		";

		//echo getDebugSQL($sql, $data); die;
		$res = $this->db->query($sql, $data);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Проверка, есть ли талон на этого человека в этом году
	 */
	function checkIfEvnPLDispOrpExists($data)
	{
		$filter = ($data['stage'] == 2)?" and DispClass_id IN (4,8)":" and DispClass_id IN (3,7)";
		$filter_1st = ($data['stage'] == 2)?" and epldorp.DispClass_id IN (3,7)":" and (1=0)";
		$filter_for1st = ($data['stage'] == 2)?" and persdo.PersonDispOrp_Year = @PersonDispOrp_Year":" and persdo.PersonDispOrp_Year = :Year";

		$sql = "
			declare
				@EvnPLDispOrp_fid bigint,
				@PersonDispOrp_Year int,
				@age int

			select top 1
				@age = dbo.Age2(Person_BirthDay, dbo.tzGetDate())
			from
				v_PersonState ps (nolock)
			where
				ps.Person_id = :Person_id

			select top 1
				@EvnPLDispOrp_fid = epldorp.EvnPLDispOrp_id,
				@PersonDispOrp_Year = year(epldorp.EvnPLDispOrp_setDate)
			from
				v_EvnPLDispOrp epldorp (nolock)
			where
				epldorp.Person_id = :Person_id and epldorp.EvnPLDispOrp_IsTwoStage = 2 and epldorp.EvnPLDispOrp_IsFinish = 2
				and not exists(
					select top 1 EvnPLDispOrp_id from v_EvnPLDispOrp epldorpsec (nolock) where epldorpsec.EvnPLDispOrp_fid = epldorp.EvnPLDispOrp_id
				)
				{$filter_1st}
			order by
				epldorp.EvnPLDispOrp_setDate desc

			select top 1
				case when persdo.CategoryChildType_id in (5,6,7) then 'orpadopted' else 'orp' end as CategoryChildType,
				evnpl.EvnPLDispOrp_id,
				evnprof.EvnPLDispTeenInspection_id,
				@EvnPLDispOrp_fid as EvnPLDispOrp_fid
			from
				v_PersonDispOrp (nolock) persdo
				outer apply(
					SELECT top 1
						EvnPLDispOrp_id
					FROM
						v_EvnPLDispOrp (nolock)
					WHERE
						Person_id = persdo.Person_id and year(EvnPLDispOrp_setDate) = persdo.PersonDispOrp_Year -- Контроль внутри и между МО. 
						{$filter}
				) evnpl
				outer apply(
					SELECT top 1
						epldti.EvnPLDispTeenInspection_id
					FROM
						v_EvnPLDispTeenInspection epldti (nolock)
					WHERE
						epldti.Person_id = persdo.Person_id
						and year(epldti.EvnPLDispTeenInspection_setDT) = persdo.PersonDispOrp_Year -- Контроль внутри и между МО.
						and epldti.DispClass_id = 10 -- профилактический осмотр
						and epldti.EvnPLDispTeenInspection_IsFinish = 2 -- закрытый
						and @age >= 3 -- 3 лет и старше
				) evnprof
			where 
				persdo.Person_id = :Person_id
				and persdo.CategoryChildType_id < 8
				{$filter_for1st}
		";

		$res = $this->db->query($sql, array('Person_id' => $data['Person_id'], 'Lpu_id' => $data['Lpu_id'], 'Year' => $data['Year']));
		if ( is_object($res) )
		{
			$sel = $res->result('array');
			if ( count($sel) > 0 ) {
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
		}
		else
			return false;
	}

	/**
	 * Получение данных для отображения в ЭМК
	 */
	function getEvnPLDispOrpViewData($data) {
		$queryParams = array(
			'EvnPLDisp_id' => $data['EvnPLDisp_id']
		);
		// – Редактирование карты диспансеризации / профосмотра доступно только из АРМ врача поликлиники, пользователем с привязкой к врачу терапевту (ВОП) / педиатру (ВОП),
		// отделение места работы которого совпадает с отделением места работы врача, создавшего карту.
		$accessType = "'view' as accessType,";
		if (!empty($data['session']['CurARM']['PostMed_id']) && in_array($data['session']['CurARM']['PostMed_id'], array(73,74,75,76,40,46,47)) && !empty($data['session']['CurARM']['LpuSection_id'])) {
			$accessType = "case when ISNULL(msf.LpuSection_id, :LpuSection_id) = :LpuSection_id then 'edit' else 'view' end as accessType,";
			$queryParams['LpuSection_id'] = $data['session']['CurARM']['LpuSection_id'];
		}

		$query = "
			select
				epldo.EvnPLDispOrp_id,
				case
					when epldo.MedStaffFact_id is not null then ISNULL(l.Lpu_Nick + ' ', '') + ISNULL(ls.LpuSection_Name + ' ', '') + ISNULL(msf.Person_Fio, '') 
					else ISNULL(l.Lpu_Nick + ' ', '') + ISNULL(pu.pmUser_Name, '')
				end as AuthorInfo,
				'EvnPLDispOrp' as Object,
				epldo.DispClass_id,
				epldo.Person_id,
				epldo.PersonEvn_id,
				epldo.Server_id,
				dc.DispClass_Code,
				dc.DispClass_Name,
				{$accessType}
				epldo.PayType_id,
				pt.PayType_Name,
				convert(varchar(10), epldo.EvnPLDispOrp_setDT, 104) as EvnPLDispOrp_setDate,
				convert(varchar(10), epldo.EvnPLDispOrp_consDT, 104) as EvnPLDispOrp_consDate,
				ah.HealthKind_id,
				hk.HealthKind_Name,
				ISNULL(epldo.EvnPLDispOrp_IsFinish, 1) as EvnPLDispOrp_IsFinish
			from
				v_EvnPLDispOrp epldo (nolock)
				left join v_Lpu l (nolock) on l.Lpu_id = epldo.Lpu_id
				left join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = epldo.MedStaffFact_id
				left join v_LpuSection ls (nolock) on ls.LpuSection_id = msf.LpuSection_id
				left join v_pmUser pu (nolock) on pu.pmUser_id = epldo.pmUser_updID
				outer apply(
					select top 1 * from v_AssessmentHealth (nolock) where EvnPLDisp_id = epldo.EvnPLDispOrp_id
				) ah
				left join v_DispClass dc (nolock) on dc.DispClass_id = epldo.DispClass_id
				left join v_PayType pt (nolock) on pt.PayType_id = epldo.PayType_id
				left join v_HealthKind hk (nolock) on hk.HealthKind_id = ah.HealthKind_id
			where
				epldo.EvnPLDispOrp_id = :EvnPLDisp_id
		";

		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Сохранение осмотров
	 */
	function saveEvnVizitDispOrp($data) {

		if (empty($data['EvnVizitDispOrp_id'])) {
			$procedure = 'p_EvnVizitDispOrp_ins';
		} else {
			$procedure = 'p_EvnVizitDispOrp_upd';
		}

		if (empty($data['DopDispInfoConsent_id']))
		{
			return array(0 => array('Error_Msg' => 'Ошибка, для осмотра не найдено согласие')); // чтобы удостовериться что для всех осмотров сохраняются ссылки на их DopDispInfoConsent'ы.
		}

		// проверяем, есть ли уже такое посещение
		$query = "
			select 
				count(*) as cnt
			from
				v_EvnVizitDispOrp (nolock)
			where
				EvnVizitDispOrp_pid = ?
				and DopDispInfoConsent_id = ?
				and ( EvnVizitDispOrp_id <> isnull(?, 0) )
		";
		$result = $this->db->query(
			$query,
			array(
				$data['EvnPLDispOrp_id'],
				$data['DopDispInfoConsent_id'],
				$data['EvnVizitDispOrp_id']
			)
		);
		if (!is_object($result))
		{
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение посещения)'));
		}
		$response = $result->result('array');
		if (!is_array($response) || count($response) == 0)
		{
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение посещения)'));
		}
		else if ($response[0]['cnt'] >= 1)
		{
			return array(array('Error_Msg' => 'Обнаружено дублирование осмотров, это недопустимо.'));
		}

		$setDT = $data['EvnVizitDispOrp_setDate'];
		if (!empty($data['EvnVizitDispOrp_setTime'])) {
			$setDT .= ' '.$data['EvnVizitDispOrp_setTime'];
		}
		$disDT = null;
		if (!empty($data['EvnVizitDispOrp_disDate'])) {
			$disDT = $data['EvnVizitDispOrp_disDate'];

			if (!empty($data['EvnVizitDispOrp_disTime'])) {
				$disDT .= ' '.$data['EvnVizitDispOrp_disTime'];
			}
		}

		if (!empty($data['UslugaComplex_id'])) {
			// Надо проверить что сохраняемая услуга соответствует списку возможных, чтобы пользователи никак не могли сохранить левую услугу. (refs #71538)
			$sql = "
				SELECT top 1
					stl.SurveyTypeLink_id
				FROM
					v_SurveyTypeLink stl with (nolock)
					inner join v_SurveyType st with (nolock) on st.SurveyType_id = stl.SurveyType_id
				WHERE
					st.OrpDispSpec_id = :OrpDispSpec_id
					and stl.UslugaComplex_id = :UslugaComplex_id
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
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :EvnVizitDispOrp_id;

			exec {$procedure}
				@EvnVizitDispOrp_id = @Res output,
				@EvnVizitDispOrp_pid = :EvnVizitDispOrp_pid,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@EvnVizitDispOrp_setDT = :EvnVizitDispOrp_setDT,
				@EvnVizitDispOrp_disDT = :EvnVizitDispOrp_disDT,
				@EvnVizitDispOrp_didDT = null,
				@LpuSection_id = :LpuSection_id,
				@MedSpecOms_id = :MedSpecOms_id,
				@LpuSectionProfile_id = :LpuSectionProfile_id,
				@MedPersonal_id = :MedPersonal_id,
				@MedStaffFact_id = :MedStaffFact_id,
				@MedPersonal_sid = null,
				@PayType_id = null,
				@UslugaComplex_id = :UslugaComplex_id,
				@OrpDispSpec_id = :OrpDispSpec_id,
				@DopDispInfoConsent_id = :DopDispInfoConsent_id,
				@Diag_id = :Diag_id,
				@TumorStage_id = :TumorStage_id,
				@DopDispDiagType_id = :DopDispDiagType_id,
				@DopDispAlien_id = :DopDispAlien_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as EvnVizitDispOrp_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
			'TumorStage_id' => (!empty($data['TumorStage_id'])?$data['TumorStage_id']:NULL),
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

		if (!empty($response[0]['EvnVizitDispOrp_id'])) {
			// сохраняем сопутствующие диагнозы
			if (!empty($data['EvnDiagDopDispGridData'])) {
				$data['EvnDiagDopDispGridData'] = json_decode($data['EvnDiagDopDispGridData'], true);
			} else {
				$data['EvnDiagDopDispGridData'] = array();
			}
			foreach($data['EvnDiagDopDispGridData'] as $EvnDiagDopDisp) {
				if ($EvnDiagDopDisp['Record_Status'] == 3) {// удаление
					$query = "
						declare
							@ErrCode int,
							@ErrMessage varchar(4000);
						exec p_EvnDiagDopDisp_del
							@EvnDiagDopDisp_id = :EvnDiagDopDisp_id,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;
						select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
							count(*) as cnt
						from
							v_EvnDiagDopDisp (nolock)
						where
							EvnDiagDopDisp_pid = ?
							and Diag_id = ?
							and DiagSetClass_id = 3
							and ( EvnDiagDopDisp_id <> isnull(?, 0) )
					";
					$result_eddd = $this->db->query(
						$query,
						array(
							$response[0]['EvnVizitDispOrp_id'],
							$EvnDiagDopDisp['Diag_id'],
							$EvnDiagDopDisp['Record_Status'] == 0 ? null : $EvnDiagDopDisp['EvnDiagDopDisp_id']
						)
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
						declare
							@EvnDiagDopDisp_id bigint,
							@ErrCode int,
							@curdate datetime = dbo.tzGetDate(),
							@ErrMessage varchar(4000);
						set @EvnDiagDopDisp_id = :EvnDiagDopDisp_id;
						exec {$proc_evdd}
							@EvnDiagDopDisp_id = @EvnDiagDopDisp_id output,
							@EvnDiagDopDisp_setDT = @curdate,
							@EvnDiagDopDisp_pid = :EvnDiagDopDisp_pid,
							@Diag_id = :Diag_id,
							@DiagSetClass_id = :DiagSetClass_id,
							@DeseaseDispType_id = :DeseaseDispType_id,
							@Lpu_id = :Lpu_id,
							@Server_id = :Server_id,
							@PersonEvn_id = :PersonEvn_id,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;
						select @EvnDiagDopDisp_id as EvnDiagDopDisp_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					";
					$result_eddd = $this->db->query($query, array(
						'EvnDiagDopDisp_id' => $EvnDiagDopDisp['Record_Status'] == 0 ? null : $EvnDiagDopDisp['EvnDiagDopDisp_id'],
						'EvnDiagDopDisp_pid' => $response[0]['EvnVizitDispOrp_id'],
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
				$query = "
					declare
						@ErrCode int,
						@EvnUslugaDispOrp_id bigint,
						@PayType_id bigint,
						@ErrMessage varchar(4000);
					set @EvnUslugaDispOrp_id = :EvnUslugaDispOrp_id;
					set @PayType_id = (select top 1 PayType_id from v_PayType with (nolock) where PayType_SysNick = 'dopdisp');
					exec {$proc}
						@EvnUslugaDispOrp_id = @EvnUslugaDispOrp_id output,
						@EvnUslugaDispOrp_pid = :EvnUslugaDispOrp_pid,
						@UslugaComplex_id = :UslugaComplex_id,
						@Lpu_id = :Lpu_id,
						@Server_id = :Server_id,
						@PayType_id = @PayType_id,
						@UslugaPlace_id = 1,
						@PersonEvn_id = :PersonEvn_id,
						@EvnPrescrTimetable_id = null,
						@EvnPrescr_id = null,
						@Diag_id = :Diag_id,
						@LpuSection_uid = :LpuSection_id,
						@MedPersonal_id = :MedPersonal_id,
						@MedStaffFact_id = :MedStaffFact_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @EvnUslugaDispOrp_id as EvnUslugaDispOrp_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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

				if ( !is_object($result) ) {
					return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение услуги) (' . __LINE__ . ')'));
				}

				$resp_usl = $result->result('array');

				if ( !is_array($resp_usl) || count($resp_usl) == 0 ) {
					return array(array('Error_Msg' => 'Ошибка при сохранении услуги (' . __LINE__ . ')'));
				}
				else if ( !empty($resp_usl[0]['Error_Msg']) ) {
					return array(array('Error_Msg' => $resp_usl[0]['Error_Msg'] . ' (' . __LINE__ . ')'));
				}
			} else if ( !empty($EvnUslugaDispOrp_id) ) {
				$query = "
					declare
						@ErrCode int,
						@ErrMessage varchar(4000);

					exec p_EvnUslugaDispOrp_del
						@EvnUslugaDispOrp_id = :EvnUslugaDispOrp_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;

					select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				$result = $this->db->query($query, array(
					'EvnUslugaDispOrp_id' => $EvnUslugaDispOrp_id,
					'pmUser_id' => $data['pmUser_id']
				));

				if ( !is_object($result) ) {
					return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление услуги) (' . __LINE__ . ')'));
				}

				$resp_usl = $result->result('array');

				if ( !is_array($resp_usl) || count($resp_usl) == 0 ) {
					return array(array('Error_Msg' => 'Ошибка при удалении услуги (' . __LINE__ . ')'));
				}
				else if ( !empty($resp_usl[0]['Error_Msg']) ) {
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
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_EvnVizitDispOrp_del "
			. "@EvnVizitDispOrp_id = ?, "
			. "@pmUser_id = ?, "
			. "@Error_Code = @ErrCode output, "
			. "@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		return $this->queryResult($query, array($data['EvnVizitDispOrp_id'], $data['pmUser_id']));
	}

	/**
	 * Сохранение осмотров
	 */
	function saveEvnVizitDispOrpSec($data) {

		if (empty($data['EvnVizitDispOrp_id'])) {
			$procedure = 'p_EvnVizitDispOrp_ins';
		} else {
			$procedure = 'p_EvnVizitDispOrp_upd';
		}

		// 1. ищем DopDispInfoConsent_id
		$data['DopDispInfoConsent_id'] = null;
		if (!empty($data['EvnVizitDispOrp_id'])) {
			$query = "
				select top 1
					DopDispInfoConsent_id
				from
					v_EvnVizitDispOrp (nolock)
				where
					EvnVizitDispOrp_id = :EvnVizitDispOrp_id
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
			declare
				@DopDispInfoConsent_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @DopDispInfoConsent_id = :DopDispInfoConsent_id;
			exec {$ddicproc}
				@DopDispInfoConsent_id = @DopDispInfoConsent_id output,
				@EvnPLDisp_id = :EvnPLDisp_id, 
				@SurveyTypeLink_id = NULL,
				@DopDispInfoConsent_IsAgree = 2, 
				@DopDispInfoConsent_IsEarlier = 1, 
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @DopDispInfoConsent_id as DopDispInfoConsent_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
			$setDT .= ' '.$data['EvnVizitDispOrp_setTime'];
		}
		$disDT = null;
		if (!empty($data['EvnVizitDispOrp_disDate'])) {
			$disDT = $data['EvnVizitDispOrp_disDate'];

			if (!empty($data['EvnVizitDispOrp_disTime'])) {
				$disDT .= ' '.$data['EvnVizitDispOrp_disTime'];
			}
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :EvnVizitDispOrp_id;

			exec {$procedure}
				@EvnVizitDispOrp_id = @Res output,
				@EvnVizitDispOrp_pid = :EvnVizitDispOrp_pid,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@EvnVizitDispOrp_setDT = :EvnVizitDispOrp_setDT,
				@EvnVizitDispOrp_disDT = :EvnVizitDispOrp_disDT,
				@EvnVizitDispOrp_didDT = null,
				@LpuSection_id = :LpuSection_id,
				@MedSpecOms_id = :MedSpecOms_id,
				@LpuSectionProfile_id = :LpuSectionProfile_id,
				@MedPersonal_id = :MedPersonal_id,
				@MedStaffFact_id = :MedStaffFact_id,
				@MedPersonal_sid = null,
				@PayType_id = null,
				@UslugaComplex_id = :UslugaComplex_id,
				@OrpDispSpec_id = null,
				@DopDispInfoConsent_id = :DopDispInfoConsent_id,
				@Diag_id = :Diag_id,
				@DopDispDiagType_id = :DopDispDiagType_id,
				@DopDispAlien_id = :DopDispAlien_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as EvnVizitDispOrp_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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

		if (!empty($response[0]['EvnVizitDispOrp_id'])) {
			// сохраняем сопутствующие диагнозы
			if (!empty($data['EvnDiagDopDispGridData'])) {
				$data['EvnDiagDopDispGridData'] = json_decode($data['EvnDiagDopDispGridData'], true);
			} else {
				$data['EvnDiagDopDispGridData'] = array();
			}
			foreach($data['EvnDiagDopDispGridData'] as $EvnDiagDopDisp) {
				if ($EvnDiagDopDisp['Record_Status'] == 3) {// удаление
					$query = "
						declare
							@ErrCode int,
							@ErrMessage varchar(4000);
						exec p_EvnDiagDopDisp_del
							@EvnDiagDopDisp_id = :EvnDiagDopDisp_id,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;
						select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
							count(*) as cnt
						from
							v_EvnDiagDopDisp (nolock)
						where
							EvnDiagDopDisp_pid = ?
							and Diag_id = ?
							and DiagSetClass_id = 3
							and ( EvnDiagDopDisp_id <> isnull(?, 0) )
					";
					$result_eddd = $this->db->query(
						$query,
						array(
							$response[0]['EvnVizitDispOrp_id'],
							$EvnDiagDopDisp['Diag_id'],
							$EvnDiagDopDisp['Record_Status'] == 0 ? null : $EvnDiagDopDisp['EvnDiagDopDisp_id']
						)
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
						declare
							@EvnDiagDopDisp_id bigint,
							@ErrCode int,
							@curdate datetime = dbo.tzGetDate(),
							@ErrMessage varchar(4000);
						set @EvnDiagDopDisp_id = :EvnDiagDopDisp_id;
						exec {$proc_evdd}
							@EvnDiagDopDisp_id = @EvnDiagDopDisp_id output,
							@EvnDiagDopDisp_setDT = @curdate,
							@EvnDiagDopDisp_pid = :EvnDiagDopDisp_pid,
							@Diag_id = :Diag_id,
							@DiagSetClass_id = :DiagSetClass_id,
							@DeseaseDispType_id = :DeseaseDispType_id,
							@Lpu_id = :Lpu_id,
							@Server_id = :Server_id,
							@PersonEvn_id = :PersonEvn_id,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;
						select @EvnDiagDopDisp_id as EvnDiagDopDisp_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					";
					$result_eddd = $this->db->query($query, array(
						'EvnDiagDopDisp_id' => $EvnDiagDopDisp['Record_Status'] == 0 ? null : $EvnDiagDopDisp['EvnDiagDopDisp_id'],
						'EvnDiagDopDisp_pid' => $response[0]['EvnVizitDispOrp_id'],
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
					declare
						@ErrCode int,
						@EvnUslugaDispOrp_id bigint,
						@PayType_id bigint,
						@ErrMessage varchar(4000);
					set @EvnUslugaDispOrp_id = :EvnUslugaDispOrp_id;
					set @PayType_id = (select top 1 PayType_id from v_PayType with (nolock) where PayType_SysNick = 'dopdisp');
					exec {$proc}
						@EvnUslugaDispOrp_id = @EvnUslugaDispOrp_id output,
						@EvnUslugaDispOrp_pid = :EvnUslugaDispOrp_pid,
						@UslugaComplex_id = :UslugaComplex_id,
						@Lpu_id = :Lpu_id,
						@Server_id = :Server_id,
						@PayType_id = @PayType_id,
						@UslugaPlace_id = 1,
						@PersonEvn_id = :PersonEvn_id,
						@EvnPrescrTimetable_id = null,
						@EvnPrescr_id = null,
						@LpuSection_uid = :LpuSection_id,
						@MedPersonal_id = :MedPersonal_id,
						@MedStaffFact_id = :MedStaffFact_id,
						@Diag_id = :Diag_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @EvnUslugaDispOrp_id as EvnUslugaDispOrp_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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

				if ( !is_object($result) ) {
					return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение услуги) (' . __LINE__ . ')'));
				}

				$resp_usl = $result->result('array');

				if ( !is_array($resp_usl) || count($resp_usl) == 0 ) {
					return array(array('Error_Msg' => 'Ошибка при сохранении услуги (' . __LINE__ . ')'));
				}
				else if ( !empty($resp_usl[0]['Error_Msg']) ) {
					return array(array('Error_Msg' => $resp_usl[0]['Error_Msg'] . ' (' . __LINE__ . ')'));
				}
			} else if ( !empty($EvnUslugaDispOrp_id) ) {
				$query = "
					declare
						@ErrCode int,
						@ErrMessage varchar(4000);

					exec p_EvnUslugaDispOrp_del
						@EvnUslugaDispOrp_id = :EvnUslugaDispOrp_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;

					select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				$result = $this->db->query($query, array(
					'EvnUslugaDispOrp_id' => $EvnUslugaDispOrp_id,
					'pmUser_id' => $data['pmUser_id']
				));

				if ( !is_object($result) ) {
					return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление услуги) (' . __LINE__ . ')'));
				}

				$resp_usl = $result->result('array');

				if ( !is_array($resp_usl) || count($resp_usl) == 0 ) {
					return array(array('Error_Msg' => 'Ошибка при удалении услуги (' . __LINE__ . ')'));
				}
				else if ( !empty($resp_usl[0]['Error_Msg']) ) {
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
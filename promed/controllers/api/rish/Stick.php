<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Stick - контроллер API для работы с ЛВН
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			API
 * @access			public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			09.11.2016
 */

require(APPPATH.'libraries/SwREST_Controller.php');

class Stick extends SwREST_Controller
{
	protected $inputRules = array(
		'loadEvnStickList' => array(
			array('field' => 'Evn_pid', 'label' => 'Идентификатор случая лечения', 'rules' => 'required', 'type' => 'id'),
		),
		'getEvnStick' => array(
			array('field' => 'EvnStickBase_id', 'label' => 'Идентификатор ЛВН', 'rules' => '', 'type' => 'id'),
			array('field' => 'Evn_pid', 'label' => 'Идентификатор случая лечения', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnStick_Num', 'label' => 'Номер ЛВН', 'rules' => '', 'type' => 'int'),
			array('field' => 'EvnStick_setDate', 'label' => 'Дата выдачи ЛВН', 'rules' => '', 'type' => 'date'),
		),
		'createEvnStick' => array(
			array('field' => 'Evn_pid', 'label' => 'Идентификатор случая', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnStick_isOriginal', 'label' => 'Оригинал', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'StickWorkType_id', 'label' => 'Тип занятости', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'StickOrder_id', 'label' => 'Порядок выдачи', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnStick_prid', 'label' => 'Идентификатор предыдущего ЛВН', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnStick_Num', 'label' => 'Номер ЛВН', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnStick_setDate', 'label' => 'Дата выдачи ЛВН', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'StickCause_id', 'label' => 'Причина нетрудоспособности', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'StickCauseDopType_id', 'label' => 'Дополнительный код нетрудоспособности', 'rules' => '', 'type' => 'id'),
			array('field' => 'StickCause_did', 'label' => 'Идентификатор изменения нетрудоспособности', 'rules' => '', 'type' => 'id'),
			array('field' => 'StickIrregularity_id', 'label' => 'Идентификатор нарушения режима', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnStick_stacBegDate', 'label' => 'Начало лечения в стационаре', 'rules' => '', 'type' => 'date'),
			array('field' => 'EvnStick_stacEndDate', 'label' => 'Окончание лечения в стационаре', 'rules' => '', 'type' => 'date'),
			array('field' => 'EvnStick_mseDate', 'label' => 'Дата направления в МСЭ', 'rules' => '', 'type' => 'date'),
			array('field' => 'EvnStick_mseRegDate', 'label' => 'Дата регистрации документов в МСЭ', 'rules' => '', 'type' => 'date'),
			array('field' => 'EvnStick_mseExamDate', 'label' => 'Дата освидетельствования документов в МСЭ', 'rules' => '', 'type' => 'date'),
			array('field' => 'InvalidGroupType_id', 'label' => 'Группа инвалидности', 'rules' => '', 'type' => 'id'),
			array('field' => 'StickLeaveType_id', 'label' => 'Идентификатор исхода', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnStick_disDate', 'label' => 'Дата исхода', 'rules' => '', 'type' => 'date'),
			array('field' => 'MedStaffFact_id', 'label' => 'Место работы врача', 'rules' => '', 'type' => 'id'),
			array('field' => 'Lpu_oid', 'label' => 'Напрвлен в другое МО', 'rules' => '', 'type' => 'id'),
		),
		'updateEvnStick' => array(
			array('field' => 'EvnStickBase_id', 'label' => 'Идентификатор ЛВН', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Evn_pid', 'label' => 'Идентификатор случая', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnStick_isOriginal', 'label' => 'Оригинал', 'rules' => '', 'type' => 'id'),
			array('field' => 'StickWorkType_id', 'label' => 'Тип занятости', 'rules' => '', 'type' => 'id'),
			array('field' => 'StickOrder_id', 'label' => 'Порядок выдачи', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnStick_prid', 'label' => 'Идентификатор предыдущего ЛВН', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnStick_Num', 'label' => 'Номер ЛВН', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnStick_setDate', 'label' => 'Дата выдачи ЛВН', 'rules' => '', 'type' => 'date'),
			array('field' => 'StickCause_id', 'label' => 'Причина нетрудоспособности', 'rules' => '', 'type' => 'id'),
			array('field' => 'StickCauseDopType_id', 'label' => 'Дополнительный код нетрудоспособности', 'rules' => '', 'type' => 'id'),
			array('field' => 'StickCause_did', 'label' => 'Идентификатор изменения нетрудоспособности', 'rules' => '', 'type' => 'id'),
			array('field' => 'StickIrregularity_id', 'label' => 'Идентификатор нарушения режима', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnStick_stacBegDate', 'label' => 'Начало лечения в стационаре', 'rules' => '', 'type' => 'date'),
			array('field' => 'EvnStick_stacEndDate', 'label' => 'Окончание лечения в стационаре', 'rules' => '', 'type' => 'date'),
			array('field' => 'EvnStick_mseDate', 'label' => 'Дата направления в МСЭ', 'rules' => '', 'type' => 'date'),
			array('field' => 'EvnStick_mseRegDate', 'label' => 'Дата регистрации документов в МСЭ', 'rules' => '', 'type' => 'date'),
			array('field' => 'EvnStick_mseExamDate', 'label' => 'Дата освидетельствования документов в МСЭ', 'rules' => '', 'type' => 'date'),
			array('field' => 'InvalidGroupType_id', 'label' => 'Группа инвалидности', 'rules' => '', 'type' => 'id'),
			array('field' => 'StickLeaveType_id', 'label' => 'Идентификатор исхода', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnStick_disDate', 'label' => 'Дата исхода', 'rules' => '', 'type' => 'date'),
			array('field' => 'MedStaffFact_id', 'label' => 'Место работы врача', 'rules' => '', 'type' => 'id'),
			array('field' => 'Lpu_oid', 'label' => 'Напрвлен в другое МО', 'rules' => '', 'type' => 'id'),
		),
		'getEvnStickStudent' => array(
			array('field' => 'EvnStickBase_id', 'label' => 'Идентификатор ЛВН', 'rules' => '', 'type' => 'id'),
			array('field' => 'Evn_pid', 'label' => 'Идентификатор случая лечения', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnStick_Num', 'label' => 'Номер ЛВН', 'rules' => '', 'type' => 'int'),
			array('field' => 'EvnStick_setDate', 'label' => 'Дата выдачи ЛВН', 'rules' => '', 'type' => 'date'),
		),
		'getEvnStickSetdate' => array(
			array('field' => 'EvnStick_mid', 'label' => 'Идентификатор учетного документа', 'rules' => 'required', 'type' => 'id')
		),
		'createEvnStickStudent' => array(
			array('field' => 'Evn_pid', 'label' => 'Идентификатор случая', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnStick_Num', 'label' => 'Номер справки', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnStick_setDate', 'label' => 'Дата выдачи', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'Org_id', 'label' => 'Организация, для которой оформлялась справка', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'StickRecipient_id', 'label' => 'Получатель справки', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'StickCause_id', 'label' => 'Причина нетрудоспособности', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnStick_isContact', 'label' => 'Контакт с инфекционными больными', 'rules' => '', 'type' => 'int'),
			array('field' => 'EvnStick_ContactDescr', 'label' => 'Описание контакта', 'rules' => '', 'type' => 'string'),
			array('field' => 'MedStaffFact_id', 'label' => 'Описание контакта', 'rules' => 'required', 'type' => 'id'),
		),
		'updateEvnStickStudent' => array(
			array('field' => 'EvnStickBase_id', 'label' => 'Идентификатор документа нетрудоспособности', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Evn_pid', 'label' => 'Идентификатор случая', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnStick_Num', 'label' => 'Номер справки', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnStick_setDate', 'label' => 'Дата выдачи', 'rules' => '', 'type' => 'date'),
			array('field' => 'Org_id', 'label' => 'Организация, для которой оформлялась справка', 'rules' => '', 'type' => 'id'),
			array('field' => 'StickRecipient_id', 'label' => 'Получатель справки', 'rules' => '', 'type' => 'id'),
			array('field' => 'StickCause_id', 'label' => 'Причина нетрудоспособности', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnStick_isContact', 'label' => 'Контакт с инфекционными больными', 'rules' => '', 'type' => 'int'),
			array('field' => 'EvnStick_ContactDescr', 'label' => 'Описание контакта', 'rules' => '', 'type' => 'string'),
			array('field' => 'MedStaffFact_id', 'label' => 'Описание контакта', 'rules' => '', 'type' => 'id'),
		),
		'loadEvnStickWorkReleaseList' => array(
			array('field' => 'EvnStickBase_id', 'label' => 'Идентификатор документа нетрудоспособности', 'rules' => 'required', 'type' => 'id'),
		),
		'getEvnStickWorkRelease' => array(
			array('field' => 'EvnStickWorkRelease_id', 'label' => 'Идентификатор освобождения', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnStickBase_id', 'label' => 'Идентификатор ЛВН', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnStickWorkRelease_begDate', 'label' => 'Дата начала освобождения', 'rules' => '', 'type' => 'date'),
			array('field' => 'EvnStickWorkRelease_endDate', 'label' => 'Дата окончания освобождения', 'rules' => '', 'type' => 'date'),
		),
		'createEvnStickWorkRelease' => array(
			array('field' => 'EvnStickBase_id', 'label' => 'Идентификатор документа нетрудоспособности', 'rules' => 'required', 'type' => 'id', 'checklpu' => true),
			array('field' => 'EvnStickWorkRelease_isDraft', 'label' => 'Флаг черновика за другую МО', 'rules' => 'required', 'type' => 'api_flag'),
			array('field' => 'EvnStickWorkRelease_begDate', 'label' => 'Начало периода освобождения', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'EvnStickWorkRelease_endDate', 'label' => 'Окончание периода освобождения', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'LpuSection_id', 'label' => 'Отделение врача 1', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MedStaffFact_id', 'label' => 'Место работы врача 1', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MedStaffFact2_id', 'label' => 'Место работы врача 2', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedStaffFact3_id', 'label' => 'Место работы врача 3', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnStickWorkRelease_isPredVK', 'label' => 'Флаг председателя ВК', 'rules' => 'required', 'type' => 'api_flag'),
		),
		'updateEvnStickWorkRelease' => array(
			array('field' => 'EvnStickWorkRelease_id', 'label' => 'Идентификатор освобождения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnStickWorkRelease_isDraft', 'label' => 'Флаг черновика за другую МО', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'EvnStickWorkRelease_begDate', 'label' => 'Начало периода освобождения', 'rules' => '', 'type' => 'date'),
			array('field' => 'EvnStickWorkRelease_endDate', 'label' => 'Окончание периода освобождения', 'rules' => '', 'type' => 'date'),
			array('field' => 'LpuSection_id', 'label' => 'Отделение врача 1', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedStaffFact_id', 'label' => 'Место работы врача 1', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedStaffFact2_id', 'label' => 'Место работы врача 2', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedStaffFact3_id', 'label' => 'Место работы врача 3', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnStickWorkRelease_isPredVK', 'label' => 'Флаг председателя ВК', 'rules' => '', 'type' => 'api_flag'),
		),
		'getEvnLinkList' => array(
			array('field' => 'Evn_id', 'label' => 'Идентификатор случая лечения', 'rules' => 'required', 'type' => 'id'),
		),
		'createEvnLink' => array(
			array('field' => 'Evn_id', 'label' => 'Идентификатор случая лечения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Evn_lid', 'label' => 'Идентификатор ЛВН', 'rules' => 'required', 'type' => 'id'),
		),
		'deleteEvnLink' => array(
			array('field' => 'Evn_id', 'label' => 'Идентификатор случая лечения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Evn_lid', 'label' => 'Идентификатор ЛВН', 'rules' => 'required', 'type' => 'id'),
		),
		'selectEvnStickType' => array(// как в controllers/Stick
			array(
				'field' => 'evnStickType',
				'label' => 'Тип ЛВН',
				'rules' => 'required',
				'type' => 'int'
			)
		),
		'msaveEvnStick' => array(// как в controllers/Stick + evnStickType
			array(
				'field' => 'EvnStick_BirthDate',
				'label' => 'Предполагаемая дата родов',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnStick_IsOriginal',
				'label' => 'Оригинал',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStick_disDate',
				'label' => 'Дата закрытия ЛВН',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnStick_id',
				'label' => 'Идентификатор ЛВН',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'StickFSSData_id',
				'label' => 'Идентификатор запроса в ФСС',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStickBase_IsFSS',
				'label' => 'ЛВН из ФСС',
				'rules' => '',
				'type' => 'id'// null, "", 0, 1 или 2 (да)
			),
			array(
				'field' => 'EvnStick_irrDate',
				'label' => 'Дата нарушения режима',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnStick_StickDT',
				'label' => 'Дата изменения причины нетрудоспособности',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnStick_IsDisability',
				'label' => 'Установлена группа инвалидности',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'InvalidGroupType_id',
				'label' => 'Установлена/изменена группа инвалидности',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStick_IsRegPregnancy',
				'label' => 'Поставлена на учет в ранние сроки беременности (до 12 недель)',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStick_mid',
				'label' => 'Идентификатор учетного документа',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStick_mseDate',
				'label' => 'Дата направления в бюро МСЭ',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnStick_mseExamDate',
				'label' => 'Дата освидетельствования в бюро МСЭ',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnStick_mseRegDate',
				'label' => 'Дата регистрации документов в бюро МСЭ',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnStick_Num',
				'label' => 'Номер ЛВН',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'RegistryESStorage_id',
				'label' => 'Номер ЛВН в хранилище',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStick_pid',
				'label' => 'Идентификатор родительского события',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStickDop_pid',
				'label' => 'Идентификатор основного ЛВН',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnStick_prid',
				'label' => 'Идентификатор предыдущего ЛВН',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStick_Ser',
				'label' => 'Серия ЛВН',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EvnStick_setDate',
				'label' => 'Дата выдачи',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnStick_sstBegDate',
				'label' => 'Дата начала СКЛ',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnStick_sstEndDate',
				'label' => 'Дата окончания СКЛ',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnStick_sstNum',
				'label' => 'Номер путевки',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EvnStick_stacBegDate',
				'label' => 'Дата начала лечения в стационаре',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnStick_stacEndDate',
				'label' => 'Дата окончания лечения в стационаре',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'evnStickCarePersonData',
				'label' => 'Список пациентов, нуждающихся в уходе',
				'rules' => '',
				'type' => 'array'
			),
			array(
				'field' => 'EvnStickBase_consentDT',
				'label' => 'Дата выдачи',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'evnStickWorkReleaseData',
				'label' => 'Список освобождений от работы',
				'rules' => '',
				'type' => 'array'
			),
			array(
				'field' => 'link',
				'label' => 'Признак необходимости добавить связку ЛВН с учетным документом',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Lpu_oid',
				'label' => 'Направлен в другое ЛПУ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'Идентификатор места работы врача, закрывшего ЛВН',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Врач, закрывший ЛВН',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Org_did',
				'label' => 'Санаторий',
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
				'field' => 'EvnStick_OrgNick',
				'label' => 'Наименование организации для печати',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека, которому выдан ЛВН',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PersonEvn_id',
				'label' => 'Идентификатор состояния человека, которому выдан ЛВН',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Post_Name',
				'label' => 'Должность',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Server_id',
				'label' => 'Идентификатор сервера',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'StickCause_did',
				'label' => 'Изм. причина нетрудоспособности',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'StickCause_id',
				'label' => 'Причина нетрудоспособности',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'StickCauseDopType_id',
				'label' => 'Доп. причина нетрудоспособности',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'StickIrregularity_id',
				'label' => 'Нарушение режима',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'StickLeaveType_id',
				'label' => 'Исход ЛВН',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'StickOrder_id',
				'label' => 'Порядок выдачи',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'StickWorkType_id',
				'label' => 'Тип занятости',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStick_oid',
				'label' => 'Идентификатор оригинала',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStick_adoptDate',
				'label' => 'Дата усыновления/удочерения',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnStick_regBegDate',
				'label' => 'Дата начала перевода на другую работу',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnStick_regEndDate',
				'label' => 'Дата окончания перевода на другую работу',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'StickRegime_id',
				'label' => 'Идентификатор режима',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ignoreStickOrderCheck',
				'label' => 'Игнорирование проверки первичного ЛВН',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'Signatures_id',
				'label' => 'Подпись исхода',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Signatures_iid',
				'label' => 'Подпись режима',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'doUpdateJobInfo',
				'label' => 'Флаг согласия обновления данных место работы',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'evnStickType',
				'label' => 'Тип ЛВН',
				'rules' => 'required',
				'type' => 'int'
			)
		),
		'saveEvnStickDop' => array(// как в controllers/Stick + evnStickType
			array(
				'field' => 'EvnStick_Num',
				'label' => 'Номер ЛВН',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'RegistryESStorage_id',
				'label' => 'Номер ЛВН в хранилище',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStick_Ser',
				'label' => 'Серия ЛВН',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EvnStick_setDate',
				'label' => 'Дата выдачи',
				'rules' => 'trim|required',
				'type' => 'date'
			),
			array(
				'field' => 'EvnStick_id',
				'label' => 'Идентификатор ЛВН',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'StickFSSData_id',
				'label' => 'Идентификатор запроса в ФСС',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStickBase_IsFSS',
				'label' => 'ЛВН из ФСС',
				'rules' => '',
				'type' => 'id'// null, "", 0, 1 или 2 (да)
			),
			array(
				'field' => 'EvnStick_prid',
				'label' => 'Идентификатор предыдущего ЛВН',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStick_mid',
				'label' => 'Идентификатор учетного документа',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStickDop_pid',
				'label' => 'Идентификатор основного ЛВН',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Org_id',
				'label' => 'Организация',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStick_OrgNick',
				'label' => 'Наименование организации для печати',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PersonEvn_id',
				'label' => 'Идентификатор состояния человека',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Server_id',
				'label' => 'Идентификатор сервера',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'Post_Name',
				'label' => 'Должность',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'StickOrder_id',
				'label' => 'Порядок выдачи',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'StickWorkType_id',
				'label' => 'Тип занятости',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStick_oid',
				'label' => 'Идентификатор оригинала',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStick_IsOriginal',
				'label' => 'Оригинал',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStickBase_consentDT',
				'label' => 'Дата выдачи',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'evnStickWorkReleaseData',
				'label' => 'Список освобождений от работы',
				'rules' => '',
				'type' => 'array'
			),
			array(
				'field' => 'StickCause_id',
				'label' => 'Причина нетрудоспособности',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStick_disDate',
				'label' => 'Дата закрытия ЛВН',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'Signatures_id',
				'label' => 'Подпись исхода',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Signatures_iid',
				'label' => 'Подпись режима',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ignoreCheckEvnStickOrg',
				'label' => 'Игнорирование проверки места работы при выписке ЛВН по совместительству',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'link',
				'label' => 'Признак необходимости добавить связку ЛВН с учетным документом',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'evnStickType',
				'label' => 'Тип ЛВН',
				'rules' => 'required',
				'type' => 'int'
			)
		),
		'mDeleteEvnStick' => array(
			array(
				'field' => 'EvnStick_id',
				'label' => 'Идентификатор ЛВН',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStick_mid',
				'label' => 'Идентификатор учетного документа',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'ignoreStickFromFSS',
				'label' => 'Игнорировать проверку',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'ignoreStickHasProlongation',
				'label' => 'Игнорировать проверку',
				'rules' => '',
				'type' => 'api_flag'
			),
			array(
				'field' => 'ignoreStickHasPrevious',
				'label' => 'Игнорировать проверку',
				'rules' => '',
				'type' => 'api_flag'
			),
			array(
				'field' => 'StickCauseDel_id',
				'label' => 'Причина прекращения действия ЭЛН',
				'rules' => '',
				'type' => 'id'
			)
		),
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('Stick_model', 'dbmodel');
	}

	/**
	 * Получение данных ЛВН
	 *
	 * @desсription
	 * {
	 * 		"output_params": {
	 * 			"error_code": "Код ошибки",
	 * 			"EvnStickBase_id": "Базовый больничный лист, идентификатор",
	 *			"Evn_id": "Идентификатор случая-родителя",
				"EvnStick_isOriginal": "Признак оригинальности",
				"StickWorkType_id": "Тип работы для которого выписывается б/л, идентификатор",
				"StickOrder_id": "порядок выдачи б/л, идентификатор",
				"EvnStick_prid": "Ссылка на предыдущий ЛВН",
				"EvnStick_Num": "Номер ЛВН",
				"EvnStick_setDate": "Дата выдачи ЛВН",
				"StickCause_id": "причина нетрудоспособности, идентификатор",
				"StickCauseDopType_id": "Дополнительный код причины нетрудоспособности, идентификатор",
				"StickCause_did": null,
				"StickIrregularity_id": "нарушение режима, идентификатор",
				"EvnStick_stacBegDate": "Дата начала лечения в стационаре",
				"EvnStick_stacEndDate": "Дата конца лечения в стационаре",
				"EvnStick_mseDate": "Дата направления в бюро МСЭ",
				"EvnStick_mseRegDate": "дата регистрации документов в бюро МСЭ",
				"EvnStick_mseExamDate": "Дата освидетельствования в бюро МСЭ",
				"InvalidGroupType_id": "Инвалидность, идентификатор",
				"StickLeaveType_id": "исход ЛВН, идентификатор",
				"EvnStick_disDate": "Дата окончания ЛВН",
				"MedStaffFact_id": "Место работы, идентификатор",
				"Lpu_oid": "ЛПУ, куда направили"
	 * 		},
	 * 		"example": {
	 * 			"error_code": 0,
				"data": [
				{
					"EvnStickBase_id": "730023881155261",
					"Evn_id": "730023881155257",
					"EvnStick_isOriginal": "1",
					"StickWorkType_id": "1",
					"StickOrder_id": "1",
					"EvnStick_prid": null,
					"EvnStick_Num": null,
					"EvnStick_setDate": "2018-11-04",
					"StickCause_id": "1",
					"StickCauseDopType_id": null,
					"StickCause_did": null,
					"StickIrregularity_id": null,
					"EvnStick_stacBegDate": null,
					"EvnStick_stacEndDate": null,
					"EvnStick_mseDate": null,
					"EvnStick_mseRegDate": null,
					"EvnStick_mseExamDate": null,
					"InvalidGroupType_id": null,
					"StickLeaveType_id": null,
					"EvnStick_disDate": null,
					"MedStaffFact_id": null,
					"Lpu_oid": null
					}
				]
	 * 		}
	 * }
	 */
	function index_get() {

		$data = $this->ProcessInputData('getEvnStick', null, true);
		$resp = $this->dbmodel->getEvnStickForAPI($data);

		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		if (count($resp) > 0 && !empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp[0]['Error_Msg']
			));
		}

		$this->response(array('error_code' => 0,'data' => $resp));
	}

	/**
	 * Добавление данных ЛВН
	 *
	 * @desсription
	 * {
	 * 		"output_params": {
	 * 			"error_code": "Код ошибки",
	 * 			"EvnStickBase_id": "Идентификатор ЛВН",
	 *			"Evn_id": "Идентификатор случая-родителя"
	 * 		},
	 * 		"example": {
	 * 			"error_code": 0,
	 * 			"data": {"EvnStickBase_id": 123123123123, "Evn_id": 112312312312}
	 * 		}
	 * }
	 */
	function index_post() {
		$data = $this->ProcessInputData('createEvnStick', null, true);

		$info = $this->dbmodel->getEvnStickInfoForAPI($data);
		if (!is_array($info)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$params = array(
			'EvnStick_id' => null,
			'EvnStick_oid' => null,
			'EvnStick_pid' => $data['Evn_pid'],
			'EvnStick_mid' => $data['Evn_pid'],
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $info['Server_id'],
			'PersonEvn_id' => $info['PersonEvn_id'],
			'StickCause_id' => $data['StickCause_id'],
			'StickCause_did' => $data['StickCause_did'],
			'StickCauseDopType_id' => $data['StickCauseDopType_id'],
			'EvnStick_Ser' => null,
			'EvnStick_Num' => $data['EvnStick_Num'],
			'EvnStick_setDate' => $data['EvnStick_setDate'],
			'EvnStick_disDate' => $data['EvnStick_disDate'],
			'StickOrder_id' => $data['StickOrder_id'],
			'EvnStick_prid' => $data['EvnStick_prid'],
			'Org_id' => null,
			'EvnStick_OrgNick' => null,
			'Post_Name' => null,
			'StickWorkType_id' => $data['StickWorkType_id'],
			'EvnStick_BirthDate' => null,
			'EvnStick_sstBegDate' => null,
			'EvnStick_sstEndDate' => null,
			'EvnStick_sstNum' => null,
			'Org_did' => null,
			'EvnStick_mseDate' => $data['EvnStick_mseDate'],
			'EvnStick_mseRegDate' => $data['EvnStick_mseRegDate'],
			'EvnStick_mseExamDate' => $data['EvnStick_mseExamDate'],
			// 'MedPersonal_mseid' => $data['MedPersonal_mseid'],
			'StickIrregularity_id' => $data['StickIrregularity_id'],
			'EvnStick_irrDate' => null,
			'StickLeaveType_id' => $data['StickLeaveType_id'],
			'EvnStick_stacBegDate' => $data['EvnStick_stacBegDate'],
			'EvnStick_stacEndDate' => $data['EvnStick_stacEndDate'],
			'EvnStick_IsDisability' => null,
			'InvalidGroupType_id' => $data['InvalidGroupType_id'],
			'EvnStick_StickDT' => null,
			'EvnStick_IsRegPregnancy' => null,
			'EvnStick_IsOriginal' => $data['EvnStick_isOriginal'],
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'MedPersonal_id' => $info['MedPersonal_id'],
			'Lpu_oid' => $data['Lpu_oid'],
			'EvnStick_adoptDate' => null,
			'EvnStick_regBegDate' => null,
			'EvnStick_regEndDate' => null,
			'StickRegime_id' => null,
			'Signatures_id' => null,
			'Signatures_iid' => null,
			'ignoreStickOrderCheck' => true,
			'pmUser_id' => $data['pmUser_id'],
			'session' => $data['session'],
		);

		$resp = $this->dbmodel->saveEvnStick($params);
		if (!is_array($resp) || !isset($resp[0])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp[0]['Error_Msg']
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				array(
					'EvnStickBase_id' => $resp[0]['EvnStick_id'],
					'Evn_id' => $data['Evn_pid'],
				)
			)
		));
	}
	
	/**
	 * Изменение данных ЛВН
	 *
	 * @desсription
	 * {
	 * 		"output_params": {
	 * 			"error_code": "Код ошибки"
	 * 		},
	 * 		"example": {
	 * 			"error_code": 0
	 * 		}
	 * }
	 */
	function index_put() {
		$data = $this->ProcessInputData('updateEvnStick', null, true);

		$resp = $this->dbmodel->getEvnStickForAPI(array(
			'EvnStickBase_id' => $data['EvnStickBase_id'],
			'Lpu_id' => $data['Lpu_id']
		));
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (count($resp) == 0) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Не найден ЛВН'
			));
		}

		foreach($data as $key => $value) {
			if (!empty($resp[0][$key]) && empty($value)) {
				$data[$key] = $resp[0][$key];
			}
		}
		if (empty($data['Evn_pid'])) {
			$data['Evn_pid'] =$resp[0]['Evn_id'];
		}

		$info = $this->dbmodel->getEvnStickInfoForAPI($data);
		if (!is_array($info)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$params = array(
			'EvnStick_id' => $data['EvnStickBase_id'],
			'EvnStick_oid' => null,
			'EvnStick_pid' => $data['Evn_pid'],
			'EvnStick_mid' => $data['Evn_pid'],
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $info['Server_id'],
			'PersonEvn_id' => $info['PersonEvn_id'],
			'StickCause_id' => $data['StickCause_id'],
			'StickCause_did' => $data['StickCause_did'],
			'StickCauseDopType_id' => $data['StickCauseDopType_id'],
			'EvnStick_Ser' => null,
			'EvnStick_Num' => $data['EvnStick_Num'],
			'EvnStick_setDate' => $data['EvnStick_setDate'],
			'EvnStick_disDate' => $data['EvnStick_disDate'],
			'StickOrder_id' => $data['StickOrder_id'],
			'EvnStick_prid' => $data['EvnStick_prid'],
			'Org_id' => null,
			'EvnStick_OrgNick' => null,
			'Post_Name' => null,
			'StickWorkType_id' => $data['StickWorkType_id'],
			'EvnStick_BirthDate' => null,
			'EvnStick_sstBegDate' => null,
			'EvnStick_sstEndDate' => null,
			'EvnStick_sstNum' => null,
			'Org_did' => null,
			'EvnStick_mseDate' => $data['EvnStick_mseDate'],
			'EvnStick_mseRegDate' => $data['EvnStick_mseRegDate'],
			'EvnStick_mseExamDate' => $data['EvnStick_mseExamDate'],
			// 'MedPersonal_mseid' => $data['MedPersonal_mseid'],
			'StickIrregularity_id' => $data['StickIrregularity_id'],
			'EvnStick_irrDate' => null,
			'StickLeaveType_id' => $data['StickLeaveType_id'],
			'EvnStick_stacBegDate' => $data['EvnStick_stacBegDate'],
			'EvnStick_stacEndDate' => $data['EvnStick_stacEndDate'],
			'EvnStick_IsDisability' => null,
			'InvalidGroupType_id' => $data['InvalidGroupType_id'],
			'EvnStick_StickDT' => null,
			'EvnStick_IsRegPregnancy' => null,
			'EvnStick_IsOriginal' => $data['EvnStick_isOriginal'],
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'MedPersonal_id' => $info['MedPersonal_id'],
			'Lpu_oid' => $data['Lpu_oid'],
			'EvnStick_adoptDate' => null,
			'EvnStick_regBegDate' => null,
			'EvnStick_regEndDate' => null,
			'StickRegime_id' => null,
			'Signatures_id' => null,
			'Signatures_iid' => null,
			'ignoreStickOrderCheck' => true,
			'pmUser_id' => $data['pmUser_id'],
			'session' => $data['session'],
			'EvnStick_nid' => (!empty($resp[0]['EvnStick_nid'])) ? $resp[0]['EvnStick_nid'] : null,
			'EvnStickBase_IsFSS' => (!empty($resp[0]['EvnStickBase_IsFSS'])) ? $resp[0]['EvnStickBase_IsFSS'] : null,
			'RegistryESStorage_id' => (!empty($resp[0]['RegistryESStorage_id'])) ? $resp[0]['RegistryESStorage_id'] : null,
			'EvnStickBase_consentDT' => (!empty($resp[0]['EvnStickBase_consentDT'])) ? $resp[0]['EvnStickBase_consentDT'] : null,
			'StickFSSData_id' => (!empty($resp[0]['StickFSSData_id'])) ? $resp[0]['StickFSSData_id'] : null,
		);

		$resp = $this->dbmodel->saveEvnStick($params);
		if (!is_array($resp) || !isset($resp[0])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp[0]['Error_Msg']
			));
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 * Удаление ЛВН связи со случаем
	 */
	function mDeleteEvnLink_post(){
		$this->EvnLink_delete();
	}

	/**
	 * Добавление данных ЛВН для мобильного
	 * перенос функционала из Stick/saveEvnStick()
	 * Используется: форма редактирования ЛВН
	 *
	 * @desсription
	 * {
		"input_params": {
			"EvnStick_mid": "Идентификатор учетного документа (required)",
			"EvnStick_pid": "Идентификатор родительского события (required)",
			"PersonEvn_id": "Идентификатор состояния человека, которому выдан ЛВН (required)",
			"Person_id": "Идентификатор человека, которому выдан ЛВН (required)",
			"Server_id": "Идентификатор сервера (required)",
			"evnStickType": "Тип ЛВН (required)",
			"EvnStickBase_IsFSS": "ЛВН из ФСС",
			"EvnStickBase_consentDT": "Дата выдачи",
			"EvnStickDop_pid": "Идентификатор основного ЛВН",
			"EvnStick_BirthDate": "Предполагаемая дата родов",
			"EvnStick_IsDisability": "Установлена группа инвалидности",
			"EvnStick_IsOriginal": "Оригинал",
			"EvnStick_IsRegPregnancy": "Поставлена на учет в ранние сроки беременности (до 12 недель)",
			"EvnStick_Num": "Номер ЛВН",
			"EvnStick_OrgNick": "Наименование организации для печати",
			"EvnStick_Ser": "Серия ЛВН",
			"EvnStick_StickDT": "Дата изменения причины нетрудоспособности",
			"EvnStick_adoptDate": "Дата усыновления/удочерения",
			"EvnStick_disDate": "Дата закрытия ЛВН",
			"EvnStick_id": "Идентификатор ЛВН",
			"EvnStick_irrDate": "Дата нарушения режима",
			"EvnStick_mseDate": "Дата направления в бюро МСЭ",
			"EvnStick_mseExamDate": "Дата освидетельствования в бюро МСЭ",
			"EvnStick_mseRegDate": "Дата регистрации документов в бюро МСЭ",
			"EvnStick_oid": "Идентификатор оригинала",
			"EvnStick_prid": "Идентификатор предыдущего ЛВН",
			"EvnStick_regBegDate": "Дата начала перевода на другую работу",
			"EvnStick_regEndDate": "Дата окончания перевода на другую работу",
			"EvnStick_setDate": "Дата выдачи",
			"EvnStick_sstBegDate": "Дата начала СКЛ",
			"EvnStick_sstEndDate": "Дата окончания СКЛ",
			"EvnStick_sstNum": "Номер путевки",
			"EvnStick_stacBegDate": "Дата начала лечения в стационаре",
			"EvnStick_stacEndDate": "Дата окончания лечения в стационаре",
			"InvalidGroupType_id": "Установлена/изменена группа инвалидности",
			"Lpu_oid": "Направлен в другое ЛПУ",
			"MedPersonal_id": "Врач, закрывший ЛВН",
			"MedStaffFact_id": "Идентификатор места работы врача, закрывшего ЛВН",
			"Org_did": "Санаторий",
			"Org_id": "Организация",
			"Post_Name": "Должность",
			"RegistryESStorage_id": "Номер ЛВН в хранилище",
			"Signatures_id": "Подпись исхода",
			"Signatures_iid": "Подпись режима",
			"StickCauseDopType_id": "Доп. причина нетрудоспособности",
			"StickCause_did": "Изм. причина нетрудоспособности",
			"StickCause_id": "Причина нетрудоспособности",
			"StickFSSData_id": "Идентификатор запроса в ФСС",
			"StickIrregularity_id": "Нарушение режима",
			"StickLeaveType_id": "Исход ЛВН",
			"StickOrder_id": "Порядок выдачи",
			"StickRegime_id": "Идентификатор режима",
			"StickWorkType_id": "Тип занятости",
			"doUpdateJobInfo": "Флаг согласия обновления данных место работы",
			"evnStickCarePersonData": "Список пациентов, нуждающихся в уходе",
			"evnStickWorkReleaseData": "Список освобождений от работы",
			"ignoreStickOrderCheck": "Игнорирование проверки первичного ЛВН",
			"link": "Признак необходимости добавить связку ЛВН с учетным документом"
		},
		"example":{
			{
	 			"error_code":0,
	 			"data":{
					"EvnStick_id":"730023881180485"
				}
			}
		}
	 * }
	 */
	function mSaveEvnStick_post(){
		//$this->index_post();// т.к. в текущей реализации API другие поля, другие проверки, что-то не реализовано, нужно опираться на веб вариант

		$data = $this->ProcessInputData('selectEvnStickType', null, true);
		if($data === false) $this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);// что-то пошло совсем не так

		$isKZ = ($data['session']['region']['nick']=='kz');
		// $IsUfa = ($_SESSION['region']['nick'] == 'ufa');

		switch($data['evnStickType']){
			case 1:// EvnStick основной
				$data = $this->ProcessInputData('msaveEvnStick', null, true);

				if(!empty($data['EvnStickBase_IsFSS']) && 2 === (int)$data['EvnStickBase_IsFSS']){// '' (нет), null (нет),0 (нет), 1 (нет), 2 (да)
					$data['EvnStickBase_IsFSS'] = 1;
				}else{
					$data['EvnStickBase_IsFSS'] = 0;
				}

				if(empty($data['EvnStickBase_IsFSS'])){
					if(empty($data['EvnStick_IsOriginal'])){
						$this->response(array(
							'error_code' => 4,
							'error_msg' => 'Поле "Оригинал" обязательно для заполнения'
						));
					}
					if(empty($data['StickCause_id'])){
						$this->response(array(
							'error_code' => 4,
							'error_msg' => 'Поле "Причина нетрудоспособности" обязательно для заполнения'
						));
					}
					if(empty($data['EvnStick_setDate'])){
						$this->response(array(
							'error_code' => 4,
							'error_msg' => 'Поле "Дата выдачи" обязательно для заполнения'
						));
					}
					if(empty($data['StickOrder_id'])){
						$this->response(array(
							'error_code' => 4,
							'error_msg' => 'Поле "Порядок выдачи" обязательно для заполнения'
						));
					}
					if(empty($data['StickWorkType_id'])){
						$this->response(array(
							'error_code' => 4,
							'error_msg' => 'Поле "Тип занятости" обязательно для заполнения'
						));
					}
				}
				break;

			case 2:// saveEvnStickDop дубликат
				$data = $this->ProcessInputData('saveEvnStickDop', null, true);
				if(!empty($data['EvnStickBase_IsFSS']) && 2 === (int)$data['EvnStickBase_IsFSS']){// '' (нет), null (нет),0 (нет), 1 (нет), 2 (да)
					$data['EvnStickBase_IsFSS'] = 1;
				}else{
					$data['EvnStickBase_IsFSS'] = 0;
				}
				break;

			default:
				$this->response(array(
					'error_code' => 4,
					'error_msg' => 'Неверный тип ЛВН'
				));
				break;
		}

		if(!empty($data['link']) && $data['link'] == 1){
			if(empty($data['EvnStick_id'])){
				$this->response(array(
					'error_code' => 4,
					'error_msg' => 'Не указан идентификатор ЛВН'
				));
			}
		}

		$carePersonList = array();
		$evnStickCarePerson = array();
		$evnStickWorkRelease = array();

		if($data['evnStickType'] == 1){// оригинал
			if(!empty($data['StickCause_id']) && $data['StickCause_id'] == $data['StickCause_did']){
				$this->response(array(
					'error_code' => 4,
					'error_msg' => 'Поля "Причина нетрудоспособности" и "Код изм. нетрудоспособности" не могут иметь одинаковые значения'
				));
			}

			if(!empty($data['StickLeaveType_id'])){
				if(empty($data['EvnStick_disDate'])){
					$this->response(array(
						'error_code' => 4,
						'error_msg' => 'Поле "Дата" обязательно для заполнения при заполненном исходе ЛВН'
					));
				}
			}
			else{
				$data['MedStaffFact_id'] = null;
				$data['MedPersonal_id'] = null;
				$data['EvnStick_disDate'] = null;
				$data['Lpu_oid'] = null;
			}

			// Обработка EvnStickCarePerson
			if(!empty($data['evnStickCarePersonData'])){
				// Обработка списка пациентов, нуждающихся в уходе
				$evnStickCarePersonData = $data['evnStickCarePersonData'];// $evnStickCarePersonData = json_decode(toUTF($data['evnStickCarePersonData']), true);
				if(is_array($evnStickCarePersonData)){
					// Обработка входных данных
					foreach($evnStickCarePersonData as $key => $array){
						if(!isset($array['RecordStatus_Code']) || !is_numeric($array['RecordStatus_Code']) || !in_array($array['RecordStatus_Code'], array(0, 1, 2, 3))){
							continue;
						}

						if(empty($array['EvnStickCarePerson_id']) || !is_numeric($array['EvnStickCarePerson_id'])){
							continue;
						}

						// Правильность заполнения полей проверяем только для добавляемых или редактируемых записей
						if($array['RecordStatus_Code'] != 3){
							if(empty($array['Person_id']) || !is_numeric($array['Person_id'])){
								$this->response(array(
									'error_code' => 4,
									'error_msg' => 'Не указан пациент, нуждающийся в уходе'
								));
							}
							elseif(in_array($array['Person_id'], $carePersonList)){
								$this->response(array(
									'error_code' => 4,
									'error_msg' => 'Пациент не может быть указан дважды в списке нуждающихся в уходе'
								));
							}

							if((empty($array['RelatedLinkType_id']) || !is_numeric($array['RelatedLinkType_id'])) && !$isKZ ){
								$this->response(array(
									'error_code' => 4,
									'error_msg' => 'Не указана родственная связь у пациента, нуждающегося в уходе'
								));
							}

							if($array['Person_id'] == $data['Person_id']){
								$this->response(array(
									'error_code' => 4,
									'error_msg' => 'Пациент, которому выдается ЛВН, не может быть указан в списке пациентов, нуждающихся в уходе'
								));
							}
							$carePersonList[] = $array['Person_id'];
						}

						$evnStickCarePerson[] = array(
							'EvnStickCarePerson_id' => $array['EvnStickCarePerson_id'],
							'Person_id' => $array['Person_id'],
							'pmUser_id' => $data['pmUser_id'],
							'RecordStatus_Code' => $array['RecordStatus_Code'],
							'RelatedLinkType_id' => $array['RelatedLinkType_id']
						);
					}
				}
			}// if(!empty($data['evnStickCarePersonData']))

			// Проверка записей о пациентах, нуждающихся в уходе
			$cnt = 0;

			// Проверка количества записей
			foreach($evnStickCarePerson as $key => $array){
				if($array['RecordStatus_Code'] == 3) continue;// Записи на удаление не учитываем
				$cnt++;
			}

			if($cnt > 2){
				$this->response(array(
					'error_code' => 4,
					'error_msg' => 'Количество записей о пациентах, нуждающихся в уходе, не может превышать двух'
				));
			}
		}// if($data['evnStickType'] == 1)// оригинал


		if(in_array($data['evnStickType'], array(1,2))){
			// Обработка EvnStickWorkRelease
			if(!empty($data['evnStickWorkReleaseData'])){// Обработка списка грида Освобождение от работы
				$evnStickWorkReleaseData = $data['evnStickWorkReleaseData'];// $evnStickWorkReleaseData = json_decode(toUTF($data['evnStickWorkReleaseData']), true);
				if(is_array($evnStickWorkReleaseData)){
					// Обработка входных данных
					foreach($evnStickWorkReleaseData as $key => $array){
						if(!isset($array['RecordStatus_Code']) || !is_numeric($array['RecordStatus_Code']) || !in_array($array['RecordStatus_Code'], array(0, 1, 2, 3))){
							continue;
						}

						if(empty($array['EvnStickWorkRelease_id']) || !is_numeric($array['EvnStickWorkRelease_id'])){
							continue;
						}

						// Правильность заполнения полей проверяем только для добавляемых или редактируемых записей
						if($array['RecordStatus_Code'] != 3){
							if(empty($array['EvnStickWorkRelease_IsDraft'])){
								if(empty($array['LpuSection_id']) || !is_numeric($array['LpuSection_id'])){
									$this->response(array(
										'error_code' => 4,
										'error_msg' => 'Не указано отделение в освобождении от работы'
									));
								}

								if(empty($array['MedPersonal_id']) || !is_numeric($array['MedPersonal_id'])){
									$this->response(array(
										'error_code' => 4,
										'error_msg' => 'Не указан врач в освобождении от работы'
									));
								}
							}
							else{
								if(empty($array['Org_id']) || !is_numeric($array['Org_id'])){
									$this->response(array(
										'error_code' => 4,
										'error_msg' => 'Не указана МО в освобождении от работы'
									));
								}
							}

							if(empty($array['EvnStickWorkRelease_begDate'])){
								$this->response(array(
									'error_code' => 4,
									'error_msg' => 'Не указана дата начала периода освобождения от работы'
								));
							}
							elseif(CheckDateFormat($array['EvnStickWorkRelease_begDate']) != 0){
								$this->response(array(
									'error_code' => 4,
									'error_msg' => 'Неверный формат даты начала периода освобождения от работы'
								));
							}

							if(empty($array['EvnStickWorkRelease_endDate'])){
								$this->response(array(
									'error_code' => 4,
									'error_msg' => 'Не указана дата окончания периода освобождения от работы'
								));
							}
							elseif(CheckDateFormat($array['EvnStickWorkRelease_endDate']) != 0){
								$this->response(array(
									'error_code' => 4,
									'error_msg' => 'Неверный формат даты окончания периода освобождения от работы'
								));
							}
						}

						$evnStickWorkRelease[] = array(
							'EvnStickWorkRelease_begDate' => $array['EvnStickWorkRelease_begDate'],
							'EvnStickWorkRelease_endDate' => $array['EvnStickWorkRelease_endDate'],
							'EvnStickWorkRelease_id' => $array['EvnStickWorkRelease_id'],
							'EvnStickBase_id' => $array['EvnStickBase_id'],
							'LpuSection_id' => $array['LpuSection_id'],
							'MedPersonal_id' => $array['MedPersonal_id'],
							'MedPersonal2_id' => isset($array['MedPersonal2_id'])?$array['MedPersonal2_id']:null,
							'MedPersonal3_id' => isset($array['MedPersonal3_id'])?$array['MedPersonal3_id']:null,
							'MedStaffFact_id' => $array['MedStaffFact_id'],
							'MedStaffFact2_id' => isset($array['MedStaffFact2_id'])?$array['MedStaffFact2_id']:null,
							'MedStaffFact3_id' => isset($array['MedStaffFact3_id'])?$array['MedStaffFact3_id']:null,
							'pmUser_id' => $data['pmUser_id'],
							'RecordStatus_Code' => $array['RecordStatus_Code'],
							'EvnStickWorkRelease_IsPredVK' => $array['EvnStickWorkRelease_IsPredVK'],
							'EvnStickWorkRelease_IsDraft' => isset($array['EvnStickWorkRelease_IsDraft'])?$array['EvnStickWorkRelease_IsDraft']:null,
							'EvnStickWorkRelease_IsSpecLpu' => isset($array['EvnStickWorkRelease_IsSpecLpu'])?$array['EvnStickWorkRelease_IsSpecLpu']:null,
							'Org_id' => $array['Org_id'],
							'Post_id' => isset($array['Post_id'])?$array['Post_id']:null,
							'Signatures_mid' => isset($array['Signatures_mid'])?$array['Signatures_mid']:null,
							'Signatures_wid' => isset($array['Signatures_wid'])?$array['Signatures_wid']:null,
							'EvnVK_id' => isset($array['EvnVK_id'])?$array['EvnVK_id']:null
						);
					}
				}
			}

			if(array_key_exists('EvnStick_pid', $data) && empty($data['EvnStickBase_IsFSS'])){
				$resp = $this->dbmodel->checkEvnStickPerson($data['EvnStick_pid'], $data['Person_id'], $evnStickCarePerson);// [{"success":"true"}]
				if(!empty($resp[0]['Error_Msg'])){
					$this->response(array(
						'error_code' => 6,
						'error_msg' => $resp[0]['Error_Msg']
					));
				}

				if($data['EvnStick_mid'] != $data['EvnStick_pid']){
					$resp = $this->dbmodel->checkEvnStickPerson($data['EvnStick_mid'], $data['Person_id'], $evnStickCarePerson);
					if(!empty($resp[0]['Error_Msg'])){
						$this->response(array(
							'error_code' => 6,
							'error_msg' => $resp[0]['Error_Msg']
						));
					}
				}
			}

			// Проверка записей об освобождении от работы
			$cnt = 0;
			$maxEndDate = null;
			$minBegDate = null;

			// Проверка количества записей
			foreach($evnStickWorkRelease as $key => $array ){
				if($array['RecordStatus_Code'] == 3) continue;// Записи на удаление не учитываем
				$cnt++;
			}

			$data['PridStickLeaveType_Code'] = null;
			$data['StickCause_SysNick'] = null;
			$data['StickLeaveType_Code'] = null;
			$data['NextStickCause_SysNick'] = null;
			$data['EvnStick_nid'] = null;

			if(!empty($data['EvnStick_prid'])){
				$data['PridStickLeaveType_Code'] = $this->dbmodel->getFirstResultFromQuery("
					select top 1 
						stl.StickLeaveType_Code
					from 
						EvnStickBase esb (nolock)
						left join v_EvnStick es with(nolock) on es.EvnStick_id = esb.EvnStickBase_id
						left join v_EvnStickDop esd with(nolock) on esd.EvnStickDop_id = esb.EvnStickBase_id
						left join v_EvnStick pes with(nolock) on pes.EvnStick_id = esd.EvnStickDop_pid
						inner join v_StickLeaveType stl (nolock) on stl.StickLeaveType_id = isnull(es.StickLeaveType_id, pes.StickLeaveType_id)
					where 
						esb.EvnStickBase_id = :EvnStick_id
				", array('EvnStick_id' => $data['EvnStick_prid']));

				$data['StickCause_SysNick'] = $this->dbmodel->getFirstResultFromQuery("
					select 
						sc.StickCause_SysNick 
					from 
						v_StickCause sc (nolock) 
					where 
						sc.StickCause_id = :StickCause_id
				", array('StickCause_id' => $data['StickCause_id']));
			}

			if(getRegionNick() == 'ufa' && !empty($data['EvnStick_id'])){
				if(!empty($data['StickLeaveType_id'])){
					$data['StickLeaveType_Code'] = $this->dbmodel->getFirstResultFromQuery("
						select 
							slt.StickLeaveType_Code
						from 
							v_StickLeaveType slt (nolock) 
						where 
							slt.StickLeaveType_id = :StickLeaveType_id
					", array('StickLeaveType_id' => $data['StickLeaveType_id']));
				}


				$data['NextStickCause_SysNick'] = $this->dbmodel->getFirstResultFromQuery("
					select 
						sc.StickCause_SysNick 
					from
						v_EvnStick es (nolock)
						inner join v_StickCause sc (nolock) on sc.StickCause_id = es.StickCause_id 
					where 
						es.EvnStick_prid = :EvnStick_id
				", array('EvnStick_id' => $data['EvnStick_id']));
			}

			$workReleaseCanBeEmpty = false;
			if($data['StickOrder_id'] == 2 && $data['PridStickLeaveType_Code'] == '37' && $data['StickCause_SysNick'] == 'dolsan'){
				$workReleaseCanBeEmpty = true;
			}

			if(!empty($data['StickFSSData_id'])){
				$workReleaseCanBeEmpty = true;
			}

			if(!$workReleaseCanBeEmpty && $cnt == 0){
				$this->response(array(
					'error_code' => 4,
					'error_msg' => 'Должно быть заполнено хотя бы одно освобождение от работы'
				));
			}
			elseif($cnt > ($isKZ? 4:3)){
				$this->response(array(
					'error_code' => 4,
					'error_msg' => 'Количество записей об освобождении от работы не может превышать '.($isKZ?'четырех':'трех')
				));
			}

			if(!empty($data['StickFSSData_id'])){
				if($cnt == 0 && empty($data['StickLeaveType_id'])){
					$this->response(array(
						'error_code' => 4,
						'error_msg' => 'Должно быть заполнено хотя бы одно освобождение от работы или исход ЛВН'
					));
				}
			}

			foreach($evnStickWorkRelease as $key => $array){
				if($array['RecordStatus_Code'] == 3) continue;// Записи на удаление не учитываем

				// Сравниваем даты начала и окончания периода освобождения от работы
				$compareResult = swCompareDates($array['EvnStickWorkRelease_begDate'], $array['EvnStickWorkRelease_endDate']);

				if($compareResult[0] == -1){
					if($cnt == 0 && empty($data['StickLeaveType_id'])){
						$this->response(array(
							'error_code' => 4,
							'error_msg' => 'Дата начала периода не может быть больше даты окончания. Проверьте даты в записях об освобождении от работы'
						));
					}
				}

				// Максимальная дата окончания
				if($maxEndDate == null){
					$maxEndDate = $array['EvnStickWorkRelease_endDate'];
				}
				else{
					$compareResult = swCompareDates($array['EvnStickWorkRelease_endDate'], $maxEndDate);
					if($compareResult[0] == -1){
						$maxEndDate = $array['EvnStickWorkRelease_endDate'];
					}
				}

				// Минимальная дата начала
				if($minBegDate == null){
					$minBegDate = $array['EvnStickWorkRelease_begDate'];
				}
				else{
					$compareResult = swCompareDates($array['EvnStickWorkRelease_begDate'], $minBegDate);
					if($compareResult[0] == 1){
						$minBegDate = $array['EvnStickWorkRelease_begDate'];
					}
				}
			}// foreach($evnStickWorkRelease as $key => $array)

			// Проверяем пересечение периодов
			foreach($evnStickWorkRelease as $key => $array){
				if($array['RecordStatus_Code'] == 3) continue;// Записи на удаление не учитываем

				$crossDates = false;

				foreach($evnStickWorkRelease as $keyTmp => $arrayTmp){
					if($arrayTmp['RecordStatus_Code'] == 3 || $keyTmp == $key ) continue;// Записи на удаление и совпадающие с текущей записью основного цикла не учитываем

					$compareResult = swCompareDates($array['EvnStickWorkRelease_begDate'], $arrayTmp['EvnStickWorkRelease_begDate']);

					// Даты начала совпадают -> пересечение
					if($compareResult[0] == 0){
						$crossDates = true;
					}
					else{
						if($compareResult[0] == 1){// $array - ранний период
							$compareResult = swCompareDates($array['EvnStickWorkRelease_endDate'], $arrayTmp['EvnStickWorkRelease_begDate']);
						}
						elseif( $compareResult[0] == -1 ){// $array - поздний период
							$compareResult = swCompareDates($arrayTmp['EvnStickWorkRelease_begDate'], $array['EvnStickWorkRelease_endDate']);
						}
						else{
							$this->response(array(
								'error_code' => 4,
								'error_msg' => json_encode($compareResult[1])
							));
						}

						// Дата окончания раннего периода больше даты начала позднего периода -> пересечение
						if( $compareResult[0] == 0 || $compareResult[0] == -1 ) $crossDates = true;
					}
				}

				if($crossDates == true){
					$this->response(array(
						'error_code' => 4,
						'error_msg' => 'Обнаружено пересечение прериодов освобождения от работы!<br/>Проверьте указанные сроки и исправьте'
					));
				}
			}// foreach($evnStickWorkRelease as $key => $array)

			$response = $this->dbmodel->getEvnStickWorkReleaseDateLimits(array(
				'EvnStickBase_id' => $data['EvnStick_id'],
				'EvnStickBase_prid' => $data['EvnStick_prid']
			));

			if(!empty($response['Error_Msg'])){
				$this->response(array(
					'error_code' => 6,
					'error_msg' => $response['Error_Msg']
				));
			}

			if(!empty($response['minBegDate'])){
				$compareResult = swCompareDates($maxEndDate, $response['minBegDate']);

				if($compareResult[0] == 0){
					if(getRegionNick() == 'ufa' && ($data['StickLeaveType_Code'] == '37' && $data['NextStickCause_SysNick'] == 'dolsan')){
						// это исключение и ошибку выдавать не нужно
					}
					else{
						$this->response(array(
							'error_code' => 4,
							'error_msg' => 'Максимальная дата освобождения от работы по текущему ЛВН равна минимальной дате начала периодов освобождения от работы в ЛВН-продолжении'
						));
					}
				}
				elseif($compareResult[0] == -1){
					$this->response(array(
						'error_code' => 4,
						'error_msg' => 'Максимальная дата освобождения от работы по текущему ЛВН меньше минимальной даты начала периодов освобождения от работы в ЛВН-продолжении'
					));
				}
			}

			if(!empty($response['maxEndDate'])){
				$compareResult = swCompareDates($response['maxEndDate'], $minBegDate);

				if($compareResult[0] == 0){
					if(getRegionNick() == 'ufa' && ($data['PridStickLeaveType_Code'] == '37' && $data['StickCause_SysNick'] == 'dolsan')){
						// это исключение и ошибку выдавать не нужно
					}
					else {
						$this->response(array(
							'error_code' => 4,
							'error_msg' => 'Минимальная дата освобождения от работы по текущему ЛВН равна максимальной дате окончания периодов освобождения от работы в предыдущем ЛВН'
						));
					}
				}
				elseif($compareResult[0] == -1){
					$this->response(array(
						'error_code' => 4,
						'error_msg' => 'Минимальная дата освобождения от работы по текущему ЛВН меньше максимальной даты окончания периодов освобождения от работы в предыдущем ЛВН'
					));
				}
			}
		}// if(in_array($data['evnStickType'], array(1,2)))

		// Проверка места работы при выписке ЛВН по совместительству
		if($data['evnStickType'] == 2 && empty($data['ignoreCheckEvnStickOrg']) && empty($data['EvnStick_id'])){
			// Проверяем организацию на дубли
			$response = $this->dbmodel->checkEvnStickOrg($data);
			if(!is_array($response) || count($response) == 0){
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Ошибка при проверке организации на предмет использования в других ЛВН'
				));
			}
			elseif($response[0]['cnt'] > 0){
				$this->response(array(//YesNo
					'error_code' => 0,
					'warning_bypass_flag' => 'ignoreCheckEvnStickOrg',
					'warning_msg' => 'При выписке больничного листа по совместительству в блоке "Место работы" должны быть указаны данные организации, в которой работник работает по совместительству. Продолжить сохранение?',
				));
			}
		}

		// Проверяем уникальность серии и номера ЛВН
		$checkResult = $this->dbmodel->checkEvnStickSerNum($data);
		if($checkResult['success'] === false){
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $checkResult['Error_Msg']
			));
		}

		// Стартуем транзакцию
		$this->dbmodel->beginTransaction();

		// Для типа занятости "Основная работа" и "Работа по совместительству" проверяем и, в случае необходимости,
		// обновляем поле Org.Org_StickNick, а также организацию на предмет использования в других ЛВН
		if(!empty($data['Org_id'])){
			if(in_array($data['StickWorkType_id'], array(1, 2))){
				// $data['EvnStick_OrgNick'] = toAnsi($data['EvnStick_OrgNick']);
				if(sw_strlen($data['EvnStick_OrgNick']) > 29 ){
					$this->dbmodel->rollbackTransaction();
					$this->response(array(
						'error_code' => 4,
						'error_msg' => 'Длина наименования организации для печати превышает 29 символов'
					));
				}
			}
		}

		// Сохраняем ЛВН
		switch($data['evnStickType']){
			case 1:// оригинал
				$response = $this->dbmodel->saveEvnStick($data);
				break;
			case 2:// дубликат
				$response = $this->dbmodel->saveEvnStickDop($data);
				break;
		}

		if(!is_array($response) || count($response) == 0){
			$this->dbmodel->rollbackTransaction();
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Ошибка при сохранении ЛВН'
			));
		}

		$val = $response[0];

		if(!empty($val['Error_Msg'])){
			$this->dbmodel->rollbackTransaction();

			$errorCode = !empty($val['Error_Code']) ? (int)$val['Error_Code'] : 100;// не всегда возвращется код, дефолтный пусть будет отличный от нуля

			$ret = array(
				'error_code' => $errorCode,
			);

			switch($errorCode){
				case 101:
					$ret['warning_bypass_flag'] = 'ignoreStickOrderCheck';
					$ret['warning_msg'] = $val['Alert_Msg'];// 'Alert_Msg' => "Внимание! В рамках текущего документа уже заведен первичный ЛВН. Сохранить изменения?"
					$ret['error_code'] = 0;
					break;
				case 102:
					$ret['warning_bypass_flag'] = 'doUpdateJobInfo';
					$ret['warning_msg'] = $val['Alert_Msg'];// 'Alert_Msg' => "Вы указали новое место работы пациента. Обновить данные формы «Человек»?"
					$ret['error_code'] = 0;
					break;
				case 401:
					$ret['error_msg'] = $val['Error_Msg'];// 'Error_Msg' => 'Данный номер ЭЛН уже использован. Необходимо получить новый номер.'
					break;
				default:
					$ret['error_msg'] = $val['Error_Msg'];
			}

			$this->response($ret);
		}

		if(!empty($data['link']) && $data['link'] == 1){
			// Добавляем связку учетного документа и ЛВН
			$response = $this->dbmodel->addEvnLink(array(
				'EvnStickBase_id' => $data['EvnStick_id'],
				'EvnStickBase_mid' => $data['EvnStick_mid'],
				'pmUser_id' => $data['pmUser_id']
			));

			if(!is_array($response) || count($response) == 0){
				$this->dbmodel->rollbackTransaction();
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Ошибка при проверке наличия в БД добавляемой связки документа с ЛВН'
				));
			}
			elseif(!empty($response[0]['Error_Msg'])){
				$this->dbmodel->rollbackTransaction();
				$this->response(array(
					'error_code' => 6,
					'error_msg' => $response[0]['Error_Msg']
				));
			}
		}

		//Удаляем связку с учётным документом если ни один период нетрудоспособности не совпадает с диапазоном дат в учётном документе, только если этот документ не родительский.
		$response = $this->dbmodel->removeEvnLink($data, $evnStickWorkRelease, $val['EvnStick_id']);
		if(!empty($response[0]['Error_Msg'])){
			$this->dbmodel->rollbackTransaction();
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $response[0]['Error_Msg']
			));
		}

		// Удаление записей о пациентах, нуждающихся в уходе
		foreach($evnStickCarePerson as $key => $array){
			if( $array['RecordStatus_Code'] != 3 ) continue;

			$response = $this->dbmodel->deleteEvnStickCarePerson($array);

			if(!is_array($response) || count($response) == 0){
				$this->dbmodel->rollbackTransaction();
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Ошибка при удалении пациента, нуждающегося в уходе'
				));
			}
			elseif(!empty($response[0]['Error_Msg'])){
				$this->dbmodel->rollbackTransaction();
				$this->response(array(
					'error_code' => 6,
					'error_msg' => $response[0]['Error_Msg']
				));
			}
		}

		// Сохранение записей о пациентах, нуждающихся в уходе
		foreach($evnStickCarePerson as $key => $array){
			if($array['RecordStatus_Code'] == 1 || $array['RecordStatus_Code'] == 3) continue;

			$array['Evn_id'] = $val['EvnStick_id'];

			$response = $this->dbmodel->saveEvnStickCarePerson($array);

			if(!is_array($response) || count($response) == 0){
				$this->dbmodel->rollbackTransaction();
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Ошибка при сохранении пациента, нуждающегося в уходе'
				));
			}
			elseif(!empty($response[0]['Error_Msg'])){
				$this->dbmodel->rollbackTransaction();
				$this->response(array(
					'error_code' => 6,
					'error_msg' => $response[0]['Error_Msg']
				));
			}
		}

		// Удаление записей об освобождении от работы
		foreach($evnStickWorkRelease as $key => $array){
			if($array['RecordStatus_Code'] != 3) continue;

			$response = $this->dbmodel->deleteEvnStickWorkRelease($array);

			if(!is_array($response) || count($response) == 0){
				$this->dbmodel->rollbackTransaction();
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Ошибка при удалении освобождения от работы'
				));
			}
			elseif(!empty($response[0]['Error_Msg'])){
				$this->dbmodel->rollbackTransaction();
				$this->response(array(
					'error_code' => 6,
					'error_msg' => $response[0]['Error_Msg']
				));
			}
		}

		// Сохранение записей об освобождении от работы
		foreach($evnStickWorkRelease as $key => $array){
			if($array['RecordStatus_Code'] == 1 || $array['RecordStatus_Code'] == 3) continue;

			$array['EvnStickBase_id'] = $val['EvnStick_id'];
			$array['EvnStickWorkRelease_begDate'] = ConvertDateFormat($array['EvnStickWorkRelease_begDate']);
			$array['EvnStickWorkRelease_endDate'] = ConvertDateFormat($array['EvnStickWorkRelease_endDate']);

			$response = $this->dbmodel->saveEvnStickWorkRelease($array, 'local');

			if(!is_array($response) || count($response) == 0){
				$this->dbmodel->rollbackTransaction();
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Ошибка при сохранении освобождения от работы'
				));
			}
			elseif(!empty($response[0]['Error_Msg'])){
				$this->dbmodel->rollbackTransaction();
				$this->response(array(
					'error_code' => 6,
					'error_msg' => $response[0]['Error_Msg']
				));
			}
		}

		unset($val['Error_Code'], $val['Error_Msg']);

		$this->dbmodel->commitTransaction();

		if(!empty($val['EvnStick_id'])){
			// кэшируем статус
			$this->dbmodel->ReCacheEvnStickStatus(array(
				'EvnStick_id' => $val['EvnStick_id'],
				'pmUser_id' => $data['pmUser_id']
			));
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');

		$this->response(array(
			'error_code' => 0,
			'data' => $val,
		));
	}
	
	/**
	 * Изменение данных ЛВН для мобильного
	 */
	function mSaveEvnStick_put(){
		$this->index_put();
	}

	/**
	 * Загрузка данных по ЛВН
	 */
	function mGetEvnStick_get() {

		$data = $this->ProcessInputData('getEvnStick', null, true);
		$data['fromMobile'] = true;

		// получаем ЛВН
		$resp = $this->dbmodel->getEvnStickForAPI($data);

		if (!empty($resp[0]['Error_Msg'])) {
			$this->response(array('error_code' => 6,'error_msg' => $resp[0]['Error_Msg']));
		}

		// получаем список освобождений от работы
		if (!empty($resp[0]['EvnStickBase_id'])) {
			$release = $this->dbmodel->loadEvnStickWorkReleaseListForAPI($data);
			$resp[0]['evnStickWorkReleaseData'] = $release;

			$care = $this->dbmodel->loadEvnStickCarePersonListForAPI($data);// список людей, нуждающихся в уходе
			$resp[0]['evnStickCarePersonData'] = $care;
		}

		$this->response(array('error_code' => 0,'data' => $resp));

	}

	/**
	 * Получение списка ЛВН по случаю лечения
	 */
	function StickList_get() {
		$data = $this->ProcessInputData('loadEvnStickList', null, true);

		$resp = $this->dbmodel->loadEvnStickListForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (count($resp) > 0 && !empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp[0]['Error_Msg']
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение данных о справке учащегося
	 */
	function StickStudent_get() {
		$data = $this->ProcessInputData('getEvnStickStudent', null, true);

		$resp = $this->dbmodel->getEvnStickStudentForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (count($resp) > 0 && !empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp[0]['Error_Msg']
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}
	
	/**
	 * Добавление данных о справке учащегося
	 */
	function StickStudent_post() {
		$data = $this->ProcessInputData('createEvnStickStudent', null, true);

		$info = $this->dbmodel->getEvnStickInfoForAPI($data);
		if (!is_array($info)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$params = array(
			'EvnStick_id' => null,
			'EvnStick_pid' => $data['Evn_pid'],
			'EvnStick_mid' => $data['Evn_pid'],
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $info['Server_id'],
			'PersonEvn_id' => $info['PersonEvn_id'],
			'EvnStick_setDate' => $data['EvnStick_setDate'],
			'EvnStick_Num' => $data['EvnStick_Num'],
			'StickCause_id' => $data['StickCause_id'],
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'MedPersonal_id' => $info['MedPersonal_id'],
			'Org_id' => $data['Org_id'],
			'StickRecipient_id' => $data['StickRecipient_id'],
			'EvnStick_IsContact' => ($data['EvnStick_isContact'] == 1)?2:1,
			'EvnStick_ContactDescr' => $data['EvnStick_ContactDescr'],
			'session' => $data['session'],
			'pmUser_id' => $data['pmUser_id']
		);

		$resp = $this->dbmodel->saveEvnStickStudent($params);
		if (!is_array($resp) || !isset($resp[0])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp[0]['Error_Msg']
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				array(
					'EvnStickBase_id' => $resp[0]['EvnStick_id'],
					'Evn_id' => $data['Evn_pid']
				)
			)
		));
	}

	/**
	 * Изменение данных о справке учащегося
	 */
	function StickStudent_put() {
		$data = $this->ProcessInputData('updateEvnStickStudent', null, true);

		$resp = $this->dbmodel->getEvnStickStudentForAPI(array(
			'EvnStickBase_id' => $data['EvnStickBase_id'],
			'Lpu_id' => $data['Lpu_id']
		));
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (count($resp) == 0) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Не найдена справка учащегося'
			));
		}

		foreach($data as $key => $value) {
			if (!empty($resp[0][$key]) && empty($value)) {
				$data[$key] = $resp[0][$key];
			}
		}
		if (empty($data['Evn_pid'])) {
			$data['Evn_pid'] = $resp[0]['Evn_id'];
		}

		$info = $this->dbmodel->getEvnStickInfoForAPI($data);
		if (!is_array($info)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$params = array(
			'EvnStick_id' => $data['EvnStickBase_id'],
			'EvnStick_pid' => $data['Evn_pid'],
			'EvnStick_mid' => $data['Evn_pid'],
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $info['Server_id'],
			'PersonEvn_id' => $info['PersonEvn_id'],
			'EvnStick_setDate' => $data['EvnStick_setDate'],
			'EvnStick_Num' => $data['EvnStick_Num'],
			'StickCause_id' => $data['StickCause_id'],
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'MedPersonal_id' => $info['MedPersonal_id'],
			'Org_id' => $data['Org_id'],
			'StickRecipient_id' => $data['StickRecipient_id'],
			'EvnStick_IsContact' => ($data['EvnStick_isContact'] == 1)?2:1,
			'EvnStick_ContactDescr' => $data['EvnStick_ContactDescr'],
			'session' => $data['session'],
			'pmUser_id' => $data['pmUser_id']
		);

		$resp = $this->dbmodel->saveEvnStickStudent($params);
		if (!is_array($resp) || !isset($resp[0])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp[0]['Error_Msg']
			));
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 * Получение списка освобождений
	 */
	function WorkReleaseList_get() {
		$data = $this->ProcessInputData('loadEvnStickWorkReleaseList');

		$resp = $this->dbmodel->loadEvnStickWorkReleaseListForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение данных об освобождении
	 */
	function WorkRelease_get() {
		$data = $this->ProcessInputData('getEvnStickWorkRelease', null, true);

		$resp = $this->dbmodel->getEvnStickWorkReleaseForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (count($resp) > 0 && !empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp[0]['Error_Msg']
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Добавление данных об освобождении
	 */
	function WorkRelease_post() {
		$data = $this->ProcessInputData('createEvnStickWorkRelease', null, true);

		$msf_ids = array($data['MedStaffFact_id']);
		if (!empty($data['MedStaffFact2_id'])) {
			$msf_ids[] = $data['MedStaffFact2_id'];
		}
		if (!empty($data['MedStaffFact3_id'])) {
			$msf_ids[] = $data['MedStaffFact3_id'];
		}
		$msf_ids_str = implode(",", $msf_ids);
		$resp = $this->dbmodel->queryResult("
			select MedStaffFact_id, MedPersonal_id
			from v_MedStaffFact with(nolock)
			where MedStaffFact_id in ({$msf_ids_str})
		");
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$info = array(
			'MedPersonal_id' => null,
			'MedPersonal2_id' => null,
			'MedPersonal3_id' => null,
			'MedStaffFact_id' => null,
			'MedStaffFact2_id' => null,
			'MedStaffFact3_id' => null,
		);
		foreach($resp as $item) {
			switch(true) {
				case ($item['MedStaffFact_id'] == $data['MedStaffFact_id']):
					$info['MedPersonal_id'] = $item['MedPersonal_id'];break;
				case ($item['MedStaffFact_id'] == $data['MedStaffFact2_id']):
					$info['MedPersonal2_id'] = $item['MedPersonal_id'];break;
				case ($item['MedStaffFact_id'] == $data['MedStaffFact3_id']):
					$info['MedPersonal3_id'] = $item['MedPersonal_id'];break;
			}
		}

		$params = array(
			'EvnStickWorkRelease_id' => null,
			'EvnStickBase_id' => $data['EvnStickBase_id'],
			'EvnStickWorkRelease_begDate' => $data['EvnStickWorkRelease_begDate'],
			'EvnStickWorkRelease_endDate' => $data['EvnStickWorkRelease_endDate'],
			'MedPersonal_id' => $info['MedPersonal_id'],
			'MedPersonal2_id' => $info['MedPersonal2_id'],
			'MedPersonal3_id' => $info['MedPersonal3_id'],
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'MedStaffFact2_id' => $data['MedStaffFact2_id'],
			'MedStaffFact3_id' => $data['MedStaffFact3_id'],
			'LpuSection_id' => $data['LpuSection_id'],
			'EvnStickWorkRelease_IsPredVK' => $data['EvnStickWorkRelease_isPredVK'],
			'EvnStickWorkRelease_IsDraft' => $data['EvnStickWorkRelease_isDraft'],
			'EvnStickWorkRelease_IsDraft' => 0,
			'EvnStickWorkRelease_IsSpecLpu' => null,
			'session' => $data['session'],
			'pmUser_id' => $data['pmUser_id']
		);

		$resp = $this->dbmodel->saveEvnStickWorkRelease($params);
		if (!is_array($resp) || !isset($resp[0])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => strip_tags($resp[0]['Error_Msg'])
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				array(
					'EvnStickBase_id' => $data['EvnStickBase_id'],
					'EvnStickWorkRelease_id' => $resp[0]['EvnStickWorkRelease_id']
				)
			)
		));
	}

	/**
	 * Изменение данных об освобождении
	 */
	function WorkRelease_put() {
		$data = $this->ProcessInputData('updateEvnStickWorkRelease', null, true);

		$resp = $this->dbmodel->getEvnStickWorkReleaseForAPI(array(
			'EvnStickWorkRelease_id' => $data['EvnStickWorkRelease_id'],
			'Lpu_id' => $data['Lpu_id']
		));
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (count($resp) == 0) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Не найдено освобождение'
			));
		}

		foreach($data as $key => $value) {
			if (!empty($resp[0][$key]) && empty($value)) {
				$data[$key] = $resp[0][$key];
			}
		}
		$data['EvnStickBase_id'] = $resp[0]['EvnStickBase_id'];

		$msf_ids = array($data['MedStaffFact_id']);
		if (!empty($data['MedStaffFact2_id'])) {
			$msf_ids[] = $data['MedStaffFact2_id'];
		}
		if (!empty($data['MedStaffFact3_id'])) {
			$msf_ids[] = $data['MedStaffFact3_id'];
		}
		$msf_ids_str = implode(",", $msf_ids);
		$resp = $this->dbmodel->queryResult("
			select MedStaffFact_id, MedPersonal_id
			from v_MedStaffFact with(nolock)
			where MedStaffFact_id in ({$msf_ids_str})
		");
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$info = array(
			'MedStaffFact_id' => null,
			'MedStaffFact2_id' => null,
			'MedStaffFact3_id' => null,
		);
		foreach($resp as $item) {
			switch(true) {
				case ($item['MedStaffFact_id'] == $data['MedStaffFact_id']):
					$info['MedPersonal_id'] = $item['MedPersonal_id'];break;
				case ($item['MedStaffFact_id'] == $data['MedStaffFact2_id']):
					$info['MedPersonal2_id'] = $item['MedPersonal_id'];break;
				case ($item['MedStaffFact_id'] == $data['MedStaffFact3_id']):
					$info['MedPersonal3_id'] = $item['MedPersonal_id'];break;
			}
		}

		$params = array(
			'EvnStickWorkRelease_id' => $data['EvnStickWorkRelease_id'],
			'EvnStickBase_id' => $data['EvnStickBase_id'],
			'EvnStickWorkRelease_begDate' => $data['EvnStickWorkRelease_begDate'],
			'EvnStickWorkRelease_endDate' => $data['EvnStickWorkRelease_endDate'],
			'MedPersonal_id' => $info['MedPersonal_id'],
			'MedPersonal2_id' => $info['MedPersonal2_id'],
			'MedPersonal3_id' => $info['MedPersonal3_id'],
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'MedStaffFact2_id' => $data['MedStaffFact2_id'],
			'MedStaffFact3_id' => $data['MedStaffFact3_id'],
			'LpuSection_id' => $data['LpuSection_id'],
			'EvnStickWorkRelease_IsPredVK' => $data['EvnStickWorkRelease_isPredVK'],
			'EvnStickWorkRelease_IsDraft' => $data['EvnStickWorkRelease_isDraft'],
			'EvnStickWorkRelease_IsDraft' => 0,
			'EvnStickWorkRelease_IsSpecLpu' => null,
			'session' => $data['session'],
			'pmUser_id' => $data['pmUser_id']
		);

		$resp = $this->dbmodel->saveEvnStickWorkRelease($params);
		if (!is_array($resp) || !isset($resp[0])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => strip_tags($resp[0]['Error_Msg'])
			));
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 * Получение списка связей случая лечения с ЛВН
	 */
	function EvnLinkList_get() {
		$data = $this->ProcessInputData('getEvnLinkList');

		$resp = $this->dbmodel->getEvnLinkListForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Создание связи случая лечения с ЛВН
	 */
	function EvnLink_post() {
		$data = $this->ProcessInputData('createEvnLink', null, true);

		$params = array(
			'EvnStickBase_mid' => $data['Evn_id'],
			'EvnStickBase_id' => $data['Evn_lid'],
			'pmUser_id' => $data['pmUser_id']
		);

		$resp = $this->dbmodel->addEvnLink($params);
		if (!is_array($resp) || !isset($resp[0])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp[0]['Error_Msg']
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				'Evn_id' => $data['Evn_id'],
				'EvnLink_id' => $resp[0]['EvnLink_id']
			)
		));
	}

	/**
	 * Удаление связи случая лечения с ЛВН
	 *
	 * @desсription
	 * {
	 * 		"output_params": {
	 * 			"error_code": "Код ошибки"
	 * 		},
	 * 		"example": {
	 * 			"error_code": 0
	 * 		}
	 * }
	 */
	function EvnLink_delete() {
		$data = $this->ProcessInputData('deleteEvnLink', null, true);

		$resp = $this->dbmodel->deleteEvnLinkForAPI($data);
		if (!is_array($resp) || !isset($resp[0])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp[0]['Error_Msg']
			));
		}

		$this->response(array(
			'error_code' => 0
		));
	}
	
	/**
	 * Получение даты ЛВН
	 *
	 * @desсription
	 * {
	 * 		"output_params": {
	 * 			"EvnStick_setDate": "Дата ЛВН"
	 * 		},
	 * 		"example": {
	 * 			"error_code": 0,
	 * 			"data": {"EvnStick_setDate": "02.11.2018"}
	 * 		}
	 * }
	 */
	function mGetSetdate_get() {

		$data = $this->ProcessInputData('getEvnStickSetdate', null, true);
		$resp = $this->dbmodel->getEvnStickSetdate($data);

		if (!is_array($resp)) 	$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		$this->response(array('error_code' => 0,'data' => $resp));
	}

	/**
	 * Удаление ЛВН
	 *
	 * @desсription
	 * {
	 * 		"output_params": {
	 * 			"error_code": "Код ошибки"
	 * 		},
	 * 		"example": {
	 * 			"error_code": 0
	 * 		}
	 * }
	 */
	function mDeleteEvnStick_post() {

		$data = $this->ProcessInputData('mDeleteEvnStick', null, true);
		$resp = $this->dbmodel->deleteEvnStick($data);

		if (!empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp[0]['Error_Msg']
			));
		}

		$this->response(array('error_code' => 0));
	}
}
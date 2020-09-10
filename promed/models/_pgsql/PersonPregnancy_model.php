<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      PersonPregnancy
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @author       Dmitriy Vlasenko
 * @version      11 2014
 *
 * @property PersonRegister_model PersonRegister_model
 */

class PersonPregnancy_model extends swPgModel {

    protected $dateTimeForm104 = "DD.MM.YYYY";
    protected $dateTimeForm108 = "HH24:MI:SS";
    protected $dateTimeForm120 = "YYYY-MM-DD HH24:MI:SS";

	public $inputRules = array(
		'loadFinishedList' => array(
			array(
				'field' => 'Person_SurName',
				'label' => 'Фамилия',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_FirName',
				'label' => 'Имя',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_SecName',
				'label' => 'Отчество',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_BirthDay',
				'label' => 'Дата рождения',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnUsluga_setDate_From',
				'label' => 'Дата исхода от',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnUsluga_setDate_To',
				'label' => 'Дата исхода до',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'HospLpu_id',
				'label' => 'МО госпитализации',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LeaveType_id',
				'label' => 'Исход госпитализации',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Номер стартовой записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество записей',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadInterruptedList' => array(
			array(
				'field' => 'Person_SurName',
				'label' => 'Фамилия',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_FirName',
				'label' => 'Имя',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_SecName',
				'label' => 'Отчество',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_BirthDay',
				'label' => 'Дата рождения',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnUsluga_setDate_From',
				'label' => 'Дата исхода от',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnUsluga_setDate_To',
				'label' => 'Дата исхода до',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'HospLpu_id',
				'label' => 'МО госпитализации',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LeaveType_id',
				'label' => 'Исход госпитализации',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Номер стартовой записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество записей',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadList' => array(
			array(
				'field' => 'Person_SurName',
				'label' => 'Фамилия',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_FirName',
				'label' => 'Имя',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_SecName',
				'label' => 'Отчество',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_AgeFrom',
				'label' => 'Возраст с',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'Person_AgeTo',
				'label' => 'Возраст по',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'PersonRegister_Code',
				'label' => 'Номер индивидуальной карты',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'PersonPregnancy_PeriodFrom',
				'label' => 'Срок с',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PersonPregnancy_PeriodTo',
				'label' => 'Срок по',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'RiskType_id',
				'label' => 'Степень риска',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'AttachLpu_id',
				'label' => 'МО прикрепления',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'HospLpu_id',
				'label' => 'МО госпитализации',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_iid',
				'label' => 'МО постановки на учет',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'MedPersonal_iid',
				'label' => 'Врач учета',
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
				'field' => 'Diag_oid',
				'label' => 'Доп. диагноз',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_Code_From',
				'label' => 'Основной диагноз с',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Diag_Code_To',
				'label' => 'Основной диагноз по',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Diag_sCode_From',
				'label' => 'Сопуствующий диагноз с',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Diag_sCode_To',
				'label' => 'Сопуствующий диагноз по',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'PersonPregnancy_PeriodFrom',
				'label' => 'Срок с',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PersonPregnancy_PeriodTo',
				'label' => 'Срок по',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PersonPregnancy_IsKDO',
				'label' => 'Посещения КДО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonRegister_setDateRange',
				'label' => 'Дата постановки на учет',
				'rules' => '',
				'type' => 'daterange'
			),
			array(
				'field' => 'PersonRegister_disDateRange',
				'label' => 'Дата исхода',
				'rules' => '',
				'type' => 'daterange'
			),
			array(
				'field' => 'RiskType_id',
				'label' => 'Идентификатор степени риска',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PregnancyResult_id',
				'label' => 'Исход родов',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Type',
				'label' => 'Тип',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Номер стартовой записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество записей',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 100,
				'field' => 'PregnancyType_id',
				'label' => 'Вид исхода',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RiskType_did',
				'label' => 'Риск по ПР',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RiskType_bid',
				'label' => 'Идентификатор риска 572н',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ObstetricComplication_id',
				'label' => 'Акушерское осложнение',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadListRecommRouter' => array(
			array(
				'field' => 'Person_SurName',
				'label' => 'Фамилия',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_FirName',
				'label' => 'Имя',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_SecName',
				'label' => 'Отчество',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_AgeFrom',
				'label' => 'Возраст с',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'Person_AgeTo',
				'label' => 'Возраст по',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'PersonRegister_Code',
				'label' => 'Номер индивидуальной карты',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'PersonPregnancy_PeriodFrom',
				'label' => 'Срок с',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PersonPregnancy_PeriodTo',
				'label' => 'Срок по',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'RiskType_id',
				'label' => 'Степень риска',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'AttachLpu_id',
				'label' => 'МО прикрепления',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'HospLpu_id',
				'label' => 'МО госпитализации',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_iid',
				'label' => 'МО постановки на учет',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'MedPersonal_iid',
				'label' => 'Врач учета',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Diag_id',
				'label' => 'Диагноз',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_oid',
				'label' => 'Доп. диагноз',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_Code_From',
				'label' => 'Основной диагноз с',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Diag_Code_To',
				'label' => 'Основной диагноз по',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Diag_sCode_From',
				'label' => 'Сопуствующий диагноз с',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Diag_sCode_To',
				'label' => 'Сопуствующий диагноз по',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'PersonPregnancy_PeriodFrom',
				'label' => 'Срок с',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PersonPregnancy_PeriodTo',
				'label' => 'Срок по',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PersonPregnancy_IsKDO',
				'label' => 'Посещения КДО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonRegister_setDateRange',
				'label' => 'Дата постановки на учет',
				'rules' => '',
				'type' => 'daterange'
			),
			array(
				'field' => 'PersonRegister_disDateRange',
				'label' => 'Дата исхода',
				'rules' => '',
				'type' => 'daterange'
			),
			array(
				'field' => 'RiskType_id',
				'label' => 'Идентификатор степени риска',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PregnancyResult_id',
				'label' => 'Исход родов',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Type',
				'label' => 'Тип',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Номер стартовой записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество записей',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MesLevel_id',
				'label' => 'МО родоразрешения',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'YesNo_id',
				'label' => 'МО родоразрешения',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ADKS',
				'label' => 'Госпитализация по согласованию с АДКЦ',
				'rules' => '',
				'type' => 'swcheckbox'
			),
			array(
				'field' => 'SignalInfo',
				'label' => 'Сигнальная информация',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Trimester',
				'label' => 'Триместр',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'RiskType_gid',
				'label' => 'Степень риска по Радзинскому',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ObstetricComplication_id',
				'label' => 'Акушерское осложнение',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadListMonitorCenter' => array(
			array(
				'field' => 'Person_SurName',
				'label' => 'Фамилия',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_FirName',
				'label' => 'Имя',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_SecName',
				'label' => 'Отчество',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_AgeFrom',
				'label' => 'Возраст с',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'Person_AgeTo',
				'label' => 'Возраст по',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'PersonRegister_Code',
				'label' => 'Номер индивидуальной карты',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'PersonPregnancy_PeriodFrom',
				'label' => 'Срок с',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PersonPregnancy_PeriodTo',
				'label' => 'Срок по',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'RiskType_id',
				'label' => 'Степень риска',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'AttachLpu_id',
				'label' => 'МО прикрепления',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'HospLpu_id',
				'label' => 'МО госпитализации',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_iid',
				'label' => 'МО постановки на учет',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Lpu_oid',
				'label' => 'МО госпитализации',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Diag_id',
				'label' => 'Диагноз',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPS_setDate',
				'label' => 'Дата поступления',
				'rules' => '',
				'type' => 'daterange'
			),
			array(
				'field' => 'Diag_Code_From',
				'label' => 'Основной диагноз с',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Diag_Code_To',
				'label' => 'Основной диагноз по',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Diag_sCode_From',
				'label' => 'Сопуствующий диагноз с',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Diag_sCode_To',
				'label' => 'Сопуствующий диагноз по',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'PersonPregnancy_PeriodFrom',
				'label' => 'Срок с',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PersonPregnancy_PeriodTo',
				'label' => 'Срок по',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PersonPregnancy_IsKDO',
				'label' => 'Посещения КДО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonRegister_setDateRange',
				'label' => 'Дата постановки на учет',
				'rules' => '',
				'type' => 'daterange'
			),
			array(
				'field' => 'PersonRegister_disDateRange',
				'label' => 'Дата исхода',
				'rules' => '',
				'type' => 'daterange'
			),
			array(
				'field' => 'RiskType_id',
				'label' => 'Идентификатор степени риска',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PregnancyResult_id',
				'label' => 'Исход родов',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Type',
				'label' => 'Тип',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Номер стартовой записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество записей',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MesLevel_id',
				'label' => 'МО родоразрешения',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'YesNo_id',
				'label' => 'МО родоразрешения',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ADKS',
				'label' => 'Госпитализация по согласованию с АДКЦ',
				'rules' => '',
				'type' => 'swcheckbox'
			),
			array(
				'field' => 'SignalInfo',
				'label' => 'Сигнальная информация',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Trimester',
				'label' => 'Триместр',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'HighRisk_setDT',
				'label' => 'Дата поступления',
				'rules' => '',
				'type' => 'daterange'
			),
			array(
				'field' => 'ObstetricComplication_id',
				'label' => 'Акушерское осложнение',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadTrimesterListMO' => array(
			array(
				'field' => 'Lpu_iid',
				'label' => 'ИД МО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_Nick',
				'label' => 'МО',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Trimester1',
				'label' => '1 Триместр',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Trimester2',
				'label' => '2 Триместр',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Trimester3',
				'label' => '3 Триместр',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Номер стартовой записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество записей',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadNotIncludeList' => array(
			array(
				'field' => 'Person_SurName',
				'label' => 'Фамилия',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_FirName',
				'label' => 'Имя',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_SecName',
				'label' => 'Отчество',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Lpu_oid',
				'label' => 'МО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Evn_setDateRange',
				'label' => 'Период',
				'rules' => '',
				'type' => 'daterange'
			),
			array(
				'field' => 'Diag_Code_From',
				'label' => 'Основной диагноз с',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Diag_Code_To',
				'label' => 'Основной диагноз по',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Номер стартовой записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество записей',
				'rules' => '',
				'type' => 'id'
			),
			//gaf 29112017
			array(
				'default' => 0,
				'field' => 'EvnType_id',
				'label' => 'Тип случая',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'MedPersonal_iidd',
				'label' => 'Врач',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RiskType_cid',
				'label' => 'Идентификатор риска 572н',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RiskType_did',
				'label' => 'Идентификатор риска ПР',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ObstetricComplication_id',
				'label' => 'Акушерское осложнение',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadPersonPregnancyTree' => array(
			array(
				'field' => 'node',
				'label' => 'Нода дерева',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'object',
				'label' => 'Объект',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'PersonRegister_id',
				'label' => 'Идентификатор записи в регистре беременных',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'createCategoryMethod',
				'label' => 'Метод для создания категорий',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'allowCreateButton',
				'label' => '',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'deleteCategoryMethod',
				'label' => 'Метод для удаления категорий',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'allowDeleteButton',
				'label' => '',
				'rules' => '',
				'type' => 'checkbox'
			),
		),
		'loadPersonPregnancyResultGrid' => array(
			array(
				'field' => 'PersonPregnancy_id',
				'label' => 'Идентификатор записи в регистре беременных',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'loadPersonPregnancyGravidogramData' => array(
			array(
				'field' => 'PersonRegister_id',
				'label' => 'Идентификатор записи из регистра беременных',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'loadPersonPregnancy' => array(
			array(
				'field' => 'PersonRegister_id',
				'label' => 'Идентификатор записи в регистре беременных',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PersonPregnancy_id',
				'label' => 'Идентификатор анкеты',
				'rules' => '',
				'type' => 'id'
			),
		),
		'savePersonPregnancy' => array(
			array(
				'field' => 'PersonPregnancy_id',
				'label' => 'Идентфикатор анкеты',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonDisp_id',
				'label' => 'Идентфикатор карты диспансерного наблюдения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonRegister_id',
				'label' => 'Идентификатор записи в регистре беременных',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентфикатор человека (мать)',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'RiskType_id',
				'label' => 'Степень риска',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonPregnancy_RiskDPP',
				'label' => 'Риск перинатальной патологии',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Evn_id',
				'label' => 'Случай, из которого создана анкета',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonRegister_setDate',
				'label' => 'Дата постановки на учет',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'PersonPregnancy_Period',
				'label' => 'Срок беременности при постановке на учет',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PersonPregnancy_begMensDate',
				'label' => 'Последние менструации с',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'PersonPregnancy_endMensDate',
				'label' => 'Последние менструации по',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'PersonPregnancy_birthDate',
				'label' => 'Предполагаемый срок родов',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'PersonRegister_Code',
				'label' => 'Номер карты',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Lpu_iid',
				'label' => 'МО наблюдения',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_iid',
				'label' => 'Врач',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PersonPregnancy_Phone',
				'label' => 'Телефон',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'PersonPregnancy_PhoneWork',
				'label' => 'Телефон раб.',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'PersonPregnancyEducation_id',
				'label' => 'Образование',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Post_id',
				'label' => 'Профессия',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PregnancyFamilyStatus_id',
				'label' => 'Семейное положение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonPregnancy_Height',
				'label' => 'Рост',
				'rules' => '',
				'type' => 'int'
			),
			//gaf #112144 изменение типа поля с int
			array(
				'field' => 'PersonPregnancy_Weight',
				'label' => 'Вес',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'PersonPregnancy_IsWeight25',
				'label' => 'Превышение нормы веса на 25% и более',
				'rules' => '',
				'type' => 'swcheckbox'
			),
			array(
				'field' => 'BloodGroupType_id',
				'label' => 'Группа крови',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RhFactorType_id',
				'label' => 'Резус-фактор',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_did',
				'label' => 'Идентификатор человека (отец)',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonPregnancy_dadFIO',
				'label' => 'ФИО отца',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'PersonPregnancy_dadAge',
				'label' => 'возвраст отца',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PersonPregnancy_dadAddress',
				'label' => 'Адрес проживания отца',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'PersonPregnancy_dadPhone',
				'label' => 'Телефон отца',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'BloodGroupType_dadid',
				'label' => 'Группа крови отца',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RhFactorType_dadid',
				'label' => 'Резус-фактор отца',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Org_did',
				'label' => 'Место работы отца',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Answers',
				'label' => 'Ответы из анкеты',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'PersonPregnancyResultData',
				'label' => 'Данные об исходе предыдущих беременностей',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Post_aid',
				'label' => 'Профессия',
				'rules' => '',
				'type' => 'id'				
			),
            array(
                'field' => 'DifferentLpu',
                'label' => 'Иное МО',
                'rules' => 'trim',
                'type' => 'string'
            )
		),
		'deletePersonRegister' => array(
			array(
				'field' => 'PersonRegister_id',
				'label' => 'Идентфикикатор записи из регистра беременных',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'deletePersonPregnancy' => array(
			array(
				'field' => 'PersonPregnancy_id',
				'label' => 'Идентификикатор анкеты по беременности',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'getPersonRegisterByEvnVizitPL' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Evn_id',
				'label' => 'Идентификатор события',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'date',
				'label' => 'Дата',
				'rules' => '',
				'type' => 'date'
			),
		),
		'getPersonRegisterByEvnSection' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnSection_id',
				'label' => 'Идентфикикатр движения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnSection_setDate',
				'label' => 'Дата начала движения',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'EvnSection_disDate',
				'label' => 'Дата окончания движения',
				'rules' => '',
				'type' => 'date'
			),
		),
		'savePregnancyScreen' => array(
			array(
				'field' => 'PregnancyScreen_id',
				'label' => 'Идентфикикатр скрининга',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonRegister_id',
				'label' => 'Идентфикикатр записи из регистра беременных',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Evn_id',
				'label' => 'Идентфикикатр события',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PregnanyScreen_RiskPerPat',
				'label' => 'Риск перинатальной патологии',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PregnancyScreen_setDate',
				'label' => 'Дата скрининга',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'Lpu_oid',
				'label' => 'Идентификатор МО',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_oid',
				'label' => 'Идентификатор врача',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PregnancyScreen_Comment',
				'label' => 'Замечания',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Diag_id',
				'label' => 'Основной диагноз',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Answers',
				'label' => 'Ответы из анкеты',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'PregnancyScreenSopDiagData',
				'label' => 'Данные о сопутствующих диагнозах в скринингах',
				'rules' => 'required|trim',
				'type' => 'string'
			),
			//gaf #106655 11042018
			array(
				'field' => 'GestationalAge_id',
				'label' => 'Срок беременности',
				'rules' => '',
				'type' => 'string'
			),
		),
		'deletePregnancyScreen' => array(
			array(
				'field' => 'PregnancyScreen_id',
				'label' => 'Идентфикатор скрининга',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'loadPregnancyScreen' => array(
			array(
				'field' => 'PregnancyScreen_id',
				'label' => 'Идентфикатор скрининга',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'loadPregnancyScreenSopDiagGrid' => array(
			array(
				'field' => 'PregnancyScreen_id',
				'label' => 'Идентфикатор скрининга',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'loadPersonPregnancyEvnGrid' => array(
			array(
				'field' => 'PersonRegister_id',
				'label' => 'Идентфикатор записи из регистра беременных',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Номер стартовой записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество записей',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadConsultationGrid' => array(
			array(
				'field' => 'PersonRegister_id',
				'label' => 'Идентфикатор записи из регистра беременных',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Номер стартовой записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество записей',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadResearchGrid' => array(
			array(
				'field' => 'PersonRegister_id',
				'label' => 'Идентфикатор записи из регистра беременных',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Номер стартовой записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество записей',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadBirthSpecStac' => array(
			array(
				'field' => 'BirthSpecStac_id',
				'label' => 'Идентификатор исхода беременности',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonRegister_id',
				'label' => 'Идентификатор записи в регистре беременных',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Evn_id',
				'label' => 'Идентификатор события',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PregnancyScreenDates',
				'label' => 'Массив с датами измененных на клиенте скринингов',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'AnketaAnswers',
				'label' => 'Ответы из анкеты по беременности',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'LastScreenAnswers',
				'label' => 'Ответы из последнего скрининга',
				'rules' => '',
				'type' => 'string'
			),
		),
		'saveBirthSpecStac' => array(
			array(
				'field' => 'BirthSpecStac_id',
				'label' => 'Идентфикатор специфики по родам',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonRegister_id',
				'label' => 'Идентификатор записи в регистре беременных',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Evn_id',
				'label' => 'Идентфикатор события',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnSection_id',
				'label' => 'Идентфикатор движения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_oid',
				'label' => 'МО исхода',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_oid',
				'label' => 'Врач, создающий исход',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PregnancySpec_id',
				'label' => 'Идентификатор специфики по беременности в карте ДУ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'BirthSpecStac_CountPregnancy',
				'label' => 'Которая беременность',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'BirthSpecStac_OutcomDate',
				'label' => 'Дата исхода беременности',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'BirthSpecStac_OutcomTime',
				'label' => 'Время исхода беременности',
				'rules' => 'required',
				'type' => 'time'
			),
			array(
				'field' => 'BirthSpecStac_OutcomPeriod',
				'label' => 'Срок беременности',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'PregnancyResult_id',
				'label' => 'Исход беременности',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'BirthSpecStac_BloodLoss',
				'label' => 'Кровопотери',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'BirthSpecStac_IsRWtest',
				'label' => 'Обследование на сифилис',
				'rules' => '',
				'type' => 'swcheckbox'
			),
			array(
				'field' => 'BirthSpecStac_IsRW',
				'label' => 'Обследование на сифилис сероположительное',
				'rules' => '',
				'type' => 'swcheckbox'
			),
			array(
				'field' => 'BirthSpecStac_IsHIVtest',
				'label' => 'Обследование на ВИЧ',
				'rules' => '',
				'type' => 'swcheckbox'
			),
			array(
				'field' => 'BirthSpecStac_IsHIV',
				'label' => 'Обследование на ВИЧ сероположительное',
				'rules' => '',
				'type' => 'swcheckbox'
			),
			array(
				'field' => 'BirthSpecStac_IsHBtest',
				'label' => 'Обследование на гепатит B',
				'rules' => '',
				'type' => 'swcheckbox'
			),
			array(
				'field' => 'BirthSpecStac_IsHB',
				'label' => 'Обследование на гепатит B сероположительное',
				'rules' => '',
				'type' => 'swcheckbox'
			),
			array(
				'field' => 'BirthSpecStac_IsHCtest',
				'label' => 'Обследование на гепатит C',
				'rules' => '',
				'type' => 'swcheckbox'
			),
			array(
				'field' => 'BirthSpecStac_IsHC',
				'label' => 'Обследование на гепатит C сероположительное',
				'rules' => '',
				'type' => 'swcheckbox'
			),
			array(
				'field' => 'BirthSpecStac_CountPregnancy',
				'label' => 'Которая беременность',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'BirthSpecStac_CountChild',
				'label' => 'Количество плодов',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'BirthSpecStac_CountChildAlive',
				'label' => 'Количество живорожденных',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'BirthSpecStac_CountBirth',
				'label' => 'Роды которые',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'BirthPlace_id',
				'label' => 'Место родов',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'BirthSpec_id',
				'label' => 'Особенности родов',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'BirthCharactType_id',
				'label' => 'Характер родов',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'BirthSpecStac_SurgeryVolume',
				'label' => 'Объем оперативного вмешательства при внематочной беременности',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'AbortLpuPlaceType_id',
				'label' => 'Место аборта',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'AbortLawType_id',
				'label' => 'Вид аборта',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'AbortMethod_id',
				'label' => 'Метод аборта',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'AbortIndicat_id',
				'label' => 'Показания',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'BirthSpecStac_InjectVMS',
				'label' => 'Введено ВМС',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'BirthSpecStac_IsContrac',
				'label' => 'Послеродовая контрацепция',
				'rules' => '',
				'type' => 'swcheckbox'
			),
			array(
				'field' => 'BirthSpecStac_ContracDesc',
				'label' => 'Сведения о послеродовой контрацепции',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Answers',
				'label' => 'Ответы из анкеты',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'ChildDeathData',
				'label' => 'Данные о мертворожденных',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'ignoreCheckBirthSpecStacDate',
				'label' => 'Признак игнорирования проверки даты исхода беременности',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ignoreCheckChildrenCount',
				'label' => 'Признак игнорирования проверки количества плодов',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PregnancyType_id',
				'label' => 'Вид исхода',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LaborActivity_id',
				'label' => 'Родовая деятельность',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'FetalHeartbeat_id',
				'label' => 'Сердцебиение плода',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'FetalHead_id',
				'label' => 'Головка плода',
				'rules' => '',
				'type' => 'int'
			)
		),
		'beforeDeleteBirthSpecStac' => array(
			array(
				'field' => 'BirthSpecStac_id',
				'label' => 'Идентификатор исхода беременности',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'deleteBirthSpecStac' => array(
			array(
				'field' => 'BirthSpecStac_id',
				'label' => 'Идентификатор исхода беременности',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'loadDeathMother' => array(
			array(
				'field' => 'DeathMother_id',
				'label' => 'Идентификатор случая материнской смертности',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'saveDeathMother' => array(
			array(
				'field' => 'DeathMother_id',
				'label' => 'Идентификатор случая материнской смертности',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonRegister_id',
				'label' => 'Идентификатор записи в регистре беременных',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Evn_id',
				'label' => 'Идентификатор события',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_oid',
				'label' => 'Идентификатор МО',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_oid',
				'label' => 'Идентификатор врача',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'DeathMother_DeathDate',
				'label' => 'Дата смерти',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'DeathMotherType_id',
				'label' => 'Тип материнской смерти',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_cid',
				'label' => 'Клинический диагноз',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_aid',
				'label' => 'Патологоанатомический диагноз',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DeathMother_DeathPlace',
				'label' => 'Место смерти',
				'rules' => '',
				'type' => 'string'
			),
		),
		'deleteDeathMother' => array(
			array(
				'field' => 'DeathMother_id',
				'label' => 'Идентификатор случая материнской смертности',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'loadBirthCertificate' => array(
			array(
				'field' => 'BirthCertificate_id',
				'label' => 'Идентификатор родового сертификата',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'saveBirthCertificate' => array(
			array(
				'field' => 'BirthCertificate_id',
				'label' => 'Идентификатор родового сертификата',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonRegister_id',
				'label' => 'Идентификатор записи в регистре беременных',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'BirthCertificate_Ser',
				'label' => 'Серия родового сертификата',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'BirthCertificate_Num',
				'label' => 'Номер родового сертификата',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'BirthCertificate_setDate',
				'label' => 'Дата выдачи родового сертификата',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'Evn_id',
				'label' => 'Случай, из которого создан сертификат',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_oid',
				'label' => 'МО родоразрешения',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'deleteBirthCertificate' => array(
			array(
				'field' => 'BirthCertificate_id',
				'label' => 'Идентификатор родового сертификата',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'doPersonPregnancyOut' => array(
			array(
				'field' => 'PersonRegister_id',
				'label' => 'Идентификатор записи в регистре беременных',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PersonRegister_disDate',
				'label' => 'Дата исключения из регистра',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'PersonRegisterOutCause_id',
				'label' => 'Идентификатор причины исключения из регистра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_did',
				'label' => 'Идентификатор МО, в котором изключили из регистра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_did',
				'label' => 'Идентификатор врача, который исключил из регистра',
				'rules' => '',
				'type' => 'id'
			),
		),
		'cancelPersonPregnancyOut' => array(
			array(
				'field' => 'PersonRegister_id',
				'label' => 'Идентификатор записи в регистре беременных',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'getPersonRegisterInfo' => array(
			array(
				'field' => 'PersonRegister_id',
				'label' => 'Идентификатор записи в регистре беременных',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => '',
				'type' => 'id'
			),
		),
		'savelinkEco' => array(
			array(
				'field' => 'PersonPregnancy_id',
				'label' => 'Идентификатор анкеты регистра беременных',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonRegisterEco_id',
				'label' => 'Идентификатор анкеты регистра ЭКО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'IsLink',
				'label' => 'Наличие связи регситра беременных и ЭКО',
				'rules' => '',
				'type' => 'string'
 			)
		),
		'getAnketaForScreen' => array(
			array(
				'field' => 'PersonRegister_id',
				'label' => 'Идентификатор регистра',
				'rules' => '',
				'type' => 'id'
			)
		),
		'deleteDifferentLpu' => array(
			array(
				'field' => 'LpuDifferent_id',
				'label' => 'Идентфикикатор',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'getDifferentLpu' => array(),
		'getEcoLpuId' => array()
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Получение списка
	 */
	function loadList($data) {
		$filters = "";
		$fields = "";
		$join = "";

		if (!empty($data['Person_SurName'])) {
			$filters .= " and ps.Person_SurName ilike :Person_SurName || '%'";
		}
		if (!empty($data['Person_FirName'])) {
			$filters .= " and ps.Person_FirName ilike :Person_FirName || '%'";
		}
		if (!empty($data['Person_SecName'])) {
			$filters .= " and ps.Person_SecName ilike :Person_SecName || '%'";
		}
		if (!empty($data['RiskType_id'])) {
			$filters .= " and PR.RiskType_id = :RiskType_id";
		}
		if (!empty($data['RiskType_bid'])) {
			$filters .= " and PP.RiskType_bid = :RiskType_bid";
		}
		if (isset($data['Person_AgeFrom']) && $data['Person_AgeFrom'] >= 0) {
			$filters .= " and dbo.Age2(ps.Person_BirthDay, CAST(tzgetdate() as date)) >= :Person_AgeFrom";
		}
		if (isset($data['Person_AgeTo']) && $data['Person_AgeTo'] >= 0) {
			$filters .= " and dbo.Age2(ps.Person_BirthDay, CAST(tzgetdate() as date)) <= :Person_AgeTo";
		}
		if (!empty($data['AttachLpu_id'])) {
			$filters .= " and pcard.Lpu_id = :AttachLpu_id";
		}
		if (!empty($data['Lpu_iid'])) {
			if ($data['Lpu_iid'] == -1) {
				$filters .= " and PR.Lpu_iid is null";
			} else {
				$filters .= " and PR.Lpu_iid = :Lpu_iid";
			}
		}
		if (!empty($data['MedPersonal_iid'])) {
			$filters .= " and PR.MedPersonal_iid = :MedPersonal_iid";
		}
		if (!empty($data['Diag_Code_From'])) {
			$filters .= " and D.Diag_Code >= :Diag_Code_From";
		}
		if (!empty($data['Diag_Code_To'])) {
			$filters .= " and D.Diag_Code <= :Diag_Code_To";
		}
		if (!empty($data['Diag_sCode_From']) || !empty($data['Diag_sCode_To'])) {
			$SopDiagFilters = "";
			if (!empty($data['Diag_sCode_From'])) {
				$SopDiagFilters .= " and D.Diag_Code >= :Diag_sCode_From";
			}
			if (!empty($data['Diag_sCode_To'])) {
				$SopDiagFilters .= " and D.Diag_Code <= :Diag_sCode_To";
			}
			$filters .= " and exists(
				select *
				from v_PregnancyScreenSopDiag PSSD
				inner join v_PregnancyScreen Screen on Screen.PregnancyScreen_id = PSSD.PregnancyScreen_id
				inner join v_Diag D on D.Diag_id = PSSD.Diag_id
				where Screen.PersonRegister_id = PR.PersonRegister_id
				{$SopDiagFilters}
			)";
		}
		if (isset($data['PersonPregnancy_PeriodFrom']) && $data['PersonPregnancy_PeriodFrom'] >= 0) {
			$filters .= " and Period.Value >= :PersonPregnancy_PeriodFrom";
		}
		if (isset($data['PersonPregnancy_PeriodTo']) && $data['PersonPregnancy_PeriodTo'] >= 0) {
			$filters .= " and Period.Value <= :PersonPregnancy_PeriodTo";
		}
		if (!empty($data['PersonRegister_disDateRange'][0]) && !empty($data['PersonRegister_disDateRange'][1])) {
			$filters .= " and (PR.PersonRegister_disDate between :PersonRegister_disDateBeg and :PersonRegister_disDateEnd or BSS.BirthSpecStac_OutcomDT between :PersonRegister_disDateBeg and :PersonRegister_disDateEnd)";
			$data['PersonRegister_disDateBeg'] = $data['PersonRegister_disDateRange'][0];
			$data['PersonRegister_disDateEnd'] = $data['PersonRegister_disDateRange'][1];
		}
		if (isset($data['Type']) && $data['Type'] == 'all') {
			$filters .= " and BSS.BirthSpecStac_id is null and PR.PregnancyResult_id is null";
			if (!empty($data['PersonRegister_setDateRange'][0]) && !empty($data['PersonRegister_setDateRange'][1])) {
				$filters .= " and PR.PersonRegister_setDate <= :PersonRegister_setDateEnd";
				//gaf #115711 04122017
				$filters .= " and (PR.PersonRegister_setDate is null or PR.PersonRegister_setDate >= :PersonRegister_setDateBeg)";
				//$filters .= " and (PR.PersonRegister_disDate is null or PR.PersonRegister_disDate >= :PersonRegister_setDateBeg)";
				$data['PersonRegister_setDateBeg'] = $data['PersonRegister_setDateRange'][0];
				$data['PersonRegister_setDateEnd'] = $data['PersonRegister_setDateRange'][1];
			} else {
				$filters .= " and PR.PersonRegister_setDate <= dbo.tzgetdate()";
				$filters .= " and (PR.PersonRegister_disDate is null or PR.PersonRegister_disDate >= dbo.tzgetdate())";
			}
		} else {
			if (!empty($data['PersonRegister_setDateRange'][0]) && !empty($data['PersonRegister_setDateRange'][1])) {
				$filters .= " and PR.PersonRegister_setDate between :PersonRegister_setDateBeg and :PersonRegister_setDateEnd";
				$data['PersonRegister_setDateBeg'] = $data['PersonRegister_setDateRange'][0];
				$data['PersonRegister_setDateEnd'] = $data['PersonRegister_setDateRange'][1];
			}
		}
		if (!empty($data['RiskType_id'])) {
			$filters .= " and PR.RiskType_id = :RiskType_id";
		}
		if (!empty($data['PregnancyResult_id'])) {
			$filters .= " and Result.PregnancyResult_id = :PregnancyResult_id";
		}
		if (!empty($data['Type']) && $data['Type'] == 'new') {
			$filters .= " and PR.PersonRegisterOutCause_id is null";
		}
		if (!empty($data['Type']) && $data['Type'] == 'out') {
			$filters .= " and PR.PersonRegisterOutCause_id is not null";
		}
		if (!empty($data['PersonRegister_Code'])) {
			$filters .= " and PR.PersonRegister_Code = :PersonRegister_Code";
		}
		if (!empty($data['RiskType_did'])) {
			if ($data['RiskType_did'] == 1){
				$filters .= " and (PP.RiskType_did = :RiskType_did or PP.RiskType_did is null)";
			}else{
				$filters .= " and PP.RiskType_did = :RiskType_did";
			}
		}
		if (getRegionNick() == 'khak' && !empty($data['PregnancyType_id']) && $data['PregnancyType_id'] != 100) {
			$filters .= " and BSS.PregnancyType_id = :PregnancyType_id";
		}

		$response = $this->getFilterObstetricComplication($data['ObstetricComplication_id']);
		$join .= $response['join'];
		$filters .= $response['filters'];

		$order_by = "
				PR.PersonRegister_setDate desc,
				PR.PersonRegister_disDate desc
		";
		if (isset($data['Type']) && $data['Type'] == 'out') {
			$order_by = "
				PR.PersonRegister_disDate desc,
				PR.PersonRegister_setDate desc
			";
		}

		if (getRegionNick() == 'perm') {
			$fields .= ",KDO.cnt as \"PersonPregnancy_CountKDO\"";
			if (!empty($data['PersonPregnancy_IsKDO'])) {
				if ($data['PersonPregnancy_IsKDO'] == 2) {
					$filters .= " and KDO.cnt > 0";
				} else {
					$filters .= " and KDO.cnt = 0";
				}
			}
			$join .= "
				left join lateral (
					select
						count(*) as cnt
					from
						v_EvnVizitPL EVPL
						inner join v_LpuSection LS on LS.LpuSection_id = EVPL.LpuSection_id
						inner join v_LpuBuilding LB on LB.LpuBuilding_id = LS.LpuBuilding_id
						inner join v_Lpu L on L.Lpu_id = LB.Lpu_id
					where 
						EVPL.Person_id = PR.Person_id 
						and L.Lpu_Nick = 'ПЕРМЬ ККБ'
						and LB.LpuBuilding_Name = 'КПЦ подразделение амбулаторное'
						and LS.LpuSection_Code in ('81','84','85','86','87','82','83')
					limit 1
				) KDO on true
			";
		}

		$query = "
			select
				-- select
				PR.PersonRegister_id as \"PersonRegister_id\",
				PR.PersonRegister_Code as \"PersonRegister_Code\",
				to_char(PR.PersonRegister_setDate, 'dd.mm.yyyy') as \"PersonRegister_setDate\",
				OutCause.PersonRegisterOutCause_id as \"PersonRegisterOutCause_id\",
				OutCause.PersonRegisterOutCause_Code as \"PersonRegisterOutCause_Code\",
				OutCause.PersonRegisterOutCause_SysNick as \"PersonRegisterOutCause_SysNick\",
				OutCause.PersonRegisterOutCause_Name as \"PersonRegisterOutCause_Name\",
				PP.PersonPregnancy_id as \"PersonPregnancy_id\",
				PS.Person_id as \"Person_id\",
				coalesce(rtrim(PS.Person_SurName),'')
					|| coalesce(' '
					|| rtrim(PS.Person_FirName),'')
					|| coalesce(' '
					|| rtrim(PS.Person_SecName),''
				) as \"Person_Fio\",
				to_char(PS.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\",
				dbo.Age2(PS.Person_BirthDay, dbo.tzgetdate()) as \"Person_Age\",
				coalesce(PAddress.Address_Nick, PAddress.Address_Address) as \"Person_PAddress\",
				coalesce(PP.PersonPregnancy_RiskDPP, 0) + coalesce(LastScreen.PregnancyScreen_RiskPerPat, 0) as \"PersonPregnancy_ObRisk\",
				RT.RiskType_id as \"RiskType_id\",
				RT.RiskType_Name as \"RiskType_Name\",
				PR.Lpu_iid as \"Lpu_iid\",
				L.Lpu_Nick as \"Lpu_Nick\",
				LpuAttach.Lpu_id as \"LpuAttach_id\",
				LpuAttach.Lpu_Nick as \"LpuAttach_Nick\",
				LpuHosp.Lpu_id as \"LpuHosp_id\",
				LpuHosp.Lpu_Nick as \"LpuHosp_Nick\",
				PR.MedPersonal_iid as \"MedPersonal_iid\",
				MP.MedPersonal_Fio as \"MedPersonal_Fio\",
				D.Diag_id as \"Diag_id\",
				D.Diag_FullName as \"Diag_FullName\",
				Result.PregnancyResult_id as \"PregnancyResult_id\",
				Result.PregnancyResult_Name as \"PregnancyResult_Name\",
				Period.Value as \"PersonPregnancy_Period\",
				dbo.GetPregnancyPRRisk(PR.PersonRegister_id,1) as \"PRRiskFactor\",
				(case when PP.RiskType_did = 1 then 'низкий' when PP.RiskType_did = 2 then 'высокий' else 'низкий' end) as \"RiskPR\",
				(case when PP.RiskType_bid = 1 then 'низкий' when PP.RiskType_bid = 2 then 'средний' when PP.RiskType_bid = 3 then 'высокий' else 'низкий' end) as \"Risk572N\",
				dbo.GetPregnancy572NRisk(PR.PersonRegister_id,1) as \"RiskFactor572N\",
				case when exists(
					select * 
					from v_PersonQuarantine PQ
					where PQ.Person_id = PS.Person_id 
					and PQ.PersonQuarantine_endDT is null
				) then 2 else 1 end as \"PersonQuarantine_IsOn\"
				{$fields}
				-- end select
			from
				-- from
				v_PersonRegister PR
				inner join v_PersonRegisterType PRT on PRT.PersonRegisterType_id = PR.PersonRegisterType_id
					and PRT.PersonRegisterType_SysNick ilike 'pregnancy'
				left join v_PersonPregnancy PP on PP.PersonRegister_id = PR.PersonRegister_id
				left join v_PersonRegisterOutCause OutCause on OutCause.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
				left join v_PersonState PS on PS.Person_id = PR.Person_id
				left join v_Address PAddress on PAddress.Address_id = PS.PAddress_id
				left join v_RiskType RT on RT.RiskType_id = PR.RiskType_id
				left join v_PersonCard PC on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
				left join v_Lpu_all LpuAttach on LpuAttach.Lpu_id = PC.Lpu_id
				left join v_Lpu_all L on L.Lpu_id = PR.Lpu_iid
				left join lateral (
					select
						MedPersonal_id,
						Person_Fio as MedPersonal_Fio
					from v_MedPersonal
					where MedPersonal_id = PR.MedPersonal_iid
						and Lpu_id = coalesce(L.Lpu_id, Lpu_id)
					limit 1
				) MP on true
				left join v_BirthCertificate BC on BC.PersonRegister_id = PR.PersonRegister_id
				left join v_EvnVizitPL EVPL on EVPL.EvnVizitPL_id = PP.Evn_id
				left join v_BirthSpecStac BSS on BSS.PersonRegister_id = PR.PersonRegister_id
				left join v_EvnSection ES on ES.EvnSection_id = BSS.EvnSection_id
				left join v_EvnVizitPL rEVPL on rEVPL.EvnVizitPL_id = BSS.Evn_id
				left join v_PregnancyResult Result on Result.PregnancyResult_id = coalesce(PR.PregnancyResult_id,BSS.PregnancyResult_id)
				left join lateral (
					select Screen.*
					from v_PregnancyScreen Screen
					where Screen.PersonRegister_id = PR.PersonRegister_id
					order by Screen.PregnancyScreen_setDT desc
					limit 1
				) LastScreen on true
				left join lateral (
					select PQ.PregnancyQuestion_ValuesStr as Lpu_id
					from v_PregnancyQuestion PQ
						inner join v_QuestionType QT on QT.QuestionType_id = PQ.QuestionType_id
					where PQ.PregnancyScreen_id = LastScreen.PregnancyScreen_id
						and QT.QuestionType_Code = 406
					limit 1
				) LastScreenDir on true
				left join lateral (
					select PQ.PregnancyQuestion_AnswerInt as Value
					from v_PregnancyQuestion PQ
						inner join v_QuestionType QT on QT.QuestionType_id = PQ.QuestionType_id
					where PQ.PregnancyScreen_id = LastScreen.PregnancyScreen_id
						and QT.QuestionType_Code in (358,359,362,363)
					order by QT.QuestionType_Code
					limit 1
				) LastScreenPeriod on true
				left join lateral(
					select coalesce(
						BSS.BirthSpecStac_OutcomPeriod,
						LastScreenPeriod.Value + datediff('week', LastScreen.PregnancyScreen_setDT, dbo.tzgetdate()),
						PP.PersonPregnancy_Period + datediff('week', PP.PersonPregnancy_setDT, dbo.tzgetdate())
					) as Value
					limit 1
				) Period on true
				left join v_Lpu_all LpuHosp on LpuHosp.Lpu_id = coalesce(ES.Lpu_id, LastScreenDir.Lpu_id, BC.Lpu_id)
				left join v_Diag D on D.Diag_id = coalesce(ES.Diag_id,rEVPL.Diag_id,LastScreen.Diag_id,PP.Diag_id,EVPL.Diag_id)
				{$join}
				-- end from
			where
				-- where
				(1=1) {$filters}
				-- end where
			order by
				-- order by
				{$order_by}
				-- end order by
		";

		if (!empty($data['Type']) && $data['Type'] == 'out') {
			$query = "
				select
					-- select
					PR.PersonRegister_id as \"PersonRegister_id\",
					PR.PersonRegister_Code as \"PersonRegister_Code\",
					to_char(PR.PersonRegister_setDate, 'dd.mm.yyyy') as \"PersonRegister_setDate\",
					OutCause.PersonRegisterOutCause_id as \"PersonRegisterOutCause_id\",
					OutCause.PersonRegisterOutCause_Code as \"PersonRegisterOutCause_Code\",
					OutCause.PersonRegisterOutCause_SysNick as \"PersonRegisterOutCause_SysNick\",
					OutCause.PersonRegisterOutCause_Name as \"PersonRegisterOutCause_Name\",
					PP.PersonPregnancy_id as \"PersonPregnancy_id\",
					PP.PersonDisp_id as \"PersonDisp_id\",
					PS.Person_id as \"Person_id\",
					coalesce(rtrim(PS.Person_SurName),'')
						|| coalesce(' '
						|| rtrim(PS.Person_FirName),'')
						|| coalesce(' '
						|| rtrim(PS.Person_SecName),''
					) as \"Person_Fio\",
					to_char(PS.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\",
					dbo.Age2(PS.Person_BirthDay, dbo.tzgetdate()) as \"Person_Age\",
					coalesce(PAddress.Address_Nick, PAddress.Address_Address) as \"Person_PAddress\",
					coalesce(PP.PersonPregnancy_RiskDPP, 0) + coalesce(LastScreen.PregnancyScreen_RiskPerPat, 0) as \"PersonPregnancy_ObRisk\",
					RT.RiskType_id as \"RiskType_id\",
					RT.RiskType_Name as \"RiskType_Name\",
					PR.Lpu_iid as \"Lpu_iid\",
					L.Lpu_Nick as \"Lpu_Nick\",
					LpuAttach.Lpu_id as \"LpuAttach_id\",
					LpuAttach.Lpu_Nick as \"LpuAttach_Nick\",
					LpuHosp.Lpu_id as \"LpuHosp_id\",
					LpuHosp.Lpu_Nick as \"LpuHosp_Nick\",
					PR.MedPersonal_iid as \"MedPersonal_iid\",
					MP.MedPersonal_Fio as \"MedPersonal_Fio\",
					D.Diag_id as \"Diag_id\",
					D.Diag_FullName as \"Diag_FullName\",
					Result.PregnancyResult_id as \"PregnancyResult_id\",
					Result.PregnancyResult_Name as \"PregnancyResult_Name\",
					Period.Value as \"PersonPregnancy_Period\",
					dbo.GetPregnancyPRRisk(PR.PersonRegister_id, 1) as \"PRRiskFactor\",
					(case when PP.RiskType_did = 1
						then 'низкий' when PP.RiskType_did = 2
							then 'высокий'
							else 'низкий'
					end) as \"RiskPR\",
					(case when PP.RiskType_bid = 1
						then 'низкий' when PP.RiskType_bid = 2
							then 'средний' when PP.RiskType_bid = 3
								then 'высокий'
								else 'низкий'
					end) as \"Risk572N\",
					dbo.GetPregnancy572NRisk(PR.PersonRegister_id,1) as \"RiskFactor572N\"
					{$fields}
					-- end select
				from
					-- from
					v_PersonRegister PR
					inner join v_PersonRegisterType PRT on PRT.PersonRegisterType_id = PR.PersonRegisterType_id
						and PRT.PersonRegisterType_SysNick ilike 'pregnancy'
					left join v_PersonPregnancy PP on PP.PersonRegister_id = PR.PersonRegister_id
					left join v_PersonRegisterOutCause OutCause on OutCause.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
					left join v_PersonState PS on PS.Person_id = PR.Person_id
					left join v_Address PAddress on PAddress.Address_id = PS.PAddress_id
					left join v_RiskType RT on RT.RiskType_id = PR.RiskType_id
					left join v_PersonCard PC on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
					left join v_Lpu_all LpuAttach on LpuAttach.Lpu_id = PC.Lpu_id
					left join v_Lpu_all L on L.Lpu_id = PR.Lpu_iid
					left join lateral (
						select
							MedPersonal_id,
							Person_Fio as MedPersonal_Fio
						from v_MedPersonal
						where MedPersonal_id = PR.MedPersonal_iid and Lpu_id = coalesce(L.Lpu_id, Lpu_id)
						limit 1
					) MP on true
					left join v_BirthCertificate BC on BC.PersonRegister_id = PR.PersonRegister_id
					left join v_EvnVizitPL EVPL on EVPL.EvnVizitPL_id = PP.Evn_id
					left join v_BirthSpecStac BSS on BSS.PersonRegister_id = PR.PersonRegister_id
					left join v_EvnSection ES on ES.EvnSection_id = BSS.EvnSection_id
					left join v_EvnVizitPL rEVPL on rEVPL.EvnVizitPL_id = BSS.Evn_id
					left join v_PregnancyResult Result on Result.PregnancyResult_id = coalesce(PR.PregnancyResult_id,BSS.PregnancyResult_id)
					left join lateral (
						select Screen.*
						from v_PregnancyScreen Screen
						where Screen.PersonRegister_id = PR.PersonRegister_id
						order by Screen.PregnancyScreen_setDT desc
						limit 1
					) LastScreen on true
					left join lateral (
						select PQ.PregnancyQuestion_ValuesStr as Lpu_id
						from v_PregnancyQuestion PQ
						inner join v_QuestionType QT on QT.QuestionType_id = PQ.QuestionType_id
						where PQ.PregnancyScreen_id = LastScreen.PregnancyScreen_id and QT.QuestionType_Code = 406
						limit 1
					) LastScreenDir on true
					left join lateral (
						select PQ.PregnancyQuestion_AnswerInt as Value
						from v_PregnancyQuestion PQ
						inner join v_QuestionType QT on QT.QuestionType_id = PQ.QuestionType_id
						where PQ.PregnancyScreen_id = LastScreen.PregnancyScreen_id
						and QT.QuestionType_Code in (358,359,362,363)
						order by QT.QuestionType_Code
						limit 1
					) LastScreenPeriod on true
					left join lateral (
						select coalesce(
							BSS.BirthSpecStac_OutcomPeriod,
							LastScreenPeriod.Value + datediff('week', LastScreen.PregnancyScreen_setDT, dbo.tzgetdate()),
							PP.PersonPregnancy_Period + datediff('week', PP.PersonPregnancy_setDT, dbo.tzgetdate())
						) as Value
					) Period on true
					left join v_Lpu_all LpuHosp on LpuHosp.Lpu_id = coalesce(ES.Lpu_id, LastScreenDir.Lpu_id, BC.Lpu_id)
					left join v_Diag D on D.Diag_id = coalesce(ES.Diag_id,rEVPL.Diag_id,LastScreen.Diag_id,PP.Diag_id,EVPL.Diag_id)
					{$join}
					-- end from
				where
					-- where
					(1=1) {$filters}
					-- end where
				order by
					-- order by
					{$order_by}
					-- end order by
			";
		}

		//echo getDebugSQL($query, $data);exit;

		$result = $this->queryResult(getLimitSQLPH($query, $data['start'], $data['limit']), $data);
		$get_count_result = $this->queryResult(getCountSQLPH($query), $data);

		if (!is_array($result) || !is_array($get_count_result)) {
			return false;
		}

		$PersonRegister_ids = array();
		foreach($result as $item) {
			if (!empty($item['PersonRegister_id'])) {
				$PersonRegister_ids[] = $item['PersonRegister_id'];
			}
		}
		
		$ScreenListByPersonRegister = array();
		
		if (count($PersonRegister_ids) > 0) {
			for ($i = 0;;$i++) {
				if ($i * 500 > count($PersonRegister_ids)) break;
				$ids = array_slice($PersonRegister_ids, $i * 500, 500);
				$ids = implode(',', $ids);
				$query = "
					with Answers as (
						select
							PQ.PregnancyScreen_id,
							PQ.PersonRegister_id,
							PQ.PregnancyQuestion_AnswerInt,
							QT.QuestionType_SysNick
						from v_PregnancyQuestion PQ
						inner join v_QuestionType QT on QT.QuestionType_id = PQ.QuestionType_id
						where PQ.PersonRegister_id in ({$ids}) and QT.DispClass_id = 16
						and QT.QuestionType_SysNick in ('amenordate','embriondate','uzidate','fmovedate')
					)
					select
						PS.PregnancyScreen_id as \"PregnancyScreen_id\",
						PS.PersonRegister_id as \"PersonRegister_id\",
						to_char(PS.PregnancyScreen_setDT, '{$this->dateTimeForm104}') as \"PregnancyScreen_setDate\",
						coalesce(SA.amenordate, SA.embriondate, SA.uzidate, SA.fmovedate) as \"PregnancyScreen_Period\"
					from
						v_PregnancyScreen PS
						left join lateral (
							select
							(
								select PregnancyQuestion_AnswerInt
								from Answers where PregnancyScreen_id = PS.PregnancyScreen_id
								and QuestionType_SysNick = 'amenordate'
								limit 1
							) as amenordate,
							(
								select PregnancyQuestion_AnswerInt
								from Answers where PregnancyScreen_id = PS.PregnancyScreen_id
								and QuestionType_SysNick = 'embriondate'
								limit 1
							) as embriondate,
							(
								select PregnancyQuestion_AnswerInt
								from Answers where PregnancyScreen_id = PS.PregnancyScreen_id
								and QuestionType_SysNick = 'uzidate'
								limit 1
							) as uzidate,
							(
								select PregnancyQuestion_AnswerInt
								from Answers where PregnancyScreen_id = PS.PregnancyScreen_id
								and QuestionType_SysNick = 'fmovedate'
								limit 1
							) as fmovedate
						) SA on true
					where
						PS.PersonRegister_id in ({$ids})
					order by
						PS.PregnancyScreen_setDT
				";
				$resp = $this->queryResult($query);
				if (!is_array($resp)) {
					return false;
				}
				
				foreach ($resp as &$screen) {
					$period = !empty($screen['PregnancyScreen_Period']) ? $screen['PregnancyScreen_Period'] : '*';
					$screen['text'] = "Скрининг {$screen['PregnancyScreen_setDate']}, {$period} нед.";
					$ScreenListByPersonRegister[$screen['PersonRegister_id']][] = $screen;
				}
			}

			foreach($result as &$item) {
				if (isset($ScreenListByPersonRegister[$item['PersonRegister_id']])) {
					$item['ScreenData'] = json_encode($ScreenListByPersonRegister[$item['PersonRegister_id']]);
				} else {
					$item['ScreenData'] = null;
				}
				$item['RiskPR'] = ($item['PRRiskFactor'] != '' ? "<img border=0 valign=center ext:qwidth=\"500\" ext:qtip=\"" . nl2br(htmlspecialchars($item['PRRiskFactor'])) . " " .  "\" src=\"/img/icons/info16.png\" style=\"cursor: pointer;\"> " : '').$item['RiskPR'];
				$item['Risk572N'] = ($item['RiskFactor572N'] != '' ? "<img border=0 valign=center ext:qwidth=\"500\" ext:qtip=\"" . nl2br(htmlspecialchars($item['RiskFactor572N'])) . " " .  "\" src=\"/img/icons/info16.png\" style=\"cursor: pointer;\"> " : '').$item['Risk572N'];
			}
		}

		$response = array(
			'data' => $result,
			'totalCount' => $get_count_result[0]['cnt'],
		);

		return $response;
	}

	/**
	 * Получение списка
	 */
	function loadListRecommRouter($data) {
		$filters = "";
		$fields = "";
		$join = "";

		//echo '<pre>'.print_r($data, 1).'</pre>';
		
		
		if (!empty($data['Person_SurName'])) {
			$filters .= " and ps.Person_SurName ilike :Person_SurName || '%'";
		}
		if (!empty($data['Person_FirName'])) {
			$filters .= " and ps.Person_FirName ilike :Person_FirName || '%'";
		}
		if (!empty($data['Person_SecName'])) {
			$filters .= " and ps.Person_SecName ilike :Person_SecName || '%'";
		}
		//if (!empty($data['RiskType_id'])) {
			//$filters .= " and PR.RiskType_id = :RiskType_id";
		//}
		if (isset($data['Person_AgeFrom']) && $data['Person_AgeFrom'] >= 0) {
			$filters .= " and dbo.Age2(ps.Person_BirthDay, CAST(:date as date)) >= :Person_AgeFrom";
		}
		if (isset($data['Person_AgeTo']) && $data['Person_AgeTo'] >= 0) {
			$filters .= " and dbo.Age2(ps.Person_BirthDay, CAST(:date as date)) <= :Person_AgeTo";
		}
		if (!empty($data['AttachLpu_id'])) {
			$filters .= " and pcard.Lpu_id = :AttachLpu_id";
		}
		if (!empty($data['Lpu_iid'])) {
			if ($data['Lpu_iid'] == -1) {
				$filters .= " and PR.Lpu_iid is null";
			} else {
				$filters .= " and PR.Lpu_iid = :Lpu_iid";
			}
		}

		$response = $this->getFilterObstetricComplication($data['ObstetricComplication_id']);
		$join .= $response['join'];
		$filters .= $response['filters'];

		if (!empty($data['MedPersonal_iid'])) {
			$MedPersonal_iids = explode(',', $data['MedPersonal_iid']);

			$filters .= " and PR.MedPersonal_iid in (" . implode(',', $MedPersonal_iids) . ")";
		}

		if (!empty($data['Diag_Code_From'])) {
			$filters .= " and D.Diag_Code >= :Diag_Code_From";
		}
		if (!empty($data['Diag_Code_To'])) {
			$filters .= " and D.Diag_Code <= :Diag_Code_To";
		}
		if (!empty($data['Diag_sCode_From']) || !empty($data['Diag_sCode_To'])) {
			$SopDiagFilters = "";
			if (!empty($data['Diag_sCode_From'])) {
				$SopDiagFilters .= " and D.Diag_Code >= :Diag_sCode_From";
			}
			if (!empty($data['Diag_sCode_To'])) {
				$SopDiagFilters .= " and D.Diag_Code <= :Diag_sCode_To";
			}
			$filters .= " and exists(
				select 
				    *
				from 
				    v_PregnancyScreenSopDiag PSSD
                    inner join v_PregnancyScreen Screen on Screen.PregnancyScreen_id = PSSD.PregnancyScreen_id
                    inner join v_Diag D on D.Diag_id = PSSD.Diag_id
				where 
				    Screen.PersonRegister_id = PR.PersonRegister_id
				{$SopDiagFilters}
			)";
		}
		if (isset($data['PersonPregnancy_PeriodFrom']) && $data['PersonPregnancy_PeriodFrom'] >= 0) {
			$filters .= " and Period.Value >= :PersonPregnancy_PeriodFrom";
		}
		if (isset($data['PersonPregnancy_PeriodTo']) && $data['PersonPregnancy_PeriodTo'] >= 0) {
			$filters .= " and Period.Value <= :PersonPregnancy_PeriodTo";
		}
		if (!empty($data['PersonRegister_disDateRange'][0]) && !empty($data['PersonRegister_disDateRange'][1])) {
			$filters .= " and (PR.PersonRegister_disDate between :PersonRegister_disDateBeg and :PersonRegister_disDateEnd or BSS.BirthSpecStac_OutcomDT between :PersonRegister_disDateBeg and :PersonRegister_disDateEnd)";
			$data['PersonRegister_disDateBeg'] = $data['PersonRegister_disDateRange'][0];
			$data['PersonRegister_disDateEnd'] = $data['PersonRegister_disDateRange'][1];
		}
		if (isset($data['Type']) && $data['Type'] == 'all') {
			$filters .= " and BSS.BirthSpecStac_id is null and PR.PregnancyResult_id is null";
			if (!empty($data['PersonRegister_setDateRange'][0]) && !empty($data['PersonRegister_setDateRange'][1])) {
				$filters .= " and PR.PersonRegister_setDate <= :PersonRegister_setDateEnd";
				$filters .= " and (PR.PersonRegister_setDate is null or PR.PersonRegister_setDate >= :PersonRegister_setDateBeg)";
				//$filters .= " and (PR.PersonRegister_disDate is null or PR.PersonRegister_disDate >= :PersonRegister_setDateBeg)";
				$data['PersonRegister_setDateBeg'] = $data['PersonRegister_setDateRange'][0];
				$data['PersonRegister_setDateEnd'] = $data['PersonRegister_setDateRange'][1];
			} else {
				$filters .= " and PR.PersonRegister_setDate <= CAST(:date as date)";
				$filters .= " and (PR.PersonRegister_disDate is null or PR.PersonRegister_disDate >= CAST(:date as date))";
			}
		} else {
			if (!empty($data['PersonRegister_setDateRange'][0]) && !empty($data['PersonRegister_setDateRange'][1])) {
				$filters .= " and PR.PersonRegister_setDate between :PersonRegister_setDateBeg and :PersonRegister_setDateEnd";
				$data['PersonRegister_setDateBeg'] = $data['PersonRegister_setDateRange'][0];
				$data['PersonRegister_setDateEnd'] = $data['PersonRegister_setDateRange'][1];
			}
		}
		if (!empty($data['RiskType_id'])) {
			$filters .= " and PR.RiskType_aid = :RiskType_id";
		}
		if (!empty($data['RiskType_gid'])) {
			$filters .= " and PR.RiskType_id = :RiskType_gid";
		}
		if (!empty($data['PregnancyResult_id'])) {
			$filters .= " and Result.PregnancyResult_id = :PregnancyResult_id";
		}
		if (!empty($data['Type']) && $data['Type'] == 'new') {
			$filters .= " and PR.PersonRegisterOutCause_id is null";
		}
		if (!empty($data['Type']) && $data['Type'] == 'out') {
			$filters .= " and PR.PersonRegisterOutCause_id is not null";
		}
		if (!empty($data['PersonRegister_Code'])) {
			$filters .= " and PR.PersonRegister_Code = :PersonRegister_Code";
		}
		
		if (!empty($data['MesLevel_id'])) {
			$filters .= " and PR.MesLevel_id = :MesLevel_id";
		}
		//echo $data['ADKS'];
		if (!empty($data['ADKS']) && $data['ADKS'] == 2) {
			$filters .= " and PR.MesLevel_id = 8";
		}		
		if (!empty($data['YesNo_id'])) {
			if ($data['YesNo_id'] == 2){
				$filters .= " and PR.PersonRegisterOutCause_id is null";
			}else{
				$filters .= " and PR.PersonRegisterOutCause_id is not null";
			}
		}

		$order_by = "
				PR.PersonRegister_setDate desc,
				PR.PersonRegister_disDate desc
		";
		if (isset($data['Type']) && $data['Type'] == 'out') {
			$order_by = "
				PR.PersonRegister_disDate desc,
				PR.PersonRegister_setDate desc
		";
		} if (isset($data['SignalInfo']) && $data['SignalInfo'] == 1) {
			//if (!empty($data['Trimester_id'])) {

			$fields .= ", 
				to_char(EvnVizitPL_Date.EvnVizitPL_setDate, '{$this->dateTimeForm104}') as \"EvnVizitPL_setDate\",
				DiffDate.DiffDate as \"DiffDate\",
				(case when Period.Value between 1 and 12  then '1 триместр'
				when Period.Value between 13 and 27 then '2 триместр'
				when Period.Value between 28 and 43 then '3 триместр' end) as \"Trimester\",
				EPS_LPU.Lpu_Nick as \"NickHospital\"";
			$join .= "--дата последнего посещения
				left join lateral(
                    select 
                        EvnVizitPL_setDate
                    from 
                        v_EvnVizitPL DT
                    where 
                        DT.Person_id = PR.Person_id 
                    and 
                        DT.Lpu_id = PR.Lpu_iid -- смотрим посещения по лпу учета
                    order by DT.EvnVizitPL_setDate DESC
                    limit 1
				) EvnVizitPL_Date on true
				--разница между последней датой и сегодняшней
				left join lateral (
				    SELECT DATEDIFF('day', EvnVizitPL_Date.EvnVizitPL_setDate, GETDATE()) AS DiffDate
				) DiffDate on true
				left join lateral (
					select EPS_LPU.Lpu_Nick
					from v_EvnPS EPS 
					left join v_Lpu_all EPS_LPU on EPS_LPU.Lpu_id = EPS.Lpu_id
					where EPS.Person_id = PR.Person_id and EPS.EvnPS_disDate is null
					order by EPS.EvnPS_setDate desc
					limit 1
				) EPS_LPU on true"
			;
			$filters .= " and ((case when ((Period.Value between 1 and 12) and DiffDate.DiffDate>35) then 1 end=1) 
				 or (case when ((Period.Value between 13 and 27) and DiffDate.DiffDate>35) then 1 end=1)
				 or (case when ((Period.Value between 28 and 43) and DiffDate.DiffDate>22) then 1 end=1))";
			
			if ($data['Trimester'] == 'Trimester1') {
				$filters .= "and Period.Value between 1 and 12";
			} else if ($data['Trimester'] == 'Trimester2') {
				$filters .= "and Period.Value between 13 and 27";
			} else if ($data['Trimester'] == 'Trimester3') {
				$filters .= "and Period.Value between 28 and 43";
			} else {
				$filters .= "";
			}
		}


		$select = "
		PR.PersonRegister_id as \"PersonRegister_id\",
				PR.PersonRegister_Code as \"PersonRegister_Code\",
				to_char(PR.PersonRegister_setDate, '{$this->dateTimeForm104}') as \"PersonRegister_setDate\",
				OutCause.PersonRegisterOutCause_id as \"PersonRegisterOutCause_id\",
				OutCause.PersonRegisterOutCause_Code as \"PersonRegisterOutCause_Code\",
				OutCause.PersonRegisterOutCause_SysNick as \"PersonRegisterOutCause_SysNick\",
				OutCause.PersonRegisterOutCause_Name as \"PersonRegisterOutCause_Name\",
				PP.PersonPregnancy_id as \"PersonPregnancy_id\",
				PS.Person_id as \"Person_id\",
				coalesce (rtrim(PS.Person_SurName),'') || coalesce(' '||rtrim(PS.Person_FirName),'') || coalesce(' '||rtrim(PS.Person_SecName),'') as \"Person_Fio\",
				to_char(PS.Person_BirthDay, '{$this->dateTimeForm104}') as \"Person_BirthDay\",
				dbo.Age2(PS.Person_BirthDay, CAST(:date as date)) as \"Person_Age\",
				coalesce (PAddress.Address_Nick, PAddress.Address_Address) as \"Person_PAddress\",
				coalesce (PP.PersonPregnancy_RiskDPP, 0) + coalesce(LastScreen.PregnancyScreen_RiskPerPat, 0) as \"PersonPregnancy_ObRisk\",
				RT.RiskType_id as \"RiskType_id\",
				RT.RiskType_Name as \"RiskType_Name\",
				PR.Lpu_iid as \"Lpu_iid\",
				L.Lpu_Nick as \"Lpu_Nick\",
				LpuAttach.Lpu_id as \"LpuAttach_id\",
				LpuAttach.Lpu_Nick as \"LpuAttach_Nick\",
				LpuHosp.Lpu_id as \"LpuHosp_id\",
				LpuHosp.Lpu_Nick as \"LpuHosp_Nick\",
				PR.MedPersonal_iid as \"MedPersonal_iid\",
				MP.MedPersonal_Fio as \"MedPersonal_Fio\",
				D.Diag_id as \"Diag_id\",
				D.Diag_FullName as \"Diag_FullName\",
				Result.PregnancyResult_id as \"PregnancyResult_id\",
				Result.PregnancyResult_Name as \"PregnancyResult_Name\",
				Period.Value as \"PersonPregnancy_Period\"
				{$fields},
				dbo.GetPregnancyRoute(PR.PersonRegister_id, 1, 0) as \"lstfactorrisk\",
				RKT.RiskType_Name as \"RiskType_AName\",
				(case when ML.MesLevel_Name = 'Второй уровень (без высокотехнологичной помощи)' then 'МПЦ' else ML.MesLevel_Name end) as \"MesLevel_Name\",
				'' as \"VK_Date\",
				'' as \"VK\",
				'' as \"MO_hospital\",
				dbo.GetPregnancyPRRisk(PR.PersonRegister_id,1) as \"PRRiskFactor\",
				(case when PP.RiskType_did = 1 then 'низкий' when PP.RiskType_did = 2 then 'высокий' else 'низкий' end) as \"RiskPR\",
				(case when PP.RiskType_bid = 1 then 'низкий' when PP.RiskType_bid = 2 then 'средний' when PP.RiskType_bid = 3 then 'высокий' else 'низкий' end) as \"Risk572N\",
				dbo.GetPregnancy572NRisk(PR.PersonRegister_id,1) as \"RiskFactor572N\",
				to_char(PP.PersonPregnancy_birthDate, '{$this->dateTimeForm104}') as \"PersonPregnancy_birthDate\"
			";
		$from = "
		v_PersonRegister PR
				inner join v_PersonRegisterType PRT on PRT.PersonRegisterType_id = PR.PersonRegisterType_id
					and PRT.PersonRegisterType_SysNick ilike 'pregnancy'
				left join v_PersonPregnancy PP on PP.PersonRegister_id = PR.PersonRegister_id
				left join v_PersonRegisterOutCause OutCause on OutCause.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
				left join v_PersonState PS on PS.Person_id = PR.Person_id
				left join v_Address PAddress on PAddress.Address_id = PS.PAddress_id
				left join v_RiskType RT on RT.RiskType_id = PR.RiskType_id
				left join v_PersonCard PC on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
				left join v_Lpu_all LpuAttach on LpuAttach.Lpu_id = PC.Lpu_id
				left join v_Lpu_all L on L.Lpu_id = PR.Lpu_iid
				left join lateral (
					select
						MedPersonal_id,
						Person_Fio as MedPersonal_Fio
					from 
					    v_MedPersonal
					where 
					    MedPersonal_id = PR.MedPersonal_iid and Lpu_id = coalesce(L.Lpu_id, Lpu_id)
					limit 1
				) MP on true
				left join v_BirthCertificate BC on BC.PersonRegister_id = PR.PersonRegister_id
				left join v_EvnVizitPL EVPL on EVPL.EvnVizitPL_id = PP.Evn_id
				left join v_BirthSpecStac BSS on BSS.PersonRegister_id = PR.PersonRegister_id
				left join v_EvnSection ES on ES.EvnSection_id = BSS.EvnSection_id
				left join v_EvnVizitPL rEVPL on rEVPL.EvnVizitPL_id = BSS.Evn_id
				left join v_PregnancyResult Result on Result.PregnancyResult_id = coalesce (PR.PregnancyResult_id, BSS.PregnancyResult_id)
				left join lateral(
					select 
					    Screen.*
					from 
					    v_PregnancyScreen Screen
					where 
					    Screen.PersonRegister_id = PR.PersonRegister_id
					order by 
					    Screen.PregnancyScreen_setDT desc
					limit 1
				) LastScreen on true
				left join lateral (
					select 
					    PQ.PregnancyQuestion_ValuesStr as Lpu_id
					from 
                        v_PregnancyQuestion PQ
                        inner join v_QuestionType QT on QT.QuestionType_id = PQ.QuestionType_id
					where 
					    PQ.PregnancyScreen_id = LastScreen.PregnancyScreen_id and QT.QuestionType_Code = 406
					limit 1
				) LastScreenDir on true
				left join lateral(
					select 
					    PQ.PregnancyQuestion_AnswerInt as Value
					from 
					    v_PregnancyQuestion PQ
					    inner join v_QuestionType QT on QT.QuestionType_id = PQ.QuestionType_id
					where 
					    PQ.PregnancyScreen_id = LastScreen.PregnancyScreen_id
					and 
					    QT.QuestionType_Code in (358,359,362,363)
					order by 
					    QT.QuestionType_Code
					limit 1
				) LastScreenPeriod on true
				left join lateral (
					select coalesce(
						BSS.BirthSpecStac_OutcomPeriod,
						LastScreenPeriod.Value + datediff('week', LastScreen.PregnancyScreen_setDT, CAST(:date as date)),
						PP.PersonPregnancy_Period + datediff('week', PP.PersonPregnancy_setDT, CAST(:date as date))
					) as Value
					limit 1
				) Period on true
				left join v_Lpu_all LpuHosp on LpuHosp.Lpu_id = coalesce(ES.Lpu_id, LastScreenDir.Lpu_id, BC.Lpu_id)
				left join v_Diag D on D.Diag_id = coalesce(ES.Diag_id, rEVPL.Diag_id, LastScreen.Diag_id, PP.Diag_id, EVPL.Diag_id)
				left join v_RiskType RKT on RKT.RiskType_id = PR.RiskType_aid
				left join v_MesLevel ML on ML.MesLevel_id = PR.MesLevel_id
				{$join}
			";
		$query = "
			select
				-- select
				{$select}
				-- end select
			from
				-- from
				{$from}
				-- end from
			where
				-- where
				(1=1) {$filters}
				-- end where
			order by
				-- order by
				{$order_by}
				-- end order by
		";
		//echo getDebugSQL($query, $data);exit;

        $data['date'] = $this->tzGetDate();
		$result = $this->queryResult(getLimitSQLPH($query, $data['start'], $data['limit']), $data);
		$get_count_result = $this->queryResult(getCountSQLPH($query), $data);

		if (!is_array($result) || !is_array($get_count_result)) {
			return false;
		}

		$PersonRegister_ids = array();
		foreach($result as $item) {
			if (!empty($item['PersonRegister_id'])) {
				$PersonRegister_ids[] = $item['PersonRegister_id'];
			}
		}
		
		$ScreenListByPersonRegister = array();
		
		if (count($PersonRegister_ids) > 0) {
			for ($i = 0;;$i++) {
				if ($i*500 > count($PersonRegister_ids)) break;
				$ids = array_slice($PersonRegister_ids, $i*500, 500);
				$ids = implode(',', $ids);
				$query = "
					with Answers as (
						select
							PQ.PregnancyScreen_id,
							PQ.PersonRegister_id,
							PQ.PregnancyQuestion_AnswerInt,
							QT.QuestionType_SysNick
						from 
						    v_PregnancyQuestion PQ
						    inner join v_QuestionType QT on QT.QuestionType_id = PQ.QuestionType_id
						where 
						    PQ.PersonRegister_id in ({$ids}) and QT.DispClass_id = 16
						and 
						    QT.QuestionType_SysNick in ('amenordate','embriondate','uzidate','fmovedate')
					)
					select
						PS.PregnancyScreen_id as \"PregnancyScreen_id\",
						PS.PersonRegister_id as \"PersonRegister_id\",
						to_char(PS.PregnancyScreen_setDT, '{$this->dateTimeForm104}') as \"PregnancyScreen_setDate\",
						coalesce(SA.amenordate, SA.embriondate, SA.uzidate, SA.fmovedate) as \"PregnancyScreen_Period\"
					from
						v_PregnancyScreen PS
						left join lateral (
							select
							(
								select 
								    PregnancyQuestion_AnswerInt
								from 
								    Answers 
								where 
								    PregnancyScreen_id = PS.PregnancyScreen_id
								and 
								    QuestionType_SysNick = 'amenordate'
								limit 1
							) as amenordate,
							(
								select PregnancyQuestion_AnswerInt
								from 
								    Answers 
								where 
								    PregnancyScreen_id = PS.PregnancyScreen_id
								and 
								    QuestionType_SysNick = 'embriondate'
								limit 1
							) as embriondate,
							(
								select 
								    PregnancyQuestion_AnswerInt
								from 
								    Answers 
								where 
								    PregnancyScreen_id = PS.PregnancyScreen_id
								and 
								    QuestionType_SysNick = 'uzidate'
								limit 1
							) as uzidate,
							(
								select 
								    PregnancyQuestion_AnswerInt
								from
								    Answers
								where 
								    PregnancyScreen_id = PS.PregnancyScreen_id
								and 
								    QuestionType_SysNick = 'fmovedate'
								limit 1
							) as fmovedate
						) SA on true
					where
						PS.PersonRegister_id in ({$ids})
					order by
						PS.PregnancyScreen_setDT
				";
				$resp = $this->queryResult($query);
				if (!is_array($resp)) {
					return false;
				}
				
				foreach($resp as &$screen) {
					$period = !empty($screen['PregnancyScreen_Period'])?$screen['PregnancyScreen_Period']:'*';
					$screen['text'] = "Скрининг {$screen['PregnancyScreen_setDate']}, {$period} нед.";
					$ScreenListByPersonRegister[$screen['PersonRegister_id']][] = $screen;
				}
			}

			foreach($result as &$item) {
				if (isset($ScreenListByPersonRegister[$item['PersonRegister_id']])) {
					$item['ScreenData'] = json_encode($ScreenListByPersonRegister[$item['PersonRegister_id']]);
				} else {
					$item['ScreenData'] = null;
				}
				$item['RiskPR'] = ($item['PRRiskFactor'] != '' ? "<img border=0 valign=center ext:qtip=\"" . nl2br(htmlspecialchars($item['PRRiskFactor'])) . " " .  "\" src=\"/img/icons/info16.png\" style=\"cursor: pointer;\"> " : '').$item['RiskPR'];
				$item['Risk572N'] = ($item['RiskFactor572N'] != '' ? "<img border=0 valign=center ext:qtip=\"" . nl2br(htmlspecialchars($item['RiskFactor572N'])) . " " .  "\" src=\"/img/icons/info16.png\" style=\"cursor: pointer;\"> " : '').$item['Risk572N'];
			}
		}

		$response = array(
			'data' => $result,
			'totalCount' => $get_count_result[0]['cnt'],
		);

		return $response;
	}	
	
	/**
	 * Получение списка
	 */
	function loadListMonitorCenter($data) {
		$filters = "";
		$fields = "";
		$join = "";

		//echo '<pre>'.print_r($data, 1).'</pre>';
		
		
		if (!empty($data['Person_SurName'])) {
			$filters .= " and ps.Person_SurName ilike :Person_SurName || '%'";
		}
		if (!empty($data['Person_FirName'])) {
			$filters .= " and ps.Person_FirName ilike :Person_FirName || '%'";
		}
		if (!empty($data['Person_SecName'])) {
			$filters .= " and ps.Person_SecName ilike :Person_SecName || '%'";
		}
		//if (!empty($data['RiskType_id'])) {
			//$filters .= " and PR.RiskType_id = :RiskType_id";
		//}
		if (isset($data['Person_AgeFrom']) && $data['Person_AgeFrom'] >= 0) {
			$filters .= " and dbo.Age2(ps.Person_BirthDay, CAST(:date as date)) >= :Person_AgeFrom";
		}
		if (isset($data['Person_AgeTo']) && $data['Person_AgeTo'] >= 0) {
			$filters .= " and dbo.Age2(ps.Person_BirthDay, CAST(:date as date)) <= :Person_AgeTo";
		}
		if (!empty($data['AttachLpu_id'])) {
			$filters .= " and pcard.Lpu_id = :AttachLpu_id";
		}
		if (!empty($data['Lpu_iid'])) {
			if ($data['Lpu_iid'] == -1) {
				$filters .= " and PR.Lpu_iid is null";
			} else {
				$filters .= " and PR.Lpu_iid = :Lpu_iid";
			}
		}
		if (!empty($data['Lpu_oid'])) {
			if ($data['Lpu_oid'] == -1) {
				$filters .= " and EPS_LPU.Lpu_id is null";
			} else {
				$filters .= " and EPS_LPU.Lpu_id = :Lpu_oid";
			}
		}		
		
		if (!empty($data['EvnPS_setDate'][0]) && !empty($data['EvnPS_setDate'][1])) {
			$filters .= " and (EPS.EvnPS_setDate between :EvnPS_setDateBeg and :EvnPS_setDateEnd)";
			$data['EvnPS_setDateBeg'] = $data['EvnPS_setDate'][0];
			$data['EvnPS_setDateEnd'] = $data['EvnPS_setDate'][1];
		}		

		if (!empty($data['Diag_Code_From'])) {
			$filters .= " and EPS_DIAG.Diag_Code >= :Diag_Code_From";
		}
		if (!empty($data['Diag_Code_To'])) {
			$filters .= " and EPS_DIAG.Diag_Code <= :Diag_Code_To";
		}

		if (isset($data['PersonPregnancy_PeriodFrom']) && $data['PersonPregnancy_PeriodFrom'] >= 0) {
			$filters .= " and Period.Value >= :PersonPregnancy_PeriodFrom";
		}
		if (isset($data['PersonPregnancy_PeriodTo']) && $data['PersonPregnancy_PeriodTo'] >= 0) {
			$filters .= " and Period.Value <= :PersonPregnancy_PeriodTo";
		}
		
		if (!empty($data['PersonRegister_setDateRange'][0]) && !empty($data['PersonRegister_setDateRange'][1])) {
			$filters .= " and PR.PersonRegister_setDate between :PersonRegister_setDateBeg and :PersonRegister_setDateEnd  and PR.PersonRegisterOutCause_id is null";
			$data['PersonRegister_setDateBeg'] = $data['PersonRegister_setDateRange'][0];
			$data['PersonRegister_setDateEnd'] = $data['PersonRegister_setDateRange'][1];
		}
		
		if (!empty($data['RiskType_id'])) {
			$filters .= " and PR.RiskType_aid = :RiskType_id";
		}
		if (!empty($data['PregnancyResult_id'])) {
			$filters .= " and Result.PregnancyResult_id = :PregnancyResult_id";
		}
		if (!empty($data['Type']) && $data['Type'] == 'new') {
			$filters .= " and PR.PersonRegisterOutCause_id is null";
		}
		if (!empty($data['Type']) && $data['Type'] == 'out') {
			$filters .= " and PR.PersonRegisterOutCause_id is not null";
		}
		if (!empty($data['PersonRegister_Code'])) {
			$filters .= " and PR.PersonRegister_Code = :PersonRegister_Code";
		}
		
		if (!empty($data['MesLevel_id'])) {
			$filters .= " and PR.MesLevel_id = :MesLevel_id";
		}
		//echo $data['ADKS'];
		if (!empty($data['ADKS']) && $data['ADKS'] == 2) {
			$filters .= " and PR.MesLevel_id = 8";
		}		
		if (!empty($data['YesNo_id'])) {
			if ($data['YesNo_id'] == 2){
				$filters .= " and PR.PersonRegisterOutCause_id is null";
			}else{
				$filters .= " and PR.PersonRegisterOutCause_id is not null";
			}
		}

		$response = $this->getFilterObstetricComplication($data['ObstetricComplication_id']);
		$join .= $response['join'];
		$filters .= $response['filters'];

		$order_by = "
				PR.PersonRegister_setDate desc,
				PR.PersonRegister_disDate desc
		";
		if (isset($data['Type']) && $data['Type'] == 'out') {
			$order_by = "
				PR.PersonRegister_disDate desc,
				PR.PersonRegister_setDate desc
		";
		} if (isset($data['SignalInfo']) && $data['SignalInfo'] == 1) {
			//if (!empty($data['Trimester_id'])) {

			$fields .= ", 
				EvnVizitPL_Date.EvnVizitPL_setDate as \"EvnVizitPL_setDate\",
				DiffDate.DiffDate as \"DiffDate\",
				(case when Period.Value between 1 and 12  then '1 Триместр'
				when Period.Value between 13 and 27 then '2 Триместр'
				when Period.Value between 28 and 43 then '3 Триместр' end) as \"Trimester\"";
			$join .= "--дата последнего посещения
				left join lateral (
                    select 
                        EvnVizitPL_setDate
                    from 
                        v_EvnVizitPL DT
                    where 
                        DT.Person_id = PR.Person_id 
                    and 
                        DT.Lpu_id = PR.Lpu_iid -- смотрим посещения по лпу учета
                    order by 
                        DT.EvnVizitPL_setDate DESC
                    limit 1
				) EvnVizitPL_Date on true
				--разница между последней датой и сегодняшней
				left join lateral(
				    SELECT DATEDIFF('day', EvnVizitPL_Date.EvnVizitPL_setDate, GETDATE()) AS DiffDate
				) DiffDate on true"
			;
			$filters .= " and ((case when ((Period.Value between 1 and 12) and DiffDate.DiffDate>35) then 1 end=1) 
				 or (case when ((Period.Value between 13 and 27) and DiffDate.DiffDate>35) then 1 end=1)
				 or (case when ((Period.Value between 28 and 43) and DiffDate.DiffDate>22) then 1 end=1))";
			
			if ($data['Trimester'] == 'Trimester1') {
				$filters .= "and Period.Value between 1 and 12";
			} else if ($data['Trimester'] == 'Trimester2') {
				$filters .= "and Period.Value between 13 and 27";
			} else if ($data['Trimester'] == 'Trimester3') {
				$filters .= "and Period.Value between 28 and 43";
			} else {
				$filters .= "";
			}
		}
		
		if (!empty($data['HighRisk_setDT'][0]) && !empty($data['HighRisk_setDT'][1])) {
			$filters .= " and PR.PersonRegister_HighRiskDT between :HighRisk_setDTBeg and :HighRisk_setDTEnd ";
			$data['HighRisk_setDTBeg'] = $data['HighRisk_setDT'][0];
			$data['HighRisk_setDTEnd'] = $data['HighRisk_setDT'][1];
		}				

		$query = "
			select
				-- select
				PR.PersonRegister_id as \"PersonRegister_id\",
				PP.PersonPregnancy_id as \"PersonPregnancy_id\",
				PS.Person_id as \"Person_id\",
				PR.PersonRegister_Code as \"PersonRegister_Code\",								
				coalesce (rtrim(PS.Person_SurName), '') || coalesce (' ' ||rtrim(PS.Person_FirName), '') || coalesce (' ' || rtrim(PS.Person_SecName), '') as \"Person_Fio\",
				dbo.Age2(PS.Person_BirthDay, getdate()) as \"Person_Age\",
				to_char(PS.Person_BirthDay, '{$this->dateTimeForm104}') as \"Person_BirthDay\",
				PR.Lpu_iid as \"Lpu_iid\",
				L.Lpu_Nick as \"Lpu_Nick\",
				Period.Value as \"PersonPregnancy_Period\",
				LpuAttach.Lpu_id as \"LpuAttach_id\",
				LpuAttach.Lpu_Nick as \"LpuAttach_Nick\",
				RKT.RiskType_Name as \"RiskType_AName\",
				--add date set high risk
				to_char(PR.PersonRegister_HighRiskDT, '{$this->dateTimeForm104}') as \"HighRisk_setDT\",
				coalesce (PP.PersonPregnancy_RiskDPP, 0) + coalesce (LastScreen.PregnancyScreen_RiskPerPat, 0) as \"PersonPregnancy_ObRisk\",
				dbo.GetPregnancyRoute(PR.PersonRegister_id, 1, 0) as \"lstfactorrisk\",
				(case when ML.MesLevel_Name = 'Второй уровень (без высокотехнологичной помощи)' then 'МПЦ' else ML.MesLevel_Name end) as \"MesLevel_Name\",
				EPS_LPU.Lpu_Nick as \"NickHospital\",
				to_char(EPS.EvnPS_setDate, '{$this->dateTimeForm104}') as \"DateHospital\",
				--EPS.EvnPS_setDate,
				--EPS.EvnPS_setTime,


				EPS_DIAG.Diag_FullName as \"BaseDiagnozHospital\",
				EPS_PROFILE.LpuSectionProfile_Name as \"ProfilHospital\",
				
				--причина попадания в список - КВС
				(case when PR.EvnPS_id is null then 0 else 1 end) as \"has_evnps\",
				--причина попадания в список - высокий риск
				(case when PR.PersonRegister_HighRiskDT is null then 0 else 1 end) as \"has_highrisk\",
				--новая запись по причине появления КВС
				(case when EPS.EvnPS_id is null then 0 when PR.EvnPS_id=EPS.EvnPS_id then 0 else (case when (PR.PersonRegister_setDate is not null and EPS.EvnPS_setDate is not null and EPS.EvnPS_setDate>PR.PersonRegister_setDate and EPS.EvnPS_disDate is null) then 1 else 0 end) end) as \"notopen_evnps\",
				--новая запись по причине установления высокого риска
				(case when PR.RiskType_aid > 2
					then (case when PR.PersonRegister_HighRiskDT is not null
							then 0
							else 1
						end)
					else 0
				end) as \"notopen_highrisk\",
				dbo.GetPregnancyPRRisk(PR.PersonRegister_id,1) as \"PRRiskFactor\",
				(case when PP.RiskType_bid = 1 then 'низкий' when PP.RiskType_bid = 2 then 'средний' when PP.RiskType_bid = 3 then 'высокий' else '' end) as \"Risk572N\",
				dbo.GetPregnancy572NRisk(PR.PersonRegister_id,1) as \"RiskFactor572N\",
				PR.MedPersonal_iid as \"MedPersonal_iid\",
				PR.Lpu_iid as \"Lpu_iid\",
				UserCache.pmUser_id as \"pmUser_id\",
				UserCache.pmUser_Login as \"pmUser_Login\",
				UserCache.pmUser_Name as \"pmUser_Name\",
				pucgl.GroupPregnancy as \"GroupPregnancy\"
				-- end select
			from
				-- from
				v_PersonRegister PR
				inner join v_PersonRegisterType PRT on PRT.PersonRegisterType_id = PR.PersonRegisterType_id
					and PRT.PersonRegisterType_SysNick ilike 'pregnancy'
				left join v_PersonPregnancy PP on PP.PersonRegister_id = PR.PersonRegister_id
				left join v_PersonRegisterOutCause OutCause on OutCause.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
				left join v_PersonState PS on PS.Person_id = PR.Person_id
				left join v_Address PAddress on PAddress.Address_id = PS.PAddress_id
				left join v_RiskType RT on RT.RiskType_id = PR.RiskType_id
				left join v_PersonCard PC on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
				left join v_Lpu_all LpuAttach on LpuAttach.Lpu_id = PC.Lpu_id
				left join v_Lpu_all L on L.Lpu_id = PR.Lpu_iid
				left join lateral (
					select
						MedPersonal_id,
						Person_Fio as MedPersonal_Fio
					from 
					    v_MedPersonal
					where 
					    MedPersonal_id = PR.MedPersonal_iid 
					and 
					  Lpu_id = coalesce (L.Lpu_id, Lpu_id)
					limit 1
				) MP ON true
				left join v_BirthCertificate BC on BC.PersonRegister_id = PR.PersonRegister_id
				left join v_EvnVizitPL EVPL on EVPL.EvnVizitPL_id = PP.Evn_id
				left join v_BirthSpecStac BSS on BSS.PersonRegister_id = PR.PersonRegister_id
				left join lateral (
					select 
					    Screen.*
					from 
					    v_PregnancyScreen Screen
					where 
					    Screen.PersonRegister_id = PR.PersonRegister_id
					order by 
					    Screen.PregnancyScreen_setDT desc
					limit 1
				) LastScreen on true
				left join lateral (
					select 
					    PQ.PregnancyQuestion_ValuesStr as Lpu_id
					from 
					    v_PregnancyQuestion PQ
					    inner join v_QuestionType QT on QT.QuestionType_id = PQ.QuestionType_id
					where 
					    PQ.PregnancyScreen_id = LastScreen.PregnancyScreen_id
					and 
					    QT.QuestionType_Code = 406
					limit 1
				) LastScreenDir on true
				left join lateral (
					select 
					    PQ.PregnancyQuestion_AnswerInt as Value
					from 
					    v_PregnancyQuestion PQ
					    inner join v_QuestionType QT on QT.QuestionType_id = PQ.QuestionType_id
					where 
					    PQ.PregnancyScreen_id = LastScreen.PregnancyScreen_id
					and 
					    QT.QuestionType_Code in (358,359,362,363)
					order by 
					    QT.QuestionType_Code
					limit 1
				) LastScreenPeriod on true
				left join lateral (
					select  coalesce(
						BSS.BirthSpecStac_OutcomPeriod,
						LastScreenPeriod.Value + datediff('week', LastScreen.PregnancyScreen_setDT, getdate()),
						PP.PersonPregnancy_Period + datediff('week', PP.PersonPregnancy_setDT, getdate())
					) as Value
					limit 1
				) Period on true
				left join v_RiskType RKT on RKT.RiskType_id = PR.RiskType_aid
				left join v_MesLevel ML on ML.MesLevel_id = PR.MesLevel_id
				left join v_EvnPS EPS on EPS.Person_id=PR.Person_id
				left join lateral (
					select 
					    *
					from 
					    v_EvnSection ES
					where
					    ES.EvnSection_pid=EPS.EvnPS_id
					and 
					    ES.LeaveType_id is null 
					limit 1
				) ESS on true
				left join v_Lpu_all EPS_LPU on EPS_LPU.Lpu_id = ESS.Lpu_id
				left join v_Diag EPS_DIAG on EPS_DIAG.Diag_id = ESS.Diag_id
				left join v_LpuSectionProfile EPS_PROFILE on EPS_PROFILE.LpuSectionProfile_id = ESS.LpuSectionProfile_id
				left join lateral (
					select
						pmUser_id,
						RTrim(pmUser_Login) as pmUser_Login,
						pmUser_Name
					from
						pmUserCache
					where
						pmUserCache.MedPersonal_id=PR.MedPersonal_iid and pmUserCache.Lpu_id=PR.Lpu_iid
					order by pmUserCache.pmUserCache_updDT desc
					limit 1
				) UserCache on true
				left join lateral (
					select 1 as GroupPregnancy
					from pmUserCacheGroupLink pucgl0
						inner join pmUserCacheGroup pucg on pucg.pmUserCacheGroup_id=pucgl0.pmUserCacheGroup_id
							and pucg.pmUserCacheGroup_SysNick in ('OperPregnRegistry','RegOperPregnRegistry')
					where pucgl0.pmUserCache_id = UserCache.pmUser_id
					limit 1
				) pucgl on true
				{$join}
				-- end from
			where
				-- where
				(1=1) {$filters}
					and ((PR.PersonRegister_setDate is not null and EPS.EvnPS_setDate is not null and EPS.EvnPS_setDate>PR.PersonRegister_setDate and EPS.EvnPS_disDate is null) or
					--(PR.PersonRegister_HighRiskDT is not null)
					(PR.RiskType_aid>2)
					)					
				-- end where
			order by
				-- order by
				{$order_by}
				-- end order by
				
		";
		//echo getDebugSQL($query, $data);exit;
        $data['date'] = $this->dateTimeForm104;
		$result = $this->queryResult(getLimitSQLPH($query, $data['start'], $data['limit']), $data);
		$get_count_result = $this->queryResult(getCountSQLPH($query), $data);

		if (!is_array($result) || !is_array($get_count_result)) {
			return false;
		}

		foreach($result as &$item) {
			$item['PRRiskFactor'] = ($item['PRRiskFactor'] != '' ? "<img border=0 valign=center ext:qtip=\"" . nl2br(htmlspecialchars($item['PRRiskFactor'] == '' ? 'Отсутствуют' : $item['PRRiskFactor'])) .  "\" src=\"/img/icons/info16.png\" style=\"cursor: pointer;\"> <br>" : '').$item['PRRiskFactor'];
			$item['Risk572N'] = ($item['RiskFactor572N'] != '' ? "<img border=0 valign=center ext:qtip=\"" . nl2br(htmlspecialchars($item['RiskFactor572N'])) . " " .  "\" src=\"/img/icons/info16.png\" style=\"cursor: pointer;\"> <br>" : '').$item['Risk572N'];
		}

		$response = array(
			'data' => $result,
			'totalCount' => $get_count_result[0]['cnt'],
		);

		return $response;
	}
	
	/**
	 * Получение списка беременных для сигнальной информации в разбивке по МО и триместрам
	 */
	function loadTrimesterListMO($data) {
	    $select = "PR.Lpu_iid as \"Lpu_iid\",
				L.Lpu_Nick as \"Lpu_Nick\",
                count ( case when Period.Value between 1 and 12  then '1 Триместр' end ) as \"Trimester1\",
                count ( case when Period.Value between 13 and 27 then '2 Триместр' end ) as \"Trimester2\",
                count ( case when Period.Value between 28 and 43 then '3 Триместр' end ) as \"Trimester3\"
        ";

	    $from = "v_PersonRegister PR
				inner join v_PersonRegisterType PRT on PRT.PersonRegisterType_id = PR.PersonRegisterType_id
					and PRT.PersonRegisterType_SysNick ilike 'pregnancy'
				left join v_PersonPregnancy PP on PP.PersonRegister_id = PR.PersonRegister_id
				left join v_PersonRegisterOutCause OutCause on OutCause.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
				left join v_PersonState PS on PS.Person_id = PR.Person_id
				left join v_Address PAddress on PAddress.Address_id = PS.PAddress_id
				left join v_RiskType RT on RT.RiskType_id = PR.RiskType_id
				left join v_PersonCard PC on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
				left join v_Lpu_all LpuAttach on LpuAttach.Lpu_id = PC.Lpu_id
				left join v_Lpu_all L on L.Lpu_id = PR.Lpu_iid
				left join lateral (
					select
						MedPersonal_id,
						Person_Fio as MedPersonal_Fio
					from v_MedPersonal
					where MedPersonal_id = PR.MedPersonal_iid and Lpu_id = coalesce (L.Lpu_id, Lpu_id)
					limit 1
				) MP on true
				left join v_BirthCertificate BC on BC.PersonRegister_id = PR.PersonRegister_id
				left join v_EvnVizitPL EVPL on EVPL.EvnVizitPL_id = PP.Evn_id
				left join v_BirthSpecStac BSS on BSS.PersonRegister_id = PR.PersonRegister_id
				left join v_EvnSection ES on ES.EvnSection_id = BSS.EvnSection_id
				left join v_EvnVizitPL rEVPL on rEVPL.EvnVizitPL_id = BSS.Evn_id
				left join v_PregnancyResult Result on Result.PregnancyResult_id = coalesce(PR.PregnancyResult_id,BSS.PregnancyResult_id)
				left join lateral (
					select 
					    Screen.*
					from
					    v_PregnancyScreen Screen
					where 
					    Screen.PersonRegister_id = PR.PersonRegister_id
					order by 
					    Screen.PregnancyScreen_setDT desc
					limit 1
				) LastScreen on true
				left join lateral (
					select 
					    PQ.PregnancyQuestion_ValuesStr as Lpu_id
					from 
					    v_PregnancyQuestion PQ
					    inner join v_QuestionType QT on QT.QuestionType_id = PQ.QuestionType_id
					where 
					    PQ.PregnancyScreen_id = LastScreen.PregnancyScreen_id
					and 
					  QT.QuestionType_Code = 406
					limit 1
				) LastScreenDir on true
				left join lateral (
					select 
					    PQ.PregnancyQuestion_AnswerInt as Value
					from 
					    v_PregnancyQuestion PQ
					    inner join v_QuestionType QT on QT.QuestionType_id = PQ.QuestionType_id
					where
					    PQ.PregnancyScreen_id = LastScreen.PregnancyScreen_id
					and 
					    QT.QuestionType_Code in (358,359,362,363)
					order by 
					    QT.QuestionType_Code
					limit 1
				) LastScreenPeriod on true
				left join lateral (
					select coalesce(
						BSS.BirthSpecStac_OutcomPeriod,
						LastScreenPeriod.Value + datediff('week', LastScreen.PregnancyScreen_setDT, (select date from cte)),
						PP.PersonPregnancy_Period + datediff('week', PP.PersonPregnancy_setDT, (select date from cte))
					) as Value
					limit 1
				) Period on true
				left join v_Lpu_all LpuHosp on LpuHosp.Lpu_id = coalesce(ES.Lpu_id, LastScreenDir.Lpu_id, BC.Lpu_id)
				left join v_Diag D on D.Diag_id = coalesce(ES.Diag_id, rEVPL.Diag_id, LastScreen.Diag_id, PP.Diag_id, EVPL.Diag_id)
				left join v_RiskType RKT on RKT.RiskType_id = PR.RiskType_aid
				left join v_MesLevel ML on ML.MesLevel_id = PR.MesLevel_id
				--дата последнего посещения
				left join lateral (
                    select 
                        EvnVizitPL_setDate
                    from 
                        v_EvnVizitPL DT
                    where 
                        DT.Person_id = PR.Person_id 
                    and 
                        DT.Lpu_id = PR.Lpu_iid -- смотрим посещения по лпу учета
                    order by 
                        DT.EvnVizitPL_setDate DESC
                    limit 1
				) EvnVizitPL_Date on true
				--разница между последней датой и сегодняшней
				left join lateral (
				    select DATEDIFF('day', EvnVizitPL_Date.EvnVizitPL_setDate, GETDATE()) AS DiffDate
				) DiffDate on true
		";

	    $where = "
	            (1=1) 
			and 
			    PR.PersonRegisterOutCause_id is null
            and 
                ((case when ((Period.Value between 1 and 12) and DiffDate.DiffDate>35) then 1 end=1) 
			or 
			    (case when ((Period.Value between 13 and 27) and DiffDate.DiffDate>28) then 1 end=1)
			or 
			    (case when ((Period.Value between 28 and 43) and DiffDate.DiffDate>19) then 1 end=1))
	    ";
		$query = "
			-- addit with
            with cte as (
                select dbo.tzGetDate() as date
            )
            -- end addit with
            
			select
				-- select
				{$select}				
				-- end select
			from
				-- from
				{$from}
				-- end from
			where
				{$where}
			group by
				PR.Lpu_iid,
				L.Lpu_Nick
		  	order by
				PR.Lpu_iid";

		return $this->queryResult($query);
	}

	/**
	 * Получение списка записей не включенных в регистр
	 */
	function loadNotIncludeList($data) {
		$fieldsList = array();
		$response = $this->getFilterObstetricComplication($data['ObstetricComplication_id']);
		$filterList = array("( 1 = 1) {$response['filters']} ");
		$joinList = array($response['join']);

		if (!empty($data['Person_SurName'])) {
			$filterList[] = "PS.Person_SurName ilike :Person_SurName || '%'";
		}
		if (!empty($data['Person_FirName'])) {
			$filterList[] = "PS.Person_FirName ilike :Person_FirName || '%'";
		}
		if (!empty($data['Person_SecName'])) {
			$filterList[] = "PS.Person_SecName ilike :Person_SecName || '%'";
		}
		if (!empty($data['Lpu_oid'])) {
			$Lpu_id = "and E.Lpu_id = :Lpu_oid";
		}
		else {
			$Lpu_id = '';
		}
		if (!empty($data['RiskType_cid'])) {
			if ($data['RiskType_cid'] == 1) {
				$filterList[] = "(PP.RiskType_bid = 1 or PP.RiskType_bid is null)";
			}
			else {
				$filterList[] = "PP.RiskType_bid = :RiskType_cid";
			}
		}
		if (!empty($data['RiskType_did'])) {
			if ($data['RiskType_did'] == 1) {
				$filterList[] = "(PP.RiskType_did = 1 or PP.RiskType_did is null)";
			}
			else {
				$filterList[] = "PP.RiskType_did = :RiskType_did";
			}
		}		
		if (!empty($data['Evn_setDateRange'][0]) && !empty($data['Evn_setDateRange'][1])) {
			$EvnDateRange = "and E.Evn_setDate between :Evn_setDateBeg and :Evn_setDateEnd";
			$data['Evn_setDateBeg'] = $data['Evn_setDateRange'][0];
			$data['Evn_setDateEnd'] = $data['Evn_setDateRange'][1];
		}
		else {
			$EvnDateRange = '';
		}
		if (!empty($data['Diag_Code_From'])) {
			$filterList[] = "D.Diag_Code >= :Diag_Code_From";
		}
		if (!empty($data['Diag_Code_To'])) {
			$filterList[] = "D.Diag_Code <= :Diag_Code_To";
		}

		//gaf #106851 29112017
		if ( !empty($data['EvnType_id']) && in_array($data['EvnType_id'], array(1, 2)) ) {
			switch ( $data['EvnType_id'] ) {
				case 1:
					$data['EvnClass_SysNick'] = 'EvnPS';

					$fieldsList[] = "EPS.EvnPS_NumCard as \"Evn_NumCard\"";
					$fieldsList[] = "LT.LeaveType_id as \"LeaveType_id\"";
					$fieldsList[] = "null as \"ResultClass_id\"";
					$fieldsList[] = "LT.LeaveType_Name as \"EvnResult\"";

					$joinList[] = "left join v_LeaveType LT on LT.LeaveType_id = EPS.LeaveType_id";
					$joinList[] = "left join v_Diag D on D.Diag_id = EPS.Diag_id";
					//$joinList[] = "left join v_EvnSection ES on ES.EvnSection_pid = EPS.EvnPS_id and ES.EvnSection_Index = EvnSection_Count - 1";
					$joinList[] = "left join lateral (select * from v_EvnSection where EvnSection_pid = EPS.EvnPS_id and EvnSection_Index = EvnSection_Count - 1 limit 1) ES on true";
					$joinList[] = "left join v_MedStaffFact MSF on MSF.MedStaffFact_id = ES.MedStaffFact_id";

					$EvnFilter = "and E.EvnClass_SysNick = 'EvnPS'";
					break;

				case 2:
					$data['EvnClass_SysNick'] = 'EvnPL';

					$fieldsList[] = "EPL.EvnPL_NumCard as \"Evn_NumCard\"";
					$fieldsList[] = "null as \"LeaveType_id\"";
					$fieldsList[] = "RC.ResultClass_id as \"ResultClass_id\"";
					$fieldsList[] = "RC.ResultClass_Name as \"EvnResult\"";

					$joinList[] = "left join v_ResultClass RC on RC.ResultClass_id = EPL.ResultClass_id";
					$joinList[] = "left join v_Diag D on D.Diag_id = EPL.Diag_id";
					//$joinList[] = "left join v_EvnVizitPL EVPL on EVPL.EvnVizitPL_pid = EPL.EvnPL_id and EVPL.EvnVizitPL_Index = EvnVizitPL_Count - 1";
					$joinList[] = "left join lateral (select * from v_EvnVizitPL where EvnVizitPL_pid = EPL.EvnPL_id and EvnVizitPL_Index = EvnVizitPL_Count - 1 limit 1) EVPL on true";
					$joinList[] = "left join v_MedStaffFact MSF on MSF.MedStaffFact_id = EVPL.MedStaffFact_id";

					$EvnFilter = "and E.EvnClass_SysNick = 'EvnPL'";
					break;
			}
		}
		else {
			$data['EvnClass_SysNick'] = '';

			$fieldsList[] = "coalesce(EPL.EvnPL_NumCard, EPS.EvnPS_NumCard) as \"Evn_NumCard\"";
			$fieldsList[] = "LT.LeaveType_id as \"LeaveType_id\"";
			$fieldsList[] = "RC.ResultClass_id as \"ResultClass_id\"";
			$fieldsList[] = "coalesce(LT.LeaveType_Name, RC.ResultClass_Name) as \"EvnResult\"";

			$joinList[] = "left join v_ResultClass RC on RC.ResultClass_id = EPL.ResultClass_id";
			$joinList[] = "left join v_LeaveType LT on LT.LeaveType_id = EPS.LeaveType_id";
			$joinList[] = "left join v_Diag D on D.Diag_id = coalesce(EPL.Diag_id, EPS.Diag_id)";
			//$joinList[] = "left join v_EvnVizitPL EVPL on EVPL.EvnVizitPL_pid = EPL.EvnPL_id and EVPL.EvnVizitPL_Index = EvnVizitPL_Count - 1";
			$joinList[] = "left join lateral (select * from v_EvnVizitPL where EvnVizitPL_pid = EPL.EvnPL_id and EvnVizitPL_Index = EvnVizitPL_Count - 1 limit 1) EVPL on true";
			//$joinList[] = "left join v_EvnSection ES on ES.EvnSection_pid = EPS.EvnPS_id and ES.EvnSection_Index = EvnSection_Count - 1";
			$joinList[] = "left join lateral (select * from v_EvnSection where EvnSection_pid = EPS.EvnPS_id and EvnSection_Index = EvnSection_Count - 1 limit 1) ES on true";
			$joinList[] = "left join v_MedStaffFact MSF on MSF.MedStaffFact_id = coalesce (EVPL.MedStaffFact_id, ES.MedStaffFact_id)";

			$EvnFilter = "and E.EvnClass_id in (3, 30)"; // E.EvnClass_SysNick in ('EvnPL', 'EvnPS')
		}

		if (!empty($data['MedPersonal_iidd'])) {
			$filterList[] = "MSF.MedPersonal_id = :MedPersonal_iidd";
		}

		$withCte = "
		with cte  as
		(
            select 
                dbo.tzGetDate() as date, 
                dateadd('year', 50, dbo.tzGetDate()) as bigdate
            limit 1
		)
		";

		$select  = "
		        E.Evn_id as \"Evn_id\",
				E.EvnClass_SysNick as \"EvnClass_SysNick\",
				case
					when E.EvnClass_SysNick = 'EvnPL' then 'Амбулаторный'
					when E.EvnClass_SysNick = 'EvnPS' then 'Стационарный'
				end as \"EvnType\",
				to_char(coalesce(EPL.EvnPL_setDate, EPS.EvnPS_setDate), '{$this->dateTimeForm104}') as \"Evn_setDate\",
				to_char(coalesce(EPL.EvnPL_disDate, EPS.EvnPS_disDate), '{$this->dateTimeForm104}') as \"Evn_disDate\",
				L.Lpu_id as \"Lpu_id\",
				L.Lpu_Nick as \"Lpu_Nick\",
				D.Diag_id as \"Diag_id\",
				D.Diag_FullName as \"Diag_FullName\",
				PS.Person_id as \"Person_id\",
				PS.Person_SurName || ' ' || PS.Person_FirName || coalesce(' ' || PS.Person_SecName, '') as \"Person_Fio\",
				to_char(PS.Person_BirthDay, '{$this->dateTimeForm104}') as \"Person_BirthDay\",
				LpuAttach.Lpu_id as \"LpuAttach_id\",
				LpuAttach.Lpu_Nick as \"LpuAttach_Nick\",
				coalesce(PAddress.Address_Nick, PAddress.Address_Address) as \"Person_PAddress\",				
				MSF.Person_Fio as \"MedPersonal\",
				dbo.GetPregnancyPRRisk(PR.PersonRegister_id,1) as \"PRRiskFactor\",
				(case when PP.RiskType_did = 1 then 'низкий' when PP.RiskType_did = 2 then 'высокий' else 'низкий' end) as \"RiskPR\",
				(case when PP.RiskType_bid = 1 then 'низкий' when PP.RiskType_bid = 2 then 'средний' when PP.RiskType_bid = 3 then 'высокий' else 'низкий' end) as \"Risk572N\",
				dbo.GetPregnancy572NRisk(PR.PersonRegister_id,1) as \"RiskFactor572N\",
				case when exists(
					select *
					from v_PersonQuarantine PQ
					where PQ.Person_id = PS.Person_id 
					and PQ.PersonQuarantine_endDT is null
				) then 2 else 1 end as \"PersonQuarantine_IsOn\"
				," . implode(", ", $fieldsList);

		$from ="
				(select
					max(E.EvnClass_SysNick) as EvnClass_SysNick,
					max(E.Evn_id) as Evn_id,
					max(E.Lpu_id) as Lpu_id,
					E.Person_id
					from
						v_Evn E
						left join v_PersonPregnancy PP on PP.Evn_id = E.Evn_id
						left join lateral (
							select 
								PersonRegister_id 
							from 
								v_PersonRegister PR
							where 
								PR.Person_id = E.Person_id
								and
								PR.PersonRegisterType_id = 2
							and 
					   			coalesce(PR.PersonRegister_disDate, (select bigdate from cte)) >= E.Evn_setDate
							limit 1
						) PR on true
						left join v_EvnPL EPL on EPL.EvnPL_id = E.Evn_id
						left join v_EvnPS EPS on EPS.EvnPS_id = E.Evn_id
						left join v_Diag D on D.Diag_id = coalesce(EPL.Diag_id, EPS.Diag_id)
					WHERE 
						(1 = 1)
						{$Lpu_id}
						{$EvnDateRange}
						{$EvnFilter}
						and exists (select 1 from v_MorbusDiag MD  where MD.MorbusType_id = 2 and MD.Diag_id = D.Diag_id limit 1)
						and D.Diag_id not in (1944, 1946, 11071,11072,11073,11074,11075,11076,11077,11078,11079,11089,11090,11091)
						and PP.PersonPregnancy_id is null
						and PR.PersonRegister_id is null
					Group by E.Person_id
				) E
				left join v_PersonPregnancy PP on PP.Evn_id = E.Evn_id
				left join v_Lpu L on L.Lpu_id = E.Lpu_id
				left join v_PersonState PS on PS.Person_id = E.Person_id
				left join v_PersonCard PC on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
				left join v_Lpu_all LpuAttach on LpuAttach.Lpu_id = PC.Lpu_id
				left join v_Address PAddress on PAddress.Address_id = PS.PAddress_id
				left join v_EvnPL EPL on EPL.EvnPL_id = E.Evn_id
				left join v_EvnPS EPS on EPS.EvnPS_id = E.Evn_id				
				left join lateral (
                    select 
					    PersonRegister_id 
                    from 
					    v_PersonRegister PR
					where 
					    PR.Person_id = E.Person_id
                        and
                        PR.PersonRegisterType_id = 2 -- общерегиональный
					and 
					    coalesce(PR.PersonRegister_disDate, (select bigdate from cte)) >= coalesce(EPL.EvnPL_setDate, EPS.EvnPS_setDate)
					limit 1
				) PR on true
				" . implode(" ", $joinList) . "
		";

		$where = implode(" and ", $filterList);
		
		$query = "
			-- variables
		       {$withCte}
			-- end variables

			select
				-- select
				{$select}
				-- end select
			from
				-- from
				{$from}
				-- end from
			where
				-- where
				{$where}
				-- end where
			order by
				-- order by
				coalesce(EPL.EvnPL_disDate, EPS.EvnPS_disDate) desc,
				coalesce(EPL.EvnPL_setDate, EPS.EvnPS_setDate) desc
				-- end order by
		";
		//echo getDebugSQL($query, $data);exit;
		$resp = $this->getPagingResponse($query, $data, $data['start'], $data['limit'], true);

		if (isset($resp['data'])) {
			foreach($resp['data'] as &$item) {
				$item['RiskPR'] = ($item['PRRiskFactor'] != '' ? "<img border=0 valign=center ext:qtip=\"" . nl2br(htmlspecialchars($item['PRRiskFactor'])) . " " .  "\" src=\"/img/icons/info16.png\" style=\"cursor: pointer;\"> <br>" : '').$item['RiskPR'];
				$item['Risk572N'] = ($item['RiskFactor572N'] != '' ? "<img border=0 valign=center ext:qtip=\"" . nl2br(htmlspecialchars($item['RiskFactor572N'])) . " " .  "\" src=\"/img/icons/info16.png\" style=\"cursor: pointer;\"> <br>" : '').$item['Risk572N'];
			}
		}

		return $resp;
	}

	/**
	 * Получение списка
	 */
	function loadFinishedList($data) {
		$filters = "";
		if (!empty($data['Person_SurName'])) {
			$filters .= " and ps.Person_SurName ilike :Person_SurName || '%'";
		}
		if (!empty($data['Person_FirName'])) {
			$filters .= " and ps.Person_FirName ilike :Person_FirName || '%'";
		}
		if (!empty($data['Person_SecName'])) {
			$filters .= " and ps.Person_SecName ilike :Person_SecName || '%'";
		}
		if (!empty($data['Person_BirthDay'])) {
			$filters .= " and ps.Person_BirthDay = :Person_BirthDay";
		}
		if (!empty($data['EvnUsluga_setDate_From'])) {
			$filters .= " and EvnUsluga_setDate >= :EvnUsluga_setDate_From";
		}
		if (!empty($data['EvnUsluga_setDate_To'])) {
			$filters .= " and EvnUsluga_setDate <= :EvnUsluga_setDate_To";
		}
		if (!empty($data['HospLpu_id'])) {
			$filters .= " and eps.Lpu_id = :HospLpu_id";
		}
		if (!empty($data['LeaveType_id'])) {
			$filters .= " and eps.LeaveType_id = :LeaveType_id";
		}

		$select = "eu.Person_id as \"Person_id\",
				coalesce(PS.Person_SurName,'') || ' ' || coalesce (PS.Person_FirName, '') || ' ' || coalesce(PS.Person_SecName, '') as \"Person_Fio\",
				to_char(PS.Person_BirthDay, '{$this->dateTimeForm104}') as \"Person_BirthDay\",
				l.Lpu_Nick as \"Lpu_Nick\",
				lt.LeaveType_Name as \"LeaveType_Name\",
				eps.EvnPS_id as \"EvnPS_id\",
				to_char(eps.EvnPS_setDate, '{$this->dateTimeForm104}') as \"EvnPS_setDate\",
				to_char(eps.EvnPS_disDate, '{$this->dateTimeForm104}') as \"EvnPS_disDate\",
				to_char(eu.EvnUsluga_setDate, '{$this->dateTimeForm104}') as \"EvnUsluga_setDate\"
		";
		$query = "
			select
				-- select
				{$select}
				-- end select
			from
				-- from
				v_EvnUsluga eu
				inner join UslugaComplex uc on uc.uslugacomplex_id = eu.uslugacomplex_id
				inner join v_EvnPS eps on eu.evnusluga_rid = eps.evnps_id
				inner join v_PersonState ps on ps.person_id = eps.person_id
				inner join v_Lpu l on l.lpu_id = eps.lpu_id
				left join v_LeaveType lt on lt.leavetype_id = eps.leavetype_id
				-- end from
			where
				-- where
				(1 = 1)
				{$filters}
				and uc.uslugacomplex_code in ('B01.001.006', 'B01.001.009', 'B02.001.002')
				-- end where
			order by
				-- order by
				eu.EvnUsluga_setDate desc,
				l.Lpu_Nick asc,
				ps.Person_SurName asc
				-- end order by
		";

		$limit_query = getLimitSQLPH($query, $data['start'], $data['limit']);
		$result = $this->db->query($limit_query, $data);

		if ( is_object($result) ) {
			$res = $result->result('array');

			if ( is_array($res) ) {
				if ( $data['start'] == 0 && count($res) < $data['limit'] ) {
					$response['data'] = $res;
					$response['totalCount'] = count($res);
					return $response;
				}
				else {
					$response['data'] = $res;
					$get_count_query = getCountSQLPH($query);
					$get_count_result = $this->db->query($get_count_query, $data);

					if ( is_object($get_count_result) ) {
						$response['totalCount'] = $get_count_result->result('array');
						$response['totalCount'] = $response['totalCount'][0]['cnt'];
						return $response;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Получение списка
	 */
	function loadInterruptedList($data) {
		$filters = "";
		if (!empty($data['Person_SurName'])) {
			$filters .= " and ps.Person_SurName ilike :Person_SurName || '%'";
		}
		if (!empty($data['Person_FirName'])) {
			$filters .= " and ps.Person_FirName ilike :Person_FirName || '%'";
		}
		if (!empty($data['Person_SecName'])) {
			$filters .= " and ps.Person_SecName ilike :Person_SecName || '%'";
		}
		if (!empty($data['Person_BirthDay'])) {
			$filters .= " and ps.Person_BirthDay = :Person_BirthDay";
		}
		if (!empty($data['EvnUsluga_setDate_From'])) {
			$filters .= " and EvnUsluga_setDate >= :EvnUsluga_setDate_From";
		}
		if (!empty($data['EvnUsluga_setDate_To'])) {
			$filters .= " and EvnUsluga_setDate <= :EvnUsluga_setDate_To";
		}
		if (!empty($data['HospLpu_id'])) {
			$filters .= " and eps.Lpu_id = :HospLpu_id";
		}
		if (!empty($data['LeaveType_id'])) {
			$filters .= " and eps.LeaveType_id = :LeaveType_id";
		}

		$query = "
			select
				-- select
				eu.Person_id,
				coalesce (PS.Person_SurName, '') || ' ' || coalesce (PS.Person_FirName, '') || ' ' || coalesce (PS.Person_SecName,'') as \"Person_Fio\",
				to_char(PS.Person_BirthDay, '{$this->dateTimeForm104}') as \"Person_BirthDay\",
				l.Lpu_Nick as \"Lpu_Nick\",
				lt.LeaveType_Name as \"LeaveType_Name\",
				eps.EvnPS_id as \"EvnPS_id\",
				to_char(eps.EvnPS_setDate, '{$this->dateTimeForm104}') as \"EvnPS_setDate\",
				to_char(eps.EvnPS_disDate, '{$this->dateTimeForm104}') as \"EvnPS_disDate\",
				to_char(eu.EvnUsluga_setDate, '{$this->dateTimeForm104}') as \"EvnUsluga_setDate\"
				-- end select
			from
				-- from
				v_EvnUsluga eu 
				inner join UslugaComplex uc on uc.uslugacomplex_id = eu.uslugacomplex_id
				inner join v_EvnPS eps on eu.evnusluga_rid = eps.evnps_id
				inner join v_PersonState ps on ps.person_id = eps.person_id
				inner join v_Lpu l on l.lpu_id = eps.lpu_id
				left join v_LeaveType lt on lt.leavetype_id = eps.leavetype_id
				-- end from
			where
				-- where
				(1 = 1)
				{$filters}
				and uc.uslugacomplex_code in ('A16.20.079', 'A16.20.037')
				-- end where
			order by
				-- order by
				eu.EvnUsluga_setDate desc,
				l.Lpu_Nick asc,
				ps.Person_SurName asc
				-- end order by
		";

		$limit_query = getLimitSQLPH($query, $data['start'], $data['limit']);
		$result = $this->db->query($limit_query, $data);

		if ( is_object($result) ) {
			$res = $result->result('array');

			if ( is_array($res) ) {
				if ( $data['start'] == 0 && count($res) < $data['limit'] ) {
					$response['data'] = $res;
					$response['totalCount'] = count($res);
					return $response;
				}
				else {
					$response['data'] = $res;
					$get_count_query = getCountSQLPH($query);
					$get_count_result = $this->db->query($get_count_query, $data);

					if ( is_object($get_count_result) ) {
						$response['totalCount'] = $get_count_result->result('array');
						$response['totalCount'] = $response['totalCount'][0]['cnt'];
						return $response;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Получение списка результатов предыдущих беременностей
	 */
	function loadPersonPregnancyResultGrid($data) {
		$params = array('PersonPregnancy_id' => $data['PersonPregnancy_id']);
		$query = "
			select
				PPR.PersonPregnancyResult_id as \"PersonPregnancyResult_id\",
				PPR.PersonPregnancy_id as \"PersonPregnancy_id\",
				1 as \"RecordStatus_Code\",
				PPR.PersonPregnancyResult_Num as \"PersonPregnancyResult_Num\",
				PPR.PersonPregnancyResult_Year as \"PersonPregnancyResult_Year\",
				PPR.PersonPregnancyResult_OutcomPeriod as \"PersonPregnancyResult_OutcomPeriod\",
				PPR.PersonPregnancyResult_WeigthChild as \"PersonPregnancyResult_WeigthChild\",
				PPR.PersonPregnancyResult_AgeChild as \"PersonPregnancyResult_AgeChild\",
				PPR.PersonPregnancyResult_Descr as \"PersonPregnancyResult_Descr\",
				PR.PregnancyResult_id as \"PregnancyResult_id\",
				PR.PregnancyResult_Name as \"PregnancyResult_Name\",
				BCR.BirthChildResult_id as \"BirthChildResult_id\",
				BCR.BirthChildResult_Name as \"BirthChildResult_Name\",
				CSR.ChildStateResult_id as \"ChildStateResult_id\",
				CSR.ChildStateResult_Name as \"ChildStateResult_Name\"
			from
				v_PersonPregnancyResult PPR
				left join v_PregnancyResult PR on PR.PregnancyResult_id = PPR.PregnancyResult_id
				left join v_BirthChildResult BCR on BCR.BirthChildResult_id = PPR.BirthChildResult_id
				left join v_ChildStateResult CSR on CSR.ChildStateResult_id = PPR.ChildStateResult_id
			where 
			    PPR.PersonPregnancy_id = :PersonPregnancy_id
		";
		return $this->queryResult($query, $params);
	}
	
	/**
	 * Получение данных для гравидограммы
	 */
	function loadPersonPregnancyGravidogramData($data) {
		$params = array('PersonRegister_id' => $data['PersonRegister_id']);
		$result = array();
		
		//получим все скрининги по записи регистра
		$query = "
			select
				PS.PregnancyScreen_id as \"PregnancyScreen_id\",
				PS.PregnancyScreen_Comment as \"PregnancyScreen_Comment\",
				to_char(PS.PregnancyScreen_setDT, '{$this->dateTimeForm104}') as \"PregnancyScreen_setDate\",
				to_char(PP.PersonPregnancy_setDT, '{$this->dateTimeForm104}') as \"PersonPregnancy_setDT\",
				coalesce(weeks.number, datediff('week', PP.PersonPregnancy_setDT, PS.PregnancyScreen_setDT)) as \"PersonPregnancy_Week\",
				PS.GestationalAge_id as \"GestationalAge_id\",
				PP.PersonPregnancy_Period as \"PersonPregnancy_Period\"
			from
				v_PregnancyScreen PS
				left join v_PersonPregnancy PP on PP.PersonRegister_id=PS.PersonRegister_id
				left join lateral (
					select
						PQ.PregnancyQuestion_AnswerInt as number
					from
						v_PregnancyQuestion PQ
						left join v_QuestionType QT on QT.QuestionType_id = PQ.QuestionType_id
					where
						PQ.PregnancyScreen_id = PS.PregnancyScreen_id
						and PQ.PregnancyQuestion_AnswerInt is not null
						and QT.QuestionType_Code in (358,359,362,363)
					order by QT.QuestionType_Code
					limit 1
				) as weeks on true
			where
				PS.PersonRegister_id = :PersonRegister_id
		";
		$scrinings = $this->queryResult($query, $params);
		
		//по каждому скринингу получим ответы анкеты
		foreach ($scrinings as $scrin) {
			$params = array('PregnancyScreen_id' => $scrin['PregnancyScreen_id']);
			$query = "
				select
					QT.QuestionType_SysNick as \"QuestionType_SysNick\",
					QT.AnswerType_id as \"AnswerType_id\",
					QT.QuestionType_id as \"QuestionType_id\",
					QT.QuestionType_Code as \"QuestionType_Code\",
					QT.QuestionType_Name as \"QuestionType_Name\",
					coalesce(
						cast(PQ.PregnancyQuestion_IsTrue as varchar),
						cast(PQ.PregnancyQuestion_AnswerInt as varchar),
						cast(PQ.PregnancyQuestion_AnswerFloat as varchar),
						PQ.PregnancyQuestion_AnswerText,
						cast(PQ.PregnancyQuestion_ValuesStr as varchar)
					) as \"Answer\"
				from
					v_PregnancyQuestion PQ
					left join v_QuestionType QT on QT.QuestionType_id = PQ.QuestionType_id
				where PQ.PregnancyScreen_id = :PregnancyScreen_id and QT.DispClass_id = 16
			";
			$answers = $this->queryResult($query, $params);
			
			//выделяем нужные ответы для гравидограммы
			foreach($answers as $answer) {
				if (in_array($answer['QuestionType_SysNick'], array(
					'vdm', //высота стояния дна матки
					'childpositiontype', //предлежание плода
					'abdominometry', //окружность живота
					'oedema', //наличие отеков
					'FetusHeartRate', //сердцебиение плода
					'HemoglobinLevel', //гемоглобин
					'BloodSugarLevel', //сахар в крови
					'Proteinuria', //белок в моче
					'RhSensitization', //Резус-сенсибилизация
					'TitrRhClarification', //Титр Rh
					'TitrABOClarification', //Титр АВО
					'SystolicBP', //систолическое давление
					'DiastolicBP' //диастолическое давление
				))
				) {
					$scrin[$answer['QuestionType_SysNick']] = $answer['Answer'];
				}
				//AB0-сенсибилизация
				if ($answer['QuestionType_Code'] == '552') {
					$scrin['avo'] = $answer['Answer'];
				}

				//Резус-сенсибилизация
				if ($answer['QuestionType_Code'] == '415') {
					$scrin['RhSensitization'] = $answer['Answer'];
				}
			}
			$result[] = $scrin;
		}
		return $result;
	}

	/**
	 * Получение данных для формирования основных разделов в дереве сведений о беременности
	 */
	function getPersonPregnancyInfoForTree($data) {
		$info = array(
			'AnketaEvn_id' => null,
			'AnketaLpu_id' => null,
			'AnketaEvnClass' => null,
			'ResultEvn_id' => null,
			'ResultLpu_id' => null,
			'ResultEvnClass' => null,
			'LeaveType_SysNick' => null,
			'PersonPregnancy_id' => null,
			'BirthSpecStac_id' => null,
			'DeathMother_id' => null,
			'BirthCertificate_id' => null,
			'ScreenCount' => 0,
			'ScreenRisk' => '*',
			'PersonPregnancy_RiskDPP' => '*',
		);

		if (!empty($data['PersonRegister_id']) && $data['PersonRegister_id'] > 0) {
			$query = "
				select
					AnketaEvn.Evn_id as \"AnketaEvn_id\",
					AnketaEvn.Lpu_id as \"AnketaLpu_id\",
					AnketaEvn.EvnClass_SysNick as \"AnketaEvnClass\",
					ResultEvn.Evn_id as \"ResultEvn_id\",
					ResultEvn.Lpu_id as \"ResultLpu_id\",
					ResultEvn.EvnClass_SysNick as \"ResultEvnClass\",
					PP.PersonPregnancy_id as \"PersonPregnancy_id\",
					BSS.BirthSpecStac_id as \"BirthSpecStac_id\",
					DM.DeathMother_id as \"DeathMother_id\",
					BC.BirthCertificate_id as \"BirthCertificate_id\",
					CountScreen.Value as \"ScreenCount\",
					LastScreen.PregnancyScreen_RiskPerPat as \"ScreenRisk\",
					PP.PersonPregnancy_RiskDPP as \"PersonPregnancy_RiskDPP\"
				from
					v_PersonRegister PR
					left join v_PersonPregnancy PP on PP.PersonRegister_id = PR.PersonRegister_id
					left join v_BirthSpecStac BSS on BSS.PersonRegister_id = PR.PersonRegister_id
					left join v_DeathMother DM on DM.PersonRegister_id = PR.PersonRegister_id
					left join v_BirthCertificate BC on BC.PersonRegister_id = PR.PersonRegister_id
					left join v_Evn AnketaEvn on AnketaEvn.Evn_id = PP.Evn_id
					left join v_Evn ResultEvn on ResultEvn.Evn_id = coalesce(BSS.EvnSection_id, BSS.Evn_id)
					left join lateral (
						select 
						    count(*) as Value
						from 
						    v_PregnancyScreen PS
						where 
						    PS.PersonRegister_id = PR.PersonRegister_id
						limit 1
					) CountScreen on true
					left join lateral (
						select 
						    PS.PregnancyScreen_RiskPerPat
						from 
						    v_PregnancyScreen PS
						where 
						    PS.PersonRegister_id = PR.PersonRegister_id
						order by 
						    PS.PregnancyScreen_setDT desc
						limit 1
					) LastScreen on true
				where
					PR.PersonRegister_id = :PersonRegister_id
				limit 1
			";
			//echo getDebugSQL($query, $data);exit;
			$allInfo = $this->getFirstRowFromQuery($query, $data, true);
			if ($allInfo === false) {
				//return $this->createError('', 'Ошибка при получении сведений о беременности');
				return array();
			}
			$info = array_merge($info, !empty($allInfo)?$allInfo:array());
		} else if (!empty($data['BirthSpecStac_id'])) {
			$query = "
				select
					ResultEvn.Evn_id as \"ResultEvn_id\",
					ResultEvn.Lpu_id as \"ResultLpu_id\",
					ResultEvn.EvnClass_SysNick as \"ResultEvnClass\",
					BSS.BirthSpecStac_id as \"BirthSpecStac_id\"
				from
					v_BirthSpecStac BSS
					left join v_Evn ResultEvn on ResultEvn.Evn_id = coalesce(BSS.EvnSection_id, BSS.Evn_id)
				where
					BSS.BirthSpecStac_id = :BirthSpecStac_id
				limit 1
			";
			$resultInfo = $this->getFirstRowFromQuery($query, $data);
			if ($resultInfo === false) {
				return array();
			}
			$info = array_merge($info, $resultInfo);
		}

		return $info;
	}

	/**
	 * Получение дерева для сведений о беременности
	 */
	function loadPersonPregnancyTree($data) {
		$nodes = array();

		$userGroups = array();
		if (!empty($_SESSION['groups']) && is_string($_SESSION['groups'])) {
			$userGroups = explode('|', $_SESSION['groups']);
		}
		$allowPregnancyRegisterAccess = count(array_intersect(array('OperPregnRegistry','RegOperPregnRegistry'), $userGroups)) > 0;

		$createCategoryMethod = !empty($data['createCategoryMethod'])?$data['createCategoryMethod']:null;
		$allowCreateButton = (!empty($createCategoryMethod) && !empty($data['allowCreateButton']))?$data['allowCreateButton']:false;

		$deleteCategoryMethod = !empty($data['deleteCategoryMethod'])?$data['deleteCategoryMethod']:null;
		$allowDeleteButton = (!empty($deleteCategoryMethod) && !empty($data['allowDeleteButton']))?$data['allowDeleteButton']:false;

		if ($data['node'] == 'root') {
			$items = array(
				'PersonPregnancy' => array('id' => 'PersonPregnancy', 'object' => 'PersonPregnancy', 'text' => 'Сведения о беременности', 'leaf' => false),
			);

			if (!empty($data['PersonRegister_id'])) {
				// Общий риск по беременной есть сумма риска анкеты и последнего скрининга
				$SumRisk = 0;
				$resp_risk = $this->queryResult("
					select
						LastScreen.PregnancyScreen_RiskPerPat as \"ScreenRisk\",
						PP.PersonPregnancy_RiskDPP as \"PersonPregnancy_RiskDPP\"
					from
						v_PersonRegister PR
						left join v_PersonPregnancy PP on PP.PersonRegister_id = PR.PersonRegister_id
						left join lateral (
							select
							    PS.PregnancyScreen_RiskPerPat
							from 
							    v_PregnancyScreen PS
							where 
							    PS.PersonRegister_id = PR.PersonRegister_id
							order by 
							    PS.PregnancyScreen_setDT desc
						) LastScreen on true
					where
						PR.PersonRegister_id = :PersonRegister_id
				", array(
					'PersonRegister_id' => $data['PersonRegister_id']
				));
				if (!empty($resp_risk[0]['ScreenRisk'])) {
					$SumRisk += $resp_risk[0]['ScreenRisk'];
				}
				if (!empty($resp_risk[0]['PersonPregnancy_RiskDPP'])) {
					$SumRisk += $resp_risk[0]['PersonPregnancy_RiskDPP'];
				}

				$items['PersonPregnancy']['text'] .= " (Общий риск {$SumRisk})";
			}

			$nodes = array_values($items);
		} else {
			switch($data['object']) {
				case 'PersonPregnancy':
					$allowCategories = isset($data['allowCategories'])?$data['allowCategories']:null;
					$allowCreateCategories = isset($data['allowCreateCategories'])?$data['allowCreateCategories']:null;
					$unsetCategories = array();

					$info = $this->getPersonPregnancyInfoForTree($data);

					$items = array(
						'Anketa' => array('id'=>'Anketa','object' =>'Anketa','text' => 'Анкета','leaf' => true,'grid'=>false),
						'ScreenList' => array('id'=>'ScreenList','object' =>'ScreenList','text' => 'Скрининги','leaf' => true,'grid'=>false),
						'EvnList' => array('id'=>'EvnList','object' =>'EvnList','text' => 'Случаи лечения','leaf' => true,'grid'=>true),
						'ConsultationList' => array('id'=>'ConsultationList','object' =>'ConsultationList','text' => 'Консультации','leaf' => true,'grid'=>true),
						'ResearchList' => array('id'=>'ResearchList','object' =>'ResearchList','text' => 'Исследования','leaf' => true,'grid'=>true),
						'Certificate' => array('id'=>'Certificate','object' =>'Certificate','text' => 'Родовой сертификат','leaf' => true,'grid'=>false),
						'Result' => array('id'=>'Result','object' =>'Result','text' => 'Исход','leaf' => true,'grid'=>false),
						'DeathMother' => array('id'=>'DeathMother','object' =>'DeathMother','text' => 'Случай материнской смертности','leaf' => true,'grid'=>false),
					);

					if (!$allowCreateCategories) {
						if (empty($info['PersonPregnancy_id'])) {
							$allowCreateCategories = array('Anketa');
						} else {
							$allowCreateCategories = array('Anketa','ScreenList','Certificate','Result','DeathMother');
						}
					}

					foreach($items as $name => &$item) {
						$allowCreate = ($allowCreateButton && $allowCreateCategories && in_array($name, $allowCreateCategories));
						switch($name) {
							case 'Anketa':
								$access = false;
								if (!empty($data['Evn_id'])) {
									$access = (empty($info['AnketaEvn_id']) || $data['Evn_id'] == $info['AnketaEvn_id']);
								} else if($allowPregnancyRegisterAccess) {
									$access = (empty($info['AnketaEvn_id']) || $data['Lpu_id'] == $info['AnketaLpu_id']);
								}

								$item['leaf'] = empty($info['PersonPregnancy_id']);

								$item['readOnly'] = !$access;
								if (empty($info['PersonPregnancy_id'])) {
									if ($allowCreate) {
										$item['text'] .= " <span class=\"link create\" onclick=\"{$createCategoryMethod}('Anketa')\">Создать</span>";
									}
									$item['key'] = null;
								} else {
									$item['key'] = $info['PersonPregnancy_id'];
									$item['text'] .= ' (Перинатальный риск '.$info['PersonPregnancy_RiskDPP'].')';
									if ($access && $allowDeleteButton) {
										$item['text'] .= " <span class=\"link delete\" onclick=\"{$deleteCategoryMethod}('Anketa', {$info['PersonPregnancy_id']})\">Удалить</span>";
									}
								}
								break;
							case 'ScreenList':
								if ($info['ScreenCount'] > 0) {
									$item['leaf'] = false;
									$item['text'] .= " (Перинатальный риск {$info['ScreenRisk']})";
								}
								if ($allowCreate) {
									$item['text'] .= " <span class=\"link create\" onclick=\"{$createCategoryMethod}('Screen')\">Создать</span>";
								}
								//ссылка на гравидограмму
								//todo: когда будет готово - убрать
								if ($this->isDebug) {
									$paramStr = "{PersonRegister_id: ".$data['PersonRegister_id']."}";
									$item['text'] .= " <span class=\"link create\" onclick=\"getWnd('swPregnancyGravidogamExt6').show($paramStr)\">Гравидограмма</span>";
								}
								break;
							case 'Certificate':
								$access = $allowPregnancyRegisterAccess;

								$item['readOnly'] = !$access;
								if (empty($info['BirthCertificate_id'])) {
									if ($allowCreate) {
										$item['text'] .= " <span class=\"link create\" onclick=\"{$createCategoryMethod}('Certificate')\">Создать</span>";
									}
									$item['key'] = null;
								} else {
									if ($access && $allowDeleteButton) {
										$item['text'] .= " <span class=\"link delete\" onclick=\"{$deleteCategoryMethod}('Certificate', {$info['BirthCertificate_id']})\">Удалить</span>";
									}
									$item['key'] = $info['BirthCertificate_id'];
								}
								break;
							case 'Result':
								$access = false;
								if (!empty($data['Evn_id'])) {
									$access = (empty($info['ResultEvn_id']) || $data['Evn_id'] == $info['ResultEvn_id']);
								} else if($allowPregnancyRegisterAccess) {
									$access = (empty($info['ResultEvn_id']) || $data['Lpu_id'] == $info['ResultLpu_id']);
								}
								//для исхода созданного в регистре ЭКО
								$resp_risk = $this->queryResult("						
									select 1 as 
									    \"hasEcoBirthSpecStac\"
									from 
									    v_PersonPregnancy PP 
									    left join v_PersonRegisterEcoSluchData Eco on Eco.PersonRegisterEco_id=PP.PersonRegisterEco_id 
									    inner join v_PersonRegister PR on PR.PersonRegister_id=PP.PersonRegister_id
									where 
									    PP.PersonRegister_id=:PersonRegister_id and PP.Person_id=PR.Person_id
									and 
									    Eco.BirthSpecStac_id is not null
								", array(
									'PersonRegister_id' => $data['PersonRegister_id']			
								));
								if ($access && !empty($resp_risk[0]['hasEcoBirthSpecStac'])) {
									$access = $resp_risk[0]['hasEcoBirthSpecStac'] = 1 ? false : true;
								}													
								
								$item['readOnly'] = !$access;
								if (empty($info['BirthSpecStac_id'])) {
									if ($allowCreate) {
										$item['text'] .= " <span class=\"link create\" onclick=\"{$createCategoryMethod}('Result')\">Создать</span>";
									}
									$item['key'] = null;
								} else {
									$item['key'] = $info['BirthSpecStac_id'];

									$risk = $this->recalculatePregnancyQuestionBirthSpecStacRisk($info);
									$item['text'] .= " (Интранатальные факторы риска {$risk})";

									if ($access && $allowDeleteButton) {
										$item['text'] .= " <span class=\"link delete\" onclick=\"{$deleteCategoryMethod}('Result', {$info['BirthSpecStac_id']})\">Удалить</span>";
									}
								}
								break;
							case 'DeathMother':
								$access = ($info['ResultEvnClass'] != 'EvnSection');

								$item['readOnly'] = !$access;
								if (empty($info['DeathMother_id'])) {
									if ($allowCreate) {
										$item['text'] .= " <span class=\"link create\" onclick=\"{$createCategoryMethod}('DeathMother')\">Создать</span>";
									}
									$item['key'] = null;
								} else {
									if ($access && $allowDeleteButton) {
										$item['text'] .= " <span class=\"link delete\" onclick=\"{$deleteCategoryMethod}('DeathMother', {$info['DeathMother_id']})\">Удалить</span>";
									}
									$item['key'] = $info['DeathMother_id'];
								}
								if (empty($item['key']) && !$allowCreate) {
									$unsetCategories[] = $name;
								}
								break;
						}
					}
					unset($name);

					foreach($items as $name => &$item) {
						if ($allowCategories && !in_array($name, $allowCategories)) {
							$unsetCategories[] = $name;
						}
						/*if (empty($item['key']) && !$item['grid'] && $allowCreateCategories && !in_array($name, $allowCreateCategories)) {
							$unsetCategories[] = $name;
						}*/
					}
					foreach(array_unique($unsetCategories) as $name) {
						unset($items[$name]);
					}

					$nodes = array_values($items);
					break;
				case 'Anketa':
					if (!empty($data['PersonRegister_id'])) {
						$info = $this->getFirstRowFromQuery("
							select 
							    E.Evn_id as \"Evn_id \", 
							    E.Lpu_id as \"Lpu_id\", 
							    PP.PersonPregnancy_id \"PersonPregnancy_id\"
							from 
							    v_PersonPregnancy PP
							    left join v_Evn E on E.Evn_id = PP.Evn_id
							where 
							    PP.PersonRegister_id = :PersonRegister_id
							limit 1
						", $data);
						if (is_array($info)) {
							$key = $info['PersonPregnancy_id'];
							$readOnly = ($key>0 && !empty($data['Evn_id']))?($data['Evn_id'] != $info['Evn_id'] || $data['Lpu_id'] != $info['Lpu_id']):false;
							$nodes = array(
								array('id'=>'AnketaCommonData','object'=>'AnketaCommonData','text'=>'Общие сведения','leaf'=>true,'readOnly'=>$readOnly,'key'=>$key),
								array('id'=>'AnketaFatherData','object'=>'AnketaFatherData','text'=>'Сведения об отце','leaf'=>true,'readOnly'=>$readOnly,'key'=>$key),
								array('id'=>'AnketaAnamnesData','object'=>'AnketaAnamnesData','text'=> 'Акушерский анамнез','leaf'=>true,'readOnly'=>$readOnly,'key'=>$key),
								array('id'=>'AnketaExtragenitalDisease','object' =>'AnketaExtragenitalDisease','text'=>'Экстрагенитальные заболевания','leaf' => true, 'readOnly' => $readOnly,'key'=>$key)
							);
						}
					}
					break;
				case 'ScreenList':
					if (!empty($data['PersonRegister_id']) && $data['PersonRegister_id'] > 0) {
						$query = "
							select
								PS.PregnancyScreen_id as \"PregnancyScreen_id\",
								to_char(PS.PregnancyScreen_setDT, '{$this->dateTimeForm104}') as \"PregnancyScreen_setDate\",
								coalesce (PS.PregnancyScreen_RiskPerPat, 0) as \"PregnancyScreen_RiskPerPat\",
								coalesce (weeks.number::varchar, '*') as \"weeks\",
								E.Evn_id as \"Evn_id\",
								E.Lpu_id as \"Lpu_id\"
							from
								v_PregnancyScreen PS
								left join v_Evn E on E.Evn_id = PS.Evn_id
								left join lateral (
									select
										PQ.PregnancyQuestion_AnswerInt as number
									from
										v_PregnancyQuestion PQ
										left join v_QuestionType QT on QT.QuestionType_id = PQ.QuestionType_id
									where
										PQ.PregnancyScreen_id = PS.PregnancyScreen_id
									and 
								  	    PQ.PregnancyQuestion_AnswerInt is not null
									and 
									    QT.QuestionType_Code in (358,359,362,363)
									order by QT.QuestionType_Code
									limit 1
								) weeks on true
							where
								PS.PersonRegister_id = :PersonRegister_id
							order by
								PS.PregnancyScreen_setDT
						";
						$list = $this->queryResult($query, $data);
						foreach($list as $item) {
							$readOnly = !empty($data['Evn_id'])?($data['Evn_id'] != $item['Evn_id'] || $data['Lpu_id'] != $item['Lpu_id']):false;
							$text = "{$item['PregnancyScreen_setDate']}, {$item['weeks']} нед., Пер. риск {$item['PregnancyScreen_RiskPerPat']}";
							if (!$readOnly && $allowDeleteButton) {
								$text .= " <span class=\"link delete\" onclick=\"{$deleteCategoryMethod}('Screen', {$item['PregnancyScreen_id']})\">Удалить</span>";
							}
							$nodes[] = array(
								'id' => 'PregnancyScreen_'.$item['PregnancyScreen_id'],
								'object' => 'Screen',
								'key' => $item['PregnancyScreen_id'],
								'text' => $text,
								'date' => $item['PregnancyScreen_setDate'],
								'readOnly' => $readOnly,
								'leaf' => true
							);
						}
					}
					break;
			}
		}

		$query = "
                  update 
                      PersonRegister 
                  set 
                      PersonRegister_OpenDT = getdate()
                  where 
                      PersonRegister_id = :PersonRegister_id
        ";
		//сохраняем дату открытия		
		$this->db->query($query, array(
			'PersonRegister_id' => $data['PersonRegister_id']
		));		
		return $nodes;
	}

	/**
	 * Получение данных по умолчанию для анкеты по беременности
	 */
	function loadPersonPregnancyDefaults($data) {
		$params = array('PersonRegister_id' => $data['PersonRegister_id']);
		$query = "
			select
				PP.PersonPregnancy_id as \"PersonPregnancy_id\",
				PR.PersonRegister_id as \"PersonRegister_id\",
				PR.PersonRegister_Code as \"PersonRegister_Code\",
				to_char(PR.PersonRegister_setDate, '{$this->dateTimeForm104}') as \"PersonRegister_setDate\"
			from
				v_PersonRegister PR
				left join v_PersonPregnancy PP on PP.PersonRegister_id = PR.PersonRegister_id
			where
				PR.PersonRegister_id = :PersonRegister_id
			limit 1
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение значений основных полей формы для редактирования записи в регистре беременных
	 */
	function loadPersonPregnancyForm($data) {
		$params = array('PersonPregnancy_id' => $data['PersonPregnancy_id']);
		$query = "
			select
				PP.PersonPregnancy_id as \"PersonPregnancy_id\",
				PP.PersonRegister_id as \"PersonRegister_id\",
				PP.Person_id as \"Person_id\",
				PP.RiskType_id as \"RiskType_id\",
				PP.PersonPregnancy_RiskDPP as \"PersonPregnancy_RiskDPP\",
				PP.Evn_id as \"Evn_id\",
				to_char(PR.PersonRegister_setDate, '{$this->dateTimeForm104}') as \"PersonRegister_setDate\",
				PP.PersonPregnancy_Period as \"PersonPregnancy_Period\",
				to_char(PP.PersonPregnancy_begMensDate, '{$this->dateTimeForm104}') as \"PersonPregnancy_begMensDate\",
				to_char(PP.PersonPregnancy_endMensDate, '{$this->dateTimeForm104}') as \"PersonPregnancy_endMensDate\",
				to_char(PP.PersonPregnancy_birthDate, '{$this->dateTimeForm104}') as \"PersonPregnancy_birthDate\",
				to_char(PP.PersonPregnancy_birthDate, '{$this->dateTimeForm104}') as \"PersonPregnancy_birthDate\",
				PR.PersonRegister_Code as \"PersonRegister_Code\",
				PR.Lpu_iid as \"Lpu_iid\",
				PR.MedPersonal_iid as \"MedPersonal_iid\",
				PP.PersonPregnancy_Phone as \"PersonPregnancy_Phone\",
				PP.PersonPregnancy_PhoneWork as \"PersonPregnancy_PhoneWork\",
				PP.PersonPregnancyEducation_id as \"PersonPregnancyEducation_id\",
				PP.Post_id as \"Post_id\",
				PP.PregnancyFamilyStatus_id as \"PregnancyFamilyStatus_id\",
				PP.PersonPregnancy_Height as \"PersonPregnancy_Height\",
				PP.PersonPregnancy_Weight as \"PersonPregnancy_Weight\",
				case when PP.PersonPregnancy_IsWeight25 = 2 then 1 else 0 end as \"PersonPregnancy_IsWeight25\",
				PP.BloodGroupType_id as \"BloodGroupType_id\",
				PP.RhFactorType_id as \"RhFactorType_id\",
				PP.Person_did as \"Person_did\",
				case when PP.Person_did is not null
					then rtrim(dad.Person_SurName) || ' ' || rtrim(dad.Person_FirName) ||rtrim(coalesce(' '||dad.Person_SecName,''))
					else PP.PersonPregnancy_dadFIO
				end as \"PersonPregnancy_dadFIO\",
				PP.PersonPregnancy_dadAge as \"PersonPregnancy_dadAge\",
				PP.PersonPregnancy_dadAddress as \"PersonPregnancy_dadAddress\",
				PP.PersonPregnancy_dadPhone as \"PersonPregnancy_dadPhone\",
				PP.BloodGroupType_dadid as \"BloodGroupType_dadid\",
				PP.RhFactorType_dadid as \"RhFactorType_dadid\",
				PP.Org_did as \"Org_did\",
				PP.Diag_id as \"Diag_id\",
				PP.PregnancyResult_id as \"PregnancyResult_id\",
				PP.Post_aid as \"Post_aid\",
				PP.PersonDisp_id as \"PersonDisp_id\"
			from
				v_PersonPregnancy PP
				inner join v_PersonRegister PR on PR.PersonRegister_id = PP.PersonRegister_id
				left join v_PersonState dad on dad.Person_id = PP.Person_did
			where
				PP.PersonPregnancy_id = :PersonPregnancy_id
			limit 1
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение ответов на вопросы из анкеты по беременности
	 */
	function loadPregnancyQuestionData($data) {
		$params = array();
		$filter = "1=1";

		if (!empty($data['DispClass_id'])) {
			$filter .= " and QT.DispClass_id = :DispClass_id";
			$params['DispClass_id'] = $data['DispClass_id'];
		}
		if (!empty($data['PersonRegister_id'])) {
			$filter .= " and PQ.PersonRegister_id = :PersonRegister_id";
			$params['PersonRegister_id'] = $data['PersonRegister_id'];
		}
		if (!empty($data['PregnancyScreen_id'])) {
			$filter .= " and PQ.PregnancyScreen_id = :PregnancyScreen_id";
			$params['PregnancyScreen_id'] = $data['PregnancyScreen_id'];
		}

		$query = "
			select
				QT.AnswerType_id as \"AnswerType_id\",
				QT.QuestionType_id as \"QuestionType_id\",
				QT.QuestionType_Code as \"QuestionType_Code\",
				QT.QuestionType_Name as \"QuestionType_Name\",
				coalesce(
					PQ.PregnancyQuestion_IsTrue::varchar,
					PQ.PregnancyQuestion_AnswerInt::varchar,
					PQ.PregnancyQuestion_AnswerFloat::varchar,
					PQ.PregnancyQuestion_AnswerText,
					PQ.PregnancyQuestion_ValuesStr::varchar
				) as \"Answer\"
			from
				v_PregnancyQuestion PQ
				left join v_QuestionType QT on QT.QuestionType_id = PQ.QuestionType_id
			where
				{$filter}
		";
		$resp = $this->queryResult($query, $params);

		$response = array();
		foreach($resp as $item) {
			$key = 'QuestionType_'.$item['QuestionType_id'];
			$response[$key] = $item['Answer'];
			if ($item['AnswerType_id'] == 2 && $item['QuestionType_Name'] == 'Другое' && !empty($item['Answer'])) {
				$key = 'QuestionType_'.$item['QuestionType_id'].'_check';
				$response[$key] = 2;
			}
		}

		return $response;
	}

	/**
	 * Получение данных для записи в регистре беременных
	 */
	function loadPersonPregnancy($data) {
		if (empty($data['PersonPregnancy_id'])) {
			$response = $this->loadPersonPregnancyDefaults($data);
		} else {
			$response = $this->loadPersonPregnancyForm($data);
			if (!is_array($response) || count($response) == 0) {
				return $this->createError('','Ошибка при получении данных записи из регистра беременных');
			}

			$PregnancyQuestionData = $this->loadPregnancyQuestionData(array(
				'PersonRegister_id' => $data['PersonRegister_id'],
				'DispClass_id' => 14
			));
			$response[0] = array_merge($response[0], $PregnancyQuestionData);
		}

		return $response;
	}

	/**
	 * Получение идентификатора записи в базовом регистре по идентификатору регистра беременных
	 */
	function getPersonRegisterId($data) {
		$params = array('PersonPregnancy_id' => $data['PersonPregnancy_id']);
		$query = "
			select
			    PersonRegister_id
			from 
			    v_PersonPregnancy
			where 
			    PersonPregnancy_id = :PersonPregnancy_id
			limit 1
		";
		return $this->getFirstResultFromQuery($query, $params, true);
	}

	/**
	 * Выполнение проверок перед сохранением записи в регистре беременных
	 */
	function validatePersonRegister($data) {
		if (empty($data['PersonRegister_setDate']) || empty($data['Person_id'])) {
			return array(array('success' => true, 'Error_Msg' => ''));
		}
		if (!empty($data['PersonRegister_disDate']) && strtotime($data['PersonRegister_disDate']) < strtotime($data['PersonRegister_setDate'])) {
			return $this->createError('','Дата исключения из регистра не может быть меньше дате включения в регистр');
		}

		if (empty($data['PersonRegister_id'])) {
			$PersonInfo = $this->getFirstRowFromQuery("
				select 
				    PS.Person_id as \"Person_id\",
				    PS.Sex_id as \"Sex_id\"
				from 
				  v_PersonState PS
				where PS.Person_id = :Person_id
				limit 1
			", $data);
			if (!is_array($PersonInfo)) {
				return $this->createError('','Ошибка при получении данных пациента');
			}
			if ($PersonInfo['Sex_id'] == 1) {
				return $this->createError('','Не возможно добавить пациента мужского пола в регистр беременных');
			}
		}

		$query = "
		    with cte as 
		    (
		        select 
		            dateadd('year', 50, dbo.tzGetDate()) as bigdate
		        limit 1
		    )
			select
				count(PR.PersonRegister_id) as cnt
			from
			 	v_PersonRegister PR
				inner join v_PersonRegisterType PRT on PRT.PersonRegisterType_id = PR.PersonRegisterType_id
			where
				PR.Person_id = :Person_id
				and PRT.PersonRegisterType_SysNick ilike 'pregnancy'
				and PR.PersonRegister_id <> coalesce (:PersonRegister_id, cast(0 as bigint))
				and PR.PersonRegister_setDate >= dateadd('month', -11, :PersonRegister_setDate)
				and PR.PersonRegister_setDate <= coalesce(:PersonRegister_disDate, (select bigdate from cte))
				and coalesce(PR.PersonRegister_disDate, (select bigdate from cte)) >= :PersonRegister_setDate
		";
		$params = array(
			'Person_id' => $data['Person_id'],
			'PersonRegister_id' => !empty($data['PersonRegister_id'])?$data['PersonRegister_id']:null,
			'PersonRegister_setDate' => $data['PersonRegister_setDate'],
			'PersonRegister_disDate' => !empty($data['PersonRegister_disDate'])?$data['PersonRegister_disDate']:null,
		);
		$count = $this->getFirstResultFromQuery($query, $params);
		if ($count === false) {
			return $this->createError('','Ошибка при проверке пересечений с другими записями регистра беременных');
		}
		if ($count > 0) {
			return $this->createError('','Уже существуют запись регистра беременных в указанный период');
		}
		return array(array('success' => true, 'Error_Msg' => ''));
	}

	/**
	 * Сохранение данных базового регистра
	 */
	function savePersonRegister($data, $isAllowTransaction = true, $force = false) {
		$resp = $this->validatePersonRegister($data);
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}

		$OutCauseListOnResult = array('birth','SponAbort','IndAbort','EctPregn');
		if (!$force && !empty($data['PersonRegister_id']) && !empty($data['PersonRegisterOutCause_SysNick']) &&
			in_array($data['PersonRegisterOutCause_SysNick'], $OutCauseListOnResult)
		) {
			$query = "
				select
					Cause.PersonRegisterOutCause_Name as \"PersonRegisterOutCause_Name\",
					Cause.PersonRegisterOutCause_SysNick as \"PersonRegisterOutCause_SysNick\"
				from
					v_PersonRegister PR
					left join v_PersonRegisterOutCause Cause on Cause.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
				where 
				    PR.PersonRegister_id = :PersonRegister_id
				limit 1
			";
			$checkInfo = $this->getFirstRowFromQuery($query, $data);
			if (!is_array($checkInfo)) {
				return $this->createError('','Ошибка при получении данных базового регистра');
			}
			if ($checkInfo['PersonRegisterOutCause_SysNick'] == 'Death') {
				//Данные о закрытии регистра не изменяются
				return array(array('success' => true, 'PersonRegister_id' => $data['PersonRegister_id']));
			}
		}
		if (!$force && !empty($data['PersonRegister_id']) &&
			(array_key_exists('PersonRegisterOutCause_id', $data) || array_key_exists('PersonRegisterOutCause_SysNick', $data)) &&
			empty($data['PersonRegisterOutCause_id']) && empty($data['PersonRegisterOutCause_SysNick'])
		) {
			$query = "
				select
					Cause.PersonRegisterOutCause_Code as \"PersonRegisterOutCause_Code\"
				from
					v_PersonRegister PR
					left join v_PersonRegisterOutCause Cause on Cause.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
				where PR.PersonRegister_id = :PersonRegister_id
				limit 1
			";
			$checkInfo = $this->getFirstRowFromQuery($query, $data);
			if (!is_array($checkInfo)) {
				return $this->createError('','Ошибка при получении данных базового регистра');
			}
			if (!empty($checkInfo['PersonRegisterOutCause_Code']) && !in_array($checkInfo['PersonRegisterOutCause_Code'], array('2','7'))) {
				return $this->createError('','Вернуть в регистр можно только запись с причиной исключения "Смена места жительства" или "Нет сведений"');
			}
		}

		$data['autoExcept'] = true;

		$this->load->model('PersonRegister_model');
		$this->PersonRegister_model->isAllowTransaction = $isAllowTransaction;
		$response = $this->PersonRegister_model->savePersonRegister($data);
		$this->PersonRegister_model->isAllowTransaction = true;

		if (!$this->isSuccessful($response)) {
			return $response;
		}
		if (isset($response[0]) && !empty($response[0]['Alert_Msg'])) {
			return $this->createError('',$response[0]['Alert_Msg']);
		}
		if (!isset($response[0]) || empty($response[0]['PersonRegister_id'])) {
			return $this->createError('','Ошибка при сохранении базового регистра');
		}

		return $response;
	}

	/**
	 * Сохранение данных для записи регистра беременных
	 */
	function savePersonPregnancy($data, $isAllowTransaction = true) {
		$response = array('success' => true, 'PersonPregnancy_id' => null, 'PersonRegister_id' => null);

		$this->isAllowTransaction = $isAllowTransaction;
		$this->beginTransaction();

		if (date_create($data['PersonRegister_setDate']) > date_create(date('Y-m-d'))) {
			$this->rollbackTransaction();
			return $this->createError('','Дата постановки на учет не может быть больше текущей даты');
		}

		//Базовый регистр
		$params = array(
			'PersonRegister_id' => !empty($data['PersonRegister_id'])?$data['PersonRegister_id']:null,
			'PersonRegister_setDate' => $data['PersonRegister_setDate'],
			'PersonRegister_Code' => !empty($data['PersonRegister_Code'])?$data['PersonRegister_Code']:null,
			'Diag_id' => !empty($data['Diag_id'])?$data['Diag_id']:null,
			'RiskType_id' => !empty($data['RiskType_id'])?$data['RiskType_id']:null,
			'PregnancyResult_id' => !empty($data['PregnancyResult_id'])?$data['PregnancyResult_id']:null,
			'Person_id' => $data['Person_id'],
			'Lpu_iid' => $data['Lpu_iid'],
			'MedPersonal_iid' => $data['MedPersonal_iid'],
			'PersonRegisterType_SysNick' => 'pregnancy',
			'MorbusType_SysNick' => 'pregnancy',
			'pmUser_id' => $data['pmUser_id'],
			'session' => $data['session'],
		);
		$resp = $this->savePersonRegister($params, false);
		if (!$this->isSuccessful($resp)) {
			$this->rollbackTransaction();
			return $resp;
		}
		$data['PersonRegister_id'] = $response['PersonRegister_id'] = $resp[0]['PersonRegister_id'];

		//Регистр беременных
		if (empty($data['PersonPregnancy_id'])) {
			$procedure = 'p_PersonPregnancy_ins';
		} else {
			$procedure = 'p_PersonPregnancy_upd';
		}
		$query = "
		    select
		        PersonPregnancy_id as \"PersonPregnancy_id\", 
		        Error_Code as \"Error_Code\", 
		        Error_Message as \"Error_Msg\"
		    from  {$procedure}
		    (
		        PersonPregnancy_id := :PersonPregnancy_id,
		        PersonDisp_id := :PersonDisp_id,
				Person_id := :Person_id,
				PersonPregnancy_RiskDPP := :PersonPregnancy_RiskDPP,
				PersonPregnancy_setDT := :PersonPregnancy_setDT,
				PersonPregnancy_Period := :PersonPregnancy_Period,
				Lpu_oid := :Lpu_oid,
				PersonRegister_id := :PersonRegister_id,
				Evn_id := :Evn_id,
				PersonPregnancy_dispDate := :PersonPregnancy_dispDate,
				PersonPregnancy_birthDate := :PersonPregnancy_birthDate,
				PersonPregnancy_begMensDate := :PersonPregnancy_begMensDate,
				PersonPregnancy_endMensDate := :PersonPregnancy_endMensDate,
				PersonPregnancy_Phone := :PersonPregnancy_Phone,
				PersonPregnancy_PhoneWork := :PersonPregnancy_PhoneWork,
				PersonPregnancyEducation_id := :PersonPregnancyEducation_id,
				Post_id := :Post_id,
				PregnancyFamilyStatus_id := :PregnancyFamilyStatus_id,
				PersonPregnancy_Height := :PersonPregnancy_Height,
				PersonPregnancy_Weight := :PersonPregnancy_Weight,
				PersonPregnancy_IsWeight25 := :PersonPregnancy_IsWeight25,
				BloodGroupType_id := :BloodGroupType_id,
				RhFactorType_id := :RhFactorType_id,
				Person_did := :Person_did,
				PersonPregnancy_dadFIO := :PersonPregnancy_dadFIO,
				PersonPregnancy_dadAge := :PersonPregnancy_dadAge,
				PersonPregnancy_dadAddress := :PersonPregnancy_dadAddress,
				PersonPregnancy_dadPhone := :PersonPregnancy_dadPhone,
				BloodGroupType_dadid := :BloodGroupType_dadid,
				RhFactorType_dadid := :RhFactorType_dadid,
				Org_did := :Org_did,
				pmUser_id := :pmUser_id
		    )
		";
		$params = array(
			'PersonPregnancy_id' => !empty($data['PersonPregnancy_id'])?$data['PersonPregnancy_id']:null,
			'PersonDisp_id' => !empty($data['PersonDisp_id'])?$data['PersonDisp_id']:null,
			'Person_id' => $data['Person_id'],
			'PersonPregnancy_RiskDPP' => !empty($data['PersonPregnancy_RiskDPP'])?$data['PersonPregnancy_RiskDPP']:null,
			'Evn_id' => !empty($data['Evn_id'])?$data['Evn_id']:null,
			'Diag_id' => !empty($data['Diag_id'])?$data['Diag_id']:null,
			'PersonPregnancy_setDT' => $data['PersonRegister_setDate'],
			'PersonPregnancy_Period' => !empty($data['PersonPregnancy_Period'])?$data['PersonPregnancy_Period']:null,
			'Lpu_oid' => $data['Lpu_iid'],
			'PersonRegister_id' => $data['PersonRegister_id'],
			'PersonPregnancy_dispDate' => $data['PersonRegister_setDate'],
			'PersonPregnancy_birthDate' => !empty($data['PersonPregnancy_birthDate'])?$data['PersonPregnancy_birthDate']:null,
			'PersonPregnancy_begMensDate' => !empty($data['PersonPregnancy_begMensDate'])?$data['PersonPregnancy_begMensDate']:null,
			'PersonPregnancy_endMensDate' => !empty($data['PersonPregnancy_endMensDate'])?$data['PersonPregnancy_endMensDate']:null,
			'PersonPregnancy_Phone' => !empty($data['PersonPregnancy_Phone'])?$data['PersonPregnancy_Phone']:null,
			'PersonPregnancy_PhoneWork' => !empty($data['PersonPregnancy_PhoneWork'])?$data['PersonPregnancy_PhoneWork']:null,
			'PersonPregnancyEducation_id' => !empty($data['PersonPregnancyEducation_id'])?$data['PersonPregnancyEducation_id']:null,
			'Post_id' => !empty($data['Post_id'])?$data['Post_id']:null,
			'PregnancyFamilyStatus_id' => !empty($data['PregnancyFamilyStatus_id'])?$data['PregnancyFamilyStatus_id']:null,
			'PersonPregnancy_Height' => !empty($data['PersonPregnancy_Height'])?$data['PersonPregnancy_Height']:null,
			'PersonPregnancy_Weight' => !empty($data['PersonPregnancy_Weight'])?$data['PersonPregnancy_Weight']:null,
			'PersonPregnancy_IsWeight25' => (!empty($data['PersonPregnancy_IsWeight25']) && $data['PersonPregnancy_IsWeight25'] == 2)?2:1,
			'BloodGroupType_id' => !empty($data['BloodGroupType_id'])?$data['BloodGroupType_id']:null,
			'RhFactorType_id' => !empty($data['RhFactorType_id'])?$data['RhFactorType_id']:null,
			'Person_did' => !empty($data['Person_did'])?$data['Person_did']:null,
			'PersonPregnancy_dadFIO' => !empty($data['PersonPregnancy_dadFIO'])?$data['PersonPregnancy_dadFIO']:null,
			'PersonPregnancy_dadAge' => !empty($data['PersonPregnancy_dadAge'])?$data['PersonPregnancy_dadAge']:null,
			'PersonPregnancy_dadAddress' => !empty($data['PersonPregnancy_dadAddress'])?$data['PersonPregnancy_dadAddress']:null,
			'PersonPregnancy_dadPhone' => !empty($data['PersonPregnancy_dadPhone'])?$data['PersonPregnancy_dadPhone']:null,
			'BloodGroupType_dadid' => !empty($data['BloodGroupType_dadid'])?$data['BloodGroupType_dadid']:null,
			'RhFactorType_dadid' => !empty($data['RhFactorType_dadid'])?$data['RhFactorType_dadid']:null,
			'Org_did' => !empty($data['Org_did'])?$data['Org_did']:null,
			'pmUser_id' => $data['pmUser_id'],
			'Post_aid' => !empty($data['Post_aid'])?$data['Post_aid']:null
		);
		$resp = $this->queryResult($query, $params);
		if (!$this->isSuccessful($resp)) {
			$this->rollbackTransaction();
			return $resp;
		}
		$data['PersonPregnancy_id'] = $response['PersonPregnancy_id'] = $resp[0]['PersonPregnancy_id'];

		//Результаты предыдущих беременностей
		$PersonPregnancyResultData = array();
		if (!empty($data['PersonPregnancyResultData'])) {
			if (is_array($data['PersonPregnancyResultData'])) {
				$PersonPregnancyResultData = $data['PersonPregnancyResultData'];
			} else if (is_string($data['PersonPregnancyResultData'])) {
				$PersonPregnancyResultData = json_decode($data['PersonPregnancyResultData'], true);
			}
		}

		foreach($PersonPregnancyResultData as $PersonPregnancyResult) {
			$PersonPregnancyResult['PersonPregnancy_id'] = $data['PersonPregnancy_id'];
			$PersonPregnancyResult['pmUser_id'] = $data['pmUser_id'];
			switch($PersonPregnancyResult['RecordStatus_Code']) {
				case 0:
					$PersonPregnancyResult['PersonPregnancyResult_id'] = null;
					$resp = $this->savePersonPregnancyResult($PersonPregnancyResult);
					break;
				case 2:
					$resp = $this->savePersonPregnancyResult($PersonPregnancyResult);
					break;
				case 3:
					$resp = $this->deletePersonPregnancyResult($PersonPregnancyResult);
					break;
			}
			if (!$this->isSuccessful($resp)) {
				$this->rollbackTransaction();
				return $resp;
			}
		}

		//Заполнение анкеты
		$data['DispClass_id'] = 14;
		$resp = $this->savePregnancyQuestionAnswers($data);
		if (!$this->isSuccessful($resp)) {
			$this->rollbackTransaction();
			return $resp;
		}
		
		$this->checkAndSaveQuarantine([
			'Person_id' => $data['Person_id'],
			'pmUser_id' => $data['pmUser_id']
		]);

		//рекомендации по акушерским осложнениям
		$query = "
		    select
		        error_code as \"error_code\",
		        error_message as \"error_msg\"
		    from  p_personregisterobstetricpathologytype_upd
		    (
				ppersonregister_id := :personregister_id,
				ppmuser_id := :pmuser_id
			)
		";
		$params = array(
			'personregister_id' => $data['PersonRegister_id'],
			'pmuser_id' => $data['pmUser_id']
		);
		$resp = $this->queryResult($query, $params);
		if (!$this->isSuccessful($resp)) {
			$this->rollbackTransaction();
			return $resp;
		}

		$query = "
		    select
		        obstetricpathologytype_text as \"ObstetricPathologyType_text\"
		    from  getobstetricpathologytype
		    (
				personregister_id := :personregister_id
			)
		";
		$params = array(
			'personregister_id' => $data['PersonRegister_id']
		);
		$resp = $this->queryResult($query, $params);
		if (!$this->isSuccessful($resp)) {
			$this->rollbackTransaction();
			return $resp;
		}
		$response['ObstetricPathologyType_text'] = $resp;

		$this->isAllowTransaction = $isAllowTransaction;
		$this->commitTransaction();

		return array($response);
	}

	/**
	 * Сохранение ответов из анкеты
	 */
	function savePregnancyQuestionAnswers($data) {
		$params = array(
			'DispClass_id' => $data['DispClass_id'],
			'PersonRegister_id' => $data['PersonRegister_id'],
			'PregnancyScreen_id' => !empty($data['PregnancyScreen_id'])?$data['PregnancyScreen_id']:null,
			'pmUser_id' => $data['pmUser_id']
		);

		$filter = " and PQ.PersonRegister_id = :PersonRegister_id";
		if (empty($params['PregnancyScreen_id'])) {
			$filter .= " and PQ.PregnancyScreen_id is null";
		} else {
			$filter .= " and PQ.PregnancyScreen_id = :PregnancyScreen_id";
		}

		$query = "
			select
				QT.QuestionType_id as \"QuestionType_id\",
				QT.QuestionType_Code as \"QuestionType_Code\",
				QT.AnswerType_id as \"AnswerType_id\",
				QT.AnswerClass_id as \"AnswerClass_id\",
				PQ.PregnancyQuestion_id as \"PregnancyQuestion_id\",
				coalesce(
					PQ.PregnancyQuestion_IsTrue::varchar,
					PQ.PregnancyQuestion_AnswerInt::varchar,
					PQ.PregnancyQuestion_AnswerFloat::varchar,
					PQ.PregnancyQuestion_AnswerText,
					PQ.PregnancyQuestion_ValuesStr::varchar
				) as \"Answer\"
			from v_QuestionType QT
			left join lateral (
				select
					PregnancyQuestion_id,
					PregnancyQuestion_IsTrue,
					PregnancyQuestion_AnswerInt,
					PregnancyQuestion_AnswerFloat,
					PregnancyQuestion_AnswerText,
					PregnancyQuestion_ValuesStr
				from v_PregnancyQuestion PQ
				where PQ.QuestionType_id = QT.QuestionType_id {$filter}
				limit 1
			) PQ on true
			where 
			  QT.DispClass_id = :DispClass_id 
			and 
			  QT.AnswerType_id is not null
		";
		$resp = $this->queryResult($query, $params);
		$questions = array();
		foreach($resp as $item) {
			if ($item['QuestionType_id'] == '774'){
				$s = "";
			}
			$questions[$item['QuestionType_id']] = $item;
		}

		$answers = array();
		if (is_array($data['Answers'])) {
			$answers = $data['Answers'];
		} else if (is_string($data['Answers'])) {
			$answers = json_decode($data['Answers'], true);
		}

		if (!empty($data['DifferentLpu']) && strlen($data['DifferentLpu'])>3){
			if ($answers['774'] != "" && $answers['774'].". " == substr($data['DifferentLpu'], 0, strlen($answers['774'].". "))){
				$data['DifferentLpu'] = substr($data['DifferentLpu'], strlen($answers['774'].". "), strlen($data['DifferentLpu']) - strlen($answers['774'].". "));
			}

			$params = array(
				'DifferentLpu' => $data['DifferentLpu']
			);
			$id = $this->getFirstResultFromQuery("
				select
					LpuDifferent_id
				from Dbo.LpuDifferent
				where LpuDifferent_Name=:DifferentLpu
			", $params);

			if (!empty($id)) {
				$query = "
					INSERT INTO Dbo.LpuDifferent(
						LpuDifferent_Code,
						LpuDifferent_Name,
						pmUser_insID,
						pmUser_updID,
						LpuDifferent_insDT,
						LpuDifferent_updDT
					)
					Values( '1', :DifferentLpu, '1', '1', dbo.tzgetdate(), dbo.tzgetdate());
		
					UPDATE Dbo.LpuDifferent
						set LpuDifferent_Code = LASTVAL()
					where LpuDifferent_Id = LASTVAL()
					returning 0 as \"Error_Code\", '' as \"Error_Msg\", LASTVAL() as \"id\";
				";
				$respp = $this->queryResult($query, $params);
			} else {
				$respp = [
					'Error_Code' => 500,
					'Error_Msg' => 'Ошибка выполнения запроса'
				];
			}

			if (!$this->isSuccessful($respp)) {
				$this->rollbackTransaction();
				return $respp;
			}

			$answers['774'] = $respp[0]['id'];
		}

		foreach($answers as $QuestionType_Code => $answer) {
			if (!isset($questions[$QuestionType_Code])) {
				return $this->createError('',"Не найден вопрос с кодом {$QuestionType_Code} в БД");
				//continue;
			}
			$question = $questions[$QuestionType_Code];
			if ($question['AnswerType_id'] == 1) $answer = $answer?2:1;
			if ($answer === '') { $answer = null; }
			if (
				($answer !== null && $question['Answer'] !== null && $question['Answer'] == $answer)
				|| ($answer === null && $question['Answer'] === null)
			) continue;

			$resp = $this->savePregnancyQuestion(array(
				'PregnancyQuestion_id' => $question['PregnancyQuestion_id'],
				'PersonRegister_id' => $params['PersonRegister_id'],
				'PregnancyScreen_id' => $params['PregnancyScreen_id'],
				'QuestionType_id' => $question['QuestionType_id'],
				'Answer' => $answer,
				'AnswerType_id' => $question['AnswerType_id'],
				'AnswerClass_id' => $question['AnswerClass_id'],
				'pmUser_id' => $params['pmUser_id'],
			));
			if (!$this->isSuccessful($resp)) {
				return $resp;
			}
		}

		// считаем "Риск развития перинатальной патологии на основе анкеты" и записываем в PersonPregnancy_RiskDPP
		$this->recalculatePregnancyQuestionRisk($data);

		return array(array('success' => true, 'Error_Msg' => ''));
	}

	/**
	 * Рассчёт "Интранатальные факторы риска"
	 */
	function recalculatePregnancyQuestionBirthSpecStacRisk($data)
	{
		$BirthSpecStac_Risk = 0;

		$regionNick = getRegionNick();

		$query = "
			select
				bss.BirthSpecStac_OutcomPeriod as \"BirthSpecStac_OutcomPeriod\"
			from
				v_BirthSpecStac bss
			where
				bss.BirthSpecStac_id = :BirthSpecStac_id
		";
		$resp_pp = $this->queryResult($query, array(
			'BirthSpecStac_id' => $data['BirthSpecStac_id']
		));

		// Срок беременности при родах
		if (!empty($resp_pp[0]['BirthSpecStac_OutcomPeriod'])) {
			switch ($regionNick) {
				case 'buryatiya':
				case 'astra':
				case 'ekb':
					// не считаем
					break;
				case 'kareliya':
					if ($resp_pp[0]['BirthSpecStac_OutcomPeriod'] >= 28 && $resp_pp[0]['BirthSpecStac_OutcomPeriod'] <= 30) {
						$BirthSpecStac_Risk += 16;
					} elseif ($resp_pp[0]['BirthSpecStac_OutcomPeriod'] >= 31 && $resp_pp[0]['BirthSpecStac_OutcomPeriod'] <= 35) {
						$BirthSpecStac_Risk += 8;
					}
					break;
				default:
					if ($resp_pp[0]['BirthSpecStac_OutcomPeriod'] >= 28 && $resp_pp[0]['BirthSpecStac_OutcomPeriod'] <= 30) {
						$BirthSpecStac_Risk += 16;
					} elseif ($resp_pp[0]['BirthSpecStac_OutcomPeriod'] >= 31 && $resp_pp[0]['BirthSpecStac_OutcomPeriod'] <= 35) {
						$BirthSpecStac_Risk += 8;
					} elseif ($resp_pp[0]['BirthSpecStac_OutcomPeriod'] >= 36 && $resp_pp[0]['BirthSpecStac_OutcomPeriod'] <= 37) {
						$BirthSpecStac_Risk += 3;
					}
					break;
			}
		}

		// тянем ответы анкеты
		$query = "
			select
				pq.QuestionType_id as \"QuestionType_id\",
				case
					when qt.AnswerType_id = 1 then pq.PregnancyQuestion_IsTrue::varchar
					when qt.AnswerType_id = 2 then pq.PregnancyQuestion_AnswerText
					when qt.AnswerType_id = 3 then pq.PregnancyQuestion_ValuesStr::varchar
					when qt.AnswerType_id = 5 then pq.PregnancyQuestion_AnswerInt::varchar
					else pq.PregnancyQuestion_ValuesStr::varchar
				end as \"value\"
			from
				v_BirthSpecStac bss
				inner join v_PregnancyQuestion pq on pq.PersonRegister_id = bss.PersonRegister_id
				left join v_QuestionType QT on QT.QuestionType_id = pq.QuestionType_id
			where
				bss.BirthSpecStac_id = :BirthSpecStac_id
		";
		$resp_pq = $this->queryResult($query, array(
			'BirthSpecStac_id' => $data['BirthSpecStac_id']
		));
		// формируем удобный массив
		$answers = array();
		foreach ($resp_pq as $one_pq) {
			$answers[$one_pq['QuestionType_id']] = $one_pq['value'];
		}

		// и поехали смотреть ответы на интересующие нас вопросы
		// 519	Нефропатия
		if (!empty($answers[519]) && $answers[519] == 2) {
			switch ($regionNick) {
				case 'buryatiya':
				case 'astra':
				case 'ekb':
					// не считаем
					break;
				default:
					$BirthSpecStac_Risk += 5;
					break;
			}
		}

		// 520	Преэклампсия
		if (!empty($answers[520]) && $answers[520] == 2) {
			switch ($regionNick) {
				case 'buryatiya':
				case 'astra':
				case 'ekb':
					// не считаем
					break;
				default:
					$BirthSpecStac_Risk += 8;
					break;
			}
		}

		// 521	Эклампсия
		if (!empty($answers[521]) && $answers[521] == 2) {
			switch ($regionNick) {
				case 'buryatiya':
				case 'astra':
				case 'ekb':
					// не считаем
					break;
				default:
					$BirthSpecStac_Risk += 12;
					break;
			}
		}

		// 522	Несвоевременное излитие околоводных вод (12 часов и более)
		if (!empty($answers[522]) && $answers[522] == 2) {
			switch ($regionNick) {
				case 'astra':
				case 'ekb':
					// не считаем
					break;
				case 'buryatiya':
					$BirthSpecStac_Risk += 6;
					break;
				default:
					$BirthSpecStac_Risk += 2;
					break;
			}
		}

		// 523	Слабость родовой деятельности
		if (!empty($answers[523]) && $answers[523] == 2) {
			switch ($regionNick) {
				case 'astra':
				case 'ekb':
					// не считаем
					break;
				default:
					$BirthSpecStac_Risk += 4;
					break;
			}
		}

		// 524	Быстрые роды
		if (!empty($answers[524]) && $answers[524] == 2) {
			switch ($regionNick) {
				case 'buryatiya':
				case 'astra':
				case 'ekb':
					// не считаем
					break;
				default:
					$BirthSpecStac_Risk += 3;
					break;
			}
		}

		// 525	Родовозбуждение, стимуляция родовой деятельности
		if (!empty($answers[525]) && $answers[525] == 2) {
			switch ($regionNick) {
				case 'buryatiya':
				case 'astra':
				case 'ekb':
					// не считаем
					break;
				default:
					$BirthSpecStac_Risk += 2;
					break;
			}
		}

		// 526	Клинический узкий таз
		if (!empty($answers[526]) && $answers[526] == 2) {
			switch ($regionNick) {
				case 'buryatiya':
				case 'astra':
				case 'ekb':
					// не считаем
					break;
				default:
					$BirthSpecStac_Risk += 4;
					break;
			}
		}

		// 527	Угрожающий разрыв матки
		if (!empty($answers[527]) && $answers[527] == 2) {
			switch ($regionNick) {
				case 'buryatiya':
				case 'astra':
				case 'ekb':
					// не считаем
					break;
				default:
					$BirthSpecStac_Risk += 18;
					break;
			}
		}

		// 529	Предлежание плаценты
		if (!empty($answers[529])) {
			switch ($regionNick) {
				case 'buryatiya':
				case 'astra':
				case 'ekb':
					// не считаем
					break;
				default:
					switch ($answers[529]) {
						case '1': // частичное
							$BirthSpecStac_Risk += 3;
							break;
						case '2': // полное
							$BirthSpecStac_Risk += 12;
							break;
					}
					break;
			}
		}

		// 532	Преждевременная отслойка нормально расположенной плаценты
		if (!empty($answers[532]) && $answers[532] == 2) {
			switch ($regionNick) {
				case 'buryatiya':
				case 'astra':
				case 'ekb':
					// не считаем
					break;
				default:
					$BirthSpecStac_Risk += 26;
					break;
			}
		}

		// 537	Хориоамнионит
		if (!empty($answers[537]) && $answers[537] == 2) {
			switch ($regionNick) {
				case 'buryatiya':
					$BirthSpecStac_Risk += 4;
					break;
			}
		}

		// 538	Нарушение сердечного ритма (в течение 30 мин и более)
		if (!empty($answers[538]) && $answers[538] == 2) {
			switch ($regionNick) {
				case 'buryatiya':
				case 'astra':
				case 'ekb':
					// не считаем
					break;
				default:
					$BirthSpecStac_Risk += 3;
					break;
			}
		}

		// 540	Патология пуповины/ выпадение
		if (!empty($answers[540]) && $answers[540] == 2) {
			switch ($regionNick) {
				case 'buryatiya':
				case 'astra':
				case 'ekb':
					// не считаем
					break;
				default:
					$BirthSpecStac_Risk += 9;
					break;
			}
		}

		// 541	Патология пуповины/ обвитие
		if (!empty($answers[541]) && $answers[541] == 2) {
			switch ($regionNick) {
				case 'buryatiya':
				case 'astra':
				case 'ekb':
					// не считаем
					break;
				default:
					$BirthSpecStac_Risk += 2;
					break;
			}
		}

		// 543	Тазовое предлежание/пособия
		if (!empty($answers[543]) && $answers[543] == 2) {
			switch ($regionNick) {
				case 'buryatiya':
				case 'astra':
				case 'ekb':
					// не считаем
					break;
				default:
					$BirthSpecStac_Risk += 3;
					break;
			}
		}

		// 544	Тазовое предлежание/экстракция плода
		if (!empty($answers[544]) && $answers[544] == 2) {
			switch ($regionNick) {
				case 'buryatiya':
				case 'astra':
				case 'ekb':
					// не считаем
					break;
				default:
					$BirthSpecStac_Risk += 15;
					break;
			}
		}

		// 546	Оперативные вмешательства/кесарево сечение
		if (!empty($answers[546]) && $answers[546] == 2) {
			switch ($regionNick) {
				case 'buryatiya':
				case 'astra':
				case 'ekb':
					// не считаем
					break;
				default:
					$BirthSpecStac_Risk += 5;
					break;
			}
		}

		// 547	Оперативные вмешательства/акушерские щипцы - полостные
		if (!empty($answers[547]) && $answers[547] == 2) {
			switch ($regionNick) {
				case 'buryatiya':
				case 'astra':
				case 'ekb':
					// не считаем
					break;
				default:
					$BirthSpecStac_Risk += 4;
					break;
			}
		}

		// 548	Оперативные вмешательства/акушерские щипцы – выходные
		if (!empty($answers[548]) && $answers[548] == 2) {
			switch ($regionNick) {
				case 'buryatiya':
				case 'astra':
				case 'ekb':
					// не считаем
					break;
				default:
					$BirthSpecStac_Risk += 3;
					break;
			}
		}

		// 549	Оперативные вмешательства/ вакуум-экстракция
		if (!empty($answers[549]) && $answers[549] == 2) {
			switch ($regionNick) {
				case 'buryatiya':
				case 'astra':
				case 'ekb':
					// не считаем
					break;
				default:
					$BirthSpecStac_Risk += 3;
					break;
			}
		}

		// 550	Оперативные вмешательства/ затрудненное выведение плечиков
		if (!empty($answers[550]) && $answers[550] == 2) {
			switch ($regionNick) {
				case 'buryatiya':
				case 'astra':
				case 'ekb':
					// не считаем
					break;
				default:
					$BirthSpecStac_Risk += 2;
					break;
			}
		}

		// 551	Общая анестезия в родах
		if (!empty($answers[551]) && $answers[551] == 2) {
			switch ($regionNick) {
				case 'buryatiya':
				case 'astra':
				case 'ekb':
					// не считаем
					break;
				default:
					$BirthSpecStac_Risk += 1;
					break;
			}
		}


		return $BirthSpecStac_Risk;
	}

	/**
	 * Рассчёт "Риск по скринингу"
	 */
	function recalculatePregnancyQuestionScreenRisk($data)
	{
		$PregnancyScreen_RiskPerPat = 0;

		$regionNick = getRegionNick();
		//gaf #106655 11042018
		if ($regionNick == ""){
			$regionNick=$this->regionNick;
		}		

		// тянем ответы анкеты
		$query = "
			select
				pq.QuestionType_id as \"QuestionType_id\",
				case
					when qt.AnswerType_id = 1 then pq.PregnancyQuestion_IsTrue::varchar
					when qt.AnswerType_id = 2 then pq.PregnancyQuestion_AnswerText
					when qt.AnswerType_id = 3 then pq.PregnancyQuestion_ValuesStr::varchar
					when qt.AnswerType_id = 5 then pq.PregnancyQuestion_AnswerInt::varchar
					else pq.PregnancyQuestion_ValuesStr::varchar
				end as \"value\"
			from
				v_PregnancyQuestion pq
				left join v_QuestionType QT on QT.QuestionType_id = pq.QuestionType_id
			where
				pq.PregnancyScreen_id = :PregnancyScreen_id
		";
		$resp_pq = $this->queryResult($query, array(
			'PregnancyScreen_id' => $data['PregnancyScreen_id']
		));
		// формируем удобный массив
		$answers = array();
		foreach ($resp_pq as $one_pq) {
			$answers[$one_pq['QuestionType_id']] = $one_pq['value'];
		}

		// и поехали смотреть ответы на интересующие нас вопросы

		// 374	Острые инфекции при беременности, в т.ч. острые респираторно-вирусные
		if (!empty($answers[374]) && $answers[374] == 2) {
			$PregnancyScreen_RiskPerPat += 4;
		}

		// 375	Выраженный ранний токсикоз
		if (!empty($answers[375]) && $answers[375] == 2) {
			$PregnancyScreen_RiskPerPat += 2;
		}

		// 376	Рецидивирующая угроза прерывания
		if (!empty($answers[376]) && $answers[376] == 2) {
			switch ($regionNick) {
				case 'buryatiya':
				case 'astra':
				case 'ekb':
					//gaf #106655 09042018	
				case 'penza':				
				case 'ufa':				
					$PregnancyScreen_RiskPerPat += 2;
					break;
				case 'kareliya':
					// не считаем
					break;
				default:
					$PregnancyScreen_RiskPerPat += 1;
					break;
			}
		}

		// 377	Антифосфолипидный синдром
		if (!empty($answers[377]) && $answers[377] == 2) {
			switch ($regionNick) {
				case 'ekb':
					$PregnancyScreen_RiskPerPat += 25;
					break;
				//gaf #106655 09042018
				case 'penza':
				case 'ufa':
					$PregnancyScreen_RiskPerPat += 4;
					break;						
			}
		}

		// 378	Отеки беременных
		if (!empty($answers[378]) && $answers[378] == 2) {
			switch ($regionNick) {
				case 'buryatiya':
				case 'astra':
				case 'ekb':
					//gaf #106655 09042018
				case 'penza':				
				case 'ufa':				
					$PregnancyScreen_RiskPerPat += 2;
					break;
			}
		}

		// 379	Обострение заболеваний почек
		if (!empty($answers[379]) && $answers[379] == 2) {
			switch ($regionNick) {
				case 'buryatiya':
				case 'kareliya':
				case 'astra':
				case 'ekb':
					//gaf #106655 09042018
				case 'penza':				
				case 'ufa':				
					$PregnancyScreen_RiskPerPat += 4;
					break;
			}
		}

		// 380	Венозные осложнения
		if (!empty($answers[380]) && $answers[380] == 2) {
			switch ($regionNick) {
				case 'ekb':
					$PregnancyScreen_RiskPerPat += 25;
					break;
				//gaf #106655 09042018
				case 'penza':
				case 'ufa':
					$PregnancyScreen_RiskPerPat += 4;
					break;						
			}
		}

		// 381	Положение плода
		if (!empty($answers[381])) {
			switch ($regionNick) {
				case 'buryatiya':
				case 'astra':
					//gaf #106655 09042018
				case 'penza':			
				case 'ufa':			
					if ($answers[381] != 1) {
						$PregnancyScreen_RiskPerPat += 3;
					}
					break;
				case 'ekb':
					if ($answers[381] != 1) {
						$PregnancyScreen_RiskPerPat += 25;
					}
					break;
			}
		}

		// 382	Предлежание (Тазовое предлежание)
		// 383	Крупный плод
		// 384	Узкий таз
		switch ($regionNick) {
			case 'buryatiya':			
			case 'kareliya':
			case 'ekb':
				if (
					(!empty($answers[382]) && in_array($answers[382], array('2', '3')))
					|| (!empty($answers[383]) && $answers[383] == 2)
					|| (!empty($answers[384]) && $answers[384] == 2)
				) {
					$PregnancyScreen_RiskPerPat += 3;
				}
				break;
			case 'astra':
				if (
					(!empty($answers[382]) && in_array($answers[382], array('2', '3', '4')))
					|| (!empty($answers[383]) && $answers[383] == 2)
					|| (!empty($answers[384]) && $answers[384] == 2)
				) {
					$PregnancyScreen_RiskPerPat += 3;
				}
				break;
			//gaf #106655 09042018
			case 'penza':				
			case 'ufa':				
				if (!empty($answers[382])){
					if ($answers[382] == 2){
						$PregnancyScreen_RiskPerPat += 3;
					}else if ($answers[382] == 3){
						$PregnancyScreen_RiskPerPat += 3;
					}else if ($answers[382] == 4){
						$PregnancyScreen_RiskPerPat += 3;
					} 
				}				
				if (!empty($answers[383]) && $answers[383] == 2) {
					$PregnancyScreen_RiskPerPat += 3;
				}
				if (!empty($answers[384]) && $answers[384] == 2) {
					$PregnancyScreen_RiskPerPat += 3;
				}
				break;					
			default:
				if (!empty($answers[382]) && in_array($answers[382], array('2', '3'))) {
					$PregnancyScreen_RiskPerPat += 3;
				}
				break;
		}

		// 386	Низкая плацентация
		if (!empty($answers[386]) && $answers[386] == 2) {
			switch ($regionNick) {
				case 'ekb':
					$PregnancyScreen_RiskPerPat += 10;
					break;
			}
		}

		// 387	Центральное предлежание плаценты
		if (!empty($answers[387]) && $answers[387] == 2) {
			switch ($regionNick) {
				case 'ekb':
					$PregnancyScreen_RiskPerPat += 25;
					break;
			}
		}

		// 390	Биологическая незрелость родовых путей в 40 недель беременности
		if (!empty($answers[390]) && $answers[390] == 2) {
			switch ($regionNick) {
				case 'buryatiya':
				case 'astra':
					//gaf #106655 09042018
				case 'penza':				
				case 'ufa':				
					$PregnancyScreen_RiskPerPat += 4;
					break;
			}
		}

		// 391	Перенашивание беременности
		if (!empty($answers[391]) && $answers[391] == 2) {
			switch ($regionNick) {
				case 'buryatiya':
				case 'kareliya':
				case 'astra':
					//gaf #106655 09042018
				case 'penza':				
				case 'ufa':				
					$PregnancyScreen_RiskPerPat += 3;
					break;
			}
		}

		// 393	Активный вызов
		if (!empty($answers[393]) && $answers[393] == 2) {
			switch ($regionNick) {
				case 'ekb':
					$PregnancyScreen_RiskPerPat += 25;
					break;
			}
		}

		// 398	Несоответствие данных УЗИ сроку гестации/ Состояние ок.-плод вод (количество (ИАЖ), прозрачность, наличие взвеси, примес.)
		// 399	Несоответствие данных УЗИ сроку гестации/ Фетометрии (размеры плодного яйца, эмбриона, плода)
		// 400	Несоответствие данных УЗИ сроку гестации/ плаценты/хориона (толщина, структура, степень зрелости)
		switch ($regionNick) {
			case 'ekb':
				if (
					(!empty($answers[398]) && $answers[398] == 2)
					|| (!empty($answers[399]) && $answers[399] == 2)
					|| (!empty($answers[400]) && $answers[400] == 2)
				) {
					$PregnancyScreen_RiskPerPat += 15;
				}
				break;
		}

		// 401	Хроническая плацентарная недостаточность
		if (!empty($answers[401]) && $answers[401] == 2) {
			switch ($regionNick) {
				case 'buryatiya':
					//gaf #106655 09042018
				case 'penza':					
				case 'ufa':					
					$PregnancyScreen_RiskPerPat += 4;
					break;
			}
		}

		// 402	Оценка КТГ по шкале Фишер
		if (!empty($answers[402])) {
			switch ($regionNick) {
				case 'buryatiya':
				case 'ekb':
					if ($answers[402] >= 7) {
						$PregnancyScreen_RiskPerPat += 4;
					} else if ($answers[402] >= 6 && $answers[402] < 7) {
						$PregnancyScreen_RiskPerPat += 8;
					} else if ($answers[402] >= 5 && $answers[402] < 6) {
						$PregnancyScreen_RiskPerPat += 12;
					} else if ($answers[402] >= 4 && $answers[402] < 5) {
						$PregnancyScreen_RiskPerPat += 16;
					} else if ($answers[402] < 4) {
						$PregnancyScreen_RiskPerPat += 20;
					}
					break;
				case 'astra':
					if ($answers[402] == 6) {
						$PregnancyScreen_RiskPerPat += 4;
					} else if ($answers[402] == 5) {
						$PregnancyScreen_RiskPerPat += 8;
					} else if ($answers[402] == 4) {
						// экстренная госпитализация о_О
						$PregnancyScreen_RiskPerPat += 25; // Березовский Сергей: "а КТГ при 4 баллах ставь 20"
					}
					break;
			}
		}

		$srok = null;
		if (!empty($answers[358])) {
			$srok = $answers[358];
		}
		if (!empty($answers[359])) {
			$srok = $answers[359];
		}
		if (!empty($answers[362])) {
			$srok = $answers[362];
		}
		if (!empty($answers[363])) {
			$srok = $answers[363];
		}

		// 403	Содержание эстриола в суточной моче мг/сут
		if (!empty($answers[403])) {
			switch ($regionNick) {
				case 'buryatiya':
				case 'astra':
				case 'ekb':
					// не считаем
					break;
				default:
					if (!empty($srok) && $srok <= 30 && $answers[403] < 4.9) {
						$PregnancyScreen_RiskPerPat += 34;
					} else if (!empty($srok) && $srok >= 31 && $srok <= 40 && $answers[403] < 12) {
						$PregnancyScreen_RiskPerPat += 15;
					}
					break;
			}
		}

		// 404	Наличие мекония в околоплодных водах
		if (!empty($answers[404]) && $answers[404] == 2) {
			switch ($regionNick) {
				case 'buryatiya':
				case 'astra':
				case 'ekb':
					// не считаем
					break;
				default:
					$PregnancyScreen_RiskPerPat += 3;
					break;
			}
		}

		// 414	Гестоз
		if (!empty($answers[414])) {
			switch ($regionNick) {
				case 'ekb':
					switch ($answers[414]) {
						case '2': // водянка
							$PregnancyScreen_RiskPerPat += 2;
							break;
						case '3': // легкой степени
							$PregnancyScreen_RiskPerPat += 3;
							break;
						case '4': // тяжелой степени
							$PregnancyScreen_RiskPerPat += 25;
							break;
						case '5': // преэклампсия
							$PregnancyScreen_RiskPerPat += 25;
							break;
						case '6': // эклампсия
							$PregnancyScreen_RiskPerPat += 25;
							break;
						case '7': // гепатоз
							$PregnancyScreen_RiskPerPat += 25;
							break;
					}
					break;
				case 'astra':			
					switch ($answers[414]) {
						case '9': // тяжелой степени
							$PregnancyScreen_RiskPerPat += 10;
							break;
						case '10': // эклампсия
							$PregnancyScreen_RiskPerPat += 12;
							break;
						case '11': // средней степени
							$PregnancyScreen_RiskPerPat += 5;
							break;
					}
					break;
				//gaf #106655 11042018
				case 'penza':
				case 'ufa':
					switch ($answers[414]) {
						case '2': // водянка
							$PregnancyScreen_RiskPerPat += 2;
							break;
						case '3': // легкой степени
							$PregnancyScreen_RiskPerPat += 3;
							break;
						case '4': // тяжелой степени
							$PregnancyScreen_RiskPerPat += 10;
							break;
						case '5': // преэклампсия
							$PregnancyScreen_RiskPerPat += 11;
							break;
						case '6': // эклампсия
							$PregnancyScreen_RiskPerPat += 12;
							break;
						case '7': // гестоз
							$PregnancyScreen_RiskPerPat += 5;
							break;						
						case '8': // средней степени
							$PregnancyScreen_RiskPerPat += 5;
							break;						
					}
					break;						
				default:
					switch ($answers[414]) {
						case '2': // водянка
							$PregnancyScreen_RiskPerPat += 2;
							break;
						case '3': // легкой степени
							$PregnancyScreen_RiskPerPat += 3;
							break;
						case '4': // тяжелой степени
							$PregnancyScreen_RiskPerPat += 10;
							break;
						case '5': // преэклампсия
							$PregnancyScreen_RiskPerPat += 11;
							break;
						case '6': // эклампсия
							$PregnancyScreen_RiskPerPat += 12;
							break;							
					}
					break;
			}
		}

		// 415	Резус-сенсибилизация
		if (!empty($answers[415])) {
			switch ($regionNick) {
				case 'ekb':
					switch ($answers[415]) {
						case '4':
							$PregnancyScreen_RiskPerPat += 25;
							break;
						case '2':
						case '3':
							// Если срок беременности скрининга в первом триместре  (1-13 недель)- 15, иначе 25
							if (!empty($srok) && $srok <= 13) {
								$PregnancyScreen_RiskPerPat += 15;
							} else {
								$PregnancyScreen_RiskPerPat += 25;
							}
							break;
					}
					break;
				default:
					switch ($answers[415]) {
						case '2':
						case '3':
						case '4':
							$PregnancyScreen_RiskPerPat += 5;
							break;
					}
					break;
			}
		}

		// 552 ABO Сенсибилизация
		//if (!empty($answers[552]) && $answers[552] == 2) {
		//	$PregnancyScreen_RiskPerPat += 10;
		//}

		// 416	Нарушения околоплодных вод
		if (!empty($answers[416])) {
			switch ($regionNick) {
				case 'ekb':
					// не считаем
					break;
				default:
					switch ($answers[416]) {
						case '1':
							$PregnancyScreen_RiskPerPat += 3;
							break;
						case '2':
							$PregnancyScreen_RiskPerPat += 4;
							break;
					}
					break;
			}
		}


		// 417	Многоплодие
		if (!empty($answers[417])) {
			switch ($regionNick) {
				case 'buryatiya':
				case 'kareliya':
					$PregnancyScreen_RiskPerPat += 3;
					break;
 				case 'astra':
					if ($answers[417] == 8) {
						$PregnancyScreen_RiskPerPat += 10;
					}else{
						$PregnancyScreen_RiskPerPat += 3;
					}
					break;
				case 'ekb':
					if ($answers[417] == 2) { // бихориальная двойня
						$PregnancyScreen_RiskPerPat += 15;
					} else {
						$PregnancyScreen_RiskPerPat += 25;
					}
					break;
				case 'penza':
				case 'ufa':
					if ($answers[417] == 5) {
						$PregnancyScreen_RiskPerPat += 10;
					} else if ($answers[417] == 6) {
						$PregnancyScreen_RiskPerPat += 3;
					} else if ($answers[417] == 7) {
						$PregnancyScreen_RiskPerPat += 3;
					} else if ($answers[417] == 3) {
						$PregnancyScreen_RiskPerPat += 3;
					} else if ($answers[417] == 4) {
						$PregnancyScreen_RiskPerPat += 3;
					}
					break;										
			}
		}

		// 418	b-ХГЧ
		if (!empty($answers[418])) {
			switch ($regionNick) {
				case 'buryatiya':
				case 'astra':
					//gaf #106655 09042018
				case 'penza':						
				case 'ufa':						
					switch ($answers[418]) {
						case '3': // выше нормы
							$PregnancyScreen_RiskPerPat += 3;
							break;
						case '4': // ниже нормы
							$PregnancyScreen_RiskPerPat += 4;
							break;
					}
					break;
			}
		}

		// 419	АФП
		if (!empty($answers[419])) {
			switch ($regionNick) {
				case 'buryatiya':
					//gaf #106655 09042018
				case 'penza':						
				case 'ufa':						
					switch ($answers[419]) {
						case '3': // выше нормы
							$PregnancyScreen_RiskPerPat += 6;
							break;
						case '4': // ниже нормы
							$PregnancyScreen_RiskPerPat += 8;
							break;
					}
					break;
			}
		}

		// 420	PAPP-A
		if (!empty($answers[420])) {
			switch ($regionNick) {
				case 'buryatiya':
					//gaf #106655 09042018
				case 'penza':					
				case 'ufa':					
					switch ($answers[420]) {
						case '3': // выше нормы
							$PregnancyScreen_RiskPerPat += 2;
							break;
						case '4': // ниже нормы
							$PregnancyScreen_RiskPerPat += 3;
							break;
					}
					break;
			}
		}

		// 421	Наличие ВПР по результатам УЗИ
		if (!empty($answers[421])) {
			switch ($regionNick) {
				case 'ekb':
					switch ($answers[421]) {
						case '2': // Подозрение на ВПР
						case '3': // МХА
							$PregnancyScreen_RiskPerPat += 25;
							break;
					}
					break;
			}
		}

		// 422	Общее состояние плода
		if (!empty($answers[422])) {
			switch ($regionNick) {
				case 'kareliya':
				case 'ekb':
					// не считаем
					break;
				default:
					switch ($answers[422]) {
						case '3': // СЗРП 1 ст
							$PregnancyScreen_RiskPerPat += 10;
							break;
						case '4': // СЗРП 2 ст
							$PregnancyScreen_RiskPerPat += 15;
							break;
						case '5': // СЗРП 3 ст
							$PregnancyScreen_RiskPerPat += 20;
							break;
					}
					break;
			}
		}

		// 553	Нарушение кровотока по ДПМ
		if (!empty($answers[553])) {
			switch ($regionNick) {
				case 'astra':
					switch ($answers[553]) {
						case '1': // 1. I «А»
							$PregnancyScreen_RiskPerPat += 1;
							break;
						case '2': // 2. I «Б»
							$PregnancyScreen_RiskPerPat += 2;
							break;
						case '3': // 3. II
							$PregnancyScreen_RiskPerPat += 3;
							break;
						case '4': // 4. III
							$PregnancyScreen_RiskPerPat += 25;
							break;
					}
					break;
				default:
					// не считаем
					break;
			}
		}

		
		// 552	АВО Сенсибилизация
		if (!empty($answers[552]) && $answers[552] == 2) {
			switch ($regionNick) {
				case 'penza':
				case 'ufa':
					$PregnancyScreen_RiskPerPat += 5;
					break;
				default:
					$PregnancyScreen_RiskPerPat += 10;
					break;
			}
		}

		// 660	Резус-сенсибилизация
		if (!empty($answers[660]) && $answers[660] == 2) {
			switch ($regionNick) {
				case 'penza':
				case 'ufa':
					$PregnancyScreen_RiskPerPat += 10;
					break;
			}
		}
		
		//для получения максимального значения среди параметров блока Плацентарная недостаточность и гипокция плода
		$maxparam = 0;		
		
		// 662	Оценка КТГ по шкале Фишер
		if (!empty($answers[662])) {
			switch ($regionNick) {
				case 'penza':
				case 'ufa':
					if ($answers[662] == 1) {
						$maxparam = 0;
					} else if ($answers[662] == 2) {
						$maxparam = 4;
					} else if ($answers[662] == 3) {
						$maxparam = 8;
					} else if ($answers[662] == 4) {
						$maxparam = 12;
					} else if ($answers[662] == 5) {
						$maxparam = 16;
					} else if ($answers[662] == 6) {
						$maxparam = 20;
					}
					break;
			}
		}

		// 663	Оценка КТГ по показателю состояния плода
		if (!empty($answers[663])) {
			switch ($regionNick) {
				case 'penza':
				case 'ufa':
					if ($answers[663] == 1) {
						$PregnancyScreen_RiskPerPat += 0;
					} else if ($answers[663] == 2) {
						if ($maxparam < 4) $maxparam = 4;
					} else if ($answers[663] == 3) {
						if ($maxparam < 8) $maxparam = 8;
					} else if ($answers[663] == 4) {
						if ($maxparam < 12) $maxparam = 12;
					} else if ($answers[663] == 5) {
						if ($maxparam < 16) $maxparam = 16;
					} else if ($answers[663] == 6) {
						if ($maxparam < 20) $maxparam = 20;
					}
					break;
			}
		}

		// 664	Оценка биофизического профиля плода (A. Vintzileos, 1983)
		if (!empty($answers[664])) {
			switch ($regionNick) {
				case 'penza':
				case 'ufa':
					if ($answers[664] == 1) {
						$PregnancyScreen_RiskPerPat += 0;
					} else if ($answers[664] == 2) {
						if ($maxparam < 12) $maxparam = 12;
					} else if ($answers[664] == 3) {
						if ($maxparam < 16) $maxparam = 16;
					} else if ($answers[664] == 4) {
						if ($maxparam < 20) $maxparam = 20;
					} else if ($answers[664] == 5) {
						if ($maxparam < 25) $maxparam = 25;
					}
					break;
			}
		}

		// 665	Оценка данных допплерометрии
		if (!empty($answers[665])) {
			switch ($regionNick) {
				case 'penza':
				case 'ufa':
					if ($answers[665] == 1) {
						$PregnancyScreen_RiskPerPat += 0;
					} else if ($answers[665] == 2) {
						if ($maxparam < 2) $maxparam = 2;
					} else if ($answers[665] == 3) {
						if ($maxparam < 7) $maxparam = 7;
					} else if ($answers[665] == 4) {
						if ($maxparam < 15) $maxparam = 15;
					} else if ($answers[665] == 5) {
						if ($maxparam < 25) $maxparam = 25;
					}
					break;
			}
		}
		$PregnancyScreen_RiskPerPat += $maxparam;
		
		// 670	Патологический  прелиминарный период
		if (!empty($answers[670]) && $answers[670] == 2) {
			switch ($regionNick) {
				case 'penza':
				case 'ufa':
					$PregnancyScreen_RiskPerPat += 4;
					break;
			}
		}
		
		// 671 Признаки внутриамниотической инфекции (ультразвуковые, клинико-лабораторные)	
		if (!empty($answers[671]) && $answers[671] == 2) {
			switch ($regionNick) {
				case 'penza':
				case 'ufa':
					$PregnancyScreen_RiskPerPat += 4;
					break;
			}
		}
				
		// 756	АВО Сенсибилизация
		if (!empty($answers[756]) && $answers[756] == 2) {
			switch ($regionNick) {
				case 'astra':
					$PregnancyScreen_RiskPerPat += 5;
					break;
				default:
					break;
			}
		}
		
		//757  Синдром задержки развития плода
		if (!empty($answers[757])) {
			switch ($regionNick) {
				case 'astra':
					switch ($answers[757]) {
						case '1': 
							$PregnancyScreen_RiskPerPat += 10;
							break;
						case '2': 
							$PregnancyScreen_RiskPerPat += 15;
							break;
						case '3': 
							$PregnancyScreen_RiskPerPat += 20;
							break;
					}
					break;
				default:
					// не считаем
					break;
			}
		}	

		$query = "
                  update 
                      PregnancyScreen 
                  set 
                      PregnancyScreen_RiskPerPat = :PregnancyScreen_RiskPerPat 
                  where 
                      PregnancyScreen_id = :PregnancyScreen_id
        ";
		// обновляем в скрининге
		$this->db->query($query, array(
			'PregnancyScreen_id' => $data['PregnancyScreen_id'],
			'PregnancyScreen_RiskPerPat' => $PregnancyScreen_RiskPerPat
		));

		// пересчитать степнь риска
		$this->recalculateRiskType($data);

		return $PregnancyScreen_RiskPerPat;
	}

	/**
	 * Рассчёт степени риска
	 */
	function recalculateRiskType($data) {
		$regionNick = getRegionNick();
		if ($regionNick == ""){
			$regionNick=$this->regionNick;
		}
		
		$resp_risk = $this->queryResult("
			select
				coalesce(PP.PersonPregnancy_RiskDPP, 0) + coalesce (LastScreen.PregnancyScreen_RiskPerPat, 0) as \"PersonRegister_ObRisk\"
			from
				v_PersonRegister PR
				left join v_PersonPregnancy PP on PP.PersonRegister_id = PR.PersonRegister_id
				left join lateral (
					select 
					    PS.PregnancyScreen_RiskPerPat
					from 
					    v_PregnancyScreen PS
					where 
					    PS.PersonRegister_id = PR.PersonRegister_id
					order by 
					    PS.PregnancyScreen_setDT desc
				) LastScreen on true
			where
				PR.PersonRegister_id = :PersonRegister_id
		", array(
			'PersonRegister_id' => $data['PersonRegister_id']
		));	

		$lowRiskLimit = 15;
		$midRiskLimit = 25;
		
		if ($regionNick == 'astra'){
			$lowRiskLimit = 20;
		}

		$RiskType_id = 1; // низкая степень
		if (!empty($resp_risk[0]['PersonRegister_ObRisk'])) {
			if ($resp_risk[0]['PersonRegister_ObRisk'] < $lowRiskLimit) {
				$RiskType_id = 1; // низкая степень
			} else if ($resp_risk[0]['PersonRegister_ObRisk'] < $midRiskLimit) {
				$RiskType_id = 2; // среднняя степень
			} else {
				$RiskType_id = 3; // высокая степень
			}
		}

		$query = "
                  update 
                      PersonRegister 
                  set 
                      RiskType_id = :RiskType_id,
                      EvnPS_id = cast(dbo.GetPregnancyEvnPS(PersonRegister_id) as bigint),
                      PersonRegister_HighRiskDT = (case when cast(dbo.GetPregnancyRoute(PersonRegister_id, 2, :RiskType_id) as bigint) > 2 then getdate() else null end),
                      PersonRegister_ModerateRiskDT = (case when cast(dbo.GetPregnancyRoute(PersonRegister_id, 2, :RiskType_id) as bigint) = 2 then getdate() else null end),
                      RiskType_aid = cast(dbo.GetPregnancyRoute(PersonRegister_id, 2, :RiskType_id) as bigint),
                      MesLevel_id = cast(dbo.GetPregnancyRoute(PersonRegister_id, 3, :RiskType_id) as bigint)
                  where 
                      PersonRegister_id = :PersonRegister_id";
		// обновляем в анкете
		$this->db->query($query, array(
			'PersonRegister_id' => $data['PersonRegister_id'],
			'RiskType_id' => $RiskType_id
		));
	}

	/**
	 * Рассчёт "Риск развития перинатальной патологии на основе анкеты"
	 */
	function recalculatePregnancyQuestionRisk($data)
	{
		$PersonPregnancy_RiskDPP = 0;

		$regionNick = getRegionNick();
		//gaf 119289
		if ($regionNick == ""){
			$regionNick = $this->regionNick;
		}

		$query = "
			select
				dbo.Age2(psm.Person_BirthDay, pp.PersonPregnancy_setDT) as \"Mother_Age\",
				pp.PersonPregnancy_dadAge as \"PersonPregnancy_dadAge\",
				pp.PregnancyFamilyStatus_id as \"PregnancyFamilyStatus_id\",
				pp.PersonPregnancyEducation_id as \"PersonPregnancyEducation_id\",
				pp.PersonPregnancy_Height as \"PersonPregnancy_Height\",
				pp.PersonPregnancy_IsWeight25 as \"PersonPregnancy_IsWeight25\",
				pp.RhFactorType_id as \"RhFactorType_id\"
			from
				v_PersonPregnancy pp
				left join v_PersonState psm on psm.Person_id = pp.Person_id
			where
				pp.PersonRegister_id = :PersonRegister_id
		";
		$resp_pp = $this->queryResult($query, array(
			'PersonRegister_id' => $data['PersonRegister_id']
		));

		// Возраст матери
		if (isset($resp_pp[0]) && !empty($resp_pp[0]['Mother_Age'])) {
			switch ($regionNick) {
				case 'buryatiya':
				case 'astra':
					if ($resp_pp[0]['Mother_Age'] <= 18) {
						$PersonPregnancy_RiskDPP += 2;
					} elseif ($resp_pp[0]['Mother_Age'] >= 40) {
						$PersonPregnancy_RiskDPP += 4;
					}
					break;
				case 'kareliya':
					if ($resp_pp[0]['Mother_Age'] == 20) {
						$PersonPregnancy_RiskDPP += 2;
					} elseif ($resp_pp[0]['Mother_Age'] >= 25 && $resp_pp[0]['Mother_Age'] <= 29) {
						$PersonPregnancy_RiskDPP += 1;
					} elseif ($resp_pp[0]['Mother_Age'] >= 30 && $resp_pp[0]['Mother_Age'] <= 34) {
						$PersonPregnancy_RiskDPP += 2;
					} elseif ($resp_pp[0]['Mother_Age'] >= 35 && $resp_pp[0]['Mother_Age'] <= 39) {
						$PersonPregnancy_RiskDPP += 3;
					} elseif ($resp_pp[0]['Mother_Age'] >= 40) {
						$PersonPregnancy_RiskDPP += 4;
					}
					break;
				case 'ekb':
					if ($resp_pp[0]['Mother_Age'] <= 17) {
						$PersonPregnancy_RiskDPP += 25;
					}
					break;
				//gaf 119289
				case 'penza':
				case 'ufa':
					if ($resp_pp[0]['Mother_Age'] < 18) {
						$PersonPregnancy_RiskDPP += 2;
					} elseif ($resp_pp[0]['Mother_Age'] >= 35 && $resp_pp[0]['Mother_Age'] <= 39) {
						$PersonPregnancy_RiskDPP += 3;
					} elseif ($resp_pp[0]['Mother_Age'] >= 40) {
						$PersonPregnancy_RiskDPP += 4;
					}
					break;										
				default:
					if ($resp_pp[0]['Mother_Age'] < 21) {
						$PersonPregnancy_RiskDPP += 2;
					} elseif ($resp_pp[0]['Mother_Age'] >= 25 && $resp_pp[0]['Mother_Age'] <= 29) {
						$PersonPregnancy_RiskDPP += 1;
					} elseif ($resp_pp[0]['Mother_Age'] >= 30 && $resp_pp[0]['Mother_Age'] <= 34) {
						$PersonPregnancy_RiskDPP += 2;
					} elseif ($resp_pp[0]['Mother_Age'] >= 35 && $resp_pp[0]['Mother_Age'] <= 39) {
						$PersonPregnancy_RiskDPP += 3;
					} elseif ($resp_pp[0]['Mother_Age'] >= 40) {
						$PersonPregnancy_RiskDPP += 4;
					}
					break;
			}
		}

		// Возраст отца
		if (isset($resp_pp[0]) && !empty($resp_pp[0]['PersonPregnancy_dadAge'])) {
			switch ($regionNick) {
				case 'buryatiya':
				case 'astra':
					if ($resp_pp[0]['PersonPregnancy_dadAge'] >= 40) {
						$PersonPregnancy_RiskDPP += 2;
					}
					break;
				case 'ekb':
					// а екб не учитывает возраст отца:)
					break;
				//gaf 119289
				case 'penza':
				case 'ufa':
					if ($resp_pp[0]['PersonPregnancy_dadAge'] >= 40) {
						$PersonPregnancy_RiskDPP += 2;
					}
					break;					
				default:
					if ($resp_pp[0]['PersonPregnancy_dadAge'] < 20) {
						$PersonPregnancy_RiskDPP += 1;
					} elseif ($resp_pp[0]['PersonPregnancy_dadAge'] >= 40) {
						$PersonPregnancy_RiskDPP += 2;
					}
					break;
			}
		}

		// Семейное положение
		if (isset($resp_pp[0]) && !empty($resp_pp[0]['PregnancyFamilyStatus_id'])) {
			switch ($regionNick) {
				case 'ekb':
					if (in_array($resp_pp[0]['PregnancyFamilyStatus_id'], array(2, 3, 4, 5))) { // одинокая, разведёнка, регистрация во время беременности, гражданский брак
						$PersonPregnancy_RiskDPP += 1;
					}
					break;
				//gaf 119289
				case 'penza':
				case 'ufa':
					if (in_array($resp_pp[0]['PregnancyFamilyStatus_id'], array(4))) { // одинокая
						$PersonPregnancy_RiskDPP += 1;
					}
					if (in_array($resp_pp[0]['PregnancyFamilyStatus_id'], array(5))) { // разведёнка
						$PersonPregnancy_RiskDPP += 1;
					}					
					break;					
				default:
					if ($resp_pp[0]['PregnancyFamilyStatus_id'] == 4) { // одинокая
						$PersonPregnancy_RiskDPP += 1;
					}
					break;
			}
		}

		// Образование
		if (isset($resp_pp[0]) && !empty($resp_pp[0]['PersonPregnancyEducation_id'])) {
			switch ($regionNick) {
				case 'buryatiya':
				case 'astra':
				case 'ekb':
				case 'penza':
				case 'ufa':
					// не учитываем образование
					break;
				default:
					if (in_array($resp_pp[0]['PersonPregnancyEducation_id'], array(1, 3))) { // начальное и высшее
						$PersonPregnancy_RiskDPP += 1;
					}
					break;
			}
		}

		// Рост матери
		if (isset($resp_pp[0]) && !empty($resp_pp[0]['PersonPregnancy_Height'])) {
			switch ($regionNick) {
				case 'buryatiya':
				case 'astra':
					if ($resp_pp[0]['PersonPregnancy_Height'] <= 158) {
						$PersonPregnancy_RiskDPP += 2;
					}
					break;
				case 'ekb':
					if ($resp_pp[0]['PersonPregnancy_Height'] <= 150) {
						$PersonPregnancy_RiskDPP += 25;
					}
					break;
				//gaf 119289 27032018 18042018
				case 'penza':
				case 'ufa':
					if ($resp_pp[0]['PersonPregnancy_Height'] <= 150) {
						$PersonPregnancy_RiskDPP += 2;
					}
					break;						
				default:
					if ($resp_pp[0]['PersonPregnancy_Height'] <= 150) {
						$PersonPregnancy_RiskDPP += 1;
					}
					break;
			}
		}

		// Превышение нормы веса на 25% и более
		if (isset($resp_pp[0]) && !empty($resp_pp[0]['PersonPregnancy_IsWeight25'])) {
			switch ($regionNick) {
				case 'ekb':
					// не учитываем превышение нормы веса
					break;
				default:
					if ($resp_pp[0]['PersonPregnancy_IsWeight25'] == 2) {
						$PersonPregnancy_RiskDPP += 2;
					}
					break;
			}
		}

		// Резус-фактор
		if (isset($resp_pp[0]) && !empty($resp_pp[0]['RhFactorType_id'])) {
			switch ($regionNick) {
				case 'astra':
				case 'kareliya':
					if ($resp_pp[0]['RhFactorType_id'] == 2) { // отрицательный
						$PersonPregnancy_RiskDPP += 5;
					}
					break;
			}
		}

		// тянем ответы анкеты
		$query = "
			select
				pq.QuestionType_id as \"QuestionType_id\",
				case
					when qt.AnswerType_id = 1 then pq.PregnancyQuestion_IsTrue::varchar
					when qt.AnswerType_id = 2 then pq.PregnancyQuestion_AnswerText
					when qt.AnswerType_id = 3 then pq.PregnancyQuestion_ValuesStr::varchar
					when qt.AnswerType_id = 5 then pq.PregnancyQuestion_AnswerInt::varchar
					else pq.PregnancyQuestion_ValuesStr::varchar
				end as \"value\"
			from
				v_PregnancyQuestion pq
				left join v_QuestionType QT on QT.QuestionType_id = pq.QuestionType_id
			where
				pq.PersonRegister_id = :PersonRegister_id
		";
		$resp_pq = $this->queryResult($query, array(
			'PersonRegister_id' => $data['PersonRegister_id']
		));
		// формируем удобный массив
		$answers = array();
		foreach ($resp_pq as $one_pq) {
			$answers[$one_pq['QuestionType_id']] = $one_pq['value'];
		}

		// и поехали смотреть ответы на интересующие нас вопросы

		// 180	Вредные условия труда и быта
		// 181	Химические
		// 182	Радиоактивные
		// 183	Неудовл. Жилищные условия
		// 185  Другое
		switch ($regionNick) {
			case 'buryatiya':
			case 'kareliya':
				if (
					(!empty($answers[181]) && $answers[181] == 2)
					|| (!empty($answers[182]) && $answers[182] == 2)
					|| (!empty($answers[183]) && $answers[183] == 2)
				) {
					$PersonPregnancy_RiskDPP += 3;
				}
				break;
			case 'astra':
				if (
					(!empty($answers[181]) && $answers[181] == 2)
					|| (!empty($answers[183]) && $answers[183] == 2)
				) {
					$PersonPregnancy_RiskDPP += 3;
 				}
				break;
			case 'ekb':
				if (!empty($answers[181]) && $answers[181] == 2) {
					$PersonPregnancy_RiskDPP += 1;
				}
				if (!empty($answers[182]) && $answers[182] == 2) {
					$PersonPregnancy_RiskDPP += 1;
				}
				if (!empty($answers[183]) && $answers[183] == 2) {
					$PersonPregnancy_RiskDPP += 1;
				}
				break;
			//gaf 119289	
			case 'penza':
			case 'ufa':
				if ((!empty($answers[181]) && $answers[181] == 2) || (!empty($answers[183]) && $answers[183] == 2)) {
					$PersonPregnancy_RiskDPP += 3;
				}
				if (!empty($answers[184]) && $answers[184] == 2) {
					$PersonPregnancy_RiskDPP += 1;
				}
				break;				
		}

		// 184	Эмоциональные нагрузки
		switch ($regionNick) {
			case 'buryatiya':
			case 'astra':
			case 'kareliya':
				if (!empty($answers[184]) && $answers[184] == 2) {
					$PersonPregnancy_RiskDPP += 1;
				}
				break;
		}

		// 187	Курение
		// 188	Злоупотребление алкоголем
		// 189	Токсикомания
		// 190	Наркомания
		switch ($regionNick) {
			case 'buryatiya':
			case 'astra':
				if (!empty($answers[187]) && $answers[187] == 2) {
					$PersonPregnancy_RiskDPP += 2;
				}
				if (!empty($answers[188]) && $answers[188] == 2) {
					$PersonPregnancy_RiskDPP += 4;
				}
				break;
			case 'ekb':
				if (!empty($answers[187]) && $answers[187] == 2) {
					$PersonPregnancy_RiskDPP += 2;
				}
				if (!empty($answers[188]) && $answers[188] == 2) {
					$PersonPregnancy_RiskDPP += 4;
				}
				if (!empty($answers[189]) && $answers[189] == 2) {
					$PersonPregnancy_RiskDPP += 4;
				}
				if (!empty($answers[190]) && $answers[190] == 2) {
					$PersonPregnancy_RiskDPP += 4;
				}
				break;
			//gaf 119289
			case 'penza':
			case 'ufa':
				if (!empty($answers[187]) && $answers[187] == 2) {
					$PersonPregnancy_RiskDPP += 2;
				}
				if (!empty($answers[188]) && $answers[188] == 2) {
					$PersonPregnancy_RiskDPP += 4;
				}
				if (!empty($answers[189]) && $answers[189] == 2) {
					$PersonPregnancy_RiskDPP += 4;
				}
				break;				
			default:
				if (!empty($answers[187]) && $answers[187] == 2) {
					$PersonPregnancy_RiskDPP += 1;
				}
				if (!empty($answers[188]) && $answers[188] == 2) {
					$PersonPregnancy_RiskDPP += 2;
				}
				break;
		}

		// 193	Химические
		// 194	Радиоактивные
		// 195	Неудовл. Жилищные условия
		// 196	Эмоциональные нагрузки
		// 197	Другое
		switch ($regionNick) {
			case 'buryatiya':
			case 'kareliya':
				if (
					(!empty($answers[193]) && $answers[193] == 2)
					|| (!empty($answers[194]) && $answers[194] == 2)
					|| (!empty($answers[195]) && $answers[195] == 2)
					|| (!empty($answers[196]) && $answers[196] == 2)
					|| (!empty($answers[197])) // другое
				) {
					$PersonPregnancy_RiskDPP += 3;
				}
				break;
			case 'astra':
				if ((!empty($answers[193]) && $answers[193] == 2)					
					|| (!empty($answers[195]) && $answers[195] == 2))
				{
					$PersonPregnancy_RiskDPP += 3;
				}
				break;				
			case 'ekb':
				// не считается
				break;
			//gaf 119289
			case 'penza':
			case 'ufa':
				if (
					(!empty($answers[193]) && $answers[193] == 2)
					|| (!empty($answers[194]) && $answers[194] == 2)
					|| (!empty($answers[195]) && $answers[195] == 2)
				) {
					$PersonPregnancy_RiskDPP += 3;
				}
				break;				
			default:
				if (
					(!empty($answers[193]) && $answers[193] == 2)
					|| (!empty($answers[194]) && $answers[194] == 2)
					|| (!empty($answers[195]) && $answers[195] == 2)
				) {
					$PersonPregnancy_RiskDPP += 3;
				}
				if (!empty($answers[196]) && $answers[196] == 2) {
					$PersonPregnancy_RiskDPP += 1;
				}
				break;
		}

		// 199	Курение
		// 200	Злоупотребление алкоголем
		// 201	Токсикомания
		// 202	Наркомания
		switch ($regionNick) {
			case 'ekb':
				if (!empty($answers[199]) && $answers[199] == 2) {
					$PersonPregnancy_RiskDPP += 2;
				}
				if (!empty($answers[200]) && $answers[200] == 2) {
					$PersonPregnancy_RiskDPP += 4;
				}
				if (!empty($answers[201]) && $answers[201] == 2) {
					$PersonPregnancy_RiskDPP += 4;
				}
				if (!empty($answers[202]) && $answers[202] == 2) {
					$PersonPregnancy_RiskDPP += 4;
				}
				break;
			case 'astra':
				if (!empty($answers[200]) && $answers[200] == 2) {
					$PersonPregnancy_RiskDPP += 2;
				}
				break;				
			//gaf 119289
			case 'penza':
			case 'ufa':
				if (!empty($answers[200]) && $answers[200] == 2) {
					$PersonPregnancy_RiskDPP += 2;
				}
				if (
					(!empty($answers[201]) && $answers[201] == 2) 
					|| (!empty($answers[202]) && $answers[202] == 2)
				) {
					$PersonPregnancy_RiskDPP += 2;
				}			
				break;				
			default:
				if (!empty($answers[200]) && $answers[200] == 2) {
					$PersonPregnancy_RiskDPP += 2;
				}
				break;
		}

		// 208	Хронические воспалительные заболевания придатков
		// 209	Осложнения после абортов
		// 210	Внутриматочный контрацептив
		switch ($regionNick) {
			case 'buryatiya':
			case 'astra':
				if (
					(!empty($answers[208]) && $answers[208] == 2)
					|| (!empty($answers[209]) && $answers[209] == 2)
					|| (!empty($answers[210]) && $answers[210] == 2)
				) {
					$PersonPregnancy_RiskDPP += 3;
				}
				break;
			case 'ekb':
				if (!empty($answers[208]) && $answers[208] == 2) {
					$PersonPregnancy_RiskDPP += 3;
				}
				if (!empty($answers[209]) && $answers[209] == 2) {
					$PersonPregnancy_RiskDPP += 3;
				}
				if (!empty($answers[210]) && $answers[210] == 2) {
					$PersonPregnancy_RiskDPP += 3;
				}
				break;
			//gaf 119289
			case 'penza':
			case 'ufa':
				if (
					(!empty($answers[208]) && $answers[208] == 2)
					|| (!empty($answers[209]) && $answers[209] == 2)					
					|| (!empty($answers[210]) && $answers[210] == 2)					
				) {
					$PersonPregnancy_RiskDPP += 3;
				}
				break;								
		}

		// 211	Опухоли матки и/или придатков
		if (!empty($answers[211]) && $answers[211] == 2) {
			$PersonPregnancy_RiskDPP += 4;
		}

		// 214	Пороки развития матки
		if (!empty($answers[214]) && $answers[214] == 2) {
			$PersonPregnancy_RiskDPP += 3;
		}

		// 217	Истмико-цервикальная недостаточность
		// 218	Доброкачественные заболевания шейки матки
		// 219	Деформация, перенесенная деструкция шейки матки
		switch ($regionNick) {
			case 'buryatiya':
			case 'kareliya':
			case 'astra':
				if (
					(!empty($answers[217]) && $answers[217] == 2)
					|| (!empty($answers[218]) && $answers[218] == 2)
					|| (!empty($answers[219]) && $answers[219] == 2)
				) {
					$PersonPregnancy_RiskDPP += 2;
				}
				break;
			case 'ekb':
				if (!empty($answers[217]) && $answers[217] == 2) {
					$PersonPregnancy_RiskDPP += 2;
				}
				if (!empty($answers[218]) && $answers[218] == 2) {
					$PersonPregnancy_RiskDPP += 2;
				}
				if (!empty($answers[219]) && $answers[219] == 2) {
					$PersonPregnancy_RiskDPP += 2;
				}
				break;
			//gaf 119289
			case 'penza':
			case 'ufa':
				if (
					(!empty($answers[217]) && $answers[217] == 2)
					|| (!empty($answers[218]) && $answers[218] == 2)
					|| (!empty($answers[219]) && $answers[219] == 2)
				) {
					$PersonPregnancy_RiskDPP += 2;
				}
				break;					
			default:
				if (!empty($answers[217]) && $answers[217] == 2) {
					$PersonPregnancy_RiskDPP += 2;
				}
				break;
		}

		// 221	Продолжительность, лет
		if (!empty($answers[221])) {
			if ($answers[221] >= 2 && $answers[221] <= 4) {
				$PersonPregnancy_RiskDPP += 2;
			} else if ($answers[221] >= 5) {
				$PersonPregnancy_RiskDPP += 4;
			}
		}

		// 225	Медикаментозная стимуляция овуляции
		switch ($regionNick) {
			case 'ekb':
				if (!empty($answers[225]) && $answers[225] == 2) {
					$PersonPregnancy_RiskDPP += 1;
				}
				break;
			//gaf 119289
			case 'penza':
			case 'ufa':
				if (!empty($answers[225]) && $answers[225] == 2) {
					$PersonPregnancy_RiskDPP += 1;
				}
				break;				
		}

		// 226	ЭКО
		// 227	ICSI
		switch ($regionNick) {
			case 'buryatiya':
			case 'ekb':
				if (!empty($answers[226]) && $answers[226] == 2) {
					$PersonPregnancy_RiskDPP += 1;
				}
				if (!empty($answers[227]) && $answers[227] == 2) {
					$PersonPregnancy_RiskDPP += 2;
				}
				break;
			case 'astra':
				if ((!empty($answers[226]) && $answers[226] == 2)) {
 					$PersonPregnancy_RiskDPP += 3;
 				}
				if ((!empty($answers[227]) && $answers[227] == 2)) {
					$PersonPregnancy_RiskDPP += 3;
				}
				break;
			//gaf 119289
			case 'penza':
			case 'ufa':
				if (!empty($answers[226]) && $answers[226] == 2) {
					$PersonPregnancy_RiskDPP += 1;
				}
				if (!empty($answers[227]) && $answers[227] == 2) {
					$PersonPregnancy_RiskDPP += 2;
				}
				break;				
		}

		// 229	Внутриматочные вмешательства
		switch ($regionNick) {
			case 'buryatiya':			
			case 'ekb':
				if (!empty($answers[229]) && $answers[229] == 2) {
					$PersonPregnancy_RiskDPP += 2;
				}
				break;
			//gaf 119289
			case 'penza':
			case 'ufa':
				if (!empty($answers[229]) && $answers[229] == 2) {
					$PersonPregnancy_RiskDPP += 2;
				}
				break;								
		}

		// 231	Паритет
		if (isset($answers[231])) {
			switch ($regionNick) {
				case 'buryatiya':
				case 'ekb':
					if ($answers[231] >= 4 && $answers[231] <= 7) {
						$PersonPregnancy_RiskDPP += 1;
					} else if ($answers[231] >= 8) {
						$PersonPregnancy_RiskDPP += 2;
					}
					break;
				case 'astra':
					if ($answers[231] <= 3) {
						$PersonPregnancy_RiskDPP += 1;
					} else if ($answers[231] > 3) {
						$PersonPregnancy_RiskDPP += 2;
					}
					break;
				//gaf 119289
				case 'penza':
				case 'ufa':
					if ($answers[231] > 4 && $answers[231] <= 7) {
						$PersonPregnancy_RiskDPP += 1;
					} else if ($answers[231] >= 8) {
						$PersonPregnancy_RiskDPP += 2;
					}
					break;						
				default:
					if ($answers[231] == 0) {
						$PersonPregnancy_RiskDPP += 1;
					} else if ($answers[231] >= 4 && $answers[231] <= 7) {
						$PersonPregnancy_RiskDPP += 1;
					} else if ($answers[231] >= 8) {
						$PersonPregnancy_RiskDPP += 2;
					}
					break;
			}
		}

		// 233	Аборты перед первыми предстоящими родами
		if (!empty($answers[233])) {
			if ($answers[233] == 1) {
				$PersonPregnancy_RiskDPP += 2;
			} else if ($answers[233] == 2) {
				$PersonPregnancy_RiskDPP += 3;
			} else if ($answers[233] >= 3) {
				$PersonPregnancy_RiskDPP += 4;
			}
		}

		// 234	Аборты перед повторными или после последних родов
		if (!empty($answers[234])) {
			switch ($regionNick) {
				case 'kareliya':
					if ($answers[234] >= 3) {
						$PersonPregnancy_RiskDPP += 1;
					}
					break;
				default:
					if ($answers[234] >= 3) {
						$PersonPregnancy_RiskDPP += 2;
					}
					break;
			}
		}

		// 235	Преждевременные роды
		if (!empty($answers[235])) {
			switch ($regionNick) {
				case 'kareliya':
					if ($answers[235] == 1) {
						$PersonPregnancy_RiskDPP += 2;
					} else if ($answers[235] >= 2) {
						$PersonPregnancy_RiskDPP += 7;
					}
					break;
				default:
					if ($answers[235] == 1) {
						$PersonPregnancy_RiskDPP += 2;
					} else if ($answers[235] >= 2) {
						$PersonPregnancy_RiskDPP += 3;
					}
					break;
			}
		}

		// 236	Мертворожденные, невынашивание, неразвивающаяся беременность
		if (!empty($answers[236])) {
			switch ($regionNick) {
				case 'astra':
					if ($answers[236] == 1) {
						$PersonPregnancy_RiskDPP += 2;
					} else if ($answers[236] >= 2) {
						$PersonPregnancy_RiskDPP += 3;
					}
					break;
				//gaf 119289
				case 'penza':
				case 'ufa':
					if ($answers[236] == 1) {
						$PersonPregnancy_RiskDPP += 1;
					} else if ($answers[236] >= 2) {
						$PersonPregnancy_RiskDPP += 8;
					}
					break;							
				default:
					if ($answers[236] == 1) {
						$PersonPregnancy_RiskDPP += 3;
					} else if ($answers[236] >= 2) {
						$PersonPregnancy_RiskDPP += 8;
					}
					break;
			}
		}

		// 237	Внематочная беременность
		if (!empty($answers[237])) {
			switch ($regionNick) {
				case 'buryatiya':
				case 'astra':
				case 'penza':
				case 'ufa':
					$PersonPregnancy_RiskDPP += 3;
					break;
			}
		}

		// 241	Аномалии развития у детей, рожденных ранее
		if (!empty($answers[241]) && $answers[241] == 2) {
			$PersonPregnancy_RiskDPP += 3;
		}

		// 242	Неврологические нарушения у детей, рожденных ранее
		if (!empty($answers[242]) && $answers[242] == 2) {
			$PersonPregnancy_RiskDPP += 2;
		}

		// 243	Масса доношенных детей до 2500 Г
		if (!empty($answers[243]) && $answers[243] == 2) {
			$PersonPregnancy_RiskDPP += 2;
		}

		// 244	Масса доношенных детей более 4000 г
		if (!empty($answers[244]) && $answers[244] == 2) {
			switch ($regionNick) {
				case 'astra':
					$PersonPregnancy_RiskDPP += 2;
					break;
				default:
					$PersonPregnancy_RiskDPP += 2;
					break;
			}
		}

		// 245	Рубцов на матке после кесарева сечения
		if (!empty($answers[245])) {
			switch ($regionNick) {
				case 'ekb':
					if ($answers[245] == 1) {
						$PersonPregnancy_RiskDPP += 15;
					} else if ($answers[245] >= 2) {
						$PersonPregnancy_RiskDPP += 25;
					}
					break;
				case 'astra':
					break;
				default:
					$PersonPregnancy_RiskDPP += 4;
					break;
			}
		}

		// 246	Смерть в неонатальном периоде
		if (!empty($answers[246])) {
			if ($answers[246] == 1) {
				$PersonPregnancy_RiskDPP += 2;
			} else if ($answers[246] >= 2) {
				$PersonPregnancy_RiskDPP += 7;
			}
		}

		// 255	Разрыв мягких родовых путей 3 степени
		// 256	Массивное акушерское кровотечение
		// 257	Гнойно-септические заболевания
		// 258	Антенатальная гибель плода
		// 259	Интранатальная гибель плода
		// 260	Плодоразрушающая операция
		switch ($regionNick) {
			case 'buryatiya':
			case 'kareliya':
			case 'astra':
			case 'ekb':
				// не считаем
				break;
			//gaf 119289
			case 'penza':
			case 'ufa':
				if (
					(!empty($answers[248]) && $answers[248] == 2)
					|| (!empty($answers[249]) && $answers[249] == 2)
					|| (!empty($answers[250]) && $answers[250] == 2)
					|| (!empty($answers[251]) && $answers[251] == 2)
					|| (!empty($answers[252]) && $answers[252] == 2)
					|| (!empty($answers[253]) && $answers[253] == 2)
					|| (!empty($answers[255]) && $answers[255] == 2)
					|| (!empty($answers[256]) && $answers[256] == 2)
					|| (!empty($answers[257]) && $answers[257] == 2)
					|| (!empty($answers[258]) && $answers[258] == 2)
					|| (!empty($answers[259]) && $answers[259] == 2)
					|| (!empty($answers[260]) && $answers[260] == 2)							
				) {
					$PersonPregnancy_RiskDPP += 3;
				}
				break;				
			default:
				if (
					(!empty($answers[255]) && $answers[255] == 2)
					|| (!empty($answers[256]) && $answers[256] == 2)
					|| (!empty($answers[257]) && $answers[257] == 2)
					|| (!empty($answers[258]) && $answers[258] == 2)
					|| (!empty($answers[259]) && $answers[259] == 2)
					|| (!empty($answers[260]) && $answers[260] == 2)
				) {
					$PersonPregnancy_RiskDPP += 2;
				}
				break;
		}

		// 263	Сифилис
		// 264	Вич
		// 265	Туберкулез
		// 266	Вирусный гепатит
		// 267	Бруцеллез
		// 268	Токсоплазмоз
		// 269	Цитомегаловирус
		// 270	Герпетическая инфекция
		// 271	Хламидиоз
		switch ($regionNick) {
			case 'ekb':
				if (!empty($answers[264]) && $answers[264] == 2) {
					$PersonPregnancy_RiskDPP += 3;
				}
				if (!empty($answers[265]) && $answers[265] == 2) {
					$PersonPregnancy_RiskDPP += 3;
				}
				if (!empty($answers[266]) && $answers[266] == 2) {
					$PersonPregnancy_RiskDPP += 3;
				}
				if (!empty($answers[267]) && $answers[267] == 2) {
					$PersonPregnancy_RiskDPP += 3;
				}
				if (!empty($answers[268]) && $answers[268] == 2) {
					$PersonPregnancy_RiskDPP += 3;
				}
				break;
			//gaf 119289
			case 'penza':
			case 'ufa':
				if (
					(!empty($answers[263]) && $answers[263] == 2)
					|| (!empty($answers[264]) && $answers[264] == 2)
					|| (!empty($answers[265]) && $answers[265] == 2)
					|| (!empty($answers[266]) && $answers[266] == 2)
					|| (!empty($answers[267]) && $answers[267] == 2)
					|| (!empty($answers[268]) && $answers[268] == 2)
					|| (!empty($answers[269]) && $answers[269] == 2)
					|| (!empty($answers[270]) && $answers[270] == 2)
					|| (!empty($answers[271]) && $answers[271] == 2)
					|| (!empty($answers[603]) && $answers[603] > 0)
				) {
					$PersonPregnancy_RiskDPP += 3;
				}
				break;					
			default:
				if (
					(!empty($answers[263]) && $answers[263] == 2)
					|| (!empty($answers[264]) && $answers[264] == 2)
					|| (!empty($answers[265]) && $answers[265] == 2)
					|| (!empty($answers[266]) && $answers[266] == 2)
					|| (!empty($answers[267]) && $answers[267] == 2)
					|| (!empty($answers[268]) && $answers[268] == 2)
					|| (!empty($answers[269]) && $answers[269] == 2)
					|| (!empty($answers[270]) && $answers[270] == 2)
					|| (!empty($answers[271]) && $answers[271] == 2)
				) {
					$PersonPregnancy_RiskDPP += 3;
				}
				break;
		}

		// 272	Злокачественные новообразования
		if (!empty($answers[272]) && $answers[272] == 2) {
			switch ($regionNick) {
				case 'ekb':
					$PersonPregnancy_RiskDPP += 25;
					break;
			}
		}

		// 274	сахарный диабет
		if (!empty($answers[274]) && $answers[274] == 2) {
			switch ($regionNick) {
				case 'ekb':
					$PersonPregnancy_RiskDPP += 25;
					break;
				default:
					$PersonPregnancy_RiskDPP += 10;
					break;
			}
		}

		// 275	Заболевания надпочечеников, нейрообменный эндокринный синдром
		if (!empty($answers[275]) && $answers[275] == 2) {
			switch ($regionNick) {
				case 'ekb':
					$PersonPregnancy_RiskDPP += 25;
					break;
				default:
					$PersonPregnancy_RiskDPP += 10;
					break;
			}
		}

		// 276	Заболевания щитовидной железы без нарушения функций
		if (!empty($answers[276]) && $answers[276] == 2) {
			switch ($regionNick) {
				case 'buryatiya':
				case 'kareliya':
					break;
				case 'astra':
					$PersonPregnancy_RiskDPP += 2;
					break;
				case 'ekb':
					$PersonPregnancy_RiskDPP += 15;
					break;
				//gaf 119289
				case 'penza':
				case 'ufa':
					$PersonPregnancy_RiskDPP += 7;
					break;						
				default:
					$PersonPregnancy_RiskDPP += 10;
					break;
			}
		}

		// 277	Тиреотоксикоз
		if (!empty($answers[277]) && $answers[277] == 2) {
			switch ($regionNick) {
				case 'buryatiya':
					$PersonPregnancy_RiskDPP += 7;
					break;
				case 'ekb':
					$PersonPregnancy_RiskDPP += 25;
					break;
			}
		}

		// 278	Гипотериоз
		if (!empty($answers[278]) && $answers[278] == 2) {
			switch ($regionNick) {
				case 'buryatiya':
					$PersonPregnancy_RiskDPP += 7;
					break;
				case 'ekb':
					$PersonPregnancy_RiskDPP += 25;
					break;
			}
		}

		// 279	Микро- и макроаденома гипофиза
		if (!empty($answers[279]) && $answers[279] == 2) {
			switch ($regionNick) {
				case 'ekb':
					$PersonPregnancy_RiskDPP += 25;
					break;
			}
		}

		// 280	Нарушения жирового обмена
		if (!empty($answers[280]) && $answers[280] == 2) {
			switch ($regionNick) {
				case 'ekb':
					$PersonPregnancy_RiskDPP += 15;
					break;
			}
		}

		// 281	Ожирение
		if (!empty($answers[281]) && $answers[281] == 2) {
			switch ($regionNick) {
				case 'buryatiya':
				case 'astra':
					$PersonPregnancy_RiskDPP += 2;
					break;
				//gaf 119289
				case 'penza':
				case 'ufa':
					$PersonPregnancy_RiskDPP += 2;
					break;								
			}
		}

		// 283	Тромбозы
		// 284	Тромбоэмболии
		// 285	Тромбофлебиты
		// 286	Гемолитическая анемия
		// 287	Апластическая анемия
		// 288	Тяжелая железодефицитная анемия
		// 289	Гемобластозы
		// 290	Тромбоцитопения
		// 291	Болезнь Виллебранда
		// 301	Эпилепсия
		// 303	Нарушения мозгового кровообращения
		// 304	Состояния после перенесенных инсультов
		// 309	Оперированные пороки сердца
		// 310	Аритмии
		// 311	Миокардиты
		// 312	Кардиомиопатии
		// 317	НЦД
		// 318	Сосудистые мальформации
		// 319	Аневризмы сосудов
		// 322	Компенсированная патология (без дыхательной недостаточности)
		// 323	Патология, сопровождающаяся развитием легочной или сердечной недостаточности
		// 337	Хронический гастрит
		// 338	Дуоденит
		// 339	Колит
		// 340	Диффузные заболевания соединительной ткани
		// 341	Болезни печени
		// 342	Токсический гепатит
		// 343	Острый гепатит
		// 344	Хронический гепатит
		// 345	Цирроз
		// 347	Черепно-мозговые
		// 348	Позвоночника
		// 349	Таза
		switch ($regionNick) {
			case 'ekb':
				if (!empty($answers[283]) && $answers[283] == 2) {
					$PersonPregnancy_RiskDPP += 25;
				}
				if (!empty($answers[284]) && $answers[284] == 2) {
					$PersonPregnancy_RiskDPP += 25;
				}
				if (!empty($answers[285]) && $answers[285] == 2) {
					$PersonPregnancy_RiskDPP += 25;
				}
				if (!empty($answers[286]) && $answers[286] == 2) {
					$PersonPregnancy_RiskDPP += 25;
				}
				if (!empty($answers[287]) && $answers[287] == 2) {
					$PersonPregnancy_RiskDPP += 25;
				}
				if (!empty($answers[288]) && $answers[288] == 2) {
					$PersonPregnancy_RiskDPP += 25;
				}
				if (!empty($answers[289]) && $answers[289] == 2) {
					$PersonPregnancy_RiskDPP += 25;
				}
				if (!empty($answers[290]) && $answers[290] == 2) {
					$PersonPregnancy_RiskDPP += 25;
				}
				if (!empty($answers[291]) && $answers[291] == 2) {
					$PersonPregnancy_RiskDPP += 25;
				}
				if (!empty($answers[301]) && $answers[301] == 2) {
					$PersonPregnancy_RiskDPP += 25;
				}
				if (!empty($answers[303]) && $answers[303] == 2) {
					$PersonPregnancy_RiskDPP += 25;
				}
				if (!empty($answers[304]) && $answers[304] == 2) {
					$PersonPregnancy_RiskDPP += 25;
				}
				if (!empty($answers[309]) && $answers[309] == 2) {
					$PersonPregnancy_RiskDPP += 25;
				}
				if (!empty($answers[310]) && $answers[310] == 2) {
					$PersonPregnancy_RiskDPP += 25;
				}
				if (!empty($answers[311]) && $answers[311] == 2) {
					$PersonPregnancy_RiskDPP += 25;
				}
				if (!empty($answers[312]) && $answers[312] == 2) {
					$PersonPregnancy_RiskDPP += 25;
				}
				if (!empty($answers[317]) && $answers[317] == 2) {
					$PersonPregnancy_RiskDPP += 25;
				}
				if (!empty($answers[318]) && $answers[318] == 2) {
					$PersonPregnancy_RiskDPP += 10;
				}
				if (!empty($answers[319]) && $answers[319] == 2) {
					$PersonPregnancy_RiskDPP += 25;
				}
				if (!empty($answers[322]) && $answers[322] == 2) {
					$PersonPregnancy_RiskDPP += 15;
				}
				if (!empty($answers[323]) && $answers[323] == 2) {
					$PersonPregnancy_RiskDPP += 25;
				}
				if (!empty($answers[337]) && $answers[337] == 2) {
					$PersonPregnancy_RiskDPP += 10;
				}
				if (!empty($answers[338]) && $answers[338] == 2) {
					$PersonPregnancy_RiskDPP += 10;
				}
				if (!empty($answers[339]) && $answers[339] == 2) {
					$PersonPregnancy_RiskDPP += 10;
				}
				if (!empty($answers[340]) && $answers[340] == 2) {
					$PersonPregnancy_RiskDPP += 25;
				}
				if (!empty($answers[341]) && $answers[341] == 2) {
					$PersonPregnancy_RiskDPP += 25;
				}
				if (!empty($answers[342]) && $answers[342] == 2) {
					$PersonPregnancy_RiskDPP += 25;
				}
				if (!empty($answers[343]) && $answers[343] == 2) {
					$PersonPregnancy_RiskDPP += 25;
				}
				if (!empty($answers[344]) && $answers[344] == 2) {
					$PersonPregnancy_RiskDPP += 25;
				}
				if (!empty($answers[345]) && $answers[345] == 2) {
					$PersonPregnancy_RiskDPP += 25;
				}
				if (!empty($answers[347]) && $answers[347] == 2) {
					$PersonPregnancy_RiskDPP += 25;
				}
				if (!empty($answers[348]) && $answers[348] == 2) {
					$PersonPregnancy_RiskDPP += 25;
				}
				if (!empty($answers[349]) && $answers[349] == 2) {
					$PersonPregnancy_RiskDPP += 25;
				}
				break;
			//gaf 119289
			case 'penza':
			case 'ufa':
				if ((!empty($answers[283]) && $answers[283] == 2) 
					|| (!empty($answers[284]) && $answers[284] == 2)  
					|| (!empty($answers[285]) && $answers[285] == 2) 
					)
					{
					$PersonPregnancy_RiskDPP += 2;
				}
				break;
			case 'astra':
				if ((!empty($answers[283]) && $answers[283] == 2) 
					|| (!empty($answers[284]) && $answers[284] == 2)  
					|| (!empty($answers[285]) && $answers[285] == 2) 
					)
					{
					$PersonPregnancy_RiskDPP += 2;
				}
				break;
		}

		// 292	Врожденные коагулопатии
		// 293	Приобретенные коагулопатии
		switch ($regionNick) {
			case 'ekb':
				if (!empty($answers[292]) && $answers[292] == 2) {
					$PersonPregnancy_RiskDPP += 25;
				}
				if (!empty($answers[293]) && $answers[293] == 2) {
					$PersonPregnancy_RiskDPP += 25;
				}
				break;
			//gaf 119289
			case 'penza':
			case 'ufa':
			case 'astra':
				if (
					(!empty($answers[290]) && $answers[290] == 2)
					|| (!empty($answers[291]) && $answers[291] == 2)
					|| (!empty($answers[292]) && $answers[292] == 2)
					|| (!empty($answers[293]) && $answers[293] == 2)
				) {
					$PersonPregnancy_RiskDPP += 2;
				}
				break;
			default:
				if (
					(!empty($answers[292]) && $answers[292] == 2)
					|| (!empty($answers[293]) && $answers[293] == 2)
				) {
					$PersonPregnancy_RiskDPP += 2;
				}
				break;
		}

		// 294	Гемоглобин (г/л)
		switch ($regionNick) {
			//gaf 119289
			case 'penza':
			case 'ufa':
				if (!empty($answers[294])) {
					if ($answers[294] <= 89) {
						$PersonPregnancy_RiskDPP += 4;
					} else if ($answers[294] >= 90 && $answers[294] <= 100) {
						$PersonPregnancy_RiskDPP += 2;
					} else if ($answers[294] > 100 && $answers[294] < 110) {
						$PersonPregnancy_RiskDPP += 1;
					}
				}
			break;
			default:
				if (!empty($answers[294])) {
					if ($answers[294] <= 90) {
						$PersonPregnancy_RiskDPP += 4;
					} else if ($answers[294] > 90 && $answers[294] <= 100) {
						$PersonPregnancy_RiskDPP += 2;
					} else if ($answers[294] > 100 && $answers[294] <= 110) {
						$PersonPregnancy_RiskDPP += 1;
					}
				}
				break;
		}

		// 301	Эпилепсия
		// 302	Рассеянный склероз
		// 303	Нарушения мозгового кровообращения
		// 304	Состояния после перенесенных инсультов
		// 305	Миастения
		switch ($regionNick) {
			case 'ekb':
				if (!empty($answers[302]) && $answers[302] == 2) {
					$PersonPregnancy_RiskDPP += 25;
				}
				if (!empty($answers[305]) && $answers[305] == 2) {
					$PersonPregnancy_RiskDPP += 25;
				}
				break;
			case 'astra':
				if ((!empty($answers[301]) && $answers[301] == 2)
					|| (!empty($answers[302]) && $answers[302] == 2)
					|| (!empty($answers[303]) && $answers[303] == 2)
					|| (!empty($answers[304]) && $answers[304] == 2)
					|| (!empty($answers[305]) && $answers[305] == 2)) {
					$PersonPregnancy_RiskDPP += 2;
				}
				break;
		}

		// 307	Пороки сердца без нарушения кровообращения
		if (!empty($answers[307]) && $answers[307] == 2) {
			switch ($regionNick) {
				case 'ekb':
					$PersonPregnancy_RiskDPP += 15;
					break;
				default:
					$PersonPregnancy_RiskDPP += 3;
					break;
			}
		}

		// 308	Пороки сердца с нарушением кровообращения
		if (!empty($answers[308]) && $answers[308] == 2) {
			switch ($regionNick) {
				case 'ekb':
					$PersonPregnancy_RiskDPP += 25;
					break;
				default:
					$PersonPregnancy_RiskDPP += 10;
					break;
			}
		}

		// 313	Хроническая артериальная гипертензия 1 степени
		// 314	Хроническая артериальная гипертензия 2 степени
		// 315	Хроническая артериальная гипертензия 3 степени
		switch ($regionNick) {
			case 'buryatiya':
			case 'kareliya':
			case 'astra':
				if (!empty($answers[313]) && $answers[313] == 2) {
					$PersonPregnancy_RiskDPP += 2;
				}
				if (!empty($answers[314]) && $answers[314] == 2) {
					$PersonPregnancy_RiskDPP += 8;
				}
				if (!empty($answers[315]) && $answers[315] == 2) {
					$PersonPregnancy_RiskDPP += 12;
				}
				break;
			case 'ekb':
				if (
					(!empty($answers[313]) && $answers[313] == 2)
					|| (!empty($answers[314]) && $answers[314] == 2)
					|| (!empty($answers[315]) && $answers[315] == 2)
				) {
					$PersonPregnancy_RiskDPP += 25;
				}
				break;
			//gaf 119289
			case 'penza':
			case 'ufa':
				if (!empty($answers[309]) && $answers[309] == 2) {
					$PersonPregnancy_RiskDPP += 10;
				}								
				if (!empty($answers[310]) && $answers[310] == 2) {
					$PersonPregnancy_RiskDPP += 3;
				}				
				if (!empty($answers[313]) && $answers[313] == 2) {
					$PersonPregnancy_RiskDPP += 2;
				}
				if (!empty($answers[314]) && $answers[314] == 2) {
					$PersonPregnancy_RiskDPP += 8;
				}
				if (!empty($answers[315]) && $answers[315] == 2) {
					$PersonPregnancy_RiskDPP += 12;
				}
				break;				
		}

		// 316	Гипотензитивный синдром
		if (!empty($answers[316]) && $answers[316] == 2) {
			switch ($regionNick) {
				case 'buryatiya':
				case 'kareliya':
				case 'astra':
					$PersonPregnancy_RiskDPP += 2;
					break;
				case 'ekb':
					$PersonPregnancy_RiskDPP += 25;
					break;
				//gaf 119289
				case 'penza':
				case 'ufa':
					$PersonPregnancy_RiskDPP += 2;
					break;					
			}
		}

		// 320	Варикозная болезнь
		if (!empty($answers[320]) && $answers[320] == 2) {
			switch ($regionNick) {
				case 'buryatiya':
				case 'astra':
					$PersonPregnancy_RiskDPP += 2;
					break;
				//gaf 119289
				case 'penza':
				case 'ufa':
					$PersonPregnancy_RiskDPP += 2;
					break;					
			}
		}
		
		// 323	Патология, сопровождающаяся развитием легочной или сердечной недостаточности
		if (!empty($answers[323]) && $answers[323] == 2) {
			switch ($regionNick) {
				case 'penza':
				case 'ufa':
				case 'astra':
					$PersonPregnancy_RiskDPP += 10;
					break;
			}
		}				

		// 325	Хронические пиелонефрит
		// 326	Инфекции мочевыводящих путей вне обострения
		// 327	Заболевания почек, сопровождающиеся почечной недостаточностью или артериальной гипертензией
		// 328	Единственная почка, аномалии мочевыводительной системы
		// 329	Нефрэктомия
		// 330	Гломерулонефрит
		switch ($regionNick) {
			case 'ekb':
				if (!empty($answers[325]) && $answers[325] == 2) {
					$PersonPregnancy_RiskDPP += 10;
				}
				if (!empty($answers[326]) && $answers[326] == 2) {
					$PersonPregnancy_RiskDPP += 10;
				}
				if (!empty($answers[327]) && $answers[327] == 2) {
					$PersonPregnancy_RiskDPP += 25;
				}
				if (!empty($answers[328]) && $answers[328] == 2) {
					$PersonPregnancy_RiskDPP += 25;
				}
				if (!empty($answers[329]) && $answers[329] == 2) {
					$PersonPregnancy_RiskDPP += 25;
				}
				if (!empty($answers[330]) && $answers[330] == 2) {
					$PersonPregnancy_RiskDPP += 25;
				}
				break;
			case 'astra':
				if (
					(!empty($answers[325]) && $answers[325] == 2)
					|| (!empty($answers[326]) && $answers[326] == 2)
					|| (!empty($answers[328]) && $answers[328] == 2)
					|| (!empty($answers[607]) && $answers[607] > 0)							
				) {
					$PersonPregnancy_RiskDPP += 4;
				}
				break;				
			default:
				if (
					(!empty($answers[325]) && $answers[325] == 2)
					|| (!empty($answers[326]) && $answers[326] == 2)
					|| (!empty($answers[327]) && $answers[327] == 2)
					//gaf 01122017
					|| (!empty($answers[607]) && $answers[607] > 0)							
					|| (!empty($answers[328]) && $answers[328] == 2)
					|| (!empty($answers[329]) && $answers[329] == 2)
					|| (!empty($answers[330]) && $answers[330] == 2)
				) {
					$PersonPregnancy_RiskDPP += 4;
				}
				break;
		}

		// 332	Миопия 1 и 2 степени без изменений на глазном дне
		// 333	Миопия высокой степени с изменениями на глазном дне
		// 334	Отслойка сетчатки в анамнезе
		// 335	Глаукома
		switch ($regionNick) {
			case 'ekb':
				if (!empty($answers[332]) && $answers[332] == 2) {
					$PersonPregnancy_RiskDPP += 10;
				}
				if (!empty($answers[333]) && $answers[333] == 2) {
					$PersonPregnancy_RiskDPP += 25;
				}
				if (!empty($answers[334]) && $answers[334] == 2) {
					$PersonPregnancy_RiskDPP += 25;
				}
				if (!empty($answers[335]) && $answers[335] == 2) {
					$PersonPregnancy_RiskDPP += 25;
				}
				break;
			default:
				if (
					(!empty($answers[332]) && $answers[332] == 2)
					|| (!empty($answers[333]) && $answers[333] == 2)
					|| (!empty($answers[334]) && $answers[334] == 2)
					|| (!empty($answers[335]) && $answers[335] == 2)
				) {
					$PersonPregnancy_RiskDPP += 2;
				}
				break;
		}

		// 351	Положительный волчаночный антикоагулянт
		// 352	IgG от 9,99 и выше
		// 353	IgM от 9,99 и выше
		switch ($regionNick) {
			case 'buryatiya':
			case 'astra':
			case 'penza':
			case 'ufa':
				if (!empty($answers[351]) && $answers[351] == 2) {
					$PersonPregnancy_RiskDPP += 4;
				}
				if (!empty($answers[352]) && $answers[352] == 2) {
					$PersonPregnancy_RiskDPP += 2;
				}
				if (!empty($answers[353]) && $answers[353] == 2) {
					$PersonPregnancy_RiskDPP += 3;
				}
				break;
		}
		
		// 599 Рубец на матке
		// 600 Репродуктивные потери в анамнезе
		//gaf 119289
		switch ($regionNick) {
			case 'penza':
			case 'ufa':
				if (!empty($answers[599]) && $answers[599] == 2) {
					$PersonPregnancy_RiskDPP += 4;
				}
				if (!empty($answers[602])) {
					if ($answers[602] == 1) {
						$PersonPregnancy_RiskDPP += 5;
					} else if ($answers[602] == 2) {
						$PersonPregnancy_RiskDPP += 5;
					} else if ($answers[602] == 3) {
						$PersonPregnancy_RiskDPP += 7;
					} else if ($answers[602] == 4) {
						$PersonPregnancy_RiskDPP += 20;
					}
				}
				if (!empty($answers[604]) && $answers[604] == 2) {
					$PersonPregnancy_RiskDPP += 2;
				}
				if (!empty($answers[605]) && ($answers[605] == 1 || $answers[605] == 2 || $answers[605] == 3)) {
					$PersonPregnancy_RiskDPP += 2;
				}
				if (!empty($answers[606]) && $answers[606] == 1) {
					$PersonPregnancy_RiskDPP += 5;
				}				
				if (!empty($answers[606]) && $answers[606] == 2) {
					$PersonPregnancy_RiskDPP += 10;
				}				
				break;
			case 'astra':
				if (!empty($answers[599]) && $answers[599] == 2) {
					$PersonPregnancy_RiskDPP += 4;
				}
				if (!empty($answers[605]) && ($answers[605] == 1 || $answers[605] == 2 || $answers[605] == 3)) {
					$PersonPregnancy_RiskDPP += 2;
				}
				break;				
		}

		// 755	Заболевания щитовидной железы с нарушением функций
		if (!empty($answers[755]) && $answers[755] == 2) {
            switch ($regionNick) {
                case 'astra':
                    $PersonPregnancy_RiskDPP += 7;
                    break;
                default:
                    break;
            }
        }

		$query = "
            update 
                PersonPregnancy 
            set 
                PersonPregnancy_RiskDPP = :PersonPregnancy_RiskDPP 
            where 
                PersonRegister_id = :PersonRegister_id";

		// обновляем в анкете
		$this->db->query($query, array(
			'PersonRegister_id' => $data['PersonRegister_id'],
			'PersonPregnancy_RiskDPP' => $PersonPregnancy_RiskDPP
		));

		// пересчитать степнь риска
		$this->recalculateRiskType($data);

		return $PersonPregnancy_RiskDPP;
	}

	/**
	 * Удаление ответов на вопросы из анкет по беременности
	 */
	function deletePregnancyQuestionAnswers($data) {
		$params = array(
			'PersonRegister_id' => $data['PersonRegister_id'],
		);
		$filters = array();

		if (!empty($data['PregnancyScreen_id'])) {
			$filters[] = "PregnancyScreen_id = :PregnancyScreen_id";
			$params['PregnancyScreen_id'] = $data['PregnancyScreen_id'];
		}
		if (!empty($data['DispClass_id'])) {
			$filters[] = "DispClass_id = :DispClass_id";
			$params['DispClass_id'] = $data['DispClass_id'];
		}

		$filters_str = count($filters)>0?('and '.implode(' and ', $filters)):'';

		$query = "
            delete from 
                PregnancyQuestion 
            where 
                PregnancyQuestion_id in (
					select PQ.PregnancyQuestion_id
					from v_PregnancyQuestion PQ
					left join v_QuestionType QT on QT.QuestionType_id = PQ.QuestionType_id
					where PQ.PersonRegister_id = :PersonRegister_id
					{$filters_str}
				)
			returning null as \"Error_Code\", null as \"Error_Code\"
		";
		//echo getDebugSQL($query, $params);exit;
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('','Ошибка при удалении ответов из анкет по беременности');
		}
		return $response;
	}

	/**
	 * Сохранение данных результата предыдущей беременности
	 */
	function savePersonPregnancyResult($data) {
		$params = array(
			'PersonPregnancyResult_id' => !empty($data['PersonPregnancyResult_id'])?$data['PersonPregnancyResult_id']:null,
			'PersonPregnancy_id' => $data['PersonPregnancy_id'],
			'PersonPregnancyResult_Num' => $data['PersonPregnancyResult_Num'],
			'PersonPregnancyResult_Year' => $data['PersonPregnancyResult_Year'],
			'PregnancyResult_id' => $data['PregnancyResult_id'],
			'PersonPregnancyResult_OutcomPeriod' => $data['PersonPregnancyResult_OutcomPeriod'],
			'BirthChildResult_id' => !empty($data['BirthChildResult_id'])?$data['BirthChildResult_id']:null,
			'PersonPregnancyResult_WeigthChild' => !empty($data['PersonPregnancyResult_WeigthChild'])?$data['PersonPregnancyResult_WeigthChild']:null,
			'ChildStateResult_id' => !empty($data['ChildStateResult_id'])?$data['ChildStateResult_id']:null,
			'PersonPregnancyResult_AgeChild' => !empty($data['PersonPregnancyResult_AgeChild'])?$data['PersonPregnancyResult_AgeChild']:null,
			'PersonPregnancyResult_Descr' => !empty($data['PersonPregnancyResult_Descr'])?$data['PersonPregnancyResult_Descr']:null,
			'pmUser_id' => $data['pmUser_id'],
		);

		if (empty($params['PersonPregnancyResult_id'])) {
			$procedure = 'p_PersonPregnancyResult_ins';
		} else {
			$procedure = 'p_PersonPregnancyResult_upd';
		}

		$query = "
		    select 
		        PersonPregnancyResult_id as \"PersonPregnancyResult_id\",
		        Error_Code as \"Error_Code\",
		        Error_Message as \"Error_Msg\"
		    from {$procedure}
		    (
		        PersonPregnancyResult_id := :PersonPregnancyResult_id,
				PersonPregnancy_id := :PersonPregnancy_id,
				PersonPregnancyResult_Num := :PersonPregnancyResult_Num,
				PersonPregnancyResult_Year := :PersonPregnancyResult_Year,
				PregnancyResult_id := :PregnancyResult_id,
				PersonPregnancyResult_OutcomPeriod := :PersonPregnancyResult_OutcomPeriod,
				BirthChildResult_id := :BirthChildResult_id,
				PersonPregnancyResult_WeigthChild := :PersonPregnancyResult_WeigthChild,
				ChildStateResult_id := :ChildStateResult_id,
				PersonPregnancyResult_AgeChild := :PersonPregnancyResult_AgeChild,
				PersonPregnancyResult_Descr := :PersonPregnancyResult_Descr,
				pmUser_id := :pmUser_id
		    )
		";
		//echo getDebugSQL($query, $params);exit;
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при сохранении результата предыдущей беременности');
		}
		return $resp;
	}

	/**
	 * Удаление данных результата предыдущей беременности
	 */
	function deletePersonPregnancyResult($data) {
		$params = array('PersonPregnancyResult_id' => $data['PersonPregnancyResult_id']);
		$query = "
		    select 
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
            from p_PersonPregnancyResult_del
            (
            	PersonPregnancyResult_id := :PersonPregnancyResult_id
            )
		";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('','Ошибка при удалении данных результата предыдущей беременности');
		}
		return $response;
	}

	/**
	 * Сохранение ответа на вопрос из анкеты по беременности
	 */
	function savePregnancyQuestion($data) {
		$params = array(
			'PregnancyQuestion_id' => !empty($data['PregnancyQuestion_id'])?$data['PregnancyQuestion_id']:null,
			'PersonRegister_id' => $data['PersonRegister_id'],
			'PregnancyScreen_id' => !empty($data['PregnancyScreen_id'])?$data['PregnancyScreen_id']:null,
			'QuestionType_id' => $data['QuestionType_id'],
			'AnswerClass_id' => $data['AnswerClass_id'],
			'PregnancyQuestion_IsTrue' => ($data['AnswerType_id']==1)?$data['Answer']:null,	//да/нет
			'PregnancyQuestion_AnswerInt' => ($data['AnswerType_id']==5 || $data['AnswerType_id']==8)?$data['Answer']:null,        //число
			'PregnancyQuestion_AnswerFloat' => ($data['AnswerType_id']==13)?$data['Answer']:null,        //дробное число
			'PregnancyQuestion_AnswerText' => ($data['AnswerType_id']==2)?$data['Answer']:null,		//текст
			'PregnancyQuestion_ValuesStr' => ($data['AnswerType_id']==3)?$data['Answer']:null,		//справочник
			'pmUser_id' => $data['pmUser_id']
		);

		if (empty($params['PregnancyQuestion_id'])) {
			$procedure = 'p_PregnancyQuestion_ins';
		} else {
			$procedure = 'p_PregnancyQuestion_upd';
		}

		$query = "
            select 
                PregnancyQuestion_id as \"PregnancyQuestion_id\",
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
            from {$procedure}
            (
                PregnancyQuestion_id := :PregnancyQuestion_id::bigint,
				PersonRegister_id := :PersonRegister_id,
				PregnancyScreen_id := :PregnancyScreen_id,
				QuestionType_id := :QuestionType_id,
				PregnancyQuestion_IsTrue := :PregnancyQuestion_IsTrue,
				PregnancyQuestion_AnswerInt := :PregnancyQuestion_AnswerInt,
				PregnancyQuestion_AnswerFloat:= :PregnancyQuestion_AnswerFloat,
				PregnancyQuestion_AnswerText := :PregnancyQuestion_AnswerText,
				AnswerClass_id := :AnswerClass_id,
				PregnancyQuestion_ValuesStr := :PregnancyQuestion_ValuesStr,
				pmUser_id := :pmUser_id
            )
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при сохранении ответа в анкету для регистрации беременной');
		}
		return $resp;
	}

	/**
	 * Проверка возможности удаления записи регистра беременных
	 */
	function checkDeletePersonRegister($data) {
		$params = array('PersonRegister_id' => $data['PersonRegister_id']);
		$linkObjectList = array('PersonPregnancy','PregnancyScreen','BirthCertificate','BirthSpecStac','DeathMother');

		$checkExistsObjectList = array();
		foreach($linkObjectList as $object) {
			$checkExistsObjectList[] = "
				when exists(select * from {$object} where PersonRegister_id = PR.PersonRegister_id) then 1
			";
		}

		$existsLinks = 0;
		if (count($checkExistsObjectList) > 0) {
			$existsObjectQuery = implode('', $checkExistsObjectList);

			$query = "
				select
					case
						{$existsObjectQuery}
						else 0
					end as \"ExistsLinks\"
				from
					v_PersonRegister PR
				where
					PR.PersonRegister_id = :PersonRegister_id
				limit 1
			";
			$existsLinks = $this->getFirstResultFromQuery($query, $params);
			if ($existsLinks === false) {
				return $this->createError('','Ошибка при проверке подчиненных записей регистра');
			}
		}
		if ($existsLinks) {
			return $this->createError(1,'Удаление записи регистра недоступно, так как существуют связанные записи.');
		}

		return array(array('success' => true));
	}

	/**
	 * Проставление отметки об удалении записи из регистра беременных.
	 */
	function deletePersonRegister($data) {
		$resp = $this->checkDeletePersonRegister($data);
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}

		$params = array(
			'PersonRegister_id' => $data['PersonRegister_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$this->beginTransaction();
		//Проставляется исключение из региста с причиной "Другое"
		$query = "
		    with cte as 
		    (
		        select 
		            dbo.tzGetDate() as date,
		            (select PersonRegisterOutCause_id from v_PersonRegisterOutCause where PersonRegisterOutCause_SysNick = 'other' limit 1) as PersonRegisterOutCause_id
		        limit 1
		    )
            update PersonRegister
            set
                PersonRegisterOutCause_id = (select PersonRegisterOutCause_id from cte),
                PersonRegister_disDate = (select date from cte),
                PersonRegister_updDT = (select date from cte),
                pmUser_updID = :pmUser_id
            where PersonRegister_id = :PersonRegister_id
            returning null as \"Error_Code\", null as \"Error_Msg\"
		";

		$resp = $this->queryResult($query, $params);
		if (!$this->isSuccessful($resp)) {
			$this->rollbackTransaction();
			return $resp;
		}

		//Запись в регистре помечается как удаленная
		$query = "
			select
			    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from p_PersonRegister_del
			(
			    PersonRegister_id := :PersonRegister_id,
				pmUser_id := :pmUser_id
			)
		";
		$resp = $this->queryResult($query, $params);
		if (!$this->isSuccessful($resp)) {
			$this->rollbackTransaction();
			return $resp;
		}

		$this->commitTransaction();
		return array(array('success' => true));
	}

	/**
	 * Удаление анкеты о беременности
	 */
	function deletePersonPregnancy($data, $isAllowTransaction = true) {
		$params = array('PersonPregnancy_id' => $data['PersonPregnancy_id']);

		$this->isAllowTransaction = $isAllowTransaction;
		$this->beginTransaction();

		$PersonRegister_id = $this->getPersonRegisterId($params);
		if (!$PersonRegister_id) {
			$this->rollbackTransaction();
			return $this->createError('','Ошибка при получении идентификатора регистра');
		}

		//Удаление списка предыдущих беременностей из анкеты
		$PersonPregnancyResultList = $this->queryResult("
			select 
			    PersonPregnancyResult_id as \"PersonPregnancyResult_id\"
			from 
			    v_PersonPregnancyResult 
			where 
			    PersonPregnancy_id = :PersonPregnancy_id
		", $params);
		if (!is_array($PersonPregnancyResultList)) {
			$this->rollbackTransaction();
			return $this->createError('','Ошибка при получении списка предыдущих беременностей');
		}
		foreach($PersonPregnancyResultList as $PersonPregnancyResult) {
			$resp = $this->deletePersonPregnancyResult($PersonPregnancyResult);
			if (!$this->isSuccessful($resp)) {
				$this->rollbackTransaction();
				return $resp;
			}
		}

		//Удаление ответов на вопросы анкеты
		$resp = $this->deletePregnancyQuestionAnswers(array(
			'PersonRegister_id' => $PersonRegister_id,
			'DispClass_id' => 14
		));
		if (!$this->isSuccessful($resp)) {
			$this->rollbackTransaction();
			return $resp;
		}

		//Удаление анкеты
		$query = "
			select 
			    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from p_PersonPregnancy_del
			(
			    PersonPregnancy_id := :PersonPregnancy_id
			)
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			$this->rollbackTransaction();
			return $this->createError('','Ошибка при удалении анкеты регистрации беременности');
		}
		if (!$this->isSuccessful($resp)) {
			$this->rollbackTransaction();
			return $resp;
		}

		$this->recalculateRiskType(array('PersonRegister_id' => $PersonRegister_id));

		$this->isAllowTransaction = $isAllowTransaction;
		$this->commitTransaction();

		return array(array('success' => true));
	}

	/**
	 * Поиск записи в регистре беременных по данным движения через исход беременности
	 */
	function getPersonRegisterByEvnSection($data) {
		$params = array(
			'Person_id' => $data['Person_id'],
			'EvnSection_id' => !empty($data['EvnSection_id'])?$data['EvnSection_id']:null,
			'EvnSection_setDate' => $data['EvnSection_setDate'],
			'EvnSection_disDate' => !empty($data['EvnSection_disDate'])?$data['EvnSection_disDate']:null,
		);
		$query = "
		    with cte as 
		    (
		        select dateadd('year', 50, dbo.tzGetDate()) as bigdate
		    )

			select
				PR.PersonRegister_id as \"PersonRegister_id\"
			from
				v_PersonRegister PR
				inner join v_PersonRegisterType PRT on PRT.PersonRegisterType_id = PR.PersonRegisterType_id
					and PRT.PersonRegisterType_SysNick ilike 'pregnancy'
				left join v_BirthSpecStac BSS on BSS.PersonRegister_id = PR.PersonRegister_id
			where
				PR.Person_id = CAST(:Person_id as bigint)
				and (
					BSS.EvnSection_id = CAST(:EvnSection_id as bigint)
					or (
						not exists(select * from v_BirthSpecStac where EvnSection_id = CAST(:EvnSection_id as bigint))
						and PR.PersonRegister_setDate <= CAST(:EvnSection_setDate as date)
						and PR.PersonRegister_setDate <= coalesce(CAST(:EvnSection_disDate as date), (select bigdate from cte))
						and PR.PersonRegister_setDate >= dateadd('month', -11, CAST(:EvnSection_setDate as date))
						and (PR.PersonRegister_disDate is null or PR.PersonRegister_disDate >= CAST(:EvnSection_disDate as date))
						and BSS.EvnSection_id is null
					)
				)
			order by
				PR.PersonRegister_setDate desc
		    limit 1
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при получении идентификатора записи в регистре беременных');
		}
		return array(array(
			'success' => true,
			'Error_Msg' => '',
			'PersonRegister_id' => isset($resp[0])?$resp[0]['PersonRegister_id']:null,
		));
	}

	/**
	 * Получение идентфикатора записи из регистра беременных
	 */
	function getPersonRegisterByEvnVizitPL($data) {
		$params = array(
			'Person_id' => $data['Person_id'],
			'Evn_id' => !empty($data['Evn_id'])?$data['Evn_id']:null,
			'date' => !empty($data['date'])?$data['date']:null,
		);

		$query = "
			select
				PR.PersonRegister_id as \"PersonRegister_id\",
				PR.PersonDisp_id as \"PersonDisp_id\"
			from
				v_PersonRegister PR
				left join v_PersonPregnancy PP on PP.PersonRegister_id = PR.PersonRegister_id
				left join v_BirthSpecStac BSS on BSS.PersonRegister_id = PR.PersonRegister_id
			where
				PP.Person_id = :Person_id
				and (
					(PR.PersonRegister_setDate <= CAST(:date as date) and coalesce(PR.PersonRegister_disDate, CAST(:date as date)) >= CAST(:date as date))
					or PP.Evn_id = :Evn_id
					or BSS.Evn_id = :Evn_id
					or exists(
						select
						    * 
						from 
						    v_PregnancyScreen t
						where 
						    t.PersonRegister_id = PR.PersonRegister_id 
						and 
						    t.Evn_id = :Evn_id
					)
				)
			order by
				PR.PersonRegister_setDate desc
			limit 1
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при получении идентификатора записи в регистре беременных');
		}
		return array(array(
			'success' => true,
			'Error_Msg' => '',
			'PersonRegister_id' => !empty($resp[0]['PersonRegister_id'])?$resp[0]['PersonRegister_id']:null,
			'PersonDisp_id' => !empty($resp[0]['PersonDisp_id'])?$resp[0]['PersonDisp_id']:null,
		));
	}

	/**
	 * Обновление записи регистра беременности
	 */
    function updatePersonRegister($data) {
		$params = array(
			'PersonRegister_id' => $data['PersonRegister_id'],
		);
		$query = "
		   with cte as
            (
                select
                    ES.Diag_id,
                    BSS.PregnancyResult_id
                from
                    v_BirthSpecStac BSS
                    left join v_EvnSection ES on ES.EvnSection_id = BSS.EvnSection_id
                where
                    BSS.PersonRegister_id = :PersonRegister_id
                limit 1
            ),
           cte2 as
            (
                select
                    PS.Diag_id
				from
				    v_PregnancyScreen PS
				where
				    PS.PersonRegister_id = :PersonRegister_id
				order by
				    PS.PregnancyScreen_setDT desc
				limit 1
            )

            update
                PersonRegister
		    set
                Diag_id = coalesce((select Diag_id from cte), (select Diag_id from cte2)),
                PregnancyResult_id = (select PregnancyResult_id from cte)
            where
                PersonRegister_id = :PersonRegister_id
			returning null as \"Error_Code\", null as \"Error_Msg\"
		";
		//echo getDebugSQL($query, $params);exit;
		$resp = $this->queryResult($query, $params);
		if (!$this->isSuccessful($resp)) {
			return $this->createError('Ошибка при обновлении записи регистра беременности');
		}
		return array(array('success' => true));
	}

	/**
	 * Сохранение скрининга беременности
	 */
	function savePregnancyScreen($data, $isAllowTransaction = true) {
		$response = array('success' => true, 'PregnancyScreen_id' => null);

		$this->isAllowTransaction = $isAllowTransaction;
		$this->beginTransaction();

		if (empty($data['PregnancyScreen_id'])) {
			$procedure = 'p_PregnancyScreen_ins';
		} else {
			$procedure = 'p_PregnancyScreen_upd';
		}
		$query = "
		    select 
		        PregnancyScreen_id as \"PregnancyScreen_id\",
		        Error_Code as \"Error_Code\",
		        Error_Message as \"Error_Msg\"
		    from {$procedure}
		    (
                PregnancyScreen_id := :PregnancyScreen_id,
				PersonRegister_id := :PersonRegister_id,
				PregnancyScreen_setDT := :PregnancyScreen_setDT,
				PregnancyScreen_RiskPerPat := :PregnancyScreen_RiskPerPat,
				Evn_id := :Evn_id,
				Lpu_id := :Lpu_id,
				MedPersonal_id := :MedPersonal_id,
				PregnancyScreen_Comment := :PregnancyScreen_Comment,
				Diag_id := :Diag_id,
				pmUser_id := :pmUser_id,
                GestationalAge_id := :GestationalAge_id
		    )
		";
		$params = array(
			'PregnancyScreen_id' => !empty($data['PregnancyScreen_id'])?$data['PregnancyScreen_id']:null,
			'PersonRegister_id' => $data['PersonRegister_id'],
			'PregnancyScreen_setDT' => $data['PregnancyScreen_setDate'],
			'PregnancyScreen_RiskPerPat' => !empty($data['PregnancyScreen_RiskPerPat'])?$data['PregnancyScreen_RiskPerPat']:null,
			'Evn_id' => !empty($data['Evn_id'])?$data['Evn_id']:null,
			'Lpu_id' => $data['Lpu_oid'],
			'MedPersonal_id' => $data['MedPersonal_oid'],
			'PregnancyScreen_Comment' => !empty($data['PregnancyScreen_Comment'])?$data['PregnancyScreen_Comment']:null,
			'Diag_id' => $data['Diag_id'],
			'pmUser_id' => $data['pmUser_id'],
			'GestationalAge_id' => $data['GestationalAge_id'],
		);
		$resp = $this->queryResult($query, $params);
		if (!$this->isSuccessful($resp)) {
			$this->rollbackTransaction();
			return $resp;
		}
		$data['PregnancyScreen_id'] = $response['PregnancyScreen_id'] = $resp[0]['PregnancyScreen_id'];

		//Сопутствующие диагнозы
		$PregnancyScreenSopDiagData = array();
		if (is_array($data['PregnancyScreenSopDiagData'])) {
			$PregnancyScreenSopDiagData = $data['PregnancyScreenSopDiagData'];
		} else if (is_string($data['PregnancyScreenSopDiagData'])) {
			$PregnancyScreenSopDiagData = json_decode($data['PregnancyScreenSopDiagData'], true);
		}

		foreach($PregnancyScreenSopDiagData as $PregnancyScreenSopDiag) {
			$PregnancyScreenSopDiag['PregnancyScreen_id'] = $data['PregnancyScreen_id'];
			$PregnancyScreenSopDiag['pmUser_id'] = $data['pmUser_id'];
			switch($PregnancyScreenSopDiag['RecordStatus_Code']) {
				case 0:
					$PregnancyScreenSopDiag['PregnancyScreenSopDiag_id'] = null;
					$resp = $this->savePregnancyScreenSopDiag($PregnancyScreenSopDiag);
					break;
				case 2:
					$resp = $this->savePregnancyScreenSopDiag($PregnancyScreenSopDiag);
					break;
				case 3:
					$resp = $this->deletePregnancyScreenSopDiag($PregnancyScreenSopDiag);
					break;
			}
			if (!$this->isSuccessful($resp)) {
				$this->rollbackTransaction();
				return $resp;
			}
		}

		//Заполнение анкеты
		$data['DispClass_id'] = 16;
		$resp = $this->savePregnancyQuestionAnswers($data);
		if (!$this->isSuccessful($resp)) {
			$this->rollbackTransaction();
			return $resp;
		}

		//Обновление записи регистра
		$resp = $this->updatePersonRegister($data);
		if (!$this->isSuccessful($resp)) {
			$this->rollbackTransaction();
			return $resp;
		}

		// считаем "Риск по скринингу" и записываем в PregnancyScreen_RiskPerPat
		$this->recalculatePregnancyQuestionScreenRisk($data);

		//рекомендации по акушерским осложнениям
		$query = "
		    select
		        error_code as \"error_code\",
		        error_message as \"error_msg\"
		    from  p_personregisterobstetricpathologytype_upd
		    (
				ppersonregister_id := :personregister_id,
				ppmuser_id := :pmuser_id
			)
		";
		$params = array(
			'personregister_id' => $data['PersonRegister_id'],
			'pmuser_id' => $data['pmUser_id']
		);
		$resp = $this->queryResult($query, $params);
		if (!$this->isSuccessful($resp)) {
			$this->rollbackTransaction();
			return $resp;
		}

		$query = "
		    select
		        obstetricpathologytype_text as \"ObstetricPathologyType_text\"
		    from  getobstetricpathologytype
		    (
				personregister_id := :personregister_id
			)
		";
		$params = array(
			'personregister_id' => $data['PersonRegister_id']
		);
		$resp = $this->queryResult($query, $params);
		if (!$this->isSuccessful($resp)) {
			$this->rollbackTransaction();
			return $resp;
		}
		$response['ObstetricPathologyType_text'] = $resp;

		$this->isAllowTransaction = $isAllowTransaction;
		$this->commitTransaction();

		return array($response);
	}

	/**
	 * Сохранение сопутствующего диагноза в скрининге беременнсти
	 */
	function savePregnancyScreenSopDiag($data) {
		$params = array(
			'PregnancyScreenSopDiag_id' => !empty($data['PregnancyScreenSopDiag_id'])?$data['PregnancyScreenSopDiag_id']:null,
			'PregnancyScreen_id' => $data['PregnancyScreen_id'],
			'Diag_id' => $data['Diag_id'],
			'DiagSetClass_id' => $data['DiagSetClass_id'],
			'pmUser_id' => $data['pmUser_id'],
		);

		if (empty($params['PregnancyScreenSopDiag_id'])) {
			$procedure = 'p_PregnancyScreenSopDiag_ins';
		} else {
			$procedure = 'p_PregnancyScreenSopDiag_upd';
		}

		$query = "
		    select 
		        PregnancyScreenSopDiag_id as \"PregnancyScreenSopDiag_id\",
		        Error_Code as \"Error_Code\",
		        Error_Message as \"Error_Msg\"
		    from {$procedure}
		    (
		    	PregnancyScreenSopDiag_id := :PregnancyScreenSopDiag_id,
				PregnancyScreen_id := :PregnancyScreen_id,
				Diag_id := :Diag_id,
				DiagSetClass_id := :DiagSetClass_id,
				pmUser_id := :pmUser_id
		    )
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при сохранении сопутствующего диагноза');
		}
		return $resp;
	}

	/**
	 * Удаление сопутствующего диагноза в скрининге беременнсти
	 */
	function deletePregnancyScreenSopDiag($data) {
		$params = array('PregnancyScreenSopDiag_id' => $data['PregnancyScreenSopDiag_id']);
		$query = "
		    select 
		        Error_Code as \"Error_Code\",
		        Error_Message as \"Error_Msg\"
		    from p_PregnancyScreenSopDiag_del
		    (
		    	PregnancyScreenSopDiag_id := :PregnancyScreenSopDiag_id
		    )
		";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('','Ошибка при удалении сопутствующего диагноза');
		}
		return $response;
	}

	/**
	 * Удаление скрининга беременности
	 */
	function deletePregnancyScreen($data, $isAllowTransaction = true) {
		$params = array('PregnancyScreen_id' => $data['PregnancyScreen_id']);

		$this->isAllowTransaction = $isAllowTransaction;
		$this->beginTransaction();

		$PersonRegister_id = $this->getFirstResultFromQuery("
			select
			    PersonRegister_id
			from 
			    v_PregnancyScreen
			where 
			    PregnancyScreen_id = :PregnancyScreen_id
			limit 1
		", $params);
		if (!$PersonRegister_id) {
			$this->rollbackTransaction();
			return $this->createError('','Ошибка при получении идентификатора записи из регистра беременных');
		}
		$params['PersonRegister_id'] = $PersonRegister_id;

		//Удаление сопутсвующих диагнозов
		$SopDiagList = $this->queryResult("
			select 
			    PregnancyScreenSopDiag_id as \"PregnancyScreenSopDiag_id\"
			from 
			    v_PregnancyScreenSopDiag
			where 
			    PregnancyScreen_id = :PregnancyScreen_id
		", $params);
		if (!is_array($SopDiagList)) {
			$this->rollbackTransaction();
			return $this->createError('','Ошибка при получении списка сопутствующих диагнозов скрининга');
		}
		foreach($SopDiagList as $SopDiag) {
			$resp = $this->deletePregnancyScreenSopDiag($SopDiag);
			if (!$this->isSuccessful($resp)) {
				$this->rollbackTransaction();
				return $resp;
			}
		}

		//Удаление ответов на вопросы анкеты скрининга
		$resp = $this->deletePregnancyQuestionAnswers($params);
		if (!$this->isSuccessful($resp)) {
			$this->rollbackTransaction();
			return $resp;
		}

		//Удаление скрининга
		$query = "
			select
			    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from p_PregnancyScreen_del
			(
				PregnancyScreen_id := :PregnancyScreen_id			
			)
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			$this->rollbackTransaction();
			return $this->createError('Ошибка при удалении данных скрининга');
		}
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}

		//Обновление данных записи из регистра беременных
		$resp = $this->updatePersonRegister($params);
		if (!$this->isSuccessful($resp)) {
			$this->rollbackTransaction();
			return $resp;
		}
		//Обновление степени риска
		$this->recalculateRiskType($params);

		$this->isAllowTransaction = $isAllowTransaction;
		$this->commitTransaction();

		return array(array('success' => true));
	}

	/**
	 * Получение данных для редактирования скрининга беременности
	 */
	function loadPregnancyScreenForm($data) {
		$params = array('PregnancyScreen_id' => $data['PregnancyScreen_id']);
		$query = "
			select
				PS.PregnancyScreen_id as \"PregnancyScreen_id\",
				PS.PersonRegister_id as \"PersonRegister_id\",
				PS.PregnancyScreen_RiskPerPat as \"PregnancyScreen_RiskPerPat\",
				PS.PregnancyScreen_Comment as \"PregnancyScreen_Comment\",
				to_char(PS.PregnancyScreen_setDT, 'YYYY-MM-DD') as \"PregnancyScreen_setDate\",
				PS.Evn_id as \"Evn_id\",
				PS.Lpu_id as \"Lpu_oid\",
				PS.MedPersonal_id as \"MedPersonal_oid\",
				PS.Diag_id as \"Diag_id\",
				PS.GestationalAge_id as \"GestationalAge_id\",
				PP.PersonPregnancy_Period as \"PersonPregnancy_Period\",
				to_char(PP.PersonPregnancy_setDT, 'YYYY-MM-DD') as \"PersonPregnancy_setDT\"
			from
				v_PregnancyScreen PS
				left join v_PersonPregnancy PP on PP.PersonRegister_id=PS.PersonRegister_id
			where
				PS.PregnancyScreen_id = :PregnancyScreen_id
			limit 1
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение скрининга беременности
	 */
	function loadPregnancyScreen($data) {
		$response = $this->loadPregnancyScreenForm($data);
		if (!is_array($response) || count($response) == 0) {
			return $this->createError('','Ошибка при получении скрининга беременности');
		}

		$PregnancyQuestionData = $this->loadPregnancyQuestionData(array(
			'PregnancyScreen_id' => $data['PregnancyScreen_id'],
			'DispClass_id' => 16
		));
		$response[0] = array_merge($response[0], $PregnancyQuestionData);

		return $response;
	}

	/**
	 * Получение списка сопутствующих диагнозов в скрининге беременности
	 */
	function loadPregnancyScreenSopDiagGrid($data) {
		$params = array('PregnancyScreen_id' => $data['PregnancyScreen_id']);
		$query = "
			select
				PSSD.PregnancyScreenSopDiag_id as \"PregnancyScreenSopDiag_id\",
				PSSD.PregnancyScreen_id as \"PregnancyScreen_id\",
				D.Diag_id as \"Diag_id\",
				D.Diag_FullName as \"Diag_FullName\",
				DSC.DiagSetClass_id as \"DiagSetClass_id\",
				DSC.DiagSetClass_Name as \"DiagSetClass_Name\",
				1 as \"RecordStatus_Code\"
			from
				v_PregnancyScreenSopDiag PSSD
				left join v_Diag D on D.Diag_id = PSSD.Diag_id
				left join v_DiagSetClass DSC on DSC.DiagSetClass_id = PSSD.DiagSetClass_id
			where
				PSSD.PregnancyScreen_id = :PregnancyScreen_id
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка случаев лечения в течении периода мониторинга беременности
	 */
	function loadPersonPregnancyEvnGrid($data) {
		$params = array('PersonRegister_id' => $data['PersonRegister_id']);
		$query = "
			-- variables
			with cte as
			(
			    select
                    PR.PersonRegister_setDate as begDate,
                    coalesce(PR.PersonRegister_disDate, dateadd('year', 50, dbo.tzGetDate())) as endDate,
				    PR.Person_id as Person_id
                from 
                    v_PersonRegister PR
                where 
                    PR.PersonRegister_id = :PersonRegister_id
                limit 1
			)
			-- end variables

			select
				-- select
				E.Evn_id as \"Evn_id\",
				E.EvnClass_SysNick as \"EvnClass_SysNick\",
				case
					when E.EvnClass_SysNick = 'EvnPL' then 'Амбулаторный'
					when E.EvnClass_SysNick = 'EvnPS' then 'Стационарный'
				end as \"EvnType\",
				to_char(E.Evn_setDate, '{$this->dateTimeForm104}') as \"Evn_setDate\",
				to_char(E.Evn_disDate, '{$this->dateTimeForm104}') as \"Evn_disDate\",
				coalesce(EPL.EvnPL_NumCard, EPS.EvnPS_NumCard) as \"Evn_NumCard\",
				L.Lpu_id as \"Lpu_id\",
				L.Lpu_Nick as \"Lpu_Nick\",
				D.Diag_id as \"Diag_id\",
				D.Diag_FullName as \"Diag_FullName\",
				LT.LeaveType_id as \"LeaveType_id\",
				RC.ResultClass_id as \"ResultClass_id\",
				coalesce(LT.LeaveType_Name, RC.ResultClass_Name) as \"EvnResult\",
				null as \"CreatedObjects\"
				-- end select
			from
				-- from
				v_Evn E 
				left join v_Lpu L on L.Lpu_id = E.Lpu_id
				left join v_EvnPL EPL on EPL.EvnPL_id = E.Evn_id
				left join v_EvnPS EPS on EPS.EvnPS_id = E.Evn_id
				left join v_LeaveType LT on LT.LeaveType_id = EPS.LeaveType_id
				left join v_ResultClass RC on RC.ResultClass_id = EPL.ResultClass_id
				left join v_Diag D on D.Diag_id = coalesce(EPL.Diag_id, EPS.Diag_id)
				-- end from
			where
				-- where
				E.EvnClass_SysNick in ('EvnPL','EvnPS')
				and E.Person_id = (select Person_id from cte)
				and E.Evn_setDate between (select begDate from cte) and (select endDate from cte)
				-- end where
			order by
				-- order by
				E.Evn_setDate
				-- end order by
		";

		$evn_list = $this->queryResult(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		$evn_count = $this->queryResult(getCountSQLPH($query), $params);
		if (!is_array($evn_list) || !is_array($evn_count)) {
			return false;
		}

		$evn_ids = array();
		foreach($evn_list as $evn) {
			$evn_ids[] = $evn['Evn_id'];
		}
		if (count($evn_ids) > 0) {
			$evn_ids_str = implode(",", $evn_ids);

			$query = "
				select *
				from (
					(
						select
							E.Evn_rid as \"Evn_id\",
							to_char(PP.PersonPregnancy_setDT, 'dd.mm.yyyy') as \"setDT\",
							'Anketa' as \"Category\"
						from 
							v_PersonPregnancy PP
							left join v_Evn E on E.Evn_id = PP.Evn_id
						where 
							E.Evn_rid in ({$evn_ids_str})
					)
					union all
					(
						select
							E.Evn_rid as \"Evn_id\",
							to_char(PS.PregnancyScreen_setDT, 'dd.mm.yyyy') as \"setDT\",
							'Screen' as \"Category\"
						from 
							v_PregnancyScreen PS
							left join v_Evn E on E.Evn_id = PS.Evn_id
						where 
							E.Evn_rid in ({$evn_ids_str})
					)
					union all
					(
						select
							E.Evn_rid as \"Evn_id\",
							to_char(BC.BirthCertificate_setDT, 'dd.mm.yyyy') as \"setDT\",
							'Certificate' as \"Category\"
						from 
							v_BirthCertificate BC
						left join v_Evn E on E.Evn_id = BC.Evn_id
						where
							E.Evn_rid in ({$evn_ids_str})
					)
					union all
					(
						select
							E.Evn_rid as \"Evn_id\",
							to_char(BSS.BirthSpecStac_OutcomDT, 'dd.mm.yyyy') as \"setDT\",
							'Result' as \"Category\"
						from 
							v_BirthSpecStac BSS
							left join v_Evn E on E.Evn_id = coalesce(BSS.EvnSection_id, BSS.Evn_id)
						where 
							E.Evn_rid in ({$evn_ids_str})
						order by 
							\"setDT\"
					)
				) t
			";

			//echo getDebugSQL($query, array());exit;
			$resp = $this->queryResult($query);
			if (!is_array($resp)) {
				return false;
			}

			$CreatedObjects = array();
			foreach($resp as $item) {
				$str = '';

				switch($item['Category']){
					case 'Anketa':
						$str = 'Создана анкета';
						break;
					case 'Screen':
						$str = 'Создан скрининг от '. DateTime::createFromFormat('d.m.Y', $item['setDT'])->format('d.m.Y');
						break;
					case 'Certificate':
						$str = 'Создан раздел "Родовой сертификат"';
						break;
					case 'Result':
						$str = 'Создан раздел "Исход"';
						break;
					default:
						$str = 'Неизвестный раздел';
				}

				$CreatedObjects[$item['Evn_id']][] = $str;
			}

			foreach($evn_list as &$evn) {
				$key = $evn['Evn_id'];

				if (isset($CreatedObjects[$key])) {
					$evn['CreatedObjects'] = implode("<br/>", $CreatedObjects[$key]);
				}
			}
		}

		return array('data' => $evn_list, 'totalCount' => $evn_count[0]['cnt']);
	}

	/**
	 * Получение списка услуг, выполненных в течении периода мониторинга беременности
	 */
	function loadPersonPregnancyEvnUslugaGrid($data) {
		$params = array('PersonRegister_id' => $data['PersonRegister_id']);
		$filter = "";

		if (!empty($data['AttributeList']) && is_array($data['AttributeList']) && count($data['AttributeList']) > 0) {
			$attribute_list_str = "'".implode("','", $data['AttributeList'])."'";
			$filter .= "
				and exists(
					select 
					    * 
					from 
					    v_UslugaComplexAttribute UCA
					    inner join v_UslugaComplexAttributeType UCAT on UCAT.UslugaComplexAttributeType_id = UCA.UslugaComplexAttributeType_id
					where 
					    UCA.UslugaComplex_id = UC.UslugaComplex_id
					and 
					    UCAT.UslugaComplexAttributeType_SysNick in ({$attribute_list_str})
				)
			";
		}
		
		$query = "
			-- variables
			with cte as
			(
			    select
                    PR.PersonRegister_setDate as begDate ,
                    coalesce(PR.PersonRegister_disDate, dateadd('year', 50, dbo.tzGetDate())) as endDate,
                    PR.Person_id as Person_id 
                from 
                    v_PersonRegister PR
                where 
                    PR.PersonRegister_id = :PersonRegister_id
                limit 1
			)
			-- end variables
			
			select
				-- select
				EU.EvnUsluga_id as \"EvnUsluga_id\",
				to_char(EU.EvnUsluga_setDate, '{$this->dateTimeForm104}') as \"EvnUsluga_setDate\",
				UC.UslugaComplex_id as \"UslugaComplex_id\",
				UC.UslugaComplex_Code || '. ' || UC.UslugaComplex_Name as \"UslugaComplex_FullName\",				
				(case when EU.Org_uid is NULL then org1.org_id else EU.Org_uid end) as \"Lpu_id\",
				(case when EU.Org_uid is NULL then Org1.Org_Nick else Org.Org_Nick end) as \"Lpu_Nick\",
				MP.MedPersonal_id as \"MedPersonal_id\",
				MP.MedPersonal_FIO as \"MedPersonal_FIO\",
				EX.EvnXml_id as \"EvnXml_id\",
				(case when EU.EvnUsluga_rid is NULL then EU.pmUser_insID else NULL end) as \"pmUser\"
				-- end select
			from
				-- from
				v_EvnUsluga EU
				inner join v_UslugaComplex UC on UC.UslugaComplex_id = EU.UslugaComplex_id
				left join v_org org on org.org_id = EU.Org_uid
				left join v_Evn ParentEvn on ParentEvn.Evn_id = EU.EvnUsluga_pid
				left join v_Lpu L on L.Lpu_id = EU.Lpu_id
				inner join v_org org1 on org1.Org_id = L.Org_id 
				left join lateral (
					select
						MP.MedPersonal_id,
						MP.Person_FIO as MedPersonal_FIO
					from 
					    v_MedPersonal MP
					where 
					    MP.MedPersonal_id = EU.MedPersonal_id
					limit 1
				) MP on true
				left join v_EvnXml EX on EX.Evn_id = EU.EvnUsluga_id
				-- end from
			where
				-- where
				EU.Person_id = (select Person_id from cte)
				and EU.EvnUsluga_setDate between (select begDate from cte) and (select endDate from cte)
				and coalesce(ParentEvn.EvnClass_SysNick, '') not ilike 'EvnUsluga%'
				{$filter}
				-- end where
			order by
				-- order by
				EU.EvnUsluga_setDate
				-- end order by
		";		

		//echo getDebugSQL($query, $params);
		
		$result = $this->queryResult(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		$result_count = $this->queryResult(getCountSQLPH($query), $params);
		if (!is_array($result) || !is_array($result_count)) {
			return false;
		}
		return array('data' => $result, 'totalCount' => $result_count[0]['cnt']);
	}

	/**
	 * Получение данных исхода беременности для редактирования
	 */
	function loadBirthSpecStacForm($data) {
		$params = array(
			'BirthSpecStac_id' => $data['BirthSpecStac_id']
		);

		$pregnancysrok = ", '' as \"PregnancySrokInDay\"";
		if (getRegionNick() == 'khak'){
			$pregnancysrok = "
				,(
					select
						(case when LastScreen.PregnancyScreen_setDT is null
							then '' when PP.PersonPregnancy_setDT is null
								then '' when PP.PersonPregnancy_begMensDate is null
									then ''
									else
										cast(
											extract(day from age(PP.PersonPregnancy_setDT, PP.PersonPregnancy_begMensDate)) +
											extract(day from age(LastScreen.PregnancyScreen_setDT, PP.PersonPregnancy_setDT))
										as text)
						end) as \"PregnancySrokInDay\"
					from v_PersonPregnancy PP
						left join lateral(
							select Screen.*
							from v_PregnancyScreen Screen
							where Screen.PersonRegister_id = PP.PersonRegister_id
							order by Screen.PregnancyScreen_setDT desc
							limit 1
						) LastScreen on true
					where PP.PersonRegister_id = BSS.PersonRegister_id
				)	as \"PregnancySrokInDay\"
			";
		}

		$query = "
			select
				BSS.BirthSpecStac_id as \"BirthSpecStac_id\",
				BSS.PersonRegister_id as \"PersonRegister_id\",
				BSS.PregnancySpec_id as \"PregnancySpec_id\",
				BSS.Evn_id as \"Evn_id\",
				BSS.EvnSection_id as \"EvnSection_id\",
				BSS.PregnancyResult_id as \"PregnancyResult_id\",
				BSS.Lpu_id as \"Lpu_oid\",
				BSS.BirthSpecStac_OutcomPeriod as \"BirthSpecStac_OutcomPeriod\",
				BSS.BirthSpecStac_CountPregnancy as \"BirthSpecStac_CountPregnancy\",
				BSS.BirthSpecStac_CountBirth as \"BirthSpecStac_CountBirth\",
				BSS.BirthSpecStac_CountChild as \"BirthSpecStac_CountChild\",
				BSS.BirthSpecStac_CountChildAlive as \"BirthSpecStac_CountChildAlive\",
				BSS.BirthSpecStac_BloodLoss as \"BirthSpecStac_BloodLoss\",
				BSS.BirthSpecStac_SurgeryVolume as \"BirthSpecStac_SurgeryVolume\",
				BSS.BirthSpecStac_InjectVMS as \"BirthSpecStac_InjectVMS\",
				BSS.BirthSpecStac_Info as \"BirthSpecStac_Info\",
				BSS.AbortLpuPlaceType_id as \"AbortLpuPlaceType_id\",
				BSS.AbortLawType_id as \"AbortLawType_id\",
				BSS.AbortMethod_id as \"AbortMethod_id\",
				BSS.AbortIndicat_id as \"AbortIndicat_id\",
				BSS.BirthPlace_id as \"BirthPlace_id\",
				BSS.BirthSpec_id as \"BirthSpec_id\",
				BSS.BirthCharactType_id as \"BirthCharactType_id\",
				coalesce(BSS.BirthSpecStac_IsHIVtest,1) as \"BirthSpecStac_IsHIVtest\",
				coalesce(BSS.BirthSpecStac_IsHIV,1) as \"BirthSpecStac_IsHIV\",
				coalesce(BSS.BirthSpecStac_IsRWtest,1) as \"BirthSpecStac_IsRWtest\",
				coalesce(BSS.BirthSpecStac_IsRW,1) as \"BirthSpecStac_IsRW\",
				coalesce(BSS.BirthSpecStac_IsHBtest,1) as \"BirthSpecStac_IsHBtest\",
				coalesce(BSS.BirthSpecStac_IsHB,1) as \"BirthSpecStac_IsHB\",
				coalesce(BSS.BirthSpecStac_IsHCtest,1) as \"BirthSpecStac_IsHCtest\",
				coalesce(BSS.BirthSpecStac_IsHC,1) as \"BirthSpecStac_IsHC\",
				coalesce(BSS.BirthSpecStac_IsContrac,1) as \"BirthSpecStac_IsContrac\",
				BSS.BirthSpecStac_ContracDesc as \"BirthSpecStac_ContracDesc\",
				to_char(BSS.BirthSpecStac_OutcomDT, 'dd.mm.yyyy') as \"BirthSpecStac_OutcomDate\",
				to_char(BSS.BirthSpecStac_OutcomDT, 'hh24:mi') as \"BirthSpecStac_OutcomTime\",
				(
					select PregnancyQuestion_AnswerInt
					from dbo.PregnancyQuestion
					where PersonRegister_id=BSS.PersonRegister_id
						and QuestionType_id=231
					limit 1
				) as \"AnketaParitet\",
				(
					select PQ.PregnancyQuestion_ValuesStr
					from dbo.v_PregnancyQuestion PQ
						inner join dbo.v_PregnancyScreen PS on PS.PregnancyScreen_id=PQ.PregnancyScreen_id
					where PQ.PersonRegister_id=BSS.PersonRegister_id and PQ.QuestionType_id=382 order by PS.PregnancyScreen_setDT desc
					limit 1
				) as \"ScrinnPredleg\",
                (
					select PregnancyQuestion_AnswerInt
					from dbo.PregnancyQuestion
					where PersonRegister_id=BSS.PersonRegister_id
						and QuestionType_id=770
					limit 1
				) as \"AnketaCaesarian\",
				(
					select PQ.PregnancyQuestion_ValuesStr
					from dbo.v_PregnancyQuestion PQ
						inner join dbo.v_PregnancyScreen PS on PS.PregnancyScreen_id=PQ.PregnancyScreen_id
					where PQ.PersonRegister_id=BSS.PersonRegister_id
						and PQ.QuestionType_id=381
					order by PS.PregnancyScreen_setDT desc
					limit 1
				) as \"ScrinnPolog\",
				BSS.PregnancyType_id as \"PregnancyType_id\",
				OperationCaesarian.IsOperationCaesarian as \"IsOperationCaesarian\",
				BSS.LaborActivity_id as \"LaborActivity_id\",
				BSS.FetalHeartbeat_id as \"FetalHeartbeat_id\",
				BSS.FetalHead_id as \"FetalHead_id\",
				coalesce(ES.MedPersonal_id,EVPL.MedPersonal_id) as \"MedPersonal_oid\"
				{$pregnancysrok}
			from
				v_BirthSpecStac BSS
				left join v_EvnSection ES on ES.EvnSection_id = BSS.EvnSection_id
				left join v_EvnVizitPL EVPL on EVPL.EvnVizitPL_id = BSS.Evn_id
				left join lateral (
					select 1 as IsOperationCaesarian
					from v_EvnUsluga EU 
						inner join v_PersonRegister PR on PR.PersonRegister_id = BSS.PersonRegister_id
						inner join v_EvnSection ES on ES.Person_id = PR.Person_id
						left join v_Evn EvnParent on EvnParent.Evn_id = EU.EvnUsluga_pid
						left join v_UslugaComplex UC on UC.UslugaComplex_id = EU.UslugaComplex_id
					where
						coalesce(EU.EvnUsluga_IsVizitCode, 1) = 1
						and UC.UslugaComplex_Code = 'A16.20.005'
						and EU.EvnUsluga_setDate between dateadd('day', -30, BSS.BirthSpecStac_OutcomDT) and BSS.BirthSpecStac_OutcomDT
						and EvnParent.EvnClass_SysNick != 'EvnUslugaPar'
						and EU.Person_id = PR.Person_id
				) OperationCaesarian on true
			where
				BSS.BirthSpecStac_id = :BirthSpecStac_id
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Получение данных исхода беременности по умолчанию
	 */
	function loadBirthSpecStacDefaults($data) {
		$params = array(
			'PersonRegister_id' => $data['PersonRegister_id'],
			'Evn_id' => !empty($data['Evn_id'])?$data['Evn_id']:null
		);


		$tableName = "answers".time();
        $queryAnswers = '';
		$resetTable = "
			DROP TABLE if exists {$tableName};
		    create temp table {$tableName} (QuestionType_SysNick varchar(100), DispClass_id bigint, Answer varchar(1024));
		";
		$this->queryResult($resetTable);
		$AnketaAnswers = !empty($data['AnketaAnswers'])?json_decode($data['AnketaAnswers'], true):null;
		$LastScreenAnswers = !empty($data['LastScreenAnswers'])?json_decode($data['LastScreenAnswers'], true):null;

		if ($params['PersonRegister_id'] > 0 && !empty($data['PregnancyScreenDates'])) {
			$PregnancyScreenDates = json_decode($data['PregnancyScreenDates'], true);
			$screens = $this->queryResult("
				select
					PS.PregnancyScreen_id as \"PregnancyScreen_id\",
					PS.PregnancyScreen_setDT::timestamp as \"PregnancyScreen_setDate\",
					0 as \"changed\"
				from v_PregnancyScreen PS
				where PS.PersonRegister_id = :PersonRegister_id
				order by PS.PregnancyScreen_setDT
			", $params);
			$arr = array();
			foreach($screens as $item) {
				$arr[$item['PregnancyScreen_id']] = $item;
			}
			foreach($PregnancyScreenDates as $id => $date) {
				$arr[$id] = array(
					'PregnancyScreen_id' => $id,
					'PregnancyScreen_setDate' => date_create(ConvertDateFormat($date)),
					'changed' => 1
				);
			}
			usort($arr, function($a, $b){
				return ($a['PregnancyScreen_setDate'] < $b['PregnancyScreen_setDate'])?-1:1;
			});

			$lastScreen = end($arr);
			if (!$lastScreen['changed']) {
				$LastScreenAnswers = null;	//Данные последнего скрининга нужно брать из БД
			}
		}

		if (!empty($data['AnketaAnswers']) || !empty($data['LastScreenAnswers'])) {
			$AnketaAnswers = is_array($AnketaAnswers)?$AnketaAnswers:array();
			$LastScreenAnswers = is_array($LastScreenAnswers)?$LastScreenAnswers:array();
			$Answers = $AnketaAnswers + $LastScreenAnswers;

			$codeList = array_keys($Answers);
			$codeList_str = implode(",", $codeList);
			$query = "
				select 
				    QuestionType_Code as \"QuestionType_Code\",
				    QuestionType_SysNick as \"QuestionType_SysNick\",
				    DispClass_id as \"DispClass_id\"
				from 
				    v_QuestionType
				where QuestionType_Code in ({$codeList_str}) 
				and QuestionType_SysNick is not null
			";
			$QuestionTypeList = $this->queryResult($query);

			$values = array();
			foreach($QuestionTypeList as $QuestionType) {
				$code = $QuestionType['QuestionType_Code'];
				$sysNick = $QuestionType['QuestionType_SysNick'];
				$class = $QuestionType['DispClass_id'];
				if (is_bool($Answers[$code])) {
					$answer = $Answers[$code]?2:1;
				} else if (!empty($Answers[$code])) {
					$answer = "'".$Answers[$code]."'";
				}
				if (!empty($answer)) {
					$values[] = "('{$sysNick}', {$class}, {$answer})";
				}
			}
			if (count($values) > 0) {
				$values_str = implode(",", $values);
				$queryAnswers .= "
				insert into {$tableName} (QuestionType_SysNick, DispClass_id, Answer)
				values {$values_str};
				";
			}
		}
		if (empty($AnketaAnswers) && $data['PersonRegister_id'] > 0) {
			$queryAnswers = "
				insert into {$tableName} (QuestionType_SysNick, DispClass_id, Answer)
				select
					QT.QuestionType_SysNick,
					QT.DispClass_id,
					coalesce(
						PQ.PregnancyQuestion_IsTrue::varchar,
						PQ.PregnancyQuestion_AnswerInt::varchar,
						PQ.PregnancyQuestion_AnswerText,
						PQ.PregnancyQuestion_ValuesStr::varchar
					) as Answer
				from
					v_PregnancyQuestion PQ
					inner join v_QuestionType QT on QT.QuestionType_id = PQ.QuestionType_id
				where
					QT.DispClass_id = 14 and PQ.PersonRegister_id = :PersonRegister_id::bigint;
			";
		}
		if (empty($LastScreenAnswers) && $data['PersonRegister_id'] > 0) {
			$queryAnswers .= "
				insert into {$tableName} (QuestionType_SysNick, DispClass_id, Answer)
				select
					QT.QuestionType_SysNick,
					QT.DispClass_id,
					coalesce(
						PQ.PregnancyQuestion_IsTrue::varchar,
						PQ.PregnancyQuestion_AnswerInt::varchar,
						PQ.PregnancyQuestion_AnswerText,
						PQ.PregnancyQuestion_ValuesStr::varchar
					) as Answer
				from
					v_PregnancyQuestion PQ
					inner join v_QuestionType QT on QT.QuestionType_id = PQ.QuestionType_id
				where
					QT.DispClass_id = 16
					and PQ.PregnancyScreen_id = (
						select PS.PregnancyScreen_id
						from 
						    v_PregnancyScreen PS
						where 
						    PS.PersonRegister_id = :PersonRegister_id::bigint
						order by PS.PregnancyScreen_setDT desc
						limit 1
					);
			";
		}
		//echo getDebugSQL($queryAnswers, $params);

		$query = "			
			{$queryAnswers}

			with cte as (
			    select dbo.tzGetDate() as currDT
			),
            DiagList as (
				select 
				    D.Diag_Code
				from 
				    v_EvnDiag ED
				    inner join v_Diag D on D.Diag_id = ED.Diag_id
				where 
				    ED.EvnDiag_pid = :Evn_id
			)
			select 
				:PersonRegister_id as \"PersonRegister_id\",
				coalesce (ES.EvnSection_pid, E.Evn_id) as \"Evn_id\",
				ES.EvnSection_id as \"EvnSection_id\",
				E.Lpu_id as \"Lpu_oid\",
				coalesce(ES.MedPersonal_id, EVPL.MedPersonal_id) as \"MedPersonal_oid\",
				coalesce((
					select Answer from {$tableName} where QuestionType_SysNick = 'Pregnancy_Count' limit 1
				)::int, 1) as \"BirthSpecStac_CountPregnancy\",
				coalesce((
					select Answer from {$tableName} where QuestionType_SysNick = 'Pregnancy_CountBirth' limit 1
				)::int, 0) + 1 as \"BirthSpecStac_CountBirth\",
				coalesce((
					select Answer from {$tableName} where QuestionType_SysNick = 'PregChildCount' limit 1
				)::int, 0) as \"BirthSpecStac_CountChild\",
				(
					select BirthPlace_id from v_BirthPlace where BirthPlace_Code = 1 limit 1
				) as \"BirthPlace_id\",
				case when (left(D.Diag_Code,3) between 'O81' and 'O83') or (D.Diag_Code between 'O84.1' and 'O84.8')
					then 2 else 1
				end as \"BirthCharactType_id\",
				case when ES.EvnSection_id is not null
					then 2 else 1
				end as \"AbortLpuPlaceType_id\",
				coalesce((
					select Answer from {$tableName} where QuestionType_SysNick = 'IsHIVtest' limit 1
				)::bigint, 1) as \"BirthSpecStac_IsHIVtest\",
				coalesce((
					select Answer from {$tableName} where QuestionType_SysNick = 'IsHIV' limit 1
				)::bigint, 1) as \"BirthSpecStac_IsHIV\",
				coalesce((
					select Answer from {$tableName} where QuestionType_SysNick = 'IsRWtest' limit 1
				)::bigint, 1) as \"BirthSpecStac_IsRWtest\",
				coalesce((
					select Answer from {$tableName} where QuestionType_SysNick = 'IsRW' limit 1
				)::bigint, 1) as \"BirthSpecStac_IsRW\",
				coalesce((
					select Answer from {$tableName} where QuestionType_SysNick = 'IsHBtest' limit 1
				)::bigint, 1) as \"BirthSpecStac_IsHBtest\",
				coalesce((
					select Answer from {$tableName} where QuestionType_SysNick = 'IsHB' limit 1
				)::bigint, 1) as \"BirthSpecStac_IsHB\",
				coalesce((
					select Answer from {$tableName} where QuestionType_SysNick = 'IsHCtest' limit 1
				)::bigint, 1) as \"BirthSpecStac_IsHCtest\",
				coalesce((
					select Answer from {$tableName} where QuestionType_SysNick = 'IsHC' limit 1
				)::bigint, 1) as \"BirthSpecStac_IsHC\",
				(case when exists(select * from DiagList where left(Diag_Code,3) = 'O15')
					then 2 else 1
				end) as \"QuestionType_521\",
				(case when exists(select * from DiagList where left(Diag_Code,3) = 'O42')
					then 2 else 1
				end) as \"QuestionType_522\",
				(case when exists(select * from DiagList where Diag_Code between 'O62.0' and 'O62.2')
					then 2 else 1
				end) as \"QuestionType_523\",
				(case when exists(select * from DiagList where Diag_Code = 'O62.3')
					then 2 else 1
				end) as \"QuestionType_524\",
				(case when exists(select * from DiagList where left(Diag_Code,3) = 'O45')
					then 2 else 1
				end) as \"QuestionType_532\",
				(case when exists(select * from DiagList where Diag_Code = 'O69.0')
					then 2 else 1
				end) as \"QuestionType_540\",
				(case when exists(select * from DiagList where Diag_Code = 'O69.1')
					then 2 else 1
				end) as \"QuestionType_541\",
                OperationCaesarian.IsOperationCaesarian as \"IsOperationCaesarian\",
            	(
            		select PregnancyQuestion_AnswerInt
            		from dbo.PregnancyQuestion
            		where PersonRegister_id=:PersonRegister_id
            			and QuestionType_id=231
					limit 1
            	) as \"AnketaParitet\",
				(
					select PQ.PregnancyQuestion_ValuesStr
					from dbo.v_PregnancyQuestion PQ
						inner join dbo.v_PregnancyScreen PS on PS.PregnancyScreen_id=PQ.PregnancyScreen_id
					where PQ.PersonRegister_id=:PersonRegister_id
						and PQ.QuestionType_id=382
					order by PS.PregnancyScreen_setDT desc
					limit 1
				) as \"ScrinnPredleg\",
				(
					select PregnancyQuestion_AnswerInt
					from dbo.PregnancyQuestion
					where PersonRegister_id=:PersonRegister_id
						and QuestionType_id=770
					limit 1
				) as \"AnketaCaesarian\",
				(
					select PQ.PregnancyQuestion_ValuesStr
					from dbo.v_PregnancyQuestion PQ
						inner join dbo.v_PregnancyScreen PS on PS.PregnancyScreen_id=PQ.PregnancyScreen_id
					where PQ.PersonRegister_id=:PersonRegister_id
						and PQ.QuestionType_id=381
					order by PS.PregnancyScreen_setDT desc
					limit 1
				) as \"ScrinnPolog\"
			from
				(select 1 as f) as t
				left join v_Evn E on E.Evn_id = :Evn_id
				left join v_EvnSection ES on ES.EvnSection_id = E.Evn_id
				left join v_EvnVizitPL EVPL on EVPL.EvnVizitPL_id = E.Evn_id
				left join v_Diag D on D.Diag_id = ES.Diag_id
				left join lateral(
					select 1 as IsOperationCaesarian
					from v_EvnSection ES0
						left join v_EvnUsluga EU on EU.EvnUsluga_pid=ES0.EvnSection_id
						left join v_UslugaComplex UC on UC.UslugaComplex_id = EU.UslugaComplex_id
					where
						UC.UslugaComplex_Code='A16.20.005'
						and ES0.EvnSection_id=ES.EvnSection_id
				) OperationCaesarian on true
			limit 1	
		";
		//echo getDebugSQL($query, $params);exit;
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение данных исхода беременности
	 */
	function loadBirthSpecStac($data) {
		$params = array(
			'PersonRegister_id' => !empty($data['PersonRegister_id'])?$data['PersonRegister_id']:null,
			'Evn_id' => !empty($data['Evn_id'])?$data['Evn_id']:null,
			'PregnancyScreenDates' => !empty($data['PregnancyScreenDates'])?$data['PregnancyScreenDates']:null,
			'AnketaAnswers' => !empty($data['AnketaAnswers'])?$data['AnketaAnswers']:null,
			'LastScreenAnswers' => !empty($data['LastScreenAnswers'])?$data['LastScreenAnswers']:null,
		);

		$BirthSpecStac_id = !empty($data['BirthSpecStac_id'])?$data['BirthSpecStac_id']:null;

		if (empty($BirthSpecStac_id)) {
			$BirthSpecStac_id = $this->getFirstResultFromQuery("
				select 
				    BSS.BirthSpecStac_id as \"BirthSpecStac_id\"
				from
				    v_BirthSpecStac BSS
				where 
				    (BSS.PersonRegister_id = :PersonRegister_id or BSS.EvnSection_id = :Evn_id)
				limit 1
			", $params, true);
			if ($BirthSpecStac_id === false) {
				return $this->createError('','Ошибка при получении идентификатора исхода родов');
			}
		}

		if (!$BirthSpecStac_id) {
			//Получение данных исхода по умолчанию
			$response = $this->loadBirthSpecStacDefaults($params);
		} else {
			//Получение сохраненных данных исхода
			$response = $this->loadBirthSpecStacForm(array('BirthSpecStac_id' => $BirthSpecStac_id));
			if (!is_array($response) || count($response) == 0) {
				return $this->createError('','Ошибка при получении данных исхода беременности');
			}
			if (!empty($params['PersonRegister_id'])) {
				$response[0]['PersonRegister_id'] = $params['PersonRegister_id'];
			}

			$PregnancyQuestionData = $this->loadPregnancyQuestionData(array(
				'PersonRegister_id' => $response[0]['PersonRegister_id'],
				'DispClass_id' => 18,	//Интранатальные факторы риска
			));
			$response[0] = array_merge($response[0], $PregnancyQuestionData);
		}

		return $response;
	}

	/**
	 * Рассчет типа аборта
	 */
	function getAbortTypeId($data) {
		$params = array(
			'PregnancyResult_id' => $data['PregnancyResult_id'],
			'BirthSpecStac_OutcomPeriod' => $data['BirthSpecStac_OutcomPeriod'],
			'AbortLawType_id' => !empty($data['AbortLawType_id'])?$data['AbortLawType_id']:null,
			'AbortIndicat_id' => !empty($data['AbortIndicat_id'])?$data['AbortIndicat_id']:null,
			'AbortMethod_id' => !empty($data['AbortMethod_id'])?$data['AbortMethod_id']:null,
		);

		$conditions = "when 1=1 then ''";
		if (getRegionNick() == 'kz') {
			$conditions = "
				when (select PregnancyResult_Code from cte) = 2 then 'Self'
				when (select PregnancyResult_Code from cte) = 3 and :BirthSpecStac_OutcomPeriod <= 12 then 'Med'
				when (select PregnancyResult_Code from cte) = 3 and (select AbortMethod_Code from cte) = 2 then 'Mini'
				when (select PregnancyResult_Code from cte) = 3 and (select AbortIndicat_Code from cte) = 3 then 'SocP'
				when (select PregnancyResult_Code from cte) = 3 and (select AbortIndicat_Code from cte) in (1,2) then 'MedP'
				when (select PregnancyResult_Code from cte) = 3 and (select AbortLawType_Code from cte) = 2 then 'Crime'
			";
		} else {
			$conditions = "
				when (select PregnancyResult_Code from cte) = 3 and (select AbortIndicat_Code from cte) = 1 then 'medpok'
				when (select PregnancyResult_Code from cte) = 3 and (select AbortIndicat_Code from cte) = 2 then 'anom'
				when (select PregnancyResult_Code from cte) = 3 and (select AbortIndicat_Code from cte) = 3 then 'socpok'
				when (select PregnancyResult_Code from cte) = 3 and (select AbortLawType_Code from cte) = 1 then 'med'
				when (select PregnancyResult_Code from cte) = 3 and (select AbortLawType_Code from cte) = 2 then 'krim'
			";
		}

		$query = "
		    with cte as 
		    (
		        select 
		            (
		                select 
		                    PregnancyResult_Code 
		                from 
		                    v_PregnancyResult 
		                where 
		                    PregnancyResult_id = :PregnancyResult_id
		                limit 1
		            ) as PregnancyResult_Code,
		            (
		                select 
		                    AbortLawType_Code 
		                from 
		                    v_AbortLawType 
		                where 
		                    AbortLawType_id = :AbortLawType_id 
		                limit 1
		            ) as AbortLawType_Code,
		            (
		                select 
		                    AbortIndicat_Code
		                from 
		                    v_AbortIndicat 
		                where 
		                    AbortIndicat_id = :AbortIndicat_id 
		                limit 1
		            ) as AbortIndicat_Code,
		            (
		                select 
		                    AbortMethod_Code 
		                from 
		                    v_AbortMethod 
		                where 
		                    AbortMethod_id = :AbortMethod_id
		                limit 1
		            ) as AbortMethod_Code		        
		    )
		    
			select 
			    AbortType_id as \"AbortType_id\"
			from 
			    v_AbortType
			where 
			    AbortType_SysNick ilike (case {$conditions} end)
			limit 1
		";

		return $this->getFirstResultFromQuery($query, $params, true);
	}

	/**
	 * Получение идентификатора места родов по коду
	 */
	function getBirthPlaceId($BirthPlace_Code) {
		$params = array('BirthPlace_Code' => $BirthPlace_Code);
		$query = "
                select 
                    BirthPlace_id  as \"BirthPlace_id\"
                from 
                    v_BirthPlace 
                where 
                    BirthPlace_Code = :BirthPlace_Code
                limit 1
                ";
		return $this->getFirstResultFromQuery($query, $params);
	}

	/**
	 * Получение количества детей, введенных в исходе беременности
	 */
	function getChildrenCount($data) {
		$params = array('BirthSpecStac_id' => $data['BirthSpecStac_id']);
		$query = "
			select (
				select count(*) from v_PersonNewBorn where BirthSpecStac_id = :BirthSpecStac_id
			) + (
				select count(*) from v_ChildDeath where BirthSpecStac_id = :BirthSpecStac_id
			)
		";
		return $this->getFirstResultFromQuery($query, $params);
	}

	/**
	 * Выполнение проверок перед сохранением исхода беременности
	 */
	function validateBirthSpecStac($data) {
		if ($data['PregnancyResult_Code'] == 1 &&
			!empty($data['BirthSpecStac_CountPregnancy']) && !empty($data['BirthSpecStac_CountBirth']) &&
			$data['BirthSpecStac_CountPregnancy'] < $data['BirthSpecStac_CountBirth']
		) {
			return $this->createError('','Количество родов превышает количество беременностей.');
		}

		if (empty($data['BirthSpecStac_id'])) {
			$query = "
				select
				    count(BSS.BirthSpecStac_id) as \"cnt\"
				from
				    v_BirthSpecStac BSS
				where
				    BSS.PersonRegister_id = :PersonRegister_id
				limit 1
			";
			$cnt = $this->getFirstResultFromQuery($query, $data);
			if ($cnt === false) {
				return $this->createError('','Ошибка при проверке существования исхода беременности в записи регистра');
			}
			if ($cnt > 0) {
				return $this->createError('','У записи регистра уже существует исход беременности');
			}

			if (!empty($data['EvnSection_id'])) {
				$query = "
					select
					    count(BSS.BirthSpecStac_id) as \"cnt\"
					from
					    v_BirthSpecStac BSS
					where
					    BSS.EvnSection_id = :EvnSection_id
				";
				$cnt = $this->getFirstResultFromQuery($query, $data);
				if ($cnt === false) {
					return $this->createError('','Ошибка при проверке существования исхода беременности в движении');
				}
				if ($cnt > 0) {
					return $this->createError('','В движении уже существует исход беременности');
				}
			}

			if (empty($data['ignoreCheckBirthSpecStacDate']) || !$data['ignoreCheckBirthSpecStacDate']) {
				$query = "
					select
						to_char(BSS.BirthSpecStac_OutcomDT, '{$this->dateTimeForm104}') as \"BirthSpecStac_OutcomDate\"
					from
						v_BirthSpecStac BSS
						inner join v_PersonRegister PR on PR.PersonRegister_id = BSS.PersonRegister_id
					where
						PR.Person_id = (select Person_id from v_PersonRegister where PersonRegister_id = :PersonRegister_id limit 1)
						and abs(datediff('day', BSS.BirthSpecStac_OutcomDT, :BirthSpecStac_OutcomDT)) < 14
					order by
						BSS.BirthSpecStac_OutcomDT desc
					limit 1
				";
				$date = $this->getFirstResultFromQuery($query, $data, true);
				if ($date === false) {
					return $this->createError('','Ошибка при проверке ближайшего исхода');
				}
				if (!empty($date)) {
					$this->_setAlertMsg("В системе существуют сведения об исходе беременности данной пациентки от {$date}");
					return $this->createError(201,'YesNo');
				}
			}
		}

		return array(array('success' => true));
	}

	/**
	 * Сохранение данных исхода беременности
	 */
	function getRobsonsValue($data) {

		//Паритет из Анкеты, определение значения
		$anketaParitet = $data['AnketaParitet'];
		//Предлежание из Скрининга, определение значения
		$scrinnPredleg = $data['ScrinnPredleg'];
		//Кесарево сечение из Анкеты, определение значения
		$anketaCaesarian = $data['AnketaCaesarian'];
		//Положение плода, определение значения
		$ScrinnPolog = $data['ScrinnPolog'];

		//Признак До/после родовой деятельности в Скрининге, находим компоненту
		$field771 = $data['QuestionType_771'];
		//Чекбокс Кесарево сечение, находим компоненту
		$field546 = $data['QuestionType_546'];
		//Значение Признак До/после родовой деятельности в Скрининге, по умолчанию устанавливаем 0
		$value771 = 0;
		if ($field546){
			$value771 = $field771;
		}

		//Значение Количество плодов, указанных в Исходе
		$fieldB_CountChild = $data['BirthSpecStac_CountChild'];

		//Значение Срок недель, указанных в Исходе
		$field772_OutcomPeriod = $data['BirthSpecStac_OutcomPeriod'];

		//Признак Быстрые роды, указанный в Исходе
		$field524 = $data['QuestionType_524'];

		//Признак Родовозбуждение, стимуляция родовой деятельности, указанный в Исходе
		$field525 = $data['QuestionType_525'];

		if ($anketaParitet == ""){
			$anketaParitet = 0;
		}

		//Рассчет Робсона
		if ($anketaParitet == 0 && $fieldB_CountChild == 1 && $scrinnPredleg == 1 && $field772_OutcomPeriod >= 37 && $field524){
			//return "1";
			return 1;
		}else
		if ($anketaParitet == 0 && $fieldB_CountChild == 1 && $scrinnPredleg == 1 && $field772_OutcomPeriod >= 37 && $field525){
			//return "2а";
			return 2;
		}else
		if ($anketaParitet == 0 && $fieldB_CountChild == 1 && $scrinnPredleg == 1 && $field772_OutcomPeriod >= 37 && $value771 == 1){
			//return "2б";
			return 3;
		}else
		if ($anketaParitet >= 1 && $anketaCaesarian == 0 && $fieldB_CountChild == 1 && $scrinnPredleg == 1 && $field772_OutcomPeriod >= 37 && $field524){
			//return "3";
			return 4;
		}else
		if ($anketaParitet >= 1 && $anketaCaesarian == 0 && $fieldB_CountChild == 1 && $scrinnPredleg == 1 && $field772_OutcomPeriod >= 37 && $field525){
			//return "4а";
			return 5;
		}else
		if ($anketaParitet >= 1 && $anketaCaesarian == 0 && $fieldB_CountChild == 1 && $scrinnPredleg == 1 && $field772_OutcomPeriod >= 37 && $value771 == 1){
			//return "4б";
			return 6;
		}else
		if ($anketaParitet >= 1 && $anketaCaesarian == 1 && $fieldB_CountChild == 1 && $scrinnPredleg == 1 && $field772_OutcomPeriod >= 37){
			//return "5.1";
			return 7;
		}else
		if ($anketaParitet > 1 && $anketaCaesarian > 1 && $fieldB_CountChild == 1 && $scrinnPredleg == 1 && $field772_OutcomPeriod >= 37){
			//return "5.2";
			return 8;
		}else
		if ($anketaParitet == 0 && $fieldB_CountChild== 1 && ($scrinnPredleg == 2 || $scrinnPredleg == 3 || $scrinnPredleg == 4)){
			//return "6";
			return 9;
		}else
		if ($anketaParitet >= 1 && $fieldB_CountChild == 1 && ($scrinnPredleg == 2 || $scrinnPredleg == 3 || $scrinnPredleg == 4) && $anketaCaesarian >= 0){
			//return "7";
			return 10;
		}else
		if ($fieldB_CountChild > 1 && $anketaCaesarian >= 0){
			//return "8";
			return 11;
		}else
		if ($fieldB_CountChild == 1 && ($ScrinnPolog == 2 || $ScrinnPolog == 3) && $anketaCaesarian >= 0){
			//return "9";
			return 12;
		}else
		if ($fieldB_CountChild == 1 && $scrinnPredleg == 1 && $field772_OutcomPeriod < 37 && $anketaCaesarian >= 0){
			//return "10";
			return 13;
		}
		return "";
	}

	/**
	 * Сохранение данных исхода беременности
	 */
	function saveBirthSpecStac($data, $isAllowTransaction = true) {
		$response = array('success' => true, 'BirthSpecStac_id' => null);

		$this->isAllowTransaction = $isAllowTransaction;
		$this->beginTransaction();

		$OutcomPeriod = $data['BirthSpecStac_OutcomPeriod'];

		$PregnancyResult_Code = $this->getFirstResultFromQuery("
			select
			    PregnancyResult_Code as \"PregnancyResult_Code\"
			from 
			    v_PregnancyResult 
		    where 
		        PregnancyResult_id = :PregnancyResult_id
		    limit 1
		", array('PregnancyResult_id' => $data['PregnancyResult_id']));
		if (!$PregnancyResult_Code) {
			$this->rollbackTransaction();
			return $this->createError('','Ошибка при получении кода исхода беременности');
		}

		$data['BirthSpecStac_OutcomDT'] = $data['BirthSpecStac_OutcomDate'].' '.$data['BirthSpecStac_OutcomTime'];

		if (empty($data['ChildDeathData'])) {
			$data['ChildDeathData'] = array();
		}
		if (is_string($data['ChildDeathData'])) {
			$data['ChildDeathData'] = json_decode($data['ChildDeathData'], true);
		}

		$params = array(
			'pmUser_id' => $data['pmUser_id'],
			//Общая часть
			'BirthSpecStac_id' => !empty($data['BirthSpecStac_id'])?$data['BirthSpecStac_id']:null,
			'PersonRegister_id' => $data['PersonRegister_id'],
			'PregnancySpec_id' => !empty($data['PregnancySpec_id'])?$data['PregnancySpec_id']:null,
			'Evn_id' => $data['Evn_id'],
			'EvnSection_id' => $data['EvnSection_id'],
			'Lpu_id' => $data['Lpu_oid'],
			'BirthSpecStac_OutcomDT' => $data['BirthSpecStac_OutcomDT'],
			'BirthSpecStac_OutcomPeriod' => $data['BirthSpecStac_OutcomPeriod'],
			'PregnancyResult_id' => $data['PregnancyResult_id'],
			'BirthSpecStac_CountPregnancy' => $data['BirthSpecStac_CountPregnancy'],
			'BirthSpecStac_CountChild' => $data['BirthSpecStac_CountChild'],
			'BirthSpecStac_BloodLoss' => $data['BirthSpecStac_BloodLoss'],
			'BirthSpecStac_IsRWtest' => !empty($data['BirthSpecStac_IsRWtest'])?$data['BirthSpecStac_IsRWtest']:1,
			'BirthSpecStac_IsRW' => !empty($data['BirthSpecStac_IsRW'])?$data['BirthSpecStac_IsRW']:1,
			'BirthSpecStac_IsHIVtest' => !empty($data['BirthSpecStac_IsHIVtest'])?$data['BirthSpecStac_IsHIVtest']:1,
			'BirthSpecStac_IsHIV' => !empty($data['BirthSpecStac_IsHIV'])?$data['BirthSpecStac_IsHIV']:1,
			'BirthSpecStac_IsHBtest' => !empty($data['BirthSpecStac_IsHBtest'])?$data['BirthSpecStac_IsHBtest']:1,
			'BirthSpecStac_IsHB' => !empty($data['BirthSpecStac_IsHB'])?$data['BirthSpecStac_IsHB']:1,
			'BirthSpecStac_IsHCtest' => !empty($data['BirthSpecStac_IsHCtest'])?$data['BirthSpecStac_IsHCtest']:1,
			'BirthSpecStac_IsHC' => !empty($data['BirthSpecStac_IsHC'])?$data['BirthSpecStac_IsHC']:1,
			'BirthResult_id' => null,		//Рассчитываемое поле
			'ignoreCheckBirthSpecStacDate' => !empty($data['ignoreCheckBirthSpecStacDate'])?$data['ignoreCheckBirthSpecStacDate']:0,
			//Роды
			'BirthPlace_id' => null,
			'BirthSpec_id' => null,
			'BirthCharactType_id' => null,
			'BirthSpecStac_CountBirth' => null,
			'BirthSpecStac_CountChildAlive' => null,
			'BirthSpecStac_IsContrac' => null,
			'BirthSpecStac_ContracDesc' => null,
			//Аборт
			'AbortLpuPlaceType_id' => null,
			'AbortLawType_id' => null,
			'AbortMethod_id' => null,
			'AbortIndicat_id' => null,
			'BirthSpecStac_InjectVMS' => null,
			'AbortType_id' => null,		//Рассчитываемое поле
			'BirthSpecStac_IsMedicalAbort' => null,	//Рассчитываемое поле
			//Внематочная беременность
			'BirthSpecStac_SurgeryVolume' => null,
			'PregnancyType_id' => !empty($data['PregnancyType_id'])?$data['PregnancyType_id']:NULL,
			'LaborActivity_id' => $data['LaborActivity_id'],
			'FetalHeartbeat_id' => $data['FetalHeartbeat_id'],
			'FetalHead_id' => $data['FetalHead_id']
		);

		switch(true) {
			//Роды в срок
			case ($PregnancyResult_Code == 1 && $OutcomPeriod >= 38): $params['BirthResult_id'] = 1;break;
			//Преждевременные роды
			case ($PregnancyResult_Code == 1 && $OutcomPeriod <= 37): $params['BirthResult_id'] = 2;break;
			//Аборт
			case ($PregnancyResult_Code == 3): $params['BirthResult_id'] = 3;break;
			//Выкидыш
			case ($PregnancyResult_Code == 2): $params['BirthResult_id'] = 4;break;
			//В остальных случаях - выкидыш
			default: $params['BirthResult_id'] = 4;
		}

		$params['BirthPlace_id'] = $this->getBirthPlaceId(3);	//По умолчанию "В другом месте"
		if (!$params['BirthPlace_id']) {
			$this->rollbackTransaction();
			return $this->createError('','Ошибка при получении идентификатора места родов');
		}

		switch($PregnancyResult_Code) {
			case 1:	//Роды
				$params['BirthPlace_id'] = !empty($data['BirthPlace_id'])?$data['BirthPlace_id']:null;
				$params['BirthSpec_id'] = !empty($data['BirthSpec_id'])?$data['BirthSpec_id']:null;
				$params['BirthCharactType_id'] = !empty($data['BirthCharactType_id'])?$data['BirthCharactType_id']:null;
				$params['BirthSpecStac_CountBirth'] = isset($data['BirthSpecStac_CountBirth'])?$data['BirthSpecStac_CountBirth']:null;
				$params['BirthSpecStac_CountChildAlive'] = isset($data['BirthSpecStac_CountChildAlive'])?$data['BirthSpecStac_CountChildAlive']:null;
				$params['BirthSpecStac_IsContrac'] = !empty($data['BirthSpecStac_IsContrac'])?$data['BirthSpecStac_IsContrac']:null;
				$params['BirthSpecStac_ContracDesc'] = !empty($data['BirthSpecStac_ContracDesc'])?$data['BirthSpecStac_ContracDesc']:null;
				break;

			case 2:	//Самопроизвольный аборт
				$AbortType_id = $this->getAbortTypeId($params);
				if ($AbortType_id === false) {
					$this->rollbackTransaction();
					return $this->createError('','Ошибка при рассчете типа аборта');
				}
				$params['AbortType_id'] = !empty($AbortType_id)?$AbortType_id:null;
				break;

			case 3:	//Искусственный аборт
				$params['AbortLpuPlaceType_id'] = !empty($data['AbortLpuPlaceType_id'])?$data['AbortLpuPlaceType_id']:null;
				$params['AbortLawType_id'] = !empty($data['AbortLawType_id'])?$data['AbortLawType_id']:null;
				$params['AbortMethod_id'] = !empty($data['AbortMethod_id'])?$data['AbortMethod_id']:null;
				$params['AbortIndicat_id'] = !empty($data['AbortIndicat_id'])?$data['AbortIndicat_id']:null;
				$params['BirthSpecStac_InjectVMS'] = !empty($data['BirthSpecStac_InjectVMS'])?$data['BirthSpecStac_InjectVMS']:null;

				$params['BirthSpecStac_IsMedicalAbort'] = ($params['AbortMethod_id'] == 1)?2:1;

				$AbortType_id = $this->getAbortTypeId($params);
				if ($AbortType_id === false) {
					$this->rollbackTransaction();
					return $this->createError('','Ошибка при рассчете типа аборта');
				}
				$params['AbortType_id'] = $AbortType_id;
				break;

			case 4:	//Внематочная беременность
				$params['BirthSpecStac_SurgeryVolume'] = !empty($data['BirthSpecStac_SurgeryVolume'])?$data['BirthSpecStac_SurgeryVolume']:null;
				break;
		}

		$resp = $this->validateBirthSpecStac(array_merge($params, array(
			'PregnancyResult_Code' => $PregnancyResult_Code
		)));
		if (!$this->isSuccessful($resp)) {
			$this->rollbackTransaction();
			return $resp;
		}

		$query = "
				select
				(
					select PregnancyQuestion_AnswerInt
					from dbo.PregnancyQuestion
					where PersonRegister_id=PR.PersonRegister_id
						and QuestionType_id=231
				) as \"anketaParitet\",
				(
					select PQ.PregnancyQuestion_ValuesStr
					from dbo.PregnancyQuestion PQ
						inner join v_PregnancyScreen PS on PS.PregnancyScreen_id=PQ.PregnancyScreen_id
					where PQ.PersonRegister_id=PR.PersonRegister_id
						and PQ.QuestionType_id=382
					order by PS.PregnancyScreen_setDT desc
				) as \"scrinnPredleg\",
				(
					select PregnancyQuestion_AnswerInt
					from dbo.PregnancyQuestion
					where PersonRegister_id=PR.PersonRegister_id
						and QuestionType_id=770
				) as \"anketaCaesarian\",
				(
					select PQ.PregnancyQuestion_ValuesStr
					from dbo.PregnancyQuestion PQ
						inner join v_PregnancyScreen PS on PS.PregnancyScreen_id=PQ.PregnancyScreen_id
					where PQ.PersonRegister_id=PR.PersonRegister_id
						and PQ.QuestionType_id=381
					order by PS.PregnancyScreen_setDT desc
				) as \"scrinnPolog\"
			from
				v_PersonRegister PR
				left join lateral (
					select PP.*
					from dbo.v_PersonPregnancy PP
					where PP.PersonRegister_id=PR.PersonRegister_id
					order by PP.PersonPregnancy_updDT desc
					limit 1
				) LastPregnancy on true
				left join lateral (
					select Screen.*
					from v_PregnancyScreen Screen
					where Screen.PersonRegister_id = PR.PersonRegister_id
					order by Screen.PregnancyScreen_setDT desc
					limit 1
				) LastScreen on true
			where
				LastPregnancy.person_id = (select Person_id from dbo.PersonRegister where PersonRegister_id=:PersonRegister_id)
		";

		$resp_robson = $this->getFirstRowFromQuery($query, $data);
		$robson_params = array();
		if (is_array($resp_robson)) {
			$robson_params['AnketaParitet']=$resp_robson['anketaParitet'];
			$robson_params['ScrinnPredleg']=$resp_robson['scrinnPredleg'];
			$robson_params['AnketaCaesarian']=$resp_robson['anketaCaesarian'];
			$robson_params['ScrinnPolog']=$resp_robson['scrinnPolog'];
		}else{
			$robson_params['AnketaParitet']=0;
			$robson_params['ScrinnPredleg']=0;
			$robson_params['AnketaCaesarian']=0;
			$robson_params['ScrinnPolog']=0;
		}

		$answers = array();
		if (is_array($data['Answers'])) {
			$answers = $data['Answers'];
		} else if (is_string($data['Answers'])) {
			$answers = json_decode($data['Answers'], true);
		}

		$robson_params['QuestionType_771']=$answers[771];
		$robson_params['QuestionType_546']=$answers[546];
		$robson_params['BirthSpecStac_CountChild']=$data['BirthSpecStac_CountChild'];
		$robson_params['BirthSpecStac_OutcomPeriod']=$data['BirthSpecStac_OutcomPeriod'];
		$robson_params['QuestionType_524']=$answers[524];
		$robson_params['QuestionType_525']=$answers[525];

		$answers[772] = $this->getRobsonsValue($robson_params);
		$data['Answers']=$answers;

		if (empty($data['BirthSpecStac_id'])) {
			$procedure = 'p_BirthSpecStac_ins';
		} else {
			$procedure = 'p_BirthSpecStac_upd';
		}

		$query = "
			
            select 
                BirthSpecStac_id as \"BirthSpecStac_id\",
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
            from {$procedure}
            (
				BirthSpecStac_id := :BirthSpecStac_id,
				PersonRegister_id := :PersonRegister_id,
				PregnancySpec_id := :PregnancySpec_id,
				Evn_id := :Evn_id,
				EvnSection_id := :EvnSection_id,
				Lpu_id := :Lpu_id,
				BirthSpecStac_OutcomDT := :BirthSpecStac_OutcomDT,
				BirthSpecStac_OutcomPeriod := :BirthSpecStac_OutcomPeriod,
				PregnancyResult_id := :PregnancyResult_id,
				BirthSpecStac_CountPregnancy := :BirthSpecStac_CountPregnancy,
				BirthSpecStac_CountBirth := :BirthSpecStac_CountBirth,
				BirthSpecStac_CountChild := :BirthSpecStac_CountChild,
				BirthSpecStac_CountChildAlive := :BirthSpecStac_CountChildAlive,
				BirthSpecStac_BloodLoss := :BirthSpecStac_BloodLoss,
				BirthSpecStac_IsRWtest := :BirthSpecStac_IsRWtest,
				BirthSpecStac_IsRW := :BirthSpecStac_IsRW,
				BirthSpecStac_IsHIVtest := :BirthSpecStac_IsHIVtest,
				BirthSpecStac_IsHIV := :BirthSpecStac_IsHIV,
				BirthSpecStac_IsHBtest := :BirthSpecStac_IsHBtest,
				BirthSpecStac_IsHB := :BirthSpecStac_IsHB,
				BirthSpecStac_IsHCtest := :BirthSpecStac_IsHCtest,
				BirthSpecStac_IsHC := :BirthSpecStac_IsHC,
				BirthSpecStac_IsContrac := :BirthSpecStac_IsContrac,
				BirthSpecStac_ContracDesc := :BirthSpecStac_ContracDesc,
				BirthResult_id := :BirthResult_id,
				BirthPlace_id := :BirthPlace_id,
				BirthSpec_id := :BirthSpec_id,
				BirthCharactType_id := :BirthCharactType_id,
				AbortLpuPlaceType_id := :AbortLpuPlaceType_id,
				AbortLawType_id := :AbortLawType_id,
				AbortMethod_id := :AbortMethod_id,
				AbortIndicat_id := :AbortIndicat_id,
				AbortType_id := :AbortType_id,
				BirthSpecStac_IsMedicalAbort := :BirthSpecStac_IsMedicalAbort,
				BirthSpecStac_InjectVMS := :BirthSpecStac_InjectVMS,
				BirthSpecStac_SurgeryVolume := :BirthSpecStac_SurgeryVolume,
				LaborActivity_id := :LaborActivity_id,
				FetalHeartbeat_id := :FetalHeartbeat_id,
				FetalHead_id := :FetalHead_id,
				pmUser_id := :pmUser_id,
				PregnancyType_id := :PregnancyType_id
			)
		";
		//echo getDebugSQL($query, $params);exit;
		$resp = $this->queryResult($query, $params);
		if (!$this->isSuccessful($resp)) {
			$this->rollbackTransaction();
			return $resp;
		}
		$response['BirthSpecStac_id'] = $data['BirthSpecStac_id'] = $resp[0]['BirthSpecStac_id'];
		$response['RobsonValue'] = $answers[772];

		//Обновление записи регистра
		$resp = $this->updatePersonRegister($data);
		if (!$this->isSuccessful($resp)) {
			$this->rollbackTransaction();
			return $resp;
		}

		//Редактирование интранатальных факторов риска
		$data['DispClass_id'] = 18;
		if ($PregnancyResult_Code == 1) {
			//Заполнение анкеты (только в исходе "Роды")
			$resp = $this->savePregnancyQuestionAnswers($data);
			if (!$this->isSuccessful($resp)) {
				$this->rollbackTransaction();
				return $resp;
			}
		} else {
			//Очистка анкеты (при всех остальных исходах)
			$resp = $this->deletePregnancyQuestionAnswers($data);
			if (!$this->isSuccessful($resp)) {
				$this->rollbackTransaction();
				return $resp;
			}
		}

		//Обновление данных о мертворожденных
		$this->load->model('BirthSpecStac_model');

		$ChildDeathData = $data['ChildDeathData'];
		if(!(count($ChildDeathData) == 1 && empty($ChildDeathData[0]['Diag_id']) && empty($ChildDeathData[0]['MedStaffFact_id']))){
			foreach($ChildDeathData as $ChildDeath) {
				$ChildDeath['ChildDeath_id'] = (!empty($ChildDeath['ChildDeath_id'])&&$ChildDeath['ChildDeath_id']>0)?$ChildDeath['ChildDeath_id']:null;
				$ChildDeath['BirthSpecStac_id'] = $data['BirthSpecStac_id'];
				$ChildDeath['pmUser_id'] = $data['pmUser_id'];
				$ChildDeath['Server_id'] = $data['Server_id'];

				foreach ($ChildDeath as $key => $value) {
					if($value === ''){
						$ChildDeath[$key] = null;
					}
				}
				switch($ChildDeath['RecordStatus_Code']) {
					case 0:
					case 2:
						$resp = $this->BirthSpecStac_model->saveChildDeath($ChildDeath);
						break;
					case 3:
						$resp = $this->BirthSpecStac_model->deleteChildDeath($ChildDeath['ChildDeath_id']);
						break;
				}
				if (!$this->isSuccessful($resp)) {
					$this->rollbackTransaction();
					return $resp;
				}
			}
		}

		$ChildrenCount = $this->getChildrenCount($data);
		if ($PregnancyResult_Code != 1 && $ChildrenCount > 0) {
			$this->rollbackTransaction();
			return $this->createError('','В исходе беременности заведены дети. Результатом исхода беременности может быть только "Роды".');
		}
		$ignoreCheckChildrenCount = (isset($data['ignoreCheckChildrenCount']) && $data['ignoreCheckChildrenCount']);
		if ($this->regionNick == 'ufa' &&
			!$ignoreCheckChildrenCount &&
			$PregnancyResult_Code == 1 &&
			$data['BirthSpecStac_CountChild'] != $ChildrenCount
		) {
			$this->rollbackTransaction();
			$this->_setAlertMsg("Указанное  значение в поле «Количество плодов» отличается от рассчитанного автоматически. Продолжить сохранение?");
			return $this->createError(202,'YesNo');
		}

		//Закрытие записи в базовом регистре
		$params = array(
			'PersonRegister_id' => $data['PersonRegister_id'],
			'MedPersonal_did' => $data['MedPersonal_oid'],
			'Lpu_did' => $data['Lpu_oid'],
			'PersonRegister_disDate' => $data['BirthSpecStac_OutcomDate'],
			'PersonRegisterOutCause_SysNick' => null,
			'pmUser_id' => $data['pmUser_id'],
			'session' => $data['session']
		);
		switch($PregnancyResult_Code) {
			case 1: $params['PersonRegisterOutCause_SysNick'] = 'birth';break;
			case 2: $params['PersonRegisterOutCause_SysNick'] = 'SponAbort';break;
			case 3: $params['PersonRegisterOutCause_SysNick'] = 'IndAbort';break;
			case 4: $params['PersonRegisterOutCause_SysNick'] = 'EctPregn';break;
		}
		$resp = $this->savePersonRegister($params, false);
		if (!$this->isSuccessful($resp)) {
			$this->rollbackTransaction();
			return $resp;
		}

		if ($data["BirthSpecStac_BloodLoss"] > 1000){

			$params['PersonRegister_id'] = $data['PersonRegister_id'];
			//получение данные о сотрудниках, у кого стоит галочка на акушерское кровотечение
			$users_notice = $this->getUsersForPersonNoticePolka($data);
			if(is_array($users_notice) && count($users_notice)>0){
				//создаем уведомления
				$query = "
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from dbo.p_BirthSpecStac_Notice_Add(
						PersonRegister_id := :PersonRegister_id,
						UserIdS := :UserIdS
					)
				";
				$params['UserIdS'] = implode(',',$users_notice);
				$resp = $this->queryResult($query, $params);

				if (!is_array($resp)) {
					$this->rollbackTransaction();
					return $resp;
				}
			}
		}

		$this->isAllowTransaction = $isAllowTransaction;
		$this->commitTransaction();

		return array($response);
	}
	
	/**
	 * Получение пользователей для рассылки уведомлений по акушерским кровотечениям
	 * (для поликлиники)
	 */
	function getUsersForPersonNoticePolka($data) {

		if(empty($data['PersonRegister_id'])) return false;

		$params = ['PersonRegister_id' => $data['PersonRegister_id']];
		$users_notice = array();

		$query = "
			SELECT distinct
				pmUser_id as \"pmUser_id\"
			from
				v_pmUserCache puc
			where
				(
					puc.lpu_id = (select lpu_oid from dbo.v_PersonPregnancy where PersonRegister_id = :PersonRegister_id)
					or
					puc.lpu_id = (select lpu_id from dbo.v_BirthSpecStac where PersonRegister_id = :PersonRegister_id order by BirthSpecStac_insDT desc limit 1)
				)
				and coalesce(puc.pmUser_deleted, 1) = 1
				and puc.pmUser_EvnClass ilike '%is_perinatal_haemorrhage%'
		";

		if(getRegionNick() == 'ufa'){
			$query = "
				SELECT distinct
					puc.pmUser_id as \"pmUser_id\"
				from
					v_pmUserCache puc
					inner join lateral(
						SELECT
							msf.MedPersonal_id
						FROM
							v_MedStaffFact msf
							left join v_Lpu lpu on lpu.Lpu_id = msf.Lpu_id
							left join v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id
							left join v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
							left join v_LpuUnitType lut on lut.LpuUnitType_id = lu.LpuUnitType_id
						WHERE
							msf.MedPersonal_id = puc.MedPersonal_id and msf.Lpu_id = puc.lpu_id
							and msf.MedStaffFact_Stavka > 0
							and lut.LpuUnitType_SysNick in ('stac', 'polka')
						union
						select
							msmp.MedPersonal_id
						from v_MedService MS
						inner join lateral(
							Select
								msmp.MedPersonal_id
							from v_MedServiceMedPersonal msmp
							where msmp.MedService_id = MS.MedService_id
							and msmp.MedPersonal_id = puc.MedPersonal_id
							limit 1
						) as msmp on true
						left join v_MedServiceType mst on mst.MedServiceType_id = MS.MedServiceType_id
	
						where
						 msmp.MedPersonal_id = puc.MedPersonal_id and
						mst.MedServiceType_SysNick in ('leadermo')
					) as ms on true
				where
					(
						puc.lpu_id = (select lpu_oid from dbo.v_PersonPregnancy where PersonRegister_id = :PersonRegister_id)
						or
						puc.lpu_id = (select lpu_id from dbo.v_BirthSpecStac where PersonRegister_id = :PersonRegister_id  order by BirthSpecStac_insDT desc limit 1)
					)
					and coalesce(puc.pmUser_deleted, 1) = 1
					and puc.pmUser_EvnClass ilike '%is_perinatal_haemorrhage%'
			";
		}

		$users = $this->queryResult($query, $params);
		if (!$this->isSuccessful($users)) {
			return array();
		}

		foreach($users as $item) {
			array_push($users_notice, $item['pmUser_id']);
		}

		return $users_notice;
	}

	/**
	 * Получение информации по исходу беременности
	 */
	function getBirthSpecStacInfo($data) {
		$params = array('BirthSpecStac_id' => $data['BirthSpecStac_id']);
		$query = "
			select
				PR.PersonRegister_id as \"PersonRegister_id\",
				DM.DeathMother_id as \"DeathMother_id\",
				case when Evn.cnt+BirthSvid.cnt+PntDeathSvid.cnt > 0 then 0 else 1 end as \"allowDelete\"
			from
				v_BirthSpecStac BSS
				left join v_PersonRegister PR on PR.PersonRegister_id = BSS.PersonRegister_id
				left join v_DeathMother DM on DM.PersonRegister_id = PR.PersonRegister_id
				left join lateral(
					select 
					    count(*) as cnt
					from 
					    v_Evn E
					where 
					    E.Person_id in (select Person_id from v_PersonNewBorn where BirthSpecStac_id = :BirthSpecStac_id)
				) Evn on true
				left join lateral (
					select 
					    count(*) as cnt
					from 
					    v_BirthSvid BS
					where 
					    BS.Person_cid in (select Person_id from v_PersonNewBorn where BirthSpecStac_id = :BirthSpecStac_id)
					and 
                        coalesce (BS.BirthSvid_IsBad, 1) = 1
					limit 1
				) BirthSvid on true
				left join lateral(
					select 
					    count(*) as cnt
					from 
					    v_PntDeathSvid PDS
					where 
                        (
                              PDS.Person_cid in (select Person_id from v_PersonNewBorn where BirthSpecStac_id = :BirthSpecStac_id)
                            or 
                              PDS.PntDeathSvid_id in (select PntDeathSvid_id from v_ChildDeath where BirthSpecStac_id = :BirthSpecStac_id)
                        )
					and 
					    coalesce (PDS.PntDeathSvid_isBad, 1) = 1
					and 
					    coalesce (PDS.PntDeathSvid_IsLose, 1) = 1
					and 
					    coalesce (PDS.PntDeathSvid_IsActual, 1) = 2					
				) PntDeathSvid on true
			where BSS.BirthSpecStac_id = :BirthSpecStac_id
			limit 1
		";
		return $this->getFirstRowFromQuery($query, $params);
	}

	/**
	 * Выполение проверок перед удалением исхода беременности (в движении КВС)
	 */
	function beforeDeleteBirthSpecStac($data) {
		$info = $this->getBirthSpecStacInfo($data);
		if (!is_array($info)) {
			$this->rollbackTransaction();
			return $this->createError('','Ошибка при получении данных исхода беременности');
		}

		if (!$info['allowDelete']) {
			$this->rollbackTransaction();
			return $this->createError('','Для удаления исхода беременности у детей не должно быть случаев лечения, данных наблюдений и мед. свидетельств.');
		}
		return array(array('success' => true));
	}

	/**
	 * Удаление данных исхода беременности
	 */
	function deleteBirthSpecStac($data, $isAllowTransaction = true) {
		$this->isAllowTransaction = $isAllowTransaction;
		$this->beginTransaction();

		$response = array('success' => true, 'deletedObjects' => array());

		$info = $this->getBirthSpecStacInfo($data);
		if (!is_array($info)) {
			$this->rollbackTransaction();
			return $this->createError('','Ошибка при получении данных исхода беременности');
		}

		if (!$info['allowDelete']) {
			$this->rollbackTransaction();
			return $this->createError('','Для удаления исхода беременности у детей не должно быть случаев лечения, данных наблюдений и мед. свидетельств.');
		}

		//Удаление исхода
		$this->load->model('BirthSpecStac_model');
		$resp = $this->BirthSpecStac_model->del($data['BirthSpecStac_id'], $data['pmUser_id'], false);
		if (!$this->isSuccessful($resp)) {
			$this->rollbackTransaction();
			return $resp;
		}

		if (!empty($info['PersonRegister_id'])) {
			//Очистка анкеты
			$resp = $this->deletePregnancyQuestionAnswers(array(
				'PersonRegister_id' => $info['PersonRegister_id'],
				'DispClass_id' => 18
			));
			if (!$this->isSuccessful($resp)) {
				$this->rollbackTransaction();
				return $resp;
			}

			//Попытка удалить запись регистра
			$resp = $this->deletePersonRegister(array(
				'PersonRegister_id' => $info['PersonRegister_id'],
				'pmUser_id' => $data['pmUser_id'],
			));
			if ($this->isSuccessful($resp)) {
				$response['deletedObjects']['PersonRegister_id'] = $info['PersonRegister_id'];
				$info['PersonRegister_id'] = null;
			} else if ($resp[0]['Error_Code'] != 1) {
				$this->rollbackTransaction();
				return $resp;
			}

			if (!empty($info['PersonRegister_id'])) {
				//Очистка параметров закрытия регистра, если нет случая материнской смертности
				if (empty($info['DeathMother_id'])) {
					$params = array(
						'PersonRegister_id' => $info['PersonRegister_id'],
						'MedPersonal_did' => null,
						'Lpu_did' => null,
						'PersonRegister_disDate' => null,
						'PersonRegisterOutCause_SysNick' => null,
						'pmUser_id' => $data['pmUser_id'],
						'session' => $data['session']
					);
					$resp = $this->savePersonRegister($params, false, true);
					if (!$this->isSuccessful($resp)) {
						$this->rollbackTransaction();
						return $resp;
					}
				}

				//Обновление записи регистра
				$resp = $this->updatePersonRegister(array(
					'PersonRegister_id' => $info['PersonRegister_id']
				));
				if (!$this->isSuccessful($resp)) {
					$this->rollbackTransaction();
					return $resp;
				}
			}
		}

		$this->isAllowTransaction = $isAllowTransaction;
		$this->commitTransaction();

		return array($response);
	}

	/**
	 * Расчет случая материнской смертности
	 */
function generateDeathMother($data) {
		$params = array(
			'PersonRegister_id' => $data['PersonRegister_id'],
			'EvnSection_id' => $data['EvnSection_id'],
		);

		$query = "
			select
				DM.DeathMother_id as \"DeathMother_id\",
				PR.PersonRegister_id as \"PersonRegister_id\",
				ES.EvnSection_id as \"Evn_id\",
				ES.Lpu_id as \"Lpu_oid\",
				ES.MedPersonal_id as \"MedPersonal_oid\",
				to_char(coalesce (DS.DeathSvid_DeathDate, ES.EvnSection_disDT), '{$this->dateTimeForm104}') as \"DeathMother_DeathDate\",
				to_char(coalesce (DS.DeathSvid_DeathDate, ES.EvnSection_disDT), '{$this->dateTimeForm108}') as \"DeathMother_DeathTime\",
				coalesce (DMT.DeathMotherType_id, DM.DeathMotherType_id) as \"DeathMotherType_id\",
				ES.Diag_id as \"Diag_cid\",
				coalesce (ED.Diag_aid, DM.Diag_aid) as \"Diag_aid\",
				coalesce (DP.DeathPlace_Name, DM.DeathMother_DeathPlace) as \"DeathMother_DeathPlace\"
			from
				v_EvnSection ES
				inner join v_EvnDie ED on ED.EvnDie_pid = ES.EvnSection_id
				inner join v_PersonRegister PR on PR.Person_id = ES.Person_id and PR.PersonRegister_id = :PersonRegister_id
				left join v_DeathMother DM on DM.PersonRegister_id = PR.PersonRegister_id
				left join v_BirthSpecStac BSS on BSS.PersonRegister_id = PR.PersonRegister_id
				left join v_PregnancyResult Result on Result.PregnancyResult_id = BSS.PregnancyResult_id
				left join lateral(
					select
					    DMT.DeathMotherType_id
					from
					    v_DeathMotherType DMT
					where DMT.DeathMotherType_Code = (case
						when Result.PregnancyResult_Code = 4 then 1
						when Result.PregnancyResult_Code in (2,3) then 2
						when Result.PregnancyResult_Code = 1 and BSS.BirthSpecStac_OutcomPeriod < 28 then 3
						when Result.PregnancyResult_Code = 1 and BSS.BirthSpecStac_OutcomPeriod >= 28 then 4
					end)
					limit 1
				) DMT on true
				left join lateral (
					select
						DS.DeathSvid_DeathDate,
						DS.DeathPlace_id
					from v_DeathSvid DS
					where DS.Person_id = ES.Person_id
					order by case
						when DS.DeathSvidType_id = 4 then 1
						when DS.DeathSvidType_id = 1 then 2
						when DS.DeathSvidType_id = 3 then 3
						when DS.DeathSvidType_id = 2 then 4
					end
					limit 1
				) DS on true
				left join v_DeathPlace DP on DP.DeathPlace_id = DS.DeathPlace_id
			where
				ES.EvnSection_id = :EvnSection_id
		";

		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('Ошибка при расчете случая материнской смертности');
		}
		return $response;
	}

	/**
	 * Получения данных случая материнской смертности для редактирования
	 */
	function loadDeathMotherForm($data) {
		$params = array('DeathMother_id' => $data['DeathMother_id']);
		$query = "
			select
				DM.DeathMother_id as \"DeathMother_id\",
				DM.PersonRegister_id as \"PersonRegister_id\",
				DM.Evn_id as \"Evn_id\",
				to_char(DM.DeathMother_DeathDate, '{$this->dateTimeForm104}') as \"DeathMother_DeathDate\",
				DM.DeathMother_DeathPlace as \"DeathMother_DeathPlace\",
				DM.DeathMotherType_id as \"DeathMotherType_id\",
				DM.Diag_cid as \"Diag_cid\",
				DM.Diag_aid as \"Diag_aid\",
				PR.Lpu_did as \"Lpu_oid\",
				PR.MedPersonal_did as \"MedPersonal_oid\"
			from
				v_DeathMother DM
				left join v_PersonRegister PR on PR.PersonRegister_id = DM.PersonRegister_id
			where 
			    DM.DeathMother_id = :DeathMother_id
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Сохранение случая материнской смертности
	 */
	function saveDeathMother($data, $isAllowTransaction = true) {
		$response = array('success' => true, 'DeathMother_id' => null);

		$this->isAllowTransaction = $isAllowTransaction;
		$this->beginTransaction();

		$params = array(
			'DeathMother_id' => !empty($data['DeathMother_id'])?$data['DeathMother_id']:null,
			'PersonRegister_id' => $data['PersonRegister_id'],
			'Evn_id' => !empty($data['Evn_id'])?$data['Evn_id']:null,
			'DeathMother_DeathDate' => $data['DeathMother_DeathDate'],
			'DeathMother_DeathPlace' => !empty($data['DeathMother_DeathPlace'])?$data['DeathMother_DeathPlace']:null,
			'DeathMotherType_id' => $data['DeathMotherType_id'],
			'Diag_cid' => !empty($data['Diag_cid'])?$data['Diag_cid']:null,
			'Diag_aid' => !empty($data['Diag_aid'])?$data['Diag_aid']:null,
			'pmUser_id' => $data['pmUser_id']
		);
		if (empty($params['DeathMother_id'])) {
			$procedure = "p_DeathMother_ins";
		} else {
                $procedure = "p_DeathMother_upd";
		}
		$query = "
            select 
                DeathMother_id as \"DeathMother_id\",
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
            from {$procedure}
            (
				DeathMother_id := :DeathMother_id,
				PersonRegister_id := :PersonRegister_id,
				Evn_id := :Evn_id,
				DeathMother_DeathDate := :DeathMother_DeathDate,
				DeathMother_DeathPlace := :DeathMother_DeathPlace,
				DeathMotherType_id := :DeathMotherType_id,
				Diag_cid := :Diag_cid,
				Diag_aid := :Diag_aid,
				pmUser_id := :pmUser_id
			)
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			$this->rollbackTransaction();
			return $this->createError('','Ошибка при сохранении случая материнской смертности');
		}
		$response['DeathMother_id'] = $resp[0]['DeathMother_id'];

		//Закрытие записи в базовом регистре
		$params = array(
			'PersonRegister_id' => $data['PersonRegister_id'],
			'MedPersonal_did' => $data['MedPersonal_oid'],
			'Lpu_did' => $data['Lpu_oid'],
			'PersonRegister_disDate' => $data['DeathMother_DeathDate'],
			'PersonRegisterOutCause_SysNick' => 'Death',
			'pmUser_id' => $data['pmUser_id'],
			'session' => $data['session']
		);
		$resp = $this->savePersonRegister($params, false);
		if (!$this->isSuccessful($resp)) {
			$this->rollbackTransaction();
			return $resp;
		}

		$this->isAllowTransaction = $isAllowTransaction;
		$this->commitTransaction();

		return array($response);
	}

	/**
	 * Удаление случая материнской смертности
	 */
	function deleteDeathMother($data) {
		$params = array('DeathMother_id' => $data['DeathMother_id']);

		$info = $this->getFirstRowFromQuery("
			select
				PR.PersonRegister_id as \"PersonRegister_id\",
				Result.PregnancyResult_Code as \"PregnancyResult_Code\",
				BSS.Lpu_id as \"Lpu_id\",
				coalesce(ES.MedPersonal_id,EVPL.MedPersonal_id) as \"MedPersonal_id\",
				to_char(BSS.BirthSpecStac_OutcomDT, '{$this->dateTimeForm108}') as \"BirthSpecStac_OutcomDate\"
			from
				v_DeathMother DM
				inner join v_PersonPregnancy PR on PR.PersonRegister_id = DM.PersonRegister_id
				left join v_BirthSpecStac BSS on BSS.PersonRegister_id = PR.PersonRegister_id
				left join v_PregnancyResult Result on Result.PregnancyResult_id = BSS.PregnancyResult_id
				left join v_EvnSection ES on ES.EvnSection_id = BSS.EvnSection_id
				left join v_EvnVizitPL EVPL on EVPL.EvnVizitPL_id = BSS.Evn_id
			where
				DM.DeathMother_id = :DeathMother_id
		", $params);
		if (!is_array($info)) {
			return $this->createError('','Ошибка при получении данных случая материнской смертности');
		}

		$this->beginTransaction();

		//Удаление случая материнской смертности
		$query = "
		    select 
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
            from p_DeathMother_del
            (
                DeathMother_id := :DeathMother_id
            )
		";
		$resp = $this->queryResult($query, $params);
		if(!is_array($resp)) {
			$this->rollbackTransaction();
			$this->createError('','Ошибка при удалении случая материнской смертности');
		}
		if (!$this->isSuccessful($resp)) {
			$this->rollbackTransaction();
			return $resp;
		}

		//Очистка/изменение параметров закрытия регистра
		if (empty($info['PregnancyResult_Code'])) {
			$params = array(
				'PersonRegister_id' => $info['PersonRegister_id'],
				'MedPersonal_did' => null,
				'Lpu_did' => null,
				'PersonRegister_disDate' => null,
				'PersonRegisterOutCause_SysNick' => null,
				'pmUser_id' => $data['pmUser_id'],
				'session' => $data['session']
			);
		} else {
			$params = array(
				'PersonRegister_id' => $info['PersonRegister_id'],
				'MedPersonal_did' => $info['MedPersonal_id'],
				'Lpu_did' => $info['Lpu_id'],
				'PersonRegister_disDate' => $info['BirthSpecStac_OutcomDate'],
				'PersonRegisterOutCause_SysNick' => null,
				'pmUser_id' => $data['pmUser_id'],
				'session' => $data['session']
			);
			switch($info['PregnancyResult_Code']) {
				case 1: $params['PersonRegisterOutCause_SysNick'] = 'birth';break;
				case 2: $params['PersonRegisterOutCause_SysNick'] = 'SponAbort';break;
				case 3: $params['PersonRegisterOutCause_SysNick'] = 'IndAbort';break;
				case 4: $params['PersonRegisterOutCause_SysNick'] = 'EctPregn';break;
			}
		}
		$resp = $this->savePersonRegister($params, false, true);
		if (!$this->isSuccessful($resp)) {
			$this->rollbackTransaction();
			return $resp;
		}

		$this->commitTransaction();
		return array(array('success' => true));
	}

	/**
	 * Получение данных родового сертификата для редактирования
	 */
	function loadBirthCertificateForm($data) {
		$params = array('BirthCertificate_id' => $data['BirthCertificate_id']);
		$query = "
			select
				BC.BirthCertificate_id as \"BirthCertificate_id\",
				BC.PersonRegister_id as \"PersonRegister_id\",
				BC.BirthCertificate_Num as \"BirthCertificate_Num\",
				BC.BirthCertificate_Ser as \"BirthCertificate_Ser\",
				to_char(BC.BirthCertificate_setDT, '{$this->dateTimeForm104}') as \"BirthCertificate_setDate\",
				BC.Lpu_id as \"Lpu_oid\"
			from 
			    v_BirthCertificate BC
			where 
			    BC.BirthCertificate_id = :BirthCertificate_id
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Сохранение данных родового сертификата
	 */
	function saveBirthCertificate($data) {
		$params = array(
			'BirthCertificate_id' => !empty($data['BirthCertificate_id'])?$data['BirthCertificate_id']:null,
			'PersonRegister_id' => $data['PersonRegister_id'],
			'BirthCertificate_Ser' => $data['BirthCertificate_Ser'],
			'BirthCertificate_Num' => $data['BirthCertificate_Num'],
			'BirthCertificate_setDT' => $data['BirthCertificate_setDate'],
			'Evn_id' => !empty($data['Evn_id'])?$data['Evn_id']:null,
			'Lpu_id' => $data['Lpu_oid'],
			'pmUser_id' => $data['pmUser_id']
		);
		if (empty($params['BirthCertificate_id'])) {
			$procedure = "p_BirthCertificate_ins";
		} else {
			$procedure = "p_BirthCertificate_upd";
		}
		$query = "
			select 
			    BirthCertificate_id as \"BirthCertificate_id\", 
			    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from {$procedure}
			(
				BirthCertificate_id := :BirthCertificate_id,
				PersonRegister_id := :PersonRegister_id,
				BirthCertificate_Ser := :BirthCertificate_Ser,
				BirthCertificate_Num := :BirthCertificate_Num,
				BirthCertificate_setDT := :BirthCertificate_setDT,
				Evn_id := :Evn_id,
				Lpu_id := :Lpu_id,
				pmUser_id := :pmUser_id
			)
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при сохранении родового сертификата');
		}
		return $resp;
	}

	/**
	 * Удаление родового сертификата
	 */
	function deleteBirthCertificate($data) {
		$params = array('BirthCertificate_id' => $data['BirthCertificate_id']);
		$query = "
			select 
                Error_Code as \"Error_Code\", 
                Error_Message as \"Error_Msg\"
            from p_BirthCertificate_del
            (
				BirthCertificate_id := :BirthCertificate_id
		    )
		";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('','Ошибка при удалении родового сертификата');
		}
		return $response;
	}

	/**
	 * Исключение из регистра беременных
	 */
	function doPersonPregnancyOut($data, $allowTransaction = true) {
		$params = array(
			'PersonRegister_id' => $data['PersonRegister_id'],
			'PersonRegister_disDate' => $data['PersonRegister_disDate'],
			'PersonRegisterOutCause_id' => !empty($data['PersonRegisterOutCause_id'])?$data['PersonRegisterOutCause_id']:null,
			'MedPersonal_did' => !empty($data['MedPersonal_did'])?$data['MedPersonal_did']:$data['session']['medpersonal_id'],
			'Lpu_did' => !empty($data['Lpu_did'])?$data['Lpu_did']:$data['session']['lpu_id'],
			'pmUser_id' => $data['pmUser_id'],
			'session' => $data['session']
		);

		return $this->savePersonRegister($params, $allowTransaction);
	}

	/**
	 * Отменить исключение из регистра беременных
	 */
	function cancelPersonPregnancyOut($data, $allowTransaction = true) {
		$params = array(
			'PersonRegister_id' => $data['PersonRegister_id'],
			'PersonRegister_disDate' => null,
			'PersonRegisterOutCause_id' => null,
			'MedPersonal_did' => null,
			'Lpu_did' => null,
			'pmUser_id' => $data['pmUser_id'],
			'session' => $data['session']
		);

		return $this->savePersonRegister($params, $allowTransaction);
	}

	/**
	 * Получение данных для информациионной панели в окне редактирования записи регистра беременных
	 */
	function getPersonRegisterInfo($data) {
		$params = array(
			'Person_id' => !empty($data['Person_id'])?$data['Person_id']:null,
			'PersonRegister_id' => !empty($data['PersonRegister_id'])?$data['PersonRegister_id']:null,
		);

		if (empty($params['Person_id']) && empty($params['PersonRegister_id'])) {
			return $this->createError('','Отсутсвуют обязательные параметры');
		}

		if (!empty($data['PersonRegister_id'])) {
			$query = "
				select
					PR.PersonRegister_id as \"PersonRegister_id\",
					PR.PersonRegister_Code as \"PersonRegister_Code\",
					to_char(PR.PersonRegister_setDate, '{$this->dateTimeForm104}') as \"PersonRegister_setDate\",
					coalesce (PS.Person_SurName,'') || coalesce (' '||PS.Person_FirName,'') || coalesce (' ' || PS.Person_SecName,'') as \"Person_Fio\",
					to_char(PS.Person_BirthDay, '{$this->dateTimeForm104}') as \"Person_BirthDay\",
					L.Lpu_Nick as \"Lpu_Nick\",
					MP.Person_Fio as \"MedPersonal_Fio\",

					coalesce(PP.PersonPregnancy_RiskDPP, 0) + coalesce (LastScreen.PregnancyScreen_RiskPerPat, 0) as \"PersonPregnancy_ObRisk\",
					RT.RiskType_Name as \"RiskType_Name\",
					(case when RKT.RiskType_Name is null
					then '&nbsp;&nbsp;&nbsp;&nbsp;'
					else RKT.RiskType_Name
					end) as \"RiskType_AName\",
					(case when ML.MesLevel_Name = 'Второй уровень (без высокотехнологичной помощи)'
						then 'МПЦ' when ML.MesLevel_Name is null
							then '    '
							else ML.MesLevel_Name
					end) as \"MesLevel_Name\",
                    (case when PR.PregnancyResult_id is null then ''
							else 'Группа акушерской популяции по Робсону: ' ||
								(case when PQ.PregnancyQuestion_ValuesStr is null then ''
							else RP.RobsonPopulation_Name end)
					end) as \"Robson\"
				from
					v_PersonRegister PR
					left join v_PersonState PS on PS.Person_id = PR.Person_id
					left join v_Lpu_all L on L.Lpu_id = PR.Lpu_iid
					left join v_MedPersonal MP on MP.MedPersonal_id = PR.MedPersonal_iid

					left join v_PersonPregnancy PP on PP.PersonRegister_id = PR.PersonRegister_id
					left join v_RiskType RT on RT.RiskType_id = PR.RiskType_id
					left join lateral (
						select
						    Screen.*
						from
						    v_PregnancyScreen Screen
						where
						    Screen.PersonRegister_id = PR.PersonRegister_id
						order by
						    Screen.PregnancyScreen_setDT desc
						limit 1
					) LastScreen on true
					left join v_RiskType RKT on RKT.RiskType_id = PR.RiskType_aid
					left join v_MesLevel ML on ML.MesLevel_id = PR.MesLevel_id
					left join v_PregnancyQuestion PQ on PQ.PersonRegister_id = PR.PersonRegister_id
						and PQ.QuestionType_id=772
					left join v_RobsonPopulation RP on RP.RobsonPopulation_id = PQ.PregnancyQuestion_ValuesStr
				where
					PR.PersonRegister_id = :PersonRegister_id
				limit 1
			";
		} else {
			$query = "
				select
					coalesce(PS.Person_SurName, '') || coalesce (' ' || PS.Person_FirName, '') || coalesce (' ' || PS.Person_SecName, '') as \"Person_Fio\",
					to_char(PS.Person_BirthDay, '{$this->dateTimeForm104}') as \"Person_BirthDay\"
				from
					v_PersonState PS
				where
					PS.Person_id = :Person_id
				limit 1
			";
		}

		$response = $this->getFirstRowFromQuery($query, $params);
		if (!is_array($response)) {
			return $this->createError('','Ошибка при получении данных пациента в регистре беременных');
		}

		// обновляем в анкете
		if (!empty($data['PersonRegister_id'])) {
			$this->db->query("
				update PersonRegister
				set
					EvnPS_id = dbo.GetPregnancyEvnPS(PersonRegister_id),
					PersonRegister_HighRiskDT = (case when cast(dbo.GetPregnancyRoute(PersonRegister_id, 2, RiskType_id) as bigint) > 2
						then dbo.tzgetdate()
						else null
					end),
					PersonRegister_ModerateRiskDT = (case when cast(dbo.GetPregnancyRoute(PersonRegister_id, 2, RiskType_id) as bigint) = 2
						then dbo.tzgetdate()
						else null
					end)
					where PersonRegister_id = :PersonRegister_id
			", [
				'PersonRegister_id' => $data['PersonRegister_id']
			]);
		}

		if (!empty($data['PersonRegister_id'])) {
			$this->db->query("
				update dbo.PersonPregnancy
				set
					RiskType_bid = dbo.GetPregnancy572NRisk(PersonRegister_id,2)::bigint,
					RiskType_did = dbo.GetPregnancyPRRisk(PersonRegister_id,2)::bigint
				where PersonRegister_id = :PersonRegister_id
			", array(
				'PersonRegister_id' => $data['PersonRegister_id']
			));
		}

		return array(array_merge($response, array('success' => true)));
	}
	
	/**
	 * Сохранение взаимосвязи регистра беременных и регистра ЭКО
	 */
	function savelinkEco($data) {

		$params = array('PersonPregnancy_id' => $data['PersonPregnancy_id'], 'PersonRegisterEco_id' => $data['PersonRegisterEco_id'], 'IsLink' => $data['IsLink']);
		$query = "
		    select 
		        Error_Code as \"Error_Code\",
		        Error_Message as \"Error_Msg\"
		    from p_Link_Eco_Pregnancy
		    (
		        PersonPregnancy_id := :PersonPregnancy_id,
				PersonRegisterEco_id := :PersonRegisterEco_id,
				IsLink := :IsLink
		    )
		";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('','Ошибка при удалении связи между регистром ЭКО и регистром беременных');
		}
		return $response;	
		
	}
	
	/**
     * Получение данных из раздела Анкеты для раздела Скрининга
     * @param $data
     * @return array|bool
     */
    public function getAnketaForScreen($data)
    {
        $params = ['PersonRegister_id' => $data['PersonRegister_id']];
        $query = "
			select
			    PersonPregnancy_Period as \"PersonPregnancy_Period\",
			    to_char(PersonPregnancy_setDT, {$this->dateTimeForm120}) as \"PersonPregnancy_setDT\"
			from
			    v_PersonPregnancy
			where
			    PersonRegister_id = :PersonRegister_id
			limit 1
		";
        return $this->getFirstRowFromQuery($query, $params, true);
    }

    /**
     * Получение идентификаторов ЛПУ с лицензией "акушерство и гинекология(использование ВРТ)"
     * @return string
     */
    public function getEcoLpuId()
    {
        $query = "
            select
                Lpu_id as \"Lpu_id\"
            from v_LpuLicence LpuLicence
                inner join fed.v_LpuLicenceProfile LLP on LLP.LpuLicence_id=LpuLicence.LpuLicence_id
                inner join fed.LpuLicenceProfileType LLPT on LLPT.LpuLicenceProfileType_id=LLP.LpuLicenceProfileType_id
            where
                LLPT.LpuLicenceProfileType_Code=201 and LpuLicence.LpuLicence_endDate is null
        ";
        $resp = $this->queryResult($query);

        $strLpuId = ',';
        foreach($resp as $item) {
            $strLpuId .= $item['Lpu_id'].',';
        }

        return $strLpuId;
    }

    /**
     * Список комбобокса Иное МО
     * @return array|bool
     */
    public function getDifferentLpu()
    {
        $params = [];
        $query = "
            select
                LpuDifferent_id as \"LpuDifferent_id\",
                LpuDifferent_Code as \"LpuDifferent_Code\",
                LpuDifferent_Name as \"LpuDifferent_Name\"
            from
                dbo.LpuDifferent
        ";

        $result = $this->db->query($query, $params);

        if (!is_object($result)) {
            return false;
        }
        
        return $result->result('array');
    }

    /**
     * Удаление Иное МО
     * @param $data
     * @return array|false
     */
    public function deleteDifferentLpu($data)
    {

        $params = array(
            'LpuDifferent_id' => $data['LpuDifferent_id']
        );

        $this->beginTransaction();
        //Проставляется исключение из региста с причиной "Другое"
        $query = "
            delete from dbo.LpuDifferent where LpuDifferent_id=:LpuDifferent_id
            returning 0 as \"Error_Code\", '' as \"Error_Msg\";
        ";
        $resp = $this->queryResult($query, $params);
        if (!$this->isSuccessful($resp)) {
            $this->rollbackTransaction();
            return $resp;
        }

        $this->commitTransaction();
        return [['success' => true]];
    }

	/**
	 * Формирование фильтра по полю Акушерское осложнение
	 */
	function getFilterObstetricComplication($value)
	{
		if (empty($value))
			return array('join' => '', 'filters' => '');

		if ($value == 1)
			return array(
				'join' => '
					left join lateral (
						select
							PC1.Preeclampsia_Code as Val
						from dbo.PregnancyQuestion PQ1
							inner join v_PregnancyScreen PS1 on PS1.PregnancyScreen_id = PQ1.PregnancyScreen_id
							inner join v_Preeclampsia PC1 on PC1.Preeclampsia_id = PQ1.PregnancyQuestion_ValuesStr
						where PQ1.PersonRegister_id = PR.PersonRegister_id and PQ1.QuestionType_id = 414 and PC1.Preeclampsia_Code = 5
						order by PS1.PregnancyScreen_setDT desc
						limit 1
					) PregnancyScreen on true
				',
				'filters' => '
					and PregnancyScreen.Val is not null
				'
			);

		if ($value == 2)
			return array(
				'join' => '
						left join lateral (
							select
								PQ1.PregnancyQuestion_IsTrue as Val
							from dbo.PregnancyQuestion PQ1
								inner join v_PregnancyScreen PS1 on PS1.PregnancyScreen_id = PQ1.PregnancyScreen_id
							where PQ1.PersonRegister_id = PR.PersonRegister_id and PQ1.QuestionType_id = 401 and PQ1.PregnancyQuestion_IsTrue = 2
							order by PS1.PregnancyScreen_setDT desc
							limit 1
						) PregnancyScreen on true
				',
				'filters' => '
					and PregnancyScreen.Val is not null
				'
			);

		if ($value == 3)
			return array(
				'join' => '
					left join lateral (
						select
							PQ2.PregnancyQuestion_ValuesStr as Val
						from v_PregnancyScreen PS1
							inner join v_PregnancyQuestion PQ1 on PQ1.PregnancyScreen_id=PS1.PregnancyScreen_id
							inner join v_PregnancyQuestion PQ2 on PQ2.PregnancyScreen_id=PS1.PregnancyScreen_id
						where PQ1.PersonRegister_id = PR.PersonRegister_id and PQ1.QuestionType_id in (395) and PQ2.QuestionType_id in (662,663,664,665)
							and PQ2.PregnancyQuestion_ValuesStr > 1 and (PQ1.PregnancyQuestion_IsTrue = 1 or PQ1.PregnancyQuestion_IsTrue is null)
						order by PS1.PregnancyScreen_setDT,PQ2.PregnancyQuestion_ValuesStr desc
						limit 1
					) PregnancyScreen on true
				',
				'filters' => '
					and PregnancyScreen.Val is not null
				'
			);

		if ($value == 4)
			return array(
				'join' => '
					left join lateral (
						select
							PQ1.PregnancyQuestion_IsTrue as Val
						from dbo.PregnancyQuestion PQ1
							inner join v_PregnancyScreen PS1 on PS1.PregnancyScreen_id = PQ1.PregnancyScreen_id
						where PQ1.PersonRegister_id = PR.PersonRegister_id and PQ1.QuestionType_id = 671 and PQ1.PregnancyQuestion_IsTrue = 2
						order by PS1.PregnancyScreen_setDT desc
						limit 1
					) PregnancyScreen on true
				',
				'filters' => '
					and PregnancyScreen.Val is not null
				'
			);

		if ($value == 5)
			return array(
				'join' => '
					left join v_PregnancyQuestion PQ1 on PQ1.PersonRegister_id = PR.PersonRegister_id and PQ1.QuestionType_id = 599
					left join v_PregnancyQuestion PQ2 on PQ2.PersonRegister_id = PR.PersonRegister_id and PQ2.QuestionType_id = 600
					left join v_PregnancyQuestion PQ3 on PQ3.PersonRegister_id = PR.PersonRegister_id and PQ3.QuestionType_id = 601
				',
				'filters' => '
					and PQ1.PregnancyQuestion_IsTrue = 2 and PQ2.PregnancyQuestion_ValuesStr is not null and PQ3.PregnancyQuestion_ValuesStr =2
				'
			);

		if ($value == 6)
			return array(
				'join' => '
					left join lateral (
						select
							PQ1.PregnancyQuestion_IsTrue as Val
						from dbo.PregnancyQuestion PQ1
							inner join v_PregnancyScreen PS1 on PS1.PregnancyScreen_id = PQ1.PregnancyScreen_id
						where PQ1.PersonRegister_id = PR.PersonRegister_id and PQ1.QuestionType_id = 764 and PQ1.PregnancyQuestion_IsTrue = 2
						order by PS1.PregnancyScreen_setDT desc
						limit 1
					) PregnancyScreen on true
				',
				'filters' => '
					and PregnancyScreen.Val is not null
				'
			);

		return array(
			'join' => '
				left join lateral (
					select
						1 as Val
					from
						v_PregnancyQuestion PQ
					where
						PQ.PersonRegister_id = PR.PersonRegister_id and PQ.QuestionType_id in (523,524,525) and PQ.PregnancyQuestion_IsTrue = 2
					order by PQ.QuestionType_id desc
					limit 1
				) PregnancyResult on true
				',
			'filters' => '
				and PregnancyResult.Val is not null
				'
		);
	}

    /**
     * @param $data
     */
    function checkAndSaveQuarantine($data) {
		
		$pp = $this->getFirstRowFromQuery("
			select PersonPregnancy_id as \"PersonPregnancy_id\", PersonPregnancy_IsInQuarantine as \"PersonPregnancy_IsInQuarantine\"
			from v_PersonPregnancy PP 
			inner join v_PersonRegister PR on PP.PersonRegister_id = PR.PersonRegister_id
			where 
				PP.Person_id = :Person_id
				and PR.PersonRegister_disDate is null
			order by PersonPregnancy_id desc
			limit 1
		", $data);
		
		if ($pp == false || empty($pp)) return;
		
		$chk = $this->getFirstResultFromQuery("
			with diags as (select Diag_id from Diag where Diag_Code in ('U07.1', 'U07.2', 'Z03.8', 'Z11.5', 'Z20.8', 'Z22.8')) 
			
			select 
				case 
					when exists (select EvnVizitPL_id from v_EvnVizitPL where Person_id = :Person_id and Diag_id in (select Diag_id from diags) limit 1) then 1 
					else 0 
				end +
				case 
					when exists (select EvnSection_id from v_EvnSection where Person_id = :Person_id and Diag_id in (select Diag_id from diags) limit 1) then 1 
					else 0 
				end +
				case 
					when exists (select PersonQuarantine_id from v_PersonQuarantine where Person_id = :Person_id limit 1) then 1 
					else 0 
				end 
			as dsign
		", $data);
		
		$PersonPregnancy_IsInQuarantine = $chk > 0 ? 2 : null;
		
		if ($PersonPregnancy_IsInQuarantine != $pp['PersonPregnancy_IsInQuarantine']) {
			
			$this->swUpdate('PersonPregnancy', [
				'PersonPregnancy_id' => $pp['PersonPregnancy_id'],
				'PersonPregnancy_IsInQuarantine' => $PersonPregnancy_IsInQuarantine,
				'pmUser_id' => $data['pmUser_id']
			]);
		}
    }

	/**проверка на связь анкеты по беременности с ТАП*/
	function checkLinkEvn($data){
		$query="select count(*)
 			from PersonPregnancy pp 
 			where pp.Evn_id in (select e.Evn_id from v_Evn e  where  :Evn_id in (e.Evn_id, e.Evn_pid,e.Evn_rid))";
		$params = array('Evn_id'=>$data['Evn_id']);
		$result = $this->getFirstResultFromQuery($query,$params);
		return $result;
	}
}

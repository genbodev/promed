<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Mse - контроллер для работы с медико-социальной экспертизой
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Mse
* @access       public
* @copyright    Copyright (c) 2011 Swan Ltd.
* @author       Dmitry Storozhev
* @version      11.10.2011
* @property Mse_model $dbmodel
*/

class Mse extends swController {

	public $inputRules = array(
		'searchData' => array(
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
				'label' => 'д/р',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'ЛПУ прикрепления',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedService_id',
				'label' => 'Ид. службы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnMse_setDT',
				'label' => 'Даты освидетельствования от – до',
				'rules' => '',
				'type' => 'daterange'
			),
			array(
				'field' => 'Diag_id',
				'label' => 'Диагноз МСЭ',
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
				'type' => 'int'
			),
			array(
				'default' => 'off',
				'field' => 'onlySQL',
				'label' => 'Вывести SQL-запрос',
				'rules' => '',
				'type' => 'string'
			)
		),
		'getEvnMse' => array(
			array(
				'field' => 'EvnMse_id',
				'label' => 'Ид. протокола МСЭ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPrescrMse_id',
				'label' => 'Ид. направления на МСЭ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnVK_id',
				'label' => 'Ид. протокола',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPL_id',
				'label' => 'Ид. ТАП',
				'rules' => '',
				'type' => 'id'
			)
		),	
		'saveEvnMse' => array(
			array(
				'field' => 'Server_id', 
				'label' => 'Идентификатор сервера', 
				'rules' => '', 
				'type' => 'int'
			),
			array(
				'field' => 'EvnMse_id',
				'label' => 'Ид. протокола МСЭ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedService_id',
				'label' => 'Ид. службы',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnMse_pid',
				'label' => '',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnMse_rid',
				'label' => '',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonEvn_id',
				'label' => '',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnMse_setDT',
				'label' => 'Дата создания протокола МСЭ',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnMse_disDT',
				'label' => 'Дата создания протокола МСЭ',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnMse_didDT',
				'label' => 'Дата создания протокола МСЭ',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnMse_Index',
				'label' => '',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnMse_Count',
				'label' => '',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnPrescrMse_id',
				'label' => 'Ид. направления на МСЭ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnVK_id',
				'label' => 'Ид. протокола',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnMse_NumAct',
				'label' => 'Номер акта медико-социальной экспертизы',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Diag_id',
				'label' => 'Код основного заболевания по МКБ',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Diag_sid',
				'label' => 'Сопутствующее заболевание',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Diag_aid',
				'label' => 'Осложнение основного заболевания',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'HealthAbnorm_id',
				'label' => 'Вид нарушения',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'HealthAbnormDegree_id',
				'label' => 'Степень выраженности',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'CategoryLifeType_id',
				'label' => 'Категория жизнедеятельности',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'CategoryLifeDegreeType_id',
				'label' => 'Степень выраженности',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'InvalidGroupType_id',
				'label' => 'Установлена инвалидность',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'InvalidCouseType_id',
				'label' => 'Причина инвалидности',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnMse_InvalidPercent',
				'label' => 'Степень утраты профессиональной трудоспособности в процентах',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ProfDisabilityPeriod_id',
				'label' => 'Срок, на который установлена степень утраты профессиональной трудоспособности',
				'rules' => '',
				'type' => 'id'
			),	
			array(
				'field' => 'EvnMse_ProfDisabilityStartDate',
				'label' => 'Дата, с которой установлена степень утраты профессиональной трудоспособности',
				'rules' => '',
				'type' => 'date'
			),	
			array(
				'field' => 'EvnMse_ProfDisabilityEndDate',
				'label' => 'Дата, до которой установлена степень утраты профессиональной трудоспособности',
				'rules' => '',
				'type' => 'date'
			),	
			array(
				'field' => 'EvnMse_ReExamDate',
				'label' => 'Дата переосвидетельствования',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'InvalidRefuseType_id',
				'label' => 'Причины отказа в установлении инвалидности',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnMse_SendStickDate',
				'label' => 'Дата отправки обратного талона',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnMse_HeadStaffMse',
				'label' => 'Руководитель бюро/экспертного состава, в котором проводилась медико-социальная экспертиза',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'MedServiceMedPersonal_id',
				'label' => 'Руководитель федерального государственного учреждения медико-социальной экспертизы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnMse_MedRecomm',
				'label' => 'Рекомендации по медицинской реабилитации',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnMse_ProfRecomm',
				'label' => 'Рекомендации по профессиональной, социальной, психолого-педагогической реабилитации',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'SopDiagList',
				'label' => 'Сопутствующие заболевания',
				'rules' => '',
				'type' => 'json_array'
			),
			array(
				'field' => 'OslDiagList',
				'label' => 'Осложнения основного заболевания',
				'rules' => '',
				'type' => 'json_array'
			),
			array(
				'field' => 'EvnMse_DiagDetail',
				'label' => 'Уточнение основного заболевания по МКБ',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnMse_DiagSDetail',
				'label' => 'Уточнение для сопутствующего заболевания по МКБ',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnMse_DiagADetail',
				'label' => 'Уточнение для осложнения основного заболевания по МКБ',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Diag_bid',
				'label' => 'Осложнение сопутствующего заболевания',
				'rules' => '',
			'type' => 'int'
			),
			array(
				'field' => 'EvnMse_DiagBDetail',
				'label' => 'Уточнение осложнения сопутствующего заболевания',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnMse_SendStickDetail',
				'label' => 'Уточнение причин отказа в установлении инвалидности',
				'rules' => '',
				'type' => 'string'
			),		
		),
		'getEvnStickOfYear' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Ид. Персона',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'saveEvnStick' => array(
			array(
				'field' => 'EvnStick_id',
				'label' => 'Ид. больн. листа',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Ид. персона',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStickClass',
				'label' => 'Тип больн. листа (общий/МСЭ)',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Diag_id',
				'label' => 'Ид. диагноза',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnMseStick_begDT',
				'label' => 'Дата начала',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnMseStick_endDT',
				'label' => 'Дата окончания',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnMseStick_StickNum',
				'label' => 'Номер ЛВН',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnMseStick_IsStick',
				'label' => 'ЭЛН',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'action',
				'label' => 'Экшн',
				'rules' => '',
				'type' => 'string'
			)
		),
		'saveEvnPrescrVK' => array(
			array(
				'field' => 'Server_id', 
				'label' => 'Идентификатор сервера', 
				'rules' => '', 
				'type' => 'int'
			),
			array(
				'field' => 'MedService_id', 
				'label' => 'Идентификатор службы', 
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPrescrVK_id',
				'label' => 'Ид. назначения на ВК',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPrescrVK_pid',
				'label' => 'ТАП/КВС',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPrescrVK_rid',
				'label' => '',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonEvn_id',
				'label' => '',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPrescrVK_setDT',
				'label' => '',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnPrescrVK_disDT',
				'label' => '',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnPrescrVK_didDT',
				'label' => '',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnPrescrVK_Index',
				'label' => '',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnPrescrVK_Count',
				'label' => '',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 1, //
				'field' => 'PrescriptionStatusType_id',
				'label' => '',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPrescrVK_Descr',
				'label' => '',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnPrescrVK_IsExec',
				'label' => '',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'TimetableGraf_id',
				'label' => '',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'TimetableMedService_id',
				'label' => '',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'CauseTreatmentType_id',
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
				'field' => 'EvnStick_id',
				'label' => '',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_sid',
				'label' => 'Врач, подписавший назначение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_sid',
				'label' => 'отделение врача, подписавшего назначение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_cid',
				'label' => 'Врач, отменивший назначение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_cid',
				'label' => 'отделение врача, отменившего назначение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPrescrVK_Note',
				'label' => 'Примечание',
				'rules' => 'max_length[100]',
				'type' => 'string'
			),
			array(
				'field' => 'EvnPrescrVK_LVN',
				'label' => 'ЛВН (ручной ввод)',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Lpu_gid',
				'label' => 'Направившее МО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPrescrMse_id',
				'label' => 'Направление на МСЭ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirectionHTM_id',
				'label' => 'Направление на ВМП',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnXml_id',
				'label' => 'Эпикриз',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PalliatQuestion_id',
				'label' => 'Анкета',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadEvnPrescrVKGrid' => array(
			array(
				'field' => 'MedService_id',
				'label' => 'Ид. службы',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Person_SurName',
				'label' => '',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_FirName',
				'label' => '',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_SecName',
				'label' => '',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'isEvnVK',
				'label' => '',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'isEvnPrescrMse',
				'label' => '',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'isEvnMse',
				'label' => '',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Person_BirthDay',
				'label' => '',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'begDate',
				'label' => '',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'endDate',
				'label' => '',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnStatus_id',
				'label' => '',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'CauseTreatmentType_id',
				'label' => '',
				'rules' => '',
				'type' => 'id'
			)
		),
		'setMseAppointDT' => array(
			array('field' => 'EvnPrescrMse_id', 'label' => 'Назначение на МСЭ', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnPrescrMse_appointDate', 'label' => 'Дата', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'EvnPrescrMse_appointTime', 'label' => 'Время', 'rules' => 'required', 'type' => 'time')
		),
		'setEpmMedService' => array(
			array('field' => 'EvnPrescrMse_id', 'label' => 'Назначение на МСЭ', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MedService_id', 'label' => 'Служба МСЭ', 'rules' => '', 'type' => 'id'),
		),
		'loadEvnPrescrMseGrid' => array(
			array(
				'field' => 'MedService_id',
				'label' => 'Ид. службы',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Person_SurName',
				'label' => '',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_FirName',
				'label' => '',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_SecName',
				'label' => '',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_BirthDay',
				'label' => '',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'Lpu_id',
				'label' => '',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MSEDirStatus_id',
				'label' => 'Статус направления',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'isEvnMse',
				'label' => '',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'begDate',
				'label' => '',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'endDate',
				'label' => '',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'Person_Snils',
				'label' => '',
				'rules' => '',
				'type' => 'string'
			),			
		),
		'loadEvnVKRejectGrid' => array(
			array(
				'field' => 'MedService_id',
				'label' => 'Ид. службы',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Person_SurName',
				'label' => '',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_FirName',
				'label' => '',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_SecName',
				'label' => '',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_BirthDay',
				'label' => '',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'Lpu_id',
				'label' => '',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'isEvnMse',
				'label' => '',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'onlyOwnLpu',
				'label' => 'Только свои МО',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'begDate',
				'label' => '',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'endDate',
				'label' => '',
				'rules' => '',
				'type' => 'date'
			)
		),
		'getOrgAddress' => array(
			array(
				'field' => 'Org_id',
				'label' => 'Организация',
				'rules' => '',
				'type' => 'id'
			)
		),
		'getPersonBodyData' => array(
			array('field' => 'PersonHeight_id', 'label' => '', 'rules' => '', 'type' => 'id'),
			array('field' => 'PersonWeight_id', 'label' => '', 'rules' => '', 'type' => 'id'),
			array('field' => 'Person_id', 'label' => '', 'rules' => 'required', 'type' => 'id'),
		),
		'defineActionForEvnPrescrMse' => array(
			array(
				'field' => 'EvnVK_id',
				'label' => 'Протокол ВК',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPrescrMse_id',
				'label' => 'Направление',
				'rules' => '',
				'type' => 'id'
			)
		),
		'savePersonBodyData' => array(
			array('field' => 'WeightAbnormType_id', 'label' => 'Тип отклонения (масса)', 'rules' => '', 'type' => 'id'),
			array('field' => 'WeightMeasureType_id', 'label' => 'Вид замера (масса)', 'rules' => '', 'type' => 'id'),
			array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => '', 'type' => 'id'),
			array('field' => 'PersonWeight_Weight', 'label' => 'Масса',	'rules' => 'required', 'type' => 'float'),
			array('field' => 'PersonWeight_id', 'label' => 'Идентификатор измерения массы пациента', 'rules' => '', 'type' => 'id'),
			array('field' => 'PersonWeight_IsAbnorm', 'label' => 'Отклонение (масса)', 'rules' => '', 'type' => 'id'),
			array('field' => 'PersonWeight_setDate', 'label' => 'Дата измерения (масса)', 'rules' => 'trim', 'type' => 'date'),
			array('field' => 'Server_id', 'label' => 'Идентификатор сервера', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'HeightAbnormType_id', 'label' => 'Тип отклонения (рост)', 'rules' => '', 'type' => 'id'),
			array('field' => 'HeightMeasureType_id', 'label' => 'Вид замера (рост)', 'rules' => '', 'type' => 'id'),
			array('field' => 'PersonHeight_Height', 'label' => 'Рост', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'PersonHeight_id', 'label' => 'Идентификатор измерения роста пациента', 'rules' => '', 'type' => 'id'),
			array('field' => 'PersonHeight_IsAbnorm', 'label' => 'Отклонение (рост)', 'rules' => '', 'type' => 'id'),
			array('field' => 'PersonHeight_setDate', 'label' => 'Дата измерения (рост)', 'rules' => '', 'type' => 'date')
		),
		'saveEvnPrescrMse' => array(
			array('field' => 'Server_id', 'label' => 'Идентификатор сервера', 'rules' => '', 'type' => 'int'),
			array('field' => 'EvnPrescrMse_id', 'label' => 'Назначение на МСЭ', 'rules' => '', 'type' => 'id'),
			array('field' => 'ARMType', 'label' => 'АРМ', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnStatus_id', 'label' => 'Статус', 'rules' => '', 'type' => 'id'),
			array('field' => 'TimetableMedService_id', 'label' => 'бирка', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedService_id', 'label' => 'бирка', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPrescrMse_pid', 'label' => '', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPrescrMse_rid', 'label' => '', 'rules' => '', 'type' => 'id'),
            array('field' => 'PersonEvn_id', 'label' => '', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPrescrMse_issueDT', 'label' => '', 'rules' => '', 'type' => 'date'),
			array('field' => 'EvnPrescrMse_setDT', 'label' => '', 'rules' => '', 'type' => 'date'),
			array('field' => 'EvnPrescrMse_disDT', 'label' => '', 'rules' => '', 'type' => 'date'),
			array('field' => 'EvnPrescrMse_didDT', 'label' => '', 'rules' => '', 'type' => 'date'),
			array('field' => 'EvnPrescrMse_Index', 'label' => '', 'rules' => '', 'type' => 'int'),
			array('field' => 'EvnPrescrMse_Count', 'label' => '', 'rules' => '', 'type' => 'int'),
			array('field' => 'PrescriptionStatusType_id', 'label' => 'Статус назначения', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPrescrMse_Descr', 'label' => '', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnPrescrMse_IsExec', 'label' => '', 'rules' => '', 'type' => 'int'),
			array('field' => 'TimetableGraf_id', 'label' => '', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnVK_id', 'label' => 'Протокол ВК', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPrescrMse_IsFirstTime', 'label' => 'Направляется (Первично / Повторно)', 'rules' => '', 'type' => 'int'),
            array('field' => 'Person_sid', 'label' => 'Законный представитель', 'rules' => '', 'type' => 'id'),
			array('field' => 'InvalidGroupType_id', 'label' => 'Инвалидность', 'rules' => '', 'type' => 'id', 'default' => 1 ),
			array('field' => 'EvnPrescrMse_InvalidPercent', 'label' => 'Степень утраты профессиональной трудоспособности в процентах', 'rules' => '', 'type' => 'int'),
			array('field' => 'EvnPrescrMse_IsWork', 'label' => 'Работает (Да/Нет)', 'rules' => '', 'type' => 'int'),
			array('field' => 'Post_id', 'label' => 'Должность', 'rules' => '', 'type' => 'id'),
			array('field' => 'PostNew', 'label' => 'Новая должность', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'EvnPrescrMse_ExpPost', 'label' => 'Стаж работы по должности (лет)', 'rules' => '', 'type' => 'int'),
			//array('field' => 'Okved_id', 'label' => 'Профессия', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPrescrMse_Prof', 'label' => 'Профессия', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnPrescrMse_ExpProf', 'label' => 'Стаж работы по профессии (лет)', 'rules' => '', 'type' => 'int'),
			array('field' => 'EvnPrescrMse_Spec', 'label' => 'Специальность', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnPrescrMse_ExpSpec', 'label' => 'Стаж работы по специальности (лет)', 'rules' => '', 'type' => 'int'),
			array('field' => 'EvnPrescrMse_Skill', 'label' => 'Квалификация', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnPrescrMse_ExpSkill', 'label' => 'Стаж работы по квалификации (лет)', 'rules' => '', 'type' => 'int'),
			array('field' => 'Org_id', 'label' => 'Наименование организации', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPrescrMse_CondWork', 'label' => 'Условия и характер выполняемого труда', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnPrescrMse_MainProf', 'label' => 'Основная профессия (специальность)', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnPrescrMse_MainProfSkill', 'label' => 'Квалификация по основной профессии', 'rules' => '', 'type' => 'string'),
			array('field' => 'Org_did', 'label' => 'Наименование учреждения (учеба)', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPrescrMse_Dop', 'label' => 'Дополнительно', 'rules' => '', 'type' => 'string'),
			array('field' => 'LearnGroupType_id', 'label' => 'Группа / Класс / Курс', 'rules' => '', 'type' => 'id'),
			//array('field' => 'Okved_did', 'label' => 'Профессия (специальность)', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPrescrMse_ProfTraining', 'label' => 'Профессия (специальность)', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnPrescrMse_OrgMedDateYear', 'label' => 'Наблюдается в организациях, оказывающих лечебно-профилактическую помощь с (год)', 'rules' => '', 'type' => 'int'),
			array('field' => 'EvnPrescrMse_OrgMedDateMonth', 'label' => 'Наблюдается в организациях, оказывающих лечебно-профилактическую помощь с (месяц)', 'rules' => '', 'type' => 'int'),
			array('field' => 'EvnPrescrMse_DiseaseHist', 'label' => 'История заболевания', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnPrescrMse_LifeHist', 'label' => 'Анамнез жизни', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnPrescrMse_MedRes', 'label' => 'Рез-ты проведенных меропр', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnPrescrMse_State', 'label' => 'Состояние гражданина при направлении на медико-социальную экспертизу', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnPrescrMse_DopRes', 'label' => 'Результаты дополнительных методов исследования', 'rules' => '', 'type' => 'string'),
			array('field' => 'PersonWeight_id', 'label' => '', 'rules' => '', 'type' => 'id'),
			array('field' => 'PersonHeight_id', 'label' => '', 'rules' => '', 'type' => 'id'),
			array('field' => 'StateNormType_id', 'label' => 'Оценка психофизиологической выносливости', 'rules' => '', 'type' => 'id'),
			array('field' => 'StateNormType_did', 'label' => 'Оценка эмоциональной устойчивости', 'rules' => '', 'type' => 'id'),
			array('field' => 'Diag_id', 'label' => 'Код основного заболевания по МКБ', 'rules' => '', 'type' => 'id'),
			array('field' => 'Diag_sid', 'label' => 'Сопутствующее заболевание', 'rules' => '', 'type' => 'id'),
			array('field' => 'Diag_aid', 'label' => 'Осложнение основного заболевания', 'rules' => '', 'type' => 'id'),
			array('field' => 'MseDirectionAimType_id', 'label' => 'Цель направления на медико-социальную экспертизу', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPrescrMse_AimMseOver', 'label' => 'Другая цель', 'rules' => '', 'type' => 'string'),
			array('field' => 'ClinicalForecastType_id', 'label' => 'Клинический прогноз', 'rules' => '', 'type' => 'id'),
			array('field' => 'ClinicalPotentialType_id', 'label' => 'Реабилитационный потенциал', 'rules' => '', 'type' => 'id'),
			array('field' => 'ClinicalForecastType_did', 'label' => 'Реабилитационный прогноз', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPrescrMse_Recomm', 'label' => 'Рекомендуемые меропр. по мед. реабилитации', 'rules' => '', 'type' => 'string'),
			array('field' => 'MedPersonal_sid', 'label' => 'Врач-пользователь, подписавший назначение', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_sid', 'label' => 'Отделение Врач-пользователь, подписавший назначение', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedPersonal_cid', 'label' => 'Врач-пользователь, отменивший назначение', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_cid', 'label' => 'Отделение Врач-пользователь, отменивший назначение', 'rules' => '', 'type' => 'id'),
			array('field' => 'MilitaryKind_id', 'label' => 'Отношение к военной службе', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPrescrMse_IsCanAppear', 'label' => '', 'rules' => '', 'type' => 'id'),
			array('field' => 'Org_sid', 'label' => '', 'rules' => '', 'type' => 'id'),
			array('field' => 'Org_gid', 'label' => '', 'rules' => '', 'type' => 'id'),
			array('field' => 'OAddress_id','label' => 'Адрес (идентификатор)','rules' => '','type' => 'string'),
			array('field' => 'OAddress_Zip','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'OKLCountry_id','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'OKLRGN_id','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'OKLSubRGN_id','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'OKLCity_id','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'OKLTown_id','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'OKLStreet_id','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'OAddress_House','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'OAddress_Corpus','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'OAddress_Flat','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'OAddress_Address','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'EAddress_id','label' => 'Адрес (идентификатор)','rules' => '','type' => 'string'),
			array('field' => 'EAddress_Zip','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'EKLCountry_id','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'EKLRGN_id','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'EKLSubRGN_id','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'EKLCity_id','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'EKLTown_id','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'EKLStreet_id','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'EAddress_House','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'EAddress_Corpus','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'EAddress_Flat','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'EAddress_Address','label' => 'Адрес (служебное поле)','rules' => '','type' => 'string'),
			array('field' => 'EvnPrescrMse_IsPersonInhabitation', 'label' => '', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPrescrMse_IsPalliative', 'label' => '', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnMse_id', 'label' => '', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPrescrMse_InvalidDate', 'label' => 'Дата установления инвалидности', 'rules' => '', 'type' => 'date'),
			array('field' => 'InvalidPeriodType_id', 'label' => 'Срок, на который установлена инвалидность', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPrescrMse_InvalidEndDate', 'label' => '', 'rules' => '', 'type' => 'date'),
			array('field' => 'EvnPrescrMse_InvalidPeriod', 'label' => '', 'rules' => '', 'type' => 'int'),
			array('field' => 'InvalidCouseType_id', 'label' => '', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPrescrMse_InvalidCouseAnother', 'label' => '', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnPrescrMse_InvalidCouseAnotherLaw', 'label' => '', 'rules' => '', 'type' => 'string'),
			array('field' => 'ProfDisabilityPeriod_id', 'label' => '', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPrescrMse_ProfDisabilityEndDate', 'label' => '', 'rules' => '', 'type' => 'date'),
			array('field' => 'EvnPrescrMse_ProfDisabilityAgainPercent', 'label' => '', 'rules' => '', 'type' => 'string'),
			array('field' => 'DocumentAuthority_id', 'label' => '', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPrescrMse_DocumentSer', 'label' => '', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnPrescrMse_DocumentNum', 'label' => '', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnPrescrMse_DocumentIssue', 'label' => '', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnPrescrMse_DocumentDate', 'label' => '', 'rules' => '', 'type' => 'date'),
			array(
				'field' => 'withCreateDirection',
				'label' => 'Надо ли создавать направление',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSectionProfile_id',
				'label' => 'Профиль направления',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPrescrMse_MainDisease',
				'label' => 'Основное заболевание',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'SopDiagList',
				'label' => 'Сопутствующие заболевания',
				'rules' => '',
				'type' => 'json_array'
			),
			array(
				'field' => 'OslDiagList',
				'label' => 'Осложнения основного заболевания',
				'rules' => '',
				'type' => 'json_array'
			),
			array(
				'field' => 'filesVK',
				'label' => 'Файлы',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'filesMSE',
				'label' => 'Файлы',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Lpu_gid',
				'label' => 'Направившее МО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnQueue_id',
				'label' => 'Очередь',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MeasuresForMedicalRehabilitation',
				'label' => 'Результаты проведенных мероприятий по медицинской реабилитации в соответствии с индивидуальной программой реабилитации инвалида',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'UslugaComplexMSEData',
				'label' => 'Обследования и исследования',
				'rules' => '',
				'type' => 'json_array'
			),
			array(
				'field' => 'Aims',
				'label' => 'Цели',
				'rules' => '',
				'type' => 'json_array'
			),
			array(
				'field' => 'EvnPrescrMse_MeasureSurgery',
				'label' => '',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EvnPrescrMse_MeasureProstheticsOrthotics',
				'label' => '',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EvnPrescrMse_HealthResortTreatment',
				'label' => '',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'MeasuresRehabEffect_id',
				'label' => '',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'IPRARegistry_id',
				'label' => '',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MeasuresRehabEffect_IsRecovery',
				'label' => '',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'IPRAResult_rid',
				'label' => '',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MeasuresRehabEffect_IsCompensation',
				'label' => '',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'IPRAResult_cid',
				'label' => '',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MeasuresRehabEffect_Comment',
				'label' => '',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'PhysiqueType_id',
				'label' => '',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPrescrMse_DailyPhysicDepartures',
				'label' => '',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'EvnPrescrMse_Waist',
				'label' => '',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'EvnPrescrMse_Hips',
				'label' => '',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'EvnPrescrMse_WeightBirth',
				'label' => '',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'EvnPrescrMse_PhysicalDevelopment',
				'label' => '',
				'rules' => 'trim',
				'type' => 'string'
			),
			array('field' => 'ignorePersonDocumentCheck', 'label' => '', 'rules' => '', 'type' => 'int'),
			array('field' => 'ignoreRequiredSetOfStudiesCheck', 'label' => '', 'rules' => '', 'type' => 'int')
		),
		'getEvnPrescrMse' => array(
			array(
				'field' => 'EvnVK_id',
				'label' => 'Протокол ВК',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPrescrMse_id',
				'label' => 'Направление',
				'rules' => '',
				'type' => 'id'
			)
		),
		'defineEvnMseFormParams' => array(
			array(
				'field' => 'EvnMse_id',
				'label' => 'Протокол МСЭ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPrescrMse_id',
				'label' => 'Направление на МСЭ',
				'rules' => '',
				'type' => 'id'
			)
		),
		'getEvnPLXmlData' => array(
			array(
				'field' => 'EvnPL_id',
				'label' => 'ТАП',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'printEvnMse' => array(
			array(
				'field' => 'EvnMse_id',
				'label' => 'Протокол МСЭ',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'isMseDepers',
				'label' => 'Признак деперсонализации',
				'rules' => '',
				'type' => 'id'
			)
		),
		'printEvnPrescrMse' => array(
			array(
				'field' => 'EvnPrescrMse_id',
				'label' => 'Направление на МСЭ',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'clearTimeMSOnEvnPrescrVK' => array(
			array(
				'field' => 'EvnPrescrVK_id',
				'label' => 'Ид. направления на ВК',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'TimetableMedService_id',
				'label' => 'Ид. бирки',
				'rules' => '',
				'type' => 'id'
			)
		),
		'clearTimeMSOnEvnPrescrMse' => array(
			array(
				'field' => 'EvnPrescrMse_id',
				'label' => 'Ид. направления на МСЭ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'TimetableMedService_id',
				'label' => 'Ид. бирки',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getPersonJobData' => array(
			array(
				'field' => 'Person_id',
				'label' => '',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Server_id',
				'label' => '',
				'rules' => 'required',
				'type' => 'int'
			)
		),
		'getDeputyKind' => array(
			array(
				'field' => 'Person_id',
				'label' => '',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'saveDeputyKind' => array(
			array(
				'field' => 'Person_id',
				'label' => '',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Person_pid',
				'label' => '',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'DeputyKind_id',
				'label' => '',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonDeputy_id',
				'label' => '',
				'rules' => '',
				'type' => 'id'
			)
		),
		'deleteDeputyKind' => array(
			array(
				'field' => 'Person_id',
				'label' => '',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadEvnPrescrJournalGrid' => array(
			array(
				'field' => 'MedPersonal_id', // Направивший врач
				'label' => '',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'ARMType',
				'label' => '',
				'rules' => 'required',
				'type' => 'string'
			),
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
				'label' => 'Д/Р',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'isEvnVK',
				'label' => 'Создать протокол ВК или нет',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'isEvnMSE',
				'label' => 'Создать протокол МСЭ или нет',
				'rules' => '',
				'type' => 'int'
			)
		),
		'cancelEvnPrescr' => array(
			array(
				'field' => 'PrescrNick',
				'label' => 'Тип направления',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'EvnPrescrObj_id',
				'label' => 'Направление',
				'rules' => 'required',
				'type' => 'int'
			)
		),
		'deleteEvnMse' => array(
			array(
				'field' => 'EvnMse_id',
				'label' => 'Идентификатор талона',
				'rules' => 'required',
				'type' => 'string'
			)
		),
		'getEvnPrescrMseStatusHistory' => array(
			array(
				'field' => 'EvnPrescrMse_id',
				'label' => 'Идентификатор направления',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'setEvnVKIsFail' => array(
			array(
				'field' => 'EvnVK_id',
				'label' => 'Протокол ВК',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnVK_IsFail',
				'label' => '',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadCategoryLifeTypeLinkList' => array(
			array(
				'field' => 'CategoryLifeType_id',
				'label' => 'Категория жизнедеятельности',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadEvnMseCategoryLifeTypeLink' => array(
			array(
				'field' => 'EvnMse_id',
				'label' => 'Протокол МСЭ',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'saveEvnMseCategoryLifeType' => array(
			array(
				'field' => 'EvnMseCategoryLifeTypeLink_id',
				'label' => 'Идентификатор',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnMse_id',
				'label' => 'Протокол МСЭ',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'CategoryLifeType_id',
				'label' => 'Категория жизнедеятельности',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'CategoryLifeTypeLink_id',
				'label' => 'Степень выраженности',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'searchEvnPrescrMse' => [
			[ 'field' => 'Person_SurName', 'label' => 'Фамилия', 'rules' => 'trim', 'type' => 'string' ],
			[ 'field' => 'Person_FirName', 'label' => 'Имя', 'rules' => 'trim', 'type' => 'string' ],
			[ 'field' => 'Person_SecName', 'label' => 'Отчество', 'rules' => 'trim', 'type' => 'string' ],
			[ 'field' => 'Person_BirthDay', 'label' => 'Д/р', 'rules' => 'trim', 'type' => 'date' ],
			[ 'field' => 'EvnPrescrVK_Status', 'label' => 'Направление на ВК', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'Diag_id', 'label' => 'Диагноз', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'EvnPrescrMse_issueDT', 'label' => 'Даты направления', 'rules' => '', 'type' => 'daterange' ],
			[ 'field' => 'EvnStatus_id', 'label' => 'Статус направления', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'EvnDirection_Num', 'label' => 'Номер направления', 'rules' => 'trim', 'type' => 'string' ],
			[ 'default' => 0, 'field' => 'start', 'label' => 'Номер стартовой записи', 'rules' => '', 'type' => 'int' ],
			[ 'default' => 100, 'field' => 'limit', 'label' => 'Количество записей', 'rules' => '', 'type' => 'int' ],
		],
		'checkEvnPrescrMseExists' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Человек',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'getEvnPrescrMseList' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Человек',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPrescrMse_id',
				'label' => 'Идентификатор направления',
				'rules' => '',
				'type' => 'id'
			)
		),
		'getEvnDirectionHTMList' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Человек',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirectionHTM_id',
				'label' => 'Идентификатор направления',
				'rules' => '',
				'type' => 'id'
			)
		),
		'getEvnPrescrMseData' => array(
			array(
				'field' => 'EvnPrescrVK_id',
				'label' => 'Идентификатор направления',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'updateEvnPrescrMseStatus' => array(
			array(
				'field' => 'Evn_id',
				'label' => 'Идентификатор события',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStatus_SysNick',
				'label' => 'Статус события',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'EvnStatusHistory_Cause',
				'label' => 'Причина отказа',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'MedService_id',
				'label' => 'Идентификатор службы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Идентификатор сотрудника службы',
				'rules' => '',
				'type' => 'id'
			)
		),
		'exportEvnPrescrMse' => array(
			array(
				'field' => 'Lpu_oid',
				'label' => 'Идентификатор МО',
				'rules' => '',
				'type' => 'id'
			),array(
				'field' => 'MedService_id',
				'label' => 'Идентификатор службы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStatus_id',
				'label' => 'Идентификатор статуса события',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'ExportDateRange',
				'label' => 'Период',
				'rules' => 'required',
				'type' => 'daterange'
			),
			array(
				'field' => 'ExportAllRecords',
				'label' => 'Учитывать направления, выгруженные ранее',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'ARMType',
				'label' => 'АРМ',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'G_CODE',
				'label' => 'Код направления',
				'rules' => '',
				'type' => 'string'
			)
		),
		'importEvnMse' => array(
			array(
				'field' => 'Lpu_oid',
				'label' => 'Идентификатор МО',
				'rules' => '',
				'type' => 'id'
			)
		),
		'searchUslugaComplexMSE' =>array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_id',
				'label' => 'Идентификатор диагноза',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplex_id',
				'label' => 'Идентификатор услуги',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnUsluga_DateRange',
				'label' => 'Период дат услуги',
				'rules' => '',
				'type' => 'daterange'
			),
			array(
				'field' => 'RecommendedOnly',
				'label' => 'Только рекомендованные',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'AllDiag',
				'label' => 'Все диагнозы',
				'rules' => '',
				'type' => 'checkbox'
			),
			array('field' => 'EvnPrescrMse_IsFirstTime', 'label' => 'Направляется (Первично / Повторно)', 'rules' => '', 'type' => 'int'),
		),
		'getUslugaComplexMSERecommended' =>array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_id',
				'label' => 'Идентификатор диагноза',
				'rules' => '',
				'type' => 'id'
			),
			array('field' => 'EvnPrescrMse_IsFirstTime', 'label' => 'Направляется (Первично / Повторно)', 'rules' => '', 'type' => 'int'),
		),
		'loadUslugaComplexMSEList' =>array(
			array(
				'field' => 'EvnPrescrMse_id',
				'label' => 'Идентификатор направления',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadMultiplePrescrAims' =>array(
			array(
				'field' => 'EvnPrescrMse_id',
				'label' => 'Идентификатор направления',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getIPRAData' =>array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getPrevEvnMseList' =>array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'checkPersonDocument' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Человек',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'completenessTestMSE' => array(
			array(
				'field' => 'EvnPrescrMse_id',
				'label' => 'направление МСЭ',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'loadVKJournalGrid' => [
			[
				'field' => 'begDate',
				'label' => 'Дата начала',
				'rules' => 'required',
				'type' => 'date'
			], [
				'field' => 'endDate',
				'label' => 'Дата окончания',
				'rules' => 'required',
				'type' => 'date'
			], [
				'field' => 'Person_SurName',
				'label' => 'Фамилия',
				'rules' => '',
				'type' => 'string'
			], [
				'field' => 'Person_FirName',
				'label' => 'Имя',
				'rules' => '',
				'type' => 'string'
			], [
				'field' => 'Person_SecName',
				'label' => 'Отчество',
				'rules' => '',
				'type' => 'string'
			], [
				'field' => 'Person_BirthDay_From',
				'label' => 'Дата рождения с',
				'rules' => '',
				'type' => 'date'
			], [
				'field' => 'Person_BirthDay_To',
				'label' => 'Дата рождения по',
				'rules' => '',
				'type' => 'date'
			], [
				'field' => 'CauseTreatmentType_id',
				'label' => 'Причина обращения',
				'rules' => '',
				'type' => 'id'
			], [
				'field' => 'EvnStatus_id',
				'label' => 'Статус направления',
				'rules' => '',
				'type' => 'id'
			], [
				'field' => 'LpuSection_id',
				'label' => 'Отделение',
				'rules' => '',
				'type' => 'id'
			], [
				'field' => 'MedStaffFact_id',
				'label' => 'Врач',
				'rules' => '',
				'type' => 'id'
			], [
				'default' => 0,
				'field' => 'start',
				'label' => 'Номер стартовой записи',
				'rules' => '',
				'type' => 'int'
			], [
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество записей',
				'rules' => '',
				'type' => 'int'
			]
		],
		'loadEvnPrescrVKWindow' => [
			[
				'field' => 'EvnPrescrVK_id',
				'label' => 'Идентификатор направления на ВК',
				'rules' => 'required',
				'type' => 'id'
			]
		],
		'loadEvnPrescrVKStatusGrid' => [
			[
				'field' => 'EvnPrescrVK_id',
				'label' => 'Идентификатор направления на ВК',
				'rules' => 'required',
				'type' => 'id'
			]
		]
	);
	
	private $inputData = array();

	/**
	 *	Method description
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('Mse_model', 'dbmodel');
	}
	
	/**
	 *	Method description
	 */
	function searchData()
	{
		$data = $this->ProcessInputData('searchData', false, false, false, false, true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->searchData($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 *	Сохранение данных по протоколу МСЭ
	 */
	function saveEvnMse()
	{
		$data = $this->ProcessInputData('saveEvnMse', true);
		if($data){
			$response = $this->dbmodel->saveEvnMse($data);
			$this->ProcessModelSave($response)->ReturnData();
			
			if ( !empty($response[0]['Error_Msg']) ) {
				return;
			}
			
			// Генерируем уведомление
			$this->genNotice(
				array(
					'object' => 'EvnMse',
					'object_id' => $response[0]['EvnMse_id'],
					'action' => ( !empty($data['EvnMse_id']) ) ? 'upd' : 'ins',
					'Lpu_id' => $data['Lpu_id'],
					'pmUser_id' => $data['pmUser_id']
				)
			);
		}
	}

	/**
	 *	Удаление данных по протоколу МСЭ
	 */
	function deleteEvnMse()
	{
		$data = $this->ProcessInputData('deleteEvnMse', true);
		if($data){
			$response = $this->dbmodel->deleteEvnMse($data);
			$this->ProcessModelSave($response)->ReturnData();
		}
	}
	
	/**
	 *	Чтение данных по протоколу МСЭ
	 *	input: EvnPrescrMse_id, EvnMse_id
	 */
	function getEvnMse()
	{
		$data = $this->ProcessInputData('getEvnMse', true);
		if($data){
			$response = $this->dbmodel->getEvnMse($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}
	
	/**
	 *	Чтение данных по временной нетрудоспособности (за последние 12 месяцев)
	 */
	function getEvnStickOfYear()
	{
		$val = array();
		$data = $this->ProcessInputData('getEvnStickOfYear', true);
		if($data){
			$response = $this->dbmodel->getEvnStickOfYear($data);
			if(is_array($response) && count($response)>0){
				$i = 1;
				foreach($response as $row) {
					$row['num'] = $i;
					$val[] = $row;
					$i++;
				}
			}
			$this->ProcessModelList($val, true, true)->ReturnData();
		}
	}
	
	/**
	 *	Сохранение данных по временной нетрудоспособности (ветка после else пока что не используется и скорее всего не будет использоваться)
	 */
	function saveEvnStick()
	{
		$data = $this->ProcessInputData('saveEvnStick', true);
		if($data['EvnStickClass'] == 'EvnMseStick') {
			$data['EvnMseStick_id'] = $data['EvnStick_id'];
			$response = $this->dbmodel->saveEvnMseStick($data);
			$this->ProcessModelSave($response)->ReturnData();
		} else {
			$stickData = $this->dbmodel->getEvnStick($data);
			if(is_array($stickData) && $stickData[0]){
				$stickData = $stickData[0];
				$stickData['pmUser_id'] = $data['pmUser_id'];
				$stickData['EvnStick_setDT'] = $data['EvnMseStick_begDT'];
				$stickData['EvnStick_disDT'] = $data['EvnMseStick_endDT'];
				$stickData['Diag_pid'] = $data['Diag_id'];
				@$response = $this->dbmodel->saveEvnStick($stickData);
				$this->ProcessModelSave($response)->ReturnData();
			}
		}
	}
	
	/**
	 *	Сохранение данных по направлению на ВК
	 */
	function saveEvnPrescrVK()
	{
		$data = $this->ProcessInputData('saveEvnPrescrVK', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->saveEvnPrescrVK($data);
		$this->ProcessModelSave($response)->ReturnData();
		
		if ( !empty($response[0]['Error_Msg']) ) {
			return false;
		}
		
		// Генерируем уведомление
		$this->genNotice(
			array(
				'object' => 'EvnPrescrVK',
				'object_id' => $response[0]['EvnPrescrVK_id'],
				'action' => ( !empty($data['EvnPrescrVK_id']) ) ? 'upd' : 'ins',
				'Lpu_id' => $data['Lpu_id'],
				'pmUser_id' => $data['pmUser_id']
			)
		);
	}
	
	/**
	 *	Освобождение записи (удаление привязки направления на ВК к бирке, пока что направление также удаляется)
	 */
	function clearTimeMSOnEvnPrescrVK()
	{
		$data = $this->ProcessInputData('clearTimeMSOnEvnPrescrVK', true);
		if (empty($data['EvnPrescrVK_id']) && empty($data['TimetableMedService_id'])) {
			DieWithError('Не выбрана запись');
			return false;
		}
		if($data) {
			$this->load->model('TimetableMedService_model', 'ttms_model');
			$response = $this->dbmodel->clearTimeMSOnEvnPrescrVK($data);
			if(!is_array($response) || strlen($response[0]['Error_Msg']) > 0){
				DieWithError('При изменении данных произошла ошибка');
				return false;
			}
			if (!empty($data['TimetableMedService_id'])) {
				$response = $this->ttms_model->Clear(array(
					'object' => 'TimetableMedService',
					'pmUser_id' => $data['pmUser_id'],
					'session' => $data['session'],
					'TimetableMedService_id' => $data['TimetableMedService_id']
				));
			}
			$this->ProcessModelSave($response)->ReturnData();
		}
	}
	
	/**
	 *	Освобождение записи (удаление привязки направления на МСЭ к бирке, пока что направление также удаляется)
	 */
	function clearTimeMSOnEvnPrescrMse()
	{
		$data = $this->ProcessInputData('clearTimeMSOnEvnPrescrMse', true);
		if($data) {
			$this->load->model('TimetableMedService_model', 'ttms_model');
			$response = $this->dbmodel->clearTimeMSOnEvnPrescrMse($data);
			if(!is_array($response) || strlen($response[0]['Error_Msg']) > 0){
				DieWithError('При изменении данных произошла ошибка');
				return false;
			}
			$data['object']='TimetableMedService';
			$response = $this->ttms_model->Clear($data);
			$this->ProcessModelSave($response)->ReturnData();
		}
	}
	
	
	
	/**
	 *	Загрузка грида с направлениями на ВК (АРМ ВК)
	 */
	function loadEvnPrescrVKGrid()
	{
		$data = $this->ProcessInputData('loadEvnPrescrVKGrid', true);
		if($data){
			$response = $this->dbmodel->loadEvnPrescrVKGrid($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}
	
	/**
	 *	Загрузка грида с направлениями на МСЭ (АРМ МСЭ)
	 */
	function loadEvnPrescrMseGrid()
	{
		$data = $this->ProcessInputData('loadEvnPrescrMseGrid', false);
		if($data){
			$response = $this->dbmodel->loadEvnPrescrMseGrid($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}

	/**
	 *	Загрузка журнала отказов
	 */
	function loadEvnVKRejectGrid()
	{
		$sp = getSessionParams();
		$data = $this->ProcessInputData('loadEvnVKRejectGrid', false);
		if($data){
			$data['myLpu_id'] = $sp['Lpu_id'];
			$response = $this->dbmodel->loadEvnVKRejectGrid($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}

	/**
	 *	Установка даты проведения
	 */
	function setMseAppointDT()
	{
		$data = $this->ProcessInputData('setMseAppointDT', true);
		if ($data) {
			$response = $this->dbmodel->setMseAppointDT($data);
			$this->ProcessModelSave($response, true)->ReturnData();
		}
	}

	/**
	 *	Установка службы
	 */
	function setEpmMedService()
	{
		$data = $this->ProcessInputData('setEpmMedService', true);
		if ($data) {
			$response = $this->dbmodel->setEpmMedService($data);
			$this->ProcessModelSave($response, true)->ReturnData();
		}
	}
	
	/**
	 *	Определение адреса организации
	 *	input: Org_id
	 */
	function getOrgAddress()
	{
		$data = $this->ProcessInputData('getOrgAddress', true);
		if($data){
			$response = $this->dbmodel->getOrgAddress($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}
	
	/**
	 *	Чтение данных по росту и весу человека (последний замер)
	 */
	function getPersonBodyData()
	{
		$data = $this->ProcessInputData('getPersonBodyData', true);
		if($data){
			$response = $this->dbmodel->getPersonBodyData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}
	
	/**
	 *	Чтение данных по актуальному месту работы человека
	 */
	function getPersonJobData()
	{
		$data = $this->ProcessInputData('getPersonJobData', true);
		if($data){
			$response = $this->dbmodel->getPersonJobData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}
	
	
	/**
	 *	Определение параметров формы "Направление на МСЭ" ( если протокол МСЭ выписан для данного направления, то форму открывать на просмотр )
	 */
	function defineActionForEvnPrescrMse()
	{
		$data = $this->ProcessInputData('defineActionForEvnPrescrMse', true);
		if($data){
			$response = $this->dbmodel->defineActionForEvnPrescrMse($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}
	
	/**
	 *	Несмотря на то что редактировать персданные из назначения на МСЭ это 3.14здец, однако надо значит надо (_!_)
	 */
	function savePersonBodyData()
	{
		$this->load->model('PersonHeight_model', 'h_model');
		$this->load->model('PersonWeight_model', 'w_model');
		$data = $this->ProcessInputData('savePersonBodyData', true);
		if ($data === false) { return false; }

		// Сохраняем данные по росту
		$response1 = $this->h_model->savePersonHeight($data);
		if(strlen($response1[0]['Error_Msg']) > 0){
			DieWithError('При сохранении данных произошла ошибка');
			return false;
		}
		// Сохраняем данные по весу
		$data['Okei_id'] = 37;
		$response2 = $this->w_model->savePersonWeight($data);
		if(strlen($response2[0]['Error_Msg']) > 0){
			DieWithError('При сохранении данных произошла ошибка');
			return false;
		}
		$this->ReturnData(array('success' => true));
	}
	
	/**
	 *	Сохранение данных по направлению на МСЭ
	 */
	function saveEvnPrescrMse()
	{
		$data = $this->ProcessInputData('saveEvnPrescrMse', true);

		if($data){
			$response = $this->dbmodel->saveEvnPrescrMse($data);
			$this->ProcessModelSave($response)->ReturnData();
			
			if ( empty ($response) || !is_array($response) ) {
				return false;
			}
			if ( isset($response[0]) && is_array($response[0])) {
				$response = $response[0];
			}
			if ( empty($response['EvnPrescrMse_id']) ) {
				return false;
			}
			
			// Генерируем уведомление
			$this->genNotice(
				array(
					'object' => 'EvnPrescrMse',
					'object_id' => $response['EvnPrescrMse_id'],
					'action' => ( !empty($data['EvnPrescrMse_id']) ) ? 'upd' : 'ins',
					'Lpu_id' => $data['Lpu_id'],
					'pmUser_id' => $data['pmUser_id']
				)
			);
			return true;
		}
		return false;
	}
	
	/**
	 *	Чтение данных по направлению на МСЭ (если есть для данного протокола ВК)
	 *	input: EvnVK_id
	 */
	function getEvnPrescrMse()
	{
		$data = $this->ProcessInputData('getEvnPrescrMse', true);
		if($data){
			$response = $this->dbmodel->getEvnPrescrMse($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}
	
	
	/**
	 *	Для определения параметров формы "протокол МСЭ" ( если протокол МСЭ для данного направления уже выписан, то берем данные по протоколу, иначе подставляем их из направления )
	 *	input: EvnPrescrMse_id
	 */
	function defineEvnMseFormParams()
	{
		$data = $this->ProcessInputData('defineEvnMseFormParams', true);
		if($data){
			$response = $this->dbmodel->defineEvnMseFormParams($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}
	
	
	/**
	 *	Чтение данных по КВС из шаблона
	 *	input: EvnPL_id
	 */
	function getEvnPLXmlData()
	{
		$this->load->helper("Xml");
		$this->load->library('parser');
		$data = $this->ProcessInputData('getEvnPLXmlData', true);
		if($data){
			$response = $this->dbmodel->getEvnPLXmlData($data);
			/*if(count($response)>0 && isset($response[0]['EvnXml_Data'])){
				$xml = $this->parser->parse_from_xml($response[0]['EvnXml_Data']);
				echo $xml;
			} else {*/
				$this->ProcessModelList($response, true, true)->ReturnData();
			//}
		}
	}
	
	/**
	 *	Печать обратного талона
	 *	input: EvnMse_id
	 */
	function printEvnMse()
	{
		$data = $this->ProcessInputData('printEvnMse', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->printEvnMse($data);
		if (!empty($response['Error_Msg'])) {
			$this->ReturnError($response['Error_Msg']);
		}
	}
	
	/**
	 *	Печать бланка направления на МСЭ
	 *	input: EvnPrescrMse_id
	 */
	function printEvnPrescrMse()
	{
		$data = $this->ProcessInputData('printEvnPrescrMse', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->printEvnPrescrMse($data);
		if (!empty($response['Error_Msg'])) {
			$this->ReturnError($response['Error_Msg']);
		}
	}
	
	/**
	 *	Чтение законного представителя для формы "направление на МСЭ"
	 */
	function getDeputyKind()
	{
		$data = $this->ProcessInputData('getDeputyKind', false);
		if(!$data) return false;
		
		$response = $this->dbmodel->getDeputyKind($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 *	Сохранение законного представителя для формы "направление на МСЭ"
	 */
	function saveDeputyKind()
	{
		$data = $this->ProcessInputData('saveDeputyKind', true);
		if(!$data) return false;
		
		$response = $this->dbmodel->saveDeputyKind($data);
		$this->ProcessModelSave($response)->ReturnData();
	}
	
	/**
	 *	Удаление законного представителя для формы "направление на МСЭ"
	 */
	function deleteDeputyKind()
	{
		$data = $this->ProcessInputData('deleteDeputyKind', true);
		if(!$data) return false;
		
		$response = $this->dbmodel->deleteDeputyKind($data);
		$this->ProcessModelSave($response)->ReturnData();
	}
	
	/**
	 *	Method description
	 */
	function loadEvnPrescrJournalGrid()
	{
		$data = $this->ProcessInputData('loadEvnPrescrJournalGrid', false);
		if(!$data) return false;
		
		$response = $this->dbmodel->loadEvnPrescrJournalGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}
	
	/**
	 *	Method description
	 */
	function cancelEvnPrescr()
	{
		$data = $this->ProcessInputData('cancelEvnPrescr', false);
		if(!$data) return false;
		
		$response = $this->dbmodel->cancelEvnPrescr($data);
		$this->ProcessModelSave($response)->ReturnData();
	}
	
	/**
	 *	Генерация уведомлений для врачей
	 *	input:	object ('EvnPrescrVK', 'EvnVK', 'EvnPrescrMse', 'EvnMse')
	 *			action ('ins', 'upd')
	 *			object_id
	 */
	function genNotice($data)
	{
		if (!$data || !isset($data['object']))
			return false;
		
		switch ( $data['object'] ) {
			case 'EvnPrescrVK': $evnText = 'направление на ВК'; break;
			case 'EvnVK': $evnText = 'протокол ВК'; break;
			case 'EvnPrescrMse': $evnText = 'направление на МСЭ'; break;
			case 'EvnMse': $evnText = 'обратный талон МСЭ';  //$evnText = 'протокол МСЭ';
				break;
		}
		
		// Находим всех "причастных"
		$recipients = $this->dbmodel->getMedPersonalForNotice($data);
		if(!$recipients) return false;
		
		// Находим данные для уведомления
		$personData = $this->dbmodel->getDataForNotice($data);		
		if(!$personData) return false;
		
		if( in_array($data['object'], array('EvnPrescrVK', 'EvnPrescrMse')) ) {
			$text = (( $data['action'] == 'upd' ) ? 'Изменено ' : 'Выписано ' ).$evnText.'. ';
		} else {
			$text = (( $data['action'] == 'upd' ) ? 'Изменен ' : 'Создан ' ).$evnText.'. ';
		}	
		$text .= 'Пациент - '.$personData['Person_Fio'].', '.$personData['Person_BirthDay'].'. Служба - '.$personData['MedService_Name'].'.';
		
		// Формируем ссылку на документ
		if($data['object'] != 'EvnPrescrVK') {
			switch ( $data['object'] ) {
				case 'EvnVK': $winPrototype = 'swPersonEmkWindow'; //$winPrototype = 'swClinExWorkEditWindow';
					break;
				case 'EvnPrescrMse': $winPrototype = 'swDirectionOnMseEditForm'; 
					if(!empty($personData['EvnStatus_Name']))
						$text.=' Новый статус: '.$personData['EvnStatus_Name'].'. ';
					break;
				case 'EvnMse': $winPrototype = 'swProtocolMseEditForm'; break;
			}
			$text .= ' <a href="javascript://" onClick="getWnd(\''.$winPrototype.'\').show({'.
						$data['object'].'_id: '.$personData[$data['object'].'_id'].','.
						'Person_id: '.$personData['Person_id'].','.
						'Server_id: '.$personData['Server_id'].','.
						'action: \'view\','.
						'showtype: \'view\'';
			if($data['object'] == 'EvnPrescrMse')
				$text .= ',EvnVK_id:'.$personData['EvnVK_id'];
			if($data['object'] == 'EvnVK') {
				$text .= ",ARMType: 'common',searchNodeObj:{EvnClass_SysNick: 'EvnVK', Evn_id: ".$personData['EvnVK_id'].", parentNodeId: 'root',last_child: false,disableLoadViewForm: false}";
				$text .= '})">Подробнее</a>';
			} else
			if($data['object'] == 'EvnPrescrMse') {
				$text .= '})">Подробнее</a>';
			}
			else
			$text .= '})">смотреть документ</a>';
		}
		
		$noticeData = array(
			'autotype' => 1
			,'Lpu_rid' => $data['Lpu_id']
			,'pmUser_id' => $data['pmUser_id']
			,'type' => 1
			,'title' => 'Автоматическое уведомление'
			,'text' => $text
		);
		$noticeData2 = null;
		if(!empty($personData['VkLpu_id']) and $data['Lpu_id'] != $personData['VkLpu_id']) {
			$noticeData2 = array(
				'autotype' => 1
				,'Lpu_rid' => $personData['VkLpu_id']
				,'pmUser_id' => $data['pmUser_id']
				,'type' => 1
				,'title' => 'Автоматическое уведомление'
				,'text' => $text
			);
		}
		
		$this->load->model('Messages_model', 'Messages_model');
		
		foreach($recipients as $recipient) {
			$noticeData['MedPersonal_rid'] = $recipient;
			
			$noticeResponse = $this->Messages_model->autoMessage($noticeData);
			if ( !empty($noticeResponse['Error_Msg']) ) {
				echo json_return_errors($noticeResponse['Error_Msg']);
				return false;
			}
			if($noticeData2!=null) {
				$noticeData2['MedPersonal_rid'] = $recipient;
				$noticeResponse2 = $this->Messages_model->autoMessage($noticeData2);
				if ( !empty($noticeResponse2['Error_Msg']) ) {
					echo json_return_errors($noticeResponse2['Error_Msg']);
					return false;
				}
			}
		}
	}
	
	/**
	 * Транслитерация текста из русского в латиницу
	 */
	function rus2translit($string)
	{
		$converter = array(
			'а' => 'a',   'б' => 'b',   'в' => 'v',
			'г' => 'g',   'д' => 'd',   'е' => 'e',
			'ё' => 'jo',   'ж' => 'zh',  'з' => 'z',
			'и' => 'i',   'й' => 'y',   'к' => 'k',
			'л' => 'l',   'м' => 'm',   'н' => 'n',
			'о' => 'o',   'п' => 'p',   'р' => 'r',
			'с' => 's',   'т' => 't',   'у' => 'u',
			'ф' => 'f',   'х' => 'kh',   'ц' => 'c',
			'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
			'ь' => "'",  'ы' => 'y',   'ъ' => "'",
			'э' => 'e',   'ю' => 'ju',  'я' => 'ja',
			
			'А' => 'A',   'Б' => 'B',   'В' => 'V',
			'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
			'Ё' => 'Jo',   'Ж' => 'Zh',  'З' => 'Z',
			'И' => 'I',   'Й' => 'Y',   'К' => 'K',
			'Л' => 'L',   'М' => 'M',   'Н' => 'N',
			'О' => 'O',   'П' => 'P',   'Р' => 'R',
			'С' => 'S',   'Т' => 'T',   'У' => 'U',
			'Ф' => 'F',   'Х' => 'Kh',   'Ц' => 'C',
			'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
			'Ь' => "'",  'Ы' => 'Y',   'Ъ' => "'",
			'Э' => 'E',   'Ю' => 'Ju',  'Я' => 'Ja',
			' ' => '_'
		);
		return strtr($string, $converter);
	}
	
	/**
	*	Загружает файл на сервер
	*/
	function uploadFiles() {
		if(!isset($_FILES['file'])) {
			echo json_encode(array('success' => false, 'Error_Code' => 10501, 'error' => toUTF('Ошибка загрузки файла!')));
			return false;
		}
		if($_FILES['file']['error'] == 1 || (int)$_FILES['file']['size'] > 2097152) {
			echo json_encode(array('success' => false, 'error' => toUTF('Запрещено загружать файлы размером более 2 мб!')));
			return false;
		}
		if($_FILES['file']['tmp_name'] == '') {
			echo json_encode(array('success' => false, 'Error_Code' => 10502, 'error' => toUTF('Ошибка загрузки файла!')));
			return false;
		}
		$newfile = basename($_FILES['file']['tmp_name']);
		$attachFilesDir = IMPORTPATH_ROOT . "mseattaches/";
		if( !is_dir($attachFilesDir) ) {
			if( !mkdir($attachFilesDir) ) {
				return;
			}
		}
		if (getRegionNick() != 'kz') $_FILES['file']['name'] = $this->rus2translit($_FILES['file']['name']);
		$newname = $attachFilesDir . str_replace('.', '', $newfile).rand(1,10000).'.tmp';
		$flag = @rename($_FILES['file']['tmp_name'], $newname);
		if(!$flag) {
			echo json_encode(array('success' => false, 'Error_Code' => 10503, 'error' => toUTF('Ошибка загрузки файла!')));
			return false;
		}
		
		$val = array(
			'name'		=> toUTF($_FILES['file']['name']),
			'tmp_name'	=> toUTF($newname),
			'size'		=> $_FILES['file']['size'],
			'success'	=> true
		);
		$this->ReturnData($val);
	}
	
	/**
	 *	Method description
	 */
	function getEvnPrescrMseStatusHistory() {
		$data = $this->ProcessInputData('getEvnPrescrMseStatusHistory', false);
		if(!$data) return false;
		
		$response = $this->dbmodel->getEvnPrescrMseStatusHistory($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 *	Method description
	 */
	function setEvnVKIsFail() {
		$data = $this->ProcessInputData('setEvnVKIsFail', false);
		if(!$data) return false;
		
		$response = $this->dbmodel->setEvnVKIsFail($data);
		$this->ProcessModelSave($response)->ReturnData();
	}
	
	/**
	 *	Method description
	 */
	function loadCategoryLifeTypeLinkList() {
		$data = $this->ProcessInputData('loadCategoryLifeTypeLinkList', false);
		if(!$data) return false;
		
		$response = $this->dbmodel->loadCategoryLifeTypeLinkList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 *	Method description
	 */
	function loadEvnMseCategoryLifeTypeLink() {
		$data = $this->ProcessInputData('loadEvnMseCategoryLifeTypeLink', false);
		if(!$data) return false;
		
		$response = $this->dbmodel->loadEvnMseCategoryLifeTypeLink($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 *	Method description
	 */
	function saveEvnMseCategoryLifeType() {
		$data = $this->ProcessInputData('saveEvnMseCategoryLifeType', true);
		if(!$data) return false;
		
		$response = $this->dbmodel->saveEvnMseCategoryLifeType($data);
		$this->ProcessModelSave($response)->ReturnData();
	}
	
	/**
	 *	Поиск направлений
	 */
	function searchEvnPrescrMse() {
		$data = $this->ProcessInputData('searchEvnPrescrMse', true);
		if(!$data) return false;

		$response = $this->dbmodel->searchEvnPrescrMse($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}
	
	/**
	 *	Проверка существования направления
	 */
	function checkEvnPrescrMseExists()
	{
		$sp = getSessionParams();
		$data = $this->ProcessInputData('checkEvnPrescrMseExists', false);
		if($data){
			$data['Lpu_id'] = $sp['Lpu_id'];
			$response = $this->dbmodel->checkEvnPrescrMseExists($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}
	
	/**
	 *	Загрузка списка направлений на МСЭ
	 */
	function getEvnPrescrMseList()
	{
		$sp = getSessionParams();
		$data = $this->ProcessInputData('getEvnPrescrMseList', false);
		if($data){
			$data['Lpu_id'] = $sp['Lpu_id'];
			$response = $this->dbmodel->getEvnPrescrMseList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}

	/**
	 *	Загрузка списка направлений на ВМП
	 */
	function getEvnDirectionHTMList()
	{
		$sp = getSessionParams();
		$data = $this->ProcessInputData('getEvnDirectionHTMList', false);
		if($data){
			$data['Lpu_id'] = $sp['Lpu_id'];
			$response = $this->dbmodel->getEvnDirectionHTMList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}

	/**
	 *	DSCR
	 */
	function getEvnPrescrMseData()
	{
		$data = $this->ProcessInputData('getEvnPrescrMseData', false);
		if($data){
			$response = $this->dbmodel->getEvnPrescrMseData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}

	/**
	 * @return bool
	 */
	function updateEvnPrescrMseStatus() {
		$data = $this->ProcessInputData('updateEvnPrescrMseStatus', true);
		if(!$data) return false;

		$response = $this->dbmodel->updateEvnPrescrMseStatus($data);
		$this->ProcessModelSave($response)->ReturnData();
		
		if ( empty ($response) || !is_array($response) ) {
			return false;
		}
		if ( isset($response[0]) && is_array($response[0])) {
			$response = $response[0];
		}
		if ( empty($response['success']) ) {
			return false;
		}
		// Генерируем уведомление
		$this->genNotice(
			array(
				'object' => 'EvnPrescrMse',
				'object_id' => $data['Evn_id'],
				'action' => ( !empty($data['Evn_id']) ) ? 'upd' : 'ins',
				'Lpu_id' => $data['Lpu_id'],
				'pmUser_id' => $data['pmUser_id']
			)
		);
	}

	/**
	 * @return bool
	 */
	function exportEvnPrescrMse() {
		$data = $this->ProcessInputData('exportEvnPrescrMse', true);
		if(!$data) return false;

		$response = $this->dbmodel->exportEvnPrescrMse($data);
		$this->ProcessModelSave($response, true, 'Ошибка при экспорте направлений на МСЭ')->ReturnData();
	}

	/**
	 * Импорт обратных талонов
	*/
	function importEvnMse()
	{
		$data = $this->ProcessInputData('importEvnMse', true);
		if(!$data) return false;

		$response = $this->dbmodel->importEvnMse($data);
		$this->ProcessModelSave($response, true, 'Ошибка при импорте обратных талонов')->ReturnData();
	}
	
	/**
	 *	DSCR
	 */
	function searchUslugaComplexMSE() {
		$data = $this->ProcessInputData('searchUslugaComplexMSE', true);
		if(!$data) return false;
		
		$response = $this->dbmodel->searchUslugaComplexMSE($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 *	DSCR
	 */
	function getUslugaComplexMSERecommended() {
		$data = $this->ProcessInputData('getUslugaComplexMSERecommended', true);
		if(!$data) return false;
		
		$response = $this->dbmodel->getUslugaComplexMSERecommended($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 *	DSCR
	 */
	function loadUslugaComplexMSEList() {
		$data = $this->ProcessInputData('loadUslugaComplexMSEList', true);
		if(!$data) return false;
		
		$response = $this->dbmodel->loadUslugaComplexMSEList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Получение целей МСЭ
	*/
	function loadMultiplePrescrAims()
	{
		$data = $this->ProcessInputData('loadMultiplePrescrAims', true);
		if(!$data) return false;

		$response = $this->dbmodel->loadMultiplePrescrAims($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Получение данных по регистру ИПРА
	*/
	function getIPRAData()
	{
		$data = $this->ProcessInputData('getIPRAData', true);
		if(!$data) return false;

		$response = $this->dbmodel->getIPRAData($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Список обратных талонов по человеку
	*/
	function getPrevEvnMseList()
	{
		$data = $this->ProcessInputData('getPrevEvnMseList', true);
		if(!$data) return false;

		$response = $this->dbmodel->getPrevEvnMseList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Проверка наличия документа у человека
	*/
	function checkPersonDocument()
	{
		$data = $this->ProcessInputData('checkPersonDocument', true);
		if(!$data) return false;

		$response = $this->dbmodel->checkPersonDocument($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * Получение необходимого набора исследований
	 */
	function getRequiredSetOfStudies(){
		$data = $this->ProcessInputData('getRequiredSetOfStudies', true);
		if(!$data) return false;

		$response = $this->dbmodel->getRequiredSetOfStudies($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * проверка на полноту исследований МСЭ
	 */
	function completenessTestMSE(){
		$data = $this->ProcessInputData('completenessTestMSE', true);
		if(!$data) return false;

		$response = $this->dbmodel->completenessTestMSE($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Загрузка журнала запросов ВК
	 */
	function loadVKJournalGrid(){
		$data = $this->ProcessInputData('loadVKJournalGrid', true);
		if(!$data) return false;

		$response = $this->dbmodel->loadVKJournalGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	 * Загрузка формы направления на ВК
	 */
	function loadEvnPrescrVKWindow(){
		$data = $this->ProcessInputData('loadEvnPrescrVKWindow', true);
		if(!$data) return false;

		$response = $this->dbmodel->loadEvnPrescrVKWindow($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Загрузка списка статусов направления на ВК
	 */
	function loadEvnPrescrVKStatusGrid(){
		$data = $this->ProcessInputData('loadEvnPrescrVKStatusGrid', true);
		if(!$data) return false;

		$response = $this->dbmodel->loadEvnPrescrVKStatusGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
}
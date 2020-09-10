<?php
defined('BASEPATH') or die('404. Script not found.');

/**
 * @class EmergencyTeam
 * 
 * Бригада СМП
 * 
 * @author Dyomin Dmitry
 * @since 09.2012
 */

/**
 * Class EmergencyTeam4E
 * @property EmergencyTeam_model4E $dbmodel
 */
class EmergencyTeam4E extends swController {
	
	public $inputRules = array(
		'saveEmergencyTeam' => array(
			array(
				'field'	=> 'EmergencyTeam_id',
				'label'	=> 'Идентификатор бригады',
				'rules'	=> '',
				'type'	=> 'id'
			),
			array(
				'field'	=> 'EmergencyTeam_Num',
				'label'	=> 'Номер бригады',
				'rules'	=> 'required',
				'type'	=> 'string'
			),
			array(
				'field'	=> 'EmergencyTeam_CarNum',
				'label'	=> 'Номер кареты',
				'rules'	=> '',
				'type'	=> 'string'
			),
			array(
				'field'	=> 'EmergencyTeam_CarBrand',
				'label'	=> 'Марка авто',
				'rules'	=> '',
				'type'	=> 'string'
			),
			array(
				'field'	=> 'EmergencyTeam_CarModel',
				'label'	=> 'Модель авто',
				'rules'	=> '',
				'type'	=> 'string'
			),
			array(
				'field'	=> 'EmergencyTeam_PortRadioNum',
				'label'	=> 'Номер рации',
				'rules'	=> '',
				'type'	=> 'string'
			),
			array(
				'field'	=> 'GeoserviceTransport_id',
				'label'	=> 'GPS/Глонасс',
				'rules'	=> '',
				'type'	=> 'id'
			),
			// Пока не используется
			array(
				'field'	=> 'EmergencyTeam_GpsNum',
				'label'	=> 'Номер GPS/Глонасс',
				'rules'	=> '',
				'type'	=> 'string'
			),
			array(
				'field'	=> 'LpuBuilding_id',
				'label'	=> 'Номер базовой подстанции',
				'rules'	=> '',
				'type'	=> 'int'
			),
			array(
				'field'	=> 'EmergencyTeamSpec_id',
				'label'	=> 'Профиль бригады',
				'rules'	=> '',
				'type'	=> 'int'
			),
			array(
				'field'	=> 'EmergencyTeam_HeadShift',
				'label'	=> 'Старший бригады',
				'rules'	=> '',
				'type'	=> 'int'
			),
			array(
				'field'	=> 'EmergencyTeam_HeadShiftWorkPlace',
				'label'	=> 'Место работы старшего бригады',
				'rules'	=> '',
				'type'	=> 'int'
			),
			array(
				'field'	=> 'EmergencyTeam_HeadShift2',
				'label'	=> 'Старший бригады',
				'rules'	=> '',
				'type'	=> 'int'
			),
			array(
				'field'	=> 'EmergencyTeam_HeadShift2WorkPlace',
				'label'	=> 'Место работы первого помощника',
				'rules'	=> '',
				'type'	=> 'int'
			),
			array(
				'field'	=> 'EmergencyTeam_Driver',
				'label'	=> 'Водитель',
				'rules'	=> '',
				'type'	=> 'int'
			),
			array(
				'field'	=> 'EmergencyTeam_DriverWorkPlace',
				'label'	=> 'Место работы водителя',
				'rules'	=> '',
				'type'	=> 'int'
			),
			array(
				'field'	=> 'EmergencyTeam_Driver2',
				'label'	=> 'Водитель',
				'rules'	=> '',
				'type'	=> 'int'
			),
			array(
				'field'	=> 'EmergencyTeam_Assistant1',
				'label'	=> 'Первый помощник',
				'rules'	=> '',
				'type'	=> 'int'
			),
			array(
				'field'	=> 'EmergencyTeam_Assistant1WorkPlace',
				'label'	=> 'Место работы второго помощника',
				'rules'	=> '',
				'type'	=> 'int'
			),
			array(
				'field'	=> 'EmergencyTeam_Assistant2',
				'label'	=> 'Второй помощник',
				'rules'	=> '',
				'type'	=> 'int'
			),
			array(
				'field'	=> 'ARMType',
				'label'	=> '',
				'rules'	=> '',
				'type'	=> 'string'
			),			
			array(
				'field'	=> 'EmergencyTeam_Head1ShiftTime',
				'label'	=> 'Смена старшего смены1',
				'rules'	=> '',
				'type'	=> 'string'
			),			
			array(
				'field'	=> 'EmergencyTeam_Head2ShiftTime',
				'label'	=> 'Смена старшего смены2',
				'rules'	=> '',
				'type'	=> 'string'
			),			
			array(
				'field'	=> 'EmergencyTeam_Assistant1ShiftTime',
				'label'	=> 'Смена помощника1',
				'rules'	=> '',
				'type'	=> 'string'
			),			
			array(
				'field'	=> 'EmergencyTeam_Assistant2ShiftTime',
				'label'	=> 'Смена помощника2',
				'rules'	=> '',
				'type'	=> 'string'
			),			
			array(
				'field'	=> 'EmergencyTeam_Driver1ShiftTime',
				'label'	=> 'Смена водителя1',
				'rules'	=> '',
				'type'	=> 'string'
			),			
			array(
				'field'	=> 'EmergencyTeam_Driver2ShiftTime',
				'label'	=> 'Смена водителя2',
				'rules'	=> '',
				'type'	=> 'string'
			),			
			array(
				'field'	=> 'EmergencyTeam_DutyTime',
				'label'	=> 'Общее время',
				'rules'	=> '',
				'type'	=> 'string'
			),
			array(
				'field'	=> 'EmergencyTeam_isTemplate',
				'label'	=> 'Шаблон',
				'rules'	=> '',
				'type'	=> 'int'
			),			
			array(
				'field'	=> 'EmergencyTeam_TemplateName',
				'label'	=> 'Имя шаблона',
				'rules'	=> '',
				'type'	=> 'string'
			),
			array(
				'field'	=> 'MedProductCard_id',
				'label'	=> 'Автомобиль',
				'rules'	=> '',
				'type'	=> 'int'
			),
			array(
				'field'	=> 'EmergencyTeamDuty_DTStart',
				'label'	=> 'Время начала смены',
				'rules'	=> '',
				'type'	=> 'string'
			),
			array(
				'field'	=> 'EmergencyTeamDuty_DTFinish',
				'label'	=> 'Время начала смены',
				'rules'	=> '',
				'type'	=> 'string'
			),
			array(
				'field'	=> 'CMPTabletPC_id',
				'label'	=> 'Идентификатор бригады',
				'rules'	=> '',
				'type'	=> 'id'
			),
			array(
				'field'	=> 'EmergencyTeam_Phone',
				'label'	=> 'Телефон',
				'rules'	=> '',
				'type'	=> 'string'
			)
		),
		
		'deleteEmergencyTeam' => array(
			array(
				'field'	=> 'EmergencyTeam_id',
				'label'	=> 'Идентификатор бригады',
				'rules'	=> 'required',
				'type'	=> 'id',
			),
		),
		
		'deleteEmergencyTeamList' => array(
			array(
				'field'	=> 'EmergencyTeamsList',
				'label'	=> 'Бригады',
				'rules'	=> 'required',
				'type'	=> 'json_array',
				'assoc' => true
			)
		),
		
		'loadEmergencyTeamVigils' => array(
			array(
				'field'	=> 'EmergencyTeam_id',
				'label'	=> 'Идентификатор бригады',
				'rules'	=> 'required',
				'type'	=> 'id',
			),
		),
		
		'loadSingleEmergencyTeamVigil' => array(
			array(
				'field'	=> 'EmergencyTeam_id',
				'label'	=> 'Идентификатор бригады',
				'rules'	=> 'required',
				'type'	=> 'id'
			),
			array(
				'field'	=> 'CmpEmTeamDuty_id',
				'label'	=> 'Идентификатор бригады',
				'rules'	=> '',
				'type'	=> 'int'
			),
		),		
		
		'deleteEmergencyTeamVigil' => array(
			array(
				'field'	=> 'CmpEmTeamDuty_id',
				'label'	=> 'Идентификатор дежурства',
				'rules'	=> 'required',
				'type'	=> 'int'
			),
		),
		'editEmergencyTeamVigilTimes' => array(
			array(
				'field'	=> 'CmpEmTeamDuty_id',
				'label'	=> 'Идентификатор дежурства',
				'rules'	=> 'required',
				'type'	=> 'int'
			),
			array(
				'field'	=> 'CmpEmTeamDuty_FactBegDT',
				'label'	=> 'Начало фактическое',
				'rules'	=> '',
				'type'	=> 'string'
			),
			array(
				'field'	=> 'CmpEmTeamDuty_FactEndDT',
				'label'	=> 'Окончание фактическое',
				'rules'	=> '',
				'type'	=> 'string'
			)
		),
		'saveEmergencyTeamVigil' => array(
			array(
				'field'	=> 'CmpEmTeamDuty_id',
				'label'	=> 'Идентификатор дежурства',
				'rules'	=> '',
				'type'	=> 'id'
			),
			array(
				'field'	=> 'EmergencyTeam_id',
				'label'	=> 'Идентификатор бригады',
				'rules'	=> 'required',
				'type'	=> 'id'
			),
			array(
				'field'	=> 'KLRgn_id',
				'label'	=> 'Идентификатор региона',
				'rules'	=> '',
				'type'	=> 'id'
			),
			array(
				'field'	=> 'KLSubRgn_id',
				'label'	=> 'Идентификатор района',
				'rules'	=> '',
				'type'	=> 'id'
			),
			array(
				'field'	=> 'KLCity_id',
				'label'	=> 'Идентификатор города',
				'rules'	=> '',
				'type'	=> 'id'
			),
			array(
				'field'	=> 'KLTown_id',
				'label'	=> 'Идентификатор нас пункта',
				'rules'	=> '',
				'type'	=> 'id'
			),			
			array(
				'field'	=> 'KLStreet_id',
				'label'	=> 'Идентификатор улицы',
				'rules'	=> '',
				'type'	=> 'id'
			),
			array(
				'field'	=> 'CmpEmTeamDuty_House',
				'label'	=> 'Номер дома',
				'rules'	=> '',
				'type'	=> 'string'
			),
			array(
				'field'	=> 'CmpEmTeamDuty_Corpus',
				'label'	=> 'Номер корпуса',
				'rules'	=> '',
				'type'	=> 'string'
			),
			array(
				'field'	=> 'CmpEmTeamDuty_Flat',
				'label'	=> 'Номер квартиры',
				'rules'	=> '',
				'type'	=> 'int'
			),			
			array(
				'field'	=> 'UnformalizedAddressDirectory_id',
				'label'	=> 'Идентификатор неформ адреса',
				'rules'	=> '',
				'type'	=> 'int'
			),
			array(
				'field'	=> 'CmpEmTeamDuty_PlanBegDT',
				'label'	=> 'Начало плановое',
				'rules'	=> '',
				'type'	=> 'string'
			),
			array(
				'field'	=> 'CmpEmTeamDuty_PlanEndDT',
				'label'	=> 'Окончание плановое',
				'rules'	=> '',
				'type'	=> 'string'
			),
			
			array(
				'field'	=> 'CmpEmTeamDuty_FactBegDT',
				'label'	=> 'Начало фактическое',
				'rules'	=> '',
				'type'	=> 'string'
			),
			array(
				'field'	=> 'CmpEmTeamDuty_FactEndDT',
				'label'	=> 'Окончание фактическое',
				'rules'	=> '',
				'type'	=> 'string'
			),
			array(
				'field'	=> 'CmpEmTeamDuty_Discription',
				'label'	=> 'Описание',
				'rules'	=> '',
				'type'	=> 'string'
			)
		),
		
		'loadEmergencyTeamOperEnv' => array(
			array('field'	=> 'Lpu_id','label'	=> 'Идентификатор ЛПУ','rules'	=> '','type'=> 'id'),
			array('field'	=> 'MedService_id','label'	=> 'Идентификатор службы','rules'=> '','type'=> 'id'),
			array('field'	=> 'ShowWorkingTeams','label'	=> 'Показывать активные','rules'	=> '','type'	=> 'string'),
			
		),
				
		'getEmergencyTeamTemplatesNames' => array(),
		'loadEmergencyTeamOperEnvForSmpUnit' => array(),
		'loadEmergencyTeamTemplateList' => array(),
		'loadEmergencyTeamOperEnvForSmpUnitsNested' => array(),
		'loadEmergencyTeamsARMCenterDisaster' => array(
			array( 'field' => 'Lpu_ids', 'label' => 'Идентификаторы МО', 'rules' => '', 'type' => 'string')
		),
		'loadCmpCallCardsARMCenterDisaster' => array(
			array('field' => 'begDate','label' => 'Дата с','rules' => 'trim', 'type' => 'date'),
			array('field' => 'endDate','label' => 'Дата по','rules' => 'trim','type' => 'date'),
			array('field' => 'begTime','label' => 'Время с','rules' => 'trim','type' => 'string'),
			array('field' => 'endTime','label' => 'Время по','rules' => 'trim','type' => 'string'),
			array('field' => 'isNmp',  'label' => 'Вызовы НМП?', 'rules' => 'trim', 'type' => 'int'),
			array( 'field' => 'Lpu_ids', 'label' => 'Идентификаторы МО', 'rules' => '', 'type' => 'string')
		),
		'getCountsTeamsCallsAndDocsARMCenterDisaster' => array(
			array( 'field' => 'Lpu_ids', 'label' => 'Идентификаторы МО', 'rules' => '', 'type' => 'string')
		),

		'loadEmergencyTeamStatuses' => array(
			array('field' => 'EmergencyTeamStatus_pid','label' => 'Ид текущего статуса','rules' => '', 'type' => 'id')
		),

		'loadEmergencyTeamStatusesHistory' => array(
			array(
				'field'	=> 'EmergencyTeam_id',
				'label'	=> 'Идентификатор бригады',
				'rules'	=> 'required',
				'type'	=> 'id',
			)
		),
		
		'saveEmergencyTeamDutyTime' => array(
			array(
				'field'	=> 'EmergencyTeamsDutyTimes',
				'label'	=> 'Идентификатор бригады',
				'rules'	=> 'required',
				'type'	=> 'string'
			)
			
		),
		
		'saveEmergencyTeams' => array(
			array(
				'field'	=> 'EmergencyTeams',
				'label'	=> 'Бригады',
				'rules'	=> 'required',
				'type'	=> 'json_array',
				'assoc' => true
			)
		),
		
		'saveEmergencyTeamsSplit' => array(
			array(
				'field'	=> 'EmergencyTeams',
				'label'	=> 'Бригады',
				'rules'	=> 'required',
				'type'	=> 'json_array',
				'assoc' => true
			)
		),
		
		'editEmergencyTeamDutyTime' => array(
			array(
				'field'	=> 'EmergencyTeam_id',
				'label'	=> 'Идентификатор бригады',
				'rules'	=> 'required',
				'type'	=> 'id',
			),
			array(
				'field'	=> 'EmergencyTeamDuty_id',
				'label'	=> 'Идентификатор смены бригады',
				'rules'	=> '',
				'type'	=> 'id',
			),
			array(
				'field' => 'EmergencyTeamDuty_DateStart',
				'label' => 'Дата и время начала смены',
				'rules' => '',
				'type' => 'string',
				'is_array' => true,
			),
			array(
				'field' => 'EmergencyTeamDuty_DateFinish',
				'label' => 'Дата и время окончания смены',
				'rules' => '',
				'type' => 'string',
				'is_array' => true,
			),
		),
		
		'deleteEmergencyTeamDutyTime' => array(
			array(
				'field'	=> 'EmergencyTeamDuty_id',
				'label'	=> 'Идентификатор бригады',
				'rules'	=> 'required',
				'type'	=> 'id',
			),
		),
		
		'deleteEmergencyTeamDutyTimeList' => array(
			array(
				'field'	=> 'EmergencyTeamDutyList',
				'label'	=> 'Смены',
				'rules'	=> 'required',
				'type'	=> 'json_array',
				'assoc' => true
			)
		),
		
		'loadDispatchOperEnv' => array(
			array(
				'field'	=> 'Lpu_id',
				'label'	=> 'Идентификатор ЛПУ',
				'rules'	=> '',
				'type'	=> 'id',
			),
			array(
				'field'	=> 'online',
				'label'	=> 'Статус онлайн',
				'rules'	=> '',
				'type'	=> 'string',
			)
		),

		'loadEmergencyTeamDrugsPack' => array(
			array(
				'field'	=> 'EmergencyTeam_id',
				'label'	=> 'Идентификатор бригады',
				'rules'	=> 'required',
				'type'	=> 'id'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Начальный номер записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество возвращаемых записей',
				'rules' => '',
				'type' => 'int'
			)
		),
		

		'loadEmergencyTeam' => array(
			array(
				'field'	=> 'EmergencyTeam_id',
				'label'	=> 'Идентификатор бригады',
				'rules'	=> 'required',
				'type'	=> 'id'
			),
			array(
				'field'	=> 'EmergencyTeamDuty_id',
				'label'	=> 'Идентификатор смены',
				'rules'	=> '',
				'type'	=> 'int'
			)
		),
		
		'loadEmergencyTeamList' => array(
			array(
				'field'	=> 'EmergencyTeam_id',
				'label'	=> 'Идентификатор бригады',
				'rules'	=> '',
				'type'	=> 'id'
			),
			array(
				'field'	=> 'Template',
				'label'	=> 'Идентификатор отображения шаблона бригад',
				'rules'	=> '',
				'type'	=> 'int'
			),
			array(
				'field'	=> 'LpuBuilding_id',
				'label'	=> 'Идентификатор подстанции',
				'rules'	=> '',
				'type'	=> 'int'
			)
		),
		'loadEmergencyTeamAutoList'=>array(
			array(
				'field'	=> 'LpuBuilding_id',
				'label'	=> 'Идентификатор подстанции',
				'rules'	=> '',
				'type'	=> 'int'
			),
			array(
				'field'	=> 'MedService_id',
				'label'	=> 'Идентификатор службы',
				'rules'	=> '',
				'type'	=> 'int'
			),
			array(
				'field'	=> 'display',
				'label'	=> 'Режим отображения',
				'rules'	=> '',
				'type'	=> 'string'
			),
			array(
				'field'	=> 'filterLpuBuilding',
				'label'	=> 'Фильтр по подстанции',
				'rules'	=> '',
				'type'	=> 'int'
			),
            array(
				'field'	=> 'viewAllMO',
				'label'	=> 'Показывать все подстанции',
				'rules'	=> '',
				'type'	=> 'int'
			),
			array(
				'field'	=> 'dStart',
				'label'	=> 'Фильтр по дате',
				'rules'	=> '',
				'type'	=> 'date'
			),
			array(
				'field'	=> 'dFinish',
				'label'	=> 'Фильтр по дате',
				'rules'	=> '',
				'type'	=> 'date'
			),
            array(
				'field'	=> 'dtStart',
				'label'	=> 'Фильтр по дате',
				'rules'	=> '',
				'type'	=> 'string'
			),
			array(
				'field'	=> 'dtFinish',
				'label'	=> 'Фильтр по дате',
				'rules'	=> '',
				'type'	=> 'string'
			)
		),
		
		'setEmergencyTeamStatus' => array(
			array(
				'field'	=> 'EmergencyTeam_id',
				'label'	=> 'Идентификатор бригады',
				'rules'	=> 'required',
				'type'	=> 'id',
			),
			array(
				'field'	=> 'EmergencyTeamStatus_id',
				'label'	=> 'Статус бригады',
				'rules'	=> '',
				'type'	=> 'int',
			),
			array(
				'field'	=> 'EmergencyTeamStatus_Code',
				'label'	=> 'Статус бригады',
				'rules'	=> '',
				'type'	=> 'int',
			),
			array(
				'field'	=> 'ARMType',
				'label'	=> 'Наименование арма',
				'rules'	=> '',
				'type'	=> 'string'
			),
			array(
				'field'	=> 'CmpCallCard_id',
				'label'	=> 'Ид талона',
				'rules'	=> '',
				'type'	=> 'int'
			)
		),
		
		'cancelEmergencyTeamFromCall' => array(
			array(
				'field'	=> 'CmpCallCard_id',
				'label'	=> 'Идентификатор вызова',
				'rules'	=> 'required',
				'type'	=> 'id',
			),	
			array(
				'field'	=> 'EmergencyTeam_id',
				'label'	=> 'Идентификатор бригады',
				'rules'	=> 'required',
				'type'	=> 'id',
			),		
			array(
				'field'	=> 'CmpCallCard_rid',
				'label'	=> 'Идентификатор первичного вызова',
				'rules'	=> '',
				'type'	=> 'int',
			),		
			array(
				'field'	=> 'ARMType',
				'label'	=> 'Наименование арма',
				'rules'	=> 'required',
				'type'	=> 'string'
		),
			array(
				'field' => 'typeSetStatusCCC',
				'label' => 'Тип отмены вызова',
				'rules' => '',
				'type' => 'string' ),
		),
		
		'loadEmergencyTeamDutyTimeGrid' => array(
			array(
				'field'	=> 'EmergencyTeam_id',
				'label'	=> 'Идентификатор бригады',
				'rules'	=> 'required',
				'type'	=> 'id',
			),
			array(
				'field' => 'dateStart',
				'label' => 'Дата начала поиска смен',
				'rules' => 'required',
				'type' => 'date',
			),
			array(
				'field' => 'dateFinish',
				'label' => 'Дата окончания поиска смен',
				'rules' => 'required',
				'type' => 'date',
			),
		),
		
		'loadEmergencyTeamDutyTimeListGrid' => array(
			array(
				'field'	=> 'EmergencyTeam_id',
				'label'	=> 'Идентификатор бригады',
				'rules'	=> '',
				'type'	=> 'id',
			),
			array(
				'field' => 'dateStart',
				'label' => 'Дата начала поиска смен',
				'rules' => '',
				'type' => 'string',
			),
			array(
				'field' => 'dateFinish',
				'label' => 'Дата окончания поиска смен',
				'rules' => '',
				'type' => 'string',
			),
			array(
				'field' => 'loadSelectSmp',
				'label' => '',
				'rules' => '',
				'type' => 'boolean',
			),
		),
		
		'setEmergencyTeamWorkComing' => array(
			array(
				'field'	=> 'EmergencyTeamDuty_id',
				'label'	=> 'Идентификатор смены бригады',
				'rules'	=> 'required',
				'type'	=> 'id',
			),
			array(
				'field'	=> 'EmergencyTeam_id',
				'label'	=> 'Идентификатор бригады',
				'rules'	=> 'required',
				'type'	=> 'int',
			),
			array(
				'field'	=> 'EmergencyTeamDuty_isComesToWork',
				'label'	=> 'Флаг выхода на смену бригады',
				'rules'	=> 'required',
				'type'	=> 'int',
			),
		),
		
		'setEmergencyTeamsWorkComingList' => array(
			array(
				'field'	=> 'EmergencyTeamsDutyTimesAndComing',
				'label'	=> 'Массив данных о выходе бригад на смену(время и признак)',
				'rules'	=> 'required',
				'type'	=> 'string'
			)
		),
		
		'setEmergencyTeamsCloseList' => array(
			array(
				'field'	=> 'EmergencyTeamsClose',
				'label'	=> 'Массив данных',
				'rules'	=> 'required',
				'type'	=> 'string'
			)
		),
		
		'loadEmergencyTeamCombo' => array(),
		
		'printCloseDuty' => array(
			array(
				'field' => 'dateStart',
				'label' => 'От',
				'rules' => 'required',
				'type' => 'date'
			), 
			array(
				'field' => 'dateFinish',
				'label' => 'До',
				'rules' => 'required',
				'type' => 'date'
			)
		),
		
		'loadEmergencyTeamComboWithWialonID' => array(
			array(
				'field' => 'dateStart',
				'label' => 'От',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'workComing',
				'label' => 'Вышел на работу',
				'rules' => '',
				'type' => 'string'
			)
		),
		'getEmergencyTeamProposalLogic' =>array(

			array('field' => 'EmergencyTeamProposalLogic_id', 'label' => 'Ид. правила', 'rules' => '', 'type' => 'id' ),
		),
		'getEmergencyTeamProposalLogicRuleSpecSequence'=>array(
			array('field' => 'EmergencyTeamProposalLogic_id', 'label' => 'Ид. правила', 'rules' => '', 'type' => 'id' )
		),
		'saveEmergencyTeamProposalLogicRule'=>array(
			array('field' => 'EmergencyTeamProposalLogic_id', 'label' => 'Ид. правила', 'rules' => '', 'type' => 'id' ),
			array('field' => 'CmpReason_id', 'label' => 'Ид. повод вызова', 'rules' => 'required', 'type' => 'id' ),
			array('field' => 'Sex_id', 'label' => 'Ид. пола пациента', 'rules' => '', 'type' => 'id' ),
			array('field' => 'EmergencyTeamProposalLogic_AgeFrom', 'label' => 'Возраст от', 'rules' => '', 'type' => 'int' ),
			array('field' => 'EmergencyTeamProposalLogic_AgeTo', 'label' => 'Возраст до', 'rules' => '', 'type' => 'int' ),
			array('field' => 'EmergencyTeamProposalLogicRule_Sequence', 'label' => 'Последовательность профилей', 'rules' => 'required', 'type' => 'string' )
		),
		'deleteEmergencyTeamProposalLogicRule'=>array(
			array('field' => 'EmergencyTeamProposalLogic_id', 'label' => 'Ид. правила', 'rules' => 'required', 'type' => 'id' )
		),
		'loadEmergencyTeamShiftList'=>array(
			array( 'field' => 'dateStart', 'label' => 'Дата начала поиска смен', 'rules' => '', 'type' => 'string', ), 
			array( 'field' => 'dateFinish', 'label' => 'Дата окончания поиска смен', 'rules' => '', 'type' => 'string', ),
			array( 'field' => 'dateFactStart', 'label' => 'Дата фактического начала', 'rules' => '', 'type' => 'date'),
			array( 'field' => 'dateFactFinish', 'label' => 'Дата фактического окончания', 'rules' => '', 'type' => 'date'),
			array( 'field' => 'timeFactStart', 'label' => 'Время фактического начала', 'rules' => '', 'type' => 'string'),
			array( 'field' => 'timeFactFinish', 'label' => 'ВремяДата фактического окончания', 'rules' => '', 'type' => 'string'),
			array( 'field' => 'loadSelectSmp', 'label' => '', 'rules' => '', 'type' => 'boolean', ),
			array( 'field' => 'showCurrentTeamsByFact', 'label' => '', 'rules' => '', 'type' => 'boolean', ),
			array( 'field' => 'EmergencyTeamSpec_id', 'label' => 'Профиль бригады', 'rules' => '', 'type' => 'int'),
			array( 'field' => 'Lpu_ids', 'label' => 'Идентификаторы МО', 'rules' => '', 'type' => 'string')
		),
		'loadUnfinishedEmergencyTeamList'=>array(
			array( 'field'	=> 'EmergencyTeam_id', 'label'	=> 'Идентификатор бригады', 'rules'	=> '', 'type' => 'id' ),
		),
		'saveEmergencyTeamsIsComingToWorgFlag'=>array(
			array('field' => 'selectedEmergencyTeamIds', 'label' => 'Список идентификаторов бригад, вышедших на смену', 'rules' => 'required', 'type' => 'string' )
		),
        'getCmpCallCardTrackPlayParams'=>array(
            array('field' => 'CmpCallCard_id', 'label' => 'Идентификатор вызова', 'rules' => 'required', 'type' => 'id' ),
            array('field' => 'EmergencyTeam_id', 'label' => 'Идентификатор бригады', 'rules' => 'required', 'type' => 'id' ),
        ),
		'checkLunchTimeOut'=>array(
			array('field' => 'EmergencyTeam_id', 'label' => 'Идентификатор бригады', 'rules' => 'required', 'type' => 'id')
		),
		'getDefaultPhoneNumber'=>array(
			array('field' => 'EmergencyTeam_HeadShift', 'label' => 'Идентификатор старшего врача', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MedProductCard_id', 'label' => 'Идентификатор авто', 'rules' => 'required', 'type' => 'id')
		),
		'loadEmergencyTeamOperEnvForInteractiveMap'=>array(
			array( 'field'	=> 'EmergencyTeamStatus_id', 'label'	=> 'Идентификатор бригады', 'rules'	=> '', 'type' => 'id' ),
		),
		'getEmergencyTeam' => array(
			array(
				'field' => 'EmergencyTeam_id',
				'label' => 'Ид бригады',
				'rules' => 'required',
				'type'  => 'id'
			)
		),
		'loadOutfitsARMCenterDisaster' => array(
			array( 'field' => 'dateStart', 'label' => 'Дата начала поиска смен', 'rules' => '', 'type' => 'string', ), 
			array( 'field' => 'dateFinish', 'label' => 'Дата окончания поиска смен', 'rules' => '', 'type' => 'string', ),
			array( 'field' => 'dateFactStart', 'label' => 'Дата фактического начала', 'rules' => '', 'type' => 'date'),
			array( 'field' => 'dateFactFinish', 'label' => 'Дата фактического окончания', 'rules' => '', 'type' => 'date'),
			array( 'field' => 'timeFactStart', 'label' => 'Время фактического начала', 'rules' => '', 'type' => 'string'),
			array( 'field' => 'timeFactFinish', 'label' => 'ВремяДата фактического окончания', 'rules' => '', 'type' => 'string'),
			array( 'field' => 'Lpu_ids', 'label' => 'Идентификаторы МО', 'rules' => '', 'type' => 'string')
		)
	);


	/**
	 * @desc Инициализация
	 * 
	 * @return void
	 */
	public function __construct(){
		parent::__construct();
		
		$this->load->database();
		$this->load->model('EmergencyTeam_model4E', 'dbmodel');
	}
	
	
	/**
	 * @desc Сохранение бригады СМП
	 * 
	 * @return bool
	 */
	public function saveEmergencyTeam() {
		$data = $this->ProcessInputData( 'saveEmergencyTeam', true );
		if ( $data === false ) {
			return false;
		}
		
		$response = $this->dbmodel->saveEmergencyTeam( $data );		

		$this->ProcessModelSave( $response, true, 'Ошибка при сохранении бригады СМП' )->ReturnData();

		return true;
	}
	
	
	/**
	 * @desc Возвращает данные по оперативной обстановке бригад СМП
	 * 
	 * @return bool
	 */
	public function loadEmergencyTeamOperEnv(){
		$data = $this->ProcessInputData( 'loadEmergencyTeamOperEnv', true );
		if ( $data === false ) {
			return false;
		}
		
		//
		// Получаем список нарядов
		//
		
		$response = $this->dbmodel->loadEmergencyTeamOperEnv( $data );
		
		//
		// Связываем автомобили с реальными наименованиями из геосервиса
		//
		
		$region = getRegionNick();
		if (is_array($response) && $region == 'pskov') {
		
			$get_geoservice_transport_list_result = $this->_getGeoserviceTransportList($data);
			if (!$this->dbmodel->isSuccessful($get_geoservice_transport_list_result)) {
				$result = $get_geoservice_transport_list_result;
				$this->ProcessModelSave($result,true)->ReturnData(); 
				return false;
			}
			$response = $this->_mergeEmergencyTeamListWithTransportList($response,$get_geoservice_transport_list_result);
		}
		
		$this->ProcessModelList( $response, true, true )->ReturnData();
		
		return true;
	}
	
	/**
	 * @desc Возвращает данные по оперативной обстановке бригад СМП для текущей подстанции
	 * 
	 * @return bool
	 */
	public function loadEmergencyTeamOperEnvForSmpUnit(){
		$data = $this->ProcessInputData( 'loadEmergencyTeamOperEnvForSmpUnit', true );
		if ( $data === false ) {
			return false;
		}

		$params = isset( $data[ 'session' ][ 'CurMedService_id' ] ) ? array( 'MedService_id' => $data[ 'session' ][ 'CurMedService_id' ] ) : array();
		$params[ 'CurArmType' ] = isset( $data[ 'session' ][ 'CurArmType' ] ) ? $data[ 'session' ][ 'CurArmType' ] : null;
        $params['Lpu_id'] = $data['Lpu_id'];
        $params['pmUser_id'] = $data['pmUser_id'];
		//
		// Получаем список нарядов
		//
		
		$response = $this->dbmodel->loadEmergencyTeamOperEnvForSmpUnit( $params );
		
		//
		// Связываем автомобили с реальными наименованиями из геосервиса
		//
		
		$region = getRegionNick();
		if (is_array($response) && $region == 'pskov') {
		
			$get_geoservice_transport_list_result = $this->_getGeoserviceTransportList($data);
			if (!$this->dbmodel->isSuccessful($get_geoservice_transport_list_result) && !empty($get_geoservice_transport_list_result)) {
				$result = $get_geoservice_transport_list_result;
				$this->ProcessModelSave($result,true)->ReturnData(); 
				return false;
			}
			$response = $this->_mergeEmergencyTeamListWithTransportList($response,$get_geoservice_transport_list_result);
		}
		
		//привязка данных тнц на уфе
		if (is_array($response) && isset($response[0]) && !isset($response[0]['success']) && $region == 'ufa') {
			
			//фильтр для выборки трекеров
			$tncfilter = array();
			foreach ($response as $f) {
				if($f['GeoserviceTransport_id'] != NULL)
				$tncfilter[] = $f['GeoserviceTransport_id'];
			}
			if(count($tncfilter) > 0){
			$get_geoservice_transport_list_result = $this->_getGeoserviceTransportList($data, $tncfilter);
			
			if ($this->dbmodel->isSuccessful($get_geoservice_transport_list_result)) {
				$result = $get_geoservice_transport_list_result;
				$response = $this->_mergeEmergencyTeamListWithTransportList($response,$get_geoservice_transport_list_result);
			}
			}
			
		}
		
		$this->ProcessModelList( $response, true, true )->ReturnData();
		
		return true;
	}
	
	
	/**
	 * @desc Возвращает данные по оперативной обстановке бригад СМП для текущей подстанции
	 * 
	 * @return bool
	 */
	public function loadEmergencyTeamOperEnvForSmpUnitsNested(){
		$data = $this->ProcessInputData( 'loadEmergencyTeamOperEnvForSmpUnitsNested', true );
		if ( $data === false ) {
			return false;
		}
		
		$response = $this->dbmodel->loadEmergencyTeamOperEnvForSmpUnitsNested( $data );

		$this->ProcessModelList( $response, true, true )->ReturnData();
		
		return true;
	}	
	
	/**
	 * @desc Возвращает шаблоны бригад
	 * 
	 * @return bool
	 */
	public function loadEmergencyTeamTemplateList(){
		$data = $this->ProcessInputData( 'loadEmergencyTeamTemplateList', true );
		if ( $data === false ) {
			return false;
		}
		
		$response = $this->dbmodel->loadEmergencyTeamTemplateList( $data );

		$this->ProcessModelList( $response, true, true )->ReturnData();
		
		return true;
	}	
	
	/**
	 * @desc Возвращает шаблоны бригад
	 * 
	 * @return bool
	 */
	public function getEmergencyTeamTemplatesNames(){
		$data = $this->ProcessInputData( 'getEmergencyTeamTemplatesNames', true );
		if ( $data === false ) {
			return false;
		}
		
		$response = $this->dbmodel->getEmergencyTeamTemplatesNames( $data );

		$this->ProcessModelList( $response, true, true )->ReturnData();
		
		return true;
	}
	
	/**
	 * @desc Возвращает список дежурств по выбранному наряду
	 * 
	 * @return bool
	 */
	public function loadEmergencyTeamVigils(){
		$data = $this->ProcessInputData( 'loadEmergencyTeamVigils', true );
		if ( $data === false ) {
			return false;
		}
		
		$response = $this->dbmodel->loadEmergencyTeamVigils( $data );

		$this->ProcessModelList( $response, true, true )->ReturnData();
		
		return true;
	}
	
	/**
	 * @desc Возвращает дежурство
	 * 
	 * @return bool
	 */
	public function loadSingleEmergencyTeamVigil(){
		$data = $this->ProcessInputData( 'loadSingleEmergencyTeamVigil', true );
		if ( $data === false ) {
			return false;
		}
		
		$response = $this->dbmodel->loadSingleEmergencyTeamVigil( $data );

		$this->ProcessModelList( $response, true, true )->ReturnData();
		
		return true;
	}
	
	/**
	 * @desc Сохраняет дежурство
	 * 
	 * @return bool
	 */
	public function saveEmergencyTeamVigil(){

		$data = $this->ProcessInputData( 'saveEmergencyTeamVigil', true );
		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->saveEmergencyTeamVigil( $data );
		$this->ProcessModelList( $response, true )->ReturnData();

		return true;
	}		
	
	/**
	 * @desc Метод изменения времени дежурства с сохранением ост. параметров
	 * 
	 * @return bool
	 */
	public function editEmergencyTeamVigilTimes(){

		$data = $this->ProcessInputData( 'editEmergencyTeamVigilTimes', false );
		
		$data['pmUser_id'] = $_SESSION['pmuser_id'];
		
		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->editEmergencyTeamVigilTimes( $data );
		$this->ProcessModelList( $response, true )->ReturnData();

		return true;
	}	
	
	/**
	 * @desc Удаляет дежурство
	 * 
	 * @return bool
	 */
	public function deleteEmergencyTeamVigil(){

		$data = $this->ProcessInputData( 'deleteEmergencyTeamVigil', true );
		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->deleteEmergencyTeamVigil( $data );
		$this->ProcessModelList( $response, true )->ReturnData();

		return true;
	}
	
	/**
	 * @desc Возвращает данные по оперативной обстановке бригад СМП для текущей подстанции арм ЦМК
	 * 
	 * @return bool
	 */
	public function loadEmergencyTeamsARMCenterDisaster(){
		$data = $this->ProcessInputData( 'loadEmergencyTeamsARMCenterDisaster', true );
		if ( $data === false ) {
			return false;
		}
		
		$response = $this->dbmodel->loadEmergencyTeamsARMCenterDisaster( $data );

		$this->ProcessModelList( $response, true, true )->ReturnData();
		
		return true;
	}		
	
	/**
	 * @desc Возвращает данные по оперативной обстановке бригад СМП для текущей подстанции арм ЦМК
	 * 
	 * @return bool
	 */
	public function loadCmpCallCardsARMCenterDisaster(){
		$data = $this->ProcessInputData( 'loadCmpCallCardsARMCenterDisaster', true );
		if ( $data === false ) {
			return false;
		}
		
		$response = $this->dbmodel->loadCmpCallCardsARMCenterDisaster( $data );

		$this->ProcessModelList( $response, true, true )->ReturnData();
		
		return true;
	}	
	
	/**
	 * @desc Возвращает кол-во врачей, бригад, вызовов для арма ЦМК
	 * 
	 * @return bool
	 */
	public function getCountsTeamsCallsAndDocsARMCenterDisaster(){
		$data = $this->ProcessInputData( 'getCountsTeamsCallsAndDocsARMCenterDisaster', true );
		if ( $data === false ) {
			return false;
		}
		
		$response = $this->dbmodel->getCountsTeamsCallsAndDocsARMCenterDisaster( $data );

		$this->ProcessModelList( $response, true, true )->ReturnData();
		
		return true;
	}
	
	
	/**
	 * @desc Изменяет заданную дату и время начала и окончания смены
	 * 
	 * @return bool
	 */
	public function editEmergencyTeamDutyTime(){
		$data = $this->ProcessInputData( 'editEmergencyTeamDutyTime', true );
		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->editEmergencyTeamDutyTime( $data );

		$this->ProcessModelSave( $response, true )->ReturnData();

		return true;
	}
	
	/**
	 * @desc Сохраняет смены бригад
	 * 
	 * @return bool
	 */
	public function saveEmergencyTeamDutyTime(){
		$data = $this->ProcessInputData( 'saveEmergencyTeamDutyTime', true );
		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->saveEmergencyTeamDutyTime( $data );

		$this->ProcessModelList( $response, true )->ReturnData();

		return true;
	}
	
	/**
	 * @desc Сохраняет бригады
	 * 
	 * @return bool
	 */
	public function saveEmergencyTeams(){

		$data = $this->ProcessInputData( 'saveEmergencyTeams', true );
		if ( $data === false ) {
			return false;
		}
		
		//проверяем не закрыта ли бригада
		$response = $this->dbmodel->checkOpenEmergencyTeam($data["EmergencyTeams"][0]["EmergencyTeam_id"]);		

		if (is_array($response) && (count($response) > 0)){
			$this->ProcessModelList( $response, true )->ReturnData();
		}else{
			$response = $this->dbmodel->saveEmergencyTeams( $data );
			$this->ProcessModelList( $response, true )->ReturnData();
		}				

		return true;
	}
	
	/**
	 * @desc Разделяет 1 бригаду на 2
	 * 
	 * @return bool
	 */
	public function saveEmergencyTeamsSplit(){

		$data = $this->ProcessInputData( 'saveEmergencyTeams', true );
		if ( $data === false ) {
			return false;
		}
		
		// 1 изменяем у исходного наряда окончание смены значением "применить изменения с"
		
		$firstEmergencyTeam = $data["EmergencyTeams"][0];
		
		$firstEmergencyTeam["EmergencyTeamDuty_DTFinish"] = $firstEmergencyTeam['applyChangesFrom'];
		
		$this->dbmodel->saveEmergencyTeamDutyTime( array(
			'EmergencyTeamsDutyTimes' => json_encode( array( $firstEmergencyTeam ) ),
			'pmUser_id' => $data[ 'pmUser_id' ]
		) );
		
		// 2 и создаем новый наряд
		$data["EmergencyTeams"][0]["EmergencyTeam_id"] = null;
		$data["EmergencyTeams"][0]["EmergencyTeamDuty_DTStart"] = $data["EmergencyTeams"][0]['applyChangesFrom'];
		
		$response = $this->dbmodel->saveEmergencyTeams( $data );
		$this->ProcessModelList( $response, true )->ReturnData();

		return true;
	}
	
	/**
	 * Установка отметок о выходе на смену
	 */
	
	public function saveEmergencyTeamsIsComingToWorgFlag(){
		$data = $this->ProcessInputData( 'saveEmergencyTeamsIsComingToWorgFlag', true );
		if ( $data === false ) {
			return false;
		}
		
		$ET_Array = json_decode($data['selectedEmergencyTeamIds'],true);
		
		$this->db->trans_begin();
		if (is_array($ET_Array) && sizeof($ET_Array)>0) {
			foreach ($ET_Array as $ET_Data) {
				if (!empty($ET_Data['EmergencyTeam_id']) && !empty($ET_Data['EmergencyTeamDuty_id']) ) {
					$response = $this->dbmodel->setEmergencyTeamWorkComing(array(
						'EmergencyTeam_id'=>$ET_Data['EmergencyTeam_id'],
						'EmergencyTeamDuty_id'=>$ET_Data['EmergencyTeamDuty_id'],
						'pmUser_id'=>$data['pmUser_id'],
						'EmergencyTeamDuty_isComesToWork'=>2
					));
					if (!$response || strlen($response[0]['Error_Msg'])>0 ) {
						$this->db->trans_rollback();
						$this->ProcessModelSave( $response, true )->ReturnData();
						return true;
					}
				} else {
					$response = array(array('success'=>false,'Error_Msg'=>'Не верно сформированы данные. Обратитесь к администратору'));
					$this->db->trans_rollback();
					$this->ProcessModelSave( $response, true )->ReturnData();
					return true;
				}
			}
		} else {
			$response = array(array('success'=>false,'Error_Msg'=>'Не выбрана ни одной новой отметки о выходе или не верно сформированы данные'));
			$this->db->trans_rollback();
			$this->ProcessModelSave( $response, true )->ReturnData();
			return true;
		}
		$this->ProcessModelSave( $response, true )->ReturnData();
		$this->db->trans_commit();
		return true;
	}
	
	/**
	 * @desc Сохраняет заданную дату и время начала и окончания смены
	 * 
	 * @return bool
	 */
	public function deleteEmergencyTeamDutyTime(){
		$data = $this->ProcessInputData( 'deleteEmergencyTeamDutyTime', true );
		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->deleteEmergencyTeamDutyTime( $data );

		$this->ProcessModelSave( $response, true )->ReturnData();

		return true;
	}
	
	/**
	 * @desc Удаляет заданную дату и время начала и окончания у смен
	 * 
	 * @return bool
	 */
	public function deleteEmergencyTeamDutyTimeList(){
		$data = $this->ProcessInputData( 'deleteEmergencyTeamDutyTimeList', true );
		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->deleteEmergencyTeamDutyTimeList( $data );
		//$this->ProcessModelSave( $response, true )->ReturnData();
		$this->ReturnData($response);
		return true;
	}
	
	
	
	/**
	 * @desc Возвращает данные по оперативной обстановке бригад СМП
	 * 
	 * @return bool
	 */
	public function loadDispatchOperEnv(){
		/*
		// У нас пока нет входящих данных
		$data = $this->ProcessInputData( 'loadDispatchOperEnv', true );
		if ( $data === false ) {
			return false;
		}
		*/
		
		
		$data = $this->ProcessInputData( 'loadDispatchOperEnv', true );
		
		$this->load->helper('NodeJS');
		$params = array('action'=>'getOnlineUsersDNDV'
			,'Lpu_id'=>$data['Lpu_id']
		);
		array_walk($params, 'ConvertFromWin1251ToUTF8');

		$postSendResult = NodePostRequest($params);
		if ($postSendResult[0]['success']==true) {
			//нод жив
			$responseData = json_decode($postSendResult[0]['data'],true);
			
			$response = $this->dbmodel->loadDispatchOperEnv($data);
			
			if ($responseData["success"]===true) {
				
				$restextend = $response;
				foreach ($response as $key => $value)
				{					
					$result = array_intersect($restextend[$key], $responseData['data']);
					$restextend[$key]['online'] = false;
					if (count($result))
					{
						$restextend[$key]['online'] = true;
					}
				}
				$this->ProcessModelList( $restextend, true, true )->ReturnData();
			} else {
				//$this->ProcessModelList(array(0=>array('success'=>false,'Err_Msg'=>'Нет онлайновых пользователей')), true)->ReturnData();
				//$response = $this->dbmodel->loadDispatchOperEnv($data);
				$this->ProcessModelList( $response, true, true )->ReturnData();
			} 
		} else {
			//нод мертв
			$response = $this->dbmodel->loadDispatchOperEnv($data);
			$this->ProcessModelList( $response, true, true )->ReturnData();
			//$this->ProcessModelSave($postSendResult, true)->ReturnData();
		}
	
		//$data = array();
		
		//$response = $this->dbmodel->loadDispatchOperEnv( $data );
	
		//$this->ProcessModelList( $response, true, true )->ReturnData();
		
		return true;
	}
	
	
	/**
	 * @desc Возвращает укладку
	 * 
	 * @return bool
	 */
	public function loadEmergencyTeamDrugsPack(){				
		$data = $this->ProcessInputData('loadEmergencyTeamDrugsPack', true);
		if ($data) {
			$response = $this->dbmodel->loadEmergencyTeamDrugsPack($data);
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}	
		
	}
	
	
	/**
	 * @desc Возвращает данные указанной бригады
	 * 
	 * @return bool
	 */
	public function loadEmergencyTeam(){
		$data = $this->ProcessInputData( 'loadEmergencyTeam', true );
		
		if ( $data === false ) {
			return false;
		}
		
		$response = $this->dbmodel->loadEmergencyTeam( $data );

		$this->ProcessModelList( $response, true )->ReturnData();

		return true;
	}
	
	/**
	 * Получение списка автомобилей (шаблонов бригад)
	 * @return boolean
	 */
	public function loadEmergencyTeamAutoList()
	{
		
		$data = $this->ProcessInputData( 'loadEmergencyTeamAutoList', true );
		
		if ( $data === false ) {
			return false;
		}
		
		//
		// 1. Проверяем наличие LpuBuilding_id
		//
		
		//Автомобили привязаны к подстанции (LpuBuilding_id).
		//Если LpuBuilding_id не передано в параметрах, будем получать его по
		//переданному MedService_id или по CurMedService_id из сессии, если служба(MedService_id) не передана в параметрах
        if (empty($data['viewAllMO']) ) {
            if (empty($data['LpuBuilding_id']) ) {
                $get_lpu_building_result = $this->_getLpuBuildingByMedService($data);
                if (!$this->dbmodel->isSuccessful($get_lpu_building_result)) {
                    $this->ProcessModelList( $get_lpu_building_result, true )->ReturnData();
                    return false;
                }
                $data['LpuBuilding_id'] = $get_lpu_building_result[0]['LpuBuilding_id'];
            }
        }
		
		//
		// 2. Получаем список автомобилей
		//
		
		$data['Template'] = 2;
		$get_emergency_team_template_list_result = $this->dbmodel->loadEmergencyTeamList($data);
		if (!$this->dbmodel->isSuccessful($get_emergency_team_template_list_result)) {
			$result = $get_emergency_team_template_list_result;
			$this->ProcessModelSave($result,true)->ReturnData(); 
			return false;
		}
		
		
		//
		// 3. Связываем автомобили с реальными наименованиями из геосервиса
		//
		
		//фильтр для выборки трекеров
		$tncfilter = array();
		foreach ($get_emergency_team_template_list_result as $f) {
			if($f['GeoserviceTransport_id'] != NULL)
			$tncfilter[] = $f['GeoserviceTransport_id'];
		}		

		$get_geoservice_transport_list_result = $this->_getGeoserviceTransportList($data, $tncfilter);
		if (!$this->dbmodel->isSuccessful($get_geoservice_transport_list_result)) {
			// Если не удалось получить данные из геосервиса, все равно выводим автомобили из БД
			$get_geoservice_transport_list_result = array();
			/*
			$result = $get_geoservice_transport_list_result;
			$this->ProcessModelSave($result,true)->ReturnData(); 
			return false;
			*/
		}
		
		$result = $this->_mergeEmergencyTeamListWithTransportList($get_emergency_team_template_list_result,$get_geoservice_transport_list_result);
		
		$this->ProcessModelList($result,true,true)->ReturnData(); 
		return true;
	}
	
	/**
	* Метод объединения массива нарядов (бригад) и списка транспортных средств геосервиса
	*/
	protected function _mergeEmergencyTeamListWithTransportList($emergency_team_list = array(), $transport_list = array() ) {
		
		if (!is_array($emergency_team_list) || !is_array($transport_list)) {
			return false;
		}
		
		foreach ($emergency_team_list as $e => $emergency_team) {
			/*
			if (empty($emergency_team['GeoserviceTransport_id'])) {
				continue;
			}
			*/
			foreach ($transport_list as $t => $transport) {
				if (empty($transport['GeoserviceTransport_id'])) {
					continue;
				}
				if ($transport['GeoserviceTransport_id'] == $emergency_team['GeoserviceTransport_id']) {
					$emergency_team['GeoserviceTransport_name'] = (!empty($transport['GeoserviceTransport_name'])) ? $transport['GeoserviceTransport_name'] : null;
					$emergency_team['groups'] = (!empty($transport['groups'])) ? $transport['groups'] : null;
				}
			}
			$emergency_team_list[$e] = $emergency_team;
		}
		
		return $emergency_team_list;
		
	}
	
	/**
	 * Метод получения списка транспортных средств геосервиса
	 * @param type $data
	 * @return type
	 */
	protected function _getGeoserviceTransportList($data, $filter = null)
	{
		$region = getRegionNick();
	
		$result = array();
		
		if ($region == 'ufa') {
			
			$result = $this->_getTNCTransportList($data, $filter);
			
		} else {
			
			$result = $this->_getWialonTransportList($data);
		}
		
		return $result;
		
	}
	/**
	 * Метод получения списка транспортных средств из геосервиса ТНЦ
	 * 
	 * @param type $data
	 * @return array
	 */
	protected function _getTNCTransportList($data, $filter = null)
	{
		$response = array();
		$this->load->model('TNC_model');
		$cars = $this->TNC_model->getTransportList( false, $filter );
		
		if (is_array($cars) && (!isset($cars[0]['success'])) ) {
			foreach ($cars as $car) {
				$response[] = array(
					'GeoserviceTransport_name' => $car['name'],
					'GeoserviceTransport_id' => $car['id'],
					'groups' => $car['groupCode']
				);
			}
		}
		
		
		return $response;
	}
	
	/**
	 * Метод получения списка транспортных средств из геосервиса Wialon
	 * @param type $data
	 * @return type
	 */
	protected function _getWialonTransportList($data) {
		$response = array();
		
		$cars = array();

		$this->load->model('Wialon_model');

		try {
			$cars = $this->Wialon_model->getAllAvlUnitsForMergeData();
		} catch ( Exception $e ) {}

		if (is_array($cars)) {
			foreach ($cars as $car) {
				$response[] = array(
					'GeoserviceTransport_name' => $car['nm'],
					'GeoserviceTransport_id' => $car['id'],
					'groups'=>( !empty( $car[ 'ugs' ] ) ) ? $car[ 'ugs' ] : array(), 
				);
			}
		}
			
		return $response;
	}
	
	/**
	 * @desc Возвращает данные всех бригад ЛПУ
	 * 
	 * @return bool
	 */
	public function loadEmergencyTeamList(){
		$data = $this->ProcessInputData( 'loadEmergencyTeamList', true );
		
		if ( $data === false ) {
			return false;
		}
		
		$response = $this->dbmodel->loadEmergencyTeamList( $data );

		$region = getRegionNick();
		if (is_array($response) && $region == 'perm') {
			$this->load->model('Wialon_model');
			$cars = $this->Wialon_model->getAllAvlUnitsForMergeData();
			if (is_array($cars)) {
				for ($i=0;$i<sizeof($response);$i++) {
					for ($k=0;$k<sizeof($cars) && $response["$i"]['GeoserviceTransport_name']=='';$k++) {
						if ($response["$i"]['GeoserviceTransport_id']==$cars["$k"]->id) {
							$response["$i"]['GeoserviceTransport_name'] = $cars["$k"]->nm;
						}
					}
				}
			}
			
		}
		$this->ProcessModelList( $response, true )->ReturnData();

		return true;
	}	
	
	/**
	* Получение идентификатора подстанции по идентификатору службы
	*/
	protected function _getLpuBuildingByMedService($data) {
	
		$this->load->model('CmpCallCard_model4E', 'ccc4model');
		
		$MedService_id = (!empty($data['MedService_id'])) ? $data['MedService_id'] : ( (!empty($data['session']['CurMedService_id']) ? $data['session']['CurMedService_id'] : false) );

		$CurArmType = $data['session']['CurArmType'];

		//особая группа
		if ( in_array($CurArmType, array('dispcallnmp', 'dispdirnmp')) ){

		}
		if (!$MedService_id && !in_array($CurArmType, array('dispcallnmp', 'dispdirnmp'))) {
			return $this->dbmodel->createError(false,'Невозможно получить идентификатор подстанции. Не передан идентификатор связанной службы');
		}

		$get_lpu_building_result = $this->ccc4model->getLpuBuildingBySessionData($data);

		if (empty($get_lpu_building_result[0]['LpuBuilding_id'])) {
			return $this->dbmodel->createError(false,'Невозможно получить идентификатор подстанции.');
		}

		return $get_lpu_building_result;
	}
	
	/**
	 * @desc Возвращает данные всех назначенных смен бригад ЛПУ
	 * @return bool
	 */
	public function loadEmergencyTeamShiftList(){
		$data = $this->ProcessInputData( 'loadEmergencyTeamShiftList', true );
		if ( $data === false ) {
			return false;
		}
		
		
		//
		// 1. Проверяем наличие LpuBuilding_id
		//
		
		//Автомобили привязаны к подстанции (LpuBuilding_id).
		//Если LpuBuilding_id не передано в параметрах, будем получать его по
		//переданному MedService_id или по CurMedService_id из сессии, если служба(MedService_id) не передана в параметрах
		$CurArmType = $data['session']['CurArmType'];
		if (empty($data['LpuBuilding_id']) && $CurArmType != 'zmk') {
			
			$get_lpu_building_result = $this->_getLpuBuildingByMedService($data);
			if (!$this->dbmodel->isSuccessful($get_lpu_building_result)) {
				$this->ProcessModelList( $get_lpu_building_result, true )->ReturnData();
				return false;
			}
			$data['LpuBuilding_id'] = $get_lpu_building_result[0]['LpuBuilding_id'];
		}


		//
		// 2. Получаем список бригад на смене
		// 
		
		$get_emergency_team_shift_list_result = $this->dbmodel->loadEmergencyTeamShiftList( $data );
		if (!$this->dbmodel->isSuccessful($get_emergency_team_shift_list_result)) {
			$this->ProcessModelList( $get_emergency_team_shift_list_result, true )->ReturnData();
			return false;
		}
		
		//
		// 3. Связываем автомобили с реальными наименованиями из геосервиса
		//
		$tncfilter = array();
		foreach ($get_emergency_team_shift_list_result as $f) {
			if($f['GeoserviceTransport_id'] != NULL)
			$tncfilter[] = $f['GeoserviceTransport_id'];
		}		
		
		$get_geoservice_transport_list_result = array();
		
		if(!empty($tncfilter)){
		$get_geoservice_transport_list_result = $this->_getGeoserviceTransportList($data, $tncfilter);
		}

		$result = $this->_mergeEmergencyTeamListWithTransportList($get_emergency_team_shift_list_result,$get_geoservice_transport_list_result);
		
		$this->ProcessModelList($result,true,true)->ReturnData(); 
		return true;
		
	}
	/**
	 * Получение списка бригад с незавершёнными сменами
	 * @return boolean
	 */
	public function loadUnfinishedEmergencyTeamList() {
		$data = $this->ProcessInputData( 'loadUnfinishedEmergencyTeamList', true );
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->loadUnfinishedEmergencyTeamList( $data );
		$this->ProcessModelList( $response, true, true )->ReturnData();
		return true;
	}

	/**
	 * @desc Удаляет бригаду СМП
	 * 
	 * @return bool
	 */
	public function deleteEmergencyTeam() {
		$data = $this->ProcessInputData( 'deleteEmergencyTeam', true );
		if ( $data === false ) {
			return false;
		}
		
		$response = $this->dbmodel->deleteEmergencyTeam( $data );
		
		$this->ProcessModelSave( $response, true, true )->ReturnData();

		return true;
	}
	
	/**
	 * @desc Удаляет бригады СМП
	 * 
	 * @return bool
	 */
	public function deleteEmergencyTeamList() {
		$data = $this->ProcessInputData( 'deleteEmergencyTeamList', true );
		if ( $data === false ) {
			return false;
		}
		
		$response = $this->dbmodel->deleteEmergencyTeamList( $data );
		$this->ReturnData($response);
		return true;
	}
	
	/**
	 * @desc Изменение статуса бригады СМП
	 * 
	 * @return bool
	 */
	function setEmergencyTeamStatus() {
		$data = $this->ProcessInputData( 'setEmergencyTeamStatus', true );
		if ( $data === false ) {
			return false;
		}
		
		/*получение арм_код из армлиста 
		и получение фрм_ид из базы*/
		/*
		require_once APPPATH.'controllers/User.php';
		$user = new User;
		
		$res = $user->getARMList();

		$result = $user->dbmodel->getARMinDB(array('ARMType_Code'=>$res[$data['ARMType']]['Arm_id']));
		
		$data['ARMType_id'] = $result[0]['ARMType_id'];
		*/
		$data['ARMType_id'] = !empty($_SESSION['CurARM']['ARMType_id']) ? $_SESSION['CurARM']['ARMType_id'] : null; //хотя такого не должно быть

		$response = $this->dbmodel->setEmergencyTeamStatus($data);		
		$this->ProcessModelSave( $response, true )->ReturnData();
		
		return true;
	}
	
	/**
	 * @desc Изменение статуса бригады СМП на предыдущий у вызова
	 * 
	 * @return bool
	 */
	function cancelEmergencyTeamFromCall() {
		$data = $this->ProcessInputData( 'cancelEmergencyTeamFromCall', true );
		if ( $data === false ) {
			return false;
		}
		
		$data['ARMType_id'] = $_SESSION['CurARM']['ARMType_id'];
		$response = $this->dbmodel->cancelEmergencyTeamFromCall($data);		
		$this->ProcessModelSave( $response, true )->ReturnData();
		
		return true;
	}
	
	
	/**
	 * @desc Получение списка смен указанной бригады для графика нарядов
	 * 
	 * @return bool
	 */
	function loadEmergencyTeamDutyTimeGrid() {
		$data = $this->ProcessInputData( 'loadEmergencyTeamDutyTimeGrid', true );
		if ( $data === false ) {
			return false;
		}
		
		$response = $this->dbmodel->loadEmergencyTeamDutyTimeGrid( $data );

		$this->ProcessModelMultiList( $response, true, true )->ReturnData();
		
		return true;
	}
	
	/**
	 * @desc Получение списка смен бригады данной ЛПУ для графика нарядов
	 * 
	 * @return bool
	 */
	function loadEmergencyTeamDutyTimeListGrid() {
		$data = $this->ProcessInputData( 'loadEmergencyTeamDutyTimeListGrid', true );
		if ( $data === false ) {
			return false;
		}
		
		$response = $this->dbmodel->loadEmergencyTeamDutyTimeListGrid( $data );

		$this->ProcessModelMultiList( $response, true, true )->ReturnData();
		
		return true;
	}
	
	
	/**
	 * @desc Отметка о выходе или невыходе на работу
	 * 
	 * @return bool
	 */
	public function setEmergencyTeamWorkComing() {
		$data = $this->ProcessInputData( 'setEmergencyTeamWorkComing', true );
		if ( $data === false ) {
			return false;
		}
		
		$response = $this->dbmodel->setEmergencyTeamWorkComing( $data );
		
		$this->ProcessModelSave( $response, true, true )->ReturnData();

		return true;
	}
	
	
	/**
	 * @desc Отметка о выходе или невыходе на работу и время
	 * 
	 * @return bool
	 */
	public function setEmergencyTeamsWorkComingList() {
		$data = $this->ProcessInputData( 'setEmergencyTeamsWorkComingList', true );
		if ( $data === false ) {
			return false;
		}
		
		$response = $this->dbmodel->setEmergencyTeamsWorkComingList( $data );
		
		$this->ProcessModelList( $response, true, true )->ReturnData();

		return true;
	}
	
	
	/**
	 * @desc Отметка о закрытии смен
	 * 
	 * @return bool
	 */
	public function setEmergencyTeamsCloseList() {
		$data = $this->ProcessInputData( 'setEmergencyTeamsCloseList', true );
		if ( $data === false ) {
			return false;
		}
		
		$response = $this->dbmodel->setEmergencyTeamsCloseList( $data );
		
		$this->ProcessModelList( $response, true, true )->ReturnData();

		return true;
	}
	
	
	/**
	 * @desc описание
	 */
	function printCloseDuty() {		
		$this->load->library('parser');

		$data = $this->ProcessInputData('printCloseDuty', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadEmergencyTeamDutyTimeListGrid($data);
		
		//$this->load->model("LpuStructure_model", "lsmodel");
		//$bname = $this->lsmodel->getLpuBuildingList($data);
		
		if ( (!is_array($response)) || (count($response) == 0) ) {
			echo 'Ошибка.';
			return true;
		} 
						
		$parse_data = array(
			'resp' => $response
		//	'buildings' => $bname			
		);
		
		$this->parser->parse('print_formCloseDuty', $parse_data);
	}
	
	
	/**
	 * @desc Возращает список для справочника списка бригад СМП
	 * 
	 * @return JSON
	 */
    function loadEmergencyTeamCombo(){
		$data = $this->ProcessInputData( 'loadEmergencyTeamCombo', true, true );
		if ( $data === false ) {
			return false;
		}
		
		$response = $this->dbmodel->loadEmergencyTeamCombo( $data );
		
		$this->ProcessModelList( $response, true, true )->ReturnData();
		
		return true;
    }
	
    /**
	 * @desc Возращает список для справочника списка бригад СМП с идентификаторами Wialon
	 * 
	 * @return JSON
	 */
    public function loadEmergencyTeamComboWithWialonID(){
    	$data = $this->ProcessInputData( 'loadEmergencyTeamComboWithWialonID', true, true );
		if ( $data === false ) {
			return false;
		}
		$response = $this->dbmodel->loadEmergencyTeamComboWithWialonID( $data );
		$this->ProcessModelList( $response, true, true )->ReturnData();
		
		return true;
    }
	
	/**
	 * @desc Возращает систорию статусов бригады СМП
	 * 
	 * @return JSON
	 */
    function loadEmergencyTeamStatusesHistory(){
		$data = $this->ProcessInputData( 'loadEmergencyTeamStatusesHistory', true );
		if ( $data === false ) {
			return false;
		}
		
		$response = $this->dbmodel->loadEmergencyTeamStatusesHistory( $data );
		
		$this->ProcessModelList( $response, true, true )->ReturnData();
		
		return true;
    }
	
	
	/**
	 * @desc Получение списка правил, описывающих логику предложения бригад на вызов
	 * 
	 * @return JSON
	 */
    function getEmergencyTeamProposalLogic(){
		$data = $this->ProcessInputData( 'getEmergencyTeamProposalLogic', true, true );
		if ( $data === false ) {
			return false;
		}
		$response = $this->dbmodel->getEmergencyTeamProposalLogic( $data );
		$this->ProcessModelMultiList( $response, true, true )->ReturnData();
		return true;
    }
	
	
	/**
	 * @desc Получение списка возможных статусов бригады
	 * в последствии будет дорабатываться
	 * 
	 * @return JSON
	 */
    function loadEmergencyTeamStatuses(){
		$data = $this->ProcessInputData( 'loadEmergencyTeamStatuses', true, true );
		if ( $data === false ) {
			return false;
		}
		$response = $this->dbmodel->loadEmergencyTeamStatuses( $data );
		$this->ProcessModelList( $response, true, true )->ReturnData();
		return true;
    }
	
	
	
	/**
	 * @desc Получение последовательноси профилей бригад в том порядке, в котором они будут предлагаться по определенному правилу
	 * 
	 * 
	 * @return JSON
	 */
    function getEmergencyTeamProposalLogicRuleSpecSequence(){
		$data = $this->ProcessInputData( 'getEmergencyTeamProposalLogicRuleSpecSequence', true, true );
		if ( $data === false ) { 
			return false;
		}
		$response = $this->dbmodel->getEmergencyTeamProposalLogicRuleSpecSequence( $data );
		$this->ProcessModelMultiList( $response, true, true )->ReturnData();
		return true;
    }
	
	/**
	* @desc Сохранение правли для логики предложения бригады на вызов
	* 
	* @return bool
	*/
	public function saveEmergencyTeamProposalLogicRule() {
		$data = $this->ProcessInputData( 'saveEmergencyTeamProposalLogicRule', true );
		if ( $data === false ) {
			return false;
		}
		
		$sequence = json_decode($data['EmergencyTeamProposalLogicRule_Sequence'],true);
		
		$responseRule = $this->dbmodel->saveEmergencyTeamProposalLogicRule( $data );
		if ( (!$responseRule)||(count($responseRule)==0 )||(!isset($responseRule[0]['EmergencyTeamProposalLogic_id'])) ) {
			$this->ProcessModelSave( $responseRule, true, true )->ReturnData();
		} else {
			$continue =true;
			$EmergencyTeamProposalLogic_id = $responseRule[0]['EmergencyTeamProposalLogic_id'];
			for ($i=0;$i<count($sequence)&&$continue;$i++) {
				$ruleData = array();
				$ruleData['pmUser_id'] = $data['pmUser_id'];
				$ruleData['EmergencyTeamProposalLogic_id'] = $EmergencyTeamProposalLogic_id;
				$ruleData['EmergencyTeamProposalLogicRule_SequenceNum'] = $i;
				$ruleData['EmergencyTeamSpec_id'] = $sequence[$i]['EmergencyTeamSpec_id'];
				$ruleData['EmergencyTeamProposalLogicRule_id'] = $sequence[$i]['EmergencyTeamProposalLogicRule_id'];
				$responseSequence = $this->dbmodel->saveEmergencyTeamProposalLogicRuleSequence( $ruleData );
				if (!isset($responseSequence[0]['EmergencyTeamProposalLogicRule_id'])){
					$this->ProcessModelSave( $responseSequence, true, true )->ReturnData();
					$continue = false;
				}
			}
			if ($continue) {
				$this->ProcessModelSave( $responseRule, true, true )->ReturnData();
			}
		}
		
		return true;
	}
	
	/**
	* @desc Описание хз функции
	* 
	* @return bool
	*/
	public function deleteEmergencyTeamProposalLogicRule() {
		$data = $this->ProcessInputData( 'deleteEmergencyTeamProposalLogicRule', true );
		if ( $data === false ) {
			return false;
		}
		$response = $this->dbmodel->deleteEmergencyTeamProposalLogicRule( $data );
		$this->ProcessModelSave( $response, true, true )->ReturnData();		
	}

	/**
	 * Получение параметров для проигрывания трека виалон
	 * @return bool
	 */
	public function getCmpCallCardTrackPlayParams() {
        $data = $this->ProcessInputData( 'getCmpCallCardTrackPlayParams', true );

        if ( $data === false ) {
            return false;
        }

        $response = $this->dbmodel->getCmpCallCardTrackPlayParams( $data );
        $this->ProcessModelList( $response, true )->ReturnData();

        return true;
    }

	/**
	 * Работа с установкой статусов блигады Ремонт\ремонт ТС
	 * @return bool
	 */
	public function setEmergencyTeamStatusRepair() {
		$data = $this->ProcessInputData('setEmergencyTeamStatus', true);
		if ($data === false) {
			return false;
		}

		$EmergencyTeam = $this->dbmodel->loadEmergencyTeam($data);
		$this->load->model('CmpCallCard_model4E', 'CmpCallCard_model4E');
		if ($EmergencyTeam[0]['EmergencyTeamStatus_Code'] == '36') {

			$data['ARMType_id'] = $_SESSION['CurARM']['ARMType_id'];

			//статус вызова = передано
			$data['CmpCallCardStatusType_id'] = 1;
			$this->CmpCallCard_model4E->setStatusCmpCallCard($data);

			//удалить связь между вызовом и бригадой
			$this->dbmodel->cancelEmergencyTeamFromCall($data);
			//устанавливаем статус бригады
			$EmergencyTeamStatus = $this->dbmodel->setEmergencyTeamStatus($data);
			$this->ProcessModelList($EmergencyTeamStatus, true)->ReturnData();

		} else {

			$data['ARMType_id'] = $_SESSION['CurARM']['ARMType_id'];

			$this->CmpCallCard_model4E->setStatusCmpCallCard(array(
				"CmpCallCard_id" => $data['CmpCallCard_id'],
				"CmpCallCardStatusType_Code" => 4,
				"pmUser_id" => $data["pmUser_id"]
			));

			//Копируем талон вызова
			$this->CmpCallCard_model4E->copyCmpCallCard($data);

			//Меняем статус бригады
			$EmergencyTeamStatus = $this->dbmodel->setEmergencyTeamStatus($data);



			$this->ProcessModelList($EmergencyTeamStatus, true)->ReturnData();
		}

		return true;
	}

	/**
	 * Проверка превышения времени обеда бригады
	 *
	 */
	function checkLunchTimeOut(){
		$data = $this->ProcessInputData('checkLunchTimeOut', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->checkLunchTimeOut($data);

		return $this->ProcessModelList($response, true)->ReturnData();
	}

	/**
	 * Номер телефона по умолчанию для наряда
	 */
	function getDefaultPhoneNumber(){
		$data = $this->ProcessInputData('getDefaultPhoneNumber', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->getDefaultPhoneNumber($data);
		return $this->ProcessModelList($response, true)->ReturnData();
	}

	/**
	 * Получение списка бригад для АРМ интерактивной карты СМП
	 */
	public function loadEmergencyTeamOperEnvForInteractiveMap() {
		$data = $this->ProcessInputData( 'loadEmergencyTeamOperEnvForInteractiveMap', true );
		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->loadEmergencyTeamOperEnvForInteractiveMap( $data );
		$this->ProcessModelList( $response, true, true )->ReturnData();

		return true;
	}

	/**
	 * Информация о бригаде в АРМе ЦМК
	 */
	public function getEmergencyTeam() {
		$data = $this->ProcessInputData( 'getEmergencyTeam', true );
		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->getEmergencyTeam( $data );
		$this->ProcessModelList( $response, true, true )->ReturnData();

		return true;
	}

	/**
	 * Загрузка раздела Наряды в АРМе ЦМК
	 */
	public function loadOutfitsARMCenterDisaster() {
		$data = $this->ProcessInputData( 'loadOutfitsARMCenterDisaster', true );
		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->loadOutfitsARMCenterDisaster( $data );
		$this->ProcessModelList( $response, true, true )->ReturnData();

		return true;
	}
}
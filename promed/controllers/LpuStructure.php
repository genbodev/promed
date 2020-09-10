<?php
/**
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package	  All
 * @access	   public
 * @copyright	Copyright (c) 2009 Swan Ltd.
 * @author	   Andrew Markoff
 * @version	  01.09.2009
 * @property LpuStructure_model $dbmodel
 */
class LpuStructure extends swController
{
	/**
	 * Это Doc-блок
	 */
	function __construct()
	{
		parent::__construct();

		$this->load->database();
		$this->load->model('LpuStructure_model', 'dbmodel');

		$this->inputRules = array(
			'loadLpuSectionCodeList' => array(
				array('field' => 'LpuSectionCode_begDate', 'label' => 'Дата начала действия', 'rules' => '', 'type' => 'date'),
				array('field' => 'LpuSectionCode_endDate', 'label' => 'Дата окончания действия', 'rules' => '', 'type' => 'date'),
				array('field' => 'LpuSectionProfile_id', 'label' => 'Профиль отделения', 'rules' => '', 'type' => 'id'),
				array('field' => 'LpuUnitType_id', 'label' => 'Тип группы отделений', 'rules' => 'required', 'type' => 'id')
			),
			'loadLpuSectionLpuSectionProfileGrid' => array(
				array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => 'required', 'type' => 'id')
			),
			'loadLpuSectionMedProductTypeLinkGrid' => array(
				array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => 'required', 'type' => 'id')
			),
			'getAllowedMedServiceTypes' => array(
				array('field' => 'MedServiceLevelType_id', 'label' => 'Уровень структурного элемента МО', 'rules' => 'required', 'type' => 'id')
			),
			'checkLpuSectionIsVMP' => array(
				array('field' => 'LpuSection_id', 'label' => 'Идентификатор подразделения', 'rules' => 'required', 'type' => 'id'),
			),
			'GetLpuSectionBedState' => array(
				array('field' => 'LpuSection_id', 'label' => 'Идентификатор подразделения', 'rules' => '', 'type' => 'id'),
				array('field' => 'LpuSectionBedState_id', 'label' => 'Статус койки', 'rules' => '', 'type' => 'id'),
				array('field' => 'is_Act', 'label' => 'Ис акт', 'rules' => 'trim', 'type' => 'string')
			),
			'getLpuUnitSetCombo' => array(
				array('field' => 'LpuUnitSet_id', 'label' => 'Идентификатор', 'rules' => '', 'type' => 'id'),
				array('field' => 'LpuUnitSet_IsCmp', 'label' => 'Признак СМП', 'rules' => '', 'type' => 'id')
			),
			'GetLpuStructure' => array(
				array('field' => 'LpuUnitType_id', 'label' => 'Тип группы отделений', 'rules' => '', 'type' => 'id'),
				array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => '', 'type' => 'id'),
				array('field' => 'from', 'label' => 'Форма', 'rules' => '', 'type' => 'string'),
				array('field' => 'level', 'label' => 'Уровень', 'rules' => '', 'type' => 'int'),
				array('field' => 'level_two', 'label' => 'Уровень (2)', 'rules' => '', 'type' => 'string'),
				array('field' => 'node', 'label' => 'Узел', 'rules' => '', 'type' => 'string'),
				array('field' => 'object', 'label' => 'Объект', 'rules' => '', 'type' => 'string'),
				array('field' => 'regionsOnly', 'label' => 'Признак "Только участки"', 'rules' => '', 'type' => 'string'),
				array('field' => 'deniedSectionsList', 'label' => 'Отделения МО которые ненадо показывать', 'rules' => '', 'type' => 'string'),
				array('field' => 'LpuBuildingPass_id', 'label' => 'Идентификатор здания МО', 'rules' => '', 'type' => 'id'),
				array('field' => 'LpuFilial_id', 'label' => 'Идентификатор филиала', 'rules' => '', 'type' => 'id')
			),
			'copyUslugaSectionList' => array(
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedService_id',
					'label' => 'Идентификатор службы',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_pid',
					'label' => 'Идентификатор отделения, услуги которого копируются',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getIsNoFRMP' => array(
			  array(
				  'field' => 'Lpu_id',
				  'label' => 'Идентификатор ЛПУ',
				  'rules' => 'required',
				  'type'  => 'id'
			  )
			),
			'ExportErmpStaff' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор ЛПУ',
					'rules' => '',
					'type'  => 'int'
				),
				array(
					'field' => 'ESESW_date',
					'label' => 'Дата',
					'rules' => '',
					'type'  => 'date'
				)
			),
			'SaveMedStaffRegion' => array(
				array(
					'field' => 'MedStaffRegion_id',
					'label' => 'Идентификатор врача на участке',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Врач',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'LpuRegion_id',
					'label' => 'Участок',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'MedStaffRegion_begDate',
					'label' => 'Дата начала',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'MedStaffRegion_endDate',
					'label' => 'Дата окончания',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'ЛПУ',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'checkPost',
					'label' => 'Признак проверки должности',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'checkLpuSection',
					'label' => 'Признак проверки отделения',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'checkStavka',
					'label' => 'Признак проверки ставки',
					'rules' => '',
					'type' => 'string'
				)
			),
			'SaveLpuBuildingStreet' => array(
				array(
					'field' => 'LpuBuildingStreet_id',
					'label' => 'Идентификатор улицы участка',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuBuildingStreet_HouseSet',
					'label' => 'Номера домов',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'KLCountry_id',
					'label' => 'Идентификатор страны',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'KLRGN_id',
					'label' => 'Идентификатор региона',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'KLSubRGN_id',
					'label' => 'Идентификатор субрегиона',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'KLCity_id',
					'label' => 'Идентификатор города',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'KLTown_id',
					'label' => 'Идентификатор населенного пункта',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'KLStreet_id',
					'label' => 'Идентификатор улицы',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuBuilding_id',
					'label' => '',
					'rules' => '',
					'type'  => 'id'
				),
				array('field' => 'LpuBuildingStreet_IsAll', 'label' => 'Вся указанная территориия', 'rules', 'type' => 'boolean', 'default' => '0'),
			),
			'SaveMedServiceStreet' => array(
				array(
					'field' => 'MedServiceStreet_id',
					'label' => 'Идентификатор улицы участка',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedServiceStreet_HouseSet',
					'label' => 'Номера домов',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'KLCountry_id',
					'label' => 'Идентификатор страны',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'KLRGN_id',
					'label' => 'Идентификатор региона',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'KLSubRGN_id',
					'label' => 'Идентификатор субрегиона',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'KLCity_id',
					'label' => 'Идентификатор города',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'KLTown_id',
					'label' => 'Идентификатор населенного пункта',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'KLStreet_id',
					'label' => 'Идентификатор улицы',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedService_id',
					'label' => '',
					'rules' => '',
					'type'  => 'id'
				),
				array(
					'field' => 'MedServiceStreet_isAll',
					'label' => 'Признак всей указанной территории',
					'rules' => '',
					'type' => 'string',
					'default' => 1
				),
				
			),
			'SaveLpuRegionStreet' => array(
				array(
					'field' => 'LpuRegionStreet_id',
					'label' => 'Идентификатор улицы участка',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuRegionStreet_HouseSet',
					'label' => 'Номера домов',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'KLCountry_id',
					'label' => 'Идентификатор страны',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'KLRGN_id',
					'label' => 'Идентификатор региона',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'KLSubRGN_id',
					'label' => 'Идентификатор субрегиона',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'KLCity_id',
					'label' => 'Идентификатор города',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'KLTown_id',
					'label' => 'Идентификатор населенного пункта',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'KLStreet_id',
					'label' => 'Идентификатор улицы',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuRegion_id',
					'label' => '',
					'rules' => '',
					'type'  => 'id'
				),
				array(
					'field' => 'LpuRegionStreet_IsAll',
					'label' => 'Вся указанная территориия',
					'rules' => '',
					'type' => 'string'
				),
			),
			'SaveUslugaSection' => array(
				array(
					'field' => 'UslugaSection_id',
					'label' => 'Идентификатор услуги в отделении',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Отделение',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Usluga_id',
					'label' => 'Услуга',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaPrice_ue',
					'label' => 'Цена (УЕТ)',
					'rules' => '',
					'type' => 'float'
				)
			),
			'SaveUslugaSectionTariff' => array(
				array(
					'field' => 'UslugaSectionTariff_id',
					'label' => 'Идентификатор тарифа на услуги',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaSection_id',
					'label' => 'Услуги на отделении',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaSectionTariff_Tariff',
					'label' => 'Тариф',
					'rules' => 'required|numeric|no_zero',
					'type' => 'float'
				),
				array(
					'field' => 'UslugaSectionTariff_begDate',
					'label' => 'Дата начала действия',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'UslugaSectionTariff_endDate',
					'label' => 'Дата окончания действия',
					'rules' => '',
					'type' => 'date'
				)
			),
			'SaveUslugaComplexTariff' => array(
				array(
					'field' => 'UslugaComplexTariff_id',
					'label' => 'Идентификатор тарифа на услуги',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Услуги на отделении',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaComplexTariff_Tariff',
					'label' => 'Тариф',
					'rules' => 'required|numeric|no_zero',
					'type' => 'float'
				),
				array(
					'field' => 'UslugaComplexTariff_begDate',
					'label' => 'Дата начала действия',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'UslugaComplexTariff_endDate',
					'label' => 'Дата окончания действия',
					'rules' => '',
					'type' => 'date'
				)
			),
			'SaveLpuSectionTariff' => array(
				array(
					'field' => 'LpuSectionTariff_id',
					'label' => 'Идентификатор тарифа на отделении',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Отделение',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'TariffClass_id',
					'label' => 'Вид тарифа',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSectionTariff_Tariff',
					'label' => 'Тариф на отделение',
					'rules' => 'required|numeric',
					'type' => 'float'
				),				
				array(
					'field' => 'LpuSectionTariff_TotalFactor',
					'label' => 'Итоговый коэффициент',
					'rules' => 'trim|numeric',
					'type' => 'float'
				),
				array(
					'field' => 'LpuSectionTariff_setDate',
					'label' => 'Дата начала действия',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'LpuSectionTariff_disDate',
					'label' => 'Дата окончания действия',
					'rules' => '',
					'type' => 'date'
				)
			),
			'SaveLpuSectionBedState' => array(
				array(
					'field' => 'LpuSectionBedState_CountOms',
					'label' => 'Количество коек оплачиваемых по ОМС',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuSectionBedState_id',
					'label' => 'Идентификатор элемента коечного фонда',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Отделение',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSectionProfile_id',
					'label' => 'Профиль койки',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSectionBedProfile_id',
					'label' => 'Профиль койки',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSectionBedState_Plan',
					'label' => 'Количество (план)',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'LpuSectionBedState_Repair',
					'label' => 'Ремонт',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuSectionBedState_Fact',
					'label' => 'Количество (факт)',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'LpuSectionBedState_begDate',
					'label' => 'Дата начала действия',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'LpuSectionBedState_endDate',
					'label' => 'Дата окончания действия',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'LpuSectionBedState_ProfileName',
					'label' => 'Наименование профиля коек',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'DBedOperationData',
					'label' => 'Операции над койкой',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'LpuSectionBedState_MalePlan',
					'label' => 'Количество (план)',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuSectionBedState_MaleFact',
					'label' => 'Количество (факт)',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuSectionBedState_FemalePlan',
					'label' => 'Количество (план)',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuSectionBedState_FemaleFact',
					'label' => 'Количество (факт)',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuSectionBedProfileLink_id',
					'label' => 'Ссылка на таблицу стыковки fed.LpuSectionBedProfileLink ',
					'rules' => '',
					'type' => 'id'
				)
			),
			'SaveLpuSectionFinans' => array(
				array(
					'field' => 'LpuSectionFinans_id',
					'label' => 'Идентификатор источника финансирования',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Отделение',
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
					'field' => 'LpuSectionFinans_IsQuoteOff',
					'label' => 'Отключить квоту',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSectionFinans_IsMRC',
					'label' => 'МРЦ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSectionFinans_Plan',
					'label' => 'План работы койки',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuSectionFinans_PlanHosp',
					'label' => 'План работы койки',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuSectionFinans_begDate',
					'label' => 'Дата начала действия',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'LpuSectionFinans_endDate',
					'label' => 'Дата окончания действия',
					'rules' => '',
					'type' => 'date'
				)
			),
			'SaveLpuSectionLicence' => array(
				array(
					'field' => 'LpuSectionLicence_id',
					'label' => 'Идентификатор лицензии',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Отделение',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSectionLicence_Num',
					'label' => 'Вид оплаты',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'LpuSectionLicence_begDate',
					'label' => 'Дата начала действия',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'LpuSectionLicence_endDate',
					'label' => 'Дата окончания действия',
					'rules' => '',
					'type' => 'date'
				)
			),
			'SaveLpuSectionShift' => array(
				array(
					'field' => 'LpuSectionShift_id',
					'label' => 'Идентификатор смен',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Отделение',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSectionShift_Count',
					'label' => 'Кол-во смен',
					'rules' => 'required|numeric|is_natural_no_zero',
					'type' => 'string'
				),
				array(
					'field' => 'LpuSectionShift_setDate',
					'label' => 'Дата начала действия',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'LpuSectionShift_disDate',
					'label' => 'Дата окончания действия',
					'rules' => '',
					'type' => 'date'
				)
			),
			'SaveLpuSectionTariffMes' => array(
				array(
					'field' => 'LpuSectionTariffMes_id',
					'label' => 'Идентификатор тарифа МЭС',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Отделение',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Mes_id',
					'label' => 'Код МЭС',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'TariffMesType_id',
					'label' => 'Тип тарифа',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSectionTariffMes_Tariff',
					'label' => 'Тариф МЭС',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSectionTariffMes_setDate',
					'label' => 'Дата начала действия',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'LpuSectionTariffMes_disDate',
					'label' => 'Дата окончания действия',
					'rules' => '',
					'type' => 'date'
				)
			),
			'SaveLpuSectionPlan' => array(
				array(
					'field' => 'LpuSectionPlan_id',
					'label' => 'Идентификатор плана',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Отделение',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSectionPlanType_id',
					'label' => 'Тип плана',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSectionPlan_setDate',
					'label' => 'Дата начала действия',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'LpuSectionPlan_disDate',
					'label' => 'Дата окончания действия',
					'rules' => '',
					'type' => 'date'
				)
			),
			'saveLpuSectionWardComfortLink' => array(
				array(
					'field' => 'LpuSectionWardComfortLink_id',
					'label' => 'Идентификатор объекта комфортности',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSectionWard_id',
					'label' => 'Идентификатор палаты',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DChamberComfort_id',
					'label' => 'Наименование объекта комфортности',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuSectionWardComfortLink_Count',
					'label' => 'Количество объектов комфортности',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadLpuSectionWardComfortLink' => array(
				array(
					'field' => 'LpuSectionWardComfortLink_id',
					'label' => 'Идентификатор объекта комфортности',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSectionWard_id',
					'label' => 'Идентификатор палаты',
					'rules' => '',
					'type' => 'int'
				)
			),
			'deleteSectionWardComfortLink' => array(
				array(
					'field' => 'LpuSectionWardComfortLink_id',
					'label' => 'Идентификатор объекта комфортности',
					'rules' => '',
					'type' => 'id'
				)
			),
			'deleteSectionBedStateOper' => array(
				array(
					'field' => 'LpuSectionBedStateOper_id',
					'label' => 'Идентификатор операции',
					'rules' => '',
					'type' => 'id'
				)
			),
			'saveStaffOSMGridDetail' => array(
				array(
					'field' => 'Staff_id',
					'label' => 'Идентификатор организационно-штатного мероприятия',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Staff_Num',
					'label' => 'Номер штата',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Staff_OrgName',
					'label' => 'Наименование ОШМ',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Staff_OrgDT',
					'label' => 'Дата ОШМ',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'Staff_OrgBasis',
					'label' => 'Основание ОШМ',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Staff_Code',
					'label' => 'Код ОШМ',//Непонятно откуда берущийся
					'rules' => '',
					'type' => 'int'
				)
			),
			'getStaffOSMGridDetail' => array(
				array(
					'field' => 'Staff_id',
					'label' => 'Идентификатор организационно-штатного мероприятия',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'id'
				)
			),
			'saveDBedOperation' => array(
				array(
					'field' => 'LpuSectionBedStateOper_id',
					'label' => 'Идентификатор операции',
					'rules' => '',
					'type' => 'id'
				),
				/*array(
					'field' => 'LpuSectionBedStateOper_OperName',
					'label' => 'Наименование операции',
					'rules' => '',
					'type' => 'string'
				),*/
				array(
					'field' => 'LpuSectionBedStateOper_OperDT',
					'label' => 'Дата операции',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'DBedOperation_id',
					'label' => 'Наименование операции',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'LpuSectionBedState_id',
					'label' => 'Идентификатор профиля койки',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuSectionBedStateOper_Code',
					'label' => 'Код операции',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadDBedOperation' => array(
				array(
					'field' => 'LpuSectionBedState_id',
					'label' => 'Идентификатор операции над койкой',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSectionBedStateOper_id',
					'label' => 'Идентификатор койки',
					'rules' => '',
					'type' => 'int'
				)
			),
			'GetLpuSectionQuote' => array(
				array(
					'field' => 'LpuSectionQuote_id',
					'label' => 'Идентификатор плана',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSectionQuote_Year',
					'label' => 'Год',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuUnitType_id',
					'label' => 'Вид медицинской помощи',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSectionProfile_id',
					'label' => 'Профиль',
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
					'field' => 'Lpu_id',
					'label' => 'ЛПУ',
					'rules' => '',
					'type' => 'id'
				)
			),
			'GetPersonDopDispPlan' => array(
				array(
					'field' => 'PersonDopDispPlan_id',
					'label' => 'Идентификатор плана',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DispDopClass_id',
					'label' => 'Тип плана диспансеризации',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PersonDopDispPlan_Year',
					'label' => 'Год',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'ЛПУ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'groups',
					'label' => 'Группы',
					'rules' => 'trim',
					'type' => 'string'
				)
			),
			'SavePersonDopDispPlan' => array(
				array(
					'field' => 'PersonDopDispPlan_id',
					'label' => 'Идентификатор плана',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PersonDopDispPlan_Year',
					'label' => 'Год',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'PersonDopDispPlan_Month',
					'label' => 'Месяц',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EducationInstitutionType_id',
					'label' => 'Идентификатор типа образовательного учреждения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'QuoteUnitType_id',
					'label' => 'Единицы измерения',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'ЛПУ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuRegion_id',
					'label' => 'ЛПУ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DispDopClass_id',
					'label' => 'Тип плана диспансеризации',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PersonDopDispPlan_Plan',
					'label' => 'План',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'SaveLpuSectionQuote' => array(
				array(
					'field' => 'LpuSectionQuote_id',
					'label' => 'Идентификатор плана',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'ЛПУ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSectionQuote_Year',
					'label' => 'Год',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'LpuSectionQuote_Count',
					'label' => 'Ограничение',
					'rules' => 'required',
					'type' => 'float'
				),
				array(
					'field' => 'LpuUnitType_id',
					'label' => 'Вид медицинской помощи',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSectionProfile_id',
					'label' => 'Профиль',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSectionQuote_begDate',
					'label' => 'Дата начала действия',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'QuoteUnitType_id',
					'label' => 'Единицы измерения',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'PayType_id',
					'label' => 'Вид оплаты',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getLpuSectionFinans' => array(
				array(
					'field' => 'LpuSectionFinans_id',
					'label' => 'Идентификатор финансирования',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Отделение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'isClose',
					'label' => 'Флаг закрытия',
					'rules' => '',
					'type' => 'int'
				)
			),
			'GetLpuSectionWard' => array(
				/**array(
				 'field' => 'Server_id',
				 'label' => 'Идентификатор сервера',
				 'rules' => '',
				 'type' => 'id'
				),*/
				array(
					'field' => 'LpuSectionWard_id',
					'label' => 'Идентификатор палаты',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'default' => 2,
					'field' => 'LpuSectionWard_isAct',
					'label' => 'Признак, что палата действующая',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'SaveLpuSectionWard' => array(
				array(
					'field' => 'Server_id',
					'label' => 'Идентификатор сервера',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSectionWard_id',
					'label' => 'Идентификатор палаты',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'LpuWardType_id',
					'label' => 'Идентификатор типа палаты',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Sex_id',
					'label' => 'Вид палаты (м\ж)',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSectionWard_Name',
					'label' => 'Наименование (номер)',
					'rules' => 'required|max_length[64]',
					'type' => 'string'
				),
				array(
					'field' => 'LpuSectionWard_Floor',
					'label' => 'Этаж',
					'rules' => 'required|max_length[64]',
					'type' => 'string'
				),
				array(
					'field' => 'LpuSectionWard_BedCount',
					'label' => 'Количество коек',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'LpuSectionWard_BedRepair',
					'label' => 'Койки на ремонте',
					'rules' => 'required|numeric|max_length[4]',
					'type' => 'string'
				),
				array(
					'default' => 0,
					'field' => 'LpuSectionWard_DayCost',
					'label' => 'Стоимость нахождения в сутки',
					'rules' => 'required|numeric|max_length[10]',
					'type' => 'string'
				),
				array(
					'field' => 'LpuSectionWard_setDate',
					'label' => 'Дата начала действия',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'LpuSectionWard_disDate',
					'label' => 'Дата окончания действия',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'LpuSectionWard_CountRoom',
					'label' => 'Количество комнат',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuSectionWard_DopPlace',
					'label' => 'Количество дополнительных мест',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuSectionWard_MainPlace',
					'label' => 'количество основных мест(коек) в палате',
					'rules' => 'required|numeric|is_natural_no_zero|max_length[4]',
					'type'  => 'int'
				),
				array(
					'field' => 'LpuSectionWard_Views',
					'label' => 'Вид из окна',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'LpuSectionWard_Square',
					'label' => 'Площадь палаты, кв. м.',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'DChamberComfortData',
					'label' => 'Объекты комфортности',
					'rules' => '',
					'type' => 'string'
				)
			),
			'getLpuSectionGrid' => array(
				array(
					'field' => 'LpuSection_pid',
					'label' => 'Идентификатор родительного отделения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuUnit_id',
					'label' => 'Идентификатор объединения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'isClose',
					'label' => 'Флаг закрытия',
					'rules' => '',
					'type' => 'int'
				)
			),
			'CheckLpuSectionBedState' => array(
				array(
					'field' => 'LpuSectionBedState_begDate',
					'label' => 'Дата начала действия',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'LpuSectionBedState_endDate',
					'label' => 'Дата окончания действия',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_pid',
					'label' => 'Идентификатор отделения-родителя',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'child_count',
					'label' => 'Число подотделений',
					'rules' => 'required|numeric',
					'type' => 'string'
				),
				array(
					'field' => 'LpuSectionBedState_Plan',
					'label' => 'Количество плановых коек',
					'rules' => 'required|numeric|is_natural_no_zero',
					'type' => 'string'
				),
				array(
					'field' => 'LpuSectionBedState_id',
					'label' => 'Идентификатор коечного фонда',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getLpuSectionComment' => array(
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор рабочего места',
					'rules' => 'required',
					'type' => 'id'
				),
			),
			'saveLpuSectionComment' => array(
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => '',
					'type' => 'id',
					'session_value' => 'CurMedStaffFact_id'
				),
				array(
					'field' => 'LpuSection_Descr',
					'label' => 'Комментарий отделения',
					'rules' => '',
					'type' => 'string'
				),
			),
			'GetLpuSectionTariff' => array(
				array(
					'field' => 'Server_id',
					'label' => 'Идентификатор сервера',
					'rules' => '',
					'type'  => 'id'
				),
				array(
					'field' => 'LpuSectionTariff_id',
					'label' => 'Идентификатор тарифа на отделении',
					'rules' => '',
					'type'  => 'id'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => '',
					'type'  => 'id'
				),
				array(
					'field' => 'isClose',
					'label' => 'Флаг закрытия',
					'rules' => '',
					'type'  => 'int'
				)
			),
			'CheckLpuSectionTariff' => array(
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => '',
					'type'  => 'id'
				),
				array(
					'field' => 'LpuSectionTariff_id',
					'label' => 'Идентификатор тарифа на отделении',
					'rules' => '',
					'type'  => 'id'
				),
				array(
					'field' => 'Server_id',
					'label' => 'Идентификатор сервера',
					'rules' => '',
					'type'  => 'id'
				),
				array(
					'field' => 'TariffClass_id',
					'label' => 'Вид тарифа',
					'rules' => '',
					'type'  => 'id'
				),
				array(
					'field' => 'LpuSectionTariff_setDate',
					'label' => 'Дата начала действия',
					'rules' => '',
					'type'  => 'date'
				)
			),
			'CheckLpuSectionFinans' => array(
				array(
					'field' => 'Server_id',
					'label' => 'Идентификатор сервера',
					'rules' => '',
					'type'  => 'id'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => 'required',
					'type'  => 'id'
				),
				array(
					'field' => 'LpuSectionFinans_id',
					'label' => 'Идентификатор финансирования',
					'rules' => '',
					'type'  => 'id'
				),
				array(
					'field' => 'LpuSectionFinans_begDate',
					'label' => 'Дата начала действия',
					'rules' => 'required',
					'type'  => 'date'
				)
			),
			'CheckLpuSectionLicence' => array(
				array(
					'field' => 'Server_id',
					'label' => 'Идентификатор сервера',
					'rules' => '',
					'type'  => 'id'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => '',
					'type'  => 'id'
				),
				array(
					'field' => 'LpuSectionLicence_id',
					'label' => 'Идентификатор лицензии',
					'rules' => '',
					'type'  => 'id'
				),
				array(
					'field' => 'LpuSectionLicence_begDate',
					'label' => 'Дата начала действия',
					'rules' => '',
					'type'  => 'date'
				)
			),
			'getLpuSectionBedAllQuery' => array(
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => '',
					'type'  => 'id'
				)
			),
			'updMaxEmergencyBed' => array(
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => '',
					'type'  => 'id'
				),
				array(
					'field' => 'LpuSection_MaxEmergencyBed',
					'label' => 'Максимальное количество коек',
					'rules' => '',
					'type'  => 'float'
				)
			),
			'getLpuSectionBedProfileforCombo' => array(
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => '',
					'type'  => 'id'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор ЛПУ',
					'rules' => '',
					'type'  => 'id'
				)
			),
			'getLpuSectionProfileforCombo' => array(
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => '',
					'type'  => 'id'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор ЛПУ',
					'rules' => '',
					'type'  => 'id'
				)
			),
			'getLpuSectionProfile' => array(
				array(
					'field' => 'LpuUnitType_id',
					'label' => 'Тип подразделения',
					'rules' => '',
					'type'  => 'id'
				),
				array(
					'field' => 'isProfileSpecCombo',
					'label' => 'признак комбо со специальностями',
					'rules' => '',
					'type'  => 'boolean'
				)
			),
			'getLpuSectionPid' => array(
				array(
					'field' => 'LpuSection_id',
					'label' => 'Отделение',
					'rules' => '',
					'type'  => 'id'
				), 
				array(
					'field' => 'LpuUnit_id',
					'label' => 'Группа отделений',
					'rules' => '',
					'type'  => 'id'
				), 
				array(
					'field' => 'Object',
					'label' => 'Объект',
					'rules' => '',
					'type'  => 'string'
				)
			),
			'getLpuSectionData' => array(
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => 'required',
					'type'  => 'id'
				)
			),
			'CheckLpuSectionShift' => array(
				array(
					'field' => 'Server_id',
					'label' => 'Идентификатор сервера',
					'rules' => '',
					'type'  => 'id'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => '',
					'type'  => 'id'
				),
				array(
					'field' => 'LpuSectionShift_id',
					'label' => 'Идентификатор смен',
					'rules' => '',
					'type'  => 'id'
				),
				array(
					'field' => 'LpuSectionShift_setDate',
					'label' => 'Дата начала действия',
					'rules' => '',
					'type'  => 'date'
				)
			),
			'CheckLpuSectionTariffMes' => array(
				array(
					'field' => 'Mes_id',
					'label' => 'Код МЭС',
					'rules' => '',
					'type'  => 'id'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => '',
					'type'  => 'id'
				),
				array(
					'field' => 'LpuSectionTariffMes_id',
					'label' => 'Идентификатор тарифа МЭС',
					'rules' => '',
					'type'  => 'id'
				),
				array(
					'field' => 'LpuSectionTariffMes_setDate',
					'label' => 'Дата начала действия',
					'rules' => '',
					'type'  => 'date'
				)
			),
			'CheckLpuSectionPlan' => array(
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => '',
					'type'  => 'id'
				),
				array(
					'field' => 'LpuSectionPlan_id',
					'label' => 'Идентификатор плана',
					'rules' => '',
					'type'  => 'id'
				),
				array(
					'field' => 'LpuSectionPlan_setDate',
					'label' => 'Дата начала действия',
					'rules' => '',
					'type'  => 'date'
				)
			),
			'loadSectionAverageDurationGrid' => array(
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => 'required',
					'type'  => 'id'
				)
			),
			'loadLpuBuildingType' => array(
				array(
					'field' => 'LpuBuilding_id',
					'label' => 'Идентификатор здания',
					'rules' => 'required',
					'type'  => 'id'
				)
			),
			'loadLpuSectionProfileList' => array(
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => '',
					'type'  => 'id'
				),
				array(
					'field' => 'LpuSection_ids',
					'label' => 'Список идентификаторов отделений',
					'rules' => '',
					'type'  => 'string'
				)
			),
			'saveSectionAverageDuration' => array(
				array(
					'field' => 'SectionAverageDuration_id',
					'label' => 'Идентификатор',
					'rules' => '',
					'type'  => 'id'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => 'required',
					'type'  => 'id'
				),
				array(
					'field' => 'SectionAverageDuration_Duration',
					'label' => 'Средняя продолжительность',
					'rules' => 'required',
					'type'  => 'float'
				),
				array(
					'field' => 'SectionAverageDuration_begDate',
					'label' => 'Дата начала',
					'rules' => 'required',
					'type'  => 'date'
				),
				array(
					'field' => 'SectionAverageDuration_endDate',
					'label' => 'Дата окончания',
					'rules' => '',
					'type'  => 'date'
				)
			),
			'getLpuStructureElementList' => array(
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuUnit_id',
					'label' => 'Идентификатор группы отделений',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор ЛПУ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuBuilding_id',
					'label' => 'Идентификатор',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getLpuListByAddress' => array(
				array(
					'field' => 'KLCity_id',
					'label' => 'Идентификатор города',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'KLTown_id',
					'label' => 'Идентификатор нас. пункта',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getAddressByLpuStructure' => array(
				array(
					'field' => 'Org_id',
					'label' => 'Идентификатор организации',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuBuilding_id',
					'label' => 'Идентификатор подразделения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuUnit_id',
					'label' => 'Идентификатор группы отделений',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getLpuWithUnservedDiagMedService' => array(),
			'getUnservedDiagMedService' => array(
				array('field' => 'Lpu_id','label' => 'Идентификатор ЛПУ','rules' => 'required','type' => 'id'),
			),
			'saveLinkFDServiceToRCCService' => array(
				array('field' => 'MedServiceLink_id','label' => 'Идентификатор связи служб ЦУК и ФД','rules' => '','type' => 'id'),
				array('field' => 'MedService_FDid','label' => 'Идентификатор службы ФД','rules' => 'required','type' => 'id'),
				array('field' => 'MedService_RCCid','label' => 'Идентификатор службы ЦУК','rules' => 'required','type' => 'id'),
			),
			'deleteLinkFDServiceToRCCService' => array(
				array('field' => 'MedServiceLink_id','label' => 'Идентификатор связи служб ЦУК и ФД','rules' => '','type' => 'id'),
			),
			'getFDServicesConnectedToRCCService' => array(
				array('field' => 'MedService_id','label' => 'Идентификатор службы ЦУК','rules' => 'required','type' => 'id'),
			),
			'getSmpUnitTypes' => array(
				array('field' => 'LpuBuilding_id','label' => 'Идентификатор подстанции СМП','rules' => 'required','type' => 'id')
			),
			'getLpuBuildingsForFilials'=>array(
				array('field' => 'LpuBuilding_id','label' => 'Идентификатор подстанции СМП','rules' => 'required','type' => 'id')
			),
			'saveSmpUnitParams'=>array(
				array('field' => 'SmpUnitParam_id','label' => 'Идентификатор параметров подстанции СМП','rules' => '','type' => 'id'),
				array('field' => 'SmpUnitParam_IsAutoBuilding','label' => 'Автоматически определять подразделение обслуживания неотложных вызовов','rules' => '','type' => 'swcheckbox'),
				array('field' => 'SmpUnitParam_IsCall112','label' => 'Принимать звонки из 112','rules' => '','type' => 'swcheckbox'),
				array('field' => 'LpuBuilding_id','label' => 'Идентификатор подстанции СМП','rules' => 'required','type' => 'id'),
				array('field' => 'SmpUnitType_id','label' => 'Идентификатор типа подстанции','rules' => 'required','type' => 'id'),
				array('field' => 'LpuBuilding_pid','label' => 'Идентификатор диспетчерской подстанции СМП','rules' => '','type' => 'id'),
				array('field' => 'SmpUnitParam_IsSignalBeg','label' => 'Флаг звук при передаче вызова на подстанцию','rules' => '','type' => 'swcheckbox'),
				array('field' => 'SmpUnitParam_IsSignalEnd','label' => 'Флаг звук по окончании обслуживания вызова','rules' => '','type' => 'swcheckbox'),
				array('field' => 'SmpUnitParam_IsOverCall','label' => 'Флаг Отображать вызовы с превышением срока обслуживания в отдельной группе АРМ СВ','rules' => '','type' => 'swcheckbox'),
				array('field' => 'SmpUnitParam_IsCallSenDoc','label' => 'Флаг Создавать вызовы в АРМ Старшего врача','rules' => '','type' => 'swcheckbox'),
				array('field' => 'SmpUnitParam_IsKTPrint','label' => 'Запрос печати КТ при назначении бригады на вызов','rules' => '','type' => 'swcheckbox'),
				array('field' => 'SmpUnitParam_IsAutoEmergDuty','label' => 'Автоматически выводить бригады на смену','rules' => '','type' => 'swcheckbox'),
				array('field' => 'SmpUnitParam_IsAutoEmergDutyClose','label' => 'Автоматически закрывать смены бригад','rules' => '','type' => 'swcheckbox'),
				array('field' => 'SmpUnitParam_IsSendCall','label' => 'Передача вызовов на другие подстанции Опер. отдела','rules' => '','type' => 'swcheckbox'),
				array('field' => 'SmpUnitParam_IsViewOther','label' => 'Просмотр бригад других подстанций Опер. отдела','rules' => '','type' => 'swcheckbox'),
				array('field' => 'SmpUnitParam_IsCancldCall','label' => 'Вызовы, требующие решения диспетчера отправляющей части', 'rules' => '', 'type'	=> 'swcheckbox'),
				array('field' => 'SmpUnitParam_IsCancldDisp','label' => 'Вызовы, требующие решения диспетчера удаленной подстанции', 'rules' => '', 'type'	=> 'swcheckbox'),
				array('field' => 'SmpUnitParam_IsCallControll','label' => 'Включить функцию «Контроль вызовов»', 'rules' => '', 'type'	=> 'swcheckbox'),
				array('field' => 'SmpUnitParam_IsSaveTreePath','label' => 'Сохранять путь в дереве решений', 'rules' => '', 'type'	=> 'swcheckbox'),
				array('field' => 'SmpUnitParam_IsShowAllCallsToDP','label' => 'Включить функцию «Контроль вызовов»', 'rules' => '', 'type'	=> 'swcheckbox'),
				array('field' => 'SmpUnitParam_IsShowCallCount','label' => 'Показывать количество вызовов, назначенных на бригаду', 'rules' => '', 'type'	=> 'swcheckbox'),
				array('field' => 'SmpUnitParam_IsNoMoreAssignCall','label' => 'Запрещать назначение вызова на бригаду при превышении', 'rules' => '', 'type'	=> 'swcheckbox'),
				array('field' => 'SmpUnitParam_MaxCallCount','label' => 'Запрещать назначение вызова на бригаду при превышении, минут', 'rules' => '', 'type'	=> 'int'),
				array('field' => 'Lpu_eid','label' => 'МО передачи (СМП)', 'rules' => '', 'type' => 'int'),
				array('field' => 'LpuBuilding_eid','label' => 'Подразделение СМП', 'rules' => '', 'type' => 'int'),
				//array('field' => 'SmpUnitParam_IsAutoHome','label' => 'Автоматической создание вызова на дом', 'rules' => '', 'type'	=> 'swcheckbox'),
				//array('field' => 'SmpUnitParam_IsPrescrHome','label' => 'Назначение врача для вызова на дом', 'rules' => '', 'type'	=> 'swcheckbox'),
				array('field' => 'SmpUnitParam_IsCallApproveSend','label' => 'Вызов утверждается и передается оперативным отделом', 'rules' => '', 'type'	=> 'swcheckbox'),
				array('field' => 'SmpUnitParam_IsNoTransOther','label' => 'Запретить перевод на другую подстанцию', 'rules' => '', 'type'	=> 'swcheckbox'),
				array('field' => 'SmpUnitParam_IsDenyCallAnswerDisp','label' => 'Отклоняющие вызовы', 'rules' => '', 'type'	=> 'swcheckbox'),
				array('field' => 'SmpUnitParam_IsDispNoControl','label' => 'Сообщать диспетчерам оперативного отдела о подстанциях, не взятых под управление', 'rules' => '', 'type'	=> 'swcheckbox'),
				array('field' => 'SmpUnitParam_IsDocNoControl','label' => 'Сообщать Старшему врачу о подстанциях, не взятых под управление', 'rules' => '', 'type'	=> 'swcheckbox'),
				array('field' => 'SmpUnitParam_IsDispOtherControl','label' => 'Сообщать диспетчеру, если подстанция уже находится под управлением другого диспетчера', 'rules' => '', 'type'	=> 'swcheckbox'),
				array('field' => 'SmpUnitParam_IsGroupSubstation','label' => 'Группировать вызовы по подстанциям', 'rules' => '', 'type'	=> 'swcheckbox')

			),
			'saveSmpUnitTimes'=>array(
				array('field' => 'LpuBuilding_id','label' => 'Идентификатор подстанции СМП','rules' => 'required','type' => 'id'),
				array('field' => 'minTimeSMP','label' => 'Время на принятие вызова подстанцией СМП в форме скорой помощи, минут','rules' => '','type' => 'string'),
				array('field' => 'maxTimeSMP','label' => 'Общее время на выполнение вызова подстанцией СМП в форме скорой помощи','rules' => '','type' => 'string'),
				array('field' => 'minTimeNMP','label' => 'Время на принятие вызова отделением (кабинетом) НМП, минут','rules' => '','type' => 'string'),
				array('field' => 'maxTimeNMP','label' => 'Общее время на выполнение вызова отделением (кабинетом) НМП, минут','rules' => '','type' => 'string'),
				array('field' => 'minResponseTimeNMP','label' => 'Время на принятие вызова подстанцией СМП в форме неотложной помощи, минут','rules' => '','type' => 'string'),
				array('field' => 'maxResponseTimeNMP','label' => 'Общее время на выполнение вызова подстанцией СМП в форме неотложной помощи, минут','rules' => '','type' => 'string'),
				
				array('field' => 'minResponseTimeET','label' => 'Общее время вызова НМП','rules' => '','type' => 'string'),
				array('field' => 'minResponseTimeETNMP','label' => 'Время на принятие вызова бригадой СМП в форме неотложной помощи, минут','rules' => '','type' => 'string'),
				array('field' => 'maxResponseTimeET','label' => 'Общее время вызова НМП','rules' => '','type' => 'string'),
				array('field' => 'maxResponseTimeETNMP','label' => 'Время на выезд на вызов в форме неотложной помощи, минут','rules' => '','type' => 'string'),
				array('field' => 'ArrivalTimeET','label' => 'Общее время вызова СМП','rules' => '','type' => 'string'),
				array('field' => 'ArrivalTimeETNMP','label' => 'Время доезда на место вызова НМП, минут','rules' => '','type' => 'string'),
				array('field' => 'ServiceTimeET','label' => 'Общее время вызова НМП','rules' => '','type' => 'string'),
				array('field' => 'DispatchTimeET','label' => 'Общее время вызова НМП','rules' => '','type' => 'string'),
				array('field' => 'LunchTimeET','label' => 'Время перерыва на обед для бригад СМП','rules' => '','type' => 'string'),
			),
			'getSmpUnitData'=>array(
				array('field' => 'LpuBuilding_id','label' => 'Идентификатор подстанции СМП','rules' => '','type' => 'id')
			),
			'getLpuBuildingData'=>array(
				array('field' => 'LpuBuilding_id','label' => 'Идентификатор подстанции СМП','rules' => '','type' => 'id')
			),
			'getRowLpuSectionService'=>array(
				array(
					'field' => 'LpuSectionService_id',
					'label' => 'Идентификатор связи отделения c обслуживаемым отделением',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_did',
					'label' => 'Идентификатор обслуживаемого отделения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'RecordStatus_Code',
					'label' => 'Код статсуа записи',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadLpuSectionServiceGrid'=>array(
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getLpuSectionServiceCount'=>array(
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getLpuWithMedServiceList'=>array(
				array('field' => 'MedServiceType_Code','label' => 'Тип службы','rules' => 'required','type' => 'int')
			),
			'saveForenCorpServingMedServices'=>array(
				array('field' => 'MedService_id','label' => 'Идентификатор обслуживаемого отделения','rules' => 'required','type' => 'id'),
				array('field' => 'MedService_ForenCrim_id','label' => 'Идентификатор медико-криминалистической службы','rules' => 'required','type' => 'id'),
				array('field' => 'MedService_ForenChem_id','label' => 'Идентификатор судебно-химической службы','rules' => 'required','type' => 'id'),
				array('field' => 'MedService_ForenHist_id','label' => 'Идентификатор судебно-гистологической службы','rules' => 'required','type' => 'id'),
				array('field' => 'MedService_ForenBio_id','label' => 'Идентификатор судебно-гистологической службы','rules' => 'required','type' => 'id'),
			),
			'loadForenCorpServingMedServices'=>array(
				array('field' => 'MedService_id','label' => 'Идентификатор обслуживаемого отделения','rules' => 'required','type' => 'id'),
			),
			'saveForenHistServingMedServices'=>array(
				array('field' => 'MedService_id','label' => 'Идентификатор обслуживаемого отделения','rules' => 'required','type' => 'id'),
				array('field' => 'MedService_ForenChem_id','label' => 'Идентификатор судебно-химической службы','rules' => 'required','type' => 'id'),
			),
			'loadForenHistServingMedServices'=>array(
				array('field' => 'MedService_id','label' => 'Идентификатор обслуживаемого отделения','rules' => 'required','type' => 'id'),
			),
			'getLpuUnitCountByType'=>array(
				array('field' => 'Lpu_id','label' => 'Идентификатор МО','rules' => '','type' => 'id'),
				array('field' => 'LpuUnitType_SysNick','label' => 'Тип группы отделений','rules' => 'required','type' => 'string'),
			),
			'uploadOrgPhoto'=>array(
				array('field' => 'org_photo','label' => 'Фотография','rules' => '','type' => 'string'),
				array('field' => 'Lpu_id','label' => 'МО','rules' => '','type' => 'id'),
				array('field' => 'LpuBuilding_id','label' => 'Здания','rules' => '','type' => 'id'),
				array('field' => 'LpuUnit_id','label' => 'МО','rules' => '','type' => 'id'),
				array('field' => 'LpuSection_id','label' => 'МО','rules' => '','type' => 'id')
			),
			'GetLpuSectionEdit' => array(
				array('field' => 'LpuSection_id', 'label' => 'Отделение', 'rules' => 'required', 'type'  => 'id'),
				array('field' => 'LpuUnit_id', 'label' => 'Подразделение', 'rules' => '', 'type'  => 'id')
			),
			'GetLpuAllQuery' => array(
				array('field' => 'Lpu_id', 'label' => 'МО', 'rules' => 'required', 'type'  => 'id')
			), 
			'GetLpuUnitEdit' => array(
				array('field' => 'LpuUnit_id', 'label' => 'Группа отделений', 'rules' => 'required', 'type'  => 'id')
			), 
			'GetLpuBuilding' => array(
				array('field' => 'LpuBuilding_id', 'label' => 'Подразделение', 'rules' => '', 'type'  => 'id'),
				array('field' => 'Lpu_id', 'label' => 'МО', 'rules' => '', 'type'  => 'id'),
				array('field' => 'isClose', 'label' => 'Закрытые', 'rules' => '', 'type'  => 'id'),
				array('field' => 'LpuFilial_id', 'label' => 'Идентификатор филиала', 'rules' => '', 'type' => 'id')
			),
			'loadLpuRegionInfo' => array(
				array(
					'field' => 'LpuRegion_id',
					'label' => 'Идентификатор участка',
					'rules' => 'required',
					'type'	=> 'int'
				)
			),
			'saveLpuBuildingAdditionalParams' => array(
				array(
					'field' => 'LpuBuilding_id',
					'label' => 'Идентификатор подразделения',
					'rules' => 'required',
					'type'	=> 'int'
				),
				array(
					'field' => 'LpuBuilding_IsPrint',
					'label' => 'Двусторонняя печать Карты вызова',
					'rules' => '',
					'type'	=> 'string'
				),
				array(
					'field' => 'LpuBuildingSmsType_id',
					'label' => 'Отправлять СМС-сообщение о назначении бригады на вызов',
					'rules' => '',
					'type'	=> 'id'
				),
				array(
					'field' => 'LpuBuilding_setDefaultAddressCity',
					'label' => 'При приеме вызова населенный пункт заполнять по умолчанию',
					'rules' => '',
					'type'	=> 'string'
				),
				array(
					'field' => 'LpuBuilding_IsEmergencyTeamDelay',
					'label' => 'Запрашивать причины задержек бригады СМП',
					'rules' => '',
					'type'	=> 'string'
				),
				array(
					'field' => 'LpuBuilding_IsCallCancel',
					'label' => 'Отменяющие вызовы',
					'rules' => '',
					'type'	=> 'string'
				),
				array(
					'field' => 'LpuBuilding_IsCallDouble',
					'label' => 'Дублирующие вызовы',
					'rules' => '',
					'type'	=> 'string'
				),
				array(
					'field' => 'LpuBuilding_IsCallSpecTeam',
					'label' => 'При приеме вызова населенный пункт заполнять по умолчанию',
					'rules' => '',
					'type'	=> 'string'
				),
				array(
					'field' => 'LpuBuilding_IsCallReason',
					'label' => 'Вызовы с поводом, требующим наблюдения старшего врача ',
					'rules' => '',
					'type'	=> 'string'
				),
				array(
					'field' => 'LpuBuilding_IsUsingMicrophone',
					'label' => 'Использовать микрофон для записи вызова',
					'rules' => '',
					'type'	=> 'string'
				),
				array(
					'field' => 'LpuBuilding_IsWithoutBalance',
					'label' => 'Учет расхода медикаментов без обращения к остаткам',
					'rules' => '',
					'type'	=> 'string'
				),
				array(
					'field' => 'LpuBuilding_IsDenyCallAnswerDoc',
					'label' => 'Отклоняющие вызовы',
					'rules' => '',
					'type'	=> 'string'
				)
				
			),
			'getNmpParams' => array(
				array(
					'field' => 'MedService_id',
					'label' => 'Идентификатор службы НМП',
					'rules' => 'required',
					'type'	=> 'id'
				),
			),
			'saveNmpParams' => array(
				array(
					'field' => 'MedService_id',
					'label' => 'Идентификатор службы НМП',
					'rules' => 'required',
					'type'	=> 'id'
				),
				array(
					'field' => 'LpuHMPWorkTime_MoFrom',
					'label' => 'Время',
					'rules' => '',
					'type'	=> 'string'
				),
				array(
					'field' => 'LpuHMPWorkTime_MoTo',
					'label' => 'Время',
					'rules' => '',
					'type'	=> 'string'
				),
				array(
					'field' => 'LpuHMPWorkTime_TuFrom',
					'label' => 'Время',
					'rules' => '',
					'type'	=> 'string'
				),
				array(
					'field' => 'LpuHMPWorkTime_TuTo',
					'label' => 'Время',
					'rules' => '',
					'type'	=> 'string'
				),
				array(
					'field' => 'LpuHMPWorkTime_WeFrom',
					'label' => 'Время',
					'rules' => '',
					'type'	=> 'string'
				),
				array(
					'field' => 'LpuHMPWorkTime_WeTo',
					'label' => 'Время',
					'rules' => '',
					'type'	=> 'string'
				),
				array(
					'field' => 'LpuHMPWorkTime_ThFrom',
					'label' => 'Время',
					'rules' => '',
					'type'	=> 'string'
				),
				array(
					'field' => 'LpuHMPWorkTime_ThTo',
					'label' => 'Время',
					'rules' => '',
					'type'	=> 'string'
				),
				array(
					'field' => 'LpuHMPWorkTime_FrFrom',
					'label' => 'Время',
					'rules' => '',
					'type'	=> 'string'
				),
				array(
					'field' => 'LpuHMPWorkTime_FrTo',
					'label' => 'Время',
					'rules' => '',
					'type'	=> 'string'
				),
				array(
					'field' => 'LpuHMPWorkTime_SaFrom',
					'label' => 'Время',
					'rules' => '',
					'type'	=> 'string'
				),
				array(
					'field' => 'LpuHMPWorkTime_SaTo',
					'label' => 'Время',
					'rules' => '',
					'type'	=> 'string'
				),
				array(
					'field' => 'LpuHMPWorkTime_SuFrom',
					'label' => 'Время',
					'rules' => '',
					'type'	=> 'string'
				),
				array(
					'field' => 'LpuHMPWorkTime_SuTo',
					'label' => 'Время',
					'rules' => '',
					'type'	=> 'string'
				),
			),
			'loadLpuMseLinkGrid' => array(
				array(
					'field' => 'Lpu_bid',
					'label' => 'Идентификатор бюро МСЭ',
					'rules' => '',
					'type'	=> 'id'
				),
				array(
					'field' => 'Lpu_oid',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type'	=> 'id'
				),
				array(
					'field' => 'LpuMseLink_begDate',
					'label' => 'Дата начала',
					'rules' => '',
					'type'	=> 'date'
				),
				array(
					'field' => 'LpuMseLink_endDate',
					'label' => 'Дата окончания',
					'rules' => '',
					'type'	=> 'date'
				),
				array(
					'field' => 'isClose',
					'label' => 'Отображать закрытые записи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 0,
					'field' => 'start',
					'label' => 'Начальная запись',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 100,
					'field' => 'limit',
					'label' => 'Лимит записей',
					'rules' => '',
					'type' => 'int'
				),
			),
			'saveLpuMseLink' => array(
				array(
					'field' => 'LpuMseLink_id',
					'label' => 'Идентификатор связи МО с бюро МСЭ',
					'rules' => '',
					'type'	=> 'id'
				),
				array(
					'field' => 'Lpu_oid',
					'label' => 'Идентификатор МО',
					'rules' => 'required',
					'type'	=> 'id'
				),
				array(
					'field' => 'Lpu_bid',
					'label' => 'Идентификатор MO МСЭ',
					'rules' => 'required',
					'type'	=> 'id'
				),
				array(
					'field' => 'LpuMseLink_begDate',
					'label' => 'Дата начала',
					'rules' => 'required',
					'type'	=> 'date'
				),
				array(
					'field' => 'LpuMseLink_endDate',
					'label' => 'Дата окончания',
					'rules' => '',
					'type'	=> 'date'
				),
				array(
					'field' => 'MedService_id',
					'label' => 'Идентификатор бюро МСЭ',
					'rules' => 'required',
					'type'	=> 'id'
				),
			),
			'loadLpuMseLinkForm' => array(
				array(
					'field' => 'LpuMseLink_id',
					'label' => 'Идентификатор связи МО с бюро МСЭ',
					'rules' => 'required',
					'type'	=> 'id'
				),
			),
			'deleteLpuMseLink' => array(
				array(
					'field' => 'LpuMseLink_id',
					'label' => 'Идентификатор связи МО с бюро МСЭ',
					'rules' => 'required',
					'type'	=> 'id'
				),
			),
			'getLpuUnitProfile' => array(
				array('field' => 'LpuUnitProfile_fid','label' => 'Идентификатор профиля (ФРМО)','rules' => '','type' => 'id'),
				array('field' => 'LpuUnitProfile_Name','label' => 'Профиль (ФРМО)','rules' => '','type'=> 'string'),
				array('field' => 'LpuUnitProfile_pid','label' => 'Идентификатор профиля (ФРМО)','rules' => '','type' => 'id'),
				array('field' => 'LpuUnitProfile_Form30','label' => 'Идентификатор профиля (ФРМО)','rules' => '','type' => 'id'),
				array('field' => 'UnitDepartType_id','label' => 'Идентификатор профиля (ФРМО)','rules' => '','type' => 'id'),
				array('field' => 'LpuUnitTypeFRMO_id','label' => 'Тип (ФРМО)','rules' => '','type' => 'id'),
			),
			'getFRMPSubdivisionType' => array(
				array('field' => 'id','label' => 'Тип (Форма 30)','rules' => '','type' => 'id'),
				array('field' => 'name','label' => 'Тип (Форма 30)','rules' => '','type'=> 'string'),
				array('field' => 'fullname','label' => 'Тип (Форма 30)','rules' => '','type' => 'string'),
				array('field' => 'parent','label' => 'Тип (Форма 30)','rules' => '','type' => 'string')
			),
			'getLpuAddress' => array(
				array('field' => 'KLStreet_id', 'label' => 'Улица', 'rules' => 'required', 'type' => 'id' ),
				array('field' => 'KLTown_id', 'label' => 'Нас.пункт', 'rules' => '', 'type' => 'id' ),
				array('field' => 'KLRgn_id', 'label' => 'Нас.пункт', 'rules' => '', 'type' => 'id' ),
				array('field' => 'KLSubRGN_id', 'label' => 'Нас.пункт', 'rules' => '', 'type' => 'id' ),
				array('field' => 'KLCity_id', 'label' => 'город', 'rules' => '', 'type' => 'id' ),
				array('field' => 'KLHome', 'label' => 'Дом', 'rules' => '', 'type' => 'string' ),
				array('field' => 'Person_Age', 'label' => 'Возраст', 'rules' => '', 'type' => 'int'),
				array('field' => 'Lpu_id', 'label' => 'ид мо', 'rules' => '', 'type' => 'int')
			),
			'getLpuPhoneMO' => array(
				array('field' => 'Lpu_id', 'label' => 'МО', 'rules' => 'required', 'type'  => 'id')
			),
			'getLpuStaffGridDetail' => array(
				array('field' => 'LpuStaff_id', 'label' => 'Идентификатор штатного расписания', 'rules' => '', 'type' => 'id'),
				array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => '', 'type' => 'id'),
			),
			'saveLpuStaffGridDetail' => array(
				array(
					'field' => 'LpuStaff_id',
					'label' => 'Идентификатор штатного расписания',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuStaff_Num',
					'label' => 'Номер штатного расписания',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'LpuStaff_Descript',
					'label' => 'Описание',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'LpuStaff_ApprovalDT',
					'label' => 'Дата утверждения',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'LpuStaff_begDate',
					'label' => 'Дата начала',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'LpuStaff_endDate',
					'label' => 'Дата окончания',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Staff_Code',
					'label' => 'Код ОШМ',//Непонятно откуда берущийся
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Staff_id',
					'label' => 'Идентификатор организационно-штатного мероприятия',
					'rules' => '',
					'type' => 'id'
				),
			),
			'hasMedStaffFactInAIDSCenter' => array(
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Идентификатор врача',
					'rules' => 'required',
					'type' => 'id'
				),
			),
			'getFpList' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => 'required',
					'type' => 'id'
				),
			)
		);
	}

	/**
	 * Это Doc-блок
	 * @todo Отрефакторить этот трэш
	 */
	public function GetLpuStructure()
	{
		/**
		 * Это Doc-блок
		 */
		function GetLpuStructureChild($childrens, $field, $lvl)
		{
			$val = array();
			if ($childrens != false && count($childrens) > 0) {
				foreach ($childrens as $rows)
				{
					$obj = array(
						'text' => toUTF(trim($rows[$field['name']])),
						'id' => $field['object'] . '_lvl' . $lvl . '_' . $rows[$field['id']],
						'object' => $field['object'],
						'object_id' => $field['id'],
						'object_value' => $rows[$field['id']],
						'leaf' => $field['leaf'],
						'iconCls' => !empty($rows['iconCls'])?$rows['iconCls']:$field['iconCls'],
						'cls' => $field['cls'],
						'qtip' => !empty($field['qtip']) && !empty($rows[$field['qtip']]) ? toUTF(trim($rows[$field['qtip']])) : null
					);

					if (!empty($rows['claimed'])) {
						$obj['claimed'] = $rows['claimed'];
					}

					if (isset($field['parent_id'])) {
						$obj['id'] .= '_'.$field['parent_id'];
						$obj['parent_id'] = $field['parent_id'];
					}
					
					if (isset($field['id'])) {
						$obj['object_key'] = $field['id'];
					}					
					
					if (isset($field['profile'])) {
						$obj['profile'] = $rows['LpuSectionProfile_id'];
					}
					
					if (isset($field['MesAgeLpuType_Code'])) {
						$obj['MesAgeLpuType_Code'] = $rows['MesAgeLpuType_Code'];
					}
					
					if (isset($field['MedServiceType_SysNick'])) {
						$obj['MedServiceType_SysNick'] = $rows['MedServiceType_SysNick'];
					}
					
					if (isset($field['ElectronicQueueInfo_id'])) {
						$obj['ElectronicQueueInfo_id'] = $rows['ElectronicQueueInfo_id'];
					}

					if (isset($field['UnitDepartType_fid']) && isset($rows['UnitDepartType_fid'])) {
						$obj['UnitDepartType_fid'] = $rows['UnitDepartType_fid'];
					}

					if (isset($field['FRMOUnit_OID']) && isset($rows['FRMOUnit_OID'])) {
						$obj['FRMOUnit_OID'] = $rows['FRMOUnit_OID'];
					}

					if (isset($field['RegisterMO_OID']) && isset($rows['RegisterMO_OID'])) {
						$obj['RegisterMO_OID'] = $rows['RegisterMO_OID'];
					}

					// Если чайлдэлементов в ветке нет
					if ((isset($rows['leafcount'])) && ($rows['leafcount'] == 0)) {
						$obj['leaf'] = true;
					}
					
					if ($field['object'] == 'LpuBuilding') {
						$obj['LpuBuildingType_id'] = $rows['LpuBuildingType_id'];
					}
					
					if ($field['object'] == 'LpuUnitType') {
						$n_id = array('id' => $field['object'] . '_lvl' . $lvl . '_' . $rows['LpuBuilding_id'] . '_' . $rows['LpuUnitType_id']);
					}
					else
						$n_id = array();
					if (isset($rows['LpuRegionType_id']))
						$lrt = array('LpuRegionType_id' => $rows['LpuRegionType_id']);
					else
						$lrt = array();
					if (isset($rows['LpuUnitType_id'])) {
						$lst = array('LpuUnitType_id' => $rows['LpuUnitType_id'], 'LpuUnitType_Nick' => toUTF(trim($rows['LpuUnitType_Nick'])));
					}
					else
						$lst = array();
					$val[] = array_merge($obj, $n_id, $lrt, $lst);
				}
			}
			return $val;
		}
		/**
		 * Это Doc-блок
		 */
		function FormNameNode($lvl, $getdata)
		{
			$arr = array();
			/**
			 * Это Doc-блок
			 */
			Switch ($lvl)
			{
				case 'LpuRegionTitle':
					if (onlyCadrUserView()) {
						return array();
					} else {
						$grouptype = array(array('id' => 11111, 'object' => 'LpuRegionTitle', 'name' => 'Участки'));
						return $grouptype;
					}

					break;
				case 'LpuRegionType':
					break;
				case 'LpuRegion':
					$i = 0;
					if (count($getdata) > 0) {
						foreach ($getdata as $row)
						{
							// Номер участка 
							//$name = '<b>'.$row['EvnClass_Name'].'</b> ';
							$name = '' . $row['LpuRegion_Name'] . ' ';
							// Описание 
							if ((!empty($row['LpuRegion_Descr'])) && ($row['LpuRegion_Descr'] != 'Null'))
								$name = $name . ' (' . $row['LpuRegion_Descr'] . ') ';
							$getdata[$i]['name'] = $name;
							$i++;
						}
					}
					return $getdata;
					break;
				case 'StorageTitle':
					$grouptype = array(array('id' => 22222, 'object' => 'StorageTitle', 'name' => 'Склады'));
					return $grouptype;
					break;
			}
		}
	
		$data = $_REQUEST;

		if (!empty($data['deniedSectionsList'])) {
			$data['deniedSectionsList']  = explode(",", $data['deniedSectionsList']);
			foreach ($data['deniedSectionsList'] as &$el) {
				$el = floatval($el);
			}
		}

		// Получаем сессионные переменные
		$data['session'] = $_SESSION;
		
		$val = array();
		$val_new = array();
		$data['node'] = str_replace(Array('LpuRegion', 'LpuSection', 'LpuUnit', 'Lpu', 'Building', 'LpuFilial'), '', $data['node']);
		//log_message('debug', 'Пришедшая нода: '.$data['node']); //.' - '.$data['node_level']
		
		if ($data['object'] == 'MedService' && $this->config->item('IS_DEBUG') == true) {
			// аппараты
			$childrens = $this->dbmodel->GetMedServiceAppNodeList($data);
			$field = Array('object' => 'MedService', 'id' => 'MedService_id', 'name' => 'MedService_Name', 'MedServiceType_SysNick' => 'MedServiceType_SysNick', 'iconCls' => 'lpu-usluga16', 'leaf' => false, 'cls' => 'folder');
			$val_new = GetLpuStructureChild($childrens, $field, $data['level']);
		}
		/*if ($data['object'] == 'MedService') {
			$childrens = $this->dbmodel->GetStorageNodeList($data,'medservice');
			$field = Array('object' => 'Storage', 'parent_id' => $data['object_id'], 'id' => 'Storage_id', 'name' => 'Storage_Name', 'iconCls' => 'product16', 'leaf' => true, 'cls' => 'folder');
		}*/
		if (!empty($data['SectionsOnly']) && $data['SectionsOnly'] == true && !isset($data['regionsOnly'])) {

			Switch ($data['level']) {
				case 1:
					$childrens = $this->dbmodel->GetLpuBuildingNodeList($data);
					$field = Array('object' => "LpuBuilding", 'id' => "LpuBuilding_id", 'name' => "LpuBuilding_Name", 'iconCls' => 'lpu-building16', 'leaf' => false, 'cls' => "folder", 'RegisterMO_OID' => 'true');
				break;
				case 2:
					if ($data['object'] == 'LpuBuilding') {
						$childrens = $this->dbmodel->GetLpuUnitTypeNodeList($data);
						$field = Array('object' => "LpuUnitType", 'id' => "LpuBuilding_id", 'name' => "LpuUnitType_Name", 'iconCls' => 'lpu-unittype16', 'leaf' => false, 'cls' => "folder");
					}
				break;
				case 3:
					if ($data['object'] == 'LpuUnitType') {
						$childrens = $this->dbmodel->GetLpuUnitNodeList($data);
						$field = Array('object' => "LpuUnit", 'id' => "LpuUnit_id", 'name' => "LpuUnit_Name", 'iconCls' => 'lpu-unit16', 'leaf' => false, 'cls' => "folder", 'UnitDepartType_fid' => 'true', 'FRMOUnit_OID' => 'true');
					}
				break;
				case 4:
					if ($data['level_two'] == 'LpuSection') {
						$childrens = $this->dbmodel->GetLpuSectionNodeList($data);
						$field = Array('object' => "LpuSection", 'id' => "LpuSection_id", 'name' => "LpuSection_Name", 'iconCls' => 'lpu-section16', 'leaf' => false, 'cls' => "folder");
					}
				break;
				case 5:
					if (($data['level_two'] == 'LpuSection') || ($data['level_two'] == 'All')) {
						$childrens = $this->dbmodel->GetLpuSectionPidNodeList($data);
						$field = Array('object' => "LpuSection", 'id' => "LpuSection_id", 'name' => "LpuSection_Name", 'iconCls' => 'lpu-subsection16', 'leaf' => false, 'cls' => "folder", 'profile' => "LpuSection_Profile_id");
					}
				break;
				case 6:
					/*if ($data['object'] == 'LpuSection' && $data['level_two'] == 'All') {
						// склады
						$childrens = $this->dbmodel->GetStorageNodeList($data,'lpusection');
						$field = Array('object' => 'Storage', 'parent_id' => $data['object_id'], 'id' => 'Storage_id', 'name' => 'Storage_Name', 'iconCls' => 'product16', 'leaf' => true, 'cls' => 'folder');
					}*/
				break;
				default:
					if ( $data['object'] != 'MedService' ) {
						if ( !isSuperAdmin() && !empty($data['session']['setting']['server']['mp_is_uch']) && !empty($data['from']) && $data['from'] == 'FundHolding' ) {
							$data['uchOnly'] = true;
							$childrens = $this->dbmodel->GetLpuRegionNodeList($data);
							$childrens = FormNameNode('LpuRegion', $childrens);
							$field = Array('object' => "LpuRegion", 'id' => "LpuRegion_id", 'name' => "name", 'iconCls' => 'lpu-region16', 'leaf' => true, 'cls' => "folder");
						}
						else {
							if ( (!isSuperAdmin()&&!isTFOMSUser()&&!isSMOUser()) /*|| empty($data['Lpu_id'])*/ ) {
								if(empty($data['Lpu_id']))
									$data['Lpu_id'] = $_SESSION['lpu_id'];
							}
							$childrens = $this->dbmodel->GetLpuNodeList($data);
							$field = Array('object' => "Lpu", 'id' => "Lpu_id", 'name' => "Lpu_Name", 'MesAgeLpuType_Code' => "MesAgeLpuType_Code",'iconCls' => 'lpu16', 'leaf' => false, 'cls' => "folder");
						}
					}
				break;
			}
		} else {

			Switch ($data['level'])
			{
				case 1:
					if (!isset($data['regionsOnly'])) {

						if ($data['object'] === 'Lpu')
						{
							// Филиалы
							$children = $this->dbmodel->GetLpuFilialNodeList($data);
							$field = array('object' => 'LpuFilial', 'id' => 'LpuFilial_id', 'name' => 'LpuFilial_Name', 'iconCls' => 'lpu-building16', 'leaf' => false, 'cls' => "folder");
							$val_new = GetLpuStructureChild($children, $field, $data['level']);

							// службы
							$childrens = $this->dbmodel->GetMedServiceNodeList($data,'lpu');
							$field = Array('object' => 'MedService', 'id' => 'MedService_id', 'name' => 'MedService_Name', 'MedServiceType_SysNick' => 'MedServiceType_SysNick', 'ElectronicQueueInfo_id' => 'ElectronicQueueInfo_id', 'iconCls' => 'medservice16', 'leaf' => false, 'cls' => 'folder');
							$val_new2 = GetLpuStructureChild($childrens, $field, $data['level']);
							$val_new = array_merge($val_new2, $val_new);

							// склады
							/*$childrens = $this->dbmodel->GetStorageNodeList($data,'lpu');
							$field = Array('object' => 'Storage', 'id' => 'Storage_id', 'name' => 'Storage_Name', 'iconCls' => 'product16', 'leaf' => true, 'cls' => 'folder');
							$val_new2 = GetLpuStructureChild($childrens, $field, $data['level']);
							$val_new = array_merge($val_new2, $val_new);*/
						}

						// Выносим здания сюда, так как с параметром level = 1 может прийти Филиал, которому кроме них ничего не нужно
						$childrens = $this->dbmodel->GetLpuBuildingNodeList($data);
						$field = Array('object' => "LpuBuilding", 'id' => "LpuBuilding_id", 'name' => "LpuBuilding_Name", 'iconCls' => 'lpu-building16', 'leaf' => false, 'cls' => "folder", 'ElectronicQueueInfo_id' => 'ElectronicQueueInfo_id', 'RegisterMO_OID' => 'true');
						$val_new2 = GetLpuStructureChild($childrens, $field, $data['level']);
						$val_new = isset($val_new) ? array_merge($val_new2, $val_new): $val_new2;


						// Обнуляем для филиалов, т.к. в конце функция снова использует их и записи дублируются
						$childrens = array();
						$field = array();

					}

					if ($data['object'] !== 'Lpu') break;

					// Склады - заголовок
					$childrens = FormNameNode('StorageTitle', array());
					$field = Array('object' => "StorageTitle", 'id' => "id", 'name' => "name", 'iconCls' => 'product16', 'leaf' => false, 'cls' => "folder");
					$val_new2 = GetLpuStructureChild($childrens, $field, $data['level']);
					$val_new = isset($val_new) ? array_merge($val_new2, $val_new): $val_new2;

					// Участки - заголовок
					$childrens = FormNameNode('LpuRegionTitle', array());
					$field = Array('object' => "LpuRegionTitle", 'id' => "id", 'name' => "name", 'iconCls' => 'lpu-regiontitle16', 'leaf' => false, 'cls' => "folder");

					break;
				case 2:
					if ($data['object'] == 'LpuBuilding') {
						// склады
						/*$childrens = $this->dbmodel->GetStorageNodeList($data,'lpubuilding');
						$field = Array('object' => 'Storage', 'id' => 'Storage_id', 'name' => 'Storage_Name', 'iconCls' => 'product16', 'leaf' => true, 'cls' => 'folder');
						$val_new = GetLpuStructureChild($childrens, $field, $data['level']);*/
						// службы
						$childrens = $this->dbmodel->GetMedServiceNodeList($data,'lpubuilding');
						$field = Array('object' => 'MedService', 'id' => 'MedService_id', 'name' => 'MedService_Name', 'MedServiceType_SysNick' => 'MedServiceType_SysNick', 'ElectronicQueueInfo_id' => 'ElectronicQueueInfo_id', 'iconCls' => 'medservice16', 'leaf' => false, 'cls' => 'folder');
						//$val_new2 = GetLpuStructureChild($childrens, $field, $data['level']);
						//$val_new = array_merge($val_new2, $val_new);
						$val_new = GetLpuStructureChild($childrens, $field, $data['level']);
						//
						$childrens = $this->dbmodel->GetLpuUnitTypeNodeList($data);
						$field = Array('object' => "LpuUnitType", 'id' => "LpuBuilding_id", 'name' => "LpuUnitType_Name", 'iconCls' => 'lpu-unittype16', 'leaf' => false, 'cls' => "folder");
					}
					if ($data['object'] == 'LpuRegionTitle') {
						$childrens = $this->dbmodel->GetLpuRegionTypeNodeList($data);
						$field = Array('object' => "LpuRegionType", 'id' => "LpuRegionType_id", 'name' => "LpuRegionType_Name", 'iconCls' => 'lpu-regiontype16', 'leaf' => false, 'cls' => "folder");
					}
					if ($data['object'] == 'StorageTitle') {
						$childrens = $this->dbmodel->GetStorageNodeList($data,'title');
						$field = Array('object' => 'Storage', 'id' => 'Storage_id', 'name' => 'Storage_Name', 'iconCls' => 'product16', 'leaf' => false, 'cls' => 'folder', 'qtip' => 'MerchMedService_Nick');
					}
					break;
				case 3:
					if ($data['object'] == 'LpuUnitType') {
						// службы
						$childrens = $this->dbmodel->GetMedServiceNodeList($data,'lpuunittype');
						$field = Array('object' => 'MedService', 'id' => 'MedService_id', 'name' => 'MedService_Name', 'MedServiceType_SysNick' => 'MedServiceType_SysNick', 'ElectronicQueueInfo_id' => 'ElectronicQueueInfo_id', 'iconCls' => 'medservice16', 'leaf' => false, 'cls' => 'folder');
						$val_new = GetLpuStructureChild($childrens, $field, $data['level']);
						//
						$childrens = $this->dbmodel->GetLpuUnitNodeList($data);
						$field = Array('object' => "LpuUnit", 'id' => "LpuUnit_id", 'name' => "LpuUnit_Name", 'iconCls' => 'lpu-unit16', 'leaf' => false, 'cls' => "folder", 'UnitDepartType_fid' => 'true', 'FRMOUnit_OID' => 'true');
					}
					if ($data['object'] == 'LpuRegionType') {
						if(empty($data['Lpu_id'])) $data['Lpu_id'] = $data['session']['lpu_id'];
						$childrens = $this->dbmodel->GetLpuRegionNodeList($data);
						$childrens = FormNameNode('LpuRegion', $childrens);
						$field = Array('object' => "LpuRegion", 'id' => "LpuRegion_id", 'name' => "name", 'iconCls' => 'lpu-region16', 'leaf' => true, 'cls' => "folder");
					}
					if ($data['object'] == 'Storage') {
						$childrens = $this->dbmodel->GetStorageNodeList($data, 'storage');
						//$childrens = FormNameNode('Storage', $childrens);
						$field = Array('object' => "Storage", 'id' => "Storage_id", 'name' => "Storage_Name", 'iconCls' => 'lpu-product16', 'leaf' => false, 'cls' => "folder", 'qtip' => 'MerchMedService_Nick');
					}
					break;
				case 4:
					/**
					if ($data['level_two']=='LpuRegion')
						{
						$childrens = $this->dbmodel->GetLpuRegionNodeList($data);
						$field = Array('object' => "LpuRegion",'id' => "LpuRegion_id", 'name' => "LpuRegion_Name", 'iconCls' => 'lpu-region16', 'leaf' => true, 'cls' => "folder");
						}
					*/


					if ($data['level_two'] == 'LpuSection') {
						// склады
						/*$childrens = $this->dbmodel->GetStorageNodeList($data,'lpusection');
						$field = Array('object' => 'Storage', 'id' => 'Storage_id', 'name' => 'Storage_Name', 'iconCls' => 'product16', 'leaf' => true, 'cls' => 'folder');
						$val_new = GetLpuStructureChild($childrens, $field, $data['level']);*/
						// службы
						$childrens = $this->dbmodel->GetMedServiceNodeList($data,'lpusection');
						$field = Array('object' => 'MedService', 'id' => 'MedService_id', 'name' => 'MedService_Name', 'MedServiceType_SysNick' => 'MedServiceType_SysNick', 'ElectronicQueueInfo_id' => 'ElectronicQueueInfo_id', 'iconCls' => 'medservice16', 'leaf' => false, 'cls' => 'folder');
						/*$val_new2 = GetLpuStructureChild($childrens, $field, $data['level']);
						$val_new = array_merge($val_new2, $val_new);*/
						$val_new = GetLpuStructureChild($childrens, $field, $data['level']);
						//
						$childrens = $this->dbmodel->GetLpuSectionNodeList($data);
						$field = Array('object' => "LpuSection", 'id' => "LpuSection_id", 'name' => "LpuSection_Name", 'iconCls' => 'lpu-section16', 'leaf' => false, 'cls' => "folder", 'UnitDepartType_fid' => 'true', 'ElectronicQueueInfo_id' => 'ElectronicQueueInfo_id');

					}
					if ($data['level_two'] == 'All') {
						$childrens = $this->dbmodel->GetLpuSectionNodeList($data);
						$field = Array('object' => "LpuSection", 'id' => "LpuSection_id", 'name' => "LpuSection_Name", 'iconCls' => 'lpu-section16', 'leaf' => false, 'cls' => "folder", 'profile' => "LpuSection_Profile_id", 'ElectronicQueueInfo_id' => 'ElectronicQueueInfo_id');
						/**
						$val_new = GetLpuStructureChild($childrens, $field, $data['level']);
						$childrens = $this->dbmodel->GetLpuRegionNodeList($data);
						$field = Array('object' => "LpuRegion",'id' => "LpuRegion_id", 'name' => "LpuRegion_Name", 'iconCls' => 'lpu-region16', 'leaf' => true, 'cls' => "folder");
						*/
					}
					if ($data['level_two'] == 'All' && $data['object'] == 'LpuUnit') {
						// склады
						/*$childrens2 = $this->dbmodel->GetStorageNodeList($data,'lpuunit');
						$field2 = Array('object' => 'Storage', 'id' => 'Storage_id', 'name' => 'Storage_Name', 'iconCls' => 'product16', 'leaf' => true, 'cls' => 'folder', 'UnitDepartType_fid' => 'true');
						$val_new2 = GetLpuStructureChild($childrens2, $field2, $data['level']);
						$val_new = array_merge($val_new2, $val_new);*/
						// службы
						$childrens2 = $this->dbmodel->GetMedServiceNodeList($data,'lpuunit');
						$field2 = Array('object' => 'MedService', 'id' => 'MedService_id', 'name' => 'MedService_Name', 'MedServiceType_SysNick' => 'MedServiceType_SysNick', 'ElectronicQueueInfo_id' => 'ElectronicQueueInfo_id', 'iconCls' => 'medservice16', 'leaf' => false, 'cls' => 'folder');
						$val_new2 = GetLpuStructureChild($childrens2, $field2, $data['level']);
						$val_new = array_merge($val_new2, $val_new);
					}
					if ($data['level_two'] == 'All' && $data['object'] == 'Storage') {
						$childrens2 = $this->dbmodel->GetStorageNodeList($data, 'storage');
						$field2 = Array('object' => "Storage", 'id' => "Storage_id", 'name' => "Storage_Name", 'iconCls' => 'lpu-product16', 'leaf' => false, 'cls' => "folder", 'qtip' => 'MerchMedService_Nick');
						$val_new2 = GetLpuStructureChild($childrens2, $field2, $data['level']);
						$val_new = array_merge($val_new2, $val_new);
					}
					break;
				case 5:

					if ($data['object'] == 'LpuSection' && $data['level_two'] == 'All') {
						// склады
						/*$childrens = $this->dbmodel->GetStorageNodeList($data,'lpusection');
						$field = Array('object' => 'Storage', 'id' => 'Storage_id', 'name' => 'Storage_Name', 'iconCls' => 'product16', 'leaf' => true, 'cls' => 'folder');
						$val_new = GetLpuStructureChild($childrens, $field, $data['level']);*/
						// службы
						$childrens = $this->dbmodel->GetMedServiceNodeList($data,'lpusection');
						$field = Array('object' => 'MedService', 'id' => 'MedService_id', 'name' => 'MedService_Name', 'MedServiceType_SysNick' => 'MedServiceType_SysNick', 'ElectronicQueueInfo_id' => 'ElectronicQueueInfo_id', 'iconCls' => 'medservice16', 'leaf' => false, 'cls' => 'folder');
						/*$val_new2 = GetLpuStructureChild($childrens, $field, $data['level']);
						$val_new = array_merge($val_new2, $val_new);*/
						$val_new = GetLpuStructureChild($childrens, $field, $data['level']);
					}
					if (($data['level_two'] == 'LpuSection') || ($data['level_two'] == 'All')) {
						$childrens = $this->dbmodel->GetLpuSectionPidNodeList($data);
						$field = Array('object' => "LpuSection", 'id' => "LpuSection_id", 'name' => "LpuSection_Name", 'iconCls' => 'lpu-subsection16', 'leaf' => false, 'cls' => "folder", 'profile' => "LpuSection_Profile_id", 'ElectronicQueueInfo_id' => 'ElectronicQueueInfo_id');
					}
					if ($data['level_two'] == 'All' && $data['object'] == 'Storage') {
						$childrens2 = $this->dbmodel->GetStorageNodeList($data, 'storage');
						$field2 = Array('object' => "Storage", 'id' => "Storage_id", 'name' => "Storage_Name", 'iconCls' => 'lpu-product16', 'leaf' => false, 'cls' => "folder", 'qtip' => 'MerchMedService_Nick');
						$val_new2 = GetLpuStructureChild($childrens2, $field2, $data['level']);
						$val_new = array_merge($val_new2, $val_new);
					}
					break;
				case 6:
					/*if ($data['object'] == 'LpuSection' && $data['level_two'] == 'All') {
						// склады
						$childrens = $this->dbmodel->GetStorageNodeList($data,'lpusection');
						$field = Array('object' => 'Storage', 'parent_id' => $data['object_id'], 'id' => 'Storage_id', 'name' => 'Storage_Name', 'iconCls' => 'product16', 'leaf' => true, 'cls' => 'folder');
					}*/
					if ($data['object'] == 'Storage') {
						$childrens = $this->dbmodel->GetStorageNodeList($data, 'storage');
						$field = Array('object' => "Storage", 'id' => "Storage_id", 'name' => "Storage_Name", 'iconCls' => 'lpu-product16', 'leaf' => false, 'cls' => "folder", 'qtip' => 'MerchMedService_Nick');
					}
					break;
				default:
					if ( $data['object'] != 'MedService' ) {
						if ( !isSuperAdmin() && !empty($data['session']['setting']['server']['mp_is_uch']) && !empty($data['from']) && $data['from'] == 'FundHolding' ) {
							$data['uchOnly'] = true;
							$childrens = $this->dbmodel->GetLpuRegionNodeList($data);
							$childrens = FormNameNode('LpuRegion', $childrens);
							$field = Array('object' => "LpuRegion", 'id' => "LpuRegion_id", 'name' => "name", 'iconCls' => 'lpu-region16', 'leaf' => true, 'cls' => "folder");
						}
						else {
							if ( (!isSuperAdmin()&&!isTFOMSUser()&&!isSMOUser()) || empty($data['Lpu_id']) ) {
								if(empty($data['Lpu_id']))
									$data['Lpu_id'] = $_SESSION['lpu_id'];
							}

							$childrens = $this->dbmodel->GetLpuNodeList($data);
							$field = Array('object' => "Lpu", 'id' => "Lpu_id", 'name' => "Lpu_Name", 'MesAgeLpuType_Code' => "MesAgeLpuType_Code",'iconCls' => 'lpu16', 'leaf' => false, 'cls' => "folder");
						}
					}
					break;
			}
		}

		$val = GetLpuStructureChild($childrens, $field, $data['level']);
		if (count($val_new) > 0) {
			$val = array_merge($val_new, $val);
		}
		$this->ReturnData($val);
	}

	/**
	 * Это Doc-блок
	 */
	public function GetLpuAllQuery(){
		$data = $this->ProcessInputData('GetLpuAllQuery',false); // Сессию не берем
		if ($data === false) {return false;}
		
		$lpuall = $this->dbmodel->GetLpuAllQuery( $data );
		if (is_array($lpuall) && ($data['Lpu_id']>0)) {
			// Для получения фотографии пробежимся по элементам списка
			foreach( $lpuall as $k=>$row ){
				if ($row['Lpu_id']>0) {
					$lpuall[$k]['photo'] = $this->dbmodel->getOrgPhoto($row);
				}
			}
		}
		$this->ProcessModelList($lpuall, true)->ReturnData();
	}
	/**
	 * Это Doc-блок
	 */
	public function getHouseArray($arr)
	{
		$arr = trim($arr);
		//print $arr.": ";
		if (preg_match("/^([Ч|Н])\((\d+)([а-яА-Я]*)\-(\d+)([а-яА-Я]?)\)$/iu", $arr, $matches)) {
			// Четный или нечетный 
			$matches[count($matches)] = 1;
			return $matches;
		} else {
			if (preg_match("/^([\s]?)(\d+)([а-яА-Я]*)\-(\d+)([а-яА-Я]?)$/iu", $arr, $matches)) {
				// Обычный диапазон
				$matches[count($matches)] = 2;
				return $matches;
			} else {
				if (preg_match("/^(\d+[а-яА-Я]?[\/]?\d{0,3}[а-яА-Я]?(\s[к]\d{0,3})?)$/iu", $arr, $matches)) {
					//print $arr." ";
					if (preg_match("/^(\d+)/iu", $matches[1], $ms)) {
						$matches[count($matches)] = $ms[1];
					}
					else
					{
						$matches[count($matches)] = '';
					}
					$matches[count($matches)] = 3;
					return $matches;
				}
			}
		}
		return array();
	}
	/**
	 * Возвращает признак вхождения в диапазон домов
	 */
	public function HouseExist($h_arr, $houses, $lpuregion_id = null)
	{
		// Сначала разбираем h_arr и определяем: 
		// 1. Обычный диапазон 
		// 2. Четный диапазон
		// 3. Нечетный диапазон
		// 4. Перечисление 

		// Разбиваем на номера домов и диапазоны с которым будем проверять
		$hs_arr = preg_split('[,|;]', $houses, -1, PREG_SPLIT_NO_EMPTY);
		$i = 0;
		foreach ($h_arr as $row_arr)
		{
			//print $row_arr."   | ";
			$ch = $this->getHouseArray($row_arr); // сохраняемый 
			//print_r($ch);
			if (count($ch) > 0) {
				//print $i."-";
				foreach ($hs_arr as $rs_arr)
				{
					$chn = $this->getHouseArray($rs_arr); // выбранный
					if (count($chn) > 0) {
						// Проверка на правильность указания диапазона
						if ((($ch[count($ch) - 1] == 1) || ($ch[count($ch) - 1] == 2)) && ($ch[2] > $ch[4])) {
							return "Проверьте поле 'Номера домов': Неверно указан диапазон!";
						}

						// Проверка пересечений должна быть отключена согласно задаче #19653
						// Но проверка на корректность ввода должна работать
						/*if ((($ch[count($ch) - 1] == 1) && ($chn[count($chn) - 1] == 1) && ($ch[1] == 'Ч') && ($chn[1] == 'Ч')) || // сверяем четный с четным
							(($ch[count($ch) - 1] == 1) && ($chn[count($chn) - 1] == 1) && ($ch[1] == 'Н') && ($chn[1] == 'Н')) || // сверяем нечетный с нечетным
							((($ch[count($ch) - 1] == 1) || ($ch[count($ch) - 1] == 2)) && ($chn[count($chn) - 1] == 2))
						) // или любой диапазон с обычным
						{
							if (($ch[2] <= $chn[4]) && ($ch[4] >= $chn[2])) {
								$data = array(
									'LpuRegion_id' => $lpuregion_id,
									'LpuRegion_Name' => null,
									'object' => 'LpuRegion'
								);
								$this->load->model("Utils_model", "umodel");
								$region = $this->umodel->GetObjectList($data);
								return "Дома пересекаются с ранее введенным участком!<br/>Пересечение с участком: " . $region[0]['LpuRegion_Name']; // Перечесение (С) и (В) диапазонов
							}
						}
						if ((($ch[count($ch) - 1] == 1) || ($ch[count($ch) - 1] == 2)) && ($chn[count($chn) - 1] == 3)) // Любой диапазон с домом
						{
							if ((($ch[1] == 'Ч') && ($chn[2] % 2 == 0)) || // если четный
								(($ch[1] == 'Н') && ($chn[2] % 2 <> 0)) || // нечетный
								($ch[count($ch) - 1] == 2)
							) // обычный
							{
								if (($ch[2] <= $chn[2]) && ($ch[4] >= $chn[2])) {
									$data = array(
										'LpuRegion_id' => $lpuregion_id,
										'LpuRegion_Name' => null,
										'object' => 'LpuRegion'
									);
									$this->load->model("Utils_model", "umodel");
									$region = $this->umodel->GetObjectList($data);
									return "Дома пересекаются с ранее введенным участком!<br/>Пересечение с участком: " . $region[0]['LpuRegion_Name']; // Перечесение диапазона с конкретным домом
								}
							}
						}
						if ((($chn[count($chn) - 1] == 1) || ($chn[count($chn) - 1] == 2)) && ($ch[count($ch) - 1] == 3)) // Любой дом с диапазоном
						{
							if ((($chn[1] == 'Ч') && ($ch[2] % 2 == 0)) || // если четный
								(($chn[1] == 'Н') && ($ch[2] % 2 <> 0)) || // нечетный
								($chn[count($chn) - 1] == 2)
							) // обычный
							{
								if (($chn[2] <= $ch[2]) && ($chn[4] >= $ch[2])) {
									$data = array(
										'LpuRegion_id' => $lpuregion_id,
										'LpuRegion_Name' => null,
										'object' => 'LpuRegion'
									);
									$this->load->model("Utils_model", "umodel");
									$region = $this->umodel->GetObjectList($data);
									return "Дома пересекаются с ранее введенным участком!<br/>Пересечение с участком: " . $region[0]['LpuRegion_Name']; // Перечесение дома с каким-либо диапазоном
								}
							}
						}
						if (($ch[count($ch) - 1] == 3) && ($chn[count($chn) - 1] == 3)) // Дом с домом
						{
							if (mb_strtolower($ch[0]) == mb_strtolower($chn[0])) {
								$data = array(
									'LpuRegion_id' => $lpuregion_id,
									'LpuRegion_Name' => null,
									'object' => 'LpuRegion'
								);
								$this->load->model("Utils_model", "umodel");
								$region = $this->umodel->GetObjectList($data);
								return "Дома пересекаются с ранее введенным участком!<br/>Пересечение с участком: " . $region[0]['LpuRegion_Name']; // Перечесение дома с домом
							}
						}*/
					}
				}
			}
			else
			{
				return "Поле 'Номера домов' заполнено с ошибками!"; // Перечесение дома с домом
			}
		}
		return "";
	}
	/**
	 * Это Doc-блок
	 */
	protected function _isHousesExist($model, $town_id, $street_id, $lpuregion_id, $lpuregionstreet_id, $houseset)
	{
		$town_id = empty($town_id) ? 0 : $town_id;
		$street_id = empty($street_id) ? 0 : $street_id;
		$lpuregionstreet_id = empty($lpuregionstreet_id) ? 0 : $lpuregionstreet_id;
		if (($town_id == 0) && ($street_id == 0)) {
			return "Следует указать, как минимум, населенный пункт или улицу/n";
		}
		// Разбираем дома 
		$harr = preg_split('[,|;]', $houseset, -1, PREG_SPLIT_NO_EMPTY);
		
		$data = array();
		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());
		
		$res = $model->getStreetHouses($data, $town_id, $street_id, $lpuregion_id, $lpuregionstreet_id);
		if (is_array($res) && (count($res) > 0)) {
			// Перебор с проверкой 
			foreach ($res as $row)
			{
				$re = $this->HouseExist($harr, $row['LpuRegionStreet_HouseSet'], $row['LpuRegion_id']);
				if (strlen($re) > 0) {
					return $re;
				}
			}
		}
		else
		{
			$re = $this->HouseExist($harr, "");
			if (strlen($re) > 0) {
				return $re;
			}
			// Нет пересечения по улице / населенному пункту 
			return "";
		}
	}
	/**
	 * Это Doc-блок
	 */
	protected function _checkSaveMedStaffRegion($model, $data)
	{
		$result = $model->checkSaveMedStaffRegion($data);
		if (is_array($result) && (count($result) > 0)) {
			if ($result[0]['record_count'] > 0)
				return "Данный врач уже внесен на указанном участке.";
			else
				return "";
		}
		else
			return "При выполнении проверки на уникальность врача на участке<br/>сервер базы данных вернул ошибку!";
	}

	/**
	 * Это Doc-блок
	 */
	protected function _getObjectCheck($model, $data, $method)
	{
		$res = '';

		switch ($method) {
			case 'SaveLpuRegionStreet':
				// Проверка пересечений должна быть отключена согласно задаче #19653
				// Но проверка на корректность ввода должна работать, сейчас всё в одном методе
				$res = $this->_isHousesExist($model, $data['KLTown_id'], $data['KLStreet_id'], $data['LpuRegion_id'], $data['LpuRegionStreet_id'], $data['LpuRegionStreet_HouseSet']);
			break;

			case 'SaveMedStaffRegion':
				// Проверка на пересечение с ранее введенным участком
				$res = $this->_checkSaveMedStaffRegion($model, $data);
			break;
		}

		return $res;
	}

	/**
	 * Это Doc-блок
	 */
	protected function _saveObject($method) {
		$data = $this->ProcessInputData(isset($this->dbmodel->inputRules[$method]) ? $this->dbmodel->inputRules[$method] : $method, true, false, false, true);
		if ($data === false) {return false;}

		// Ошибки логики
		$err = $this->_getObjectCheck($this->dbmodel, $data, $method);
		if ( !empty($err) ) {
			$this->ReturnError($err);
			return false;
		}

		if ( method_exists($this->dbmodel, $method) ) {
			$result = $this->dbmodel->$method($data);
			$this->ProcessModelSave($result, true)->ReturnData();
			return true;
		}
		else {
			return false;
		}
	}
	/**
	 * Это Doc-блок
	 */
	public function SaveUslugaSectionTariff()
	{
		$this->_saveObject('SaveUslugaSectionTariff');
	}
	/**
	 * Это Doc-блок
	 */
	public function SaveUslugaComplexTariff()
	{
		$this->_saveObject('SaveUslugaComplexTariff');
	}
	/**
	 * Это Doc-блок
	 */
	public function SaveMedStaffRegion()
	{
		$this->_saveObject('SaveMedStaffRegion');
	}

	/**
	 * Сохранение группы отделений
	 */
	public function saveLpuUnit() {
		$this->_saveObject('saveLpuUnit');
	}

	/**
	 * Это Doc-блок
	 */
	public function saveLpuBuilding()
	{
		$this->_saveObject('saveLpuBuilding');
	}
	/**
	 * Это Doc-блок
	 */
	public function saveLpuSection()
	{
		$this->_saveObject('saveLpuSection');
	}
	/**
	 * Это Doc-блок
	 */
	public function SaveLpuRegionStreet()
	{
		$this->_saveObject('SaveLpuRegionStreet');
	}
	
	/**
	 * Сохранение территории обслуживамой подразделением
	 */
	public function SaveLpuBuildingStreet(){
		$this->_saveObject('SaveLpuBuildingStreet');
	}
	/**
	 * Это Doc-блок
	 */
	public function SaveMedServiceStreet()
	{
		$this->_saveObject('SaveMedServiceStreet');
	}
	/**
	 * Это Doc-блок
	 */
	public function SaveLpuSectionTariff()
	{
		$this->_saveObject('SaveLpuSectionTariff');
	}
	/**
	 * Это Doc-блок
	 */
	public function SaveLpuSectionBedState()
	{
		$this->_saveObject('SaveLpuSectionBedState');
	}
	/**
	 * Это Doc-блок
	 */
	public function SaveLpuSectionFinans()
	{
		$this->_saveObject('SaveLpuSectionFinans');
	}
	/**
	 * Это Doc-блок
	 */
	public function SaveLpuSectionShift()
	{
		$this->_saveObject('SaveLpuSectionShift');
	}
	/**
	 * Это Doc-блок
	 */
	public function SaveLpuSectionLicence()
	{
		$this->_saveObject('SaveLpuSectionLicence');
	}
	/**
	 * Это Doc-блок
	 */
	public function SaveLpuSectionTariffMes()
	{
		$this->_saveObject('SaveLpuSectionTariffMes');
	}
	/**
	 * Это Doc-блок
	 */
	public function SaveLpuSectionPlan()
	{
		$this->_saveObject('SaveLpuSectionPlan');
	}
	/**
	 * Это Doc-блок
	 */
	public function SaveLpuSectionQuote()
	{
		$this->_saveObject('SaveLpuSectionQuote');
	}
	/**
	 * Это Doc-блок
	 */
	public function SavePersonDopDispPlan()
	{
		$this->_saveObject('SavePersonDopDispPlan');
	}

	/**
	 * Сохранение участка
	 */
	public function saveLpuRegion() {
		$data = $this->ProcessInputData($this->dbmodel->inputRules['saveLpuRegion'], true);
		if ($data === false) {return false;}

		$result = $this->dbmodel->saveLpuRegion($data);
		$this->ProcessModelSave($result, true)->ReturnData();

		return true;
	}

	/**
	 * Это Doc-блок
	 */
	public function SaveUslugaSection()
	{
		$val = array();
		$data = $this->ProcessInputData('SaveUslugaSection',true);
		if ($data === false) {return false;}
		$result = $this->dbmodel->SaveUslugaSection($data);
		if (is_array($result) && (count($result) == 1)) {
			if ($result[0]['Error_Code'] > 0) {
				$result[0]['success'] = false;
				$val = $result[0];
			}
			else
			{
				// Получаем запись по идешнику 
				$data['object'] = 'UslugaSection';
				$data['UslugaSection_id'] = $result[0]['UslugaSection_id'];
				$data['LpuSection_id'] = '';
				$data['Usluga_id'] = '';
				$data['UslugaPrice_ue'] = '';
				$data['level'] = 3;
				$data['UslugaSection_Code'] = '';
				unset($data['pmUser_id']);
				unset($data['MedStaffFact_id']); // неожиданно вообще, кстати
				unset($data['Server_id']);
				$res = $this->dbmodel->GetLpuUsluga($data);
				if (is_array($res) && count($res) > 0) {
					$res[0]['success'] = true;
					$val = $res[0];
				}
				else
				{
					// Получить не удалось, почему-то 
					$val = array('success' => false, 'Error_Code' => 100006, 'Error_Msg' => 'Не удалось получить сохраненную запись');
				}
			}
		}
		else
		{
			$val = array('success' => false, 'Error_Code' => 100002, 'Error_Msg' => 'Системная ошибка при выполнении скрипта');
		}
		array_walk($val, 'ConvertFromWin1251ToUTF8');
		$this->ReturnData($val);
	}

	/**
	 * Это Doc-блок
	 */
	public function getLpuUnitList()
	{
		$data = $_REQUEST;
		$getLpuUnitList = $this->dbmodel->getLpuUnitList($data);
		if ($getLpuUnitList != false && count($getLpuUnitList) > 0) {
			foreach ($getLpuUnitList as $rows)
			{
				$val[] = array(
					'LpuUnit_id' => toUTF(trim($rows['LpuUnit_id'])),
					'LpuUnit_Code' => toUTF(trim($rows['LpuUnit_Code'])),
					'LpuUnitType_id' => toUTF(trim($rows['LpuUnitType_id'])),
					'LpuUnit_Name' => toUTF(trim($rows['LpuUnit_Name']))
				);
			}
			$this->ReturnData($val);
		}
		else
			ajaxErrorReturn();
	}
	/**
	 * Это Doc-блок
	 */
	public function getLpuUnitCombo()
	{
		$data = $_REQUEST;
		// Получаем сессионные переменные
		$data['session'] = $_SESSION;

		$getLpuUnitList = $this->dbmodel->getLpuUnitCombo($data);
		if ($getLpuUnitList != false && count($getLpuUnitList) > 0) {
			/*foreach ($getLpuUnitList as $rows)
			{
				$val[] = array(
					'LpuUnit_id' => toUTF(trim($rows['LpuUnit_id'])),
					'LpuUnit_Code' => toUTF(trim($rows['LpuUnit_Code'])),
					'LpuUnit_Name' => toUTF(trim($rows['LpuUnit_Name'])),
					'LpuBuilding_id' => toUTF(trim($rows['LpuBuilding_id'])),
					'LpuBuilding_Name' => toUTF(trim($rows['LpuBuilding_Name']))
				);
			}*/
			$this->ReturnData($getLpuUnitList);
		}
		else
			ajaxErrorReturn();
	}
	/**
	* comment
	*/

	/**
	 * Получение списка кодов подразделений
	 */
	public function getLpuUnitSetCombo()
	{
		$data = $this->ProcessInputData('getLpuUnitSetCombo',true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->getLpuUnitSetCombo($data);

		$this->ProcessModelList($response,true,true)->ReturnData();
		return true;
	}
	/**
	* comment
	*/

	/**
	 * Это Doc-блок
	 */
	public function GetLpuUnitEdit()
	{
		$data = $this->ProcessInputData('GetLpuUnitEdit',false); // Сессию не нужно
		if ($data === false) {return false;}
		
		$getLpuUnitList = $this->dbmodel->getLpuUnitList($data);
		if (is_array($getLpuUnitList) && ($data['LpuUnit_id']>0)) {
			// Для получения фотографии пробежимся по элементам списка
			foreach ($getLpuUnitList as $k => $row) {
				if ($row['LpuUnit_id']>0 && isset($row['Lpu_id'])) {
					$getLpuUnitList[$k]['photo'] = $this->dbmodel->getOrgPhoto($row);
				}
			}
		}
		$this->ProcessModelList($getLpuUnitList, true)->ReturnData();
		return true;
	}
	/**
	 * Это Doc-блок
	 */
	public function getLpuUnit()
	{
		$data = $_REQUEST;
		$getLpuUnitList = $this->dbmodel->getLpuUnitList($data);
		$this->ProcessModelList($getLpuUnitList, true, true)->ReturnData();
	}
	/**
	 * Это Doc-блок
	 */
	public function GetLpuBuilding()
	{
		$data = $this->ProcessInputData('GetLpuBuilding',false); // Сессию не берем
		if ($data === false) {return false;}
		
		$getLpuBuildingList = $this->dbmodel->getLpuBuildingList($data);
		// Для получения фотографии пробежимся по элементам списка
		if (is_array($getLpuBuildingList) && ($data['LpuBuilding_id']>0)) {
			foreach ($getLpuBuildingList as $k => $row) {
				if ($row['LpuBuilding_id']>0 && isset($row['Lpu_id'])) {
					$getLpuBuildingList[$k]['photo'] = $this->dbmodel->getOrgPhoto($row);
				}
			}
		}
		$this->ProcessModelList($getLpuBuildingList, true, true)->ReturnData();
	}


	/**
	 * Это Doc-блок
	 */
	public function GetLpuRegion()
	{
		$data = $_REQUEST;
		$getdata = $this->dbmodel->getLpuRegionList($data);
		$this->ProcessModelList($getdata,true,true)->ReturnData();
		return true;
	}

	/**
	* Врачи на участках - получение списка и отдельной записи
	*/
	/**
	 * Это Doc-блок
	 */
	public function GetMedStaffRegion()
	{
		$data = $_REQUEST;
		array_walk($data, 'ConvertFromUTF8ToWin1251');
		// Получаем сессионные переменные
		$data['session'] = $_SESSION;
		
		$getdata = $this->dbmodel->getMedStaffRegion($data);
		$this->ProcessModelList($getdata,true,true)->ReturnData();
		return true;
		/*$val = array();
		if ($getdata != false && count($getdata) > 0) {
			foreach ($getdata as $rows)
			{
				$val[] = array(
					'MedStaffRegion_id' => $rows['MedStaffRegion_id'],
					'MedStaffFact_id' => $rows['MedStaffFact_id'],
					'MedStaffRegion_isMain' => $rows['MedStaffRegion_isMain'],
					'LpuRegion_id' => $rows['LpuRegion_id'],
					'Person_Fio' => toUTF(trim($rows['Person_Fio'])),
					'LpuRegion_Name' => toUTF(trim($rows['LpuRegion_Name'])),
					'LpuRegionType_id' => $rows['LpuRegionType_id'],
					'MedStaffRegion_begDate' => $rows['MedStaffRegion_begDate'],
					'MedStaffRegion_endDate' => $rows['MedStaffRegion_endDate'],
					'Lpu_id' => $rows['Lpu_id']
				);
			}
		}*/
		//$this->ReturnData($val);
	}

	/**
	* Тарифы на отделении
	*/
	/**
	 * Это Doc-блок
	 */
	public function GetLpuSectionTariff()
	{
		$data = $this->ProcessInputData('GetLpuSectionTariff',true);
		if ($data === false) {return false;}

		$getdata = $this->dbmodel->getLpuSectionTariff($data);
		$val = array();
		if ($getdata != false && count($getdata) > 0) {
			foreach ($getdata as $rows)
			{
				$val[] = array(
					'LpuSectionTariff_id' => $rows['LpuSectionTariff_id'],
					'LpuSection_id' => $rows['LpuSection_id'],
					'TariffClass_id' => $rows['TariffClass_id'],
					'TariffClass_Name' => toUTF(trim($rows['TariffClass_Name'])),
					'LpuSectionTariff_Tariff' => $rows['LpuSectionTariff_Tariff'],
					'LpuSectionTariff_TotalFactor' => $rows['LpuSectionTariff_TotalFactor'],
					'LpuSectionTariff_setDate' => toUTF(trim($rows['LpuSectionTariff_setDate'])),
					'LpuSectionTariff_disDate' => toUTF(trim($rows['LpuSectionTariff_disDate']))
				);
			}
		}
		$this->ReturnData($val);
	}

	/**
	* Смены на отделении
	*/
	/**
	 * Это Doc-блок
	 */
	public function GetLpuSectionShift()
	{
		$data = $_REQUEST;
		array_walk($data, 'ConvertFromUTF8ToWin1251');

		// Получаем сессионные переменные
		$data['session'] = $_SESSION;
		
		$getdata = $this->dbmodel->getLpuSectionShift($data);
		$val = array();
		if ($getdata != false && count($getdata) > 0) {
			foreach ($getdata as $rows)
			{
				$val[] = array(
					'LpuSectionShift_id' => $rows['LpuSectionShift_id'],
					'LpuSection_id' => $rows['LpuSection_id'],
					'LpuSectionShift_Count' => $rows['LpuSectionShift_Count'],
					'LpuSectionShift_setDate' => toUTF(trim($rows['LpuSectionShift_setDate'])),
					'LpuSectionShift_disDate' => toUTF(trim($rows['LpuSectionShift_disDate']))
				);
			}
		}
		$this->ReturnData($val);
	}

	/**
	* Койки на отделении
	*/
	/**
	 * Это Doc-блок
	 */
	public function GetLpuSectionBedState()
	{
		// Получаем сессионные переменные
		$data = $this->ProcessInputData('GetLpuSectionBedState',true);
		if ($data === false) {return false;}

		$getdata = $this->dbmodel->getLpuSectionBedState($data);
		/*$val = array();
		if ($getdata != false && count($getdata) > 0) {
			foreach ($getdata as $rows) {
				$val[] = array(
					'LpuSectionBedState_id' => $rows['LpuSectionBedState_id'],
					'LpuSection_id' => $rows['LpuSection_id'],
					'LpuSectionProfile_id' => $rows['LpuSectionProfile_id'],
					'LpuSectionBedProfile_id' => $rows['LpuSectionBedProfile_id'],
					'LpuSectionProfile_Name' => toUTF(trim($rows['LpuSectionProfile_Name'])),
					'LpuSectionBedState_Plan' => $rows['LpuSectionBedState_Plan'],
					'LpuSectionBedState_ProfileName' => toUTF(trim($rows['LpuSectionBedState_ProfileName'])),
					'LpuSectionBedState_Fact' => $rows['LpuSectionBedState_Fact'],
					'LpuSectionBedState_Repair' => $rows['LpuSectionBedState_Repair'],
					'LpuSectionBedState_begDate' => toUTF(trim($rows['LpuSectionBedState_begDate'])),
					'LpuSectionBedState_endDate' => toUTF(trim($rows['LpuSectionBedState_endDate']))
				);
			}
		}*/
		$this->ReturnData($getdata);
	}

	/**
	* Финансирование на отделении
	*/
	/**
	 * Это Doc-блок
	 */
	public function GetLpuSectionFinans()
	{
		$data = $this->ProcessInputData('getLpuSectionFinans', true);
		if ($data) {
			$response = $this->dbmodel->getLpuSectionFinans($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	* Лицензии на отделении
	*/
	/**
	 * Это Doc-блок
	 */
	public function GetLpuSectionLicence()
	{
		$data = $_REQUEST;
		array_walk($data, 'ConvertFromUTF8ToWin1251');
		
		// Получаем сессионные переменные
		$data['session'] = $_SESSION;
		
		$getdata = $this->dbmodel->getLpuSectionLicence($data);
		$val = array();
		if ($getdata != false && count($getdata) > 0) {
			foreach ($getdata as $rows)
			{
				$val[] = array(
					'LpuSectionLicence_id' => $rows['LpuSectionLicence_id'],
					'LpuSection_id' => $rows['LpuSection_id'],
					'LpuSectionLicence_Num' => toUTF(trim($rows['LpuSectionLicence_Num'])),
					'LpuSectionLicence_begDate' => toUTF(trim($rows['LpuSectionLicence_begDate'])),
					'LpuSectionLicence_endDate' => toUTF(trim($rows['LpuSectionLicence_endDate']))
				);
			}
		}
		$this->ReturnData($val);
	}

	/**
	* Тарифы МЭС на отделении
	*/
	/**
	 * Это Doc-блок
	 */
	public function GetLpuSectionTariffMes()
	{
		$data = $_REQUEST;
		array_walk($data, 'ConvertFromUTF8ToWin1251');
		$getdata = $this->dbmodel->getLpuSectionTariffMes($data);
		$val = array();
		if ($getdata != false && count($getdata) > 0) {
			foreach ($getdata as $rows) {
				$val[] = array(
					'LpuSectionTariffMes_id' => $rows['LpuSectionTariffMes_id'],
					'LpuSection_id' => $rows['LpuSection_id'],
					'Mes_id' => $rows['Mes_id'],
					'Mes_Code' => $rows['Mes_Code'],
					'Diag_Name' => toUTF(trim($rows['Diag_Name'])),
					'TariffMesType_id' => $rows['TariffMesType_id'],
					'TariffMesType_Name' => toUTF(trim($rows['TariffMesType_Name'])),
					'LpuSectionTariffMes_Tariff' => $rows['LpuSectionTariffMes_Tariff'],
					'LpuSectionTariffMes_setDate' => toUTF(trim($rows['LpuSectionTariffMes_setDate'])),
					'LpuSectionTariffMes_disDate' => toUTF(trim($rows['LpuSectionTariffMes_disDate']))
				);
			}
		}
		$this->ReturnData($val);
	}

	/**
	* Планирование на отделении
	*/
	/**
	 * Это Doc-блок
	 */
	public function GetLpuSectionPlan()
	{
		$data = $_REQUEST;
		array_walk($data, 'ConvertFromUTF8ToWin1251');
		$getdata = $this->dbmodel->getLpuSectionPlan($data);
		$val = array();
		if ($getdata != false && count($getdata) > 0) {
			foreach ($getdata as $rows) {
				$val[] = array(
					'LpuSectionPlan_id' => $rows['LpuSectionPlan_id'],
					'LpuSection_id' => $rows['LpuSection_id'],
					'LpuSectionPlanType_id' => $rows['LpuSectionPlanType_id'],
					'LpuSectionPlanType_Name' => toUTF(trim($rows['LpuSectionPlanType_Name'])),
					'LpuSectionPlan_setDate' => toUTF(trim($rows['LpuSectionPlan_setDate'])),
					'LpuSectionPlan_disDate' => toUTF(trim($rows['LpuSectionPlan_disDate']))
				);
			}
		}
		$this->ReturnData($val);
	}

	/**
	 * Получение данных для вкладки "План диспансеризации" на  уровне ЛПУ и для формы редактирования
	 */
	public function GetPersonDopDispPlan()
	{
		$data = $this->ProcessInputData('GetPersonDopDispPlan', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->GetPersonDopDispPlan($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
	
	/**
	 * Получение данных для вкладыки "Планирование" на  уровне ЛПУ и для формы редактирования
	 *
	 */
	/**
	 * Получает данные для вкладки "Планирование" и для формы редактирования на уровне ЛПУ.
	 * Также через допзапрос получает для каждой записи данные фактически выполненного муниципального заказа из реестров.
	 * Возвращает массив данных в JSON-формате.
	 *
	 * @access public
	 * @param array $_POST
	 *
	 * @return array Возвращает массив данных в JSON-формате.
	 */
	public function GetLpuSectionQuote()
	{
		$data = $this->ProcessInputData('GetLpuSectionQuote', true);
		if ($data) {
			$response = $this->dbmodel->getLpuSectionQuote($data);
			$outdata = $this->ProcessModelList($response, true, true)->GetOutData();
			// после получения массива надо дополнить его данными о сумме фактически выполненного муниципального заказа с реестровой базы
			//unset($this->db);
			//$this->load->database('registry');
			foreach ($outdata as &$row)
			{
				// Передаем в том числе и полученные данные из запроса для фильтрации 
				// Временно закрыто до внедрения кеширования 
				/**
				$response = $this->dbmodel->getLpuSectionQuoteFact(array_merge($data, $row));
				$record = $this->ProcessModelList($response, false, true)->GetOutData(0);
				$row['LpuSectionQuote_Fact'] = $record['LpuSectionQuote_Fact'];
				*/
				$row['LpuSectionQuote_Fact'] = '';

			}
			$this->ReturnData($outdata);
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Это Doc-блок
	 */
	public function CheckLpuSectionBedState()
	{
		$data = $this->ProcessInputData('CheckLpuSectionBedState', true);
		if ($data === false) {
			return false;
		}
		// проверка пересечения с ранее введенными данными о коечном фонде по этому отделению
		/**устарело, оставил при сохранении (с) Night
		$getdata = $this->dbmodel->checkLpuSectionBedState($data);
		if (!is_array($getdata))
		{
			echo json_encode(array("success"=>false, "ErrorMessage"=>toUTF('Ошибка запроса БД при проверке пересечения с ранее введенными данными о коечном фонде по этому отделению!')));
			return false;
		}
		if (count($getdata) > 0)
		{
			echo json_encode(array("success"=>false, "ErrorMessage"=>toUTF('Существуют ранее введенные данные о коечном фонде по этому отделению, которые пересекаются с указаным периодом действия!')));
			return false;
		}
		*/
		// проверка суммы плановых коек
		$bedstate_plan_parent = 0;
		$bedstate_plan_sum = 0;
		$data['LpuSection_isParent'] = null;
		$test1 = '';
		if (isset($data['LpuSection_pid']) AND $data['LpuSection_pid'] > 0 AND $data['child_count'] == 0) {
			// это подотделение
			$data['LpuSection_isParent'] = false;
			$bedstate_plan_sum = $data['LpuSectionBedState_Plan'];
			//$test1 .= ', '.$data['LpuSection_id'].' - '.$data['LpuSectionBedState_Plan'];
		}
		if (empty($data['LpuSection_pid']) AND $data['child_count'] > 0) {
			// это отделение-родитель
			$data['LpuSection_isParent'] = true;
			$bedstate_plan_parent = $data['LpuSectionBedState_Plan'];
		}
		if (isset($data['LpuSection_isParent'])) {
			$getdata = $this->dbmodel->getLpuSectionBedStatePlan($data);
			if ($getdata === false) {
				echo json_encode(array("success" => false, "ErrorMessage" => toUTF('Ошибка запроса БД при проверке суммы плановых коек!')));
				return false;
			}
			if (is_array($getdata)) {
				foreach ($getdata as $row)
				{
					//LpuSectionBedState_Plan LpuSection_pid LpuSection_id
					if ($data['LpuSection_id'] == $row['LpuSection_id']) {
						continue;
					}
					if ($data['LpuSection_pid'] == $row['LpuSection_id']) {
						$bedstate_plan_parent = $row['LpuSectionBedState_Plan'];
					}
					else
					{
						$bedstate_plan_sum += $row['LpuSectionBedState_Plan'];
						//$test1 .= ', '.$row['LpuSection_id'].' - '.$row['LpuSectionBedState_Plan'];
					}
				}
			}
			if (empty($bedstate_plan_parent)) {
				echo json_encode(array("success" => false, "ErrorMessage" => toUTF('Данные о плановых койках отделения-родителя отсутствуют или устарели! Сначала заполните действительными данными коечный фонд отделения-родителя!')));
				return false;
			}
			if ($bedstate_plan_parent < $bedstate_plan_sum) {
				echo json_encode(array("success" => false, "ErrorMessage" => toUTF('Cумма плановых коек подотделений (' . $bedstate_plan_sum . ') не должна превышать количество плановых коек в отделении-родителе (' . $bedstate_plan_parent . ')!' . $test1)));
				return false;
			}
		}
		echo json_encode(array("success" => true, "ErrorMessage" => ""));
		return true;
	}
	/**
	 * Это Doc-блок
	 */
	public function CheckLpuSectionFinans()
	{
		$data = $this->ProcessInputData('CheckLpuSectionFinans',true);
		if ($data === false) {return false;}

		$getdata = $this->dbmodel->checkLpuSectionFinans($data);

		if (is_array($getdata) && count($getdata) > 0) {
			$this->ReturnError('Существуют ранее введенные данные о способе финансирования по этому отделению с той же датой начала!');
		}
		else
		{
			echo json_encode(array("success" => true, "Error_Msg" => ""));
		}
	}
	/**
	 * Это Doc-блок
	 */
	public function CheckLpuSectionTariff()
	{
		$data = $this->ProcessInputData('CheckLpuSectionTariff',true);
		if ($data === false) {return false;}

		$getdata = $this->dbmodel->checkLpuSectionTariff($data);
		$val = array();

		if (count($getdata) > 0) {
			echo json_encode(array("success" => false, "ErrorMessage" => toUTF('Существует ранее введенный тариф аналогичного вида по этому отделению с той же датой начала!')));

		}
		else
		{
			echo json_encode(array("success" => true, "ErrorMessage" => ""));
		}
	}
	/**
	 * Это Doc-блок
	 */
	public function CheckLpuSectionLicence()
	{
		$data = $this->ProcessInputData('CheckLpuSectionLicence',true);
		if ($data === false) {return false;}
		$getdata = $this->dbmodel->checkLpuSectionLicence($data);
		$val = array();

		if (count($getdata) > 0) {
			echo json_encode(array("success" => false, "ErrorMessage" => toUTF('Существует ранее введенная лицензия по этому отделению с той же датой начала!')));
		}
		else
		{
			echo json_encode(array("success" => true, "ErrorMessage" => ""));
		}
	}

	/**
	 * Это Doc-блок
	 */
	public function CheckLpuSectionShift()
	{
		$data = $this->ProcessInputData('CheckLpuSectionShift',true);
		if ($data === false) {return false;}
		
		$res = $this->dbmodel->checkLpuSectionShift($data);
		$this->ReturnData($res);
	}

	/**
	 * Это Doc-блок
	 */
	public function CheckLpuSectionTariffMes()
	{
		$data = $this->ProcessInputData('CheckLpuSectionTariffMes',true);
		if ($data === false) {return false;}

		$getdata = $this->dbmodel->checkLpuSectionTariffMes($data);
		$val = array();

		if (count($getdata) > 0) {
			echo json_encode(array("success" => false, "ErrorMessage" => toUTF('Существуют ранее введенные данные о тарифах ' . getMESAlias() . ' по этому отделению, по этому ' . getMESAlias() . ' с той же датой начала!')));
		} else {
			echo json_encode(array("success" => true, "ErrorMessage" => ""));
		}
	}

	/**
	 * Это Doc-блок
	 */
	public function CheckLpuSectionPlan()
	{
		$data = $this->ProcessInputData('CheckLpuSectionPlan',true);
		if($data === false) {return false;}

		$getdata = $this->dbmodel->checkLpuSectionPlan($data);
		$val = array();

		if (count($getdata) > 0) {
			echo json_encode(array("success" => false, "ErrorMessage" => toUTF('Существуют ранее введенные данные о планировании по этому отделению с той же датой начала!')));
		} else {
			echo json_encode(array("success" => true, "ErrorMessage" => ""));
		}
	}


	/**
	* Услуги на отделениях - в структуре ЛПУ
	*/
	public function GetUslugaSectionTariff()
	{
		$data = $_REQUEST;
		// Получаем сессионные переменные
		$data['session'] = $_SESSION;
		
		$getdata = $this->dbmodel->getUslugaSectionTariff($data);
		$val = array();
		if ($getdata != false && count($getdata) > 0) {
			foreach ($getdata as $rows)
			{
				$val[] = array(
					'UslugaSectionTariff_id' => $rows['UslugaSectionTariff_id'],
					'UslugaSection_id' => $rows['UslugaSection_id'],
					'UslugaSectionTariff_Tariff' => $rows['UslugaSectionTariff_Tariff'],
					'UslugaSectionTariff_begDate' => $rows['UslugaSectionTariff_begDate'],
					'UslugaSectionTariff_endDate' => $rows['UslugaSectionTariff_endDate']
				);
			}
		}
		$this->ReturnData($val);
	}


	/**
	 * Услуги на отделениях (комплексные) - в структуре ЛПУ
	 */
	public function GetUslugaComplexTariff()
	{
		$data = $_REQUEST;
		// Получаем сессионные переменные
		$data['session'] = $_SESSION;
		
		$getdata = $this->dbmodel->getUslugaComplexTariff($data);
		$val = array();
		if ($getdata != false && count($getdata) > 0) {
			foreach ($getdata as $rows)
			{
				$val[] = array(
					'UslugaComplexTariff_id' => $rows['UslugaComplexTariff_id'],
					'UslugaComplex_id' => $rows['UslugaComplex_id'],
					'UslugaComplexTariff_Tariff' => $rows['UslugaComplexTariff_Tariff'],
					'UslugaComplexTariff_begDate' => $rows['UslugaComplexTariff_begDate'],
					'UslugaComplexTariff_endDate' => $rows['UslugaComplexTariff_endDate']
				);
			}
		}
		$this->ReturnData($val);
	}
	

	/**
	* Улицы на участках - в структуре ЛПУ
	*/
	public function GetLpuRegionStreet()
	{
		$data = $_REQUEST;
		// Получаем сессионные переменные
		$data['session'] = $_SESSION;
		
		$getdata = $this->dbmodel->getLpuRegionStreet($data);
		$val = array();
		if ($getdata != false && count($getdata) > 0) {
			foreach ($getdata as $rows)
			{
				$val[] = array(
					'LpuRegionStreet_id' => $rows['LpuRegionStreet_id'],
					'LpuRegion_id' => $rows['LpuRegion_id'],
					'KLCountry_id' => $rows['KLCountry_id'],
					'KLRGN_id' => $rows['KLRGN_id'],
					'KLSubRGN_id' => $rows['KLSubRGN_id'],
					'KLCity_id' => $rows['KLCity_id'],
					'KLTown_id' => $rows['KLTown_id'],
					'KLTown_Name' => toUTF(trim($rows['KLTown_Name'])),
					'KLStreet_id' => $rows['KLStreet_id'],
					'KLStreet_Name' => toUTF(trim($rows['KLStreet_Name'])),
					'LpuRegionStreet_HouseSet' => toUTF(trim($rows['LpuRegionStreet_HouseSet'])),
					'LpuRegionStreet_IsAll' => $rows['LpuRegionStreet_IsAll'] == 2 ? 1 : 0
				);
			}
		}
		$this->ReturnData($val);
	}

	
	/**
	* Территории на подстанциях - в структуре ЛПУ
	*/
	public function GetLpuBuildingStreet()
	{
		$data = $_REQUEST;
		// Получаем сессионные переменные
		$data['session'] = $_SESSION;
		
		$getdata = $this->dbmodel->getLpuBuildingStreet($data);
		$val = array();
		if ($getdata != false && count($getdata) > 0) {
			foreach ($getdata as $rows)
			{
				$val[] = array(
					'LpuBuildingStreet_id' => $rows['LpuBuildingStreet_id'],
					'LpuBuilding_id' => $rows['LpuBuilding_id'],
					'KLCountry_id' => $rows['KLCountry_id'],
					'KLRGN_id' => $rows['KLRGN_id'],
					'KLSubRGN_id' => $rows['KLSubRGN_id'],
					'KLCity_id' => $rows['KLCity_id'],
					'KLTown_id' => $rows['KLTown_id'],
					'KLTown_Name' => toUTF(trim($rows['KLTown_Name'])),
					'KLStreet_id' => $rows['KLStreet_id'],
					'KLStreet_Name' => toUTF(trim($rows['KLStreet_Name'])),
					'LpuBuildingStreet_HouseSet' => toUTF(trim($rows['LpuBuildingStreet_HouseSet']))
				);
			}
		}
		$this->ReturnData($val);
	}
	
	
	/**
	* Территории в службах - в структуре ЛПУ
	*/
	public function GetMedServiceStreet()
	{
		$data = $_REQUEST;
		// Получаем сессионные переменные
		$data['session'] = $_SESSION;
		
		$getdata = $this->dbmodel->getMedServiceStreet($data);
		$val = array();
		if ($getdata != false && count($getdata) > 0) {
			foreach ($getdata as $rows)
			{
				$val[] = array(
					'MedServiceStreet_id' => $rows['MedServiceStreet_id'],
					'MedService_id' => $rows['MedService_id'],
					'KLCountry_id' => $rows['KLCountry_id'],
					'KLRGN_id' => $rows['KLRGN_id'],
					'KLSubRGN_id' => $rows['KLSubRGN_id'],
					'KLSubRGN_Name' => $rows['KLSubRGN_Name'],
					'KLCity_id' => $rows['KLCity_id'],
					'KLTown_id' => $rows['KLTown_id'],
					'KLTown_Name' => toUTF(trim($rows['KLTown_Name'])),
					'KLStreet_id' => $rows['KLStreet_id'],
					'KLStreet_Name' => toUTF(trim($rows['KLStreet_Name'])),
					'MedServiceStreet_HouseSet' => toUTF(trim($rows['MedServiceStreet_HouseSet'])),
					'MedServiceStreet_isAll' => $rows['MedServiceStreet_isAll']
				);
			}
		}
		$this->ReturnData($val);
	}
	
	/**
	 * Функция получает список отделений которые могут стать родительскими для данного отделения
	 */
	public function getLpuSectionPid()
	{
		$data = $this->ProcessInputData('getLpuSectionPid',true);
		if ($data === false) {return false;}
		$list = $this->dbmodel->getLpuSectionPid($data);
		$this->ProcessModelList($list, true, true)->ReturnData();
	}

	/**
	 * Функция получает отделение для вкладки "Описание" структуры МО
	 */
	public function GetLpuSectionEdit()
	{
		$data = $this->ProcessInputData('GetLpuSectionEdit',false); // Lpu_id не нужно, поэтому сессию не берем
		if ($data === false) {return false;}
		$getLpuSectionList = $this->dbmodel->getLpuSectionList($data);
		// Для получения фотографии пробежимся по элементам списка
		if (is_array($getLpuSectionList) && ($data['LpuSection_id']>0)) {
			foreach ($getLpuSectionList as $k => $row) {
				if ($row['LpuSection_id']>0 && isset($row['Lpu_id'])) {
					$getLpuSectionList[$k]['photo'] = $this->dbmodel->getOrgPhoto($row);
				}
			}
		}
		$this->ProcessModelList($getLpuSectionList, true)->ReturnData();
		return true;
	}
	/**
	 * Получение списка услуг на структуре ЛПУ взависимости от level
	 */
	public function GetLpuUsluga()
	{
		$data = $_REQUEST;
		$val = array();

		// Получаем сессионные переменные
		$data['session'] = $_SESSION;
		
		$getList = $this->dbmodel->GetLpuUsluga($data);
		if ($getList != false && count($getList) > 0) {
			if ((isset($data['level'])) && ($data['level'] == 1)) {
				foreach ($getList as $rows)
				{
					$val[] = array(
						'UslugaSection_id' => $rows['Usluga_id'],
						'Usluga_id' => $rows['Usluga_id'],
						'Usluga_Code' => toUTF(trim($rows['Usluga_Code'])),
						'Usluga_Name' => toUTF(trim($rows['Usluga_Name'])),
						'UslugaPrice_ue' => number_format($rows['UslugaPrice_ue'], 2, '.', '')
					);
				}
			}
			else
			{
				foreach ($getList as $rows)
				{
					$val[] = array(
						'UslugaSection_id' => $rows['UslugaSection_id'],
						'LpuSection_id' => $rows['LpuSection_id'],
						//'LpuSection_Name'=>toUTF(trim($rows['LpuSection_Name'])),
						//'LpuUnit_id'=>$rows['LpuUnit_id'],
						'Usluga_id' => $rows['Usluga_id'],
						'Usluga_Code' => toUTF(trim($rows['Usluga_Code'])),
						'Usluga_Name' => toUTF(trim($rows['Usluga_Name'])),
						'UslugaPrice_ue' => number_format($rows['UslugaPrice_ue'], 2, '.', '')
					);
				}
			}
		}
		$this->ReturnData($val);
	}

	/**
	 * Это Doc-блок
	 */
	public function copyUslugaSectionList()
	{
		$val = array();

		// Получаем сессионные переменные
		$data = $this->ProcessInputData('copyUslugaSectionList', true);
		if ($data === false)
		{
			return false;
		}

		$response = $this->dbmodel->copyUslugaSectionList($data);

		if ($response==true) {
			$val['success'] = true;
		}
		else {
			$val['Error_Msg'] = 'Ошибка при выполнении запроса к базе данных (копирование услуг)';
			$val['success'] = false;
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');

		$this->ReturnData($val);

		return true;
	}

	/**
	 * Это Doc-блок
	 */
	public function ExportErmpStaff()
	{
		$data = $this->ProcessInputData('ExportErmpStaff',true);
		if($data === false) {
			return false;
		}
		set_time_limit(0); //Отключаем таймлимит, ибо запрос выполняется слишком долго.
		$response = $this->dbmodel->ExportErmpStaff($data); //Получаем данные из запроса.
		//array_walk_recursive($response, 'ConvertFromUTF8ToWin1251', true);
		$this->load->library('parser');
		reset($response);
		if(($data['session']['region']['nick'] == 'kareliya') && ($data['Lpu_id'] == '100500')){ //Для Карелии, если выбрано значение "все", данные по каждому ЛПУ пихаем в отдельный файл
			$lpu_array = array();
			$k=0;
			$lpu_array[$k][] = $response[0];
			$lpu_nick = $response[0]['UZ_Name'];
			for($i=1;$i<sizeof($response);$i++){
				if($lpu_nick == $response[$i]['UZ_Name'])
					$lpu_array[$k][] = $response[$i];
				else{
					$lpu_nick = $response[$i]['UZ_Name'];
					$k=$k+1;
					$lpu_array[$k][] = $response[$i];
				}
			}
			//Создаем zip-архив.
			$out_dir = "st_".date("Y-m-d_H-i-s");
			if (!file_exists(EXPORTPATH_STAFF)) {
				mkdir( EXPORTPATH_STAFF );
			}
			mkdir( EXPORTPATH_STAFF.$out_dir );
			$file_zip_name = EXPORTPATH_STAFF.$out_dir."/st_".date("Y-m-d_H-i-s").".zip";
			$zip=new ZipArchive();
			$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
			$file_staff_data_name = array();
			for ($i=0;$i<sizeof($lpu_array);$i++){
				$lpu_nick = $lpu_array[$i][0]['UZ_Name'];

				$staff_fields['STAFF_FIELDS'] = $lpu_array[$i];
				$xml = "<?xml version=\"1.0\"?>\r\n" . $this->parser->parse('export_xml/exportermpstaff', $staff_fields, true);

				$file_staff_data_name[$i] = str_replace("\"",'',EXPORTPATH_STAFF.$out_dir."/st_".$lpu_nick."_".date("Y-m-d_H-i-s").".xml");
				file_put_contents($file_staff_data_name[$i], $xml);
				$zip->AddFile($file_staff_data_name[$i],"st_".iconv('utf-8','cp866',str_replace("\"",'',$lpu_nick))."_".date("Y-m-d_H-i-s").".xml");
			}

			$zip->close();
			for ($i=0;$i<sizeof($lpu_array);$i++){
				unlink($file_staff_data_name[$i]);
			}
		}
		else {
			$staff_fields['STAFF_FIELDS'] = $response;
			$xml = "<?xml version=\"1.0\"?>\r\n" . $this->parser->parse('export_xml/exportermpstaff', $staff_fields, true);
			$out_dir = "st_".date("Y-m-d_H-i-s");
			if (!file_exists(EXPORTPATH_STAFF)) {
				mkdir( EXPORTPATH_STAFF );
			}
			mkdir( EXPORTPATH_STAFF.$out_dir );

			$file_staff_data_name = EXPORTPATH_STAFF.$out_dir."/st_".date("Y-m-d_H-i-s").".xml";
			file_put_contents($file_staff_data_name, $xml);

			$file_zip_name = EXPORTPATH_STAFF.$out_dir."/st_".date("Y-m-d_H-i-s").".zip";
			$zip=new ZipArchive();
			$zip->open($file_zip_name, ZIPARCHIVE::CREATE);

			//Получаем размер xml-файла и сравниваем его с предельно допустимым (в мб)
			$max_filesize = 14;
			$filesize = round(filesize($file_staff_data_name)/1048576,2);
			if($filesize <= $max_filesize){  //Если не превышает допустимый размер, то сразу отправляем в архив:
				$zip->AddFile($file_staff_data_name,"st_".date("Y-m-d_H-i-s").".xml");
				$zip->close();
				unlink($file_staff_data_name);
			}
			else{ //Иначе - разбиваем исходный массив на несколько отдельных, формируем из каждого xml-файл и отправляем все в архив
				unlink($file_staff_data_name);
				$div_count = round($filesize/$max_filesize); //Определим, на сколько частей нужно разбить массив
				$array_size = count($response);//Получаем число строк в исходном массиве
				$div_array_size = round($array_size/$div_count);//Определим, по сколько строк записей должно быть в каждом подмассиве
				$div_array = array_chunk($response,$div_array_size); //Разбиваем массив на несколько
				$staff_fields = array();
				$file_staff_data_name_i = array();

				for ( $i = 0; $i < $div_count; $i++ ){
					reset($div_array[$i]);
					$staff_fields[$i]["STAFF_FIELDS"] = $div_array[$i];
					$xml = "<?xml version=\"1.0\"?>\r\n" . $this->parser->parse('export_xml/exportermpstaff', $staff_fields[$i], true); //Получаем xml
					$file_staff_data_name_i[$i] = EXPORTPATH_STAFF.$out_dir."/st_".date("Y-m-d_H-i-s")."_".($i+1).".xml";
					file_put_contents($file_staff_data_name_i[$i],$xml);
					$zip->AddFile($file_staff_data_name_i[$i],"st_".date("Y-m-d_H-i-s")."_".($i+1).".xml");
				}
				$zip->close(); //Закрываем архив и подчищаем за собой
				for($i = 0; $i < $div_count; $i++){
					unlink($file_staff_data_name_i[$i]);
				}
			}
		}

		if(file_exists($file_zip_name)){
			$filedata = array(
				'link' => $file_zip_name,
				'success' => true
			);
		}
		else{
			$filedata = array(
				'succes' => false
			);
		}
		$this->ReturnData($filedata);
	}

	/**
	 * https://redmine.swan.perm.ru/issues/41129
	 */
	public function getIsNoFRMP()
	{
		$data = $this->ProcessInputData('getIsNoFRMP', false);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->getIsNoFRMP($data);

		if(is_array($response) && count($response) && isset($response[0]['PasportMO_IsNoFRMP']) && $response[0]['PasportMO_IsNoFRMP']=='2'){
			$this->ReturnData(array('success' => false));
		}
		else{
			$this->ReturnData(array('success' => true));
		}
	}
	/**
	 * Это Doc-блок
	 */
	public function GetLpuSectionWard()
	{
		$data = $this->ProcessInputData('GetLpuSectionWard', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->GetLpuSectionWard($data);
		if (count($response) && isset($response[0]['LpuSectionWard_DayCost'])) {
			$response[0]['LpuSectionWard_DayCost'] = sprintf('%.2f', $response[0]['LpuSectionWard_DayCost']);
		}
		$this->ProcessModelList($response, true, true, 'Ошибка при выполнении запроса к базе данных (получение списка палат)')->ReturnData();
		return true;
	}
	/**
	 * Это Doc-блок
	 */
	public function SaveLpuSectionWard()
	{
		$data = $this->ProcessInputData('SaveLpuSectionWard', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->SaveLpuSectionWard($data);
		$this->ProcessModelSave($response, true, 'При сохранении данных произошла ошибка запроса к БД.')->ReturnData();
		return true;
	}
	/**
	 * Это Doc-блок
	 */
	public function getLpuSectionGrid()
	{
		$data = $this->ProcessInputData('getLpuSectionGrid', true);
		if ($data === false) {
			return false;
		}
		if (empty($data['LpuUnit_id']) AND empty($data['LpuSection_pid'])) {
			echo json_encode(array('success' => false, 'Error_Msg' => toUTF('Должен быть указан или идентификатор объединения или идентификатор отделения верхнего уровня')));
			return false;
		}
		$response = $this->dbmodel->getLpuSectionGrid($data);
		$this->ProcessModelList($response, true, true, 'Ошибка при выполнении запроса к базе данных (получение списка палат)')->ReturnData();
		return true;
	}


	/**
	 * Функция чтения справочника профилей, по которым заведены отделения в структуре ЛПУ
	 */
	public function getLpuSectionProfile()
	{
		$data = $this->ProcessInputData('getLpuSectionProfile', true);
		if ($data) {
			$response = $this->dbmodel->getLpuSectionProfile($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Для чтения информации о коечном фонде отделения
	 */
	public function getLpuSectionBedAllQuery()
	{
		$data = $this->ProcessInputData('getLpuSectionBedAllQuery',true);

		if ($data) {
			$response = $this->dbmodel->getLpuSectionBedAllQuery($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Это Doc-блок
	 */
	public function getLpuSectionBedProfileforCombo()
	{
		$data = $this->ProcessInputData('getLpuSectionBedProfileforCombo',true);
		if ($data) {
			$response = $this->dbmodel->getLpuSectionBedProfileforCombo($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Это Doc-блок
	 */
	public function getLpuSectionProfileforCombo()
	{
		$data = $this->ProcessInputData('getLpuSectionProfileforCombo',true);
		if ($data) {
			$response = $this->dbmodel->getLpuSectionProfileforCombo($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Это Doc-блок
	 */
	public function getLpuSectionData()
	{
		$data = $this->ProcessInputData('getLpuSectionData',true);

		if ($data) {
			$response = $this->dbmodel->getLpuSectionData($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Это Doc-блок
	 */
	public function updMaxEmergencyBed()
	{
		$data = $this->ProcessInputData('updMaxEmergencyBed',true);
		if ($data) {
			$response = $this->dbmodel->updMaxEmergencyBed($data);
			if ((isset($response[0]['Error_Msg'])) && (strlen($response[0]['Error_Msg']) > 0))
				echo json_encode(array('success' => false));
			else
				echo json_encode(array('success' => true));
		}
	}


		/**
		 * Экспортирует в DBF указанный в переменной $query2export запрос
		 * 'REG_FOND' - НОВОЕ Регистр для фонда
		 * 'LPU_Q' - LPU_Q.DBF (Данная выгрузка предусматривает - Справочник ЛПУ работающих в системе ОНЛС для аптек)
		 * 'SVF_Q' - SVF_Q.DBF (Данная выгрузка предусматривает -Справочник врачей, имеющих право выписывать льготные рецепты в системе ОНЛС  -для аптек)
		 * 'SVF_Q_2' - SVF_Q_2.DBF (Эта выгрузка дублирует SVF_Q.DBF, но в ней есть поиск врача по его личному коду ЛЛО, для быстрого поиска)
		 *
		 * Архивирует и отдает клиенту ссылку на скачивание
		 *
		 * Параметры:
		 * $query2export: ('REG_FOND'|'LPU_Q'|'SVF_Q'|'SVF_Q_2')
		 * На выходе: Ничего
		 */
	public function staffExport2Dbf()
	{
		/**
		* Это Doc-блок
		*/
		function zipit($ziparchivename, $filename)
		{
			$result = true;
			$zip = new ZipArchive();
			$result = $result && $zip->open($ziparchivename, ZIPARCHIVE::CREATE);
			$result = $result && $zip->AddFile($filename, basename($filename));
			$result = $result && $zip->close();
			return $result;
		}
		/**
		 * Это Doc-блок
		 */
		function create_directories($base_name)
		{
			$result = true;
			$out_dir = "exp" . time();
			$dir_name_path = $base_name . EXPORTPATH_LPU_STAFF_QWERTY_REG_FOND;
			if (!is_dir($dir_name_path)) {
				if (!mkdir($dir_name_path)) {
					log_message('error', "Не удалось создать каталог ($dir_name_path)!");
					$result = false;
				}
			}
			if ($result) {
				$dir_name_path = $base_name . EXPORTPATH_LPU_STAFF_QWERTY_REG_FOND . $out_dir;
				if (!mkdir($dir_name_path)) {
					log_message('error', "Не удалось создать каталог ($dir_name_path)!");
					$result = false;
				}
			}
			if ($result) {
				$result = EXPORTPATH_LPU_STAFF_QWERTY_REG_FOND . $out_dir . '/';
			}
			return $result;
		}
		/**
		 * Это Doc-блок
		 */
		function base_name()
		{
			$result = $_SERVER["DOCUMENT_ROOT"];
			$have_end_slash = ($result[strlen($result) - 1] == "/");
			if (!$have_end_slash) {
				$result = $result . "/";
			}
			return $result;
		}
		/**
		 * Это Doc-блок
		 */
		function dbf_data_definition($query2export)
		{
			$data_def = array(
				'REG_FOND' => array(
					array("TF_OKATO", "N", 5, 0),
					array("C_OGRN", "C", 15),
					array("LCOD", "C", 7),
					array("TYPE", "C", 1),
					array("PCOD", "C", 22),
					array("FAM_V", "C", 50),
					array("IM_V", "C", 30),
					array("OT_V", "C", 30),
					array("W", "C", 1),
					array("DR", "C", 10, 'from_date', 'Y/m/d'),
					array("SS", "C", 14),
					array("PRVD", "N", 4, 0),
					array("D_PR", "D", 8),
					array("D_SER", "D", 8),
					array("PRVS", "N", 9, 0),
					array("KV_KAT", "N", 1, 0),
					array("YEAR_KAT", "N", 4, 0),
					array("DATE_B", "D", 8),
					array("DATE_E", "D", 8),
					array("STAVKA", "N", 4, 2),
					array("MSG_TEXT", "C", 100),
					array("DATE_P", "D", 8),
					array("PRIKREP", "C", 4),
					array("VEDOM_P", "C", 30)
				),
				'REG_FOND_NEW' => array(
					array("TF_OKATO", "N", 5, 0),
					array("C_OGRN", "C", 15),
					array("MCOD", "C", 7),
					array("TYPE", "C", 1),
					array("PCOD", "C", 22),
					array("FAM_V", "C", 50),
					array("IM_V", "C", 30),
					array("OT_V", "C", 30),
					array("W", "C", 1),
					array("DR", "C", 10, 'from_date', 'Y/m/d'),
					array("SS", "C", 14),
					array("PRVD", "N", 6, 0),
					array("D_PR", "D", 8),
					array("D_SER", "D", 8),
					array("PRVS", "N", 9, 0),
					array("KV_KAT", "N", 1, 0),
					array("YEAR_KAT", "N", 4, 0),
					array("DATE_B", "D", 8),
					array("DATE_E", "D", 8),
					array("MSG_TEXT", "C", 100),
					array("DATE_P", "D", 8),
					array("PRIKREP", "C", 4),
					array("VEDOM_P", "C", 30)
				),
				'LPU_Q' => array(
					array('LPU_OUZ', 'C', 7),
					array('MCOD', 'C', 7),
					array('TF_OKATO', 'N', 5, 0),
					array('C_OGRN', 'C', 15),
					array('M_NAMES', 'C', 50),
					array('M_NAMEF', 'C', 150),
					array('POST_ID', 'N', 6, 0),
					array('ADRES', 'C', 200),
					array('FAM_GV', 'C', 40),
					array('IM_GV', 'C', 40),
					array('OT_GV', 'C', 40),
					array('FAM_BUX', 'C', 40),
					array('IM_BUX', 'C', 40),
					array('OT_BUX', 'C', 40),
					array('TEL', 'C', 40),
					array('FAX', 'C', 40),
					array('E_MAIL', 'C', 30),
					array('DATE_B', 'D', 8),
					array('DATE_E', 'D', 8),
					array('KOD_TER', 'C', 3),
					array('KOD_LPU', 'C', 3),
					array('S_LR_LPU', 'C', 10)
				),
				'SVF_Q' => array(
					array("TF_OKATO", "N", 5, 0),
					array("MCOD", "C", 7),
					array("PCOD", "C", 22),
					array("FAM_V", "C", 30),
					array("IM_V", "C", 20),
					array("OT_V", "C", 20),
					array("C_OGRN", "C", 15),
					array("PRVD", "N", 4, 0),
					array("D_JOB", "C", 50),
					array("D_PRIK", "D", 8),
					array("D_SER", "D", 8),
					array("PRVS", "C", 9),
					array("KV_KAT", "N", 1, 0),
					array("DATE_B", "D", 8),
					array("DATE_E", "D", 8),
					array("MSG_TEXT", "C", 100),
					array("KOD_TER", "C", 3),
					array("KOD_LPU", "C", 3)
				),
				'SVF_Q_2' => array(
					array("TF_OKATO", "N", 5, 0),
					array("SCOD", "C", 6),
					array("MCOD", "C", 7),
					array("PCOD", "C", 22),
					array("FAM_V", "C", 30),
					array("IM_V", "C", 20),
					array("OT_V", "C", 20),
					array("C_OGRN", "C", 15),
					array("PRVD", "N", 4, 0),
					array("D_JOB", "C", 50),
					array("D_PRIK", "D", 8),
					array("D_SER", "D", 8),
					array("PRVS", "C", 9),
					array("KV_KAT", "N", 1, 0),
					array("DATE_B", "D", 8),
					array("DATE_E", "D", 8),
					array("MSG_TEXT", "C", 100),
					array("KOD_TER", "C", 3),
					array("KOD_LPU", "C", 3)
				)
			);
			$result = $data_def[$query2export];
			return $result;
		}

		try
		{
			if (!isSuperadmin()) {
				throw new Exception('Access denied');
			}
			if (!isset($query2export)) {
				if (isset($_POST['query2export'])) {
					$query2export = $_POST['query2export'];
				} else {
					throw new Exception('query2export param missing');
				}
			}
			set_time_limit(0);
			$DBF = '.dbf';
			$ZIP = '.zip';
			$base_name = base_name();
			$out_dir = create_directories($base_name);
			if (!$out_dir) {
				throw new Exception('Ошибка создания каталогов');
			}
			$dbf_filename = mb_strtolower($query2export);
			$data_def = dbf_data_definition($query2export);
			$data4dbf = $this->dbmodel->getExp2DbfData($query2export);
			if (!$data4dbf) {
				throw new Exception("getExp2DbfData() failed");
			}

			$data4dbf = toAnsiR($data4dbf, true);

			if (strcmp($dbf_filename, 'reg_fond_new') == 0) {
				$dbf_filename = 'reg_fond';
			}

			$dbf_full_name = $base_name . $out_dir . $dbf_filename . $DBF;
			$h = dbase_create($dbf_full_name, $data_def);
			if (!$h) {
				throw new Exception('dbase_create() fails ' . $dbf_full_name);
			}
			$add_ok = true;
			$cnt = 0;
			//наполняю DBF из результатов модели
			foreach ($data4dbf as $record) {
				foreach ($data_def as $col) {
					$col_type = $col[1];
					switch ($col_type) {
						case 'D':
							if (!empty($record[$col[0]])) {
								if ($record[$col[0]] instanceOf DateTime) {
									$record[$col[0]] = $record[$col[0]]->format('Ymd');
								} else {
									try {
										$record[$col[0]] = new DateTime($record[$col[0]]);
									} catch (Exception $e) {
										throw new Exception('Неверная дата в записи (' . implode(', ', $record) . ')');
									}
									$record[$col[0]] = $record[$col[0]]->format('Ymd');
								}
							}
							break;
						case 'C':
							if (isset($col[3]) && ($col[3]=='from_date')){
								if ($record[$col[0]] instanceOf DateTime) {
									$record[$col[0]] = $record[$col[0]]->format($col[4]);
								}
							} else {
								if (!empty($record[$col[0]])) {
									ConvertFromWin1251ToCp866($record[$col[0]]);
								}
							}
							break;
					}
				}
				$add_ok = $add_ok && dbase_add_record($h, array_values($record));
				if ($add_ok) {
					$cnt++;
				} else {
					throw new Exception('Ошибка добавления записи в DBF (' . implode(', ', $record) . ')');
				}
			}
			log_message('debug', 'Записей добавлено в DBF: ' . $cnt);
			if (!dbase_close($h)) {
				throw new Exception('Не удалось сохранить изменения в ' . $dbf_full_name);
			}

			//архивирую результат
			$zip_full_name = $base_name . $out_dir . $dbf_filename . $ZIP;
			if (!zipit($zip_full_name, $dbf_full_name)) {
				throw new Exception('Ошибка аривации ' . $dbf_full_name . '->' . $zip_full_name);
			}

			//Удаляю исходный файл
			if (!@unlink($dbf_full_name)) {
				log_message('debug', 'Не удалось удалить исходный файл после архивации ' . $dbf_full_name);
			}

			//формирую ссылку на архив
			$link = $out_dir . $dbf_filename . $ZIP;
			log_message('debug', "[" . date('Y-m-d H:i:s') . "] Возвращаем ссылку на файл {$link}");
			//отдаю клиенту
			echo "{'success':true,'Link':'$link'}";
			$data['Status'] = $out_dir . $dbf_filename . $ZIP;
		}
		catch (Exception $e)
		{
			log_message('error', $e->getMessage());
			echo "{'success':false}";
		}
	}
	/**
	 * Это Doc-блок
	 */
	public function checkLpuFRMP2Dbf()
	{
		if( !isset($_FILES['file']) ) {
			$this->ReturnData(array('success' => false));
			return false;
		}
		
		if( !is_file($_FILES['file']['tmp_name']) ) {
			$this->ReturnData(array('success' => false, 'Error_Msg' => 'Не удалось найти файл!'));
			return false;
		}
		
		$file = IMPORTPATH_ROOT.$_FILES['file']['name'];
		
		if( @rename($_FILES['file']['tmp_name'], $file) ) {
			if( preg_match('/csv/iu', $file) ) {
				$type = 'csv';
			} else if( preg_match('/xml/iu', $file) ) {
				$type = 'xml';
			}
		}
		if( !isset($type) ) {
			$this->ReturnData(array('success' => false, 'Error_Msg' => 'Данный тип файла не поддерживается!'));
			return false;
		}
		$data = array();
		switch($type) {
			case 'csv':
				if(($h = fopen($file, 'r')) !== false) {
					while(($data[] = fgetcsv($h, 1000, ";")) !== false) {
						//
					}
					fclose($h);
				}
				$title = array(
					'ЛПУ', 'ИНН', 'КПП', 'ОГРН', 'Тип учреждения', 'Уровень', 'Муниципалитет', 'Тип организации', 'Введено сотрудников',
					'Из них, работающих на данный момент', 'Из них, имеющих личное дело', 'Всего ставок по личным делам работающих сотрудников',
					'Введено записей штатного расписания', 'Всего ставок по штатному расписанию'
				);
				$keyTitle = array_search($title, $data); // это номер строки с заголовками столбцов
				array_splice($data, 0, $keyTitle+1); // TO-DO
				break;
			case 'xml':
				//
				break;
		}
		unlink($file);
		/**
		 * Это Doc-блок
		 */
		function zipit($ziparchivename, $filename)
		{
			$result = true;
			$zip = new ZipArchive();
			$result = $result && $zip->open($ziparchivename, ZIPARCHIVE::CREATE);
			$result = $result && $zip->AddFile($filename, basename($filename));
			$result = $result && $zip->close();
			return $result;
		}
		/**
		 * Это Doc-блок
		 */
		function create_directories($base_name)
		{
			$result = true;
			$out_dir = "exp" . time();
			$dir_name_path = $base_name . EXPORTPATH_LPU_STAFF_QWERTY_REG_FOND;
			if (!is_dir($dir_name_path)) {
				if (!mkdir($dir_name_path)) {
					log_message('error', "Не удалось создать каталог ($dir_name_path)!");
					$result = false;
				}
			}
			if ($result) {
				$dir_name_path = $base_name . EXPORTPATH_LPU_STAFF_QWERTY_REG_FOND . $out_dir;
				if (!mkdir($dir_name_path)) {
					log_message('error', "Не удалось создать каталог ($dir_name_path)!");
					$result = false;
				}
			}
			if ($result) {
				$result = EXPORTPATH_LPU_STAFF_QWERTY_REG_FOND . $out_dir . '/';
			}
			return $result;
		}
		/**
		 * Это Doc-блок
		 */
		function base_name()
		{
			$result = $_SERVER["DOCUMENT_ROOT"];
			$have_end_slash = ($result[strlen($result) - 1] == "/");
			if (!$have_end_slash) {
				$result = $result . "/";
			}
			return $result;
		}
		
		$data_def = array(
			//array('LPU_OUZ', 'C', 7),
			array('M_NAMEF', 'C', 80),
			array('M_NAMES', 'C', 50),			
			array('LPU_INN', 'C', 15),
			array('LPU_KPP', 'C', 15),
			array('C_OGRN', 'C', 20),
			array('B_DATE', 'C', 12)
		);
		
		try {
			set_time_limit(0);
			$DBF = '.dbf';
			$ZIP = '.zip';
			$base_name = base_name();
			$out_dir = create_directories($base_name);
			if (!$out_dir) {
				throw new Exception('Ошибка создания каталогов');
			}
			$dbf_filename = 'LpuNotFRMP';
			$data4dbf = $this->dbmodel->getAllLpuNotFRMP($data);
			//print_r($data4dbf); exit();
			
			$dbf_full_name = $base_name . $out_dir . $dbf_filename . $DBF;
			$h = dbase_create($dbf_full_name, $data_def);
			if (!$h) {
				throw new Exception('dbase_create() fails ' . $dbf_full_name);
			}
			$add_ok = true;
			$cnt = 0;
			
			foreach($data4dbf as $record) {
				foreach ($data_def as $col) {
					$col_type = $col[1];
					switch ($col_type) {
						case 'D':
							if (!empty($record[$col[0]])) {
								if ($record[$col[0]] instanceOf DateTime) {
									$record[$col[0]] = $record[$col[0]]->format('Ymd');
								} else {
									throw new Exception('Неверная дата в записи (' . implode(', ', $record) . ')');
								}
							}
							break;
						case 'C':
							if (isset($col[3]) && ($col[3]=='from_date')){
								if ($record[$col[0]] instanceOf DateTime) {
									$record[$col[0]] = $record[$col[0]]->format($col[4]);
								} else {
									throw new Exception('Неверная дата в записи (' . implode(', ', $record) . ')');
								}
							} else {
								if (!empty($record[$col[0]])) {
									ConvertFromWin1251ToCp866($record[$col[0]]);
								}
							}
							break;
					}
				}
				$add_ok = $add_ok && dbase_add_record($h, array_values($record));
				if ($add_ok) {
					$cnt++;
				} else {
					throw new Exception('Ошибка добавления записи в DBF (' . implode(', ', $record) . ')');
				}
			}
			
			log_message('debug', 'Записей добавлено в DBF: ' . $cnt);
			if (!dbase_close($h)) {
				throw new Exception('Не удалось сохранить изменения в ' . $dbf_full_name);
			}

			//архивирую результат
			$zip_full_name = $base_name . $out_dir . $dbf_filename . $ZIP;
			if (!zipit($zip_full_name, $dbf_full_name)) {
				throw new Exception('Ошибка аривации ' . $dbf_full_name . '->' . $zip_full_name);
			}

			//Удаляю исходный файл
			if (!@unlink($dbf_full_name)) {
				log_message('debug', 'Не удалось удалить исходный файл после архивации ' . $dbf_full_name);
			}

			//формирую ссылку на архив
			$link = $out_dir . $dbf_filename . $ZIP;
			log_message('debug', "[" . date('Y-m-d H:i:s') . "] Возвращаем ссылку на файл {$link}");
			//отдаю клиенту
			echo "{'success':true,'Link':'$link'}";
			$data['Status'] = $out_dir . $dbf_filename . $ZIP;
			
		} catch (Exception $e) {
			log_message('error', $e->getMessage());
			$this->ReturnData(array('success' => false));
		}	
	}
	
	/**
	 * Получение комментария отделения
	 */
	public function getLpuSectionComment()
	{
		$data = $this->ProcessInputData('getLpuSectionComment', true, true);
		if ($data) {
			$response = $this->dbmodel->getLpuSectionComment($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	} //end getMedStaffFactComment()
	
	/**
	 * Сохранение комментария отделения
	 */
	public function saveLpuSectionComment()
	{
		$data = $this->ProcessInputData('saveLpuSectionComment', true, true);
		if ($data) {
			$response = $this->dbmodel->saveLpuSectionComment($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	} //end saveMedStaffFactComment()
	
	/**
	 * Получение списка средних длительностей лечения для отделения
	 */
	public function loadSectionAverageDurationGrid()
	{
		$data = $this->ProcessInputData('loadSectionAverageDurationGrid', true, true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadSectionAverageDurationGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение типа здания
	 */
	public function loadLpuBuildingType()
	{
		$data = $this->ProcessInputData('loadLpuBuildingType', true, true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadLpuBuildingType($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Сохранение средней длительности лечения для отделения
	 */
	public function saveSectionAverageDuration()
	{
		$data = $this->ProcessInputData('saveSectionAverageDuration', true, true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->saveSectionAverageDuration($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}


	/**
	 *  Функция получения объекта комфортности
	 */
	public function loadLpuSectionWardComfortLink()
	{
		$data = $this->ProcessInputData('loadLpuSectionWardComfortLink', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadLpuSectionWardComfortLink($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *  Функция удаления объекта комфортности
	 */
	public function deleteSectionWardComfortLink()
	{
		$data = $this->ProcessInputData('deleteSectionWardComfortLink', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteSectionWardComfortLink($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *  Функция удаления операции над койкой
	 */
	public function deleteSectionBedStateOper()
	{
		$data = $this->ProcessInputData('deleteSectionBedStateOper', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteSectionBedStateOper($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Сохранение получения объекта комфортности
	 */
	public function saveLpuSectionWardComfortLink()
	{
		$data = $this->ProcessInputData('saveLpuSectionWardComfortLink', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveLpuSectionWardComfortLink($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}

	/**
	 *  Функция получения объекта комфортности
	 */
	public function loadDBedOperation()
	{
		$data = $this->ProcessInputData('loadDBedOperation', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadDBedOperation($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Сохранение получения объекта комфортности
	 */
	public function saveDBedOperation()
	{
		$data = $this->ProcessInputData('saveDBedOperation', true);
		if ($data === false) { return false; }

		if (!isset($data['LpuSectionBedState_id'])) {
			$val = array('success' => false, 'Error_Msg' => 'Для того что бы создать операции над койкой необходимо создать профиль койки.');
			array_walk($val, 'ConvertFromWin1251ToUTF8');
			$this->ReturnData($val);
			return false;
		}

		$response = $this->dbmodel->saveDBedOperation($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;

	}

	/**
	 *  Функция получения объекта комфортности
	 */
	public function getStaffOSMGridDetail()
	{
		$data = $this->ProcessInputData('getStaffOSMGridDetail', true);
		if ($data === false) { return false; }

		// var_dump($data['Lpu_id']);
		$response = $this->dbmodel->getStaffOSMGridDetail($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Сохранение получения объекта комфортности
	 */
	public function saveStaffOSMGridDetail()
	{
		$data = $this->ProcessInputData('saveStaffOSMGridDetail', true);
		if ($data === false) { return false; }
		// print_r($data['Lpu_id']);
		$response = $this->dbmodel->saveStaffOSMGridDetail($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;

	}

	/**
	 * Проверка может ли выполнять отделение ВМП
	 */
	public function checkLpuSectionIsVMP()
	{
		$data = $this->ProcessInputData('checkLpuSectionIsVMP', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkLpuSectionIsVMP($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}

	/**
	 * Получение списка доступных типов служб в зависимости от уровеня структурного элемента МО
	 */		
	public function getAllowedMedServiceTypes() {
		$data = $this->ProcessInputData('getAllowedMedServiceTypes',true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->getAllowedMedServiceTypes($data);
		$this->ProcessModelList($response,true,true)->ReturnData();

		return true;
	}

	/**
	 * Получение списка структурных элементов МО
	 */
	public function getLpuStructureElementList() {
		$data = $this->ProcessInputData('getLpuStructureElementList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getLpuStructureElementList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * Получение списка ЛПУ, обладающих службами ФД, не обслуживаемых ни одним консультационным центром
	 */
	public function getLpuWithUnservedDiagMedService() {
		$data = $this->ProcessInputData('getLpuWithUnservedDiagMedService', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getLpuWithUnservedDiagMedService($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * Получение списка отделений выбранного ЛПУ, в которых есть службы ФД, не обслуживающиеся ни одним консультационным центром
	 */
	public function getUnservedDiagMedService(){
		$data = $this->ProcessInputData('getUnservedDiagMedService', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getUnservedDiagMedService($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * Привязка службы ФД к службе центра удаленной консультации
	 */
	public function saveLinkFDServiceToRCCService(){
		$data = $this->ProcessInputData('saveLinkFDServiceToRCCService', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveLinkFDServiceToRCCService($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}
	
	/**
	 * Удаление связи между службой ФД и службой центра удаленной консультации
	 */
	public function deleteLinkFDServiceToRCCService(){
		$data = $this->ProcessInputData('deleteLinkFDServiceToRCCService', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteLinkFDServiceToRCCService($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}
	
	/**
	 * Получение списка служб ФД привязанных к службе ЦУК
	 */
	public function getFDServicesConnectedToRCCService(){
		$data = $this->ProcessInputData('getFDServicesConnectedToRCCService', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getFDServicesConnectedToRCCService($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Получение спсика ЛПУ по адресу
	 */
	public function getLpuListByAddress(){
		$data = $this->ProcessInputData('getLpuListByAddress', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getLpuListByAddress($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Получение адреса для структурного уровня лпу
	 */
	public function getAddressByLpuStructure(){
		$data = $this->ProcessInputData('getAddressByLpuStructure', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getAddressByLpuStructure($data);
		$this->ProcessModelSave($response)->ReturnData();
	}

	/**
	 * Получение списка дополнительных профилей отделения
	 */
	public function loadLpuSectionLpuSectionProfileGrid(){
		$data = $this->ProcessInputData('loadLpuSectionLpuSectionProfileGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadLpuSectionLpuSectionProfileGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Получение списка мед. оборудования
	 */
	public function loadLpuSectionMedProductTypeLinkGrid(){
		$data = $this->ProcessInputData('loadLpuSectionMedProductTypeLinkGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadLpuSectionMedProductTypeLinkGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Получение списка профилей отделений
	 */
	public function loadLpuSectionProfileList(){
		$data = $this->ProcessInputData('loadLpuSectionProfileList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadLpuSectionProfileList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
	
	/**
	 * Получение списка кодов отделений
	 * @task https://redmine.swan.perm.ru/issues/51349
	 */
	public function loadLpuSectionCodeList(){
		$data = $this->ProcessInputData('loadLpuSectionCodeList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadLpuSectionCodeList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
	
	/**
	 * Получение списка типов подстанций СМП
	 */
	public function getSmpUnitTypes() {
		$data = $this->ProcessInputData('getSmpUnitTypes', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getSmpUnitTypes($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	/**
	 * Получение подстанций СМП для филиалов
	 */
	public function getLpuBuildingsForFilials() {
		$data = $this->ProcessInputData('getLpuBuildingsForFilials', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getLpuBuildingsForFilials($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	
	/**
	 * Сохранение параметров подстанции
	 */
	public function saveSmpUnitParams(){
		$data = $this->ProcessInputData('saveSmpUnitParams', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->saveSmpUnitParams($data);
		return $this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	 * Сохранение параметров подстанции
	 */
	public function saveSmpUnitTimes(){
		$data = $this->ProcessInputData('saveSmpUnitTimes', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->saveSmpUnitTimes($data);
		return $this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	 * Получение информации о подстанции СМП
	 */
	public function getSmpUnitData(){
		$data = $this->ProcessInputData('getSmpUnitData', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->getSmpUnitData($data);
		return $this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Получение информации о таймерах подстанции СМП
	 */
	public function getLpuBuildingData(){
		$data = $this->ProcessInputData('getLpuBuildingData', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->getLpuBuildingData($data);
		return $this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Формирование строки грида обслуживаемых отделений
	 */
	public function getRowLpuSectionService() {
		$data = $this->ProcessInputData('getRowLpuSectionService', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getRowLpuSectionService($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Возвращает список обслуживаемых отделений
	 */
	public function loadLpuSectionServiceGrid() {
		$data = $this->ProcessInputData('loadLpuSectionServiceGrid', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadLpuSectionServiceGrid($data);

		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Возвращает количество обслуживаемых отделений
	 */
	public function getLpuSectionServiceCount() {
		$data = $this->ProcessInputData('getLpuSectionServiceCount', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getLpuSectionServiceCount($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Получение количества групп отделений по типу группы в ЛПУ
	 */
	public function getLpuUnitCountByType() {
		$data = $this->ProcessInputData('getLpuUnitCountByType', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getLpuUnitCountByType($data);

		$this->ProcessModelSave($response, true, 'Ошибка при получении данных')->ReturnData();
		return true;
	}

	/**
	 * Функция получения списка отделений со службами определённого типа
	 * @return boolean
	 */
	public function getLpuWithMedServiceList() {
		$data = $this->ProcessInputData('getLpuWithMedServiceList', false);
		if ($data === false) { return false; }
		$response = $this->dbmodel->getLpuWithMedServiceList($data);
		$this->ProcessModelList($response)->ReturnData();
		return true;
	}
	/**
	 * Функция сохранения обслуживающих отделений для службы судебно-медицинской экспертизы трупов
	 * @return boolean
	 */	
	public function saveForenCorpServingMedServices() {
		$data = $this->ProcessInputData('saveForenCorpServingMedServices', false);
		if ($data === false) { return false; }
		$response = $this->dbmodel->saveForenCorpServingMedServices($data);
		$this->ProcessModelList($response)->ReturnData();
		return true;
	}
	/**
	 * Функция получения обслуживающих отделений для службы судебно-медицинской экспертизы трупов
	 * @return boolean
	 */
	public function loadForenCorpServingMedServices() {
		$data = $this->ProcessInputData('loadForenCorpServingMedServices', false);
		if ($data === false) { return false; }
		$response = $this->dbmodel->loadForenCorpServingMedServices($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}
	/**
	 * Функция сохранения обслуживающих отделений для медико-криминалистической / судебно-гистологической службы
	 * @return boolean
	 */	
	public function saveForenHistServingMedServices() {
		$data = $this->ProcessInputData('saveForenHistServingMedServices', false);
		if ($data === false) { return false; }
		$response = $this->dbmodel->saveForenHistServingMedServices($data);
		$this->ProcessModelList($response)->ReturnData();
		return true;
	}
	/**
	 * Функция получения обслуживающих отделений для медико-криминалистической / судебно-гистологической службы
	 * @return boolean
	 */
	public function loadForenHistServingMedServices() {
		$data = $this->ProcessInputData('loadForenHistServingMedServices', false);
		if ($data === false) { return false; }
		$response = $this->dbmodel->loadForenHistServingMedServices($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}
	
	/**
	 * Метод загрузки фотографии подразделения
	 * формирует два файла по пути вида вида: 
	 * uploads/orgs/photos/[lpu_id]/LpuSection/[LpuSection_id].(jpg|png|gif)
	 * uploads/orgs/photos/[lpu_id]/LpuSection/thumbs/[LpuSection_id].(jpg|png|gif)
	 */
	public function uploadOrgPhoto() {
		$data = $this->ProcessInputData('uploadOrgPhoto', false);
		$response = $this->dbmodel->uploadOrgPhoto($data, $_FILES);
		if (is_array($response)) {
			$this->ReturnData($response);
		} else {
			DieWithError('Не удалось загрузить файл!');
			return false;
		}
	}

	/**
	 * Получение информации об участке для прикрепления
	 */
	public function loadLpuRegionInfo(){
		$data = $this->ProcessInputData('loadLpuRegionInfo', false);
		if($data === false)
			return false;
		$response = $this->dbmodel->loadLpuRegionInfo($data);
		//var_dump($response);die;
		$this->ProcessModelList($response)->ReturnData();
		return true;
	}

	/**
	 * Сохранение доп параметров подстанции
	 */
	function saveLpuBuildingAdditionalParams(){
		$data = $this->ProcessInputData('saveLpuBuildingAdditionalParams', true);
		if($data === false)
			return false;
		$response = $this->dbmodel->saveLpuBuildingAdditionalParams($data);
		$this->ProcessModelSave($response)->ReturnData();
	}

	/**
	 * Получение параметров службы НМП
	 */
	function getNmpParams() {
		$data = $this->ProcessInputData('getNmpParams');
		if($data === false) return false;

		$response = $this->dbmodel->getNmpParams($data);
		$this->ProcessModelList($response)->ReturnData();
	}

	/**
	 * Сохранение параметров службы НМП
	 */
	function saveNmpParams() {
		$data = $this->ProcessInputData('saveNmpParams', true);
		if($data === false) return false;

		$response = $this->dbmodel->saveNmpParams($data);
		$this->ProcessModelSave($response)->ReturnData();
	}

	/**
	 * Получение списка МО по региону
	 */
	function getLpuListByRegion(){
		$data = getSessionParams();

		if(!empty($data['session']['region']['number'])){
			$data['Region_id'] = $data['session']['region']['number'];
			$response = $this->dbmodel->getLpuListByRegion($data);
			$this->ProcessModelList($response)->ReturnData();
		}

		return false;
	}

	/**
	 * Получение списка связей МО с бюро МСЭ
	 */
	function loadLpuMseLinkGrid() {
		$data = $this->ProcessInputData('loadLpuMseLinkGrid', true);
		if($data === false) return false;

		$response = $this->dbmodel->loadLpuMseLinkGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	 * Получение данных для редактирования связи МО с бюро МСЭ
	 */
	function loadLpuMseLinkForm() {
		$data = $this->ProcessInputData('loadLpuMseLinkForm', true);
		if($data === false) return false;

		$response = $this->dbmodel->loadLpuMseLinkForm($data);
		$this->ProcessModelList($response)->ReturnData();
	}

	/**
	 * Сохранение связи МО с бюро МСЭ
	 */
	function saveLpuMseLink() {
		$data = $this->ProcessInputData('saveLpuMseLink', true);
		if($data === false) return false;

		$response = $this->dbmodel->saveLpuMseLink($data);
		$this->ProcessModelSave($response)->ReturnData();
	}

	/**
	 * Удаление связи МО с бюро МСЭ
	 */
	function deleteLpuMseLink() {
		$data = $this->ProcessInputData('deleteLpuMseLink', true);
		if($data === false) return false;

		$response = $this->dbmodel->deleteLpuMseLink($data);
		$this->ProcessModelSave($response)->ReturnData();
	}

	/**
	 * Получение списка профилей ФРМО
	 */
	public function getLpuUnitProfile() {
		$data = $this->ProcessInputData('getLpuUnitProfile', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getLpuUnitProfile($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка типов ФРМП (Форма 30)
	 */
	public function getFRMPSubdivisionType() {
		$data = $this->ProcessInputData('getFRMPSubdivisionType', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getFRMPSubdivisionType($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	
	/**
	 * Получение МО обслуживания адреса (МО обслуживания активного вызова)
	 */
	function getLpuAddress(){
		$data = $this->ProcessInputData('getLpuAddress', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->getLpuAddress($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	
	/**
	 * Получить номер телефона из настроек группы отделений 
	 */
	function getLpuPhoneMO(){
		$data = $this->ProcessInputData('getLpuPhoneMO', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->getLpuPhoneMO($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	
	/**
	 *  Функция получения штатного расписания
	 */
	public function getLpuStaffGridDetail(){
		$data = $this->ProcessInputData('getLpuStaffGridDetail', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getLpuStaffGridDetail($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * Сохранение штатного расписания
	 */
	public function saveLpuStaffGridDetail(){
		$data = $this->ProcessInputData('saveLpuStaffGridDetail', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->saveLpuStaffGridDetail($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;

	}

	/**
	 * @return bool
	 */
	public function hasMedStaffFactInAIDSCenter() {
		$data = $this->ProcessInputData('hasMedStaffFactInAIDSCenter', false);
		if ($data === false) return false;
		$response = null;

		$flag = $this->dbmodel->hasMedStaffFactInAIDSCenter($data);
		if ($flag !== null) {
			$response = array('success' => true, 'flag' => $flag);
		}

		$this->ProcessModelSave($response, true, 'Ошибка при получении данных')->ReturnData();

		return true;
	}

	/**
	 * Загрузка списка МО для формы "Выбор МО для управления"
	 */
	public function getLpuListWithSmp(){

		$response = $this->dbmodel->getLpuListWithSmp();
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Загрузка списка Функциональных подразделений по СУР
	 */
	public function getFpList(){
		$data = $this->ProcessInputData('getFpList', false);
		if ($data === false) return false;

		$response = $this->dbmodel->getFpList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
}
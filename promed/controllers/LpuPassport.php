<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * LpuPassport - контроллер для выполнения операций с паспортом МО
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Марков Андрей
 * @version      май 2010
 *
 * @property LpuPassport_model dbmodel
 */

class LpuPassport extends swController
{

	/**
	 * Описание правил для входящих параметров
	 * @var array
	 */
    var $inputRules = array(
			'printLpuPassportER' => array(
				array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id'),
			),
			'loadLpuPeriodOMSGrid' => array(
				array(
					'field' => 'LpuPeriodOMS_id',
					'label' => 'Идентификатор ',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadLpuDispContractCombo' => array(
				array(
					'field' => 'LpuSectionProfile_id',
					'label' => 'Профиль',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuDispContract_id',
					'label' => 'Договор',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'onDate',
					'label' => 'Дата',
					'rules' => '',
					'type' => 'date'
				)
			),
			'loadLpuOMSGrid' => array(
				array(
					'field' => 'LpuPeriodOMS_pid',
					'label' => 'Идентификатор ',
					'rules' => '',
					'type' => 'int'
				)
			),
			'getLpuPassport' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadLpuPeriodOMS' => array(
				array(
					'field' => 'LpuPeriodOMS_id',
					'label' => 'Идентификатор ',
					'rules' => 'trim|required',
					'type' => 'int'
				)
			),
			'hasLpuPeriodOMS' => array(
				array(
					'field' => 'Org_oid',
					'label' => 'Идентификатор организации (МО)',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Date',
					'label' => 'Дата',
					'rules' => '',
					'type' => 'date'
				)
			),
			'loadLpuOMS' => array(
				array(
					'field' => 'LpuPeriodOMS_id',
					'label' => 'Идентификатор ',
					'rules' => 'trim|required',
					'type' => 'int'
				)
			),
			'loadLpuPeriodDLOGrid' => array(
				array(
					'field' => 'LpuPeriodDLO_id',
					'label' => 'Идентификатор ',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadLpuPeriodDLO' => array(
				array(
					'field' => 'LpuPeriodDLO_id',
					'label' => 'Идентификатор ',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadOrgWorkPeriodGrid' => array(
				array(
					'field' => 'Org_id',
					'label' => 'Идентификатор Организации',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadOrgWorkPeriod' => array(
				array(
					'field' => 'OrgWorkPeriod_id',
					'label' => 'Идентификатор ',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadLpuPeriodDMSGrid' => array(
				array(
					'field' => 'LpuPeriodDMS_id',
					'label' => 'Идентификатор ',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadLpuPeriodDMS' => array(
				array(
					'field' => 'LpuPeriodDMS_id',
					'label' => 'Идентификатор ',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadLpuPeriodFondHolderGrid' => array(
				array(
					'field' => 'LpuPeriodFondHolder_id',
					'label' => 'Идентификатор ',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'HolderDate',
					'label'	=> 'Дата',
					'rules'	=> '',
					'type'	=> 'string'
				)
			),
			'loadLpuPeriodFondHolder' => array(
				array(
					'field' => 'LpuPeriodFondHolder_id',
					'label' => 'Идентификатор ',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadLpuBuilding' => array(
				array(
					'field' => 'LpuBuildingPass_id',
					'label' => 'Идентификатор ',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'int'
				)
			),
			'LpuBuildingPassList' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadMedProductClassForm' => array(
				array(
					'field' => 'MedProductClassForm_pid',
					'label' => 'идентификатор родителя',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadMOSections' => array(
				array(
					'field' => 'LpuBuildingPass_id',
					'label' => 'Идентификатор ',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'int'
				)
			),
			'getMOSectionsForList' => array(
				array(
					'field' => 'type',
					'label' => 'Тип ветки',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'unitType',
					'label' => 'Идентификатор типа подгруппы отделений',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'deniedSectionsList',
					'label' => 'Отделения МО которые ненадо добавлять',
					'rules' => '',
					'type' => 'string'
				)
			),
			'loadLpuLicenceGrid' => array(
				array(
					'field' => 'LpuLicence_id',
					'label' => 'Идентификатор ',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadLpuLicence' => array(
				array(
					'field' => 'LpuLicence_id',
					'label' => 'Идентификатор ',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadLpuMobileTeamGrid' => array(
				array(
					'field' => 'LpuMobileTeam_id',
					'label' => 'Идентификатор ',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadLpuMobileTeam' => array(
				array(
					'field' => 'LpuMobileTeam_id',
					'label' => 'Идентификатор ',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadSmpTariffGrid' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'isClose',
					'label' => 'Флаг закрытия',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadTariffDispGrid' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'isClose',
					'label' => 'Флаг закрытия',
					'rules' => '',
					'type' => 'int'
				)
			),
			'deleteMedicalCareBudgTypeTariff' => array(
				array(
					'field' => 'id',
					'label' => 'Идентификатор тарифа',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'saveMedicalCareBudgTypeTariff' => array(
				array(
					'field' => 'MedicalCareBudgTypeTariff_id',
					'label' => 'Идентификатор тарифа',
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
					'field' => 'MedicalCareBudgType_id',
					'label' => 'Тип мед. помощи',
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
					'field' => 'QuoteUnitType_id',
					'label' => 'Единица измерения',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'MedicalCareBudgTypeTariff_Value',
					'label' => 'Значение',
					'rules' => 'required',
					'type' => 'float'
				),
				array(
					'field' => 'MedicalCareBudgTypeTariff_begDT',
					'label' => 'Дата начала',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'MedicalCareBudgTypeTariff_endDT',
					'label' => 'Дата окончания',
					'rules' => 'required',
					'type' => 'date'
				)
			),
			'loadMedicalCareBudgTypeTariffEditWindow' => array(
				array(
					'field' => 'MedicalCareBudgTypeTariff_id',
					'label' => 'Идентификатор тарифа',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadMedicalCareBudgTypeTariffGrid' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'isClose',
					'label' => 'Флаг закрытия',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'MedicalCareBudgType_id',
					'label' => 'Тип мед. помощи',
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
					'field' => 'QuoteUnitType_id',
					'label' => 'Единица измерения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'addWithoutLpu',
					'label' => 'Флаг показа запсией без МО, при фильтре по МО',
					'rules' => '',
					'type' => 'int'
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
			'loadTariffLpuGrid' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'isClose',
					'label' => 'Флаг закрытия',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadTariffDisp' => array(
				array(
					'field' => 'TariffDisp_id',
					'label' => 'Идентификатор ',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadSmpTariff' => array(
				array(
					'field' => 'CmpProfileTariff_id',
					'label' => 'Идентификатор ',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadTariffLpu' => array(
				array(
					'field' => 'LpuTariff_id',
					'label' => 'Идентификатор ',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadLpuEquipment' => array(
				array(
					'field' => 'LpuEquipment_id',
					'label' => 'Идентификатор ',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuEquipmentPacs_id',
					'label' => 'Идентификатор PACS',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadMedProductCard' => array(
				array(
					'field' => 'MedProductCard_id',
					'label' => 'Идентификатор ',
					'rules' => '',
					'type' => 'int'
				),
                array(
                    'field' => 'Lpu_id',
                    'label' => 'Идентификатор МО',
                    'rules' => '',
                    'type' => 'int'
                ),
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
                    'field' => 'AccountingData_InventNumber',
                    'label' => 'Инвентарный номер',
                    'rules' => '',
                    'type' => 'string'
				),
				array(
                    'field' => 'MedProductClass_id',
                    'label' => 'Идентификатор медицинского изделия',
                    'rules' => '',
                    'type' => 'string'
				),
				array(
                    'field' => 'MedProductClass_Name',
                    'label' => 'Наименование медицинского изделия',
                    'rules' => '',
                    'type' => 'string'
				),
				array(
                    'field' => 'MedProductClass_Model',
                    'label' => 'Модель медицинского изделия',
                    'rules' => '',
                    'type' => 'string'
				),
				array(
                    'field' => 'MedProductCard_SerialNumber',
                    'label' => 'Серийный номер',
                    'rules' => '',
                    'type' => 'string'
				),
				array(
                    'field' => 'AccountingData_RegNumber',
                    'label' => 'Регистрационный номер',
                    'rules' => '',
                    'type' => 'string'
				),
				array(
                    'field' => 'CardType_id',
                    'label' => 'Тип медицинского изделия',
                    'rules' => '',
                    'type' => 'string'
				),
				array(
                    'field' => 'FRMOEquipment_id',
                    'label' => 'Идентификатор медицинского оборудования ФРМО',
                    'rules' => '',
                    'type' => 'string'
				),
				array(
                    'field' => 'ClassRiskType_id',
                    'label' => 'Идентификатор класса риска применения',
                    'rules' => '',
                    'type' => 'string'
				),
				array(
                    'field' => 'FuncPurpType_id',
                    'label' => 'Идентификатор функционального назначения',
                    'rules' => '',
                    'type' => 'string'
				),
				array(
                    'field' => 'UseAreaType_id',
                    'label' => 'Идентификатор области применения',
                    'rules' => '',
                    'type' => 'string'
				),
				array(
                    'field' => 'UseSphereType_id',
                    'label' => 'Идентификатор сферы применения',
                    'rules' => '',
                    'type' => 'string'
				),
				array(
                    'field' => 'LpuBuilding_Name',
                    'label' => 'Код подразделения',
                    'rules' => '',
                    'type' => 'string'
				),
				array(
                    'field' => 'FinancingType_id',
                    'label' => 'Идентификатор программы закупки',
                    'rules' => '',
                    'type' => 'string'
				),
				array(
                    'field' => 'AccountingData_setDate',
                    'label' => 'Дата ввода в эксплуатацию',
                    'rules' => '',
                    'type' => 'date'
				),
				array(
                    'field' => 'AccountingData_RegNumber',
                    'label' => 'Регистрационный знак',
                    'rules' => '',
                    'type' => 'string'
				),
				array(
                    'field' => 'MedProductCard_BoardNumber',
                    'label' => 'Бортовой номер',
                    'rules' => '',
                    'type' => 'string'
				),
				array(
                    'field' => 'MedProductCard_IsNotFRMO',
                    'label' => 'Не передавать на ФРМО',
                    'rules' => '',
                    'type' => 'checkbox'
				),
			),
			'getMedProductClassList' => array(
				array(
                    'field' => 'Lpu_id',
                    'label' => 'Идентификатор МО',
                    'rules' => '',
                    'type'  => 'id'
                ),
				array(
                    'field' => 'query',
                    'label' => 'Запрос от комбобокса',
                    'rules' => 'trim',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'MedProductClass_id',
                    'label' => 'Идентификатор класса медицинского изделия',
                    'rules' => 'trim',
                    'type'  => 'id'
                ),
                array(
                    'field' => 'MedProductClass_Name',
                    'label' => 'Наименование класса медицинского изделия',
                    'rules' => 'trim',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'MedProductClass_Model',
                    'label' => 'Модель класса медицинского изделия',
                    'rules' => 'trim',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'MedProductType_id',
                    'label' => 'Вид МИ',
                    'rules' => 'trim',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'CardType_id',
                    'label' => 'Тип медицинского изделия',
                    'rules' => 'trim',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'ClassRiskType_id',
                    'label' => 'Класс потенциального риска применения',
                    'rules' => 'trim',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'FuncPurpType_id',
                    'label' => 'Функциональное назначение',
                    'rules' => 'trim',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'UseAreaType_id',
                    'label' => 'Область применения',
                    'rules' => 'trim',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'UseSphereType_id',
                    'label' => 'Сфера применения',
                    'rules' => 'trim',
                    'type'  => 'string'
				),
				array(
                    'field' => 'FRMOEquipment_id',
                    'label' => 'ФРМО. Перечень аппаратов и оборудования отделений (кабинетов) медицинской организации',
                    'rules' => 'trim',
                    'type'  => 'string'
				)
			),
			'getClassDataFields' => array(
				array(
                    'field' => 'query',
                    'label' => 'Запрос от комбобокса',
                    'rules' => 'trim',
                    'type'  => 'string'
                )
			),
			'loadLpuTransport' => array(
				array(
					'field' => 'LpuTransport_id',
					'label' => 'Идентификатор ',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadLpuDispContract' => array(
				array(
					'field' => 'LpuDispContract_id',
					'label' => 'Идентификатор ',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadLpuQuote' => array(
				array(
					'field' => 'LpuQuote_id',
					'label' => 'Идентификатор ',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'int'
				)
			),
			'saveLpuDispContract' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuDispContract_id',
					'label' => 'Идентификатор договора с другим МО',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuDispContract_setDate',
					'label' => 'Дата начала договора',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'LpuDispContract_disDate',
					'label' => 'Дата окончания договора',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'SideContractType_id',
					'label' => 'Сторона договора',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'ContractType_id',
					'label' => 'Тип договора',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'LpuDispContract_Num',
					'label' => 'Номер договора',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'Lpu_oid',
					'label' => 'МО',
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
					'field' => 'LpuSection_id',
					'label' => 'Отделение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'serviceContractList',
					'label' => 'Услуга договора',
					'rules' => '',
					'type' => 'string'
				)
			),
			'saveLpuPeriodOMS' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuPeriodOMS_id',
					'label' => 'Идентификатор периода ОМС',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'LpuPeriodOMS_begDate',
					'label' => 'Дата включения',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'LpuPeriodOMS_endDate',
					'label' => 'Дата исключения',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'LpuPeriodOMS_DogNum',
					'label' => 'Номер договора',
					'rules' => 'trim',
					'type' => 'string'
				),	array(
					'field' => 'LpuPeriodOMS_RegNumC',
					'label' => 'Код территории МО',
					'rules' => 'trim',
					'type' => 'string'
				),	array(
					'field' => 'LpuPeriodOMS_RegNumN',
					'label' => 'Регистрационный номер МО',
					'rules' => 'trim',
					'type' => 'string'
				),
			),
		'saveLpuOMS' => array(
				array(
					'field' => 'Org_id',
					'label' => 'Org_id',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuPeriodOMS_id',
					'label' => 'Идентификатор периода ОМС',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'LpuPeriodOMS_pid',
					'label' => 'Идентификатор периода ОМС',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'LpuPeriodOMS_begDate',
					'label' => 'Дата включения',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'LpuPeriodOMS_DogNum',
					'label' => 'Номер договора',
					'rules' => 'trim',
					'type' => 'string'
				),	array(
					'field' => 'LpuPeriodOMS_RegNumC',
					'label' => 'Код территории МО',
					'rules' => 'trim',
					'type' => 'string'
				),	array(
					'field' => 'LpuPeriodOMS_RegNumN',
					'label' => 'Регистрационный номер МО',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'LpuPeriodOMS_Descr',
					'label' => 'LpuPeriodOMS_Descr',
					'rules' => 'trim',
					'type' => 'string'
				),
			),
			'saveLpuPeriodDLO' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuUnit_id',
					'label' => 'Идентификатор группы отделений',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuPeriodDLO_id',
					'label' => 'Идентификатор периода ЛЛО',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'LpuPeriodDLO_begDate',
					'label' => 'Дата включения',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'LpuPeriodDLO_Code',
					'label' => 'Код ЛЛО',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'LpuPeriodDLO_endDate',
					'label' => 'Дата исключения',
					'rules' => 'trim',
					'type' => 'date'
				)
			),
			'saveOrgWorkPeriod' => array(
				array(
					'field' => 'Org_id',
					'label' => 'Идентификатор организации',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'OrgWorkPeriod_id',
					'label' => 'Идентификатор периода работы в системе Промед',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'OrgWorkPeriod_begDate',
					'label' => 'Дата начала',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'OrgWorkPeriod_endDate',
					'label' => 'Дата окончания',
					'rules' => 'trim',
					'type' => 'date'
				)
			),
			'saveLpuPeriodDMS' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuPeriodDMS_id',
					'label' => 'Идентификатор периода ДМС',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'LpuPeriodDMS_begDate',
					'label' => 'Дата включения',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'LpuPeriodDMS_endDate',
					'label' => 'Дата исключения',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'LpuPeriodDMS_DogNum',
					'label' => 'Номер договора',
					'rules' => 'trim',
					'type' => 'string'
				)
			),
			'saveLpuPeriodFondHolder' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuPeriodFondHolder_id',
					'label' => 'Идентификатор периода Фондодержания',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'LpuPeriodFondHolder_begDate',
					'label' => 'Дата включения',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'LpuPeriodFondHolder_endDate',
					'label' => 'Дата исключения',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'LpuRegionType_id',
					'label' => 'Тип участка',
					'rules' => '',
					'type' => 'string'
				)
			),
			'saveLpuPassport' => array(
				array(
					'field' => 'Server_id',
					'label' => 'Идентификатор сервера',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'PassportToken_tid',
					'label' => 'ОИД',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Org_KPN',
					'label' => 'КПН',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'LpuPmuClass_id',
					'label' => 'Тип МО (ИЭМК)',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'ID',
					'rules' => 'trim|required',
					'type' => 'id'
				),
                array(
                    'field' => 'Lpu_gid',
                    'label' => 'Головное учреждение',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'InstitutionLevel_id',
                    'label' => 'Уровень учреждения в иерархии сети',
                    'rules' => '',
                    'type' => 'id'
                ),
				array(
					'field' => 'TOUZType_id',
					'label' => 'Территория (отдел) ТОУЗ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Org_tid',
					'label' => 'ТОУЗ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Org_pid',
					'label' => 'Вышестоящая организация',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_Name',
					'label' => 'Наименование',
					'rules' => 'trim|required',
					'type' => 'string'
				),
				array(
					'field' => 'Lpu_Nick',
					'label' => 'Краткое наименование',
					'rules' => 'trim|required',
					'type' => 'string'
				),
				array(
					'field' => 'Lpu_Ouz',
					'label' => 'Код ОУЗ',
					'rules' => 'trim|max_length[7]',
					'type' => 'int'
				),
				array(
					'field' => 'Lpu_f003mcod',
					'label' => 'Федеральный реестровый код МО',
					'rules' => 'trim|max_length[6]',
					'type' => 'string'
				),
				array(
					'field' => 'Lpu_RegNomN2',
					'label' => 'Региональный реестровый код МО',
					'rules' => 'trim|max_length[6]',
					'type' => 'string'
				),
				array(
					'field' => 'Org_RegName',
					'label' => 'Региональное наименование МО',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'LpuSUR_id',
					'label' => 'Идентификатор МО СУР',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_begDate',
					'label' => 'Дата начала деятельности',
					'rules' => 'trim|required',
					'type' => 'date'
				),
				array(
					'field' => 'Lpu_endDate',
					'label' => 'Дата закрытия',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'Lpu_pid',
					'label' => 'Правопреемник',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'Lpu_nid',
					'label' => 'Наследователь',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'Lpu_Email',
					'label' => 'Адрес электронной почты',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Lpu_Www',
					'label' => 'Сайт МО',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Lpu_Phone',
					'label' => 'Телефон МО',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Lpu_Worktime',
					'label' => 'Время работы МО',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Lpu_StickNick',
					'label' => 'Наименование для печати ЛВН',
					'rules' => 'trim|max_length[38]',
					'type' => 'string'
				),
				array(
					'field' => 'Lpu_StickAddress',
					'label' => 'Адрес для печати ЛВН',
					'rules' => 'trim|max_length[38]',
					'type' => 'string'
				),
				array(
					'field' => 'Lpu_Okato',
					'label' => 'ОКАТО',
					'rules' => 'max_length[20]|required',
					'type' => 'string'
				),
				array(
					'field' => 'Oktmo_id',
					'label' => 'ОКТМО',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'LpuPmuType_id',
					'label' => 'Тип МО для ПМУ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuType_id',
					'label' => 'Тип МО',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'LpuAgeType_id',
					'label' => 'Тип МО по возрасту',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Lpu_isCMP',
					'label' => 'Идентификатор СМП',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'OftenCallers_CallTimes',
					'label' => 'Количество звонков (Регистр часто обращающихся)',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'OftenCallers_SearchDays',
					'label' => 'Количество дней, проверяемых на наличие вызовов (Регистр часто обращающихся)',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'OftenCallers_FreeDays',
					'label' => 'Количество дней нахождения в регистре(Регистр часто обращающихся)',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Lpu_HasLocalPacsServer',
					'label' => 'Флаг наличия локального PACS-сервера в МО',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Lpu_LocalPacsServerIP',
					'label' => 'IP-адрес лоакльного PACS-сервера',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Lpu_LocalPacsServerAetitle',
					'label' => 'AETITLE лоакльного PACS-сервера',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Lpu_LocalPacsServerPort',
					'label' => 'Порт лоакльного PACS-сервера',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Lpu_LocalPacsServerWadoPort',
					'label' => 'Wado-порт лоакльного PACS-сервера',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'PAddress_id',
					'label' => 'Идентификатор адреса',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'PKLCountry_id',
					'label' => 'Страна',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'PKLRGN_id',
					'label' => 'Регион',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'PKLSubRGN_id',
					'label' => 'Район',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'PKLCity_id',
					'label' => 'Город',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'PKLTown_id',
					'label' => 'Населенный пункт',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'PKLStreet_id',
					'label' => 'Улица',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'PAddress_Address',
					'label' => 'Текстовая строка адреса',
					'length' => 100,
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'PAddress_Corpus',
					'label' => 'Номер корпуса',
					'length' => 5,
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'PAddress_Flat',
					'label' => 'Номер квартиры',
					'length' => 5,
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'PAddress_House',
					'label' => 'Номер дома',
					'length' => 5,
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'PAddress_Zip',
					'label' => 'Почтовый индекс',
					'length' => 6,
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'UAddress_id',
					'label' => 'Идентификатор адреса',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'UKLCountry_id',
					'label' => 'Страна',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'UKLRGN_id',
					'label' => 'Регион',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'UKLSubRGN_id',
					'label' => 'Район',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'UKLCity_id',
					'label' => 'Город',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'UKLTown_id',
					'label' => 'Населенный пункт',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'UKLStreet_id',
					'label' => 'Улица',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'UAddress_Address',
					'label' => 'Текстовая строка адреса',
					'length' => 100,
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'UAddress_Corpus',
					'label' => 'Номер корпуса',
					'length' => 5,
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'UAddress_Flat',
					'label' => 'Номер квартиры',
					'length' => 5,
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'UAddress_House',
					'label' => 'Номер дома',
					'length' => 5,
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'UAddress_Zip',
					'label' => 'Почтовый индекс',
					'length' => 6,
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Okfs_id',
					'label' => 'ОКФС',
					'rules' => 'trim|required',
					'type' => 'int'
				),
				array(
					'field' => 'Okopf_id',
					'label' => 'ОКОПФ',
					'rules' => 'trim|required',
					'type' => 'int'
				),
				array(
					'field' => 'Org_OKPO',
					'label' => 'ОКПО',
					'rules' => 'trim|required',
					'type' => 'int'
				),
				array(
					'field' => 'Org_INN',
					'label' => 'ИНН',
					'rules' => 'trim|required',
					'type' => 'int'
				),
				array(
					'field' => 'Org_KPP',
					'label' => 'КПП МО',
					'rules' => 'trim|required|exact_length[9]',
					'type' => 'string'
				),
				array(
					'field' => 'Org_OGRN',
					'label' => 'ОГРН',
					'rules' => 'trim|required',
					'type' => 'int'
				),
				array(
					'field' => 'Org_OKDP',
					'label' => 'ОКДП',
					'rules' => 'trim',
					'type' => 'int'
				),
				// Убрал обязательность поля
				// https://redmine.swan.perm.ru/issues/10300
				array(
					'field' => 'Okogu_id',
					'label' => 'ОКОГУ',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'Okved_id',
					'label' => 'ОКВЭД',
					'rules' => 'trim|required',
					'type' => 'int'
				),
				array(
					'field' => 'Lpu_DistrictRate',
					'label' => 'Районный коэффициент',
					'rules' => 'is_numeric|trim',
					'type' => 'string'
				),
				array(
					'field' => 'DepartAffilType_id',
					'label' => 'Ведомственная принадлежность',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'Lpu_DocReg',
					'label' => 'Наименование регистрационного документа',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Org_lid',
					'label' => 'Орган',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_RegDate',
					'label' => 'Дата регистрации',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'Lpu_RegNum',
					'label' => 'Рег. номер',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'Lpu_PensRegNum',
					'label' => 'Рег. номер в ПФ РФ',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'Lpu_FSSRegNum',
					'label' => 'Рег. номер в ФСС',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'LpuSubjectionLevel_id',
					'label' => 'Уровень подчиненности',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'LpuLevel_id',
					'label' => 'Уровень МО',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'FedLpuLevel_id',
					'label' => 'Уровень МО',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'LpuLevel_cid',
					'label' => 'Уровень МО (СМП)',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'LpuLevelType_id',
					'label' => 'Идентификатор записи уровня оказания МП',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LevelType_id',
					'label' => 'Уровень оказания МП',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_VizitFact',
					'label' => 'Посещений в смену',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'Lpu_KoikiFact',
					'label' => 'Число коек',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'Lpu_AmbulanceCount',
					'label' => 'Число выездных бригад',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'Lpu_FondOsn',
					'label' => 'Фондооснащенность',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'Lpu_FondEquip',
					'label' => 'Фондовооруженность',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'Lpu_ErInfo',
					'label' => 'Информация для ЭР',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Lpu_IsAllowInternetModeration',
					'label' => 'Разрешить модерацию записей через интернет',
					'rules' => '',
					'type'  => 'string'
				),
				array(
					'field' => 'Lpu_MedCare',
					'label' => 'Виды помощи',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Lpu_IsSecret',
					'label' => 'Флаг особого статуса МО',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'LpuSpecType_id',
					'label' => 'Специализация',
					'rules' => 'trim',
					'type' => 'id'
				),
                //PassportMO
                array(
                    'field' => 'PasportMO_id',
                    'label' => 'Идентификатор паспорта МО',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'PasportMO_MaxDistansePoint',
                    'label' => 'Расстояние до наиболее удаленной точки территориального обслуживания (км)',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'PasportMO_IsFenceTer',
                    'label' => 'Ограждение территории',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'PasportMO_IsNoFRMP',
                    'label' => 'Не учитывать при выгрузке ФРМР',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'DLocationLpu_id',
                    'label' => 'Местоположение',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'PasportMO_IsSecur',
                    'label' => 'Наличие охраны',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'PasportMO_IsMetalDoors',
                    'label' => 'Наличие металлических входных дверей в здании',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'PasportMO_IsVideo',
                    'label' => 'Видеонаблюдение территорий и помещений для здания',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'PasportMO_IsAssignNasel',
                    'label' => 'флаг МО имеет приписное население',
					'rules' => 'trim',
					'type' => 'string'
                ),
                array(
					'field' => 'PasportMO_IsTerLimited',
					'label' => 'Приспособленность территории для пациентов с ограниченными возможностями',
					'rules' => '',
					'type' => 'string'
                ),
                array(
                    'field' => 'PasportMO_IsAccompanying',
                    'label' => 'Проживание сопровождающих лиц',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'PasportMO_Station',
                    'label' => 'Ближайшая станция',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'PasportMO_DisStation',
                    'label' => 'Расстояние до ближайшей станции (км)',
                    'rules' => '',
                    'type' => 'float'
                ),
                array(
                    'field' => 'PasportMO_Airport',
                    'label' => 'Ближайший аэропорт',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'PasportMO_DisAirport',
                    'label' => 'Расстояние до аэропорта (км)',
                    'rules' => '',
                    'type' => 'float'
                ),
                array(
                    'field' => 'PasportMO_Railway',
                    'label' => 'Ближайший автовокзал',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'PasportMO_Disrailway',
                    'label' => 'Расстояние до автовокзала (км)',
                    'rules' => '',
                    'type' => 'float'
                ),
                array(
                    'field' => 'PasportMO_Heliport',
                    'label' => 'Ближайшая вертолетная площадка',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'PasportMO_DisHeliport',
                    'label' => 'Расстояние до вертолетной площадки (км)',
                    'rules' => '',
                    'type' => 'float'
                ),
                array(
                    'field' => 'PasportMO_MainRoad',
                    'label' => 'Главная дорога',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Lpu_IsLab',
                    'label' => 'Лаборатория',
                    'rules' => '',
                    'type' => 'id'
                ),
				array( 'field' => 'LpuOwnership_id', 'label' => 'Форма собственности', 'rules' => '', 'type' => 'id'),
				array( 'field' => 'MOAreaFeature_id', 'label' => 'Территориальный признак', 'rules' => '', 'type' => 'id'),
				array( 'field' => 'LpuBuildingPass_mid', 'label' => 'Основное здание', 'rules' => '', 'type' => 'id'),
				array( 'field' => 'Lpu_Founder', 'label' => 'Учредитель', 'rules' => '', 'type' => 'string'),
				//kz
                array( 'field' => 'LpuInfo_id', 'label' => 'идентификатор', 'rules' => '', 'type' => 'id'),
                array( 'field' => 'Lpu_id', 'label' => 'ЛПУ', 'rules' => '', 'type' => 'id'),
                array( 'field' => 'LpuNomen_id', 'label' => 'Номенклатура ЛПО', 'rules' => '', 'type' => 'id'),
                array( 'field' => 'LpuInfo_BIN', 'label' => 'БИН, целое число', 'rules' => '', 'type' => 'string'),
                array( 'field' => 'PropertyClass_id', 'label' => 'Форма собственности', 'rules' => '', 'type' => 'id'),
                array( 'field' => 'LpuInfo_AkkrNum', 'label' => 'Номер аккредитации', 'rules' => '', 'type' => 'string'),
                array( 'field' => 'LpuInfo_AkkrDate', 'label' => 'Дата аккредитации', 'rules' => '', 'type' => 'string'),
                array( 'field' => 'SubjectionType_id', 'label' => 'Подчиненность', 'rules' => '', 'type' => 'id'),
                array( 'field' => 'LpuInfo_Area', 'label' => 'Занимаемая площадь (кв.м)', 'rules' => '', 'type' => 'string'),
                array( 'field' => 'LpuInfo_Distance', 'label' => 'Отдаленность от районного центра (в км) для организаций здравоохранения', 'rules' => '', 'type' => 'string'),
                array( 'field' => 'CmpStationCategory_id', 'label' => 'Идентификатор категорийности станции', 'rules' => '', 'type' => 'id'),

				array( 'field' => 'PasportMO_KolServ', 'label' => 'Численность обслуживаемого населения', 'rules' => '', 'type' => 'int' ),
				array( 'field' => 'PasportMO_KolServSel', 'label' => 'Численность обслуживаемого населения сельского', 'rules' => '', 'type' => 'int' ),
				array( 'field' => 'PasportMO_KolServDet', 'label' => 'Численность обслуживаемого населения детского', 'rules' => '', 'type' => 'int' ),
				array( 'field' => 'PasportMO_KolCmpMes', 'label' => 'Число самостоятельных станций СМП, применяющих МЭС', 'rules' => '', 'type' => 'int' ),
				array( 'field' => 'PasportMO_KolCmpPay', 'label' => 'Число самостоятельных станций СМП, переведенных на оплату МП по результату деятельности', 'rules' => '', 'type' => 'int' ),
				array( 'field' => 'PasportMO_KolCmpWage', 'label' => 'Число самостоятельных станций СМП, переведенных на отраслевую систему оплаты труда', 'rules' => '', 'type' => 'int' ),
			),
			'getIsAllowInternetModeration' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => 'required',
					'type'  => 'id'
				)
			),
			'saveLpuBuilding' => array(
				array(
					'field' => 'MOArea_id',
					'label' => 'Идентификатор площадки, занимаемой МО ',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'LpuBuildingPass_id',
					'label' => 'Идентификатор здания',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'MOSectionsData',
					'label' => 'Строка с связанными отделениями',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'FuelType_id',
					'label' => 'Тип толива',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'LpuBuildingPass_IsFreeEnergy',
					'label' => 'Наличие независимых источников энергоснабжения',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'LpuBuildingPass_MedWorkCabinet',
					'label' => 'Число кабинетов врачебного приема',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'LpuBuildingPass_BedArea',
					'label' => 'Площадь коечных отделений',
					'rules' => 'trim',
					'type' => 'float'
				),
				array(
					'field' => 'LpuBuildingPass_BuildingIdent',
					'label' => 'Идентификатор здания',
					'rules' => 'trim|required',
					'type' => 'string'
				),
				array(
					'field' => 'BuildingTechnology_id',
					'label' => 'Идентификатор класса строительства',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuBuildingPass_Name',
					'label' => 'Наименование',
					'rules' => 'trim|required',
					'type' => 'string'
				),
				array(
					'field' => 'LpuBuildingType_id',
					'label' => 'Тип',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'LpuBuildingPass_Number',
					'label' => 'Номер',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'BuildingAppointmentType_id',
					'label' => 'Назначение',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'LpuBuildingPass_Project',
					'label' => 'Построено по проекту',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'LpuBuildingPass_YearBuilt',
					'label' => 'Год постройки',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'LpuBuildingPass_YearRepair',
					'label' => 'Год последней реконструкции (капитального ремонта)',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'LpuBuildingPass_PurchaseCost',
					'label' => 'Первоначальная стоимость',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'LpuBuildingPass_ResidualCost',
					'label' => 'Остаточная стоимость',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'LpuBuildingPass_Floors',
					'label' => 'Этажность',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'LpuBuildingPass_TotalArea',
					'label' => 'Общая площадь здания',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'LpuBuildingPass_WorkArea',
					'label' => 'Рабочая площадь',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'LpuBuildingPass_RegionArea',
					'label' => 'Площадь участка',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'LpuBuildingPass_WorkAreaWardSect',
					'label' => 'Рабочая площадь палатных отделений (кв. м.)',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'LpuBuildingPass_WorkAreaWard',
					'label' => 'В. т. ч. палат, (кв. м.)',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'LpuBuildingPass_PowerProjBed',
					'label' => 'Мощность по проекту (коек)',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'LpuBuildingPass_PowerProjViz',
					'label' => 'Мощность по проекту (посещений в смену)',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'LpuBuildingPass_OfficeCount',
					'label' => 'Кол-во кабинетов врачебного приема',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'LpuBuildingPass_OfficeArea',
					'label' => 'Площадь кабинетов врачебного приема (кв. м.)',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'BuildingType_id',
					'label' => 'Тип постройки',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'LpuBuildingPass_NumProj',
					'label' => 'Номер проекта',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'BuildingHoldConstrType_id',
					'label' => 'Несущие конструкции',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'BuildingOverlapType_id',
					'label' => 'Перекрытия',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'LpuBuildingPass_IsAirCond',
					'label' => 'Кондиционирование',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'LpuBuildingPass_IsVentil',
					'label' => 'Вентиляция',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'LpuBuildingPass_IsDetached',
					'label' => 'Отдельно стоящее здание',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'LpuBuildingPass_IsElectric',
					'label' => 'Электроснабжение',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'LpuBuildingPass_IsPhone',
					'label' => 'Телефонизация',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'LpuBuildingPass_IsHeat',
					'label' => 'Отопление',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'LpuBuildingPass_IsColdWater',
					'label' => 'Холодное водоснабжение',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'LpuBuildingPass_IsHotWater',
					'label' => 'Горячее водоснабжение',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'LpuBuildingPass_IsSewerage',
					'label' => 'Канализация',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'LpuBuildingPass_IsDomesticGas',
					'label' => 'Бытовое газоснабжение',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'LpuBuildingPass_IsMedGas',
					'label' => 'Централизованное лечебное газоснабжение',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'LpuBuildingPass_HostLift',
					'label' => 'Число больничных лифтов',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'LpuBuildingPass_HostLiftReplace',
					'label' => 'Число больничных лифтов, требующих замены',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'LpuBuildingPass_PassLift',
					'label' => 'Число лифтов пассажирских',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'LpuBuildingPass_PassLiftReplace',
					'label' => 'Число пассажирских  лифтов, требующих замены',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'LpuBuildingPass_TechLift',
					'label' => 'Число технологических подъемников',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'LpuBuildingPass_TechLiftReplace',
					'label' => 'Число технологических подъемников, требующих замены',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'LpuBuildingPass_WearPersent',
					'label' => 'Степень износа здания (%)',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'PropertyType_id',
					'label' => 'Отношение к собственности',
					//'rules' => 'required|trim',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuBuildingPass_IsInsulFacade',
					'label' => 'Наличие утепления фасада',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'LpuBuildingPass_IsFireAlarm',
					'label' => 'Наличие охранно-пожарной сигнализации',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'LpuBuildingPass_IsHeatMeters',
					'label' => 'Приборы учета тепла',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'LpuBuildingPass_IsWaterMeters',
					'label' => 'Приборы учета водоснабжения',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'LpuBuildingPass_IsRequirImprovement',
					'label' => 'Требует благоустройства',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'LpuBuildingPass_YearProjDoc',
					'label' => 'Год разработки проектной документации',
					'rules' => 'trim',
					'type' => 'date'
				),
                //Доработки
                array(
					'field' => 'LpuBuildingPass_IsRequirImprovement',
					'label' => 'Требует благоустройства',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'LpuBuildingPass_StatPlace',
					'label' => 'Стационарные места',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'LpuBuildingPass_AmbPlace',
					'label' => 'Амбулаторные места',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'LpuBuildingPass_BuildVol',
					'label' => 'Объем здания',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'LpuBuildingPass_IsBalance',
					'label' => 'На балансе',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'LpuBuildingPass_EffBuildVol',
					'label' => 'Полезная площадь здания, кв. м.',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'DHotWater_id',
					'label' => 'Горячее водоснабжение',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DHeating_id',
					'label' => 'Отопление',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DCanalization_id',
					'label' => 'Канализация',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'BuildingCurrentState_id',
					'label' => 'Текущее состояние здания',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'DLink_id',
					'label' => 'Канал связи',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'LpuBuildingPass_FactVal',
					'label' => 'Фактическая стоимость',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'LpuBuildingPass_ValDT',
					'label' => 'Дата оценки стоимости',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'LpuBuildingPass_IsAutoFFSig',
					'label' => 'Автоматическая пожарная сигнализация в здании',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'LpuBuildingPass_IsCallButton',
					'label' => 'Кнопка (брелок) экстренного вызова милиции в здании',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'LpuBuildingPass_IsSecurAlarm',
					'label' => 'Охранная сигнализация в здании',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'LpuBuildingPass_IsWarningSys',
					'label' => 'Система оповещения и управления эвакуацией людей при пожаре в здании',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'LpuBuildingPass_IsFFWater',
					'label' => 'Противопожарное водоснабжение здания',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'LpuBuildingPass_IsFFOutSignal',
					'label' => 'Вывод сигнала о срабатывании систем противопожарной защиты в подразделении пожарной охраны в здании',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'LpuBuildingPass_IsConnectFSecure',
					'label' => 'Прямая телефонная связь с подразделением пожарной охраны для здания',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'LpuBuildingPass_CountDist',
					'label' => 'Количество нарушений требований пожарной безопасности',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'LpuBuildingPass_IsEmergExit',
					'label' => 'Наличие эвакуационных путей и выходов в здании',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'LpuBuildingPass_RespProtect',
					'label' => 'Обеспеченность персонала здания учреждения средствами индивидуальной защиты органов дыхания',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'LpuBuildingPass_StretProtect',
					'label' => 'Обеспеченность персонала здания учреждения носилками для эвакуации маломобильных пациентов',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'LpuBuildingPass_FSDis',
					'label' => 'Удаление от ближайшего пожарного подразделения',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'LpuBuildingPass_IsBuildEmerg',
					'label' => 'Находится в аварийном состоянии',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'LpuBuildingPass_IsNeedRec',
					'label' => 'Требует реконструкции',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'LpuBuildingPass_IsNeedCap',
					'label' => 'Требует капитального ремонта',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'LpuBuildingPass_IsNeedDem',
					'label' => 'Требует сноса',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'LpuBuildingPass_CoordLat',
					'label' => 'Широта',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'LpuBuildingPass_CoordLong',
					'label' => 'Долгота',
					'rules' => 'trim',
					'type' => 'string'
				),

				//kz
				array( 'field' => 'BuildingLpu_id','label' => 'Идентификатор', 'rules' => '','type' => 'id' ),
				array( 'field' => 'PropertyClass_id','label' => 'Форма владения', 'rules' => '','type' => 'id' ),
				array( 'field' => 'BuildingUse_id','label' => 'Назначение здания (сооружения)', 'rules' => '','type' => 'int' ),
				array( 'field' => 'BuildingClass_id','label' => 'Тип здания', 'rules' => '','type' => 'id' ),
				array( 'field' => 'BuildingState_id','label' => 'Текущее состояние здания', 'rules' => '','type' => 'id' ),
				array( 'field' => 'HeatingType_id','label' => 'Отопление', 'rules' => '','type' => 'id' ),
				array( 'field' => 'BuildingLpu_RepEndDate','label' => 'Окончание текущего ремонта', 'rules' => '','type' => 'date' ),
				array( 'field' => 'BuildingLpu_RepCost','label' => 'Стоимость текущего ремонта (в тыс. тенге)', 'rules' => '','type' => 'string' ),
				array( 'field' => 'BuildingLpu_RepCapBegDate','label' => 'Начало капитального ремонта', 'rules' => '','type' => 'date' ),
				array( 'field' => 'BuildingLpu_RepCapEndDate','label' => 'Окончание капитального ремонта', 'rules' => '','type' => 'date' ),
				array( 'field' => 'BuildingLpu_RepCapCost','label' => 'Стоимость капитального ремонта (в тыс. тенге)', 'rules' => '','type' => 'string' ),
				array( 'field' => 'ColdWaterType_id','label' => 'Холодное водоснабжение', 'rules' => '','type' => 'id' ),
				array( 'field' => 'VentilationType_id','label' => 'Вентиляция', 'rules' => '','type' => 'id' ),
				array( 'field' => 'ElectricType_id','label' => 'Электроснабжение', 'rules' => '','type' => 'id' ),
				array( 'field' => 'GasType_id','label' => 'Газоснабжение', 'rules' => '','type' => 'id' ),
				array( 'field' => 'BuildingLpu_DeprecCost','label' => 'Стоимость износа (тыс. тенге)', 'rules' => '','type' => 'string' ),
				array( 'field' => 'BuildingLpu_RepBegDate','label' => 'Начало текущего ремонта', 'rules' => '','type' => 'date' ),
			),
			'saveSmpTariff' => array(
				array(
					'field' => 'CmpProfileTariff_id',
					'label' => 'Идентификатор тарифа',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'LpuSectionProfile_id',
					'label' => 'Профиль',
					'rules' => '',
					'type' => 'int',
				),
				array(
					'field' => 'TariffClass_id',
					'label' => 'Вид тарифа',
					'rules' => 'required',
					'type' => 'int',
				),
				array(
					'field' => 'CmpProfileTariff_begDT',
					'label' => 'Начало действия',
					'rules' => 'trim|required',
					'type' => 'date'
				),
				array(
					'field' => 'CmpProfileTariff_endDT',
					'label' => 'Окончание действия',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'CmpProfileTariff__Value',
					'label' => 'Значение',
					'rules' => 'required',
					'type' => 'string'
				),
			),
			'saveLpuTariff' => array(
				array(
					'field' => 'LpuTariff_id',
					'label' => 'Идентификатор тарифа',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'TariffClass_id',
					'label' => 'Вид тарифа',
					'rules' => 'required',
					'type' => 'int',
				),
				array(
					'field' => 'LpuTariff_setDate',
					'label' => 'Начало действия',
					'rules' => 'trim|required',
					'type' => 'date'
				),
				array(
					'field' => 'LpuTariff_disDate',
					'label' => 'Окончание действия',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'LpuTariff_Tariff',
					'label' => 'Значение',
					'rules' => 'required',
					'type' => 'string'
				)
			),
			'saveTariffDisp' => array(
				array(
					'field' => 'TariffDisp_id',
					'label' => 'Идентификатор тарифа',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSectionProfile_id',
					'label' => 'Профиль',
					'rules' => '',
					'type' => 'id',
				),
				array(
					'field' => 'TariffClass_id',
					'label' => 'Вид тарифа',
					'rules' => 'required',
					'type' => 'id',
				),
				array(
					'field' => 'AgeGroupDisp_id',
					'label' => 'Возрастная группа',
					'rules' => '',
					'type' => 'id',
				),
				array(
					'field' => 'Sex_id',
					'label' => 'Пол',
					'rules' => 'required',
					'type' => 'id',
				),
				array(
					'field' => 'TariffDisp_begDT',
					'label' => 'Начало действия',
					'rules' => 'trim|required',
					'type' => 'date'
				),
				array(
					'field' => 'TariffDisp_endDT',
					'label' => 'Окончание действия',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'TariffDisp_Tariff',
					'label' => 'Значение',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'TariffDisp_TariffDayOff',
					'label' => 'Тариф выходного дня',
					'rules' => '',
					'type' => 'string'
				)
			),
            'deleteSmpTariff' => array (
                array(
                    'field' => 'CmpProfileTariff_id',
                    'label' => 'Идентификатор тарифа',
                    'rules' => 'trim|required',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Lpu_id',
                    'label' => 'Идентификатор МО',
                    'rules' => 'trim|required',
                    'type' => 'int'
                )
            ),
			'deleteTariffLpu' => array(
				array(
					'field' => 'LpuTariff_id',
					'label' => 'Идентификатор тарифа',
					'rules' => 'trim|required',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => 'trim|required',
					'type' => 'int'
				)
			),
            'deleteLpuLicenceProfile' => array (
                array(
                    'field' => 'LpuLicenceProfile_id',
                    'label' => 'Идентификатор вида лицензии',
                    'rules' => 'trim|required',
                    'type' => 'int'
                ),
            ),
            'deleteLpuLicenceOperationLink' => array (
                array(
                    'field' => 'LpuLicenceOperationLink_id',
                    'label' => 'Идентификатор операции с лицензией',
                    'rules' => 'trim|required',
                    'type' => 'int'
                )
            ),
            'deleteLpuLicenceLink' => array (
                array(
                    'field' => 'LpuLicenceLink_id',
                    'label' => 'Идентификатор профиля лицензии',
                    'rules' => 'trim|required',
                    'type' => 'id'
                )
            ),
            'deleteConsumables' => array (
                array(
                    'field' => 'Consumables_id',
                    'label' => 'Идентификатор расходного материала',
                    'rules' => 'trim|required',
                    'type' => 'int'
                )
            ),
            'deleteAmortization' => array (
                array(
                    'field' => 'Amortization_id',
                    'label' => 'Идентификатор оценки износа материала',
                    'rules' => 'trim|required',
                    'type' => 'int'
                )
            ),
            'deleteWorkData' => array (
                array(
                    'field' => 'WorkData_id',
                    'label' => 'Идентификатор эксплуатации материала',
                    'rules' => 'trim|required',
                    'type' => 'int'
                )
            ),
            'deleteDowntime' => array (
                array(
                    'field' => 'Downtime_id',
                    'label' => 'Идентификатор простоя МИ',
                    'rules' => 'trim|required',
                    'type' => 'int'
                )
            ),
            'deleteMeasureFundCheck' => array (
                array(
                    'field' => 'MeasureFundCheck_id',
                    'label' => 'Идентификатор свидетельства о проверке средства измерения',
                    'rules' => 'trim|required',
                    'type' => 'int'
                )
            ),
            'deleteMedProductCard' => array (
                array(
                    'field' => 'MedProductCard_id',
                    'label' => 'Идентификатор карточки медицинского изделия',
                    'rules' => 'trim|required',
                    'type' => 'int'
                )
            ),
            'deleteLpuBuildingPass' => array (
                array(
                    'field' => 'LpuBuildingPass_id',
                    'label' => 'Идентификатор здания МО',
                    'rules' => 'trim|required',
                    'type' => 'int'
                )
            ),
            'deleteEquipment' => array (
                array(
                    'field' => 'LpuEquipment_id',
                    'label' => 'Идентификатор оборудования',
                    //'rules' => 'trim|required',
                    'type' => 'id'
                ),
                array(
                    'field' => 'LpuEquipmentPacs_id',
                    'label' => 'Идентификатор оборудования PACS',
                    'rules' => 'trim|required',
                    'type' => 'int'
                ),
            ),
			'deleteTariffDisp' => array (
                array(
                    'field' => 'TariffDisp_id',
                    'label' => 'Идентификатор тарифа',
                    'rules' => 'trim|required',
                    'type' => 'id'
                )
            ),
			'deleteMOSectionBuildingPass' => array (
                array(
                    'field' => 'LpuSection_id',
                    'label' => 'Идентификатор отделения',
                    'rules' => 'trim|required',
                    'type' => 'id'
                )
            ),
			'saveLpuMobileTeam' => array(
				array(
					'field' => 'LpuMobileTeam_id',
					'label' => 'Идентификатор мобильной бригады',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'LpuMobileTeam_begDate',
					'label' => 'Дата начала',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'LpuMobileTeam_endDate',
					'label' => 'Дата окончания',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'LpuMobileTeam_Count',
					'label' => 'Количество бригад',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'TypeBrig1',
					'label' => '',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'TypeBrig2',
					'label' => '',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'TypeBrig3',
					'label' => '',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'TypeBrig4',
					'label' => '',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'TypeBrig5',
					'label' => '',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'TypeBrig6',
					'label' => '',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'TypeBrig7',
					'label' => '',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'TypeBrig8',
					'label' => '',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'TypeBrig9',
					'label' => '',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'TypeBrig10',
					'label' => '',
					'rules' => '',
					'type' => 'checkbox'
				)
			),
            /*'loadMkb10CodeClass' => array (
                array(
                    'field' => 'Mkb10Code_pid',
                    'label' => 'Класс МКБ-10',
                    'rules' => '',
                    'type' => 'id'
                )
            ),*/
            'loadKurortStatus' => array (
                array(
                    'field' => 'KurortStatusDoc_id',
                    'label' => 'Идентификатор статуса',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'Lpu_id',
                    'label' => 'Идентификатор лечебного учереждения',
                    'rules' => '',
                    'type' => 'int'
                )
            ),
            'loadDisSanProtection' => array (
                array(
                    'field' => 'Lpu_id',
                    'label' => 'Идентификатор МО',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'DisSanProtection_id',
                    'label' => 'Идентификатор округа горно-санитарной охраны',
                    'rules' => '',
                    'type' => 'int'
                )
            ),
            'saveKurortStatus' => array(
                array(
                    'field' => 'Lpu_id',
                    'label' => 'Идентификатор МО',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'KurortStatusDoc_IsStatus',
                    'label' => 'Наличие статуса курорта',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'KurortStatus_id',
                    'label' => 'Статус курорта',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'KurortStatusDoc_id',
                    'label' => 'Статус курорта',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'KurortStatusDoc_Doc',
                    'label' => 'Документ',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'KurortStatusDoc_Num',
                    'label' => 'Номер документа',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'KurortStatusDoc_Date',
                    'label' => 'Дата документа',
                    'rules' => '',
                    'type' => 'date'
                )
            ),
            'deleteKurortStatus' => array(
                array(
                    'field' => 'KurortStatusDoc_id',
                    'label' => 'Статус курорта',
                    'rules' => '',
                    'type' => 'id'
                )
            ),
            'saveMOArrival' => array(
                array(
                    'field' => 'Lpu_id',
                    'label' => 'Идентификатор МО',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'MOArrival_id',
                    'label' => 'Идентификатор заезда',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'MOArrival_CountPerson',
                    'label' => 'Количество человек в заезде',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'MOArrival_TreatDis',
                    'label' => 'Длительность лечения',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'MOArrival_EndDT',
                    'label' => 'Дата окончания заезда',
                    'rules' => '',
                    'type' => 'date'
                )
            ),
            'loadMOArrival' => array(
                array(
                    'field' => 'Lpu_id',
                    'label' => 'Идентификатор МО',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'MOArrival_id',
                    'label' => 'Идентификатор заезда',
                    'rules' => '',
                    'type' => 'id'
                )
            ),
            'saveKurortTypeLink' => array(
                array(
                    'field' => 'Lpu_id',
                    'label' => 'Идентификатор МО',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'KurortTypeLink_id',
                    'label' => 'Идентификатор типа курорта',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'KurortTypeLink_IsKurortTypeLink',
                    'label' => 'Наличие типа курорта',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'KurortType_id',
                    'label' => 'Статус курорта',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'KurortTypeLink_Doc',
                    'label' => 'Документ',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'KurortTypeLink_Num',
                    'label' => 'Номер документа',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'KurortTypeLink_Date',
                    'label' => 'Дата документа',
                    'rules' => '',
                    'type' => 'date'
                )
            ),
            'loadKurortTypeLink' => array(
                array(
                    'field' => 'Lpu_id',
                    'label' => 'Идентификатор МО',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'KurortTypeLink_id',
                    'label' => 'Идентификатор типа курорта',
                    'rules' => '',
                    'type' => 'id'
                )
            ),
            'saveMOArea' => array(
                array(
                    'field' => 'Lpu_id',
                    'label' => 'Идентификатор МО',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'OKATO_id',
                    'label' => 'Идентфиикатор ОКАТО',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'MOArea_id',
                    'label' => 'Идентификатор площадки, занимаемой организацией',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'MOArea_Name',
                    'label' => 'Наименование площадки',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'MOArea_Member',
                    'label' => 'Идентификатор участка',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'MoArea_Right',
                    'label' => 'Право на земельный участок',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'MoArea_Space',
                    'label' => 'Площадь участка',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'MoArea_KodTer',
                    'label' => 'Код территории',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'MoArea_OrgDT',
                    'label' => 'Дата организации',
                    'rules' => '',
                    'type' => 'date'
                ),
                array(
                    'field' => 'MoArea_AreaSite',
                    'label' => 'Площадь площадки',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'MoArea_OKATO',
                    'label' => 'ОКАТО',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'Address_id',
                    'label' => 'Идентификатор адреса',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'KLCountry_id',
                    'label' => 'Страна',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'KLRGN_id',
                    'label' => 'Регион',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'KLSubRGN_id',
                    'label' => 'Район',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'KLCity_id',
                    'label' => 'Город',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'KLAreaType_id',
                    'label' => 'Тип территории',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'KLTown_id',
                    'label' => 'Населенный пункт',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'KLStreet_id',
                    'label' => 'Улица',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Address_Address',
                    'label' => 'Текстовая строка адреса',
                    'length' => 100,
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Address_Corpus',
                    'label' => 'Номер корпуса',
                    'length' => 5,
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Address_Flat',
                    'label' => 'Номер квартиры',
                    'length' => 5,
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Address_House',
                    'label' => 'Номер дома',
                    'length' => 5,
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Address_Zip',
                    'label' => 'Почтовый индекс',
                    'length' => 6,
                    'rules' => 'trim',
                    'type' => 'string'
                )
            ),
            'loadMOArea' => array(
                array(
                    'field' => 'Lpu_id',
                    'label' => 'Идентификатор МО',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'MOArea_id',
                    'label' => 'Идентификатор площадки, занимаемой организацией',
                    'rules' => '',
                    'type' => 'id'
                )
            ),
            'loadTransportConnect' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'int'
				),
                array(
                    'field' => 'TransportConnect_id',
                    'label' => 'Идентификатор связи с транспортным узлом',
                    'rules' => '',
                    'type' => 'id'
                )
            ),
            'loadFunctionTime' => array(
                array(
                    'field' => 'Lpu_id',
                    'label' => 'Идентификатор МО',
                    'rules' => 'required',
                    'type' => 'int'
                ),
                array(
                    'field' => 'FunctionTime_id',
                    'label' => 'Идентификатор периода функционирования',
                    'rules' => '',
                    'type' => 'id'
                )
            ),
            'saveCmpSubstation' => array(
                array(
                    'field' => 'CmpSubstation_id',
                    'label' => 'Идентификатор подстанции СМП',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'Lpu_uid',
                    'label' => 'Идентификатор МО',
                    'rules' => 'required',
                    'type' => 'id'
                ),
                array(
                    'field' => 'LpuBuilding_id',
                    'label' => 'Идентификатор продразделения',
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
                ),
                array(
                    'field' => 'CmpSubstation_Code',
                    'label' => 'Код подстанции СМП',
                    'rules' => 'required',
                    'type' => 'string'
                ),
                array(
                    'field' => 'CmpSubstation_Name',
                    'label' => 'Наименование подстанции СМП',
                    'rules' => 'required',
                    'type' => 'string'
                ),
                array(
                    'field' => 'LpuStructure_id',
                    'label' => 'Уровень структуры',
                    'rules' => 'required',
                    'type' => 'string'
                ),
                array(
                    'field' => 'CmpEmergencyTeamData',
                    'label' => 'Данные бригад СМП',
                    'rules' => '',
                    'type' => 'string'
                ),
				array(
                    'field' => 'CmpStationCategory_id',
                    'label' => 'Категория станции',
                    'rules' => '',
                    'type' => 'string'
                ),
				array(
                    'field' => 'CMPSubstation_IsACS',
                    'label' => 'Оснащена АСУ приема и обработки вызова',
                    'rules' => '',
                    'type' => 'string'
                )
            ),
            'deleteCmpSubstation' => array(
                array(
                    'field' => 'CmpSubstation_id',
                    'label' => 'Идентификатор подстанции СМП',
                    'rules' => 'required',
                    'type' => 'int'
                )
            ),
            'loadCmpSubstationGrid' => array(
                array(
                    'field' => 'CmpSubstation_id',
                    'label' => 'Идентификатор подстанции СМП',
                    'rules' => '',
                    'type' => 'int'
                ),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'int'
				)
            ),
            'loadCmpSubstationForm' => array(
                array(
                    'field' => 'CmpSubstation_id',
                    'label' => 'Идентификатор подстанции СМП',
                    'rules' => 'required',
                    'type' => 'int'
                )
            ),
            'loadCmpEmergencyTeamGrid' => array(
                array(
                    'field' => 'CmpSubstation_id',
                    'label' => 'Идентификатор подстанции СМП',
                    'rules' => 'required',
                    'type' => 'int'
                )
            ),
            'saveMOAreaObject' => array(
                array(
                    'field' => 'Lpu_id',
                    'label' => 'Идентификатор МО',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'MOAreaObject_id',
                    'label' => 'Идентификатор oбъекта инфраструктуры, находящегося на территории',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'DObjInfrastructure_id',
                    'label' => 'Наименование объекта',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'MOAreaObject_Count',
                    'label' => 'Количество объектов',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'MOAreaObject_Member',
                    'label' => 'Идентификатор участка',
                    'rules' => '',
                    'type' => 'string'
                )
            ),
            'loadMOAreaObject' => array(
                array(
                    'field' => 'Lpu_id',
                    'label' => 'Идентификатор МО',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'MOAreaObject_id',
                    'label' => 'Идентификатор oбъектов инфраструктуры, находящихся на территории',
                    'rules' => '',
                    'type' => 'id'
                )
            ),
            'saveMOInfoSys' => array(
                array(
                    'field' => 'Lpu_id',
                    'label' => 'Идентификатор МО',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'MOInfoSys_id',
                    'label' => 'Идентификатор информационной системы',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'MOInfoSys_Name',
                    'label' => 'Наименование ИС',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'DInfSys_id',
                    'label' => 'Тип ИС',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'MOInfoSys_Cost',
                    'label' => 'Стоимость ИС, р.',
                    'rules' => '',
                    'type' => 'float'
                ),
                array(
                    'field' => 'MOInfoSys_CostYear',
                    'label' => 'Стоимость сопровождения ИС в год, р.',
                    'rules' => '',
                    'type' => 'float'
                ),
                array(
                    'field' => 'MOInfoSys_IntroDT',
                    'label' => 'Дата внедрения',
                    'rules' => '',
                    'type' => 'date'
                ),
                array(
                    'field' => 'MOInfoSys_IsMainten',
                    'label' => 'Признак сопровождения',
                    'rules' => '',
                    'type' => 'swcheckbox'
                ),
                array(
                    'field' => 'MOInfoSys_NameDeveloper',
                    'label' => 'Наименование разработчика',
                    'rules' => '',
                    'type' => 'string'
                )
            ),
            'loadMOInfoSys' => array(
                array(
                    'field' => 'Lpu_id',
                    'label' => 'Идентификатор МО',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'MOInfoSys_id',
                    'label' => 'Идентификатор информационной системы',
                    'rules' => '',
                    'type' => 'int'
                )
            ),
            'saveMedUsluga' => array(
                array(
                    'field' => 'Lpu_id',
                    'label' => 'Идентификатор МО',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'MedUsluga_id',
                    'label' => 'Идентификатор услуги',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'DUslugi_id',
                    'label' => 'Наименование услуги',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'MedUsluga_LicenseNum',
                    'label' => 'Номер лицензии',
                    'rules' => '',
                    'type' => 'string'
                )
            ),
            'loadMedUsluga' => array(
                array(
                    'field' => 'Lpu_id',
                    'label' => 'Идентификатор МО',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'MedUsluga_id',
                    'label' => 'Идентификатор информационной системы',
                    'rules' => '',
                    'type' => 'id'
                )
            ),
            'loadLpuLicenceSpecializationMO' => array(
                array(
                    'field' => 'Lpu_id',
                    'label' => 'Идентификатор МО',
                    'rules' => '',
                    'type' => 'int'
                )
            ),
            'loadLpuBuildingMedTechnology' => array(
                array(
                    'field' => 'Lpu_id',
                    'label' => 'Идентификатор МО',
                    'rules' => '',
                    'type' => 'int'
                )
            ),
            'calcWorkAreaWard' => array(
                array(
                    'field' => 'deniedSectionsList',
                    'label' => 'Список отделений, для который расчитывается площадь связанных палат',
                    'rules' => 'required',
                    'type' => 'string'
                )
            ),
            'saveMedTechnology' => array(
                array(
                    'field' => 'Lpu_id',
                    'label' => 'Идентификатор МО',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'MedTechnology_id',
                    'label' => 'Идентификатор медицинской технологии',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'MedTechnology_Name',
                    'label' => 'Наименование медицинской технологии',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'TechnologyClass_id',
                    'label' => 'Класс технологии',
                    'rules' => 'required',
                    'type' => 'id'
                ),
                array(
                    'field' => 'LpuBuildingPass_id',
                    'label' => 'Идентификатор здания',
                    'rules' => '',
                    'type' => 'int'
                )
            ),
            'loadMedTechnology' => array(
                array(
                    'field' => 'Lpu_id',
                    'label' => 'Идентификатор МО',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'MedTechnology_id',
                    'label' => 'Идентификатор медицинской технологии',
                    'rules' => '',
                    'type' => 'id'
                )
            ),
            'savePitanFormTypeLink' => array(
                array(
                    'field' => 'Lpu_id',
                    'label' => 'Идентификатор МО',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'PitanFormTypeLink_id',
                    'label' => 'Идентификатор питания',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'VidPitan_id',
                    'label' => 'Вид питания',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'PitanCnt_id',
                    'label' => 'Кратность питания',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'PitanForm_id',
                    'label' => 'Форма питания',
                    'rules' => '',
                    'type' => 'int'
                )
            ),
            'loadPitanFormTypeLink' => array(
                array(
                    'field' => 'Lpu_id',
                    'label' => 'Идентификатор МО',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'PitanFormTypeLink_id',
                    'label' => 'Идентификатор питания',
                    'rules' => '',
                    'type' => 'id'
                )
            ),
            'saveSpecializationMO' => array(
                array(
                    'field' => 'Lpu_id',
                    'label' => 'Идентификатор МО',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'SpecializationMO_id',
                    'label' => 'Идентификатор специализации организации',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Mkb10Code_id',
                    'label' => 'Код МКБ-10',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'Mkb10CodeClass_id',
                    'label' => 'Класс МКБ-10',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'SpecializationMO_MedProfile',
                    'label' => 'Медицинский профиль',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'LpuLicence_id',
                    'label' => 'Номер лицензии',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'SpecializationMO_IsDepAftercare',
                    'label' => 'Наличие отделения долечивания',
                    'rules' => '',
                    'type' => 'swcheckbox'
                )
            ),
            'loadSpecializationMO' => array(
                array(
                    'field' => 'Lpu_id',
                    'label' => 'Идентификатор МО',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'SpecializationMO_id',
                    'label' => 'Идентификатор специализации организации',
                    'rules' => '',
                    'type' => 'id'
                )
            ),
            'savePlfDocTypeLink' => array(
                array(
                    'field' => 'Lpu_id',
                    'label' => 'Идентификатор МО',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'PlfDocTypeLink_id',
                    'label' => 'Идентификатор природного лечебного фактора',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Plf_id',
                    'label' => 'Наименование фактора',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'PlfDocTypeLink_Num',
                    'label' => 'Номер документа',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'PlfType_id',
                    'label' => 'Тип фактора',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'DocTypeUsePlf_id',
                    'label' => 'Документ',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'PlfDocTypeLink_GetDT',
                    'label' => 'Дата выдачи документа',
                    'rules' => '',
                    'type' => 'date'
                ),
                array(
                    'field' => 'PlfDocTypeLink_BegDT',
                    'label' => 'Дата начала действия фактора',
                    'rules' => '',
                    'type' => 'date'
                ),
                array(
                    'field' => 'PlfDocTypeLink_EndDT',
                    'label' => 'Дата окончания действия фактора',
                    'rules' => '',
                    'type' => 'date'
                )
            ),
            'loadPlfDocTypeLink' => array(
                array(
                    'field' => 'Lpu_id',
                    'label' => 'Идентификатор МО',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'PlfDocTypeLink_id',
                    'label' => 'Идентификатор природного лечебного фактора',
                    'rules' => '',
                    'type' => 'id'
                )
            ),
            'savePlfObjectCount' => array(
                array(
                    'field' => 'Lpu_id',
                    'label' => 'Идентификатор МО',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'PlfObjectCount_id',
                    'label' => 'Идентификатор объекта/места использования природных лечебных факторов',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'PlfObjects_id',
                    'label' => 'Наименование объекта',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'PlfObjectCount_Count',
                    'label' => 'Количество объектов по использованию',
                    'rules' => '',
                    'type' => 'int'
                )
            ),
            'loadPlfObjectCount' => array(
                array(
                    'field' => 'Lpu_id',
                    'label' => 'Идентификатор МО',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'PlfObjectCount_id',
                    'label' => 'Идентификатор объекта/места использования природных лечебных факторов',
                    'rules' => '',
                    'type' => 'id'
                )
            ),
            'saveLpuLicenceOperationLink' => array(
                array(
                    'field' => 'LpuLicence_id',
                    'label' => 'Идентификатор лицензии МО',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'LpuLicenceOperationLink_id',
                    'label' => 'Идентификатор операции с лицензией',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'LicsOperation_id',
                    'label' => 'Наименование операции',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'LpuLicenceOperationLink_Date',
                    'label' => 'Дата операции',
                    'rules' => 'required',
                    'type' => 'date'
                )
            ),
            'saveLpuLicenceDop' => array(
                array(
                    'field' => 'LpuLicence_id',
                    'label' => 'Идентификатор лицензии МО',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'LpuLicenceDop_id',
                    'label' => 'Идентификатор приложения к лицензии',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'LpuLicenceDop_Num',
                    'label' => 'Номер приложения',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'LpuLicenceDop_setDate',
                    'label' => 'Дата приложения',
                    'rules' => '',
                    'type' => 'date'
                )
            ),
            'saveLpuLicenceProfile' => array(
                array(
                    'field' => 'LpuLicence_id',
                    'label' => 'Идентификатор лицензии МО',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'LpuLicenceProfile_id',
                    'label' => 'Идентификатор вида лицензии по профилю',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'LpuLicenceProfileType_id',
                    'label' => 'Вид лицензии',
                    'rules' => '',
                    'type' => 'int'
                )
            ),
            'loadUslugaComplexLpu' => array(
                array(
                    'field' => 'Lpu_id',
                    'label' => 'Идентификатор МО',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'UslugaComplexLpu_id',
                    'label' => 'Идентификатор направления оказания медицинской помощи',
                    'rules' => '',
                    'type' => 'id'
                )
            ),
            'saveUslugaComplexLpu' => array(
                array(
                    'field' => 'UslugaComplexLpu_id',
                    'label' => 'Идентификатор направления оказания медицинской помощи',
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
                    'field' => 'UslugaComplex_id',
                    'label' => 'Идентификатор услуги ГОСТ',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'UslugaComplexLpu_begDate',
                    'label' => 'Дата начала оказания услуги',
                    'rules' => '',
                    'type' => 'date'
                ),
                array(
                    'field' => 'UslugaComplexLpu_endDate',
                    'label' => 'Дата окончания оказания услуги',
                    'rules' => '',
                    'type' => 'date'
                )
            ),
            'loadLpuLicenceOperationLink' => array(
                array(
                    'field' => 'LpuLicence_id',
                    'label' => 'Идентификатор лицензии МО',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'LpuLicenceOperationLink_id',
                    'label' => 'Идентификатор операции с лицензией',
                    'rules' => '',
                    'type' => 'id'
                )
            ),
            'loadLpuLicenceLink' => array(
                array(
                    'field' => 'LpuLicence_id',
                    'label' => 'Идентификатор лицензии МО',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'LpuLicenceLink_id',
                    'label' => 'Идентификатор профиля лицензии',
                    'rules' => '',
                    'type' => 'id'
                )
            ),
            'loadLpuLicenceDop' => array(
                array(
                    'field' => 'LpuLicence_id',
                    'label' => 'Идентификатор лицензии МО',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'LpuLicenceOperationLink_id',
                    'label' => 'Идентификатор приложения к лицензии',
                    'rules' => '',
                    'type' => 'id'
                )
            ),
            'loadConsumables' => array(
                array(
                    'field' => 'Consumables_id',
                    'label' => 'Идентификатор расходного материала',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'MedProductCard_id',
                    'label' => 'Идентификатор карты изделия',
                    'rules' => 'required',
                    'type' => 'id'
                )
            ),
            'loadDowntime' => array(
                array(
                    'field' => 'Downtime_id',
                    'label' => 'Идентификатор прчины простоя',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'MedProductCard_id',
                    'label' => 'Идентификатор карты изделия',
                    'rules' => 'required',
                    'type' => 'id'
                )
            ),
            'loadMeasureFundCheck' => array(
                array(
                    'field' => 'MeasureFundCheck_id',
                    'label' => 'Идентификатор свидетельства поверки',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'MedProductCard_id',
                    'label' => 'Идентификатор карты изделия',
                    'rules' => 'required',
                    'type' => 'id'
                )
            ),
            'loadWorkData' => array(
                array(
                    'field' => 'WorkData_id',
                    'label' => 'Идентификатор эксплуатационных данных',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'MedProductCard_id',
                    'label' => 'Идентификатор карты изделия',
                    'rules' => 'required',
                    'type' => 'id'
                )
            ),
            'loadMedProductCardData' => array(
                array(
                    'field' => 'MedProductCard_id',
                    'label' => 'Идентификатор карты изделия',
                    'rules' => 'required',
                    'type' => 'id'
                )
            ),
            'loadAmortization' => array(
                array(
                    'field' => 'Amortization_id',
                    'label' => 'Идентификатор оценки износа',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'MedProductCard_id',
                    'label' => 'Идентификатор карты изделия',
                    'rules' => 'required',
                    'type' => 'id'
                )
            ),
            'loadLpuLicenceProfile' => array(
                array(
                    'field' => 'LpuLicence_id',
                    'label' => 'Идентификатор лицензии МО',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'LpuLicenceProfile_id',
                    'label' => 'Идентификатор вида лицензии',
                    'rules' => '',
                    'type' => 'id'
                )
            ),
            'saveDisSanProtection' => array(
                array(
                    'field' => 'Lpu_id',
                    'label' => 'Идентификатор МО',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'DisSanProtection_id',
                    'label' => 'Идентификатор округа горно-санитарной охраны',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'DisSanProtection_IsProtection',
                    'label' => 'Признак наличия округа',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'DisSanProtection_Doc',
                    'label' => 'Документ',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'DisSanProtection_Num',
                    'label' => 'Номер документа',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'DisSanProtection_Date',
                    'label' => 'Дата документа',
                    'rules' => '',
                    'type' => 'date'
                )
            ),
            'saveMedProductCard' => array(
                array(
                    'field' => 'Lpu_id',
                    'label' => 'Идентификатор МО',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'MedProductCard_AvgProcTime',
                    'label' => 'Средняя длительность процедуры',
                    'rules' => '',
                    'type' => 'float'
                ),
                array(
                    'field' => 'MedProductCard_SetResource',
                    'label' => 'Установленный/назначенный ресурс',
                    'rules' => '',
                    'type' => 'float'
                ),
                array(
                    'field' => 'MedProductCard_DocumentTO',
                    'label' => 'Документ подтверждающий прохождение ТО',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'MedProductCard_IsOrgLic',
                    'label' => 'Наличие лицензии на проведение ТО у организации',
                    'rules' => '',
                    'type' => 'checkbox'
                ),
                array(
                    'field' => 'MedProductCard_IsLpuLic',
                    'label' => 'Наличие лицензии на проведение ТО у МО',
                    'rules' => '',
                    'type' => 'checkbox'
                ),
				array(
                    'field' => 'MedProductCard_IsOutsorc',
                    'label' => 'По договору аутсорсинга',
                    'rules' => '',
                    'type' => 'checkbox'
                ),
                array(
                    'field' => 'Org_toid',
                    'label' => 'Организация, выполняющая техническое обслуживание',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'MedProductCard_IsContractTO',
                    'label' => 'Наличие договора на техническое обслуживание',
                    'rules' => '',
                    'type' => 'checkbox'
                ),
                array(
                    'field' => 'MedProductCard_RepairDate',
                    'label' => 'Дата установки статуса Требует ремонта',
                    'rules' => '',
                    'type' => 'date'
                ),
                array(
                    'field' => 'MedProductCard_SpisanDate',
                    'label' => 'Дата установки статуса Требует списания',
                    'rules' => '',
                    'type' => 'date'
                ),
                array(
                    'field' => 'MedProductClass_id',
                    'label' => 'Класс медицинского изделия',
                    'rules' => 'required',
                    'type' => 'id'
                ),
				array(
					'field' => 'PropertyType_id',
					'label' => 'Отношение к собственности',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Подразделение',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'LpuUnit_id',
					'label' => 'Отделение',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'LpuBuilding_id',
					'label' => 'Здание',
					'rules' => 'trim',
					'type' => 'int'
				),
                array(
                    'field' => 'Downtime_KolvoStudy',
                    'label' => 'Кол-во выполненных процедур (исследований) с начала года',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Downtime_KolvoHours',
                    'label' => 'Количество часов простоя с начала года (в днях)',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Downtime_Cause',
                    'label' => 'Причина простоя',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Downtime_Comment',
                    'label' => 'Комментарий простоя',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'AmortizationGridData',
                    'label' => 'Данные из грида износа',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'ConsumablesGridData',
                    'label' => 'Данные из грида расходных материалов',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'WorkDataGridData',
                    'label' => 'Данные из грида эксплуатационных данных',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'DowntimeGridData',
                    'label' => 'Данные из грида простоев МИ',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'MeasureFundCheckGridData',
                    'label' => 'Данные из грида проверок средств измерения',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'OkeiLink_id',
                    'label' => 'Ед. изм.',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'DeliveryType_id',
                    'label' => 'Тип поставки',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'FinancingType_id',
                    'label' => 'Программа закупки',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'GosContract_setDate',
                    'label' => 'Дата заключения контракта',
                    'rules' => '',
                    'type' => 'date'
                ),
                array(
                    'field' => 'GosContract_Number',
                    'label' => 'Номер гос. контракта',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'MeasureFund_AccuracyClass',
                    'label' => 'Класс точности средств измерения',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'MeasureFund_RegNumber',
                    'label' => 'Регистрационный номер средства измерения',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'MeasureFund_IsMeasure',
                    'label' => 'Является средством измерения',
                    'rules' => '',
                    'type' => 'checkbox'
                ),
                array(
                    'field' => 'MeasureFund_Range',
                    'label' => 'Диапазон измерений',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Org_decid',
                    'label' => 'Декларант',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Org_prid',
                    'label' => 'Производитель',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Org_regid',
                    'label' => 'Держатель удостоверения',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'RegCertificate_MedProductName',
                    'label' => 'Наименование МИ по регистрационным документам',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'RegCertificate_OrderNumber',
                    'label' => 'Номер приказа',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'RegCertificate_Number',
                    'label' => 'Номер регистрационного удостовреения',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'RegCertificate_setDate',
                    'label' => 'Дата регистрационного удостовреения',
                    'rules' => '',
                    'type' => 'date'
                ),
                array(
                    'field' => 'RegCertificate_endDate',
                    'label' => 'Срок действия рег. удостоверения',
                    'rules' => '',
                    'type' => 'date'
                ),
                array(
                    'field' => 'AccountingData_ProductCost',
                    'label' => 'Цена производителя',
                    'rules' => '',
                    'type' => 'float'
                ),
                array(
                    'field' => 'AccountingData_BuyCost',
                    'label' => 'Стоимость приобретения',
                    'rules' => '',
                    'type' => 'float'
                ),
                array(
                    'field' => 'AccountingData_endDate',
                    'label' => 'Дата снятия с учёта',
                    'rules' => '',
                    'type' => 'date'
                ),
                array(
                    'field' => 'AccountingData_begDate',
                    'label' => 'Дата принятия на учёт',
                    'rules' => '',
                    'type' => 'date'
                ),
                array(
                    'field' => 'AccountingData_setDate',
                    'label' => 'Дата ввода в эксплуатацию',
                    'rules' => '',
                    'type' => 'date'
                ),
                array(
                    'field' => 'AccountingData_buyDate',
                    'label' => 'Дата приобретения',
                    'rules' => '',
                    'type' => 'date'
                ),
                array(
                    'field' => 'AccountingData_RegNumber',
                    'label' => 'Регистрационный знак (для автомобилей)',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'AccountingData_InventNumber',
                    'label' => 'Инвентарный номер',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'LpuBuilding_id',
                    'label' => 'Подразделение',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'MedProductCard_OtherParam',
                    'label' => 'Прочие параметры',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'MedProductCard_Options',
                    'label' => 'Комплектация',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'MedProductCard_SerialNumber',
                    'label' => 'Серийный номер',
                    'rules' => '',
                    'type' => 'string'
                ),
				array(
                    'field' => 'MedProductCard_BoardNumber',
                    'label' => 'Бортовой номер',
                    'rules' => '',
                    'type' => 'string'
                ),
				array(
                    'field' => 'MedProductCard_Phone',
                    'label' => 'Телефон',
                    'rules' => '',
                    'type' => 'string'
                ),
				array(
                    'field' => 'MedProductCard_Glonass',
                    'label' => 'GPS/Glonass',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'MedProductCard_UsePeriod',
                    'label' => 'Срок использования',
                    'rules' => '',
                    'type' => 'int'
				),
				array(
                    'field' => 'MedProductCard_UsePeriod_Check',
                    'label' => 'Срок использования - бессрочно',
                    'rules' => '',
                    'type' => 'checkbox'
                ),
                array(
                    'field' => 'PrincipleWorkType_id',
                    'label' => 'Принцип работы',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'MedProductCard_IsWorkList',
                    'label' => 'Работа с рабочим списком',
                    'rules' => '',
                    'type' => 'checkbox'
                ),
                array(
                    'field' => 'MedProductCard_AETitle',
                    'label' => 'AE Title',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'LpuEquipmentPacs_id',
                    'label' => 'PACS',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'MedProductCard_begDate',
                    'label' => 'Дата выпуска',
                    'rules' => 'required',
                    'type' => 'date'
                ),
                array(
                    'field' => 'OKPDType_id',
                    'label' => 'ОКПД',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'OKPType_id',
                    'label' => 'ОКП',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'OKOFType_id',
                    'label' => 'ОКОФ',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'MT97Type_id',
                    'label' => 'МТ по 97пр',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'GMDNType_id',
                    'label' => 'GMDN',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'TNDEDType_id',
                    'label' => 'ТН ВЭД',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'FZ30Type_id',
                    'label' => '30й ФЗ',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'UseSphereType_id',
                    'label' => 'Сфера применения',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'UseAreaType_id',
                    'label' => 'Область применения',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'CardType_id',
                    'label' => 'Тип медицинского изделия',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'FuncPurpType_id',
                    'label' => 'Функциональное назначение',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'ClassRiskType_id',
                    'label' => 'Класс потенциального риска применения',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'MedProductCard_id',
                    'label' => 'Идентификатор карточки медицинского изделия',
                    'rules' => '',
                    'type' => 'id'
                ),
				array(
					'field' => 'MedProductCard_Name',
					'label' => 'Наименование МИ',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'MedProductCard_Model',
					'label' => 'Модель МИ',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'MedProductCard_IsRepair',
					'label' => 'Требует ремонта',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'MedProductCard_IsSpisan',
					'label' => 'Требует списания',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'Org_id',
					'label' => 'Поставщик',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedProductCard_IsEducatAct',
					'label' => 'Наличие акта об обучении мед. персонала работе на ми',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'MedProductCard_IsNoAvailLpu',
					'label' => 'Недоступна для МО',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'MedProductCard_IsAvailibleSpecialists',
					'label' => 'Наличие специалистов для работы на указанном оборудовании» ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedProductCard_IsClockMode',
					'label' => 'Работа в круглосуточном режиме',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedProductClassForm_secid',
					'label' => 'Раздел формы 30',
					'rules' => '',
					'type' => 'id'
				),
                array(
					'field' => 'MedProductClassForm_strid',
					'label' => 'Строка формы 30',
					'rules' => '',
					'type' => 'id'
				),
                array(
					'field' => 'MedProductClassForm_fsubid',
					'label' => 'Подстрока 1 формы 30',
					'rules' => '',
					'type' => 'id'
				),
                array(
					'field' => 'MedProductClassForm_ssubid',
					'label' => 'Подстрока 2 формы 30',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedProductCard_IsEducatAct',
					'label' => 'Наличие акта об обучении мед. персонала работе на ми',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'FRMOEquipment_id',
					'label' => 'ФРМО. Перечень аппаратов и оборудования отделений (кабинетов) медицинской организации',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedProductCard_IsNotFRMO',
					'label' => 'Чекбокс. Не передавать на ФРМО',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'MedProductCauseType_id',
					'label' => 'Идентификатор причины снятия с учета',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedProductCard_Cause',
					'label' => 'Причина снятия с учета',
					'rules' => '',
					'type' => 'string'
				),
            ),
			'saveLpuLicence' => array(
				array(
					'field' => 'LpuLicence_id',
					'label' => 'Идентификатор лицензии',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuLicence_Ser',
					'label' => 'Серия лицензии',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'LpuLicence_Num',
					'label' => 'Номер лицензии',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Org_id',
					'label' => 'Выдавшая огранизация',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'LpuLicence_setDate',
					'label' => 'Дата выдачи',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'LpuLicence_RegNum',
					'label' => 'Регистрационный номер',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'VidDeat_id',
					'label' => 'Вид деятельности',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'LpuLicence_begDate',
					'label' => 'Начало действия',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'LpuLicence_endDate',
					'label' => 'Окончание действия',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'KLAreaStat_id',
					'label' => 'Территория',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'KLCountry_id',
					'label' => 'Страна',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'KLRgn_id',
					'label' => 'Регион',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'KLSubRgn_id',
					'label' => 'Район',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'KLCity_id',
					'label' => 'Город',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'KLTown_id',
					'label' => 'Населенный пункт',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'LpuLicenceOperationLinkData',
					'label' => 'Операции над лицензией',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'LpuLicenceDopData',
					'label' => 'Приложения к лицензиям',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'LpuLicenceProfileData',
					'label' => 'Виды лицензии',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'LpuLicenceLinkData',
					'label' => 'Профили лицензии',
					'rules' => '',
					'type' => 'string'
				)
			),
			'saveLpuTransport' => array(
				array(
					'field' => 'LpuTransport_id',
					'label' => 'Идентификатор транспорта',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuTransport_Name',
					'label' => 'Наименование',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'LpuTransport_Producer',
					'label' => 'Производитель',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'LpuTransport_Model',
					'label' => 'Модель',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'LpuTransport_ReleaseDT',
					'label' => 'Дата выпуска',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'LpuTransport_PurchaseDT',
					'label' => 'Дата приобретения',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'LpuTransport_Supplier',
					'label' => 'Поставщик',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'LpuTransport_RegNum',
					'label' => 'Регистрационный номер',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'LpuTransport_EngineNum',
					'label' => 'Номер двигателя',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'LpuTransport_BodyNum',
					'label' => 'Номер кузова',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'LpuTransport_ChassiNum',
					'label' => 'Номер шасси',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'LpuTransport_StartUpDT',
					'label' => 'Дата ввода в эксплуатацию',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'LpuTransport_WearPersent',
					'label' => '% износа',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'LpuTransport_PurchaseCost',
					'label' => 'Стоимость приобретения',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'LpuTransport_ResidualCost',
					'label' => 'Остаточная стоимость',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'LpuTransport_ValuationDT',
					'label' => 'Дата оценки стоимости',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'LpuTransport_IsNationProj',
					'label' => 'Поставлен по нац. Проекту',
					'rules' => 'trim',
					'type' => 'int'
				),
			),
			'saveLpuEquipment' => array(
				array(
					'field' => 'LpuEquipment_id',
					'label' => 'Идентификатор оборудования',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuEquipmentType_id',
					'label' => 'Тип оборудования',
					'rules' => 'required|trim',
					'type' => 'int'
				),
				array(
					'field' => 'LpuEquipment_Name',
					'label' => 'Наименование',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'LpuEquipment_Producer',
					'label' => 'Производитель',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'LpuEquipment_ReleaseDT',
					'label' => 'Дата выпуска',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'LpuEquipment_PurchaseDT',
					'label' => 'Дата приобретения',
					'rules' => 'trim',
					'type' => 'date'
				),
			    array(
					'field' => 'LpuEquipment_Model',
					'label' => 'Модель',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'LpuEquipment_InvNum',
					'label' => 'Инвентарный номер',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'LpuEquipment_SerNum',
					'label' => 'Серийный номер',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'LpuEquipment_StartUpDT',
					'label' => 'Дата ввода в эксплуатацию',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'LpuEquipment_WearPersent',
					'label' => '% износа',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'LpuEquipment_ConclusionDT',
					'label' => 'Дата заключения о пригодности аппарата',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'LpuEquipment_PurchaseCost',
					'label' => 'Стоимость приобретения',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'LpuEquipment_ResidualCost',
					'label' => 'Остаточная стоимость',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'LpuEquipment_IsNationProj',
					'label' => 'Поставлен по нац. Проекту',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'LpuEquipment_AmortizationTerm',
					'label' => 'Срок амортизации',
					'rules' => 'trim',
					'type' => 'int'
				),

				array(
					'field' => 'LpuEquipmentPacs_id',
					'label' => 'Идентификатор PACS оборудования',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'PACS_name',
					'label' => 'Наименование',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'PACS_ip_local',
					'label' => 'ip-адрес локальный',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'PACS_ip_vip',
					'label' => 'ip-адрес VipNET',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'PACS_aet',
					'label' => 'AETittle',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'PACS_port',
					'label' => 'TCP/IP port',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'PACS_wado',
					'label' => 'Порт WADO',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'PACS_Interval',
					'label' => 'Интервал: значение',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'PACS_Interval_TimeType_id',
					'label' => 'Интервал: ед',
					'rules' => '',
					'type' => 'id'
				)
				,array(
					'field' => 'PACS_ExcludeTimeFrom',
					'label' => 'Крон интервал от',
					'rules' => 'trim',
					'type' => 'int'
				)
				,array(
					'field' => 'PACS_ExcludeTimeTo',
					'label' => 'интервал до',
					'rules' => 'trim',
					'type' => 'int'
				)
				,array(
					'field' => 'PACS_CronExpression',
					'label' => 'интервал',
					'rules' => 'trim',
					'type' => 'string'
				)
				,array(
					'field' => 'LpuPacsCompressionType_id',
					'label' => 'Тип компрессии',
					'rules' => '',
					'type' => 'id'
				)
				,array(
					'field' => 'PACS_StudyAge',
					'label' => 'Возраст: значение',
					'rules' => 'trim',
					'type' => 'int'
				)
				,array(
					'field' => 'PACS_Age_TimeType_id',
					'label' => 'Возраст: ед',
					'rules' => '',
					'type' => 'id'
				)
				,array(
					'field' => 'PACS_DeleteFromDb',
					'label' => 'Удалять из БД',
					'rules' => 'trim',
					'type' => 'string'
				)
				,array(
					'field' => 'PACS_DeletePatientsWithoutStudies',
					'label' => 'Удалять пациентов без иссдедований',
					'rules' => 'trim',
					'type' => 'string'
				)
				,array(
					'field' => 'PACS_CronRequests',
					'label' => 'CRON запросы',
					'rules' => 'trim',
					'type' => 'string'
				)
			),
			'saveLpuQuote' => array(
				array(
					'field' => 'LpuQuote_id',
					'label' => 'Идентификатор квоты',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'PayType_id',
					'label' => 'Вид оплаты',
					'rules' => 'trim|required',
					'type' => 'int'
				),
				array(
					'field' => 'LpuQuote_HospCount',
					'label' => 'Кол-во госпитализаций',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'LpuQuote_BedDaysCount',
					'label' => 'Кол-во койко-дней',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'LpuQuote_VizitCount',
					'label' => 'Кол-во посещений',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'LpuQuote_begDate',
					'label' => 'Начало',
					'rules' => 'trim|required',
					'type' => 'date'
				),
				array(
					'field' => 'LpuQuote_endDate',
					'label' => 'Окончание',
					'rules' => 'trim',
					'type' => 'date'
				),
			),
			'loadCronRequests'=>array(
				array(
					'field' => 'LpuEquipmentPacs_id',
					'label' => 'Идентификатор устройства PACS',
					'rules' => 'trim',
					'type' => 'id'
				),
			),
			'saveMedProductClass'=>array(
				array( 'field' => 'Lpu_id', 'label' => 'Иидентификатор МО', 'rules' => 'required', 'type' => 'id'),
				array( 'field' => 'MedProductClass_id', 'label' => 'Иидентификатор МИ', 'rules' => '', 'type' => 'id'),
				array( 'field' => 'MedProductClass_Name', 'label' => 'Наименование МИ', 'rules' => 'required', 'type' => 'string'),
				array( 'field' => 'MedProductClass_Model', 'label' => 'Модель МИ', 'rules' => 'required', 'type' => 'string'),
				array( 'field' => 'CardType_id', 'label' => 'Иденитфикатор типа мединциского изделия', 'rules' => 'required', 'type' => 'id'),
				array( 'field' => 'FRMOEquipment_id', 'label' => 'ФРМО. Идентификатор аппаратов и оборудования отделений (кабинетов) медицинской организации', 'rules' => '', 'type' => 'id'),
				array( 'field' => 'FRMOEquipment_Name', 'label' => 'ФРМО. Наименование аппаратов и оборудования отделений (кабинетов) медицинской организации', 'rules' => '', 'type' => 'string'),
				array( 'field' => 'ClassRiskType_id', 'label' => 'Идентификатор класса потенциального риска', 'rules' => 'required', 'type' => 'id'),
				array( 'field' => 'FuncPurpType_id', 'label' => 'Идентификатор функционального знчаения', 'rules' => 'required', 'type' => 'id'),
				array( 'field' => 'FZ30Type_id', 'label' => 'Идентификатор справочника 30й ФЗ', 'rules' => '', 'type' => 'id'),
				array( 'field' => 'GMDNType_id', 'label' => 'Идентификатор справочника GMDN', 'rules' => '', 'type' => 'id'),
				array( 'field' => 'MT97Type_id', 'label' => 'Идентификатор классификатора МТ по 97 приказу', 'rules' => '', 'type' => 'id'),
				array( 'field' => 'OKOFType_id', 'label' => 'Идентификатор справочника ОКОФ оборудования', 'rules' => '', 'type' => 'id'),
				array( 'field' => 'OKPType_id', 'label' => 'Идентификатор справочника ОКП оборудования', 'rules' => '', 'type' => 'id'),
				array( 'field' => 'OKPDType_id', 'label' => 'Идентификатор справочника ОКПД оборудования', 'rules' => '', 'type' => 'id'),
				array( 'field' => 'TNDEDType_id', 'label' => 'Идентификатор справочника ТН ВЭД', 'rules' => '', 'type' => 'id'),
				array( 'field' => 'UseAreaType_id', 'label' => 'Идентификатор области медицинского применения', 'rules' => 'required', 'type' => 'id'),
				array( 'field' => 'UseSphereType_id', 'label' => 'Идентификатор сферы применения', 'rules' => 'required', 'type' => 'id'),
				array( 'field' => 'MedProductType_id', 'label' => 'Идентификатор вида медицинского изделия', 'rules' => '', 'type' => 'id'),
				array( 'field' => 'MedProductClass_IsAmbulNovor', 'label' => 'Флаг Реанимобиль для новорожденных и детей раннего возраста', 'rules' => '', 'type' => 'string'),
				array( 'field' => 'MedProductClass_IsAmbulTerr', 'label' => 'Флаг Реанимобиль повышенной проходимости', 'rules' => '', 'type' => 'string')
            ),
			'saveTransportConnect' => array(
				array( 'field' => 'TransportConnect_id', 'label' => 'идентификатор', 'rules' => '', 'type' => 'id'),
				array( 'field' => 'MOArea_id', 'label' => 'идентификатор площадки, занимаемая учреждением', 'rules' => '', 'type' => 'id'),
				array( 'field' => 'TransportConnect_AreaIdent', 'label' => 'Идентификатор участка ', 'rules' => '', 'type' => 'id'),
				array( 'field' => 'TransportConnect_Station', 'label' => 'Ближайшая станция', 'rules' => '', 'type' => 'string'),
				array( 'field' => 'TransportConnect_DisStation', 'label' => 'Расстояние до ближайшей станции (км)', 'rules' => '', 'type' => 'string'),
				array( 'field' => 'TransportConnect_Airport', 'label' => 'Ближайший аэропорт', 'rules' => '', 'type' => 'string'),
				array( 'field' => 'TransportConnect_DisAirport', 'label' => 'Расстояние до аэропорта (км)', 'rules' => '', 'type' => 'string'),
				array( 'field' => 'TransportConnect_Railway', 'label' => 'Ближайший автовокзал', 'rules' => '', 'type' => 'string'),
				array( 'field' => 'TransportConnect_DisRailway', 'label' => 'Расстояние до автовокзала (км)', 'rules' => '', 'type' => 'string'),
				array( 'field' => 'TransportConnect_Heliport', 'label' => 'Ближайшая вертолетная площадка', 'rules' => '', 'type' => 'string'),
				array( 'field' => 'TransportConnect_DisHeliport', 'label' => 'Расстояние до вертолетной площадки (км)', 'rules' => '', 'type' => 'string'),
				array( 'field' => 'TransportConnect_MainRoad', 'label' => 'Главная дорога', 'rules' => '', 'type' => 'string')

            ),
			'saveFunctionTime' => array(
				array( 'field' => 'FunctionTime_id', 'label' => 'Идентификатор периода функционирования', 'rules' => '', 'type' => 'id'),
				array( 'field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id'),
				array( 'field' => 'InstitutionFunction_id', 'label' => 'идентификатор период функционирования учреждения', 'rules' => 'required', 'type' => 'id'),
				array( 'field' => 'FunctionTime_begDate', 'label' => 'Дата начала периода', 'rules' => 'required', 'type' => 'date'),
				array( 'field' => 'FunctionTime_endDate', 'label' => 'Дата окончания периода', 'rules' => '', 'type' => 'date'),

            ),
			'loadOkeiLinkCombo' => array(

			),
			'exportLpuPassportReport' => array(
				array( 'field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'int')
			),
			'exportLpuPassportXml' => array(
				array( 'field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'int'),
				array( 'field' => 'exportType', 'label' => 'Вид выгрузки', 'rules' => 'required', 'type' => 'int'),
			),
			'loadOKATOList' => array(
				array(
					'field' => 'query',
					'label' => 'Строка поиска',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'OKATO_id',
					'label' => 'Идентификатор ОКАТО',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'OKATO_Code',
					'label' => 'Код ОКАТО',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'OKATO_Name',
					'label' => 'Наименование ОКАТО',
					'rules' => '',
					'type' => 'string'
				)
			),
			'loadLpuPeriodStom' => array(
				array(
					'field' => 'LpuPeriodStom_id',
					'label' => 'Идентификатор периода',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'int'
				)
			),
			'saveLpuPeriodStom' => array(
				array(
					'field' => 'LpuPeriodStom_id',
					'label' => 'Идентификатор периода',
					'rules' => 'required',
					'type' => 'int'
				),
				array( 'field' => 'LpuPeriodStom_begDate', 'label' => 'Дата начала периода', 'rules' => 'required', 'type' => 'date'),
				array( 'field' => 'LpuPeriodStom_endDate', 'label' => 'Дата окончания периода', 'rules' => '', 'type' => 'date')
			),
			'checkLpuStomLicenceDates' => array(),
			'checkMedProductCardHasClass' => array(
				array(
                    'field' => 'MedProductClass_id',
                    'label' => 'Класс медицинского изделия',
                    'rules' => 'required',
                    'type' => 'id'
				)
			),
			'getLpuTariffClassList' => array(
				array(
                    'field' => 'Lpu_oid',
                    'label' => 'Идентификатор МО',
                    'rules' => 'required',
                    'type' => 'id'
				),
				array(
					'field' => 'Date',
					'label' => 'Дата',
					'rules' => 'required',
					'type' => 'date'
				)
			),
			'checkOrgServiceTerr' => array(
				array(
					'field' => 'person_id',
					'label'	=> 'Идентификатор пациента',
					'rules'	=> 'required',
					'type'	=> 'int'
				),
				array(
					'field'	=> 'org_id',
					'label'	=> 'Идентификатор организации (МО)',
					'rules'	=> 'required',
					'type'	=> 'int'
				)
			),
			'loadLpuComputerEquipment' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'ComputerEquip_id',
					'label' => 'Идентификатор устройства',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'ComputerEquip_Year',
					'label' => 'Год',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Period_id',
					'label' => 'Период',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Device_id',
					'label' => 'id категории',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'ComputerEquip_UsageColumn',
					'label' => 'Цель использования',
					'rules' => '',
					'type' => 'string'
				)
			),
			'loadLpuComputerEquipmentDevices' => array(
				array(
					'field' => 'parent_id',
					'label' => 'Идентификатор_родителя',
					'rules' => '',
					'type' => 'int'
				)
			),
			'saveLpuComputerEquipment' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'ComputerEquip_id',
					'label' => 'Идентификатор оборудования',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Device_id',
					'label' => 'Идентификатор устройства',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Period_id',
					'label' => 'Идентификатор периода',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'ComputerEquip_Year',
					'label' => 'Год',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'ComputerEquip_DevCnt',
					'label' => 'Количество',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'ComputerEquip_MedPAmb',
					'label' => 'Для нужд МП амбулаторно',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'ComputerEquip_MedPStac',
					'label' => 'Для нужд МП в стационарах',
					'rules' => '',
					'type' => 'int'
				),array(
					'field' => 'ComputerEquip_AHDAmb',
					'label' => 'Для нужд АХД амбулаторно',
					'rules' => '',
					'type' => 'int'
				),array(
					'field' => 'ComputerEquip_AHDStac',
					'label' => 'Для нужд АХД в стационарах',
					'rules' => '',
					'type' => 'int'
				),array(
					'field' => 'ComputerEquip_MedStatCab',
					'label' => 'Для кабинетов медицинской статистики',
					'rules' => '',
					'type' => 'int'
				),array(
					'field' => 'ComputerEquip_other',
					'label' => 'Другие нужды',
					'rules' => '',
					'type' => 'int'
				)
		),
		'checkLpuComputerEquipmentUniqRecord' => array(
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор МО',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Device_id',
				'label' => 'Идентификатор устройства',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Period_id',
				'label' => 'Идентификатор периода',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ComputerEquip_Year',
				'label' => 'Год',
				'rules' => '',
				'type' => 'int'
			)
		),'deleteLpuComputerEquipment' => array(
			array(
				'field' => 'ComputerEquip_id',
				'label' => 'Идентификатор оборудования',
				'rules' => '',
				'type' => 'id'
			),
		),'checkLpuComputerEquipmentChildDeviceUsage' => array(
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор МО',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Device_id',
				'label' => 'Идентификатор устройства',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Period_id',
				'label' => 'Идентификатор периода',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ComputerEquip_Year',
				'label' => 'Год',
				'rules' => '',
				'type' => 'int'
			)
		),'checkLpuComputerEquipmentParentDeviceUsage' => array(
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор МО',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Device_id',
				'label' => 'Идентификатор устройства',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Period_id',
				'label' => 'Идентификатор периода',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ComputerEquip_Year',
				'label' => 'Год',
				'rules' => '',
				'type' => 'int'
			)
		),'checkBeforeDeleteComputerEquip' => array(
			array(
				'field' => 'ComputerEquip_id',
				'label' => 'Идентификатор оборудования',
				'rules' => '',
				'type' => 'id'
			)
		),'loadLpuComputerEquipmentYearsUniq' => array(
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор МО',
				'rules' => '',
				'type' => 'int'
			)
		),'loadLpuHouseholdGrid' => array(
			array(
				'field' => 'LPEW_Lpu_id',
				'label' => 'Идентификатор МО',
				'rules' => '',
				'type' => 'int'
			)
		),'saveLpuHouseholdRecord' => array(
			array(
				'field' => 'LPEW_Lpu_id',
				'label' => 'Идентификатор МО',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'LpuHousehold_id',
				'label' => 'Идентификатор МО',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'LpuHousehold_Name',
				'label' => 'Наименование',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'LpuHousehold_ContactPerson',
				'label' => 'Контактное лицо',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'LpuHousehold_ContactPhone',
				'label' => 'Контактный телефон',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'LpuHousehold_CadNumber',
				'label' => 'Кадастровый номер',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'LpuHousehold_CoordLat',
				'label' => 'Координаты (широта)',
				'rules' => '',
				'type' => 'float'
			),
			array(
				'field' => 'LpuHousehold_CoordLon',
				'label' => 'Координаты (долгота)',
				'rules' => '',
				'type' => 'float'
			),
			array(
				'field' => 'LHHEW_PAddress_id',
				'label' => 'Идентификатор адреса',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'LHHEW_PAddress_Zip',
				'label' => 'Почтовый индекс',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'LHHEW_PKLCountry_id',
				'label' => 'Идентификатор страны',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'LHHEW_PKLRGN_id',
				'label' => 'Идентификатор региона',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'LHHEW_PKLSubRGN_id',
				'label' => 'Идентификатор саб-региона',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'LHHEW_PKLCity_id',
				'label' => 'Идентификатор города',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'LHHEW_PKLTown_id',
				'label' => 'Идентификатор нас. пункта',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'LHHEW_PKLStreet_id',
				'label' => 'Идентификатор улицы',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'LHHEW_PAddress_House',
				'label' => 'Номер дома',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'LHHEW_PAddress_Corpus',
				'label' => 'Номер корпуса',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'LHHEW_PAddress_Flat',
				'label' => 'Номер квартиры',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'LHHEW_PAddress_Address',
				'label' => 'Полный адрес',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'formPrefix',
				'label' => 'Краткое имя формы',
				'rules' => '',
				'type' => 'string'
			)
		),'getLpuHouseholdRecord' => array(
			array(
				'field' => 'LpuHousehold_id',
				'label' => 'Идентификатор домового хозяйства',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'formPrefix',
				'label' => 'Краткое имя формы',
				'rules' => '',
				'type' => 'string'
			)
		), 'getLpuFilialGrid' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuFilial_begDate', 'label' => 'Дата начала', 'rules' => '', 'type' => 'date'),
			array('field' => 'LpuFilial_endDate', 'label' => 'Дата завершения', 'rules' => '', 'type' => 'date'),
			array('field' => 'LpuFilial_Name', 'label' => 'Наименование', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuFilial_Nick', 'label' => 'Краткое наименование', 'rules' => '', 'type' => 'string'),
		), 'getLpuFilialRecord' => array(
			array('field' => 'LpuFilial_id', 'label' => 'Идентификатор домового хозяйства', 'rules' => 'required', 'type' => 'id')
		), 'saveLpuFilialRecord' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'type' => 'id', 'rules' => 'required'),
			array('field' => 'LpuFilial_id', 'label' => 'Идентификатор филиала', 'type' => 'id', 'rules' => ''),
			array('field' => 'LpuFilial_Name', 'label' => 'Наименование записи', 'type' => 'string', 'rules' => 'required', 'length' => 300),
			array('field' => 'LpuFilial_Nick', 'label' => 'Краткое наименование записи', 'type' => 'string', 'rules' => 'required', 'length' => 300),
			array('field' => 'LpuFilial_Code', 'label' => 'Код записи', 'type' => 'string', 'rules' => 'required', 'length' => 30),
			array('field' => 'Oktmo_id', 'label' => 'Идентификатор кода ОКТМО', 'type' => 'id', 'rules' => 'required'),
			array('field' => 'RegisterMO_id', 'label' => 'ОИД филиала', 'type' => 'id', 'rules' => ''),
			array('field' => 'LpuFilial_begDate', 'label' => 'Дата начала', 'type' => 'date', 'rules' => 'required'),
			array('field' => 'LpuFilial_endDate', 'label' => 'Дата завершения', 'type' => 'date', 'rules' => '')
		), 'deleteLpuFilialRecord' => array(
			array('field' => 'id', 'label' => 'Идентификатор филиала', 'type' => 'id', 'rules' => 'required')
		), 'loadServiceContract' => array(
			array(
				'field' => 'LpuDispContract_id',
				'label' => 'Договор',
				'rules' => 'required',
				'type' => 'id'
			)
		)
    );

	/**
	 *	Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->load->database();
		$this->load->model('LpuPassport_model', 'dbmodel');
	}

	/**
	 * Получение общих данных паспорта МО
	 */
	function getLpuPassport() {
		$data = $this->ProcessInputData('getLpuPassport', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getLpuPassport($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 *  Функция получения списка сторонних специалистов.
	 *  Входящие данные: сессия.
	 *  На выходе: JSON-строка со списком договоров МО.
	 */
	function loadLpuDispContract()
	{
		$data = $this->ProcessInputData('loadLpuDispContract', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadLpuDispContract($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *  Функция получения списка ОМС.
	 *  Входящие данные: сессия.
	 *  На выходе: JSON-строка со списком ОМС.
	 */
	function loadLpuPeriodOMSGrid()
	{
		$data = $this->ProcessInputData('loadLpuPeriodOMSGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadLpuPeriodOMSGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	/**
	 *  Функция получения списка ОМС.
	 *  Входящие данные: сессия.
	 *  На выходе: JSON-строка со списком ОМС.
	 */
	function loadLpuOMSGrid()
	{
		$data = $this->ProcessInputData('loadLpuOMSGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadLpuOMSGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *  Загрузка комбо "По договору"
	 */
	function loadLpuDispContractCombo()
	{
		$data = $this->ProcessInputData('loadLpuDispContractCombo', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadLpuDispContractCombo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	*  Получение информации о ОМС
	*/
	function loadLpuPeriodOMS()
	{
		$data = $this->ProcessInputData('loadLpuPeriodOMS', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadLpuPeriodOMS($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
	/**
	 *  Возвращает флаг о наличии на МО периода ОМС
	 */
	function hasLpuPeriodOMS()
	{
		$data = $this->ProcessInputData('hasLpuPeriodOMS', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->hasLpuPeriodOMS($data);
		$this->ProcessModelSave($response)->ReturnData();

		return true;
	}
	/**
	*  Получение информации о ОМС
	*/
	function loadLpuOMS()
	{
		$data = $this->ProcessInputData('loadLpuOMS', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadLpuOMS($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 *  Функция получения списка ЛЛО.
	 *  Входящие данные: сессия.
	 *  На выходе: JSON-строка со списком ЛЛО.
	 */
	function loadLpuPeriodDLOGrid()
	{
		$data = $this->ProcessInputData('loadLpuPeriodDLOGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadLpuPeriodDLOGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *  Функция получения периода ЛЛО.
	 *  Входящие данные: сессия.
	 *  На выходе: JSON-строка с периодом ЛЛО.
	 */
	function loadLpuPeriodDLO()
	{
		$data = $this->ProcessInputData('loadLpuPeriodDLO', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadLpuPeriodDLO($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
	/**
	 *  Функция получения списка работы в системе Промед.
	 *  Входящие данные: сессия.
	 *  На выходе: JSON-строка со списком работы в системе Промед.
	 */
	function loadOrgWorkPeriodGrid()
	{
		$data = $this->ProcessInputData('loadOrgWorkPeriodGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadOrgWorkPeriodGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *  Функция получения периода работы в системе Промед.
	 *  Входящие данные: сессия.
	 *  На выходе: JSON-строка с периодом работы в системе Промед.
	 */
	function loadOrgWorkPeriod()
	{
		$data = $this->ProcessInputData('loadOrgWorkPeriod', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadOrgWorkPeriod($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 *  Функция получения списка ДМС.
	 *  Входящие данные: сессия.
	 *  На выходе: JSON-строка со списком ДМС.
	 */
	function loadLpuPeriodDMSGrid()
	{
		$data = $this->ProcessInputData('loadLpuPeriodDMSGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadLpuPeriodDMSGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *  Функция получения периода ДМС.
	 *  Входящие данные: сессия.
	 *  На выходе: JSON-строка с периодом ДМС.
	 */
	function loadLpuPeriodDMS()
	{
		$data = $this->ProcessInputData('loadLpuPeriodDMS', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadLpuPeriodDMS($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;

	}

	/**
	 *  Функция получения списка Фондодержания.
	 *  Входящие данные: сессия.
	 *  На выходе: JSON-строка со списком Фондодержания.
	 */
	function loadLpuPeriodFondHolderGrid()
	{
		$data = $this->ProcessInputData('loadLpuPeriodFondHolderGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadLpuPeriodFondHolderGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *  Функция получения периода Фондодержания.
	 *  Входящие данные: сессия.
	 *  На выходе: JSON-строка с периодом Фондодержания.
	 */
	function loadLpuPeriodFondHolder()
	{
		$data = $this->ProcessInputData('loadLpuPeriodFondHolder', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadLpuPeriodFondHolder($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *  Функция получения списка лицензий МО.
	 *  Входящие данные: сессия.
	 *  На выходе: JSON-строка со списком лицензий.
	 */
	function loadLpuLicenceGrid()
	{
		$data = $this->ProcessInputData('loadLpuLicenceGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadLpuLicenceGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *  Функция получения лицензии МО.
	 *  Входящие данные: сессия.
	 *  На выходе: JSON-строка с лицензей МО.
	 */
	function loadLpuLicence()
	{
		$data = $this->ProcessInputData('loadLpuLicence', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadLpuLicenceGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *  Функция получения списка мобильных бригад МО.
	 */
	function loadLpuMobileTeamGrid()
	{
		$data = $this->ProcessInputData('loadLpuMobileTeamGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadLpuMobileTeamGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *  Функция получения мобильной бригады МО.
	 */
	function loadLpuMobileTeam()
	{
		$data = $this->ProcessInputData('loadLpuMobileTeam', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadLpuMobileTeam($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}


	/**
	 *  @desc Возвращает список установленных тарифов для МО
	 *  @return JSON
	 */
	function loadSmpTariffGrid(){
		$data = $this->ProcessInputData( 'loadSmpTariffGrid', true );
		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->loadSmpTariffGrid( $data );
		$this->ProcessModelList( $response, true, true )->ReturnData();
	}

	/**
	 *  @desc Возвращает список установленных тарифов для МО
	 *  @return JSON
	 */
	function loadTariffDispGrid(){
		$data = $this->ProcessInputData( 'loadTariffDispGrid', true );
		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->loadTariffDispGrid( $data );
		$this->ProcessModelList( $response, true, true )->ReturnData();
	}

	/**
	 * Удаление тарифа по бюджету
	 */
	function deleteMedicalCareBudgTypeTariff() {
		$data = $this->ProcessInputData('deleteMedicalCareBudgTypeTariff', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->deleteMedicalCareBudgTypeTariff($data);
		$this->ProcessModelSave($response, true, 'Ошибка удаления тарифа по бюджету')->ReturnData();
	}

	/**
	 * Сохранение тарифа по бюджету
	 */
	function saveMedicalCareBudgTypeTariff() {
		$data = $this->ProcessInputData('saveMedicalCareBudgTypeTariff', false);
		if ($data === false) {
			return false;
		}

		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];

		$response = $this->dbmodel->saveMedicalCareBudgTypeTariff($data);
		$this->ProcessModelSave($response, true, 'Ошибка сохранения тарифа по бюджету')->ReturnData();
	}

	/**
	 * Загрузка тарифа по бюджету на редактирование
	 */
	function loadMedicalCareBudgTypeTariffEditWindow() {
		$data = $this->ProcessInputData('loadMedicalCareBudgTypeTariffEditWindow', false);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->loadMedicalCareBudgTypeTariffEditWindow($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}


	/**
	 * Загрузка списка тарифов по бюджету
	 */
	function loadMedicalCareBudgTypeTariffGrid() {
		$data = $this->ProcessInputData('loadMedicalCareBudgTypeTariffGrid', false);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->loadMedicalCareBudgTypeTariffGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	 * @return bool
	 */
	function loadTariffLpuGrid(){
		$data = $this->ProcessInputData( 'loadTariffLpuGrid', true );
		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->loadTariffLpuGrid( $data );
		$this->ProcessModelList( $response, true, true )->ReturnData();
	}

	/**
	 *  @desc Возвращает установленный тариф для МО
	 *  @return JSON
	 */
	function loadSmpTariff(){
		$data = $this->ProcessInputData( 'loadSmpTariff', true );
		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->loadSmpTariff( $data );
		$this->ProcessModelList( $response, true, true )->ReturnData();
	}

	/**
	 * @return bool
	 */
	function loadTariffLpu(){
		$data = $this->ProcessInputData( 'loadTariffLpu', true );
		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->loadTariffLpu( $data );
		$this->ProcessModelList( $response, true, true )->ReturnData();
	}

	/**
	 *  @desc Возвращает установленный тариф ДД для МО
	 *  @return JSON
	 */
	function loadTariffDisp(){
		$data = $this->ProcessInputData( 'loadTariffDisp', true );
		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->loadTariffDisp( $data );
		$this->ProcessModelList( $response, true, true )->ReturnData();
	}

	/**
	 *  Функция получения списка лицензий МО.
	 *  Входящие данные: сессия.
	 *  На выходе: JSON-строка со списком лицензий.
	 */
	function loadLpuBuilding()
	{
		$data = $this->ProcessInputData('loadLpuBuilding', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadLpuBuilding($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *  Функция получения отделений здания МО.
	 *  Входящие данные: сессия.
	 *  На выходе: JSON-строка со списком лицензий.
	 */
	function loadMOSections()
	{
		$data = $this->ProcessInputData('loadMOSections', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadMOSections($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *  Функция получения отделений здания МО.
	 *  Входящие данные: сессия.
	 *  На выходе: JSON-строка со списком лицензий.
	 */
	function getMOSectionsForList()
	{
		$data = $this->ProcessInputData('getMOSectionsForList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getMOSectionsForList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *  Функция получения отделений здания МО.
	 *  Входящие данные: сессия.
	 *  На выходе: JSON-строка со списком лицензий.
	 */
	function calcWorkAreaWard()
	{
		$data = $this->ProcessInputData('calcWorkAreaWard', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->calcWorkAreaWard($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *  Функция получения списка оборудования МО.
	 *  Входящие данные: сессия.
	 *  На выходе: JSON-строка со списком оборудования.
	 */
	function loadLpuEquipment()
	{
		$data = $this->ProcessInputData('loadLpuEquipment', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadLpuEquipment($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *  Функция получения списка медицинских изделий.
	 *  Входящие данные: сессия.
	 *  На выходе: JSON-строка со списком оборудования.
	 */
	function loadMedProductCard()
	{
		$data = $this->ProcessInputData('loadMedProductCard', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadMedProductCard($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}


    /**
     * Получение списка организаций по запросу в комбобокс
     * Результат расцвечивается
     */
    function getMedProductClassList() {

        $data = $this->ProcessInputData('getMedProductClassList',true);
        if ($data === false) {return false;}

        $med_prod_data = $this->dbmodel->getMedProductClassList($data);
        $this->ReturnData($med_prod_data);

        return true;
    }

	/**
	 *  Функция получения списка транспорта МО.
	 *  Входящие данные: сессия.
	 *  На выходе: JSON-строка со списком транспорта.
	 */
	function loadLpuTransport()
	{
		$data = $this->ProcessInputData('loadLpuTransport', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadLpuTransport($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *  Функция получения списка расчётных квот.
	 *  Входящие данные: сессия.
	 *  На выходе: JSON-строка со списком расчётных квот.
	 */
	function loadLpuQuote()
	{
		$data = $this->ProcessInputData('loadLpuQuote', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadLpuQuote($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}


	/**
	 * Печать данных паспорта по регистратуре
	 */
	public function printLpuPassportER()
	{
		$data = $this->ProcessInputData('printLpuPassportER', true);
		if ($data === false) { return false; }

		$this->load->library('parser');

		// основные данные по LPU
		$response = $this->dbmodel->getLpuPassportMainDataForPrint($data);
		if ( is_array($response) && count($response) > 0 ) {
			$val = $response[0];
			$parse_data = $response[0];
		}

		// ответственные сотрудники МО
		$orghead_lpu = array();
		// дефолтные значения
		for ( $i = 1; $i <= 14; $i++ )
		{
			$orghead_lpu[$i] = array(
				'OrgHeadPost_id' => '',
				'OrgHead_FIO' => '',
				'OrgHeadPost_Name' => '',
				'OrgHead_Email' => '',
				'OrgHead_Phone' => '',
				'OrgHead_Mobile' => '',
				'OrgHead_CommissNum' => '',
				'OrgHead_CommissDate' => '',
				'OrgHead_Address' => ''
			);
		}

		$parse_data['Lpu_GlavVrach_FioPhone'] = null;
		$parse_data['Lpu_ZamGlavVrach_FioPhone'] = null;
		$parse_data['Lpu_ZavTer_FioPhone'] = null;
		$parse_data['Lpu_ZavUzk_FioPhone'] = null;

		$response = $this->dbmodel->getLpuPassportHeadDataForPrint($data);
		if ( is_array($response) && count($response) > 0 ) {
			for ( $i = 0; $i < count($response); $i++ )
			{
				$orghead_lpu[$response[$i]['OrgHeadPost_id']] = $response[$i];
				switch ( $response[$i]['OrgHeadPost_id'] )
				{
					case 1:
						$parse_data['Lpu_GlavVrach_FioPhone'] = $response[$i]['OrgHead_FIO'].($response[$i]['OrgHead_FIO'] == '' ? '' : ', ').$response[$i]['OrgHead_Phone'];
					break;
					case 4:
						$parse_data['Lpu_ZamGlavVrach_FioPhone'] = $response[$i]['OrgHead_FIO'].($response[$i]['OrgHead_FIO'] == '' ? '' : ', ').$response[$i]['OrgHead_Phone'];
					break;
					case 13:
						$parse_data['Lpu_ZavTer_FioPhone'] = $response[$i]['OrgHead_FIO'].($response[$i]['OrgHead_FIO'] == '' ? '' : ', ').$response[$i]['OrgHead_Phone'];
					break;
					case 14:
						$parse_data['Lpu_ZavUzk_FioPhone'] = $response[$i]['OrgHead_FIO'].($response[$i]['OrgHead_FIO'] == '' ? '' : ', ').$response[$i]['OrgHead_Phone'];
					break;
				}
			}
		}
		$parse_data['orghead_lpu'] = $orghead_lpu;

		// LpuUnit - данные
		$lpuunit_data = array();
		$response = $this->dbmodel->getLpuUnitPassportMainDataForPrint($data);
		if ( is_array($response) && count($response) > 0 ) {
			for ( $i = 0; $i < count($response); $i++ )
			{
				$lpuunit_data[$response[$i]['LpuUnit_id']] = $response[$i];

				// дефолтные значения для руководства c 8, 9, 10, 12, 13, 14
				for ( $j = 8; $j <= 14; $j++ )
				{
					if ( $j != 11 )
					{
						$index = 'OrgHeadPost_id'.$j;
						$lpuunit_data[$response[$i]['LpuUnit_id']][$index] = '';
						$index = 'OrgHead_FIO'.$j;
						$lpuunit_data[$response[$i]['LpuUnit_id']][$index] = '';
						$index = 'OrgHeadPost_Name'.$j;
						$lpuunit_data[$response[$i]['LpuUnit_id']][$index] = '';
						$index = 'OrgHead_Email'.$j;
						$lpuunit_data[$response[$i]['LpuUnit_id']][$index] = '';
						$index = 'OrgHead_Phone'.$j;
						$lpuunit_data[$response[$i]['LpuUnit_id']][$index] = '';
						$index = 'OrgHead_Mobile'.$j;
						$lpuunit_data[$response[$i]['LpuUnit_id']][$index] = '';
						$index = 'OrgHead_CommissNum'.$j;
						$lpuunit_data[$response[$i]['LpuUnit_id']][$index] = '';
						$index = 'OrgHead_CommissDate'.$j;
						$lpuunit_data[$response[$i]['LpuUnit_id']][$index] = '';
						$index = 'OrgHead_Address'.$j;
						$lpuunit_data[$response[$i]['LpuUnit_id']][$index] = '';
					}
				}
				// из базы сразу тянем
				$data['LpuUnit_id'] = $response[$i]['LpuUnit_id'];
				$resp = $this->dbmodel->getLpuUnitPassportHeadDataForPrint($data);
				if ( is_array($response) && count($resp) > 0 ) {
					for ( $k = 0; $k < count($resp); $k++ )
					{
						switch ( $resp[$k]['OrgHeadPost_id'] )
						{
							case 8: case 9: case 10: case 12: case 13: case 14:
								$index = 'OrgHeadPost_id'.$resp[$k]['OrgHeadPost_id'];
								$lpuunit_data[$response[$i]['LpuUnit_id']][$index] = $resp[$k]['OrgHeadPost_id'];
								$index = 'OrgHead_FIO'.$resp[$k]['OrgHeadPost_id'];
								$lpuunit_data[$response[$i]['LpuUnit_id']][$index] = $resp[$k]['OrgHead_FIO'];
								$index = 'OrgHeadPost_Name'.$resp[$k]['OrgHeadPost_id'];
								$lpuunit_data[$response[$i]['LpuUnit_id']][$index] = $resp[$k]['OrgHeadPost_Name'];
								$index = 'OrgHead_Email'.$resp[$k]['OrgHeadPost_id'];
								$lpuunit_data[$response[$i]['LpuUnit_id']][$index] = $resp[$k]['OrgHead_Email'];
								$index = 'OrgHead_Phone'.$resp[$k]['OrgHeadPost_id'];
								$lpuunit_data[$response[$i]['LpuUnit_id']][$index] = $resp[$k]['OrgHead_Phone'];
								$index = 'OrgHead_Mobile'.$resp[$k]['OrgHeadPost_id'];
								$lpuunit_data[$response[$i]['LpuUnit_id']][$index] = $resp[$k]['OrgHead_Mobile'];
								$index = 'OrgHead_CommissNum'.$resp[$k]['OrgHeadPost_id'];
								$lpuunit_data[$response[$i]['LpuUnit_id']][$index] = $resp[$k]['OrgHead_CommissNum'];
								$index = 'OrgHead_CommissDate'.$resp[$k]['OrgHeadPost_id'];
								$lpuunit_data[$response[$i]['LpuUnit_id']][$index] = $resp[$k]['OrgHead_CommissDate'];
								$index = 'OrgHead_Address'.$resp[$k]['OrgHeadPost_id'];
								$lpuunit_data[$response[$i]['LpuUnit_id']][$index] = $resp[$k]['OrgHead_Address'];
							break;
						}
					}
				}
			}
		}
		switch ($_SESSION['region']['nick']) {
			// лейблы для УФЫ
			case 2:
				$parse_data['form_caption'] = 'Форма для подачи информации в организацию, предоставляющую услугу «Единая регистратура» в г. Уфа';
				$parse_data['label_er_info'] = 'Информация о возможности записи пациентов организацией, оказывающей услугу «Единая регистратура» в г. Уфа на резервные бирки.<sup>1</sup>';
				$parse_data['forum_label'] = 'Работа с форумом на сайте ufacity.info';
			break;
			default:
				$parse_data['form_caption'] = 'Форма для подачи информации в организацию, предоставляющую услугу «Единая регистратура»';
				$parse_data['label_er_info'] = 'Информация о возможности записи пациентов организацией, оказывающей услугу «Единая регистратура» в на резервные бирки.<sup>1</sup>';
				$parse_data['forum_label'] = 'Работа с форумом на сайте';
		}
		$parse_data['lpuunit_data'] = $lpuunit_data;
		$this->parser->parse('lpu_passport_template', $parse_data);
	}


	/**
	 * Сохранение периода ОМС
	 */
	function saveLpuPeriodOMS()
	{
		$data = $this->ProcessInputData('saveLpuPeriodOMS', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveLpuPeriodOMS($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;

	}
	/**
	 * Сохранение  ОМС
	 */
	function saveLpuOMS()
	{
		$data = $this->ProcessInputData('saveLpuOMS', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveLpuOMS($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;

	}

    /**
     * Загрузка списка классов МКБ10
     */
    function loadMkb10CodeClass()
    {
        $response = $this->dbmodel->loadMkb10CodeClass();
        $this->ProcessModelList($response, true, true)->ReturnData();
    }

    /**
	 * Сохранение статуса курорта
	 */
	function saveDistrictsType()
	{
		$data = $this->ProcessInputData('saveDistrictsType', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveDistrictsType($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;

	}

    /**
     * Сохранение статуса курорта
     */
    function saveKurortStatus()
    {
        $data = $this->ProcessInputData('saveKurortStatus', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->saveKurortStatus($data);
        $this->ProcessModelSave($response, true)->ReturnData();

        return true;

    }

    /**
     *  Функция получения списка стутусов курорта.
     *  Входящие данные: сессия.
     */
    function loadKurortStatus()
    {
        $data = $this->ProcessInputData('loadKurortStatus', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->loadKurortStatus($data);
        $this->ProcessModelList($response, true, true)->ReturnData();
    }

    /**
     *  Функция удаления стутуса курорта.
     *  Входящие данные: сессия.
     */
    function deleteKurortStatus()
    {
        $data = $this->ProcessInputData('deleteKurortStatus', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->deleteKurortStatus($data);
        $this->ProcessModelList($response, true, true)->ReturnData();
    }

    /**
     * Сохранение статуса курорта
     */
    function saveDisSanProtection()
    {
        $data = $this->ProcessInputData('saveDisSanProtection', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->saveDisSanProtection($data);
        $this->ProcessModelSave($response, true)->ReturnData();

        return true;

    }

    /**
     *  Функция получения списка округов горно-санитарной охраны.
     *  Входящие данные: сессия.
     */
    function loadDisSanProtection()
    {
        $data = $this->ProcessInputData('loadDisSanProtection', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->loadDisSanProtection($data);
        $this->ProcessModelList($response, true, true)->ReturnData();
    }

    /**
     * Сохранение заезда
     */
    function saveMOArrival()
    {
        $data = $this->ProcessInputData('saveMOArrival', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->saveMOArrival($data);
        $this->ProcessModelSave($response, true)->ReturnData();

        return true;

    }

    /**
     *  Функция получения списка заездов.
     *  Входящие данные: сессия.
     */
    function loadMOArrival()
    {
        $data = $this->ProcessInputData('loadMOArrival', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->loadMOArrival($data);
        $this->ProcessModelList($response, true, true)->ReturnData();
    }

	/**
	 *  Функция получения списка домовых хозяйств.
	 */
	function loadLpuHouseholdGrid()
	{
		$data = $this->ProcessInputData('loadLpuHouseholdGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadLpuHouseholdGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *  Функция получения записи домового хозяйства.
	 */
	function getLpuHouseholdRecord()
	{
		$data = $this->ProcessInputData('getLpuHouseholdRecord', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getLpuHouseholdRecord($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *  Функция сохранения записи домового хозяйства.
	 */
	function saveLpuHouseholdRecord()
	{
		$data = $this->ProcessInputData('saveLpuHouseholdRecord', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveLpuHouseholdRecord($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}

	/**
	 *  Функция получения списка компьютерного оборудования.
	 */
	function loadLpuComputerEquipment()
	{
		$data = $this->ProcessInputData('loadLpuComputerEquipment', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadLpuComputerEquipment($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *  Функция получения списка периодов для компьютерного оборудования.
	 */
	function loadLpuComputerEquipmentYearsUniq()
	{
		$data = $this->ProcessInputData('loadLpuComputerEquipmentYearsUniq', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadLpuComputerEquipmentYearsUniq($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *  Функция получения списка периодов для компьютерного оборудования.
	 */
	function loadLpuComputerEquipmentYearPeriods()
	{
		$response = $this->dbmodel->loadLpuComputerEquipmentYearPeriods();
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *  Функция получения списка типов использования компьютерного оборудования.
	 */
	function loadLpuComputerEquipmentIntendUse()
	{
		$response = $this->dbmodel->loadLpuComputerEquipmentIntendUse();
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *  Функция получения списка категорий для компьютерного оборудования.
	 */
	function loadLpuComputerEquipmentDevicesCat()
	{
		$response = $this->dbmodel->loadLpuComputerEquipmentDevices();
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *  Функция получения компьютерного оборудования.
	 */
	function loadLpuComputerEquipmentDevices()
	{
		$data = $this->ProcessInputData('loadLpuComputerEquipmentDevices', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadLpuComputerEquipmentDevices($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *	Проверка на возможность удаления родительской категории;
	 * 	поля: категория, год, период;
	 */
	function checkBeforeDeleteComputerEquip() {

		$data = $this->ProcessInputData('checkBeforeDeleteComputerEquip', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkBeforeDeleteComputerEquip($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *  Функция проверки на уникальность записи компьютерного оборудования.
	 */
	function checkLpuComputerEquipmentUniqRecord()
	{
		$data = $this->ProcessInputData('checkLpuComputerEquipmentUniqRecord', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkLpuComputerEquipmentUniqRecord($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *  Функция проверки на количество использования в дочерних категориях
	 */
	function checkLpuComputerEquipmentChildDeviceUsage()
	{
		$data = $this->ProcessInputData('checkLpuComputerEquipmentChildDeviceUsage', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkLpuComputerEquipmentChildDeviceUsage($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *  Функция проверки на количество использования в родительской категории.
	 */
	function checkLpuComputerEquipmentParentDeviceUsage()
	{
		$data = $this->ProcessInputData('checkLpuComputerEquipmentParentDeviceUsage', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkLpuComputerEquipmentParentDeviceUsage($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Сохранение компьютерного оснащения
	 */
	function saveLpuComputerEquipment()
	{
		$data = $this->ProcessInputData('saveLpuComputerEquipment', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveLpuComputerEquipment($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}

	/**
	 *  Функция удаления компьютерного оснащения.
	 */
	function deleteLpuComputerEquipment()
	{
		$data = $this->ProcessInputData('deleteLpuComputerEquipment', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteLpuComputerEquipment($data);
		$this->ProcessModelSave( $response, true )->ReturnData();

		return true;
	}

	/**
	 *  Печать акта
	 */
	function printLpuComputerEquipment() {

		$this->load->model('LpuPassport_model', 'dbmodel');
		$this->load->library('parser');

		$params = array('Lpu_id', 'ComputerEquip_Year');
		$template = 'print_computer_equipment';
		$data = '';

		foreach ($params as $p_name) {
			if ((isset($_GET[$p_name])) && (is_numeric($_GET[$p_name])) && ($_GET[$p_name] >= 0)) {

				$data[$p_name] = $_GET[$p_name];
			} else {
				echo 'Не указан '.$p_name;
				return true;
			}
		}

		$response = $this->dbmodel->getLpuCompterEquipPrintData($data);
		$parse_data['categories'] =  $response['main_cats'];
		$parse_data['medstatcabs'] =  (isset($response['medstatcabs'])) ? $response['medstatcabs']: '';

		$this->parser->parse($template, $parse_data);

		return true;
	}

    /**
     *  Функция получения списка заездов.
     */
    function loadKurortTypeLink()
    {
        $data = $this->ProcessInputData('loadKurortTypeLink', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->loadKurortTypeLink($data);
        $this->ProcessModelList($response, true, true)->ReturnData();
    }

    /**
     * Сохранение заезда
     */
    function saveKurortTypeLink()
    {
        $data = $this->ProcessInputData('saveKurortTypeLink', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->saveKurortTypeLink($data);
        $this->ProcessModelSave($response, true)->ReturnData();

        return true;

    }

    /**
     *  Функция получения списка заездов.
     */
    function loadMOArea()
    {
        $data = $this->ProcessInputData('loadMOArea', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->loadMOArea($data);
        $this->ProcessModelList($response, true, true)->ReturnData();
    }

    /**
     * Сохранение площадки, занимаемой организациии
     */
    function saveMOArea()
    {
        $data = $this->ProcessInputData('saveMOArea', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->saveMOArea($data);
        $this->ProcessModelSave($response, true)->ReturnData();

        return true;

    }

    /**
     *  Функция получения списка заездов.
     */
    function loadMOAreaObject()
    {
        $data = $this->ProcessInputData('loadMOAreaObject', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->loadMOAreaObject($data);
        $this->ProcessModelList($response, true, true)->ReturnData();
    }

    /**
     * Сохранение площадки, занимаемой организациии
     */
    function saveMOAreaObject()
    {
        $data = $this->ProcessInputData('saveMOAreaObject', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->saveMOAreaObject($data);
        $this->ProcessModelSave($response, true)->ReturnData();

        return true;

    }

    /**
     *  Функция получения списка информационных систем.
     */
    function loadMOInfoSys()
    {
        $data = $this->ProcessInputData('loadMOInfoSys', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->loadMOInfoSys($data);
        $this->ProcessModelList($response, true, true)->ReturnData();
    }

    /**
     * Сохранение информационной системы
     */
    function saveMOInfoSys()
    {
        $data = $this->ProcessInputData('saveMOInfoSys', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->saveMOInfoSys($data);
        $this->ProcessModelSave($response, true)->ReturnData();

        return true;

    }

    /**
     *  Функция получения специализации организации
     */
    function loadSpecializationMO()
    {
        $data = $this->ProcessInputData('loadSpecializationMO', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->loadSpecializationMO($data);
        $this->ProcessModelList($response, true, true)->ReturnData();
    }

    /**
     * Сохранение специализации организации
     */
    function saveSpecializationMO()
    {
        $data = $this->ProcessInputData('saveSpecializationMO', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->saveSpecializationMO($data);
        $this->ProcessModelSave($response, true)->ReturnData();

        return true;

    }

    /**
     *  Функция получения списка медицинских услуг
     */
    function loadMedUsluga()
    {
        $data = $this->ProcessInputData('loadMedUsluga', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->loadMedUsluga($data);
		$this->load->model('Utils_model', 'utilsmodel');

		if (is_array($response) && empty($response['Error_Msg'])) {
			foreach ($response as $key => &$value){
				$value['object'] = 'DUslugi';
				$value['scheme'] = 'fed';
				$value['separator'] = ' ';
				$value['id'] = $value['DUslugi_id'];
				$value['Lpu_id'] = $data['Lpu_id'];

				$response_with_pass = $this->utilsmodel->getObjectNameWithPath($value);
				if (is_array($response_with_pass) && !empty($response_with_pass[0]['name'])) {
					$value['DUslugi_Name'] = $response_with_pass[0]['name'];
				}
			}
		}

		$this->ProcessModelList($response, true, true)->ReturnData();
    }

    /**
     * Сохранение медицинской услуги
     */
    function saveMedUsluga()
    {
        $data = $this->ProcessInputData('saveMedUsluga', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->saveMedUsluga($data);
        $this->ProcessModelSave($response, true)->ReturnData();

        return true;

    }

    /**
     *  Функция получения списка медицинских услуг
     */
    function loadMedTechnology()
    {
        $data = $this->ProcessInputData('loadMedTechnology', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->loadMedTechnology($data);
        $this->ProcessModelList($response, true, true)->ReturnData();
    }

    /**
     *  Загрузка комбика Номер лицензии на форме Специализация организации
     */
    function loadLpuLicenceSpecializationMO()
    {
        $data = $this->ProcessInputData('loadLpuLicenceSpecializationMO', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->loadLpuLicenceSpecializationMO($data);
        $this->ProcessModelList($response, true, true)->ReturnData();
    }

    /**
     *  Загрузка комбика идентификатор здания на форме Медицнская технология
     */
    function loadLpuBuildingMedTechnology()
    {
        $data = $this->ProcessInputData('loadLpuBuildingMedTechnology', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->loadLpuBuildingMedTechnology($data);
        $this->ProcessModelList($response, true, true)->ReturnData();
    }

    /**
     * Сохранение медицинской услуги
     */
    function saveMedTechnology()
    {
        $data = $this->ProcessInputData('saveMedTechnology', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->saveMedTechnology($data);
        $this->ProcessModelSave($response, true)->ReturnData();

        return true;

    }

    /**
     *  Функция получения списка медицинских услуг
     */
    function loadPitanFormTypeLink()
    {
        $data = $this->ProcessInputData('loadPitanFormTypeLink', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->loadPitanFormTypeLink($data);
        $this->ProcessModelList($response, true, true)->ReturnData();
    }

    /**
     * Сохранение медицинской услуги
     */
    function savePitanFormTypeLink()
    {
        $data = $this->ProcessInputData('savePitanFormTypeLink', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->savePitanFormTypeLink($data);
        $this->ProcessModelSave($response, true)->ReturnData();

        return true;

    }

    /**
     *  Функция получения списка медицинских услуг
     */
    function loadPlfDocTypeLink()
    {
        $data = $this->ProcessInputData('loadPlfDocTypeLink', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->loadPlfDocTypeLink($data);
        $this->ProcessModelList($response, true, true)->ReturnData();
    }

    /**
     * Сохранение медицинской услуги
     */
    function savePlfDocTypeLink()
    {
        $data = $this->ProcessInputData('savePlfDocTypeLink', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->savePlfDocTypeLink($data);
        $this->ProcessModelSave($response, true)->ReturnData();

        return true;

    }

    /**
     *  Функция получения Объекты/места использования природных лечебных факторов
     */
    function loadPlfObjectCount()
    {
        $data = $this->ProcessInputData('loadPlfObjectCount', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->loadPlfObjectCount($data);
        $this->ProcessModelList($response, true, true)->ReturnData();
    }

    /**
     * Сохранение Объекты/места использования природных лечебных факторов
     */
    function savePlfObjectCount()
    {
        $data = $this->ProcessInputData('savePlfObjectCount', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->savePlfObjectCount($data);
        $this->ProcessModelSave($response, true)->ReturnData();

        return true;

    }

    /**
     *  Функция получения операий с лицензиями МО
     */
    function loadLpuLicenceOperationLink()
    {
        $data = $this->ProcessInputData('loadLpuLicenceOperationLink', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->loadLpuLicenceOperationLink($data);
        $this->ProcessModelList($response, true, true)->ReturnData();
    }

    /**
     *  Функция получения операий с лицензиями МО
     */
    function loadLpuLicenceLink()
    {
        $data = $this->ProcessInputData('loadLpuLicenceLink', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->loadLpuLicenceLink($data);
        $this->ProcessModelList($response, true, true)->ReturnData();
    }

    /**
     *  Функция получения приложения к лицензии МО (Казахстан)
     */
    function loadLpuLicenceDop()
    {
        $data = $this->ProcessInputData('loadLpuLicenceDop', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->loadLpuLicenceDop($data);
        $this->ProcessModelList($response, true, true)->ReturnData();
    }

    /**
     *  Функция получения расходных материалов
     */
    function loadConsumables()
    {
        $data = $this->ProcessInputData('loadConsumables', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->loadConsumables($data);
        $this->ProcessModelList($response, true, true)->ReturnData();
    }

    /**
     *  Функция получения расходных материалов
     */
    function loadDowntime()
    {
        $data = $this->ProcessInputData('loadDowntime', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->loadDowntime($data);
        $this->ProcessModelList($response, true, true)->ReturnData();
    }

    /**
     *  Функция получения эксплуатационных данных
     */
    function loadWorkData()
    {
        $data = $this->ProcessInputData('loadWorkData', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->loadWorkData($data);
        $this->ProcessModelList($response, true, true)->ReturnData();
    }

    /**
     *  Функция получения списка свидетельств поверок
     */
    function loadMeasureFundCheck()
    {
        $data = $this->ProcessInputData('loadMeasureFundCheck', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->loadMeasureFundCheck($data);
        $this->ProcessModelList($response, true, true)->ReturnData();
    }

    /**
     *  Функция получения расходных материалов
     */
    function loadMedProductCardData()
    {
        $data = $this->ProcessInputData('loadMedProductCardData', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->loadMedProductCardData($data);
        $this->ProcessModelList($response, true, true)->ReturnData();
    }

    /**
     *  Функция получения оценки износа
     */
    function loadAmortization()
    {
        $data = $this->ProcessInputData('loadAmortization', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->loadAmortization($data);
        $this->ProcessModelList($response, true, true)->ReturnData();
    }

    /**
     * Сохранение операии с лицензией МО
     */
    function saveLpuLicenceOperationLink()
    {
        $data = $this->ProcessInputData('saveLpuLicenceOperationLink', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->saveLpuLicenceOperationLink($data);
        $this->ProcessModelSave($response, true)->ReturnData();

        return true;

    }


    /**
     * Сохранение операии с лицензией МО
     */
    function saveLpuLicenceDop()
    {
        $data = $this->ProcessInputData('saveLpuLicenceDop', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->saveLpuLicenceDop($data);
        $this->ProcessModelSave($response, true)->ReturnData();

        return true;

    }



	/**
	 * @desc Удаляет операцию с лицензией
	 * @return boolean
	 */
    function deleteLpuLicenceOperationLink() {
        $data = $this->ProcessInputData( 'deleteLpuLicenceOperationLink', true );
        if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->deleteLpuLicenceOperationLink( $data );
		$this->ProcessModelSave( $response, true )->ReturnData();

		return true;
    }

	/**
	 * @desc Удаляет операцию с лицензией
	 * @return boolean
	 */
    function deleteLpuLicenceLink() {
        $data = $this->ProcessInputData( 'deleteLpuLicenceLink', true );
        if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->deleteLpuLicenceLink( $data );
		$this->ProcessModelSave( $response, true )->ReturnData();

		return true;
    }

	/**
	 * @desc Удаляет операцию с лицензией
	 * @return boolean
	 */
    function deleteConsumables() {
        $data = $this->ProcessInputData( 'deleteConsumables', true );
        if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->deleteConsumables( $data );
		$this->ProcessModelSave( $response, true )->ReturnData();

		return true;
    }

	/**
	 * @desc Удаляет операцию с лицензией
	 * @return boolean
	 */
    function deleteAmortization() {
        $data = $this->ProcessInputData( 'deleteAmortization', true );
        if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->deleteAmortization( $data );
		$this->ProcessModelSave( $response, true )->ReturnData();

		return true;
    }

	/**
	 * @desc Удаляет запись о эксплуатации материала
	 * @return boolean
	 */
    function deleteWorkData() {
        $data = $this->ProcessInputData( 'deleteWorkData', true );
        if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->deleteWorkData( $data );
		$this->ProcessModelSave( $response, true )->ReturnData();

		return true;
    }

	/**
	 * @desc Удаляет запись о простое МИ
	 * @return boolean
	 */
    function deleteDowntime() {
        $data = $this->ProcessInputData( 'deleteDowntime', true );
        if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->deleteDowntime( $data );
		$this->ProcessModelSave( $response, true )->ReturnData();

		return true;
    }

	/**
	 * @desc Удаляет запись о свидетельстве проверки средств измерения
	 * @return boolean
	 */
    function deleteMeasureFundCheck() {
        $data = $this->ProcessInputData( 'deleteMeasureFundCheck', true );
        if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->deleteMeasureFundCheck( $data );
		$this->ProcessModelSave( $response, true )->ReturnData();

		return true;
    }

	/**
	 * @desc Удаляет операцию с лицензией
	 * @return boolean
	 */
    function deleteMedProductCard() {
        $data = $this->ProcessInputData( 'deleteMedProductCard', true );
        if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->deleteMedProductCard( $data );
		$this->ProcessModelSave( $response, true )->ReturnData();

		return true;
    }

	/**
	 * @desc Удаляет здание МО
	 * @return boolean
	 */
    function deleteLpuBuildingPass() {
        $data = $this->ProcessInputData( 'deleteLpuBuildingPass', true );
        if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->deleteLpuBuildingPass( $data );
		$this->ProcessModelSave( $response, true )->ReturnData();

		return true;
    }


    /**
     * Сохранение вида лицензии МО
     */
    function saveLpuLicenceProfile()
    {
        $data = $this->ProcessInputData('saveLpuLicenceProfile', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->saveLpuLicenceProfile($data);
        $this->ProcessModelSave($response, true)->ReturnData();

        return true;

    }

    /**
     *  Получение списка направлений оказания медицинской помощи
     */
    function loadUslugaComplexLpu()
    {
        $data = $this->ProcessInputData('loadUslugaComplexLpu', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->loadUslugaComplexLpu($data);
        $this->ProcessModelList($response, true, true)->ReturnData();
    }

    /**
     * Сохранение направления оказания медицинской помощи
     */
    function saveUslugaComplexLpu()
    {
        $data = $this->ProcessInputData('saveUslugaComplexLpu', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->saveUslugaComplexLpu($data);
        $this->ProcessModelSave($response, true)->ReturnData();

        return true;

    }

    /**
     *  Функция получения операий с лицензиями МО
     */
    function loadLpuLicenceProfile()
    {
        $data = $this->ProcessInputData('loadLpuLicenceProfile', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->loadLpuLicenceProfile($data);
		if (is_array($response) && empty($response['Error_Msg'])) {
			$this->load->model('Utils_model', 'utilsmodel');
			foreach ($response as $key => &$value){
				$value['object'] = 'LpuLicenceProfileType';
				$value['scheme'] = 'fed';
				$value['separator'] = ' ';
				$value['id'] = $value['LpuLicenceProfileType_id'];
				$value['Lpu_id'] = $data['Lpu_id'];

				$response_with_pass = $this->utilsmodel->getObjectNameWithPath($value);
				if (is_array($response_with_pass) && !empty($response_with_pass[0]['name'])) {
					$value['LpuLicenceProfileType_Name'] = $response_with_pass[0]['name'];
				}
			}
		}
        $this->ProcessModelList($response, true, true)->ReturnData();
    }


	/**
	 * @desc Удаляет тариф
	 * @return boolean
	 */
    function deleteLpuLicenceProfile() {
        $data = $this->ProcessInputData( 'deleteLpuLicenceProfile', true );
        if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->deleteLpuLicenceProfile( $data );
		$this->ProcessModelSave( $response, true )->ReturnData();

		return true;
    }


	/**
	 * Сохранение периода ЛЛО
	 */
	function saveLpuPeriodDLO()
	{
		$data = $this->ProcessInputData('saveLpuPeriodDLO', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveLpuPeriodDLO($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;

	}

	/**
	 * Сохранение периода работы в системе Промед
	 */
	function saveOrgWorkPeriod()
	{
		$data = $this->ProcessInputData('saveOrgWorkPeriod', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveOrgWorkPeriod($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;

	}
	/**
	 * Сохранение периода ДМС
	 */
	function saveLpuPeriodDMS()
	{
		$data = $this->ProcessInputData('saveLpuPeriodDMS', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveLpuPeriodDMS($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;

	}

	/**
	 * Сохранение периода по фондодержанию
	 */
	function saveLpuPeriodFondHolder()
	{
		$data = $this->ProcessInputData('saveLpuPeriodFondHolder', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveLpuPeriodFondHolder($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;

	}

	/**
	 * Сохранение здания МО
	 */
	function saveLpuBuilding()
	{
		$data = $this->ProcessInputData('saveLpuBuilding', true);
		if ($data === false) { return false; }

		switch(true){
			case (!empty($data['LpuBuildingPass_YearBuilt']) && date('Y',strtotime($data['LpuBuildingPass_YearBuilt'])) > date('Y') ):
				echo json_return_errors('Год постройки не может быть больше текущего года.');
				return false;
				break;
			case (!empty($data['LpuBuildingPass_YearRepair']) && date('Y',strtotime($data['LpuBuildingPass_YearRepair'])) > date('Y') ):
				echo json_return_errors('Год последней реконструкции не может быть больше текущего года.');
				return false;
				break;
			case (!empty($data['LpuBuildingPass_YearProjDoc']) && date('Y',strtotime($data['LpuBuildingPass_YearProjDoc'])) > date('Y') ):
				echo json_return_errors('Год разработки не может быть больше текущего года.');
				return false;
				break;
			case (!empty($data['LpuBuildingPass_EffBuildVol']) && !empty($data['LpuBuildingPass_TotalArea']) && $data['LpuBuildingPass_EffBuildVol'] > $data['LpuBuildingPass_TotalArea'] ):
				echo json_return_errors('Общая площадь здания не может быть меньше чем полезная.');
				return false;
				break;
			case (!empty($data['LpuBuildingPass_WorkArea']) && !empty($data['LpuBuildingPass_TotalArea']) && $data['LpuBuildingPass_WorkArea'] > $data['LpuBuildingPass_TotalArea'] ):
				echo json_return_errors('Общая площадь здания не может быть меньше чем рабочая.');
				return false;
				break;
		}

        if ( date('Y',strtotime($data['LpuBuildingPass_YearBuilt'])) > date('Y') ) {
        }

		$response = $this->dbmodel->saveLpuBuilding($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;

	}

	/**
	 * Сохранение лицензий МО
	 */
	function saveLpuLicence()
	{
		$data = $this->ProcessInputData('saveLpuLicence', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveLpuLicence($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;

	}

	/**
	 * Сохранение карточки медицинского изделия
	 */
	function saveMedProductCard()
	{
		$data = $this->ProcessInputData('saveMedProductCard', true);
		if ($data === false) { return false; }

        if (
			empty($data['MedProductCard_IsOutsorc']) &&
			empty($data['AccountingData_setDate']) &&
			empty($data['AccountingData_begDate']) &&
			empty($data['AccountingData_endDate'])
			) {
			$this->ReturnError('Одно из следующих полей на вкладке "Бухгалтерский учёт" должно быть заполнено: <br/> - Дата ввода в эксплуатацию <br/> - Дата принятия на учёт <br/> - Дата снятия с учёта');
			return false;
		}

		$response = $this->dbmodel->saveMedProductCard($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;

	}

	/**
	 * Сохранение мобильной бригады
	 */
	function saveLpuMobileTeam()
	{
		$data = $this->ProcessInputData('saveLpuMobileTeam', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveLpuMobileTeam($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;

	}


	/**
	 * Сохранение лицензий МО
	 */
	function saveSmpTariff(){
		$data = $this->ProcessInputData( 'saveSmpTariff', true );
		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->saveSmpTariff( $data );
		$this->ProcessModelSave( $response, true )->ReturnData();

		return true;
	}

	/**
	 * @return bool
	 */
	function saveLpuTariff(){
		$data = $this->ProcessInputData( 'saveLpuTariff', true );
		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->saveLpuTariff( $data );
		$this->ProcessModelSave( $response, true )->ReturnData();

		return true;
	}

	/**
	 * Сохранение тарифов по ДД
	 */
	function saveTariffDisp(){
		$data = $this->ProcessInputData( 'saveTariffDisp', true );
		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->saveTariffDisp( $data );
		$this->ProcessModelSave( $response, true )->ReturnData();

		return true;
	}

	/**
	 * @desc Удаляет тариф
	 * @return boolean
	 */
    function deleteSmpTariff() {
        $data = $this->ProcessInputData( 'deleteSmpTariff', true );
        if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->deleteSmpTariff( $data );
		$this->ProcessModelSave( $response, true )->ReturnData();

		return true;
    }

	/**
	 * @return bool
	 */
	function deleteTariffLpu() {
		$data = $this->ProcessInputData( 'deleteTariffLpu', true );
		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->deleteTariffLpu( $data );
		$this->ProcessModelSave( $response, true )->ReturnData();

		return true;
	}

	/**
	 * @desc Удаляет оборудование
	 * @return boolean
	 */
    function deleteEquipment() {
        $data = $this->ProcessInputData( 'deleteEquipment', true );
        if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->deleteEquipment( $data );
		$this->ProcessModelSave( $response, true )->ReturnData();

		return true;
    }

	/**
	 * @desc Удаляет тариф
	 * @return boolean
	 */
    function deleteTariffDisp() {
        $data = $this->ProcessInputData( 'deleteTariffDisp', true );
        if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->deleteTariffDisp( $data );
		$this->ProcessModelSave( $response, true )->ReturnData();

		return true;
    }

	/**
	 * @desc Удаляет связь отделения МО и здания
	 * @return boolean
	 */
    function deleteMOSectionBuildingPass() {
        $data = $this->ProcessInputData( 'deleteMOSectionBuildingPass', true );
        if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->deleteMOSectionBuildingPass( $data );
		$this->ProcessModelSave( $response, true )->ReturnData();

		return true;
    }

	/**
	 * Сохранение транспорта МО
	 */
	function saveLpuTransport()
	{
		$data = $this->ProcessInputData('saveLpuTransport', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveLpuTransport($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}

	/**
	 * Сохранение оборудования МО
	 */
	function saveLpuEquipment()
	{
		$data = $this->ProcessInputData('saveLpuEquipment', true);
		if ($data === false) { return false; }

		if ($data['LpuEquipmentType_id'] == '4') {
			$this->load->model('Dicom_model', 'dicommodel');

			$setForvardSettingsResult = $this->dicommodel->setForvardSettings(array(
				'PACS_Interval' => $data['PACS_Interval'],
				'PACS_Interval_TimeType_id' => $data['PACS_Interval_TimeType_id'],
				'PACS_ExcludeTimeFrom' => $data['PACS_ExcludeTimeFrom'],
				'PACS_ExcludeTimeTo' => $data['PACS_ExcludeTimeTo'],
				'PACS_CronExpression' => $data['PACS_CronExpression'],
				'LpuPacsCompressionType_id' => $data['LpuPacsCompressionType_id'],
				'PACS_StudyAge' => $data['PACS_StudyAge'],
				'PACS_Age_TimeType_id' => $data['PACS_Age_TimeType_id'],
				'PACS_aet' => $data['PACS_aet'],
				'PACS_DeleteFromDb' => $data['PACS_DeleteFromDb'],
				'PACS_DeletePatientsWithoutStudies' => $data['PACS_DeletePatientsWithoutStudies'],
				'PACS_ip_vip' => $data['PACS_ip_vip'],
				'PACS_wado' => $data['PACS_wado'],
				'PACS_CronRequests' => $data['PACS_CronRequests'],
			));

			if ((isset($setForvardSettingsResult[0]))||(isset($setForvardSettingsResult[0]['success']))) {
				if (!$setForvardSettingsResult[0]['success']) {
					$this->ProcessModelSave($setForvardSettingsResult,true)->ReturnData();
					return true;
				}
			} else {
				$this->ProcessModelSave(array(array('success'=>false,'Error_Msg'=>'Неизвестная ошибка сохранения. Обратитесь к администратору')), true)->ReturnData();
				return true;
			}
		}


		$response = $this->dbmodel->saveLpuEquipment($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;

	}

	/**
	 * Получение справочника типов единиц времени
	 */
	function getTimeTypes(){
		$response = $this->dbmodel->getTimeTypes();
		$this->ProcessModelList($response,true,true)->ReturnData();
		return true;
	}

	/**
	 * Получение справочника типов единиц времени
	 */
	function getPacsCompressionTypes() {
		$response = $this->dbmodel->getPacsCompressionTypes();
		$this->ProcessModelList($response,true,true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка крон-запросов
	 */
	function loadCronRequests() {
		$data = $this->ProcessInputData('loadCronRequests', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadCronRequests($data);
		$this->ProcessModelList($response,true,true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка крон-запросов
	 */
	function loadOkeiLinkCombo() {
		$data = $this->ProcessInputData('loadOkeiLinkCombo', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadOkeiLinkCombo($data);
		$this->ProcessModelList($response,true,true)->ReturnData();
		return true;
	}

	/**
	 * Сохранение расчётных квот
	 */
	function saveLpuQuote()
	{
		$data = $this->ProcessInputData('saveLpuQuote', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveLpuQuote($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}


	/**
	 * Сохранение договора по сторонним специалистам с другими МО
	 */
	function saveLpuDispContract()
	{
		$data = $this->ProcessInputData('saveLpuDispContract', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveLpuDispContract($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}


	/**
	 * Сохранение данных паспорта МО
	 */
	function saveLpuPassport()
	{
		$data = $this->ProcessInputData('saveLpuPassport', true);
		if ($data === false) { return false; }

		if ( !empty($data['Lpu_endDate']) && ($data['Lpu_begDate'] > $data['Lpu_endDate']) ) {
			echo json_return_errors('Дата закрытия не может быть меньше даты начала работы');
			return false;
		}
		if ( ($data['Lpu_HasLocalPacsServer']==2) && (empty($data['Lpu_LocalPacsServerIP'])) ) {
			echo json_return_errors('Не указан IP-адрес локлаьного PACS-сервера');
			return false;
		}
		if ( ($data['Lpu_HasLocalPacsServer']==2) && (empty($data['Lpu_LocalPacsServerAetitle'])) ) {
			echo json_return_errors('Не указан AETITLE локлаьного PACS-сервера');
			return false;
		}
		if ( ($data['Lpu_HasLocalPacsServer']==2) && (empty($data['Lpu_LocalPacsServerPort'])) ) {
			echo json_return_errors('Не указан порт локлаьного PACS-сервера');
			return false;
		}
		if ( ($data['Lpu_HasLocalPacsServer']==2) && (empty($data['Lpu_LocalPacsServerWadoPort'])) ) {
			echo json_return_errors('Не указан wado-порт локлаьного PACS-сервера');
			return false;
		}

		$response = $this->dbmodel->saveLpuPassport($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}


	/**
	 *	Получение признака возможности интернет-модерации
	 */
	function getIsAllowInternetModeration()
	{
		$data = $this->ProcessInputData('getIsAllowInternetModeration',true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->getIsAllowInternetModeration($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *	Сохранение класса медицинского изделия
	 */
	function saveMedProductClass()
	{
		$data = $this->ProcessInputData('saveMedProductClass',true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->saveMedProductClass($data);
        $this->ProcessModelSave($response, true)->ReturnData();
        return true;

		//$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *	Сохранение связи с транспортными узлами
	 */
	function saveTransportConnect()
	{
		$data = $this->ProcessInputData('saveTransportConnect',true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->saveTransportConnect($data);
        $this->ProcessModelSave($response, true)->ReturnData();
        return true;

		//$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *  Получение списка связей с транспортными узлами
	 *  Входящие данные: сессия.
	 *  На выходе: JSON-строка со списком связей.
	 */
	function loadTransportConnect()
	{
		$data = $this->ProcessInputData('loadTransportConnect', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadTransportConnect($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *	Сохранение периода функционирования
	 */
	function saveFunctionTime()
	{
		$data = $this->ProcessInputData('saveFunctionTime',true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->saveFunctionTime($data);
        $this->ProcessModelSave($response, true)->ReturnData();
        return true;

		//$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *  Получение списка периодов функционирования
	 *  Входящие данные: сессия.
	 *  На выходе: JSON-строка со списком связей.
	 */
	function loadFunctionTime()
	{
		$data = $this->ProcessInputData('loadFunctionTime', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadFunctionTime($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Сохранение подстанции
	 */
	function saveCmpSubstation() {
		$data = $this->ProcessInputData('saveCmpSubstation',true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->saveCmpSubstation($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 * Удаления подстанции
	 */
	function deleteCmpSubstation() {
		$data = $this->ProcessInputData('deleteCmpSubstation',true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->deleteCmpSubstation($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка подстанций
	 */
	function loadCmpSubstationGrid() {
		$data = $this->ProcessInputData('loadCmpSubstationGrid',true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->loadCmpSubstationGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение  подстанции для редактирования
	 */
	function loadCmpSubstationForm() {
		$data = $this->ProcessInputData('loadCmpSubstationForm',true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->loadCmpSubstationForm($data);
		$this->ProcessModelList($response, true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка бригад
	 */
	function loadCmpEmergencyTeamGrid() {
		$data = $this->ProcessInputData('loadCmpEmergencyTeamGrid',true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->loadCmpEmergencyTeamGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * отчет мониторинга паспортов мед. организаций
	 */
	function exportLpuPassportReport() {
		$data = $this->ProcessInputData('exportLpuPassportReport', false);
		if ($data === false) {return false;}

		set_time_limit(0);

		$lpu_list = array();
		if ($data['Lpu_id'] > 0) {
			$lpu_list = $this->dbmodel->loadLpuListForReport(array('Lpu_id' => $data['Lpu_id']));
			if (empty($lpu_list[0]['Lpu_f003mcod'])) {
				DieWithError('Отсутсвует федеральный код МО');
			}
		} else {
			$lpu_list = $this->dbmodel->loadLpuListForReport();
		}

		$file_zip_sign = 'lpu_passport_report_'.time();
		$out_dir = $file_zip_sign;
		mkdir(EXPORTPATH_ROOT.'lpu_passport/' . $out_dir, 0777, true);

		$file_zip_name = EXPORTPATH_ROOT.'lpu_passport/' . $out_dir . "/" . $file_zip_sign . ".zip";
		$zip = new ZipArchive();
		$zip->open($file_zip_name, ZIPARCHIVE::CREATE);

		$this->load->library('textlog', array('file'=>'LpuPassport.log'));
		$this->textlog->add('start');

		foreach($lpu_list as $lpu) {
			if (empty($lpu['Lpu_f003mcod'])) {
				$this->textlog->add($lpu['Lpu_Nick'].': skip');
				continue;
			}
			$file_name = $lpu['Lpu_f003mcod'].'.xls';
			$birt_suffix = $this->usePostgre ? '_pg' : '';

			$url = (defined('BIRT_SERVLET_PATH_ABS')?BIRT_SERVLET_PATH_ABS:'')."/preview?__report=report/PassportLpu{$birt_suffix}.rptdesign&Lpu_id={$lpu['Lpu_id']}&__format=xls";

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
			curl_setopt($ch, CURLOPT_TIMEOUT, 100);

			$result=curl_exec($ch);
			if (!$result) {
				DieWithError(curl_error($ch));
			}
			curl_close($ch);

			$zip->AddFromString($file_name, $result);
			$this->textlog->add($lpu['Lpu_Nick'].': add');
		}

		$zip->close();
		$this->textlog->add('end');

		$response = array(array('succes' => true, 'Link' => $file_zip_name));
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 * Выгрузка паспортов МО
	 */
	function exportLpuPassportXml() {
		$data = $this->ProcessInputData('exportLpuPassportXml', false);
		if ($data === false) {return false;}

		set_time_limit(0);

		$config = $this->config->item('PMU');

		$LpuList = $this->dbmodel->getExportLpuPassportXmlData(array(
			'Lpu_id' => ($data['Lpu_id']>0)?$data['Lpu_id']:null
		));

		$response = array();
		if ($data['exportType'] == 1) {
			//Выгрузка xml
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $config['xmlurl']);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
			curl_setopt($ch, CURLOPT_TIMEOUT, 100);
			curl_setopt($ch, CURLOPT_USERPWD, 'swan:swan');

			$result=curl_exec($ch);
			if (!$result) {
				DieWithError(curl_error($ch));
			}
			curl_close($ch);

			//Приходится парсить html-страницу со списком файлов
			$dom = new DOMDocument();
			$res = $dom->loadHTML($result);
			$xpd = new DOMXPath($dom);

			$xpd->registerNamespace("php", "http://php.net/xpath");
			$xpd->registerPHPFunctions('preg_match');

			$arr = array();
			$file_name_list = array();
			foreach($LpuList as $Lpu) {
				$nick = urlencode(str_replace(' ', '_', $Lpu['Lpu_Nick']));
				$regex = '/^'.$nick.'_\d+\.xml$/';

				$a = $xpd->query("//a[php:functionString('preg_match', '{$regex}', @href) > 0]");

				if ($a->length > 0) {
					$file_name_list[] = urldecode($a->item($a->length-1)->getAttribute('href'));
				}
				$arr[] = array(
					urldecode($nick),
					urldecode($regex),
					$a->length
				);
			}

			if (count($file_name_list) == 0) {
				$response = array(array('success' => false, 'Error_Msg' => 'Отсутствуют xml-файлы по выбранным МО'));
			} else {
				$file_zip_sign = 'lpu_passport_xml_'.time();
				$out_dir = EXPORTPATH_ROOT.'lpu_passport/' . $file_zip_sign;
				mkdir($out_dir, 0777, true);
				$file_zip_name = $out_dir . "/" . $file_zip_sign . ".zip";
				$zip = new ZipArchive();
				$zip->open($file_zip_name, ZIPARCHIVE::CREATE);

				foreach($file_name_list as $file_name) {
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $config['xmlurl'].'/'.$file_name);
					curl_setopt($ch, CURLOPT_HEADER, 0);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
					curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
					curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
					curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
					curl_setopt($ch, CURLOPT_TIMEOUT, 100);
					curl_setopt($ch, CURLOPT_USERPWD, 'swan:swan');

					$result=curl_exec($ch);
					if (!$result) {
						DieWithError(curl_error($ch));
					}
					curl_close($ch);

					$zip->addFromString(iconv('UTF-8', 'cp866//IGNORE', $file_name), $result);
				}

				$zip->close();

				$response = array(array('success' => true, 'Msg' => '<a target="_blank" href="'.$file_zip_name.'">Скачать и сохранить файл</a>'));
			}
		} elseif($data['exportType'] == 2) {
			//Запуск сервиса
			$serviceURL = $config['replicatorurl'].'/rest/passport';

			$errors = array();
			foreach($LpuList as $Lpu) {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $serviceURL.'/'.$Lpu['Lpu_id']);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120 );
				curl_setopt($ch, CURLOPT_TIMEOUT, 120 );
				curl_setopt($ch, CURLOPT_FAILONERROR, true);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
				curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
				curl_setopt($ch, CURLOPT_TIMEOUT, 100);

				$result=curl_exec($ch);

				if (!$result) {
					$errors[] = "МО: '{$Lpu['Lpu_Nick']}'. ".curl_error($ch);
				} else {
					$resp = json_decode($result, true);
					if (!is_array($resp) || !array_key_exists('errorMessage', $resp)) {
						$errors[] = "МО: '{$Lpu['Lpu_Nick']}'. Ошибка запуска сервиса.";
					} if (!empty($resp['errorMessage']) && $resp['errorMessage'] != 'OK') {
						$errors[] = "МО: '{$Lpu['Lpu_Nick']}'.\n{$resp['errorMessage']}";
					}
				}
				curl_close($ch);
			}

			if (count($errors) > 0) {
				$out_dir = EXPORTPATH_ROOT.'lpu_passport/lpu_passport_xml_'.time();
				mkdir($out_dir, 0777, true);
				$errorFile = $out_dir . '/lpu_passport_errors.txt';

				file_put_contents($errorFile, implode("\n\n", $errors));

				$response = array(array('success' => true, 'Msg' => 'Выгрузка завершена. <a target="_blank" href="'.$errorFile.'">Скачать файл с текстом ошибок</a>'));
			} else {
				$response = array(array('success' => true, 'Msg' => 'Выгрузка завершена без ошибок'));
			}
		}

		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}


	/**
	 *  Получение кодов ОКАТО
	 *  На выходе: JSON-строка
	 *  Используется: форма добавления площадки занимаемой организацией
	 */
	function loadOKATOList() {
		$data = $this->ProcessInputData('loadOKATOList', true, true);

		$response = $this->dbmodel->loadOKATOList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 *  Функция получения списка периодов, в которых МО может производить обслуживание населения на дому по стоматологическим профилям.
	 *  Входящие данные: сессия.
	 *  На выходе: JSON-строка со списком периодов.
	 */
	function loadLpuPeriodStom()
	{
		$data = $this->ProcessInputData('loadLpuPeriodStom', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadLpuPeriodStom($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Сохранение периода, в который МО может производить обслуживание населения на дому по стоматологическим профилям
	 */
	function saveLpuPeriodStom()
	{
		$data = $this->ProcessInputData('saveLpuPeriodStom', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveLpuPeriodStom($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;

	}

	/**
	 *  Функция проверки дат действующих стом. лицензий МО
	 *  Входящие данные: сессия.
	 *  На выходе: JSON-строка со списком периодов.
	 */
	function checkLpuStomLicenceDates()
	{
		$data = $this->ProcessInputData('checkLpuStomLicenceDates', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkLpuStomLicenceDates($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *  Функция проверки класса медицинского изделия на наличие созданных медицинских изделий
	 */
	function checkMedProductCardHasClass()
	{
		$data = $this->ProcessInputData('checkMedProductCardHasClass', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkMedProductCardHasClass($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Получение списка тарифов МО
	 */
	function getLpuTariffClassList()
	{
		$data = $this->ProcessInputData('getLpuTariffClassList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getLpuTariffClassList($data['Lpu_oid'], $data['Date']);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	* Проверка адреса пациента на соответствие зонам обслуживания МО
	*/
	function checkOrgServiceTerr()
	{
		$data = $this->ProcessInputData('checkOrgServiceTerr', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkOrgServiceTerr($data);
		//return $response;
		//var_dump($response);die;
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *  Функция получения списка зданий МО.
	 */
	function LpuBuildingPassList()
	{
		$data = $this->ProcessInputData('LpuBuildingPassList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->LpuBuildingPassList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Метод для получения данных обо всех филиалах МО по Lpu_id или некоторых с учетом фильтров. Для отображения в гриде
	 * input_parameters [Lpu_id; optional: LpuFilial_begDate, LpuFilial_endDate, LpuFilial_Name, LpuFilial_Nick]
	 *
	 * @return JSON | bool
	 * @throws Exception
	 */
	function getLpuFilialGrid()
	{
		$data = $this->ProcessInputData('getLpuFilialGrid', false);

		$response = $this->dbmodel->getLpuFilialGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Функция для получения одной записи из таблицы филиалов
	 * input_parameters [LpuFilial_id]
	 *
	 * @return JSON
	 */
	function getLpuFilialRecord()
	{
		$data = $this->ProcessInputData('getLpuFilialRecord', false);

		$response = $this->dbmodel->getLpuFilialRecord($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Функция для сохранения (вставки или обновления) записи в таблицу LpuFilial
	 * input_params = [Lpu_id, LpuFilial_Name, LpuFilial_Nick, LpuFilial_Code, LpuFilial_begDate, Oktmo_id; optional: LpuFilial_id, LpuFilial_endDate]
	 * session_params = [pmUser_id]
	 *
	 * @return JSON
	 */
	function saveLpuFilialRecord()
	{
		$data = $this->ProcessInputData('saveLpuFilialRecord', true);

		$response = $this->dbmodel->saveLpuFilialRecord($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	 * Метод удаляет запись филиала из таблицы LpuFilial_id
	 * input_params = [Lpu_id]
	 */
	function deleteLpuFilialRecord()
	{
		$data = $this->ProcessInputData('deleteLpuFilialRecord', false);

		$response = $this->dbmodel->deleteLpuFilialRecord($data);

		$this->ProcessModelSave( $response, true )->ReturnData();
	}

	/**
	 * Загрузка грида "Услуга договора" формы "Договор по сторонним специалистам" (Паспорт МО)
	 */
	function loadServiceContract()
	{
		$data = $this->ProcessInputData('loadServiceContract', false);
		$response = $this->dbmodel->loadServiceContract($data);
		
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 *  Функция получения данных классификации МИ (форма 30).
	 */
	function loadMedProductClassForm() 
	{
		$data = $this->ProcessInputData('loadMedProductClassForm', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadMedProductClassForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *  Функция получения данных о типах медицинских изделий для формы добавления класса медицинского изделия .
	 */
	function loadCardTypeList()
	{
		$response = $this->dbmodel->loadCardTypeList();
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
}

?>
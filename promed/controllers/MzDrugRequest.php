<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* Контроллер для объектов Справочник медикаментов: заявки по медикаментам
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2011 Swan Ltd.
* @author       ModelGenerator
* @version
* @property MzDrugRequest_model MzDrugRequest_model
*/

class MzDrugRequest extends swController {

	/**
	 * Конструктор
	 */
	function __construct(){
		parent::__construct();
		$this->inputRules = array(
			'addDrugRequestPersonOrderMissingPerson' => array(
				array('field' => 'DrugRequest_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id')
			),
			'saveDrugRequest' => array(
				array('field' => 'DrugRequest_id', 'label' => 'Идентификатор заявки', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugRequestCategory_id', 'label' => 'Идентификатор категории', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugRequestCategory_Code', 'label' => 'Код категории', 'rules' => '', 'type' => 'string'),
				array('field' => 'DrugRequestStatus_id', 'label' => 'Идентификатор статуса', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugRequestStatus_Code', 'label' => 'Код статуса', 'rules' => '', 'type' => 'string'),
				array('field' => 'DrugRequest_Name', 'label' => 'Наименование заявки', 'rules' => '', 'type' => 'string'),
				array('field' => 'DrugRequestPeriod_id', 'label' => 'Рабочий период', 'rules' => '', 'type' => 'id'),
				array('field' => 'PersonRegisterType_id', 'label' => 'Тип регистра', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugGroup_id', 'label' => 'Группа медикаментов', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugRequestKind_id', 'label' => 'Тип заявки', 'rules' => '', 'type' => 'id'),
				array('field' => 'Lpu_id', 'label' => 'ЛПУ', 'rules' => '', 'type' => 'id'),
				array('field' => 'LpuUnit_id', 'label' => 'Группа отделений', 'rules' => '', 'type' => 'id'),
				array('field' => 'LpuRegion_id', 'label' => 'Участок', 'rules' => '', 'type' => 'id'),
				array('field' => 'LpuSection_id', 'label' => 'Отделение', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedPersonal_id', 'label' => 'Врач', 'rules' => '', 'type' => 'id')
			),
			'saveDrugRequestLpuRegion' => array(
				array('field' => 'DrugRequest_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'LpuRegion_id', 'label' => 'Идентификатор участка', 'rules' => 'required', 'type' => 'id')
			),
			'saveDrugRequestStatus' => array(
				array('field' => 'DrugRequest_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'DrugRequestStatus_Code', 'label' => 'Код статуса', 'rules' => 'required', 'type' => 'string')
			),
			'saveDrugRequestRegion' => array(
				array(
					'field' => 'Server_id',
					'label' => 'источник данных',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DrugRequest_id',
					'label' => 'идентификатор',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DrugRequestPeriod_id',
					'label' => 'идентификатор справочника медикаментов: период заявки',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'DrugRequestStatus_id',
					'label' => 'идентификатор справочника медикаментов: статус заявки',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DrugRequest_Name',
					'label' => 'наименование',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'идентификатор справочника ЛПУ',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'идентификатор справочника отделений ЛПУ',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'MedPersonal_id',
					'label' => 'идентификатор справочника медицинских работников',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DrugRequest_Summa',
					'label' => 'сумма по строке заявки',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'DrugRequest_YoungChildCount',
					'label' => 'количество прикрепленных детей по заявке',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'PersonRegisterType_id',
					'label' => 'Тип регистра',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DrugRequest_IsSigned',
					'label' => 'Признак подписания документа',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'pmUser_signID',
					'label' => 'Пользователь, подписавший документ',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DrugRequest_signDT',
					'label' => 'Дата подписания',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'DrugRequest_Version',
					'label' => 'Версия документа',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DrugRequestKind_id',
					'label' => 'Вид заявки',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DrugRequestProperty_id',
					'label' => 'Список медикаментов федеральной льготы',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DrugRequestPropertyFed_id',
					'label' => 'Список медикаментов федеральной льготы',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DrugRequestPropertyReg_id',
					'label' => 'Список медикаментов региональной льготы',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DrugGroup_id',
					'label' => 'Группа медикаментов',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugRequestQuota_Person',
					'label' => 'Лимит финансирования на одного льготника',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'DrugRequestQuota_PersonFed',
					'label' => 'Лимит финансирования на одного льготника',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'DrugRequestQuota_PersonReg',
					'label' => 'Лимит финансирования на одного льготника',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'DrugRequestQuota_Total',
					'label' => 'Лимит финансирования по заявке в целом',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'DrugRequestQuota_TotalFed',
					'label' => 'Лимит финансирования по заявке в целом',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'DrugRequestQuota_TotalReg',
					'label' => 'Лимит финансирования по заявке в целом',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'DrugRequestQuota_IsPersonalOrderObligatory',
					'label' => 'Признак обязательности наличия персональной разнарядки',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'MedPersonalList_JsonData',
					'label' => 'Список врачей',
					'rules' => '',
					'type' => 'string'
				)
			),
            'saveDrugRequestMo' => array(
                array('field' => 'RegionDrugRequest_id', 'label' => 'Идентификатор заявочной кампании', 'rules' => 'required', 'type' => 'id'),
                array('field' => 'Lpu_id', 'label' => 'Идентификатор ЛПУ', 'rules' => 'required', 'type' => 'id')
            ),
			'load' => array(
				array(
					'field' => 'DrugRequest_id',
					'label' => 'идентификатор',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'loadConsolidatedDrugRequest' => array(
				array(
					'field' => 'DrugRequest_id',
					'label' => 'Идентификатор заявки',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'loadDrugRequestLpuRegion' => array(
				array(
					'field' => 'DrugRequest_id',
					'label' => 'Идентификатор заявки',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadRegionList' => array(
				array(
					'field' => 'Year',
					'label' => 'Год',
					'rules' => '',
					'type' => 'int'
				),
                array(
                    'field' => 'DrugRequestProperty_Org_id',
                    'label' => 'Организация координатора',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'fromLpuPharmacyHead',
                    'label' => 'флаг источника запроса',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
					'field' => 'DrugRequestKind_id',
					'label' => 'Вид заявки',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DrugGroup_id',
					'label' => 'Группа медикаментов',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugRequestStatus_id',
					'label' => 'Статус заявки',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Coordinator_id',
					'label' => 'Координатор',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugRequest_Summa1',
					'label' => 'Сумма завяки (от)',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DrugRequest_Summa2',
					'label' => 'Сумма завяки (до)',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadMoList' => array(
				array(
					'field' => 'KLArea_id',
					'label' => 'Территория',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DrugRequestStatus_id',
					'label' => 'Статус заявки',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLAreaStat_id',
					'label' => 'Территория',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DrugRequestPeriod_id',
					'label' => 'рабочий период',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'PersonRegisterType_id',
					'label' => 'Тип регистра',
					'rules' => '',
					'type' => 'int',
					'default' => -1
				),
				array(
					'field' => 'DrugRequestKind_id',
					'label' => 'Тип заявки',
					'rules' => '',
					'type' => 'int',
					'default' => -1
				),
				array(
					'field' => 'DrugGroup_id',
					'label' => 'Группа медикаментов',
					'rules' => '',
					'type' => 'int',
					'default' => -1
				),
                array(
                    'field' => 'DrugRequest_Version',
                    'label' => 'Версия документа',
                    'rules' => '',
                    'type' => 'int'
                ),
				array(
					'field' => 'OrgServiceTerr_Org_id',
					'label' => 'Организация территории обслуживания',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadMPList' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'ЛПУ',
					'rules' => 'required',
					'type' => 'int'
				),

				array(
					'field' => 'LpuUnit_id',
					'label' => 'Группа отделений',
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
					'field' => 'DrugRequestStatus_id',
					'label' => 'Статус заявки',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'KLAreaStat_id',
					'label' => 'Территория',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DrugRequestPeriod_id',
					'label' => 'рабочий период',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'PersonRegisterType_id',
					'label' => 'Тип регистра',
					'rules' => '',
					'type' => 'int',
					'default' => -1
				),
				array(
					'field' => 'DrugRequestKind_id',
					'label' => 'Тип заявки',
					'rules' => '',
					'type' => 'int',
					'default' => -1
				),
				array(
					'field' => 'DrugGroup_id',
					'label' => 'Группа медикаментов',
					'rules' => '',
					'type' => 'int',
					'default' => -1
				),
                array(
                    'field' => 'DrugRequest_Version',
                    'label' => 'Версия документа',
                    'rules' => '',
                    'type' => 'int'
                ),
				array(
					'field' => 'DrugFinance_id',
					'label' => 'Финансирование',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Врач',
					'rules' => '',
					'type' => 'id'
				)
			),
			'loadList' => array(
				array(
					'field' => 'Server_id',
					'label' => 'источник данных',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DrugRequest_id',
					'label' => 'идентификатор',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DrugRequestPeriod_id',
					'label' => 'идентификатор справочника медикаментов: период заявки',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'PersonRegisterType_id',
					'label' => 'Тип регистра',
					'rules' => '',
					'type' => 'int',
					'default' => -1
				),
				array(
					'field' => 'DrugRequestStatus_id',
					'label' => 'идентификатор справочника медикаментов: статус заявки',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DrugRequest_Name',
					'label' => 'наименование',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'идентификатор справочника ЛПУ',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'идентификатор справочника отделений ЛПУ',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'MedPersonal_id',
					'label' => 'идентификатор справочника медицинских работников',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DrugRequest_Summa',
					'label' => 'сумма по строке заявки',
					'rules' => '',
					'type' => 'money'
				),
				array(
					'field' => 'DrugRequest_YoungChildCount',
					'label' => 'количество прикрепленных детей по заявке',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DrugRequest_IsSigned',
					'label' => 'Признак подписания документа',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'pmUser_signID',
					'label' => 'Пользователь, подписавший документ',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DrugRequest_signDT',
					'label' => 'Дата подписания',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'DrugRequest_Version',
					'label' => 'Версия документа',
					'rules' => '',
					'type' => 'int'
				)
			),
			'delete' => array(
				array(
					'field' => 'id',
					'label' => 'идентификатор',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'loadMedPersonalList' => array(
				array(
					'field' => 'DrugRequestPeriod_id',
					'label' => 'Рабочий период',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'PersonRegisterType_id',
					'label' => 'Тип заболевания',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugRequestKind_id',
					'label' => 'Тип заявки',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugGroup_id',
					'label' => 'Группа медикаментов',
					'rules' => '',
					'type' => 'id'
				)
			),
			'loadLpuList' => array(
				array(
					'field' => 'DrugRequestPeriod_id',
					'label' => 'Рабочий период',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'PersonRegisterType_id',
					'label' => 'Тип заболевания',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugRequestKind_id',
					'label' => 'Тип заявки',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugGroup_id',
					'label' => 'Группа медикаментов',
					'rules' => '',
					'type' => 'id'
				)
			),
			'loadLpuSelectList' => array(
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
					'field' => 'Lpu_id',
					'label' => 'ЛПУ',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuSectionProfile_id',
					'label' => 'Профиль',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'PostMed_id',
					'label' => 'Должность',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'begDate',
					'label' => 'Начало периода',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'endDate',
					'label' => 'Окончание периода',
					'rules' => '',
					'type' => 'date'
				),
                array(
                    'field' => 'WorkData_IsResponsible',
                    'label' => 'Признак наличия ответственного по ЛЛО',
                    'rules' => '',
                    'type' => 'int'
                )
			),
			'loadMedPersonalSelectList' => array(
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
					'field' => 'Lpu_id',
					'label' => 'ЛПУ',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuSectionProfile_id',
					'label' => 'Профиль',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'PostMed_id',
					'label' => 'Должность',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuRegionType_id',
					'label' => 'Тип участка',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'WorkData_IsDlo',
					'label' => 'Признак врача ЛЛО',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'begDate',
					'label' => 'Начало периода',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'endDate',
					'label' => 'Окончание периода',
					'rules' => '',
					'type' => 'date'
				)
			),
			'changeDrugRequestStatus' => array(
				array(
					'field' => 'DrugRequest_id',
					'label' => 'Идентификатор заявки',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'DrugRequestStatus_Code',
					'label' => 'Код статуса заявки',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'createDrugRequestRegionFirstCopy' => array(
				array(
					'field' => 'DrugRequest_id',
					'label' => 'Идентификатор заявки',
					'rules' => 'required',
					'type' => 'id'
				),
                array(
                    'field' => 'check_status',
                    'label' => 'Признак проверки статуса',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'check_consolidated_request',
                    'label' => 'Признак проверки наличия сводной заявки',
                    'rules' => '',
                    'type' => 'int'
                )
            ),
			'deleteDrugRequestRegionFirstCopy' => array(
				array(
					'field' => 'DrugRequest_id',
					'label' => 'Идентификатор заявки',
					'rules' => 'required',
					'type' => 'id'
				)
            ),
			'createDrugRequestArchiveCopy' => array(
				array(
					'field' => 'DrugRequest_id',
					'label' => 'Идентификатор заявки',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugRequestPeriod_id',
					'label' => 'Идентификатор рабочего периода заявки',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор ЛПУ',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getArchiveCopyDifferencesProtocol' => array(
				array(
					'field' => 'DrugRequest_id',
					'label' => 'Идентификатор заявки',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'saveDrugRequestPurchaseSpecParams' => array(
				array(
					'field' => 'DrugRequestPurchaseSpec_id',
					'label' => 'Идентификатор строки спецификации',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'DrugRequestPurchaseSpec_pKolvo',
					'label' => 'Количество к закупу',
					'rules' => '',
					'type' => 'float'
				)
			),
			'saveDrugRequestPlanParams' => array(
				array(
					'field' => 'DrugRequest_id',
					'label' => 'Идентификатор заявки',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'field',
					'label' => 'Поле',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'value',
					'label' => 'Значение',
					'rules' => '',
					'type' => 'string'
				)
			),
			/*'calculateDrugRequestPlanParams' => array(
				array(
					'field' => 'DrugRequest_list',
					'label' => 'Список заявок',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'object',
					'label' => 'Объект расчета',
					'rules' => 'required',
					'type' => 'string'
				)
			),*/
			'calculateDrugRequestPlanRegionParams' => array(
                array('field' => 'RegionDrugRequest_id', 'label' => 'Идентификатор заявочной кампании', 'rules' => 'required', 'type' => 'id'),
                array('field' => 'mode', 'label' => 'Режим расчета', 'rules' => 'required', 'type' => 'string')
			),
            'calculateDrugRequestPlanLpuParams' => array(
                array('field' => 'Lpu_id', 'label' => 'Идентификатор ЛПУ', 'rules' => 'required', 'type' => 'id'),
                array('field' => 'DrugRequestPeriod_id', 'label' => 'Рабочий период', 'rules' => 'required', 'type' => 'id'),
                array('field' => 'PersonRegisterType_id', 'label' => 'Тип заболевания', 'rules' => '', 'type' => 'id'),
                array('field' => 'DrugRequestKind_id', 'label' => 'Тип заявки', 'rules' => '', 'type' => 'id'),
                array('field' => 'DrugGroup_id', 'label' => 'Группа медикаментов', 'rules' => '', 'type' => 'id'),
                array('field' => 'DrugRequest_Version', 'label' => 'Версия документа', 'rules' => '', 'type' => 'int'),
				array('field' => 'status_check_disabled', 'label' => 'Флаг отмены проверки статуса', 'rules' => '', 'type' => 'string'),
				array('field' => 'background_mode_enabled', 'label' => 'Флаг', 'rules' => '', 'type' => 'string')
			),
            'calculateDrugRequestPlanLpuRegionParams' => array(
                array('field' => 'LpuRegionDrugRequest_id', 'label' => 'Идентификатор заявки участка', 'rules' => '', 'type' => 'id'),
                array('field' => 'RegionDrugRequest_id', 'label' => 'Идентификатор заявочной кампании', 'rules' => '', 'type' => 'id'),
                array('field' => 'status_check_disabled', 'label' => 'Флаг отмены проверки статуса', 'rules' => '', 'type' => 'string'),
                array('field' => 'background_mode_enabled', 'label' => 'Флаг', 'rules' => '', 'type' => 'string')
			),
            'calculateDrugRequestSum' => array(
                array('field' => 'DrugRequest_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id')
			),
			'loadConsolidatedDrugRequestList' => array(
				array(
					'field' => 'ConsolidatedDrugRequest_begDate',
					'label' => 'Рабочий период: дата начала',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'ConsolidatedDrugRequest_endDate',
					'label' => 'Рабочий период: дата окончания',
					'rules' => '',
					'type' => 'date'
				),
                array('field' => 'start', 'label' => 'Начальный номер записи', 'rules' => '', 'type' => 'int', 'default' => 0),
                array('field' => 'limit', 'label' => 'Количество возвращаемых записей', 'rules' => '', 'type' => 'int', 'default' => 100)
			),
			'loadConsolidatedDrugRequestRowList' => array(
				array(
					'field' => 'DrugRequestPurchaseSpec_id',
					'label' => 'Идентификатор позиции сводной заявки',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'loadConsolidatedDrugRequestSourceList' => array(),
			'loadConsolidatedRegionDrugRequestList' => array(
                array('field' => 'DrugRequest_id', 'label' => 'Идентификатор cводной заявки', 'rules' => '', 'type' => 'id')
            ),
			'loadDrugRequestPurchaseSpecSumList' => array(
				array(
					'field' => 'DrugRequest_id',
					'label' => 'Идентификатор заявки',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'RlsClsatc_id',
					'label' => 'Идентификатор АТХ',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'RlsClsPhGrLimp_id',
					'label' => 'Идентификатор фармгруппы ЖНВЛС',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Drug_Name',
					'label' => 'Наименование медикамента',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'DrugFinance_id',
					'label' => 'Идентификатор источника финансирования',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadDrugRequestPurchaseSpecList' => array(
				array(
					'field' => 'DrugRequest_id',
					'label' => 'Идентификатор заявки',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DrugComplexMnn_id',
					'label' => 'Идентификатор комплексного МНН',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'TRADENAMES_id',
					'label' => 'Идентификатор торгового наименования',
					'rules' => '',
					'type' => 'int',
                    'default' => 0
				),
				array(
					'field' => 'Evn_id',
					'label' => 'Идентификатор протоковла ВК',
					'rules' => '',
					'type' => 'int',
                    'default' => 0
				),
				array(
					'field' => 'DrugFinance_id',
					'label' => 'Идентификатор источника финансирования',
					'rules' => '',
					'type' => 'id'
				)
			),
			'saveConsolidatedDrugRequest' => array(
				array(
					'field' => 'DrugRequest_id',
					'label' => 'Идентификатор заявки',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'FinYear',
					'label' => 'Финансовый год',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'PersonRegisterType_id',
					'label' => 'Тип регистра пациентов',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugGroup_id',
					'label' => 'Группа медикаментов',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugRequest_Name',
					'label' => 'Наименование заявки',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'SelectedRequest_List',
					'label' => 'Список заявок ЛЛО',
					'rules' => 'required',
					'type' => 'string'
				)
			),
			'recalculateDrugRequestByFin' => array(
				array('field' => 'RegionDrugRequest_id', 'label' => 'Идентификатор заявочной кампании', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Lpu_List', 'label' => 'Список МО', 'rules' => '', 'type' => 'string'),
				array('field' => 'status_change_disabled', 'label' => 'Флаг', 'rules' => '', 'type' => 'string'),
				array('field' => 'dr_sum_recalculate_disabled', 'label' => 'Флаг', 'rules' => '', 'type' => 'string'),
				array('field' => 'background_mode_enabled', 'label' => 'Флаг', 'rules' => '', 'type' => 'string')
			),
			'recalculateDrugRequestByPersonOrderKolvo' => array(
				array(
					'field' => 'RegionDrugRequest_id',
					'label' => 'Идентификатор заявочной кампании',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'reCreateConsolidatedDrugRequest' => array(
				array(
					'field' => 'DrugRequest_id',
					'label' => 'Идентификатор сводной заявки',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'deleteConsolidatedDrugRequest' => array(
				array(
					'field' => 'id',
					'label' => 'Идентификатор сводной заявки',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'closeDrugRequestPurchaseSpec' => array(
				array(
					'field' => 'Id_List',
					'label' => 'Список строк спецификации',
					'rules' => 'required',
					'type' => 'string'
				)
			),
			'openDrugRequestPurchaseSpec' => array(
				array(
					'field' => 'Id_List',
					'label' => 'Список строк спецификации',
					'rules' => 'required',
					'type' => 'string'
				)
			),
			'loadDrugComplexMnnCombo' => array(
				array('field' => 'DrugComplexMnn_id', 'label' => 'Идентификатор комплесного МНН', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugRequest_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'DrugFinance_id', 'label' => 'Идентификатор тип финансирования', 'rules' => '', 'type' => 'id'),
				array('field' => 'query', 'label' => 'Строка запроса из комбобокса', 'rules' => '', 'type' => 'string')
			),
			'loadTradenamesCombo' => array(
				array('field' => 'Tradenames_id', 'label' => 'Идентификатор торгового наименования', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugComplexMnn_id', 'label' => 'Идентификатор комплексного МНН', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugRequest_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'fromPersonOrder', 'label' => 'Флаг загрузки из персональной разнарядки', 'rules' => '', 'type' => 'id'),
				array('field' => 'query', 'label' => 'Строка запроса из комбобокса', 'rules' => '', 'type' => 'string')
			),
			'loadProtokolVKCombo' => array(
				array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'CauseTreatmentType_id', 'label' => 'Причина обращения', 'rules' => '', 'type' => 'int')
			),
			'loadDrugComplexMnnComboForSupply' => array(
				array(
					'field' => 'WhsDocumentProcurementRequest_id',
					'label' => 'Идентификатор лота',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'query',
					'label' => 'Строка запроса из комбобокса',
					'rules' => '',
					'type' => 'string'
				)
			),
			'loadRlsDrugComboForSupply' => array(
				array(
					'field' => 'Drug_id',
					'label' => 'Медикамент',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DrugComplexMnn_id',
					'label' => 'Комплексное МНН',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Tradenames_id',
					'label' => 'Торговое наименование',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'WhsDocumentProcurementRequest_id',
					'label' => 'Лот',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'query',
					'label' => 'Строка поиска',
					'rules' => '',
					'type' => 'string'
				)
			),
			'getLimitDataForRequestSelectWindow' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Лпу',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'PersonRegisterType_id',
					'label' => 'Тип регистра',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'DrugRequestPeriod_id',
					'label' => 'Рабочий период заявки',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'getNotice' => array(
				array(
					'field' => 'DrugRequest_id',
					'label' => 'идентификатор',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'event',
					'label' => 'Событие',
					'rules' => 'required',
					'type' => 'string'
				)
			),
			'setAutoDrugRequestStatus' => array(
				array(
					'field' => 'DrugRequest_id',
					'label' => 'идентификатор',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'loadRegionDrugRequestCombo' => array(
				array(
					'field' => 'mode',
					'label' => 'Режим',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'show_first_copy',
					'label' => 'Флаг отображения первых копий',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadMoDrugRequestCombo' => array(
				array('field' => 'RegionDrugRequest_id', 'label' => 'Идентификатор заявочной кампании', 'rules' => '', 'type' => 'id'),
				array('field' => 'Lpu_id', 'label' => 'Идентификатор ЛПУ', 'rules' => '', 'type' => 'id'),
				array('field' => 'query', 'label' => 'Строка поиска', 'rules' => '', 'type' => 'string')
			),
			'saveDrugRequestRowBuyDataFromJSON' => array(
				array(
					'field' => 'DrugRequestPurchaseSpec_id',
					'label' => 'Идентификатор позиции сводной заявки',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'JsonData',
					'label' => 'Строка с данными',
					'rules' => 'required',
					'type' => 'string'
				)
			),
			'getDrugRequestRowCount' => array(
				array(
					'field' => 'DrugRequest_id',
					'label' => 'Идентификатор заявки',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'createDrugRequestCopy' => array(
				array(
					'field' => 'DrugRequest_id',
					'label' => 'Идентификатор заявки',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'SourceDrugRequest_id',
					'label' => 'Идентификатор оригинальной заявки',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'createDrugRequestPersonList' => array(
				array(
					'field' => 'DrugRequest_id',
					'label' => 'Идентификатор заявки',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'createMzDrugRequestPersonList' => array(
				array(
					'field' => 'DrugRequest_id',
					'label' => 'Идентификатор заявки',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'createDrugRequestDrugCopy' => array(
				array(
					'field' => 'DrugRequest_id',
					'label' => 'Идентификатор заявки',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'SourceDrugRequest_id',
					'label' => 'Идентификатор оригинальной заявки',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'createMzDrugRequestDrugCopy' => array(
				array(
					'field' => 'DrugRequest_id',
					'label' => 'Идентификатор заявки',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'SourceDrugRequest_id',
					'label' => 'Идентификатор оригинальной заявки',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'loadSourceDrugRequestCombo' => array(
				array(
					'field' => 'DrugRequest_id',
					'label' => 'Идентификатор заявки',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'getMoRequestByMpRequest' => array(
				array(
					'field' => 'DrugRequest_id',
					'label' => 'Идентификатор заявки врача',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'getMoRequestStatusByParams' => array(
				array('field' => 'DrugRequestPeriod_id', 'label' => 'Идентификатор рабочего периода заявки', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'PersonRegisterType_id', 'label' => 'Идентификатор типа регистра', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugRequestKind_id', 'label' => 'Идентификатор типа заявки', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugRequest_Version', 'label' => 'Версия документа', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugGroup_id', 'label' => 'Идентификатор группы медикаментов', 'rules' => '', 'type' => 'id'),
				array('field' => 'Lpu_id', 'label' => 'Идентификатор ЛПУ', 'rules' => 'required', 'type' => 'id')
			),
			'getMoRequestPlanParamsByParams' => array(
				array('field' => 'DrugRequestPeriod_id', 'label' => 'Идентификатор рабочего периода заявки', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'PersonRegisterType_id', 'label' => 'Идентификатор типа регистра', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugRequestKind_id', 'label' => 'Идентификатор типа заявки', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugGroup_id', 'label' => 'Идентификатор группы медикаментов', 'rules' => '', 'type' => 'id'),
				array('field' => 'Lpu_id', 'label' => 'Идентификатор ЛПУ', 'rules' => 'required', 'type' => 'id')
			),
			'saveDrugRequestRow' => array(
				array('field' => 'DrugRequestRow_id', 'label' => 'Идентификатор строки заявки', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugRequest_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'DrugComplexMnn_id', 'label' => 'Идентификатор комплексного МНН', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'TRADENAMES_id', 'label' => 'Идентификатор торгового наименования', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugRequestRow_Kolvo', 'label' => 'Количество', 'rules' => '', 'type' => 'float'),
				array('field' => 'DrugRequestRow_Summa', 'label' => 'Сумма', 'rules' => '', 'type' => 'float'),
				array('field' => 'DrugFinance_id', 'label' => 'Источник финансирования', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'PersonRegisterType_id', 'label' => 'Тип заявки', 'rules' => '', 'type' => 'id')
			),
			'saveDrugRequestRowKolvo' => array(
				array('field' => 'DrugRequest_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'DrugComplexMnn_id', 'label' => 'Идентификатор комплексного МНН', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'TRADENAMES_id', 'label' => 'Идентификатор торгового наименования', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugRequestRow_Kolvo', 'label' => 'Количество', 'rules' => '', 'type' => 'float'),
				array('field' => 'DrugRequestRow_Price', 'label' => 'Цена', 'rules' => '', 'type' => 'float'),
				array('field' => 'DrugFinance_id', 'label' => 'Источник финансирования', 'rules' => '', 'type' => 'id')
			),
			'saveDrugRequestRowDose' => array(
				array(
					'field' => 'DrugRequestRow_id',
					'label' => 'Идентификатор строки заявки',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DrugRequestRow_DoseOnce',
					'label' => 'Разовая доза',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'DrugRequestRow_DoseDay',
					'label' => 'Дневная доза',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'DrugRequestRow_DoseCource',
					'label' => 'Курсовая доза',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Okei_oid',
					'label' => 'Единицы измерения разовой дозы',
					'rules' => '',
					'type' => 'id'
				)
			),
			'saveDrugRequestPersonOrder' => array(
				array('field' => 'DrugRequestPersonOrder_id', 'label' => 'Идентификатор строки разнарядки', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugRequest_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'DrugRequestFirstCopy_id', 'label' => 'Идентификатор "первой копии"', 'rules' => '', 'type' => 'id'),
				array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'MedPersonal_id', 'label' => 'Идентификатор врача', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugComplexMnn_id', 'label' => 'Идентификатор врача', 'rules' => '', 'type' => 'id'),
				array('field' => 'Tradenames_id', 'label' => 'Идентификатор врача', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugRequestPersonOrder_Kolvo', 'label' => 'Количество в выписанных рецептах', 'rules' => '', 'type' => 'float'),
				array('field' => 'DrugRequestPersonOrder_OrdKolvo', 'label' => 'Количество', 'rules' => '', 'type' => 'float'),
				array('field' => 'DrugRequestPersonOrder_begDate', 'label' => 'Дата включения', 'rules' => '', 'type' => 'date'),
				array('field' => 'DrugRequestPersonOrder_endDate', 'label' => 'Дата исключения', 'rules' => '', 'type' => 'date'),
				array('field' => 'DrugRequestExceptionType_id', 'label' => 'Причина исключения', 'rules' => '', 'type' => 'id'),
                array('field' => 'DrugFinance_id', 'label' => 'Источник финансирования', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnVK_id', 'label' => 'Идентификатор протокола ВК', 'rules' => '', 'type' => 'id')
			),
			'saveDrugRequestPersonOrderOrdKolvo' => array(
				array('field' => 'DrugRequestPersonOrder_id', 'label' => 'Идентификатор строки разнарядки', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'MedPersonal_id', 'label' => 'Идентификатор врача', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugFinance_id', 'label' => 'Источник финансирования', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugRequestPersonOrder_OrdKolvo', 'label' => 'Количество', 'rules' => 'required', 'type' => 'float'),
                array('field' => 'DrugRequestFirstCopy_id', 'label' => 'Идентификатор "первой копии"', 'rules' => '', 'type' => 'id')
			),
			'loadDrugRequestPlanPeriodList' => array(
				array(
					'field' => 'DrugRequestPeriod_id',
					'label' => 'Идентификатор рабочего периода',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadLpuCombo' => array(
				array('field' => 'Lpu_id', 'label' => 'Идентификатор ЛПУ', 'rules' => '', 'type' => 'id'),
				array('field' => 'query', 'label' => 'Строка поиска', 'rules' => '', 'type' => 'string'),
				array('field' => 'Date', 'label' => 'Дата актуальности ЛПУ', 'rules' => '', 'type' => 'date')
			),
			'loadLpuSectionCombo' => array(
				array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => '', 'type' => 'id'),
				array('field' => 'Lpu_id', 'label' => 'Идентификатор ЛПУ', 'rules' => '', 'type' => 'id'),
				array('field' => 'LpuUnit_id', 'label' => 'Идентификатор группы отделений', 'rules' => '', 'type' => 'id')
			),
			'loadLpuRegionCombo' => array(
				array('field' => 'LpuRegion_id', 'label' => 'Идентификатор участка', 'rules' => '', 'type' => 'id'),
				array('field' => 'Lpu_id', 'label' => 'Идентификатор ЛПУ', 'rules' => '', 'type' => 'id'),
				array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => '', 'type' => 'id')
			),
			'loadMedPersonalCombo' => array(
				array('field' => 'MedPersonal_id', 'label' => 'Идентификатор врача', 'rules' => '', 'type' => 'id'),
				array('field' => 'Lpu_id', 'label' => 'Идентификатор ЛПУ', 'rules' => '', 'type' => 'id'),
				array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => '', 'type' => 'id'),
				array('field' => 'LpuUnit_id', 'label' => 'Идентификатор группы отделений', 'rules' => '', 'type' => 'id')
			),
			'getDrugRequestData' => array(
				array('field' => 'DrugRequest_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id')
			),
			'getDrugRequestStatus' => array(
				array('field' => 'DrugRequest_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id')
			),
			'loadDrugRequestRow' => array(
				array('field' => 'DrugRequestRow_id', 'label' => 'Идентификатор строки заявки', 'rules' => 'required', 'type' => 'id')
			),
			'deleteDrugRequestRow' => array(
				array('field' => 'id', 'label' => 'Идентификатор строки заявки', 'rules' => 'required', 'type' => 'id')
			),
			'deleteDrugRequestPersonOrder' => array(
				array('field' => 'id', 'label' => 'Идентификатор строки разнарядки', 'rules' => 'required', 'type' => 'id')
			),
			'loadDrugRequestRowFactorList' => array(),
			'loadDrugRequestPersonOrder' => array(
				array('field' => 'DrugRequestPersonOrder_id', 'label' => 'Идентификатор строки разнарядки', 'rules' => 'required', 'type' => 'id')
			),
			'loadDrugRequestPersonOrderList' => array(
				array('field' => 'DrugRequest_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id')
			),
			'loadMzDrugRequestMoDrugGrid' => array(
				array('field' => 'DrugRequestPeriod_id', 'label' => 'Идентификатор рабочего периода заявки', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'PersonRegisterType_id', 'label' => 'Идентификатор типа регистра', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugRequestKind_id', 'label' => 'Идентификатор типа заявки', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugGroup_id', 'label' => 'Идентификатор группы медикаментов', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugFinance_id', 'label' => 'Идентификатор финансирования', 'rules' => '', 'type' => 'id'),
				array('field' => 'Lpu_id', 'label' => 'Идентификатор ЛПУ', 'rules' => '', 'type' => 'id'),
				array('field' => 'Tradenames_Name', 'label' => 'Торговое наименование', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'ClsDrugForms_Name', 'label' => 'Лекарственная форма', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'DrugComplexMnnName_Name', 'label' => 'МНН', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'DrugComplexMnnDose_Name', 'label' => 'Дозировка', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'DrugComplexMnnFas_Name', 'label' => 'Фасовка', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'start', 'label' => 'Начальный номер записи', 'rules' => '', 'type' => 'int', 'default' => 0),
				array('field' => 'limit', 'label' => 'Количество возвращаемых записей', 'rules' => '', 'type' => 'int', 'default' => 100)
			),
			'loadMzDrugRequestDrugGrid' => array(
				array('field' => 'DrugRequest_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'DrugRequestRow_updDateRange', 'label' => 'Период даты изменения', 'rules' => '', 'type' => 'daterange'),
				array('field' => 'Tradenames_Name', 'label' => 'Торговое наименование', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'ClsDrugForms_Name', 'label' => 'Лекарственная форма', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'DrugComplexMnnName_Name', 'label' => 'МНН', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'DrugComplexMnnDose_Name', 'label' => 'Дозировка', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'DrugComplexMnnFas_Name', 'label' => 'Фасовка', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'DrugFinance_id', 'label' => 'Идентификатор финансирования', 'rules' => '', 'type' => 'id'),
				array('field' => 'PersonRegisterType_id', 'label' => 'Тип заявки', 'rules' => '', 'type' => 'id'),
				array('field' => 'ShowDeleted', 'label' => 'Признак отображения удаленных записей', 'rules' => '', 'type' => 'int'),
				array('field' => 'ShowWithoutPerson', 'label' => 'Признак отображения записей без распределения по пациентам', 'rules' => '', 'type' => 'int'),
				array('field' => 'start', 'label' => 'Начальный номер записи', 'rules' => '', 'type' => 'int', 'default' => 0),
				array('field' => 'limit', 'label' => 'Количество возвращаемых записей', 'rules' => '', 'type' => 'int', 'default' => 100)
			),
			'loadMzDrugRequestDrugPersonGrid' => array(
				array('field' => 'DrugRequest_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'DrugComplexMnn_id', 'label' => 'Идентификатор МНН', 'rules' => '', 'type' => 'id'),
				array('field' => 'Tradenames_id', 'label' => 'Идентификатор торгового наименования', 'rules' => '', 'type' => 'id')
			),
			'loadMzDrugRequestDrugListGrid' => array(
				array('field' => 'DrugRequest_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'DrugRequestRow_updDateRange', 'label' => 'Период даты изменения', 'rules' => '', 'type' => 'daterange'),
				array('field' => 'Tradenames_Name', 'label' => 'Торговое наименование', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'ClsDrugForms_Name', 'label' => 'Лекарственная форма', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'DrugComplexMnnName_Name', 'label' => 'МНН', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'DrugComplexMnnDose_Name', 'label' => 'Дозировка', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'DrugComplexMnnFas_Name', 'label' => 'Фасовка', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'DrugFinance_id', 'label' => 'Идентификатор финансирования', 'rules' => '', 'type' => 'id'),
				array('field' => 'start', 'label' => 'Начальный номер записи', 'rules' => '', 'type' => 'int', 'default' => 0),
				array('field' => 'limit', 'label' => 'Количество возвращаемых записей', 'rules' => '', 'type' => 'int', 'default' => 100)
			),
			'loadMzDrugRequestPersonGrid' => array(
				array('field' => 'DrugRequest_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Person_SurName', 'label' => 'Фамилия', 'rules' => '', 'type' => 'string'),
				array('field' => 'Person_FirName', 'label' => 'Имя', 'rules' => '', 'type' => 'string'),
				array('field' => 'Person_SecName', 'label' => 'Отчество', 'rules' => '', 'type' => 'string'),
                array('field' => 'ShowPersonOnlyWthoutDrug', 'label' => 'Признак отображения пациентов без медикаментов', 'rules' => '', 'type' => 'int'),
				array('field' => 'start', 'label' => 'Начальный номер записи', 'rules' => '', 'type' => 'int', 'default' => 0),
				array('field' => 'limit', 'label' => 'Количество возвращаемых записей', 'rules' => '', 'type' => 'int', 'default' => 100)
			),
			'loadMzDrugRequestFirstCopyGrid' => array(
				array('field' => 'DrugRequest_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'DrugRequestFirstCopy_id', 'label' => 'Идентификатор "первой копии"', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Person_SurName', 'label' => 'Фамилия', 'rules' => '', 'type' => 'string'),
				array('field' => 'Person_FirName', 'label' => 'Имя', 'rules' => '', 'type' => 'string'),
				array('field' => 'Person_SecName', 'label' => 'Отчество', 'rules' => '', 'type' => 'string'),
                array('field' => 'ShowPersonOnlyWthoutDrug', 'label' => 'Признак отображения пациентов без медикаментов', 'rules' => '', 'type' => 'int'),
				array('field' => 'start', 'label' => 'Начальный номер записи', 'rules' => '', 'type' => 'int', 'default' => 0),
				array('field' => 'limit', 'label' => 'Количество возвращаемых записей', 'rules' => '', 'type' => 'int', 'default' => 100)
			),
			'loadMzDrugRequestPersonDrugGrid' => array(
				array('field' => 'DrugRequest_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => '', 'type' => 'id')
			),
			'loadMzDrugRequestFirstCopyDrugGrid' => array(
				array('field' => 'DrugRequest_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
                array('field' => 'DrugRequestFirstCopy_id', 'label' => 'Идентификатор "первой копии"', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => '', 'type' => 'id')
			),
			'getDrugRequestRowKolvoData'=> array(
				array('field' => 'DrugRequestRow_id', 'label' => 'Идентификатор строки заявки', 'rules' => 'required', 'type' => 'id')
			),
			'getDrugRequestPersonOrderContext'=> array(
				array('field' => 'DrugRequest_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id')
			),
			'getEvnReceptSumKolvoByParams'=> array(
				array('field' => 'DrugRequest_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'DrugComplexMnn_id', 'label' => 'Идентификатор комплексного МНН', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Tradenames_id', 'label' => 'Идентификатор торгового наименования', 'rules' => '', 'type' => 'id')
			),
			'loadDrugRequestPeriod' => array(
				array('field' => 'DrugRequestPeriod_id', 'label' => 'Идентификатор рабочего периода', 'rules' => 'required', 'type' => 'id')
			),
			'loadDrugRequestPlanDeliveryGrid'=> array(
				array('field' => 'DrugRequest_id', 'label' => 'Идентификатор заявки', 'rules' => '', 'type' => 'id'),
				array('field' => 'PeriodId_List', 'label' => 'Список идентификаторов периодов', 'rules' => 'required', 'type' => 'string')
			),
			'saveDrugRequestPlanDeliveryKolvo'=> array(
				array('field' => 'DrugRequest_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'DrugRequestPlanPeriod_id', 'label' => 'Идентификатор отчетного периода', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'DrugComplexMnn_id', 'label' => 'Идентификатор комплексного МНН', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Tradenames_id', 'label' => 'Идентификатор торгового наименования', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugRequestPlanDelivery_Kolvo', 'label' => 'Количество', 'rules' => '', 'type' => 'float')
			),
			'approveAllDrugRequestMo'=> array(
                array('field' => 'RegionDrugRequest_id', 'label' => 'Идентификатор заявочной кампании', 'rules' => 'required', 'type' => 'id'),
                array('field' => 'check_status', 'label' => 'Признак проверки статуса', 'rules' => '', 'type' => 'int'),
                array('field' => 'set_auto_status', 'label' => 'Признак автоматической установки статуса для заявки региона', 'rules' => '', 'type' => 'int')
			),
			'unapproveAllDrugRequestMo'=> array(
                array('field' => 'RegionDrugRequest_id', 'label' => 'Идентификатор заявочной кампании', 'rules' => 'required', 'type' => 'id'),
                array('field' => 'check_consolidated_request', 'label' => 'Признак проверки наличия сводной заявки', 'rules' => '', 'type' => 'int')
			),
            'loadDrugRequestExecList' => array(
                array('field' => 'DrugRequest_id', 'label' => 'Идентификатор сводной заявки', 'rules' => 'required', 'type' => 'id')
            ),
            'loadDrugRequestExecPurchaseList' => array(
                array('field' => 'DrugRequestPurchaseSpec_id', 'label' => 'Идентификатор строки сводной заявки', 'rules' => 'required', 'type' => 'id')
            ),
            'loadDrugRequestExecSourceList' => array(
                array('field' => 'DrugRequest_id', 'label' => 'Идентификатор сводной заявки', 'rules' => 'required', 'type' => 'id'),
                array('field' => 'Org_id', 'label' => 'Идентификатор организации закупщика', 'rules' => '', 'type' => 'id')
            ),
            'saveDrugRequestExecCount' => array(
                array('field' => 'DrugRequestExec_id', 'label' => 'Идентификатор строки данных', 'rules' => 'required', 'type' => 'id'),
                array('field' => 'DrugRequestPurchaseSpec_id', 'label' => 'Идентификатор строки заявки', 'rules' => 'required', 'type' => 'id'),
                array('field' => 'DrugRequestExec_Count', 'label' => 'Количество для закупа', 'rules' => '', 'type' => 'float'),
                array('field' => 'DrugRequestExec_SupplyCount', 'label' => 'Количество из ГК', 'rules' => '', 'type' => 'float')
            ),
            'saveDrugRequestExecFromJSON' => array(
                array('field' => 'DrugRequest_id', 'label' => 'Идентификатор сводной заявки', 'rules' => 'required', 'type' => 'id'),
                array('field' => 'json_str', 'label' => 'Строка данных', 'rules' => '', 'type' => 'string')
            ),
            'deleteDrugRequestExec' => array(
                array('field' => 'id', 'label' => 'Идентификатор строки', 'rules' => 'required', 'type' => 'id')
            ),
            'saveDrugRequestPurchaseSpecRefuseCount' => array(
                array('field' => 'DrugRequestPurchaseSpec_id', 'label' => 'Идентификатор строки заявки', 'rules' => 'required', 'type' => 'id'),
                array('field' => 'DrugRequestPurchaseSpec_RefuseCount', 'label' => 'Количество по отказу', 'rules' => '', 'type' => 'float')
            ),
            'saveDrugRequestQuotaTotal' => array(
                array('field' => 'DrugRequest_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
                array('field' => 'DrugRequestQuota_Total', 'label' => 'Общий лимит', 'rules' => '', 'type' => 'float')
            ),
            'loadWhsDocumentProcurementPriceLinkList' => array(
                array('field' => 'DrugRequestPurchaseSpec_id', 'label' => 'Идентификатор строки сводной заявки', 'rules' => '', 'type' => 'id'),
                array('field' => 'WhsDocumentProcurementRequestSpec_id', 'label' => 'Идентификатор строки лота', 'rules' => '', 'type' => 'id')
            ),
            'loadWhsDocumentProcurementPriceLinkSourceList' => array(
                array('field' => 'DrugRequestPurchaseSpec_id', 'label' => 'Идентификатор строки сводной заявки', 'rules' => '', 'type' => 'id'),
                array('field' => 'WhsDocumentProcurementRequestSpec_id', 'label' => 'Идентификатор строки лота', 'rules' => '', 'type' => 'id')
            ),
            'loadWhsDocumentCommercialOfferDrugList' => array(
                array('field' => 'DrugRequestPurchaseSpec_id', 'label' => 'Идентификатор строки сводной заявки', 'rules' => '', 'type' => 'id'),
                array('field' => 'WhsDocumentProcurementRequestSpec_id', 'label' => 'Идентификатор строки лота', 'rules' => '', 'type' => 'id'),
                array('field' => 'UserOrg_id', 'label' => 'Организация пользователя', 'rules' => '', 'type' => 'id')
            ),
            'loadWhsDocumentCommercialOfferDrugSourceList' => array(
                array('field' => 'DrugRequestPurchaseSpec_id', 'label' => 'Идентификатор строки сводной заявки', 'rules' => '', 'type' => 'id'),
                array('field' => 'WhsDocumentProcurementRequestSpec_id', 'label' => 'Идентификатор строки лота', 'rules' => '', 'type' => 'id'),
                array('field' => 'UserOrg_id', 'label' => 'Организация пользователя', 'rules' => '', 'type' => 'id')
            ),
            'loadWhsDocumentProcurementSupplySpecList' => array(
                array('field' => 'DrugRequestPurchaseSpec_id', 'label' => 'Идентификатор строки сводной заявки', 'rules' => '', 'type' => 'id'),
                array('field' => 'WhsDocumentProcurementRequestSpec_id', 'label' => 'Идентификатор строки лота', 'rules' => '', 'type' => 'id'),
                array('field' => 'UserOrg_id', 'label' => 'Организация пользователя', 'rules' => '', 'type' => 'id')
            ),
            'loadWhsDocumentProcurementSupplySpecSourceList' => array(
                array('field' => 'DrugRequestPurchaseSpec_id', 'label' => 'Идентификатор строки сводной заявки', 'rules' => '', 'type' => 'id'),
                array('field' => 'WhsDocumentProcurementRequestSpec_id', 'label' => 'Идентификатор строки лота', 'rules' => '', 'type' => 'id'),
                array('field' => 'UserOrg_id', 'label' => 'Организация пользователя', 'rules' => '', 'type' => 'id')
            ),
            'saveWhsDocumentProcurementPrice' => array(
                array('field' => 'DrugRequestPurchaseSpec_id', 'label' => 'Идентификатор строки сводной заявки', 'rules' => '', 'type' => 'id'),
                array('field' => 'WhsDocumentProcurementRequestSpec_id', 'label' => 'Идентификатор строки лота', 'rules' => '', 'type' => 'id'),
                array('field' => 'TariffJsonData', 'label' => 'Данные по тарифам', 'rules' => '', 'type' => 'string'),
                array('field' => 'OfferJsonData', 'label' => 'Данные по коммерческим предложениям', 'rules' => '', 'type' => 'string'),
                array('field' => 'SupplyJsonData', 'label' => 'Данные по контрактам', 'rules' => '', 'type' => 'string'),
                array('field' => 'CalculatPriceType_id', 'label' => 'Тип расчета цены', 'rules' => '', 'type' => 'id'),
                array('field' => 'TotalPrice', 'label' => 'Цена', 'rules' => '', 'type' => 'float'),
                array('field' => 'CalculationDate', 'label' => 'Дата рассчета цены', 'rules' => '', 'type' => 'date')
            ),
            'loadWhsDocumentProcurementPrice' => array(
                array('field' => 'DrugRequestPurchaseSpec_id', 'label' => 'Идентификатор строки сводной заявки', 'rules' => '', 'type' => 'id'),
                array('field' => 'WhsDocumentProcurementRequestSpec_id', 'label' => 'Идентификатор строки лота', 'rules' => '', 'type' => 'id')
            ),
            'getPersonPrivilegeData' => array(
                array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id')
            ),
            /*'checkDrugAmount' => array(
				array('field' => 'DrugComplexMnn_id', 'label' => 'Идентификатор комплесного МНН', 'rules' => '', 'type' => 'id'),
				array('field' => 'Tradenames_id', 'label' => 'Идентификатор торгового наименования', 'rules' => '', 'type' => 'id'),
				array('field' => 'DrugRequest_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'DrugRequestPersonOrder_OrdKolvo', 'label' => 'Количество медикамента', 'rules' => '', 'type' => 'float'),
				array('field' => 'DrugRequestPersonOrder_id', 'label' => 'Идентификатор разнарядки', 'rules' => '', 'type' => 'id')
			),*/
            'checkExistPersonInFirstCopy' => array(
				array('field' => 'DrugRequestFirstCopy_id', 'label' => 'Идентификатор "первой копии" заявки', 'rules' => 'required', 'type' => 'id'),
                array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id')
			),
            'checkExistPersonDrugInRegionFirstCopy' => array(
				array('field' => 'DrugRequestFirstCopy_id', 'label' => 'Идентификатор "первой копии" заявки', 'rules' => 'required', 'type' => 'id'),
                array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'DrugComplexMnn_id', 'label' => 'Идентификатор комплесного МНН', 'rules' => '', 'type' => 'id'),
				array('field' => 'Tradenames_id', 'label' => 'Идентификатор торгового наименования', 'rules' => '', 'type' => 'id')
			),
            'copyDrugRequestPlanToFirstCopy' => array(
				array('field' => 'RegionDrugRequest_id', 'label' => 'Идентификатор заявочной кампании', 'rules' => 'required', 'type' => 'id')
			)
		);
		$this->load->database();
		$this->load->model('MzDrugRequest_model', 'MzDrugRequest_model');
	}

	/**
	 * Функция добавления пациентов имеющих прикрепление к участку, но отстутсвующих в участковой заявке
	 */
	function addDrugRequestPersonOrderMissingPerson() {
		$data = $this->ProcessInputData('addDrugRequestPersonOrderMissingPerson', false);
		if ($data){
			$response = $this->MzDrugRequest_model->addDrugRequestPersonOrderMissingPerson($data);
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении строк разнарядки')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Сохранение заявки
	 */
	function saveDrugRequest() {
		$data = $this->ProcessInputData('saveDrugRequest', true);
		if ($data){
			//при необходимости восстанавливаем параметры по кодам
			if (empty($data['DrugRequestStatus_id']) && !empty($data['DrugRequestStatus_Code'])) {
				$data['DrugRequestStatus_id'] = $this->MzDrugRequest_model->getObjectIdByCode('DrugRequestStatus', $data['DrugRequestStatus_Code']);
			}
			if (empty($data['DrugRequestCategory_id']) && !empty($data['DrugRequestCategory_Code'])) {
				$data['DrugRequestCategory_id'] = $this->MzDrugRequest_model->getObjectIdByCode('DrugRequestCategory', $data['DrugRequestCategory_Code']);
			}

			//проверки при добавлении
			if (empty($data['DrugRequest_id'])) {
				//проверяем соответствуют ли даные категории заявки (пока только для заявок врачей)
				$response = $this->MzDrugRequest_model->checkDrugRequestCategory($data);
				if (!empty($response['Error_Msg'])) {
					$this->ReturnData(array('success' => false, 'Error_Code' => null, 'Error_Msg' => toUTF($response['Error_Msg'])));
					return true;
				}

				//проверяем нет ли уже заявки с заданными параметрами, если есть - возвращаем идентификатор
				$request_id = $this->MzDrugRequest_model->getDrugRequestIdByParams($data);
				if ($request_id > 0) {
					$this->ProcessModelSave(array('success' => true, 'DrugRequest_id' => $request_id, 'alreadyExist' => 1), true)->ReturnData();
					return true;
				}
			}

			$response = $this->MzDrugRequest_model->saveObject('DrugRequest', $data);

			//при необходимости производим перерасчет статусов родительских заявок
			if ($response && !empty($response['DrugRequest_id']) && empty($data['DrugRequest_id'])) {
				$this->MzDrugRequest_model->setServer_id($data['Server_id']);
				$this->MzDrugRequest_model->setpmUser_id($data['pmUser_id']);
				$res = $this->MzDrugRequest_model->setAutoDrugRequestStatus(array(
					'DrugRequest_id' => $response['DrugRequest_id']
				));
			}

			$this->ProcessModelSave($response, true, 'Ошибка при сохранении заявки')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Изменение статуса заявки
	 */
	function saveDrugRequestLpuRegion() {
		$data = $this->ProcessInputData('saveDrugRequestLpuRegion', false);
		if ($data){
            try {
            	//проверка статуса заявки
				$status_data = $this->MzDrugRequest_model->getDrugRequestStatus($data['DrugRequest_id']);
				if (!empty($status_data['DrugRequest_id'])) {
					if ($status_data['DrugRequestStatus_Code'] != '1') {
						throw new Exception('Недопустимый статус заявки');
					}
				} else {
					throw new Exception('Не удалось получить информацию о статусе заявки');
				}

				//проверка на существование заявки участка
				$check_data = $this->MzDrugRequest_model->checkDrugRequestLpuRegionExist($data);
				if (!empty($check_data['Error_Msg'])) {
					throw new Exception($check_data['Error_Msg']);
				}

				//проверка персональной разнарядки
				$check_data = $this->MzDrugRequest_model->checkDrugRequestPersonOrderByLpuRegion($data);
				if (!empty($check_data['Error_Msg'])) {
					throw new Exception($check_data['Error_Msg']);
				}
			} catch (Exception $e) {
            	$this->ReturnError($e->getMessage());
            	return false;
			}
			$response = $this->MzDrugRequest_model->saveObject('DrugRequest', $data);
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении заявки')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Изменение статуса заявки
	 */
	function saveDrugRequestStatus() {
		$warning_list = array();
		$session_data = getSessionParams();
		$data = $this->ProcessInputData('saveDrugRequestStatus', false);
		$data['pmUser_id'] = $session_data['pmUser_id'];
		if ($data){
			//получение категории заявки
			$category_data = $this->MzDrugRequest_model->getDrugRequestCategory($data['DrugRequest_id']);
			$category = !empty($category_data['DrugRequestCategory_id']) ? $category_data['DrugRequestCategory_SysNick'] : null;

			//проверки для заявок врачей и участков
			if ($category == 'vrach') {
				if (in_array($data['DrugRequestStatus_Code'], array('2', '3'))) { //2 - Сформированная; 3 - Утвержденная
					//проверка на наличие в заявке пациентов без медикаментов в рамках заявочной кампании
					$check_data = $this->MzDrugRequest_model->checkDrugRequestPersonOrderEmptyPerson($data);
					if (!empty($check_data['Error_Msg'])) {
						$this->ReturnError($check_data['Error_Msg']);
						return false;
					}
				}
				if ($data['DrugRequestStatus_Code'] == '2') { //2 - Сформированная
					//проверка на наличие медикаментов без распределения по пациентам
					$check_data = $this->MzDrugRequest_model->checkDrugRequestRowWithoutPerson($data);
					if (!empty($check_data['Error_Msg'])) {
						$warning_list[]  = $check_data['Error_Msg']; //выводим в виде предупреждения
					}

					//проверка на наличие в заявке пациентов без медикаментов в рамках заявочной кампании
					$check_data = $this->MzDrugRequest_model->checkDrugRequestPersonOrderUnattachedPerson($data);
					if (!empty($check_data['Error_Msg'])) {
						if (isset($check_data['Person_List'])) {
							$check_data['Error_Msg'] = 'Заявка не может быть сформирована, т .к. в заявке есть пациенты, не имеющие прикрепления к участковой заявке';
							$this->ReturnData(
								array(
									'Cancel_Error_Handle' => true,
									'success' => false,
									'Error_Code' => 0,
									'Error_Msg' => toUtf($check_data['Error_Msg']),
									'Error_Type' => 'drugrequest_forming_not_attach_persons',
									'Error_Data' => $check_data
								)
							);
							return false;
						} else {
							$this->ReturnError($check_data['Error_Msg']);
							return false;
						}
					}

					//проверка на наличие в заявке пациентов без медикаментов в рамках заявочной кампании
					$check_data = $this->MzDrugRequest_model->checkDrugRequestPersonOrderMissingPerson($data);
					if (!empty($check_data['Error_Msg'])) {
						if (isset($check_data['Person_List'])) {
							$check_data['Error_Msg'] = 'Заявка не может быть сформирована, так как  в разнарядку заявки участкового врача включены не все пациенты, для которых узкие специалисты заказали ЛС';
							$this->ReturnData(
								array(
									'Cancel_Error_Handle' => true,
									'success' => false,
									'Error_Code' => 0,
									'Error_Msg' => toUtf($check_data['Error_Msg']),
									'Error_Type' => 'drugrequest_forming_missing_persons',
									'Error_Data' => $check_data
								)
							);
							return false;
						} else {
							$this->ReturnError($check_data['Error_Msg']);
							return false;
						}
					}
				}
			}

            if (in_array($data['DrugRequestStatus_Code'], array('2', '3'))) { //2 - Сформированная; 3 - Утвержденная
                //проверка на наличие в заявке пациентов без медикаментов в рамках заявочной кампании
                $check_data = $this->MzDrugRequest_model->checkDrugRequestPersonOrderEmptyPerson($data);
                if (!empty($check_data['Error_Msg'])) {
                    $this->ReturnError($check_data['Error_Msg']);
                    return false;
                }
            }

			$data['DrugRequestStatus_id'] = $this->MzDrugRequest_model->getObjectIdByCode('DrugRequestStatus', $data['DrugRequestStatus_Code']);
			$response = $this->MzDrugRequest_model->saveObject('DrugRequest', $data);
			if ($response && !empty($response['DrugRequest_id'])) { //автообновление статуса
				$this->MzDrugRequest_model->setServer_id($session_data['Server_id']);
				$this->MzDrugRequest_model->setpmUser_id($session_data['pmUser_id']);
				$res = $this->MzDrugRequest_model->setAutoDrugRequestStatus($data);
			}
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении заявки');
			if (count($warning_list) > 0) {
				$this->OutData['CheckWarning_Msg'] = $warning_list[0]; //Warning_Msg уже используется для вывода всплывающего уведомления
			}
			$this->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Сохранение региональной заявки
	 */
	function saveDrugRequestRegion() {
		$data = $this->ProcessInputData('saveDrugRequestRegion', true);
		if ($data){
			if (isset($data['session']['server_id'])) {
				$this->MzDrugRequest_model->setServer_id($data['session']['server_id']);
			}
			if (isset($data['pmUser_id'])) {
				$this->MzDrugRequest_model->setpmUser_id($data['pmUser_id']);
			}
			if (isset($data['DrugRequest_id'])) {
				$this->MzDrugRequest_model->setDrugRequest_id($data['DrugRequest_id']);
			}
			if (isset($data['DrugRequestPeriod_id'])) {
				$this->MzDrugRequest_model->setDrugRequestPeriod_id($data['DrugRequestPeriod_id']);
			}
			if (isset($data['DrugRequestStatus_id'])) {
				$this->MzDrugRequest_model->setDrugRequestStatus_id($data['DrugRequestStatus_id']);
			}
			if (isset($data['DrugRequest_Name'])) {
				$this->MzDrugRequest_model->setDrugRequest_Name($data['DrugRequest_Name']);
			}
			if (isset($data['Lpu_id'])) {
				$this->MzDrugRequest_model->setLpu_id($data['Lpu_id']);
			}
			if (isset($data['LpuSection_id'])) {
				$this->MzDrugRequest_model->setLpuSection_id($data['LpuSection_id']);
			}
			if (isset($data['MedPersonal_id'])) {
				$this->MzDrugRequest_model->setMedPersonal_id($data['MedPersonal_id']);
			}
			if (isset($data['DrugRequest_Summa'])) {
				$this->MzDrugRequest_model->setDrugRequest_Summa($data['DrugRequest_Summa']);
			}
			if (isset($data['DrugRequest_YoungChildCount'])) {
				$this->MzDrugRequest_model->setDrugRequest_YoungChildCount($data['DrugRequest_YoungChildCount']);
			}
			if (isset($data['PersonRegisterType_id'])) {
				$this->MzDrugRequest_model->setPersonRegisterType_id($data['PersonRegisterType_id']);
			}
			if (isset($data['DrugRequest_IsSigned'])) {
				$this->MzDrugRequest_model->setDrugRequest_IsSigned($data['DrugRequest_IsSigned']);
			}
			if (isset($data['pmUser_signID'])) {
				$this->MzDrugRequest_model->setpmUser_signID($data['pmUser_signID']);
			}
			if (isset($data['DrugRequest_signDT'])) {
				$this->MzDrugRequest_model->setDrugRequest_signDT($data['DrugRequest_signDT']);
			}
			if (isset($data['DrugRequest_Version'])) {
				$this->MzDrugRequest_model->setDrugRequest_Version($data['DrugRequest_Version']);
			}
			if (isset($data['DrugRequestKind_id'])) {
				$this->MzDrugRequest_model->setDrugRequestKind_id($data['DrugRequestKind_id']);
			}
			if (isset($data['DrugRequestProperty_id'])) {
				$this->MzDrugRequest_model->setDrugRequestProperty_id($data['DrugRequestProperty_id']);
			}
			if (isset($data['DrugRequestPropertyFed_id'])) {
				$this->MzDrugRequest_model->setDrugRequestPropertyFed_id($data['DrugRequestPropertyFed_id']);
			}
			if (isset($data['DrugRequestPropertyReg_id'])) {
				$this->MzDrugRequest_model->setDrugRequestPropertyReg_id($data['DrugRequestPropertyReg_id']);
			}
			if (isset($data['DrugRequestQuota_Person'])) {
				$this->MzDrugRequest_model->setDrugRequestQuota_Person($data['DrugRequestQuota_Person']);
			}
			if (isset($data['DrugRequestQuota_PersonFed'])) {
				$this->MzDrugRequest_model->setDrugRequestQuota_PersonFed($data['DrugRequestQuota_PersonFed']);
			}
			if (isset($data['DrugRequestQuota_PersonReg'])) {
				$this->MzDrugRequest_model->setDrugRequestQuota_PersonReg($data['DrugRequestQuota_PersonReg']);
			}
			if (isset($data['DrugRequestQuota_Total'])) {
				$this->MzDrugRequest_model->setDrugRequestQuota_Total($data['DrugRequestQuota_Total']);
			}
			if (isset($data['DrugRequestQuota_TotalFed'])) {
				$this->MzDrugRequest_model->setDrugRequestQuota_TotalFed($data['DrugRequestQuota_TotalFed']);
			}
			if (isset($data['DrugRequestQuota_TotalReg'])) {
				$this->MzDrugRequest_model->setDrugRequestQuota_TotalReg($data['DrugRequestQuota_TotalReg']);
			}
			if (isset($data['DrugRequestQuota_Reserve'])) {
				$this->MzDrugRequest_model->setDrugRequestQuota_Reserve($data['DrugRequestQuota_Reserve']);
			}
			if (isset($data['DrugRequestQuota_IsPersonalOrderObligatory'])) {
				$this->MzDrugRequest_model->setDrugRequestQuota_IsPersonalOrderObligatory($this->MzDrugRequest_model->getObjectIdByCode('YesNo', $data['DrugRequestQuota_IsPersonalOrderObligatory']));
			}
			if (isset($data['DrugGroup_id'])) {
				$this->MzDrugRequest_model->setDrugGroup_id($data['DrugGroup_id']);
			}

			$err_msg = null;

			if (isset($data['DrugRequest_id']) && $data['DrugRequest_id'] > 0) {
				$err_msg = $this->MzDrugRequest_model->checkAllowedDrugRequestEdit(array(
					'DrugRequest_id' => $data['DrugRequest_id']
				));
			}

			if (empty($err_msg)) {
				$response = $this->MzDrugRequest_model->saveDrugRequestRegion();
				$this->ProcessModelSave($response, true, 'Ошибка при сохранении Справочник медикаментов: заявки по медикаментам')->ReturnData();

				if (!empty($data['MedPersonalList_JsonData']) && isset($data['DrugRequestPeriod_id']) && $data['DrugRequestPeriod_id'] > 0) {
					$response = $this->MzDrugRequest_model->saveLpuListFromJSON(array(
						'DrugRequestPeriod_id' => $data['DrugRequestPeriod_id'],
						'PersonRegisterType_id' => $data['PersonRegisterType_id'],
						'DrugRequestKind_id' => $data['DrugRequestKind_id'],
						'DrugGroup_id' => $data['DrugGroup_id'],
						'json_str' => $data['MedPersonalList_JsonData'],
						'pmUser_id' => $data['pmUser_id']
					));
				}

				$this->MzDrugRequest_model->createMoDrugRequst();

				if (isset($data['DrugRequestStatus_id']) && !empty($data['DrugRequestStatus_id']) && $data['DrugRequestStatus_id'] != 5) { //Если у заявки есть статус и он не равен "Нулевая"
					//Последобавления заявок МО необходимо пересмотреть статус заявки региона
					$this->MzDrugRequest_model->setAutoDrugRequestStatus(array(
						'category' => 'mo',
						'DrugRequestPeriod_id' => $data['DrugRequestPeriod_id'],
						'PersonRegisterType_id' => $data['PersonRegisterType_id'],
						'DrugRequestKind_id' => $data['DrugRequestKind_id'],
						'DrugGroup_id' => $data['DrugGroup_id']
					));
				}
			}

			if (!empty($err_msg)) {
				$this->ReturnData(array('success' => false, 'Error_Code' => null, 'Error_Msg' => toUTF($err_msg)));
			}
			return true;
		} else {
			return false;
		}
	}

    /**
     * Добавление заявки МО
     */
    function saveDrugRequestMo() {
        $session_data = getSessionParams();
        $data = $this->ProcessInputData('saveDrugRequestMo', false);
        $data['pmUser_id'] = $session_data['pmUser_id'];
        if ($data){
            //получаем наименование ЛПУ
            $lpu_name = '';
            $response =  $this->MzDrugRequest_model->loadLpuCombo(array('Lpu_id' => $data['Lpu_id']));
            if (isset($response[0]) && !empty($response[0]['Lpu_Name'])) {
                $lpu_name = $response[0]['Lpu_Name'];
            }

            //загружаем данные заявки региона
            $this->MzDrugRequest_model->setDrugRequest_id($data['RegionDrugRequest_id']);
            $request_data = $this->MzDrugRequest_model->load();

            if (is_array($request_data) && isset($request_data[0])) {
                $request_data = $request_data[0];

                //проверка на наличие подобной заявки
                $dbl_data = $this->MzDrugRequest_model->checkObjectDoubles('DrugRequest', array(
                    'DrugRequestPeriod_id' => $request_data['DrugRequestPeriod_id'],
                    'PersonRegisterType_id' => $request_data['PersonRegisterType_id'],
                    'DrugRequestKind_id' => $request_data['DrugRequestKind_id'],
                    'DrugGroup_id' => $request_data['DrugGroup_id'],
                    'Lpu_id' => $data['Lpu_id'],
                    'DrugRequestCategory_id' => $this->MzDrugRequest_model->getObjectIdByCode('DrugRequestCategory', 2) //2 - Заявка МО
                ));
                if ($dbl_data && !empty($dbl_data['DrugRequest_id'])) {
                    $this->ReturnError('Данная заявка уже существует');
                    return false;
                }

                //проверка на статус заявочной кампании
                if (!empty($request_data['DrugRequestStatus_Code']) && !in_array($request_data['DrugRequestStatus_Code'], array(1,4))) { //1 - Начальная; 4 - Нулевая
                    $this->ReturnError('Cтатус заявочной кампании должен быть «Начальная» или «Нулевая»');
                    return false;
                }
            }

            $response = $this->MzDrugRequest_model->copyObject('DrugRequest', array(
                'DrugRequest_id' => $data['RegionDrugRequest_id'],
                'Lpu_id' => $data['Lpu_id'],
                'DrugRequestCategory_id' => $this->MzDrugRequest_model->getObjectIdByCode('DrugRequestCategory', 2), //2 - Заявка МО
                'DrugRequest_Name' => "Заявка МО {$lpu_name}",
                'DrugRequestProperty_id' => null,
                'pmUser_id' => $session_data['pmUser_id']
            ));
            $this->ProcessModelSave($response, true, 'Ошибка при сохранении заявки')->ReturnData();
            return true;
        } else {
            return false;
        }
    }

	/**
	 * Загрузка
	 */
	function load() {
		$data = $this->ProcessInputData('load', true);
		if ($data){
			$this->MzDrugRequest_model->setDrugRequest_id($data['DrugRequest_id']);
			$response = $this->MzDrugRequest_model->load();
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка
	 */
	function loadConsolidatedDrugRequest() {
		$data = $this->ProcessInputData('loadConsolidatedDrugRequest', true);
		if ($data){
			$this->MzDrugRequest_model->setDrugRequest_id($data['DrugRequest_id']);
			$response = $this->MzDrugRequest_model->loadConsolidatedDrugRequest();
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка данных для формы редактирования участка заявки
	 */
	function loadDrugRequestLpuRegion() {
		$data = $this->ProcessInputData('loadDrugRequestLpuRegion', false);
		if ($data){
			$this->MzDrugRequest_model->setDrugRequest_id($data['DrugRequest_id']);
			$response = $this->MzDrugRequest_model->loadDrugRequestLpuRegion();
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка
	 */
	function loadList() {
		$data = $this->ProcessInputData('loadList', true);
		if ($data) {
			$filter = $data;
			$response = $this->MzDrugRequest_model->loadList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка заявок региона
	 */
	function loadRegionList() {
		$data = $this->ProcessInputData('loadRegionList', true);
		if ($data) {
			$filter = $data;
			$filter['list_type'] = 'region';
			$response = $this->MzDrugRequest_model->loadList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка заявок МО
	 */
	function loadMoList() {
		$data = $this->ProcessInputData('loadMoList', true);
		if ($data) {
			$filter = $data;
			$filter['list_type'] = 'lpu';
			$response = $this->MzDrugRequest_model->loadList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка заявок врачей
	 */
	function loadMPList() {
		$data = $this->ProcessInputData('loadMPList', true);
		if ($data) {
			$filter = $data;
			$filter['list_type'] = 'medpersonal';
			$response = $this->MzDrugRequest_model->loadList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Удаление
	 */
	function delete() {
		$data = $this->ProcessInputData('delete', true, true);
		if ($data) {
			$this->MzDrugRequest_model->setDrugRequest_id($data['id']);
			$this->MzDrugRequest_model->setServer_id($data['Server_id']);
			$this->MzDrugRequest_model->setpmUser_id($data['pmUser_id']);
			$response = $this->MzDrugRequest_model->delete();
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Удаление сводной заявки
	 */
	function deleteConsolidatedDrugRequest() {
		$data = $this->ProcessInputData('deleteConsolidatedDrugRequest', true, true);
		if ($data) {
			$response = $this->MzDrugRequest_model->deleteConsolidatedDrugRequest(array(
                'DrugRequest_id' => $data['id']
            ));
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка МО
	 */
	function loadLpuList() {
		$data = $this->ProcessInputData('loadLpuList', true);
		if ($data) {
			$filter = $data;
			$response = $this->MzDrugRequest_model->loadLpuList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка врачей
	 */
	function loadMedPersonalList() {
		$data = $this->ProcessInputData('loadMedPersonalList', true);
		if ($data) {
			$filter = $data;
			$response = $this->MzDrugRequest_model->loadMedPersonalList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка МО для выбора
	 */
	function loadLpuSelectList() {
		$data = $this->ProcessInputData('loadLpuSelectList', true);
		if ($data) {
			$filter = $data;
			$response = $this->MzDrugRequest_model->loadLpuSelectList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка врачей для выбора
	 */
	function loadMedPersonalSelectList() {
		$data = $this->ProcessInputData('loadMedPersonalSelectList', true);
		if ($data) {
			$filter = $data;
			$response = $this->MzDrugRequest_model->loadMedPersonalSelectList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Изменение статуса заявки
	 */
	function changeDrugRequestStatus() {
		$data = $this->ProcessInputData('changeDrugRequestStatus', true);
		if ($data){			
			$response = $this->MzDrugRequest_model->changeDrugRequestStatus($data);
			if (isset($response['Error_Data'])) {
				$this->ReturnData(
					array(
						'Cancel_Error_Handle' => true,
						'success' => false,
						'Error_Code' => 0,
						'Error_Msg' => toUtf($response['Error_Msg']),
						'Error_Type' => $response['Error_Type'],
						'Error_Data' => $response['Error_Data']
					)
				);
				return false;
			} else {
				$this->ProcessModelSave($response, true, 'Ошибка при смене статуса заявки')->ReturnData();
				return true;
			}
		} else {
			return false;
		}
	}

	/**
	 * Создание полной копии заявочной кампании, включая все дочерние заявки и разнарядки (копирование потребности)
	 */
	function createDrugRequestRegionFirstCopy() {
        $session_data = getSessionParams();
		$data = $this->ProcessInputData('createDrugRequestRegionFirstCopy', true);
		if ($data){
            $data['check_status'] = ($data['check_status'] == 1);
            $data['check_consolidated_request'] = ($data['check_consolidated_request'] == 1);

			$response = $this->MzDrugRequest_model->createDrugRequestRegionFirstCopy($data);
            if ($response['success']) { //если копия создана успешно, меняем статус всей заявочной кампании на "Начальная"
                $this->MzDrugRequest_model->setServer_id($session_data['Server_id']);
                $res = $this->MzDrugRequest_model->unapproveAllDrugRequestMo(array(
                    'RegionDrugRequest_id' => $data['DrugRequest_id'],
                    'check_consolidated_request' => false
                ));
            }
			$this->ProcessModelSave($response, true, 'Ошибка при создании копии заявки')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Удаление полной копии заявочной кампании, включая все дочерние заявки и разнарядки (функция для разработчика)
	 */
	function deleteDrugRequestRegionFirstCopy() {
		$data = $this->ProcessInputData('deleteDrugRequestRegionFirstCopy', true);
		if ($data){
			$response = $this->MzDrugRequest_model->deleteDrugRequestRegionFirstCopy($data);
			$this->ProcessModelSave($response, true, 'Ошибка при создании копии заявки')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Создание архивной копии заявки
	 */
	function createDrugRequestArchiveCopy() {
		$data = $this->ProcessInputData('createDrugRequestArchiveCopy', true);
		if ($data){
			$response = $this->MzDrugRequest_model->createDrugRequestArchiveCopy($data);
			$this->ProcessModelSave($response, true, 'Ошибка при создании архивной копии заявки')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение списка различий меж текущей и архивной заявкой (возможно не используется)
	 */
	function getArchiveCopyDifferencesProtocol() {
		$data = $this->ProcessInputData('getArchiveCopyDifferencesProtocol', true);
		if ($data){
			$this->MzDrugRequest_model->setDrugRequest_id($data['DrugRequest_id']);
			$response = $this->MzDrugRequest_model->getArchiveCopyDifferencesProtocol();
			$this->ProcessModelSave($response, true, 'Ошибка при создании архивной копии заявки')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Сохранение параметров
	 */
	function saveDrugRequestPurchaseSpecParams() {
		$data = $this->ProcessInputData('saveDrugRequestPurchaseSpecParams', true);
		if ($data){
			$response = $this->MzDrugRequest_model->saveDrugRequestPurchaseSpecParams($data);
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении параметра')->ReturnData();			
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Сохранение параметров
	 */
	function saveDrugRequestPlanParams() {
		$data = $this->ProcessInputData('saveDrugRequestPlanParams', false);
		if ($data){
            //список полей доступных для сохранения
            $allowed_fields = array(
                'DrugRequestPlan_FedKolvo',
                'DrugRequestPlan_RegKolvo',
                'DrugRequestPlan_Kolvo',
                'DrugRequestPlan_FedSumma',
                'DrugRequestPlan_RegSumma',
                'DrugRequestPlan_Summa'
            );

            if (in_array($data['field'], $allowed_fields)) {
                $save_data = array();
                $save_data['DrugRequest_id'] = $data['DrugRequest_id'];
                $save_data[$data['field']] = $data['value'];

                $response = $this->MzDrugRequest_model->saveDrugRequestPlanParams($save_data);
                $this->ProcessModelSave($response, true, 'Ошибка при сохранении параметра')->ReturnData();
                return true;
            } else {
                $this->ReturnError('Данный параметр не может быть сохранен');
            }
		}

		return false;
	}

	/**
	 * Рассчет параметров для заявок
	 */
	/*function calculateDrugRequestPlanParams() {
		$data = $this->ProcessInputData('calculateDrugRequestPlanParams', false);
		if ($data){
			$response = $this->MzDrugRequest_model->calculateDrugRequestPlanParams($data);
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении параметра')->ReturnData();
			return true;
		} else {
			return false;
		}
	}*/

	/**
	 * Рассчет параметров для заявочной кампани
	 */
	function calculateDrugRequestPlanRegionParams() {
		$data = $this->ProcessInputData('calculateDrugRequestPlanRegionParams', false);
		if ($data){
			$response = $this->MzDrugRequest_model->calculateDrugRequestPlanRegionParams($data);
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении параметра')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Рассчет параметров для заявки МО
	 */
	function calculateDrugRequestPlanLpuParams() {
		$data = $this->ProcessInputData('calculateDrugRequestPlanLpuParams', false);
		if ($data){
			$response = $this->MzDrugRequest_model->calculateDrugRequestPlanLpuRegionParams($data); //вызов того же метода что и для заявки участка но с другими парамтрами
            $request_id = $this->MzDrugRequest_model->getDrugRequestIdByParams($data);
            if ($request_id > 0) { //дописываем в ответ обновленные плановые параметры заявки
                $plan_params = $this->MzDrugRequest_model->getDrugRequestPlanParams(array('DrugRequest_id' => $request_id));
                if (is_array($plan_params)) {
                    $response = array_merge($response, $plan_params);
                }
            }
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении параметра')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Рассчет параметров для заявки участка (либо рассчет тех же данных но для всей заявочной кампании)
	 */
	function calculateDrugRequestPlanLpuRegionParams() {
		$data = $this->ProcessInputData('calculateDrugRequestPlanLpuRegionParams', false);
		if ($data){
			$response = $this->MzDrugRequest_model->calculateDrugRequestPlanLpuRegionParams($data);

			//для расчетпо участку дописываем в ответ обновленные плановые параметры заявки
			if (!empty($data['LpuRegionDrugRequest_id'])) {
				$plan_params = $this->MzDrugRequest_model->getDrugRequestPlanParams(array('DrugRequest_id' => $data['LpuRegionDrugRequest_id']));
				if (is_array($plan_params)) {
					$response = array_merge($response, $plan_params);
				}
				$sum_data = $this->MzDrugRequest_model->getDrugRequestSumData($data['LpuRegionDrugRequest_id']);
				if (is_array($sum_data) && count($response) > 0) {
					$response = array_merge($response, $sum_data);
				}
			}
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении параметра')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Рассчет сумм для заявок
	 */
	function calculateDrugRequestSum() {
		$data = $this->ProcessInputData('calculateDrugRequestSum', false);
		if ($data){
			$response = $this->MzDrugRequest_model->calculateDrugRequestSum($data);
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении параметра')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка
	 */
	function loadConsolidatedDrugRequestList() {
		$data = $this->ProcessInputData('loadConsolidatedDrugRequestList', true);
		if ($data) {
			$filter = $data;
			$response = $this->MzDrugRequest_model->loadConsolidatedDrugRequestList($filter);
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка строк заявки, соотвествующих конкретной позиции сводной заявки
	 */
	function loadConsolidatedDrugRequestRowList() {
		$data = $this->ProcessInputData('loadConsolidatedDrugRequestRowList', true);
		if ($data) {
			$filter = $data;
			$response = $this->MzDrugRequest_model->loadConsolidatedDrugRequestRowList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение списка региональных заявок для формирования сводной заявки
	 */
	function loadConsolidatedDrugRequestSourceList() {
		$data = $this->ProcessInputData('loadConsolidatedDrugRequestSourceList', true);
		if ($data) {
			$response = $this->MzDrugRequest_model->loadConsolidatedDrugRequestSourceList();
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * агрузка списка региональных заявок включенных в список сводной заявки
	 */
	function loadConsolidatedRegionDrugRequestList() {
		$data = $this->ProcessInputData('loadConsolidatedRegionDrugRequestList', true);
		if ($data) {
			$response = $this->MzDrugRequest_model->loadConsolidatedRegionDrugRequestList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка сгруппированой спецификации сводной заявки
	 */
	function loadDrugRequestPurchaseSpecSumList() {
		$data = $this->ProcessInputData('loadDrugRequestPurchaseSpecSumList', true);
		if ($data) {
			$filter = $data;
			$response = $this->MzDrugRequest_model->loadDrugRequestPurchaseSpecSumList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение списка медикаментов сводной заявки
	 */
	function loadDrugRequestPurchaseSpecList() {
		$data = $this->ProcessInputData('loadDrugRequestPurchaseSpecList', true);
		if ($data) {
			$filter = $data;
			$response = $this->MzDrugRequest_model->loadDrugRequestPurchaseSpecList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Сохранение сводной заявки
	 */
	function saveConsolidatedDrugRequest() {
		$data = $this->ProcessInputData('saveConsolidatedDrugRequest', true);
		if ($data){
			$response = $this->MzDrugRequest_model->createConsolidatedDrugRequest($data);
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении сводной заявки')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Расчет лимитированной заявки
	 */
	function recalculateDrugRequestByFin() {
		$data = $this->ProcessInputData('recalculateDrugRequestByFin', false);
		if ($data){
			$response = $this->MzDrugRequest_model->recalculateDrugRequestByFin($data);
			$this->ProcessModelSave($response, true, 'Ошибка при расчете')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Пересчет сумм и количеств в заявочной компании, по содержимому персональной разнарядки
	 */
	function recalculateDrugRequestByPersonOrderKolvo() {
		$data = $this->ProcessInputData('recalculateDrugRequestByPersonOrderKolvo', false);
		if ($data){
			$response = $this->MzDrugRequest_model->recalculateDrugRequestByPersonOrderKolvo($data);
			$this->ProcessModelSave($response, true, 'Ошибка при расчете')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Пересоздание сводной заявки
	 */
	function reCreateConsolidatedDrugRequest() {
		$data = $this->ProcessInputData('reCreateConsolidatedDrugRequest', true);
		if ($data){
			if (isset($data['DrugRequest_id'])) {
				$this->MzDrugRequest_model->setDrugRequest_id($data['DrugRequest_id']);
			}
			if (isset($data['Server_id'])) {
				$this->MzDrugRequest_model->setServer_id($data['Server_id']);
			}
			if (isset($data['pmUser_id'])) {
				$this->MzDrugRequest_model->setpmUser_id($data['pmUser_id']);
			}
			$response = $this->MzDrugRequest_model->reCreateConsolidatedDrugRequest();
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении сводной заявки')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Закрытие позиции сводной заявки
	 */
	function closeDrugRequestPurchaseSpec() {
		$data = $this->ProcessInputData('closeDrugRequestPurchaseSpec', true);
		if ($data){
			$response = $this->MzDrugRequest_model->closeDrugRequestPurchaseSpec($data);
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении сводной заявки')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Снятие запрета редактирования для группы строк спецификации сводной заявки
	 */
	function openDrugRequestPurchaseSpec() {
		$data = $this->ProcessInputData('openDrugRequestPurchaseSpec', true);
		if ($data){
			$response = $this->MzDrugRequest_model->openDrugRequestPurchaseSpec($data);
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении сводной заявки')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка МНН для комбо
	 */
	function loadDrugComplexMnnCombo() {
		$data = $this->ProcessInputData('loadDrugComplexMnnCombo', true);
		if ($data) {
			$filter = $data;
			$response = $this->MzDrugRequest_model->loadDrugComplexMnnCombo($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка торговых наименований для комбо
	 */
	function loadTradenamesCombo() {
		$data = $this->ProcessInputData('loadTradenamesCombo', false);
		if ($data) {
			$filter = $data;
			$response = $this->MzDrugRequest_model->loadTradenamesCombo($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка протоколов ВК для комбо
	 */
	function loadProtokolVKCombo() {
		$data = $this->ProcessInputData('loadProtokolVKCombo', false);
		if ($data) {
			$filter = $data;
			$response = $this->MzDrugRequest_model->loadProtokolVKCombo($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка МНН для комбо (используется при редактировании спецификации ГК)
	 */
	function loadDrugComplexMnnComboForSupply() {
		$data = $this->ProcessInputData('loadDrugComplexMnnComboForSupply', true);
		if ($data) {
			$filter = $data;
			$response = $this->MzDrugRequest_model->loadDrugComplexMnnComboForSupply($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка медикаментов для комбо (используется при редактировании спецификации ГК)
	 */
	function loadRlsDrugComboForSupply() {
		$data = $this->ProcessInputData('loadRlsDrugComboForSupply', true);
		if ($data) {
			$filter = $data;
			$response = $this->MzDrugRequest_model->loadRlsDrugComboForSupply($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение данных о лимитах
	 */
	function getLimitDataForRequestSelectWindow() {
		$data = $this->ProcessInputData('getLimitDataForRequestSelectWindow', true);
		if ($data){
			$response = $this->MzDrugRequest_model->getLimitDataForRequestSelectWindow($data);
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение сгенерированного уведомления
	 */
	function getNotice() {
		$data = $this->ProcessInputData('getNotice', true);
		if ($data){
			$response = $this->genNotice($data);
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Генерация уведомлений связанных с заявками
	 * input:	event
	 * DrugRequest_id
	 */
	function genNotice($data) {
		if (!$data || !isset($data['event']))
			return false;

		$this->load->model('Org_model', 'Org_model');
		$this->load->model('Messages_model', 'Messages_model');

		$res = array();
		$message_data = array();
		$recipient = array();
		$message_id = 0;
		$send = false;
		$header = '';
		$text = '';

		if ($data['event'] == 'mo_request_set_formed' || $data['event'] == 'mo_request_set_confirmed')
			$send = true;

		// Получаем шаблоны для уведомления
		$tpl = $this->MzDrugRequest_model->getTemplateForNotice($data['event']);
		$header = $tpl['header'];
		$text = $tpl['text'];

		// Находим данные организации пользователя
		$orgData =  array(
			'Org_Phone' => '',
			'Org_Email' => ''
		);
		if ($data['session']['org_id']) {
			$response = $this->Org_model->getOrgData(array('Org_id' => $data['session']['org_id']));
			if (isset($response[0]))
				$orgData = $response[0];
		}

		// Находим данные для уведомления
		$recipient = $this->MzDrugRequest_model->getRecipientForNotice($data);
		//print_r($recipientData); die;
		//$recipient[] = $data['session']['pmuser_id'];
		if(!$recipient) return false;

		// Находим данные для уведомления
		$requestData = $this->MzDrugRequest_model->getDataForNotice($data);
		if(!$requestData) return false;

		if (isset($requestData[0])) {
			$requestData = $requestData[0];
			$requestData['User_Name'] = toAnsi($data['session']['user']);
			$requestData['User_Phone'] = $orgData['Org_Phone'];
			$requestData['User_Email'] = !empty($data['session']['email']) ? $data['session']['email'] : $orgData['Org_Email'];

			// Заполняем шаблоны даннами
			foreach($requestData as $key => $value) {
				$header = preg_replace('/{'.$key.'}/', $value, $header);
				$text = preg_replace('/{'.$key.'}/', $value, $text);
			}
		}

		//print_r($data['session']); die;
		//print $text; die;

		// Формируем данные для сообщения
		$message_data['action'] = 'ins';
		$message_data['Message_id'] = null;
		$message_data['Message_pid'] = null;
		$message_data['pmUser_id'] = $data['session']['pmuser_id'];
		$message_data['Message_Subject'] = $header;
		$message_data['Message_Text'] = $text;
		$message_data['Message_isSent'] = $send ? 1 : null;
		$message_data['NoticeType_id'] = 1;
		$message_data['Message_isFlag'] = null;
		$message_data['Message_isDelete'] = null;
		$message_data['RecipientType_id'] = 1;
		$message_data['MessageRecipient_id'] = null;
		$message_data['Message_isRead'] = null;

		// Добавляем само сообщение
		$response = $this->Messages_model->insMessage($message_data);
		if(is_array($response) && strlen($response[0]['Error_Msg']) == 0) {
			$message_id = $response[0]['Message_id'];
		}

		// Если сообщение заинсертилось, т.е. существует его ид'шник, то добавляем связи для получателей
		if(isset($message_id)) {
			for($j=0; $j<count($recipient); $j++) {
				$res[$j] = $this->Messages_model->insMessageLink($message_id, $recipient[$j], $message_data);
				if(strlen($res[$j][0]['Error_Msg']) > 0) {
					break;
					DieWithError('Не удалось сохранить сообщение!');
					return false;
				}

				// Отправляем сообщение
				if ($send)
					$this->Messages_model->sendMessage($message_data, $recipient[$j], $message_id);
			}
		}


		$res['Message_Subject'] = $header;
		$res['Message_Text'] = $text;
		$res['Message_id'] = $message_id;

		return array($res);
	}

	/**
	 * Установка автоматического статуса
	 */
	function setAutoDrugRequestStatus() {
		$data = $this->ProcessInputData('setAutoDrugRequestStatus', true);
		if ($data){
			$this->MzDrugRequest_model->setServer_id($data['Server_id']);
			$this->MzDrugRequest_model->setpmUser_id($data['pmUser_id']);

			$response = $this->MzDrugRequest_model->setAutoDrugRequestStatus($data);
			//$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка региональных заявок для комбобокса
	 */
	function loadRegionDrugRequestCombo() {
		$data = $this->ProcessInputData('loadRegionDrugRequestCombo', true);
		if ($data) {
            $data['show_first_copy'] = ($data['show_first_copy'] == 1);
			$filter = $data;
			$response = $this->MzDrugRequest_model->loadRegionDrugRequestCombo($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка заявок МО для комбобокса
	 */
	function loadMoDrugRequestCombo() {
		$data = $this->ProcessInputData('loadMoDrugRequestCombo', false);
		if ($data) {
			$filter = $data;
			$response = $this->MzDrugRequest_model->loadMoDrugRequestCombo($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Сохранение информации о количестве и сумме "К закупу"
	 */
	function saveDrugRequestRowBuyDataFromJSON() {
		$data = $this->ProcessInputData('saveDrugRequestRowBuyDataFromJSON', true);
		if ($data){
			$response = $this->MzDrugRequest_model->saveDrugRequestRowBuyDataFromJSON($data);
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении данных')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *	Экспорт спецификации сводной заявки в формате CSV
	 */
	function exportDrugRequestPurchaseSpecList() {
		$data = $this->ProcessInputData('loadDrugRequestPurchaseSpecList', true);
		if ($data === false) { return false; }

		$data['export'] = true;
		$response = $this->MzDrugRequest_model->loadDrugRequestPurchaseSpecListForExport($data);
		if( !is_array($response) || count($response) == 0 ) {
			DieWithError("Нет данных для экспорта");
		}

		set_time_limit(0);

		if(!is_dir(EXPORTPATH_CONSOLIDATED_REQUEST)) {
			if (!mkdir(EXPORTPATH_CONSOLIDATED_REQUEST)) {
				DieWithError("Ошибка при создании директории ".EXPORTPATH_CONSOLIDATED_REQUEST."!");
			}
		}

		$f_name = "spec_list";
		$file_name = EXPORTPATH_CONSOLIDATED_REQUEST.$f_name.".csv";
		$archive_name = EXPORTPATH_CONSOLIDATED_REQUEST.$f_name.".zip";
		if( is_file($archive_name) ) {
			unlink($archive_name);
		}

		try {
			$h = fopen($file_name, 'w');
			if(!$h) {
				DieWithError("Ошибка при попытке открыть файл!");
			}
            $strTmp  = "Идентификатор заявки;";
            $strTmp .= "Организация;";
            $strTmp .= "Группа медикаментов;";
            $strTmp .= "Тип финансирования;";
            $strTmp .= "Тип регистра;";
            $strTmp .= "Медикамент в заявке;";
            $strTmp .= "Торговое наименование;";
            $strTmp .= "Протокол ВК;";
            $strTmp .= "Цена;";
            $strTmp .= "Тип расчета цены;";
            $strTmp .= "Дата расчета цены;";
            $strTmp .= "Количество по заявкам МО;";
            $strTmp .= "Стоимость заявленного МО;";
            $strTmp .= "Количество упаковок: отказ;";
            $strTmp .= "Количество упаковок: из остатков;";
            $strTmp .= "Количество «к закупу для МО»;";
            $strTmp .= "Стоимость закупаемого для МО;";
            $strTmp .= "Количество в заявке на закуп;";
            $strTmp .= "Стоимость в заявке на закуп;";
            $strTmp .= "№ лота\n";

			ConvertFromUTF8ToWin1251($strTmp, null, true);

			$str_result = $strTmp;

			foreach($response as $row) {
                $strTmp  = $row['DrugRequest_id'].";"; //Идентификатор заявки
                $strTmp .= str_replace(';','',$row['Org_Name']).";"; //Организация
                $strTmp .= str_replace(';','',$row['DrugGroup_Name']).";"; //Группа медикаментов
                $strTmp .= str_replace(';','',$row['DrugFinance_Name']).";"; //Тип финансирования
                $strTmp .= str_replace(';','',$row['PersonRegisterType_Name']).";"; //Тип регистра
                $strTmp .= str_replace(';','',$row['DrugComplexMnn_Name']).";"; //Медикамент в заявке
                $strTmp .= str_replace(';','',$row['Tradenames_Name']).";"; //Торговое наименование
                $strTmp .= $row['Evn_id'].";"; //Протокол ВК
                $strTmp .= str_replace('.',',',$row['DrugRequestPurchaseSpec_Price']).";"; //Цена
                $strTmp .= str_replace('.',',',$row['CalculatPriceType_Name']).";"; //Тип расчета цены
                $strTmp .= $row['DrugRequestPurchaseSpec_priceDate'].";"; //Дата расчета цены
                $strTmp .= str_replace('.',',',$row['DrugRequestPurchaseSpec_lKolvo']).";"; //Количество по заявкам МО
                $strTmp .= str_replace('.',',',$row['DrugRequestPurchaseSpec_lSum']).";"; //Стоимость заявленного МО
                $strTmp .= str_replace('.',',',$row['DrugRequestPurchaseSpec_RefuseCount']).";"; //Количество упаковок: отказ
                $strTmp .= str_replace('.',',',$row['DrugRequestPurchaseSpec_RestCount']).";"; //Количество упаковок: из остатков
                $strTmp .= str_replace('.',',',$row['DrugRequestPurchaseSpec_Kolvo']).";"; //Количество «к закупу для МО»
                $strTmp .= str_replace('.',',',$row['DrugRequestPurchaseSpec_Sum']).";"; //Стоимость закупаемого для МО
                $strTmp .= str_replace('.',',',$row['DrugRequestPurchaseSpec_pKolvo']).";"; //Количество в заявке на закуп
                $strTmp .= str_replace('.',',',$row['DrugRequestPurchaseSpec_pSum']).";"; //Стоимость в заявке на закуп
                $strTmp .= str_replace(';','',$row['WhsDocumentUc_Num'])."\n"; //№ лота

				ConvertFromUTF8ToWin1251($strTmp, null, true);

				$str_result .= $strTmp;
			}

			fwrite($h, $str_result);
			fclose($h);

			$zip = new ZipArchive();
			$zip->open($archive_name, ZIPARCHIVE::CREATE);
			$zip->AddFile($file_name, basename($file_name));
			$zip->close();
			unlink($file_name);

			$this->ReturnData(array('success' => true, 'url' => $archive_name));
		} catch (Exception $e) {
			DieWithError($e->getMessage());
			$this->ReturnData(array('success' => false));
		}

		if(is_file($file_name)) {
			@unlink($file_name);
		}
	}

	/**
	 * Подсчет количества актуальных строк в заявке
	 */
	function getDrugRequestRowCount() {
		$data = $this->ProcessInputData('getDrugRequestRowCount', true);
		if ($data){
			$response = $this->MzDrugRequest_model->getDrugRequestRowCount($data);
			$this->ProcessModelSave($response, true, 'Ошибка при получении данных')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Создание копии заявки
	 */
	function createDrugRequestCopy() {
		$data = $this->ProcessInputData('createDrugRequestCopy', true);
		if ($data){
			$this->MzDrugRequest_model->setDrugRequest_id($data['DrugRequest_id']);
			$response = $this->MzDrugRequest_model->createDrugRequestCopy($data);
			$this->ProcessModelSave($response, true, 'Ошибка при создании копии заявки')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Создание списка пациентов для заявки
	 */
	function createDrugRequestPersonList() {
		$data = $this->ProcessInputData('createDrugRequestPersonList', true);
		if ($data){
			$this->MzDrugRequest_model->setDrugRequest_id($data['DrugRequest_id']);
			$response = $this->MzDrugRequest_model->createDrugRequestPersonList($data);
			$this->ProcessModelSave($response, true, 'Ошибка при создании списка пациентов')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Создание списка пациентов для заявки (новая версия заявки с разнарядками по пациентам)
	 */
	function createMzDrugRequestPersonList() {
		$data = $this->ProcessInputData('createMzDrugRequestPersonList', true);
		if ($data){
			$this->MzDrugRequest_model->setDrugRequest_id($data['DrugRequest_id']);
			$response = $this->MzDrugRequest_model->createMzDrugRequestPersonList($data);
			$this->ProcessModelSave($response, true, 'Ошибка при создании списка пациентов')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Копирование списка медикаментов из одной заявки в другую
	 */
	function createDrugRequestDrugCopy() {
		$data = $this->ProcessInputData('createDrugRequestDrugCopy', true);
		if ($data){
			$this->MzDrugRequest_model->setDrugRequest_id($data['DrugRequest_id']);
			$response = $this->MzDrugRequest_model->createDrugRequestDrugCopy($data);
			$this->ProcessModelSave($response, true, 'Ошибка при копировании списка медикаментов')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Копирование списка медикаментов из одной заявки в другую (новая версия заявки с разнарядками по пациентам)
	 */
	function createMzDrugRequestDrugCopy() {
		$data = $this->ProcessInputData('createMzDrugRequestDrugCopy', true);
		if ($data){
			$this->MzDrugRequest_model->setDrugRequest_id($data['DrugRequest_id']);
			$response = $this->MzDrugRequest_model->createMzDrugRequestDrugCopy($data);
			$this->ProcessModelSave($response, true, 'Ошибка при копировании списка медикаментов')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка заявок для комбобокса (копирование заявок)
	 */
	function loadSourceDrugRequestCombo() {
		$data = $this->ProcessInputData('loadSourceDrugRequestCombo', true);
		if ($data) {
			$filter = $data;
			$response = $this->MzDrugRequest_model->loadSourceDrugRequestCombo($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение заявки МО по заявке врача
	 */
	function getMoRequestByMpRequest() {
		$data = $this->ProcessInputData('getMoRequestByMpRequest', true);
		if ($data && isset($data['DrugRequest_id'])){
			$this->MzDrugRequest_model->setDrugRequest_id($data['DrugRequest_id']);
			$response = $this->MzDrugRequest_model->getMoRequestByMpRequest();
			$this->ProcessModelSave($response, true, 'Ошибка при получении данных')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение статуса заявки МО по параметрам
	 */
	function getMoRequestStatusByParams() {
		$data = $this->ProcessInputData('getMoRequestStatusByParams', true);
		if ($data){
			$response = $this->MzDrugRequest_model->getMoRequestStatusByParams($data);
			$this->ProcessModelList($response, true, 'Ошибка при получении данных')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение статуса заявки МО по параметрам
	 */
	function getMoRequestPlanParamsByParams() {
		$data = $this->ProcessInputData('getMoRequestStatusByParams', true);
		if ($data){
            $request_id = $this->MzDrugRequest_model->getDrugRequestIdByParams($data);
            if ($request_id > 0) {
                $response = $this->MzDrugRequest_model->getDrugRequestPlanParams(array('DrugRequest_id' => $request_id));
                $this->ProcessModelSave($response, true, 'Ошибка при получении данных')->ReturnData();
                return true;
            } else {
                $this->ReturnError('Не удалось получить данные заявки МО');
            }
		}
		return false;
	}

	/**
	 * Сохранение строки заявки
	 */
	function saveDrugRequestRow() {
		$data = $this->ProcessInputData('saveDrugRequestRow', true);
		if ($data){
            if (!empty($data['DrugRequestRow_id'])) {
                //проверка наличия строки разнарядки для данной строки заявки
                $check_data = $this->MzDrugRequest_model->checkExistsDrugRequestPersonOrderForDrugRequestRow(array(
                    'DrugRequestRow_id' => $data['DrugRequestRow_id']
                ));
                if (isset($check_data['drpo_cnt'])) {
                    if ($data['DrugRequestRow_Kolvo'] > 0 && $data['DrugRequestRow_Kolvo'] < $check_data['drpo_kolvo']) {
                        $this->ReturnError('Сохранение невозможно, т.к. количество в заявке врача не может быть  меньше количества в персональных разнарядках - '.$check_data['drpo_kolvo']);
                        return false;
                    }
                } else {
                    $this->ReturnError('Проверка при удалении завершилась ошибкой');
                    return false;
                }
            }

			$original_row = $this->MzDrugRequest_model->checkObjectDoubles('DrugRequestRow', array(
				'DrugRequestRow_id' => $data['DrugRequestRow_id'],
				'DrugRequest_id' => $data['DrugRequest_id'],
				'DrugFinance_id' => $data['DrugFinance_id'],
				'DrugComplexMnn_id' => $data['DrugComplexMnn_id'],
				'TRADENAMES_id' => $data['TRADENAMES_id'],
				'Person_id' => null
			));
			if (is_array($original_row) && $original_row['DrugRequestRow_id'] > 0) {
				$data['DrugRequestRow_id'] = $original_row['DrugRequestRow_id'];
				$data['DrugRequestRow_Kolvo'] = $data['DrugRequestRow_Kolvo'] > 0 ? $data['DrugRequestRow_Kolvo'] + $original_row['DrugRequestRow_Kolvo'] : $original_row['DrugRequestRow_Kolvo'];
				$data['DrugRequestRow_Summa'] = $data['DrugRequestRow_Summa'] > 0 ? $data['DrugRequestRow_Summa'] + $original_row['DrugRequestRow_Summa'] : $original_row['DrugRequestRow_Summa'];
			}
			if(!empty($data['PersonRegisterType_id']) && $data['PersonRegisterType_id'] == 1){
				if(!empty($data['DrugFinance_id']) && $data['DrugFinance_id'] == 3){
					$data['DrugRequestType_id'] = 1;
				}
				if(!empty($data['DrugFinance_id']) && $data['DrugFinance_id'] == 27){
					$data['DrugRequestType_id'] = 2;
				}
			}
			unset($data['PersonRegisterType_id']);
			$response = $this->MzDrugRequest_model->saveObject('DrugRequestRow', $data);
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении строки заявки')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Сохранение количества в строке заявки
	 */
	function saveDrugRequestRowKolvo() {
		$data = $this->ProcessInputData('saveDrugRequestRowKolvo', true);
		if ($data){
            //проверка наличия строки разнарядки для данной строки заявки
            $check_data = $this->MzDrugRequest_model->checkExistsDrugRequestPersonOrderForDrugRequestRow($data);
            if (isset($check_data['drpo_cnt'])) {
                if (empty($data['DrugRequestRow_Kolvo']) && $check_data['drpo_cnt'] > 0) {
                    $this->ReturnError('Удаление не возможно, т.к. на этот медикамент составлена персональная разнарядка');
                    return false;
                }
                if ($data['DrugRequestRow_Kolvo'] > 0 && $data['DrugRequestRow_Kolvo'] < $check_data['drpo_kolvo']) {
                    $this->ReturnError('Сохранение невозможно, т.к. количество в заявке врача не может быть  меньше количества в персональных разнарядках - '.$check_data['drpo_kolvo']);
                    return false;
                }
            } else {
                $this->ReturnError('Проверка при удалении завершилась ошибкой');
                return false;
            }

			$response = $this->MzDrugRequest_model->saveDrugRequestRowKolvo($data);
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении строки заявки')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Сохранение информации о дозировках
	 */
	function saveDrugRequestRowDose() {
		$data = $this->ProcessInputData('saveDrugRequestRowDose', true);
		if ($data){
			$response = $this->MzDrugRequest_model->saveDrugRequestRowDose($data);
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении дозировок')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Сохранение строки персональной разнарядки
	 */
	function saveDrugRequestPersonOrder() {
		$data = $this->ProcessInputData('saveDrugRequestPersonOrder', true);
		if ($data){
			$response = $this->MzDrugRequest_model->saveDrugRequestPersonOrder($data);
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении строки персональной разнарядки')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Редактирование количества в строке персональной разнарядки
	 */
	function saveDrugRequestPersonOrderOrdKolvo() {
		$data = $this->ProcessInputData('saveDrugRequestPersonOrderOrdKolvo', true);
		if ($data){
            $drpo_data = $this->MzDrugRequest_model->loadDrugRequestPersonOrder(array('DrugRequestPersonOrder_id' => $data['DrugRequestPersonOrder_id']));
            if (isset($drpo_data[0]) && isset($drpo_data[0]['DrugRequestPersonOrder_OrdKolvo'])) {
                $response = $this->MzDrugRequest_model->saveDrugRequestPersonOrder(array(
                    'DrugRequestPersonOrder_id' => $drpo_data[0]['DrugRequestPersonOrder_id'],
                    'DrugRequest_id' => $drpo_data[0]['DrugRequest_id'],
                    'Person_id' => $drpo_data[0]['Person_id'],
                    'DrugComplexMnn_id' => $drpo_data[0]['DrugComplexMnn_id'],
                    'Tradenames_id' => $drpo_data[0]['Tradenames_id'],
                    'DrugRequestPersonOrder_OrdKolvo' => $data['DrugRequestPersonOrder_OrdKolvo'],
                    'DrugRequestFirstCopy_id' => $data['DrugRequestFirstCopy_id'],
                    'need_check_kolvo_in_first_copy' => true
                ));
                if (empty($response['DrugRequestPersonOrder_id'])) {
                    $this->ReturnError(!empty($response['Error_Msg']) ? $response['Error_Msg'] : 'При сохранении произошла ошибка');
                    return false;
                }
                $response['DrugRequestPersonOrder_OrdKolvo'] = $data['DrugRequestPersonOrder_OrdKolvo'];
                $this->ProcessModelSave($response, true, 'Ошибка при сохранении строки персональной разнарядки')->ReturnData();
                return true;
            }
            return false;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка планово-отчетных периодов
	 */
	function loadDrugRequestPlanPeriodList() {
		$data = $this->ProcessInputData('loadDrugRequestPlanPeriodList', true);
		if ($data) {
			$response = $this->MzDrugRequest_model->loadDrugRequestPlanPeriodList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка ЛПУ для комбобокса
	 */
	function loadLpuCombo() {
		$data = $this->ProcessInputData('loadLpuCombo', false);
		if ($data) {
			$response = $this->MzDrugRequest_model->loadLpuCombo($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка участков для комбобокса
	 */
	function loadLpuRegionCombo() {
		$data = $this->ProcessInputData('loadLpuRegionCombo', false);
		if ($data) {
			$response = $this->MzDrugRequest_model->loadLpuRegionCombo($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка отделений для комбобокса
	 */
	function loadLpuSectionCombo() {
		$data = $this->ProcessInputData('loadLpuSectionCombo', false);
		if ($data) {
			$response = $this->MzDrugRequest_model->loadLpuSectionCombo($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка врачей для комбобокса
	 */
	function loadMedPersonalCombo() {
		$data = $this->ProcessInputData('loadMedPersonalCombo', true);
		if ($data) {
			$response = $this->MzDrugRequest_model->loadMedPersonalCombo($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение данных заявки (используется на форме редактирования заявки)
	 */
	function getDrugRequestData() {
		$data = $this->ProcessInputData('getDrugRequestData', true);
		if ($data){
			$response = $this->MzDrugRequest_model->getDrugRequestData($data['DrugRequest_id']);
            $plan_params = $this->MzDrugRequest_model->getDrugRequestPlanParams($data);
            if (is_array($plan_params) && count($response) > 0) {
                $response[0] = array_merge($response[0], $plan_params);
            }
            $sum_data = $this->MzDrugRequest_model->getDrugRequestSumData($data['DrugRequest_id']);
            if (is_array($sum_data) && count($response) > 0) {
                $response[0] = array_merge($response[0], $sum_data);
            }
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение данных о статусе заявки
	 */
	function getDrugRequestStatus() {
		$data = $this->ProcessInputData('getDrugRequestStatus', true);
		if ($data){
			$response = $this->MzDrugRequest_model->getDrugRequestStatus($data['DrugRequest_id']);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка строки заявки
	 */
	function loadDrugRequestRow() {
		$data = $this->ProcessInputData('loadDrugRequestRow', true);
		if ($data){
			$response = $this->MzDrugRequest_model->loadDrugRequestRow($data);
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Удаление строки заявки
	 */
	function deleteDrugRequestRow() {
		$data = $this->ProcessInputData('deleteDrugRequestRow', false);
		if ($data) {
            //проверка наличия строки разнарядки для данной строки заявки
            $check_data = $this->MzDrugRequest_model->checkExistsDrugRequestPersonOrderForDrugRequestRow(array(
                'DrugRequestRow_id' => $data['id']
            ));
            if (isset($check_data['drpo_cnt'])) {
                if ($check_data['drpo_cnt'] > 0) {
                    $this->ReturnError('Удаление не возможно, т.к. на этот медикамент составлена персональная разнарядка');
                    return false;
                }
            } else {
                $this->ReturnError('Проверка при удалении завершилась ошибкой');
                return false;
            }

			$response = $this->MzDrugRequest_model->deleteObject('DrugRequestRow', array(
				'DrugRequestRow_id' => $data['id']
			));
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Удаление строки разнарядки
	 */
	function deleteDrugRequestPersonOrder() {
		$data = $this->ProcessInputData('deleteDrugRequestPersonOrder', false);
		if ($data) {
			$response = $this->MzDrugRequest_model->deleteDrugRequestPersonOrder($data);
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка аналитики для строки заявки
	 */
	function loadDrugRequestRowFactorList() {
		$data = $this->ProcessInputData('loadDrugRequestRowFactorList', false);
		/*if ($data) {*/
			$response = $this->MzDrugRequest_model->loadDrugRequestRowFactorList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		/*} else {
			return false;
		}*/
	}

	/**
	 * Загрузка строки разнарядки по пациентам
	 */
	function loadDrugRequestPersonOrder() {
		$data = $this->ProcessInputData('loadDrugRequestPersonOrder', true);
		if ($data){
			$response = $this->MzDrugRequest_model->loadDrugRequestPersonOrder($data);
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение списка льгот человека
	 */
	function getPersonPrivilegeData() {
		$data = $this->ProcessInputData('getPersonPrivilegeData', true);
		if ($data){
			$response = $this->MzDrugRequest_model->getPersonPrivilegeData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка грида со списком строк заявки для заявочной кампании
	 */
	function loadMzDrugRequestMoDrugGrid() {
		$data = $this->ProcessInputData('loadMzDrugRequestMoDrugGrid', false);
		if ($data) {
			$response = $this->MzDrugRequest_model->loadMzDrugRequestMoDrugGrid($data);
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка грида со списком строк заявки
	 */
	function loadMzDrugRequestDrugGrid() {
		$data = $this->ProcessInputData('loadMzDrugRequestDrugGrid', false);
		if ($data) {
			$response = $this->MzDrugRequest_model->loadMzDrugRequestDrugGrid($data);
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка грида со списком строк разнарядки для конкретного медикамента
	 */
	function loadMzDrugRequestDrugPersonGrid() {
		$data = $this->ProcessInputData('loadMzDrugRequestDrugPersonGrid', true);
		if ($data){
			$response = $this->MzDrugRequest_model->loadMzDrugRequestDrugPersonGrid($data);
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка грида со списком медикаментов для заявки
	 */
	function loadMzDrugRequestDrugListGrid() {
		$data = $this->ProcessInputData('loadMzDrugRequestDrugListGrid', false);
		if ($data) {
			$response = $this->MzDrugRequest_model->loadMzDrugRequestDrugListGrid($data);
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка грида со списком пациентов
	 */
	function loadMzDrugRequestPersonGrid() {
		$data = $this->ProcessInputData('loadMzDrugRequestPersonGrid', true);
		if ($data){
			$response = $this->MzDrugRequest_model->loadMzDrugRequestPersonGrid($data);
			$this->ProcessModelMultiList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка грида со списком пациентов (аналитика персональной потребности)
	 */
	function loadMzDrugRequestFirstCopyGrid() {
		$data = $this->ProcessInputData('loadMzDrugRequestFirstCopyGrid', true);
		if ($data){
			$response = $this->MzDrugRequest_model->loadMzDrugRequestFirstCopyGrid($data);
			$this->ProcessModelMultiList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка грида со списком строк разнарядки для конкретного пациента
	 */
	function loadMzDrugRequestPersonDrugGrid() {
		$data = $this->ProcessInputData('loadMzDrugRequestPersonDrugGrid', true);
		if ($data){
			$response = $this->MzDrugRequest_model->loadMzDrugRequestPersonDrugGrid($data);
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка грида со списком строк разнарядки для конкретного пациента (аналитика персональной потребности)
	 */
	function loadMzDrugRequestFirstCopyDrugGrid() {
		$data = $this->ProcessInputData('loadMzDrugRequestFirstCopyDrugGrid', true);
		if ($data){
			$response = $this->MzDrugRequest_model->loadMzDrugRequestFirstCopyDrugGrid($data);
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение данных о количестве медикамента в строке заявки и в разнарядке
	 */
	function getDrugRequestRowKolvoData() {
		$data = $this->ProcessInputData('getDrugRequestRowKolvoData', false);
		if ($data){
			$response = $this->MzDrugRequest_model->getDrugRequestRowKolvoData($data);
			$this->ProcessModelSave($response, true, 'Ошибка при получении данных о строке заявки')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение дополнительных данных для строки разнарядки
	 */
	function getDrugRequestPersonOrderContext() {
		$data = $this->ProcessInputData('getDrugRequestPersonOrderContext', false);
		if ($data){
			$response = $this->MzDrugRequest_model->getDrugRequestPersonOrderContext($data);
			$this->ProcessModelSave($response, true, 'Ошибка при получении данных')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение суммарного количества медикаментов в выписанных рецептах
	 */
	function getEvnReceptSumKolvoByParams() {
		$data = $this->ProcessInputData('getEvnReceptSumKolvoByParams', false);
		if ($data){
			$response = $this->MzDrugRequest_model->getEvnReceptSumKolvoByParams($data);
			$this->ProcessModelSave($response, true, 'Ошибка при получении данных')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка данных о рабочем периоде
	 */
	function loadDrugRequestPeriod() {
		$data = $this->ProcessInputData('loadDrugRequestPeriod', true);
		if ($data){
			$response = $this->MzDrugRequest_model->loadDrugRequestPeriod($data);
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка грида с информацией о потребности
	 */
	function loadDrugRequestPlanDeliveryGrid() {
		$data = $this->ProcessInputData('loadDrugRequestPlanDeliveryGrid', false);
		if ($data){
			$response = $this->MzDrugRequest_model->loadDrugRequestPlanDeliveryGrid($data);
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Сохранение количества в плане потребности
	 */
	function saveDrugRequestPlanDeliveryKolvo() {
		$data = $this->ProcessInputData('saveDrugRequestPlanDeliveryKolvo', true);
		if ($data){
			$response = $this->MzDrugRequest_model->saveDrugRequestPlanDeliveryKolvo($data);
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении плана потребности')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

    /**
     * Утверждение всех заявок МО в заявочной кампании
     */
    function approveAllDrugRequestMo() {
        $session_data = getSessionParams();
        $data = $this->ProcessInputData('approveAllDrugRequestMo', false);
        $data['pmUser_id'] = $session_data['pmUser_id'];
        if ($data){
            $data['check_status'] = ($data['check_status'] == 1);
            $data['set_auto_status'] = ($data['set_auto_status'] == 1);
            $this->MzDrugRequest_model->setServer_id($session_data['Server_id']);
            $this->MzDrugRequest_model->setpmUser_id($session_data['pmUser_id']);
            $response = $this->MzDrugRequest_model->approveAllDrugRequestMo($data);
            $this->ProcessModelSave($response, true, 'Ошибка при сохранении')->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Отмена утверждения всех заявок МО в заявочной кампании
     */
    function unapproveAllDrugRequestMo() {
        $session_data = getSessionParams();
        $data = $this->ProcessInputData('unapproveAllDrugRequestMo', false);
        $data['pmUser_id'] = $session_data['pmUser_id'];
        if ($data){
            $data['check_consolidated_request'] = ($data['check_consolidated_request'] == 1);
            $this->MzDrugRequest_model->setServer_id($session_data['Server_id']);
            $response = $this->MzDrugRequest_model->unapproveAllDrugRequestMo($data);
            $this->ProcessModelSave($response, true, 'Ошибка при сохранении')->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Получение списка данных о исполнении сводной заявки
     */
    function loadDrugRequestExecList() {
        $data = $this->ProcessInputData('loadDrugRequestExecList', false);
        if ($data) {
            $response = $this->MzDrugRequest_model->loadDrugRequestExecList($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Получение данных закупа при исполнении сводной заявки
     */
    function loadDrugRequestExecPurchaseList() {
        $data = $this->ProcessInputData('loadDrugRequestExecPurchaseList', false);
        if ($data) {
            $response = $this->MzDrugRequest_model->loadDrugRequestExecPurchaseList($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Получение списка остатков для исполнения сводной заявки
     */
    function loadDrugRequestExecSourceList() {
        $data = $this->ProcessInputData('loadDrugRequestExecSourceList', false);
        if ($data) {
            $response = $this->MzDrugRequest_model->loadDrugRequestExecSourceList($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Сохранение данных о исполнении сводной заявки. Сохранение количества.
     */
    function saveDrugRequestExecCount() {
        $session_data = getSessionParams();
        $data = $this->ProcessInputData('saveDrugRequestExecCount', false);
        if ($data){
            $data['pmUser_id'] = $session_data['pmUser_id'];
            $response = $this->MzDrugRequest_model->saveObject('DrugRequestExec', $data);
            if (empty($response['Error_Msg']) && !empty($response['DrugRequestExec_id'])) {
                $response['DrugRequestExec_Count'] = $data['DrugRequestExec_Count'];
                $response['DrugRequestExec_SupplyCount'] = $data['DrugRequestExec_SupplyCount'];

                $res = $this->MzDrugRequest_model->recalculateDrugRequestPurchaseSpecData(array(
                    'DrugRequestPurchaseSpec_id' => $data['DrugRequestPurchaseSpec_id'],
                    'pmUser_id' => $session_data['pmUser_id']
                ));
            }
            $this->ProcessModelSave($response, true, 'Ошибка при сохранении данных')->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Сохранение данных о исполнении сводной заявки из JSON
     */
    function saveDrugRequestExecFromJSON() {
        $session_data = getSessionParams();
        $data = $this->ProcessInputData('saveDrugRequestExecFromJSON', false);
        $data['pmUser_id'] = $session_data['pmUser_id'];
        if ($data){
            $response = $this->MzDrugRequest_model->saveDrugRequestExecFromJSON($data);
            $this->ProcessModelSave($response, true, 'Ошибка при сохранении данных')->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Удаление строки данных о исполнении сводной заявки
     */
    function deleteDrugRequestExec() {
        $session_data = getSessionParams();
        $data = $this->ProcessInputData('deleteDrugRequestExec', false);
        if ($data) {
            $exec_data = $this->MzDrugRequest_model->loadDrugRequestExecList(array(
                'DrugRequestExec_id' => $data['id']
            ));

            $check_data = $this->MzDrugRequest_model->checkDrugRequestExecDelete(array(
                'DrugRequestExec_id' => $data['id']
            ));

            if (empty($check_data['Error_Msg'])) {
                $response = $this->MzDrugRequest_model->deleteObject('DrugRequestExec', array(
                    'DrugRequestExec_id' => $data['id']
                ));
                if (count($exec_data) > 0 && !empty($exec_data[0]['DrugRequestPurchaseSpec_id'])) {
                    $res = $this->MzDrugRequest_model->recalculateDrugRequestPurchaseSpecData(array(
                        'DrugRequestPurchaseSpec_id' => $exec_data[0]['DrugRequestPurchaseSpec_id'],
                        'pmUser_id' => $session_data['pmUser_id']
                    ));
                }
            } else {
                $response = $check_data;
            }

            $this->ProcessModelSave($response, true, $response)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Сохранение данных о исполнении сводной заявки. Сохранение количества.
     */
    function saveDrugRequestPurchaseSpecRefuseCount() {
        $session_data = getSessionParams();
        $data = $this->ProcessInputData('saveDrugRequestPurchaseSpecRefuseCount', false);
        if ($data){
            $data['pmUser_id'] = $session_data['pmUser_id'];
            $response = $this->MzDrugRequest_model->saveObject('DrugRequestPurchaseSpec', $data);
            if (empty($response['Error_Msg']) && !empty($response['DrugRequestPurchaseSpec_id'])) {
                $res = $this->MzDrugRequest_model->recalculateDrugRequestPurchaseSpecData(array(
                    'DrugRequestPurchaseSpec_id' => $data['DrugRequestPurchaseSpec_id'],
                    'pmUser_id' => $session_data['pmUser_id']
                ));

                $spec_data = $this->MzDrugRequest_model->loadDrugRequestPurchaseSpecList(array(
                    'DrugRequestPurchaseSpec_id' => $data['DrugRequestPurchaseSpec_id']
                ));
                //print_r($spec_data);

                if (is_array($spec_data) && isset($spec_data[0])) {
                    $response['DrugRequestPurchaseSpec_RefuseCount'] = !empty($spec_data[0]['DrugRequestPurchaseSpec_RefuseCount']) ? $spec_data[0]['DrugRequestPurchaseSpec_RefuseCount'] : null;
                    $response['DrugRequestPurchaseSpec_pKolvo'] = !empty($spec_data[0]['DrugRequestPurchaseSpec_pKolvo']) ? $spec_data[0]['DrugRequestPurchaseSpec_pKolvo'] : null;
                    $response['DrugRequestPurchaseSpec_pSum'] = !empty($spec_data[0]['DrugRequestPurchaseSpec_pSum']) ? $spec_data[0]['DrugRequestPurchaseSpec_pSum'] : null;
                }
            }
            $this->ProcessModelSave($response, true, 'Ошибка при сохранении данных')->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Сохранение общего лимита по заявке
     */
    function saveDrugRequestQuotaTotal() {
        $session_data = getSessionParams();
        $data = $this->ProcessInputData('saveDrugRequestQuotaTotal', false);
        if ($data){
            $data['pmUser_id'] = $session_data['pmUser_id'];
            $response = $this->MzDrugRequest_model->saveDrugRequestQuotaTotal($data);
            $this->ProcessModelSave($response, true, 'Ошибка при сохранении данных')->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Загрузка списка связей позиций сводной заявки/лота с ценами на медикаменты
     */
    function loadWhsDocumentProcurementPriceLinkList() {
        $data = $this->ProcessInputData('loadWhsDocumentProcurementPriceLinkList', false);
        if ($data) {
            $response = $this->MzDrugRequest_model->loadWhsDocumentProcurementPriceLinkList($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Загрузка списка цен на медикаменты для формы добавления
     */
    function loadWhsDocumentProcurementPriceLinkSourceList() {
        $data = $this->ProcessInputData('loadWhsDocumentProcurementPriceLinkSourceList', false);
        if ($data) {
            $response = $this->MzDrugRequest_model->loadWhsDocumentProcurementPriceLinkSourceList($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Загрузка списка связей позиций сводной заявки/лота с коммерческими предложениями
     */
    function loadWhsDocumentCommercialOfferDrugList() {
        $data = $this->ProcessInputData('loadWhsDocumentCommercialOfferDrugList', false);
        if ($data) {
            $response = $this->MzDrugRequest_model->loadWhsDocumentCommercialOfferDrugList($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Загрузка списка коммерческих предложений для формы добавления
     */
    function loadWhsDocumentCommercialOfferDrugSourceList() {
        $data = $this->ProcessInputData('loadWhsDocumentCommercialOfferDrugSourceList', false);
        if ($data) {
            $response = $this->MzDrugRequest_model->loadWhsDocumentCommercialOfferDrugSourceList($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Загрузка списка связей позиций сводной заявки/лота с позициями ГК
     */
    function loadWhsDocumentProcurementSupplySpecList() {
        $data = $this->ProcessInputData('loadWhsDocumentProcurementSupplySpecList', false);
        if ($data) {
            $response = $this->MzDrugRequest_model->loadWhsDocumentProcurementSupplySpecList($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Загрузка списка позиций ГК для формы добавления
     */
    function loadWhsDocumentProcurementSupplySpecSourceList() {
        $data = $this->ProcessInputData('loadWhsDocumentProcurementSupplySpecSourceList', false);
        if ($data) {
            $response = $this->MzDrugRequest_model->loadWhsDocumentProcurementSupplySpecSourceList($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Сохраненние данных по рассчету цен для позиции сводной заявки или лота
     */
    function saveWhsDocumentProcurementPrice() {
        $session_data = getSessionParams();
        $data = $this->ProcessInputData('saveWhsDocumentProcurementPrice', false);
        if ($data){
            $data['pmUser_id'] = $session_data['pmUser_id'];

            $err_msg = null;

            if (empty($err_msg) && !empty($data['TariffJsonData'])) {
                $response = $this->MzDrugRequest_model->saveWhsDocumentProcurementPriceLinkFromJSON(array(
                    'DrugRequestPurchaseSpec_id' => $data['DrugRequestPurchaseSpec_id'],
                    'WhsDocumentProcurementRequestSpec_id' => $data['WhsDocumentProcurementRequestSpec_id'],
                    'json_str' => $data['TariffJsonData'],
                    'pmUser_id' => $data['pmUser_id']
                ));
                if (!empty($response['Error_Msg'])) {
                    $err_msg = $response['Error_Msg'];
                }
            }

            if (empty($err_msg) && !empty($data['OfferJsonData'])) {
                $response = $this->MzDrugRequest_model->saveWhsDocumentCommercialOfferDrugFromJSON(array(
                    'DrugRequestPurchaseSpec_id' => $data['DrugRequestPurchaseSpec_id'],
                    'WhsDocumentProcurementRequestSpec_id' => $data['WhsDocumentProcurementRequestSpec_id'],
                    'json_str' => $data['OfferJsonData'],
                    'pmUser_id' => $data['pmUser_id']
                ));
                if (!empty($response['Error_Msg'])) {
                    $err_msg = $response['Error_Msg'];
                }
            }

            if (empty($err_msg) && !empty($data['SupplyJsonData'])) {
                $response = $this->MzDrugRequest_model->saveWhsDocumentProcurementSupplySpecFromJSON(array(
                    'DrugRequestPurchaseSpec_id' => $data['DrugRequestPurchaseSpec_id'],
                    'WhsDocumentProcurementRequestSpec_id' => $data['WhsDocumentProcurementRequestSpec_id'],
                    'json_str' => $data['SupplyJsonData'],
                    'pmUser_id' => $data['pmUser_id']
                ));
                if (!empty($response['Error_Msg'])) {
                    $err_msg = $response['Error_Msg'];
                }
            }

            //редактирование строки сводной заявки
            if (empty($err_msg) && !empty($data['DrugRequestPurchaseSpec_id'])) {
                $response = $this->MzDrugRequest_model->saveWhsDocumentProcurementPriceDataInRequestSpec(array(
                    'DrugRequestPurchaseSpec_id' => $data['DrugRequestPurchaseSpec_id'],
                    'TotalPrice' => $data['TotalPrice'],
                    'CalculationDate' => $data['CalculationDate'],
                    'CalculatPriceType_id' => $data['CalculatPriceType_id'],
                    'pmUser_id' => $data['pmUser_id']
                ));


                if (!empty($response['Error_Msg'])) {
                    $err_msg = $response['Error_Msg'];
                }
            }

            //редактирование строки лота
            if (empty($err_msg) && !empty($data['WhsDocumentProcurementRequestSpec_id'])) {
                $response = $this->MzDrugRequest_model->saveObject('WhsDocumentProcurementRequestSpec', array(
                    'WhsDocumentProcurementRequestSpec_id' => $data['WhsDocumentProcurementRequestSpec_id'],
                    'WhsDocumentProcurementRequestSpec_PriceMax' => $data['TotalPrice'],
                    'WhsDocumentProcurementRequestSpec_CalcPriceDate' => $data['CalculationDate'],
                    'CalculatPriceType_id' => $data['CalculatPriceType_id'],
                    'pmUser_id' => $data['pmUser_id']
                ));
                if (!empty($response['Error_Msg'])) {
                    $err_msg = $response['Error_Msg'];
                }
            }

            if (!empty($err_msg)) {
                $this->ReturnError(toUTF($err_msg));
                return false;
            }

            $this->ReturnData(array('success' => true, 'Error_Code' => null, 'Error_Msg' => null));
            return true;
        } else {
            return false;
        }
    }

    /**
     * Загрузка данных для рассчета цен
     */
    function loadWhsDocumentProcurementPrice() {
        $data = $this->ProcessInputData('loadWhsDocumentProcurementPrice', false);
        if ($data){
            $response = $this->MzDrugRequest_model->loadWhsDocumentProcurementPrice($data);
            $this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
	 * Проверка количества медикамента
	 */
	/*function checkDrugAmount() {
		$data = $this->ProcessInputData('checkDrugAmount', true);
		if ($data) {
			$response = $this->MzDrugRequest_model->checkDrugAmount($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}*/

    /**
	 * Проверка наличия человека в разнарядке "первой копии" заявки
	 */
	function checkExistPersonInFirstCopy() {
		$data = $this->ProcessInputData('checkExistPersonInFirstCopy', true);
		if ($data) {
			$response = $this->MzDrugRequest_model->checkExistPersonInFirstCopy($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

    /**
     * Проверка наличия медикамента в разнарядках "первой копии" заявочной кампании
     */
    function checkExistPersonDrugInRegionFirstCopy() {
		$data = $this->ProcessInputData('checkExistPersonDrugInRegionFirstCopy', true);
		if ($data) {
			$response = $this->MzDrugRequest_model->checkExistPersonDrugInRegionFirstCopy($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Копирование плановых параметров в "первую копию заявки", с удалением существующих
	 */
	function copyDrugRequestPlanToFirstCopy() {
		$data = $this->ProcessInputData('copyDrugRequestPlanToFirstCopy', true);
		if ($data) {
			$response = $this->MzDrugRequest_model->copyDrugRequestPlanToFirstCopy($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
}

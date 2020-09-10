<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Api - контроллер API для работы с медицинскими изделиями
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			API
 * @access			public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Dmitriy Vlasenko
 * @version			11.2016
 */

require(APPPATH.'libraries/SwREST_Controller.php');

class MedProductCard extends SwREST_Controller {

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('LpuPassport_model', 'dbmodel');
		$this->inputRules = array(
			'getMedProductCard' => array(
				array('field' => 'MedProductCard_id', 'label' => 'Идентификатор мед. изделия', 'rules' => '', 'type' => 'id'),
				array('field' => 'AccountingData_InventNumber', 'label' => 'Инвентарный номер', 'rules' => '', 'type' => 'string')
			),
			'getMedProductCardList' => array(
				array('field' => 'MedProductClass_id', 'label' => 'Класс МИ', 'rules' => 'required', 'type' => 'id')
			),
			'createMedProductCard' => array(
				array('field' => 'MedProductClass_id', 'label' => 'Класс МИ', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'AccountingData_InventNumber', 'label' => 'Инвентарный номер', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'MedProductCard_SerialNumber', 'label' => 'Серийный номер', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'AccountingData_RegNumber', 'label' => 'Регистрационный знак для автомобилей', 'rules' => '', 'type' => 'string'),
				array('field' => 'LpuBuilding_id', 'label' => 'Идентификатор подразделения', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'LpuUnit_id', 'label' => 'Идентификатор группы отделений', 'rules' => '', 'type' => 'id'),
				array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => '', 'type' => 'id'),
				array('field' => 'Org_id', 'label' => 'Идентификатор поставщика', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'MedProductCard_begDate', 'label' => 'Дата выпуска', 'rules' => 'required', 'type' => 'date'),
				array('field' => 'MedProductCard_UsePeriod', 'label' => 'Срок использования', 'rules' => '', 'type' => 'int'),
				array('field' => 'MedProductCard_IsEducatAct', 'label' => 'Признак "Наличе акта об обучении мед. персонала"', 'rules' => 'required', 'type' => 'api_flag_nc'),
				array('field' => 'MedProductCard_IsNoAvailLpu', 'label' => 'Признак "Недоступна для МО"', 'rules' => 'required', 'type' => 'api_flag_nc'),
				array('field' => 'RegCertificate_endDate', 'label' => 'Срок действия рег. удостоверения', 'rules' => '', 'type' => 'date'),
				array('field' => 'RegCertificate_setDate', 'label' => 'Дата рег. удостоверения', 'rules' => '', 'type' => 'date'),
				array('field' => 'RegCertificate_Number', 'label' => 'Номер рег. удостоверения', 'rules' => '', 'type' => 'string'),
				array('field' => 'RegCertificate_OrderNumber', 'label' => 'Номер приказа', 'rules' => '', 'type' => 'string'),
				array('field' => 'RegCertificate_MedProductName', 'label' => 'Наименование МИ по регистрационным документам', 'rules' => '', 'type' => 'string'),
				array('field' => 'Org_regid', 'label' => 'Идентификатор организации держателя удостоверения', 'rules' => '', 'type' => 'id'),
				array('field' => 'Org_prid', 'label' => 'Идентификатор организации производителя', 'rules' => '', 'type' => 'id'),
				array('field' => 'Org_decid', 'label' => 'Идентификатор организации декларанта', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedProductCard_Options', 'label' => 'Комплектация', 'rules' => '', 'type' => 'string'),
				array('field' => 'MedProductCard_OtherParam', 'label' => 'Прочие параметры', 'rules' => '', 'type' => 'string'),
				array('field' => 'MeasureFund_IsMeasure', 'label' => 'Признак "Является средством измерения"', 'rules' => 'required', 'type' => 'api_flag_nc'),
				array('field' => 'MeasureFund_Range', 'label' => 'Диапазон измерения', 'rules' => '', 'type' => 'string'),
				array('field' => 'OkeiLink_id', 'label' => '', 'rules' => 'Ед. измерения', 'type' => 'id'),
				array('field' => 'MeasureFund_RegNumber', 'label' => 'Регистрационный номер средств измерения', 'rules' => '', 'type' => 'string'),
				array('field' => 'MeasureFund_AccuracyClass', 'label' => 'Класс точности средств измерения', 'rules' => '', 'type' => 'string'),
				array('field' => 'AccountingData_buyDate', 'label' => 'Дата приобретения', 'rules' => 'required', 'type' => 'date'),
				array('field' => 'AccountingData_begDate', 'label' => 'Дата принятия на учёт', 'rules' => '', 'type' => 'date'),
				array('field' => 'GosContract_Number', 'label' => 'Номер гос. контракта', 'rules' => '', 'type' => 'string'),
				array('field' => 'AccountingData_setDate', 'label' => 'Дата ввода в эксплуатацию', 'rules' => '', 'type' => 'date'),
				array('field' => 'AccountingData_endDate', 'label' => 'Дата вывода из эксплуатации', 'rules' => '', 'type' => 'date'),
				array('field' => 'FinancingType_id', 'label' => 'Идентификатор "Программа закупки"', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'AccountingData_ProductCost', 'label' => 'Цена производителя', 'rules' => 'required', 'type' => 'float'),
				array('field' => 'PropertyType_id', 'label' => 'Форма владения', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'GosContract_setDate', 'label' => 'Дата заключения контракта', 'rules' => '', 'type' => 'date'),
				array('field' => 'AccountingData_BuyCost', 'label' => 'Стоимость приобретения', 'rules' => 'required', 'type' => 'float'),
				array('field' => 'DeliveryType_id', 'label' => 'Тип поставки', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'MedProductCard_DocumentTO', 'label' => 'Документ, подтверждающий прохождение ТО', 'rules' => '', 'type' => 'string'),
				array('field' => 'MedProductCard_IsContractTO', 'label' => 'Наличие договора на тех. обслуживание', 'rules' => 'required', 'type' => 'api_flag_nc'),
				array('field' => 'Org_toid', 'label' => 'Идентификатор организации, осуществляющей тех. обслуживание', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedProductCard_IsOrgLic', 'label' => 'Наличие лицензии на проведение тех. обслуживания', 'rules' => 'required', 'type' => 'api_flag_nc'),
				array('field' => 'MedProductCard_IsLpuLic', 'label' => 'Наличие лицензии у МО на проведение тех. обслуживания', 'rules' => 'required', 'type' => 'api_flag_nc'),
				array('field' => 'MedProductCard_IsRepair', 'label' => 'Признак "Требует ремонта"', 'rules' => 'required', 'type' => 'api_flag_nc'),
				array('field' => 'MedProductCard_IsSpisan', 'label' => 'Признак "Требует списания"', 'rules' => 'required', 'type' => 'api_flag_nc'),
				array('field' => 'MedProductCard_RepairDate', 'label' => 'Дата установки статуса: "Требует ремонта"', 'rules' => '', 'type' => 'date'),
				array('field' => 'MedProductCard_SpisanDate', 'label' => 'Дата установки статуса: "Требует списания"', 'rules' => '', 'type' => 'date'),
				array('field' => 'MedProductCard_SetResource', 'label' => 'Установленный/назначенный ресурс (ед.)', 'rules' => '', 'type' => 'float'),
				array('field' => 'MedProductCard_AvgProcTime', 'label' => 'Средняя длительность процедуры (ед.)', 'rules' => '', 'type' => 'float'),
			),
			'updateMedProductCard' => array(
				array('field' => 'MedProductCard_id', 'label' => 'Идентификатор записи', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'MedProductClass_id', 'label' => 'Класс МИ', 'rules' => '', 'type' => 'id'),
				array('field' => 'AccountingData_InventNumber', 'label' => 'Инвентарный номер', 'rules' => '', 'type' => 'string'),
				array('field' => 'MedProductCard_SerialNumber', 'label' => 'Серийный номер', 'rules' => '', 'type' => 'string'),
				array('field' => 'AccountingData_RegNumber', 'label' => 'Регистрационный знак для автомобилей', 'rules' => '', 'type' => 'string'),
				array('field' => 'LpuBuilding_id', 'label' => 'Идентификатор подразделения', 'rules' => '', 'type' => 'id'),
				array('field' => 'LpuUnit_id', 'label' => 'Идентификатор группы отделений', 'rules' => '', 'type' => 'id'),
				array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => '', 'type' => 'id'),
				array('field' => 'Org_id', 'label' => 'Идентификатор поставщика', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedProductCard_begDate', 'label' => 'Дата выпуска', 'rules' => '', 'type' => 'date'),
				array('field' => 'MedProductCard_UsePeriod', 'label' => 'Срок использования', 'rules' => '', 'type' => 'int'),
				array('field' => 'MedProductCard_IsEducatAct', 'label' => 'Признак "Наличе акта об обучении мед. персонала"', 'rules' => '', 'type' => 'api_flag_nc'),
				array('field' => 'MedProductCard_IsNoAvailLpu', 'label' => 'Признак "Недоступна для МО"', 'rules' => '', 'type' => 'api_flag_nc'),
				array('field' => 'RegCertificate_endDate', 'label' => 'Срок действия рег. удостоверения', 'rules' => '', 'type' => 'date'),
				array('field' => 'RegCertificate_setDate', 'label' => 'Дата рег. удостоверения', 'rules' => '', 'type' => 'date'),
				array('field' => 'RegCertificate_Number', 'label' => 'Номер рег. удостоверения', 'rules' => '', 'type' => 'string'),
				array('field' => 'RegCertificate_OrderNumber', 'label' => 'Номер приказа', 'rules' => '', 'type' => 'string'),
				array('field' => 'RegCertificate_MedProductName', 'label' => 'Наименование МИ по регистрационным документам', 'rules' => '', 'type' => 'string'),
				array('field' => 'Org_regid', 'label' => 'Идентификатор организации держателя удостоверения', 'rules' => '', 'type' => 'id'),
				array('field' => 'Org_prid', 'label' => 'Идентификатор организации производителя', 'rules' => '', 'type' => 'id'),
				array('field' => 'Org_decid', 'label' => 'Идентификатор организации декларанта', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedProductCard_Options', 'label' => 'Комплектация', 'rules' => '', 'type' => 'string'),
				array('field' => 'MedProductCard_OtherParam', 'label' => 'Прочие параметры', 'rules' => '', 'type' => 'string'),
				array('field' => 'MeasureFund_IsMeasure', 'label' => 'Признак "Является средством измерения"', 'rules' => '', 'type' => 'api_flag_nc'),
				array('field' => 'MeasureFund_Range', 'label' => 'Диапазон измерения', 'rules' => '', 'type' => 'string'),
				array('field' => 'OkeiLink_id', 'label' => '', 'rules' => 'Ед. измерения', 'type' => 'id'),
				array('field' => 'MeasureFund_RegNumber', 'label' => 'Регистрационный номер средств измерения', 'rules' => '', 'type' => 'string'),
				array('field' => 'MeasureFund_AccuracyClass', 'label' => 'Класс точности средств измерения', 'rules' => '', 'type' => 'string'),
				array('field' => 'AccountingData_buyDate', 'label' => 'Дата приобретения', 'rules' => '', 'type' => 'date'),
				array('field' => 'AccountingData_begDate', 'label' => 'Дата принятия на учёт', 'rules' => '', 'type' => 'date'),
				array('field' => 'GosContract_Number', 'label' => 'Номер гос. контракта', 'rules' => '', 'type' => 'string'),
				array('field' => 'AccountingData_setDate', 'label' => 'Дата ввода в эксплуатацию', 'rules' => '', 'type' => 'date'),
				array('field' => 'AccountingData_endDate', 'label' => 'Дата вывода из эксплуатации', 'rules' => '', 'type' => 'date'),
				array('field' => 'FinancingType_id', 'label' => 'Идентификатор "Программа закупки"', 'rules' => '', 'type' => 'id'),
				array('field' => 'AccountingData_ProductCost', 'label' => 'Цена производителя', 'rules' => '', 'type' => 'float'),
				array('field' => 'PropertyType_id', 'label' => 'Форма владения', 'rules' => '', 'type' => 'id'),
				array('field' => 'GosContract_setDate', 'label' => 'Дата заключения контракта', 'rules' => '', 'type' => 'date'),
				array('field' => 'AccountingData_BuyCost', 'label' => 'Стоимость приобретения', 'rules' => '', 'type' => 'float'),
				array('field' => 'DeliveryType_id', 'label' => 'Тип поставки', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedProductCard_DocumentTO', 'label' => 'Документ, подтверждающий прохождение ТО', 'rules' => '', 'type' => 'string'),
				array('field' => 'MedProductCard_IsContractTO', 'label' => 'Наличие договора на тех. обслуживание', 'rules' => '', 'type' => 'api_flag_nc'),
				array('field' => 'Org_toid', 'label' => 'Идентификатор организации, осуществляющей тех. обслуживание', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedProductCard_IsOrgLic', 'label' => 'Наличие лицензии на проведение тех. обслуживания', 'rules' => '', 'type' => 'api_flag_nc'),
				array('field' => 'MedProductCard_IsLpuLic', 'label' => 'Наличие лицензии у МО на проведение тех. обслуживания', 'rules' => '', 'type' => 'api_flag_nc'),
				array('field' => 'MedProductCard_IsRepair', 'label' => 'Признак "Требует ремонта"', 'rules' => '', 'type' => 'api_flag_nc'),
				array('field' => 'MedProductCard_IsSpisan', 'label' => 'Признак "Требует списания"', 'rules' => '', 'type' => 'api_flag_nc'),
				array('field' => 'MedProductCard_RepairDate', 'label' => 'Дата установки статуса: "Требует ремонта"', 'rules' => '', 'type' => 'date'),
				array('field' => 'MedProductCard_SpisanDate', 'label' => 'Дата установки статуса: "Требует списания"', 'rules' => '', 'type' => 'date'),
				array('field' => 'MedProductCard_SetResource', 'label' => 'Установленный/назначенный ресурс (ед.)', 'rules' => '', 'type' => 'float'),
				array('field' => 'MedProductCard_AvgProcTime', 'label' => 'Средняя длительность процедуры (ед.)', 'rules' => '', 'type' => 'float'),
			),
			'getConsumablesById' => array(
				array('field' => 'Consumables_id', 'label' => 'Идентификатор записи', 'rules' => 'required', 'type' => 'id')
			),
			'getConsumablesByPar' => array(
				array('field' => 'Consumables_Name', 'label' => 'Наименование расходного материала', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'MedProductCard_id', 'label' => 'Идентификатор мед. изделия', 'rules' => 'required', 'type' => 'id')
			),
			'createConsumables' => array(
				array('field' => 'MedProductCard_id', 'label' => 'Идентификатор мед. изделия', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Consumables_Name', 'label' => 'Наименование расходного материала', 'rules' => 'required', 'type' => 'string')
			),
			'updateConsumables' => array(
				array('field' => 'Consumables_id', 'label' => 'Идентификатор записи', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'MedProductCard_id', 'label' => 'Идентификатор мед. изделия', 'rules' => '', 'type' => 'id'),
				array('field' => 'Consumables_Name', 'label' => 'Наименование расходного материала', 'rules' => '', 'type' => 'string')
			),
			'getMeasureFundCheck' => array(
				array('field' => 'MeasureFundCheck_id', 'label' => 'Идентификатор записи', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedProductCard_id', 'label' => 'Идентификатор мед. изделия', 'rules' => '', 'type' => 'id'),
				array('field' => 'MeasureFundCheck_Number', 'label' => 'Номер свидетельства о проверки', 'rules' => '', 'type' => 'string'),
				array('field' => 'MeasureFundCheck_endDate', 'label' => 'Срок действия свидетельства', 'rules' => '', 'type' => 'date')
			),
			'createMeasureFundCheck' => array(
				array('field' => 'MedProductCard_id', 'label' => 'Идентификатор мед. изделия', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'MeasureFundCheck_Number', 'label' => 'Номер свидетельства о проверке', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'MeasureFundCheck_setDate', 'label' => 'Дата свидетельства о проверке', 'rules' => 'required', 'type' => 'date'),
				array('field' => 'MeasureFundCheck_endDate', 'label' => 'Срок действия свидетельства', 'rules' => 'required', 'type' => 'date')
			),
			'updateMeasureFundCheck' => array(
				array('field' => 'MeasureFundCheck_id', 'label' => 'Идентификатор записи', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'MedProductCard_id', 'label' => 'Идентификатор мед. изделия', 'rules' => '', 'type' => 'id'),
				array('field' => 'MeasureFundCheck_Number', 'label' => 'Номер свидетельства о проверке', 'rules' => '', 'type' => 'string'),
				array('field' => 'MeasureFundCheck_setDate', 'label' => 'Дата свидетельства о проверке', 'rules' => '', 'type' => 'date'),
				array('field' => 'MeasureFundCheck_endDate', 'label' => 'Срок действия свидетельства', 'rules' => '', 'type' => 'date')
			),
			'getAmortization' => array(
				array('field' => 'Amortization_id', 'label' => 'Идентификатор записи', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedProductCard_id', 'label' => 'Идентификатор мед. изделия', 'rules' => '', 'type' => 'id'),
				array('field' => 'Amortization_setDate', 'label' => 'Дата оценки', 'rules' => '', 'type' => 'date')
			),
			'createAmortization' => array(
				array('field' => 'MedProductCard_id', 'label' => 'Идентификатор мед. изделия', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Amortization_setDate', 'label' => 'Дата оценки', 'rules' => 'required', 'type' => 'date'),
				array('field' => 'Amortization_FactCost', 'label' => 'Фактическая стоимость', 'rules' => 'required', 'type' => 'float'),
				array('field' => 'Amortization_WearPercent', 'label' => 'Процент износа', 'rules' => 'required', 'type' => 'float'),
				array('field' => 'Amortization_ResidCost', 'label' => 'Остаточная стоимость', 'rules' => 'required', 'type' => 'float')
			),
			'updateAmortization' => array(
				array('field' => 'Amortization_id', 'label' => 'Идентификатор записи', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'MedProductCard_id', 'label' => 'Идентификатор мед. изделия', 'rules' => '', 'type' => 'id'),
				array('field' => 'Amortization_setDate', 'label' => 'Дата оценки', 'rules' => '', 'type' => 'date'),
				array('field' => 'Amortization_FactCost', 'label' => 'Фактическая стоимость', 'rules' => '', 'type' => 'float'),
				array('field' => 'Amortization_WearPercent', 'label' => 'Процент износа', 'rules' => '', 'type' => 'float'),
				array('field' => 'Amortization_ResidCost', 'label' => 'Остаточная стоимость', 'rules' => '', 'type' => 'float')
			),
			'getDowntimeByPar' => array(
				array('field' => 'Downtime_id', 'label' => 'Идентификатор записи', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedProductCard_id', 'label' => 'Идентификатор мед. изделия', 'rules' => '', 'type' => 'id'),
				array('field' => 'Downtime_begDate', 'label' => 'Дата начала простоя', 'rules' => '', 'type' => 'date'),
				array('field' => 'DowntimeCause_id', 'label' => 'Идентификатор причин простоя', 'rules' => '', 'type' => 'id')
			),
			'getDowntime' => array(
				array('field' => 'MedProductCard_id', 'label' => 'Идентификатор мед. изделия', 'rules' => 'required', 'type' => 'id')
			),
			'createDowntime' => array(
				array('field' => 'MedProductCard_id', 'label' => 'Идентификатор мед. изделия', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Downtime_begDate', 'label' => 'Дата начала простоя', 'rules' => 'required', 'type' => 'date'),
				array('field' => 'Downtime_endDate', 'label' => 'Дата возобновления работы', 'rules' => 'required', 'type' => 'date'),
				array('field' => 'DowntimeCause_id', 'label' => 'Идентификатор причин простоя', 'rules' => 'required', 'type' => 'id')
			),
			'updateDowntime' => array(
				array('field' => 'Downtime_id', 'label' => 'Идентификатор записи', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'MedProductCard_id', 'label' => 'Идентификатор мед. изделия', 'rules' => '', 'type' => 'id'),
				array('field' => 'Downtime_begDate', 'label' => 'Дата начала простоя', 'rules' => '', 'type' => 'date'),
				array('field' => 'Downtime_endDate', 'label' => 'Дата возобновления работы', 'rules' => '', 'type' => 'date'),
				array('field' => 'DowntimeCause_id', 'label' => 'Идентификатор причин простоя', 'rules' => '', 'type' => 'id')
			),
			'getWorkData' => array(
				array('field' => 'WorkData_id', 'label' => 'Идентификатор записи', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedProductCard_id', 'label' => 'Идентификатор мед. изделия', 'rules' => '', 'type' => 'id'),
				array('field' => 'WorkData_WorkPeriod', 'label' => 'Период эксплуатации', 'rules' => '', 'type' => 'date'),
			),
			'createWorkData' => array(
				array('field' => 'MedProductCard_id', 'label' => 'Идентификатор мед. изделия', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'WorkData_WorkPeriod', 'label' => 'Период эксплуатации', 'rules' => 'required', 'type' => 'date'),
				array('field' => 'WorkData_DayChange', 'label' => 'Количество смен в сутки', 'rules' => 'required', 'type' => 'float'),
				array('field' => 'WorkData_CountUse', 'label' => 'Общее количество применений за период', 'rules' => 'required', 'type' => 'int'),
				array('field' => 'WorkData_KolDay', 'label' => 'Количество рабочий дней в периоде', 'rules' => 'required', 'type' => 'float')
			),
			'updateWorkData' => array(
				array('field' => 'WorkData_id', 'label' => 'Идентификатор записи', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'MedProductCard_id', 'label' => 'Идентификатор мед. изделия', 'rules' => '', 'type' => 'id'),
				array('field' => 'WorkData_WorkPeriod', 'label' => 'Период эксплуатации', 'rules' => '', 'type' => 'date'),
				array('field' => 'WorkData_DayChange', 'label' => 'Количество смен в сутки', 'rules' => '', 'type' => 'float'),
				array('field' => 'WorkData_CountUse', 'label' => 'Общее количество применений за период', 'rules' => '', 'type' => 'int'),
				array('field' => 'WorkData_KolDay', 'label' => 'Количество рабочий дней в периоде', 'rules' => '', 'type' => 'float')
			),
			'deleteMedProductCardAttributes' => array(
				array('field' => 'MedProductCard_id', 'label' => 'Идентификатор мед. изделия', 'rules' => 'required', 'type' => 'id')
			)
		);
	}

	/**
	 * Получение Медицинского изделия по идентификатору
	 */
	function index_get() {
		$data = $this->ProcessInputData('getMedProductCard');

		$paramsExist = false;
		foreach ($data as $value) {
			if(!empty($value)){
				$paramsExist = true;
			}
		}
		if(!$paramsExist){
			$this->response(array(
				'error_msg' => 'Не передан ни один параметр',
				'error_code' => '6',
				'data' => ''
			));
		}

		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];

		$resp = $this->dbmodel->getMedProductCardForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 *  Получение списка Медицинских изделий одного класса
	 */
	function getMedProductCardList_get() {
		$data = $this->ProcessInputData('getMedProductCardList', null, true);

		$resp = $this->dbmodel->getMedProductCardListForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 *  Получение количества Медицинских изделий одного класса
	 */
	function getMedProductCardCount_get() {
		$data = $this->ProcessInputData('getMedProductCardList', null, true);

		$resp = $this->dbmodel->getMedProductCardCountForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if(!empty($resp[0]['cnt'])){
			$resp = $resp[0]['cnt'];
		} else {
			$resp = 0;
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Создание Медицинского изделия
	 */
	function index_post() {
		$data = $this->ProcessInputData('createMedProductCard');

		if (!empty($data['MedProductCard_IsContractTO']) && $data['MedProductCard_IsContractTO'] == 1) {
			if (empty($data['Org_toid'])) {
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Не заполнено поле Org_toid'
				));
			}
		}
		if (!empty($data['MedProductCard_IsRepair']) && $data['MedProductCard_IsRepair'] == 1) {
			if (empty($data['MedProductCard_RepairDate'])) {
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Не заполнено поле MedProductCard_RepairDate'
				));
			}
		}
		if (!empty($data['MedProductCard_IsSpisan']) && $data['MedProductCard_IsSpisan'] == 1) {
			if (empty($data['MedProductCard_SpisanDate'])) {
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Не заполнено поле MedProductCard_SpisanDate'
				));
			}
		}

		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		
		$lpuID = $this->dbmodel->getFirstResultFromQuery('SELECT Lpu_id FROM v_LpuBuilding (nolock) WHERE LpuBuilding_id = :LpuBuilding_id', $data);
		if($lpuID != $sp['Lpu_id']){
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Данный метод доступен только для своей МО'
			));
		}
		
		//получаем список полей
		$queryFields = $this->db->query("select 'Parameter_name' = name, 'Type' = type_name(user_type_id) from sys.parameters where object_id = object_id('passport.p_MedProductCard_ins')");
		$allFields = $queryFields->result_array();
		foreach ($allFields as $fieldVal)
		{
			$field = ltrim($fieldVal["Parameter_name"], "@");
			if(empty($data[$field]))$data[$field] = null;
		}
		$resp = $this->dbmodel->saveMedProductCard(array_merge($data, array(
			'MedProductCard_id' => null,
			'Lpu_id' => $this->dbmodel->getFirstResultFromQuery('SELECT Lpu_id FROM v_LpuBuilding (nolock) WHERE LpuBuilding_id = :LpuBuilding_id', $data)
		)));
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['MedProductCard_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array('MedProductCard_id'=>$resp[0]['MedProductCard_id'])
		));
	}

	/**
	 * Редактирование Медицинского изделия
	 */
	function index_put() {
		$data = $this->ProcessInputData('updateMedProductCard');

		// достаём обязательные поля из метода создания (их нельзя перезаписывать на пустые)
		$requiredFields = $this->getRequiredFields('createMedProductCard');
		$data = $this->unsetEmptyFields($data, $requiredFields);

		if (!empty($data['MedProductCard_IsContractTO']) && $data['MedProductCard_IsContractTO'] == 1) {
			if (empty($data['Org_toid'])) {
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Не заполнено поле Org_toid'
				));
			}
		}
		if (!empty($data['MedProductCard_IsRepair']) && $data['MedProductCard_IsRepair'] == 1) {
			if (empty($data['MedProductCard_RepairDate'])) {
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Не заполнено поле MedProductCard_RepairDate'
				));
			}
		}
		if (!empty($data['MedProductCard_IsSpisan']) && $data['MedProductCard_IsSpisan'] == 1) {
			if (empty($data['MedProductCard_SpisanDate'])) {
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Не заполнено поле MedProductCard_SpisanDate'
				));
			}
		}

		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		$data['Lpu_id'] = $sp['Lpu_id'];

		$old_data = $this->dbmodel->getMedProductCardForAPI($data);
		if (empty($old_data[0])) {
			//$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Медицинское изделие для редактирования не найдено.'
			));
		}

		$data = array_merge($old_data[0], $data);
		
		//получаем список полей
		$queryFields = $this->db->query("select 'Parameter_name' = name, 'Type' = type_name(user_type_id) from sys.parameters where object_id = object_id('passport.p_MedProductCard_upd')");
		$allFields = $queryFields->result_array();
		foreach ($allFields as $fieldVal)
		{
			$field = ltrim($fieldVal["Parameter_name"], "@");
			if(empty($data[$field]))$data[$field] = null;
		}
		$resp = $this->dbmodel->saveMedProductCard(array_merge($data, array(
			'Lpu_id' => $this->dbmodel->getFirstResultFromQuery('SELECT Lpu_id FROM v_LpuBuilding (nolock) WHERE LpuBuilding_id = :LpuBuilding_id', $data)
		)));
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['MedProductCard_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array('MedProductCard_id'=>$resp[0]['MedProductCard_id'])
		));
	}

	/**
	 * Получение записи «Расходные материалы» по идентификатору
	 */
	function ConsumablesById_get() {
		$data = $this->ProcessInputData('getConsumablesById');
		
		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];

		$resp = $this->dbmodel->getConsumablesForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Создание записи «Расходные материалы»
	 */
	function Consumables_post() {
		$data = $this->ProcessInputData('createConsumables');
		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		
		$lpuID = $this->dbmodel->getLpuByMedProductCard($data);
		if($lpuID != $sp['Lpu_id']){
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Данный метод доступен только для своей МО'
			));
		}

		$resp = $this->dbmodel->saveConsumables(array_merge($data, array(
			'Consumables_id' => null
		)));
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['Consumables_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array('Consumables_id'=>$resp[0]['Consumables_id'])
		));
	}

	/**
	 * Редактирование записи «Расходные материалы»
	 */
	function Consumables_put() {
		$data = $this->ProcessInputData('updateConsumables');

		// достаём обязательные поля из метода создания (их нельзя перезаписывать на пустые)
		$requiredFields = $this->getRequiredFields('createConsumables');
		$data = $this->unsetEmptyFields($data, $requiredFields);

		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		$data['Lpu_id'] = $sp['Lpu_id'];

		$old_data = $this->dbmodel->getFirstRowFromQuery("
			select
				C.Consumables_id,
				C.MedProductCard_id,
				C.Consumables_Name,
				LB.Lpu_id
			from
				passport.v_Consumables C (nolock)
				left join passport.v_MedProductCard MPC with(nolock) on MPC.MedProductCard_id = C.MedProductCard_id
				left join LpuBuilding LB with (nolock) on MPC.LpuBuilding_id = LB.LpuBuilding_id
			where
				C.Consumables_id = :Consumables_id
		", $data);
		if(!empty($old_data) && $old_data['Lpu_id'] != $sp['Lpu_id']){
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Данный метод доступен только для своей МО'
			));
		}
		
		if (empty($old_data)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$resp = $this->dbmodel->saveConsumables(array_merge($old_data, $data));
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['Consumables_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array('Consumables_id'=>$resp[0]['Consumables_id'])
		));
	}

	/**
	 * Получение записи «Расходные материалы» по наименованию и мед изделию
	 */
	function ConsumablesByPar_get() {
		$data = $this->ProcessInputData('getConsumablesByPar');
		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];

		$resp = $this->dbmodel->getConsumablesForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение  записи «Свидетельство о проверке»
	 */
	function MeasureFundCheck_get() {
		$data = $this->ProcessInputData('getMeasureFundCheck',null,true);
		if(empty($data['MeasureFundCheck_id'])){
			if(empty($data['MedProductCard_id']) && empty($data['MeasureFundCheck_Number']) && empty($data['MeasureFundCheck_endDate'])){
				$this->response(array(
					'error_code' => 3,
					'error_msg' => 'При отсутствии параметров (MedProductCard_id,MeasureFundCheck_Number,MeasureFundCheck_endDate) параметр MeasureFundCheck_id обязателен'
				));	
			} else if(empty($data['MedProductCard_id']) || empty($data['MeasureFundCheck_Number']) || empty($data['MeasureFundCheck_endDate'])){
				$this->response(array(
					'error_code' => 3,
					'error_msg' => 'При отсутствии параметра MeasureFundCheck_id параметры (MedProductCard_id,MeasureFundCheck_Number,MeasureFundCheck_endDate) обязательны'
				));
			}
		}

		$resp = $this->dbmodel->getMeasureFundCheckForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Создание записи «Свидетельство о проверке»
	 */
	function MeasureFundCheck_post() {
		$data = $this->ProcessInputData('createMeasureFundCheck');
		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		
		$lpuID = $this->dbmodel->getLpuByMedProductCard($data);
		if($lpuID != $sp['Lpu_id']){
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Данный метод доступен только для своей МО'
			));
		}

		$resp = $this->dbmodel->saveMeasureFundCheck(array_merge($data, array(
			'MeasureFundCheck_id' => null
		)));
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['MeasureFundCheck_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array('MeasureFundCheck_id'=>$resp[0]['MeasureFundCheck_id'])
		));
	}

	/**
	 * Редактирование  записи «Свидетельство о проверке»
	 */
	function MeasureFundCheck_put() {
		$data = $this->ProcessInputData('updateMeasureFundCheck');
		$filter = "";
		// достаём обязательные поля из метода создания (их нельзя перезаписывать на пустые)
		$requiredFields = $this->getRequiredFields('createMeasureFundCheck');
		$data = $this->unsetEmptyFields($data, $requiredFields);

		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		$data['Lpu_id'] = $sp['Lpu_id'];
		
		if (!empty($data['Lpu_id'])) {
			$filter .= " and LB.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}

		$old_data = $this->dbmodel->getFirstRowFromQuery("
			select
				MFC.MeasureFundCheck_id,
				MFC.MedProductCard_id,
				MFC.MeasureFundCheck_Number,
				convert(varchar(10), MFC.MeasureFundCheck_setDate, 120) as MeasureFundCheck_setDate,
				convert(varchar(10), MFC.MeasureFundCheck_endDate, 120) as MeasureFundCheck_endDate
			from
				passport.v_MeasureFundCheck MFC (nolock)
				left join passport.v_MedProductCard MPC with(nolock) on MPC.MedProductCard_id = MFC.MedProductCard_id
				left join LpuBuilding LB with (nolock) on MPC.LpuBuilding_id = LB.LpuBuilding_id
			where
				MFC.MeasureFundCheck_id = :MeasureFundCheck_id
				{$filter}
		", $data);
		if (empty($old_data)) {
			//$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
			$this->response(array(
				'error_msg' => 'Записи для редактирования «Свидетельство о проверке» не найдено',
				'error_code' => '6'
			));
		}

		$resp = $this->dbmodel->saveMeasureFundCheck(array_merge($old_data, $data));
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['MeasureFundCheck_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array('MeasureFundCheck_id'=>$resp[0]['MeasureFundCheck_id'])
		));
	}

	/**
	 * Получение записи «Начисление износа»
	 */
	function Amortization_get() {
		$data = $this->ProcessInputData('getAmortization');
		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];

		$resp = $this->dbmodel->getAmortizationForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Создание записи «Начисление износа»
	 */
	function Amortization_post() {
		$data = $this->ProcessInputData('createAmortization');
		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		
		$lpuID = $this->dbmodel->getLpuByMedProductCard($data);
		if($lpuID != $sp['Lpu_id']){
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Данный метод доступен только для своей МО'
			));
		}

		$resp = $this->dbmodel->saveAmortization(array_merge($data, array(
			'Amortization_id' => null
		)));
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['Amortization_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array('Amortization_id'=>$resp[0]['Amortization_id'])
		));
	}

	/**
	 * Редактирование записи «Начисление износа»
	 */
	function Amortization_put() {
		$data = $this->ProcessInputData('updateAmortization');

		// достаём обязательные поля из метода создания (их нельзя перезаписывать на пустые)
		$requiredFields = $this->getRequiredFields('createAmortization');
		$data = $this->unsetEmptyFields($data, $requiredFields);

		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];

		$old_data = $this->dbmodel->getFirstRowFromQuery("
			select
				A.Amortization_id,
				A.MedProductCard_id,
				convert(varchar(10), A.Amortization_setDate, 120) as Amortization_setDate,
				A.Amortization_FactCost,
				A.Amortization_WearPercent,
				A.Amortization_ResidCost,
				LB.Lpu_id
			from
				passport.v_Amortization A (nolock)
				left join passport.v_MedProductCard MPC with(nolock) on MPC.MedProductCard_id = A.MedProductCard_id
				left join LpuBuilding LB with (nolock) on MPC.LpuBuilding_id = LB.LpuBuilding_id
			where
				A.Amortization_id = :Amortization_id
		", $data);
		if(!empty($old_data) && $old_data['Lpu_id'] != $sp['Lpu_id']){
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Данный метод доступен только для своей МО'
			));
		}
		if (empty($old_data)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$resp = $this->dbmodel->saveAmortization(array_merge($old_data, $data));
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['Amortization_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array('Amortization_id'=>$resp[0]['Amortization_id'])
		));
	}

	/**
	 * Получение записи «Простой МИ» по идентификатору
	 */
	function DowntimeByPar_get() {
		$data = $this->ProcessInputData('getDowntimeByPar');

		$paramsExist = false;
		foreach ($data as $value) {
			if(!empty($value)){
				$paramsExist = true;
			}
		}
		if(!$paramsExist){
			$this->response(array(
				'error_msg' => 'Не передан ни один параметр',
				'error_code' => '6',
				'data' => ''
			));
		}

		if (empty($data['Downtime_id']) && (!empty($data['MedProductCard_id']) || !empty($data['Downtime_begDate']) || !empty($data['DowntimeCause_id']))) {
			if (empty($data['MedProductCard_id']) || empty($data['Downtime_begDate']) || empty($data['DowntimeCause_id'])) {
				$this->response(array(
					'error_msg' => 'Параметры обязательные в группе: MedProductCard_id, Downtime_begDate, DowntimeCause_id',
					'error_code' => '6',
					'data' => ''
				));
			}
		}

		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];
		
		$resp = $this->dbmodel->getDowntimeForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Создание записи «Простой МИ»
	 */
	function Downtime_post() {
		$data = $this->ProcessInputData('createDowntime');
		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		
		$lpuID = $this->dbmodel->getLpuByMedProductCard($data);
		if($lpuID != $sp['Lpu_id']){
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Данный метод доступен только для своей МО'
			));
		}

		$resp = $this->dbmodel->saveDowntime(array_merge($data, array(
			'Downtime_id' => null
		)));
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['Downtime_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array('Downtime_id'=>$resp[0]['Downtime_id'])
		));
	}

	/**
	 * Редактирование записи «Простой МИ»
	 */
	function Downtime_put() {
		$data = $this->ProcessInputData('updateDowntime');

		// достаём обязательные поля из метода создания (их нельзя перезаписывать на пустые)
		$requiredFields = $this->getRequiredFields('createDowntime');
		$data = $this->unsetEmptyFields($data, $requiredFields);

		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];

		$old_data = $this->dbmodel->getFirstRowFromQuery("
			select
				D.Downtime_id,
				D.MedProductCard_id,
				convert(varchar(10), D.Downtime_begDate, 120) as Downtime_begDate,
				convert(varchar(10), D.Downtime_endDate, 120) as Downtime_endDate,
				D.DowntimeCause_id,
				LB.Lpu_id
			from
				passport.v_Downtime D (nolock)
				left join passport.v_MedProductCard MPC with(nolock) on MPC.MedProductCard_id = D.MedProductCard_id
				left join LpuBuilding LB with (nolock) on MPC.LpuBuilding_id = LB.LpuBuilding_id
			where
				Downtime_id = :Downtime_id
		", $data);
		
		if(!empty($old_data) && $old_data['Lpu_id'] != $sp['Lpu_id']){
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Данный метод доступен только для своей МО'
			));
		}
		if (empty($old_data)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$resp = $this->dbmodel->saveDowntime(array_merge($old_data, $data));
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['Downtime_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array('Downtime_id'=>$resp[0]['Downtime_id'])
		));
	}

	/**
	 * Получение списка записей «Простой МИ» для МИ
	 */
	function Downtime_get() {
		$data = $this->ProcessInputData('getDowntime');
		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];

		$resp = $this->dbmodel->getDowntimeForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение записи «Эксплуатационные данные»
	 */
	function WorkData_get() {
		$data = $this->ProcessInputData('getWorkData');
		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];

		$resp = $this->dbmodel->getWorkDataForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Создание записи «Эксплуатационные данные»
	 */
	function WorkData_post() {
		$data = $this->ProcessInputData('createWorkData');
		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		
		$lpuID = $this->dbmodel->getLpuByMedProductCard($data);
		if($lpuID != $sp['Lpu_id']){
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Данный метод доступен только для своей МО'
			));
		}

		$resp = $this->dbmodel->saveWorkData(array_merge($data, array(
			'WorkData_id' => null
		)));
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['WorkData_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				'WorkData_id'=>$resp[0]['WorkData_id'],
				'WorkData_AvgUse'=>$resp[0]['WorkData_AvgUse']
			)
		));
	}

	/**
	 * Редактирование записи «Эксплуатационные данные»
	 */
	function WorkData_put() {
		$data = $this->ProcessInputData('updateWorkData');

		// достаём обязательные поля из метода создания (их нельзя перезаписывать на пустые)
		$requiredFields = $this->getRequiredFields('createWorkData');
		$data = $this->unsetEmptyFields($data, $requiredFields);

		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];

		$old_data = $this->dbmodel->getFirstRowFromQuery("
			select
				WD.WorkData_id,
				WD.MedProductCard_id,
				convert(varchar(10), WD.WorkData_WorkPeriod, 120) as WorkData_WorkPeriod,
				WD.WorkData_DayChange,
				WD.WorkData_CountUse,
				WD.WorkData_KolDay,
				LB.Lpu_id
			from
				passport.v_WorkData WD (nolock)
				left join passport.v_MedProductCard MPC with(nolock) on MPC.MedProductCard_id = WD.MedProductCard_id
				left join LpuBuilding LB with (nolock) on MPC.LpuBuilding_id = LB.LpuBuilding_id
			where
				WD.WorkData_id = :WorkData_id
		", $data);
		if(!empty($old_data) && $old_data['Lpu_id'] != $sp['Lpu_id']){
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Данный метод доступен только для своей МО'
			));
		}
		if (empty($old_data)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$resp = $this->dbmodel->saveWorkData(array_merge($old_data, $data));
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['WorkData_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				'WorkData_id'=>$resp[0]['WorkData_id'],
				'WorkData_AvgUse'=>$resp[0]['WorkData_AvgUse']
			)
		));
	}

	/**
	 *  Удаление свойств Медицинского изделия
	 */
	function MedProductCardAttributes_delete() {
		$data = $this->ProcessInputData('deleteMedProductCardAttributes');
		$this->load->model('Utils_model', 'umodel');
		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];
		
		$med_data = $this->dbmodel->getFirstRowFromQuery("
			SELECT top 1
				LB.Lpu_id
			FROM
				passport.v_MedProductCard MPC
				left join LpuBuilding LB with (nolock) on MPC.LpuBuilding_id = LB.LpuBuilding_id
			WHERE 
				MPC.MedProductCard_id = :MedProductCard_id
		", $data);
		
		if(!empty($med_data) && $med_data['Lpu_id'] != $sp['Lpu_id']){
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Данный метод доступен только для своей МО'
			));
		}

		$resp = $this->dbmodel->getAmortizationForAPI($data, true);
		
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		} else if( count($resp)>0 ){
			foreach ($resp as $value) {
				if(!empty($value['Amortization_id'])){
					$res = $this->umodel->ObjectRecordsDelete(false, 'Amortization', false, array($value['Amortization_id']), 'passport', false);
					if(!empty($res[0]['Error_Message'])){
						$this->response(array(
							'error_msg' => $res[0]['Error_Message'],
							'error_code' => '6'
						));
					}
					if (!is_array($res)) {
						$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
					}
				}
			}
		}

		$resp = $this->dbmodel->getMeasureFundCheckForAPI($data, true);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		} else if( count($resp)>0 ){
			foreach ($resp as $value) {
				if(!empty($value['MeasureFundCheck_id'])){
					$res = $this->umodel->ObjectRecordsDelete(false, 'MeasureFundCheck', false, array($value['MeasureFundCheck_id']), 'passport', false);
					if(!empty($res[0]['Error_Message'])){
						$this->response(array(
							'error_msg' => $res[0]['Error_Message'],
							'error_code' => '6'
						));
					}
					if (!is_array($res)) {
						$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
					}
				}
			}
		}

		$resp = $this->dbmodel->getDowntimeForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		} else if( count($resp)>0 ){
			foreach ($resp as $value) {
				if(!empty($value['Downtime_id'])){
					$res = $this->umodel->ObjectRecordsDelete(false, 'Downtime', false, array($value['Downtime_id']), 'passport', false);
					if(!empty($res[0]['Error_Message'])){
						$this->response(array(
							'error_msg' => $res[0]['Error_Message'],
							'error_code' => '6'
						));
					}
					if (!is_array($res)) {
						$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
					}
				}
			}
		}

		$resp = $this->dbmodel->getWorkDataForAPI($data, true);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		} else if( count($resp)>0 ){
			foreach ($resp as $value) {
				if(!empty($value['WorkData_id'])){
					$res = $this->umodel->ObjectRecordsDelete(false, 'WorkData', false, array($value['WorkData_id']), 'passport', false);
					if(!empty($res[0]['Error_Message'])){
						$this->response(array(
							'error_msg' => $res[0]['Error_Message'],
							'error_code' => '6'
						));
					}
					if (!is_array($res)) {
						$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
					}
				}
			}
		}

		$resp = $this->dbmodel->getConsumablesForAPI($data, true);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		} else if( count($resp)>0 ){
			foreach ($resp as $value) {
				if(!empty($value['Consumables_id'])){
					$res = $this->umodel->ObjectRecordsDelete(false, 'Consumables', false, array($value['Consumables_id']), 'passport', false);
					if(!empty($res[0]['Error_Message'])){
						$this->response(array(
							'error_msg' => $res[0]['Error_Message'],
							'error_code' => '6'
						));
					}
					if (!is_array($res)) {
						$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
					}
				}
			}
		}

		$this->response(array(
			'error_code' => 0
		));
	}
}